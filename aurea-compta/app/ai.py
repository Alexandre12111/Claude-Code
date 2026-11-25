"""Lecture des justificatifs et assistant.

L'IA n'est jamais indispensable : les calculs comptables sont faits par du code
déterministe. L'IA propose (extraction, catégorie), l'utilisateur valide.

Ordre d'extraction d'une facture :
1. Factur-X / ZUGFeRD (XML structuré embarqué dans le PDF, format de la
   facturation électronique) : lecture exacte, sans IA.
2. Claude (API Anthropic) si une clé est configurée.
"""
import base64
import io
import json
import re
from datetime import date, datetime
from xml.etree import ElementTree as ET

from . import config, db, services, tax, vat
from .money import to_cents, vat_from_ht

EXTRACTION_SCHEMA = {
    "type": "object",
    "properties": {
        "document_type": {"type": "string", "enum": ["facture", "avoir", "ticket", "note_de_frais", "releve", "autre"]},
        "supplier_name": {"type": "string"},
        "supplier_country": {"type": "string", "description": "Code pays ISO à 2 lettres du fournisseur"},
        "supplier_vat_number": {"type": "string"},
        "customer_name": {"type": "string", "description": "Destinataire de la facture"},
        "invoice_number": {"type": "string"},
        "date": {"type": "string", "description": "Date de facture AAAA-MM-JJ"},
        "currency": {"type": "string"},
        "total_ht": {"type": "string", "description": "Montant HT, nombre décimal avec un point, ex. 100.00"},
        "total_vat": {"type": "string"},
        "total_ttc": {"type": "string"},
        "vat_lines": {"type": "array", "items": {"type": "object", "properties": {
            "rate": {"type": "string"}, "base": {"type": "string"}, "vat": {"type": "string"}},
            "required": ["rate", "base", "vat"], "additionalProperties": False}},
        "vat_regime": {"type": "string", "enum": ["fr", "sans_tva", "autoliquidation_ue", "autoliquidation_hors_ue"]},
        "nature": {"type": "string", "enum": ["services", "biens"]},
        "description": {"type": "string", "description": "Nature de la dépense en quelques mots"},
        "category_code": {"type": "string"},
        "is_asset_candidate": {"type": "boolean"},
        "anomalies": {"type": "array", "items": {"type": "string"}},
        "confidence": {"type": "number", "description": "Confiance globale entre 0 et 1"},
    },
    "required": ["document_type", "supplier_name", "supplier_country", "supplier_vat_number", "customer_name", "invoice_number",
                 "date", "currency", "total_ht", "total_vat", "total_ttc", "vat_lines", "vat_regime", "nature", "description",
                 "category_code", "is_asset_candidate", "anomalies", "confidence"],
    "additionalProperties": False,
}


def ai_available(conn) -> bool:
    return bool(config.load_secrets().get("anthropic_api_key")) and db.get_setting(conn, "ai").get("enabled", False)


def _client():
    import anthropic
    return anthropic.Anthropic(api_key=config.load_secrets()["anthropic_api_key"])


def _model(conn):
    return db.get_setting(conn, "ai").get("model") or "claude-opus-5-5"


def _call(conn, **kwargs):
    """Appel Messages API avec repli automatique côté serveur en cas de refus."""
    import anthropic
    client = _client()
    try:
        resp = client.beta.messages.create(model=_model(conn), betas=["server-side-fallback-2026-07-01"],
                                           fallbacks="default", **kwargs)
    except anthropic.BadRequestError as e:
        raise RuntimeError(f"Requête refusée par l'API : {e.message}") from e
    except anthropic.AuthenticationError as e:
        raise RuntimeError("Clé API Anthropic invalide.") from e
    except anthropic.RateLimitError as e:
        raise RuntimeError("Limite de requêtes atteinte, réessayez dans une minute.") from e
    except anthropic.APIStatusError as e:
        raise RuntimeError(f"Erreur du service IA ({e.status_code}).") from e
    except anthropic.APIConnectionError as e:
        raise RuntimeError("Pas de connexion Internet : l'IA est indisponible, la saisie manuelle reste possible.") from e
    if resp.stop_reason == "refusal":
        raise RuntimeError("L'IA a refusé de traiter ce document.")
    return resp


# ================================================================= Factur-X
NS = {"rsm": "urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100",
      "ram": "urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100",
      "udt": "urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100"}


def facturx_xml(content: bytes) -> bytes | None:
    try:
        from pypdf import PdfReader
        reader = PdfReader(io.BytesIO(content))
        for name, data in (reader.attachments or {}).items():
            if name.lower().endswith(".xml") and any(k in name.lower() for k in ("factur", "zugferd", "xrechnung", "cii")):
                return data[0] if isinstance(data, list) else data
    except Exception:
        return None
    return None


def parse_cii(xml: bytes) -> dict:
    root = ET.fromstring(xml)
    t = lambda path: (root.find(path, NS).text.strip() if root.find(path, NS) is not None and root.find(path, NS).text else "")
    raw_date = t(".//rsm:ExchangedDocument/ram:IssueDateTime/udt:DateTimeString")
    d = f"{raw_date[:4]}-{raw_date[4:6]}-{raw_date[6:8]}" if len(raw_date) >= 8 else ""
    vat_lines = []
    for tt in root.findall(".//ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax", NS):
        g = lambda p: (tt.find(p, NS).text if tt.find(p, NS) is not None else "0")
        vat_lines.append({"rate": g("ram:RateApplicablePercent"), "base": g("ram:BasisAmount"), "vat": g("ram:CalculatedAmount")})
    type_code = t(".//rsm:ExchangedDocument/ram:TypeCode")
    country = t(".//ram:SellerTradeParty/ram:PostalTradeAddress/ram:CountryID")
    total_vat = t(".//ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TaxTotalAmount") or "0"
    return {"document_type": "avoir" if type_code == "381" else "facture", "supplier_name": t(".//ram:SellerTradeParty/ram:Name"),
            "supplier_country": country, "supplier_vat_number": t(".//ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID"),
            "customer_name": t(".//ram:BuyerTradeParty/ram:Name"), "invoice_number": t(".//rsm:ExchangedDocument/ram:ID"), "date": d,
            "currency": t(".//ram:InvoiceCurrencyCode") or "EUR",
            "total_ht": t(".//ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TaxBasisTotalAmount"),
            "total_vat": total_vat, "total_ttc": t(".//ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:GrandTotalAmount"),
            "vat_lines": vat_lines,
            "vat_regime": "fr" if (country in ("FR", "") and float(total_vat or 0) != 0) else ("sans_tva" if country in ("FR", "") else "autoliquidation_ue"),
            "nature": "services", "description": t(".//ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:Name"),
            "category_code": "", "is_asset_candidate": False, "anomalies": [], "confidence": 1.0, "source": "facturx"}


# ================================================================= Claude
def extract_with_claude(conn, content: bytes, mime: str) -> dict:
    cats = db.q(conn, "SELECT code, name, help FROM categories WHERE active=1")
    company = db.get_setting(conn, "company")
    b64 = base64.standard_b64encode(content).decode()
    if mime == "application/pdf":
        block = {"type": "document", "source": {"type": "base64", "media_type": "application/pdf", "data": b64}}
    elif mime in ("image/jpeg", "image/png", "image/webp", "image/gif"):
        block = {"type": "image", "source": {"type": "base64", "media_type": mime, "data": b64}}
    else:
        raise RuntimeError("Format non pris en charge (PDF, JPG, PNG).")
    prompt = (
        f"Tu lis un justificatif comptable reçu par la société {company.get('name')} ({company.get('legal_form')}, France). "
        "Extrais les informations demandées en recopiant exactement les montants imprimés (nombres décimaux avec un point). "
        "Si une information n'apparaît pas, laisse une chaîne vide ; n'invente rien. "
        "vat_regime : « fr » si la facture porte de la TVA française ; « autoliquidation_ue » pour un fournisseur établi dans un "
        "autre pays de l'UE qui facture sans TVA (mention autoliquidation / reverse charge) ; « autoliquidation_hors_ue » pour un "
        "fournisseur hors UE qui facture sans TVA ; « sans_tva » pour une opération exonérée ou un ticket sans TVA. "
        "Choisis category_code parmi : " + "; ".join(f"{c['code']} = {c['name']}" for c in cats) + ". "
        "is_asset_candidate : vrai pour un bien durable de plus de 500 € HT (ordinateur, mobilier). "
        "anomalies : signale en français ce qui pose problème (destinataire autre que la société, TVA incohérente, document "
        "illisible, ticket sans TVA détaillée, facture étrangère en devise, dépense d'apparence personnelle...). "
        "confidence : ta confiance globale dans l'extraction."
    )
    resp = _call(conn, max_tokens=4000, output_config={"effort": "medium", "format": {"type": "json_schema", "schema": EXTRACTION_SCHEMA}},
                 messages=[{"role": "user", "content": [block, {"type": "text", "text": prompt}]}])
    text = next(b.text for b in resp.content if b.type == "text")
    data = json.loads(text)
    data["source"] = "ia"
    return data


# ================================================================= post-traitement déterministe
def _c(v):
    try:
        return to_cents(v) if v not in (None, "") else None
    except ValueError:
        return None


def normalize_extraction(conn, data: dict) -> dict:
    """Convertit en centimes et applique des contrôles déterministes."""
    out = dict(data)
    ht, tva, ttc = _c(data.get("total_ht")), _c(data.get("total_vat")), _c(data.get("total_ttc"))
    anomalies = list(data.get("anomalies") or [])
    if ttc is not None and ht is not None and tva is None:
        tva = ttc - ht
    if ttc is not None and tva is not None and ht is None:
        ht = ttc - tva
    if ht is not None and tva is not None and ttc is None:
        ttc = ht + tva
    if None not in (ht, tva, ttc) and ht + tva != ttc:
        anomalies.append(f"HT + TVA ({(ht + tva)/100:.2f}) ≠ TTC ({ttc/100:.2f}).")
    rates = [l["rate"].replace(",", ".").rstrip("%").strip() for l in data.get("vat_lines") or [] if l.get("rate")]
    rate = rates[0] if len(set(rates)) == 1 else ("" if not rates else "multi")
    if rate and rate != "multi" and ht and tva is not None:
        expected = vat_from_ht(ht, rate)
        if abs(expected - tva) > max(2, ht // 500):
            anomalies.append(f"TVA {tva/100:.2f} € incohérente avec le taux {rate} % (attendu {expected/100:.2f} €).")
    if rate == "multi":
        anomalies.append("Plusieurs taux de TVA : vérifiez la ventilation.")
    company = db.get_setting(conn, "company")
    cust = data.get("customer_name") or ""
    if cust and services.name_similarity(cust, company.get("name") or "") < 0.5:
        anomalies.append(f"La facture est adressée à « {cust} » et non à la société : TVA non récupérable sans facture au nom de la société.")
    if (data.get("currency") or "EUR").upper() not in ("EUR", "€", ""):
        anomalies.append(f"Facture en {data.get('currency')} : vérifiez le montant débité en euros.")
    cat = db.q1(conn, "SELECT * FROM categories WHERE code=?", (data.get("category_code") or "",))
    if data.get("is_asset_candidate") and ht and ht >= 50000:
        cat = db.q1(conn, "SELECT * FROM categories WHERE code='immobilisation'") or cat
    out.update({"ht_cents": ht, "vat_cents": tva, "ttc_cents": ttc, "vat_rate": rate if rate not in ("", "multi") else ("20" if tva else "0"),
                "category_id": cat["id"] if cat else None, "category_name": cat["name"] if cat else None, "anomalies": anomalies})
    return out


def find_transaction_match(conn, ex: dict) -> list[dict]:
    if not ex.get("ttc_cents"):
        return []
    sign = 1 if ex.get("document_type") == "avoir" else -1
    cands = []
    d0 = ex.get("date") or date.today().isoformat()
    for t in db.q(conn, "SELECT * FROM bank_transactions WHERE amount_cents=? AND date BETWEEN date(?, '-15 day') AND date(?, '+45 day')",
                  (sign * ex["ttc_cents"], d0, d0)):
        # déjà rattachée à une dépense avec justificatif ?
        linked = db.q1(conn, "SELECT x.id, x.document_id FROM allocations a JOIN expenses x ON x.id=a.target_id WHERE a.target_type='depense' AND a.bank_transaction_id=?", (t["id"],))
        if linked and linked["document_id"]:
            continue
        days = abs((datetime.fromisoformat(t["date"]) - datetime.fromisoformat(d0)).days) if re.match(r"\d{4}-\d\d-\d\d", d0) else 30
        score = 0.5 + 0.35 * services.name_similarity(t["counterparty"] or t["label"] or "", ex.get("supplier_name") or "") + (0.15 if days <= 3 else 0.05 if days <= 10 else 0)
        cands.append({"transaction": t, "score": round(min(score, 1.0), 2), "expense_id": linked["id"] if linked else None})
    cands.sort(key=lambda c: -c["score"])
    return cands


def process_document(conn, doc_id: int) -> dict:
    """Lit un justificatif, propose la dépense et la rapproche si certain."""
    doc = db.q1(conn, "SELECT * FROM documents WHERE id=?", (doc_id,))
    content = services.document_path(doc).read_bytes()
    raw = None
    if doc["mime"] == "application/pdf":
        xml = facturx_xml(content)
        if xml:
            try:
                raw = parse_cii(xml)
            except ET.ParseError:
                raw = None
    if raw is None:
        if not ai_available(conn):
            conn.execute("UPDATE documents SET ai_status='manuel' WHERE id=?", (doc_id,))
            return {"status": "manuel", "message": "IA non configurée : complétez la dépense manuellement.", "document_id": doc_id}
        raw = extract_with_claude(conn, content, doc["mime"])
    ex = normalize_extraction(conn, raw)
    matches = find_transaction_match(conn, ex)
    conn.execute("UPDATE documents SET ai_status='extrait', ai_data=?, doc_date=?, party_name=?, amount_cents=? WHERE id=?",
                 (json.dumps(ex, ensure_ascii=False), ex.get("date") or None, ex.get("supplier_name"), ex.get("ttc_cents"), doc_id))
    threshold = db.get_setting(conn, "ai").get("auto_link_threshold", 0.9)
    certain = (matches and matches[0]["score"] >= threshold and (len(matches) == 1 or matches[1]["score"] < matches[0]["score"] - 0.2)
               and (ex.get("confidence") or 0) >= threshold and not ex["anomalies"] and ex.get("document_type") in ("facture", "ticket", "note_de_frais"))
    result = {"status": "a_confirmer", "extraction": ex, "matches": matches[:5], "document_id": doc_id}
    if certain:
        m = matches[0]
        if m["expense_id"]:
            services.save_expense(conn, {"document_id": doc_id, "invoice_number": ex.get("invoice_number"), "status": "propose_ia"},
                                  m["expense_id"], "Justificatif rattaché automatiquement")
            result.update(status="rattache", expense_id=m["expense_id"])
        else:
            r = services.classify_transaction(conn, m["transaction"]["id"], expense_payload(ex, doc_id), "Rapprochement automatique justificatif")
            conn.execute("UPDATE expenses SET status='propose_ia', source='ia' WHERE id=?", (r["id"],))
            result.update(status="rattache", expense_id=r["id"])
        result["transaction_id"] = m["transaction"]["id"]
    return result


def expense_payload(ex: dict, doc_id: int) -> dict:
    p = {"type": "depense_nouvelle", "supplier_name": ex.get("supplier_name"), "label": ex.get("description") or ex.get("supplier_name"),
         "category_id": ex.get("category_id"), "vat_regime": ex.get("vat_regime") or "fr", "vat_rate": ex.get("vat_rate"),
         "nature": ex.get("nature") or "services", "document_id": doc_id, "invoice_number": ex.get("invoice_number"),
         "comment": "; ".join(ex.get("anomalies") or []) or None}
    if ex.get("vat_cents") is not None and (ex.get("vat_regime") or "fr") == "fr":
        p["vat_cents"] = ex["vat_cents"]
    return p


# ================================================================= assistant
def _tools_impl(conn):
    from . import closing, reports

    def fy():
        return closing.fiscal_year(conn)

    def month_range(month: str | None):
        from .checks import month_bounds
        m = month or date.today().strftime("%Y-%m")
        return month_bounds(m)

    def depenses(month: str | None = None, category: str | None = None):
        s, e = month_range(month)
        rows = db.q(conn, "SELECT x.date, x.supplier_name, x.label, x.ht_cents, x.vat_cents, x.ttc_cents, c.name categorie FROM expenses x"
                          " LEFT JOIN categories c ON c.id=x.category_id WHERE x.date BETWEEN ? AND ? AND IFNULL(c.rule,'')<>'personnel'", (s, e))
        if category:
            rows = [r for r in rows if category.lower() in (r["categorie"] or "").lower()]
        return {"periode": [s, e], "nombre": len(rows), "total_ttc_cents": sum(r["ttc_cents"] for r in rows),
                "total_ht_cents": sum(r["ht_cents"] for r in rows), "detail": rows[:50]}

    def associe():
        a = services.associate_summary(conn)
        return {k: v for k, v in a.items() if k not in ("mouvements",)}

    def tva(start: str | None = None, end: str | None = None):
        if start and end:
            d = vat.compute(conn, start, end)
            return {"periode": [start, end], "tva_brute_cents": d["tva_brute_cents"], "tva_deductible_cents": d["tva_deductible_cents"],
                    "a_payer_cents": d["a_payer_cents"], "credit_cents": d["credit_cents"]}
        est = vat.current_estimate(conn)
        paid = conn.execute("SELECT COALESCE(SUM(a.amount_cents),0) FROM allocations a WHERE a.target_type='tva'").fetchone()[0]
        return {"periode_en_cours": est, "tva_reglee_au_tresor_cents": -paid}

    def resultat():
        f = fy()
        inc = reports.income_statement(conn, f["start_date"], f["end_date"])
        return {"exercice": [f["start_date"], f["end_date"]], "produits_cents": inc["total_produits"], "charges_cents": inc["total_charges"],
                "resultat_cents": inc["resultat_net"], "chiffre_affaires_cents": inc["chiffre_affaires"],
                "is_estime_cents": tax.estimate(conn, f)["is_estime_cents"]}

    def a_faire():
        f = fy()
        from .checks import period_checks
        issues = period_checks(conn, f["start_date"], f["end_date"])
        return {"nombre": len(issues), "points": [{"niveau": i["niveau"], "message": i["message"]} for i in issues[:40]]}

    def tresorerie():
        from . import ledger
        return {"solde_comptable_banque_cents": ledger.account_balance(conn, "512"), "qonto": db.get_setting(conn, "qonto").get("balance_cents")}

    return {"depenses": depenses, "compte_courant_associe": associe, "tva": tva, "resultat": resultat, "points_a_traiter": a_faire,
            "tresorerie": tresorerie}


TOOLS = [
    {"name": "depenses", "description": "Dépenses professionnelles d'un mois (AAAA-MM), éventuellement filtrées par catégorie. Montants en centimes.",
     "input_schema": {"type": "object", "properties": {"month": {"type": "string"}, "category": {"type": "string"}}}},
    {"name": "compte_courant_associe", "description": "Apports, remboursements et solde du compte courant d'associé (centimes). Solde positif = la société doit à l'associé.",
     "input_schema": {"type": "object", "properties": {}}},
    {"name": "tva", "description": "TVA d'une période (start, end au format AAAA-MM-JJ) ou de la période en cours et TVA déjà réglée.",
     "input_schema": {"type": "object", "properties": {"start": {"type": "string"}, "end": {"type": "string"}}}},
    {"name": "resultat", "description": "Résultat comptable provisoire de l'exercice et IS estimé (centimes).",
     "input_schema": {"type": "object", "properties": {}}},
    {"name": "points_a_traiter", "description": "Justificatifs manquants, opérations à catégoriser, anomalies de l'exercice.",
     "input_schema": {"type": "object", "properties": {}}},
    {"name": "tresorerie", "description": "Solde bancaire.", "input_schema": {"type": "object", "properties": {}}},
]

SYSTEM = ("Tu es l'assistant comptable de la SASU {name}. Réponds en français, simplement, pour un dirigeant non comptable. "
          "Utilise uniquement les données renvoyées par les outils (montants en centimes à convertir en euros). "
          "Explique brièvement le calcul et sa source. Si une donnée manque, dis-le : n'invente jamais de chiffre. "
          "Tu ne peux rien modifier : tu peux proposer une écriture ou une catégorie, l'utilisateur la valide dans l'application. "
          "Aujourd'hui : {today}.")


def ask(conn, question: str) -> dict:
    tools = _tools_impl(conn)
    if not ai_available(conn):
        return local_answer(conn, question, tools)
    company = db.get_setting(conn, "company")
    messages = [{"role": "user", "content": question}]
    used = []
    for _ in range(8):
        resp = _call(conn, max_tokens=4000, system=SYSTEM.format(name=company.get("name"), today=date.today().isoformat()),
                     tools=TOOLS, messages=messages, output_config={"effort": "medium"})
        if resp.stop_reason != "tool_use":
            text = "\n".join(b.text for b in resp.content if b.type == "text")
            return {"answer": text, "tools": used, "mode": "ia"}
        messages.append({"role": "assistant", "content": resp.content})
        results = []
        for b in resp.content:
            if b.type == "tool_use":
                try:
                    out = tools[b.name](**(b.input or {}))
                    results.append({"type": "tool_result", "tool_use_id": b.id, "content": json.dumps(out, ensure_ascii=False, default=str)})
                except Exception as e:  # erreur renvoyée au modèle
                    results.append({"type": "tool_result", "tool_use_id": b.id, "content": str(e), "is_error": True})
                used.append(b.name)
        messages.append({"role": "user", "content": results})
    return {"answer": "Je n'ai pas pu terminer l'analyse.", "tools": used, "mode": "ia"}


def local_answer(conn, question: str, tools) -> dict:
    """Réponses sans IA aux questions courantes (calculs déterministes)."""
    from .money import fmt
    q = services.norm_name(question)
    if any(k in q for k in ("INJECTE", "APPORT", "DOIT", "COMPTE COURANT", "REMBOURS")):
        a = tools["compte_courant_associe"]()
        txt = (f"Vous avez apporté {fmt(a['apports_cents'])} par virement et avancé {fmt(a['avances_frais_cents'])} de frais payés personnellement, "
               f"soit {fmt(a['total_injecte_cents'])}. La société vous a remboursé {fmt(a['remboursements_cents'])}. "
               f"Solde du compte courant : {fmt(abs(a['solde_cents']))}, {a['sens']}. (Source : compte 455 du grand livre.)")
    elif "TVA" in q:
        t = tools["tva"]()
        p = t["periode_en_cours"]
        txt = (f"Période {p.get('periode')} : TVA estimée à payer {fmt(p.get('a_payer_cents', 0))}"
               + (f", crédit {fmt(p['credit_cents'])}" if p.get("credit_cents") else "") +
               f". TVA déjà réglée au Trésor : {fmt(t['tva_reglee_au_tresor_cents'])}. (Source : comptes 4457, 4456 et 4452.)")
    elif any(k in q for k in ("DEPENSE", "DEPENSE")):
        d = tools["depenses"]()
        txt = f"Dépenses du {d['periode'][0]} au {d['periode'][1]} : {d['nombre']} dépense(s), {fmt(d['total_ttc_cents'])} TTC ({fmt(d['total_ht_cents'])} HT)."
    elif any(k in q for k in ("RESULTAT", "BENEFICE", "PERTE")):
        r = tools["resultat"]()
        txt = (f"Résultat provisoire de l'exercice : {fmt(r['resultat_cents'])} (produits {fmt(r['produits_cents'])}, charges {fmt(r['charges_cents'])}). "
               f"IS estimé : {fmt(r['is_estime_cents'])}.")
    elif any(k in q for k in ("MANQU", "CATEGORIS", "A FAIRE", "VERIFIER", "JUSTIFICATIF")):
        a = tools["points_a_traiter"]()
        txt = f"{a['nombre']} point(s) à traiter :\n" + "\n".join(f"• {p['message']}" for p in a["points"][:15])
    elif any(k in q for k in ("SOLDE", "TRESORERIE", "BANQUE")):
        t = tools["tresorerie"]()
        txt = f"Solde bancaire comptable : {fmt(t['solde_comptable_banque_cents'])}."
    else:
        txt = ("Sans IA configurée, je réponds aux questions sur : apports et compte courant, TVA, dépenses du mois, résultat, "
               "points à traiter, solde bancaire.")
    return {"answer": txt, "tools": [], "mode": "local"}

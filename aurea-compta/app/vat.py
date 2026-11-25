"""Moteur TVA : périodes, calcul, rubriques CA3 / CA12, validation.

Le calcul part des comptes de TVA du grand livre (445710, 445200, 445620,
445660, 445662) sur la période : ces comptes ne sont mouvementés qu'à la date
d'exigibilité (facture pour les biens, encaissement pour les services), donc
la déclaration respecte l'exigibilité et chaque montant renvoie à ses pièces.

Les numéros de lignes reprennent le formulaire 3310-CA3-SD (et 3517-S-SD pour
la CA12). Vérifiez la notice de l'année avant report : l'administration fait
évoluer les formulaires (les règles sont versionnées dans rules.py).
"""
import json
from datetime import date, timedelta

from . import checks, db, ledger, rules
from .ledger import AccountingError, C, D
from .money import fmt

RATE_LINES = {"20": ("08", "Taux normal 20 %"), "10": ("9B", "Taux réduit 10 %"),
              "5.5": ("09", "Taux réduit 5,5 %"), "2.1": ("P2", "Taux particulier 2,1 %")}


def _rate(code: str) -> str:
    r = "".join(ch for ch in (code or "") if ch.isdigit() or ch == ".")
    return r or "0"


def periods(conn, year: int) -> list[dict]:
    tax = db.get_setting(conn, "tax")
    per = tax.get("vat_periodicity", "mensuelle")
    if tax.get("vat_regime") == "reel_simplifie":
        per = "annuelle"
    out = []
    if per == "annuelle":
        fy = db.q1(conn, "SELECT * FROM fiscal_years WHERE substr(end_date,1,4)=?", (str(year),))
        s, e = (fy["start_date"], fy["end_date"]) if fy else (f"{year}-01-01", f"{year}-12-31")
        out.append({"start": s, "end": e, "label": f"Exercice {s[:4]}" if s[:4] == e[:4] else f"Exercice {s} au {e}", "form": "CA12"})
    else:
        step = 1 if per == "mensuelle" else 3
        for m in range(1, 13, step):
            s = date(year, m, 1)
            e = date(year + ((m + step - 1) // 12 if m + step > 12 else 0), ((m + step - 1) % 12) + 1, 1) - timedelta(days=1)
            label = s.strftime("%m/%Y") if step == 1 else f"T{(m - 1)//3 + 1} {year}"
            out.append({"start": s.isoformat(), "end": e.isoformat(), "label": label, "form": "CA3"})
    for p in out:
        r = db.q1(conn, "SELECT id,status FROM vat_returns WHERE period_start=? AND period_end=?", (p["start"], p["end"]))
        p["return_id"], p["status"] = (r["id"], r["status"]) if r else (None, None)
    return out


def _lines(conn, start, end, accounts, vat_prefix=None):
    sql = ("SELECT l.*, e.date, e.journal, e.number, e.label entry_label, e.piece_ref, e.source_type, e.source_id, e.status, e.document_id"
           " FROM entry_lines l JOIN entries e ON e.id=l.entry_id WHERE e.date BETWEEN ? AND ?"
           " AND IFNULL(e.source_type,'') NOT LIKE 'tva_declaration%' AND l.account IN (" + ",".join("?" * len(accounts)) + ")")
    rows = db.q(conn, sql, [start, end] + list(accounts))
    if vat_prefix:
        rows = [r for r in rows if (r["vat_code"] or "").startswith(vat_prefix)]
    return rows


def compute(conn, start: str, end: str) -> dict:
    """Calcule la TVA de la période avec le détail de chaque montant."""
    collected = _lines(conn, start, end, ["445710"])
    autoliq = _lines(conn, start, end, ["445200"])
    ded_assets = _lines(conn, start, end, ["445620"])
    ded_other = _lines(conn, start, end, ["445660", "445662"])
    base_rows = db.q(conn, "SELECT l.*, e.date, e.label entry_label, e.piece_ref, e.source_type, e.source_id, e.document_id FROM entry_lines l JOIN entries e ON e.id=l.entry_id"
                           " WHERE e.date BETWEEN ? AND ? AND l.vat_code IN ('E1','E2') AND IFNULL(e.source_type,'') NOT LIKE 'tva_declaration%'", (start, end))

    def piece(r):
        tag = r.get("lettrage") or ""
        if tag.startswith("V") and tag[1:].isdigit():
            inv = db.q1(conn, "SELECT number FROM sales_invoices WHERE id=?", (int(tag[1:]),))
            return inv["number"] if inv else r["piece_ref"]
        if tag.startswith("D") and tag[1:].isdigit():
            x = db.q1(conn, "SELECT invoice_number, supplier_name FROM expenses WHERE id=?", (int(tag[1:]),))
            return (x["invoice_number"] or x["supplier_name"]) if x else r["piece_ref"]
        return r["piece_ref"]

    def detail(rows, sign):
        return [{"date": r["date"], "libelle": r["entry_label"], "piece": piece(r), "journal": r["journal"] if "journal" in r else None,
                 "source_type": r["source_type"], "source_id": r["source_id"], "document_id": r["document_id"],
                 "base_cents": (r["vat_base_cents"] or 0) * (1 if sign > 0 else 1), "taxe_cents": sign * (r["credit_cents"] - r["debit_cents"]),
                 "provisoire": r.get("status") == "brouillon"} for r in rows]

    lines = {}

    def put(code, label, base=None, tax=None, det=None):
        lines[code] = {"code": code, "libelle": label, "base_cents": base, "taxe_cents": tax, "detail": det or []}

    # Opérations imposables (section A)
    taxable_base = sum((r["vat_base_cents"] or 0) for r in collected)
    put("A1", "Ventes, prestations de services", taxable_base, None, detail(collected, 1))
    al_ue = [r for r in autoliq if (r["vat_code"] or "").startswith("AL_UE")]
    al_hue = [r for r in autoliq if (r["vat_code"] or "").startswith("AL_HUE")]
    put("A3", "Achats de prestations de services intracommunautaires (art. 283-2 CGI)", sum(r["vat_base_cents"] or 0 for r in al_ue), None, detail(al_ue, 1))
    put("B4", "Achats de biens ou prestations auprès d'un assujetti non établi en France (hors UE)", sum(r["vat_base_cents"] or 0 for r in al_hue), None, detail(al_hue, 1))
    e1 = [r for r in base_rows if r["vat_code"] == "E1"]
    e2 = [r for r in base_rows if r["vat_code"] == "E2"]
    put("E1", "Exportations hors UE", sum(r["vat_base_cents"] or 0 for r in e1), None, detail(e1, 1))
    put("E2", "Autres opérations non imposables (dont prestations intracommunautaires B2B)", sum(r["vat_base_cents"] or 0 for r in e2), None, detail(e2, 1))
    # TVA brute par taux (collectée + autoliquidée)
    by_rate = {}
    for r in collected + autoliq:
        rt = _rate(r["vat_code"])
        g = by_rate.setdefault(rt, {"base": 0, "tax": 0, "rows": []})
        g["base"] += r["vat_base_cents"] or 0
        g["tax"] += r["credit_cents"] - r["debit_cents"]
        g["rows"].append(r)
    brute = 0
    for rt, g in sorted(by_rate.items(), key=lambda x: -float(x[0])):
        code, label = RATE_LINES.get(rt, (f"T{rt}", f"Taux {rt} %"))
        put(code, label, g["base"], g["tax"], detail(g["rows"], 1))
        brute += g["tax"]
    put("16", "Total de la TVA brute due", None, brute)
    put("17", "Dont TVA sur acquisitions intracommunautaires (biens)", None, 0)
    t19 = sum(r["debit_cents"] - r["credit_cents"] for r in ded_assets)
    t20 = sum(r["debit_cents"] - r["credit_cents"] for r in ded_other)
    put("19", "TVA déductible : biens constituant des immobilisations", None, t19, detail(ded_assets, -1))
    for d in lines["19"]["detail"]:
        d["taxe_cents"] = -d["taxe_cents"]
    put("20", "TVA déductible : autres biens et services", None, t20, detail(ded_other, -1))
    for d in lines["20"]["detail"]:
        d["taxe_cents"] = -d["taxe_cents"]
    adj = db.q1(conn, "SELECT data FROM vat_returns WHERE period_start=? AND period_end=?", (start, end))
    t21 = 0
    if adj:
        t21 = json.loads(adj["data"]).get("ajustements", {}).get("21", 0)
    put("21", "Autre TVA à déduire (régularisations)", None, t21)
    # Report du crédit de la déclaration précédente
    prev = db.q1(conn, "SELECT * FROM vat_returns WHERE period_end<? AND status IN ('validee','deposee') ORDER BY period_end DESC LIMIT 1", (start,))
    t22 = 0
    if prev:
        pd = json.loads(prev["data"])
        t22 = pd.get("lignes", {}).get("27", {}).get("taxe_cents") or 0
    put("22", "Report du crédit apparaissant ligne 27 de la précédente déclaration", None, t22)
    t23 = t19 + t20 + t21 + t22
    put("23", "Total TVA déductible", None, t23)
    if brute >= t23:
        put("28", "TVA nette due (ligne 16 - ligne 23)", None, brute - t23)
        put("25", "Crédit de TVA (ligne 23 - ligne 16)", None, 0)
        put("27", "Crédit à reporter", None, 0)
    else:
        put("28", "TVA nette due", None, 0)
        put("25", "Crédit de TVA (ligne 23 - ligne 16)", None, t23 - brute)
        put("27", "Crédit à reporter (sauf demande de remboursement ligne 26)", None, t23 - brute)
    put("32", "Total à payer", None, lines["28"]["taxe_cents"])
    # TVA non récupérable (information)
    nonrec = db.q(conn, "SELECT x.id, x.date, x.supplier_name, x.label, x.vat_cents, x.vat_recovery FROM expenses x"
                        " WHERE x.date BETWEEN ? AND ? AND x.vat_regime='fr' AND x.vat_cents>0 AND x.vat_recovery<100", (start, end))
    nonrec_total = sum(n["vat_cents"] - (n["vat_cents"] * n["vat_recovery"] // 100) for n in nonrec)
    pending_out = ledger.account_balance(conn, "445870", end)
    pending_in = ledger.account_balance(conn, "445860", end)
    drafts = sum(1 for k in ("A1", "19", "20") for d in lines[k]["detail"] if d["provisoire"])
    return {"start": start, "end": end, "lignes": lines, "tva_brute_cents": brute, "tva_deductible_cents": t23,
            "a_payer_cents": lines["28"]["taxe_cents"], "credit_cents": lines["25"]["taxe_cents"],
            "non_recuperable_cents": nonrec_total, "non_recuperable": nonrec,
            "tva_collectee_en_attente_cents": -pending_out, "tva_deductible_en_attente_cents": pending_in,
            "lignes_provisoires": drafts}


def ca12_view(conn, data: dict, year_end: str) -> dict:
    """Présentation CA12 (régime simplifié) : récapitulatif annuel et acomptes."""
    l = data["lignes"]
    acomptes = ledger.account_balance(conn, "445810", year_end)
    net = data["tva_brute_cents"] - data["tva_deductible_cents"]
    base_next = max(net + l["19"]["taxe_cents"], 0)  # base des acomptes : TVA due hors TVA sur immobilisations
    seuil = rules.get_rule("ca12_seuil_acomptes_cents", year_end)
    jul = rules.get_rule("ca12_acompte_juillet_pct", year_end)
    dec = rules.get_rule("ca12_acompte_decembre_pct", year_end)
    from .money import pct_of
    return {
        "rubriques": [
            {"libelle": "Opérations imposables (base HT)", "montant_cents": l["A1"]["base_cents"]},
            {"libelle": "TVA brute due", "montant_cents": data["tva_brute_cents"]},
            {"libelle": "TVA déductible sur immobilisations", "montant_cents": l["19"]["taxe_cents"]},
            {"libelle": "TVA déductible sur autres biens et services", "montant_cents": l["20"]["taxe_cents"]},
            {"libelle": "Total TVA déductible", "montant_cents": data["tva_deductible_cents"]},
            {"libelle": "Acomptes versés dans l'année", "montant_cents": acomptes},
            {"libelle": "Solde à payer" if net - acomptes >= 0 else "Crédit de TVA", "montant_cents": abs(net - acomptes)},
        ],
        "acomptes_annee_suivante": (
            [] if base_next <= seuil else
            [{"echeance": "juillet", "pct": jul, "montant_cents": pct_of(base_next, jul)},
             {"echeance": "décembre", "pct": dec, "montant_cents": pct_of(base_next, dec)}]),
        "note": "Déclaration annuelle CA12 (3517-S-SD) à déposer au plus tard le 2e jour ouvré suivant le 1er mai (exercice civil). "
                "Acomptes semestriels de 55 % (juillet) et 40 % (décembre) de la TVA due de l'année précédente, si celle ci dépasse 1 000 €.",
    }


def prepare(conn, start, end) -> dict:
    """Bouton « Préparer ma déclaration » : contrôles + calcul + enregistrement."""
    data = compute(conn, start, end)
    issues = checks.period_checks(conn, start, end)
    blocking = [i for i in issues if i["niveau"] == "bloquant"]
    tax = db.get_setting(conn, "tax")
    form = "CA12" if tax.get("vat_regime") == "reel_simplifie" else "CA3"
    payload = {"lignes": {k: {kk: vv for kk, vv in v.items() if kk != "detail"} for k, v in data["lignes"].items()},
               "a_payer_cents": data["a_payer_cents"], "credit_cents": data["credit_cents"],
               "anomalies": issues, "definitive": not blocking and data["lignes_provisoires"] == 0}
    if form == "CA12":
        payload["ca12"] = ca12_view(conn, data, end)
    with db.tx(conn):
        existing = db.q1(conn, "SELECT * FROM vat_returns WHERE period_start=? AND period_end=?", (start, end))
        if existing and existing["status"] != "preparee":
            return {"return": existing, "data": data, "issues": issues, "form": form, "locked": True}
        if existing:
            old = json.loads(existing["data"])
            payload["ajustements"] = old.get("ajustements", {})
            conn.execute("UPDATE vat_returns SET data=?, form=?, created_at=? WHERE id=?",
                         (json.dumps(payload, ensure_ascii=False), form, db.now(), existing["id"]))
            rid = existing["id"]
        else:
            rid = conn.execute("INSERT INTO vat_returns(period_start,period_end,form,data,created_at) VALUES(?,?,?,?,?)",
                               (start, end, form, json.dumps(payload, ensure_ascii=False), db.now())).lastrowid
    return {"return": db.q1(conn, "SELECT * FROM vat_returns WHERE id=?", (rid,)), "data": data, "issues": issues,
            "form": form, "statut": "définitive" if payload["definitive"] else "provisoire (contrôles à terminer)"}


def validate(conn, return_id: int, force=False) -> dict:
    """Valide la déclaration : écritures de la période validées et TVA soldée vers 445510 / 445670."""
    r = db.q1(conn, "SELECT * FROM vat_returns WHERE id=?", (return_id,))
    if r["status"] != "preparee":
        raise AccountingError("Déclaration déjà validée.")
    res = prepare(conn, r["period_start"], r["period_end"])
    blocking = [i for i in res["issues"] if i["niveau"] == "bloquant"]
    if blocking and not force:
        raise AccountingError(f"{len(blocking)} anomalie(s) bloquante(s) à corriger avant validation.")
    data = res["data"]
    L = data["lignes"]
    with db.tx(conn):
        for e in db.q(conn, "SELECT id FROM entries WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date,id", (r["period_start"], r["period_end"])):
            ledger.validate_entry(conn, e["id"])
        coll = sum(x["credit_cents"] - x["debit_cents"] for x in _lines(conn, r["period_start"], r["period_end"], ["445710"]))
        auto = sum(x["credit_cents"] - x["debit_cents"] for x in _lines(conn, r["period_start"], r["period_end"], ["445200"]))
        ded = {acc: sum(x["debit_cents"] - x["credit_cents"] for x in _lines(conn, r["period_start"], r["period_end"], [acc]))
               for acc in ("445620", "445660", "445662")}
        lines = [D("445710", coll), D("445200", auto)] + [C(acc, v) for acc, v in ded.items()]
        if L["22"]["taxe_cents"]:
            lines.append(C("445670", L["22"]["taxe_cents"], label="Imputation du crédit antérieur"))
        if L["21"]["taxe_cents"]:
            lines.append(C("445800", L["21"]["taxe_cents"], label="Régularisation"))
        if data["a_payer_cents"]:
            lines.append(C("445510", data["a_payer_cents"], label="TVA à décaisser"))
        if data["credit_cents"]:
            lines.append(D("445670", data["credit_cents"], label="Crédit de TVA à reporter"))
        eid = ledger.create_entry(conn, "OD", r["period_end"], f"Déclaration de TVA {r['period_start']} au {r['period_end']}", lines,
                                  piece_ref=f"TVA{r['period_end'][:7]}", source_type="tva_declaration", source_id=return_id, status="valide")
        stored = json.loads(r["data"])
        stored.update({"lignes": {k: {kk: vv for kk, vv in v.items() if kk != "detail"} for k, v in L.items()},
                       "a_payer_cents": data["a_payer_cents"], "credit_cents": data["credit_cents"],
                       "validation_forcee": bool(blocking)})
        conn.execute("UPDATE vat_returns SET status='validee', entry_id=?, validated_at=?, data=? WHERE id=?",
                     (eid, db.now(), json.dumps(stored, ensure_ascii=False), return_id))
        db.audit(conn, "validation", "declaration_tva", return_id, None, {"a_payer": data["a_payer_cents"], "credit": data["credit_cents"]},
                 "Validation forcée malgré anomalies" if blocking else None)
    return {"ok": True, "entry_id": eid, "a_payer": fmt(data["a_payer_cents"]), "credit": fmt(data["credit_cents"])}


def mark_filed(conn, return_id: int) -> None:
    with db.tx(conn):
        conn.execute("UPDATE vat_returns SET status='deposee', filed_at=? WHERE id=? AND status='validee'", (db.now(), return_id))
        db.audit(conn, "depot", "declaration_tva", return_id, None, {"deposee_le": db.now()})


def set_adjustment(conn, return_id: int, line: str, cents: int) -> None:
    r = db.q1(conn, "SELECT * FROM vat_returns WHERE id=?", (return_id,))
    if r["status"] != "preparee":
        raise AccountingError("Déclaration validée : ajustement impossible.")
    d = json.loads(r["data"])
    d.setdefault("ajustements", {})[line] = cents
    conn.execute("UPDATE vat_returns SET data=? WHERE id=?", (json.dumps(d, ensure_ascii=False), return_id))
    db.audit(conn, "ajustement", "declaration_tva", return_id, None, {line: cents})


def current_estimate(conn) -> dict:
    """TVA estimée de la période en cours (pour la vue d'ensemble)."""
    today = date.today()
    ps = periods(conn, today.year)
    cur = next((p for p in ps if p["start"] <= today.isoformat() <= p["end"]), ps[0] if ps else None)
    if not cur:
        return {}
    d = compute(conn, cur["start"], cur["end"])
    return {"periode": cur["label"], "start": cur["start"], "end": cur["end"], "a_payer_cents": d["a_payer_cents"], "credit_cents": d["credit_cents"]}

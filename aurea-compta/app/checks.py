"""Contrôles : vérification du mois, avant déclaration de TVA, avant clôture.

Chaque anomalie : {"niveau": bloquant | a_verifier | info, "type", "message", "lien"}.
« bloquant » interdit de présenter une déclaration ou un bilan comme définitif.
"""
import json
from datetime import date, timedelta

from . import db, ledger, rules, services


def _d(s):
    return f"{s[8:10]}/{s[5:7]}/{s[:4]}" if s and len(s) >= 10 else (s or "")


def _e(c):
    from .money import fmt
    return fmt(c or 0)


def _issue(level, typ, message, link=None, amount=None):
    return {"niveau": level, "type": typ, "message": message, "lien": link, "montant_cents": amount}


def period_checks(conn, start: str, end: str) -> list[dict]:
    out = []
    # 1. Opérations bancaires non rapprochées
    for t in db.q(conn, "SELECT * FROM bank_transactions WHERE date BETWEEN ? AND ? AND status IN ('a_traiter','propose') ORDER BY date", (start, end)):
        out.append(_issue("bloquant", "operation_non_rapprochee",
                          f"Opération du {_d(t['date'])} « {t['counterparty'] or t['label']} » ({_e(t['amount_cents'])}) non catégorisée.",
                          f"#/banque?tx={t['id']}", t["amount_cents"]))
    # 2. Compte d'attente
    s471 = ledger.account_balance(conn, "471", end)
    if s471:
        out.append(_issue("bloquant", "compte_attente", f"Le compte d'attente 471 n'est pas soldé ({_e(s471)}).", "#/banque", s471))
    # 3. Dépenses
    for e in db.q(conn, "SELECT x.*, c.rule, c.name cat_name, c.is_asset FROM expenses x LEFT JOIN categories c ON c.id=x.category_id WHERE x.date BETWEEN ? AND ?", (start, end)):
        who = e["supplier_name"] or ""
        lab = e["label"] if services.norm_name(e["label"]) != services.norm_name(who) else ""
        name = f"{who} {lab}".strip() + f" du {_d(e['date'])}"
        if not e["document_id"] and e["rule"] != "personnel":
            out.append(_issue("bloquant" if e["vat_cents"] and e["vat_regime"] == "fr" and e["vat_recovery"] else "a_verifier",
                              "justificatif_manquant", f"Justificatif manquant : {name}.", f"#/depenses?id={e['id']}", e["ttc_cents"]))
        if not e["category_id"]:
            out.append(_issue("bloquant", "non_categorisee", f"Dépense non catégorisée : {name}.", f"#/depenses?id={e['id']}"))
        if e["status"] in ("propose_ia", "importe"):
            out.append(_issue("a_verifier", "proposition_ia", f"Données proposées par l'IA à confirmer : {name}.", f"#/depenses?id={e['id']}"))
        elif e["status"] == "a_valider":
            out.append(_issue("a_verifier", "depense_a_valider", f"Dépense à valider : {name}.", f"#/depenses?id={e['id']}"))
        cat = {"rule": e["rule"], "is_asset": e["is_asset"]} if e["category_id"] else None
        for w in services.fiscal_warnings(conn, e, cat):
            if "Justificatif" in w or "Catégorie" in w or "Sans facture" in w:
                continue
            lvl = "bloquant" if w.startswith("TVA incohérente") else "a_verifier"
            out.append(_issue(lvl, "traitement_fiscal", f"{name} : {w}", f"#/depenses?id={e['id']}"))
        if e["rule"] == "personnel":
            out.append(_issue("info", "depense_personnelle", f"Dépense personnelle imputée au compte courant : {name}.", f"#/depenses?id={e['id']}"))
        if e["payment_method"] == "qonto" and services.expense_paid(conn, e) == 0:
            out.append(_issue("a_verifier", "depense_sans_paiement", f"Dépense payée par Qonto mais non rapprochée d'une opération : {name}.", f"#/depenses?id={e['id']}"))
    # 4. Doublons de dépenses
    dup = db.q(conn, "SELECT a.id ida, b.id idb, a.supplier_name, a.ttc_cents, a.date FROM expenses a JOIN expenses b ON a.id<b.id"
                     " AND a.ttc_cents=b.ttc_cents AND abs(julianday(a.date)-julianday(b.date))<=3"
                     " AND IFNULL(a.supplier_name,'')=IFNULL(b.supplier_name,'') WHERE a.date BETWEEN ? AND ?", (start, end))
    for d in dup:
        out.append(_issue("a_verifier", "doublon", f"Doublon possible : {d['supplier_name']} {_e(d['ttc_cents'])} le {_d(d['date'])} (dépenses {d['ida']} et {d['idb']}).",
                          f"#/depenses?id={d['idb']}"))
    dupdoc = db.q(conn, "SELECT document_id, COUNT(*) n FROM expenses WHERE document_id IS NOT NULL AND date BETWEEN ? AND ? GROUP BY document_id HAVING n>1", (start, end))
    for d in dupdoc:
        out.append(_issue("a_verifier", "doublon", f"Le même justificatif est rattaché à {d['n']} dépenses.", "#/depenses"))
    # 5. Factures clients
    for inv in db.q(conn, "SELECT s.*, p.name client FROM sales_invoices s JOIN parties p ON p.id=s.client_id WHERE s.issue_date BETWEEN ? AND ?", (start, end)):
        if inv["vat_regime"] == "fr":
            lines = db.q(conn, "SELECT * FROM sales_invoice_lines WHERE invoice_id=?", (inv["id"],))
            for l in lines:
                if abs(services.vat_from_ht(l["ht_cents"], l["vat_rate"]) - l["vat_cents"]) > 1:
                    out.append(_issue("bloquant", "tva_incoherente", f"TVA incohérente sur la facture {inv['number']}.", f"#/recettes?id={inv['id']}"))
        if inv["vat_regime"] == "autoliquidation_ue":
            p = db.q1(conn, "SELECT vat_number FROM parties WHERE id=?", (inv["client_id"],))
            if not (p and p["vat_number"]):
                out.append(_issue("a_verifier", "mention_facture", f"Facture {inv['number']} en autoliquidation : numéro de TVA du client manquant.", f"#/recettes?id={inv['id']}"))
        if not inv["document_id"]:
            out.append(_issue("info", "pdf_facture", f"Facture {inv['number']} sans PDF archivé.", f"#/recettes?id={inv['id']}"))
    today = date.today().isoformat()
    for inv in db.q(conn, "SELECT s.*, p.name client FROM sales_invoices s JOIN parties p ON p.id=s.client_id WHERE s.kind='facture' AND s.status='emise' AND s.due_date<? AND s.issue_date<=?", (today, end)):
        st = services.invoice_status(conn, inv)
        if st["remaining_cents"] > 0:
            out.append(_issue("info", "facture_echue", f"Facture {inv['number']} ({inv['client']}) échue, reste dû {_e(st['remaining_cents'])}.", f"#/recettes?id={inv['id']}", st["remaining_cents"]))
    # 6. Apports non rapprochés
    for m in db.q(conn, "SELECT * FROM associate_movements WHERE bank_transaction_id IS NULL AND type IN ('apport','remboursement','capital_libere') AND date BETWEEN ? AND ?", (start, end)):
        out.append(_issue("a_verifier", "apport_non_rapproche", f"{m['type'].capitalize()} du {_d(m['date'])} ({_e(m['amount_cents'])}) non rapproché d'un virement Qonto.", "#/associe"))
    # 7. Écritures
    bal = ledger.check_global_balance(conn)
    if not bal["ok"]:
        out.append(_issue("bloquant", "desequilibre", f"{len(bal['unbalanced'])} écriture(s) déséquilibrée(s).", "#/comptabilite"))
    drafts = conn.execute("SELECT COUNT(*) FROM entries WHERE status='brouillon' AND date BETWEEN ? AND ?", (start, end)).fetchone()[0]
    if drafts:
        out.append(_issue("info", "brouillons", f"{drafts} écriture(s) provisoire(s) seront validées lors de la clôture de la période.", "#/comptabilite"))
    # 8. Rapprochement bancaire : solde comptable 512 vs solde Qonto
    rec = bank_reconciliation(conn, end)
    if rec["ecart_cents"] not in (None, 0):
        out.append(_issue("bloquant", "ecart_rapprochement", f"Écart de rapprochement bancaire au {_d(rec['date_reference'])} : {_e(rec['ecart_cents'])} (banque {_e(rec['solde_banque_cents'])}, comptabilité {_e(rec['solde_comptable_cents'])}).", "#/banque"))
    # 9. Associé débiteur
    s455 = ledger.account_balance(conn, "455", end)
    if s455 > 0:
        out.append(_issue("bloquant", "compte_courant_debiteur", f"Compte courant d'associé débiteur de {_e(s455)} : situation interdite pour le président d'une SAS, à régulariser.", "#/associe", s455))
    order = {"bloquant": 0, "a_verifier": 1, "info": 2}
    out.sort(key=lambda i: order[i["niveau"]])
    return out


def bank_reconciliation(conn, end: str) -> dict:
    """Compare le solde du compte 512 au solde bancaire réel connu à la même date."""
    last = db.q1(conn, "SELECT raw, date FROM bank_transactions WHERE date<=? AND source='qonto' ORDER BY date DESC, id DESC LIMIT 1", (end,))
    bank_balance, ref = None, None
    if last:
        try:
            raw = json.loads(last["raw"] or "{}")
            if raw.get("settled_balance_cents") is not None:
                # Solde après l'opération la plus récente de la journée
                same_day = db.q(conn, "SELECT raw FROM bank_transactions WHERE date=? AND source='qonto'", (last["date"],))
                bals = []
                for r in same_day:
                    rr = json.loads(r["raw"] or "{}")
                    if rr.get("settled_balance_cents") is not None:
                        bals.append((rr.get("settled_at") or "", rr["settled_balance_cents"]))
                bals.sort()
                bank_balance, ref = bals[-1][1], last["date"]
        except (ValueError, IndexError):
            pass
    manual = db.get_setting(conn, "bank_statement") or {}
    if manual.get("date") and manual.get("date") <= end and (ref is None or manual["date"] >= ref):
        bank_balance, ref = manual.get("balance_cents"), manual["date"]
    book = ledger.account_balance(conn, "512", ref or end)
    return {"date_reference": ref, "solde_banque_cents": bank_balance, "solde_comptable_cents": book,
            "ecart_cents": (bank_balance - book) if bank_balance is not None else None}


def month_bounds(month: str):
    y, m = int(month[:4]), int(month[5:7])
    start = date(y, m, 1)
    end = (date(y + (m == 12), (m % 12) + 1, 1) - timedelta(days=1))
    return start.isoformat(), end.isoformat()


def verify_month(conn, month: str) -> dict:
    start, end = month_bounds(month)
    issues = period_checks(conn, start, end)
    blocking = [i for i in issues if i["niveau"] == "bloquant"]
    closed = db.q1(conn, "SELECT * FROM month_closings WHERE month=?", (month,))
    return {"month": month, "start": start, "end": end, "issues": issues, "blocking": len(blocking),
            "ok": not blocking, "closed": closed}


def close_month(conn, month: str, force=False) -> dict:
    res = verify_month(conn, month)
    if res["blocking"] and not force:
        raise ledger.AccountingError("Des anomalies bloquantes restent à corriger avant de clôturer le mois.")
    with db.tx(conn):
        for e in db.q(conn, "SELECT id FROM entries WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date, id", (res["start"], res["end"])):
            ledger.validate_entry(conn, e["id"])
        for x in db.q(conn, "SELECT id, document_id FROM expenses WHERE date BETWEEN ? AND ?", (res["start"], res["end"])):
            services.lock_document(conn, x["document_id"])
        conn.execute("INSERT OR REPLACE INTO month_closings(month,closed_at,report) VALUES(?,?,?)",
                     (month, db.now(), json.dumps(res["issues"], ensure_ascii=False)))
        db.audit(conn, "cloture_mois", "periode", None, None, {"mois": month, "anomalies_restantes": len(res["issues"])},
                 "Clôture forcée" if force and res["blocking"] else None)
    return verify_month(conn, month)

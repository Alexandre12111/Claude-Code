"""États comptables calculés depuis le grand livre : balance, grand livre,
journal, compte de résultat, bilan, créances et dettes."""
from . import db


def _period_filter(start, end, include_drafts=True):
    sql, p = "", []
    if start:
        sql += " AND e.date>=?"
        p.append(start)
    if end:
        sql += " AND e.date<=?"
        p.append(end)
    if not include_drafts:
        sql += " AND e.status='valide'"
    return sql, p


def trial_balance(conn, start, end, include_drafts=True):
    f, p = _period_filter(start, end, include_drafts)
    rows = db.q(conn, "SELECT l.account, a.label, SUM(l.debit_cents) debit, SUM(l.credit_cents) credit"
                      " FROM entry_lines l JOIN entries e ON e.id=l.entry_id LEFT JOIN accounts a ON a.number=l.account"
                      " WHERE 1=1" + f + " GROUP BY l.account ORDER BY l.account", p)
    for r in rows:
        r["solde"] = r["debit"] - r["credit"]
        r["solde_debiteur"] = max(r["solde"], 0)
        r["solde_crediteur"] = max(-r["solde"], 0)
    tot = {"debit": sum(r["debit"] for r in rows), "credit": sum(r["credit"] for r in rows)}
    tot["equilibre"] = tot["debit"] == tot["credit"]
    return {"rows": rows, "totals": tot}


def general_ledger(conn, start, end, account=None, include_drafts=True):
    f, p = _period_filter(start, end, include_drafts)
    if account:
        f += " AND l.account LIKE ?"
        p.append(account + "%")
    rows = db.q(conn, "SELECT l.*, e.date, e.journal, e.number, e.piece_ref, e.status, e.label entry_label, e.document_id,"
                      " a.label account_label FROM entry_lines l JOIN entries e ON e.id=l.entry_id"
                      " LEFT JOIN accounts a ON a.number=l.account WHERE 1=1" + f +
                      " ORDER BY l.account, e.date, e.id", p)
    out, cur = [], None
    for r in rows:
        if cur is None or cur["account"] != r["account"]:
            cur = {"account": r["account"], "label": r["account_label"], "lines": [], "debit": 0, "credit": 0}
            out.append(cur)
        cur["debit"] += r["debit_cents"]
        cur["credit"] += r["credit_cents"]
        r["cumul"] = cur["debit"] - cur["credit"]
        cur["lines"].append(r)
    for c in out:
        c["solde"] = c["debit"] - c["credit"]
    return out


def journal(conn, start, end, journal_code=None, include_drafts=True):
    f, p = _period_filter(start, end, include_drafts)
    if journal_code:
        f += " AND e.journal=?"
        p.append(journal_code)
    entries = db.q(conn, "SELECT e.* FROM entries e WHERE 1=1" + f + " ORDER BY e.date, e.journal, e.id", p)
    for e in entries:
        e["lines"] = db.q(conn, "SELECT l.*, a.label account_label FROM entry_lines l LEFT JOIN accounts a ON a.number=l.account WHERE entry_id=? ORDER BY id", (e["id"],))
    return entries


# ---------------------------------------------------------------- compte de résultat
IS_SECTIONS = [
    ("produits_exploitation", "Produits d'exploitation", ("70", "71", "72", "74", "75", "781")),
    ("charges_exploitation", "Charges d'exploitation", ("60", "61", "62", "63", "64", "65", "681")),
    ("produits_financiers", "Produits financiers", ("76", "786")),
    ("charges_financieres", "Charges financières", ("66", "686")),
    ("produits_exceptionnels", "Produits exceptionnels", ("77", "787")),
    ("charges_exceptionnelles", "Charges exceptionnelles", ("67", "687")),
    ("impot", "Impôt sur les bénéfices", ("69",)),
]


def income_statement(conn, start, end, include_drafts=True):
    tb = trial_balance(conn, start, end, include_drafts)["rows"]
    sections = {}
    for key, label, prefixes in IS_SECTIONS:
        lines = []
        for r in tb:
            if r["account"][0] in "67" and any(r["account"].startswith(px) for px in prefixes):
                amount = -r["solde"] if r["account"][0] == "7" else r["solde"]
                lines.append({"account": r["account"], "label": r["label"], "amount": amount})
        sections[key] = {"label": label, "lines": lines, "total": sum(l["amount"] for l in lines)}
    s = {k: v["total"] for k, v in sections.items()}
    exploitation = s["produits_exploitation"] - s["charges_exploitation"]
    financier = s["produits_financiers"] - s["charges_financieres"]
    exceptionnel = s["produits_exceptionnels"] - s["charges_exceptionnelles"]
    avant_impot = exploitation + financier + exceptionnel
    return {"sections": sections, "resultat_exploitation": exploitation, "resultat_financier": financier,
            "resultat_exceptionnel": exceptionnel, "resultat_avant_impot": avant_impot, "impot": s["impot"],
            "resultat_net": avant_impot - s["impot"],
            "total_produits": s["produits_exploitation"] + s["produits_financiers"] + s["produits_exceptionnels"],
            "total_charges": s["charges_exploitation"] + s["charges_financieres"] + s["charges_exceptionnelles"] + s["impot"],
            "chiffre_affaires": sum(l["amount"] for l in sections["produits_exploitation"]["lines"] if l["account"].startswith("70"))}


# ---------------------------------------------------------------- bilan
def _classify(account: str, solde: int):
    """Retourne (côté, rubrique) pour un compte de bilan selon son solde."""
    a = account
    deb = solde > 0
    if a.startswith("109"):
        return "actif", "Capital souscrit non appelé"
    if a.startswith("28") or a.startswith("29"):
        return "actif", "Amortissements et dépréciations"
    if a[:2] in ("20",):
        return "actif", "Immobilisations incorporelles"
    if a[:2] in ("21", "23"):
        return "actif", "Immobilisations corporelles"
    if a[:2] in ("26", "27"):
        return "actif", "Immobilisations financières"
    if a[0] == "3":
        return "actif", "Stocks"
    if a.startswith("101"):
        return "passif", "Capital social"
    if a.startswith("106"):
        return "passif", "Réserves"
    if a.startswith("11"):
        return "passif", "Report à nouveau"
    if a.startswith("12"):
        return "passif", "Résultat de l'exercice"
    if a.startswith("16"):
        return "passif", "Emprunts et dettes auprès des établissements de crédit"
    if a.startswith("1"):
        return "passif", "Autres fonds propres et provisions"
    if a.startswith("486"):
        return "actif", "Charges constatées d'avance"
    if a.startswith("487"):
        return "passif", "Produits constatés d'avance"
    if a.startswith("455"):
        return ("actif", "Autres créances (compte courant débiteur)") if deb else ("passif", "Emprunts et dettes financières divers (compte courant d'associé)")
    if a[:2] == "41":
        return ("actif", "Créances clients") if deb else ("passif", "Avances et acomptes reçus / clients créditeurs")
    if a[:2] == "40":
        return ("actif", "Autres créances") if deb else ("passif", "Dettes fournisseurs")
    if a[:2] in ("42", "43", "44"):
        return ("actif", "Autres créances (État, TVA)") if deb else ("passif", "Dettes fiscales et sociales")
    if a[0] == "4":
        return ("actif", "Autres créances") if deb else ("passif", "Autres dettes")
    if a[0] == "5":
        return ("actif", "Disponibilités") if deb else ("passif", "Concours bancaires courants")
    return None


ACTIF_ORDER = ["Capital souscrit non appelé", "Immobilisations incorporelles", "Immobilisations corporelles",
               "Immobilisations financières", "Amortissements et dépréciations", "Stocks", "Créances clients",
               "Autres créances (État, TVA)", "Autres créances", "Autres créances (compte courant débiteur)",
               "Disponibilités", "Charges constatées d'avance"]
PASSIF_ORDER = ["Capital social", "Réserves", "Report à nouveau", "Résultat de l'exercice", "Autres fonds propres et provisions",
                "Emprunts et dettes auprès des établissements de crédit",
                "Emprunts et dettes financières divers (compte courant d'associé)", "Avances et acomptes reçus / clients créditeurs",
                "Dettes fournisseurs", "Dettes fiscales et sociales", "Autres dettes", "Concours bancaires courants",
                "Produits constatés d'avance"]


def balance_sheet(conn, fy, include_drafts=True):
    tb = trial_balance(conn, fy["start_date"], fy["end_date"], include_drafts)["rows"]
    actif, passif = {}, {}
    result = 0
    for r in tb:
        if r["account"][0] in "67":
            result -= r["solde"]
            continue
        if r["solde"] == 0:
            continue
        c = _classify(r["account"], r["solde"])
        if not c:
            continue
        side, rub = c
        target = actif if side == "actif" else passif
        amount = r["solde"] if side == "actif" else -r["solde"]
        target.setdefault(rub, {"total": 0, "accounts": []})
        target[rub]["total"] += amount
        target[rub]["accounts"].append({"account": r["account"], "label": r["label"], "amount": amount})
    passif.setdefault("Résultat de l'exercice", {"total": 0, "accounts": []})
    passif["Résultat de l'exercice"]["total"] += result
    passif["Résultat de l'exercice"]["accounts"].append({"account": "6/7", "label": "Produits moins charges", "amount": result})
    ta = sum(v["total"] for v in actif.values())
    tp = sum(v["total"] for v in passif.values())
    order = lambda d, o: [{"rubrique": k, **d[k]} for k in o if k in d] + [{"rubrique": k, **v} for k, v in d.items() if k not in o]
    return {"actif": order(actif, ACTIF_ORDER), "passif": order(passif, PASSIF_ORDER), "total_actif": ta,
            "total_passif": tp, "equilibre": ta == tp, "resultat": result}


def receivables_payables(conn, end):
    """État des créances et dettes à la date (par tiers, comptes 401/404/411/455/44x)."""
    rows = db.q(conn, "SELECT l.account, l.aux, MAX(l.aux_label) aux_label, SUM(l.debit_cents-l.credit_cents) solde"
                      " FROM entry_lines l JOIN entries e ON e.id=l.entry_id WHERE e.date<=? AND"
                      " (l.account LIKE '40%' OR l.account LIKE '41%' OR l.account LIKE '44%' OR l.account LIKE '455%' OR l.account LIKE '42%' OR l.account LIKE '43%' OR l.account LIKE '486%' OR l.account LIKE '487%')"
                      " GROUP BY l.account, l.aux HAVING solde<>0 ORDER BY l.account", (end,))
    names = {f"C{p['id']:04d}": p["name"] for p in db.q(conn, "SELECT id,name FROM parties WHERE kind='client'")}
    names.update({f"F{p['id']:04d}": p["name"] for p in db.q(conn, "SELECT id,name FROM parties WHERE kind='supplier'")})
    for r in rows:
        r["tiers"] = names.get(r["aux"] or "", r["aux_label"] or "")
        r["label"] = (db.q1(conn, "SELECT label FROM accounts WHERE number=?", (r["account"],)) or {}).get("label")
    return {"creances": [r for r in rows if r["solde"] > 0], "dettes": [dict(r, solde=-r["solde"]) for r in rows if r["solde"] < 0],
            "note": "Toutes les créances et dettes sont à moins d'un an, sauf indication contraire."}

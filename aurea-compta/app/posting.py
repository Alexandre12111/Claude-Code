"""Génération automatique des écritures à partir des pièces.

Une information saisie une seule fois (facture, dépense, opération bancaire,
apport) produit ici ses écritures. Toute modification de la source régénère
les écritures : brouillons remplacés, écritures validées contrepassées.

Exigibilité de la TVA (CGI art. 269) :
* prestations de services : TVA exigible à l'encaissement (sauf option débits).
  La TVA facturée est d'abord portée en 445870 « TVA collectée en attente »,
  puis virée en 445710 au prorata de chaque encaissement.
* ventes de biens (ou option débits) : TVA exigible à la facture (445710).
* symétriquement, la TVA sur achats de services n'est déductible qu'au paiement
  (445860 en attente, puis 445660 au paiement) ; achats de biens et
  immobilisations : déductible dès la facture (445660 / 445620).
"""
from . import db, rules
from .ledger import AccountingError, C, D, Line, clear_source, create_entry
from .money import pct_of, prorata


def vat_code(prefix: str, rate) -> str:
    r = str(rate or "0").replace(",", ".")
    if r.endswith(".0"):
        r = r[:-2]
    return f"{prefix}{r}"


def _rate_of_code(code: str) -> str:
    return "".join(ch for ch in code if ch.isdigit() or ch == ".")


# ---------------------------------------------------------------- ventes
def invoice_vat_on_cash(conn, inv) -> bool:
    tax = db.get_setting(conn, "tax")
    return inv["nature"] == "services" and not tax.get("vat_on_debits") and inv["vat_regime"] == "fr"


def post_sales_invoice(conn, inv_id: int, reason="Mise à jour de la facture") -> None:
    inv = db.q1(conn, "SELECT * FROM sales_invoices WHERE id=?", (inv_id,))
    clear_source(conn, "vente", inv_id, reason)
    if inv is None or inv["status"] != "emise":
        return
    client = db.q1(conn, "SELECT * FROM parties WHERE id=?", (inv["client_id"],))
    sign = -1 if inv["kind"] == "avoir" else 1
    aux, aux_label = f"C{client['id']:04d}", client["name"]
    tag = f"V{inv['id']}"
    lines: list[Line] = []
    on_cash = invoice_vat_on_cash(conn, inv)
    groups: dict[tuple, dict] = {}
    for ln in db.q(conn, "SELECT * FROM sales_invoice_lines WHERE invoice_id=?", (inv_id,)):
        g = groups.setdefault((ln["account"], ln["vat_rate"]), {"ht": 0, "vat": 0})
        g["ht"] += ln["ht_cents"]
        g["vat"] += ln["vat_cents"]
    label = f"{'Avoir' if sign < 0 else 'Facture'} {inv['number']} {client['name']}"
    non_taxed_code = {"exonere": "E2", "autoliquidation_ue": "E2",
                      "export": "E1" if inv["nature"] == "biens" else "E2"}.get(inv["vat_regime"])
    for (account, rate), g in groups.items():
        lines.append(C(account, sign * g["ht"], vat_code=non_taxed_code,
                       vat_base=sign * g["ht"] if non_taxed_code else None))
        if g["vat"]:
            code = vat_code("C", rate)
            if inv["kind"] == "avoir" and on_cash:
                # Avoir : réduit d'abord la TVA en attente de la facture d'origine.
                orig = inv["credited_invoice_id"]
                pend = _pending_balance(conn, "445870", f"V{orig}", code) if orig else 0
                part = max(0, min(g["vat"], pend))
                if part:
                    lines.append(D("445870", part, vat_code=code, lettrage=f"V{orig}"))
                if g["vat"] - part:
                    rest = g["vat"] - part
                    lines.append(D("445710", rest, vat_code=code, vat_base=-prorata(g["ht"], rest, g["vat"])))
            elif on_cash:
                lines.append(C("445870", sign * g["vat"], vat_code=code, lettrage=tag))
            else:
                lines.append(C("445710", sign * g["vat"], vat_code=code, vat_base=sign * g["ht"]))
    lines.append(D(rules.CLIENTS, sign * inv["ttc_cents"], aux=aux, aux_label=aux_label, lettrage=tag))
    create_entry(conn, "VT", inv["issue_date"], label, lines, piece_ref=inv["number"],
                 document_id=inv["document_id"], source_type="vente", source_id=inv_id, reason=reason)


def _pending_balance(conn, account, tag, code):
    """Solde restant (crédit - débit pour 445870 ; débit - crédit pour 445860)."""
    r = conn.execute("SELECT COALESCE(SUM(credit_cents-debit_cents),0) FROM entry_lines WHERE account=? AND lettrage=? AND vat_code=?",
                     (account, tag, code)).fetchone()[0]
    return r if account == "445870" else -r


def _moved(conn, account, tag, code):
    """TVA déjà rendue exigible par les règlements (écritures de banque)."""
    r = conn.execute(
        "SELECT COALESCE(SUM(l.debit_cents-l.credit_cents),0) FROM entry_lines l JOIN entries e ON e.id=l.entry_id"
        " WHERE l.account=? AND l.lettrage=? AND l.vat_code=? AND e.source_type LIKE 'banque%'",
        (account, tag, code)).fetchone()[0]
    return r if account == "445870" else -r


def _paid_until(conn, target_type, target_id, tx) -> int:
    return conn.execute(
        "SELECT COALESCE(SUM(a.amount_cents),0) FROM allocations a JOIN bank_transactions t ON t.id=a.bank_transaction_id"
        " WHERE a.target_type=? AND a.target_id=? AND (t.date < ? OR (t.date=? AND t.id<=?))",
        (target_type, target_id, tx["date"], tx["date"], tx["id"])).fetchone()[0]


def _invoice_vat_moves(conn, inv, tx) -> list[Line]:
    if inv["kind"] != "facture" or not invoice_vat_on_cash(conn, inv):
        return []
    tag = f"V{inv['id']}"
    avoirs = db.q(conn, "SELECT id FROM sales_invoices WHERE credited_invoice_id=? AND status='emise'", (inv["id"],))
    ids = [inv["id"]] + [a["id"] for a in avoirs]
    marks = ",".join("?" * len(ids))
    by_code = db.q(conn, f"SELECT l.vat_rate, SUM(CASE WHEN s.kind='avoir' THEN -l.vat_cents ELSE l.vat_cents END) vat,"
                         f" SUM(CASE WHEN s.kind='avoir' THEN -l.ht_cents ELSE l.ht_cents END) ht"
                         f" FROM sales_invoice_lines l JOIN sales_invoices s ON s.id=l.invoice_id"
                         f" WHERE l.invoice_id IN ({marks}) GROUP BY l.vat_rate", ids)
    ttc_net = conn.execute(f"SELECT SUM(CASE WHEN kind='avoir' THEN -ttc_cents ELSE ttc_cents END) FROM sales_invoices WHERE id IN ({marks})", ids).fetchone()[0]
    paid = _paid_until(conn, "vente", inv["id"], tx)
    out = []
    for row in by_code:
        if not row["vat"]:
            continue
        code = vat_code("C", row["vat_rate"])
        exig = row["vat"] if paid >= ttc_net else prorata(row["vat"], max(paid, 0), ttc_net)
        move = max(0, min(exig - _moved(conn, "445870", tag, code), _pending_balance(conn, "445870", tag, code)))
        if move:
            out.append(D("445870", move, vat_code=code, lettrage=tag, label=f"TVA exigible encaissement {inv['number']}"))
            out.append(C("445710", move, vat_code=code, vat_base=prorata(row["ht"], move, row["vat"]), lettrage=tag,
                         label=f"TVA collectée encaissement {inv['number']}"))
    return out


# ---------------------------------------------------------------- dépenses
def expense_counterpart(exp, cat) -> str:
    if exp["payment_method"] == "perso":
        return rules.ASSOCIATE
    if cat and cat["rule"] == "personnel":
        return rules.ASSOCIATE
    if cat and cat["is_asset"]:
        return rules.SUPPLIERS_ASSETS
    return rules.SUPPLIERS


def expense_vat_split(exp) -> tuple[int, int]:
    """(TVA récupérable, TVA non récupérable)"""
    if exp["vat_regime"] == "sans_tva":
        return 0, 0
    rec = pct_of(exp["vat_cents"], exp["vat_recovery"])
    return rec, exp["vat_cents"] - rec


def post_expense(conn, exp_id: int, reason="Mise à jour de la dépense") -> None:
    exp = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (exp_id,))
    clear_source(conn, "depense", exp_id, reason)
    if exp is None:
        return
    cat = db.q1(conn, "SELECT * FROM categories WHERE id=?", (exp["category_id"],)) if exp["category_id"] else None
    if cat and cat["rule"] == "personnel":
        # Dépense privée : pas de charge. Le paiement bancaire débite le compte courant (455).
        # Payée personnellement, elle n'a aucune incidence pour la société.
        return
    account = exp["account"] or (cat["account"] if cat else rules.SUSPENSE)
    if exp["fixed_asset_id"]:
        fa = db.q1(conn, "SELECT account FROM fixed_assets WHERE id=?", (exp["fixed_asset_id"],))
        if fa:
            account = fa["account"]
    is_asset = bool(cat and cat["is_asset"]) or account.startswith("2")
    cp = expense_counterpart(exp, cat)
    sup_name = exp["supplier_name"] or "Fournisseur"
    aux = (f"F{exp['supplier_id']:04d}" if exp["supplier_id"] else None) if cp != rules.ASSOCIATE else "ASSOCIE"
    aux_label = sup_name if cp != rules.ASSOCIATE else "Associé unique"
    tag = f"D{exp['id']}"
    lines: list[Line] = []
    label = f"{sup_name} {exp['label']}"[:200]
    rate = exp["vat_rate"] or "20"
    if exp["vat_regime"] in ("autoliquidation_ue", "autoliquidation_hors_ue"):
        auto = exp["vat_cents"] or pct_of(exp["ht_cents"], rate)
        rec = pct_of(auto, exp["vat_recovery"])
        code = vat_code("AL_UE" if exp["vat_regime"] == "autoliquidation_ue" else "AL_HUE", rate)
        lines += [D(account, exp["ht_cents"] + auto - rec),
                  D(rules.VAT_DEDUCTIBLE_AUTOLIQ, rec, vat_code=vat_code("I" if is_asset else "D", rate), vat_base=exp["ht_cents"]),
                  C(rules.VAT_DUE_AUTOLIQ, auto, vat_code=code, vat_base=exp["ht_cents"]),
                  C(cp, exp["ht_cents"], aux=aux, aux_label=aux_label, lettrage=tag)]
    elif exp["vat_regime"] == "sans_tva" or not exp["vat_cents"]:
        lines += [D(account, exp["ttc_cents"]), C(cp, exp["ttc_cents"], aux=aux, aux_label=aux_label, lettrage=tag)]
    else:
        rec, nonrec = expense_vat_split(exp)
        if is_asset:
            vat_acc, code = rules.VAT_DEDUCTIBLE_ASSETS, vat_code("I", rate)
        elif exp["nature"] == "biens" or exp["payment_method"] == "perso":
            vat_acc, code = rules.VAT_DEDUCTIBLE, vat_code("D", rate)
        else:
            vat_acc, code = rules.VAT_DEDUCTIBLE_PENDING, vat_code("D", rate)
        base = prorata(exp["ht_cents"], rec, exp["vat_cents"]) if exp["vat_cents"] else 0
        lines += [D(account, exp["ht_cents"] + nonrec),
                  D(vat_acc, rec, vat_code=code, vat_base=base if vat_acc != rules.VAT_DEDUCTIBLE_PENDING else None,
                    lettrage=tag if vat_acc == rules.VAT_DEDUCTIBLE_PENDING else None),
                  C(cp, exp["ttc_cents"], aux=aux, aux_label=aux_label, lettrage=tag)]
    create_entry(conn, "AC", exp["date"], label, lines, piece_ref=exp["invoice_number"] or f"DEP{exp['id']}",
                 document_id=exp["document_id"], source_type="depense", source_id=exp_id, reason=reason)


def _expense_vat_moves(conn, exp, tx) -> list[Line]:
    tag = f"D{exp['id']}"
    pend_rows = db.q(conn, "SELECT vat_code, SUM(debit_cents-credit_cents) v FROM entry_lines WHERE account='445860' AND lettrage=? GROUP BY vat_code", (tag,))
    out = []
    rec, _ = expense_vat_split(exp)
    if not rec:
        return out
    paid = -_paid_until(conn, "depense", exp["id"], tx)
    for row in pend_rows:
        code = row["vat_code"]
        exig = rec if paid >= exp["ttc_cents"] else prorata(rec, max(paid, 0), exp["ttc_cents"])
        move = max(0, min(exig - _moved(conn, "445860", tag, code), _pending_balance(conn, "445860", tag, code)))
        if move:
            base = prorata(exp["ht_cents"], move, exp["vat_cents"])
            out.append(C("445860", move, vat_code=code, lettrage=tag, label=f"TVA déductible au paiement {exp['supplier_name'] or ''}"))
            out.append(D("445660", move, vat_code=code, vat_base=base, lettrage=tag, label=f"TVA déductible au paiement {exp['supplier_name'] or ''}"))
    return out


# ---------------------------------------------------------------- banque
def post_bank_transaction(conn, tx_id: int, reason="Mise à jour du rapprochement") -> None:
    tx = db.q1(conn, "SELECT * FROM bank_transactions WHERE id=?", (tx_id,))
    clear_source(conn, "banque", tx_id, reason)
    if tx is None or tx["status"] == "ignore":
        return
    lines = [D(rules.BANK, tx["amount_cents"])]
    allocated = 0
    doc_id = None
    for a in db.q(conn, "SELECT * FROM allocations WHERE bank_transaction_id=? ORDER BY id", (tx_id,)):
        amt = a["amount_cents"]
        allocated += amt
        t = a["target_type"]
        if t == "vente":
            inv = db.q1(conn, "SELECT * FROM sales_invoices WHERE id=?", (a["target_id"],))
            lines.append(C(rules.CLIENTS, amt, aux=f"C{inv['client_id']:04d}", lettrage=f"V{inv['id']}",
                           label=f"Règlement {inv['number']}"))
            lines += _invoice_vat_moves(conn, inv, tx)
            doc_id = doc_id or inv["document_id"]
        elif t == "depense":
            exp = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (a["target_id"],))
            cat = db.q1(conn, "SELECT * FROM categories WHERE id=?", (exp["category_id"],)) if exp["category_id"] else None
            cp = expense_counterpart(dict(exp, payment_method="qonto"), cat)
            aux = "ASSOCIE" if cp == rules.ASSOCIATE else (f"F{exp['supplier_id']:04d}" if exp["supplier_id"] else None)
            lines.append(C(cp, amt, aux=aux, lettrage=f"D{exp['id']}", label=f"Paiement {exp['supplier_name'] or exp['label']}"))
            if cp != rules.ASSOCIATE:
                lines += _expense_vat_moves(conn, exp, tx)
            doc_id = doc_id or exp["document_id"]
        elif t == "associe":
            lines.append(C(rules.ASSOCIATE, amt, aux="ASSOCIE", aux_label="Associé unique",
                           label="Apport en compte courant" if amt > 0 else "Remboursement compte courant"))
        elif t == "capital":
            lines.append(C("101300", amt, label="Libération du capital social"))
        elif t == "tva":
            lines.append(C(a["account"] or rules.VAT_TO_PAY, amt, label="TVA réglée au Trésor" if amt < 0 else "Remboursement de crédit de TVA"))
        elif t == "is":
            lines.append(C("444000", amt, label="Impôt sur les sociétés"))
        elif t == "virement_interne":
            lines.append(C("580000", amt))
        elif t == "compte":
            if not a["account"]:
                raise AccountingError("Compte manquant pour l'imputation")
            lines.append(C(a["account"], amt))
    rest = tx["amount_cents"] - allocated
    if rest:
        lines.append(C(rules.SUSPENSE, rest, label="Opération à justifier"))
    create_entry(conn, "BQ", tx["date"], (tx["counterparty"] or tx["label"] or "Opération bancaire")[:200], lines,
                 piece_ref=(tx["reference"] or tx["external_id"])[:40], document_id=doc_id,
                 source_type="banque", source_id=tx_id, reason=reason)
    if rest == 0 and allocated != 0:
        new_status = "rapproche"
    else:
        new_status = "propose" if tx["suggestion"] else "a_traiter"
    if tx["status"] != new_status:
        conn.execute("UPDATE bank_transactions SET status=? WHERE id=?", (new_status, tx_id))


def repost_bank_for(conn, target_type: str, target_id: int, reason: str) -> None:
    for r in db.q(conn, "SELECT DISTINCT bank_transaction_id FROM allocations WHERE target_type=? AND target_id=?",
                  (target_type, target_id)):
        post_bank_transaction(conn, r["bank_transaction_id"], reason)


# ---------------------------------------------------------------- associé / capital
def post_associate_movement(conn, mv_id: int, reason="Mise à jour du mouvement associé") -> None:
    mv = db.q1(conn, "SELECT * FROM associate_movements WHERE id=?", (mv_id,))
    clear_source(conn, "associe", mv_id, reason)
    if mv is None:
        return
    # Les apports et remboursements passent par la banque : l'écriture est celle
    # de l'opération bancaire rapprochée (pas de double comptabilisation).
    if mv["type"] == "capital_non_appele":
        create_entry(conn, "OD", mv["date"], "Capital souscrit non appelé",
                     [D("109000", mv["amount_cents"]), C("101100", mv["amount_cents"])],
                     piece_ref="STATUTS", source_type="associe", source_id=mv_id, reason=reason)

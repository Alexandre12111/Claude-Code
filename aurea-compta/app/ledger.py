"""Moteur d'écritures en partie double.

Principes :
* chaque écriture est équilibrée (contrôle systématique, refus sinon) ;
* une écriture "brouillon" peut être régénérée quand sa source change ;
* une écriture "validée" n'est jamais modifiée : on passe une contrepassation
  datée et motivée, puis une nouvelle écriture (traçabilité complète) ;
* chaque écriture garde sa source (facture, dépense, opération bancaire...) et
  la référence de la pièce justificative.
"""
from dataclasses import dataclass, field

from . import db, rules
from .money import fmt


class AccountingError(ValueError):
    pass


@dataclass
class Line:
    account: str
    debit: int = 0
    credit: int = 0
    label: str | None = None
    aux: str | None = None
    aux_label: str | None = None
    vat_code: str | None = None
    vat_base: int | None = None
    lettrage: str | None = None


def D(account, amount, **kw) -> Line:
    """Ligne au débit (montant négatif : basculée au crédit)."""
    return Line(account, debit=amount, **kw) if amount >= 0 else Line(account, credit=-amount, **kw)


def C(account, amount, **kw) -> Line:
    return Line(account, credit=amount, **kw) if amount >= 0 else Line(account, debit=-amount, **kw)


def fiscal_year_for(conn, date: str):
    return db.q1(conn, "SELECT * FROM fiscal_years WHERE start_date<=? AND end_date>=?", (date, date))


def check_period(conn, date: str, reason: str | None = None) -> None:
    fy = fiscal_year_for(conn, date)
    if fy is None:
        raise AccountingError(f"Aucun exercice comptable ne couvre la date {date}. Créez l'exercice dans Paramètres.")
    if fy["status"] == "closed":
        raise AccountingError(f"L'exercice {fy['start_date']} au {fy['end_date']} est clôturé : aucune écriture ne peut y être ajoutée.")
    closed = db.q1(conn, "SELECT month FROM month_closings WHERE month=?", (date[:7],))
    if closed:
        db.audit(conn, "regularisation_mois_cloture", "periode", None, None, {"mois": date[:7]},
                 reason or "Écriture ajoutée sur un mois déjà vérifié et clôturé")


def create_entry(conn, journal: str, date: str, label: str, lines: list[Line], *,
                 piece_ref=None, piece_date=None, document_id=None, source_type=None, source_id=None,
                 status="brouillon", reason=None) -> int | None:
    if journal not in rules.JOURNALS:
        raise AccountingError(f"Journal inconnu {journal}")
    clean = []
    for ln in lines:
        if ln.debit < 0 or ln.credit < 0:
            raise AccountingError("Montant négatif dans une ligne")
        if ln.debit and ln.credit:
            # Ligne mixte : on compense
            net = ln.debit - ln.credit
            ln.debit, ln.credit = (net, 0) if net > 0 else (0, -net)
        if ln.debit == 0 and ln.credit == 0:
            continue
        clean.append(ln)
    if not clean:
        return None
    td = sum(l.debit for l in clean)
    tc = sum(l.credit for l in clean)
    if td != tc:
        raise AccountingError(f"Écriture déséquilibrée « {label} » : débit {fmt(td)} / crédit {fmt(tc)}")
    check_period(conn, date, reason)
    for l in clean:
        if not db.q1(conn, "SELECT number FROM accounts WHERE number=?", (l.account,)):
            raise AccountingError(f"Compte {l.account} absent du plan comptable")
    cur = conn.execute(
        "INSERT INTO entries(journal,date,label,piece_ref,piece_date,document_id,source_type,source_id,status,created_at)"
        " VALUES(?,?,?,?,?,?,?,?, 'brouillon', ?)",
        (journal, date, label[:250], piece_ref, piece_date or date, document_id, source_type, source_id, db.now()))
    eid = cur.lastrowid
    for l in clean:
        conn.execute(
            "INSERT INTO entry_lines(entry_id,account,aux,aux_label,label,debit_cents,credit_cents,vat_code,vat_base_cents,lettrage)"
            " VALUES(?,?,?,?,?,?,?,?,?,?)",
            (eid, l.account, l.aux, l.aux_label, (l.label or label)[:250], l.debit, l.credit, l.vat_code, l.vat_base, l.lettrage))
    if status == "valide":
        validate_entry(conn, eid)
    return eid


def validate_entry(conn, entry_id: int) -> None:
    e = db.q1(conn, "SELECT * FROM entries WHERE id=?", (entry_id,))
    if not e:
        raise AccountingError("Écriture introuvable")
    if e["status"] == "valide":
        return
    t = db.q1(conn, "SELECT SUM(debit_cents) d, SUM(credit_cents) c FROM entry_lines WHERE entry_id=?", (entry_id,))
    if (t["d"] or 0) != (t["c"] or 0) or not t["d"]:
        raise AccountingError("Écriture déséquilibrée ou vide : validation impossible")
    fy = fiscal_year_for(conn, e["date"])
    if fy is None or fy["status"] == "closed":
        raise AccountingError("Exercice clôturé ou inexistant")
    year = fy["end_date"][:4]
    prefix = f"{e['journal']}{year}-"
    last = db.q1(conn, "SELECT number FROM entries WHERE number LIKE ? ORDER BY number DESC LIMIT 1", (prefix + "%",))
    seq = int(last["number"].split("-")[-1]) + 1 if last else 1
    conn.execute("UPDATE entries SET status='valide', number=?, validated_at=? WHERE id=?",
                 (f"{prefix}{seq:05d}", db.now(), entry_id))
    db.audit(conn, "validation", "ecriture", entry_id, None, {"numero": f"{prefix}{seq:05d}"})


def reverse_entry(conn, entry_id: int, date: str | None, reason: str) -> int:
    """Contrepassation d'une écriture validée (sens inverse), elle-même validée."""
    e = db.q1(conn, "SELECT * FROM entries WHERE id=?", (entry_id,))
    if e["reversed_by"]:
        return e["reversed_by"]
    lines = db.q(conn, "SELECT * FROM entry_lines WHERE entry_id=?", (entry_id,))
    d = date or e["date"]
    fy = fiscal_year_for(conn, d)
    if fy is None or fy["status"] == "closed":
        d = db.now()[:10]
    rev = create_entry(conn, e["journal"], d, f"Contrepassation {e['number'] or e['id']} : {e['label']}"[:250], [
        Line(l["account"], debit=l["credit_cents"], credit=l["debit_cents"], label=l["label"], aux=l["aux"],
             aux_label=l["aux_label"], vat_code=l["vat_code"],
             vat_base=(-l["vat_base_cents"] if l["vat_base_cents"] is not None else None), lettrage=l["lettrage"])
        for l in lines], piece_ref=e["piece_ref"], document_id=e["document_id"],
        source_type=(e["source_type"] or "") + ":contrepassation", source_id=e["source_id"], status="valide", reason=reason)
    conn.execute("UPDATE entries SET reversed_by=? WHERE id=?", (rev, entry_id))
    conn.execute("UPDATE entries SET reversal_of=? WHERE id=?", (entry_id, rev))
    db.audit(conn, "contrepassation", "ecriture", entry_id, None, {"contrepassation": rev}, reason)
    return rev


def clear_source(conn, source_type: str, source_id: int, reason: str, date: str | None = None) -> None:
    """Supprime les brouillons d'une source, contrepasse ses écritures validées."""
    for e in db.q(conn, "SELECT * FROM entries WHERE source_type=? AND source_id=? AND reversed_by IS NULL",
                  (source_type, source_id)):
        if e["status"] == "brouillon":
            snapshot = db.q(conn, "SELECT account,debit_cents,credit_cents FROM entry_lines WHERE entry_id=?", (e["id"],))
            conn.execute("DELETE FROM entry_lines WHERE entry_id=?", (e["id"],))
            conn.execute("DELETE FROM entries WHERE id=?", (e["id"],))
            db.audit(conn, "suppression_brouillon", "ecriture", e["id"], {"entete": e, "lignes": snapshot}, None, reason)
        else:
            reverse_entry(conn, e["id"], date, reason)


def source_entries(conn, source_type, source_id):
    return db.q(conn, "SELECT * FROM entries WHERE source_type=? AND source_id=? AND reversed_by IS NULL ORDER BY id",
                (source_type, source_id))


def entry_with_lines(conn, entry_id):
    e = db.q1(conn, "SELECT * FROM entries WHERE id=?", (entry_id,))
    if e:
        e["lines"] = db.q(conn, "SELECT l.*, a.label AS account_label FROM entry_lines l LEFT JOIN accounts a ON a.number=l.account WHERE entry_id=? ORDER BY l.id", (entry_id,))
    return e


def account_balance(conn, account_prefix: str, date_to: str | None = None, date_from: str | None = None,
                    include_drafts=True) -> int:
    """Solde débiteur (débit - crédit) des comptes commençant par le préfixe."""
    sql = ("SELECT COALESCE(SUM(l.debit_cents - l.credit_cents),0) FROM entry_lines l JOIN entries e ON e.id=l.entry_id"
           " WHERE l.account LIKE ?")
    p = [account_prefix + "%"]
    if date_to:
        sql += " AND e.date<=?"
        p.append(date_to)
    if date_from:
        sql += " AND e.date>=?"
        p.append(date_from)
    if not include_drafts:
        sql += " AND e.status='valide'"
    return conn.execute(sql, p).fetchone()[0]


def check_global_balance(conn) -> dict:
    """Contrôle d'intégrité : chaque écriture et le grand livre sont équilibrés."""
    bad = db.q(conn, "SELECT e.id, e.label, SUM(l.debit_cents) d, SUM(l.credit_cents) c FROM entries e"
                     " JOIN entry_lines l ON l.entry_id=e.id GROUP BY e.id HAVING d<>c")
    t = db.q1(conn, "SELECT COALESCE(SUM(debit_cents),0) d, COALESCE(SUM(credit_cents),0) c FROM entry_lines")
    return {"ok": not bad and t["d"] == t["c"], "unbalanced": bad, "total_debit": t["d"], "total_credit": t["c"]}

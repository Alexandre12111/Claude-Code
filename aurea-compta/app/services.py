"""Opérations métier : tiers, pièces, factures, dépenses, banque, associé, immobilisations."""
import hashlib
import json
import mimetypes
import re
import shutil
import unicodedata
from datetime import date, datetime, timedelta
from pathlib import Path

from . import config, db, posting, rules
from .ledger import AccountingError, Line, D, C, clear_source, create_entry
from .money import pct_of, prorata, rate, split_ttc, to_cents, vat_from_ht

# ================================================================= utilitaires
def slug(s: str, n=40) -> str:
    s = unicodedata.normalize("NFKD", s or "").encode("ascii", "ignore").decode()
    s = re.sub(r"[^A-Za-z0-9]+", "-", s).strip("-").lower()
    return s[:n] or "inconnu"


def norm_name(s: str) -> str:
    s = unicodedata.normalize("NFKD", s or "").encode("ascii", "ignore").decode().upper()
    s = re.sub(r"\b(SAS|SASU|SARL|SA|EURL|LTD|LIMITED|INC|GMBH|BV|SRL|SL|IRELAND|FRANCE|EUROPE)\b", " ", s)
    s = re.sub(r"[^A-Z0-9 ]", " ", s)
    return re.sub(r"\s+", " ", s).strip()


def name_similarity(a: str, b: str) -> float:
    a, b = norm_name(a), norm_name(b)
    if not a or not b:
        return 0.0
    if a == b or a in b or b in a:
        return 1.0
    ta, tb = set(a.split()), set(b.split())
    return len(ta & tb) / max(1, min(len(ta), len(tb)))


def cents(v) -> int:
    if v is None or v == "":
        return 0
    if isinstance(v, int):
        return v
    return to_cents(v)


# ================================================================= tiers
def get_or_create_party(conn, kind: str, name: str, **kw) -> int:
    name = (name or "").strip() or ("Client" if kind == "client" else "Fournisseur")
    for p in db.q(conn, "SELECT * FROM parties WHERE kind=?", (kind,)):
        if norm_name(p["name"]) == norm_name(name) and norm_name(name):
            return p["id"]
    cur = conn.execute("INSERT INTO parties(kind,name,siren,vat_number,country,email,address,created_at) VALUES(?,?,?,?,?,?,?,?)",
                       (kind, name, kw.get("siren"), kw.get("vat_number"), kw.get("country") or "FR",
                        kw.get("email"), kw.get("address"), db.now()))
    db.audit(conn, "creation", "tiers", cur.lastrowid, None, {"kind": kind, "name": name})
    return cur.lastrowid


# ================================================================= pièces justificatives
def store_document(conn, filename: str, content: bytes, kind="justificatif", doc_date=None, party_name=None,
                   source="upload", external_id=None) -> int:
    sha = hashlib.sha256(content).hexdigest()
    existing = db.q1(conn, "SELECT id FROM documents WHERE sha256=?", (sha,))
    if existing:
        return existing["id"]
    ext = Path(filename or "piece").suffix.lower() or ".bin"
    mime = mimetypes.guess_type(filename or "")[0] or "application/octet-stream"
    d = doc_date or date.today().isoformat()
    rel = Path(d[:4]) / d[5:7] / slug(kind, 20) / slug(party_name or "divers", 30) / f"{d}_{slug(party_name or Path(filename or 'piece').stem, 30)}_{sha[:8]}{ext}"
    full = config.data_dir() / "documents" / rel
    full.parent.mkdir(parents=True, exist_ok=True)
    full.write_bytes(content)
    cur = conn.execute(
        "INSERT INTO documents(path,original_name,sha256,mime,size,kind,doc_date,party_name,source,external_id,uploaded_at)"
        " VALUES(?,?,?,?,?,?,?,?,?,?,?)",
        (str(rel), filename, sha, mime, len(content), kind, doc_date, party_name, source, external_id, db.now()))
    db.audit(conn, "ajout", "piece", cur.lastrowid, None, {"fichier": filename, "chemin": str(rel)})
    return cur.lastrowid


def refile_document(conn, doc_id: int, kind=None, doc_date=None, party_name=None) -> None:
    """Range la pièce selon exercice / mois / type / tiers après identification."""
    doc = db.q1(conn, "SELECT * FROM documents WHERE id=?", (doc_id,))
    if not doc:
        return
    kind = kind or doc["kind"]
    d = doc_date or doc["doc_date"] or doc["uploaded_at"][:10]
    party = party_name or doc["party_name"]
    ext = Path(doc["path"]).suffix
    rel = Path(d[:4]) / d[5:7] / slug(kind, 20) / slug(party or "divers", 30) / f"{d}_{slug(party or 'piece', 30)}_{doc['sha256'][:8]}{ext}"
    base = config.data_dir() / "documents"
    if str(rel) != doc["path"] and (base / doc["path"]).exists():
        (base / rel).parent.mkdir(parents=True, exist_ok=True)
        shutil.move(str(base / doc["path"]), str(base / rel))
    conn.execute("UPDATE documents SET path=?, kind=?, doc_date=?, party_name=? WHERE id=?",
                 (str(rel), kind, d, party, doc_id))


def document_path(doc) -> Path:
    return config.data_dir() / "documents" / doc["path"]


def lock_document(conn, doc_id):
    if doc_id:
        conn.execute("UPDATE documents SET locked=1 WHERE id=?", (doc_id,))


# ================================================================= factures clients
def next_invoice_number(conn, kind: str, issue_date: str) -> str:
    inv = db.get_setting(conn, "invoicing")
    prefix = inv["credit_prefix"] if kind == "avoir" else inv["prefix"]
    year = issue_date[:4]
    start = f"{prefix}{year}-"
    nums = [r["number"] for r in db.q(conn, "SELECT number FROM sales_invoices WHERE number LIKE ?", (start + "%",))]
    seq = max([int(n.split("-")[-1]) for n in nums if n.split("-")[-1].isdigit()] or [0]) + 1
    return inv["pattern"].format(prefix=prefix, year=year, seq=seq)


def check_invoice_number(conn, number: str, issue_date: str, kind: str, exclude_id=None) -> list[str]:
    """Contrôles de numérotation (CGI ann. II art. 242 nonies A : numéro unique, séquence chronologique continue)."""
    warn = []
    if db.q1(conn, "SELECT id FROM sales_invoices WHERE number=? AND id<>?", (number, exclude_id or 0)):
        raise AccountingError(f"Le numéro {number} est déjà utilisé : chaque facture doit avoir un numéro unique.")
    m = re.match(r"^(.*?)(\d+)$", number)
    if m:
        prefix, n = m.group(1), int(m.group(2))
        same = db.q(conn, "SELECT number, issue_date FROM sales_invoices WHERE number LIKE ? AND id<>?",
                    (prefix + "%", exclude_id or 0))
        seqs = {}
        for r in same:
            mm = re.match(r"^(.*?)(\d+)$", r["number"])
            if mm and mm.group(1) == prefix:
                seqs[int(mm.group(2))] = r["issue_date"]
        if seqs:
            if n > max(seqs) + 1:
                warn.append(f"Rupture de séquence : le dernier numéro de la série est {prefix}{max(seqs)}.")
            later = [k for k, d in seqs.items() if k < n and d > issue_date]
            earlier = [k for k, d in seqs.items() if k > n and d < issue_date]
            if later or earlier:
                warn.append("Ordre chronologique non respecté : une facture de numéro inférieur porte une date postérieure.")
    return warn


def _compute_lines(lines):
    out = []
    for ln in lines:
        qty = rate(ln.get("quantity") or "1")
        unit = cents(ln.get("unit_price_cents") if ln.get("unit_price_cents") is not None else ln.get("unit_price"))
        ht = int((qty * unit).to_integral_value(rounding="ROUND_HALF_UP"))
        if ln.get("ht_cents") is not None:
            ht = cents(ln["ht_cents"])
        vr = str(ln.get("vat_rate", "20")).replace(",", ".")
        vat = vat_from_ht(ht, vr)
        if ln.get("vat_cents") is not None and ln.get("vat_cents") != "":
            given = cents(ln["vat_cents"])
            if abs(given - vat) > 1:
                raise AccountingError(f"TVA incohérente sur la ligne « {ln.get('description')} » : {given} ≠ {vat} centimes attendus.")
            vat = given
        out.append({"description": ln.get("description") or "Prestation", "quantity": str(qty), "unit_price_cents": unit,
                    "vat_rate": vr, "account": ln.get("account") or "706000", "ht_cents": ht, "vat_cents": vat})
    return out


def save_sales_invoice(conn, data: dict, invoice_id: int | None = None, reason="Saisie facture") -> dict:
    with db.tx(conn):
        kind = data.get("kind", "facture")
        issue_date = data["issue_date"]
        client_id = data.get("client_id") or get_or_create_party(conn, "client", data.get("client_name"),
                                                                 vat_number=data.get("client_vat_number"),
                                                                 country=data.get("client_country"))
        vat_regime = data.get("vat_regime", "fr")
        lines = _compute_lines(data.get("lines") or [])
        if not lines:
            raise AccountingError("Une facture doit comporter au moins une ligne.")
        if vat_regime != "fr":
            for l in lines:
                l["vat_rate"], l["vat_cents"] = "0", 0
        ht = sum(l["ht_cents"] for l in lines)
        vat = sum(l["vat_cents"] for l in lines)
        before = None
        if invoice_id:
            before = db.q1(conn, "SELECT * FROM sales_invoices WHERE id=?", (invoice_id,))
            if any(e["status"] == "valide" for e in db.q(conn, "SELECT status FROM entries WHERE source_type='vente' AND source_id=?", (invoice_id,))):
                raise AccountingError("Cette facture est comptabilisée et validée : elle ne peut plus être modifiée. Émettez un avoir.")
        number = data.get("number") or (before["number"] if before else next_invoice_number(conn, kind, issue_date))
        warnings = check_invoice_number(conn, number, issue_date, kind, invoice_id)
        credited = data.get("credited_invoice_id")
        if kind == "avoir":
            if not credited:
                raise AccountingError("Un avoir doit référencer la facture qu'il corrige.")
            orig = db.q1(conn, "SELECT * FROM sales_invoices WHERE id=?", (credited,))
            already = conn.execute("SELECT COALESCE(SUM(ttc_cents),0) FROM sales_invoices WHERE credited_invoice_id=? AND id<>? AND status='emise'",
                                   (credited, invoice_id or 0)).fetchone()[0]
            if ht + vat + already > orig["ttc_cents"]:
                raise AccountingError("Le total des avoirs dépasse le montant de la facture d'origine.")
            client_id = orig["client_id"]
        row = (number, kind, credited, client_id, issue_date, data.get("due_date"), data.get("nature", "services"),
               vat_regime, ht, vat, ht + vat, data.get("status", "emise"), data.get("document_id"), data.get("notes"))
        if invoice_id:
            conn.execute("UPDATE sales_invoices SET number=?,kind=?,credited_invoice_id=?,client_id=?,issue_date=?,due_date=?,"
                         "nature=?,vat_regime=?,ht_cents=?,vat_cents=?,ttc_cents=?,status=?,document_id=?,notes=? WHERE id=?",
                         row + (invoice_id,))
            conn.execute("DELETE FROM sales_invoice_lines WHERE invoice_id=?", (invoice_id,))
        else:
            invoice_id = conn.execute(
                "INSERT INTO sales_invoices(number,kind,credited_invoice_id,client_id,issue_date,due_date,nature,vat_regime,"
                "ht_cents,vat_cents,ttc_cents,status,document_id,notes,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                row + (db.now(),)).lastrowid
        for l in lines:
            conn.execute("INSERT INTO sales_invoice_lines(invoice_id,description,quantity,unit_price_cents,vat_rate,account,ht_cents,vat_cents)"
                         " VALUES(?,?,?,?,?,?,?,?)", (invoice_id, l["description"], l["quantity"], l["unit_price_cents"],
                                                       l["vat_rate"], l["account"], l["ht_cents"], l["vat_cents"]))
        after = db.q1(conn, "SELECT * FROM sales_invoices WHERE id=?", (invoice_id,))
        db.audit(conn, "modification" if before else "creation", "facture_client", invoice_id, before, after, reason)
        if data.get("document_id"):
            client = db.q1(conn, "SELECT name FROM parties WHERE id=?", (client_id,))
            refile_document(conn, data["document_id"], "facture-client", issue_date, client["name"])
        posting.post_sales_invoice(conn, invoice_id, reason)
        if kind == "avoir":
            posting.repost_bank_for(conn, "vente", credited, reason)
        posting.repost_bank_for(conn, "vente", invoice_id, reason)
        return {"id": invoice_id, "number": number, "warnings": warnings}


def invoice_status(conn, inv) -> dict:
    paid = conn.execute("SELECT COALESCE(SUM(amount_cents),0) FROM allocations WHERE target_type='vente' AND target_id=?",
                        (inv["id"],)).fetchone()[0]
    credited = conn.execute("SELECT COALESCE(SUM(ttc_cents),0) FROM sales_invoices WHERE credited_invoice_id=? AND status='emise'",
                            (inv["id"],)).fetchone()[0] if inv["kind"] == "facture" else 0
    if inv["kind"] == "avoir":
        due = -inv["ttc_cents"]
        remaining = due - paid
        st = "rembourse" if remaining == 0 and paid else "impute"
        return {"paid_cents": paid, "credited_cents": 0, "remaining_cents": remaining, "payment_status": st}
    remaining = inv["ttc_cents"] - credited - paid
    if inv["status"] != "emise":
        st = inv["status"]
    elif remaining <= 0:
        st = "payee"
    elif paid > 0:
        st = "partielle"
    else:
        st = "impayee"
    return {"paid_cents": paid, "credited_cents": credited, "remaining_cents": remaining, "payment_status": st}


def delete_sales_invoice(conn, inv_id):
    with db.tx(conn):
        inv = db.q1(conn, "SELECT * FROM sales_invoices WHERE id=?", (inv_id,))
        if inv["status"] != "brouillon":
            raise AccountingError("Une facture émise ne se supprime pas : émettez un avoir.")
        conn.execute("DELETE FROM sales_invoices WHERE id=?", (inv_id,))
        db.audit(conn, "suppression", "facture_client", inv_id, inv, None)


# ================================================================= dépenses
def fiscal_warnings(conn, exp: dict, cat: dict | None) -> list[str]:
    w = []
    d = exp.get("date") or date.today().isoformat()
    if cat is None:
        w.append("Catégorie non renseignée.")
        return w
    if cat["rule"] == "cadeau":
        seuil = rules.get_rule("tva_seuil_cadeaux_ttc_cents", d)
        if exp.get("ttc_cents", 0) > seuil and exp.get("vat_recovery", 100) > 0:
            w.append("Cadeau de plus de 73 € TTC : la TVA n'est pas récupérable (sauf si plusieurs bénéficiaires, à préciser).")
        if not exp.get("beneficiary"):
            w.append("Cadeau : indiquez le bénéficiaire (relevé des frais généraux).")
    if cat["rule"] == "deplacement" and exp.get("vat_cents") and exp.get("vat_recovery", 0) > 0:
        w.append("Transport de personnes et hébergement du dirigeant : TVA en principe non récupérable.")
    if cat["rule"] in ("repas", "invitation") and not exp.get("comment"):
        w.append("Repas : précisez l'objet et les participants en commentaire pour justifier l'intérêt de l'entreprise.")
    if cat["rule"] == "materiel" and exp.get("ht_cents", 0) >= rules.get_rule("seuil_immobilisation_ht_cents", d):
        w.append("Matériel de 500 € HT ou plus : à enregistrer en immobilisation.")
    if cat["rule"] == "personnel":
        w.append("Dépense personnelle : non déductible, imputée au compte courant d'associé.")
    if exp.get("vat_regime") == "fr" and exp.get("vat_cents") and exp.get("ht_cents"):
        r = exp.get("vat_rate")
        if r and r not in ("0", "-1"):
            expected = vat_from_ht(exp["ht_cents"], r)
            if abs(expected - exp["vat_cents"]) > max(2, exp["ht_cents"] // 1000):
                w.append(f"TVA incohérente : {exp['vat_cents']/100:.2f} € pour un taux de {r} % (attendu {expected/100:.2f} €).")
    if exp.get("vat_regime") == "fr" and exp.get("vat_cents") and not exp.get("document_id"):
        w.append("Sans facture, la TVA n'est pas récupérable : ajoutez le justificatif.")
    return w


def _expense_amounts(data) -> dict:
    regime = data.get("vat_regime") or "fr"
    r = str(data.get("vat_rate") or ("0" if regime == "sans_tva" else "20")).replace(",", ".")
    ht = cents(data.get("ht_cents")) if data.get("ht_cents") not in (None, "") else None
    vat = cents(data.get("vat_cents")) if data.get("vat_cents") not in (None, "") else None
    ttc = cents(data.get("ttc_cents")) if data.get("ttc_cents") not in (None, "") else None
    if regime == "sans_tva":
        amount = ttc if ttc is not None else (ht or 0)
        return {"ht_cents": amount, "vat_cents": 0, "ttc_cents": amount, "vat_rate": "0"}
    if regime.startswith("autoliquidation"):
        base = ht if ht is not None else (ttc or 0)
        auto = vat if vat else vat_from_ht(base, r)
        return {"ht_cents": base, "vat_cents": auto, "ttc_cents": base, "vat_rate": r}
    if ttc is not None and ht is not None and vat is None:
        vat = ttc - ht
    if ttc is not None and vat is not None and ht is None:
        ht = ttc - vat
    if ht is not None and vat is not None:
        ttc = ht + vat if ttc is None else ttc
        if ht + vat != ttc:
            raise AccountingError(f"HT + TVA ≠ TTC ({ht + vat} ≠ {ttc} centimes).")
        return {"ht_cents": ht, "vat_cents": vat, "ttc_cents": ttc, "vat_rate": r}
    if ttc is not None:
        ht, vat = split_ttc(ttc, r)
        return {"ht_cents": ht, "vat_cents": vat, "ttc_cents": ttc, "vat_rate": r}
    if ht is not None:
        vat = vat_from_ht(ht, r)
        return {"ht_cents": ht, "vat_cents": vat, "ttc_cents": ht + vat, "vat_rate": r}
    raise AccountingError("Montant manquant.")


EXPENSE_FIELDS = ("date", "supplier_id", "supplier_name", "label", "invoice_number", "ht_cents", "vat_cents", "ttc_cents",
                  "vat_rate", "vat_regime", "vat_recovery", "deductible", "nature", "category_id", "account",
                  "payment_method", "beneficiary", "document_id", "status", "source", "comment")


def save_expense(conn, data: dict, expense_id: int | None = None, reason="Saisie dépense") -> dict:
    with db.tx(conn):
        before = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (expense_id,)) if expense_id else None
        merged = dict(before or {})
        merged.update({k: v for k, v in data.items() if k in EXPENSE_FIELDS or k in ("ht_cents", "vat_cents", "ttc_cents")})
        if any(k in data for k in ("ht_cents", "vat_cents", "ttc_cents", "vat_rate", "vat_regime")):
            src = {k: data.get(k, None) for k in ("ht_cents", "vat_cents", "ttc_cents")}
            if before and not any(data.get(k) not in (None, "") for k in src):
                src = {k: before[k] for k in src}
            amounts = _expense_amounts({**src, "vat_rate": merged.get("vat_rate"), "vat_regime": merged.get("vat_regime")})
            merged.update(amounts)
        cat = db.q1(conn, "SELECT * FROM categories WHERE id=?", (merged.get("category_id"),)) if merged.get("category_id") else None
        if cat and (not before or data.get("category_id") not in (None, before.get("category_id"))):
            if "vat_recovery" not in data:
                merged["vat_recovery"] = cat["vat_recovery"]
            if "deductible" not in data:
                merged["deductible"] = cat["deductible"]
            if "account" not in data:
                merged["account"] = None
            if cat["vat_recovery"] == 0 and merged.get("vat_regime") == "fr" and not merged.get("vat_cents"):
                merged["vat_regime"] = "sans_tva"
        if cat and cat["rule"] == "cadeau":
            if merged.get("ttc_cents", 0) > rules.get_rule("tva_seuil_cadeaux_ttc_cents", merged["date"]) and "vat_recovery" not in data:
                merged["vat_recovery"] = 0
        if cat and cat["rule"] == "personnel":
            merged["deductible"], merged["vat_recovery"] = 0, 0
        merged.setdefault("vat_regime", "fr")
        merged.setdefault("vat_recovery", 100)
        merged.setdefault("deductible", 100)
        merged.setdefault("nature", "services")
        merged.setdefault("payment_method", "qonto")
        merged.setdefault("status", "a_valider")
        merged.setdefault("source", "manuel")
        if not merged.get("supplier_id") and merged.get("supplier_name"):
            merged["supplier_id"] = get_or_create_party(conn, "supplier", merged["supplier_name"])
        if not merged.get("label"):
            merged["label"] = (cat["name"] if cat else "Dépense")
        cols = [k for k in EXPENSE_FIELDS if k in merged]
        if expense_id:
            if before["status"] == "valide" and data.get("status") != "valide" and not data.get("_allow_validated"):
                reason = reason + " (modification d'une dépense validée)"
            conn.execute(f"UPDATE expenses SET {', '.join(c + '=?' for c in cols)} WHERE id=?",
                         [merged[c] for c in cols] + [expense_id])
        else:
            cols2 = cols + ["created_at"]
            expense_id = conn.execute(f"INSERT INTO expenses({', '.join(cols2)}) VALUES({', '.join('?' * len(cols2))})",
                                      [merged[c] for c in cols] + [db.now()]).lastrowid
        after = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (expense_id,))
        db.audit(conn, "modification" if before else "creation", "depense", expense_id, before, after, reason)
        # Immobilisation automatique
        if cat and cat["is_asset"] and not after["fixed_asset_id"]:
            acc = after["account"] or cat["account"]
            years = rules.ASSET_TYPES.get(acc, {"years": 3})["years"]
            fa = conn.execute("INSERT INTO fixed_assets(label,account,acquisition_date,in_service_date,cost_cents,duration_years,expense_id,created_at)"
                              " VALUES(?,?,?,?,?,?,?,?)",
                              (after["label"], acc, after["date"], after["date"],
                               after["ht_cents"] + (after["vat_cents"] - pct_of(after["vat_cents"], after["vat_recovery"]) if after["vat_regime"] == "fr" else 0),
                               years, expense_id, db.now())).lastrowid
            conn.execute("UPDATE expenses SET fixed_asset_id=? WHERE id=?", (fa, expense_id))
        elif after["fixed_asset_id"]:
            conn.execute("UPDATE fixed_assets SET cost_cents=?, acquisition_date=? WHERE id=?",
                         (after["ht_cents"] + (after["vat_cents"] - pct_of(after["vat_cents"], after["vat_recovery"]) if after["vat_regime"] == "fr" else 0),
                          after["date"], after["fixed_asset_id"]))
        if after["document_id"]:
            refile_document(conn, after["document_id"], "facture-fournisseur", after["date"], after["supplier_name"])
        if after["status"] == "valide":
            lock_document(conn, after["document_id"])
        # Apprentissage : fournisseur -> catégorie
        if after["supplier_name"] and after["category_id"]:
            conn.execute("INSERT INTO supplier_rules(pattern,category_id,vat_regime,updated_at) VALUES(?,?,?,?)"
                         " ON CONFLICT(pattern) DO UPDATE SET category_id=excluded.category_id, vat_regime=excluded.vat_regime, updated_at=excluded.updated_at",
                         (norm_name(after["supplier_name"]), after["category_id"], after["vat_regime"], db.now()))
        posting.post_expense(conn, expense_id, reason)
        posting.repost_bank_for(conn, "depense", expense_id, reason)
        return {"id": expense_id, "warnings": fiscal_warnings(conn, after, cat)}


def delete_expense(conn, exp_id, reason="Suppression de la dépense"):
    with db.tx(conn):
        exp = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (exp_id,))
        if exp["status"] == "valide":
            raise AccountingError("Dépense validée : elle ne peut pas être supprimée (modifiez-la, la correction sera tracée).")
        txs = db.q(conn, "SELECT DISTINCT bank_transaction_id t FROM allocations WHERE target_type='depense' AND target_id=?", (exp_id,))
        conn.execute("DELETE FROM allocations WHERE target_type='depense' AND target_id=?", (exp_id,))
        clear_source(conn, "depense", exp_id, reason)
        if exp["fixed_asset_id"]:
            conn.execute("UPDATE expenses SET fixed_asset_id=NULL WHERE id=?", (exp_id,))
            conn.execute("DELETE FROM fixed_assets WHERE id=?", (exp["fixed_asset_id"],))
        conn.execute("DELETE FROM associate_movements WHERE expense_id=?", (exp_id,))
        conn.execute("DELETE FROM expenses WHERE id=?", (exp_id,))
        db.audit(conn, "suppression", "depense", exp_id, exp, None, reason)
        for t in txs:
            posting.post_bank_transaction(conn, t["t"], reason)


def expense_paid(conn, exp) -> int:
    if exp["payment_method"] == "perso":
        return exp["ttc_cents"]
    return -conn.execute("SELECT COALESCE(SUM(amount_cents),0) FROM allocations WHERE target_type='depense' AND target_id=?",
                         (exp["id"],)).fetchone()[0]


# ================================================================= banque
def import_transactions(conn, items: list[dict], source: str) -> dict:
    """Import idempotent : une opération déjà connue (même identifiant) est ignorée."""
    new, dup = 0, 0
    with db.tx(conn):
        for it in items:
            ext = it["external_id"]
            if db.q1(conn, "SELECT id FROM bank_transactions WHERE external_id=?", (ext,)):
                dup += 1
                continue
            # Doublon probable entre deux sources (CSV puis API) : même date, montant, libellé
            twin = db.q1(conn, "SELECT id, source FROM bank_transactions WHERE date=? AND amount_cents=? AND source<>? AND"
                               " (counterparty=? OR label=?)",
                         (it["date"], it["amount_cents"], source, it.get("counterparty"), it.get("label")))
            if twin:
                dup += 1
                db.audit(conn, "doublon_ignore", "operation_bancaire", twin["id"], None, {"external_id": ext, "source": source})
                continue
            tid = conn.execute(
                "INSERT INTO bank_transactions(source,external_id,bank_account,date,emitted_at,amount_cents,label,counterparty,"
                "operation_type,reference,note,qonto_vat_cents,qonto_vat_rate,attachment_ids,raw,imported_at)"
                " VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                (source, ext, it.get("bank_account"), it["date"], it.get("emitted_at"), it["amount_cents"], it.get("label"),
                 it.get("counterparty"), it.get("operation_type"), it.get("reference"), it.get("note"),
                 it.get("vat_cents"), it.get("vat_rate"), json.dumps(it.get("attachment_ids") or []),
                 json.dumps(it.get("raw") or {}, default=str), db.now())).lastrowid
            new += 1
            suggest_for_transaction(conn, tid)
            posting.post_bank_transaction(conn, tid, "Import bancaire")
    return {"imported": new, "duplicates": dup}


def _associate_names(conn) -> list[str]:
    c = db.get_setting(conn, "company")
    return [n for n in [c.get("associate_name")] + list(c.get("associate_aliases") or []) if n]


def suggest_for_transaction(conn, tx_id: int) -> dict | None:
    tx = db.q1(conn, "SELECT * FROM bank_transactions WHERE id=?", (tx_id,))
    who = tx["counterparty"] or tx["label"] or ""
    amt = tx["amount_cents"]
    sug = None
    # 1. Associé : virement depuis / vers le compte personnel du dirigeant
    if any(name_similarity(who, n) >= 0.99 for n in _associate_names(conn)) and tx["operation_type"] in (None, "transfer", "income", "swift_income", "other"):
        sug = {"type": "associe", "confidence": 0.8,
               "explanation": ("Virement reçu de l'associé : apport en compte courant probable." if amt > 0 else
                               "Virement vers l'associé : remboursement de compte courant probable (à confirmer : ce n'est ni une dépense ni une rémunération).")}
    elif tx["operation_type"] == "qonto_fee":
        cat = db.q1(conn, "SELECT id FROM categories WHERE code='banque'")
        sug = {"type": "depense_nouvelle", "category_id": cat["id"] if cat else None, "confidence": 0.95,
               "explanation": "Frais Qonto : frais bancaires."}
    elif re.search(r"\b(DGFIP|TRESOR PUBLIC|IMPOTS?\.GOUV|SIE)\b", norm_name(who + " " + (tx["label"] or ""))):
        sug = {"type": "tva" if "TVA" in norm_name((tx["label"] or "") + " " + (tx["reference"] or "")) else "impot",
               "confidence": 0.6, "explanation": "Paiement à l'administration fiscale : TVA, IS ou CFE à préciser."}
    elif amt > 0:
        best = None
        for inv in db.q(conn, "SELECT s.*, p.name client FROM sales_invoices s JOIN parties p ON p.id=s.client_id WHERE s.kind='facture' AND s.status='emise'"):
            st = invoice_status(conn, inv)
            if st["remaining_cents"] <= 0:
                continue
            score = 0.0
            if st["remaining_cents"] == amt:
                score += 0.6
            elif amt < st["remaining_cents"]:
                score += 0.25
            score += 0.3 * name_similarity(who, inv["client"])
            if inv["number"] and inv["number"].replace(" ", "") in ((tx["reference"] or "") + (tx["label"] or "")).replace(" ", ""):
                score += 0.4
            if score > 0.5 and (best is None or score > best[0]):
                best = (score, inv)
        if best:
            sug = {"type": "vente", "invoice_id": best[1]["id"], "confidence": min(best[0], 1.0),
                   "explanation": f"Règlement probable de la facture {best[1]['number']} ({best[1]['client']})."}
    else:
        best = None
        for exp in db.q(conn, "SELECT * FROM expenses WHERE payment_method IN ('qonto','a_payer')"):
            if expense_paid(conn, exp) >= exp["ttc_cents"]:
                continue
            if exp["ttc_cents"] != -amt:
                continue
            days = abs((datetime.fromisoformat(exp["date"]) - datetime.fromisoformat(tx["date"])).days)
            if days > 45:
                continue
            score = 0.55 + 0.35 * name_similarity(who, exp["supplier_name"] or "") + (0.1 if days <= 5 else 0)
            if best is None or score > best[0]:
                best = (score, exp)
        if best:
            sug = {"type": "depense", "expense_id": best[1]["id"], "confidence": min(best[0], 1.0),
                   "explanation": f"Paiement probable de la dépense « {best[1]['supplier_name']} {best[1]['label']} »."}
        else:
            rule = None
            for r in db.q(conn, "SELECT * FROM supplier_rules"):
                if r["pattern"] and name_similarity(who, r["pattern"]) >= 0.99:
                    rule = r
                    break
            if rule:
                sug = {"type": "depense_nouvelle", "category_id": rule["category_id"], "vat_regime": rule["vat_regime"],
                       "confidence": 0.75, "explanation": "Fournisseur déjà connu : même catégorie que précédemment."}
    conn.execute("UPDATE bank_transactions SET suggestion=?, status=CASE WHEN status='a_traiter' AND ? IS NOT NULL THEN 'propose' ELSE status END WHERE id=?",
                 (json.dumps(sug, ensure_ascii=False) if sug else None, json.dumps(sug) if sug else None, tx_id))
    return sug


def classify_transaction(conn, tx_id: int, payload: dict, reason="Rapprochement bancaire") -> dict:
    """Affecte une opération bancaire. payload["type"] parmi :
    depense_nouvelle, depense, vente, associe, capital, tva, is, virement_interne, compte, doublon.
    """
    with db.tx(conn):
        tx = db.q1(conn, "SELECT * FROM bank_transactions WHERE id=?", (tx_id,))
        if tx is None:
            raise AccountingError("Opération introuvable")
        t = payload["type"]
        before = db.q(conn, "SELECT * FROM allocations WHERE bank_transaction_id=?", (tx_id,))
        if not payload.get("append"):
            conn.execute("DELETE FROM allocations WHERE bank_transaction_id=?", (tx_id,))
            for mv in db.q(conn, "SELECT id FROM associate_movements WHERE bank_transaction_id=?", (tx_id,)):
                conn.execute("DELETE FROM associate_movements WHERE id=?", (mv["id"],))
        amt = cents(payload["amount_cents"]) if payload.get("amount_cents") not in (None, "") else tx["amount_cents"]
        result = {}

        def alloc(target_type, target_id=None, amount=amt, account=None):
            conn.execute("INSERT INTO allocations(bank_transaction_id,target_type,target_id,account,amount_cents,created_at) VALUES(?,?,?,?,?,?)",
                         (tx_id, target_type, target_id, account, amount, db.now()))

        if t == "depense_nouvelle":
            if amt >= 0:
                raise AccountingError("Une dépense correspond à un débit bancaire.")
            data = {"date": tx["date"], "supplier_name": payload.get("supplier_name") or tx["counterparty"] or tx["label"],
                    "label": payload.get("label") or tx["label"] or "Dépense", "ttc_cents": -amt,
                    "vat_rate": payload.get("vat_rate") or (tx["qonto_vat_rate"] if tx["qonto_vat_rate"] not in (None, "-1") else None),
                    "vat_cents": payload.get("vat_cents") if payload.get("vat_cents") not in (None, "") else tx["qonto_vat_cents"],
                    "vat_regime": payload.get("vat_regime") or "fr", "category_id": payload.get("category_id"),
                    "nature": payload.get("nature") or "services", "payment_method": "qonto",
                    "document_id": payload.get("document_id"), "comment": payload.get("comment"),
                    "beneficiary": payload.get("beneficiary"), "source": "qonto" if tx["source"] == "qonto" else "banque",
                    "status": "a_valider", "invoice_number": payload.get("invoice_number")}
            if data["vat_cents"] in (None, ""):
                data.pop("vat_cents")
            r = save_expense(conn, data, reason=reason)
            alloc("depense", r["id"])
            result = r
        elif t == "depense":
            alloc("depense", int(payload["expense_id"]))
            exp = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (int(payload["expense_id"]),))
            if exp["payment_method"] == "a_payer":
                conn.execute("UPDATE expenses SET payment_method='qonto' WHERE id=?", (exp["id"],))
                posting.post_expense(conn, exp["id"], reason)
        elif t == "vente":
            for item in payload.get("invoices") or [{"invoice_id": payload["invoice_id"], "amount_cents": amt}]:
                alloc("vente", int(item["invoice_id"]), cents(item.get("amount_cents")) if item.get("amount_cents") not in (None, "") else amt)
        elif t == "associe":
            mtype = "apport" if tx["amount_cents"] > 0 else "remboursement"
            mv = conn.execute("INSERT INTO associate_movements(date,type,amount_cents,comment,bank_transaction_id,created_at) VALUES(?,?,?,?,?,?)",
                              (tx["date"], mtype, abs(amt), payload.get("comment"), tx_id, db.now())).lastrowid
            alloc("associe", mv)
        elif t == "capital":
            mv = conn.execute("INSERT INTO associate_movements(date,type,amount_cents,comment,bank_transaction_id,created_at) VALUES(?,?,?,?,?,?)",
                              (tx["date"], "capital_libere", abs(amt), payload.get("comment"), tx_id, db.now())).lastrowid
            alloc("capital", mv)
        elif t == "tva":
            alloc("tva", None, amt, payload.get("account") or (rules.VAT_TO_PAY if amt < 0 else rules.VAT_CREDIT))
        elif t == "is":
            alloc("is", None, amt, "444000")
        elif t == "impot":
            raise AccountingError("Précisez : TVA, IS (444) ou CFE (dépense catégorie Impôts et taxes).")
        elif t == "virement_interne":
            alloc("virement_interne", None, amt, "580000")
        elif t == "compte":
            acc = payload.get("account")
            if not db.q1(conn, "SELECT number FROM accounts WHERE number=?", (acc,)):
                raise AccountingError("Compte inconnu")
            alloc("compte", None, amt, acc)
        elif t == "doublon":
            conn.execute("UPDATE bank_transactions SET status='ignore', comment=? WHERE id=?",
                         (payload.get("comment") or "Doublon", tx_id))
        elif t == "aucun":
            pass
        else:
            raise AccountingError(f"Type d'affectation inconnu : {t}")
        total = conn.execute("SELECT COALESCE(SUM(amount_cents),0) FROM allocations WHERE bank_transaction_id=?", (tx_id,)).fetchone()[0]
        if abs(total) > abs(tx["amount_cents"]) or (total and (total > 0) != (tx["amount_cents"] > 0)):
            raise AccountingError("Le montant affecté dépasse le montant de l'opération.")
        if payload.get("comment"):
            conn.execute("UPDATE bank_transactions SET comment=? WHERE id=?", (payload["comment"], tx_id))
        after = db.q(conn, "SELECT * FROM allocations WHERE bank_transaction_id=?", (tx_id,))
        db.audit(conn, "rapprochement", "operation_bancaire", tx_id, before, after, reason)
        posting.post_bank_transaction(conn, tx_id, reason)
        return {"ok": True, **result}


def apply_suggestion(conn, tx_id):
    tx = db.q1(conn, "SELECT * FROM bank_transactions WHERE id=?", (tx_id,))
    sug = json.loads(tx["suggestion"]) if tx["suggestion"] else None
    if not sug:
        raise AccountingError("Aucune proposition pour cette opération.")
    return classify_transaction(conn, tx_id, sug, reason="Proposition acceptée")


# ================================================================= associé
def add_associate_movement(conn, data: dict) -> dict:
    """Apport, remboursement, capital. Rapproché d'un virement si fourni ou trouvé."""
    with db.tx(conn):
        t = data["type"]
        amount = cents(data.get("amount_cents") or data.get("amount"))
        if amount <= 0:
            raise AccountingError("Le montant doit être positif.")
        tx_id = data.get("bank_transaction_id")
        if tx_id:
            tx = db.q1(conn, "SELECT * FROM bank_transactions WHERE id=?", (tx_id,))
            payload_type = {"apport": "associe", "remboursement": "associe", "capital_libere": "capital"}[t]
            classify_transaction(conn, tx_id, {"type": payload_type, "comment": data.get("comment")})
            mv = db.q1(conn, "SELECT id FROM associate_movements WHERE bank_transaction_id=?", (tx_id,))
            return {"id": mv["id"], "matched": True}
        mv = conn.execute("INSERT INTO associate_movements(date,type,amount_cents,comment,created_at) VALUES(?,?,?,?,?)",
                          (data["date"], t, amount, data.get("comment"), db.now())).lastrowid
        db.audit(conn, "creation", "mouvement_associe", mv, None, data)
        posting.post_associate_movement(conn, mv)
        # Recherche du virement correspondant
        sign = 1 if t in ("apport", "capital_libere") else -1
        cands = db.q(conn, "SELECT * FROM bank_transactions WHERE amount_cents=? AND status IN ('a_traiter','propose')"
                           " AND date BETWEEN date(?, '-10 day') AND date(?, '+10 day')", (sign * amount, data["date"], data["date"]))
        return {"id": mv, "matched": False, "candidates": cands}


def link_movement_to_transaction(conn, mv_id, tx_id):
    with db.tx(conn):
        mv = db.q1(conn, "SELECT * FROM associate_movements WHERE id=?", (mv_id,))
        tx = db.q1(conn, "SELECT * FROM bank_transactions WHERE id=?", (tx_id,))
        sign = 1 if mv["type"] in ("apport", "capital_libere") else -1
        if tx["amount_cents"] != sign * mv["amount_cents"]:
            raise AccountingError("Le montant du virement ne correspond pas.")
        conn.execute("DELETE FROM allocations WHERE bank_transaction_id=?", (tx_id,))
        conn.execute("UPDATE associate_movements SET bank_transaction_id=?, date=? WHERE id=?", (tx_id, tx["date"], mv_id))
        conn.execute("INSERT INTO allocations(bank_transaction_id,target_type,target_id,amount_cents,created_at) VALUES(?,?,?,?,?)",
                     (tx_id, "capital" if mv["type"] == "capital_libere" else "associe", mv_id, tx["amount_cents"], db.now()))
        db.audit(conn, "rapprochement", "mouvement_associe", mv_id, None, {"operation": tx_id})
        posting.post_bank_transaction(conn, tx_id)


def delete_associate_movement(conn, mv_id):
    with db.tx(conn):
        mv = db.q1(conn, "SELECT * FROM associate_movements WHERE id=?", (mv_id,))
        if mv["bank_transaction_id"]:
            conn.execute("DELETE FROM allocations WHERE bank_transaction_id=? AND target_type IN ('associe','capital') AND target_id=?",
                         (mv["bank_transaction_id"], mv_id))
        conn.execute("DELETE FROM associate_movements WHERE id=?", (mv_id,))
        clear_source(conn, "associe", mv_id, "Suppression du mouvement associé")
        db.audit(conn, "suppression", "mouvement_associe", mv_id, mv, None)
        if mv["bank_transaction_id"]:
            posting.post_bank_transaction(conn, mv["bank_transaction_id"])


def associate_summary(conn, date_to=None) -> dict:
    """Situation du compte courant d'associé, calculée depuis le grand livre (compte 455)."""
    p = [date_to] if date_to else []
    cond = " AND e.date<=?" if date_to else ""
    rows = db.q(conn, "SELECT e.date, e.label, e.journal, e.source_type, e.source_id, l.debit_cents, l.credit_cents, l.label line_label"
                      " FROM entry_lines l JOIN entries e ON e.id=l.entry_id WHERE l.account LIKE '455%'" + cond +
                      " ORDER BY e.date, e.id", p)
    contributions = sum(r["credit_cents"] for r in rows if r["source_type"] == "banque")
    advances = sum(r["credit_cents"] for r in rows if r["source_type"] != "banque")
    repayments = sum(r["debit_cents"] for r in rows if r["source_type"] == "banque")
    other_debits = sum(r["debit_cents"] for r in rows if r["source_type"] != "banque")
    credit = sum(r["credit_cents"] for r in rows)
    debit = sum(r["debit_cents"] for r in rows)
    balance = credit - debit
    personal_paid_by_company = db.q(conn, "SELECT SUM(a.amount_cents) s FROM allocations a JOIN expenses x ON x.id=a.target_id JOIN categories c ON c.id=x.category_id"
                                          " WHERE a.target_type='depense' AND c.rule='personnel'")[0]["s"] or 0
    pending = db.q(conn, "SELECT * FROM associate_movements WHERE bank_transaction_id IS NULL AND type IN ('apport','remboursement','capital_libere')")
    capital = db.get_setting(conn, "company").get("capital_cents", 0)
    capital_paid = -conn.execute("SELECT COALESCE(SUM(l.debit_cents-l.credit_cents),0) FROM entry_lines l WHERE l.account='101300'").fetchone()[0]
    return {
        "apports_cents": contributions,
        "avances_frais_cents": advances,
        "total_injecte_cents": contributions + advances,
        "remboursements_cents": repayments,
        "depenses_perso_payees_par_societe_cents": -personal_paid_by_company,
        "autres_debits_cents": other_debits,
        "solde_cents": balance,
        "du_par_la_societe_cents": max(balance, 0),
        "sens": "créditeur (la société vous doit cette somme)" if balance > 0 else
                ("débiteur : vous devez cette somme à la société. Un compte courant débiteur est interdit pour le président d'une SAS (C. com. L225-43 et L227-1) : régularisez rapidement." if balance < 0 else "soldé"),
        "mouvements": rows,
        "en_attente_de_virement": pending,
        "capital_social_cents": capital,
        "capital_libere_comptabilise_cents": capital_paid,
    }


# ================================================================= immobilisations
def days_between(a: date, b: date) -> int:
    return (b - a).days + 1


def depreciation_for_year(asset: dict, fy_start: str, fy_end: str) -> dict:
    """Amortissement linéaire au prorata temporis (jours réels)."""
    start = date.fromisoformat(asset["in_service_date"])
    s, e = date.fromisoformat(fy_start), date.fromisoformat(fy_end)
    end_life = date(start.year + asset["duration_years"], start.month, min(start.day, 28)) - timedelta(days=1)
    if asset.get("disposal_date"):
        end_life = min(end_life, date.fromisoformat(asset["disposal_date"]))
    cost = asset["cost_cents"]
    total_days = days_between(start, date(start.year + asset["duration_years"], start.month, min(start.day, 28)) - timedelta(days=1))
    def cumul(until: date) -> int:
        if until < start:
            return 0
        d = days_between(start, min(until, end_life))
        if until >= end_life and not asset.get("disposal_date"):
            return cost
        return min(cost, prorata(cost, d, total_days))
    before = cumul(s - timedelta(days=1))
    after = cumul(e)
    return {"dotation_cents": after - before, "cumul_debut_cents": before, "cumul_fin_cents": after,
            "vnc_fin_cents": cost - after}


def post_depreciation(conn, fy_id: int, reason="Dotations aux amortissements") -> list:
    fy = db.q1(conn, "SELECT * FROM fiscal_years WHERE id=?", (fy_id,))
    out = []
    for a in db.q(conn, "SELECT * FROM fixed_assets WHERE in_service_date<=?", (fy["end_date"],)):
        sid = fy_id * 1_000_000 + a["id"]
        clear_source(conn, "amortissement", sid, reason)
        dep = depreciation_for_year(a, fy["start_date"], fy["end_date"])
        dep_acc = rules.ASSET_TYPES.get(a["account"], {"dep_account": "281880"})["dep_account"]
        if dep["dotation_cents"] > 0:
            date_ = min(fy["end_date"], a["disposal_date"] or fy["end_date"])
            create_entry(conn, "OD", date_, f"Dotation amortissement {a['label']}",
                         [D("681100", dep["dotation_cents"]), C(dep_acc, dep["dotation_cents"])],
                         piece_ref=f"IMMO{a['id']}", source_type="amortissement", source_id=sid, reason=reason)
        out.append({**a, **dep})
    return out


def dispose_asset(conn, asset_id: int, disposal_date: str, price_cents: int = 0):
    with db.tx(conn):
        a = db.q1(conn, "SELECT * FROM fixed_assets WHERE id=?", (asset_id,))
        conn.execute("UPDATE fixed_assets SET disposal_date=?, disposal_price_cents=? WHERE id=?", (disposal_date, price_cents, asset_id))
        a = db.q1(conn, "SELECT * FROM fixed_assets WHERE id=?", (asset_id,))
        fy = db.q1(conn, "SELECT * FROM fiscal_years WHERE start_date<=? AND end_date>=?", (disposal_date, disposal_date))
        post_depreciation(conn, fy["id"], "Amortissement jusqu'à la sortie")
        dep = depreciation_for_year(a, fy["start_date"], disposal_date)
        dep_acc = rules.ASSET_TYPES.get(a["account"], {"dep_account": "281880"})["dep_account"]
        clear_source(conn, "sortie_immo", asset_id, "Sortie d'immobilisation")
        create_entry(conn, "OD", disposal_date, f"Sortie d'immobilisation {a['label']}",
                     [D(dep_acc, dep["cumul_fin_cents"]), D("675000", a["cost_cents"] - dep["cumul_fin_cents"]),
                      C(a["account"], a["cost_cents"])],
                     piece_ref=f"IMMO{a['id']}", source_type="sortie_immo", source_id=asset_id)
        db.audit(conn, "sortie", "immobilisation", asset_id, None, {"date": disposal_date, "prix": price_cents})
        return {"vnc_cents": a["cost_cents"] - dep["cumul_fin_cents"],
                "note": "Le prix de cession encaissé se rapproche dans Banque en l'imputant au compte 775000."}


def save_fixed_asset(conn, data, asset_id=None):
    with db.tx(conn):
        vals = (data["label"], data.get("account", "218300"), data["acquisition_date"], data.get("in_service_date") or data["acquisition_date"],
                cents(data["cost_cents"]), int(data.get("duration_years") or rules.ASSET_TYPES.get(data.get("account", "218300"), {"years": 3})["years"]))
        if asset_id:
            before = db.q1(conn, "SELECT * FROM fixed_assets WHERE id=?", (asset_id,))
            conn.execute("UPDATE fixed_assets SET label=?,account=?,acquisition_date=?,in_service_date=?,cost_cents=?,duration_years=? WHERE id=?", vals + (asset_id,))
            db.audit(conn, "modification", "immobilisation", asset_id, before, data)
            if before["expense_id"]:
                posting.post_expense(conn, before["expense_id"], "Modification de l'immobilisation")
        else:
            asset_id = conn.execute("INSERT INTO fixed_assets(label,account,acquisition_date,in_service_date,cost_cents,duration_years,created_at) VALUES(?,?,?,?,?,?,?)",
                                    vals + (db.now(),)).lastrowid
            db.audit(conn, "creation", "immobilisation", asset_id, None, data)
        return {"id": asset_id}

"""Application web locale (FastAPI). Lancement : python lancer.py"""
import json
import threading
import time
from contextlib import asynccontextmanager
from datetime import date
from pathlib import Path

from fastapi import FastAPI, File, Form, HTTPException, Request, UploadFile
from fastapi.responses import FileResponse, HTMLResponse, JSONResponse, RedirectResponse, Response
from fastapi.staticfiles import StaticFiles

from . import (ai, backup, checks, closing, config, db, exports, ledger, posting, qonto, remote, reports, rules, services, tax, vat)
from .ledger import AccountingError

STATIC = Path(__file__).parent / "static"
_sync_state = {"running": False, "last_error": None, "last_result": None}


def conn():
    return db.get_conn()


def _auto_sync_loop():
    while True:
        try:
            c = db.get_conn()
            q = db.get_setting(c, "qonto")
            minutes = int(q.get("auto_sync_minutes") or 0)
            if q.get("enabled") and minutes > 0 and not _sync_state["running"]:
                last = q.get("last_sync")
                due = not last or (time.time() - _ts(last)) > minutes * 60
                if due:
                    _run_sync(c)
        except Exception as e:  # la synchro ne doit jamais faire tomber l'application
            _sync_state["last_error"] = str(e)
        time.sleep(60)


def _ts(iso):
    from datetime import datetime
    try:
        return datetime.fromisoformat(iso).timestamp()
    except ValueError:
        return 0


def _run_sync(c, full=False):
    _sync_state["running"] = True
    try:
        res = qonto.sync(c, full)
        _sync_state.update(last_result=res, last_error=None)
        return res
    except qonto.QontoError as e:
        _sync_state["last_error"] = str(e)
        raise
    except Exception as e:
        _sync_state["last_error"] = f"Synchronisation impossible (connexion ?) : {e}"
        raise qonto.QontoError(_sync_state["last_error"]) from e
    finally:
        _sync_state["running"] = False


@asynccontextmanager
async def lifespan(app):
    c = db.get_conn()
    backup.daily_backup_if_needed()
    if not config.is_demo() and not _sync_state.get("thread"):
        _sync_state["thread"] = True
        threading.Thread(target=_auto_sync_loop, daemon=True).start()
    yield


app = FastAPI(title="Aurea Compta", lifespan=lifespan)


app.state.restart = False
PUBLIC_PATHS = {"/login", "/api/login", "/manifest.webmanifest", "/static/app.css", "/static/icon.svg",
                "/static/icon-180.png", "/static/icon-192.png", "/static/icon-512.png"}


@app.middleware("http")
async def guard(request: Request, call_next):
    """L'ordinateur accède librement ; tout autre appareil doit être connecté."""
    host = request.client.host if request.client else None
    if remote.is_local(host):
        return await call_next(request)
    if not remote.settings(db.get_conn()).get("enabled") or not remote.password_set():
        return JSONResponse({"detail": "Accès distant désactivé"}, status_code=403)
    if request.url.path in PUBLIC_PATHS or remote.valid_session(request.cookies.get("aurea_session")):
        return await call_next(request)
    if request.url.path.startswith("/api/"):
        return JSONResponse({"detail": "Connexion requise"}, status_code=401)
    return RedirectResponse("/login", status_code=303)


def _local_only(request: Request):
    if not remote.is_local(request.client.host if request.client else None):
        raise HTTPException(403, "Ce réglage se modifie uniquement depuis l'ordinateur.")


@app.get("/login")
def login_page():
    return HTMLResponse(remote.LOGIN_PAGE.replace("{error}", ""))


@app.post("/api/login")
async def login(request: Request):
    ip = request.client.host if request.client else "?"
    if remote.too_many_attempts(ip):
        return HTMLResponse(remote.LOGIN_PAGE.replace("{error}", '<div class="alert bad">Trop d\'essais : réessayez dans 15 minutes.</div>'), status_code=429)
    form = await request.form()
    if not remote.check_password(str(form.get("password") or "")):
        remote.record_failure(ip)
        return HTMLResponse(remote.LOGIN_PAGE.replace("{error}", '<div class="alert bad">Mot de passe incorrect.</div>'), status_code=401)
    r = RedirectResponse("/", status_code=303)
    r.set_cookie("aurea_session", remote.make_session(), max_age=remote.SESSION_DAYS * 86400, httponly=True, samesite="strict")
    return r


@app.get("/api/remote")
def remote_info(request: Request):
    c = conn()
    s = remote.settings(c)
    port = request.url.port or s.get("port", 8765)
    addrs = remote.local_addresses()
    urls = [{"url": f"http://{a['ip']}:{port}", "type": a["type"]} for a in addrs]
    main_url = next((u["url"] for u in urls if u["type"] == "wifi"), urls[0]["url"] if urls else None)
    return {"enabled": s.get("enabled", False), "password_set": remote.password_set(), "urls": urls,
            "qr": remote.qr_svg(main_url) if main_url and s.get("enabled") else None,
            "listening_network": getattr(request.app.state, "host", None) == "0.0.0.0",
            "is_local": remote.is_local(request.client.host if request.client else None)}


@app.put("/api/remote")
def remote_update(request: Request, data: dict):
    _local_only(request)
    c = conn()
    if data.get("password"):
        remote.set_password(data["password"])
    if data.get("enabled") and not remote.password_set():
        raise AccountingError("Choisissez d'abord un mot de passe.")
    s = remote.settings(c)
    s["enabled"] = bool(data.get("enabled"))
    db.set_setting(c, "remote", s)
    return {"ok": True, "restart_needed": s["enabled"] != (getattr(request.app.state, "host", None) == "0.0.0.0")}


@app.post("/api/restart")
def restart(request: Request):
    _local_only(request)
    server = getattr(request.app.state, "server", None)
    if server is None:
        raise AccountingError("Relancez l'application manuellement.")
    request.app.state.restart = True
    server.should_exit = True
    return {"ok": True}


@app.post("/api/shutdown")
def shutdown(request: Request):
    _local_only(request)
    server = getattr(request.app.state, "server", None)
    if server is None:
        raise AccountingError("Arrêt automatique indisponible : fermez la fenêtre de lancement.")
    request.app.state.restart = False
    server.should_exit = True
    return {"ok": True}


@app.get("/manifest.webmanifest")
def manifest():
    return JSONResponse({
        "name": "Aurea Compta", "short_name": "Aurea Compta", "start_url": "/", "display": "standalone",
        "background_color": "#f7f7f5", "theme_color": "#9a7a2e", "lang": "fr",
        "icons": [{"src": "/static/icon-192.png", "sizes": "192x192", "type": "image/png"},
                  {"src": "/static/icon-512.png", "sizes": "512x512", "type": "image/png"}]},
        media_type="application/manifest+json")


@app.exception_handler(AccountingError)
async def acc_err(request: Request, exc: AccountingError):
    return JSONResponse({"detail": str(exc)}, status_code=400)


@app.exception_handler(qonto.QontoError)
async def qonto_err(request: Request, exc):
    return JSONResponse({"detail": str(exc)}, status_code=502)


@app.exception_handler(RuntimeError)
async def rt_err(request: Request, exc):
    return JSONResponse({"detail": str(exc)}, status_code=502)


@app.exception_handler(ValueError)
async def val_err(request: Request, exc):
    return JSONResponse({"detail": str(exc)}, status_code=400)


# ================================================================= vue d'ensemble
@app.get("/api/overview")
def overview():
    c = conn()
    fy = closing.fiscal_year(c)
    inc = reports.income_statement(c, fy["start_date"], fy["end_date"])
    q = db.get_setting(c, "qonto")
    a = services.associate_summary(c)
    to_review = c.execute("SELECT COUNT(*) FROM bank_transactions WHERE status IN ('a_traiter','propose')").fetchone()[0]
    exp_review = c.execute("SELECT COUNT(*) FROM expenses WHERE status IN ('a_valider','propose_ia','importe')").fetchone()[0]
    missing_docs = c.execute("SELECT COUNT(*) FROM expenses x LEFT JOIN categories c ON c.id=x.category_id WHERE x.document_id IS NULL AND IFNULL(c.rule,'')<>'personnel'").fetchone()[0]
    docs_pending = c.execute("SELECT COUNT(*) FROM documents d WHERE d.kind='justificatif' AND d.id NOT IN (SELECT document_id FROM expenses WHERE document_id IS NOT NULL) AND d.id NOT IN (SELECT document_id FROM sales_invoices WHERE document_id IS NOT NULL)").fetchone()[0]
    confirmed = db.get_setting(c, "confirmed")
    book_balance = ledger.account_balance(c, "512")
    return {
        "exercice": fy,
        "solde_bancaire_cents": q.get("balance_cents") if q.get("balance_cents") is not None else book_balance,
        "solde_source": f"Qonto au {q.get('balance_at', '')[:16].replace('T', ' ')}" if q.get("balance_cents") is not None else "comptabilité",
        "solde_comptable_cents": book_balance,
        "recettes_cents": inc["chiffre_affaires"],
        "depenses_cents": inc["total_charges"] - inc["impot"],
        "resultat_cents": inc["resultat_avant_impot"],
        "tva": vat.current_estimate(c),
        "apports_non_rembourses_cents": a["du_par_la_societe_cents"],
        "compte_courant_cents": a["solde_cents"],
        "a_verifier": {"operations": to_review, "depenses": exp_review, "justificatifs_manquants": missing_docs, "pieces_non_traitees": docs_pending},
        "parametres_provisoires": [k for k, v in confirmed.items() if not v],
        "sync": {**{k: v for k, v in _sync_state.items() if k != "thread"}, "last_sync": q.get("last_sync"), "enabled": q.get("enabled")},
        "mode_demo": config.is_demo(),
    }


# ================================================================= paramètres
@app.get("/api/settings")
def get_settings():
    c = conn()
    return {k: db.get_setting(c, k) for k in db.DEFAULT_SETTINGS} | {"secrets": config.masked_secrets(), "data_dir": str(config.data_dir()),
                                                                      "demo": config.is_demo()}


@app.put("/api/settings/{key}")
def put_setting(key: str, value: dict):
    if key not in db.DEFAULT_SETTINGS and key != "bank_statement":
        raise HTTPException(404)
    c = conn()
    with db.tx(c):
        db.set_setting(c, key, value)
        if key in ("company", "tax"):
            conf = db.get_setting(c, "confirmed")
            conf[key] = True
            db.set_setting(c, "confirmed", conf)
        if key == "tax":
            # L'exigibilité dépend de l'option débits : régénération des écritures de vente
            for inv in db.q(c, "SELECT id FROM sales_invoices"):
                posting.post_sales_invoice(c, inv["id"], "Changement de régime de TVA")
            for t in db.q(c, "SELECT DISTINCT bank_transaction_id t FROM allocations WHERE target_type='vente'"):
                posting.post_bank_transaction(c, t["t"], "Changement de régime de TVA")
    return {"ok": True}


@app.put("/api/secrets")
def put_secrets(request: Request, values: dict):
    _local_only(request)
    for k, v in values.items():
        if k in config.SECRET_KEYS and v is not None:
            config.save_secret(k, v)
    return config.masked_secrets()


@app.get("/api/fiscal-years")
def fiscal_years():
    return db.q(conn(), "SELECT * FROM fiscal_years ORDER BY start_date")


@app.post("/api/fiscal-years")
def add_fiscal_year(data: dict):
    c = conn()
    if data["end_date"] <= data["start_date"]:
        raise AccountingError("Dates d'exercice invalides.")
    overlap = db.q1(c, "SELECT id FROM fiscal_years WHERE NOT (end_date<? OR start_date>?)", (data["start_date"], data["end_date"]))
    if overlap and not data.get("replace_id"):
        raise AccountingError("Cet exercice chevauche un exercice existant.")
    with db.tx(c):
        if data.get("replace_id"):
            fy = db.q1(c, "SELECT * FROM fiscal_years WHERE id=?", (data["replace_id"],))
            if fy["status"] == "closed":
                raise AccountingError("Exercice clôturé.")
            c.execute("UPDATE fiscal_years SET start_date=?, end_date=? WHERE id=?", (data["start_date"], data["end_date"], fy["id"]))
            db.audit(c, "modification", "exercice", fy["id"], fy, data)
        else:
            c.execute("INSERT INTO fiscal_years(start_date,end_date) VALUES(?,?)", (data["start_date"], data["end_date"]))
    return {"ok": True}


@app.get("/api/categories")
def categories():
    return db.q(conn(), "SELECT * FROM categories ORDER BY is_asset, name")


@app.post("/api/categories")
def save_category(data: dict):
    c = conn()
    with db.tx(c):
        if data.get("id"):
            before = db.q1(c, "SELECT * FROM categories WHERE id=?", (data["id"],))
            c.execute("UPDATE categories SET name=?, account=?, vat_recovery=?, deductible=?, active=? WHERE id=?",
                      (data["name"], data["account"], int(data["vat_recovery"]), int(data["deductible"]), int(data.get("active", 1)), data["id"]))
            db.audit(c, "modification", "categorie", data["id"], before, data)
        else:
            c.execute("INSERT INTO categories(code,name,account,vat_recovery,deductible,rule) VALUES(?,?,?,?,?,?)",
                      (services.slug(data["name"]), data["name"], data["account"], int(data.get("vat_recovery", 100)), int(data.get("deductible", 100)), "standard"))
    return {"ok": True}


@app.get("/api/accounts")
def accounts():
    return db.q(conn(), "SELECT * FROM accounts ORDER BY number")


@app.post("/api/accounts")
def add_account(data: dict):
    c = conn()
    if not data.get("number", "").isdigit():
        raise AccountingError("Numéro de compte invalide.")
    c.execute("INSERT OR REPLACE INTO accounts(number,label,custom) VALUES(?,?,1)", (data["number"].ljust(6, "0"), data["label"]))
    return {"ok": True}


@app.get("/api/parties")
def parties(kind: str | None = None):
    c = conn()
    return db.q(c, "SELECT * FROM parties WHERE (? IS NULL OR kind=?) ORDER BY name", (kind, kind))


@app.post("/api/parties")
def save_party(data: dict):
    c = conn()
    with db.tx(c):
        if data.get("id"):
            before = db.q1(c, "SELECT * FROM parties WHERE id=?", (data["id"],))
            c.execute("UPDATE parties SET name=?, siren=?, vat_number=?, country=?, email=?, address=? WHERE id=?",
                      (data["name"], data.get("siren"), data.get("vat_number"), data.get("country"), data.get("email"), data.get("address"), data["id"]))
            db.audit(c, "modification", "tiers", data["id"], before, data)
            return {"id": data["id"]}
        return {"id": services.get_or_create_party(c, data["kind"], data["name"], **data)}


# ================================================================= banque
@app.get("/api/transactions")
def transactions(status: str | None = None, month: str | None = None, q: str | None = None):
    c = conn()
    sql, p = "SELECT * FROM bank_transactions WHERE 1=1", []
    if status == "a_verifier":
        sql += " AND status IN ('a_traiter','propose')"
    elif status:
        sql += " AND status=?"
        p.append(status)
    if month:
        sql += " AND substr(date,1,7)=?"
        p.append(month)
    if q:
        sql += " AND (label LIKE ? OR counterparty LIKE ? OR reference LIKE ? OR CAST(amount_cents AS TEXT) LIKE ?)"
        p += [f"%{q}%"] * 3 + [f"%{services.cents(q) if q.replace(',', '').replace('.', '').replace('-', '').isdigit() else q}%"]
    rows = db.q(c, sql + " ORDER BY date DESC, id DESC LIMIT 1000", p)
    for r in rows:
        r["suggestion"] = json.loads(r["suggestion"]) if r["suggestion"] else None
        r["allocations"] = db.q(c, "SELECT * FROM allocations WHERE bank_transaction_id=?", (r["id"],))
        r.pop("raw", None)
    return rows


@app.get("/api/transactions/{tx_id}")
def transaction(tx_id: int):
    c = conn()
    t = db.q1(c, "SELECT * FROM bank_transactions WHERE id=?", (tx_id,))
    if not t:
        raise HTTPException(404)
    t["suggestion"] = json.loads(t["suggestion"]) if t["suggestion"] else None
    t["raw"] = json.loads(t["raw"] or "{}")
    allocs = db.q(c, "SELECT * FROM allocations WHERE bank_transaction_id=?", (tx_id,))
    for a in allocs:
        if a["target_type"] == "depense":
            a["target"] = db.q1(c, "SELECT * FROM expenses WHERE id=?", (a["target_id"],))
        elif a["target_type"] == "vente":
            a["target"] = db.q1(c, "SELECT * FROM sales_invoices WHERE id=?", (a["target_id"],))
    t["allocations"] = allocs
    t["entries"] = [ledger.entry_with_lines(c, e["id"]) for e in ledger.source_entries(c, "banque", tx_id)]
    for a in allocs:
        if a["target_type"] in ("depense", "vente"):
            st = "depense" if a["target_type"] == "depense" else "vente"
            t["entries"] += [ledger.entry_with_lines(c, e["id"]) for e in ledger.source_entries(c, st, a["target_id"])]
    docs = {e["document_id"] for e in t["entries"] if e and e.get("document_id")}
    t["documents"] = [db.q1(c, "SELECT * FROM documents WHERE id=?", (d,)) for d in docs]
    return t


@app.post("/api/transactions/{tx_id}/classify")
def classify(tx_id: int, payload: dict):
    return services.classify_transaction(conn(), tx_id, payload)


@app.post("/api/transactions/{tx_id}/accept")
def accept(tx_id: int):
    return services.apply_suggestion(conn(), tx_id)


@app.post("/api/transactions/accept-all")
def accept_all(min_confidence: float = 0.9):
    c = conn()
    done = 0
    for t in db.q(c, "SELECT id, suggestion FROM bank_transactions WHERE status='propose'"):
        s = json.loads(t["suggestion"] or "null")
        if s and s.get("confidence", 0) >= min_confidence and s["type"] in ("depense", "vente", "depense_nouvelle") and (s["type"] != "depense_nouvelle" or s.get("category_id")):
            services.apply_suggestion(c, t["id"])
            done += 1
    return {"accepted": done}


@app.post("/api/transactions")
def add_manual_transaction(data: dict):
    """Saisie manuelle d'une opération (relevé papier, banque non synchronisée)."""
    c = conn()
    item = {"external_id": f"manuel:{int(time.time()*1000)}", "date": data["date"], "amount_cents": services.cents(data["amount_cents"]),
            "label": data.get("label"), "counterparty": data.get("counterparty") or data.get("label")}
    return services.import_transactions(c, [item], "manuel")


@app.post("/api/qonto/sync")
def qonto_sync(full: bool = False):
    return _run_sync(conn(), full)


@app.post("/api/qonto/test")
def qonto_test():
    org = qonto.organization(conn())
    return {"name": org.get("name") or org.get("legal_name"), "accounts": [
        {"id": a["id"], "name": a.get("name"), "iban": a.get("iban"), "balance_cents": a.get("balance_cents"), "main": a.get("main")}
        for a in org.get("bank_accounts", [])]}


@app.post("/api/import/csv")
async def import_csv(file: UploadFile = File(...)):
    items = qonto.parse_csv(await file.read())
    return services.import_transactions(conn(), items, "csv")


# ================================================================= pièces
@app.post("/api/documents")
async def upload(file: UploadFile = File(...), kind: str = Form("justificatif"), process: bool = Form(True)):
    c = conn()
    content = await file.read()
    if len(content) > 25 * 1024 * 1024:
        raise AccountingError("Fichier trop volumineux (25 Mo maximum).")
    with db.tx(c):
        doc_id = services.store_document(c, file.filename, content, kind)
    result = {"document_id": doc_id}
    if process and kind == "justificatif":
        try:
            with db.tx(c):
                result.update(ai.process_document(c, doc_id))
        except RuntimeError as e:
            result.update(status="erreur", message=str(e))
    return result


@app.get("/api/documents")
def documents(q: str | None = None, unlinked: bool = False):
    c = conn()
    sql = ("SELECT d.*, (SELECT id FROM expenses WHERE document_id=d.id LIMIT 1) expense_id,"
           " (SELECT id FROM sales_invoices WHERE document_id=d.id LIMIT 1) invoice_id FROM documents d WHERE 1=1")
    p = []
    if q:
        sql += " AND (d.original_name LIKE ? OR d.party_name LIKE ? OR d.doc_date LIKE ? OR CAST(d.amount_cents AS TEXT) LIKE ?)"
        amount = str(services.cents(q)) if any(ch.isdigit() for ch in q) and all(ch in "0123456789,. " for ch in q) else q
        p += [f"%{q}%", f"%{q}%", f"%{q}%", f"%{amount}%"]
    rows = db.q(c, sql + " ORDER BY d.id DESC LIMIT 500", p)
    if unlinked:
        rows = [r for r in rows if not r["expense_id"] and not r["invoice_id"]]
    for r in rows:
        r["ai_data"] = json.loads(r["ai_data"]) if r["ai_data"] else None
    return rows


@app.get("/api/documents/{doc_id}/file")
def document_file(doc_id: int):
    d = db.q1(conn(), "SELECT * FROM documents WHERE id=?", (doc_id,))
    if not d:
        raise HTTPException(404)
    return FileResponse(services.document_path(d), media_type=d["mime"], filename=d["original_name"],
                        content_disposition_type="inline")


@app.post("/api/documents/{doc_id}/process")
def process_doc(doc_id: int):
    c = conn()
    with db.tx(c):
        return ai.process_document(c, doc_id)


@app.post("/api/documents/{doc_id}/confirm")
def confirm_doc(doc_id: int, payload: dict):
    """Validation de la proposition : rattacher à une opération, une dépense, ou créer la dépense."""
    c = conn()
    action = payload.get("action")
    if action == "transaction":
        r = services.classify_transaction(c, int(payload["transaction_id"]), {**payload.get("expense", {}), "type": "depense_nouvelle", "document_id": doc_id})
        return r
    if action == "expense":
        return services.save_expense(c, {"document_id": doc_id}, int(payload["expense_id"]), "Justificatif rattaché")
    if action == "new_expense":
        data = {**payload["expense"], "document_id": doc_id}
        return services.save_expense(c, data, reason="Dépense créée depuis un justificatif")
    raise AccountingError("Action inconnue")


@app.delete("/api/documents/{doc_id}")
def delete_doc(doc_id: int):
    c = conn()
    d = db.q1(c, "SELECT * FROM documents WHERE id=?", (doc_id,))
    used = db.q1(c, "SELECT id FROM expenses WHERE document_id=? UNION SELECT id FROM sales_invoices WHERE document_id=? UNION SELECT id FROM entries WHERE document_id=?", (doc_id, doc_id, doc_id))
    if d["locked"] or used:
        raise AccountingError("Pièce rattachée à la comptabilité : suppression interdite.")
    with db.tx(c):
        c.execute("DELETE FROM documents WHERE id=?", (doc_id,))
        db.audit(c, "suppression", "piece", doc_id, d, None)
    p = services.document_path(d)
    if p.exists():
        p.unlink()
    return {"ok": True}


# ================================================================= recettes
@app.get("/api/invoices")
def invoices():
    c = conn()
    rows = db.q(c, "SELECT s.*, p.name client FROM sales_invoices s JOIN parties p ON p.id=s.client_id ORDER BY s.issue_date DESC, s.number DESC")
    for r in rows:
        r.update(services.invoice_status(c, r))
    return rows


@app.get("/api/invoices/next-number")
def next_number(kind: str = "facture", issue_date: str | None = None):
    return {"number": services.next_invoice_number(conn(), kind, issue_date or date.today().isoformat())}


@app.get("/api/invoices/{inv_id}")
def invoice(inv_id: int):
    c = conn()
    r = db.q1(c, "SELECT s.*, p.name client, p.vat_number client_vat, p.country client_country FROM sales_invoices s JOIN parties p ON p.id=s.client_id WHERE s.id=?", (inv_id,))
    if not r:
        raise HTTPException(404)
    r.update(services.invoice_status(c, r))
    r["lines"] = db.q(c, "SELECT * FROM sales_invoice_lines WHERE invoice_id=?", (inv_id,))
    r["payments"] = db.q(c, "SELECT a.*, t.date, t.label FROM allocations a JOIN bank_transactions t ON t.id=a.bank_transaction_id WHERE a.target_type='vente' AND a.target_id=?", (inv_id,))
    r["credit_notes"] = db.q(c, "SELECT id, number, ttc_cents, issue_date FROM sales_invoices WHERE credited_invoice_id=?", (inv_id,))
    r["entries"] = [ledger.entry_with_lines(c, e["id"]) for e in ledger.source_entries(c, "vente", inv_id)]
    return r


@app.post("/api/invoices")
def create_invoice(data: dict):
    return services.save_sales_invoice(conn(), data)


@app.put("/api/invoices/{inv_id}")
def update_invoice(inv_id: int, data: dict):
    return services.save_sales_invoice(conn(), data, inv_id, data.get("reason") or "Modification de la facture")


@app.delete("/api/invoices/{inv_id}")
def remove_invoice(inv_id: int):
    services.delete_sales_invoice(conn(), inv_id)
    return {"ok": True}


# ================================================================= dépenses
@app.get("/api/expenses")
def expenses(month: str | None = None, status: str | None = None, payment: str | None = None):
    c = conn()
    sql = ("SELECT x.*, c.name category, c.rule FROM expenses x LEFT JOIN categories c ON c.id=x.category_id WHERE 1=1")
    p = []
    if month:
        sql += " AND substr(x.date,1,7)=?"
        p.append(month)
    if status:
        sql += " AND x.status=?"
        p.append(status)
    if payment:
        sql += " AND x.payment_method=?"
        p.append(payment)
    rows = db.q(c, sql + " ORDER BY x.date DESC, x.id DESC LIMIT 2000", p)
    for r in rows:
        r["paid_cents"] = services.expense_paid(c, r)
    return rows


@app.get("/api/expenses/{exp_id}")
def expense(exp_id: int):
    c = conn()
    r = db.q1(c, "SELECT x.*, c.name category, c.rule, c.help category_help FROM expenses x LEFT JOIN categories c ON c.id=x.category_id WHERE x.id=?", (exp_id,))
    if not r:
        raise HTTPException(404)
    cat = db.q1(c, "SELECT * FROM categories WHERE id=?", (r["category_id"],)) if r["category_id"] else None
    r["warnings"] = services.fiscal_warnings(c, r, cat)
    r["rule_explanation"] = rules.RULE_EXPLANATIONS.get(cat["rule"]) if cat else None
    r["paid_cents"] = services.expense_paid(c, r)
    r["payments"] = db.q(c, "SELECT a.*, t.date, t.label FROM allocations a JOIN bank_transactions t ON t.id=a.bank_transaction_id WHERE a.target_type='depense' AND a.target_id=?", (exp_id,))
    r["entries"] = [ledger.entry_with_lines(c, e["id"]) for e in ledger.source_entries(c, "depense", exp_id)]
    for pmt in r["payments"]:
        r["entries"] += [ledger.entry_with_lines(c, e["id"]) for e in ledger.source_entries(c, "banque", pmt["bank_transaction_id"])]
    r["document"] = db.q1(c, "SELECT * FROM documents WHERE id=?", (r["document_id"],)) if r["document_id"] else None
    r["history"] = db.q(c, "SELECT * FROM audit_log WHERE object_type='depense' AND object_id=? ORDER BY id DESC", (exp_id,))
    return r


@app.post("/api/expenses")
def create_expense(data: dict):
    return services.save_expense(conn(), data)


@app.put("/api/expenses/{exp_id}")
def update_expense(exp_id: int, data: dict):
    return services.save_expense(conn(), data, exp_id, data.pop("reason", None) or "Modification de la dépense")


@app.post("/api/expenses/{exp_id}/validate")
def validate_expense(exp_id: int):
    c = conn()
    r = services.save_expense(c, {"status": "valide"}, exp_id, "Validation de la dépense")
    with db.tx(c):
        for e in ledger.source_entries(c, "depense", exp_id):
            ledger.validate_entry(c, e["id"])
    return r


@app.delete("/api/expenses/{exp_id}")
def remove_expense(exp_id: int):
    services.delete_expense(conn(), exp_id)
    return {"ok": True}


# ================================================================= associé
@app.get("/api/associate")
def associate():
    c = conn()
    s = services.associate_summary(c)
    s["movements_list"] = db.q(c, "SELECT * FROM associate_movements ORDER BY date DESC, id DESC")
    s["perso_expenses"] = db.q(c, "SELECT x.*, c.name category FROM expenses x LEFT JOIN categories c ON c.id=x.category_id WHERE x.payment_method='perso' ORDER BY date DESC")
    s["personal_paid"] = db.q(c, "SELECT x.*, c.name category FROM expenses x JOIN categories c ON c.id=x.category_id WHERE c.rule='personnel' ORDER BY date DESC")
    return s


@app.post("/api/associate/movements")
def add_movement(data: dict):
    return services.add_associate_movement(conn(), data)


@app.post("/api/associate/movements/{mv_id}/link")
def link_movement(mv_id: int, data: dict):
    services.link_movement_to_transaction(conn(), mv_id, int(data["transaction_id"]))
    return {"ok": True}


@app.delete("/api/associate/movements/{mv_id}")
def del_movement(mv_id: int):
    services.delete_associate_movement(conn(), mv_id)
    return {"ok": True}


# ================================================================= immobilisations
@app.get("/api/assets")
def assets():
    c = conn()
    fy = closing.fiscal_year(c)
    rows = db.q(c, "SELECT * FROM fixed_assets ORDER BY acquisition_date")
    for a in rows:
        a.update(services.depreciation_for_year(a, fy["start_date"], fy["end_date"]))
    return {"exercice": fy, "assets": rows, "types": rules.ASSET_TYPES}


@app.post("/api/assets")
def add_asset(data: dict):
    return services.save_fixed_asset(conn(), data, data.get("id"))


@app.post("/api/assets/{asset_id}/dispose")
def dispose(asset_id: int, data: dict):
    return services.dispose_asset(conn(), asset_id, data["date"], services.cents(data.get("price_cents") or 0))


# ================================================================= TVA
@app.get("/api/vat/periods")
def vat_periods(year: int | None = None):
    return vat.periods(conn(), year or date.today().year)


@app.get("/api/vat/compute")
def vat_compute(start: str, end: str):
    return vat.compute(conn(), start, end)


@app.post("/api/vat/prepare")
def vat_prepare(data: dict):
    return vat.prepare(conn(), data["start"], data["end"])


@app.post("/api/vat/returns/{rid}/validate")
def vat_validate(rid: int, force: bool = False):
    return vat.validate(conn(), rid, force)


@app.post("/api/vat/returns/{rid}/filed")
def vat_filed(rid: int):
    vat.mark_filed(conn(), rid)
    return {"ok": True}


@app.post("/api/vat/returns/{rid}/adjust")
def vat_adjust(rid: int, data: dict):
    vat.set_adjustment(conn(), rid, data["line"], services.cents(data["amount_cents"]))
    return {"ok": True}


@app.get("/api/vat/export.zip")
def vat_export(start: str, end: str):
    return Response(exports.vat_dossier(conn(), start, end), media_type="application/zip",
                    headers={"Content-Disposition": f'attachment; filename="tva-{start}-{end}.zip"'})


# ================================================================= comptabilité
@app.get("/api/entries")
def entries(start: str | None = None, end: str | None = None, journal: str | None = None, status: str | None = None):
    c = conn()
    rows = reports.journal(c, start, end, journal)
    if status:
        rows = [r for r in rows if r["status"] == status]
    return rows[-1500:]


@app.post("/api/entries")
def add_entry(data: dict):
    return {"id": closing.manual_inventory_entry(conn(), data)}


@app.post("/api/entries/validate")
def validate_entries(data: dict):
    c = conn()
    n = 0
    with db.tx(c):
        for e in db.q(c, "SELECT id FROM entries WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date,id", (data["start"], data["end"])):
            ledger.validate_entry(c, e["id"])
            n += 1
    return {"validated": n}


@app.post("/api/entries/{entry_id}/reverse")
def reverse(entry_id: int, data: dict):
    c = conn()
    with db.tx(c):
        e = db.q1(c, "SELECT * FROM entries WHERE id=?", (entry_id,))
        if e["source_type"] not in ("manuel", None):
            raise AccountingError("Écriture générée automatiquement : corrigez plutôt sa source (dépense, facture, opération).")
        return {"id": ledger.reverse_entry(c, entry_id, data.get("date"), data.get("reason") or "Correction")}


@app.get("/api/reports/{name}")
def report(name: str, start: str | None = None, end: str | None = None, account: str | None = None, fy_id: int | None = None):
    c = conn()
    fy = closing.fiscal_year(c, fy_id)
    start, end = start or fy["start_date"], end or fy["end_date"]
    if name == "balance":
        return reports.trial_balance(c, start, end)
    if name == "grand-livre":
        return reports.general_ledger(c, start, end, account)
    if name == "resultat":
        return reports.income_statement(c, start, end)
    if name == "bilan":
        return reports.balance_sheet(c, fy)
    if name == "integrite":
        return ledger.check_global_balance(c)
    if name == "rapprochement":
        return checks.bank_reconciliation(c, end)
    raise HTTPException(404)


@app.get("/api/audit")
def audit_log(limit: int = 300):
    return db.q(conn(), "SELECT * FROM audit_log ORDER BY id DESC LIMIT ?", (limit,))


@app.get("/api/tax/estimate")
def tax_estimate(fy_id: int | None = None):
    c = conn()
    return tax.estimate(c, closing.fiscal_year(c, fy_id))


# ================================================================= mois
@app.get("/api/month/{month}/check")
def month_check(month: str):
    return checks.verify_month(conn(), month)


@app.post("/api/month/{month}/close")
def month_close(month: str, force: bool = False):
    return checks.close_month(conn(), month, force)


@app.get("/api/month/{month}/export.zip")
def month_export(month: str):
    return Response(exports.month_dossier(conn(), month), media_type="application/zip",
                    headers={"Content-Disposition": f'attachment; filename="dossier-{month}.zip"'})


# ================================================================= clôture
@app.get("/api/closing/prepare")
def closing_prepare(fy_id: int | None = None):
    return closing.prepare(conn(), fy_id)


@app.post("/api/closing/{fy_id}/close")
def closing_close(fy_id: int, force: bool = False):
    return closing.close(conn(), fy_id, force)


@app.post("/api/closing/{fy_id}/allocate")
def closing_allocate(fy_id: int, data: dict):
    return {"entry_id": closing.allocate_result(conn(), fy_id, services.cents(data.get("legal_reserve_cents") or 0),
                                                services.cents(data.get("dividends_cents") or 0), data.get("date"))}


@app.get("/api/closing/{fy_id}/export.zip")
def closing_export(fy_id: int):
    c = conn()
    fy = closing.fiscal_year(c, fy_id)
    return Response(exports.annual_dossier(c, fy), media_type="application/zip",
                    headers={"Content-Disposition": f'attachment; filename="dossier-annuel-{fy["end_date"][:4]}.zip"'})


@app.get("/api/closing/{fy_id}/fec")
def closing_fec(fy_id: int):
    c = conn()
    name, content = exports.fec(c, closing.fiscal_year(c, fy_id))
    return Response(content.encode("utf-8"), media_type="text/plain", headers={"Content-Disposition": f'attachment; filename="{name}"'})


@app.get("/api/closing/{fy_id}/statements/{name}")
def closing_statement(fy_id: int, name: str):
    c = conn()
    files = exports.statements_pdf(c, closing.fiscal_year(c, fy_id))
    if name not in files:
        raise HTTPException(404)
    return Response(files[name], media_type="application/pdf", headers={"Content-Disposition": f'inline; filename="{name}"'})


# ================================================================= sauvegardes
@app.get("/api/backups")
def backups():
    return backup.list_backups()


@app.post("/api/backups")
def backup_now():
    return {"file": backup.backup_db("manuelle").name}


@app.get("/api/backup/export.zip")
def full_export():
    return Response(backup.full_export(), media_type="application/zip",
                    headers={"Content-Disposition": f'attachment; filename="aurea-compta-export-{date.today()}.zip"'})


@app.post("/api/backup/restore")
async def restore(file: UploadFile = File(...)):
    return backup.restore(await file.read())


# ================================================================= assistant et recherche
@app.post("/api/assistant")
def assistant(data: dict):
    return ai.ask(conn(), data["question"])


@app.get("/api/search")
def search(q: str):
    c = conn()
    like = f"%{q}%"
    amount = None
    try:
        amount = abs(services.cents(q)) if any(ch.isdigit() for ch in q) else None
    except ValueError:
        pass
    res = {
        "transactions": db.q(c, "SELECT id, date, counterparty, label, amount_cents, status FROM bank_transactions WHERE label LIKE ? OR counterparty LIKE ? OR date LIKE ? OR abs(amount_cents)=? LIMIT 20", (like, like, like, amount or -1)),
        "depenses": db.q(c, "SELECT id, date, supplier_name, label, ttc_cents, status FROM expenses WHERE supplier_name LIKE ? OR label LIKE ? OR date LIKE ? OR ttc_cents=? LIMIT 20", (like, like, like, amount or -1)),
        "factures": db.q(c, "SELECT s.id, s.number, s.issue_date, p.name client, s.ttc_cents FROM sales_invoices s JOIN parties p ON p.id=s.client_id WHERE s.number LIKE ? OR p.name LIKE ? OR s.issue_date LIKE ? OR s.ttc_cents=? LIMIT 20", (like, like, like, amount or -1)),
        "pieces": db.q(c, "SELECT id, original_name, party_name, doc_date, amount_cents FROM documents WHERE original_name LIKE ? OR party_name LIKE ? OR doc_date LIKE ? OR amount_cents=? LIMIT 20", (like, like, like, amount or -1)),
    }
    return res


# ================================================================= interface
app.mount("/static", StaticFiles(directory=STATIC), name="static")


@app.get("/")
def index():
    return FileResponse(STATIC / "index.html")

"""Synchronisation Qonto par l'API officielle (Business API v2) et import CSV de secours.

Documentation : https://docs.qonto.com/
* Authentification par clé API : en-tête « Authorization: {identifiant}:{clé secrète} »
  (clé générée dans Qonto > Intégrations et partenariats > Clé API, lecture seule).
* GET /v2/organization : comptes bancaires et soldes.
* GET /v2/transactions : opérations, paginées (per_page 100, meta.next_page),
  filtre updated_at_from pour ne récupérer que les nouveautés.
* GET /v2/attachments/{id} : justificatif, URL valable 30 minutes.
Aucune récupération non officielle (scraping) n'est utilisée.
"""
import csv
import hashlib
import io
import json
from datetime import datetime, timedelta

import httpx

from . import config, db, services

BASES = {"production": "https://thirdparty.qonto.com", "sandbox": "https://thirdparty-sandbox.staging.qonto.co"}


class QontoError(Exception):
    pass


def _client(conn) -> httpx.Client:
    s = config.load_secrets()
    if not s.get("qonto_login") or not s.get("qonto_secret_key"):
        raise QontoError("Identifiant et clé secrète Qonto non configurés (Paramètres > Qonto).")
    q = db.get_setting(conn, "qonto")
    headers = {"Authorization": f"{s['qonto_login']}:{s['qonto_secret_key']}", "Accept": "application/json"}
    if q.get("environment") == "sandbox" and s.get("qonto_staging_token"):
        headers["X-Qonto-Staging-Token"] = s["qonto_staging_token"]
    return httpx.Client(base_url=BASES.get(q.get("environment"), BASES["production"]), headers=headers, timeout=30)


def _get(client, path, params=None):
    for attempt in range(4):
        r = client.get(path, params=params)
        if r.status_code in (429, 503):
            import time
            time.sleep(min(int(r.headers.get("Retry-After", "2")), 10) * (attempt + 1))
            continue
        if r.status_code == 401:
            raise QontoError("Qonto refuse l'authentification : vérifiez l'identifiant et la clé API.")
        if r.status_code >= 400:
            raise QontoError(f"Erreur Qonto {r.status_code} : {r.text[:200]}")
        return r.json()
    raise QontoError("Qonto indisponible (trop de requêtes), réessayez plus tard.")


def organization(conn) -> dict:
    with _client(conn) as c:
        return _get(c, "/v2/organization")["organization"]


def normalize(t: dict, bank_account_id: str | None = None) -> dict:
    amount = t.get("amount_cents")
    if amount is None:
        amount = services.to_cents(str(t.get("amount", 0)))
    signed = amount if t.get("side") == "credit" else -amount
    vat_cents = t.get("vat_amount_cents")
    vat_rate = t.get("vat_rate")
    return {
        "external_id": f"qonto:{t['id']}",
        "bank_account": t.get("bank_account_id") or bank_account_id,
        "date": (t.get("settled_at") or t.get("emitted_at"))[:10],
        "emitted_at": t.get("emitted_at"),
        "amount_cents": signed,
        "label": t.get("label"),
        "counterparty": t.get("clean_counterparty_name") or t.get("label"),
        "operation_type": t.get("operation_type"),
        "reference": t.get("reference"),
        "note": t.get("note"),
        "vat_cents": vat_cents if vat_cents not in (None, 0) else None,
        "vat_rate": (str(vat_rate) if vat_rate not in (None, -1, "-1") else None),
        "attachment_ids": t.get("attachment_ids") or [],
        "raw": t,
    }


def sync(conn, full=False) -> dict:
    """Synchronise les nouvelles opérations (ou tout l'historique si full)."""
    q = db.get_setting(conn, "qonto")
    started = datetime.now().astimezone()
    with _client(conn) as c:
        org = _get(c, "/v2/organization")["organization"]
        accounts = [a for a in org.get("bank_accounts", []) if a.get("status", "active") == "active"]
        if q.get("bank_account_id"):
            accounts = [a for a in accounts if a["id"] == q["bank_account_id"]] or accounts
        main = next((a for a in accounts if a.get("main")), accounts[0] if accounts else None)
        if not main:
            raise QontoError("Aucun compte bancaire actif trouvé.")
        params = {"bank_account_id": main["id"], "status[]": ["completed"], "sort_by": "updated_at:asc", "per_page": 100,
                  "includes[]": ["vat_details", "attachments"]}
        if q.get("last_sync") and not full:
            params["updated_at_from"] = (datetime.fromisoformat(q["last_sync"]) - timedelta(hours=1)).isoformat()
        items, page = [], 1
        while True:
            params["page"] = page
            data = _get(c, "/v2/transactions", params)
            items += [normalize(t, main["id"]) for t in data.get("transactions", []) if t.get("settled_at") or t.get("emitted_at")]
            nxt = (data.get("meta") or {}).get("next_page")
            if not nxt:
                break
            page = nxt
        res = services.import_transactions(conn, items, "qonto")
        docs = fetch_attachments(conn, c)
    q["last_sync"] = started.isoformat()
    q["balance_cents"] = main.get("balance_cents")
    q["balance_at"] = started.isoformat()
    q["iban"] = main.get("iban")
    db.set_setting(conn, "qonto", q)
    db.set_setting(conn, "bank_statement", {"date": started.date().isoformat(), "balance_cents": main.get("balance_cents"), "source": "qonto"})
    return {**res, "attachments": docs, "balance_cents": main.get("balance_cents")}


def fetch_attachments(conn, client) -> int:
    """Télécharge les justificatifs Qonto des opérations et les relie aux dépenses."""
    n = 0
    for t in db.q(conn, "SELECT * FROM bank_transactions WHERE source='qonto' AND attachment_ids IS NOT NULL AND attachment_ids<>'[]'"):
        for att_id in json.loads(t["attachment_ids"]):
            if db.q1(conn, "SELECT id FROM documents WHERE external_id=?", (f"qonto-att:{att_id}",)):
                continue
            try:
                a = _get(client, f"/v2/attachments/{att_id}")["attachment"]
                content = httpx.get(a["url"], timeout=60).content
            except (QontoError, httpx.HTTPError, KeyError):
                continue
            with db.tx(conn):
                doc_id = services.store_document(conn, a.get("file_name") or f"{att_id}.pdf", content, "justificatif",
                                                 t["date"], t["counterparty"], source="qonto", external_id=f"qonto-att:{att_id}")
                exp = db.q1(conn, "SELECT x.id FROM expenses x JOIN allocations al ON al.target_type='depense' AND al.target_id=x.id"
                                  " WHERE al.bank_transaction_id=? AND x.document_id IS NULL", (t["id"],))
                if exp:
                    services.save_expense(conn, {"document_id": doc_id}, exp["id"], "Justificatif Qonto rattaché")
            n += 1
    return n


# ---------------------------------------------------------------- CSV de secours
CSV_ALIASES = {
    "date": ["date de l'opération (utc)", "date de règlement (utc)", "settlement date (utc)", "date (utc)", "date", "settled_at", "operation date"],
    "amount": ["montant total (ttc)", "total amount (incl. vat)", "montant", "amount", "montant (ttc)"],
    "debit": ["débit", "debit"],
    "credit": ["crédit", "credit"],
    "counterparty": ["nom de la contrepartie", "contrepartie", "counterparty name", "counterparty", "tiers", "bénéficiaire"],
    "label": ["libellé", "label", "description", "référence", "reference"],
    "reference": ["référence", "reference"],
    "id": ["id de la transaction", "transaction id", "id", "identifiant"],
    "vat": ["montant de la tva", "vat amount", "tva"],
    "status": ["statut", "status"],
    "type": ["type d'opération", "operation type", "type"],
}


def parse_csv(content: bytes) -> list[dict]:
    text = content.decode("utf-8-sig", errors="replace")
    sample = text[:4000]
    delim = ";" if sample.count(";") > sample.count(",") else ","
    reader = csv.DictReader(io.StringIO(text), delimiter=delim)
    headers = {h.lower().strip(): h for h in (reader.fieldnames or [])}

    def col(key):
        for alias in CSV_ALIASES[key]:
            if alias in headers:
                return headers[alias]
        return None
    c = {k: col(k) for k in CSV_ALIASES}
    if not c["date"] or not (c["amount"] or c["debit"] or c["credit"]):
        raise ValueError("Colonnes « date » et « montant » introuvables dans le CSV.")
    items, seen = [], {}
    for row in reader:
        if c["status"] and (row.get(c["status"]) or "").lower() in ("declined", "refusée", "refusé", "annulée", "reversed"):
            continue
        raw_date = (row.get(c["date"]) or "").strip()
        if not raw_date:
            continue
        d = None
        for fmt_ in ("%Y-%m-%d", "%d/%m/%Y", "%d-%m-%Y", "%d.%m.%Y"):
            try:
                d = datetime.strptime(raw_date[:10], fmt_).date().isoformat()
                break
            except ValueError:
                continue
        if d is None:
            try:
                d = datetime.fromisoformat(raw_date.replace("Z", "+00:00")).date().isoformat()
            except ValueError:
                continue
        if c["amount"]:
            amount = services.to_cents(row.get(c["amount"]))
        else:
            amount = services.to_cents(row.get(c["credit"]) or 0) - abs(services.to_cents(row.get(c["debit"]) or 0))
        label = (row.get(c["label"]) or "").strip() if c["label"] else ""
        cp = (row.get(c["counterparty"]) or "").strip() if c["counterparty"] else label
        ext = row.get(c["id"]) if c["id"] else None
        if not ext:
            key = f"{d}|{amount}|{cp}|{label}"
            seen[key] = seen.get(key, 0) + 1
            ext = "csv:" + hashlib.sha1(f"{key}|{seen[key]}".encode()).hexdigest()[:20]
        else:
            ext = f"qontocsv:{ext}"
        items.append({"external_id": ext, "date": d, "amount_cents": amount, "label": label or cp, "counterparty": cp,
                      "reference": row.get(c["reference"]) if c["reference"] else None,
                      "vat_cents": abs(services.to_cents(row.get(c["vat"]))) if c["vat"] and row.get(c["vat"]) else None,
                      "operation_type": (row.get(c["type"]) or None) if c["type"] else None, "raw": row})
    return items

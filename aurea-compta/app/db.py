"""Base SQLite : schéma, connexion, protections et journal des modifications."""
import json
import sqlite3
import threading
from datetime import datetime
from pathlib import Path

from . import config, rules

SCHEMA_VERSION = 1

SCHEMA = """
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS meta (key TEXT PRIMARY KEY, value TEXT);
CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL);

CREATE TABLE IF NOT EXISTS fiscal_years (
  id INTEGER PRIMARY KEY,
  start_date TEXT NOT NULL,
  end_date TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'open' CHECK (status IN ('open','closed')),
  closed_at TEXT,
  summary TEXT
);

CREATE TABLE IF NOT EXISTS accounts (
  number TEXT PRIMARY KEY,
  label TEXT NOT NULL,
  custom INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS categories (
  id INTEGER PRIMARY KEY,
  code TEXT UNIQUE,
  name TEXT NOT NULL,
  account TEXT NOT NULL,
  vat_recovery INTEGER NOT NULL DEFAULT 100,
  deductible INTEGER NOT NULL DEFAULT 100,
  rule TEXT NOT NULL DEFAULT 'standard',
  is_asset INTEGER NOT NULL DEFAULT 0,
  help TEXT,
  active INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS parties (
  id INTEGER PRIMARY KEY,
  kind TEXT NOT NULL CHECK (kind IN ('client','supplier')),
  name TEXT NOT NULL,
  siren TEXT, vat_number TEXT, country TEXT DEFAULT 'FR',
  email TEXT, address TEXT,
  default_category_id INTEGER REFERENCES categories(id),
  created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS ix_parties_name ON parties(kind, name);

CREATE TABLE IF NOT EXISTS documents (
  id INTEGER PRIMARY KEY,
  path TEXT NOT NULL,
  original_name TEXT,
  sha256 TEXT NOT NULL UNIQUE,
  mime TEXT, size INTEGER,
  kind TEXT NOT NULL DEFAULT 'justificatif',
  doc_date TEXT,
  party_name TEXT,
  amount_cents INTEGER,
  source TEXT NOT NULL DEFAULT 'upload',
  external_id TEXT,
  ai_status TEXT,
  ai_data TEXT,
  locked INTEGER NOT NULL DEFAULT 0,
  uploaded_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS bank_transactions (
  id INTEGER PRIMARY KEY,
  source TEXT NOT NULL,
  external_id TEXT NOT NULL UNIQUE,
  bank_account TEXT,
  date TEXT NOT NULL,
  emitted_at TEXT,
  amount_cents INTEGER NOT NULL,
  label TEXT,
  counterparty TEXT,
  operation_type TEXT,
  reference TEXT,
  note TEXT,
  qonto_vat_cents INTEGER,
  qonto_vat_rate TEXT,
  attachment_ids TEXT,
  kind TEXT,
  status TEXT NOT NULL DEFAULT 'a_traiter'
     CHECK (status IN ('a_traiter','propose','rapproche','ignore')),
  suggestion TEXT,
  comment TEXT,
  raw TEXT,
  imported_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS ix_tx_date ON bank_transactions(date);

CREATE TABLE IF NOT EXISTS sales_invoices (
  id INTEGER PRIMARY KEY,
  number TEXT NOT NULL UNIQUE,
  kind TEXT NOT NULL DEFAULT 'facture' CHECK (kind IN ('facture','avoir')),
  credited_invoice_id INTEGER REFERENCES sales_invoices(id),
  client_id INTEGER NOT NULL REFERENCES parties(id),
  issue_date TEXT NOT NULL,
  due_date TEXT,
  nature TEXT NOT NULL DEFAULT 'services' CHECK (nature IN ('services','biens')),
  vat_regime TEXT NOT NULL DEFAULT 'fr' CHECK (vat_regime IN ('fr','exonere','autoliquidation_ue','export')),
  ht_cents INTEGER NOT NULL,
  vat_cents INTEGER NOT NULL,
  ttc_cents INTEGER NOT NULL,
  status TEXT NOT NULL DEFAULT 'emise' CHECK (status IN ('brouillon','emise','annulee')),
  document_id INTEGER REFERENCES documents(id),
  notes TEXT,
  external_id TEXT,
  created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS sales_invoice_lines (
  id INTEGER PRIMARY KEY,
  invoice_id INTEGER NOT NULL REFERENCES sales_invoices(id) ON DELETE CASCADE,
  description TEXT NOT NULL,
  quantity TEXT NOT NULL DEFAULT '1',
  unit_price_cents INTEGER NOT NULL,
  vat_rate TEXT NOT NULL DEFAULT '20',
  account TEXT NOT NULL DEFAULT '706000',
  ht_cents INTEGER NOT NULL,
  vat_cents INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS expenses (
  id INTEGER PRIMARY KEY,
  date TEXT NOT NULL,
  supplier_id INTEGER REFERENCES parties(id),
  supplier_name TEXT,
  label TEXT NOT NULL,
  invoice_number TEXT,
  ht_cents INTEGER NOT NULL,
  vat_cents INTEGER NOT NULL DEFAULT 0,
  ttc_cents INTEGER NOT NULL,
  vat_rate TEXT,
  vat_regime TEXT NOT NULL DEFAULT 'fr'
     CHECK (vat_regime IN ('fr','sans_tva','autoliquidation_ue','autoliquidation_hors_ue')),
  vat_recovery INTEGER NOT NULL DEFAULT 100,
  deductible INTEGER NOT NULL DEFAULT 100,
  nature TEXT NOT NULL DEFAULT 'services' CHECK (nature IN ('services','biens')),
  category_id INTEGER REFERENCES categories(id),
  account TEXT,
  payment_method TEXT NOT NULL DEFAULT 'qonto'
     CHECK (payment_method IN ('qonto','perso','a_payer')),
  beneficiary TEXT,
  document_id INTEGER REFERENCES documents(id),
  status TEXT NOT NULL DEFAULT 'a_valider'
     CHECK (status IN ('importe','propose_ia','a_valider','valide')),
  source TEXT NOT NULL DEFAULT 'manuel',
  fixed_asset_id INTEGER,
  comment TEXT,
  created_at TEXT NOT NULL
);

-- Rapprochement : une opération bancaire règle une ou plusieurs pièces.
CREATE TABLE IF NOT EXISTS allocations (
  id INTEGER PRIMARY KEY,
  bank_transaction_id INTEGER NOT NULL REFERENCES bank_transactions(id),
  target_type TEXT NOT NULL CHECK (target_type IN
    ('vente','depense','associe','capital','tva','is','virement_interne','compte')),
  target_id INTEGER,
  account TEXT,
  amount_cents INTEGER NOT NULL,
  created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS ix_alloc_tx ON allocations(bank_transaction_id);
CREATE INDEX IF NOT EXISTS ix_alloc_target ON allocations(target_type, target_id);

CREATE TABLE IF NOT EXISTS associate_movements (
  id INTEGER PRIMARY KEY,
  date TEXT NOT NULL,
  type TEXT NOT NULL CHECK (type IN ('apport','remboursement','avance_frais','depense_perso','capital_libere','capital_non_appele')),
  amount_cents INTEGER NOT NULL CHECK (amount_cents > 0),
  comment TEXT,
  bank_transaction_id INTEGER REFERENCES bank_transactions(id),
  expense_id INTEGER REFERENCES expenses(id),
  created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS fixed_assets (
  id INTEGER PRIMARY KEY,
  label TEXT NOT NULL,
  account TEXT NOT NULL DEFAULT '218300',
  acquisition_date TEXT NOT NULL,
  in_service_date TEXT NOT NULL,
  cost_cents INTEGER NOT NULL,
  duration_years INTEGER NOT NULL,
  method TEXT NOT NULL DEFAULT 'lineaire',
  expense_id INTEGER REFERENCES expenses(id),
  disposal_date TEXT,
  disposal_price_cents INTEGER,
  created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS entries (
  id INTEGER PRIMARY KEY,
  journal TEXT NOT NULL,
  number TEXT,
  date TEXT NOT NULL,
  label TEXT NOT NULL,
  piece_ref TEXT,
  piece_date TEXT,
  document_id INTEGER REFERENCES documents(id),
  source_type TEXT,
  source_id INTEGER,
  status TEXT NOT NULL DEFAULT 'brouillon' CHECK (status IN ('brouillon','valide')),
  reversal_of INTEGER REFERENCES entries(id),
  reversed_by INTEGER REFERENCES entries(id),
  validated_at TEXT,
  created_at TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS ix_entries_source ON entries(source_type, source_id);
CREATE INDEX IF NOT EXISTS ix_entries_date ON entries(date);

CREATE TABLE IF NOT EXISTS entry_lines (
  id INTEGER PRIMARY KEY,
  entry_id INTEGER NOT NULL REFERENCES entries(id),
  account TEXT NOT NULL,
  aux TEXT,
  aux_label TEXT,
  label TEXT,
  debit_cents INTEGER NOT NULL DEFAULT 0 CHECK (debit_cents >= 0),
  credit_cents INTEGER NOT NULL DEFAULT 0 CHECK (credit_cents >= 0),
  vat_code TEXT,
  vat_base_cents INTEGER,
  lettrage TEXT
);
CREATE INDEX IF NOT EXISTS ix_lines_entry ON entry_lines(entry_id);
CREATE INDEX IF NOT EXISTS ix_lines_account ON entry_lines(account);

CREATE TABLE IF NOT EXISTS vat_returns (
  id INTEGER PRIMARY KEY,
  period_start TEXT NOT NULL,
  period_end TEXT NOT NULL,
  form TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'preparee' CHECK (status IN ('preparee','validee','deposee')),
  data TEXT NOT NULL,
  entry_id INTEGER REFERENCES entries(id),
  created_at TEXT NOT NULL,
  validated_at TEXT,
  filed_at TEXT,
  UNIQUE (period_start, period_end)
);

CREATE TABLE IF NOT EXISTS month_closings (
  month TEXT PRIMARY KEY,
  closed_at TEXT NOT NULL,
  report TEXT
);

CREATE TABLE IF NOT EXISTS audit_log (
  id INTEGER PRIMARY KEY,
  ts TEXT NOT NULL,
  action TEXT NOT NULL,
  object_type TEXT NOT NULL,
  object_id INTEGER,
  before TEXT,
  after TEXT,
  reason TEXT
);

CREATE TABLE IF NOT EXISTS supplier_rules (
  pattern TEXT PRIMARY KEY,
  category_id INTEGER REFERENCES categories(id),
  vat_regime TEXT,
  kind TEXT,
  updated_at TEXT NOT NULL
);

-- Protection des écritures validées : ni modification ni suppression possibles,
-- même par erreur de programmation. Seul le marquage "contrepassée" est permis.
CREATE TRIGGER IF NOT EXISTS trg_entries_no_update
BEFORE UPDATE ON entries
WHEN OLD.status = 'valide' AND (
  NEW.status <> OLD.status OR NEW.date <> OLD.date OR NEW.journal <> OLD.journal
  OR IFNULL(NEW.number,'') <> IFNULL(OLD.number,'') OR NEW.label <> OLD.label)
BEGIN SELECT RAISE(ABORT, 'Écriture validée : modification interdite, passer une contrepassation'); END;

CREATE TRIGGER IF NOT EXISTS trg_entries_no_delete
BEFORE DELETE ON entries WHEN OLD.status = 'valide'
BEGIN SELECT RAISE(ABORT, 'Écriture validée : suppression interdite'); END;

CREATE TRIGGER IF NOT EXISTS trg_lines_no_update
BEFORE UPDATE ON entry_lines
WHEN (SELECT status FROM entries WHERE id = OLD.entry_id) = 'valide'
 AND (NEW.debit_cents <> OLD.debit_cents OR NEW.credit_cents <> OLD.credit_cents OR NEW.account <> OLD.account)
BEGIN SELECT RAISE(ABORT, 'Ligne d''écriture validée : modification interdite'); END;

CREATE TRIGGER IF NOT EXISTS trg_lines_no_delete
BEFORE DELETE ON entry_lines
WHEN (SELECT status FROM entries WHERE id = OLD.entry_id) = 'valide'
BEGIN SELECT RAISE(ABORT, 'Ligne d''écriture validée : suppression interdite'); END;

CREATE TRIGGER IF NOT EXISTS trg_docs_no_delete
BEFORE DELETE ON documents WHEN OLD.locked = 1
BEGIN SELECT RAISE(ABORT, 'Pièce justificative validée : suppression interdite'); END;

CREATE TRIGGER IF NOT EXISTS trg_audit_no_change
BEFORE UPDATE ON audit_log
BEGIN SELECT RAISE(ABORT, 'Historique non modifiable'); END;
CREATE TRIGGER IF NOT EXISTS trg_audit_no_delete
BEFORE DELETE ON audit_log
BEGIN SELECT RAISE(ABORT, 'Historique non modifiable'); END;
"""

# Paramètres provisoires : clairement identifiés dans l'interface tant qu'ils
# n'ont pas été confirmés (clé "confirmed").
DEFAULT_SETTINGS = {
    "company": {
        "name": "Aurea Media", "legal_form": "SASU", "siren": "", "siret": "", "vat_number": "",
        "address": "", "naf": "", "capital_cents": 100000, "capital_fully_paid": True,
        "held_by_natural_persons_75": True, "associate_name": "", "associate_aliases": [],
        "creation_date": "",
    },
    "tax": {
        "vat_regime": "reel_normal",            # reel_normal | reel_simplifie | franchise
        "vat_periodicity": "mensuelle",         # mensuelle | trimestrielle | annuelle
        "vat_start_date": "",
        "vat_on_debits": False,                 # option pour les débits (prestations)
        "main_activity": "services",            # services | biens | mixte
        "is_regime": "reel_simplifie",          # reel_simplifie | reel_normal
        "previous_is_cents": 0,                 # IS de l'exercice précédent (acomptes)
        "loss_carryforward_cents": 0,           # déficits reportables
        "salaried_count": 0,
    },
    "invoicing": {"prefix": "F", "credit_prefix": "AV", "pattern": "{prefix}{year}-{seq:04d}",
                  "payment_terms_days": 30, "legal_mentions": ""},
    "qonto": {"enabled": False, "environment": "production", "auto_sync_minutes": 30,
              "bank_account_id": "", "last_sync": None},
    "ai": {"enabled": False, "model": "claude-opus-5-5", "auto_link_threshold": 0.9},
    "remote": {"enabled": False, "port": 8765},
    "confirmed": {"company": False, "tax": False},
}

_local = threading.local()


def connect(path: Path | None = None) -> sqlite3.Connection:
    p = path or config.db_path()
    conn = sqlite3.connect(p, isolation_level=None, check_same_thread=False)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA foreign_keys = ON")
    conn.execute("PRAGMA journal_mode = WAL")
    conn.execute("PRAGMA busy_timeout = 5000")
    return conn


def get_conn() -> sqlite3.Connection:
    conn = getattr(_local, "conn", None)
    if conn is None or getattr(_local, "path", None) != str(config.db_path()):
        conn = connect()
        init(conn)
        _local.conn = conn
        _local.path = str(config.db_path())
    return conn


def reset_thread_conn():
    c = getattr(_local, "conn", None)
    if c is not None:
        c.close()
    _local.conn = None


def now() -> str:
    return datetime.now().isoformat(timespec="seconds")


def init(conn: sqlite3.Connection) -> None:
    conn.executescript(SCHEMA)
    conn.execute("INSERT OR IGNORE INTO meta(key,value) VALUES('schema_version',?)", (str(SCHEMA_VERSION),))
    for num, label in rules.ACCOUNTS:
        conn.execute("INSERT OR IGNORE INTO accounts(number,label) VALUES(?,?)", (num, label))
    if conn.execute("SELECT COUNT(*) FROM categories").fetchone()[0] == 0:
        for c in rules.DEFAULT_CATEGORIES:
            conn.execute(
                "INSERT INTO categories(code,name,account,vat_recovery,deductible,rule,is_asset,help) VALUES(?,?,?,?,?,?,?,?)",
                (c["code"], c["name"], c["account"], c["vat_recovery"], c["deductible"], c["rule"],
                 c.get("is_asset", 0), c.get("help")))
    for k, v in DEFAULT_SETTINGS.items():
        conn.execute("INSERT OR IGNORE INTO settings(key,value) VALUES(?,?)", (k, json.dumps(v)))
    if conn.execute("SELECT COUNT(*) FROM fiscal_years").fetchone()[0] == 0:
        y = datetime.now().year
        conn.execute("INSERT INTO fiscal_years(start_date,end_date) VALUES(?,?)", (f"{y}-01-01", f"{y}-12-31"))


# ---------------------------------------------------------------- helpers
def q(conn, sql, params=()):
    return [dict(r) for r in conn.execute(sql, params).fetchall()]


def q1(conn, sql, params=()):
    r = conn.execute(sql, params).fetchone()
    return dict(r) if r else None


def get_setting(conn, key):
    r = conn.execute("SELECT value FROM settings WHERE key=?", (key,)).fetchone()
    base = DEFAULT_SETTINGS.get(key)
    val = json.loads(r["value"]) if r else base
    if isinstance(base, dict) and isinstance(val, dict):
        merged = dict(base)
        merged.update(val)
        return merged
    return val


def set_setting(conn, key, value):
    before = get_setting(conn, key)
    conn.execute("INSERT INTO settings(key,value) VALUES(?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value",
                 (key, json.dumps(value)))
    audit(conn, "modification", "parametres", None, before, value)


def audit(conn, action, object_type, object_id=None, before=None, after=None, reason=None):
    conn.execute(
        "INSERT INTO audit_log(ts,action,object_type,object_id,before,after,reason) VALUES(?,?,?,?,?,?,?)",
        (now(), action, object_type, object_id,
         json.dumps(before, ensure_ascii=False, default=str) if before is not None else None,
         json.dumps(after, ensure_ascii=False, default=str) if after is not None else None, reason))


class tx:
    """Transaction SQLite explicite (BEGIN IMMEDIATE / COMMIT / ROLLBACK), réentrante."""

    def __init__(self, conn):
        self.conn = conn
        self.outer = False

    def __enter__(self):
        if not self.conn.in_transaction:
            self.conn.execute("BEGIN IMMEDIATE")
            self.outer = True
        return self.conn

    def __exit__(self, exc_type, exc, tb):
        if self.outer:
            if exc_type is None:
                self.conn.execute("COMMIT")
            else:
                self.conn.execute("ROLLBACK")
        return False

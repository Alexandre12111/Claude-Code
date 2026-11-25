"""Emplacements des données et gestion des secrets (clés API).

Les données réelles sont dans ./data (ou AUREA_DATA_DIR).
Les données de démonstration et de test vivent dans un autre dossier :
elles ne sont jamais mélangées avec la base réelle.
"""
import json
import os
import stat
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


def data_dir() -> Path:
    d = Path(os.environ.get("AUREA_DATA_DIR", ROOT / "data"))
    d.mkdir(parents=True, exist_ok=True)
    for sub in ("documents", "backups", "exports"):
        (d / sub).mkdir(exist_ok=True)
    return d


def db_path() -> Path:
    return data_dir() / "aurea.sqlite"


def is_demo() -> bool:
    return os.environ.get("AUREA_MODE", "").lower() in ("demo", "test")


# ---------------------------------------------------------------- secrets
# Fichier séparé de la base, droits 600, jamais exporté ni renvoyé en clair par l'API.
SECRET_KEYS = ("qonto_login", "qonto_secret_key", "qonto_staging_token", "anthropic_api_key")


def _secrets_file() -> Path:
    return data_dir() / "secrets.json"


def load_secrets() -> dict:
    f = _secrets_file()
    out = {}
    if f.exists():
        try:
            out = json.loads(f.read_text("utf-8"))
        except ValueError:
            out = {}
    # Les variables d'environnement ont priorité (pratique pour un trousseau externe).
    env = {"qonto_login": "QONTO_LOGIN", "qonto_secret_key": "QONTO_SECRET_KEY",
           "anthropic_api_key": "ANTHROPIC_API_KEY"}
    for k, var in env.items():
        if os.environ.get(var):
            out[k] = os.environ[var]
    return out


def save_secret(key: str, value: str | None) -> None:
    if key not in SECRET_KEYS:
        raise ValueError("Clé inconnue")
    f = _secrets_file()
    data = {}
    if f.exists():
        try:
            data = json.loads(f.read_text("utf-8"))
        except ValueError:
            data = {}
    if value:
        data[key] = value.strip()
    else:
        data.pop(key, None)
    f.write_text(json.dumps(data), "utf-8")
    os.chmod(f, stat.S_IRUSR | stat.S_IWUSR)


def masked_secrets() -> dict:
    s = load_secrets()
    return {k: (("•••• " + s[k][-4:]) if s.get(k) else None) for k in SECRET_KEYS}

"""Sauvegarde automatique, export complet et restauration."""
import io
import json
import shutil
import sqlite3
import zipfile
from datetime import datetime
from pathlib import Path

from . import config, db

KEEP = 30


def backup_db(reason="auto") -> Path:
    """Copie cohérente de la base (API de sauvegarde SQLite), rotation des 30 dernières."""
    dest_dir = config.data_dir() / "backups"
    dest = dest_dir / f"aurea-{datetime.now():%Y%m%d-%H%M%S}-{reason}.sqlite"
    src = sqlite3.connect(config.db_path())
    dst = sqlite3.connect(dest)
    with dst:
        src.backup(dst)
    src.close()
    dst.close()
    files = sorted(dest_dir.glob("aurea-*.sqlite"))
    for old in files[:-KEEP]:
        old.unlink()
    return dest


def daily_backup_if_needed() -> Path | None:
    dest_dir = config.data_dir() / "backups"
    today = f"aurea-{datetime.now():%Y%m%d}"
    if not config.db_path().exists() or any(dest_dir.glob(today + "*")):
        return None
    return backup_db("quotidienne")


def full_export() -> bytes:
    """Archive complète : base + documents (sans les clés API)."""
    snap = backup_db("export")
    buf = io.BytesIO()
    with zipfile.ZipFile(buf, "w", zipfile.ZIP_DEFLATED) as z:
        z.write(snap, "aurea.sqlite")
        docs = config.data_dir() / "documents"
        for p in docs.rglob("*"):
            if p.is_file():
                z.write(p, "documents/" + str(p.relative_to(docs)))
        z.writestr("manifest.json", json.dumps({"app": "aurea-compta", "date": datetime.now().isoformat(), "schema": db.SCHEMA_VERSION}))
    return buf.getvalue()


def restore(archive: bytes) -> dict:
    """Restaure une archive d'export complet. La base actuelle est sauvegardée avant."""
    z = zipfile.ZipFile(io.BytesIO(archive))
    names = z.namelist()
    if "aurea.sqlite" not in names or "manifest.json" not in names:
        raise ValueError("Archive invalide : aurea.sqlite ou manifest.json manquant.")
    if config.db_path().exists():
        backup_db("avant-restauration")
    db.reset_thread_conn()
    tmp = config.data_dir() / "restore-tmp.sqlite"
    tmp.write_bytes(z.read("aurea.sqlite"))
    chk = sqlite3.connect(tmp)
    ok = chk.execute("PRAGMA integrity_check").fetchone()[0]
    chk.close()
    if ok != "ok":
        tmp.unlink()
        raise ValueError("Base de l'archive corrompue.")
    for suffix in ("-wal", "-shm"):
        p = Path(str(config.db_path()) + suffix)
        if p.exists():
            p.unlink()
    shutil.move(tmp, config.db_path())
    docs = config.data_dir() / "documents"
    n = 0
    for name in names:
        if name.startswith("documents/") and not name.endswith("/"):
            target = (docs / name[len("documents/"):]).resolve()
            if not str(target).startswith(str(docs.resolve())):
                continue  # protection contre les chemins sortant du dossier
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_bytes(z.read(name))
            n += 1
    return {"ok": True, "documents": n}


def list_backups():
    return [{"name": p.name, "size": p.stat().st_size, "date": datetime.fromtimestamp(p.stat().st_mtime).isoformat(timespec="seconds")}
            for p in sorted((config.data_dir() / "backups").glob("aurea-*.sqlite"), reverse=True)]

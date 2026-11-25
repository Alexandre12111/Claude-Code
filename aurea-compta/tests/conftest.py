"""Les tests utilisent une base temporaire isolée : jamais la base réelle."""
import os
import pytest


@pytest.fixture()
def conn(tmp_path, monkeypatch):
    monkeypatch.setenv("AUREA_DATA_DIR", str(tmp_path / "data"))
    monkeypatch.setenv("AUREA_MODE", "test")
    for v in ("ANTHROPIC_API_KEY", "QONTO_LOGIN", "QONTO_SECRET_KEY"):
        monkeypatch.delenv(v, raising=False)
    from app import db
    db.reset_thread_conn()
    c = db.get_conn()
    c.execute("DELETE FROM fiscal_years")
    c.execute("INSERT INTO fiscal_years(start_date,end_date) VALUES('2026-01-01','2026-12-31')")
    db.set_setting(c, "company", {**db.get_setting(c, "company"), "siren": "123456789", "associate_name": "Alexandre Coury",
                                   "capital_cents": 100000})
    db.set_setting(c, "tax", {**db.get_setting(c, "tax"), "vat_regime": "reel_normal", "vat_periodicity": "mensuelle"})
    db.set_setting(c, "confirmed", {"company": True, "tax": True})
    yield c
    db.reset_thread_conn()

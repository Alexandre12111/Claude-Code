"""Accès depuis le téléphone : mot de passe obligatoire, réglages sensibles réservés à l'ordinateur."""
from fastapi.testclient import TestClient


def clients(conn):
    from app.main import app
    return TestClient(app, client=("127.0.0.1", 50000)), TestClient(app, client=("192.168.1.20", 50000))


def test_acces_distant_ferme_par_defaut(conn):
    pc, phone = clients(conn)
    assert pc.get("/api/overview").status_code == 200
    assert phone.get("/api/overview").status_code == 403


def test_connexion_par_mot_de_passe(conn):
    pc, phone = clients(conn)
    assert pc.put("/api/remote", json={"password": "court", "enabled": True}).status_code == 400
    assert pc.put("/api/remote", json={"password": "un-bon-mot-de-passe", "enabled": True}).status_code == 200
    assert phone.get("/api/overview").status_code == 401
    r = phone.get("/", follow_redirects=False)
    assert r.status_code == 303 and r.headers["location"] == "/login"
    assert phone.post("/api/login", data={"password": "faux"}, follow_redirects=False).status_code == 401
    assert phone.post("/api/login", data={"password": "un-bon-mot-de-passe"}, follow_redirects=False).status_code == 303
    assert phone.get("/api/overview").status_code == 200
    # Réglages sensibles : uniquement depuis l'ordinateur
    assert phone.put("/api/secrets", json={"anthropic_api_key": "x"}).status_code == 403
    assert phone.put("/api/remote", json={"enabled": False}).status_code == 403
    # Changer le mot de passe déconnecte les sessions existantes
    pc.put("/api/remote", json={"password": "nouveau-mot-de-passe", "enabled": True})
    assert phone.get("/api/overview").status_code == 401


def test_blocage_apres_trop_d_essais(conn):
    pc, phone = clients(conn)
    pc.put("/api/remote", json={"password": "un-bon-mot-de-passe", "enabled": True})
    for _ in range(10):
        phone.post("/api/login", data={"password": "faux"})
    assert phone.post("/api/login", data={"password": "un-bon-mot-de-passe"}).status_code == 429

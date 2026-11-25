"""Accès depuis le téléphone : mot de passe, session, adresses réseau, QR code.

Par défaut l'application n'écoute que sur l'ordinateur (127.0.0.1).
Quand l'accès téléphone est activé, elle écoute aussi sur le réseau local
(et sur Tailscale s'il est installé) et exige un mot de passe pour tout
appareil autre que l'ordinateur lui même.
"""
import hashlib
import hmac
import io
import secrets
import socket
import time

from . import config, db

SESSION_DAYS = 30
_failures: dict[str, list[float]] = {}


def settings(conn) -> dict:
    return db.get_setting(conn, "remote")


def is_local(host: str | None) -> bool:
    return host in ("127.0.0.1", "::1", "localhost")


# ---------------------------------------------------------------- mot de passe
def hash_password(password: str, salt: bytes | None = None) -> str:
    salt = salt or secrets.token_bytes(16)
    h = hashlib.pbkdf2_hmac("sha256", password.encode(), salt, 300_000)
    return f"pbkdf2${salt.hex()}${h.hex()}"


def check_password(password: str) -> bool:
    stored = config.load_secrets().get("remote_password_hash")
    if not stored:
        return False
    _, salt, h = stored.split("$")
    return hmac.compare_digest(hash_password(password, bytes.fromhex(salt)).split("$")[2], h)


def set_password(password: str) -> None:
    if len(password) < 8:
        raise ValueError("Le mot de passe doit contenir au moins 8 caractères.")
    config.save_secret("remote_password_hash", hash_password(password))
    config.save_secret("session_secret", secrets.token_hex(32))   # déconnecte les anciennes sessions


def password_set() -> bool:
    return bool(config.load_secrets().get("remote_password_hash"))


# ---------------------------------------------------------------- session
def _secret() -> bytes:
    s = config.load_secrets().get("session_secret")
    if not s:
        s = secrets.token_hex(32)
        config.save_secret("session_secret", s)
    return s.encode()


def make_session() -> str:
    exp = str(int(time.time()) + SESSION_DAYS * 86400)
    return exp + "." + hmac.new(_secret(), exp.encode(), hashlib.sha256).hexdigest()


def valid_session(token: str | None) -> bool:
    if not token or "." not in token:
        return False
    exp, sig = token.split(".", 1)
    if not exp.isdigit() or int(exp) < time.time():
        return False
    return hmac.compare_digest(sig, hmac.new(_secret(), exp.encode(), hashlib.sha256).hexdigest())


def too_many_attempts(ip: str) -> bool:
    now = time.time()
    _failures[ip] = [t for t in _failures.get(ip, []) if now - t < 900]
    return len(_failures[ip]) >= 10


def record_failure(ip: str) -> None:
    _failures.setdefault(ip, []).append(time.time())


# ---------------------------------------------------------------- adresses
def local_addresses() -> list[dict]:
    """Adresses par lesquelles le téléphone peut joindre l'ordinateur."""
    out, seen = [], set()

    def add(ip, kind):
        if ip and ip not in seen and not ip.startswith("127."):
            seen.add(ip)
            out.append({"ip": ip, "type": kind})
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("10.255.255.255", 1))   # aucun paquet envoyé : sert à trouver l'interface active
        add(s.getsockname()[0], "wifi")
        s.close()
    except OSError:
        pass
    try:
        for info in socket.getaddrinfo(socket.gethostname(), None, socket.AF_INET):
            ip = info[4][0]
            add(ip, "tailscale" if ip.startswith("100.") else "wifi")
    except OSError:
        pass
    return out


def qr_svg(text: str) -> str | None:
    try:
        import qrcode
        import qrcode.image.svg
    except ImportError:
        return None
    img = qrcode.make(text, image_factory=qrcode.image.svg.SvgPathImage, box_size=8, border=2)
    buf = io.BytesIO()
    img.save(buf)
    svg = buf.getvalue().decode()
    import re
    return re.sub(r'<svg width="[^"]+" height="[^"]+"', '<svg width="100%" height="100%"', svg, count=1)


LOGIN_PAGE = """<!doctype html><html lang="fr"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aurea Compta</title><link rel="manifest" href="/manifest.webmanifest">
<link rel="apple-touch-icon" href="/static/icon-180.png"><meta name="apple-mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#9a7a2e"><link rel="stylesheet" href="/static/app.css"></head>
<body><div style="max-width:360px;margin:18vh auto;padding:0 16px">
<div class="brand" style="margin-bottom:24px"><span class="logo">A</span><div><strong>Aurea Media</strong><small>Comptabilité SASU</small></div></div>
<form class="panel panel-pad" method="post" action="/api/login">
<div class="field"><label>Mot de passe</label><input type="password" name="password" autofocus autocomplete="current-password"></div>
{error}<button class="primary" style="width:100%;justify-content:center">Se connecter</button></form>
<p class="hint" style="text-align:center">L'ordinateur doit être allumé et l'application ouverte.</p></div></body></html>"""

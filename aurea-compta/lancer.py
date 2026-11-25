"""Lance Aurea Compta et ouvre le navigateur sur http://127.0.0.1:8765

Normalement on ne l'appelle pas soi même : double cliquez sur
« Aurea Compta.app » (Mac) ou « Aurea Compta.vbs » (Windows).

    python lancer.py            données réelles (dossier ./data)
    python lancer.py --demo     données fictives de démonstration (dossier ./data-demo)
"""
import argparse
import os
import socket
import sys
import threading
import webbrowser
from pathlib import Path

ROOT = Path(__file__).resolve().parent
sys.path.insert(0, str(ROOT))

parser = argparse.ArgumentParser()
parser.add_argument("--demo", action="store_true", help="données fictives, séparées des données réelles")
parser.add_argument("--port", type=int, default=8765)
parser.add_argument("--no-browser", action="store_true")
args = parser.parse_args()
url = f"http://127.0.0.1:{args.port}"

# Sans console (lancement par double clic), les messages vont dans un fichier.
if sys.stdout is None or sys.stderr is None:
    log = open(ROOT / "aurea-compta.log", "a", encoding="utf-8", buffering=1)
    sys.stdout = sys.stderr = log

# Déjà ouverte ? On affiche simplement la fenêtre.
with socket.socket() as s:
    s.settimeout(0.5)
    if s.connect_ex(("127.0.0.1", args.port)) == 0:
        if not args.no_browser:
            webbrowser.open(url)
        sys.exit(0)

if args.demo:
    os.environ["AUREA_DATA_DIR"] = str(ROOT / "data-demo")
    os.environ["AUREA_MODE"] = "demo"
    from app import demo
    demo.seed_if_empty()

import uvicorn  # noqa: E402

from app import db  # noqa: E402
from app.main import app  # noqa: E402

first = True
while True:
    remote = db.get_setting(db.get_conn(), "remote")
    # 127.0.0.1 : seul cet ordinateur. 0.0.0.0 : aussi le téléphone (mot de passe obligatoire).
    host = "0.0.0.0" if remote.get("enabled") else "127.0.0.1"
    server = uvicorn.Server(uvicorn.Config(app, host=host, port=args.port, log_level="warning", log_config=None))
    app.state.server, app.state.host, app.state.restart = server, host, False
    if first and not args.no_browser:
        threading.Timer(1.5, lambda: webbrowser.open(url)).start()
    first = False
    print(f"Aurea Compta : {url}" + ("  (accessible depuis le téléphone)" if host == "0.0.0.0" else ""), flush=True)
    server.run()
    if not app.state.restart:
        break

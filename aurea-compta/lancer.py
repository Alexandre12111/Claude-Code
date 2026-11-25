"""Lance Aurea Compta en local : http://127.0.0.1:8765

    python lancer.py            données réelles (dossier ./data)
    python lancer.py --demo     données fictives de démonstration (dossier ./data-demo)
"""
import argparse
import os
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

if args.demo:
    os.environ["AUREA_DATA_DIR"] = str(ROOT / "data-demo")
    os.environ["AUREA_MODE"] = "demo"
    from app import demo
    demo.seed_if_empty()

import uvicorn  # noqa: E402

url = f"http://127.0.0.1:{args.port}"
if not args.no_browser:
    threading.Timer(1.5, lambda: webbrowser.open(url)).start()
print(f"Aurea Compta : {url}  (Ctrl+C pour arrêter)")
# 127.0.0.1 : l'application n'est accessible que depuis cet ordinateur.
uvicorn.run("app.main:app", host="127.0.0.1", port=args.port, log_level="warning")

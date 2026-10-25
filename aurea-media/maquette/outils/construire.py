"""Reconstruit toutes les pages générées puis applique SEO, GEO et typographie : python3 outils/construire.py"""
import subprocess, sys
from pathlib import Path
ICI = Path(__file__).resolve().parent
for script in ("pages_services.py", "pages_autres.py", "seo_geo.py"):
    subprocess.run([sys.executable, script], cwd=ICI, check=True)
from typo import corriger
for p in ICI.parent.glob("*.html"):
    p.write_text(corriger(p.read_text()))
print("Maquette reconstruite.")

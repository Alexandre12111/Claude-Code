"""Reconstruit toutes les pages générées puis applique SEO, GEO et typographie : python3 outils/construire.py"""
import subprocess, sys
from pathlib import Path
ICI = Path(__file__).resolve().parent
for script in ("pages_services.py", "pages_autres.py", "pages_secteurs.py", "seo_geo.py"):
    subprocess.run([sys.executable, script], cwd=ICI, check=True)
from typo import corriger
for p in ICI.parent.glob("*.html"):
    p.write_text(corriger(p.read_text()))
print("Maquette reconstruite.")

# Contrôle : chaque page doit avoir des balises équilibrées (sinon des blocs sortent de leur colonne)
from html.parser import HTMLParser
VIDES = {"img", "br", "meta", "link", "input", "use", "path", "circle", "rect", "source", "stop", "ellipse", "animate"}
class Verif(HTMLParser):
    def __init__(self):
        super().__init__(); self.pile = []; self.erreurs = []
    def handle_starttag(self, t, a):
        if t not in VIDES: self.pile.append((t, self.getpos()[0]))
    def handle_endtag(self, t):
        if t in VIDES: return
        if self.pile and self.pile[-1][0] == t: self.pile.pop()
        else: self.erreurs.append(f"</{t}> ligne {self.getpos()[0]}")
for p in sorted(ICI.parent.glob("*.html")):
    v = Verif(); v.feed(p.read_text())
    if v.erreurs or v.pile:
        print("BALISES À CORRIGER :", p.name, v.erreurs[:3], v.pile[-2:])

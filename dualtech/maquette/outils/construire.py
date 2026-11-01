"""Reconstruit la maquette DualTech : python3 outils/construire.py

1. génère les dix pages (pages.py) ;
2. applique la typographie française (espaces insécables) ;
3. crée le favicon et l'image de partage Open Graph (1200 x 630, rendue avec Chromium) ;
4. contrôle l'équilibre des balises et l'unicité du H1 de chaque page.
"""
import subprocess, sys, re
from html.parser import HTMLParser
from pathlib import Path

ICI = Path(__file__).resolve().parent
RACINE = ICI.parent
ASSETS = RACINE / "assets"

subprocess.run([sys.executable, "pages.py"], cwd=ICI, check=True)

from typo import corriger
for p in RACINE.glob("*.html"):
    p.write_text(corriger(p.read_text()))

# Favicon (carré DualTech du site actuel)
from PIL import Image
src = ICI / "favicon-source.png"
if src.exists():
    im = Image.open(src).convert("RGBA")
    for t in (32, 180, 192):
        im.resize((t, t), Image.LANCZOS).save(ASSETS / f"favicon-{t}.png")

# Image de partage Open Graph
og = ASSETS / "img" / "og-dualtech-2026.jpg"
if not og.exists() or "--og" in sys.argv:
    html = (ICI / "og.html").resolve().as_uri()
    script = f"""
const {{ chromium }} = require('playwright');
(async () => {{
  const b = await chromium.launch(); const p = await b.newPage({{ viewport: {{ width: 1200, height: 630 }} }});
  await p.goto('{html}'); await p.waitForTimeout(1200);
  await p.screenshot({{ path: '{ASSETS}/img/og.png' }}); await b.close();
}})();"""
    subprocess.run(["node", "-e", script], check=True, cwd="/tmp")
    Image.open(ASSETS / "img" / "og.png").convert("RGB").save(og, quality=86, optimize=True, progressive=True)
    (ASSETS / "img" / "og.png").unlink()

# Contrôles
VIDES = {"img", "br", "meta", "link", "input", "use", "path", "circle", "rect", "source", "hr"}
class Verif(HTMLParser):
    def __init__(self):
        super().__init__(); self.pile = []; self.erreurs = []; self.h1 = 0
    def handle_starttag(self, t, a):
        self.h1 += t == "h1"
        if t not in VIDES: self.pile.append((t, self.getpos()[0]))
    def handle_endtag(self, t):
        if t in VIDES: return
        if self.pile and self.pile[-1][0] == t: self.pile.pop()
        else: self.erreurs.append(f"</{t}> ligne {self.getpos()[0]}")
ok = True
for p in sorted(RACINE.glob("*.html")):
    s = p.read_text()
    v = Verif(); v.feed(s)
    titre = re.search(r"<title>(.*?)</title>", s).group(1)
    desc = re.search(r'name="description" content="(.*?)"', s).group(1)
    import html as h
    lt, ld = len(h.unescape(titre)), len(h.unescape(desc))
    alerte = []
    if v.erreurs or v.pile: alerte.append(f"balises {v.erreurs[:3]} {v.pile[-2:]}")
    if v.h1 != 1: alerte.append(f"{v.h1} H1")
    if not 40 <= lt <= 62: alerte.append(f"title {lt} car.")
    if not 130 <= ld <= 162: alerte.append(f"description {ld} car.")
    ok &= not alerte
    print(f"{p.name:38} title {lt:>3} · desc {ld:>3} · {'OK' if not alerte else ' / '.join(alerte)}")
print("Maquette reconstruite." if ok else "Maquette reconstruite, avec des points à corriger.")

# Version autonome et navigable de la maquette (un seul fichier)
subprocess.run([sys.executable, "interactive.py"], cwd=ICI, check=True)

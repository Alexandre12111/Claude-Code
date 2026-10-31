"""Assemble la maquette SapoCycle en un seul fichier HTML autonome.

Sorties :
  index.html     document complet, à ouvrir dans un navigateur
  artifact.html  même page sans doctype/html/head/body (publication Artifact)
"""
import base64
import pathlib
import re

ROOT = pathlib.Path(__file__).parent
SRC = ROOT / "src"
ASSETS = ROOT / "assets"

GSAP = "https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"
SCROLLTRIGGER = "https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"
LENIS = "https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js"
THREE = "https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"
FONTS = ("https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900"
         "&family=Geist:wght@300..600&family=Comfortaa:wght@400&display=swap")

TITLE = "Maquette SapoCycle"
BLANK = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
DESCRIPTION = ("Recyclage des savons d'hôtel : SapoCycle collecte vos savons usagés, "
               "les fait recycler par des personnes en situation de handicap et les offre à des familles.")


def logo_group() -> str:
    svg = (ASSETS / "logo.svg").read_text(encoding="utf-8")
    inner = svg[svg.index(">", svg.index("<svg")) + 1: svg.rindex("</svg>")]
    inner = re.sub(r"<metadata>.*?</metadata>", "", inner, flags=re.S)
    inner = re.sub(r"<style.*?</style>", "", inner, flags=re.S)
    inner = inner.replace('class="st1"', 'clip-path="url(#logoClip)" fill="currentColor"')
    inner = inner.replace('class="st0"', 'fill="currentColor"')
    inner = inner.replace("SVGID_1_", "logoClipRect").replace("SVGID_2_", "logoClip")
    inner = inner.replace("xlink:href", "href")
    inner = re.sub(r"\s+", " ", inner).strip()
    return f'<g id="logo-g">{inner}</g>'


def data_uri(name: str) -> str:
    raw = (ASSETS / f"{name}.webp").read_bytes()
    return "data:image/webp;base64," + base64.b64encode(raw).decode()


def build() -> None:
    css = (SRC / "styles.css").read_text(encoding="utf-8")
    js = (SRC / "xp.js").read_text(encoding="utf-8") + "\n" + (SRC / "app.js").read_text(encoding="utf-8")
    body = (SRC / "body.html").read_text(encoding="utf-8")
    body = body.replace("{{LOGO_GROUP}}", logo_group())
    body = body.replace("{{ICONS}}", (SRC / "icons.html").read_text(encoding="utf-8"))
    body = body.replace("{{FILM}}", (SRC / "film.html").read_text(encoding="utf-8"))
    # Chaque photo n'est intégrée qu'une fois ; les reprises copient sa source au chargement (voir app.js).
    seen = set()

    def img(m):
        name = m.group(1)
        if name in seen:
            return f'src="{BLANK}" data-same="{name}"'
        seen.add(name)
        return f'src="{data_uri(name)}" data-img="{name}"'

    body = re.sub(r'src="\{\{IMG:([\w-]+)\}\}"', img, body)

    head = f"""<title>{TITLE}</title>
<meta name="claude:viewport-fit-sides" content="cover">
<meta name="description" content="{DESCRIPTION}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="{FONTS}">
<script type="application/ld+json">{{"@context":"https://schema.org","@type":"NGO","name":"Fondation SapoCycle","url":"https://sapocycle.org/","slogan":"Bubbles saving lives","foundingDate":"2014","founder":{{"@type":"Person","name":"Dorothée Schiesser-Peyrouzet"}},"email":"info@sapocycle.org","address":{{"@type":"PostalAddress","streetAddress":"Münchensteinerstrasse 274","postalCode":"4053","addressLocality":"Basel","addressCountry":"CH"}},"areaServed":["CH","FR","DE"]}}</script>
<style>
{css}
</style>"""

    scripts = f"""<script src="{GSAP}"></script>
<script src="{SCROLLTRIGGER}"></script>
<script src="{LENIS}"></script>
<script src="{THREE}"></script>
<script>
{js}
</script>"""

    fragment = f"{head}\n{body}\n{scripts}\n"
    (ROOT / "artifact.html").write_text(fragment, encoding="utf-8")

    full = f"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
{head}
</head>
<body>
{body}
{scripts}
</body>
</html>
"""
    (ROOT / "index.html").write_text(full, encoding="utf-8")
    print(f"index.html {len(full) / 1024:.0f} Ko, artifact.html {len(fragment) / 1024:.0f} Ko")


if __name__ == "__main__":
    build()

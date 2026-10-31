"""Génère le site SapoCycle : une vraie page HTML par onglet.

Sources : src/pages/<slug>.html (première ligne : métadonnées JSON en commentaire).
Sortie : <slug>.html à la racine du dossier, plus index.html (copie de l'accueil).
Pour l'Artifact : artifact.html (accueil sans doctype), les autres pages restent des documents complets.
"""
import json
import pathlib
import re

ROOT = pathlib.Path(__file__).parent
SRC = ROOT / "src"

GSAP = "https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"
SCROLLTRIGGER = "https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"
LENIS = "https://cdn.jsdelivr.net/npm/lenis@1.1.13/dist/lenis.min.js"
FONTS = ("https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,300..600;1,6..72,300..600"
         "&family=Public+Sans:wght@300..700&display=swap")

NAV = [
    ("qui-sommes-nous", "Qui sommes-nous"),
    ("notre-approche", "Notre approche"),
    ("hotels", "Hôtels"),
    ("impact", "Impact"),
    ("partenaires", "Partenaires"),
    ("actualites", "Actualités"),
]

ICONS = """
<symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></symbol>
<symbol id="i-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></symbol>
<symbol id="i-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></symbol>
<symbol id="i-play" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15a1 1 0 0 0 1.5.86l12-7.5a1 1 0 0 0 0-1.72l-12-7.5A1 1 0 0 0 7 4.5z"/></symbol>
<symbol id="i-pause" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16" rx="1.2"/><rect x="14" y="4" width="4" height="16" rx="1.2"/></symbol>
<symbol id="i-restart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></symbol>
<symbol id="i-truck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></symbol>
<symbol id="i-award" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.48 12.89 17 22l-5-3-5 3 1.52-9.11"/></symbol>
<symbol id="i-megaphone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></symbol>
<symbol id="i-chart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></symbol>
<symbol id="i-recycle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7 19H4.8a1.8 1.8 0 0 1-1.57-.88 1.8 1.8 0 0 1 0-1.78L7.2 9.5"/><path d="M11 19h8.2a1.8 1.8 0 0 0 1.56-.89 1.8 1.8 0 0 0 0-1.78l-1.23-2.12"/><path d="m14 16-3 3 3 3"/><path d="M8.3 13.6 7.2 9.5 3.1 10.6"/><path d="m9.34 5.81 1.1-1.89A1.8 1.8 0 0 1 12 3a1.8 1.8 0 0 1 1.53.89l3.94 6.84"/><path d="m13.38 9.63 4.1 1.1 1.1-4.1"/></symbol>
<symbol id="i-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></symbol>
<symbol id="i-box" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8v13H3V8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/></symbol>
<symbol id="i-linkedin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></symbol>
<symbol id="i-instagram" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><path d="M17.5 6.5h.01"/></symbol>
<symbol id="i-facebook" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></symbol>
"""


def logo_group() -> str:
    svg = (SRC / "logo.svg").read_text(encoding="utf-8")
    inner = svg[svg.index(">", svg.index("<svg")) + 1: svg.rindex("</svg>")]
    inner = re.sub(r"<metadata>.*?</metadata>", "", inner, flags=re.S)
    inner = re.sub(r"<style.*?</style>", "", inner, flags=re.S)
    inner = inner.replace('class="st1"', 'clip-path="url(#logoClip)" fill="currentColor"')
    inner = inner.replace('class="st0"', 'fill="currentColor"')
    inner = inner.replace("SVGID_1_", "logoClipRect").replace("SVGID_2_", "logoClip")
    inner = inner.replace("xlink:href", "href")
    return '<g id="logo-g">' + re.sub(r"\s+", " ", inner).strip() + "</g>"


def header(active: str) -> str:
    links = "".join(
        f'<li><a href="{slug}.html"{" aria-current=\"page\"" if slug == active else ""}>{label}</a></li>'
        for slug, label in NAV
    )
    drawer = "".join(
        f'<li><a href="{slug}.html"{" aria-current=\"page\"" if slug == active else ""}>{label}</a></li>'
        for slug, label in [("index", "Accueil")] + NAV + [("contact", "Contact")]
    )
    cta = ('<div class="header__cta">'
           '<a class="btn btn--ghost btn--sm" href="soutenir.html">Nous soutenir</a>'
           '<a class="btn btn--sm" href="hotels.html">Devenir hôtel membre</a></div>')
    return f"""
<div class="topbar"><div class="wrap">
  <span class="topbar__left">Fondation à but non lucratif · Suisse, France, Allemagne</span>
  <div class="topbar__right"><a href="soutenir.html">Faire un don</a><nav class="lang" aria-label="Langue"><a href="#" aria-current="true">FR</a><a href="#">DE</a><a href="#">EN</a></nav></div>
</div></div>
<header class="header" id="header">
  <div class="wrap header__in">
    <a class="brand" href="index.html" aria-label="SapoCycle, accueil"><svg viewBox="0 0 348.4 372" aria-hidden="true"><use href="#logo-g"/></svg><span><b>SapoCycle</b><small>Bubbles saving lives</small></span></a>
    <nav aria-label="Navigation principale"><ul class="nav">{links}</ul></nav>
    {cta}
    <button class="burger" type="button" aria-expanded="false" aria-controls="drawer">Menu<i aria-hidden="true"></i></button>
  </div>
</header>
<div class="drawer" id="drawer"><ul>{drawer}</ul>{cta}</div>
"""


PREFOOT = """
<section class="prefoot"><div class="wrap"><div class="prefoot__in" data-reveal>
  <span class="prefoot__bub" style="width:420px;height:420px;right:-120px;top:-180px" aria-hidden="true"></span>
  <span class="prefoot__bub" style="width:160px;height:160px;right:22%;bottom:-70px" aria-hidden="true"></span>
  <div style="display:grid;gap:16px"><span class="eyebrow">Pour les hôtels</span><h2 class="h2">Vos savons usagés peuvent <em>aider une famille.</em></h2></div>
  <div class="prefoot__actions"><a class="btn btn--light" href="contact.html">Nous contacter<svg aria-hidden="true"><use href="#i-arrow"/></svg></a></div>
</div></div></section>
"""

FOOTER = """
<footer class="footer">
  <div class="wrap">
    <div class="footer__top">
      <div class="footer__brand">
        <svg viewBox="0 0 348.4 410.8" role="img" aria-label="SapoCycle, Bubbles saving lives"><use href="#logo-g"/></svg>
        <p>SapoCycle collecte les savons usagés des hôtels, les fait recycler par des personnes en situation de handicap et les offre à des familles dans le besoin.</p>
        <form class="news-form" data-news><label class="visually-hidden" for="news-mail">Votre e-mail</label><input id="news-mail" type="email" placeholder="Votre e-mail pour la newsletter" autocomplete="email" required><button class="btn btn--sm" type="submit">S'abonner</button></form>
      </div>
      <div><h4>SapoCycle</h4><ul><li><a href="qui-sommes-nous.html">Qui sommes-nous</a></li><li><a href="notre-approche.html">Notre approche</a></li><li><a href="impact.html">Impact</a></li><li><a href="actualites.html">Actualités</a></li></ul></div>
      <div><h4>S'engager</h4><ul><li><a href="hotels.html">Hôtels membres</a></li><li><a href="partenaires.html">Partenaires</a></li><li><a href="soutenir.html">Faire un don</a></li><li><a href="soutenir.html#family">SapoCycle Family</a></li><li><a href="contact.html">Contact</a></li></ul></div>
      <div><h4>Fondation SapoCycle</h4><ul><li>Fondation des Fondateurs</li><li>Breitingerstrasse 35</li><li>8002 Zurich, Suisse</li><li><a href="contact.html">info@sapocycle.org</a></li></ul></div>
    </div>
    <div class="footer__labels"><span>Recommandé par</span><b>Green Globe</b><b>Clef Verte</b><b>EarthCheck</b><b>myclimate</b><b>Green Sign</b></div>
    <div class="footer__bottom">
      <span>© 2026 Fondation SapoCycle. Maquette de refonte.</span>
      <div class="socials">
        <a href="https://www.linkedin.com/company/sapocycle/" target="_blank" rel="noopener" aria-label="LinkedIn"><svg aria-hidden="true"><use href="#i-linkedin"/></svg></a>
        <a href="https://www.instagram.com/sapocycle/" target="_blank" rel="noopener" aria-label="Instagram"><svg aria-hidden="true"><use href="#i-instagram"/></svg></a>
        <a href="https://www.facebook.com/sapocycle/" target="_blank" rel="noopener" aria-label="Facebook"><svg aria-hidden="true"><use href="#i-facebook"/></svg></a>
      </div>
    </div>
  </div>
</footer>
"""


def page(slug: str, meta: dict, content: str, logo: str, fragment: bool) -> str:
    title = meta["title"]
    head = f"""<title>{title}</title>
<meta name="claude:viewport-fit-sides" content="cover">
<meta name="description" content="{meta['desc']}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="{FONTS}">
<link rel="stylesheet" href="assets/css/site.css">"""
    body = f"""<a class="skip" href="#main">Aller au contenu</a>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>{logo}{ICONS}</defs></svg>
{header(meta.get('nav', slug))}
<main id="main">
{content}
{'' if meta.get('noprefoot') else PREFOOT}
</main>
{FOOTER}
<script src="{GSAP}"></script>
<script src="{SCROLLTRIGGER}"></script>
<script src="{LENIS}"></script>
<script src="assets/js/site.js"></script>"""
    if fragment:
        return head + "\n" + body + "\n"
    return f"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
{head}
</head>
<body>
{body}
</body>
</html>
"""


def build() -> None:
    logo = logo_group()
    film = (SRC / "film.html").read_text(encoding="utf-8")
    for src in sorted((SRC / "pages").glob("*.html")):
        raw = src.read_text(encoding="utf-8")
        first, content = raw.split("\n", 1)
        meta = json.loads(first.strip()[4:-3])
        content = content.replace("{{FILM}}", film)
        slug = src.stem
        out = "index" if slug == "accueil" else slug
        (ROOT / f"{out}.html").write_text(page(slug, meta, content, logo, False), encoding="utf-8")
        if slug == "accueil":
            (ROOT / "artifact.html").write_text(page(slug, meta, content, logo, True), encoding="utf-8")
        print("page", out)


if __name__ == "__main__":
    build()

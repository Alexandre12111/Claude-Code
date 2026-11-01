"""Assemble les 9 pages du site SapoCycle en un seul fichier HTML interactif et autonome.

Sortie : sapocycle-site-complet.html (styles, scripts du site, logo et photos intégrés).
Seules les polices Google et les bibliothèques d'animation sont chargées depuis Internet.
"""
import base64
import json
import pathlib
import re

import build

ROOT = pathlib.Path(__file__).parent
OUT = ROOT / "sapocycle-site-complet.html"
BLANK = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
ORDER = ["accueil", "qui-sommes-nous", "notre-approche", "hotels", "impact", "partenaires", "actualites", "soutenir", "contact"]

ROUTER = """
(function () {
  'use strict';
  var pages = Array.prototype.slice.call(document.querySelectorAll('[data-page]'));
  var names = pages.map(function (p) { return p.dataset.page; });
  var links = Array.prototype.slice.call(document.querySelectorAll('.nav a, .drawer a'));
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var ctx = null, current = null;

  function show(name) {
    var film = document.getElementById('film');
    if (film && film._pause) film._pause();
    pages.forEach(function (p) { p.hidden = p.dataset.page !== name; });
    current = name;
    var el = pages[names.indexOf(name)];
    document.title = el.dataset.title;
    links.forEach(function (a) {
      if (a.getAttribute('href') === '#' + name) a.setAttribute('aria-current', 'page');
      else a.removeAttribute('aria-current');
    });
    var root = document.documentElement, burger = document.querySelector('.burger');
    root.classList.remove('menu-open');
    if (burger) burger.setAttribute('aria-expanded', 'false');
    var lenis = window.SapoCycle && window.SapoCycle.lenis;
    if (lenis) { lenis.start(); lenis.scrollTo(0, { immediate: true, force: true }); } else window.scrollTo(0, 0);
    if (window.ScrollTrigger) {
      if (ctx) ctx.revert();
      ScrollTrigger.getAll().forEach(function (t) { t.kill(); });
    }
    if (window.gsap) ctx = gsap.context(function () { window.SapoCycle.animate(el); }, el);
    else window.SapoCycle.animate(el);
    if (window.ScrollTrigger) ScrollTrigger.refresh();
  }

  function route() {
    var h = decodeURIComponent(location.hash.slice(1));
    if (!h) h = 'accueil';
    if (names.indexOf(h) === -1) {
      // Ancre interne : on affiche sa page si besoin
      var t = document.getElementById(h);
      var host = t && t.closest('[data-page]');
      if (host && host.dataset.page !== current) show(host.dataset.page);
      return;
    }
    if (h === current) return;
    if (current && document.startViewTransition && !reduced) document.startViewTransition(function () { show(h); });
    else show(h);
  }
  window.addEventListener('hashchange', route);
  route();
})();
"""


def data_uri(path: pathlib.Path) -> str:
    return "data:image/webp;base64," + base64.b64encode(path.read_bytes()).decode()


def rewrite_links(html: str) -> str:
    # Liens entre pages : x.html ou x.html#ancre deviennent #x
    def repl(m):
        slug = m.group(1)
        return 'href="#' + ("accueil" if slug == "index" else slug) + '"'
    return re.sub(r'href="(?!https?:)([a-z-]+)\.html(?:#[\w-]+)?"', repl, html)


def main() -> None:
    logo = build.logo_group()
    film = (build.SRC / "film.html").read_text(encoding="utf-8")
    sections = []
    for slug in ORDER:
        raw = (build.SRC / "pages" / f"{slug}.html").read_text(encoding="utf-8")
        first, content = raw.split("\n", 1)
        meta = json.loads(first.strip()[4:-3])
        content = content.replace("{{FILM}}", film)
        prefoot = "" if meta.get("noprefoot") else build.PREFOOT
        sections.append(f'<div class="page" data-page="{slug}" data-title="{meta["title"]}" hidden>\n{content}\n{prefoot}\n</div>')

    header = build.header("")
    body = f"""<a class="skip" href="#main">Aller au contenu</a>
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>{logo}{build.ICONS}</defs></svg>
{header}
<main id="main">
{chr(10).join(sections)}
</main>
{build.FOOTER}"""
    body = rewrite_links(body)
    body = body.replace('href="#main"', 'href="#main" onclick="document.getElementById(\'main\').focus();return false;"', 1)

    # Chaque photo n'est intégrée qu'une fois ; les reprises copient sa source au chargement
    seen = set()

    def img(m):
        name = m.group(1)
        if name in seen:
            return f'src="{BLANK}" data-same="{name}"'
        seen.add(name)
        return f'src="{data_uri(ROOT / "assets" / "img" / name)}" data-img="{name}"'

    body = re.sub(r'src="assets/img/([\w-]+\.webp)"', img, body)

    css = (ROOT / "assets" / "css" / "site.css").read_text(encoding="utf-8")
    css += "\n.page[hidden]{display:none!important}\nmain:focus{outline:none}\n"
    js = (ROOT / "assets" / "js" / "site.js").read_text(encoding="utf-8")
    same = ("document.querySelectorAll('img[data-same]').forEach(function (i) {"
            " var s = document.querySelector('img[data-img=\"' + i.dataset.same + '\"]'); if (s) i.src = s.src; });")

    html = f"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>SapoCycle · Recyclage des savons d'hôtel</title>
<meta name="description" content="SapoCycle collecte les savons usagés des hôtels, les fait recycler par des personnes en situation de handicap et les offre à des familles dans le besoin.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="{build.FONTS}">
<style>
{css}
</style>
</head>
<body>
{body}
<script src="{build.GSAP}"></script>
<script src="{build.SCROLLTRIGGER}"></script>
<script src="{build.LENIS}"></script>
<script>window.SAPO_SPA = true; {same}</script>
<script>
{js}
</script>
<script>
{ROUTER}
</script>
</body>
</html>
"""
    html = html.replace('<main id="main">', '<main id="main" tabindex="-1">', 1)
    OUT.write_text(html, encoding="utf-8")
    print(f"{OUT.name} : {len(html) / 1024 / 1024:.2f} Mo, {len(seen)} photos intégrées")


if __name__ == "__main__":
    main()

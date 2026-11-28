#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur de la maquette du site de Me Charles Culioli-Paoli (Python 3, sans dépendance).

    python3 build.py

Assemble chaque page (en-tête, navigation, pied de page, fenêtre « Urgence pénale »,
balises SEO, Open Graph, JSON-LD), écrit les fichiers HTML à la racine de la maquette,
puis produit sitemap.xml, robots.txt et plan-seo.html (contrôle page par page).

Les liens internes pointent vers les fichiers locaux (page.html) pour que la maquette
s'ouvre d'un double clic ; canonical, Open Graph, JSON-LD et sitemap utilisent
les URL de production (/slug/).
"""
import datetime
import html
import json
import os
import re

from content import PAGES, SITE
from direction import DIRECTION

ROOT = os.path.dirname(os.path.abspath(__file__))
E = html.escape
TODAY = datetime.date.today().isoformat()

ICON = {
    "arrow": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 12h16M14 6l6 6-6 6"/></svg>',
    "menu": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 7h18M3 12h18M9 17h12"/></svg>',
    "close": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>',
    "phone": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>',
    "mail": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="5" width="18" height="14"/><path d="m3 7 9 6 9-6"/></svg>',
    "wa": '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.8-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2s.2-1.1.1-1.2l-.5-.2Z"/></svg>',
}

NAV = [
    ("charles-culioli-paoli-avocat", "Le cabinet"),
    ("expertises", "Expertises"),
    ("interventions", "Territoires"),
    ("ressources", "Ressources"),
    ("honoraires", "Honoraires"),
    ("contact", "Contact"),
]


def href(slug):
    return "index.html" if slug in ("", "index") else f"{slug}.html"


def prod(slug):
    return SITE["url"] + ("/" if slug in ("", "index") else f"/{slug}/")


def local_links(body):
    """Réécrit les liens de production /slug/ en fichiers locaux slug.html."""
    def rep(m):
        s = m.group(1)
        return f'href="{href(s.strip("/"))}{m.group(2) or ""}"'
    return re.sub(r'href="/([a-z0-9\-]*)/?(#[a-z0-9\-]+)?"', rep, body)


def header(slug):
    links = "".join(
        f'<a href="{href(s)}"{" aria-current=\"page\"" if s == slug else ""}>{l}</a>'
        for s, l in NAV)
    dlinks = "".join(f'<a href="{href(s)}">{l}</a>' for s, l in NAV)
    return f"""<a class="skip" href="#contenu">Aller au contenu</a>
<div class="mock-flag">Maquette de travail · contenus soumis à la validation de Me Culioli-Paoli · <a href="direction-artistique.html">Direction artistique</a> · <a href="plan-seo.html">Plan SEO</a></div>
<header class="site-header">
  <div class="wrap">
    <a class="brand" href="index.html" aria-label="Accueil, Charles Culioli-Paoli, avocat">
      <span class="brand-name">Charles Culioli-Paoli</span>
      <span class="brand-sub">Avocat au Barreau de Paris</span>
    </a>
    <nav class="nav" aria-label="Navigation principale">{links}</nav>
    <div class="head-actions">
      <button class="urg-link" type="button" data-urgence aria-haspopup="dialog"><span class="dot" aria-hidden="true"></span>Urgence pénale</button>
      <a class="btn btn--line btn--sm" href="contact.html">Rendez-vous</a>
    </div>
    <button class="menu-toggle" type="button" data-menu-open aria-controls="drawer" aria-expanded="false"><span>Menu</span></button>
  </div>
</header>
<div class="drawer" id="drawer" aria-hidden="true">
  <div class="drawer-top">
    <span class="brand-name">Charles Culioli-Paoli</span>
    <button class="menu-toggle" type="button" data-menu-close style="display:inline-flex"><span>Fermer</span></button>
  </div>
  <nav aria-label="Menu mobile"><a href="index.html">Accueil</a>{dlinks}</nav>
  <div class="drawer-foot">
    <button class="btn btn--solid" type="button" data-urgence>Urgence pénale</button>
    <a class="btn btn--line" href="contact.html">Prendre rendez-vous</a>
  </div>
</div>"""


def footer():
    s = SITE
    exp = "".join(f'<li><a href="{href(x["slug"])}">{E(x["nav"])}</a></li>' for x in PAGES if x.get("group") == "expertise")
    return f"""<footer class="site-footer">
  <div class="wrap">
    <div class="foot-top">
      <div>
        <p class="foot-name">Charles Culioli-Paoli</p>
        <p class="foot-role">Avocat au Barreau de Paris · Droit pénal</p>
      </div>
      <div class="foot-cta"><a class="btn btn--light" href="contact.html">Prendre rendez-vous</a><button class="urg-link urg-link--light" type="button" data-urgence><span class="dot" aria-hidden="true"></span>Urgence pénale</button></div>
    </div>
    <div class="foot-grid">
      <div>
        <h2>Cabinet</h2>
        <address>{s["address"]}<span class="todo">à confirmer</span><br>Bureau secondaire à Massy<span class="todo">à confirmer</span></address>
        <p>Sur rendez-vous, au cabinet ou en visioconférence.</p>
      </div>
      <div><h2>Expertises</h2><ul>{exp}</ul></div>
      <div><h2>Territoires</h2><ul>
        <li><a href="avocat-penaliste-paris.html">Paris</a></li>
        <li><a href="avocat-penaliste-essonne.html">Essonne</a></li>
        <li><a href="etablissements-penitentiaires.html">Établissements pénitentiaires</a></li>
        <li><a href="avocat-centre-penitentiaire-fleury-merogis.html">Fleury-Mérogis</a></li>
        <li><a href="interventions.html">Autres territoires</a></li>
      </ul></div>
      <div><h2>Informations</h2><ul>
        <li><a href="charles-culioli-paoli-avocat.html">Le cabinet</a></li>
        <li><a href="honoraires.html">Honoraires</a></li>
        <li><a href="avocat-garde-a-vue-paris.html">Garde à vue</a></li>
        <li><a href="ressources.html">Ressources</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul></div>
    </div>
    <div class="foot-legal">
      <span>© {datetime.date.today().year} Charles Culioli-Paoli, avocat. Informations générales ne constituant pas une consultation juridique.</span>
      <span><a href="mentions-legales.html">Mentions légales</a><a href="mentions-legales.html#donnees">Confidentialité</a><a href="mentions-legales.html#cookies">Cookies</a></span>
    </div>
  </div>
</footer>"""


def chrome_end():
    s = SITE
    return f"""<dialog class="urgence" id="urgence" aria-labelledby="urg-title">
  <div class="urg-in">
    <button class="urg-close" type="button" data-urg-close aria-label="Fermer">{ICON["close"]}</button>
    <p class="eyebrow">Urgence pénale · 24h/24, 7j/7<span class="todo">à confirmer</span></p>
    <h2 id="urg-title">Garde à vue, perquisition, déferrement</h2>
    <p>Une personne placée en garde à vue peut demander l'assistance d'un avocat dès le début de la mesure. Communiquez aux enquêteurs le nom de Me Charles Culioli-Paoli, ou appelez directement le cabinet.</p>
    <button class="btn btn--light urg-reveal" id="urg-reveal" type="button" data-n="{s["tel"]}" data-d="{s["tel_display"]}">Afficher le numéro d'urgence</button>
    <div class="urg-number" id="urg-number" hidden>
      <a class="tel" href="#">…</a>
      <div class="row">
        <a class="link link--light" href="{s["whatsapp"]}" rel="noopener" target="_blank">Écrire sur WhatsApp</a>
        <button class="link link--light" type="button" id="urg-copy"><span>Copier le numéro</span></button>
      </div>
      <p class="urg-law">Article 63-3-1 du Code de procédure pénale : dès le début de la garde à vue, la personne peut demander à être assistée par un avocat. Elle a également le droit de se taire.</p>
    </div>
    <p class="urg-note">Numéro provisoire de maquette<span class="todo">numéro à fournir</span></p>
  </div>
</dialog>
<a class="wa-float" href="{s["whatsapp"]}" target="_blank" rel="noopener" aria-label="Écrire au cabinet sur WhatsApp">{ICON["wa"]}</a>
<nav class="mbar" aria-label="Actions rapides">
  <button type="button" data-urgence><span class="dot" aria-hidden="true"></span>Urgence</button>
  <a href="{s["whatsapp"]}" target="_blank" rel="noopener">WhatsApp</a>
  <a href="contact.html">Rendez-vous</a>
</nav>
<script src="assets/site.js" defer></script>"""


def jsonld(p):
    s = SITE
    graph = [{
        "@type": ["LegalService", "Attorney"],
        "@id": s["url"] + "/#cabinet",
        "name": "Cabinet Charles Culioli-Paoli, avocat",
        "url": s["url"] + "/",
        "image": s["url"] + "/assets/img/charles-culioli-paoli-tribunal-paris.webp",
        "telephone": s["tel"],
        "priceRange": "Sur devis",
        "address": {"@type": "PostalAddress", "streetAddress": "264 rue du Faubourg Saint-Honoré",
                    "postalCode": "75008", "addressLocality": "Paris", "addressCountry": "FR"},
        "areaServed": ["Paris", "Essonne", "Île-de-France", "France"],
        "knowsLanguage": ["fr", "en", "es", "ar", "co"],
        "founder": {"@id": s["url"] + "/#charles"},
    }, {
        "@type": "Person",
        "@id": s["url"] + "/#charles",
        "name": "Charles Culioli-Paoli",
        "jobTitle": "Avocat",
        "worksFor": {"@id": s["url"] + "/#cabinet"},
        "alumniOf": [{"@type": "CollegeOrUniversity", "name": "Harvard Law School"},
                     {"@type": "CollegeOrUniversity", "name": "Sciences Po, École de droit"}],
        "knowsLanguage": ["fr", "en", "es", "ar", "co"],
        "image": s["url"] + "/assets/img/charles-culioli-paoli-portrait.webp",
        "url": prod("charles-culioli-paoli-avocat"),
    }]
    if p["slug"] != "index":
        items = [{"@type": "ListItem", "position": 1, "name": "Accueil", "item": prod("index")}]
        for i, (lbl, sl) in enumerate(p.get("crumbs", []), 2):
            items.append({"@type": "ListItem", "position": i, "name": lbl, "item": prod(sl)})
        items.append({"@type": "ListItem", "position": len(items) + 1, "name": p["nav"], "item": prod(p["slug"])})
        graph.append({"@type": "BreadcrumbList", "itemListElement": items})
    if p.get("schema") == "Service":
        graph.append({"@type": "Service", "name": p["nav"], "serviceType": p["kw"], "provider": {"@id": s["url"] + "/#cabinet"},
                      "areaServed": p.get("area", "France"), "url": prod(p["slug"])})
    if p.get("schema") == "Article":
        graph.append({"@type": "Article", "headline": p["h1_plain"], "author": {"@id": s["url"] + "/#charles"},
                      "datePublished": TODAY, "dateModified": TODAY, "mainEntityOfPage": prod(p["slug"])})
    if p.get("faq"):
        graph.append({"@type": "FAQPage", "mainEntity": [
            {"@type": "Question", "name": q, "acceptedAnswer": {"@type": "Answer", "text": re.sub("<[^>]+>", "", a)}}
            for q, a in p["faq"]]})
    return json.dumps({"@context": "https://schema.org", "@graph": graph}, ensure_ascii=False, indent=1)


def crumbs_html(p):
    if p["slug"] == "index":
        return ""
    parts = ['<li><a href="index.html">Accueil</a></li>']
    parts += [f'<li><a href="{href(sl)}">{E(l)}</a></li>' for l, sl in p.get("crumbs", [])]
    parts.append(f'<li aria-current="page">{E(p["nav"])}</li>')
    return f'<ol class="crumbs" aria-label="Fil d\'Ariane">{"".join(parts)}</ol>'


def faq_html(p):
    if not p.get("faq"):
        return ""
    items = "".join(f"<details><summary>{q}</summary><div><p>{a}</p></div></details>" for q, a in p["faq"])
    return f'<h2 id="faq">Questions fréquentes</h2><div class="faq">{items}</div>'


def render(p):
    s = SITE
    robots = "noindex, nofollow" if p.get("noindex") else "index, follow, max-image-preview:large"
    body = p["body"](p) if callable(p["body"]) else p["body"]
    body = body.replace("{{CRUMBS}}", crumbs_html(p)).replace("{{FAQ}}", faq_html(p))
    body = body.replace("{{ARROW}}", ICON["arrow"]).replace("{{WA}}", ICON["wa"]).replace("{{PHONE}}", ICON["phone"]).replace("{{MAIL}}", ICON["mail"])
    body = local_links(body)
    img = s["url"] + "/assets/img/" + p.get("og", "charles-culioli-paoli-tribunal-paris.webp")
    return f"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{E(p["title"])}</title>
<meta name="claude:viewport-fit-sides" content="cover">
<meta name="description" content="{E(p["desc"])}">
<meta name="robots" content="{robots}">
<link rel="canonical" href="{prod(p["slug"])}">
<meta property="og:type" content="{"article" if p.get("schema") == "Article" else "website"}">
<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="Charles Culioli-Paoli, avocat">
<meta property="og:title" content="{E(p["title"])}">
<meta property="og:description" content="{E(p["desc"])}">
<meta property="og:url" content="{prod(p["slug"])}">
<meta property="og:image" content="{img}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#F6F2EA">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Manrope:wght@400;500;600&display=swap">
<link rel="stylesheet" href="assets/site.css">
<script type="application/ld+json">
{jsonld(p)}
</script>
</head>
<body>
{header(p.get("nav_slug", "expertises" if p.get("group") in ("expertise", "procedure") else p["slug"]))}
<main id="contenu">
{body}
</main>
{footer()}
{chrome_end()}
</body>
</html>
"""


def seo_plan():
    rows = []
    for p in PAGES:
        if p.get("noindex"):
            continue
        tl, dl = len(p["title"]), len(p["desc"])
        ok = lambda c: '<span class="pill pill--on">OK</span>' if c else '<span class="pill">À revoir</span>'
        rows.append(f"""<tr><td><b>{E(p["nav"])}</b><br><span class="small muted">/{"" if p["slug"] == "index" else p["slug"] + "/"}</span></td>
<td>{E(p["kw"])}<br><span class="small muted">{E(", ".join(p.get("kw2", [])))}</span></td>
<td>{E(p["title"])}<br><span class="small muted">{tl} car.</span> {ok(45 <= tl <= 62)}</td>
<td>{E(p["desc"])}<br><span class="small muted">{dl} car.</span> {ok(135 <= dl <= 162)}</td>
<td>{E(p["h1_plain"])}</td><td>{E(p.get("schema_label", p.get("schema", "WebPage")))}</td><td>{E(p.get("prio", ""))}</td></tr>""")
    body = f"""<section class="page-head"><div class="wrap">{{{{CRUMBS}}}}
<h1>Plan SEO <em>page par page</em></h1>
<p class="intro">Contrôle des règles Rank Math pour chaque page de la maquette : mot-clé principal, title (50 à 60 caractères), meta description (140 à 160 caractères), H1 unique et schéma. Page interne, non indexée.</p></div></section>
<section class="section"><div class="wrap"><div class="table-wrap"><table>
<thead><tr><th>Page et URL</th><th>Mot-clé principal et secondaires</th><th>SEO title</th><th>Meta description</th><th>H1</th><th>Schema</th><th>Priorité</th></tr></thead>
<tbody>{"".join(rows)}</tbody></table></div></div></section>"""
    return {"slug": "plan-seo", "nav": "Plan SEO", "title": "Plan SEO de la maquette | Charles Culioli-Paoli",
            "desc": "Contrôle interne des balises SEO.", "kw": "", "h1_plain": "Plan SEO", "noindex": True, "body": body}


def main():
    pages = PAGES + [seo_plan(), DIRECTION]
    for p in pages:
        out = os.path.join(ROOT, href(p["slug"]))
        open(out, "w", encoding="utf-8").write(render(p))
    urls = "".join(f"<url><loc>{prod(p['slug'])}</loc><lastmod>{TODAY}</lastmod></url>\n" for p in PAGES if not p.get("noindex"))
    open(os.path.join(ROOT, "sitemap.xml"), "w").write(
        f'<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n{urls}</urlset>\n')
    open(os.path.join(ROOT, "robots.txt"), "w").write(
        f"User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nDisallow: /?s=\n\nSitemap: {SITE['url']}/sitemap_index.xml\n")
    print(f"{len(pages)} pages écrites.")


if __name__ == "__main__":
    main()

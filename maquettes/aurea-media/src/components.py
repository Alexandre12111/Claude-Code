# -*- coding: utf-8 -*-
"""
Composants de la maquette Aurea Media.
Chaque composant correspond à un futur bloc du thème WordPress : même balisage,
mêmes classes, mêmes données (lues depuis data.py, puis depuis les réglages).
"""
import html
import math

from data import SITE, BRIEF, SERVICES, PLANS, PROJECTS, PROJECT_CATS, REVIEWS, POSTS, POST_CATS

E = html.escape

# ---------------------------------------------------------------------------
# Icônes (SVG en ligne, trait 1,5 ; dessinées pour la maquette)
# ---------------------------------------------------------------------------
ICONS = {
    "arrow": '<path d="M7 17 17 7M8.5 7H17v8.5"/>',
    "arrow-right": '<path d="M4 12h15M13 6l6 6-6 6"/>',
    "check": '<path d="M4.5 12.5l4.8 4.8L19.5 7"/>',
    "phone": '<path d="M5 3.5h3.2l1.6 4.2-2.2 1.4a11.5 11.5 0 0 0 7.3 7.3l1.4-2.2 4.2 1.6V19a1.5 1.5 0 0 1-1.6 1.5A16.5 16.5 0 0 1 3.5 5.1 1.5 1.5 0 0 1 5 3.5z"/>',
    "mail": '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
    "pin": '<path d="M12 21s-6.5-5.8-6.5-11a6.5 6.5 0 0 1 13 0c0 5.2-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.4"/>',
    "clock": '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2"/>',
    "calendar": '<rect x="3" y="4.5" width="18" height="16.5" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/>',
    "shield": '<path d="M12 3l7 3v5.2c0 4.6-3.1 8.2-7 9.8-3.9-1.6-7-5.2-7-9.8V6z"/><path d="m8.8 12 2.2 2.2 4.2-4.4"/>',
    "key": '<circle cx="8" cy="15" r="4.5"/><path d="M11.2 11.8 20 3M16.5 6.5l2.5 2.5"/>',
    "layout": '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M9 9v11"/>',
    "bag": '<path d="M5 8h14l-1.1 11.2a2 2 0 0 1-2 1.8H8.1a2 2 0 0 1-2-1.8z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/>',
    "refresh": '<path d="M19.5 10.5A7.8 7.8 0 0 0 5.4 7.6M4.5 3.5v4.2h4.2M4.5 13.5a7.8 7.8 0 0 0 14.1 2.9M19.5 20.5v-4.2h-4.2"/>',
    "search": '<circle cx="11" cy="11" r="6.8"/><path d="m20 20-4.2-4.2"/>',
    "pen": '<path d="M12 20h8.5M16.3 3.8a2.1 2.1 0 0 1 3 3L7.2 18.9 3.5 20l1.1-3.7z"/>',
    "slides": '<rect x="3" y="4" width="18" height="12" rx="1.6"/><path d="M12 16v4.5M8 20.5h8M7 12.5l3-3 2.2 2.2 4-4"/>',
    "users": '<circle cx="9" cy="8" r="3.4"/><path d="M2.8 20a6.2 6.2 0 0 1 12.4 0M16 4.8a3.4 3.4 0 0 1 0 6.5M21.2 20a6.2 6.2 0 0 0-3.7-5.7"/>',
    "zap": '<path d="M13 2.5 4.5 13.5H11l-1 8L19.5 10H13z"/>',
    "trend": '<path d="m3 17 6-6 4 4 8-8M15 7h6v6"/>',
    "file": '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
    "lock": '<rect x="4.5" y="10.5" width="15" height="10.5" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>',
    "globe": '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a13.5 13.5 0 0 1 0 18M12 3a13.5 13.5 0 0 0 0 18"/>',
    "message": '<path d="M20.5 12a8.5 8.5 0 0 1-12.3 7.6L3.5 21l1.4-4.6A8.5 8.5 0 1 1 20.5 12z"/>',
    "target": '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.2"/>',
    "gauge": '<path d="M3.8 17.5a9 9 0 1 1 16.4 0"/><path d="m12 14 4.2-4.2"/>',
    "euro": '<path d="M17.5 6.4A7 7 0 1 0 17.5 17.6M4 10h9.5M4 14h9.5"/>',
    "chev": '<path d="m6 9 6 6 6-6"/>',
    "left": '<path d="M15 5.5 8.5 12l6.5 6.5"/>',
    "right": '<path d="M9 5.5l6.5 6.5L9 18.5"/>',
    "sparkle": '<path d="M12 2.5c.7 5 2.4 7 9.5 9.5-7.1 2.5-8.8 4.5-9.5 9.5-.7-5-2.4-7-9.5-9.5 7.1-2.5 8.8-4.5 9.5-9.5z" fill="currentColor" stroke="none"/>',
    "star": '<path d="M12 2.8l2.8 5.9 6.4.8-4.7 4.4 1.2 6.4L12 17.2l-5.7 3.1 1.2-6.4-4.7-4.4 6.4-.8z" fill="currentColor" stroke="none"/>',
    "linkedin": '<path d="M4.5 3.5h15a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1h-15a1 1 0 0 1-1-1v-15a1 1 0 0 1 1-1z"/><path d="M8 10.5V17M8 7.5v.01M11.5 17v-6.5M11.5 13.2c0-1.6 1-2.7 2.5-2.7s2.5 1 2.5 2.7V17"/>',
    "google": '<path d="M20.5 12.2c0-.6-.1-1.2-.2-1.7H12v3.3h4.8a4.1 4.1 0 0 1-1.8 2.7v2.2h2.9c1.7-1.6 2.6-3.9 2.6-6.5z"/><path d="M12 21c2.4 0 4.5-.8 5.9-2.2L15 16.5c-.8.5-1.8.9-3 .9-2.3 0-4.3-1.6-5-3.7H4.1V16A9 9 0 0 0 12 21z"/><path d="M7 13.7a5.4 5.4 0 0 1 0-3.4V8H4.1a9 9 0 0 0 0 8z"/><path d="M12 6.6c1.3 0 2.5.5 3.4 1.3L18 5.4A9 9 0 0 0 4.1 8L7 10.3c.7-2.1 2.7-3.7 5-3.7z"/>',
    "quote": '<path d="M9.5 7.5C6.5 8.3 5 10.5 5 13.5V17h4.5v-4.5H7c0-1.8.9-3 2.5-3.5zM19 7.5c-3 .8-4.5 3-4.5 6V17H19v-4.5h-2.5c0-1.8.9-3 2.5-3.5z"/>',
    "home": '<path d="M4 11 12 4l8 7v8.5a1.5 1.5 0 0 1-1.5 1.5h-4v-6h-5v6h-4A1.5 1.5 0 0 1 4 19.5z"/>',
    "list": '<path d="M9 6h11M9 12h11M9 18h11M4.5 6h.01M4.5 12h.01M4.5 18h.01"/>',
}


def icon(name, cls="", label=None):
    body = ICONS.get(name, "")
    aria = f'role="img" aria-label="{E(label)}"' if label else 'aria-hidden="true"'
    c = f' class="{cls}"' if cls else ""
    return (f'<svg{c} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" '
            f'stroke-linecap="round" stroke-linejoin="round" {aria} focusable="false">{body}</svg>')


def stars(n=5):
    return '<span class="stars" aria-hidden="true">' + "".join(icon("star") for _ in range(n)) + "</span>"


def euro(n):
    """1000 -> '1 000' avec espace fine insécable."""
    return f"{n:,}".replace(",", " ")


# ---------------------------------------------------------------------------
# Marque
# ---------------------------------------------------------------------------
def logo_mark(cls="brand__mark", uid="m"):
    # « A » d'Aurea redessiné en vecteur : triangle, courbe évidée, point d'or.
    return (
        f'<svg class="{cls}" viewBox="0 0 64 64" aria-hidden="true" focusable="false">'
        f'<defs><mask id="{uid}-cut"><rect width="64" height="64" fill="#fff"/>'
        '<path d="M47.5 27.5C39 31.5 34.2 39.8 33.6 49" stroke="#000" stroke-width="3.4" fill="none" stroke-linecap="round"/>'
        '<circle cx="33.4" cy="53.6" r="6.6" fill="#000"/></mask></defs>'
        f'<path mask="url(#{uid}-cut)" d="M29.2 5.8c1.3-2.4 4.3-2.4 5.6 0l25.4 47.6c1.2 2.3-.6 5-3.1 4.6-7.3-1.1-15.9-1.9-25.1-1.9s-17.8.8-25.1 1.9c-2.5.4-4.3-2.3-3.1-4.6z" fill="currentColor"/>'
        '<circle cx="33.4" cy="53.6" r="3.9" fill="#C9A961"/></svg>'
    )


def brand(href="/", uid="hd"):
    return (f'<a class="brand" href="{href}" aria-label="Aurea Media, retour à l\'accueil">'
            f'{logo_mark(uid=uid)}<span class="brand__name">Aurea<em>Media</em></span></a>')


# ---------------------------------------------------------------------------
# Spirale d'or (rectangles de Fibonacci + quarts de cercle)
# ---------------------------------------------------------------------------
def golden_spiral(cls="phero__spiral golden-svg", n=10, draw=True, grid=True):
    H = 1000.0
    W = H * (1 + math.sqrt(5)) / 2
    x, y, w, h = 0.0, 0.0, W, H
    d = []
    rects = []
    for i in range(n):
        k = i % 4
        if k == 0:
            s = h; rects.append((x, y, s, s))
            if i == 0: d.append(f"M{x:.2f} {y + s:.2f}")
            d.append(f"A{s:.2f} {s:.2f} 0 0 1 {x + s:.2f} {y:.2f}")
            x, w = x + s, w - s
        elif k == 1:
            s = w; rects.append((x, y, s, s))
            d.append(f"A{s:.2f} {s:.2f} 0 0 1 {x + s:.2f} {y + s:.2f}")
            y, h = y + s, h - s
        elif k == 2:
            s = h; rects.append((x + w - s, y, s, s))
            d.append(f"A{s:.2f} {s:.2f} 0 0 1 {x + w - s:.2f} {y + s:.2f}")
            w = w - s
        else:
            s = w; rects.append((x, y + h - s, s, s))
            d.append(f"A{s:.2f} {s:.2f} 0 0 1 {x:.2f} {y + h - s:.2f}")
            h = h - s
    lines = ""
    if grid:
        lines = "".join(f'<rect class="grid-l" x="{a:.2f}" y="{b:.2f}" width="{c:.2f}" height="{c:.2f}"/>' for a, b, c, _ in rects)
    attr = ' data-draw' if draw else ''
    return (f'<svg class="{cls}" viewBox="-2 -2 {W + 4:.0f} {H + 4:.0f}" aria-hidden="true" focusable="false"{attr}>'
            f'{lines}<path class="spiral" d="{" ".join(d)}"/></svg>')


# ---------------------------------------------------------------------------
# En-tête et menus
# ---------------------------------------------------------------------------
NAV = [
    ("Réalisations", "/realisations-agence-web/", "realisations"),
    ("Tarifs", "/prix-site-internet-paris/", "tarifs"),
    ("L'agence", "/a-propos/", "agence"),
    ("Conseils", "/nos-conseils-web/", "conseils"),
]


def header(section=""):
    mega_items = "".join(
        f'<li class="mega__item"><a href="{s["path"]}"><span class="mega__num">{s["num"]}</span>'
        f'<span class="mega__title">{E(s["name"])}</span><span class="mega__price">{E(s["price_label"])}</span>'
        f'<span class="mega__desc">{E(s["desc"])}</span></a></li>' for s in SERVICES)
    svc_current = ' aria-current="page"' if section == "services" else ""
    links = "".join(
        f'<li><a class="nav__link" href="{href}"{" aria-current=page" if section == key else ""}>{E(label)}</a></li>'
        for label, href, key in NAV)
    links = links.replace("aria-current=page", 'aria-current="page"')
    mnav_svcs = "".join(f'<li><a href="{s["path"]}">{E(s["name"])}</a></li>' for s in SERVICES)
    return f'''
<a class="skip" href="#contenu">Aller au contenu</a>
<div class="progress" aria-hidden="true"><span></span></div>
<header class="hd" data-hd>
  <div class="hd__in container">
    {brand("/", "hd")}
    <nav class="nav" aria-label="Navigation principale">
      <ul class="nav__list">
        <li class="has-mega">
          <button class="nav__link" type="button" aria-expanded="false" aria-controls="mega-services"{svc_current}>Services {icon("chev", "chev")}</button>
          <div class="mega" id="mega-services">
            <ul class="mega__list">{mega_items}</ul>
            <div class="mega__aside">
              <p class="label" style="color:var(--gold)">Simulateur</p>
              <p class="h3">Quel budget pour <em style="color:var(--gold)">votre</em> site&nbsp;?</p>
              <p>Trois questions, trente secondes, et la formule qui vous correspond, prix compris.</p>
              <a class="btn btn--gold btn--sm" href="/prix-site-internet-paris/#simulateur">Estimer mon projet {icon("arrow", "arr")}</a>
            </div>
          </div>
        </li>
        {links}
      </ul>
    </nav>
    <div class="hd__actions">
      <a class="hd__tel" href="tel:{SITE["phone_href"]}">{icon("phone")}<span>{SITE["phone"]}</span></a>
      <a class="btn btn--gold btn--sm" href="/contact-agence/" data-magnetic><span>Devis gratuit</span> {icon("arrow", "arr")}</a>
      <button class="burger" type="button" aria-expanded="false" aria-controls="menu-mobile"><span></span><span></span><span class="sr-only">Ouvrir le menu</span></button>
    </div>
  </div>
</header>
<div class="mnav" id="menu-mobile" hidden>
  <nav aria-label="Navigation mobile">
    <ul class="mnav__list">
      <li><a href="/creation-site-internet-paris/">Services <small>06</small></a>
        <ul class="mnav__sub">{mnav_svcs}</ul></li>
      <li><a href="/realisations-agence-web/">Réalisations <small>20</small></a></li>
      <li><a href="/prix-site-internet-paris/">Tarifs</a></li>
      <li><a href="/a-propos/">L'agence</a></li>
      <li><a href="/nos-conseils-web/">Conseils</a></li>
      <li><a href="/contact-agence/">Contact</a></li>
    </ul>
  </nav>
  <div class="mnav__foot">
    <a class="btn btn--gold btn--block" href="/contact-agence/"><span>Recevoir mon devis sous 24&nbsp;h</span> {icon("arrow", "arr")}</a>
    <a href="tel:{SITE["phone_href"]}">{SITE["phone_intl"]}</a>
    <a href="mailto:{SITE["email"]}">{SITE["email"]}</a>
  </div>
</div>
<div class="cursor" aria-hidden="true"><div class="cursor__ring"><span class="cursor__label">Voir</span></div></div>
'''


# ---------------------------------------------------------------------------
# Pied de page
# ---------------------------------------------------------------------------
def footer():
    svc = "".join(f'<li><a href="{s["path"]}">{E(s["name"])}</a></li>' for s in SERVICES)
    return f'''
<footer class="ft" role="contentinfo">
  <div class="container">
    <div class="ft__top">
      <div class="ft__about">
        {brand("/", "ft")}
        <p>{E(BRIEF)}</p>
        <p class="small" style="display:flex;align-items:center;gap:.6rem">{stars()} <span><b style="color:var(--paper)">{SITE["rating"]}/5</b> sur {SITE["reviews"]} avis Google</span></p>
        <div class="socials">
          <a href="{SITE["linkedin_company"]}" rel="noopener" aria-label="Aurea Media sur LinkedIn">{icon("linkedin")}</a>
          <a href="{SITE["gbp"]}" rel="noopener" aria-label="Aurea Media sur Google">{icon("google")}</a>
          <a href="mailto:{SITE["email"]}" aria-label="Écrire à Aurea Media">{icon("mail")}</a>
        </div>
      </div>
      <div>
        <h2>Services</h2>
        <ul>{svc}<li><a href="/prix-site-internet-paris/">Tous les tarifs</a></li></ul>
      </div>
      <div>
        <h2>L'agence</h2>
        <ul>
          <li><a href="/a-propos/">À propos d'Aurea Media</a></li>
          <li><a href="/realisations-agence-web/">Réalisations</a></li>
          <li><a href="/realisations-agence-web/novexia-construction/">Étude de cas Novexia</a></li>
          <li><a href="/nos-conseils-web/">Conseils et guides</a></li>
          <li><a href="/agence-web-hauts-de-seine/">Agence web Hauts-de-Seine</a></li>
          <li><a href="/contact-agence/">Contact et devis</a></li>
        </ul>
      </div>
      <div>
        <h2>Contact</h2>
        <address class="ft__nap">
          <a href="tel:{SITE["phone_href"]}">{SITE["phone_intl"]}</a>
          <a href="mailto:{SITE["email"]}">{SITE["email"]}</a>
          <span>{SITE["district"]}, rendez-vous sur demande</span>
          <span>{SITE["hours"]}</span>
        </address>
        <a class="btn btn--gold btn--sm" style="margin-top:1.2rem" href="/contact-agence/"><span>Demander un devis</span> {icon("arrow", "arr")}</a>
      </div>
    </div>
    <p class="ft__word" aria-hidden="true">Aurea <em>Media</em></p>
    <div class="ft__bottom">
      <p>© <span data-year>2026</span> Aurea Media, agence web à Paris · SIREN {SITE["siren"]}</p>
      <ul>
        <li><a href="/mentions-legales/">Mentions légales</a></li>
        <li><a href="/politique-de-confidentialite/">Confidentialité</a></li>
        <li><a href="/plan-du-site-aurea-media-paris/">Plan du site</a></li>
      </ul>
    </div>
  </div>
</footer>'''


# ---------------------------------------------------------------------------
# Sections
# ---------------------------------------------------------------------------
def section_head(idx, label, title, lede="", cta="", center=False, split=True):
    sp = " data-split" if split else ""
    side = ""
    if lede or cta:
        side = f'<div class="sh__side">{f"<p class=lede>{lede}</p>" if lede else ""}{cta}</div>'
        side = side.replace("<p class=lede>", '<p class="lede">')
    cls = "sh sh--center" if center else "sh"
    return (f'<div class="{cls}"><div><p class="sh__idx label"><b>({idx})</b> {E(label)}</p>'
            f'<h2{sp}>{title}</h2></div>{side}</div>')


def brief_block():
    facts = [
        ("Fondation", f"Studio créé par Alexandre Coury, qui conçoit des sites depuis {SITE['since']}"),
        ("Basé à", f"{SITE['district']}, rendez-vous sur demande"),
        ("Intervient", "Paris, Île-de-France, France et international"),
        ("Services", "Sites vitrines, e-commerce, refonte, SEO, identité visuelle, présentations"),
        ("Tarifs", "Site vitrine dès 1 000 € HT · e-commerce dès 1 650 € HT"),
        ("Délai", "4 semaines en moyenne pour un site vitrine"),
        ("Technologies", "WordPress et WooCommerce, développés sur mesure"),
        ("Avis", f"{SITE['rating']}/5 sur {SITE['reviews']} avis Google"),
    ]
    rows = "".join(f"<div><dt>{E(a)}</dt><dd>{E(b)}</dd></div>" for a, b in facts)
    text = E(BRIEF).replace("Aurea Media", "<strong>Aurea Media</strong>", 1)
    return f'''<div class="brief">
      <div>
        <p class="sh__idx label"><b>(00)</b> Aurea Media en bref</p>
        <p class="brief__text" data-reveal>{text}</p>
      </div>
      <dl class="facts" data-stagger>{rows}</dl>
    </div>'''


def marquee(items=None):
    items = items or [p["name"] for p in PROJECTS[:14]]
    group = "".join(f'<li class="marquee__item">{E(n)}</li><li aria-hidden="true">{icon("sparkle", "marquee__sep")}</li>' for n in items)
    return (f'<div class="marquee" role="region" aria-label="Quelques clients">'
            f'<div class="marquee__track"><ul class="marquee__group" role="list">{group}</ul>'
            f'<ul class="marquee__group" role="list" aria-hidden="true">{group}</ul></div></div>')


def picture(pid, alt, sizes="(min-width: 1024px) 60vw, 100vw", lazy=True, cls=""):
    base = f"/assets/img/realisations/{pid}"
    load = 'loading="lazy" decoding="async"' if lazy else 'fetchpriority="high"'
    c = f' class="{cls}"' if cls else ""
    return (f'<picture><source type="image/avif" srcset="{base}-720.avif 720w, {base}-1440.avif 1440w" sizes="{sizes}">'
            f'<img{c} src="{base}-720.webp" srcset="{base}-720.webp 720w, {base}-1440.webp 1440w" sizes="{sizes}" '
            f'width="1440" height="720" alt="{E(alt)}" {load}></picture>')


def services_rows(ids=None):
    items = [s for s in SERVICES if not ids or s["id"] in ids]
    rows = "".join(f'''
      <li class="svc__row">
        <a class="svc__link" href="{s["path"]}">
          <span class="svc__num">{s["num"]}</span>
          <span class="svc__title">{s["title_html"]}</span>
          <span class="svc__desc">{E(s["desc"])}</span>
          <span class="svc__meta"><span class="svc__price">{E(s["price_label"])}</span><span class="svc__go">{icon("arrow")}</span></span>
          <span class="sr-only">: {E(s["name"])}</span>
        </a>
      </li>''' for s in items)
    # Aperçus chargés seulement au premier survol (inutiles au tactile)
    prev = "".join(f'<img data-src="/assets/img/realisations/{s["img"]}-720.webp" alt="" width="720" height="360">' for s in items)
    return f'<ul class="svc" role="list">{rows}</ul><div class="svc-preview" aria-hidden="true">{prev}</div>'


def service_cards(exclude=None, ids=None):
    items = [s for s in SERVICES if s["id"] != exclude and (not ids or s["id"] in ids)]
    cards = "".join(f'''
      <a class="card" href="{s["path"]}">
        <span class="card__ico">{icon(s["icon"])}</span>
        <h3>{E(s["name"])}</h3>
        <p>{E(s["desc"])}</p>
        <span class="link" style="margin-top:auto">{E(s["price_label"].capitalize())} {icon("arrow-right", "arr")}</span>
      </a>''' for s in items)
    return f'<div class="grid-3" data-stagger>{cards}</div>'


def project_card(p, lazy=True, cursor=True, heading="h3"):
    href = p.get("case") or p["url"]
    ext = "" if p.get("case") else ' rel="noopener" target="_blank"'
    label = "Lire l'étude de cas" if p.get("case") else f"Voir le site {p['name']} (nouvel onglet)"
    tags = "".join(f'<span class="tag">{E(t)}</span>' for t in p["tags"])
    cur = ' data-cursor="Voir"' if cursor else ""
    return f'''
      <a class="proj" href="{href}"{ext} data-cat="{p["cat"]}"{cur} aria-label="{E(p["name"])} : {E(label)}">
        <div class="shot"><div class="shot__bar"><i></i><i></i><i></i><span>{E(p["domain"])}</span></div>
          <div class="shot__img">{picture(p["id"], f"Page d'accueil du site {p['name']} créé par Aurea Media", lazy=lazy)}</div></div>
        <div class="proj__meta">
          <div><{heading} class="proj__title">{E(p["name"])}</{heading}><p class="proj__cat">{E(p["type"])} · {E(p["sector"])}</p></div>
          <div class="proj__tags">{tags}</div>
        </div>
      </a>'''


def projects_h(ids):
    items = [next(p for p in PROJECTS if p["id"] == i) for i in ids]
    cards = "".join(project_card(p) for p in items)
    return f'''<div class="hscroll"><div class="hscroll__track">{cards}
      <div class="hscroll__end">
        <p class="hscroll__count">20<span style="font-size:.4em">+</span></p>
        <p class="lede" style="color:#c9c3b4">réalisations en ligne : BTP, santé, industrie, immobilier, institutions.</p>
        <a class="btn btn--gold" href="/realisations-agence-web/" data-magnetic><span>Toutes les réalisations</span> {icon("arrow", "arr")}</a>
      </div></div></div>'''


def projects_grid():
    counts = {}
    for p in PROJECTS:
        for c in p["cat"].split():
            counts[c] = counts.get(c, 0) + 1
    chips = "".join(
        f'<button class="chip" type="button" data-filter="{k}" aria-pressed="{"true" if k == "tout" else "false"}">{E(v)} <small>{len(PROJECTS) if k == "tout" else counts.get(k, 0)}</small></button>'
        for k, v in PROJECT_CATS)
    cards = "".join(project_card(p, heading="h2") for p in PROJECTS)
    return (f'<div class="filters" role="group" aria-label="Filtrer les réalisations par secteur" data-filters="work">{chips}</div>'
            f'<p class="sr-only" aria-live="polite" data-filter-live></p>'
            f'<div class="work-grid" id="work">{cards}</div>')


def project_pair(ids):
    items = [next(p for p in PROJECTS if p["id"] == i) for i in ids]
    if len(items) == 1:
        return project_card(items[0])
    return '<div class="grid-2" style="gap:var(--s-5)">' + "".join(project_card(p) for p in items) + "</div>"


def stats():
    data = [
        (SITE["projects"], "+", "projets livrés depuis 2021, en France et à l'international"),
        ("4.9", "/5", f"note Google sur {SITE['reviews']} avis clients vérifiés"),
        ("4", "sem.", "délai moyen entre le brief et la mise en ligne d'un site vitrine"),
        ("24", "h", "délai de réponse garanti à chaque demande de devis"),
    ]
    items = "".join(f'<div class="stat"><p class="stat__n"><span data-count="{n}">{n.replace(".", ",")}</span><small>{u}</small></p><p class="stat__l">{E(l)}</p></div>' for n, u, l in data)
    return f'<div class="stats">{items}</div>'


STEP_SETS = {
    "site": [
        ("Semaine 1", "Audit et stratégie", "Analyse de votre marché, de vos concurrents et des mots-clés que tapent vos futurs clients. L'arborescence est validée ensemble.", "un échange d'une heure et vos éléments existants"),
        ("Semaine 2", "Design et maquettes", "Maquettes sur mesure, page par page. Vous validez chaque écran avant la première ligne de code ; les retouches sont comprises.", "vos retours sur les maquettes"),
        ("Semaine 3", "Développement", "Intégration WordPress, contenus, formulaires, tests sur mobile, tablette et ordinateur. Vous suivez l'avancement en direct.", "vos textes et photos, ou notre rédaction assistée"),
        ("Semaine 4", "SEO et mise en ligne", "Référencement technique, vitesse, données structurées, Search Console, puis mise en ligne et formation à la prise en main.", "une session de formation d'une heure"),
    ],
    "ecommerce": [
        ("Semaines 1 et 2", "Audit et architecture", "Catalogue, parcours d'achat, moyens de paiement et de livraison, mots-clés produits : tout est cadré avant le design.", "votre catalogue et vos conditions de vente"),
        ("Semaines 3 et 4", "Design et maquettes", "Pages produit, panier et tunnel de commande dessinés pour convertir, validés écran par écran.", "vos retours sur les maquettes"),
        ("Semaines 5 à 7", "Développement et catalogue", "WooCommerce sur mesure, import des produits, Stripe et PayPal, e-mails transactionnels, tests de commande.", "vos fiches produits et photos"),
        ("Semaine 8", "Tests, SEO et lancement", "Recette complète, SEO des fiches, Google Shopping, mise en ligne et formation à la gestion de la boutique.", "une session de formation"),
    ],
    "refonte": [
        ("Semaine 1", "Audit de l'existant", "Inventaire des pages, du trafic et des positions Google. On repère ce qui rapporte, pour le garder.", "l'accès à Search Console et à l'hébergement"),
        ("Semaines 2 et 3", "Nouveau design", "Maquettes sur mesure qui corrigent les freins à la conversion, sans casser ce qui fonctionne.", "vos retours sur les maquettes"),
        ("Semaines 3 à 5", "Développement et redirections", "Nouveau site, reprise des contenus et plan de redirections 301 page par page, testé avant la bascule.", "la validation des contenus repris"),
        ("8 semaines après", "Suivi des positions", "Surveillance des positions et des erreurs d'exploration après la bascule, corrections incluses.", "rien, on s'en occupe"),
    ],
    "seo": [
        ("Semaine 1", "Audit complet", "Technique, contenus, concurrence, fiche Google, visibilité dans les réponses des IA : un diagnostic chiffré et priorisé.", "l'accès à Search Console et à votre fiche Google"),
        ("Semaines 2 à 4", "Corrections prioritaires", "Balises, vitesse, données structurées, maillage interne, fiche Google Business Profile optimisée.", "la validation des modifications"),
        ("Chaque mois", "Contenus et notoriété", "Pages et articles qui répondent aux questions de vos clients, citations locales, avis.", "30 minutes par mois pour valider les sujets"),
        ("Chaque mois", "Mesure et rapport", "Positions, trafic, appels et demandes issus de Google : un rapport clair et des décisions.", "un point mensuel de 20 minutes"),
    ],
    "branding": [
        ("Jours 1 à 3", "Briefing et recherche", "Votre histoire, vos clients, vos concurrents et vos goûts : un questionnaire, un échange, puis une recherche visuelle.", "une heure d'échange et vos références"),
        ("Jours 4 à 7", "Trois pistes créatives", "Trois directions différentes, présentées et argumentées, déclinées sur des supports réels.", "le choix de la piste"),
        ("Semaine 2", "Finalisation et déclinaisons", "Retouches illimitées sur la piste retenue, puis couleurs, typographies, carte de visite, signature et réseaux sociaux.", "vos retours"),
        ("Livraison", "Guide de marque", "Fichiers sources, guide d'usage en PDF et cession de droits écrite : votre marque est prête à vivre.", "rien, tout vous est remis"),
    ],
    "maquettes": [
        ("Jour 1", "Briefing et structure", "Objectif, public, message clé : on construit ensemble le déroulé, slide par slide, avant tout design.", "votre contenu brut et vos chiffres"),
        ("Jours 2 à 4", "Mise en page et design", "Chaque slide dit une seule chose. Graphiques, schémas et visuels sont créés sur mesure, dans votre charte.", "vos retours sur la première version"),
        ("Jour 5", "Retouches", "Les ajustements sont intégrés, les chiffres vérifiés, la lecture fluidifiée.", "une relecture finale"),
        ("Jour 6 ou 7", "Livraison", "Fichiers PowerPoint, Keynote, Google Slides ou Canva 100 % éditables, avec vos polices et vos couleurs.", "rien, les fichiers sont prêts"),
    ],
}


def steps(set_name="site"):
    items = "".join(f'''
      <li class="step">
        <span class="step__dot">0{i + 1}</span>
        <p class="step__when">{E(w)}</p>
        <h3>{E(t)}</h3>
        <p>{E(d)}</p>
        <p class="step__you"><b>De votre côté :</b> {E(y)}</p>
      </li>''' for i, (w, t, d, y) in enumerate(STEP_SETS[set_name]))
    return f'<ol class="steps" role="list"><div class="steps__line" aria-hidden="true"><span></span></div>{items}</ol>'


def plans(set_name):
    out = []
    for p in PLANS[set_name]:
        hl = p.get("hl")
        badge = '<span class="plan__badge">Le plus choisi</span>' if hl else ""
        if p["price"]:
            price = f'<small>à partir de</small><b>{euro(p["price"])}</b><span>€ HT</span>'
        else:
            price = '<small>tarification</small><b>Sur devis</b>'
        items = "".join(f"<li>{icon('check')}<span>{E(i)}</span></li>" for i in p["items"])
        cls = "plan plan--hl" if hl else "plan"
        btn = "btn--gold" if hl else "btn--ghost"
        out.append(f'''
      <article class="{cls}" data-tilt>
        {badge}
        <h3 class="plan__name">{E(p["name"])}</h3>
        <p class="plan__for">{E(p["for"])}</p>
        <p class="plan__price">{price}</p>
        <ul>{items}</ul>
        <a class="btn {btn} btn--block" href="/contact-agence/?formule={E(p["name"])}"><span>Demander un devis</span> {icon("arrow", "arr")}</a>
      </article>''')
    return '<div class="plans" data-stagger>' + "".join(out) + "</div>"


def assure(items=None):
    items = items or ["Devis détaillé et engageant, sans supplément caché", "Délai annoncé et calendrier fourni au départ", "Vous restez propriétaire du site, du domaine et des fichiers"]
    return '<ul class="assure" role="list">' + "".join(f"<li>{icon('shield')}<span>{E(i)}</span></li>" for i in items) + "</ul>"


def reviews_block(title=True):
    cards = "".join(f'''
      <figure class="rv">
        {stars()}
        <blockquote><p>« {E(r["text"])} »</p></blockquote>
        <figcaption><footer><span class="rv__av" aria-hidden="true">{E(r["name"][0])}</span><span><b>{E(r["name"])}</b>{E(r["meta"])} · avis Google</span></footer></figcaption>
      </figure>''' for r in REVIEWS)
    cards += f'''
      <a class="rv" href="{SITE["gbp"]}" rel="noopener" style="background:var(--ink);color:var(--paper);justify-content:space-between">
        <span class="label" style="color:var(--gold)">Fiche Google</span>
        <span style="font-family:var(--f-serif);font-size:2.2rem;line-height:1.05">Lire les {SITE["reviews"]} avis sur Google</span>
        <span class="link" style="color:var(--paper)">Ouvrir la fiche {icon("arrow", "arr")}</span>
      </a>'''
    return f'''<div data-reviews>
      <div class="reviews__head">
        <div class="gscore"><span class="gscore__n">{SITE["rating"]}</span><span class="gscore__meta">{stars()}<span>{SITE["reviews"]} avis Google vérifiés</span></span></div>
        <div class="rv-nav"><button type="button" data-rv="prev" aria-label="Avis précédent">{icon("left")}</button><button type="button" data-rv="next" aria-label="Avis suivant">{icon("right")}</button></div>
      </div>
      <div class="rv-track" tabindex="0" role="region" aria-label="Avis clients, faire défiler horizontalement">{cards}</div>
    </div>'''


CAT_LABEL = dict(POST_CATS)
CAT_COVER = {
    "vitrine": "radial-gradient(90% 90% at 85% 15%, rgba(201,169,97,.55), transparent 60%)",
    "ecommerce": "radial-gradient(90% 90% at 15% 20%, rgba(228,201,138,.45), transparent 60%)",
    "seo": "radial-gradient(90% 90% at 80% 85%, rgba(201,169,97,.5), transparent 60%)",
    "identite": "radial-gradient(100% 80% at 50% 0%, rgba(154,122,58,.6), transparent 65%)",
    "metiers": "radial-gradient(90% 90% at 10% 90%, rgba(201,169,97,.45), transparent 60%)",
}
MONTHS = ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"]


def fr_date(iso):
    y, m, d = iso.split("-")
    return f"{int(d)} {MONTHS[int(m) - 1]} {y}"


def post_card(p, i=0):
    cover = CAT_COVER.get(p["cat"], CAT_COVER["vitrine"])
    return f'''
      <article class="post" data-cat="{p["cat"]}">
        <a class="post__cover" href="/{p["slug"]}/" style="--cover:{cover}" tabindex="-1" aria-hidden="true">
          {golden_spiral("post__art golden-svg", n=7, draw=False, grid=False)}
          <span class="post__big">{i + 1:02d}</span>
          <span class="post__cat">{E(CAT_LABEL.get(p["cat"], ""))}</span>
        </a>
        <p class="post__meta"><time datetime="{p["date"]}">{fr_date(p["date"])}</time><span>{p["read"]} min de lecture</span></p>
        <h3><a href="/{p["slug"]}/">{E(p["title"])}</a></h3>
        <p>{E(p["excerpt"])}</p>
      </article>'''


def posts(slugs=None, cat=None, limit=3, heading="h3"):
    items = POSTS
    if slugs:
        items = [next(p for p in POSTS if p["slug"] == s) for s in slugs]
    elif cat:
        items = [p for p in POSTS if p["cat"] == cat]
    items = items[:limit]
    html_ = "".join(post_card(p, i) for i, p in enumerate(items))
    if heading != "h3":
        html_ = html_.replace("<h3>", f"<{heading}>").replace("</h3>", f"</{heading}>")
    return f'<div class="posts" data-stagger>{html_}</div>'


def posts_all():
    counts = {}
    for p in POSTS:
        counts[p["cat"]] = counts.get(p["cat"], 0) + 1
    chips = "".join(
        f'<button class="chip" type="button" data-filter="{k}" aria-pressed="{"true" if k == "tout" else "false"}">{E(v)} <small>{len(POSTS) if k == "tout" else counts.get(k, 0)}</small></button>'
        for k, v in POST_CATS)
    items = sorted(POSTS, key=lambda p: p["date"], reverse=True)
    cards = "".join(post_card(p, i) for i, p in enumerate(items)).replace("<h3>", "<h2 class=\"h3\">").replace("</h3>", "</h2>")
    return (f'<div class="filters" role="group" aria-label="Filtrer les articles par thème" data-filters="articles">{chips}</div>'
            f'<p class="sr-only" aria-live="polite" data-filter-live></p>'
            f'<div class="posts" id="articles">{cards}</div>')


def faq(items, title="Questions fréquentes", idx="09", lede="", cta=True):
    det = "".join(f'''
        <details{" open" if i == 0 else ""}>
          <summary><span>{E(q)}</span><span class="acc__ico" aria-hidden="true"></span></summary>
          <div class="acc__body"><p>{a}</p></div>
        </details>''' for i, (q, a) in enumerate(items))
    c = f'<a class="btn btn--ghost" href="/contact-agence/"><span>Poser ma question</span> {icon("arrow", "arr")}</a>' if cta else ""
    return f'''<div class="faq">
      <div class="faq__side">
        <p class="sh__idx label"><b>({idx})</b> FAQ</p>
        <h2 data-split>{title}</h2>
        {f'<p class="lede">{lede}</p>' if lede else ""}
        {c}
      </div>
      <div class="acc">{det}</div>
    </div>'''


def cta(title="Parlons de <em>votre</em> projet.", text="Décrivez votre projet en quelques lignes. Vous recevez une première analyse et un devis détaillé sous 24 h ouvrées, sans engagement.", gl=True):
    canvas = '<canvas class="hero__gl" data-gl data-count="2200" data-cx="0" data-cy="0" data-cxm="0" data-cym="0" data-scale="0.78" data-point="6.5" aria-hidden="true"></canvas>' if gl else ""
    return f'''
<section class="section cta s-dark" aria-labelledby="cta-title">
  {canvas}
  <div class="cta__glow" aria-hidden="true"></div>
  <div class="container">
    <p class="eyebrow" style="justify-content:center">Votre projet</p>
    <h2 class="cta__title" id="cta-title" data-split style="margin-top:var(--s-4)">{title}</h2>
    <p class="lede">{text}</p>
    <div class="btn-row">
      <a class="btn btn--gold btn--lg" href="/contact-agence/" data-magnetic><span>Recevoir mon devis sous 24&nbsp;h</span> {icon("arrow", "arr")}</a>
      <a class="btn btn--ghost btn--lg" href="/contact-agence/#rendez-vous"><span>Réserver un appel de 20 min</span> {icon("calendar", "arr")}</a>
    </div>
    <ul class="cta__assure" role="list">
      <li>{icon("check")}Devis gratuit et détaillé</li>
      <li>{icon("check")}Réponse sous 24 h ouvrées</li>
      <li>{icon("check")}Sans engagement</li>
      <li>{icon("phone")}<a href="tel:{SITE["phone_href"]}">{SITE["phone"]}</a></li>
    </ul>
  </div>
</section>'''


def founder_block(heading="h2", quote=True, idx="06"):
    q = '<p class="founder__quote" data-split>Mon obsession&nbsp;: des projets qui fonctionnent, pas juste de jolis pixels.</p>' if quote else ""
    return f'''<div class="founder">
      <figure class="founder__pic" data-reveal="clip">
        <picture><source type="image/avif" srcset="/assets/img/alexandre-coury-480.avif 480w, /assets/img/alexandre-coury-800.avif 800w" sizes="(min-width: 960px) 36vw, 100vw">
        <img src="/assets/img/alexandre-coury-480.webp" srcset="/assets/img/alexandre-coury-480.webp 480w, /assets/img/alexandre-coury-800.webp 800w" sizes="(min-width: 960px) 36vw, 100vw" width="800" height="1000" alt="Portrait d'Alexandre Coury, fondateur d'Aurea Media" loading="lazy" decoding="async"></picture>
        <figcaption><span><b>Alexandre Coury</b>Fondateur, développeur web et SEO</span><a class="socials" href="{SITE["linkedin_person"]}" rel="noopener" aria-label="Alexandre Coury sur LinkedIn" style="width:42px;height:42px;border-radius:50%;border:1px solid rgba(233,220,190,.25);display:grid;place-items:center">{icon("linkedin")}</a></figcaption>
      </figure>
      <div>
        <p class="sh__idx label"><b>({idx})</b> Votre interlocuteur</p>
        {q}
        <div class="founder__text">
          <p>Développeur web et spécialiste du référencement, <strong>Alexandre Coury</strong> accompagne depuis {SITE["since"]} des indépendants, des artisans, des TPE, des PME et des associations. Chaque projet est pris en charge directement : stratégie, design, développement et SEO, sans commercial ni sous-traitance.</p>
          <p>Vous parlez toujours à la personne qui construit votre site, avant comme après la mise en ligne. Les accès administrateur, le nom de domaine et les fichiers vous appartiennent dès la livraison.</p>
        </div>
        <div class="founder__creds"><span class="tag">WordPress et WooCommerce</span><span class="tag">SEO technique et local</span><span class="tag">GEO et recherche IA</span><span class="tag">Direction artistique</span></div>
        <div class="btn-row mt-2"><a class="link" href="/a-propos/">Découvrir l'agence {icon("arrow-right", "arr")}</a></div>
      </div>
    </div>'''


def estimator(idx="05"):
    return f'''<div class="est" id="simulateur">
      <form class="est__form" data-estimator onsubmit="return false" aria-describedby="est-note">
        <fieldset class="est__q">
          <legend><span>01</span> Quel type de site&nbsp;?</legend>
          <div class="opts">
            <div class="opt"><input type="radio" id="t-vitrine" name="type" value="vitrine" checked><label for="t-vitrine"><b>Site vitrine</b><small>Présenter votre activité et recevoir des demandes</small></label></div>
            <div class="opt"><input type="radio" id="t-ecommerce" name="type" value="ecommerce"><label for="t-ecommerce"><b>Site e-commerce</b><small>Vendre vos produits en ligne</small></label></div>
          </div>
        </fieldset>
        <fieldset class="est__q">
          <legend><span>02</span> Quelle taille&nbsp;?</legend>
          <div class="range">
            <div class="range__top"><label for="est-range" class="small muted" data-est="unit-label">Nombre de pages</label><output class="range__val" for="est-range" data-est="pages">5 pages</output></div>
            <input id="est-range" type="range" min="0" max="5" step="1" value="2" aria-describedby="est-scale">
            <div class="range__scale" id="est-scale"><span data-est="scale-min">1</span><span data-est="scale-max">15+</span></div>
          </div>
        </fieldset>
        <fieldset class="est__q">
          <legend><span>03</span> Des besoins particuliers&nbsp;?</legend>
          <div class="opts">
            <div class="opt"><input type="checkbox" id="o-bilingue" name="bilingue"><label for="o-bilingue"><b>Version bilingue</b><small>Français et anglais, SEO international</small></label></div>
            <div class="opt"><input type="checkbox" id="o-sur" name="surmesure"><label for="o-sur"><b>Fonctions sur mesure</b><small>Réservation, espace client, connexion à un outil</small></label></div>
            <div class="opt"><input type="checkbox" id="o-brand" name="branding"><label for="o-brand"><b>Identité visuelle</b><small>Logo et charte graphique avec le site</small></label></div>
          </div>
        </fieldset>
      </form>
      <aside class="est__out" aria-live="polite" aria-label="Estimation">
        <p class="est__formula" data-est="formula">Site vitrine Professionnel</p>
        <p class="est__price" data-est="price">1 800<small>€ HT</small></p>
        <ul class="est__list" data-est="list"></ul>
        <p class="est__delay"><span>Délai de réalisation</span><b data-est="delay">4 semaines</b></p>
        <a class="btn btn--gold btn--block" data-est="cta" data-base="/contact-agence/" href="/contact-agence/"><span>Recevoir le devis détaillé</span> {icon("arrow", "arr")}</a>
        <p class="est__note" id="est-note">Estimation indicative fondée sur nos formules publiques. Le devis final, gratuit et engageant, est établi après un échange.</p>
      </aside>
    </div>'''


def contact_form(preset=None, compact=False, idp="f"):
    besoins = ["Site vitrine", "Site e-commerce", "Refonte", "SEO et visibilité", "Identité visuelle", "Présentation", "Autre"]
    pills = "".join(
        f'<div class="pill"><input type="radio" id="{idp}-b{i}" name="besoin" value="{E(b)}"{" checked" if b == preset else ""}><label for="{idp}-b{i}">{E(b)}</label></div>'
        for i, b in enumerate(besoins))
    budgets = ["Moins de 1 000 €", "1 000 à 1 800 €", "1 800 à 3 000 €", "3 000 à 5 000 €", "Plus de 5 000 €", "Je ne sais pas encore"]
    opts = '<option value="">Sélectionner</option>' + "".join(f"<option>{E(b)}</option>" for b in budgets)
    url_field = ""
    if preset in ("Refonte", "SEO et visibilité"):
        url_field = f'''<div class="field"><label for="{idp}-url">Adresse de votre site actuel</label><input id="{idp}-url" name="url" type="url" inputmode="url" placeholder="https://" autocomplete="url"></div>'''
    return f'''<form class="form" data-form novalidate action="#" method="post">
      <div class="form__fields" style="display:grid;gap:1.2rem">
        <fieldset class="pills"><legend>Votre besoin</legend>{pills}</fieldset>
        <div class="form__row">
          <div class="field"><label for="{idp}-nom">Nom et prénom</label><input id="{idp}-nom" name="nom" type="text" autocomplete="name" required><p class="field__err">Indiquez votre nom pour que je puisse vous répondre.</p></div>
          <div class="field"><label for="{idp}-mail">Adresse e-mail</label><input id="{idp}-mail" name="email" type="email" autocomplete="email" required><p class="field__err">Cette adresse e-mail semble incomplète.</p></div>
        </div>
        <div class="form__row">
          <div class="field"><label for="{idp}-tel">Téléphone <span class="opt-l">(facultatif)</span></label><input id="{idp}-tel" name="tel" type="tel" autocomplete="tel"></div>
          <div class="field"><label for="{idp}-budget">Budget envisagé <span class="opt-l">(facultatif)</span></label><select id="{idp}-budget" name="budget">{opts}</select></div>
        </div>
        {url_field}
        <div class="field"><label for="{idp}-msg">Votre projet en quelques lignes</label><textarea id="{idp}-msg" name="message" required aria-describedby="{idp}-help"></textarea><p class="field__help" id="{idp}-help">Votre activité, vos objectifs, une échéance éventuelle.</p><p class="field__err">Décrivez votre projet en une ou deux phrases.</p></div>
        <button class="btn btn--gold btn--lg" type="submit" data-magnetic><span>Recevoir mon devis gratuit</span> {icon("arrow", "arr")}</button>
        <p class="form__legal">Vos données servent uniquement à répondre à votre demande. Aucun démarchage, aucune revente. <a class="inline-link" href="/politique-de-confidentialite/">Politique de confidentialité</a>.</p>
      </div>
      <div class="form__ok" role="status">
        <p class="label gold">Demande envoyée</p>
        <p class="h3">Merci, votre demande est bien arrivée.</p>
        <p class="muted">Alexandre vous répond personnellement sous 24 h ouvrées, avec une première analyse et un devis détaillé.</p>
      </div>
    </form>'''


def slot_picker():
    import datetime
    jours = ["lun.", "mar.", "mer.", "jeu.", "ven."]
    mois = ["janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."]
    days, day = [], datetime.date.today()
    while len(days) < 5:
        day += datetime.timedelta(days=1)
        if day.weekday() < 5:
            days.append((jours[day.weekday()], f"{day.day} {mois[day.month - 1]}"))
    slots = ["9 h 30", "11 h 00", "14 h 00", "16 h 30", "18 h 00"]
    d = "".join(f'<div class="pill"><input type="radio" id="d{i}" name="jour" value="{a} {b}"{" checked" if i == 0 else ""}><label for="d{i}">{a} {b}</label></div>' for i, (a, b) in enumerate(days))
    s = "".join(f'<div class="pill"><input type="radio" id="s{i}" name="heure" value="{h}"><label for="s{i}">{h}</label></div>' for i, h in enumerate(slots))
    return f'''<form class="form" data-form novalidate action="#" method="post" aria-label="Réserver un appel de 20 minutes">
      <div class="form__fields" style="display:grid;gap:1.2rem">
        <fieldset class="pills"><legend>Jour</legend>{d}</fieldset>
        <fieldset class="pills"><legend>Heure (heure de Paris)</legend>{s}</fieldset>
        <div class="form__row">
          <div class="field"><label for="r-nom">Nom et prénom</label><input id="r-nom" name="nom" type="text" autocomplete="name" required><p class="field__err">Indiquez votre nom.</p></div>
          <div class="field"><label for="r-tel">Téléphone</label><input id="r-tel" name="tel" type="tel" autocomplete="tel" required><p class="field__err">Indiquez un numéro pour l'appel.</p></div>
        </div>
        <button class="btn btn--ink btn--lg" type="submit"><span>Réserver ce créneau</span> {icon("calendar", "arr")}</button>
        <p class="form__legal">Maquette : dans le thème, ce bloc sera relié à l'agenda (Cal.com ou Google Agenda).</p>
      </div>
      <div class="form__ok" role="status"><p class="label gold">Créneau réservé</p><p class="h3">C'est noté.</p><p class="muted">Vous recevez une confirmation par e-mail et un rappel la veille.</p></div>
    </form>'''


def crumbs(items):
    lis = []
    for i, (name, href) in enumerate(items):
        if i == len(items) - 1:
            lis.append(f'<li><span aria-current="page">{E(name)}</span></li>')
        else:
            lis.append(f'<li><a href="{href}">{E(name)}</a></li>')
    return f'<nav class="crumbs" aria-label="Fil d\'Ariane"><ol>{"".join(lis)}</ol></nav>'


def checks(items, cls="checks"):
    return f'<ul class="{cls}" role="list">' + "".join(f"<li>{icon('check')}<span>{i}</span></li>" for i in items) + "</ul>"


def todo(text):
    """Emplacement de donnée réelle à compléter (jamais de chiffre inventé)."""
    return f'<span class="todo" title="Donnée réelle à compléter avant publication">{E(text)}</span>'


def contact_section(preset=None, title="Démarrons <em>votre</em> projet.", text=None, idx="10", idp="c"):
    text = text or "Décrivez votre projet en quelques lignes : vous recevez une première analyse et un devis détaillé sous 24 h ouvrées, sans engagement."
    return f'''
<section class="section s-paper-2" id="devis" aria-labelledby="devis-title">
  <div class="container">
    <div class="contact">
      <div class="contact__side">
        <p class="sh__idx label"><b>({idx})</b> Devis gratuit</p>
        <h2 id="devis-title" data-split>{title}</h2>
        <p class="lede">{text}</p>
        <div class="direct">
          <a href="tel:{SITE["phone_href"]}"><span class="ico">{icon("phone")}</span><small>Téléphone</small><b>{SITE["phone_intl"]}</b></a>
          <a href="mailto:{SITE["email"]}"><span class="ico">{icon("mail")}</span><small>E-mail direct</small><b>{SITE["email"]}</b></a>
          <a href="/contact-agence/#rendez-vous"><span class="ico">{icon("calendar")}</span><small>Rendez-vous</small><b>Réserver un appel de 20 min</b></a>
        </div>
        {checks(["Réponse personnelle d'Alexandre, jamais un message automatique", "Devis détaillé ligne par ligne, sans coût caché", "Aucun démarchage, aucune revente de vos données"])}
      </div>
      <div class="contact__card">{contact_form(preset, idp=idp)}</div>
    </div>
  </div>
</section>'''


def before_after(before="avant-aurea", after="apres-aurea", alt_before="", alt_after="", caption="", uid="ba"):
    def pic(name, alt):
        return (f'<picture><source type="image/avif" srcset="/assets/img/{name}.avif">'
                f'<img src="/assets/img/{name}.webp" width="1440" height="720" alt="{E(alt)}" loading="lazy" decoding="async"></picture>')
    return f'''<figure class="ba" data-ba style="--pos:50%">
      <div class="ba__stage">
        <div class="ba__img">{pic(after, alt_after)}</div>
        <div class="ba__img ba__before">{pic(before, alt_before)}</div>
        <div class="ba__handle" aria-hidden="true"><span>{icon("left")}{icon("right")}</span></div>
        <span class="ba__tag ba__tag--l" aria-hidden="true">Avant</span><span class="ba__tag ba__tag--r" aria-hidden="true">Après</span>
        <label class="sr-only" for="{uid}-range">Glisser pour comparer l'ancien et le nouveau site</label>
        <input id="{uid}-range" class="ba__range" type="range" min="0" max="100" value="50">
      </div>
      <figcaption class="small muted">{caption}</figcaption>
    </figure>'''

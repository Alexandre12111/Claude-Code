"""Gabarit commun : balises de tête (SEO), en-tête, pied de page, icônes et données structurées."""
import json
from html import escape
from donnees import SITE, PAGES, LUMA, LINKEDIN, INTERVENANTS

MAJ = "2026-10-28"

# Icônes SVG en ligne (tracés Phosphor, licence MIT, poids « regular »)
_P = {
    "fleche": "M221.66 133.66l-72 72a8 8 0 0 1-11.32-11.32L196.69 136H40a8 8 0 0 1 0-16h156.69l-58.35-58.34a8 8 0 0 1 11.32-11.32l72 72a8 8 0 0 1 0 11.32Z",
    "sortie": "M200 64v104a8 8 0 0 1-16 0V83.31L69.66 197.66a8 8 0 0 1-11.32-11.32L172.69 72H88a8 8 0 0 1 0-16h104a8 8 0 0 1 8 8Z",
    "play": "M240 128a15.74 15.74 0 0 1-7.6 13.51L88.32 229.65a16 16 0 0 1-16.2.3A15.86 15.86 0 0 1 64 216.13V39.87a15.86 15.86 0 0 1 8.12-13.82 16 16 0 0 1 16.2.3l144.08 88.14A15.74 15.74 0 0 1 240 128Z",
    "pause": "M216 48v160a16 16 0 0 1-16 16h-40a16 16 0 0 1-16-16V48a16 16 0 0 1 16-16h40a16 16 0 0 1 16 16ZM96 32H56a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h40a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16Z",
    "son": "M155.51 24.81a8 8 0 0 0-8.42.88L77.25 80H32a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h45.25l69.84 54.31A8 8 0 0 0 160 224V32a8 8 0 0 0-4.49-7.19ZM144 207.64l-61.09-47.5A8 8 0 0 0 78 158.4H32V96h46a8 8 0 0 0 4.91-1.68L144 48.36ZM198 101.56a40 40 0 0 1 0 52.88 8 8 0 0 1-12-10.58 24 24 0 0 0 0-31.72 8 8 0 0 1 12-10.58ZM248 128a79.9 79.9 0 0 1-20.37 53.34 8 8 0 0 1-11.92-10.67 64 64 0 0 0 0-85.33 8 8 0 1 1 11.92-10.67A79.83 79.83 0 0 1 248 128Z",
    "muet": "M155.51 24.81a8 8 0 0 0-8.42.88L77.25 80H32a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h45.25l69.84 54.31A8 8 0 0 0 160 224V32a8 8 0 0 0-4.49-7.19ZM144 207.64l-61.09-47.5A8 8 0 0 0 78 158.4H32V96h46a8 8 0 0 0 4.91-1.68L144 48.36Zm101.66-61.3a8 8 0 0 1-11.32 11.32L216 139.31l-18.34 18.35a8 8 0 0 1-11.32-11.32L204.69 128l-18.35-18.34a8 8 0 0 1 11.32-11.32L216 116.69l18.34-18.35a8 8 0 0 1 11.32 11.32L227.31 128Z",
    "x": "M205.66 194.34a8 8 0 0 1-11.32 11.32L128 139.31l-66.34 66.35a8 8 0 0 1-11.32-11.32L116.69 128 50.34 61.66a8 8 0 0 1 11.32-11.32L128 116.69l66.34-66.35a8 8 0 0 1 11.32 11.32L139.31 128Z",
    "loupe": "M229.66 218.34l-50.07-50.06a88.11 88.11 0 1 0-11.31 11.31l50.06 50.07a8 8 0 0 0 11.32-11.32ZM40 112a72 72 0 1 1 72 72 72.08 72.08 0 0 1-72-72Z",
    "pin": "M128 64a40 40 0 1 0 40 40 40 40 0 0 0-40-40Zm0 64a24 24 0 1 1 24-24 24 24 0 0 1-24 24Zm0-112a88.1 88.1 0 0 0-88 88c0 31.4 14.51 64.68 42 96.25a254.19 254.19 0 0 0 41.45 38.3 8 8 0 0 0 9.18 0 254.19 254.19 0 0 0 41.37-38.3c27.45-31.57 42-64.85 42-96.25a88.1 88.1 0 0 0-88-88Zm0 206c-16.53-13-72-60.75-72-118a72 72 0 0 1 144 0c0 57.23-55.47 105-72 118Z",
    "linkedin": "M216 24H40a16 16 0 0 0-16 16v176a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V40a16 16 0 0 0-16-16Zm0 192H40V40h176v176ZM96 112v64a8 8 0 0 1-16 0v-64a8 8 0 0 1 16 0Zm88 28v36a8 8 0 0 1-16 0v-36a20 20 0 0 0-40 0v36a8 8 0 0 1-16 0v-64a8 8 0 0 1 15.79-1.78A36 36 0 0 1 184 140Zm-84-56a12 12 0 1 1-12-12 12 12 0 0 1 12 12Z",
    "mail": "M224 48H32a8 8 0 0 0-8 8v136a16 16 0 0 0 16 16h176a16 16 0 0 0 16-16V56a8 8 0 0 0-8-8Zm-96 85.15L52.57 64h150.86ZM98.71 128 40 181.81V74.19Zm11.84 10.85 12 11.05a8 8 0 0 0 10.82 0l12-11.05 58 53.15H52.57ZM157.29 128 216 74.18v107.64Z",
    "usine": "M232 208h-8V88a8 8 0 0 0-12.8-6.4L168 114V88a8 8 0 0 0-12.8-6.4L112 114V40a8 8 0 0 0-8-8H48a8 8 0 0 0-8 8v168h-8a8 8 0 0 0 0 16h200a8 8 0 0 0 0-16ZM56 48h40v82l-40 30Zm0 132 104-78v30a8 8 0 0 0 12.8 6.4l35.2-26.4v96H56Zm120-12a8 8 0 0 1-8 8h-24a8 8 0 0 1 0-16h24a8 8 0 0 1 8 8Z",
    "graphique": "M232 208a8 8 0 0 1-8 8H32a8 8 0 0 1-8-8V48a8 8 0 0 1 16 0v108.69l50.34-50.35a8 8 0 0 1 11.32 0L128 132.69 180.69 80H160a8 8 0 0 1 0-16h40a8 8 0 0 1 8 8v40a8 8 0 0 1-16 0V91.31l-58.34 58.35a8 8 0 0 1-11.32 0L96 123.31l-56 56V200h184a8 8 0 0 1 8 8Z",
    "institution": "M24 104h24v64H32a8 8 0 0 0 0 16h192a8 8 0 0 0 0-16h-16v-64h24a8 8 0 0 0 4.19-14.81l-104-64a8 8 0 0 0-8.38 0l-104 64A8 8 0 0 0 24 104Zm40 0h32v64H64Zm80 0v64h-32v-64Zm48 64h-32v-64h32ZM128 41.39 203.74 88H52.26ZM248 208a8 8 0 0 1-8 8H16a8 8 0 0 1 0-16h224a8 8 0 0 1 8 8Z",
    "calendrier": "M208 32h-24v-8a8 8 0 0 0-16 0v8H88v-8a8 8 0 0 0-16 0v8H48a16 16 0 0 0-16 16v160a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16ZM72 48v8a8 8 0 0 0 16 0v-8h80v8a8 8 0 0 0 16 0v-8h24v32H48V48Zm136 160H48V96h160v112Z",
    "badge": "M200 112a8 8 0 0 1-8 8h-40a8 8 0 0 1 0-16h40a8 8 0 0 1 8 8Zm-8 24h-40a8 8 0 0 0 0 16h40a8 8 0 0 0 0-16Zm40-80v144a16 16 0 0 1-16 16H40a16 16 0 0 1-16-16V56a16 16 0 0 1 16-16h176a16 16 0 0 1 16 16Zm-16 144V56H40v144Zm-80.26-34a8 8 0 1 1-15.5 4c-2.63-10.26-13.06-18-25.25-18s-22.61 7.73-25.25 18a8 8 0 1 1-15.5-4 31.89 31.89 0 0 1 17.73-21.07 28 28 0 1 1 46 0A31.89 31.89 0 0 1 135.74 166ZM96 144a12 12 0 1 0-12-12 12 12 0 0 0 12 12Z",
    "train": "M184 24H72a32 32 0 0 0-32 32v120a32 32 0 0 0 32 32h4l-12 16a8 8 0 0 0 12.8 9.6L94 208h68l17.2 23.6a8 8 0 1 0 12.8-9.6l-12-16h4a32 32 0 0 0 32-32V56a32 32 0 0 0-32-32ZM56 120V80h64v40Zm80-40h64v40h-64ZM72 40h112a16 16 0 0 1 16 16v8H56v-8a16 16 0 0 1 16-16Zm112 152H72a16 16 0 0 1-16-16v-40h144v40a16 16 0 0 1-16 16Zm-84-28a12 12 0 1 1-12-12 12 12 0 0 1 12 12Zm80 0a12 12 0 1 1-12-12 12 12 0 0 1 12 12Z",
    "accessible": "M128 72a28 28 0 1 0-28-28 28 28 0 0 0 28 28Zm0-40a12 12 0 1 1-12 12 12 12 0 0 1 12-12Zm80 64a8 8 0 0 1-8 8h-56v24.93l38.44 72.07a8 8 0 0 1-14.12 7.52L131.39 160h-6.78l-36.93 68.52a8 8 0 1 1-14.12-7.52L112 128.93V104H56a8 8 0 0 1 0-16h144a8 8 0 0 1 8 8Z",
    "trophee": "M232 64h-24V48a8 8 0 0 0-8-8H56a8 8 0 0 0-8 8v16H24A16 16 0 0 0 8 80v16a40 40 0 0 0 40 40h3.65A80.13 80.13 0 0 0 120 191.61V216H96a8 8 0 0 0 0 16h64a8 8 0 0 0 0-16h-24v-24.42c31.94-3.23 58.44-25.64 68.08-55.58H208a40 40 0 0 0 40-40V80a16 16 0 0 0-16-16ZM48 120a24 24 0 0 1-24-24V80h24v32q0 4 .39 8Zm144-8.9c0 35.52-28.49 64.64-63.51 64.9H128a64 64 0 0 1-64-64V56h128ZM232 96a24 24 0 0 1-24 24h-.5a81.81 81.81 0 0 0 .5-8.9V80h24Z",
}


def ico(nom, cls=""):
    c = f' class="{cls}"' if cls else ""
    return f'<svg{c} viewBox="0 0 256 256" fill="currentColor" aria-hidden="true" focusable="false"><path d="{_P[nom]}"/></svg>'


def lien(cle, ancre=""):
    return PAGES[cle][0] + ancre


def url(cle):
    return SITE + PAGES[cle][1]


def lignes(*ls, tag="h2", cls="h2", attrs=""):
    """Titre révélé ligne par ligne (masque) : chaque ligne glisse vers le haut."""
    inner = "".join(f'<span class="l"><span style="--i:{i}">{l}</span></span>' for i, l in enumerate(ls))
    return f'<{tag} class="{cls} lignes"{attrs}>{inner}</{tag}>'


NAV = [("programme", "Programme"), ("intervenants", "Intervenants"), ("partenaires", "Partenaires"), ("workshops", "Workshops"), ("infos", "Infos pratiques"), ("contact", "Contact")]
TIROIR = [("accueil", "Accueil"), ("programme", "Programme 2026"), ("intervenants", "Intervenants"), ("partenaires", "Devenir partenaire"), ("workshops", "Ateliers & Workshops"),
          ("prix", "Prix de l'innovation duale"), ("infos", "Infos pratiques"), ("retrospective", "Rétrospective 2026"), ("contact", "Contact")]

# ---------- Données structurées partagées ----------
INAS = {"@type": "Organization", "@id": f"{SITE}/#inas", "name": "Institut National des Affaires Stratégiques", "alternateName": "INAS", "url": "https://inas-france.fr/"}
POLD = {"@type": "GovernmentOrganization", "@id": f"{SITE}/#pold", "name": "Paris Ouest La Défense", "description": "Établissement public territorial", "url": "https://www.parisouestladefense.fr/"}
SITEWEB = {"@type": "WebSite", "@id": f"{SITE}/#site", "url": f"{SITE}/", "name": "DualTech", "inLanguage": "fr-FR", "publisher": {"@id": f"{SITE}/#inas"}}
LIEU = {"@type": "Place", "@id": f"{SITE}/#campus-cyber", "name": "Campus Cyber", "url": "https://campuscyber.fr/",
        "address": {"@type": "PostalAddress", "streetAddress": "5-7 rue Bellini", "postalCode": "92800", "addressLocality": "Puteaux", "addressRegion": "Île-de-France", "addressCountry": "FR"}}
EVENEMENT = {
    "@type": "Event", "@id": f"{SITE}/#dualtech-2026", "name": "DualTech 2026",
    "description": "Seconde édition de DualTech, le rendez-vous de l'industrie duale : tables rondes, keynotes, pitch sessions, expositions et ateliers réunissant acteurs publics, industriels, scientifiques et financiers de la souveraineté technologique française et européenne.",
    "startDate": "2026-11-26T08:30:00+01:00", "endDate": "2026-11-26T19:00:00+01:00",
    "eventStatus": "https://schema.org/EventScheduled", "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
    "location": {"@id": f"{SITE}/#campus-cyber"}, "image": [f"{SITE}/wp-content/themes/dualtech/assets/img/og-dualtech-2026.jpg"],
    "organizer": {"@id": f"{SITE}/#inas"}, "sponsor": {"@id": f"{SITE}/#pold"}, "inLanguage": "fr-FR", "url": f"{SITE}/",
    "maximumAttendeeCapacity": 400, "isAccessibleForFree": True,
    "offers": {"@type": "Offer", "url": LUMA, "price": "0", "priceCurrency": "EUR", "availability": "https://schema.org/InStock", "description": "Inscription sur demande, validée par l'organisation"},
    "performer": [{"@type": "Person", "name": i["nom"], "jobTitle": i["fonction"], "worksFor": {"@type": "Organization", "name": i["orga"]}} for i in INTERVENANTS],
}


def fil(*etapes):
    return {"@type": "BreadcrumbList", "itemListElement": [{"@type": "ListItem", "position": n + 1, "name": nom, "item": u} for n, (nom, u) in enumerate(etapes)]}


def tete(cle, titre, description, graphe, precharge="", og_image="img/og-dualtech-2026.jpg"):
    canon = url(cle)
    g = json.dumps({"@context": "https://schema.org", "@graph": graphe}, ensure_ascii=False, separators=(",", ":"))
    return f"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{escape(titre)}</title>
<meta name="description" content="{escape(description)}">
<link rel="canonical" href="{canon}">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta property="og:locale" content="fr_FR">
<meta property="og:type" content="website">
<meta property="og:site_name" content="DualTech">
<meta property="og:title" content="{escape(titre)}">
<meta property="og:description" content="{escape(description)}">
<meta property="og:url" content="{canon}">
<meta property="og:image" content="{SITE}/wp-content/themes/dualtech/assets/{og_image}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#041226">
<link rel="icon" href="assets/favicon-32.png" sizes="32x32">
<link rel="apple-touch-icon" href="assets/favicon-180.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&family=Public+Sans:wght@300..700&display=swap">
{precharge}<link rel="stylesheet" href="assets/dualtech.css">
<script>document.documentElement.classList.add('js')</script>
<script type="application/ld+json">{g}</script>
</head>
"""


def entete(cle):
    menu = "".join(f'<a href="{lien(k)}"{" aria-current=\"page\"" if k == cle else ""}>{t}</a>' for k, t in NAV)
    prix = f'<a class="nav__prix" href="{lien("prix")}"{" aria-current=\"page\"" if cle == "prix" else ""}>Prix de l\'innovation</a>'
    tiroir = "".join(f'<a class="tiroir__lien" href="{lien(k)}"{" aria-current=\"page\"" if k == cle else ""}><small>{n + 1:02d}</small>{t}</a>' for n, (k, t) in enumerate(TIROIR))
    return f"""<body>
<a class="skip" href="#contenu">Aller au contenu</a>
<header class="nav" role="banner">
  <div class="wrap nav__in">
    <a class="nav__logo" href="{lien('accueil')}" aria-label="DualTech, retour à l'accueil"><img src="assets/logo-dualtech.webp" alt="DualTech" width="150" height="21"></a>
    <nav class="nav__menu" aria-label="Navigation principale">{menu}{prix}</nav>
    <a class="btn btn--primaire btn--petit nav__cta" href="{lien('inscription')}">S'inscrire{ico('fleche')}</a>
    <button class="nav__burger" type="button" aria-expanded="false" aria-controls="tiroir" aria-label="Ouvrir le menu"><span class="nav__burger-in"><span></span><span></span></span></button>
  </div>
  <div class="nav__progression" aria-hidden="true"></div>
</header>
<div class="tiroir" id="tiroir">
  <nav aria-label="Navigation mobile">{tiroir}</nav>
  <a class="btn btn--primaire" href="{lien('inscription')}">S'inscrire à l'événement{ico('fleche')}</a>
</div>
<main id="contenu">
"""


def pied():
    return f"""</main>
<footer class="pied">
  <div class="wrap">
    <div class="pied__grille">
      <div class="pied__marque">
        <a class="pied__logo" href="{lien('accueil')}"><img src="assets/logo-dualtech.webp" alt="DualTech" width="170" height="24" loading="lazy"></a>
        <p>Un événement organisé par l'Institut National des Affaires Stratégiques avec le soutien de Paris Ouest La Défense.</p>
        <div class="pied__orgas">
          <a href="https://inas-france.fr/" target="_blank" rel="noopener"><img src="assets/partenaires/inas-blanc.webp" alt="Institut National des Affaires Stratégiques (INAS)" width="44" height="44" loading="lazy"></a>
          <a href="https://www.parisouestladefense.fr/" target="_blank" rel="noopener"><img src="assets/partenaires/pold-blanc.webp" alt="Paris Ouest La Défense" width="88" height="44" loading="lazy"></a>
        </div>
      </div>
      <div><h2>L'événement</h2><ul><li><a href="{lien('accueil')}">Accueil</a></li><li><a href="{lien('retrospective')}">Rétrospective première édition</a></li><li><a href="{lien('intervenants')}">Intervenants</a></li><li><a href="{lien('prix')}">Prix de l'innovation duale</a></li></ul></div>
      <div><h2>Participer</h2><ul><li><a href="{lien('programme')}">Programme 2026</a></li><li><a href="{lien('inscription')}">S'inscrire</a></li><li><a href="{lien('partenaires')}">Devenir partenaire</a></li><li><a href="{lien('workshops')}">Proposer un workshop</a></li></ul></div>
      <div><h2>S'informer</h2><ul><li><a href="{lien('infos')}">Infos pratiques</a></li><li><a href="{lien('contact')}">Contact</a></li><li><a href="{LINKEDIN}" target="_blank" rel="noopener">LinkedIn</a></li></ul></div>
    </div>
    <div class="pied__bas"><p>© 2026 DualTech · Tous droits réservés</p><p>Jeudi 26 novembre 2026 · Campus Cyber · La Défense</p></div>
  </div>
</footer>
<script src="assets/vendor/gsap.min.js" defer></script>
<script src="assets/vendor/ScrollTrigger.min.js" defer></script>
<script src="assets/dualtech.js" defer></script>
<script src="assets/dualtech-fx.js" defer></script>
</body>
</html>
"""


def phero(ariane, surtitre, titre_lignes, lead, actions="", fond="", meta="", deco=True):
    ar = "".join(f'<li><a href="{lien(k)}">{t}</a></li>' if k else f'<li aria-current="page">{t}</li>' for k, t in ariane)
    return f"""<section class="phero" data-reseau>
  {fond}{'<div class="phero__grille-deco" aria-hidden="true"></div>' if deco else ''}
  <div class="wrap">
    <nav aria-label="Fil d'Ariane"><ol class="ariane">{ar}</ol></nav>
    <p class="surtitre" data-reveal>{surtitre}</p>
    {lignes(*titre_lignes, tag="h1", cls="")}
    <p class="lead" data-reveal style="--d:.25s">{lead}</p>
    {f'<div class="actions" data-reveal style="--d:.35s">{actions}</div>' if actions else ''}
    {meta}
  </div>
</section>
"""


def btn(texte, href, style="primaire", icone="fleche", externe=False):
    ext = ' target="_blank" rel="noopener"' if externe else ""
    return f'<a class="btn btn--{style}" href="{href}"{ext}>{texte}{ico(icone) if icone else ""}</a>'

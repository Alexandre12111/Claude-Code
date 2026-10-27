"""Convertit la maquette en contenu pour le thème WordPress Aurea : python3 exporter_theme.py

La maquette (aurea-media/maquette) reste la référence du design. Ce script en tire :
- parts/entete.html et parts/pied.html : en-tête (sprite d'icônes, menu, méga menu) et pied de page ;
- contenu/<slug>.html : le contenu de chaque page, découpé en blocs Gutenberg (un bloc HTML par section),
  prêt à être importé par le thème (Apparence → Installation Aurea) ;
- contenu/archive-realisations.html, contenu/blog-entete.html : en-têtes des pages générées par le thème ;
- contenu/pages.json : titre, Rank Math (titre SEO, description, mot clé), fil d'Ariane, service et prix
  de chaque page ;
- assets/ : feuille de style, script, images de la maquette, polices auto hébergées.

Repères remplacés au moment de l'import ou de l'affichage (inc/gabarit.php) :
{{accueil}}, {{url:slug}}, {{theme}}, {{tel}}, {{tel_affiche}}, {{tel_inter}}, {{email}}.
Les parties qui dépendent des données d'Aurea Studio deviennent des codes courts :
[aurea_mur], [aurea_logos_bandeau], [aurea_portfolio], [aurea_projets …], [aurea_avis_cartes],
[aurea_avis_note], [aurea_contact_jeton].
"""
import html as H
import json
import re
import shutil
import sys
from pathlib import Path

ICI = Path(__file__).resolve().parent
MAQUETTE = ICI.parent.parent / "maquette"
SEO = ICI.parent.parent / "seo"
THEME = ICI.parent / "aurea"
POLICES = Path(sys.argv[1]) if len(sys.argv) > 1 else None  # dossier des woff2 (fontsource)

sys.path.insert(0, str(MAQUETTE / "outils"))
from seo_geo import PAGES as SEO_PAGES  # noqa: E402

# fichier de la maquette : (slug WordPress, titre de la page dans l'administration)
PAGES = {
    "index.html": ("accueil", "Accueil"),
    "creation-site-vitrine.html": ("creation-site-vitrine", "Création de site vitrine"),
    "creation-site-ecommerce.html": ("creation-site-ecommerce", "Création de site e-commerce"),
    "refonte-site-internet.html": ("refonte-site-internet", "Refonte de site internet"),
    "logo-identite-visuelle.html": ("logo-identite-visuelle", "Logo et identité visuelle"),
    "motion-design.html": ("motion-design", "Motion design"),
    "maquettes-presentation.html": ("maquettes-presentation", "Maquettes de présentation"),
    "site-internet-avocat.html": ("site-internet-avocat", "Site internet avocat"),
    "site-internet-professions-sante.html": ("site-internet-professions-sante", "Site internet professions de santé"),
    "site-internet-architecte.html": ("site-internet-architecte", "Site internet architecte"),
    "site-internet-restaurant.html": ("site-internet-restaurant", "Site internet restaurant"),
    "site-internet-artisan-btp.html": ("site-internet-artisan-btp", "Site internet artisan et BTP"),
    "site-internet-coach-consultant.html": ("site-internet-coach-consultant", "Site internet coach et consultant"),
    "agence-web-paris.html": ("agence-web-paris", "Agence web Paris"),
    "agence-web-hauts-de-seine.html": ("agence-web-hauts-de-seine", "Agence web Hauts de Seine"),
    "tarifs.html": ("tarifs", "Tarifs"),
    "agence.html": ("agence", "L'agence"),
    "contact.html": ("contact", "Contact"),
    "mentions-legales.html": ("mentions-legales", "Mentions légales"),
    "politique-confidentialite.html": ("politique-confidentialite", "Politique de confidentialité"),
    "plan-du-site.html": ("plan-du-site", "Plan du site"),
}
# Pages de texte : importées en blocs WordPress natifs (paragraphes, titres, listes), modifiables sans HTML.
TEXTE = {"mentions-legales.html", "politique-confidentialite.html", "plan-du-site.html"}

# Liens de la maquette vers les adresses WordPress
LIENS = {f: s for f, (s, _) in PAGES.items()}
LIENS.update({"realisations.html": "realisations", "conseils.html": "conseils", "realisation-havel-trading.html": "realisation:havel-trading",
              "article-prix-site-vitrine.html": "article:prix-site-vitrine"})


def bloc_html(contenu):
    return f"<!-- wp:html -->\n{contenu.strip()}\n<!-- /wp:html -->"


def elements(html):
    """Éléments de premier niveau d'un fragment (balises imbriquées comptées)."""
    sortie, i = [], 0
    while True:
        m = re.compile(r"<(section|header|article|div|nav|aside|form|footer)\b").search(html, i)
        if not m:
            break
        tag, profondeur = m.group(1), 0
        for b in re.finditer(rf"<(/?){tag}\b[^>]*>", html[m.start():]):
            profondeur += -1 if b.group(1) else 1
            if profondeur == 0:
                fin = m.start() + b.end()
                break
        sortie.append(html[m.start():fin])
        i = fin
    return sortie


def interieur(html, debut):
    """Remplace le contenu d'un élément (gardé) : renvoie (début, fin) de son intérieur."""
    s = html.index(debut)
    tag = re.match(r"<(\w+)", debut).group(1)
    ouverture = html.index(">", s) + 1
    profondeur = 0
    for b in re.finditer(rf"<(/?){tag}\b[^>]*>", html[s:]):
        profondeur += -1 if b.group(1) else 1
        if profondeur == 0:
            return ouverture, s + b.start()
    raise ValueError(debut)


def remplacer_element(html, debut, neuf):
    s = html.index(debut)
    tag = re.match(r"<(\w+)", debut).group(1)
    profondeur = 0
    for b in re.finditer(rf"<(/?){tag}\b[^>]*>", html[s:]):
        profondeur += -1 if b.group(1) else 1
        if profondeur == 0:
            return html[:s] + neuf + html[s + b.end():]
    raise ValueError(debut)


def cles_galerie(gal):
    return [re.search(r"realisations/([a-z0-9-]+)\.webp", a).group(1) for a in re.findall(r"<article class=\"proj.*?</article>", gal, re.S)]


def dynamiser(html, fichier):
    """Les parties alimentées par Aurea Studio deviennent des codes courts."""
    if '<div class="wall">' in html:
        a, b = interieur(html, '<div class="wall">')
        html = html[:a] + "[aurea_mur]" + html[b:]
    if '<section class="logos"' in html:
        html = remplacer_element(html, '<section class="logos"', "[aurea_logos_bandeau]")
    if '<div class="work-track">' in html:
        a, b = interieur(html, '<div class="work-track">')
        html = html[:a] + "[aurea_portfolio]" + html[b:]
    if '<div class="reviews">' in html:
        html = remplacer_element(html, '<div class="reviews">', "[aurea_avis_cartes]")
    if '<div class="g-score' in html:
        html = remplacer_element(html, '<div class="g-score', "[aurea_avis_note]")
    if '<div class="gal gal--filtre">' in html:
        html = remplacer_element(html, '<div class="gal gal--filtre">', '[aurea_projets filtre="1" fiche="1"]')
    # Galeries de projets : par type sur les pages services, sinon la sélection de la maquette
    while re.search(r'<div class="gal( gal--(?:plus|2))?">', html):
        m = re.search(r'<div class="gal( gal--(?:plus|2))?">', html)
        s = m.start()
        gal = html[s:]
        a, b = interieur(html, m.group(0))
        cles = cles_galerie(html[a:b])
        meta = ' meta="secteur"' if re.search(r'<div class="proj-meta"><h3>[^<]+</h3><span>(?!Site )', html[a:b]) else ""
        classe = ' classe="gal--2"' if m.group(1) == " gal--2" else ""
        if m.group(1) == " gal--plus":  # page site vitrine : tous les sites vitrines
            code = f'[aurea_projets type="site-vitrine" plus="6"{meta}]'
        elif fichier == "creation-site-ecommerce.html":
            code = f'[aurea_projets type="site-ecommerce"{meta}{classe}]'
        else:
            code = f'[aurea_projets cles="{",".join(cles)}"{meta}{classe}]'
        html = remplacer_element(html, m.group(0), code)
        # la suite du bouton « Afficher les 21 sites vitrines » est générée par le code court
        html = re.sub(r'\s*<div class="plus-wrap">.*?</div>', "", html, count=1, flags=re.S)
    if 'id="form-contact"' in html:
        html = html.replace('<form class="formulaire rv" id="form-contact" novalidate>',
                            '<form class="formulaire rv" id="form-contact" method="post" action="{{admin_post}}" data-envoi="serveur" novalidate>[aurea_contact_jeton]')
        html = re.sub(r"\s*Exemple de maquette\s*:\s*rien n'a été transmis\.", "", html).replace('name="besoin"', 'name="besoin[]"')
    return html


def reperes(html):
    """Adresses de la maquette remplacées par des repères résolus par WordPress."""
    def lien(m):
        f, ancre = m.group(1), m.group(2) or ""
        if f == "index.html":
            return f'href="{{{{accueil}}}}{ancre}"'
        cle = LIENS.get(f)
        if not cle:
            print("  lien inconnu :", f)
            return m.group(0)
        return f'href="{{{{url:{cle}}}}}{ancre}"'
    html = re.sub(r'href="([a-z0-9-]+\.html)(#[^"]*)?"', lien, html)
    html = re.sub(r'(src|href|data-src|poster)="assets/', r'\1="{{theme}}/assets/', html)
    html = html.replace("url(logos/", "url({{theme}}/assets/logos/")
    html = html.replace("tel:+33651287761", "tel:{{tel}}").replace("06 51 28 77 61", "{{tel_affiche}}").replace("+33 6 51 28 77 61", "{{tel_inter}}")
    html = html.replace("mailto:alexandre.coury@aurea-media.fr", "mailto:{{email}}").replace("alexandre.coury@aurea-media.fr", "{{email}}")
    # Note et nombre d'avis : résolus à l'affichage (Aurea Studio ou API Google), jamais figés dans les pages
    html = re.sub(r"\b39((?:\s|&nbsp;|\u202f|\u00a0)avis)", r"{{avis_total}}\1", html)
    html = re.sub(r"4,9(?=(?:\s|&nbsp;|\u202f|\u00a0)?(?:/5|·|sur(?:\s|\u00a0)5|sur(?:\s|\u00a0)Google|</span><sup>|</b>(?:\s|\u00a0)?sur|(?:\s|\u00a0)\(|</dd>))", "{{avis_note}}", html)
    return html


def natifs(prose):
    """HTML simple (titres, paragraphes, listes) en blocs WordPress natifs."""
    sortie = []
    for m in re.finditer(r"<(h2|h3|p|ul)\b([^>]*)>(.*?)</\1>", prose, re.S):
        tag, txt = m.group(1), m.group(3).strip()
        if tag in ("h2", "h3"):
            n = tag[1]
            attrs = "" if n == "2" else ' {"level":3}'
            ident = re.search(r'id="([^"]+)"', m.group(2))
            ident = f' id="{ident.group(1)}"' if ident else ""
            sortie.append(f'<!-- wp:heading{attrs} -->\n<{tag} class="wp-block-heading"{ident}>{txt}</{tag}>\n<!-- /wp:heading -->')
        elif tag == "p":
            sortie.append(f"<!-- wp:paragraph -->\n<p>{txt}</p>\n<!-- /wp:paragraph -->")
        else:
            items = re.findall(r"<li>(.*?)</li>", txt, re.S)
            lis = "".join(f"<!-- wp:list-item -->\n<li>{re.sub(r'<svg.*?</svg>', '', i).strip()}</li>\n<!-- /wp:list-item -->" for i in items)
            sortie.append(f"<!-- wp:list -->\n<ul>{lis}</ul>\n<!-- /wp:list -->")
    return "\n\n".join(sortie)


def principal(html):
    return html[html.index('<main id="contenu">') + len('<main id="contenu">'):html.index("</main>")]


def nettoyer_tete(html):
    """Retire les balises de tête propres à la maquette (Rank Math s'en charge sur le site)."""
    return re.sub(r"<!-- (seo|enbref):debut -->.*?<!-- \1:fin -->\n?", lambda m: m.group(0) if m.group(1) == "enbref" else "", html, flags=re.S)


def texte_brut(fragment):
    return re.sub(r"\s+", " ", H.unescape(re.sub(r"<[^>]+>", "", fragment))).strip()


def main():
    (THEME / "contenu").mkdir(parents=True, exist_ok=True)
    (THEME / "parts").mkdir(parents=True, exist_ok=True)
    rank = {p["slug"]: p for p in json.loads((SEO / "rankmath-pages.json").read_text())}
    index = (MAQUETTE / "index.html").read_text()

    # En-tête et pied de page
    corps = index[index.index("<body>") + len("<body>"):index.index('<main id="contenu">')]
    # Sélecteur FR / EN : rempli par le thème avec les adresses de Polylang (vide sans version anglaise)
    corps = re.sub(r'<div class="langues".*?</div>', "{{langues}}", corps, flags=re.S)
    (THEME / "parts" / "entete.html").write_text(reperes(corps).strip() + "\n")
    pied = index[index.index("</main>") + len("</main>"):index.index('<script src="assets/aurea.js"')]
    pied = pied.replace("© 2026 Aurea Media", "© {{annee}} Aurea Media")
    (THEME / "parts" / "pied.html").write_text(reperes(pied).strip() + "\n")

    infos = {}
    for fichier, (slug, titre_admin) in PAGES.items():
        html = nettoyer_tete((MAQUETTE / fichier).read_text())
        titre_seo = H.unescape(re.search(r"<title>(.*?)</title>", html).group(1))
        desc = H.unescape(re.search(r'<meta name="description" content="([^"]*)">', html).group(1))
        main_html = principal(html)
        if fichier in TEXTE:
            prose = re.search(r'<div class="prose">(.*?)</div>\s*</div>\s*</div>', main_html, re.S).group(1)
            contenu = natifs(reperes(prose))
            chapo = re.search(r'<p class="lead[^"]*"[^>]*>(.*?)</p>', main_html, re.S)
            # liens internes résolus à l'import, comme pour les autres pages
        else:
            blocs = [bloc_html(reperes(dynamiser(e, fichier))) for e in elements(main_html)]
            contenu = "\n\n".join(blocs)
        (THEME / "contenu" / f"{slug}.html").write_text(contenu + "\n")
        url, typ, nom, prix, fil = SEO_PAGES[fichier]
        r = rank.get("" if slug == "accueil" else slug, {})
        h1 = texte_brut(re.search(r"<h1[^>]*>(.*?)</h1>", main_html, re.S).group(1))
        infos[slug] = {
            "titre": titre_admin, "h1": h1, "fichier": f"{slug}.html", "texte": fichier in TEXTE,
            # Titre et description : ceux du plan Rank Math validé (seo/rankmath-pages.json), sinon ceux de la maquette
            "seo_titre": r.get("title") or titre_seo, "seo_description": r.get("description") or desc,
            "mot_cle": r.get("focus_keyword", ""), "type": typ, "service": nom, "prix": prix,
            "fil": [[n, u] for n, u in fil],
        }
        if fichier in TEXTE and chapo:
            infos[slug]["extrait"] = texte_brut(chapo.group(1))
        print(f"{slug:36} {len(contenu):7} car. · title {len(infos[slug]['seo_titre'])} · desc {len(infos[slug]['seo_description'])}")

    # Archive des réalisations et en-tête du blog : gabarits du thème
    real = nettoyer_tete((MAQUETTE / "realisations.html").read_text())
    blocs = elements(principal(real))
    (THEME / "contenu" / "archive-realisations.html").write_text(reperes(dynamiser("\n".join(blocs), "realisations.html")) + "\n")
    infos["realisations"] = {"seo_titre": rank.get("realisations", {}).get("title") or H.unescape(re.search(r"<title>(.*?)</title>", real).group(1)),
                             "seo_description": H.unescape(re.search(r'<meta name="description" content="([^"]*)">', real).group(1)),
                             "mot_cle": rank.get("realisations", {}).get("focus_keyword", "")}
    blog = nettoyer_tete((MAQUETTE / "conseils.html").read_text())
    blocs = elements(principal(blog))
    (THEME / "contenu" / "blog-entete.html").write_text(reperes(blocs[0]) + "\n")
    (THEME / "contenu" / "blog-appel.html").write_text(reperes(blocs[-1]) + "\n")
    infos["conseils"] = {"seo_titre": rank.get("conseils", {}).get("title") or H.unescape(re.search(r"<title>(.*?)</title>", blog).group(1)),
                         "seo_description": H.unescape(re.search(r'<meta name="description" content="([^"]*)">', blog).group(1)),
                         "mot_cle": rank.get("conseils", {}).get("focus_keyword", "")}

    # Étude de cas Havel Trading et article : contenus importés dans leurs fiches
    cas = principal((MAQUETTE / "realisation-havel-trading.html").read_text())
    corps_cas = re.search(r'<div class="cas-corps">(.*?)</div>\s*</div>\s*</div>\s*</section>', cas, re.S).group(1)
    corps_cas = re.sub(r'<p class="cas-note.*?</p>', "", corps_cas, flags=re.S)
    (THEME / "contenu" / "etude-havel-trading.html").write_text(natifs(corps_cas) + "\n")
    (THEME / "contenu" / "cas-appel.html").write_text(reperes(elements(cas)[-1]) + "\n")
    art = nettoyer_tete((MAQUETTE / "article-prix-site-vitrine.html").read_text())
    prose = re.search(r'<div class="prose">(.*?)</div>\s*</div>\s*</div>\s*</article>', principal(art), re.S).group(1)
    (THEME / "contenu" / "article-prix-site-vitrine.html").write_text(article_blocs(reperes(prose)) + "\n")
    infos["article:prix-site-vitrine"] = {"titre": "Combien coûte un site vitrine en 2026 ?",
                                          "seo_titre": H.unescape(re.search(r"<title>(.*?)</title>", art).group(1)),
                                          "seo_description": H.unescape(re.search(r'<meta name="description" content="([^"]*)">', art).group(1)),
                                          "mot_cle": "prix site vitrine,combien coûte un site vitrine,tarif site vitrine",
                                          "extrait": "Entre 500 € et 10 000 €, les écarts de prix sont énormes. Voici ce qui explique vraiment le prix d'un site vitrine, et comment lire un devis."}
    (THEME / "contenu" / "pages.json").write_text(json.dumps(infos, ensure_ascii=False, indent=1) + "\n")
    copier_assets()


def article_blocs(prose):
    """Article : blocs natifs, avec le tableau, l'encart et l'appel à l'action en styles du thème."""
    sortie = []
    for m in re.finditer(r'<(p|h2|ul|div)\b([^>]*)>(.*?)</\1>(?=\s*(?:<(?:p|h2|ul|div)\b|$))', prose, re.S):
        tag, attrs, txt = m.groups()
        if tag == "p" and 'class="intro"' in attrs:
            sortie.append(f'<!-- wp:paragraph {{"className":"intro"}} -->\n<p class="intro">{txt.strip()}</p>\n<!-- /wp:paragraph -->')
        elif tag in ("p", "h2", "ul"):
            sortie.append(natifs(m.group(0)))
        elif 'class="tableau"' in attrs:
            table = re.search(r"<table>.*?</table>", txt, re.S).group(0)
            sortie.append(f'<!-- wp:table {{"className":"tableau"}} -->\n<figure class="wp-block-table tableau">{table}</figure>\n<!-- /wp:table -->')
        else:  # encart, appel à l'action : bloc HTML (mise en forme du thème)
            sortie.append(bloc_html(m.group(0)))
    return "\n\n".join(sortie)


def copier_assets():
    dest = THEME / "assets"
    for sous in ("realisations", "creations"):
        if (dest / sous).exists():
            shutil.rmtree(dest / sous)
        shutil.copytree(MAQUETTE / "assets" / sous, dest / sous)
    for f in ("aurea.css", "aurea.js", "logo-aurea-media-clair.png", "logo-aurea-media.png", "logo-mark-clair.png", "alexandre-coury.webp", "LICENCE-phosphor-MIT.txt"):
        shutil.copy2(MAQUETTE / "assets" / f, dest / f)
    # Polices auto hébergées : plus de requête vers Google Fonts (vitesse et RGPD)
    css = (dest / "aurea.css").read_text()
    if POLICES:
        (dest / "polices").mkdir(exist_ok=True)
        for src, nom in (("fontsource-variable-bricolage-grotesque-5.3.0/package/files/bricolage-grotesque-latin-opsz-normal.woff2", "bricolage-grotesque.woff2"),
                         ("fontsource-variable-geist-5.3.0/package/files/geist-latin-wght-normal.woff2", "geist.woff2"),
                         ("fontsource-geist-mono-5.3.0/package/files/geist-mono-latin-400-normal.woff2", "geist-mono-400.woff2"),
                         ("fontsource-geist-mono-5.3.0/package/files/geist-mono-latin-500-normal.woff2", "geist-mono-500.woff2")):
            shutil.copy2(POLICES / src, dest / "polices" / nom)
    polices = """/* Polices auto hébergées (licence SIL OFL) */
@font-face{font-family:"Bricolage Grotesque";src:url(polices/bricolage-grotesque.woff2) format("woff2");font-weight:200 800;font-display:swap}
@font-face{font-family:"Geist";src:url(polices/geist.woff2) format("woff2");font-weight:100 900;font-display:swap}
@font-face{font-family:"Geist Mono";src:url(polices/geist-mono-400.woff2) format("woff2");font-weight:400;font-display:swap}
@font-face{font-family:"Geist Mono";src:url(polices/geist-mono-500.woff2) format("woff2");font-weight:500;font-display:swap}
"""
    (dest / "aurea.css").write_text(polices + css)


if __name__ == "__main__":
    main()
    import traduire  # version anglaise, à partir du contenu français tout juste exporté
    traduire.main()

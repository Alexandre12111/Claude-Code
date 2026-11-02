"""Convertit la maquette DualTech en thème WordPress : python3 wordpress/outils/exporter_theme.py

La maquette (dualtech/maquette, version la plus récente) reste la référence du design. Ce script en tire :
- parts/entete.html et parts/pied.html : en-tête (menu, tiroir mobile), pied de page, fenêtres vidéo et partenaire ;
- contenu/<slug>.html : le contenu de chaque page, découpé en blocs Gutenberg (un bloc HTML par section) ;
- contenu/pages.json : titre, Rank Math (titre SEO, description, mot clé), données structurées de chaque page ;
- contenu/donnees.json : partenaires, intervenants et séances du programme, importés dans DualTech Studio ;
- assets/ : feuilles de style, scripts, images, vidéo, polices auto hébergées (assets/polices).

Repères remplacés au moment de l'import ou de l'affichage (inc/gabarit.php) : {{accueil}}, {{url:slug}}, {{theme}},
{{admin_post}}, {{site}}. Les parties alimentées par DualTech Studio deviennent des codes courts :
[dualtech_orateurs], [dualtech_intervenants], [dualtech_programme], [dualtech_partenaires], [dualtech_logos],
[dualtech_logos_defile], [dualtech_partenaires_desc], [dualtech_temps_forts], [dualtech_nombre_intervenants],
[dualtech_formulaire] (champs cachés des formulaires, fournis par l'extension).
"""
import json
import re
import shutil
import sys
import urllib.request
from pathlib import Path

ICI = Path(__file__).resolve().parent
RACINE = ICI.parent.parent                       # dossier dualtech
MAQUETTE = RACINE / "maquette"
THEME = ICI.parent / "dualtech"
POLICES = ICI / "polices"                        # woff2 téléchargés une fois, gardés avec les outils

sys.path.insert(0, str(MAQUETTE / "outils"))
import donnees as D  # noqa: E402

SITE = D.SITE
# fichier de la maquette : (slug WordPress, titre dans l'administration, mot clé principal Rank Math)
PAGES = {
    "index.html": ("accueil", "Accueil", "salon industrie duale"),
    "l-evenement.html": ("l-evenement", "L'événement", "événement DualTech"),
    "programme-2026.html": ("programme-2026", "Programme 2026", "programme DualTech 2026"),
    "intervenants-2026.html": ("intervenants-2026", "Intervenants 2026", "intervenants DualTech"),
    "ateliers-workshops.html": ("ateliers-workshops", "Ateliers & Workshops", "workshop DualTech"),
    "devenir-partenaire.html": ("devenir-partenaire", "Devenir partenaire", "partenaire DualTech"),
    "prix-de-linnovation-duale.html": ("prix-de-linnovation-duale", "Prix de l'innovation duale", "prix de l'innovation duale"),
    "infos-pratiques.html": ("infos-pratiques", "Infos pratiques", "DualTech Campus Cyber"),
    "retrospective-fevrier-2026.html": ("retrospective-fevrier-2026", "Rétrospective février 2026", "rétrospective DualTech"),
    "contact.html": ("contact", "Contact", "contact DualTech"),
    "inscription.html": ("inscription", "Inscription", "inscription DualTech 2026"),
}
LIENS = {f: s for f, (s, _, _) in PAGES.items()}


# ---------------------------------------------------------------- outils HTML

def element(html, debut):
    """(début, fin) de l'élément complet qui commence par `debut` (balises imbriquées comptées)."""
    s = html.index(debut)
    tag = re.match(r"<(\w+)", debut).group(1)
    profondeur = 0
    for b in re.finditer(rf"<(/?){tag}\b[^>]*>", html[s:]):
        profondeur += -1 if b.group(1) else 1
        if profondeur == 0:
            return s, s + b.end()
    raise ValueError(debut)


def remplacer_interieur(html, debut, neuf):
    """Remplace le contenu d'un élément (la balise ouvrante et fermante sont gardées)."""
    s, e = element(html, debut)
    ouverture = html.index(">", s) + 1
    fermeture = html.rindex("</", s, e)
    return html[:ouverture] + neuf + html[fermeture:]


def elements(html):
    """Éléments de premier niveau d'un fragment."""
    sortie, i = [], 0
    while True:
        m = re.compile(r"<(section|header|article|div|nav|aside|dialog|footer|p)\b").search(html, i)
        if not m:
            break
        s, e = element(html[m.start():], m.group(0))
        sortie.append(html[m.start():m.start() + e])
        i = m.start() + e
    return sortie


def bloc_html(contenu):
    return f"<!-- wp:html -->\n{contenu.strip()}\n<!-- /wp:html -->"


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
    html = re.sub(r'(src|href|data-src|poster|data-photo|data-logo)="assets/', r'\1="{{theme}}/assets/', html)
    html = html.replace("url(assets/", "url({{theme}}/assets/")
    return html


# ---------------------------------------------------------------- parties dynamiques

def dynamiser(html, fichier):
    if fichier == "index.html":
        html = remplacer_interieur(html, '<ul class="orateurs orateurs--6">', '[dualtech_orateurs nombre="12"]')
    if '<div class="horizon__regle"' in html:
        html = remplacer_interieur(html, '<div class="horizon__regle"', '[dualtech_temps_forts partie="regle"]<i class="horizon__progres"></i>')
        html = remplacer_interieur(html, '<p class="horizon__horloge"', '[dualtech_temps_forts partie="horloge"]')
        html = remplacer_interieur(html, '<ul class="temps">', '[dualtech_temps_forts]')
    if '<div class="logos-defile"' in html:
        html = remplacer_interieur(html, '<div class="logos-defile"', '[dualtech_logos_defile]')
    if fichier == "programme-2026.html":
        html = remplacer_interieur(html, '<div class="filtres" role="group"', '[dualtech_programme partie="filtres"]')
        html = remplacer_interieur(html, '<div class="journee">', '[dualtech_programme]')
    if fichier == "intervenants-2026.html":
        html = remplacer_interieur(html, '<div class="iv-mosaique"', '[dualtech_intervenants partie="mosaique"]')
        html = remplacer_interieur(html, '<div class="filtres" role="group" aria-label="Filtrer les intervenants', '[dualtech_intervenants partie="filtres"]')
        html = remplacer_interieur(html, '<p class="secondaire" id="iv-statut"', '[dualtech_nombre_intervenants] intervenants')
        html = remplacer_interieur(html, '<div class="iv-grille" id="iv-grille">', '[dualtech_intervenants]')
    if fichier == "devenir-partenaire.html":
        html = remplacer_interieur(html, '<div class="logos-groupes">', '[dualtech_partenaires]')
    if fichier == "prix-de-linnovation-duale.html":
        html = remplacer_interieur(html, '<ul class="logos logos--6">', '[dualtech_logos rubrique="prix" libelle="Partenaire du Prix de l\'innovation duale"]')
    if fichier == "retrospective-fevrier-2026.html":
        html = remplacer_interieur(html, '<div class="grille-3">', '[dualtech_partenaires_desc rubrique="edition-2025"]')
    html = re.sub(r"Voir les \d+ intervenants", "Voir les [dualtech_nombre_intervenants] intervenants", html)
    # Formulaires : envoyés à WordPress (DualTech Studio → Demandes reçues) au lieu du service externe
    formulaires = {"contact.html": "contact", "devenir-partenaire.html": "partenariat", "ateliers-workshops.html": "workshop"}
    if fichier in formulaires:
        t = formulaires[fichier]
        html, n = re.subn(r'<form class="form form--2" action="https://formsubmit\.co/[^"]+" method="post">',
                          f'<form class="form form--2" id="formulaire-{t}" data-formulaire="{t}" action="{{{{admin_post}}}}" method="post">[dualtech_formulaire type="{t}"]', html)
        assert n == 1, fichier
        html = re.sub(r'\s*<input type="hidden" name="_subject"[^>]*>', "", html)
    return html


# ---------------------------------------------------------------- données initiales

def donnees_initiales():
    groupes = {"Institutions publiques": "institutions", "Financement et marchés": "financement",
               "Écosystèmes industriels et technologiques": "ecosystemes", "Communication et médias": "medias"}
    partenaires, index = [], {}

    def ajoute(nom, logo, rubrique, description):
        cle = D.slug(f"{nom}-{logo or 'texte'}")
        if cle in index:
            p = index[cle]
            if rubrique not in p["rubriques"]:
                p["rubriques"].append(rubrique)
            return
        p = {"cle": cle, "nom": nom, "logo": f"partenaires/{logo}.webp" if logo else "", "description": description, "lien": "", "rubriques": [rubrique], "ordre": len(partenaires)}
        index[cle] = p
        partenaires.append(p)

    for groupe, liste in D.PARTENAIRES_2026:
        for nom, logo in liste:
            ajoute(nom, logo, groupes[groupe], D.DESCRIPTIONS.get(nom, ""))
    for nom, logo in D.PARTENAIRES_PRIX:
        ajoute(nom, logo, "prix", D.DESCRIPTIONS.get(nom, ""))
    for nom, logo, desc in D.PARTENAIRES_2025:          # première édition : fiches distinctes (descriptions propres)
        cle = D.slug(f"{nom}-2025")
        p = {"cle": cle, "nom": nom, "logo": f"partenaires/{logo}.webp", "description": desc, "lien": "", "rubriques": ["edition-2025"], "ordre": len(partenaires)}
        partenaires.append(p)

    intervenants = [{"cle": D.slug(i["nom"]), "nom": i["nom"], "fonction": i["fonction"], "orga": i["orga"], "univers": i["univers"],
                     "photo": i["photo"].replace("assets/", "")} for i in D.INTERVENANTS]
    # Temps forts de la page L'événement (texte court affiché dans la frise)
    temps_forts = {"09h10": "Stéphane Boujnah, CEO et Chairman of the Managing Board d'Euronext",
                   "09h30": "Thales, MBDA, Safran AI et GICAT autour de la table",
                   "11h00": "Emmanuel Chiva, Président-directeur général de l'ONERA",
                   "14h15": "Expansion, Eurazeo Tech, Aldoria et Rothschild & Co",
                   "17h15": "Trois lauréats en Robotique, Santé et Deeptech",
                   "18h15": "Stéphane Ouaki, European Innovation Council and SMEs Executive Agency (EISMEA)"}
    seances = []
    for _, creneaux in D.PROGRAMME:
        for d, f, t, titre, note, intervs, modos in creneaux:
            seances.append({"cle": f"seance-{len(seances) + 1:02d}", "titre": titre, "debut": d.replace("h", ":"), "fin": f.replace("h", ":"), "type": t,
                            "note": note, "intervenants": [D.slug(n) for n in intervs], "moderateurs": [D.slug(n) for n in modos],
                            "temps_fort": d in temps_forts, "resume": temps_forts.get(d, "")})
    return {"partenaires": partenaires, "intervenants": intervenants, "seances": seances}


# ---------------------------------------------------------------- polices auto hébergées

def polices():
    """Archivo (axes largeur et graisse) et Public Sans en woff2, sous ensembles latin et latin étendu."""
    POLICES.mkdir(exist_ok=True)
    css_cache = POLICES / "google.css"
    if not css_cache.exists():
        req = urllib.request.Request("https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&family=Public+Sans:wght@300..700&display=swap",
                                     headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"})
        css_cache.write_text(urllib.request.urlopen(req).read().decode())
    css = css_cache.read_text()
    sortie = []
    for bloc in re.findall(r"/\* ([a-z-]+) \*/\s*(@font-face \{.*?\})", css, re.S):
        sous, regle = bloc
        if sous not in ("latin", "latin-ext"):
            continue
        url = re.search(r"url\((https://[^)]+\.woff2)\)", regle).group(1)
        famille = re.search(r"font-family: '([^']+)'", regle).group(1)
        nom = f"{D.slug(famille)}-{sous}-{Path(url).stem[-10:]}.woff2"
        if not (POLICES / nom).exists():
            (POLICES / nom).write_bytes(urllib.request.urlopen(url).read())
        sortie.append(f"/* {famille}, {sous} */\n" + regle.replace(url, f"polices/{nom}"))
    return "\n".join(sortie)


# ---------------------------------------------------------------- export

def main():
    if THEME.exists():
        for sous in ("assets", "parts", "contenu"):
            shutil.rmtree(THEME / sous, ignore_errors=True)
    (THEME / "parts").mkdir(parents=True, exist_ok=True)
    (THEME / "contenu").mkdir(exist_ok=True)

    # assets de la maquette, polices locales
    shutil.copytree(MAQUETTE / "assets", THEME / "assets")
    (THEME / "assets" / "polices").mkdir(exist_ok=True)
    (THEME / "assets" / "polices.css").write_text("/* Polices de la charte DualTech, hébergées avec le thème (aucun appel à Google Fonts) */\n" + polices())
    for f in POLICES.glob("*.woff2"):
        shutil.copy(f, THEME / "assets" / "polices" / f.name)

    accueil = (MAQUETTE / "index.html").read_text()
    entete = accueil[accueil.index('<a class="skip"'):accueil.index('<main id="contenu">')]
    entete = re.sub(r' aria-current="page"', "", entete)
    pied = accueil[accueil.index('<footer class="pied">'):accueil.index('<script src="assets/vendor')]
    (THEME / "parts" / "entete.html").write_text(reperes(entete))
    (THEME / "parts" / "pied.html").write_text(reperes(pied))

    infos = {}
    for fichier, (slug, titre, mot_cle) in PAGES.items():
        html = (MAQUETTE / fichier).read_text()
        corps = html[html.index('<main id="contenu">') + len('<main id="contenu">'):html.index("</main>")]
        corps = reperes(dynamiser(corps, fichier))
        blocs = elements(corps)
        reste = re.sub(r"\s+", "", corps)
        assert len("".join(re.sub(r"\s+", "", b) for b in blocs)) == len(reste), f"contenu perdu dans {fichier}"
        (THEME / "contenu" / f"{slug}.html").write_text("\n\n".join(bloc_html(b) for b in blocs) + "\n")
        graphe = json.loads(re.search(r'<script type="application/ld\+json">(.*?)</script>', html, re.S).group(1))
        graphe = json.loads(json.dumps(graphe, ensure_ascii=False).replace(SITE, "{{site}}"))
        infos[slug] = {
            "fichier": f"{slug}.html",
            "titre": titre,
            "seo_titre": re.search(r"<title>(.*?)</title>", html).group(1).replace("&#x27;", "'").replace("&amp;", "&"),
            "seo_description": re.search(r'<meta name="description" content="(.*?)">', html).group(1).replace("&#x27;", "'").replace("&amp;", "&"),
            "mot_cle": mot_cle,
            "precharge": "assets/img/hero-poster.webp" if fichier == "index.html" else "",
            "graphe": graphe["@graph"],
        }
    (THEME / "contenu" / "pages.json").write_text(json.dumps(infos, ensure_ascii=False, indent=1))
    (THEME / "contenu" / "donnees.json").write_text(json.dumps(donnees_initiales(), ensure_ascii=False, indent=1))
    print(f"Thème exporté : {len(infos)} pages, parts, données, polices.")


if __name__ == "__main__":
    main()

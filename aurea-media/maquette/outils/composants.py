"""Composants partagés des pages de la maquette (en-tête et pied repris de index.html)."""
import re
from pathlib import Path
from typo import corriger

RACINE = Path(__file__).resolve().parent.parent
INDEX = (RACINE / "index.html").read_text()

# Réalisations : la même liste que dans le plugin Aurea Studio
REAL = {
    "havel-trading": ("Havel Trading", "haveltrading.com", "Site vitrine", "Industrie et énergie", "Genève", "Négoce d'énergie indépendant : une présence institutionnelle sobre, à la hauteur des marchés européens."),
    "sunrock": ("Sunrock", "sunrock.fr", "Site e-commerce", "Industrie et énergie", "France", "Catalogue balistique et tactique pour les acteurs de la Défense et du maintien de l'ordre."),
    "portugal-expert": ("Portugal Expert Properties", "portugalexpertproperties.com", "Site vitrine", "Architecture et immobilier", "Lisbonne", "Résidences de luxe sur mesure au sud de Lisbonne, mises en scène comme un magazine."),
    "eddy-bilou": ("Eddy Bilou", "eddybilouphotography.com", "Site vitrine", "Photographes et créatifs", "Paris", "Une galerie cinématographique pour un photographe de mariage issu de la mode."),
    "verisol": ("Groupe Verisol", "groupe-verisol.fr", "Site vitrine", "Industrie et énergie", "France", "Architecture claire pour présenter les filiales d'un groupe de la transition énergétique."),
    "perle-hair": ("Perle Hair", "perlehair.fr", "Site e-commerce", "Commerce et beauté", "France", "Boutique d'extensions et de soins capillaires, pensée pour l'achat sur mobile."),
    "novexia": ("Novexia Construction", "novexia-construction.fr", "Site vitrine", "Artisans et BTP", "Occitanie", "Construction, rénovation, isolation et extensions de maisons."),
    "xpert-medical": ("Xpert Medical", "xpert-medical.fr", "Site vitrine", "Santé", "France", "Réseau de centres médicaux pluridisciplinaires, pour un accès rapide aux soins."),
    "dual-tech": ("Dual Tech", "dualtech-event.eu", "Site vitrine", "Événementiel et institutions", "Europe", "Salon européen de l'innovation duale et de la souveraineté technologique."),
    "villa-moniris": ("Villa Moniris", "villamoniris.fr", "Site vitrine", "Restauration et hôtellerie", "La Crau", "Propriété de charme pour 14 personnes, avec réservation par espace."),
    "barber-korner": ("Barber Korner", "barber-korner.fr", "Site vitrine", "Commerce et beauté", "Paris", "Salon de barbier traditionnel avec prise de rendez vous en ligne."),
    "laurence-paoli": ("Maître Laurence Paoli", "avocat-paoli.fr", "Site vitrine", "Avocats et droit", "Essonne", "Avocate spécialisée en droit de la famille, des personnes et de leur patrimoine."),
    "normande-isolation": ("Normande d'Isolation", "normande-disolation.com", "Site vitrine", "Industrie et énergie", "Normandie", "Isolation industrielle : calorifugeage, acoustique, protection incendie et échafaudages."),
    "tcm-grand-est": ("TCM Grand Est", "tcm-grandest.fr", "Site vitrine", "Industrie et énergie", "Grand Est", "Travaux industriels sur mesure en soudure, tuyauterie et mécanique."),
    "sultan": ("Sultan Conseil & Leviers", "sultan.cl", "Site vitrine", "Coachs et consultants", "International", "Cabinet indépendant pour les transformations à fort enjeu, entre politique, économie et géopolitique."),
    "enzym": ("Enzym", "enzymco.fr", "Site vitrine", "Coachs et consultants", "France", "Coaching et formation pour dirigeants, managers et particuliers."),
    "inas": ("INAS", "inas-france.fr", "Site vitrine", "Événementiel et institutions", "Paris", "Think tank indépendant dédié à la souveraineté nationale et européenne."),
    "iff-paris": ("Centre Franco Iranien", "iff-paris.com", "Site vitrine", "Événementiel et institutions", "Paris", "Association qui crée des ponts culturels, économiques et scientifiques entre la France et l'Iran."),
    "cercle-la-raison": ("Cercle La Raison", "raison-cercle.fr", "Site vitrine", "Restauration et hôtellerie", "Bordeaux", "Cercle privé de réservation d'événements d'exception, dans un cadre confidentiel."),
    "duchentyls": ("Domaine Duchentyls", "domaine-duchentyls.fr", "Site vitrine", "Restauration et hôtellerie", "France", "Lieu de charme pour séjours, réceptions privées et événements en pleine nature."),
    "massy": ("Changer d'Air à Massy", "massy-changer-dair.fr", "Site vitrine", "Événementiel et institutions", "Massy", "Plateforme de campagne municipale pour les élections de Massy 2026."),
    "tayeb": ("Affaire Tayeb Benabderrahmane", "affaire-benabderrahmane.com", "Site vitrine", "Événementiel et institutions", "International", "Site biographique consacré à l'affaire Tayeb Benabderrahmane."),
    "maryes": ("Maryes Vidéo", "maryesvideaste.com", "Site vitrine", "Photographes et créatifs", "Paris", "Vidéaste de mariage à l'approche naturelle, discrète et émotionnelle."),
}


def element(html, debut):
    """Extrait l'élément complet qui commence par `debut` (balises imbriquées comptées)."""
    s = html.index(debut)
    tag = re.match(r"<(\w+)", debut).group(1)
    profondeur, i = 0, s
    for m in re.finditer(rf"<(/?){tag}\b[^>]*>", html[s:]):
        profondeur += -1 if m.group(1) else 1
        if profondeur == 0:
            return html[s:s + m.end()]
    raise ValueError(debut)

def _bloc(debut, fin):
    s = INDEX.index(debut)
    return INDEX[s:INDEX.index(fin, s) + len(fin)]

TETE = INDEX[:INDEX.index('<main id="contenu">')]
PIED = INDEX[INDEX.index("</main>"):]
STEPS = element(INDEX, '<div class="steps">')
LOGOS = _bloc('<section class="logos"', "</section>")
PRIX = element(INDEX, '<div class="prices">')
AVIS = element(INDEX, '<div class="reviews">')


def page(fichier, titre, description, corps, precharge=""):
    tete = re.sub(r"<title>.*?</title>", f"<title>{titre}</title>", TETE, count=1)
    tete = re.sub(r'<meta name="description" content="[^"]*">', f'<meta name="description" content="{description}">', tete, count=1)
    if precharge:
        tete = tete.replace("</head>", f'<link rel="preload" as="image" href="{precharge}">\n</head>', 1)
    (RACINE / fichier).write_text(corriger(tete + '<main id="contenu">\n' + corps + "\n" + PIED))
    print("écrit", fichier, f"title={len(titre)} desc={len(description)}")


def ico(nom):
    return f'<svg><use href="#i-{nom}"/></svg>'


def fil(*etapes):
    liens = ['<a href="index.html">Accueil</a>']
    for i, (nom, url) in enumerate(etapes):
        liens.append("<span>/</span>")
        liens.append(f'<span aria-current="page">{nom}</span>' if i == len(etapes) - 1 else f'<a href="{url}">{nom}</a>')
    return f'<nav class="crumbs rv" aria-label="Fil d\'Ariane">{"".join(liens)}</nav>'


def hero(etapes, surtitre, h1, chapo, visuel, boutons=None):
    boutons = boutons or [("Obtenir mon devis", "contact.html", True), ("Voir les réalisations", "realisations.html", False)]
    btn = "".join(f'<a class="btn{"" if p else " btn--ghost"}" href="{u}">{t}{" " + ico("arrow") if p else ""}</a>' for t, u, p in boutons)
    return f"""<section class="phero">
  <div class="gridbg" aria-hidden="true"></div>
  <div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap">
    {fil(*etapes)}
    <div class="phero-grid">
      <div>
        <span class="eyebrow rv">{surtitre}</span>
        <h1 class="split-words">{h1}</h1>
        <p class="lead rv">{chapo}</p>
        <div class="cta-row rv" style="margin-top:34px">{btn}</div>
      </div>
      <div class="phero-vis rv" style="--d:.2s">{visuel}</div>
    </div>
  </div>
</section>"""


def entete(surtitre, h2, chapo="", centre=False):
    p = f'<p class="lead rv">{chapo}</p>' if chapo else ""
    if centre:
        return f'<div class="sec-hd sec-hd--c"><span class="eyebrow rv">{surtitre}</span><h2 class="split-words">{h2}</h2>{p}</div>'
    return f'<div class="sec-hd"><div><span class="eyebrow rv">{surtitre}</span><h2 class="split-words">{h2}</h2></div>{p}</div>'


def section(contenu, papier=False, ident="", style=""):
    cls = "sec paper" if papier else "sec"
    grille = '\n  <div class="gridbg" aria-hidden="true"></div>' if papier else ""
    i = f' id="{ident}"' if ident else ""
    s = f' style="{style}"' if style else ""
    return f'<section class="{cls}"{i}{s}>{grille}\n  <div class="wrap">\n{contenu}\n  </div>\n</section>'


def avantages(surtitre, h2, chapo, items, extra=""):
    arts = "".join(f'<article class="rv"><div class="ico">{ico(i)}</div><div><h3>{t}</h3><p>{d}</p></div></article>' for i, t, d in items)
    return section(f"""<div class="split">
    <div class="sticky">
      <span class="eyebrow rv">{surtitre}</span>
      <h2 class="split-words" style="margin:20px 0 24px">{h2}</h2>
      <p class="lead rv">{chapo}</p>
      <a class="btn btn--dark rv" href="contact.html" style="margin-top:30px">Parler de mon projet {ico("arrow")}</a>
      {extra}
    </div>
    <div class="feat">{arts}</div>
  </div>""", papier=True)


def navigateur(cle, lazy=True, classe=""):
    t, dom = REAL[cle][0], REAL[cle][1]
    return f'<div class="browser {classe}"><div class="browser-bar"><i></i><i></i><i></i><span>{dom}</span></div><img src="assets/realisations/{cle}.webp" alt="Site {t} réalisé par Aurea Media" width="1600" height="1000"{" loading=\"lazy\"" if lazy else ""}></div>'


def carte_projet(cle, meta=None, fiche=False):
    t, dom, typ, sect, lieu, acc = REAL[cle]
    lien = "realisation-havel-trading.html" if fiche else f"https://{dom}/"
    cible = "" if fiche else ' target="_blank" rel="noopener"'
    return f"""<article class="proj rv" data-type="{typ}" data-secteur="{sect}"><a class="browser proj-img" href="{lien}"{cible}><span class="browser-bar"><i></i><i></i><i></i><span>{dom}</span></span><img src="assets/realisations/{cle}.webp" alt="Site {t} réalisé par Aurea Media" width="1600" height="1000" loading="lazy"><span class="visit">{"Voir l'étude de cas" if fiche else "Voir le site"} <svg width="16" height="16"><use href="#i-out"/></svg></span></a><div class="proj-meta"><h3>{t}</h3><span>{meta or typ}</span></div><p>{acc}</p></article>"""


def galerie(surtitre, h2, chapo, cles, papier=False):
    return section(entete(surtitre, h2, chapo) + f'\n<div class="gal">{"".join(carte_projet(c, REAL[c][3] if not papier else None) for c in cles)}</div>', papier=papier)


def methode(h2="Votre site en <span class=\"nw\"><span class=\"gold\">4&nbsp;semaines</span>.</span>", chapo="Un prix fixé au départ, des validations à chaque étape et un interlocuteur unique du premier appel à la mise en ligne."):
    return section(entete("Méthode", h2, chapo) + "\n" + STEPS, papier=True)


def faq(h2, questions):
    d = "".join(f'<details{" open" if i == 0 else ""}><summary>{q}<i></i></summary><p class="ans">{r}</p></details>' for i, (q, r) in enumerate(questions))
    return section(entete("Questions fréquentes", h2, centre=True) + f'\n<div class="faq rv">{d}</div>')


SQUELETTE = '<div class="final-squelette"><span class="fs-nav"><i></i><i></i><i></i><b></b></span><span class="fs-titre">Votre site, <em>ici</em>.</span><span class="fs-ligne"></span><span class="fs-ligne court"></span><span class="fs-btn"></span><span class="fs-cartes"><i></i><i></i><i></i></span></div>'


def appel(surtitre, h2, chapo, bouton="Demander mon devis", ecrans=("villa-moniris", "xpert-medical", "dual-tech")):
    devs = "".join(navigateur(c) for c in ecrans[:2]) + '<div class="browser"><div class="browser-bar"><i></i><i></i><i></i><span>votre-site.fr</span></div>' + SQUELETTE + '</div>'
    return f"""<section class="sec final" style="padding-top:0">
  <div class="wrap">
    <div class="final-box">
      <div>
        <span class="eyebrow rv">{surtitre}</span>
        <h2 class="split-words">{h2}</h2>
        <p class="lead rv">{chapo}</p>
        <div class="cta-row rv"><a class="btn" href="contact.html">{bouton} {ico("arrow")}</a><a class="btn btn--ghost" href="tel:+33651287761">06 51 28 77 61</a></div>
        <div class="reassure rv"><span>{ico("clock")}Réponse sous 24h ouvrées</span><span>{ico("check")}Devis gratuit et détaillé</span><span>{ico("shield")}Sans engagement</span></div>
      </div>
      <div class="final-devs" aria-hidden="true">{devs}</div>
    </div>
  </div>
</section>"""


def carte_prix(montant, mention, points, bouton="Devis gratuit"):
    li = "".join(f"<li>{ico('check')}{p}</li>" for p in points)
    return f'<aside class="phero-card" aria-label="Tarif"><span class="eyebrow">Tarif</span><p class="amount">{montant} <small>{mention}</small></p><ul>{li}</ul><a class="btn" href="contact.html">{bouton} {ico("arrow")}</a></aside>'


def puce(icone, titre, texte, style, ok=False):
    return f'<div class="chip" style="{style}"><span class="ci{" ok" if ok else ""}">{ico(icone)}</span><div><b>{titre}</b><span>{texte}</span></div></div>'


# Pages secteur : (fichier, nom, accroche, capture)
SECTEURS = [("site-internet-avocat.html", "Avocats", "Crédibilité et prise de rendez vous", "laurence-paoli"),
            ("site-internet-professions-sante.html", "Professions de santé", "Médecins, kinés, centres de soins", "xpert-medical"),
            ("site-internet-architecte.html", "Architectes", "Portfolio et projets", "portugal-expert"),
            ("site-internet-restaurant.html", "Restaurants et lieux", "Carte, réservation, événements", "villa-moniris"),
            ("site-internet-artisan-btp.html", "Artisans et BTP", "Devis et chantiers", "novexia"),
            ("site-internet-coach-consultant.html", "Coachs et consultants", "Expertise et rendez vous", "sultan")]


def autres_secteurs(courant):
    cartes = "".join(f'<a class="sector rv" href="{f}"><div class="sector-img"><img src="assets/realisations/{img}.webp" alt="" width="1600" height="1000" loading="lazy"></div><div class="sector-txt"><div><b>{n}</b><span>{d}</span></div><svg><use href="#i-out"/></svg></div></a>'
                     for f, n, d, img in SECTEURS if f != courant)
    return section(entete("Autres secteurs", 'Un site adapté à <span class="gold">votre métier</span>.', centre=True) + f'\n<div class="sectors">{cartes}</div>')

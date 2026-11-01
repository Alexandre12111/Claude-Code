"""Génère les dix pages de la maquette DualTech."""
from html import escape
from pathlib import Path
from donnees import *
from gabarit import *
from icones import icone

RACINE = Path(__file__).resolve().parent.parent
POSTER = '<link rel="preload" as="image" href="assets/img/hero-poster.webp" fetchpriority="high">\n'


def ecrire(cle, titre, description, graphe, corps, precharge=""):
    page = tete(cle, titre, description, graphe, precharge) + entete(cle) + corps + pied()
    (RACINE / PAGES[cle][0]).write_text(page)


def webpage(cle, nom, typ="WebPage", extra=None):
    d = {"@type": typ, "@id": url(cle) + "#page", "url": url(cle), "name": nom, "isPartOf": {"@id": f"{SITE}/#site"}, "inLanguage": "fr-FR",
         "dateModified": MAJ, "breadcrumb": {"@id": url(cle) + "#ariane"}}
    d.update(extra or {})
    return d


def ariane_ld(cle, nom):
    f = fil(("Accueil", f"{SITE}/"), (nom, url(cle)))
    f["@id"] = url(cle) + "#ariane"
    return f


BASE = [INAS, POLD, SITEWEB, LIEU]

# ---------- Blocs réutilisés ----------

def bloc_orateur(i, niveau="h3"):
    cherche = escape(slug(f"{i['nom']} {i['fonction']} {i['orga']}").replace("-", " "))
    return f"""<li class="orateur" data-incline data-cherche="{cherche}">
  <div class="orateur__photo"><img src="{i['photo']}" alt="{escape(i['nom'])}, {escape(i['fonction'])}, {escape(i['orga'])}" width="480" height="480" loading="lazy"></div>
  <{niveau}>{escape(i['nom'])}</{niveau}>
  <p>{escape(i['fonction'])}<strong>{escape(i['orga'])}</strong></p>
</li>"""


def logo_btn(nom, f, cat="Partenaire de DualTech 2026"):
    d = DESCRIPTIONS.get(nom, "")
    return (f'<button type="button" class="logo-btn" data-fiche data-nom="{escape(nom)}" data-cat="{escape(cat)}" data-desc="{escape(d)}" data-logo="assets/partenaires/{f}.webp" aria-label="{escape(nom)} : voir la présentation">'
            f'<img src="assets/partenaires/{f}.webp" alt="Logo {escape(nom)}" width="200" height="64" loading="lazy"></button>')


EXPERIENCE_ITEMS = [
    ("conference-keynote", "Keynotes", "Des prises de parole de dirigeants publics, industriels et européens tout au long de la journée."),
    ("conference-table-ronde", "Tables rondes", "Défense, financement, réindustrialisation : quatre tables rondes réunissent décideurs et experts."),
    ("conference-networking", "Networking", "Petit-déjeuner, pauses et déjeuner pour rencontrer donneurs d'ordre, investisseurs et startups."),
    ("conference-auditoire", "Pitch sessions", "Les entreprises des filières stratégiques présentent leurs innovations duales."),
    ("robotique-bras", "Expositions", "Un espace exposants pour découvrir les technologies à double usage."),
    ("conference-public", "Ateliers", "Workshops et ateliers au Campus Cyber, de 15h00 à 15h45."),
]
EXPERIENCE = f"""<section class="sec sec--blanc" aria-labelledby="titre-experience">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>L'expérience DualTech</p>
        {lignes("Une journée,", "six formats", attrs=' id="titre-experience"')}
      </div>
      <p class="lead" data-reveal>Tables rondes, keynotes, pitch sessions, expositions, ateliers et learning expeditions : DualTech met en lumière, tout au long de la journée, les acteurs des filières stratégiques à double usage.</p>
    </div>
    <div class="experience">{"".join(f'<figure class="experience__item"><img src="assets/photos/{f}.webp" alt="" width="800" height="533" loading="lazy" data-parallaxe-img><span class="num-exp">{n + 1:02d}</span><figcaption><b>{t}</b><span>{d}</span></figcaption></figure>' for n, (f, t, d) in enumerate(EXPERIENCE_ITEMS))}</div>
    <p class="mention" style="margin-top:16px">Photos d'illustration.</p>
  </div>
</section>"""


def bloc_logos(liste, cls="logos", cat="Partenaire de DualTech 2026"):
    out = []
    for nom, f in liste:
        if f:
            out.append(f'<li>{logo_btn(nom, f, cat)}</li>')
        else:
            out.append(f'<li><span style="font:700 1.25rem var(--titre);color:var(--encre);letter-spacing:-.01em">{escape(nom)}</span></li>')
    return f'<ul class="{cls}">{"".join(out)}</ul>'


def bloc_lieu(titre_h2="Campus Cyber", texte=None, surtitre="Infos pratiques", carte=True, bouton=True, image="assets/img/campus-cyber-video.webp", alt="Le Campus Cyber à La Défense"):
    texte = texte or "Le Campus Cyber est un lieu phare dédié à la cybersécurité qui rassemble les principaux acteurs nationaux et internationaux du domaine. Il permet en particulier d'accueillir sur un même site des entreprises (grands groupes, PME), des services de l'État, des organismes de formation, des acteurs de la recherche et des associations."
    icos = ["pin", "train", "badge", "accessible"]
    infos = "".join(f'<li><span class="num">{ico(icos[n])}</span><h3>{t}</h3><p>{d}</p></li>' for n, (t, d) in enumerate(INFOS_LIEU))
    c = f"""<div class="carte" style="background-image:url(assets/img/skyline-la-defense.webp)">
      <button class="carte__btn" type="button" data-carte="{CARTE}"><span class="pin">{ico('pin')}</span><strong>Afficher le plan d'accès</strong><small>La carte Google Maps se charge au clic.</small></button>
    </div>""" if carte else ""
    b = f'<div class="actions" style="margin-top:32px">{btn("Préparer sa venue", lien("infos"), "ghost")}</div>' if bouton else ""
    return f"""<div class="lieu">
  <div class="lieu__visuel" data-volet data-yt="{VIDEO_CAMPUS}"><img src="{image}" alt="{alt}" width="640" height="360" loading="lazy"><span class="lieu__etiquette">2026 / La Défense</span><button type="button" class="btn btn--ghost btn--petit btn-video" data-film="{VIDEO_CAMPUS}">Voir le film du Campus Cyber{ico('play')}</button></div>
  <div>
    <p class="surtitre" data-reveal>{surtitre}</p>
    {lignes(titre_h2)}
    <p class="lead" data-reveal style="margin-top:24px">{texte}</p>
    <h3 class="h3" data-reveal style="margin-top:40px">Au cœur du premier quartier d'affaires européen</h3>
    <ul class="infos" data-reveal>{infos}</ul>
    {b}
  </div>
</div>
{c}"""


def bloc_faq(items):
    out = "".join(f'<details><summary>{q}<span class="plus" aria-hidden="true"></span></summary><div class="rep"><p>{r.format(partenaires=lien("partenaires"))}</p></div></details>' for q, r in items)
    return f'<div class="faq" data-reveal>{out}</div>'


def bloc_final(titre=("Le 26 novembre,", "rejoignez l'écosystème dual."), texte="400 places sont disponibles sur inscription préalable. Chaque demande est examinée par l'organisation avant confirmation."):
    return f"""<section class="sec sec--nuit final">
  <div class="wrap">
    <p class="final__date" aria-hidden="true"><span>26</span>.11.2026</p>
    <div class="final__grille">
      <div>
        {lignes(*titre)}
        <p class="lead" data-reveal style="margin-top:24px">{texte}</p>
      </div>
      <div data-reveal>
        <div class="decompte" data-decompte aria-label="Temps restant avant l'ouverture de DualTech 2026" role="timer">
          <div class="decompte__bloc"><span class="decompte__val" data-u="j">--</span><span class="decompte__lib">Jours</span></div>
          <div class="decompte__bloc"><span class="decompte__val" data-u="h">--</span><span class="decompte__lib">Heures</span></div>
          <div class="decompte__bloc"><span class="decompte__val" data-u="m">--</span><span class="decompte__lib">Min</span></div>
        </div>
        <div class="actions" style="margin-top:32px">{btn("S'inscrire", lien("inscription"))}{btn("Devenir partenaire", lien("partenaires"), "ghost")}</div>
      </div>
    </div>
  </div>
</section>
"""


def decompte_hero():
    return """<div class="decompte" data-decompte role="timer" aria-label="Temps restant avant l'ouverture de DualTech 2026">
          <div class="decompte__bloc"><span class="decompte__val" data-u="j">--</span><span class="decompte__lib">Jours</span></div>
          <div class="decompte__bloc"><span class="decompte__val" data-u="h">--</span><span class="decompte__lib">Heures</span></div>
          <div class="decompte__bloc"><span class="decompte__val" data-u="m">--</span><span class="decompte__lib">Min</span></div>
          <div class="decompte__bloc"><span class="decompte__val" data-u="s">--</span><span class="decompte__lib">Sec</span></div>
        </div>"""


# ====================================================================
# ACCUEIL
# ====================================================================
def accueil():
    def ph(t):
        f = PHOTOS_FILIERES.get(t)
        return f'<span class="secteur__photo" aria-hidden="true"><img src="assets/photos/{f}.webp" alt="" width="400" height="300" loading="lazy"></span>' if f else ""
    filieres = "".join(f'<li class="secteur fx-trace faisceau{" secteur--photo" if t in PHOTOS_FILIERES else ""}" tabindex="0" style="--td:{(n % 5) * .08:.2f}s">{ph(t)}<div class="secteur__haut"><span class="secteur__ico-cadre">{icone(t)}</span><span class="secteur__num">{n + 1:02d}</span></div><h3>{t}</h3><p>{d}</p></li>' for n, (t, d) in enumerate(FILIERES))
    bande = "".join(f"<span>{t}</span>" for t, _ in FILIERES)
    chiffres = "".join(f'<li><svg class="anneau" viewBox="0 0 64 64" aria-hidden="true" style="--v:{(0, .12, .3, .2)[n]}"><circle class="fond" cx="32" cy="32" r="30"/><circle class="val" cx="32" cy="32" r="30" pathLength="1"/></svg><b class="chiffre"><sup>{s}</sup><span data-compte="{v}">{v}</span></b><span class="chiffres__lib">{t}</span></li>'.replace("<sup></sup>", "") for n, (v, s, t) in enumerate(CHIFFRES))
    profils = "".join(f'<article class="profil projecteur faisceau" data-incline><span class="profil__num">{n + 1:02d}</span>{ico(i, "ico")}<h3>{t}</h3><p>{d}</p></article>' for n, (t, d, i) in enumerate(PROFILS))
    vedettes = ["Stéphane Boujnah", "Emmanuel Chiva", "Stéphane Ouaki", "Benoist Grossmann", "Alexandre Papaemmanuel", "Charles Beigbeder",
                "Jean-Louis Constanza", "Eric Ottou", "Mathieu Fossat", "Jean-Christophe Fromantin", "Alexandra André", "François Carlot"]
    orateurs = "".join(bloc_orateur(PAR_NOM[n]) for n in vedettes)
    temps = [
        ("09h10", "keynote", "Allocution d'ouverture", "Stéphane Boujnah, CEO et Chairman of the Managing Board d'Euronext"),
        ("09h30", "table", "Industrie de défense, quelles innovations pour préparer les conflits de demain ?", "Thales, MBDA, Safran AI et GICAT autour de la table"),
        ("11h00", "keynote", "Recherche, investissement et commande : quel nouveau modèle pour l'innovation de défense ?", "Emmanuel Chiva, Président-directeur général de l'ONERA"),
        ("14h15", "table", "Investir dans la New defence : effet de mode ou tendance de fond ?", "Expansion, Eurazeo Tech, Aldoria et Rothschild & Co"),
        ("17h15", "temps-fort", "Remise du Prix de l'Innovation Duale", "Trois lauréats en Robotique, Santé et Deeptech"),
        ("18h15", "keynote", "Souveraineté technologique : le Dual-use au cœur de la nouvelle stratégie européenne d'innovation", "Stéphane Ouaki, European Innovation Council and SMEs Executive Agency (EISMEA)"),
    ]
    tf = "".join(f'<li data-heure="{h}"><span class="temps__grand" aria-hidden="true">{h[:2]}</span><span class="temps__heure">{h}</span><span class="badge badge--{t}">{TYPES[t]}</span><h3>{titre}</h3><p>{note}</p></li>' for n, (h, t, titre, note) in enumerate(temps))
    logos = bloc_logos([x for _, g in PARTENAIRES_2026 for x in g], "logos logos--6")
    cats = "".join(f'<li><span class="num">{n + 1:02d}</span><h3>{t}</h3><p>{d}</p></li>' for n, (t, d) in enumerate(CATEGORIES_PRIX))

    regle = "".join(f'<span style="left:{n / (len(temps) - 1) * 100:.1f}%">{h}</span>' for n, (h, *_ ) in enumerate(temps))
    portraits = [i for i in INTERVENANTS]
    rang = lambda l: "".join(f'<figure class="portrait"><img src="{i["photo"]}" alt="" width="168" height="168" loading="lazy"><figcaption><b>{escape(i["nom"])}</b><br>{escape(i["orga"])}</figcaption></figure>' for i in l)
    r1, r2 = portraits[::2], portraits[1::2]
    tous_logos = [x for _, g in PARTENAIRES_2026 for x in g] + [(n, f) for n, f in PARTENAIRES_PRIX if f and n not in ("Paris Ouest La Défense", "La Place Stratégique")]
    rang_logos = lambda l: "".join(f'<li>{logo_btn(n, f)}</li>' for n, f in l)
    moitie = len(tous_logos) // 2
    corps = f"""
<div class="intro" aria-hidden="true">
  <div class="intro__volet"></div>
  <div class="intro__in">
    <img class="intro__logo" src="assets/logo-dualtech.webp" alt="" width="340" height="48">
    <div class="intro__barre"><i></i></div>
    <div class="intro__info"><span>Campus Cyber · La Défense</span><span><span class="intro__pc">000</span> %</span></div>
  </div>
</div>
<section class="hero" data-video="{VIDEO_HERO}" aria-labelledby="titre-hero">
  <div class="hero__media" aria-hidden="true">
    <img class="hero__poster" src="assets/img/hero-poster.webp" alt="" width="1920" height="1080" fetchpriority="high">
  </div>
  <div class="hero__voile" aria-hidden="true"></div>
  <div class="hero__trame" aria-hidden="true"></div>
  <div class="hero__balayage" aria-hidden="true"></div>
  <div class="hud" aria-hidden="true"><i></i><i></i><i></i><i></i><span class="hud__coord"><b>●</b>48°53′ N · 2°14′ E · La Défense</span></div>
  <div class="wrap hero__contenu">
    <p class="hero__meta" data-reveal><span>Jeudi 26 novembre 2026</span><span class="point"></span><span>Campus Cyber</span><span class="point"></span><span>La Défense</span></p>
    <h1 id="titre-hero" class="lignes"><span class="l"><span style="--i:0">Le rendez-vous</span></span><span class="l"><span style="--i:1">incontournable de</span></span><span class="l"><span style="--i:2"><em>l'industrie duale</em></span></span></h1>
    <p class="hero__texte" data-reveal style="--d:.35s">Une journée pour réunir les acteurs publics, industriels, scientifiques et financiers qui construisent la souveraineté technologique française et européenne de demain.</p>
    <div class="actions" data-reveal style="--d:.45s">{btn("S'inscrire", lien("inscription"))}{btn("Devenir partenaire", lien("partenaires"), "ghost")}</div>
  </div>
  <div class="hero__bas">
    <p class="hero__ticker" aria-hidden="true"><span>Édition <b>02</b></span><span>Accès <b>sur accréditation</b></span><span><b>400</b> participants</span><span><b>20</b> filières duales</span></p>
    <div class="wrap hero__bas-in">
      <p class="hero__edition"><strong>Seconde édition</strong> organisée par l'Institut National des Affaires Stratégiques en partenariat avec l'établissement public territorial Paris Ouest La Défense.</p>
      {decompte_hero()}
    </div>
  </div>
  <span class="defiler" aria-hidden="true">Défiler</span>
</section>

<section class="orga orga--clair" aria-label="Organisateurs">
  <div class="wrap orga__in">
    <p class="orga__txt"><strong>DualTech 2026</strong> est porté par un think tank indépendant et par le territoire du premier quartier d'affaires européen.</p>
    <a class="orga__carte" href="https://inas-france.fr/" target="_blank" rel="noopener"><img src="assets/partenaires/inas.webp" alt="Institut National des Affaires Stratégiques (INAS)" width="104" height="47" loading="lazy"><span>Découvrir l'Institut National des Affaires Stratégiques (INAS)</span>{ico('sortie')}</a>
    <a class="orga__carte" href="https://www.parisouestladefense.fr/app/uploads/2026/07/Version-finale-Plaquette-marketing-territorial.pdf" target="_blank" rel="noopener"><img src="assets/partenaires/pold.webp" alt="Paris Ouest La Défense" width="104" height="52" loading="lazy"><span>Découvrir un territoire qui s'engage pour la souveraineté et l'innovation</span>{ico('sortie')}</a>
  </div>
</section>

<section class="sec sec--blanc" aria-labelledby="titre-manifeste" data-reseau>
  <div class="wrap manifeste">
    <div>
      <p class="surtitre" data-reveal>Filières industrielles représentées</p>
    </div>
    <div>
      <h2 id="titre-manifeste" class="manifeste__titre" data-forts="souveraineté|européenne|française">Promouvoir les acteurs de la souveraineté française et européenne</h2>
      <p class="lead" data-reveal style="margin-top:32px">Au travers de tables rondes, pitch sessions, expositions et ateliers, DualTech met en lumière, tout au long de la journée, les entreprises et industriels des filières stratégiques à double usage qui contribuent à la souveraineté industrielle et technologique.</p>
      <ul class="formats" data-reveal><li>Tables rondes</li><li>Keynotes</li><li>Pitch sessions</li><li>Expositions</li><li>Ateliers</li><li>Learning expeditions</li></ul>
    </div>
  </div>
</section>

<section class="cinema" aria-labelledby="titre-cinema">
  <div class="cinema__cadre">
    <video data-src="{MP4_AERO}" poster="assets/img/aero-poster.webp" muted loop playsinline preload="none" aria-hidden="true"></video>
  </div>
  <p class="cinema__badge" aria-hidden="true">Industrie duale · civil et défense</p>
  <div class="wrap cinema__texte">
    <h2 id="titre-cinema">Des technologies au service du civil et de la défense</h2>
    <p>L'industrie duale conçoit des technologies dont les applications servent à la fois les marchés civils et les besoins de défense et de sécurité. Le quantique, le spatial, la robotique, l'intelligence artificielle et les matériaux avancés en sont les principales filières.</p>
  </div>
</section>

<div class="bandeau bandeau--bleu" aria-hidden="true"><div class="bandeau__piste">{bande}{bande}</div></div>

<section class="sec sec--papier" aria-labelledby="titre-filieres">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Double usage</p>
        {lignes("Les filières", "stratégiques à l'honneur", attrs=' id="titre-filieres"')}
      </div>
      <div class="filieres__compteur" data-reveal style="margin:0"><b class="chiffre"><span data-compte="20">20</span></b><span>filières civiles et de défense réunies au Campus Cyber. Survolez un secteur pour le découvrir.</span></div>
    </div>
    <ol class="secteurs">{filieres}</ol>
  </div>
</section>

<section class="sec sec--nuit chiffres-sec" aria-labelledby="titre-chiffres">
  <div class="video-fond" aria-hidden="true"><video data-src="{MP4_RESEAU}" poster="assets/img/fond-reseau-poster.webp" muted loop playsinline preload="none"></video></div>
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Édition 2026</p>
        {lignes("Une journée unique au cœur", "du deuxième quartier d'affaires", "le plus attractif en Europe", attrs=' id="titre-chiffres"')}
      </div>
      <p class="lead" data-reveal>Décideurs publics, industriels, chercheurs et investisseurs réunis le temps d'une journée, sur accréditation, à La Défense.</p>
    </div>
    <ul class="chiffres">{chiffres}</ul>
  </div>
</section>

<section class="sec sec--blanc horizon-sec" aria-labelledby="titre-temps">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Programme du 26 novembre</p>
        {lignes("Les temps forts", "de la journée", attrs=' id="titre-temps"')}
      </div>
      <div data-reveal>
        <p class="lead">Tables rondes, keynotes, sessions de pitch, espace exposants et learning expeditions rythmeront cette journée entièrement consacrée à l'innovation duale et à la souveraineté technologique.</p>
        <a class="lien-fleche" href="{lien('programme')}" style="margin-top:16px">Voir le programme complet{ico('sortie')}</a>
      </div>
    </div>
    <div class="horizon__regle" aria-hidden="true">{regle}<i class="horizon__progres"></i></div>
  </div>
  <div class="horizon"><div class="wrap" style="display:flex;gap:40px;align-items:flex-start"><p class="horizon__horloge" aria-hidden="true">{temps[0][0]}</p><ul class="temps">{tf}</ul></div></div>
</section>

<section class="sec sec--nuit" aria-labelledby="titre-orateurs">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Speakers 2026</p>
        {lignes("Ils prendront", "la parole", attrs=' id="titre-orateurs"')}
      </div>
      <div data-reveal>
        <p class="lead">Dirigeants industriels, investisseurs, élus et responsables publics : plus de quarante intervenants et animateurs se succéderont sur scène.</p>
        <a class="lien-fleche" href="{lien('intervenants')}" style="margin-top:16px">Voir les {len(INTERVENANTS)} intervenants{ico('sortie')}</a>
      </div>
    </div>
    <ul class="orateurs orateurs--6">{orateurs}</ul>
  </div>
  <div class="defile-portraits" aria-hidden="true"><div class="defile-portraits__rang">{rang(r1)}{rang(r1)}</div><div class="defile-portraits__rang defile-portraits__rang--inverse">{rang(r2)}{rang(r2)}</div></div>
</section>

<section class="sec sec--papier" aria-labelledby="titre-profils">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Participer</p>
        {lignes("Intérêt de la participation", "pour les acteurs de l'écosystème", attrs=' id="titre-profils"')}
      </div>
      <div class="actions" data-reveal>{btn("S'inscrire", lien("inscription"))}{btn("Infos pratiques", lien("infos"), "ghost", None)}</div>
    </div>
    <div class="profils">{profils}</div>
  </div>
</section>
{EXPERIENCE}

<section class="sec sec--nuit" aria-labelledby="titre-prix">
  <div class="wrap">
    <div class="prix-teaser" data-reveal><div class="video-fond prix-teaser__video" aria-hidden="true"><video data-src="{MP4_PRIX}" poster="assets/img/prix-poster.webp" muted loop playsinline preload="none"></video></div><svg class="medaille" viewBox="0 0 88 88" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><g class="rayons"><circle cx="44" cy="44" r="42" stroke-dasharray="2 5"/><circle cx="44" cy="44" r="36" stroke-opacity=".5"/></g><circle cx="44" cy="44" r="27"/><path d="M44 29l4.4 9 9.9 1.4-7.2 7 1.7 9.8L44 51.6l-8.8 4.6 1.7-9.8-7.2-7 9.9-1.4Z" fill="currentColor" fill-opacity=".18"/></svg>
      <div class="theme-or">
        <p class="surtitre surtitre--or">Prix de l'innovation duale · Édition 2026</p>
        {lignes("Faire émerger les", "innovations stratégiques", "de demain.", attrs=' id="titre-prix"')}
        <p class="lead">Porté par Paris Ouest La Défense, le Prix identifie, valorise et accompagne les entreprises innovantes développant des technologies à fort potentiel de double usage.</p>
        <div class="dotation"><b class="chiffre reflet">5 000 €</b><span>par prix, remis à l'occasion de DualTech 2026</span></div>
        <div class="actions" style="margin-top:32px">{btn("Découvrir le Prix", lien("prix"), "or")}</div>
      </div>
      <ul class="cats">{cats}</ul>
    </div>
  </div>
</section>

<section class="sec sec--papier" aria-labelledby="titre-partenaires">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Écosystème</p>
        {lignes("Ils soutiennent", "DualTech", attrs=' id="titre-partenaires"')}
      </div>
      <div data-reveal>
        <p class="lead">Institutions publiques, acteurs du financement, organisations industrielles, entreprises et médias spécialisés accompagnent DualTech.</p>
        <a class="lien-fleche" href="{lien('partenaires')}" style="margin-top:16px">Associer votre organisation{ico('sortie')}</a>
      </div>
    </div>
    <div class="logos-defile" data-reveal><ul class="logos-defile__rang">{rang_logos(tous_logos[:moitie])}{rang_logos(tous_logos[:moitie])}</ul><ul class="logos-defile__rang logos-defile__rang--inverse">{rang_logos(tous_logos[moitie:])}{rang_logos(tous_logos[moitie:])}</ul></div>
  </div>
</section>

<section class="sec" aria-labelledby="titre-lieu">
  <div class="wrap">
    {bloc_lieu(texte="La seconde édition de DualTech se déroulera au sein du Campus Cyber, lieu totem de la cybersécurité fédérant un écosystème composé de plus de 250 acteurs publics et privés et favorisant les collaborations ainsi que les synergies innovantes. Grâce à ses initiatives et à son rayonnement, le Campus Cyber s'impose comme un centre névralgique national et européen, contribuant à accélérer le développement de solutions face aux défis à venir, tant en matière de cybersécurité que d'intelligence artificielle.").replace('<h2 class="h2 lignes">', '<h2 class="h2 lignes" id="titre-lieu">', 1)}
  </div>
</section>

<section class="sec sec--blanc" aria-labelledby="titre-faq">
  <div class="wrap deux-col deux-col--5-7">
    <div class="collant">
      <p class="surtitre" data-reveal>Questions fréquentes</p>
      {lignes("Ce qu'il faut savoir", "avant de venir", attrs=' id="titre-faq"')}
      <a class="lien-fleche" href="{lien('infos', '#faq')}" style="margin-top:24px" data-reveal>Toutes les questions pratiques{ico('sortie')}</a>
    </div>
    {bloc_faq(FAQ_ACCUEIL)}
  </div>
</section>

{bloc_final()}
"""
    graphe = BASE + [EVENEMENT, webpage("accueil", "DualTech 2026 : le rendez-vous de l'industrie duale", extra={"about": {"@id": f"{SITE}/#dualtech-2026"}, "primaryImageOfPage": f"{SITE}/wp-content/themes/dualtech/assets/img/og-dualtech-2026.jpg"})]
    graphe[-1].pop("breadcrumb")
    ecrire("accueil", "DualTech 2026 : salon de l'industrie duale | La Défense",
           "Le 26 novembre 2026 au Campus Cyber, DualTech réunit décideurs publics, industriels et investisseurs de l'industrie duale. Demandez votre accréditation.",
           graphe, corps, POSTER)


# ====================================================================
# PROGRAMME
# ====================================================================
def interv_html(nom):
    i = PAR_NOM.get(nom)
    if not i:
        return f'<div class="interv"><span><b>{escape(nom)}</b></span></div>'
    return f'<div class="interv"><img src="{i["photo"]}" alt="" width="44" height="44" loading="lazy"><span><b>{escape(nom)}</b><span>{escape(i["fonction"])}, {escape(i["orga"])}</span></span></div>'


def programme():
    total = sum(len(c) for _, c in PROGRAMME)
    comptes = {k: sum(1 for _, c in PROGRAMME for x in c if (x[2] if x[2] != "temps-fort" else "autres") == k) for k, _ in FILTRES[1:]}
    comptes["tous"] = total
    filtres = "".join(f'<button type="button" data-filtre="{k}" aria-pressed="{"true" if k == "tous" else "false"}">{t}<span class="n">{comptes[k]}</span></button>' for k, t in FILTRES)
    parties = []
    for nom, creneaux in PROGRAMME:
        items = []
        for d, f, t, titre, note, intervs, modos in creneaux:
            filtre = "autres" if t == "temps-fort" else t
            leger = " creneau--leger" if t == "networking" else ""
            iv = f'<div class="intervs">{"".join(interv_html(n) for n in intervs)}</div>' if intervs else ""
            md = f'<div class="moderation"><p>Modération</p><div class="intervs">{"".join(interv_html(n) for n in modos)}</div></div>' if modos else ""
            nt = f'<p class="creneau__note">{note}</p>' if note else ""
            items.append(f"""<article class="creneau{leger}" data-type="{filtre}">
  <p class="creneau__heure chiffre"><time datetime="2026-11-26T{d.replace('h', ':')}">{d}</time><span>{f}</span></p>
  <div><span class="badge badge--{t}">{TYPES[t]}</span><h3>{titre}</h3>{nt}{iv}{md}</div>
</article>""")
        parties.append(f"""<div class="partie">
  <div class="partie__titre"><h2>{nom}</h2><p>{creneaux[0][0]} à {creneaux[-1][1]}</p></div>
  <div class="creneaux"><span class="creneaux__fil" aria-hidden="true"></span>{"".join(items)}</div>
</div>""")
    meta = """<dl class="phero__meta" data-reveal style="--d:.45s"><div><dt>Date</dt><dd>Jeudi 26 novembre 2026</dd></div><div><dt>Horaires</dt><dd>8h30 à 19h00</dd></div><div><dt>Lieu</dt><dd>Campus Cyber, La Défense</dd></div></dl>"""
    corps = phero([("accueil", "Accueil"), (None, "Programme 2026")], "26 novembre 2026 · Campus Cyber", ["Le programme", "de la journée"],
                  "Tables rondes, keynotes, sessions de pitch, espace exposants et learning expeditions rythmeront cette journée entièrement consacrée à l'innovation duale et à la souveraineté technologique.",
                  btn("S'inscrire", lien("inscription")) + btn("Voir les intervenants", lien("intervenants"), "ghost"),
                  '<div class="phero__fond" aria-hidden="true"><img src="assets/photos/conference-keynote.webp" alt="" width="1600" height="1067"></div>', meta)
    corps += f"""
<section class="sec sec--blanc" aria-labelledby="titre-journee">
  <div class="wrap">
    <div class="tete-sec">
      <p class="surtitre" data-reveal>Programme de la journée</p>
      {lignes("Jeudi 26 novembre 2026", attrs=' id="titre-journee"')}
    </div>
    <div class="filtres" role="group" aria-label="Filtrer le programme par format">{filtres}</div>
    <p class="sr-only" id="programme-statut" aria-live="polite"></p>
    <p class="vide" id="programme-vide" hidden>Aucun événement ou speaker ne correspond à votre recherche.</p>
    <div class="journee">{"".join(parties)}</div>
  </div>
</section>

<section class="sec" aria-labelledby="titre-lieu">
  <div class="wrap">{bloc_lieu(carte=False).replace('<h2 class="h2 lignes">', '<h2 class="h2 lignes" id="titre-lieu">', 1)}</div>
</section>
{bloc_final()}"""
    ev = {"@type": "Event", "@id": f"{SITE}/#dualtech-2026", "name": "DualTech 2026", "subEvent": []}
    for _, creneaux in PROGRAMME:
        for d, f, t, titre, note, intervs, modos in creneaux:
            if t in ("keynote", "table", "temps-fort"):
                se = {"@type": "Event", "name": titre.replace("« ", "").replace(" »", ""), "startDate": f"2026-11-26T{d.replace('h', ':')}:00+01:00", "endDate": f"2026-11-26T{f.replace('h', ':')}:00+01:00",
                      "location": {"@id": f"{SITE}/#campus-cyber"}, "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode", "eventStatus": "https://schema.org/EventScheduled"}
                if intervs:
                    se["performer"] = [{"@type": "Person", "name": n} for n in intervs]
                ev["subEvent"].append(se)
    graphe = BASE + [ev, webpage("programme", "Programme DualTech 2026", extra={"about": {"@id": f"{SITE}/#dualtech-2026"}}), ariane_ld("programme", "Programme 2026")]
    ecrire("programme", "Programme DualTech 2026 : keynotes et tables rondes",
           "Programme du 26 novembre 2026 au Campus Cyber : keynotes, tables rondes, workshops et remise du Prix de l'innovation duale, de 8h30 à 19h.", graphe, corps)


# ====================================================================
# INTERVENANTS
# ====================================================================
def intervenants():
    import json as _json
    lib = dict(UNIVERS)
    def carte(i, n):
        sess = [{"h": f"{d} à {f}", "t": titre, "r": role} for d, f, t, titre, role in i["sessions"]]
        prem = i["sessions"][0] if i["sessions"] else None
        voile = f'<small>{prem[0]} · {TYPES[prem[2]]}</small><span>{escape(prem[3])}</span>' if prem else '<small>Intervenant 2026</small>'
        chips = "".join(f'<span class="iv-chip iv-chip--{t}"><b>{d}</b>{TYPES[t]}</span>' for d, f, t, *_ in i["sessions"]) or '<span class="iv-chip"><b>2026</b>Horaire à venir</span>'
        cherche = escape(slug(f"{i['nom']} {i['fonction']} {i['orga']}").replace("-", " "))
        return (f'<article class="iv-carte" data-univers="{i["univers"]}" data-cherche="{cherche}" data-nom="{escape(i["nom"])}" data-fonction="{escape(i["fonction"])}" '
                f'data-orga="{escape(i["orga"])}" data-photo="{i["photo"]}" data-sessions="{escape(_json.dumps(sess, ensure_ascii=False))}" data-incline>'
                f'<button type="button" class="iv-carte__ouvrir" aria-label="Voir la fiche de {escape(i["nom"])}"></button>'
                f'<div class="iv-carte__photo"><img src="{i["photo"]}" alt="" width="480" height="480" loading="lazy"><span class="iv-carte__univers">{lib[i["univers"]]}</span>'
                f'<span class="iv-carte__plus" aria-hidden="true">{ico("fleche")}</span><span class="iv-carte__voile" aria-hidden="true">{voile}</span></div>'
                f'<div class="iv-carte__texte"><span class="iv-carte__num">{n:02d}</span><h2>{escape(i["nom"])}</h2><p>{escape(i["fonction"])}<strong>{escape(i["orga"])}</strong></p></div><div class="iv-carte__passages">{chips}</div><span class="iv-carte__fleche" aria-hidden="true">{ico("fleche")}</span></article>')
    cartes = "".join(carte(i, n + 1) for n, i in enumerate(INTERVENANTS))
    comptes = {k: sum(1 for i in INTERVENANTS if k == "tous" or i["univers"] == k) for k, _ in UNIVERS}
    filtres = "".join(f'<button type="button" data-univers="{k}" aria-pressed="{"true" if k == "tous" else "false"}">{t}<span class="n">{comptes[k]}</span></button>' for k, t in UNIVERS)
    mosaique = "".join(f'<img src="{i["photo"]}" alt="" width="120" height="120" loading="lazy">' for i in (INTERVENANTS * 2)[:30])
    nb_keynotes = sum(1 for _, c in PROGRAMME for x in c if x[2] == "keynote")
    nb_tables = sum(1 for _, c in PROGRAMME for x in c if x[2] == "table")
    corps = f"""
<section class="iv-hero" data-reseau>
  <div class="phero__grille-deco" aria-hidden="true"></div>
  <div class="wrap iv-hero__grille">
    <div>
      <nav aria-label="Fil d'Ariane"><ol class="ariane"><li><a href="{lien('accueil')}">Accueil</a></li><li aria-current="page">Intervenants</li></ol></nav>
      <p class="surtitre" data-reveal>26 novembre 2026 · Campus Cyber</p>
      <h1 class="lignes"><span class="l"><span style="--i:0">Speakers <b>2026</b></span></span></h1>
      <p class="lead" data-reveal style="margin-top:24px;color:var(--brume)">Dirigeants industriels, investisseurs, responsables publics et experts de l'INAS : découvrez les personnalités qui interviendront lors de la seconde édition de DualTech, et retrouvez l'heure de leur passage.</p>
      <div class="actions" data-reveal style="margin-top:32px">{btn("Voir le programme", lien("programme"))}{btn("S'inscrire", lien("inscription"), "ghost")}</div>
      <div class="iv-stats" data-reveal><div><b><span data-compte="{len(INTERVENANTS)}">{len(INTERVENANTS)}</span></b><span>intervenants</span></div><div><b><span data-compte="{nb_keynotes}">{nb_keynotes}</span></b><span>keynotes</span></div><div><b><span data-compte="{nb_tables}">{nb_tables}</span></b><span>tables rondes</span></div></div>
    </div>
    <div class="iv-mosaique" aria-hidden="true">{mosaique}</div>
  </div>
</section>

<div class="iv-barre">
  <div class="wrap iv-barre__in">
    <div class="filtres" role="group" aria-label="Filtrer les intervenants par univers">{filtres}</div>
    <div style="display:flex;gap:12px;align-items:center;flex:1 1 320px;justify-content:flex-end">
      <div class="recherche">
        <label class="sr-only" for="iv-recherche">Rechercher un intervenant, une fonction ou une organisation</label>
        {ico('loupe')}
        <input id="iv-recherche" type="search" placeholder="Rechercher" autocomplete="off">
        <button type="button" id="iv-effacer" aria-label="Effacer la recherche">{ico('x')}</button>
      </div>
      <div class="vues" role="group" aria-label="Affichage"><button type="button" data-vue="grille" aria-pressed="true" aria-label="Afficher en grille"><svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M104 40H56a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16Zm0 64H56V56h48Zm96-64h-48a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16Zm0 64h-48V56h48Zm-96 32H56a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16v-48a16 16 0 0 0-16-16Zm0 64H56v-48h48Zm96-64h-48a16 16 0 0 0-16 16v48a16 16 0 0 0 16 16h48a16 16 0 0 0 16-16v-48a16 16 0 0 0-16-16Zm0 64h-48v-48h48Z"/></svg></button><button type="button" data-vue="liste" aria-pressed="false" aria-label="Afficher en liste"><svg viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M224 128a8 8 0 0 1-8 8H40a8 8 0 0 1 0-16h176a8 8 0 0 1 8 8ZM40 72h176a8 8 0 0 0 0-16H40a8 8 0 0 0 0 16Zm176 112H40a8 8 0 0 0 0 16h176a8 8 0 0 0 0-16Z"/></svg></button></div>
    </div>
  </div>
</div>

<section class="sec sec--blanc" aria-label="Liste des intervenants" style="padding-top:56px">
  <div class="wrap">
    <p class="secondaire" id="iv-statut" aria-live="polite" style="margin-bottom:24px">{len(INTERVENANTS)} intervenants</p>
    <div class="iv-entete" aria-hidden="true"><span>Intervenant</span><span>Fonction et organisation</span><span>Passage sur scène</span></div>
    <div class="iv-grille" id="iv-grille">{cartes}</div>
    <p class="vide" id="iv-vide" hidden>Aucun intervenant ne correspond à votre recherche.</p>
  </div>
</section>

<dialog class="fiche fiche--iv" id="fiche-iv" aria-labelledby="fiche-iv-nom">
  <button class="fiche__fermer" type="button" aria-label="Fermer la fiche">{ico('x')}</button>
  <div class="fiche__grille">
    <div class="fiche__photo"><img src="" alt="" width="480" height="480"></div>
    <div class="fiche__corps">
      <p class="fiche__cat"></p>
      <h2 id="fiche-iv-nom"></h2>
      <p class="fiche__fonction"></p>
      <ul class="fiche__sessions"></ul>
      <div class="fiche__nav"><button type="button" data-iv-pas="-1">Intervenant précédent</button><a class="lien-fleche" href="{lien('programme')}">Programme complet{ico('fleche')}</a><button type="button" data-iv-pas="1">Intervenant suivant</button></div>
    </div>
  </div>
</dialog>
{bloc_final()}"""
    liste = {"@type": "ItemList", "name": "Intervenants DualTech 2026", "numberOfItems": len(INTERVENANTS),
             "itemListElement": [{"@type": "ListItem", "position": n + 1, "item": {"@type": "Person", "name": i["nom"], "jobTitle": i["fonction"], "worksFor": {"@type": "Organization", "name": i["orga"]}, "image": f"{SITE}/wp-content/themes/dualtech/{i['photo']}"}} for n, i in enumerate(INTERVENANTS)]}
    graphe = BASE + [webpage("intervenants", "Intervenants DualTech 2026", "CollectionPage", {"mainEntity": liste}), ariane_ld("intervenants", "Intervenants")]
    ecrire("intervenants", "Intervenants DualTech 2026 : 34 speakers défense et industrie",
           "Euronext, ONERA, Thales, MBDA, Eurazeo, EISMEA : découvrez les 34 intervenants de DualTech 2026, le 26 novembre au Campus Cyber de La Défense.", graphe, corps)


# ====================================================================
# WORKSHOPS
# ====================================================================
def workshops():
    fond = f'<div class="phero__fond" aria-hidden="true" data-video-fond="{VIDEO_WORKSHOPS}" data-fin="38"><img src="assets/img/workshops-poster.webp" alt="" width="1600" height="900"></div>'
    corps = phero([("accueil", "Accueil"), (None, "Ateliers & Workshops")], "Workshops", ["Organiser un workshop", "lors de DualTech"],
                  "Partagez votre expertise et proposez un atelier consacré aux enjeux de l'innovation duale. DualTech invite les organisations à contribuer à son programme en proposant des formats favorisant les échanges et les retours d'expérience.",
                  btn("Proposer un workshop", "#formulaire"), fond)
    corps += f"""
<section class="sec" aria-labelledby="titre-expertise">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Contribuer au programme</p>
        {lignes("Partagez", "votre expertise", attrs=' id="titre-expertise"')}
      </div>
      <p class="lead" data-reveal>Les workshops permettent d'approfondir un sujet, de partager des expériences et de faire dialoguer les acteurs de l'innovation duale. Vous pouvez proposer une thématique ou construire votre projet avec l'équipe DualTech.</p>
    </div>
    <div class="cartes cartes--2">
      <article class="carte-b projecteur faisceau" data-reveal>
        <span class="etiq">Proposer un sujet</span>
        <h3 class="h3">Vous avez une thématique</h3>
        <p>Votre organisation souhaite partager une expertise ou ouvrir une discussion sur un enjeu identifié ? Présentez le sujet que vous souhaitez aborder lors de votre workshop.</p>
        <ul class="puces puces--check"><li>Partager une expertise en lien avec l'innovation duale</li><li>Approfondir un besoin opérationnel ou un enjeu industriel et technologique</li><li>Favoriser les échanges autour de retours d'expérience et de cas concrets</li></ul>
        <a class="lien-fleche" href="#formulaire">Proposer un workshop{ico('fleche')}</a>
      </article>
      <article class="carte-b projecteur faisceau" data-reveal style="--d:.1s">
        <span class="etiq">Construire un atelier</span>
        <h3 class="h3">Votre projet reste à préciser</h3>
        <p>Vous souhaitez contribuer à DualTech sans avoir encore arrêté le sujet de votre atelier ? Manifestez votre intérêt pour engager un échange avec notre équipe.</p>
        <ul class="puces puces--check"><li>Identifier une thématique en lien avec votre domaine d'expertise</li><li>Préciser les objectifs et le format envisagés pour l'atelier</li><li>Étudier son articulation avec le programme de DualTech</li></ul>
        <a class="lien-fleche" href="#formulaire">Nous contacter{ico('fleche')}</a>
      </article>
    </div>
  </div>
</section>

<section class="sec sec--papier" id="formulaire" aria-labelledby="titre-form">
  <div class="wrap deux-col deux-col--5-7">
    <div class="collant">
      <p class="surtitre" data-reveal>Formulaire</p>
      {lignes("Proposez", "votre workshop*", attrs=' id="titre-form"')}
      <p class="lead" data-reveal style="margin-top:24px">Vous souhaitez organiser un workshop lors de DualTech ? Renseignez vos coordonnées et indiquez la thématique envisagée. Si votre sujet reste à définir, cochez simplement la case prévue. Votre proposition permettra d'engager un échange avec notre équipe.</p>
      <p class="mention" data-reveal style="margin-top:20px">* L'organisation d'un workshop est soumise à une tarification, dont les modalités vous seront communiquées lors des échanges avec l'équipe DualTech.</p>
      <p data-reveal style="margin-top:28px"><span class="secondaire">Une question ?</span><br><a class="lien-fleche" href="mailto:workshops@dualtech-event.eu">{ico('mail')}workshops@dualtech-event.eu</a></p>
    </div>
    <div class="panneau-form" data-reveal>
      <form class="form form--2" action="https://formsubmit.co/workshops@dualtech-event.eu" method="post">
        <p class="mention plein">Les champs marqués d'un astérisque sont obligatoires.</p>
        <div class="champ"><label for="w-prenom">Prénom <span class="req">*</span></label><input id="w-prenom" name="prenom" autocomplete="given-name" required></div>
        <div class="champ"><label for="w-nom">Nom <span class="req">*</span></label><input id="w-nom" name="nom" autocomplete="family-name" required></div>
        <div class="champ"><label for="w-tel">Numéro de téléphone <span class="req">*</span></label><input id="w-tel" name="telephone" type="tel" autocomplete="tel" required></div>
        <div class="champ"><label for="w-mail">Adresse e-mail <span class="req">*</span></label><input id="w-mail" name="email" type="email" autocomplete="email" required></div>
        <div class="champ plein"><label for="w-structure">Nom de la structure <span class="req">*</span></label><input id="w-structure" name="structure" autocomplete="organization" required></div>
        <div class="champ plein"><label for="w-theme">Thématique du workshop proposé <span class="req">*</span></label><textarea id="w-theme" name="thematique" aria-describedby="w-theme-aide"></textarea><span class="aide" id="w-theme-aide">Renseignez une thématique ou cochez la case ci-dessous.</span></div>
        <label class="case plein"><input type="checkbox" name="thematique_a_definir" value="oui"> Je n'ai pas encore défini la thématique.</label>
        <div class="plein"><button class="btn btn--primaire" type="submit">Envoyer la demande{ico('fleche')}</button></div>
        <p class="mention plein">En envoyant ce formulaire, vous acceptez d'être recontacté par l'équipe DualTech au sujet de votre demande.</p>
      </form>
    </div>
  </div>
</section>
{bloc_final()}"""
    graphe = BASE + [webpage("workshops", "Ateliers et workshops DualTech 2026"), ariane_ld("workshops", "Ateliers & Workshops")]
    ecrire("workshops", "Workshops DualTech 2026 : proposer un atelier dual",
           "Proposez un workshop lors de DualTech 2026 au Campus Cyber : partagez votre expertise de l'innovation duale avec industriels, investisseurs et institutions.", graphe, corps)


# ====================================================================
# PARTENAIRES
# ====================================================================
def partenaires():
    groupes = "".join(f'<div class="logos-groupe" data-reveal><h3>{g}</h3>{bloc_logos(l, cat=g)}</div>' for g, l in PARTENAIRES_2026)
    meta = """<dl class="phero__meta" data-reveal style="--d:.45s"><div><dt>Participants</dt><dd>400 attendus sur accréditation</dd></div><div><dt>Intervenants</dt><dd>40 publics, industriels et financiers</dd></div><div><dt>Filières</dt><dd>+20 représentées</dd></div></dl>"""
    corps = phero([("accueil", "Accueil"), (None, "Devenir partenaire")], "Partenariats 2026", ["Associez votre", "organisation à DualTech"],
                  "Rejoignez les institutions, industriels et investisseurs qui font de DualTech un rendez-vous industriel de haut niveau. Deux niveaux de partenariat existent, du soutien institutionnel à la prise de parole en plénière.",
                  btn("Recevoir le dossier de partenariat", "#demande") + btn("Voir les partenaires actuels", "#partenaires", "ghost"),
                  '<div class="phero__fond" aria-hidden="true"><img src="assets/photos/partenariat-poignee-de-main.webp" alt="" width="1600" height="1067"></div>', meta)
    corps += f"""
<section class="sec sec--blanc" id="partenaires" aria-labelledby="titre-part">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Écosystème</p>
        {lignes("Nos partenaires", attrs=' id="titre-part"')}
      </div>
      <p class="lead" data-reveal>Institutions publiques, acteurs du financement, organisations industrielles, entreprises et médias spécialisés accompagnent DualTech.</p>
    </div>
    <div class="logos-groupes">{groupes}</div>
  </div>
</section>

<section class="sec" aria-labelledby="titre-modalites">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Partenariats</p>
        {lignes("Deux modalités", "de partenariat", attrs=' id="titre-modalites"')}
      </div>
      <p class="lead" data-reveal>DualTech s'appuie sur un réseau de partenaires institutionnels et médias associés à la préparation, à la visibilité et au rayonnement de l'événement. Deux modalités de partenariat sont proposées selon la nature et le niveau d'implication de chaque organisation.</p>
    </div>
    <div class="cartes cartes--2">
      <article class="carte-b projecteur faisceau" data-reveal>
        <span class="etiq">Institutionnel</span>
        <h3 class="h3">Partenaire institutionnel</h3>
        <p>Le partenariat institutionnel associe votre organisation à DualTech et permet de valoriser son engagement au sein de l'écosystème industriel, technologique et institutionnel réuni à l'occasion de l'événement.</p>
        <ul class="puces puces--check"><li>Présence du logo de votre organisation sur le site internet et les principaux supports de communication de DualTech</li><li>Mise en lumière de votre organisation dans les communications institutionnelles relatives à l'événement</li><li>Association de votre organisation à l'écosystème de partenaires mobilisés autour de DualTech</li><li>Implication dans l'événement et, selon les modalités convenues, dans certaines séquences de son programme</li></ul>
      </article>
      <article class="carte-b projecteur faisceau" data-reveal style="--d:.1s">
        <span class="etiq">Média</span>
        <h3 class="h3">Partenaire média</h3>
        <p>Le partenariat média vise à associer des médias de référence à la diffusion de DualTech et à renforcer la visibilité de l'événement auprès des publics concernés par ses thématiques.</p>
        <ul class="puces puces--check"><li>Présence du logo du média sur le site internet et les supports de communication dédiés aux partenaires</li><li>Identification en qualité de partenaire média de DualTech</li><li>Relais des principales communications relatives à l'événement sur les canaux du média partenaire</li><li>Contribution à la diffusion et au rayonnement de l'événement auprès de son audience et de son écosystème</li></ul>
      </article>
    </div>
  </div>
</section>

<section class="sec sec--papier" id="demande" aria-labelledby="titre-demande">
  <div class="wrap deux-col deux-col--5-7">
    <div class="collant">
      <p class="surtitre" data-reveal>Partenariats</p>
      {lignes("Soumettre une demande", "de partenariat", attrs=' id="titre-demande"')}
      <p class="lead" data-reveal style="margin-top:24px">Les organisations souhaitant s'associer à DualTech sont invitées à renseigner le formulaire ci-contre. Chaque demande fait l'objet d'un examen par l'équipe en charge des partenariats, qui prendra contact avec votre organisation afin de préciser les modalités de collaboration.</p>
      <ul class="puces puces--check" data-reveal style="margin-top:28px"><li>Examen individualisé de chaque demande de partenariat</li><li>Échange avec l'équipe DualTech afin de définir les modalités de collaboration</li><li>Traitement confidentiel des informations communiquées</li></ul>
    </div>
    <div class="panneau-form" data-reveal>
      <form class="form form--2" action="https://formsubmit.co/contact@dualtech-event.eu" method="post">
        <input type="hidden" name="_subject" value="Demande de partenariat DualTech 2026">
        <div class="champ"><label for="p-nom">Nom et prénom <span class="req">*</span></label><input id="p-nom" name="nom" autocomplete="name" required></div>
        <div class="champ"><label for="p-orga">Organisation <span class="req">*</span></label><input id="p-orga" name="organisation" autocomplete="organization" required></div>
        <div class="champ"><label for="p-mail">Adresse e-mail professionnelle <span class="req">*</span></label><input id="p-mail" name="email" type="email" autocomplete="email" required></div>
        <div class="champ"><label for="p-tel">Téléphone</label><input id="p-tel" name="telephone" type="tel" autocomplete="tel"></div>
        <div class="champ plein"><label for="p-type">Nature du partenariat envisagé <span class="req">*</span></label><select id="p-type" name="nature" required><option value="">Sélectionner une modalité</option><option>Partenaire institutionnel</option><option>Partenaire média</option><option>À préciser avec l'équipe DualTech</option></select></div>
        <div class="champ plein"><label for="p-msg">Présentation de votre demande</label><textarea id="p-msg" name="message"></textarea></div>
        <div class="plein"><button class="btn btn--primaire" type="submit">Transmettre ma demande{ico('fleche')}</button></div>
        <p class="mention plein">Les informations transmises sont utilisées exclusivement dans le cadre de l'étude et du suivi de votre demande de partenariat avec DualTech.</p>
      </form>
    </div>
  </div>
</section>
{bloc_final()}"""
    graphe = BASE + [webpage("partenaires", "Devenir partenaire de DualTech 2026", extra={"about": {"@id": f"{SITE}/#dualtech-2026"}}), ariane_ld("partenaires", "Devenir partenaire")]
    ecrire("partenaires", "Devenir partenaire DualTech 2026 | Industrie duale",
           "Associez votre organisation à DualTech 2026 : partenariat institutionnel ou média, 400 décideurs et 40 intervenants au Campus Cyber. Recevez le dossier.", graphe, corps)


# ====================================================================
# PRIX DE L'INNOVATION DUALE
# ====================================================================
def prix():
    cats = "".join(f'<article class="carte-b projecteur faisceau" data-reveal style="--d:{n * .08}s"><span class="etiq" style="color:var(--or-texte)">{n + 1:02d}</span><h3 class="h3">{t}</h3><p>{d}</p><a class="lien-fleche" href="{CANDIDATURE_PRIX}" target="_blank" rel="noopener" style="color:var(--or-texte)">Candidater{ico("sortie")}</a></article>' for n, (t, d) in enumerate(CATEGORIES_PRIX))
    avantages = [
        ("01 · Soutien financier", "Une dotation pour soutenir le développement du projet", "Chaque lauréat recevra une dotation de 5 000 euros destinée à contribuer au développement de son innovation et à la poursuite de ses prochaines étapes."),
        ("02 · Écosystème", "Un accès privilégié aux réseaux partenaires", "Les lauréats bénéficieront de mises en relation avec les écosystèmes de Paris Ouest La Défense, de l'INAS et de La Place Stratégique, afin de faciliter les connexions avec les acteurs pertinents pour leur développement."),
        ("03 · Rencontres", "Des échanges avec les décideurs et acteurs du territoire", "Des rencontres pourront être organisées avec les collectivités du territoire, les membres du jury, les partenaires du Prix et les acteurs économiques ou institutionnels susceptibles d'accompagner le développement des projets sélectionnés."),
        ("04 · Visibilité", "Un pack média dédié aux lauréats", "Chaque lauréat bénéficiera d'un dispositif de valorisation comprenant une captation vidéo, un shooting photo et une mise en visibilité à l'occasion d'événements stratégiques."),
    ]
    av = "".join(f'<li data-reveal><span class="num">{a}</span><h3>{b}</h3><p>{c}</p></li>' for a, b, c in avantages)
    av += '<li class="plein" data-reveal><span class="num">+ · Accompagnement</span><h3>Un parcours adapté à la maturité de chaque projet.</h3><p>Les modalités d\'accompagnement seront définies avec chaque lauréat en fonction de son niveau de maturité, de ses besoins et de ses perspectives de développement, afin de mobiliser les dispositifs et interlocuteurs les plus pertinents.</p></li>'
    onglets = "".join(f'<button type="button" role="tab" id="etape-{n}" aria-controls="etape-p-{n}" aria-selected="{"true" if n == 1 else "false"}" tabindex="{0 if n == 1 else -1}"><span class="n">{n + 1:02d} / 06</span><span class="q">{q}</span><span class="t">{t}</span></button>' for n, (q, t, *_ ) in enumerate(CALENDRIER_PRIX))
    panneaux = "".join(f'<div class="calendrier__panneau" role="tabpanel" id="etape-p-{n}" aria-labelledby="etape-{n}"{"" if n == 1 else " hidden"}><p class="surtitre surtitre--or" style="margin:0">{d}</p><h3>{t}</h3><p>{x}</p></div>' for n, (_, _, d, t, x) in enumerate(CALENDRIER_PRIX))
    meta = """<dl class="phero__meta" data-reveal style="--d:.45s"><div><dt>Dotation</dt><dd>5 000 € par prix</dd></div><div><dt>Catégories</dt><dd>Robotique · Santé · Deeptech</dd></div><div><dt>Remise des prix</dt><dd>Novembre 2026, à DualTech</dd></div></dl>"""
    corps = phero([("accueil", "Accueil"), (None, "Prix de l'innovation duale")], "Prix de l'innovation duale · Appel à candidatures · Septembre 2026",
                  ["Faire émerger les", "innovations stratégiques", "de demain."],
                  "Le Prix de l'innovation duale porté par Paris Ouest La Défense vise à identifier, valoriser et accompagner les entreprises innovantes développant des technologies à fort potentiel de double usage, répondant à la fois aux besoins des marchés civils et aux enjeux de défense et de sécurité.",
                  btn("Candidater au Prix", CANDIDATURE_PRIX, "or", "sortie", True) + btn("Découvrir les catégories", "#categories", "ghost"),
                  '<div class="phero__fond" aria-hidden="true"><video data-src="{MP4_PRIX}" poster="assets/img/prix-poster.webp" muted loop playsinline preload="none"></video></div>', meta).replace('<section class="phero">', '<section class="phero theme-or">').replace('<p class="surtitre" data-reveal>', '<p class="surtitre surtitre--or" data-reveal>', 1)
    corps += f"""
<section class="sec" aria-labelledby="titre-pold">
  <div class="wrap deux-col deux-col--centre">
    <div>
      <p class="surtitre surtitre--or" data-reveal>Un prix porté par Paris Ouest La Défense</p>
      {lignes("Au cœur de", "l'innovation duale", attrs=' id="titre-pold"')}
    </div>
    <div data-reveal>
      <img src="assets/partenaires/pold-blanc.webp" alt="Paris Ouest La Défense" width="160" height="80" loading="lazy" style="height:64px;width:auto;margin-bottom:24px">
      <p class="lead">L'établissement public territorial Paris Ouest La Défense (POLD) regroupe un pôle économique majeur en Île-de-France où l'innovation duale, des technologies à double usage civil et militaire, trouve un terreau fertile grâce à la concentration de milliers d'entreprises et d'acteurs stratégiques.</p>
      <p class="secondaire" style="margin-top:20px">Cette initiative a pour ambition de favoriser les collaborations entre startups, entreprises technologiques, industriels, acteurs publics, collectivités et experts afin d'accélérer l'émergence de solutions stratégiques et de contribuer au renforcement de la souveraineté technologique.</p>
    </div>
  </div>
</section>

<section class="sec sec--nuit theme-or" id="categories" aria-labelledby="titre-cat">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre surtitre--or" data-reveal>Édition 2026 · Trois catégories, trois lauréats</p>
        {lignes("Trois champs d'innovation", "stratégique", attrs=' id="titre-cat"')}
      </div>
      <p class="lead" data-reveal>Trois catégories ont été définies afin de couvrir des champs technologiques complémentaires. Dans tous les cas, la technologie candidate doit impérativement présenter un caractère dual, reposant sur des applications à la fois civiles et relevant de la défense ou de la sécurité.</p>
    </div>
    <div class="cartes cartes--3">{cats}</div>
    <div style="margin-top:64px" data-reveal>
      <h3 class="h3">Niveau de maturité recherché : phase d'amorçage</h3>
      <ul class="maturite" style="margin-top:24px">
        <li><b>Pre-seed</b><span>Éligible</span><p>Premières preuves de concept</p></li>
        <li><b>Seed</b><span>Éligible</span><p>Premières validations marché</p></li>
        <li><b>Jeunes entreprises</b><p>Le Prix s'adresse prioritairement aux jeunes entreprises technologiques aux premiers stades de leur développement.</p></li>
      </ul>
    </div>
  </div>
</section>

<section class="sec sec--papier" aria-labelledby="titre-dotation">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre surtitre--or" data-reveal>Dotation & accompagnement</p>
        {lignes("Plus qu'un prix,", "un accélérateur.", attrs=' id="titre-dotation"')}
        <p class="lead" data-reveal style="margin-top:24px">Les trois lauréats bénéficieront d'une dotation financière ainsi que d'un accompagnement destiné à renforcer leur visibilité, faciliter les mises en relation et soutenir le développement de leur projet au sein de l'écosystème de l'innovation duale.</p>
      </div>
      <ul class="chiffres chiffres--3" data-reveal>
        <li style="border-color:var(--filet)"><b class="chiffre" style="color:#8A6A1F"><span data-compte="5000">5 000</span></b><span class="chiffres__lib" style="color:var(--gris)">€ attribués à chacun des trois prix</span></li>
        <li style="border-color:var(--filet)"><b class="chiffre" style="color:var(--encre)">3</b><span class="chiffres__lib" style="color:var(--gris)">catégories, 3 lauréats</span></li>
        <li style="border-color:var(--filet)"><b class="chiffre" style="color:var(--encre)"><span data-compte="15000">15 000</span></b><span class="chiffres__lib" style="color:var(--gris)">€ de dotation totale</span></li>
      </ul>
    </div>
    <ul class="avantages">{av}</ul>
  </div>
</section>

<section class="sec sec--nuit" aria-labelledby="titre-cal">
  <div class="wrap">
    <div class="tete-sec">
      <p class="surtitre surtitre--or" data-reveal>Calendrier 2026</p>
      {lignes("De la candidature", "à la remise des prix.", attrs=' id="titre-cal"')}
      <p class="secondaire" data-reveal>Sélectionnez une étape pour afficher les informations associées.</p>
    </div>
    <div class="calendrier" data-reveal>
      <div class="calendrier__etapes" role="tablist" aria-label="Les grandes étapes du Prix">{onglets}</div>
      {panneaux}
    </div>
  </div>
</section>

<section class="sec sec--blanc" aria-labelledby="titre-collectif">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre surtitre--or" data-reveal>Une initiative collective</p>
        {lignes("Ils portent le Prix", attrs=' id="titre-collectif"')}
      </div>
      <p class="lead" data-reveal>Le Prix de l'innovation duale réunit des acteurs publics, institutionnels, industriels et financiers engagés au service de l'innovation stratégique et des technologies à double usage.</p>
    </div>
    <div data-reveal>{bloc_logos(PARTENAIRES_PRIX, "logos logos--6", "Partenaire du Prix de l'innovation duale")}</div>
  </div>
</section>

<section class="sec sec--nuit theme-or" aria-labelledby="titre-cand">
  <div class="wrap">
    <div class="prix-teaser" data-reveal><svg class="medaille" viewBox="0 0 88 88" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><g class="rayons"><circle cx="44" cy="44" r="42" stroke-dasharray="2 5"/><circle cx="44" cy="44" r="36" stroke-opacity=".5"/></g><circle cx="44" cy="44" r="27"/><path d="M44 29l4.4 9 9.9 1.4-7.2 7 1.7 9.8L44 51.6l-8.8 4.6 1.7-9.8-7.2-7 9.9-1.4Z" fill="currentColor" fill-opacity=".18"/></svg>
      <div>
        <p class="surtitre surtitre--or">Appel à candidatures 2026</p>
        {lignes("Votre technologie répond-elle", "à des usages civils", "et de défense ?", attrs=' id="titre-cand"')}
        <p class="lead">Startups, spin-off, entreprises technologiques et industriels : présentez une innovation dont les applications civiles et les usages en matière de défense ou de sécurité sont clairement identifiés, dans l'une des trois catégories du Prix.</p>
        <div class="actions" style="margin-top:32px">{btn("Candidater au Prix", CANDIDATURE_PRIX, "or", "sortie", True)}</div>
        <p class="secondaire" style="margin-top:16px;font-size:.875rem">Les modalités de candidature et les éléments requis seront précisés lors de l'ouverture officielle de l'appel.</p>
      </div>
      <ul class="cats">{"".join(f'<li><span class="num">{n + 1:02d}</span><h3>{t}</h3><p>{d}</p></li>' for n, (t, d) in enumerate(CATEGORIES_PRIX))}</ul>
    </div>
  </div>
</section>"""
    award = {"@type": "Event", "@id": url("prix") + "#remise", "name": "Remise du Prix de l'innovation duale 2026", "startDate": "2026-11-26T17:15:00+01:00", "endDate": "2026-11-26T18:00:00+01:00",
             "location": {"@id": f"{SITE}/#campus-cyber"}, "superEvent": {"@id": f"{SITE}/#dualtech-2026"}, "organizer": {"@id": f"{SITE}/#pold"},
             "eventStatus": "https://schema.org/EventScheduled", "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
             "description": "Remise des trois prix (Robotique, Santé, Deeptech) dotés de 5 000 € chacun, lors de DualTech 2026."}
    graphe = BASE + [award, webpage("prix", "Prix de l'innovation duale 2026", extra={"about": {"@id": url("prix") + "#remise"}}), ariane_ld("prix", "Prix de l'innovation duale")]
    ecrire("prix", "Prix de l'innovation duale 2026 | Paris Ouest La Défense",
           "Robotique, Santé, Deeptech : le Prix de l'innovation duale récompense trois startups à double usage civil et défense, 5 000 € par lauréat. Candidatez.", graphe, corps)


# ====================================================================
# INFOS PRATIQUES
# ====================================================================
def infos():
    hotels = "".join(f'<article class="hotel projecteur" data-reveal style="--d:{n * .08}s"><span class="etiq" style="font:600 .75rem var(--titre);letter-spacing:.16em;text-transform:uppercase">{e}</span><h3>{t}</h3><p>{d}</p><a class="lien-fleche" href="{u}" target="_blank" rel="noopener" style="margin-top:auto">Accéder au site officiel{ico("sortie")}</a></article>' for n, (e, t, d, u) in enumerate(HOTELS))
    ancres = '<nav class="ancres" aria-label="Sections de la page"><div class="wrap"><ul><li><a href="#lieu">Lieu et accès</a></li><li><a href="#hebergement">Hébergement</a></li><li><a href="#faq">Questions fréquentes</a></li></ul></div></nav>'
    meta = """<dl class="phero__meta" data-reveal style="--d:.45s"><div><dt>Date</dt><dd>Jeudi 26 novembre 2026</dd></div><div><dt>Lieu</dt><dd>Campus Cyber, Puteaux</dd></div><div><dt>Accès</dt><dd>Sur accréditation nominative</dd></div></dl>"""
    corps = phero([("accueil", "Accueil"), (None, "Infos pratiques")], "Jeudi 26 novembre 2026 · Campus Cyber", ["Préparer", "votre venue"],
                  "Adresse, transports, hébergement à proximité et réponses aux questions les plus fréquentes. Tout ce qu'il faut savoir avant le 26 novembre.",
                  "", '<div class="phero__fond" aria-hidden="true"><img src="assets/img/skyline-la-defense.webp" alt="" width="680" height="340"></div>', meta)
    corps += ancres + f"""
<section class="sec" id="lieu" aria-labelledby="titre-lieu">
  <div class="wrap">
    <div class="bref" data-reveal style="margin-bottom:72px">
      <h2>En bref</h2>
      <p>DualTech 2026 se tient le jeudi 26 novembre 2026, de 8h30 à 19h00, au Campus Cyber (5-7 rue Bellini, 92800 Puteaux), au cœur du quartier d'affaires de La Défense. L'accès est gratuit mais nominatif : il se fait sur accréditation validée par l'organisation, avec une pièce d'identité à présenter à l'entrée. Le site est desservi par le RER A, le Transilien L, la ligne 1 du métro et le tramway T2 (station La Défense Grande Arche) et il est accessible aux personnes à mobilité réduite. Aucun parking n'est réservé pour l'événement.</p>
      <p class="maj">Mis à jour le 28 octobre 2026 par l'équipe DualTech.</p>
    </div>
    {bloc_lieu(bouton=False, surtitre="Lieu et accès").replace('<h2 class="h2 lignes">', '<h2 class="h2 lignes" id="titre-lieu">', 1)}
  </div>
</section>

<section class="sec sec--papier" id="hebergement" aria-labelledby="titre-hebergement">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Hébergement</p>
        {lignes("Se loger à proximité", attrs=' id="titre-hebergement"')}
      </div>
      <p class="lead" data-reveal>DualTech ne dispose pas de contingent de chambres réservé. Plusieurs établissements situés à proximité du Campus Cyber permettent toutefois de rejoindre facilement le lieu de l'événement. Les réservations sont à effectuer directement auprès des hôtels.</p>
    </div>
    <div class="cartes cartes--4">{hotels}</div>
    <p class="mention" data-reveal style="margin-top:32px;max-width:80ch">Cette sélection est communiquée à titre indicatif. DualTech n'entretient aucun lien commercial avec les établissements mentionnés et n'intervient ni dans les réservations ni dans les conditions tarifaires proposées. Il est recommandé d'effectuer les réservations suffisamment en amont de l'événement.</p>
  </div>
</section>

<section class="sec" id="faq" aria-labelledby="titre-faq">
  <div class="wrap deux-col deux-col--5-7">
    <div class="collant">
      <p class="surtitre" data-reveal>Questions fréquentes</p>
      {lignes("Ce qu'il faut savoir", "avant de venir", attrs=' id="titre-faq"')}
      <p class="secondaire" data-reveal style="margin-top:24px">Une question qui n'est pas listée ici ?</p>
      <a class="lien-fleche" href="{lien('contact')}" data-reveal>Contactez l'équipe DualTech{ico('fleche')}</a>
    </div>
    {bloc_faq(FAQ_INFOS)}
  </div>
</section>
{bloc_final()}"""
    graphe = BASE + [{"@id": f"{SITE}/#dualtech-2026", "@type": "Event", "name": "DualTech 2026", "startDate": "2026-11-26T08:30:00+01:00", "location": {"@id": f"{SITE}/#campus-cyber"}},
                     webpage("infos", "Infos pratiques DualTech 2026", extra={"about": {"@id": f"{SITE}/#dualtech-2026"}}), ariane_ld("infos", "Infos pratiques")]
    ecrire("infos", "Infos pratiques DualTech 2026 : accès au Campus Cyber",
           "Accès au Campus Cyber de La Défense, transports, accréditation, hôtels à proximité et FAQ : tout pour préparer votre venue à DualTech le 26 novembre 2026.", graphe, corps)


# ====================================================================
# RÉTROSPECTIVE
# ====================================================================
def retrospective():
    temoins = "".join(f'<li class="orateur" data-reveal style="--d:{n * .08}s"><div class="orateur__photo"><img src="{p}" alt="{escape(nom)}" width="480" height="480" loading="lazy"></div><h3>{nom}</h3><p>{d}</p></li>' for n, (nom, d, p) in enumerate(GRANDS_TEMOINS))
    tables = ""
    for etiq, titre, modos, intervs in TABLES_2025:
        mods = "".join(f'<li><b style="color:var(--encre)">{n}</b><br><span class="secondaire" style="font-size:.875rem">{f}, {o}</span></li>' for n, f, o in modos)
        ivs = "".join(f'<li class="orateur"><div class="orateur__photo"><img src="assets/intervenants/{slug(n)}.webp" alt="{escape(n)}" width="480" height="480" loading="lazy"></div><h4 class="h3" style="font-size:1rem;margin-top:14px;color:var(--encre)">{n}</h4><p>{f}<strong>{o}</strong></p></li>' for n, f, o in intervs)
        tables += f"""<article class="table-ronde" data-reveal>
  <div><span class="badge badge--table" style="color:#0F7A55;border-color:rgba(15,122,85,.35)">{etiq}</span><h3>{titre}</h3>
    <p class="surtitre" style="margin:28px 0 12px">Modération</p><ul class="puces" style="gap:14px">{mods}</ul></div>
  <div><p class="surtitre" style="margin-bottom:20px">Intervenants</p><ul class="mini-orateurs">{ivs}</ul></div>
</article>"""
    parts = "".join(f'<article class="partenaire-desc projecteur" data-reveal><img src="assets/partenaires/{f}.webp" alt="Logo {escape(n)}" width="200" height="64" loading="lazy"><h3>{n}</h3><p>{d}</p></article>' for n, f, d in PARTENAIRES_2025)
    ancres = '<nav class="ancres" aria-label="Sections de la page"><div class="wrap"><ul><li><a href="#tables-rondes">Tables rondes</a></li><li><a href="#intervenants">Intervenants</a></li><li><a href="#partenaires">Partenaires</a></li></ul></div></nav>'
    meta = """<dl class="phero__meta" data-reveal style="--d:.45s"><div><dt>Date</dt><dd>5 février 2026, dès 19h30</dd></div><div><dt>Lieu</dt><dd>Palais du Luxembourg, Sénat</dd></div><div><dt>Invités</dt><dd>150 décideurs réunis</dd></div></dl>"""
    corps = phero([("accueil", "Accueil"), (None, "Rétrospective février 2026")], "Première édition · 5 février 2026", ["Palais du Luxembourg"],
                  "150 invités réunis dès 19h30 sous le haut patronage de M. Cédric Perrin, président de la commission des affaires étrangères, de la défense et des forces armées du Sénat, pour échanger autour des enjeux majeurs liés à la souveraineté industrielle et technologique.",
                  "", '<div class="phero__fond" aria-hidden="true"><img src="assets/img/palais-du-luxembourg.webp" alt="" width="1440" height="360"></div>', meta)
    corps += ancres + f"""
<section class="sec" id="intervenants" aria-labelledby="titre-temoins">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Grands témoins</p>
        {lignes("Ils ont ouvert", "les échanges", attrs=' id="titre-temoins"')}
      </div>
      <p class="lead" data-reveal>Deux questions ont structuré les échanges du 5 février, en présence de décideurs publics, industriels et financeurs.</p>
    </div>
    <ul class="orateurs" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">{temoins}</ul>
  </div>
</section>

<section class="sec sec--papier" id="tables-rondes" aria-labelledby="titre-tables">
  <div class="wrap">
    <div class="tete-sec">
      <p class="surtitre" data-reveal>Deux temps forts</p>
      {lignes("Les tables rondes", "de la soirée", attrs=' id="titre-tables"')}
    </div>
    {tables}
  </div>
</section>

<section class="sec sec--blanc" id="partenaires" aria-labelledby="titre-p25">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>Écosystème</p>
        {lignes("Les partenaires", "de la première édition", attrs=' id="titre-p25"')}
      </div>
      <div data-reveal>
        <p class="lead">Douze organisations ont soutenu DualTech au Palais du Luxembourg, des institutions publiques aux accélérateurs spécialisés dans les technologies duales.</p>
        <div class="actions" style="margin-top:24px">{btn("Devenir partenaire de la prochaine édition", lien("partenaires"))}</div>
      </div>
    </div>
    <div class="grille-3">{parts}</div>
  </div>
</section>
{bloc_final()}"""
    ev = {"@type": "Event", "@id": url("retrospective") + "#edition-2026-02", "name": "DualTech, première édition", "startDate": "2026-02-05T19:30:00+01:00",
          "eventStatus": "https://schema.org/EventScheduled", "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
          "location": {"@type": "Place", "name": "Palais du Luxembourg", "address": {"@type": "PostalAddress", "streetAddress": "15 rue de Vaugirard", "postalCode": "75006", "addressLocality": "Paris", "addressCountry": "FR"}},
          "organizer": {"@id": f"{SITE}/#inas"}, "maximumAttendeeCapacity": 150,
          "performer": [{"@type": "Person", "name": n} for n, *_ in GRANDS_TEMOINS] + [{"@type": "Person", "name": n} for _, _, _, iv in TABLES_2025 for n, *_ in iv]}
    graphe = BASE + [ev, webpage("retrospective", "Rétrospective DualTech, février 2026", "AboutPage", {"about": {"@id": url("retrospective") + "#edition-2026-02"}}), ariane_ld("retrospective", "Rétrospective février 2026")]
    ecrire("retrospective", "Rétrospective DualTech : 1re édition au Sénat, février 2026",
           "Retour sur la première édition de DualTech au Palais du Luxembourg le 5 février 2026 : 150 invités, deux tables rondes et douze partenaires de l'innovation duale.", graphe, corps)


# ====================================================================
# CONTACT
# ====================================================================
def contact():
    corps = phero([("accueil", "Accueil"), (None, "Contact")], "Nous contacter", ["Une question", "sur DualTech ?"],
                  "Programme, inscription, partenariat, presse : l'équipe DualTech vous répond sous 48h ouvrées.", "",
                  '<div class="phero__fond" aria-hidden="true"><img src="assets/photos/cybersecurite-circuit.webp" alt="" width="1600" height="1068"></div>')
    corps += f"""
<section class="sec sec--papier" aria-labelledby="titre-contact">
  <div class="wrap deux-col deux-col--5-7">
    <div>
      <h2 class="sr-only" id="titre-contact">Écrire à l'équipe DualTech</h2>
      <div class="cartes" style="gap:16px">
        <a class="carte-b projecteur faisceau" href="{lien('partenaires', '#demande')}" data-reveal><span class="etiq">Partenariats</span><span class="h3" style="font:700 1.375rem var(--titre)">Formulaire dédié</span><span class="lien-fleche" style="padding:0">Devenir partenaire{ico('fleche')}</span></a>
        <a class="carte-b projecteur faisceau" href="{lien('inscription')}" data-reveal style="--d:.08s"><span class="etiq">Inscription</span><span class="h3" style="font:700 1.375rem var(--titre)">S'inscrire gratuitement</span><span class="lien-fleche" style="padding:0">Demander une accréditation{ico('fleche')}</span></a>
        <a class="carte-b projecteur faisceau" href="{LINKEDIN}" target="_blank" rel="noopener" data-reveal style="--d:.16s"><span class="etiq">Suivre l'événement</span><span class="h3" style="font:700 1.375rem var(--titre)">LinkedIn</span><span class="lien-fleche" style="padding:0">{ico('linkedin')}DualTech sur LinkedIn</span></a>
      </div>
    </div>
    <div class="panneau-form" data-reveal>
      <form class="form form--2" action="https://formsubmit.co/contact@dualtech-event.eu" method="post">
        <div class="champ"><label for="c-nom">Nom et prénom <span class="req">*</span></label><input id="c-nom" name="nom" autocomplete="name" required></div>
        <div class="champ"><label for="c-mail">Adresse e-mail <span class="req">*</span></label><input id="c-mail" name="email" type="email" autocomplete="email" required></div>
        <div class="champ"><label for="c-orga">Organisation</label><input id="c-orga" name="organisation" autocomplete="organization"></div>
        <div class="champ"><label for="c-sujet">Sujet <span class="req">*</span></label><select id="c-sujet" name="sujet" required><option value="">Sélectionner un sujet</option><option>Programme et intervenants</option><option>Inscription</option><option>Partenariat</option><option>Presse et médias</option><option>Autre demande</option></select></div>
        <div class="champ plein"><label for="c-msg">Votre message <span class="req">*</span></label><textarea id="c-msg" name="message" required></textarea></div>
        <div class="plein"><button class="btn btn--primaire" type="submit">Envoyer le message{ico('fleche')}</button></div>
        <p class="mention plein">En envoyant ce formulaire, vous acceptez d'être recontacté par l'équipe DualTech au sujet de votre demande.</p>
      </form>
    </div>
  </div>
</section>"""
    graphe = BASE + [webpage("contact", "Contacter l'équipe DualTech", "ContactPage"), ariane_ld("contact", "Contact")]
    ecrire("contact", "Contact DualTech : programme, partenariat et presse",
           "Programme, inscription, partenariat ou presse : contactez l'équipe DualTech, réponse sous 48h ouvrées. DualTech 2026, le 26 novembre au Campus Cyber.", graphe, corps)


# ====================================================================
# INSCRIPTION
# ====================================================================
def inscription():
    etapes = [("Remplir le formulaire", "Complétez vos informations professionnelles sur notre billetterie sécurisée."),
              ("Examen de votre dossier", "L'organisation valide chaque demande au regard du profil du participant et des places disponibles par catégorie."),
              ("Confirmation d'inscription", "Une fois validée, votre inscription vous est confirmée par e-mail avec toutes les informations pratiques pour le 26 novembre.")]
    et = "".join(f'<li data-reveal style="--d:{n * .1}s"><span class="num">{n + 1:02d}</span><h3>{t}</h3><p>{d}</p></li>' for n, (t, d) in enumerate(etapes))
    meta = """<dl class="phero__meta" data-reveal style="--d:.45s"><div><dt>Date</dt><dd>Jeudi 26 novembre 2026</dd></div><div><dt>Horaires</dt><dd>8h30 à 18h30</dd></div><div><dt>Lieu</dt><dd>Campus Cyber, La Défense</dd></div><div><dt>Capacité</dt><dd>400 places</dd></div></dl>"""
    corps = phero([("accueil", "Accueil"), (None, "Inscription")], "Jeudi 26 novembre 2026 · Campus Cyber", ["S'inscrire"],
                  "400 places sont disponibles sur inscription préalable. Chaque demande est examinée par l'organisation avant l'envoi de la confirmation définitive de participation.",
                  btn("Accéder au formulaire d'inscription", LUMA, "primaire", "sortie", True),
                  '<div class="phero__fond" aria-hidden="true"><img src="assets/photos/conference-salle.webp" alt="" width="1600" height="1067"></div>', meta)
    corps = corps.replace('<div class="actions" data-reveal style="--d:.35s">', '<div class="actions" data-reveal style="--d:.35s;flex-direction:column;align-items:flex-start">', 1).replace(
        f'{ico("sortie")}</a></div>', f'{ico("sortie")}</a><p class="secondaire" style="font-size:.875rem">Vous serez redirigé vers notre plateforme de billetterie sécurisée Luma pour finaliser votre demande.</p></div>', 1)
    corps += f"""
<section class="sec sec--papier" aria-labelledby="titre-etapes">
  <div class="wrap">
    <div class="tete-sec tete-sec--split">
      <div>
        <p class="surtitre" data-reveal>S'inscrire à DualTech</p>
        {lignes("Réalisez votre démarche", "en 3 étapes", attrs=' id="titre-etapes"')}
      </div>
      <div class="actions" data-reveal>{btn("Demande d'inscription", LUMA, "primaire", "sortie", True)}</div>
    </div>
    <ol class="etapes">{et}</ol>
  </div>
</section>

<section class="sec" aria-labelledby="titre-profils">
  <div class="wrap">
    <div class="tete-sec">
      <p class="surtitre" data-reveal>Pour qui ?</p>
      {lignes("Intérêt de la participation", "pour les acteurs de l'écosystème", attrs=' id="titre-profils"')}
    </div>
    <div class="cartes cartes--3">{"".join(f'<article class="carte-b projecteur faisceau" data-reveal style="--d:{n * .08}s">{ico(i, "ico")}<h3 class="h3">{t}</h3><p>{d}</p></article>'.replace('class="ico"', 'class="ico" style="width:32px;height:32px;color:var(--tech)"') for n, (t, d, i) in enumerate(PROFILS))}</div>
  </div>
</section>
{bloc_final(texte="Chaque demande est examinée par l'organisation avant l'envoi de la confirmation définitive de participation.")}"""
    graphe = BASE + [EVENEMENT, webpage("inscription", "Inscription à DualTech 2026", extra={"about": {"@id": f"{SITE}/#dualtech-2026"}}), ariane_ld("inscription", "Inscription")]
    ecrire("inscription", "Inscription DualTech 2026 : demandez votre accréditation",
           "Inscription gratuite à DualTech 2026 le 26 novembre au Campus Cyber : 400 places sur demande validée par l'organisation. Formulaire en ligne en 3 étapes.", graphe, corps)


if __name__ == "__main__":
    for f in (accueil, programme, intervenants, workshops, partenaires, prix, infos, retrospective, contact, inscription):
        f()
    print("10 pages générées")

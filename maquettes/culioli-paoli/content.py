# -*- coding: utf-8 -*-
"""Contenus de la maquette. Toute information biographique provient de la fiche Justifit
et reste marquée « à confirmer » tant que Me Culioli-Paoli ne l'a pas validée."""
from pages_expertises import EXPERTISES, GAV
from pages_lieux import LIEUX

SITE = {
    "url": "https://www.culioli-paoli-avocat.fr",  # domaine provisoire
    "tel": "+33600000000",                          # numéro provisoire
    "tel_display": "06 00 00 00 00",
    "whatsapp": "https://wa.me/33600000000",
    "address": "264 rue du Faubourg Saint-Honoré, 75008 Paris",
}

T = '<span class="todo">à confirmer</span>'

# ---------------------------------------------------------------------------- Accueil
FRISE = [
    ("Plainte", "Avant l'enquête", "Déposer plainte ou y répondre",
     "La plainte déclenche souvent la procédure. Rédigée avec un avocat, elle expose clairement les faits et les préjudices. La personne visée peut, elle aussi, préparer sa défense dès ce stade.",
     [("Victimes", "Plainte simple, plainte avec constitution de partie civile, citation directe"), ("Mis en cause", "Préparation d'une audition libre ou d'une convocation")], "/avocat-penaliste-paris/"),
    ("Garde à vue", "24 à 48 heures", "Être assisté dès la première heure",
     "La garde à vue est le moment le plus exposé de la procédure. L'avocat s'entretient avec la personne, consulte certaines pièces et l'assiste pendant les auditions.",
     [("Entretien", "30 minutes, confidentiel"), ("Droits", "Se taire, prévenir un proche, voir un médecin")], "/avocat-garde-a-vue-paris/"),
    ("Enquête", "Préliminaire ou flagrance", "Comprendre ce qui est reproché",
     "Pendant l'enquête, le parquet dirige les investigations : perquisitions, réquisitions, auditions. L'avocat anticipe les suites possibles et prépare la défense.",
     [("Actes", "Perquisition, saisies, réquisitions"), ("Issues", "Classement, alternative aux poursuites, convocation, ouverture d'information")], "/avocat-penaliste-paris/"),
    ("Information judiciaire", "Juge d'instruction", "Agir dans le dossier d'instruction",
     "Mis en examen ou témoin assisté, la personne dispose de droits pour agir sur le dossier : demandes d'actes, requêtes en nullité, demandes de mise en liberté.",
     [("Statuts", "Mis en examen, témoin assisté, partie civile"), ("Détention", "Débat devant le juge des libertés et de la détention")], "/avocat-penaliste-paris/"),
    ("Audience", "Tribunal ou cour", "Plaider le dossier",
     "Tribunal correctionnel, cour criminelle départementale, cour d'assises : l'audience se prépare longtemps à l'avance, avec la personne défendue.",
     [("Délits", "Tribunal correctionnel, comparution immédiate, CRPC"), ("Crimes", "Cour criminelle départementale, cour d'assises")], "/avocat-plaidoirie-penale/"),
    ("Exécution de la peine", "Après la décision", "Obtenir un aménagement",
     "Bracelet électronique, semi-liberté, libération conditionnelle : la défense se poursuit devant le juge de l'application des peines.",
     [("Juridiction", "Juge de l'application des peines"), ("Mesures", "Surveillance électronique, semi-liberté, libération conditionnelle")], "/avocat-amenagement-de-peine/"),
]


def home_body(p):
    tabs = "".join(
        f'<button role="tab" id="tab-{i}" aria-controls="pan-{i}" aria-selected="{"true" if i == 0 else "false"}" tabindex="{0 if i == 0 else -1}"><small>{s}</small>{n}</button>'
        for i, (n, s, *_r) in enumerate(FRISE))
    pans = "".join(
        f'''<div class="frise-panel" role="tabpanel" id="pan-{i}" aria-labelledby="tab-{i}"{"" if i == 0 else " hidden"}>
  <div><h3>{h}</h3><p>{txt}</p><a class="link-arrow" href="{url}">En savoir plus {{{{ARROW}}}}</a></div>
  <ul>{"".join(f"<li><b>{k}</b><span>{v}</span></li>" for k, v in li)}</ul></div>'''
        for i, (n, s, h, txt, li, url) in enumerate(FRISE))
    som = "".join(
        f'<li><a href="/{x["slug"]}/"><h3>{x["nav"]}</h3><p>{x["somm"]}</p><span class="arrow">{{{{ARROW}}}}</span></a></li>'
        for x in EXPERTISES)
    return f"""
<section class="night hero">
  <div class="wrap">
    <div class="hero-grid">
      <div>
        <div class="hero-kicker"><span class="cote">Cabinet d'avocat · Paris 8<sup>e</sup></span><span class="rule" aria-hidden="true"></span><span class="eyebrow">Droit pénal</span></div>
        <h1><span class="l"><span>Charles</span></span><span class="l"><span><em>Culioli-Paoli</em></span></span><span class="l"><span class="role">Avocat pénaliste à Paris</span></span></h1>
        <p class="hero-lede">Une défense pénale <strong>exigeante, disponible et discrète</strong>, de la garde à vue à l'exécution de la peine, pour les personnes mises en cause comme pour les victimes.</p>
        <div class="hero-actions">
          <a class="btn btn--solid" href="/contact/">Exposer votre situation {{{{ARROW}}}}</a>
          <a class="btn btn--ghost" href="/charles-culioli-paoli-avocat/">Découvrir le cabinet</a>
        </div>
      </div>
      <div class="hero-figure">
        <figure>
          <img src="assets/img/charles-culioli-paoli-avocat-robe.webp" width="900" height="1317" alt="Me Charles Culioli-Paoli en robe d'avocat au tribunal judiciaire de Paris" fetchpriority="high">
          <figcaption><b>Tribunal judiciaire de Paris</b>Me Charles Culioli-Paoli, avocat au Barreau de Paris</figcaption>
        </figure>
      </div>
    </div>
    <dl class="registre">
      <div><dt>Formation</dt><dd>LL.M., Harvard Law School</dd></div>
      <div><dt>Formation</dt><dd>Master, École de droit de Sciences Po</dd></div>
      <div><dt>Barreau</dt><dd>Paris{T}</dd></div>
      <div><dt>Langues</dt><dd>Français, English, Español, العربية, Corsu</dd></div>
    </dl>
  </div>
</section>

<section class="section">
  <div class="wrap folio">
    <div class="folio-margin"><span class="cote">D.01 · Approche</span><span class="note">Une défense qui commence par la lecture du dossier.</span></div>
    <div class="folio-body reveal">
      <p class="manifeste">Chaque dossier pénal raconte une histoire incomplète. Le travail de l'avocat consiste à la <em>lire entièrement</em>, à en éprouver chaque pièce, puis à la rendre audible devant le juge.</p>
      <div class="principes">
        <div><h3>Rigueur</h3><p>Une lecture complète de la procédure, un contrôle de la régularité de chaque acte et une stratégie expliquée sans détour.</p></div>
        <div><h3>Disponibilité</h3><p>Une intervention rapide en garde à vue et un interlocuteur unique, joignable aux moments où tout se décide.</p></div>
        <div><h3>Discrétion</h3><p>Le secret professionnel comme règle de travail, pour les particuliers comme pour les dirigeants.</p></div>
      </div>
    </div>
  </div>
</section>

<section class="night section">
  <div class="wrap folio">
    <div class="folio-margin"><span class="cote">D.02 · Procédure</span><span class="note">Six étapes, une même défense.</span></div>
    <div class="folio-body">
      <h2 class="sec-title">À chaque étape de la <em>procédure pénale</em></h2>
      <div class="frise">
        <div class="frise-tabs" role="tablist" aria-label="Étapes de la procédure pénale">{tabs}<span class="frise-progress" aria-hidden="true"></span></div>
        {pans}
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap folio">
    <div class="folio-margin"><span class="cote">D.03 · Expertises</span><span class="note">Sept domaines d'intervention.</span></div>
    <div class="folio-body">
      <h2 class="sec-title">Domaines <em>d'intervention</em></h2>
      <ul class="sommaire reveal">{som}</ul>
    </div>
  </div>
</section>

<section class="section" style="background:var(--paper-2)">
  <div class="wrap folio">
    <div class="folio-margin"><span class="cote">D.04 · Parcours</span><span class="note">Paris, Cambridge, le Parquet.</span></div>
    <div class="folio-body parcours">
      <div class="parcours-photo reveal"><img src="assets/img/charles-culioli-paoli-portrait.webp" width="400" height="400" loading="lazy" alt="Portrait de Me Charles Culioli-Paoli, avocat pénaliste à Paris"></div>
      <div class="reveal">
        <h2 class="sec-title" style="font-size:var(--step-3)">Une formation <em>internationale</em>, une pratique ancrée à Paris</h2>
        <div class="ecoles">
          <div><span class="deg">LL.M.</span><span class="sch">Harvard Law School</span></div>
          <div><span class="deg">Master</span><span class="sch">École de droit de Sciences Po</span></div>
        </div>
        <ul class="experiences">
          <li><span>Fedida &amp; Associés</span><span>Cabinet{T}</span></li>
          <li><span>Schapira Associés</span><span>Cabinet{T}</span></li>
          <li><span>Parquet de Paris</span><span>Expérience{T}</span></li>
          <li><span>Ministère de l'Économie</span><span>Expérience{T}</span></li>
        </ul>
        <div class="langues" aria-label="Langues de travail"><span>Français</span><span lang="en">English</span><span lang="es">Español</span><span lang="ar">العربية</span><span lang="co">Corsu</span></div>
        <p style="margin-top:2rem"><a class="link-arrow" href="/charles-culioli-paoli-avocat/">Lire le parcours de Charles Culioli-Paoli {{{{ARROW}}}}</a></p>
      </div>
    </div>
  </div>
</section>

<section class="night section">
  <div class="wrap folio">
    <div class="folio-margin"><span class="cote">D.05 · Territoires</span><span class="note">Paris en priorité, partout où la défense l'exige.</span></div>
    <div class="folio-body">
      <h2 class="sec-title">Où le cabinet <em>intervient</em></h2>
      <div class="territoires reveal">
        <a class="terr t-a" href="/avocat-penaliste-paris/"><span class="dep">75</span><h3>Paris</h3><p>Tribunal judiciaire de Paris, cour d'appel de Paris, commissariats parisiens.</p><span class="tag">Cabinet principal</span></a>
        <a class="terr t-b" href="/avocat-penaliste-essonne/"><span class="dep">91</span><h3>Essonne</h3><p>Évry-Courcouronnes, Massy, Corbeil-Essonnes, Palaiseau, Longjumeau, Étampes.</p><span class="tag">Massy{T}</span></a>
        <a class="terr t-c" href="/avocat-centre-penitentiaire-fleury-merogis/"><span class="dep">FM</span><h3>Fleury-Mérogis</h3><p>Visites, détention provisoire, aménagement de peine.</p><span class="tag">Établissement</span></a>
        <a class="terr t-d" href="/interventions/"><span class="dep">IDF</span><h3>Île-de-France</h3><p>Nanterre, Créteil, Bobigny, Versailles, Meaux.</p><span class="tag">Juridictions</span></a>
        <a class="terr t-e" href="/interventions/"><span class="dep">2A·2B</span><h3>Corse</h3><p>Ajaccio, Bastia, Borgo. Échanges possibles en langue corse.</p><span class="tag">Proposition</span></a>
        <a class="terr t-f" href="/etablissements-penitentiaires/"><span class="dep">FR</span><h3>Établissements</h3><p>Fresnes, La Santé, Réau, Villepinte, Lyon-Corbas, Marseille.</p><span class="tag">Rubrique dédiée</span></a>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap folio">
    <div class="folio-margin"><span class="cote">D.06 · Ressources</span><span class="note">Comprendre avant d'agir.</span></div>
    <div class="folio-body">
      <h2 class="sec-title">Ressources <em>juridiques</em></h2>
      <div class="edito reveal">
        <a class="edito-main" href="/guide-amenagement-de-peine/"><span class="cote">Guide · Exécution des peines</span><h3>Aménagement de peine : conditions, procédure et délais</h3><p>Bracelet électronique, semi-liberté, libération conditionnelle : ce que prévoit le Code de procédure pénale et comment préparer la demande.</p></a>
        <div class="edito-list">
          <a href="/avocat-garde-a-vue-paris/"><span class="cote">Procédure</span><h3>Garde à vue : les droits de la personne et le rôle de l'avocat</h3></a>
          <a href="/avocat-extradition/"><span class="cote">International</span><h3>Mandat d'arrêt européen : les premières heures</h3></a>
          <a href="/ressources/"><span class="cote">Toutes les thématiques</span><h3>Guides, analyses et repères de procédure</h3></a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="night contact-band"><div class="wrap">
  <div class="cb"><span class="cote">Urgence pénale</span><h2 style="margin-top:.8rem">Un proche est en <em>garde à vue</em>&nbsp;?</h2>
    <p>Le cabinet intervient en urgence à Paris et en Essonne. Le numéro s'affiche sur demande.</p>
    <button class="btn btn--solid" type="button" data-urgence>{{{{PHONE}}}} Afficher le numéro d'urgence</button></div>
  <div class="cb"><span class="cote">Premier rendez-vous</span><h2 style="margin-top:.8rem">Parlons de votre <em>dossier</em></h2>
    <p>Décrivez brièvement votre situation. Le cabinet vous répond pour organiser un premier entretien, au cabinet ou en visioconférence.</p>
    <a class="btn btn--ghost" href="/contact/">Écrire au cabinet {{{{ARROW}}}}</a></div>
</div></section>"""


SOMM = {
    "avocat-penaliste-paris": "Personnes mises en cause et victimes, de l'enquête à l'audience.",
    "avocat-droit-penal-des-affaires": "Abus de biens sociaux, blanchiment, fraude fiscale, corruption.",
    "avocat-extradition": "Mandat d'arrêt européen et demandes d'extradition.",
    "avocat-droit-penal-international": "Notice rouge Interpol, entraide pénale, dossiers transfrontières.",
    "avocat-amenagement-de-peine": "Bracelet électronique, semi-liberté, libération conditionnelle.",
    "avocat-cybercriminalite": "Cyberattaques, fraude informatique, escroqueries en ligne.",
    "avocat-plaidoirie-penale": "Tribunal correctionnel, cour criminelle départementale, cour d'assises.",
}
for _x in EXPERTISES:
    _x["somm"] = SOMM[_x["slug"]]

HOME = {
    "slug": "index", "nav": "Accueil", "prio": "P1", "schema_label": "LegalService, Person",
    "kw": "avocat pénaliste Paris", "kw2": ["Charles Culioli-Paoli", "avocat droit pénal Paris"],
    "title": "Charles Culioli-Paoli | Avocat pénaliste à Paris",
    "desc": "Me Charles Culioli-Paoli, avocat pénaliste à Paris, diplômé de Harvard Law School et de Sciences Po. Garde à vue, instruction, audience. Prendre contact.",
    "h1_plain": "Charles Culioli-Paoli, avocat pénaliste à Paris",
    "og": "charles-culioli-paoli-tribunal-paris.webp",
    "body": home_body,
}

# ---------------------------------------------------------------------------- Le cabinet
CABINET = {
    "slug": "charles-culioli-paoli-avocat", "nav": "Le cabinet", "prio": "P1", "schema_label": "ProfilePage, Person",
    "kw": "Charles Culioli-Paoli avocat", "kw2": ["Me Culioli-Paoli", "avocat Harvard Paris", "avocat pénaliste Sciences Po"],
    "title": "Charles Culioli-Paoli, avocat | Parcours et vision",
    "desc": "Le parcours de Me Charles Culioli-Paoli, avocat pénaliste à Paris : LL.M. de Harvard Law School, Master de Sciences Po, et sa vision de la défense pénale.",
    "h1_plain": "Charles Culioli-Paoli, avocat",
    "body": f"""
<section class="night page-head"><div class="wrap">
{{{{CRUMBS}}}}
<div class="head-split">
  <div><span class="cote">Le cabinet</span>
    <h1 style="margin-top:1rem">Charles Culioli-Paoli, <em>avocat</em></h1>
    <p class="intro">Avocat pénaliste indépendant installé à Paris, <strong>Charles Culioli-Paoli</strong> a construit sa pratique entre la rigueur académique de Sciences Po et de Harvard Law School et l'expérience concrète des cabinets pénalistes et du Parquet de Paris.</p></div>
  <aside class="head-aside"><dl>
    <div><dt>Prestation de serment</dt><dd>2025{T}</dd></div>
    <div><dt>Barreaux</dt><dd>Paris · New York{T}</dd></div>
    <div><dt>Cabinet</dt><dd>Paris 8<sup>e</sup> · Massy{T}</dd></div>
  </dl></aside>
</div></div></section>

<section class="section"><div class="wrap folio">
  <div class="folio-margin"><span class="cote">Pièce 1 · Le portrait</span></div>
  <div class="folio-body parcours">
    <div class="parcours-photo"><img src="assets/img/charles-culioli-paoli-portrait.webp" width="400" height="400" alt="Portrait de Charles Culioli-Paoli, avocat au Barreau de Paris"></div>
    <div class="prose">
      <p class="lead">Défendre, c'est d'abord comprendre une personne, puis un dossier.</p>
      <p>Charles Culioli-Paoli exerce en droit pénal, aux côtés des personnes mises en cause comme des victimes. Sa pratique couvre l'ensemble de la chaîne pénale : garde à vue, enquête, information judiciaire, audience et exécution des peines.</p>
      <p>Il porte une attention particulière aux dossiers qui exigent une lecture technique (droit pénal des affaires, cybercriminalité) et à ceux qui franchissent les frontières (extradition, mandat d'arrêt européen, entraide pénale).</p>
      <p class="ref">Texte de présentation proposé, à réécrire avec Me Culioli-Paoli pour refléter sa voix.</p>
    </div>
  </div>
</div></section>

<section class="night section"><div class="wrap folio">
  <div class="folio-margin"><span class="cote">Pièce 2 · Formation</span><span class="note">Deux écoles, deux traditions juridiques.</span></div>
  <div class="folio-body">
    <h2 class="sec-title">Une formation entre <em>Paris et Cambridge</em></h2>
    <div class="principes" style="margin-top:2.5rem">
      <div style="border-color:var(--champagne)"><span class="cote">Cambridge, Massachusetts</span><h3 style="font-size:var(--step-3);font-weight:300;margin-top:.6rem">LL.M., Harvard Law School</h3><p style="color:var(--on-night-soft)">Une immersion dans la common law et la procédure pénale américaine, utile pour les dossiers internationaux et le dialogue avec les confrères étrangers.{T}</p></div>
      <div style="border-color:var(--champagne)"><span class="cote">Paris</span><h3 style="font-size:var(--step-3);font-weight:300;margin-top:.6rem">Master, École de droit de Sciences Po</h3><p style="color:var(--on-night-soft)">Une formation exigeante, ouverte sur le contentieux et l'international.{T}</p></div>
      <div style="border-color:var(--champagne)"><span class="cote">Barreau</span><h3 style="font-size:var(--step-3);font-weight:300;margin-top:.6rem">Paris, New York</h3><p style="color:var(--on-night-soft)">Inscription aux barreaux de Paris et de New York, mentionnée sur sa fiche professionnelle.{T}</p></div>
    </div>
  </div>
</div></section>

<section class="section"><div class="wrap folio">
  <div class="folio-margin"><span class="cote">Pièce 3 · Expériences</span></div>
  <div class="folio-body prose">
    <h2 style="margin-top:0">Le pénal vu de chaque côté de la barre</h2>
    <p>Avant de prêter serment, Charles Culioli-Paoli s'est formé auprès de cabinets reconnus de la place parisienne, dont Fedida &amp; Associés et Schapira Associés. Il a également acquis une expérience au Parquet de Paris et au ministère de l'Économie.{T}</p>
    <p>Cette double perspective, celle de la défense et celle de l'accusation, nourrit sa manière de lire un dossier : anticiper les arguments adverses, mesurer les risques, identifier les marges de manœuvre.</p>
    <ul class="experiences">
      <li><span>Fedida &amp; Associés</span><span>Défense pénale</span></li>
      <li><span>Schapira Associés</span><span>Défense pénale</span></li>
      <li><span>Parquet de Paris</span><span>Ministère public</span></li>
      <li><span>Ministère de l'Économie</span><span>Administration</span></li>
    </ul>
    <p class="ref">Dates, fonctions exactes et intitulés à compléter et valider.</p>
  </div>
</div></section>

<section class="section" style="background:var(--paper-2)"><div class="wrap folio">
  <div class="folio-margin"><span class="cote">Pièce 4 · Vision</span></div>
  <div class="folio-body">
    <p class="manifeste">La défense pénale demande trois choses : <em>connaître le dossier</em> mieux que quiconque, être présent quand tout se décide, et dire à la personne défendue la vérité sur sa situation.</p>
    <p class="ref" style="margin-top:1rem">Formulation proposée, à valider par Me Culioli-Paoli.</p>
    <div class="principes">
      <div><h3>La plaidoirie</h3><p>Une parole préparée, précise, qui part des pièces et parle au juge comme aux jurés.</p></div>
      <div><h3>L'international</h3><p>Une aisance en anglais et en espagnol pour les dossiers qui dépassent les frontières.</p></div>
      <div><h3>Les langues</h3><p>Français, anglais, espagnol, arabe et corse : parler à chacun dans sa langue.{T}</p></div>
    </div>
  </div>
</div></section>

<section class="night contact-band"><div class="wrap">
  <div class="cb"><span class="cote">Rendez-vous</span><h2 style="margin-top:.8rem">Rencontrer <em>Charles Culioli-Paoli</em></h2>
    <p>Au cabinet, à Paris, ou en visioconférence.</p><a class="btn btn--solid" href="/contact/">Prendre rendez-vous {{{{ARROW}}}}</a></div>
  <div class="cb"><span class="cote">Expertises</span><h2 style="margin-top:.8rem">Sept domaines <em>d'intervention</em></h2>
    <p>Droit pénal général, pénal des affaires, extradition, international, aménagement de peine, cybercriminalité, plaidoirie.</p><a class="btn btn--ghost" href="/expertises/">Voir les expertises {{{{ARROW}}}}</a></div>
</div></section>""",
}

# ---------------------------------------------------------------------------- Hub expertises
def expertises_hub(p):
    som = "".join(
        f'<li><a href="/{x["slug"]}/"><h3>{x["nav"]}</h3><p>{x["somm"]}</p><span class="arrow">{{{{ARROW}}}}</span></a></li>'
        for x in EXPERTISES)
    return f"""<section class="night page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Expertises</span><h1 style="margin-top:1rem">Les domaines <em>d'intervention</em> du cabinet</h1>
<p class="intro">Le cabinet de <strong>Me Charles Culioli-Paoli</strong> intervient en droit pénal, du premier acte d'enquête à l'exécution de la peine, en France et dans les dossiers internationaux.</p></div></section>
<section class="section"><div class="wrap folio">
  <div class="folio-margin"><span class="cote">Sommaire</span><span class="note">Cliquez sur un domaine pour le détail.</span></div>
  <div class="folio-body"><ul class="sommaire" style="margin-top:0">{som}</ul>
  <div class="prose" style="margin-top:3rem"><h2 style="margin-top:0">Une procédure en particulier&nbsp;?</h2>
  <p>Consultez la page dédiée à la <a href="/avocat-garde-a-vue-paris/">garde à vue</a> ou le <a href="/guide-amenagement-de-peine/">guide de l'aménagement de peine</a>.</p>
  <p class="ref">Mention déontologique : les domaines présentés sont des domaines d'activité dominante. Ils ne constituent pas des mentions de spécialisation délivrées par le Conseil national des barreaux.</p></div></div>
</div></section>"""


EXP_HUB = {
    "slug": "expertises", "nav": "Expertises", "prio": "P1", "schema_label": "CollectionPage",
    "kw": "avocat droit pénal Paris", "kw2": ["expertises avocat pénaliste", "domaines d'intervention avocat"],
    "title": "Expertises en droit pénal | Me Charles Culioli-Paoli",
    "desc": "Droit pénal général, pénal des affaires, extradition, droit pénal international, aménagement de peine, cybercriminalité et plaidoirie : les expertises du cabinet.",
    "h1_plain": "Les domaines d'intervention du cabinet", "body": expertises_hub,
}

# ---------------------------------------------------------------------------- Ressources
RES = [
    ("Guides pratiques", [("Garde à vue : vos droits et le rôle de l'avocat", "/avocat-garde-a-vue-paris/", "Publié"),
                          ("Que faire après une convocation par la police&nbsp;?", "", "À rédiger")]),
    ("Comprendre une procédure pénale", [("Enquête, instruction, jugement : la chaîne pénale", "", "À rédiger"),
                                         ("La comparution immédiate en pratique", "", "À rédiger")]),
    ("Comprendre l'aménagement de peine", [("Aménagement de peine : conditions, procédure et délais", "/guide-amenagement-de-peine/", "Publié"),
                                           ("La libération sous contrainte", "", "À rédiger")]),
    ("Extradition et droit international", [("Mandat d'arrêt européen : les premières heures", "/avocat-extradition/", "Publié"),
                                            ("Contester une notice rouge Interpol", "", "À rédiger")]),
    ("Droit pénal des affaires", [("Perquisition dans l'entreprise : les bons réflexes", "", "À rédiger"),
                                  ("CRPC et CJIP : les issues négociées", "", "À rédiger")]),
    ("Cybercriminalité", [("Entreprise victime d'un rançongiciel : la démarche pénale", "", "À rédiger")]),
]


def ressources_body(p):
    blocks = ""
    for i, (theme, items) in enumerate(RES, 1):
        li = "".join(
            (f'<a href="{u}"><span class="cote">{st}</span><h3>{t}</h3></a>' if u else
             f'<div style="padding:1.2rem 0;border-top:1px solid var(--rule);opacity:.7"><span class="cote">{st}</span><h3 style="font-size:var(--step-2);margin-top:.4rem">{t}</h3></div>')
            for t, u, st in items)
        blocks += f"""<div class="folio" style="margin-top:3rem"><div class="folio-margin"><span class="cote">Thème {i:02d}</span></div>
<div class="folio-body"><h2 class="sec-title" style="font-size:var(--step-3)">{theme}</h2><div class="edito-list" style="margin-top:1rem">{li}</div></div></div>"""
    return f"""<section class="night page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Ressources</span><h1 style="margin-top:1rem">Comprendre le droit pénal, <em>thème par thème</em></h1>
<p class="intro">Des guides et des analyses rédigés par <strong>Me Charles Culioli-Paoli</strong> pour comprendre une procédure, connaître ses droits et préparer sa défense. Pas d'actualités au fil de l'eau : des contenus organisés et mis à jour.</p></div></section>
<section class="section"><div class="wrap">{blocks}</div></section>"""


RESSOURCES = {
    "slug": "ressources", "nav": "Ressources", "prio": "P2", "schema_label": "CollectionPage",
    "kw": "guide procédure pénale", "kw2": ["comprendre la procédure pénale", "droits garde à vue", "aménagement de peine guide"],
    "title": "Ressources en droit pénal | Guides de Me Culioli-Paoli",
    "desc": "Guides pratiques et analyses en droit pénal : garde à vue, aménagement de peine, extradition, pénal des affaires et cybercriminalité, classés par thème.",
    "h1_plain": "Comprendre le droit pénal, thème par thème", "body": ressources_body,
}

# ---------------------------------------------------------------------------- Guide
GUIDE = {
    "slug": "guide-amenagement-de-peine", "nav": "Guide de l'aménagement de peine", "crumbs": [("Ressources", "ressources")],
    "nav_slug": "ressources", "prio": "P1", "schema": "Article",
    "kw": "aménagement de peine", "kw2": ["conditions aménagement de peine", "bracelet électronique conditions", "libération conditionnelle"],
    "title": "Aménagement de peine : conditions, procédure et délais",
    "desc": "Bracelet électronique, semi-liberté, libération conditionnelle : conditions de l'aménagement de peine, procédure devant le juge et conseils pour la demande.",
    "h1_plain": "Aménagement de peine : conditions, procédure et délais",
    "faq": [("Combien de temps dure la procédure&nbsp;?", "Les délais varient selon les juridictions et la situation de la personne. Il faut compter plusieurs semaines, parfois plusieurs mois, entre le dépôt de la requête et le débat contradictoire."),
            ("Peut-on demander un aménagement sans avocat&nbsp;?", "Oui, mais l'assistance d'un avocat permet de construire un dossier complet et de défendre le projet lors du débat devant le juge.")],
    "body": f"""<section class="night page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Guide · Exécution des peines</span>
<h1 style="margin-top:1rem">Aménagement de peine : <em>conditions, procédure et délais</em></h1>
<p class="intro">Ce guide présente les principales mesures d'<strong>aménagement de peine</strong>, les conditions prévues par le Code de procédure pénale et la manière de préparer une demande devant le juge de l'application des peines.</p>
<p class="ref" style="color:var(--on-night-soft);margin-top:1.2rem">Par Me Charles Culioli-Paoli · Mis à jour le 27 novembre 2026 · Lecture : 6 minutes</p></div></section>
<section class="section"><div class="wrap article">
<article class="prose">
<h2 id="principe" style="margin-top:0">Le principe</h2>
<p>L'aménagement de peine permet d'exécuter une peine d'emprisonnement autrement qu'en détention, ou de sortir de détention avant la fin de la peine, sous le contrôle du juge de l'application des peines. Il repose sur un projet de réinsertion et sur des obligations.</p>
<h2 id="quand">À quel moment le demander</h2>
<h3>Avant l'incarcération</h3>
<p>Depuis la réforme entrée en vigueur en mars 2020, la juridiction de jugement peut aménager elle-même les peines d'emprisonnement inférieures ou égales à un an. Lorsque la peine n'a pas été aménagée à l'audience, la personne condamnée libre peut être convoquée devant le juge de l'application des peines (article 723-15 du Code de procédure pénale).</p>
<h3>En cours de détention</h3>
<p>La personne détenue peut saisir le juge de l'application des peines compétent pour l'établissement où elle se trouve. Pour un détenu de <a href="/avocat-centre-penitentiaire-fleury-merogis/">Fleury-Mérogis</a>, il s'agit du juge du tribunal judiciaire d'Évry-Courcouronnes.</p>
<h2 id="mesures">Les mesures possibles</h2>
<div class="table-wrap"><table><thead><tr><th>Mesure</th><th>Principe</th><th>Point d'attention</th></tr></thead><tbody>
<tr><td><b>Détention à domicile sous surveillance électronique</b></td><td>La peine s'exécute au domicile, avec un bracelet et des horaires fixés.</td><td>Hébergement stable et accord de l'occupant du logement.</td></tr>
<tr><td><b>Semi-liberté</b></td><td>Activité à l'extérieur la journée, retour en établissement le soir.</td><td>Activité justifiée : emploi, formation, soins.</td></tr>
<tr><td><b>Placement à l'extérieur</b></td><td>Exécution de la peine hors de l'établissement, avec un accompagnement.</td><td>Souvent en lien avec une structure associative.</td></tr>
<tr><td><b>Libération conditionnelle</b></td><td>Sortie anticipée sous obligations, en principe à mi-peine (article 729).</td><td>Efforts sérieux de réinsertion à démontrer.</td></tr>
</tbody></table></div>
<h2 id="dossier">Préparer le dossier</h2>
<ol class="steps">
<li><div><b>Justifier d'une activité</b><span>Contrat de travail, promesse d'embauche, inscription en formation.</span></div></li>
<li><div><b>Justifier d'un hébergement</b><span>Attestation d'hébergement, bail, accord pour la surveillance électronique.</span></div></li>
<li><div><b>Montrer l'indemnisation des victimes</b><span>Versements effectués, échéancier, démarches engagées.</span></div></li>
<li><div><b>Documenter le suivi</b><span>Soins, suivi addictologique, attaches familiales.</span></div></li>
</ol>
<div class="callout" style="margin-top:2rem">Le projet compte autant que les conditions légales : un dossier précis et vérifiable rassure le juge.<small>Pour un accompagnement, consultez la page <a href="/avocat-amenagement-de-peine/">avocat en aménagement de peine</a>.</small></div>
<h2 id="recours">Les recours</h2>
<p>Les décisions du juge de l'application des peines peuvent faire l'objet d'un appel devant la chambre de l'application des peines de la cour d'appel, dans des délais brefs fixés par le Code de procédure pénale.</p>
<p class="ref">Sources : Code de procédure pénale, articles 707 et suivants, sur <a href="https://www.legifrance.gouv.fr/" rel="noopener" target="_blank">Légifrance</a> ; fiches pratiques sur <a href="https://www.justice.fr/" rel="noopener" target="_blank">justice.fr</a>.</p>
{{{{FAQ}}}}
</article>
<aside class="toc" aria-label="Sommaire"><span class="eyebrow">Sommaire</span><ol>
<li><a href="#principe">Le principe</a></li><li><a href="#quand">À quel moment</a></li><li><a href="#mesures">Les mesures</a></li>
<li><a href="#dossier">Préparer le dossier</a></li><li><a href="#recours">Les recours</a></li><li><a href="#faq">Questions fréquentes</a></li></ol>
<div class="aside-cta"><span class="cote">Accompagnement</span><p>Préparer une requête et la défendre devant le juge.</p><a class="btn btn--solid" href="/contact/">Prendre contact</a></div></aside>
</div></section>""",
}

# ---------------------------------------------------------------------------- Contact
CONTACT = {
    "slug": "contact", "nav": "Contact", "prio": "P1", "schema_label": "ContactPage",
    "kw": "contacter avocat pénaliste Paris", "kw2": ["rendez-vous avocat pénaliste", "avocat pénaliste Paris 8"],
    "title": "Contacter Me Charles Culioli-Paoli | Avocat à Paris 8e",
    "desc": "Prendre rendez-vous avec Me Charles Culioli-Paoli, avocat pénaliste à Paris 8e : formulaire, WhatsApp ou ligne d'urgence pénale pour une garde à vue.",
    "h1_plain": "Contacter le cabinet",
    "body": f"""<section class="night page-head"><div class="wrap">{{{{CRUMBS}}}}
<span class="cote">Contact</span><h1 style="margin-top:1rem">Contacter <em>le cabinet</em></h1>
<p class="intro">Pour une situation urgente, utilisez la <strong>ligne d'urgence pénale</strong>. Pour toute autre demande, le formulaire ci-dessous permet d'exposer brièvement votre situation.</p>
<div class="hero-actions"><button class="btn btn--solid" type="button" data-urgence>{{{{PHONE}}}} Urgence pénale</button><a class="btn btn--ghost" href="{SITE["whatsapp"]}" target="_blank" rel="noopener">{{{{WA}}}} WhatsApp</a></div></div></section>
<section class="section"><div class="wrap article">
<div>
  <form id="contact-form" novalidate>
    <div class="form-grid">
      <div class="field"><label for="nom">Nom et prénom</label><input id="nom" name="nom" autocomplete="name" required><span class="err" aria-live="polite"></span></div>
      <div class="field"><label for="email">Adresse email</label><input id="email" name="email" type="email" autocomplete="email" required><span class="err" aria-live="polite"></span></div>
      <div class="field"><label for="tel">Téléphone</label><input id="tel" name="tel" type="tel" autocomplete="tel" inputmode="tel" required><span class="err" aria-live="polite"></span></div>
      <div class="field"><label for="objet">Objet de la demande</label><select id="objet" name="objet" required><option value="">Choisir</option><option>Premier rendez-vous</option><option>Convocation reçue</option><option>Proche détenu</option><option>Dossier en cours</option><option>Autre demande</option></select><span class="err" aria-live="polite"></span></div>
      <div class="field field--full"><label for="domaine">Domaine juridique concerné</label><select id="domaine" name="domaine" required><option value="">Choisir</option><option>Droit pénal général</option><option>Droit pénal des affaires</option><option>Extradition</option><option>Droit pénal international</option><option>Aménagement de peine</option><option>Cybercriminalité</option><option>Je ne sais pas</option></select><span class="err" aria-live="polite"></span></div>
      <div class="field field--full"><label for="message">Message</label><textarea id="message" name="message" required aria-describedby="msg-help"></textarea><span class="help" id="msg-help">Décrivez la situation en quelques lignes : nature de la procédure, juridiction, échéance éventuelle. Ne joignez aucune pièce.</span><span class="err" aria-live="polite"></span></div>
      <div class="field--full confid"><b>Confidentialité.</b> Ce formulaire sert uniquement à une première prise de contact. N'y indiquez pas d'informations détaillées sur les faits ni de documents sensibles : ils seront échangés directement avec l'avocat, dans un cadre couvert par le secret professionnel.</div>
      <div class="field--full"><label class="check"><input type="checkbox" id="rgpd" name="rgpd"> <span>J'accepte que mes données soient utilisées pour être recontacté par le cabinet. Elles ne sont jamais transmises à des tiers. <a href="/mentions-legales/#donnees">Politique de confidentialité</a></span></label><span class="err" id="rgpd-err" aria-live="polite" style="color:var(--danger);font-size:.82rem"></span></div>
      <div class="field--full"><button class="btn btn--solid" type="submit">Envoyer la demande {{{{ARROW}}}}</button></div>
    </div>
  </form>
  <div class="form-success" id="form-success" tabindex="-1" hidden><span class="cote">Maquette</span><h2 style="font-size:var(--step-2);margin-top:.5rem">Demande prête à être envoyée</h2><p style="margin-top:.6rem;color:var(--ink-soft)">Dans la maquette, aucune donnée n'est transmise. Sur le site final, le cabinet confirmera la réception par email.</p></div>
</div>
<aside class="toc" aria-label="Coordonnées"><span class="eyebrow">Cabinet</span>
  <dl class="fiche" style="grid-template-columns:1fr">
    <div><dt>Adresse</dt><dd>264 rue du Faubourg Saint-Honoré<br>75008 Paris{T}</dd></div>
    <div><dt>Second bureau</dt><dd>Massy (91){T}</dd></div>
    <div><dt>Rendez-vous</dt><dd>Au cabinet ou en visioconférence</dd></div>
    <div><dt>Langues</dt><dd>Français, anglais, espagnol, arabe, corse</dd></div>
  </dl>
  <div class="aside-cta"><span class="cote">Garde à vue en cours</span><p>N'attendez pas une réponse par email.</p><button class="btn btn--solid" type="button" data-urgence>Urgence pénale</button></div>
</aside>
</div></section>""",
}

# ---------------------------------------------------------------------------- Mentions légales
MENTIONS = {
    "slug": "mentions-legales", "nav": "Mentions légales", "prio": "P3", "noindex": True,
    "kw": "", "title": "Mentions légales | Charles Culioli-Paoli, avocat", "desc": "Mentions légales du site.",
    "h1_plain": "Mentions légales",
    "body": f"""<section class="night page-head"><div class="wrap">{{{{CRUMBS}}}}<h1>Mentions légales</h1></div></section>
<section class="section"><div class="wrap"><div class="prose">
<h2 style="margin-top:0">Éditeur du site</h2>
<p>Me Charles Culioli-Paoli, avocat au Barreau de Paris{T}. Structure d'exercice : à compléter (exercice individuel, SELARL ou autre). Adresse : 264 rue du Faubourg Saint-Honoré, 75008 Paris{T}. Numéro de toque, SIRET et numéro de TVA intracommunautaire : à compléter.</p>
<p>Avocat soumis aux règles de la profession d'avocat : loi n° 71-1130 du 31 décembre 1971, décret n° 2005-790 du 12 juillet 2005 et Règlement intérieur national (RIN), consultables sur le site du <a href="https://www.cnb.avocat.fr/" target="_blank" rel="noopener">Conseil national des barreaux</a>.</p>
<h2>Médiation de la consommation</h2><p>En cas de litige, le client consommateur peut saisir gratuitement le Médiateur de la consommation de la profession d'avocat, après une démarche préalable écrite auprès de l'avocat.</p>
<h2>Hébergement</h2><p>Hébergeur : à compléter (raison sociale, adresse, téléphone).</p>
<h2 id="donnees">Données personnelles</h2><p>Les données transmises via le formulaire sont utilisées uniquement pour répondre à la demande et ne sont pas transmises à des tiers. Durée de conservation, base légale et droits (accès, rectification, effacement, opposition) : à compléter. Réclamation possible auprès de la CNIL.</p>
<h2 id="cookies">Cookies</h2><p>Le site utilise une mesure d'audience respectueuse de la vie privée, sans dépôt de cookie publicitaire. Bandeau de consentement à configurer si un outil soumis à consentement est ajouté.</p>
<h2>Communication de l'avocat</h2><p>Les informations publiées sont générales et ne constituent pas une consultation juridique. Elles ne préjugent pas de l'issue d'un dossier. Les domaines présentés sont des domaines d'activité et non des mentions de spécialisation.</p>
</div></div></section>""",
}

PAGES = [HOME, CABINET, EXP_HUB] + EXPERTISES + [GAV] + LIEUX + [RESSOURCES, GUIDE, CONTACT, MENTIONS]

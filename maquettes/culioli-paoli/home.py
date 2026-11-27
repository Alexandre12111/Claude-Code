# -*- coding: utf-8 -*-
"""Page d'accueil · identité v2 (« Nuit d'audience »)."""
from pages_expertises import EXPERTISES

T = '<span class="todo">à confirmer</span>'

SVG = {
    "cap": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2.5 9 2.5 12 0v-5"/></svg>',
    "bar": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 21h16M6 18V9M10 18V9M14 18V9M18 18V9M3 9l9-6 9 6z"/></svg>',
    "clock": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    "lock": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="3"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>',
    "eye": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
    "search": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
}

PROC = [
    ("Plainte", "Avant l'enquête", "Déposer plainte ou y répondre", "Une plainte rédigée avec un avocat expose clairement les faits et les préjudices. La personne visée peut préparer sa défense dès ce stade.", ["Plainte avec partie civile", "Citation directe"], "/avocat-penaliste-paris/"),
    ("Garde à vue", "24 à 48 heures", "Être assisté dès la première heure", "Entretien confidentiel, consultation de pièces, présence pendant les auditions : le moment le plus exposé de la procédure.", ["Droit au silence", "Entretien de 30 min"], "/avocat-garde-a-vue-paris/"),
    ("Enquête", "Préliminaire ou flagrance", "Anticiper les suites", "Perquisitions, réquisitions, auditions : l'avocat anticipe les orientations possibles du parquet.", ["Perquisition", "Audition libre"], "/avocat-penaliste-paris/"),
    ("Instruction", "Juge d'instruction", "Agir dans le dossier", "Mis en examen ou témoin assisté : demandes d'actes, nullités, demandes de mise en liberté.", ["Mise en examen", "Détention provisoire"], "/avocat-penaliste-paris/"),
    ("Audience", "Tribunal ou cour", "Plaider le dossier", "Tribunal correctionnel, cour criminelle départementale, cour d'assises : une préparation longue, une parole précise.", ["Comparution immédiate", "Cour d'assises"], "/avocat-plaidoirie-penale/"),
    ("Exécution", "Après la décision", "Obtenir un aménagement", "Bracelet électronique, semi-liberté, libération conditionnelle : la défense continue devant le juge de l'application des peines.", ["Bracelet électronique", "Libération conditionnelle"], "/avocat-amenagement-de-peine/"),
]

CHIPS = {
    "avocat-penaliste-paris": ["Garde à vue", "Instruction", "Victimes"],
    "avocat-droit-penal-des-affaires": ["Abus de biens sociaux", "Blanchiment", "Fraude fiscale"],
    "avocat-extradition": ["Mandat d'arrêt européen", "Chambre de l'instruction"],
    "avocat-droit-penal-international": ["Notice rouge Interpol", "Entraide pénale"],
    "avocat-amenagement-de-peine": ["Bracelet", "Semi-liberté", "JAP"],
    "avocat-cybercriminalite": ["Cyberattaque", "Fraude informatique"],
    "avocat-plaidoirie-penale": ["Cour d'assises", "Tribunal correctionnel"],
}
COLORS = ["#0B1424", "#101C33", "#15233E", "#1A2A48", "#1F3152", "#24385C", "#2A3F66"]

TERR = [
    ("75", "Paris", "Cabinet principal. Tribunal judiciaire de Paris, cour d'appel, pôle financier, commissariats parisiens.", "Cabinet principal", "/avocat-penaliste-paris/"),
    ("91", "Essonne", "Tribunal judiciaire d'Évry-Courcouronnes, commissariats de Massy, Corbeil-Essonnes, Palaiseau, Longjumeau, Étampes.", "Bureau de Massy · à confirmer", "/avocat-penaliste-essonne/"),
    ("FM", "Fleury-Mérogis", "Visites au parloir avocat, demandes de mise en liberté, commission de discipline, aménagement de peine.", "Établissement", "/avocat-centre-penitentiaire-fleury-merogis/"),
    ("IDF", "Île-de-France", "Nanterre, Bobigny, Créteil, Versailles, Meaux. Fresnes, Villepinte, Réau, Bois-d'Arcy.", "Juridictions", "/interventions/"),
    ("2B", "Corse", "Ajaccio, Bastia, Borgo. Échanges possibles en langue corse.", "Proposition", "/interventions/"),
]

ETABS = [
    ("Centre pénitentiaire de Fleury-Mérogis", "Essonne (91)", "/avocat-centre-penitentiaire-fleury-merogis/"),
    ("Centre pénitentiaire de Fresnes", "Val-de-Marne (94)", ""),
    ("Prison de Paris-La Santé", "Paris (75)", ""),
    ("Maison d'arrêt de Villepinte", "Seine-Saint-Denis (93)", ""),
    ("Centre pénitentiaire Sud-Francilien, Réau", "Seine-et-Marne (77)", ""),
    ("Maison d'arrêt de Bois-d'Arcy", "Yvelines (78)", ""),
    ("Maison d'arrêt de Nanterre", "Hauts-de-Seine (92)", ""),
    ("Maison d'arrêt du Val-d'Oise, Osny", "Val-d'Oise (95)", ""),
    ("Maison d'arrêt de Lyon-Corbas", "Rhône (69)", ""),
    ("Centre pénitentiaire de Marseille", "Bouches-du-Rhône (13)", ""),
    ("Centre pénitentiaire de Châteauroux", "Indre (36)", ""),
    ("Centre pénitentiaire de Borgo", "Haute-Corse (2B)", ""),
]


def home_body(p):
    words = ["Garde à vue", "Instruction", "Cour d'assises", "Extradition", "Mandat d'arrêt européen", "Aménagement de peine",
             "Droit pénal des affaires", "Cybercriminalité", "Comparution immédiate", "Notice rouge Interpol"]
    marquee = "".join(f"<span>{w}</span>" for w in words) * 2
    pcards = "".join(
        f'''<a class="p-card spot" href="{u}"><span class="n" aria-hidden="true">{i+1:02d}</span><span class="when">{n} · {s}</span>
<h3>{h}</h3><p>{t}</p><span class="chips">{"".join(f'<span class="chip">{c}</span>' for c in ch)}</span><span class="go">En savoir plus {{{{ARROW}}}}</span></a>'''
        for i, (n, s, h, t, ch, u) in enumerate(PROC))
    stack = "".join(
        f'''<a class="s-card" href="/{x["slug"]}/" style="--i:{i};--c:{COLORS[i]}">
  <div class="l"><span class="k">Expertise {i+1:02d} / 07</span><h3>{x["nav"]}</h3><p>{x["somm"]}</p>
    <span class="chips">{"".join(f'<span class="chip">{c}</span>' for c in CHIPS[x["slug"]])}</span></div>
  <div class="r"><span class="cta">{{{{ARROW}}}}</span><span class="big" aria-hidden="true">{i+1:02d}</span></div></a>'''
        for i, x in enumerate(EXPERTISES))
    terr = "".join(
        f'''<a class="ex{" on" if i == 0 else ""}" href="{u}"><span class="dep" aria-hidden="true">{d}</span><h3>{n}</h3><p class="more">{t}</p><span class="chip">{tag}</span></a>'''
        for i, (d, n, t, tag, u) in enumerate(TERR))
    etabs = "".join(
        (f'<li data-k="{n} {l}"><a href="{u}"><span><b>{n}</b><small>{l}</small></span><span class="chip">Page dédiée</span></a></li>' if u else
         f'<li data-k="{n} {l}"><div><span><b>{n}</b><small>{l}</small></span><span class="chip">Bientôt</span></div></li>')
        for n, l, u in ETABS)
    return f"""
<section class="hero grain">
  <div class="hero-glow" aria-hidden="true"></div>
  <span class="hero-mono" aria-hidden="true">CCP</span>
  <div class="wrap">
    <div class="hero-grid">
      <div>
        <span class="hero-tag"><b>Urgence pénale 24h/24</b>Cabinet à Paris 8<sup>e</sup>{T}</span>
        <h1><span class="l"><span>Charles</span></span><span class="l"><span class="g">Culioli-Paoli</span></span></h1>
        <span class="role">Avocat pénaliste à Paris</span>
        <p class="hero-lede">Une défense pénale <strong>exigeante, disponible et discrète</strong>, de la garde à vue à l'exécution de la peine, pour les personnes mises en cause comme pour les victimes.</p>
        <div class="hero-actions">
          <a class="btn btn--solid" href="/contact/">Exposer votre situation {{{{ARROW}}}}</a>
          <button class="btn btn--ghost" type="button" data-urgence>{{{{PHONE}}}} Urgence pénale</button>
        </div>
      </div>
      <div class="hero-card">
        <div class="tilt">
          <div class="hero-photo">
            <img src="assets/img/charles-culioli-paoli-avocat-robe.webp" width="900" height="1317" alt="Me Charles Culioli-Paoli en robe d'avocat au tribunal judiciaire de Paris" fetchpriority="high">
            <div class="cap"><b>Me Charles Culioli-Paoli</b>Tribunal judiciaire de Paris</div>
          </div>
          <div class="float f1"><span class="ic">{SVG["cap"]}</span><span><b>LL.M., Harvard</b>Harvard Law School</span></div>
          <div class="float f2"><span class="ic">{SVG["bar"]}</span><span><b>Barreau de Paris</b>New York{T}</span></div>
          <div class="float f3"><span class="ic">{SVG["cap"]}</span><span><b>Sciences Po</b>École de droit</span></div>
        </div>
      </div>
    </div>
  </div>
  <div class="marquee" aria-hidden="true"><div class="marquee-track">{marquee}</div></div>
  <div class="wrap">
    <div class="stats stagger">
      <div class="stat"><b data-count="7">7</b><span>domaines d'intervention en droit pénal</span></div>
      <div class="stat"><b data-count="6">6</b><span>étapes de la procédure couvertes</span></div>
      <div class="stat"><b data-count="5">5</b><span>langues de travail, dont le corse{T}</span></div>
      <div class="stat"><b data-count="24" data-suf="h">24h</b><span>sur 24 pour les urgences pénales{T}</span></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="sec-head"><div><span class="cote">L'approche</span><h2 class="sec-title">Une défense <em>sur mesure</em></h2></div>
      <p>Un avocat indépendant, un interlocuteur unique. Chaque dossier est lu, compris et défendu personnellement par Charles Culioli-Paoli.</p></div>
    <div class="bento stagger">
      <div class="b-card b-quote spot">
        <span class="cote" style="color:var(--gold)">Manifeste</span>
        <p>Chaque dossier pénal raconte une histoire incomplète. Mon travail est de la <em>lire entièrement</em>, puis de la rendre audible devant le juge.</p>
        <div class="sig"><img src="assets/img/charles-culioli-paoli-portrait.webp" width="400" height="400" alt="" loading="lazy"><span><b>Charles Culioli-Paoli</b>Avocat au Barreau de Paris · citation à valider</span></div>
      </div>
      <div class="b-card b-1 spot"><span class="ic">{SVG["eye"]}</span><h3>Rigueur</h3><p>Lecture intégrale de la procédure, contrôle de chaque acte, stratégie expliquée sans détour.</p></div>
      <div class="b-card b-2 spot"><span class="ic">{SVG["clock"]}</span><h3>Disponibilité</h3><p>Intervention rapide en garde à vue, joignable aux moments où tout se décide.</p></div>
      <div class="b-card b-1 spot"><span class="ic">{SVG["lock"]}</span><h3>Discrétion</h3><p>Le secret professionnel comme règle de travail, pour les particuliers comme pour les dirigeants.</p></div>
      <div class="b-card b-2 spot" style="grid-column:span 7"><span class="ic">{SVG["cap"]}</span><h3>Culture internationale</h3><p>Formé à Harvard Law School et à Sciences Po, à l'aise avec les dossiers qui franchissent les frontières.</p></div>
      <div class="b-card b-wide"><h3>Garde à vue, convocation, mise en examen&nbsp;?</h3><a class="btn" href="/contact/">Parler de votre dossier {{{{ARROW}}}}</a></div>
    </div>
  </div>
</section>

<section class="dark abyss hs" aria-label="Les étapes de la procédure pénale">
  <div class="hs-sticky">
    <div class="hs-track">
      <div class="hs-intro"><span class="cote">La procédure</span><h2 class="sec-title">À chaque <em>étape</em>, une même défense</h2><p>Faites défiler : de la plainte à l'exécution de la peine, découvrez comment l'avocat intervient.</p></div>
      {pcards}
    </div>
    <div class="hs-bar" aria-hidden="true"><i></i></div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="sec-head"><div><span class="cote">Expertises</span><h2 class="sec-title">Sept domaines, <em>un seul cap</em></h2></div>
      <p>Du droit pénal général aux dossiers internationaux, chaque domaine dispose d'une page détaillée.</p></div>
    <div class="stack">{stack}</div>
  </div>
</section>

<section class="section" style="background:var(--paper-2)">
  <div class="wrap parcours">
    <div class="portrait reveal"><img src="assets/img/charles-culioli-paoli-portrait.webp" width="400" height="400" loading="lazy" alt="Portrait de Me Charles Culioli-Paoli, avocat pénaliste à Paris"><span class="badge">Prestation de serment 2025{T}</span></div>
    <div class="reveal">
      <span class="cote">Le parcours</span>
      <h2 class="sec-title" style="font-size:var(--step-3);max-width:20ch">Paris, Cambridge, <em>le Parquet</em></h2>
      <div class="flips">
        <button class="flip" type="button" aria-pressed="false" aria-label="LL.M., Harvard Law School : afficher le détail">
          <span class="flip-in"><span class="flip-f"><span class="hint"><span>Cambridge, MA</span><span>Retourner ↻</span></span><span class="deg">LL.M.</span><span class="sch">Harvard Law School</span></span>
          <span class="flip-b"><b>Common law et procédure américaine</b><p>Une formation utile aux dossiers internationaux, à l'extradition et au dialogue avec les confrères étrangers.{T}</p></span></span></button>
        <button class="flip" type="button" aria-pressed="false" aria-label="Master, École de droit de Sciences Po : afficher le détail">
          <span class="flip-in"><span class="flip-f"><span class="hint"><span>Paris</span><span>Retourner ↻</span></span><span class="deg">Master</span><span class="sch">École de droit de Sciences Po</span></span>
          <span class="flip-b"><b>Contentieux et international</b><p>Une formation exigeante, ouverte sur le contentieux, les libertés et l'international.{T}</p></span></span></button>
      </div>
      <ul class="timeline">
        <li><b>Fedida &amp; Associés</b><span>Défense pénale{T}</span></li>
        <li><b>Schapira Associés</b><span>Défense pénale{T}</span></li>
        <li><b>Parquet de Paris</b><span>Ministère public{T}</span></li>
        <li><b>Ministère de l'Économie</b><span>Administration{T}</span></li>
      </ul>
      <div class="langs" aria-label="Langues de travail"><span>Français</span><span lang="en">English</span><span lang="es">Español</span><span lang="ar">العربية</span><span lang="co">Corsu</span></div>
      <p style="margin-top:1.8rem"><a class="link-arrow" href="/charles-culioli-paoli-avocat/">Découvrir le parcours complet {{{{ARROW}}}}</a></p>
    </div>
  </div>
</section>

<section class="dark section grain">
  <div class="wrap">
    <div class="sec-head"><div><span class="cote">Territoires</span><h2 class="sec-title">Où le cabinet <em>intervient</em></h2></div>
      <p>Paris en priorité, l'Essonne en second cœur d'activité, et partout en France lorsque la défense l'exige.</p></div>
    <div class="expand">{terr}</div>
    <div class="finder">
      <div><span class="cote">Établissements pénitentiaires</span><h3 style="margin-top:.8rem">Un proche est détenu&nbsp;?</h3>
        <p>Recherchez l'établissement : le cabinet se rend en détention pour les demandes de mise en liberté, la discipline et l'aménagement de peine.</p>
        <div class="search"><label for="etab-q">Rechercher un établissement</label>{SVG["search"]}<input id="etab-q" type="search" placeholder="Fleury-Mérogis, Fresnes, Lyon…" autocomplete="off"></div>
        <p style="margin-top:1.2rem"><a class="link-arrow" href="/etablissements-penitentiaires/" style="color:var(--gold-2)">Tous les établissements {{{{ARROW}}}}</a></p></div>
      <ul class="results" aria-live="polite">{etabs}<li class="empty" hidden>Aucun établissement trouvé. Contactez le cabinet, il intervient dans toute la France.</li></ul>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="sec-head"><div><span class="cote">Ressources</span><h2 class="sec-title">Comprendre <em>avant d'agir</em></h2></div>
      <a class="btn btn--ghost" href="/ressources/" style="border-color:var(--rule)">Toutes les ressources {{{{ARROW}}}}</a></div>
    <div class="covers stagger">
      <a class="cover cover--gold" href="/guide-amenagement-de-peine/"><span class="meta"><span>Guide</span><span>6 min</span></span><h3>Aménagement de peine : conditions, procédure et délais</h3><span class="glyph" aria-hidden="true">A</span></a>
      <a class="cover" href="/avocat-garde-a-vue-paris/"><span class="meta"><span>Procédure</span><span>4 min</span></span><h3>Garde à vue : vos droits et le rôle de l'avocat</h3><span class="glyph" aria-hidden="true">G</span></a>
      <a class="cover cover--ink" href="/avocat-extradition/"><span class="meta"><span>International</span><span>5 min</span></span><h3>Mandat d'arrêt européen : les premières heures</h3><span class="glyph" aria-hidden="true">E</span></a>
    </div>
  </div>
</section>

<section class="section--tight" style="padding-top:0">
  <div class="wrap">
    <div class="final grain reveal">
      <div class="txt"><span class="cote">Prendre contact</span><h2>Un proche est en <em>garde à vue</em>&nbsp;?</h2>
        <p>Le cabinet intervient en urgence à Paris et en Essonne. Pour toute autre demande, décrivez votre situation : vous serez recontacté rapidement.</p>
        <div class="hero-actions"><button class="btn btn--solid" type="button" data-urgence>{{{{PHONE}}}} Afficher le numéro d'urgence</button><a class="btn btn--ghost" href="/contact/">Écrire au cabinet {{{{ARROW}}}}</a></div></div>
      <div class="img"><img src="assets/img/charles-culioli-paoli-tribunal-paris.webp" width="1600" height="1200" loading="lazy" alt="Charles Culioli-Paoli dans la salle des pas perdus du tribunal judiciaire de Paris"></div>
    </div>
  </div>
</section>"""

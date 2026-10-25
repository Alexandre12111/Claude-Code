"""Pages secteur, locale, réalisations, étude de cas, tarifs, agence, contact, conseils et article."""
from composants import *

# Exemple de design pour un cabinet d'avocats (maquette d'illustration, pas un client)
AVOCAT = """<div class="browser law"><div class="browser-bar"><i></i><i></i><i></i><span>exemple · cabinet-avocats.fr</span></div>
  <div class="law-nav"><b>MOREL <small>&amp; ASSOCIÉS</small></b><span>Expertises</span><span>Le cabinet</span><span>Honoraires</span><i>Prendre rendez vous</i></div>
  <div class="law-hero"><div><small>Avocats au barreau de Paris</small><strong>Droit des affaires<br>et droit social.</strong><p>Conseil et contentieux pour dirigeants et entreprises.</p><i>Consultation sous 48h</i></div><span class="law-seal">M&amp;A</span></div>
  <div class="law-cards"><span><b>Droit des sociétés</b>Création, cession</span><span><b>Droit social</b>Contrats, litiges</span><span><b>Contentieux</b>Procédures</span></div>
</div>"""


def secteur():
    visuel = f"""{AVOCAT}
    <div class="phone law-phone"><div class="phone-view"><div class="inbox">
      <h4>Rendez vous</h4>
      <div class="msg"><i>J</i><div><b>Consultation droit social</b><span>Jeudi 10h · cabinet</span></div></div>
      <div class="msg"><i>S</i><div><b>Création de société</b><span>Lundi 14h · visio</span></div></div>
      <div class="msg"><i>P</i><div><b>Rupture conventionnelle</b><span>Mardi 9h30</span></div></div>
    </div></div></div>
    <span class="vis-note">Exemple de design</span>"""
    corps = hero([("Secteurs", "creation-site-vitrine.html"), ("Avocats", "")], "Site internet pour avocat",
                 'Création de site internet pour <span class="gold">avocat</span> et cabinet.',
                 "Un site qui inspire confiance, présente clairement vos domaines d'intervention et facilite la prise de rendez vous, dans le respect des règles de communication de la profession.",
                 visuel, [("Obtenir mon devis", "contact.html", True), ("Voir les tarifs", "tarifs.html", False)])
    corps += avantages("Pensé pour les cabinets", 'Ce qu\'un client attend du site de son <span class="gold">avocat</span>.', "Avant d'appeler, un client veut comprendre si vous traitez son problème, combien cela peut coûter et comment vous joindre.",
                       [("seal", "Domaines d'intervention clairs", "Une page par expertise, rédigée pour les questions réelles de vos clients et pour Google."),
                        ("calendar", "Prise de rendez vous en ligne", "Consultation au cabinet ou en visio, rappels automatiques et paiement possible en ligne."),
                        ("shield", "Conformité déontologique", "Mentions obligatoires, informations sur les honoraires et ton conforme au règlement intérieur national."),
                        ("search", "Référencement local", "Fiche Google, pages par ville et par expertise pour être trouvé près de chez vos clients."),
                        ("user", "Présentation des associés", "Parcours, publications et spécialités, pour que le client choisisse son interlocuteur.")])
    corps += galerie("Références", 'Des sites qui <span class="gold">inspirent confiance</span>.', "Professions réglementées, institutions et cabinets de conseil nous confient leur image.", ["havel-trading", "verisol", "xpert-medical"])
    corps += section(entete("Autres secteurs", 'Un site adapté à <span class="gold">votre métier</span>.', centre=True) + """
<div class="sectors">""" + "".join(f'<a class="sector rv" href="#"><div class="sector-img"><img src="assets/realisations/{img}.webp" alt="" width="1200" height="620" loading="lazy"></div><div class="sector-txt"><div><b>{n}</b><span>{d}</span></div><svg><use href="#i-out"/></svg></div></a>' for n, d, img in [
        ("Professions de santé", "Médecins, kinés, praticiens", "xpert-medical"), ("Architectes", "Portfolio et projets", "portugal-expert"), ("Restaurants", "Carte, réservation, avis", "villa-moniris"),
        ("Artisans et BTP", "Devis et chantiers", "novexia"), ("Coachs et consultants", "Expertise et rendez vous", "eddy-bilou"), ("Commerces", "Boutique et prise de rendez vous", "barber-korner")]) + "</div>")
    corps += faq('Les questions des <span class="gold">avocats</span>.', [
        ("Un avocat peut il faire de la publicité sur son site ?", "Oui, la publicité personnelle est autorisée depuis 2014, dans le respect des principes essentiels de la profession : dignité, loyauté, confidentialité. Le site est rédigé en conséquence."),
        ("Peut on afficher ses honoraires ?", "Oui, et c'est recommandé : une page honoraires claire (forfaits, taux horaire, convention) rassure et qualifie les demandes."),
        ("Combien coûte le site d'un cabinet ?", "À partir de 1 000 € HT pour un site vitrine sur mesure ; plus selon le nombre d'expertises et la prise de rendez vous en ligne."),
        ("Combien de temps pour être en ligne ?", "Environ 4 semaines, rédaction des pages d'expertise comprise.")])
    corps += appel("Votre cabinet", 'Un site à la hauteur de votre <span class="gold">cabinet</span>.', "Présentez nous votre cabinet : proposition détaillée sous 24h.")
    page("site-internet-avocat.html", "Site internet avocat : création pour cabinet | Aurea",
         "Création de site internet pour avocat : pages domaines d'intervention, prise de rendez vous en ligne et respect des règles de la profession.", corps)


def local():
    carte = """<div class="carte-idf" aria-hidden="true">
      <svg viewBox="0 0 520 380" role="presentation">
        <defs><radialGradient id="lum" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#C9A961" stop-opacity=".35"/><stop offset="1" stop-color="#C9A961" stop-opacity="0"/></radialGradient></defs>
        <ellipse cx="260" cy="190" rx="230" ry="160" fill="none" stroke="rgba(245,245,240,.08)" stroke-dasharray="4 6"/>
        <ellipse cx="260" cy="190" rx="150" ry="104" fill="none" stroke="rgba(245,245,240,.12)"/>
        <ellipse cx="262" cy="182" rx="74" ry="52" fill="rgba(201,169,97,.06)" stroke="rgba(201,169,97,.55)" stroke-width="1.5"/>
        <path d="M20 150 C90 120 140 210 200 175 S300 120 330 170 S420 240 500 205" fill="none" stroke="#3b6b8f" stroke-width="5" stroke-linecap="round" opacity=".55"/>
        <circle cx="262" cy="182" r="90" fill="url(#lum)"/>
        <g font-family="Geist Mono" font-size="11" fill="#A9A9A2"><text x="244" y="160">PARIS</text><text x="150" y="232">Boulogne</text><text x="226" y="290">Clamart</text><text x="96" y="140">Hauts de Seine</text><text x="355" y="120">Seine Saint Denis</text><text x="345" y="300">Val de Marne</text></g>
        <g fill="#C9A961"><circle cx="246" cy="172" r="7"/><circle cx="168" cy="220" r="5"/><circle cx="236" cy="276" r="5"/><circle cx="300" cy="196" r="4"/><circle cx="200" cy="150" r="4"/></g>
        <circle cx="246" cy="172" r="14" fill="none" stroke="#C9A961" opacity=".5"><animate attributeName="r" values="8;22;8" dur="3s" repeatCount="indefinite"/><animate attributeName="opacity" values=".6;0;.6" dur="3s" repeatCount="indefinite"/></circle>
      </svg>
    </div>""" + puce("pin", "Basé à Paris", "Île de France et Europe", "top:8px;right:0") + puce("calendar", "Rendez vous sur place", "ou en visio, au choix", "bottom:10px;left:0", ok=True)
    corps = hero([("Agence web Paris", "")], "Agence web à Paris",
                 'Agence web à Paris, un studio <span class="gold">indépendant</span>.',
                 "Aurea Media crée des sites vitrines et e-commerce sur mesure pour les entreprises parisiennes et franciliennes. Un interlocuteur unique, des rendez vous sur place ou en visio, et un devis sous 24h.",
                 carte)
    corps += LOGOS
    corps += avantages("Pourquoi un studio parisien", 'La réactivité d\'un indépendant, la rigueur d\'une <span class="gold">grande agence</span>.', "Vous travaillez directement avec Alexandre Coury, de l'audit à la mise en ligne.",
                       [("pin", "Proche de vous", "Rendez vous à Paris, chez vous en Île de France ou en visio."),
                        ("search", "Référencement local", "Fiche Google Business Profile, pages par quartier et par ville pour être trouvé à Paris."),
                        ("user", "Un seul interlocuteur", "Pas de commercial, pas de sous traitance : la personne qui vous écoute est celle qui construit."),
                        ("clock", "Délais tenus", "Un site vitrine en ligne en 4 semaines, planning validé dès le départ.")])
    corps += galerie("Réalisations", 'Des projets à <span class="gold">Paris</span> et au delà.', "Entreprises parisiennes, mais aussi Genève, Lisbonne et toute la France.", ["eddy-bilou", "barber-korner", "maryes"])
    corps += section(entete("Avis clients", 'Ils en parlent <span class="gold">mieux</span> que nous.') + "\n" + AVIS)
    corps += faq('Les questions sur l\'agence à <span class="gold">Paris</span>.', [
        ("Peut on se rencontrer ?", "Oui, à Paris ou en Île de France, ou en visio si vous préférez. La plupart des projets se font avec 2 ou 3 rendez vous."),
        ("Travaillez vous uniquement à Paris ?", "Non : la majorité de nos clients sont en Île de France, mais nous travaillons partout en France et en Europe."),
        ("Combien coûte un site à Paris ?", "Nos tarifs sont les mêmes partout : site vitrine dès 1 000 € HT, e-commerce dès 1 650 € HT.")])
    corps += appel("Votre projet à Paris", 'On se <span class="gold">rencontre</span> ?', "Proposez un créneau : rendez vous à Paris ou en visio, devis sous 24h.", "Prendre rendez vous")
    page("agence-web-paris.html", "Agence web Paris : création de site internet sur mesure",
         "Agence web à Paris : création de sites vitrines et e-commerce sur mesure, rendez vous sur place ou en visio. Devis gratuit et détaillé sous 24h.", corps)


def realisations():
    types = ["Tous", "Site vitrine", "Site e-commerce"]
    filtres = "".join(f'<button type="button" class="filtre{" is-on" if i == 0 else ""}" data-filtre="{"" if i == 0 else t}">{t}</button>' for i, t in enumerate(types))
    cartes = "".join(carte_projet(c, REAL[c][4] + " · " + REAL[c][2], fiche=(c == "havel-trading")) for c in REAL)
    corps = f"""<section class="phero phero--court">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap">
    {fil(("Réalisations", ""))}
    <span class="eyebrow rv">Réalisations</span>
    <h1 class="split-words">Des sites qui <span class="gold">travaillent</span> pour nos clients.</h1>
    <div class="phero-bas"><p class="lead rv">Sites vitrines, boutiques en ligne et identités créés pour des entreprises en France et en Europe. Chaque projet part d'une page blanche.</p>
    <div class="kpis rv"><div><b>45+</b><span>projets livrés</span></div><div><b>12+</b><span>secteurs</span></div><div><b>4,9</b><span>sur Google</span></div></div></div>
  </div>
</section>
<section class="sec" style="padding-top:20px">
  <div class="wrap">
    <div class="filtres rv" role="group" aria-label="Filtrer les réalisations">{filtres}<span class="filtres-n" aria-live="polite"></span></div>
    <div class="gal gal--filtre">{cartes}</div>
  </div>
</section>"""
    corps += LOGOS
    corps += appel("Votre projet", 'Le prochain, c\'est <span class="gold">le vôtre</span> ?', "Parlez nous de votre activité : proposition et planning sous 24h.")
    page("realisations.html", "Réalisations : sites vitrines et e-commerce | Aurea Media",
         "Découvrez nos réalisations : sites vitrines, boutiques en ligne et identités créés pour des entreprises en France et en Europe. 45+ projets livrés.", corps)


def etude_de_cas():
    corps = f"""<section class="phero phero--court">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap">
    {fil(("Réalisations", "realisations.html"), ("Havel Trading", ""))}
    <span class="eyebrow rv">Étude de cas · Site vitrine</span>
    <h1 class="split-words">Havel Trading : un site <span class="gold">institutionnel</span> pour un négociant d'énergie.</h1>
    <div class="cas-infos rv">
      <div><small>Client</small><b>Havel Trading</b></div><div><small>Lieu</small><b>Genève, Suisse</b></div><div><small>Secteur</small><b>Négoce d'énergie</b></div><div><small>Livrables</small><b>Site vitrine, SEO</b></div>
      <a class="btn btn--ghost" href="https://haveltrading.com/" target="_blank" rel="noopener">Voir le site {ico("out")}</a>
    </div>
  </div>
</section>
<section class="sec" style="padding-top:10px">
  <div class="wrap">
    <div class="cas-hero rv">
      <div class="laptop"><div class="laptop-screen"><div class="laptop-view"><img class="is-on" src="assets/realisations/havel-trading.webp" alt="Page d'accueil du site Havel Trading" width="1200" height="620"></div></div><div class="laptop-base"></div></div>
    </div>
  </div>
</section>
<section class="sec paper">
  <div class="gridbg" aria-hidden="true"></div>
  <div class="wrap">
    <div class="cas-texte">
      <aside class="cas-sommaire rv"><span class="eyebrow">Sommaire</span><a href="#enjeu">L'enjeu</a><a href="#solution">Notre réponse</a><a href="#resultat">Le résultat</a></aside>
      <div class="cas-corps">
        <h2 id="enjeu" class="rv">L'enjeu</h2>
        <p class="rv">Havel Trading est une société indépendante de négoce physique de gaz naturel, de GNL et d'électricité, basée à Genève. Ses interlocuteurs sont des producteurs, des énergéticiens et des banques : le site devait inspirer immédiatement la confiance d'un acteur établi, en anglais, sans tomber dans le cliché du site financier générique.</p>
        <h2 id="solution" class="rv">Notre réponse</h2>
        <p class="rv">Une direction sobre et maritime : grands visuels de terminaux et de méthaniers, typographie éditoriale, palette marine et dorée. L'arborescence va droit à l'essentiel : mission et valeurs, équipe, activités, contact. Chaque page a été rédigée et balisée pour le référencement international.</p>
        <ul class="cas-liste rv"><li>{ico("check")}Site WordPress sur mesure, en anglais</li><li>{ico("check")}Pages équipe et valeurs pour rassurer les partenaires</li><li>{ico("check")}Référencement technique et données structurées</li></ul>
        <h2 id="resultat" class="rv">Le résultat</h2>
        <p class="rv">Un site institutionnel rapide et sobre, qui sert de carte de visite lors des premières prises de contact avec les contreparties.</p>
        <p class="cas-note rv">Texte d'exemple : le contenu de chaque étude de cas se rédige dans sa fiche, depuis Aurea Studio.</p>
      </div>
    </div>
  </div>
</section>
<section class="sec">
  <div class="wrap">
    {entete("Sur tous les écrans", 'Pensé pour le <span class="gold">mobile</span> d\'abord.')}
    <div class="cas-ecrans rv">
      {navigateur("havel-trading")}
      <div class="phone"><div class="phone-view"><img src="assets/realisations/havel-trading.webp" alt="" width="1200" height="620" loading="lazy" style="object-position:8% top;transform:scale(1.02)"></div></div>
    </div>
  </div>
</section>
<section class="sec paper">
  <div class="gridbg" aria-hidden="true"></div>
  <div class="wrap">
    {entete("Projet suivant", 'D\'autres <span class="gold">réalisations</span>.')}
    <div class="gal">{carte_projet("portugal-expert", "Lisbonne")}{carte_projet("verisol", "France")}{carte_projet("sunrock", "France")}</div>
  </div>
</section>"""
    corps += appel("Votre projet", 'Et si on écrivait <span class="gold">votre</span> étude de cas ?', "Décrivez votre projet : proposition et planning sous 24h.")
    page("realisation-havel-trading.html", "Site Havel Trading : étude de cas site vitrine | Aurea",
         "Étude de cas : création du site vitrine institutionnel de Havel Trading, négociant d'énergie à Genève. Enjeux, design, développement et résultat.", corps, "assets/realisations/havel-trading.webp")


def tarifs():
    options = [("Page supplémentaire", "Rédaction et design d'une page en plus du forfait"), ("Site multilingue", "Version anglaise ou autre langue, avec hreflang"), ("Blog et articles SEO", "Rédaction d'articles optimisés chaque mois"),
               ("Réservation en ligne", "Rendez vous, tables ou chambres, avec rappels"), ("Logo et charte", "Identité visuelle complète, fichiers sources"), ("Maintenance mensuelle", "Mises à jour, sauvegardes, sécurité, petites modifications")]
    opts = "".join(f'<div class="option rv"><div><b>{n}</b><span>{d}</span></div><em>Sur devis</em></div>' for n, d in options)
    corps = f"""<section class="phero phero--court">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap">
    {fil(("Tarifs", ""))}
    <span class="eyebrow rv">Tarifs</span>
    <h1 class="split-words">Combien coûte votre <span class="gold">site internet</span> ?</h1>
    <p class="lead rv" style="margin-top:24px">Des prix affichés, un devis détaillé ligne par ligne et aucun coût caché. Le référencement technique est toujours inclus.</p>
  </div>
</section>
<section class="sec paper" style="padding-top:110px">
  <div class="wrap">{PRIX}</div>
</section>
<section class="sec">
  <div class="wrap">
    {entete("Inclus dans chaque site", 'Ce que comprend le <span class="gold">prix</span>.', "Pas de mauvaise surprise : voici ce qui est compris dans tous nos forfaits.")}
    <div class="inclus rv">{"".join(f"<span>{ico('check')}{t}</span>" for t in ["Design 100 % sur mesure", "Version mobile et tablette", "Référencement technique", "Données structurées Schema.org", "Formulaire de contact", "Search Console configurée", "Formation à l'administration", "Hébergement conseillé à votre nom", "Site et contenus qui vous appartiennent"])}</div>
  </div>
</section>
<section class="sec paper">
  <div class="gridbg" aria-hidden="true"></div>
  <div class="wrap">
    {entete("Options", 'Pour aller <span class="gold">plus loin</span>.', "Chaque option est chiffrée précisément dans votre devis, selon votre besoin.")}
    <div class="options">{opts}</div>
  </div>
</section>"""
    corps += faq('Les questions sur les <span class="gold">prix</span>.', [
        ("Pourquoi « à partir de » ?", "Le prix final dépend du nombre de pages, des fonctionnalités et de la rédaction. Le devis détaillé fixe le prix avant le début du projet : il ne bouge plus ensuite."),
        ("Comment se passe le paiement ?", "Les modalités et l'échéancier sont indiqués dans le devis, avant tout engagement."),
        ("Y a t il un abonnement ?", "Non. Seuls l'hébergement et le nom de domaine sont annuels, à votre nom. La maintenance est optionnelle."),
        ("Le référencement est il en plus ?", "Le référencement technique est inclus. La rédaction d'articles réguliers est une option.")])
    corps += appel("Votre devis", 'Un prix <span class="gold">précis</span> sous 24h.', "Décrivez votre projet en quelques lignes : devis détaillé, gratuit et sans engagement.")
    page("tarifs.html", "Prix d'un site vitrine et e-commerce en 2026 | Aurea Media",
         "Prix d'un site vitrine dès 1 000 € HT, site e-commerce dès 1 650 € HT : ce qui est inclus, les options et le détail ligne par ligne. Devis 24h.", corps)


def agence():
    fondateur = INDEX[INDEX.index('<div class="founder">'):INDEX.index('<div class="nums">')]
    nums = INDEX[INDEX.index('<div class="nums">'):INDEX.index("</div>\n  </div>\n</section>\n\n<!-- AVIS")]
    outils = ["WordPress", "WooCommerce", "Figma", "Rank Math", "Google Search Console", "After Effects"]
    corps = f"""<section class="phero phero--court">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap">
    {fil(("L'agence", ""))}
    <span class="eyebrow rv">L'agence</span>
    <h1 class="split-words">Un studio <span class="gold">indépendant</span>, fondé en 2021.</h1>
    <p class="lead rv" style="margin-top:24px">Aurea Media conçoit des sites sur mesure pour les indépendants, TPE et PME qui veulent une présence en ligne à la hauteur de leur savoir faire.</p>
  </div>
</section>
<section class="sec" style="padding-top:20px"><div class="wrap">{fondateur.replace('Un studio <span class="nw"><span class="gold">indépendant</span>,</span> une exigence de grande agence.', 'Alexandre Coury, <span class="gold">développeur</span> et designer.').replace("L'agence</span>", "Le fondateur</span>")}{nums}</div></div></section>
<section class="sec paper">
  <div class="gridbg" aria-hidden="true"></div>
  <div class="wrap">
    {entete("Nos engagements", 'Ce qui ne <span class="gold">change jamais</span>.', centre=True)}
    <div class="valeurs">
      <article class="rv"><div class="ico">{ico("user")}</div><h3>Un seul interlocuteur</h3><p>Pas de commercial ni de sous traitance. Vous parlez à la personne qui conçoit votre site.</p></article>
      <article class="rv" style="--d:.1s"><div class="ico">{ico("seal")}</div><h3>Du sur mesure, toujours</h3><p>Aucun template acheté. Chaque site est dessiné pour son marché et ses clients.</p></article>
      <article class="rv" style="--d:.2s"><div class="ico">{ico("clock")}</div><h3>Des délais tenus</h3><p>Un planning validé au départ et respecté. Vous savez toujours où en est votre projet.</p></article>
      <article class="rv"><div class="ico">{ico("shield")}</div><h3>Vous êtes propriétaire</h3><p>Le site, les contenus et les accès sont à votre nom. Aucun abonnement imposé.</p></article>
      <article class="rv" style="--d:.1s"><div class="ico">{ico("chart")}</div><h3>Des résultats mesurés</h3><p>Search Console et statistiques configurées : on suit les demandes générées, pas les impressions.</p></article>
      <article class="rv" style="--d:.2s"><div class="ico">{ico("globe")}</div><h3>France et Europe</h3><p>Des clients à Paris, Genève, Lisbonne et Bruxelles, en français et en anglais.</p></article>
    </div>
  </div>
</section>
<section class="sec">
  <div class="wrap">
    {entete("Nos outils", 'Des outils <span class="gold">éprouvés</span>, choisis pour vous.', "Des technologies standard, documentées et maintenues : votre site n'est jamais prisonnier d'une agence.")}
    <div class="outils rv">{"".join(f"<span>{o}</span>" for o in outils)}</div>
  </div>
</section>"""
    corps += LOGOS
    corps += appel("Travaillons ensemble", 'Faisons <span class="gold">connaissance</span>.', "Un premier échange de 20 minutes pour comprendre votre projet, sans engagement.", "Prendre contact")
    page("agence.html", "Aurea Media, agence web indépendante fondée par A. Coury",
         "Aurea Media est une agence web indépendante fondée en 2021 par Alexandre Coury, développeur et designer. Méthode, valeurs et engagements.", corps, "assets/alexandre-coury.webp")


def contact():
    besoins = ["Site vitrine", "Site e-commerce", "Refonte", "Identité visuelle", "Motion design", "Présentation", "Autre"]
    budgets = ["Moins de 1 000 €", "1 000 à 1 800 €", "1 800 à 2 750 €", "Plus de 2 750 €", "Je ne sais pas"]
    puces = lambda nom, liste, typ: "".join(f'<label class="choix"><input type="{typ}" name="{nom}" value="{v}"><span>{v}</span></label>' for v in liste)
    corps = f"""<section class="phero phero--court phero--contact">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap">
    {fil(("Contact", ""))}
    <div class="contact-grid">
      <div>
        <span class="eyebrow rv">Contact</span>
        <h1 class="split-words">Parlons de votre <span class="gold">projet</span>.</h1>
        <p class="lead rv" style="margin:22px 0 34px">Décrivez votre projet en quelques lignes : vous recevez une première analyse, une estimation de budget et un délai sous 24h ouvrées.</p>
        <div class="contact-infos rv">
          <div class="ci-carte"><span class="ci">{ico("phone")}</span><div><small>Téléphone</small><b>06 51 28 77 61</b><span>Du lundi au vendredi, 9h à 19h</span></div></div>
          <div class="ci-carte"><span class="ci">{ico("mail")}</span><div><small>E-mail</small><b>alexandre.coury@aurea-media.fr</b><span>Réponse personnelle sous 24h</span></div></div>
          <div class="ci-carte"><span class="ci">{ico("pin")}</span><div><small>Adresse</small><b>60 rue François Ier, 75008 Paris</b><span>Sur rendez vous, ou en visio</span></div></div>
        </div>
        <div class="contact-moi rv"><img src="assets/alexandre-coury.webp" alt="" width="1024" height="800" loading="lazy"><p>« Je lis chaque demande moi même et vous réponds personnellement, jamais avec un message automatique. »<b>Alexandre Coury, fondateur</b></p></div>
      </div>
      <form class="formulaire rv" id="form-contact" novalidate>
        <div class="f-duo">
          <div class="f-champ"><label for="f-nom">Nom et prénom <em>*</em></label><input id="f-nom" name="nom" autocomplete="name" required></div>
          <div class="f-champ"><label for="f-tel">Téléphone <span>facultatif</span></label><input id="f-tel" name="tel" type="tel" autocomplete="tel"></div>
        </div>
        <div class="f-champ"><label for="f-mail">Adresse e-mail <em>*</em></label><input id="f-mail" name="mail" type="email" autocomplete="email" required><small class="f-err" id="f-mail-err" hidden>Saisissez une adresse e-mail valide, par exemple nom@entreprise.fr.</small></div>
        <fieldset class="f-champ"><legend>Votre besoin</legend><div class="choix-liste">{puces("besoin", besoins, "checkbox")}</div></fieldset>
        <fieldset class="f-champ"><legend>Budget envisagé <span>facultatif</span></legend><div class="choix-liste">{puces("budget", budgets, "radio")}</div></fieldset>
        <div class="f-champ"><label for="f-msg">Décrivez votre projet <em>*</em></label><textarea id="f-msg" name="message" rows="5" required placeholder="Votre activité, vos objectifs, votre site actuel s'il existe…"></textarea></div>
        <button class="btn" type="submit">Recevoir mon devis gratuit {ico("arrow")}</button>
        <p class="f-note">{ico("shield")}Vos données servent uniquement à répondre à votre demande. Aucun démarchage, aucun partage.</p>
        <div class="f-ok" hidden role="status"><span class="ci ok">{ico("check")}</span><div><b>Merci, votre demande est bien envoyée.</b><span>Réponse personnelle sous 24h ouvrées. Exemple de maquette : rien n'a été transmis.</span></div></div>
      </form>
    </div>
  </div>
</section>"""
    corps += faq('Avant de nous <span class="gold">écrire</span>.', [
        ("Le devis est il vraiment gratuit ?", "Oui, gratuit et sans engagement. Vous recevez un devis détaillé ligne par ligne sous 24h ouvrées."),
        ("Que se passe t il après ma demande ?", "Un appel ou une visio de 20 minutes pour préciser votre besoin, puis le devis et le planning par écrit."),
        ("Je n'ai pas encore de contenus, est ce un problème ?", "Non. Nous pouvons rédiger les textes et choisir les visuels avec vous.")])
    page("contact.html", "Contact et devis gratuit sous 24h | Aurea Media",
         "Parlez nous de votre projet de site internet : réponse personnelle et devis détaillé sous 24h ouvrées, sans engagement. Téléphone ou formulaire.", corps)


ARTICLES = [
    ("Combien coûte un site vitrine en 2026 ?", "Prix", "Le budget réel d'un site vitrine, poste par poste, et les pièges à éviter dans un devis.", "verisol", "8 min"),
    ("Anatomie d'un site vitrine qui convertit", "Site vitrine", "Les pages et les blocs qui transforment un visiteur en demande de devis.", "havel-trading", "10 min"),
    ("Shopify ou WooCommerce : lequel choisir ?", "E-commerce", "Frais, liberté, référencement : la comparaison honnête pour une PME.", "perle-hair", "9 min"),
    ("Pourquoi votre site ne génère pas de clients", "Conversion", "Les huit causes les plus fréquentes, et comment les corriger.", "novexia", "7 min"),
    ("Optimiser sa fiche Google Business Profile", "Référencement local", "La méthode pas à pas pour apparaître dans la carte Google.", "barber-korner", "11 min"),
    ("Refonte d'identité visuelle : par où commencer ?", "Identité", "Moderniser son image sans perdre ce qui fait sa reconnaissance.", "eddy-bilou", "6 min"),
    ("WordPress ou Webflow pour un site pro ?", "Outils", "Coût, autonomie, référencement : quel outil pour quel projet.", "dual-tech", "8 min"),
]


def conseils():
    une = ARTICLES[0]
    cats = ["Tous", "Prix", "Site vitrine", "E-commerce", "Référencement local", "Identité"]
    cartes = "".join(f'<a class="art rv" href="article-prix-site-vitrine.html"><div class="art-img"><img src="assets/realisations/{img}.webp" alt="" width="1200" height="620" loading="lazy"></div><div class="art-txt"><span class="art-cat">{cat} · {duree}</span><h3>{t}</h3><p>{d}</p><span class="link">Lire l\'article {ico("arrow")}</span></div></a>' for t, cat, d, img, duree in ARTICLES[1:])
    corps = f"""<section class="phero phero--court">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap">
    {fil(("Conseils", ""))}
    <span class="eyebrow rv">Conseils</span>
    <h1 class="split-words">Des conseils pour un site qui vous <span class="gold">rapporte</span>.</h1>
    <p class="lead rv" style="margin-top:24px">Prix, référencement, refonte, choix des outils : des articles concrets, écrits par un développeur web, pour décider en connaissance de cause.</p>
  </div>
</section>
<section class="sec" style="padding-top:10px">
  <div class="wrap">
    <a class="art-une rv" href="article-prix-site-vitrine.html">
      <div class="art-img"><img src="assets/realisations/{une[3]}.webp" alt="" width="1200" height="620"></div>
      <div class="art-txt"><span class="art-cat">À la une · {une[1]} · {une[4]}</span><h2>{une[0]}</h2><p>{une[2]}</p><div class="auteur"><img src="assets/alexandre-coury.webp" alt="" width="1024" height="800"><span><b>Alexandre Coury</b>Mis à jour le 20 octobre 2026</span></div></div>
    </a>
    <div class="filtres rv" style="margin-top:56px">{"".join(f'<button type="button" class="filtre{" is-on" if i == 0 else ""}">{c}</button>' for i, c in enumerate(cats))}</div>
    <div class="arts">{cartes}</div>
  </div>
</section>"""
    corps += appel("Besoin d'un avis ?", 'Une question sur <span class="gold">votre projet</span> ?', "Posez la directement : réponse personnelle sous 24h.", "Poser ma question")
    page("conseils.html", "Conseils pour réussir son site internet | Blog Aurea Media",
         "Prix, référencement, refonte, choix des outils : nos conseils concrets pour réussir votre site internet, écrits par un développeur web.", corps)


def article():
    sommaire = [("budget", "Le budget en un coup d'œil"), ("postes", "Ce que vous payez, poste par poste"), ("pieges", "Les pièges à éviter dans un devis"), ("choisir", "Comment bien choisir")]
    toc = "".join(f'<a href="#{i}">{t}</a>' for i, t in sommaire)
    corps = f"""<article class="article">
<header class="phero phero--court article-tete">
  <div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
  <div class="wrap wrap--etroit">
    {fil(("Conseils", "conseils.html"), ("Prix d'un site vitrine", ""))}
    <span class="eyebrow rv">Prix · 8 min de lecture</span>
    <h1 class="split-words">Combien coûte un site vitrine en <span class="gold">2026</span> ?</h1>
    <p class="lead rv" style="margin-top:22px">Entre 500 € et 10 000 €, les écarts de prix sont énormes. Voici ce qui explique vraiment le prix d'un site vitrine, et comment lire un devis.</p>
    <div class="auteur rv"><img src="assets/alexandre-coury.webp" alt="" width="1024" height="800"><span><b>Alexandre Coury</b>Développeur web, fondateur d'Aurea Media · Mis à jour le 20 octobre 2026</span></div>
  </div>
</header>
<div class="sec paper article-zone">
  <div class="wrap article-grille">
    <aside class="art-toc"><span class="eyebrow">Sommaire</span>{toc}<div class="art-toc-cta"><b>Un projet de site ?</b><span>Devis gratuit sous 24h.</span><a class="btn" href="contact.html">Demander un devis</a></div></aside>
    <div class="prose">
      <p class="intro">Le prix d'un site vitrine dépend de trois choses : le niveau de sur mesure, le volume de contenu à produire et ce qui est fait pour qu'il soit trouvé sur Google. Un site à 500 € et un site à 3 000 € n'ont souvent rien à voir, même s'ils se ressemblent au premier coup d'œil.</p>
      <h2 id="budget">Le budget en un coup d'œil</h2>
      <div class="tableau"><table><thead><tr><th>Solution</th><th>Budget</th><th>Pour qui</th></tr></thead><tbody>
        <tr><td>Créateur en ligne (Wix, Squarespace)</td><td>15 à 40 € par mois</td><td>Tester une activité</td></tr>
        <tr><td>Freelance sur template</td><td>500 à 1 500 €</td><td>Petit budget, besoin simple</td></tr>
        <tr><td>Site sur mesure en studio</td><td>1 000 à 5 000 €</td><td>TPE, PME, professions libérales</td></tr>
        <tr><td>Agence 360°</td><td>8 000 € et plus</td><td>Grands comptes, projets complexes</td></tr>
      </tbody></table></div>
      <h2 id="postes">Ce que vous payez, poste par poste</h2>
      <p>Un devis sérieux détaille au minimum : l'audit et l'arborescence, le design des maquettes, le développement, la rédaction ou l'intégration des textes, le référencement technique et la formation.</p>
      <div class="encart"><span class="ci">{ico("lightning")}</span><div><b>Bon à savoir</b><p>Le référencement technique (balises, vitesse, données structurées) doit être inclus. S'il est vendu à part, demandez ce qu'il contient précisément.</p></div></div>
      <h2 id="pieges">Les pièges à éviter dans un devis</h2>
      <ul class="liste-or"><li>{ico("check")}Un abonnement obligatoire qui rend le site locatif.</li><li>{ico("check")}Un nom de domaine réservé au nom du prestataire.</li><li>{ico("check")}Aucune mention de la vitesse de chargement ni du mobile.</li><li>{ico("check")}« Référencement offert » sans aucun détail.</li></ul>
      <h2 id="choisir">Comment bien choisir</h2>
      <p>Demandez des exemples de sites livrés et appelez un ou deux clients. Vérifiez que vous serez propriétaire du site et de ses accès. Enfin, comparez ce qui est inclus, pas seulement le montant final.</p>
      <div class="art-cta"><div><b>Vous voulez un prix précis pour votre site ?</b><span>Décrivez votre projet, devis détaillé sous 24h.</span></div><a class="btn" href="contact.html">Recevoir mon devis {ico("arrow")}</a></div>
    </div>
  </div>
</div>
</article>
<section class="sec">
  <div class="wrap">
    {entete("À lire aussi", 'Pour aller <span class="gold">plus loin</span>.')}
    <div class="arts">{"".join(f'<a class="art rv" href="article-prix-site-vitrine.html"><div class="art-img"><img src="assets/realisations/{img}.webp" alt="" width="1200" height="620" loading="lazy"></div><div class="art-txt"><span class="art-cat">{cat} · {duree}</span><h3>{t}</h3><p>{d}</p></div></a>' for t, cat, d, img, duree in ARTICLES[1:4])}</div>
  </div>
</section>"""
    page("article-prix-site-vitrine.html", "Combien coûte un site vitrine en 2026 ? Prix détaillés",
         "Prix d'un site vitrine en 2026 : budgets selon la solution, ce que contient un devis poste par poste et les pièges à éviter. Par un développeur web.", corps)


if __name__ == "__main__":
    secteur(); local(); realisations(); etude_de_cas(); tarifs(); agence(); contact(); conseils(); article()

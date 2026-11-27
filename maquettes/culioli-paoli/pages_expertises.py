# -*- coding: utf-8 -*-
"""Pages d'expertise et de procédure : un gabarit commun, un contenu propre à chaque page."""


def expertise_body(p):
    aside = "".join(f"<div><dt>{k}</dt><dd>{v}</dd></div>" for k, v in p["aside"])
    toc = "".join(f'<li><a href="#{i}">{t}</a></li>' for i, t, _ in p["sections"])
    if p.get("faq"):
        toc += '<li><a href="#faq">Questions fréquentes</a></li>'
    secs = "".join(f'<h2 id="{i}">{t}</h2>{h}' for i, t, h in p["sections"])
    rel = "".join(f'<a href="/{s}/"><b>{t}</b><span>{d}</span></a>' for s, t, d in p["related"])
    return f"""<section class="page-head grain"><div class="wrap">
{{{{CRUMBS}}}}
<div class="head-split">
  <div>
    <span class="cote">{p["cote"]}</span>
    <h1 style="margin-top:1rem">{p["h1"]}</h1>
    <p class="intro">{p["intro"]}</p>
    <div class="hero-actions">
      <a class="btn btn--solid" href="/contact/">Exposer votre situation {{{{ARROW}}}}</a>
      <button class="btn btn--ghost" type="button" data-urgence>{{{{PHONE}}}} Urgence pénale</button>
    </div>
  </div>
  <aside class="head-aside"><dl>{aside}</dl></aside>
</div></div></section>
<section class="section"><div class="wrap article">
  <article class="prose">{secs}{{{{FAQ}}}}
    <h2 id="liens">Pour aller plus loin</h2>
    <div class="related">{rel}</div>
  </article>
  <aside class="toc" aria-label="Sommaire de la page">
    <span class="eyebrow">Sur cette page</span>
    <ol>{toc}</ol>
    <div class="aside-cta"><span class="cote">Premier échange</span>
      <p>Un premier entretien permet d'analyser votre situation et de définir la stratégie de défense.</p>
      <a class="btn btn--solid" href="/contact/">Prendre rendez-vous</a></div>
  </aside>
</div></section>
<section class="contact-band"><div class="wrap">
  <div class="cb"><span class="cote">Urgence</span><h2 style="margin-top:.8rem">Une mesure est <em>en cours</em>&nbsp;?</h2>
    <p>Garde à vue, perquisition, convocation imminente : le cabinet peut être joint à tout moment pour une intervention rapide.</p>
    <button class="btn btn--solid" type="button" data-urgence>{{{{PHONE}}}} Afficher le numéro d'urgence</button></div>
  <div class="cb"><span class="cote">Rendez-vous</span><h2 style="margin-top:.8rem">Préparer votre <em>défense</em></h2>
    <p>Décrivez votre situation sans transmettre de pièce sensible. Le cabinet vous répond pour organiser un premier entretien.</p>
    <a class="btn btn--ghost" href="/contact/">Écrire au cabinet {{{{ARROW}}}}</a></div>
</div></section>"""


def _steps(items):
    return '<ol class="steps">' + "".join(f"<li><div><b>{t}</b><span>{d}</span></div></li>" for t, d in items) + "</ol>"


EXP = "expertise"
CR = [("Expertises", "expertises")]

EXPERTISES = [
    # ------------------------------------------------------------------ Droit pénal général
    {
        "slug": "avocat-penaliste-paris", "nav": "Droit pénal général", "group": EXP, "crumbs": CR,
        "schema": "Service", "area": "Paris", "prio": "P1", "cote": "Expertise · D.01",
        "kw": "avocat pénaliste Paris",
        "kw2": ["avocat droit pénal Paris", "avocat défense pénale", "avocat tribunal correctionnel Paris"],
        "title": "Avocat pénaliste Paris | Me Charles Culioli-Paoli",
        "desc": "Avocat pénaliste à Paris, Me Charles Culioli-Paoli défend les personnes mises en cause et les victimes, de la garde à vue à l'audience. Échange rapide.",
        "h1": "Avocat pénaliste à Paris, <em>de l'enquête à l'audience</em>",
        "h1_plain": "Avocat pénaliste à Paris, de l'enquête à l'audience",
        "intro": "Vous êtes convoqué, placé en garde à vue, mis en examen ou victime d'une infraction&nbsp;? <strong>Me Charles Culioli-Paoli, avocat pénaliste à Paris</strong>, intervient à chaque étape de la procédure pénale pour défendre vos droits avec rigueur et discrétion.",
        "aside": [("Juridictions", "Tribunal judiciaire de Paris, cour d'appel de Paris, juridictions d'Île-de-France"),
                  ("Intervention", "Personnes mises en cause et victimes"), ("Langues", "Français, anglais, espagnol, arabe, corse")],
        "sections": [
            ("role", "Le rôle de l'avocat pénaliste", """<p>Le droit pénal sanctionne les comportements que la loi qualifie d'infractions : contraventions, délits et crimes. Face à une procédure pénale, l'avocat pénaliste veille au respect des droits de la défense, analyse la régularité de chaque acte et construit une stratégie adaptée au dossier.</p>
<p>Son intervention ne se limite pas à l'audience. Les choix décisifs se font souvent très tôt : lors de la garde à vue, d'une audition libre ou dans les premières semaines d'une information judiciaire.</p>"""),
            ("situations", "Les situations dans lesquelles le cabinet intervient", """<h3>Pour les personnes mises en cause</h3>
<ul><li>Assistance en <a href="/avocat-garde-a-vue-paris/">garde à vue</a> et en audition libre.</li>
<li>Défense lors de l'enquête préliminaire ou de flagrance, et lors de l'information judiciaire (mise en examen, statut de témoin assisté, demandes d'actes, requêtes en nullité).</li>
<li>Défense devant le tribunal de police, le tribunal correctionnel, la cour criminelle départementale et la cour d'assises.</li>
<li>Comparution immédiate, CRPC (plaider-coupable) et procédures alternatives aux poursuites.</li>
<li>Détention provisoire : débat devant le juge des libertés et de la détention, demandes de mise en liberté, appel devant la chambre de l'instruction.</li></ul>
<h3>Pour les victimes</h3>
<ul><li>Dépôt de plainte, plainte avec constitution de partie civile, citation directe.</li>
<li>Constitution de partie civile et demande d'indemnisation du préjudice.</li>
<li>Saisine de la commission d'indemnisation des victimes d'infractions (CIVI) lorsque les conditions sont réunies.</li></ul>"""),
            ("methode", "Une méthode de défense", _steps([
                ("Analyse du dossier", "Lecture complète de la procédure, identification des enjeux, des fragilités et des risques."),
                ("Stratégie", "Choix des actes à demander, des nullités à soulever et de la ligne de défense, expliqués avec clarté."),
                ("Préparation", "Préparation des auditions, des interrogatoires et de l'audience avec la personne défendue."),
                ("Plaidoirie", "Défense à l'audience, puis conseil sur les voies de recours et l'exécution de la décision.")])),
        ],
        "faq": [
            ("Quand faut-il contacter un avocat pénaliste&nbsp;?", "Le plus tôt possible : dès la réception d'une convocation, avant une audition libre, ou dès le placement en garde à vue d'un proche. Les premiers actes de la procédure ont souvent une influence déterminante sur la suite."),
            ("L'avocat peut-il intervenir en dehors de Paris&nbsp;?", "Oui. Un avocat inscrit au Barreau de Paris peut plaider devant toutes les juridictions pénales de France. Le cabinet intervient notamment en Essonne et dans les autres départements d'Île-de-France."),
            ("Comment sont fixés les honoraires&nbsp;?", "Les honoraires font l'objet d'une convention écrite, conformément à la loi. Ils dépendent de la complexité du dossier, du temps consacré et des diligences nécessaires. Ils sont présentés avant toute intervention."),
        ],
        "related": [("avocat-garde-a-vue-paris", "Garde à vue", "Vos droits et le rôle de l'avocat"),
                    ("avocat-plaidoirie-penale", "Plaidoirie pénale", "Tribunal correctionnel et cour d'assises"),
                    ("avocat-amenagement-de-peine", "Aménagement de peine", "Après la condamnation"),
                    ("avocat-penaliste-essonne", "Avocat pénaliste en Essonne", "Évry-Courcouronnes, Massy, Fleury-Mérogis")],
    },
    # ------------------------------------------------------------------ Pénal des affaires
    {
        "slug": "avocat-droit-penal-des-affaires", "nav": "Droit pénal des affaires", "group": EXP, "crumbs": CR,
        "schema": "Service", "area": "France", "prio": "P1", "cote": "Expertise · D.02",
        "kw": "avocat droit pénal des affaires Paris",
        "kw2": ["avocat abus de biens sociaux", "avocat blanchiment", "avocat fraude fiscale", "avocat défense dirigeant"],
        "title": "Avocat droit pénal des affaires Paris | Culioli-Paoli",
        "desc": "Avocat en droit pénal des affaires à Paris : abus de biens sociaux, blanchiment, fraude fiscale, corruption. Défense des dirigeants, enquête et audience.",
        "h1": "Avocat en droit pénal des affaires à Paris, <em>aux côtés des dirigeants</em>",
        "h1_plain": "Avocat en droit pénal des affaires à Paris, aux côtés des dirigeants",
        "intro": "Dirigeant, cadre ou entreprise visés par une enquête économique et financière&nbsp;? <strong>Me Charles Culioli-Paoli</strong> intervient en droit pénal des affaires, de la perquisition à l'audience, avec la discrétion qu'imposent ces dossiers.",
        "aside": [("Interlocuteurs", "Parquet national financier, parquets, juges d'instruction, administrations"),
                  ("Expérience", "Ministère de l'Économie<span class=\"todo\">à confirmer</span>"),
                  ("Approche", "Dossiers techniques, lecture comptable et fiscale")],
        "sections": [
            ("infractions", "Les infractions concernées", """<ul>
<li><b>Abus de biens sociaux</b> (articles L.241-3 et L.242-6 du Code de commerce) et abus de confiance (article 314-1 du Code pénal).</li>
<li><b>Blanchiment</b> (article 324-1 du Code pénal), y compris le blanchiment de fraude fiscale.</li>
<li><b>Fraude fiscale</b> (article 1741 du Code général des impôts), en lien avec les procédures de contrôle et de redressement.</li>
<li><b>Corruption et trafic d'influence</b>, favoritisme, prise illégale d'intérêts.</li>
<li>Escroquerie, faux et usage de faux, banqueroute, délits boursiers.</li></ul>"""),
            ("enquete", "Perquisition, audition, garde à vue : les premiers actes", """<p>Les procédures économiques et financières commencent souvent par une perquisition au siège de l'entreprise ou au domicile du dirigeant, suivie d'auditions ou d'une garde à vue. La présence d'un avocat dès ces premiers actes permet de protéger les documents couverts par le secret professionnel, de préparer les auditions et d'éviter des déclarations prématurées.</p>
<div class="callout">La stratégie se construit dès l'enquête : les déclarations faites en garde à vue figurent au dossier jusqu'à l'audience.<small>Pour une perquisition en cours, utilisez le bouton Urgence pénale.</small></div>"""),
            ("issues", "Les issues possibles de la procédure", """<p>Selon le dossier, l'avocat peut discuter d'une issue négociée ou préparer la défense au fond :</p>
<ul><li>Comparution sur reconnaissance préalable de culpabilité (CRPC).</li>
<li>Convention judiciaire d'intérêt public (CJIP) pour les personnes morales, prévue à l'article 41-1-2 du Code de procédure pénale.</li>
<li>Défense devant le tribunal correctionnel, puis devant la cour d'appel.</li></ul>"""),
            ("methode", "La méthode du cabinet", _steps([
                ("Cartographie du risque", "Identification des qualifications possibles et des personnes exposées, physiques ou morales."),
                ("Lecture technique", "Analyse des flux, des documents comptables et des échanges avec l'administration fiscale."),
                ("Coordination", "Travail avec l'expert-comptable, le conseil habituel de l'entreprise et, si besoin, un avocat fiscaliste."),
                ("Défense", "Mémoires, demandes d'actes, nullités et plaidoirie.")])),
        ],
        "faq": [
            ("Un dirigeant peut-il être poursuivi en même temps que sa société&nbsp;?", "Oui. La responsabilité pénale de la personne morale n'exclut pas celle des personnes physiques auteurs ou complices des mêmes faits (article 121-2 du Code pénal). Les intérêts de chacun peuvent diverger, ce qui doit être examiné dès le début."),
            ("Une procédure fiscale peut-elle mener à des poursuites pénales&nbsp;?", "Oui. Certains redressements peuvent donner lieu à une plainte pour fraude fiscale ou à une transmission au parquet. L'articulation entre la procédure fiscale et la procédure pénale doit être anticipée."),
        ],
        "related": [("avocat-cybercriminalite", "Cybercriminalité", "Fraude informatique et escroqueries en ligne"),
                    ("avocat-droit-penal-international", "Droit pénal international", "Entraide pénale et enquêtes transfrontières"),
                    ("avocat-garde-a-vue-paris", "Garde à vue", "Assistance dès la première heure"),
                    ("ressources", "Ressources", "Analyses en droit pénal des affaires")],
    },
    # ------------------------------------------------------------------ Extradition
    {
        "slug": "avocat-extradition", "nav": "Extradition", "group": EXP, "crumbs": CR,
        "schema": "Service", "area": "France", "prio": "P1", "cote": "Expertise · D.03",
        "kw": "avocat extradition",
        "kw2": ["avocat mandat d'arrêt européen", "avocat procédure d'extradition", "avocat extradition Paris"],
        "title": "Avocat extradition et mandat d'arrêt européen | Paris",
        "desc": "Avocat en extradition à Paris : mandat d'arrêt européen, demande d'extradition, audience devant la chambre de l'instruction. Intervention rapide, en anglais.",
        "h1": "Avocat en extradition <em>et mandat d'arrêt européen</em>",
        "h1_plain": "Avocat en extradition et mandat d'arrêt européen",
        "intro": "Une personne est arrêtée en France sur le fondement d'un mandat d'arrêt européen ou d'une demande d'extradition&nbsp;? Les délais sont courts. <strong>Me Charles Culioli-Paoli</strong>, formé à Harvard Law School, intervient devant la chambre de l'instruction et échange avec les avocats étrangers en anglais et en espagnol.",
        "aside": [("Juridiction", "Chambre de l'instruction de la cour d'appel"),
                  ("Textes", "Articles 695-11 et suivants, 696 et suivants du Code de procédure pénale"),
                  ("Langues de travail", "Français, anglais, espagnol")],
        "sections": [
            ("mae", "Le mandat d'arrêt européen", """<p>Entre États membres de l'Union européenne, la remise d'une personne recherchée repose sur le mandat d'arrêt européen. La procédure est judiciaire et encadrée par des délais brefs. La personne arrêtée est présentée au parquet général, puis comparaît devant la chambre de l'instruction, qui statue sur la remise.</p>
<p>La personne peut consentir à sa remise ou s'y opposer. Ce choix a des conséquences importantes, notamment au regard de la règle de la spécialité : il doit être fait avec un avocat, en connaissance de cause.</p>"""),
            ("extradition", "L'extradition hors Union européenne", """<p>Lorsque l'État requérant n'est pas membre de l'Union européenne, la procédure d'extradition comporte une phase judiciaire, devant la chambre de l'instruction qui rend un avis, puis une phase administrative, avec un décret du Premier ministre susceptible de recours devant le Conseil d'État.</p>
<p>L'avocat examine les conventions applicables, les garanties offertes par l'État requérant, le respect des droits fondamentaux et les motifs de refus prévus par la loi.</p>"""),
            ("defense", "Les axes de défense", """<ul><li>Régularité de l'arrestation et de la notification des droits.</li>
<li>Contrôle de la double incrimination et de la prescription.</li>
<li>Risques de traitements contraires à l'article 3 de la Convention européenne des droits de l'homme.</li>
<li>Situation personnelle et familiale en France, garanties de représentation, contrôle judiciaire ou assignation à résidence sous surveillance électronique.</li></ul>"""),
            ("methode", "Une intervention coordonnée", _steps([
                ("Prise en charge immédiate", "Assistance dès la présentation au parquet général et préparation de la première comparution."),
                ("Liaison internationale", "Échanges avec l'avocat de l'État requérant pour obtenir les pièces et comprendre la procédure étrangère."),
                ("Audience", "Défense devant la chambre de l'instruction, puis recours éventuel.")])),
        ],
        "faq": [
            ("Faut-il consentir à sa remise&nbsp;?", "Cela dépend du dossier. Le consentement accélère la procédure mais a des effets sur certaines garanties. Cette décision doit être prise après un échange avec un avocat."),
            ("Peut-on rester libre pendant la procédure&nbsp;?", "La chambre de l'instruction peut ordonner un placement sous contrôle judiciaire ou une assignation à résidence sous surveillance électronique, à la place de l'incarcération, selon les garanties présentées."),
        ],
        "related": [("avocat-droit-penal-international", "Droit pénal international", "Notice rouge Interpol, entraide pénale"),
                    ("avocat-penaliste-paris", "Droit pénal général", "Défense pénale à Paris"),
                    ("ressources", "Ressources", "Extradition et droit international")],
    },
    # ------------------------------------------------------------------ Pénal international
    {
        "slug": "avocat-droit-penal-international", "nav": "Droit pénal international", "group": EXP, "crumbs": CR,
        "schema": "Service", "area": "France", "prio": "P2", "cote": "Expertise · D.04",
        "kw": "avocat droit pénal international",
        "kw2": ["avocat notice rouge Interpol", "avocat entraide pénale internationale", "international criminal lawyer Paris"],
        "title": "Avocat droit pénal international Paris | Culioli-Paoli",
        "desc": "Avocat en droit pénal international à Paris : notice rouge Interpol, entraide pénale, procédures transfrontières. Un avocat formé à Harvard Law School.",
        "h1": "Avocat en droit pénal international, <em>entre Paris et l'étranger</em>",
        "h1_plain": "Avocat en droit pénal international, entre Paris et l'étranger",
        "intro": "Notice rouge Interpol, commission rogatoire internationale, enquête menée dans plusieurs pays&nbsp;: <strong>Me Charles Culioli-Paoli</strong> accompagne les personnes concernées par une procédure pénale qui dépasse les frontières, en lien avec des confrères étrangers.",
        "aside": [("Formation", "LL.M., Harvard Law School"), ("Barreaux", "Paris<span class=\"todo\">New York à confirmer</span>"),
                  ("Langues", "Français, anglais, espagnol, arabe")],
        "sections": [
            ("interpol", "Notice rouge Interpol", """<p>Une notice rouge est une demande de localisation et d'arrestation provisoire diffusée par Interpol à la demande d'un État membre. Elle peut limiter fortement les déplacements d'une personne. Il est possible de demander l'accès aux informations la concernant et, dans certains cas, leur suppression auprès de la Commission de contrôle des fichiers d'Interpol (CCF), notamment lorsque la demande présente un caractère politique ou ne respecte pas les règles de l'organisation.</p>"""),
            ("entraide", "Entraide pénale et enquêtes transfrontières", """<p>Les enquêtes modernes mobilisent l'entraide pénale : demandes d'entraide, décisions d'enquête européennes, équipes communes d'enquête. L'avocat vérifie la régularité de la collecte des preuves à l'étranger et leur utilisation dans la procédure française.</p>"""),
            ("etrangers", "Défense des personnes de nationalité étrangère", """<p>Le cabinet accompagne aussi les personnes étrangères poursuivies en France, ainsi que les Français poursuivis à l'étranger, en coordination avec un avocat local. La pratique de plusieurs langues permet un échange direct avec la personne défendue et sa famille.</p>"""),
        ],
        "faq": [("Peut-on contester une notice rouge Interpol&nbsp;?", "Oui. Une demande peut être adressée à la Commission de contrôle des fichiers d'Interpol pour accéder aux données, les faire rectifier ou les faire supprimer lorsque les règles d'Interpol ne sont pas respectées. Le dossier doit être solidement argumenté.")],
        "related": [("avocat-extradition", "Extradition", "Mandat d'arrêt européen et extradition"),
                    ("avocat-droit-penal-des-affaires", "Droit pénal des affaires", "Fraude et blanchiment transfrontières"),
                    ("charles-culioli-paoli-avocat", "Le cabinet", "Le parcours de Charles Culioli-Paoli")],
    },
    # ------------------------------------------------------------------ Aménagement de peine
    {
        "slug": "avocat-amenagement-de-peine", "nav": "Aménagement de peine", "group": EXP, "crumbs": CR,
        "schema": "Service", "area": "France", "prio": "P1", "cote": "Expertise · D.05",
        "kw": "avocat aménagement de peine",
        "kw2": ["avocat juge de l'application des peines", "avocat libération conditionnelle", "avocat bracelet électronique", "avocat réduction de peine"],
        "title": "Avocat aménagement de peine | Me Culioli-Paoli, Paris",
        "desc": "Avocat en aménagement de peine : bracelet électronique, semi-liberté, libération conditionnelle. Défense devant le juge de l'application des peines.",
        "h1": "Avocat en aménagement de peine, <em>devant le juge de l'application des peines</em>",
        "h1_plain": "Avocat en aménagement de peine, devant le juge de l'application des peines",
        "intro": "Une condamnation n'est pas la fin de la défense. <strong>Me Charles Culioli-Paoli</strong> accompagne les personnes condamnées et leurs proches pour obtenir un aménagement de peine adapté : détention à domicile sous surveillance électronique, semi-liberté, placement à l'extérieur ou libération conditionnelle.",
        "aside": [("Juridictions", "Juge et tribunal de l'application des peines, chambre de l'application des peines"),
                  ("Établissements", "Fleury-Mérogis, Fresnes, Paris-La Santé et autres"),
                  ("Pour qui", "Personnes condamnées, libres ou détenues, et leurs proches")],
        "sections": [
            ("mesures", "Les mesures d'aménagement", """<ul>
<li><b>Détention à domicile sous surveillance électronique</b>, avec un bracelet électronique.</li>
<li><b>Semi-liberté</b> : la personne exerce une activité à l'extérieur et rejoint l'établissement le soir.</li>
<li><b>Placement à l'extérieur</b>, souvent dans le cadre d'une structure d'accompagnement.</li>
<li><b>Libération conditionnelle</b> (article 729 du Code de procédure pénale), assortie d'obligations.</li>
<li>Fractionnement ou suspension de peine pour motif grave d'ordre médical, familial, professionnel ou social.</li></ul>
<p>Le détail des conditions est présenté dans notre <a href="/guide-amenagement-de-peine/">guide de l'aménagement de peine</a>.</p>"""),
            ("projet", "Construire un projet crédible", """<p>Le juge de l'application des peines apprécie le projet de réinsertion : emploi ou formation, hébergement, suivi médical, indemnisation des victimes, liens familiaux. Le rôle de l'avocat consiste à constituer un dossier solide, à rassembler les justificatifs et à défendre ce projet lors du débat contradictoire.</p>"""),
            ("methode", "Les étapes de l'accompagnement", _steps([
                ("Calcul de la situation pénale", "Vérification des dates, des crédits de réduction de peine et de l'éligibilité aux mesures."),
                ("Constitution du dossier", "Promesse d'embauche, attestation d'hébergement, justificatifs de soins et d'indemnisation."),
                ("Requête et débat", "Dépôt de la requête, préparation de l'entretien avec le service pénitentiaire d'insertion et de probation, débat devant le juge."),
                ("Recours", "Appel devant la chambre de l'application des peines si la demande est rejetée.")])),
        ],
        "faq": [
            ("Quel juge est compétent pour un détenu&nbsp;?", "Pour une personne détenue, le juge de l'application des peines compétent est celui de la juridiction dans le ressort de laquelle se trouve l'établissement pénitentiaire (article 712-10 du Code de procédure pénale). Pour un détenu de Fleury-Mérogis, il s'agit du tribunal judiciaire d'Évry-Courcouronnes."),
            ("Les proches peuvent-ils contacter l'avocat&nbsp;?", "Oui. Les proches d'une personne détenue peuvent prendre contact avec le cabinet pour organiser la défense. L'avocat échange ensuite directement avec la personne concernée."),
        ],
        "related": [("guide-amenagement-de-peine", "Guide de l'aménagement de peine", "Conditions, procédure et délais"),
                    ("avocat-centre-penitentiaire-fleury-merogis", "Fleury-Mérogis", "Intervenir auprès d'un détenu"),
                    ("etablissements-penitentiaires", "Établissements pénitentiaires", "Les établissements où le cabinet intervient")],
    },
    # ------------------------------------------------------------------ Cybercriminalité
    {
        "slug": "avocat-cybercriminalite", "nav": "Cybercriminalité", "group": EXP, "crumbs": CR,
        "schema": "Service", "area": "France", "prio": "P2", "cote": "Expertise · D.06",
        "kw": "avocat cybercriminalité",
        "kw2": ["avocat fraude informatique", "avocat cyberattaque", "avocat pénal numérique", "avocat escroquerie en ligne"],
        "title": "Avocat cybercriminalité Paris | Me Culioli-Paoli",
        "desc": "Avocat en cybercriminalité à Paris : fraude informatique, cyberattaque, escroquerie en ligne, cyberharcèlement. Défense des victimes et des mis en cause.",
        "h1": "Avocat en cybercriminalité, <em>pour les victimes comme pour la défense</em>",
        "h1_plain": "Avocat en cybercriminalité, pour les victimes comme pour la défense",
        "intro": "Intrusion dans un système informatique, rançongiciel, escroquerie en ligne, cyberharcèlement&nbsp;: <strong>Me Charles Culioli-Paoli</strong> intervient en droit pénal du numérique, aux côtés des entreprises et des particuliers victimes comme des personnes mises en cause.",
        "aside": [("Textes", "Articles 323-1 et suivants du Code pénal"), ("Interlocuteurs", "Parquet de Paris, services d'enquête spécialisés"),
                  ("Pour qui", "Entreprises, dirigeants, particuliers")],
        "sections": [
            ("infractions", "Les infractions numériques", """<ul>
<li>Atteintes aux systèmes de traitement automatisé de données : accès ou maintien frauduleux, entrave, extraction de données (articles 323-1 et suivants du Code pénal).</li>
<li>Escroqueries en ligne, fraude au président, faux ordres de virement.</li>
<li>Usurpation d'identité numérique, cyberharcèlement, diffusion d'images sans consentement.</li></ul>"""),
            ("victimes", "Entreprise victime d'une cyberattaque", """<p>Après une attaque, les premières heures comptent : préservation des preuves, déclaration aux autorités compétentes, plainte, et articulation avec les obligations de notification prévues par le RGPD. L'avocat coordonne la démarche pénale avec les équipes techniques et l'assureur.</p>"""),
            ("defense", "Défense des personnes mises en cause", """<p>Les dossiers numériques reposent sur des preuves techniques : adresses IP, réquisitions aux opérateurs, analyses de supports saisis. La défense en vérifie la fiabilité, la régularité et l'interprétation, au besoin avec l'appui d'un expert.</p>"""),
        ],
        "faq": [("Faut-il porter plainte après une cyberattaque&nbsp;?", "Oui, dans la plupart des cas. La plainte permet l'ouverture d'une enquête et peut être exigée par l'assureur. Elle doit être accompagnée d'éléments techniques préservés avec soin.")],
        "related": [("avocat-droit-penal-des-affaires", "Droit pénal des affaires", "Fraude et escroquerie en entreprise"),
                    ("avocat-droit-penal-international", "Droit pénal international", "Enquêtes transfrontières"),
                    ("avocat-penaliste-paris", "Droit pénal général", "Défense pénale à Paris")],
    },
    # ------------------------------------------------------------------ Plaidoirie
    {
        "slug": "avocat-plaidoirie-penale", "nav": "Plaidoirie", "group": EXP, "crumbs": CR,
        "schema": "Service", "area": "France", "prio": "P2", "cote": "Expertise · D.07",
        "kw": "avocat cour d'assises",
        "kw2": ["avocat plaidoirie pénale", "avocat défense criminelle", "avocat cour criminelle départementale", "avocat tribunal correctionnel"],
        "title": "Avocat cour d'assises et plaidoirie pénale | Paris",
        "desc": "Avocat en défense criminelle : cour d'assises, cour criminelle départementale, tribunal correctionnel. Une plaidoirie préparée à partir du dossier.",
        "h1": "Plaidoirie pénale et défense <em>devant la cour d'assises</em>",
        "h1_plain": "Plaidoirie pénale et défense devant la cour d'assises",
        "intro": "L'audience est le moment où le dossier devient une parole. <strong>Me Charles Culioli-Paoli</strong> prépare chaque plaidoirie à partir d'une lecture complète de la procédure, devant le tribunal correctionnel, la cour criminelle départementale et la cour d'assises.",
        "aside": [("Juridictions", "Tribunal correctionnel, cour criminelle départementale, cour d'assises"),
                  ("Partie défendue", "Accusés, prévenus et parties civiles")],
        "sections": [
            ("juridictions", "Les juridictions de jugement", """<p>Les délits sont jugés par le tribunal correctionnel. Les crimes relèvent de la cour d'assises, composée de magistrats et de jurés, ou, pour certains crimes punis de quinze ou vingt ans de réclusion, de la cour criminelle départementale composée uniquement de magistrats. Chaque juridiction appelle une préparation et une manière de plaider différentes.</p>"""),
            ("preparation", "Préparer une audience criminelle", _steps([
                ("Maîtrise du dossier", "Lecture intégrale, chronologie, contradictions, expertises."),
                ("Témoins et experts", "Choix des témoins, préparation des questions et des contre-interrogatoires."),
                ("Préparation de l'accusé", "Explication du déroulement de l'audience et préparation de l'interrogatoire."),
                ("Plaidoirie", "Construction d'un propos clair, rigoureux et incarné.")])),
            ("approche", "Une conception de la plaidoirie", """<div class="callout">Plaider, c'est rendre audible une histoire que la procédure a fragmentée.<small>Formulation proposée, à valider par Me Culioli-Paoli.</small></div>"""),
        ],
        "faq": [("Peut-on faire appel d'un arrêt de cour d'assises&nbsp;?", "Oui. L'appel est examiné par une autre cour d'assises, désignée par la chambre criminelle de la Cour de cassation. Le délai d'appel est de dix jours à compter du prononcé de l'arrêt.")],
        "related": [("avocat-penaliste-paris", "Droit pénal général", "De l'enquête à l'audience"),
                    ("charles-culioli-paoli-avocat", "Le cabinet", "Une vision de la défense"),
                    ("avocat-amenagement-de-peine", "Aménagement de peine", "Après le jugement")],
    },
]

GAV = {
    "slug": "avocat-garde-a-vue-paris", "nav": "Garde à vue", "group": "procedure",
    "crumbs": [("Expertises", "expertises"), ("Droit pénal général", "avocat-penaliste-paris")],
    "schema": "Service", "area": "Paris", "prio": "P1", "cote": "Procédure · Garde à vue",
    "kw": "avocat garde à vue Paris",
    "kw2": ["droits en garde à vue", "avocat garde à vue urgence", "durée garde à vue"],
    "title": "Avocat garde à vue Paris | Urgence 24h/24, Culioli-Paoli",
    "desc": "Avocat en garde à vue à Paris et en Essonne : entretien confidentiel, assistance aux auditions, droits du gardé à vue. Le cabinet intervient en urgence.",
    "h1": "Avocat en garde à vue à Paris, <em>dès la première heure</em>",
    "h1_plain": "Avocat en garde à vue à Paris, dès la première heure",
    "intro": "Un proche vient d'être placé en garde à vue&nbsp;? Il peut demander l'assistance d'un avocat dès le début de la mesure. <strong>Me Charles Culioli-Paoli</strong> intervient en urgence dans les commissariats et brigades de Paris et d'Essonne.",
    "aside": [("Durée", "24 heures, renouvelable une fois, sauf régimes dérogatoires"), ("Entretien", "30 minutes, confidentiel"),
              ("Disponibilité", "24h/24, 7j/7<span class=\"todo\">à confirmer</span>")],
    "sections": [
        ("droits", "Les droits de la personne gardée à vue", """<ul>
<li>Être informée de la qualification, de la date et du lieu présumés de l'infraction.</li>
<li>Faire prévenir un proche et son employeur.</li>
<li>Être examinée par un médecin.</li>
<li>Être assistée par un avocat (article 63-3-1 du Code de procédure pénale).</li>
<li>Faire des déclarations, répondre aux questions ou se taire.</li></ul>"""),
        ("avocat", "Ce que fait l'avocat", _steps([
            ("Entretien confidentiel", "Trente minutes d'échange avec la personne gardée à vue, à chaque début de mesure et de prolongation (article 63-4)."),
            ("Consultation de pièces", "Accès au procès-verbal de notification des droits, au certificat médical et aux procès-verbaux d'audition de la personne (article 63-4-1)."),
            ("Assistance aux auditions", "Présence lors des auditions et confrontations, questions et observations écrites versées à la procédure."),
            ("Suite de la mesure", "Préparation d'un éventuel déferrement, d'une comparution immédiate ou d'une convocation.")])),
        ("duree", "Combien de temps dure une garde à vue&nbsp;?", """<p>La garde à vue dure en principe 24 heures. Elle peut être prolongée de 24 heures sur autorisation du procureur de la République lorsque l'infraction est punie d'au moins un an d'emprisonnement. Pour certaines infractions relevant de la criminalité organisée, la durée peut être portée à 96 heures.</p>"""),
        ("proches", "Conseils aux proches", """<p>Les proches n'ont pas accès au dossier. Ils peuvent toutefois désigner un avocat : celui-ci se présente alors au service d'enquête, et la personne gardée à vue confirme ou non ce choix. Notez le service d'enquête et la ville si vous les connaissez.</p>"""),
    ],
    "faq": [
        ("Un proche peut-il choisir l'avocat&nbsp;?", "Oui. Un proche peut désigner un avocat, dont la désignation doit ensuite être confirmée par la personne gardée à vue."),
        ("La personne doit-elle répondre aux questions&nbsp;?", "Non. Elle a le droit de se taire. L'avocat l'aide à choisir l'attitude la plus adaptée à son dossier."),
    ],
    "related": [("avocat-penaliste-paris", "Avocat pénaliste à Paris", "La défense pénale dans son ensemble"),
                ("avocat-penaliste-essonne", "Avocat pénaliste en Essonne", "Commissariats et brigades du département"),
                ("avocat-droit-penal-des-affaires", "Droit pénal des affaires", "Perquisition et garde à vue des dirigeants")],
}

for _p in EXPERTISES + [GAV]:
    _p["body"] = expertise_body

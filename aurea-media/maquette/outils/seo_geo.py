"""SEO et GEO des pages de la maquette : balises de tête, JSON-LD et blocs « En bref » citables.

Appliqué après la génération (idempotent) : python3 outils/seo_geo.py
Règles suivies (skills /seo et /seo-geo) :
- canonical, Open Graph, robots « max-image-preview:large » sur chaque page ;
- un graphe JSON-LD par page : ProfessionalService et WebSite partagés, puis Service, Article,
  CreativeWork, CollectionPage, AboutPage ou ContactPage selon la page, et BreadcrumbList ;
- pas de FAQPage (plus de résultat enrichi depuis mai 2026), pas de HowTo, pas d'AggregateRating
  auto déclaré ;
- un bloc « En bref » de 134 à 167 mots en haut des pages stratégiques : définition, prix, délai,
  preuves, avec date de mise à jour et auteur (citabilité dans les réponses des moteurs IA).
"""
import json, re
from pathlib import Path

RACINE = Path(__file__).resolve().parent.parent
SITE = "https://aurea-media.fr"
MAJ = "2026-10-25"
MAJ_TXT = "25 octobre 2026"

ORGA = {
    "@type": "ProfessionalService", "@id": f"{SITE}/#organisation", "name": "Aurea Media",
    "url": f"{SITE}/", "logo": f"{SITE}/wp-content/themes/aurea/assets/logo-aurea-media.png",
    "image": f"{SITE}/wp-content/themes/aurea/assets/og-aurea-media.jpg",
    "description": "Agence web indépendante fondée en 2021 : création de sites vitrines et e-commerce sur mesure, identité visuelle, motion design et présentations.",
    "telephone": "+33651287761", "email": "alexandre.coury@aurea-media.fr", "priceRange": "€€", "foundingDate": "2021",
    "address": {"@type": "PostalAddress", "streetAddress": "60 rue François Ier", "postalCode": "75008", "addressLocality": "Paris", "addressRegion": "Île-de-France", "addressCountry": "FR"},
    "areaServed": [{"@type": "Country", "name": "France"}, {"@type": "AdministrativeArea", "name": "Île-de-France"}, {"@type": "Place", "name": "Europe"}],
    "founder": {"@id": f"{SITE}/#alexandre-coury"},
    "sameAs": ["https://www.linkedin.com/company/aureamedia/", "https://www.google.com/search?kgmid=/g/11mcqnsjrx"],
    "knowsAbout": ["Création de site vitrine", "Création de site e-commerce", "WordPress", "WooCommerce", "Référencement naturel", "Identité visuelle", "Motion design"],
}
PERSONNE = {"@type": "Person", "@id": f"{SITE}/#alexandre-coury", "name": "Alexandre Coury", "jobTitle": "Fondateur, développeur et designer web",
            "worksFor": {"@id": f"{SITE}/#organisation"}, "sameAs": ["https://www.linkedin.com/in/alexandre-coury/"]}
SITEWEB = {"@type": "WebSite", "@id": f"{SITE}/#site", "url": f"{SITE}/", "name": "Aurea Media", "inLanguage": "fr-FR", "publisher": {"@id": f"{SITE}/#organisation"}}

# fichier : (url, type, nom de service ou titre, prix « dès », fil d'Ariane)
PAGES = {
    "index.html": ("/", "accueil", None, None, []),
    "creation-site-vitrine.html": ("/creation-site-vitrine/", "service", "Création de site vitrine sur mesure", "1000", [("Services", "/#services"), ("Site vitrine", None)]),
    "creation-site-ecommerce.html": ("/creation-site-ecommerce/", "service", "Création de site e-commerce WooCommerce", "1650", [("Services", "/#services"), ("Site e-commerce", None)]),
    "refonte-site-internet.html": ("/refonte-site-internet/", "service", "Refonte de site internet", None, [("Services", "/#services"), ("Refonte", None)]),
    "logo-identite-visuelle.html": ("/logo-identite-visuelle/", "service", "Création d'identité visuelle et de logo", None, [("Services", "/#services"), ("Identité visuelle", None)]),
    "motion-design.html": ("/motion-design/", "service", "Motion design pour entreprise", None, [("Services", "/#services"), ("Motion design", None)]),
    "maquettes-presentation.html": ("/maquettes-presentation/", "service", "Création de présentations et pitch decks", None, [("Services", "/#services"), ("Maquettes de présentation", None)]),
    "site-internet-avocat.html": ("/site-internet-avocat/", "service", "Création de site internet pour avocat", "1000", [("Secteurs", "/creation-site-vitrine/"), ("Avocats", None)]),
    "agence-web-paris.html": ("/agence-web-paris/", "local", "Agence web à Paris", "1000", [("Agence web Paris", None)]),
    "site-internet-professions-sante.html": ("/site-internet-professions-sante/", "service", "Création de site internet pour professionnel de santé", "1000", [("Secteurs", "/creation-site-vitrine/"), ("Santé", None)]),
    "site-internet-architecte.html": ("/site-internet-architecte/", "service", "Création de site internet pour architecte", "1000", [("Secteurs", "/creation-site-vitrine/"), ("Architectes", None)]),
    "site-internet-restaurant.html": ("/site-internet-restaurant/", "service", "Création de site internet pour restaurant", "1000", [("Secteurs", "/creation-site-vitrine/"), ("Restaurants", None)]),
    "site-internet-artisan-btp.html": ("/site-internet-artisan-btp/", "service", "Création de site internet pour artisan et BTP", "1000", [("Secteurs", "/creation-site-vitrine/"), ("Artisans et BTP", None)]),
    "site-internet-coach-consultant.html": ("/site-internet-coach-consultant/", "service", "Création de site internet pour coach et consultant", "1000", [("Secteurs", "/creation-site-vitrine/"), ("Coachs et consultants", None)]),
    "agence-web-hauts-de-seine.html": ("/agence-web-hauts-de-seine/", "local", "Agence web dans les Hauts de Seine", "1000", [("Agence web Hauts de Seine", None)]),
    "mentions-legales.html": ("/mentions-legales/", "texte", "Mentions légales", None, [("Mentions légales", None)]),
    "politique-confidentialite.html": ("/politique-confidentialite/", "texte", "Politique de confidentialité", None, [("Confidentialité", None)]),
    "plan-du-site.html": ("/plan-du-site/", "texte", "Plan du site", None, [("Plan du site", None)]),
    "realisations.html": ("/realisations/", "collection", "Réalisations", None, [("Réalisations", None)]),
    "realisation-havel-trading.html": ("/realisations/havel-trading/", "cas", "Site vitrine Havel Trading", None, [("Réalisations", "/realisations/"), ("Havel Trading", None)]),
    "tarifs.html": ("/tarifs/", "tarifs", "Tarifs", None, [("Tarifs", None)]),
    "agence.html": ("/agence/", "apropos", "L'agence", None, [("L'agence", None)]),
    "contact.html": ("/contact/", "contact", "Contact", None, [("Contact", None)]),
    "conseils.html": ("/conseils/", "blog", "Conseils", None, [("Conseils", None)]),
    "article-prix-site-vitrine.html": ("/conseils/prix-site-vitrine/", "article", "Combien coûte un site vitrine en 2026 ?", None, [("Conseils", "/conseils/"), ("Prix d'un site vitrine", None)]),
}

# Blocs « En bref » : question, réponse de 134 à 167 mots, faits clés
EN_BREF = {
    "site-internet-professions-sante.html": ("Que doit contenir le site d'un professionnel de santé ?",
        "Le site internet d'un professionnel de santé doit permettre à un patient de savoir en quelques secondes si le praticien traite son problème, où se trouve le cabinet et comment prendre rendez vous. Il présente les praticiens, leurs spécialités et leurs langues parlées, les actes pratiqués, les horaires, l'accès et l'accessibilité, avec un lien vers l'outil de réservation comme Doctolib sur chaque page. Le ton reste informatif : depuis 2020, le code de déontologie médicale autorise les médecins à communiquer sur leur activité, à condition que l'information soit loyale, honnête et sans caractère commercial. Les autres professions de santé suivent des règles proches, fixées par leur ordre. Aurea Media a notamment créé le site du réseau de centres médicaux Xpert Medical. Un site de cabinet coûte à partir de 1 000 € HT, rédaction des pages de spécialités comprise, et il est en ligne en quatre semaines environ.",
        [("Prix", "dès 1 000 € HT"), ("Délai", "4 semaines environ"), ("Rendez vous", "Doctolib ou votre outil"), ("Référence", "Xpert Medical")]),
    "site-internet-architecte.html": ("Comment réussir le site internet d'un architecte ?",
        "Le site internet d'un architecte est d'abord un portfolio : chaque projet y est présenté avec son contexte, le parti pris architectural, les matériaux, les surfaces et des photographies de qualité. Pour rester rapide malgré des images lourdes, les photos sont converties au format WebP, redimensionnées pour chaque écran et chargées seulement lorsqu'elles deviennent visibles. Les projets sont classés par type, résidentiel, tertiaire, rénovation ou architecture intérieure, et chacun dispose de sa propre page, indexable par Google avec sa localisation. Un formulaire de contact qui demande la surface, le budget et le calendrier permet de qualifier les demandes avant le premier rendez vous. Aurea Media a notamment créé le site de Portugal Expert Properties, consacré à des résidences de luxe au sud de Lisbonne. Un site d'architecte coûte à partir de 1 000 € HT et il est en ligne en quatre semaines environ, selon le nombre de projets à mettre en page.",
        [("Prix", "dès 1 000 € HT"), ("Délai", "4 semaines environ"), ("Images", "WebP, chargement différé"), ("Référence", "Portugal Expert Properties")]),
    "site-internet-restaurant.html": ("Que doit proposer le site internet d'un restaurant ?",
        "Le site internet d'un restaurant doit répondre en quelques secondes aux quatre questions d'un client : que propose la carte, quand est ce ouvert, où se trouve l'établissement et comment réserver. La carte, les horaires, l'adresse et un bouton de réservation sont donc visibles dès l'ouverture de la page, surtout sur téléphone, d'où vient la majorité des visites. La carte se modifie depuis l'administration, sans prestataire, et l'outil de réservation déjà utilisé, comme TheFork ou Zenchef, est intégré au site. Les avis Google peuvent être affichés et mis à jour automatiquement. Une page dédiée aux groupes, aux privatisations et aux événements, avec son formulaire, complète l'ensemble. Aurea Media a notamment créé les sites de Villa Moniris, du Cercle La Raison et du Domaine Duchentyls. Un site de restaurant coûte à partir de 1 000 € HT et il est en ligne en quatre semaines environ.",
        [("Prix", "dès 1 000 € HT"), ("Délai", "4 semaines environ"), ("Réservation", "TheFork, Zenchef ou formulaire"), ("Références", "Villa Moniris, Cercle La Raison")]),
    "site-internet-artisan-btp.html": ("Pourquoi un artisan a t il besoin d'un site internet ?",
        "Avant d'appeler un artisan, un client vérifie quatre choses : des réalisations comparables à son projet, la zone d'intervention, des gages de sérieux et un moyen simple de demander un devis. Le site internet d'un artisan ou d'une entreprise du bâtiment répond à ces questions mieux qu'une simple fiche d'annuaire. Il présente les chantiers en photos, avant et après, ajoutées depuis un téléphone à la fin de chaque intervention, les villes couvertes, l'assurance décennale, les qualifications comme RGE ou Qualibat, et les avis des clients. Un bouton d'appel et un formulaire de devis restent visibles sur mobile. Associé à une fiche Google Business Profile optimisée, le site aide à être trouvé localement. Aurea Media a notamment créé les sites de Novexia Construction, de Normande d'Isolation et de TCM Grand Est. Un site d'artisan coûte à partir de 1 000 € HT et il est en ligne en quatre semaines environ.",
        [("Prix", "dès 1 000 € HT"), ("Délai", "4 semaines environ"), ("Inclus", "galerie de chantiers, devis en ligne"), ("Références", "Novexia, Normande d'Isolation")]),
    "site-internet-coach-consultant.html": ("Que doit montrer le site d'un coach ou d'un consultant ?",
        "Le site internet d'un coach ou d'un consultant doit installer la confiance avant le premier échange, car ce type de prestation se choisit rarement sur le prix. Il expose clairement le positionnement : pour qui, sur quels sujets et avec quelle méthode. Les offres sont présentées sans jargon, avec leurs formats et, si possible, un tarif de départ ou une fourchette. Un appel découverte se réserve directement dans l'agenda, avec un outil comme Calendly. Un blog simple à alimenter permet de publier des articles sur ses sujets d'expertise, qui sont trouvés sur Google et peuvent être cités par les moteurs de réponse comme ChatGPT ou Perplexity. Les références, publications et interventions apportent les preuves. Aurea Media a notamment créé les sites de Sultan Conseil & Leviers, d'Enzym et de l'INAS. Un site de consultant coûte à partir de 1 000 € HT et il est en ligne en quatre semaines environ.",
        [("Prix", "dès 1 000 € HT"), ("Délai", "4 semaines environ"), ("Rendez vous", "Calendly ou Google Agenda"), ("Références", "Sultan Conseil, Enzym, INAS")]),
    "agence-web-hauts-de-seine.html": ("Quelle agence web choisir dans les Hauts de Seine ?",
        "Aurea Media est une agence web indépendante basée à Paris, 60 rue François Ier, qui accompagne les indépendants, TPE et PME des Hauts de Seine : Boulogne-Billancourt, Issy-les-Moulineaux, Neuilly-sur-Seine, Levallois-Perret, Clamart, Nanterre et les autres communes du département. Le fondateur, Alexandre Coury, conduit chaque projet de l'audit à la mise en ligne, sans commercial ni sous traitance, et se déplace dans vos locaux pour le rendez vous de lancement si vous le souhaitez. L'agence crée des sites vitrines à partir de 1 000 € HT et des sites e-commerce à partir de 1 650 € HT, aux mêmes tarifs partout en France. Chaque site est livré avec un référencement technique complet et peut être accompagné d'une optimisation de la fiche Google Business Profile pour la visibilité locale. Un site vitrine est en ligne en quatre semaines en moyenne, et le devis détaillé est envoyé sous 24 heures.",
        [("Basée à", "Paris 8e"), ("Zone", "tout le 92 et l'Île de France"), ("Site vitrine", "dès 1 000 € HT"), ("Devis", "sous 24 heures")]),
    "index.html": ("Qu'est ce qu'Aurea Media ?",
        "Aurea Media est une agence web indépendante fondée en 2021 par Alexandre Coury, développeur et designer basé à Paris. L'agence conçoit des sites vitrines et des sites e-commerce sur mesure pour les indépendants, les TPE et les PME, en France et en Europe. Chaque site est dessiné pour son marché, développé sous WordPress sans constructeur de pages et livré avec un référencement technique complet : balises, données structurées, vitesse de chargement et Search Console. Un site vitrine coûte à partir de 1 000 € HT et un site e-commerce à partir de 1 650 € HT, avec un devis détaillé envoyé sous 24 heures. Le délai moyen de mise en ligne est de quatre semaines. Plus de 45 sites ont été livrés, avec une note de 4,9 sur 5 sur 39 avis Google. L'agence réalise aussi des identités visuelles, des vidéos en motion design et des présentations commerciales.",
        [("Fondée", "2021, à Paris"), ("Site vitrine", "dès 1 000 € HT"), ("E-commerce", "dès 1 650 € HT"), ("Délai moyen", "4 semaines"), ("Avis Google", "4,9 sur 5 (39 avis)")]),
    "creation-site-vitrine.html": ("Qu'est ce qu'un site vitrine sur mesure ?",
        "Un site vitrine est un site internet qui présente une entreprise, ses services et ses réalisations, sans vente en ligne. Son rôle est de rassurer un prospect, d'être trouvé sur Google et de déclencher une prise de contact : appel, demande de devis ou rendez vous. Un site vitrine sur mesure est dessiné spécialement pour l'activité, au lieu de reprendre un modèle partagé par des milliers d'autres sites. Chez Aurea Media, un site vitrine compte en général cinq à huit pages : accueil, services, réalisations, à propos et contact, auxquelles s'ajoutent des pages par métier ou par ville quand la stratégie de référencement le justifie. Il coûte à partir de 1 000 € HT, référencement technique compris, et il est en ligne en quatre semaines en moyenne. Le client reste propriétaire du site et le modifie lui même grâce à une formation incluse.",
        [("Prix", "dès 1 000 € HT"), ("Délai", "4 semaines en moyenne"), ("Pages", "5 à 8 en général"), ("Technologie", "WordPress sur mesure"), ("Inclus", "SEO technique et formation")]),
    "creation-site-ecommerce.html": ("Combien coûte un site e-commerce sur mesure ?",
        "Un site e-commerce sur mesure réalisé par Aurea Media coûte à partir de 1 650 € HT. Il est construit avec WooCommerce, l'extension de vente en ligne de WordPress, ce qui évite les abonnements mensuels et les frais par vente des plateformes fermées. Le prix final dépend surtout du nombre de produits à intégrer, des moyens de paiement et de livraison, et des fonctionnalités comme les abonnements, le multilingue ou les tarifs professionnels. Le paiement passe par Stripe ou PayPal, avec Apple Pay et Google Pay, et l'argent arrive directement sur le compte du marchand. Chaque boutique est livrée avec des fiches produits optimisées pour Google, un tunnel de commande court et une administration utilisable depuis un téléphone. Le délai de réalisation est de six à huit semaines selon la taille du catalogue, et les produits existants peuvent être importés depuis Shopify, Wix ou PrestaShop.",
        [("Prix", "dès 1 650 € HT"), ("Délai", "6 à 8 semaines"), ("Plateforme", "WooCommerce"), ("Paiement", "Stripe, PayPal, Apple Pay"), ("Frais par vente", "aucun ajouté par l'agence")]),
    "refonte-site-internet.html": ("Comment refaire son site sans perdre son référencement ?",
        "Une refonte de site internet consiste à reconstruire un site existant, souvent devenu lent, daté ou peu efficace, sans perdre la visibilité acquise sur Google. Le risque principal d'une refonte est la perte de trafic : des pages disparaissent ou changent d'adresse, et les positions obtenues s'effondrent. Pour l'éviter, Aurea Media commence par un audit des pages qui reçoivent des visites, des mots clés positionnés et des liens entrants. Chaque ancienne adresse est ensuite redirigée, par une redirection permanente 301, vers la page nouvelle qui lui correspond, jamais vers la page d'accueil par défaut. Les contenus utiles sont conservés, ceux qui ne convertissent pas sont réécrits. Après la mise en ligne, la Search Console est surveillée pendant trente jours pour corriger les erreurs. Une refonte de site vitrine coûte à partir de 1 000 € HT, et l'audit de départ est offert avant le devis.",
        [("Prix", "dès 1 000 € HT"), ("Audit", "offert avant devis"), ("Redirections", "301, page par page"), ("Suivi", "30 jours après la mise en ligne")]),
    "logo-identite-visuelle.html": ("Que comprend une identité visuelle ?",
        "Une identité visuelle est l'ensemble des éléments graphiques qui rendent une entreprise reconnaissable : le logo, la palette de couleurs, les typographies et les règles qui encadrent leur usage. Elle se distingue du logo seul, qui n'en est qu'une partie. Chez Aurea Media, une identité visuelle comprend le logo dans toutes ses déclinaisons, version principale, monogramme, versions claire et foncée et favicon, la palette avec ses codes HEX, RVB et CMJN, une paire de typographies adaptée au web et à l'impression, et une charte graphique au format PDF qui explique les marges, les tailles minimales et les usages interdits. Le travail commence par un questionnaire sur les valeurs et la clientèle, se poursuit avec trois pistes créatives et se termine par l'affinage de la piste choisie. Tous les fichiers sources, vectoriels et modifiables, sont cédés au client à la livraison.",
        [("Livrables", "logo, palette, typographies, charte"), ("Pistes", "3 directions créatives"), ("Fichiers", "SVG, PNG, PDF vectoriel"), ("Droits", "cédés au client")]),
    "motion-design.html": ("Qu'est ce que le motion design ?",
        "Le motion design est l'animation de formes, de textes, de logos et d'illustrations pour créer une vidéo, sans tournage avec une caméra. Il sert à expliquer une offre en quelques secondes, à donner vie à une marque ou à capter l'attention sur les réseaux sociaux. Les formats les plus demandés sont l'animation de logo, de trois à six secondes, la vidéo explicative, de soixante à quatre vingt dix secondes avec voix off et sous titres, et les formats courts verticaux pour LinkedIn, Instagram ou TikTok. Le film de marque présenté sur cette page a été conçu et animé par Aurea Media pour sa propre communication. Chaque projet suit quatre étapes validées avec le client : le script, le storyboard, l'animation avec la musique, puis la livraison dans tous les formats utiles. Sur un site, la vidéo est compressée et ne se charge que lorsqu'elle devient visible, pour ne pas ralentir les pages.",
        [("Formats", "logo animé, vidéo explicative, réseaux"), ("Durée", "de 3 secondes à 90 secondes"), ("Livraison", "MP4 et WebM, 16:9, carré, vertical"), ("Étapes", "script, storyboard, animation")]),
    "maquettes-presentation.html": ("Qu'est ce qu'une bonne présentation commerciale ?",
        "Une bonne présentation commerciale raconte une histoire simple : le problème du client, la solution proposée, les preuves qu'elle fonctionne et l'action attendue à la fin. Chaque diapositive porte une seule idée, lisible en quelques secondes, avec peu de texte, des chiffres mis en valeur et des visuels cohérents avec l'identité de l'entreprise. Aurea Media conçoit des pitch decks, des propositions commerciales et des présentations d'entreprise sous PowerPoint, Keynote ou Google Slides. La présentation montrée sur cette page est celle que l'agence envoie à ses propres prospects : cinq diapositives pour la promesse, la méthode, les preuves, les réalisations et l'investissement. Le support est livré avec des modèles de diapositives réutilisables, pour que le client crée seul ses prochaines présentations. Une présentation de dix à vingt diapositives demande environ une semaine de travail, selon le contenu fourni et la part de rédaction.",
        [("Formats", "PowerPoint, Keynote, Google Slides"), ("Délai", "environ 1 semaine"), ("Structure", "problème, solution, preuves, action"), ("Bonus", "modèles réutilisables")]),
    "site-internet-avocat.html": ("Que doit contenir le site internet d'un avocat ?",
        "Le site internet d'un avocat doit permettre à un client de comprendre en quelques secondes si le cabinet traite son problème, combien cela peut coûter et comment prendre rendez vous. Il comprend en général une page par domaine d'intervention, une présentation des avocats et de leur parcours, une page consacrée aux honoraires, des informations pratiques et un moyen de prise de rendez vous, au cabinet ou en visioconférence. La publicité personnelle des avocats est autorisée depuis 2014, dans le respect des principes essentiels de la profession : dignité, loyauté et confidentialité. Les mentions obligatoires, comme le barreau d'inscription, doivent figurer sur le site. Aurea Media a notamment créé le site de Maître Laurence Paoli Culioli, avocate spécialiste en droit de la famille, inscrite au barreau de l'Essonne. Un site de cabinet coûte à partir de 1 000 € HT et il est en ligne en quatre semaines environ.",
        [("Prix", "dès 1 000 € HT"), ("Délai", "4 semaines environ"), ("Pages clés", "expertises, honoraires, rendez vous"), ("Référence", "Maître Laurence Paoli")]),
    "agence-web-paris.html": ("Pourquoi choisir une agence web indépendante à Paris ?",
        "Une agence web indépendante à Paris offre un interlocuteur unique, de la première réunion à la mise en ligne, là où une grande agence répartit un projet entre un commercial, un chef de projet et des sous traitants. Aurea Media est un studio parisien fondé en 2021 par Alexandre Coury, qui conçoit et développe lui même chaque site. Les rendez vous ont lieu à Paris, chez le client en Île de France ou en visioconférence. Pour les entreprises locales, le site est accompagné d'un travail de référencement local : optimisation de la fiche Google Business Profile, pages par ville ou par quartier lorsque c'est pertinent, et données structurées décrivant l'entreprise. Les tarifs sont les mêmes partout, à partir de 1 000 € HT pour un site vitrine et de 1 650 € HT pour un site e-commerce, avec un devis détaillé sous 24 heures et une mise en ligne en quatre semaines en moyenne.",
        [("Basée à", "Paris"), ("Zone", "Île de France, France, Europe"), ("Rendez vous", "sur place ou en visio"), ("Devis", "sous 24 heures")]),
    "tarifs.html": ("Combien coûte un site internet chez Aurea Media ?",
        "Chez Aurea Media, un site vitrine sur mesure coûte à partir de 1 000 € HT et un site e-commerce à partir de 1 650 € HT. L'identité visuelle, le motion design et les présentations sont chiffrés sur devis, selon le périmètre. Le prix final d'un site dépend de trois éléments : le nombre de pages, les fonctionnalités, comme la réservation en ligne, le multilingue ou le blog, et la part de rédaction confiée à l'agence. Chaque prix comprend le design sur mesure, l'adaptation au mobile et à la tablette, le référencement technique, les données structurées, le formulaire de contact, la configuration de la Search Console et une formation à l'administration. Il n'y a pas d'abonnement imposé : seuls l'hébergement et le nom de domaine sont payés chaque année, au nom du client, qui reste propriétaire de son site. Le devis détaillé, ligne par ligne, est gratuit et envoyé sous 24 heures.",
        [("Site vitrine", "dès 1 000 € HT"), ("E-commerce", "dès 1 650 € HT"), ("Abonnement", "aucun imposé"), ("Devis", "gratuit, sous 24 heures")]),
}


def mots(t):
    return len(re.findall(r"[\wÀ-ÿ'’-]+", t))


def bloc_en_bref(fichier):
    q, r, faits = EN_BREF[fichier]
    dl = "".join(f"<div><dt>{a}</dt><dd>{b}</dd></div>" for a, b in faits)
    return f"""<!-- enbref:debut -->
<section class="sec enbref" aria-labelledby="enbref-titre">
  <div class="wrap">
    <div class="eb">
      <div class="eb-txt">
        <span class="eyebrow rv">En bref</span>
        <h2 id="enbref-titre" class="rv">{q}</h2>
        <p class="rv">{r}</p>
        <p class="eb-maj rv">Mis à jour le <time datetime="{MAJ}">{MAJ_TXT}</time> par <a href="agence.html">Alexandre Coury</a>, fondateur d'Aurea Media.</p>
      </div>
      <dl class="eb-faits rv">{dl}</dl>
    </div>
  </div>
</section>
<!-- enbref:fin -->"""


def graphe(fichier, titre, description):
    url, typ, nom, prix, fil = PAGES[fichier]
    page_url = SITE + url
    noeuds = [ORGA, PERSONNE, SITEWEB]
    web = {"@type": "WebPage", "@id": page_url + "#page", "url": page_url, "name": titre, "description": description, "inLanguage": "fr-FR",
           "isPartOf": {"@id": f"{SITE}/#site"}, "about": {"@id": f"{SITE}/#organisation"}, "dateModified": MAJ}
    if fil:
        elements = [{"@type": "ListItem", "position": 1, "name": "Accueil", "item": f"{SITE}/"}]
        for i, (n, u) in enumerate(fil, start=2):
            e = {"@type": "ListItem", "position": i, "name": n}
            e["item"] = SITE + (u if u else url)
            elements.append(e)
        noeuds.append({"@type": "BreadcrumbList", "@id": page_url + "#ariane", "itemListElement": elements})
        web["breadcrumb"] = {"@id": page_url + "#ariane"}
    if typ in ("service", "local"):
        s = {"@type": "Service", "@id": page_url + "#service", "name": nom, "serviceType": nom, "provider": {"@id": f"{SITE}/#organisation"},
             "areaServed": ([{"@type": "AdministrativeArea", "name": "Hauts-de-Seine"}] if "hauts" in url else [{"@type": "City", "name": "Paris"}]) + [{"@type": "AdministrativeArea", "name": "Île-de-France"}] if typ == "local" else [{"@type": "Country", "name": "France"}, {"@type": "Place", "name": "Europe"}],
             "url": page_url}
        if prix:
            s["offers"] = {"@type": "Offer", "priceCurrency": "EUR", "priceSpecification": {"@type": "PriceSpecification", "minPrice": prix, "priceCurrency": "EUR", "valueAddedTaxIncluded": False}}
        noeuds.append(s)
        web["mainEntity"] = {"@id": page_url + "#service"}
    elif typ == "article":
        noeuds.append({"@type": "BlogPosting", "@id": page_url + "#article", "headline": nom, "description": description, "inLanguage": "fr-FR",
                       "author": {"@id": f"{SITE}/#alexandre-coury"}, "publisher": {"@id": f"{SITE}/#organisation"},
                       "datePublished": "2026-06-25", "dateModified": "2026-10-20", "mainEntityOfPage": {"@id": page_url + "#page"},
                       "image": f"{SITE}/wp-content/uploads/prix-site-vitrine.webp"})
    elif typ == "cas":
        noeuds.append({"@type": "CreativeWork", "@id": page_url + "#realisation", "name": nom, "creator": {"@id": f"{SITE}/#organisation"},
                       "about": {"@type": "Organization", "name": "Havel Trading", "url": "https://haveltrading.com/"}, "url": "https://haveltrading.com/", "genre": "Site vitrine"})
        web["mainEntity"] = {"@id": page_url + "#realisation"}
    elif typ == "collection":
        web["@type"] = "CollectionPage"
    elif typ == "apropos":
        web["@type"] = "AboutPage"; web["mainEntity"] = {"@id": f"{SITE}/#alexandre-coury"}
    elif typ == "contact":
        web["@type"] = "ContactPage"
    elif typ == "blog":
        web["@type"] = "CollectionPage"
    noeuds.append(web)
    return {"@context": "https://schema.org", "@graph": noeuds}


def tete(fichier, html):
    titre = re.search(r"<title>(.*?)</title>", html).group(1)
    desc = re.search(r'<meta name="description" content="([^"]*)">', html).group(1)
    url = SITE + PAGES[fichier][0]
    og_type = "article" if PAGES[fichier][1] == "article" else "website"
    meta = f"""<!-- seo:debut -->
<link rel="canonical" href="{url}">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta property="og:locale" content="fr_FR">
<meta property="og:type" content="{og_type}">
<meta property="og:site_name" content="Aurea Media">
<meta property="og:title" content="{titre}">
<meta property="og:description" content="{desc}">
<meta property="og:url" content="{url}">
<meta property="og:image" content="{SITE}/wp-content/themes/aurea/assets/og-aurea-media.jpg">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0A0A0A">
<script type="application/ld+json">{json.dumps(graphe(fichier, titre, desc), ensure_ascii=False, separators=(",", ":"))}</script>
<!-- seo:fin -->
"""
    html = re.sub(r"<!-- seo:debut -->.*?<!-- seo:fin -->\n", "", html, flags=re.S)
    return html.replace("</head>", meta + "</head>", 1)


def appliquer():
    for fichier in PAGES:
        p = RACINE / fichier
        html = tete(fichier, p.read_text())
        html = re.sub(r"<!-- enbref:debut -->.*?<!-- enbref:fin -->\n?", "", html, flags=re.S)
        if fichier in EN_BREF:
            n = mots(EN_BREF[fichier][1])
            if not 134 <= n <= 167:
                print("À REVOIR, longueur du bloc En bref :", fichier, n, "mots")
            main = html.index('<main id="contenu">')
            fin_hero = html.index("</section>", main) + len("</section>")
            html = html[:fin_hero] + "\n" + bloc_en_bref(fichier) + html[fin_hero:]
        p.write_text(html)
    print("SEO et GEO appliqués à", len(PAGES), "pages")


if __name__ == "__main__":
    appliquer()

# -*- coding: utf-8 -*-
"""
Source unique des faits d'Aurea Media.

Les audits SEO, GEO et SXO ont relevé des prix, des nombres d'avis et des
nombres de projets contradictoires d'une page à l'autre (1 000 € ou 1 200 €,
39 ou 40 avis, « 45+ sites vitrines » ou « 45+ projets »). Dans la refonte,
chaque chiffre vit ici, une seule fois, et toutes les pages le lisent.
Dans le thème WordPress, ces valeurs deviendront des réglages (une page
d'options), lus par les blocs et par les données structurées.
"""

SITE = {
    "name": "Aurea Media",
    "url": "https://aurea-media.fr",
    "lang": "fr-FR",
    "founder": "Alexandre Coury",
    "founder_role": "Fondateur, développeur web et consultant SEO",
    "since": 2021,
    "phone": "06 51 28 77 61",
    "phone_intl": "+33 6 51 28 77 61",
    "phone_href": "+33651287761",
    "email": "alexandre.coury@aurea-media.fr",
    "street": "60 rue François Ier",
    "zip": "75008",
    "city": "Paris",
    "district": "Paris 8e",
    "siren": "992 693 473",
    "hours": "Du lundi au vendredi, de 9 h à 19 h",
    "rating": "4,9",
    "rating_num": 4.9,
    "reviews": 39,
    "projects": "45",          # à confirmer : un rechercher/remplacer « 150 » > « 45 » est suspecté
    "response": "24 h",
    "delay_vitrine": "4 semaines",
    "gbp": "https://www.google.com/search?kgmid=/g/11mcqnsjrx",
    "linkedin_company": "https://www.linkedin.com/company/aureamedia/",
    "linkedin_person": "https://www.linkedin.com/in/alexandre-coury/",
    "zones": ["Paris", "Hauts-de-Seine", "Île-de-France", "France entière", "Genève", "Lisbonne", "Montréal", "Dubaï"],
}

# Définition d'entité (GEO) : réutilisée par l'accueil, l'agence, le pied de page,
# les données structurées et le llms.txt.
BRIEF = (
    "Aurea Media est une agence web indépendante basée à Paris 8e, fondée par "
    "Alexandre Coury, qui crée des sites depuis 2021. Le studio conçoit des sites "
    "vitrines (dès 1 000 € HT) et des boutiques WooCommerce (dès 1 650 € HT) sur "
    "mesure pour les indépendants, les TPE et les PME, avec le référencement "
    "naturel intégré dès la conception. Un site vitrine est livré en 4 semaines "
    "en moyenne, et un seul interlocuteur suit le projet du devis à la maintenance."
)

# ---------------------------------------------------------------------------
# Services : une page par intention de recherche
# ---------------------------------------------------------------------------
SERVICES = [
    {
        "id": "vitrine", "num": "01", "path": "/creation-de-site-vitrine-paris/",
        "name": "Création de site vitrine", "short": "Site vitrine", "title_html": "Site <em>vitrine</em>",
        "price": 1000, "price_label": "dès 1 000 € HT", "delay": "3 à 4 semaines",
        "desc": "Un site sur mesure qui rassure, se trouve sur Google et transforme les visites en demandes de devis.",
        "img": "novexia", "icon": "layout",
    },
    {
        "id": "ecommerce", "num": "02", "path": "/creation-de-site-e-commerce-paris/",
        "name": "Création de site e-commerce", "short": "Site e-commerce", "title_html": "Boutique <em>en ligne</em>",
        "price": 1650, "price_label": "dès 1 650 € HT", "delay": "6 à 8 semaines",
        "desc": "Une boutique WooCommerce à votre nom : zéro commission sur les ventes, tunnel d'achat pensé pour convertir.",
        "img": "sunrock", "icon": "bag",
    },
    {
        "id": "refonte", "num": "03", "path": "/refonte-site-internet-paris/",
        "name": "Refonte de site internet", "short": "Refonte", "title_html": "Refonte <em>de site</em>",
        "price": None, "price_label": "sur devis", "delay": "4 à 6 semaines",
        "desc": "Un site moderne et rapide, sans perdre votre trafic : audit, plan de redirections et suivi des positions.",
        "img": "groupe-verisol", "icon": "refresh",
    },
    {
        "id": "seo", "num": "04", "path": "/seo-local-paris/",
        "name": "SEO local et visibilité IA", "short": "SEO local", "title_html": "SEO <em>local</em>",
        "price": None, "price_label": "audit sur devis", "delay": "résultats en 3 à 6 mois",
        "desc": "Sortir dans Google Maps, dans les résultats locaux et dans les réponses des IA : fiche Google, avis, pages locales.",
        "img": "xpert-medical", "icon": "search",
    },
    {
        "id": "branding", "num": "05", "path": "/branding-identite/",
        "name": "Branding et identité visuelle", "short": "Identité visuelle", "title_html": "Identité <em>visuelle</em>",
        "price": None, "price_label": "sur devis", "delay": "1 à 2 semaines",
        "desc": "Logo, charte graphique et guide de marque : trois pistes créatives et une cession de droits totale.",
        "img": "sultan-conseil", "icon": "pen",
    },
    {
        "id": "maquettes", "num": "06", "path": "/maquettes-de-presentation/",
        "name": "Pitch decks et présentations", "short": "Présentations", "title_html": "Pitch <em>decks</em>",
        "price": None, "price_label": "sur devis", "delay": "livré en 1 semaine",
        "desc": "Pitch decks, propositions commerciales et rapports sur mesure, en fichiers 100 % éditables.",
        "img": "dual-tech", "icon": "slides",
    },
]

# ---------------------------------------------------------------------------
# Formules (reprises à l'identique des pages actuelles)
# ---------------------------------------------------------------------------
PLANS = {
    "vitrine": [
        {"name": "Essentiel", "for": "Pour lancer votre activité", "price": 1000, "delay": "3 semaines",
         "items": ["Site 3 pages sur mesure", "Design unique, aucun modèle acheté", "SEO technique de base", "Formulaire de contact RGPD", "Responsive et Core Web Vitals au vert", "Livraison en 3 semaines"]},
        {"name": "Professionnel", "for": "Pour développer votre visibilité", "price": 1800, "delay": "4 semaines", "hl": True,
         "items": ["Site 5 à 7 pages sur mesure", "SEO complet et Search Console", "Blog intégré, prêt à publier", "Rédaction assistée des contenus", "Formation à la prise en main", "Livraison en 4 semaines"]},
        {"name": "Premium", "for": "Pour les projets ambitieux", "price": None, "delay": "selon le périmètre",
         "items": ["Site de 8 pages et plus", "Version bilingue français et anglais", "Fonctionnalités sur mesure", "Stratégie de référencement approfondie", "Identité visuelle en option", "Accompagnement dans la durée"]},
    ],
    "ecommerce": [
        {"name": "Lancement", "for": "Pour démarrer la vente en ligne", "price": 1650, "delay": "6 semaines",
         "items": ["Jusqu'à 30 produits", "Design WooCommerce sur mesure", "Paiement Stripe et PayPal", "Livraison et CGV configurées", "SEO technique de base", "Livraison en 6 semaines"]},
        {"name": "Croissance", "for": "Pour développer vos ventes", "price": 3200, "delay": "8 semaines", "hl": True,
         "items": ["Catalogue illimité et variations", "SEO complet et Google Shopping", "Relance des paniers abandonnés", "Codes promo et programme fidélité", "Blog intégré et rédaction assistée", "Livraison en 8 semaines"]},
        {"name": "Sur mesure", "for": "Pour les catalogues complexes", "price": None, "delay": "selon le périmètre",
         "items": ["Boutique bilingue et multidevise", "Tarifs B2B et comptes clients", "Connexion ERP, caisse ou comptabilité", "Abonnements et ventes récurrentes", "Stratégie SEO et acquisition", "Accompagnement dans la durée"]},
    ],
    "branding": [
        {"name": "Logo", "for": "Pour une identité minimale", "price": None, "delay": "1 à 2 semaines",
         "items": ["Logo en 3 pistes créatives", "Déclinaisons horizontale et verticale", "Palette de couleurs associée", "Fichiers sources livrés", "Livraison en 1 à 2 semaines"]},
        {"name": "Identité complète", "for": "Pour une marque cohérente", "price": None, "delay": "1 à 2 semaines", "hl": True,
         "items": ["Logo, couleurs et typographies", "Guide de marque complet en PDF", "Carte de visite et signature e-mail", "Gabarits pour les réseaux sociaux", "Cession de droits totale"]},
        {"name": "Marque et site", "for": "Pour un lancement complet", "price": None, "delay": "selon le périmètre",
         "items": ["Identité complète incluse", "Site vitrine ou e-commerce associé", "Cohérence totale entre marque et site", "Un seul interlocuteur du début à la fin", "Tarif global avantageux"]},
    ],
    "maquettes": [
        {"name": "Essentiel", "for": "Pour un support court et efficace", "price": None, "delay": "3 à 5 jours",
         "items": ["Jusqu'à 10 slides", "Structure narrative simple", "Mise en page sur mesure", "Fichier Canva ou PowerPoint", "Livraison en 3 à 5 jours"]},
        {"name": "Complet", "for": "Pour un pitch ou une proposition", "price": None, "delay": "1 semaine", "hl": True,
         "items": ["10 à 30 slides", "Structure narrative construite avec vous", "Graphiques et schémas sur mesure", "Respect strict de votre charte", "Retouches incluses"]},
        {"name": "Sur mesure", "for": "Pour les présentations complexes", "price": None, "delay": "selon le périmètre",
         "items": ["30 slides et plus", "Rapports annuels et dossiers longs", "Version bilingue sur demande", "Identité visuelle en option", "Accompagnement sur plusieurs supports"]},
    ],
}

# ---------------------------------------------------------------------------
# Réalisations (20 projets en ligne, textes repris de la page actuelle)
# ---------------------------------------------------------------------------
PROJECTS = [
    {"id": "novexia", "name": "Novexia Construction", "cat": "btp", "type": "Site vitrine", "sector": "BTP et rénovation",
     "url": "https://novexia-construction.fr/", "domain": "novexia-construction.fr",
     "desc": "Entreprise du bâtiment en Occitanie : construction, rénovation, isolation et extensions. Site commercial de 11 pages, une page par métier, devis en ligne et SEO local.",
     "tags": ["WordPress", "11 pages", "SEO local"], "case": "/realisations-agence-web/novexia-construction/"},
    {"id": "sunrock", "name": "Sunrock", "cat": "ecommerce industrie", "type": "Site e-commerce", "sector": "Défense et sécurité",
     "url": "https://www.sunrock.fr/", "domain": "sunrock.fr",
     "desc": "PME française partenaire des ministères des Armées et de l'Intérieur : solutions balistiques, équipements tactiques et formations.",
     "tags": ["E-commerce", "Catalogue produits", "Défense"]},
    {"id": "groupe-verisol", "name": "Groupe Verisol", "cat": "btp industrie", "type": "Site institutionnel", "sector": "Transition énergétique",
     "url": "https://groupe-verisol.fr/", "domain": "groupe-verisol.fr",
     "desc": "Groupe spécialisé dans la transition énergétique. Site premium de 5 pages : présentation des filiales, architecture claire, contact simplifié.",
     "tags": ["WordPress", "5 pages", "SEO"]},
    {"id": "dual-tech", "name": "Dual Tech", "cat": "institutions", "type": "Site événementiel", "sector": "Salon professionnel",
     "url": "https://dualtech-event.eu/", "domain": "dualtech-event.eu",
     "desc": "Salon européen de l'innovation duale et de la souveraineté technologique. Site de 9 pages avec réservation.",
     "tags": ["WordPress", "9 pages", "Réservation"]},
    {"id": "havel-trading", "name": "Havel Trading", "cat": "services international", "type": "Site vitrine", "sector": "Négoce d'énergie, Genève",
     "url": "https://haveltrading.com/", "domain": "haveltrading.com",
     "desc": "Société indépendante de négoce physique de gaz naturel, de GNL et d'électricité sur les marchés européens, basée à Genève.",
     "tags": ["Site vitrine", "Énergie", "Genève"]},
    {"id": "xpert-medical", "name": "Xpert Medical", "cat": "sante", "type": "Site vitrine", "sector": "Centres médicaux",
     "url": "https://xpert-medical.fr/", "domain": "xpert-medical.fr",
     "desc": "Réseau de centres médicaux pluridisciplinaires dédié à l'accès rapide aux soins : consultations, spécialités et services de santé.",
     "tags": ["Site vitrine", "Santé", "Centres médicaux"]},
    {"id": "avocat-paoli", "name": "Maître Laurence Paoli", "cat": "sante", "type": "Site vitrine", "sector": "Avocate, droit de la famille",
     "url": "https://avocat-paoli.fr/", "domain": "avocat-paoli.fr",
     "desc": "Avocate au barreau de l'Essonne, spécialiste en droit de la famille, des personnes et de leur patrimoine.",
     "tags": ["Site vitrine", "Avocate", "Essonne"]},
    {"id": "portugal-expert", "name": "Portugal Expert Properties", "cat": "immobilier international", "type": "Site vitrine", "sector": "Immobilier haut de gamme, Lisbonne",
     "url": "https://portugalexpertproperties.com/", "domain": "portugalexpertproperties.com",
     "desc": "Conception et construction de résidences haut de gamme sur mesure au sud de Lisbonne.",
     "tags": ["Site vitrine", "Immobilier", "Lisbonne"]},
    {"id": "villa-moniris", "name": "Villa Moniris", "cat": "immobilier", "type": "Site de réservation", "sector": "Location saisonnière, Var",
     "url": "https://villamoniris.fr/", "domain": "villamoniris.fr",
     "desc": "Propriété de charme à La Crau pour 14 personnes : trois espaces indépendants, galerie immersive et réservation par espace.",
     "tags": ["Réservation", "Galerie", "Provence"]},
    {"id": "domaine-duchentyls", "name": "Domaine Duchentyls", "cat": "immobilier", "type": "Site vitrine", "sector": "Séjours et réceptions",
     "url": "https://domaine-duchentyls.fr/", "domain": "domaine-duchentyls.fr",
     "desc": "Lieu de charme dédié aux séjours et aux événements, dans un cadre naturel et authentique.",
     "tags": ["Site vitrine", "Séjours", "Événements"]},
    {"id": "normande-isolation", "name": "Normande d'Isolation", "cat": "btp industrie", "type": "Site vitrine", "sector": "Isolation industrielle",
     "url": "https://normande-disolation.com/", "domain": "normande-disolation.com",
     "desc": "Calorifugeage, isolation acoustique, protection incendie, préfabrication et location d'échafaudages.",
     "tags": ["Site vitrine", "Industrie", "Isolation"]},
    {"id": "tcm-grand-est", "name": "TCM Grand Est", "cat": "btp industrie", "type": "Site vitrine", "sector": "Soudure et tuyauterie",
     "url": "http://www.tcm-grandest.fr/", "domain": "tcm-grandest.fr",
     "desc": "Travaux industriels sur mesure en soudure, tuyauterie et mécanique, pour des secteurs exigeants.",
     "tags": ["Site vitrine", "Industrie", "Grand Est"]},
    {"id": "barber-korner", "name": "Barber Korner", "cat": "services", "type": "Site vitrine", "sector": "Barbier, Paris",
     "url": "https://barber-korner.fr/", "domain": "barber-korner.fr",
     "desc": "Salon de coiffure et barbier traditionnel à Paris, avec prise de rendez-vous en ligne.",
     "tags": ["Site vitrine", "Rendez-vous en ligne", "Paris"]},
    {"id": "eddy-bilou", "name": "Eddy Bilou", "cat": "services", "type": "Portfolio", "sector": "Photographe",
     "url": "https://eddybilouphotography.com/", "domain": "eddybilouphotography.com",
     "desc": "Photographe de mariage à l'atmosphère cinématographique : un portfolio qui laisse toute la place aux images.",
     "tags": ["Portfolio", "Photographie", "Galerie"]},
    {"id": "sultan-conseil", "name": "Sultan Conseil et Leviers", "cat": "services international", "type": "Site vitrine", "sector": "Conseil stratégique",
     "url": "https://sultan.cl/", "domain": "sultan.cl",
     "desc": "Cabinet indépendant qui accompagne les transformations à fort enjeu, entre politique, économie et géopolitique.",
     "tags": ["Site vitrine", "Conseil", "International"]},
    {"id": "enzym", "name": "Enzym", "cat": "services", "type": "Site vitrine", "sector": "Coaching et formation",
     "url": "https://enzymco.fr", "domain": "enzymco.fr",
     "desc": "Coaching et formation pour les entreprises, les dirigeants, les managers et les particuliers.",
     "tags": ["Site vitrine", "Coaching", "Formation"]},
    {"id": "cercle-la-raison", "name": "Cercle La Raison", "cat": "services", "type": "Plateforme privée", "sector": "Réservation d'événements",
     "url": "https://raison-cercle.fr", "domain": "raison-cercle.fr",
     "desc": "Cercle privé de réservation d'événements d'exception, dans un cadre confidentiel et raffiné.",
     "tags": ["Plateforme", "Membres", "Réservation"]},
    {"id": "inas", "name": "INAS", "cat": "institutions", "type": "Site institutionnel", "sector": "Think tank",
     "url": "https://inas-france.fr/", "domain": "inas-france.fr",
     "desc": "Institut National des Affaires Stratégiques, think tank indépendant consacré à la souveraineté française et européenne.",
     "tags": ["Site institutionnel", "Think tank", "Analyses"]},
    {"id": "centre-franco-iranien", "name": "Centre Franco-Iranien", "cat": "institutions", "type": "Site associatif", "sector": "Association culturelle",
     "url": "https://iff-paris.com/", "domain": "iff-paris.com",
     "desc": "Association fondée en 2016 pour les échanges culturels, économiques, scientifiques et sportifs entre la France et l'Iran.",
     "tags": ["Association", "Culture", "Événements"]},
    {"id": "changer-d-air-massy", "name": "Changer d'air à Massy", "cat": "institutions", "type": "Site de campagne", "sector": "Élections municipales 2026",
     "url": "https://massy-changer-dair.fr/", "domain": "massy-changer-dair.fr",
     "desc": "Plateforme de campagne municipale : programme par axes, actualités et mobilisation des habitants.",
     "tags": ["Campagne", "Programme", "Massy"]},
]

PROJECT_CATS = [
    ("tout", "Tous"),
    ("btp", "BTP et rénovation"),
    ("industrie", "Industrie"),
    ("sante", "Santé et droit"),
    ("immobilier", "Immobilier et tourisme"),
    ("services", "Commerces et services"),
    ("institutions", "Institutions"),
    ("ecommerce", "E-commerce"),
    ("international", "International"),
]

# ---------------------------------------------------------------------------
# Avis Google (seuls les avis reproduits sur le site actuel ; aucun inventé)
# ---------------------------------------------------------------------------
REVIEWS = [
    {"name": "Arthur Jorrot", "meta": "Local Guide · septembre 2025",
     "text": "Très bon accompagnement tout au long du projet. À l'écoute, réactif, et le site correspond vraiment à ce que j'avais en tête. Une collaboration simple, efficace et agréable."},
    {"name": "Charles Culioli", "meta": "Janvier 2025",
     "text": "Je cherchais une agence web fiable pour mon site vitrine, j'ai trouvé un interlocuteur à l'écoute, réactif et pro. Le résultat dépasse mes attentes."},
    {"name": "Victor H.", "meta": "Janvier 2025",
     "text": "Je ne savais pas vers qui me tourner pour mon site vitrine, on m'a recommandé Aurea Media. Tout de suite compris ce que je cherchais, le site me ressemble vraiment."},
]

# ---------------------------------------------------------------------------
# Articles conservés après les fusions recommandées par l'audit
# (cat : vitrine, ecommerce, seo, identite, metiers ; date = dernière mise à jour)
# ---------------------------------------------------------------------------
POSTS = [
    {"slug": "prix-site-vitrine-paris", "cat": "vitrine", "date": "2026-09-25", "read": 12, "title": "Combien coûte un site vitrine à Paris en 2026 ?", "excerpt": "Les vrais prix du marché parisien, du freelance à l'agence, et ce qui les justifie : pages, contenus, SEO, propriété du site."},
    {"slug": "combien-coute-un-site-e-commerce", "cat": "ecommerce", "date": "2026-06-01", "read": 11, "title": "Combien coûte un site e-commerce en 2026 ?", "excerpt": "Le budget d'une boutique en ligne selon votre profil, votre catalogue et vos ambitions, avec les coûts cachés à anticiper."},
    {"slug": "refonte-site-internet", "cat": "vitrine", "date": "2026-08-03", "read": 10, "title": "Refonte de site internet : quand et comment refaire son site", "excerpt": "Les signaux qui imposent une refonte, les étapes pour la réussir sans perdre votre référencement et les budgets réels."},
    {"slug": "optimiser-sa-fiche-google-my-business", "cat": "seo", "date": "2026-08-10", "read": 12, "title": "Optimiser sa fiche Google Business Profile en 2026", "excerpt": "Catégories, services, photos, avis et publications : le guide complet pour apparaître dans le pack local Google Maps."},
    {"slug": "shopify-vs-woocommerce", "cat": "ecommerce", "date": "2026-06-01", "read": 11, "title": "Shopify ou WooCommerce : lequel choisir pour votre boutique ?", "excerpt": "Prix réels, SEO, liberté et propriété : le comparatif pour choisir la bonne plateforme selon votre projet."},
    {"slug": "anatomie-site-vitrine-qui-convertit", "cat": "vitrine", "date": "2026-09-16", "read": 12, "title": "Anatomie d'un site vitrine qui convertit", "excerpt": "Le plan complet, zone par zone et page par page, d'un site qui transforme les visiteurs en contacts qualifiés."},
    {"slug": "site-vitrine-qui-ne-genere-pas-de-clients", "cat": "vitrine", "date": "2026-08-31", "read": 9, "title": "Pourquoi votre site vitrine ne génère ni clients ni leads", "excerpt": "Votre site existe mais le téléphone ne sonne pas. Les huit causes les plus fréquentes, et comment les corriger."},
    {"slug": "avis-google", "cat": "seo", "date": "2026-09-16", "read": 12, "title": "Avis Google : en obtenir plus et améliorer votre note", "excerpt": "Demander, répondre, gérer les avis négatifs : la méthode pour faire de vos avis un levier de visibilité locale."},
    {"slug": "wordpress-vs-webflow", "cat": "vitrine", "date": "2026-06-01", "read": 10, "title": "WordPress ou Webflow : quel CMS pour un site pro ?", "excerpt": "Deux outils qui ne répondent pas au même besoin. Autonomie, coût, SEO et évolutivité comparés sans parti pris."},
    {"slug": "creer-un-site-vitrine-soi-meme-ou-en-agence", "cat": "vitrine", "date": "2026-07-06", "read": 10, "title": "Créer un site vitrine : soi-même, freelance ou agence ?", "excerpt": "Wix en un week-end, un freelance ou une agence : le vrai coût de chaque option, pas seulement financier."},
    {"slug": "creer-un-site-e-commerce-professionnel", "cat": "ecommerce", "date": "2026-07-09", "read": 13, "title": "Créer un site e-commerce professionnel : les étapes", "excerpt": "Les étapes, les fonctionnalités indispensables et les erreurs à éviter pour une boutique qui vend vraiment."},
    {"slug": "choisir-agence-web-paris", "cat": "vitrine", "date": "2026-09-16", "read": 11, "title": "Choisir une agence web à Paris : 7 critères", "excerpt": "Budget, références, expertise SEO, méthode : les critères qui comptent et les cinq questions à poser avant de signer."},
    {"slug": "logo-charte-graphique-identite-visuelle", "cat": "identite", "date": "2026-06-04", "read": 11, "title": "Logo, charte graphique, identité visuelle : les différences", "excerpt": "Trois termes souvent confondus. Les livrables concrets de chacun et comment savoir ce dont vous avez besoin."},
    {"slug": "refonte-identite-visuelle-entreprise", "cat": "identite", "date": "2026-09-14", "read": 9, "title": "Refonte d'identité visuelle : moderniser sans perdre sa reconnaissance", "excerpt": "Les signaux d'alerte, les étapes d'une refonte réussie et le budget réel, avec un simulateur de périmètre."},
    {"slug": "optimiser-fiche-google-my-business-coach", "cat": "seo", "date": "2026-08-15", "read": 7, "title": "Fiche Google sans local : le guide pour coachs et consultants", "excerpt": "Catégorie, zone desservie sans exposer son adresse, collecte d'avis : être visible localement sans bureau fixe."},
    {"slug": "site-vitrine-avocat", "cat": "metiers", "date": "2026-07-23", "read": 14, "title": "Site internet pour avocat : pages, déontologie et SEO", "excerpt": "Les pages indispensables, les contraintes déontologiques du web et les leviers pour être trouvé sur « avocat Paris »."},
    {"slug": "site-vitrine-medecin", "cat": "metiers", "date": "2026-07-27", "read": 13, "title": "Site internet pour médecin : cabinet, RGPD santé et rendez-vous", "excerpt": "Pages essentielles, données de santé, prise de rendez-vous en ligne et SEO local pour un cabinet médical."},
    {"slug": "site-vitrine-kinesitherapeute", "cat": "metiers", "date": "2026-08-06", "read": 8, "title": "Site internet pour kinésithérapeute : structure et SEO", "excerpt": "Ne plus dépendre uniquement de Doctolib : structure par spécialité, obligations RGPD et visibilité locale."},
    {"slug": "site-vitrine-architecte", "cat": "metiers", "date": "2026-07-30", "read": 12, "title": "Site internet pour architecte : un portfolio qui décroche des projets", "excerpt": "Mettre en scène vos réalisations, qualifier les demandes et se positionner sur les recherches locales."},
    {"slug": "site-vitrine-restaurant", "cat": "metiers", "date": "2026-07-20", "read": 12, "title": "Site internet pour restaurant : attirer plus de clients", "excerpt": "Les sections indispensables, les erreurs à éviter et les leviers locaux pour être trouvé et réservé en ligne."},
    {"slug": "site-vitrine-coach-consultant", "cat": "metiers", "date": "2026-09-16", "read": 11, "title": "Site internet pour coach ou consultant : les indispensables", "excerpt": "Le seul actif digital que vous possédez vraiment : crédibilité, offre claire et prise de contact sans friction."},
    {"slug": "site-vitrine-pour-artisan", "cat": "metiers", "date": "2026-06-01", "read": 10, "title": "Site internet pour artisan : ce qu'il faut savoir", "excerpt": "Répondre en 10 secondes à trois questions : faites-vous ce dont j'ai besoin, dans ma zone, et êtes-vous sérieux ?"},
    {"slug": "site-vitrine-artisan-renovateur-en-batiment", "cat": "metiers", "date": "2026-08-24", "read": 7, "title": "Site internet pour entreprise de rénovation : vendre un chantier", "excerpt": "Un chantier de rénovation ne se vend pas comme un dépannage : ce qui change sur le site, exemple réel à l'appui."},
]

POST_CATS = [
    ("tout", "Tous les articles"),
    ("vitrine", "Site vitrine"),
    ("ecommerce", "E-commerce"),
    ("seo", "SEO et Google"),
    ("identite", "Identité visuelle"),
    ("metiers", "Guides métiers"),
]

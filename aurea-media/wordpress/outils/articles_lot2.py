"""Articles du blog, lot 2."""

LOT2 = [
    {
        'slug': 'refonte-site-referencement', 'ordre': 4,
        'titre': 'Refonte de site : comment la réussir sans perdre son référencement',
        'categorie': ('Référencement', 'referencement'),
        'mot_cle': 'sans perdre son référencement',
        'titre_seo': 'Refonte de site sans perdre son référencement : 8 étapes',
        'description': 'Refonte de site sans perdre son référencement : inventaire des pages, plan de redirections 301, préproduction, mise en ligne et suivi, étape par étape.',
        'extrait': 'Une refonte mal préparée peut effacer des années de référencement en quelques jours. Les huit étapes pour changer de site en gardant vos positions sur Google.',
        'image': ('refonte-site-internet-referencement.webp', 'Designer qui dessine des maquettes de site sur papier devant un écran d’ordinateur'),
        'intro': 'Changer de site est souvent nécessaire : design daté, site lent, impossible à modifier. Mais une refonte mal préparée peut faire chuter votre trafic du jour au lendemain. La bonne nouvelle : on peut refondre un site sans perdre son référencement, à condition de suivre une méthode précise, du premier inventaire jusqu’au suivi après la mise en ligne.',
        'bref': ('En bref : comment refondre un site sans perdre son référencement ?',
                 'Pour refondre un site sans perdre son référencement, il faut d’abord faire l’inventaire de toutes les adresses existantes et repérer celles qui apportent du trafic. On conserve ensuite les adresses qui fonctionnent, et chaque adresse qui change reçoit une redirection 301 vers la nouvelle page équivalente, jamais vers l’accueil. Les contenus et les balises qui marchent sont gardés ou améliorés. Le nouveau site est testé sur une version de préproduction, non indexée, puis mis en ligne avec un plan du site à jour, déclaré dans la Google Search Console. Pendant les semaines qui suivent, on surveille les erreurs 404 et l’évolution du trafic. Des fluctuations temporaires sont normales.'),
        'sections': [
            ('Pourquoi une refonte peut faire chuter votre trafic', [
                ('p', 'Google a appris à associer chacune de vos adresses à des recherches précises. Si ces adresses disparaissent, changent sans redirection ou perdent leur contenu, Google doit tout réévaluer, et vos positions peuvent baisser.'),
                ('ul', [
                    'Des pages supprimées sans redirection, qui renvoient une erreur 404.',
                    'Des redirections groupées vers la page d’accueil, que Google traite comme des pages introuvables.',
                    'Des textes raccourcis au profit du design, qui font perdre la pertinence des pages.',
                    'Un site laissé en « noindex » après la mise en ligne, par oubli.',
                    'Un nouveau site plus lent, surtout sur mobile.',
                ]),
            ]),
            ('Étape 1 : faire l’inventaire de toutes les pages', [
                ('p', 'Listez toutes les adresses du site actuel : pages, articles, catégories, images importantes. Croisez trois sources : le plan du site XML, un outil d’exploration du site, et le rapport « Pages » de la Google Search Console, qui montre aussi les adresses que vous auriez oubliées.'),
            ]),
            ('Étape 2 : repérer les pages qui rapportent', [
                ('p', 'Dans la Search Console, le rapport « Performances » indique les pages qui reçoivent des clics et les recherches associées. Ces pages sont prioritaires : leur adresse, leur contenu et leurs balises doivent être conservés ou améliorés, jamais appauvris.'),
            ]),
            ('Étape 3 : garder les adresses qui fonctionnent', [
                ('p', 'La meilleure redirection est celle qu’on n’a pas besoin de faire. Si une adresse est courte, lisible et bien positionnée, gardez la telle quelle dans la nouvelle arborescence. Ne changez une adresse que si le gain est réel.'),
            ]),
            ('Étape 4 : préparer le plan de redirections 301', [
                ('p', 'Chaque ancienne adresse qui change doit pointer, par une redirection 301, vers la nouvelle page la plus proche par son sujet. Préparez un tableau à deux colonnes, ancienne adresse et nouvelle adresse, et faites le valider avant la mise en ligne.'),
                ('table', ['Ancienne adresse', 'Nouvelle adresse', 'Bon choix ?'], [
                    ['/creation-de-site-vitrine-paris/', '/creation-site-vitrine/', 'Oui : même sujet'],
                    ['/nos-tarifs-2023/', '/tarifs/', 'Oui : page équivalente'],
                    ['/ancien-article-seo/', '/', 'Non : jamais vers l’accueil'],
                    ['/page-sans-equivalent/', 'Page la plus proche, ou vraie 404', 'Oui, si rien ne correspond'],
                ], 'Exemples de plan de redirections'),
                ('encart', 'Vécu :', 'lors de la refonte de notre propre site, d’anciennes redirections renvoyaient nos deux pages principales vers l’accueil, et toutes les adresses inconnues aussi. Google ne pouvait plus les indexer. Il a fallu désactiver ces redirections et les réorienter page par page : c’est exactement ce que ce plan évite.'),
            ]),
            ('Étape 5 : conserver et améliorer les contenus', [
                ('p', 'Reprenez les textes des pages qui fonctionnent, en les améliorant : structure en titres H2 et H3, informations à jour, exemples, questions fréquentes. Conservez les titres SEO et les descriptions qui marchent, ou réécrivez les pour qu’ils soient plus précis. Ne supprimez pas un contenu utile pour gagner de la place.'),
            ]),
            ('Étape 6 : tester sur une version de préproduction', [
                ('p', 'Le nouveau site est construit sur une adresse de test, bloquée aux moteurs de recherche. On y vérifie les redirections, les liens internes, les formulaires, l’affichage sur mobile et la vitesse. Ce n’est qu’après ces tests que l’on bascule.'),
            ]),
            ('Étape 7 : mettre en ligne proprement', [
                ('ol', [
                    'Retirer le blocage « noindex » de la version de test.',
                    'Activer les redirections 301 au moment de la bascule.',
                    'Générer un plan du site XML à jour et le déclarer dans la Search Console.',
                    'Si le nom de domaine change, utiliser l’outil de changement d’adresse de la Search Console.',
                    'Vérifier les pages principales une par une, sur ordinateur et sur mobile.',
                ]),
            ]),
            ('Étape 8 : surveiller les semaines suivantes', [
                ('img', 'suivi-trafic-apres-refonte.webp', 'Après la mise en ligne, on surveille le trafic et les erreurs pendant plusieurs semaines', 'Écran d’ordinateur affichant des courbes de statistiques de trafic'),
                ('p', 'Pendant plusieurs semaines, surveillez dans la Search Console les erreurs 404, les pages exclues de l’index et l’évolution des clics. Corrigez chaque adresse oubliée par une redirection. Des variations temporaires sont normales le temps que Google explore le nouveau site ; une baisse durable signale un problème à corriger.'),
            ]),
            ('Les indicateurs à comparer avant et après', [
                ('p', 'Notez ces chiffres juste avant la mise en ligne, puis comparez les chaque semaine pendant deux mois. C’est la seule façon objective de savoir si la refonte a réussi.'),
                ('table', ['Indicateur', 'Où le trouver', 'Ce qu’il révèle'], [
                    ['Clics et impressions', 'Search Console, rapport Performances', 'L’évolution globale de la visibilité'],
                    ['Pages indexées', 'Search Console, rapport Pages', 'Les pages que Google a bien prises en compte'],
                    ['Erreurs 404', 'Search Console et outil d’exploration', 'Les redirections oubliées'],
                    ['Positions sur vos recherches clés', 'Search Console ou outil de suivi', 'Les pages qui gagnent ou perdent'],
                    ['Demandes de contact', 'Formulaire, téléphone, statistiques', 'Ce qui compte vraiment pour votre activité'],
                ], 'Tableau de suivi d’une refonte'),
            ]),
            ('Faire appel à un professionnel pour votre refonte', [
                ('p', 'Une [refonte de site internet](refonte-site-internet) chez Aurea Media comprend l’inventaire des pages, le plan de redirections et le suivi après la mise en ligne. L’objectif est double : un site plus beau et plus rapide, qui garde, voire améliore, vos positions sur Google. Pour structurer le nouveau site, lisez aussi notre article sur les [pages indispensables d’un site vitrine](article:pages-indispensables-site-vitrine).'),
            ]),
        ],
        'faq': [
            ('Combien de temps faut il pour retrouver ses positions après une refonte ?', 'Quand la refonte est bien préparée, les variations sont généralement limitées et temporaires, le temps que Google explore le nouveau site. Si le trafic baisse durablement, il faut chercher les redirections manquantes ou les contenus perdus.'),
            ('Peut on changer de nom de domaine pendant une refonte ?', 'Oui, mais cela ajoute un risque. Il faut rediriger chaque adresse de l’ancien domaine vers la nouvelle, et signaler le changement dans la Google Search Console. Si possible, évitez de changer de domaine et de structure en même temps.'),
            ('Faut il rediriger toutes les anciennes pages vers l’accueil ?', 'Non. Une redirection massive vers l’accueil est traitée par Google comme une page introuvable. Chaque ancienne page doit pointer vers la page équivalente, ou renvoyer une vraie erreur 404 si aucune page ne correspond.'),
        ],
        'appel': ('Vous préparez la refonte de votre site ?', 'Audit offert avant devis, avec l’inventaire de vos pages qui rapportent.', 'Demander un audit', 'contact'),
        'sources': [
            ('Déplacer un site en changeant d’URL, Google Search Central', 'https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes?hl=fr'),
            ('Redirections et recherche Google, Google Search Central', 'https://developers.google.com/search/docs/crawling-indexing/301-redirects?hl=fr'),
            ('Google Search Console', 'https://search.google.com/search-console'),
        ],
    },
    {
        'slug': 'woocommerce-ou-shopify', 'ordre': 5,
        'titre': 'WooCommerce ou Shopify : que choisir pour votre boutique en ligne ?',
        'categorie': ('E-commerce', 'e-commerce'),
        'mot_cle': 'woocommerce ou shopify',
        'titre_seo': 'WooCommerce ou Shopify : que choisir pour sa boutique ?',
        'description': 'WooCommerce ou Shopify : propriété, coûts réels, personnalisation, référencement et maintenance. Le comparatif pour choisir votre boutique en ligne.',
        'extrait': 'Les deux solutions les plus utilisées pour créer une boutique en ligne, comparées critère par critère : propriété, coûts réels, liberté, référencement et maintenance.',
        'image': ('woocommerce-ou-shopify-boutique.webp', 'Sacs de shopping rouges et noirs posés sur un fond sombre'),
        'intro': 'WooCommerce ou Shopify : c’est la question que se posent la plupart des commerçants qui veulent vendre en ligne. Les deux solutions sont solides et permettent de créer une belle boutique, mais elles reposent sur deux logiques opposées. Précision utile : chez Aurea Media, nous construisons nos boutiques sous WooCommerce. Ce comparatif reste honnête, et Shopify est parfois le meilleur choix.',
        'bref': ('En bref : faut il choisir WooCommerce ou Shopify ?',
                 'WooCommerce est une extension gratuite et libre qui transforme un site WordPress en boutique. Le site est installé sur l’hébergement de votre choix et vous appartient entièrement : vous pouvez tout personnaliser, mais vous devez assurer les mises à jour et la maintenance, seul ou avec un prestataire. Shopify est une solution hébergée par abonnement : tout est pris en charge, la prise en main est rapide, mais vous dépendez de la plateforme, de ses formules et de ses applications payantes. WooCommerce convient bien aux entreprises qui veulent un site unique, riche en contenus et sans commission de plateforme. Shopify convient aux commerçants qui veulent démarrer vite sans gérer la technique.'),
        'sections': [
            ('Deux logiques différentes', [
                ('p', '<strong>WooCommerce</strong> est une extension libre pour WordPress. Vous installez le site sur un hébergement que vous louez, à votre nom. Vous êtes propriétaire du site, des données et du code.'),
                ('p', '<strong>Shopify</strong> est un service en ligne, sur abonnement. Shopify héberge et maintient la boutique pour vous. En contrepartie, vous utilisez la plateforme selon ses règles et ses formules.'),
            ]),
            ('Le comparatif critère par critère', [
                ('table', ['Critère', 'WooCommerce', 'Shopify'], [
                    ['Propriété', 'Site, données et code à vous', 'Boutique hébergée par la plateforme'],
                    ['Coût de départ', 'Extension gratuite, création du site à prévoir', 'Abonnement mensuel selon la formule'],
                    ['Coûts récurrents', 'Hébergement, extensions éventuelles, maintenance', 'Abonnement, applications, frais de paiement'],
                    ['Personnalisation', 'Totale', 'Large, dans le cadre de la plateforme'],
                    ['Contenus et blog', 'Toute la puissance de WordPress', 'Blog intégré plus simple'],
                    ['Maintenance technique', 'À assurer (vous ou un prestataire)', 'Prise en charge par Shopify'],
                    ['Prise en main', 'Demande un peu d’accompagnement', 'Très rapide'],
                ], 'Comparatif WooCommerce et Shopify'),
                ('p', 'Les tarifs de Shopify évoluent régulièrement : consultez la grille officielle pour connaître le prix des formules et les éventuels frais de transaction selon le moyen de paiement choisi (lien en fin d’article).'),
            ]),
            ('Les coûts réels sur plusieurs années', [
                ('p', 'Comparer le prix de départ ne suffit pas. Additionnez les coûts sur trois ans :'),
                ('ul', [
                    '<strong>Avec WooCommerce</strong> : la création de la boutique, l’hébergement, les éventuelles extensions premium et la maintenance. Il n’y a pas de commission de plateforme sur vos ventes, seulement les frais de votre solution de paiement, comme Stripe ou PayPal.',
                    '<strong>Avec Shopify</strong> : l’abonnement mensuel, les applications payantes qui s’ajoutent souvent au fil du temps, un thème premium éventuel, et les frais de paiement.',
                ]),
                ('p', 'Pour une petite boutique qui démarre, Shopify peut être moins cher au début. Pour une boutique qui grandit et ajoute des fonctionnalités, les abonnements aux applications peuvent peser lourd.'),
            ]),
            ('Le référencement : WordPress a une longueur d’avance sur les contenus', [
                ('img', 'vente-en-ligne-commercant.webp', 'Une boutique en ligne bien référencée prolonge la relation avec vos clients', 'Commerçant souriant qui encaisse une cliente avec un terminal de paiement'),
                ('p', 'Les deux solutions permettent un bon référencement technique. La différence se joue sur les contenus : WordPress est d’abord un outil de publication, ce qui facilite les guides d’achat, les articles de conseils et les pages éditoriales qui attirent du trafic. Shopify impose aussi des préfixes dans ses adresses, comme /products/ ou /collections/, alors que WooCommerce laisse plus de liberté sur la structure des URL.'),
            ]),
            ('Paiement, livraison et fonctionnalités', [
                ('p', 'Les deux solutions couvrent les besoins courants d’une boutique : paiement par carte, modes de livraison, codes promo, comptes clients, e-mails de commande. La différence tient à la manière d’ajouter des fonctionnalités.'),
                ('ul', [
                    '<strong>Avec WooCommerce</strong>, on ajoute des extensions, gratuites ou payantes, ou on développe une fonction sur mesure. Le choix est immense, mais il faut sélectionner des extensions fiables et maintenues.',
                    '<strong>Avec Shopify</strong>, on installe des applications depuis la boutique officielle, souvent payantes au mois. C’est simple, mais chaque application ajoute un coût récurrent.',
                ]),
                ('p', 'Pour le paiement, WooCommerce se connecte notamment à Stripe et PayPal. Shopify propose sa propre solution de paiement et accepte aussi d’autres prestataires, sous conditions : vérifiez les frais applicables sur la grille officielle.'),
            ]),
            ('La maintenance, en pratique', [
                ('p', 'C’est le point qui inquiète le plus les commerçants qui envisagent WooCommerce. En pratique, la maintenance consiste à mettre à jour WordPress, WooCommerce et les extensions, à faire des sauvegardes régulières et à surveiller la sécurité et la vitesse. Comptez quelques interventions par mois, que vous pouvez faire vous même ou confier à un prestataire dans le cadre d’un contrat de maintenance.'),
                ('p', 'Avec Shopify, ces opérations sont prises en charge par la plateforme. En contrepartie, vous ne choisissez ni l’hébergement, ni le rythme des évolutions, ni certaines limites techniques.'),
            ]),
            ('Quand choisir WooCommerce', [
                ('ul', [
                    'Vous voulez un site unique, au design sur mesure.',
                    'Le contenu compte pour vous : conseils, guides, blog.',
                    'Vous voulez rester propriétaire de votre boutique et de vos données.',
                    'Vous avez déjà un site WordPress et voulez y ajouter la vente.',
                    'Vous préférez un coût maîtrisé sans abonnements cumulés.',
                ]),
            ]),
            ('Quand choisir Shopify', [
                ('ul', [
                    'Vous voulez démarrer vite, sans gérer la technique.',
                    'Votre boutique est simple et le contenu éditorial compte peu.',
                    'Vous préférez un abonnement tout compris à une maintenance à organiser.',
                    'Vous vendez sur plusieurs canaux que Shopify connecte nativement.',
                ]),
            ]),
            ('Notre conseil selon votre situation', [
                ('table', ['Votre situation', 'Solution conseillée', 'Pourquoi'], [
                    ['Artisan ou créateur qui vend quelques produits en plus de ses services', 'WooCommerce', 'La boutique s’ajoute au site vitrine existant'],
                    ['Marque qui mise sur le contenu, les guides et le référencement', 'WooCommerce', 'Toute la puissance de WordPress pour publier'],
                    ['Commerçant qui veut tester la vente en ligne rapidement, seul', 'Shopify', 'Mise en route rapide, sans technique'],
                    ['Boutique au catalogue important et aux besoins spécifiques', 'WooCommerce', 'Développements sur mesure possibles, sans abonnements cumulés'],
                ], 'Choisir selon votre profil'),
                ('p', 'Dans tous les cas, le choix de la solution compte moins que la qualité des fiches produits, des photos et du parcours d’achat sur mobile. Une boutique bien pensée vend, quelle que soit la technologie.'),
            ]),
            ('Notre approche chez Aurea Media', [
                ('p', 'Nous créons des [boutiques WooCommerce sur mesure](creation-site-ecommerce), à partir de 1 650 € HT, avec paiement par Stripe et PayPal et aucune commission sur vos ventes. La boutique est à votre nom et vous êtes formé pour gérer vos produits et vos commandes en autonomie. Exemple : la boutique [Perle Hair](realisation:perle-hair). Si vous hésitez encore sur le type de site, lisez notre article [site vitrine ou site e-commerce](article:site-vitrine-ou-site-e-commerce).'),
            ]),
        ],
        'faq': [
            ('Peut on passer de Shopify à WooCommerce, ou l’inverse ?', 'Oui. Les produits, les clients et les commandes peuvent être exportés et importés, mais le design et certaines fonctionnalités sont à refaire. Il faut aussi rediriger les anciennes adresses vers les nouvelles pour garder le référencement.'),
            ('WooCommerce est il vraiment gratuit ?', 'L’extension WooCommerce est gratuite. Il faut en revanche un hébergement, la création du site et éventuellement des extensions payantes pour certaines fonctionnalités avancées.'),
            ('Quelle solution est la plus sécurisée ?', 'Shopify gère la sécurité de la plateforme. Avec WooCommerce, la sécurité dépend des mises à jour, de l’hébergement et des sauvegardes : c’est très fiable quand la maintenance est faite régulièrement.'),
        ],
        'appel': ('Vous voulez lancer votre boutique en ligne ?', 'Boutique WooCommerce sur mesure dès 1 650 € HT. Devis sous 24 h.', 'Demander mon devis', 'contact'),
        'sources': [
            ('WooCommerce, site officiel', 'https://woocommerce.com/'),
            ('Tarifs de Shopify, site officiel', 'https://www.shopify.com/fr/tarifs'),
        ],
    },
    {
        'slug': 'referencement-local-google', 'ordre': 6,
        'titre': 'Référencement local : 10 actions pour être visible sur Google dans votre ville',
        'categorie': ('Référencement', 'referencement'),
        'mot_cle': 'référencement local',
        'titre_seo': 'Référencement local : 10 actions pour être visible sur Google',
        'description': 'Référencement local : fiche Google, avis clients, pages locales, données structurées et annuaires. 10 actions pour être trouvé dans votre ville.',
        'extrait': 'Fiche Google, avis, pages locales, données structurées : dix actions concrètes pour apparaître quand un client cherche votre métier dans votre ville.',
        'image': ('referencement-local-commerce.webp', 'Intérieur d’une boutique de vêtements avec portants et étagères'),
        'intro': 'Le référencement local, c’est l’art d’apparaître quand un client cherche votre métier près de chez lui : « plombier Boulogne », « restaurant Montpellier », « avocat Essonne ». Pour un artisan, un commerce ou une profession libérale, c’est souvent la première source de clients. Voici dix actions concrètes, de la fiche Google à votre site, pour gagner en visibilité dans votre ville.',
        'bref': ('En bref : comment améliorer son référencement local ?',
                 'Le référencement local repose sur trois critères que Google présente lui même : la pertinence, la distance et la notoriété. Pour progresser, il faut d’abord une fiche d’établissement Google complète et à jour, avec la bonne catégorie, les horaires, la zone desservie et des photos. Les avis clients, réguliers et suivis de réponses, renforcent la notoriété. Votre nom, votre adresse et votre téléphone doivent être identiques partout. Côté site, des pages locales réellement utiles, une page contact complète, des données structurées LocalBusiness et un site rapide sur mobile complètent le dispositif. Les résultats se construisent sur plusieurs mois et se suivent dans les statistiques de la fiche et la Search Console.'),
        'sections': [
            ('Comment Google classe les résultats locaux', [
                ('p', 'Google indique trois critères principaux pour les résultats locaux :'),
                ('ul', [
                    '<strong>La pertinence</strong> : votre fiche et votre site correspondent ils à la recherche ?',
                    '<strong>La distance</strong> : à quelle distance êtes vous de la personne qui cherche, ou du lieu indiqué dans la recherche ?',
                    '<strong>La notoriété</strong> : votre entreprise est elle connue, citée, bien notée ?',
                ]),
                ('p', 'Vous ne pouvez pas changer la distance, mais vous pouvez travailler la pertinence et la notoriété. Les dix actions suivantes vont dans ce sens.'),
            ]),
            ('1. Compléter votre fiche d’établissement Google', [
                ('p', 'C’est la base. Choisissez la catégorie principale la plus précise possible, renseignez l’adresse ou la zone desservie, les horaires, y compris les jours fériés, le téléphone, le site, les services et une description claire. Ajoutez de vraies photos de vos locaux, de votre équipe et de vos réalisations.'),
            ]),
            ('2. Obtenir des avis et y répondre', [
                ('p', 'Demandez un avis à chaque client satisfait, avec un lien direct vers votre fiche. Répondez à tous les avis, positifs comme négatifs, de façon courtoise et précise. N’achetez jamais d’avis et n’en rédigez pas vous même : c’est interdit et sanctionné.'),
            ]),
            ('3. Garder un nom, une adresse et un téléphone identiques partout', [
                ('p', 'Votre nom, votre adresse et votre numéro doivent être écrits de la même façon sur votre site, votre fiche Google, les annuaires et les réseaux sociaux. Les incohérences sèment le doute chez Google comme chez vos clients.'),
            ]),
            ('4. Créer des pages locales vraiment utiles', [
                ('p', 'Une page par ville ou par zone où vous intervenez réellement peut vous aider à apparaître sur ces recherches. Mais chaque page doit apporter un contenu propre : chantiers réalisés sur place, délais d’intervention, particularités locales. Des pages identiques où seul le nom de la ville change n’apportent rien et peuvent pénaliser le site.'),
                ('encart', 'Exemple :', 'nos pages [agence web à Paris](agence-web-paris) et [agence web dans les Hauts-de-Seine](agence-web-hauts-de-seine) présentent chacune un contenu adapté à sa zone.'),
            ]),
            ('5. Soigner la page contact', [
                ('p', 'Votre page contact doit afficher l’adresse complète, une carte, les horaires, un téléphone cliquable sur mobile et les moyens d’accès. Si vous avez plusieurs établissements, créez une page par adresse.'),
            ]),
            ('6. Ajouter des données structurées LocalBusiness', [
                ('p', 'Les données structurées décrivent votre entreprise aux moteurs de recherche dans un langage standard : nom, adresse, téléphone, horaires, zone desservie, avis. Le type LocalBusiness, ou un sous type plus précis comme Restaurant ou Plumber, aide Google à comprendre votre activité.'),
            ]),
            ('7. S’inscrire dans les bons annuaires', [
                ('p', 'Inscrivez votre entreprise dans les annuaires reconnus et dans ceux de votre métier ou de votre ville, avec des informations identiques à votre fiche Google. Privilégiez la qualité : quelques annuaires sérieux valent mieux que des dizaines de sites douteux.'),
            ]),
            ('8. Publier des contenus ancrés dans votre ville', [
                ('img', 'statistiques-visibilite-locale.webp', 'Les statistiques montrent quelles actions font progresser votre visibilité', 'Ordinateur portable affichant un tableau de bord de statistiques de visites'),
                ('p', 'Présentez vos réalisations en précisant la ville, publiez des conseils liés à votre zone, parlez des événements locaux auxquels vous participez. Ces contenus renforcent la pertinence locale de votre site et donnent des raisons à d’autres sites locaux de parler de vous.'),
            ]),
            ('9. Avoir un site rapide et pensé pour le mobile', [
                ('p', 'La plupart des recherches locales se font sur téléphone, souvent au moment d’agir. Un site lent ou difficile à utiliser sur mobile fait fuir ces clients vers le concurrent suivant. Vérifiez la vitesse et l’affichage de vos pages principales.'),
            ]),
            ('10. Mesurer et ajuster', [
                ('p', 'Les statistiques de votre fiche Google indiquent combien de personnes ont vu votre fiche, appelé ou demandé un itinéraire. La Search Console montre les recherches qui mènent à votre site. Suivez ces chiffres chaque mois pour savoir ce qui fonctionne.'),
            ]),
            ('Les erreurs qui freinent votre visibilité locale', [
                ('ul', [
                    'Une fiche Google créée puis oubliée, avec des horaires faux ou un ancien numéro.',
                    'Une catégorie principale trop vague, comme « entreprise » au lieu de votre métier précis.',
                    'Des dizaines de pages villes identiques, où seul le nom change.',
                    'Des avis sans réponse, surtout les avis négatifs.',
                    'Une adresse écrite différemment sur le site, la fiche et les annuaires.',
                    'Un mot clé ajouté au nom de l’entreprise sur la fiche Google, contraire aux règles et risqué.',
                ]),
                ('p', 'Corriger ces points ne coûte presque rien et produit souvent des effets visibles plus vite que n’importe quelle autre action.'),
            ]),
            ('Votre plan d’action sur trois mois', [
                ('ol', [
                    '<strong>Premier mois</strong> : compléter et vérifier la fiche Google, harmoniser le nom, l’adresse et le téléphone partout, corriger la page contact.',
                    '<strong>Deuxième mois</strong> : mettre en place la demande d’avis après chaque prestation, répondre aux avis existants, ajouter les données structurées.',
                    '<strong>Troisième mois</strong> : publier deux ou trois contenus locaux, comme des réalisations situées dans votre ville, et s’inscrire dans les annuaires de qualité.',
                ]),
                ('p', 'Ensuite, entretenez la dynamique : de nouvelles photos sur la fiche, des avis réguliers, un contenu local par mois. Le référencement local récompense la constance plus que les coups d’éclat.'),
            ]),
            ('Être accompagné pour votre visibilité locale', [
                ('p', 'Chaque [site vitrine](creation-site-vitrine) créé par Aurea Media intègre les bases du référencement local : données structurées, page contact complète, pages par zone quand elles sont pertinentes et configuration de la Search Console. Nous accompagnons notamment les [artisans et entreprises du bâtiment](site-internet-artisan-btp) et les [restaurants et lieux de réception](site-internet-restaurant), pour qui la visibilité locale est décisive.'),
            ]),
        ],
        'faq': [
            ('Combien de temps faut il pour voir les effets du référencement local ?', 'Les premiers effets d’une fiche Google bien remplie peuvent être rapides, mais une visibilité solide se construit sur plusieurs mois, avec des avis réguliers et des contenus locaux.'),
            ('Faut il une adresse physique pour apparaître dans les résultats locaux ?', 'Non. Une entreprise qui se déplace chez ses clients peut indiquer une zone desservie et masquer son adresse sur sa fiche Google, à condition de respecter les règles de Google.'),
            ('Les avis négatifs nuisent ils au référencement local ?', 'Quelques avis négatifs sont normaux. Ce qui compte, c’est la note globale, le nombre d’avis, leur régularité et la qualité de vos réponses, qui rassurent les futurs clients.'),
        ],
        'appel': ('Vous voulez être trouvé dans votre ville ?', 'Un site pensé pour le référencement local, dès 1 000 € HT. Devis sous 24 h.', 'Demander mon devis', 'contact'),
        'sources': [
            ('Améliorer votre classement local sur Google, aide Google', 'https://support.google.com/business/answer/7091?hl=fr'),
            ('Fiche d’établissement Google', 'https://www.google.com/intl/fr_fr/business/'),
            ('Type LocalBusiness, schema.org', 'https://schema.org/LocalBusiness'),
        ],
    },
]

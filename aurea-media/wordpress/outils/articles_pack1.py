"""Pack 1 : articles programmés (un mardi par semaine). Ils ne sont pas livrés avec le thème, seulement en pack."""

PACK1 = [
    {
        'slug': 'prix-boutique-en-ligne', 'ordre': 1,
        'titre': 'Prix d’une boutique en ligne en 2026 : combien prévoir ?',
        'categorie': ('Prix', 'prix'),
        'mot_cle': 'prix d’une boutique en ligne',
        'titre_seo': 'Prix d’une boutique en ligne en 2026 : le budget complet',
        'description': 'Prix d’une boutique en ligne en 2026 : budget de création selon la solution, coûts récurrents, frais de paiement et dépenses cachées à prévoir avant de vendre.',
        'extrait': 'Création, abonnements, frais de paiement, photos, maintenance : tout ce qui compose le prix d’une boutique en ligne, pour construire un budget réaliste.',
        'image': ('prix-boutique-en-ligne-budget.webp', 'Tirelire rose posée sur une table à côté de quelques pièces'),
        'intro': 'Le prix d’une boutique en ligne ne se résume pas au devis de création. Abonnements, frais de paiement, photos des produits, maintenance : de nombreux postes s’ajoutent, et c’est souvent leur oubli qui fragilise un lancement. Voici comment construire un budget complet et réaliste, avant de vendre votre premier produit.',
        'bref': ('En bref : combien coûte une boutique en ligne en 2026 ?',
                 'Une boutique en ligne peut démarrer avec un simple abonnement à une plateforme hébergée, compter de l’ordre de 1 500 à 6 000 € pour une boutique sur mesure créée par un studio ou une petite agence, et dépasser 10 000 € pour un projet complexe relié à un logiciel de gestion. Le prix dépend du nombre de produits, des variantes, des moyens de paiement et de livraison, et des fonctionnalités. À la création s’ajoutent des coûts récurrents : hébergement ou abonnement, nom de domaine, frais prélevés sur chaque paiement, extensions éventuelles et maintenance. Il faut aussi prévoir les photos, la rédaction des fiches produits et le temps de gestion. Chez Aurea Media, une boutique WooCommerce sur mesure démarre à 1 650 € HT, sans commission sur vos ventes.'),
        'sections': [
            ('Le budget de création selon la solution', [
                ('p', 'Ces montants sont des ordres de grandeur constatés en France. Ils permettent de situer un devis, mais seul un cahier des charges précis fixe le prix réel.'),
                ('table', ['Solution', 'Budget indicatif de création', 'Pour qui'], [
                    ['Plateforme hébergée, créée soi même', 'Abonnement mensuel, peu ou pas de frais de départ', 'Tester un produit, petit catalogue'],
                    ['Freelance sur thème existant', 'De 1 000 à 3 000 € environ', 'Boutique simple, budget serré'],
                    ['Boutique sur mesure en studio', 'De 1 500 à 6 000 € environ', 'Commerçants, marques, artisans'],
                    ['Agence et projet complexe', '10 000 € et plus', 'Gros catalogue, connexions à un logiciel de gestion'],
                ], 'Ordres de grandeur constatés en France, hors coûts récurrents'),
                ('p', 'Le premier choix structurant est celui de la technologie : une plateforme hébergée par abonnement, ou une solution libre comme WooCommerce, installée sur votre propre hébergement. Notre comparatif [WooCommerce ou Shopify](article:woocommerce-ou-shopify) détaille les avantages de chacune.'),
            ]),
            ('Ce qui fait varier le prix de la création', [
                ('h3', 'Le nombre de produits et de variantes'),
                ('p', 'Intégrer vingt produits ou mille produits ne demande pas le même travail. Les variantes, comme les tailles, les couleurs ou les longueurs, multiplient aussi les fiches à configurer et à tester.'),
                ('h3', 'Les moyens de paiement et de livraison'),
                ('p', 'La carte bancaire et PayPal couvrent la plupart des besoins. Le retrait en boutique, la livraison en points relais, les tarifs par poids ou par zone, ou la vente à l’international ajoutent de la configuration.'),
                ('h3', 'Les fonctionnalités'),
                ('p', 'Codes promo, comptes clients, liste de souhaits, abonnements, ventes aux professionnels avec des prix dédiés, plusieurs langues ou devises : chaque fonctionnalité a un coût de mise en place et parfois une licence annuelle.'),
                ('h3', 'Les connexions à vos outils'),
                ('p', 'Relier la boutique à un logiciel de caisse, de comptabilité, de gestion des stocks ou à un logisticien représente souvent la part la plus technique d’un projet.'),
            ]),
            ('Les coûts récurrents à prévoir chaque année', [
                ('img', 'tableau-de-bord-ventes-en-ligne.webp', 'Les frais récurrents se suivent comme les ventes, mois après mois', 'Écran affichant un tableau de bord avec des indicateurs de performance des ventes'),
                ('ul', [
                    '<strong>L’hébergement ou l’abonnement</strong> : un hébergement adapté au e-commerce pour WooCommerce, ou l’abonnement mensuel de la plateforme.',
                    '<strong>Le nom de domaine</strong> : en général de l’ordre d’une dizaine à une vingtaine d’euros par an.',
                    '<strong>Les frais de paiement</strong> : chaque prestataire, comme Stripe ou PayPal, prélève une commission sur chaque transaction, selon sa grille tarifaire.',
                    '<strong>Les extensions ou applications</strong> : certaines fonctionnalités sont payantes chaque mois ou chaque année.',
                    '<strong>La maintenance</strong> : mises à jour, sauvegardes, sécurité, surveillance de la vitesse.',
                ]),
                ('encart', 'Bon à savoir :', 'les frais de paiement sont proportionnels à votre chiffre d’affaires. Intégrez les dans le calcul de votre marge, produit par produit, dès le départ.'),
            ]),
            ('Les dépenses qu’on oublie souvent', [
                ('p', 'Ce sont rarement les lignes du devis qui posent problème, mais ce qui n’y figure pas :'),
                ('ul', [
                    '<strong>Les photos des produits</strong> : de belles photos, homogènes, sous plusieurs angles, sont décisives pour vendre.',
                    '<strong>La rédaction des fiches produits</strong> : des descriptions précises et uniques, utiles au client et au référencement.',
                    '<strong>Les documents légaux</strong> : conditions générales de vente, mentions légales, politique de confidentialité, à faire relire par un professionnel du droit.',
                    '<strong>Les emballages et l’expédition</strong> : à intégrer dans vos prix ou vos frais de port.',
                    '<strong>Le marketing de lancement</strong> : une boutique ne vend pas seule, il faut l’alimenter en visiteurs.',
                    '<strong>Votre temps</strong> : préparation des commandes, service client, retours.',
                ]),
            ]),
            ('Un exemple de budget de lancement', [
                ('p', 'Pour une petite boutique sur mesure, voici les postes à chiffrer avant de vous lancer. Tous ne relèvent pas de l’agence : certains dépendent de vous ou d’autres prestataires.'),
                ('table', ['Poste', 'Qui s’en occupe', 'Fréquence'], [
                    ['Création de la boutique', 'Agence ou studio', 'Une fois'],
                    ['Photos des produits', 'Vous ou un photographe', 'Au lancement, puis à chaque nouveauté'],
                    ['Fiches produits', 'Vous ou l’agence', 'Au lancement, puis à chaque nouveauté'],
                    ['Hébergement et nom de domaine', 'À votre nom', 'Chaque année'],
                    ['Frais de paiement', 'Prestataire de paiement', 'À chaque vente'],
                    ['Maintenance', 'Vous ou un prestataire', 'Chaque mois'],
                ], 'Les postes d’un budget de lancement'),
            ]),
            ('Calculer en combien de commandes la boutique est amortie', [
                ('p', 'Plutôt que de regarder le prix seul, calculez combien de commandes il faut pour rentabiliser la création. La formule est simple : on part de la marge réelle d’une commande moyenne, après tous les frais.'),
                ('ol', [
                    'Prix de vente moyen d’une commande.',
                    'Moins le coût d’achat ou de fabrication des produits.',
                    'Moins les frais de paiement, l’emballage et la part de l’expédition que vous prenez en charge.',
                    'Le résultat est votre marge par commande. Divisez le coût de création par cette marge.',
                ]),
                ('p', 'Exemple fictif, pour illustrer le calcul : avec une marge réelle de 25 € par commande, une boutique qui a coûté 2 500 € est amortie au bout de 100 commandes. Ce raisonnement aide aussi à décider s’il vaut mieux investir dans plus de fonctionnalités ou dans de meilleures photos.'),
            ]),
            ('Combien coûte une boutique chez Aurea Media ?', [
                ('p', 'Chez Aurea Media, une [boutique WooCommerce sur mesure](creation-site-ecommerce) démarre à 1 650 € HT. Le prix comprend le design sur mesure, l’adaptation au mobile, le paiement par Stripe et PayPal, des fiches produits optimisées pour le référencement et une formation pour gérer vos produits et vos commandes. Il n’y a aucune commission sur vos ventes et la boutique est à votre nom. Le détail est sur notre page [tarifs](tarifs).'),
                ('p', 'Vous hésitez encore entre vendre en ligne ou présenter votre activité ? Lisez notre article [site vitrine ou site e-commerce](article:site-vitrine-ou-site-e-commerce), et découvrez un exemple concret avec la boutique [Perle Hair](realisation:perle-hair).'),
            ]),
        ],
        'faq': [
            ('Peut on lancer une petite boutique avec un budget limité ?', 'Oui. Il vaut mieux commencer avec un catalogue réduit et des fiches soignées que mettre en ligne des centaines de produits mal présentés. La boutique pourra s’agrandir ensuite.'),
            ('Les frais de paiement sont ils négociables ?', 'Les prestataires de paiement publient des grilles standard. Pour des volumes importants, certains proposent des conditions particulières : renseignez vous directement auprès d’eux.'),
            ('Combien de temps faut il pour créer une boutique en ligne ?', 'Une boutique demande en général plus de temps qu’un site vitrine, car il faut intégrer les produits, configurer le paiement et la livraison, puis tester chaque étape d’achat. Le calendrier est fixé dans le devis, selon la taille du catalogue.'),
        ],
        'appel': ('Vous voulez un budget précis pour votre boutique ?', 'Devis gratuit et détaillé, ligne par ligne, sous 24 h.', 'Demander mon devis', 'contact'),
        'sources': [
            ('Tarifs de Stripe, site officiel', 'https://stripe.com/fr/pricing'),
            ('Faire du commerce en ligne : règles à respecter, Service Public', 'https://entreprendre.service-public.gouv.fr/vosdroits/F23455'),
        ],
    },
    {
        'slug': 'choisir-agence-web', 'ordre': 2,
        'titre': 'Comment choisir son agence web : 10 critères pour ne pas se tromper',
        'categorie': ('Création de site web', 'creation-site-web'),
        'mot_cle': 'choisir son agence web',
        'titre_seo': 'Choisir son agence web : 10 critères pour ne pas se tromper',
        'description': 'Choisir son agence web : réalisations, devis, propriété du site, référencement, maintenance. Les 10 critères et les questions à poser avant de signer.',
        'extrait': 'Réalisations, devis, propriété du site, référencement, suivi : les dix critères pour choisir la bonne agence web, et les signaux qui doivent vous alerter.',
        'image': ('choisir-agence-web-reunion.webp', 'Réunion de travail autour d’un mur couvert de post-it colorés'),
        'intro': 'Choisir son agence web, c’est choisir un partenaire pour plusieurs années : celui qui va concevoir votre site, mais aussi vous accompagner quand il faudra le faire évoluer. Les offres se ressemblent souvent sur le papier. Voici dix critères concrets pour les départager, et les questions à poser avant de signer.',
        'bref': ('En bref : comment bien choisir son agence web ?',
                 'Pour bien choisir son agence web, regardez d’abord ses réalisations en ligne et vérifiez qu’elles sont rapides et lisibles sur mobile. Lisez des avis vérifiables et demandez qui sera votre interlocuteur. Exigez un devis détaillé poste par poste, avec un calendrier, et vérifiez que le nom de domaine, l’hébergement et les accès seront à votre nom. Demandez ce que contient précisément le référencement inclus, quelle technologie est utilisée et si vous pourrez changer de prestataire. Enfin, renseignez vous sur la formation, la maintenance et le suivi après la mise en ligne. Méfiez vous des contrats de location avec engagement de plusieurs années et des promesses de première position sur Google.'),
        'sections': [
            ('1. Des réalisations comparables à votre projet', [
                ('p', 'Ne vous contentez pas de captures d’écran : visitez les sites en ligne, sur ordinateur et sur téléphone. Sont ils rapides ? Lisibles ? Faciles à parcourir ? Une agence qui a déjà travaillé dans votre secteur comprendra plus vite vos enjeux. Nos [réalisations](realisations) présentent chacune une étude de cas détaillée.'),
            ]),
            ('2. Des avis vérifiables', [
                ('p', 'Privilégiez les avis publiés sur des plateformes indépendantes, comme la fiche Google de l’agence, plutôt que des témoignages anonymes sur son propre site. Si possible, demandez à échanger avec un ancien client.'),
            ]),
            ('3. Un interlocuteur identifié', [
                ('p', 'Qui suivra votre projet ? Qui répondra à vos questions dans un an ? Dans une petite structure, vous parlez souvent directement à la personne qui conçoit le site. Dans une grande agence, vérifiez que vous aurez un chef de projet attitré.'),
            ]),
            ('4. Une méthode et un calendrier clairs', [
                ('img', 'equipe-agence-web-projet.webp', 'Une bonne agence explique sa méthode avant de parler technique', 'Équipe souriante réunie autour d’un ordinateur portable pour un projet'),
                ('p', 'Une agence sérieuse présente ses étapes : cadrage, arborescence, maquettes, développement, contenus, tests, mise en ligne. Elle indique ce qu’elle attend de vous et à quel moment, avec un calendrier réaliste.'),
            ]),
            ('5. Un devis détaillé', [
                ('p', 'Un devis d’une seule ligne ne permet pas de comparer. Demandez le détail de chaque poste, ce qui est inclus et ce qui ne l’est pas, le nombre de pages, les allers retours prévus sur les maquettes et les modalités de paiement. Notre article sur le [prix d’un site vitrine](article:prix-site-vitrine) explique comment lire un devis.'),
            ]),
            ('6. La propriété de votre site', [
                ('p', 'C’est le critère le plus important, et le plus souvent négligé. Le nom de domaine, l’hébergement, les accès administrateur et les contenus doivent être à votre nom. Sans cela, changer de prestataire peut devenir très compliqué.'),
                ('encart', 'Signal d’alerte :', 'méfiez vous des contrats de location de site avec un engagement de plusieurs années. Le site ne vous appartient pas, et la résiliation anticipée peut coûter très cher. Lisez attentivement chaque clause avant de signer.'),
            ]),
            ('7. Un référencement concret', [
                ('p', '« Référencement inclus » ne veut rien dire sans détail. Demandez ce qui est fait : balises title et descriptions, structure des titres, vitesse, données structurées, plan du site, configuration de la Search Console. Et fuyez toute promesse de première position garantie : personne ne contrôle le classement de Google.'),
            ]),
            ('8. Une technologie standard', [
                ('p', 'Un site construit avec une technologie répandue, comme WordPress, peut être repris par n’importe quel prestataire compétent. Un outil propriétaire vous rend dépendant de l’agence qui l’a créé.'),
            ]),
            ('9. Une formation à l’administration', [
                ('p', 'Vous devez pouvoir modifier vos textes, vos photos et publier des articles sans appeler votre agence. Vérifiez qu’une formation est prévue, et qu’un guide ou une vidéo vous sera remis.'),
            ]),
            ('10. Le suivi après la mise en ligne', [
                ('p', 'Un site a besoin de mises à jour, de sauvegardes et d’une surveillance de la sécurité. Demandez ce qui est proposé après la livraison, à quel prix, et si la maintenance est optionnelle ou imposée.'),
            ]),
            ('Agence, studio ou freelance : quelles différences ?', [
                ('table', ['Profil', 'Points forts', 'Points de vigilance'], [
                    ['Freelance', 'Tarifs souvent bas, interlocuteur unique', 'Disponibilité, continuité en cas d’absence'],
                    ['Studio ou petite agence', 'Sur mesure, interlocuteur direct, réactivité', 'Capacité limitée sur les très gros projets'],
                    ['Grande agence', 'Équipes pluridisciplinaires, gros projets', 'Budget élevé, interlocuteurs multiples'],
                ], 'Comparer les types de prestataires'),
                ('p', 'Il n’y a pas de bon choix universel : tout dépend de la taille de votre projet, de votre budget et du niveau d’accompagnement que vous attendez.'),
            ]),
            ('Comment comparer deux devis', [
                ('p', 'Deux devis au même prix peuvent cacher des prestations très différentes. Comparez les ligne par ligne, sur ces points :'),
                ('table', ['Point à comparer', 'Ce qu’il faut vérifier'], [
                    ['Nombre de pages', 'Le nombre exact, et le prix d’une page supplémentaire'],
                    ['Design', 'Sur mesure ou thème existant, nombre d’allers retours'],
                    ['Contenus', 'Qui rédige les textes, qui fournit les photos'],
                    ['Référencement', 'La liste précise de ce qui est fait'],
                    ['Propriété', 'Domaine, hébergement et accès à votre nom'],
                    ['Après la mise en ligne', 'Formation, garantie, maintenance et son prix'],
                ], 'Grille de comparaison de devis'),
            ]),
            ('Les signaux qui doivent vous alerter', [
                ('ul', [
                    'Une promesse de première position sur Google, ou un nombre garanti de visiteurs.',
                    'Un prix très bas sans aucun détail, ou des options découvertes en cours de projet.',
                    'Un engagement de plusieurs années pour un simple site vitrine.',
                    'Aucun site réalisé visible en ligne.',
                    'Un refus de vous donner les accès administrateur de votre propre site.',
                ]),
            ]),
            ('Ce qu’il faut préparer avant de contacter une agence', [
                ('p', 'Quelques éléments préparés à l’avance vous feront gagner du temps et rendront les devis plus précis et plus faciles à comparer :'),
                ('ul', [
                    'Une description de votre activité, de vos clients et de vos services prioritaires.',
                    'Deux ou trois sites que vous aimez, et ce qui vous plaît dans chacun.',
                    'Votre budget approximatif et la date de mise en ligne souhaitée.',
                    'Ce que vous pouvez fournir : textes, photos, logo, avis clients.',
                    'Les accès à votre site et à votre nom de domaine actuels, si vous en avez.',
                ]),
            ]),
            ('Les questions à poser avant de signer', [
                ('ol', [
                    'Puis je voir trois sites que vous avez réalisés, en ligne ?',
                    'Qui sera mon interlocuteur pendant et après le projet ?',
                    'Le nom de domaine, l’hébergement et les accès seront ils à mon nom ?',
                    'Que contient exactement le référencement inclus ?',
                    'Combien d’allers retours sont prévus sur les maquettes ?',
                    'Que se passe t il si je veux changer de prestataire dans deux ans ?',
                ]),
                ('p', 'Chez Aurea Media, nous répondons à toutes ces questions dans le devis : [création de site vitrine](creation-site-vitrine) sur mesure, propriété totale, référencement technique inclus et aucun abonnement imposé.'),
            ]),
        ],
        'faq': [
            ('Faut il choisir une agence près de chez soi ?', 'Ce n’est pas indispensable : la plupart des projets se gèrent très bien à distance, par visioconférence. La proximité peut toutefois faciliter les échanges et la connaissance de votre marché local.'),
            ('Combien de devis faut il demander ?', 'Deux ou trois devis suffisent généralement pour comparer. Au delà, il devient difficile de les analyser sérieusement. Comparez les à périmètre identique.'),
            ('Une agence peut elle garantir la première place sur Google ?', 'Non. Personne ne contrôle le classement de Google. Une agence sérieuse explique ce qu’elle met en place pour améliorer votre visibilité, sans promettre de position.'),
        ],
        'appel': ('Vous cherchez une agence web pour votre projet ?', 'Échange sans engagement et devis détaillé sous 24 h.', 'Parler de mon projet', 'contact'),
        'sources': [
            ('Avez vous besoin d’un expert SEO ? Google Search Central', 'https://developers.google.com/search/docs/fundamentals/do-i-need-seo?hl=fr'),
        ],
    },
    {
        'slug': 'site-internet-artisan', 'ordre': 3,
        'titre': 'Site internet pour artisan : par où commencer ?',
        'categorie': ('Création de site web', 'creation-site-web'),
        'mot_cle': 'site internet pour artisan',
        'titre_seo': 'Site internet pour artisan : par où commencer en 2026 ?',
        'description': 'Site internet pour artisan : zone, fiche Google, pages métiers, photos de chantiers, formulaire de devis. Les étapes pour créer un site qui apporte des clients.',
        'extrait': 'Zone d’intervention, fiche Google, pages par métier, photos de chantiers, demande de devis : les étapes pour créer un site d’artisan qui apporte vraiment des clients.',
        'image': ('site-internet-artisan-electricien.webp', 'Électricien casqué qui intervient sur une installation électrique'),
        'intro': 'Vous êtes artisan et vos clients vous trouvent surtout par le bouche à oreille ? C’est une force, mais ces mêmes clients vérifient presque toujours votre sérieux sur Google avant d’appeler. Un site internet pour artisan bien construit confirme ce bouche à oreille et vous apporte de nouveaux chantiers. Voici par où commencer, étape par étape.',
        'bref': ('En bref : comment créer un site internet pour artisan ?',
                 'Un site internet pour artisan commence par trois décisions : vos métiers principaux, votre zone d’intervention et le type de chantiers que vous voulez attirer. Il faut ensuite réserver un nom de domaine, créer une adresse e-mail professionnelle et compléter votre fiche d’établissement Google. Le site lui même contient une page d’accueil claire, une page par métier, des réalisations avec de vraies photos de chantiers, vos avis clients, votre zone d’intervention et un formulaire de devis simple. Les informations qui rassurent, comme vos assurances, vos qualifications ou vos labels réels, doivent être visibles. Un site d’artisan sur mesure démarre à 1 000 € HT chez Aurea Media.'),
        'sections': [
            ('Pourquoi un site quand on a déjà du bouche à oreille ?', [
                ('p', 'Le bouche à oreille reste le premier canal des artisans. Mais quand un voisin vous recommande, le futur client tape votre nom sur Google. S’il ne trouve rien, ou une fiche incomplète, il hésite. Un site sert d’abord à transformer une recommandation en appel.'),
                ('p', 'Il permet aussi d’être trouvé par ceux qui ne vous connaissent pas encore, quand ils cherchent votre métier dans leur ville : « électricien Montpellier », « rénovation salle de bain Nîmes ».'),
            ]),
            ('Étape 1 : définir vos métiers et votre zone', [
                ('p', 'Listez les prestations qui comptent le plus pour vous, celles que vous voulez développer, et les communes où vous intervenez réellement. Ces choix détermineront les pages du site et les recherches sur lesquelles vous apparaîtrez.'),
            ]),
            ('Étape 2 : nom de domaine et adresse e-mail professionnelle', [
                ('p', 'Réservez un nom de domaine à votre nom, idéalement le nom de votre entreprise, et créez une adresse e-mail associée, comme contact@votre-entreprise.fr. C’est simple, peu coûteux, et beaucoup plus rassurant qu’une adresse personnelle.'),
            ]),
            ('Étape 3 : votre fiche Google', [
                ('p', 'Avant même le site, complétez votre fiche d’établissement Google : métier, zone desservie, horaires, téléphone, photos de chantiers. C’est souvent elle qui apparaît en premier quand on cherche un artisan près de chez soi. Notre guide sur le [référencement local](article:referencement-local-google) détaille les actions qui comptent.'),
            ]),
            ('Étape 4 : les pages de votre site', [
                ('img', 'artisan-plans-chantier.webp', 'Un bon site d’artisan se prépare comme un chantier : avec un plan', 'Artisan qui dessine des plans sur une table de travail'),
                ('ul', [
                    '<strong>L’accueil</strong> : votre métier, votre zone, vos points forts et un bouton de devis.',
                    '<strong>Une page par métier</strong> : maçonnerie, isolation, plomberie… chacune avec son déroulement de chantier.',
                    '<strong>Les réalisations</strong> : vos chantiers, avec photos avant et après et la commune.',
                    '<strong>Votre entreprise</strong> : votre histoire, votre équipe, vos qualifications.',
                    '<strong>La zone d’intervention</strong> : les communes desservies, avec une carte.',
                    '<strong>La demande de devis</strong> : un formulaire court et votre téléphone.',
                ]),
                ('p', 'Le site de l’entreprise [Novexia Construction](realisation:novexia-construction) suit exactement cette logique, avec une page par métier et des pages par ville.'),
            ]),
            ('Étape 5 : de vraies photos de chantiers', [
                ('p', 'Rien ne vaut vos propres photos : un chantier propre, un avant et après, votre équipe au travail. Prenez l’habitude de photographier chaque chantier, en demandant l’accord de vos clients pour les publier. Les photos de banques d’images ne prouvent pas votre savoir-faire.'),
            ]),
            ('Étape 6 : un formulaire de devis qui donne envie', [
                ('p', 'Demandez seulement l’essentiel : le type de travaux, la commune, le délai souhaité, un téléphone, et la possibilité de joindre une photo. Un formulaire trop long fait fuir ; un formulaire trop court vous oblige à rappeler pour tout redemander.'),
            ]),
            ('Étape 7 : afficher ce qui rassure', [
                ('p', 'Vos clients confient leur maison : ils ont besoin d’être rassurés. Mettez en avant vos avis clients, votre ancienneté, vos qualifications et labels réels, ainsi que vos assurances professionnelles.'),
                ('encart', 'À savoir :', 'pour les professionnels concernés, la mention de l’assurance professionnelle doit figurer sur les devis et les factures. L’afficher aussi sur votre site renforce la confiance. N’affichez jamais un label que vous ne détenez pas.'),
            ]),
            ('Les mots que vos clients utilisent vraiment', [
                ('p', 'Écrivez avec les mots de vos clients, pas avec le jargon du métier. Un particulier cherche « refaire sa salle de bain » ou « isoler ses combles », rarement le nom technique d’un procédé. Associez votre métier aux communes où vous intervenez, sans répéter mécaniquement les mêmes expressions : Google comprend très bien les variantes.'),
                ('p', 'Chaque page de métier peut répondre aux questions que l’on vous pose au téléphone : combien de temps dure le chantier, faut il quitter les lieux, quelles aides existent, comment se déroule le devis. Ces réponses rassurent et aident votre référencement.'),
            ]),
            ('Faire vivre son site après la mise en ligne', [
                ('ul', [
                    'Ajouter un chantier terminé chaque mois, avec ses photos et la commune.',
                    'Demander un avis Google à chaque client satisfait.',
                    'Mettre à jour vos horaires, vos congés et vos coordonnées.',
                    'Vérifier une fois par mois que le formulaire de devis fonctionne.',
                    'Publier de temps en temps un conseil utile, lié à vos métiers.',
                ]),
            ]),
            ('Ce qu’il faut rassembler avant de lancer votre site', [
                ('ul', [
                    'Votre logo, ou l’envie d’en créer un.',
                    'Une vingtaine de photos de chantiers récents, avec l’accord de vos clients.',
                    'La liste de vos métiers et des communes où vous intervenez.',
                    'Vos qualifications, labels et assurances, avec leurs justificatifs.',
                    'Quelques avis clients, ou le lien vers votre fiche Google.',
                ]),
                ('p', 'Avec ces éléments, la création avance vite et le site reflète vraiment votre entreprise.'),
            ]),
            ('Les erreurs fréquentes des sites d’artisans', [
                ('ul', [
                    'Un site d’une seule page qui liste tous les métiers sans détail.',
                    'Aucune photo de chantier, ou seulement des photos d’illustration.',
                    'Un numéro de téléphone difficile à trouver sur mobile.',
                    'Une zone d’intervention floue, qui attire des demandes trop lointaines.',
                    'Un site jamais mis à jour, avec des réalisations datées.',
                ]),
            ]),
            ('Combien coûte un site d’artisan ?', [
                ('p', 'Chez Aurea Media, un site sur mesure pour artisan démarre à 1 000 € HT, avec le design, l’adaptation au mobile, le référencement technique, le formulaire de devis et la formation. Découvrez notre approche pour les [artisans et entreprises du bâtiment](site-internet-artisan-btp), et d’autres exemples avec [Normande d’Isolation](realisation:normande-disolation) et [TCM Grand Est](realisation:tcm-grand-est).'),
            ]),
        ],
        'faq': [
            ('Un artisan a t il vraiment besoin d’un site s’il a une fiche Google ?', 'La fiche Google est indispensable, mais elle reste limitée. Le site permet de présenter chaque métier en détail, de montrer vos réalisations et de vous positionner sur davantage de recherches. Les deux se complètent.'),
            ('Combien de temps faut il pour créer un site d’artisan ?', 'En moyenne quatre semaines environ pour un site vitrine sur mesure. Le plus long est souvent de rassembler les photos de chantiers et les informations sur l’entreprise.'),
            ('Puis je mettre à jour mes réalisations moi même ?', 'Oui. Avec une formation à l’administration, vous pouvez ajouter un chantier et ses photos en quelques minutes, depuis votre ordinateur ou votre téléphone.'),
        ],
        'appel': ('Vous êtes artisan et voulez plus de demandes de devis ?', 'Site sur mesure dès 1 000 € HT. Devis gratuit sous 24 h.', 'Demander mon devis', 'contact'),
        'sources': [
            ('Mentions obligatoires sur le site internet d’une entreprise, Service Public', 'https://entreprendre.service-public.gouv.fr/vosdroits/F37351'),
            ('Fiche d’établissement Google', 'https://www.google.com/intl/fr_fr/business/'),
        ],
    },
    {
        'slug': 'fiche-google-business-profile', 'ordre': 4,
        'titre': 'Fiche Google Business Profile : le guide complet pour votre entreprise',
        'categorie': ('Référencement', 'referencement'),
        'mot_cle': 'fiche google business profile',
        'titre_seo': 'Fiche Google Business Profile : le guide complet 2026',
        'description': 'Fiche Google Business Profile : créer et valider sa fiche, remplir chaque champ, photos, avis, publications et statistiques. Le guide pour être visible sur Maps.',
        'extrait': 'Création, validation, catégories, photos, avis, publications, statistiques : le guide pas à pas pour une fiche Google qui attire des clients.',
        'image': ('fiche-google-business-profile-smartphone.webp', 'Main tenant un smartphone pour consulter une fiche d’entreprise'),
        'intro': 'La fiche Google Business Profile est souvent la première image de votre entreprise : elle apparaît dans Google Maps et dans les résultats de recherche, avec vos horaires, vos avis et votre téléphone. Gratuite, elle reste pourtant négligée par beaucoup d’entreprises. Voici comment créer, compléter et faire vivre une fiche qui attire des clients.',
        'bref': ('En bref : comment optimiser sa fiche Google Business Profile ?',
                 'La fiche Google Business Profile est gratuite. Pour l’optimiser, il faut d’abord la créer ou la revendiquer, puis la valider selon la méthode proposée par Google. Renseignez ensuite chaque champ avec précision : le nom réel de l’entreprise, sans mot clé ajouté, la catégorie principale la plus précise, l’adresse ou la zone desservie, les horaires et les horaires exceptionnels, le téléphone, le site, les services et une description claire. Ajoutez régulièrement de vraies photos, publiez des actualités, demandez des avis à vos clients et répondez à chacun. Les statistiques de la fiche indiquent combien de personnes vous ont trouvé, appelé ou demandé un itinéraire.'),
        'sections': [
            ('À quoi sert une fiche Google Business Profile ?', [
                ('p', 'Elle permet à votre entreprise d’apparaître dans Google Maps et dans le bloc des résultats locaux, quand quelqu’un cherche votre métier près de lui. En un coup d’œil, le client voit votre note, vos horaires, votre adresse, vos photos, et peut vous appeler ou obtenir un itinéraire.'),
                ('p', 'Pour une entreprise locale, c’est souvent la source de contacts la plus directe, avant même le site internet. Les deux fonctionnent ensemble : la fiche renvoie vers le site, qui convainc.'),
            ]),
            ('Créer et valider sa fiche', [
                ('ol', [
                    'Rendez vous sur la page Google Business Profile et connectez vous avec un compte Google professionnel.',
                    'Cherchez votre entreprise : si une fiche existe déjà, revendiquez la plutôt que d’en créer une nouvelle.',
                    'Renseignez le nom, la catégorie et l’adresse ou la zone desservie.',
                    'Validez la fiche selon la méthode proposée par Google, qui varie selon les cas.',
                ]),
                ('encart', 'Important :', 'une seule fiche par établissement. Les doublons sèment la confusion et peuvent entraîner la suspension de vos fiches.'),
            ]),
            ('Remplir chaque champ avec soin', [
                ('h3', 'Le nom'),
                ('p', 'Utilisez le nom réel de votre entreprise, tel qu’il apparaît sur votre devanture ou vos documents. Ajouter un mot clé ou une ville au nom est contraire aux consignes de Google et peut entraîner une suspension.'),
                ('h3', 'Les catégories'),
                ('p', 'La catégorie principale est le réglage le plus important : choisissez la plus précise, par exemple « Électricien » plutôt que « Entreprise ». Ajoutez ensuite quelques catégories secondaires pertinentes.'),
                ('h3', 'L’adresse ou la zone desservie'),
                ('p', 'Si vous recevez des clients, indiquez votre adresse. Si vous vous déplacez chez eux, vous pouvez masquer l’adresse et indiquer la zone desservie.'),
                ('h3', 'Les horaires'),
                ('p', 'Des horaires exacts, y compris les horaires exceptionnels des jours fériés et des vacances, évitent les déceptions et les avis négatifs.'),
                ('h3', 'La description et les services'),
                ('p', 'Décrivez votre activité en quelques phrases claires : ce que vous faites, pour qui, où, et ce qui vous distingue. La description est limitée à 750 caractères. Listez aussi vos services ou vos produits principaux.'),
            ]),
            ('Des photos qui donnent envie', [
                ('img', 'carte-recherche-locale.webp', 'La fiche Google place votre entreprise sur la carte de vos clients', 'Carte routière détaillée avec des villes et des routes'),
                ('p', 'Ajoutez votre logo, une photo de couverture, des photos de vos locaux, de votre équipe et de vos réalisations. Préférez de vraies photos, récentes et lumineuses, aux images génériques. Ajoutez en de nouvelles régulièrement : une fiche active inspire confiance.'),
            ]),
            ('Publier des actualités', [
                ('p', 'Les publications permettent de partager une nouveauté, une offre, un événement ou une réalisation récente. Elles montrent que votre entreprise est active et donnent une raison de plus de vous contacter. Une photo, deux ou trois phrases et un bouton, comme « Appeler » ou « En savoir plus », suffisent.'),
            ]),
            ('Obtenir des avis et y répondre', [
                ('p', 'Les avis influencent à la fois la décision des clients et votre visibilité locale. Demandez un avis à chaque client satisfait, avec le lien direct que Google vous fournit. Répondez à tous les avis : remerciez pour les positifs, et répondez aux négatifs avec calme et des faits.'),
                ('p', 'N’achetez jamais d’avis, n’en rédigez pas vous même et n’offrez pas de contrepartie en échange : c’est contraire aux règles et risqué pour votre fiche.'),
            ]),
            ('Relier la fiche à votre site', [
                ('p', 'Indiquez l’adresse de votre site, et vérifiez que votre nom, votre adresse et votre téléphone sont identiques sur la fiche et sur le site. Pour mesurer les visites qui viennent de la fiche, vous pouvez ajouter des paramètres de suivi à l’adresse du site, qui apparaîtront dans vos statistiques.'),
                ('p', 'Côté site, une page contact complète et des données structurées LocalBusiness renforcent la cohérence entre les deux. C’est ce que nous intégrons à chaque [création de site vitrine](creation-site-vitrine).'),
            ]),
            ('Suivre les statistiques', [
                ('p', 'La fiche indique combien de personnes l’ont vue, sur quelles recherches, et combien ont appelé, demandé un itinéraire ou visité votre site. Consultez ces chiffres chaque mois : ils montrent l’effet de vos nouvelles photos, de vos publications et de vos avis.'),
            ]),
            ('Une routine mensuelle de quinze minutes', [
                ('p', 'Une fiche efficace n’exige pas beaucoup de temps, mais de la régularité. Bloquez quinze minutes par mois :'),
                ('ol', [
                    'Vérifier les horaires, notamment avant les jours fériés et les vacances.',
                    'Ajouter deux ou trois photos récentes de vos locaux ou de vos réalisations.',
                    'Publier une actualité : nouveauté, conseil, chantier terminé, événement.',
                    'Répondre aux avis reçus dans le mois.',
                    'Regarder les statistiques et noter l’évolution des appels et des itinéraires.',
                ]),
                ('p', 'Cette routine suffit souvent à distinguer votre fiche de celles de concurrents qui ne l’ont pas mise à jour depuis des mois.'),
            ]),
            ('Les erreurs qui peuvent suspendre une fiche', [
                ('ul', [
                    'Un nom d’entreprise enrichi de mots clés ou d’une ville.',
                    'Une adresse fictive ou une boîte postale pour apparaître dans une ville.',
                    'Plusieurs fiches pour un même établissement.',
                    'Des avis achetés ou rédigés par l’entreprise elle même.',
                    'Des informations qui ne correspondent pas à la réalité.',
                ]),
                ('p', 'Pour aller plus loin, lisez notre guide sur le [référencement local](article:referencement-local-google), et découvrez nos pages [agence web à Paris](agence-web-paris) et [restaurants et lieux de réception](site-internet-restaurant).'),
            ]),
        ],
        'faq': [
            ('La fiche Google Business Profile est elle payante ?', 'Non, la création et la gestion de la fiche sont gratuites. Méfiez vous des démarchages qui proposent de la créer ou de la « certifier » contre paiement.'),
            ('Peut on avoir une fiche sans adresse physique ?', 'Oui. Une entreprise qui se déplace chez ses clients peut masquer son adresse et indiquer une zone desservie, à condition de respecter les consignes de Google.'),
            ('Combien de temps pour voir des résultats ?', 'Une fiche complète peut gagner en visibilité assez rapidement, mais la progression se construit sur plusieurs mois, avec des avis réguliers, des photos et des publications.'),
            ('Que faire si des informations de ma fiche ont été modifiées sans moi ?', 'Les internautes peuvent suggérer des modifications, que Google peut appliquer. Consultez régulièrement votre fiche et les notifications de votre compte, corrigez toute information inexacte et gardez des données identiques à celles de votre site internet.'),
        ],
        'appel': ('Vous voulez être mieux trouvé près de chez vous ?', 'Un site relié à votre fiche Google, dès 1 000 € HT. Devis sous 24 h.', 'Demander mon devis', 'contact'),
        'sources': [
            ('Consignes de représentation de votre établissement sur Google', 'https://support.google.com/business/answer/3038177?hl=fr'),
            ('Améliorer votre classement local sur Google, aide Google', 'https://support.google.com/business/answer/7091?hl=fr'),
            ('Fiche d’établissement Google', 'https://www.google.com/intl/fr_fr/business/'),
        ],
    },
]

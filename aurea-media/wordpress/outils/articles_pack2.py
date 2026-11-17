"""Pack 2 : articles approfondis programmés en décembre 2026 (un mardi par semaine).

Chaque article vise 2 000 mots ou plus, avec un bloc « En bref » citable, des tableaux, des sources officielles
et des liens vers les pages piliers. Aucun chiffre client n'est inventé : les seules données propres à Aurea Media
viennent de sa Search Console (refonte de son propre site) ou de ses tarifs publics.
"""

PACK2 = [
    # ------------------------------------------------------------------ 1. Collectivités
    {
        'slug': 'site-internet-mairie-obligations', 'ordre': 1,
        'titre': 'Site internet de mairie : obligations RGAA et contenus indispensables',
        'categorie': ('Création de site web', 'creation-site-web'),
        'mot_cle': 'site internet de mairie',
        'titre_seo': 'Site internet de mairie : obligations RGAA et contenus clés',
        'description': 'Site internet de mairie : obligations d’accessibilité RGAA, sanctions de l’Arcom, contenus indispensables, publication des actes et règles d’achat public en 2026.',
        'extrait': 'Accessibilité RGAA, déclaration, schéma pluriannuel, publication des actes, achat public : le guide complet pour créer ou refondre le site internet de votre commune.',
        'image': ('site-internet-mairie-hemicycle.webp', 'Hémicycle d’une assemblée délibérante vu depuis les tribunes'),
        'intro': 'Le site internet de mairie est devenu le premier guichet de la commune : on y cherche un horaire, une démarche, un compte rendu de conseil ou une alerte travaux. C’est aussi un service public numérique, soumis à des obligations précises d’accessibilité et de publication. Ce guide rassemble ce que la loi impose, ce que les habitants attendent et la façon d’acheter le projet simplement en 2026.',
        'bref': ('En bref : que doit respecter le site internet d’une mairie ?',
                 'Le site internet d’une mairie doit être accessible à tous, y compris aux personnes en situation de handicap, selon le référentiel général d’amélioration de l’accessibilité (RGAA). La commune doit afficher sur sa page d’accueil une mention du niveau de conformité, publier une déclaration d’accessibilité établie à partir d’un audit et adopter un schéma pluriannuel sur trois ans. Depuis l’ordonnance du 6 septembre 2023, l’Arcom contrôle ces obligations et peut, après mise en demeure, prononcer une sanction allant jusqu’à 50 000 €, et jusqu’à 25 000 € pour l’absence de déclaration ou de schéma. Le site doit aussi respecter le RGPD et présenter les actes publiés par voie électronique de façon lisible. Côté achat, un marché de services de moins de 60 000 € HT peut être conclu sans publicité ni mise en concurrence préalables depuis le 1er avril 2026.'),
        'sections': [
            ('Pourquoi le site de la commune est un service public à part entière', [
                ('p', 'Pour beaucoup d’habitants, le site de la mairie a remplacé l’appel à l’accueil. Ils veulent savoir si la mairie est ouverte samedi matin, quels papiers apporter pour une carte d’identité, quand passe la collecte des encombrants ou ce qu’a voté le dernier conseil municipal. Chaque information introuvable se transforme en appel, en déplacement ou en frustration.'),
                ('p', 'Un bon site de commune soulage donc directement les services : moins d’appels répétitifs, des dossiers mieux préparés, une information de crise diffusée en quelques minutes. Il porte aussi l’image de la ville auprès des futurs habitants, des entreprises qui cherchent à s’implanter et des associations.'),
                ('encart', 'À retenir :', 'un site de mairie se juge d’abord à la vitesse à laquelle un habitant trouve une réponse, sur son téléphone, sans connaître l’organisation interne des services.'),
            ]),
            ('Les obligations légales d’accessibilité', [
                ('p', 'L’article 47 de la loi du 11 février 2005 pour l’égalité des droits et des chances impose aux collectivités territoriales, quelle que soit leur taille, de rendre leurs services de communication en ligne accessibles. Le référentiel technique est le RGAA, dans sa version 4.1, qui compte 106 critères répartis en 13 thématiques : images, couleurs, tableaux, liens, structure, formulaires, navigation, consultation, entre autres.'),
                ('h3', 'Les quatre obligations à afficher'),
                ('table', ['Obligation', 'Où', 'Contenu attendu'], [
                    ['Mention de conformité', 'Page d’accueil', 'Accessibilité : totalement, partiellement ou non conforme'],
                    ['Déclaration d’accessibilité', 'Page dédiée, liée depuis le pied de page', 'Résultats de l’audit, contenus non accessibles, contact, voies de recours'],
                    ['Schéma pluriannuel', 'Page publique', 'Politique d’accessibilité sur trois ans et plans d’action annuels'],
                    ['Moyen de signalement', 'Déclaration d’accessibilité', 'Adresse ou formulaire pour signaler un défaut et obtenir le contenu autrement'],
                ], 'Les obligations d’affichage de l’accessibilité pour une commune'),
                ('h3', 'Les sanctions depuis 2023'),
                ('p', 'L’ordonnance du 6 septembre 2023 a confié le contrôle à l’Arcom. Celle ci peut mettre en demeure la collectivité de se mettre en conformité, puis prononcer une sanction pouvant atteindre 50 000 €, renouvelable si le manquement persiste plus de six mois. L’absence de déclaration d’accessibilité ou de schéma pluriannuel est passible à elle seule d’une sanction de 25 000 € au plus. C’est le manquement le plus simple à constater : il suffit de regarder le pied de page.'),
                ('h3', 'Ce que l’accessibilité change concrètement'),
                ('ul', [
                    '<strong>Des contrastes suffisants</strong> entre le texte et le fond, ce qui profite aussi aux habitants qui lisent en plein soleil.',
                    '<strong>Une navigation au clavier</strong> complète, avec un lien d’évitement pour aller directement au contenu.',
                    '<strong>Des textes alternatifs</strong> sur les images porteuses d’information, et des images décoratives ignorées par les lecteurs d’écran.',
                    '<strong>Des titres hiérarchisés</strong> qui structurent chaque page et permettent de la parcourir rapidement.',
                    '<strong>Des formulaires étiquetés</strong>, avec des messages d’erreur clairs.',
                    '<strong>Des documents lisibles</strong> : un PDF scanné du bulletin municipal n’est pas accessible ; un PDF balisé ou une page web le sont.',
                ]),
            ]),
            ('Les contenus indispensables d’un site de mairie', [
                ('img', 'publication-actes-signature.webp', 'Délibérations et arrêtés doivent rester faciles à retrouver en ligne', 'Main signant un document posé sur un bureau'),
                ('p', 'Au delà des obligations, un site de commune efficace répond aux questions les plus fréquentes des habitants. Voici l’arborescence que nous recommandons comme point de départ, à adapter à la taille de la commune.'),
                ('table', ['Rubrique', 'Exemples de contenus', 'Mise à jour'], [
                    ['Démarches', 'État civil, papiers d’identité, urbanisme, inscriptions scolaires, location de salles', 'Quand la procédure change'],
                    ['Actualités et agenda', 'Événements, travaux, fermetures, conseil municipal', 'Chaque semaine'],
                    ['Vie municipale', 'Élus, délibérations, arrêtés, comptes rendus, budget', 'Après chaque conseil'],
                    ['Services et équipements', 'Écoles, périscolaire, médiathèque, gymnase, déchetterie', 'Horaires à chaque changement'],
                    ['Vie locale', 'Associations, commerces, marchés, patrimoine', 'Plusieurs fois par an'],
                    ['Infos pratiques', 'Horaires de la mairie, contacts, plan, numéros d’urgence', 'Dès qu’un horaire change'],
                ], 'Une arborescence type pour le site d’une commune'),
                ('h3', 'La publication des actes'),
                ('p', 'Depuis le 1er juillet 2022, la publication des actes des collectivités se fait par principe sous forme électronique. Les communes de moins de 3 500 habitants peuvent choisir, par délibération, un autre mode de publicité. Dans tous les cas, les délibérations, arrêtés et comptes rendus publiés en ligne doivent être présentés dans leur intégralité, sous un format non modifiable, et rester consultables. Un classement par année et par type, avec un moteur de recherche, évite que la rubrique devienne une pile de PDF.'),
                ('h3', 'Le bandeau d’alerte'),
                ('p', 'Vigilance météo, coupure d’eau, fermeture d’école, déviation : un bandeau d’alerte activable en un clic par les agents, visible sur toutes les pages, est l’une des fonctionnalités les plus utiles d’un site communal.'),
            ]),
            ('L’identité de la commune et les règles de design', [
                ('p', 'Le système de design de l’État, reconnaissable à la Marianne, est réservé aux sites des services de l’État. Une commune doit donc construire sa propre identité : son blason ou son logo, ses couleurs, ses typographies, dans le respect des contrastes imposés par le RGAA.'),
                ('p', 'C’est une bonne nouvelle : le site devient le reflet de la ville, de son patrimoine et de ses projets, plutôt qu’un gabarit générique. Si l’identité visuelle date ou n’existe que sur papier, le projet de site est souvent l’occasion de la moderniser ; notre page [logo et identité visuelle](logo-identite-visuelle) détaille cette démarche.'),
            ]),
            ('Données personnelles : les règles du RGPD', [
                ('ul', [
                    '<strong>Des formulaires minimaux</strong> : ne demander que les informations nécessaires à la démarche.',
                    '<strong>Une mention d’information</strong> sous chaque formulaire : finalité, durée de conservation, droits des personnes.',
                    '<strong>Une mesure d’audience respectueuse</strong> : des outils sans cookie publicitaire évitent un bandeau de consentement envahissant.',
                    '<strong>Un hébergement dans l’Union européenne</strong>, au nom de la commune.',
                    '<strong>Le registre des traitements</strong> tenu par la collectivité, qui doit mentionner les traitements liés au site.',
                ]),
                ('encart', 'Bon à savoir :', 'la CNIL publie des guides dédiés aux collectivités, utiles pour rédiger la politique de confidentialité du site.'),
            ]),
            ('Comment acheter le site en 2026', [
                ('p', 'Le décret du 29 décembre 2025 a relevé le seuil de dispense de procédure. Depuis le 1er avril 2026, un marché de fournitures ou de services d’un montant inférieur à 60 000 € HT peut être conclu sans publicité ni mise en concurrence préalables. L’acheteur doit toutefois choisir une offre pertinente, faire une bonne utilisation des deniers publics et ne pas contracter systématiquement avec le même prestataire lorsqu’il existe plusieurs offres susceptibles de répondre au besoin.'),
                ('p', 'Le site d’une commune se situe le plus souvent bien en dessous de ce seuil. En pratique, beaucoup de collectivités demandent tout de même deux ou trois devis pour comparer, ce qui reste une bonne pratique. Votre service achats reste seul juge de la procédure adaptée.'),
                ('table', ['Étape', 'Conseil'], [
                    ['Définir le besoin', 'Lister les rubriques, les démarches, les contenus à reprendre et les agents qui publieront'],
                    ['Comparer les offres', 'Vérifier l’audit d’accessibilité, la formation, la propriété du site et les coûts annuels'],
                    ['Exiger un devis détaillé', 'Une ligne par poste, pour justifier le choix dans le dossier d’achat'],
                    ['Prévoir l’après', 'Maintenance, hébergement, mises à jour de la déclaration d’accessibilité'],
                ], 'Les étapes d’achat d’un site de commune'),
            ]),
            ('Créer ou refondre : la méthode en six étapes', [
                ('ol', [
                    '<strong>Cadrage en mairie</strong> avec les élus et les services : objectifs, rubriques, contenus existants.',
                    '<strong>Inventaire de l’ancien site</strong> : pages consultées, documents à reprendre, adresses à rediriger.',
                    '<strong>Maquettes</strong> de l’accueil et des pages types, validées avant le développement.',
                    '<strong>Développement</strong> sous WordPress avec des gabarits accessibles et verrouillés.',
                    '<strong>Audit d’accessibilité</strong>, rédaction de la déclaration et corrections avant la mise en ligne.',
                    '<strong>Formation des agents</strong> et mise en ligne, avec redirections et suivi dans la Search Console.',
                ]),
                ('p', 'La reprise de l’existant est souvent sous estimée. Une refonte mal préparée fait disparaître des pages bien référencées et casse les liens partagés par les habitants ; notre guide [refonte de site sans perdre son référencement](article:refonte-site-referencement) explique comment l’éviter.'),
            ]),
            ('Mesurer l’usage du site après la mise en ligne', [
                ('p', 'Un site de commune n’est jamais terminé. Les premières semaines montrent ce que les habitants cherchent vraiment, et il faut adapter l’accueil en conséquence. Trois outils suffisent pour piloter le site sans cookie publicitaire ni bandeau de consentement envahissant.'),
                ('ul', [
                    '<strong>La Search Console de Google</strong> indique les recherches qui mènent au site : « horaires mairie », « inscription cantine », « carte d’identité ». Ces requêtes disent quelles pages mettre en avant.',
                    '<strong>Une mesure d’audience sans cookie</strong>, installée sur le serveur de la commune, montre les pages les plus consultées et les périodes de pointe, comme la rentrée scolaire.',
                    '<strong>Le moteur de recherche interne</strong> révèle les mots tapés par les habitants qui ne trouvent rien : chaque recherche sans résultat est une page à créer ou à renommer.',
                ]),
                ('p', 'Un point trimestriel avec le service communication, à partir de ces trois sources, permet de faire évoluer le menu, de mettre à jour les démarches les plus demandées et de supprimer ce qui ne sert plus.'),
            ]),
            ('Les erreurs les plus fréquentes', [
                ('ul', [
                    '<strong>Tout publier en PDF</strong> : un menu de cantine ou un horaire en PDF scanné n’est ni accessible ni lisible sur mobile.',
                    '<strong>Organiser le site comme l’organigramme</strong> : l’habitant ne sait pas quel service gère quoi ; il cherche une démarche.',
                    '<strong>Oublier la déclaration d’accessibilité</strong> : c’est l’obligation la plus visible et la plus simple à contrôler.',
                    '<strong>Laisser des actualités périmées</strong> en page d’accueil, qui donnent l’impression d’un site abandonné.',
                    '<strong>Dépendre d’un prestataire pour chaque modification</strong> : les agents doivent pouvoir publier seuls.',
                ]),
            ]),
            ('Notre approche pour les collectivités', [
                ('p', 'Aurea Media crée des sites pour les collectivités et les institutions depuis son studio parisien, avec des rendez vous en mairie dans les Hauts de Seine et en Île de France. Nous avons notamment conçu le site du salon européen [Dual Tech](realisation:dual-tech) et celui du think tank [INAS](realisation:inas), deux projets où la clarté de l’information et la confiance sont essentielles.'),
                ('p', 'Toutes les informations sur notre offre, les obligations et les références sont réunies sur notre page [création de site internet pour les collectivités](site-internet-collectivites).'),
            ]),
        ],
        'faq': [
            ('Une petite commune doit elle respecter le RGAA ?', 'Oui. L’obligation d’accessibilité s’applique à toutes les collectivités territoriales, quelle que soit leur taille. Seuls certains contenus, comme les archives anciennes ou les contenus de tiers, peuvent faire l’objet de dérogations, à justifier dans la déclaration.'),
            ('Qui rédige la déclaration d’accessibilité ?', 'Elle est établie à partir d’un audit réalisé sur un échantillon de pages. Le prestataire peut réaliser l’audit et préparer la déclaration, mais c’est la collectivité qui la publie et qui en est responsable.'),
            ('Un site de mairie peut il être fait sous WordPress ?', 'Oui. WordPress permet de créer des gabarits accessibles et laisse les agents publier simplement. L’accessibilité dépend surtout de la qualité du thème et de la façon dont les contenus sont saisis, d’où l’importance de la formation.'),
            ('Combien de temps faut il pour créer le site d’une commune ?', 'Pour une petite ou moyenne commune, comptez en général de quelques semaines à trois mois, selon le volume de contenus à reprendre et le calendrier de validation des élus.'),
        ],
        'appel': ('Vous préparez le site de votre commune ?', 'Rendez vous en mairie dans le 92 ou en visio, devis détaillé sous 24 h.', 'Parler de notre projet', 'contact'),
        'sources': [
            ('Accessibilité des sites des collectivités, collectivites-locales.gouv.fr', 'https://www.collectivites-locales.gouv.fr/accessibilite'),
            ('Référentiel général d’amélioration de l’accessibilité, accessibilite.numerique.gouv.fr', 'https://accessibilite.numerique.gouv.fr/'),
            ('Ordonnance du 6 septembre 2023 sur l’accessibilité des sites publics, vie-publique.fr', 'https://www.vie-publique.fr/loi/290899-ordonnance-6-septembre-2023-accessibilite-sites-web-publics-renforcee'),
            ('Décrets de simplification de la commande publique, Banque des Territoires', 'https://www.banquedesterritoires.fr/commande-publique-les-decrets-de-simplification-sont-parus'),
        ],
    },
    # ------------------------------------------------------------------ 2. Identité visuelle
    {
        'slug': 'refonte-identite-visuelle', 'ordre': 2,
        'titre': 'Refonte d’identité visuelle : quand et comment moderniser votre marque',
        'categorie': ('Création de site web', 'creation-site-web'),
        'mot_cle': 'refonte d’identité visuelle',
        'titre_seo': 'Refonte d’identité visuelle : quand, comment et à quel prix',
        'description': 'Refonte d’identité visuelle : les signes qu’il est temps de changer, les étapes, les livrables d’une charte graphique, le budget et les erreurs à éviter.',
        'extrait': 'Logo daté, supports incohérents, nouvelle offre : les signes qu’une refonte d’identité visuelle s’impose, la méthode pas à pas et ce qu’il faut recevoir à la fin.',
        'image': ('refonte-identite-visuelle-nuancier.webp', 'Nuanciers de couleurs et palette posés sur une table de travail'),
        'intro': 'Une refonte d’identité visuelle ne consiste pas à changer de logo parce qu’on s’en est lassé. Elle répond à un décalage entre ce que l’entreprise est devenue et l’image qu’elle renvoie. Bien menée, elle rend la marque plus lisible, plus cohérente et plus crédible, sur le site comme sur chaque support. Voici comment savoir s’il est temps, comment s’y prendre et ce que vous devez obtenir à la fin.',
        'bref': ('En bref : qu’est ce qu’une refonte d’identité visuelle ?',
                 'Une refonte d’identité visuelle consiste à moderniser l’ensemble des signes qui représentent une entreprise : le logo, les couleurs, les typographies, les pictogrammes, le style des images et les règles qui les relient, réunies dans une charte graphique. Elle se justifie quand l’image ne correspond plus à l’activité, quand les supports sont incohérents entre eux, quand le logo est illisible en petit ou sur mobile, ou lors d’un changement de positionnement. La démarche suit en général cinq étapes : audit de l’existant, positionnement, pistes créatives, déclinaison et charte. Selon l’ampleur, on parle d’évolution, qui conserve les éléments reconnus, ou de rupture, qui repart de zéro. Les livrables doivent comprendre les fichiers vectoriels, les déclinaisons et la cession des droits. Chez Aurea Media, une identité est livrée avec trois pistes, une charte et les fichiers SVG, PNG et PDF.'),
        'sections': [
            ('Les signes qu’il est temps de revoir votre identité', [
                ('p', 'Une identité vieillit rarement d’un coup. Elle se décale progressivement de l’entreprise, jusqu’à devenir un frein. Ces signaux doivent alerter :'),
                ('ul', [
                    '<strong>Votre logo est illisible en petit</strong> : sur un favicon, un avatar LinkedIn ou un écran de téléphone, on ne reconnaît plus rien.',
                    '<strong>Vos supports ne se ressemblent pas</strong> : le site, les devis, les cartes de visite et les réseaux sociaux semblent venir de trois entreprises différentes.',
                    '<strong>Votre offre a changé</strong> : vous visez une clientèle plus haut de gamme, des entreprises plutôt que des particuliers, ou un nouveau métier.',
                    '<strong>Vos concurrents se sont modernisés</strong> et votre image paraît datée à côté d’eux.',
                    '<strong>Vous n’avez pas les fichiers sources</strong> de votre logo, seulement une image floue.',
                    '<strong>Vous avez honte de donner votre carte</strong>, ou vous évitez d’envoyer prospects et partenaires vers votre site.',
                ]),
                ('encart', 'À retenir :', 'si vous vous reconnaissez dans deux de ces situations ou plus, une refonte d’identité visuelle sera probablement plus rentable qu’une succession de retouches.'),
            ]),
            ('Évolution ou rupture : deux niveaux de refonte', [
                ('table', ['', 'Évolution', 'Rupture'], [
                    ['Principe', 'Moderniser en gardant les éléments reconnus', 'Repartir d’une page blanche'],
                    ['Quand', 'L’image est connue mais vieillit', 'L’image freine, ou l’activité a profondément changé'],
                    ['Risque', 'Changement jugé trop timide', 'Perte de reconnaissance si elle est mal annoncée'],
                    ['Exemples', 'Simplifier le logo, nouvelle typographie, palette resserrée', 'Nouveau nom, nouveau symbole, nouvelle charte'],
                ], 'Comparatif des deux approches de refonte'),
                ('p', 'Pour une TPE ou une PME qui a une clientèle fidèle, l’évolution est souvent le bon choix : on garde ce qui est reconnu, une couleur ou une forme, et on corrige ce qui date. La rupture se justifie lors d’un changement de nom, d’une fusion ou d’un repositionnement complet.'),
            ]),
            ('Les éléments qui composent une identité visuelle', [
                ('h3', 'Le logo et ses déclinaisons'),
                ('p', 'Un logo professionnel n’est pas un fichier unique. Il existe en version principale, en version horizontale et verticale, en symbole seul pour les petits formats, en version monochrome et en version négative sur fond foncé.'),
                ('h3', 'La palette de couleurs'),
                ('p', 'Deux ou trois couleurs principales et quelques couleurs d’accompagnement, chacune avec ses références pour l’écran et l’impression. Les contrastes doivent rester lisibles, notamment pour le texte sur le site.'),
                ('h3', 'Les typographies'),
                ('p', 'Une typographie de titre et une typographie de texte, avec leurs graisses. Il faut vérifier les licences : une police utilisée sur un site doit être autorisée pour le web.'),
                ('h3', 'Les éléments graphiques et le style des images'),
                ('p', 'Pictogrammes, motifs, cadrages photo, traitement des couleurs : ce sont eux qui rendent une marque reconnaissable même sans logo.'),
                ('h3', 'La charte graphique'),
                ('p', 'Le document qui rassemble ces règles et montre des exemples d’application, pour que chaque prestataire, imprimeur ou collaborateur utilise la marque correctement.'),
            ]),
            ('Les étapes d’une refonte d’identité visuelle', [
                ('img', 'designer-logo-tablette.webp', 'Les pistes créatives sont dessinées puis testées sur des supports réels', 'Graphiste dessinant sur une tablette graphique à côté d’un nuancier'),
                ('ol', [
                    '<strong>Audit de l’existant</strong> : rassembler tous les supports, repérer ce qui est reconnu et ce qui pose problème.',
                    '<strong>Positionnement</strong> : clarifier la cible, les valeurs, le niveau de gamme et les concurrents.',
                    '<strong>Pistes créatives</strong> : plusieurs directions distinctes, présentées sur des supports réels plutôt que sur fond blanc.',
                    '<strong>Affinage</strong> de la piste retenue : proportions, couleurs, typographies, déclinaisons.',
                    '<strong>Charte et fichiers</strong> : rédaction des règles, préparation de tous les formats, cession des droits.',
                    '<strong>Déploiement</strong> : site internet, réseaux sociaux, documents commerciaux, signalétique.',
                ]),
                ('p', 'Le déploiement est souvent l’étape oubliée. Une nouvelle identité appliquée à moitié crée plus de confusion que l’ancienne. Il vaut mieux planifier dès le départ les supports à mettre à jour et leur ordre.'),
            ]),
            ('Préparer le brief : les questions à se poser avant de commencer', [
                ('p', 'La qualité d’une refonte dépend largement du brief. Un graphiste qui comprend votre entreprise propose des pistes justes dès le premier rendu, ce qui économise des allers retours. Prenez le temps de répondre à ces questions, par écrit :'),
                ('ol', [
                    '<strong>Qui sont vos clients</strong>, et qui voulez vous attirer dans trois ans ?',
                    '<strong>Quels mots</strong> voudriez vous qu’un client emploie pour décrire votre entreprise ?',
                    '<strong>Quels concurrents</strong> admirez vous, et desquels voulez vous vous distinguer ?',
                    '<strong>Qu’est ce qui doit absolument rester</strong> : une couleur, un symbole, un nom ?',
                    '<strong>Où la marque sera t elle vue le plus souvent</strong> : écran, véhicule, façade, emballage, réseaux sociaux ?',
                    '<strong>Qui valide</strong>, et dans quel délai ?',
                ]),
                ('p', 'Joignez au brief tous vos supports actuels, même ceux que vous trouvez ratés : ils montrent au graphiste les contraintes réelles et les erreurs à ne pas reproduire.'),
            ]),
            ('Tester une piste avant de la valider', [
                ('p', 'Un logo se juge en situation, jamais seul sur un fond blanc. Avant de valider une piste, demandez à la voir appliquée à cinq supports concrets :'),
                ('table', ['Test', 'Ce qu’il révèle'], [
                    ['Favicon et avatar de réseau social', 'La lisibilité en très petit format'],
                    ['Page d’accueil du site, sur mobile', 'L’équilibre avec les textes et les photos'],
                    ['Version noir et blanc', 'La solidité de la forme, sans l’aide de la couleur'],
                    ['Fond foncé et fond photo', 'La qualité de la version négative'],
                    ['Support physique : carte, véhicule, enseigne', 'Le rendu à distance et à l’impression'],
                ], 'Cinq tests pour valider une identité visuelle'),
                ('p', 'Montrez ensuite les pistes à quelques clients de confiance plutôt qu’à tout l’entourage. Leur avis porte sur ce qui compte : est ce que la marque leur paraît crédible, claire et adaptée à ce qu’ils achètent ?'),
            ]),
            ('Combien coûte une refonte d’identité visuelle ?', [
                ('p', 'Les prix varient fortement selon le prestataire et l’ampleur du travail. Ces ordres de grandeur, constatés en France, aident à situer un devis.'),
                ('table', ['Prestation', 'Budget indicatif', 'Ce qui est généralement inclus'], [
                    ['Logo seul en freelance', 'De quelques centaines à 1 500 € environ', 'Une ou deux pistes, fichiers du logo'],
                    ['Identité complète en studio', 'De 1 500 à 5 000 € environ', 'Pistes, logo décliné, palette, typographies, charte'],
                    ['Plateforme de marque en agence', '10 000 € et plus', 'Stratégie, nom, identité, charte détaillée, déploiement'],
                ], 'Ordres de grandeur constatés en France, hors impression'),
                ('p', 'À ces montants peuvent s’ajouter le dépôt de la marque à l’INPI, la refonte du site, l’impression des nouveaux supports et la signalétique. Prévoyez ces postes dès le départ pour éviter un déploiement partiel.'),
            ]),
            ('Protéger votre nouvelle identité', [
                ('p', 'Avant de valider un nouveau nom ou un nouveau logo, vérifiez qu’il n’est pas déjà déposé par une autre entreprise pour des activités proches. La base de données de l’INPI est consultable gratuitement. Le dépôt de la marque, en ligne, protège ensuite votre nom et votre logo pour dix ans renouvelables.'),
                ('p', 'Exigez aussi la cession des droits d’auteur par écrit. Sans elle, le créateur du logo reste juridiquement titulaire de certains droits, ce qui peut poser problème le jour où vous voulez modifier ou décliner la marque.'),
                ('encart', 'Bon à savoir :', 'la cession des droits doit préciser les droits cédés, les supports, la durée et le territoire. Une simple facture ne suffit pas.'),
            ]),
            ('Réussir le lancement de la nouvelle identité', [
                ('ul', [
                    '<strong>Expliquer le changement</strong> à vos clients : un message court sur ce qui évolue et pourquoi.',
                    '<strong>Changer le site en premier</strong>, c’est le support le plus consulté ; profitez en pour vérifier sa vitesse et son référencement.',
                    '<strong>Mettre à jour les profils en ligne</strong> : fiche Google, réseaux sociaux, annuaires professionnels.',
                    '<strong>Remplacer les documents commerciaux</strong> : devis, factures, présentations, signatures d’e-mail.',
                    '<strong>Garder la cohérence dans la durée</strong> en partageant la charte avec chaque prestataire.',
                ]),
                ('p', 'Si votre site doit évoluer en même temps, une [refonte de site internet](refonte-site-internet) menée avec la nouvelle identité évite de payer deux fois la conception graphique. Pour la fiche Google, notre guide [fiche Google Business Profile](article:fiche-google-business-profile) détaille les réglages à reprendre.'),
            ]),
            ('Identité visuelle et site internet : les points de vigilance', [
                ('p', 'Le site est souvent le premier support où la nouvelle identité sera vue, et le plus exigeant techniquement. Quelques règles évitent les mauvaises surprises au moment de l’intégration.'),
                ('ul', [
                    '<strong>Le logo en SVG</strong> : un fichier vectoriel reste net sur tous les écrans et pèse très peu, contrairement à une image PNG agrandie.',
                    '<strong>Des couleurs accessibles</strong> : la couleur principale doit offrir un contraste suffisant avec le texte, sinon il faut prévoir une variante plus foncée pour les boutons et les liens.',
                    '<strong>Des polices web optimisées</strong> : deux familles au maximum, chargées localement, pour ne pas ralentir le site.',
                    '<strong>Un favicon dédié</strong> : le symbole seul, simplifié, plutôt que le logo complet réduit à 32 pixels.',
                    '<strong>Une image de partage</strong> aux couleurs de la marque, affichée quand une page est partagée sur LinkedIn ou dans une messagerie.',
                ]),
                ('p', 'Une identité pensée dès le départ pour l’écran se décline ensuite sans difficulté sur papier ; l’inverse est beaucoup moins vrai.'),
            ]),
            ('Les erreurs à éviter', [
                ('ul', [
                    '<strong>Choisir selon ses goûts personnels</strong> plutôt que selon sa clientèle.',
                    '<strong>Suivre une tendance du moment</strong> qui datera vite.',
                    '<strong>Juger un logo sur fond blanc</strong> au lieu de le tester en petit, sur un site, sur un véhicule ou une façade.',
                    '<strong>Multiplier les avis internes</strong> jusqu’à obtenir un compromis sans caractère.',
                    '<strong>Oublier les fichiers vectoriels</strong> et la cession des droits.',
                ]),
            ]),
            ('Notre méthode chez Aurea Media', [
                ('p', 'Chez Aurea Media, une identité visuelle est conçue avec trois pistes créatives présentées sur des supports réels, puis affinée jusqu’à la charte. Vous recevez le logo et ses déclinaisons, la palette, les typographies et la charte, en fichiers SVG, PNG et PDF vectoriels, avec la cession des droits. Le détail de l’offre et des exemples sont sur notre page [logo et identité visuelle](logo-identite-visuelle).'),
            ]),
        ],
        'faq': [
            ('Faut il changer de logo en même temps que le site ?', 'Ce n’est pas obligatoire, mais c’est souvent plus économique : la conception graphique du site repose sur l’identité. Changer de logo six mois après un nouveau site oblige à reprendre une partie du travail.'),
            ('Combien de temps dure une refonte d’identité visuelle ?', 'Pour une TPE ou une PME, comptez en général de trois à six semaines entre l’audit et la livraison de la charte, selon le nombre d’allers retours et la disponibilité des décideurs.'),
            ('Peut on garder son ancien logo et changer le reste ?', 'Oui. On peut conserver un logo reconnu et moderniser la palette, les typographies et les supports. C’est une forme d’évolution très courante.'),
            ('Une refonte d’identité visuelle fait elle perdre des clients ?', 'Rarement, si le changement est expliqué et que les éléments reconnus sont conservés. Les clients s’attachent à la qualité du service plus qu’au logo ; une image plus claire facilite en revanche la conquête de nouveaux clients.'),
            ('Que faire si je n’ai pas les fichiers de mon ancien logo ?', 'Le logo peut être redessiné en vectoriel à l’identique. C’est souvent l’occasion de corriger ses défauts de lisibilité.'),
        ],
        'appel': ('Votre image ne reflète plus votre entreprise ?', 'Parlons de votre marque : proposition détaillée sous 24 h.', 'Demander mon devis', 'contact'),
        'sources': [
            ('Déposer une marque, INPI', 'https://www.inpi.fr/realiser-demarches/propriete-intellectuelle/depot-de-marque'),
            ('Institut national de la propriété industrielle, site officiel', 'https://www.inpi.fr/'),
        ],
    },
    # ------------------------------------------------------------------ 3. Prix refonte
    {
        'slug': 'prix-refonte-site-internet', 'ordre': 3,
        'titre': 'Prix d’une refonte de site internet en 2026 : le budget détaillé',
        'categorie': ('Prix', 'prix'),
        'mot_cle': 'prix d’une refonte de site internet',
        'titre_seo': 'Prix d’une refonte de site internet en 2026 : budget détaillé',
        'description': 'Prix d’une refonte de site internet en 2026 : budgets selon le type de site, lignes du devis, coûts cachés et méthode pour protéger votre référencement.',
        'extrait': 'Combien prévoir pour refaire votre site ? Budgets selon le type de site, lignes d’un devis, coûts oubliés et méthode pour ne pas perdre votre référencement.',
        'image': ('prix-refonte-maquette-tablette.webp', 'Maquette filaire d’un site dessinée sur une tablette'),
        'intro': 'Le prix d’une refonte de site internet dépend moins du nombre de pages que de ce qu’il faut conserver, transformer et protéger. Une refonte n’est pas une création à partir de rien : il y a des contenus à reprendre, un référencement à préserver, parfois des données clients à migrer. Voici comment construire un budget réaliste et lire un devis ligne par ligne.',
        'bref': ('En bref : combien coûte la refonte d’un site internet en 2026 ?',
                 'La refonte d’un site vitrine de cinq à quinze pages coûte en général de 1 000 à 5 000 € chez un studio ou une petite agence, la refonte d’une boutique en ligne de 2 000 à 8 000 € selon le catalogue et les connexions, et un projet sur mesure complexe peut dépasser 10 000 €. Le prix dépend de cinq facteurs : le volume de contenus à reprendre ou réécrire, le design, les fonctionnalités, la migration des données et le travail de protection du référencement, avec l’inventaire des pages et le plan de redirections. Les coûts souvent oubliés sont la rédaction, les photos, les redirections et la formation. Une refonte bien menée peut améliorer nettement la visibilité : après la refonte de son propre site, Aurea Media a vu sa position moyenne dans Google passer d’environ 60 à environ 35 en quelques semaines.'),
        'sections': [
            ('Les budgets selon le type de site', [
                ('table', ['Type de refonte', 'Budget indicatif', 'Délai courant'], [
                    ['Site vitrine simple, 5 à 15 pages', 'De 1 000 à 5 000 € environ', '3 à 6 semaines'],
                    ['Site vitrine riche, blog et multilingue', 'De 4 000 à 10 000 € environ', '6 à 10 semaines'],
                    ['Boutique en ligne', 'De 2 000 à 8 000 € environ', '5 à 10 semaines'],
                    ['Plateforme sur mesure, espace client', '10 000 € et plus', 'Plusieurs mois'],
                ], 'Ordres de grandeur constatés en France chez les studios et petites agences'),
                ('p', 'Ces fourchettes servent à situer un devis. Elles varient selon le prestataire, la part de travail confiée et l’état du site de départ. Pour les créations, notre article sur le [prix d’un site vitrine](article:prix-site-vitrine) donne les repères équivalents.'),
            ]),
            ('Les cinq facteurs qui font le prix', [
                ('h3', '1. Le volume de contenus'),
                ('p', 'Reprendre 10 pages à l’identique ou réécrire 80 pages ne demande pas le même travail. La rédaction est souvent le poste le plus sous estimé, alors que c’est elle qui convainc les visiteurs et Google.'),
                ('h3', '2. Le design'),
                ('p', 'Un thème existant légèrement adapté coûte moins cher qu’une maquette sur mesure, mais donne un site qui ressemble à beaucoup d’autres. Le design sur mesure se justifie quand l’image est un argument commercial.'),
                ('h3', '3. Les fonctionnalités'),
                ('p', 'Prise de rendez vous, multilingue, espace membre, filtres de recherche, connexion à un logiciel : chaque fonctionnalité ajoute du développement et des tests.'),
                ('h3', '4. La migration des données'),
                ('p', 'Produits, clients, commandes, articles de blog : changer de technologie impose de transférer ces données proprement, sans perte ni doublon.'),
                ('h3', '5. La protection du référencement'),
                ('p', 'Inventaire des pages qui reçoivent du trafic, plan de redirections, reprise des balises et suivi après la mise en ligne. C’est la ligne que l’on supprime pour baisser un devis, et celle qui coûte le plus cher quand elle manque.'),
            ]),
            ('Lire un devis de refonte ligne par ligne', [
                ('table', ['Poste', 'Ce qu’il doit couvrir', 'Signal d’alerte'], [
                    ['Audit de l’existant', 'Pages, trafic, positions, contenus à garder', 'Absent du devis'],
                    ['Arborescence et maquettes', 'Pages types validées avant le développement', 'Aucune maquette prévue'],
                    ['Développement', 'Intégration, mobile, vitesse, accessibilité', 'Prix global sans détail'],
                    ['Contenus', 'Reprise, réécriture ou rédaction, avec le nombre de pages', 'Rédaction non mentionnée'],
                    ['Redirections et SEO', 'Plan de redirections 301, balises, sitemap, Search Console', 'Non prévu ou en option floue'],
                    ['Formation et propriété', 'Prise en main, accès et nom de domaine à votre nom', 'Accès conservés par le prestataire'],
                ], 'Les lignes d’un devis de refonte complet'),
                ('encart', 'Conseil :', 'demandez toujours qui sera propriétaire du site, du nom de domaine et des comptes à la fin du projet. La réponse doit être : vous.'),
            ]),
            ('Les coûts cachés d’une refonte', [
                ('ul', [
                    '<strong>Les photos</strong> : changer de design révèle souvent des images de mauvaise qualité.',
                    '<strong>Les licences</strong> : extensions, polices ou thèmes payants, parfois chaque année.',
                    '<strong>Le temps interne</strong> : relire, valider, fournir les contenus, souvent plus long que prévu.',
                    '<strong>Les mises à jour des supports</strong> : liens dans les signatures, documents et réseaux sociaux.',
                    '<strong>La maintenance</strong> : sauvegardes, mises à jour et sécurité après la mise en ligne.',
                ]),
            ]),
            ('Refonte ou nouveau site : que choisir ?', [
                ('p', 'Une refonte partielle, qui garde la structure et change le design, convient quand le site est techniquement sain et bien référencé. Une refonte complète, avec nouvelle technologie et nouvelle arborescence, se justifie quand le site est lent, impossible à modifier, non sécurisé ou construit sur un outil abandonné.'),
                ('table', ['Situation', 'Recommandation'], [
                    ['Site récent, bien positionné, design daté', 'Refonte graphique en gardant les adresses'],
                    ['Site lent, difficile à modifier', 'Refonte complète avec migration des contenus'],
                    ['Activité qui a changé', 'Nouvelle arborescence et nouveaux contenus, avec redirections'],
                    ['Boutique bloquée par sa plateforme', 'Migration vers une solution plus souple'],
                ], 'Choisir le niveau de refonte'),
            ]),
            ('Protéger votre référencement pendant la refonte', [
                ('img', 'statistiques-site-avant-refonte.webp', 'Relever le trafic et les positions avant la refonte permet de mesurer le résultat', 'Ordinateur portable affichant un tableau de bord de statistiques'),
                ('ol', [
                    'Exporter depuis la Search Console les pages qui reçoivent des clics et des impressions.',
                    'Associer chaque ancienne adresse à sa nouvelle page dans un plan de redirections 301.',
                    'Reprendre les titres, descriptions et contenus qui fonctionnent, puis les améliorer.',
                    'Tester les redirections avant la mise en ligne, puis envoyer le nouveau sitemap.',
                    'Suivre les erreurs 404 et les positions pendant les semaines qui suivent.',
                ]),
                ('p', 'Notre exemple : lors de la refonte de son propre site, Aurea Media a mis en place un plan de redirections couvrant les anciennes adresses. Dans les semaines suivantes, la position moyenne relevée dans la Search Console est passée d’environ 60 à environ 35, et les clics ont progressé. Le détail de la méthode est dans notre guide [refonte de site sans perdre son référencement](article:refonte-site-referencement).'),
            ]),
            ('Exemple de répartition d’un budget de refonte', [
                ('p', 'Pour comprendre où va l’argent, voici une répartition type pour la refonte d’un site vitrine d’une dizaine de pages. Les proportions changent selon les projets, mais elles montrent que le développement n’est qu’une partie du travail.'),
                ('table', ['Poste', 'Part indicative du budget'], [
                    ['Audit, arborescence et plan de redirections', 'Environ 10 à 15 %'],
                    ['Maquettes et design', 'Environ 20 à 25 %'],
                    ['Développement et intégration', 'Environ 35 à 45 %'],
                    ['Contenus, reprise et optimisation', 'Environ 15 à 20 %'],
                    ['Tests, mise en ligne et formation', 'Environ 5 à 10 %'],
                ], 'Répartition indicative pour une refonte de site vitrine'),
                ('p', 'Si un devis consacre presque tout au développement et rien à l’audit ou aux contenus, le site sera peut être joli, mais il risque de perdre ses positions et de mal convertir.'),
            ]),
            ('Ce qu’une refonte doit améliorer au delà du design', [
                ('ul', [
                    '<strong>La vitesse</strong> : un site plus léger améliore l’expérience et les signaux de performance suivis par Google.',
                    '<strong>Le parcours de conversion</strong> : un bouton d’appel visible, un formulaire court, des preuves près des appels à l’action.',
                    '<strong>Les données structurées</strong> : elles aident Google à comprendre votre entreprise, vos services et vos avis.',
                    '<strong>L’accessibilité</strong> : contrastes, navigation au clavier et titres hiérarchisés profitent à tous les visiteurs.',
                    '<strong>L’autonomie</strong> : vous devez pouvoir publier une actualité ou changer un prix sans appeler personne.',
                ]),
                ('p', 'Fixez deux ou trois objectifs mesurables avant de commencer, par exemple le nombre de demandes de devis par mois ou le temps de chargement sur mobile. Ils serviront à juger la refonte trois mois après la mise en ligne.'),
            ]),
            ('Un calendrier type de refonte', [
                ('p', 'Voici le déroulé d’une refonte de site vitrine menée en cinq semaines environ. Il varie selon le volume de contenus et la rapidité des validations, mais les étapes restent les mêmes.'),
                ('table', ['Période', 'Étape', 'Ce que vous faites'], [
                    ['Semaine 1', 'Audit et arborescence', 'Vous partagez les accès et vos objectifs'],
                    ['Semaine 2', 'Maquettes des pages types', 'Vous validez ou demandez des ajustements'],
                    ['Semaines 3 et 4', 'Développement et contenus', 'Vous relisez les textes et fournissez les photos'],
                    ['Semaine 5', 'Tests, redirections, mise en ligne', 'Vous suivez la formation'],
                    ['Semaines suivantes', 'Suivi du référencement', 'Vous recevez un point sur les positions'],
                ], 'Le calendrier d’une refonte de site vitrine'),
                ('p', 'Le principal facteur de retard n’est presque jamais technique : ce sont les contenus et les validations. Désigner une seule personne pour valider côté entreprise fait souvent gagner une à deux semaines.'),
            ]),
            ('Les questions à poser avant de signer', [
                ('ol', [
                    '<strong>Comment allez vous protéger mes positions actuelles ?</strong> La réponse doit mentionner l’inventaire des pages et les redirections 301.',
                    '<strong>Verrai je des maquettes avant le développement ?</strong> Sans maquette, les modifications coûtent plus cher en fin de projet.',
                    '<strong>Le site sera t il plus rapide que l’actuel ?</strong> Demandez une mesure avant et après, par exemple avec PageSpeed Insights.',
                    '<strong>Pourrai je modifier mes contenus seul ?</strong> Une formation doit être prévue.',
                    '<strong>Qui possède le site, le domaine et les comptes ?</strong> Vous, sans condition.',
                    '<strong>Que se passe t il après la mise en ligne ?</strong> Un suivi des erreurs et des positions pendant quelques semaines est un vrai plus.',
                ]),
                ('p', 'Pour comparer plusieurs prestataires sur ces critères, notre guide [comment choisir son agence web](article:choisir-agence-web) propose une grille complète.'),
            ]),
            ('Réduire le budget sans sacrifier l’essentiel', [
                ('ul', [
                    '<strong>Fournir vous même les contenus</strong> que vous maîtrisez, en confiant la relecture à l’agence.',
                    '<strong>Supprimer les pages inutiles</strong> : une page sans visite ni utilité n’a pas besoin d’être refaite.',
                    '<strong>Phaser le projet</strong> : d’abord les pages qui rapportent, ensuite le blog ou les fonctionnalités secondaires.',
                    '<strong>Garder la technologie</strong> si elle convient, pour éviter une migration.',
                ]),
                ('p', 'En revanche, ne supprimez ni les maquettes, ni les redirections, ni la formation : ce sont les lignes qui évitent de payer une seconde refonte.'),
            ]),
            ('Le prix d’une refonte chez Aurea Media', [
                ('p', 'Chez Aurea Media, une [refonte de site internet](refonte-site-internet) démarre au même prix qu’une création : à partir de 1 000 € HT pour un site vitrine et de 1 650 € HT pour une boutique en ligne. Chaque devis inclut l’audit de l’existant, les maquettes, le développement sur mesure, le plan de redirections et la formation. Le détail est sur notre page [tarifs](tarifs).'),
            ]),
        ],
        'faq': [
            ('Combien de temps dure une refonte de site internet ?', 'Pour un site vitrine, comptez en général de trois à six semaines entre la validation du devis et la mise en ligne. Une boutique ou un site riche en contenus demande davantage, selon la migration et la rédaction.'),
            ('Une refonte fait elle perdre des positions dans Google ?', 'Une baisse temporaire est possible pendant que Google prend en compte les changements. Avec un inventaire des pages et des redirections complètes, les positions se stabilisent en général rapidement, et progressent si les contenus sont meilleurs.'),
            ('Faut il changer de prestataire pour refaire son site ?', 'Pas forcément. L’essentiel est d’obtenir tous les accès et la propriété du nom de domaine, pour rester libre de choisir le prestataire de la refonte.'),
            ('La refonte améliore t elle la vitesse du site ?', 'C’est l’un des bénéfices les plus fréquents, à condition que le nouveau site soit conçu pour la performance : images au format WebP, code léger, peu d’extensions. Demandez une mesure avant et après.'),
            ('Peut on refaire son site par étapes ?', 'Oui. On peut commencer par les pages qui génèrent des demandes, puis traiter le blog ou les fonctionnalités secondaires. Le devis doit alors préciser chaque phase.'),
        ],
        'appel': ('Vous voulez un budget précis pour votre refonte ?', 'Audit de votre site actuel et devis détaillé sous 24 h.', 'Demander mon devis', 'contact'),
        'sources': [
            ('Modifier les URL d’un site avec des redirections 301, Google Search Central', 'https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes?hl=fr'),
            ('Google Search Console, aide officielle', 'https://support.google.com/webmasters/?hl=fr'),
        ],
    },
    # ------------------------------------------------------------------ 4. Maintenance WordPress
    {
        'slug': 'prix-maintenance-site-wordpress', 'ordre': 4,
        'titre': 'Maintenance d’un site WordPress : prix, contenu et bonnes pratiques',
        'categorie': ('Prix', 'prix'),
        'mot_cle': 'maintenance d’un site WordPress',
        'titre_seo': 'Maintenance d’un site WordPress : prix et contenu en 2026',
        'description': 'Maintenance d’un site WordPress : ce qu’elle comprend, son prix selon la formule, ce que vous pouvez faire seul et comment choisir un contrat utile en 2026.',
        'extrait': 'Mises à jour, sauvegardes, sécurité, vitesse : ce que comprend la maintenance d’un site WordPress, combien elle coûte et ce que vous pouvez faire vous même.',
        'image': ('maintenance-wordpress-code.webp', 'Écran d’ordinateur portable affichant du code dans un éditeur'),
        'intro': 'La maintenance d’un site WordPress est l’assurance de votre site : on ne la voit pas quand tout va bien, on la regrette le jour où le site tombe ou se fait pirater. WordPress fait fonctionner plus de 40 % des sites dans le monde, ce qui en fait aussi une cible fréquente. Voici ce qu’une maintenance doit couvrir, combien elle coûte et comment choisir sans payer pour rien.',
        'bref': ('En bref : que comprend la maintenance d’un site WordPress et combien coûte t elle ?',
                 'La maintenance d’un site WordPress regroupe les mises à jour du cœur, du thème et des extensions, des sauvegardes régulières stockées hors du serveur, la surveillance de la sécurité et de la disponibilité, le contrôle de la vitesse et, selon les formules, un temps de petites modifications. En France, les formules constatées vont d’environ 30 à 80 € HT par mois pour un site vitrine, à 100 à 300 € HT par mois pour une boutique en ligne ou un site critique, avec davantage de vérifications et un délai d’intervention garanti. Un propriétaire à l’aise peut assurer lui même une partie des tâches, à condition de sauvegarder avant chaque mise à jour et de tester le site ensuite. L’essentiel est de ne jamais laisser un site sans mises à jour pendant des mois : c’est la première cause de piratage.'),
        'sections': [
            ('Pourquoi un site WordPress a besoin de maintenance', [
                ('p', 'Un site WordPress est un logiciel composé de plusieurs briques : le cœur de WordPress, un thème, des extensions et la version de PHP du serveur. Chacune évolue de son côté, avec des corrections de sécurité et de nouvelles fonctions. Sans suivi, ces briques prennent du retard, deviennent incompatibles entre elles ou laissent des failles connues ouvertes.'),
                ('ul', [
                    '<strong>La sécurité</strong> : les failles corrigées sont publiées, donc rapidement exploitées sur les sites non mis à jour.',
                    '<strong>La compatibilité</strong> : un hébergeur qui change de version de PHP peut casser un site ancien.',
                    '<strong>La vitesse</strong> : les extensions accumulées et les images lourdes ralentissent le site au fil des mois.',
                    '<strong>Le référencement</strong> : un site lent, en erreur ou piraté perd des positions dans Google.',
                ]),
            ]),
            ('Ce que comprend une maintenance sérieuse', [
                ('table', ['Tâche', 'Fréquence conseillée', 'Pourquoi'], [
                    ['Mises à jour du cœur, du thème et des extensions', 'Chaque semaine ou chaque mois, et sans délai pour les correctifs de sécurité', 'Fermer les failles connues'],
                    ['Sauvegarde complète, fichiers et base de données', 'Chaque jour pour une boutique, chaque semaine pour un site vitrine', 'Pouvoir revenir en arrière'],
                    ['Stockage des sauvegardes hors du serveur', 'À chaque sauvegarde', 'Survivre à une panne de l’hébergeur'],
                    ['Surveillance de la disponibilité', 'En continu', 'Être alerté si le site tombe'],
                    ['Analyse de sécurité', 'Chaque semaine', 'Détecter un fichier modifié ou un code malveillant'],
                    ['Contrôle de la vitesse', 'Chaque mois', 'Garder de bons temps de chargement'],
                    ['Vérification des formulaires', 'Chaque mois', 'Ne pas perdre de demandes de contact'],
                    ['Rapport', 'Chaque mois', 'Savoir ce qui a été fait'],
                ], 'Les tâches d’une maintenance WordPress'),
                ('encart', 'Point clé :', 'une sauvegarde n’a de valeur que si elle a déjà été restaurée avec succès au moins une fois. Demandez à votre prestataire comment il teste les siennes.'),
            ]),
            ('Combien coûte la maintenance d’un site WordPress ?', [
                ('table', ['Formule', 'Prix indicatif', 'Pour qui'], [
                    ['Essentielle', 'Environ 30 à 80 € HT par mois', 'Site vitrine, peu de modifications'],
                    ['Confort', 'Environ 80 à 150 € HT par mois', 'Site avec blog, quelques modifications mensuelles'],
                    ['Boutique et site critique', 'Environ 100 à 300 € HT par mois', 'E-commerce, réservations, délai d’intervention garanti'],
                    ['À l’intervention', 'Au temps passé', 'Propriétaire autonome qui a besoin d’aide ponctuelle'],
                ], 'Ordres de grandeur constatés en France'),
                ('p', 'Le prix dépend surtout du nombre d’extensions, de la présence d’une boutique, de la fréquence des sauvegardes et du délai d’intervention promis. Comparez le contenu des formules plutôt que leur seul montant : une formule bon marché sans sauvegarde externe ni test après mise à jour protège peu.'),
            ]),
            ('Les mises à jour expliquées simplement', [
                ('p', 'Toutes les mises à jour ne présentent pas le même risque. Comprendre leur nature aide à décider quand les appliquer et avec quelles précautions.'),
                ('table', ['Type de mise à jour', 'Exemple', 'Risque', 'Conseil'], [
                    ['Correctif de sécurité', 'Version 6.x.1 vers 6.x.2', 'Faible', 'Appliquer sans attendre'],
                    ['Version majeure de WordPress', 'Nouvelle version 6.x', 'Moyen', 'Attendre quelques jours, sauvegarder, tester'],
                    ['Extension', 'Formulaire, SEO, cache', 'Variable', 'Une par une, avec un test après chacune'],
                    ['Thème', 'Nouvelle version du thème', 'Moyen à élevé', 'Vérifier les personnalisations avant'],
                    ['Version de PHP', 'Changement chez l’hébergeur', 'Élevé sur un site ancien', 'Tester sur une copie du site'],
                ], 'Le niveau de risque des différentes mises à jour'),
                ('p', 'La règle d’or reste la même pour toutes : une sauvegarde complète juste avant, et une vérification des pages clés juste après.'),
            ]),
            ('L’hébergement, l’autre moitié de la maintenance', [
                ('img', 'hebergement-serveurs-site.webp', 'Un bon hébergement fait une grande partie du travail de sécurité et de vitesse', 'Couloir de salle serveurs éclairée en bleu'),
                ('p', 'Un hébergement de qualité réduit fortement le travail de maintenance : versions de PHP à jour, sauvegardes automatiques, certificat HTTPS, pare feu et support réactif. À l’inverse, l’hébergement le moins cher se paie souvent en lenteur et en temps perdu.'),
                ('ul', [
                    '<strong>Serveurs dans l’Union européenne</strong>, pour la conformité au RGPD.',
                    '<strong>Version de PHP récente</strong>, conforme aux recommandations de WordPress.',
                    '<strong>Sauvegardes quotidiennes</strong> conservées plusieurs jours.',
                    '<strong>Certificat HTTPS</strong> inclus et renouvelé automatiquement.',
                    '<strong>Compte à votre nom</strong>, pour rester propriétaire de votre site.',
                ]),
            ]),
            ('Ce que vous pouvez faire vous même', [
                ('p', 'Un propriétaire à l’aise avec l’administration de WordPress peut assurer une maintenance de base, en respectant une routine stricte :'),
                ('ol', [
                    'Faire une sauvegarde complète, et vérifier qu’elle est bien téléchargée hors du serveur.',
                    'Lire les notes de version des mises à jour importantes, notamment celles du thème.',
                    'Mettre à jour les extensions une par une, puis le thème, puis le cœur.',
                    'Tester les pages clés, le formulaire de contact et, pour une boutique, une commande complète.',
                    'Supprimer les extensions désactivées et inutiles.',
                ]),
                ('p', 'Les limites : diagnostiquer un conflit entre extensions, nettoyer un site piraté ou optimiser une base de données demande une vraie expertise. C’est là qu’un prestataire fait gagner du temps et évite les erreurs coûteuses.'),
            ]),
            ('Maintenance et référencement', [
                ('p', 'La maintenance protège aussi votre visibilité dans Google. Un site qui affiche des erreurs, devient lent ou se fait injecter des pages indésirables perd la confiance des moteurs de recherche, parfois pour longtemps.'),
                ('ul', [
                    '<strong>Surveiller la Search Console</strong> chaque mois : pages en erreur, problèmes d’indexation, alertes de sécurité.',
                    '<strong>Corriger les liens cassés</strong> et rediriger les pages supprimées.',
                    '<strong>Contrôler la vitesse sur mobile</strong> après chaque ajout d’extension ou de fonctionnalité.',
                    '<strong>Vérifier le sitemap</strong> et les balises des nouvelles pages.',
                ]),
            ]),
            ('Les points en plus pour une boutique WooCommerce', [
                ('p', 'Une boutique en ligne demande une maintenance plus attentive, car chaque minute de panne fait perdre des ventes et parce qu’elle manipule des commandes et des données clients.'),
                ('ul', [
                    '<strong>Des sauvegardes plus fréquentes</strong>, pour ne perdre aucune commande en cas de restauration.',
                    '<strong>Un test d’achat complet</strong> après chaque mise à jour : panier, livraison, paiement, e-mail de confirmation.',
                    '<strong>Le suivi des extensions de paiement et de livraison</strong>, qui évoluent avec les exigences des prestataires.',
                    '<strong>Le nettoyage régulier</strong> des commandes anciennes, des paniers abandonnés et des sessions qui alourdissent la base de données.',
                ]),
                ('p', 'Pour comparer WooCommerce à une plateforme hébergée, qui prend en charge une partie de cette maintenance contre un abonnement, lisez notre comparatif [WooCommerce ou Shopify](article:woocommerce-ou-shopify).'),
            ]),
            ('Les signes qu’un site est mal entretenu', [
                ('ul', [
                    '<strong>Des alertes de mise à jour</strong> qui s’accumulent dans le tableau de bord.',
                    '<strong>Un site qui ralentit</strong> sans raison apparente.',
                    '<strong>Des demandes de contact qui n’arrivent plus</strong>, souvent à cause d’un formulaire cassé.',
                    '<strong>Des pages inconnues</strong> qui apparaissent dans Google, signe possible de piratage.',
                    '<strong>Un avertissement de sécurité</strong> affiché par le navigateur.',
                ]),
                ('p', 'Si votre site cumule plusieurs de ces signes et repose sur un thème ou des extensions abandonnés, une simple maintenance ne suffira pas. Notre article sur le [prix d’une refonte de site internet](article:prix-refonte-site-internet) aide à évaluer l’option d’une refonte.'),
            ]),
            ('Préparer un plan de reprise en cas de panne', [
                ('p', 'Même bien entretenu, un site peut tomber : panne de l’hébergeur, mise à jour défectueuse, nom de domaine expiré. Un plan de reprise d’une page, préparé à l’avance, transforme une crise en simple contretemps.'),
                ('ul', [
                    '<strong>La liste des accès</strong> : hébergeur, nom de domaine, administration WordPress, comptes de messagerie, rangés dans un gestionnaire de mots de passe.',
                    '<strong>L’emplacement des sauvegardes</strong> et la procédure pour en restaurer une.',
                    '<strong>Les contacts utiles</strong> : support de l’hébergeur, prestataire, registraire du nom de domaine.',
                    '<strong>Les dates d’échéance</strong> du nom de domaine et de l’hébergement, avec renouvellement automatique activé.',
                    '<strong>Un message de secours</strong> à publier sur vos réseaux sociaux et votre fiche Google si le site est indisponible.',
                ]),
                ('encart', 'À vérifier :', 'parmi les pannes les plus évitables figure l’expiration du nom de domaine, liée à une carte bancaire expirée ou à une adresse e-mail qui n’est plus consultée. Vérifiez ces deux points une fois par an.'),
            ]),
            ('Choisir un contrat de maintenance utile', [
                ('table', ['Question à poser', 'Bonne réponse'], [
                    ['Où sont stockées les sauvegardes ?', 'Hors du serveur du site, avec plusieurs jours d’historique'],
                    ['Les mises à jour sont elles testées ?', 'Oui, sur une copie ou avec une vérification des pages clés'],
                    ['Quel délai en cas de panne ?', 'Un délai écrit dans le contrat'],
                    ['Que se passe t il en cas de piratage ?', 'Nettoyage inclus ou tarif annoncé à l’avance'],
                    ['Puis je résilier facilement ?', 'Oui, avec remise de tous les accès'],
                ], 'Les questions à poser avant de signer'),
            ]),
            ('Notre approche chez Aurea Media', [
                ('p', 'Les sites créés par Aurea Media sont livrés avec une formation, un hébergement et un nom de domaine au nom du client, sans abonnement imposé. La maintenance mensuelle est proposée en option : mises à jour, sauvegardes, sécurité et petites modifications. Le détail des options est sur notre page [tarifs](tarifs), et la création de site est présentée sur notre page [création de site vitrine](creation-site-vitrine).'),
            ]),
        ],
        'faq': [
            ('La maintenance est elle obligatoire ?', 'Aucune loi ne l’impose, mais un site WordPress sans mises à jour devient rapidement vulnérable. Si vous ne prenez pas de contrat, prévoyez au minimum une routine mensuelle de sauvegarde et de mise à jour.'),
            ('Peut on activer les mises à jour automatiques ?', 'Oui, WordPress le permet pour le cœur et les extensions. C’est utile pour les correctifs de sécurité, mais il faut des sauvegardes et une surveillance pour réagir si une mise à jour casse une page.'),
            ('Que faire si mon site WordPress est piraté ?', 'Mettre le site hors ligne si nécessaire, changer tous les mots de passe, restaurer une sauvegarde saine, mettre à jour l’ensemble puis rechercher la faille. Un professionnel est souvent nécessaire pour un nettoyage complet.'),
            ('Combien de temps garder les sauvegardes ?', 'Gardez plusieurs points de restauration : par exemple les sauvegardes quotidiennes des sept derniers jours et une sauvegarde mensuelle sur plusieurs mois. Un piratage peut rester invisible plusieurs semaines avant d’être découvert.'),
            ('La maintenance comprend elle les modifications de contenu ?', 'Cela dépend des formules. Certaines incluent un temps mensuel de petites modifications, d’autres les facturent à part. Vérifiez ce point dans le contrat.'),
        ],
        'appel': ('Votre site WordPress a besoin d’être repris en main ?', 'Diagnostic de votre site et proposition sous 24 h.', 'Demander un diagnostic', 'contact'),
        'sources': [
            ('Configuration requise pour WordPress, wordpress.org', 'https://wordpress.org/about/requirements/'),
            ('Part de marché des CMS, W3Techs', 'https://w3techs.com/technologies/details/cm-wordpress'),
            ('Sécuriser son site web, cybermalveillance.gouv.fr', 'https://www.cybermalveillance.gouv.fr/'),
        ],
    },
]

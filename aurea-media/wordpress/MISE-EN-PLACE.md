# Mise en place du nouveau site aurea-media.fr

Ce guide vous emmène de votre site actuel au nouveau site, sans perdre votre référencement.
Comptez environ une heure, dont vingt minutes de vérifications. Le nouveau site repart sur une base propre : les anciens articles sont retirés, et chacune de leurs adresses est redirigée vers la bonne page.

Trois fichiers, tous dans ce dossier :

| Fichier | Rôle |
|---|---|
| `aurea-installateur.zip` | Une petite extension (6 Ko) qui installe les deux fichiers suivants même si votre hébergeur limite la taille des envois (erreur « 413 Request Entity Too Large ») |
| `aurea.zip` | Le thème Aurea Media : design de la maquette, pages, blog, réalisations, SEO, formulaire de devis |
| `aurea-studio.zip` | L'extension Aurea Studio (menu à gauche) : réalisations, logos clients, avis Google |

Une extension gratuite à ajouter depuis WordPress : **Rank Math SEO**.

---

## Étape 0 : sauvegarder (10 minutes)

1. Installez l'extension gratuite **UpdraftPlus** (ou utilisez la sauvegarde de votre hébergeur) et faites une sauvegarde complète : fichiers et base de données.
2. Recommandé : faites la mise en place d'abord sur une **copie de préproduction** (la plupart des hébergeurs la proposent en un clic : « staging »). Une fois le résultat validé, vous pousserez la copie en production.
3. Notez vos réglages Rank Math actuels si vous en avez (Rank Math → Tableau de bord → Exporter les réglages).

## Étape 1 : installer le thème et Aurea Studio (5 minutes)

L'erreur « 413 Request Entity Too Large » vient du serveur de votre hébergeur (nginx) : il refuse les fichiers au delà d'une certaine taille, et le thème pèse 12 Mo à cause du film de marque en haute définition. L'extension Aurea Installateur contourne cette limite en envoyant le fichier par petits morceaux.

1. **Extensions → Ajouter → Téléverser une extension** : choisissez `aurea-installateur.zip`, installez, activez.
2. **Outils → Installer Aurea** : choisissez `aurea.zip` et `aurea-studio.zip` (ou déposez les deux fichiers dans le cadre). Les barres de progression avancent, puis WordPress installe chaque fichier.
3. Cliquez sur **Activer** sous Aurea Studio, puis sous le thème Aurea Media.
4. **Extensions → Ajouter** : recherchez « Rank Math SEO », installez, activez. Dans l'assistant de Rank Math, choisissez le mode **Avancé** et le type **Petite entreprise / Entreprise**, nom « Aurea Media ». Les étapes Google peuvent attendre : le thème complète ensuite tous les réglages utiles.
5. Si votre ancien site utilisait une autre extension de SEO (Yoast, All in One SEO…), désactivez la : une seule extension SEO à la fois.
6. Une fois tout installé, vous pouvez supprimer Aurea Installateur (gardez le zip pour les prochaines mises à jour du thème).

Si l'installateur affiche une erreur d'identifiants FTP : ajoutez la ligne `define( 'FS_METHOD', 'direct' );` dans `wp-config.php`, ou demandez à votre hébergeur de le faire.

## Étape 2 : installation en un clic (2 minutes)

**Apparence → Installation Aurea → bouton « Tout installer ».** (Cet écran s'ouvre tout seul juste après l'activation du thème.)

Les pages du site n'existent pas tant que ce bouton n'a pas été cliqué : c'est lui qui les crée. L'installation avance étape par étape, avec un compte rendu à l'écran ; les images sont importées par petits lots pour ne pas dépasser le temps autorisé par votre hébergeur. Si une étape affiche une erreur, cliquez sur « Relancer l'installation » : ce qui est déjà fait n'est pas refait.

Ce bouton enchaîne six étapes, que vous pouvez aussi lancer une par une :

1. **Extensions** : vérifie qu'Aurea Studio et Rank Math sont actifs.
2. **Réalisations et logos** : importe les 23 réalisations et les 18 logos dans Aurea Studio.
3. **Pages** : crée les 21 pages de la maquette, la page Conseils, l'article « Combien coûte un site vitrine en 2026 ? » et l'étude de cas Havel Trading. Chaque page reçoit son titre SEO, sa meta description et son mot clé principal dans Rank Math.
   Une ancienne page qui porte le même nom (par exemple votre page Contact actuelle) est **remplacée** par la nouvelle : son ancienne version reste récupérable dans les révisions de la page. Les pages créées par le thème ne sont plus jamais retouchées ensuite, pour garder vos modifications.
4. **Réglages WordPress** : page d'accueil, page des articles « Conseils », adresses des articles en `/conseils/nom-de-l-article/`, rubriques en `/conseils/categorie/…`, fuseau horaire de Paris, commentaires fermés.
5. **Rank Math** : organisation Aurea Media (logo, image de partage), séparateur « | », pas de schéma « Article » sur les pages, plan du site sans pages auteur ni images jointes, module Redirections activé.
6. **Redirections 301** : 52 anciennes adresses redirigées vers les nouvelles pages (plan de l'audit), visibles et modifiables dans **Rank Math → Redirections**.

Chaque étape peut être relancée sans risque : rien n'est supprimé.

## Étape 3 : base propre (5 minutes)

Toujours dans **Apparence → Installation Aurea**, section **Base propre** :

1. Cliquez sur **Voir ce qui sera retiré**. Un tableau liste tous les anciens articles, les anciennes pages qui ne font pas partie du nouveau site, les rubriques et mots clés devenus vides, et pour chacun l'adresse vers laquelle il sera redirigé : un ancien article « site vitrine plombier » vers la page Artisans et BTP, « prix site vitrine 2025 » vers Tarifs, « Shopify ou WooCommerce » vers la page e-commerce, et ainsi de suite. Un contenu sans sujet reconnu mène au blog (article) ou à l'accueil (page).
2. Si le tableau vous convient, cliquez sur **Mettre à la corbeille et rediriger**.

Ce qui se passe :
* les anciens articles et pages vont à la **corbeille**, où ils restent récupérables 30 jours ;
* chaque ancienne adresse reçoit une **redirection 301** dans Rank Math (votre référencement est transmis aux nouvelles pages) ;
* les anciennes redirections de Rank Math qui envoyaient vers l'accueil sont réorientées vers la page la plus proche ;
* seuls restent l'article livré avec le thème (« Combien coûte un site vitrine en 2026 ? ») et les articles que vous écrirez ensuite, qui ne sont jamais touchés.

## Étape 4 : vos informations (10 minutes)

1. **Réglages → Général → Langue du site : Français**, puis enregistrez (les dates s'afficheront en français).
2. **Apparence → Personnaliser → Aurea Media** : vérifiez téléphone, e-mail, adresse, SIREN, liens LinkedIn et fiche Google. Indiquez l'adresse qui doit recevoir les demandes de devis si elle diffère de l'e-mail affiché.
3. **Aurea Studio → Avis Google** : collez votre clé d'API Google et l'identifiant de votre fiche (la marche à suivre est sur l'écran). En attendant, la note et le nombre d'avis de secours s'affichent.
4. **Pages → Mentions légales** et **Politique de confidentialité** : complétez les passages entre crochets (forme juridique, nom et adresse de l'hébergeur, outil de mesure d'audience éventuel).

## Étape 5 : vérifier (20 minutes)

* **Le site** : parcourez l'accueil, une page service, une page secteur, Réalisations, Conseils, Contact, sur ordinateur et sur téléphone.
* **Le formulaire** : envoyez une demande depuis la page Contact. Vous devez la recevoir par e-mail, et elle apparaît aussi dans le menu **Messages reçus**. Si l'e-mail n'arrive pas, installez l'extension gratuite **WP Mail SMTP** (votre hébergeur bloque peut être l'envoi direct) : les demandes restent de toute façon enregistrées dans Messages reçus.
* **Les redirections** : ouvrez trois anciennes adresses, par exemple `/creation-de-site-vitrine-paris/`, `/site-vitrine-avocat/` et un ancien article `/nom-de-l-article/` : elles doivent mener à la nouvelle page.
* **Rank Math** : ouvrez une page dans l'éditeur, le panneau Rank Math affiche le mot clé et un score. Visez 80 et plus, pas forcément 100.
* **Les fichiers pour Google et les IA** : `/sitemap_index.xml` (plan du site Rank Math), `/robots.txt`, `/llms.txt`.

## Étape 6 : mise en ligne et suivi

1. Si vous avez travaillé sur une préproduction : poussez la copie en production depuis l'interface de votre hébergeur.
2. Vérifiez que **Réglages → Lecture → « Demander aux moteurs de recherche de ne pas indexer ce site »** est bien **décoché** en production.
3. **Google Search Console** : Sitemaps → ajoutez `sitemap_index.xml`. Puis Inspection d'URL → demandez l'indexation de l'accueil, de la page site vitrine et de la page tarifs.
4. Pendant 30 jours, surveillez dans la Search Console les pages en erreur 404 (rapport Pages) et ajoutez une redirection dans Rank Math pour chaque ancienne adresse oubliée.

---

## Au quotidien

| Je veux… | Où |
|---|---|
| Ajouter, masquer ou réordonner une réalisation | **Aurea Studio → Réalisations** (glisser déposer, interrupteurs). Elle apparaît partout : mur de l'accueil, portfolio, pages services et secteurs, page Réalisations |
| Rédiger l'étude de cas d'une réalisation | **Aurea Studio → Fiches complètes** → la réalisation. Tant que le texte est vide, la fiche n'est pas indexée par Google (contenu trop mince) |
| Ajouter ou retirer un logo client | **Aurea Studio → Logos clients** |
| Gérer les avis | **Aurea Studio → Avis Google** (avis Google automatiques, avis ajoutés à la main, note minimale) |
| Publier un article | **Articles → Ajouter** : la composition « Article SEO complet » se place d'office (introduction, bloc « En bref » citable par les IA, intertitres, tableau, encart, questions, appel à l'action). Le sommaire, le temps de lecture, l'auteur et la date de mise à jour s'affichent tout seuls |
| Ajouter un encart, un « En bref » ou des questions dans un article | Bouton **+** → **Compositions** → **Aurea** |
| Modifier un texte d'une page | **Pages** → la page. Les sections de la maquette sont des blocs HTML : cliquez dans le bloc pour modifier le texte, ou sur « Aperçu » pour voir le rendu |
| Changer le téléphone ou l'e-mail du site | **Apparence → Personnaliser → Aurea Media** (en-tête, pied de page, formulaire, données Google). Les numéros déjà présents dans le texte des pages se modifient dans les pages |
| Lire les demandes de devis | Menu **Messages reçus** (une copie de chaque demande, même si l'e-mail s'est perdu) |
| Rejouer une étape d'installation | **Apparence → Installation Aurea** |

## Ce que fait le thème pour le SEO, sans réglage

* Titre, description et mot clé Rank Math préremplis sur chaque page, d'après le plan validé (titres de 50 à 60 caractères, descriptions de 135 à 160).
* Un seul H1 par page, intertitres H2 et H3 structurés, textes alternatifs sur les captures des réalisations, liens internes entre services, secteurs, réalisations et tarifs.
* Données structurées complétées dans le graphe de Rank Math : l'agence (adresse, téléphone, zone desservie, SIREN, profils), le fondateur, un « Service » avec prix de départ sur chaque page service, secteur et locale, le fil d'Ariane, une page « À propos » et une page « Contact », les études de cas et la liste des réalisations. Pas de schéma FAQ ni de note d'avis auto déclarée (sans effet ou ignorés par Google).
* Blocs « En bref » en haut des pages stratégiques : réponses de 130 à 170 mots, datées et signées, faites pour être citées par Google et les moteurs IA.
* `/llms.txt` généré à partir des pages et des articles du site, toujours à jour. `robots.txt` qui autorise explicitement les robots de citation (ChatGPT Search, Claude, Perplexity).
* Pages rapides : polices hébergées sur le site (aucun appel à Google Fonts, conforme au RGPD), styles des blocs chargés seulement s'ils sont utilisés, images en WebP avec dimensions, vidéos chargées seulement à l'écran, pas d'émojis ni de scripts inutiles de WordPress.
* Anciennes adresses d'articles redirigées automatiquement vers `/conseils/…`.

## Bon à savoir

* **Mettre à jour le thème** : Outils → Installer Aurea (réactivez l'installateur si vous l'aviez supprimé) puis choisissez le nouveau `aurea.zip` : la nouvelle version remplace l'ancienne. Vos pages, articles, réalisations et réglages sont dans la base de données : ils ne bougent pas.
* **La maquette reste la référence du design.** Le script `wordpress/outils/exporter_theme.py` régénère le contenu du thème à partir de la maquette quand elle évolue.
* **Corbeille** : après 30 jours, WordPress vide la corbeille tout seul. Les redirections, elles, restent en place.
* **Pages locales** : l'audit prévoyait aussi Boulogne-Billancourt et Clamart. Pour éviter des pages quasi identiques, ces deux adresses sont redirigées vers la page Hauts de Seine, qui cite les deux villes. Une page dédiée pourra être créée le jour où vous aurez des projets et des contenus propres à ces villes.
* **Nouveaux articles** : le blog repart de zéro. Visez deux articles bien faits par mois plutôt que dix. Quand un nouveau guide reprend le sujet d'un ancien article (par exemple la fiche Google Business Profile), modifiez dans Rank Math → Redirections la redirection de l'ancienne adresse pour qu'elle pointe vers ce guide.

## Mettre à jour le thème ou Aurea Studio

1. Ouvre **Outils → Installer Aurea**. Le tableau « Installé en ce moment » indique la version du thème, d'Aurea Studio et de Rank Math.
2. Dépose le nouveau `aurea.zip` (et `aurea-studio.zip` si fourni). Le tableau affiche la nouvelle version une fois la page rechargée.
3. Ne clique jamais sur « Mettre à jour » proposé par WordPress pour un thème nommé « Aurea » dans Apparence → Thèmes ou Tableau de bord → Mises à jour : un autre thème, publié sur wordpress.org sous le même nom de dossier, remplacerait le tien. Depuis la version 2.0.0, le thème, Aurea Studio et l'installateur bloquent cette fausse mise à jour.

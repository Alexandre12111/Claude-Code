# Thème Schiesser 0.14

Thème WordPress sur mesure pour la Confiserie Schiesser, sans constructeur de pages ni extension payante.
Le design de la maquette v3 est reproduit à l'identique dans le code ; tout le contenu se modifie depuis l'administration, sans écrire une ligne de code.

## Version 0.17.3 : photos changées par leur adresse

* Une photo dont l'adresse (imageUrl, avantUrl, apresUrl…) a été changée à la main dans l'éditeur de code, sans changer son numéro de médiathèque (imageId, avantId…), s'affiche désormais aussi sur le site, comme dans l'aperçu de l'éditeur. Le thème retrouve la photo correspondante dans la médiathèque (images adaptées à chaque écran conservées).

## Version 0.17.2 : chaque plat et chaque produit dans la bonne rubrique

* Carte du Tea Room et boutique : une rubrique est reconnue sous ses trois noms (Kaffee, Café, Coffee). Un plat ou un produit rangé dans la rubrique d'une autre langue s'affiche quand même, sous le nom de la rubrique dans la langue de la page : plus de rubrique vide ni de doublon, plus de produit sans catégorie.
* « Réparer les langues » range chaque plat et chaque produit dans la rubrique de sa propre langue, et crée la rubrique traduite si elle manque.

## Version 0.17.1 : rubriques du Tea Room sans doublon

* Carte du Tea Room : chaque langue n'affiche que ses rubriques. Une rubrique restée dans une autre langue (ex. « Kaffee » sur la carte française) rejoint sa traduction (« Café ») au lieu d'apparaître deux fois.
* « Réparer les langues » range aussi chaque plat et chaque produit dans la rubrique de sa propre langue ; l'avis signale ces cas.
* La réparation et l'import des langues ne remplacent plus jamais une photo choisie à la main (plat, produit ou rubrique) ; le contenu des pages n'est pas touché.
* Mise à jour sans risque : installer le nouveau zip ne touche ni aux photos de la médiathèque, ni aux pages, ni aux produits (tout est dans la base de données). Ne cliquez pas sur « Mettre à jour le contenu des pages », qui remet les textes du thème.

## Version 0.17 : textes dans les autres langues, depuis l'éditeur

* En modifiant une page (ex. l'accueil en français), une petite fenêtre « Dans les autres langues » s'affiche en bas à droite de l'éditeur. Cliquez sur une section ou un élément : la fenêtre montre le même texte sur les versions Deutsch et English de la page. Corrigez le texte et cliquez sur « Enregistrer en Deutsch » (ou Ctrl+Entrée) : la version allemande est mise à jour aussitôt, sans ouvrir l'autre page.
* Le texte que vous êtes en train de modifier est choisi automatiquement ; le menu « Texte » permet d'en choisir un autre (titre, texte, boutons, description de la photo…). Les mots entre *étoiles* s'affichent en italique sur le site.
* Si la mise en page d'une version est différente (section ajoutée ou déplacée), la fenêtre le signale et propose d'ouvrir la page.
* Évitez d'avoir la page allemande ouverte dans un autre onglet en même temps : en l'enregistrant, elle remplacerait le texte corrigé depuis la fenêtre.

## Version 0.16 : langues réparées, photo partagée entre les langues

* **Réparer les langues** : si Polylang a pris le français comme langue par défaut, il a marqué tous les produits, plats et pages « Français ». Un avis dans Produits (et le tableau de bord) indique le nombre de contenus par langue ; le bouton « Réparer les langues » retrouve la vraie langue de chacun d’après son nom, relie les versions DE, FR et EN, met les doublons à la corbeille (récupérables) et crée les traductions manquantes. Prix, photos et textes retouchés sont conservés.
* L’import du contenu donne désormais toujours la langue Deutsch au contenu allemand, quelle que soit la langue par défaut de Polylang.
* **Photo partagée** : après l’enregistrement d’une page dont une photo a changé, l’éditeur propose de mettre la même photo sur les autres langues (toutes, une seule, ou aucune). Les textes de chaque langue ne bougent pas.
* Carte Google : le libellé « Découvrir » qui restait figé au survol de la carte a disparu.

## Version 0.15 : photos au naturel et finitions

* Les photos ne sont plus teintées en vert : un voile neutre chocolat, placé seulement sous le texte, garde la lecture confortable et laisse aux pâtisseries leurs vraies couleurs. Le vert sapin reste sur les bandeaux, les sections et les boutons.
* Accueil, « Zwei Etagen, ein Haus » : le lien de chaque carte devient un vrai bouton crème (cible de 44 px), qui passe en vert sapin au survol.
* Savoir faire : moins de vide entre les étapes, étapes en attente plus lisibles.
* Titres équilibrés sur plusieurs lignes, FAQ plus nette au survol, mouvements coupés quand le visiteur demande moins d'animations.
* Mise à jour : installer le nouveau zip suffit, rien à réimporter.

## Version 0.14 : le vert sapin, couleur principale

Trois couleurs, trois rôles : **vert sapin** pour les grandes surfaces et les actions, **chocolat** pour le pied de page et les contrastes, **beige** pour la lecture. Mêmes éléments, mêmes polices, aucune fonction modifiée.

- Sections sombres (chronologie, galerie de la Confiserie, encart et moments du Tea Room, itinéraires, appel final) en **vert sapin**, avec une lumière douce et des cartes intérieures chocolat.
- Bandeau du haut, bandeau « ouvert / fermé », vitrine du jour et chiffres de l'Histoire en vert sapin.
- Étiquettes « N° » pleines, petit filet vert sous chaque titre de section, numéros et flèches en vert.
- Carte du Tea Room : barre des rubriques verte, rubrique active en crème.
- « Weiter im Haus » : bloc vert sapin avec cartes crème qui se soulèvent au survol.
- Questions fréquentes ouvertes, ligne du jour des horaires, graphique d'affluence : vert sapin.
- Retours plus nets au toucher et au clic, défilement doux, contours de focus visibles sur toutes les surfaces.

## Version 0.13 : lumière sapin et nouvelle carte du Tea Room

- **Un seul vert : le vert sapin.** Le vert clair (menthe) est retiré partout (site, éditeur, PDF). Sur les fonds chocolat, le vert sapin apparaît en lumière dans les arrière-plans ; les petits accents passent en sable clair. Un ancien réglage « Menthe » est remplacé automatiquement (Réglages maison → Charte graphique → « Accent clair »).
- **Carte du Tea Room plus pratique** : la barre des rubriques reste visible en haut pendant la lecture, sur une ligne qui défile ; choisir une rubrique ramène au début de sa liste ; en-tête compact avec le nombre de plats ; plats en lignes claires avec prix alignés ; photo, PDF (bouton vert) et suggestion du jour dans la colonne de droite qui suit la lecture.
- **Accueil** : le zoom des photos « Confiserie » et « Tea Room » au survol est de nouveau fluide.
- PDF de la carte régénérés (couverture en lumière sapin), finitions de boutons et de survols.

## Version 0.12 : design modernisé

Même maison, mêmes couleurs (crème, chocolat, vert sapin, menthe), mêmes polices et tous les éléments conservés : seul le dessin change. Aucune fonction ni aucun contenu n'est modifié, il n'y a rien à réimporter.

- **Grandes photos encadrées par l'espace** : coins arrondis, plus de filets ni de coins dorés ; bandeau d'état collé sous la photo.
- **Menu en pastille** (page active en vert sapin), sélecteur de langue assorti, boutons arrondis.
- **En-têtes de section allégés** : une étiquette « N° 01 » au lieu du losange et du long filet, notes en texte droit plus lisible.
- **Cartes et photos arrondies** avec ombres douces : étages, catalogue, galeries, archives, carte du Tea Room, questions fréquentes, grilles pratiques, formulaire.
- **Pied de page et appel final en chocolat**, posés comme des cartes.
- Textes secondaires plus contrastés, section savoir-faire moins haute, rendu téléphone vérifié.

## Version 0.11.1 : typographie épurée et photos d'archives

- **Titres et textes plus petits sur tout le site** : grand titre de l'accueil environ 40 % plus petit, titres de section environ 45 % plus petits, texte courant à 15 px. Mise en page plus aérée et plus lisible. L'accueil occupe un peu moins de hauteur.
- La taille de tous les titres reste réglable dans Réglages maison → Charte graphique (échelle des titres).
- **7 photos d'archives de la maison** intégrées : façade « Patisserie · Leckerly », la maison Schiesser sur le Marktplatz, la vitrine « Seit 1870 », le comptoir, l'atelier (deux photos) et la caisse du salon de thé. Elles illustrent l'accueil (savoir-faire), la Confiserie, le Tea Room et la page Histoire (8 images dans « Aus dem Archiv »), avec textes alternatifs en allemand, français et anglais.
- Pour les mettre en place : Pages → « Mettre à jour le contenu des pages ».

## Nouveautés de la version 0.11 : site en allemand, français et anglais

L'allemand reste la langue principale. Avec l'extension gratuite **Polylang**, le thème fournit les versions **française** et **anglaise** complètes, rédigées à la main (pas de traduction automatique) :

- **8 pages** traduites (textes, boutons, questions fréquentes, textes alternatifs), avec leurs adresses : `/accueil/`, `/boutique/`, `/salon-de-the/`, `/notre-histoire/`, `/nous-visiter/`, `/contact/`, `/cadeaux-entreprise/` en français ; `/home/`, `/confectionery/`, `/tearoom/`, `/history/`, `/visit-us/`, `/contact-us/`, `/corporate-gifts/` en anglais. Les liens internes pointent vers les pages de la même langue.
- **Réglages Rank Math dans chaque langue** : titre SEO de 50 à 60 caractères, description de 140 à 160 caractères, 5 mots-clés (le premier est le principal).
- **70 produits** (noms, accroches, catégories, SEO) et **carte du Tea Room** (78 plats, 8 rubriques, descriptions, mentions) en français et en anglais.
- **Textes du thème** dans la langue de la page : état « Ouvert / Fermé », horaires (« 7 h 30 » en français, « 7:30 » en anglais), jours fériés, allergènes, formulaire, Merkliste, sélection, mentions de prix (« dès CHF 8.90 », « from CHF 8.90 »).
- **Carte du Tea Room en PDF** dans les trois langues (le bouton ouvre celle de la langue de la page).
- Prix, photos et allergènes sont **communs** aux trois langues : modifiés une fois, ils sont recopiés dans les traductions. « Épuisé aujourd'hui » et la suggestion du jour valent pour toutes les langues.

### Mise en place (une fois)

1. Extensions → Ajouter → **Polylang** → Installer, Activer.
2. Langues : créer **Deutsch** (`de_CH`) en premier (langue par défaut), puis **Français** (`fr_FR`) et **English** (`en_GB`). Accepter d'attribuer la langue par défaut aux contenus existants.
3. Langues → Réglages → URL modifiées : « La langue est définie par le nom de dossier » et « Cacher le code de langue pour la langue par défaut ».
4. Pages → bouton **« Mettre à jour le contenu des pages »** : les versions FR et EN sont créées et reliées. Le bouton peut être relancé sans créer de doublon.
5. Langues → Traductions : traduire le slogan du site et, si vous les avez changés, les textes des Réglages maison.
6. Rank Math → Plan du site : vérifier que les trois langues y figurent ; Polylang ajoute les balises `hreflang`.

## Version 0.10.1 : vert sapin

- Les accents dorés laissent la place au **vert sapin** (#174633), couleur secondaire de la maison, avec une touche de chocolat pour les numéros et les filets. Contraste vérifié pour une lecture facile.
- La couleur reste réglable dans Réglages maison → Charte graphique (« Vert maison ») : tous les accents suivent.
- Correction : le zoom des photos au survol (cartes Confiserie et Tea Room de l'accueil, catalogue) est de nouveau progressif.
- La carte du Tea Room en PDF reprend le vert sapin.

## Nouveautés de la version 0.10 : design prestige, carte du Tea Room en PDF, maillage

**Accueil**
- Les **spécialités** restent en tête de l'accueil (Basler Läckerli, Pralinen, Truffes-Cake, Bonbonnière, Marrons glacés, Zopf), même pendant que la liste des produits est « en préparation ». Tant qu'un produit n'a pas sa photo, c'est celle de sa **catégorie** qui s'affiche (Produits boutique → Catégories → « Photo de la catégorie »), et le lien mène à la page Confiserie.

**Tea Room**
- **La carte passe en premier** sur la page Tea Room, juste sous la grande photo.
- **Nouveau design** : carte encadrée de filets dorés, onglets en pastilles numérotées, titre de rubrique centré avec ornement, photo de la rubrique et suggestion de la maison à côté.
- **Carte en PDF** : le bouton « Karte ansehen (PDF) » de la grande photo et le lien « Die ganze Karte als PDF » ouvrent la carte mise en page (couverture et 7 pages A4). Pour la remplacer : Réglages maison → Pied de page → « Carte du Tea Room en PDF ». Une **carte imprimable toujours à jour** (`/?schiesser_karte=druck`) permet de refaire le PDF après une modification des Produits Tea Room : Imprimer → Enregistrer au format PDF.
- Dans un bouton, le lien `#karte-pdf` ouvre toujours le PDF de la carte.

**Design prestige** (tous les blocs restent les mêmes)
- Typographie affinée (titres plus grands et plus fins, notes en italique), dorures sur les filets, numéros et ornements, coins dorés sur la grande photo.
- Titres qui montent mot par mot, photos qui se dévoilent en rideau, léger effet de profondeur sur la grande photo, boutons aimantés avec reflet, barre de lecture dorée, en-tête qui s'affine au défilement.
- Les effets sont désactivés automatiquement pour les visiteurs qui ont choisi « mouvement réduit » ; contrastes vérifiés (0 erreur d'accessibilité).

**SEO**
- Section **« Weiter im Haus »** en bas de chaque page : trois liens vers les autres pages de la maison.
- Davantage de **liens dans les textes** et les questions fréquentes, avec des textes de lien descriptifs.
- **5 mots-clés Rank Math** par page et par produit (le premier reste le mot-clé principal).

### Mise à jour depuis la version 0.9

Remplacer le thème par le nouveau zip, puis cliquer sur **« Mettre à jour le contenu des pages »** sur le tableau de bord (carte du Tea Room en haut, liens, mots-clés). Les réglages SEO que vous avez modifiés à la main dans les fiches produits sont conservés.

## Version 0.9.1 : SEO prêt pour la mise en ligne

- Rapport SEO (`SEO-RAPPORT.md`) entièrement mis à jour pour le site en allemand.
- H1 de la Startseite avec le mot-clé principal (« Confiserie in Basel, seit 1870 am Marktplatz »).
- Fiches produits : titres SEO de 47 à 60 caractères, mot-clé principal égal au nom du produit, données Google avec le prix le plus bas, le plus haut et le nombre de formats.
- Pied de page : colonne « Adresse & Kontakt » (évite deux titres « So finden Sie uns » sur la même page).

## Nouveautés de la version 0.9 : site en allemand, liste de prix et carte du Tea Room

**Langues**
- **Le site public est entièrement en allemand (Suisse)** : pages, boutons, horaires (« 7.30–18.30 Uhr »), jours fériés (Auffahrt, Bundesfeier…), messages du formulaire, filtres d'allergènes, titres pour Google et données structurées (`de-CH`).
- **L'administration reste en français** : menus, écrans, réglages, guide, messages à l'équipe. Les deux langues fonctionnent en même temps, sans extension.
- **Adresses des pages en allemand** : `/confiserie/`, `/tea-room/`, `/geschichte/`, `/besuch/`, `/kontakt/`, `/firmengeschenke/`, `/impressum/`, fiches produits en `/produkte/…`. Les anciennes adresses françaises redirigent automatiquement (301).
- Sur le tableau de bord, un bouton **« Installer la langue allemande »** ajoute le pack de langue de WordPress pour les rares textes de WordPress et des extensions (Rank Math) visibles sur le site.

**Contenu**
- **Liste de prix de la boutique** : 70 produits en 8 rubriques (Läckerli & Basler Spezialitäten, Pralinen & Confiserie, Schokolade, Torten & Patisserie, Aus der Backstube, Salziges & Apéro, Guetzli & Gebäck, Glace), avec leurs formats et prix (« ab CHF … » quand il y a plusieurs formats).
- **Carte du Tea Room** : 78 articles en 8 rubriques (Kaffee, Tee, Heisse Schokolade, Frühstück, Suppen & Hausgemachtes, Dessert & Glace, Kalte Getränke, Wein, Cüpli & Drinks), gérés dans « Produits Tea Room ».
- **Pages réécrites en allemand**, avec l'histoire racontée par la carte (fondation en 1870 par le confiseur glaronais Rudolf Schiesser, façade néogothique et Tea Room de son fils Hans) et deux photos d'archives (façade en 1889, Marktplatz vers 1900).

**Liste des produits « en préparation »**
- Tant que les photos des produits ne sont pas prêtes, la boutique affiche **« Produktliste folgt in Kürze »** avec un aperçu des rubriques, un bouton d'appel et un lien vers les horaires. Les fiches produits ne sont pas encore publiques (ni pour les visiteurs, ni pour Google).
- Quand tout est prêt : **Produits boutique → Mise en ligne → « Tout est prêt : afficher la liste des produits »**. Un clic, et la liste complète apparaît. Le bouton inverse remet la liste en préparation. Le rappel est aussi dans la barre noire du haut (« Liste des produits : en préparation »).

## Nouveautés de la version 0.8 : modifier plus vite, commander par téléphone

**Pour l'équipe**
- **Écran « Aujourd'hui »** (premier menu de l'administration, pratique sur téléphone) : état ouvert ou fermé, nouveaux messages, vitrine du jour, suggestion du Tea Room, produits « épuisés aujourd'hui » et bandeau d'annonce, enregistrés d'un seul clic. Les produits cochés « épuisés » redeviennent disponibles tout seuls le lendemain.
- **Sections programmées** : sur chaque bloc, panneau de droite « Affichage programmé », afficher du … au …. La section apparaît et disparaît toute seule ; dans l'éditeur, elle est signalée par un cadre pointillé et une étiquette.
- **« Modifier cette section »** : une fois connecté, un bouton apparaît au survol de chaque section du site et ouvre l'éditeur directement sur ce bloc.
- **Point d'intérêt des photos** : dans la médiathèque (ou le panneau « Photo » d'un bloc), un clic sur l'endroit important de la photo. Les recadrages (téléphone, cartes, grande photo) le gardent visible, partout où la photo est utilisée.
- **Menu épuré pour le rôle « Gérant(e) du site »** : Aujourd'hui, Pages, Produits boutique, Produits Tea Room, Messages reçus, Avis clients, Réglages maison. Le gérant arrive sur « Aujourd'hui » après la connexion ; médiathèque, menus du site et guide restent accessibles par les liens en bas de cet écran.

**Pour les clients**
- **« Appeler pour commander »** sur les pages produits, la fiche rapide et la barre du téléphone : ouvert, le bouton appelle la boutique ; fermé, il affiche « Fermé · ouvre demain à 7 h 30 » et propose « Laisser un message », avec le formulaire déjà rempli du nom du produit.
- **« Ma sélection »** : un cœur pour mettre des produits de côté, avec les quantités. La liste s'ouvre depuis un bouton flottant : « Appeler pour commander », « Envoyer par message » (formulaire prérempli avec la liste), « Copier la liste ». Gardée dans le navigateur du visiteur, sans compte ni cookie.
- **Recherche dans la boutique**, aussi par ingrédient (« praliné », « kirsch »), accents ignorés.
- **« Épuisé aujourd'hui »** affiché sur la carte du produit, sa page et dans la carte du salon.
- **Partage** : WhatsApp, e-mail, copier le lien (et le partage du téléphone quand il existe), sur les produits et la carte du salon.
- **Navigation plus fluide** : fondu entre les pages et pages suivantes préparées à l'avance (navigateurs récents ; les autres naviguent comme avant).

## Nouveautés de la version 0.7 : horaires, allergènes et sécurité

- **Jours fériés de Bâle calculés tout seuls**, chaque année : Nouvel An, Fasnacht (lundi, mardi, mercredi), Vendredi saint, Pâques, Lundi de Pâques, 1er mai, Ascension, Pentecôte, Lundi de Pentecôte, 1er août, Veille de Noël, Noël, Saint-Étienne, Saint-Sylvestre. Pour chacun : horaires habituels, fermé ou horaires spéciaux (Réglages maison → Coordonnées et horaires). Les réglages proposés sont une base à vérifier avec la maison.
- **Dates exceptionnelles** : vacances, inventaire, horaires réduits, avec un motif affiché sur le site. Les périodes passées s'effacent toutes seules.
- Tout le site en tient compte : statut « Ouvert / Fermé » en direct, bandeau du haut, compte à rebours, « Jours particuliers à venir » sous les tableaux d'horaires, et horaires spéciaux transmis à Google (données structurées).
- **Bandeau d'annonce programmable** (Réglages maison → Accueil et pied de page) : message, lien, dates de début et de fin, couleur. Il apparaît et disparaît tout seul ; le visiteur peut le masquer. Option : annonce automatique des jours fériés et fermetures 7 jours avant.
- **Barre du téléphone** : « Appeler », « Itinéraire » et « Ouvert jusqu'à 18 h 30 » (ou « Fermé · ouvre demain à 8 h ») fixés en bas de l'écran. Désactivable ; absente des fiches produits, qui ont leur propre barre.
- **Allergènes et régimes** : boîte « Allergènes et régimes » dans chaque produit et chaque plat du Tea Room (14 allergènes à déclarer, alcool, végétarien, végane, sans gluten, sans lactose, traces possibles). Pictogrammes sur la page du produit, dans la fiche rapide et dans la carte du salon ; filtres « Sans gluten », « Sans fruits à coque », « Végane »… sur la boutique et la carte. Un produit non renseigné est masqué dès qu'un filtre est actif, pour ne jamais promettre « sans » par erreur. Remarque générale modifiable dans Réglages maison.
- **Messages reçus** : chaque message du formulaire est gardé dans l'administration (Nouveau, Lu, Traité), avec un bouton « Répondre ». Aucun message perdu si l'e-mail ne part pas. Effacement automatique après 12 mois (protection des données) : pensez à le mentionner dans la politique de confidentialité.
- **Anti spam renforcé**, sans captcha : champ piège, délai minimum, geste du visiteur obligatoire, 5 envois par heure au plus. Les envois suspects sont classés « Spam probable » (visibles, sans e-mail) plutôt que perdus.
- **Connexion protégée** : 5 mots de passe faux en 15 minutes bloquent la connexion depuis cette adresse pendant 15 minutes ; messages d'erreur neutres ; liste des comptes cachée ; XML-RPC fermé ; en-têtes de sécurité ; version de WordPress masquée.
- **Affichage plus rapide** : la grande photo du haut de page est préchargée dès le début du chargement (les polices l'étaient déjà).

## Nouveautés de la version 0.6 : mise en page sans code et avis Google

- **Styles de texte enregistrés** : dans le panneau **Aa**, « + Enregistrer ces réglages comme nouveau style » (ex. « Accroche verte », « Petit label »). Le style s'applique ensuite en un clic dans n'importe quel texte du site. « Mettre à jour » modifie tous les textes qui l'utilisent, partout ; « Supprimer » les ramène à leur apparence normale. Deux styles d'exemple sont fournis.
- **Taille sur téléphone** : dans la rubrique « Taille » du panneau **Aa**, les icônes ordinateur et téléphone. En mode téléphone, l'aperçu de l'éditeur passe en largeur mobile et la taille réglée ne vaut que sur les petits écrans (moins de 760 px).
- **Espacements des sections** : panneau de droite → « Espacements », un curseur pour l'espace au-dessus et un pour l'espace en dessous (0 à 320 px). « Automatique » garde l'espace prévu ; sur téléphone, l'espace est réduit proportionnellement.
- **Boutons d'une section** : panneau de droite → « Boutons » : couleur du bouton, couleur du texte, taille (petit, normal, grand), arrondi des coins. Disponible sur la grande photo, les sections libres, l'appel final, Nous trouver, Passer nous voir, Plan et horaires, Cartes numérotées, Trajets, Formulaire et Avis.
- **Copier une section vers une autre page** : menu **⋮** du bloc → « Copier vers une autre page… », choix de la page et de l'emplacement (début ou fin). La copie garde textes, photos et réglages.
- **Avis Google** : nouveau bloc « Avis clients (Google) » avec la note moyenne, les étoiles, quelques avis et les boutons « Voir tous les avis » et « Laisser un avis ». Les avis viennent de la fiche Google (clé Google Places API et identifiant du lieu dans Réglages maison → Fiche Google et SEO, mise à jour toutes les 12 heures), ou du nouveau menu « Avis clients » pour les saisir à la main. Si Google ne répond pas, le site affiche les avis saisis à la main. Réglages du bloc : nombre d'avis, note minimale, longueur des textes. Aucune donnée structurée « avis » n'est ajoutée : Google ignore les avis qu'une entreprise publie sur son propre site.

## Nouveautés de la version 0.5 : modifier les textes comme dans Elementor

- **Panneau texte « Aa »** : on clique dans un texte, puis bouton **Aa** de la barre d'outils. Un panneau s'ouvre : police (titres ou texte de la charte), taille sans limite de pas (curseur de 30 à 400 %, boutons − et +, saisie directe, tailles rapides S, M, L, XL, XXL), graisse (fin, normal, moyen, gras), italique, majuscules, souligné, espacement des lettres, couleur (charte ou couleur libre), copier et coller le style, tout remettre par défaut. Le texte change en direct. Sans sélection, le réglage vaut pour tout le champ ; avec une sélection, seulement pour ces mots. Fonctionne dans tous les textes : blocs de la maquette, grande photo, paragraphes et titres. Pour un paragraphe entier, le panneau de droite propose aussi police, taille, graisse et couleur.
- **Les tailles ne sont plus limitées à un cran** : chaque clic sur − ou + réduit ou agrandit de 10 %, autant de fois que l'on veut, et la valeur peut se taper directement. Les tailles sont relatives (100 % = taille normale), donc elles restent proportionnées sur téléphone.
- Les textes mis en forme avec la version 0.4 (Plus petit, Plus grand, Majuscules espacées…) restent tels quels et se retouchent dans le nouveau panneau.

## Nouveautés de la version 0.4

- **Deux menus de produits** dans l'administration : « Produits boutique » (inchangé) et **« Produits Tea Room »**, la carte du salon de thé : nom, prix, description, mention (Signature, En saison…), rubrique, photo, et une case « Suggestion du jour ». Les rubriques (onglets de la carte) ont leur photo et leur ordre. La page Salon de thé affiche automatiquement cette carte, et Google reçoit la carte avec ses prix (données structurées « Menu »).
- **Typographie et couleur au clic** : bouton **Aa** de la barre d'outils, dans tous les textes (remplacé en 0.5 par un panneau complet, voir plus haut).
- **Cartes Google Maps** : les cartes de l'accueil et de la page Nous visiter montrent la fiche Google de la confiserie (sans clé ni compte). Une vue précise peut être collée depuis Google Maps (« Intégrer une carte »), et la carte peut ne s'afficher qu'après un clic (protection des données). OpenStreetMap et CARTO restent disponibles dans chaque bloc.
- **« Assombrir la photo »** : un curseur pour chaque photo (grande photo du haut comprise), pour que le texte posé dessus ressorte mieux.
- **Accroches plus lisibles** : la phrase d'accroche du bloc Introduction passe dans la police du texte, en plus petit (le style italique de la maquette reste disponible dans le bloc).
- **CSS personnalisé** : Réglages maison → Avancé, pour les retouches de style, conservé lors des mises à jour.
- **Adresse par défaut** alignée sur les annuaires : Marktplatz 19, 4051 Basel (à confirmer avec la fiche Google).
- Corrigé : le badge « Aujourd'hui » des horaires ne chevauche plus l'heure sur téléphone.

## Nouveautés de la version 0.3 : la maquette, section par section

- **Les pages suivent la maquette v3 à l'identique** : chaque section de la maquette est devenue un bloc de la catégorie « Schiesser » (31 blocs, liste plus bas), avec le même balisage, les mêmes styles et les mêmes animations. Les six pages (Accueil, La boutique, Salon de thé, Notre histoire, Nous visiter, Contact) et la page « Cadeaux d’entreprise » sont recomposées dans l'ordre de la maquette.
- **Tout reste modifiable sans code** : on clique sur un texte pour l'écrire, sur une photo pour la changer, sur le **+** d'un bloc pour ajouter un élément (une date, un plat, une question, un palier…). Les parties animées sur le site (montée à l'étage, savoir-faire, moments de la journée) s'affichent en fiches dans l'éditeur.
- **Bandeau « Ouvert / Fermé » en direct** sous la grande photo, calculé avec les horaires des Réglages maison et le fuseau de Bâle.
- **Carte interactive** (Leaflet, incluse dans le thème) sur l'accueil et la page Nous visiter, avec la fiche de la maison, les boutons de zoom et un lien Google Maps de secours.
- **Formulaire de contact intégré** (page Contact), sans extension : protection anti-robots sans captcha, message envoyé à l'adresse e-mail des Réglages maison.
- **Pied de page** : nouvelle colonne « Sur cette page » créée automatiquement avec les sections qui ont une ancre, comme dans la maquette.
- **Produits** : nouveau champ « Accroche » (la ligne sous le nom dans le catalogue de l'accueil) ; le catalogue de l'accueil permet de choisir les produits affichés et leur ordre.
- **Sélecteur de langue** FR · DE · EN de la maquette dans l'en-tête, dès que Polylang gère une deuxième langue.
- **SEO conservé** : titres SEO, descriptions, mots-clés, H1, textes, questions fréquentes, liens internes et données structurées de la version 0.2.1 sont repris à l'identique.
- **Vérifié** : 0 bloc invalide dans l'éditeur, 0 erreur d'accessibilité (outil axe, ordinateur et mobile), aucun débordement horizontal de 320 à 1440 pixels de large, aucune erreur JavaScript, toutes les interactions testées dans un navigateur.

## Nouveautés de la version 0.2

- **Charte graphique modifiable** : 8 couleurs et 2 polices (titres, texte) dans Réglages maison → Charte graphique, avec alerte de lisibilité.
- **Sections libres** : bloc « Section » (fond, largeur, en-tête N° / titre / note) dans lequel on ajoute textes, images, colonnes, boutons, questions fréquentes, vidéo ou code HTML.
- **Styles maison** pour les blocs WordPress : boutons (vert, contour, crème, lien fléché), chapeau, surtitre, grille à filets, cartes, cadre vitrine, losanges, grande citation.
- **Compositions** : 10 sections toutes prêtes et 2 modèles de page (outil d'ajout → Compositions → Schiesser).
- **Une page par produit** (/produits/nom-du-produit/), avec photo, fiche, prix, présentation détaillée et produits liés.
- **SEO complet** : intégration Rank Math, données structurées (établissement, fil d'Ariane, produits, liste de produits, questions), titres et descriptions optimisés pour chaque page du contenu de démonstration.
- **Prêt pour WooCommerce** : transfert des produits en un clic, redirections automatiques, panier dans l'en-tête, pages boutique aux couleurs de la maison.
- **Administration repensée** : réglages en onglets, tableau de bord avec raccourcis, Guide du site, page de connexion à la marque, rôle « Gérant(e) du site ».
- **Optimisation SEO complète (0.2.1)** : textes réécrits après un audit en six volets, fiches produits enrichies, nouvelle page « Cadeaux d’entreprise », menu de pied de page, allergènes précisés. Détail dans `SEO-RAPPORT.md`.
- **Commande facilitée** : bouton « Commander » avec un e-mail prérempli, et sur mobile une barre de commande fixe sur chaque fiche produit.
- **Typographie française** : espaces insécables automatiques avant « : ; ? ! » et dans les heures.

## Installation

### Première installation (site de test)

1. **Apparence → Thèmes → Ajouter → Téléverser un thème**, choisir `schiesser.zip`, puis **Activer**.
2. **Réglages → Général** : Langue du site « Français » (l'administration ; le site public s'affiche de toute façon en allemand), Fuseau horaire « Zurich », Titre du site « Confiserie Schiesser », puis Enregistrer. Ces trois réglages sont lus par Google et par le bandeau « Ouvert / Fermé ».
3. Sur le tableau de bord, cliquer sur **Importer le contenu de démonstration** (70 produits de la liste de prix, 78 articles du Tea Room, 7 pages en allemand, menus de l'en-tête et du pied de page, réglages SEO ; les photos sont téléchargées depuis Unsplash, comptez une minute).
4. **Réglages → Permaliens** : vérifier que « Titre de la publication » est sélectionné, puis Enregistrer.

### Mise à jour depuis la version 0.8

1. Remplacer le thème par le nouveau zip.
2. Sur le tableau de bord, cliquer sur **« Passer le site en allemand »** : le texte des 7 pages est remplacé par la version allemande, la liste de prix et la carte du Tea Room sont importées, les anciens produits de démonstration en français partent à la corbeille (récupérables). Les photos de la médiathèque restent.
3. **Réglages → Permaliens** : cliquer sur Enregistrer (nouvelle adresse `/produkte/`).
4. Si vous aviez modifié les textes « Présentation », « Mention » ou la vitrine dans Réglages maison, les réécrire en allemand.

### Mise à jour depuis la version 0.7

Remplacer le thème par le nouveau zip. Rien d'autre à faire. Pour que l'équipe profite du menu épuré, donnez lui le rôle « Gérant(e) du site » (Comptes → modifier le compte → Rôle).

### Mise à jour depuis la version 0.6

Remplacer le thème par le nouveau zip. Les jours fériés fonctionnent aussitôt avec les réglages proposés. À vérifier ensuite : les jours fériés (onglet Coordonnées et horaires) et les allergènes de chaque produit. Si le site utilise une application ou un service qui passe par XML-RPC (rare), le signaler : il est désormais fermé.

### Mise à jour depuis la version 0.5

Remplacer le thème par le nouveau zip (Apparence → Thèmes → Ajouter → Téléverser, puis « Remplacer la version installée »). Les nouveaux réglages apparaissent d'eux-mêmes. Pour les avis automatiques, saisir la clé Google et l'identifiant du lieu (Réglages maison → Fiche Google et SEO → Avis Google).

### Mise à jour depuis la version 0.4

Remplacer le thème par le nouveau zip (Apparence → Thèmes → Ajouter → Téléverser, puis « Remplacer la version installée »). Rien d’autre à faire : le bouton **Aa** ouvre désormais le nouveau panneau.

### Mise à jour depuis la version 0.3

1. **Apparence → Thèmes → Ajouter → Téléverser un thème**, choisir le nouveau `schiesser.zip`, puis **Remplacer la version installée**. Les pages, produits, photos, menus et réglages ne sont pas touchés.
2. Sur le tableau de bord, cliquer sur **Transférer la carte dans Produits Tea Room** : les rubriques et les plats du bloc « Carte du salon » deviennent des produits du Tea Room (mêmes textes, prix et photos). Sans ce clic, la carte reste saisie dans la page, comme avant.
3. Les cartes passent automatiquement sur Google Maps, et le nouveau bouton **Aa** est disponible dans l'éditeur.
4. Dans **Réglages maison → Coordonnées**, vérifier l'adresse (« Marktplatz 19 », code postal « 4051 » selon les annuaires) pour qu'elle soit identique à la fiche Google.

### Mise à jour depuis une version plus ancienne (0.1 ou 0.2)

1. **Apparence → Thèmes → Ajouter → Téléverser un thème**, choisir le nouveau `schiesser.zip`, puis **Remplacer la version installée**.
2. Sur le tableau de bord, cliquer sur **Mettre à jour le contenu de démonstration**. Les pages sont alors recomposées avec les blocs de la maquette et la carte du Tea Room est créée. Attention : cela remplace le contenu des pages et des produits de démonstration. Sur un site de test, c'est ce que l'on veut ; si des textes ont déjà été retouchés, notez-les avant.
3. Les anciennes adresses redirigent automatiquement vers les nouvelles : `/tea-room/` vers `/salon-de-the/`, et les produits renommés (par exemple `/produits/truffes-maison/` vers `/produits/truffes-au-chocolat/`).

Prérequis : WordPress 6.4 ou plus récent (conseillé : la dernière version), PHP 7.4 ou plus récent.
Elementor n'est pas utilisé : il peut être désactivé. **Code Snippets** peut rester installé : c'est l'endroit prévu pour les petits ajouts de code (voir « Faire évoluer le site »).

## Extensions

| Extension | Quand | Pourquoi |
|---|---|---|
| **Rank Math SEO** (gratuit) | Maintenant | Titres, descriptions, mots-clés, plan du site, redirections. Le thème le complète automatiquement. |
| **Une extension d'envoi d'e-mails** (par exemple FluentSMTP ou WP Mail SMTP, gratuites) | Avant d'utiliser le formulaire de contact | Les messages du formulaire partent alors par la messagerie de la maison : sans elle, certains hébergeurs bloquent les envois ou les messages arrivent en courrier indésirable. |
| **WooCommerce** (gratuit) | Le jour où la vente en ligne démarre | Panier, paiement, stock. Tout est déjà préparé dans le thème. |
| **Code Snippets** (gratuit) | Quand un petit ajout de code vous est fourni | Ajouter une fonctionnalité sans toucher au thème ; conservé lors des mises à jour. |
| Une extension de cache (selon l'hébergeur) | À la mise en ligne | Vitesse d'affichage. |
| Une extension de sauvegarde (par exemple UpdraftPlus) | À la mise en ligne | Sauvegardes automatiques. |
| **Polylang** (gratuit) | Pour le français et l'anglais | Versions linguistiques fournies par le thème ; le sélecteur DE · FR · EN apparaît dans l'en-tête (voir la version 0.11). |

Aucune extension de formulaire n'est nécessaire : le formulaire de la maquette est intégré au thème (bloc « Formulaire de contact »).

## Ce que le client modifie, et où

| Contenu | Où | Effet |
|---|---|---|
| Téléphone, e-mail, adresse, horaires, vitrine du jour, pied de page, réseaux sociaux | **Réglages maison** | Mis à jour partout : bandeaux « Ouvert / Fermé », fiches d'adresse, tableau des horaires, pied de page, formulaire de contact, fiche Google |
| Position (latitude, longitude) | **Réglages maison → Fiche Google et SEO** | Données pour Google ; cartes OpenStreetMap et CARTO |
| Fiche Google (type d'établissement, année de fondation, canton, raison sociale, numéro IDE, profils officiels) | **Réglages maison → Fiche Google et SEO** | Données structurées lues par Google et les moteurs d'IA |
| Produits de la boutique (nom, photo, catégorie, prix, badge, accroche, fiche, présentation) | **Produits boutique** | Catalogue de l'accueil, grille de la boutique, fiche rapide et page du produit |
| Ordre des produits | **Produits boutique → Modifier → Ordre d'affichage** | 1 = premier |
| Carte du salon de thé (produits, prix, descriptions, mentions, suggestion du jour) | **Produits Tea Room** | Carte en onglets de la page Salon de thé, données « Menu » pour Google |
| Onglets de la carte (nom, grande photo, ordre) | **Produits Tea Room → Rubriques** | Onglets et photo de la carte |
| Police, taille, couleur d'un mot ou d'une phrase | Cliquer dans le texte (ou sélectionner des mots), puis bouton **Aa** | Tout le champ, ou uniquement les mots sélectionnés ; « Tout remettre par défaut » pour revenir au style d'origine |
| Photo plus sombre (texte plus lisible) | Panneau de droite du bloc → **Photo** → « Assombrir la photo » | Uniquement cette photo |
| Carte Google Maps | **Réglages maison → Coordonnées** (lien, carte, affichage après un clic) ; « Fond de carte » dans le bloc | Cartes de l'accueil et de la page Nous visiter |
| Produits du catalogue de l'accueil | Bloc **Catalogue des créations** → panneau de droite → « Choisir les produits un par un » | Seuls ces produits, dans cet ordre (sinon : les premiers du menu Produits) |
| Catégories (filtres de la boutique) | **Produits → Catégories** | Les filtres apparaissent automatiquement |
| Textes, photos et sections des pages | **Pages → Modifier** | Clic direct sur le texte ; clic sur une photo (ou bouton « Photo » de la barre du bloc) ; bouton + pour ajouter un élément ou une section |
| Fond, numéro, ancre d'une section | Panneau de droite du bloc | L'ancre crée aussi le lien « Sur cette page » du pied de page |
| Affluence par jour et par heure | Page **Nous visiter** → bloc **Affluence** → panneau de droite | Graphique, heures calmes et animées calculées automatiquement |
| Style d'un bouton, d'un paragraphe, d'une image | Panneau de droite → onglet **Styles** | Uniquement des styles aux couleurs de la maison |
| Couleurs et polices du site | **Réglages maison → Charte graphique** (administrateur) | Tout le site et l'éditeur suivent |
| Code sur toutes les pages (statistiques, pixel) | **Réglages maison → Avancé** (administrateur) | Ajouté dans l'en-tête ou en fin de page |
| Code sur une seule page (widget de réservation, avis) | Bloc **HTML personnalisé** dans une Section | Le reste de la page reste modifiable normalement |
| Menus | **Apparence → Menus** | « Menu principal » pour l'en-tête, « Menu du pied de page » pour la colonne Explorer (sans lui, le pied de page reprend le menu principal) |

Un **Guide du site** pas à pas se trouve dans le menu Réglages maison (fiche « Les blocs de la maquette »).

## Blocs de la catégorie « Schiesser »

### Blocs de la maquette

Chaque bloc reproduit une section de la maquette. Les éléments (dates, plats, questions…) s'ajoutent avec le **+** du bloc, les réglages (fond, numéro N°, ancre, liens, photos et textes alternatifs) sont dans le panneau de droite.

| Page de la maquette | Blocs |
|---|---|
| Toutes les pages | **Bandeau « ouvert / fermé »** (état en direct et deux informations), **Séparateur losanges**, **Questions fréquentes** (aussi transmises à Google), **Introduction** (accroche en italique et texte), **Appel final** (section sombre et deux boutons) |
| Accueil | **Vitrine du jour**, **Catalogue des créations** (aperçu photo au survol), **Deux étages** (cartes photo 0 et 1), **Savoir-faire** (la photo change au défilement), **Ligne du temps** (cartes à faire glisser), **Nous trouver** (carte et adresse) |
| La boutique | **Grille des produits**, **Galerie** (visionneuse avec vignettes), **Bon à savoir** (pictogrammes), **Cartes numérotées** (« Passer commande ») |
| Salon de thé | **Montée à l’étage** (scène au défilement), **Encart d’exception** (« La doyenne »), **Carte du salon** (onglets, prix et suggestion du jour, tirés du menu « Produits Tea Room »), **Panneaux photo**, **Moments de la journée**, **Passer nous voir** |
| Notre histoire | **Chiffres clés**, **Plaque anniversaire**, **Récit avec lettrine** (et citation en exergue), **Ligne du temps** en mode chronologie à onglets, **Archives** (agrandies au clic), **Hier et aujourd’hui** (comparateur à curseur), **Cartes numérotées** (« Principes ») |
| Nous visiter | **Plan et horaires**, **Affluence**, **Composez votre trajet** (départs et étapes), **Grande photo légendée** (« Reconnaître la maison ») |
| Contact | **Formulaire de contact**, **Fiche de contact** |
| Où vous voulez | **Avis clients (Google)** : note moyenne, étoiles et avis |

Les lignes d'information (adresse, horaires, téléphone, e-mail) peuvent se remplir **automatiquement** depuis les Réglages maison : panneau de droite de la ligne → liste « Contenu ».

### Blocs de mise en page

- **Hero (grande photo)** : surtitre, titre (H1 de la page, italique possible), texte, deux boutons avec leur style, photo et texte alternatif, « Assombrir la photo », hauteur, teinte de la photo (sépia pour l'histoire), barre de progression de lecture, couleur des mots en italique, sceau tournant. Protégé contre la suppression.
- **Section** : fond (papier, clair, crème, sable, chocolat), largeur (large ou lecture), en-tête facultatif, ancre pour les liens directs, contenu libre.
- **Grille des produits** : titre et note modifiables, nombre de produits, filtres, source (produits du site ou WooCommerce), bouton « Voir toute la boutique ».

Codes courts utilisables partout (bloc « Code court » ou dans un paragraphe). Ils se mettent à jour tout seuls quand les Réglages maison changent :

| Code court | Affiche |
|---|---|
| `[schiesser_horaires]` | Le tableau des horaires, jour courant mis en avant |
| `[schiesser_horaires_phrase]` | Les horaires dans une phrase : « du lundi au vendredi de 7 h 30 à 18 h 30, … » |
| `[schiesser_statut]` | « Ouvert » ou « Fermé » en direct |
| `[schiesser_adresse]` | L'adresse sur deux lignes |
| `[schiesser_telephone]` | Le numéro, cliquable |
| `[schiesser_email]` | L'adresse e-mail, cliquable |
| `[schiesser_itineraire texte="Itinéraire"]` | Un bouton vers Google Maps |

## Carte, formulaire et vie privée (nLPD)

- **Carte Google Maps** (par défaut) : la carte intégrée montre la fiche Google de la confiserie, trouvée d'après le nom et l'adresse des Réglages maison ; elle ne demande ni clé ni compte Google. Pour une vue précise, collez le code « Intégrer une carte » de Google Maps dans **Réglages maison → Coordonnées → Carte Google Maps du site**. La carte se charge quand le visiteur arrive à sa hauteur ; à ce moment, son navigateur contacte Google (adresse IP transmise, cookies possibles) : à mentionner dans la politique de confidentialité. L'option **« Afficher la carte Google seulement après un clic »** évite tout envoi à Google avant l'accord du visiteur.
- **Lien Google Maps** : le lien « Partager » de la fiche Google Maps est le meilleur. Un lien de recherche Google (celui de la barre d'adresse quand la fiche s'affiche dans les résultats) est accepté : le thème le transforme en lien Google Maps propre, sans les paramètres de suivi.
- **OpenStreetMap et CARTO** (au choix, « Fond de carte » dans le bloc) : la bibliothèque Leaflet, incluse dans le thème, ne se charge que si l'une de ces cartes est affichée. OpenStreetMap est gratuit pour un usage raisonnable, avec la mention des sources affichée sur la carte. CARTO est gratuit dans certaines limites : vérifiez ses conditions pour un site commercial. Là aussi, l'adresse IP du visiteur est transmise au serveur de la carte.
- **Formulaire de contact** : les messages ne sont pas enregistrés dans le site, ils sont envoyés par e-mail à l'adresse des Réglages maison (réponse possible directement au visiteur). Protection sans captcha ni service extérieur : champ piège invisible, délai minimal avant l'envoi, un message par minute depuis une même connexion. À mentionner dans la politique de confidentialité.
- **Polices** : Bodoni Moda et Inter sont hébergées dans le thème, aucun appel à Google Fonts tant que la charte d'origine est conservée.

## Faire évoluer le site sans renvoyer tout le thème

Le thème ne contient que la mise en page et les fonctionnalités. **Tout le contenu est dans la base de données** : pages, produits de la boutique et du Tea Room, photos, menus, Réglages maison, réglages Rank Math. Remplacer le thème par une nouvelle version (« Remplacer la version installée ») ne touche donc à rien de tout cela. Seul le bouton « Mettre à jour le contenu de démonstration » remplace des pages : il n'est utile que sur un site de test.

Pour les ajouts futurs, trois façons de faire, de la plus légère à la plus complète :

| Besoin | Comment | Conservé lors d'une mise à jour du thème |
|---|---|---|
| Retouche de style (couleur, espacement, taille d'un élément) | Quelques lignes de CSS dans **Réglages maison → Avancé → CSS personnalisé** | Oui |
| Petite fonctionnalité (code court, champ, redirection, ajout dans une page) | Un extrait de code PHP dans l'extension gratuite **Code Snippets** : Extraits → Ajouter, coller, « Enregistrer et activer ». En cas d'erreur, Code Snippets désactive l'extrait tout seul. | Oui |
| Outil externe sur toutes les pages (statistiques, pixel) | **Réglages maison → Avancé → Code dans l'en-tête** | Oui |
| Nouveau bloc, nouvelle page type, changement de mise en page | Nouvelle version du thème (`schiesser.zip`), installée en trois clics | Le contenu reste en place |

Règle d'or : ne jamais modifier les fichiers du thème directement (Apparence → Éditeur de fichiers) : ces modifications seraient perdues à la mise à jour suivante. Tout ajout passe par l'un des trois emplacements ci-dessus.

## Rank Math : réglages conseillés

1. Installer et activer **Rank Math SEO**, suivre l'assistant en mode « Facile ».
2. Type de site : **Petite entreprise** ; nom : Confiserie Schiesser ; logo : le logo de la maison.
3. Inutile de ressaisir l'adresse et les horaires dans Rank Math : le thème ajoute automatiquement les informations des Réglages maison à la fiche de Rank Math.
4. **Plan du site** : inclure les Pages et les Produits ; exclure les Articles s'il n'y a pas de blog. Tant que la liste des produits est « en préparation », le thème retire lui-même les produits du plan du site.
   Ne pas ressaisir l'adresse et les horaires dans le module SEO local de Rank Math : le thème les fournit déjà.
5. Activer le module **Redirections** : chaque changement d'adresse d'une page créera une redirection.
6. Dans chaque page, le panneau Rank Math affiche déjà le **mot-clé principal**, le **titre SEO** et la **méta description** préparés. Les textes des blocs de la maquette sont transmis à l'analyse de Rank Math. Viser un score de 80 ou plus, sans chercher 100 à tout prix.
7. Après la mise en ligne : relier **Google Search Console** et envoyer le plan du site.

Détails, mots-clés par page et recommandations de l'audit : voir `SEO-RAPPORT.md`.

## WooCommerce : le jour où la vente en ligne démarre

1. Extensions → Ajouter → **WooCommerce** → Installer, puis Activer. WooCommerce reprend la page « La boutique » existante.
2. WooCommerce → Réglages : devise franc suisse (CHF), adresse, TVA, livraison ; paiements (TWINT et carte via Payrexx ou Stripe, retrait en boutique).
3. **Réglages maison → Boutique en ligne → Transférer les produits** : chaque produit devient un produit WooCommerce (nom, photo, prix, catégorie, accroche, fiche, titres SEO). Les anciennes adresses `/produits/…` redirigent vers les nouvelles fiches, et le catalogue de l'accueil affiche les fiches WooCommerce. Le transfert peut être annulé depuis le même écran.
4. Vérifier les prix (les produits « Sur commande » n'ont pas de prix en ligne), puis tester une commande.

La grille de la page Boutique affiche alors les produits WooCommerce avec le bouton « Ajouter au panier », et un panier apparaît dans l'en-tête.
Ce parcours a été testé ici avec une imitation de WooCommerce (l'extension ne peut pas être téléchargée dans l'environnement de développement) : à valider sur le site de test.

## Comptes et droits

- **Administrateur** : tout, y compris Charte graphique, code personnalisé, extensions.
- **Gérant(e) du site** (rôle créé par le thème) : pages, produits, photos, menu, Réglages maison (coordonnées, horaires, accueil, fiche Google). Pas d'accès aux extensions, aux thèmes ni aux réglages techniques. C'est le rôle à donner à la personne qui gère le site au quotidien.

## Avant la mise en ligne

- Ajouter une photo à chaque produit, relire noms, formats et prix, puis **Produits boutique → Mise en ligne → « Tout est prêt »**.
- Remplacer les photos d'illustration (Unsplash) du Tea Room et des pages par les photos de la maison.
- Vérifier **Réglages → Général** (langue Français pour l'administration, fuseau Zurich, titre « Confiserie Schiesser ») et, le jour du lancement, décocher « Demander aux moteurs de recherche de ne pas indexer ce site » dans **Réglages → Lecture**.
- Relire tous les textes de démonstration (dates, chiffres, prix, allergènes, affirmations historiques) et les faire valider par la maison : la liste des faits à vérifier est dans `SEO-RAPPORT.md`, section 7. En particulier la mention « Le plus ancien café de Suisse » de l'encart « La doyenne », les durées des trajets et les chiffres d'affluence, donnés à titre d'exemple.
- Remplacer les photos par les photos de la maison, avec un texte alternatif descriptif pour chacune ; les archives et les comparaisons « hier et aujourd’hui » attendent le fonds photographique de la maison.
- Saisir les vraies coordonnées et les horaires dans Réglages maison, ainsi que la position exacte (latitude, longitude).
- Installer une extension d'envoi d'e-mails (SMTP) et **envoyer un message de test** avec le formulaire de contact.
- Vérifier que l'adresse des Réglages maison est exactement celle de la fiche Google (les annuaires indiquent Marktplatz 19, 4051 Basel), puis contrôler la carte Google Maps et les boutons « Itinéraire ».
- Compléter et publier la page **Impressum** (brouillon créé par l'import) et la **Politique de confidentialité** (nLPD), en y mentionnant la carte et le formulaire (voir plus haut).
- Créer ou mettre à jour la fiche **Google Business Profile** avec exactement le même nom, la même adresse et le même téléphone.
- Mettre le site en HTTPS, activer un cache, programmer des sauvegardes.
- Relier Google Search Console et envoyer le plan du site.
- Si le site remplace le site actuel : redirections 301 de toutes les anciennes adresses (voir `SEO-RAPPORT.md`, section 9).

## Organisation du code

```
schiesser/
├── style.css, functions.php, theme.json
├── header.php, footer.php, page.php, index.php, 404.php
├── single-schiesser_produit.php   page d'un produit
├── woocommerce.php                pages WooCommerce (utilisé seulement si WooCommerce est actif)
├── parts/titre-page.php           bandeau de titre des pages sans grande photo
├── inc/
│   ├── carte-druck.php    carte du Tea Room en PDF et carte imprimable
│   ├── maillage.php       section « Weiter im Haus » (liens vers les autres pages)
│   ├── langue.php         site en allemand, administration en français, dates et heures, adresses des pages
│   ├── i18n.php           trois langues avec Polylang : langue de la page, traductions liées, prix et photos communs
│   ├── traductions.php    textes du thème en français et en anglais (boutons, horaires, fériés, allergènes…)
│   ├── contenu-traductions.php  pages, produits et carte du Tea Room en français et en anglais (et leur SEO)
│   ├── import-langues.php création des versions FR et EN à l'import (Polylang)
│   ├── lancement.php      liste des produits « en préparation » et bouton de mise en ligne
│   ├── contenu-produits.php  liste de prix de la boutique et carte du Tea Room
│   ├── contenu-pages.php  texte allemand des pages
│   ├── setup.php          réglages du thème, styles, scripts, navigation, redirections de pages
│   ├── charte.php         couleurs et typographie
│   ├── reglages.php       données des Réglages maison
│   ├── horaires.php       jours fériés de Bâle (Fasnacht comprise), dates exceptionnelles
│   ├── annonces.php       bandeau d'annonce programmable, barre du téléphone
│   ├── page-reglages.php  écran des Réglages maison
│   ├── shortcodes.php     codes courts
│   ├── produits.php       type de contenu Produits boutique, champs, cartes, produits liés
│   ├── tea-room.php       type de contenu Produits Tea Room (carte du salon), rubriques, transfert, données « Menu »
│   ├── blocs.php          blocs maison et styles des blocs WordPress
│   ├── maquette.php       blocs de la maquette : déclaration, rendu du site, formulaire de contact
│   ├── allergenes.php     allergènes et régimes : saisie, pictogrammes, filtres
│   ├── messages.php       messages reçus du formulaire, statuts, effacement après 12 mois
│   ├── securite.php       connexion protégée, en-têtes de sécurité, préchargement de la photo
│   ├── avis.php           avis clients : menu « Avis clients », avis Google (Places API), bloc Avis
│   ├── mise-en-page.php   styles de texte enregistrés, espacements des sections, réglages des boutons
│   ├── compositions.php   compositions de l'éditeur et outils d'écriture des blocs
│   ├── seo.php            Rank Math et données structurées
│   ├── woocommerce.php    préparation de la vente en ligne
│   ├── aujourdhui.php     écran « Aujourd'hui », produits épuisés du jour
│   ├── client.php         « Appeler pour commander », « Ma sélection », partage, navigation fluide
│   ├── edition.php        sections programmées, « Modifier cette section », point d'intérêt, menu du gérant
│   ├── admin.php          habillage de l'administration, tableau de bord, guide, rôle
│   └── demo.php           contenu de démonstration
├── blocks/     hero/, section/, produits/  (block.json + index.js + render.php)
└── assets/
    ├── css/     schiesser.css (site), editeur-maquette.css (éditeur), WooCommerce
    ├── js/      site.js, maquette.js (animations du site), prestige.js (effets premium), blocs-maquette.js (éditeur des blocs), typographie.js (panneau Aa), mise-en-page.js (espacements, boutons, copie de section), boutique, éditeur
    ├── vendor/leaflet/   bibliothèque de carte (licence BSD)
    └── admin/, fonts/
```

Le JavaScript est écrit en JS simple : aucune compilation (Node, npm) n'est nécessaire pour le modifier.
Les polices Bodoni Moda et Inter sont hébergées dans le thème (licence SIL Open Font License, fichiers dans `assets/fonts/`).

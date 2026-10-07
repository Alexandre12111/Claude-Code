# BQP Gouvernance

Plugin WordPress pour gérer et afficher les membres de la gouvernance de boursequatrepoint.fr.

Indépendant du plugin **BQP Partenaires** : tous les noms de fonctions, de constantes et de classes CSS sont préfixés `bqg_`, les deux peuvent tourner ensemble sans conflit.

## Installation

### Avec Code Snippets (recommandé ici)

1. Ouvrir `bqp-gouvernance-code-snippets.txt`
2. Copier tout le contenu dans un nouveau snippet
3. Régler le snippet sur **Run everywhere**
4. Enregistrer et activer

Aucune balise `<?php` ni `?>`, aucun `register_activation_hook` : le fichier fonctionne tel quel dans Code Snippets.

### En extension classique

Compresser le dossier en `.zip`, puis Extensions → Ajouter → Téléverser.

Dans les deux cas, les instances **Bureau** et **Conseil scientifique** sont créées automatiquement à la première visite de l'administration.

## Utilisation

Un menu **Gouvernance** apparaît dans la colonne de gauche :

- **Tous les membres** : la liste, avec aperçu des photos et tri manuel
- **Ajouter un membre** : prénom et nom, photo, fonction, description, instance, ordre, et la **fiche détaillée** (profession, rôle dans l'association, parcours, CV en PDF, lien)
- **Instances** : Bureau, Conseil scientifique, couleur des étiquettes
- **Mode d'emploi** : rappel du shortcode et de ses options

Puis coller le shortcode dans une page :

```
[bqp_gouvernance]
```

## Options du shortcode

| Option | Valeurs | Défaut |
|---|---|---|
| `instances` | slugs séparés par une virgule | toutes |
| `colonnes` | 2, 3 ou 4 | 4 |
| `format` | carre, portrait, rond | carre |
| `affichage` | replie (une carte par instance, à déplier) / onglets | replie |
| `defaut` | slug de l'instance ouverte au chargement ; `aucun` pour tout replier (en onglets : `tous`) | bureau |
| `filtres` | oui / non (affichage onglets) | oui |
| `tous` | oui / non (bouton « Tous », affichage onglets) | non |
| `compteurs` | oui / non | oui |
| `titre` | texte libre | vide |
| `photos` | couleur / grisaille | couleur |
| `badges` | oui / non (affichage onglets) | oui |
| `limite` | nombre | tous |
| `ordre` | manuel / nom / recent | manuel |
| `fenetre` | oui / non (fiche détaillée au clic) | oui |

Exemples :

```
[bqp_gouvernance titre="Notre gouvernance"]
[bqp_gouvernance defaut="conseil-scientifique"]
[bqp_gouvernance affichage="onglets" tous="oui" format="rond" photos="grisaille"]
```

## Les instances repliées (par défaut depuis la 1.3.0)

Comme sur les pages Bibliothèque et Partenaires, chaque instance est d'abord une **carte**, dans le même design que les catégories de partenaires : bandeau teinté de la couleur de l'instance avec son pictogramme (un groupe pour le Bureau, un livre pour le Conseil scientifique) et le nombre de membres en grand (« 05 membres »), puis le nom et le texte d'introduction en entier, et en pied les photos des premiers membres avec « Découvrir ». Un clic sur une carte **déplie ses membres** juste en dessous, **4 par ligne** ; le pied de la carte se colore, affiche « Replier » et pointe vers le bloc ouvert.

- **Le Bureau est ouvert à l'arrivée sur la page.** Pour ouvrir une autre instance : `defaut="conseil-scientifique"` ; pour tout replier : `defaut="aucun"`. Ce choix est fait côté serveur : pas de clignotement.
- Une seule instance ouverte à la fois ; un nouveau clic sur la carte, la croix ou la touche Échap la replient.
- Le texte de chaque carte est la **Description** de l'instance (Gouvernance › Instances). Les deux instances d'origine reçoivent un texte par défaut à la mise à jour, s'il était vide.
- Lien direct vers une instance : `/gouvernance/#gouvernance-conseil-scientifique`. Le lien direct d'un membre (`/gouvernance/#prenom-nom`) ouvre son instance puis sa fiche.
- Dans la fiche, les flèches passent au membre suivant **de la même instance**, avec son nom dans la barre.
- Un membre des deux instances apparaît dans les deux blocs.
- Sans JavaScript, les deux instances restent visibles.

Titres : sans `titre`, les instances sont des H2 et les membres des H3 ; avec `titre`, le titre est un H2, les instances des H3 et les membres des H4.

## L'affichage en onglets

Avec `affichage="onglets"`, on retrouve l'affichage de la 1.2 : deux onglets seulement : **Bureau** et **Conseil scientifique**, dans cet ordre. Il n'y a pas de bouton « Tous » par défaut, on peut le rajouter avec `tous="oui"`.

Au chargement de la page, c'est le Bureau qui s'affiche. Ce choix aussi est calculé côté serveur, donc la bonne instance est déjà en place dans le HTML : pas de clignotement ni d'attente du JavaScript. Pour ouvrir sur une autre instance, utiliser `defaut="conseil-scientifique"` ou `defaut="tous"`.

## La fiche détaillée (fenêtre au clic)

Depuis la version 1.2.0, chaque membre peut avoir une fiche complète qui s'ouvre dans une fenêtre quand on clique sur sa carte. Les champs se remplissent dans le bloc **Fiche détaillée** de l'écran d'édition du membre :

| Champ | Rôle |
|---|---|
| Profession | Le métier exercé en dehors de l'association, affiché dans un encadré sous la fonction |
| Son rôle dans l'association | Pourquoi la personne est là, ce qu'elle apporte, mis en valeur sous forme de citation |
| Parcours (CV texte) | Éditeur simple : intertitres, gras, italique, listes, liens |
| CV en PDF | Choisi dans la médiathèque, seuls les fichiers PDF sont acceptés |
| Lien | Profil LinkedIn ou page personnelle |

La description courte existante s'affiche dans la fiche sous le titre « Présentation ».

Une carte devient cliquable dès qu'au moins un de ces champs (ou la description) est rempli. Elle affiche alors un pied « Voir le profil », avec une pastille « CV » si un PDF est joint. Les cartes sans contenu supplémentaire restent statiques. Sur la grille, les descriptions trop longues sont coupées avec un léger fondu : le texte complet est dans la fiche.

Dans la fenêtre :

- colonne de gauche : portrait, boutons **Télécharger le CV** (avec le poids du fichier), **Ouvrir le CV**, **Profil LinkedIn** ou **Page personnelle** ;
- colonne principale : instance, nom, fonction, profession, rôle dans l'association, présentation, parcours ;
- flèches précédent et suivant, ou touches ← et →, pour passer d'un membre à l'autre parmi ceux visibles dans l'onglet actif, avec la position « 2 / 5 » ;
- fermeture par la croix, la touche Échap ou un clic en dehors ; le focus revient sur la carte.

Chaque fiche a son lien direct : `/gouvernance/#prenom-nom` (le slug du membre). Ouvrir ce lien affiche la page avec la fiche déjà ouverte, en basculant sur le bon onglet si besoin. Sur mobile, la fenêtre passe en plein écran.

Pour désactiver la fenêtre et garder des cartes simples : `[bqp_gouvernance fenetre="non"]`.

**RGPD** : un CV contient des données personnelles. Ne publier le PDF et le parcours qu'avec l'accord écrit de la personne, et retirer le fichier de la médiathèque si elle le demande. Un CV allégé (sans adresse ni téléphone) est préférable.

## Le recadrage des photos

Deux mécanismes se complètent pour que toutes les photos aient exactement la même taille, quelle que soit celle envoyée :

1. **Deux tailles d'image sont enregistrées** dans WordPress, en recadrage dur, cadré en haut pour ne jamais couper le visage :
   - `bqg_carre` : 560 × 560 px (format par défaut)
   - `bqg_portrait` : 520 × 650 px
2. **Un recadrage CSS** (`object-fit: cover` + ratio fixe) prend le relais à l'affichage, ce qui couvre aussi les photos envoyées avant l'installation du plugin.

Les règles de dimension de l'image sont en `!important`, car le thème `hello-elementor` et Elementor appliquent tous les deux `img { height: auto }`, ce qui empêchait la photo de remplir le cadre.

Pour que les photos déjà présentes dans la médiathèque bénéficient du premier mécanisme, lancer une régénération des miniatures avec l'extension *Regenerate Thumbnails*. Ce n'est pas obligatoire, le rendu reste correct sans.

Taille source conseillée : au moins 560 × 560 px, sujet centré et cadré en buste. N'importe quel format de fichier convient, paysage comme panoramique, le recadrage s'en charge.

## Notes techniques

- Les fiches des membres ne génèrent aucune page publique (`public => false`), ce qui évite des pages trop légères dans l'index Google.
- CSS et JS injectés en ligne uniquement sur les pages contenant le shortcode.
- Les couleurs des filtres sont en `!important` : elles résistent aux règles de bouton du thème et d'Elementor.
- Les retours à la ligne de la description sont conservés via `wpautop`, après échappement.
- Les photos reçoivent un `alt` automatique du type « Portrait de Prénom Nom ».
- La fiche détaillée utilise l'élément natif `<dialog>` : piège du focus, touche Échap et lecture par les lecteurs d'écran (`aria-labelledby` sur le nom) sont gérés par le navigateur. Le contenu de chaque fiche est rendu côté serveur dans un `<template>`, donc rien n'est chargé en AJAX.
- Le parcours est filtré avec `wp_kses_post` (aucun script possible), l'engagement avec `sanitize_textarea_field`, le lien avec `esc_url_raw`. Le CV n'est conservé que si son type MIME est `application/pdf`.
- Une colonne « Fiche détaillée » dans la liste des membres indique d'un coup d'œil les champs remplis (Profession, Rôle, Parcours, CV PDF).

## Historique

- **1.4.0** : cartes d'instance au design des Partenaires (bandeau coloré, nombre de membres en grand, introduction complète, photos et « Découvrir » / « Replier » en pied) ; membres sur 4 colonnes par défaut (3 sous 1100 px, 2 sous 860 px, 1 sur mobile)
- **1.3.0** : instances repliées en cartes, comme la Bibliothèque et les Partenaires ; le Bureau est ouvert à l'arrivée ; texte d'introduction et pictogramme par instance ; navigation de la fiche limitée à l'instance ; liens directs vers une instance ; option `affichage="onglets"` pour l'ancien affichage
- **1.2.0** : fiche détaillée dans une fenêtre (profession, rôle dans l'association, parcours, CV en PDF, lien)

## Testé (1.3.0)

Sur WordPress 6.8 avec le thème Hello Elementor et les 11 membres de démonstration : Bureau ouvert au chargement, passage au Conseil scientifique, repli par la croix et par Échap (qui ferme d'abord la fiche si elle est ouverte), membre présent dans les deux instances, carte simple sans fiche, fiche ouverte avec la position « 1 / 6 » et le nom de l'instance, flèches limitées à l'instance, liens directs vers un membre et vers une instance, affichage en onglets inchangé, titres H2, H3, H4, mobile 390 px sans débordement, aucune erreur JavaScript ni PHP.

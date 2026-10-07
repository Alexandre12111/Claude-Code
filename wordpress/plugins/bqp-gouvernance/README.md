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
| `colonnes` | 2, 3 ou 4 | 3 |
| `format` | carre, portrait, rond | carre |
| `filtres` | oui / non | oui |
| `defaut` | slug de l'instance ouverte au chargement, ou `tous` | bureau |
| `tous` | oui / non (bouton « Tous » devant les instances) | non |
| `compteurs` | oui / non | oui |
| `titre` | texte libre | vide |
| `photos` | couleur / grisaille | couleur |
| `badges` | oui / non | oui |
| `limite` | nombre | tous |
| `ordre` | manuel / nom / recent | manuel |
| `fenetre` | oui / non (fiche détaillée au clic) | oui |

Exemples :

```
[bqp_gouvernance titre="Notre gouvernance"]
[bqp_gouvernance defaut="conseil-scientifique"]
[bqp_gouvernance tous="oui" format="rond" photos="grisaille"]
```

## Comportement des filtres

Deux onglets seulement : **Bureau** et **Conseil scientifique**, dans cet ordre. Il n'y a pas de bouton « Tous » par défaut, on peut le rajouter avec `tous="oui"`.

Au chargement de la page, c'est le Bureau qui s'affiche. Ce choix est calculé côté serveur, donc la bonne instance est déjà en place dans le HTML : pas de clignotement ni d'attente du JavaScript. Pour ouvrir sur une autre instance, utiliser `defaut="conseil-scientifique"` ou `defaut="tous"`.

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

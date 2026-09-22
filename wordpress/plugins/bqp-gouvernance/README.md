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
- **Ajouter un membre** : prénom et nom, photo, fonction, description, instance, ordre
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

Exemples :

```
[bqp_gouvernance titre="Notre gouvernance"]
[bqp_gouvernance defaut="conseil-scientifique"]
[bqp_gouvernance tous="oui" format="rond" photos="grisaille"]
```

## Comportement des filtres

Deux onglets seulement : **Bureau** et **Conseil scientifique**, dans cet ordre. Il n'y a pas de bouton « Tous » par défaut, on peut le rajouter avec `tous="oui"`.

Au chargement de la page, c'est le Bureau qui s'affiche. Ce choix est calculé côté serveur, donc la bonne instance est déjà en place dans le HTML : pas de clignotement ni d'attente du JavaScript. Pour ouvrir sur une autre instance, utiliser `defaut="conseil-scientifique"` ou `defaut="tous"`.

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

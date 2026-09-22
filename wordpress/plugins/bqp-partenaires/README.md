# BQP Partenaires

Plugin WordPress pour gérer et afficher les partenaires de boursequatrepoint.fr.

## Installation

Deux fichiers, au choix.

### Avec Code Snippets (recommandé ici)

1. Ouvrir `bqp-partenaires-code-snippets.txt`
2. Copier tout le contenu dans un nouveau snippet
3. Régler le snippet sur **Run everywhere** (il sert en administration et côté visiteur)
4. Enregistrer et activer

Le fichier ne contient aucune balise `<?php` ni `?>`, et aucun `register_activation_hook`, donc il fonctionne tel quel dans Code Snippets.

### En extension classique

1. Compresser le dossier `bqp-partenaires` en `.zip`
2. WordPress → Extensions → Ajouter → Téléverser une extension
3. Activer

Dans les deux cas, les cinq catégories (Entreprises, Académiques, Institutionnels, Médias, Associations) sont créées automatiquement à la première visite de l'administration, avec leur couleur.

## Utilisation

Un menu **Partenaires** apparaît dans la colonne de gauche de l'administration :

- **Tous les partenaires** : la liste, avec aperçu des logos et tri manuel
- **Ajouter un partenaire** : nom, logo, site internet, courte description, catégorie, ordre
- **Catégories** : renommer, ajouter, changer la couleur des étiquettes
- **Mode d'emploi** : rappel du shortcode et de ses options

Puis coller le shortcode dans une page :

```
[bqp_partenaires]
```

## Options du shortcode

| Option | Valeurs | Défaut |
|---|---|---|
| `categories` | slugs séparés par une virgule | toutes |
| `colonnes` | 2, 3 ou 4 | 3 |
| `filtres` | oui / non | oui |
| `compteurs` | oui / non | oui |
| `titre` | texte libre | vide |
| `logos` | grisaille / couleur | grisaille |
| `limite` | nombre | tous |
| `ordre` | manuel / nom / recent | manuel |

Exemple :

```
[bqp_partenaires titre="Ils soutiennent le prix" colonnes="4" logos="couleur"]
```

## Charte appliquée

Couleurs relevées sur les réglages Elementor du site : bordeaux `#74041C`, bordeaux profond `#31020C`, marine `#001756`, orange `#C75A18`, anthracite `#424242`.
Typographies : Cormorant Garamond pour les titres, Inter et Montserrat pour le texte et les libellés. Aucune police externe n'est chargée par le plugin, il réutilise celles déjà présentes sur le site.

## Notes techniques

- Les fiches partenaires ne génèrent aucune page publique (`public => false`), ce qui évite des pages trop légères dans l'index Google.
- CSS et JS sont injectés en ligne uniquement sur les pages contenant le shortcode, sans requête HTTP supplémentaire.
- Les styles des filtres sont volontairement en `!important` sur les couleurs : ils résistent aux règles de bouton du thème et d'Elementor.
- Aucune dépendance externe, pas de jQuery côté visiteur.
- Les logos reçoivent un attribut `alt` automatique basé sur le nom du partenaire.

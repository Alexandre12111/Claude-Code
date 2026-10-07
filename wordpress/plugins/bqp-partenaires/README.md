# BQP Partenaires

Plugin WordPress pour gérer et afficher les partenaires de boursequatrepoint.fr. Version 2.0.0.

Les partenaires ne sont plus mélangés : **un bloc par catégorie** (Académiques, Institutionnels, Entreprises, Associations, Médias), chacun avec sa couleur, son pictogramme, son titre et son texte d'introduction. Un clic sur un partenaire ouvre sa **fiche détaillée** dans une fenêtre, comme pour la Gouvernance.

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

Dans les deux cas, les cinq catégories sont créées automatiquement à la première visite de l'administration, avec leur titre de bloc, leur couleur, leur pictogramme, leur ordre et leur texte d'introduction.

### Mise à jour depuis la 1.x

Remplacer le contenu du snippet. À la première visite de l'administration, les catégories existantes reçoivent leur titre de bloc, leur ordre et leur introduction, **sans rien écraser** : seuls les champs vides sont remplis. Les couleurs d'origine des Associations (bordeaux profond) et des Médias (anthracite), trop proches des autres, passent au bleu canard et au prune, sauf si elles avaient été modifiées. Une catégorie supprimée volontairement n'est pas recréée. Les partenaires existants s'affichent tels quels ; leur fiche détaillée se complète quand on veut.

## Utilisation

Un menu **Partenaires** apparaît dans la colonne de gauche de l'administration :

- **Tous les partenaires** : la liste, avec aperçu des logos et tri manuel
- **Ajouter un partenaire** : nom, logo, site internet, courte description, catégorie, ordre, et la **fiche détaillée**
- **Catégories** : chaque catégorie est un bloc. Nom court (étiquette), titre du bloc, couleur, pictogramme, ordre des blocs et texte d'introduction (champ Description). La liste montre le bloc tel qu'il apparaîtra.
- **Mode d'emploi** : rappel du shortcode et de ses options

Puis coller le shortcode dans une page :

```
[bqp_partenaires]
```

## Un bloc par catégorie

`[bqp_partenaires]` affiche, dans l'ordre choisi :

1. un **sommaire** : une pastille par catégorie (pictogramme, nom, nombre), qui fait défiler jusqu'au bloc ;
2. **un bloc par catégorie** : pictogramme sur fond de couleur, titre (« Partenaires académiques »), introduction, nombre de partenaires, puis les cartes. Chaque bloc a sa couleur, reprise sur le filet des cartes : on distingue les catégories au premier coup d'œil.

Chaque bloc a une ancre : `/partenaires/#partenaires-academiques`, `#partenaires-associations`… Un partenaire classé dans deux catégories apparaît dans les deux blocs. Les partenaires sans catégorie sont réunis dans un dernier bloc « Autres partenaires ».

Pour placer un seul bloc ailleurs (par exemple les médias sur la page presse) : `[bqp_partenaires categories="medias"]`.

L'ancien affichage (une seule grille avec des filtres) reste disponible : `[bqp_partenaires affichage="grille"]`.

## La fiche détaillée

Champs du bloc **Fiche détaillée**, tous facultatifs :

| Champ | Affichage dans la fiche |
|---|---|
| Partenaire depuis | colonne d'informations, et sur la carte |
| Localisation | colonne d'informations, et sur la carte |
| Page LinkedIn | bouton sous le site internet |
| Domaines de collaboration | pastilles (séparés par des virgules) |
| Notre partenariat | mis en valeur comme une citation |
| Présentation détaillée | texte mis en forme : intertitres, gras, listes, liens |

La fenêtre montre à gauche le logo en grand, la catégorie, l'année, la localisation, le site, et les boutons « Visiter le site » et « Page LinkedIn » ; à droite le nom, la courte description en introduction, puis les sections remplies.

- Une carte s'ouvre dès que le partenaire a une description ou un champ de la fiche. Elle affiche « Voir la fiche » et garde un petit bouton « Site » cliquable à part. Un partenaire sans aucun contenu reste une carte simple avec « Visiter le site ».
- Les flèches (boutons ou touches ← et →) passent au partenaire suivant **du même bloc**, avec la position « 2 / 4 » et le nom du bloc dans la barre.
- Fermeture par la croix, la touche Échap ou un clic en dehors ; le focus revient sur la carte.
- Lien direct : `/partenaires/#identifiant-du-partenaire` ouvre la page avec la fiche affichée.
- Sur mobile, la fenêtre passe en plein écran.

## Options du shortcode

| Option | Valeurs | Défaut |
|---|---|---|
| `categories` | slugs séparés par une virgule | toutes |
| `affichage` | blocs / grille | blocs |
| `sommaire` | oui / non (liens vers les blocs) | oui |
| `intro` | oui / non (texte sous le titre du bloc) | oui |
| `fenetre` | oui / non (fiche détaillée au clic) | oui |
| `colonnes` | 2, 3 ou 4 | 3 |
| `filtres` | oui / non (affichage grille) | oui |
| `compteurs` | oui / non | oui |
| `titre` | texte libre | vide |
| `logos` | grisaille / couleur | grisaille |
| `limite` | nombre (par bloc en affichage blocs) | tous |
| `ordre` | manuel / nom / recent | manuel |

Exemple :

```
[bqp_partenaires titre="Ils soutiennent le prix" colonnes="4" logos="couleur"]
[bqp_partenaires categories="academiques,associations" sommaire="non"]
```

Titres : sans `titre`, les blocs sont des H2 et les partenaires des H3 ; avec `titre`, le titre est un H2, les blocs des H3 et les partenaires des H4. La page garde ainsi un seul H1.

## Charte appliquée

Couleurs relevées sur les réglages Elementor du site : bordeaux `#74041C`, bordeaux profond `#31020C`, marine `#001756`, orange `#C75A18`, anthracite `#424242`. Couleurs des blocs par défaut : Académiques marine, Institutionnels orange, Entreprises bordeaux, Associations bleu canard `#1F5561`, Médias prune `#5A2A4F`.
Typographies : Cormorant Garamond pour les titres, Inter et Montserrat pour le texte et les libellés. Aucune police externe n'est chargée par le plugin, il réutilise celles déjà présentes sur le site.

## Notes techniques

- Les fiches partenaires ne génèrent aucune page publique (`public => false`), ce qui évite des pages trop légères dans l'index Google.
- CSS et JS sont injectés en ligne uniquement sur les pages contenant le shortcode, sans requête HTTP supplémentaire.
- Les styles des filtres sont volontairement en `!important` sur les couleurs : ils résistent aux règles de bouton du thème et d'Elementor.
- Aucune dépendance externe, pas de jQuery côté visiteur.
- Les logos reçoivent un attribut `alt` automatique basé sur le nom du partenaire.
- La fiche utilise l'élément natif `<dialog>` (focus, Échap et lecteurs d'écran gérés par le navigateur, `aria-labelledby` sur le nom). Son contenu est rendu côté serveur dans un `<template>` : rien n'est chargé au clic, et le texte reste dans la page.
- La présentation est filtrée avec `wp_kses_post`, les autres champs avec les fonctions de WordPress adaptées (texte, adresse, année).
- Une colonne « Fiche détaillée » dans la liste des partenaires indique les champs remplis.

## Testé

Version 2.0.0, sur WordPress 6.8 avec le thème Hello Elementor et 17 partenaires de démonstration : migration depuis la 1.1 (titres, ordre, introductions et couleurs ajoutés sans écraser), cinq blocs dans l'ordre, sommaire et défilement vers un bloc, ouverture de la fiche, flèches limitées au bloc avec retour au début, Échap et clic en dehors, focus rendu à la carte, lien direct, bouton « Site » qui n'ouvre pas la fiche, carte simple sans fiche, affichage grille avec filtres et fiche, bloc unique avec `categories`, enregistrement des champs en administration, mobile 390 px sans débordement, aucune erreur JavaScript ni PHP.

## Historique

- **2.0.0** : un bloc par catégorie avec sommaire, couleur, pictogramme, titre et introduction ; fiche détaillée dans une fenêtre (depuis, localisation, LinkedIn, domaines, notre partenariat, présentation) ; nouvelles options `affichage`, `sommaire`, `intro`, `fenetre` ; couleurs distinctes pour Associations et Médias ; correction du texte « Aucun logo » qui restait visible à côté d'un logo en administration
- **1.1.0** : filtres bordeaux compacts, logos en grisaille

# BQP Bibliothèque : démo

Documents fictifs pour tester le design de la bibliothèque. À installer le temps des tests, puis à supprimer.

Nécessite **BQP Bibliothèque 3.0 ou plus**.

## Installation

1. Ouvrir `bqp-bibliotheque-demo-code-snippets.txt`
2. Copier tout le contenu dans un **nouveau** snippet (ne pas remplacer celui de la bibliothèque)
3. Régler sur **Run everywhere**, enregistrer et activer
4. Aller dans **Bibliothèque › Documents de démo** et cliquer sur **Créer les documents de démo**

## Ce qui est créé

- **65 documents**, au moins un par élément des blocs 1 à 4, et plusieurs dans les rubriques à regroupements (2.2 Travaux primés par lauréat et par année, 4.2 Publications de partenaires par partenaire…)
- des thèmes dans les cinq thématiques du bloc 5, secteurs et pays compris
- **11 personnes** fictives : 6 lauréats de 2021 à 2025 avec leur prix, 4 experts, 1 représentante de partenaire
- **7 organisations** fictives : partenaires, éditeur, institution, média, entreprise
- des années de 1968 à 2026, les quatre niveaux d'accès, **6 couvertures** générées aux couleurs du site, un **PDF** à télécharger, des **vidéos** (court métrage libre de droits de la Blender Foundation), des liens externes, des mots-clés et des documents associés
- une biographie provisoire pour Jean-Michel Quatrepoint si la sienne est vide, pour tester la page dédiée du bloc 1

Chaque texte indique qu'il s'agit d'un exemple fictif.

## Sans risque pour le référencement

- chaque document de démo est **noindex, nofollow** (balise WordPress et réglage Rank Math)
- il est retiré du sitemap WordPress ; Rank Math écarte de lui-même les contenus noindex

## Supprimer

**Bibliothèque › Documents de démo › Tout supprimer** efface les documents, les couvertures, le PDF, les personnes, organisations et mots-clés créés par la démo, et la biographie provisoire. Rien d'autre n'est touché : une vraie fiche qui porterait le même nom qu'une personne fictive n'est ni modifiée ni supprimée.

Puis désactiver et supprimer le snippet.

« Recréer les documents de démo » supprime la démo existante avant de la recréer : pas de doublons.

## Testé

Dans un WordPress 6.8 réel avec le thème Hello Elementor : création en 6 secondes, blocs remplis dans toutes les rubriques, regroupements par lauréat, par année, par organisation, filtres secteur, pays et nature, annuaire, fiche avec couverture, PDF, citation et documents associés, balise robots `noindex, nofollow`, suppression complète vérifiée en base.

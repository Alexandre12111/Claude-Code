# Thème Schiesser : prototype 0.1

Thème WordPress sur mesure, sans constructeur de pages ni plugin payant.
Le design de la maquette est figé dans le code ; le contenu se modifie depuis l'administration.

## Installation sur le site de test

1. **Apparence → Thèmes → Ajouter → Téléverser un thème**, choisir `schiesser.zip`, puis **Activer**.
2. Sur le tableau de bord, cliquer sur **Importer le contenu de démonstration**.
   Cela crée les 8 produits, les 6 pages avec leurs blocs, le menu principal, et télécharge les photos depuis Unsplash.
3. **Réglages → Permaliens** : vérifier que « Titre de la publication » est sélectionné, puis Enregistrer.

Prérequis : WordPress 6.4 ou plus récent, PHP 7.4 ou plus récent. Aucun plugin nécessaire.
Elementor et Code Snippets ne sont pas utilisés par ce thème : ils peuvent rester désactivés sur ce site de test.

## Ce que le client modifie, et où

| Contenu | Où | Effet |
|---|---|---|
| Téléphone, e-mail, adresse, horaires, vitrine du jour, pied de page | **Réglages maison** | Mis à jour partout : bandeau « Ouvert / Fermé », pied de page, etc. |
| Produits (nom, photo, catégorie, prix, badge, fiche détaillée) | **Produits** | La grille de la boutique et la fiche produit se mettent à jour |
| Ordre des produits | **Produits → Modifier → Attributs → Ordre** | 1 = premier |
| Catégories (filtres de la boutique) | **Produits → Catégories** | Les filtres apparaissent automatiquement |
| Textes et photos des pages | **Pages → Modifier** | Clic direct sur le texte dans l'aperçu ; bouton « Photo » dans la barre du bloc |
| Menu | **Apparence → Menus** | En-tête et pied de page |

## Blocs disponibles (catégorie « Schiesser » dans l'éditeur)

- **Hero (grande image)** : surtitre, titre (italique possible), texte, deux boutons, photo, hauteur, sceau tournant.
- **Grille des produits** : titre et note modifiables ; les produits viennent du menu « Produits ».

Les autres sections de la maquette (Deux maisons, Le geste, Carte du Tea Room, Chronologie, Horaires, FAQ…) seront ajoutées bloc par bloc sur le même principe.

## Organisation du code

```
schiesser/
├── style.css, functions.php, theme.json
├── header.php, footer.php, page.php, index.php, 404.php
├── inc/        setup, reglages, produits, blocs, demo
├── blocks/     hero/, produits/  (block.json + index.js + render.php)
└── assets/     css/schiesser.css, js/site.js, js/boutique.js, admin/
```

Le JavaScript de l'éditeur est écrit en JS simple : aucune compilation (Node, npm) n'est nécessaire pour le modifier.

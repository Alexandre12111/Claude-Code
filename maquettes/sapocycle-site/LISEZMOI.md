# Site SapoCycle 2026, version 5

Refonte repartie de zéro avec la méthode ui-ux-pro-max : un site de 9 pages pour les hôtels de luxe, au ton clairement associatif.

## Pages

| Fichier | Page |
|---|---|
| `index.html` | Accueil |
| `qui-sommes-nous.html` | Fondatrice, mission, histoire, équipe, rapports annuels |
| `notre-approche.html` | Les 5 étapes, l'atelier WohnWerk, le programme liquides, le film |
| `hotels.html` | Bénéfices, adhésion en 4 étapes, simulateur, FAQ |
| `impact.html` | Chiffres clés, hygiène, trois piliers |
| `partenaires.html` | Partenaires filtrables, ambassadeurs |
| `actualites.html` | Articles et presse filtrables |
| `soutenir.html` | Don, SapoCycle Family, bénévolat |
| `contact.html` | Formulaire et adresses |

Chaque page est un vrai fichier HTML. Les styles et scripts sont partagés : `assets/css/site.css`, `assets/js/site.js`.

## Système de design

Recommandations ui-ux-pro-max retenues : pour une association, style accessible et éthique, chaleur organique, grille éditoriale suisse et récit « histoire et confiance ».

* **Couleurs :** bleu SapoCycle `#0069B4`, bleu nuit `#072743`, bleu clair `#52ABFF`, fond crème chaud `#F7F3EC`, rose `#D63A75` réservé aux dons.
* **Typographie :** Newsreader (titres, élégance éditoriale) et Public Sans (texte, lisibilité).
* **Signature :** photos découpées en forme de pain de savon (rectangle très arrondi).
* **Confiance :** labels verts, presse, chiffres sourcés, rapports annuels, témoignages, bandeau « Fondation à but non lucratif » et lien « Faire un don » sur toutes les pages.
* **Mouvement :** transitions natives entre pages, apparitions au défilement, photos qui s'ouvrent, compteurs, tracé des étapes, récit à image fixe, carrousel avec pause. Le mode « mouvement réduit » est respecté.

## Visuels

Les photos produit des savons (main, savon en équilibre, savon jaune) viennent des couvertures des rapports annuels 2023 à 2025 de SapoCycle, recadrées sans le texte. Les autres photos viennent du site actuel ou d'Unsplash (licence gratuite, crédits dans `../sapocycle/LISEZMOI.md`).

## Régénérer

```bash
python3 build.py
```

Les contenus de chaque page sont dans `src/pages/`. L'en-tête, le pied de page et les icônes sont dans `build.py`.

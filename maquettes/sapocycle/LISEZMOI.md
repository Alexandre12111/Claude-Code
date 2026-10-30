# Maquette SapoCycle 2026

Proposition de refonte de https://sapocycle.org/, pensée pour les hôtels : premium, immersive, centrée sur le savon, l'écologie et l'impact humain.

## Fichiers

| Fichier | Rôle |
|---|---|
| `index.html` | Maquette complète, à ouvrir dans un navigateur (images et logo intégrés) |
| `video/sapocycle-solide-vs-liquide.mp4` | Film « solide ou liquide » exporté en 1080p, 52 s |
| `src/body.html`, `src/styles.css`, `src/app.js` | Sources de la page |
| `build.py` | Régénère `index.html` et `artifact.html` : `python3 build.py` |
| `assets/` | Logo officiel et photos du site actuel, compressées en WebP |

## Identité conservée

* Bleu du logo `#0069B4`, bleus du site `#0175D6` et `#52ABFF`, rose du badge 10 ans en accent.
* Montserrat (police historique) pour le texte, Bodoni Moda pour les titres (registre palace), Comfortaa pour l'esprit arrondi du logo.
* Signature « Bubbles saving lives », savon embossé, bulles.

## Parcours de la page

1. Hero : savon 3D qui suit la souris, mousse au clic, bulles qui éclatent au survol
2. Labels verts (Green Globe, Clef Verte, EarthCheck, myclimate, Green Sign)
3. Manifeste qui s'allume mot à mot
4. Parcours d'un savon en défilement horizontal, en 5 étapes
5. Film animé « solide ou liquide » avec lecteur et chapitres, puis comparateur interactif
6. Trois piliers : environnement, intégration sociale, action humanitaire
7. Chiffres clés européens animés
8. Espace hôtels : bénéfices, 4 étapes avec bac bleu qui se remplit, simulateur d'impact
9. Ambassadeurs, hygiène et santé (UNICEF), frise 2014 à 2026, partenaires, actualités
10. Formulaire « Rejoindre le programme »

Transitions « mousse » entre les sections, curseur bulle, boutons magnétiques, défilement doux. Le mode « mouvement réduit » du système est respecté.

## SEO de la page d'accueil (Rank Math)

| Élément | Proposition |
|---|---|
| Focus keyword | recyclage savon hôtel |
| Secondaires | savon solide hôtel, recyclage produits d'accueil, hôtel durable |
| SEO Title | Recyclage savon hôtel \| Fondation SapoCycle (44 caractères) |
| Meta description | Recyclage des savons d'hôtel : SapoCycle collecte vos savons usagés, les fait recycler par des personnes en situation de handicap et les offre à des familles. |
| H1 | Le recyclage des savons d'hôtel qui sauve des vies. |
| Schema | NGO (déjà intégré en JSON-LD), FAQ et Service sur la future page Hôtels |
| Slug page hôtels | /recyclage-savon-hotel/ |

## Points à valider avec SapoCycle

* Chiffres du film et du comparateur : étude ETH Zurich (Koehler et Wildbolz, 2009), article Femina 2025, étude FHNW 2016.
* Hypothèses du simulateur (7 g récupérables par nuitée occupée).
* Remplacer les photos de 2016 par un shooting récent de l'atelier et des savons.
* Traductions DE et EN, et sélecteur de pays Suisse, France, Allemagne.

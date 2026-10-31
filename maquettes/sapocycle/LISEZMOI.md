# Maquette SapoCycle 2026, version 4

Refonte de https://sapocycle.org/ pour les hôtels : un site de 8 pages (Accueil, Qui sommes-nous, Notre approche, Hôtels, Impact, Partenaires, Actualités, Contact).

## L'expérience de l'accueil

Un savon SapoCycle en 3D (Three.js), embossé du logo, reste au premier plan. Le défilement raconte sa seconde vie en cinq chapitres :

1. La chambre : le savon s'use et se creuse
2. La collecte : des dizaines de savons rejoignent la pile (compteur 299 hôtels)
3. L'atelier : le savon éclate en copeaux sur fond bleu nuit
4. Le contrôle : les copeaux se reforment en savon neuf, logo intact
5. Les familles : le savon se multiplie sur fond bleu SapoCycle (compteur 420 000 savons)

Le fond, la frise de progression et les chiffres suivent le chapitre lu. Sans WebGL ou avec le mouvement réduit, la photo du savon remplace la 3D et tout le texte reste lisible.

## Textes

Tous les textes ont été réécrits dans un langage clair, centré sur la mission et l'impact, à partir des contenus du site actuel.

Chaque page a sa propre adresse (`index.html#maison`, `#hotels`...), avec une transition plein écran entre les pages.

## Typographie

* Archivo, en version étendue et fine pour les titres. Sa largeur et sa graisse sont variables : les lettres du titre d'accueil se resserrent et s'épaississent sous le curseur, et le logotype du pied de page s'étire au défilement.
* Geist pour le texte courant.

## Effets

* Savon 3D piloté par le défilement (accueil)
* Transition de page en deux volets (bleu SapoCycle puis bleu nuit) avec le nom de la page
* Titre d'accueil à lettres variables, vague d'ouverture
* Survol « eau savonneuse » des photos (distorsion douce)
* Bandeau qui accélère et s'incline avec la vitesse du défilement
* Parcours horizontal épinglé (Savoir-faire), cartes qui s'empilent (Hôtels)
* Curseur sur mesure, boutons magnétiques, pastille de navigation qui glisse
* Aperçu photo qui suit le curseur dans le Journal, accordéons, frise à faire glisser
* Simulateur d'impact et film « solide ou liquide »

## Fichiers

| Fichier | Rôle |
|---|---|
| `index.html` | Maquette complète, à ouvrir dans un navigateur (images et logo intégrés) |
| `video/sapocycle-solide-vs-liquide.mp4` | Film « solide ou liquide » en 1080p, 52 s |
| `src/body.html`, `src/styles.css`, `src/app.js`, `src/xp.js`, `src/film.html` | Sources (`xp.js` : le savon 3D) |
| `build.py` | Régénère `index.html` et `artifact.html` : `python3 build.py` |
| `assets/` | Photos retouchées en WebP et logo officiel |

## Direction artistique

* Ivoire, bleu nuit et bleu du logo `#0069B4`. Photos documentaires de l'atelier et des familles en noir et blanc chaud.

## Crédits photo

Photos SapoCycle : hero, atelier WohnWerk, distribution, enfants, portraits (site sapocycle.org).

Photos Unsplash, licence Unsplash gratuite (usage commercial autorisé, crédit apprécié) :

* Salle de bain en marbre : Tsuyoshi Kozu, https://unsplash.com/photos/jC9a3X0uiBo
* Salle de bain minérale : Ela De Pure, https://unsplash.com/photos/HIu7A27ptE0
* Lac de Seealpsee : Fabian Kleiser, https://unsplash.com/photos/atEGGVXWOv8
* Mains d'enfant au lavabo : Maria Lin Kim, https://unsplash.com/photos/I3x3xn8o3G8
* Chambre d'hôtel : Markus Winkler, https://unsplash.com/photos/zk3DLNjeObQ
* Pains de savon empilés : Annie Spratt, https://unsplash.com/photos/cxAZxTuL7Sk

## SEO de la page d'accueil (Rank Math)

| Élément | Proposition |
|---|---|
| Focus keyword | recyclage savon hôtel |
| Secondaires | savon solide hôtel, recyclage produits d'accueil, hôtel durable |
| SEO Title | Recyclage savon hôtel \| Fondation SapoCycle (44 caractères) |
| Meta description | Recyclage des savons d'hôtel : SapoCycle collecte vos savons usagés, les fait recycler par des personnes en situation de handicap et les offre à des familles. |
| H1 | Le recyclage des savons d'hôtel qui sauve des vies. |
| Schema | NGO (intégré en JSON-LD), FAQ et Service sur la future page Hôtels |

## À prévoir pour la mise en ligne

* Un shooting professionnel à l'atelier WohnWerk et chez un hôtel partenaire : c'est ce qui fera la différence finale.
* Valider les chiffres du film (ETH Zurich 2009, Femina 2025, FHNW 2016) et l'accord des personnes photographiées.
* Traductions DE et EN.

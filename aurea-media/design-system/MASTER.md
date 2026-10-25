# Design system Aurea Media (refonte 2026)

Construit avec le skill ui-ux-pro-max (requête « creative web agency premium luxury dark », réglages variance 7, mouvement 7, densité 3), puis ajusté à la charte existante.

## Direction

* **Pattern** : storytelling au scroll, avec un appel à l'action à la fin de chaque chapitre et un grand appel final. Le récit reste lisible sans animation.
* **Style** : minimalisme éditorial sombre. Typographie surdimensionnée, beaucoup d'espace, un seul accent (l'or). Le style « Liquid Glass » proposé par l'outil est limité à la barre de navigation (verre fumé), car il est pensé pour les interfaces Apple.
* **Objectif** : faire sur mesure et premium, et aller droit au but. Chaque section répond à une question du prospect : quoi, pour qui, preuve, prix, comment, contact.

## Couleurs (conservées de la charte actuelle)

| Rôle | Valeur | Variable |
|---|---|---|
| Fond principal | `#0A0A0A` | `--ink` |
| Fond surélevé | `#121211` | `--ink-2` |
| Carte | `#191917` | `--ink-3` |
| Texte principal | `#F5F5F0` | `--cream` |
| Texte secondaire | `#AEAEA8` | `--mute` (contraste 9,6:1 sur `--ink`) |
| Accent or | `#C9A961` | `--gold` (contraste 9,4:1 sur `--ink`) |
| Or profond, survol | `#B8934E` | `--gold-deep` |
| Section claire | `#F5F5F0` fond, `#0A0A0A` texte, `#44443F` secondaire | `--paper` |
| Filets | `rgba(245,245,240,.10)` | `--line` |
| Validation | `#2F9E6E` | `--ok` |

Règle : l'or ne sert jamais de fond à du texte long. Texte sur bouton or : `--ink`.

## Typographie

* **Titres** : Instrument Serif (400 et italique). L'italique or met en valeur un ou deux mots par titre, jamais plus.
* **Texte et interface** : Geist (300 à 600).
* Échelle : H1 `clamp(2.75rem, 7vw, 6.5rem)`, H2 `clamp(2.25rem, 4.6vw, 4.25rem)`, H3 `1.5rem`, texte 1.0625rem, interligne 1.6, surtitres en capitales 0.75rem espacées de 0.18em.
* Polices auto hébergées dans le thème (woff2, `font-display: swap`).

## Espacements et grille

Échelle 8 px, densité aérée : sections de 120 à 160 px en desktop, 80 px en mobile. Conteneur 1240 px, gouttière 24 px (16 px en mobile). Rayons : 6 px (boutons), 20 px (cartes), 28 px (grands blocs).

## Mouvement

| Effet | Détail |
|---|---|
| Révélation des titres | mots qui montent avec un masque, 700 ms, `cubic-bezier(.2,.7,.2,1)`, décalage 60 ms |
| Apparition des blocs | opacité et translation 24 px, déclenchée par IntersectionObserver |
| Mur de réalisations du hero | 3 colonnes de captures qui défilent en continu, sens alternés |
| Réalisations | défilement horizontal piloté par le scroll vertical (section collante) |
| Méthode | ligne or qui se remplit au scroll |
| Boutons | effet magnétique léger, halo or au survol |
| Curseur | halo or doux qui suit la souris sur desktop |
| Compteurs | chiffres animés à l'entrée dans l'écran |

Toutes les animations sont coupées avec `prefers-reduced-motion: reduce` : le contenu s'affiche dans son état final. Aucune bibliothèque lourde : CSS et JavaScript natif (environ 6 ko).

## Accessibilité et performance

* Contraste 4.5:1 minimum, focus visible or sur tous les éléments interactifs, cibles de 44 px minimum.
* Un seul H1 par page, ordre de lecture du DOM identique à l'ordre visuel.
* Images WebP avec dimensions fixes (pas de CLS), lazy loading hors écran, image du hero préchargée.
* Icônes SVG en ligne, aucun emoji.

## À éviter

Visuels bas de gamme, animations rapides ou gratuites, plus de deux mots en italique or par titre, cartes toutes identiques, carrousels automatiques, pop up à l'arrivée.

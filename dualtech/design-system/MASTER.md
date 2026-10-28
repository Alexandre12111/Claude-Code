# Design system DualTech (refonte 2026)

Construit avec le skill ui-ux-pro-max (requêtes « defense industry conference event institutional premium dark immersive » puis « government defense institutional trust navy », réglages variance 5 à 6, mouvement 6 à 7, densité 3), puis aligné sur la charte DualTech existante : couleurs, polices, logo et contenus sont conservés, seul le design repart de zéro.

## Direction

* **Pattern** : hero vidéo plein écran, puis récit au défilement en chapitres. Chaque chapitre répond à une question du visiteur (quoi, pour qui, quand, avec qui, où) et se termine par une action. Une seule action principale partout : **S'inscrire**. Action secondaire : **Devenir partenaire**.
* **Style** : institutionnel de défense, éditorial et sombre. Base « Accessible & Ethical » (contraste, focus, lisibilité, pensée pour un public public et institutionnel), avec les codes du « Parallax Storytelling » dosés : sections collantes, profondeur, chiffres animés. Typographie très affirmée, filets fins, repères de visée (coins, coordonnées, numérotation 01 à 20) qui évoquent l'instrumentation sans tomber dans le jeu vidéo.
* **Ton** : sobre, souverain, précis. Pas de dégradés criards, pas de néons, pas d'effets gratuits. Le rouge du logo est un signal rare.

## Couleurs (charte DualTech conservée)

| Rôle | Valeur | Variable |
|---|---|---|
| Fond le plus profond | `#041226` | `--nuit` |
| Fond principal sombre | `#071B36` | `--marine-fonce` |
| Marine de la charte | `#0B2244` | `--marine` |
| Marine surélevé (cartes) | `#123059` | `--marine-clair` |
| Bleu DualTech (boutons, liens sur clair) | `#1B5FC1` | `--bleu` (6,1:1 sur blanc) |
| Bleu survol | `#154DA0` | `--bleu-hover` |
| Bleu technique (liens et accents sur sombre) | `#49A7FF` | `--tech` (6,8:1 sur `--marine-fonce`) |
| Or (Prix, distinctions) | `#A9852F` | `--or` ; sur sombre `#C9A24A` (`--or-clair-texte`, 7:1) |
| Or très clair (fonds) | `#F3EBDA` | `--or-clair` |
| Rouge signal (T du logo) | `#DF2229` | `--signal` : uniquement pastille « en direct », compte à rebours, repère de section. Jamais en texte long |
| Encre (texte sur clair) | `#131A26` | `--encre` |
| Gris (texte secondaire sur clair) | `#5B6779` | `--gris` (5,7:1 sur blanc) |
| Texte sur sombre | `#E8EFFA` | `--brume` |
| Texte secondaire sur sombre | `#9DB6DC` | `--acier` (8,1:1 sur `--marine-fonce`) |
| Filets clairs | `#DDE3EC` | `--filet` |
| Papier | `#F5F7FA` | `--papier` |

Règles : les sections alternent sombre (récit, émotion) et papier (information, logos partenaires). Texte de bouton sur `--bleu` : blanc. L'or est réservé au Prix de l'innovation duale.

## Typographie (charte conservée)

* **Titres** : Archivo, axe de largeur exploité. Titres d'affichage en Archivo 700, largeur 106, interlettrage −0,02 em. Surtitres et libellés en Archivo condensé (largeur 85) 600, capitales, interlettrage 0,16 em.
* **Texte** : Public Sans 300 à 600. Chiffres tabulaires pour horaires, compteurs et compte à rebours.
* **Échelle** : H1 `clamp(2.6rem, 6.4vw, 6rem)`, H2 `clamp(2rem, 4.2vw, 3.75rem)`, H3 `1.375rem`, texte `1.0625rem`, interligne 1,6.
* Polices auto hébergées dans le thème WordPress (woff2, `font-display: swap`). Dans la maquette : Google Fonts.

## Espacements et grille

Échelle de 8 px, densité aérée : sections de 128 px en desktop, 80 px en mobile. Conteneur 1280 px, gouttière 24 px (16 px en mobile). Rayons volontairement faibles (2 px boutons, 4 px cartes) : rigueur institutionnelle.

## Mouvement

| Effet | Détail |
|---|---|
| Hero | vidéo YouTube plein écran en fond, image de couverture affichée d'abord (LCP rapide), boutons Son et Pause, bouton « Voir le film » qui ouvre la vidéo en grand avec le son |
| Titres | lignes révélées par un masque, 800 ms, `cubic-bezier(.2,.7,.2,1)` |
| Manifeste | texte qui s'éclaire mot à mot au défilement |
| Filières | bandeau défilant, puis liste 01 à 20 avec titre collant |
| Chiffres | compteurs animés à l'entrée dans l'écran |
| Compte à rebours | jours, heures, minutes, secondes jusqu'au 26 novembre 2026, 8 h 30 |
| Programme | ligne de temps qui se remplit au défilement, filtres par format |
| Intervenants | portraits en niveaux de gris qui passent en couleur au survol |
| En-tête | transparent sur le hero, verre marine ensuite, barre de progression de lecture |

Tout est coupé avec `prefers-reduced-motion: reduce` : contenu dans son état final, vidéo en pause sur l'image de couverture. JavaScript natif, sans bibliothèque.

## Accessibilité et performance

* Contraste 4,5:1 minimum, focus visible (anneau `--tech` de 3 px), cibles de 44 px minimum, lien d'évitement.
* Un seul H1 par page, ordre du DOM identique à l'ordre visuel.
* Vidéo : chargée après l'image de couverture, en `youtube-nocookie`, avec contrôles accessibles. Carte Google chargée au clic seulement.
* Images WebP avec dimensions fixes (aucun CLS), `loading="lazy"` hors écran, image du hero préchargée.
* Icônes SVG en ligne, aucun emoji.

## À éviter

Néons, glitch, typographie « militaire » pochoir, camouflage, carrousels automatiques, pop up à l'arrivée, rouge en aplat, plus d'une action principale par écran.

## Effets dynamiques (version 2, à la demande du client)

Moteur : GSAP 3.12.5 et ScrollTrigger hébergés avec le site (`assets/vendor/`), effets dans `assets/dualtech-fx.js`. Règles du skill suivies : au plus une section épinglée par page, défilement lié en `scrub` doux, apparitions courtes, tout coupé avec `prefers-reduced-motion`.

| Effet | Où |
|---|---|
| Intro : logo révélé, barre rouge et compteur 000 à 100, puis rideau (une fois par session) | Accueil |
| Titre du hero animé lettre par lettre, ligne de balayage radar, bandeau d'informations, profondeur au défilement (vidéo qui zoome, texte qui s'efface) | Accueil |
| Curseur réticule (point rouge et cercle), « Voir » sur les portraits et la vidéo | Desktop |
| Boutons magnétiques, cartes inclinées en 3D, halo qui suit la souris sur les cartes | Partout |
| 20 icônes de filières dessinées au trait, chacune avec son animation (radar, ADN, roues, orbites, ECG, houle, éclair…) | Accueil |
| Constellation de points reliés qui s'écarte de la souris | Manifeste, en-têtes des pages |
| Bandeaux de filières dont la vitesse et l'inclinaison suivent le défilement | Accueil |
| Programme des temps forts en défilement horizontal épinglé, avec horloge et règle horaire | Accueil, desktop |
| Défilé de portraits sur deux rangs, logos partenaires défilants | Accueil |
| Surtitres « déchiffrés » façon décodage | Partout |
| Médaille animée et reflet sur la dotation, cadre doré en rotation | Prix |
| Images révélées par volet, anneaux de progression sur les chiffres, date finale qui se remplit | Partout |
| Bouton haut de page avec anneau de progression, transition en volet entre les pages | Partout |

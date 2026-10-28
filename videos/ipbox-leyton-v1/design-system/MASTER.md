# Design system : vidéos Leyton (CIR V9, V10 et IP Box)

Source de vérité visuelle de la vidéo, établie avec le skill `ui-ux-pro-max`
(`--design-system "B2B tax consulting explainer motion design" --variance 6 --motion 8 --density 3`)
puis adaptée à la charte Leyton (les couleurs et polices de la marque priment sur la palette générique proposée).

## Direction

* Style : Flat Design (recherche `--domain style`), surfaces unies, pas de dégradés, une seule ombre d'élévation.
* Pattern : Trust & Authority. Hiérarchie claire, chiffres mis en avant, ton institutionnel.
* À éviter : effets purement décoratifs, dégradés violets ou roses, textes trop petits, couleur seule pour porter un sens.

## Couleurs (tokens sémantiques)

| Token | Valeur | Usage | Contraste sur blanc |
|---|---|---|---|
| `--navy` (primary) | `#002C49` | Traits, titres, texte principal | 14,4:1 |
| `--orange` (accent) | `#F0682F` | Graphismes, chiffres et titres grands formats (60 px et plus) | 3,1:1 (grand texte uniquement) |
| `--accent-ink` | `#B8461B` | Texte orange de petite taille (surtitres, étiquettes) | 5,3:1 |
| `--ink-2` | `#3B5266` | Texte secondaire | 8,1:1 |
| `--surface` | `#FFFFFF` | Fond des tableaux | |
| `--surface-2` | `#F4F7FA` | Cartes désactivées | |
| `--line` | `#D3DEE7` | Séparateurs, segments inactifs | |
| `--sub-muted` | `#9FB3C4` | Mots à venir des sous-titres (sur navy) | 6,7:1 |

## Typographie

Nohemi Bold pour les titres et chiffres, Montserrat pour le texte. Échelle unique (pixels sur une image 1920×1080) :

| Rôle | Taille |
|---|---|
| display | 110 et plus (chiffres clés, « CIR ») |
| h1 | 84 |
| h2 | 60 |
| title | 46 |
| body | 38 |
| label | 32 (minimum absolu à l'écran) |
| kicker | 28, capitales, interlettrage 6 px, `--accent-ink` |

Chiffres en `tabular-nums` pour que les compteurs ne tremblent pas.

## Mise en page

* Chaque tableau : surtitre de chapitre centré (y = 112), titre centré (ligne de base y = 222), contenu entre y = 260 et y = 860.
* La bande basse reste libre pour les sous-titres.
* Cartes : rayon 24, bordure 4 px, grille 2 × 3 au rythme de 8 px.
* Repère de progression en haut à gauche (01/09 et 9 segments en V10) : chapitre courant en orange, chapitres vus en navy.

## Mouvement

* Deux transitions seulement : l'anneau Leyton (changement de chapitre) et le panoramique directionnel (continuité spatiale).
* Une emphase forte par tableau au maximum (éclat ou onde sur le moment clé : CIR, déverrouillage, 30 %, remboursement, conclusion).
* Décélération à l'arrivée, accélération au départ ; pas de halo coloré ni de balayage lumineux décoratif.
* La main 3D, l'écriture lettre par lettre et la synchronisation voix restent inchangées.

## Accessibilité

* Sous-titres karaoké sur fond navy opaque, mots à venir à 6,7:1.
* « Brevets » exclu indiqué par une rature et la mention « exclus depuis 2025 », pas seulement par une couleur grise.

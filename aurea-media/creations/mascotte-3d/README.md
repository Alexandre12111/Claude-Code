# Gaspard 3D, modèle de référence

Ce modèle 3D articulé est désormais la source unique du personnage. Il reprend les proportions et les couleurs du dessin 2D (`episodes/ep01-v3/gaspard.js`), à l'échelle 1 unité = 100 px.

## Fichiers

| Fichier | Rôle |
|---|---|
| `gaspard3d.js` | Le modèle : géométrie, squelette, matériaux, poses et expressions (module ES, Three.js 0.160). |
| `visionneuse.html` | Visionneuse interactive : rotation, poses de référence, réglages du visage et des mains, export `.glb`. Elle charge Three.js depuis jsDelivr. |
| `gaspard.glb` | Export standard (pose de repos), qui s'ouvre dans Blender, Unity ou Unreal. Les contours cartoon n'y sont pas, car ils relèvent du rendu. |
| `planche-3d-*.png` | Planches de contrôle : tour complet, poses, mains, expressions. |
| `planche-3d.html` | Page qui produit ces planches (utilisée par les scripts de vérification). |

Pour ouvrir la visionneuse, il faut servir le dossier `creations/` avec un petit serveur local, par exemple `python3 -m http.server`, puis ouvrir `mascotte-3d/visionneuse.html`. Un double clic sur le fichier ne suffit pas : les navigateurs bloquent les modules ES ouverts en `file://`.

## Repère

- **Origine :** les pieds au sol en (0, 0, 0).
- **Axes :** y vers le haut, z vers la caméra (côté visage).
- **Côtés :** « D » désigne la droite du spectateur, « G » sa gauche.

## Dimensions clés (unités)

| Élément | Valeur |
|---|---|
| Corps (haricot) | largeur 3,3, hauteur 4,7, aplati à 0,8 en profondeur |
| Épaules | (±1,5 ; 2,5) |
| Bras | humérus 0,88, avant-bras 0,84 |
| Yeux | centre (±0,5 ; 3,7), rayons 0,40 × 0,48 |
| Museau | centre y 2,74, rayons 0,96 × 0,62 |
| Casque | base y 4,52, dôme 1,56 × 1,42, bord jusqu'à environ 2,0 |

## Squelette (55 os)

| Zone | Os |
|---|---|
| Tronc | `racine` → `bassin` → `torse` (penche le haut du corps) → `tete`, plus `machoire` (ouverture de la bouche) |
| Tête | `oeilG`, `oeilD` (pivots des yeux et des paupières), `casque` (décalage pour les rebonds) |
| Bras | `epauleX` → `coudeX` → `poignetX` → `mainX` |
| Doigts | `doigtN_1X`, `doigtN_2X`, `doigtN_3X` : N = 1 auriculaire … 4 index, 3 phalanges par doigt |
| Pouce | `pouce1X` → `pouce2X` → `pouce3X` (opposable) |
| Jambes | `hancheX` → `genouX` → `chevilleX` (pied toujours à plat) |
| Queue | `queue0` → `queue1` → `queue2` (pagaie souple) |

X vaut G ou D.

**Maillages souples (skinning) :**
- le corps et le ventre suivent le bassin et le torse ;
- les bras suivent l'épaule et le coude, avec un pli doux au coude ;
- les jambes et la queue suivent leurs trois os.

Les doigts, les yeux, le museau, le casque et la ceinture sont rigides et rattachés à leur os.

## Utilisation

```js
import * as THREE from 'three';
import { creerGaspard, POSES } from './gaspard3d.js';

const g = creerGaspard(THREE);          // { racine, os, poser, poserMain, poserVisage, ... }
scene.add(g.racine);

g.poser({
  brasD: { main: [1.55, 3.95, .9], coude: 1 },        // cible de la main (IK), coude 1 = vers l'extérieur, -1 = vers le bas
  mainD: { forme: 'tient', objet: 'tel' },            // formes : ouverte, salut, poing, tient, pointe, pouce
  visage: { bouche: 'ouvert', sourcils: .6, regardX: .6 },
  penche: 0, pencheAvant: 0, queue: 0, casque: 0,
});
g.poser(POSES.declic);                               // poses de référence : content, panique, epuise, declic, joie, telephone, pouce
```

## Paramètres de `poser(p)`

| Paramètre | Effet |
|---|---|
| `position`, `rotationY` | placement du personnage |
| `penche`, `pencheAvant`, `etire` | inclinaison latérale ou avant du haut du corps, étirement vertical (squash and stretch) |
| `brasG` / `brasD` | `main: [x, y, z]` cible du poignet, `coude: 1 \| -1` |
| `mainG` / `mainD` | `forme`, `fermeture` (0 à 1), `doigts: [f1..f4]`, `pouceFermeture`, `objet` (`tel`, `tasse`, `truelle`) |
| poignet (dans `mainX`) | `flexion` (±1,2 rad), `inclinaison` (±0,6), `torsion` (±1,6), ou une orientation imposée : `doigtsVers: [x, y, z]`, `paumeVers: [x, y, z]` |
| `piedG` / `piedD` | cible de la cheville |
| `queue`, `queueLeve`, `queueCourbe` | orientation et courbure de la queue |
| `casque` | décalage vertical du casque (follow-through) |
| `telCeinture` | téléphone visible dans la poche, masqué automatiquement quand il est en main |
| `visage` | `bouche` (sourire, ouvert, rire, o, cri, moue, plat, grimace, baille), `paupieres`, `cligne`, `sourcils`, `froncer`, `regardX`, `regardY`, `oeil`, `dents` |

**Yeux :** l'œil, la pupille et les paupières vivent dans un même repère étiré où tout est une sphère. La paupière glisse donc exactement sur l'œil, sans jamais y entrer.

**Bouche articulée :** le museau du haut porte le nez et les deux dents, toujours fixées sous lui comme chez un vrai castor. Une mâchoire inférieure (os `machoire`) pivote pour ouvrir la bouche et dévoile une cavité sombre et une langue, placées derrière les dents. Les dents suivent donc forcément la bouche, sans aucune forme plaquée. L'ouverture dépend de l'expression ; le paramètre `visage.ouverture` (en radians) la règle librement.

## Poignet

L'os `mainX` est une vraie articulation. Chaque forme a son orientation naturelle :
- **salut :** paume vers l'avant, doigts en l'air ;
- **téléphone :** contre la joue, vertical ;
- **tasse :** tenue de côté ;
- **pouce levé :** poing de côté, pouce vers le haut.

Les réglages fins (flexion, inclinaison, torsion) s'ajoutent par-dessus. L'angle entre les doigts et l'avant-bras est limité à 80° pour que le poignet ne se casse jamais.

## Le bras ne traverse jamais le corps

Trois mécanismes s'enchaînent :
1. **Cible repoussée :** elle sort du volume du corps et ne dépasse pas le milieu du ventre vers l'autre côté. Elle passe devant le ventre plutôt que derrière.
2. **Recherche du coude :** si le coude ou les segments entrent dans le corps, le plan du coude tourne autour de l'axe épaule–main jusqu'à l'orientation libre la plus proche.
3. **Repli :** si aucune orientation n'est libre, la cible glisse progressivement vers un point sûr sur le côté.

La racine de l'épaule reste ancrée dans le flanc, comme sur le dessin 2D.

Contrôle : 800 positions de main aléatoires, puis 500 avec le corps penché au hasard, donnent 0 bras à plus de 3 cm dans le corps.

**Conséquence :** une main visée tout droit au-dessus de la tête est ramenée de côté, bras en V, car un bras vertical traverserait la tête.

## Règles automatiques (reprises de la 2D)

- **Index :** c'est toujours le doigt voisin du pouce qui se tend en forme `pointe`, jamais le majeur.
- **Pouce levé :** en forme `pouce`, le pouce vise toujours le haut de l'écran, quel que soit l'angle du bras.
- **Tasse :** elle reste droite quelle que soit la rotation de la main.
- **Objets tenus :** chaque objet a sa forme mathématique dans le repère de la main (le téléphone est une boîte, la tasse et le manche de truelle sont des cylindres). Pour chaque doigt et pour le pouce, un solveur cherche la courbure des trois phalanges qui touche la surface sans la traverser. Mesure : contact à 5 mm près, aucune pénétration. Le calcul est fait une fois par objet, puis mis en cache.
  - **Téléphone :** tenu à la verticale à l'oreille, écran vers la joue, doigts refermés sur son bord arrière.
  - **Tasse :** reste droite, doigts enroulés autour.
  - **Truelle :** manche dans le poing fermé.
- **Bras tendu :** quand il est presque tendu, il s'étire légèrement (jusqu'à 6 %) au lieu de se verrouiller sèchement.

## Modifier le personnage

- **Couleurs :** objet `COULEURS` en tête de `gaspard3d.js`. Le même code hexadécimal que la 2D garde les deux versions alignées.
- **Proportions :** objet `DIM`, ainsi que les profils de rayon des membres (`rBras`, jambes, queue) dans `creerGaspard`.
- **Nouvelle expression de bouche :** ajouter une entrée dans `defB`. Elle apparaît aussitôt dans la visionneuse.
- **Nouvelle pose :** ajouter une entrée dans `POSES`, ou utiliser « Copier la pose (JSON) » dans la visionneuse.

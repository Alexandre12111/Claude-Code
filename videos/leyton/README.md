# Vidéo de présentation du site Leyton

Vidéo motion design qui présente le site [leyton.com/fr](https://leyton.com/fr/) : l'animation de particules du site, une visite commentée de l'accueil, les chiffres clés, les six expertises, la page À propos et la version mobile.

| Fichier | Détail |
| --- | --- |
| `leyton-presentation-site.mp4` | 1 min 17, 1920 × 1080, 60 i/s, H.264, 26 Mo, sans piste audio |
| `leyton-presentation-site-miniature.jpg` | Miniature 1920 × 1080 pour LinkedIn, YouTube ou une intégration web |

## Déroulé

| Temps | Séquence |
| --- | --- |
| 0:00 | Les particules de la vidéo hero du site forment le symbole Leyton, puis le logo et la promesse « Votre partenaire pour l'innovation et la performance financière » |
| 0:08 | Le site apparaît dans un navigateur, sur le hero de l'accueil |
| 0:11 | Accueil commenté : promesse, expertises |
| 0:18 | Plongée dans les chiffres clés, avec compteurs animés |
| 0:25 | Méthode, résultats clients, références, analyses |
| 0:39 | Les six expertises en carrousel |
| 0:52 | À propos : histoire depuis 1997, IA avec Leyton CognitX, partenariat France SailGP Team |
| 1:03 | Version mobile sur trois smartphones |
| 1:10 | Appel à l'action « Parler à un expert », puis logo et adresse du site |

La vidéo est pensée pour une lecture sans le son (lecture automatique sur LinkedIn ou sur un site) : tout le message passe par les textes à l'écran.

## Modifier et régénérer

Les textes (légendes, chiffres, expertises) se trouvent en tête de `composition/main.js`. Le rythme se règle dans la fonction `build()` du même fichier.

```bash
cd videos/leyton
npm install
pip install pillow numpy imageio-ffmpeg
npm run build      # environ 10 minutes : captures, rendu en 60 i/s, encodage
npm run preview    # aperçu en temps réel dans le navigateur, après un premier build
```

`scripts/build.sh` enchaîne cinq étapes :

1. téléchargement des médias et de la charte depuis leyton.com (vidéos du hero, logo, icônes, police Nohemi) ;
2. capture des pages en tranches d'écran assemblées (`capture.js`, `stitch.py`), pour que chaque section soit photographiée une fois ses images et animations chargées ;
3. préparation des ressources de la composition (`prepare_assets.py`) ;
4. rendu image par image de `composition/index.html` avec Playwright (`render.js`) ;
5. encodage H.264 avec ffmpeg.

Les captures, polices et images générées (`work/`, `composition/assets/`) ne sont pas versionnées.

## Sources

Contenus, chiffres et visuels issus de leyton.com/fr (septembre 2026). Charte : bleu `#012D48`, orange `#EB6739`, polices Nohemi pour les titres et Montserrat pour les textes.

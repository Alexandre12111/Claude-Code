# Vidéo motion design CIR, Leyton (V1)

Vidéo explicative de 45 secondes sur le Crédit d'Impôt Recherche, style « main qui dessine », aux couleurs de la charte Leyton (Guide CIR 2026).

## Livrables

| Fichier | Usage |
|---|---|
| `CIR_Leyton_V1.mp4` | Vidéo 1920×1080, 30 i/s, H.264 + AAC, sous-titres incrustés, `faststart` pour le web |
| `CIR_Leyton_V1.vtt` | Sous-titres WebVTT (balise `<track>` sur le site) |
| `CIR_Leyton_V1.srt` | Sous-titres SRT (YouTube, LinkedIn, Vimeo) |
| `CIR_Leyton_V1_poster.jpg` | Image d'aperçu (attribut `poster` du lecteur) |

## Charte appliquée

* Bleu marine `#002C49`, orange `#F0682F`, fonds gris bleuté dégradés comme les pages du guide
* Titres en Nohemi Bold, textes en Montserrat
* Logo vectoriel extrait du guide, panneaux arrondis sur fond marine et grands numéros de chapitre en filigrane

## Intégration sur le site

```html
<video controls playsinline muted preload="metadata" poster="CIR_Leyton_V1_poster.jpg" width="1920" height="1080">
  <source src="CIR_Leyton_V1.mp4" type="video/mp4">
  <track kind="captions" src="CIR_Leyton_V1.vtt" srclang="fr" label="Français">
</video>
```

## Régénérer la vidéo

Le dossier `source/` contient le script, l'animation (HTML/SVG rendu image par image avec Playwright), la synthèse de la voix (edge-tts, voix `fr-FR-RemyMultilingualNeural`) et le mixage audio (musique et bruitages générés en Python). Les polices Nohemi et Montserrat sont à placer dans `source/fonts/`.

1. `python3 tts.py fr-FR-RemyMultilingualNeural "+4%"`
2. `python3 build_timeline.py`
3. `node render.js full`
4. `python3 audio.py`, puis assemblage avec ffmpeg (voir l'historique du commit)

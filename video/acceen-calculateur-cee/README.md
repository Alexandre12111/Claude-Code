# Vidéo LinkedIn : calculateur CEE Acceen

Vidéo de 20 s (1080 x 1350, 4:5, 30 i/s, H.264) qui montre le calculateur
https://acceen.com/calculateur-cee/ : « 5 questions, un résultat immédiat ».

## Livrables

- `out/acceen-calculateur-cee.mp4` : vidéo finale, sans musique
- `out/miniature.png` : miniature (image 0)

## Modifier les textes ou le timing

Tout se règle dans `src/config.ts` :

- `TEXTS` : accroche et appel à l'action
- `SUBTITLES` : sous-titres (début, fin, numéro en jaune, texte de 6 mots maximum)
- `PROFILE` : profil factice saisi dans le formulaire
- `RESULT_VALUE` : montant affiché
- `SCENES`, `CLICKS`, `TYPING`, `ANSWERS` : timing en images (30 images = 1 s)

L'interface du calculateur (`src/calc/`) reprend les libellés, couleurs, polices et
styles CSS du site. Aucun formulaire n'est soumis : tout est recréé dans Remotion.

## Commandes

```bash
npm install
```

```bash
npm run studio
```

```bash
npm run render
```

```bash
npm run still
```

Dans un environnement sans Chrome téléchargeable, ajoutez
`--browser-executable=/chemin/vers/chrome-headless-shell` aux commandes de rendu.

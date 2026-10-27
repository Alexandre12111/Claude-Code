# Performance (mesures de laboratoire)

**Score : 72/100**

PageSpeed Insights a refusé la requête (quota journalier atteint). Mesures faites avec Chromium, cache de page contourné (pire cas), mobile bridé en 4G avec processeur ralenti x4.

| Page | Appareil | TTFB | FCP | LCP | CLS | Élément LCP |
|---|---|---|---|---|---|---|
| / | mobile | 1,73 s | 4,23 s | 4,23 s | 0 | H1 |
| / | ordinateur | 0,74 s | 1,53 s | 1,53 s | 0 | H1 |
| /en/ | mobile | 0,73 s | 2,10 s | 3,63 s | 0 | paragraphe d'intro |
| /creation-site-vitrine/ | mobile | 0,71 s | 1,89 s | 3,15 s | 0 | paragraphe d'intro |
| /realisations/ | mobile | 0,74 s | 1,75 s | 1,75 s | 0 | H1 |

- CLS à 0 partout : images dimensionnées, pas de décalage.
- Le paragraphe d'introduction restait invisible tant que le script d'animation n'avait pas tourné, ce qui retardait le LCP mobile de 1,2 à 1,5 s. Corrigé dans 3.1.2 : le texte du haut de page s'affiche tout de suite.
- TTFB en cache : 0,5 à 0,8 s. Sans cache : jusqu'à 1,7 s. Un hébergement IONOS plus rapide ou un cache objet aiderait.
- INP : non mesurable sans données terrain (pas encore assez de trafic dans CrUX).

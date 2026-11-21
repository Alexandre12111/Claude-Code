# 5. Performance (55 / 100)

Mesures Lighthouse (laboratoire, connexion mobile simulée) :

| | Mobile | Ordinateur |
|---|---|---|
| Score performance | 64 | 71 |
| LCP (affichage du plus grand élément) | 6,9 s | 3,1 s |
| FCP (premier affichage) | 3,8 s | 2,0 s |
| CLS (stabilité) | 0 | 0 |
| TBT (blocage) | 140 ms | 0 ms |
| Poids total | 2,4 Mo | 2,4 Mo |
| Requêtes | 71 | 71 |

**Causes identifiées**

| Gravité | Constat | Correction |
|---|---|---|
| Élevée | Les 13 photos de filières (affichées en 400 × 300) pèsent de 53 à 252 Ko chacune : 1,7 Mo gaspillés sur mobile. | Les redimensionner à 800 px de large (qualité WebP 75) : environ 40 Ko chacune. |
| Moyenne | Elementor, Essential Addons et Royal Addons chargent leurs CSS et JS sur toutes les pages, plus Font Awesome et deux familles Google Fonts (Roboto, Roboto Slab, 18 graisses chacune) qui ne sont pas utilisées par le design. | Désactiver Elementor et ses modules après le retour à l'en‑tête du thème. |
| Moyenne | Travail du processeur élevé sur mobile (9 s simulées, surtout styles et rendu) : animations GSAP, effets de flou (`backdrop-filter`) et vidéo d'arrière‑plan. | Alléger les effets sur mobile (flou désactivé sous 768 px, animations de défilement réduites). |
| Faible | jQuery et jQuery Migrate chargés pour Elementor uniquement. | Disparaissent avec Elementor. |

Gain attendu : plus de 1,5 Mo en moins sur mobile. Le LCP réel est à confirmer après correction (Search Console, rapport Signaux Web essentiels, dès que Google aura assez de visites).

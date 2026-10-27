# Audit SEO complet : aurea-media.fr

Date : 27 octobre 2026 (troisième passage) · Thème en ligne : Aurea 3.1.5 · 77 adresses explorées

## Score de santé SEO : 91/100

| Catégorie | Poids | 1er passage | 2e passage | Aujourd'hui |
|---|---|---|---|---|
| SEO technique | 22 % | 78 | 92 | 94 |
| Contenu et E-E-A-T | 23 % | 82 | 84 | 85 |
| SEO on-page | 20 % | 90 | 92 | 94 |
| Données structurées | 10 % | 88 | 88 | 88 |
| Performance | 10 % | 72 | 96 | 94 |
| Moteurs de réponse IA | 10 % | 88 | 88 | 88 |
| Images | 5 % | 92 | 94 | 94 |

## État du site
- 77 adresses, toutes en 200 ; aucune page orpheline ; aucune page indexable hors sitemap ; hreflang tous réciproques.
- Titres, descriptions, H1 et canonical conformes sur toutes les pages indexables.
- Avis Google connectés (API Places) : note et nombre d'avis à jour, carrousel de 4 avis sur l'accueil.
- Mentions légales et confidentialité complètes, en français et en anglais.

## Lighthouse (site en ligne, laboratoire)
| Page | Appareil | Perf. | Access. | Bonnes pratiques | SEO | LCP | CLS |
|---|---|---|---|---|---|---|---|
| / | mobile | 98 | 96 | 100 | 100 | 2,2 s | 0 |
| /en/ | mobile | 97 | 96 | 100 | 100 | 2,1 s | 0 |
| /creation-site-vitrine/ | mobile | 96 | 100 | 100 | 100 | 2,7 s | 0 |
| /creation-site-vitrine/ | ordinateur | 98 | 100 | 100 | 100 | 0,8 s | 0 |
| /prix-site-vitrine/ (article) | mobile | 76 à 82 | 96 | 100 | 100 | 2,3 s | 0 |
| /prix-site-vitrine/ (article) | ordinateur | 99 | 96 | 100 | 100 | 0,6 s | 0 |

SEO Lighthouse : 100 sur toutes les pages testées. Après le thème 3.1.6, accessibilité 100 sur l'accueil et les articles (vérifié en local).

## Points relevés
| Priorité | Constat | Statut |
|---|---|---|
| Basse | Points du carrousel d'avis trop petits au doigt (8 px, minimum 24 px) | Corrigé dans 3.1.6 (zone tactile de 24 px) |
| Basse | Lien de l'encart auteur des articles : contraste 2,9 au lieu de 4,5 | Corrigé dans 3.1.6 |
| Moyenne | Articles sur mobile : temps de blocage très variable en laboratoire (Lighthouse 640 à 960 ms, mesures directes 100 à 220 ms), sans script tiers ni cause unique identifiée | À surveiller avec les données réelles (INP) dans la Search Console |
| Moyenne | 4 avis seulement dans le carrousel (limite de l'API Google) | Ajouter 4 ou 5 avis à la main dans Aurea Studio |
| Moyenne | Blog jeune : 6 articles, pack 1 programmé à partir du 3 novembre | En cours |
| Moyenne | Peu de mentions de la marque sur des sites tiers | Prochains sites, annuaires, Google Business Profile |

## Limites
- PageSpeed Insights hors quota : Lighthouse 12 lancé en local sur le site en ligne.
- Pas de données terrain (CrUX), ni Search Console, ni liens entrants.

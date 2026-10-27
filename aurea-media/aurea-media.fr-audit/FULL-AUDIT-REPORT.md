# Audit SEO complet : aurea-media.fr

Date : 27 octobre 2026 (second passage) · Thème en ligne : Aurea 3.1.3 · 77 adresses explorées

## Score de santé SEO : 89/100 (83 au premier passage)

| Catégorie | Poids | 1er passage | Aujourd'hui |
|---|---|---|---|
| SEO technique | 22 % | 78 | 92 |
| Contenu et E-E-A-T | 23 % | 82 | 84 |
| SEO on-page | 20 % | 90 | 92 |
| Données structurées | 10 % | 88 | 88 |
| Performance | 10 % | 72 | 96 |
| Moteurs de réponse IA | 10 % | 88 | 88 |
| Images | 5 % | 92 | 94 |

Avec le thème 3.1.4 (accessibilité et liens), le score attendu est de 90 à 91.

## Corrigé depuis le premier passage
- Anciennes adresses d'articles : redirection 301 vers le bon article (vérifiée sur les 6).
- Page vide « Accueil - English » : retirée du site et du sitemap.
- Rubriques du blog : noindex, follow.
- LCP mobile : le texte du premier écran s'affiche sans attendre l'animation.
- Mentions légales et confidentialité complètes (SASU, capital, RCS, TVA, IONOS, Independent Analytics), en français et en anglais.
- GTranslate retiré : un seul système de langues (Polylang).

## Lighthouse (laboratoire, site en ligne)
| Page | Appareil | Performance | Accessibilité | Bonnes pratiques | SEO | LCP | CLS |
|---|---|---|---|---|---|---|---|
| / | mobile | 98 | 95 | 100 | 92 | 2,2 s | 0 |
| / | ordinateur | 99 | 95 | 100 | 92 | 0,7 s | 0 |
| /en/ | mobile | 97 | 95 | 100 | 85 | 2,1 s | 0 |
| /creation-site-vitrine/ | mobile | 96 | 98 | 100 | 92 | 2,5 s | 0 |

Après le thème 3.1.4, en local : accessibilité 100 et SEO 100 sur l'accueil et la page site vitrine.

## Points restants
| Priorité | Constat | Statut |
|---|---|---|
| Moyenne | Lien sans adresse dans le faux résultat Google (illustration) : « liens non explorables » | Corrigé dans 3.1.4 |
| Moyenne | Version anglaise : liens « Learn more » sans texte descriptif | Corrigé dans 3.1.4 (texte complémentaire pour lecteurs d'écran et robots) |
| Basse | Contraste insuffisant sur les adresses des barres de navigateur | Corrigé dans 3.1.4 |
| Basse | Ordre des titres : H4 du pied de page après un H2 | Corrigé dans 3.1.4 (H3) |
| Moyenne | Blog jeune : 6 articles, pack 1 programmé à partir du 3 novembre | À poursuivre |
| Moyenne | Peu de mentions de la marque sur des sites tiers | Phase 3 |
| Info | Articles à la racine (/titre/) au lieu de /conseils/titre/ | Accepté : les deux formes redirigent correctement |
| Info | robots.txt avec deux groupes « User-agent: * » (WP-Optimize) | Sans effet |

## Limites
- PageSpeed Insights hors quota : Lighthouse 12 lancé en local sur le site en ligne (même moteur).
- Pas de données terrain (CrUX), ni Search Console, ni liens entrants.

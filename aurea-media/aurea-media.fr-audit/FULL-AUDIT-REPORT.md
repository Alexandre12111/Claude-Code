# Audit SEO complet : aurea-media.fr

Date : 27 octobre 2026 · Thème en ligne : Aurea 3.1.1 · 78 adresses explorées (sitemap + liens internes)

## Score de santé SEO : 83/100

| Catégorie | Poids | Score |
|---|---|---|
| SEO technique | 22 % | 78 |
| Contenu et E-E-A-T | 23 % | 82 |
| SEO on-page | 20 % | 90 |
| Données structurées | 10 % | 88 |
| Performance | 10 % | 72 |
| Moteurs de réponse IA | 10 % | 88 |
| Images | 5 % | 92 |

Type d'activité détecté : agence web (services aux entreprises, zone France et Europe, ancrage local Paris et Hauts-de-Seine).

## Les 5 problèmes principaux
1. **Critique** : la structure des permaliens a changé. Les articles sont passés de /conseils/titre/ à /titre/.
2. **Critique** : les 6 anciennes adresses d'articles (celles que Google connaît) redirigent vers des pages services au lieu des articles. Par exemple /conseils/prix-site-vitrine/ mène à /tarifs/.
3. **Haute** : une page anglaise vide « Accueil - English » (/en/accueil-english/), créée par l'assistant de Polylang, est indexable et dans le sitemap.
4. **Moyenne** : LCP mobile de 3,1 à 4,2 s en laboratoire, retardé par l'animation d'apparition du texte du haut de page.
5. **Moyenne** : rubriques du blog indexables sans contenu propre ni description.

## Les 5 gains rapides
1. Remettre les permaliens sur /conseils/%postname%/ (un bouton dans Installation Aurea).
2. Installer le thème 3.1.2 (redirections par slug, page vide retirée, rubriques en noindex, LCP).
3. Compléter les mentions légales (capital social, ville du RCS, outil de mesure d'audience).
4. Renvoyer le sitemap dans la Search Console après la correction des permaliens.
5. Désactiver GTranslate s'il est encore actif (doublon avec Polylang).

## Ce qui fonctionne très bien
- Toutes les pages répondent en 200, aucune erreur, aucun lien interne cassé.
- Titres et descriptions dans les bonnes longueurs, sans doublon, un seul H1 par page, canonical justes.
- Version anglaise propre : 22 paires reliées par hreflang (fr, en, x-default), accueil sur /en/.
- Données structurées complètes et valides (agence, fondateur, services avec prix, études de cas, articles, fil d'Ariane).
- CLS à 0, images WebP dimensionnées, alt partout.
- llms.txt bilingue, robots d'IA autorisés, blocs « En bref » faciles à citer.

## Détail par catégorie
Voir le dossier `findings/` : technique.md, contenu.md, on-page.md, schema.md, performance.md, geo.md, images.md.

## Limites de cet audit
- PageSpeed Insights indisponible (quota journalier) : performances mesurées en laboratoire avec Chromium.
- Pas d'accès à la Search Console ni à GA4 : pas de données d'indexation ni de trafic réel.
- Pas d'API de liens entrants configurée : profil de backlinks non analysé.

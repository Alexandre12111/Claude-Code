# Rapports détaillés de l'audit SEO d'aurea-media.fr

Rapports bruts des sept audits spécialisés menés le 25 septembre 2026 avec le skill SEO, sur les 50 adresses du site (sitemap, catégories, pages légales, test de 404). La synthèse et les arbitrages entre ces rapports sont dans [`../RAPPORT-SEO-GEO.md`](../RAPPORT-SEO-GEO.md) : en cas de divergence, c'est la synthèse qui fait foi.

| Fichier | Volet | Score |
|---|---|---|
| [`technique.md`](technique.md) | Crawl, indexation, en-têtes, performance, exigences pour le nouveau thème | 59/100 |
| [`schema.md`](schema.md) | Données structurées, modèles JSON-LD | 60/100 |
| [`contenu.md`](contenu.md) | Qualité, E-E-A-T, cannibalisation, balises Rank Math | 52/100 (E-E-A-T 41) |
| [`geo.md`](geo.md) | Visibilité dans les moteurs de réponse IA, llms.txt, mentions | 54/100 |
| [`local.md`](local.md) | Google Business Profile, NAP, avis, citations | 43/100 |
| [`sxo.md`](sxo.md) | Expérience de recherche, personas, structure des pages | par page |
| [`strategie.md`](strategie.md) | Mots-clés, clusters, architecture, maillage, calendrier | sans objet |

Notes de lecture :

- Les rapports citent un fichier de contexte et des données de travail (`scratchpad/…` : crawl, relevés de SERP) qui ne sont pas versionnés.
- Le volet technique mentionne « seul l'accueil a du JSON-LD » : c'est une erreur de l'extraction initiale, corrigée par les volets GEO et contenu (45 pages ont du JSON-LD).
- Les éléments marqués « non vérifié » n'ont pas pu être contrôlés depuis l'environnement d'audit (Search Console, Google Business Profile, certains annuaires, données de volume).

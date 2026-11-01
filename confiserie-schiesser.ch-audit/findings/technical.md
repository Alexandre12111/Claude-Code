# Technique : 62 / 100

## Ce qui fonctionne
* HTTPS et redirections http/www propres
* canonical correcte sur les 20 pages
* hreflang DE/FR/EN réciproques
* Rendu côté serveur, pages indexables

## Constats
### Plan du site introuvable (Critical)
/sitemap_index.xml répond 404 ; /sitemap.xml et /wp-sitemap.xml y redirigent.

**Recommandation :** Activer le module Plan du site de Rank Math, réenregistrer les permaliens, envoyer le sitemap à Search Console et Bing.

### robots.txt incomplet (High)
Seulement « Crawl-delay: 10 », pas de ligne Sitemap. Bing respecte le délai.

**Recommandation :** Retirer Crawl-delay, ajouter « Sitemap: https://confiserie-schiesser.ch/sitemap_index.xml ».

### Langue déclarée de-DE / fr-FR (Low)
Le site vise la Suisse (allemand de Suisse).

**Recommandation :** Locale de_CH et fr_CH dans Polylang.

### En-têtes de sécurité (Low)
Seul HSTS est présent.

**Recommandation :** Ajouter X-Content-Type-Options, Referrer-Policy, X-Frame-Options chez l'hébergeur.

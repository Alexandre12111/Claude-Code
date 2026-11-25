# 1. SEO technique (72 / 100)

**Points positifs**
* HTTPS partout, `http://` et `www.` redirigés en 301 vers `https://dualtech-event.eu/`.
* robots.txt correct : seul `/wp-admin/` est bloqué, le plan du site est déclaré.
* Plan du site Rank Math accessible (`/sitemap_index.xml`, et `/sitemap.xml` redirige bien).
* Balises canonical présentes et correctes sur les 13 pages, robots `index, follow`, `lang="fr-FR"`.
* Temps de réponse serveur bon : environ 150 ms pour le document HTML (cache serveur actif, `x-cache: HIT`).
* `xmlrpc.php` bloqué (403), liste des utilisateurs de l'API protégée (401).

**Problèmes**

| Gravité | Constat | Correction |
|---|---|---|
| Élevée | Lien interne vers `/salon-industrie-duale/` (404) sur les 15 pages explorées, dans le pied de page Elementor. | Rétablir le pied de page du thème, ou corriger le lien vers `/l-evenement/`. Ajouter aussi une redirection 301 `/salon-industrie-duale/` vers `/l-evenement/` (Rank Math → Redirections), car l'ancienne adresse a pu être partagée. |
| Moyenne | 7 fichiers de Royal Elementor Addons appelés sur chaque page renvoient une erreur 404 (`frontend.min.css`, `particles.js`, `jarallax.min.js`…). L'extension semble supprimée en partie alors que ses réglages sont encore actifs. | Désactiver proprement Royal Elementor Addons et ses gabarits d'en‑tête et de pied de page. |
| Moyenne | Plan du site pollué : `?wpr_templates=user-header-header`, `?wpr_templates=user-footer-footer`, `/category/uncategorized/`. | Rank Math → Plan du site : désactiver le type « wpr_templates » et les catégories. Supprimer les gabarits inutilisés. |
| Moyenne | Article d'exemple `/hello-world/` publié et indexable (contenu anglais par défaut, schéma BlogPosting avec auteur « admin »). | Supprimer l'article, et l'URL renverra une 404 propre. |
| Faible | Aucun en‑tête de sécurité HTTP (HSTS, X‑Content‑Type‑Options, X‑Frame‑Options, Referrer‑Policy). | À ajouter dans le `.htaccess` chez IONOS (exemple dans le plan d'action). |
| Faible | Durée de cache des images, polices et scripts : 30 minutes seulement. | Règles `Expires` ou `Cache-Control` d'un an sur `/wp-content/` dans le `.htaccess`. |
| Info | Flux RSS actifs (`/feed/`) alors que le site n'a pas d'actualités. | Sans conséquence, peut rester. |

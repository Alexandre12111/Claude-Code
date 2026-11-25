# SEO technique

**Score : 78/100**

## Ce qui fonctionne
- 78 adresses explorées, toutes en 200, aucune erreur 404 ni chaîne de redirection interne.
- HTTPS avec HSTS, en-têtes de sécurité (nosniff, X-Frame-Options, Referrer-Policy, Permissions-Policy).
- Sitemap Rank Math propre : 75 adresses (7 articles, 44 pages FR et EN, 24 réalisations), toutes en 200.
- Balises canonical correctes sur toutes les pages.
- Version anglaise : hreflang fr, en et x-default réciproques sur les 22 paires de pages.
- Cache de page WP-Optimize actif, CSS compressé (gzip) et mis en cache 28 jours.

## Problèmes
| Priorité | Constat | Preuve | Correction |
|---|---|---|---|
| Critique | Structure des permaliens modifiée : les articles sont passés de /conseils/titre/ à /titre/ | sitemap : https://aurea-media.fr/prix-site-vitrine/ ; Réglages → Permaliens | Remettre /conseils/%postname%/ (Apparence → Installation Aurea → Réglages WordPress) |
| Critique | Les 6 anciennes adresses d'articles redirigent vers des pages services au lieu des articles | /conseils/prix-site-vitrine/ → 301 /tarifs/ ; /conseils/woocommerce-ou-shopify/ → /creation-site-ecommerce/ | Corrigé dans le thème 3.1.2 : une adresse introuvable est d'abord recherchée par son slug |
| Haute | Page anglaise vide « Accueil - English » indexable et dans le sitemap | /en/accueil-english/ : 5 mots, sans description, hreflang non réciproque | Corrigé dans 3.1.2 : mise à la corbeille automatique |
| Moyenne | Rubriques du blog sans description ni contenu propre (97 à 136 mots) | /conseils/categorie/prix/ | Corrigé dans 3.1.2 : noindex, follow |
| Basse | robots.txt avec deux groupes « User-agent: * » (ajout de WP-Optimize) | robots.txt | Sans effet bloquant (Google fusionne les groupes) |
| Basse | Pas d'en-tête Content-Security-Policy | en-têtes HTTP | Optionnel |

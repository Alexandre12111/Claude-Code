# Plan d'action SEO : dualtech-event.eu

Score actuel : **74 / 100**. Score visé après les phases 1 et 2 : environ 88 / 100.

## Phase 1 : corrections critiques (cette semaine, avant l'événement du 26 novembre)

| # | Priorité | Action | Où | Temps |
|---|---|---|---|---|
| 1 | Élevée | **Retirer l'en‑tête et le pied de page Elementor** pour rétablir ceux du thème (menu complet visible sur ordinateur, liens Mentions légales, Politique de confidentialité, Gérer les cookies, lien LinkedIn DualTech, lien L'événement correct). | Royal Addons → Theme Builder (ou Modèles Elementor) → en‑tête et pied de page « user-header » et « user-footer » : supprimer la condition d'affichage « Tout le site », puis vider le cache. | 10 min |
| 2 | Élevée | **Redirection 301** `/salon-industrie-duale/` vers `/l-evenement/` (ancienne adresse partagée). | Rank Math → Redirections → Ajouter. | 2 min |
| 3 | Élevée | **Lier la page L'événement** : elle revient dans le menu avec l'en‑tête du thème ; ajouter aussi un lien depuis l'accueil. | Point 1, puis Pages → Accueil. | 5 min |
| 4 | Moyenne | **Supprimer les pages parasites** : article « Hello world! », catégorie « Uncategorized » (renommer en « Actualités » si besoin), gabarits Elementor inutilisés. | Articles, Articles → Catégories, Modèles. | 5 min |
| 5 | Moyenne | **Nettoyer le plan du site** : désactiver les catégories et le type « wpr_templates ». | Rank Math → Réglages du plan du site. | 3 min |
| 6 | Moyenne | **Désactiver Elementor, Essential Addons et Royal Elementor Addons** une fois l'en‑tête rétabli (aucune page n'en dépend plus). Supprime 7 erreurs 404, Font Awesome, Google Fonts Roboto et jQuery. | Extensions. Vérifier chaque page ensuite. | 10 min |
| 7 | Moyenne | **Déclarer GTranslate** dans le bandeau cookies et la politique de confidentialité (traduction par Google), ou le charger seulement après accord. | Thème (je peux le faire). | 30 min |

## Phase 2 : améliorations à fort impact (semaines 2 et 3)

| # | Priorité | Action | Détail |
|---|---|---|---|
| 8 | Élevée | **Alléger les photos de filières** | 13 images de 53 à 252 Ko affichées en 400 × 300 : les redimensionner à 800 px (WebP qualité 75, environ 40 Ko). Plus de 1,5 Mo économisés sur mobile. Correction dans le thème. |
| 9 | Moyenne | **Alléger les effets sur mobile** | Désactiver le flou d'arrière‑plan (`backdrop-filter`) et réduire les animations de défilement sous 768 px. |
| 10 | Moyenne | **Mise en cache longue des fichiers statiques** | `.htaccess` chez IONOS (voir plus bas). |
| 11 | Faible | **Aligner les mots clés Rank Math sur les titres** | « workshops DualTech », « infos pratiques DualTech », « mentions légales DualTech » ; reprendre le mot clé dans la description quand c'est naturel. Viser 80 ou plus, pas 100. |
| 12 | Faible | **Espaces entre les lignes des H1** | Correction d'une ligne dans le gabarit du thème. |
| 13 | Faible | **En‑têtes de sécurité** | `.htaccess` (voir plus bas). |

### Code à ajouter au `.htaccess` (avant `# BEGIN WordPress`)

```apache
# Sécurité
<IfModule mod_headers.c>
  Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
</IfModule>

# Cache des fichiers statiques (1 an : les fichiers du thème portent un numéro de version)
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType video/mp4 "access plus 1 year"
</IfModule>
```

## Phase 3 : contenu et notoriété (mois 2)

| # | Action | Détail |
|---|---|---|
| 14 | Page ou section Presse | Communiqué, kit média (logos, photos HD), contact presse : facilite les articles et les liens entrants. |
| 15 | Liens des partenaires | Demander aux 36 partenaires et aux intervenants un lien vers dualtech-event.eu depuis leur site ou leur actualité LinkedIn. |
| 16 | Enrichir la page Contact | Délais de réponse par sujet, contact presse, adresse postale de l'INAS. |
| 17 | Logo dans les données structurées | Ajouter `logo` à l'Organization INAS. |
| 18 | Après l'événement | Mettre à jour la rétrospective 2026 (photos, chiffres, vidéos) et passer l'Event en événement passé ; préparer la page de l'édition suivante sur la même adresse pour garder l'historique SEO. |

## Phase 4 : suivi (en continu)

* Connecter Google Search Console (propriété de domaine), soumettre `https://dualtech-event.eu/sitemap_index.xml`, surveiller les erreurs 404 et les Signaux Web essentiels.
* Tester l'accueil et le Programme dans l'outil de test des résultats enrichis de Google après chaque modification du programme.
* Relancer cet audit après la phase 1 pour mesurer le gain.

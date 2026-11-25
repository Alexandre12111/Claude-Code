# Audit SEO complet : dualtech-event.eu

Audit réalisé le 3 novembre 2026 sur le site en ligne (thème DualTech actif). Exploration de 16 URL (plan du site et liens internes), Lighthouse 12 en local (mobile et ordinateur), contrôle du rendu avec Chromium. L'API PageSpeed de Google était à court de quota : pas de données terrain CrUX, les mesures de performance sont des mesures de laboratoire.

## Synthèse

**Score de santé SEO : 74 / 100**

| Catégorie | Poids | Score |
|---|---|---|
| SEO technique | 22 % | 72 |
| Qualité du contenu | 23 % | 78 |
| SEO on‑page | 20 % | 75 |
| Données structurées | 10 % | 90 |
| Performance (Core Web Vitals) | 10 % | 55 |
| Préparation recherche IA | 10 % | 75 |
| Images | 5 % | 65 |

**Type de site** : site d'événement professionnel (B2B, institutionnel), un seul événement daté (26 novembre 2026, Campus Cyber, La Défense), sans blog.

**Ce qui fonctionne bien** : titres et descriptions uniques et de bonne longueur sur toutes les pages, un seul H1 par page, URL courtes, HTTPS et redirections correctes, robots.txt propre, plan du site Rank Math, données structurées Event très complètes (34 intervenants, programme, lieu, organisateur, offre gratuite), aucun décalage de mise en page (CLS 0), Lighthouse SEO 100 / 100.

**La cause principale des problèmes** : l'ancien en‑tête et l'ancien pied de page faits avec Elementor (blocs `dt-hdr` et `dt-ftr`, gabarits Royal Elementor Addons) s'affichent encore à la place de ceux du thème. Cela crée à lui seul le lien cassé présent sur toutes les pages, la page L'événement orpheline, l'absence de liens vers les pages légales et « Gérer les cookies », le menu caché sur ordinateur, et une bonne partie du poids inutile (Elementor, Font Awesome, Google Fonts Roboto, 7 fichiers en erreur 404).

### Les 5 problèmes prioritaires

1. **Lien cassé sur toutes les pages** : « L'évènement » dans le pied de page pointe vers `/salon-industrie-duale/`, qui renvoie une erreur 404 (15 pages concernées).
2. **Page L'événement orpheline** : `/l-evenement/` ne reçoit aucun lien interne. Google ne la trouve que par le plan du site, et elle ne bénéficie d'aucun maillage.
3. **Pages légales et gestion des cookies introuvables** : le pied de page Elementor ne contient ni « Mentions légales », ni « Politique de confidentialité », ni « Gérer les cookies » (0 lien entrant vers les mentions légales). C'est aussi une obligation légale (LCEN, RGPD).
4. **Performance mobile 64 / 100** : LCP de 6,9 s en laboratoire (objectif : moins de 2,5 s), 2,4 Mo transférés, dont 1,7 Mo d'images de filières trop grandes pour leur taille d'affichage.
5. **Pages parasites indexables** : article « Hello world! » (texte anglais par défaut de WordPress), catégorie « Uncategorized » et deux gabarits Elementor (`?wpr_templates=…`) présents dans le plan du site.

### Les 5 actions rapides

1. Désactiver l'en‑tête et le pied de page Elementor (conditions d'affichage des gabarits Royal Addons), ce qui rétablit ceux du thème : corrige les points 1, 2 et 3 d'un coup.
2. Supprimer l'article « Hello world! » et la catégorie vide, retirer les catégories et les gabarits du plan du site dans Rank Math.
3. Désactiver Elementor et ses modules (Essential Addons, Royal Elementor Addons) une fois l'en‑tête rétabli : moins de CSS et de JS, plus de Google Fonts Roboto.
4. Redimensionner les 13 photos de filières à 800 px de large (elles sont affichées en 400 × 300).
5. Ajouter GTranslate au bandeau cookies et à la politique de confidentialité, ou le charger seulement après accord.

---

## 1. SEO technique (72 / 100)

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

## 2. Qualité du contenu (78 / 100)

**Points positifs**
* Contenu original et précis sur chaque page : de 172 mots (Contact) à 1 346 mots (Intervenants). Accueil 1 001 mots, Programme 999, Prix 933.
* Signaux de confiance (E‑E‑A‑T) solides : organisateur identifié (INAS), soutien institutionnel (Paris Ouest La Défense), 34 intervenants nommés avec fonction et organisation, 36 partenaires, rétrospective de la première édition au Sénat, mentions légales complètes.
* Informations pratiques concrètes : adresse, transports, hôtels, FAQ.

**Problèmes**

| Gravité | Constat | Correction |
|---|---|---|
| Élevée | La page L'événement (603 mots, présentation des formats et des temps forts) n'est liée depuis aucune page. | Rétablir le menu du thème (qui la contient) et ajouter un lien depuis l'accueil (« Découvrir le programme de la journée »). |
| Moyenne | Article « Hello world! » en anglais visible publiquement. | Supprimer. |
| Faible | La page Contact est courte (172 mots). | Ajouter quelques lignes : délais de réponse par sujet, contact presse, adresse postale de l'INAS. |
| Faible | Pas de page ou section « Presse » (communiqués, kit média, logos). | Utile pour obtenir des liens de médias et de partenaires. |

## 3. SEO on‑page (75 / 100)

**Points positifs**
* 13 titres uniques de 40 à 63 caractères, mot clé principal en début de titre.
* 13 descriptions uniques de 138 à 162 caractères.
* Un seul H1 par page, structure H2 cohérente.
* URL courtes et lisibles.

**Problèmes**

| Gravité | Constat | Correction |
|---|---|---|
| Élevée | Sur ordinateur, l'en‑tête Elementor cache tout le menu derrière un bouton « burger » : les liens principaux ne sont pas visibles, ce qui affaiblit leur poids et l'ergonomie. L'en‑tête du thème affiche le menu complet. | Rétablir l'en‑tête du thème. |
| Faible | Dans le HTML brut, les lignes des titres H1 sont collées (« Le rendez-vousincontournable de… », « Mentionslégales »). Le navigateur les affiche bien sur des lignes séparées, mais un outil qui lit le HTML sans le rendre (certains robots, extraits de partage) lit des mots collés. | Ajouter un espace entre les lignes dans le gabarit du titre (correction d'une ligne dans le thème). |
| Faible | Le mot clé principal Rank Math n'apparaît pas tel quel dans la description de 9 pages (par exemple « workshop DualTech » alors que le titre dit « Workshops DualTech », « DualTech Campus Cyber » absent du titre de la page Infos pratiques). | Aligner les mots clés Rank Math sur les titres : « workshops DualTech », « infos pratiques DualTech », « mentions légales DualTech »… Score Rank Math visé : 80 ou plus. |
| Faible | Le lien LinkedIn du pied de page pointe vers `https://www.linkedin.com/` et non vers la page DualTech. | Corrigé automatiquement avec le pied de page du thème. |
| Faible | Ordre des titres non séquentiel (un H4 du pied de page Elementor). | Corrigé avec le pied de page du thème. |

## 4. Données structurées (90 / 100)

**Points positifs**
* JSON‑LD valide sur toutes les pages, un seul graphe par page (pas de doublon avec Rank Math).
* Event complet : nom, dates de début et de fin, lieu (Place avec adresse), statut, mode présentiel, image, description, offre gratuite (Offer à 0 €), organisateur, 34 intervenants (`performer`), programme en sous‑événements.
* Organization (INAS), GovernmentOrganization (Paris Ouest La Défense), WebSite, BreadcrumbList, ContactPage, CollectionPage avec la liste des intervenants.

**Problèmes**

| Gravité | Constat | Correction |
|---|---|---|
| Faible | `/hello-world/` et `/category/uncategorized/` produisent un schéma BlogPosting et CollectionPage sans intérêt. | Disparaît avec la suppression de ces pages. |
| Info | Le logo de l'organisateur n'est pas déclaré (`logo` de l'Organization INAS). | Ajouter l'URL du logo INAS dans le graphe. |

Test conseillé : https://search.google.com/test/rich-results sur l'accueil et la page Programme.

## 5. Performance (55 / 100)

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

## 6. Images (65 / 100)

* Attributs `alt` : présents sur toutes les images (les images décoratives ont un `alt` vide, ce qui est correct).
* Dimensions `width` et `height` : présentes partout sauf le logo PNG du pied de page Elementor (`Screenshot_2026-04-03_153343-removebg-preview.png`, nom de fichier non descriptif, 48 Ko de trop).
* Formats : WebP pour toutes les images du thème.
* Problème principal : photos de filières surdimensionnées (voir Performance).
* L'attribut `loading="lazy"` est écrit deux fois sur certaines images (ajouté par WordPress en plus de celui du thème) : sans effet visible, mais HTML invalide.

## 7. Préparation à la recherche IA (75 / 100)

* Robots d'IA (GPTBot, ClaudeBot, PerplexityBot, Google‑Extended) non bloqués : le contenu peut être cité.
* Contenu bien structuré : FAQ, programme horaire, liste d'intervenants, chiffres clés, données structurées riches. Bonnes réponses citables (date, lieu, gratuité, nombre de places).
* `llms.txt` absent (optionnel, ignoré par Google ; utile seulement pour certains assistants).
* Peu de signaux externes connus (mentions de presse, page Wikipédia de l'INAS, liens de partenaires vers le site) : à développer avec les partenaires (chaque partenaire peut lier vers dualtech-event.eu depuis sa page actualité).

## 8. Conformité (hors score)

* Bandeau cookies du thème actif et fonctionnel.
* **GTranslate** est installé : en version gratuite, la traduction passe par Google dans le navigateur du visiteur. Il n'est pas encore déclaré dans le bandeau ni dans la politique de confidentialité.
* **Google Fonts** (Roboto) est chargé depuis les serveurs de Google par Elementor, sans accord : transfert d'adresse IP à Google. Disparaît avec la désactivation d'Elementor.
* Pages légales en ligne mais non liées depuis le pied de page (voir point 3 de la synthèse).

## Annexes

* Plan d'action détaillé : `ACTION-PLAN.md`
* Constats par catégorie : `findings/`
* Captures : `screenshots/`
* Données brutes : `crawl-data.json`, `lighthouse.json`, `audit-data.json`

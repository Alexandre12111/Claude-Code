# Plan d'action SEO : confiserie-schiesser.ch

Score : 75 / 100 au 1er passage, **86 / 100 après les corrections du 1er novembre**. Les actions 1 à 9 et 13 sont faites (✓).

Qui fait quoi :
* **Admin** : réglage dans WordPress, sans code.
* **Thème** : correction dans le thème Schiesser (livrée par le développeur).
* **Import** : fichier d'import « Export Schiesser ».
* **Hors site** : Google Business Profile et annuaires.

## Phase 1 : critique (cette semaine)

| # | Action | Qui | Temps | Comment vérifier |
|---|---|---|---|---|
| 1 | ✓ **Réactiver le plan du site** : Rank Math → Tableau de bord → Modules → « Plan du site » activé ; Rank Math → Plan du site : pages et produits Tea Room inclus. Puis Réglages → Permaliens → « Enregistrer ». | Admin | 5 min | `confiserie-schiesser.ch/sitemap_index.xml` affiche une liste (et non « Seite nicht gefunden ») |
| 2 | ✓ **Envoyer le plan du site** à Google Search Console et à Bing Webmaster Tools (propriété de domaine). | Admin | 10 min | État « Opération effectuée » et nombre de pages découvertes ≈ 20 |
| 3 | ✓ **robots.txt** : retirer `Crawl-delay: 10`, ajouter `Sitemap: https://confiserie-schiesser.ch/sitemap_index.xml`. Le fichier semble servi par l'hébergeur, hors de WordPress : à modifier chez l'hébergeur, ou via Rank Math → Réglages généraux → Modifier robots.txt s'il est éditable. | Admin ou hébergeur | 5 min | `confiserie-schiesser.ch/robots.txt` |

## Phase 2 : fort impact (semaines 2 et 3)

| # | Action | Qui | Temps | Comment vérifier |
|---|---|---|---|---|
| 4 | ✓ **Corriger les 38 liens français** qui passent par une redirection (`/boutique/` → `/fr/boutique/`…). | Import | 5 min | Aucun lien vers une 301 dans les pages FR |
| 5 | ✓ **Image de partage par défaut** : Rank Math → Titres & métas → Global → « Image OpenGraph » (photo de la façade ou de la vitrine, 1200 × 630). Idéalement une image par page (onglet Social de Rank Math). | Admin | 10 à 30 min | Aperçu d'un lien collé dans WhatsApp |
| 6 | ✓ **Logo officiel dans la fiche établissement** : Rank Math → Titres & métas → SEO local → Logo : `schiesser-logo.png` (carré, logo droit). | Admin | 5 min | Test des résultats enrichis de Google : logo correct |
| 7 | **En partie fait : profils liés (`sameAs`)** : Instagram ajouté par l’import du 2 novembre ; reste à saisir dans Réglages maison → Fiche établissement les adresses Google Business Profile, Instagram, Facebook, TripAdvisor. | Admin | 10 min | `sameAs` présent dans le code de l'accueil |
| 8 | ✓ **Photo de banque d'images du Tea Room** (Unsplash, 507 Ko, chargée inutilement) : la retirer de la feuille de style. | Thème | 5 min | Page Tea Room sous 400 Ko |
| 9 | ✓ **Lien de plan mal encodé** (`&amp;` dans `hasMap`) : le décoder dans le thème. | Thème | 5 min | `hasMap` contient `&query=` |

## Phase 3 : contenu et autorité (mois 2)

| # | Action | Qui | Temps |
|---|---|---|---|
| 10 | **Google Business Profile** : catégories principale et secondaire (Confiserie, Salon de thé), photos récentes, horaires des jours fériés, réponses à tous les avis, lien vers le site par langue. | Hors site | 1 h, puis 15 min par semaine |
| 11 | **Relier la note Google** : Réglages maison → Fiche établissement → clé Places API et identifiant du lieu. Le site affichera les avis et une note vérifiable. | Admin | 20 min |
| 12 | **Citations locales** cohérentes (même nom, adresse, téléphone) : local.ch, search.ch, TripAdvisor, Basel Tourismus, Falstaff ou Gault&Millau si pertinent. | Hors site | 2 h |
| 13 | ✓ **Textes alternatifs** des grandes photos (accueil, Firmengeschenke, contact FR) : décrire la scène (« Vitrine de la Confiserie Schiesser au Marktplatz »). | Admin ou import | 15 min |
| 14 | **Photos des « moments » du Tea Room en JPEG ou WebP** au lieu de PNG. | Admin | 20 min |
| 15 | **Langue Suisse** : Polylang → Langues : locale `de_CH` (et `fr_CH`) pour que `lang`, `hreflang` et `og:locale` indiquent la Suisse. | Admin | 10 min |

## Phase 4 : suivi (en continu)
* Search Console : couverture (20 pages indexées par langue), requêtes « confiserie basel », « tea room basel », « basler läckerli kaufen », clics par langue.
* Bing Webmaster Tools : indexation, après le retrait du `Crawl-delay`.
* Refaire cet audit dans 2 mois. Pour une comparaison automatique, enregistrer un point de référence avec `/seo drift baseline` une fois les phases 1 et 2 faites.
* En-têtes de sécurité (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`) : à demander à l'hébergeur, priorité basse.

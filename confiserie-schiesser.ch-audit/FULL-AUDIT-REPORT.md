# Audit SEO : confiserie-schiesser.ch

Date : 1er novembre 2026. Thème Schiesser 0.23.1 en ligne.
Méthode : exploration complète du site en suivant ses liens (27 adresses, robots.txt respecté : 10 s entre deux requêtes), analyse du code de chaque page, mesures de laboratoire dans Chromium (ordinateur et mobile).

Ce qui n'a pas pu être mesuré :
* Pas de données terrain de Google : PageSpeed Insights avait dépassé son quota du jour, et aucun accès Search Console ni GA4 n'est configuré.
* Pas de données de liens entrants (Moz ou Bing non configurés).
* Le module Claude SEO refuse le proxy réseau de l'environnement : l'exploration a été faite avec curl et Chromium, les mêmes contrôles à la main.

## Résumé

**Score de santé SEO : 75 / 100** (bonne base, un défaut important à corriger tout de suite).

Type d'activité détecté : **commerce local** (confiserie et salon de thé avec adresse, horaires, téléphone, plan), site vitrine en trois langues (DE, FR, EN), sans boutique en ligne active.

| Catégorie | Poids | Score |
|---|---|---|
| Technique | 22 % | 62 |
| Contenu | 23 % | 80 |
| SEO de page (on-page) | 20 % | 82 |
| Données structurées | 10 % | 72 |
| Performance | 10 % | 85 (laboratoire) |
| Préparation à la recherche IA | 10 % | 75 |
| Images | 5 % | 70 |

### Les 5 problèmes principaux
1. **Le plan du site (sitemap) n'existe pas** : `/sitemap_index.xml` répond 404, et `/sitemap.xml` y redirige. Google et Bing n'ont pas de liste des pages.
2. **Liens internes français vers des adresses redirigées** : les 7 pages françaises pointent vers `/boutique/`, `/salon-de-the/`, `/nous-visiter/`… (301 vers `/fr/…`). 38 liens au total, de 3 à 7 par page.
3. **Aucune image de partage (og:image)** sur les 20 pages : un lien partagé sur WhatsApp, Facebook ou LinkedIn s'affiche sans photo.
4. **Fiche établissement (schema) incomplète** : logo = l'icône du site (« ChatGPT-Image… »), pas de profils liés (`sameAs` : Google, Instagram, TripAdvisor…), lien de plan mal encodé (`&amp;`).
5. **robots.txt** : `Crawl-delay: 10` ralentit fortement Bing (et donc Copilot), et il ne signale aucun sitemap.

### Les 5 gains rapides
1. Réactiver le sitemap de Rank Math et enregistrer les permaliens (5 min).
2. Ajouter la ligne `Sitemap:` et retirer `Crawl-delay` du robots.txt (5 min).
3. Corriger les 38 liens français par un fichier d'import (préparé sur demande, 5 min d'import).
4. Choisir une image de partage par défaut dans Rank Math (Titres & métas → Global → Image OpenGraph) (5 min).
5. Renseigner les réseaux sociaux et le logo dans Rank Math et dans Réglages maison (10 min).

## Ce qui fonctionne bien
* **20 pages indexables**, une seule balise H1 par page, aucun titre en double, `canonical` correcte partout, HTTPS et redirections `http`/`www` propres.
* **Titres SEO** de 48 à 60 caractères et **méta-descriptions** de 133 à 160 caractères sur toutes les pages, mot-clé principal au début.
* **hreflang** DE/FR/EN réciproques sur toutes les pages.
* **Données structurées riches** : `Bakery` + `CafeOrCoffeeShop` avec adresse, coordonnées GPS, horaires, carte du Tea Room (`hasMenu`), fil d'Ariane sur les sous-pages, `AboutPage` pour l'histoire.
* **Performance de laboratoire excellente** : affichage principal (LCP) entre 1,3 et 2,2 s, aucun décalage de mise en page (CLS 0 à 0,006), pages légères (260 à 360 Ko, 17 à 25 requêtes), aucun débordement horizontal sur mobile.
* **Contenu substantiel et local** : 330 à 2 100 mots par page, nombreuses questions fréquentes, histoire documentée par des archives, adresse et horaires partout.

## Technique (62)

| Constat | Gravité |
|---|---|
| `sitemap_index.xml` en 404 alors que `sitemap.xml` et `wp-sitemap.xml` y redirigent. Le module « Plan du site » de Rank Math est probablement désactivé, ou les permaliens n'ont pas été réenregistrés. | **Critique** |
| robots.txt : seulement `Crawl-delay: 10`, pas de ligne `Sitemap:`. Google ignore la consigne, mais Bing la suit : au plus 8 640 pages par jour, et des recrawls lents. | Haute |
| Les accueils FR et EN sont `/fr/accueil/` et `/en/home/` : `/fr/` et `/en/` redirigent dessus. Cela fonctionne, mais l'adresse de la langue elle-même serait plus propre (réglage Polylang). | Basse |
| En-têtes de sécurité : seul HSTS est présent (sans `includeSubDomains`). Manquent `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options` ou `frame-ancestors`, `Permissions-Policy`. | Basse |
| Langue déclarée `de-DE`, `fr-FR`, `en-GB` alors que le site vise la Suisse (textes en allemand de Suisse). Recommandé : `de-CH` (et `fr-CH`) dans Polylang. | Basse |
| Pas de `x-default` dans les hreflang. | Info |

## Contenu (80)
* Pages riches et spécifiques au lieu ; vocabulaire local (Läckerli, Marktplatz, Rathaus). Aucun contenu mince.
* E-E-A-T : l'expérience et l'ancienneté (1870, archives, Rathstübli) sont très bien démontrées. Les signaux d'autorité externes manquent sur le site : avis clients (la note Google n'est pas reliée), presse, prix, mentions.
* Les pages Kontakt (330 à 360 mots) sont les plus courtes. C'est correct pour une page de contact.

## SEO de page (82)
* Titres, descriptions et H1 conformes sur les 20 pages.
* **Liens internes redirigés** : 38 liens dans le texte des 7 pages françaises (Accueil 6, Confiserie 7, Salon de thé 6, Histoire 6, Nous visiter 3, Contact 4, Cadeaux 6). Ils sont écrits sans le préfixe `/fr/`. **Moyenne.**
* **Aucune balise `og:image`** : le partage sur les réseaux sociaux se fait sans photo. **Moyenne.**

## Données structurées (72)
* Bonne base `Bakery` / `CafeOrCoffeeShop` avec NAP, géo, horaires et carte.
* **Logo** : c'est l'icône du site (`cropped-ChatGPT-Image-Oct-1-2026…png`, 512 px) qui est utilisée. Mettre le logo officiel (`schiesser-logo.png`, carré 800 px, logo droit) dans Rank Math → Titres & métas → SEO local. **Moyenne.**
* **`sameAs` absent** : aucun lien vers la fiche Google, Instagram, Facebook ou TripAdvisor. C'est important pour que Google et les assistants IA relient l'entité. **Moyenne.**
* **`hasMap` mal encodé** : `…?api=1&amp;query=…`, le `&amp;` est échappé deux fois. Correction dans le thème (une ligne). **Basse.**
* `FAQPage` sur la plupart des pages : Google n'affiche plus de résultats enrichis FAQ depuis 2026. Ce n'est pas une erreur, ne rien supprimer. **Info.**
* Pas de note (`aggregateRating`) : elle ne peut venir que d'avis réellement affichés sur le site. Relier la fiche Google dans Réglages maison → Fiche établissement. **Basse.**

## Performance (85, mesures de laboratoire)

| Page | Appareil | LCP | CLS | Poids |
|---|---|---|---|---|
| Accueil | Ordinateur | 2,2 s | 0 | 362 Ko |
| Accueil | Mobile | 1,3 s | 0 | 259 Ko |
| Confiserie | Ordinateur / mobile | 1,7 / 1,6 s | 0,006 / 0 | 361 / 259 Ko |
| Tea Room | Ordinateur / mobile | 1,6 / 1,3 s | 0,001 / 0 | 775 / 775 Ko |

* **Tea Room : une photo de banque d'images (Unsplash, 507 Ko) est téléchargée depuis un site tiers** pour le fond de la « montée au Tea Room ». C'est un reste de la maquette, invisible car recouvert par vos photos. Elle double presque le poids de la page. Correction dans le thème (une ligne). **Moyenne.**
* Les 5 photos des « moments » du Tea Room sont en PNG (`Pause-gourmande…png`…) : en JPEG ou WebP, elles seraient 3 à 5 fois plus légères. **Basse.**
* Le cache des pages est très court (`max-age=3`) : c'est normal avec WP Super Cache, qui sert des pages statiques.
* Données terrain (CrUX) : non disponibles. Probablement trop peu de trafic pour l'instant.

## Images (70)
* Alt présents sur les photos de contenu.
* La grande photo (Hero) de l'accueil, de Firmengeschenke et du contact FR a un alt vide. Elle est porteuse de sens (vitrine, Tea Room) : ajouter un alt descriptif. **Basse.**
* Vignettes de la galerie Confiserie sans alt : acceptable, car ce sont des doublons de la grande photo. **Info.**
* Pas de WebP ni d'AVIF : WordPress peut les générer (Instant Images ou un module de conversion). **Basse.**

## Préparation à la recherche IA (75)
* Aucun robot IA bloqué. Le contenu est rendu côté serveur (pas besoin de JavaScript) et structuré en questions et réponses : bonne « citabilité ».
* Il manque les signaux d'entité (`sameAs`, fiche Google liée) et les mentions externes.
* Pas de `llms.txt` : facultatif, ignoré par Google. **Info.**
* `Crawl-delay` freine le crawler de Bing, qui alimente Copilot et une partie de ChatGPT Search. **Haute** (voir Technique).

## SEO local
* NAP identique partout : Marktplatz 19, 4051 Basel, +41 61 261 60 77.
* Itinéraires, horaires en direct, carte et questions locales : très bon.
* À vérifier hors site : fiche Google Business Profile (catégories « Confiserie » et « Salon de thé », photos, horaires des jours fériés, réponses aux avis) et cohérence sur local.ch, search.ch, TripAdvisor, Basel Tourismus.

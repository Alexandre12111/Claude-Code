# Audit technique et performance — aurea-media.fr

Date de l'audit : 25 septembre 2026. Périmètre : crawlabilité, indexabilité, architecture des URL et maillage interne, sécurité, mobile, Core Web Vitals et poids de page, rendu JavaScript, IndexNow. Méthode : analyse du crawl local (50 URL, dossier `crawl/`), lecture du code source, vérifications live (robots.txt, sitemap, en-têtes HTTP, poids des ressources, redirections). Le test PageSpeed Insights (API publique) a répondu 429 (quota journalier épuisé sur la clé partagée) et l'API CrUX a répondu 403 (accès sans clé refusé) : aucun résultat de terrain n'a donc pu être récupéré aujourd'hui. Les constats Core Web Vitals ci‑dessous reposent sur l'inspection du code source (poids des ressources, blocage du rendu, dimensions d'images, polices) et doivent être confirmés par un test PageSpeed Insights ou CrUX dès qu'une clé API sera disponible.

## Score technique global : 59/100

Le site est indexable, servi en HTTPS, avec un sitemap XML valide et un contenu entièrement lisible sans JavaScript (bon socle). Le score est pénalisé par une fausse page 404 qui redirige tout vers l'accueil, des en‑têtes de sécurité absents sur les pages servies depuis le cache (la majorité du trafic), quatre pages d'archives orphelines et dupliquées avec des pages services, et un poids de page élevé porté par 21 feuilles de style et 18 scripts bloquants, cinq familles de polices chargées par trois mécanismes différents, et zéro image au format moderne (WebP/AVIF).

| Catégorie | Statut | Constat principal |
|---|---|---|
| Crawlabilité | Échec partiel | Fausse page 404 (redirection 301 vers l'accueil), archives orphelines indexables |
| Indexabilité | Passable | Canonicals et robots corrects sur les 42 URL du sitemap, mais 4 pages hors sitemap dupliquent des pages services |
| Sécurité | Échec partiel | HTTPS et HSTS corrects en théorie, mais en‑têtes de sécurité absents sur les pages en cache |
| Architecture des URL | Passable | URL propres, mais confusion articles/pages et cannibalisation probable sur plusieurs couples de pages |
| Mobile | Passe | Viewport correct, mise en page responsive, pas d'anomalie détectée dans le code |
| Core Web Vitals / poids | Échec partiel | CSS et JS non consolidés, polices redondantes, images sans format moderne ni `srcset` |
| Rendu JavaScript | Passe | Contenu entièrement rendu côté serveur, aucune dépendance JS pour l'indexation |
| Données structurées | Échec partiel | Détection seulement ici (voir audit dédié `schema.md` pour la validation complète) |
| IndexNow | Échec | Aucune preuve d'implémentation trouvée |

---

## 1. Crawlabilité et indexabilité

### 1.1 [Critique] La page 404 n'existe pas : tout renvoie vers l'accueil en 301

Constat : toute URL inexistante (testé sur `/cette-page-nexiste-pas-404/` et sur une URL aléatoire `/xyz-totally-random-path-12345/`) reçoit un `HTTP/2 301` avec `location: https://aurea-media.fr` et l'en‑tête `x-redirect-by: Rank Math`. Il n'existe donc aucune vraie page 404 sur le site : Rank Math est configuré pour rediriger automatiquement les URL introuvables vers l'accueil.

Pourquoi c'est un problème : Google traite ce comportement comme une « redirection douce » (soft 404). Cela signale un manque de fiabilité de l'ensemble des URL du site, dilue le budget de crawl sur des URL qui ne devraient pas exister, et empêche de détecter de vrais liens cassés (internes ou externes) puisqu'ils semblent tous « fonctionner ». Pour l'utilisateur, atterrir sur l'accueil sans explication après un lien cassé est aussi une mauvaise expérience.

Action : dans la refonte, remplacer cette redirection générale par une vraie page 404 en HTTP 404, avec un design cohérent, un message clair, un lien vers l'accueil, la page contact et 2 ou 3 pages services mises en avant. Conserver des redirections 301 ciblées uniquement pour les anciennes URL identifiées (voir section « Exigences techniques »), jamais une redirection générique de toute URL inconnue vers l'accueil.

Comment vérifier : tester plusieurs URL aléatoires inexistantes après mise en ligne et confirmer un code `HTTP 404` (pas 301, pas 200) via `curl -I`. Contrôler aussi Google Search Console (rapport Pages) pour voir apparaître ces URL en « Introuvable (404) » plutôt qu'en redirection.

### 1.2 [Haute] Quatre pages d'archives orphelines, indexables et absentes du sitemap, qui dupliquent des pages services

Constat : le crawl révèle quatre URL d'archives de catégorie WordPress qui ne sont jamais liées par aucune autre page du site (0 lien entrant hors leur propre balise de fil d'Ariane) :
- `/creation-site-vitrine/` (aucun H1, méta description de 364 caractères)
- `/creation-site-ecommerce/` (aucun H1, méta description de 295 caractères)
- `/creation-site-web/` (aucun H1)
- `/google-my-business-seo-local/` (aucun H1, méta description de 293 caractères, catégorie nommée « SEO (Référecement) », faute de frappe déjà signalée)

Ces quatre pages ne figurent dans aucun des deux sitemaps déclarés (`page-sitemap.xml` : 9 URL, `post-sitemap.xml` : 33 URL, total 42, vérifié via `sitemap_index.xml` en direct). Pourtant leur balise `robots` est `follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large` : elles sont indexables. Ce sont des pages d'archive de catégorie auto‑générées par WordPress, jamais construites comme des pages à part entière.

Pourquoi c'est un problème : `creation-site-vitrine` recoupe directement la page service `creation-de-site-vitrine-paris`, `creation-site-ecommerce` recoupe `creation-de-site-e-commerce-paris`, et `creation-site-web` recoupe `agence-web-a-paris`. Deux pages qui ciblent la même intention de recherche se cannibalisent dans les résultats Google : c'est en général la page la moins pertinente qui ressort, ou aucune des deux ne se positionne clairement. De plus, une page indexable sans H1, orpheline et absente du sitemap est un signal de qualité faible pour Google.

Action : dans la refonte, supprimer purement et simplement les archives de catégorie du plan du site (ou les passer en `noindex, follow` si elles doivent subsister techniquement), et rediriger en 301 les quatre URL actuelles vers leur page service correspondante la plus proche (`creation-site-vitrine` vers `creation-de-site-vitrine-paris`, `creation-site-ecommerce` vers `creation-de-site-e-commerce-paris`, `creation-site-web` vers `agence-web-a-paris` ou `nos-services`, `google-my-business-seo-local` vers l'article hub le plus proche ou `nos-conseils-web`).

Comment vérifier : `curl -I` sur les quatre URL doit renvoyer un `301` vers la bonne cible après refonte. Dans Search Console, suivre la désindexation des quatre anciennes URL sur 4 à 8 semaines.

### 1.3 [Basse] Aucune page orpheline parmi les 42 URL réelles du site (bon point)

Constat : en reconstruisant le graphe de liens internes à partir des 50 pages du crawl (BFS depuis l'accueil), les 42 URL déclarées dans les sitemaps sont toutes atteignables en 1 ou 2 clics depuis l'accueil. Aucune des vraies pages ou vrais articles n'est orpheline. Seules les quatre archives de catégorie décrites en 1.2 le sont.

Pourquoi c'est bien : cela signifie que le maillage interne actuel, bien qu'améliorable (voir section 2), distribue correctement le jus de lien vers l'ensemble du contenu réel. C'est une base saine à conserver dans la refonte.

Action : dans la refonte, maintenir cette règle : toute page ou article publié doit être lié depuis au moins une page de niveau 1 (accueil, page hub de catégorie, ou menu) au moment de sa mise en ligne, jamais seulement depuis le sitemap.

Comment vérifier : réexécuter un calcul de profondeur de clic après refonte (crawl avec Screaming Frog ou équivalent) et confirmer 0 page orpheline parmi les URL indexables.

### 1.4 [Moyenne] robots.txt fonctionnel mais dupliqué et minimal

Constat (contenu récupéré en direct) :
```
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://aurea-media.fr/sitemap_index.xml

User-agent: *
Disallow: /wp-content/uploads/wpo/wpo-plugins-tables-list.json
```
Le bloc `User-agent: *` apparaît deux fois (un artefact issu de deux plugins qui écrivent chacun dans le fichier, probablement Rank Math et l'extension de cache WPO). Cela reste valide pour Googlebot (les règles se cumulent) mais n'est pas propre. Le `Sitemap:` déclaré est correct et pointe vers `sitemap_index.xml`, qui référence bien `post-sitemap.xml` et `page-sitemap.xml`.

Pourquoi c'est un problème mineur : un robots.txt avec des blocs dupliqués est un signe de configuration non maîtrisée et peut dérouter certains robots moins tolérants que Googlebot (Bingbot, robots d'IA). Ce n'est pas bloquant.

Action : dans la refonte, écrire un robots.txt unique et volontaire (un seul bloc `User-agent: *`), avec la ligne `Sitemap:` en premier ou dernier, et ajouter explicitement les règles souhaitées pour les robots d'IA (voir la section dédiée du skill SEO technique pour les tokens à jour).

Comment vérifier : `curl https://aurea-media.fr/robots.txt` doit renvoyer un fichier avec un seul bloc `User-agent: *` et être validé sans erreur dans le rapport « robots.txt » de Search Console.

### 1.5 [Basse] GTranslate : pas de duplication détectée via paramètres d'URL

Constat : le widget GTranslate (`gtranslate/js/float.js`) est chargé sur toutes les pages. Un test en direct avec des paramètres typiques de Google Translate (`?_x_tr_sl=fr&_x_tr_tl=en&_x_tr_hl=fr`) renvoie un `200` avec le même contenu que la page normale (pas de page de traduction serveur générée, pas de contenu dupliqué indexable détecté côté serveur). Le widget agit uniquement côté client (redirection vers le sous‑domaine `translate.goog` de Google au clic), ce qui limite le risque de pages dupliquées indexées sous le nom de domaine du site.

Pourquoi le signaler quand même : le sous‑domaine `*.translate.goog` généré par Google Translate peut occasionnellement être exploré et indexé par des moteurs tiers en dehors du contrôle du site, avec un contenu dégradé (traduction automatique). Ce risque est faible et hors du contrôle direct du site, mais autant l'anticiper.

Action : si le nouveau thème conserve GTranslate (ou un équivalent), ne pas ajouter de code serveur qui génère des URL de traduction propres au domaine. Si la cible internationale (clients à Genève, Lisbonne, Montréal, Dubaï mentionnés dans le contexte) justifie un vrai multilingue à terme, prévoir plutôt de vraies pages traduites avec hreflang plutôt que la traduction automatique du navigateur, qui n'apporte aucune valeur SEO.

Comment vérifier : rechercher `site:translate.goog aurea-media.fr` dans Google pour confirmer l'absence d'indexation parasite.

### 1.6 [Basse] Pagination du blog correcte

Constat : `/nos-conseils-web/` (page d'index du blog) génère une pagination WordPress standard (`/nos-conseils-web/page/2/`) avec un `rel="next"` détecté dans le code source.

Action : conserver ce mécanisme dans la refonte, en s'assurant que chaque page de pagination reste indexable (`index, follow`) et n'a pas de canonical erroné pointant vers la page 1.

Comment vérifier : contrôler la balise canonical de `/nos-conseils-web/page/2/` après refonte : elle doit pointer vers elle‑même, pas vers `/nos-conseils-web/`.

---

## 2. Architecture des URL et maillage interne

### 2.1 [Haute] Cannibalisation probable entre articles et pages services

Constat, à partir des titres, H1 et profondeur de clic mesurés :
- `prix-site-vitrine-paris` (article, profondeur 2, H1 « Combien coûte un site vitrine à Paris en 2026 ? ») cible une intention très proche de `creation-de-site-vitrine-paris` (page service, profondeur 1, H1 « Création de site vitrine à Paris pour convertir vos visiteurs. »).
- `creation-site-vitrine-tpe-paris` (article, profondeur 1, lien direct depuis l'accueil) a un nom de slug et un H1 (« Création site vitrine TPE Paris : le budget qu'il faut vraiment prévoir. ») presque identiques à la page service `creation-de-site-vitrine-paris`.
- `choisir-agence-web-paris` (article) et `agence-web-a-paris` (page service) partagent le même champ sémantique, avec un risque plus faible car l'article est clairement informationnel (« Comment choisir... ») contre transactionnel pour la page.

Pourquoi c'est un problème : deux contenus proches en concurrence sur les mêmes mots‑clés se cannibalisent dans les résultats Google au lieu de se renforcer. Cela dilue l'autorité au lieu de la concentrer sur une seule page pivot par intention de recherche, contrairement à la règle « un mot‑clé principal par page » déjà appliquée par le client dans Rank Math.

Action : dans la refonte, définir explicitement une seule page cible par intention de recherche. Transformer les articles à forte proximité transactionnelle (`prix-site-vitrine-paris`, `creation-site-vitrine-tpe-paris`) en sections ou blocs internes de la page service correspondante (par exemple une section « Combien ça coûte » et une section « TPE » directement sur `creation-de-site-vitrine-paris`), et garder les articles uniquement pour les angles réellement informationnels et non transactionnels, avec un maillage interne explicite (lien contextuel) vers la page service comme destination de conversion.

Comment vérifier : dans Search Console, comparer les pages qui se positionnent sur les mêmes requêtes avant et après refonte (rapport Performances, filtré par requête). Une baisse du nombre de pages concurrentes sur une même requête est le signal de succès.

### 2.2 [Moyenne] Des pages locales publiées comme articles de blog plutôt que comme pages dédiées

Constat : plusieurs contenus ciblant des villes ou zones géographiques précises sont publiés comme articles de blog (présents dans `post-sitemap.xml`, avec une date de publication) plutôt que comme pages : `agence-web-hauts-de-seine`, `agence-web-collectivites-boulogne-billancourt`, `site-internet-boulogne-billancourt`, `creation-site-internet-clamart`, `agence-seo-clamart`, `creation-de-site-e-commerce-boulogne`.

Pourquoi c'est un problème : ces contenus sont en réalité des pages de zone d'intervention à vocation commerciale et durable (comme les pages services), pas des articles d'actualité. Les publier comme articles les expose à paraître datés dans les résultats de recherche (Google affiche parfois la date de publication), et les dilue au milieu du flux du blog au lieu d'être regroupées dans une architecture géographique claire et facilement accessible en un clic depuis le pied de page ou une page « Zones d'intervention ».

Action : dans la refonte, créer un type de page dédié (ou une section de pages statiques) « Zones d'intervention » avec une page par ville ou secteur (Paris, Boulogne‑Billancourt, Clamart, Hauts‑de‑Seine), reliée depuis le pied de page global. Rediriger en 301 les anciennes URL d'articles vers ces nouvelles pages si les slugs changent, ou conserver les slugs actuels et simplement changer leur type de contenu et leur maillage si techniquement possible (éviter de changer une URL qui n'a pas besoin de changer).

Comment vérifier : contrôler que chaque page de zone est accessible en un clic depuis le pied de page sur toutes les pages du site, et que son `H1` cible clairement la ville sans dupliquer le contenu des autres pages de zone (éviter le copier‑coller entre villes).

### 2.3 [Basse] Maillage interne concentré sur un noyau de pages, articles récents sous‑liés

Constat : les 14 pages liées directement depuis l'accueil (profondeur 1) cumulent chacune 49 liens entrants dans le graphe interne calculé (accueil, nos-services, agence-web-a-paris, contact-agence, réalisations, creation-de-site-vitrine-paris, creation-de-site-e-commerce-paris, maquettes-de-presentation, nos-conseils-web, plan du site, mentions légales, confidentialité, et deux articles remontés en avant sur l'accueil : `logo-et-charte-graphique` et `creation-site-vitrine-tpe-paris`). À l'inverse, une majorité des 33 articles n'ont que 2 à 4 liens entrants internes (uniquement depuis d'autres articles ou depuis le blog), ce qui les place systématiquement à 2 clics de l'accueil.

Pourquoi c'est à surveiller : ce n'est pas une erreur en soi (une architecture en pyramide est normale), mais la faible densité de liens entrants sur les articles limite leur potentiel de positionnement, alors que plusieurs d'entre eux portent un volume de mots conséquent (2000 à 3000 mots) et un travail éditorial réel.

Action : dans la refonte, ajouter des liens contextuels croisés entre articles proches thématiquement (par exemple tous les articles « site vitrine par métier » entre eux), et un bloc « articles liés » systématique en bas de chaque article, pas seulement les 3 derniers articles du blog.

Comment vérifier : recalculer le maillage après refonte et viser un minimum de 5 à 8 liens entrants internes par article publié.

---

## 3. Sécurité et en‑têtes HTTP

### 3.1 [Critique] Les en‑têtes de sécurité disparaissent sur les pages servies depuis le cache

Constat, vérifié en direct par comparaison de deux requêtes sur la même URL :

Page servie depuis le cache statique (`wpo-cache-status: cached`, cas de l'accueil et de `nos-services`, donc de la majorité du trafic) :
```
content-type: text/html; charset=UTF-8
cache-control: no-cache
wpo-cache-status: cached
```
Aucun `strict-transport-security`, aucun `x-content-type-options`, aucun `x-frame-options`, aucun `referrer-policy`, aucun `permissions-policy`.

Page non mise en cache (`wpo-cache-status: not cached`, cas de `/contact-agence/`, de la fausse page 404, et de toute URL avec paramètres) :
```
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
referrer-policy: strict-origin-when-cross-origin
permissions-policy: camera=(), microphone=(), geolocation=()
strict-transport-security: max-age=31536000; includeSubDomains
```

Pourquoi c'est un problème : ces en‑têtes de sécurité sont manifestement ajoutés par PHP (probablement par un plugin de sécurité ou par une règle applicative), mais le cache de page statique (plugin d'optimisation, en‑tête `wpo-cache-status`) sert le HTML directement sans repasser par PHP, donc sans ces en‑têtes. Résultat concret : l'écrasante majorité des visiteurs, qui arrivent sur des pages mises en cache (accueil, pages services, articles), ne reçoivent ni HSTS, ni protection anti‑clickjacking (X‑Frame‑Options), ni protection MIME‑sniffing. Le HSTS en particulier perd une grande partie de son intérêt s'il n'est pas envoyé de façon fiable dès la première visite.

Action : dans la refonte, définir ces en‑têtes au niveau du serveur (Apache, dans la configuration du VirtualHost ou un `.htaccess` chargé en dehors du cycle de cache PHP) plutôt que dans le code applicatif, afin qu'ils s'appliquent identiquement aux réponses mises en cache et non mises en cache. Ajouter à cette occasion une politique `Content-Security-Policy` (absente aujourd'hui sur toutes les pages testées), au minimum en mode `Content-Security-Policy-Report-Only` le temps de la valider avec tous les scripts tiers utilisés (Google Fonts, Google My Business, futurs outils analytics).

Comment vérifier : après refonte, exécuter `curl -sS -D - https://aurea-media.fr/` deux fois de suite (pour couvrir un premier hit non caché puis un hit mis en cache) et confirmer que `strict-transport-security`, `x-content-type-options`, `x-frame-options`, `referrer-policy` et `permissions-policy` sont présents dans les deux cas. Un test avec securityheaders.com doit obtenir au minimum un B.

### 3.2 [Basse] Version de PHP exposée dans l'en‑tête `X-Powered-By`

Constat : toutes les réponses testées renvoient `x-powered-by: PHP/8.1.34`.

Pourquoi c'est un problème mineur : cela révèle inutilement la version exacte de PHP côté serveur à toute personne qui inspecte les en‑têtes, ce qui facilite la recherche de vulnérabilités connues pour cette version précise si elle prend du retard sur les correctifs.

Action : désactiver l'envoi de cet en‑tête (`expose_php = Off` dans `php.ini`, ou suppression via la configuration Apache) sur le nouvel hébergement.

Comment vérifier : `curl -I https://aurea-media.fr/` ne doit plus renvoyer d'en‑tête `X-Powered-By`.

### 3.3 [Basse] Bon point : HTTPS forcé et redirections propres

Constat : `http://aurea-media.fr/` renvoie un `301` propre vers `https://aurea-media.fr/`. Le fait établi en amont (www vers domaine racine) n'a pas pu être re‑testé aujourd'hui car le sous‑domaine `www` n'est pas joignable depuis cet environnement d'audit (restriction réseau locale à l'environnement, sans rapport avec le site), mais reste considéré comme acquis d'après le contexte partagé.

Action : dans la refonte, conserver un unique canonical HTTPS sans `www`, avec une seule redirection 301 (éviter les chaînes de redirection à 2 ou 3 sauts).

Comment vérifier : `curl -IL http://www.aurea-media.fr/` doit montrer une seule redirection avant d'atteindre `https://aurea-media.fr/` (`num_redirects` à 1 dans un test avec `curl -w`).

---

## 4. Mobile

Constat : la balise viewport est correcte (`width=device-width, initial-scale=1.0, viewport-fit=cover`), le thème est basé sur Elementor avec une structure responsive standard (menu mobile dédié détecté dans les commentaires du code source), et aucune anomalie de mise en page n'a été détectée dans l'inspection du HTML/CSS statique (pas de largeur fixe en pixels détectée sur les conteneurs principaux). Le test de terrain (Lighthouse mobile, PageSpeed Insights) n'a pas pu être exécuté aujourd'hui faute de quota API.

Action pour la refonte : conserver un design mobile‑first, avec des cibles tactiles d'au moins 44 par 44 pixels pour tous les boutons et liens de navigation (bouton flottant GTranslate compris, souvent trop petit sur les implémentations par défaut), et valider explicitement l'absence de contenu qui déborde horizontalement sur les petits écrans une fois la maquette intégrée.

Comment vérifier : test « Ergonomie mobile » dans Search Console (fonctionnalité historique remplacée par les rapports d'expérience, à vérifier au moment de la refonte) et audit Lighthouse mobile en conditions réelles (débit 4G simulé) sur les gabarits accueil, page service et article.

---

## 5. Core Web Vitals et poids de page

Avertissement méthodologique : sans accès à PageSpeed Insights ni à CrUX aujourd'hui (429 et 403, voir en-tête du rapport), les constats suivants sont des indicateurs de risque issus de l'inspection du code source et du poids réel des ressources (mesuré en direct par `curl`), pas des mesures de terrain LCP/INP/CLS. Un test PageSpeed Insights mobile doit être effectué dès que possible pour confirmer ou infirmer ces risques avec des valeurs chiffrées de LCP, INP et CLS.

### 5.1 [Haute] Rendu bloqué par 21 feuilles de style et de multiples requêtes de polices

Constat : la page d'accueil charge 21 feuilles de style CSS et 18 scripts JavaScript en balises séparées (aucun regroupement, aucun `media="print"` de contournement, aucun préchargement critique). Un échantillon de 5 des 21 fichiers CSS pèse à lui seul 570 Ko non compressés (dont `royal-elementor-addons/assets/css/frontend.min.css` à 448 Ko pour ce seul fichier). Un échantillon de 7 des 18 fichiers JS pèse 350 Ko non compressés. Le HTML de l'accueil pèse 240 Ko non compressé, 46 Ko compressé en gzip (mesuré en direct).

Pourquoi c'est un problème : chaque feuille de style bloque le rendu tant qu'elle n'est pas téléchargée et interprétée. Avec 21 fichiers CSS sur des origines multiples (domaine principal, `fonts.googleapis.com`), le LCP (Largest Contentful Paint) est mécaniquement retardé par la chaîne de requêtes avant le premier affichage utile, même si les scripts JS sont majoritairement bien positionnés en fin de page (16 des 18 scripts sont hors du `<head>`, seulement 2 y sont, ce qui limite le blocage côté JS).

Action : dans le nouveau thème, sans Elementor, viser une seule feuille de style compilée et minifiée par gabarit de page (accueil, page service, article), avec un budget maximal indicatif de 100 Ko CSS non compressé par page et un chargement de tout CSS non critique en différé (`media="print" onload="this.media='all'"` ou équivalent au chargement du framework choisi). Éliminer les bibliothèques JS non utilisées sur toutes les pages (particles.js, jarallax, isotope, slick, lightgallery, perfect‑scrollbar) au profit d'alternatives natives CSS quand c'est possible, ou d'un chargement conditionnel uniquement sur les pages qui en ont réellement besoin (la page Réalisations pour la galerie, par exemple).

Comment vérifier : test PageSpeed Insights mobile après refonte, viser un LCP inférieur ou égal à 2,5 secondes sur l'accueil et sur un gabarit de page service, en conditions de terrain (CrUX) une fois assez de trafic collecté, et en laboratoire (Lighthouse) dans l'intervalle.

### 5.2 [Haute] Cinq familles de polices chargées par trois mécanismes différents et redondants

Constat détaillé à partir du code source de l'accueil :
- Une feuille `fonts.googleapis.com/css2?family=Fraunces...&family=DM+Sans...&display=swap` limitée à quelques graisses (API moderne, correcte).
- Une seconde feuille `fonts.googleapis.com/css?family=Fraunces:100,100italic,200,...900,900italic&display=swap` et son équivalent pour DM Sans, chacune déclarant 18 graisses et styles (API historique, cette fois avec la totalité des graisses de 100 à 900 en romain et italique). Les deux mécanismes coexistent pour les deux mêmes familles.
- Trois feuilles supplémentaires auto‑hébergées par Elementor (`wp-content/uploads/elementor/google-fonts/css/roboto.css`, `robotoslab.css`, `poppins.css`), chacune avec de multiples graisses, dont les fichiers `.woff2` ne sont pas servis depuis `aurea-media.fr` mais depuis un domaine annexe `aureamedia-8yc8tfkyt9.live-website.com` (probablement un alias technique laissé par l'hébergeur lors d'une migration). Ce domaine n'a pas pu être testé en direct depuis cet environnement d'audit (restriction réseau locale), sa disponibilité réelle pour les visiteurs doit être vérifiée manuellement.

Pourquoi c'est un problème : au minimum 6 requêtes de feuilles de police et un troisième nom d'hôte non préconnecté (`aureamedia-8yc8tfkyt9.live-website.com` n'apparaît dans aucun `<link rel="preconnect">`, contrairement à `fonts.googleapis.com` et `fonts.gstatic.com` qui eux sont bien préconnectés) alourdissent le chemin critique de rendu. Charger 18 graisses par famille sur 2 familles (soit potentiellement des dizaines de fichiers de police si toutes les graisses déclarées sont réellement utilisées par le CSS) est très supérieur au besoin réel d'un site qui, visuellement, n'utilise vraisemblablement que 2 à 4 graisses par famille. Roboto, Roboto Slab et Poppins semblent être des polices historiques du thème non retirées alors que le design actuel repose sur Fraunces et DM Sans (voir contexte partagé) : elles sont probablement inutilisées et chargées pour rien sur chaque page.

Action : dans le nouveau thème, choisir 1 à 2 familles de polices maximum, ne charger que les graisses réellement utilisées dans la maquette (typiquement 2 à 3 par famille : régulier, medium ou semi‑bold, et gras), auto‑héberger les fichiers `.woff2` directement sur le domaine principal `aurea-media.fr` (aucune dépendance à un domaine tiers ou annexe), et supprimer Roboto, Roboto Slab et Poppins si le design final ne les utilise plus. Conserver `font-display: swap` (déjà en place) pour éviter le texte invisible, tout en étant conscient qu'il peut provoquer un léger décalage visuel (FOUT) à l'arrivée de la police : envisager `size-adjust` ou une police de repli à métriques proches pour limiter le CLS.

Comment vérifier : dans les outils de développement du navigateur (onglet Réseau, filtré sur « Font »), compter le nombre de requêtes de polices sur l'accueil après refonte : viser 4 fichiers `.woff2` au maximum sur la page la plus chargée. Confirmer dans le rapport PageSpeed Insights l'absence d'alerte « Éliminer les ressources qui bloquent le rendu » liée aux polices.

### 5.3 [Haute] Aucune image au format moderne, aucune image responsive (`srcset`)

Constat : sur l'accueil, les images détectées sont exclusivement en `.jpg` (9) et `.png` (52), aucune en `.webp` ni `.avif`. Aucun attribut `srcset` n'est présent sur aucune image de la page (0 occurrence détectée). Deux images du bandeau de logos clients pèsent, à titre d'exemple mesuré en direct, 137 Ko et 253 Ko chacune en `.jpg` pour un simple visuel d'illustration dans un article.

Pourquoi c'est un problème : le format WebP (et plus encore AVIF) réduit le poids d'une image de 25 à 50 % à qualité visuelle égale par rapport au JPG/PNG, sans coût de mise en œuvre côté visiteur (support natif dans tous les navigateurs modernes). L'absence de `srcset` signifie que les mobiles téléchargent probablement la même image en pleine résolution que les écrans de bureau, ce qui pèse directement sur le LCP mobile, la métrique prioritaire aujourd'hui.

Action : dans le nouveau thème, générer systématiquement les images uploadées en WebP (avec repli JPG/PNG uniquement si compatibilité ancienne requise, ce qui n'est plus nécessaire en 2026) et en plusieurs largeurs avec `srcset` et `sizes`, soit via une fonctionnalité native de WordPress (les thèmes modernes et le cœur de WordPress génèrent nativement des tailles multiples), soit via un plugin de conversion d'image dédié. Fixer un budget indicatif par image de contenu de 150 Ko maximum en pleine résolution desktop, et 60 à 80 Ko sur la variante mobile servie via `srcset`.

Comment vérifier : `curl -I` sur une image de la nouvelle version doit renvoyer `content-type: image/webp` (ou `image/avif`). Un audit PageSpeed Insights après refonte ne doit plus signaler « Diffuser des images au format nouvelle génération » ni « Dimensionner correctement les images ».

### 5.4 [Moyenne] Attribut `loading="lazy"` dupliqué sur la majorité des images, et 3 images sans largeur ni hauteur

Constat : sur l'accueil, 52 balises `<img>` déclarent l'attribut `loading="lazy"` deux fois dans la même balise (exemple exact relevé dans le code source : `<img loading="lazy" src="..." ... loading="lazy" decoding="async">`). C'est un doublon inoffensif pour le navigateur (qui ignore la répétition) mais révélateur d'un conflit entre deux mécanismes qui ajoutent chacun l'attribut (probablement l'ajout natif de WordPress et un plugin ou une fonction du thème qui fait la même chose). Par ailleurs, 3 images du carrousel d'articles récents sur l'accueil (attribut `data-no-lazy="1"`) n'ont ni `width` ni `height` déclarés.

Pourquoi c'est un problème : le doublon d'attribut est un signe de dette technique à nettoyer, sans impact direct mesurable. L'absence de dimensions sur des images, en revanche, est une cause fréquente de décalage de mise en page (CLS) : le navigateur ne peut pas réserver l'espace nécessaire avant le chargement de l'image, ce qui pousse le contenu environnant au moment où l'image apparaît.

Action : dans le nouveau thème, générer systématiquement les attributs `width` et `height` (ou un ratio CSS `aspect-ratio`) sur 100 % des images, sans exception, et s'assurer qu'aucune fonction du thème n'ajoute l'attribut `loading` en double.

Comment vérifier : inspection du code source après refonte : `grep -o 'loading="lazy"' page.html | wc -l` doit être égal au nombre de balises `<img loading="lazy">`, pas au double. Un audit Lighthouse ne doit plus signaler d'image sans dimensions explicites.

### 5.5 [Basse] Bonne pratique déjà en place sur l'image LCP probable

Constat : l'image du bandeau logo (`fetchpriority="high"`, dimensions `200x56`) et l'image principale du visuel d'en‑tête (`fetchpriority="high"`, dimensions `720x560`, classe `ahr-img`) sont toutes deux exemptées de `loading="lazy"`, dotées de `fetchpriority="high"` et de dimensions explicites. C'est la bonne pratique attendue pour l'image la plus probablement responsable du LCP.

Action : conserver cette logique dans le nouveau thème (identifier l'image la plus grande visible sans défilement sur chaque gabarit de page et lui appliquer `fetchpriority="high"`, jamais `loading="lazy"`, avec des dimensions explicites).

Comment vérifier : dans le rapport PageSpeed Insights, contrôler que l'élément identifié comme LCP correspond bien à cette image et qu'aucun avertissement « Élément LCP découvert tardivement » n'apparaît.

### 5.6 [Moyenne] Compression gzip active mais pas de Brotli, cache navigateur limité à 28 jours sur les fichiers statiques

Constat : les ressources statiques (CSS, JS, HTML) sont bien compressées en gzip quand le navigateur l'annonce (`content-encoding: gzip` vérifié en direct), mais une requête explicite avec `Accept-Encoding: br` n'obtient pas de réponse compressée en Brotli, qui est pourtant plus performant que gzip à qualité égale et supporté par tous les navigateurs modernes. Le cache navigateur des fichiers CSS/JS/images est réglé à `max-age=2419200` (28 jours), alors que ces fichiers portent déjà un paramètre de version dans l'URL (`?ver=...`) qui permet sans risque un cache bien plus long.

Action : sur le nouvel hébergement, activer la compression Brotli au niveau serveur (module `mod_brotli` pour Apache ou équivalent) en plus de gzip, et étendre la durée de cache navigateur des ressources versionnées à 1 an (`max-age=31536000, immutable`), puisque tout changement de fichier s'accompagne déjà d'un changement de paramètre de version dans l'URL.

Comment vérifier : `curl -sS -D - -H "Accept-Encoding: br" <url_css>` doit renvoyer `content-encoding: br`. `curl -sS -D - <url_css_versionnée>` doit renvoyer `cache-control: max-age=31536000, immutable` (ou approchant).

### 5.7 [Basse] Poids du DOM raisonnable

Constat : la page d'accueil compte environ 1169 balises HTML ouvrantes, dont 181 `<div>`. C'est en dessous du seuil de 1500 nœuds à partir duquel Lighthouse signale un DOM excessif, mais l'accueil reste la page la plus lourde du site avec 58 images et de nombreux widgets Elementor (particules, parallaxe, carrousel, galerie).

Action : dans la refonte, sans le poids d'Elementor et de ses bibliothèques d'animation, ce chiffre devrait naturellement baisser ; fixer un budget indicatif de 800 nœuds DOM sur l'accueil.

Comment vérifier : Lighthouse, section Diagnostics, « Éviter une taille excessive du DOM ».

---

## 6. Rendu JavaScript

Constat : l'ensemble du contenu textuel (titres, paragraphes, H1/H2, texte des articles) a été récupéré par un simple `curl` sans exécution JavaScript ni navigateur headless, et correspond au contenu visible sur le site. WordPress avec Elementor génère du HTML côté serveur (rendu au moment de la publication et mis en cache), et non un rendu par un framework JavaScript côté client (pas de SPA, pas de React/Vue détecté). Le JavaScript présent (jQuery, particles.js, jarallax, isotope, slick, lightgallery, GTranslate) sert uniquement des effets visuels et des interactions (animations, carrousel, galerie lightbox, widget de traduction), jamais l'affichage du contenu principal ni des liens de navigation.

Pourquoi c'est un point positif à conserver : Googlebot et les autres robots n'ont besoin d'aucune capacité de rendu JavaScript pour indexer correctement le contenu du site. C'est un avantage réel à ne pas perdre dans la refonte.

Action pour la refonte : quel que soit le nouveau thème ou la nouvelle stack (à condition de rester sur WordPress classique comme indiqué dans le contexte du projet), conserver un rendu HTML complet côté serveur pour tout le contenu et la navigation, et réserver le JavaScript aux seules améliorations d'interaction (animations, formulaires, widgets).

Comment vérifier : désactiver JavaScript dans le navigateur (ou utiliser `curl`) sur chaque gabarit de page après refonte, et confirmer que le contenu principal, les titres et les liens de navigation restent visibles et fonctionnels dans le code source brut.

---

## 7. Données structurées (détection)

Constat : seule la page d'accueil porte un balisage JSON‑LD (`ProfessionalService`, `AggregateRating`, `Person`, `PostalAddress`, `GeoCoordinates`). Aucune des pages services (`nos-services`, `creation-de-site-vitrine-paris`, `creation-de-site-e-commerce-paris`, `branding-identite`, `maquettes-de-presentation`) ne porte de balisage structuré. Une quinzaine d'articles portent un balisage `Question`/`Answer`/`ListItem` en microdonnées (format FAQ), les autres articles n'ont aucun balisage. Aucune page ne porte de `BreadcrumbList` ni de balisage `Article`/`BlogPosting`.

Une analyse dédiée et plus poussée de la validité de ces balisages (JSON‑LD cassé, incohérences de note, doublons de `LocalBusiness`, prix contradictoires, `HowTo` obsolète) est en cours dans l'audit `schema.md` du même dossier : se référer à ce document pour le détail et les actions de correction. Ce rapport se limite au constat de détection ci‑dessus, qui rejoint le score global du présent audit.

Action pour la refonte (volet architecture technique uniquement) : implémenter un balisage `BreadcrumbList` sur toutes les pages profondes (services, articles), un balisage `Service` ou `LocalBusiness` cohérent et unique sur les pages services (pas seulement l'accueil), et abandonner tout balisage `FAQPage` dans une optique de résultat enrichi puisque Google n'affiche plus ces résultats enrichis depuis le 7 mai 2026 (le balisage `FAQPage` peut être conservé pour sa valeur sémantique, jamais présenté en interne comme un levier de visibilité SERP).

Comment vérifier : passer chaque gabarit de page dans l'outil de test des résultats enrichis de Google après refonte, zéro erreur critique.

---

## 8. IndexNow

Constat : aucune preuve d'implémentation d'IndexNow n'a été trouvée (pas de fichier de clé détecté, pas de référence au protocole dans le code source des pages testées, pas de plugin dédié identifié dans la pile technique établie en amont : WordPress, Elementor, Royal Elementor Kit/Addons, Essential Addons, Rank Math, GTranslate). Rank Math intègre une fonctionnalité d'indexation instantanée mais elle nécessite une activation et une configuration explicites (clé API Bing/IndexNow) qui ne semblent pas en place.

Pourquoi c'est une opportunité : le site publie du contenu neuf régulièrement (33 articles, plusieurs mis à jour ces dernières semaines d'après les dates `lastmod` du sitemap). IndexNow permet de signaler instantanément à Bing, Yandex et Naver toute création ou mise à jour de page, sans attendre leur prochain passage de crawl, pour un coût de mise en œuvre très faible.

Action : dans la refonte, activer IndexNow via Rank Math (module dédié ou extension complémentaire) ou un plugin léger spécialisé, générer la clé de vérification requise (`/<clé>.txt` à la racine), et confirmer l'envoi automatique à chaque publication ou mise à jour d'article.

Comment vérifier : `curl https://aurea-media.fr/<clé>.txt` doit renvoyer la clé en texte brut. Le tableau de bord Bing Webmaster Tools doit afficher une activité IndexNow après publication d'un nouvel article.

---

## Exigences techniques pour le nouveau thème

Cette section fixe les règles à respecter par l'équipe qui construira le nouveau thème WordPress (sans Elementor, Rank Math conservé), pour ne pas reproduire les problèmes identifiés ci‑dessus.

### Budget de performance (à valider sur mobile, réseau 4G simulé, par gabarit)
- Poids total de page (HTML + CSS + JS, compressé) : 500 Ko maximum sur les gabarits accueil et page service, 350 Ko sur un gabarit article.
- CSS : un seul fichier compilé par gabarit, 100 Ko non compressé maximum, chargement critique en ligne (`inline`) pour le rendu au‑dessus de la ligne de flottaison, reste différé.
- JavaScript : 150 Ko non compressé maximum au chargement initial, tout script non indispensable au premier rendu chargé en `defer` ou en différé après interaction.
- LCP cible : inférieur ou égal à 2,5 secondes en mobile (donnée de terrain CrUX une fois disponible, à défaut Lighthouse mobile en attendant).
- INP cible : inférieur ou égal à 200 ms.
- CLS cible : inférieur ou égal à 0,1, avec dimensions explicites sur 100 % des images et des espaces réservés pour tout élément chargé de façon asynchrone (widgets, formulaires).
- Nombre de requêtes de police : 4 fichiers `.woff2` maximum par page.

### Stratégie de polices
- 1 à 2 familles maximum, 2 à 3 graisses par famille.
- Fichiers `.woff2` auto‑hébergés sur le domaine principal `aurea-media.fr`, jamais sur un domaine tiers ou annexe.
- `font-display: swap` conservé, avec une police de repli à métriques proches pour limiter le décalage visuel au chargement.
- Suppression de toute police héritée non utilisée par le nouveau design (Roboto, Roboto Slab, Poppins actuellement chargées sans usage visible confirmé, Font Awesome à remplacer par des SVG inline ou une icônothèque allégée si seul un sous‑ensemble d'icônes est utilisé).

### Stratégie d'images
- Format WebP par défaut pour toute image de contenu, génération de plusieurs largeurs avec `srcset` et `sizes`.
- `width` et `height` (ou `aspect-ratio` CSS) obligatoires sur 100 % des images, sans exception.
- `loading="lazy"` sur toute image sous la ligne de flottaison, jamais sur l'image LCP identifiée par gabarit, qui doit au contraire porter `fetchpriority="high"`.
- Budget par image de contenu : 150 Ko maximum en pleine résolution desktop, 80 Ko maximum sur la variante mobile.
- Compression et redimensionnement systématiques à l'upload (pas de recours à l'affichage redimensionné en CSS d'une image en pleine résolution).

### Cache
- En‑têtes de sécurité (HSTS, X‑Content‑Type‑Options, X‑Frame‑Options, Referrer‑Policy, Permissions‑Policy, Content‑Security‑Policy) définis au niveau serveur, identiques que la page soit servie depuis le cache ou non.
- Cache navigateur des ressources statiques versionnées : 1 an, `immutable`.
- Compression Brotli en plus de gzip.
- Cache de page HTML conservé pour la performance, mais audité pour confirmer qu'il ne supprime aucun en‑tête de sécurité ni aucune donnée dynamique nécessaire (formulaire de contact, widget d'avis).

### Redirections 301 à prévoir si des URL changent
- Si les quatre archives de catégorie orphelines sont supprimées (recommandé, voir 1.2) : `creation-site-vitrine` vers `creation-de-site-vitrine-paris`, `creation-site-ecommerce` vers `creation-de-site-e-commerce-paris`, `creation-site-web` vers `agence-web-a-paris`, `google-my-business-seo-local` vers la page hub la plus pertinente (`nos-conseils-web` ou une future page pilier SEO local).
- Si les pages locales actuellement publiées comme articles (`agence-web-hauts-de-seine`, `agence-web-collectivites-boulogne-billancourt`, `site-internet-boulogne-billancourt`, `creation-site-internet-clamart`, `agence-seo-clamart`, `creation-de-site-e-commerce-boulogne`) migrent vers une nouvelle architecture « Zones d'intervention » avec de nouveaux slugs : rediriger individuellement chaque ancienne URL vers sa nouvelle page correspondante (pas de redirection groupée vers une page générique).
- Conserver sans changement les URL suivantes, qui ont une profondeur de clic faible, un bon maillage entrant et des métadonnées déjà conformes aux règles Rank Math du client : `/`, `/agence-web-a-paris/`, `/nos-services/`, `/creation-de-site-vitrine-paris/`, `/creation-de-site-e-commerce-paris/`, `/branding-identite/`, `/maquettes-de-presentation/`, `/realisations-agence-web/`, `/contact-agence/`, `/nos-conseils-web/`. Toute modification de ces URL doit être accompagnée d'une redirection 301 individuelle et d'un test de non‑régression sur leur position Search Console avant et après.
- Aucune redirection groupée ou générique de type « tout vers l'accueil » : chaque redirection 301 doit cibler la page la plus proche en intention, jamais une page générique par défaut (c'est précisément le défaut relevé en 1.1 pour les 404).

### Règles pour les 404
- Toute URL réellement inexistante doit renvoyer un code `HTTP 404` explicite (jamais un 301 générique vers l'accueil).
- La page 404 doit rester sur le domaine et proposer une navigation utile (recherche, lien accueil, 2 à 3 pages services, page contact), sans redirection automatique.
- Un rapport mensuel des 404 réelles (via les journaux serveur ou Search Console) doit être mis en place pour détecter les liens cassés internes ou externes et les corriger ou rediriger individuellement au cas par cas, jamais en masse vers l'accueil.
- Comment vérifier après mise en ligne : tester au moins 10 URL aléatoires inexistantes, confirmer un code 404 sur chacune, et vérifier dans Search Console (rapport Pages) que ces URL apparaissent en « Introuvable (404) » et non en redirection.

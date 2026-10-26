# Stratégie de contenu, ciblage et architecture sémantique : aurea-media.fr

Audit du 25 septembre 2026, préparé pour la refonte (nouveau thème sans Elementor, Rank Math conservé).
Périmètre : 10 pages, 32 articles et l'index du blog (/nos-conseils-web/), 4 catégories, 76 requêtes analysées en SERP.

## 0. Synthèse

- Le site a une bonne base éditoriale (32 articles de 1 300 à 3 400 mots, 8 pages métiers, 3 pages locales), mais **plusieurs pages visent le même mot-clé** : l'accueil, /agence-web-a-paris/ et /choisir-agence-web-paris/ ont toutes un title qui commence par « Agence web (à) Paris » ; deux articles se disputent « logo et charte graphique » ; deux pages visent Clamart, trois visent Boulogne-Billancourt, deux visent la fiche Google.
- L'offre vendue dépasse ce que le site expose : la refonte, le SEO local et la maintenance sont cités dans les textes mais n'ont **aucune page service**. Il n'y a pas non plus de page tarifs, alors que les prix affichés sont le premier argument face aux agences parisiennes.
- Décision : **4 nouvelles pages services** (tarifs, refonte, SEO local, maintenance), un pilier généraliste « création site internet Paris » (ex /nos-services/), une page À propos, un hub Secteurs, 5 fusions avec 301, 3 pages métiers et 15 articles planifiés sur 6 mois.
- Les prix ne sont pas cohérents entre pages (e-commerce à 2 000 € HT dans un article contre 1 650 € HT sur la page service ; maintenance à 89 € HT/mois dans un article contre « sans abonnement imposé » sur les pages services). La page tarifs doit devenir la source unique.
- Les cibles à prioriser : PME B2B en refonte, professions libérales réglementées (avocats, médecins, kinés, psychologues, architectes), artisans et entreprises du bâtiment en Île-de-France. Les indépendants et coachs restent une cible de volume, les associations une cible secondaire.

## 1. Méthode et limites des données

- **Collecte** : 76 requêtes en français passées dans l'outil WebSearch (9 à 10 résultats organiques par requête), stockées dans `scratchpad/serp/serp.json` (brut : `serp1.txt`). Recoupement calculé par paire : nombre d'URL communes (URL normalisées) et nombre de domaines communs, Wikipédia exclu.
- **Seuils appliqués** (méthode seo-cluster) : 7 à 10 URL communes = même page ; 4 à 6 = même cluster ; 2 à 3 = maillage ; 0 à 1 = pages séparées. Pour les scores de 2 à 4, le recoupement de domaines et l'intention servent d'arbitre.
- **Limites** :
  - L'outil n'est pas un relevé Google France géolocalisé. Certaines requêtes ont renvoyé des résultats anglophones (« maintenance site wordpress », « shopify ou woocommerce », « audit seo site internet ») ou du bruit Wikipédia (« prix site internet paris »). Les recoupements sont donc **sous-estimés** : aucun couple n'atteint 7, le maximum observé est 6.
  - **Aucun volume de recherche n'est disponible** : tous les volumes sont **non vérifiés**. La difficulté est estimée d'après la composition de la SERP : **forte** (comparateurs Sortlist, La Fabrique du Net, Codeur, plateformes Wix, Shopify, HubSpot, grandes agences), **moyenne** (agences établies et quelques indépendants), **faible** (freelances, petits sites, pages peu optimisées, bruit).
  - Les données Search Console (clics, impressions par URL) n'étaient pas accessibles : chaque décision qui dépend du trafic d'une URL est marquée **non vérifié** et assortie d'une condition.
- **À refaire avant la mise en ligne** : exporter Search Console (16 mois, par page et par requête) et passer les 20 mots-clés principaux dans un outil Google FR (Semrush, Ahrefs ou Haloscan) pour confirmer volumes et recoupements.

## 2. Recherche de mots-clés par intention

Légende des intentions : **Transactionnelle** (achat ou devis immédiat), **Commerciale** (comparer des prestataires ou des solutions), **Informationnelle** (se renseigner), **Locale** (ville ou zone dans la requête ou pack local attendu). Volumes : non vérifiés pour toutes les lignes.

### 2.1 Agence web et création de site internet à Paris

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| agence web paris | Commerciale, locale | Forte | Comparateurs (Sortlist, Codeur, La Fabrique du Net) et agences établies (Adveris, Beyonds, 6LAB) | Accueil |
| agence création site internet paris | Commerciale, locale | Forte | Sortlist, Noiise, Little Big Things, WebGazelle, Beyonds, WeDezign, 6LAB | /creation-site-internet-paris/ (secondaire) |
| création site internet paris | Transactionnelle, locale | Forte | Adveris (2 pages), Sortlist, WP Marmite Start, Noiise, Vistalid, 6LAB, Jalis | /creation-site-internet-paris/ |
| création site wordpress paris | Commerciale, locale | Moyenne à forte | Agences WordPress (Globalis, Youdemus, BSA Web, Palmsquare, Farouk Nasri) | /creation-site-internet-paris/ (secondaire) |
| création site internet pas cher paris | Transactionnelle, locale | Moyenne | Offres low cost (EvolveTech dès 399 €, siteunepage, Epixelic, Webutin, Jalis) | /prix-site-internet-paris/ (secondaire) |
| agence web tpe pme paris | Commerciale, locale | Moyenne | L'agence123, Sedigitaliser, Cohérence, Transacts, Frametonic, annuaire France Num | Accueil (secondaire) |
| agence web île-de-france | Commerciale, locale | Moyenne à forte | Sortlist, PagesJaunes, Digital Unicorn, Agence2Web, Farouk Nasri, KreaRise | Accueil (secondaire), pas de page dédiée |
| création site internet professionnel sur mesure | Commerciale | Moyenne | Plateformes (Comeup, LWS, SiteW) et petites agences | /creation-site-internet-paris/ (secondaire) |
| développeur web freelance paris wordpress | Commerciale, locale | Moyenne | Codeur, LesBonsFreelances, freelances installés | /a-propos/ |
| comment choisir son agence web | Informationnelle | Moyenne | Blogs d'agences, Blog du Dirigeant | /choisir-agence-web-paris/ |
| cahier des charges site internet | Informationnelle | Forte | La Fabrique du Net, HubSpot, Noiise, France Num, modèles à télécharger | À créer : /cahier-des-charges-site-internet/ |
| combien de temps pour créer un site internet | Informationnelle | Moyenne | Wix, Simplébo, Linkeo, blogs | À créer : /delai-creation-site-internet/ |
| création site multilingue wordpress | Commerciale | Moyenne | Noiise, Galopins, Agence2Web, Amphibee | À créer : /site-internet-bilingue/ |

### 2.2 Site vitrine

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| création site vitrine paris | Transactionnelle, locale | Moyenne | Palmsquare, agenceweb.fr, Feel and Clic, Kapuccino, Pixel Paris, Beecomy, Smart Agency | /creation-de-site-vitrine-paris/ |
| création site vitrine | Informationnelle et commerciale | Forte | Codeur, Shopify, e-monsite, Orson, Beyonds, Facton | Pilier vitrine (secondaire) |
| création site vitrine wordpress | Informationnelle et commerciale | Moyenne | Codeur, Leptidigital, Galopins, freelances | Pilier vitrine (secondaire) |
| site vitrine c'est quoi | Informationnelle | Forte | Wikipédia, Shopify, 1min30, Blog du Dirigeant | Section du pilier vitrine, pas de page |
| budget site internet tpe | Informationnelle | Faible à moyenne | Blogs régionaux, Comptanoo, Portail des PME, France Num | /creation-site-vitrine-tpe-paris/ repositionné |

### 2.3 Prix et tarifs

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| prix site internet paris | Transactionnelle, locale | Faible à moyenne | Petites agences (Clickzou, Inleven, CKOM, Sismeo, Epixelic) et bruit | À créer : /prix-site-internet-paris/ |
| prix site internet | Informationnelle à visée commerciale | Forte | Noiise, Wix, Indy, agences | /prix-site-internet-paris/ (secondaire) |
| tarif création site internet | Informationnelle | Forte | La Fabrique du Net, Wix, HelloAsso, Wise, Indy, Squarespace | /prix-site-internet-paris/ (secondaire) |
| prix site vitrine | Informationnelle | Moyenne | Codeur, Wix, Ipaoo, freelances | /prix-site-vitrine-paris/ |
| combien coûte un site vitrine | Informationnelle | Moyenne | 6 URL communes avec « prix site vitrine » | /prix-site-vitrine-paris/ |
| combien coûte un site e-commerce | Informationnelle | Forte | Codeur, Shopify, Wix, La Fabrique du Net, Chronopost, France Num | /combien-coute-un-site-e-commerce/ |
| prix refonte site internet | Informationnelle à visée commerciale | Moyenne | Codeur, Palmsquare, Smart Agency, blogs | À créer : /prix-refonte-site-internet/ |
| prix création logo | Informationnelle | Moyenne à forte | Wix, VistaPrint, Codeur, graphistes | À créer : /prix-creation-logo/ |
| tarif maintenance site internet | Informationnelle | Moyenne | Simplébo, studios, blogs | À créer : /prix-maintenance-site-internet/ |
| prix référencement naturel tpe | Informationnelle | Moyenne | Pixalione, blogs d'agences SEO | À créer : /prix-seo-local/ |

### 2.4 E-commerce

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| création site e-commerce paris | Transactionnelle, locale | Moyenne à forte | Pixelys, Feel and Clic, Transacts, KNR, Startbiz (budgets cités dès 12 000 €) | /creation-de-site-e-commerce-paris/ |
| agence woocommerce paris | Commerciale, locale | Moyenne | La Fabrique du Net, WeDezign, Condorito, Walt, Pixel Paris, Ingenius | À créer : /agence-woocommerce-paris/ |
| création boutique en ligne woocommerce sur mesure | Commerciale | Moyenne | Agences et hébergeurs (Amen, LWS) | Pilier e-commerce (secondaire) |
| création site e-commerce | Informationnelle | Forte | Guides (Simplébo, Payplug, Codeur), Wix, e-monsite | /creer-un-site-e-commerce-professionnel/ |
| shopify ou woocommerce | Commerciale (comparaison) | Forte | woocommerce.com, Shopify, Kinsta (résultats en partie anglophones) | /shopify-vs-woocommerce/ |

### 2.5 Refonte

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| agence refonte site internet paris | Commerciale, locale | Moyenne | Sortlist, Churchill, agenceweb.fr, Actif Digital, Pulsar, Vivado | À créer : /refonte-site-internet-paris/ |
| refonte site internet | Informationnelle | Forte | Codeur, Invox, Noiise et pages services (Youdemus, Amphibee) | /refonte-site-internet/ (article) |
| refonte site internet sans perdre référencement | Informationnelle | Moyenne | Blogs d'agences SEO (Pilot'in, Thrive, Azède), Audreytips | À créer : /refonte-site-seo/ |
| refonte site wordpress | Informationnelle et commerciale | Moyenne | Anthedesign, WPFormation, WP Marmite, freelances | Pilier refonte (secondaire) |

### 2.6 Référencement et SEO local

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| seo local paris | Commerciale, locale | Moyenne | Komunike, SEO Supernova, Ekko Media, Uclic, consultants | À créer : /seo-local-paris/ |
| référencement local paris agence | Commerciale, locale | Moyenne | Sedigitaliser, Digiberries, SEO Supernova, Vu du Web, Linkeo | /seo-local-paris/ (secondaire) |
| agence seo local | Commerciale | Moyenne à forte | SEO.fr, Eskimoz, AWI, Passedevant | /seo-local-paris/ (secondaire) |
| référencement local google | Informationnelle | Forte | Wikipédia, Noiise, Semrush, Solocal, Geolid, CCI | À créer : /referencement-local-google/ |
| optimiser sa fiche google my business | Informationnelle | Moyenne | Partoo, SEOMix, Guest Suite, Geolid | /optimiser-sa-fiche-google-my-business/ |
| optimiser fiche google business profile | Informationnelle | Moyenne | 5 URL communes avec la ligne précédente | Même page |
| google my business artisan | Informationnelle | Faible à moyenne | Blogs et petites agences pour artisans | /google-my-business-artisans-tpe/ |
| comment obtenir des avis google | Informationnelle | Moyenne | Partoo, aide Google, Trustt | /avis-google/ |
| agence seo paris | Commerciale, locale | Forte | SEO.fr, Eskimoz, Pixalione, Agence 90, Feel and Clic, comparateurs | Non ciblé |
| référencement naturel paris | Commerciale, locale | Forte | Axess, Yateo, Ad's up, Tactee, Maxelik, Linkeo, Oscar | Non ciblé |
| consultant seo paris freelance | Commerciale, locale | Moyenne à forte | Codeur et consultants installés | Non ciblé |
| audit seo site internet | Informationnelle (outils) | Forte | Outils anglophones (Semrush, Ahrefs) | Non ciblé |

### 2.7 Branding et présentations

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| création logo paris | Transactionnelle, locale | Faible à moyenne | Graphistes indépendants (Farouk Nasri, JR Graphiste, Mathieu Orenge), Agence Sweep | /branding-identite/ |
| création logo identité visuelle | Commerciale | Moyenne | Studios hors Paris, Adobe, HubSpot | /branding-identite/ (secondaire) |
| agence branding paris | Commerciale, locale | Forte | Bien-Fondé, Graphéine, 4Beez, types top, Black Pizza | Non ciblé |
| logo charte graphique identité visuelle différence | Informationnelle | Moyenne | Canva, Eskimoz, studios | /logo-charte-graphique-identite-visuelle/ |
| logo et charte graphique | Informationnelle | Moyenne | Adobe, Wikipédia, Eskimoz, Canva | Même page (fusion) |
| création pitch deck powerpoint professionnel | Informationnelle et commerciale | Moyenne | Studios spécialisés (Mprez, La Boîte à Slides, Histoires et Slides), Microsoft, Slidesgo | /maquettes-de-presentation/ |

### 2.8 Maintenance

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| maintenance wordpress paris | Transactionnelle, locale | Faible | Freelances et petites agences (Yes We Blog, Athirion, 10ket, EvidenceWP, Maintenance WP) | À créer : /maintenance-wordpress-paris/ |
| contrat maintenance site wordpress prix | Commerciale | Moyenne | WPFormation, Hyppoweb, Graindesite (29 €/mois), Pulsar (159 € HT/mois) | /prix-maintenance-site-internet/ (secondaire) |
| maintenance site wordpress | Commerciale | Non vérifié (résultats anglophones) | | /maintenance-wordpress-paris/ (secondaire) |

### 2.9 Site internet pour un métier

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| site internet pour artisan | Commerciale | Moyenne | WP Marmite, Codeur, PlancheContact, Web iPro, siteartisan | /site-vitrine-pour-artisan/ |
| création site internet btp | Commerciale | Moyenne | HubSpot, Web iPro, ChantierWeb, B2B Online, Digidream, TrustUp | /site-vitrine-artisan-renovateur-en-batiment/ |
| site internet artisan rénovation bâtiment | Commerciale | Faible à moyenne | VisibiliteCom, Digidream, Atelier des Chantiers, plateformes de devis | Même page |
| création site internet plombier | Commerciale | Moyenne | Simplébo, Wix, WebGazelle, Geoffrey Leduc | Section de /site-vitrine-pour-artisan/ |
| création site internet avocat | Commerciale | Moyenne à forte | Septeo, Digital Avocat, Gallia Lex, spécialistes | /site-vitrine-avocat/ |
| site vitrine avocat | Commerciale | Moyenne | Digital Avocat, HubSpot, Evico, Adveris | Même page |
| création site internet avocat paris | Commerciale, locale | Moyenne | WebTensor, Transacts, Feel and Clic, Digidream, 6LAB | Même page |
| création site internet médecin | Commerciale | Moyenne | IONOS, SiteW, Web iPro, Followdoc, Digidocteur | /site-vitrine-medecin/ |
| site internet médecin paris | Commerciale, locale | Faible à moyenne | Petites agences santé (Alexeo, Slagon, Pixell, Doctoweb) | Même page |
| site internet kinésithérapeute | Commerciale | Moyenne | Simplébo, MACSF, KinéOWeb, Kine-site | /site-vitrine-kinesitherapeute/ |
| création site internet psychologue | Commerciale | Faible à moyenne | Freelances spécialisés (Psy-Site, Visipsy, Evico, Nicodev) | À créer : /site-internet-psychologue/ |
| création site internet architecte | Commerciale | Moyenne | Studios spécialisés, Simplébo, WebGazelle | /site-vitrine-architecte/ |
| création site internet coach | Commerciale | Moyenne | Wasabi Web Design, Simplébo, WP Marmite, WebGazelle | /site-vitrine-coach-consultant/ |
| création site internet restaurant | Commerciale | Moyenne à forte | Zenchef, WP Marmite, Wix, Clickeat | /site-vitrine-restaurant/ |
| création site internet association | Commerciale | Moyenne à forte | HubSpot, Wix, AssoConnect, SiteW, WebGazelle | À créer : /site-internet-association/ |

### 2.10 Local

| Mot-clé | Intention | Difficulté | Ce que montre la SERP | Page cible |
|---|---|---|---|---|
| création site internet clamart | Transactionnelle, locale | Faible | AlloVoisins, Création Site 24, Transacts, ABC Idea ; **Aurea Media apparaît en 5e position** dans nos relevés (non vérifié sur Google FR) | /creation-site-internet-clamart/ |
| création site internet boulogne-billancourt | Transactionnelle, locale | Moyenne | Sortlist, La Fabrique du Net, ABC Idea, Digital Unicorn, FW16, Linkeo, Beecomy | /site-internet-boulogne-billancourt/ |
| agence web boulogne-billancourt | Commerciale, locale | Moyenne | Net Concept, PagesJaunes, Sortlist, Digital Unicorn, Futur Digital | Même page |
| création site internet hauts-de-seine | Transactionnelle, locale | Faible à moyenne | ABC Idea, YM Studio, WAI31, Pixell, webmasters | /agence-web-hauts-de-seine/ |

**Lecture business** : les têtes parisiennes (« agence web paris », « création site internet paris », « agence seo paris ») sont tenues par des comparateurs et des agences de 50 à 70 personnes. Les gains rapides se trouvent sur les requêtes **service + métier**, **prix**, **SEO local**, **maintenance** et **villes du 92**, où la SERP est faite de freelances et de petites agences.

## 3. Recoupement de SERP (paires décisives)

Score = URL communes / domaines communs sur le top 9 ou 10. Liste complète des 76 requêtes dans `scratchpad/serp/serp.json`.

| Paire | Score | Relation | Décision |
|---|---|---|---|
| prix site vitrine / combien coûte un site vitrine | 6 / 6 | Même cluster, intention identique | Une seule page : /prix-site-vitrine-paris/ |
| optimiser sa fiche google my business / optimiser fiche google business profile | 5 / 5 | Même cluster, intention identique | Une seule page : /optimiser-sa-fiche-google-my-business/ |
| création site internet paris / agence création site internet paris | 4 / 4 | Même cluster | Une seule page : /creation-site-internet-paris/ |
| création site internet paris / création site internet pas cher paris | 3 / 3 | Maillage | « pas cher » traité sur la page tarifs, lien depuis le pilier |
| agence web paris / agence création site internet paris | 2 / 3 | Maillage | Accueil et pilier création séparés, liés |
| agence web paris / création site internet paris | 1 / 3 | Séparées | Accueil = « agence web paris » ; pilier création = « création site internet paris » |
| création site internet paris / création site vitrine paris | 1 / 1 | Séparées | Pilier généraliste et pilier vitrine distincts |
| prix site internet / tarif création site internet | 2 / 2 | Maillage | Secondaires de la page tarifs |
| création site internet avocat / site vitrine avocat | 2 / 2 | Maillage, même métier | Une page métier avocat |
| création site internet avocat / création site internet avocat paris | 2 / 2 | Maillage, même métier | Même page métier avocat |
| logo et charte graphique / logo charte graphique identité visuelle différence | 2 / 2 | Maillage | Les deux articles du site ont le même focus : fusion |
| référencement local paris agence / référencement naturel paris | 2 / 3 | Maillage | On ne vise que le local |
| création site internet boulogne-billancourt / agence web boulogne-billancourt | 1 / 2 | Même intention locale | Une page Boulogne-Billancourt |
| site internet artisan rénovation bâtiment / création site internet btp | 1 / 1 | Même famille | Page BTP unique |
| site internet artisan rénovation bâtiment / site internet pour artisan | 0 / 0 | Séparées | Garder artisan (généraliste) et BTP (rénovation) |
| optimiser fiche google business profile / google my business artisan | 0 / 1 | Séparées | Garder les deux articles |
| refonte site internet / agence refonte site internet paris | 0 / 0 | Séparées | Article guide et page service coexistent |
| prix refonte site internet / refonte site internet | 0 / 1 | Séparées | Article prix dédié |
| création site e-commerce paris / agence woocommerce paris | 0 / 0 | Séparées | Page WooCommerce dédiée possible |
| création site e-commerce paris / création site e-commerce | 0 / 0 | Séparées | Pilier service et guide distincts |
| création site vitrine paris / création site vitrine | 0 / 0 | Séparées | La requête nationale reste secondaire du pilier |
| prix site internet paris / prix site internet | 0 / 0 | Séparées | La page tarifs vise la version Paris |
| prix site vitrine / prix site internet | 0 / 1 | Séparées | Article prix vitrine et page tarifs coexistent |
| budget site internet tpe / prix site vitrine | 0 / 0 | Séparées | Article TPE repositionné sur le budget |
| comment choisir son agence web / agence web paris | 0 / 0 | Séparées | L'article ne doit plus viser « agence web paris » |
| seo local paris / agence seo paris | 0 / 1 | Séparées | Viser le local, pas la tête SEO |
| référencement local google / seo local paris | 0 / 0 | Séparées | Guide informationnel dédié |
| création logo paris / agence branding paris | 0 / 0 | Séparées | Viser le logo, pas le branding d'agence |
| maintenance wordpress paris / contrat maintenance site wordpress prix | 0 / 0 | Séparées | Page service et article prix |
| création site internet clamart / création site internet hauts-de-seine | 0 / 1 | Séparées | Garder la page ville et la page département |

## 4. Architecture hub and spoke cible

### 4.1 Arborescence

- **Accueil** (agence web paris)
  - **Création de site internet à Paris** /creation-site-internet-paris/ (hub des services, ex /nos-services/)
    - Site vitrine /creation-de-site-vitrine-paris/
    - Site e-commerce /creation-de-site-e-commerce-paris/, puis Agence WooCommerce /agence-woocommerce-paris/
    - **Refonte** /refonte-site-internet-paris/ (nouveau)
    - **SEO local** /seo-local-paris/ (nouveau)
    - **Maintenance WordPress** /maintenance-wordpress-paris/ (nouveau)
    - Logo et identité visuelle /branding-identite/
    - Présentations /maquettes-de-presentation/
  - **Tarifs** /prix-site-internet-paris/ (nouveau, hub transversal des articles prix)
  - **Secteurs** /secteurs/ (nouveau, hub des pages métiers)
  - **Hauts-de-Seine** /agence-web-hauts-de-seine/ (hub local : Boulogne-Billancourt, Clamart, villes futures)
  - Réalisations /realisations-agence-web/
  - Conseils /nos-conseils-web/ (index des satellites)
  - **À propos** /a-propos/ (nouveau, reprend /agence-web-a-paris/)
  - Contact /contact-agence/

Garder la structure de permaliens **/%postname%/** : pages et articles sont à la racine, ce qui permet de convertir un article en page (pages métiers) sans changer d'URL.

### 4.2 Pages piliers (services)

| Pilier | URL | Action | Mot-clé principal | Secondaires | Gabarit | Longueur |
|---|---|---|---|---|---|---|
| Accueil | / | Garder, retitrer | agence web paris | agence web tpe pme paris ; agence web île-de-france ; agence web indépendante paris | Accueil | 1 800 à 2 300 mots |
| Création de site internet | /creation-site-internet-paris/ | Transformer /nos-services/ puis 301 | création site internet paris | agence création site internet paris ; création site wordpress paris ; création site internet professionnel sur mesure | Page service | 1 800 à 2 500 |
| Site vitrine | /creation-de-site-vitrine-paris/ | Garder | création site vitrine paris | création site vitrine ; site vitrine sur mesure ; création site vitrine wordpress | Page service | 1 800 à 2 500 |
| Site e-commerce | /creation-de-site-e-commerce-paris/ | Garder | création site e-commerce paris | création boutique en ligne woocommerce ; site e-commerce sur mesure ; boutique en ligne sans commission | Page service | 1 800 à 2 500 |
| Refonte | /refonte-site-internet-paris/ | **Créer** | refonte site internet paris | agence refonte site internet ; refonte site wordpress ; refonte site vitrine | Page service | 1 500 à 2 200 |
| SEO local | /seo-local-paris/ | **Créer** | seo local paris | référencement local paris ; agence seo local ; optimisation fiche google business profile ; référencement google maps | Page service | 1 500 à 2 200 |
| Maintenance | /maintenance-wordpress-paris/ | **Créer** (condition : offre formalisée) | maintenance wordpress paris | maintenance site wordpress ; contrat maintenance wordpress ; mise à jour wordpress | Page service | 1 200 à 1 800 |
| Logo et identité | /branding-identite/ | Garder (slug : voir 4.5) | création logo paris | création logo identité visuelle ; identité visuelle entreprise ; charte graphique | Page service | 1 500 à 2 200 |
| Présentations | /maquettes-de-presentation/ | Garder, repositionner | création pitch deck (volume non vérifié) | présentation powerpoint professionnelle ; proposition commerciale ; support de présentation | Page service | 1 200 à 1 800 |
| Tarifs | /prix-site-internet-paris/ | **Créer** | prix site internet paris | prix site internet ; tarif création site internet ; création site internet pas cher paris ; devis site internet | Page tarifs | 1 200 à 1 800 |

Note sur les longueurs : la méthode seo-cluster prévoit 2 500 à 4 000 mots pour un pilier éditorial. Ici les piliers sont des **pages de conversion** : 1 500 à 2 500 mots utiles suffisent, la profondeur est portée par les satellites.

### 4.3 Clusters et sort des URL existantes

Légende des actions : **Garder** (URL conservée), **Retitrer** (title, H1 et focus Rank Math modifiés, URL conservée), **Fusionner** (contenu utile intégré dans la cible, puis 301), **Rediriger** (301 sans reprise), **Créer**.

#### Cluster A : Création de site internet (pilier /creation-site-internet-paris/)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /nos-services/ | Rediriger 301 vers /creation-site-internet-paris/ | | | | |
| /choisir-agence-web-paris/ | Garder, retitrer (ne plus commencer par « Agence web Paris ») | choisir agence web paris | comment choisir son agence web ; agence web ou freelance ; devis agence web | Satellite | Informationnelle |
| /wordpress-vs-webflow/ | Garder | wordpress ou webflow (non vérifié) | webflow avis ; cms site vitrine | Satellite comparatif | Commerciale |
| /cahier-des-charges-site-internet/ | Créer (mois 2) | cahier des charges site internet | modèle cahier des charges site web ; brief site internet | Satellite et ressource à télécharger | Informationnelle |
| /delai-creation-site-internet/ | Créer (mois 5) | combien de temps pour créer un site internet | délai création site internet ; étapes création site internet | Satellite | Informationnelle |
| /site-internet-bilingue/ | Créer (mois 5) | site internet bilingue (non vérifié) | création site multilingue wordpress ; site français anglais ; hreflang | Satellite service | Commerciale |

#### Cluster B : Site vitrine (pilier /creation-de-site-vitrine-paris/)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /anatomie-site-vitrine-qui-convertit/ | Garder | site vitrine qui convertit (non vérifié) | pages essentielles site vitrine ; structure site vitrine | Satellite | Informationnelle |
| /creer-un-site-vitrine-soi-meme-ou-en-agence/ | Garder | créer un site vitrine (non vérifié) | site vitrine soi-même ou agence ; freelance ou agence | Satellite comparatif | Commerciale |
| /site-vitrine-qui-ne-genere-pas-de-clients/ | Garder | site vitrine sans clients (non vérifié) | site internet qui ne rapporte rien ; site sans demandes de devis | Satellite, pont vers la refonte | Informationnelle |
| /creation-site-vitrine-tpe-paris/ | Retitrer. Si Search Console montre moins de 10 clics sur 3 mois (non vérifié) : 301 vers /budget-site-internet-tpe/ | budget site internet tpe | site internet tpe ; site vitrine petite entreprise | Satellite | Informationnelle |

#### Cluster C : E-commerce (pilier /creation-de-site-e-commerce-paris/)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /creer-un-site-e-commerce-professionnel/ | Garder | création site e-commerce | créer un site e-commerce ; étapes boutique en ligne | Satellite guide | Informationnelle |
| /shopify-vs-woocommerce/ | Garder | shopify ou woocommerce | shopify vs woocommerce ; woocommerce avis | Satellite comparatif | Commerciale |
| /combien-coute-un-site-e-commerce/ | Garder, **corriger le prix** (2 000 € HT et 4 à 6 semaines contre 1 650 € HT et 6 à 8 semaines ailleurs) | combien coûte un site e-commerce | prix site e-commerce ; tarif boutique en ligne | Satellite prix | Informationnelle |
| /creation-de-site-e-commerce-boulogne/ | Fusionner dans /site-internet-boulogne-billancourt/ puis 301 | | | | |
| /agence-woocommerce-paris/ | Créer (mois 4) | agence woocommerce paris | création site woocommerce ; développeur woocommerce paris | Page service satellite | Commerciale, locale |

#### Cluster D : Refonte (pilier /refonte-site-internet-paris/, à créer)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /refonte-site-internet/ | Garder, retitrer en guide | refonte site internet | quand refaire son site ; étapes refonte site web | Satellite guide | Informationnelle |
| /refonte-site-seo/ | Créer (mois 1) | refonte site internet sans perdre référencement | plan de redirection 301 ; migration seo ; refonte seo | Satellite | Informationnelle |
| /prix-refonte-site-internet/ | Créer (mois 1) | prix refonte site internet | coût refonte site web ; prix refonte site wordpress | Satellite prix | Informationnelle, commerciale |
| /cas-refonte-aurea-media/ | Créer (mois 4, avec 90 jours de données) | étude de cas, pas de mot-clé concurrentiel | refonte site elementor (non vérifié) | Étude de cas | Informationnelle |

#### Cluster E : SEO local (pilier /seo-local-paris/, à créer)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /optimiser-sa-fiche-google-my-business/ | Garder, absorbe la version coach | optimiser sa fiche google my business | optimiser fiche google business profile ; fiche google entreprise | Satellite | Informationnelle |
| /optimiser-fiche-google-my-business-coach/ | Fusionner dans la ligne précédente (section « coachs et consultants ») puis 301 | | | | |
| /google-my-business-artisans-tpe/ | Garder | google my business artisan | fiche google artisan ; google business profile artisan | Satellite | Informationnelle |
| /avis-google/ | Garder | avis google | comment obtenir des avis google ; répondre aux avis google | Satellite | Informationnelle |
| /agence-seo-clamart/ | Fusionner dans /creation-site-internet-clamart/ (section SEO local) puis 301 | | | | |
| /prix-seo-local/ | Créer (mois 2) | prix référencement naturel tpe | tarif seo local ; budget seo tpe | Satellite prix | Informationnelle, commerciale |
| /referencement-local-google/ | Créer (mois 4) | référencement local google | seo local c'est quoi ; pack local google | Satellite guide | Informationnelle |

#### Cluster F : Maintenance (pilier /maintenance-wordpress-paris/, à créer)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /prix-maintenance-site-internet/ | Créer (mois 3) | tarif maintenance site internet | contrat maintenance site wordpress prix ; coût maintenance site web | Satellite prix | Informationnelle |
| /wordpress-vs-webflow/ | Lien croisé depuis le cluster A | | | | |

#### Cluster G : Logo et identité (pilier /branding-identite/)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /branding-identite-visuelle/ | Rediriger 301 vers /branding-identite/ (doublon qui répond en 200 avec canonical) | | | | |
| /logo-charte-graphique-identite-visuelle/ | Garder, absorbe /logo-et-charte-graphique/ | logo charte graphique identité visuelle | différence logo charte graphique ; logo et charte graphique | Satellite | Informationnelle |
| /logo-et-charte-graphique/ | Fusionner (les « 5 questions avant de se lancer » deviennent une section) puis 301 | | | | |
| /refonte-identite-visuelle-entreprise/ | Garder | refonte identité visuelle | moderniser son logo ; rebranding entreprise | Satellite | Informationnelle |
| /prix-creation-logo/ | Créer (mois 3) | prix création logo | tarif logo ; prix identité visuelle | Satellite prix | Informationnelle |

#### Cluster H : Présentations (pilier /maquettes-de-presentation/)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /structure-pitch-deck/ | Créer (mois 6) | pitch deck (structure) | comment faire un pitch deck ; pitch deck investisseurs | Satellite | Informationnelle |

Ce cluster reste sous le minimum de 2 satellites : c'est assumé, le service est secondaire et la SERP est tenue par des studios spécialisés.

#### Cluster I : Tarifs (hub /prix-site-internet-paris/, à créer)

| URL | Action | Mot-clé principal | Secondaires | Type | Intention |
|---|---|---|---|---|---|
| /prix-site-vitrine-paris/ | Garder, retitrer (le title actuel commence par « Site vitrine Paris ») | prix site vitrine | combien coûte un site vitrine ; prix site vitrine paris | Satellite prix | Informationnelle |
| Satellites prix des autres clusters | /combien-coute-un-site-e-commerce/, /prix-refonte-site-internet/, /prix-creation-logo/, /prix-maintenance-site-internet/, /prix-seo-local/, /creation-site-vitrine-tpe-paris/ | | | | |

### 4.4 Pages métiers (hub /secteurs/, à créer)

Les 8 articles métiers deviennent des **pages métiers** : même URL, gabarit hybride (enjeux du métier, contraintes déontologiques, pages indispensables, offre et prix, réalisation liée, appel à l'action). Mot-clé principal au format « site internet + métier », qui correspond aux SERP observées (pages services et guides mêlés).

| URL | Action | Mot-clé principal | Secondaires | Famille | Preuve au portfolio |
|---|---|---|---|---|---|
| /secteurs/ | Créer | site internet profession libérale (non vérifié) | site internet par métier | Hub | |
| /site-vitrine-avocat/ | Garder, convertir | site internet avocat | création site internet avocat ; site vitrine avocat ; création site internet avocat paris | Conseil et droit | Avocat Paoli |
| /site-vitrine-medecin/ | Garder, convertir | site internet médecin | création site internet médecin ; site cabinet médical ; site internet médecin paris | Santé | Xpert Medical |
| /site-vitrine-kinesitherapeute/ | Garder, convertir | site internet kinésithérapeute | site kiné ; site internet kiné paris | Santé | Non vérifié |
| /site-internet-psychologue/ | **Créer** (mois 1) | création site internet psychologue | site internet thérapeute ; site psychologue paris | Santé | Non vérifié |
| /site-vitrine-architecte/ | Garder, convertir | site internet architecte | création site internet architecte ; portfolio architecte | Conseil et création | Non vérifié |
| /site-vitrine-coach-consultant/ | Garder, convertir | site internet coach | création site internet coach ; site consultant indépendant | Conseil et création | ENZYM, Sultan Conseils |
| /site-vitrine-pour-artisan/ | Garder, convertir | site internet artisan | création site internet artisan ; site internet plombier ; site internet électricien (non vérifié) | Artisans | Barber Korner |
| /site-vitrine-artisan-renovateur-en-batiment/ | Garder, retitrer vers le BTP | création site internet btp | site internet artisan bâtiment ; site internet entreprise rénovation | Artisans | Novexia Construction, Normande d'Isolation |
| /site-vitrine-restaurant/ | Garder, convertir | site internet restaurant | création site internet restaurant ; site restaurant réservation | Commerces | Non vérifié |
| /site-internet-association/ | **Créer** (mois 2) | création site internet association | site internet association paris ; site internet collectivité | Associations | Centre Franco-Iranien, Changer d'Air à Massy |
| /agence-web-collectivites-boulogne-billancourt/ | Fusionner dans /site-internet-association/ (section collectivités) puis 301, une fois la page publiée | | | | |
| /site-internet-expert-comptable/ | **Créer** (mois 6, non vérifié) | site internet expert-comptable | site cabinet comptable | Conseil et droit | Non vérifié |

### 4.5 Pages locales

| URL | Action | Mot-clé principal | Secondaires |
|---|---|---|---|
| /agence-web-hauts-de-seine/ | Garder (hub 92) | agence web hauts-de-seine | création site internet hauts-de-seine ; agence web 92 |
| /site-internet-boulogne-billancourt/ | Garder, absorbe l'e-commerce Boulogne | création site internet boulogne-billancourt | agence web boulogne-billancourt ; site e-commerce boulogne |
| /creation-site-internet-clamart/ | Garder (seule page déjà visible dans nos relevés), absorbe le SEO Clamart | création site internet clamart | agence web clamart ; seo clamart |
| Nouvelle ville du 92 (mois 5) | Créer seulement avec une référence client réelle dans la ville : Issy-les-Moulineaux, Neuilly-sur-Seine ou Levallois-Perret (non vérifié) | création site internet + ville | agence web + ville |
| /creation-site-internet-geneve/ (mois 6) | Créer si un client genevois accepte d'être cité (non vérifié) | création site internet genève | site internet suisse romande |

Pas de pages par arrondissement parisien : sans contenu propre, ce seraient des pages satellites quasi identiques, à risque de filtrage.

### 4.6 Catégories, doublons et plan de redirections 301

| Ancienne URL | Nouvelle cible | Moment |
|---|---|---|
| /agence-web-a-paris/ | /a-propos/ | Lancement |
| /nos-services/ | /creation-site-internet-paris/ | Lancement |
| /branding-identite-visuelle/ | /branding-identite/ | Lancement |
| /logo-et-charte-graphique/ | /logo-charte-graphique-identite-visuelle/ | Lancement |
| /optimiser-fiche-google-my-business-coach/ | /optimiser-sa-fiche-google-my-business/ | Lancement |
| /agence-seo-clamart/ | /creation-site-internet-clamart/ | Lancement |
| /creation-de-site-e-commerce-boulogne/ | /site-internet-boulogne-billancourt/ | Lancement |
| /agence-web-collectivites-boulogne-billancourt/ | /site-internet-association/ | Mois 2 |
| /creation-site-vitrine/ (catégorie) | /conseils/site-vitrine/ | Lancement |
| /creation-site-ecommerce/ (catégorie) | /conseils/site-e-commerce/ | Lancement |
| /google-my-business-seo-local/ (catégorie) | /conseils/seo-local/ | Lancement |
| /creation-site-web/ (catégorie) | /conseils/creation-site-web/ | Lancement |
| Catégorie « Identité visuelle » (URL non vérifiée) | /conseils/identite-visuelle/ | Lancement |
| Conditionnel : /creation-site-vitrine-tpe-paris/ | /budget-site-internet-tpe/ | Si moins de 10 clics sur 3 mois (non vérifié) |
| Conditionnel : /branding-identite/ | /creation-logo-paris/ | Si aucun clic ni lien externe (non vérifié) |

Les catégories passent en **noindex, follow** : leurs slugs actuels (« creation-site-vitrine », « creation-site-ecommerce ») ressemblent aux mots-clés des piliers et brouillent le signal. La faute « SEO (Référecement) » disparaît avec le renommage en « SEO local ».

### 4.7 Contrôle de cannibalisation

| Conflit constaté | Pages | Résolution |
|---|---|---|
| « agence web paris » | Accueil, /agence-web-a-paris/, /choisir-agence-web-paris/ | Accueil seule cible ; À propos sur « développeur WordPress Paris » ; article retitré « Choisir une agence web à Paris » |
| « création site vitrine paris » | Pilier vitrine, /creation-site-vitrine-tpe-paris/, catégorie /creation-site-vitrine/, /prix-site-vitrine-paris/ (title « Site vitrine Paris ») | Article TPE sur « budget site internet tpe », article prix sur « prix site vitrine », catégorie en noindex |
| « création site e-commerce » | Pilier e-commerce, catégorie /creation-site-ecommerce/, /creation-de-site-e-commerce-boulogne/ | Catégorie en noindex, fusion Boulogne |
| « logo charte graphique » | Deux articles | Fusion |
| Branding | /branding-identite/ et /branding-identite-visuelle/ | 301 du doublon |
| Fiche Google | Guide général et version coach | Fusion |
| Clamart | Page création et page SEO | Fusion |
| Boulogne-Billancourt | Page création, page e-commerce, page collectivités, page 92 | Une page ville, la page 92 reste hub, collectivités vers la page association |
| « refonte site internet » | Article existant et future page service | Recoupement SERP nul : l'article garde l'informationnel, le service vise « refonte site internet paris » et « agence refonte site internet » |

Après ces actions, aucun mot-clé principal n'est partagé par deux URL.

## 5. Nouvelles pages services : décisions

| Page | Décision | Priorité | Pourquoi (SERP et business) |
|---|---|---|---|
| Tarifs /prix-site-internet-paris/ | **Créer** | Haute | SERP « prix site internet paris » faible à moyenne (petites agences) ; les prix publics sont le premier différenciant ; les prix actuels se contredisent entre pages |
| Refonte /refonte-site-internet-paris/ | **Créer** | Haute | SERP « agence refonte site internet paris » moyenne (agences de taille moyenne, pas de géants) ; clientèle déjà équipée d'un site donc budget existant ; la refonte d'aurea-media.fr fournit une preuve concrète |
| SEO local /seo-local-paris/ | **Créer** | Haute | SERP « seo local paris » moyenne ; 3 articles fiche Google et avis déjà écrits sans page service vers laquelle convertir ; revenu récurrent ; cœur de cible (artisans, professions libérales). Ne pas viser « agence seo paris » (forte, hors positionnement) |
| Maintenance /maintenance-wordpress-paris/ | **Créer, sous condition** | Moyenne | SERP « maintenance wordpress paris » faible (freelances) ; revenu récurrent sur 45 clients existants. Condition : publier des forfaits clairs. Tant que la politique reste « sans abonnement imposé », une section dans chaque page service suffit |

Pour chacune, voir le détail constat, pourquoi, action, vérification en section 6 (H3, H4, H5, M1).

## 6. Recommandations priorisées

### Critique

**C1. Préserver les URL qui ont de la valeur pendant la refonte**
- Constat : 32 articles et 10 pages sont publiés à la racine (/%postname%/), la plupart entre juin et septembre 2026 ; la page Clamart ressort déjà en 5e position dans nos relevés. Rank Math redirige toute URL inconnue vers l'accueil (pas de vraie 404).
- Pourquoi : une URL indexée perdue sans redirection fait perdre ses positions ; la redirection globale vers l'accueil masque les erreurs et Google la traite comme une soft 404.
- Action : conserver /%postname%/ sur le nouveau thème ; exporter depuis Search Console les URL avec clics ou impressions avant la bascule ; saisir le plan 301 de la section 4.6 dans Rank Math, une règle par URL ; désactiver la redirection des 404 vers l'accueil.
- Vérification : crawl de la liste des anciennes URL à J+1 : 100 % en 200 ou en 301 direct vers la bonne cible, sans chaîne ; Search Console, rapport Pages : pas de hausse des 404 ; clics organiques stables à J+30.

### Haute

**H1. Réserver « agence web paris » à l'accueil**
- Constat : trois titles commencent par « Agence web (à) Paris » (accueil, /agence-web-a-paris/, /choisir-agence-web-paris/). Le title de l'accueil mélange deux mots-clés.
- Pourquoi : Google alterne entre les URL et aucune ne s'installe (recoupement nul entre « comment choisir son agence web » et « agence web paris »).
- Action : title accueil `Agence web Paris | Sites qui convertissent – Aurea Media` ; /agence-web-a-paris/ devient /a-propos/ (301) avec le focus « développeur WordPress Paris » (volume non vérifié) et le schéma Person ; article retitré `Choisir une agence web à Paris | Critères clés – Aurea Media`.
- Vérification : Search Console, requête « agence web paris » : une seule URL reçoit les impressions après 6 à 8 semaines.

**H2. Transformer /nos-services/ en pilier « création site internet paris »**
- Constat : le title actuel vise déjà « Création de Site Internet à Paris » mais l'URL /nos-services/ n'a pas le mot-clé ; aucune page ne vise « création site internet paris » en propre.
- Pourquoi : la requête a une SERP distincte de « agence web paris » (1 URL commune) et presque identique à « agence création site internet paris » (4 URL communes) : une page dédiée capte les deux.
- Action : créer /creation-site-internet-paris/ (reprise du contenu de /nos-services/ enrichi : les 7 services, méthode, prix, FAQ utilisateurs, réalisations), 301 depuis /nos-services/, lien obligatoire vers chaque service et chaque satellite du cluster A. Schéma Service et BreadcrumbList.
- Vérification : positions sur « création site internet paris » et « agence création site internet paris » suivies chaque semaine ; la page apparaît dans le top 30 à 3 mois (objectif réaliste face à Adveris, Sortlist et Noiise).

**H3. Créer la page tarifs et aligner tous les prix**
- Constat : l'article /combien-coute-un-site-e-commerce/ annonce « à partir de 2 000 € HT en 4 à 6 semaines », la page e-commerce 1 650 € HT et la FAQ de l'accueil 6 à 8 semaines ; le même article cite une maintenance à 89 € HT/mois alors que les pages services promettent « sans forfait mensuel obligatoire » ; l'accueil affiche « +300 % de trafic » sans source.
- Pourquoi : un prospect qui compare repère l'incohérence et perd confiance ; les prix affichés sont l'argument le plus fort face à Palmsquare (4 000 à 8 000 €) ou Feel and Clic (site vitrine PME dès 15 000 € d'après la SERP).
- Action : créer /prix-site-internet-paris/ avec les grilles vitrine (1 000, 1 800 € HT, sur devis), e-commerce (1 650, 3 200 € HT), refonte, maintenance, logo, présentations, SEO local, plus un calcul du coût total sur 3 ans ; corriger les chiffres dans tous les articles ; sourcer ou retirer « +300 % ».
- Vérification : recherche de « € HT » dans le texte des 50 URL : un seul prix par prestation ; Search Console : impressions sur « prix site internet paris » et « tarif création site internet » à 8 semaines.

**H4. Créer la page service refonte**
- Constat : la refonte est vendue (page Clamart) et documentée (article de 2 400 mots), mais sans page service.
- Pourquoi : SERP moyenne, prospects déjà équipés et solvables, preuve disponible avec la refonte d'aurea-media.fr.
- Action : /refonte-site-internet-paris/ : signaux qui imposent une refonte, méthode (audit, conservation des URL, plan 301, migration), prix indicatif, étude de cas, lien vers la maintenance. Title `Refonte site internet Paris | Sans perte SEO – Aurea Media`.
- Vérification : demandes de devis avec le type « refonte » (ajouter l'option au formulaire) ; impressions sur « refonte site internet paris » à 8 semaines.

**H5. Créer la page service SEO local**
- Constat : 3 articles sur la fiche Google et les avis, une page « agence SEO Clamart », aucune page service ; la catégorie s'appelle « SEO (Référecement) ».
- Pourquoi : SERP « seo local paris » moyenne ; offre récurrente ; cible artisans et professions libérales qui cherchent d'abord à sortir dans Google Maps. La tête « agence seo paris » est hors de portée (SEO.fr, Eskimoz, Pixalione).
- Action : /seo-local-paris/ : audit de fiche, optimisation Google Business Profile, avis, pages locales, citations NAP, suivi mensuel, forfait affiché. Title `SEO local Paris | Google Maps et fiche Google – Aurea Media`.
- Vérification : trafic et conversions de la page ; impressions sur « seo local paris » et « référencement local paris » à 8 semaines.

**H6. Faire les 5 fusions de doublons au lancement**
- Constat : doublons listés en 4.7 (logo, fiche Google coach, Clamart, Boulogne e-commerce, branding).
- Pourquoi : chaque doublon divise les signaux et crée des pages minces.
- Action : intégrer le contenu utile dans la page cible (section dédiée), puis 301 ; mettre à jour tous les liens internes vers la cible pour éviter les redirections internes.
- Vérification : crawl : zéro lien interne vers une URL redirigée ; la page cible gagne les requêtes de l'ancienne dans Search Console.

**H7. Retitrer les satellites qui empiètent sur les piliers**
- Constat : /prix-site-vitrine-paris/ (« Site vitrine Paris 2026 : prix… »), /creation-site-vitrine-tpe-paris/ (« Création Site Vitrine TPE Paris »), /refonte-site-internet/, /choisir-agence-web-paris/.
- Pourquoi : un title qui commence par le mot-clé du pilier fait concurrence au pilier.
- Action : titles proposés en section 11 ; H1 alignés ; focus Rank Math mis à jour.
- Vérification : pour chaque pilier, la requête principale n'affiche qu'une URL du site dans Search Console.

### Moyenne

**M1. Créer la page maintenance WordPress quand l'offre est fixée**
- Constat : maintenance citée partout, sans offre lisible ; 89 € HT/mois dans un seul article.
- Pourquoi : SERP faible, revenu récurrent, argument de fidélisation.
- Action : fixer 2 ou 3 forfaits (la SERP montre une fourchette de 29 à 199 €/mois), publier /maintenance-wordpress-paris/ au mois 3 avec l'article prix.
- Vérification : nombre de contrats signés via la page ; impressions sur « maintenance wordpress paris ».

**M2. Hub Secteurs et pages métiers**
- Constat : 8 articles métiers solides (1 650 à 3 230 mots), sans hub ni lien vers une offre dédiée.
- Pourquoi : les SERP « site internet + métier » mêlent pages services et guides ; ce sont les requêtes les plus accessibles et les plus proches de l'achat.
- Action : créer /secteurs/ ; convertir les 8 articles en pages métiers (même URL) avec bloc offre, prix de départ, réalisation liée, contraintes déontologiques (CNB, CNOM, CNOMK) ; créer psychologue, association et expert-comptable.
- Vérification : taux de conversion des pages métiers (objectif : au moins le niveau des pages services) ; impressions sur les requêtes « site internet + métier ».

**M3. Catégories en noindex et nouveau préfixe /conseils/**
- Constat : catégories sans H1, meta de 293 à 364 caractères, slugs proches des mots-clés des piliers.
- Pourquoi : pages faibles qui entrent en concurrence avec les piliers.
- Action : préfixe de catégorie /conseils/, renommage, noindex follow, 301 des anciennes URL.
- Vérification : les anciennes URL de catégorie sortent de l'index en 4 à 8 semaines.

**M4. Déployer la matrice de maillage (section 7)**
- Constat : maillage actuel surtout par menus et cartes d'articles.
- Pourquoi : les liens contextuels transmettent le sujet et guident vers la conversion.
- Action : liens obligatoires en premier, ancres variées, bloc « Services liés » en bas de chaque satellite.
- Vérification : crawl : chaque satellite a au moins 3 liens entrants contextuels ; aucune ancre ne dépasse 40 % des liens vers une page.

**M5. Page Agence WooCommerce (mois 4)**
- Constat : l'offre e-commerce est 100 % WooCommerce, la SERP « agence woocommerce paris » est distincte de celle du pilier e-commerce.
- Pourquoi : SERP moyenne, ticket élevé, argument « sans commission » face à Shopify.
- Action : page service satellite, liée au pilier e-commerce et au comparatif Shopify.
- Vérification : impressions et devis e-commerce attribués à la page.

### Basse

**B1. Pages locales supplémentaires** : une par trimestre au maximum, uniquement avec une réalisation dans la ville ; vérification : la page reçoit des impressions locales à 8 semaines, sinon fusion dans la page 92.
**B2. International francophone** : page Genève et page bilingue (mois 5 et 6), à valider par une recherche Google Suisse (non vérifié) ; vérification : impressions depuis la Suisse dans Search Console.
**B3. Présentations** : repositionner vers « pitch deck » et « présentation PowerPoint professionnelle » (volumes non vérifiés), un seul satellite ; vérification : demandes « maquettes » dans le formulaire.
**B4. Plan du site HTML** : le passer en noindex ou le supprimer (301 vers l'accueil), le sitemap XML suffit.

## 7. Matrice de maillage interne

Règles : chaque satellite lie son pilier (obligatoire) ; chaque pilier lie tous ses satellites (obligatoire) ; 2 ou 3 liens entre satellites d'un même cluster (recommandé) ; 0 ou 1 lien vers un autre cluster (optionnel) ; au moins 3 liens entrants par page ; ancres variées, aucune au delà de 40 % des liens vers une même page ; liens placés dans le texte, pas seulement en bas de page.

### 7.1 Navigation globale

- Menu : Services (création de site internet, site vitrine, e-commerce, refonte, SEO local, maintenance, logo et identité, présentations), Tarifs, Réalisations, Secteurs, Conseils, À propos, Contact.
- Pied de page : Hauts-de-Seine, Boulogne-Billancourt, Clamart ; 4 secteurs (avocat, médecin, artisan, BTP) ; pages légales.
- Fil d'Ariane sur toutes les pages (schéma BreadcrumbList).

### 7.2 Liens contextuels

| Depuis | Vers | Ancre proposée | Type |
|---|---|---|---|
| Accueil | /creation-site-internet-paris/ | création de site internet à Paris | Obligatoire |
| Accueil | /creation-de-site-vitrine-paris/ | création de site vitrine | Obligatoire |
| Accueil | /creation-de-site-e-commerce-paris/ | boutique WooCommerce sur mesure | Obligatoire |
| Accueil | /refonte-site-internet-paris/ | refonte de votre site | Obligatoire |
| Accueil | /seo-local-paris/ | être visible dans Google Maps | Obligatoire |
| Accueil | /prix-site-internet-paris/ | nos tarifs | Obligatoire |
| Accueil | /secteurs/, /agence-web-hauts-de-seine/, /a-propos/ | sites par métier ; agence web dans les Hauts-de-Seine ; Alexandre Coury | Recommandé |
| /creation-site-internet-paris/ | Les 7 pages services | site vitrine sur mesure ; boutique en ligne ; refonte ; référencement local ; maintenance WordPress ; création de logo ; pitch deck | Obligatoire |
| /creation-site-internet-paris/ | /choisir-agence-web-paris/, /cahier-des-charges-site-internet/, /delai-creation-site-internet/, /wordpress-vs-webflow/, /site-internet-bilingue/ | bien choisir son agence web ; modèle de cahier des charges ; délai de création ; WordPress ou Webflow ; site bilingue français anglais | Obligatoire |
| /creation-site-internet-paris/ | /prix-site-internet-paris/ | prix d'un site internet à Paris | Obligatoire |
| Satellites cluster A | /creation-site-internet-paris/ | création de site internet à Paris ; agence de création de site internet ; faire créer son site | Obligatoire |
| /choisir-agence-web-paris/ | /cahier-des-charges-site-internet/ | rédiger son cahier des charges | Recommandé |
| /cahier-des-charges-site-internet/ | /delai-creation-site-internet/ | combien de temps prévoir | Recommandé |
| /wordpress-vs-webflow/ | /maintenance-wordpress-paris/ | maintenance d'un site WordPress | Optionnel |
| /creation-de-site-vitrine-paris/ | /anatomie-site-vitrine-qui-convertit/, /creer-un-site-vitrine-soi-meme-ou-en-agence/, /site-vitrine-qui-ne-genere-pas-de-clients/, /prix-site-vitrine-paris/, /creation-site-vitrine-tpe-paris/ | pages indispensables d'un site vitrine ; le faire soi-même ou passer par une agence ; site vitrine qui ne génère pas de contacts ; prix d'un site vitrine ; budget d'une TPE | Obligatoire |
| /creation-de-site-vitrine-paris/ | /secteurs/ et 4 pages métiers (avocat, médecin, artisan, BTP) | votre métier ; site pour avocat ; site pour médecin ; site pour artisan ; site pour le bâtiment | Recommandé |
| Satellites cluster B | /creation-de-site-vitrine-paris/ | création de site vitrine à Paris ; site vitrine sur mesure ; notre offre site vitrine | Obligatoire |
| /anatomie-site-vitrine-qui-convertit/ | /site-vitrine-qui-ne-genere-pas-de-clients/ | pourquoi un site ne convertit pas | Recommandé |
| /creer-un-site-vitrine-soi-meme-ou-en-agence/ | /prix-site-vitrine-paris/ | ce que coûte un site vitrine | Recommandé |
| /creation-site-vitrine-tpe-paris/ | /prix-site-vitrine-paris/ | prix détaillé d'un site vitrine | Recommandé |
| /site-vitrine-qui-ne-genere-pas-de-clients/ | /refonte-site-internet-paris/ | refondre son site | Optionnel |
| /creation-de-site-e-commerce-paris/ | /creer-un-site-e-commerce-professionnel/, /shopify-vs-woocommerce/, /combien-coute-un-site-e-commerce/, /agence-woocommerce-paris/ | étapes pour créer un site e-commerce ; Shopify ou WooCommerce ; prix d'un site e-commerce ; agence WooCommerce à Paris | Obligatoire |
| Satellites cluster C | /creation-de-site-e-commerce-paris/ | création de site e-commerce à Paris ; boutique WooCommerce sur mesure ; vendre en ligne sans commission | Obligatoire |
| /shopify-vs-woocommerce/ | /combien-coute-un-site-e-commerce/ et /agence-woocommerce-paris/ | coût réel sur 3 ans ; développeur WooCommerce | Recommandé |
| /creer-un-site-e-commerce-professionnel/ | /shopify-vs-woocommerce/ | choisir sa plateforme | Recommandé |
| /refonte-site-internet-paris/ | /refonte-site-internet/, /refonte-site-seo/, /prix-refonte-site-internet/, /cas-refonte-aurea-media/ | guide de la refonte ; garder son référencement ; prix d'une refonte ; notre propre refonte | Obligatoire |
| /refonte-site-internet-paris/ | /maintenance-wordpress-paris/ | maintenance après la mise en ligne | Recommandé |
| Satellites cluster D | /refonte-site-internet-paris/ | refonte de site internet à Paris ; agence de refonte ; confier sa refonte | Obligatoire |
| /refonte-site-internet/, /refonte-site-seo/, /prix-refonte-site-internet/ | entre eux | plan de redirection ; budget de refonte ; étapes d'une refonte | Recommandé |
| /cas-refonte-aurea-media/ | /refonte-site-seo/ | méthode de migration | Recommandé |
| /seo-local-paris/ | /optimiser-sa-fiche-google-my-business/, /google-my-business-artisans-tpe/, /avis-google/, /prix-seo-local/, /referencement-local-google/ | optimiser sa fiche Google ; fiche Google pour artisan ; obtenir des avis Google ; prix du SEO local ; guide du référencement local | Obligatoire |
| /seo-local-paris/ | /creation-site-internet-clamart/, /site-internet-boulogne-billancourt/, /agence-web-hauts-de-seine/ | Clamart ; Boulogne-Billancourt ; Hauts-de-Seine | Recommandé |
| Satellites cluster E | /seo-local-paris/ | SEO local à Paris ; référencement local ; accompagnement SEO local | Obligatoire |
| /optimiser-sa-fiche-google-my-business/ | /avis-google/ et /prix-seo-local/ | avis clients ; budget SEO local | Recommandé |
| /google-my-business-artisans-tpe/ | /site-vitrine-pour-artisan/ | site internet pour artisan | Optionnel |
| /referencement-local-google/ | /optimiser-sa-fiche-google-my-business/ | fiche Google Business Profile | Recommandé |
| /maintenance-wordpress-paris/ | /prix-maintenance-site-internet/, /refonte-site-internet-paris/, /wordpress-vs-webflow/ | tarifs de maintenance ; quand la refonte s'impose ; pourquoi WordPress | Obligatoire (1er), recommandé (2 autres) |
| /prix-maintenance-site-internet/ | /maintenance-wordpress-paris/ | maintenance WordPress à Paris ; contrat de maintenance | Obligatoire |
| /branding-identite/ | /logo-charte-graphique-identite-visuelle/, /refonte-identite-visuelle-entreprise/, /prix-creation-logo/ | logo, charte et identité ; moderniser son identité ; prix d'un logo | Obligatoire |
| /branding-identite/ | /maquettes-de-presentation/ | décliner l'identité sur vos présentations | Recommandé |
| Satellites cluster G | /branding-identite/ | création de logo à Paris ; identité visuelle sur mesure | Obligatoire |
| /logo-charte-graphique-identite-visuelle/, /refonte-identite-visuelle-entreprise/, /prix-creation-logo/ | entre eux | budget d'un logo ; refonte d'identité ; charte graphique | Recommandé |
| /maquettes-de-presentation/ | /structure-pitch-deck/ | structure d'un pitch deck | Obligatoire |
| /structure-pitch-deck/ | /maquettes-de-presentation/ et /site-vitrine-coach-consultant/ | création de pitch deck ; site pour consultant | Obligatoire et optionnel |
| /prix-site-internet-paris/ | Tous les articles prix (6) et toutes les pages services | prix d'un site vitrine ; prix d'un site e-commerce ; prix d'une refonte ; prix d'un logo ; prix de la maintenance ; prix du SEO local ; budget TPE | Obligatoire |
| Articles prix | /prix-site-internet-paris/ | tous nos tarifs ; grille tarifaire complète | Obligatoire |
| /secteurs/ | Les 12 pages métiers | site internet + métier | Obligatoire |
| Pages métiers | /creation-de-site-vitrine-paris/ | création de site vitrine ; site vitrine sur mesure | Obligatoire |
| Pages métiers | /prix-site-internet-paris/ | nos tarifs | Recommandé |
| Pages métiers santé (médecin, kiné, psychologue) | entre elles et /seo-local-paris/ | site pour kiné ; site pour médecin ; site pour psychologue ; visibilité locale | Recommandé |
| Pages métiers conseil (avocat, architecte, coach, expert-comptable) | entre elles | site pour avocat ; portfolio d'architecte ; site pour consultant | Recommandé |
| /site-vitrine-pour-artisan/ et /site-vitrine-artisan-renovateur-en-batiment/ | entre elles, /google-my-business-artisans-tpe/, /seo-local-paris/ | site pour le bâtiment ; fiche Google artisan ; SEO local | Recommandé |
| Pages métiers | /realisations-agence-web/ (ancre sur le projet lié) | nom du projet (Novexia Construction, Avocat Paoli, Xpert Medical…) | Recommandé |
| /agence-web-hauts-de-seine/ | /site-internet-boulogne-billancourt/, /creation-site-internet-clamart/, villes futures | création de site à Boulogne-Billancourt ; agence web à Clamart | Obligatoire |
| Pages villes | /agence-web-hauts-de-seine/, /creation-de-site-vitrine-paris/, /seo-local-paris/ | agence web dans le 92 ; site vitrine ; SEO local | Obligatoire, recommandé |
| /site-internet-bilingue/ | /creation-site-internet-geneve/ | clients en Suisse romande | Optionnel |
| /a-propos/ | /cas-refonte-aurea-media/, /realisations-agence-web/, /site-internet-bilingue/ | notre refonte ; nos réalisations ; clients à l'international | Recommandé |

### 7.3 Contrôle des liens entrants des pages à créer

| Page | Liens entrants prévus | Total |
|---|---|---|
| /refonte-site-seo/ | pilier refonte, guide refonte, prix refonte, étude de cas | 4 |
| /prix-refonte-site-internet/ | pilier refonte, tarifs, guide refonte, refonte SEO | 4 |
| /site-internet-psychologue/ | secteurs, médecin, kiné, tarifs (retour) | 3 et plus |
| /prix-seo-local/ | pilier SEO local, tarifs, guide fiche Google | 3 |
| /site-internet-association/ | secteurs, réalisations, pilier vitrine (bloc secteurs) | 3 |
| /cahier-des-charges-site-internet/ | pilier création, choisir une agence, délai | 3 |
| /prix-maintenance-site-internet/ | pilier maintenance, tarifs, prix e-commerce | 3 |
| /prix-creation-logo/ | pilier logo, tarifs, article logo et charte | 3 |
| /cas-refonte-aurea-media/ | pilier refonte, refonte SEO, À propos | 3 |
| /agence-woocommerce-paris/ | pilier e-commerce, Shopify contre WooCommerce, pilier création | 3 |
| /referencement-local-google/ | pilier SEO local, guide fiche Google, avis Google | 3 |
| /site-internet-bilingue/ | pilier création, Genève, À propos | 3 |
| /delai-creation-site-internet/ | pilier création, cahier des charges, pilier vitrine | 3 |
| /structure-pitch-deck/ | présentations, logo, page coach | 3 |
| /site-internet-expert-comptable/ | secteurs, avocat, coach | 3 |
| Ville du 92, /creation-site-internet-geneve/ | hub 92 ou page bilingue, SEO local ou À propos, pied de page ou accueil | 3 |

## 8. Calendrier éditorial sur 6 mois

Hypothèse : mois 1 = octobre 2026 ; décaler le tout si la mise en ligne de la refonte glisse. Classement par potentiel business : proximité de l'achat, ticket, difficulté de la SERP.

### Lot 0 : au lancement de la refonte

- Pages créées : /creation-site-internet-paris/, /prix-site-internet-paris/, /refonte-site-internet-paris/, /seo-local-paris/, /a-propos/, /secteurs/.
- Fusions et 301 : section 4.6 (sauf collectivités, au mois 2).
- Retitrages : section 11 ; conversion des 8 articles métiers en pages métiers ; correction des prix.

### Mois 1 à 6

| Mois | Titre (H1 proposé) | Slug | Mot-clé principal | Intention | Cluster | Pilier cible | Potentiel |
|---|---|---|---|---|---|---|---|
| 1 | Prix d'une refonte de site internet : ce qui fait varier le devis | /prix-refonte-site-internet/ | prix refonte site internet | Informationnelle à visée commerciale | Refonte | /refonte-site-internet-paris/ | Élevé |
| 1 | Refonte de site internet : garder son référencement Google | /refonte-site-seo/ | refonte site internet sans perdre référencement | Informationnelle | Refonte | /refonte-site-internet-paris/ | Élevé |
| 1 | Site internet pour psychologue : rassurer et remplir son agenda | /site-internet-psychologue/ | création site internet psychologue | Commerciale | Métiers (santé) | /creation-de-site-vitrine-paris/ | Élevé |
| 2 | Prix du SEO local pour une TPE : quel budget mensuel ? | /prix-seo-local/ | prix référencement naturel tpe | Informationnelle à visée commerciale | SEO local | /seo-local-paris/ | Élevé |
| 2 | Site internet pour association : dons, adhésions et bénévoles | /site-internet-association/ | création site internet association | Commerciale | Métiers | /creation-de-site-vitrine-paris/ | Moyen |
| 2 | Cahier des charges de site internet : modèle gratuit et exemple | /cahier-des-charges-site-internet/ | cahier des charges site internet | Informationnelle | Création de site internet | /creation-site-internet-paris/ | Moyen (capte des contacts) |
| 3 | Maintenance WordPress à Paris : mises à jour, sauvegardes, sécurité | /maintenance-wordpress-paris/ | maintenance wordpress paris | Transactionnelle, locale | Maintenance | (pilier) | Élevé, récurrent |
| 3 | Prix de la maintenance d'un site internet : forfaits et contenu | /prix-maintenance-site-internet/ | tarif maintenance site internet | Informationnelle | Maintenance | /maintenance-wordpress-paris/ | Moyen |
| 3 | Prix d'un logo : ce que vous payez vraiment | /prix-creation-logo/ | prix création logo | Informationnelle | Logo et identité | /branding-identite/ | Moyen |
| 4 | Refonte d'aurea-media.fr : ce que nous avons gardé, redirigé et créé | /cas-refonte-aurea-media/ | étude de cas (pas de mot-clé concurrentiel) | Informationnelle | Refonte | /refonte-site-internet-paris/ | Élevé (preuve) |
| 4 | Agence WooCommerce à Paris : une boutique sans commission | /agence-woocommerce-paris/ | agence woocommerce paris | Commerciale, locale | E-commerce | /creation-de-site-e-commerce-paris/ | Élevé |
| 4 | Référencement local Google : le guide pour les entreprises de proximité | /referencement-local-google/ | référencement local google | Informationnelle | SEO local | /seo-local-paris/ | Moyen |
| 5 | Site internet bilingue français anglais : méthode et coûts | /site-internet-bilingue/ | site internet bilingue (non vérifié) | Commerciale | Création de site internet | /creation-site-internet-paris/ | Moyen |
| 5 | Combien de temps pour créer un site internet ? | /delai-creation-site-internet/ | combien de temps pour créer un site internet | Informationnelle | Création de site internet | /creation-site-internet-paris/ | Moyen |
| 5 | Création de site internet à [ville du 92 avec client] | /creation-site-internet-[ville]/ | création site internet [ville] (non vérifié) | Transactionnelle, locale | Local | /agence-web-hauts-de-seine/ | Moyen |
| 6 | Création de site internet à Genève | /creation-site-internet-geneve/ | création site internet genève (non vérifié) | Transactionnelle, locale | Local international | /creation-site-internet-paris/ | Moyen |
| 6 | Pitch deck : la structure qui décroche un rendez-vous | /structure-pitch-deck/ | pitch deck structure (non vérifié) | Informationnelle | Présentations | /maquettes-de-presentation/ | Faible |
| 6 | Site internet pour expert-comptable : inspirer confiance | /site-internet-expert-comptable/ | site internet expert-comptable (non vérifié) | Commerciale | Métiers | /creation-de-site-vitrine-paris/ | Moyen |

Longueurs : pages services 1 200 à 2 200 mots, pages métiers 1 500 à 2 200, satellites 1 200 à 1 800. Chaque contenu : auteur Alexandre Coury avec bio, date de mise à jour, une donnée ou un exemple tiré d'un projet réel (E-E-A-T), schéma BlogPosting ou Service avec BreadcrumbList. Les FAQ restent pour le lecteur, sans balisage FAQPage attendu en SERP.

## 9. Personas et ciblage

| Priorité | Persona | Déclencheur | Budget visé | Pages d'entrée | Objections à lever | Preuve disponible |
|---|---|---|---|---|---|---|
| 1 | **Dirigeant de PME B2B** (industrie, holding, négoce, salon) | Site daté, image en décalage avec l'entreprise, besoin bilingue | Sur devis, au delà de 1 800 € HT | Refonte, création site internet Paris, site bilingue, réalisations | Peur de perdre le référencement, capacité d'un indépendant à suivre | Groupe Verisol, TCM Grand Est, Normande d'Isolation, Havel Trading, Dual Tech |
| 2 | **Profession libérale réglementée** (avocat, médecin, kiné, psychologue, architecte, expert-comptable) | Installation, association, patientèle ou clientèle à développer localement | 1 000 à 1 800 € HT, plus SEO local | Pages métiers, SEO local, tarifs | Déontologie (CNB, CNOM, CNOMK), manque de temps pour rédiger | Avocat Paoli, Xpert Medical |
| 3 | **Artisan et entreprise du bâtiment en Île-de-France** (rénovation, isolation, plomberie) | Dépendance aux plateformes de devis, besoin de chantiers qualifiés | 1 800 à 3 500 € HT, plus SEO local | Page BTP, page artisan, fiche Google artisan, SEO local | « Le bouche à oreille suffit », peur de payer sans résultat | Novexia Construction (11 pages, SEO local IDF) |
| 4 | **Commerçant ou marque qui vend en ligne** | Frais Shopify, envie de propriété, boutique physique à prolonger | 1 650 à 3 200 € HT et plus | E-commerce, agence WooCommerce, Shopify contre WooCommerce, prix e-commerce | Gestion du catalogue, sécurité, maintenance | Sunrock |
| 5 | **Indépendant, coach, consultant** | Lancement d'activité, besoin de crédibilité et de rendez-vous | 1 000 à 1 800 € HT, plus présentations | Page coach, site vitrine, présentations, logo | Prix face aux outils à faire soi-même | ENZYM, Sultan Conseils |
| 6 | **Entreprise francophone hors de France** (Genève, Lisbonne, Montréal, Dubaï) | Marché local cher, besoin d'un site bilingue | Sur devis | Site bilingue, Genève, réalisations | Travail à distance, fuseaux horaires | Portugal Expert Properties ; clients cités sur l'accueil |
| 7 | **Association** | Recherche de dons, d'adhérents, visibilité d'événements | 1 000 € HT | Page association | Budget très serré | Centre Franco-Iranien, Changer d'Air à Massy |

### Métiers et secteurs à forte valeur

| Métier | Valeur business | Difficulté SERP | Preuve | Décision |
|---|---|---|---|---|
| PME B2B en refonte | Très haute (ticket, bilingue, maintenance) | Moyenne | Forte | Priorité 1 |
| Avocats | Haute (ticket, image, récurrence SEO) | Moyenne à forte | Oui | Priorité 1 |
| BTP et rénovation | Haute (valeur d'un chantier, besoin de devis) | Moyenne | Oui | Priorité 1 |
| Médecins, kinés, psychologues | Haute (installation, SEO local) | Faible à moyenne | Partielle | Priorité 2 |
| Architectes, experts-comptables | Haute (non vérifié pour l'expert-comptable) | Moyenne | Non | Priorité 2 |
| E-commerce TPE | Moyenne à haute | Moyenne à forte | Oui | Priorité 2 |
| Coachs et consultants | Moyenne (volume, ticket bas, vente croisée présentations) | Moyenne | Oui | Priorité 3 |
| Restaurants | Moyenne (outils de réservation déjà en place) | Moyenne à forte | Non | Priorité 3 |
| Associations | Basse | Moyenne à forte | Oui | Priorité 4 |

## 10. Concurrents observés et angles de différenciation

Données tirées des extraits de résultats (non vérifiées sur les sites eux-mêmes).

| Groupe | Concurrents rencontrés | Ce qu'ils mettent en avant | Réponse d'Aurea Media |
|---|---|---|---|
| Grandes agences parisiennes | Adveris (70 experts internes), Beyonds, 6LAB (agence-web-paris.com), Noiise, WeDezign, Globalis, Youdemus | Taille, ancienneté, références grands comptes | Un seul interlocuteur qui conçoit, code et référence ; délai de 4 semaines ; prix affichés |
| Agences qui visent TPE et PME | Palmsquare (WordPress 4 000 à 8 000 €), Feel and Clic (site vitrine PME dès 15 000 €), Pixel Paris (« ultra rapide »), Kapuccino, Beecomy, Smart Agency, Frametonic, Transacts, L'agence123, Cohérence, Sedigitaliser | Sur mesure pour PME, pages villes et métiers | Même sur mesure pour 1 000 à 3 200 € HT, preuve par les avis (4,9/5 sur 39) |
| Offres par abonnement ou low cost | Simplébo (dès 50 € HT/mois), Linkeo, Jalis, Epixelic (1 690 € HT avec rédaction), EvolveTech (dès 399 €) | Prix d'entrée bas | Propriété totale du site, pas d'abonnement ni de commission, coût total sur 3 ans comparé sur la page tarifs |
| Spécialistes par métier | WebGazelle (pages métiers, 1 500 sites), Geoffrey Leduc (2 000 € HT par métier), Web iPro, Wasabi Web Design, Digital Avocat, Septeo, Psy-Site, Visipsy, Kine-site | Connaissance d'un métier | Pages métiers avec déontologie, réalisations du secteur, SEO local inclus |
| Refonte | Churchill (120 refontes depuis 2018), Actif Digital, Pulsar, Vivado | Méthode de refonte | Étude de cas publique de la refonte d'aurea-media.fr, plan 301 expliqué |
| SEO | SEO.fr, Eskimoz, Pixalione, Agence 90, Linkeo, Maxelik ; en local : Komunike, SEO Supernova, Ekko Media, Digiberries | Expertise SEO pure | Ne pas les affronter sur « agence seo paris » ; SEO local packagé pour TPE, lié au site |
| Maintenance | Yes We Blog, Athirion, 10ket, EvidenceWP, Maintenance WP, Graindesite (29 €/mois), Pulsar (159 € HT/mois) | Prix mensuel, réactivité | Maintenance par le développeur qui a construit le site, forfaits publics |
| Logo et branding | Graphistes : Farouk Nasri, JR Graphiste, Mathieu Orenge, Agence Sweep ; agences : Bien-Fondé, Graphéine, 4Beez | Créativité, portfolio | Identité conçue pour le site, trois pistes, droits cédés |
| Comparateurs et plateformes | Sortlist, La Fabrique du Net, Codeur, Trustfolio, PagesJaunes, WP Marmite Start ; Wix, Shopify, e-monsite, SiteW, HubSpot | Trafic sur les têtes de requêtes | Obtenir des profils et avis sur Sortlist, La Fabrique du Net et Codeur plutôt que les concurrencer |

### Angles de différenciation à porter sur chaque page

1. **Prix publics et justes** : 1 000 € HT le site vitrine, 1 650 € HT la boutique, quand les agences parisiennes observées affichent 4 000 à 15 000 € ou « sur devis ». Ne pas se positionner sur « pas cher » : le terrain est pris par des offres à 399 €.
2. **Propriété totale, zéro abonnement, zéro commission** : site, domaine, code et fichiers au nom du client, face aux formules par abonnement et à Shopify.
3. **Un seul interlocuteur** qui fait stratégie, design, code et SEO, joignable après la livraison.
4. **Délai annoncé et tenu** : 4 semaines pour un site vitrine, calendrier fourni au départ.
5. **Preuve** : 4,9/5 sur 39 avis Google, 45 projets et plus, réalisations par secteur. Le chiffre « +300 % de trafic » doit être sourcé par une étude de cas ou retiré.
6. **Métiers réglementés** : déontologie des avocats, médecins et kinés intégrée dès la conception.
7. **International francophone et bilingue** : clients à Genève, Lisbonne, Montréal et Dubaï, balises hreflang.
8. **Refonte sans perte de trafic, démontrée sur son propre site**.

## 11. Balises Rank Math proposées

Titles de 50 à 60 caractères avec le mot-clé en tête, meta descriptions de 140 à 160 caractères (longueurs vérifiées par script).

| Page | SEO Title (caractères) |
|---|---|
| Accueil | `Agence web Paris | Sites qui convertissent – Aurea Media` (56) |
| /creation-site-internet-paris/ | `Création site internet Paris | Dès 1 000 € HT – Aurea Media` (59) |
| /creation-de-site-vitrine-paris/ | `Création site vitrine Paris | Dès 1 000 € HT – Aurea Media` (58) |
| /creation-de-site-e-commerce-paris/ | `Création site e-commerce Paris | WooCommerce – Aurea Media` (58) |
| /refonte-site-internet-paris/ | `Refonte site internet Paris | Sans perte SEO – Aurea Media` (58) |
| /seo-local-paris/ | `SEO local Paris | Google Maps et fiche Google – Aurea Media` (59) |
| /maintenance-wordpress-paris/ | `Maintenance WordPress Paris | Site sécurisé – Aurea Media` (57) |
| /prix-site-internet-paris/ | `Prix site internet Paris | Tarifs affichés – Aurea Media` (56) |
| /branding-identite/ | `Création logo Paris | Identité visuelle – Aurea Media` (53) |
| /maquettes-de-presentation/ | `Création pitch deck Paris | Slides sur mesure – Aurea Media` (59) |
| /a-propos/ | `Développeur WordPress Paris | Alexandre Coury – Aurea Media` (59) |
| /agence-woocommerce-paris/ | `Agence WooCommerce Paris | Boutique sans commission – Aurea` (59) |
| /site-internet-boulogne-billancourt/ | `Création site internet Boulogne-Billancourt | Aurea Media` (57) |
| /creation-site-internet-clamart/ | `Création site internet Clamart | Sur mesure – Aurea Media` (57) |
| /site-vitrine-avocat/ | `Site internet avocat | Déontologie respectée – Aurea Media` (58) |
| /site-vitrine-medecin/ | `Site internet médecin | Conforme au CNOM – Aurea Media` (54) |
| /site-vitrine-kinesitherapeute/ | `Site internet kinésithérapeute | Règles CNOMK – Aurea Media` (59) |
| /site-internet-psychologue/ | `Site internet psychologue | Patientèle locale – Aurea Media` (59) |
| /site-vitrine-pour-artisan/ | `Site internet artisan | Plus de devis locaux – Aurea Media` (58) |
| /site-vitrine-artisan-renovateur-en-batiment/ | `Création site internet BTP | Devis qualifiés – Aurea Media` (58) |
| /site-internet-association/ | `Site internet association | Dons et adhésions – Aurea Media` (59) |
| /site-vitrine-coach-consultant/ | `Site internet coach | Plus d'appels découverte – Aurea Media` (60) |
| /site-vitrine-architecte/ | `Site internet architecte | Portfolio pro – Aurea Media` (54) |
| /site-vitrine-restaurant/ | `Site internet restaurant | Réservez en direct – Aurea Media` (59) |
| /prix-site-vitrine-paris/ | `Prix site vitrine Paris | Budget réel en 2026 – Aurea Media` (59) |
| /creation-site-vitrine-tpe-paris/ | `Budget site internet TPE | Repères 2026 – Aurea Media` (53) |
| /choisir-agence-web-paris/ | `Choisir une agence web à Paris | Critères clés – Aurea Media` (60) |
| /refonte-site-internet/ | `Refonte site internet | Le guide complet – Aurea Media` (54) |
| /logo-charte-graphique-identite-visuelle/ | `Logo, charte graphique, identité visuelle | Aurea Media` (55) |

| Page | Meta description (caractères) |
|---|---|
| Accueil | Agence web à Paris : sites vitrines et e-commerce sur mesure pour TPE, PME et indépendants. SEO intégré, livraison en 4 semaines, 4,9/5 sur Google. Devis 24h. (158) |
| /creation-site-internet-paris/ | Création de site internet à Paris : vitrine, e-commerce et refonte sur mesure, SEO intégré, livraison en 4 semaines. 4,9/5 sur 39 avis. Devis sous 24h. (151) |
| /prix-site-internet-paris/ | Prix d'un site internet à Paris : site vitrine dès 1 000 € HT, e-commerce dès 1 650 € HT. Tarifs affichés, site 100 % à vous. Devis gratuit sous 24h. (149) |
| /seo-local-paris/ | SEO local à Paris : fiche Google optimisée, avis clients et pages locales pour sortir dans Google Maps. Suivi mensuel lisible, sans engagement. Audit offert. (157) |
| /refonte-site-internet-paris/ | Refonte de site internet à Paris : nouveau design, WordPress rapide et plan de redirections 301 pour garder votre trafic Google. Devis détaillé sous 24h. (153) |
| /maintenance-wordpress-paris/ | Maintenance WordPress à Paris : mises à jour, sauvegardes, sécurité et support assurés par un développeur joignable. Forfait mensuel clair. Devis sous 24h. (155) |
| /site-internet-psychologue/ | Site internet pour psychologue : un cabinet rassurant, visible dans votre quartier et conforme au code de déontologie. Dès 1 000 € HT. Devis sous 24h. (150) |
| /site-internet-association/ | Site internet pour association : présenter vos actions, collecter dons et adhésions, annoncer vos événements. WordPress autonome dès 1 000 € HT. Devis 24h. (155) |

« Audit offert » et « sans engagement » sur la page SEO local sont à confirmer avec l'offre réelle. Viser un score Rank Math de 80 à 90, pas 100.

## 12. Checklist de validation

| Contrôle | Statut |
|---|---|
| Aucun mot-clé principal partagé par deux pages | OK après fusions et retitrages (section 4.7) |
| Chaque satellite lie son pilier | OK dans la matrice |
| Chaque pilier lie tous ses satellites | OK dans la matrice |
| Au moins 3 liens entrants par satellite | OK (section 7.3) |
| Aucune page orpheline | OK : hubs Secteurs, Tarifs, Conseils et Hauts-de-Seine |
| Gabarit adapté à l'intention | OK : service pour le transactionnel, comparatif pour « X ou Y », guide pour l'informationnel, page métier hybride |
| Longueurs | Écart assumé : piliers services de 1 500 à 2 500 mots (pages de conversion) ; satellites existants plus longs que 1 800 mots, conservés |
| Taille des clusters (2 à 5 clusters de 2 à 4 satellites) | Écart assumé : 9 clusters services ; hub Métiers de 12 pages (hub de navigation) ; cluster Présentations à 1 satellite |
| Recoupement SERP d'au moins 4 entre pages d'un même cluster | Non atteint pour la plupart des paires (outil limité, résultats sous-estimés) : clusters fondés sur l'intention et le service vendu. À revalider sur Google FR (non vérifié) |

## 13. Sources

- Relevés SERP bruts (76 requêtes) : `scratchpad/serp/serp.json` et `scratchpad/serp/serp1.txt`.
- Crawl du site : `scratchpad/crawl/summary.json` et `scratchpad/crawl/txt/`.
- Concurrents cités (extraits de résultats) : [Adveris](https://www.adveris.fr/page-art/creation-site-internet-paris/), [Sortlist Paris](https://www.sortlist.fr/site-internet/paris-fr), [La Fabrique du Net](https://www.lafabriquedunet.fr/agences/pages/agences-web-ile-de-france-paris), [Noiise](https://www.noiise.com/agences/paris/creation-site-internet/), [6LAB](https://www.agence-web-paris.com/), [Beyonds](https://www.beyonds.fr/), [WeDezign](https://wedezign.fr/), [Palmsquare](https://palmsquare.fr/creation-site-vitrine-paris/), [Feel and Clic](https://www.feelandclic.com/agence-creation-site-vitrine), [Pixel Paris](https://pixelparis.com/services/creation-site-vitrine-paris/), [Kapuccino](https://www.kapuccino.fr/creation-site-web-paris), [Beecomy](https://www.beecomy.com/ville/paris/), [Transacts](https://www.transacts.fr/service/creation-site-internet-clamart-92140), [Simplébo](https://www.simplebo.fr/creer-site-internet-plombier-plomberie), [WebGazelle](https://www.webgazelle.net/agence-de-paris.php), [Epixelic](https://www.epixelic.com/creation-site-internet-clamart), [Churchill](https://www.agence-churchill.fr/refonte/), [Actif Digital](https://www.actifdigital.fr/agence-refonte-site-internet/), [SEO.fr](https://www.seo.fr/agence-seo-local), [Eskimoz](https://www.eskimoz.co.uk/seo-agency/), [Komunike](https://komunike.fr/agence-seo-local-paris/), [SEO Supernova](https://seosupernova.fr/nos-agences/ile-de-france/paris/paris), [Yes We Blog](https://yesweblog.fr/maintenance-wordpress/maintenance-wordpress-paris/), [Graindesite](https://graindesite.com/maintenance-wordpress/), [Farouk Nasri](https://www.farouknasri.com/creation-logo-paris/), [Graphéine](https://grapheine.com/en/), [Digital Avocat](https://www.digital-avocat.fr/), [Psy-Site](https://psy-site.fr/).

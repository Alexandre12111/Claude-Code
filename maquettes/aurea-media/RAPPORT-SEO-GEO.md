# aurea-media.fr : audit SEO et GEO, stratégie de contenu et ciblage

Audit réalisé le 25 septembre 2026 sur le site en ligne (50 adresses explorées : sitemap, catégories, pages légales, test de 404), avec les sept audits spécialisés du skill SEO : technique, données structurées, contenu et E-E-A-T, recherche IA (GEO), SEO local, expérience de recherche (SXO), stratégie de mots-clés. Les rapports détaillés sont dans [`audit/`](audit/). Ce document en fait la synthèse, arbitre leurs divergences et relie chaque recommandation à ce que la maquette de refonte applique déjà.

**Limites** : pas d'accès à la Search Console, à GA4 ni à la fiche Google Business Profile ; API PageSpeed indisponible ce jour-là ; certaines sources externes (annuaires, recherche-entreprises.api.gouv.fr) bloquées depuis l'environnement d'audit ; SERP lues avec un moteur non géolocalisé en France, et volumes de recherche non disponibles. Tout ce qui en dépend est signalé « à vérifier ».

## 1. En résumé

- **Le site a de bonnes bases** : 33 articles utiles, des prix publics, un vrai fondateur identifiable, 4,9/5 sur 39 avis, des pages services bien rédigées, aucune page orpheline, un contenu entièrement lisible sans JavaScript.
- **Mais il se contredit** : prix d'entrée à 1 000 € ou 1 200 € HT selon la page, e-commerce à 1 650, 2 000 ou 2 500 €, 39 ou 40 avis, fourchettes à l'envers (« de 80 à 45 € »), devis type faux. La cause probable est un rechercher/remplacer global de « 150 » en « 45 ». Pour un client prudent comme pour une IA, une seule contradiction fait tomber la promesse « un prix annoncé, aucune surprise ».
- **Le balisage prend des risques** : note d'avis auto déclarée avec des valeurs différentes (4,9/39 puis 5/40), téléphone factice « +33-1-XX-XX-XX-XX » publié, deux JSON-LD invalides, HowTo (abandonné par Google) sur une douzaine d'articles.
- **Les preuves manquent** : aucune étude de cas chiffrée, un « +300 % de trafic » sans source, un discours « sans template » alors que le site tourne sur Elementor et un kit acheté. La confiance est la dimension la plus faible pour les quatre profils de clients analysés.
- **L'architecture se cannibalise** : l'accueil, la page agence et un article visent tous « agence web Paris » ; plusieurs articles se disputent Google My Business, le logo ou le prix d'un site vitrine ; les pages locales sont publiées comme articles.
- **Il manque trois pages qui vendent** : une page tarifs (source unique des prix), une page refonte et une page SEO local, alors que ces offres existent et que les articles y renvoient déjà.
- **La refonte corrige tout ce qui dépend du site** (partie 4) : architecture, balises, schéma, source unique des faits, performance, accessibilité. Le reste dépend d'Aurea Media : vrais chiffres, études de cas, avis, fiche Google, annuaires (parties 9 à 11).

## 2. Scores

| Domaine | Score actuel | Ce qui pèse le plus |
|---|---|---|
| Technique | 59/100 | Fausses 404 (301 vers l'accueil), en-têtes de sécurité perdus sur les pages en cache, archives de catégories indexables qui doublonnent les services |
| Contenu | 52/100 (E-E-A-T 41) | Chiffres contradictoires ou corrompus, affirmations non sourcées, pas de page auteur, cannibalisation |
| Données structurées | 60/100 | Note d'avis auto déclarée et incohérente, téléphone factice, JSON-LD invalides, HowTo, Organization déclarée environ 70 fois avec des variantes |
| Recherche IA (GEO) | 54/100 | Faits contradictoires, peu de H2 en questions, llms.txt peu utile, peu de mentions externes |
| SEO local | 43/100 | Adresse de domiciliation, note fabriquée dans le balisage, pages villes quasi identiques, flux d'avis arrêté depuis septembre 2025 sur le site |
| Performance (estimée sur le code) | environ 40/100 | 21 feuilles de style et 18 scripts, 5 familles de polices, aucune image WebP ou AVIF, captures PNG jusqu'à 2 Mo |

**Score global estimé : 55/100** (pondération du skill SEO : technique 22 %, contenu 23 %, on-page 20 %, schéma 10 %, performance 10 %, IA 10 %, images 5 %). Les scores de performance et d'images sont des estimations faute de données de terrain.

**La maquette de refonte**, mesurée avec Lighthouse mobile : performance de 96 à 100, accessibilité, bonnes pratiques et SEO à 100 sur toutes les pages testées, LCP de 1,5 à 2,5 s, CLS nul ou quasi nul, 0 violation d'accessibilité (axe, WCAG 2.1 AA).

## 3. Les constats critiques

Chaque point : constat, pourquoi c'est grave, action, et comment vérifier.

### C1. Chiffres corrompus et prix contradictoires

- **Constat** : site vitrine à 1 000 € HT sur la page service et 1 200 € HT dans une douzaine d'articles ; e-commerce à 1 650, 2 000 ou 2 500 € ; 39 ou 40 avis ; fourchettes inversées (« entre 60 et 45 € par an », « 80 à 45 €/mois », « entre 49 et 45 € HT/mois ») ; devis type annoncé à 1 500 € HT dont les postes totalisent 1 395 € (le poste « Brief » affiche 45 € au lieu de 150 €).
- **Pourquoi** : une profession libérale ou un dirigeant de PME recoupe les chiffres ; une IA qui trouve trois prix différents n'en cite aucun.
- **Action** : rechercher dans la base « 45 € », « et 45 », « à 45 », « 1 200 € », « 40 avis » et corriger chaque occurrence ; dans le nouveau thème, une source unique des faits (déjà en place dans la maquette : `src/data.py`) lue par toutes les pages, le schéma et le llms.txt ; une page tarifs qui fait référence.
- **Vérifier** : une recherche plein texte sur l'export des contenus ne remonte plus aucune fourchette dont la borne haute est inférieure à la borne basse ; les prix sont identiques sur toutes les pages.

### C2. Fausses pages 404

- **Constat** : toute adresse inexistante est redirigée en 301 vers l'accueil par Rank Math.
- **Pourquoi** : Google traite ces redirections comme des « soft 404 », les liens cassés sont attribués à l'accueil, et les robots IA qui testent `/llms-full.txt` ou `/ai.txt` reçoivent 245 Ko de HTML.
- **Action** : désactiver la redirection automatique des 404 dans Rank Math ; publier une vraie page 404 utile (celle de la maquette).
- **Vérifier** : `curl -I https://aurea-media.fr/nimporte-quoi/` renvoie 404 ; ces adresses apparaissent en « Introuvable (404) » dans la Search Console.

### C3. Données structurées à risque

- **Constat** : `AggregateRating` sur sa propre entreprise, avec trois valeurs différentes (4,9/39 sur l'accueil, 5/40 sur deux pages locales) ; téléphone factice « +33-1-XX-XX-XX-XX » sur `/choisir-agence-web-paris/` ; deux blocs JSON-LD invalides (`/anatomie-site-vitrine-qui-convertit/`, `/refonte-identite-visuelle-entreprise/`) ; HowTo sur une douzaine d'articles ; entreprise déclarée environ 70 fois avec des coordonnées GPS et des prix différents.
- **Pourquoi** : Google n'affiche pas d'étoiles pour les avis qu'une entreprise publie sur elle-même, et une note inventée est contraire à ses consignes ; les incohérences brouillent l'entité pour Google comme pour les IA.
- **Action** : un seul graphe par page avec des `@id` stables, généré par le thème et complété par Rank Math (modèle en place dans la maquette) ; suppression de toutes les notes d'avis, des HowTo et des blocs en double.
- **Vérifier** : Test des résultats enrichis et validateur Schema.org sans erreur sur un échantillon de 10 pages ; une seule entité `ProfessionalService` avec le même `@id` partout.

### C4. En-têtes de sécurité perdus sur les pages en cache

- **Constat** : HSTS, X-Frame-Options et les autres en-têtes sont présents sur les pages non cachées mais disparaissent sur l'accueil et les pages services servies depuis le cache ; aucune Content-Security-Policy ; version de PHP exposée.
- **Action** : définir les en-têtes au niveau du serveur (et non de WordPress) pour qu'ils s'appliquent aussi aux pages en cache ; masquer `X-Powered-By`.
- **Vérifier** : `curl -I` sur l'accueil et sur une page d'article affiche les mêmes en-têtes.

### C5. Cannibalisation

| Intention | Pages en concurrence | Décision |
|---|---|---|
| « agence web Paris » | accueil, `/agence-web-a-paris/`, `/choisir-agence-web-paris/` | L'accueil seul ; la page agence devient `/a-propos/` (301) sur « développeur WordPress Paris » ; l'article est retitré « Choisir une agence web à Paris : 7 critères » |
| « création site internet Paris » | `/nos-services/`, accueil | Nouvelle page `/creation-site-internet-paris/` (301 depuis `/nos-services/`) |
| Fiche Google | `/optimiser-sa-fiche-google-my-business/`, `/google-my-business-artisans-tpe/` | Fusion (301) ; la version « coach » est recentrée sur la fiche sans adresse publique |
| Logo et charte | deux articles | Fusion (301) |
| Prix d'un site vitrine | `/prix-site-vitrine-paris/`, `/creation-site-vitrine-tpe-paris/`, page service | Fusion de l'article TPE dans l'article prix (301), la page service garde une grille courte |
| Pages locales du 92 | six contenus Boulogne, Clamart, Hauts-de-Seine | Trois pages réelles : le hub Hauts-de-Seine, Boulogne-Billancourt, Clamart ; converties d'article en page sans changer d'adresse |
| Catégories du blog | `/creation-site-vitrine/`, `/creation-site-ecommerce/`… | Déplacées sous `/conseils/…` en noindex, follow |

Avant chaque 301 : comparer dans la Search Console (16 mois) les clics, impressions et liens des URL du groupe ; la cible doit être celle qui performe le mieux. La table complète est en partie 6.

### C6. Preuves insuffisantes (E-E-A-T)

- **Constat** : 21 réalisations sans aucun résultat mesuré et avec uniquement des liens sortants ; « +300 % de trafic » sans source ; « 45+ projets » alors que 21 sont montrés ; aucune page auteur et trois biographies différentes ; « aucun template » alors que le site actuel utilise Elementor et Royal Elementor Kit ; Novexia situé en Île-de-France dans un article et en Occitanie sur la page Réalisations ; mentions légales sans la mention d'entreprise individuelle ni les numéros RNE et TVA (à vérifier).
- **Action** : retirer ou sourcer chaque chiffre ; publier 3 à 5 études de cas chiffrées au lancement (gabarit Novexia de la maquette) ; une page À propos qui sert de page auteur ; une biographie unique ; la refonte elle-même sans constructeur de pages rend enfin la promesse vraie.
- **Vérifier** : chaque chiffre public renvoie à une source ou à une étude de cas ; chaque page service lie au moins deux études de cas.

### C7. Parcours de conversion incomplet

- **Constat** : un seul type de contact (le devis), pas de rendez-vous, aucun formulaire sur les pages services, pas de page tarifs, pas de page refonte ni SEO local, formulaire sans option refonte ou SEO ni champ pour l'adresse du site actuel.
- **Action** (en place dans la maquette) : formulaire intégré à chaque page service avec le besoin présélectionné, réservation d'un appel de 20 minutes, simulateur de budget qui préremplit la demande, pages refonte, SEO local et tarifs, champ adresse du site pour la refonte et le SEO.

### C8. Performance et images

- **Constat** : 21 feuilles de style bloquantes et 18 scripts, cinq familles de polices chargées par trois mécanismes (dont 18 graisses de Fraunces et de DM Sans), aucune image moderne ni `srcset`, captures de réalisations en PNG de 0,5 à 2,1 Mo, `loading="lazy"` en double sur 52 images, noms et textes alternatifs du type « ChatGPT Image 12 avr. 2025 ».
- **Action** (en place dans la maquette) : un seul fichier CSS et quatre scripts différés, trois polices auto hébergées, captures en AVIF et WebP à deux tailles (12 à 35 Ko au lieu de 1 à 2 Mo), dimensions sur toutes les images, textes alternatifs descriptifs.

### C9. Adresse de domiciliation et fiche Google

- **Constat** : le 60 rue François Ier est très probablement une adresse de domiciliation (à confirmer) ; le site dit lui-même « rendez-vous sur demande ».
- **Pourquoi** : Google n'autorise l'affichage public d'une adresse que si des clients y sont reçus aux horaires indiqués. Une adresse de domiciliation affichée expose la fiche à une suspension.
- **Action** : configurer la fiche en établissement de services avec zone desservie (adresse masquée, Paris et Hauts-de-Seine) ; sur le site, « Paris 8e, rendez-vous sur demande » sur les pages commerciales et l'adresse complète dans les mentions légales et le schéma (appliqué dans la maquette) ; aucune carte ni photo de façade à cette adresse.

### C10. Défauts visibles

- 13 apostrophes affichées « ‡ » sur la page Réalisations (« l‡isolation »).
- Commentaires internes laissés dans le code source (« ⚠ Dans Rank Math, ne PAS ajouter de schéma FAQ… »).
- Catégorie nommée « SEO (Référecement) ».
- Environ 90 liens internes vers 15 adresses qui n'existent pas (toutes redirigées vers l'accueil), dont 21 vers `/optimiser-fiche-google-my-business/`.

## 4. Ce que la maquette de refonte corrige déjà

| Problème | Solution dans la maquette |
|---|---|
| Prix et chiffres contradictoires | Source unique `src/data.py`, lue par les pages, le schéma, le llms.txt ; page tarifs avec simulateur |
| Cannibalisation | Une intention par URL, nouvelles pages hub, À propos, tarifs, refonte, SEO local ; table de 301 générée |
| Balises hors règles Rank Math | Titles de 53 à 59 caractères, descriptions de 149 à 160, un H1 avec le mot-clé, mot-clé dans l'introduction, contrôlés à chaque génération (`SEO-PAGES.md`) |
| Schéma incohérent | Un graphe par page, `@id` stables, Service avec offres et prix HT, BlogPosting avec auteur, BreadcrumbList, aucune note auto déclarée, aucun HowTo |
| Fausses 404 | Vraie page 404 utile, en noindex |
| Entité floue pour les IA | Bloc « Aurea Media en bref » identique sur l'accueil, À propos, pied de page, schéma et llms.txt ; H2 en questions et réponses autonomes dans l'article modèle |
| Preuves | Gabarit d'étude de cas, portrait et page À propos, avis datés, lien vers la fiche Google, liens vers les 20 sites en ligne |
| Conversion | Formulaire sur chaque page service, rendez-vous, simulateur, téléphone cliquable partout |
| Performance | 170 à 260 Ko par page, Lighthouse mobile 96 à 100, CLS nul |
| Accessibilité | 0 violation axe, navigation au clavier complète, mouvement réduit respecté |
| robots.txt et llms.txt | Un seul groupe, robots IA autorisés ; llms.txt réécrit pour la nouvelle architecture |

## 5. Architecture cible et carte des mots-clés

Un mot-clé principal par page, jamais partagé. Les titles suivent le format du client « Mot-clé | Proposition – Marque ». Le détail avec les longueurs est dans [`SEO-PAGES.md`](SEO-PAGES.md).

| Page | Mot-clé principal | Mots-clés secondaires | SEO Title |
|---|---|---|---|
| `/` | agence web Paris | agence web TPE PME Paris, agence web indépendante Paris | Agence web Paris \| Sites qui convertissent – Aurea Media |
| `/creation-site-internet-paris/` | création site internet Paris | agence création site internet Paris, création site WordPress Paris | Création site internet Paris \| Dès 1 000 € HT – Aurea Media |
| `/creation-de-site-vitrine-paris/` | création site vitrine Paris | création site vitrine, site vitrine sur mesure | Création site vitrine Paris \| Dès 1 000 € HT – Aurea Media |
| `/creation-de-site-e-commerce-paris/` | création site e-commerce Paris | boutique WooCommerce, boutique en ligne sans commission | Création site e-commerce Paris \| Dès 1 650 € – Aurea Media |
| `/refonte-site-internet-paris/` | refonte site internet Paris | agence refonte site internet, refonte site WordPress | Refonte site internet Paris \| Sans perte SEO – Aurea Media |
| `/seo-local-paris/` | SEO local Paris | référencement local Paris, référencement Google Maps | SEO local Paris \| Google Maps et fiche Google – Aurea Media |
| `/branding-identite/` | création logo Paris | identité visuelle entreprise, charte graphique | Création logo Paris \| Identité visuelle – Aurea Media |
| `/maquettes-de-presentation/` | création pitch deck | présentation PowerPoint professionnelle, proposition commerciale | Création pitch deck Paris \| Slides sur mesure – Aurea Media |
| `/prix-site-internet-paris/` | prix site internet Paris | tarif création site internet, devis site internet | Prix site internet Paris \| Tarifs affichés – Aurea Media |
| `/realisations-agence-web/` | exemples de sites vitrines | réalisations agence web Paris, portfolio | Exemples de sites vitrines \| Projets livrés – Aurea Media |
| `/a-propos/` | développeur WordPress Paris | Alexandre Coury, agence web indépendante Paris | Développeur WordPress Paris \| Alexandre Coury – Aurea Media |
| `/contact-agence/` | devis site internet Paris | contact agence web Paris | Devis site internet Paris \| Réponse sous 24h – Aurea Media |
| `/nos-conseils-web/` | conseils site internet | guide création site internet | Conseils site internet \| Guides TPE et PME – Aurea Media |
| `/agence-web-hauts-de-seine/` | agence web Hauts-de-Seine | agence web Boulogne-Billancourt, agence web 92 | Agence web Hauts-de-Seine \| Boulogne, Clamart – Aurea Media |
| `/prix-site-vitrine-paris/` | prix site vitrine Paris | combien coûte un site vitrine | Prix site vitrine Paris \| Budget réel en 2026 – Aurea Media |

**Pourquoi « SEO local Paris » et pas « agence SEO Paris »** : la SERP d'« agence SEO Paris » est tenue par des agences spécialisées (Eskimoz, SEO.fr, Pixalione…) ; le SEO local packagé pour TPE, lié au site et aux trois articles déjà publiés sur la fiche Google et les avis, est accessible et correspond à l'offre réelle.

**Pages à créer ensuite** : `/secteurs/` (hub des pages métiers : les 8 guides métiers deviennent des pages sans changer d'adresse), `/maintenance-wordpress-paris/` (seulement une fois des forfaits publics fixés), 3 à 5 études de cas, une page `/avis-clients/` si le volume d'avis le justifie.

## 6. Redirections 301 au lancement

Fichier prêt : [`redirections-301.htaccess`](redirections-301.htaccess) (ou import dans Rank Math, Redirections). Après la bascule, mettre à jour tous les liens internes vers la cible, sans laisser de lien vers une adresse redirigée.

| Ancienne adresse | Nouvelle adresse |
|---|---|
| `/agence-web-a-paris/` | `/a-propos/` |
| `/nos-services/` | `/creation-site-internet-paris/` |
| `/branding-identite-visuelle/` | `/branding-identite/` |
| `/logo-et-charte-graphique/` | `/logo-charte-graphique-identite-visuelle/` |
| `/google-my-business-artisans-tpe/` | `/optimiser-sa-fiche-google-my-business/` |
| `/creation-site-vitrine-tpe-paris/` | `/prix-site-vitrine-paris/` |
| `/agence-seo-clamart/` | `/creation-site-internet-clamart/` |
| `/creation-de-site-e-commerce-boulogne/` | `/site-internet-boulogne-billancourt/` |
| `/agence-web-collectivites-boulogne-billancourt/` | `/agence-web-hauts-de-seine/` |
| `/creation-site-web/` | `/nos-conseils-web/` |
| `/creation-site-vitrine/` | `/conseils/site-vitrine/` |
| `/creation-site-ecommerce/` | `/conseils/site-e-commerce/` |
| `/google-my-business-seo-local/` | `/conseils/seo-local/` |

## 7. Stratégie de contenu

### 7.1 Clusters (hub and spoke)

| Cluster | Page pilier | Satellites existants | À créer (6 mois) |
|---|---|---|---|
| Création de site | `/creation-site-internet-paris/` | choisir une agence, soi-même ou agence, WordPress ou Webflow | cahier des charges, délai de création, site bilingue |
| Site vitrine | `/creation-de-site-vitrine-paris/` | prix site vitrine, anatomie d'un site qui convertit, site qui ne génère pas de clients | (piloté par les pages métiers) |
| E-commerce | `/creation-de-site-e-commerce-paris/` | combien coûte un e-commerce, Shopify ou WooCommerce, créer un e-commerce | agence WooCommerce Paris |
| Refonte | `/refonte-site-internet-paris/` | refonte de site internet (guide) | prix d'une refonte, refonte sans perdre son SEO, étude de cas de la refonte d'aurea-media.fr |
| SEO local | `/seo-local-paris/` | fiche Google, avis Google, fiche Google pour coach | prix du SEO local, référencement local Google |
| Identité | `/branding-identite/` | logo, charte, identité ; refonte d'identité | prix d'un logo |
| Métiers | `/secteurs/` | avocat, médecin, kiné, architecte, restaurant, coach, artisan, rénovation | psychologue, association, expert-comptable |
| Local | `/agence-web-hauts-de-seine/` | Boulogne-Billancourt, Clamart | une ville du 92 avec un vrai client, Genève |

Règle de maillage : chaque satellite lie sa page pilier dans l'introduction et en conclusion ; chaque pilier lie ses satellites dans une section « Guides » ; chaque page reçoit au moins trois liens internes contextuels. Matrice complète des ancres dans [`audit/strategie.md`](audit/strategie.md), partie 7.

### 7.2 Calendrier éditorial

Hypothèse : mois 1 = mois de la mise en ligne de la refonte.

| Mois | Contenu | Adresse | Mot-clé | Pilier |
|---|---|---|---|---|
| 1 | Prix d'une refonte de site internet : ce qui fait varier le devis | `/prix-refonte-site-internet/` | prix refonte site internet | Refonte |
| 1 | Refonte de site internet : garder son référencement Google | `/refonte-site-seo/` | refonte site sans perdre référencement | Refonte |
| 1 | Site internet pour psychologue | `/site-internet-psychologue/` | création site internet psychologue | Métiers |
| 2 | Prix du SEO local pour une TPE | `/prix-seo-local/` | prix référencement local TPE | SEO local |
| 2 | Site internet pour association | `/site-internet-association/` | création site internet association | Métiers |
| 2 | Cahier des charges de site internet : modèle et exemple | `/cahier-des-charges-site-internet/` | cahier des charges site internet | Création de site |
| 3 | Maintenance WordPress à Paris (si forfaits définis) | `/maintenance-wordpress-paris/` | maintenance WordPress Paris | Maintenance |
| 3 | Prix de la maintenance d'un site | `/prix-maintenance-site-internet/` | tarif maintenance site internet | Maintenance |
| 3 | Prix d'un logo | `/prix-creation-logo/` | prix création logo | Identité |
| 4 | Étude de cas : la refonte d'aurea-media.fr | `/cas-refonte-aurea-media/` | (preuve, sans mot-clé concurrentiel) | Refonte |
| 4 | Agence WooCommerce à Paris | `/agence-woocommerce-paris/` | agence WooCommerce Paris | E-commerce |
| 4 | Référencement local Google : le guide | `/referencement-local-google/` | référencement local Google | SEO local |
| 5 | Site internet bilingue français anglais | `/site-internet-bilingue/` | site internet bilingue | Création de site |
| 5 | Combien de temps pour créer un site internet ? | `/delai-creation-site-internet/` | délai création site internet | Création de site |
| 5 | Page ville du 92 (avec un client réel) | `/creation-site-internet-[ville]/` | création site internet [ville] | Local |
| 6 | Création de site internet à Genève | `/creation-site-internet-geneve/` | création site internet Genève | Local |
| 6 | Pitch deck : la structure qui décroche un rendez-vous | `/structure-pitch-deck/` | structure pitch deck | Présentations |
| 6 | Site internet pour expert-comptable | `/site-internet-expert-comptable/` | site internet expert-comptable | Métiers |

Chaque contenu : un mot-clé principal, 1 200 à 1 800 mots pour un satellite, l'auteur avec sa biographie, la date de mise à jour visible, au moins une donnée ou un exemple tiré d'un projet réel, schéma BlogPosting ou Service avec fil d'Ariane. Score Rank Math visé : 80 à 90.

### 7.3 Gabarit d'article optimisé pour Google et les IA

Appliqué dans l'article modèle de la maquette (`prix-site-vitrine-paris.html`) :

1. H1 formulé comme la question du lecteur, avec le mot-clé.
2. Auteur, date de publication et date de mise à jour visibles.
3. Chapeau de 40 à 60 mots qui répond directement (sujet nommé, chiffre, lieu, année, nuance).
4. Encadré « En bref » de 3 à 5 points chiffrés.
5. H2 formulés en questions, chacun ouvert par un bloc réponse autonome de 40 à 80 mots qui nomme le sujet en toutes lettres.
6. Vrais tableaux HTML avec légende pour toute comparaison de prix ou d'options.
7. Une section « Ce que nous observons sur nos projets » (donnée propriétaire).
8. Questions fréquentes en texte visible (sans FAQPage), sources datées, encadré auteur, appel à l'action vers le service.

### 7.4 Personas prioritaires

| Priorité | Profil | Déclencheur | Pages d'entrée | Objection à lever | Preuve disponible |
|---|---|---|---|---|---|
| 1 | Dirigeant de PME B2B (industrie, holding, négoce, salon) | Site daté, besoin bilingue | Refonte, création site internet, réalisations | Peur de perdre le référencement ; continuité d'un indépendant | Groupe Verisol, TCM Grand Est, Havel Trading, Dual Tech |
| 1 | Profession libérale réglementée (avocat, santé, architecte) | Installation, développement local | Pages métiers, SEO local, tarifs | Déontologie, manque de temps | Maître Paoli, Xpert Medical |
| 1 | Entreprise du bâtiment | Dépendance aux plateformes de devis | Page rénovation, site vitrine, SEO local | « Le bouche-à-oreille suffit » | Novexia Construction |
| 2 | Commerçant ou marque en ligne | Frais Shopify, envie de propriété | E-commerce, Shopify ou WooCommerce | Gestion du catalogue | Sunrock |
| 3 | Coach, consultant, indépendant | Lancement, crédibilité | Site vitrine, présentations, logo | Prix face aux outils à faire soi-même | Enzym, Sultan Conseil |
| 3 | Entreprise francophone hors de France | Marché local cher | Site bilingue, Genève | Travail à distance | Portugal Expert Properties, Havel Trading |

### 7.5 Concurrents et angles de différenciation

Rencontrés dans les SERP : grandes agences parisiennes (Adveris, Beyonds, 6LAB, Noiise), agences TPE et PME (Palmsquare, Feel and Clic, Pixel Paris), offres par abonnement ou à bas prix (Simplébo, Linkeo, EvolveTech), spécialistes par métier (WebGazelle, Digital Avocat, Psy-Site), spécialistes de la refonte (Churchill) et du SEO (Eskimoz, SEO.fr).

Les angles à porter sur chaque page :

1. **Des prix publics et justes** (1 000 € HT le site vitrine, 1 650 € HT la boutique) face à des agences à 4 000 € et plus ou « sur devis » ; ne jamais se placer sur « pas cher », terrain pris par des offres à 399 €.
2. **Propriété totale, sans abonnement ni commission**, face aux formules par abonnement et à Shopify.
3. **Un seul interlocuteur** qui fait la stratégie, le design, le code et le SEO, joignable après la livraison.
4. **Un délai annoncé et tenu** : 4 semaines pour un site vitrine.
5. **La preuve** : avis Google, réalisations par secteur, études de cas chiffrées.
6. **Les métiers réglementés** : déontologie intégrée dès la conception.
7. **La refonte sans perte de trafic, démontrée sur son propre site.**

## 8. Plan GEO (visibilité dans les moteurs de réponse IA)

- **Entité unique** : le bloc « Aurea Media en bref » (en place dans la maquette) partout à l'identique, y compris sur la fiche Google, LinkedIn et les annuaires.
- **Accès** : robots.txt en un seul groupe, tous les robots IA autorisés (pour une marque jeune, être connue des modèles est un atout) ; vraies 404 ; llms.txt statique réécrit (fourni), module llms.txt de Rank Math désactivé pour éviter sa régénération.
- **Citabilité** : H2 en questions et blocs réponse autonomes sur les 12 articles les plus stratégiques, chiffres datés et sourcés, tableaux HTML, dates de mise à jour visibles et `dateModified` fiable.
- **Données propriétaires** : un baromètre des prix d'un site vitrine à Paris (prix médian, délais réels mesurés sur les projets, méthode publiée) : c'est le type de contenu que les IA et la presse citent.
- **Requêtes à suivre chaque mois** dans ChatGPT, Perplexity, Google AI Mode et Copilot (cité oui ou non, adresse citée, concurrents cités) :

| Requête | Page qui doit répondre |
|---|---|
| Quelle agence web choisir à Paris pour un site vitrine à moins de 2 000 euros ? | `/creation-de-site-vitrine-paris/` |
| Combien coûte un site vitrine pour une petite entreprise à Paris ? | `/prix-site-vitrine-paris/` |
| Combien coûte une boutique WooCommerce pour une TPE ? | `/combien-coute-un-site-e-commerce/` |
| Quelle agence peut créer une boutique en ligne sans commission ? | `/creation-de-site-e-commerce-paris/` |
| Freelance ou agence web pour créer son site ? | `/creer-un-site-vitrine-soi-meme-ou-en-agence/` |
| Quels critères pour choisir une agence web à Paris ? | `/choisir-agence-web-paris/` |
| Shopify ou WooCommerce, lequel coûte le moins cher sur 3 ans ? | `/shopify-vs-woocommerce/` |
| Combien de temps pour créer un site vitrine professionnel ? | `/creation-site-internet-paris/` (méthode) |
| Quelle agence web pour une TPE à Boulogne-Billancourt ? | `/site-internet-boulogne-billancourt/` |
| Comment refaire son site sans perdre son référencement ? | `/refonte-site-internet/` et `/refonte-site-internet-paris/` |
| Quelle agence web pour un cabinet d'avocat à Paris ? | `/site-vitrine-avocat/` |
| Combien coûte un logo avec une charte graphique ? | `/branding-identite/` |
| Qui peut créer un pitch deck professionnel en une semaine ? | `/maquettes-de-presentation/` |
| Aurea Media est-elle une agence sérieuse ? Quels avis ? | `/a-propos/` et la fiche Google |
| Qui est Alexandre Coury ? | `/a-propos/` |

- **Mentions externes, sur 8 semaines** : semaines 1 et 2, fiche Google, Bing Places et Bing Webmaster Tools (avec IndexNow), Apple Business Connect, une seule page LinkedIn ; semaines 3 à 5, Malt, Sortlist, La Fabrique du Net, crédit « Site réalisé par Aurea Media » sur les sites clients (avec leur accord) ; semaines 6 à 8, Clutch, quelques vidéos YouTube (prix d'un site, visite d'une étude de cas), baromètre proposé à la presse TPE PME et locale. Wikidata seulement après deux ou trois sources indépendantes ; pas de page Wikipédia.
- **Mesure** : visites référentes de chatgpt.com, perplexity.ai, copilot.microsoft.com et gemini.google.com dans GA4.

## 9. Plan SEO local

- **Fiche Google Business Profile** : établissement de services avec zone desservie (Paris, Hauts-de-Seine, éventuellement 78 et 94), adresse masquée ; catégorie principale « Concepteur de sites Web », secondaires pertinentes ; services avec prix identiques au site ; description reprenant le bloc en bref ; une publication par mois ; photos du fondateur et des projets (jamais de façade à l'adresse de domiciliation) ; questions et réponses amorcées.
- **NAP** : nom, téléphone au format international et adresse identiques partout ; une seule adresse LinkedIn ; plus aucune note ni aucun nombre d'avis écrit en dur ailleurs que dans la source unique.
- **Avis : de 39 à 80 et plus** : demande systématique dans les 48 heures après la mise en ligne, relance à 3 mois ; relance ciblée des clients livrés sans avis ; un nouvel avis au moins toutes les deux ou trois semaines (au delà, la visibilité locale a tendance à baisser) ; question ouverte sur le projet avant l'envoi du lien, jamais de texte suggéré ; réponse à 100 % des avis sous 48 heures ; avis récents et variés sur le site.
- **Citations prioritaires** : PagesJaunes, Bing Places et Sortlist d'abord ; puis Malt, Codeur.com, Apple Plans, annuaire de la CCI ; ensuite les annuaires d'agences web, Hoodspot, 118712 ; Waze, Mappy et Yelp seulement pour corriger d'éventuelles fiches erronées.
- **Pages locales** : trois pages réelles (Hauts-de-Seine, Boulogne-Billancourt, Clamart), chacune avec une preuve locale réelle (client, projet, déplacement) ; pas de multiplication de pages villes quasi identiques.

## 10. Exigences techniques pour le thème WordPress

La maquette respecte déjà ces règles ; le thème devra les conserver.

- **Budget** : 500 Ko maximum par page (compressé), un seul CSS, JavaScript différé ; LCP ≤ 2,5 s, INP ≤ 200 ms, CLS ≤ 0,1 en mobile.
- **Polices** : 3 familles au plus, auto hébergées en woff2, `font-display: swap`, polices de repli aux métriques ajustées, 4 fichiers au plus par page.
- **Images** : AVIF ou WebP, `srcset` et `sizes`, `width` et `height` sur toutes les images, `loading="lazy"` sous la ligne de flottaison et jamais sur l'image principale, noms de fichiers et textes alternatifs descriptifs.
- **Serveur** : Brotli, cache d'un an sur les fichiers versionnés, en-têtes de sécurité définis au niveau du serveur (y compris CSP) et identiques pour les pages en cache.
- **Rank Math** : titles et descriptions de la carte ci-dessus, catégories en noindex, follow, redirections 301 de la partie 6, 404 automatiques désactivées, module llms.txt désactivé au profit du fichier statique, schéma complété par le thème sans doublon, IndexNow activé.
- **Contrôles après la bascule** : 10 adresses inexistantes renvoient 404 ; aucune ancienne adresse ne finit en 404 ou sur l'accueil ; Search Console sans erreur d'exploration nouvelle ; positions des pages principales comparées avant et après pendant 8 semaines.

## 11. À vérifier ou à fournir par Aurea Media

1. Le bon nombre de projets (45 ou autre) et la réparation des chiffres touchés par le remplacement « 150 » en « 45 ».
2. La date d'immatriculation du SIREN 992 693 473 et la formulation de l'ancienneté (« Alexandre crée des sites depuis 2021 » dans la maquette).
3. Les prix de la refonte, du SEO local, de l'identité visuelle et des présentations (des articles citent 900 € HT pour une refonte et 800 € HT pour un logo).
4. Les résultats chiffrés de 3 à 5 projets (Search Console et formulaires, avec l'accord des clients) et une citation de chaque client.
5. La localisation de Novexia (Occitanie ou Île-de-France).
6. Les réglages actuels de la fiche Google (adresse affichée ou masquée, catégories, zone), le nombre d'avis des trois dernières semaines et le taux de réponse.
7. L'adresse LinkedIn officielle de l'entreprise.
8. Les mentions légales : mention d'entreprise individuelle, numéros RNE et TVA.
9. L'existence d'une offre de maintenance à prix public (condition pour créer la page maintenance).
10. Les données de la Search Console avant toute fusion de pages.

## 12. Indicateurs de suivi

| Indicateur | Outil | Objectif à 6 mois |
|---|---|---|
| Clics et impressions sur les 15 mots-clés principaux | Search Console | Progression régulière, top 10 sur les pages services |
| Demandes de devis et appels issus du site | GA4 (événements), formulaire | En hausse par rapport aux 6 mois précédents |
| Avis Google | Fiche Google | 80 avis et plus, aucun trou de plus de 3 semaines |
| Visibilité dans le pack local | Suivi de positions local | Présence dans le top 3 sur « création site internet » dans les Hauts-de-Seine |
| Citations par les IA | Panel mensuel de 15 requêtes, GA4 | Aurea Media citée sur au moins 5 requêtes |
| Core Web Vitals | Search Console (données de terrain) | 100 % des adresses en « Bonnes » |
| Erreurs d'exploration et 404 | Search Console | Aucune nouvelle erreur après la bascule |

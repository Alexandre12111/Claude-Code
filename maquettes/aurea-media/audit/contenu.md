# Audit contenu et E-E-A-T : aurea-media.fr

Date : 25 septembre 2026. Périmètre : 50 URL crawlées (accueil, 9 pages, 33 articles, 4 catégories, pages légales). Objectif : préparer la refonte (ce qu'on garde, ce qu'on change, ce qu'on crée).

## Synthèse

| Indicateur | Score | Lecture |
|---|---|---|
| Qualité du contenu | **52 / 100** | Beaucoup de volume et une bonne couverture du sujet, mais des chiffres faux ou contradictoires, une forte cannibalisation et environ 90 liens internes cassés |
| E-E-A-T (modèle interne pondéré) | **41 / 100** | Identité réelle et joignable, portfolio en ligne, mais peu de preuves d'expérience, aucune page auteur, et des affirmations invérifiables qui fragilisent la confiance |
| Préparation aux réponses IA (citation) | **55 / 100** | Prix, FAQ et résumés bien structurés, mais des faits contradictoires d'une page à l'autre (prix, nombre d'avis, ancienneté) qui rendent le site peu fiable à citer |

Détail du score contenu : couverture thématique 16/20, qualité rédactionnelle et lisibilité 12/20, exactitude et cohérence 5/20, architecture éditoriale (cannibalisation, maillage) 7/20, optimisation on-page 12/20.

Les cinq messages à retenir :

1. **Des chiffres ont été corrompus sur le site.** Plusieurs fourchettes de prix sont inversées (« entre 60 et 45 € par an », « 80 à 45 € HT ») et deux calculs sont faux. Le motif laisse penser qu'un rechercher/remplacer global a transformé « 150 » en « 45 ». C'est la priorité numéro un : ces erreurs sont visibles par les prospects.
2. **L'ancienneté « depuis 2021 » n'est pas prouvée et semble contredite par le SIREN.** Il faut l'aligner sur le registre avant la refonte (voir 1.6).
3. **Les preuves d'expérience manquent.** Aucune étude de cas chiffrée, et la seule étude de cas publiée contient une erreur factuelle (Novexia présentée en Île-de-France alors qu'elle est en Occitanie). Le « +300 % de trafic » n'est adossé à aucune donnée.
4. **La cannibalisation est forte** sur « agence web Paris » (3 URL), Google My Business (4 URL), logo et charte (3 URL), prix du site vitrine (3 URL) et les pages locales du 92 (6 URL). Il faut 6 fusions en 301 et 5 repositionnements.
5. **Deux générations d'articles coexistent**, avec des prix, des bios auteur et des styles différents (tirets cadratins en masse, « guide complet » dans 21 titles sur 33). La refonte est le bon moment pour harmoniser.

## Méthode et limites

- Données : `crawl/txt/*.txt` et `crawl/summary.json`, plus contrôle du HTML brut. Le nombre de mots cité ici porte sur le contenu principal (menu et pied de page retirés), il est donc inférieur à celui de `summary.json`.
- Lisibilité : score Kandel et Moles (adaptation française de Flesch), calcul approximatif (syllabes estimées, listes sans ponctuation). À lire comme un ordre de grandeur.
- **Non vérifié** :
  - La date de création au registre : l'appel à `recherche-entreprises.api.gouv.fr` a été bloqué par la politique réseau de l'environnement d'audit (code 403).
  - Les données Search Console, Analytics et backlinks : non fournies, donc les décisions de fusion doivent être confirmées avec ces données avant exécution.
  - La note et le nombre d'avis sur la fiche Google.
  - Les sites clients autres que novexia-construction.fr, qui n'étaient pas joignables depuis l'environnement d'audit.
  - Le volume de recherche des mots-clés proposés.
  - Le script `metadata_template.py`, qui n'a pas été exécuté : le constat sur les métadonnées répétitives est manuel.
- **Correction du contexte partagé :** le JSON-LD n'est pas limité à l'accueil. 45 pages en contiennent. Les pages services ont `Service` et `Offer`, et les articles ont `BlogPosting` avec `Person`, `FAQPage` et, pour 12 articles, `HowTo`. Deux blocs JSON-LD sont invalides : `/anatomie-site-vitrine-qui-convertit/` et `/refonte-identite-visuelle-entreprise/`. L'extraction de `summary.json` ne reconnaissait pas la syntaxe `"@type": "..."` avec une espace. À transmettre à l'audit schéma.

## 1. E-E-A-T

*Pondération propre à cet audit (Expérience 20 %, Expertise 25 %, Autorité 25 %, Confiance 30 %). Google ne publie aucune pondération chiffrée ; il indique seulement que la confiance est le critère le plus important.*

| Facteur | Score | Points forts | Points faibles |
|---|---|---|---|
| Expérience (20 %) | 35 / 100 | 21 sites clients listés avec lien vers le site en ligne ; méthode en 4 semaines décrite ; une étude de cas (Novexia) | Aucun chiffre de résultat, aucune capture, aucun avant/après ; l'étude de cas Novexia contient une erreur de localisation ; « +300 % » sans source ; aucun exemple de logo ni de présentation alors que ces services sont vendus |
| Expertise (25 %) | 50 / 100 | Articles sectoriels détaillés (Ordre des architectes, CNB, Ordre des kinés, RGPD santé, CNIL, France Num, Baymard) | Pas de page auteur ; bio variable ; erreurs techniques (étoiles AggregateRating, « Core Web Vitals > 85 », FAQ en résultats enrichis) ; appellation « Google My Business » dépassée ; calculs faux |
| Autorité (25 %) | 30 / 100 | 39 avis Google annoncés ; entité Google (kgmid) ; LinkedIn fondateur et entreprise dans le schéma | Aucune mention presse, aucun lien de crédit chez les clients (vérifié sur Novexia : aucune mention d'Aurea Media), entité récente |
| Confiance (30 %) | 45 / 100 | Mentions légales, SIREN, téléphone, e-mail nominatif, horaires, prix publics sur les deux offres principales | Ancienneté contestable, chiffres contradictoires (39 ou 40 avis, 1 000 € ou 1 200 €), promesse « sans thème acheté » contredite par le site lui-même, statut EI présenté comme « agence », mentions légales EI incomplètes |
| **Total pondéré** | **41 / 100** | | |

### 1.1 Expérience

- **Réalisations** (`/realisations-agence-web/`) : 21 projets avec lien vers le site en ligne. C'est le meilleur actif E-E-A-T du site. Mais chaque fiche décrit le client (« Novexia Construction est une entreprise… ») et jamais ce qu'Aurea Media a fait : pas de brief, pas de livrables, pas de résultat. Le H1 promet « des résultats concrets » et la page n'en montre aucun.
- **Écart entre le nombre annoncé et le nombre montré** : « 45+ projets » est répété sur environ 40 emplacements, alors que 21 sont visibles. Il n'y a qu'un seul e-commerce (Sunrock) et aucun exemple de branding ni de maquette de présentation.
- **Étude de cas Novexia** (`/site-vitrine-artisan-renovateur-en-batiment/`) : elle présente l'entreprise « en Île-de-France », et l'accueil comme la page vitrine parlent de « SEO local sur toute l'Île-de-France ». Or la page Réalisations et le site du client (title : « Entreprise de Construction & Rénovation en Occitanie ») la situent en Occitanie. Vérifié le 25/09/2026. Le chiffre « 11 pages : la moyenne d'un site sur-mesure BTP » est tiré de ce seul site.
- **Occasion manquée** : les articles sectoriels ne renvoient pas vers les projets correspondants, alors que ces projets existent (avocat vers Avocat Paoli, médecin vers Xpert Medical, coach vers ENZYM, artisan vers Novexia, e-commerce vers Sunrock).

### 1.2 Expertise et auteur (Alexandre Coury)

- **Pas de page auteur** : `/author/…` et `/a-propos/` redirigent vers l'accueil. Le schéma `Person` pointe vers `/agence-web-a-paris/#alexandre`, une page agence sans parcours.
- **Bio incohérente** : « Fondateur & Directeur créatif » dans le schéma des articles de mai et juin, « Fondateur » dans ceux d'août et septembre, « Développeur web & marketing digital » dans le texte visible. La signature des premiers articles se limite au prénom (« Alexandre, Aurea Media Paris »).
- **Aucun élément vérifiable de compétence** : formation, postes précédents, certifications, années d'expérience datées, interventions.
- **Erreurs techniques qui entament l'expertise** :
  - `/avis-google/` affirme que le balisage AggregateRating des avis Google « génère des étoiles dans les résultats ». C'est faux : Google n'affiche plus d'étoiles pour les avis qu'une entreprise publie sur elle-même depuis 2019, et des avis Google recopiés ne sont pas éligibles. L'accueil applique pourtant ce balisage.
  - « Core Web Vitals > 85 » confond les Core Web Vitals (LCP, INP, CLS) avec le score PageSpeed.
  - `/anatomie-site-vitrine-qui-convertit/` recommande FAQ « pour les résultats enrichis ». Google ne les affiche plus depuis le 7 mai 2026.
  - « Google My Business » est utilisé sans mentionner le nom actuel, Google Business Profile, dans les titres de 4 URL.

### 1.3 Autorité

- Points positifs : l'entité Google existe (lien kgmid dans le schéma), il y a des profils LinkedIn, et les avis affichés portent des noms réels.
- Aucun signal externe éditorial n'a été trouvé : ni presse, ni annuaire professionnel, ni crédit « site réalisé par » chez les clients (absent sur novexia-construction.fr ; les autres sites n'ont pas été vérifiés).
- Un avis (Charles Culioli) correspond à un projet du portfolio (Changer d'Air à Massy). Relier avis et réalisations, avec l'accord des clients, renforcerait la crédibilité des deux.

### 1.4 Confiance

- Présents : mentions légales, SIREN, adresse, téléphone, e-mail nominatif, horaires, prix « dès » sur les deux offres principales, devis en 24 h.
- **Mentions légales d'EI incomplètes** : la forme juridique « Entreprise individuelle » est indiquée, mais pas le nom de l'entrepreneur accolé à la mention « EI » ou « entrepreneur individuel ». Le numéro RNE et le numéro de TVA intracommunautaire (ou la mention de franchise) manquent aussi. La page affiche « Dernière mise à jour : mai 2025 », et sa meta description contient une faute (« légalesd'Aurea ») et décrit en réalité la politique de confidentialité. À faire valider par le client ou son conseil.
- **Adresse** : le 60 rue François Ier (Paris 8e) semble être une adresse de domiciliation, où de nombreuses sociétés sont enregistrées (**non vérifié**). Le site parle pourtant de « Studio indépendant dans Paris · Paris 8e » et de rendez-vous en présentiel. Si c'est une domiciliation, il faut éviter de la présenter comme un studio. Pour la fiche Google, les règles excluent les adresses virtuelles non occupées : une fiche en zone desservie, adresse masquée, est plus sûre.
- **Promesses contredites par le site lui-même** : « Chaque site est développé sur-mesure sous WordPress, sans thème acheté ni template » (page agence). Or le site tourne sur le thème Royal Elementor Kit avec Elementor, et le devis type de `/prix-site-vitrine-paris/` indique « Développement WordPress + Elementor ». De même, « Core Web Vitals au vert » et « chargement en moins de deux secondes » sont affichés sur un site qui charge 20 feuilles de style et 18 scripts.
- **Statut** : les articles opposent l'agence au « freelance seul » qui « ne peut pas garantir » la continuité, alors qu'Aurea Media est une entreprise individuelle d'une personne. Il vaut mieux assumer « studio indépendant, un seul interlocuteur », ce qui est déjà l'argument le plus fort du site.

### 1.5 Affirmations fragiles ou invérifiables

| Affirmation | Où | Problème | Formulation honnête |
|---|---|---|---|
| « +300 % de trafic (organique moyen) après 6 mois » | Accueil (2 fois), badge de la page vitrine | Aucune source, aucun échantillon, aucune période | Supprimer, ou remplacer par un cas daté : « Client X : de N à M clics mensuels entre [mois] et [mois] (capture Search Console) » |
| « 45+ projets / sites livrés depuis 2021 » | Environ 40 occurrences (pied de page, bandeaux, meta, schéma) | 21 projets visibles ; la page vitrine parle de « 45 sites vitrines », les autres de « 45 projets » tous types confondus | « 21 projets en ligne à consulter », ou publier la liste complète si elle existe |
| « Agence créée en 2021 » | Hero et FAQ de la page agence, accueil, pied de page de toutes les pages, bios, meta | Voir 1.6 | Voir 1.6 |
| « 40 avis Google », « tous 5 étoiles » | `/choisir-agence-web-paris/`, `/creation-site-internet-clamart/`, bandeau de 6 articles | Le reste du site dit 39 avis et 4,9/5 ; « tous 5 étoiles » est incompatible avec 4,9 | Un seul chiffre, mis à jour automatiquement depuis la fiche, ou « 4,9/5 sur Google » sans nombre figé |
| « 200+ audits réalisés », « 95 % des cas », « 90 % de nos audits » | `/site-vitrine-qui-ne-genere-pas-de-clients/` | Invérifiable, et incohérent avec 45 projets | « Les causes que je rencontre le plus souvent lors de mes audits » |
| « WooCommerce pour 80 % de nos projets e-commerce » | `/shopify-vs-woocommerce/` | Un seul e-commerce dans le portfolio | « Je recommande WooCommerce dans la plupart des cas, sauf si… » |
| « 85 % de nos clients » | `/wordpress-vs-webflow/` | Idem | Formulation qualitative |
| « Plus de 2 000 agences web à Paris » | `/choisir-agence-web-paris/` | Aucune source | Sourcer ou retirer |
| « 11 pages : la moyenne d'un site BTP » | Article rénovation | Un seul site | « Le site Novexia compte 11 pages » |
| Novexia « en Île-de-France » | Accueil, page vitrine, article rénovation | Faux : Occitanie | Corriger partout |

**Chiffres corrompus** (hypothèse : un rechercher/remplacer global de « 150 » par « 45 », probablement lors de la correction du nombre de projets ; à confirmer dans l'historique des révisions WordPress) :

| Page | Texte actuel (tiret remplacé par « à ») | Pourquoi c'est faux |
|---|---|---|
| `/choisir-agence-web-paris/` | Hébergement imposé « 50 à 45 €/mois » | Fourchette inversée |
| `/choisir-agence-web-paris/` | Modifications facturées « 80 à 45 € HT » | Fourchette inversée |
| `/choisir-agence-web-paris/` | « 800 € + 45 €/mois = 6 200 € en 3 ans » | 800 + 45 × 36 = 2 420 €. Le total n'est juste qu'avec 150 €/mois |
| `/prix-site-vitrine-paris/` | Poste « Brief stratégique : 45 € HT » dans un devis total de 1 500 € | La somme des postes fait 1 395 €. Juste avec 150 € |
| `/prix-site-vitrine-paris/` | « 80 à 45 €/mois », « 80 à 45 €/heure », « entre 49 et 45 € HT/mois » | Fourchettes inversées |
| `/creation-de-site-vitrine-paris/` (FAQ) | « entre 60 et 45 € par an » | Fourchette inversée, sur une page service |
| `/site-vitrine-qui-ne-genere-pas-de-clients/` | « 45 à 350 € HT » | Très probablement 150 à 350 € |

**Prix contradictoires** :

| Offre | Pages services | Articles |
|---|---|---|
| Site vitrine | Dès 1 000 € HT | Dès 1 200 € HT dans une douzaine d'articles (prix, choisir, anatomie, soi-même, avocat, médecin, kiné, architecte, restaurant, Clamart, Boulogne) |
| E-commerce | Dès 1 650 € HT | 2 000 € HT (`/combien-coute-un-site-e-commerce/`), 2 500 € HT (`/creer-un-site-e-commerce-professionnel/`, Clamart, Boulogne) |
| Branding | « Sur devis » | Dès 800 € HT ; identité et site dès 1 800 € HT |
| Refonte, maintenance, fiche Google | Aucune page | Refonte dès 900 € HT ; maintenance dès 89 €/mois ; audit de fiche dès 200 € ; gestion de fiche 89 à 189 €/mois |

### 1.6 SIREN 992 693 473 et « depuis 2021 »

- **Statut : non vérifié.** L'API `recherche-entreprises.api.gouv.fr` était inaccessible depuis l'environnement d'audit (blocage réseau 403).
- **Indices** :
  - Les numéros SIREN commençant par 99 appartiennent aux séries attribuées le plus récemment (estimation : immatriculation vers 2025, à confirmer).
  - Les mentions légales datent de mai 2025.
  - Le premier article est daté de mai 2026.
  - Les avis affichés datent de janvier et septembre 2025.
- **Point juridique important** : pour une entreprise individuelle, l'INSEE attribue un SIREN unique à la personne, qu'elle conserve même après une cessation puis une reprise d'activité. Un SIREN récent pour une EI signifie donc, en principe, une première immatriculation récente en nom propre. Si Alexandre a travaillé avant, c'était sous un autre cadre (salariat, portage, société, étranger) : il faut le documenter.
- **Vérification (2 minutes)** : ouvrir `https://annuaire-entreprises.data.gouv.fr/entreprise/992693473` et relever la date de création.
- **Présentation honnête**, au choix selon ce que montre le registre :
  - *Activité antérieure documentable* : « Alexandre Coury conçoit des sites web depuis 2021 (d'abord en [cadre réel]). Aurea Media est immatriculée depuis [mois année]. » Distinguer l'expérience de la personne et l'âge de la structure, et détailler le parcours sur la page À propos.
  - *Pas d'activité documentable avant l'immatriculation* : retirer « depuis 2021 » partout et écrire « Studio créé en [année] ». La crédibilité viendra alors des preuves de résultat, pas de l'ancienneté.
- **Emplacements à corriger** : pied de page (toutes les pages), hero et chiffre « 2021 : année de création » de la page agence, FAQ « Depuis quand Aurea Media existe-t-elle ? », meta descriptions de l'accueil et de la page e-commerce, bios auteur, `/choisir-agence-web-paris/` (« Fondée en 2021 », « engagement tenu depuis l'ouverture »), et `foundingDate` dans le schéma s'il est ajouté.

## 2. Cannibalisation

Règle appliquée : une intention de recherche = une URL. Avant chaque 301, il faut comparer dans Search Console (16 mois) les clics, les impressions et les backlinks des URL du groupe. L'URL cible doit être celle qui performe le mieux, même si ce n'est pas celle proposée ci-dessous (**non vérifié**, données absentes).

| Groupe (intention) | URL concernées | Décision | Détail |
|---|---|---|---|
| **« agence web Paris »** | `/` (title « Agence Web à Paris… »), `/agence-web-a-paris/` (title commençant par « Agence web à Paris »), `/choisir-agence-web-paris/` (title commençant par « Agence web Paris 2026 ») | Différencier | L'accueil garde « agence web Paris ». `/agence-web-a-paris/` devient la page À propos et page auteur (mot-clé « développeur web freelance Paris ») ; en refonte, créer `/a-propos/` avec une 301 depuis `/agence-web-a-paris/`, sauf si Search Console montre que cette URL se classe mieux que l'accueil sur « agence web Paris » (dans ce cas, inverser les rôles). `/choisir-agence-web-paris/` reste un guide informationnel, retitré « Choisir une agence web à Paris : 7 critères », sans commencer par « Agence web Paris » |
| **Google My Business : optimiser sa fiche** | `/optimiser-sa-fiche-google-my-business/` (2 714 mots), `/google-my-business-artisans-tpe/` (3 111 mots), catégorie `/google-my-business-seo-local/` (reprend 71 % du texte du premier) | Fusionner | 301 de `/google-my-business-artisans-tpe/` vers `/optimiser-sa-fiche-google-my-business/`, en reprenant la section « configuration par corps de métier ». Les deux articles déroulent « 6 étapes » et « 7 étapes » pour le même objectif. Mettre à jour le vocabulaire : « Google Business Profile (ex Google My Business) » |
| Fiche Google sans local | `/optimiser-fiche-google-my-business-coach/` | Différencier | Recentrer sur l'intention distincte « fiche Google sans adresse, zone desservie » (coach, consultant, thérapeute) |
| Avis Google | `/avis-google/` | Garder | Intention distincte. Corriger le passage AggregateRating |
| **Logo et charte graphique** | `/logo-charte-graphique-identite-visuelle/` (juin, 2 517 mots), `/logo-et-charte-graphique/` (août, 1 250 mots), service `/branding-identite/` | Fusionner | 301 de `/logo-et-charte-graphique/` vers `/logo-charte-graphique-identite-visuelle/` (intention « différences et budget »). Le service vise « création logo Paris ». `/refonte-identite-visuelle-entreprise/` est distinct et reste |
| **Prix d'un site vitrine** | `/prix-site-vitrine-paris/` (2 677 mots), `/creation-site-vitrine-tpe-paris/` (1 080 mots, H1 sur le budget), section « Combien coûte un site vitrine ? » de `/creation-de-site-vitrine-paris/` | Fusionner et différencier | 301 de `/creation-site-vitrine-tpe-paris/` vers `/prix-site-vitrine-paris/` (ajouter une section TPE). La page service garde une grille de prix courte et renvoie au guide. Supprimer le lien vers `/prix-site-vitrine-2025/`, qui n'existe pas |
| **Pages locales 92** | `/agence-web-hauts-de-seine/` (hub), `/site-internet-boulogne-billancourt/`, `/creation-de-site-e-commerce-boulogne/`, `/agence-web-collectivites-boulogne-billancourt/`, `/creation-site-internet-clamart/`, `/agence-seo-clamart/` | Fusionner à 3 pages | Garder le hub et une page par ville (Boulogne, Clamart). 301 de `/agence-seo-clamart/` vers `/creation-site-internet-clamart/` (il n'existe aucun service SEO vendu séparément, et le texte recoupe le hub à 23 %). 301 de `/creation-de-site-e-commerce-boulogne/` vers `/site-internet-boulogne-billancourt/`. 301 de `/agence-web-collectivites-boulogne-billancourt/` vers le hub, sauf si le client a de vraies références de collectivités (aucune dans le portfolio). Convertir les pages gardées d'article en page, sans changer leur slug. Chaque page ville doit contenir une preuve locale réelle (client, projet, déplacement), sinon elle ressemble à une page satellite |
| E-commerce | Service `/creation-de-site-e-commerce-paris/`, `/creer-un-site-e-commerce-professionnel/`, `/combien-coute-un-site-e-commerce/`, catégorie `/creation-site-ecommerce/` (reprend 76 % du texte de l'article prix) | Différencier | Service = transactionnel ; « créer » = étapes ; « combien coûte » = prix. Harmoniser les prix (1 650 € partout). Corriger la catégorie (extraits courts) |
| Site vitrine artisan | `/site-vitrine-pour-artisan/`, `/site-vitrine-artisan-renovateur-en-batiment/` | Différencier | Le second vise « site internet entreprise de rénovation » (coordination de plusieurs corps d'état) et devient une vraie étude de cas Novexia, localisation corrigée |
| Catégories et services | Catégorie `/creation-site-vitrine/` et service `/creation-de-site-vitrine-paris/` ; catégorie `/creation-site-ecommerce/` et service e-commerce ; `/creation-site-web/` (298 mots) | Différencier | Slugs presque identiques entre catégories et services. En refonte, placer les catégories sous `/conseils/…` avec des 301, donner à chacune un H1 et une introduction, et envoyer `/creation-site-web/` en 301 vers `/nos-conseils-web/` |

Bilan : 6 redirections 301 (`google-my-business-artisans-tpe`, `logo-et-charte-graphique`, `creation-site-vitrine-tpe-paris`, `agence-seo-clamart`, `creation-de-site-e-commerce-boulogne`, `agence-web-collectivites-boulogne-billancourt`), plus 1 conditionnelle (`agence-web-a-paris`) et 1 catégorie (`creation-site-web`). Après fusion, mettre à jour tous les liens internes vers la cible (ne pas laisser de liens vers une URL redirigée).

## 3. Qualité on-page (guidelines Rank Math du client)

### 3.1 Constats transverses

- **Titles hors norme (50 à 60 caractères)** :
  - Trop longs : `/site-vitrine-coach-consultant/` (67), `/creation-de-site-e-commerce-paris/` (66), `/contact-agence/` (66), `/anatomie-site-vitrine-qui-convertit/` (65), `/creation-de-site-vitrine-paris/` (63), `/wordpress-vs-webflow/` (63), `/site-vitrine-pour-artisan/` (63), `/nos-services/` (62).
  - Trop courts : `/realisations-agence-web/` (43), 4 articles (47 à 48), catégories (41 à 47), `/creation-site-web/` (34).
- **Descriptions hors norme (140 à 160 caractères)** :
  - Trop longues : accueil et `/maquettes-de-presentation/` (177), `/wordpress-vs-webflow/` (170), `/contact-agence/` (165), 2 articles à 161, catégories de 293 à 364.
  - Trop courtes : 10 articles de 113 à 133 (avocat 113, médecin 120, architecte 121, kiné 122, refonte 127, branding 128…).
- **Métadonnées répétitives** (constat manuel, script non exécuté) : 21 titles d'articles sur 33 contiennent « guide », « guide complet » ou « guide essentiel ». La plupart des descriptions finissent par une signature du type « par Aurea Media, agence web Paris » au lieu d'un appel à l'action. C'est la signature d'une production en série : il faut réécrire avec un bénéfice propre à chaque page.
- **« 2026 »** apparaît dans environ 13 titles et 19 H1. Pour les articles de prix et les comparatifs, c'est pertinent s'ils sont vraiment mis à jour chaque année. Pour les guides sectoriels (avocat, médecin, restaurant…), c'est inutile et ça les périmera en janvier 2027.
- **H1** : un seul H1 par page partout, sauf les 4 catégories qui n'en ont aucun. Plusieurs H1 ne contiennent pas le mot-clé (Réalisations, Contact, Conseils). Les H1 de la seconde série d'articles finissent par un point, ce qui est inhabituel mais sans effet SEO.
- **Mot-clé dans l'introduction** : correct sur les pages services et les articles récents. Les articles de mai et juin ouvrent souvent sur une statistique avant le mot-clé.
- **Images sans alt** : 1 sur `/nos-services/`, `/branding-identite/` et `/creation-de-site-e-commerce-paris/`.

### 3.2 Tableau des 10 pages principales

Le « – » placé avant la marque suit le format Rank Math demandé par le client (« Mot-clé | Proposition – Marque »). Les nombres entre parenthèses sont des nombres de caractères, espaces compris. Les volumes de recherche ne sont **pas vérifiés**. Les chiffres « 4,9/5 » et « 39 avis » ont été volontairement retirés des métadonnées tant qu'ils ne sont pas synchronisés avec la fiche.

| Page | Mot-clé principal | Title proposé (car.) | Meta description proposée (car.) | H1 proposé |
|---|---|---|---|---|
| `/` | agence web Paris | Agence web Paris \| Sites qui convertissent – Aurea Media (56) | Agence web à Paris pour indépendants et TPE : sites vitrines et e-commerce WordPress sur mesure, SEO inclus, un seul interlocuteur. Devis gratuit sous 24h. (155) | Agence web à Paris : des sites qui transforment vos visiteurs en clients |
| `/agence-web-a-paris/` (futur `/a-propos/`) | développeur web freelance Paris | Développeur web freelance Paris \| Mon parcours – Aurea Media (60) | Développeur web freelance à Paris, Alexandre Coury conçoit votre site WordPress de A à Z, sans sous-traitance. Parcours, méthode et projets livrés en détail. (157) | Alexandre Coury, développeur web freelance à Paris et fondateur d'Aurea Media |
| `/nos-services/` | création site internet Paris | Création site internet Paris \| Nos 4 offres – Aurea Media (57) | Création de site internet à Paris : site vitrine dès 1 000 € HT, e-commerce dès 1 650 € HT, logo et pitch deck. SEO inclus, prix publics. Demandez un devis. (156) | Création de site internet à Paris : nos quatre offres |
| `/creation-de-site-vitrine-paris/` | création site vitrine Paris | Création site vitrine Paris \| Dès 1 000 € HT – Aurea Media (58) | Création de site vitrine à Paris dès 1 000 € HT : design sur mesure, SEO local inclus, mise en ligne en 4 semaines. Un seul interlocuteur. Devis gratuit en 24h. (160) | Création de site vitrine à Paris, pensé pour générer des demandes de devis |
| `/creation-de-site-e-commerce-paris/` | création site e-commerce Paris | Création site e-commerce Paris \| Dès 1 650 € – Aurea Media (58) | Création de site e-commerce à Paris dès 1 650 € HT : boutique WooCommerce à votre nom, zéro commission sur vos ventes, SEO inclus. Devis détaillé sous 24h. (155) | Création de site e-commerce à Paris, sans commission sur vos ventes |
| `/branding-identite/` | création logo Paris | Création logo Paris \| Logo et charte graphique – Aurea Media (60) | Création de logo à Paris : 3 pistes créatives, charte graphique complète et cession totale des droits. Tarif affiché dès 800 € HT. Demandez votre devis gratuit. (160) | Création de logo et d'identité visuelle à Paris |
| `/maquettes-de-presentation/` | création pitch deck | Création pitch deck Paris \| Livré en 1 semaine – Aurea Media (60) | Création de pitch deck et de présentations commerciales à Paris : structure, design et fichiers 100 % éditables livrés en 1 semaine. Devis gratuit sous 24h. (156) | Création de pitch deck et de présentations commerciales |
| `/realisations-agence-web/` | exemples de sites vitrines | Exemples de sites vitrines \| Projets livrés – Aurea Media (57) | Exemples de sites vitrines et e-commerce créés par Aurea Media : BTP, santé, industrie, avocat, immobilier. Sites à visiter. Parlez-nous de votre projet. (153) | Exemples de sites vitrines et e-commerce livrés à nos clients |
| `/contact-agence/` | devis site internet Paris | Devis site internet Paris \| Réponse sous 24h – Aurea Media (58) | Demandez un devis de site internet à Paris : réponse personnelle d'Alexandre Coury sous 24h ouvrées, devis détaillé ligne par ligne, sans engagement. (149) | Demander un devis de site internet |
| `/nos-conseils-web/` | conseils site internet | Conseils site internet \| Guides TPE et PME – Aurea Media (56) | Conseils site internet pour TPE et indépendants : prix réels, SEO local, fiche Google, choix du CMS. Guides d'un développeur web parisien. Bonne lecture. (153) | Conseils pour créer et faire connaître votre site internet |

Remarques :

- Pour `/branding-identite/`, le prix « dès 800 € HT » vient des articles. Il faut le confirmer avant de l'afficher, ou retirer cette phrase de la description.
- Pour `/maquettes-de-presentation/`, « maquettes de présentation » est un terme peu recherché (non vérifié). « Pitch deck » ou « présentation PowerPoint professionnelle » correspondent mieux à la demande. Le slug actuel peut être conservé.
- Les slugs des pages services sont conservés : ils sont courts et descriptifs, et les changer coûterait de la valeur.

## 4. Contenu mince, dupliqué et maillage

- **Pages catégories** (`/creation-site-vitrine/`, `/creation-site-ecommerce/`, `/google-my-business-seo-local/`, `/creation-site-web/`) :
  - Aucun H1, et des descriptions de 293 à 364 caractères.
  - La catégorie « SEO (Référecement) » contient une faute.
  - Les extraits reprennent le texte entier de certains articles : 76 % de `/combien-coute-un-site-e-commerce/` et 71 % de `/optimiser-sa-fiche-google-my-business/` sont dupliqués dans leur catégorie.
  - On y trouve des dates au format américain (« août 27, 2026 ») et des mentions « Commentaires fermés sur… ».
  - `/creation-site-web/` compte 298 mots.
- **Pages minces** : `/nos-conseils-web/` (344 mots, dont une grande part de listes de liens), `/creation-site-web/` (298), `/contact-agence/` (586 mots, suffisant pour ce type de page).
- **Articles sous le seuil de 1 500 mots** (seuil de couverture, pas facteur de classement) :
  - `/creation-site-vitrine-tpe-paris/` (1 080), `/agence-web-collectivites-boulogne-billancourt/` (1 097), `/logo-et-charte-graphique/` (1 250) et `/creation-de-site-e-commerce-boulogne/` (1 290). Tous les quatre sont des candidats à la fusion.
  - `/optimiser-fiche-google-my-business-coach/` (1 339) et `/site-vitrine-artisan-renovateur-en-batiment/` (1 424), à enrichir avec des cas réels.
- **Blocs répétés** : faibles (le bandeau d'avis, les CTA « Décrivez votre projet… »). La duplication n'est pas un problème de corps de texte, sauf sur les catégories.
- **Liens internes cassés : environ 90 liens vers 15 URL inexistantes**. Toutes redirigent en 301 vers l'accueil (vérifié le 25/09/2026), ce qui revient à une soft 404 et fait perdre le maillage.

| URL cible inexistante | Liens | Correction |
|---|---|---|
| `/optimiser-fiche-google-my-business/` | 21 | Vers `/optimiser-sa-fiche-google-my-business/` |
| `/combien-coute-un-site-ecommerce/` | 13 | Vers `/combien-coute-un-site-e-commerce/` |
| `/nos-conseils-web/creation-site-vitrine/` (fil d'Ariane de 12 articles) | 12 | Vers la catégorie réelle |
| `/site-vitrine-vs-site-ecommerce/` | 10 | Créer l'article (intention réelle, 10 liens la réclament) ou retirer les liens |
| `/creation-google-my-business/` (« Voir le service GMB ») | 10 | Créer la page service si ce service est vendu, sinon retirer |
| `/prix-site-vitrine-2025/` | 4 | Vers `/prix-site-vitrine-paris/` |
| `/identite-visuelle-strategie-digitale/`, `/ghostwriting-linkedin-paris/` | 3 + 3 | Retirer (contenu ou service inexistant) |
| `/nos-conseils-web/creation-site-ecommerce/`, `/nos-conseils-web/google-my-business-seo-local/` | 3 + 3 | Vers les catégories réelles |
| `/creer-site-ecommerce-professionnel/`, `/creer-site-vitrine-soi-meme-ou-agence/`, `/nos-conseils-web/agence-web-ile-de-france/` | 2 + 2 + 2 | Vers les bons slugs, ou retirer |
| `/refonte-identite-visuelle/`, `/nos-conseils-web/branding-identite-visuelle/` | 1 + 1 | Vers `/refonte-identite-visuelle-entreprise/` et la catégorie réelle |

## 5. Lisibilité, ton et fraîcheur

- **Lisibilité** : phrases moyennes de 18 à 22 mots dans les articles, score Kandel et Moles d'environ 38 à 55 (difficile à assez difficile). C'est acceptable pour un public de dirigeants, mais un peu lourd pour la cible artisans et TPE. Objectif : 15 mots par phrase en moyenne et des paragraphes de 3 lignes au plus.
- **Deux générations d'articles** :
  - Mai et juin 2026 : 9 à 20 tirets cadratins pour 1 000 mots (`/shopify-vs-woocommerce/` 20, `/logo-charte-graphique-identite-visuelle/` 16, `/choisir-agence-web-paris/` 16, `/optimiser-sa-fiche-google-my-business/` 15, `/wordpress-vs-webflow/` 15, `/site-vitrine-pour-artisan/` 15). On y trouve aussi des tournures « ce n'est pas X, c'est Y », des statistiques très affirmées et des prix à 1 200 €.
  - Août et septembre 2026 : sections numérotées, encadré « Écrit par », pas de tiret, prix à 1 000 €.
  - Les tirets en masse et les structures répétées sont des marqueurs fréquents de texte généré par IA ; ils vont aussi contre la préférence du client. Le contenu IA est acceptable s'il apporte une expérience réelle. Ici, c'est justement ce qui manque (voir 1.1).
- **Rythme de publication** : 33 articles datés entre le 15 mai et le 15 septembre 2026, avec des gabarits identiques. Pour un studio d'une personne, cela ressemble à une production à grande échelle. Mieux vaut moins d'articles, chacun avec un cas réel.
- **Dates** : les articles de mai et juin ont `dateModified` égal à `datePublished` et affichent « Mis à jour mai 2026 », alors que le sitemap indique des modifications en septembre. Il faut une date de mise à jour réelle, affichée et balisée, qui ne change que lors d'une vraie modification.
- **Ton** : clair et orienté bénéfice sur les pages services. Il devient parfois accusateur envers les concurrents (« levez-vous », « pièges déloyaux ») et survend (« dominer Google Maps »). Recommandation : un ton de conseiller à la première personne (« ce que je vois chez mes clients »), cohérent avec le « un seul interlocuteur ».

## 6. Préparation aux réponses IA (citation)

- **Points forts** :
  - Prix « dès » et délais clairs sur les pages services.
  - FAQ en HTML, avec des réponses autonomes de 2 à 4 phrases.
  - Encadré « Résumé en 30 secondes » dans plusieurs articles.
  - llms.txt présent, et BlogPosting avec auteur.
- **Points faibles** :
  - Faits contradictoires : un moteur IA peut citer 1 000 € ou 1 200 €, 39 ou 40 avis, 2021 ou une autre date.
  - Aucune fiche « Aurea Media en bref » datée.
  - Des statistiques sans source.
  - Pas d'entité auteur solide.
- **Actions** :
  - Créer un bloc de faits unique (prix, délais, zone, statut, date d'immatriculation, nombre de projets documentés), réutilisé à l'identique par la page À propos, le pied de page et le schéma.
  - Sourcer chaque statistique avec un lien et une année.
  - Commencer chaque article par une réponse directe de 40 à 60 mots à la question du title.

## 7. Plan d'action priorisé

### Critique

**C1. Corriger les chiffres corrompus et contradictoires**
- Constat : fourchettes inversées et calculs faux (section 1.5), prix de départ différents selon les pages, 39 ou 40 avis, « tous 5 étoiles », Novexia placée en Île-de-France.
- Pourquoi : erreurs visibles par un prospect qui compare, et atteinte directe à la confiance, le facteur le plus important.
- Action : restaurer les valeurs d'origine avec l'historique des révisions ; fixer une grille officielle (vitrine 1 000 €, e-commerce 1 650 €, etc.) et l'appliquer aux 33 articles ; supprimer les nombres d'avis figés ; corriger Novexia (Occitanie).
- Vérification : une recherche plein texte (WP CLI ou export) ne renvoie plus « 45 € », « 1 200 € HT », « 2 000 € HT », « 2 500 € HT », « 40 avis » ni « Île-de-France » associé à Novexia.

**C2. Aligner l'ancienneté sur le registre**
- Constat : « depuis 2021 » est affirmé partout ; le SIREN semble récent (non vérifié).
- Pourquoi : une date de création fausse est une affirmation trompeuse, facile à vérifier par un prospect ou un concurrent.
- Action : suivre la section 1.6.
- Vérification : la date affichée est identique à celle de l'Annuaire des entreprises, et aucune occurrence de « 2021 » ne reste sans contexte.

**C3. Retirer ou prouver les affirmations de résultats**
- Constat : « +300 % », « 200+ audits », « 80 % », « 85 % », « 45+ projets ».
- Pourquoi : aucune preuve ; les guidelines d'évaluation de Google considèrent les affirmations non étayées sur des pages commerciales comme un signal de faible confiance.
- Action : supprimer, ou remplacer par des cas datés avec capture et accord du client.
- Vérification : chaque chiffre de résultat restant renvoie à une étude de cas.

**C4. Réparer les environ 90 liens internes cassés**
- Constat : 15 URL inexistantes redirigées vers l'accueil (section 4).
- Pourquoi : maillage perdu, parcours cassé, et l'accueil reçoit des liens hors sujet.
- Action : corriger dans l'ancien site si la refonte tarde, sinon dans le plan de migration ; remplacer la redirection des 404 vers l'accueil par une vraie 404 (audit technique).
- Vérification : un crawl (Screaming Frog ou équivalent) ne trouve plus aucun lien interne en 3xx ni en 4xx.

### Haute

**H1. Traiter la cannibalisation (section 2)**
- Constat : 6 groupes en conflit.
- Pourquoi : les URL se concurrencent et diluent liens et signaux.
- Action : 6 redirections 301 plus 2 conditionnelles, repositionnements, mise à jour des liens internes. Décider avec les données Search Console.
- Vérification : 8 semaines après, dans Search Console (Performances, filtre par requête), une seule URL reçoit les impressions de chaque requête cible.

**H2. Créer une page auteur et À propos**
- Constat : aucune page auteur ; bio en trois versions.
- Pourquoi : c'est la base de l'expertise et de l'autorité d'un studio d'une personne.
- Action : page `/a-propos/` avec photo, parcours daté et vérifiable, stack réelle, projets signés, liens LinkedIn et profils professionnels, schéma `Person` avec `sameAs`. Un seul intitulé de poste, une signature cliquable et une date de mise à jour sur chaque article.
- Vérification : tous les `author` du schéma pointent vers cette page ; le test des résultats enrichis ne signale aucune erreur.

**H3. Publier 3 à 5 études de cas chiffrées**
- Constat : aucun résultat mesuré.
- Pourquoi : c'est le premier « E » (expérience), et la meilleure réponse à « prouvez-le ».
- Action : contexte, objectif, ce qui a été fait, captures avant/après, chiffres Search Console ou Analytics datés, avis du client. Candidats : Novexia, Xpert Medical, Avocat Paoli, Sunrock, Barber Korner. Relier chaque article sectoriel au cas correspondant.
- Vérification : chaque page service et chaque article sectoriel renvoie vers au moins un cas.

**H4. Réécrire les métadonnées**
- Constat : section 3.1.
- Pourquoi : format Rank Math non respecté et métadonnées répétitives.
- Action : appliquer le tableau 3.2, puis traiter les 33 articles de la même façon (sans « guide complet », avec « 2026 » seulement sur les pages de prix et les comparatifs).
- Vérification : export Rank Math, 100 % des titles entre 50 et 60 caractères et des descriptions entre 140 et 160.

**H5. Compléter les mentions légales et clarifier l'adresse**
- Constat : section 1.4.
- Pourquoi : obligation légale et signal de confiance.
- Action : « Alexandre Coury EI », numéro RNE, TVA, date de mise à jour, meta description propre ; présenter l'adresse comme adresse administrative si c'est une domiciliation ; vérifier la fiche Google (zone desservie).
- Vérification : relecture par le client ou son conseil ; fiche Google cohérente avec le site (nom, adresse, téléphone).

**H6. Dire la vérité sur la stack et le statut**
- Constat : « sans thème ni template » alors que le site utilise Elementor et Royal Elementor Kit ; « agence » face au « freelance seul ».
- Pourquoi : contradictions faciles à repérer (le code source l'affiche).
- Action : décrire la méthode réelle, et assumer « studio indépendant, un seul interlocuteur ». La refonte sans Elementor rendra la promesse vraie : dater ce changement.
- Vérification : plus aucune promesse contredite par le code source du site.

### Moyenne

**M1. Pages catégories**
- Constat : pas de H1, descriptions trop longues, articles entiers en extrait, faute « Référecement ».
- Pourquoi : contenu dupliqué et pages faibles indexées.
- Action : un H1 et 150 à 250 mots d'introduction par catégorie, des extraits de 30 mots, une description de 140 à 160 caractères, « SEO local » au lieu de « SEO (Référecement) », `/creation-site-web/` en 301 vers `/nos-conseils-web/`.
- Vérification : un crawl montre un H1 par catégorie et moins de 10 % de texte commun avec les articles.

**M2. Pages locales**
- Constat : 6 URL pour le 92, publiées comme articles, sans preuve locale.
- Pourquoi : risque de pages satellites et cannibalisation.
- Action : section 2 ; convertir en pages ; ajouter un client, un projet ou une zone réelle par ville.
- Vérification : 3 URL locales indexées, chacune avec une preuve locale.

**M3. Fraîcheur**
- Constat : « 2026 » dans 19 H1 ; dates de mise à jour non fiables.
- Pourquoi : pages périmées en janvier et signal de fraîcheur faussé.
- Action : retirer l'année des guides sectoriels ; garder une date de mise à jour réelle, affichée et balisée.
- Vérification : `dateModified` correspond à la dernière modification de fond.

**M4. Balisage et exactitude technique**
- Constat : HowTo sur 12 articles, 2 JSON-LD invalides, intitulé de poste incohérent, conseil erroné sur AggregateRating, FAQ présentée comme source de résultats enrichis.
- Pourquoi : les erreurs de balisage et les conseils faux nuisent à l'expertise.
- Action : retirer HowTo ; corriger les JSON invalides ; ne pas compter sur FAQPage pour gagner de la place dans les résultats ; corriger `/avis-google/` et `/anatomie-site-vitrine-qui-convertit/`. À coordonner avec l'audit schéma.
- Vérification : test des résultats enrichis et validateur Schema.org sans erreur.

**M5. Page Réalisations**
- Constat : 13 caractères « ‡ » à la place d'apostrophes ; fiches centrées sur le client.
- Pourquoi : bug visible et preuves faibles.
- Action : corriger l'encodage ; pour chaque projet, préciser le rôle d'Aurea Media, les livrables, la date, et un résultat ou un avis si possible ; ajouter au moins un exemple de branding et un de présentation.
- Vérification : aucun « ‡ » dans le HTML ; chaque fiche contient les mentions « Rôle » et « Livré en ».

**M6. Page Tarifs unique**
- Constat : branding « sur devis » alors que 800 € apparaît dans les articles ; refonte, maintenance et fiche Google chiffrées uniquement dans les articles.
- Pourquoi : transparence et source unique pour les moteurs IA.
- Action : créer `/tarifs/` et y renvoyer depuis les articles.
- Vérification : chaque prix du site correspond à celui de la page Tarifs.

### Basse

**B1. Lisibilité et ton**
- Constat : phrases longues et tirets cadratins dans les articles de mai et juin.
- Pourquoi : cible TPE, préférence du client, marqueurs de texte généré.
- Action : réécriture légère (phrases de 15 mots en moyenne, suppression des tirets, variété des structures).
- Vérification : score Kandel et Moles au-dessus de 50 sur les articles réécrits.

**B2. Terminologie**
- Constat : « Google My Business » partout.
- Action : écrire « Google Business Profile (ex Google My Business) » dans le texte, et garder « Google My Business » dans les titles là où la demande existe (volume non vérifié).
- Vérification : les deux formes sont présentes dans chaque article sur la fiche.

**B3. Autorité externe**
- Action :
  - Demander un crédit « site réalisé par Aurea Media » aux clients (absent chez Novexia).
  - Relier avis et projets, avec accord.
  - S'inscrire sur des annuaires professionnels pertinents.
  - Choisir les projets mis en avant selon la cible visée : certains projets à caractère politique ou judiciaire peuvent ne pas parler à une TPE (choix éditorial du client).
- Vérification : nouveaux domaines référents visibles dans Search Console (rapport Liens).

**B4. Commentaires internes**
- Constat : notes Elementor et Rank Math laissées dans le code HTML de 8 pages.
- Action : les supprimer lors de la refonte.
- Vérification : le code source ne contient plus « ne PAS ajouter de schéma FAQ ».

## 8. Pour la refonte : garder, changer, créer

| Décision | URL |
|---|---|
| **Garder** (URL et contenu, à corriger) | `/`, `/nos-services/`, `/creation-de-site-vitrine-paris/`, `/creation-de-site-e-commerce-paris/`, `/branding-identite/`, `/maquettes-de-presentation/`, `/realisations-agence-web/`, `/contact-agence/`, `/nos-conseils-web/`, `/prix-site-vitrine-paris/`, `/combien-coute-un-site-e-commerce/`, `/creer-un-site-e-commerce-professionnel/`, `/shopify-vs-woocommerce/`, `/wordpress-vs-webflow/`, `/creer-un-site-vitrine-soi-meme-ou-en-agence/`, `/anatomie-site-vitrine-qui-convertit/`, `/site-vitrine-qui-ne-genere-pas-de-clients/`, `/refonte-site-internet/`, `/optimiser-sa-fiche-google-my-business/`, `/avis-google/`, `/logo-charte-graphique-identite-visuelle/`, `/refonte-identite-visuelle-entreprise/`, les 7 guides sectoriels (avocat, médecin, kiné, architecte, restaurant, coach, artisan), `/agence-web-hauts-de-seine/`, `/site-internet-boulogne-billancourt/`, `/creation-site-internet-clamart/` |
| **Changer** (repositionner) | `/agence-web-a-paris/` (page À propos), `/choisir-agence-web-paris/`, `/optimiser-fiche-google-my-business-coach/`, `/site-vitrine-artisan-renovateur-en-batiment/` (étude de cas), les 4 catégories |
| **Rediriger en 301** | `/google-my-business-artisans-tpe/`, `/logo-et-charte-graphique/`, `/creation-site-vitrine-tpe-paris/`, `/agence-seo-clamart/`, `/creation-de-site-e-commerce-boulogne/`, `/agence-web-collectivites-boulogne-billancourt/`, `/creation-site-web/` (cibles en section 2) |
| **Créer** | `/a-propos/` (page auteur), 3 à 5 études de cas, `/tarifs/`, l'article « site vitrine ou site e-commerce » (réclamé par 10 liens), la page service fiche Google et SEO local uniquement si ce service est vendu (réclamée par 10 liens) |

Avant la mise en ligne : exporter les URL du crawl et de Search Console, construire la table de redirections (anciennes URL vers nouvelles), et recrawler après la bascule pour vérifier qu'aucune URL ne finit en 404 ni en redirection vers l'accueil.

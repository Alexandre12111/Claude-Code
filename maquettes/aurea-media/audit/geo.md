# Audit GEO : visibilité de aurea-media.fr dans les moteurs de réponse IA

Date : 25 septembre 2026. Périmètre : Google AI Overviews et AI Mode, Gemini, ChatGPT Search, Perplexity, Copilot. Sources : crawl du 25/09/2026 (51 URL, HTML brut et texte), robots.txt et llms.txt récupérés en direct, tests d'accès avec les user agents des robots IA.

Limite importante : les sources tierces (Wikipedia, Wikidata, Reddit, YouTube, LinkedIn, Malt, Sortlist, Clutch, Pages Jaunes, registre des entreprises) sont bloquées par le proxy réseau de cet environnement. Tout ce qui les concerne est marqué **non vérifié** et doit être contrôlé à la main (liste de contrôle en section 6).

---

## 1. Synthèse

### Score GEO : 54 / 100

| Dimension | Poids | Note /100 | Points | Résumé |
|---|---|---|---|---|
| Citabilité | 25 % | 55 | 13,8 | Bons blocs « Résumé en 30 secondes », devis poste par poste, FAQ directes. Mais faits contradictoires d'une page à l'autre, fourchettes de prix corrompues, statistiques maison non sourcées. |
| Lisibilité structurelle | 20 % | 62 | 12,4 | Un H1 par article, sommaire, tableaux. Mais 8 % seulement des H2 d'articles sont des questions, catégories sans H1, surtitres décoratifs avant chaque H2. |
| Contenu multimodal | 15 % | 40 | 6,0 | Images avec alt, portfolio avec liens réels. Aucune vidéo, aucune chaîne YouTube liée, simulateurs en JavaScript sans équivalent texte vérifié. |
| Autorité et signaux de marque | 20 % | 35 | 7,0 | Auteur nommé, 39 avis Google, entité Google Knowledge Graph déclarée. Mais un seul profil externe relié (LinkedIn, avec deux URL différentes), aucune mention presse ou annuaire reliée, présence tierce non vérifiée. |
| Accessibilité technique | 20 % | 72 | 14,4 | Rendu serveur (WordPress), tous les robots IA autorisés et servis en 200, max-snippet:-1. Mais données structurées dupliquées et parfois invalides, dates de mise à jour fausses, fausses URL redirigées vers l'accueil, pages lourdes. |
| **Total** | 100 % | | **53,6 soit 54** | |

### Scores par plateforme (estimation)

| Plateforme | Score | Robot qui décide | Point bloquant principal |
|---|---|---|---|
| Google AI Overviews et AI Mode | 52 | Googlebot (Google-Extended n'a aucun effet ici) | Faits contradictoires sur les prix, entité peu corroborée hors du site |
| Gemini (application) | 50 | Google-Extended pour l'ancrage Gemini, Googlebot pour l'index | Mêmes causes, peu de mentions tierces |
| ChatGPT Search | 42 | OAI-SearchBot (GPTBot ne concerne que l'entraînement) | Faible présence sur les sources tierces que ChatGPT cite pour « quelle agence choisir » (annuaires, comparatifs, Reddit) |
| Perplexity | 45 | PerplexityBot | Statistiques non sourcées, peu de données propriétaires datées |
| Copilot (Bing) | 42 | Bingbot | Inscription Bing Webmaster Tools, Bing Places et IndexNow non vérifiées |

### Les 5 changements à plus fort impact

| # | Changement | Priorité | Effort | Gain attendu |
|---|---|---|---|---|
| 1 | Créer une source unique des faits (fiche entité + page /tarifs/) et corriger toutes les contradictions et fourchettes corrompues | Critique | 1 à 2 jours | Les IA arrêtent de citer des prix faux ou incohérents ; condition préalable à toute citation fiable |
| 2 | Unifier les données structurées dans Rank Math (une Organization, une Person, un BlogPosting par article), supprimer HowTo, doublons, AggregateRating et JSON-LD invalides | Critique | 1 jour dans la refonte | Entité claire et stable pour Google et Bing |
| 3 | Réécrire les sections des 12 articles prioritaires : H2 en question + bloc réponse autonome de 40 à 80 mots en tête | Haute | 30 à 45 min par article (environ 8 h) | Passages directement extractibles par AI Overviews, ChatGPT et Perplexity |
| 4 | Lancer le plan de mentions externes (Google, Bing, Apple, LinkedIn unique, Malt, Sortlist, Clutch, La Fabrique du Net, crédits sur sites clients) | Haute | 2 à 3 jours répartis sur 8 semaines | Corroboration de l'entité ; ce sont ces sources que ChatGPT et Perplexity citent pour les requêtes « quelle agence » |
| 5 | Publier le nouveau llms.txt et des données propriétaires (baromètre des prix issu des projets, études de cas chiffrées) | Haute | llms.txt : 1 h ; baromètre : 2 à 3 jours | Contenu que seule Aurea Media peut fournir, donc citable en priorité |

---

## 2. Accessibilité aux robots IA

### 2.1 robots.txt actuel

```
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://aurea-media.fr/sitemap_index.xml

User-agent: *
Disallow: /wp-content/uploads/wpo/wpo-plugins-tables-list.json
```

Aucune règle spécifique aux robots IA : tous sont autorisés par défaut. Deux groupes `User-agent: *` coexistent (le second est ajouté par WP-Optimize). La norme RFC 9309 demande de les fusionner, mais certains analyseurs ne lisent que le premier groupe.

### 2.2 Statut par robot

Test du 25/09/2026 : requête sur /prix-site-vitrine-paris/ avec le user agent officiel de chaque robot. Toutes les réponses sont en 200 avec le contenu complet (environ 179 Ko). Un blocage éventuel par adresse IP au niveau du pare-feu de l'hébergeur (en-têtes `x-ws-ratelimit` présents) est **non vérifié** : seul un contrôle des journaux serveur le confirmera.

| Robot | robots.txt | Test HTTP | Ce qu'il gouverne réellement |
|---|---|---|---|
| Googlebot | Autorisé | 200 | Recherche Google, AI Overviews et AI Mode |
| Google-Extended | Autorisé (non cité) | sans objet (jeton robots.txt, pas un robot) | Entraînement et ancrage de Gemini et Vertex AI uniquement ; aucun effet sur Google Search ni AI Overviews |
| Bingbot | Autorisé | 200 | Bing et Copilot |
| OAI-SearchBot | Autorisé | 200 | Citations dans ChatGPT Search |
| ChatGPT-User | Autorisé | 200 | Visites déclenchées par un utilisateur de ChatGPT |
| GPTBot | Autorisé | 200 | Entraînement des modèles OpenAI uniquement ; ne dit rien de la citabilité dans ChatGPT Search |
| Claude-SearchBot | Autorisé | 200 | Citations dans la recherche de Claude |
| Claude-User | Autorisé | 200 | Visites déclenchées par un utilisateur de Claude |
| ClaudeBot | Autorisé | 200 | Entraînement des modèles Anthropic uniquement |
| PerplexityBot | Autorisé | 200 | Index et citations de Perplexity |
| Perplexity-User | Autorisé | 200 | Visites déclenchées par un utilisateur de Perplexity |
| Applebot | Autorisé | 200 | Siri, Spotlight, Safari |
| Applebot-Extended | Autorisé (non cité) | sans objet (jeton robots.txt) | Entraînement d'Apple Intelligence uniquement |
| CCBot | Autorisé | 200 | Common Crawl (entraînement de nombreux modèles) |
| MistralAI-User, Amazonbot, DuckAssistBot, Meta-ExternalAgent | Autorisés | 200 | Assistants secondaires |

### 2.3 Recommandations d'accès

**[Basse] B1. Fusionner les deux groupes du robots.txt**
- Constat : deux groupes `User-agent: *`.
- Pourquoi : lecture ambiguë par certains analyseurs ; aucun intérêt à conserver la règle WP-Optimize (le fichier JSON listé n'a pas de valeur).
- Action : dans Rank Math (Réglages généraux, Modifier robots.txt), publier la version ci-dessous. Garder tous les robots IA autorisés : pour une marque jeune, être présente dans les données d'entraînement (GPTBot, ClaudeBot, Google-Extended, Applebot-Extended, CCBot) aide les modèles à connaître la marque. Bloquer ces robots est un choix commercial possible mais non recommandé ici.
- Vérifier : `curl https://aurea-media.fr/robots.txt` affiche un seul groupe ; test robots.txt de la Search Console sans avertissement.

```
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php

Sitemap: https://aurea-media.fr/sitemap_index.xml
```

**[Moyenne] M7. Rétablir de vraies erreurs 404**
- Constat : toute URL inexistante est redirigée en 301 vers l'accueil. /llms-full.txt, /ai.txt, /.well-known/ai.txt et /en/ renvoient donc la page d'accueil.
- Pourquoi : un robot IA qui teste ces fichiers reçoit 245 Ko de HTML au lieu d'une absence claire ; les liens cassés vers le site sont attribués à l'accueil.
- Action : désactiver la redirection automatique des 404 dans Rank Math ; créer une page 404 utile (liens vers services, tarifs, conseils).
- Vérifier : `curl -I https://aurea-media.fr/cette-page-nexiste-pas/` renvoie 404.

**[Basse] B2. Licence RSL 1.0 absente**
- Constat : aucun fichier de licence RSL (/license.xml redirige vers l'accueil).
- Pourquoi : RSL sert à fixer des conditions d'usage payant par les IA. Pour un site dont l'objectif est d'être cité et de générer des devis, ce n'est pas un levier.
- Action : ne rien faire pour l'instant.

**[Basse] B4. Indexation Bing (Copilot et une partie de ChatGPT Search)**
- Constat : inscription Bing Webmaster Tools, Bing Places et IndexNow **non vérifiées**.
- Action : vérifier le site dans Bing Webmaster Tools, importer le sitemap, activer IndexNow via le module « Instant Indexing » de Rank Math.
- Vérifier : Bing Webmaster Tools affiche les URL indexées ; une URL publiée apparaît dans Bing sous 48 h.

---

## 3. llms.txt actuel : présent mais peu utile

Statut : **présent, malformé sur la forme, faible sur le fond**. Généré par Rank Math, 14,4 Ko, servi en `text/plain` avec `x-robots-tag: noindex, nofollow` (normal).

| Problème | Détail |
|---|---|
| Préambule avant le titre | La première ligne est « Generated by Rank Math SEO… » ; le format attend `# Nom` en première ligne |
| Pas de résumé | Aucun bloc `>` décrivant l'entreprise ; la seule présentation est en toute fin de fichier |
| Pages décrites par des slogans | Accueil, services, contact : « Devis gratuit · Réponse sous 24h ». Aucune donnée (prix, délais, cible) |
| Description erronée | L'article architecte reprend la description de l'article médecin (« être trouvé sur médecin Paris ») |
| Contenu daté | Le blog est décrit par « Le site vitrine en 2025 » |
| Faute visible | Catégorie « SEO (Référecement) » |
| Aucun fait d'entité | Pas de fondateur, adresse, SIREN, prix, délais, avis, zones |
| Catégories inutiles | Les pages catégories (sans H1, contenu mince) sont listées |

Le nouveau fichier complet est fourni en section 8.3.

---

## 4. Clarté de l'entité et cohérence entre pages

C'est le point le plus grave. Les moteurs IA croisent les pages entre elles et avec les sources externes. Quand un même fait a trois valeurs, ils en citent une au hasard, ou s'abstiennent.

### 4.1 Contradictions relevées

| Fait | Valeurs trouvées | Où | Valeur à retenir (à confirmer par le client) |
|---|---|---|---|
| Prix d'entrée site vitrine | 1 000 € HT ; 1 200 € HT ; 900 € HT (refonte) | 1 000 € : accueil, services, menu de 50 pages. 1 200 € : 13 articles (prix, avocat, médecin, kiné, restaurant, architecte, artisan, coach, Clamart, Boulogne, choisir agence, soi-même ou agence, anatomie). 900 € : Clamart, Boulogne, refonte | 1 000 € HT (formule Essentiel, 3 pages) ; prix de la refonte à publier sur /tarifs/ |
| Prix d'entrée e-commerce | 1 650 € HT ; 2 000 € HT ; 2 500 € HT | 1 650 € : services. 2 000 € : combien coûte un site e-commerce, catégorie. 2 500 € : créer un site e-commerce, Clamart, Boulogne | 1 650 € HT, formule Croissance dès 3 200 € HT |
| Fourchette du marché vitrine à Paris | 1 200 à 3 500 € HT ; 1 200 à 4 000 € HT | Deux valeurs dans le même article prix, et dans choisir agence | Une seule fourchette, datée et justifiée |
| Branding | « Sur devis » ; « à partir de 800 € HT » | Services contre articles identité visuelle | Publier un prix plancher ou retirer le chiffre des articles |
| Avis Google | 4,9/5 sur 39 avis ; 40 avis ; note 5 sur 40 avis dans le schéma | 39 : accueil et services. 40 : choisir agence, prix, Clamart, Boulogne. Note 5 : JSON-LD de Clamart et Boulogne | 4,9/5, nombre et date issus de la source unique |
| Délai site vitrine | 4 semaines ; 3 à 6 semaines ; jours 28 à 35 | Accueil et service contre article prix | 3 semaines (Essentiel), 4 semaines (Professionnel) |
| Délai e-commerce | 6 à 8 semaines ; 4 à 6 semaines | FAQ accueil contre créer un site e-commerce | À trancher |
| Maintenance | 79 €/mois ; 89 à 250 € HT/mois ; fourchette corrompue | Prix vitrine, choisir agence | Grille unique sur /tarifs/ |
| Volume de projets | 45+ projets (tous types) ; 45+ sites vitrines | Accueil contre page site vitrine | « Plus de 45 projets, dont N sites vitrines » |
| Titre du fondateur | Développeur web et marketing digital ; Fondateur et Directeur créatif (21 articles) ; Fondateur (9 articles) | Accueil contre schéma Person des articles | Un seul intitulé partout |
| Signature des articles | « Alexandre, Aurea Media Paris » contre « Alexandre Coury » | En-tête contre encadré auteur | Nom complet partout |
| Services proposés | 4 services sur le site ; le schéma de l'accueil ajoute « ghostwriting LinkedIn et Google My Business » ; un article affiche une gestion Google My Business de 89 à 189 € HT/mois | JSON-LD accueil, choisir agence | Soit créer ces offres, soit les retirer |
| International | Texte : Genève, Lisbonne, Montréal, Dubaï. Schéma : Suisse, Belgique. Aucune page en anglais (GTranslate traduit côté navigateur, sans URL ni hreflang) | Accueil, agence | Aligner, et créer une vraie page /en/ si l'international est un axe |
| Page LinkedIn entreprise | /company/aureamedia/ et /company/aurea-media | Schéma accueil contre schéma articles | Une seule URL (laquelle existe : **non vérifié**) |
| Logo de l'entité | Fichier de 2024 (27 pages) et fichier de 2026 (3 pages) | JSON-LD | Un seul fichier |
| Ancienneté | « depuis 2021 » partout ; entreprise individuelle, SIREN 992 693 473 | Mentions légales | Date d'immatriculation **non vérifiée** : un SIREN commençant par 99 correspond en général à une immatriculation récente. Si l'immatriculation est postérieure à 2021, écrire « activité exercée depuis 2021, entreprise individuelle immatriculée en 20XX » pour éviter une contradiction avec Pappers ou l'Annuaire des entreprises |
| Mentions légales | « Dernière mise à jour : mai 2025 » | Mentions légales | Date réelle |

### 4.2 Chiffres corrompus et calculs faux

Six fourchettes de prix sont inversées, sans doute après un rechercher remplacer global. Ce sont précisément les phrases qu'une IA extrait pour répondre à « combien coûte… ».

| Page | Texte affiché |
|---|---|
| /prix-site-vitrine-paris/ | hébergement propriétaire « de 80 à 45 €/mois » |
| /prix-site-vitrine-paris/ | modifications facturées « de 80 à 45 €/heure » |
| /prix-site-vitrine-paris/ | maintenance « entre 49 et 45 € HT/mois » |
| /choisir-agence-web-paris/ | hébergement « de 50 à 45 €/mois » |
| /choisir-agence-web-paris/ | modification facturée « de 80 à 45 € HT » |
| /creation-de-site-vitrine-paris/ | hébergement et domaine « entre 60 et 45 € par an » |

Autre erreur : le devis type de l'article prix annonce un total de 1 500 € HT alors que la somme des postes fait 1 395 € HT (45 + 280 + 380 + 220 + 220 + 80 + 120 + 50).

### 4.3 Recommandations

**[Critique] C1. Créer une source unique des faits**
- Constat : tableau 4.1.
- Pourquoi : les moteurs IA pondèrent la cohérence. Une entité dont le prix varie de 900 à 1 200 € selon la page est moins citée, et la citation peut être fausse (risque commercial et juridique sur un prix annoncé).
- Action :
  1. Rédiger une fiche entité interne (tableau ci-dessus, colonne de droite validée) et la tenir à jour.
  2. Créer /tarifs/ : grille complète (formules, délais, inclus, options, maintenance, refonte), date de mise à jour visible. Toutes les autres pages citent le prix plancher et renvoient vers /tarifs/.
  3. Dans le nouveau thème, afficher prix, avis et délais via un réglage unique (bloc réutilisable ou champ d'options) au lieu de chiffres saisis à la main dans chaque article.
  4. Corriger les 6 fourchettes et le devis type avant la refonte, sans attendre.
- Vérifier : `grep` sur l'export du site ou crawl Screaming Frog avec extraction personnalisée : une seule valeur pour « à partir de », « avis », « semaines » ; aucune fourchette dont la borne basse dépasse la borne haute.

**[Critique] C3. Données structurées : une seule entité, sans doublon**
- Constat :
  - L'organisation est déclarée environ 70 fois avec des variantes (deux identifiants #organization et #localbusiness, deux logos, deux LinkedIn, note 4,9 ou 5, 39 ou 40 avis).
  - Les articles portent à la fois un BlogPosting en JSON-LD et un BlogPosting en microdonnées, souvent avec un FAQPage en double.
  - 10 articles contiennent un schéma HowTo.
  - 2 blocs JSON-LD sont invalides (erreur de syntaxe) : /anatomie-site-vitrine-qui-convertit/ et /refonte-identite-visuelle-entreprise/.
  - `dateModified` égale `datePublished` sur 25 articles alors que `og:updated_time` indique des mises à jour postérieures (exemple : choisir agence, publié et « modifié » le 29/05, mis à jour le 16/09).
  - AggregateRating auto-attribué sur l'accueil, Clamart et Boulogne : Google l'ignore pour les avis sur sa propre entreprise.
- Pourquoi : Google et Bing reconstruisent l'entité à partir de ces nœuds ; des valeurs divergentes brouillent le Knowledge Graph (l'identifiant /g/11mcqnsjrx déclaré est un atout à protéger).
- Action dans la refonte : tout générer par Rank Math (Knowledge Graph et SEO local), supprimer les JSON-LD codés à la main dans les widgets HTML. Structure cible :
  - `ProfessionalService` unique, `@id` https://aurea-media.fr/#organization, avec name, url, logo, description (texte du bloc « en bref »), founder, foundingDate, address, telephone, email, openingHours, areaServed, sameAs (liste de la section 7), identifier SIREN.
  - `Person` unique, `@id` https://aurea-media.fr/alexandre-coury/#person, jobTitle identique partout, sameAs LinkedIn, Malt.
  - `BlogPosting` par article, author et publisher en référence par `@id`, dateModified réelle.
  - `Service` et `Offer` sur les pages services avec les prix de /tarifs/.
  - Supprimer HowTo et AggregateRating. Les questions réponses restent visibles en HTML (utiles aux IA) ; le balisage FAQPage n'apporte plus de résultat enrichi depuis le 7 mai 2026, ne pas le dupliquer.
- Vérifier : validateur schema.org sur 5 gabarits (accueil, service, article, page locale, à propos) : zéro erreur, un seul nœud Organization par page, mêmes valeurs partout.

**[Moyenne] M6. Nettoyer le code source**
- Constat : 81 commentaires HTML sur l'accueil, dont des notes internes (« ne PAS ajouter de schéma FAQ », « remplacez les # par vos vraies URL », explication sur les avis Google retirés).
- Pourquoi : bruit pour les analyseurs ; certains extracteurs simplifiés gardent ces textes, qui polluent le contexte.
- Action : nouveau thème sans widgets HTML collés ; aucun commentaire en production.
- Vérifier : `curl -s https://aurea-media.fr/ | grep -c '<!--'` proche de 0 (hors cache).

---

## 5. Citabilité passage par passage

### 5.1 Mesures sur les 33 articles

| Indicateur | Constat |
|---|---|
| Blocs de 40 à 80 mots | Nombreux (15 à 29 par guide long) : bonne base, la longueur n'est pas le problème |
| H2 formulés en question | Environ 20 sur 250 (8 %) |
| Premier bloc après un H2 | 35 à 49 mots en moyenne, mais c'est presque toujours une phrase de contexte (« Le marché parisien est fragmenté en 4 segments… »), pas une réponse |
| Bruit avant les H2 | Surtitres « 01 », « La checklist complète », « Transparence totale » : texte isolé sans valeur pour un extracteur |
| Encadré « Résumé en 30 secondes » | Présent sur les guides longs : excellent format, à généraliser |
| FAQ des pages services | Réponses directes, chiffrées, de 50 à 70 mots : les meilleurs passages du site |
| Tableaux | Segments du marché, devis poste par poste, comparatif CMS, fourchettes : très citables, mais certains sont des grilles de div et non de vrais tableaux HTML |
| Statistiques sourcées | Quelques liens (W3Techs, FEVAD, Baymard, Moz, CNIL, ordres professionnels) |
| Statistiques non sourcées | « +300 % de trafic organique moyen après 6 mois » (accueil, service vitrine), « plus de 2 000 agences web à Paris », « 60 % du trafic web est mobile », « double votre visibilité locale » |

Exemple de passage faible puis réécrit (article prix, section 01) :

> Actuel : « Le marché parisien de la création de sites vitrines est fragmenté en 4 segments très distincts. Chacun répond à des besoins différents, et les confondre est la principale source de déception et de gaspillage. »

> Réécrit (61 mots) : « Un site vitrine professionnel coûte entre 1 000 et 3 500 € HT à Paris en 2026 chez un freelance senior ou une petite agence, SEO technique inclus. Les plateformes comme Wix coûtent 10 à 30 € par mois sans propriété du code, et les grandes agences facturent 5 000 à 15 000 € HT. Chez Aurea Media, la formule Essentiel démarre à 1 000 € HT. »

### 5.2 Recommandations

**[Haute] H1. Une question par H2, une réponse en tête**
- Constat : 8 % de H2 en question, réponse différée.
- Pourquoi : AI Overviews, ChatGPT et Perplexity extraient un passage qui répond seul à la question posée ; la phrase qui suit le titre est la plus souvent reprise.
- Action : appliquer le modèle d'article (section 8.2) aux 12 articles prioritaires : prix vitrine, combien coûte un e-commerce, choisir agence, soi-même ou agence, Shopify contre WooCommerce, WordPress contre Webflow, refonte, avocat, médecin, artisan, restaurant, fiche Google. Supprimer les surtitres numérotés ou les passer en attribut décoratif (pseudo élément CSS).
- Vérifier : au moins 60 % des H2 en question ; chaque H2 suivi d'un paragraphe de 40 à 80 mots contenant un chiffre ou une définition.

**[Haute] H2. Sourcer ou retirer les chiffres**
- Constat : liste ci-dessus.
- Pourquoi : Perplexity et AI Overviews privilégient les chiffres attribués ; un chiffre promotionnel sans méthode (« +300 % ») peut aussi relever de la pratique commerciale trompeuse.
- Action : pour chaque chiffre, lien vers la source primaire et année. Pour les chiffres maison, publier la méthode (« moyenne sur N sites livrés entre 2022 et 2026, source Search Console ») ou supprimer.
- Vérifier : aucun pourcentage ou montant de marché sans source ou méthode dans le crawl de la nouvelle version.

**[Moyenne] M3. Doubler les simulateurs par du texte**
- Constat : plusieurs articles annoncent des simulateurs ou comparateurs (« Sélectionnez votre situation pour voir une estimation », comparateur avant après, simulateur de périmètre). La présence de tous les résultats dans le HTML initial est **non vérifiée**.
- Pourquoi : les robots IA n'exécutent généralement pas le JavaScript ; un résultat calculé à l'écran n'existe pas pour eux.
- Action : sous chaque simulateur, un vrai tableau HTML statique avec tous les cas.
- Vérifier : `curl` de la page et recherche des montants du simulateur dans le HTML brut.

### 5.3 Données propriétaires citables

Existant à valoriser : devis type poste par poste, trois formules vitrine (1 000 € / 1 800 € / sur devis), formules e-commerce (1 650 € / 3 200 € / sur devis), calendrier semaine par semaine, délais de réponse, branding en trois pistes et 1 à 2 semaines, maquettes livrées en une semaine.

**[Moyenne] M1. Transformer l'expérience des 45 projets en données**
- Action :
  1. Page /tarifs/ : tableau unique (formule, pages, délai, inclus, prix), date de mise à jour, lien depuis chaque page.
  2. Baromètre annuel « Prix et délais réels d'un site vitrine à Paris » construit sur les projets livrés : prix médian, nombre de pages moyen, délai réel moyen, part des retards dus aux contenus, part des projets bilingues. Publier la méthode et la date.
  3. Section « Ce que nous observons sur nos projets » dans chaque guide, avec 2 ou 3 chiffres maison.
- Vérifier : les requêtes de la section 8.4 renvoient une citation de /tarifs/ ou du baromètre dans Perplexity et ChatGPT (panel mensuel).

**[Moyenne] M2. Études de cas chiffrées**
- Constat : la page Réalisations liste des projets sans résultats mesurés et affiche 13 fois le caractère « ‡ » à la place d'une apostrophe.
- Action : une page par étude de cas (/realisations-agence-web/nom-du-projet/) avec client, secteur, ville, date, nombre de pages, délai, technologie, résultat chiffré (demandes, positions, trafic) et citation du client. Corriger l'encodage.
- Vérifier : chaque étude contient au moins 3 chiffres datés ; plus aucun « ‡ » dans le HTML.

---

## 6. Signaux de marque et mentions externes

### 6.1 État

| Source | Statut | Détail |
|---|---|---|
| Google Knowledge Graph | Présent (déclaré) | Identifiant /g/11mcqnsjrx dans le sameAs de l'accueil ; contenu de la fiche **non vérifié** |
| Google Business Profile | Présent probablement (4,9/5, 39 avis) | Cohérence NAP et catégories **non vérifiée** |
| LinkedIn entreprise | Deux URL différentes déclarées | /company/aureamedia/ et /company/aurea-media : laquelle existe **non vérifié** |
| LinkedIn fondateur | Déclaré | /in/alexandre-coury/ |
| Wikipedia | **Non vérifié** (absence probable) | Notoriété insuffisante pour un article : ne pas en créer |
| Wikidata | **Non vérifié** | Voir plan section 8.5 |
| Reddit | **Non vérifié** | Aucun lien depuis le site |
| YouTube | **Non vérifié** | Aucune chaîne liée ; c'est le signal le plus corrélé aux citations IA (environ 0,74) |
| Malt, Sortlist, Clutch, La Fabrique du Net, Codeur, Pages Jaunes | **Non vérifié** | Aucun lien depuis le site |
| Presse | **Non vérifié** | Aucune mention reliée depuis le site |
| Réseaux sociaux | Absents du pied de page | Le code contient la consigne « remplacez les # par vos vraies URL » : bloc social jamais rempli |
| Sites clients | 3 à 20 sites liés depuis le portfolio | Mention « site réalisé par Aurea Media » sur ces sites **non vérifiée** |

**[Haute] H3. Corroborer l'entité hors du site**
- Constat : un seul profil externe relié, et en double.
- Pourquoi : ChatGPT Search et Perplexity répondent à « quelle agence web choisir à Paris » surtout à partir de comparatifs, annuaires et forums. Une marque absente de ces pages n'est pas proposée, quelle que soit la qualité de son site. Seuls 11 % des domaines sont cités à la fois par ChatGPT et par AI Overviews : il faut couvrir les deux écosystèmes.
- Action : plan de la section 8.5.
- Vérifier : recherche `"Aurea Media" -site:aurea-media.fr` sur Google et Bing : au moins 15 pages tierces cohérentes sous 3 mois.

### 6.2 Liste de contrôle manuelle (non vérifiée ici)

1. Rechercher « Aurea Media » sur Wikidata, Reddit, YouTube, Malt, Sortlist, Clutch, Pages Jaunes, La Fabrique du Net.
2. Ouvrir les deux URL LinkedIn entreprise ; garder celle qui existe, rediriger ou supprimer l'autre.
3. Consulter la fiche SIREN 992 693 473 sur l'Annuaire des entreprises : date d'immatriculation, nom commercial, adresse.
4. Vérifier si le 60 rue François Ier est une adresse de domiciliation ; si oui, contrôler la conformité de la fiche Google Business Profile (zone desservie plutôt qu'adresse affichée si aucun accueil client).
5. Poser les 15 requêtes de la section 8.4 dans ChatGPT, Perplexity, Google AI Mode et Copilot ; noter les sources citées : ce sont les cibles prioritaires de mentions.

---

## 7. Fraîcheur et structure

**[Haute] H5. Dates fiables**
- Constat : articles publiés entre le 15 mai et le 31 août 2026 ; date visible « Mai 2026 » sans jour ; dateModified jamais mise à jour dans le schéma ; mentions légales datées de mai 2025 ; titres contenant « 2026 ».
- Pourquoi : les moteurs IA favorisent les contenus récents et datés précisément ; une date de modification figée ou incohérente est ignorée ou pénalisante.
- Action : afficher « Publié le » et « Mis à jour le » (date complète, balise `time`) ; laisser Rank Math générer dateModified ; révision trimestrielle des pages prix ; en janvier 2027, mise à jour réelle des guides datés avant de changer l'année dans les titres.
- Vérifier : dateModified du schéma égale à la date visible et à `og:updated_time` sur un échantillon de 10 articles.

**[Haute] H6. Pages locales et doublons**
- Constat : Clamart, Boulogne-Billancourt et Hauts-de-Seine sont publiées comme articles ; deux pages visent Clamart (/creation-site-internet-clamart/ et /agence-seo-clamart/) ; /branding-identite-visuelle/ (catégorie) affiche un contenu identique à /branding-identite/ ; trois articles se recoupent sur la fiche Google (générique, artisans TPE, coach) et deux sur logo et charte.
- Pourquoi : pour « agence web Boulogne-Billancourt », une IA cherche une page d'entité locale claire, pas un billet de blog ; les doublons dispersent les signaux.
- Action : convertir les pages locales en pages (même slug, gabarit « page locale » avec bloc en bref local, projets de la zone, accès, FAQ) ; fusionner les deux pages Clamart et les deux articles logo ; arbitrer les trois articles fiche Google avec l'audit SEO.
- Vérifier : une seule URL par intention dans le sitemap ; chaque page locale cite au moins un projet réel de la zone.

**[Moyenne] M5. Taxonomie**
- Constat : catégories sans H1, descriptions de 293 à 364 caractères, faute « Référecement », catégorie « Création de site web » presque vide.
- Action : 4 catégories maximum (Prix et budget, Choisir sa technologie, Site par métier, Visibilité locale), un H1 et une introduction de 60 mots chacune ; ne pas les inclure dans llms.txt.

**[Moyenne] M4. International**
- Constat : clients revendiqués à Genève, Lisbonne, Montréal et Dubaï ; GTranslate traduit en JavaScript sans URL indexable ; aucune balise hreflang.
- Action : soit une page /en/ rédigée (offre, prix, méthode, contact) liée par hreflang, soit retirer la promesse de sites bilingues des pages d'entité.

### Ce qu'il faut garder, changer, créer (angle GEO)

| Garder (URL et contenus qui ont de la valeur) | Changer | Créer |
|---|---|---|
| Toutes les URL de services, /agence-web-a-paris/, /realisations-agence-web/, /contact-agence/, /nos-conseils-web/ | Chiffres contradictoires et corrompus | /tarifs/ (source de vérité) |
| Les guides longs (prix vitrine, coût e-commerce, choisir agence, comparatifs, guides métiers) avec leurs slugs | H2 affirmatifs en H2 questions + bloc réponse | /alexandre-coury/ (page auteur, entité Person) |
| Encadrés « Résumé en 30 secondes », FAQ des services, devis poste par poste, tableaux | Pages locales publiées en articles : passer en pages, même slug | /methode/ (calendrier, livrables) |
| Identifiant Knowledge Graph, avis Google | Données structurées : une entité, un format, via Rank Math | /avis-clients/ |
| Liens vers sources officielles (CNIL, ordres, France Num, FEVAD) | llms.txt : fichier rédigé à la main | Baromètre des prix, études de cas chiffrées, /en/ si international |

---

## 8. Livrables pour la refonte

### 8.1 Bloc « Aurea Media en bref »

À placer sur /agence-web-a-paris/ (haut de page), en fin d'accueil, dans l'encadré auteur de chaque article (version courte) et comme description de l'organisation dans Rank Math. Mêmes mots partout, y compris sur LinkedIn, Malt, Google Business Profile et annuaires.

> **Aurea Media en bref.** Aurea Media est une agence web indépendante basée à Paris (8e), fondée en 2021 par Alexandre Coury, développeur web et spécialiste du marketing digital. Elle conçoit des sites vitrines WordPress sur mesure à partir de 1 000 € HT, des boutiques WooCommerce sans commission à partir de 1 650 € HT, des identités visuelles et des maquettes de présentation. Elle s'adresse aux indépendants, artisans, professions libérales, TPE et PME, en Île-de-France et à distance. Délai moyen : 4 semaines. Note Google : 4,9/5 (39 avis en septembre 2026).

83 mots. Avant publication : confirmer les prix (section 4.1), l'année de création face au registre (non vérifié) et mettre à jour la note et la date à chaque trimestre.

Version courte pour les annuaires (30 mots) :

> Agence web indépendante à Paris fondée en 2021 par Alexandre Coury. Sites vitrines WordPress dès 1 000 € HT, e-commerce WooCommerce dès 1 650 € HT, branding et maquettes. Livraison en 4 semaines.

### 8.2 Modèle d'article optimisé IA

Règles communes : un mot-clé principal ; title de 50 à 60 caractères au format Rank Math du client (mot-clé au début, proposition, marque) ; meta description de 140 à 160 caractères ; slug court ; un seul H1 ; score Rank Math visé de 80 à 90. Aucun tiret au milieu d'une phrase.

```markdown
# [H1 : la question principale, telle qu'un utilisateur la tape ou la dit]
     Exemple : Combien coûte un site vitrine à Paris en 2026 ?

Publié le 30 mai 2026 · Mis à jour le 25 septembre 2026 · Par Alexandre Coury, fondateur d'Aurea Media · Lecture : 9 min

[CHAPEAU : réponse directe en 40 à 60 mots. Elle doit se suffire à elle même :
sujet nommé, chiffre, contexte (lieu, année), nuance principale.]
     Exemple : Un site vitrine professionnel coûte entre 1 000 et 3 500 € HT à Paris
     en 2026 chez un freelance senior ou une petite agence, SEO technique inclus.
     Le prix dépend surtout du nombre de pages, de la rédaction et des
     fonctionnalités. En dessous de 800 €, il s'agit presque toujours d'un modèle
     préfabriqué.

## En bref
- [Fait chiffré 1, avec source ou « donnée Aurea Media »]
- [Fait chiffré 2]
- [Fait chiffré 3]
- [Recommandation principale en une phrase]

## [H2 question 1 : sous-question la plus fréquente]
[BLOC RÉPONSE : 40 à 80 mots, autonome. Reprendre le sujet en toutes lettres
(jamais « il » ou « ce dernier » en début de bloc), donner le chiffre ou la
définition dans la première phrase, citer la source.]

[Développement : explications, exemples, liste à puces ou étapes numérotées.]

| Critère | Option A | Option B | Option C |
|---|---|---|---|
| [vrai tableau HTML, jamais une grille de div] | | | |

## [H2 question 2 : « Qu'est-ce que … ? » : définition]
[DÉFINITION en une phrase : « Un X est un Y qui Z. » puis 2 ou 3 phrases de précision.]

## [H2 question 3 : « Combien de temps … ? » ou « Combien coûte … ? »]
[BLOC RÉPONSE chiffré + tableau.]

## Ce que nous observons sur nos projets
[2 ou 3 données propriétaires avec méthode : « Sur 45 projets livrés entre 2021
et 2026, le délai réel médian est de N semaines ; N % des retards viennent de la
fourniture des contenus. » Lien vers une étude de cas.]

## [H2 question 4 : erreurs à éviter / comment choisir]
[BLOC RÉPONSE + liste.]

## Questions fréquentes
### [Question longue traîne 1 ?]
[Réponse de 40 à 60 mots, autonome.]
### [Question 2 ?]
### [Question 3 ?]
(Texte visible uniquement pour l'utilisateur et les IA. Pas de balisage HowTo.
Pas de FAQPage ajouté pour un gain SERP : ces résultats enrichis ne s'affichent
plus depuis le 7 mai 2026.)

## Sources
1. [Organisme, titre, année, lien]
2. [...]

---
[ENCADRÉ AUTEUR : photo, « Alexandre Coury, fondateur d'Aurea Media, développeur
web et spécialiste du marketing digital », version courte du bloc en bref,
liens vers /alexandre-coury/ et LinkedIn.]

[APPEL À L'ACTION : lien vers le service lié et /tarifs/.]
```

Réglages techniques du gabarit :
- Schéma BlogPosting généré par Rank Math uniquement ; `author` et `publisher` en référence par `@id` ; dateModified automatique.
- Date visible en balise `<time datetime="AAAA-MM-JJ">`.
- Sommaire en liens d'ancre vers chaque H2.
- Pas de surtitre décoratif en texte réel avant les H2.
- Tableaux en `<table>` avec `<caption>` et `<th>`.
- Images avec légende qui décrit la donnée montrée (une légende est un passage citable).
- Longueur : chaque H2 doit pouvoir être lu seul ; viser 134 à 167 mots par section courte si la section n'a qu'un bloc, 40 à 80 mots pour le bloc réponse d'ouverture.

Exemple de balises pour l'article prix, au format du client :
- Title (60 caractères) : `Prix site vitrine Paris | Grille et devis type – Aurea Media`
- Meta description (150 caractères) : `Prix d'un site vitrine à Paris en 2027 : grille par formule, devis type poste par poste et délais réels. Données issues de 45 projets. Devis sous 24h.`

### 8.3 Nouveau llms.txt

À servir comme fichier statique à la racine (/llms.txt), module llms.txt de Rank Math désactivé pour éviter toute régénération automatique. À mettre à jour à chaque nouvelle page ou changement de prix. Les URL marquées « nouvelle page » sont à créer pendant la refonte ; retirer leur ligne si la page n'est pas créée. Valeurs à confirmer avant mise en ligne : prix, délai e-commerce, date de création.

```markdown
# Aurea Media

> Aurea Media est une agence web indépendante basée à Paris (75008), fondée en 2021 par Alexandre Coury, développeur web et spécialiste du marketing digital. Elle crée des sites vitrines WordPress sur mesure (dès 1 000 € HT), des boutiques WooCommerce sans commission (dès 1 650 € HT), des identités visuelles et des maquettes de présentation pour indépendants, artisans, professions libérales, TPE et PME.

Informations clés, mises à jour le 1er octobre 2026 :

- Forme juridique : entreprise individuelle, SIREN 992 693 473
- Fondateur et interlocuteur unique : Alexandre Coury ; aucune sous-traitance
- Adresse : 60 rue François Ier, 75008 Paris, France
- Contact : +33 6 51 28 77 61, alexandre.coury@aurea-media.fr, du lundi au vendredi de 9 h à 19 h
- Zone d'intervention : Paris et Île-de-France (rendez-vous possibles), France entière et international en visioconférence
- Technologies : WordPress pour les sites vitrines, WooCommerce pour les boutiques ; développement sur mesure, sans thème acheté
- Délais moyens : 3 semaines (site 3 pages), 4 semaines (site 5 à 7 pages), 6 à 8 semaines (e-commerce), 1 à 2 semaines (identité visuelle), 1 semaine (maquettes de présentation)
- Inclus dans chaque site : SEO technique, données structurées, formulaire RGPD, formation à la prise en main ; le client est propriétaire du domaine, de l'hébergement et des fichiers
- Réponse aux demandes de devis : sous 24 h ouvrées, devis détaillé ligne par ligne
- Références : plus de 45 projets livrés depuis 2021 ; note Google 4,9/5 sur 39 avis (septembre 2026)

## Services

- [Création de site vitrine à Paris](https://aurea-media.fr/creation-de-site-vitrine-paris/): sites WordPress sur mesure pour indépendants, artisans, professions libérales, TPE et PME. Formule Essentiel (3 pages, dès 1 000 € HT, 3 semaines), Professionnel (5 à 7 pages avec blog, dès 1 800 € HT, 4 semaines), Premium (8 pages et plus, bilingue possible, sur devis).
- [Création de site e-commerce à Paris](https://aurea-media.fr/creation-de-site-e-commerce-paris/): boutiques WooCommerce sans commission sur les ventes ni abonnement imposé. Dès 1 650 € HT ; formule Croissance dès 3 200 € HT (catalogue illimité, Google Shopping, relance des paniers abandonnés) ; projets B2B, multidevises ou connectés à un ERP sur devis.
- [Branding et identité visuelle](https://aurea-media.fr/branding-identite/): logo, charte graphique et direction artistique ; trois pistes créatives argumentées, retouches illimitées sur la piste choisie, fichiers sources et cession de droits écrite ; livraison en 1 à 2 semaines ; sur devis.
- [Maquettes de présentation](https://aurea-media.fr/maquettes-de-presentation/): pitch decks, propositions commerciales et rapports sur mesure ; fichiers 100 % éditables livrés en une semaine ; sur devis.
- [Tarifs](https://aurea-media.fr/tarifs/): nouvelle page. Grille complète des formules, devis type poste par poste, options, maintenance et refonte, date de mise à jour.
- [Méthode et délais](https://aurea-media.fr/methode/): nouvelle page. Les 4 étapes d'un projet semaine par semaine, ce que le client fournit, livrables et garanties.

## L'agence

- [Aurea Media, agence web indépendante à Paris](https://aurea-media.fr/agence-web-a-paris/): histoire, fonctionnement sans sous-traitance, engagements, zone d'intervention, questions fréquentes sur l'agence.
- [Alexandre Coury, fondateur](https://aurea-media.fr/alexandre-coury/): nouvelle page. Parcours, compétences, projets, publications et profils externes.
- [Réalisations](https://aurea-media.fr/realisations-agence-web/): plus de 45 projets (industrie, BTP, événementiel, santé, droit, commerce) avec liens vers les sites en production et études de cas chiffrées.
- [Avis clients](https://aurea-media.fr/avis-clients/): nouvelle page. Avis Google vérifiés, témoignages détaillés, lien vers la fiche Google.
- [Contact et devis](https://aurea-media.fr/contact-agence/): formulaire, téléphone, e-mail ; devis gratuit sous 24 h ouvrées.

## Agence web près de chez vous

- [Agence web dans les Hauts-de-Seine](https://aurea-media.fr/agence-web-hauts-de-seine/): accompagnement des entreprises de Boulogne-Billancourt, Clamart, Issy-les-Moulineaux et environs.
- [Création de site internet à Boulogne-Billancourt](https://aurea-media.fr/site-internet-boulogne-billancourt/): offre, prix et projets pour les entreprises de Boulogne-Billancourt.
- [Création de site internet à Clamart](https://aurea-media.fr/creation-site-internet-clamart/): offre, prix et projets pour les entreprises de Clamart.

## Guides : prix et choix d'un prestataire

- [Combien coûte un site vitrine à Paris ?](https://aurea-media.fr/prix-site-vitrine-paris/): fourchettes du marché parisien par type de prestataire, devis type poste par poste, délais, pièges qui font grimper le budget.
- [Combien coûte un site e-commerce ?](https://aurea-media.fr/combien-coute-un-site-e-commerce/): budgets par profil de boutique, coûts récurrents, comparaison des plateformes.
- [Baromètre des prix d'un site vitrine à Paris](https://aurea-media.fr/barometre-prix-site-vitrine-paris/): nouvelle page. Prix médian, délais réels et contenu moyen mesurés sur les projets Aurea Media, méthode publiée.
- [Comment choisir son agence web à Paris ?](https://aurea-media.fr/choisir-agence-web-paris/): 7 critères de choix, signaux d'alerte, 5 questions à poser avant de signer.
- [Créer son site vitrine soi-même, avec un freelance ou une agence ?](https://aurea-media.fr/creer-un-site-vitrine-soi-meme-ou-en-agence/): coût réel sur 3 ans de chaque option et profil conseillé.
- [Site vitrine pour TPE à Paris : quel budget prévoir ?](https://aurea-media.fr/creation-site-vitrine-tpe-paris/): besoins et budget d'une très petite entreprise.

## Guides : technologie, e-commerce et refonte

- [Shopify ou WooCommerce ?](https://aurea-media.fr/shopify-vs-woocommerce/): comparatif des coûts, commissions, SEO et autonomie pour une boutique en France.
- [WordPress ou Webflow ?](https://aurea-media.fr/wordpress-vs-webflow/): comparatif pour un site professionnel : propriété, SEO, coût sur 3 ans.
- [Créer un site e-commerce professionnel](https://aurea-media.fr/creer-un-site-e-commerce-professionnel/): étapes, fonctionnalités indispensables et obligations légales.
- [Refonte de site internet](https://aurea-media.fr/refonte-site-internet/): signaux qui imposent une refonte, étapes pour conserver son référencement, budgets.

## Guides : site vitrine par métier

- [Site vitrine pour artisan](https://aurea-media.fr/site-vitrine-pour-artisan/): éléments indispensables pour transformer les visites en demandes de devis.
- [Site vitrine pour artisan rénovateur](https://aurea-media.fr/site-vitrine-artisan-renovateur-en-batiment/): cas réel d'une entreprise de rénovation en Île-de-France.
- [Site vitrine pour coach ou consultant](https://aurea-media.fr/site-vitrine-coach-consultant/): pages clés, preuves et prise de rendez-vous.
- [Site vitrine pour avocat](https://aurea-media.fr/site-vitrine-avocat/): pages indispensables et règles déontologiques du Conseil national des barreaux.
- [Site vitrine pour médecin](https://aurea-media.fr/site-vitrine-medecin/): pages essentielles, RGPD santé, prise de rendez-vous.
- [Site vitrine pour kinésithérapeute](https://aurea-media.fr/site-vitrine-kinesitherapeute/): structure par spécialité et règles de l'Ordre.
- [Site vitrine pour architecte](https://aurea-media.fr/site-vitrine-architecte/): portfolio, présentation des projets et règles de l'Ordre des architectes.
- [Site vitrine pour restaurant](https://aurea-media.fr/site-vitrine-restaurant/): menu, réservation et visibilité locale.
- [Anatomie d'un site vitrine qui convertit](https://aurea-media.fr/anatomie-site-vitrine-qui-convertit/): plan page par page et zone par zone.
- [Pourquoi un site vitrine ne génère pas de clients](https://aurea-media.fr/site-vitrine-qui-ne-genere-pas-de-clients/): 8 causes et corrections.

## Guides : Google Business Profile et avis

- [Optimiser sa fiche Google Business Profile](https://aurea-media.fr/optimiser-sa-fiche-google-my-business/): création, catégories, photos, publications et suivi.
- [Fiche Google pour coach ou consultant](https://aurea-media.fr/optimiser-fiche-google-my-business-coach/): zone desservie sans adresse publique, catégories, avis.
- [Avis Google](https://aurea-media.fr/avis-google/): obtenir plus d'avis, répondre aux avis négatifs, améliorer sa note.

## Guides : identité visuelle

- [Logo, charte graphique, identité visuelle : quelles différences ?](https://aurea-media.fr/logo-charte-graphique-identite-visuelle/): définitions, livrables et budgets.
- [Refonte d'identité visuelle](https://aurea-media.fr/refonte-identite-visuelle-entreprise/): signaux d'alerte, étapes, budget.

## Optional

- [Tous les conseils web](https://aurea-media.fr/nos-conseils-web/): liste complète des articles.
- [Mentions légales](https://aurea-media.fr/mentions-legales/)
- [Politique de confidentialité](https://aurea-media.fr/politique-de-confidentialite/)
- [Plan du site](https://aurea-media.fr/plan-du-site-aurea-media-paris/)
- [Sitemap XML](https://aurea-media.fr/sitemap_index.xml)
```

Vérifier : `curl -s https://aurea-media.fr/llms.txt | head -3` commence par `# Aurea Media` ; toutes les URL du fichier répondent en 200 (script de contrôle mensuel) ; aucune description ne contient un prix différent de /tarifs/.

### 8.4 Quinze requêtes conversationnelles cibles

Chaque page cible doit contenir, sous un H2 formulé comme la requête, un bloc réponse de 40 à 80 mots qui nomme Aurea Media, donne un chiffre et renvoie vers /tarifs/ ou un projet.

| # | Requête | Intention | Page qui doit répondre | Élément de réponse à publier |
|---|---|---|---|---|
| 1 | Quelle agence web choisir à Paris pour un site vitrine à moins de 2 000 euros ? | Transactionnelle | /creation-de-site-vitrine-paris/ (appui /tarifs/) | Formules à 1 000 € et 1 800 € HT, ce qui est inclus, délai, avis |
| 2 | Combien coûte un site vitrine pour une petite entreprise à Paris en 2026 ? | Information prix | /prix-site-vitrine-paris/ | Fourchette unique du marché, devis type juste, baromètre |
| 3 | Combien coûte une boutique WooCommerce pour une TPE ? | Information prix | /combien-coute-un-site-e-commerce/ | Budgets par profil, coûts récurrents, prix plancher 1 650 € HT |
| 4 | Quelle agence peut créer une boutique en ligne sans commission sur les ventes ? | Transactionnelle | /creation-de-site-e-commerce-paris/ | Comparaison commissions Shopify contre WooCommerce, offre Aurea |
| 5 | Vaut-il mieux un freelance ou une agence web pour créer son site ? | Comparaison | /creer-un-site-vitrine-soi-meme-ou-en-agence/ | Tableau coût sur 3 ans des trois options |
| 6 | Quels critères pour choisir une agence web à Paris ? | Information | /choisir-agence-web-paris/ | 7 critères en liste, 5 questions à poser |
| 7 | Shopify ou WooCommerce pour une boutique en France, lequel coûte le moins cher sur 3 ans ? | Comparaison | /shopify-vs-woocommerce/ | Tableau de coût total sur 3 ans, chiffres datés et sourcés |
| 8 | Combien de temps faut-il pour créer un site vitrine professionnel ? | Information | /methode/ (nouvelle page) | Calendrier semaine par semaine, délai médian réel |
| 9 | Quelle agence web pour une TPE à Boulogne-Billancourt ? | Locale | /site-internet-boulogne-billancourt/ | Bloc en bref local, projets dans le 92, prix, rendez-vous possibles |
| 10 | Comment refaire son site internet sans perdre son référencement ? | Information | /refonte-site-internet/ | Étapes (inventaire des URL, redirections 301, contrôle), prix d'une refonte |
| 11 | Quelle agence web recommander pour un cabinet d'avocat à Paris ? | Métier | /site-vitrine-avocat/ | Pages indispensables, règles du CNB, prix, exemple réel |
| 12 | Combien coûte un logo avec une charte graphique pour une PME ? | Information prix | /branding-identite/ (appui /logo-charte-graphique-identite-visuelle/) | Prix plancher publié, 3 pistes, délai 1 à 2 semaines |
| 13 | Qui peut créer un pitch deck professionnel en une semaine à Paris ? | Transactionnelle | /maquettes-de-presentation/ | Délai d'une semaine, formats livrés, exemples |
| 14 | Aurea Media est-elle une agence web sérieuse ? Quels sont les avis ? | Marque | /avis-clients/ (nouvelle page) | Note, nombre d'avis, date, extraits, lien vers la fiche Google, études de cas |
| 15 | Qui est Alexandre Coury, le fondateur d'Aurea Media ? | Entité | /alexandre-coury/ (nouvelle page) | Bloc biographique de 60 à 90 mots, parcours, profils externes |

Vérifier : panel mensuel des 15 requêtes dans ChatGPT, Perplexity, Google AI Mode et Copilot (tableau : cité oui ou non, URL citée, concurrents cités) ; dans GA4, suivi des visites référentes de chatgpt.com, perplexity.ai, copilot.microsoft.com et gemini.google.com.

### 8.5 Plan de mentions externes

Règle commune : mêmes nom, adresse, téléphone, site, description (bloc en bref version courte), prix plancher et catégories partout. Chaque profil créé est ajouté au `sameAs` de l'organisation dans Rank Math.

| Priorité | Plateforme | Action | Effort | Pourquoi | Vérification |
|---|---|---|---|---|---|
| Critique | Google Business Profile | Aligner nom, catégories (Concepteur de sites Web, Agence de marketing), services avec prix, description, lien /tarifs/ ; publier un post par mois ; répondre à tous les avis | 2 h puis 30 min par mois | Source principale de Google pour les requêtes locales et AI Mode | Fiche identique au bloc en bref ; avis en hausse régulière |
| Critique | LinkedIn entreprise | Garder une seule page, renseigner description, site, adresse ; supprimer ou rediriger l'autre URL | 1 h | Deux URL dans le schéma brouillent l'entité | Une seule URL LinkedIn dans tout le site |
| Haute | Bing Places + Bing Webmaster Tools | Importer la fiche Google ; vérifier le site ; activer IndexNow | 1 h | Copilot s'appuie sur Bing | Fiche Bing active ; URL indexées dans Bing |
| Haute | Apple Business Connect | Créer la fiche | 30 min | Siri, Plans, Spotlight | Fiche visible dans Plans |
| Haute | Crédits sur les sites clients | Ajouter « Site réalisé par Aurea Media » en pied de page des sites livrés (avec accord) | 2 h pour 20 sites | Mentions contextualisées nombreuses, faciles à obtenir | Recherche des liens entrants dans la Search Console |
| Haute | Malt (profil Alexandre Coury) | Profil complet, portfolio, prix, demande d'avis aux clients existants | 2 h | Source fréquente pour les requêtes « freelance ou agence » | Profil publié avec au moins 5 avis |
| Haute | Sortlist | Profil agence, projets, avis | 2 h | Cité dans les comparatifs « meilleures agences web Paris » | Profil publié, avis |
| Haute | La Fabrique du Net | Fiche agence, projets | 1 h 30 | Annuaire français souvent repris par les réponses IA en France | Fiche publiée |
| Moyenne | Clutch | Profil agence et avis vérifiés (entretiens clients) | 3 h | Très cité par ChatGPT et Perplexity pour « top agences » | 3 avis vérifiés |
| Moyenne | Codeur.com, Pages Jaunes, annuaire CCI | Fiches cohérentes | 2 h | Corroboration NAP en France | Fiches publiées |
| Moyenne | France Num (activateurs) | Candidater au réseau des activateurs France Num si l'éligibilité est confirmée (non vérifié) | 2 h | Domaine public de référence déjà cité dans les articles | Fiche d'activateur publiée |
| Moyenne | YouTube | Chaîne avec 6 à 10 vidéos courtes : prix d'un site vitrine, WordPress contre Webflow, visite commentée d'une étude de cas ; description avec lien et bloc en bref | 3 à 4 jours | Signal le plus corrélé aux citations IA (environ 0,74) | Vidéos indexées, liées depuis les articles correspondants |
| Moyenne | Reddit et forums francophones d'entrepreneurs | Réponses utiles et signées, sans autopromotion, sur les questions de prix et de choix de prestataire | 1 h par semaine | Perplexity et ChatGPT citent beaucoup ces discussions | Réponses conservées, mentions naturelles |
| Moyenne | Presse spécialisée et locale | Proposer le baromètre des prix à des médias TPE PME (JDN, Maddyness, Les Echos Entrepreneurs) et à la presse locale des Hauts-de-Seine | 2 jours | Mentions éditoriales, source de citations et de liens | 2 articles publiés en 6 mois |
| Basse | Wikidata | Créer l'élément seulement après 2 ou 3 sources tierces indépendantes : instance d'entreprise, pays, siège, date de création, fondateur, site officiel, SIREN, LinkedIn, identifiant Knowledge Graph | 1 h | Consolide l'entité ; un élément sans sources risque la suppression | Élément conservé après 30 jours |
| Ne pas faire | Wikipedia | Pas de page : notoriété insuffisante, suppression quasi certaine | | | |

Ordre conseillé : semaines 1 et 2, fiches Google, Bing, Apple, LinkedIn ; semaines 3 à 5, Malt, Sortlist, La Fabrique du Net, crédits clients ; semaines 6 à 8, Clutch, YouTube, baromètre et presse ; Wikidata ensuite.

---

## 9. Récapitulatif des recommandations

| ID | Priorité | Recommandation | Effort |
|---|---|---|---|
| C1 | Critique | Source unique des faits, page /tarifs/, correction des contradictions et des 6 fourchettes corrompues | 1 à 2 jours |
| C3 | Critique | Données structurées unifiées dans Rank Math, suppression HowTo, doublons, AggregateRating, JSON-LD invalides | 1 jour |
| H1 | Haute | H2 en questions et blocs réponse sur 12 articles | 8 h |
| H2 | Haute | Sourcer ou retirer les chiffres non sourcés | 3 h |
| H3 | Haute | Plan de mentions externes | 2 à 3 jours sur 8 semaines |
| H4 | Haute | Nouveau llms.txt statique | 1 h |
| H5 | Haute | Dates visibles et dateModified fiables | 2 h dans le gabarit |
| H6 | Haute | Pages locales en pages, fusion des doublons | 1 jour |
| M1 | Moyenne | Baromètre et données propriétaires | 2 à 3 jours |
| M2 | Moyenne | Études de cas chiffrées, correction de l'encodage | 1 jour pour 5 études |
| M3 | Moyenne | Tableaux statiques sous les simulateurs | 2 h |
| M4 | Moyenne | Page /en/ ou retrait de la promesse internationale | 1 jour |
| M5 | Moyenne | Taxonomie simplifiée | 2 h |
| M6 | Moyenne | Code source sans commentaires internes | inclus dans la refonte |
| M7 | Moyenne | Vraies erreurs 404 | 30 min |
| B1 | Basse | robots.txt fusionné | 10 min |
| B2 | Basse | RSL : ne rien faire | aucun |
| B4 | Basse | Bing Webmaster Tools et IndexNow | 1 h |

## Note méthodologique pour les autres volets de l'audit

Le champ `schema` de crawl/summary.json ne détecte que le JSON-LD compact : il manque tous les blocs indentés. En réalité, les pages services portent un JSON-LD Service et Offer, l'agence un AboutPage, le contact un ContactPage, et 30 articles un BlogPosting en JSON-LD (souvent doublé en microdonnées). L'affirmation « les pages services n'ont aucun schéma » du fichier CONTEXTE.md est donc à corriger.

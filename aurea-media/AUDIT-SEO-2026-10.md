# Audit SEO aurea-media.fr : octobre 2026

Audit réalisé le 25 octobre 2026 sur les 42 URL des sitemaps (9 pages, 33 articles), avec rendu Chromium pour les mesures de vitesse et le design.

Limites : pas d'accès à la Search Console, à GA4 ni à un outil de volumes de mots clés. Les causes de la baisse de trafic ci dessous sont donc des hypothèses solides tirées du site, à confirmer avec la Search Console (onglet Performances, comparaison sur 16 mois, et rapport Pages).

## Score de santé SEO : 62 / 100

| Catégorie | Poids | Note |
|---|---|---|
| Technique | 22 % | 72 |
| Qualité du contenu | 23 % | 50 |
| On page | 20 % | 65 |
| Données structurées | 10 % | 55 |
| Performance | 10 % | 65 |
| Visibilité IA | 10 % | 60 |
| Images | 5 % | 80 |

Les bases techniques sont propres (index, canonicals, titres, metas, un seul H1 partout). Le problème est **stratégique** : architecture, ciblage et modèle de contenu.

## 1. Pourquoi le trafic s'est effondré (hypothèses par ordre de probabilité)

### 1.1 Contenu produit en série sur un domaine jeune (critique)

* 33 articles publiés entre juin et septembre 2026, presque tous entre 2 000 et 3 300 mots.
* Même gabarit partout : titre « … en 2026 : le guide essentiel / complet », H2 identiques, FAQ et HowTo sur presque chaque article.
* Les dates de modification de 15 pages tombent le même jour (16 septembre 2026), signe de retouches en masse.

Sur un domaine à faible autorité, ce schéma ressemble à ce que Google classe comme contenu à grande échelle sans valeur ajoutée propre. Après une mise à jour majeure, tout le site peut perdre en confiance, pas seulement les articles.

**Vérification** : si la chute dans la Search Console coïncide avec une date de core update et touche aussi la home et les pages services, l'hypothèse est confirmée.

### 1.2 Cannibalisation massive sur « Paris » (critique)

Plusieurs pages se battent pour la même requête, donc aucune ne gagne :

| Requête visée | Pages en concurrence |
|---|---|
| agence web Paris | home, /agence-web-a-paris/, /choisir-agence-web-paris/, /realisations-agence-web/, /nos-conseils-web/, /contact-agence/ (toutes ont « Agence Web Paris » dans le title) |
| création site internet Paris | home, /nos-services/ |
| site vitrine Paris | /creation-de-site-vitrine-paris/, /prix-site-vitrine-paris/, /creation-site-vitrine-tpe-paris/ |
| Boulogne-Billancourt | /site-internet-boulogne-billancourt/, /creation-de-site-e-commerce-boulogne/, /agence-web-collectivites-boulogne-billancourt/ |
| Clamart | /creation-site-internet-clamart/, /agence-seo-clamart/ |
| logo et charte | /branding-identite/, /logo-et-charte-graphique/, /logo-charte-graphique-identite-visuelle/, /refonte-identite-visuelle-entreprise/ |
| Google Business Profile | /optimiser-sa-fiche-google-my-business/, /google-my-business-artisans-tpe/, /optimiser-fiche-google-my-business-coach/ |

### 1.3 Mauvaise intention de recherche (élevé)

* « site vitrine avocat », « site internet Boulogne-Billancourt », « création site e-commerce Boulogne » sont des requêtes **commerciales** : l'internaute cherche un prestataire. Elles sont servies par des **articles de blog** (BlogPosting), alors que Google affiche des pages de service sur ces requêtes.
* « Paris » est l'une des requêtes les plus concurrentielles de France pour une agence. Tout miser dessus avec un domaine récent, c'est se battre contre des agences installées depuis 10 ans.

### 1.4 Signaux de confiance incohérents (moyen)

* Adresse au 60 rue François 1er (75008), connue comme adresse de domiciliation. Correct pour le schema, mais faible pour le pack local.
* AggregateRating auto déclaré sur sa propre organisation : Google ignore ces avis « auto proclamés » pour une entreprise locale.
* 39 avis dans le schema, 41 affichés sur la home.
* Le schema mentionne « ghostwriting LinkedIn » et « Google My Business », qui ne figurent pas dans l'offre affichée. Le motion design, lui, est absent du site.

## 2. Constats techniques

| Priorité | Constat | Correctif |
|---|---|---|
| Élevée | Elementor + Royal Elementor Kit : 20 JS, 21 CSS, ~1 200 nœuds DOM, 1,5 à 2,1 Mo sur la home | Thème sur mesure sans constructeur (prévu dans la refonte) |
| Élevée | LCP mobile mesuré à 2,9 s **sans bridage réseau** : en 4G réelle, probablement au dessus du seuil de 2,5 s | Hero en HTML et CSS, image LCP préchargée, polices auto hébergées |
| Moyenne | CLS desktop 0,10 sur la home (limite du « bon ») | Dimensions fixes sur images et bandeau logos |
| Moyenne | H1 sans espaces dans le code : « site internetà Clamart », « unsite vitrine quiconvertit » | Ne pas couper les mots avec des retours à la ligne entre balises |
| Moyenne | Les 42 pages pointent vers /branding-identite-visuelle/ qui redirige (301) | Lien direct vers l'URL finale |
| Basse | Schema HowTo sur 12 articles (abandonné par Google depuis 2023) | Retirer |
| Info | FAQPage sur presque tous les articles : plus de résultat enrichi depuis mai 2026 | Garder les FAQ utiles en contenu, pas de nouveau balisage FAQ pour Google |
| Basse | GTranslate : traduction côté client, aucune page indexable dans une autre langue | Sans effet SEO, à remplacer seulement si une version EN devient stratégique |

Points positifs : HTTPS, redirection http vers https, robots.txt correct, sitemaps Rank Math, canonicals justes, index/follow partout, ALT présents, titres et metas aux bonnes longueurs.

## 3. Nouvelle stratégie de ciblage

Principe : **le national pour le volume, l'Île de France pour gagner vite, les secteurs pour convertir.** Une requête = une page.

### 3.1 Architecture cible

```
/                                    Agence web sur mesure (marque + création site internet)
│
├── Services (national, sans ville dans l'URL)
│   ├── /creation-site-vitrine/           ★ service principal
│   ├── /creation-site-e-commerce/
│   ├── /refonte-site-internet/           (article actuel transformé en page service)
│   ├── /logo-identite-visuelle/
│   ├── /motion-design/                   (nouveau)
│   └── /maquettes-presentation/
│
├── Secteurs (landing pages commerciales, schema Service)
│   ├── /site-internet-avocat/
│   ├── /site-internet-professions-sante/  (fusion médecin + kiné)
│   ├── /site-internet-architecte/
│   ├── /site-internet-restaurant/
│   ├── /site-internet-artisan-btp/        (fusion artisan + artisan rénovateur)
│   └── /site-internet-coach-consultant/
│
├── Local Île de France (4 pages maximum, contenu réellement local)
│   ├── /agence-web-paris/                 (fusion agence-web-a-paris + choisir-agence-web-paris)
│   ├── /agence-web-hauts-de-seine/
│   ├── /agence-web-boulogne-billancourt/  (fusion des 3 pages Boulogne)
│   └── /agence-web-clamart/               (fusion des 2 pages Clamart)
│
├── /realisations/  +  /realisations/[projet]/   (études de cas, schema CreativeWork)
├── /tarifs/                                     (nouveau : prix affichés, fort potentiel de clic)
├── /agence/                                     (fondateur, méthode, E-E-A-T)
├── /contact/
└── /conseils/  (blog informationnel uniquement)
```

### 3.2 Mot clé par page (format Rank Math)

| Page | Focus keyword | Secondaires |
|---|---|---|
| Home | agence web sur mesure | création site internet, agence création site web |
| /creation-site-vitrine/ | création site vitrine | site vitrine sur mesure, site vitrine WordPress, site vitrine professionnel |
| /creation-site-e-commerce/ | création site e-commerce | boutique en ligne sur mesure, site WooCommerce |
| /refonte-site-internet/ | refonte site internet | refonte site web, moderniser son site |
| /logo-identite-visuelle/ | création identité visuelle | création logo entreprise, charte graphique |
| /motion-design/ | motion design entreprise | vidéo motion design, animation logo |
| /maquettes-presentation/ | création maquette présentation | pitch deck, présentation commerciale |
| /agence-web-paris/ | agence web Paris | création site internet Paris, site vitrine Paris |
| /agence-web-hauts-de-seine/ | agence web Hauts de Seine | création site internet 92 |
| /site-internet-avocat/ | site internet avocat | création site cabinet d'avocat |
| /tarifs/ | prix site vitrine | tarif création site internet, prix site e-commerce |

Les volumes et la difficulté réels sont à valider avec un outil (DataForSEO, Semrush ou le planificateur Google Ads) avant de figer la liste.

### 3.3 Plan de redirections 301

| Ancienne URL | Nouvelle URL |
|---|---|
| /creation-de-site-vitrine-paris/ | /creation-site-vitrine/ |
| /creation-de-site-e-commerce-paris/ | /creation-site-e-commerce/ |
| /branding-identite/ | /logo-identite-visuelle/ |
| /maquettes-de-presentation/ | /maquettes-presentation/ |
| /agence-web-a-paris/, /choisir-agence-web-paris/ | /agence-web-paris/ |
| /site-internet-boulogne-billancourt/, /creation-de-site-e-commerce-boulogne/, /agence-web-collectivites-boulogne-billancourt/ | /agence-web-boulogne-billancourt/ |
| /creation-site-internet-clamart/, /agence-seo-clamart/ | /agence-web-clamart/ |
| /site-vitrine-avocat/ | /site-internet-avocat/ |
| /site-vitrine-medecin/, /site-vitrine-kinesitherapeute/ | /site-internet-professions-sante/ |
| /site-vitrine-architecte/ | /site-internet-architecte/ |
| /site-vitrine-restaurant/ | /site-internet-restaurant/ |
| /site-vitrine-pour-artisan/, /site-vitrine-artisan-renovateur-en-batiment/ | /site-internet-artisan-btp/ |
| /site-vitrine-coach-consultant/ | /site-internet-coach-consultant/ |
| /prix-site-vitrine-paris/, /creation-site-vitrine-tpe-paris/ | /tarifs/ |
| /logo-et-charte-graphique/, /logo-charte-graphique-identite-visuelle/ | /conseils/logo-charte-graphique-identite-visuelle/ (un seul guide) |
| /google-my-business-artisans-tpe/, /optimiser-fiche-google-my-business-coach/ | /conseils/optimiser-fiche-google-business-profile/ |
| /realisations-agence-web/ | /realisations/ |
| /contact-agence/ | /contact/ |
| /nos-services/ | / (ou une page /services/ légère) |

Articles conservés dans /conseils/ (après réécriture pour casser le gabarit) : anatomie d'un site qui convertit, site vitrine sans clients, soi même ou agence, combien coûte un e-commerce, Shopify vs WooCommerce, WordPress vs Webflow, avis Google, Google Business Profile, refonte identité visuelle.

Résultat : de 42 URL à environ 30, chacune avec un rôle clair.

### 3.4 Nouvelles règles éditoriales

1. **Une preuve par page** : capture d'un vrai projet, chiffre client, devis type, avant et après. C'est ce qu'une IA ne peut pas inventer.
2. **Fin du gabarit unique** : longueur selon l'intention (une comparaison peut faire 1 200 mots), plus de « guide essentiel 2026 » dans tous les titres.
3. **Rythme** : 2 articles bien faits par mois plutôt que 10.
4. **Auteur visible** : Alexandre Coury avec photo, parcours et LinkedIn sur chaque article.
5. **Schema honnête** : Organization + ProfessionalService sur la home, Service sur les pages services et secteurs, BlogPosting sur les articles, CreativeWork sur les réalisations. Pas de HowTo, pas d'AggregateRating auto déclaré.

### 3.5 Hors site

* Google Business Profile : catégorie « Concepteur de sites Web », photos de projets, publications mensuelles, réponse à chaque avis.
* Annuaires d'agences (La Fabrique du Net, Sortlist, Codeur, Malt) avec la même fiche NAP partout.
* Crédit « Site réalisé par Aurea Media » en pied de page des sites clients (avec leur accord) : les liens les plus naturels pour une agence.

## 4. Ordre d'exécution

1. **Avant la refonte** : exporter les 16 derniers mois de la Search Console (requêtes et pages) pour confirmer le diagnostic et repérer les pages qui gardent des impressions.
2. **Pendant la refonte** : nouveau thème, nouvelle architecture, redirections 301 testées une par une, schema propre.
3. **Mise en ligne** : soumettre le sitemap, inspecter les 10 pages clés dans la Search Console, surveiller les erreurs 404 pendant 30 jours.
4. **Après** : 2 articles par mois, une étude de cas par projet livré, un lien d'annuaire par semaine.

## 5. Indicateurs à suivre

| Indicateur | Où | Signal attendu |
|---|---|---|
| Impressions des pages services | Search Console | hausse sous 4 à 8 semaines après la mise en ligne |
| Nombre de pages en concurrence sur une même requête | Search Console, onglet Requêtes puis Pages | une seule page par requête |
| Position moyenne « création site vitrine » | Search Console | entrée dans le top 30 puis top 10 |
| Demandes de devis | formulaire, GA4 | hausse du taux de conversion des pages services |
| LCP et INP mobile | Search Console, Core Web Vitals | 100 % des URL en « bon » |

**Comment savoir si la stratégie échoue** : si 8 semaines après la mise en ligne les impressions des pages services n'ont pas progressé, le problème vient de l'autorité du domaine plus que de l'architecture, et il faudra accélérer sur les liens (annuaires, crédits clients, presse locale).

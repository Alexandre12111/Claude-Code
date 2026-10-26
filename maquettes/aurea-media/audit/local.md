# Audit SEO local — aurea-media.fr

Périmètre : type d'établissement et règles Google Business Profile (GBP), cohérence NAP, pages locales existantes et stratégie pour la refonte, avis clients, citations françaises, optimisation de la fiche GBP. Analyse fondée sur le crawl (`scratchpad/crawl/html` et `txt`, 50 URL) et sur les faits établis dans CONTEXTE.md. L'accès réseau sortant vers des services tiers (API SIREN, PagesJaunes, Google Maps, etc.) a été bloqué par le proxy pendant cette session : les points qui en dépendent sont marqués « non vérifié » avec une méthode de vérification manuelle indiquée.

## Score Local SEO : 43 / 100

| Dimension | Poids | Points obtenus | Constat en une ligne |
|---|---|---|---|
| Signaux GBP | 25 | 11 / 25 | Fiche Google visiblement active (Knowledge Panel lié) mais statut d'adresse non clarifié et aucune preuve d'optimisation vérifiable depuis le site |
| Avis et réputation | 20 | 10 / 20 | 4,9/5 sur 39 avis crédible, mais preuve sociale figée (3 avis statiques, dernier daté de septembre 2025) et chiffres contredits par le propre code du site |
| SEO local on page | 20 | 11 / 20 | Bonnes bases (NAP en pied de page, deux pages locales) mais architecture en doublon et pages catégories sans H1 |
| Cohérence NAP et citations | 15 | 6 / 15 | NAP visible irréprochable partout, mais citations françaises non détectables depuis le site et aucune preuve de présence sur les annuaires prioritaires |
| Balisage schema local | 10 | 3 / 10 | Schéma correct sur l'accueil mais contredit par au moins 4 autres pages, avec un numéro de téléphone factice publié en production |
| Liens et autorité locale | 10 | 2 / 10 | Aucun profil social lié, aucune citation ni mention externe détectable |
| **Total** | **100** | **43 / 100** | Base honnête (vraie fiche Google, vrais avis, vraie activité) mais exécution technique locale à reprendre avant la refonte |

Ce score traduit un point de départ correct sur le fond (activité réelle, avis authentiques, positionnement Paris/Île-de-France cohérent) mais fragilisé par des données techniques contradictoires et une absence de vérification possible sur les citations externes.

---

## 1. Type d'établissement : très probablement un établissement de services (SAB) derrière une adresse de domiciliation

### Constat
Le 60 rue François Ier, 75008 Paris est une adresse très fréquemment utilisée par des sociétés de domiciliation dans ce quartier du 8e arrondissement. Plusieurs indices convergent sur le site : Aurea Media est une entreprise individuelle (un seul dirigeant, Alexandre Coury), le texte revendique « zéro sous-traitance, zéro intermédiaire », et la page Agence précise elle même « Rendez-vous en présentiel possibles, sur demande », ce qui est la formulation typique d'une activité sans accueil physique permanent plutôt que d'un bureau ouvert au public. Aucune page ne montre de photo de façade, de vitrine ou de bureau à cette adresse.

Vérification SIREN 992 693 473 auprès de `recherche-entreprises.api.gouv.fr` : **non vérifié**, l'appel a été bloqué par le proxy réseau de cet environnement (`EGRESS_BLOCKED`). À vérifier manuellement avant la refonte en consultant `https://recherche-entreprises.api.gouv.fr/search?q=992693473` ou `https://annuaire-entreprises.data.gouv.fr/entreprise/992693473`, en particulier le champ dénomination du siège : si l'adresse est associée à une société de domiciliation tierce (souvent visible dans les actes ou via une recherche du numéro de SIREN sur le même palier d'adresse partagé par de nombreuses entreprises sans lien entre elles), cela confirme la domiciliation.

### Pourquoi c'est important
Google applique des règles strictes de représentation des établissements (« Consignes relatives aux informations sur les établissements »). Une adresse ne peut être affichée publiquement sur une fiche Google Business Profile que si l'établissement y reçoit réellement des clients pendant les horaires affichés, avec une présence humaine sur place. Une adresse de domiciliation, un bureau virtuel ou un espace de coworking sans bureau privé dédié et accessible au public ne remplissent pas cette condition. Si l'adresse est aujourd'hui affichée publiquement sur la fiche Google alors qu'elle correspond à une domiciliation, l'établissement est en infraction avec les consignes Google, avec trois risques concrets : suspension de la fiche après un contrôle (« Business Redetermination »), signalement par un concurrent via « Suggérer une modification », et perte du repère de proximité qui alimente le pack local puisque Google peut invalider l'épingle.

### La bonne configuration GBP pour ce cas
Configurer la fiche comme un établissement de services avec zone desservie (Service Area Business) :
- Dans les paramètres de la fiche, cocher « Cet établissement propose des livraisons/services aux clients à leur adresse » et masquer l'adresse (option « Ne pas afficher mon adresse professionnelle, seule la zone desservie sera visible »). L'adresse reste saisie en back office pour la vérification (obligatoire pour valider la fiche) mais n'apparaît plus publiquement sur Maps.
- Définir une zone desservie réaliste et resserrée plutôt qu'un large « France entière » : Paris, puis les départements où l'activité réelle se concentre (92 Hauts-de-Seine en priorité, éventuellement 78, 94). Google déconseille les zones disproportionnées par rapport à l'activité réelle du 92 conseil que le site donne lui-même dans son article Agence SEO Clamart, ce qui est la bonne pratique mais n'est manifestement pas appliquée à la propre fiche de l'agence.
- Masquer l'adresse sur la fiche n'entraîne pas de perte de classement local : le classement continue de s'appuyer sur l'adresse de vérification en arrière-plan, seule sa visibilité publique change.
- Ne jamais ajouter de photo de façade ou d'enseigne à cette adresse, et éviter toute mention « bureau ouvert au public » dans les publications GBP.

### Comment présenter l'adresse sur le site et dans le schéma
Deux obligations différentes coexistent et ne s'opposent pas :
- Obligation légale française (mentions légales) : l'adresse complète du siège social doit rester publiée dans les mentions légales, c'est déjà le cas et il ne faut rien changer sur ce point.
- Positionnement commercial : sur les pages commerciales (accueil, agence, contact, pages locales), continuer à afficher la ville et l'arrondissement (« Paris 8e ») comme repère de confiance plutôt que comme adresse d'accueil physique, et généraliser la mention déjà présente sur la page Agence (« sur demande ») pour éviter toute ambiguïté sur un accueil au public. Ne pas ajouter de carte Google Maps intégrée pointant sur l'adresse (c'est actuellement le cas, à conserver).
- Balisage schema.org : conserver `PostalAddress` avec l'adresse légale dans le JSON-LD (le schéma ne détermine pas le statut SAB, seul le paramétrage GBP le fait), mais harmoniser les coordonnées `geo` et supprimer toute promesse d'accueil physique dans les descriptions.

### Comment vérifier
Consulter la fiche depuis google.com/maps en recherchant « Aurea Media Paris » : si l'adresse complète et un bouton « Itinéraire » apparaissent, l'adresse est publique et doit être masquée. Si seule une zone ombrée apparaît sans adresse précise, la configuration est déjà correcte, à confirmer en se connectant au compte Google Business Profile Manager, rubrique Infos, section Adresse et zone de service.

---

## 2. Cohérence NAP

### NAP visible (HTML, footer, header, mentions légales)
Excellent sur ce point : le nom, l'adresse postale, le téléphone, l'e-mail et les horaires sont strictement identiques sur les 44 pages de contenu inspectées.

| Champ | Valeur observée | Où |
|---|---|---|
| Nom | Aurea Media | Toutes les pages, footer et header |
| Adresse | 60 rue François Ier 75008 Paris, France | Footer de toutes les pages, mentions légales, pages Clamart et Boulogne |
| Téléphone (affiché) | 06 51 28 77 61 | Header, nav mobile |
| Téléphone (footer, lien tel:) | +33 6 51 28 77 61, href="tel:+33651287761" | Footer de toutes les pages |
| E-mail | Alexandre.coury@aurea-media.fr (affichage), href="mailto:Alexandre.coury@aurea-media.fr" | Footer, page Contact |
| Horaires | Lundi au vendredi, 9h à 19h | Footer, page Contact, mentions légales |
| SIREN | 992 693 473 | Footer de toutes les pages, mentions légales |

Aucune divergence détectée sur ce niveau visible : le format téléphone national en en-tête et international en pied de page est une variation normale et sans risque tant que le lien `tel:` reste au format international, ce qui est le cas.

### NAP et preuve sociale dans le balisage structuré (JSON-LD) : incohérences critiques

Quatre pages sur les 50 crawlées embarquent un bloc JSON-LD `LocalBusiness` distinct de celui de l'accueil (`creation-site-internet-clamart`, `site-internet-boulogne-billancourt`, `choisir-agence-web-paris`, `google-my-business-artisans-tpe`), en plus du bloc `ProfessionalService` de l'accueil. Ces cinq blocs ne racontent pas la même histoire :

| Source | Type schema | Téléphone | Coordonnées geo | Note / avis | Tarif site vitrine |
|---|---|---|---|---|---|
| Accueil (`#organization`) | ProfessionalService | +33651287761 | 48.8719, 2.3076 (4 décimales) | 4,9 / 39 avis | dès 1 000 € HT |
| Clamart (même `@id` `#organization`) | LocalBusiness | +33651287761 | 48.8722, 2.2994 | 5 / 40 avis | dès 1 200 € HT |
| Boulogne-Billancourt (même `@id` `#organization`) | LocalBusiness | +33651287761 | 48.8722, 2.2994 | 5 / 40 avis | dès 1 200 € HT |
| choisir-agence-web-paris (`@id` `#localbusiness`, distinct) | LocalBusiness | **+33-1-XX-XX-XX-XX** | 48.8722, 2.3043 | absente | 1 200 à 4 000 € HT |
| google-my-business-artisans-tpe | LocalBusiness | à confirmer, même bloc type | à confirmer | à confirmer | à confirmer (non approfondi, même famille de contenu que choisir-agence-web-paris) |

Trois problèmes distincts, par ordre de gravité :
1. **Numéro de téléphone factice publié en production** sur `choisir-agence-web-paris` : `+33-1-XX-XX-XX-XX` est un espace réservé (placeholder) jamais remplacé, resté dans le JSON-LD indexable. C'est une donnée structurée invalide et trompeuse, potentiellement traitée par Google comme un signal de qualité négatif pour la page, en plus de contredire l'attribution du numéro réel de l'agence.
2. **Note et nombre d'avis fabriqués** sur les pages Clamart et Boulogne-Billancourt : `5 / 40 avis, tous 5 étoiles` alors que le reste du site affiche partout `4,9 / 39 avis`. Ce ne sont pas des données issues d'une source vérifiable (ni de l'API Google, ni d'un widget d'avis), donc un `aggregateRating` non conforme aux règles Google sur les extraits d'avis, qui exigent que la note reflète des avis réels et vérifiables.
3. **Coordonnées GPS divergentes sur 3 valeurs différentes** pour la même entité (`#organization` /`#localbusiness`) : 48.8719/2.3076, 48.8722/2.2994 et 48.8722/2.3043. Un écart de cet ordre (plusieurs centaines de mètres) n'est pas un simple arrondi, cela ressemble à des coordonnées non vérifiées, probablement générées automatiquement lors de la création de chaque page. Le standard recommandé (5 décimales minimum) n'est atteint sur aucun des trois blocs.

### Pourquoi c'est important
Un moteur de recherche qui lit des valeurs contradictoires sur des propriétés structurées de la même entité (`@id` identique) sur des pages différentes ne peut pas fusionner correctement l'entité dans son graphe de connaissances, et peut choisir d'ignorer le rich result plutôt que de trancher. Le tarif contradictoire (1 000 € sur les pages officielles vs 1 200 à 4 000 € sur une page article) crée en plus un doute côté prospect qui recoupe les pages avant de contacter l'agence.

### Action
- Retirer immédiatement le numéro placeholder de `choisir-agence-web-paris` et le remplacer par le vrai numéro, ou supprimer purement ce bloc `LocalBusiness` dupliqué.
- Choisir une seule source de vérité pour le balisage local (recommandé : un seul bloc `ProfessionalService` réutilisé à l'identique via un composant/partiel commun dans le futur thème, au lieu d'un JSON-LD recopié à la main dans chaque article), avec des coordonnées `geo` vérifiées à 5 décimales et une note/nombre d'avis alimentés depuis une seule variable mise à jour (idéalement automatisée depuis l'API Google Business Profile plutôt que saisie à la main dans chaque article).
- Supprimer tout `aggregateRating` codé en dur qui ne correspond pas au chiffre réel affiché ailleurs sur le site.
- Harmoniser les tarifs affichés partout, ou assumer explicitement un tarif « spécial zone » différent mais alors le justifier dans le texte plutôt que de le laisser passer pour une incohérence.

### Comment vérifier
Reproduire le test avec l'outil de test de résultats enrichis de Google (`search.google.com/test/rich-results`) sur les 4 pages listées, avant/après refonte : les valeurs `telephone`, `aggregateRating` et `geo` doivent être strictement identiques sur toutes les pages qui portent le même `@id`. Passer aussi ces 4 URL dans le validateur schema.org.

---

## 3. Pages locales actuelles : qualité, unicité, format, et stratégie pour la refonte

### Inventaire des pages à connotation locale

| Page | Format actuel | Nature réelle |
|---|---|---|
| `/creation-site-internet-clamart/` | Article de blog (post WordPress, catégorie « création site vitrine », dans le plan du site sous « Articles ») | Page de conversion « ville » avec prix, FAQ et schema LocalBusiness |
| `/site-internet-boulogne-billancourt/` | Article de blog, même structure | Page de conversion « ville », quasi identique à la précédente |
| `/agence-seo-clamart/` | Article de blog | Guide pédagogique sur le SEO local, non transactionnel |
| `/agence-web-hauts-de-seine/` | Article de blog | Guide pédagogique sur le choix d'une agence locale, réutilise des blocs de texte entiers de `agence-seo-clamart` |
| `/agence-web-collectivites-boulogne-billancourt/` | Article de blog | Guide de niche (marchés publics, mairies), peu de lien avec l'offre commerciale TPE/PME |
| `/creation-de-site-e-commerce-boulogne/` | Article de blog | Guide e-commerce local, contenu réellement différencié |

### Constat sur la qualité et l'unicité
Les deux pages « ville » (Clamart et Boulogne-Billancourt) suivent un gabarit identique poste pour poste : même découpage en 6 sections numérotées, mêmes types de statistiques encadrées, même bloc « Pourquoi choisir Aurea Media », même liste de villes voisines en bas de page, mêmes questions fréquentes reformulées avec le nom de la ville substitué. Le test de permutation (remplacer « Clamart » par « Boulogne-Billancourt » dans la page Clamart) fonctionne presque à l'identique, à l'exception du bloc « Secteurs d'activité » propre à la page Boulogne-Billancourt. C'est le profil type d'une paire de pages satellites (« doorway pages ») : utile pour le maillage sémantique local mais risqué si le modèle est démultiplié sur davantage de villes sans contenu réellement différenciant, et fragile pour un établissement de services sans point de vente physique dans chacune de ces villes.

Les 4 articles « guide » sont d'une autre nature : contenu éditorial réel visant une intention informationnelle (« comment fonctionne le SEO local », « ce qui différencie une agence de collectivités »), pertinent pour l'E-E-A-T et pour capter des recherches en amont de l'achat. Leur défaut n'est pas le doublon inter-villes mais la réutilisation de paragraphes statistiques quasiment mot pour mot entre `agence-seo-clamart` et `agence-web-hauts-de-seine` (le passage sur le pack local à 3 résultats et le gain de 25 % de clics en passant de 3 à 5 étoiles), ce qui dilue la valeur unique de chaque article aux yeux de Google.

Point commun à toutes ces pages : elles sont classées comme « Articles » dans le plan du site et dans la navigation (menu « Nos conseils web »), pas comme des pages de destination commerciales. Aucune n'est reliée depuis le menu principal ni depuis la page d'accueil autrement que via les blocs « articles liés » en bas de page ou le plan du site, ce qui limite fortement le maillage interne et le jus de lien qu'elles reçoivent.

### Stratégie recommandée pour la refonte

Le principe directeur : pour un établissement de services sans point de vente physique par ville, la priorité doit aller aux pages de service dédiées plutôt qu'à la multiplication de pages villes quasi identiques. Whitespark 2026 confirme que les pages de service dédiées sont le facteur numéro un du SEO local organique, largement devant la simple déclinaison géographique d'un même gabarit.

1. **Ne pas créer de nouvelles pages ville au delà de celles qui existent.** Créer une page par arrondissement parisien ou une page par commune supplémentaire des Hauts-de-Seine (Issy-les-Moulineaux, Meudon, Neuilly, Vanves...) reproduirait le même problème à plus grande échelle et augmenterait le risque de pages passerelles aux yeux de Google, d'autant plus risqué compte tenu du statut probable d'établissement de services sans agence physique dans chaque ville.
2. **Fusionner les deux pages ville actuelles en une seule page réelle, hors blog : « Agence web Hauts-de-Seine ».** Transformer en vraie page (pas un article) intégrant les deux implantations (Clamart et Boulogne-Billancourt) comme sous-sections dans un contenu unique et substantiellement plus riche que la simple substitution du nom de ville, avec un lien depuis le menu principal ou depuis un pied de page « Zones d'intervention ». Rediriger en 301 les deux anciennes URL d'article vers cette nouvelle page pour ne pas perdre leur historique.
3. **Modèle de page locale utile pour la refonte (unique par grande zone, pas par ville) :**
   - un paragraphe de contexte réellement spécifique à la zone (pas des statistiques BrightLocal génériques recopiées d'un article à l'autre),
   - une preuve sociale vérifiable liée à cette zone si elle existe (réalisation, avis client localisé), sans jamais inventer une antériorité qui n'existe pas,
   - un bloc Schema `Service` + `areaServed` propre, sans dupliquer un `LocalBusiness` complet par page (un seul bloc organisation suffit, référencé depuis chaque page),
   - un lien de navigation réel (menu ou pied de page), pas seulement un lien depuis un article de blog,
   - pas de FAQ dupliquée mot pour mot d'une page à l'autre : d'après les règles internes du client, Google n'affiche plus de résultat enrichi FAQ depuis le 7 mai 2026, donc le FAQPage ne doit plus être maintenu dans une logique de gain SERP, seulement conservé comme contenu utile si besoin, sans effort disproportionné.
4. **Garder les 4 articles « guide »** comme contenu de blog (ils captent une intention différente et utile), mais dédupliquer les blocs de statistiques recopiés et les relier davantage vers les pages de service réelles plutôt que vers d'autres articles seulement.
5. **Cibler en priorité Paris et Hauts-de-Seine (92)**, sans page dédiée par arrondissement parisien : la page `/agence-web-a-paris/` existante suffit comme page de destination Paris, le badge « Paris 8e » restant un repère de confiance plutôt qu'un argument de proximité physique par quartier.
6. **Nombre de pages locales cible pour la refonte : 2 pages de destination réelles (Paris et Hauts-de-Seine/Île-de-France), pas davantage**, complétées par les articles de blog existants qui restent dans la logique éditoriale.

### Comment vérifier
Après refonte, comparer le contenu texte des deux pages restantes avec un outil de similarité de contenu (ou une lecture croisée manuelle) : viser un taux de contenu réellement unique par page supérieur à 70 à 80 %, hors éléments de structure commune (header, footer, blocs CTA). Vérifier dans Search Console, quelques semaines après publication, que les deux pages sont indexées et ne sont pas regroupées par Google sous un seul canonical.

---

## 4. Avis clients : passer de 39 à 80 et plus

### Constat
39 avis Google pour 45 projets livrés depuis 2021 est en réalité un taux de conversion avis élevé (environ 85 % des projets livrés). Le retard ne vient donc pas d'un problème structurel de satisfaction mais d'un flux qui ne semble plus alimenté récemment : les 3 témoignages affichés sur le site sont statiques (mêmes 3 avis mot pour mot sur l'accueil et sur la page Agence) et le plus récent est daté de septembre 2025, soit plus d'un an avant la date de cet audit. La fraîcheur réelle de la fiche Google (nombre d'avis obtenus ces 3 dernières semaines, taux de réponse effectif) n'est pas vérifiable depuis le site : **non vérifié**, à consulter directement dans Google Business Profile Manager, section Avis.

### Pourquoi c'est important
Sterling Sky documente une règle des 18 jours : au delà de 3 semaines sans nouvel avis, le classement local peut chuter brutalement (effet falaise), avant de remonter une fois le flux repris. Un flux d'avis régulier compte donc davantage qu'un pic ponctuel. Par ailleurs, les avis qui mentionnent naturellement le type de projet ou le secteur du client renforcent la pertinence sémantique de la fiche pour les recherches locales correspondantes.

### Plan d'action
- **Process systématique post-livraison** : envoyer la demande d'avis dans les 48h suivant la mise en ligne du site (moment où la satisfaction est la plus haute), avec le lien court `g.page` déjà décrit dans l'article `avis-google` du site lui-même. Ajouter une seconde relance à 3 mois pour les clients qui n'ont pas répondu à la première sollicitation.
- **Reprise de la base des 45 projets livrés** : identifier les clients livrés sans avis laissé (environ 6 à 10 clients d'après le ratio actuel) et leur envoyer une relance ciblée avant la refonte, pour redémarrer un flux visible.
- **Maintenir un rythme régulier plutôt qu'une campagne ponctuelle** pour respecter la règle des 18 jours, par exemple un objectif minimum d'un avis toutes les deux semaines une fois le flux de nouveaux projets et le rattrapage des anciens clients combinés.
- **Ne jamais suggérer un texte tout fait au client** (contraire aux règles Google), mais poser une question ouverte avant l'envoi du lien (« quel type de site avons-nous réalisé pour vous, et quel a été le principal bénéfice ? ») pour orienter naturellement le client vers un avis riche qui mentionne le type de prestation, sans jamais dicter la formulation.
- **Répondre à 100 % des avis**, positifs et négatifs, avec un délai cible (48h par exemple), une réponse personnalisée qui mentionne naturellement le type de projet quand c'est pertinent. Le taux de réponse actuel n'est pas vérifiable depuis le site : **non vérifié**, à contrôler dans Google Business Profile Manager.
- **Corriger en priorité la fabrication de chiffres d'avis dans le code** (section 2 de ce rapport) avant de communiquer sur l'objectif de 80 avis : afficher un chiffre inventé (5/40) à côté du vrai chiffre (4,9/39) nuit à la crédibilité de toute la démarche avis.
- **Remplacer les 3 témoignages statiques par un flux réellement vivant** (widget d'avis à jour ou mise à jour manuelle régulière du bloc témoignages), pour que le site reflète la progression réelle vers l'objectif de 80 avis plutôt que de rester figé sur une capture de septembre 2025.

### Comment vérifier
Suivre dans Google Business Profile Manager le nombre d'avis total chaque semaine (objectif : ne jamais laisser passer plus de 18 jours sans nouvel avis) et le taux de réponse (objectif 100 %). Revenir sur le site après refonte pour confirmer que le chiffre affiché (accueil, footer, pages locales) correspond au chiffre réel de la fiche, sans écart.

---

## 5. Citations françaises prioritaires

Aucune des présences suivantes n'a pu être vérifiée depuis cet environnement (accès réseau sortant bloqué vers pagesjaunes.fr et l'ensemble des domaines testés) : **non vérifié pour l'ensemble de cette section**, à contrôler manuellement en recherchant « Aurea Media » sur chaque plateforme, ou via un outil de gestion de citations (Moz Local, BrightLocal, Whitespark) une fois disponible.

Ordre de priorité recommandé, en tenant compte du profil réel de l'activité (agence web B2B, établissement de services sans accueil public, cible professionnelle plutôt que grand public) :

### Priorité 1 — Critique
- **PagesJaunes** : annuaire généralisé le plus consulté en France, source secondaire souvent recoupée par Google pour valider une entité, fiche gratuite disponible. À créer ou revendiquer en premier, avec la zone desservie plutôt qu'une adresse publique.
- **Bing Places for Business** : gratuit, alimente Bing Maps et de plus en plus les assistants IA (Copilot), pertinent pour la visibilité IA mentionnée dans les facteurs de classement 2026.
- **Sortlist** : annuaire B2B spécialisé agences web et marketing, forte intention d'achat, comparaison directe avec les concurrents du secteur. Prioritaire car parfaitement aligné avec l'activité réelle, contrairement à un annuaire généraliste.

### Priorité 2 — Haute
- **Codeur.com** et **Malt** : places de marché françaises pour freelances et agences web, fort trafic qualifié, profil personnel d'Alexandre Coury en développeur web particulièrement adapté à Malt.
- **Apple Plans (Apple Business Connect)** : gratuit, alimente Siri et Plans, pertinent pour les recherches vocales et les utilisateurs iPhone, en configurant également la zone desservie plutôt qu'une adresse publique.
- **CCI (Chambre de Commerce et d'Industrie, annuaire des entreprises)** : citation institutionnelle à forte autorité, à vérifier si l'entreprise y figure déjà automatiquement du fait de son immatriculation.

### Priorité 3 — Moyenne
- **Annuaires spécialisés agences web** (hors Sortlist déjà cité) : renforcent la pertinence thématique des citations, un facteur explicitement cité parmi les critères de visibilité IA.
- **La French Tech** : pertinent seulement si les critères d'éligibilité de la communauté locale sont remplis (à vérifier, toutes les structures ne sont pas éligibles) ; ne pas prioriser tant que l'éligibilité n'est pas confirmée.
- **Hoodspot** et **118712** : présence correcte à maintenir pour la cohérence NAP globale, impact plus limité.

### Priorité 4 — Basse
- **Yelp** : faible part de marché en France pour ce type d'activité B2B, fiche de base suffisante.
- **Waze** et **Mappy** : orientés « itinéraire » et calcul de trajet, peu pertinents pour un établissement sans adresse publique de destination ; à vérifier seulement pour s'assurer qu'aucune fiche erronée générée automatiquement (via d'anciens agrégateurs de données) n'affiche une adresse ou un numéro obsolète, sans chercher à les optimiser activement.

### Action générale
Avant de créer la moindre fiche, figer une référence NAP unique (nom exact « Aurea Media », adresse au format identique à celui du site, numéro au format international) et l'utiliser à l'identique sur chaque plateforme, en indiquant partout une zone desservie plutôt qu'un accueil physique, cohérent avec la configuration GBP recommandée en section 1.

### Comment vérifier
Rechercher « Aurea Media Paris » sur chaque plateforme listée et comparer le nom, l'adresse, le téléphone affichés avec la référence NAP interne. Un tableau de suivi simple (une ligne par annuaire, une colonne par champ NAP) suffit, comme le recommande d'ailleurs l'article `agence-seo-clamart` publié par le site lui-même.

---

## 6. Optimisation de la fiche Google Business Profile

L'accès à la fiche elle même n'a pas été possible depuis cet environnement : **non vérifié pour l'ensemble de cette section**, recommandations à appliquer directement dans Google Business Profile Manager puis à contrôler visuellement sur la fiche publique.

### Catégorie
- Catégorie principale : **Concepteur de sites Web**, confirmée par le brief client et cohérente avec l'activité réelle. C'est le facteur de classement local le plus déterminant selon Whitespark 2026 : une catégorie principale incorrecte serait à l'inverse le facteur négatif le plus pénalisant, donc à vérifier en priorité absolue si ce n'est pas déjà le cas.
- Catégories secondaires suggérées, à valider dans le sélecteur de catégories GBP au moment de la configuration (le libellé exact peut varier) : agence de marketing, agence de design graphique ou graphiste, consultant en marketing. Se limiter à 3 à 5 catégories réellement représentatives de l'activité, sans ajout de catégories approximatives.

### Services
Lister dans l'onglet Services de la fiche chaque prestation réelle avec une description courte, alignée avec les pages du site une fois les tarifs harmonisés (section 2) : création de site vitrine, création de site e-commerce WooCommerce, refonte de site existant, branding et identité visuelle, maquettes de présentation, accompagnement SEO local et fiche Google Business Profile.

### Produits
Ajouter dans l'onglet Produits une ou deux offres packagées avec photo, prix de départ et lien direct vers la page correspondante du site (par exemple « Pack site vitrine, dès 1 000 € HT » vers `/creation-de-site-vitrine-paris/`), en veillant à ce que le prix affiché soit identique à celui du site.

### Posts
Publier au minimum toutes les une à deux semaines, conformément à la propre recommandation du site dans l'article `agence-seo-clamart`. Chaque post doit correspondre à un événement réel (nouveau projet livré, nouvel avis marquant, article de blog publié, offre ponctuelle) avec un lien traçable vers le site.

### Photos
Ajouter et renouveler régulièrement : portrait du fondateur (déjà disponible sur le site, à réutiliser), captures avant/après de sites livrés, logo et image de couverture. Ne jamais publier de photo de façade ou d'enseigne à l'adresse de domiciliation, ce qui contredirait la configuration en établissement de services et pourrait déclencher un contrôle Google.

### Questions et réponses
Amorcer la section Q&A en publiant et en répondant soi-même aux questions les plus prévisibles (« Intervenez-vous en dehors de Paris ? », « Le devis est-il vraiment gratuit ? », questions déjà formulées presque à l'identique dans les FAQ du site), pour occuper le terrain avant que des questions non maîtrisées n'apparaissent, puis surveiller et répondre rapidement à toute nouvelle question.

### Attributs
Activer les attributs cohérents avec un établissement de services sans accueil public (sur rendez-vous uniquement, devis en ligne disponible), et vérifier qu'aucun attribut n'implique un accueil physique permanent.

### Comment vérifier
Rechercher « Aurea Media » sur Google et Google Maps après chaque changement, comparer catégorie principale, services, produits et photos avec la liste ci-dessus, et suivre dans Business Profile Manager les statistiques de performance (appels, demandes d'itinéraire qui doivent rester faibles ou nulles compte tenu du statut SAB, clics vers le site) pour confirmer que la fiche se comporte bien comme un établissement de services.

---

## Limitations de cet audit

- Aucun accès réseau sortant n'a été possible depuis cet environnement vers l'API SIREN (`recherche-entreprises.api.gouv.fr`), vers Google Maps/Business Profile, ni vers aucun annuaire de citation (PagesJaunes, Bing Places, Apple Plans, Yelp, BBB, Sortlist, Codeur, Malt, etc.) : toutes les données qui en dépendent sont marquées « non vérifié » ci-dessus et doivent être contrôlées manuellement par le client ou l'agence.
- La fiche Google Business Profile elle même (catégorie actuellement configurée, zone de service actuelle, statut d'affichage de l'adresse, photos, posts, Q&A, taux de réponse aux avis, vélocité réelle des avis) n'a pas pu être consultée : toutes les recommandations de la section 6 sont formulées à partir des bonnes pratiques Google et du contenu du site, à confronter à la fiche réelle.
- L'audit ne couvre que les 50 URL déjà crawlées ; aucune page supplémentaire n'a été récupérée en direct sur le site pendant cette session.

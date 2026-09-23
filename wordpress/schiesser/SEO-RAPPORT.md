# Rapport SEO : Confiserie Schiesser

Thème Schiesser 0.2.1. Audit réalisé le 23 septembre 2026 sur le site de test (WordPress 6.5, contenu de démonstration), suivi des corrections et d'une nouvelle vérification complète.

## 1. En résumé

- **Six audits spécialisés** ont été menés : technique, données structurées, référencement local, recherche par IA (GEO), contenu, expérience de recherche (SXO).
- **Tout ce qui dépend du thème et du contenu de démonstration a été corrigé**, puis vérifié page par page : titres, descriptions, H1, données structurées valides, redirections, accessibilité (0 erreur), affichage mobile.
- **Ce qui reste dépend de la maison et du site réel** : trois réglages WordPress, les vraies coordonnées, la validation des faits, les vraies photos, la fiche Google, les avis et les annuaires (sections 6 à 10).
- **Rank Math** : chaque page et chaque produit arrive avec son mot-clé principal, son titre SEO et sa méta description. Objectif : un score de 80 à 90, sans chercher 100 à tout prix.

## 2. Scores

| Domaine | Audit initial | Après corrections (estimation) | Ce qui limite encore la note |
|---|---|---|---|
| Technique | 78/100 | environ 90 | Langue du site à régler sur le site réel ; feuilles de style de WordPress non allégées |
| Données structurées | 78/100 | environ 92 | Logo carré à définir ; profils officiels (sameAs) à renseigner |
| Référencement local | 35/100 (sur page 82, schéma 74) | environ 45 avant lancement | Fiche Google, avis et annuaires : 45 % de la note, tous hors site |
| Recherche par IA (GEO) | 58/100 | environ 70 | Faits à valider, personnes nommées, mentions de la marque sur d'autres sites |
| Contenu | 61/100 (E-E-A-T 50) | environ 72 | Preuves d'expertise : personnes, archives, presse, vraies photos |
| Expérience de recherche (SXO) | 51/100 | environ 65 | Avis, vraies photos, guide consacré au Läckerli |

Les notes « après corrections » sont des estimations : un nouvel audit sur le site en ligne, avec Rank Math actif et les vraies données, les confirmera. Le score local ne montera vraiment qu'avec la fiche Google et les premiers avis.

## 3. Ce qui a été corrigé dans le thème

### Technique
- **Redirections 301** des anciennes adresses : `/tea-room/` vers `/salon-de-the/`, et les anciennes adresses des produits renommés (par exemple `/produits/truffes-maison/` vers `/produits/truffes-au-chocolat/`).
- **Contenus d'exemple de WordPress** (article « Bonjour tout le monde », page d'exemple) supprimés à l'import s'ils n'ont jamais été modifiés.
- **Pages sans valeur en noindex** : recherche interne, erreur 404, archives vides.
- **Images** : photo principale chargée en priorité, images plus bas dans la page chargées au défilement, texte alternatif descriptif partout.
- **En-tête allégé** : version de WordPress masquée, liens techniques inutiles retirés.
- **Typographie française** : espaces insécables avant « : ; ? ! » et dans les heures (« 7 h 30 »), pour qu'aucune ponctuation ni aucune heure ne soit coupée en fin de ligne.
- **Alerte** dans Réglages maison si la langue du site n'est pas le français.

### Données structurées
- **Fiche établissement complète** (Bakery et CafeOrCoffeeShop) : adresse avec canton, position GPS, horaires regroupés, gamme de prix chiffrée, devise, année de fondation, raison sociale et numéro IDE s'ils sont renseignés, profils officiels, carte du salon, logo.
- **Avec Rank Math**, le thème complète la fiche de Rank Math au lieu d'en créer une seconde (même identifiant), retire l'« Article » ajouté par défaut sur les pages et ajoute à chaque page sa description, sa langue, ses dates, son image principale et son fil d'Ariane.
- **Produits** : données Product avec prix en CHF et vente en boutique. Aucune donnée Product n'est publiée sans prix chiffré (gâteau sur commande), pour ne jamais envoyer de données invalides à Google.
- **Questions fréquentes** : le balisage FAQPage reste en place car il décrit fidèlement le contenu visible. À titre d'information, Google n'affiche plus de résultats enrichis FAQ depuis le 7 mai 2026 : aucun gain d'affichage n'est à en attendre.
- **Vérifié** : données structurées valides sur toutes les pages, avec et sans Rank Math.

### Référencement local
- Nom, adresse et téléphone identiques partout, tous lus depuis Réglages maison.
- Nouveaux champs : canton, raison sociale, numéro IDE, profils officiels (une adresse par ligne).
- Horaires écrits en toutes lettres dans les textes (code court `[schiesser_horaires_phrase]`) et tableau des horaires balisé pour les moteurs.
- Page Nous visiter enrichie : tram, gare CFF, à pied, en voiture, accessibilité, paiement, liens vers bvb.ch et basel.com.

### Recherche par IA (GEO)
- **Présentation de la maison** réécrite comme une définition claire, reprise dans le pied de page et les données structurées.
- **Section « La Confiserie Schiesser en bref »** sur l'accueil : qui, quoi, où, depuis quand, horaires.
- Passages qui nomment la marque et le lieu, questions fréquentes enrichies, contenu entièrement lisible sans JavaScript.
- Le statut « Ouvert / Fermé » est exclu des extraits (`data-nosnippet`) pour qu'aucun moteur ne cite un état périmé.

### Contenu
- **Les 8 fiches produits réécrites** : nom et adresse qui portent le mot-clé (« Truffes au chocolat », « Gâteau sur commande »…), 260 à 380 mots, intertitres, préparation, conservation, formats, prix, allergènes et liens internes.
- **Allergènes précisés** (gluten des Läckerli, soja des chocolats) : à faire valider par la maison.
- **Affirmations fragiles adoucies** : amandes « de la région », « aucune machine », conchage en atelier, numérotation des pièces.
- **Nouvelle catégorie « Confiseries »** pour les marrons glacés.
- **Maillage interne** : chaque page et chaque produit reçoit au moins deux liens depuis le texte d'autres pages, et un menu de pied de page reprend toutes les pages principales.
- **Nouvelle page « Cadeaux d’entreprise »** (`/cadeaux-entreprise/`) avec demande de devis préremplie : c'était le public le moins bien servi (39 % dans l'audit SXO).

### Expérience de recherche (SXO)
- Bouton « Commander » avec un e-mail prérempli (produit, quantité, date de retrait, coordonnées).
- Sur mobile : prix et boutons de commande juste après la description, puis une barre de commande fixe pendant la lecture de la fiche.
- Numéros de planche cohérents avec l'ordre d'affichage, produits liés variés d'une fiche à l'autre.

## 4. Carte des mots-clés

Un mot-clé principal par page, placé dans le titre SEO, la méta description, le H1 et l'introduction. Les mots-clés secondaires sont déjà saisis dans Rank Math. Les titres suivent la règle « Mot-clé | Proposition – Marque ».

| Page | Mot-clé principal | Titre SEO (caractères) | Description (caractères) |
|---|---|---|---|
| Accueil `/` | confiserie à Bâle | Confiserie à Bâle \| Chocolats et salon de thé – Schiesser (57) | 147 |
| `/boutique/` | chocolats à Bâle | Chocolats à Bâle \| Faits main au Marktplatz – Schiesser (55) | 154 |
| `/salon-de-the/` | salon de thé à Bâle | Salon de thé à Bâle \| Tea Room du Marktplatz – Schiesser (56) | 143 |
| `/notre-histoire/` | confiserie historique à Bâle | Confiserie historique à Bâle \| Depuis 1870 – Schiesser (54) | 153 |
| `/nous-visiter/` | confiserie au Marktplatz | Confiserie au Marktplatz \| Horaires et accès – Schiesser (56) | 155 |
| `/contact/` | contacter la Confiserie Schiesser | Contacter la Confiserie Schiesser \| Commandes à Bâle (52) | 150 |
| `/cadeaux-entreprise/` | cadeaux d’entreprise à Bâle | Cadeaux d’entreprise à Bâle \| Coffrets maison – Schiesser (57) | 154 |
| `/produits/lackerli-de-bale/` | Läckerli de Bâle | Läckerli de Bâle \| Biscuit au miel fait main – Schiesser (56) | 154 |
| `/produits/truffes-au-chocolat/` | truffes au chocolat | Truffes au chocolat \| Roulées chaque matin – Schiesser (54) | 141 |
| `/produits/pralines-artisanaux/` | pralinés artisanaux | Pralinés artisanaux \| Assortiment de saison – Schiesser (55) | 149 |
| `/produits/tablette-de-chocolat-artisanale/` | tablette de chocolat artisanale | Tablette de chocolat artisanale \| Faite main – Schiesser (56) | 158 |
| `/produits/biscuits-aux-amandes/` | biscuits aux amandes | Biscuits aux amandes \| Cuits chaque matin – Schiesser (53) | 150 |
| `/produits/marrons-glaces/` | marrons glacés | Marrons glacés \| Confits lentement, en saison – Schiesser (57) | 152 |
| `/produits/gateau-sur-commande/` | gâteau sur commande | Gâteau sur commande à Bâle \| Anniversaires – Schiesser (54) | 146 |
| `/produits/coffret-de-chocolats/` | coffret de chocolats | Coffret de chocolats \| Assortiment à offrir – Schiesser (55) | 147 |

Les adresses restent courtes et lisibles. Le mot-clé n'y figure que lorsque c'est naturel : Rank Math signalera donc « mot-clé absent de l'adresse » sur quelques pages (boutique, histoire, visite, contact). C'est voulu : changer ces adresses nuirait à la clarté du menu pour un gain minime.

## 5. Données structurées par page

| Page | Données publiées |
|---|---|
| Toutes les pages | Établissement (Bakery et CafeOrCoffeeShop), site (WebSite) |
| Accueil | WebPage, liste de produits (ItemList), questions (FAQPage) |
| Boutique | CollectionPage, fil d'Ariane, liste de produits, questions |
| Salon de thé | WebPage, fil d'Ariane, questions ; la fiche établissement pointe vers la carte du salon |
| Notre histoire | AboutPage, fil d'Ariane |
| Nous visiter | WebPage, fil d'Ariane, questions |
| Contact | ContactPage, fil d'Ariane, questions |
| Cadeaux d’entreprise | WebPage, fil d'Ariane, questions |
| Fiches produits | ItemPage, fil d'Ariane, Product avec prix (sauf gâteau sur commande) |

Pour le gâteau : si la maison indique un prix de départ dans le champ Prix (par exemple « Dès CHF 45.00 »), le thème publie automatiquement un Product avec une offre « à partir de ».

## 6. Réglages à faire sur le site réel (environ 15 minutes)

1. **Réglages → Général** : Langue du site « Français » (« Français de Suisse » si proposé), Fuseau horaire « Zurich », Titre du site « Confiserie Schiesser ». Ces trois réglages corrigent d'un coup la langue déclarée à Google, l'heure du bandeau « Ouvert / Fermé » et le nom affiché dans les résultats.
2. **Réglages → Lecture** : au lancement, décocher « Demander aux moteurs de recherche de ne pas indexer ce site ».
3. **Réglages maison** : vraies coordonnées (numéro de rue, code postal), latitude et longitude à 5 décimales, lien de la fiche Google, canton, raison sociale, numéro IDE, profils officiels (Instagram, Facebook, fiche Google, Tripadvisor…).
4. **Apparence → Personnaliser → Identité du site** : logo carré d'au moins 112 × 112 pixels et icône du site.
5. **Rank Math** : assistant en mode facile, type « Petite entreprise », plan du site (Pages et Produits), module Redirections, module Instant Indexing (pour Bing), connexion à Google Search Console et à Bing Webmaster Tools.
6. **Publier** les Mentions légales (brouillon créé par l'import) et la Politique de confidentialité (nLPD).

## 7. Faits à faire valider par la maison

Le contenu de démonstration reprend la maquette : Google et les moteurs d'IA recoupent les faits avec d'autres sources, il faut donc qu'ils soient exacts.

1. **Adresse** : le site indique « Marktplatz, 4001 Basel » ; des fiches tierces indiquent « Marktplatz 19 » et « 4051 Basel ». Choisir la forme officielle et l'utiliser partout à l'identique.
2. **Seconde boutique et personnes** : des sites tiers mentionnent une seconde boutique, une terrasse et nomment des membres de la famille et de l'équipe. Si c'est exact, le dire sur le site (Notre histoire, Nous visiter) renforce beaucoup la confiance.
3. **Dates** de la chronologie (1920 salon de thé, 1948 atelier, 1975 transmission) et formule « transmis de génération en génération ».
4. **Atelier visible** depuis la boutique, biscuits cuits « chaque matin », truffes « roulées chaque matin ».
5. **Salon** : boiseries, porcelaine, glaces maison, cappuccino servi avec un praliné, réservation dès six personnes, privatisation.
6. **Pratique** : horaires (dont le dimanche), euros acceptés, langues parlées, accès au salon par escalier, délai de réponse aux e-mails.
7. **Produits** : tous les prix, formats (100 et 250 g ; 9, 16 et 25 pièces ; 6 à 40 personnes), compositions et allergènes, mois de disponibilité des marrons glacés, logo possible sur les coffrets, retrait uniquement en boutique.

## 8. À faire hors du site

**Fiche Google Business Profile** (le levier local le plus important)
- Catégorie principale « Confiserie » ; catégories secondaires « Salon de thé », « Chocolaterie », « Café ».
- Attributs (accès de plain-pied, paiement sans contact…), photos réelles (façade, vitrine, salon, produits), carte du salon.
- Un post toutes les deux à trois semaines ; questions et réponses préremplies avec les textes du site.

**Annuaires et plateformes** avec exactement le même nom, la même adresse et le même téléphone : local.ch, search.ch, Tripadvisor (important pour le salon de thé), basel.com (Basel Tourismus), Bing Places, Apple Business Connect (Plans d'Apple), Yelp. Une fiche Wikidata est envisageable pour une maison fondée en 1870, si des sources publiques existent.

**Avis** : QR code en caisse et au salon, demande régulière plutôt que ponctuelle, réponse à chaque avis sous 48 heures. Ne jamais publier d'avis sur le site sans source réelle.

**Mentions** : presse locale, guides (Petit Futé et autres), blogs de voyage. Chaque mention sur un site sérieux renforce la marque auprès de Google et des moteurs d'IA.

## 9. Bascule depuis le site actuel

Le site actuel (confiserie-schiesser.ch) apparaît déjà dans Google. D'après son titre (« Confiserei Schiesser Basel »), il semble rédigé en allemand.

1. **Langue** : Bâle cherche surtout en allemand (« Konditorei Basel », « Confiserie Basel »). Si le nouveau site français remplace l'ancien sur le même domaine, les positions en allemand risquent de baisser. Le plus sûr : prévoir la version allemande (Polylang) avant la bascule, ou au moins la garder en ligne.
2. **Redirections** : relever toutes les adresses de l'ancien site (plan du site, Search Console), puis créer une redirection 301 de chacune vers la page nouvelle la plus proche (Rank Math → Redirections). Aucune ancienne adresse ne doit finir en erreur 404.
3. **Après la bascule** : envoyer le nouveau plan du site dans Search Console et surveiller les pages en erreur pendant quatre à six semaines.

## 10. Pour aller plus loin

| Action | Pourquoi | Indicateur à suivre | Signe que ça ne marche pas |
|---|---|---|---|
| Vraies photos avec nom de fichier et texte alternatif descriptifs | Les résultats locaux valorisent les photos ; elles prouvent le savoir-faire | Vues des photos sur la fiche Google, clics sur les images | Aucune évolution des vues après trois mois |
| Page Notre histoire : personnes, archives, presse | Expertise et confiance (E-E-A-T), citations par les IA | Recherches sur le nom de la maison dans Search Console | La page reste sans impressions |
| Guide « Le Läckerli de Bâle : histoire, recette et où l'acheter » | La recherche « Läckerli » est surtout informative : un guide y répond mieux qu'une fiche produit | Impressions sur « Läckerli » et « Basler Läckerli » | Le guide et la fiche se concurrencent (même requête, positions qui alternent) |
| Prix de départ du gâteau sur commande | Donnée produit valide, visiteur rassuré | Clics sur « Commander » depuis la fiche | Pas plus de demandes qu'avant |
| Version allemande (et anglaise) avec balises hreflang | Capter la recherche locale bâloise | Impressions en allemand dans Search Console | Les deux versions se concurrencent : vérifier les balises hreflang |
| Performance chez l'hébergeur : cache, images WebP ou AVIF | Core Web Vitals (LCP, INP, CLS) | Rapport Core Web Vitals de Search Console | Pages classées « à améliorer » sur mobile |

**Robots d'IA** : tous sont autorisés, ce qui est conseillé pour une maison qui veut être connue. Pour refuser seulement l'entraînement des modèles sans perdre la visibilité dans les réponses des moteurs, ajouter dans Rank Math → Modifier robots.txt :

```
User-agent: GPTBot
User-agent: ClaudeBot
User-agent: Google-Extended
User-agent: Applebot-Extended
User-agent: CCBot
Disallow: /
```

Ne jamais bloquer Googlebot, Bingbot, OAI-SearchBot, Claude-SearchBot, PerplexityBot ni Applebot. Vérifier aussi que l'hébergeur ou Cloudflare ne bloque pas ces robots par défaut.

**Fichier llms.txt** : facultatif et ignoré par Google, priorité basse.

## 11. Vérifier après la mise en ligne

- Test des résultats enrichis de Google et validateur schema.org sur l'accueil, la boutique, une fiche produit et la page Nous visiter.
- Search Console : pages indexées, plan du site, Core Web Vitals (LCP, INP, CLS), requêtes « confiserie Bâle », « Läckerli Bâle », « salon de thé Bâle », « chocolats Bâle ».
- Rank Math : un score de 80 ou plus sur chaque page principale.
- Fiche Google : appels, demandes d'itinéraire et clics vers le site, chaque mois.

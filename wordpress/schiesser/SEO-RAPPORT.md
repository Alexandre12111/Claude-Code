# Rapport SEO : Confiserie Schiesser

Thème Schiesser 0.11. État vérifié le 1er octobre 2026 sur le site de test (WordPress 6.5, contenu importé par « Passer le site en allemand »), page par page, avec la liste des produits en préparation puis en ligne.

## 1. En résumé

- **Le site public est en allemand (Suisse)** : textes, adresses des pages, titres SEO, descriptions, données structurées et langue déclarée à Google (`de-CH`). L'administration reste en français.
- **Chaque page et chaque produit arrive avec son titre SEO, sa méta description et 5 mots-clés** (le premier est le principal) déjà saisis dans Rank Math. Les longueurs suivent la règle maison : titres de 47 à 60 caractères, descriptions de 129 à 157.
- **Les données structurées sont valides** sur toutes les pages, avec ou sans Rank Math. Le thème complète la fiche de Rank Math au lieu d'en créer une seconde.
- **La liste des produits est « en préparation »** tant que les photos manquent : les fiches produits ne sont ni visibles ni indexées. Elles entrent dans le plan du site au clic sur « Tout est prêt ».
- **Ce qui reste dépend de la maison** : vraies photos, faits à valider, Impressum, fiche Google, avis et annuaires (sections 7 à 9).
- **Objectif Rank Math** : un score de 80 à 90 sur les pages principales, sans chercher 100 à tout prix.

## 2. Langue et adresses

- **Langue** : toutes les pages publiques sont servies en `de_CH` (balise `lang="de-CH"`, `og:locale`, `inLanguage` des données structurées), quel que soit le réglage de langue de l'administration. Il n'y a qu'une langue publique : aucune balise hreflang n'est nécessaire.
- **Dates et heures** à la suisse alémanique : « Dienstag, 29. September », « 7.30–18.30 Uhr ». Jours fériés de Bâle en allemand (Fasnacht, Auffahrt, Bundesfeier…).
- **Adresses courtes et allemandes** :

| Page | Adresse | Ancienne adresse (redirigée en 301) |
|---|---|---|
| Startseite | `/` | `/accueil/` |
| Confiserie | `/confiserie/` | `/boutique/`, `/la-boutique/` |
| Tea Room | `/tea-room/` | `/salon-de-the/` |
| Geschichte | `/geschichte/` | `/notre-histoire/`, `/histoire/` |
| Besuch | `/besuch/` | `/nous-visiter/`, `/visiter/` |
| Kontakt | `/kontakt/` | `/contact/` |
| Firmengeschenke | `/firmengeschenke/` | `/cadeaux-entreprise/` |
| Impressum | `/impressum/` | `/mentions-legales/` |
| Fiches produits | `/produkte/nom-du-produit/` | `/produits/…` |

Les redirections sont faites par le thème, sans réglage. Elles ont été vérifiées une par une (code 301 vers la bonne page).

### Versions française et anglaise (0.11, avec Polylang)

- L'allemand reste la langue principale (adresses sans préfixe). Avec Polylang réglé sur « nom de dossier » et « cacher le code de la langue par défaut », le français est servi sous `/fr/` et l'anglais sous `/en/` ; chaque page déclare sa langue (`fr-FR`, `en-GB`) et Polylang ajoute les balises `hreflang` entre les trois versions.
- Adresses françaises : `/fr/accueil/` (accueil `/fr/`), `/fr/boutique/`, `/fr/salon-de-the/`, `/fr/notre-histoire/`, `/fr/nous-visiter/`, `/fr/contact/`, `/fr/cadeaux-entreprise/`. Adresses anglaises : `/en/`, `/en/confectionery/`, `/en/tearoom/`, `/en/history/`, `/en/visit-us/`, `/en/contact-us/`, `/en/corporate-gifts/`. Les anciennes adresses françaises sans préfixe (`/salon-de-the/`…) redirigent en 301 vers la page française.
- Chaque page traduite a son titre SEO (50 à 60 caractères), sa description (140 à 160 caractères) et 5 mots-clés dans sa langue, par exemple `salon de thé Bâle`, `confiserie Bâle`, `tea room Basel`, `confectionery Basel`. Les 70 produits ont aussi leur SEO en français et en anglais.
- Les textes ont été rédigés à la main pour chaque langue (pas de traduction automatique). Liens internes, maillage « À découvrir aussi », menus et carte PDF suivent la langue de la page.
- Vérifié sur le site de test : aucune erreur d'accessibilité (axe) sur les pages FR et EN, aucun bloc invalide dans l'éditeur, aucun reste d'allemand hors noms propres (Läckerli, Rathstübli, Kaffee Lutz…).

## 3. Carte des mots-clés

Un mot-clé principal par page, placé dans le titre SEO, la méta description et l'introduction. Le H1 le contient ou en reprend la variante naturelle. Les mots-clés secondaires sont déjà saisis dans Rank Math. Les titres suivent la règle « Mot-clé | Proposition – Marque ».

| Page | Mot-clé principal | Secondaires | Titre SEO (caractères) | Description | H1 |
|---|---|---|---|---|---|
| Startseite | Confiserie Basel | Tea Room Basel, Confiserie Schiesser, Kaffeehaus Basel, Basler Läckerli | Confiserie in Basel \| Tea Room seit 1870 – Schiesser (52) | 155 | Confiserie in Basel, seit 1870 am Marktplatz |
| Confiserie | Läckerli Basel | Pralinen Basel, Basler Läckerli kaufen, Confiserie Marktplatz Basel, Torten Basel | Läckerli & Pralinen Basel \| Confiserie Schiesser (48) | 143 | Confiserie in Basel, aus der eigenen Backstube |
| Tea Room | Tea Room Basel | Kaffeehaus Basel, Frühstück Basel Marktplatz, ältestes Kaffeehaus der Schweiz, Café Marktplatz Basel | Tea Room Basel \| Ältestes Kaffeehaus der Schweiz – Schiesser (60) | 143 | Tea Room in Basel, über dem Marktplatz |
| Geschichte | Basler Confiserie seit 1870 | Confiserie Schiesser Geschichte, Rudolf Schiesser, Rathstübli Basel, Geschichte Marktplatz Basel | Geschichte seit 1870 \| Basler Confiserie – Schiesser (52) | 153 | Seit 1870 am Basler Marktplatz |
| Besuch | Confiserie Marktplatz Basel | Öffnungszeiten Confiserie Schiesser, Confiserie Schiesser Adresse, Anfahrt Marktplatz Basel, Confiserie beim Rathaus Basel | Öffnungszeiten & Anfahrt \| Confiserie Marktplatz Basel (54) | 133 | Confiserie am Marktplatz, mitten in Basel |
| Kontakt | Confiserie Schiesser Kontakt | Torte bestellen Basel, Pralinen bestellen Basel, Tea Room Basel reservieren, Geschenkbox Basel | Kontakt & Bestellung \| Confiserie Schiesser Basel (49) | 157 | Kontakt zur Confiserie Schiesser |
| Firmengeschenke | Firmengeschenke Basel | Kundengeschenke Basel, Pralinen Firmengeschenk, Läckerli Geschenkbox, Weihnachtsgeschenke Firmen Basel | Firmengeschenke Basel \| Pralinen & Läckerli – Schiesser (55) | 144 | Firmengeschenke aus Basel, von Hand gemacht |
| Impressum | Impressum | | Impressum \| Confiserie Schiesser (32) | 131 | Impressum |

**Fiches produits (70)** : réglages préparés automatiquement à partir de la liste de prix, puis modifiables dans Rank Math.
- Titre : « Basler Läckerli | Confiserie Schiesser am Marktplatz Basel », raccourci en « … | Confiserie Schiesser Basel » quand le nom est long (47 à 60 caractères).
- Description : nom, maison, lieu, formats et prix de départ, retrait en boutique et commande par téléphone (129 à 157 caractères).
- 5 mots-clés : le nom du produit (principal), le nom avec la ville (ou « Geschenk » s'il contient déjà Basel), le nom avec « kaufen », le thème de la catégorie (« Patisserie Basel », « Basler Spezialitäten »…) et « Confiserie Schiesser » avec le nom. Exemple : Pralinen, Pralinen Basel, Pralinen kaufen, Confiserie Basel, Confiserie Schiesser Pralinen.
- Un réglage modifié à la main n'est jamais écrasé par un nouvel import.

Rank Math signalera « mot-clé absent de l'adresse » sur quelques pages (Geschichte, Besuch, Kontakt) : c'est voulu, des adresses courtes et claires valent mieux qu'un mot-clé de plus.

## 4. Données structurées par page

| Page | Données publiées |
|---|---|
| Toutes les pages | Établissement (Bakery et CafeOrCoffeeShop) et site (WebSite) |
| Startseite | WebPage, questions (FAQPage) ; liste de produits (ItemList) dès que la liste est en ligne |
| Confiserie | CollectionPage, fil d'Ariane, questions ; liste de produits dès que la liste est en ligne |
| Tea Room | WebPage, fil d'Ariane, questions, carte (Menu : 8 rubriques, 78 articles avec prix et régimes) |
| Geschichte | AboutPage, fil d'Ariane |
| Besuch | WebPage, fil d'Ariane, questions |
| Kontakt | ContactPage, fil d'Ariane, questions |
| Firmengeschenke | WebPage, fil d'Ariane, questions |
| Fiches produits | ItemPage, fil d'Ariane, Product. Un seul format : Offer avec prix. Plusieurs formats : AggregateOffer avec prix le plus bas, le plus haut et nombre de formats (par exemple Basler Läckerli, 3 formats de CHF 8.90 à 39.50). Vente en boutique (InStoreOnly). |

- **Fiche établissement** construite depuis Réglages maison : nom, adresse avec canton (Marktplatz 19, 4051 Basel, Basel-Stadt), position GPS, téléphone, e-mail, horaires regroupés, horaires spéciaux des 90 prochains jours (jours fériés et fermetures), gamme de prix, devise, année de fondation, description, profils officiels, logo et carte du Tea Room.
- **Avec Rank Math** : même identifiant que la fiche de Rank Math, « Article » ajouté par défaut retiré des pages, description, langue, dates, image principale et fil d'Ariane ajoutés à chaque page.
- **Questions fréquentes** : le balisage FAQPage décrit fidèlement le contenu visible et reste en place. Google n'affiche plus de résultats enrichis FAQ depuis le 7 mai 2026 : aucun gain d'affichage n'est à en attendre.
- **Gamme de prix** : réglée par défaut sur « CHF 2–30 », ce qui correspond à la plupart des articles (prix médian CHF 9.50). À ajuster dans Réglages maison si la maison préfère une autre fourchette.

## 5. Technique

- **Indexation** : recherche interne, page introuvable et archives vides en `noindex, follow` (sans Rank Math ; avec Rank Math, ses propres réglages s'appliquent). Le statut « Geöffnet / Geschlossen » est exclu des extraits (`data-nosnippet`) pour qu'aucun moteur ne cite un état périmé.
- **Plan du site** : les 7 pages publiées. Les fiches produits et leurs catégories en sont exclues tant que la liste est en préparation (plan du site de WordPress et de Rank Math), puis y entrent automatiquement.
- **Fiches produits en préparation** : redirection temporaire (302) des visiteurs vers la page Confiserie, qui affiche « Produktliste folgt in Kürze » avec l'aperçu des rubriques. Google n'indexe donc pas de fiches sans photo.
- **Titres de WordPress** en allemand : « Seite nicht gefunden », « Suchergebnisse für … ».
- **Images** : photo principale chargée en priorité (préchargement), les autres au défilement, texte alternatif en allemand sur toutes les images importées, point d'intérêt respecté dans les recadrages.
- **En-tête allégé** : version de WordPress masquée, liens techniques inutiles retirés, polices hébergées dans le thème.
- **Accessibilité** : 0 erreur (axe) sur les 7 pages, 1 seul H1 par page, titres de section sans doublon.
- **Éditeur** : tous les blocs des 7 pages s'ouvrent sans erreur.

## 6. Contenu

- **Pages réécrites en allemand**, sur la base des faits de la carte du Tea Room : fondation en 1870 par le confiseur glaronais Rudolf Schiesser, façade néogothique sur le modèle du Rathaus, Tea Room et « Rathstübli » aménagés par son fils Hans, « ältestes Kaffeehaus der Schweiz », recettes des années de fondation.
- **Deux photos d'archives** (façade en 1889, Marktplatz vers 1900) avec légende et texte alternatif.
- **Liste de prix** : 70 produits en 8 rubriques, avec formats et prix. **Carte du Tea Room** : 78 articles en 8 rubriques.
- **Maillage interne** :
  - liens dans les textes et les réponses des questions fréquentes, avec des textes de lien descriptifs (« Confiserie am Basler Marktplatz », « Firmengeschenke aus Basel », « Öffnungszeiten und Anfahrt »…) ;
  - section **« Weiter im Haus »** en bas de chaque page : trois cartes vers les pages les plus utiles depuis la page en cours ;
  - le pied de page reprend toutes les pages principales et les sections de la page (« Auf dieser Seite »).
- **Tea Room** : la carte est la première section de la page (« Die Karte des Tea Room »), avec la carte complète en PDF.
- **Section « Das Haus in Kürze »** sur la Startseite : qui, quoi, où, depuis quand, horaires. Utile aux moteurs de recherche par IA, qui citent volontiers ce type de résumé.

## 7. À faire à la mise en ligne (environ 20 minutes)

1. **Réglages → Général** : Fuseau horaire « Zurich », Titre du site « Confiserie Schiesser ». La langue choisie ici ne règle que l'administration ; le site public reste en allemand.
2. **Réglages → Permaliens** : cliquer sur Enregistrer (adresses `/produkte/`).
3. **Réglages → Lecture** : décocher « Demander aux moteurs de recherche de ne pas indexer ce site ».
4. **Réglages maison** : vérifier l'adresse (exactement celle de la fiche Google), le téléphone, la position GPS à 5 décimales, le lien de la fiche Google, la raison sociale, le numéro IDE et les profils officiels (Instagram, Facebook, fiche Google, Tripadvisor…). Écrire la présentation et la vitrine en allemand.
5. **Apparence → Personnaliser → Identité du site** : logo carré d'au moins 112 × 112 pixels et icône du site.
6. **Rank Math** :
   - assistant en mode Facile, type « Petite entreprise », nom « Confiserie Schiesser », logo ;
   - ne pas ressaisir l'adresse et les horaires dans le module SEO local : le thème les fournit déjà, une double saisie créerait des informations contradictoires ;
   - plan du site : Pages et Produits, sans les Articles ;
   - modules Redirections et Instant Indexing (pour Bing) ;
   - connexion à Google Search Console et à Bing Webmaster Tools, puis envoi du plan du site.
7. **Tableau de bord** : bouton « Installer la langue allemande » (traduit les rares textes de WordPress et de Rank Math visibles sur le site).
8. **Publier** l'Impressum (brouillon créé par l'import) et la Datenschutzerklärung (nLPD), en y mentionnant la carte Google Maps et le formulaire de contact.
9. **E-mails** : installer WP Mail SMTP avec une boîte Groupware2go, puis envoyer un message de test avec le formulaire.
10. **Quand les photos sont prêtes** : Produits boutique → Mise en ligne → « Tout est prêt : afficher la liste des produits ».

## 8. Faits et contenus à faire valider par la maison

Google et les moteurs d'IA recoupent les faits avec d'autres sources : ils doivent être exacts.

1. **Adresse** : le site indique « Marktplatz 19, 4051 Basel ». L'utiliser à l'identique partout (site, fiche Google, annuaires).
2. **Histoire** : la date des travaux de Hans Schiesser (façade, Tea Room) ne figure pas dans la carte ; le site écrit « Nach 1900 ». Préciser si la maison connaît la date.
3. **« Ältestes Kaffeehaus der Schweiz »** : la formule vient de la carte de la maison. La réponse de la FAQ de la Startseite reste prudente (« wohl nirgends sonst so weitgehend im Original erhalten ») ; à confirmer.
4. **Liste de prix** : noms, formats et prix. Fautes de frappe corrigées à l'import : Thon, Buttergipfeli, Sackmesser, Liqueur, Bonbonnière. Orthographe de la maison conservée : Makröndli, Wurstwegge, Griots…
5. **Carte du Tea Room** : corrections Allegrini, Ecuador, Vanille, Hagebutte, Grand Cru.
6. **Allergènes** : à renseigner produit par produit (boîte « Allergènes et régimes »). Un produit non renseigné n'apparaît pas dans les filtres « Ohne … ».
7. **Exemples à remplacer** : durées des trajets, chiffres d'affluence par heure, photos d'illustration (Unsplash) des pages et du Tea Room.

## 9. À faire hors du site

**Fiche Google Business Profile** (le levier local le plus important)
- Nom, adresse et téléphone exactement identiques au site.
- Catégorie principale « Konditorei » ou « Confiserie » ; catégories secondaires « Café », « Teestube », « Chocolatier ».
- Attributs, photos réelles (façade, vitrine, Tea Room, produits), lien vers la carte du Tea Room (`/tea-room/#karte`).
- Un post toutes les deux à trois semaines.

**Annuaires** avec exactement le même nom, la même adresse et le même téléphone : local.ch, search.ch, Tripadvisor (important pour le Tea Room), Basel Tourismus (basel.com), Bing Places, Apple Business Connect, Yelp.

**Avis** : QR code en caisse et au Tea Room, demande régulière, réponse à chaque avis sous 48 heures. Le bloc « Avis clients » du site affiche automatiquement les avis Google une fois la fiche reliée dans Réglages maison.

**Mentions** : presse locale, guides de Bâle, blogs de voyage. Chaque mention sur un site sérieux renforce la marque auprès de Google et des moteurs d'IA.

## 10. Bascule depuis l'ancien site

Le domaine confiserie-schiesser.ch était déjà indexé par Google (ancien site en allemand, hébergé chez Metanet).

1. **Avant la bascule** : relever les adresses de l'ancien site (plan du site, Search Console, ou recherche `site:confiserie-schiesser.ch` dans Google).
2. **Redirections** : créer dans Rank Math → Redirections une redirection 301 de chaque ancienne adresse vers la page nouvelle la plus proche. Aucune ancienne adresse ne doit finir en erreur 404.
3. **DNS** : le site doit avoir ses enregistrements A (et `www`) chez Infomaniak ; les enregistrements e-mail (MX, SPF, DKIM, DMARC) restent ceux de Groupware2go.
4. **Après la bascule** : envoyer le plan du site dans Search Console et surveiller les pages en erreur pendant quatre à six semaines.

## 11. Pour aller plus loin

| Action | Pourquoi | Indicateur à suivre | Signe que ça ne marche pas |
|---|---|---|---|
| Vraies photos des produits, nom de fichier et texte alternatif en allemand | Les fiches deviennent visibles ; les résultats locaux valorisent les photos | Pages produits indexées dans Search Console | Fiches « explorées, non indexées » |
| Guide « Basler Läckerli : Geschichte, Rezept und wo kaufen » | La recherche « Läckerli » est surtout informative : un guide y répond mieux qu'une fiche | Impressions sur « Basler Läckerli » | Le guide et la fiche se concurrencent |
| Page Geschichte enrichie : personnes, archives, presse | Expertise et confiance (E-E-A-T), citations par les IA | Recherches sur le nom de la maison | La page reste sans impressions |
| Version française ou anglaise (touristes, Bâle bilingue) | Capter les visiteurs non germanophones | Impressions hors Suisse alémanique | Les versions se concurrencent : vérifier les balises hreflang |
| Performance chez Infomaniak : cache, images WebP ou AVIF | Core Web Vitals (LCP, INP, CLS) | Rapport Core Web Vitals de Search Console | Pages « à améliorer » sur mobile |

**Robots d'IA** : tous sont autorisés, ce qui est conseillé pour une maison qui veut être connue. Pour refuser seulement l'entraînement des modèles sans perdre la visibilité dans les réponses des moteurs, ajouter dans Rank Math → Modifier robots.txt :

```
User-agent: GPTBot
User-agent: ClaudeBot
User-agent: Google-Extended
User-agent: Applebot-Extended
User-agent: CCBot
Disallow: /
```

Ne jamais bloquer Googlebot, Bingbot, OAI-SearchBot, Claude-SearchBot, PerplexityBot ni Applebot.

## 12. Vérifier après la mise en ligne

- Test des résultats enrichis de Google et validateur schema.org sur la Startseite, la page Confiserie, la page Tea Room, une fiche produit et la page Besuch.
- Search Console : pages indexées, plan du site, Core Web Vitals, requêtes « Confiserie Basel », « Läckerli Basel », « Tea Room Basel », « Pralinen Basel ».
- Rank Math : un score de 80 ou plus sur chaque page principale.
- Fiche Google : appels, demandes d'itinéraire et clics vers le site, chaque mois.

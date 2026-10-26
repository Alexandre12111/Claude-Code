# Audit des données structurées (Schema.org) — aurea-media.fr

Analyse du JSON-LD et des microdonnées présents sur les 50 URL crawlées (dossier `crawl/html/*.html`), en priorité l'accueil, les pages services, la page agence, la page contact, la page réalisations, la page blog et un échantillon d'articles avec FAQ.

## Score global : 60/100

Le site est loin d'être vierge de données structurées : contrairement à ce que laissait supposer un premier survol, la majorité des pages clés (accueil, 3 pages services, page agence, contact, nos services, réalisations, blog, et 30 articles) portent déjà un `@graph` JSON-LD construit avec de bons réflexes (`@id` stables, réutilisation de l'entité organisation par référence, BreadcrumbList systématique, FAQ posée en microdonnées propres plutôt qu'en JSON-LD dupliqué). C'est ce socle qui sauve la note.

Elle est plafonnée à 60 par une série de défauts sérieux et concentrés : deux pages avec un JSON-LD invalide au sens strict (accolade manquante, bloc mal fermé), une note moyenne (AggregateRating) auto-déclarée sur l'entreprise elle-même et répétée avec trois valeurs différentes sur trois pages distinctes, quatre déclarations de type LocalBusiness concurrentes et incohérentes entre elles (jusqu'à un numéro de téléphone laissé en placeholder), des prix contradictoires entre un ancien article de blog et les pages services actuelles, du HowTo déprécié encore en place sur treize articles, et l'absence de la propriété `image` sur l'intégralité des trente BlogPosting.

## Constats par priorité

### Critique

1. **JSON-LD invalide sur deux pages.** `anatomie-site-vitrine-qui-convertit.html` contient un objet `ListItem` jamais refermé avant l'ouverture du bloc `FAQPage` suivant (erreur de parsing dès le caractère 3849). `refonte-identite-visuelle-entreprise.html` contient une accolade manquante après le premier `acceptedAnswer` du `FAQPage` (erreur dès le caractère 2097). Dans les deux cas, `JSON.parse` échoue intégralement : Google Rich Results Test et tout parseur strict rejettent la totalité du bloc, donc BlogPosting, FAQPage et BreadcrumbList sont perdus sur ces deux pages, pas seulement la partie fautive. Pourquoi c'est grave : un bloc invalide vaut un bloc absent, avec en plus un signal de mauvaise qualité technique. Action pour la refonte : régénérer ces deux blocs avec le nouveau moteur de templating plutôt que corriger le HTML actuel à la main. Vérification : coller l'URL dans le Rich Results Test de Google et confirmer zéro erreur de syntaxe.

2. **AggregateRating auto-déclaré sur la propre entité de l'agence, avec trois valeurs différentes.** L'accueil déclare `ratingValue: 4.9` / `reviewCount: 39` sur l'organisation (`#organization`, type ProfessionalService). `creation-site-internet-clamart.html` et `site-internet-boulogne-billancourt.html` redéclarent une entité LocalBusiness pour la même entreprise avec `ratingValue: 5` / `reviewCount: 40` / `bestRating: 5`. Deux problèmes distincts et cumulés : d'une part ces trois chiffres ne concordent pas alors qu'ils décrivent censément la même réalité (39 avis un jour, 40 un autre, jamais mis à jour ensemble) ; d'autre part, et c'est le point le plus important, les consignes de Google sur les extraits d'avis interdisent explicitement le balisage d'avis ou de note globale à propos de sa propre entité sur son propre site (« self-serving reviews »). Ce balisage n'a jamais généré et ne générera jamais d'étoiles dans les résultats Google, et son caractère auto-déclaré (donc invérifiable par un tiers) l'expose à un risque de action manuelle « données structurées trompeuses » si Google l'examine. Action : supprimer purement et simplement `aggregateRating`/`review` de toutes les entités Organization/LocalBusiness dans la refonte. Les avis Google restent visibles nativement dans Maps et dans le pack local sans qu'aucun balisage ne soit nécessaire côté site. Vérification : recherche plein texte de `aggregateRating` sur l'ensemble du nouveau gabarit, résultat attendu zéro occurrence.

3. **Quatre déclarations LocalBusiness concurrentes et incohérentes pour la même entreprise.** En plus du bloc légitime de l'accueil (`ProfessionalService`, `@id #organization`), quatre articles de blog embarquent chacun leur propre copie complète de l'entreprise sous forme de `LocalBusiness` : `choisir-agence-web-paris.html` (`@id #localbusiness`), `creation-site-internet-clamart.html` (`@id #organization`, donc même `@id` que l'accueil mais avec un `@type` différent et des données différentes), `google-my-business-artisans-tpe.html` (`@id #localbusiness`), `site-internet-boulogne-billancourt.html` (`@id #organization`). Constats aggravants : les coordonnées GPS diffèrent sur les quatre blocs (48.8719/2.3076, 48.8722/2.3043, 48.8722/2.2994) sans qu'aucune ne soit signalée comme la valeur de référence ; le catalogue de prix intégré dans `choisir-agence-web-paris.html` (`hasOfferCatalog`) annonce un site vitrine « à partir de 1 200 € HT » et un e-commerce « à partir de 2 000 € HT », des montants qui ne correspondent ni aux tarifs actuels de l'agence (1 000 € et 1 650 € HT) ni aux prix affichés en JSON-LD et en microdonnées sur les vraies pages services ; et le téléphone de `choisir-agence-web-paris.html` est un placeholder resté en production : `"telephone": "+33-1-XX-XX-XX-XX"`. Pourquoi c'est grave : dupliquer l'entité de l'entreprise viole la règle Google « ne créez pas plusieurs fiches pour la même entreprise », un numéro de téléphone factice publié en donnée structurée est une donnée fausse au sens propre du terme, et des prix contradictoires entre pages nuisent à la cohérence perçue par Google comme par les moteurs IA qui lisent ces prix. Action : dans la refonte, aucune page de contenu (article, page locale) ne doit plus jamais réembarquer l'entité entreprise en entier ; elle doit systématiquement la référencer par `{"@id": "https://aurea-media.fr/#organization"}`, comme le font déjà correctement les pages services. Vérification : recherche de `"@type": "LocalBusiness"` et `"@type": "ProfessionalService"` dans tout le nouveau code, résultat attendu une seule occurrence de définition complète (sur l'accueil), toutes les autres pages ne doivent contenir que des références `@id`.

4. **Prix contradictoires entre les données structurées.** Trois jeux de prix coexistent pour les mêmes prestations : 1 000 €/1 800 € HT (Essentiel/Professionnel) sur `creation-de-site-vitrine-paris.html`, cohérent avec les microdonnées Offer de la même page et avec les tarifs annoncés ailleurs sur le site ; 1 650 €/3 200 € HT (Lancement/Croissance) sur `creation-de-site-e-commerce-paris.html`, cohérent également ; et 1 200 €/2 000 €/800 € HT dans le `hasOfferCatalog` de l'article `choisir-agence-web-paris.html`, qui ne correspond à rien d'actuel. Action : voir point 3, la correction est la même (référence par `@id`, plus de prix dupliqués en dur dans les articles).

### Haute

5. **HowTo présent sur treize articles, type déprécié depuis septembre 2023.** Détecté sur `choisir-agence-web-paris.html`, `combien-coute-un-site-e-commerce.html`, `creer-un-site-e-commerce-professionnel.html`, `creer-un-site-vitrine-soi-meme-ou-en-agence.html`, `google-my-business-artisans-tpe.html`, `logo-charte-graphique-identite-visuelle.html`, `optimiser-sa-fiche-google-my-business.html`, `refonte-site-internet.html`, `site-vitrine-architecte.html`, `site-vitrine-avocat.html`, `site-vitrine-kinesitherapeute.html`, `site-vitrine-medecin.html`, `site-vitrine-restaurant.html`. Ce balisage ne génère plus aucun résultat enrichi et n'a donc plus aucune utilité, seulement du poids de page et un risque de confusion pour la prochaine personne qui maintiendra le thème. Action : ne pas migrer ce bloc dans la refonte, le contenu texte des étapes peut rester en HTML classique. Vérification : recherche de `HowTo` dans le nouveau gabarit, résultat attendu zéro occurrence.

6. **Trente BlogPosting sur trente n'ont pas de propriété `image`.** Vérifié sur l'ensemble des articles avec BlogPosting en JSON-LD. L'image est une propriété fortement recommandée par Google pour l'éligibilité Article/Discover. Action : ajouter systématiquement `image` (idéalement une URL en 1200 px de large, ratio 16:9, 4:3 ou 1:1) dans le nouveau gabarit d'article, alimentée par l'image mise en avant de l'article. Vérification : contrôle du champ `image` sur un échantillon d'articles dans le Rich Results Test après mise en ligne.

7. **Aucune entité WebSite sur l'ensemble du site.** Aucune des 50 pages crawlées ne déclare de type `WebSite`, donc pas de `potentialAction` SearchAction, pas d'entité racine à laquelle rattacher les pages via `isPartOf`. Action : ajouter un bloc WebSite unique, référencé par `@id` depuis toutes les pages. Voir modèle plus bas.

### Moyenne

8. **Adresse au 60 rue François Ier : vérifier s'il s'agit d'une domiciliation.** Le brief indique que cette adresse peut être une simple domiciliation et non un lieu physiquement tenu. Si c'est le cas, publier des horaires d'ouverture (`openingHoursSpecification`) directement sur l'entité LocalBusiness/ProfessionalService laisse entendre à tort qu'un accueil physique y est assuré de 9h à 19h, ce que dément d'ailleurs le contenu même du site (la FAQ de `agence-web-hauts-de-seine.html` précise que le suivi se fait majoritairement en visioconférence et qu'un rendez-vous en présentiel n'est que ponctuel). Recommandation retenue pour la refonte : conserver l'adresse dans `address` (cohérence NAP utile pour la fiche Google Business Profile et le SIREN), mais déplacer les horaires dans un `contactPoint.hoursAvailable` qui représente une disponibilité de contact (téléphone, visio) plutôt qu'un horaire d'accueil sur place, et ne pas ajouter `hasMap` ni de mise en avant d'un lieu visitable. À confirmer avec le client avant publication : si des rendez-vous physiques sont réellement tenus à cette adresse, `openingHoursSpecification` directe redevient légitime.

9. **`@id` de la personne (Alexandre Coury) ancré sur une URL de page plutôt que sur la racine du domaine.** Actuellement `https://aurea-media.fr/agence-web-a-paris/#alexandre`. Si le slug de la page « agence » change lors de la refonte, cet `@id` change aussi et casse toutes les références existantes (auteur d'articles, `worksFor`, etc.). Action : dans la refonte, ancrer les `@id` des entités durables (organisation, site, personne) sur la racine du domaine plutôt que sur une URL de page, comme détaillé dans la section conception ci-dessous.

10. **Incohérences mineures de contenu entre blocs.** Le `jobTitle` d'Alexandre Coury varie entre « Fondateur » (`agence-web-hauts-de-seine.html`) et « Fondateur & Directeur créatif » (page agence et majorité des articles). Le `founder` de l'accueil est une copie intégrale sans `@id` alors qu'une entité Person complète avec `@id` existe déjà sur la page agence. `dateModified` est systématiquement identique à `datePublished` sur les articles échantillonnés, ce qui indique une valeur générée automatiquement plutôt qu'un vrai suivi de mise à jour. Action : harmoniser le `jobTitle`, référencer le `founder` par `@id`, et faire en sorte que `dateModified` reflète une vraie modification de contenu après la refonte.

11. **Deux `Offer` en microdonnées sans entité Product/Service parente.** Sur `creation-de-site-vitrine-paris.html` et `creation-de-site-e-commerce-paris.html`, les cartes de tarifs portent des microdonnées `itemscope itemtype="https://schema.org/Offer"` autonomes, sans `Product` ni `Service` englobant. Une entité Offer isolée n'est pas éligible aux résultats enrichis Google (qui exigent une Offer imbriquée dans un Product ou associée à un Service via `offers`). Les mêmes prix existent heureusement déjà correctement structurés dans le JSON-LD Service de la même page, donc rien n'est perdu côté Google, mais ce doublon microdonnées/JSON-LD est un entretien inutile à ne pas reconduire. Action : dans la refonte, ne garder que le JSON-LD Service + Offer, supprimer les microdonnées Offer autonomes du HTML des cartes tarifaires.

### Basse / Info

12. **FAQPage présent (JSON-LD et microdonnées) sur l'accueil, l'agence, le contact, nos services, branding, maquettes et une trentaine d'articles.** Conformément à la politique Google (plus aucun résultat enrichi FAQ depuis le 7 mai 2026, y compris hors gouvernement/santé), ce balisage existant ne présente plus de bénéfice SERP avéré. Un bénéfice IA/GEO (citation dans des réponses d'assistants) reste possible mais non confirmé. Priorité Info uniquement : pas d'urgence à retirer ce qui existe, mais ne pas en ajouter sur de nouvelles pages dans l'optique d'un gain Google, et ne compter dessus que si le client accepte l'incertitude côté IA/GEO. Un commentaire HTML interne laissé sur l'accueil (« ne pas ajouter de schéma FAQ dans Rank Math, il est déjà dans le HTML ») montre que l'équipe précédente avait déjà connaissance du risque de doublon, bon réflexe à conserver dans la refonte quel que soit l'outil utilisé.

13. **Pages sans aucune donnée structurée.** Les pages de catégorie WordPress `creation-site-vitrine.html`, `creation-site-ecommerce.html`, `creation-site-web.html`, `google-my-business-seo-local.html`, ainsi que `mentions-legales.html`, `politique-de-confidentialite.html` et `plan-du-site-aurea-media-paris.html`, n'ont aucun schéma. Ces pages de catégorie sont par ailleurs déjà identifiées comme candidates à la consolidation dans la refonte (pas de H1, contenu fin) : ne pas investir de schéma dessus tant que leur sort dans la nouvelle architecture n'est pas tranché. Pour les pages légales, un simple `WebPage` générique rattaché au site suffit, sans urgence.

## Détail page par page (état actuel)

| Page | JSON-LD | Microdonnées | Statut |
|---|---|---|---|
| Accueil | ProfessionalService, AggregateRating, Person, PostalAddress, GeoCoordinates | FAQPage, Question, Answer | Valide mais AggregateRating auto-déclaré, pas de WebSite, `founder` sans `@id`, pas d'`openingHoursSpecification` |
| Agence (`agence-web-a-paris`) | Person, AboutPage, BreadcrumbList | FAQPage, Question, Answer | Valide, bon modèle à répliquer, `@id` Person à stabiliser |
| Contact (`contact-agence`) | ContactPage, BreadcrumbList | FAQPage, Question, Answer | Valide, minimal, pourrait gagner un `contactPoint` |
| Nos services | CollectionPage, ItemList, BreadcrumbList | FAQPage, Question, Answer | Valide, bonne réutilisation des `@id` de Service |
| Réalisations | CollectionPage, ItemList (21 CreativeWork), BreadcrumbList | aucune | Valide |
| Blog (`nos-conseils-web`) | Blog, BreadcrumbList | aucune | Valide |
| Création site vitrine Paris | Service, Offer x2, BreadcrumbList | FAQPage, Question, Answer, Offer x2 (redondant) | Valide, prix cohérents |
| Création site e-commerce Paris | Service, Offer x2, BreadcrumbList | FAQPage, Question, Answer, Offer x2 (redondant) | Valide, prix cohérents |
| Branding & identité | Service, BreadcrumbList | FAQPage, Question, Answer | Valide, pas de prix (normal, sur devis) |
| Maquettes de présentation | Service, BreadcrumbList | FAQPage, Question, Answer | Valide |
| Articles (30 avec BlogPosting) | BlogPosting, FAQPage, BreadcrumbList, parfois HowTo/LocalBusiness/Service | itemscope BlogPosting vide (résidu sans itemprop) | Valide dans l'ensemble sauf les 2 pages cassées, `image` manquante partout, HowTo à retirer sur 13 d'entre eux |
| `anatomie-site-vitrine-qui-convertit` | JSON invalide (accolade/array mal fermés) | BlogPosting, Question, Answer | Cassé, à régénérer |
| `refonte-identite-visuelle-entreprise` | JSON invalide (accolade manquante) | BlogPosting | Cassé, à régénérer |
| `choisir-agence-web-paris`, `creation-site-internet-clamart`, `google-my-business-artisans-tpe`, `site-internet-boulogne-billancourt` | BlogPosting + LocalBusiness dupliqué (voir Critique 3 et 4) | BlogPosting | Valide en JSON mais données de l'entreprise incohérentes |
| Pages catégories WP, légales, plan du site | aucun | aucune | Absence de schéma, priorité basse |

## Conception pour la refonte

### Principe : un seul `@graph` cohérent, des `@id` stables et réutilisés

Le meilleur réflexe déjà présent sur le site actuel (référencer l'organisation par `{"@id": "https://aurea-media.fr/#organization"}` plutôt que de la recopier) doit devenir la règle absolue, sans exception, y compris pour les pages locales et les articles. Convention d'`@id` retenue pour la refonte, ancrée sur la racine du domaine pour les entités durables (afin qu'un changement de slug ne casse jamais une référence) :

- `https://aurea-media.fr/#organization` : Organization / ProfessionalService (entité unique de l'entreprise)
- `https://aurea-media.fr/#website` : WebSite
- `https://aurea-media.fr/#alexandre` : Person (Alexandre Coury)
- `https://aurea-media.fr/{slug}/#webpage` (ou `#aboutpage`, `#contactpage`, `#collectionpage` selon le sous-type) : la page elle-même
- `https://aurea-media.fr/{slug}/#service` : chaque Service
- `https://aurea-media.fr/{slug}/#breadcrumb` : chaque fil d'Ariane
- `https://aurea-media.fr/{slug}/#article` : chaque BlogPosting
- `https://aurea-media.fr/nos-conseils-web/#blog` : le Blog

### Choix du type : ProfessionalService, avec une nuance sur l'adresse

ProfessionalService (sous-type de LocalBusiness) reste le type le plus juste pour une agence web indépendante : ce n'est ni un commerce avec pignon sur rue, ni une entreprise purement en ligne sans ancrage local, mais un prestataire de services identifié par une adresse professionnelle et une zone d'intervention. Le point d'attention n'est donc pas le type mais l'usage qui est fait de l'adresse : si le 60 rue François Ier est une domiciliation, la refonte ne doit pas laisser penser qu'il s'agit d'un lieu d'accueil ouvert au public sur des horaires fixes (voir constat Moyenne n°8). La solution retenue ci-dessous conserve `address` pour la cohérence NAP et le rattachement SIREN, mais déplace les horaires dans un `contactPoint.hoursAvailable`, qui décrit une disponibilité de contact et non un accueil physique.

Autre choix assumé : suppression complète d'`aggregateRating` de l'entité organisation (voir constat Critique n°2). Les avis Google restent affichés nativement par Google dans Maps et le pack local sans qu'aucun balisage ne soit nécessaire ni souhaitable ici.

### Modèle 1 : Accueil

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "ProfessionalService",
      "@id": "https://aurea-media.fr/#organization",
      "name": "Aurea Media",
      "legalName": "Aurea Media",
      "description": "Aurea Media est une agence web indépendante basée à Paris, fondée par Alexandre Coury. Création de sites vitrines, e-commerce, branding et maquettes de présentation, pour des clients en France et à l'international.",
      "url": "https://aurea-media.fr/",
      "image": "https://aurea-media.fr/wp-content/uploads/2026/05/cropped-Logo-Aurea-Media-2.png",
      "logo": "https://aurea-media.fr/wp-content/uploads/2026/05/cropped-Logo-Aurea-Media-2.png",
      "telephone": "+33651287761",
      "email": "alexandre.coury@aurea-media.fr",
      "priceRange": "€€",
      "identifier": {
        "@type": "PropertyValue",
        "name": "SIREN",
        "value": "992693473"
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "60 rue François Ier",
        "postalCode": "75008",
        "addressLocality": "Paris",
        "addressRegion": "Île-de-France",
        "addressCountry": "FR"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 48.8719,
        "longitude": 2.3076
      },
      "areaServed": [
        { "@type": "City", "name": "Paris" },
        { "@type": "AdministrativeArea", "name": "Île-de-France" },
        { "@type": "Country", "name": "France" },
        { "@type": "Country", "name": "Suisse" },
        { "@type": "Country", "name": "Belgique" }
      ],
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "telephone": "+33651287761",
        "email": "alexandre.coury@aurea-media.fr",
        "availableLanguage": ["French"],
        "hoursAvailable": {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
          "opens": "09:00",
          "closes": "19:00"
        }
      },
      "founder": { "@id": "https://aurea-media.fr/#alexandre" },
      "sameAs": [
        "https://www.linkedin.com/company/aureamedia/",
        "https://www.google.com/search?kgmid=/g/11mcqnsjrx"
      ]
    },
    {
      "@type": "WebSite",
      "@id": "https://aurea-media.fr/#website",
      "url": "https://aurea-media.fr/",
      "name": "Aurea Media",
      "publisher": { "@id": "https://aurea-media.fr/#organization" },
      "inLanguage": "fr-FR"
    },
    {
      "@type": "Person",
      "@id": "https://aurea-media.fr/#alexandre",
      "name": "Alexandre Coury",
      "jobTitle": "Fondateur & Directeur créatif",
      "worksFor": { "@id": "https://aurea-media.fr/#organization" },
      "url": "https://aurea-media.fr/agence-web-a-paris/",
      "sameAs": [
        "https://www.linkedin.com/in/alexandre-coury/"
      ]
    },
    {
      "@type": "WebPage",
      "@id": "https://aurea-media.fr/#webpage",
      "url": "https://aurea-media.fr/",
      "name": "Aurea Media — Agence web à Paris",
      "isPartOf": { "@id": "https://aurea-media.fr/#website" },
      "about": { "@id": "https://aurea-media.fr/#organization" },
      "inLanguage": "fr-FR"
    }
  ]
}
```

Note : `sameAs` de la personne et de l'organisation doit être complété avec les profils réellement vérifiés (Malt, Instagram, X, etc.) au moment de la refonte. Ne jamais inventer une URL de profil qui n'existe pas ou n'est pas confirmée.

### Modèle 2 : page service (exemple site vitrine)

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Service",
      "@id": "https://aurea-media.fr/creation-de-site-vitrine-paris/#service",
      "name": "Création de site vitrine à Paris",
      "serviceType": "Création de site internet",
      "description": "Création de sites vitrines professionnels sur-mesure sous WordPress, avec référencement naturel inclus, pour indépendants, artisans, professions libérales, TPE et PME.",
      "provider": { "@id": "https://aurea-media.fr/#organization" },
      "areaServed": [
        { "@type": "City", "name": "Paris" },
        { "@type": "AdministrativeArea", "name": "Île-de-France" },
        { "@type": "Country", "name": "France" }
      ],
      "url": "https://aurea-media.fr/creation-de-site-vitrine-paris/",
      "offers": [
        {
          "@type": "Offer",
          "name": "Essentiel",
          "price": "1000",
          "priceCurrency": "EUR",
          "availability": "https://schema.org/InStock",
          "url": "https://aurea-media.fr/creation-de-site-vitrine-paris/#formules"
        },
        {
          "@type": "Offer",
          "name": "Professionnel",
          "price": "1800",
          "priceCurrency": "EUR",
          "availability": "https://schema.org/InStock",
          "url": "https://aurea-media.fr/creation-de-site-vitrine-paris/#formules"
        },
        {
          "@type": "Offer",
          "name": "Premium",
          "description": "Tarification sur devis, adaptée au nombre de pages et aux fonctionnalités du projet.",
          "url": "https://aurea-media.fr/creation-de-site-vitrine-paris/#formules"
        }
      ]
    },
    {
      "@type": "WebPage",
      "@id": "https://aurea-media.fr/creation-de-site-vitrine-paris/#webpage",
      "url": "https://aurea-media.fr/creation-de-site-vitrine-paris/",
      "name": "Création de site vitrine à Paris | Aurea Media",
      "isPartOf": { "@id": "https://aurea-media.fr/#website" },
      "about": { "@id": "https://aurea-media.fr/creation-de-site-vitrine-paris/#service" },
      "breadcrumb": { "@id": "https://aurea-media.fr/creation-de-site-vitrine-paris/#breadcrumb" },
      "inLanguage": "fr-FR"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://aurea-media.fr/creation-de-site-vitrine-paris/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "https://aurea-media.fr/" },
        { "@type": "ListItem", "position": 2, "name": "Nos services", "item": "https://aurea-media.fr/nos-services/" },
        { "@type": "ListItem", "position": 3, "name": "Création de site vitrine à Paris", "item": "https://aurea-media.fr/creation-de-site-vitrine-paris/" }
      ]
    }
  ]
}
```

Le même modèle s'applique à la page e-commerce (`Lancement` 1650 €, `Croissance` 3200 €) et aux pages branding et maquettes, en retirant simplement le tableau `offers` puisque ces deux prestations sont intégralement sur devis.

### Modèle 3 : page agence (About)

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "AboutPage",
      "@id": "https://aurea-media.fr/agence-web-a-paris/#aboutpage",
      "url": "https://aurea-media.fr/agence-web-a-paris/",
      "name": "L'agence — Aurea Media",
      "isPartOf": { "@id": "https://aurea-media.fr/#website" },
      "mainEntity": { "@id": "https://aurea-media.fr/#organization" },
      "breadcrumb": { "@id": "https://aurea-media.fr/agence-web-a-paris/#breadcrumb" },
      "inLanguage": "fr-FR"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://aurea-media.fr/agence-web-a-paris/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "https://aurea-media.fr/" },
        { "@type": "ListItem", "position": 2, "name": "L'agence", "item": "https://aurea-media.fr/agence-web-a-paris/" }
      ]
    }
  ]
}
```

La personne (`https://aurea-media.fr/#alexandre`) est déjà déclarée dans le graphe de l'accueil : il suffit de la référencer par `@id` depuis n'importe quelle page qui en a besoin (article, agence), jamais de la recopier en entier.

### Modèle 4 : page contact

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "ContactPage",
      "@id": "https://aurea-media.fr/contact-agence/#contactpage",
      "url": "https://aurea-media.fr/contact-agence/",
      "name": "Contact — Aurea Media",
      "isPartOf": { "@id": "https://aurea-media.fr/#website" },
      "mainEntity": { "@id": "https://aurea-media.fr/#organization" },
      "breadcrumb": { "@id": "https://aurea-media.fr/contact-agence/#breadcrumb" },
      "inLanguage": "fr-FR"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://aurea-media.fr/contact-agence/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "https://aurea-media.fr/" },
        { "@type": "ListItem", "position": 2, "name": "Contact", "item": "https://aurea-media.fr/contact-agence/" }
      ]
    }
  ]
}
```

### Modèle 5 : page réalisations

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "CollectionPage",
      "@id": "https://aurea-media.fr/realisations-agence-web/#collectionpage",
      "url": "https://aurea-media.fr/realisations-agence-web/",
      "name": "Réalisations — Aurea Media",
      "isPartOf": { "@id": "https://aurea-media.fr/#website" },
      "about": { "@id": "https://aurea-media.fr/#organization" },
      "breadcrumb": { "@id": "https://aurea-media.fr/realisations-agence-web/#breadcrumb" },
      "inLanguage": "fr-FR"
    },
    {
      "@type": "ItemList",
      "@id": "https://aurea-media.fr/realisations-agence-web/#realisations",
      "name": "Réalisations Aurea Media",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "item": {
            "@type": "CreativeWork",
            "name": "Novexia Construction",
            "description": "Site vitrine BTP rénovation",
            "url": "https://novexia-construction.fr/",
            "creator": { "@id": "https://aurea-media.fr/#organization" }
          }
        },
        {
          "@type": "ListItem",
          "position": 2,
          "item": {
            "@type": "CreativeWork",
            "name": "Dual Tech",
            "description": "Site vitrine pour un salon",
            "url": "https://dualtech-event.eu/",
            "creator": { "@id": "https://aurea-media.fr/#organization" }
          }
        }
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://aurea-media.fr/realisations-agence-web/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "https://aurea-media.fr/" },
        { "@type": "ListItem", "position": 2, "name": "Réalisations", "item": "https://aurea-media.fr/realisations-agence-web/" }
      ]
    }
  ]
}
```

Modèle raccourci à deux entrées pour l'exemple ; en production, reprendre la structure `ItemList` déjà en place (21 `CreativeWork`) en ajoutant simplement `creator` sur chaque élément pour renforcer le lien vers l'organisation, et une `image` par réalisation quand elle est disponible.

### Modèle 6 : page blog (listing)

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Blog",
      "@id": "https://aurea-media.fr/nos-conseils-web/#blog",
      "name": "Nos conseils web — Aurea Media",
      "description": "Guides et conseils sur la création de site internet, le référencement naturel et l'identité de marque, écrits à partir de projets réels menés pour nos clients.",
      "url": "https://aurea-media.fr/nos-conseils-web/",
      "isPartOf": { "@id": "https://aurea-media.fr/#website" },
      "publisher": { "@id": "https://aurea-media.fr/#organization" },
      "inLanguage": "fr-FR"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://aurea-media.fr/nos-conseils-web/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "https://aurea-media.fr/" },
        { "@type": "ListItem", "position": 2, "name": "Nos conseils web", "item": "https://aurea-media.fr/nos-conseils-web/" }
      ]
    }
  ]
}
```

### Modèle 7 : article de blog

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BlogPosting",
      "@id": "https://aurea-media.fr/{slug-article}/#article",
      "headline": "Titre de l'article (moins de 110 caractères)",
      "description": "Résumé de l'article en une à deux phrases.",
      "url": "https://aurea-media.fr/{slug-article}/",
      "image": "https://aurea-media.fr/wp-content/uploads/{annee}/{mois}/{image-article}.jpg",
      "datePublished": "2026-09-25",
      "dateModified": "2026-09-25",
      "inLanguage": "fr-FR",
      "articleSection": "Conseils agence web",
      "isPartOf": { "@id": "https://aurea-media.fr/nos-conseils-web/#blog" },
      "author": { "@id": "https://aurea-media.fr/#alexandre" },
      "publisher": { "@id": "https://aurea-media.fr/#organization" },
      "mainEntityOfPage": { "@id": "https://aurea-media.fr/{slug-article}/#webpage" }
    },
    {
      "@type": "WebPage",
      "@id": "https://aurea-media.fr/{slug-article}/#webpage",
      "url": "https://aurea-media.fr/{slug-article}/",
      "isPartOf": { "@id": "https://aurea-media.fr/#website" },
      "breadcrumb": { "@id": "https://aurea-media.fr/{slug-article}/#breadcrumb" },
      "inLanguage": "fr-FR"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://aurea-media.fr/{slug-article}/#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Accueil", "item": "https://aurea-media.fr/" },
        { "@type": "ListItem", "position": 2, "name": "Nos conseils web", "item": "https://aurea-media.fr/nos-conseils-web/" },
        { "@type": "ListItem", "position": 3, "name": "Titre de l'article", "item": "https://aurea-media.fr/{slug-article}/" }
      ]
    }
  ]
}
```

`{slug-article}`, le titre, la description, l'image et les dates sont à remplacer par les valeurs réelles de chaque article au moment de la génération, jamais laissés tels quels en production. Le FAQPage n'est volontairement pas inclus dans ce modèle de base : à ajouter uniquement si le client accepte qu'il ne produit plus de résultat enrichi Google et que son seul bénéfice possible est IA/GEO, non garanti. Le HowTo ne doit dans aucun cas être ajouté, type déprécié depuis septembre 2023.

## Comment vérifier l'ensemble après mise en ligne

Pour chaque gabarit livré ci-dessus : coller l'URL de la page publiée dans le Rich Results Test de Google (zéro erreur, zéro avertissement bloquant), vérifier avec un `grep` sur le HTML généré qu'il n'existe plus qu'une seule définition complète de l'organisation (`"@type": "ProfessionalService"` une seule fois sur tout le site, ailleurs uniquement des `{"@id": "https://aurea-media.fr/#organization"}`), et confirmer qu'aucune occurrence de `aggregateRating`, `HowTo` ou de numéro de téléphone placeholder ne subsiste.

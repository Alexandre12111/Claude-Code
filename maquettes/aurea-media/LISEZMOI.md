# Refonte d'aurea-media.fr : maquette 1.0

Maquette complète et fonctionnelle de la refonte du site d'Aurea Media : 17 pages, design system, micro-animations, simulateur de budget, SEO et GEO intégrés page par page. Elle sert de référence pour le futur thème WordPress sur mesure (sans Elementor, Rank Math conservé), sur le modèle du travail mené pour Schiesser.

Le rapport d'audit et la stratégie de contenu sont dans [`RAPPORT-SEO-GEO.md`](RAPPORT-SEO-GEO.md).

## Ouvrir la maquette

- **Le plus simple** : ouvrir `index.html` dans un navigateur. Toutes les pages sont reliées entre elles.
- **Recommandé** (pour la spirale WebGL et les polices, que certains navigateurs bloquent en `file://`) : lancer `python3 -m http.server 8000` dans ce dossier, puis ouvrir `http://localhost:8000`.
- **Régénérer après une modification** : `python3 build.py` (Python 3, aucune dépendance).

Les liens pointent vers les fichiers locaux pour l'aperçu ; les balises canonical, Open Graph, le JSON-LD et le sitemap gardent les vraies adresses de production (`https://aurea-media.fr/...`). Un lien vers un article qui n'est pas dans la maquette ouvre la page actuelle du site.

## Le concept : la section d'or

« Aurea » vient du latin *sectio aurea*, la section d'or. La refonte en fait son principe de composition :

- **Espacements en suite φ** : 0,382 · 0,618 · 1 · 1,618 · 2,618 · 4,236 · 6,854 · 11,09 rem.
- **Grilles 1 : 1,618** pour les mises en page à deux colonnes.
- **Spirale de Vogel en WebGL** dans le hero : 2 600 grains d'or placés selon l'angle d'or (137,508°), qui s'assemblent au chargement, respirent, s'écartent du curseur et se déploient au défilement. Une vague lumineuse parcourt les 21 bras de la spirale (21 est un nombre de Fibonacci).
- **Rectangles et spirale d'or dessinés au trait** dans le haut de chaque page intérieure, tracés au défilement.
- **Identité conservée et affinée** : noir, or (#C9A961) et crème de la marque actuelle ; or foncé #7D5F27 pour les textes sur fond clair (contraste 5,3:1).
- **Typographie** : Instrument Serif pour les titres (italique or pour les mots clés), Geist pour le texte, Geist Mono pour les étiquettes. Polices auto hébergées (RGPD, performance), avec des polices de repli recalées au millième pour éviter tout décalage au chargement.

## Outils et choix techniques

| Outil | Usage | Pourquoi |
|---|---|---|
| **GSAP 3.15** (ScrollTrigger, SplitText, Flip) | Révélations ligne par ligne, galerie horizontale épinglée, filtres animés, compteurs | Standard des sites primés ; gratuit y compris en usage commercial ; compatible avec WordPress sans framework |
| **Lenis** | Défilement fluide (souris uniquement) | Inertie douce, désactivé au tactile et si l'utilisateur demande moins d'animations |
| **WebGL natif** | Spirale d'or du hero, de l'appel final et de la 404 | Environ 4 Ko au lieu des 600 Ko de Three.js pour le même rendu ; démarre après le chargement de la page |
| **View Transitions** (CSS) | Fondu entre les pages | Natif, aucun JavaScript |
| **Python** (`build.py`) | Générateur des pages | Aucune dépendance ; mêmes composants que le futur thème |

Framer Motion n'a pas été retenu : il impose React, alors que le site restera sous WordPress.

## Micro-interactions

- Titres H2 révélés ligne par ligne sous un masque ; titres des héros en entrée floue vers nette, en CSS pur (aucune attente du JavaScript, donc aucun effet sur le LCP).
- Manifeste dont les mots s'allument au fil du défilement.
- Lignes de services qui se retournent au survol (fond encre, flèche qui pivote) avec un aperçu de réalisation qui suit le curseur.
- Galerie de réalisations horizontale épinglée sur ordinateur, captures en parallaxe dans des fenêtres de navigateur.
- Ligne de méthode qui se dore au défilement, étapes qui s'activent, compteurs animés.
- **Simulateur de budget** : type de site, taille, options, et la formule réelle correspondante avec son prix de départ et son délai ; le bouton transmet la formule au formulaire de contact, qui se préremplit.
- Boutons magnétiques, curseur doré qui grossit sur les liens et affiche « Voir » sur les projets.
- Bandeau de clients qui accélère avec la vitesse de défilement.
- Filtres de réalisations et d'articles animés (Flip), onglets de tarifs accessibles au clavier.
- Comparateur avant et après de la refonte (glisser ou flèches du clavier).
- Planche de marque : un clic sur une couleur copie son code.
- Pile de slides qui s'empilent au défilement (page Présentations), carte du pack local animée (page SEO local).
- Formulaire avec validation en ligne, créneaux de rendez-vous calculés sur les 5 prochains jours ouvrés.

Tout reste lisible sans JavaScript, et tout mouvement est coupé si l'utilisateur a demandé moins d'animations dans son système.

## Pages de la maquette

| Fichier | Adresse en production | Mot-clé principal | Statut |
|---|---|---|---|
| `index.html` | `/` | agence web Paris | Refaite |
| `creation-site-internet-paris.html` | `/creation-site-internet-paris/` | création site internet Paris | **Nouvelle** (remplace `/nos-services/`, 301) |
| `creation-de-site-vitrine-paris.html` | `/creation-de-site-vitrine-paris/` | création site vitrine Paris | Refaite, URL conservée |
| `creation-de-site-e-commerce-paris.html` | `/creation-de-site-e-commerce-paris/` | création site e-commerce Paris | Refaite, URL conservée |
| `refonte-site-internet-paris.html` | `/refonte-site-internet-paris/` | refonte site internet Paris | **Nouvelle** |
| `seo-local-paris.html` | `/seo-local-paris/` | SEO local Paris | **Nouvelle** |
| `branding-identite.html` | `/branding-identite/` | création logo Paris | Refaite, URL conservée |
| `maquettes-de-presentation.html` | `/maquettes-de-presentation/` | création pitch deck | Refaite, URL conservée |
| `prix-site-internet-paris.html` | `/prix-site-internet-paris/` | prix site internet Paris | **Nouvelle** (source unique des prix) |
| `realisations-agence-web.html` | `/realisations-agence-web/` | exemples de sites vitrines | Refaite, URL conservée |
| `realisations-agence-web--novexia-construction.html` | `/realisations-agence-web/novexia-construction/` | site internet BTP | **Nouvelle** (gabarit d'étude de cas) |
| `a-propos.html` | `/a-propos/` | développeur WordPress Paris | **Nouvelle** (remplace `/agence-web-a-paris/`, 301) |
| `contact-agence.html` | `/contact-agence/` | devis site internet Paris | Refaite, URL conservée |
| `nos-conseils-web.html` | `/nos-conseils-web/` | conseils site internet | Refaite, URL conservée |
| `prix-site-vitrine-paris.html` | `/prix-site-vitrine-paris/` | prix site vitrine Paris | Gabarit d'article, contenu corrigé |
| `agence-web-hauts-de-seine.html` | `/agence-web-hauts-de-seine/` | agence web Hauts-de-Seine | Gabarit de page locale |
| `404.html` | toute adresse inexistante | aucun (noindex) | **Nouvelle** : vraie 404 |

Le détail des titres SEO, descriptions et H1, avec leur longueur, est généré à chaque construction dans [`SEO-PAGES.md`](SEO-PAGES.md).

## SEO et GEO intégrés

- **Règles Rank Math respectées sur les 16 pages indexables** : title de 53 à 59 caractères au format « Mot-clé | Proposition – Aurea Media », meta description de 149 à 160 caractères, un seul H1 qui contient le mot-clé, mot-clé dans l'introduction. `build.py` vérifie ces règles à chaque génération.
- **Source unique des faits** (`src/data.py`) : prix, formules, nombre d'avis, projets, coordonnées. Les contradictions du site actuel (1 000 ou 1 200 €, 39 ou 40 avis) deviennent impossibles.
- **JSON-LD en un seul graphe cohérent** par page, avec des `@id` stables : ProfessionalService, WebSite, WebPage (AboutPage, ContactPage, CollectionPage), Person, Service avec catalogue d'offres et prix HT, BlogPosting, BreadcrumbList, ItemList des réalisations. Aucune note d'avis auto déclarée, aucun HowTo, aucune FAQPage (Google ne les affiche plus depuis mai 2026) ; les FAQ restent visibles pour les lecteurs et les IA.
- **Bloc « Aurea Media en bref »** : définition d'entité reprise à l'identique sur l'accueil, la page À propos, le pied de page, le schéma et le `llms.txt`.
- **Fichiers générés** : `sitemap.xml`, `robots.txt` (un seul groupe, robots IA autorisés), `llms.txt` complet, `redirections-301.htaccess`.
- **Images de partage** 1200 × 630 propres à chaque page dans `assets/img/og/` (à téléverser dans `/wp-content/uploads/og/`, adresse déjà déclarée dans les balises).
- **Adresse** : « Paris 8e, rendez-vous sur demande » sur les pages commerciales ; adresse complète dans le schéma et les mentions légales (voir le rapport, partie SEO local).

## Résultats mesurés

Mesures du 25 septembre 2026, Lighthouse 12 mobile (réseau 4G lent simulé), serveur avec compression Brotli comme un hébergement de production :

| Page | Performance | Accessibilité | Bonnes pratiques | SEO | LCP | CLS |
|---|---|---|---|---|---|---|
| Accueil | 96 | 100 | 100 | 100 | 2,5 s | 0 |
| Création de site vitrine | 100 | 100 | 100 | 100 | 1,5 s | 0,029 |
| Tarifs | 100 | 100 | 100 | 100 | 1,6 s | 0 |
| Réalisations | 100 | 100 | 100 | 100 | 1,6 s | 0 |
| Article (prix site vitrine) | 100 | 100 | 100 | 100 | 1,5 s | 0,001 |
| Contact | 100 | 100 | 100 | 100 | 1,6 s | 0 |
| À propos, SEO local | 97 | 100 | 100 | 100 | 2,4 s | 0 |

Autres vérifications, toutes réussies :

- **Accessibilité** : 0 violation axe (WCAG 2.1 A et AA) sur les 17 pages en bureau, et sur l'accueil et le contact en mobile.
- **Affichage** : aucun débordement horizontal à 390 et 1440 pixels, un seul H1 par page, aucune image sans texte alternatif.
- **JavaScript** : aucune erreur, aucune requête en échec.
- **20 tests d'interaction** : méga-menu (clic, Échap), menu mobile, simulateur (vitrine, e-commerce, lien prérempli), FAQ, avis, formulaire (préremplissage, erreurs, envoi), créneaux, filtres, onglets au clavier, comparateur au clavier, copie de couleur.
- **Poids** : 170 à 260 Ko par page, accueil 182 Ko (le site actuel charge 20 feuilles de style, 18 scripts et des captures PNG jusqu'à 2 Mo).

## À valider avant la mise en ligne

Ces points dépendent d'Aurea Media ; la maquette ne contient aucun chiffre inventé.

1. **Nombre de projets** : « 45+ » est conservé, mais un rechercher/remplacer de « 150 » en « 45 » a visiblement touché le site (voir le rapport). Confirmer le bon chiffre dans `src/data.py`.
2. **Ancienneté** : le SIREN 992 693 473 semble récent ; la maquette dit « Alexandre crée des sites depuis 2021 », formulation juste pour une personne. À ajuster si la société elle-même date de 2021.
3. **Prix** : refonte, SEO local, identité visuelle et présentations sont « sur devis ». Un article mentionne une refonte à 900 € HT et un logo à 800 € HT : à confirmer avant de les afficher.
4. **Étude de cas Novexia** : les résultats chiffrés et la citation du client sont signalés « À compléter » (Search Console et formulaires du client, avec son accord). Localisation à confirmer : Occitanie sur la page Réalisations, Île-de-France dans un article.
5. **Avis** : seuls les 3 avis reproduits sur le site actuel sont affichés. Ajouter des avis récents et variés (vitrine, e-commerce, identité).
6. **LinkedIn** : deux adresses d'entreprise existent (`/company/aureamedia/` et `/company/aurea-media`) ; n'en garder qu'une.
7. **Rendez-vous** : le bloc de créneaux est une maquette ; le relier à Cal.com ou Google Agenda dans le thème.
8. **Formulaire** : l'envoi est simulé ; il sera branché sur WordPress (sans extension, comme pour Schiesser).

## Structure du dossier

```
maquettes/aurea-media/
├── build.py                  générateur (python3 build.py)
├── src/
│   ├── data.py               source unique des faits (prix, avis, projets, coordonnées)
│   ├── components.py         composants : en-tête, services, projets, formules, FAQ, simulateur...
│   └── pages/*.html          contenu de chaque page (en-tête JSON : title, description, mot-clé)
├── assets/
│   ├── css/aurea.css         design system (aurea.min.css est générée)
│   ├── js/aurea.js           interactions (GSAP, Lenis)
│   ├── js/aurea-gl.js        spirale d'or WebGL
│   ├── vendor/               GSAP, ScrollTrigger, SplitText, Flip, Lenis
│   ├── fonts/                Instrument Serif, Geist, Geist Mono (licences OFL incluses)
│   └── img/                  réalisations en AVIF et WebP, portrait, favicon, images OG
├── *.html                    pages générées
├── sitemap.xml, robots.txt, llms.txt, redirections-301.htaccess
├── SEO-PAGES.md              contrôle Rank Math page par page (généré)
├── RAPPORT-SEO-GEO.md        audit, stratégie de contenu et ciblage
└── audit/                    rapports détaillés des sept audits spécialisés
```

## Étape suivante : le thème WordPress

La maquette est construite pour se transformer directement en thème WordPress sur mesure, comme Schiesser : chaque composant de `components.py` devient un bloc (même balisage, mêmes classes), `data.py` devient une page de réglages lue par les blocs et par le schéma, et Rank Math reste maître des titres, descriptions, sitemap et redirections.

## Licences

GSAP : licence standard gratuite de GreenSock (usage commercial autorisé). Lenis : MIT. Instrument Serif, Geist et Geist Mono : SIL Open Font License 1.1 (fichiers de licence dans `assets/fonts/`). Captures des réalisations et portrait : propriété d'Aurea Media et de ses clients.

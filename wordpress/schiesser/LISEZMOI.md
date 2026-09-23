# Thème Schiesser 0.2

Thème WordPress sur mesure pour la Confiserie Schiesser, sans constructeur de pages ni extension payante.
Le design de la maquette est figé dans le code ; tout le contenu se modifie depuis l'administration, sans écrire une ligne de code.

## Nouveautés de la version 0.2

- **Charte graphique modifiable** : 8 couleurs et 2 polices (titres, texte) dans Réglages maison → Charte graphique, avec alerte de lisibilité.
- **Sections libres** : bloc « Section » (fond, largeur, en-tête N° / titre / note) dans lequel on ajoute textes, images, colonnes, boutons, questions fréquentes, vidéo ou code HTML.
- **Styles maison** pour les blocs WordPress : boutons (vert, contour, crème, lien fléché), chapeau, surtitre, grille à filets, cartes, cadre vitrine, losanges, grande citation.
- **Compositions** : 10 sections toutes prêtes et 2 modèles de page (outil d'ajout → Compositions → Schiesser).
- **Une page par produit** (/produits/nom-du-produit/), avec photo, fiche, prix, présentation détaillée et produits liés.
- **SEO complet** : intégration Rank Math, données structurées (établissement, fil d'Ariane, produits, liste de produits, questions), titres et descriptions optimisés pour chaque page du contenu de démonstration.
- **Prêt pour WooCommerce** : transfert des produits en un clic, redirections automatiques, panier dans l'en-tête, pages boutique aux couleurs de la maison.
- **Administration repensée** : réglages en onglets, tableau de bord avec raccourcis, Guide du site, page de connexion à la marque, rôle « Gérant(e) du site ».
- **Optimisation SEO complète (0.2.1)** : textes réécrits après un audit en six volets, fiches produits enrichies, nouvelle page « Cadeaux d’entreprise », menu de pied de page, allergènes précisés. Détail dans `SEO-RAPPORT.md`.
- **Commande facilitée** : bouton « Commander » avec un e-mail prérempli, et sur mobile une barre de commande fixe sur chaque fiche produit.
- **Typographie française** : espaces insécables automatiques avant « : ; ? ! » et dans les heures.

## Installation

### Première installation (site de test)

1. **Apparence → Thèmes → Ajouter → Téléverser un thème**, choisir `schiesser.zip`, puis **Activer**.
2. **Réglages → Général** : Langue du site « Français », Fuseau horaire « Zurich », Titre du site « Confiserie Schiesser », puis Enregistrer. Ces trois réglages sont lus par Google et par le bandeau « Ouvert / Fermé ».
3. Sur le tableau de bord, cliquer sur **Importer le contenu de démonstration** (8 produits, 7 pages, menus de l'en-tête et du pied de page, réglages SEO ; les photos sont téléchargées depuis Unsplash, comptez une minute).
4. **Réglages → Permaliens** : vérifier que « Titre de la publication » est sélectionné, puis Enregistrer.

### Mise à jour depuis une version précédente (0.1 ou 0.2.0)

1. **Apparence → Thèmes → Ajouter → Téléverser un thème**, choisir le nouveau `schiesser.zip`, puis **Remplacer la version installée**.
2. Sur le tableau de bord, cliquer sur **Mettre à jour le contenu de démonstration**. Attention : cela remplace le contenu des pages et des 8 produits de démonstration (sur un site de test, c'est ce que l'on veut). La page « Cadeaux d’entreprise » et le menu du pied de page sont créés.
3. Les anciennes adresses redirigent automatiquement vers les nouvelles : `/tea-room/` vers `/salon-de-the/`, et les produits renommés (par exemple `/produits/truffes-maison/` vers `/produits/truffes-au-chocolat/`).

Prérequis : WordPress 6.4 ou plus récent (conseillé : la dernière version), PHP 7.4 ou plus récent.
Elementor et Code Snippets ne sont pas utilisés : ils peuvent être désactivés.

## Extensions

| Extension | Quand | Pourquoi |
|---|---|---|
| **Rank Math SEO** (gratuit) | Maintenant | Titres, descriptions, mots-clés, plan du site, redirections. Le thème le complète automatiquement. |
| **WooCommerce** (gratuit) | Le jour où la vente en ligne démarre | Panier, paiement, stock. Tout est déjà préparé dans le thème. |
| Une extension de formulaire (par exemple Fluent Forms ou WPForms Lite) | Si vous voulez un formulaire de contact | Un bloc à placer dans la page Contact, dans une Section. |
| Une extension de cache (selon l'hébergeur) | À la mise en ligne | Vitesse d'affichage. |
| Une extension de sauvegarde (par exemple UpdraftPlus) | À la mise en ligne | Sauvegardes automatiques. |
| Polylang | Si le site passe en allemand et en anglais | Versions linguistiques. |

## Ce que le client modifie, et où

| Contenu | Où | Effet |
|---|---|---|
| Téléphone, e-mail, adresse, horaires, vitrine du jour, pied de page, réseaux sociaux | **Réglages maison** | Mis à jour partout : bandeau « Ouvert / Fermé », pied de page, pages Visiter et Contact, fiche Google |
| Fiche Google (type d'établissement, position, année de fondation, canton, raison sociale, numéro IDE, profils officiels) | **Réglages maison → Fiche Google et SEO** | Données structurées lues par Google et les moteurs d'IA |
| Produits (nom, photo, catégorie, prix, badge, fiche, présentation) | **Produits** | Grille de la boutique, fiche rapide et page du produit |
| Ordre des produits | **Produits → Modifier → Ordre d'affichage** | 1 = premier |
| Catégories (filtres de la boutique) | **Produits → Catégories** | Les filtres apparaissent automatiquement |
| Textes, photos et sections des pages | **Pages → Modifier** | Clic direct sur le texte ; bouton « Photo » dans la barre du bloc ; bouton + pour ajouter une section |
| Style d'un bouton, d'un paragraphe, d'une image | Panneau de droite → onglet **Styles** | Uniquement des styles aux couleurs de la maison |
| Couleurs et polices du site | **Réglages maison → Charte graphique** (administrateur) | Tout le site et l'éditeur suivent |
| Code sur toutes les pages (statistiques, pixel) | **Réglages maison → Avancé** (administrateur) | Ajouté dans l'en-tête ou en fin de page |
| Code sur une seule page (widget de réservation, carte, avis) | Bloc **HTML personnalisé** dans une Section | Le reste de la page reste modifiable normalement |
| Menus | **Apparence → Menus** | « Menu principal » pour l'en-tête, « Menu du pied de page » pour la colonne Explorer (sans lui, le pied de page reprend le menu principal) |

Un **Guide du site** pas à pas se trouve dans le menu Réglages maison.

## Blocs de la catégorie « Schiesser »

- **Hero (grande photo)** : surtitre, titre (H1 de la page, italique possible), texte, deux boutons avec leur style, photo et texte alternatif, hauteur, couleur des mots en italique, sceau tournant. Protégé contre la suppression.
- **Section** : fond (papier, clair, crème, sable, chocolat), largeur (large ou lecture), en-tête facultatif, ancre pour les liens directs, contenu libre.
- **Grille des produits** : titre et note modifiables, nombre de produits, filtres, source (produits du site ou WooCommerce), bouton « Voir toute la boutique ».

Codes courts utilisables partout (bloc « Code court » ou dans un paragraphe). Ils se mettent à jour tout seuls quand les Réglages maison changent :

| Code court | Affiche |
|---|---|
| `[schiesser_horaires]` | Le tableau des horaires, jour courant mis en avant |
| `[schiesser_horaires_phrase]` | Les horaires dans une phrase : « du lundi au vendredi de 7 h 30 à 18 h 30, … » |
| `[schiesser_statut]` | « Ouvert » ou « Fermé » en direct |
| `[schiesser_adresse]` | L'adresse sur deux lignes |
| `[schiesser_telephone]` | Le numéro, cliquable |
| `[schiesser_email]` | L'adresse e-mail, cliquable |
| `[schiesser_itineraire texte="Itinéraire"]` | Un bouton vers Google Maps |

## Rank Math : réglages conseillés

1. Installer et activer **Rank Math SEO**, suivre l'assistant en mode « Facile ».
2. Type de site : **Petite entreprise** ; nom : Confiserie Schiesser ; logo : le logo de la maison.
3. Inutile de ressaisir l'adresse et les horaires dans Rank Math : le thème ajoute automatiquement les informations des Réglages maison à la fiche de Rank Math.
4. **Plan du site** : inclure les Pages et les Produits ; exclure les Articles s'il n'y a pas de blog.
5. Activer le module **Redirections** : chaque changement d'adresse d'une page créera une redirection.
6. Dans chaque page, le panneau Rank Math affiche déjà le **mot-clé principal**, le **titre SEO** et la **méta description** préparés. Viser un score de 80 ou plus, sans chercher 100 à tout prix.
7. Après la mise en ligne : relier **Google Search Console** et envoyer le plan du site.

Détails, mots-clés par page et recommandations de l'audit : voir `SEO-RAPPORT.md`.

## WooCommerce : le jour où la vente en ligne démarre

1. Extensions → Ajouter → **WooCommerce** → Installer, puis Activer. WooCommerce reprend la page « La boutique » existante.
2. WooCommerce → Réglages : devise franc suisse (CHF), adresse, TVA, livraison ; paiements (TWINT et carte via Payrexx ou Stripe, retrait en boutique).
3. **Réglages maison → Boutique en ligne → Transférer les produits** : chaque produit devient un produit WooCommerce (nom, photo, prix, catégorie, fiche, titres SEO). Les anciennes adresses `/produits/…` redirigent vers les nouvelles fiches. Le transfert peut être annulé depuis le même écran.
4. Vérifier les prix (les produits « Sur commande » n'ont pas de prix en ligne), puis tester une commande.

La grille de la page Boutique affiche alors les produits WooCommerce avec le bouton « Ajouter au panier », et un panier apparaît dans l'en-tête.
Ce parcours a été testé ici avec une imitation de WooCommerce (l'extension ne peut pas être téléchargée dans l'environnement de développement) : à valider sur le site de test.

## Comptes et droits

- **Administrateur** : tout, y compris Charte graphique, code personnalisé, extensions.
- **Gérant(e) du site** (rôle créé par le thème) : pages, produits, photos, menu, Réglages maison (coordonnées, horaires, accueil, fiche Google). Pas d'accès aux extensions, aux thèmes ni aux réglages techniques. C'est le rôle à donner à la personne qui gère le site au quotidien.

## Avant la mise en ligne

- Vérifier **Réglages → Général** (langue Français, fuseau Zurich, titre « Confiserie Schiesser ») et, le jour du lancement, décocher « Demander aux moteurs de recherche de ne pas indexer ce site » dans **Réglages → Lecture**.
- Relire tous les textes de démonstration (dates, chiffres, prix, allergènes, affirmations historiques) et les faire valider par la maison : la liste des faits à vérifier est dans `SEO-RAPPORT.md`, section 7.
- Remplacer les photos par les photos de la maison, avec un texte alternatif descriptif pour chacune.
- Saisir les vraies coordonnées et les horaires dans Réglages maison, ainsi que la position exacte (latitude, longitude).
- Compléter et publier la page **Mentions légales** (brouillon créé par l'import) et la **Politique de confidentialité** (nLPD).
- Créer ou mettre à jour la fiche **Google Business Profile** avec exactement le même nom, la même adresse et le même téléphone.
- Mettre le site en HTTPS, activer un cache, programmer des sauvegardes.
- Relier Google Search Console et envoyer le plan du site.
- Si le site remplace le site actuel : redirections 301 de toutes les anciennes adresses (voir `SEO-RAPPORT.md`, section 9).

## Organisation du code

```
schiesser/
├── style.css, functions.php, theme.json
├── header.php, footer.php, page.php, index.php, 404.php
├── single-schiesser_produit.php   page d'un produit
├── woocommerce.php                pages WooCommerce (utilisé seulement si WooCommerce est actif)
├── parts/titre-page.php           bandeau de titre des pages sans grande photo
├── inc/
│   ├── setup.php          réglages du thème, styles, scripts, navigation, redirections de pages
│   ├── charte.php         couleurs et typographie
│   ├── reglages.php       données des Réglages maison
│   ├── page-reglages.php  écran des Réglages maison
│   ├── shortcodes.php     codes courts
│   ├── produits.php       type de contenu Produits, champs, cartes, produits liés
│   ├── blocs.php          blocs maison et styles des blocs WordPress
│   ├── compositions.php   compositions de l'éditeur et outils d'écriture des blocs
│   ├── seo.php            Rank Math et données structurées
│   ├── woocommerce.php    préparation de la vente en ligne
│   ├── admin.php          habillage de l'administration, tableau de bord, guide, rôle
│   └── demo.php           contenu de démonstration
├── blocks/     hero/, section/, produits/  (block.json + index.js + render.php)
└── assets/     css/ (site, WooCommerce), js/ (site, boutique, éditeur), admin/, fonts/
```

Le JavaScript de l'éditeur est écrit en JS simple : aucune compilation (Node, npm) n'est nécessaire pour le modifier.
Les polices Bodoni Moda et Inter sont hébergées dans le thème (licence SIL Open Font License, fichiers dans `assets/fonts/`) : aucun appel à Google Fonts tant que la charte d'origine est conservée.

# Aurea Studio 0.2

Plugin d'administration du site Aurea Media. Il ajoute un menu **Aurea Studio** dans la colonne de gauche de WordPress. Le contenu géré ici (logos, et bientôt réalisations et articles) est enregistré dans la base de données : il reste en place quand le thème est mis à jour ou changé.

## Installation

1. Extensions → Ajouter → Téléverser une extension → `aurea-studio.zip` → Activer.
2. Aurea Studio → Logos clients → **Importer les 18 logos du site actuel** (un clic, une seule fois).

## Logos clients

L'écran montre en haut un aperçu du bandeau tel qu'il apparaît sur le site, puis une carte par logo.

* **Ajouter des logos** : ouvre la médiathèque, on peut en choisir plusieurs d'un coup. Le nom reprend le texte alternatif ou le titre de l'image.
* **Changer l'ordre** : glisser une carte par sa zone noire.
* **Nom** : lu par les lecteurs d'écran, jamais affiché.
* **Lien** (facultatif) : le logo devient cliquable et ouvre le site du client dans un nouvel onglet.
* **Visible** : décocher retire le logo du site sans le supprimer.
* **Couleurs d'origine** : par défaut, chaque logo s'affiche en silhouette crème qui passe en or au survol, pour un bandeau homogène. Cocher cette case affiche le logo tel quel. Elle est cochée d'office pour les JPG, qui n'ont pas de transparence.
* **Enregistrer** : rien n'est publié avant ce clic. Quitter la page avec des modifications en attente affiche une alerte.

Fichier conseillé : PNG à fond transparent, environ 400 px de large.

## Pour le thème

* `aurea_logos_html( array( 'titre' => '…' ) )` affiche le bandeau ; `aurea_logos( true )` renvoie les logos visibles (nom, image, url, lien, couleur).
* Shortcode : `[aurea_logos titre="Ils nous ont confié leur image en ligne"]`.
* Un thème qui fournit ses propres styles déclare `add_theme_support( 'aurea-logos' )` ; sinon le plugin charge `assets/css/logos.css`.
* Les logos sont des contenus privés `aurea_logo` (titre, ordre, statut publié ou brouillon) avec les champs `_logo_image`, `_logo_lien` et `_logo_couleur`.

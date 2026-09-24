<?php
/**
 * Thème Schiesser : point d'entrée.
 *
 * Chaque fichier de inc/ gère une partie du site :
 * - setup.php         : réglages du thème, styles, scripts, navigation
 * - charte.php        : couleurs et typographie modifiables
 * - reglages.php      : données des « Réglages maison » (horaires, contacts…)
 * - horaires.php      : jours fériés de Bâle (Fasnacht comprise) et horaires exceptionnels
 * - annonces.php      : bandeau d'annonce programmable, barre « Appeler · Itinéraire » du téléphone
 * - page-reglages.php : l'écran « Réglages maison » de l'administration
 * - shortcodes.php    : codes courts [schiesser_horaires], [schiesser_horaires_phrase], [schiesser_adresse]…
 * - produits.php      : type de contenu « Produits boutique », ses champs et ses pages
 * - tea-room.php      : type de contenu « Produits Tea Room » (la carte du salon de thé)
 * - allergenes.php    : allergènes et régimes des produits et du Tea Room, pictogrammes, filtres
 * - blocs.php         : blocs sur mesure (Hero, Section, Grille) et styles des blocs
 * - messages.php      : messages du formulaire gardés dans l'administration (« Messages reçus »)
 * - securite.php      : connexion protégée, en-têtes de sécurité, préchargement de la grande photo
 * - avis.php          : avis Google (bloc « Avis clients », API Google ou saisie manuelle)
 * - mise-en-page.php  : styles de texte enregistrés, espacements des sections, boutons
 * - compositions.php  : sections toutes prêtes de l'éditeur
 * - seo.php           : Rank Math et données structurées
 * - woocommerce.php   : préparation de la vente en ligne
 * - admin.php         : habillage de l'administration, tableau de bord, guide
 * - aujourdhui.php    : écran « Aujourd'hui » (vitrine, suggestion, épuisés, annonce, messages)
 * - client.php        : « Appeler pour commander », « Ma sélection », partage, navigation fluide
 * - edition.php       : sections programmées, « Modifier cette section », point d'intérêt des photos, menu du gérant
 * - demo.php          : import du contenu de démonstration
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_VERSION', '0.8.0' );
define( 'SCHIESSER_DIR', get_template_directory() );
define( 'SCHIESSER_URI', get_template_directory_uri() );

require SCHIESSER_DIR . '/inc/setup.php';
require SCHIESSER_DIR . '/inc/charte.php';
require SCHIESSER_DIR . '/inc/reglages.php';
require SCHIESSER_DIR . '/inc/horaires.php';
require SCHIESSER_DIR . '/inc/annonces.php';
require SCHIESSER_DIR . '/inc/page-reglages.php';
require SCHIESSER_DIR . '/inc/shortcodes.php';
require SCHIESSER_DIR . '/inc/produits.php';
require SCHIESSER_DIR . '/inc/tea-room.php';
require SCHIESSER_DIR . '/inc/allergenes.php';
require SCHIESSER_DIR . '/inc/blocs.php';
require SCHIESSER_DIR . '/inc/maquette.php';
require SCHIESSER_DIR . '/inc/messages.php';
require SCHIESSER_DIR . '/inc/securite.php';
require SCHIESSER_DIR . '/inc/avis.php';
require SCHIESSER_DIR . '/inc/mise-en-page.php';
require SCHIESSER_DIR . '/inc/compositions.php';
require SCHIESSER_DIR . '/inc/seo.php';
require SCHIESSER_DIR . '/inc/woocommerce.php';
require SCHIESSER_DIR . '/inc/admin.php';
require SCHIESSER_DIR . '/inc/aujourdhui.php';
require SCHIESSER_DIR . '/inc/client.php';
require SCHIESSER_DIR . '/inc/edition.php';
require SCHIESSER_DIR . '/inc/demo.php';

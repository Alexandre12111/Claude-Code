<?php
/**
 * Thème Schiesser : point d'entrée.
 *
 * Chaque fichier de inc/ gère une partie du site :
 * - setup.php         : réglages du thème, styles, scripts, navigation
 * - charte.php        : couleurs et typographie modifiables
 * - reglages.php      : données des « Réglages maison » (horaires, contacts…)
 * - page-reglages.php : l'écran « Réglages maison » de l'administration
 * - shortcodes.php    : codes courts [schiesser_horaires], [schiesser_horaires_phrase], [schiesser_adresse]…
 * - produits.php      : type de contenu « Produits boutique », ses champs et ses pages
 * - tea-room.php      : type de contenu « Produits Tea Room » (la carte du salon de thé)
 * - blocs.php         : blocs sur mesure (Hero, Section, Grille) et styles des blocs
 * - avis.php          : avis Google (bloc « Avis clients », API Google ou saisie manuelle)
 * - mise-en-page.php  : styles de texte enregistrés, espacements des sections, boutons
 * - compositions.php  : sections toutes prêtes de l'éditeur
 * - seo.php           : Rank Math et données structurées
 * - woocommerce.php   : préparation de la vente en ligne
 * - admin.php         : habillage de l'administration, tableau de bord, guide
 * - demo.php          : import du contenu de démonstration
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_VERSION', '0.6.0' );
define( 'SCHIESSER_DIR', get_template_directory() );
define( 'SCHIESSER_URI', get_template_directory_uri() );

require SCHIESSER_DIR . '/inc/setup.php';
require SCHIESSER_DIR . '/inc/charte.php';
require SCHIESSER_DIR . '/inc/reglages.php';
require SCHIESSER_DIR . '/inc/page-reglages.php';
require SCHIESSER_DIR . '/inc/shortcodes.php';
require SCHIESSER_DIR . '/inc/produits.php';
require SCHIESSER_DIR . '/inc/tea-room.php';
require SCHIESSER_DIR . '/inc/blocs.php';
require SCHIESSER_DIR . '/inc/maquette.php';
require SCHIESSER_DIR . '/inc/avis.php';
require SCHIESSER_DIR . '/inc/mise-en-page.php';
require SCHIESSER_DIR . '/inc/compositions.php';
require SCHIESSER_DIR . '/inc/seo.php';
require SCHIESSER_DIR . '/inc/woocommerce.php';
require SCHIESSER_DIR . '/inc/admin.php';
require SCHIESSER_DIR . '/inc/demo.php';

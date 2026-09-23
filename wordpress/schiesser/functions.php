<?php
/**
 * Thème Schiesser : point d'entrée.
 *
 * Chaque fichier de inc/ gère une partie du site :
 * - setup.php    : réglages du thème, styles, scripts, navigation
 * - reglages.php : page « Réglages de la maison » (horaires, contacts…)
 * - produits.php : type de contenu « Produits » et ses champs
 * - blocs.php    : blocs de page sur mesure (Hero, Grille produits…)
 * - demo.php     : import du contenu de démonstration
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_VERSION', '0.1.0' );
define( 'SCHIESSER_DIR', get_template_directory() );
define( 'SCHIESSER_URI', get_template_directory_uri() );

require SCHIESSER_DIR . '/inc/setup.php';
require SCHIESSER_DIR . '/inc/reglages.php';
require SCHIESSER_DIR . '/inc/produits.php';
require SCHIESSER_DIR . '/inc/blocs.php';
require SCHIESSER_DIR . '/inc/demo.php';

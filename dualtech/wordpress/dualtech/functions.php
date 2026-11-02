<?php
/**
 * Thème DualTech : point d'entrée.
 *
 * Chaque fichier de inc/ gère une partie du site :
 * - gabarit.php      : en-tête et pied de page de la maquette, repères ({{url:…}}, {{theme}}…), adresses des pages
 * - setup.php        : réglages du thème, styles, scripts, polices locales, nettoyage de WordPress
 * - donnees.php      : partenaires, intervenants et programme (DualTech Studio, sinon données livrées avec le thème)
 * - sections.php     : codes courts des parties dynamiques, au balisage identique à la maquette
 * - seo.php          : Rank Math (titres, descriptions), données structurées (événement, programme, intervenants)
 * - installation.php : Apparence → Installation DualTech (données, pages, réglages, Rank Math en un clic)
 */

defined( 'ABSPATH' ) || exit;

define( 'DUALTECH_VERSION', '1.0.0' );
define( 'DUALTECH_DIR', get_template_directory() );
define( 'DUALTECH_URI', get_template_directory_uri() );

require DUALTECH_DIR . '/inc/gabarit.php';
require DUALTECH_DIR . '/inc/setup.php';
require DUALTECH_DIR . '/inc/donnees.php';
require DUALTECH_DIR . '/inc/sections.php';
require DUALTECH_DIR . '/inc/seo.php';
require DUALTECH_DIR . '/inc/installation.php';

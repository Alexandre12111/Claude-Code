<?php
/**
 * Thème Aurea Media : point d'entrée.
 *
 * Chaque fichier de inc/ gère une partie du site :
 * - gabarit.php      : en-tête et pied de page de la maquette, repères ({{url:…}}, {{theme}}…), adresses des pages
 * - setup.php        : réglages du thème, styles, scripts, polices, nettoyage de WordPress
 * - reglages.php     : coordonnées de l'agence (Apparence → Personnaliser → Aurea Media)
 * - shortcodes.php   : blocs alimentés par Aurea Studio (mur, portfolio, projets, logos, avis)
 * - contact.php      : formulaire de devis (envoi par e-mail, copie dans « Messages reçus »)
 * - seo.php          : Rank Math (titres, descriptions, données structurées), relais sans Rank Math, llms.txt, robots.txt
 * - redirections.php : redirections 301 des anciennes adresses (dans Rank Math, sinon par le thème)
 * - installation.php : Apparence → Installation Aurea (pages, réglages, Rank Math, redirections en un clic)
 * - nettoyage.php    : base propre (anciens articles et pages à la corbeille, chaque adresse redirigée)
 * - articles.php     : compositions d'articles SEO, sommaire automatique, temps de lecture
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_VERSION', '1.0.0' );
define( 'AUREA_DIR', get_template_directory() );
define( 'AUREA_URI', get_template_directory_uri() );

require AUREA_DIR . '/inc/gabarit.php';
require AUREA_DIR . '/inc/setup.php';
require AUREA_DIR . '/inc/reglages.php';
require AUREA_DIR . '/inc/shortcodes.php';
require AUREA_DIR . '/inc/contact.php';
require AUREA_DIR . '/inc/seo.php';
require AUREA_DIR . '/inc/redirections.php';
require AUREA_DIR . '/inc/articles.php';
require AUREA_DIR . '/inc/installation.php';
require AUREA_DIR . '/inc/nettoyage.php';

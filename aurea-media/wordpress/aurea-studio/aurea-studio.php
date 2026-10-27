<?php
/**
 * Plugin Name:       Aurea Studio
 * Description:       Administration du site Aurea Media : réalisations, logos clients et avis Google, partagés par tout le site. Le contenu reste en place quand le thème change.
 * Version:           0.5.1
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * Author URI:        https://aurea-media.fr/
 * Text Domain:       aurea-studio
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_STUDIO_VERSION', '0.5.1' );
define( 'AUREA_STUDIO_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUREA_STUDIO_URL', plugin_dir_url( __FILE__ ) );
define( 'AUREA_STUDIO_MENU', 'aurea-studio' );

/*
 * Le dossier du thème s'appelle « aurea », comme un thème Elementor publié sur wordpress.org.
 * Sans ce garde-fou, WordPress propose sa « mise à jour » et remplace le thème Aurea Media par l'autre.
 */
if ( ! function_exists( 'aurea_proteger_theme' ) ) {
	function aurea_proteger_theme( $maj ) {
		if ( is_object( $maj ) && isset( $maj->response['aurea'] ) && 'Aurea Media' === wp_get_theme( 'aurea' )->get( 'Name' ) ) {
			unset( $maj->response['aurea'] );
		}
		return $maj;
	}
	add_filter( 'site_transient_update_themes', 'aurea_proteger_theme' );
	add_filter( 'auto_update_theme', function ( $maj, $element ) {
		return ( is_object( $element ) && 'aurea' === ( $element->theme ?? '' ) ) ? false : $maj;
	}, 10, 2 );
}

require AUREA_STUDIO_DIR . 'inc/realisations.php';
require AUREA_STUDIO_DIR . 'inc/logos.php';
require AUREA_STUDIO_DIR . 'inc/avis.php';
require AUREA_STUDIO_DIR . 'inc/articles.php';

/* ------------------------------------------------------------------ */
/* Menu « Aurea Studio » dans la colonne de gauche                     */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	add_menu_page(
		'Aurea Studio',
		'Aurea Studio',
		'edit_pages',
		AUREA_STUDIO_MENU,
		'aurea_realisations_ecran',
		'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#a7aaad" d="M10 1 2 18.5h4.2L10 9.6l3.8 8.9H18L10 1Z"/><circle cx="7.6" cy="16" r="1.7" fill="#C9A961"/></svg>' ),
		3
	);
	// Le premier sous menu reprend l'écran principal sous son vrai nom.
	add_submenu_page( AUREA_STUDIO_MENU, 'Réalisations', 'Réalisations', 'edit_pages', AUREA_STUDIO_MENU, 'aurea_realisations_ecran' );
	add_submenu_page( AUREA_STUDIO_MENU, 'Logos clients', 'Logos clients', 'edit_pages', 'aurea-logos', 'aurea_logos_ecran' );
	add_submenu_page( AUREA_STUDIO_MENU, 'Avis Google', 'Avis Google', 'manage_options', 'aurea-avis', 'aurea_avis_ecran' );
	add_submenu_page( AUREA_STUDIO_MENU, 'Fiches des réalisations', 'Fiches complètes', 'edit_pages', 'edit.php?post_type=' . AUREA_REAL );
} );

/**
 * En-tête commun aux écrans du studio.
 */
function aurea_studio_entete( $titre, $sous_titre = '' ) {
	echo '<div class="aurea-entete"><div class="aurea-marque"><svg viewBox="0 0 20 20" aria-hidden="true"><path fill="currentColor" d="M10 1 2 18.5h4.2L10 9.6l3.8 8.9H18L10 1Z"/><circle cx="7.6" cy="16" r="1.7" fill="#C9A961"/></svg><span>Aurea Studio</span></div>';
	echo '<h1>' . esc_html( $titre ) . '</h1>';
	if ( $sous_titre ) {
		echo '<p>' . esc_html( $sous_titre ) . '</p>';
	}
	echo '</div>';
}

/* Les fiches complètes des réalisations restent rangées sous Aurea Studio. */
add_filter( 'parent_file', function ( $parent ) {
	global $typenow;
	return AUREA_REAL === $typenow ? AUREA_STUDIO_MENU : $parent;
} );

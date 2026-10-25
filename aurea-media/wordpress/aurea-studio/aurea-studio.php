<?php
/**
 * Plugin Name:       Aurea Studio
 * Description:       Administration du site Aurea Media : logos clients, et bientôt réalisations et articles SEO. Le contenu reste en place quand le thème change.
 * Version:           0.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * Author URI:        https://aurea-media.fr/
 * Text Domain:       aurea-studio
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_STUDIO_VERSION', '0.1.0' );
define( 'AUREA_STUDIO_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUREA_STUDIO_URL', plugin_dir_url( __FILE__ ) );
define( 'AUREA_STUDIO_MENU', 'aurea-studio' );

require AUREA_STUDIO_DIR . 'inc/logos.php';

/* ------------------------------------------------------------------ */
/* Menu « Aurea Studio » dans la colonne de gauche                     */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	add_menu_page(
		'Aurea Studio',
		'Aurea Studio',
		'edit_pages',
		AUREA_STUDIO_MENU,
		'aurea_logos_ecran',
		'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#a7aaad" d="M10 1 2 18.5h4.2L10 9.6l3.8 8.9H18L10 1Z"/><circle cx="7.6" cy="16" r="1.7" fill="#C9A961"/></svg>' ),
		3
	);
	// Le premier sous menu reprend l'écran principal sous son vrai nom.
	add_submenu_page( AUREA_STUDIO_MENU, 'Logos clients', 'Logos clients', 'edit_pages', AUREA_STUDIO_MENU, 'aurea_logos_ecran' );
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

<?php
/**
 * Plugin Name:       DualTech Studio
 * Description:       Administration du site DualTech : partenaires, intervenants, programme de la journée et demandes reçues par les formulaires. Le contenu reste en place quand le thème change.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * Author URI:        https://aurea-media.fr/
 * Text Domain:       dualtech-studio
 */

defined( 'ABSPATH' ) || exit;

define( 'DTS_VERSION', '1.0.0' );
define( 'DTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'DTS_URL', plugin_dir_url( __FILE__ ) );
define( 'DTS_MENU', 'dualtech-studio' );

require DTS_DIR . 'inc/commun.php';
require DTS_DIR . 'inc/partenaires.php';
require DTS_DIR . 'inc/intervenants.php';
require DTS_DIR . 'inc/programme.php';
require DTS_DIR . 'inc/demandes.php';
require DTS_DIR . 'inc/reglages.php';
require DTS_DIR . 'inc/import.php';

/* ------------------------------------------------------------------ */
/* Menu « DualTech » dans la colonne de gauche                         */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	$nouvelles = dts_demandes_nouvelles();
	$bulle     = $nouvelles ? ' <span class="awaiting-mod count-' . $nouvelles . '"><span class="pending-count">' . $nouvelles . '</span></span>' : '';
	add_menu_page(
		'DualTech',
		'DualTech' . $bulle,
		'edit_pages',
		DTS_MENU,
		'dts_accueil_ecran',
		'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="#a7aaad" d="M2 4h6.5a6 6 0 0 1 0 12H2V4Zm2.6 2.5v7h3.7a3.5 3.5 0 0 0 0-7H4.6Z"/><path fill="#DF2229" d="M11.5 4H18v2.6h-2v9.4h-2.6V6.6h-1.9V4Z"/></svg>' ),
		3
	);
	add_submenu_page( DTS_MENU, 'Tableau de bord DualTech', 'Tableau de bord', 'edit_pages', DTS_MENU, 'dts_accueil_ecran' );
	add_submenu_page( DTS_MENU, 'Partenaires', 'Partenaires', 'edit_pages', 'edit.php?post_type=' . DTS_PARTENAIRE );
	add_submenu_page( DTS_MENU, 'Rubriques des partenaires', '— Rubriques', 'manage_categories', 'edit-tags.php?taxonomy=' . DTS_RUBRIQUE . '&post_type=' . DTS_PARTENAIRE );
	add_submenu_page( DTS_MENU, 'Intervenants', 'Intervenants', 'edit_pages', 'edit.php?post_type=' . DTS_INTERVENANT );
	add_submenu_page( DTS_MENU, 'Programme', 'Programme', 'edit_pages', 'edit.php?post_type=' . DTS_SEANCE );
	add_submenu_page( DTS_MENU, 'Demandes reçues', 'Demandes reçues' . $bulle, 'edit_pages', 'edit.php?post_type=' . DTS_DEMANDE );
	add_submenu_page( DTS_MENU, 'Réglages DualTech', 'Réglages', 'manage_options', 'dualtech-reglages', 'dts_reglages_ecran' );
} );

/* Les écrans des contenus restent rangés sous « DualTech » dans le menu. */
add_filter( 'parent_file', function ( $parent ) {
	global $typenow, $taxnow;
	if ( in_array( $typenow, array( DTS_PARTENAIRE, DTS_INTERVENANT, DTS_SEANCE, DTS_DEMANDE ), true ) || DTS_RUBRIQUE === $taxnow ) {
		return DTS_MENU;
	}
	return $parent;
} );
add_filter( 'submenu_file', function ( $sous ) {
	global $typenow, $taxnow;
	if ( DTS_RUBRIQUE === $taxnow ) {
		return 'edit-tags.php?taxonomy=' . DTS_RUBRIQUE . '&post_type=' . DTS_PARTENAIRE;
	}
	if ( in_array( $typenow, array( DTS_PARTENAIRE, DTS_INTERVENANT, DTS_SEANCE, DTS_DEMANDE ), true ) ) {
		return 'edit.php?post_type=' . $typenow;
	}
	return $sous;
} );

add_action( 'admin_enqueue_scripts', function () {
	$ecran = get_current_screen();
	if ( ! $ecran ) {
		return;
	}
	$nos_ecrans = in_array( $ecran->post_type, array( DTS_PARTENAIRE, DTS_INTERVENANT, DTS_SEANCE, DTS_DEMANDE ), true )
		|| false !== strpos( (string) $ecran->id, 'dualtech' );
	if ( $nos_ecrans ) {
		wp_enqueue_style( 'dualtech-studio', DTS_URL . 'assets/admin.css', array(), DTS_VERSION );
		wp_enqueue_script( 'dualtech-studio', DTS_URL . 'assets/admin.js', array(), DTS_VERSION, true );
	}
} );

register_activation_hook( __FILE__, function () {
	dts_enregistrer_types();
	dts_rubriques_par_defaut();
	flush_rewrite_rules();
} );

/* ------------------------------------------------------------------ */
/* Tableau de bord                                                     */
/* ------------------------------------------------------------------ */

function dts_accueil_ecran() {
	$compte = function ( $type ) {
		$n = wp_count_posts( $type );
		return (int) ( $n->publish ?? 0 ) + (int) ( $n->draft ?? 0 );
	};
	$cartes = array(
		array( 'Partenaires', $compte( DTS_PARTENAIRE ), 'edit.php?post_type=' . DTS_PARTENAIRE, 'Logos, descriptions et rubriques affichés sur les pages Partenaires, L’événement, Prix et Rétrospective.', 'post-new.php?post_type=' . DTS_PARTENAIRE, 'Ajouter un partenaire' ),
		array( 'Intervenants', $compte( DTS_INTERVENANT ), 'edit.php?post_type=' . DTS_INTERVENANT, 'Photos, fonctions et organisations : page Intervenants, accueil et programme.', 'post-new.php?post_type=' . DTS_INTERVENANT, 'Ajouter un intervenant' ),
		array( 'Séances du programme', $compte( DTS_SEANCE ), 'edit.php?post_type=' . DTS_SEANCE, 'Horaires, formats, intervenants et modération : page Programme et temps forts de L’événement.', 'post-new.php?post_type=' . DTS_SEANCE, 'Ajouter une séance' ),
		array( 'Demandes reçues', $compte( DTS_DEMANDE ), 'edit.php?post_type=' . DTS_DEMANDE, dts_demandes_nouvelles() . ' nouvelle(s) demande(s) : contact, partenariat et workshops.', '', '' ),
	);
	echo '<div class="wrap dts">';
	dts_entete( 'Tableau de bord', 'Tout ce qui change d’une édition à l’autre se gère ici, sans toucher aux pages.' );
	echo '<div class="dts-cartes">';
	foreach ( $cartes as $c ) {
		echo '<div class="dts-carte"><p class="dts-carte__nb">' . esc_html( (string) $c[1] ) . '</p><h2><a href="' . esc_url( admin_url( $c[2] ) ) . '">' . esc_html( $c[0] ) . '</a></h2><p>' . esc_html( $c[3] ) . '</p>';
		echo '<p class="dts-carte__actions"><a class="button" href="' . esc_url( admin_url( $c[2] ) ) . '">Gérer</a>';
		if ( $c[4] ) {
			echo ' <a class="button button-primary" href="' . esc_url( admin_url( $c[4] ) ) . '">' . esc_html( $c[5] ) . '</a>';
		}
		echo '</p></div>';
	}
	echo '</div></div>';
}

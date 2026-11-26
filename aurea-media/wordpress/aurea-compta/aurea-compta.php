<?php
/**
 * Plugin Name:       Aurea Compta
 * Description:       Comptabilité de la SASU Aurea Media : banque Qonto, recettes, dépenses, apports en compte courant, TVA (CA3 / CA12), impôt sur les sociétés, clôture annuelle, FEC et exports. Accès réservé aux administrateurs, sur ordinateur et téléphone (adresse /compta).
 * Version:           1.3.5
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Aurea Media
 * Author URI:        https://aurea-media.fr/
 * License:           Propriétaire
 * Text Domain:       aurea-compta
 */

defined( 'ABSPATH' ) || exit;

define( 'AC_VERSION', '1.3.5' );
define( 'AC_DIR', __DIR__ );
define( 'AC_URL', plugin_dir_url( __FILE__ ) );
define( 'AC_CAP', 'manage_options' );

foreach ( array( 'money', 'db', 'rules', 'ledger', 'posting', 'services', 'checks', 'reports', 'vat', 'tax', 'closing',
	'pdf', 'xlsx', 'exports', 'qonto', 'ai', 'backup', 'mileage', 'reprise', 'api', 'admin' ) as $ac_file ) {
	if ( file_exists( AC_DIR . "/includes/class-$ac_file.php" ) ) {
		require_once AC_DIR . "/includes/class-$ac_file.php";
	}
}

register_activation_hook( __FILE__, array( 'AC_DB', 'install' ) );
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'aurea_compta_hourly' );
} );
// Désinstallation : les tables et les pièces sont volontairement conservées
// (obligation de conservation de 10 ans). Exporter les données avant toute suppression manuelle.

add_action( 'plugins_loaded', function () {
	if ( (int) get_option( AC_DB::opt( 'schema' ) ) < AC_DB::SCHEMA_VERSION ) {
		AC_DB::install();
	}
} );

if ( class_exists( 'AC_Api' ) ) {
	AC_Api::init();
}
if ( class_exists( 'AC_Admin' ) ) {
	AC_Admin::init();
}

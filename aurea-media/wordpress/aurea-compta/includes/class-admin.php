<?php
/**
 * Page de l'application dans l'administration (plein écran), adresse courte /compta,
 * installation sur l'écran d'accueil du téléphone, tâches planifiées.
 */
defined( 'ABSPATH' ) || exit;

class AC_Admin {

	const SLUG = 'aurea-compta';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'render_standalone' ), 1 );
		add_action( 'init', array( __CLASS__, 'short_url' ), 1 );
		add_action( 'aurea_compta_hourly', array( __CLASS__, 'hourly' ) );
		add_action( 'init', function () {
			if ( ! wp_next_scheduled( 'aurea_compta_hourly' ) ) {
				wp_schedule_event( time() + 300, 'hourly', 'aurea_compta_hourly' );
			}
		} );
		add_filter( 'plugin_action_links_aurea-compta/aurea-compta.php', function ( $links ) {
			array_unshift( $links, '<a href="' . esc_url( self::url() ) . '">Ouvrir</a>' );
			return $links;
		} );
	}

	public static function url(): string {
		return admin_url( 'admin.php?page=' . self::SLUG );
	}

	public static function menu(): void {
		add_menu_page( 'Aurea Compta', 'Aurea Compta', AC_CAP, self::SLUG, '__return_null', 'dashicons-chart-pie', 3 );
	}

	/** Tâche horaire : synchronisation Qonto et sauvegarde quotidienne. */
	public static function hourly(): void {
		AC_Backup::daily();
		AC_Qonto::maybe_sync();
	}

	/** aurea-media.fr/compta → application (connexion WordPress si nécessaire). */
	public static function short_url(): void {
		$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
		$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		if ( $path === trim( "$home/compta", '/' ) ) {
			wp_safe_redirect( is_user_logged_in() ? self::url() : wp_login_url( self::url() ) );
			exit;
		}
		if ( $path === trim( "$home/compta/manifest.webmanifest", '/' ) ) {
			header( 'Content-Type: application/manifest+json' );
			echo wp_json_encode( array(
				'name' => 'Aurea Compta', 'short_name' => 'Compta', 'start_url' => home_url( '/compta' ), 'scope' => home_url( '/' ), 'display' => 'standalone',
				'background_color' => '#f7f7f5', 'theme_color' => '#9a7a2e', 'lang' => 'fr',
				'icons' => array( array( 'src' => AC_URL . 'assets/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png' ),
					array( 'src' => AC_URL . 'assets/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png' ) ),
			), JSON_UNESCAPED_SLASHES );
			exit;
		}
	}

	/** L'application s'affiche en plein écran, sans l'habillage de l'administration. */
	public static function render_standalone(): void {
		if ( wp_doing_ajax() || ( $_GET['page'] ?? '' ) !== self::SLUG ) {
			return;
		}
		if ( ! current_user_can( AC_CAP ) ) {
			wp_die( 'Accès réservé aux administrateurs.', 403 );
		}
		nocache_headers();
		header( 'X-Frame-Options: SAMEORIGIN' );
		$config = array(
			'root'  => esc_url_raw( rest_url( AC_Api::NS . '/' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
			'admin' => admin_url(),
			'logout' => wp_logout_url( home_url( '/compta' ) ),
			'user'  => wp_get_current_user()->display_name,
			'version' => AC_VERSION,
		);
		$v = AC_VERSION . '-' . filemtime( AC_DIR . '/assets/app.js' );
		?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title>Aurea Compta</title>
<link rel="icon" href="<?php echo esc_url( AC_URL . 'assets/icon-192.png' ); ?>">
<link rel="manifest" href="<?php echo esc_url( home_url( '/compta/manifest.webmanifest' ) ); ?>">
<link rel="apple-touch-icon" href="<?php echo esc_url( AC_URL . 'assets/icon-180.png' ); ?>">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Compta">
<meta name="theme-color" content="#9a7a2e">
<link rel="stylesheet" href="<?php echo esc_url( AC_URL . 'assets/app.css?v=' . $v ); ?>">
</head>
<body>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <div class="brand"><span class="logo">A</span><div><strong>Aurea Media</strong><small>Comptabilité SASU</small></div></div>
    <button class="search-btn" onclick="openSearch()"><span>Rechercher</span><kbd>/</kbd></button>
    <nav id="nav">
      <a href="#/" data-route="">Vue d'ensemble</a>
      <a href="#/banque" data-route="banque">Banque et transactions <span class="count" id="c-banque"></span></a>
      <a href="#/recettes" data-route="recettes">Recettes et factures</a>
      <a href="#/depenses" data-route="depenses">Dépenses et notes de frais <span class="count" id="c-depenses"></span></a>
      <a href="#/associe" data-route="associe">Apports personnels</a>
      <a href="#/tva" data-route="tva">TVA</a>
      <a href="#/comptabilite" data-route="comptabilite">Comptabilité</a>
      <a href="#/cloture" data-route="cloture">Clôture et exports</a>
      <a href="#/parametres" data-route="parametres">Paramètres</a>
    </nav>
    <div class="side-foot">
      <button class="ghost" onclick="openAssistant()">Assistant</button>
      <button class="ghost icon" title="Thème clair ou sombre" onclick="toggleTheme()">◐</button>
      <a class="ghost icon btn" title="Retour au site WordPress" href="<?php echo esc_url( admin_url() ); ?>">⌂</a>
    </div>
  </aside>
  <main>
    <header class="topbar">
      <button class="ghost menu" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
      <h1 id="page-title">Vue d'ensemble</h1>
      <div class="top-actions" id="top-actions"></div>
    </header>
    <div id="banner"></div>
    <section id="view" class="view"></section>
  </main>
</div>
<div id="drawer" class="drawer" aria-hidden="true"><div class="drawer-panel"><button class="ghost close" onclick="closeDrawer()">✕</button><div id="drawer-body"></div></div></div>
<div id="modal" class="modal" aria-hidden="true"><div class="modal-panel" id="modal-body"></div></div>
<div id="toasts"></div>
<script>window.AC = <?php echo wp_json_encode( $config ); ?>;</script>
<script src="<?php echo esc_url( AC_URL . 'assets/app.js?v=' . $v ); ?>"></script>
</body>
</html>
		<?php
		exit;
	}
}

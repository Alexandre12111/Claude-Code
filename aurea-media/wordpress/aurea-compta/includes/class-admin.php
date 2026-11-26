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
				'background_color' => '#0a0a0a', 'theme_color' => '#0a0a0a', 'lang' => 'fr',
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
<meta name="theme-color" content="#0a0a0a">
<link rel="preload" href="<?php echo esc_url( AC_URL . 'assets/fonts/geist.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?php echo esc_url( AC_URL . 'assets/app.css?v=' . $v ); ?>">
</head>
<body>
<a class="sr-only" href="#view">Aller au contenu</a>
<div class="layout">
  <aside class="sidebar" id="sidebar" aria-label="Navigation principale">
    <div class="brand"><span class="logo" aria-hidden="true">A</span><div><strong>Aurea Media</strong><small>Comptabilité SASU</small></div></div>
    <button class="search-btn" onclick="openSearch()" aria-label="Rechercher (raccourci /)"><svg class="icon" aria-hidden="true"><use href="#i-search"/></svg><span>Rechercher</span><kbd aria-hidden="true">/</kbd></button>
    <nav id="nav">
      <a href="#/" data-route=""><svg class="icon" aria-hidden="true"><use href="#i-home"/></svg><span class="label">Vue d'ensemble</span></a>
      <a href="#/operations" data-route="operations"><svg class="icon" aria-hidden="true"><use href="#i-bank"/></svg><span class="label">Opérations</span><span class="count" id="c-operations"></span></a>
      <a href="#/recettes" data-route="recettes"><svg class="icon" aria-hidden="true"><use href="#i-invoice"/></svg><span class="label">Recettes et factures</span></a>
      <a href="#/associe" data-route="associe"><svg class="icon" aria-hidden="true"><use href="#i-user"/></svg><span class="label">Apports personnels</span></a>
      <a href="#/tva" data-route="tva"><svg class="icon" aria-hidden="true"><use href="#i-percent"/></svg><span class="label">TVA</span></a>
      <a href="#/comptabilite" data-route="comptabilite"><svg class="icon" aria-hidden="true"><use href="#i-book"/></svg><span class="label">Comptabilité</span></a>
      <a href="#/cloture" data-route="cloture"><svg class="icon" aria-hidden="true"><use href="#i-archive"/></svg><span class="label">Clôture et exports</span></a>
      <a href="#/parametres" data-route="parametres"><svg class="icon" aria-hidden="true"><use href="#i-sliders"/></svg><span class="label">Paramètres</span></a>
    </nav>
    <div class="side-foot">
      <button class="ghost grow" onclick="openAssistant()"><svg class="icon" aria-hidden="true"><use href="#i-sparkles"/></svg>Assistant</button>
      <button class="ghost icon" id="theme-btn" aria-label="Basculer le thème clair ou sombre" title="Thème clair ou sombre" onclick="toggleTheme()"><svg class="icon" aria-hidden="true"><use href="#i-moon"/></svg></button>
      <a class="btn ghost icon" aria-label="Retour à l'administration WordPress" title="Retour à WordPress" href="<?php echo esc_url( admin_url() ); ?>"><svg class="icon" aria-hidden="true"><use href="#i-external"/></svg></a>
    </div>
  </aside>
  <main>
    <header class="topbar" id="topbar">
      <button class="ghost icon menu" aria-label="Ouvrir le menu" aria-controls="sidebar" aria-expanded="false" onclick="toggleSidebar()"><svg class="icon" aria-hidden="true"><use href="#i-menu"/></svg></button>
      <h1 id="page-title">Vue d'ensemble</h1>
      <div class="top-actions" id="top-actions"></div>
    </header>
    <div id="banner"></div>
    <section id="view" class="view" tabindex="-1" aria-live="polite"></section>
  </main>
</div>
<nav class="bottom-nav" aria-label="Navigation rapide">
  <a href="#/" data-route=""><svg class="icon" aria-hidden="true"><use href="#i-home"/></svg>Accueil</a>
  <a href="#/operations" data-route="operations"><svg class="icon" aria-hidden="true"><use href="#i-bank"/></svg>Opérations<span class="dot" id="b-operations"></span></a>
  <a href="#/recettes" data-route="recettes"><svg class="icon" aria-hidden="true"><use href="#i-invoice"/></svg>Recettes</a>
  <a href="#/tva" data-route="tva"><svg class="icon" aria-hidden="true"><use href="#i-percent"/></svg>TVA</a>
  <button type="button" onclick="toggleSidebar()" aria-label="Plus de rubriques"><svg class="icon" aria-hidden="true"><use href="#i-more"/></svg>Plus</button>
</nav>
<div id="drawer" class="drawer" aria-hidden="true"><div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="drawer-title"><button class="ghost icon close" aria-label="Fermer" onclick="closeDrawer()"><svg class="icon" aria-hidden="true"><use href="#i-x"/></svg></button><div id="drawer-body"></div></div></div>
<div id="modal" class="modal" aria-hidden="true"><div class="modal-panel" id="modal-body" role="dialog" aria-modal="true"></div></div>
<div id="toasts" role="status" aria-live="polite"></div>
<script>window.AC = <?php echo wp_json_encode( $config ); ?>;</script>
<script src="<?php echo esc_url( AC_URL . 'assets/app.js?v=' . $v ); ?>"></script>
</body>
</html>
		<?php
		exit;
	}
}

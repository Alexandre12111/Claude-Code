<?php
/**
 * Langues : le site est en allemand (de-CH), l'administration reste en français.
 *
 * - Pages vues par les visiteurs : langue « de_CH » (balise lang, dates, Rank Math, Google).
 * - Administration, éditeur et page de connexion : français.
 * - Dates et heures du site écrites à la suisse alémanique (« Montag, 6. April », « 7.30 Uhr »),
 *   même si le pack de langue allemand de WordPress n'est pas installé.
 * - Adresses des pages en allemand ; les anciennes adresses françaises redirigent (301).
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_LANGUE_SITE', 'de_CH' );

/** Page de l'administration, de l'éditeur ou de connexion (qui restent en français). */
function schiesser_est_cote_admin() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return true;
	}
	$page = $GLOBALS['pagenow'] ?? '';
	if ( in_array( $page, array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return true;
	}
	// Requêtes de l'éditeur (API REST avec la langue de l'utilisateur) : elles suivent la langue de l'administration.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_GET['_locale'] ) && 'user' === $_GET['_locale'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return true;
	}
	return false;
}

/* Le site public en allemand. */
add_filter( 'locale', function ( $locale ) {
	return schiesser_est_cote_admin() ? $locale : SCHIESSER_LANGUE_SITE;
} );

/* L'administration en français, même si la langue du site est réglée sur l'allemand. */
add_filter( 'determine_locale', function ( $locale ) {
	if ( schiesser_est_cote_admin() && 0 === strpos( (string) $locale, 'de' ) && file_exists( WP_LANG_DIR . '/fr_FR.mo' ) ) {
		return 'fr_FR';
	}
	return $locale;
} );

/* ------------------------------------------------------------------ */
/* Dates et heures en allemand                                         */
/* ------------------------------------------------------------------ */

/**
 * Date en allemand. Formats reconnus : l (Montag), D (Mo.), j, d, F (April), M (Apr.), n, m, Y.
 *
 * @param string|int $date Date « AAAA-MM-JJ » ou horodatage.
 */
function schiesser_datum( $date, $format = 'l, j. F' ) {
	$d = is_numeric( $date )
		? ( new DateTimeImmutable( '@' . (int) $date ) )->setTimezone( wp_timezone() )
		: new DateTimeImmutable( (string) $date . ( 10 === strlen( (string) $date ) ? ' 12:00' : '' ), wp_timezone() );
	$jours  = array( 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag' );
	$courts = array( 'So.', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.' );
	$mois   = array( 1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' );
	$mcourt = array( 1 => 'Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Juni', 'Juli', 'Aug.', 'Sept.', 'Okt.', 'Nov.', 'Dez.' );
	$w      = (int) $d->format( 'w' );
	$n      = (int) $d->format( 'n' );
	$sortie = '';
	foreach ( str_split( $format ) as $c ) {
		switch ( $c ) {
			case 'l':
				$sortie .= $jours[ $w ];
				break;
			case 'D':
				$sortie .= $courts[ $w ];
				break;
			case 'F':
				$sortie .= $mois[ $n ];
				break;
			case 'M':
				$sortie .= $mcourt[ $n ];
				break;
			case 'j':
			case 'd':
			case 'n':
			case 'm':
			case 'Y':
				$sortie .= $d->format( $c );
				break;
			default:
				$sortie .= $c;
		}
	}
	return $sortie;
}

/** Heure à la suisse : « 07:30 » → « 7.30 Uhr », « 08:00 » → « 8 Uhr ». */
function schiesser_uhr( $hhmm, $avec_uhr = true ) {
	$p = explode( ':', (string) $hhmm );
	$h = (int) $p[0];
	$m = isset( $p[1] ) ? (int) $p[1] : 0;
	return ( $m ? $h . '.' . sprintf( '%02d', $m ) : (string) $h ) . ( $avec_uhr ? "\u{00A0}Uhr" : '' );
}

/* ------------------------------------------------------------------ */
/* Adresses des pages                                                  */
/* ------------------------------------------------------------------ */

/** Pages du site : clé => [ adresse allemande, anciennes adresses… ]. */
function schiesser_adresses_pages() {
	return array(
		'accueil'    => array( 'startseite', 'accueil' ),
		'boutique'   => array( 'confiserie', 'boutique', 'la-boutique' ),
		'salon'      => array( 'tea-room', 'salon-de-the' ),
		'histoire'   => array( 'geschichte', 'notre-histoire', 'histoire' ),
		'visiter'    => array( 'besuch', 'nous-visiter', 'visiter' ),
		'contact'    => array( 'kontakt', 'contact' ),
		'entreprise' => array( 'firmengeschenke', 'cadeaux-entreprise' ),
		'mentions'   => array( 'impressum', 'mentions-legales' ),
	);
}

/** Page du site par sa clé (« visiter », « contact »…), quelle que soit son adresse (allemande ou ancienne). */
function schiesser_page( $cle, $publiee = true ) {
	static $cache = array();
	if ( array_key_exists( $cle, $cache ) ) {
		return $cache[ $cle ];
	}
	foreach ( (array) ( schiesser_adresses_pages()[ $cle ] ?? array() ) as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p && ( ! $publiee || 'publish' === $p->post_status ) ) {
			return $cache[ $cle ] = $p;
		}
	}
	return $cache[ $cle ] = null;
}

/** Adresse d'une page du site par sa clé (accueil si elle n'existe pas). */
function schiesser_url_page( $cle, $ancre = '' ) {
	$p = schiesser_page( $cle );
	return ( $p ? get_permalink( $p ) : home_url( '/' ) ) . ( $ancre ? '#' . $ancre : '' );
}

/* Anciennes adresses des fiches produits (/produits/…) : redirigées vers /produkte/…. */
add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$chemin = trim( (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH ), '/' );
	if ( 0 === strpos( $chemin, 'produits/' ) ) {
		$p = get_page_by_path( sanitize_title( basename( $chemin ) ), OBJECT, SCHIESSER_PRODUIT );
		wp_safe_redirect( $p ? get_permalink( $p ) : schiesser_url_page( 'boutique' ), 301 );
		exit;
	}
}, 4 );

/* ------------------------------------------------------------------ */
/* Pack de langue allemand de WordPress                                */
/* ------------------------------------------------------------------ */

/*
 * Les textes du thème sont écrits en allemand ; le pack de langue traduit en plus les rares
 * textes de WordPress et des extensions visibles sur le site (Rank Math, WooCommerce…).
 */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'install_languages' ) || file_exists( WP_LANG_DIR . '/' . SCHIESSER_LANGUE_SITE . '.mo' ) ) {
		return;
	}
	$e = get_current_screen();
	if ( ! $e || ! in_array( $e->id, array( 'dashboard', 'toplevel_page_schiesser-reglages', 'toplevel_page_schiesser-aujourdhui' ), true ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_langue_de' ), 'schiesser_langue_de' );
	echo '<div class="notice notice-info"><p><strong>Site en allemand :</strong> le thème affiche déjà tout le site en allemand. Pour que les quelques textes de WordPress et des extensions (Rank Math, WooCommerce) le soient aussi, installez le pack de langue allemand (Suisse). L’administration reste en français.</p>'
		. '<p><a class="button button-primary" href="' . esc_url( $url ) . '">Installer la langue allemande</a></p></div>';
} );

add_action( 'admin_post_schiesser_langue_de', function () {
	if ( ! current_user_can( 'install_languages' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_langue_de' );
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$ok = wp_download_language_pack( SCHIESSER_LANGUE_SITE );
	wp_safe_redirect( add_query_arg( 'schiesser_langue', $ok ? 'ok' : 'erreur', admin_url( 'admin.php?page=schiesser-reglages' ) ) );
	exit;
} );

add_action( 'admin_notices', function () {
	$r = sanitize_key( $_GET['schiesser_langue'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'ok' === $r ) {
		echo '<div class="notice notice-success is-dismissible"><p>Langue allemande installée : le site public est entièrement en allemand, l’administration reste en français.</p></div>';
	} elseif ( 'erreur' === $r ) {
		echo '<div class="notice notice-error"><p>Le pack de langue n’a pas pu être téléchargé. Autre possibilité : Réglages → Général → Langue du site → « Deutsch (Schweiz) », enregistrer, puis remettre « Français » (le pack reste installé).</p></div>';
	}
} );

<?php
/**
 * Réglages du thème, styles, scripts, polices et nettoyage de WordPress.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );                 // le titre de l'onglet vient de Rank Math
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'aurea-avis' );                // Aurea Studio : avis affichés par le thème
	add_editor_style( 'assets/editeur.css' );
	remove_theme_support( 'core-block-patterns' );    // seules les compositions Aurea sont proposées
	add_image_size( 'aurea-carte', 960, 540, true );  // vignettes des articles
	add_post_type_support( 'page', 'excerpt' );
} );

/* Styles des blocs chargés seulement quand le bloc est utilisé (pages plus légères). */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

add_action( 'wp_enqueue_scripts', function () {
	$v = function ( $f ) {
		return AUREA_VERSION . '.' . (int) @filemtime( AUREA_DIR . '/assets/' . $f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	};
	wp_enqueue_style( 'aurea', AUREA_URI . '/assets/aurea.css', array(), $v( 'aurea.css' ) );
	wp_enqueue_style( 'aurea-wp', AUREA_URI . '/assets/wp.css', array( 'aurea' ), $v( 'wp.css' ) );
	wp_enqueue_script( 'aurea', AUREA_URI . '/assets/aurea.js', array(), $v( 'aurea.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_dequeue_style( 'classic-theme-styles' );
} );

/* Polices préchargées : le texte s'affiche dans la bonne police dès le premier rendu. */
add_action( 'wp_head', function () {
	foreach ( array( 'bricolage-grotesque', 'geist' ) as $police ) {
		echo '<link rel="preload" href="' . esc_url( AUREA_URI . '/assets/polices/' . $police . '.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
	echo '<meta name="theme-color" content="#0A0A0A">' . "\n";
}, 1 );

/* ------------------------------------------------------------------ */
/* Nettoyage                                                           */
/* ------------------------------------------------------------------ */

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
add_filter( 'the_generator', '__return_empty_string' );
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'emoji_svg_url', '__return_false' );

/* Commentaires désactivés : un site d'agence n'en a pas besoin (et c'est la première source de spam). */
add_action( 'init', function () {
	foreach ( array( 'post', 'page' ) as $type ) {
		remove_post_type_support( $type, 'comments' );
		remove_post_type_support( $type, 'trackbacks' );
	}
} );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
} );
add_action( 'wp_before_admin_bar_render', function () {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'comments' );
} );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

/* Extraits des articles : une phrase d'accroche, sans « […] ». */
add_filter( 'excerpt_length', function () {
	return 28;
} );
add_filter( 'excerpt_more', function () {
	return '…';
} );

/* Les pages de la maquette sont du HTML : pas de « smart quotes » qui casseraient les attributs. */
add_filter( 'run_wptexturize', function ( $run ) {
	return is_page() || is_front_page() ? false : $run;
} );

/* Classe de corps de page : page avec son propre hero, ou page de texte. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_singular( 'page' ) ) {
		$classes[] = aurea_a_son_hero() ? 'aurea-maquette' : 'aurea-texte';
	}
	return $classes;
} );

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

/*
 * Elementor reste installé sur le site, mais les pages du thème ne l'utilisent pas : ses styles globaux
 * (liens et boutons dorés, police Roboto) écrasaient le design dès qu'une page avait été ouverte dans Elementor.
 */
function aurea_page_elementor() {
	$id = is_singular() ? get_queried_object_id() : 0;
	return $id && 'builder' === get_post_meta( $id, '_elementor_edit_mode', true ) && ! get_post_meta( $id, '_aurea_version', true );
}
add_action( 'wp_enqueue_scripts', function () {
	if ( aurea_page_elementor() ) {
		return;
	}
	foreach ( wp_styles()->queue as $poignee ) {
		$src = (string) ( wp_styles()->registered[ $poignee ]->src ?? '' );
		if ( 0 === strpos( $poignee, 'elementor' ) || 0 === strpos( $poignee, 'eael' ) || false !== strpos( $src, '/elementor/' ) || false !== strpos( $src, 'essential-addons-for-elementor' ) ) {
			wp_dequeue_style( $poignee );
		}
	}
}, 9999 );
add_filter( 'elementor/frontend/print_google_fonts', function ( $oui ) {
	return aurea_page_elementor() ? $oui : false;
} );
add_filter( 'body_class', function ( $classes ) {
	return aurea_page_elementor() ? $classes : array_values( array_filter( $classes, function ( $c ) {
		return 0 !== strpos( $c, 'elementor-' );
	} ) );
}, 99 );

/* Une fois par version : les pages du thème ouvertes par erreur dans Elementor redeviennent des pages normales. */
add_action( 'admin_init', function () {
	if ( get_option( 'aurea_elementor_version' ) === AUREA_VERSION ) {
		return;
	}
	update_option( 'aurea_elementor_version', AUREA_VERSION );
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_aurea_version' ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( get_post_meta( $id, '_elementor_edit_mode', true ) ) {
			delete_post_meta( $id, '_elementor_edit_mode' );
			delete_post_meta( $id, '_elementor_data' );
			delete_post_meta( $id, '_elementor_css' );
		}
	}
} );

<?php
/**
 * Réglages du thème : styles, scripts et polices de la maquette, nettoyage de ce que WordPress ajoute
 * et dont le site n'a pas besoin (emojis, styles des blocs, styles globaux).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
} );

add_action( 'wp_enqueue_scripts', function () {
	$v = DUALTECH_VERSION;
	wp_enqueue_style( 'dualtech-polices', DUALTECH_URI . '/assets/polices.css', array(), $v );
	wp_enqueue_style( 'dualtech', DUALTECH_URI . '/assets/dualtech.css', array( 'dualtech-polices' ), $v );
	wp_enqueue_style( 'dualtech-wp', DUALTECH_URI . '/assets/wp.css', array( 'dualtech' ), $v );
	wp_enqueue_script( 'gsap', DUALTECH_URI . '/assets/vendor/gsap.min.js', array(), '3.12.5', array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_enqueue_script( 'gsap-scrolltrigger', DUALTECH_URI . '/assets/vendor/ScrollTrigger.min.js', array( 'gsap' ), '3.12.5', array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_enqueue_script( 'dualtech', DUALTECH_URI . '/assets/dualtech.js', array(), $v, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_enqueue_script( 'dualtech-fx', DUALTECH_URI . '/assets/dualtech-fx.js', array( 'gsap-scrolltrigger', 'dualtech' ), $v, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	// Le site n'utilise pas les styles des blocs de WordPress (pages en HTML de la maquette)
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $s ) {
		wp_dequeue_style( $s );
	}
}, 20 );

/* Comme dans la maquette : la classe « js » active les animations ; image du hero préchargée sur l'accueil. */
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js')</script>\n";
	foreach ( array( 'archivo-latin', 'public-sans-latin' ) as $police ) {
		$fichiers = glob( DUALTECH_DIR . '/assets/polices/' . $police . '-[!e]*.woff2' );
		if ( $fichiers ) {
			echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url( DUALTECH_URI . '/assets/polices/' . basename( $fichiers[0] ) ) . "\">\n";
		}
	}
	$info = dualtech_info_page();
	if ( ! empty( $info['precharge'] ) ) {
		echo '<link rel="preload" as="image" href="' . esc_url( DUALTECH_URI . '/' . $info['precharge'] ) . "\" fetchpriority=\"high\">\n";
	}
	echo '<meta name="theme-color" content="#041226">' . "\n";
}, 1 );

/* Icône du site : celle de la maquette, tant qu'aucune n'est choisie dans Apparence → Personnaliser. */
add_action( 'wp_head', function () {
	if ( has_site_icon() ) {
		return;
	}
	echo '<link rel="icon" href="' . esc_url( DUALTECH_URI . '/assets/favicon-32.png' ) . '" sizes="32x32">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( DUALTECH_URI . '/assets/favicon-180.png' ) . '">' . "\n";
}, 2 );

/* Nettoyage */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );

/* La barre d'administration (connecté) ne doit pas masquer le menu fixe : décalage dans assets/wp.css. */

/* L'éditeur de blocs ne réécrit pas le HTML de la maquette : pas de mise en forme automatique dans les pages. */
add_filter( 'the_content', function ( $contenu ) {
	if ( is_page() && dualtech_a_son_entete() ) {
		remove_filter( 'the_content', 'wpautop' );
	}
	return $contenu;
}, 1 );

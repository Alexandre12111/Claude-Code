<?php
/**
 * Réglages du thème, feuilles de style, scripts et navigation.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 360, 'flex-height' => true, 'flex-width' => true ) );

	// Prêt pour la vente en ligne : il suffira d'installer WooCommerce.
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1000,
		'product_grid'          => array( 'default_columns' => 4, 'min_columns' => 2, 'max_columns' => 4 ),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'principal' => 'Menu principal (en-tête)',
		'pied'      => 'Menu du pied de page (colonne « Explorer »)',
	) );
} );

/**
 * Styles partagés par le site et l'éditeur de blocs.
 * Les blocs y font référence dans leur block.json : l'éditeur affiche
 * donc exactement le même rendu que le site, charte graphique comprise.
 */
add_action( 'init', function () {
	$google = function_exists( 'schiesser_url_google_fonts' ) ? schiesser_url_google_fonts() : '';
	wp_register_style( 'schiesser-polices', $google ?: false, array(), null ); // false : polices hébergées, aucun appel externe
	wp_register_style( 'schiesser-site', SCHIESSER_URI . '/assets/css/schiesser.css', array( 'schiesser-polices' ), SCHIESSER_VERSION );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'schiesser-site' );

	wp_enqueue_script( 'schiesser-site', SCHIESSER_URI . '/assets/js/site.js', array(), SCHIESSER_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script( 'schiesser-site', 'SCHIESSER', array(
		'horaires' => schiesser_horaires_js(),
		'langue'   => substr( get_locale(), 0, 2 ),
		'fuseau'   => wp_timezone_string(), // l'état « Ouvert / Fermé » suit l'heure de Bâle, où que soit le visiteur
	) );
} );

/* JavaScript disponible : les effets d'apparition peuvent masquer les éléments en attendant leur arrivée. */
add_action( 'wp_head', function () {
	echo "<script>document.documentElement.classList.add('js')</script>\n";
}, 0 );

add_action( 'wp_head', function () {
	echo '<meta name="theme-color" content="' . esc_attr( schiesser_charte()['couleurs']['dark'] ) . '">' . "\n";

	// Précharge les polices hébergées utilisées au-dessus de la ligne de flottaison (titre du hero).
	$c = schiesser_charte();
	if ( 'bodoni-moda' === $c['titres'] ) {
		echo '<link rel="preload" href="' . esc_url( SCHIESSER_URI . '/assets/fonts/bodoni-moda.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
	if ( 'inter' === $c['texte'] ) {
		echo '<link rel="preload" href="' . esc_url( SCHIESSER_URI . '/assets/fonts/inter.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
	if ( schiesser_url_google_fonts() ) {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	}
}, 1 );

/*
 * Images : toutes celles du contenu se chargent au défilement (« lazy »).
 * Les photos visibles dès l'ouverture (grande photo du Hero, photo d'une page produit)
 * demandent elles-mêmes un chargement immédiat et prioritaire.
 */
add_filter( 'wp_omit_loading_attr_threshold', '__return_zero' );

/* En-tête HTML allégé : version de WordPress, liens techniques inutiles pour ce site. */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

/* Allègement : les émojis de WordPress ne servent pas ici (un script et un style en moins). */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
} );

/**
 * Identifiant de page utilisé par la feuille de style (#page-home, #page-shop…).
 */
function schiesser_cle_page() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_singular( SCHIESSER_PRODUIT ) || ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) ) {
		return 'shop';
	}
	$correspondances = array(
		'boutique'       => 'shop',
		'la-boutique'    => 'shop',
		'tea-room'       => 'tearoom',
		'salon-de-the'   => 'tearoom',
		'notre-histoire' => 'story',
		'histoire'       => 'story',
		'nous-visiter'   => 'visit',
		'visiter'        => 'visit',
		'contact'        => 'contact',
	);
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $correspondances[ $slug ] ) ) {
			return $correspondances[ $slug ];
		}
	}
	return 'page';
}

/** Adresse de la page boutique (réglage, sinon page « boutique », sinon accueil). */
function schiesser_url_boutique() {
	$id = (int) schiesser_reglage( 'page_boutique' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	$page = get_page_by_path( 'boutique' );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * Liens du menu principal. On lit le menu WordPress « Menu principal » ;
 * s'il n'existe pas encore, on liste les pages publiées.
 *
 * @return array Liste de [ 'titre' => …, 'url' => …, 'actif' => bool ].
 */
function schiesser_liens_menu( $position = 'principal' ) {
	$liens     = array();
	$courant   = get_queried_object_id();
	$positions = get_nav_menu_locations();
	if ( empty( $positions[ $position ] ) ) {
		$position = 'principal'; // Pas de menu de pied de page : on reprend le menu principal.
	}

	if ( ! empty( $positions[ $position ] ) ) {
		$elements = wp_get_nav_menu_items( $positions[ $position ] );
		foreach ( (array) $elements as $el ) {
			if ( (int) $el->menu_item_parent ) {
				continue;
			}
			$actif = 'post_type' === $el->type && (int) $el->object_id === $courant;
			// Une page produit « appartient » à la boutique dans le menu.
			if ( ! $actif && is_singular( SCHIESSER_PRODUIT ) && 'post_type' === $el->type && untrailingslashit( get_permalink( $el->object_id ) ) === untrailingslashit( schiesser_url_boutique() ) ) {
				$actif = true;
			}
			$liens[] = array(
				'titre' => $el->title,
				'url'   => $el->url,
				'actif' => $actif,
			);
		}
		return $liens;
	}

	$pages   = get_pages( array( 'sort_column' => 'menu_order,post_title', 'parent' => 0 ) );
	$accueil = (int) get_option( 'page_on_front' );
	foreach ( $pages as $p ) {
		if ( $p->ID === $accueil ) {
			continue;
		}
		$liens[] = array(
			'titre' => get_the_title( $p ),
			'url'   => get_permalink( $p ),
			'actif' => $p->ID === $courant,
		);
	}
	return $liens;
}

/**
 * Logo de la maison (réutilisé dans l'en-tête et le pied de page).
 */
function schiesser_logo( $sous_titre = 'Seit 1870' ) {
	?>
	<span class="x-lg-over">Confiserie <i class="x-diamond x-lg-dia"></i> Tea-Room</span>
	<span class="x-lg-name">Schiesser</span>
	<span class="x-lg-under" lang="de"><?php echo esc_html( $sous_titre ); ?></span>
	<?php
}

/**
 * Image responsive d'une pièce jointe (srcset), avec texte alternatif garanti.
 */
function schiesser_image( $id, $taille, $attributs = array(), $alt_secours = '' ) {
	if ( ! $id ) {
		return '';
	}
	$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
	if ( '' === trim( (string) $alt ) ) {
		$attributs['alt'] = $alt_secours;
	}
	return wp_get_attachment_image( $id, $taille, false, $attributs );
}

/**
 * Anciennes adresses de pages (ex. /tea-room/ devenue /salon-de-the/) : redirection 301.
 * WordPress le fait pour les articles, pas pour les pages ; Rank Math le fera ensuite
 * automatiquement (module Redirections) pour tout changement d'adresse.
 */
add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$chemin = trim( (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH ), '/' );
	$slug   = sanitize_title( basename( $chemin ) );
	if ( '' === $slug ) {
		return;
	}
	$pages = get_posts( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'numberposts' => 1,
		'fields'      => 'ids',
		'meta_key'    => '_schiesser_ancien_slug', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'  => $slug, // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	if ( $pages ) {
		wp_safe_redirect( get_permalink( $pages[0] ), 301 );
		exit;
	}
}, 5 );

/**
 * Typographie française : espace insécable avant « : ; ? ! », à l'intérieur des guillemets « »,
 * dans les prix (« CHF 6.50 ») et entre un nombre et son unité (« 100 g », « 7 h 30 »).
 * Un deux-points ou un prix ne se retrouve ainsi jamais coupé en fin de ligne.
 * Seul le texte est modifié, jamais les balises, les scripts, les styles ni le code.
 */
function schiesser_typographie_fr( $html ) {
	if ( ! is_string( $html ) || ! preg_match( '/ [:;?!»]|« |CHF \d|\d (g|kg|h)\b/u', $html ) ) {
		return $html;
	}
	$parties = preg_split( '/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( false === $parties ) {
		return $html; // texte mal encodé : laissé tel quel
	}
	$code = 0;
	foreach ( $parties as $i => $partie ) {
		if ( '' === $partie ) {
			continue;
		}
		if ( '<' === $partie[0] ) {
			if ( preg_match( '#^<(script|style|pre|code|textarea|kbd|samp)\b#i', $partie ) ) {
				$code++;
			} elseif ( $code && preg_match( '#^</(script|style|pre|code|textarea|kbd|samp)\s*>#i', $partie ) ) {
				$code--;
			}
			continue;
		}
		if ( ! $code ) {
			$parties[ $i ] = preg_replace(
				array( '/ ([:;?!»])/u', '/« /u', '/\bCHF (?=\d)/u', '/(\d) h (?=\d)/u', '/(\d) (g|kg|h)\b/u' ),
				array( "\u{00A0}" . '$1', "«\u{00A0}", "CHF\u{00A0}", '$1' . "\u{00A0}h\u{00A0}", '$1' . "\u{00A0}" . '$2' ),
				$partie
			);
		}
	}
	return implode( '', $parties );
}
add_filter( 'the_content', 'schiesser_typographie_fr', 20 );

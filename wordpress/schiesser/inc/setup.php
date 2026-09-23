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

	register_nav_menus( array(
		'principal' => 'Menu principal (en-tête)',
	) );
} );

/**
 * Styles partagés par le site et l'éditeur de blocs.
 * Les blocs y font référence dans leur block.json : l'éditeur affiche
 * donc exactement le même rendu que le site.
 */
add_action( 'init', function () {
	wp_register_style(
		'schiesser-polices',
		'https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,500;0,6..96,600;1,6..96,400;1,6..96,500&family=Inter:wght@400;500;600&display=swap',
		array(),
		null
	);
	wp_register_style(
		'schiesser-site',
		SCHIESSER_URI . '/assets/css/schiesser.css',
		array( 'schiesser-polices' ),
		SCHIESSER_VERSION
	);
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'schiesser-site' );

	wp_enqueue_script( 'schiesser-site', SCHIESSER_URI . '/assets/js/site.js', array(), SCHIESSER_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script( 'schiesser-site', 'SCHIESSER', array(
		'horaires' => schiesser_horaires_js(),
		'langue'   => 'fr',
	) );
} );

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<meta name="theme-color" content="#271B12">' . "\n";
}, 1 );

/**
 * Identifiant de page utilisé par la feuille de style (#page-home, #page-shop…).
 * On le déduit du slug de la page, avec un réglage par défaut.
 */
function schiesser_cle_page() {
	if ( is_front_page() ) {
		return 'home';
	}
	$correspondances = array(
		'boutique'       => 'shop',
		'la-boutique'    => 'shop',
		'tea-room'       => 'tearoom',
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

/**
 * Liens du menu principal. On lit le menu WordPress « Menu principal » ;
 * s'il n'existe pas encore, on liste les pages publiées.
 *
 * @return array Liste de [ 'titre' => …, 'url' => …, 'actif' => bool ].
 */
function schiesser_liens_menu() {
	$liens     = array();
	$courant   = get_queried_object_id();
	$positions = get_nav_menu_locations();

	if ( ! empty( $positions['principal'] ) ) {
		$elements = wp_get_nav_menu_items( $positions['principal'] );
		foreach ( (array) $elements as $el ) {
			if ( (int) $el->menu_item_parent ) {
				continue;
			}
			$liens[] = array(
				'titre' => $el->title,
				'url'   => $el->url,
				'actif' => 'post_type' === $el->type && (int) $el->object_id === $courant,
			);
		}
		return $liens;
	}

	$pages = get_pages( array( 'sort_column' => 'menu_order,post_title', 'parent' => 0 ) );
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
	<span class="x-lg-under"><?php echo esc_html( $sous_titre ); ?></span>
	<?php
}

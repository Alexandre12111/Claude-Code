<?php
/**
 * Blocs de page sur mesure.
 *
 * Chaque dossier de blocks/ contient :
 * - block.json : nom, champs (attributs), styles
 * - index.js   : l'édition dans l'éditeur (texte modifiable directement)
 * - render.php : le HTML affiché sur le site, identique à la maquette
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'block_categories_all', function ( $categories ) {
	array_unshift( $categories, array(
		'slug'  => 'schiesser',
		'title' => 'Schiesser',
		'icon'  => null,
	) );
	return $categories;
} );

add_action( 'init', function () {
	foreach ( array( 'hero', 'produits' ) as $bloc ) {
		register_block_type( SCHIESSER_DIR . '/blocks/' . $bloc );
	}
} );

/* Le script de la boutique n'est chargé que sur les pages qui affichent la grille. */
add_action( 'init', function () {
	wp_register_script( 'schiesser-boutique', SCHIESSER_URI . '/assets/js/boutique.js', array(), SCHIESSER_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
} );

/**
 * Sceau tournant « Confiserie · Tea-Room · Marktplatz Basel · Seit 1870 ».
 */
function schiesser_sceau_svg() {
	return '<div class="hero-seal" aria-hidden="true"><svg viewBox="0 0 200 200">'
		. '<defs><path id="sealRing" d="M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0"/></defs>'
		. '<g class="ring"><text font-family="Inter, sans-serif" font-size="11" font-weight="600" letter-spacing="3" fill="currentColor">'
		. '<textPath href="#sealRing" textLength="496" lengthAdjust="spacing">CONFISERIE · TEA-ROOM · MARKTPLATZ BASEL · SEIT 1870 ·</textPath></text></g>'
		. '<circle cx="100" cy="100" r="64" fill="none" stroke="currentColor" stroke-opacity=".55"/>'
		. '<circle cx="100" cy="100" r="58" fill="none" stroke="currentColor" stroke-opacity=".4" stroke-dasharray="1 4"/>'
		. '<text x="100" y="121" text-anchor="middle" font-family="Bodoni Moda, serif" font-size="58" font-weight="500" fill="currentColor">S</text>'
		. '</svg></div>';
}

/**
 * Balises autorisées dans les titres modifiables (italique et retour à la ligne).
 */
function schiesser_kses_titre( $html ) {
	return wp_kses( $html, array( 'em' => array(), 'br' => array(), 'strong' => array() ) );
}

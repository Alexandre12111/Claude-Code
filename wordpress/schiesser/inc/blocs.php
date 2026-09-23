<?php
/**
 * Blocs de page sur mesure et styles des blocs WordPress.
 *
 * Chaque dossier de blocks/ contient :
 * - block.json : nom, champs (attributs), styles
 * - index.js   : l'édition dans l'éditeur (texte modifiable directement)
 * - render.php : le HTML affiché sur le site, identique à la maquette
 *
 * Les blocs standard de WordPress (paragraphe, bouton, colonnes, image…)
 * reçoivent ici des « styles » maison : le client les choisit dans le
 * panneau de droite, rubrique « Styles », sans rien coder.
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
	foreach ( array( 'hero', 'section', 'produits' ) as $bloc ) {
		register_block_type( SCHIESSER_DIR . '/blocks/' . $bloc );
	}
} );

/* Le script de la boutique n'est chargé que sur les pages qui affichent la grille. */
add_action( 'init', function () {
	wp_register_script( 'schiesser-boutique', SCHIESSER_URI . '/assets/js/boutique.js', array(), SCHIESSER_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
}, 5 );

/* ------------------------------------------------------------------ */
/* Styles maison des blocs standard                                    */
/* ------------------------------------------------------------------ */

/**
 * Liste des styles : bloc => [ nom => libellé ].
 * Le rendu de chaque style est dans assets/css/schiesser.css (« Contenu libre »).
 */
function schiesser_styles_blocs() {
	return array(
		'core/button'    => array(
			'vert'    => 'Vert maison',
			'contour' => 'Contour',
			'creme'   => 'Crème (sur fond sombre)',
			'lien'    => 'Lien fléché',
		),
		'core/paragraph' => array(
			'chapeau'  => 'Chapeau (introduction)',
			'surtitre' => 'Surtitre',
		),
		'core/heading'   => array(
			'surtitre' => 'Surtitre',
		),
		'core/list'      => array(
			'filets' => 'Liste à filets',
		),
		'core/columns'   => array(
			'filets' => 'Grille à filets',
			'cartes' => 'Cartes',
		),
		'core/group'     => array(
			'carte'        => 'Carte encadrée',
			'carte-sombre' => 'Carte chocolat',
		),
		'core/image'     => array(
			'vitrine' => 'Cadre vitrine',
		),
		'core/separator' => array(
			'losanges' => 'Losanges maison',
		),
		'core/quote'     => array(
			'grande' => 'Grande citation',
		),
	);
}

add_action( 'init', function () {
	foreach ( schiesser_styles_blocs() as $bloc => $styles ) {
		foreach ( $styles as $nom => $libelle ) {
			register_block_style( $bloc, array(
				'name'       => $nom,
				'label'      => $libelle,
				'is_default' => 'core/button' === $bloc && 'vert' === $nom,
			) );
		}
	}
} );

/**
 * Script de l'éditeur : retire les styles WordPress qui ne suivent pas la charte
 * (boutons arrondis, images rondes…) et transmet à Rank Math le texte des blocs maison.
 */
add_action( 'enqueue_block_editor_assets', function () {
	// Typographie et couleur au clic sur le texte (tous les blocs) : chargé en premier.
	wp_enqueue_script(
		'schiesser-typographie',
		SCHIESSER_URI . '/assets/js/typographie.js',
		array( 'wp-rich-text', 'wp-block-editor', 'wp-element', 'wp-components', 'wp-data', 'wp-hooks' ),
		SCHIESSER_VERSION,
		false
	);
	wp_enqueue_script(
		'schiesser-editeur',
		SCHIESSER_URI . '/assets/js/editeur.js',
		array( 'wp-blocks', 'wp-dom-ready', 'wp-data', 'wp-hooks' ),
		SCHIESSER_VERSION,
		true
	);
} );

/* Une image laissée vide dans une composition n'affiche rien sur le site (au lieu d'une image cassée). */
add_filter( 'render_block_core/image', function ( $html ) {
	if ( false !== strpos( $html, '<img' ) && ! preg_match( '/<img[^>]+src=["\'][^"\']+["\']/', $html ) ) {
		return '';
	}
	return $html;
} );

/* Boutons : ajoute la flèche maison au style « Lien fléché ». */
add_filter( 'render_block_core/button', function ( $html, $bloc ) {
	if ( false !== strpos( $bloc['attrs']['className'] ?? '', 'is-style-lien' ) ) {
		$html = preg_replace( '#</a>#', ' <span class="a" aria-hidden="true">→</span></a>', $html, 1 );
	}
	return $html;
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* Petits utilitaires partagés                                         */
/* ------------------------------------------------------------------ */

/**
 * Sceau tournant « Confiserie · Tea-Room · Marktplatz Basel · Seit 1870 ».
 * Les polices suivent la charte (classes seal-txt et seal-s dans la feuille de style).
 */
function schiesser_sceau_svg() {
	return '<div class="hero-seal" aria-hidden="true"><svg viewBox="0 0 200 200">'
		. '<defs><path id="sealRing" d="M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0"/></defs>'
		. '<g class="ring"><text class="seal-txt" font-size="11" font-weight="600" letter-spacing="3" fill="currentColor">'
		. '<textPath href="#sealRing" textLength="496" lengthAdjust="spacing">CONFISERIE · TEA-ROOM · MARKTPLATZ BASEL · SEIT 1870 ·</textPath></text></g>'
		. '<circle cx="100" cy="100" r="64" fill="none" stroke="currentColor" stroke-opacity=".55"/>'
		. '<circle cx="100" cy="100" r="58" fill="none" stroke="currentColor" stroke-opacity=".4" stroke-dasharray="1 4"/>'
		. '<text class="seal-s" x="100" y="121" text-anchor="middle" font-size="58" font-weight="500" fill="currentColor">S</text>'
		. '</svg></div>';
}

/**
 * Balises autorisées dans les titres modifiables (italique et retour à la ligne).
 */
function schiesser_kses_titre( $html ) {
	return wp_kses( (string) $html, schiesser_kses_mise_en_forme( false ) );
}

/**
 * Balises de mise en forme permises dans les textes des blocs maison.
 * Typographie (police, taille, majuscules) : <span class="…"> ; couleur : <mark> de l'éditeur.
 *
 * @param bool $liens Autoriser les liens (textes longs uniquement).
 */
function schiesser_kses_mise_en_forme( $liens = false ) {
	$balises = array(
		'em'     => array( 'class' => true ),
		'strong' => array( 'class' => true ),
		'b'      => array(),
		'i'      => array(),
		'br'     => array(),
		's'      => array(),
		'sub'    => array(),
		'sup'    => array(),
		'mark'   => array( 'class' => true, 'style' => true ),
		'span'   => array( 'class' => true, 'style' => true ),
	);
	if ( $liens ) {
		$balises['a'] = array( 'href' => true, 'target' => true, 'rel' => true, 'class' => true );
	}
	return $balises;
}

/**
 * Texte brut d'un titre saisi dans un bloc (sans balises, espaces normalisés).
 */
function schiesser_texte_brut( $html ) {
	$texte = wp_strip_all_tags( str_replace( array( '<br>', '<br/>', '<br />' ), ' ', (string) $html ) );
	return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $texte, ENT_QUOTES, 'UTF-8' ) ) );
}

/**
 * Premier bloc d'un type donné dans un contenu (recherche aussi dans les blocs imbriqués).
 *
 * @return array|null Le bloc analysé par parse_blocks().
 */
function schiesser_trouver_bloc( $nom, $blocs ) {
	foreach ( $blocs as $b ) {
		if ( $nom === $b['blockName'] ) {
			return $b;
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			$trouve = schiesser_trouver_bloc( $nom, $b['innerBlocks'] );
			if ( $trouve ) {
				return $trouve;
			}
		}
	}
	return null;
}

/**
 * Tous les blocs d'un type donné (blocs imbriqués compris).
 */
function schiesser_tous_les_blocs( $nom, $blocs, &$liste = array() ) {
	foreach ( $blocs as $b ) {
		if ( $nom === $b['blockName'] ) {
			$liste[] = $b;
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			schiesser_tous_les_blocs( $nom, $b['innerBlocks'], $liste );
		}
	}
	return $liste;
}

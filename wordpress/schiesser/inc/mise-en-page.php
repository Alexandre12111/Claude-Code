<?php
/**
 * Mise en page sans code, dans l'esprit d'Elementor.
 *
 * - Styles de texte enregistrés : « Accroche verte », « Petit label »… créés depuis le
 *   panneau « Aa » de l'éditeur, appliqués en un clic. Modifier un style met à jour
 *   tous les textes qui l'utilisent.
 * - Espacement au-dessus et en dessous de chaque section (curseurs du panneau de droite).
 * - Boutons d'une section : couleur, couleur du texte, taille et arrondi.
 *
 * Édition : assets/js/typographie.js (styles) et assets/js/mise-en-page.js (sections).
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Styles de texte enregistrés                                         */
/* ------------------------------------------------------------------ */

define( 'SCHIESSER_OPTION_STYLES', 'schiesser_styles_texte' );

/** Propriétés autorisées dans un style enregistré, et le motif de leur valeur. */
function schiesser_styles_proprietes() {
	return array(
		'font-family'     => '/^var\(--(serif|sans)\)$/',
		'font-size'       => '/^\d{1,2}(\.\d{1,3})?em$/',
		'--fs-m'          => '/^\d{1,2}(\.\d{1,3})?em$/',
		'font-weight'     => '/^[1-9]00$/',
		'font-style'      => '/^italic$/',
		'text-transform'  => '/^uppercase$/',
		'letter-spacing'  => '/^-?\d(\.\d{1,3})?em$/',
		'text-decoration' => '/^underline$/',
		'color'           => '/^#[0-9a-fA-F]{3,8}$/',
	);
}

/** Nettoie une liste de styles reçue de l'éditeur. */
function schiesser_styles_nettoyer( $liste ) {
	$props  = schiesser_styles_proprietes();
	$propre = array();
	$vus    = array();
	foreach ( (array) $liste as $s ) {
		if ( ! is_array( $s ) ) {
			continue;
		}
		$nom = trim( wp_strip_all_tags( (string) ( $s['nom'] ?? '' ) ) );
		$id  = sanitize_key( (string) ( $s['id'] ?? '' ) );
		if ( '' === $nom ) {
			continue;
		}
		if ( '' === $id ) {
			$id = sanitize_key( sanitize_title( $nom ) ) ?: 'style';
		}
		$base = $id;
		$n    = 2;
		while ( isset( $vus[ $id ] ) ) {
			$id = $base . '-' . $n++;
		}
		$vus[ $id ] = true;
		$css        = array();
		foreach ( (array) ( $s['css'] ?? array() ) as $k => $v ) {
			$v = trim( (string) $v );
			if ( isset( $props[ $k ] ) && preg_match( $props[ $k ], $v ) ) {
				$css[ $k ] = $v;
			}
		}
		$propre[] = array(
			'id'  => $id,
			'nom' => mb_substr( $nom, 0, 40 ),
			'css' => $css,
		);
	}
	return array_slice( $propre, 0, 40 );
}

function schiesser_styles_texte() {
	$liste = get_option( SCHIESSER_OPTION_STYLES, null );
	if ( null === $liste ) {
		// Deux exemples pour commencer (modifiables ou supprimables depuis le panneau « Aa »).
		$c     = function_exists( 'schiesser_charte' ) ? schiesser_charte()['couleurs'] : array();
		$liste = array(
			array( 'id' => 'accroche-verte', 'nom' => 'Accroche verte', 'css' => array( 'font-family' => 'var(--serif)', 'font-style' => 'italic', 'font-size' => '1.3em', '--fs-m' => '1.1em', 'color' => $c['vert'] ?? '#23503B' ) ),
			array( 'id' => 'petit-label', 'nom' => 'Petit label', 'css' => array( 'font-family' => 'var(--sans)', 'font-size' => '0.75em', 'font-weight' => '600', 'text-transform' => 'uppercase', 'letter-spacing' => '0.16em' ) ),
		);
	}
	return schiesser_styles_nettoyer( $liste );
}

/** Feuille de style des styles enregistrés : .sty-accroche-verte{…}. */
function schiesser_styles_css( $liste = null ) {
	$liste  = null === $liste ? schiesser_styles_texte() : $liste;
	$css    = '';
	$mobile = '';
	foreach ( $liste as $s ) {
		$decl = array();
		foreach ( $s['css'] as $k => $v ) {
			if ( '--fs-m' === $k ) {
				$mobile .= '.sty-' . $s['id'] . '{font-size:' . $v . '}';
				continue;
			}
			$decl[] = $k . ':' . $v;
		}
		if ( $decl ) {
			$css .= '.sty-' . $s['id'] . '{' . implode( ';', $decl ) . '}';
		}
	}
	if ( $mobile ) {
		$css .= '@media(max-width:760px){' . $mobile . '}';
	}
	return $css;
}

add_action( 'init', function () {
	$css = schiesser_styles_css();
	if ( $css ) {
		wp_add_inline_style( 'schiesser-site', "/* Styles de texte enregistrés */\n" . $css );
	}
}, 19 ); // avant le CSS personnalisé, qui reste le dernier mot

add_action( 'rest_api_init', function () {
	register_rest_route( 'schiesser/v1', '/styles-texte', array(
		array(
			'methods'             => 'GET',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'callback'            => function () {
				return array( 'styles' => schiesser_styles_texte() );
			},
		),
		array(
			'methods'             => 'POST',
			'permission_callback' => function () {
				return current_user_can( 'edit_theme_options' );
			},
			'callback'            => function ( WP_REST_Request $r ) {
				$liste = schiesser_styles_nettoyer( $r->get_param( 'styles' ) );
				update_option( SCHIESSER_OPTION_STYLES, $liste, true );
				return array(
					'styles' => $liste,
					'css'    => schiesser_styles_css( $liste ),
				);
			},
		),
	) );
} );

add_action( 'enqueue_block_editor_assets', function () {
	wp_localize_script( 'schiesser-typographie', 'SCHIESSER_STYLES', array(
		'liste'  => schiesser_styles_texte(),
		'gerer'  => current_user_can( 'edit_theme_options' ) ? 'oui' : 'non',
	) );
	wp_enqueue_script(
		'schiesser-mise-en-page',
		SCHIESSER_URI . '/assets/js/mise-en-page.js',
		array( 'wp-hooks', 'wp-compose', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-blocks', 'wp-data', 'wp-api-fetch', 'wp-notices', 'wp-plugins' ),
		SCHIESSER_VERSION,
		true
	);
	wp_localize_script( 'schiesser-mise-en-page', 'SCHIESSER_MEP', array(
		'sections' => schiesser_blocs_espacement(),
		'boutons'  => schiesser_blocs_boutons(),
	) );
}, 20 );

/* ------------------------------------------------------------------ */
/* Espacements des sections et réglages des boutons                    */
/* ------------------------------------------------------------------ */

/** Blocs dont on règle l'espace au-dessus et en dessous (les sections, pas leurs éléments ni les bandeaux). */
function schiesser_blocs_espacement() {
	static $liste = null;
	if ( null !== $liste ) {
		return $liste;
	}
	$liste = array( 'schiesser/section', 'schiesser/produits' );
	foreach ( schiesser_mq_blocs() as $nom => $b ) {
		if ( ! $b[3] && ! in_array( $nom, array( 'bandeau-live', 'vitrine-jour', 'chiffres', 'separateur' ), true ) ) {
			$liste[] = 'schiesser/' . $nom;
		}
	}
	$liste[] = 'schiesser/avis';
	return $liste;
}

/** Blocs qui contiennent des boutons. */
function schiesser_blocs_boutons() {
	return array( 'schiesser/hero', 'schiesser/section', 'schiesser/appel', 'schiesser/adresse', 'schiesser/venir', 'schiesser/plan-horaires', 'schiesser/cartes', 'schiesser/trajets', 'schiesser/formulaire', 'schiesser/avis' );
}

/** Attributs ajoutés aux blocs concernés. */
add_filter( 'register_block_type_args', function ( $args, $nom ) {
	if ( 0 !== strpos( $nom, 'schiesser/' ) ) {
		return $args;
	}
	$ajout = array();
	if ( in_array( $nom, schiesser_blocs_espacement(), true ) ) {
		$ajout['espHaut'] = array( 'type' => 'integer' );
		$ajout['espBas']  = array( 'type' => 'integer' );
	}
	if ( in_array( $nom, schiesser_blocs_boutons(), true ) ) {
		$ajout['btnFond']  = array( 'type' => 'string', 'default' => '' );
		$ajout['btnTexte'] = array( 'type' => 'string', 'default' => '' );
		$ajout['btnTaille'] = array( 'type' => 'string', 'default' => '' );
		$ajout['btnRayon'] = array( 'type' => 'integer' );
	}
	if ( $ajout ) {
		$args['attributes'] = array_merge( isset( $args['attributes'] ) ? (array) $args['attributes'] : array(), $ajout );
	}
	return $args;
}, 10, 2 );

/** Espacement en pixels → valeur fluide : pleine taille sur grand écran, un peu plus de la moitié sur téléphone. */
function schiesser_espacement_css( $px ) {
	$px = max( 0, min( 320, (int) $px ) );
	if ( 0 === $px ) {
		return '0px';
	}
	return 'clamp(' . round( $px * 0.55 ) . 'px,' . round( $px / 14.4, 2 ) . 'vw,' . $px . 'px)';
}

/**
 * Variables et classes posées sur le bloc (site). L'éditeur pose les mêmes (mise-en-page.js).
 *
 * @return array [ classes, déclarations de style ].
 */
function schiesser_mep_style( $a ) {
	$classes = array();
	$decl    = array();
	if ( isset( $a['espHaut'] ) && is_numeric( $a['espHaut'] ) ) {
		$decl[] = '--esp-h:' . schiesser_espacement_css( $a['espHaut'] );
	}
	if ( isset( $a['espBas'] ) && is_numeric( $a['espBas'] ) ) {
		$decl[] = '--esp-b:' . schiesser_espacement_css( $a['espBas'] );
	}
	$hex = '/^#[0-9a-fA-F]{3,8}$/';
	if ( ! empty( $a['btnFond'] ) && preg_match( $hex, $a['btnFond'] ) ) {
		$classes[] = 'a-btn-fond';
		$decl[]    = '--btn-fond:' . $a['btnFond'];
	}
	if ( ! empty( $a['btnTexte'] ) && preg_match( $hex, $a['btnTexte'] ) ) {
		$classes[] = 'a-btn-texte';
		$decl[]    = '--btn-texte:' . $a['btnTexte'];
	}
	if ( ! empty( $a['btnTaille'] ) && in_array( $a['btnTaille'], array( 'petit', 'grand' ), true ) ) {
		$classes[] = 'a-btn-' . $a['btnTaille'];
	}
	if ( isset( $a['btnRayon'] ) && is_numeric( $a['btnRayon'] ) ) {
		$classes[] = 'a-btn-rayon';
		$decl[]    = '--btn-rayon:' . max( 0, min( 60, (int) $a['btnRayon'] ) ) . 'px';
	}
	return array( $classes, $decl );
}

add_filter( 'render_block', function ( $html, $bloc ) {
	$nom = $bloc['blockName'] ?? '';
	if ( '' === $html || 0 !== strpos( (string) $nom, 'schiesser/' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $html;
	}
	list( $classes, $decl ) = schiesser_mep_style( $bloc['attrs'] ?? array() );
	if ( ! $classes && ! $decl ) {
		return $html;
	}
	$p = new WP_HTML_Tag_Processor( $html );
	if ( ! $p->next_tag( array( 'class_name' => 'wp-block-' . str_replace( '/', '-', $nom ) ) ) ) {
		return $html;
	}
	foreach ( $classes as $c ) {
		$p->add_class( $c );
	}
	if ( $decl ) {
		$style = trim( (string) $p->get_attribute( 'style' ) );
		$p->set_attribute( 'style', ( $style ? rtrim( $style, ';' ) . ';' : '' ) . implode( ';', $decl ) );
	}
	return $p->get_updated_html();
}, 10, 2 );

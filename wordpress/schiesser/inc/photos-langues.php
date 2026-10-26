<?php
/**
 * Photos communes aux langues.
 *
 * Quand une photo est changée sur une page (ex. l'accueil en français), l'éditeur propose, après
 * l'enregistrement, de mettre la même photo sur les versions allemande et anglaise de la page.
 * Les textes restent ceux de chaque langue ; seule la photo (et son texte alternatif de la
 * médiathèque) est recopiée. Les photos des produits suivent déjà automatiquement.
 */

defined( 'ABSPATH' ) || exit;

/** Nom affiché d'une langue Polylang. */
function schiesser_pl_nom_langue( $slug ) {
	$noms = array( 'de' => 'Deutsch', 'fr' => 'Français', 'en' => 'English' );
	return $noms[ substr( (string) $slug, 0, 2 ) ] ?? strtoupper( (string) $slug );
}

/* Script de l'éditeur, seulement pour une page qui a des traductions. */
add_action( 'enqueue_block_editor_assets', function () {
	if ( ! schiesser_polylang() || ! function_exists( 'pll_get_post_translations' ) ) {
		return;
	}
	$post = get_post();
	if ( ! $post || ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
		return;
	}
	$trads = array();
	foreach ( (array) pll_get_post_translations( $post->ID ) as $slug => $id ) {
		if ( (int) $id === (int) $post->ID || ! current_user_can( 'edit_post', $id ) ) {
			continue;
		}
		$trads[] = array(
			'id'     => (int) $id,
			'langue' => schiesser_pl_nom_langue( $slug ),
			'lien'   => get_permalink( $id ),
		);
	}
	if ( ! $trads ) {
		return;
	}
	wp_enqueue_script( 'schiesser-photos-langues', SCHIESSER_URI . '/assets/js/photos-langues.js', array( 'wp-data', 'wp-api-fetch', 'wp-notices', 'wp-editor' ), SCHIESSER_VERSION, true );
	wp_localize_script( 'schiesser-photos-langues', 'SCHIESSER_PHOTOS', array(
		'post'   => (int) $post->ID,
		'langue' => schiesser_pl_nom_langue( (string) pll_get_post_language( $post->ID ) ),
		'trads'  => $trads,
	) );
	// Fenêtre « Dans les autres langues » (textes de l'élément sélectionné) : inc/textes-langues.php.
	wp_enqueue_script( 'schiesser-textes-langues', SCHIESSER_URI . '/assets/js/textes-langues.js', array( 'schiesser-photos-langues', 'wp-plugins', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-block-editor' ), SCHIESSER_VERSION, true );
	wp_enqueue_style( 'schiesser-textes-langues', SCHIESSER_URI . '/assets/css/textes-langues.css', array(), SCHIESSER_VERSION );
} );

/**
 * Parcourt les blocs (sans les blocs vides entre deux blocs) et appelle $f( &$bloc, $chemin ).
 */
function schiesser_pl_parcourir( &$blocs, $f, $base = array() ) {
	$i = 0;
	foreach ( $blocs as &$b ) {
		if ( empty( $b['blockName'] ) ) {
			continue;
		}
		$chemin = array_merge( $base, array( $i ) );
		$f( $b, implode( '.', $chemin ) );
		if ( ! empty( $b['innerBlocks'] ) ) {
			schiesser_pl_parcourir( $b['innerBlocks'], $f, $chemin );
		}
		++$i;
	}
	unset( $b );
}

/** Place la photo $nouveau dans un bloc (attributs <prefixe>Id, <prefixe>Url, <prefixe>Alt). */
function schiesser_pl_poser( &$b, $prefixe, $nouveau ) {
	$b['attrs'][ $prefixe . 'Id' ]  = $nouveau;
	$b['attrs'][ $prefixe . 'Url' ] = $nouveau ? (string) wp_get_attachment_image_url( $nouveau, 'large' ) : '';
	$alt                            = $nouveau ? trim( (string) get_post_meta( $nouveau, '_wp_attachment_image_alt', true ) ) : '';
	if ( '' !== $alt || ! $nouveau ) {
		$b['attrs'][ $prefixe . 'Alt' ] = $alt;
	}
}

/**
 * Recopie des photos changées sur d'autres versions d'une page.
 *
 * @param int   $source       Page modifiée.
 * @param int[] $cibles       Traductions à mettre à jour.
 * @param array $changements  Liste de [ chemin, bloc, prefixe, ancien, nouveau ].
 * @param int   $une          Nouvelle image mise en avant (-1 : inchangée).
 * @return array id => nombre de photos remplacées.
 */
function schiesser_pl_appliquer( $source, $cibles, $changements, $une = -1 ) {
	$permises = array_map( 'intval', (array) pll_get_post_translations( $source ) );
	$bilan    = array();
	foreach ( array_map( 'intval', (array) $cibles ) as $cible ) {
		if ( $cible === (int) $source || ! in_array( $cible, $permises, true ) || ! current_user_can( 'edit_post', $cible ) ) {
			continue;
		}
		$blocs = parse_blocks( (string) get_post_field( 'post_content', $cible ) );
		$n     = 0;
		foreach ( $changements as $c ) {
			$prefixe = preg_replace( '/[^A-Za-z0-9_]/', '', (string) ( $c['prefixe'] ?? '' ) );
			$ancien  = (int) ( $c['ancien'] ?? 0 );
			$nouveau = (int) ( $c['nouveau'] ?? 0 );
			if ( '' === $prefixe || $ancien === $nouveau || ( $nouveau && 'attachment' !== get_post_type( $nouveau ) ) ) {
				continue;
			}
			$trouve = false;
			// 1. Même emplacement dans la page, même bloc, même ancienne photo.
			schiesser_pl_parcourir( $blocs, function ( &$b, $chemin ) use ( $c, $prefixe, $ancien, $nouveau, &$trouve ) {
				if ( $trouve || $chemin !== (string) ( $c['chemin'] ?? '' ) || $b['blockName'] !== (string) ( $c['bloc'] ?? '' ) ) {
					return;
				}
				if ( (int) ( $b['attrs'][ $prefixe . 'Id' ] ?? 0 ) === $ancien ) {
					schiesser_pl_poser( $b, $prefixe, $nouveau );
					$trouve = true;
				}
			} );
			// 2. Sinon, la même ancienne photo ailleurs dans la page (sections déplacées).
			if ( ! $trouve && $ancien ) {
				schiesser_pl_parcourir( $blocs, function ( &$b ) use ( $prefixe, $ancien, $nouveau, &$trouve ) {
					if ( (int) ( $b['attrs'][ $prefixe . 'Id' ] ?? 0 ) === $ancien ) {
						schiesser_pl_poser( $b, $prefixe, $nouveau );
						$trouve = true;
					}
				} );
			}
			$n += $trouve ? 1 : 0;
		}
		if ( $n ) {
			wp_update_post( array( 'ID' => $cible, 'post_content' => wp_slash( serialize_blocks( $blocs ) ) ) );
		}
		if ( $une >= 0 ) {
			if ( $une && 'attachment' === get_post_type( $une ) ) {
				set_post_thumbnail( $cible, $une );
			} elseif ( ! $une ) {
				delete_post_thumbnail( $cible );
			}
			++$n;
		}
		$bilan[ $cible ] = $n;
	}
	return $bilan;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'schiesser/v1', '/photos-langues', array(
		'methods'             => 'POST',
		'permission_callback' => function ( $r ) {
			return schiesser_polylang() && current_user_can( 'edit_post', (int) $r['source'] );
		},
		'callback'            => function ( $r ) {
			$bilan = schiesser_pl_appliquer( (int) $r['source'], (array) $r['cibles'], (array) $r['changements'], isset( $r['une'] ) ? (int) $r['une'] : -1 );
			$out   = array();
			foreach ( $bilan as $id => $n ) {
				$out[] = array(
					'id'     => $id,
					'langue' => schiesser_pl_nom_langue( (string) pll_get_post_language( $id ) ),
					'photos' => $n,
					'lien'   => get_permalink( $id ),
				);
			}
			return rest_ensure_response( $out );
		},
	) );
} );

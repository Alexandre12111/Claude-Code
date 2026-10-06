<?php
/**
 * Textes dans les autres langues, depuis l'éditeur.
 *
 * Quand on sélectionne une section ou un élément d'une page (ex. le titre de l'accueil en
 * français), une petite fenêtre montre le même texte sur les versions allemande et anglaise de
 * la page : on le lit et on le corrige sur place, sans ouvrir l'autre page.
 */

defined( 'ABSPATH' ) || exit;

/** Réglages qui ne sont pas des textes (adresses, couleurs, options…). */
function schiesser_tl_cle_texte( $cle, $valeur ) {
	if ( ! is_string( $valeur ) || preg_match( '/(Url|Lien|Id|Auto|Style)$/', $cle ) ) {
		return false;
	}
	if ( in_array( $cle, array( 'hauteur', 'filtre', 'modele', 'fond', 'fondCarte', 'ancre', 'affichage', 'icone', 'auto', 'produits', 'niveau', 'align', 'className', 'accent', 'source', 'largeur', 'styleAccroche', 'numero', 'nombre', 'annee', 'afficherAu', 'afficherDu', 'schema', 'lien' ), true ) ) {
		return false;
	}
	return ! preg_match( '#^(https?://|/|\#[0-9a-f]{3,8}$|mailto:|tel:)#i', trim( $valeur ) );
}

/** Bloc d'une page à un emplacement donné (« 0.3.1 ») : référence dans le tableau des blocs. */
function &schiesser_tl_bloc( &$blocs, $chemin ) {
	$rien = null;
	$cur  = &$blocs;
	$bloc = null;
	foreach ( explode( '.', (string) $chemin ) as $n ) {
		$i     = 0;
		$trouve = false;
		foreach ( $cur as $k => &$b ) {
			if ( empty( $b['blockName'] ) ) {
				continue;
			}
			if ( $i === (int) $n ) {
				$bloc   = &$b;
				$trouve = true;
				break;
			}
			++$i;
		}
		unset( $b );
		if ( ! $trouve ) {
			return $rien;
		}
		$cur = &$bloc['innerBlocks'];
	}
	return $bloc;
}

/** Contrôle commun : la page source et la traduction visée sont liées et modifiables. */
function schiesser_tl_cibles( $source ) {
	if ( ! schiesser_polylang() || ! function_exists( 'pll_get_post_translations' ) || ! current_user_can( 'edit_post', $source ) ) {
		return array();
	}
	$out = array();
	foreach ( (array) pll_get_post_translations( $source ) as $slug => $id ) {
		if ( (int) $id !== (int) $source && current_user_can( 'edit_post', $id ) ) {
			$out[ (int) $id ] = schiesser_pl_nom_langue( $slug );
		}
	}
	return $out;
}

add_action( 'rest_api_init', function () {
	$permission = function ( $r ) {
		return (bool) schiesser_tl_cibles( (int) $r['source'] );
	};

	/* Lire : textes du même bloc sur chaque traduction. */
	register_rest_route( 'schiesser/v1', '/textes-langues', array(
		'methods'             => 'GET',
		'permission_callback' => $permission,
		'callback'            => function ( $r ) {
			$out = array();
			foreach ( schiesser_tl_cibles( (int) $r['source'] ) as $id => $langue ) {
				$blocs  = parse_blocks( (string) get_post_field( 'post_content', $id ) );
				$b      = &schiesser_tl_bloc( $blocs, (string) $r['chemin'] );
				$textes = null;
				if ( $b && $b['blockName'] === (string) $r['bloc'] ) {
					$textes = array();
					foreach ( (array) $b['attrs'] as $cle => $v ) {
						if ( schiesser_tl_cle_texte( $cle, $v ) ) {
							$textes[ $cle ] = $v;
						}
					}
				}
				unset( $b );
				$out[] = array(
					'id'     => $id,
					'langue' => $langue,
					'lien'   => get_edit_post_link( $id, 'raw' ),
					'textes' => $textes, // null : section introuvable sur cette version
				);
			}
			return rest_ensure_response( $out );
		},
	) );

	/* Écrire : un texte d'un bloc sur une traduction. */
	register_rest_route( 'schiesser/v1', '/textes-langues', array(
		'methods'             => 'POST',
		'permission_callback' => $permission,
		'callback'            => function ( $r ) {
			$cible = (int) $r['cible'];
			$cle   = preg_replace( '/[^A-Za-z0-9_]/', '', (string) $r['cle'] );
			if ( ! isset( schiesser_tl_cibles( (int) $r['source'] )[ $cible ] ) || '' === $cle ) {
				return new WP_Error( 'schiesser_tl', 'Version introuvable.', array( 'status' => 403 ) );
			}
			$valeur = wp_kses_post( (string) $r['valeur'] );
			if ( ! schiesser_tl_cle_texte( $cle, $valeur ) && '' !== $valeur ) {
				return new WP_Error( 'schiesser_tl', 'Ce réglage n’est pas un texte.', array( 'status' => 400 ) );
			}
			$blocs = parse_blocks( (string) get_post_field( 'post_content', $cible ) );
			$b     = &schiesser_tl_bloc( $blocs, (string) $r['chemin'] );
			if ( ! $b || $b['blockName'] !== (string) $r['bloc'] ) {
				return new WP_Error( 'schiesser_tl', 'Section introuvable sur cette version.', array( 'status' => 404 ) );
			}
			$b['attrs'][ $cle ] = $valeur;
			unset( $b );
			$ok = wp_update_post( array( 'ID' => $cible, 'post_content' => wp_slash( serialize_blocks( $blocs ) ) ), true );
			if ( is_wp_error( $ok ) ) {
				return $ok;
			}
			return rest_ensure_response( array( 'ok' => true, 'valeur' => $valeur ) );
		},
	) );
} );

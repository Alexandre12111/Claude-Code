<?php
/**
 * Import des données livrées avec le thème DualTech (contenu/donnees.json) : partenaires, intervenants, séances.
 *
 * Appelé par l'installation du thème, par lots (les images sont copiées dans la médiathèque).
 * Chaque élément porte une clé (_dt_cle) : relancer l'import ne crée pas de doublon et ne retouche pas
 * un élément déjà importé, pour garder les modifications faites dans l'administration.
 */

defined( 'ABSPATH' ) || exit;

/** Élément déjà importé (par sa clé). */
function dts_par_cle( $type, $cle ) {
	$ids = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_dt_cle', 'meta_value' => $cle ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	return $ids ? (int) $ids[0] : 0;
}

/** Image d'un dossier local copiée dans la médiathèque (une seule fois). */
function dts_image_locale( $chemin, $alt ) {
	$deja = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_dt_source', 'meta_value' => basename( dirname( $chemin ) ) . '/' . basename( $chemin ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $deja ) {
		return (int) $deja[0];
	}
	if ( ! is_readable( $chemin ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$copie = wp_tempnam( basename( $chemin ) );
	copy( $chemin, $copie );
	$id = media_handle_sideload( array( 'name' => basename( $chemin ), 'tmp_name' => $copie ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $copie ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_dt_source', basename( dirname( $chemin ) ) . '/' . basename( $chemin ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/**
 * Importe un lot de données.
 *
 * @param array  $donnees Contenu de donnees.json.
 * @param string $images  Dossier des images (assets du thème).
 * @param string $type    partenaires, intervenants ou seances.
 * @param int    $debut   Premier élément du lot.
 * @param int    $taille  Taille du lot.
 * @return array crees, existants, total, suivant (ou null quand c'est fini)
 */
function dts_importer_lot( $donnees, $images, $type, $debut = 0, $taille = 10 ) {
	@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	dts_rubriques_par_defaut();
	$liste  = array_values( (array) ( $donnees[ $type ] ?? array() ) );
	$lot    = array_slice( $liste, $debut, $taille );
	$crees  = 0;
	$deja   = 0;
	$images = trailingslashit( $images );
	foreach ( $lot as $n => $e ) {
		$ordre = $debut + $n;
		if ( 'partenaires' === $type ) {
			if ( dts_par_cle( DTS_PARTENAIRE, $e['cle'] ) ) {
				$deja++;
				continue;
			}
			$id = wp_insert_post( array( 'post_type' => DTS_PARTENAIRE, 'post_status' => 'publish', 'post_title' => $e['nom'], 'menu_order' => $e['ordre'] ?? $ordre ) );
			update_post_meta( $id, '_dt_cle', $e['cle'] );
			update_post_meta( $id, '_dt_description', $e['description'] ?? '' );
			update_post_meta( $id, '_dt_lien', $e['lien'] ?? '' );
			wp_set_object_terms( $id, (array) $e['rubriques'], DTS_RUBRIQUE );
			if ( ! empty( $e['logo'] ) ) {
				$img = dts_image_locale( $images . $e['logo'], 'Logo ' . $e['nom'] );
				if ( $img ) {
					set_post_thumbnail( $id, $img );
				}
			}
		} elseif ( 'intervenants' === $type ) {
			if ( dts_par_cle( DTS_INTERVENANT, $e['cle'] ) ) {
				$deja++;
				continue;
			}
			$id = wp_insert_post( array( 'post_type' => DTS_INTERVENANT, 'post_status' => 'publish', 'post_title' => $e['nom'], 'menu_order' => $ordre ) );
			update_post_meta( $id, '_dt_cle', $e['cle'] );
			update_post_meta( $id, '_dt_fonction', $e['fonction'] );
			update_post_meta( $id, '_dt_organisation', $e['orga'] );
			update_post_meta( $id, '_dt_univers', $e['univers'] );
			$img = dts_image_locale( $images . $e['photo'], $e['nom'] );
			if ( $img ) {
				set_post_thumbnail( $id, $img );
			}
		} elseif ( 'seances' === $type ) {
			if ( dts_par_cle( DTS_SEANCE, $e['cle'] ) ) {
				$deja++;
				continue;
			}
			$id = wp_insert_post( array( 'post_type' => DTS_SEANCE, 'post_status' => 'publish', 'post_title' => $e['titre'], 'menu_order' => $ordre ) );
			update_post_meta( $id, '_dt_cle', $e['cle'] );
			foreach ( array( 'debut', 'fin', 'type', 'note', 'resume' ) as $c ) {
				update_post_meta( $id, '_dt_' . $c, $e[ $c ] ?? '' );
			}
			update_post_meta( $id, '_dt_temps_fort', empty( $e['temps_fort'] ) ? '' : '1' );
			foreach ( array( 'intervenants', 'moderateurs' ) as $c ) {
				$ids = array();
				foreach ( (array) $e[ $c ] as $cle ) {
					$i = dts_par_cle( DTS_INTERVENANT, $cle );
					if ( $i ) {
						$ids[] = $i;
					}
				}
				update_post_meta( $id, '_dt_' . $c, $ids );
			}
		}
		$crees++;
	}
	$suivant = $debut + $taille < count( $liste ) ? $debut + $taille : null;
	return array( 'crees' => $crees, 'existants' => $deja, 'total' => count( $liste ), 'suivant' => $suivant );
}

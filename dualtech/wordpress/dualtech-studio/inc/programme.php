<?php
/**
 * Programme de la journée : une séance = un horaire, un format, des intervenants et une modération.
 *
 * Les séances sont rangées automatiquement par heure de début et réparties en Matinée (avant 12h00),
 * Mi-journée (avant 14h00) et Après-midi. Une séance cochée « temps fort » apparaît aussi dans la
 * frise horizontale de la page L'événement, avec son texte court.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_' . DTS_SEANCE, function () {
	add_meta_box( 'dts-seance', 'Horaire et format', 'dts_seance_boite', DTS_SEANCE, 'normal', 'high' );
	add_meta_box( 'dts-seance-intervenants', 'Intervenants et modération', 'dts_seance_intervenants_boite', DTS_SEANCE, 'normal', 'high' );
} );

function dts_seance_boite( $post ) {
	wp_nonce_field( 'dts_seance', 'dts_seance' );
	$m = function ( $cle ) use ( $post ) {
		return (string) get_post_meta( $post->ID, $cle, true );
	};
	echo '<div class="dts-ligne">';
	dts_champ( 'dts_debut', 'Début', $m( '_dt_debut' ), 'time' );
	dts_champ( 'dts_fin', 'Fin', $m( '_dt_fin' ), 'time' );
	dts_champ( 'dts_type', 'Format', $m( '_dt_type' ) ?: 'keynote', 'select', '', dts_types_seance() );
	echo '</div>';
	dts_champ( 'dts_note', 'Précision (facultatif)', $m( '_dt_note' ), 'text', 'Ex. Petit-déjeuner networking, En parallèle, visite du Catalyseur Santé' );
	dts_champ( 'dts_temps_fort', 'Temps fort', $m( '_dt_temps_fort' ), 'checkbox', 'Afficher aussi cette séance dans « Les temps forts de la journée » (page L’événement)' );
	dts_champ( 'dts_resume', 'Texte court des temps forts (facultatif)', $m( '_dt_resume' ), 'text', 'Sinon, les noms des intervenants sont affichés.' );
}

function dts_seance_intervenants_boite( $post ) {
	$tous = function_exists( 'dualtech_intervenants' ) ? dualtech_intervenants() : array();
	foreach ( array( '_dt_intervenants' => 'Intervenants', '_dt_moderateurs' => 'Modération' ) as $cle => $libelle ) {
		$choisis = array_filter( array_map( 'intval', (array) get_post_meta( $post->ID, $cle, true ) ) );
		$nom     = 'dts' . $cle;
		echo '<div class="dts-champ dts-choix" data-nom="' . esc_attr( $nom ) . '[]"><span class="dts-libelle">' . esc_html( $libelle ) . '</span><ol class="dts-choix__liste">';
		foreach ( $choisis as $id ) {
			if ( isset( $tous[ $id ] ) ) {
				dts_choix_ligne( $nom, $tous[ $id ] );
			}
		}
		echo '</ol><select class="dts-choix__ajout"><option value="">+ Ajouter…</option>';
		foreach ( $tous as $i ) {
			echo '<option value="' . (int) $i['id'] . '" data-libelle="' . esc_attr( $i['nom'] . ' · ' . $i['orga'] ) . '">' . esc_html( $i['nom'] . ' · ' . $i['orga'] ) . '</option>';
		}
		echo '</select><span class="dts-aide">L’ordre de la liste est celui de l’affichage (flèches pour le changer).</span></div>';
	}
	echo '<p class="dts-aide">Un intervenant absent de la liste se crée d’abord dans DualTech → Intervenants.</p>';
}

function dts_choix_ligne( $nom, $i ) {
	echo '<li><input type="hidden" name="' . esc_attr( $nom ) . '[]" value="' . (int) $i['id'] . '"><span>' . esc_html( $i['nom'] . ' · ' . $i['orga'] ) . '</span>'
		. '<button type="button" class="button-link" data-action="monter" aria-label="Monter">↑</button><button type="button" class="button-link" data-action="descendre" aria-label="Descendre">↓</button>'
		. '<button type="button" class="button-link dts-retirer" data-action="retirer" aria-label="Retirer">✕</button></li>';
}

add_action( 'save_post_' . DTS_SEANCE, function ( $post_id ) {
	if ( ! dts_peut_enregistrer( $post_id, 'dts_seance' ) ) {
		return;
	}
	$heure = function ( $v ) {
		$v = sanitize_text_field( wp_unslash( $v ?? '' ) );
		return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $v ) ? $v : '';
	};
	update_post_meta( $post_id, '_dt_debut', $heure( $_POST['dts_debut'] ?? '' ) );
	update_post_meta( $post_id, '_dt_fin', $heure( $_POST['dts_fin'] ?? '' ) );
	$type = sanitize_key( wp_unslash( $_POST['dts_type'] ?? '' ) );
	update_post_meta( $post_id, '_dt_type', isset( dts_types_seance()[ $type ] ) ? $type : 'keynote' );
	update_post_meta( $post_id, '_dt_note', sanitize_text_field( wp_unslash( $_POST['dts_note'] ?? '' ) ) );
	update_post_meta( $post_id, '_dt_temps_fort', empty( $_POST['dts_temps_fort'] ) ? '' : '1' );
	update_post_meta( $post_id, '_dt_resume', sanitize_text_field( wp_unslash( $_POST['dts_resume'] ?? '' ) ) );
	foreach ( array( '_dt_intervenants', '_dt_moderateurs' ) as $cle ) {
		$ids = array_values( array_unique( array_filter( array_map( 'intval', (array) wp_unslash( $_POST[ 'dts' . $cle ] ?? array() ) ) ) ) );
		update_post_meta( $post_id, $cle, $ids );
	}
} );

add_filter( 'manage_' . DTS_SEANCE . '_posts_columns', function ( $cols ) {
	return array( 'cb' => $cols['cb'], 'dts_horaire' => 'Horaire', 'title' => 'Séance', 'dts_type' => 'Format', 'dts_qui' => 'Intervenants', 'dts_tf' => 'Temps fort' );
} );
add_action( 'manage_' . DTS_SEANCE . '_posts_custom_column', function ( $col, $id ) {
	$tous = dualtech_intervenants();
	switch ( $col ) {
		case 'dts_horaire':
			echo '<strong>' . esc_html( get_post_meta( $id, '_dt_debut', true ) ) . '</strong> · ' . esc_html( get_post_meta( $id, '_dt_fin', true ) );
			break;
		case 'dts_type':
			$t = (string) get_post_meta( $id, '_dt_type', true );
			echo '<span class="dts-badge dts-badge--' . esc_attr( $t ) . '">' . esc_html( dts_types_seance()[ $t ] ?? $t ) . '</span>';
			break;
		case 'dts_qui':
			$noms = array();
			foreach ( (array) get_post_meta( $id, '_dt_intervenants', true ) as $i ) {
				if ( isset( $tous[ (int) $i ] ) ) {
					$noms[] = $tous[ (int) $i ]['nom'];
				}
			}
			echo esc_html( implode( ', ', $noms ) ?: '·' );
			break;
		case 'dts_tf':
			echo get_post_meta( $id, '_dt_temps_fort', true ) ? '★' : '';
			break;
	}
}, 10, 2 );
dts_liste_ordonnee( DTS_SEANCE, '_dt_debut' );

/** « 09:30 » → « 09h30 » (affichage du site). */
function dualtech_heure( $h ) {
	return str_replace( ':', 'h', (string) $h );
}

/**
 * Séances publiées, rangées par heure de début.
 *
 * @return array[] id, titre, debut (« 09h30 »), fin, type, note, intervenants (ids), moderateurs (ids), temps_fort, resume
 */
function dualtech_seances() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	$posts = get_posts( array( 'post_type' => DTS_SEANCE, 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => '_dt_debut', 'orderby' => array( 'meta_value' => 'ASC', 'menu_order' => 'ASC' ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	foreach ( $posts as $p ) {
		$cache[] = array(
			'id'           => $p->ID,
			'titre'        => $p->post_title,
			'debut'        => dualtech_heure( get_post_meta( $p->ID, '_dt_debut', true ) ),
			'fin'          => dualtech_heure( get_post_meta( $p->ID, '_dt_fin', true ) ),
			'type'         => (string) get_post_meta( $p->ID, '_dt_type', true ) ?: 'keynote',
			'note'         => (string) get_post_meta( $p->ID, '_dt_note', true ),
			'intervenants' => array_map( 'intval', (array) get_post_meta( $p->ID, '_dt_intervenants', true ) ),
			'moderateurs'  => array_map( 'intval', (array) get_post_meta( $p->ID, '_dt_moderateurs', true ) ),
			'temps_fort'   => (bool) get_post_meta( $p->ID, '_dt_temps_fort', true ),
			'resume'       => (string) get_post_meta( $p->ID, '_dt_resume', true ),
		);
	}
	return $cache;
}

/** Séances d'un intervenant (intervention ou modération). */
function dualtech_seances_de( $intervenant_id ) {
	$sortie = array();
	foreach ( dualtech_seances() as $s ) {
		if ( in_array( (int) $intervenant_id, $s['intervenants'], true ) ) {
			$sortie[] = $s + array( 'role' => 'intervention' );
		} elseif ( in_array( (int) $intervenant_id, $s['moderateurs'], true ) ) {
			$sortie[] = $s + array( 'role' => 'moderation' );
		}
	}
	return $sortie;
}

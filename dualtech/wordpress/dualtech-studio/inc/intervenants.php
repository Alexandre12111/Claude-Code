<?php
/**
 * Intervenants : photo (image mise en avant), fonction, organisation et univers (filtres de la page Intervenants).
 *
 * L'ordre d'affichage se règle avec le champ « Ordre » : les 12 premiers apparaissent sur l'accueil.
 * Les horaires de passage affichés dans la fiche d'un intervenant viennent du Programme.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_' . DTS_INTERVENANT, function () {
	add_meta_box( 'dts-intervenant', 'Fonction et organisation', 'dts_intervenant_boite', DTS_INTERVENANT, 'normal', 'high' );
} );

function dts_intervenant_boite( $post ) {
	wp_nonce_field( 'dts_intervenant', 'dts_intervenant' );
	echo '<p class="dts-intro">La <strong>photo</strong> se choisit dans la colonne de droite (« Photo ») : de préférence un portrait carré d’au moins 480 pixels. Elle s’affiche en noir et blanc et passe en couleur au survol.</p>';
	dts_champ( 'dts_fonction', 'Fonction', (string) get_post_meta( $post->ID, '_dt_fonction', true ), 'text', 'Ex. Président-directeur général' );
	dts_champ( 'dts_organisation', 'Organisation', (string) get_post_meta( $post->ID, '_dt_organisation', true ), 'text', 'Ex. Office National d’Études et de Recherches Aérospatiales (ONERA)' );
	dts_champ( 'dts_univers', 'Catégorie (filtre de la page Intervenants)', (string) get_post_meta( $post->ID, '_dt_univers', true ) ?: 'industrie', 'select', '', dts_univers() );
	$seances = dualtech_seances_de( $post->ID );
	echo '<div class="dts-champ"><span class="dts-libelle">Passages sur scène</span>';
	if ( $seances ) {
		echo '<ul class="dts-passages">';
		foreach ( $seances as $s ) {
			echo '<li><b>' . esc_html( $s['debut'] ) . '</b> ' . esc_html( $s['titre'] ) . ( 'moderation' === $s['role'] ? ' <em>(modération)</em>' : '' ) . ' · <a href="' . esc_url( get_edit_post_link( $s['id'] ) ) . '">modifier</a></li>';
		}
		echo '</ul>';
	} else {
		echo '<span class="dts-aide">Aucune séance pour le moment. Les passages s’ajoutent depuis DualTech → Programme.</span>';
	}
	echo '</div>';
}

add_action( 'save_post_' . DTS_INTERVENANT, function ( $post_id ) {
	if ( ! dts_peut_enregistrer( $post_id, 'dts_intervenant' ) ) {
		return;
	}
	update_post_meta( $post_id, '_dt_fonction', sanitize_text_field( wp_unslash( $_POST['dts_fonction'] ?? '' ) ) );
	update_post_meta( $post_id, '_dt_organisation', sanitize_text_field( wp_unslash( $_POST['dts_organisation'] ?? '' ) ) );
	$univers = sanitize_key( wp_unslash( $_POST['dts_univers'] ?? '' ) );
	update_post_meta( $post_id, '_dt_univers', isset( dts_univers()[ $univers ] ) ? $univers : 'industrie' );
} );

add_filter( 'manage_' . DTS_INTERVENANT . '_posts_columns', function ( $cols ) {
	return array( 'cb' => $cols['cb'], 'dts_photo' => 'Photo', 'title' => 'Nom', 'dts_fonction' => 'Fonction et organisation', 'dts_univers' => 'Catégorie', 'dts_passages' => 'Passages', 'dts_ordre' => 'Ordre' );
} );
add_action( 'manage_' . DTS_INTERVENANT . '_posts_custom_column', function ( $col, $id ) {
	switch ( $col ) {
		case 'dts_photo':
			echo dts_miniature( $id, true ); // phpcs:ignore WordPress.Security.EscapeOutput
			break;
		case 'dts_fonction':
			echo esc_html( (string) get_post_meta( $id, '_dt_fonction', true ) ) . '<br><strong>' . esc_html( (string) get_post_meta( $id, '_dt_organisation', true ) ) . '</strong>';
			break;
		case 'dts_univers':
			echo esc_html( dts_univers()[ get_post_meta( $id, '_dt_univers', true ) ] ?? '' );
			break;
		case 'dts_passages':
			echo esc_html( implode( ', ', wp_list_pluck( dualtech_seances_de( $id ), 'debut' ) ) ?: '·' );
			break;
		case 'dts_ordre':
			echo (int) get_post_field( 'menu_order', $id );
			break;
	}
}, 10, 2 );
add_filter( 'manage_edit-' . DTS_INTERVENANT . '_sortable_columns', function ( $cols ) {
	$cols['dts_ordre'] = 'menu_order';
	return $cols;
} );
dts_liste_ordonnee( DTS_INTERVENANT );

/**
 * Intervenants visibles, dans l'ordre d'affichage.
 *
 * @return array[] id, nom, fonction, orga, univers, photo
 */
function dualtech_intervenants() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	foreach ( get_posts( array( 'post_type' => DTS_INTERVENANT, 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) ) as $p ) {
		$cache[ $p->ID ] = array(
			'id'       => $p->ID,
			'nom'      => $p->post_title,
			'fonction' => (string) get_post_meta( $p->ID, '_dt_fonction', true ),
			'orga'     => (string) get_post_meta( $p->ID, '_dt_organisation', true ),
			'univers'  => (string) get_post_meta( $p->ID, '_dt_univers', true ) ?: 'industrie',
			'photo'    => dts_image( get_post_thumbnail_id( $p ), 'medium_large' ) ?: dts_image( get_post_thumbnail_id( $p ), 'full' ),
		);
	}
	return $cache;
}

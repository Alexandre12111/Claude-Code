<?php
/**
 * Partenaires : logo (image mise en avant), description affichée au clic sur le logo, lien et rubriques.
 *
 * Les rubriques décident où le logo apparaît :
 * - Institutions publiques, Financement et marchés, Écosystèmes…, Communication et médias : page Partenaires
 *   (une rangée par rubrique) et défilé de logos de la page L'événement ;
 * - Partenaires du Prix de l'innovation duale : page Prix (et défilé de L'événement) ;
 * - Partenaires de la première édition (2025) : page Rétrospective, avec la description.
 * L'ordre d'affichage se règle avec le champ « Ordre » (le plus petit en premier).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_' . DTS_PARTENAIRE, function () {
	add_meta_box( 'dts-partenaire', 'Présentation du partenaire', 'dts_partenaire_boite', DTS_PARTENAIRE, 'normal', 'high' );
} );

function dts_partenaire_boite( $post ) {
	wp_nonce_field( 'dts_partenaire', 'dts_partenaire' );
	echo '<p class="dts-intro">Le <strong>logo</strong> se choisit dans la colonne de droite (« Logo »). Préférez un fichier PNG ou WebP au fond transparent. Le nom ci-dessus est celui affiché dans la fenêtre qui s’ouvre au clic.</p>';
	dts_champ( 'dts_description', 'Description (affichée au clic sur le logo)', (string) get_post_meta( $post->ID, '_dt_description', true ), 'textarea', 'Deux ou trois phrases. Laissée vide, la fenêtre indique simplement « Partenaire de DualTech 2026 ».' );
	dts_champ( 'dts_lien', 'Site internet (facultatif)', (string) get_post_meta( $post->ID, '_dt_lien', true ), 'url', 'Ajoute un lien « Visiter le site » dans la fenêtre du partenaire.' );
}

add_action( 'save_post_' . DTS_PARTENAIRE, function ( $post_id ) {
	if ( ! dts_peut_enregistrer( $post_id, 'dts_partenaire' ) ) {
		return;
	}
	update_post_meta( $post_id, '_dt_description', sanitize_textarea_field( wp_unslash( $_POST['dts_description'] ?? '' ) ) );
	update_post_meta( $post_id, '_dt_lien', esc_url_raw( wp_unslash( $_POST['dts_lien'] ?? '' ) ) );
} );

/* Liste : logo, nom, rubriques, ordre */
add_filter( 'manage_' . DTS_PARTENAIRE . '_posts_columns', function ( $cols ) {
	return array( 'cb' => $cols['cb'], 'dts_logo' => 'Logo', 'title' => 'Nom', 'taxonomy-' . DTS_RUBRIQUE => 'Rubriques', 'dts_ordre' => 'Ordre', 'date' => $cols['date'] );
} );
add_action( 'manage_' . DTS_PARTENAIRE . '_posts_custom_column', function ( $col, $id ) {
	if ( 'dts_logo' === $col ) {
		$url = dts_image( get_post_thumbnail_id( $id ), 'medium' );
		echo $url ? '<img class="dts-logo-liste" src="' . esc_url( $url ) . '" alt="">' : '<span class="dts-aide">Sans logo (nom affiché)</span>';
	} elseif ( 'dts_ordre' === $col ) {
		echo (int) get_post_field( 'menu_order', $id );
	}
}, 10, 2 );
add_filter( 'manage_edit-' . DTS_PARTENAIRE . '_sortable_columns', function ( $cols ) {
	$cols['dts_ordre'] = 'menu_order';
	return $cols;
} );
dts_liste_ordonnee( DTS_PARTENAIRE );

/* Filtre par rubrique au-dessus de la liste */
add_action( 'restrict_manage_posts', function ( $type ) {
	if ( DTS_PARTENAIRE !== $type ) {
		return;
	}
	wp_dropdown_categories( array(
		'taxonomy'        => DTS_RUBRIQUE,
		'name'            => DTS_RUBRIQUE,
		'value_field'     => 'slug',
		'show_option_all' => 'Toutes les rubriques',
		'selected'        => sanitize_text_field( wp_unslash( $_GET[ DTS_RUBRIQUE ] ?? '' ) ), // phpcs:ignore WordPress.Security.NonceVerification
		'hide_empty'      => false,
	) );
} );

/** Titre de la fiche : « Nom du partenaire » au lieu de « Ajouter un titre ». */
add_filter( 'enter_title_here', function ( $texte, $post ) {
	if ( DTS_PARTENAIRE === $post->post_type ) {
		return 'Nom du partenaire';
	}
	if ( DTS_INTERVENANT === $post->post_type ) {
		return 'Prénom et nom';
	}
	if ( DTS_SEANCE === $post->post_type ) {
		return 'Titre de la séance (ex. Keynote : « … »)';
	}
	return $texte;
}, 10, 2 );

/**
 * Partenaires visibles, dans l'ordre d'affichage.
 *
 * @param string|null $rubrique Slug d'une rubrique, ou null pour tous.
 * @return array[] id, nom, logo, description, lien, rubriques (slugs)
 */
function dualtech_partenaires( $rubrique = null ) {
	$args = array(
		'post_type'      => DTS_PARTENAIRE,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	);
	if ( $rubrique ) {
		$args['tax_query'] = array( array( 'taxonomy' => DTS_RUBRIQUE, 'field' => 'slug', 'terms' => (array) $rubrique ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	$sortie = array();
	foreach ( get_posts( $args ) as $p ) {
		$sortie[] = array(
			'id'          => $p->ID,
			'nom'         => $p->post_title,
			'logo'        => dts_image( get_post_thumbnail_id( $p ), 'medium' ),
			'description' => (string) get_post_meta( $p->ID, '_dt_description', true ),
			'lien'        => (string) get_post_meta( $p->ID, '_dt_lien', true ),
			'rubriques'   => wp_get_post_terms( $p->ID, DTS_RUBRIQUE, array( 'fields' => 'slugs' ) ),
		);
	}
	return $sortie;
}

/** Rubriques (slug => nom) dans l'ordre de leur création. */
function dualtech_rubriques() {
	$termes = get_terms( array( 'taxonomy' => DTS_RUBRIQUE, 'hide_empty' => false, 'orderby' => 'term_id' ) );
	$sortie = array();
	foreach ( is_array( $termes ) ? $termes : array() as $t ) {
		$sortie[ $t->slug ] = $t->name;
	}
	return $sortie;
}

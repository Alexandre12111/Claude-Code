<?php
/**
 * Types de contenus de DualTech Studio et outils partagés par les écrans.
 */

defined( 'ABSPATH' ) || exit;

define( 'DTS_PARTENAIRE', 'dt_partenaire' );
define( 'DTS_RUBRIQUE', 'dt_rubrique' );
define( 'DTS_INTERVENANT', 'dt_intervenant' );
define( 'DTS_SEANCE', 'dt_seance' );
define( 'DTS_DEMANDE', 'dt_demande' );

/** Rubriques de partenaires livrées avec le site (slug => nom), dans l'ordre de la page Partenaires. */
function dts_rubriques_initiales() {
	return array(
		'institutions'  => 'Institutions publiques',
		'financement'   => 'Financement et marchés',
		'ecosystemes'   => 'Écosystèmes industriels et technologiques',
		'medias'        => 'Communication et médias',
		'prix'          => 'Partenaires du Prix de l’innovation duale',
		'edition-2025'  => 'Partenaires de la première édition (2025)',
	);
}

/** Univers des intervenants (filtres de la page Intervenants). */
function dts_univers() {
	return array(
		'innovation'   => 'Innovation',
		'institutions' => 'Institutions',
		'industrie'    => 'Industrie',
		'finance'      => 'Financement et marchés',
		'recherche'    => 'Recherche',
	);
}

/** Formats des séances du programme. */
function dts_types_seance() {
	return array(
		'keynote'    => 'Keynote',
		'table'      => 'Table ronde',
		'atelier'    => 'Ateliers',
		'networking' => 'Networking',
		'autres'     => 'Allocution',
		'temps-fort' => 'Temps fort',
	);
}

function dts_enregistrer_types() {
	$commun = array(
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => false, // sous-menus ajoutés à la main dans le menu DualTech (ordre maîtrisé, pas de doublon)
		'show_in_rest'       => false,
		'capability_type'    => 'page',
		'map_meta_cap'       => true,
		'exclude_from_search' => true,
	);
	register_post_type( DTS_PARTENAIRE, $commun + array(
		'labels'   => array(
			'name' => 'Partenaires', 'singular_name' => 'Partenaire', 'add_new' => 'Ajouter un partenaire', 'add_new_item' => 'Ajouter un partenaire',
			'edit_item' => 'Modifier le partenaire', 'search_items' => 'Rechercher un partenaire', 'not_found' => 'Aucun partenaire.',
			'featured_image' => 'Logo', 'set_featured_image' => 'Choisir le logo', 'remove_featured_image' => 'Retirer le logo', 'use_featured_image' => 'Utiliser comme logo',
			'all_items' => 'Partenaires',
		),
		'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
	) );
	register_taxonomy( DTS_RUBRIQUE, DTS_PARTENAIRE, array(
		'labels'            => array( 'name' => 'Rubriques', 'singular_name' => 'Rubrique', 'add_new_item' => 'Ajouter une rubrique', 'edit_item' => 'Modifier la rubrique', 'search_items' => 'Rechercher', 'not_found' => 'Aucune rubrique.' ),
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => false,
		'show_admin_column' => true,
		'hierarchical'      => true, // cases à cocher dans la fiche du partenaire
		'rewrite'           => false,
	) );
	register_post_type( DTS_INTERVENANT, $commun + array(
		'labels'   => array(
			'name' => 'Intervenants', 'singular_name' => 'Intervenant', 'add_new' => 'Ajouter un intervenant', 'add_new_item' => 'Ajouter un intervenant',
			'edit_item' => 'Modifier l’intervenant', 'search_items' => 'Rechercher un intervenant', 'not_found' => 'Aucun intervenant.',
			'featured_image' => 'Photo', 'set_featured_image' => 'Choisir la photo', 'remove_featured_image' => 'Retirer la photo', 'use_featured_image' => 'Utiliser comme photo',
			'all_items' => 'Intervenants',
		),
		'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
	) );
	register_post_type( DTS_SEANCE, $commun + array(
		'labels'   => array(
			'name' => 'Programme', 'singular_name' => 'Séance', 'add_new' => 'Ajouter une séance', 'add_new_item' => 'Ajouter une séance',
			'edit_item' => 'Modifier la séance', 'search_items' => 'Rechercher une séance', 'not_found' => 'Aucune séance.', 'all_items' => 'Programme',
		),
		'supports' => array( 'title' ),
	) );
	register_post_type( DTS_DEMANDE, array_merge( $commun, array(
		'labels'          => array(
			'name' => 'Demandes reçues', 'singular_name' => 'Demande', 'edit_item' => 'Demande reçue', 'search_items' => 'Rechercher dans les demandes',
			'not_found' => 'Aucune demande pour le moment.', 'not_found_in_trash' => 'Aucune demande dans la corbeille.', 'all_items' => 'Demandes reçues',
		),
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ), // une demande vient toujours d'un formulaire du site
	) ) );
}
add_action( 'init', 'dts_enregistrer_types' );

function dts_rubriques_par_defaut() {
	foreach ( dts_rubriques_initiales() as $slug => $nom ) {
		if ( ! term_exists( $slug, DTS_RUBRIQUE ) ) {
			wp_insert_term( $nom, DTS_RUBRIQUE, array( 'slug' => $slug ) );
		}
	}
}

/** En-tête commun aux écrans du studio. */
function dts_entete( $titre, $sous_titre = '' ) {
	echo '<div class="dts-entete"><p class="dts-marque">DUAL<span>T</span>ECH <em>Studio</em></p><h1>' . esc_html( $titre ) . '</h1>';
	if ( $sous_titre ) {
		echo '<p>' . esc_html( $sous_titre ) . '</p>';
	}
	echo '</div>';
}

/** Champ de formulaire d'une boîte de réglages (fiche partenaire, intervenant, séance). */
function dts_champ( $nom, $libelle, $valeur, $type = 'text', $aide = '', $options = array() ) {
	$id = 'dts-' . $nom;
	echo '<p class="dts-champ"><label for="' . esc_attr( $id ) . '">' . esc_html( $libelle ) . '</label>';
	if ( 'textarea' === $type ) {
		echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $nom ) . '" rows="4">' . esc_textarea( $valeur ) . '</textarea>';
	} elseif ( 'select' === $type ) {
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $nom ) . '">';
		foreach ( $options as $cle => $texte ) {
			echo '<option value="' . esc_attr( $cle ) . '"' . selected( $valeur, $cle, false ) . '>' . esc_html( $texte ) . '</option>';
		}
		echo '</select>';
	} elseif ( 'checkbox' === $type ) {
		echo '<span class="dts-case"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $nom ) . '" value="1"' . checked( $valeur, '1', false ) . '> ' . esc_html( $aide ) . '</span>';
		$aide = '';
	} else {
		echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $nom ) . '" value="' . esc_attr( $valeur ) . '">';
	}
	if ( $aide ) {
		echo '<span class="dts-aide">' . esc_html( $aide ) . '</span>';
	}
	echo '</p>';
}

/** Les champs d'une fiche peuvent-ils être enregistrés ? */
function dts_peut_enregistrer( $post_id, $jeton ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! isset( $_POST[ $jeton ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $jeton ] ) ), $jeton ) ) {
		return false;
	}
	return current_user_can( 'edit_post', $post_id );
}

/** Adresse d'une image de la médiathèque (taille demandée), ou ''. */
function dts_image( $id, $taille = 'medium' ) {
	return $id ? (string) wp_get_attachment_image_url( (int) $id, $taille ) : '';
}

/** Miniature carrée pour les listes de l'administration. */
function dts_miniature( $post_id, $rond = false ) {
	$url = dts_image( get_post_thumbnail_id( $post_id ), 'thumbnail' );
	return $url ? '<img class="dts-mini' . ( $rond ? ' dts-mini--rond' : '' ) . '" src="' . esc_url( $url ) . '" alt="">' : '<span class="dts-mini dts-mini--vide"></span>';
}

/** Colonne « Ordre » triable et tri par défaut des listes. */
function dts_liste_ordonnee( $type, $orderby = 'menu_order' ) {
	add_action( 'pre_get_posts', function ( $q ) use ( $type, $orderby ) {
		if ( ! is_admin() || ! $q->is_main_query() || $type !== $q->get( 'post_type' ) || $q->get( 'orderby' ) ) {
			return;
		}
		if ( 'menu_order' === $orderby ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		} else {
			$q->set( 'meta_key', $orderby );
			$q->set( 'orderby', array( 'meta_value' => 'ASC', 'title' => 'ASC' ) );
		}
	} );
}

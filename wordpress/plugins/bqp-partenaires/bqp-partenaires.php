<?php
/**
 * Plugin Name:       BQP Partenaires
 * Description:       Gestion et affichage des partenaires avec le shortcode [bqp_partenaires] : un bloc par catégorie et une fiche détaillée au clic.
 * Version:           2.3.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * License:           GPL-2.0-or-later
 */

/**
 * BQP Partenaires : gestion et affichage des partenaires.
 *
 * Un bloc par catégorie (Académiques, Institutionnels, Entreprises,
 * Associations, Médias), chacun avec sa couleur et son pictogramme, replié
 * en carte : un clic affiche ses partenaires. Une fiche détaillée s'ouvre
 * dans une fenêtre au clic sur un partenaire.
 *
 * Compatible extension classique ET Code Snippets (aucune balise de fermeture
 * PHP, aucun hook d'activation : les catégories se créent toutes seules).
 *
 * Shortcode : [bqp_partenaires]
 *
 * Version : 2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BQP_PARTENAIRES_VERSION' ) ) {
	define( 'BQP_PARTENAIRES_VERSION', '2.3.0' );
}
if ( ! defined( 'BQP_PARTENAIRES_CPT' ) ) {
	define( 'BQP_PARTENAIRES_CPT', 'bqp_partenaire' );
}
if ( ! defined( 'BQP_PARTENAIRES_TAX' ) ) {
	define( 'BQP_PARTENAIRES_TAX', 'bqp_partenaire_cat' );
}

/* -------------------------------------------------------------------------
 * 1. Palette et helpers
 * ---------------------------------------------------------------------- */

/**
 * Palette officielle du site (relevée sur les réglages Elementor).
 */
function bqp_palette() {
	return array(
		'bordeaux'      => '#74041C',
		'bordeaux_dark' => '#31020C',
		'navy'          => '#001756',
		'orange'        => '#C75A18',
		'teal'          => '#1F5561',
		'plum'          => '#5A2A4F',
		'ink'           => '#424242',
		'muted'         => '#888888',
		'line'          => '#E3E3E3',
		'soft'          => '#F5F5F5',
	);
}

/**
 * Catégories créées automatiquement, dans l'ordre des blocs :
 * slug => [nom, couleur, titre du bloc, ordre, introduction, icône].
 * Chaque catégorie a sa propre couleur pour bien distinguer les blocs.
 */
function bqp_default_terms() {
	$p = bqp_palette();

	return array(
		'academiques'     => array( 'Académiques', $p['navy'], 'Partenaires académiques', 1, 'Universités, grandes écoles et centres de recherche associés aux travaux de la Bourse.', 'academique' ),
		'institutionnels' => array( 'Institutionnels', $p['orange'], 'Partenaires institutionnels', 2, 'Les institutions et organismes publics qui accompagnent nos actions.', 'institution' ),
		'entreprises'     => array( 'Entreprises', $p['bordeaux'], 'Entreprises partenaires', 3, 'Les entreprises qui soutiennent la Bourse et ouvrent leurs portes aux lauréats.', 'entreprise' ),
		'associations'    => array( 'Associations', $p['teal'], 'Associations partenaires', 4, 'Les associations et cercles de réflexion avec lesquels nous partageons nos travaux.', 'association' ),
		'medias'          => array( 'Médias', $p['plum'], 'Médias partenaires', 5, 'Les médias qui relaient nos travaux et donnent la parole aux lauréats.', 'media' ),
	);
}

/**
 * Pictogrammes des blocs, au trait : clé => [libellé, tracé SVG].
 */
function bqp_icons() {
	return array(
		'academique'  => array( 'Toque (académique)', '<path d="M2 8l8-4 8 4-8 4z"/><path d="M5.5 9.8v3.7c0 1 2 2.5 4.5 2.5s4.5-1.5 4.5-2.5V9.8M18 8v4.5"/>' ),
		'institution' => array( 'Colonnes (institution)', '<path d="M2.5 7.5 10 3l7.5 4.5M4 8.5v7M8 8.5v7M12 8.5v7M16 8.5v7M2.5 17h15"/>' ),
		'entreprise'  => array( 'Mallette (entreprise)', '<rect x="2.5" y="6" width="15" height="10.5" rx="1.5"/><path d="M7 6V4.5A1.5 1.5 0 0 1 8.5 3h3A1.5 1.5 0 0 1 13 4.5V6M2.5 11h15"/>' ),
		'association' => array( 'Personnes (association)', '<circle cx="7" cy="7" r="2.6"/><circle cx="14" cy="7.5" r="2.1"/><path d="M2.5 16c.4-2.8 2.2-4.3 4.5-4.3s4.1 1.5 4.5 4.3M12 12c2.6-.4 4.8.9 5.5 3.7"/>' ),
		'media'       => array( 'Journal (média)', '<path d="M3 4.5h11v11.5H5a2 2 0 0 1-2-2z"/><path d="M14 8h3v6.5a1.5 1.5 0 0 1-3 0M6 8h5M6 11h5M6 14h3"/>' ),
		'etoile'      => array( 'Étoile', '<path d="M10 2.8l2.2 4.6 5 .7-3.6 3.5.9 5-4.5-2.4-4.5 2.4.9-5L2.8 8.1l5-.7z"/>' ),
	);
}

function bqp_icon_svg( $key, $size = 22 ) {
	$icons = bqp_icons();
	$path  = isset( $icons[ $key ] ) ? $icons[ $key ][1] : $icons['etoile'][1];

	return '<svg viewBox="0 0 20 20" width="' . (int) $size . '" height="' . (int) $size . '" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

/**
 * Réglages d'une catégorie (titre du bloc, ordre, icône), avec les valeurs
 * par défaut des cinq catégories d'origine.
 */
function bqp_term_setting( $term, $key ) {
	$value    = get_term_meta( $term->term_id, 'bqp_' . $key, true );
	$defaults = bqp_default_terms();
	$default  = isset( $defaults[ $term->slug ] ) ? $defaults[ $term->slug ] : null;

	if ( 'titre' === $key ) {
		return '' !== $value ? $value : ( $default ? $default[2] : $term->name );
	}
	if ( 'ordre' === $key ) {
		return '' !== $value ? (int) $value : ( $default ? (int) $default[3] : 99 );
	}
	if ( 'icone' === $key ) {
		return ( '' !== $value && array_key_exists( $value, bqp_icons() ) ) ? $value : ( $default ? $default[5] : 'etoile' );
	}

	return $value;
}

/**
 * Les catégories, dans l'ordre des blocs.
 */
function bqp_ordered_terms() {
	static $cache = null;

	if ( null !== $cache && ! is_admin() ) {
		return $cache;
	}

	$terms = get_terms( array( 'taxonomy' => BQP_PARTENAIRES_TAX, 'hide_empty' => false ) );
	$terms = is_wp_error( $terms ) ? array() : $terms;

	usort(
		$terms,
		function ( $a, $b ) {
			$diff = bqp_term_setting( $a, 'ordre' ) - bqp_term_setting( $b, 'ordre' );
			return $diff ? $diff : strcasecmp( $a->name, $b->name );
		}
	);

	$cache = $terms;

	return $terms;
}

/**
 * Convertit un hexadécimal en rgba() pour les fonds translucides.
 */
function bqp_hex_to_rgba( $hex, $alpha = 1 ) {
	$hex = ltrim( (string) $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		$hex = '74041C';
	}

	return sprintf(
		'rgba(%d,%d,%d,%s)',
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
		rtrim( rtrim( number_format( (float) $alpha, 2, '.', '' ), '0' ), '.' )
	);
}

/**
 * Couleur associée à un terme, avec repli sur le bordeaux.
 */
function bqp_term_color( $term_id ) {
	$color = get_term_meta( (int) $term_id, 'bqp_color', true );

	if ( $color && preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
		return $color;
	}

	$term     = get_term( (int) $term_id, BQP_PARTENAIRES_TAX );
	$defaults = bqp_default_terms();

	if ( $term && ! is_wp_error( $term ) && isset( $defaults[ $term->slug ] ) ) {
		return $defaults[ $term->slug ][1];
	}

	$palette = bqp_palette();

	return $palette['bordeaux'];
}

/**
 * Initiales utilisées quand aucun logo n'est renseigné.
 */
function bqp_initials( $name ) {
	$parts    = preg_split( '/[\s\-\']+/u', trim( wp_strip_all_tags( $name ) ) );
	$initials = '';

	foreach ( (array) $parts as $part ) {
		if ( '' === $part ) {
			continue;
		}

		$first     = function_exists( 'mb_substr' ) ? mb_substr( $part, 0, 1 ) : substr( $part, 0, 1 );
		$initials .= function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $first ) : strtoupper( $first );

		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $initials ) : strlen( $initials );
		if ( $length >= 2 ) {
			break;
		}
	}

	return $initials ? $initials : '?';
}

/* -------------------------------------------------------------------------
 * 2. Custom post type + taxonomie
 * ---------------------------------------------------------------------- */

add_action( 'init', 'bqp_register_content' );
function bqp_register_content() {

	register_post_type(
		BQP_PARTENAIRES_CPT,
		array(
			'labels'              => array(
				'name'               => 'Partenaires',
				'singular_name'      => 'Partenaire',
				'menu_name'          => 'Partenaires',
				'add_new'            => 'Ajouter un partenaire',
				'add_new_item'       => 'Ajouter un partenaire',
				'edit_item'          => 'Modifier le partenaire',
				'new_item'           => 'Nouveau partenaire',
				'view_item'          => 'Voir le partenaire',
				'search_items'       => 'Rechercher un partenaire',
				'not_found'          => 'Aucun partenaire pour le moment.',
				'not_found_in_trash' => 'Aucun partenaire dans la corbeille.',
				'all_items'          => 'Tous les partenaires',
				'item_published'     => 'Partenaire publié.',
				'item_updated'       => 'Partenaire mis à jour.',
			),
			// Volontairement non public : un partenaire n'a pas de page dédiée,
			// ce qui évite des pages trop légères dans l'index Google.
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-awards',
			'supports'            => array( 'title', 'page-attributes' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);

	register_taxonomy(
		BQP_PARTENAIRES_TAX,
		BQP_PARTENAIRES_CPT,
		array(
			'labels'             => array(
				'name'          => 'Catégories',
				'singular_name' => 'Catégorie',
				'menu_name'     => 'Catégories',
				'all_items'     => 'Toutes les catégories',
				'edit_item'     => 'Modifier la catégorie',
				'add_new_item'  => 'Ajouter une catégorie',
				'search_items'  => 'Rechercher une catégorie',
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'show_in_rest'       => false,
			'hierarchical'       => true,
			'rewrite'            => false,
		)
	);
}

/**
 * Création des catégories par défaut, puis passage en 2.0 : chaque
 * catégorie reçoit son titre de bloc, son ordre, son introduction et une
 * couleur bien distincte. Rien n'est écrasé : seuls les champs vides sont
 * remplis, et une catégorie supprimée volontairement n'est pas recréée.
 * Remplace register_activation_hook, inutilisable depuis Code Snippets.
 */
add_action( 'admin_init', 'bqp_maybe_seed_terms' );
function bqp_maybe_seed_terms() {
	if ( '2' === get_option( 'bqp_terms_v2' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	// Couleurs d'origine trop proches du bordeaux, remplacées si inchangées.
	$old_colors = array( 'associations' => '#31020C', 'medias' => '#424242' );
	$seeded     = get_option( 'bqp_terms_seeded' );

	foreach ( bqp_default_terms() as $slug => $data ) {
		$term = get_term_by( 'slug', $slug, BQP_PARTENAIRES_TAX );

		if ( ! $term ) {
			if ( $seeded ) {
				continue;
			}

			$created = wp_insert_term( $data[0], BQP_PARTENAIRES_TAX, array( 'slug' => $slug ) );
			if ( is_wp_error( $created ) ) {
				continue;
			}
			$term = get_term( $created['term_id'], BQP_PARTENAIRES_TAX );
		}

		$color = (string) get_term_meta( $term->term_id, 'bqp_color', true );
		if ( '' === $color || ( isset( $old_colors[ $slug ] ) && strtoupper( $color ) === $old_colors[ $slug ] ) ) {
			update_term_meta( $term->term_id, 'bqp_color', $data[1] );
		}

		foreach ( array( 'titre' => $data[2], 'ordre' => $data[3], 'icone' => $data[5] ) as $key => $value ) {
			if ( '' === (string) get_term_meta( $term->term_id, 'bqp_' . $key, true ) ) {
				update_term_meta( $term->term_id, 'bqp_' . $key, $value );
			}
		}

		if ( '' === trim( $term->description ) ) {
			wp_update_term( $term->term_id, BQP_PARTENAIRES_TAX, array( 'description' => $data[4] ) );
		}
	}

	update_option( 'bqp_terms_seeded', BQP_PARTENAIRES_VERSION, false );
	update_option( 'bqp_terms_v2', '2', false );
}

/**
 * Libellé du champ titre plus parlant.
 */
add_filter( 'enter_title_here', 'bqp_title_placeholder', 10, 2 );
function bqp_title_placeholder( $text, $post ) {
	if ( $post && BQP_PARTENAIRES_CPT === $post->post_type ) {
		return 'Nom du partenaire';
	}

	return $text;
}

/* -------------------------------------------------------------------------
 * 3. Réglages des catégories : titre du bloc, couleur, pictogramme, ordre
 * ---------------------------------------------------------------------- */

/**
 * Les champs d'une catégorie, identiques à l'ajout et à la modification.
 */
function bqp_term_fields_html( $term = null, $table = false ) {
	$palette = bqp_palette();
	$fields  = array();

	$titre   = $term ? bqp_term_setting( $term, 'titre' ) : '';
	$ordre   = $term ? bqp_term_setting( $term, 'ordre' ) : '';
	$icone   = $term ? bqp_term_setting( $term, 'icone' ) : 'etoile';
	$color   = $term ? bqp_term_color( $term->term_id ) : $palette['bordeaux'];
	$options = '';

	foreach ( bqp_icons() as $key => $icon ) {
		$options .= '<option value="' . esc_attr( $key ) . '"' . selected( $icone, $key, false ) . '>' . esc_html( $icon[0] ) . '</option>';
	}

	$fields[] = array( 'bqp_titre', 'Titre du bloc', '<input type="text" name="bqp_titre" id="bqp_titre" value="' . esc_attr( $titre ) . '" placeholder="Ex. Partenaires académiques" />', 'Affiché en tête du bloc de cette catégorie. Le nom court ci-dessus reste utilisé pour l\'étiquette des cartes et les filtres.' );
	$fields[] = array( 'bqp_color', 'Couleur du bloc', '<input type="color" name="bqp_color" id="bqp_color" value="' . esc_attr( $color ) . '" />', 'Couleur du pictogramme, du filet et de l\'étiquette des cartes. Choisissez une couleur différente pour chaque catégorie.' );
	$fields[] = array( 'bqp_icone', 'Pictogramme', '<select name="bqp_icone" id="bqp_icone">' . $options . '</select>', '' );
	$fields[] = array( 'bqp_ordre', 'Ordre des blocs', '<input type="number" name="bqp_ordre" id="bqp_ordre" value="' . esc_attr( $ordre ) . '" step="1" style="width:110px;" />', 'Plus petit en premier.' );

	$html = '';
	foreach ( $fields as $field ) {
		$html .= $table
			? '<tr class="form-field"><th scope="row"><label for="' . esc_attr( $field[0] ) . '">' . esc_html( $field[1] ) . '</label></th><td>' . $field[2] . ( $field[3] ? '<p class="description">' . esc_html( $field[3] ) . '</p>' : '' ) . '</td></tr>'
			: '<div class="form-field"><label for="' . esc_attr( $field[0] ) . '">' . esc_html( $field[1] ) . '</label>' . $field[2] . ( $field[3] ? '<p>' . esc_html( $field[3] ) . '</p>' : '' ) . '</div>';
	}

	return $html . wp_nonce_field( 'bqp_term_color', 'bqp_term_color_nonce', true, false );
}

add_action( BQP_PARTENAIRES_TAX . '_add_form_fields', 'bqp_term_color_add_field' );
function bqp_term_color_add_field() {
	echo bqp_term_fields_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( BQP_PARTENAIRES_TAX . '_edit_form_fields', 'bqp_term_color_edit_field' );
function bqp_term_color_edit_field( $term ) {
	echo bqp_term_fields_html( $term, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( 'created_' . BQP_PARTENAIRES_TAX, 'bqp_save_term_color' );
add_action( 'edited_' . BQP_PARTENAIRES_TAX, 'bqp_save_term_color' );
function bqp_save_term_color( $term_id ) {
	if ( ! isset( $_POST['bqp_term_color_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bqp_term_color_nonce'] ) ), 'bqp_term_color' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	if ( isset( $_POST['bqp_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['bqp_color'] ) );
		if ( $color ) {
			update_term_meta( $term_id, 'bqp_color', $color );
		}
	}

	if ( isset( $_POST['bqp_titre'] ) ) {
		update_term_meta( $term_id, 'bqp_titre', sanitize_text_field( wp_unslash( $_POST['bqp_titre'] ) ) );
	}

	if ( isset( $_POST['bqp_ordre'] ) && '' !== $_POST['bqp_ordre'] ) {
		update_term_meta( $term_id, 'bqp_ordre', (int) $_POST['bqp_ordre'] );
	}

	if ( isset( $_POST['bqp_icone'] ) ) {
		$icone = sanitize_key( wp_unslash( $_POST['bqp_icone'] ) );
		if ( array_key_exists( $icone, bqp_icons() ) ) {
			update_term_meta( $term_id, 'bqp_icone', $icone );
		}
	}
}

/**
 * Liste des catégories : le bloc tel qu'il apparaîtra (pictogramme, couleur,
 * titre) et son ordre.
 */
add_filter( 'manage_edit-' . BQP_PARTENAIRES_TAX . '_columns', 'bqp_term_columns' );
function bqp_term_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'name' === $key ) {
			$new['bqp_bloc']  = 'Bloc sur le site';
			$new['bqp_ordre'] = 'Ordre';
		}
	}

	return $new;
}

add_filter( 'manage_' . BQP_PARTENAIRES_TAX . '_custom_column', 'bqp_term_column_content', 10, 3 );
function bqp_term_column_content( $content, $column, $term_id ) {
	$term = get_term( $term_id, BQP_PARTENAIRES_TAX );

	if ( ! $term || is_wp_error( $term ) ) {
		return $content;
	}

	if ( 'bqp_bloc' === $column ) {
		$color = bqp_term_color( $term_id );
		return '<span class="bqp-term-chip" style="--c:' . esc_attr( $color ) . ';">' . bqp_icon_svg( bqp_term_setting( $term, 'icone' ), 16 ) . esc_html( bqp_term_setting( $term, 'titre' ) ) . '</span>';
	}

	if ( 'bqp_ordre' === $column ) {
		return (string) bqp_term_setting( $term, 'ordre' );
	}

	return $content;
}

/* -------------------------------------------------------------------------
 * 4. Fiche partenaire : logo, site, description, fiche détaillée
 * ---------------------------------------------------------------------- */

add_action( 'add_meta_boxes', 'bqp_add_meta_boxes' );
function bqp_add_meta_boxes() {
	add_meta_box(
		'bqp_partenaire_details',
		'Fiche du partenaire',
		'bqp_render_meta_box',
		BQP_PARTENAIRES_CPT,
		'normal',
		'high'
	);
}

function bqp_render_meta_box( $post ) {
	wp_nonce_field( 'bqp_save_partenaire', 'bqp_partenaire_nonce' );

	$m = function ( $key ) use ( $post ) {
		return (string) get_post_meta( $post->ID, '_bqp_' . $key, true );
	};

	$logo_id = (int) $m( 'logo_id' );
	$url     = $m( 'url' );
	$desc    = $m( 'description' );
	$logo    = $logo_id ? wp_get_attachment_image_src( $logo_id, 'medium' ) : false;
	$src     = $logo ? $logo[0] : '';
	$count   = function_exists( 'mb_strlen' ) ? mb_strlen( $desc ) : strlen( $desc );

	$html  = '<div class="bqp-admin"><div class="bqp-admin__grid">';

	/* Colonne logo */
	$html .= '<div class="bqp-admin__col bqp-admin__col--logo">';
	$html .= '<span class="bqp-admin__label">Logo du partenaire</span>';
	$html .= '<div class="bqp-admin__logo-box' . ( $logo ? ' has-logo' : '' ) . '" id="bqp-logo-box">';
	$html .= '<img src="' . esc_url( $src ) . '" alt="" id="bqp-logo-preview"' . ( $logo ? '' : ' hidden' ) . ' />';
	$html .= '<div class="bqp-admin__logo-empty" id="bqp-logo-empty"' . ( $logo ? ' hidden' : '' ) . '>';
	$html .= '<span class="dashicons dashicons-format-image"></span><span>Aucun logo</span>';
	$html .= '</div></div>';
	$html .= '<input type="hidden" name="bqp_logo_id" id="bqp-logo-id" value="' . esc_attr( $logo_id ) . '" />';
	$html .= '<div class="bqp-admin__logo-actions">';
	$html .= '<button type="button" class="button button-primary" id="bqp-logo-select">' . ( $logo ? 'Remplacer' : 'Choisir un logo' ) . '</button>';
	$html .= '<button type="button" class="button-link bqp-admin__remove" id="bqp-logo-remove"' . ( $logo ? '' : ' hidden' ) . '>Retirer</button>';
	$html .= '</div>';
	$html .= '<p class="bqp-admin__hint">PNG ou SVG sur fond transparent, largeur conseillée 400&nbsp;px.</p>';
	$html .= '</div>';

	/* Colonne champs */
	$html .= '<div class="bqp-admin__col">';

	$html .= '<p class="bqp-admin__field">';
	$html .= '<label class="bqp-admin__label" for="bqp_url">Site internet</label>';
	$html .= '<input type="url" class="bqp-admin__input" name="bqp_url" id="bqp_url" value="' . esc_attr( $url ) . '" placeholder="https://exemple.fr" />';
	$html .= '<span class="bqp-admin__hint">Le lien s\'ouvre dans un nouvel onglet, depuis la carte et depuis la fiche.</span>';
	$html .= '</p>';

	$html .= '<p class="bqp-admin__field">';
	$html .= '<label class="bqp-admin__label" for="bqp_description">Courte description</label>';
	$html .= '<textarea class="bqp-admin__input bqp-admin__textarea" name="bqp_description" id="bqp_description" rows="4" maxlength="400" placeholder="Une ou deux phrases sur le partenaire et son rôle.">' . esc_textarea( $desc ) . '</textarea>';
	$html .= '<span class="bqp-admin__hint"><span id="bqp-count">' . esc_html( $count ) . '</span> caractères. Idéalement entre 90 et 180 : affichée sur la carte, et en introduction de la fiche.</span>';
	$html .= '</p>';

	$html .= '<p class="bqp-admin__field">';
	$html .= '<span class="bqp-admin__label">Catégorie</span>';
	$html .= '<span class="bqp-admin__hint">La catégorie se choisit dans l\'encadré <strong>Catégories</strong>, à droite de cet écran. Elle décide du <strong>bloc</strong> dans lequel le partenaire apparaît (Académiques, Associations…). Un partenaire peut appartenir à plusieurs blocs.</span>';
	$html .= '</p>';

	$html .= '</div></div>';

	/* Fiche détaillée, affichée dans la fenêtre au clic */
	$html .= '<div class="bqp-admin__profile">';
	$html .= '<div class="bqp-admin__profile-head"><span class="dashicons dashicons-id-alt"></span><div><strong>Fiche détaillée</strong><span>Affichée dans une fenêtre quand un visiteur clique sur le partenaire. Tous les champs sont facultatifs : seuls ceux remplis apparaissent.</span></div></div>';

	$html .= '<div class="bqp-admin__cols3">';
	$html .= '<p class="bqp-admin__field"><label class="bqp-admin__label" for="bqp_depuis">Partenaire depuis</label><input type="number" class="bqp-admin__input" name="bqp_depuis" id="bqp_depuis" value="' . esc_attr( $m( 'depuis' ) ) . '" min="1900" max="2100" placeholder="Ex. 2019" /><span class="bqp-admin__hint">Année du début du partenariat.</span></p>';
	$html .= '<p class="bqp-admin__field"><label class="bqp-admin__label" for="bqp_lieu">Localisation</label><input type="text" class="bqp-admin__input" name="bqp_lieu" id="bqp_lieu" value="' . esc_attr( $m( 'lieu' ) ) . '" placeholder="Ex. Paris, France" /></p>';
	$html .= '<p class="bqp-admin__field"><label class="bqp-admin__label" for="bqp_linkedin">Page LinkedIn</label><input type="url" class="bqp-admin__input" name="bqp_linkedin" id="bqp_linkedin" value="' . esc_attr( $m( 'linkedin' ) ) . '" placeholder="https://www.linkedin.com/company/…" /></p>';
	$html .= '</div>';

	$html .= '<p class="bqp-admin__field"><label class="bqp-admin__label" for="bqp_domaines">Domaines de collaboration</label><input type="text" class="bqp-admin__input" name="bqp_domaines" id="bqp_domaines" value="' . esc_attr( $m( 'domaines' ) ) . '" placeholder="Ex. Mécénat, Jury du prix, Accueil de lauréats" /><span class="bqp-admin__hint">Séparés par des virgules : ils s\'affichent en pastilles dans la fiche.</span></p>';

	$html .= '<p class="bqp-admin__field"><label class="bqp-admin__label" for="bqp_partenariat">Notre partenariat</label><textarea class="bqp-admin__input bqp-admin__textarea" name="bqp_partenariat" id="bqp_partenariat" rows="3" placeholder="Ce que le partenaire apporte à la Bourse, et ce que vous faites ensemble.">' . esc_textarea( $m( 'partenariat' ) ) . '</textarea><span class="bqp-admin__hint">Mis en valeur comme une citation dans la fiche.</span></p>';

	$html .= '<div class="bqp-admin__field"><span class="bqp-admin__label">Présentation détaillée</span>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	wp_editor(
		get_post_meta( $post->ID, '_bqp_presentation', true ),
		'bqp_presentation',
		array(
			'textarea_name' => 'bqp_presentation',
			'textarea_rows' => 8,
			'media_buttons' => false,
			'teeny'         => true,
			'quicktags'     => false,
			'tinymce'       => array( 'toolbar1' => 'formatselect,bold,italic,bullist,numlist,link,unlink' ),
		)
	);

	$html  = '<span class="bqp-admin__hint">Historique, missions, chiffres clés : quelques paragraphes, des intertitres et des listes au besoin.</span></div>';
	$html .= '</div>';

	$html .= '<div class="bqp-admin__shortcode">';
	$html .= '<span class="dashicons dashicons-shortcode"></span>';
	$html .= '<span>Pour afficher les blocs sur une page :</span>';
	$html .= '<code>[bqp_partenaires]</code>';
	$html .= '</div></div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( 'save_post_' . BQP_PARTENAIRES_CPT, 'bqp_save_partenaire', 10, 2 );
function bqp_save_partenaire( $post_id, $post ) {
	if ( ! isset( $_POST['bqp_partenaire_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bqp_partenaire_nonce'] ) ), 'bqp_save_partenaire' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$store = function ( $key, $value ) use ( $post_id ) {
		if ( '' !== (string) $value && 0 !== $value ) {
			update_post_meta( $post_id, '_bqp_' . $key, $value );
		} else {
			delete_post_meta( $post_id, '_bqp_' . $key );
		}
	};

	$store( 'logo_id', isset( $_POST['bqp_logo_id'] ) ? absint( $_POST['bqp_logo_id'] ) : 0 );

	foreach ( array( 'url', 'linkedin' ) as $key ) {
		$url = isset( $_POST[ 'bqp_' . $key ] ) ? trim( (string) wp_unslash( $_POST[ 'bqp_' . $key ] ) ) : '';
		if ( '' !== $url && ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . ltrim( $url, '/' );
		}
		$store( $key, esc_url_raw( $url ) );
	}

	$store( 'description', isset( $_POST['bqp_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bqp_description'] ) ) : '' );
	$store( 'partenariat', isset( $_POST['bqp_partenariat'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bqp_partenariat'] ) ) : '' );
	$store( 'lieu', isset( $_POST['bqp_lieu'] ) ? sanitize_text_field( wp_unslash( $_POST['bqp_lieu'] ) ) : '' );
	$store( 'domaines', isset( $_POST['bqp_domaines'] ) ? sanitize_text_field( wp_unslash( $_POST['bqp_domaines'] ) ) : '' );
	$store( 'presentation', isset( $_POST['bqp_presentation'] ) ? wp_kses_post( wp_unslash( $_POST['bqp_presentation'] ) ) : '' );

	$depuis = isset( $_POST['bqp_depuis'] ) ? absint( $_POST['bqp_depuis'] ) : 0;
	$store( 'depuis', ( $depuis >= 1900 && $depuis <= 2100 ) ? $depuis : 0 );
}

/* -------------------------------------------------------------------------
 * 5. Colonnes de la liste des partenaires
 * ---------------------------------------------------------------------- */

add_filter( 'manage_' . BQP_PARTENAIRES_CPT . '_posts_columns', 'bqp_admin_columns' );
function bqp_admin_columns( $columns ) {
	$new = array();

	if ( isset( $columns['cb'] ) ) {
		$new['cb'] = $columns['cb'];
	}

	$new['bqp_logo'] = 'Logo';
	$new['title']    = 'Partenaire';

	if ( isset( $columns[ 'taxonomy-' . BQP_PARTENAIRES_TAX ] ) ) {
		$new[ 'taxonomy-' . BQP_PARTENAIRES_TAX ] = 'Catégorie';
	}

	$new['bqp_url']   = 'Site internet';
	$new['bqp_fiche'] = 'Fiche détaillée';
	$new['bqp_order'] = 'Ordre';
	$new['date']      = isset( $columns['date'] ) ? $columns['date'] : 'Date';

	return $new;
}

add_action( 'manage_' . BQP_PARTENAIRES_CPT . '_posts_custom_column', 'bqp_admin_column_content', 10, 2 );
function bqp_admin_column_content( $column, $post_id ) {
	if ( 'bqp_logo' === $column ) {
		$logo_id = (int) get_post_meta( $post_id, '_bqp_logo_id', true );

		if ( $logo_id ) {
			echo '<span class="bqp-col-logo">' . wp_get_attachment_image( $logo_id, array( 90, 60 ), false, array( 'alt' => '' ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<span class="bqp-col-logo bqp-col-logo--empty">' . esc_html( bqp_initials( get_the_title( $post_id ) ) ) . '</span>';
		}

		return;
	}

	if ( 'bqp_url' === $column ) {
		$url = (string) get_post_meta( $post_id, '_bqp_url', true );

		if ( $url ) {
			printf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
				esc_url( $url ),
				esc_html( preg_replace( '#^https?://(www\.)?#i', '', untrailingslashit( $url ) ) )
			);
		} else {
			echo '<span style="color:#b0b0b0;">&mdash;</span>';
		}

		return;
	}

	if ( 'bqp_order' === $column ) {
		echo esc_html( get_post_field( 'menu_order', $post_id ) );
	}

	if ( 'bqp_fiche' === $column ) {
		$p    = bqp_partner_data( $post_id );
		$tags = array_filter(
			array(
				'Depuis'       => $p['depuis'],
				'Lieu'         => $p['lieu'],
				'Domaines'     => $p['domaines'],
				'Partenariat'  => $p['partenariat'],
				'Présentation' => trim( wp_strip_all_tags( $p['presentation'] ) ),
			)
		);
		echo $tags ? '<span class="bqp-col-tags"><span>' . implode( '</span><span>', array_map( 'esc_html', array_keys( $tags ) ) ) . '</span></span>' : '<span style="color:#b0b0b0;">&mdash;</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

add_filter( 'manage_edit-' . BQP_PARTENAIRES_CPT . '_sortable_columns', 'bqp_sortable_columns' );
function bqp_sortable_columns( $columns ) {
	$columns['bqp_order'] = 'menu_order';

	return $columns;
}

add_action( 'pre_get_posts', 'bqp_admin_default_order' );
function bqp_admin_default_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( BQP_PARTENAIRES_CPT !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}

/* -------------------------------------------------------------------------
 * 6. Page « Mode d'emploi »
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'bqp_admin_help_page' );
function bqp_admin_help_page() {
	add_submenu_page(
		'edit.php?post_type=' . BQP_PARTENAIRES_CPT,
		'Mode d\'emploi',
		'Mode d\'emploi',
		'edit_posts',
		'bqp-aide',
		'bqp_render_help_page'
	);
}

function bqp_render_help_page() {
	$options = array(
		array( 'categories', 'Limite l\'affichage à certaines catégories (slugs séparés par une virgule). Avec une seule catégorie, on obtient un seul bloc, à placer où l\'on veut.', 'toutes', '[bqp_partenaires categories="academiques"]' ),
		array( 'affichage', 'blocs : un bloc par catégorie. grille : une seule grille avec des filtres, comme en version 1.', 'blocs', '[bqp_partenaires affichage="grille"]' ),
		array( 'replie', 'Catégories repliées : une carte par catégorie, un clic affiche ses partenaires. non : tous les blocs dépliés les uns sous les autres.', 'oui', '[bqp_partenaires replie="non"]' ),
		array( 'ouvert', 'Catégorie ouverte au chargement, quand elles sont repliées (son identifiant).', 'aucune', '[bqp_partenaires ouvert="academiques"]' ),
		array( 'sommaire', 'Blocs dépliés : liens vers chaque bloc, en haut.', 'oui', '[bqp_partenaires replie="non" sommaire="non"]' ),
		array( 'intro', 'Affiche la description de la catégorie sous le titre du bloc.', 'oui', '[bqp_partenaires intro="non"]' ),
		array( 'fenetre', 'Ouvre la fiche détaillée au clic sur un partenaire.', 'oui', '[bqp_partenaires fenetre="non"]' ),
		array( 'colonnes', 'Nombre de colonnes sur grand écran : 2, 3 ou 4.', '4', '[bqp_partenaires colonnes="4"]' ),
		array( 'filtres', 'En affichage grille : affiche ou masque la barre de filtres.', 'oui', '[bqp_partenaires affichage="grille" filtres="non"]' ),
		array( 'compteurs', 'Affiche le nombre de partenaires de chaque bloc ou filtre.', 'oui', '[bqp_partenaires compteurs="non"]' ),
		array( 'titre', 'Ajoute un titre au-dessus du bloc.', 'vide', '[bqp_partenaires titre="Ils nous accompagnent"]' ),
		array( 'logos', 'Rendu des logos : grisaille (couleur au survol) ou couleur.', 'grisaille', '[bqp_partenaires logos="couleur"]' ),
		array( 'limite', 'Nombre maximum de partenaires affichés (par bloc en affichage blocs).', 'tous', '[bqp_partenaires limite="8"]' ),
		array( 'ordre', 'Tri : manuel, nom ou recent.', 'manuel', '[bqp_partenaires ordre="nom"]' ),
	);

	$notes = array(
		'L\'ordre d\'affichage se règle avec le champ <strong>Ordre</strong> de chaque partenaire (0 en premier).',
		'Chaque catégorie forme son propre <strong>bloc</strong>. Son titre, sa couleur, son pictogramme, son ordre et son texte d\'introduction (champ Description) se règlent dans <strong>Partenaires &rsaquo; Catégories</strong>.',
		'Un clic sur un partenaire ouvre sa <strong>fiche détaillée</strong> : logo, description, partenaire depuis, localisation, domaines de collaboration, notre partenariat, présentation, site et LinkedIn. Les flèches passent au partenaire suivant du même bloc.',
		'Chaque fiche a son lien direct : l\'adresse de la page suivie de <strong>#identifiant-du-partenaire</strong> (par exemple /partenaires/#institut-corvelle).',
		'Un partenaire sans logo affiche automatiquement ses initiales dans un médaillon bordeaux.',
		'Un partenaire sans lien reste affiché, simplement sans bouton de redirection.',
		'Les fiches partenaires ne créent aucune page publique, pour ne pas diluer le référencement du site : elles vivent dans la page qui contient le shortcode.',
	);

	$html  = '<div class="wrap bqp-help">';
	$html .= '<h1 class="bqp-help__title">Bloc Partenaires</h1>';
	$html .= '<p class="bqp-help__intro">Ajoutez vos partenaires depuis le menu <strong>Partenaires</strong>, puis collez le shortcode ci-dessous dans une page Elementor (widget <em>Shortcode</em>) ou dans l\'éditeur WordPress. Les partenaires s\'affichent en un bloc par catégorie, sans être mélangés.</p>';

	$html .= '<div class="bqp-help__hero"><span>Shortcode principal</span><code>[bqp_partenaires]</code></div>';

	$html .= '<h2 class="bqp-help__subtitle">Les options disponibles</h2>';
	$html .= '<table class="widefat striped bqp-help__table"><thead><tr>';
	$html .= '<th>Option</th><th>Rôle</th><th>Par défaut</th><th>Exemple</th>';
	$html .= '</tr></thead><tbody>';

	foreach ( $options as $option ) {
		$html .= '<tr>';
		$html .= '<td><code>' . esc_html( $option[0] ) . '</code></td>';
		$html .= '<td>' . esc_html( $option[1] ) . '</td>';
		$html .= '<td>' . esc_html( $option[2] ) . '</td>';
		$html .= '<td><code>' . esc_html( $option[3] ) . '</code></td>';
		$html .= '</tr>';
	}

	$html .= '</tbody></table>';

	$html .= '<h2 class="bqp-help__subtitle">Bon à savoir</h2><ul class="bqp-help__list">';
	foreach ( $notes as $note ) {
		$html .= '<li>' . wp_kses( $note, array( 'strong' => array(), 'em' => array() ) ) . '</li>';
	}
	$html .= '</ul></div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/* -------------------------------------------------------------------------
 * 7. Styles et script de l'administration
 * ---------------------------------------------------------------------- */

add_action( 'admin_enqueue_scripts', 'bqp_admin_assets' );
function bqp_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || ( BQP_PARTENAIRES_CPT !== $screen->post_type && BQP_PARTENAIRES_TAX !== $screen->taxonomy ) ) {
		return;
	}

	$is_editor = in_array( $hook, array( 'post.php', 'post-new.php' ), true );

	if ( $is_editor ) {
		wp_enqueue_media();
	}

	wp_register_style( 'bqp-admin', false, array(), BQP_PARTENAIRES_VERSION );
	wp_enqueue_style( 'bqp-admin' );
	wp_add_inline_style( 'bqp-admin', bqp_admin_css() );

	if ( $is_editor ) {
		wp_register_script( 'bqp-admin', false, array( 'jquery' ), BQP_PARTENAIRES_VERSION, true );
		wp_enqueue_script( 'bqp-admin' );
		wp_add_inline_script( 'bqp-admin', bqp_admin_js() );
	}
}

function bqp_admin_css() {
	$p = bqp_palette();

	return '
	.bqp-admin{font-size:14px;}
	.bqp-admin__grid{display:grid;grid-template-columns:260px 1fr;gap:28px;align-items:start;}
	@media (max-width:782px){.bqp-admin__grid{grid-template-columns:1fr;}}
	.bqp-admin__label{display:block;margin-bottom:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:' . $p['bordeaux'] . ';}
	.bqp-admin__field{margin:0 0 22px;}
	.bqp-admin__input{width:100%;padding:11px 14px;border:1px solid #dcdcde;border-radius:8px;background:#fff;box-shadow:none;transition:border-color .2s,box-shadow .2s;}
	.bqp-admin__input:focus{border-color:' . $p['bordeaux'] . ';box-shadow:0 0 0 3px ' . bqp_hex_to_rgba( $p['bordeaux'], 0.12 ) . ';outline:none;}
	.bqp-admin__textarea{resize:vertical;min-height:96px;line-height:1.6;}
	.bqp-admin__hint{display:block;margin-top:7px;color:#787c82;font-size:12.5px;line-height:1.5;}
	.bqp-admin__logo-box{display:flex;align-items:center;justify-content:center;height:150px;padding:18px;border:2px dashed #dcdcde;border-radius:12px;background:#fbfbfc;transition:border-color .2s,background .2s;}
	.bqp-admin__logo-box.has-logo{border-style:solid;border-color:' . bqp_hex_to_rgba( $p['bordeaux'], 0.3 ) . ';background:#fff;}
	.bqp-admin__logo-box img{max-width:100%;max-height:114px;width:auto;height:auto;object-fit:contain;}
	.bqp-admin__logo-empty{display:flex;flex-direction:column;align-items:center;gap:6px;color:#a7aaad;font-size:12.5px;}
	.bqp-admin__logo-empty .dashicons{font-size:30px;width:30px;height:30px;}
	.bqp-admin__logo-actions{display:flex;align-items:center;gap:12px;margin-top:12px;}
	.bqp-admin__remove{color:#b32d2e;text-decoration:none;cursor:pointer;}
	.bqp-admin__remove:hover{color:#8a2223;}
	.bqp-admin__shortcode{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin-top:8px;padding:14px 18px;border-radius:10px;background:' . bqp_hex_to_rgba( $p['bordeaux'], 0.05 ) . ';border:1px solid ' . bqp_hex_to_rgba( $p['bordeaux'], 0.14 ) . ';color:' . $p['bordeaux_dark'] . ';}
	.bqp-admin__shortcode code{background:#fff;border:1px solid ' . bqp_hex_to_rgba( $p['bordeaux'], 0.2 ) . ';color:' . $p['bordeaux'] . ';padding:5px 11px;border-radius:6px;font-weight:600;}
	.bqp-admin__shortcode .dashicons{color:' . $p['bordeaux'] . ';}
	.column-bqp_logo{width:110px;}
	.column-bqp_order{width:70px;}
	.bqp-col-logo{display:flex;align-items:center;justify-content:center;width:90px;height:56px;border:1px solid #e6e6e8;border-radius:8px;background:#fff;overflow:hidden;}
	.bqp-col-logo img{max-width:74px;max-height:42px;width:auto;height:auto;object-fit:contain;}
	.bqp-col-logo--empty{background:' . bqp_hex_to_rgba( $p['bordeaux'], 0.07 ) . ';border-color:' . bqp_hex_to_rgba( $p['bordeaux'], 0.16 ) . ';color:' . $p['bordeaux'] . ';font-weight:700;letter-spacing:.04em;}
	.bqp-help__title{font-size:30px;font-weight:600;color:' . $p['bordeaux_dark'] . ';}
	.bqp-help__intro{max-width:760px;font-size:14.5px;line-height:1.7;color:#50575e;}
	.bqp-help__hero{display:flex;align-items:center;flex-wrap:wrap;gap:14px;margin:22px 0 32px;padding:22px 26px;border-radius:14px;background:linear-gradient(135deg,' . $p['bordeaux'] . ' 0%,' . $p['bordeaux_dark'] . ' 100%);color:#fff;box-shadow:0 12px 30px -16px ' . bqp_hex_to_rgba( $p['bordeaux_dark'], 0.8 ) . ';}
	.bqp-help__hero span{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.75;}
	.bqp-help__hero code{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);color:#fff;padding:9px 16px;border-radius:8px;font-size:15px;font-weight:600;}
	.bqp-help__subtitle{margin-top:34px;font-size:19px;font-weight:600;color:' . $p['bordeaux_dark'] . ';}
	.bqp-help__table{max-width:1080px;}
	.bqp-help__table th{font-weight:600;}
	.bqp-help__table code{color:' . $p['bordeaux'] . ';background:' . bqp_hex_to_rgba( $p['bordeaux'], 0.06 ) . ';}
	.bqp-help__list{max-width:840px;line-height:1.8;list-style:disc;padding-left:20px;color:#50575e;}
	.bqp-admin__profile{margin:26px 0 8px;padding:22px 24px 8px;border:1px solid ' . bqp_hex_to_rgba( $p['bordeaux'], 0.18 ) . ';border-radius:12px;background:' . bqp_hex_to_rgba( $p['bordeaux'], 0.025 ) . ';}
	.bqp-admin__profile-head{display:flex;gap:12px;align-items:flex-start;margin:0 0 20px;}
	.bqp-admin__profile-head .dashicons{color:' . $p['bordeaux'] . ';font-size:24px;width:24px;height:24px;}
	.bqp-admin__profile-head strong{display:block;font-size:15px;color:' . $p['bordeaux_dark'] . ';}
	.bqp-admin__profile-head span{color:#787c82;font-size:12.5px;}
	.bqp-admin__cols3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:0 18px;}
	@media (max-width:1100px){.bqp-admin__cols3{grid-template-columns:1fr;}}
	.bqp-admin .wp-editor-wrap{border:1px solid #dcdcde;border-radius:8px;overflow:hidden;}
	.column-bqp_fiche{width:190px;}
	.bqp-col-tags{display:flex;flex-wrap:wrap;gap:4px;}
	.bqp-col-tags span{padding:2px 8px;border-radius:999px;background:' . bqp_hex_to_rgba( $p['bordeaux'], 0.07 ) . ';color:' . $p['bordeaux'] . ';font-size:11px;font-weight:600;}
	.bqp-term-chip{display:inline-flex;align-items:center;gap:7px;padding:4px 11px 4px 8px;border-radius:999px;background:var(--c);color:#fff;font-weight:600;font-size:12px;white-space:nowrap;}
	.bqp-admin [hidden]{display:none !important;}
	.column-bqp_bloc{width:220px;}
	.column-bqp_ordre{width:60px;}
	';
}

function bqp_admin_js() {
	return "
	(function(\$){
		\$(function(){
			var frame,
				\$id      = \$('#bqp-logo-id'),
				\$preview = \$('#bqp-logo-preview'),
				\$empty   = \$('#bqp-logo-empty'),
				\$box     = \$('#bqp-logo-box'),
				\$remove  = \$('#bqp-logo-remove'),
				\$select  = \$('#bqp-logo-select');

			\$select.on('click', function(e){
				e.preventDefault();

				if (frame) { frame.open(); return; }

				frame = wp.media({
					title: 'Choisir le logo du partenaire',
					button: { text: 'Utiliser ce logo' },
					library: { type: 'image' },
					multiple: false
				});

				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON(),
						src = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;

					\$id.val(att.id);
					\$preview.attr('src', src).prop('hidden', false);
					\$empty.prop('hidden', true);
					\$box.addClass('has-logo');
					\$remove.prop('hidden', false);
					\$select.text('Remplacer');
				});

				frame.open();
			});

			\$remove.on('click', function(e){
				e.preventDefault();
				\$id.val('');
				\$preview.attr('src','').prop('hidden', true);
				\$empty.prop('hidden', false);
				\$box.removeClass('has-logo');
				\$remove.prop('hidden', true);
				\$select.text('Choisir un logo');
			});

			\$('#bqp_description').on('input', function(){
				\$('#bqp-count').text(\$(this).val().length);
			});
		});
	})(jQuery);
	";
}

/* -------------------------------------------------------------------------
 * 8. Shortcode [bqp_partenaires]
 * ---------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', 'bqp_register_front_assets' );
function bqp_register_front_assets() {
	wp_register_style( 'bqp-partenaires', false, array(), BQP_PARTENAIRES_VERSION );
	wp_add_inline_style( 'bqp-partenaires', bqp_front_css() );

	wp_register_script( 'bqp-partenaires', false, array(), BQP_PARTENAIRES_VERSION, true );
	wp_add_inline_script( 'bqp-partenaires', bqp_front_js() );

	// Chargement anticipé quand le shortcode est détectable dans la page,
	// pour que le style soit présent dès le rendu et non dans le pied de page.
	if ( is_singular() ) {
		$post    = get_post();
		$content = $post instanceof WP_Post ? (string) $post->post_content . (string) get_post_meta( $post->ID, '_elementor_data', true ) : '';

		if ( false !== strpos( $content, '[bqp_partenaires' ) ) {
			wp_enqueue_style( 'bqp-partenaires' );
			wp_enqueue_script( 'bqp-partenaires' );
		}
	}
}

/**
 * Toutes les informations d'un partenaire, pour la carte et la fiche.
 */
function bqp_partner_data( $post_id ) {
	$terms = get_the_terms( $post_id, BQP_PARTENAIRES_TAX );
	$terms = ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
	$order = array_flip( wp_list_pluck( bqp_ordered_terms(), 'term_id' ) );

	// Catégorie principale : la première dans l'ordre des blocs.
	usort(
		$terms,
		function ( $a, $b ) use ( $order ) {
			return ( isset( $order[ $a->term_id ] ) ? $order[ $a->term_id ] : 99 ) - ( isset( $order[ $b->term_id ] ) ? $order[ $b->term_id ] : 99 );
		}
	);

	$m = function ( $key ) use ( $post_id ) {
		return (string) get_post_meta( $post_id, '_bqp_' . $key, true );
	};

	$main     = $terms ? $terms[0] : null;
	$domaines = array_values( array_filter( array_map( 'trim', explode( ',', $m( 'domaines' ) ) ) ) );

	return array(
		'id'           => (int) $post_id,
		'slug'         => get_post_field( 'post_name', $post_id ),
		'name'         => get_the_title( $post_id ),
		'logo'         => (int) $m( 'logo_id' ),
		'url'          => $m( 'url' ),
		'desc'         => $m( 'description' ),
		'depuis'       => (int) $m( 'depuis' ),
		'lieu'         => $m( 'lieu' ),
		'linkedin'     => $m( 'linkedin' ),
		'domaines'     => $domaines,
		'partenariat'  => $m( 'partenariat' ),
		'presentation' => (string) get_post_meta( $post_id, '_bqp_presentation', true ),
		'terms'        => $terms,
		'label'        => $main ? $main->name : '',
		'color'        => $main ? bqp_term_color( $main->term_id ) : bqp_palette()['bordeaux'],
		'slugs'        => wp_list_pluck( $terms, 'slug' ),
	);
}

/**
 * La fiche mérite une fenêtre dès qu'elle apporte plus que la carte.
 */
function bqp_has_profile( $p ) {
	return $p['desc'] || $p['depuis'] || $p['lieu'] || $p['linkedin'] || $p['domaines'] || $p['partenariat'] || trim( wp_strip_all_tags( $p['presentation'] ) );
}

function bqp_cat_style( $color ) {
	return '--bqp-cat:' . $color . ';--bqp-cat-soft:' . bqp_hex_to_rgba( $color, 0.08 ) . ';--bqp-cat-line:' . bqp_hex_to_rgba( $color, 0.22 ) . ';';
}

function bqp_host( $url ) {
	return preg_replace( '#^www\.#i', '', (string) wp_parse_url( $url, PHP_URL_HOST ) );
}

/**
 * Une carte partenaire. $ctx : popup, tpl (identifiant du modèle de fiche),
 * badge (étiquette de catégorie), heading (niveau du nom), color.
 */
function bqp_render_card( $p, $ctx ) {
	$color     = isset( $ctx['color'] ) ? $ctx['color'] : $p['color'];
	$clickable = $ctx['popup'] && bqp_has_profile( $p );
	$tag       = $ctx['heading'];
	$meta      = array_filter( array( $p['lieu'], $p['depuis'] ? 'Partenaire depuis ' . $p['depuis'] : '' ) );

	$html  = '<article class="bqp-card' . ( $clickable ? ' is-clickable' : '' ) . '" data-cats="' . esc_attr( implode( ' ', $p['slugs'] ) ) . '" data-slug="' . esc_attr( $p['slug'] ) . '" style="' . esc_attr( bqp_cat_style( $color ) ) . '">';

	$html .= '<div class="bqp-card__logo">';
	$html .= $p['logo']
		? wp_get_attachment_image( $p['logo'], 'medium', false, array( 'class' => 'bqp-card__img', 'alt' => 'Logo ' . $p['name'], 'loading' => 'lazy' ) )
		: '<span class="bqp-card__initials" aria-hidden="true">' . esc_html( bqp_initials( $p['name'] ) ) . '</span>';
	$html .= '</div>';

	$html .= '<div class="bqp-card__body">';
	if ( $ctx['badge'] && $p['label'] ) {
		$html .= '<span class="bqp-card__badge">' . esc_html( $p['label'] ) . '</span>';
	}
	$html .= '<' . $tag . ' class="bqp-card__name">' . esc_html( $p['name'] ) . '</' . $tag . '>';
	if ( $meta ) {
		$html .= '<p class="bqp-card__meta">' . esc_html( implode( ' · ', $meta ) ) . '</p>';
	}
	if ( $p['desc'] ) {
		$html .= '<p class="bqp-card__desc">' . esc_html( $p['desc'] ) . '</p>';
	}

	$arrow = '<svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true" focusable="false"><path d="M1 8h12M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	if ( $clickable ) {
		// Toute la carte ouvre la fiche ; le lien du site reste cliquable à part.
		$html .= '<div class="bqp-card__foot"><span class="bqp-card__more" aria-hidden="true">Voir la fiche ' . $arrow . '</span>';
		if ( $p['url'] ) {
			$html .= '<a class="bqp-card__site" href="' . esc_url( $p['url'] ) . '" target="_blank" rel="noopener noreferrer">Site'
				. '<svg viewBox="0 0 16 16" width="12" height="12" aria-hidden="true" focusable="false"><path d="M6 3H3v10h10v-3M9 2h5v5M14 2 7 9" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>'
				. '<span class="screen-reader-text"> de ' . esc_html( $p['name'] ) . ' (nouvelle fenêtre)</span></a>';
		}
		$html .= '</div>';
		$html .= '<button type="button" class="bqp-card__open" data-bqp-open="' . esc_attr( $ctx['tpl'] ) . '" aria-haspopup="dialog"><span class="screen-reader-text">Voir la fiche de ' . esc_html( $p['name'] ) . '</span></button>';
	} elseif ( $p['url'] ) {
		$html .= '<a class="bqp-card__link" href="' . esc_url( $p['url'] ) . '" target="_blank" rel="noopener noreferrer"><span>Visiter le site</span>' . $arrow . '<span class="screen-reader-text">(nouvelle fenêtre)</span></a>';
	}

	return $html . '</div></article>';
}

/**
 * La fiche d'un partenaire, ouverte dans la fenêtre.
 */
function bqp_render_profile( $p ) {
	$title = 'bqp-profile-title-' . $p['id'];
	$facts = array();

	if ( $p['terms'] ) {
		$facts['Catégorie'] = implode( ', ', wp_list_pluck( $p['terms'], 'name' ) );
	}
	if ( $p['depuis'] ) {
		$facts['Partenaire depuis'] = (string) $p['depuis'];
	}
	if ( $p['lieu'] ) {
		$facts['Localisation'] = $p['lieu'];
	}
	if ( $p['url'] ) {
		$facts['Site internet'] = bqp_host( $p['url'] );
	}

	$html  = '<div class="bqp-profile" style="' . esc_attr( bqp_cat_style( $p['color'] ) ) . '" data-title="' . esc_attr( $title ) . '">';
	$html .= '<aside class="bqp-profile__side">';
	$html .= '<div class="bqp-profile__logo">' . ( $p['logo']
		? wp_get_attachment_image( $p['logo'], 'medium_large', false, array( 'alt' => 'Logo ' . $p['name'], 'loading' => 'lazy' ) )
		: '<span class="bqp-profile__initials" aria-hidden="true">' . esc_html( bqp_initials( $p['name'] ) ) . '</span>' ) . '</div>';

	if ( $facts ) {
		$html .= '<dl class="bqp-profile__facts">';
		foreach ( $facts as $label => $value ) {
			$html .= '<div><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
		}
		$html .= '</dl>';
	}

	$actions = '';
	if ( $p['url'] ) {
		$actions .= '<a class="bqp-btn" href="' . esc_url( $p['url'] ) . '" target="_blank" rel="noopener noreferrer">Visiter le site'
			. '<svg viewBox="0 0 16 16" width="13" height="13" aria-hidden="true" focusable="false"><path d="M6 3H3v10h10v-3M9 2h5v5M14 2 7 9" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></a>';
	}
	if ( $p['linkedin'] ) {
		$actions .= '<a class="bqp-btn bqp-btn--ghost" href="' . esc_url( $p['linkedin'] ) . '" target="_blank" rel="noopener noreferrer">Page LinkedIn</a>';
	}
	if ( $actions ) {
		$html .= '<div class="bqp-profile__actions">' . $actions . '</div>';
	}
	$html .= '</aside>';

	$html .= '<div class="bqp-profile__main">';
	if ( $p['label'] ) {
		$html .= '<span class="bqp-card__badge">' . esc_html( $p['label'] ) . '</span>';
	}
	$html .= '<h2 class="bqp-profile__name" id="' . esc_attr( $title ) . '">' . esc_html( $p['name'] ) . '</h2>';
	if ( $p['desc'] ) {
		$html .= '<p class="bqp-profile__lead">' . esc_html( $p['desc'] ) . '</p>';
	}
	if ( $p['domaines'] ) {
		$html .= '<section class="bqp-profile__section"><h3>Domaines de collaboration</h3><ul class="bqp-profile__tags">';
		foreach ( $p['domaines'] as $domaine ) {
			$html .= '<li>' . esc_html( $domaine ) . '</li>';
		}
		$html .= '</ul></section>';
	}
	if ( $p['partenariat'] ) {
		$html .= '<section class="bqp-profile__section bqp-profile__section--quote"><h3>Notre partenariat</h3>' . wpautop( esc_html( $p['partenariat'] ) ) . '</section>';
	}
	if ( trim( wp_strip_all_tags( $p['presentation'] ) ) ) {
		$html .= '<section class="bqp-profile__section bqp-profile__rich"><h3>Présentation</h3>' . wpautop( wp_kses_post( $p['presentation'] ) ) . '</section>';
	}

	return $html . '</div></div>';
}

add_shortcode( 'bqp_partenaires', 'bqp_shortcode' );
function bqp_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'categories' => '',
			'affichage'  => 'blocs',
			'replie'     => 'oui',
			'ouvert'     => '',
			'sommaire'   => 'oui',
			'intro'      => 'oui',
			'fenetre'    => 'oui',
			'colonnes'   => '4',
			'filtres'    => 'oui',
			'compteurs'  => 'oui',
			'titre'      => '',
			'logos'      => 'grisaille',
			'limite'     => '-1',
			'ordre'      => 'manuel',
		),
		$atts,
		'bqp_partenaires'
	);

	$yes = function ( $value ) {
		return in_array( strtolower( (string) $value ), array( 'oui', 'yes', 'true', '1' ), true );
	};

	$columns   = max( 2, min( 4, (int) $atts['colonnes'] ) );
	$blocks    = ( 'grille' !== strtolower( $atts['affichage'] ) );
	$show_nb   = $yes( $atts['compteurs'] );
	$popup     = $yes( $atts['fenetre'] );
	$grayscale = 'couleur' !== strtolower( $atts['logos'] );
	$limit     = (int) $atts['limite'];

	switch ( strtolower( $atts['ordre'] ) ) {
		case 'nom':
			$orderby = array( 'title' => 'ASC' );
			break;
		case 'recent':
			$orderby = array( 'date' => 'DESC' );
			break;
		default:
			$orderby = array( 'menu_order' => 'ASC', 'title' => 'ASC' );
	}

	$args = array(
		'post_type'              => BQP_PARTENAIRES_CPT,
		'post_status'            => 'publish',
		// En blocs, la limite s'applique à chaque bloc.
		'posts_per_page'         => ( $blocks || $limit <= 0 ) ? -1 : $limit,
		'orderby'                => $orderby,
		'no_found_rows'          => true,
		'update_post_term_cache' => true,
		'ignore_sticky_posts'    => true,
	);

	$slugs = array_filter( array_map( 'sanitize_title', explode( ',', $atts['categories'] ) ) );
	if ( $slugs ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => BQP_PARTENAIRES_TAX,
				'field'    => 'slug',
				'terms'    => $slugs,
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();

		if ( current_user_can( 'edit_posts' ) ) {
			return '<p class="bqp-empty-notice">Aucun partenaire publié pour le moment. Ajoutez-en depuis le menu <strong>Partenaires</strong>.</p>';
		}

		return '';
	}

	wp_enqueue_style( 'bqp-partenaires' );
	wp_enqueue_script( 'bqp-partenaires' );

	static $instance = 0;
	$instance++;

	$partners = array();
	foreach ( wp_list_pluck( $query->posts, 'ID' ) as $post_id ) {
		$partners[] = bqp_partner_data( $post_id );
	}
	wp_reset_postdata();

	$titled    = '' !== trim( $atts['titre'] );
	$templates = array();
	$tpl_id    = function ( $p ) use ( $instance, &$templates, $popup ) {
		$id = 'bqp-tpl-' . $instance . '-' . $p['id'];
		if ( $popup && bqp_has_profile( $p ) && ! isset( $templates[ $id ] ) ) {
			$templates[ $id ] = '<template id="' . esc_attr( $id ) . '">' . bqp_render_profile( $p ) . '</template>';
		}
		return $id;
	};

	$classes = 'bqp-partners' . ( $blocks ? ' bqp-partners--blocs' : '' ) . ( $grayscale ? ' bqp-partners--grayscale' : '' );
	$html    = '<section class="' . esc_attr( $classes ) . '" style="--bqp-cols-max:' . esc_attr( $columns ) . ';">';

	if ( $titled ) {
		$html .= '<h2 class="bqp-partners__title">' . esc_html( $atts['titre'] ) . '</h2>';
		$html .= '<span class="bqp-partners__rule" aria-hidden="true"></span>';
	}

	if ( $blocks ) {
		/* Un bloc par catégorie, dans l'ordre choisi. */
		$groups = array();

		foreach ( bqp_ordered_terms() as $term ) {
			if ( $slugs && ! in_array( $term->slug, $slugs, true ) ) {
				continue;
			}

			$members = array_values(
				array_filter(
					$partners,
					function ( $p ) use ( $term ) {
						return in_array( $term->slug, $p['slugs'], true );
					}
				)
			);

			if ( $members ) {
				$groups[] = array( $term, $limit > 0 ? array_slice( $members, 0, $limit ) : $members );
			}
		}

		$orphans = array_values( array_filter( $partners, function ( $p ) { return ! $p['slugs']; } ) );
		if ( $orphans && ! $slugs ) {
			$groups[] = array( null, $limit > 0 ? array_slice( $orphans, 0, $limit ) : $orphans );
		}

		$h_group = $titled ? 'h3' : 'h2';
		$h_card  = $titled ? 'h4' : 'h3';
		$fold    = $yes( $atts['replie'] ) && count( $groups ) > 1;
		$open    = sanitize_title( $atts['ouvert'] );
		$cats    = '';
		$panels  = '';

		// Sommaire : un accès direct à chaque bloc (blocs tous dépliés).
		if ( ! $fold && count( $groups ) > 1 && $yes( $atts['sommaire'] ) ) {
			$html .= '<nav class="bqp-toc" aria-label="Catégories de partenaires">';
			foreach ( $groups as $group ) {
				list( $term, $members ) = $group;
				$color = $term ? bqp_term_color( $term->term_id ) : bqp_palette()['bordeaux'];
				$html .= '<a class="bqp-toc__item" href="#' . esc_attr( 'partenaires-' . ( $term ? $term->slug : 'autres' ) . ( $instance > 1 ? '-' . $instance : '' ) ) . '" style="' . esc_attr( bqp_cat_style( $color ) ) . '">'
					. '<span class="bqp-toc__icon">' . bqp_icon_svg( $term ? bqp_term_setting( $term, 'icone' ) : 'etoile', 16 ) . '</span>'
					. esc_html( $term ? $term->name : 'Autres' )
					. ( $show_nb ? '<span class="bqp-toc__count">' . count( $members ) . '</span>' : '' ) . '</a>';
			}
			$html .= '</nav>';
		}

		foreach ( $groups as $group ) {
			list( $term, $members ) = $group;
			$color   = $term ? bqp_term_color( $term->term_id ) : bqp_palette()['bordeaux'];
			$slug    = $term ? $term->slug : 'autres';
			$anchor  = 'partenaires-' . $slug . ( $instance > 1 ? '-' . $instance : '' );
			$heading = $term ? bqp_term_setting( $term, 'titre' ) : 'Autres partenaires';
			$intro   = ( $term && $yes( $atts['intro'] ) ) ? trim( $term->description ) : '';
			$icon    = $term ? bqp_term_setting( $term, 'icone' ) : 'etoile';
			$n       = count( $members );
			$is_open = $fold && $open === $slug;
			$style   = esc_attr( bqp_cat_style( $color ) );

			// Replié : une carte par catégorie ; un clic déplie ses partenaires.
			if ( $fold ) {
				$cats .= '<button type="button" class="bqp-cat' . ( $is_open ? ' is-on' : '' ) . '" aria-expanded="' . ( $is_open ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $anchor ) . '" data-bqp-cat style="' . $style . '">'
					. '<span class="bqp-cat__band">'
					. '<span class="bqp-cat__icon">' . bqp_icon_svg( $icon, 24 ) . '</span>'
					. '<span class="bqp-cat__count"><strong>' . esc_html( sprintf( '%02d', $n ) ) . '</strong><em>' . _n( 'partenaire', 'partenaires', $n ) . '</em></span>'
					. '</span>'
					. '<span class="bqp-cat__body">'
					. '<span class="bqp-cat__title">' . esc_html( $term ? $term->name : 'Autres' ) . '</span>'
					. ( $intro ? '<span class="bqp-cat__text">' . esc_html( $intro ) . '</span>' : '' )
					. '</span>'
					. '<span class="bqp-cat__foot">'
					. '<span class="bqp-cat__cta"><span class="bqp-cat__see">Découvrir</span><span class="bqp-cat__hide">Replier</span></span>'
					. '<span class="bqp-cat__chevron"><svg viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5.5 8 10 12.5 14.5 8"/></svg></span>'
					. '</span>'
					. '<span class="screen-reader-text">' . esc_html( $heading ) . '</span>'
					. '</button>';
			}

			$block  = '<section class="bqp-group' . ( $fold ? ' is-panel' : '' ) . '" id="' . esc_attr( $anchor ) . '" aria-labelledby="' . esc_attr( $anchor ) . '-titre" style="' . $style . '"' . ( $fold && ! $is_open ? ' hidden' : '' ) . '>';
			$block .= '<header class="bqp-group__head">';
			$block .= '<span class="bqp-group__icon">' . bqp_icon_svg( $icon, 24 ) . '</span>';
			$block .= '<div class="bqp-group__intro"><' . $h_group . ' class="bqp-group__title" id="' . esc_attr( $anchor ) . '-titre" tabindex="-1">' . esc_html( $heading ) . '</' . $h_group . '>'
				. ( $intro ? '<p class="bqp-group__text">' . esc_html( $intro ) . '</p>' : '' ) . '</div>';
			if ( $show_nb ) {
				$block .= '<span class="bqp-group__count"><strong>' . (int) $n . '</strong> ' . _n( 'partenaire', 'partenaires', $n ) . '</span>';
			}
			if ( $fold ) {
				$block .= '<button type="button" class="bqp-group__close" data-bqp-fold-close><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M3.5 3.5l9 9M12.5 3.5l-9 9" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><span class="screen-reader-text">Replier « ' . esc_html( $heading ) . ' »</span></button>';
			}
			$block .= '</header><div class="bqp-grid">';

			foreach ( $members as $p ) {
				$block .= bqp_render_card(
					$p,
					array(
						'popup'   => $popup,
						'tpl'     => $tpl_id( $p ),
						'badge'   => false,
						'heading' => $h_card,
						'color'   => $color,
					)
				);
			}

			$block .= '</div></section>';

			if ( $fold ) {
				$panels .= $block;
			} else {
				$html .= $block;
			}
		}

		if ( $fold ) {
			$html .= '<p class="bqp-cats__hint">Cliquez sur une catégorie pour afficher ses partenaires.</p>';
			$html .= '<div class="bqp-cats" data-bqp-cats>' . $cats . $panels . '</div>';
			// Sans JavaScript, toutes les catégories restent lisibles.
			$html .= '<noscript><style>.bqp-cats .bqp-group[hidden]{display:block !important;}</style></noscript>';
		}
	} else {
		/* Une seule grille, avec les filtres par catégorie (affichage 1.x). */
		$tabs = array();
		foreach ( bqp_ordered_terms() as $term ) {
			$n = count( array_filter( $partners, function ( $p ) use ( $term ) { return in_array( $term->slug, $p['slugs'], true ); } ) );
			if ( $n ) {
				$tabs[ $term->slug ] = array( 'name' => $term->name, 'count' => $n );
			}
		}

		if ( $yes( $atts['filtres'] ) && count( $tabs ) > 1 ) {
			$html .= '<div class="bqp-filters" role="group" aria-label="Filtrer les partenaires par catégorie">';
			$html .= '<button type="button" class="bqp-filter is-active" data-filter="*" aria-pressed="true"><span class="bqp-filter__label">Tous</span>'
				. ( $show_nb ? '<span class="bqp-filter__count">' . count( $partners ) . '</span>' : '' ) . '</button>';
			foreach ( $tabs as $slug => $tab ) {
				$html .= '<button type="button" class="bqp-filter" data-filter="' . esc_attr( $slug ) . '" aria-pressed="false"><span class="bqp-filter__label">' . esc_html( $tab['name'] ) . '</span>'
					. ( $show_nb ? '<span class="bqp-filter__count">' . (int) $tab['count'] . '</span>' : '' ) . '</button>';
			}
			$html .= '</div>';
		}

		$html .= '<div class="bqp-grid">';
		foreach ( $partners as $p ) {
			$html .= bqp_render_card(
				$p,
				array(
					'popup'   => $popup,
					'tpl'     => $tpl_id( $p ),
					'badge'   => true,
					'heading' => 'h3',
				)
			);
		}
		$html .= '</div>';
		$html .= '<p class="bqp-noresult" hidden>Aucun partenaire dans cette catégorie pour le moment.</p>';
	}

	if ( $templates ) {
		$html .= implode( '', $templates );
		$html .= '<dialog class="bqp-modal" aria-label="Fiche du partenaire">'
			. '<div class="bqp-modal__bar">'
			. '<button type="button" class="bqp-modal__nav" data-bqp-prev aria-label="Partenaire précédent"><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M10 3 5 8l5 5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>'
			. '<span class="bqp-modal__pos" data-bqp-pos aria-live="polite"></span>'
			. '<button type="button" class="bqp-modal__nav" data-bqp-next aria-label="Partenaire suivant"><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="m6 3 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>'
			. '<span class="bqp-modal__where" data-bqp-where></span>'
			. '<button type="button" class="bqp-modal__close" data-bqp-close aria-label="Fermer la fiche"><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path d="M3.5 3.5l9 9M12.5 3.5l-9 9" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></button>'
			. '</div>'
			. '<div class="bqp-modal__body" data-bqp-body></div>'
			. '</dialog>';
	}

	return $html . '</section>';
}

/* -------------------------------------------------------------------------
 * 9. Styles du bloc (charte boursequatrepoint.fr)
 * ---------------------------------------------------------------------- */

function bqp_front_css() {
	$p = bqp_palette();

	return '
	.bqp-partners{
		--bqp-bordeaux:' . $p['bordeaux'] . ';
		--bqp-bordeaux-dark:' . $p['bordeaux_dark'] . ';
		--bqp-bordeaux-soft:' . bqp_hex_to_rgba( $p['bordeaux'], 0.06 ) . ';
		--bqp-ink:' . $p['ink'] . ';
		--bqp-muted:' . $p['muted'] . ';
		--bqp-line:' . $p['line'] . ';
		--bqp-soft:' . $p['soft'] . ';
		--bqp-cols:var(--bqp-cols-max,4);
		--bqp-serif:"Cormorant Garamond","Playfair Display",Georgia,"Times New Roman",serif;
		--bqp-sans:"Inter","Montserrat","Lato",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
		box-sizing:border-box;width:100%;margin:0 auto;padding:0;
	}
	.bqp-partners *,.bqp-partners *::before,.bqp-partners *::after{box-sizing:border-box;}

	.bqp-partners__title{
		margin:0 0 14px;text-align:center;
		font-family:var(--bqp-serif);font-weight:600;font-size:clamp(1.85rem,3.4vw,2.65rem);
		line-height:1.15;letter-spacing:.005em;color:var(--bqp-bordeaux-dark);
	}
	.bqp-partners__rule{
		display:block;width:64px;height:2px;margin:0 auto 38px;
		background:linear-gradient(90deg,var(--bqp-bordeaux),var(--bqp-bordeaux-dark));
	}

	/* --- Filtres : entierement bordeaux, compacts, resistants au theme --- */
	.bqp-partners .bqp-filters{
		display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin:0 0 36px;padding:0;
	}
	.bqp-partners .bqp-filters .bqp-filter{
		-webkit-appearance:none;appearance:none;
		display:inline-flex;align-items:center;gap:7px;
		width:auto;min-height:0;margin:0;padding:9px 18px;
		border:1.5px solid var(--bqp-line) !important;border-radius:999px !important;
		background:#fff !important;background-image:none !important;
		color:var(--bqp-ink) !important;
		font-family:var(--bqp-sans);font-size:.70rem;font-weight:700;line-height:1;
		letter-spacing:.055em;text-transform:uppercase;text-decoration:none;text-shadow:none;
		cursor:pointer;box-shadow:none;
		transition:color .25s ease,border-color .25s ease,background-color .25s ease,box-shadow .25s ease,transform .25s ease;
	}
	.bqp-partners .bqp-filters .bqp-filter:hover,
	.bqp-partners .bqp-filters .bqp-filter:focus{
		background:var(--bqp-bordeaux-soft) !important;
		border-color:var(--bqp-bordeaux) !important;
		color:var(--bqp-bordeaux) !important;
		transform:translateY(-1px);
	}
	.bqp-partners .bqp-filters .bqp-filter:focus-visible{
		outline:2px solid var(--bqp-bordeaux);outline-offset:3px;
	}
	.bqp-partners .bqp-filters .bqp-filter.is-active,
	.bqp-partners .bqp-filters .bqp-filter.is-active:hover,
	.bqp-partners .bqp-filters .bqp-filter.is-active:focus{
		background:var(--bqp-bordeaux) !important;
		background-image:linear-gradient(180deg,var(--bqp-bordeaux) 0%,var(--bqp-bordeaux-dark) 100%) !important;
		border-color:transparent !important;
		color:#fff !important;
		box-shadow:0 8px 18px -10px ' . bqp_hex_to_rgba( $p['bordeaux_dark'], 0.75 ) . ';
		transform:none;
	}
	.bqp-partners .bqp-filter__label{display:inline-block;}
	.bqp-partners .bqp-filter__count{
		display:inline-flex;align-items:center;justify-content:center;min-width:17px;height:17px;
		padding:0 5px;border-radius:999px;background:rgba(0,0,0,.06);color:inherit;
		font-size:.62rem;font-weight:700;letter-spacing:0;transition:background-color .25s ease;
	}
	.bqp-partners .bqp-filter:hover .bqp-filter__count{background:' . bqp_hex_to_rgba( $p['bordeaux'], 0.12 ) . ';}
	.bqp-partners .bqp-filter.is-active .bqp-filter__count{background:rgba(255,255,255,.22);}

	/* --- Grille --- */
	.bqp-grid{display:grid;grid-template-columns:repeat(var(--bqp-cols),minmax(0,1fr));gap:22px;align-items:stretch;}
	@media (max-width:1100px){.bqp-partners{--bqp-cols:min(3,var(--bqp-cols-max,3));}}
	@media (max-width:860px){.bqp-partners{--bqp-cols:2;}}
	@media (max-width:620px){
		.bqp-partners{--bqp-cols:1;}
		.bqp-grid{gap:20px;}
		.bqp-card__logo{height:126px;padding:22px;}
		.bqp-partners .bqp-filters{gap:7px;}
		.bqp-partners .bqp-filters .bqp-filter{padding:8px 15px;font-size:.66rem;}
	}

	/* --- Carte --- */
	.bqp-card{
		position:relative;display:flex;flex-direction:column;overflow:hidden;
		background:#fff;border:1px solid var(--bqp-line);border-radius:16px;
		transition:transform .38s cubic-bezier(.2,.7,.3,1),box-shadow .38s ease,border-color .38s ease;
		animation:bqp-in .45s cubic-bezier(.2,.7,.3,1) both;
	}
	.bqp-card::after{
		content:"";position:absolute;inset:0 0 auto 0;height:3px;
		background:var(--bqp-cat);transform:scaleX(0);transform-origin:left;
		transition:transform .42s cubic-bezier(.2,.7,.3,1);
	}
	.bqp-card:hover,.bqp-card:focus-within{
		transform:translateY(-6px);
		border-color:var(--bqp-cat-line,var(--bqp-line));
		box-shadow:0 22px 44px -24px rgba(49,2,12,.5);
	}
	.bqp-card:hover::after,.bqp-card:focus-within::after{transform:scaleX(1);}
	.bqp-card[hidden]{display:none;}

	@keyframes bqp-in{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:none;}}

	/* --- Logo --- */
	.bqp-card__logo{
		display:flex;align-items:center;justify-content:center;
		height:128px;padding:22px;background:#FCFBFB;border-bottom:1px solid var(--bqp-line);
	}
	.bqp-card__img{
		max-width:100%;max-height:90px;width:auto;height:auto;object-fit:contain;
		transition:filter .4s ease,opacity .4s ease,transform .4s ease;
	}
	.bqp-partners--grayscale .bqp-card__img{filter:grayscale(1);opacity:.7;}
	.bqp-partners--grayscale .bqp-card:hover .bqp-card__img{filter:none;opacity:1;transform:scale(1.03);}
	.bqp-card__initials{
		display:flex;align-items:center;justify-content:center;flex:0 0 74px;
		width:74px;height:74px;border-radius:50%;
		background:var(--bqp-cat-soft,rgba(116,4,28,.09));color:var(--bqp-cat);
		font-family:var(--bqp-serif);font-size:1.7rem;font-weight:600;letter-spacing:.02em;
	}

	/* --- Contenu --- */
	.bqp-card__body{display:flex;flex-direction:column;gap:10px;flex:1;padding:22px 20px 20px;}
	.bqp-card__badge{
		align-self:flex-start;max-width:100%;padding:5px 12px;border-radius:999px;
		overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
		background:var(--bqp-cat-soft,rgba(116,4,28,.09));
		border:1px solid var(--bqp-cat-line,rgba(116,4,28,.22));color:var(--bqp-cat);
		font-family:var(--bqp-sans);font-size:.64rem;font-weight:700;line-height:1.5;
		letter-spacing:.09em;text-transform:uppercase;
	}
	.bqp-card__name{
		margin:0;font-family:var(--bqp-serif);font-weight:600;
		font-size:1.3rem;line-height:1.2;color:var(--bqp-bordeaux-dark);
		overflow-wrap:break-word;
	}
	.bqp-card__desc{
		margin:0;flex:1;font-family:var(--bqp-sans);font-size:.93rem;line-height:1.68;color:#5d5d5d;
	}
	.bqp-partners .bqp-card__link{
		display:inline-flex;align-items:center;gap:9px;margin-top:6px;padding-top:16px;
		border-top:1px solid var(--bqp-line);color:var(--bqp-bordeaux) !important;text-decoration:none !important;
		font-family:var(--bqp-sans);font-size:.74rem;font-weight:700;line-height:1;
		letter-spacing:.08em;text-transform:uppercase;transition:color .28s ease;
	}
	.bqp-partners .bqp-card__link:hover,.bqp-partners .bqp-card__link:focus{color:var(--bqp-bordeaux-dark) !important;}
	.bqp-card__link svg{transition:transform .3s cubic-bezier(.2,.7,.3,1);}
	.bqp-card__link:hover svg{transform:translateX(5px);}
	.bqp-card__link:focus-visible{outline:2px solid var(--bqp-bordeaux);outline-offset:4px;border-radius:3px;}

	/* --- Divers --- */
	.bqp-noresult{
		margin:34px 0 0;text-align:center;color:var(--bqp-muted);
		font-family:var(--bqp-sans);font-size:.95rem;font-style:italic;
	}
	.bqp-noresult[hidden]{display:none;}
	.bqp-empty-notice{
		padding:18px 22px;border:1px dashed ' . bqp_hex_to_rgba( $p['bordeaux'], 0.35 ) . ';border-radius:12px;
		background:' . bqp_hex_to_rgba( $p['bordeaux'], 0.04 ) . ';color:' . $p['bordeaux_dark'] . ';
		font-family:"Inter",sans-serif;font-size:.95rem;
	}
	.bqp-partners .screen-reader-text{
		position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;
		clip:rect(0,0,0,0);white-space:nowrap;border:0;
	}

	/* --- Sommaire : un accès direct à chaque bloc --- */
	.bqp-toc{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin:0 0 34px;}
	.bqp-partners .bqp-toc__item{display:inline-flex;align-items:center;gap:9px;padding:8px 14px 8px 8px;border:1px solid var(--bqp-line);border-radius:999px;background:#fff;color:var(--bqp-bordeaux-dark) !important;font-family:var(--bqp-sans);font-size:.8rem;font-weight:600;line-height:1.2;text-decoration:none !important;transition:border-color .2s,background .2s,transform .2s;}
	.bqp-partners .bqp-toc__item:hover{border-color:var(--bqp-cat);background:var(--bqp-cat-soft);color:var(--bqp-cat) !important;transform:translateY(-1px);}
	.bqp-toc__icon{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:var(--bqp-cat-soft);color:var(--bqp-cat);}
	.bqp-toc__count{min-width:20px;padding:1px 7px;border-radius:999px;background:var(--bqp-cat-soft);color:var(--bqp-cat);font-size:.68rem;font-weight:700;text-align:center;}

	/* --- Un bloc par catégorie --- */
	.bqp-group{position:relative;margin:0 0 34px;padding:30px 30px 32px;border:1px solid var(--bqp-cat-line);border-radius:20px;background:linear-gradient(180deg,var(--bqp-cat-soft),rgba(255,255,255,0) 260px),#fff;scroll-margin-top:110px;}
	.bqp-group:last-of-type{margin-bottom:0;}
	.bqp-group::before{content:"";position:absolute;left:30px;top:-1px;width:64px;height:3px;border-radius:0 0 3px 3px;background:var(--bqp-cat);}
	.bqp-group__head{display:flex;align-items:center;gap:18px;margin:0 0 26px;}
	.bqp-group__icon{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:16px;background:var(--bqp-cat);color:#fff;box-shadow:0 14px 26px -16px var(--bqp-cat);}
	.bqp-group__intro{flex:1;min-width:0;}
	.bqp-partners .bqp-group__title{margin:0 !important;font-family:var(--bqp-serif) !important;font-size:clamp(1.6rem,2.8vw,2.1rem) !important;font-weight:600 !important;line-height:1.12 !important;color:var(--bqp-bordeaux-dark) !important;}
	.bqp-group__text{max-width:68ch;margin:6px 0 0;font-family:var(--bqp-sans);font-size:.92rem;line-height:1.6;color:#5d5d5d;}
	.bqp-group__count{flex:0 0 auto;display:flex;flex-direction:column;align-items:flex-end;font-family:var(--bqp-sans);font-size:.64rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--bqp-muted);}
	.bqp-group__count strong{font-family:var(--bqp-serif);font-size:2rem;font-weight:600;line-height:1;letter-spacing:0;color:var(--bqp-cat);}
	.bqp-partners--blocs .bqp-card::after{transform:scaleX(1);opacity:.85;}
	@media (max-width:620px){
		.bqp-group{padding:22px 16px 20px;border-radius:16px;}
		.bqp-group::before{left:16px;}
		.bqp-group__head{flex-wrap:wrap;gap:14px;margin-bottom:20px;}
		.bqp-group__icon{width:46px;height:46px;border-radius:13px;}
		.bqp-group__intro{flex-basis:calc(100% - 60px);}
		.bqp-group__count{flex-direction:row;align-items:baseline;gap:6px;}
		.bqp-group__count strong{font-size:1.3rem;}
		.bqp-toc{flex-wrap:nowrap;justify-content:flex-start;margin:0 0 26px;padding:0 0 4px;overflow-x:auto;scrollbar-width:none;-webkit-mask-image:linear-gradient(90deg,#000 85%,transparent);mask-image:linear-gradient(90deg,#000 85%,transparent);}
		.bqp-toc::-webkit-scrollbar{display:none;}
		.bqp-partners .bqp-toc__item{flex:0 0 auto;white-space:nowrap;}
	}

	/* --- Catégories repliées : une carte par catégorie, à déplier --- */
	.bqp-cats__hint{margin:4px 0 20px;text-align:center;font-family:var(--bqp-sans);font-size:.88rem;color:var(--bqp-muted);}
	.bqp-cats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;align-items:stretch;}
	@media (max-width:1100px){.bqp-cats{grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}}
	@media (max-width:480px){.bqp-cats{grid-template-columns:1fr;gap:10px;}}
	.bqp-partners .bqp-cat,.bqp-partners .bqp-cat:hover,.bqp-partners .bqp-cat:focus{
		position:relative;display:flex !important;flex-direction:column;align-items:stretch;min-width:0;margin:0 !important;padding:0 !important;
		border:1px solid var(--bqp-line) !important;border-radius:18px !important;background:#fff !important;background-image:none !important;box-shadow:0 1px 2px rgba(49,2,12,.05) !important;
		color:var(--bqp-ink) !important;font-family:var(--bqp-sans) !important;font-size:1rem !important;font-weight:400 !important;letter-spacing:0 !important;text-transform:none !important;line-height:1.3 !important;text-align:left !important;white-space:normal !important;
		cursor:pointer;scroll-margin-top:110px;transition:transform .3s cubic-bezier(.2,.7,.3,1),box-shadow .3s,border-color .3s;
	}
	.bqp-partners .bqp-cat:hover{transform:translateY(-4px);border-color:var(--bqp-cat-line) !important;box-shadow:0 28px 50px -32px rgba(49,2,12,.6) !important;}
	.bqp-partners .bqp-cat:focus-visible{outline:3px solid var(--bqp-cat-line) !important;outline-offset:3px;}
	.bqp-cat__band{position:relative;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:20px 20px 18px;overflow:hidden;border-radius:17px 17px 0 0;background:var(--bqp-cat-soft);border-bottom:1px solid var(--bqp-cat-line);}
	.bqp-cat__band::after{content:"";position:absolute;right:-70px;bottom:-96px;width:150px;height:150px;border-radius:50%;border:1px solid var(--bqp-cat-line);box-shadow:0 0 0 22px var(--bqp-cat-soft);pointer-events:none;}
	.bqp-cat__icon{position:relative;z-index:1;display:inline-flex;align-items:center;justify-content:center;width:50px;height:50px;border-radius:15px;background:var(--bqp-cat);color:#fff;box-shadow:0 14px 24px -14px var(--bqp-cat);transition:transform .3s;}
	.bqp-partners .bqp-cat:hover .bqp-cat__icon{transform:scale(1.05) rotate(-3deg);}
	.bqp-cat__count{position:relative;z-index:1;display:flex;flex-direction:column;align-items:flex-end;}
	.bqp-cat__count strong{font-family:var(--bqp-serif);font-size:2.3rem;font-weight:600;line-height:.95;color:var(--bqp-cat);font-variant-numeric:lining-nums;}
	.bqp-cat__count em{margin-top:4px;font-style:normal;font-size:.6rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--bqp-muted);}
	.bqp-cat__body{display:flex;flex-direction:column;flex:1;padding:18px 20px 0;}
	.bqp-cat__eyebrow{font-size:.62rem;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--bqp-cat);}
	.bqp-cat__title{margin:0;font-family:var(--bqp-serif);font-size:1.55rem;font-weight:600;line-height:1.1;color:var(--bqp-bordeaux-dark);overflow-wrap:normal;}
	.bqp-cat__text{margin:10px 0 0;font-size:.84rem;line-height:1.55;color:#626262;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;}
	.bqp-cat__foot{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:18px;padding:13px 16px 13px 20px;border-top:1px solid var(--bqp-line);border-radius:0 0 17px 17px;transition:background .25s,color .25s,border-color .25s;}
	.bqp-cat__cta{white-space:nowrap;font-size:.7rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--bqp-cat);}
	.bqp-cat__hide{display:none;}
	.bqp-cat__chevron{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:var(--bqp-cat-soft);color:var(--bqp-cat);transition:transform .3s,background .25s,color .25s;}
	.bqp-partners .bqp-cat:hover .bqp-cat__chevron{background:var(--bqp-cat);color:#fff;transform:translateY(2px);}
	.bqp-partners .bqp-cat.is-on,.bqp-partners .bqp-cat.is-on:hover,.bqp-partners .bqp-cat.is-on:focus{border-color:var(--bqp-cat) !important;box-shadow:0 0 0 1px var(--bqp-cat),0 28px 50px -32px rgba(49,2,12,.6) !important;transform:none;}
	.bqp-cat.is-on .bqp-cat__foot{background:var(--bqp-cat);border-top-color:var(--bqp-cat);}
	.bqp-cat.is-on .bqp-cat__cta{color:#fff;}
	.bqp-cat.is-on .bqp-cat__see{display:none;}
	.bqp-cat.is-on .bqp-cat__hide{display:inline;}
	.bqp-partners .bqp-cat.is-on .bqp-cat__chevron{background:rgba(255,255,255,.2);color:#fff;transform:rotate(180deg);}
	.bqp-cat.is-on::after{content:"";position:absolute;left:50%;bottom:-8px;width:16px;height:16px;margin-left:-8px;background:var(--bqp-cat);transform:rotate(45deg);border-radius:2px;}
	.bqp-cats .bqp-group{grid-column:1 / -1;margin:6px 0 10px;animation:bqp-in .35s cubic-bezier(.2,.7,.3,1);}
	.bqp-cats .bqp-group[hidden]{display:none;}
	.bqp-cats .bqp-group::before{display:none;}
	.bqp-cats .bqp-group{border-top:3px solid var(--bqp-cat);}
	.bqp-partners .bqp-group__title{outline:none;}
	.bqp-partners .bqp-group__close,.bqp-partners .bqp-group__close:hover,.bqp-partners .bqp-group__close:focus{
		flex:0 0 auto;display:inline-flex !important;align-items:center;justify-content:center;width:42px;height:42px;margin:0 !important;padding:0 !important;
		border:1px solid var(--bqp-line) !important;border-radius:50% !important;background:#fff !important;background-image:none !important;box-shadow:none !important;color:var(--bqp-muted) !important;cursor:pointer;transition:border-color .2s,color .2s;
	}
	.bqp-partners .bqp-group__close:hover{border-color:var(--bqp-cat) !important;color:var(--bqp-cat) !important;}
	.bqp-partners .bqp-group__close:focus-visible{outline:2px solid var(--bqp-cat) !important;outline-offset:2px;}
	@media (max-width:480px){
		.bqp-partners .bqp-cat,.bqp-partners .bqp-cat:hover,.bqp-partners .bqp-cat:focus{display:grid !important;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:0 14px;padding:12px 14px !important;border-radius:14px !important;}
		.bqp-cat__band{display:contents;}
		.bqp-cat__band::after{display:none;}
		.bqp-cat__icon{grid-column:1;grid-row:1;width:44px;height:44px;border-radius:12px;}
		.bqp-cat__body{grid-column:2;grid-row:1;padding:0;}
		.bqp-cat__title{font-size:1.25rem;}
		.bqp-cat__text,.bqp-cat__foot{display:none;}
		.bqp-cat__count{grid-column:3;grid-row:1;}
		.bqp-cat__count strong{font-size:1.6rem;}
		.bqp-partners .bqp-cat.is-on,.bqp-partners .bqp-cat.is-on:hover,.bqp-partners .bqp-cat.is-on:focus{background:var(--bqp-cat-soft) !important;}
		.bqp-cat.is-on::after{bottom:-7px;width:12px;height:12px;margin-left:-6px;}
		.bqp-cats .bqp-group__head{position:relative;padding-right:48px;}
		.bqp-partners .bqp-group__close{position:absolute;top:0;right:0;}
	}

	/* --- Carte cliquable --- */
	.bqp-card__meta{margin:-4px 0 0;font-family:var(--bqp-sans);font-size:.76rem;font-weight:600;color:var(--bqp-cat);}
	.bqp-card.is-clickable{cursor:pointer;}
	.bqp-card.is-clickable .bqp-card__desc{flex:0 0 auto;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
	.bqp-partners .bqp-card__open,.bqp-partners .bqp-card__open:hover,.bqp-partners .bqp-card__open:focus{position:absolute;inset:0;z-index:1;width:100%;height:100%;margin:0 !important;padding:0 !important;border:0 !important;border-radius:16px !important;background:transparent !important;box-shadow:none !important;cursor:pointer;}
	.bqp-partners .bqp-card__open:focus-visible{outline:3px solid var(--bqp-cat) !important;outline-offset:-3px;}
	.bqp-card__foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:auto;padding-top:16px;border-top:1px solid var(--bqp-line);}
	.bqp-card__more{display:inline-flex;align-items:center;gap:9px;font-family:var(--bqp-sans);font-size:.74rem;font-weight:700;line-height:1;letter-spacing:.08em;text-transform:uppercase;color:var(--bqp-cat);}
	.bqp-card__more svg{transition:transform .3s cubic-bezier(.2,.7,.3,1);}
	.bqp-card.is-clickable:hover .bqp-card__more svg{transform:translateX(5px);}
	.bqp-partners .bqp-card__site{position:relative;z-index:2;display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border:1px solid var(--bqp-line);border-radius:999px;background:#fff;color:var(--bqp-ink) !important;font-family:var(--bqp-sans);font-size:.7rem;font-weight:600;line-height:1;text-decoration:none !important;transition:border-color .2s,color .2s;}
	.bqp-partners .bqp-card__site:hover{border-color:var(--bqp-cat);color:var(--bqp-cat) !important;}

	/* --- Fenêtre de fiche --- */
	html.bqp-lock{overflow:hidden;}
	.bqp-modal{
		--bqp-bordeaux:' . $p['bordeaux'] . ';--bqp-bordeaux-dark:' . $p['bordeaux_dark'] . ';--bqp-bordeaux-soft:' . bqp_hex_to_rgba( $p['bordeaux'], 0.06 ) . ';--bqp-ink:' . $p['ink'] . ';--bqp-muted:' . $p['muted'] . ';--bqp-line:' . $p['line'] . ';
		--bqp-serif:"Cormorant Garamond","Playfair Display",Georgia,"Times New Roman",serif;--bqp-sans:"Inter","Montserrat","Lato",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
		width:min(1000px,calc(100vw - 32px));max-width:none;max-height:calc(100vh - 48px);margin:auto;padding:0;
		border:0;border-radius:20px;background:#fff;color:var(--bqp-ink);overflow:hidden;
		box-shadow:0 50px 120px -40px rgba(31,2,12,.7);font-family:var(--bqp-sans);
	}
	.bqp-modal *,.bqp-modal *::before,.bqp-modal *::after{box-sizing:border-box;}
	.bqp-modal[open]{display:flex;flex-direction:column;animation:bqp-modal-in .38s cubic-bezier(.2,.7,.3,1);}
	.bqp-modal::backdrop{background:rgba(31,2,12,.58);-webkit-backdrop-filter:blur(5px);backdrop-filter:blur(5px);}
	@keyframes bqp-modal-in{from{opacity:0;transform:translateY(18px) scale(.985);}to{opacity:1;transform:none;}}
	.bqp-modal__bar{display:flex;align-items:center;gap:8px;padding:12px 14px 12px 18px;border-bottom:1px solid var(--bqp-line);background:#fff;}
	.bqp-modal__pos{min-width:48px;font-size:.72rem;font-weight:700;letter-spacing:.08em;color:var(--bqp-muted);text-align:center;}
	.bqp-modal__where{margin-left:10px;font-size:.66rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--bqp-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
	.bqp-modal .bqp-modal__nav,.bqp-modal .bqp-modal__close,
	.bqp-modal .bqp-modal__nav:hover,.bqp-modal .bqp-modal__close:hover,
	.bqp-modal .bqp-modal__nav:focus,.bqp-modal .bqp-modal__close:focus{
		-webkit-appearance:none;appearance:none;flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;
		width:40px;height:40px;min-height:0;margin:0 !important;padding:0 !important;
		border:1px solid var(--bqp-line) !important;border-radius:50% !important;
		background:#fff !important;background-image:none !important;color:var(--bqp-bordeaux-dark) !important;
		box-shadow:none !important;cursor:pointer;transition:background-color .2s,border-color .2s,color .2s;
	}
	.bqp-modal .bqp-modal__nav:hover,.bqp-modal .bqp-modal__close:hover{background:var(--bqp-bordeaux-soft) !important;border-color:var(--bqp-bordeaux) !important;color:var(--bqp-bordeaux) !important;}
	.bqp-modal .bqp-modal__nav[hidden]{display:none !important;}
	.bqp-modal .bqp-modal__bar .bqp-modal__close.bqp-modal__close{margin-left:auto !important;}
	.bqp-modal button:focus-visible{outline:2px solid var(--bqp-bordeaux) !important;outline-offset:2px;}
	.bqp-modal__body{flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;}

	.bqp-profile{display:grid;grid-template-columns:310px minmax(0,1fr);min-height:100%;}
	.bqp-profile__side{display:flex;flex-direction:column;gap:20px;padding:30px;background:var(--bqp-cat-soft);border-right:1px solid var(--bqp-cat-line);}
	.bqp-profile__logo{display:flex;align-items:center;justify-content:center;aspect-ratio:4 / 3;padding:28px;border:1px solid var(--bqp-cat-line);border-radius:14px;background:#fff;box-shadow:0 24px 40px -30px rgba(0,0,0,.45);}
	.bqp-modal .bqp-profile__logo img{display:block;max-width:100% !important;max-height:150px !important;width:auto !important;height:auto !important;object-fit:contain;}
	.bqp-profile__initials{font-family:var(--bqp-serif);font-size:3.4rem;font-weight:600;color:var(--bqp-cat);}
	.bqp-profile__facts{margin:0;}
	.bqp-profile__facts div{padding:10px 0;border-bottom:1px solid var(--bqp-cat-line);}
	.bqp-profile__facts div:last-child{border-bottom:0;}
	.bqp-profile__facts dt{margin:0 0 2px;font-size:.6rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--bqp-muted);}
	.bqp-profile__facts dd{margin:0;font-size:.9rem;font-weight:600;line-height:1.4;color:var(--bqp-ink);overflow-wrap:anywhere;}
	.bqp-profile__actions{display:flex;flex-direction:column;gap:8px;}
	.bqp-modal .bqp-btn,.bqp-modal .bqp-btn:hover,.bqp-modal .bqp-btn:focus{
		display:flex;align-items:center;justify-content:center;gap:9px;padding:13px 16px;border:1px solid transparent;border-radius:8px;
		background:var(--bqp-cat);background-image:linear-gradient(180deg,rgba(255,255,255,.08),rgba(0,0,0,.2));
		color:#fff !important;font-family:var(--bqp-sans);font-size:.72rem;font-weight:700;letter-spacing:.09em;line-height:1.2;text-transform:uppercase;text-decoration:none !important;
		box-shadow:0 12px 22px -16px rgba(0,0,0,.8);transition:filter .2s,transform .2s;
	}
	.bqp-modal .bqp-btn:hover{filter:brightness(1.12);transform:translateY(-1px);}
	.bqp-modal .bqp-btn--ghost,.bqp-modal .bqp-btn--ghost:hover,.bqp-modal .bqp-btn--ghost:focus{background:#fff;background-image:none;border-color:var(--bqp-cat-line);color:var(--bqp-cat) !important;box-shadow:none;}
	.bqp-modal .bqp-btn--ghost:hover{border-color:var(--bqp-cat);filter:none;}

	.bqp-profile__main{padding:34px 40px 40px;}
	.bqp-profile__main .bqp-card__badge{display:inline-block;}
	.bqp-modal .bqp-profile__name{margin:12px 0 0 !important;font-family:var(--bqp-serif) !important;font-size:clamp(1.9rem,3vw,2.6rem) !important;font-weight:600 !important;line-height:1.08 !important;color:var(--bqp-bordeaux-dark) !important;}
	.bqp-profile__lead{margin:14px 0 0;font-family:var(--bqp-serif);font-size:1.25rem;line-height:1.5;color:#3b3533;}
	.bqp-profile__section{margin:30px 0 0;font-size:.96rem;line-height:1.75;color:#4d4d4d;}
	.bqp-modal .bqp-profile__section h3{display:flex;align-items:center;gap:12px;margin:0 0 12px !important;font-family:var(--bqp-sans) !important;font-size:.66rem !important;font-weight:700 !important;letter-spacing:.13em !important;text-transform:uppercase;line-height:1.4 !important;color:var(--bqp-cat) !important;}
	.bqp-profile__section h3::after{content:"";flex:1;height:1px;background:var(--bqp-line);}
	.bqp-profile__section p{margin:0 0 .9em;}
	.bqp-profile__section p:last-child{margin-bottom:0;}
	.bqp-profile__tags{display:flex;flex-wrap:wrap;gap:8px;margin:0;padding:0;list-style:none;}
	.bqp-profile__tags li{margin:0;padding:6px 13px;border:1px solid var(--bqp-cat-line);border-radius:999px;background:var(--bqp-cat-soft);font-size:.82rem;font-weight:600;color:var(--bqp-cat);}
	.bqp-profile__section--quote{padding:4px 0 4px 20px;border-left:3px solid var(--bqp-cat);}
	.bqp-profile__section--quote p{font-family:var(--bqp-serif);font-size:1.22rem;line-height:1.55;color:var(--bqp-bordeaux-dark);}
	.bqp-profile__rich h2,.bqp-profile__rich h4{margin:1.2em 0 .4em;font-family:var(--bqp-serif);font-size:1.25rem;font-weight:600;color:var(--bqp-bordeaux-dark);}
	.bqp-profile__rich ul,.bqp-profile__rich ol{margin:0 0 1em;padding-left:1.25em;}
	.bqp-profile__rich li{margin:.35em 0;}
	.bqp-profile__rich li::marker{color:var(--bqp-cat);}
	.bqp-profile__rich strong{color:var(--bqp-ink);}
	.bqp-modal .bqp-profile__rich a{color:var(--bqp-bordeaux) !important;}

	@media (max-width:760px){
		.bqp-modal{width:100vw;height:100dvh;max-height:100dvh;border-radius:0;}
		.bqp-modal__where{display:none;}
		.bqp-profile{grid-template-columns:1fr;}
		.bqp-profile__side{display:grid;grid-template-columns:130px minmax(0,1fr);align-items:start;gap:16px;padding:20px;border-right:0;border-bottom:1px solid var(--bqp-cat-line);}
		.bqp-profile__logo{padding:14px;border-radius:10px;}
		.bqp-profile__initials{font-size:2.2rem;}
		.bqp-profile__actions{grid-column:1 / -1;flex-direction:row;flex-wrap:wrap;}
		.bqp-profile__actions .bqp-btn{flex:1 1 160px;}
		.bqp-profile__main{padding:24px 20px 32px;}
	}
	@media (prefers-reduced-motion:reduce){.bqp-modal[open]{animation:none;}.bqp-card__more svg{transition:none;}}

	@media (prefers-reduced-motion:reduce){
		.bqp-card,.bqp-card::after,.bqp-card__img,.bqp-card__link svg,.bqp-partners .bqp-filter{transition:none;animation:none;}
		.bqp-card:hover{transform:none;}
	}
	';
}

/* -------------------------------------------------------------------------
 * 10. Filtrage cote navigateur
 * ---------------------------------------------------------------------- */

function bqp_front_js() {
	return <<<'JS'
(function(){
	function each(list, fn){ Array.prototype.forEach.call(list, fn); }

	/* Filtres de l'affichage en grille unique */
	function initFilters(root){
		var filters = root.querySelectorAll('.bqp-filter');
		var cards   = root.querySelectorAll('.bqp-card');
		var empty   = root.querySelector('.bqp-noresult');

		if (!filters.length) { return; }

		function apply(value){
			var visible = 0;

			each(cards, function(card){
				var cats  = (card.getAttribute('data-cats') || '').split(' ');
				var match = ('*' === value) || (cats.indexOf(value) !== -1);

				if (match) {
					card.hidden = false;
					card.style.animation = 'none';
					void card.offsetWidth;
					card.style.animation = '';
					card.style.animationDelay = (visible * 45) + 'ms';
					visible++;
				} else {
					card.hidden = true;
				}
			});

			if (empty) { empty.hidden = (visible > 0); }
		}

		each(filters, function(button){
			button.addEventListener('click', function(){
				each(filters, function(other){
					other.classList.remove('is-active');
					other.setAttribute('aria-pressed', 'false');
				});

				button.classList.add('is-active');
				button.setAttribute('aria-pressed', 'true');

				apply(button.getAttribute('data-filter'));
			});
		});
	}

	/* Sommaire : défilement doux jusqu'au bloc */
	function initToc(root){
		root.addEventListener('click', function(e){
			var a = e.target.closest('.bqp-toc__item');
			if (!a) { return; }
			var target = document.getElementById(a.getAttribute('href').slice(1));
			if (!target) { return; }
			e.preventDefault();
			target.scrollIntoView({ behavior: 'smooth', block: 'start' });
			if (window.history && history.replaceState) { history.replaceState(null, '', a.getAttribute('href')); }
		});
	}

	/* Catégories repliées : une carte ouvre ses partenaires sous sa rangée */
	function initFold(root){
		var grid = root.querySelector('[data-bqp-cats]');
		if (!grid) { return; }

		function cardFor(panel){ return grid.querySelector('[aria-controls="' + panel.id + '"]'); }

		function place(card, panel){
			var top = card.offsetTop, last = card;
			each(grid.querySelectorAll('.bqp-cat'), function(c){ if (c.offsetTop === top) { last = c; } });
			if (last.nextElementSibling !== panel) { grid.insertBefore(panel, last.nextElementSibling); }
		}

		function toggle(card, focus, force){
			var panel = document.getElementById(card.getAttribute('aria-controls'));
			var open  = (undefined !== force) ? force : 'true' !== card.getAttribute('aria-expanded');
			each(grid.querySelectorAll('.bqp-cat'), function(c){ c.setAttribute('aria-expanded', 'false'); c.classList.remove('is-on'); });
			each(grid.querySelectorAll('.bqp-group'), function(p){ p.hidden = true; });
			if (!open || !panel) { return; }
			card.setAttribute('aria-expanded', 'true');
			card.classList.add('is-on');
			place(card, panel);
			panel.hidden = false;
			if (!focus) { return; }
			var title = panel.querySelector('.bqp-group__title');
			if (title) { title.focus({ preventScroll: true }); }
			var y = card.getBoundingClientRect().top;
			if (y < 0 || y > window.innerHeight * 0.45) { card.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
		}

		// Ouverture d'une catégorie depuis la fiche d'un partenaire (lien direct).
		root.bqpOpenGroup = function(panel){ var c = cardFor(panel); if (c) { toggle(c, false, true); } };

		grid.addEventListener('click', function(e){
			var card = e.target.closest('[data-bqp-cat]');
			if (card) { toggle(card, true); return; }
			var close = e.target.closest('[data-bqp-fold-close]');
			if (close) {
				var c = cardFor(close.closest('.bqp-group'));
				if (c) { toggle(c, false, false); c.focus(); }
			}
		});

		grid.addEventListener('keydown', function(e){
			if ('Escape' !== e.key || document.querySelector('.bqp-modal[open]')) { return; }
			var panel = e.target.closest('.bqp-group');
			var c = panel ? cardFor(panel) : null;
			if (c) { toggle(c, false, false); c.focus(); }
		});

		var timer = null;
		function replace(){
			var card = grid.querySelector('.bqp-cat[aria-expanded="true"]');
			var panel = card ? document.getElementById(card.getAttribute('aria-controls')) : null;
			if (!panel) { return; }
			panel.hidden = true;
			place(card, panel);
			panel.hidden = false;
		}
		window.addEventListener('resize', function(){ clearTimeout(timer); timer = setTimeout(replace, 150); });
		replace();

		// Lien direct vers une catégorie : /partenaires/#partenaires-academiques.
		function openFromHash(){
			var id = decodeURIComponent((location.hash || '').slice(1));
			var panel = id ? document.getElementById(id) : null;
			if (!panel || !grid.contains(panel) || !panel.classList.contains('bqp-group')) { return; }
			var c = cardFor(panel);
			if (c) { toggle(c, false, true); c.scrollIntoView({ block: 'start' }); }
		}
		openFromHash();
		window.addEventListener('hashchange', openFromHash);
	}

	/* Fiche du partenaire, dans une fenêtre */
	function initModal(root){
		var dialog = root.querySelector('.bqp-modal');
		if (!dialog) { return; }

		var body    = dialog.querySelector('[data-bqp-body]');
		var pos     = dialog.querySelector('[data-bqp-pos]');
		var where   = dialog.querySelector('[data-bqp-where]');
		var prev    = dialog.querySelector('[data-bqp-prev]');
		var next    = dialog.querySelector('[data-bqp-next]');
		var current = null;

		// Les flèches parcourent le bloc du partenaire, sans passer à un autre.
		function siblings(card){
			var scope = card.closest('.bqp-group') || root;
			return Array.prototype.filter.call(scope.querySelectorAll('.bqp-card.is-clickable'), function(c){ return !c.hidden; });
		}

		function show(card){
			var button = card.querySelector('[data-bqp-open]');
			var tpl    = button ? document.getElementById(button.getAttribute('data-bqp-open')) : null;
			if (!tpl) { return; }

			body.innerHTML = '';
			body.appendChild(tpl.content.cloneNode(true));
			body.scrollTop = 0;

			var profile = body.querySelector('.bqp-profile');
			if (profile) { dialog.setAttribute('aria-labelledby', profile.getAttribute('data-title')); dialog.removeAttribute('aria-label'); }

			current = card;
			var list = siblings(card);
			var i    = list.indexOf(card);
			var many = list.length > 1 && i !== -1;
			prev.hidden = next.hidden = !many;
			pos.textContent = many ? (i + 1) + ' / ' + list.length : '';

			var group = card.closest('.bqp-group');
			var title = group ? group.querySelector('.bqp-group__title') : null;
			where.textContent = title ? title.textContent : '';

			if (!dialog.open) {
				if (dialog.showModal) { dialog.showModal(); } else { dialog.setAttribute('open', ''); }
				document.documentElement.classList.add('bqp-lock');
			}

			var slug = card.getAttribute('data-slug');
			if (slug && window.history && history.replaceState) { history.replaceState(null, '', '#' + slug); }
			var close = dialog.querySelector('[data-bqp-close]');
			if (close) { close.focus({ preventScroll: true }); }
		}

		function step(delta){
			var list = siblings(current);
			var i    = list.indexOf(current);
			if (list.length < 2 || i === -1) { return; }
			show(list[(i + delta + list.length) % list.length]);
		}

		function closeModal(){
			if (dialog.close) { dialog.close(); } else { dialog.removeAttribute('open'); onClose(); }
		}

		function onClose(){
			document.documentElement.classList.remove('bqp-lock');
			if (current && window.history && history.replaceState && location.hash === '#' + current.getAttribute('data-slug')) {
				history.replaceState(null, '', location.pathname + location.search);
			}
			var button = current ? current.querySelector('[data-bqp-open]') : null;
			if (button) { button.focus({ preventScroll: true }); }
			current = null;
		}

		dialog.addEventListener('close', onClose);

		root.addEventListener('click', function(e){
			var button = e.target.closest('[data-bqp-open]');
			if (button && root.contains(button) && !dialog.contains(button)) { show(button.closest('.bqp-card')); }
		});

		dialog.addEventListener('click', function(e){
			if (e.target === dialog) { closeModal(); return; }
			if (e.target.closest('[data-bqp-close]')) { closeModal(); }
			else if (e.target.closest('[data-bqp-prev]')) { step(-1); }
			else if (e.target.closest('[data-bqp-next]')) { step(1); }
		});

		dialog.addEventListener('keydown', function(e){
			if (e.target.closest('a, input, textarea')) { return; }
			if ('ArrowLeft' === e.key) { step(-1); e.preventDefault(); }
			if ('ArrowRight' === e.key) { step(1); e.preventDefault(); }
		});

		// Lien direct vers un partenaire : /partenaires/#nom-du-partenaire.
		function openFromHash(){
			var hash = decodeURIComponent((location.hash || '').slice(1));
			if (!hash || (current && current.getAttribute('data-slug') === hash)) { return; }
			var target = root.querySelector('.bqp-card.is-clickable[data-slug="' + hash.replace(/"/g, '') + '"]');
			if (!target) { return; }
			var group = target.closest('.bqp-group');
			if (group && group.hidden && root.bqpOpenGroup) { root.bqpOpenGroup(group); }
			if (target.hidden) {
				var cats = (target.getAttribute('data-cats') || '').split(' ');
				var tab  = Array.prototype.filter.call(root.querySelectorAll('.bqp-filter'), function(b){ return cats.indexOf(b.getAttribute('data-filter')) !== -1; })[0];
				if (tab) { tab.click(); }
			}
			target.scrollIntoView({ block: 'center' });
			show(target);
		}
		openFromHash();
		window.addEventListener('hashchange', openFromHash);
	}

	function boot(){
		each(document.querySelectorAll('.bqp-partners'), function(root){
			initFilters(root);
			initToc(root);
			initFold(root);
			initModal(root);
		});
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
JS;
}

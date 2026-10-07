<?php
/**
 * Plugin Name:       BQP Gouvernance
 * Description:       Gestion et affichage des membres de la gouvernance avec le shortcode [bqp_gouvernance], et fiche détaillée de chaque membre (profession, rôle, parcours, CV PDF).
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * License:           GPL-2.0-or-later
 */

/**
 * BQP Gouvernance — gestion et affichage des membres de la gouvernance.
 *
 * Compatible extension classique ET Code Snippets (aucune balise de fermeture
 * PHP, aucun hook d'activation : les catégories se créent toutes seules).
 *
 * Shortcode : [bqp_gouvernance]
 *
 * Un clic sur un membre ouvre sa fiche dans une fenêtre : profession, rôle
 * dans l'association, parcours (CV en texte) et CV au format PDF.
 *
 * Version : 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BQG_VERSION' ) ) {
	define( 'BQG_VERSION', '1.2.0' );
}
if ( ! defined( 'BQG_CPT' ) ) {
	define( 'BQG_CPT', 'bqg_membre' );
}
if ( ! defined( 'BQG_TAX' ) ) {
	define( 'BQG_TAX', 'bqg_membre_cat' );
}

/* -------------------------------------------------------------------------
 * 1. Palette et helpers
 * ---------------------------------------------------------------------- */

/**
 * Palette officielle du site (relevée sur les réglages Elementor).
 */
function bqg_palette() {
	return array(
		'bordeaux'      => '#74041C',
		'bordeaux_dark' => '#31020C',
		'navy'          => '#001756',
		'orange'        => '#C75A18',
		'ink'           => '#424242',
		'muted'         => '#888888',
		'line'          => '#E3E3E3',
		'soft'          => '#F5F5F5',
	);
}

/**
 * Catégories créées automatiquement : slug => [nom, couleur].
 */
function bqg_default_terms() {
	$p = bqg_palette();

	return array(
		'bureau'              => array( 'Bureau', $p['bordeaux'] ),
		'conseil-scientifique' => array( 'Conseil scientifique', $p['navy'] ),
	);
}

/**
 * Convertit un hexadécimal en rgba() pour les fonds translucides.
 */
function bqg_hex_to_rgba( $hex, $alpha = 1 ) {
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
function bqg_term_color( $term_id ) {
	$color = get_term_meta( (int) $term_id, 'bqg_color', true );

	if ( $color && preg_match( '/^#[0-9A-Fa-f]{6}$/', $color ) ) {
		return $color;
	}

	$term     = get_term( (int) $term_id, BQG_TAX );
	$defaults = bqg_default_terms();

	if ( $term && ! is_wp_error( $term ) && isset( $defaults[ $term->slug ] ) ) {
		return $defaults[ $term->slug ][1];
	}

	$palette = bqg_palette();

	return $palette['bordeaux'];
}

/**
 * Initiales utilisées quand aucune photo n'est renseignée.
 */
function bqg_initials( $name ) {
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
 * 2. Custom post type, taxonomie et tailles d'image
 * ---------------------------------------------------------------------- */

add_action( 'init', 'bqg_register_content' );
function bqg_register_content() {

	register_post_type(
		BQG_CPT,
		array(
			'labels'              => array(
				'name'               => 'Gouvernance',
				'singular_name'      => 'Membre',
				'menu_name'          => 'Gouvernance',
				'add_new'            => 'Ajouter un membre',
				'add_new_item'       => 'Ajouter un membre',
				'edit_item'          => 'Modifier le membre',
				'new_item'           => 'Nouveau membre',
				'view_item'          => 'Voir le membre',
				'search_items'       => 'Rechercher un membre',
				'not_found'          => 'Aucun membre pour le moment.',
				'not_found_in_trash' => 'Aucun membre dans la corbeille.',
				'all_items'          => 'Tous les membres',
				'item_published'     => 'Membre publié.',
				'item_updated'       => 'Membre mis à jour.',
			),
			// Non public : un membre n'a pas de page dédiée, ce qui évite
			// des pages trop légères dans l'index Google.
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_position'       => 27,
			'menu_icon'           => 'dashicons-groups',
			'supports'            => array( 'title', 'page-attributes' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);

	register_taxonomy(
		BQG_TAX,
		BQG_CPT,
		array(
			'labels'             => array(
				'name'          => 'Instances',
				'singular_name' => 'Instance',
				'menu_name'     => 'Instances',
				'all_items'     => 'Toutes les instances',
				'edit_item'     => 'Modifier l\'instance',
				'add_new_item'  => 'Ajouter une instance',
				'search_items'  => 'Rechercher une instance',
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

	// Recadrage automatique des portraits : toutes les photos sortent
	// exactement au même format, quel que soit le fichier d'origine.
	// Le cadrage est centré horizontalement et calé en haut, pour ne
	// jamais couper le visage.
	add_image_size( 'bqg_portrait', 520, 650, array( 'center', 'top' ) );
	add_image_size( 'bqg_carre', 560, 560, array( 'center', 'top' ) );
}

/**
 * Création des instances par défaut, une seule fois.
 * Remplace register_activation_hook, inutilisable depuis Code Snippets.
 */
add_action( 'admin_init', 'bqg_maybe_seed_terms' );
function bqg_maybe_seed_terms() {
	if ( get_option( 'bqg_terms_seeded' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	foreach ( bqg_default_terms() as $slug => $data ) {
		if ( term_exists( $slug, BQG_TAX ) ) {
			continue;
		}

		$term = wp_insert_term( $data[0], BQG_TAX, array( 'slug' => $slug ) );

		if ( ! is_wp_error( $term ) ) {
			add_term_meta( $term['term_id'], 'bqg_color', $data[1], true );
		}
	}

	update_option( 'bqg_terms_seeded', BQG_VERSION, false );
}

/**
 * Libellé du champ titre plus parlant.
 */
add_filter( 'enter_title_here', 'bqg_title_placeholder', 10, 2 );
function bqg_title_placeholder( $text, $post ) {
	if ( $post && BQG_CPT === $post->post_type ) {
		return 'Prénom et nom';
	}

	return $text;
}

/* -------------------------------------------------------------------------
 * 3. Champ couleur sur les instances
 * ---------------------------------------------------------------------- */

add_action( BQG_TAX . '_add_form_fields', 'bqg_term_color_add_field' );
function bqg_term_color_add_field() {
	$palette = bqg_palette();

	echo '<div class="form-field">'
		. '<label for="bqg_color">Couleur de l\'étiquette</label>'
		. '<input type="color" name="bqg_color" id="bqg_color" value="' . esc_attr( $palette['bordeaux'] ) . '" />'
		. '<p>Couleur du badge affiché sur les cartes des membres.</p>'
		. '</div>';

	wp_nonce_field( 'bqg_term_color', 'bqg_term_color_nonce' );
}

add_action( BQG_TAX . '_edit_form_fields', 'bqg_term_color_edit_field' );
function bqg_term_color_edit_field( $term ) {
	echo '<tr class="form-field">'
		. '<th scope="row"><label for="bqg_color">Couleur de l\'étiquette</label></th>'
		. '<td>'
		. '<input type="color" name="bqg_color" id="bqg_color" value="' . esc_attr( bqg_term_color( $term->term_id ) ) . '" />'
		. '<p class="description">Couleur du badge affiché sur les cartes des membres.</p>'
		. '</td></tr>';

	wp_nonce_field( 'bqg_term_color', 'bqg_term_color_nonce' );
}

add_action( 'created_' . BQG_TAX, 'bqg_save_term_color' );
add_action( 'edited_' . BQG_TAX, 'bqg_save_term_color' );
function bqg_save_term_color( $term_id ) {
	if ( ! isset( $_POST['bqg_term_color_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bqg_term_color_nonce'] ) ), 'bqg_term_color' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	if ( isset( $_POST['bqg_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['bqg_color'] ) );
		if ( $color ) {
			update_term_meta( $term_id, 'bqg_color', $color );
		}
	}
}

/* -------------------------------------------------------------------------
 * 4. Fiche membre : photo, fonction, description
 * ---------------------------------------------------------------------- */

add_action( 'add_meta_boxes', 'bqg_add_meta_boxes' );
function bqg_add_meta_boxes() {
	add_meta_box(
		'bqg_membre_details',
		'Fiche du membre',
		'bqg_render_meta_box',
		BQG_CPT,
		'normal',
		'high'
	);
}

function bqg_render_meta_box( $post ) {
	wp_nonce_field( 'bqg_save_membre', 'bqg_membre_nonce' );

	$photo_id = (int) get_post_meta( $post->ID, '_bqg_photo_id', true );
	$role     = (string) get_post_meta( $post->ID, '_bqg_fonction', true );
	$desc     = (string) get_post_meta( $post->ID, '_bqg_description', true );
	$photo    = $photo_id ? wp_get_attachment_image_src( $photo_id, 'bqg_portrait' ) : false;
	$src      = $photo ? $photo[0] : '';
	$count    = function_exists( 'mb_strlen' ) ? mb_strlen( $desc ) : strlen( $desc );

	$html  = '<div class="bqg-admin"><div class="bqg-admin__grid">';

	/* Colonne photo */
	$html .= '<div class="bqg-admin__col bqg-admin__col--photo">';
	$html .= '<span class="bqg-admin__label">Photo de profil</span>';
	$html .= '<div class="bqg-admin__photo-box' . ( $photo ? ' has-photo' : '' ) . '" id="bqg-photo-box">';
	$html .= '<img src="' . esc_url( $src ) . '" alt="" id="bqg-photo-preview"' . ( $photo ? '' : ' hidden' ) . ' />';
	$html .= '<div class="bqg-admin__photo-empty" id="bqg-photo-empty"' . ( $photo ? ' hidden' : '' ) . '>';
	$html .= '<span class="dashicons dashicons-admin-users"></span><span>Aucune photo</span>';
	$html .= '</div></div>';
	$html .= '<input type="hidden" name="bqg_photo_id" id="bqg-photo-id" value="' . esc_attr( $photo_id ) . '" />';
	$html .= '<div class="bqg-admin__photo-actions">';
	$html .= '<button type="button" class="button button-primary" id="bqg-photo-select">' . ( $photo ? 'Remplacer' : 'Choisir une photo' ) . '</button>';
	$html .= '<button type="button" class="button-link bqg-admin__remove" id="bqg-photo-remove"' . ( $photo ? '' : ' hidden' ) . '>Retirer</button>';
	$html .= '</div>';
	$html .= '<p class="bqg-admin__hint">L\'aperçu ci-dessus montre le recadrage réel. La photo est recadrée automatiquement au format portrait, cadrée sur le haut pour ne jamais couper le visage. N\'importe quelle taille convient, à partir de 520&nbsp;&times;&nbsp;650&nbsp;px.</p>';
	$html .= '</div>';

	/* Colonne champs */
	$html .= '<div class="bqg-admin__col">';

	$html .= '<p class="bqg-admin__field">';
	$html .= '<label class="bqg-admin__label" for="bqg_fonction">Fonction</label>';
	$html .= '<input type="text" class="bqg-admin__input" name="bqg_fonction" id="bqg_fonction" value="' . esc_attr( $role ) . '" maxlength="120" placeholder="Présidente du conseil scientifique" />';
	$html .= '<span class="bqg-admin__hint">Le titre exact au sein de l\'institution. Affiché sous le nom, en bordeaux.</span>';
	$html .= '</p>';

	$html .= '<p class="bqg-admin__field">';
	$html .= '<label class="bqg-admin__label" for="bqg_description">Description</label>';
	$html .= '<textarea class="bqg-admin__input bqg-admin__textarea" name="bqg_description" id="bqg_description" rows="7" maxlength="1200" placeholder="Parcours, travaux, rôle au sein de l\'institution. Les retours à la ligne sont conservés.">' . esc_textarea( $desc ) . '</textarea>';
	$html .= '<span class="bqg-admin__hint"><span id="bqg-count">' . esc_html( $count ) . '</span> caractères. Entre 200 et 500 pour des cartes bien équilibrées.</span>';
	$html .= '</p>';

	$html .= '<p class="bqg-admin__field">';
	$html .= '<span class="bqg-admin__label">Instance</span>';
	$html .= '<span class="bqg-admin__hint">Bureau ou Conseil scientifique, à cocher dans l\'encadré <strong>Instances</strong>, à droite de cet écran. C\'est ce qui alimente les filtres du bloc.</span>';
	$html .= '</p>';

	$html .= '</div></div>';

	/* Fiche détaillée, affichée dans la fenêtre au clic sur le membre */
	$profession = (string) get_post_meta( $post->ID, '_bqg_profession', true );
	$engagement = (string) get_post_meta( $post->ID, '_bqg_engagement', true );
	$cv_text    = (string) get_post_meta( $post->ID, '_bqg_cv_texte', true );
	$cv_id      = (int) get_post_meta( $post->ID, '_bqg_cv_id', true );
	$link       = (string) get_post_meta( $post->ID, '_bqg_lien', true );
	$cv_name    = $cv_id ? basename( (string) get_attached_file( $cv_id ) ) : '';

	$html .= '<div class="bqg-admin__profile">';
	$html .= '<div class="bqg-admin__profile-head"><span class="dashicons dashicons-id-alt"></span><div><strong>Fiche détaillée</strong><span>Affichée dans une fenêtre quand un visiteur clique sur le membre. Tous les champs sont facultatifs : seuls ceux remplis apparaissent.</span></div></div>';

	$html .= '<div class="bqg-admin__cols">';
	$html .= '<p class="bqg-admin__field">';
	$html .= '<label class="bqg-admin__label" for="bqg_profession">Profession</label>';
	$html .= '<input type="text" class="bqg-admin__input" name="bqg_profession" id="bqg_profession" value="' . esc_attr( $profession ) . '" maxlength="160" placeholder="Économiste, professeure associée à Sciences Po" />';
	$html .= '<span class="bqg-admin__hint">Son métier, en dehors de l\'association.</span>';
	$html .= '</p>';

	$html .= '<p class="bqg-admin__field">';
	$html .= '<label class="bqg-admin__label" for="bqg_lien">Page personnelle ou LinkedIn</label>';
	$html .= '<input type="url" class="bqg-admin__input" name="bqg_lien" id="bqg_lien" value="' . esc_attr( $link ) . '" placeholder="https://" />';
	$html .= '<span class="bqg-admin__hint">Facultatif. Ouvert dans un nouvel onglet.</span>';
	$html .= '</p>';
	$html .= '</div>';

	$html .= '<p class="bqg-admin__field">';
	$html .= '<label class="bqg-admin__label" for="bqg_engagement">Son rôle dans l\'association</label>';
	$html .= '<textarea class="bqg-admin__input bqg-admin__textarea" name="bqg_engagement" id="bqg_engagement" rows="4" maxlength="1500" placeholder="Pourquoi elle a rejoint la Bourse, ce qu\'elle y apporte, ses missions.">' . esc_textarea( $engagement ) . '</textarea>';
	$html .= '</p>';

	$html .= '<div class="bqg-admin__field"><span class="bqg-admin__label">Parcours (CV en texte)</span>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	wp_editor(
		$cv_text,
		'bqg_cv_texte',
		array(
			'textarea_name' => 'bqg_cv_texte',
			'textarea_rows' => 9,
			'media_buttons' => false,
			'teeny'         => true,
			'quicktags'     => array( 'buttons' => 'strong,em,ul,ol,li,link' ),
			'tinymce'       => array( 'toolbar1' => 'formatselect,bold,italic,bullist,numlist,link,unlink,undo,redo' ),
		)
	);

	$html  = '<span class="bqg-admin__hint">Formations, postes, publications. Les listes à puces rendent le parcours plus lisible.</span></div>';

	$html .= '<div class="bqg-admin__field"><span class="bqg-admin__label">CV au format PDF</span>';
	$html .= '<div class="bqg-admin__file" id="bqg-cv-box">';
	$html .= '<span class="dashicons dashicons-media-document"></span>';
	$html .= '<span class="bqg-admin__file-name" id="bqg-cv-name">' . ( $cv_name ? esc_html( $cv_name ) : 'Aucun fichier' ) . '</span>';
	$html .= '<input type="hidden" name="bqg_cv_id" id="bqg-cv-id" value="' . esc_attr( $cv_id ? $cv_id : '' ) . '" />';
	$html .= '<button type="button" class="button" id="bqg-cv-select">' . ( $cv_id ? 'Remplacer' : 'Choisir un PDF' ) . '</button>';
	$html .= '<button type="button" class="button-link bqg-admin__remove" id="bqg-cv-remove"' . ( $cv_id ? '' : ' hidden' ) . '>Retirer</button>';
	$html .= '</div>';
	$html .= '<span class="bqg-admin__hint">Les visiteurs pourront l\'ouvrir ou le télécharger. Un CV contient des données personnelles : publiez-le avec l\'accord du membre.</span></div>';
	$html .= '</div>';

	$html .= '<div class="bqg-admin__shortcode">';
	$html .= '<span class="dashicons dashicons-shortcode"></span>';
	$html .= '<span>Pour afficher le bloc sur une page :</span>';
	$html .= '<code>[bqp_gouvernance]</code>';
	$html .= '</div></div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( 'save_post_' . BQG_CPT, 'bqg_save_membre', 10, 2 );
function bqg_save_membre( $post_id, $post ) {
	if ( ! isset( $_POST['bqg_membre_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bqg_membre_nonce'] ) ), 'bqg_save_membre' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$photo_id = isset( $_POST['bqg_photo_id'] ) ? absint( $_POST['bqg_photo_id'] ) : 0;
	if ( $photo_id ) {
		update_post_meta( $post_id, '_bqg_photo_id', $photo_id );
	} else {
		delete_post_meta( $post_id, '_bqg_photo_id' );
	}

	$role = isset( $_POST['bqg_fonction'] ) ? sanitize_text_field( wp_unslash( $_POST['bqg_fonction'] ) ) : '';
	if ( '' !== $role ) {
		update_post_meta( $post_id, '_bqg_fonction', $role );
	} else {
		delete_post_meta( $post_id, '_bqg_fonction' );
	}

	$desc = isset( $_POST['bqg_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bqg_description'] ) ) : '';
	if ( '' !== $desc ) {
		update_post_meta( $post_id, '_bqg_description', $desc );
	} else {
		delete_post_meta( $post_id, '_bqg_description' );
	}

	// Fiche détaillée.
	$cv_id = isset( $_POST['bqg_cv_id'] ) ? absint( $_POST['bqg_cv_id'] ) : 0;
	if ( $cv_id && 'application/pdf' !== get_post_mime_type( $cv_id ) ) {
		$cv_id = 0;
	}

	$fields = array(
		'_bqg_profession' => isset( $_POST['bqg_profession'] ) ? sanitize_text_field( wp_unslash( $_POST['bqg_profession'] ) ) : '',
		'_bqg_engagement' => isset( $_POST['bqg_engagement'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bqg_engagement'] ) ) : '',
		'_bqg_cv_texte'   => isset( $_POST['bqg_cv_texte'] ) ? wp_kses_post( wp_unslash( $_POST['bqg_cv_texte'] ) ) : '',
		'_bqg_lien'       => isset( $_POST['bqg_lien'] ) ? esc_url_raw( trim( wp_unslash( $_POST['bqg_lien'] ) ) ) : '',
		'_bqg_cv_id'      => $cv_id ? $cv_id : '',
	);

	foreach ( $fields as $key => $value ) {
		if ( '' !== trim( wp_strip_all_tags( (string) $value ) ) || ( '_bqg_cv_texte' === $key && '' !== trim( (string) $value ) ) ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}

/* -------------------------------------------------------------------------
 * 5. Colonnes de la liste des membres
 * ---------------------------------------------------------------------- */

add_filter( 'manage_' . BQG_CPT . '_posts_columns', 'bqg_admin_columns' );
function bqg_admin_columns( $columns ) {
	$new = array();

	if ( isset( $columns['cb'] ) ) {
		$new['cb'] = $columns['cb'];
	}

	$new['bqg_photo'] = 'Photo';
	$new['title']     = 'Membre';

	if ( isset( $columns[ 'taxonomy-' . BQG_TAX ] ) ) {
		$new[ 'taxonomy-' . BQG_TAX ] = 'Instance';
	}

	$new['bqg_fonction'] = 'Fonction';
	$new['bqg_fiche']    = 'Fiche détaillée';
	$new['bqg_order']    = 'Ordre';
	$new['date']         = isset( $columns['date'] ) ? $columns['date'] : 'Date';

	return $new;
}

add_action( 'manage_' . BQG_CPT . '_posts_custom_column', 'bqg_admin_column_content', 10, 2 );
function bqg_admin_column_content( $column, $post_id ) {
	if ( 'bqg_photo' === $column ) {
		$photo_id = (int) get_post_meta( $post_id, '_bqg_photo_id', true );

		if ( $photo_id ) {
			echo '<span class="bqg-col-photo">' . wp_get_attachment_image( $photo_id, array( 64, 80 ), false, array( 'alt' => '' ) ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<span class="bqg-col-photo bqg-col-photo--empty">' . esc_html( bqg_initials( get_the_title( $post_id ) ) ) . '</span>';
		}

		return;
	}

	if ( 'bqg_fonction' === $column ) {
		$role = (string) get_post_meta( $post_id, '_bqg_fonction', true );

		if ( $role ) {
			echo esc_html( $role );
		} else {
			echo '<span style="color:#b0b0b0;">&mdash;</span>';
		}

		return;
	}

	if ( 'bqg_fiche' === $column ) {
		$parts = array();
		if ( get_post_meta( $post_id, '_bqg_profession', true ) ) {
			$parts[] = 'Profession';
		}
		if ( get_post_meta( $post_id, '_bqg_engagement', true ) ) {
			$parts[] = 'Rôle';
		}
		if ( get_post_meta( $post_id, '_bqg_cv_texte', true ) ) {
			$parts[] = 'Parcours';
		}
		if ( get_post_meta( $post_id, '_bqg_cv_id', true ) ) {
			$parts[] = 'CV PDF';
		}
		echo $parts ? '<span class="bqg-col-tags">' . implode( '', array_map( function ( $p ) { return '<span>' . esc_html( $p ) . '</span>'; }, $parts ) ) . '</span>' : '<span style="color:#b0b0b0;">&mdash;</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	if ( 'bqg_order' === $column ) {
		echo esc_html( get_post_field( 'menu_order', $post_id ) );
	}
}

add_filter( 'manage_edit-' . BQG_CPT . '_sortable_columns', 'bqg_sortable_columns' );
function bqg_sortable_columns( $columns ) {
	$columns['bqg_order'] = 'menu_order';

	return $columns;
}

add_action( 'pre_get_posts', 'bqg_admin_default_order' );
function bqg_admin_default_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( BQG_CPT !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
}

/* -------------------------------------------------------------------------
 * 6. Page « Mode d'emploi »
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'bqg_admin_help_page' );
function bqg_admin_help_page() {
	add_submenu_page(
		'edit.php?post_type=' . BQG_CPT,
		'Mode d\'emploi',
		'Mode d\'emploi',
		'edit_posts',
		'bqg-aide',
		'bqg_render_help_page'
	);
}

function bqg_render_help_page() {
	$options = array(
		array( 'instances', 'Limite le bloc à certaines instances (slugs séparés par une virgule).', 'toutes', '[bqp_gouvernance instances="bureau"]' ),
		array( 'colonnes', 'Nombre de colonnes sur grand écran : 2, 3 ou 4.', '3', '[bqp_gouvernance colonnes="4"]' ),
		array( 'format', 'Forme des photos : carre, portrait ou rond.', 'carre', '[bqp_gouvernance format="rond"]' ),
		array( 'filtres', 'Affiche ou masque la barre de filtres : oui ou non.', 'oui', '[bqp_gouvernance filtres="non"]' ),
		array( 'defaut', 'Instance affichée au chargement de la page, ou tous.', 'bureau', '[bqp_gouvernance defaut="conseil-scientifique"]' ),
		array( 'tous', 'Ajoute un bouton Tous devant les instances.', 'non', '[bqp_gouvernance tous="oui"]' ),
		array( 'compteurs', 'Affiche le nombre de membres dans chaque filtre.', 'oui', '[bqp_gouvernance compteurs="non"]' ),
		array( 'titre', 'Ajoute un titre au-dessus du bloc.', 'vide', '[bqp_gouvernance titre="Notre gouvernance"]' ),
		array( 'photos', 'Rendu des photos : couleur ou grisaille (couleur au survol).', 'couleur', '[bqp_gouvernance photos="grisaille"]' ),
		array( 'badges', 'Affiche ou masque l\'étiquette d\'instance sur les cartes.', 'oui', '[bqp_gouvernance badges="non"]' ),
		array( 'limite', 'Nombre maximum de membres affichés.', 'tous', '[bqp_gouvernance limite="6"]' ),
		array( 'ordre', 'Tri : manuel, nom ou recent.', 'manuel', '[bqp_gouvernance ordre="nom"]' ),
		array( 'fenetre', 'Ouvre la fiche détaillée au clic sur un membre : oui ou non.', 'oui', '[bqp_gouvernance fenetre="non"]' ),
	);

	$notes = array(
		'Les photos sont recadrées automatiquement au format choisi, cadrées sur le haut pour ne jamais couper le visage. Elles remplissent toujours tout le cadre, quelle que soit la taille du fichier envoyé.',
		'Au chargement de la page, c\'est le <strong>Bureau</strong> qui s\'affiche. Pour ouvrir sur une autre instance, utilisez <code>defaut</code>.',
		'Pour les photos envoyées <strong>avant</strong> l\'installation de ce bloc, lancez une régénération des miniatures (extension <em>Regenerate Thumbnails</em>) afin qu\'elles bénéficient du recadrage. En attendant, elles restent correctement cadrées à l\'affichage.',
		'L\'ordre d\'affichage se règle avec le champ <strong>Ordre</strong> de chaque membre (0 en premier).',
		'La couleur de chaque étiquette se modifie dans <strong>Gouvernance &rsaquo; Instances</strong>.',
		'Un membre sans photo affiche ses initiales dans un médaillon aux couleurs de son instance.',
		'Les retours à la ligne saisis dans la description sont conservés à l\'affichage.',
		'Un clic sur un membre ouvre sa <strong>fiche détaillée</strong> : profession, rôle dans l\'association, parcours et CV au format PDF. Ces champs se remplissent dans l\'encadré <strong>Fiche détaillée</strong> du membre ; seuls ceux remplis apparaissent.',
		'Chaque fiche a son adresse (par exemple <code>/gouvernance/#claire-bonnefoy</code>) : on peut envoyer le lien direct vers un membre.',
		'Dans la fenêtre, les flèches permettent de passer au membre précédent ou suivant ; la touche Échap la ferme.',
		'Les fiches des membres ne créent aucune page publique, pour ne pas diluer le référencement du site.',
	);

	$html  = '<div class="wrap bqg-help">';
	$html .= '<h1 class="bqg-help__title">Bloc Gouvernance</h1>';
	$html .= '<p class="bqg-help__intro">Ajoutez les membres depuis le menu <strong>Gouvernance</strong>, puis collez le shortcode ci-dessous dans une page Elementor (widget <em>Shortcode</em>) ou dans l\'éditeur WordPress. Les cartes et les filtres se mettent à jour automatiquement.</p>';

	$html .= '<div class="bqg-help__hero"><span>Shortcode principal</span><code>[bqp_gouvernance]</code></div>';

	$html .= '<h2 class="bqg-help__subtitle">Les options disponibles</h2>';
	$html .= '<table class="widefat striped bqg-help__table"><thead><tr>';
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

	$html .= '<h2 class="bqg-help__subtitle">Bon à savoir</h2><ul class="bqg-help__list">';
	foreach ( $notes as $note ) {
		$html .= '<li>' . wp_kses( $note, array( 'strong' => array(), 'em' => array(), 'code' => array() ) ) . '</li>';
	}
	$html .= '</ul></div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/* -------------------------------------------------------------------------
 * 7. Styles et script de l'administration
 * ---------------------------------------------------------------------- */

add_action( 'admin_enqueue_scripts', 'bqg_admin_assets' );
function bqg_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || BQG_CPT !== $screen->post_type ) {
		return;
	}

	$is_editor = in_array( $hook, array( 'post.php', 'post-new.php' ), true );

	if ( $is_editor ) {
		wp_enqueue_media();
	}

	wp_register_style( 'bqg-admin', false, array(), BQG_VERSION );
	wp_enqueue_style( 'bqg-admin' );
	wp_add_inline_style( 'bqg-admin', bqg_admin_css() );

	if ( $is_editor ) {
		wp_register_script( 'bqg-admin', false, array( 'jquery' ), BQG_VERSION, true );
		wp_enqueue_script( 'bqg-admin' );
		wp_add_inline_script( 'bqg-admin', bqg_admin_js() );
	}
}

function bqg_admin_css() {
	$p = bqg_palette();

	return '
	.bqg-admin{font-size:14px;}
	.bqg-admin__grid{display:grid;grid-template-columns:240px 1fr;gap:28px;align-items:start;}
	@media (max-width:782px){.bqg-admin__grid{grid-template-columns:1fr;}}
	.bqg-admin__label{display:block;margin-bottom:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:' . $p['bordeaux'] . ';}
	.bqg-admin__field{margin:0 0 22px;}
	.bqg-admin__input{width:100%;padding:11px 14px;border:1px solid #dcdcde;border-radius:8px;background:#fff;box-shadow:none;transition:border-color .2s,box-shadow .2s;}
	.bqg-admin__input:focus{border-color:' . $p['bordeaux'] . ';box-shadow:0 0 0 3px ' . bqg_hex_to_rgba( $p['bordeaux'], 0.12 ) . ';outline:none;}
	.bqg-admin__textarea{resize:vertical;min-height:150px;line-height:1.65;}
	.bqg-admin__hint{display:block;margin-top:7px;color:#787c82;font-size:12.5px;line-height:1.5;}
	.bqg-admin__photo-box{position:relative;width:100%;padding-bottom:125%;border:2px dashed #dcdcde;border-radius:12px;background:#fbfbfc;overflow:hidden;transition:border-color .2s;}
	.bqg-admin__photo-box.has-photo{border-style:solid;border-color:' . bqg_hex_to_rgba( $p['bordeaux'], 0.3 ) . ';background:#fff;}
	.bqg-admin__photo-box img{position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;object-position:center top;}
	.bqg-admin__photo-empty{position:absolute;top:50%;left:0;width:100%;transform:translateY(-50%);display:flex;flex-direction:column;align-items:center;gap:6px;color:#a7aaad;font-size:12.5px;}
	.bqg-admin__photo-empty .dashicons{font-size:32px;width:32px;height:32px;}
	.bqg-admin__photo-actions{display:flex;align-items:center;gap:12px;margin-top:12px;}
	.bqg-admin__remove{color:#b32d2e;text-decoration:none;cursor:pointer;}
	.bqg-admin__remove:hover{color:#8a2223;}
	.bqg-admin__shortcode{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin-top:8px;padding:14px 18px;border-radius:10px;background:' . bqg_hex_to_rgba( $p['bordeaux'], 0.05 ) . ';border:1px solid ' . bqg_hex_to_rgba( $p['bordeaux'], 0.14 ) . ';color:' . $p['bordeaux_dark'] . ';}
	.bqg-admin__shortcode code{background:#fff;border:1px solid ' . bqg_hex_to_rgba( $p['bordeaux'], 0.2 ) . ';color:' . $p['bordeaux'] . ';padding:5px 11px;border-radius:6px;font-weight:600;}
	.bqg-admin__shortcode .dashicons{color:' . $p['bordeaux'] . ';}
	.bqg-admin__profile{margin:6px 0 22px;padding:22px 22px 4px;border:1px solid ' . bqg_hex_to_rgba( $p['bordeaux'], 0.16 ) . ';border-radius:12px;background:' . bqg_hex_to_rgba( $p['bordeaux'], 0.025 ) . ';}
	.bqg-admin__profile-head{display:flex;gap:12px;align-items:flex-start;margin:0 0 20px;}
	.bqg-admin__profile-head .dashicons{color:' . $p['bordeaux'] . ';font-size:24px;width:24px;height:24px;}
	.bqg-admin__profile-head strong{display:block;font-size:15px;color:' . $p['bordeaux_dark'] . ';}
	.bqg-admin__profile-head span{display:block;margin-top:3px;color:#787c82;font-size:12.5px;}
	.bqg-admin__cols{display:grid;grid-template-columns:1fr 1fr;gap:0 22px;}
	@media (max-width:960px){.bqg-admin__cols{grid-template-columns:1fr;}}
	.bqg-admin__profile .wp-editor-wrap{border-radius:8px;overflow:hidden;}
	.bqg-admin__file{display:flex;align-items:center;flex-wrap:wrap;gap:12px;padding:12px 14px;border:1px dashed #dcdcde;border-radius:8px;background:#fff;}
	.bqg-admin__file .dashicons{color:' . $p['bordeaux'] . ';}
	.bqg-admin__file-name{flex:1;min-width:140px;font-weight:600;color:#3c434a;word-break:break-all;}
	.bqg-col-tags{display:flex;flex-wrap:wrap;gap:4px;}
	.bqg-col-tags span{padding:2px 8px;border-radius:999px;background:' . bqg_hex_to_rgba( $p['bordeaux'], 0.07 ) . ';color:' . $p['bordeaux'] . ';font-size:11px;font-weight:600;}
	.column-bqg_photo{width:90px;}
	.column-bqg_order{width:70px;}
	.bqg-col-photo{display:flex;align-items:center;justify-content:center;width:64px;height:80px;border:1px solid #e6e6e8;border-radius:8px;background:#fff;overflow:hidden;}
	.bqg-col-photo img{width:100%;height:100%;object-fit:cover;object-position:center top;}
	.bqg-col-photo--empty{background:' . bqg_hex_to_rgba( $p['bordeaux'], 0.07 ) . ';border-color:' . bqg_hex_to_rgba( $p['bordeaux'], 0.16 ) . ';color:' . $p['bordeaux'] . ';font-weight:700;letter-spacing:.04em;}
	.bqg-help__title{font-size:30px;font-weight:600;color:' . $p['bordeaux_dark'] . ';}
	.bqg-help__intro{max-width:760px;font-size:14.5px;line-height:1.7;color:#50575e;}
	.bqg-help__hero{display:flex;align-items:center;flex-wrap:wrap;gap:14px;margin:22px 0 32px;padding:22px 26px;border-radius:14px;background:linear-gradient(135deg,' . $p['bordeaux'] . ' 0%,' . $p['bordeaux_dark'] . ' 100%);color:#fff;box-shadow:0 12px 30px -16px ' . bqg_hex_to_rgba( $p['bordeaux_dark'], 0.8 ) . ';}
	.bqg-help__hero span{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.75;}
	.bqg-help__hero code{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);color:#fff;padding:9px 16px;border-radius:8px;font-size:15px;font-weight:600;}
	.bqg-help__subtitle{margin-top:34px;font-size:19px;font-weight:600;color:' . $p['bordeaux_dark'] . ';}
	.bqg-help__table{max-width:1080px;}
	.bqg-help__table th{font-weight:600;}
	.bqg-help__table code{color:' . $p['bordeaux'] . ';background:' . bqg_hex_to_rgba( $p['bordeaux'], 0.06 ) . ';}
	.bqg-help__list{max-width:900px;line-height:1.8;list-style:disc;padding-left:20px;color:#50575e;}
	';
}

function bqg_admin_js() {
	return "
	(function(\$){
		\$(function(){
			var frame,
				\$id      = \$('#bqg-photo-id'),
				\$preview = \$('#bqg-photo-preview'),
				\$empty   = \$('#bqg-photo-empty'),
				\$box     = \$('#bqg-photo-box'),
				\$remove  = \$('#bqg-photo-remove'),
				\$select  = \$('#bqg-photo-select');

			\$select.on('click', function(e){
				e.preventDefault();

				if (frame) { frame.open(); return; }

				frame = wp.media({
					title: 'Choisir la photo du membre',
					button: { text: 'Utiliser cette photo' },
					library: { type: 'image' },
					multiple: false
				});

				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON(),
						src = att.url;

					if (att.sizes) {
						if (att.sizes.bqg_portrait)  { src = att.sizes.bqg_portrait.url; }
						else if (att.sizes.medium_large) { src = att.sizes.medium_large.url; }
						else if (att.sizes.medium)   { src = att.sizes.medium.url; }
					}

					\$id.val(att.id);
					\$preview.attr('src', src).prop('hidden', false);
					\$empty.prop('hidden', true);
					\$box.addClass('has-photo');
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
				\$box.removeClass('has-photo');
				\$remove.prop('hidden', true);
				\$select.text('Choisir une photo');
			});

			\$('#bqg_description').on('input', function(){
				\$('#bqg-count').text(\$(this).val().length);
			});

			// CV au format PDF.
			var cvFrame;
			\$('#bqg-cv-select').on('click', function(e){
				e.preventDefault();
				if (cvFrame) { cvFrame.open(); return; }
				cvFrame = wp.media({
					title: 'Choisir le CV (PDF)',
					button: { text: 'Utiliser ce fichier' },
					library: { type: 'application/pdf' },
					multiple: false
				});
				cvFrame.on('select', function(){
					var att = cvFrame.state().get('selection').first().toJSON();
					if (att.mime && 'application/pdf' !== att.mime) { window.alert('Choisissez un fichier PDF.'); return; }
					\$('#bqg-cv-id').val(att.id);
					\$('#bqg-cv-name').text(att.filename || att.title);
					\$('#bqg-cv-select').text('Remplacer');
					\$('#bqg-cv-remove').prop('hidden', false);
				});
				cvFrame.open();
			});
			\$('#bqg-cv-remove').on('click', function(e){
				e.preventDefault();
				\$('#bqg-cv-id').val('');
				\$('#bqg-cv-name').text('Aucun fichier');
				\$('#bqg-cv-select').text('Choisir un PDF');
				\$(this).prop('hidden', true);
			});
		});
	})(jQuery);
	";
}

/* -------------------------------------------------------------------------
 * 8. Shortcode [bqp_gouvernance]
 * ---------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', 'bqg_register_front_assets' );
function bqg_register_front_assets() {
	wp_register_style( 'bqg-gouvernance', false, array(), BQG_VERSION );
	wp_add_inline_style( 'bqg-gouvernance', bqg_front_css() );

	wp_register_script( 'bqg-gouvernance', false, array(), BQG_VERSION, true );
	wp_add_inline_script( 'bqg-gouvernance', bqg_front_js() );

	// Chargement anticipé quand le shortcode est détectable dans la page,
	// pour que le style soit présent dès le rendu et non dans le pied de page.
	if ( is_singular() ) {
		$post = get_post();

		if ( $post instanceof WP_Post && has_shortcode( (string) $post->post_content, 'bqp_gouvernance' ) ) {
			wp_enqueue_style( 'bqg-gouvernance' );
			wp_enqueue_script( 'bqg-gouvernance' );
		}
	}
}

add_shortcode( 'bqp_gouvernance', 'bqg_shortcode' );
function bqg_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'instances' => '',
			'colonnes'  => '3',
			'format'    => 'carre',
			'filtres'   => 'oui',
			'compteurs' => 'oui',
			'tous'      => 'non',
			'defaut'    => 'bureau',
			'titre'     => '',
			'photos'    => 'couleur',
			'badges'    => 'oui',
			'limite'    => '-1',
			'ordre'     => 'manuel',
			'fenetre'   => 'oui',
		),
		$atts,
		'bqp_gouvernance'
	);

	$columns   = max( 2, min( 4, (int) $atts['colonnes'] ) );
	$show_tabs = in_array( strtolower( $atts['filtres'] ), array( 'oui', 'yes', 'true', '1' ), true );
	$show_nb   = in_array( strtolower( $atts['compteurs'] ), array( 'oui', 'yes', 'true', '1' ), true );
	$show_bdg  = in_array( strtolower( $atts['badges'] ), array( 'oui', 'yes', 'true', '1' ), true );
	$show_all  = in_array( strtolower( $atts['tous'] ), array( 'oui', 'yes', 'true', '1' ), true );
	$grayscale = 'grisaille' === strtolower( $atts['photos'] );
	$popup     = ! in_array( strtolower( $atts['fenetre'] ), array( 'non', 'no', 'false', '0' ), true );
	$limit     = (int) $atts['limite'];

	$format = strtolower( $atts['format'] );
	if ( ! in_array( $format, array( 'portrait', 'carre', 'rond' ), true ) ) {
		$format = 'carre';
	}

	$image_size = ( 'portrait' === $format ) ? 'bqg_portrait' : 'bqg_carre';

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
		'post_type'              => BQG_CPT,
		'post_status'            => 'publish',
		'posts_per_page'         => ( 0 === $limit ) ? -1 : $limit,
		'orderby'                => $orderby,
		'no_found_rows'          => true,
		'update_post_term_cache' => true,
		'ignore_sticky_posts'    => true,
	);

	$slugs = array_filter( array_map( 'sanitize_title', explode( ',', $atts['instances'] ) ) );
	if ( $slugs ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => BQG_TAX,
				'field'    => 'slug',
				'terms'    => $slugs,
			),
		);
	}

	$query = new WP_Query( $args );

	if ( ! $query->have_posts() ) {
		wp_reset_postdata();

		if ( current_user_can( 'edit_posts' ) ) {
			return '<p class="bqg-empty-notice">Aucun membre publié pour le moment. Ajoutez-en depuis le menu <strong>Gouvernance</strong>.</p>';
		}

		return '';
	}

	wp_enqueue_style( 'bqg-gouvernance' );
	wp_enqueue_script( 'bqg-gouvernance' );

	$members = array();
	$tabs    = array();

	while ( $query->have_posts() ) {
		$query->the_post();

		$post_id = get_the_ID();
		$terms   = get_the_terms( $post_id, BQG_TAX );
		$terms   = ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();

		$term_slugs = array();
		foreach ( $terms as $term ) {
			$term_slugs[] = $term->slug;

			if ( ! isset( $tabs[ $term->slug ] ) ) {
				$tabs[ $term->slug ] = array( 'name' => $term->name, 'count' => 0 );
			}
			$tabs[ $term->slug ]['count']++;
		}

		$main_term = $terms ? reset( $terms ) : null;
		$palette   = bqg_palette();

		$cv_id = (int) get_post_meta( $post_id, '_bqg_cv_id', true );
		$cv    = $cv_id ? get_attached_file( $cv_id ) : '';

		$members[] = array(
			'id'         => $post_id,
			'slug'       => get_post_field( 'post_name', $post_id ) ? get_post_field( 'post_name', $post_id ) : sanitize_title( get_the_title() ),
			'name'       => get_the_title(),
			'photo'      => (int) get_post_meta( $post_id, '_bqg_photo_id', true ),
			'role'       => (string) get_post_meta( $post_id, '_bqg_fonction', true ),
			'desc'       => (string) get_post_meta( $post_id, '_bqg_description', true ),
			'profession' => (string) get_post_meta( $post_id, '_bqg_profession', true ),
			'engagement' => (string) get_post_meta( $post_id, '_bqg_engagement', true ),
			'cv_text'    => (string) get_post_meta( $post_id, '_bqg_cv_texte', true ),
			'cv_url'     => $cv_id ? (string) wp_get_attachment_url( $cv_id ) : '',
			'cv_size'    => ( $cv && file_exists( $cv ) ) ? size_format( filesize( $cv ), filesize( $cv ) >= MB_IN_BYTES ? 1 : 0 ) : '',
			'link'       => (string) get_post_meta( $post_id, '_bqg_lien', true ),
			'label'      => $main_term ? $main_term->name : '',
			'color'      => $main_term ? bqg_term_color( $main_term->term_id ) : $palette['bordeaux'],
			'slugs'      => $term_slugs,
		);
	}

	wp_reset_postdata();

	// Ordre des onglets : les instances connues d'abord (Bureau, puis
	// Conseil scientifique), les eventuelles autres ensuite par ordre alphabetique.
	$ordered = array();
	foreach ( array_keys( bqg_default_terms() ) as $known ) {
		if ( isset( $tabs[ $known ] ) ) {
			$ordered[ $known ] = $tabs[ $known ];
			unset( $tabs[ $known ] );
		}
	}
	ksort( $tabs );
	$tabs = $ordered + $tabs;

	$show_filters = $show_tabs && count( $tabs ) > 1;

	// Instance affichee au chargement de la page.
	$raw_default = strtolower( trim( (string) $atts['defaut'] ) );
	$active      = '';

	if ( in_array( $raw_default, array( 'tous', 'all', '*' ), true ) ) {
		$active   = '*';
		$show_all = true;
	} elseif ( '' !== $raw_default && isset( $tabs[ sanitize_title( $raw_default ) ] ) ) {
		$active = sanitize_title( $raw_default );
	}

	if ( '' === $active ) {
		$active = $show_all ? '*' : (string) key( $tabs );
	}

	if ( ! $show_filters ) {
		$active = '*';
	}

	static $block = 0;
	$block++;

	$classes   = 'bqg-team bqg-team--' . $format . ( $grayscale ? ' bqg-team--grayscale' : '' ) . ( $popup ? ' bqg-team--popup' : '' );
	$modal_id  = 'bqg-modal-' . $block;
	$templates = '';

	$html  = '<section class="' . esc_attr( $classes ) . '" style="--bqg-cols-max:' . esc_attr( $columns ) . ';">';

	if ( '' !== trim( $atts['titre'] ) ) {
		$html .= '<h2 class="bqg-team__title">' . esc_html( $atts['titre'] ) . '</h2>';
		$html .= '<span class="bqg-team__rule" aria-hidden="true"></span>';
	}

	if ( $show_filters ) {
		$html .= '<div class="bqg-filters" role="group" aria-label="Filtrer les membres par instance">';

		if ( $show_all ) {
			$is_on  = ( '*' === $active );
			$html  .= '<button type="button" class="bqg-filter' . ( $is_on ? ' is-active' : '' ) . '" data-filter="*" aria-pressed="' . ( $is_on ? 'true' : 'false' ) . '">'
				. '<span class="bqg-filter__label">Tous</span>'
				. ( $show_nb ? '<span class="bqg-filter__count">' . esc_html( count( $members ) ) . '</span>' : '' )
				. '</button>';
		}

		foreach ( $tabs as $slug => $tab ) {
			$is_on = ( $slug === $active );
			$html .= '<button type="button" class="bqg-filter' . ( $is_on ? ' is-active' : '' ) . '" data-filter="' . esc_attr( $slug ) . '" aria-pressed="' . ( $is_on ? 'true' : 'false' ) . '">'
				. '<span class="bqg-filter__label">' . esc_html( $tab['name'] ) . '</span>'
				. ( $show_nb ? '<span class="bqg-filter__count">' . esc_html( $tab['count'] ) . '</span>' : '' )
				. '</button>';
		}

		$html .= '</div>';
	}

	$html .= '<div class="bqg-grid">';

	$visible = 0;

	foreach ( $members as $member ) {
		$style = '--bqg-cat:' . $member['color']
			. ';--bqg-cat-soft:' . bqg_hex_to_rgba( $member['color'], 0.09 )
			. ';--bqg-cat-line:' . bqg_hex_to_rgba( $member['color'], 0.22 ) . ';';

		// L'etat initial est calcule cote serveur : la bonne instance est
		// deja affichee au chargement, sans attendre le JavaScript.
		$is_shown = ( '*' === $active ) || in_array( $active, $member['slugs'], true );

		if ( $is_shown ) {
			$visible++;
		}

		// Fiche détaillée : la carte devient cliquable dès qu'il y a
		// quelque chose de plus à montrer que la carte elle-même.
		$has_profile = $popup && ( $member['profession'] || $member['engagement'] || trim( wp_strip_all_tags( $member['cv_text'] ) ) || $member['cv_url'] || $member['link'] || $member['desc'] );
		$tpl_id      = 'bqg-tpl-' . $block . '-' . $member['id'];

		$html .= '<article class="bqg-card' . ( $has_profile ? ' is-clickable' : '' ) . '" data-cats="' . esc_attr( implode( ' ', $member['slugs'] ) ) . '"' . ( $has_profile ? ' data-slug="' . esc_attr( $member['slug'] ) . '"' : '' ) . ' style="' . esc_attr( $style ) . '"' . ( $is_shown ? '' : ' hidden' ) . '>';

		if ( $has_profile ) {
			$html      .= '<button type="button" class="bqg-card__open" data-bqg-open="' . esc_attr( $tpl_id ) . '" aria-haspopup="dialog" aria-controls="' . esc_attr( $modal_id ) . '"><span class="bqg-sr">Voir le profil de ' . esc_html( $member['name'] ) . '</span></button>';
			$templates .= '<template id="' . esc_attr( $tpl_id ) . '" data-slug="' . esc_attr( $member['slug'] ) . '">' . bqg_render_profile( $member, $show_bdg ) . '</template>';
		}

		$html .= '<div class="bqg-card__photo">';
		if ( $member['photo'] ) {
			$html .= wp_get_attachment_image(
				$member['photo'],
				$image_size,
				false,
				array(
					'class'   => 'bqg-card__img',
					'alt'     => 'Portrait de ' . $member['name'],
					'loading' => 'lazy',
				)
			);
		} else {
			$html .= '<span class="bqg-card__initials" aria-hidden="true">' . esc_html( bqg_initials( $member['name'] ) ) . '</span>';
		}
		$html .= '</div>';

		$html .= '<div class="bqg-card__body">';

		if ( $show_bdg && $member['label'] ) {
			$html .= '<span class="bqg-card__badge">' . esc_html( $member['label'] ) . '</span>';
		}

		$html .= '<h3 class="bqg-card__name">' . esc_html( $member['name'] ) . '</h3>';

		if ( $member['role'] ) {
			$html .= '<p class="bqg-card__role">' . esc_html( $member['role'] ) . '</p>';
		}

		if ( $member['desc'] ) {
			$html .= '<div class="bqg-card__desc">' . wpautop( esc_html( $member['desc'] ) ) . '</div>';
		}

		if ( $has_profile ) {
			$html .= '<span class="bqg-card__more" aria-hidden="true">Voir le profil'
				. ( $member['cv_url'] ? '<span class="bqg-card__cv">CV</span>' : '' )
				. '<svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h12M11.5 5.5 16 10l-4.5 4.5"/></svg></span>';
		}

		$html .= '</div></article>';
	}

	$html .= '</div>';
	$html .= '<p class="bqg-noresult"' . ( $visible > 0 ? ' hidden' : '' ) . '>Aucun membre dans cette instance pour le moment.</p>';

	if ( $templates ) {
		$html .= $templates;
		$html .= '<dialog class="bqg-modal" id="' . esc_attr( $modal_id ) . '" aria-label="Profil du membre">'
			. '<div class="bqg-modal__bar">'
			. '<button type="button" class="bqg-modal__nav" data-bqg-prev aria-label="Membre précédent"><svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12.5 4.5 7 10l5.5 5.5"/></svg></button>'
			. '<span class="bqg-modal__pos" data-bqg-pos></span>'
			. '<button type="button" class="bqg-modal__nav" data-bqg-next aria-label="Membre suivant"><svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7.5 4.5 13 10l-5.5 5.5"/></svg></button>'
			. '<button type="button" class="bqg-modal__close" data-bqg-close aria-label="Fermer"><svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 5l10 10M15 5 5 15"/></svg></button>'
			. '</div>'
			. '<div class="bqg-modal__body" data-bqg-body></div>'
			. '</dialog>';
	}

	$html .= '</section>';

	return $html;
}

/**
 * Le contenu de la fenêtre d'un membre : portrait et repères à gauche,
 * profession, rôle dans l'association, présentation et parcours à droite.
 */
function bqg_render_profile( $member, $show_badge = true ) {
	$style = '--bqg-cat:' . $member['color'] . ';--bqg-cat-soft:' . bqg_hex_to_rgba( $member['color'], 0.08 ) . ';--bqg-cat-line:' . bqg_hex_to_rgba( $member['color'], 0.22 ) . ';';
	$title = 'bqg-profile-title-' . $member['id'];

	$html  = '<div class="bqg-profile" style="' . esc_attr( $style ) . '" data-title="' . esc_attr( $title ) . '">';
	$html .= '<aside class="bqg-profile__side">';
	$html .= '<div class="bqg-profile__photo">';
	$html .= $member['photo']
		? wp_get_attachment_image( $member['photo'], 'bqg_portrait', false, array( 'alt' => 'Portrait de ' . $member['name'], 'loading' => 'lazy' ) )
		: '<span class="bqg-profile__initials" aria-hidden="true">' . esc_html( bqg_initials( $member['name'] ) ) . '</span>';
	$html .= '</div>';

	$actions = '';
	if ( $member['cv_url'] ) {
		$actions .= '<a class="bqg-btn" href="' . esc_url( $member['cv_url'] ) . '" download>'
			. '<svg viewBox="0 0 20 20" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3v10M5.5 8.5 10 13l4.5-4.5M3.5 16.5h13"/></svg>'
			. 'Télécharger le CV' . ( $member['cv_size'] ? '<span class="bqg-btn__meta">PDF, ' . esc_html( $member['cv_size'] ) . '</span>' : '' ) . '</a>';
		$actions .= '<a class="bqg-btn bqg-btn--ghost" href="' . esc_url( $member['cv_url'] ) . '" target="_blank" rel="noopener">Ouvrir le CV</a>';
	}
	if ( $member['link'] ) {
		$host     = wp_parse_url( $member['link'], PHP_URL_HOST );
		$actions .= '<a class="bqg-btn bqg-btn--ghost" href="' . esc_url( $member['link'] ) . '" target="_blank" rel="noopener">'
			. ( false !== stripos( (string) $host, 'linkedin' ) ? 'Profil LinkedIn' : 'Page personnelle' ) . '</a>';
	}
	if ( $actions ) {
		$html .= '<div class="bqg-profile__actions">' . $actions . '</div>';
	}

	$html .= '</aside>';

	$html .= '<div class="bqg-profile__main">';
	if ( $show_badge && $member['label'] ) {
		$html .= '<span class="bqg-card__badge">' . esc_html( $member['label'] ) . '</span>';
	}
	$html .= '<h2 class="bqg-profile__name" id="' . esc_attr( $title ) . '">' . esc_html( $member['name'] ) . '</h2>';
	if ( $member['role'] ) {
		$html .= '<p class="bqg-card__role">' . esc_html( $member['role'] ) . '</p>';
	}
	if ( $member['profession'] ) {
		$html .= '<p class="bqg-profile__profession"><svg viewBox="0 0 20 20" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="6" width="15" height="10.5" rx="1.5"/><path d="M7 6V4.5A1.5 1.5 0 0 1 8.5 3h3A1.5 1.5 0 0 1 13 4.5V6M2.5 11h15"/></svg><span><em>Profession</em>' . esc_html( $member['profession'] ) . '</span></p>';
	}

	if ( $member['engagement'] ) {
		$html .= '<section class="bqg-profile__section bqg-profile__section--quote"><h3>Son rôle dans l\'association</h3>' . wpautop( esc_html( $member['engagement'] ) ) . '</section>';
	}
	if ( $member['desc'] ) {
		$html .= '<section class="bqg-profile__section"><h3>Présentation</h3>' . wpautop( esc_html( $member['desc'] ) ) . '</section>';
	}
	if ( trim( wp_strip_all_tags( $member['cv_text'] ) ) ) {
		$html .= '<section class="bqg-profile__section bqg-profile__cv"><h3>Parcours</h3>' . wpautop( wp_kses_post( $member['cv_text'] ) ) . '</section>';
	}

	$html .= '</div></div>';

	return $html;
}

/* -------------------------------------------------------------------------
 * 9. Styles du bloc (charte boursequatrepoint.fr)
 * ---------------------------------------------------------------------- */

function bqg_front_css() {
	$p = bqg_palette();

	return '
	.bqg-team{
		--bqg-bordeaux:' . $p['bordeaux'] . ';
		--bqg-bordeaux-dark:' . $p['bordeaux_dark'] . ';
		--bqg-bordeaux-soft:' . bqg_hex_to_rgba( $p['bordeaux'], 0.06 ) . ';
		--bqg-ink:' . $p['ink'] . ';
		--bqg-muted:' . $p['muted'] . ';
		--bqg-line:' . $p['line'] . ';
		--bqg-cols:var(--bqg-cols-max,3);
		--bqg-serif:"Cormorant Garamond","Playfair Display",Georgia,"Times New Roman",serif;
		--bqg-sans:"Inter","Montserrat","Lato",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
		box-sizing:border-box;width:100%;margin:0 auto;padding:0;
	}
	.bqg-team *,.bqg-team *::before,.bqg-team *::after{box-sizing:border-box;}

	.bqg-team__title{
		margin:0 0 14px;text-align:center;
		font-family:var(--bqg-serif);font-weight:600;font-size:clamp(1.85rem,3.4vw,2.65rem);
		line-height:1.15;letter-spacing:.005em;color:var(--bqg-bordeaux-dark);
	}
	.bqg-team__rule{
		display:block;width:64px;height:2px;margin:0 auto 38px;
		background:linear-gradient(90deg,var(--bqg-bordeaux),var(--bqg-bordeaux-dark));
	}

	/* --- Filtres --- */
	.bqg-team .bqg-filters{
		display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin:0 0 36px;padding:0;
	}
	.bqg-team .bqg-filters .bqg-filter{
		-webkit-appearance:none;appearance:none;
		display:inline-flex;align-items:center;gap:7px;
		width:auto;min-height:0;margin:0;padding:9px 18px;
		border:1.5px solid var(--bqg-line) !important;border-radius:999px !important;
		background:#fff !important;background-image:none !important;
		color:var(--bqg-ink) !important;
		font-family:var(--bqg-sans);font-size:.70rem;font-weight:700;line-height:1;
		letter-spacing:.055em;text-transform:uppercase;text-decoration:none;text-shadow:none;
		cursor:pointer;box-shadow:none;
		transition:color .25s ease,border-color .25s ease,background-color .25s ease,box-shadow .25s ease,transform .25s ease;
	}
	.bqg-team .bqg-filters .bqg-filter:hover,
	.bqg-team .bqg-filters .bqg-filter:focus{
		background:var(--bqg-bordeaux-soft) !important;
		border-color:var(--bqg-bordeaux) !important;
		color:var(--bqg-bordeaux) !important;
		transform:translateY(-1px);
	}
	.bqg-team .bqg-filters .bqg-filter:focus-visible{outline:2px solid var(--bqg-bordeaux);outline-offset:3px;}
	.bqg-team .bqg-filters .bqg-filter.is-active,
	.bqg-team .bqg-filters .bqg-filter.is-active:hover,
	.bqg-team .bqg-filters .bqg-filter.is-active:focus{
		background:var(--bqg-bordeaux) !important;
		background-image:linear-gradient(180deg,var(--bqg-bordeaux) 0%,var(--bqg-bordeaux-dark) 100%) !important;
		border-color:transparent !important;
		color:#fff !important;
		box-shadow:0 8px 18px -10px ' . bqg_hex_to_rgba( $p['bordeaux_dark'], 0.75 ) . ';
		transform:none;
	}
	.bqg-team .bqg-filter__count{
		display:inline-flex;align-items:center;justify-content:center;min-width:17px;height:17px;
		padding:0 5px;border-radius:999px;background:rgba(0,0,0,.06);color:inherit;
		font-size:.62rem;font-weight:700;letter-spacing:0;transition:background-color .25s ease;
	}
	.bqg-team .bqg-filter:hover .bqg-filter__count{background:' . bqg_hex_to_rgba( $p['bordeaux'], 0.12 ) . ';}
	.bqg-team .bqg-filter.is-active .bqg-filter__count{background:rgba(255,255,255,.22);}

	/* --- Grille --- */
	.bqg-grid{display:grid;grid-template-columns:repeat(var(--bqg-cols),minmax(0,1fr));gap:28px;align-items:stretch;}
	@media (max-width:1024px){.bqg-team{--bqg-cols:2;}}
	@media (max-width:620px){
		.bqg-team{--bqg-cols:1;}
		.bqg-grid{gap:22px;}
		.bqg-team .bqg-filters{gap:7px;}
		.bqg-team .bqg-filters .bqg-filter{padding:8px 15px;font-size:.66rem;}
	}

	/* --- Carte --- */
	.bqg-card{
		position:relative;display:flex;flex-direction:column;overflow:hidden;
		background:#fff;border:1px solid var(--bqg-line);border-radius:16px;
		transition:transform .38s cubic-bezier(.2,.7,.3,1),box-shadow .38s ease,border-color .38s ease;
		animation:bqg-in .45s cubic-bezier(.2,.7,.3,1) both;
	}
	.bqg-card::after{
		content:"";position:absolute;inset:0 0 auto 0;height:3px;z-index:2;
		background:var(--bqg-cat);transform:scaleX(0);transform-origin:left;
		transition:transform .42s cubic-bezier(.2,.7,.3,1);
	}
	.bqg-card:hover,.bqg-card:focus-within{
		transform:translateY(-6px);
		border-color:var(--bqg-cat-line,var(--bqg-line));
		box-shadow:0 22px 44px -24px rgba(49,2,12,.5);
	}
	.bqg-card:hover::after,.bqg-card:focus-within::after{transform:scaleX(1);}
	.bqg-card[hidden]{display:none;}

	@keyframes bqg-in{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:none;}}

	/* --- Photo : recadrage identique pour toutes les cartes --- */
	.bqg-card__photo{
		position:relative;width:100%;padding-bottom:125%;overflow:hidden;
		background:var(--bqg-cat-soft,rgba(116,4,28,.09));
	}
	.bqg-team--carre .bqg-card__photo{padding-bottom:100%;}
	.bqg-team--rond .bqg-card__photo{
		width:158px;padding-bottom:158px;margin:32px auto 0;border-radius:50%;
		box-shadow:0 0 0 1px var(--bqg-cat-line,rgba(116,4,28,.22));
	}
	.bqg-team .bqg-card__photo img.bqg-card__img{
		position:absolute !important;top:0 !important;left:0 !important;
		display:block !important;
		width:100% !important;height:100% !important;
		min-width:100% !important;min-height:100% !important;
		max-width:none !important;max-height:none !important;
		object-fit:cover !important;object-position:center top;
		border-radius:0;
		transition:filter .45s ease,transform .6s cubic-bezier(.2,.7,.3,1);
	}
	.bqg-team--grayscale .bqg-card__img{filter:grayscale(1);}
	.bqg-team--grayscale .bqg-card:hover .bqg-card__img{filter:none;}
	.bqg-card:hover .bqg-card__img{transform:scale(1.04);}
	.bqg-card__initials{
		position:absolute;top:0;left:0;display:flex;align-items:center;justify-content:center;
		width:100%;height:100%;color:var(--bqg-cat);
		font-family:var(--bqg-serif);font-size:2.6rem;font-weight:600;letter-spacing:.03em;
	}

	/* --- Contenu --- */
	.bqg-card__body{display:flex;flex-direction:column;gap:9px;flex:1;padding:24px 24px 26px;}
	.bqg-team--rond .bqg-card__body{align-items:center;text-align:center;}
	.bqg-card__badge{
		align-self:flex-start;max-width:100%;margin-bottom:3px;padding:5px 12px;border-radius:999px;
		overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
		background:var(--bqg-cat-soft,rgba(116,4,28,.09));
		border:1px solid var(--bqg-cat-line,rgba(116,4,28,.22));color:var(--bqg-cat);
		font-family:var(--bqg-sans);font-size:.62rem;font-weight:700;line-height:1.5;
		letter-spacing:.1em;text-transform:uppercase;
	}
	.bqg-team--rond .bqg-card__badge{align-self:center;}
	.bqg-card__name{
		margin:0;font-family:var(--bqg-serif);font-weight:600;
		font-size:1.44rem;line-height:1.22;color:var(--bqg-bordeaux-dark);
		overflow-wrap:break-word;
	}
	.bqg-card__role{
		margin:0;font-family:var(--bqg-sans);font-size:.7rem;font-weight:700;line-height:1.5;
		letter-spacing:.085em;text-transform:uppercase;color:var(--bqg-bordeaux);
	}
	.bqg-card__desc{
		margin:10px 0 0;padding-top:16px;border-top:1px solid var(--bqg-line);flex:1;
		font-family:var(--bqg-sans);font-size:.92rem;line-height:1.7;color:#5d5d5d;
	}
	.bqg-card__desc p{margin:0 0 .8em;}
	.bqg-card__desc p:last-child{margin-bottom:0;}
	.bqg-team--rond .bqg-card__desc{width:100%;}

	/* --- Divers --- */
	.bqg-noresult{
		margin:34px 0 0;text-align:center;color:var(--bqg-muted);
		font-family:var(--bqg-sans);font-size:.95rem;font-style:italic;
	}
	.bqg-noresult[hidden]{display:none;}
	.bqg-empty-notice{
		padding:18px 22px;border:1px dashed ' . bqg_hex_to_rgba( $p['bordeaux'], 0.35 ) . ';border-radius:12px;
		background:' . bqg_hex_to_rgba( $p['bordeaux'], 0.04 ) . ';color:' . $p['bordeaux_dark'] . ';
		font-family:"Inter",sans-serif;font-size:.95rem;
	}


	/* --- Carte cliquable --- */
	.bqg-sr{position:absolute !important;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);border:0;}
	.bqg-team .bqg-card__open,.bqg-team .bqg-card__open:hover,.bqg-team .bqg-card__open:focus{
		position:absolute;inset:0;z-index:3;width:100%;height:100%;margin:0 !important;padding:0 !important;
		border:0 !important;border-radius:16px !important;background:transparent !important;box-shadow:none !important;cursor:pointer;
	}
	.bqg-team .bqg-card__open:focus-visible{outline:3px solid var(--bqg-cat) !important;outline-offset:-3px;}
	.bqg-team--popup .bqg-card.is-clickable .bqg-card__desc{flex:0 1 auto;max-height:10.4em;overflow:hidden;}
	.bqg-card__desc.is-clamped{-webkit-mask-image:linear-gradient(#000 62%,transparent);mask-image:linear-gradient(#000 62%,transparent);}
	.bqg-card__more{
		display:flex;align-items:center;gap:9px;margin-top:auto;padding-top:16px;
		font-family:var(--bqg-sans);font-size:.68rem;font-weight:700;letter-spacing:.11em;text-transform:uppercase;color:var(--bqg-cat);
	}
	.bqg-card__more svg{transition:transform .3s cubic-bezier(.2,.7,.3,1);}
	.bqg-card:hover .bqg-card__more svg,.bqg-card:focus-within .bqg-card__more svg{transform:translateX(5px);}
	.bqg-card__cv{padding:2px 7px;border:1px solid var(--bqg-cat-line);border-radius:999px;font-size:.58rem;letter-spacing:.06em;}
	.bqg-team--rond .bqg-card__more{justify-content:center;}

	/* --- Fenêtre de profil --- */
	html.bqg-lock{overflow:hidden;}
	.bqg-modal{
		width:min(1000px,calc(100vw - 32px));max-width:none;max-height:calc(100vh - 48px);margin:auto;padding:0;
		border:0;border-radius:20px;background:#fff;color:var(--bqg-ink);overflow:hidden;
		box-shadow:0 50px 120px -40px rgba(31,2,12,.7);font-family:var(--bqg-sans);
	}
	.bqg-modal[open]{display:flex;flex-direction:column;animation:bqg-modal-in .38s cubic-bezier(.2,.7,.3,1);}
	.bqg-modal::backdrop{background:rgba(31,2,12,.58);-webkit-backdrop-filter:blur(5px);backdrop-filter:blur(5px);}
	@keyframes bqg-modal-in{from{opacity:0;transform:translateY(18px) scale(.985);}to{opacity:1;transform:none;}}
	.bqg-modal__bar{display:flex;align-items:center;gap:8px;padding:12px 14px 12px 18px;border-bottom:1px solid var(--bqg-line);background:#fff;}
	.bqg-modal__pos{min-width:48px;font-size:.72rem;font-weight:700;letter-spacing:.08em;color:var(--bqg-muted);text-align:center;}
	.bqg-modal .bqg-modal__nav,.bqg-modal .bqg-modal__close,
	.bqg-modal .bqg-modal__nav:hover,.bqg-modal .bqg-modal__close:hover,
	.bqg-modal .bqg-modal__nav:focus,.bqg-modal .bqg-modal__close:focus{
		-webkit-appearance:none;appearance:none;display:inline-flex;align-items:center;justify-content:center;
		width:40px;height:40px;min-height:0;margin:0 !important;padding:0 !important;
		border:1px solid var(--bqg-line) !important;border-radius:50% !important;
		background:#fff !important;background-image:none !important;color:var(--bqg-bordeaux-dark) !important;
		box-shadow:none !important;cursor:pointer;transition:background-color .2s,border-color .2s,color .2s;
	}
	.bqg-modal .bqg-modal__nav:hover,.bqg-modal .bqg-modal__close:hover{background:var(--bqg-bordeaux-soft) !important;border-color:var(--bqg-bordeaux) !important;color:var(--bqg-bordeaux) !important;}
	.bqg-modal .bqg-modal__nav[hidden]{display:none !important;}
	.bqg-modal .bqg-modal__bar .bqg-modal__close.bqg-modal__close{margin-left:auto !important;}
	.bqg-modal button:focus-visible{outline:2px solid var(--bqg-bordeaux) !important;outline-offset:2px;}
	.bqg-modal__body{flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;}

	.bqg-profile{display:grid;grid-template-columns:310px minmax(0,1fr);min-height:100%;}
	.bqg-profile__side{display:flex;flex-direction:column;gap:20px;padding:30px;background:var(--bqg-cat-soft);border-right:1px solid var(--bqg-cat-line);}
	.bqg-profile__photo{position:relative;width:100%;padding-bottom:125%;overflow:hidden;border-radius:14px;background:#fff;box-shadow:0 24px 40px -26px rgba(0,0,0,.55);}
	.bqg-modal .bqg-profile__photo img{position:absolute !important;inset:0;width:100% !important;height:100% !important;max-width:none !important;object-fit:cover !important;object-position:center top;}
	.bqg-profile__initials{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--bqg-serif);font-size:3.6rem;font-weight:600;color:var(--bqg-cat);}
	.bqg-profile__actions{display:flex;flex-direction:column;gap:8px;}
	.bqg-modal .bqg-btn,.bqg-modal .bqg-btn:hover,.bqg-modal .bqg-btn:focus{
		display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:4px 9px;padding:13px 16px;border:1px solid transparent;border-radius:8px;
		background:var(--bqg-bordeaux);background-image:linear-gradient(180deg,var(--bqg-bordeaux),var(--bqg-bordeaux-dark) 170%);
		color:#fff !important;font-family:var(--bqg-sans);font-size:.72rem;font-weight:700;letter-spacing:.09em;line-height:1.2;text-transform:uppercase;text-decoration:none !important;
		box-shadow:0 12px 22px -16px rgba(49,2,12,.9);transition:filter .2s,transform .2s;
	}
	.bqg-modal .bqg-btn:hover{filter:brightness(1.1);transform:translateY(-1px);}
	.bqg-modal .bqg-btn--ghost,.bqg-modal .bqg-btn--ghost:hover,.bqg-modal .bqg-btn--ghost:focus{background:#fff;background-image:none;border-color:var(--bqg-cat-line);color:var(--bqg-cat) !important;box-shadow:none;}
	.bqg-modal .bqg-btn--ghost:hover{border-color:var(--bqg-cat);filter:none;}
	.bqg-btn__meta{font-weight:500;letter-spacing:.02em;text-transform:none;opacity:.8;}

	.bqg-profile__main{padding:34px 40px 40px;}
	.bqg-profile__main .bqg-card__badge{display:inline-block;}
	.bqg-modal .bqg-profile__name{margin:12px 0 6px !important;font-family:var(--bqg-serif) !important;font-size:clamp(1.9rem,3vw,2.6rem) !important;font-weight:600 !important;line-height:1.08 !important;color:var(--bqg-bordeaux-dark) !important;}
	.bqg-profile__main .bqg-card__role{margin:0;}
	.bqg-profile__profession{display:flex;align-items:flex-start;gap:12px;margin:22px 0 0;padding:14px 16px;border:1px solid var(--bqg-line);border-radius:12px;background:#FAF8F7;}
	.bqg-profile__profession svg{flex:0 0 auto;margin-top:2px;color:var(--bqg-cat);}
	.bqg-profile__profession span{font-size:.98rem;font-weight:600;line-height:1.45;color:var(--bqg-ink);}
	.bqg-profile__profession em{display:block;margin-bottom:3px;font-style:normal;font-size:.6rem;font-weight:700;letter-spacing:.13em;text-transform:uppercase;color:var(--bqg-muted);}
	.bqg-profile__section{margin:30px 0 0;font-size:.96rem;line-height:1.75;color:#4d4d4d;}
	.bqg-modal .bqg-profile__section h3{display:flex;align-items:center;gap:12px;margin:0 0 12px !important;font-family:var(--bqg-sans) !important;font-size:.66rem !important;font-weight:700 !important;letter-spacing:.13em !important;text-transform:uppercase;line-height:1.4 !important;color:var(--bqg-cat) !important;}
	.bqg-profile__section h3::after{content:"";flex:1;height:1px;background:var(--bqg-line);}
	.bqg-profile__section p{margin:0 0 .9em;}
	.bqg-profile__section p:last-child{margin-bottom:0;}
	.bqg-profile__section--quote{padding:4px 0 4px 20px;border-left:3px solid var(--bqg-cat);}
	.bqg-profile__section--quote p{font-family:var(--bqg-serif);font-size:1.22rem;line-height:1.55;color:var(--bqg-bordeaux-dark);}
	.bqg-profile__cv ul,.bqg-profile__cv ol{margin:0 0 1em;padding-left:1.25em;}
	.bqg-profile__cv li{margin:.35em 0;}
	.bqg-profile__cv li::marker{color:var(--bqg-cat);}
	.bqg-profile__cv strong{color:var(--bqg-ink);}
	.bqg-modal .bqg-profile__cv a{color:var(--bqg-bordeaux) !important;}

	@media (max-width:760px){
		.bqg-modal{width:100vw;height:100dvh;max-height:100dvh;border-radius:0;}
		.bqg-profile{grid-template-columns:1fr;}
		.bqg-profile__side{display:grid;grid-template-columns:118px minmax(0,1fr);align-items:start;gap:16px;padding:20px;border-right:0;border-bottom:1px solid var(--bqg-cat-line);}
		.bqg-profile__photo{border-radius:10px;}
		.bqg-profile__initials{font-size:2.2rem;}
		.bqg-profile__main{padding:24px 20px 32px;}
	}
	@media (prefers-reduced-motion:reduce){.bqg-modal[open]{animation:none;}.bqg-card__more svg{transition:none;}}

	@media (prefers-reduced-motion:reduce){
		.bqg-card,.bqg-card::after,.bqg-card__img,.bqg-team .bqg-filter{transition:none;animation:none;}
		.bqg-card:hover{transform:none;}
		.bqg-card:hover .bqg-card__img{transform:none;}
	}
	';
}

/* -------------------------------------------------------------------------
 * 10. Filtrage cote navigateur
 * ---------------------------------------------------------------------- */

function bqg_front_js() {
	return <<<'JS'
	(function(){
		function initBlock(root){
			var filters = root.querySelectorAll('.bqg-filter');
			var cards   = root.querySelectorAll('.bqg-card');
			var empty   = root.querySelector('.bqg-noresult');

			if (!filters.length) { return; }

			function apply(value){
				var visible = 0;

				Array.prototype.forEach.call(cards, function(card){
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

			Array.prototype.forEach.call(filters, function(button){
				button.addEventListener('click', function(){
					Array.prototype.forEach.call(filters, function(other){
						other.classList.remove('is-active');
						other.setAttribute('aria-pressed', 'false');
					});

					button.classList.add('is-active');
					button.setAttribute('aria-pressed', 'true');

					apply(button.getAttribute('data-filter'));
				});
			});
		}


		/* Fenêtre de profil */
		function initModal(root){
			var dialog = root.querySelector('.bqg-modal');
			if (!dialog) { return; }

			var body    = dialog.querySelector('[data-bqg-body]');
			var pos     = dialog.querySelector('[data-bqg-pos]');
			var prev    = dialog.querySelector('[data-bqg-prev]');
			var next    = dialog.querySelector('[data-bqg-next]');
			var current = null;

			function visibleCards(){
				return Array.prototype.filter.call(root.querySelectorAll('.bqg-card.is-clickable'), function(c){ return !c.hidden; });
			}

			function show(card){
				var button = card.querySelector('[data-bqg-open]');
				var tpl    = button ? document.getElementById(button.getAttribute('data-bqg-open')) : null;
				if (!tpl) { return; }

				body.innerHTML = '';
				body.appendChild(tpl.content.cloneNode(true));
				body.scrollTop = 0;

				var profile = body.querySelector('.bqg-profile');
				if (profile) { dialog.setAttribute('aria-labelledby', profile.getAttribute('data-title')); dialog.removeAttribute('aria-label'); }

				current = card;
				var list = visibleCards();
				var i    = list.indexOf(card);
				var many = list.length > 1 && i !== -1;
				prev.hidden = next.hidden = !many;
				pos.textContent = many ? (i + 1) + ' / ' + list.length : '';

				if (!dialog.open) {
					if (dialog.showModal) { dialog.showModal(); } else { dialog.setAttribute('open', ''); }
					document.documentElement.classList.add('bqg-lock');
				}

				var slug = card.getAttribute('data-slug');
				if (slug && window.history && history.replaceState) { history.replaceState(null, '', '#' + slug); }
				var close = dialog.querySelector('[data-bqg-close]');
				if (close) { close.focus({ preventScroll: true }); }
			}

			function step(delta){
				var list = visibleCards();
				var i    = list.indexOf(current);
				if (list.length < 2 || i === -1) { return; }
				show(list[(i + delta + list.length) % list.length]);
			}

			function closeModal(){
				if (dialog.close) { dialog.close(); } else { dialog.removeAttribute('open'); onClose(); }
			}

			function onClose(){
				document.documentElement.classList.remove('bqg-lock');
				if (current && window.history && history.replaceState && location.hash === '#' + current.getAttribute('data-slug')) {
					history.replaceState(null, '', location.pathname + location.search);
				}
				var button = current ? current.querySelector('[data-bqg-open]') : null;
				if (button) { button.focus({ preventScroll: true }); }
				current = null;
			}

			dialog.addEventListener('close', onClose);

			root.addEventListener('click', function(e){
				var button = e.target.closest('[data-bqg-open]');
				if (button && root.contains(button)) { show(button.closest('.bqg-card')); }
			});

			dialog.addEventListener('click', function(e){
				if (e.target === dialog) { closeModal(); return; }
				if (e.target.closest('[data-bqg-close]')) { closeModal(); }
				else if (e.target.closest('[data-bqg-prev]')) { step(-1); }
				else if (e.target.closest('[data-bqg-next]')) { step(1); }
			});

			dialog.addEventListener('keydown', function(e){
				if (e.target.closest('a, input, textarea')) { return; }
				if ('ArrowLeft' === e.key) { step(-1); e.preventDefault(); }
				if ('ArrowRight' === e.key) { step(1); e.preventDefault(); }
			});

			// Lien direct vers un membre : /gouvernance/#claire-bonnefoy, à
			// l'arrivée sur la page comme depuis un lien de la même page.
			function openFromHash(){
				var hash = decodeURIComponent((location.hash || '').slice(1));
				if (!hash || (current && current.getAttribute('data-slug') === hash)) { return; }
				var target = root.querySelector('.bqg-card.is-clickable[data-slug="' + hash.replace(/"/g, '') + '"]');
				if (!target) { return; }
				if (target.hidden) {
					var cats = (target.getAttribute('data-cats') || '').split(' ');
					var tab  = Array.prototype.filter.call(root.querySelectorAll('.bqg-filter'), function(b){ return cats.indexOf(b.getAttribute('data-filter')) !== -1; })[0];
					if (tab) { tab.click(); }
				}
				target.scrollIntoView({ block: 'center' });
				show(target);
			}
			openFromHash();
			window.addEventListener('hashchange', openFromHash);

			// Description tronquée sur la carte : fondu seulement si elle dépasse.
			Array.prototype.forEach.call(root.querySelectorAll('.bqg-card.is-clickable .bqg-card__desc'), function(desc){
				if (desc.scrollHeight > desc.clientHeight + 4) { desc.classList.add('is-clamped'); }
			});
		}

		function boot(){
			Array.prototype.forEach.call(document.querySelectorAll('.bqg-team'), initBlock);
			Array.prototype.forEach.call(document.querySelectorAll('.bqg-team'), initModal);
		}

		if ('loading' === document.readyState) {
			document.addEventListener('DOMContentLoaded', boot);
		} else {
			boot();
		}
	})();
	
JS;
}

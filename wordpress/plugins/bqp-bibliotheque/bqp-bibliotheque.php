<?php
/**
 * Plugin Name:       BQP Bibliothèque
 * Description:       Bibliothèque numérique de la Bourse Jean-Michel Quatrepoint : documents, collections, thèmes, personnes, organisations.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * License:           GPL-2.0-or-later
 */

/**
 * BQP Bibliothèque — bibliothèque numérique de la Bourse Jean-Michel Quatrepoint.
 *
 * Étape 1 : back-office. Type de contenu « Document », neuf taxonomies
 * pré-remplies d'après l'arborescence validée, fiches Personnes et
 * Organisations, fiche document complète.
 *
 * Compatible extension classique ET Code Snippets.
 *
 * Version : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BQB_VERSION' ) ) {
	define( 'BQB_VERSION', '1.0.0' );
}
if ( ! defined( 'BQB_CPT' ) ) {
	define( 'BQB_CPT', 'bqb_document' );
}

/* -------------------------------------------------------------------------
 * 1. Palette et helpers
 * ---------------------------------------------------------------------- */

function bqb_palette() {
	return array(
		'bordeaux'      => '#74041C',
		'bordeaux_dark' => '#31020C',
		'navy'          => '#001756',
		'orange'        => '#C75A18',
		'plum'          => '#5A2A4F',
		'teal'          => '#1F5561',
		'ink'           => '#424242',
	);
}

function bqb_hex_to_rgba( $hex, $alpha = 1 ) {
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
 * Les neuf taxonomies : clé => réglages.
 *
 * « public » : a sa propre page, indexable (collections, thèmes, personnes,
 * organisations). Les autres servent de filtres sans créer de pages
 * d'archives pauvres dans l'index Google.
 */
function bqb_taxonomies() {
	return array(
		'bqb_collection'   => array( 'Collections', 'Collection', true, true, 'collection', 'Les espaces de la bibliothèque : d\'où vient le document.' ),
		'bqb_nature'       => array( 'Natures', 'Nature du document', true, false, '', 'Ce qu\'est le document : livre, article, tribune, entretien…' ),
		'bqb_theme'        => array( 'Thèmes', 'Thème', true, true, 'theme', 'De quoi parle le document.' ),
		'bqb_secteur'      => array( 'Secteurs', 'Secteur', true, false, '', 'Les secteurs stratégiques concernés.' ),
		'bqb_pays'         => array( 'Pays et territoires', 'Pays ou territoire', true, false, '', 'Les pays et zones géographiques concernés.' ),
		'bqb_prix'         => array( 'Prix et bourses', 'Prix ou bourse', true, false, '', 'Le programme de la Bourse auquel se rattache le document.' ),
		'bqb_personne'     => array( 'Personnes', 'Personne', false, true, 'personne', 'Auteurs, lauréats, experts.' ),
		'bqb_organisation' => array( 'Organisations', 'Organisation', false, true, 'organisation', 'Partenaires, institutions, éditeurs, sources.' ),
		'bqb_motcle'       => array( 'Mots-clés', 'Mot-clé', false, false, '', 'Mots-clés libres pour la recherche.' ),
	);
}

/* -------------------------------------------------------------------------
 * 2. Type de contenu et taxonomies
 * ---------------------------------------------------------------------- */

add_action( 'init', 'bqb_register_content' );
function bqb_register_content() {

	register_post_type(
		BQB_CPT,
		array(
			'labels'              => array(
				'name'                  => 'Bibliothèque',
				'singular_name'         => 'Document',
				'menu_name'             => 'Bibliothèque',
				'all_items'             => 'Tous les documents',
				'add_new'               => 'Ajouter un document',
				'add_new_item'          => 'Ajouter un document',
				'edit_item'             => 'Modifier le document',
				'new_item'              => 'Nouveau document',
				'view_item'             => 'Voir le document',
				'view_items'            => 'Voir les documents',
				'search_items'          => 'Rechercher un document',
				'not_found'             => 'Aucun document pour le moment.',
				'not_found_in_trash'    => 'Aucun document dans la corbeille.',
				'featured_image'        => 'Couverture ou visuel',
				'set_featured_image'    => 'Choisir la couverture',
				'remove_featured_image' => 'Retirer la couverture',
				'use_featured_image'    => 'Utiliser comme couverture',
				'item_published'        => 'Document publié.',
				'item_updated'          => 'Document mis à jour.',
			),
			// Public : chaque document a sa page, indexable, avec son résumé.
			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_position'       => 25,
			'menu_icon'           => 'dashicons-book-alt',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'rewrite'             => array( 'slug' => 'bibliotheque', 'with_front' => false ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);

	foreach ( bqb_taxonomies() as $key => $t ) {
		list( $plural, $singular, $hierarchical, $public, $slug ) = $t;

		register_taxonomy(
			$key,
			BQB_CPT,
			array(
				'labels'             => array(
					'name'                       => $plural,
					'singular_name'              => $singular,
					'menu_name'                  => $plural,
					'all_items'                  => 'Liste complète',
					'edit_item'                  => 'Modifier',
					'view_item'                  => 'Voir',
					'update_item'                => 'Mettre à jour',
					'add_new_item'               => 'Ajouter',
					'new_item_name'              => 'Nouveau nom',
					'search_items'               => 'Rechercher',
					'popular_items'              => 'Les plus utilisés',
					'separate_items_with_commas' => 'Séparez par des virgules',
					'add_or_remove_items'        => 'Ajouter ou retirer',
					'choose_from_most_used'      => 'Choisir parmi les plus utilisés',
					'not_found'                  => 'Aucun résultat.',
					'back_to_items'              => '← Retour',
				),
				// Listes fermées en cases à cocher (pas de faute de frappe),
				// listes longues en saisie avec autocomplétion.
				'hierarchical'       => $hierarchical,
				'public'             => $public,
				'publicly_queryable' => $public,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_admin_column'  => in_array( $key, array( 'bqb_collection', 'bqb_nature' ), true ),
				'show_in_nav_menus'  => $public,
				'show_tagcloud'      => false,
				'show_in_rest'       => false,
				'query_var'          => $key,
				'rewrite'            => $public ? array( 'slug' => $slug, 'with_front' => false, 'hierarchical' => $hierarchical ) : false,
			)
		);
	}
}

/**
 * Le type Document étant public, ses adresses doivent être enregistrées
 * une fois. Code Snippets n'a pas de hook d'activation : on le fait ici,
 * une seule fois par version.
 */
add_action( 'init', 'bqb_maybe_flush_rewrite', 99 );
function bqb_maybe_flush_rewrite() {
	if ( get_option( 'bqb_rewrite_version' ) === BQB_VERSION ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'bqb_rewrite_version', BQB_VERSION, false );
}

/**
 * Dans les arborescences (collections, thèmes), une case cochée ne remonte
 * plus en tête de liste : l'enfant reste sous son parent, lisible.
 */
add_filter( 'wp_terms_checklist_args', 'bqb_checklist_args', 10, 2 );
function bqb_checklist_args( $args, $post_id ) {
	if ( isset( $args['taxonomy'] ) && in_array( $args['taxonomy'], array( 'bqb_collection', 'bqb_theme' ), true ) ) {
		$args['checked_ontop'] = false;
	}

	return $args;
}

add_filter( 'enter_title_here', 'bqb_title_placeholder', 10, 2 );
function bqb_title_placeholder( $text, $post ) {
	if ( $post && BQB_CPT === $post->post_type ) {
		return 'Titre exact du document';
	}

	return $text;
}

/**
 * Libellé au-dessus de l'éditeur : c'est la notice éditoriale.
 */
add_action( 'edit_form_after_title', 'bqb_editor_heading' );
function bqb_editor_heading( $post ) {
	if ( BQB_CPT !== $post->post_type ) {
		return;
	}

	echo '<div class="bqb-editor-head">'
		. '<span class="bqb-admin__label">Contexte et notice éditoriale</span>'
		. '<span class="bqb-admin__hint">Présentation du document, contexte de publication, intérêt pour le lecteur. Facultatif, mais c\'est ce texte qui donne de la valeur à la page aux yeux de Google.</span>'
		. '</div>';
}

/* -------------------------------------------------------------------------
 * 3. L'arborescence validée, créée automatiquement
 * ---------------------------------------------------------------------- */

/**
 * Raccourci pour décrire un terme de l'arbre.
 */
function bqb_t( $name, $children = array(), $description = '', $meta = array() ) {
	return array(
		'name'        => $name,
		'children'    => $children,
		'description' => $description,
		'meta'        => $meta,
	);
}

function bqb_seed_data() {
	$p = bqb_palette();

	return array(

		'bqb_collection' => array(
			bqb_t(
				'Fonds Jean-Michel Quatrepoint',
				array(
					bqb_t( 'Écrits de Jean-Michel Quatrepoint', array(), 'Livres, articles, chroniques, tribunes, notes, rapports, entretiens, conférences et interventions.', array( 'bqb_ordre' => 1 ) ),
					bqb_t( 'Fonds documentaire', array(), 'Archives de presse, rapports, documents institutionnels, documents d\'entreprises, sources historiques et documents de travail conservés dans son fonds.', array( 'bqb_ordre' => 2 ) ),
				),
				'Les écrits et les archives de Jean-Michel Quatrepoint, ainsi que les documents conservés dans son fonds.',
				array( 'bqb_ordre' => 1, 'bqb_couleur' => $p['bordeaux'] )
			),
			bqb_t(
				'Travaux des lauréats',
				array(
					bqb_t( 'Travaux primés', array(), 'Les travaux distingués par la Bourse, par lauréat et par édition.', array( 'bqb_ordre' => 1 ) ),
					bqb_t( 'Travaux ultérieurs', array(), 'Les recherches menées par les lauréats après leur distinction.', array( 'bqb_ordre' => 2 ) ),
					bqb_t( 'Publications associées', array(), 'Articles, ouvrages, entretiens et autres travaux des lauréats.', array( 'bqb_ordre' => 3 ) ),
				),
				'Les recherches soutenues par la Bourse et leurs prolongements.',
				array( 'bqb_ordre' => 2, 'bqb_couleur' => $p['navy'] )
			),
			bqb_t(
				'Publications de la Bourse',
				array(
					bqb_t( 'Les Cahiers de la Bourse Jean-Michel Quatrepoint', array(), 'La collection éditoriale de référence de la Bourse.', array( 'bqb_ordre' => 1 ) ),
					bqb_t( 'Autres collections', array(), 'Les autres séries éditoriales de la Bourse.', array( 'bqb_ordre' => 2 ) ),
				),
				'La production éditoriale propre à l\'association.',
				array( 'bqb_ordre' => 3, 'bqb_couleur' => $p['orange'] )
			),
			bqb_t(
				'Contributions et partenaires',
				array(
					bqb_t( 'Contributions d\'experts', array(), 'Les travaux d\'experts associés à la Bourse.', array( 'bqb_ordre' => 1 ) ),
					bqb_t( 'Publications de partenaires', array(), 'Les publications des organisations partenaires.', array( 'bqb_ordre' => 2 ) ),
					bqb_t( 'Entretiens', array(), 'Entretiens avec des experts, des lauréats et des partenaires.', array( 'bqb_ordre' => 3 ) ),
					bqb_t( 'Tribunes invitées', array(), 'Les tribunes signées par des auteurs invités.', array( 'bqb_ordre' => 4 ) ),
					bqb_t( 'Études partenaires', array(), 'Les études produites avec ou par les partenaires.', array( 'bqb_ordre' => 5 ) ),
					bqb_t( 'Documents institutionnels', array(), 'Documents produits par les organisations et institutions.', array( 'bqb_ordre' => 6 ) ),
				),
				'Les travaux d\'experts, chercheurs, organisations et partenaires associés à la Bourse.',
				array( 'bqb_ordre' => 4, 'bqb_couleur' => $p['plum'] )
			),
		),

		'bqb_nature' => array(
			bqb_t( 'Livre ou ouvrage' ), bqb_t( 'Article' ), bqb_t( 'Chronique' ), bqb_t( 'Tribune' ),
			bqb_t( 'Note' ), bqb_t( 'Note de recherche' ), bqb_t( 'Rapport' ), bqb_t( 'Étude' ),
			bqb_t( 'Synthèse' ), bqb_t( 'Cahier' ), bqb_t( 'Dossier thématique' ), bqb_t( 'Entretien' ),
			bqb_t( 'Conférence ou intervention' ), bqb_t( 'Actes de colloque' ), bqb_t( 'Archive de presse' ),
			bqb_t( 'Document institutionnel' ), bqb_t( 'Document d\'entreprise' ), bqb_t( 'Document de travail' ),
			bqb_t( 'Source historique' ), bqb_t( 'Captation audio ou vidéo' ), bqb_t( 'Autre' ),
		),

		'bqb_theme' => array(
			bqb_t(
				'Souveraineté',
				array(
					bqb_t( 'Souveraineté industrielle' ), bqb_t( 'Souveraineté économique' ), bqb_t( 'Souveraineté technologique' ),
					bqb_t( 'Souveraineté énergétique' ), bqb_t( 'Souveraineté alimentaire' ), bqb_t( 'Souveraineté numérique' ),
					bqb_t( 'Souveraineté financière' ),
				),
				'La capacité d\'un pays à décider et à produire par lui-même dans les domaines stratégiques.',
				array( 'bqb_ordre' => 1 )
			),
			bqb_t(
				'Industrie',
				array(
					bqb_t( 'Réindustrialisation' ), bqb_t( 'Entreprises stratégiques' ), bqb_t( 'Capital' ), bqb_t( 'Filières' ),
					bqb_t( 'PME et ETI' ), bqb_t( 'Grands groupes' ), bqb_t( 'Innovation' ),
				),
				'L\'appareil productif, ses entreprises, ses filières et son financement.',
				array( 'bqb_ordre' => 2 )
			),
			bqb_t(
				'Puissance et relations internationales',
				array(
					bqb_t( 'Mondialisation' ), bqb_t( 'Commerce' ), bqb_t( 'Géopolitique' ), bqb_t( 'Guerre économique' ),
					bqb_t( 'Influence' ), bqb_t( 'Dépendances' ),
				),
				'Les rapports de force économiques entre nations et blocs.',
				array( 'bqb_ordre' => 3 )
			),
			bqb_t(
				'État et société',
				array(
					bqb_t( 'Politique publique' ), bqb_t( 'Finances publiques' ), bqb_t( 'Territoires' ), bqb_t( 'Travail' ),
					bqb_t( 'Compétences' ), bqb_t( 'Formation' ), bqb_t( 'Recherche' ), bqb_t( 'Administration' ),
					bqb_t( 'Régulation' ), bqb_t( 'Normes' ),
				),
				'L\'action publique, le travail, la formation et la régulation.',
				array( 'bqb_ordre' => 4 )
			),
		),

		'bqb_secteur' => array(
			bqb_t( 'Défense' ), bqb_t( 'Énergie' ), bqb_t( 'Nucléaire' ), bqb_t( 'Aéronautique' ),
			bqb_t( 'Automobile' ), bqb_t( 'Télécoms' ), bqb_t( 'Numérique' ), bqb_t( 'Santé' ),
			bqb_t( 'Agroalimentaire' ), bqb_t( 'Matières premières' ), bqb_t( 'Transports' ), bqb_t( 'Spatial' ),
		),

		'bqb_pays' => array(
			bqb_t( 'France' ), bqb_t( 'Europe' ), bqb_t( 'États-Unis' ), bqb_t( 'Chine' ), bqb_t( 'Russie' ),
		),

		'bqb_prix' => array(
			bqb_t( 'Grand Prix Jean-Michel Quatrepoint' ),
			bqb_t( 'Bourses Jeunes Chercheurs' ),
			bqb_t( 'Prix de l\'Essai et du Débat public' ),
		),

		'bqb_personne' => array(
			bqb_t( 'Jean-Michel Quatrepoint', array(), '', array( 'bqb_roles' => array( 'auteur' ) ) ),
		),
	);
}

/**
 * Crée un niveau de l'arbre, puis ses enfants.
 */
function bqb_seed_tree( $taxonomy, $terms, $parent = 0 ) {
	foreach ( $terms as $term ) {
		$slug   = sanitize_title( $term['name'] );
		$exists = term_exists( $slug, $taxonomy, $parent );

		if ( $exists ) {
			$term_id = (int) ( is_array( $exists ) ? $exists['term_id'] : $exists );
		} else {
			$created = wp_insert_term(
				$term['name'],
				$taxonomy,
				array(
					'slug'        => $slug,
					'parent'      => (int) $parent,
					'description' => $term['description'],
				)
			);

			if ( is_wp_error( $created ) ) {
				continue;
			}

			$term_id = (int) $created['term_id'];

			foreach ( $term['meta'] as $key => $value ) {
				update_term_meta( $term_id, $key, $value );
			}
		}

		if ( $term['children'] ) {
			bqb_seed_tree( $taxonomy, $term['children'], $term_id );
		}
	}
}

add_action( 'admin_init', 'bqb_maybe_seed' );
function bqb_maybe_seed() {
	if ( get_option( 'bqb_seeded' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	foreach ( bqb_seed_data() as $taxonomy => $terms ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			bqb_seed_tree( $taxonomy, $terms );
		}
	}

	update_option( 'bqb_seeded', BQB_VERSION, false );
}

/* -------------------------------------------------------------------------
 * 4. Champs des fiches : collections, thèmes, personnes, organisations
 * ---------------------------------------------------------------------- */

function bqb_person_roles() {
	return array(
		'auteur'     => 'Auteur',
		'laureat'    => 'Lauréat',
		'expert'     => 'Expert',
		'partenaire' => 'Représentant d\'un partenaire',
	);
}

function bqb_org_types() {
	return array(
		'partenaire'    => 'Partenaire de la Bourse',
		'institution'   => 'Institution publique',
		'etablissement' => 'Établissement d\'enseignement ou de recherche',
		'editeur'       => 'Éditeur',
		'media'         => 'Média',
		'entreprise'    => 'Entreprise',
		'autre'         => 'Autre',
	);
}

/**
 * Les champs ajoutés à chaque taxonomie : clé, type, libellé, aide.
 */
function bqb_term_fields() {
	return array(
		'bqb_collection'   => array(
			array( 'bqb_ordre', 'number', 'Ordre d\'affichage', 'Plus petit en premier. Sert à ordonner les blocs sur le site.' ),
			array( 'bqb_couleur', 'color', 'Couleur du bloc', 'Utile uniquement pour les quatre espaces de premier niveau.' ),
		),
		'bqb_theme'        => array(
			array( 'bqb_ordre', 'number', 'Ordre d\'affichage', 'Plus petit en premier.' ),
		),
		'bqb_personne'     => array(
			array( 'bqb_roles', 'roles', 'Rôles', 'Une personne peut avoir plusieurs rôles. « Lauréat » la fait apparaître dans la liste des lauréats.' ),
			array( 'bqb_photo', 'image', 'Photo', 'Portrait, idéalement carré.' ),
			array( 'bqb_bio', 'textarea', 'Biographie', 'Quelques lignes : parcours, fonctions, travaux.' ),
			array( 'bqb_annee_prix', 'number', 'Année du prix', 'Pour les lauréats uniquement.' ),
			array( 'bqb_prix_id', 'prix', 'Prix ou bourse obtenu', 'Pour les lauréats uniquement.' ),
			array( 'bqb_site', 'url', 'Page personnelle ou profil', 'Site, page universitaire, LinkedIn…' ),
		),
		'bqb_organisation' => array(
			array( 'bqb_type_orga', 'orgtype', 'Type d\'organisation', '' ),
			array( 'bqb_logo', 'image', 'Logo', 'PNG ou SVG sur fond transparent.' ),
			array( 'bqb_site', 'url', 'Site internet', '' ),
		),
	);
}

/**
 * Le champ lui-même, identique à l'ajout et à la modification.
 */
function bqb_term_field_input( $field, $value ) {
	list( $key, $type ) = $field;

	switch ( $type ) {
		case 'number':
			return '<input type="number" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" step="1" style="width:120px;" />';

		case 'color':
			return '<input type="color" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $value ? $value : '#74041C' ) . '" />';

		case 'url':
			return '<input type="url" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" placeholder="https://" />';

		case 'textarea':
			return '<textarea name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" rows="5">' . esc_textarea( $value ) . '</textarea>';

		case 'roles':
			$value = is_array( $value ) ? $value : array();
			$html  = '<span class="bqb-checks">';
			foreach ( bqb_person_roles() as $role => $label ) {
				$html .= '<label><input type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $role ) . '"' . checked( in_array( $role, $value, true ), true, false ) . ' /> ' . esc_html( $label ) . '</label>';
			}
			return $html . '</span>';

		case 'orgtype':
			$html = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '"><option value="">Choisir</option>';
			foreach ( bqb_org_types() as $slug => $label ) {
				$html .= '<option value="' . esc_attr( $slug ) . '"' . selected( $value, $slug, false ) . '>' . esc_html( $label ) . '</option>';
			}
			return $html . '</select>';

		case 'prix':
			$terms = get_terms( array( 'taxonomy' => 'bqb_prix', 'hide_empty' => false ) );
			$html  = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '"><option value="">Aucun</option>';
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$html .= '<option value="' . (int) $term->term_id . '"' . selected( (int) $value, (int) $term->term_id, false ) . '>' . esc_html( $term->name ) . '</option>';
				}
			}
			return $html . '</select>';

		case 'image':
			$src  = $value ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : '';
			$html = '<span class="bqb-media" data-media>';
			$html .= '<img src="' . esc_url( $src ) . '" alt="" data-media-preview' . ( $src ? '' : ' hidden' ) . ' />';
			$html .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" data-media-id />';
			$html .= '<button type="button" class="button" data-media-select>' . ( $src ? 'Remplacer' : 'Choisir une image' ) . '</button> ';
			$html .= '<button type="button" class="button-link bqb-remove" data-media-remove' . ( $src ? '' : ' hidden' ) . '>Retirer</button>';
			return $html . '</span>';
	}

	return '';
}

function bqb_term_add_fields( $taxonomy ) {
	$fields = bqb_term_fields();

	if ( empty( $fields[ $taxonomy ] ) ) {
		return;
	}

	foreach ( $fields[ $taxonomy ] as $field ) {
		echo '<div class="form-field bqb-term-field">'
			. '<label for="' . esc_attr( $field[0] ) . '">' . esc_html( $field[2] ) . '</label>'
			. bqb_term_field_input( $field, '' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. ( $field[3] ? '<p>' . esc_html( $field[3] ) . '</p>' : '' )
			. '</div>';
	}

	wp_nonce_field( 'bqb_term_meta', 'bqb_term_nonce' );
}

function bqb_term_edit_fields( $term, $taxonomy ) {
	$fields = bqb_term_fields();

	if ( empty( $fields[ $taxonomy ] ) ) {
		return;
	}

	foreach ( $fields[ $taxonomy ] as $field ) {
		$value = get_term_meta( $term->term_id, $field[0], true );

		echo '<tr class="form-field bqb-term-field">'
			. '<th scope="row"><label for="' . esc_attr( $field[0] ) . '">' . esc_html( $field[2] ) . '</label></th>'
			. '<td>' . bqb_term_field_input( $field, $value ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. ( $field[3] ? '<p class="description">' . esc_html( $field[3] ) . '</p>' : '' )
			. '</td></tr>';
	}

	wp_nonce_field( 'bqb_term_meta', 'bqb_term_nonce' );
}

function bqb_save_term_fields( $term_id, $tt_id = 0, $taxonomy = '' ) {
	if ( ! isset( $_POST['bqb_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bqb_term_nonce'] ) ), 'bqb_term_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	$term = get_term( $term_id );
	if ( ! $term || is_wp_error( $term ) ) {
		return;
	}

	$fields = bqb_term_fields();
	if ( empty( $fields[ $term->taxonomy ] ) ) {
		return;
	}

	foreach ( $fields[ $term->taxonomy ] as $field ) {
		list( $key, $type ) = $field;
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		switch ( $type ) {
			case 'number':
			case 'image':
			case 'prix':
				$value = ( '' === $raw ) ? '' : (int) $raw;
				break;
			case 'color':
				$value = sanitize_hex_color( (string) $raw );
				break;
			case 'url':
				$value = esc_url_raw( trim( (string) $raw ) );
				break;
			case 'textarea':
				$value = sanitize_textarea_field( (string) $raw );
				break;
			case 'roles':
				$value = array_values( array_intersect( array_map( 'sanitize_key', (array) $raw ), array_keys( bqb_person_roles() ) ) );
				break;
			case 'orgtype':
				$value = array_key_exists( (string) $raw, bqb_org_types() ) ? (string) $raw : '';
				break;
			default:
				$value = sanitize_text_field( (string) $raw );
		}

		if ( '' === $value || array() === $value || null === $value ) {
			delete_term_meta( $term_id, $key );
		} else {
			update_term_meta( $term_id, $key, $value );
		}
	}
}

add_action( 'init', 'bqb_hook_term_fields', 20 );
function bqb_hook_term_fields() {
	foreach ( array_keys( bqb_term_fields() ) as $taxonomy ) {
		add_action( $taxonomy . '_add_form_fields', 'bqb_term_add_fields' );
		add_action( $taxonomy . '_edit_form_fields', 'bqb_term_edit_fields', 10, 2 );
		add_action( 'created_' . $taxonomy, 'bqb_save_term_fields', 10, 3 );
		add_action( 'edited_' . $taxonomy, 'bqb_save_term_fields', 10, 3 );
	}
}

/**
 * Colonne « Rôles » dans la liste des personnes : on repère les lauréats
 * d'un coup d'œil.
 */
add_filter( 'manage_edit-bqb_personne_columns', 'bqb_person_columns' );
function bqb_person_columns( $columns ) {
	$new = array();

	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;

		if ( 'name' === $key ) {
			$new['bqb_roles'] = 'Rôles';
		}
	}

	return $new;
}

add_filter( 'manage_bqb_personne_custom_column', 'bqb_person_column_content', 10, 3 );
function bqb_person_column_content( $content, $column, $term_id ) {
	if ( 'bqb_roles' !== $column ) {
		return $content;
	}

	$roles  = get_term_meta( $term_id, 'bqb_roles', true );
	$labels = array();

	foreach ( (array) $roles as $role ) {
		$all = bqb_person_roles();
		if ( isset( $all[ $role ] ) ) {
			$labels[] = $all[ $role ];
		}
	}

	$year = get_term_meta( $term_id, 'bqb_annee_prix', true );

	return esc_html( implode( ', ', $labels ) ) . ( $year ? ' <span style="color:#787c82;">(' . (int) $year . ')</span>' : '' );
}

/* -------------------------------------------------------------------------
 * 5. Fiche du document
 * ---------------------------------------------------------------------- */

function bqb_access_levels() {
	return array(
		'public'      => 'Public, téléchargement libre',
		'adherents'   => 'Réservé aux adhérents',
		'partenaires' => 'Réservé aux partenaires',
		'sur-place'   => 'Consultation sur place uniquement',
	);
}

function bqb_months() {
	return array( 1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' );
}

/**
 * Le résumé, placé juste sous le titre : c'est le champ le plus important.
 * Il utilise l'extrait natif de WordPress, que Rank Math reprend pour la
 * meta description quand elle n'est pas renseignée.
 */
add_action( 'edit_form_after_title', 'bqb_render_summary', 5 );
function bqb_render_summary( $post ) {
	if ( BQB_CPT !== $post->post_type ) {
		return;
	}

	// Sur l'écran d'édition, $post->post_excerpt est déjà échappé par
	// WordPress : on relit la valeur brute pour ne pas l'échapper deux fois.
	$excerpt = (string) get_post_field( 'post_excerpt', $post->ID, 'raw' );
	$count   = function_exists( 'mb_strlen' ) ? mb_strlen( $excerpt ) : strlen( $excerpt );

	echo '<div class="bqb-summary">'
		. '<label class="bqb-admin__label" for="bqb-excerpt">Résumé <em class="bqb-req">recommandé</em></label>'
		. '<textarea name="excerpt" id="bqb-excerpt" rows="4" class="bqb-admin__input" placeholder="En quelques phrases : de quoi parle ce document, ce qu\'il apporte, à qui il s\'adresse.">' . esc_textarea( $excerpt ) . '</textarea>'
		. '<span class="bqb-admin__hint"><span id="bqb-excerpt-count">' . (int) $count . '</span> caractères. Idéalement entre 300 et 600. Affiché dans les résultats de recherche du site et repris par Google.</span>'
		. '</div>';
}

add_action( 'add_meta_boxes', 'bqb_add_meta_boxes' );
function bqb_add_meta_boxes() {
	remove_meta_box( 'postexcerpt', BQB_CPT, 'normal' );

	add_meta_box( 'bqb_details', 'Fiche du document', 'bqb_render_details_box', BQB_CPT, 'normal', 'high' );
	add_meta_box( 'bqb_related', 'Documents associés', 'bqb_render_related_box', BQB_CPT, 'normal', 'default' );
}

function bqb_render_details_box( $post ) {
	wp_nonce_field( 'bqb_save_document', 'bqb_document_nonce' );

	$m = function ( $key ) use ( $post ) {
		return get_post_meta( $post->ID, $key, true );
	};

	$annee   = $m( '_bqb_annee' );
	$mois    = (int) $m( '_bqb_mois' );
	$jour    = $m( '_bqb_jour' );
	$edition = $m( '_bqb_edition' );
	$file_id = (int) $m( '_bqb_fichier' );
	$lien    = $m( '_bqb_lien' );
	$ref     = $m( '_bqb_reference' );
	$acces   = $m( '_bqb_acces' );
	$droits  = $m( '_bqb_droits' );

	$file_name = '';
	$file_meta = '';
	if ( $file_id ) {
		$path      = get_attached_file( $file_id );
		$file_name = $path ? wp_basename( $path ) : '';
		$file_meta = bqb_file_label( $file_id );
	}

	$html = '<div class="bqb-admin">';

	/* Date */
	$html .= '<div class="bqb-admin__section"><span class="bqb-admin__label">Date de publication</span>';
	$html .= '<div class="bqb-date">';
	$html .= '<input type="number" class="bqb-admin__input bqb-date__year" name="bqb_annee" value="' . esc_attr( $annee ) . '" min="1800" max="2100" placeholder="Ex. 1989" />';
	$html .= '<select class="bqb-admin__input" name="bqb_mois"><option value="">Mois (facultatif)</option>';
	foreach ( bqb_months() as $num => $label ) {
		$html .= '<option value="' . (int) $num . '"' . selected( $mois, $num, false ) . '>' . esc_html( ucfirst( $label ) ) . '</option>';
	}
	$html .= '</select>';
	$html .= '<input type="number" class="bqb-admin__input bqb-date__day" name="bqb_jour" value="' . esc_attr( $jour ) . '" min="1" max="31" placeholder="Jour" />';
	$html .= '</div>';
	$html .= '<span class="bqb-admin__hint">L\'année seule suffit pour une archive ou un ouvrage. Sert au tri et à la chronologie.</span></div>';

	/* Accès */
	$html .= '<div class="bqb-admin__grid2">';

	$html .= '<p class="bqb-admin__field"><label class="bqb-admin__label" for="bqb_acces">Niveau d\'accès</label>';
	$html .= '<select class="bqb-admin__input" name="bqb_acces" id="bqb_acces">';
	foreach ( bqb_access_levels() as $slug => $label ) {
		$html .= '<option value="' . esc_attr( $slug ) . '"' . selected( $acces ? $acces : 'public', $slug, false ) . '>' . esc_html( $label ) . '</option>';
	}
	$html .= '</select>';
	$html .= '<span class="bqb-admin__hint">Affiché en badge sur le site. Voir l\'avertissement ci-dessous pour les fichiers réservés.</span></p>';

	$html .= '<p class="bqb-admin__field"><label class="bqb-admin__label" for="bqb_edition">Édition du prix</label>';
	$html .= '<input type="number" class="bqb-admin__input" name="bqb_edition" id="bqb_edition" value="' . esc_attr( $edition ) . '" min="1990" max="2100" placeholder="Ex. 2026" />';
	$html .= '<span class="bqb-admin__hint">Pour les travaux primés uniquement : l\'année de l\'édition qui l\'a distingué.</span></p>';

	$html .= '</div>';

	/* Fichier */
	$html .= '<div class="bqb-admin__section"><span class="bqb-admin__label">Fichier</span>';
	$html .= '<div class="bqb-file" data-file>';
	$html .= '<input type="hidden" name="bqb_fichier" value="' . esc_attr( $file_id ? $file_id : '' ) . '" data-file-id />';
	$html .= '<span class="bqb-file__icon dashicons dashicons-media-document"></span>';
	$html .= '<span class="bqb-file__info"><strong data-file-name>' . esc_html( $file_name ? $file_name : 'Aucun fichier' ) . '</strong><em data-file-meta>' . esc_html( $file_meta ) . '</em></span>';
	$html .= '<button type="button" class="button" data-file-select>' . ( $file_id ? 'Remplacer' : 'Choisir un fichier' ) . '</button>';
	$html .= '<button type="button" class="button-link bqb-remove" data-file-remove' . ( $file_id ? '' : ' hidden' ) . '>Retirer</button>';
	$html .= '</div>';
	$html .= '<span class="bqb-admin__hint">PDF, audio, image ou archive, depuis la médiathèque. Pour une vidéo, préférez un lien YouTube ou Vimeo ci-dessous.</span>';
	$html .= '<div class="bqb-warning" data-access-warning' . ( $acces && 'public' !== $acces ? '' : ' hidden' ) . '><strong>Attention :</strong> un fichier de la médiathèque est accessible à toute personne qui connaît son adresse. Pour un document réservé, n\'envoyez pas encore le fichier : la protection des fichiers arrive avec l\'étape suivante. Renseignez la fiche, le badge « réservé » s\'affichera.</div>';
	$html .= '</div>';

	/* Lien, référence, droits */
	$html .= '<p class="bqb-admin__field"><label class="bqb-admin__label" for="bqb_lien">Lien externe</label>';
	$html .= '<input type="url" class="bqb-admin__input" name="bqb_lien" id="bqb_lien" value="' . esc_attr( $lien ) . '" placeholder="https://" />';
	$html .= '<span class="bqb-admin__hint">Source d\'origine, page de l\'éditeur, Gallica, vidéo YouTube ou Vimeo…</span></p>';

	$html .= '<p class="bqb-admin__field"><label class="bqb-admin__label" for="bqb_reference">Référence bibliographique</label>';
	$html .= '<textarea class="bqb-admin__input" name="bqb_reference" id="bqb_reference" rows="2" placeholder="Éditions du Seuil, Paris, 1989, 312 p. ISBN 978-2-02-…">' . esc_textarea( $ref ) . '</textarea>';
	$html .= '<span class="bqb-admin__hint">Éditeur, revue, numéro, pages, ISBN : ce qui permet de citer le document.</span></p>';

	$html .= '<p class="bqb-admin__field"><label class="bqb-admin__label" for="bqb_droits">Mention de droits</label>';
	$html .= '<input type="text" class="bqb-admin__input" name="bqb_droits" id="bqb_droits" value="' . esc_attr( $droits ) . '" placeholder="Reproduction autorisée avec mention de la source" />';
	$html .= '</p>';

	$html .= '<div class="bqb-admin__remind"><span class="dashicons dashicons-info-outline"></span> Collection, nature, thèmes, secteurs, pays, personnes, organisations et mots-clés se renseignent dans les encadrés à droite de l\'écran.</div>';

	$html .= '</div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * « PDF · 6,4 Mo » à partir d'une pièce de la médiathèque.
 */
function bqb_file_label( $file_id ) {
	$path = get_attached_file( (int) $file_id );

	if ( ! $path ) {
		return '';
	}

	$ext  = strtoupper( pathinfo( $path, PATHINFO_EXTENSION ) );
	$size = file_exists( $path ) ? size_format( filesize( $path ), 1 ) : '';

	return trim( $ext . ( $size ? ' · ' . $size : '' ) );
}

function bqb_render_related_box( $post ) {
	$ids = get_post_meta( $post->ID, '_bqb_associes', true );
	$ids = is_array( $ids ) ? array_filter( array_map( 'intval', $ids ) ) : array();

	$html  = '<div class="bqb-admin bqb-related" data-related data-current="' . (int) $post->ID . '">';
	$html .= '<div class="bqb-related__search">';
	$html .= '<span class="dashicons dashicons-search"></span>';
	$html .= '<input type="search" class="bqb-admin__input" placeholder="Rechercher un document par son titre…" data-related-input autocomplete="off" />';
	$html .= '<div class="bqb-related__results" data-related-results hidden></div>';
	$html .= '</div>';
	$html .= '<ul class="bqb-related__list" data-related-list>';

	foreach ( $ids as $id ) {
		if ( BQB_CPT !== get_post_type( $id ) ) {
			continue;
		}

		$year  = get_post_meta( $id, '_bqb_annee', true );
		$html .= '<li><input type="hidden" name="bqb_associes[]" value="' . (int) $id . '" />'
			. '<span>' . esc_html( get_the_title( $id ) ) . ( $year ? ' <em>(' . (int) $year . ')</em>' : '' ) . '</span>'
			. '<button type="button" class="bqb-related__remove" aria-label="Retirer">×</button></li>';
	}

	$html .= '</ul>';
	$html .= '<input type="hidden" name="bqb_associes_present" value="1" />';
	$html .= '<span class="bqb-admin__hint">Documents liés à celui-ci : une réponse, une suite, une traduction, l\'étude citée… Ils s\'afficheront en bas de la fiche.</span>';
	$html .= '</div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Recherche de documents pour le champ « Documents associés ».
 */
add_action( 'wp_ajax_bqb_search_documents', 'bqb_ajax_search_documents' );
function bqb_ajax_search_documents() {
	check_ajax_referer( 'bqb_related', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error();
	}

	$term    = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$exclude = isset( $_GET['exclude'] ) ? absint( $_GET['exclude'] ) : 0;

	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( array() );
	}

	$posts = get_posts(
		array(
			'post_type'        => BQB_CPT,
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			's'                => $term,
			'numberposts'      => 12,
			'exclude'          => $exclude ? array( $exclude ) : array(),
			'suppress_filters' => false,
		)
	);

	$out = array();
	foreach ( $posts as $p ) {
		$out[] = array(
			'id'    => (int) $p->ID,
			'title' => html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ),
			'year'  => (string) get_post_meta( $p->ID, '_bqb_annee', true ),
		);
	}

	wp_send_json_success( $out );
}

add_action( 'save_post_' . BQB_CPT, 'bqb_save_document', 10, 2 );
function bqb_save_document( $post_id, $post ) {
	if ( ! isset( $_POST['bqb_document_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bqb_document_nonce'] ) ), 'bqb_save_document' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$int = function ( $key, $min, $max ) {
		if ( ! isset( $_POST[ $key ] ) || '' === $_POST[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return '';
		}
		$n = (int) $_POST[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification
		return ( $n >= $min && $n <= $max ) ? $n : '';
	};

	$annee = $int( 'bqb_annee', 1000, 2100 );
	$mois  = $int( 'bqb_mois', 1, 12 );
	$jour  = $int( 'bqb_jour', 1, 31 );

	// Un jour sans mois n'a pas de sens.
	if ( ! $mois ) {
		$jour = '';
	}

	$values = array(
		'_bqb_annee'     => $annee,
		'_bqb_mois'      => $mois,
		'_bqb_jour'      => $jour,
		'_bqb_edition'   => $int( 'bqb_edition', 1900, 2100 ),
		'_bqb_fichier'   => isset( $_POST['bqb_fichier'] ) ? absint( $_POST['bqb_fichier'] ) : '',
		'_bqb_lien'      => isset( $_POST['bqb_lien'] ) ? esc_url_raw( trim( wp_unslash( $_POST['bqb_lien'] ) ) ) : '',
		'_bqb_reference' => isset( $_POST['bqb_reference'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bqb_reference'] ) ) : '',
		'_bqb_droits'    => isset( $_POST['bqb_droits'] ) ? sanitize_text_field( wp_unslash( $_POST['bqb_droits'] ) ) : '',
	);

	$acces                = isset( $_POST['bqb_acces'] ) ? sanitize_key( wp_unslash( $_POST['bqb_acces'] ) ) : 'public';
	$values['_bqb_acces'] = array_key_exists( $acces, bqb_access_levels() ) ? $acces : 'public';

	// Date de tri : toujours complète, pour trier et grouper par année.
	$values['_bqb_date_tri'] = $annee
		? sprintf( '%04d-%02d-%02d', $annee, $mois ? $mois : 1, $jour ? $jour : 1 )
		: '';

	foreach ( $values as $key => $value ) {
		if ( '' === $value || 0 === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}

	if ( isset( $_POST['bqb_associes_present'] ) ) {
		$related = isset( $_POST['bqb_associes'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['bqb_associes'] ) ) : array();
		$related = array_values( array_unique( array_filter( $related, function ( $id ) use ( $post_id ) {
			return $id && $id !== (int) $post_id && BQB_CPT === get_post_type( $id );
		} ) ) );

		if ( $related ) {
			update_post_meta( $post_id, '_bqb_associes', $related );
		} else {
			delete_post_meta( $post_id, '_bqb_associes' );
		}
	}
}

/* -------------------------------------------------------------------------
 * 6. Liste des documents : colonnes, filtres, tri
 * ---------------------------------------------------------------------- */

add_filter( 'manage_' . BQB_CPT . '_posts_columns', 'bqb_document_columns' );
function bqb_document_columns( $columns ) {
	$new = array();

	if ( isset( $columns['cb'] ) ) {
		$new['cb'] = $columns['cb'];
	}

	$new['bqb_cover'] = '';
	$new['title']     = 'Document';

	foreach ( array( 'taxonomy-bqb_collection', 'taxonomy-bqb_nature' ) as $key ) {
		if ( isset( $columns[ $key ] ) ) {
			$new[ $key ] = $columns[ $key ];
		}
	}

	$new['bqb_annee']  = 'Année';
	$new['bqb_acces']  = 'Accès';
	$new['bqb_resume'] = 'Résumé';
	$new['bqb_source'] = 'Fichier ou lien';
	$new['date']       = isset( $columns['date'] ) ? $columns['date'] : 'Date';

	return $new;
}

add_action( 'manage_' . BQB_CPT . '_posts_custom_column', 'bqb_document_column_content', 10, 2 );
function bqb_document_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'bqb_cover':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 44, 60 ), array( 'class' => 'bqb-col-cover' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo '<span class="bqb-col-cover bqb-col-cover--empty dashicons dashicons-book-alt"></span>';
			}
			break;

		case 'bqb_annee':
			$year = get_post_meta( $post_id, '_bqb_annee', true );
			echo $year ? (int) $year : '<span class="bqb-muted">—</span>';
			break;

		case 'bqb_acces':
			$acces = get_post_meta( $post_id, '_bqb_acces', true );
			$acces = $acces ? $acces : 'public';
			$label = array(
				'public'      => 'Public',
				'adherents'   => 'Adhérents',
				'partenaires' => 'Partenaires',
				'sur-place'   => 'Sur place',
			);
			echo '<span class="bqb-badge bqb-badge--' . esc_attr( $acces ) . '">' . esc_html( isset( $label[ $acces ] ) ? $label[ $acces ] : 'Public' ) . '</span>';
			break;

		case 'bqb_resume':
			$excerpt = get_post_field( 'post_excerpt', $post_id );
			echo '' !== trim( (string) $excerpt )
				? '<span class="bqb-ok dashicons dashicons-yes-alt" title="Résumé renseigné"></span>'
				: '<span class="bqb-missing">Manquant</span>';
			break;

		case 'bqb_source':
			$file = (int) get_post_meta( $post_id, '_bqb_fichier', true );
			$link = get_post_meta( $post_id, '_bqb_lien', true );
			$out  = array();
			if ( $file ) {
				$out[] = esc_html( bqb_file_label( $file ) );
			}
			if ( $link ) {
				$out[] = '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">Lien</a>';
			}
			echo $out ? implode( ' · ', $out ) : '<span class="bqb-muted">—</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			break;
	}
}

add_filter( 'manage_edit-' . BQB_CPT . '_sortable_columns', 'bqb_document_sortable' );
function bqb_document_sortable( $columns ) {
	$columns['bqb_annee'] = 'bqb_annee';

	return $columns;
}

add_action( 'pre_get_posts', 'bqb_document_admin_query' );
function bqb_document_admin_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || BQB_CPT !== $query->get( 'post_type' ) ) {
		return;
	}

	if ( 'bqb_annee' === $query->get( 'orderby' ) ) {
		// Les documents sans date restent dans la liste.
		$query->set(
			'meta_query',
			array(
				'relation' => 'OR',
				'bqb_date' => array( 'key' => '_bqb_date_tri', 'compare' => 'EXISTS' ),
				array( 'key' => '_bqb_date_tri', 'compare' => 'NOT EXISTS' ),
			)
		);
		$query->set( 'orderby', 'bqb_date' );
	}
}

/**
 * Filtres par collection, nature et thème au-dessus de la liste :
 * indispensable dès qu'il y a plusieurs centaines de documents.
 */
add_action( 'restrict_manage_posts', 'bqb_document_filters' );
function bqb_document_filters( $post_type ) {
	if ( BQB_CPT !== $post_type ) {
		return;
	}

	$filters = array(
		'bqb_collection' => 'Toutes les collections',
		'bqb_nature'     => 'Toutes les natures',
		'bqb_theme'      => 'Tous les thèmes',
	);

	foreach ( $filters as $taxonomy => $all ) {
		$current = isset( $_GET[ $taxonomy ] ) ? sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		wp_dropdown_categories(
			array(
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'value_field'     => 'slug',
				'selected'        => $current,
				'show_option_all' => $all,
				'hierarchical'    => true,
				'hide_empty'      => false,
				'orderby'         => 'name',
			)
		);
	}
}

/* -------------------------------------------------------------------------
 * 7. Page « Mode d'emploi »
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'bqb_admin_help_page' );
function bqb_admin_help_page() {
	add_submenu_page(
		'edit.php?post_type=' . BQB_CPT,
		'Mode d\'emploi',
		'Mode d\'emploi',
		'edit_posts',
		'bqb-aide',
		'bqb_render_help_page'
	);
}

function bqb_render_help_page() {
	$rows = array(
		array( 'Collection', 'D\'où vient le document. Une case parmi les espaces 1 à 4 et leurs sous-parties.', 'Écrits de Jean-Michel Quatrepoint' ),
		array( 'Nature', 'Ce qu\'est le document.', 'Article' ),
		array( 'Thèmes', 'De quoi il parle. Plusieurs possibles.', 'Souveraineté industrielle, Filières' ),
		array( 'Secteurs', 'Les secteurs stratégiques concernés.', 'Nucléaire, Énergie' ),
		array( 'Pays et territoires', 'Les zones géographiques concernées.', 'France, Chine' ),
		array( 'Prix et bourses', 'Pour les travaux des lauréats : le programme concerné.', 'Grand Prix Jean-Michel Quatrepoint' ),
		array( 'Personnes', 'Les auteurs, lauréats, experts. Tapez le nom, il se complète.', 'Jean-Michel Quatrepoint' ),
		array( 'Organisations', 'Éditeurs, partenaires, institutions, sources.', 'Éditions du Seuil' ),
		array( 'Mots-clés', 'Mots libres pour affiner la recherche.', 'Alstom, désindustrialisation' ),
	);

	$html  = '<div class="wrap bqb-help">';
	$html .= '<h1 class="bqb-help__title">Bibliothèque numérique</h1>';
	$html .= '<p class="bqb-help__intro">Chaque document reçoit plusieurs étiquettes indépendantes. C\'est leur combinaison qui le fait apparaître au bon endroit sur le site : un article de Jean-Michel Quatrepoint sur le nucléaire sera trouvé depuis ses écrits, depuis le thème Souveraineté énergétique et depuis le secteur Nucléaire, sans être saisi trois fois.</p>';

	$html .= '<div class="bqb-help__example">';
	$html .= '<span class="bqb-help__eyebrow">Exemple de fiche bien remplie</span>';
	$html .= '<table class="widefat striped"><thead><tr><th>Étiquette</th><th>Rôle</th><th>Exemple</th></tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$html .= '<tr><td><strong>' . esc_html( $row[0] ) . '</strong></td><td>' . esc_html( $row[1] ) . '</td><td><code>' . esc_html( $row[2] ) . '</code></td></tr>';
	}
	$html .= '</tbody></table></div>';

	$html .= '<h2 class="bqb-help__subtitle">Bon à savoir</h2><ul class="bqb-help__list">';
	$html .= '<li><strong>Le résumé est le champ le plus important.</strong> Une page qui ne contient qu\'un lien vers un PDF est jugée pauvre par Google. Avec un résumé de 300 à 600 caractères, chaque document devient une page utile. La colonne « Résumé » de la liste signale ceux qui en manquent.</li>';
	$html .= '<li>La <strong>date</strong> accepte l\'année seule. Le mois et le jour sont facultatifs.</li>';
	$html .= '<li>Pour un <strong>lauréat</strong>, créez sa fiche dans <strong>Personnes</strong> et cochez le rôle Lauréat, avec l\'année et le prix. Sa page listera automatiquement tous ses travaux.</li>';
	$html .= '<li>Les <strong>collections, thèmes, personnes et organisations</strong> ont leur propre page sur le site. Remplissez leur description : elle sert d\'introduction à la page et de texte pour Google.</li>';
	$html .= '<li>Un document <strong>réservé</strong> ne doit pas encore recevoir son fichier : un fichier de la médiathèque est accessible par son adresse. La protection arrive avec l\'étape suivante.</li>';
	$html .= '<li>Si une page document affiche une erreur 404, allez dans <strong>Réglages &rsaquo; Permaliens</strong> et cliquez sur Enregistrer, sans rien changer.</li>';
	$html .= '</ul></div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/* -------------------------------------------------------------------------
 * 8. Styles et scripts de l'administration
 * ---------------------------------------------------------------------- */

add_action( 'admin_enqueue_scripts', 'bqb_admin_assets' );
function bqb_admin_assets( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen ) {
		return;
	}

	$ours = ( BQB_CPT === $screen->post_type ) || ( $screen->taxonomy && array_key_exists( $screen->taxonomy, bqb_taxonomies() ) );

	if ( ! $ours ) {
		return;
	}

	$needs_media = in_array( $hook, array( 'post.php', 'post-new.php', 'edit-tags.php', 'term.php' ), true );

	if ( $needs_media ) {
		wp_enqueue_media();
	}

	wp_register_style( 'bqb-admin', false, array(), BQB_VERSION );
	wp_enqueue_style( 'bqb-admin' );
	wp_add_inline_style( 'bqb-admin', bqb_admin_css() );

	if ( $needs_media ) {
		wp_register_script( 'bqb-admin', false, array(), BQB_VERSION, true );
		wp_enqueue_script( 'bqb-admin' );
		wp_add_inline_script(
			'bqb-admin',
			'var BQB = ' . wp_json_encode(
				array(
					'ajax'  => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'bqb_related' ),
				)
			) . ';' . bqb_admin_js()
		);
	}
}

function bqb_admin_css() {
	$p = bqb_palette();
	$b = $p['bordeaux'];

	return '
	.bqb-admin{font-size:14px;}
	.bqb-admin__label{display:block;margin-bottom:7px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:' . $b . ';}
	.bqb-admin__field{margin:0 0 20px;}
	.bqb-admin__section{margin:0 0 22px;}
	.bqb-admin__grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 24px;}
	@media (max-width:782px){.bqb-admin__grid2{grid-template-columns:1fr;}}
	.bqb-admin__input{width:100%;padding:10px 13px;border:1px solid #dcdcde;border-radius:8px;background:#fff;box-shadow:none;transition:border-color .2s,box-shadow .2s;}
	.bqb-admin__input:focus{border-color:' . $b . ';box-shadow:0 0 0 3px ' . bqb_hex_to_rgba( $b, 0.12 ) . ';outline:none;}
	.bqb-admin__hint{display:block;margin-top:6px;color:#787c82;font-size:12.5px;line-height:1.5;}
	.bqb-admin [hidden]{display:none !important;}
	.bqb-req{font-style:normal;font-weight:600;text-transform:none;letter-spacing:0;color:#787c82;margin-left:6px;}

	.bqb-summary{margin:18px 0 6px;padding:18px 20px;border:1px solid ' . bqb_hex_to_rgba( $b, 0.2 ) . ';border-radius:10px;background:' . bqb_hex_to_rgba( $b, 0.03 ) . ';}
	.bqb-summary textarea{min-height:92px;line-height:1.6;resize:vertical;font-size:14.5px;}
	.bqb-editor-head{margin:22px 0 8px;}

	.bqb-date{display:flex;gap:10px;flex-wrap:wrap;}
	.bqb-date .bqb-admin__input{width:auto;}
	.bqb-date__year{width:130px !important;}
	.bqb-date__day{width:100px !important;}

	.bqb-file{display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px dashed #dcdcde;border-radius:10px;background:#fbfbfc;}
	.bqb-file__icon{color:' . $b . ';font-size:26px;width:26px;height:26px;}
	.bqb-file__info{flex:1;min-width:0;display:flex;flex-direction:column;}
	.bqb-file__info strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
	.bqb-file__info em{color:#787c82;font-style:normal;font-size:12.5px;}
	.bqb-remove{color:#b32d2e;text-decoration:none;cursor:pointer;}
	.bqb-warning{margin-top:10px;padding:11px 14px;border-left:3px solid #C75A18;border-radius:0 8px 8px 0;background:rgba(199,90,24,.07);color:#5a3212;font-size:12.5px;line-height:1.55;}
	.bqb-admin__remind{display:flex;gap:8px;align-items:flex-start;margin-top:6px;padding:12px 14px;border-radius:8px;background:' . bqb_hex_to_rgba( $b, 0.05 ) . ';color:' . $p['bordeaux_dark'] . ';font-size:12.5px;line-height:1.5;}
	.bqb-admin__remind .dashicons{color:' . $b . ';}

	.bqb-related__search{position:relative;}
	.bqb-related__search .dashicons{position:absolute;left:11px;top:11px;color:#a7aaad;}
	.bqb-related__search input{padding-left:36px;}
	.bqb-related__results{position:absolute;z-index:20;left:0;right:0;top:calc(100% + 4px);max-height:280px;overflow:auto;border:1px solid #dcdcde;border-radius:8px;background:#fff;box-shadow:0 12px 30px -12px rgba(0,0,0,.25);}
	.bqb-related__results button{display:block;width:100%;padding:10px 14px;border:0;border-bottom:1px solid #f0f0f1;background:#fff;text-align:left;cursor:pointer;font-size:13px;}
	.bqb-related__results button:hover{background:' . bqb_hex_to_rgba( $b, 0.06 ) . ';color:' . $b . ';}
	.bqb-related__results em,.bqb-related__list em{color:#787c82;font-style:normal;}
	.bqb-related__empty{padding:12px 14px;color:#787c82;font-size:13px;}
	.bqb-related__list{margin:12px 0 0;padding:0;list-style:none;display:flex;flex-direction:column;gap:6px;}
	.bqb-related__list li{display:flex;align-items:center;gap:10px;margin:0;padding:9px 12px;border:1px solid #e2e2e4;border-radius:8px;background:#fff;}
	.bqb-related__list li span{flex:1;}
	.bqb-related__remove{border:0;background:none;color:#b32d2e;font-size:18px;line-height:1;cursor:pointer;}

	#bqb_collection-all,#bqb_theme-all{max-height:440px;}
	.bqb-checks{display:flex;flex-wrap:wrap;gap:6px 16px;}
	.bqb-checks label{display:inline-flex !important;align-items:center;gap:6px;font-weight:400 !important;}
	.bqb-media{display:inline-flex;align-items:center;gap:10px;flex-wrap:wrap;}
	.bqb-media img{width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #dcdcde;}
	.bqb-term-field textarea{width:95%;}

	.column-bqb_cover{width:52px;}
	.column-bqb_annee{width:70px;}
	.column-bqb_acces,.column-bqb_resume{width:100px;}
	.bqb-col-cover{display:block;width:44px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #e2e2e4;}
	.bqb-col-cover--empty{display:flex;align-items:center;justify-content:center;background:' . bqb_hex_to_rgba( $b, 0.06 ) . ';color:' . bqb_hex_to_rgba( $b, 0.5 ) . ';font-size:20px;}
	.bqb-badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:600;}
	.bqb-badge--public{background:#e7f4ec;color:#1d6b3a;}
	.bqb-badge--adherents,.bqb-badge--partenaires{background:' . bqb_hex_to_rgba( $b, 0.08 ) . ';color:' . $b . ';}
	.bqb-badge--sur-place{background:#f0f0f1;color:#50575e;}
	.bqb-ok{color:#1d6b3a;}
	.bqb-missing{color:#b32d2e;font-weight:600;font-size:12px;}
	.bqb-muted{color:#b0b0b0;}

	.bqb-help__title{font-size:30px;font-weight:600;color:' . $p['bordeaux_dark'] . ';}
	.bqb-help__intro{max-width:820px;font-size:14.5px;line-height:1.7;color:#50575e;}
	.bqb-help__example{max-width:1000px;margin:24px 0;}
	.bqb-help__eyebrow{display:block;margin-bottom:10px;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:' . $b . ';}
	.bqb-help__example code{color:' . $b . ';background:' . bqb_hex_to_rgba( $b, 0.06 ) . ';}
	.bqb-help__subtitle{margin-top:30px;font-size:19px;font-weight:600;color:' . $p['bordeaux_dark'] . ';}
	.bqb-help__list{max-width:900px;line-height:1.8;list-style:disc;padding-left:20px;color:#50575e;}
	';
}

function bqb_admin_js() {
	return "
	(function(){
		function ready(fn){
			if ('loading' === document.readyState) { document.addEventListener('DOMContentLoaded', fn); } else { fn(); }
		}

		function pick(opts, done){
			if (!window.wp || !wp.media) { return; }
			var frame = wp.media(opts);
			frame.on('select', function(){ done(frame.state().get('selection').first().toJSON()); });
			frame.open();
		}

		ready(function(){

			/* Images des fiches (photo, logo) */
			document.addEventListener('click', function(e){
				var sel = e.target.closest('[data-media-select]');
				var rem = e.target.closest('[data-media-remove]');
				var box = e.target.closest('[data-media]');
				if (!box || (!sel && !rem)) { return; }
				e.preventDefault();

				var id  = box.querySelector('[data-media-id]');
				var img = box.querySelector('[data-media-preview]');
				var btn = box.querySelector('[data-media-select]');
				var del = box.querySelector('[data-media-remove]');

				if (rem) {
					id.value = ''; img.src = ''; img.hidden = true; del.hidden = true; btn.textContent = 'Choisir une image';
					return;
				}

				pick({ title: 'Choisir une image', button: { text: 'Utiliser cette image' }, library: { type: 'image' }, multiple: false }, function(att){
					var src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
					id.value = att.id; img.src = src; img.hidden = false; del.hidden = false; btn.textContent = 'Remplacer';
				});
			});

			/* Fichier du document */
			var file = document.querySelector('[data-file]');
			if (file) {
				var fid  = file.querySelector('[data-file-id]');
				var name = file.querySelector('[data-file-name]');
				var meta = file.querySelector('[data-file-meta]');
				var fsel = file.querySelector('[data-file-select]');
				var fdel = file.querySelector('[data-file-remove]');

				fsel.addEventListener('click', function(e){
					e.preventDefault();
					pick({ title: 'Choisir le fichier du document', button: { text: 'Utiliser ce fichier' }, multiple: false }, function(att){
						fid.value = att.id;
						name.textContent = att.filename || att.title;
						var ext  = (att.filename || '').split('.').pop().toUpperCase();
						meta.textContent = ext + (att.filesizeHumanReadable ? ' · ' + att.filesizeHumanReadable : '');
						fdel.hidden = false; fsel.textContent = 'Remplacer';
					});
				});

				fdel.addEventListener('click', function(e){
					e.preventDefault();
					fid.value = ''; name.textContent = 'Aucun fichier'; meta.textContent = '';
					fdel.hidden = true; fsel.textContent = 'Choisir un fichier';
				});
			}

			/* Avertissement sur les fichiers des documents réservés */
			var acces = document.getElementById('bqb_acces');
			var warn  = document.querySelector('[data-access-warning]');
			if (acces && warn) {
				acces.addEventListener('change', function(){ warn.hidden = ('public' === acces.value); });
			}

			/* Compteur du résumé */
			var ex = document.getElementById('bqb-excerpt');
			var ct = document.getElementById('bqb-excerpt-count');
			if (ex && ct) {
				ex.addEventListener('input', function(){ ct.textContent = ex.value.length; });
			}

			/* Documents associés */
			var rel = document.querySelector('[data-related]');
			if (rel && window.BQB) {
				var input   = rel.querySelector('[data-related-input]');
				var results = rel.querySelector('[data-related-results]');
				var list    = rel.querySelector('[data-related-list]');
				var current = rel.getAttribute('data-current');
				var timer   = null;

				function chosen(){
					return Array.prototype.map.call(list.querySelectorAll('input'), function(i){ return i.value; });
				}

				function esc(s){
					var d = document.createElement('div'); d.textContent = s; return d.innerHTML;
				}

				function add(item){
					if (chosen().indexOf(String(item.id)) !== -1) { return; }
					var li = document.createElement('li');
					li.innerHTML = '<input type=\"hidden\" name=\"bqb_associes[]\" value=\"' + parseInt(item.id, 10) + '\" />'
						+ '<span>' + esc(item.title) + (item.year ? ' <em>(' + esc(item.year) + ')</em>' : '') + '</span>'
						+ '<button type=\"button\" class=\"bqb-related__remove\" aria-label=\"Retirer\">×</button>';
					list.appendChild(li);
				}

				list.addEventListener('click', function(e){
					if (e.target.closest('.bqb-related__remove')) { e.preventDefault(); e.target.closest('li').remove(); }
				});

				input.addEventListener('input', function(){
					clearTimeout(timer);
					var q = input.value.trim();
					if (q.length < 2) { results.hidden = true; return; }

					timer = setTimeout(function(){
						var url = BQB.ajax + '?action=bqb_search_documents&nonce=' + encodeURIComponent(BQB.nonce)
							+ '&exclude=' + encodeURIComponent(current) + '&q=' + encodeURIComponent(q);

						fetch(url, { credentials: 'same-origin' }).then(function(r){ return r.json(); }).then(function(res){
							var items = (res && res.success) ? res.data : [];
							results.innerHTML = '';

							if (!items.length) {
								results.innerHTML = '<div class=\"bqb-related__empty\">Aucun document trouvé.</div>';
							}

							items.forEach(function(item){
								var b = document.createElement('button');
								b.type = 'button';
								b.innerHTML = esc(item.title) + (item.year ? ' <em>(' + esc(item.year) + ')</em>' : '');
								b.addEventListener('click', function(){ add(item); results.hidden = true; input.value = ''; input.focus(); });
								results.appendChild(b);
							});

							results.hidden = false;
						});
					}, 250);
				});

				input.addEventListener('keydown', function(e){ if ('Enter' === e.key) { e.preventDefault(); } });
				document.addEventListener('click', function(e){ if (!rel.contains(e.target)) { results.hidden = true; } });
			}
		});
	})();
	";
}

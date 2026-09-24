<?php
/**
 * « Produits Tea Room » : la carte du salon de thé, gérée comme les produits de la boutique.
 *
 * Menu d'administration séparé de « Produits boutique » : chaque produit du Tea Room
 * (café, thé, pâtisserie…) a un nom, un prix, une description, une mention
 * (Signature, En saison…) et une rubrique (Cafés & chocolats, Thés & infusions…).
 * Chaque rubrique a sa photo et sa place dans les onglets de la carte.
 * Le bloc « Carte du salon » affiche automatiquement ces produits.
 *
 * Les produits du Tea Room n'ont pas de page à eux : ils s'affichent dans la carte
 * de la page Salon de thé (et dans les données structurées « Menu » de cette page).
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_TEAROOM  = 'schiesser_tearoom';
const SCHIESSER_RUBRIQUE = 'schiesser_rubrique';

add_action( 'init', function () {
	register_post_type( SCHIESSER_TEAROOM, array(
		'labels'              => array(
			'name'                  => 'Produits Tea Room',
			'singular_name'         => 'Produit du Tea Room',
			'menu_name'             => 'Produits Tea Room',
			'add_new'               => 'Ajouter un produit',
			'add_new_item'          => 'Ajouter un produit au Tea Room',
			'edit_item'             => 'Modifier le produit du Tea Room',
			'new_item'              => 'Nouveau produit du Tea Room',
			'search_items'          => 'Rechercher dans la carte',
			'not_found'             => 'Aucun produit dans la carte du Tea Room',
			'not_found_in_trash'    => 'Aucun produit dans la corbeille',
			'all_items'             => 'Toute la carte',
			'featured_image'        => 'Photo (facultative)',
			'set_featured_image'    => 'Choisir une photo',
			'remove_featured_image' => 'Retirer la photo',
			'use_featured_image'    => 'Utiliser comme photo',
			'item_published'        => 'Produit ajouté à la carte.',
			'item_updated'          => 'Produit mis à jour.',
			'attributes'            => 'Ordre dans la rubrique',
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false,
		'show_in_rest'        => false, // formulaire simple, comme les produits de la boutique
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-coffee',
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		'has_archive'         => false,
		'rewrite'             => false,
	) );

	register_taxonomy( SCHIESSER_RUBRIQUE, SCHIESSER_TEAROOM, array(
		'labels'            => array(
			'name'          => 'Rubriques',
			'singular_name' => 'Rubrique',
			'menu_name'     => 'Rubriques',
			'add_new_item'  => 'Ajouter une rubrique',
			'edit_item'     => 'Modifier la rubrique',
			'search_items'  => 'Rechercher une rubrique',
			'not_found'     => 'Aucune rubrique',
			'back_to_items' => '← Retour aux rubriques',
		),
		'hierarchical'      => true, // cases à cocher dans le formulaire du produit
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => false,
		'rewrite'           => false,
	) );
} );

add_filter( 'enter_title_here', function ( $texte, $post ) {
	return SCHIESSER_TEAROOM === $post->post_type ? 'Nom du produit (ex. Café crème)' : $texte;
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* Champs du produit du Tea Room                                       */
/* ------------------------------------------------------------------ */

/** Champs : clé => [ libellé, aide, type ]. */
function schiesser_champs_tearoom() {
	return array(
		'prix'        => array( 'Prix', 'Ex. « CHF 5.20 » ou « CHF 6.— ».', 'text' ),
		'mention'     => array( 'Mention', 'Petite étiquette sous le produit : « Signature », « En saison », « Chaque jour »… Vide : rien ne s’affiche.', 'text' ),
		'description' => array( 'Description', 'Une ou deux phrases, affichées sous le nom dans la carte.', 'textarea' ),
	);
}

/** Balises permises dans la description (liens vers la boutique, mise en valeur). */
function schiesser_tearoom_kses() {
	return array(
		'a'      => array( 'href' => true ),
		'em'     => array(),
		'strong' => array(),
		'br'     => array(),
	);
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'schiesser_tearoom_details', 'Dans la carte', 'schiesser_boite_tearoom', SCHIESSER_TEAROOM, 'apres_titre', 'high' );
} );

add_action( 'edit_form_after_title', function ( $post ) {
	if ( SCHIESSER_TEAROOM === $post->post_type ) {
		do_meta_boxes( get_current_screen(), 'apres_titre', $post );
	}
} );

function schiesser_boite_tearoom( $post ) {
	wp_nonce_field( 'schiesser_tearoom', 'schiesser_tearoom_nonce' );
	?>
	<div class="schiesser-champs">
		<?php foreach ( schiesser_champs_tearoom() as $cle => $c ) :
			$valeur = (string) get_post_meta( $post->ID, '_t_' . $cle, true );
			$id     = 't-' . $cle;
			?>
			<div class="schiesser-champ schiesser-champ--<?php echo esc_attr( $cle ); ?>">
				<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $c[0] ); ?></strong></label>
				<?php if ( 'textarea' === $c[2] ) : ?>
					<?php
					// Petit éditeur visuel : gras, italique et lien (vers un produit de la boutique, par exemple).
					wp_editor( $valeur, $id, array(
						'textarea_name' => 't[' . $cle . ']',
						'textarea_rows' => 3,
						'media_buttons' => false,
						'teeny'         => true,
						'quicktags'     => false,
						'tinymce'       => array(
							'toolbar1'      => 'bold,italic,link,unlink',
							'toolbar2'      => '',
							'forced_root_block' => '',
							'wpautop'       => false,
						),
					) );
					?>
				<?php else : ?>
					<input id="<?php echo esc_attr( $id ); ?>" type="text" name="t[<?php echo esc_attr( $cle ); ?>]" value="<?php echo esc_attr( $valeur ); ?>"<?php echo 'mention' === $cle ? ' list="t-mentions"' : ''; ?>>
				<?php endif; ?>
				<span class="description"><?php echo esc_html( $c[1] ); ?></span>
			</div>
		<?php endforeach; ?>
		<datalist id="t-mentions"><option value="Signature"><option value="En saison"><option value="Chaque jour"><option value="Nouveau"><option value="Fait maison"></datalist>
		<p class="schiesser-champ schiesser-champ--suggestion">
			<label>
				<input type="checkbox" name="t[suggestion]" value="1" <?php checked( (bool) get_post_meta( $post->ID, '_t_suggestion', true ) ); ?>>
				<strong>Suggestion du jour</strong>
			</label>
			<span class="description">Mis en avant dans l’encadré « La suggestion du jour » de la carte (une seule suggestion à la fois : cocher ici retire la précédente). Astuce : un produit sans rubrique n’apparaît que dans la suggestion.</span>
		</p>
		<p class="description schiesser-rappel">Rubrique (onglet de la carte) : bloc « Rubriques » à droite. Ordre dans la rubrique : champ « Ordre » à droite (1 = premier). Photo : facultative, utilisée pour la suggestion du jour.</p>
	</div>
	<?php
}

add_action( 'save_post_' . SCHIESSER_TEAROOM, function ( $post_id ) {
	if ( ! isset( $_POST['schiesser_tearoom_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['schiesser_tearoom_nonce'] ), 'schiesser_tearoom' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$entree = isset( $_POST['t'] ) ? (array) wp_unslash( $_POST['t'] ) : array();
	foreach ( schiesser_champs_tearoom() as $cle => $c ) {
		$v = (string) ( $entree[ $cle ] ?? '' );
		$v = 'textarea' === $c[2] ? trim( wp_kses( $v, schiesser_tearoom_kses() ) ) : sanitize_text_field( $v );
		update_post_meta( $post_id, '_t_' . $cle, $v );
	}
	schiesser_tearoom_definir_suggestion( $post_id, ! empty( $entree['suggestion'] ) );
} );

/** Une seule suggestion du jour : la cocher sur un produit la retire des autres. */
function schiesser_tearoom_definir_suggestion( $post_id, $oui ) {
	if ( ! $oui ) {
		delete_post_meta( $post_id, '_t_suggestion' );
		return;
	}
	foreach ( get_posts( array(
		'post_type'   => SCHIESSER_TEAROOM,
		'post_status' => 'any',
		'numberposts' => -1,
		'fields'      => 'ids',
		'exclude'     => array( $post_id ),
		'meta_key'    => '_t_suggestion', // phpcs:ignore WordPress.DB.SlowDBQuery
	) ) as $autre ) {
		delete_post_meta( $autre, '_t_suggestion' );
	}
	update_post_meta( $post_id, '_t_suggestion', 1 );
}

/* Colonnes de la liste : photo, prix, mention, suggestion */
add_filter( 'manage_' . SCHIESSER_TEAROOM . '_posts_columns', function ( $colonnes ) {
	$nouvelles = array();
	foreach ( $colonnes as $cle => $libelle ) {
		if ( 'title' === $cle ) {
			$nouvelles['t_photo'] = 'Photo';
		}
		$nouvelles[ $cle ] = $libelle;
		if ( 'title' === $cle ) {
			$nouvelles['t_prix']    = 'Prix';
			$nouvelles['t_mention'] = 'Mention';
		}
	}
	unset( $nouvelles['date'] );
	$nouvelles['t_ordre'] = 'Ordre';
	return $nouvelles;
} );

add_action( 'manage_' . SCHIESSER_TEAROOM . '_posts_custom_column', function ( $colonne, $post_id ) {
	if ( 't_photo' === $colonne ) {
		echo get_the_post_thumbnail( $post_id, array( 48, 48 ), array( 'style' => 'width:48px;height:48px;object-fit:cover;border-radius:4px' ) ) ?: '<span class="s-sans-photo" aria-hidden="true">—</span>';
	}
	if ( 't_prix' === $colonne ) {
		echo esc_html( get_post_meta( $post_id, '_t_prix', true ) );
	}
	if ( 't_mention' === $colonne ) {
		echo esc_html( get_post_meta( $post_id, '_t_mention', true ) );
		if ( get_post_meta( $post_id, '_t_suggestion', true ) ) {
			echo ' <span class="s-suggestion">★ Suggestion du jour</span>';
		}
	}
	if ( 't_ordre' === $colonne ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}, 10, 2 );

/* La liste suit la carte : rubriques dans l'ordre des onglets, puis l'ordre choisi dans chaque rubrique. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() && $q->is_main_query() && SCHIESSER_TEAROOM === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$q->set( 'schiesser_par_rubrique', 1 );
	}
} );

add_filter( 'posts_orderby', function ( $orderby, $q ) {
	global $wpdb;
	if ( ! $q->get( 'schiesser_par_rubrique' ) ) {
		return $orderby;
	}
	$rubrique = "(SELECT MIN(tm.meta_value + 0) FROM {$wpdb->term_relationships} tr"
		. " INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '" . esc_sql( SCHIESSER_RUBRIQUE ) . "'"
		. " INNER JOIN {$wpdb->termmeta} tm ON tm.term_id = tt.term_id AND tm.meta_key = 'ordre'"
		. " WHERE tr.object_id = {$wpdb->posts}.ID)";
	return $rubrique . ' ASC, ' . $orderby;
}, 10, 2 );

add_action( 'restrict_manage_posts', function ( $type ) {
	if ( SCHIESSER_TEAROOM !== $type ) {
		return;
	}
	wp_dropdown_categories( array(
		'taxonomy'        => SCHIESSER_RUBRIQUE,
		'name'            => SCHIESSER_RUBRIQUE,
		'value_field'     => 'slug',
		'show_option_all' => 'Toutes les rubriques',
		'selected'        => isset( $_GET[ SCHIESSER_RUBRIQUE ] ) ? sanitize_title( wp_unslash( $_GET[ SCHIESSER_RUBRIQUE ] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification
		'hide_empty'      => false,
		'orderby'         => 'name',
	) );
} );

/* ------------------------------------------------------------------ */
/* Rubriques : photo et ordre des onglets                              */
/* ------------------------------------------------------------------ */

function schiesser_rubrique_champs( $terme = null ) {
	$photo = $terme ? (int) get_term_meta( $terme->term_id, 'photo', true ) : 0;
	$ordre = $terme ? (int) get_term_meta( $terme->term_id, 'ordre', true ) : 0;
	$image = $photo ? wp_get_attachment_image( $photo, 'medium', false, array( 'style' => 'max-width:220px;height:auto;display:block;margin-bottom:8px' ) ) : '';
	return array(
		'photo' => '<div class="js-rubrique-photo">'
			. '<div class="js-rubrique-apercu">' . $image . '</div>'
			. '<input type="hidden" name="rubrique_photo" value="' . esc_attr( $photo ?: '' ) . '">'
			. '<button type="button" class="button js-rubrique-choisir">' . ( $photo ? 'Changer la photo' : 'Choisir une photo' ) . '</button> '
			. '<button type="button" class="button-link js-rubrique-retirer"' . ( $photo ? '' : ' hidden' ) . '>Retirer</button>'
			. '<p class="description">La grande photo affichée au-dessus des onglets quand cette rubrique est choisie.</p></div>',
		'ordre' => '<input type="number" name="rubrique_ordre" min="0" step="1" value="' . esc_attr( $ordre ?: '' ) . '" style="width:6em">'
			. '<p class="description">Place de l’onglet dans la carte (1 = premier).</p>',
	);
}

add_action( SCHIESSER_RUBRIQUE . '_add_form_fields', function () {
	$champs = schiesser_rubrique_champs();
	echo '<div class="form-field"><label>Photo de la rubrique</label>' . $champs['photo'] . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<div class="form-field"><label>Ordre</label>' . $champs['ordre'] . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
} );

add_action( SCHIESSER_RUBRIQUE . '_edit_form_fields', function ( $terme ) {
	$champs = schiesser_rubrique_champs( $terme );
	echo '<tr class="form-field"><th scope="row">Photo de la rubrique</th><td>' . $champs['photo'] . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<tr class="form-field"><th scope="row">Ordre</th><td>' . $champs['ordre'] . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
} );

/* Les formulaires de rubrique sont déjà protégés par WordPress (jeton de sécurité vérifié avant ces actions). */
function schiesser_rubrique_enregistrer( $term_id ) {
	if ( ! current_user_can( 'edit_term', $term_id ) || ! isset( $_POST['rubrique_ordre'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$photo = absint( $_POST['rubrique_photo'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
	$ordre = absint( $_POST['rubrique_ordre'] ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( $photo && wp_attachment_is_image( $photo ) ) {
		update_term_meta( $term_id, 'photo', $photo );
	} else {
		delete_term_meta( $term_id, 'photo' );
	}
	update_term_meta( $term_id, 'ordre', $ordre );
}
add_action( 'created_' . SCHIESSER_RUBRIQUE, 'schiesser_rubrique_enregistrer' );
add_action( 'edited_' . SCHIESSER_RUBRIQUE, 'schiesser_rubrique_enregistrer' );

add_filter( 'manage_edit-' . SCHIESSER_RUBRIQUE . '_columns', function ( $colonnes ) {
	unset( $colonnes['description'], $colonnes['slug'] );
	$colonnes['posts']         = 'Produits';
	$colonnes['rubrique_photo'] = 'Photo';
	$colonnes['rubrique_ordre'] = 'Ordre';
	return $colonnes;
} );

add_filter( 'manage_' . SCHIESSER_RUBRIQUE . '_custom_column', function ( $sortie, $colonne, $term_id ) {
	if ( 'rubrique_photo' === $colonne ) {
		$photo = (int) get_term_meta( $term_id, 'photo', true );
		return $photo ? wp_get_attachment_image( $photo, array( 48, 48 ), false, array( 'style' => 'width:48px;height:48px;object-fit:cover;border-radius:4px' ) ) : '—';
	}
	if ( 'rubrique_ordre' === $colonne ) {
		return (string) (int) get_term_meta( $term_id, 'ordre', true );
	}
	return $sortie;
}, 10, 3 );

/* Médiathèque sur les écrans des rubriques, style des écrans du Tea Room. */
add_action( 'admin_enqueue_scripts', function () {
	$ecran = get_current_screen();
	if ( ! $ecran ) {
		return;
	}
	if ( SCHIESSER_RUBRIQUE === $ecran->taxonomy ) {
		wp_enqueue_media();
		wp_enqueue_script( 'schiesser-admin-tearoom', SCHIESSER_URI . '/assets/admin/tearoom.js', array( 'jquery' ), SCHIESSER_VERSION, true );
	}
} );

/* ------------------------------------------------------------------ */
/* Données de la carte                                                 */
/* ------------------------------------------------------------------ */

/** Rubriques triées selon leur ordre, puis leur nom. */
function schiesser_rubriques_triees() {
	$termes = get_terms( array( 'taxonomy' => SCHIESSER_RUBRIQUE, 'hide_empty' => false ) );
	if ( is_wp_error( $termes ) ) {
		return array();
	}
	usort( $termes, function ( $a, $b ) {
		$oa = (int) get_term_meta( $a->term_id, 'ordre', true ) ?: PHP_INT_MAX;
		$ob = (int) get_term_meta( $b->term_id, 'ordre', true ) ?: PHP_INT_MAX;
		return $oa === $ob ? strcasecmp( $a->name, $b->name ) : ( $oa < $ob ? -1 : 1 );
	} );
	return $termes;
}

/** Données d'un produit du Tea Room, au format des plats du bloc « Carte du salon ». */
function schiesser_donnees_tearoom( $post ) {
	$post  = get_post( $post );
	$photo = (int) get_post_thumbnail_id( $post );
	return array(
		'id'          => $post->ID,
		'nom'         => html_entity_decode( get_the_title( $post ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
		'prix'        => (string) get_post_meta( $post->ID, '_t_prix', true ),
		'description' => (string) get_post_meta( $post->ID, '_t_description', true ),
		'mention'     => (string) get_post_meta( $post->ID, '_t_mention', true ),
		'imageId'     => $photo,
		'imageUrl'    => '',
		'imageAlt'    => $photo ? (string) get_post_meta( $photo, '_wp_attachment_image_alt', true ) : '',
		'al'          => schiesser_allergenes_de( $post->ID, '_t_' ), // allergènes et régimes (inc/allergenes.php)
	);
}

/**
 * La carte du Tea Room : rubriques (dans l'ordre des onglets) avec leurs produits,
 * et la suggestion du jour. Une rubrique sans produit n'est pas affichée.
 *
 * @param bool $rafraichir Relire la base (après un import ou un transfert).
 */
function schiesser_carte_tearoom( $rafraichir = false ) {
	static $carte = null;
	if ( null !== $carte && ! $rafraichir ) {
		return $carte;
	}
	$carte = array( 'rubriques' => array(), 'suggestion' => null, 'nombre' => 0 );
	$posts = get_posts( array(
		'post_type'   => SCHIESSER_TEAROOM,
		'post_status' => 'publish',
		'numberposts' => 300,
		'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
	if ( ! $posts ) {
		return $carte;
	}
	$par_rubrique = array();
	foreach ( $posts as $post ) {
		$d = schiesser_donnees_tearoom( $post );
		if ( ! $carte['suggestion'] && get_post_meta( $post->ID, '_t_suggestion', true ) ) {
			$carte['suggestion'] = $d;
		}
		foreach ( (array) wp_get_object_terms( $post->ID, SCHIESSER_RUBRIQUE, array( 'fields' => 'ids' ) ) as $term_id ) {
			$par_rubrique[ (int) $term_id ][] = $d;
		}
	}
	foreach ( schiesser_rubriques_triees() as $t ) {
		if ( empty( $par_rubrique[ $t->term_id ] ) ) {
			continue;
		}
		$photo                = (int) get_term_meta( $t->term_id, 'photo', true );
		$carte['rubriques'][] = array(
			'id'       => $t->term_id,
			'nom'      => html_entity_decode( $t->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'imageId'  => $photo,
			'imageUrl' => '',
			'imageAlt' => $photo ? ( (string) get_post_meta( $photo, '_wp_attachment_image_alt', true ) ?: $t->name ) : '',
			'plats'    => $par_rubrique[ $t->term_id ],
		);
	}
	$carte['nombre'] = count( $posts );
	return $carte;
}

/* ------------------------------------------------------------------ */
/* Transfert de la carte saisie dans une page (versions 0.3 et avant)   */
/* ------------------------------------------------------------------ */

/** Premier bloc « Carte du salon » qui contient encore des rubriques, et sa page. */
function schiesser_carte_dans_une_page() {
	$pages = get_posts( array(
		'post_type'   => 'page',
		'post_status' => array( 'publish', 'draft', 'private', 'pending' ),
		'numberposts' => -1,
		's'           => 'wp:schiesser/carte-salon',
	) );
	foreach ( $pages as $page ) {
		if ( false === strpos( $page->post_content, '<!-- wp:schiesser/carte-salon' ) ) {
			continue;
		}
		$chemin = array();
		$bloc   = schiesser_tearoom_chercher_carte( parse_blocks( $page->post_content ), $chemin );
		if ( $bloc && ! empty( $bloc['innerBlocks'] ) ) {
			$plats = 0;
			foreach ( $bloc['innerBlocks'] as $r ) {
				$plats += count( $r['innerBlocks'] ?? array() );
			}
			return array( 'page' => $page, 'bloc' => $bloc, 'rubriques' => count( $bloc['innerBlocks'] ), 'plats' => $plats );
		}
	}
	return null;
}

/** Cherche un bloc « Carte du salon » avec rubriques ; $chemin reçoit sa position (indices imbriqués). */
function schiesser_tearoom_chercher_carte( $blocs, &$chemin ) {
	foreach ( $blocs as $i => $b ) {
		if ( 'schiesser/carte-salon' === $b['blockName'] && ! empty( $b['innerBlocks'] ) ) {
			$chemin[] = $i;
			return $b;
		}
		if ( ! empty( $b['innerBlocks'] ) ) {
			$sous = $chemin;
			$sous[] = $i;
			$trouve = schiesser_tearoom_chercher_carte( $b['innerBlocks'], $sous );
			if ( $trouve ) {
				$chemin = $sous;
				return $trouve;
			}
		}
	}
	return null;
}

/**
 * Transfère les rubriques et les plats du bloc « Carte du salon » dans « Produits Tea Room »,
 * puis allège le bloc (il affiche alors automatiquement les produits du Tea Room).
 *
 * @return array|WP_Error Nombre de rubriques et de produits transférés.
 */
function schiesser_transferer_carte() {
	$trouve = schiesser_carte_dans_une_page();
	if ( ! $trouve ) {
		return new WP_Error( 'aucune_carte', 'Aucune carte à transférer : la carte est peut-être déjà dans « Produits Tea Room ».' );
	}
	$bloc   = $trouve['bloc'];
	$nb_rub = 0;
	$nb_pro = 0;
	foreach ( $bloc['innerBlocks'] as $i => $r ) {
		if ( 'schiesser/rubrique' !== $r['blockName'] ) {
			continue;
		}
		$ra  = $r['attrs'];
		$nom = schiesser_texte_brut( $ra['nom'] ?? '' ) ?: 'Rubrique ' . ( $i + 1 );
		$t   = term_exists( $nom, SCHIESSER_RUBRIQUE );
		$t   = $t ?: wp_insert_term( $nom, SCHIESSER_RUBRIQUE );
		if ( is_wp_error( $t ) ) {
			continue;
		}
		$term_id = (int) ( is_array( $t ) ? $t['term_id'] : $t );
		update_term_meta( $term_id, 'ordre', $i + 1 );
		if ( ! empty( $ra['imageId'] ) && wp_attachment_is_image( (int) $ra['imageId'] ) ) {
			update_term_meta( $term_id, 'photo', (int) $ra['imageId'] );
		}
		++$nb_rub;
		foreach ( $r['innerBlocks'] as $k => $p ) {
			if ( 'schiesser/plat' !== $p['blockName'] ) {
				continue;
			}
			$pa = $p['attrs'];
			$id = schiesser_tearoom_creer( array(
				'nom'         => schiesser_texte_brut( $pa['nom'] ?? '' ),
				'prix'        => schiesser_texte_brut( $pa['prix'] ?? '' ),
				'description' => (string) ( $pa['description'] ?? '' ),
				'mention'     => schiesser_texte_brut( $pa['mention'] ?? '' ),
				'ordre'       => $k + 1,
				'rubrique'    => $term_id,
			) );
			$nb_pro += $id ? 1 : 0;
		}
	}
	// La suggestion saisie dans le bloc devient un produit « suggestion du jour » (sans rubrique).
	$a = $bloc['attrs'];
	if ( '' !== schiesser_texte_brut( $a['sTitre'] ?? '' ) ) {
		$id = schiesser_tearoom_creer( array(
			'nom'         => schiesser_texte_brut( $a['sTitre'] ),
			'prix'        => schiesser_texte_brut( $a['sPrix'] ?? '' ),
			'description' => (string) ( $a['sTexte'] ?? '' ),
			'photo'       => (int) ( $a['sId'] ?? 0 ),
			'ordre'       => 0,
		) );
		if ( $id ) {
			schiesser_tearoom_definir_suggestion( $id, true );
			++$nb_pro;
		}
	}

	// Le bloc garde ses textes (titre, mention, surtitre et pied de la suggestion) mais plus ses rubriques.
	$blocs  = parse_blocks( $trouve['page']->post_content );
	$chemin = array();
	schiesser_tearoom_chercher_carte( $blocs, $chemin );
	$cible = &$blocs;
	$dernier = array_pop( $chemin );
	foreach ( $chemin as $indice ) {
		$cible = &$cible[ $indice ]['innerBlocks'];
	}
	foreach ( array( 'sTitre', 'sTexte', 'sPrix', 'sId', 'sUrl', 'sAlt' ) as $cle ) {
		unset( $cible[ $dernier ]['attrs'][ $cle ] );
	}
	unset( $cible[ $dernier ]['attrs']['source'] );
	$cible[ $dernier ]['innerBlocks']  = array();
	$cible[ $dernier ]['innerHTML']    = '';
	$cible[ $dernier ]['innerContent'] = array();
	unset( $cible );
	wp_update_post( array(
		'ID'           => $trouve['page']->ID,
		'post_content' => wp_slash( serialize_blocks( $blocs ) ),
	) );
	schiesser_carte_tearoom( true );
	return array( 'rubriques' => $nb_rub, 'produits' => $nb_pro, 'page' => $trouve['page']->ID );
}

/**
 * Crée (ou met à jour, s'il existe déjà dans la même rubrique) un produit du Tea Room.
 *
 * @param array $d nom, prix, description, mention, ordre, rubrique (id), photo (id), demo (bool).
 * @return int Identifiant du produit, 0 en cas d'échec.
 */
function schiesser_tearoom_creer( $d ) {
	$nom = trim( (string) ( $d['nom'] ?? '' ) );
	if ( '' === $nom ) {
		return 0;
	}
	$existant = 0;
	foreach ( get_posts( array(
		'post_type'   => SCHIESSER_TEAROOM,
		'post_status' => 'any',
		'numberposts' => -1,
		'title'       => $nom,
	) ) as $p ) {
		$rubs = wp_get_object_terms( $p->ID, SCHIESSER_RUBRIQUE, array( 'fields' => 'ids' ) );
		if ( empty( $d['rubrique'] ) ? ! $rubs : in_array( (int) $d['rubrique'], array_map( 'intval', (array) $rubs ), true ) ) {
			$existant = $p->ID;
			break;
		}
	}
	$donnees = array(
		'post_type'   => SCHIESSER_TEAROOM,
		'post_status' => 'publish',
		'post_title'  => $nom,
		'menu_order'  => (int) ( $d['ordre'] ?? 0 ),
	);
	if ( $existant ) {
		$donnees['ID'] = $existant;
		$id            = wp_update_post( wp_slash( $donnees ) );
	} else {
		$id = wp_insert_post( wp_slash( $donnees ) );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_t_prix', sanitize_text_field( (string) ( $d['prix'] ?? '' ) ) );
	update_post_meta( $id, '_t_description', trim( wp_kses( (string) ( $d['description'] ?? '' ), schiesser_tearoom_kses() ) ) );
	update_post_meta( $id, '_t_mention', sanitize_text_field( (string) ( $d['mention'] ?? '' ) ) );
	if ( ! empty( $d['rubrique'] ) ) {
		wp_set_object_terms( $id, array( (int) $d['rubrique'] ), SCHIESSER_RUBRIQUE );
	}
	if ( ! empty( $d['photo'] ) && wp_attachment_is_image( (int) $d['photo'] ) ) {
		set_post_thumbnail( $id, (int) $d['photo'] );
	}
	if ( ! empty( $d['demo'] ) ) {
		update_post_meta( $id, '_t_demo', 1 );
	}
	return (int) $id;
}

/* Écran « Produits Tea Room » et tableau de bord : proposer le transfert tant que la carte est dans la page. */
add_action( 'admin_notices', function () {
	$ecran = get_current_screen();
	if ( ! $ecran || ! in_array( $ecran->id, array( 'edit-' . SCHIESSER_TEAROOM, 'dashboard' ), true ) || ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( 'dashboard' === $ecran->id && get_option( 'schiesser_carte_garder' ) ) {
		return; // la personne a choisi de garder la carte dans la page
	}
	if ( isset( $_GET['schiesser_carte'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$etat = sanitize_key( wp_unslash( $_GET['schiesser_carte'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( 'ok' === $etat ) {
			printf( '<div class="notice notice-success is-dismissible"><p>Carte transférée : %d rubriques et %d produits. Le bloc « Carte du salon » de la page affiche maintenant ces produits.</p></div>', (int) ( $_GET['r'] ?? 0 ), (int) ( $_GET['p'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>Aucune carte à transférer : elle est peut-être déjà dans « Produits Tea Room ».</p></div>';
		}
		return;
	}
	if ( schiesser_carte_tearoom()['nombre'] > 0 ) {
		return;
	}
	$trouve = schiesser_carte_dans_une_page();
	if ( ! $trouve ) {
		return;
	}
	$url    = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_transferer_carte' ), 'schiesser_transferer_carte' );
	$garder = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_carte_garder' ), 'schiesser_carte_garder' );
	?>
	<div class="notice notice-info">
		<p><strong>Nouveau : le menu « Produits Tea Room ».</strong> La carte du salon est encore saisie dans la page « <?php echo esc_html( get_the_title( $trouve['page'] ) ); ?> » (<?php echo (int) $trouve['rubriques']; ?> rubriques, <?php echo (int) $trouve['plats']; ?> produits). Transférez-la pour la gérer comme les produits de la boutique : la page affichera automatiquement cette carte, avec les mêmes textes et photos.</p>
		<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>">Transférer la carte dans Produits Tea Room</a><?php if ( 'dashboard' === $ecran->id ) : ?> <a class="button-link" style="margin-left:12px" href="<?php echo esc_url( $garder ); ?>">Non merci, garder la carte dans la page</a><?php endif; ?></p>
	</div>
	<?php
} );

add_action( 'admin_post_schiesser_carte_garder', function () {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_carte_garder' );
	update_option( 'schiesser_carte_garder', 1, false );
	wp_safe_redirect( admin_url() );
	exit;
} );

add_action( 'admin_post_schiesser_transferer_carte', function () {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_transferer_carte' );
	$r   = schiesser_transferer_carte();
	$arg = is_wp_error( $r ) ? array( 'schiesser_carte' => 'erreur' ) : array( 'schiesser_carte' => 'ok', 'r' => $r['rubriques'], 'p' => $r['produits'] );
	wp_safe_redirect( add_query_arg( $arg, admin_url( 'edit.php?post_type=' . SCHIESSER_TEAROOM ) ) );
	exit;
} );

/* ------------------------------------------------------------------ */
/* Données structurées : la carte (Menu) de la page qui l'affiche       */
/* ------------------------------------------------------------------ */

/**
 * Menu → sections (rubriques) → plats avec leur prix, d'après ce que la page affiche.
 */
function schiesser_schema_menu( $post ) {
	if ( ! $post || false === strpos( (string) $post->post_content, '<!-- wp:schiesser/carte-salon' ) || ! function_exists( 'schiesser_mq_carte_donnees' ) ) {
		return null;
	}
	$bloc = schiesser_trouver_bloc( 'schiesser/carte-salon', schiesser_blocs_page( $post ) );
	if ( ! $bloc ) {
		return null;
	}
	$d        = schiesser_mq_carte_donnees( $bloc['attrs'], $bloc );
	$sections = array();
	foreach ( $d['rubriques'] as $r ) {
		$plats = array();
		foreach ( $r['plats'] as $p ) {
			$nom = schiesser_texte_brut( $p['nom'] );
			if ( '' === $nom ) {
				continue;
			}
			$plat = array( '@type' => 'MenuItem', 'name' => $nom );
			// Régimes compris par Google (schema.org RestrictedDiet).
			$diete = array( 'vegetarien' => 'VegetarianDiet', 'vegane' => 'VeganDiet', 'sans-gluten' => 'GlutenFreeDiet', 'sans-lactose' => 'LowLactoseDiet' );
			foreach ( (array) ( $p['al']['regimes'] ?? array() ) as $k ) {
				if ( isset( $diete[ $k ] ) ) {
					$plat['suitableForDiet'][] = 'https://schema.org/' . $diete[ $k ];
				}
			}
			$desc = schiesser_texte_brut( $p['description'] );
			if ( '' !== $desc ) {
				$plat['description'] = $desc;
			}
			$prix = schiesser_prix_numerique( schiesser_texte_brut( $p['prix'] ) );
			if ( null !== $prix ) {
				$plat['offers'] = array( '@type' => 'Offer', 'price' => number_format( $prix, 2, '.', '' ), 'priceCurrency' => function_exists( 'schiesser_devise' ) ? schiesser_devise() : 'CHF' );
			}
			$plats[] = $plat;
		}
		if ( $plats ) {
			$sections[] = array( '@type' => 'MenuSection', 'name' => schiesser_texte_brut( $r['nom'] ), 'hasMenuItem' => $plats );
		}
	}
	if ( ! $sections ) {
		return null;
	}
	$url   = get_permalink( $post );
	$ancre = sanitize_title( $bloc['attrs']['ancre'] ?? '' );
	return array(
		'@type'          => 'Menu',
		'@id'            => $url . '#menu',
		'url'            => $url . ( $ancre ? '#' . $ancre : '' ),
		'name'           => schiesser_texte_brut( $bloc['attrs']['titre'] ?? '' ) ?: 'Carte du salon de thé',
		'inLanguage'     => get_bloginfo( 'language' ),
		'hasMenuSection' => $sections,
	);
}

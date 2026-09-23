<?php
/**
 * Type de contenu « Produits » (boutique) et ses champs.
 *
 * Le client ajoute ou modifie un produit depuis le menu « Produits » :
 * nom, photo (image mise en avant), catégorie, prix, fiche détaillée.
 * La grille de la boutique se met à jour automatiquement.
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_PRODUIT = 'schiesser_produit';
const SCHIESSER_CATEGORIE = 'schiesser_categorie';

add_action( 'init', function () {
	register_post_type( SCHIESSER_PRODUIT, array(
		'labels'             => array(
			'name'                  => 'Produits',
			'singular_name'         => 'Produit',
			'menu_name'             => 'Produits',
			'add_new'               => 'Ajouter un produit',
			'add_new_item'          => 'Ajouter un produit',
			'edit_item'             => 'Modifier le produit',
			'new_item'              => 'Nouveau produit',
			'view_item'             => 'Voir le produit',
			'search_items'          => 'Rechercher un produit',
			'not_found'             => 'Aucun produit',
			'not_found_in_trash'    => 'Aucun produit dans la corbeille',
			'all_items'             => 'Tous les produits',
			'featured_image'        => 'Photo du produit',
			'set_featured_image'    => 'Choisir la photo',
			'remove_featured_image' => 'Retirer la photo',
			'use_featured_image'    => 'Utiliser comme photo',
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => false, // édition simple par formulaire, sans l'éditeur de blocs
		'menu_position'      => 4,
		'menu_icon'          => 'dashicons-cart',
		'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
		'has_archive'        => false,
		'rewrite'            => false,
	) );

	register_taxonomy( SCHIESSER_CATEGORIE, SCHIESSER_PRODUIT, array(
		'labels'            => array(
			'name'          => 'Catégories',
			'singular_name' => 'Catégorie',
			'menu_name'     => 'Catégories',
			'add_new_item'  => 'Ajouter une catégorie',
			'edit_item'     => 'Modifier la catégorie',
			'search_items'  => 'Rechercher une catégorie',
			'not_found'     => 'Aucune catégorie',
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
	return SCHIESSER_PRODUIT === $post->post_type ? 'Nom du produit' : $texte;
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* Champs du produit                                                   */
/* ------------------------------------------------------------------ */

/** Champs simples : clé => [ libellé, aide, type ]. */
function schiesser_champs_produit() {
	return array(
		'prix'        => array( 'Prix', 'Ex. « CHF 6.50 » ou « Sur commande »', 'text' ),
		'unite'       => array( 'Unité', 'Ex. « les 100 g », « la pièce », « 16 pièces »', 'text' ),
		'badge'       => array( 'Badge', 'Petite étiquette sur la photo, ex. « Signature », « En saison ». Laisser vide pour ne rien afficher.', 'text' ),
		'badge_style' => array( 'Couleur du badge', '', 'select' ),
		'description' => array( 'Description', 'Deux ou trois phrases, affichées dans la fiche produit.', 'textarea' ),
		'accord'      => array( 'Accord', 'Ex. « Un thé noir corsé ou un café crème »', 'text' ),
		'origine'     => array( 'Origine', 'Ex. « Miel et amandes de la région »', 'text' ),
	);
}

/** Lit toutes les données d'un produit, prêtes pour l'affichage. */
function schiesser_donnees_produit( $post ) {
	$post  = get_post( $post );
	$d     = array(
		'id'    => $post->ID,
		'nom'   => get_the_title( $post ),
		'image' => get_the_post_thumbnail_url( $post, 'large' ) ?: '',
		'alt'   => '',
	);
	foreach ( schiesser_champs_produit() as $cle => $c ) {
		$d[ $cle ] = (string) get_post_meta( $post->ID, '_s_' . $cle, true );
	}
	$d['fiche'] = array_values( array_filter( (array) get_post_meta( $post->ID, '_s_fiche', true ) ) );

	$thumb = get_post_thumbnail_id( $post );
	if ( $thumb ) {
		$d['alt'] = (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true );
	}

	$termes           = get_the_terms( $post, SCHIESSER_CATEGORIE );
	$d['categories']  = array();
	$d['categorie']   = '';
	if ( $termes && ! is_wp_error( $termes ) ) {
		foreach ( $termes as $t ) {
			$d['categories'][] = $t->slug;
		}
		$d['categorie'] = $termes[0]->name;
	}
	return $d;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'schiesser_produit_details', 'Détails du produit', 'schiesser_boite_produit', SCHIESSER_PRODUIT, 'normal', 'high' );
} );

function schiesser_boite_produit( $post ) {
	wp_nonce_field( 'schiesser_produit', 'schiesser_produit_nonce' );
	$fiche = (array) get_post_meta( $post->ID, '_s_fiche', true );
	if ( ! $fiche ) {
		$fiche = array( array( 'Composition', '' ), array( 'Format', '' ), array( 'Conservation', '' ) );
	}
	?>
	<div class="schiesser-champs">
		<?php foreach ( schiesser_champs_produit() as $cle => $c ) :
			$valeur = get_post_meta( $post->ID, '_s_' . $cle, true );
			$id     = 's-' . $cle;
			?>
			<p class="schiesser-champ">
				<label for="<?php echo esc_attr( $id ); ?>"><strong><?php echo esc_html( $c[0] ); ?></strong></label>
				<?php if ( 'textarea' === $c[2] ) : ?>
					<textarea id="<?php echo esc_attr( $id ); ?>" name="s[<?php echo esc_attr( $cle ); ?>]" rows="3"><?php echo esc_textarea( $valeur ); ?></textarea>
				<?php elseif ( 'select' === $c[2] ) : ?>
					<select id="<?php echo esc_attr( $id ); ?>" name="s[<?php echo esc_attr( $cle ); ?>]">
						<option value="vert" <?php selected( $valeur, 'vert' ); ?>>Vert maison</option>
						<option value="menthe" <?php selected( $valeur, 'menthe' ); ?>>Menthe</option>
					</select>
				<?php else : ?>
					<input id="<?php echo esc_attr( $id ); ?>" type="text" name="s[<?php echo esc_attr( $cle ); ?>]" value="<?php echo esc_attr( $valeur ); ?>">
				<?php endif; ?>
				<?php if ( $c[1] ) : ?><span class="description"><?php echo esc_html( $c[1] ); ?></span><?php endif; ?>
			</p>
		<?php endforeach; ?>

		<div class="schiesser-fiche">
			<strong>Fiche détaillée</strong>
			<span class="description">Les lignes affichées dans la fiche du produit (Composition, Format, Conservation, Voyage…).</span>
			<table class="widefat">
				<thead><tr><th style="width:32%">Intitulé</th><th>Valeur</th><th style="width:44px"></th></tr></thead>
				<tbody class="js-fiche-lignes">
				<?php foreach ( $fiche as $ligne ) : ?>
					<tr>
						<td><input type="text" name="s_fiche_k[]" value="<?php echo esc_attr( $ligne[0] ?? '' ); ?>"></td>
						<td><input type="text" name="s_fiche_v[]" value="<?php echo esc_attr( $ligne[1] ?? '' ); ?>"></td>
						<td><button type="button" class="button-link js-fiche-retirer" aria-label="Retirer la ligne">✕</button></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button js-fiche-ajouter">+ Ajouter une ligne</button></p>
		</div>
		<p class="description">Photo : utilisez le bloc « Photo du produit » à droite. Ordre d'affichage : champ « Ordre » dans « Attributs ».</p>
	</div>
	<?php
}

add_action( 'save_post_' . SCHIESSER_PRODUIT, function ( $post_id ) {
	if ( ! isset( $_POST['schiesser_produit_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['schiesser_produit_nonce'] ), 'schiesser_produit' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$entree = isset( $_POST['s'] ) ? (array) wp_unslash( $_POST['s'] ) : array();
	foreach ( schiesser_champs_produit() as $cle => $c ) {
		$v = $entree[ $cle ] ?? '';
		if ( 'textarea' === $c[2] ) {
			$v = sanitize_textarea_field( $v );
		} elseif ( 'badge_style' === $cle ) {
			$v = 'menthe' === $v ? 'menthe' : 'vert';
		} else {
			$v = sanitize_text_field( $v );
		}
		update_post_meta( $post_id, '_s_' . $cle, $v );
	}

	$k     = isset( $_POST['s_fiche_k'] ) ? (array) wp_unslash( $_POST['s_fiche_k'] ) : array();
	$v     = isset( $_POST['s_fiche_v'] ) ? (array) wp_unslash( $_POST['s_fiche_v'] ) : array();
	$fiche = array();
	foreach ( $k as $i => $intitule ) {
		$intitule = sanitize_text_field( $intitule );
		$valeur   = sanitize_text_field( $v[ $i ] ?? '' );
		if ( '' !== $intitule || '' !== $valeur ) {
			$fiche[] = array( $intitule, $valeur );
		}
	}
	update_post_meta( $post_id, '_s_fiche', $fiche );
} );

/* Script et style du formulaire produit */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$ecran = get_current_screen();
	if ( ! $ecran || SCHIESSER_PRODUIT !== $ecran->post_type ) {
		return;
	}
	wp_enqueue_style( 'schiesser-admin', SCHIESSER_URI . '/assets/admin/admin.css', array(), SCHIESSER_VERSION );
	wp_enqueue_script( 'schiesser-admin', SCHIESSER_URI . '/assets/admin/produits.js', array(), SCHIESSER_VERSION, true );
} );

/* Colonnes de la liste des produits : photo et prix */
add_filter( 'manage_' . SCHIESSER_PRODUIT . '_posts_columns', function ( $colonnes ) {
	$nouvelles = array();
	foreach ( $colonnes as $cle => $libelle ) {
		if ( 'title' === $cle ) {
			$nouvelles['s_photo'] = 'Photo';
		}
		$nouvelles[ $cle ] = $libelle;
		if ( 'title' === $cle ) {
			$nouvelles['s_prix'] = 'Prix';
		}
	}
	unset( $nouvelles['date'] );
	return $nouvelles;
} );

add_action( 'manage_' . SCHIESSER_PRODUIT . '_posts_custom_column', function ( $colonne, $post_id ) {
	if ( 's_photo' === $colonne ) {
		echo get_the_post_thumbnail( $post_id, array( 56, 56 ), array( 'style' => 'width:56px;height:56px;object-fit:cover' ) ) ?: '—';
	}
	if ( 's_prix' === $colonne ) {
		echo esc_html( trim( get_post_meta( $post_id, '_s_prix', true ) . ' · ' . get_post_meta( $post_id, '_s_unite', true ), ' ·' ) );
	}
}, 10, 2 );

/* La liste suit l'ordre choisi par le client */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() && $q->is_main_query() && SCHIESSER_PRODUIT === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
} );

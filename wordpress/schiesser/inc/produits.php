<?php
/**
 * Type de contenu « Produits » (boutique) et ses champs.
 *
 * Le client ajoute ou modifie un produit depuis le menu « Produits » :
 * nom, photo (image mise en avant), catégorie, prix, fiche détaillée,
 * texte long pour la page du produit. Chaque produit a sa propre page
 * (/produits/nom-du-produit/), indexable par Google, et la grille de la
 * boutique se met à jour automatiquement.
 *
 * Quand WooCommerce sera installé, ces produits pourront être transférés
 * en un clic (voir inc/woocommerce.php) ; les anciennes adresses redirigent alors
 * vers les nouvelles fiches (redirection 301, sans perte de référencement).
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_PRODUIT   = 'schiesser_produit';
const SCHIESSER_CATEGORIE = 'schiesser_categorie';

add_action( 'init', function () {
	register_post_type( SCHIESSER_PRODUIT, array(
		'labels'              => array(
			'name'                  => 'Produits',
			'singular_name'         => 'Produit',
			'menu_name'             => 'Produits',
			'add_new'               => 'Ajouter un produit',
			'add_new_item'          => 'Ajouter un produit',
			'edit_item'             => 'Modifier le produit',
			'new_item'              => 'Nouveau produit',
			'view_item'             => 'Voir la page du produit',
			'view_items'            => 'Voir les produits',
			'search_items'          => 'Rechercher un produit',
			'not_found'             => 'Aucun produit',
			'not_found_in_trash'    => 'Aucun produit dans la corbeille',
			'all_items'             => 'Tous les produits',
			'featured_image'        => 'Photo du produit',
			'set_featured_image'    => 'Choisir la photo',
			'remove_featured_image' => 'Retirer la photo',
			'use_featured_image'    => 'Utiliser comme photo',
			'item_published'        => 'Produit publié.',
			'item_updated'          => 'Produit mis à jour.',
			'attributes'            => 'Ordre d\'affichage',
		),
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_rest'        => false, // formulaire simple, sans l'éditeur de blocs
		'menu_position'       => 4,
		'menu_icon'           => 'dashicons-cart',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'has_archive'         => false, // la liste des produits est la page « La boutique »
		'rewrite'             => array( 'slug' => 'produits', 'with_front' => false ),
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

/* Les nouvelles adresses /produits/… fonctionnent dès l'activation, sans passer par Réglages → Permaliens. */
add_action( 'init', function () {
	if ( get_option( 'schiesser_version_reecriture' ) !== SCHIESSER_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'schiesser_version_reecriture', SCHIESSER_VERSION );
	}
}, 99 );

add_filter( 'enter_title_here', function ( $texte, $post ) {
	return SCHIESSER_PRODUIT === $post->post_type ? 'Nom du produit' : $texte;
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* Champs du produit                                                   */
/* ------------------------------------------------------------------ */

/** Champs simples : clé => [ libellé, aide, type ]. */
function schiesser_champs_produit() {
	return array(
		'prix'        => array( 'Prix', 'Ex. « CHF 6.50 » ou « Sur commande ».', 'text' ),
		'unite'       => array( 'Unité', 'Ex. « les 100 g », « la pièce », « 16 pièces ».', 'text' ),
		'badge'       => array( 'Badge', 'Petite étiquette sur la photo : « Signature », « En saison »… Laisser vide pour ne rien afficher.', 'text' ),
		'badge_style' => array( 'Couleur du badge', '', 'select' ),
		'description' => array( 'Description courte', 'Deux ou trois phrases : affichées dans la fiche rapide de la boutique et en tête de la page du produit.', 'textarea' ),
		'accord'      => array( 'Accord', 'Ex. « Un thé noir corsé ou un café crème ».', 'text' ),
		'origine'     => array( 'Origine', 'Ex. « Miel et amandes de la région ».', 'text' ),
	);
}

/** Prix numérique à partir du texte saisi : « CHF 6.50 » → 6.50 ; « Sur commande » → null. */
function schiesser_prix_numerique( $texte ) {
	if ( preg_match( '/(\d+)(?:[.,](\d{1,2}))?/', (string) $texte, $m ) ) {
		return (float) ( $m[1] . '.' . ( $m[2] ?? '0' ) );
	}
	return null;
}

/** Lit toutes les données d'un produit « maison », prêtes pour l'affichage. */
function schiesser_donnees_produit( $post ) {
	$post  = get_post( $post );
	$thumb = (int) get_post_thumbnail_id( $post );
	$d     = array(
		'id'         => $post->ID,
		'nom'        => html_entity_decode( get_the_title( $post ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ), // texte brut (la mise en forme typographique est faite à l'affichage)
		'url'        => get_permalink( $post ),
		'image_id'   => $thumb,
		'image'      => $thumb ? (string) wp_get_attachment_image_url( $thumb, 'large' ) : '',
		'alt'        => $thumb ? (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) : '',
		'panier'     => '',
		'achetable'  => false,
	);
	foreach ( schiesser_champs_produit() as $cle => $c ) {
		$d[ $cle ] = (string) get_post_meta( $post->ID, '_s_' . $cle, true );
	}
	$d['fiche'] = array_values( array_filter( (array) get_post_meta( $post->ID, '_s_fiche', true ) ) );
	if ( '' === $d['alt'] ) {
		$d['alt'] = $d['nom'];
	}

	$termes          = get_the_terms( $post, SCHIESSER_CATEGORIE );
	$d['categories'] = array();
	$d['categorie']  = '';
	if ( $termes && ! is_wp_error( $termes ) ) {
		foreach ( $termes as $t ) {
			$d['categories'][ $t->slug ] = $t->name;
		}
		$d['categorie'] = $termes[0]->name;
	}
	return $d;
}

/**
 * Liste des produits à afficher, depuis les produits « maison » ou WooCommerce.
 *
 * @param string $source 'auto' (WooCommerce s'il est actif et rempli), 'maison' ou 'woocommerce'.
 * @param int    $limite 0 = tous.
 * @param array  $exclure Identifiants à exclure (produits liés).
 */
function schiesser_liste_produits( $source = 'auto', $limite = 0, $exclure = array() ) {
	$woo = function_exists( 'schiesser_woo_actif' ) && schiesser_woo_actif();
	if ( 'auto' === $source ) {
		$source = ( $woo && schiesser_woo_nombre_produits() > 0 ) ? 'woocommerce' : 'maison';
	}
	if ( 'woocommerce' === $source && $woo ) {
		return schiesser_liste_produits_wc( $limite, $exclure );
	}

	$requete = new WP_Query( array(
		'post_type'      => SCHIESSER_PRODUIT,
		'post_status'    => 'publish',
		'posts_per_page' => $limite > 0 ? $limite : -1,
		'post__not_in'   => array_map( 'intval', $exclure ),
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'no_found_rows'  => true,
	) );
	return array_map( 'schiesser_donnees_produit', $requete->posts );
}

/** Produits liés : d'abord ceux de la même catégorie, puis les autres. */
function schiesser_produits_lies( $post_id, $nombre = 4 ) {
	$termes = wp_get_post_terms( $post_id, SCHIESSER_CATEGORIE, array( 'fields' => 'ids' ) );
	$ids    = array();
	if ( $termes && ! is_wp_error( $termes ) ) {
		$ids = get_posts( array(
			'post_type'    => SCHIESSER_PRODUIT,
			'numberposts'  => $nombre,
			'post__not_in' => array( $post_id ),
			'fields'       => 'ids',
			'orderby'      => 'menu_order',
			'order'        => 'ASC',
			'tax_query'    => array( array( 'taxonomy' => SCHIESSER_CATEGORIE, 'terms' => $termes ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
	}
	if ( count( $ids ) < $nombre ) {
		$ids = array_merge( $ids, get_posts( array(
			'post_type'    => SCHIESSER_PRODUIT,
			'numberposts'  => $nombre - count( $ids ),
			'post__not_in' => array_merge( array( $post_id ), $ids ),
			'fields'       => 'ids',
			'orderby'      => 'menu_order',
			'order'        => 'ASC',
		) ) );
	}
	return array_map( 'schiesser_donnees_produit', $ids );
}

/**
 * Carte d'un produit (grille de la boutique, produits liés).
 *
 * @param array $p            Données du produit (schiesser_donnees_produit ou version WooCommerce).
 * @param int   $i            Position dans la grille.
 * @param bool  $fiche_rapide Un clic ouvre la fiche rapide (grille de la boutique).
 */
function schiesser_carte_produit( $p, $i, $fiche_rapide = true ) {
	?>
	<a class="card" href="<?php echo esc_url( $p['url'] ); ?>"<?php if ( $fiche_rapide ) : ?> aria-haspopup="dialog" data-i="<?php echo (int) $i; ?>" data-categories="<?php echo esc_attr( implode( ' ', array_keys( $p['categories'] ) ) ); ?>"<?php endif; ?> style="animation-delay:<?php echo esc_attr( round( $i * 0.04, 2 ) ); ?>s">
		<span class="card-im">
			<?php
			if ( $p['image_id'] ) {
				echo schiesser_image( $p['image_id'], 'medium_large', array( // phpcs:ignore WordPress.Security.EscapeOutput
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '(min-width: 1100px) 300px, (min-width: 760px) 31vw, 46vw',
				), $p['nom'] );
			} elseif ( $p['image'] ) {
				echo '<img src="' . esc_url( $p['image'] ) . '" alt="' . esc_attr( $p['alt'] ) . '" loading="lazy" decoding="async">';
			}
			?>
			<span class="tint"></span>
			<?php if ( $p['badge'] ) : ?>
				<span class="card-tag<?php echo 'menthe' === $p['badge_style'] ? ' gold' : ''; ?>"><?php echo esc_html( $p['badge'] ); ?></span>
			<?php endif; ?>
			<span class="card-see" aria-hidden="true"><?php echo $fiche_rapide ? 'Voir la fiche' : 'Découvrir'; ?></span>
		</span>
		<span class="card-body">
			<span class="card-plate" aria-hidden="true">Pl. <?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
			<h3 class="card-name"><?php echo esc_html( $p['nom'] ); ?></h3>
			<?php if ( $p['prix'] ) : ?><span class="card-price"><?php echo esc_html( $p['prix'] ); ?></span><?php endif; ?>
			<?php if ( $p['unite'] ) : ?><span class="card-unit"><?php echo esc_html( $p['unite'] ); ?></span><?php endif; ?>
		</span>
	</a>
	<?php
}

/* ------------------------------------------------------------------ */
/* Formulaire d'édition                                                */
/* ------------------------------------------------------------------ */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'schiesser_produit_details', 'Détails du produit', 'schiesser_boite_produit', SCHIESSER_PRODUIT, 'apres_titre', 'high' );
} );

/* Les détails (prix, catégorie…) s'affichent juste sous le nom, avant le texte long. */
add_action( 'edit_form_after_title', function ( $post ) {
	if ( SCHIESSER_PRODUIT !== $post->post_type ) {
		return;
	}
	do_meta_boxes( get_current_screen(), 'apres_titre', $post );
	echo '<h2 class="schiesser-titre-editeur">Présentation détaillée <span>(page du produit, facultatif mais conseillé pour Google : 2 ou 3 paragraphes)</span></h2>';
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
			<p class="schiesser-champ schiesser-champ--<?php echo esc_attr( $cle ); ?>">
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
				<thead><tr><th style="width:32%">Intitulé</th><th>Valeur</th><th style="width:44px"><span class="screen-reader-text">Retirer</span></th></tr></thead>
				<tbody class="js-fiche-lignes">
				<?php foreach ( $fiche as $ligne ) : ?>
					<tr>
						<td><input type="text" name="s_fiche_k[]" value="<?php echo esc_attr( $ligne[0] ?? '' ); ?>" aria-label="Intitulé"></td>
						<td><input type="text" name="s_fiche_v[]" value="<?php echo esc_attr( $ligne[1] ?? '' ); ?>" aria-label="Valeur"></td>
						<td><button type="button" class="button-link js-fiche-retirer" aria-label="Retirer la ligne">✕</button></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button js-fiche-ajouter">+ Ajouter une ligne</button></p>
		</div>
		<p class="description schiesser-rappel">Photo : bloc « Photo du produit » à droite. Catégorie : bloc « Catégories » à droite. Ordre d'affichage : champ « Ordre » (1 = premier).</p>
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
add_action( 'admin_enqueue_scripts', function () {
	$ecran = get_current_screen();
	if ( ! $ecran || SCHIESSER_PRODUIT !== $ecran->post_type ) {
		return;
	}
	wp_enqueue_script( 'schiesser-admin-produits', SCHIESSER_URI . '/assets/admin/produits.js', array( 'wp-hooks' ), SCHIESSER_VERSION, true );
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
		echo get_the_post_thumbnail( $post_id, array( 56, 56 ), array( 'style' => 'width:56px;height:56px;object-fit:cover;border-radius:4px' ) ) ?: '<span class="s-sans-photo" title="Pas de photo">—</span>';
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

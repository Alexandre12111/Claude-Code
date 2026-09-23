<?php
/**
 * Vente en ligne : préparation pour WooCommerce.
 *
 * Tant que WooCommerce n'est pas installé, rien ne change : les produits
 * se gèrent dans le menu « Produits » et la commande se fait par e-mail.
 *
 * Une fois WooCommerce installé et activé :
 * 1. Réglages maison → Boutique en ligne → « Transférer les produits » :
 *    chaque produit devient un produit WooCommerce (nom, photo, prix,
 *    catégorie, fiche détaillée, textes SEO de Rank Math) ;
 * 2. les anciennes adresses /produits/… redirigent vers les nouvelles (301),
 *    sans perte de référencement ;
 * 3. la grille de la page Boutique affiche les produits WooCommerce,
 *    avec le bouton « Ajouter au panier » ; un panier apparaît dans l'en-tête.
 * Le transfert peut être annulé depuis le même écran.
 */

defined( 'ABSPATH' ) || exit;

function schiesser_woo_actif() {
	return class_exists( 'WooCommerce' );
}

function schiesser_woo_nombre_produits() {
	static $nombre = null;
	if ( null === $nombre ) {
		$compte = wp_count_posts( 'product' );
		$nombre = (int) ( $compte->publish ?? 0 );
	}
	return $nombre;
}

/* ------------------------------------------------------------------ */
/* Produits WooCommerce au format de la grille                         */
/* ------------------------------------------------------------------ */

function schiesser_liste_produits_wc( $limite = 0, $exclure = array() ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	$produits = wc_get_products( array(
		'status'     => 'publish',
		'limit'      => $limite > 0 ? $limite : -1,
		'exclude'    => array_map( 'intval', (array) $exclure ),
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'visibility' => 'catalog',
	) );
	return array_map( 'schiesser_donnees_produit_wc', $produits );
}

/** Même format que schiesser_donnees_produit() : la grille et la fiche rapide n'ont pas à faire la différence. */
function schiesser_donnees_produit_wc( $produit ) {
	$id     = $produit->get_id();
	$photo  = (int) $produit->get_image_id();
	$defaut = (int) get_option( 'default_product_cat' );

	$categories = array();
	$termes     = get_the_terms( $id, 'product_cat' );
	if ( $termes && ! is_wp_error( $termes ) ) {
		foreach ( $termes as $t ) {
			if ( (int) $t->term_id !== $defaut ) { // « Non classé » n'a pas sa place dans les filtres
				$categories[ $t->slug ] = $t->name;
			}
		}
	}

	$fiche = array();
	foreach ( $produit->get_attributes() as $attribut ) {
		if ( ! is_object( $attribut ) || ! $attribut->get_visible() ) {
			continue;
		}
		$valeurs = $attribut->is_taxonomy()
			? wc_get_product_terms( $id, $attribut->get_name(), array( 'fields' => 'names' ) )
			: $attribut->get_options();
		$fiche[] = array( wc_attribute_label( $attribut->get_name(), $produit ), implode( ', ', (array) $valeurs ) );
	}

	$achetable = $produit->is_purchasable() && $produit->is_in_stock() && $produit->is_type( 'simple' );
	$prix      = $produit->get_price_html() ? trim( html_entity_decode( wp_strip_all_tags( $produit->get_price_html() ), ENT_QUOTES, 'UTF-8' ) ) : '';
	if ( '' === $prix ) {
		$prix = (string) $produit->get_meta( '_s_prix' ); // produit sans prix en ligne : « Sur commande »…
	}
	$badge     = (string) $produit->get_meta( '_s_badge' );
	if ( '' === $badge && $produit->is_on_sale() ) {
		$badge = 'Promotion';
	}
	$alt = $photo ? (string) get_post_meta( $photo, '_wp_attachment_image_alt', true ) : '';

	return array(
		'id'          => $id,
		'nom'         => $produit->get_name(),
		'url'         => $produit->get_permalink(),
		'image_id'    => $photo,
		'image'       => $photo ? (string) wp_get_attachment_image_url( $photo, 'large' ) : wc_placeholder_img_src( 'woocommerce_single' ),
		'alt'         => $alt ?: $produit->get_name(),
		'panier'      => $achetable ? add_query_arg( 'add-to-cart', $id, wc_get_cart_url() ) : '',
		'achetable'   => $achetable,
		'prix'        => $prix,
		'unite'       => (string) $produit->get_meta( '_s_unite' ),
		'badge'       => $badge,
		'badge_style' => (string) $produit->get_meta( '_s_badge_style' ) ?: 'vert',
		'description' => trim( wp_strip_all_tags( $produit->get_short_description() ) ),
		'accord'      => (string) $produit->get_meta( '_s_accord' ),
		'origine'     => (string) $produit->get_meta( '_s_origine' ),
		'fiche'       => $fiche,
		'categories'  => $categories,
		'categorie'   => $categories ? reset( $categories ) : '',
	);
}

/* ------------------------------------------------------------------ */
/* Transfert des produits « maison » vers WooCommerce                  */
/* ------------------------------------------------------------------ */

/** Catégories WooCommerce correspondantes (créées si besoin, même nom, même adresse). */
function schiesser_categories_wc( $categories ) {
	$ids = array();
	foreach ( $categories as $slug => $nom ) {
		$terme = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $terme ) {
			$ids[] = (int) $terme->term_id;
			continue;
		}
		$nouveau = wp_insert_term( $nom, 'product_cat', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $nouveau ) ) {
			$ids[] = (int) $nouveau['term_id'];
		}
	}
	return $ids;
}

/**
 * Crée un produit WooCommerce pour chaque produit « maison » pas encore transféré.
 *
 * @return int Nombre de produits transférés.
 */
function schiesser_transferer_vers_woo() {
	if ( ! class_exists( 'WC_Product_Simple' ) ) {
		return 0;
	}
	$produits = get_posts( array(
		'post_type'   => SCHIESSER_PRODUIT,
		'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
		'numberposts' => -1,
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
	) );

	$nombre = 0;
	foreach ( $produits as $post ) {
		$existant = (int) get_post_meta( $post->ID, '_schiesser_wc_id', true );
		if ( $existant && get_post( $existant ) && 'trash' !== get_post_status( $existant ) ) {
			continue; // déjà transféré
		}
		$d  = schiesser_donnees_produit( $post );
		$wc = new WC_Product_Simple();
		$wc->set_name( $d['nom'] );
		$wc->set_slug( $post->post_name );
		$wc->set_status( $post->post_status );
		$wc->set_description( $post->post_content );
		$wc->set_short_description( $d['description'] );
		$wc->set_menu_order( (int) $post->menu_order );
		$prix = schiesser_prix_numerique( $d['prix'] );
		if ( null !== $prix && $prix > 0 ) {
			$wc->set_regular_price( wc_format_decimal( $prix, 2 ) );
		}
		if ( $d['image_id'] ) {
			$wc->set_image_id( $d['image_id'] );
		}
		$categories = schiesser_categories_wc( $d['categories'] );
		if ( $categories ) {
			$wc->set_category_ids( $categories );
		}

		// Fiche détaillée → attributs visibles sur la page du produit.
		$attributs = array();
		foreach ( $d['fiche'] as $position => $ligne ) {
			if ( empty( $ligne[0] ) || empty( $ligne[1] ) ) {
				continue;
			}
			$a = new WC_Product_Attribute();
			$a->set_id( 0 );
			$a->set_name( $ligne[0] );
			$a->set_options( array( $ligne[1] ) );
			$a->set_position( $position );
			$a->set_visible( true );
			$a->set_variation( false );
			$attributs[] = $a;
		}
		if ( $attributs ) {
			$wc->set_attributes( $attributs );
		}

		foreach ( array( 'prix', 'unite', 'badge', 'badge_style', 'accord', 'origine' ) as $cle ) {
			if ( '' !== $d[ $cle ] ) {
				$wc->update_meta_data( '_s_' . $cle, $d[ $cle ] ); // « prix » garde le texte d'origine (ex. « Sur commande »)
			}
		}
		$wc->update_meta_data( '_schiesser_source_id', $post->ID );
		$nouveau = $wc->save();
		if ( ! $nouveau ) {
			continue;
		}

		// Référencement : mêmes titre, description et mots-clés dans Rank Math.
		foreach ( array( 'rank_math_title', 'rank_math_description', 'rank_math_focus_keyword' ) as $meta ) {
			$valeur = get_post_meta( $post->ID, $meta, true );
			if ( '' !== $valeur ) {
				update_post_meta( $nouveau, $meta, $valeur );
			}
		}

		update_post_meta( $post->ID, '_schiesser_wc_id', $nouveau );
		update_post_meta( $post->ID, '_schiesser_statut_avant', $post->post_status );
		if ( 'publish' === $post->post_status ) {
			// L'ancienne page n'est plus publiée : son adresse redirige vers la fiche WooCommerce.
			wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'draft' ) );
		}
		$nombre++;
	}
	return $nombre;
}

/** Annule le transfert : les produits WooCommerce créés vont à la corbeille, les produits « maison » sont republiés. */
function schiesser_annuler_transfert_woo() {
	$produits = get_posts( array(
		'post_type'   => SCHIESSER_PRODUIT,
		'post_status' => 'any',
		'numberposts' => -1,
		'meta_key'    => '_schiesser_wc_id', // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	foreach ( $produits as $post ) {
		$wc_id = (int) get_post_meta( $post->ID, '_schiesser_wc_id', true );
		if ( $wc_id && get_post( $wc_id ) ) {
			wp_trash_post( $wc_id );
		}
		$avant = get_post_meta( $post->ID, '_schiesser_statut_avant', true );
		if ( $avant && $avant !== $post->post_status ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_status' => $avant ) );
		}
		delete_post_meta( $post->ID, '_schiesser_wc_id' );
		delete_post_meta( $post->ID, '_schiesser_statut_avant' );
	}
	return count( $produits );
}

add_action( 'admin_post_schiesser_boutique', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Action réservée aux administrateurs.' );
	}
	check_admin_referer( 'schiesser_boutique', 'schiesser_boutique_nonce' );
	$retour = admin_url( 'admin.php?page=schiesser-reglages' );
	$action = sanitize_key( $_POST['schiesser_boutique_action'] ?? '' );

	if ( 'transferer' === $action && schiesser_woo_actif() ) {
		$retour = add_query_arg( 'schiesser_transfert', schiesser_transferer_vers_woo(), $retour );
	} elseif ( 'annuler' === $action ) {
		$retour = add_query_arg( 'schiesser_annulation', schiesser_annuler_transfert_woo(), $retour );
	}
	wp_safe_redirect( $retour . '#boutique' );
	exit;
} );

/* Les anciennes adresses /produits/… redirigent (301) vers la fiche WooCommerce. */
add_action( 'template_redirect', function () {
	if ( ! schiesser_woo_actif() ) {
		return;
	}
	$post = null;
	if ( is_singular( SCHIESSER_PRODUIT ) ) {
		$post = get_queried_object();
	} elseif ( is_404() ) {
		$chemin = trim( (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH ), '/' );
		$base   = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		if ( $base && 0 === strpos( $chemin, $base . '/' ) ) {
			$chemin = substr( $chemin, strlen( $base ) + 1 );
		}
		if ( preg_match( '#^produits/([^/]+)$#', $chemin, $m ) ) {
			$post = get_page_by_path( sanitize_title( $m[1] ), OBJECT, SCHIESSER_PRODUIT );
		}
	}
	if ( ! $post ) {
		return;
	}
	$wc_id = (int) get_post_meta( $post->ID, '_schiesser_wc_id', true );
	if ( $wc_id && 'publish' === get_post_status( $wc_id ) ) {
		wp_safe_redirect( get_permalink( $wc_id ), 301 );
		exit;
	}
}, 1 );

/* ------------------------------------------------------------------ */
/* Écran « Boutique en ligne » (Réglages maison)                       */
/* ------------------------------------------------------------------ */

function schiesser_panneau_boutique() {
	$compte     = wp_count_posts( SCHIESSER_PRODUIT );
	$maison     = (int) $compte->publish + (int) $compte->draft + (int) $compte->pending + (int) $compte->private;
	$transferes = count( get_posts( array(
		'post_type'   => SCHIESSER_PRODUIT,
		'post_status' => 'any',
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_key'    => '_schiesser_wc_id', // phpcs:ignore WordPress.DB.SlowDBQuery
	) ) );
	$action_url = admin_url( 'admin-post.php?action=schiesser_boutique' );

	if ( isset( $_GET['schiesser_transfert'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$n = (int) $_GET['schiesser_transfert']; // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="s-etat s-etat--ok"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span> ' . esc_html( $n ? sprintf( '%d produit(s) transféré(s) vers WooCommerce. Vérifiez les prix dans Produits WooCommerce avant d\'ouvrir la vente.', $n ) : 'Aucun nouveau produit à transférer.' ) . '</div>';
	}
	if ( isset( $_GET['schiesser_annulation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="s-etat s-etat--ok"><span class="dashicons dashicons-undo" aria-hidden="true"></span> Transfert annulé : les produits du site sont de nouveau publiés.</div>';
	}

	wp_nonce_field( 'schiesser_boutique', 'schiesser_boutique_nonce', false );

	if ( ! schiesser_woo_actif() ) {
		schiesser_carte_debut( 'Vendre en ligne avec WooCommerce', 'Le thème est déjà prêt : l\'extension gratuite WooCommerce suffit, aucun code à écrire.', 'cart' );
		?>
		<ol class="s-etapes">
			<li><strong>Installer WooCommerce</strong> : Extensions → Ajouter → « WooCommerce » → Installer, puis Activer.
				<?php if ( current_user_can( 'install_plugins' ) ) : ?><a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>">Ouvrir l'installation</a><?php endif; ?></li>
			<li><strong>Régler la boutique</strong> dans WooCommerce → Réglages : devise <em>franc suisse (CHF)</em>, adresse, TVA, zones et frais de livraison.</li>
			<li><strong>Choisir les paiements</strong> : carte bancaire et TWINT (extension « Payrexx » ou « Stripe » selon votre banque), ou paiement au retrait en boutique.</li>
			<li><strong>Revenir ici</strong> et cliquer sur « Transférer les produits » : vos <?php echo (int) $maison; ?> produits deviennent des produits WooCommerce, sans rien ressaisir.</li>
		</ol>
		<p class="s-aide">Ce qui est déjà prévu : mise en page des pages WooCommerce aux couleurs de la maison, panier dans l'en-tête, bouton « Ajouter au panier » dans la fiche rapide, redirection des anciennes adresses produits, données SEO conservées.</p>
		<?php if ( $transferes ) : ?>
			<input type="hidden" name="schiesser_boutique_action" value="">
			<div class="s-etat s-etat--alerte"><span class="dashicons dashicons-warning" aria-hidden="true"></span>
				<div><strong><?php echo (int) $transferes; ?> produit(s) avaient été transférés vers WooCommerce, qui n'est plus actif.</strong> Ils ne s'affichent plus sur le site. Rétablissez-les en un clic :
				<button type="submit" class="button" formaction="<?php echo esc_url( $action_url ); ?>" formnovalidate onclick="this.form.schiesser_boutique_action.value='annuler';">Republier les produits du site</button></div>
			</div>
		<?php endif; ?>
		<?php
		schiesser_carte_fin();
		return;
	}

	schiesser_carte_debut( 'WooCommerce est actif', 'La grille de la page Boutique affiche automatiquement les produits WooCommerce dès qu\'il y en a au moins un.', 'yes-alt' );
	?>
	<ul class="s-chiffres">
		<li><strong><?php echo (int) schiesser_woo_nombre_produits(); ?></strong> produit(s) WooCommerce en ligne</li>
		<li><strong><?php echo (int) $maison; ?></strong> produit(s) dans le menu « Produits » du site</li>
		<li><strong><?php echo (int) $transferes; ?></strong> déjà transféré(s)</li>
	</ul>
	<input type="hidden" name="schiesser_boutique_action" value="">
	<p class="s-actions-ligne">
		<?php if ( $maison > $transferes ) : ?>
			<button type="submit" class="button button-primary" formaction="<?php echo esc_url( $action_url ); ?>" formnovalidate onclick="this.form.schiesser_boutique_action.value='transferer';return confirm('Transférer les produits vers WooCommerce ? Les anciennes adresses redirigeront vers les nouvelles fiches.');">
				<span class="dashicons dashicons-migrate" aria-hidden="true"></span> Transférer <?php echo (int) ( $maison - $transferes ); ?> produit(s) vers WooCommerce
			</button>
		<?php endif; ?>
		<?php if ( $transferes ) : ?>
			<button type="submit" class="button" formaction="<?php echo esc_url( $action_url ); ?>" formnovalidate onclick="this.form.schiesser_boutique_action.value='annuler';return confirm('Annuler le transfert ? Les produits WooCommerce créés iront à la corbeille.');">
				<span class="dashicons dashicons-undo" aria-hidden="true"></span> Annuler le transfert
			</button>
		<?php endif; ?>
		<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>">Produits WooCommerce</a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings' ) ); ?>">Réglages WooCommerce</a>
	</p>
	<p class="s-aide">Après le transfert, gérez les produits dans le menu <strong>Produits</strong> de WooCommerce (prix, stock, variations). La page « Boutique » du site reste modifiable comme avant.</p>
	<?php
	schiesser_carte_fin();
}

/* ------------------------------------------------------------------ */
/* Affichage des pages WooCommerce                                     */
/* ------------------------------------------------------------------ */

add_action( 'wp', function () {
	if ( ! schiesser_woo_actif() ) {
		return;
	}
	// Le titre (H1) des pages boutique est affiché par le thème, une seule fois.
	add_filter( 'woocommerce_show_page_title', '__return_false' );
	// Pas de colonne latérale : la mise en page reste celle du site.
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! schiesser_woo_actif() ) {
		return;
	}
	wp_enqueue_script( 'wc-cart-fragments' ); // met à jour le panier de l'en-tête
	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		wp_enqueue_style( 'schiesser-woocommerce', SCHIESSER_URI . '/assets/css/woocommerce.css', array( 'schiesser-site' ), SCHIESSER_VERSION );
	}
}, 20 );

/* Nombre de colonnes et de produits par page */
add_filter( 'loop_shop_columns', function () {
	return 4;
} );
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );

/** Lien vers le panier (en-tête). */
function schiesser_lien_panier() {
	$wc = ( schiesser_woo_actif() && function_exists( 'WC' ) ) ? WC() : null;
	if ( ! $wc || empty( $wc->cart ) ) {
		return; // panier indisponible (administration, API…)
	}
	$n = (int) $wc->cart->get_cart_contents_count();
	?>
	<a class="hcart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Panier, %d article(s)', $n ) ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1.2 11.2a1 1 0 01-1 .8H7.2a1 1 0 01-1-.8L5 8z"/><path d="M9 8V6.5a3 3 0 016 0V8"/></svg>
		<span class="hcart-n<?php echo $n ? '' : ' is-vide'; ?>"><?php echo (int) $n; ?></span>
	</a>
	<?php
}

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	ob_start();
	schiesser_lien_panier();
	$fragments['a.hcart'] = ob_get_clean();
	return $fragments;
} );

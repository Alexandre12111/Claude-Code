<?php
/**
 * Liste des produits de la boutique « en préparation ».
 *
 * Tant que les photos ne sont pas prêtes, le site affiche à la place de la liste un aperçu
 * de l'assortiment (rubriques) et le message « Die vollständige Produktliste folgt in Kürze ».
 * Les fiches produits ne sont pas encore publiques (redirigées vers la page Confiserie, absentes
 * des plans du site pour Google).
 *
 * Un bouton dans l'administration (menu « Produits boutique » → « Mise en ligne », barre d'outils
 * du haut et écran « Aujourd'hui ») affiche la liste d'un clic, quand tout est prêt.
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_OPTION_BOUTIQUE_PRETE', 'schiesser_boutique_prete' );

function schiesser_boutique_prete() {
	return (bool) get_option( SCHIESSER_OPTION_BOUTIQUE_PRETE, false );
}

/* ------------------------------------------------------------------ */
/* Sur le site                                                         */
/* ------------------------------------------------------------------ */

/**
 * Aperçu de l'assortiment affiché à la place de la liste des produits.
 *
 * @param array[] $produits Produits (schiesser_liste_produits).
 */
function schiesser_html_bientot( $produits ) {
	$rubriques = array();
	foreach ( $produits as $p ) {
		$cat                 = $p['categorie'] ?: schiesser_t( 'Weitere Spezialitäten' );
		$rubriques[ $cat ][] = $p['nom'];
	}
	$html = '<div class="bientot rv"><div class="bientot-tete"><p class="bientot-k">' . esc_html( schiesser_t( 'Produktliste folgt in Kürze' ) ) . '</p>'
		. '<p class="bientot-texte">' . esc_html( schiesser_t( 'Die vollständige Produktliste mit Fotos und Preisen ist bald online. Bis dahin finden Sie alles an der Theke am Marktplatz, oder Sie bestellen bequem per Telefon.' ) ) . '</p>'
		. '<div class="bientot-actions">'
		. ( schiesser_reglage( 'telephone' ) ? '<a class="btn btn-kir" href="' . esc_url( schiesser_lien_tel() ) . '"><span>' . esc_html( schiesser_t( 'Anrufen:' ) ) . ' ' . esc_html( schiesser_reglage( 'telephone' ) ) . '</span></a>' : '' )
		. '<a class="btn btn-line" href="' . esc_url( schiesser_url_page( 'visiter' ) ) . '"><span>' . esc_html( schiesser_t( 'Öffnungszeiten & Anfahrt' ) ) . '</span></a>'
		. '</div></div>';
	if ( $rubriques ) {
		$html .= '<ul class="bientot-rubriken">';
		foreach ( $rubriques as $nom => $liste ) {
			$html .= '<li><span class="bientot-nom">' . esc_html( $nom ) . '</span>'
				. '<span class="bientot-liste">' . esc_html( implode( ' · ', array_slice( $liste, 0, 5 ) ) . ( count( $liste ) > 5 ? ' …' : '' ) ) . '</span></li>';
		}
		$html .= '</ul>';
	}
	return $html . '</div>';
}

/* Fiches produits pas encore publiques : les visiteurs arrivent sur la page Confiserie. */
add_action( 'template_redirect', function () {
	if ( schiesser_boutique_prete() || ! is_singular( SCHIESSER_PRODUIT ) || current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_safe_redirect( schiesser_url_boutique(), 302 );
	exit;
} );

/* Aperçu pour l'équipe connectée : rappel que la fiche n'est pas encore visible. */
add_action( 'wp_body_open', function () {
	if ( schiesser_boutique_prete() || ! is_singular( SCHIESSER_PRODUIT ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	echo '<div class="annonce annonce--sombre"><div class="wrap"><p>Aperçu réservé à l’équipe : les fiches produits ne sont pas encore visibles pour les visiteurs. <a href="' . esc_url( admin_url( 'edit.php?post_type=' . SCHIESSER_PRODUIT . '&page=schiesser-lancement' ) ) . '">Afficher la liste des produits</a></p></div></div>';
} );

/* Plans du site pour Google : pas de fiches produits tant que la liste n'est pas en ligne. */
add_filter( 'wp_sitemaps_post_types', function ( $types ) {
	if ( ! schiesser_boutique_prete() ) {
		unset( $types[ SCHIESSER_PRODUIT ] );
	}
	return $types;
} );
add_filter( 'wp_sitemaps_taxonomies', function ( $tax ) {
	if ( ! schiesser_boutique_prete() && defined( 'SCHIESSER_CATEGORIE' ) ) {
		unset( $tax[ SCHIESSER_CATEGORIE ] );
	}
	return $tax;
} );
add_filter( 'rank_math/sitemap/exclude_post_type', function ( $exclure, $type ) {
	return ( SCHIESSER_PRODUIT === $type && ! schiesser_boutique_prete() ) ? true : $exclure;
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* Dans l'administration : le bouton de mise en ligne                  */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=' . SCHIESSER_PRODUIT,
		'Mise en ligne de la liste',
		schiesser_boutique_prete() ? 'Mise en ligne' : 'Mise en ligne <span class="awaiting-mod"><span class="pending-count">!</span></span>',
		'edit_pages',
		'schiesser-lancement',
		'schiesser_page_lancement'
	);
} );

function schiesser_url_bascule_boutique( $etat ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_boutique_prete&etat=' . ( $etat ? 1 : 0 ) ), 'schiesser_boutique_prete' );
}

/** Photos manquantes : produits publiés sans image. */
function schiesser_produits_sans_photo() {
	return get_posts( array(
		'post_type'   => SCHIESSER_PRODUIT,
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields'      => 'ids',
		'meta_query'  => array( array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
}

function schiesser_page_lancement() {
	$prete     = schiesser_boutique_prete();
	$total     = (int) wp_count_posts( SCHIESSER_PRODUIT )->publish;
	$sans      = schiesser_produits_sans_photo();
	$confiserie = schiesser_url_boutique();
	?>
	<div class="wrap s-admin">
		<h1>Mise en ligne de la liste des produits</h1>
		<hr class="wp-header-end">
		<?php if ( isset( $_GET['fait'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><?php echo $prete ? '<strong>La liste des produits est en ligne.</strong> Les visiteurs voient les produits, leurs fiches et leurs prix.' : '<strong>La liste est de nouveau en préparation.</strong> Les visiteurs voient le message « Produktliste folgt in Kürze ».'; ?> <a href="<?php echo esc_url( $confiserie ); ?>" target="_blank" rel="noopener">Voir la page ↗</a></p></div>
		<?php endif; ?>
		<?php
		schiesser_carte_debut( $prete ? 'La liste des produits est en ligne' : 'La liste des produits est en préparation', $prete
			? 'Les visiteurs voient tous les produits publiés, avec leurs fiches et leurs prix.'
			: 'Les visiteurs voient pour l’instant l’aperçu de l’assortiment et le message « Die vollständige Produktliste folgt in Kürze » (la liste complète arrive prochainement). Les fiches produits ne sont pas encore publiques.', $prete ? 'yes-alt' : 'clock' );
		?>
		<p><strong><?php echo (int) $total; ?> produits publiés</strong><?php echo $sans ? ', dont <strong>' . count( $sans ) . ' sans photo</strong>' : ', tous avec une photo'; ?>.
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . SCHIESSER_PRODUIT ) ); ?>">Voir les produits</a></p>
		<?php if ( ! $prete ) : ?>
			<ol class="s-lancement-etapes">
				<li>Ajoutez une photo à chaque produit (bloc « Photo du produit » à droite dans la fiche). Astuce : cliquez sur le point d’intérêt de la photo dans la médiathèque.</li>
				<li>Relisez les noms, les formats et les prix.</li>
				<li>Cliquez sur le bouton ci-dessous : la liste apparaît immédiatement sur le site.</li>
			</ol>
			<p><a class="button button-primary button-hero" href="<?php echo esc_url( schiesser_url_bascule_boutique( true ) ); ?>" onclick="return confirm('Afficher la liste des produits sur le site ?<?php echo $sans ? '\n' . count( $sans ) . ' produits n’ont pas encore de photo.' : ''; ?>');">Tout est prêt : afficher la liste des produits</a></p>
		<?php else : ?>
			<p><a class="button" href="<?php echo esc_url( schiesser_url_bascule_boutique( false ) ); ?>" onclick="return confirm('Masquer de nouveau la liste des produits ?');">Remettre la liste en préparation</a></p>
		<?php endif; ?>
		<?php schiesser_carte_fin(); ?>
	</div>
	<?php
}

add_action( 'admin_post_schiesser_boutique_prete', function () {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_boutique_prete' );
	update_option( SCHIESSER_OPTION_BOUTIQUE_PRETE, empty( $_GET['etat'] ) ? 0 : 1 ); // phpcs:ignore WordPress.Security.NonceVerification
	wp_safe_redirect( admin_url( 'edit.php?post_type=' . SCHIESSER_PRODUIT . '&page=schiesser-lancement&fait=1' ) );
	exit;
} );

/* Barre d'outils du haut (site et administration) : l'état de la liste, d'un coup d'œil. */
add_action( 'admin_bar_menu', function ( $barre ) {
	if ( schiesser_boutique_prete() || ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$barre->add_node( array(
		'id'    => 'schiesser-lancement',
		'title' => '<span class="ab-icon dashicons dashicons-clock" aria-hidden="true"></span><span class="ab-label">Liste des produits : en préparation</span>',
		'href'  => admin_url( 'edit.php?post_type=' . SCHIESSER_PRODUIT . '&page=schiesser-lancement' ),
		'meta'  => array( 'title' => 'Cliquer pour afficher la liste des produits sur le site' ),
	) );
}, 90 );

/* Rappel sur la liste des produits de l'administration. */
add_action( 'admin_notices', function () {
	$e = get_current_screen();
	if ( schiesser_boutique_prete() || ! $e || 'edit-' . SCHIESSER_PRODUIT !== $e->id ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><strong>Liste en préparation :</strong> les produits ne sont pas encore visibles sur le site (message « Produktliste folgt in Kürze »). Ajoutez les photos, puis <a href="' . esc_url( admin_url( 'edit.php?post_type=' . SCHIESSER_PRODUIT . '&page=schiesser-lancement' ) ) . '">affichez la liste</a>.</p></div>';
} );

<?php
/**
 * Pages WooCommerce (boutique, catégories, fiches produits), utilisé
 * uniquement quand l'extension WooCommerce est active.
 *
 * La page « Boutique » garde son contenu de blocs (Hero, grille des
 * produits…) : elle reste modifiable dans l'éditeur comme les autres pages.
 */
defined( 'ABSPATH' ) || exit;

get_header();

$page_boutique = function_exists( 'wc_get_page_id' ) ? get_post( wc_get_page_id( 'shop' ) ) : null;

if ( is_shop() && ! is_search() && ! is_paged() && $page_boutique && has_blocks( $page_boutique->post_content ) ) {
	// Page Boutique composée dans l'éditeur : on affiche ses blocs.
	global $post;
	$post = $page_boutique; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	setup_postdata( $post );
	if ( ! has_block( 'schiesser/hero', $post ) ) {
		get_template_part( 'parts/titre-page', null, array( 'titre' => get_the_title( $post ) ) );
	}
	echo apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput
	wp_reset_postdata();
} elseif ( is_singular( 'product' ) ) {
	$boutique = get_permalink( wc_get_page_id( 'shop' ) );
	?>
	<div class="woo-page woo-page--produit">
		<div class="wrap">
			<nav class="crumb crumb--clair" aria-label="Fil d'Ariane">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a>
				<?php if ( $boutique ) : ?><span aria-hidden="true">·</span><a href="<?php echo esc_url( $boutique ); ?>"><?php echo esc_html( get_the_title( wc_get_page_id( 'shop' ) ) ); ?></a><?php endif; ?>
				<span aria-hidden="true">·</span><span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
			</nav>
			<?php woocommerce_content(); ?>
		</div>
	</div>
	<?php
} else {
	get_template_part( 'parts/titre-page', null, array(
		'titre'    => woocommerce_page_title( false ),
		'surtitre' => 'Boutique en ligne',
		'ariane'   => ( ! is_shop() && wc_get_page_id( 'shop' ) > 0 ) ? array( array( get_the_title( wc_get_page_id( 'shop' ) ), get_permalink( wc_get_page_id( 'shop' ) ) ) ) : array(),
	) );
	?>
	<div class="woo-page">
		<div class="wrap">
			<?php woocommerce_content(); ?>
		</div>
	</div>
	<?php
}

get_footer();

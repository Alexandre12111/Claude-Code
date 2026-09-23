<?php
/**
 * Modèle de repli : articles, archives, recherche.
 */
defined( 'ABSPATH' ) || exit;

get_header();

if ( is_singular() ) {
	while ( have_posts() ) {
		the_post();
		if ( ! has_block( 'schiesser/hero' ) ) {
			get_template_part( 'parts/titre-page', null, array( 'titre' => get_the_title() ) );
		}
		echo '<div class="contenu-libre">';
		the_content();
		echo '</div>';
	}
} else {
	$titre = is_search() ? sprintf( 'Recherche : %s', get_search_query() ) : wp_strip_all_tags( get_the_archive_title() );
	if ( is_home() && ! is_front_page() ) {
		$titre = get_the_title( (int) get_option( 'page_for_posts' ) );
	}
	get_template_part( 'parts/titre-page', null, array( 'titre' => $titre ) );
	echo '<div class="contenu-libre liste-articles">';
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			?>
			<article <?php post_class( 'article-resume' ); ?>>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32, '…' ) ); ?></p>
			</article>
			<?php
		}
		the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) );
	} else {
		echo '<p>Aucun résultat. Essayez un autre mot, ou revenez à l\'<a href="' . esc_url( home_url( '/' ) ) . '">accueil</a>.</p>';
	}
	echo '</div>';
}

get_footer();

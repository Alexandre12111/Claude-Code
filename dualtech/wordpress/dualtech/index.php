<?php
/**
 * Gabarit de repli (articles, archives) : mise en page « texte ».
 */
defined( 'ABSPATH' ) || exit;

get_header();
dualtech_entete_simple( is_singular() ? get_the_title() : wp_get_document_title(), '' );
echo '<section class="sec sec--blanc"><div class="wrap prose">';
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		if ( is_singular() ) {
			the_content();
		} else {
			echo '<article class="prose__item"><h2><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>' . wp_kses_post( wpautop( get_the_excerpt() ) ) . '</article>';
		}
	}
	the_posts_pagination();
} else {
	echo '<p>Aucun contenu à afficher.</p>';
}
echo '</div></section>';
get_footer();

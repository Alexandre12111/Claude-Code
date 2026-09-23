<?php
/**
 * Modèle de repli (articles, archives) : même rendu que les pages.
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	the_content();
}
get_footer();

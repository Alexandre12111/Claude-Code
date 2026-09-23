<?php
/**
 * Pages : le contenu est composé de blocs (Hero, Grille des produits…).
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	the_content();
}
get_footer();

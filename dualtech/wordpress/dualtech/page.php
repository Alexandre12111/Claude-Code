<?php
/**
 * Pages : celles installées depuis la maquette affichent leur contenu tel quel (elles ont leur propre
 * en-tête de page) ; les autres (mentions légales, nouvelle page…) prennent la mise en page « texte ».
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	if ( dualtech_a_son_entete() ) {
		the_content();
	} else {
		get_template_part( 'parts/page-texte' );
	}
}
get_footer();

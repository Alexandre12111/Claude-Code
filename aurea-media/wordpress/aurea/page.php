<?php
/**
 * Pages : celles importées de la maquette affichent leur contenu tel quel (elles ont leur propre
 * hero) ; les autres (mentions légales, nouvelle page…) prennent la mise en page « texte ».
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	if ( aurea_a_son_hero() ) {
		echo '<main id="contenu">';
		the_content();
		echo '</main>';
	} else {
		get_template_part( 'parts/page-texte' );
	}
}
get_footer();

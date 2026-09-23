<?php
/**
 * Pages : le contenu est composé de blocs (Hero, Sections, Grille des produits…).
 * Une page sans Hero reçoit automatiquement un bandeau avec son titre (H1).
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	$avec_hero = has_block( 'schiesser/hero' );
	$maison    = $avec_hero || has_block( 'schiesser/section' ) || has_block( 'schiesser/produits' );

	if ( ! $avec_hero ) {
		get_template_part( 'parts/titre-page', null, array(
			'titre' => get_the_title(),
			'intro' => has_excerpt() ? get_the_excerpt() : '',
		) );
	}
	if ( $maison ) {
		the_content();
	} else {
		// Contenu simple (mentions légales, panier…) : largeur de lecture et marges du site.
		echo '<div class="contenu-libre">';
		the_content();
		echo '</div>';
	}
}
get_footer();

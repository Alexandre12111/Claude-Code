<?php
/**
 * Articles du blog « Conseils » : rédaction guidée et mise en page automatique.
 *
 * - Compositions prêtes à l'emploi dans l'éditeur (bouton + → Compositions → Aurea) :
 *   « Article SEO complet », « En bref », « Encart Bon à savoir », « Appel à l'action », « Questions fréquentes ».
 * - Sommaire généré à partir des intertitres (H2), avec ancres ajoutées automatiquement.
 * - Temps de lecture calculé, auteur et date de mise à jour affichés (signaux E-E-A-T).
 */

defined( 'ABSPATH' ) || exit;

/** Ajoute un identifiant aux intertitres H2 qui n'en ont pas (ancres du sommaire). */
function aurea_ancres_h2( $html ) {
	$vus = array();
	return preg_replace_callback( '/<h2([^>]*)>(.*?)<\/h2>/s', function ( $m ) use ( &$vus ) {
		if ( preg_match( '/\sid="/', $m[1] ) ) {
			return $m[0];
		}
		$id = sanitize_title( wp_strip_all_tags( $m[2] ) ) ?: 'partie';
		$base = $id;
		$n    = 2;
		while ( isset( $vus[ $id ] ) ) {
			$id = $base . '-' . $n++;
		}
		$vus[ $id ] = true;
		return '<h2' . $m[1] . ' id="' . esc_attr( $id ) . '">' . $m[2] . '</h2>';
	}, $html );
}

add_filter( 'the_content', function ( $html ) {
	return is_singular( 'post' ) && in_the_loop() ? aurea_ancres_h2( $html ) : $html;
}, 12 );

/** Sommaire : [ [id, intitulé], … ] d'après les H2 du contenu. */
function aurea_sommaire( $post = null ) {
	$html = aurea_ancres_h2( apply_filters( 'aurea_contenu_brut', do_blocks( (string) get_post_field( 'post_content', $post ) ) ) );
	preg_match_all( '/<h2[^>]*\sid="([^"]+)"[^>]*>(.*?)<\/h2>/s', $html, $m, PREG_SET_ORDER );
	return array_map( function ( $x ) {
		return array( $x[1], wp_strip_all_tags( $x[2] ) );
	}, $m );
}

/** Temps de lecture en minutes (230 mots par minute). */
function aurea_temps_lecture( $post = null ) {
	$mots = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post ) ), 0, 'àâäçéèêëîïôöùûüÿœæÀÂÄÇÉÈÊËÎÏÔÖÙÛÜŸŒÆ’' );
	return max( 1, (int) ceil( $mots / 230 ) );
}

/** Rubrique principale d'un article (Rank Math peut la choisir), pour l'étiquette des cartes. */
function aurea_rubrique( $post = null ) {
	$post     = get_post( $post );
	$primaire = (int) get_post_meta( $post->ID, 'rank_math_primary_category', true );
	if ( $primaire && ( $t = get_term( $primaire, 'category' ) ) && ! is_wp_error( $t ) ) {
		return $t;
	}
	$cats = get_the_category( $post->ID );
	return $cats ? $cats[0] : null;
}

/* ------------------------------------------------------------------ */
/* Compositions de l'éditeur                                           */
/* ------------------------------------------------------------------ */

add_action( 'init', function () {
	register_block_pattern_category( 'aurea', array( 'label' => 'Aurea' ) );

	$en_bref = '<!-- wp:group {"className":"en-bref","layout":{"type":"constrained"}} -->
<div class="wp-block-group en-bref"><!-- wp:heading {"level":2,"className":"en-bref-titre"} -->
<h2 class="wp-block-heading en-bref-titre">En bref : [la question exacte que se pose le lecteur] ?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[Réponse autonome de 130 à 170 mots, compréhensible sans lire le reste : définition, prix ou ordre de grandeur, délai, exemple concret. C’est le passage que Google et les moteurs IA citent le plus souvent.]</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';

	$encart = '<!-- wp:group {"className":"encart-bloc","layout":{"type":"constrained"}} -->
<div class="wp-block-group encart-bloc"><!-- wp:paragraph -->
<p><strong>Bon à savoir</strong></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[Une précision utile, un piège à éviter ou un chiffre clé.]</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';

	$appel = '<!-- wp:group {"className":"appel-bloc","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group appel-bloc"><!-- wp:paragraph -->
<p><strong>Vous voulez un prix précis pour votre site ?</strong><br>Décrivez votre projet, devis détaillé sous 24h.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( aurea_url( 'contact' ) ) . '">Recevoir mon devis</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->';

	$faq = '<!-- wp:heading -->
<h2 class="wp-block-heading">Questions fréquentes</h2>
<!-- /wp:heading -->

<!-- wp:details {"className":"faq-bloc"} -->
<details class="wp-block-details faq-bloc"><summary>[Question posée par vos clients] ?</summary><!-- wp:paragraph -->
<p>[Réponse courte et précise, en deux ou trois phrases.]</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details {"className":"faq-bloc"} -->
<details class="wp-block-details faq-bloc"><summary>[Deuxième question] ?</summary><!-- wp:paragraph -->
<p>[Réponse.]</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->';

	$article = '<!-- wp:paragraph {"className":"intro"} -->
<p class="intro">[Introduction : en deux ou trois phrases, le problème du lecteur et ce que l’article lui apporte. Placez le mot clé principal naturellement.]</p>
<!-- /wp:paragraph -->

' . $en_bref . '

<!-- wp:heading -->
<h2 class="wp-block-heading">[Premier intertitre, formulé comme une question ou une promesse]</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>[Paragraphes courts, un exemple concret, un lien vers une page service si c’est utile au lecteur.]</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">[Deuxième intertitre]</h2>
<!-- /wp:heading -->

<!-- wp:table {"className":"tableau"} -->
<figure class="wp-block-table tableau"><table><thead><tr><th>Critère</th><th>Option A</th><th>Option B</th></tr></thead><tbody><tr><td>[…]</td><td>[…]</td><td>[…]</td></tr></tbody></table></figure>
<!-- /wp:table -->

' . $encart . '

<!-- wp:heading -->
<h2 class="wp-block-heading">[Troisième intertitre]</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><!-- wp:list-item -->
<li>[Point clé]</li>
<!-- /wp:list-item --><!-- wp:list-item -->
<li>[Point clé]</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

' . $faq . '

' . $appel;

	$compositions = array(
		'article'  => array( 'Article SEO complet', 'Introduction, En bref citable, intertitres, tableau, encart, questions fréquentes et appel à l’action.', $article ),
		'en-bref'  => array( 'En bref (réponse citable)', 'Bloc de 130 à 170 mots en haut de l’article, celui que Google et les IA citent.', $en_bref ),
		'encart'   => array( 'Encart « Bon à savoir »', 'Une précision mise en avant sur fond noir.', $encart ),
		'appel'    => array( 'Appel à l’action', 'Phrase et bouton vers la page Contact.', $appel ),
		'faq'      => array( 'Questions fréquentes', 'Questions dépliables, à placer en fin d’article.', $faq ),
	);
	foreach ( $compositions as $nom => $c ) {
		register_block_pattern( 'aurea/' . $nom, array(
			'title'       => $c[0],
			'description' => $c[1],
			'categories'  => array( 'aurea' ),
			'postTypes'   => array( 'post', 'aurea_realisation' ),
			'content'     => $c[2],
		) );
	}
} );

/* Nouvel article : la composition « Article SEO complet » est proposée d'office. */
add_filter( 'default_content', function ( $contenu, $post ) {
	if ( 'post' === $post->post_type && '' === $contenu ) {
		$p = WP_Block_Patterns_Registry::get_instance()->get_registered( 'aurea/article' );
		return $p ? $p['content'] : $contenu;
	}
	return $contenu;
}, 10, 2 );

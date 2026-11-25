<?php
/**
 * En-tête et pied de page de la maquette, repères et adresses des pages.
 *
 * Les fichiers parts/entete.html, parts/pied.html et contenu/*.html sont produits par
 * wordpress/outils/exporter_theme.py à partir de la maquette. Ils contiennent des repères :
 * {{accueil}}, {{url:slug}}, {{theme}}, {{admin_post}}. Ils sont remplacés à l'affichage (en-tête,
 * pied de page) ou au moment de l'installation (contenu des pages, ensuite modifiable dans l'éditeur).
 */

defined( 'ABSPATH' ) || exit;

/** Pages livrées avec le thème (contenu/pages.json) : slug => titre, SEO, données structurées. */
function dualtech_pages() {
	static $pages = null;
	if ( null === $pages ) {
		$json  = @file_get_contents( DUALTECH_DIR . '/contenu/pages.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
		$pages = json_decode( (string) $json, true ) ?: array();
	}
	return $pages;
}

/** Page publiée d'après son slug (ou null). */
function dualtech_page_par_slug( $slug ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	return ( $page && 'trash' !== $page->post_status ) ? $page : null;
}

/**
 * Adresse d'une page du site d'après son slug (« accueil » = page d'accueil).
 * Si la page n'existe pas encore, l'adresse prévue est renvoyée : le lien sera bon dès sa création.
 */
function dualtech_url( $cle ) {
	static $cache = array();
	if ( isset( $cache[ $cle ] ) ) {
		return $cache[ $cle ];
	}
	if ( 'accueil' === $cle ) {
		$url = home_url( '/' );
	} else {
		$page = dualtech_page_par_slug( $cle );
		$url  = $page ? get_permalink( $page ) : home_url( '/' . $cle . '/' );
	}
	return $cache[ $cle ] = $url;
}

/** Remplace les repères par les adresses du site. */
function dualtech_remplacer( $html ) {
	$html = strtr( (string) $html, array(
		'{{accueil}}'    => esc_url( dualtech_url( 'accueil' ) ),
		'{{theme}}'      => esc_url( DUALTECH_URI ),
		'{{admin_post}}' => esc_url( admin_url( 'admin-post.php' ) ),
	) );
	return preg_replace_callback( '/\{\{url:([a-z0-9-]+)\}\}/', function ( $m ) {
		return esc_url( dualtech_url( $m[1] ) );
	}, $html );
}

/** Adresse de la page affichée (pour le lien actif du menu). */
function dualtech_url_courante() {
	if ( is_front_page() ) {
		return dualtech_url( 'accueil' );
	}
	return is_singular() ? get_permalink() : '';
}

/** Affiche une partie de la maquette (parts/entete.html, parts/pied.html), lien de la page courante marqué. */
function dualtech_partie( $nom ) {
	$fichier = DUALTECH_DIR . '/parts/' . $nom . '.html';
	if ( ! is_readable( $fichier ) ) {
		return;
	}
	$html    = dualtech_remplacer( file_get_contents( $fichier ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$courant = dualtech_url_courante();
	if ( 'entete' === $nom && $courant ) {
		// Menu et tiroir mobile : la page courante porte aria-current (souligné dans le menu), pas le logo.
		$html = preg_replace( '/<a (?!class="nav__logo")([^>]*?)href="' . preg_quote( esc_url( $courant ), '/' ) . '"/', '<a $1href="' . esc_url( $courant ) . '" aria-current="page"', $html );
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- gabarit du thème, repères échappés
}

/** Contenu livré avec le thème (contenu/<nom>.html), repères remplacés. */
function dualtech_contenu_livre( $nom ) {
	$fichier = DUALTECH_DIR . '/contenu/' . $nom . '.html';
	return is_readable( $fichier ) ? dualtech_remplacer( file_get_contents( $fichier ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/** Le contenu de la page a-t-il son propre en-tête de page (hero de la maquette) ? */
function dualtech_a_son_entete( $post = null ) {
	$post = get_post( $post );
	return $post && preg_match( '/<section[^>]+class="(hero|phero|iv-hero)[ "]/', $post->post_content );
}

/** En-tête de page sombre de la maquette, pour les pages « texte », les archives et la page 404. */
function dualtech_entete_simple( $titre, $intro = '' ) {
	echo '<section class="phero"><div class="phero__grille-deco" aria-hidden="true"></div><div class="wrap">'
		. '<nav aria-label="Fil d’Ariane"><ol class="ariane"><li><a href="' . esc_url( dualtech_url( 'accueil' ) ) . '">Accueil</a></li><li aria-current="page">' . esc_html( $titre ) . '</li></ol></nav>'
		. '<h1>' . esc_html( $titre ) . '</h1>'
		. ( $intro ? '<p class="lead">' . esc_html( $intro ) . '</p>' : '' )
		. '</div></section>';
}

/* Repères laissés dans le contenu d'une page (collé depuis contenu/*.html) : remplacés à l'affichage. */
add_filter( 'the_content', function ( $html ) {
	return false === strpos( $html, '{{' ) ? $html : dualtech_remplacer( $html );
}, 5 );

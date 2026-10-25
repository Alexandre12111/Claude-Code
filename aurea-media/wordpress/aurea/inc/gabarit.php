<?php
/**
 * En-tête et pied de page de la maquette, repères et adresses des pages.
 *
 * Les fichiers parts/entete.html, parts/pied.html et contenu/*.html sont produits par
 * wordpress/outils/exporter_theme.py à partir de la maquette. Ils contiennent des repères :
 * {{accueil}}, {{url:slug}}, {{theme}}, {{tel}}, {{tel_affiche}}, {{tel_inter}}, {{email}},
 * {{admin_post}}, {{annee}}. Ils sont remplacés à l'affichage (en-tête, pied de page) ou au moment
 * de l'import (contenu des pages, qui devient ensuite modifiable dans l'éditeur).
 */

defined( 'ABSPATH' ) || exit;

/** Informations des pages livrées avec le thème (contenu/pages.json). */
function aurea_pages() {
	static $pages = null;
	if ( null === $pages ) {
		$json  = file_get_contents( AUREA_DIR . '/contenu/pages.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$pages = json_decode( (string) $json, true ) ?: array();
	}
	return $pages;
}

/**
 * Adresse d'une page du site d'après sa clé.
 *
 * accueil, conseils, realisations, realisation:<slug>, article:<slug>, ou le slug d'une page.
 * Si la page n'existe pas (encore), l'adresse prévue est renvoyée : le lien sera bon dès sa création.
 */
function aurea_url( $cle ) {
	static $cache = array();
	if ( isset( $cache[ $cle ] ) ) {
		return $cache[ $cle ];
	}
	if ( 'accueil' === $cle ) {
		$url = home_url( '/' );
	} elseif ( 'conseils' === $cle ) {
		$blog = (int) get_option( 'page_for_posts' );
		$url  = $blog ? get_permalink( $blog ) : home_url( '/conseils/' );
	} elseif ( 'realisations' === $cle ) {
		$url = post_type_exists( 'aurea_realisation' ) ? get_post_type_archive_link( 'aurea_realisation' ) : home_url( '/realisations/' );
	} elseif ( 0 === strpos( $cle, 'realisation:' ) ) {
		$post = post_type_exists( 'aurea_realisation' ) ? get_page_by_path( substr( $cle, 12 ), OBJECT, 'aurea_realisation' ) : null;
		$url  = $post ? get_permalink( $post ) : aurea_url( 'realisations' );
	} elseif ( 0 === strpos( $cle, 'article:' ) ) {
		$post = get_page_by_path( substr( $cle, 8 ), OBJECT, 'post' );
		$url  = $post ? get_permalink( $post ) : aurea_url( 'conseils' );
	} else {
		$page = get_page_by_path( $cle );
		$url  = $page ? get_permalink( $page ) : home_url( '/' . $cle . '/' );
	}
	return $cache[ $cle ] = $url;
}

/** Remplace les repères par les adresses et coordonnées du site. */
function aurea_remplacer( $html ) {
	$c    = aurea_coordonnees();
	$html = strtr( $html, array(
		'{{accueil}}'     => esc_url( home_url( '/' ) ),
		'{{theme}}'       => esc_url( AUREA_URI ),
		'{{tel}}'         => esc_attr( $c['tel'] ),
		'{{tel_affiche}}' => esc_html( $c['tel_affiche'] ),
		'{{tel_inter}}'   => esc_html( $c['tel_inter'] ),
		'{{email}}'       => esc_html( $c['email'] ),
		'{{admin_post}}'  => esc_url( admin_url( 'admin-post.php' ) ),
		'{{annee}}'       => esc_html( wp_date( 'Y' ) ),
	) );
	return preg_replace_callback( '/\{\{url:([a-z0-9:-]+)\}\}/', function ( $m ) {
		return esc_url( aurea_url( $m[1] ) );
	}, $html );
}

/** Affiche une partie de la maquette (parts/entete.html, parts/pied.html). */
function aurea_partie( $nom ) {
	$fichier = AUREA_DIR . '/parts/' . $nom . '.html';
	if ( is_readable( $fichier ) ) {
		echo aurea_avis_reperes( aurea_remplacer( file_get_contents( $fichier ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.AlternativeFunctions
	}
}

/** Contenu livré avec le thème (contenu/<nom>.html), repères remplacés. */
function aurea_contenu_livre( $nom, $afficher = true ) {
	$fichier = AUREA_DIR . '/contenu/' . $nom . '.html';
	$html    = is_readable( $fichier ) ? aurea_remplacer( file_get_contents( $fichier ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions
	// À l'affichage, la note et le nombre d'avis sont résolus ; à l'installation, les repères restent dans la page
	return $afficher ? aurea_avis_reperes( $html ) : $html;
}

/** Le contenu de la page a-t-il son propre en-tête de page (hero de la maquette) ? */
function aurea_a_son_hero( $post = null ) {
	$post = get_post( $post );
	return $post && preg_match( '/<(section|header)[^>]+class="(hero|phero)[ "]/', $post->post_content );
}

/** Fil d'Ariane d'une page simple (pages de texte, articles, archives). */
function aurea_ariane( $etapes ) {
	$liens = array( '<a href="' . esc_url( home_url( '/' ) ) . '">Accueil</a>' );
	$n     = count( $etapes );
	foreach ( array_values( $etapes ) as $i => $e ) {
		$liens[] = '<span>/</span>';
		$liens[] = ( $i === $n - 1 || empty( $e[1] ) ) ? '<span aria-current="page">' . esc_html( $e[0] ) . '</span>' : '<a href="' . esc_url( $e[1] ) . '">' . esc_html( $e[0] ) . '</a>';
	}
	return '<nav class="crumbs rv" aria-label="Fil d’Ariane">' . implode( '', $liens ) . '</nav>';
}

/** Note et nombre d'avis Google, résolus à l'affichage (repères {{avis_note}} et {{avis_total}}). */
function aurea_avis_reperes( $html ) {
	if ( false === strpos( (string) $html, '{{avis_' ) ) {
		return $html;
	}
	$r = function_exists( 'aurea_avis_resume' ) ? aurea_avis_resume() : array( 'note_txt' => '4,9', 'total' => 39 );
	return strtr( $html, array( '{{avis_note}}' => esc_html( $r['note_txt'] ), '{{avis_total}}' => esc_html( (string) $r['total'] ) ) );
}
add_filter( 'the_content', 'aurea_avis_reperes', 99 );
add_filter( 'render_block', 'aurea_avis_reperes', 99 );

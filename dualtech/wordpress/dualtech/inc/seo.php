<?php
/**
 * Référencement.
 *
 * Rank Math gère les titres, descriptions, Open Graph et le plan du site (réglés par l'installation,
 * modifiables page par page). Le thème fournit les données structurées de la maquette (événement,
 * lieu, organisateurs, programme, intervenants), mises à jour à partir de DualTech Studio :
 * Rank Math garde son propre graphe pour les autres pages (mentions légales, articles…).
 * Sans Rank Math, le thème affiche lui même titre, description, Open Graph et données structurées.
 */

defined( 'ABSPATH' ) || exit;

/** Fiche SEO de la page affichée (contenu/pages.json), ou tableau vide. */
function dualtech_info_page() {
	static $info = null;
	if ( null !== $info ) {
		return $info;
	}
	$pages = dualtech_pages();
	$slug  = '';
	if ( is_front_page() ) {
		$slug = 'accueil';
	} elseif ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
	}
	return $info = ( $slug && isset( $pages[ $slug ] ) ) ? $pages[ $slug ] + array( 'slug' => $slug ) : array();
}

/** Rank Math est-il actif (et écrit-il les balises du site) ? Avant son assistant de démarrage, il n'écrit rien. */
function dualtech_rank_math() {
	if ( ! defined( 'RANK_MATH_VERSION' ) && ! class_exists( 'RankMath' ) ) {
		return false;
	}
	return ! ( is_callable( array( '\\RankMath\\Helper', 'is_invalid_registration' ) ) && \RankMath\Helper::is_invalid_registration() );
}

/** Rank Math installé (même si son assistant n'a pas encore été passé). */
function dualtech_rank_math_installe() {
	return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
}

/** Personne (schema.org) d'après un intervenant. */
function dualtech_personne( $i, $image = false ) {
	$p = array( '@type' => 'Person', 'name' => $i['nom'] );
	if ( $i['fonction'] ) {
		$p['jobTitle'] = $i['fonction'];
	}
	if ( $i['orga'] ) {
		$p['worksFor'] = array( '@type' => 'Organization', 'name' => $i['orga'] );
	}
	if ( $image && $i['photo'] ) {
		$p['image'] = $i['photo'];
	}
	return $p;
}

/** Données structurées de la page : graphe de la maquette, parties dynamiques recalculées. */
function dualtech_graphe() {
	$info = dualtech_info_page();
	if ( empty( $info['graphe'] ) ) {
		return array();
	}
	$site  = untrailingslashit( home_url() );
	$json  = wp_json_encode( $info['graphe'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$json  = str_replace( array( '{{site}}/wp-content/themes/dualtech', '{{site}}' ), array( DUALTECH_URI, $site ), $json );
	$graphe = json_decode( $json, true );
	$ivs    = array_values( dt_intervenants() );
	$tous   = dt_intervenants();

	foreach ( $graphe as &$noeud ) {
		$type = $noeud['@type'] ?? '';
		// Événement principal : intervenants et programme d'après DualTech Studio
		if ( 'Event' === $type && isset( $noeud['performer'] ) ) {
			$noeud['performer'] = array_map( 'dualtech_personne', $ivs );
		}
		if ( 'Event' === $type && isset( $noeud['subEvent'] ) ) {
			$noeud['subEvent'] = array();
			foreach ( dt_seances() as $s ) {
				if ( in_array( $s['type'], array( 'networking', 'autres' ), true ) && ! $s['intervenants'] ) {
					continue;
				}
				$sous = array(
					'@type'               => 'Event',
					'name'                => wp_strip_all_tags( $s['titre'] ),
					'startDate'           => '2026-11-26T' . str_replace( 'h', ':', $s['debut'] ) . ':00+01:00',
					'endDate'             => '2026-11-26T' . str_replace( 'h', ':', $s['fin'] ) . ':00+01:00',
					'location'            => array( '@id' => $site . '/#campus-cyber' ),
					'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
					'eventStatus'         => 'https://schema.org/EventScheduled',
				);
				$noms = array();
				foreach ( array_merge( $s['intervenants'], $s['moderateurs'] ) as $id ) {
					if ( isset( $tous[ $id ] ) ) {
						$noms[] = array( '@type' => 'Person', 'name' => $tous[ $id ]['nom'] );
					}
				}
				if ( $noms ) {
					$sous['performer'] = $noms;
				}
				$noeud['subEvent'][] = $sous;
			}
		}
		// Page Intervenants : liste complète
		if ( isset( $noeud['mainEntity']['@type'] ) && 'ItemList' === $noeud['mainEntity']['@type'] ) {
			$noeud['mainEntity']['numberOfItems']   = count( $ivs );
			$noeud['mainEntity']['itemListElement'] = array();
			foreach ( $ivs as $n => $i ) {
				$noeud['mainEntity']['itemListElement'][] = array( '@type' => 'ListItem', 'position' => $n + 1, 'item' => dualtech_personne( $i, true ) );
			}
		}
		// Date de mise à jour réelle de la page
		if ( isset( $noeud['dateModified'] ) && is_singular() ) {
			$noeud['dateModified'] = get_the_modified_date( 'Y-m-d' );
		}
	}
	unset( $noeud );
	return $graphe;
}

/* Données structurées de la maquette sur ses pages. */
add_action( 'wp_head', function () {
	$graphe = dualtech_graphe();
	if ( $graphe ) {
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graphe ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
	}
}, 30 );

/* Rank Math : pas de second graphe sur les pages qui ont déjà le leur (évite les doublons Organization, WebPage…). */
add_filter( 'rank_math/json_ld', function ( $data ) {
	return dualtech_info_page() ? array() : $data;
}, 999 );

/* Sans Rank Math : titre, description, Open Graph et robots de la maquette. */
add_filter( 'pre_get_document_title', function ( $titre ) {
	if ( dualtech_rank_math() ) {
		return $titre;
	}
	$info = dualtech_info_page();
	return ! empty( $info['seo_titre'] ) ? $info['seo_titre'] : $titre;
}, 20 );

add_action( 'wp_head', function () {
	if ( dualtech_rank_math() ) {
		return;
	}
	$info  = dualtech_info_page();
	$titre = $info['seo_titre'] ?? wp_get_document_title();
	$desc  = $info['seo_description'] ?? get_bloginfo( 'description' );
	$url   = is_front_page() ? home_url( '/' ) : ( is_singular() ? get_permalink() : '' );
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
	echo '<meta property="og:locale" content="fr_FR">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="DualTech">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $titre ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $url ) {
		echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	}
	echo '<meta property="og:image" content="' . esc_url( DUALTECH_URI . '/assets/img/og-dualtech-2026.jpg' ) . '">' . "\n";
	echo '<meta property="og:image:width" content="1200">' . "\n" . '<meta property="og:image:height" content="630">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}, 5 );

/* Rank Math : image de partage par défaut (celle de la maquette) quand la page n'en a pas. */
add_filter( 'rank_math/opengraph/facebook/image', function ( $image ) {
	return $image ?: DUALTECH_URI . '/assets/img/og-dualtech-2026.jpg';
} );

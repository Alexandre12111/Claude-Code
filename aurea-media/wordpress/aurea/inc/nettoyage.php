<?php
/**
 * Base propre : retrait des anciens contenus, sans perte de référencement.
 *
 * - Articles : tous les anciens articles vont à la corbeille (récupérables 30 jours), sauf ceux
 *   publiés par le thème et ceux rédigés depuis l'installation.
 * - Pages : celles qui ne font pas partie de la nouvelle architecture vont à la corbeille.
 * - Rubriques et mots clés devenus vides : supprimés.
 * - Chaque adresse retirée est redirigée (301) vers la page la plus proche par son sujet
 *   (avocat → page avocat, prix → tarifs, Shopify → e-commerce…), jamais vers l'accueil par défaut.
 * - Les redirections déjà présentes dans Rank Math qui envoyaient vers l'accueil sont réorientées
 *   de la même façon.
 *
 * Un aperçu détaillé est affiché avant toute action (Apparence → Installation Aurea → Base propre).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Page la plus proche d'une ancienne adresse, d'après les mots de son slug.
 * Renvoie une clé pour aurea_url() : un slug de page, « tarifs », « conseils »…
 */
function aurea_cible_pour( $chemin, $defaut = 'conseils' ) {
	$mots   = array_filter( preg_split( '/[^a-z0-9]+/', remove_accents( strtolower( (string) $chemin ) ) ) );
	$regles = array(
		'site-internet-avocat'            => array( 'avocat', 'notaire', 'juridique', 'juriste', 'comptable', 'droit', 'cabinet' ),
		'site-internet-professions-sante' => array( 'medecin', 'medical', 'kine', 'osteo', 'psy', 'sante', 'dentiste', 'infirmier', 'therapeute', 'esthetique', 'praticien' ),
		'site-internet-architecte'        => array( 'architecte', 'architecture', 'immobilier', 'decorateur', 'interieur' ),
		'site-internet-restaurant'        => array( 'restaurant', 'restauration', 'hotel', 'gite', 'traiteur', 'boulangerie', 'cafe' ),
		'site-internet-artisan-btp'       => array( 'artisan', 'artisans', 'btp', 'plombier', 'electricien', 'chauffagiste', 'serrurier', 'batiment', 'renovateur', 'couvreur', 'menuisier', 'macon', 'peintre', 'pac' ),
		'site-internet-coach-consultant'  => array( 'coach', 'coaching', 'consultant', 'consultants', 'formateur', 'therapeute' ),
		'agence-web-hauts-de-seine'       => array( 'boulogne', 'billancourt', 'clamart', 'issy', 'neuilly', 'levallois', 'nanterre', '92', 'hauts' ),
		'agence-web-paris'                => array( 'paris' ),
		'creation-site-ecommerce'         => array( 'ecommerce', 'commerce', 'boutique', 'shopify', 'woocommerce', 'prestashop', 'vendre', 'vente' ),
		'tarifs'                          => array( 'prix', 'tarif', 'tarifs', 'cout', 'couts', 'budget', 'combien' ),
		'refonte-site-internet'           => array( 'refonte', 'refondre', 'moderniser', 'migration' ),
		'logo-identite-visuelle'          => array( 'logo', 'logos', 'identite', 'charte', 'branding', 'graphique', 'marque' ),
		'motion-design'                   => array( 'motion', 'video', 'videos', 'animation' ),
		'maquettes-presentation'          => array( 'presentation', 'pitch', 'deck', 'powerpoint' ),
		'realisations'                    => array( 'realisation', 'realisations', 'portfolio', 'references' ),
		'contact'                         => array( 'contact', 'contacter' ),
		'agence'                          => array( 'agence', 'equipe', 'propos', 'fondateur' ),
		'creation-site-vitrine'           => array( 'vitrine', 'vitrines', 'wordpress', 'creation', 'site', 'seo', 'referencement', 'google', 'homepage', 'accueil' ),
	);
	foreach ( $regles as $cle => $liste ) {
		if ( array_intersect( $mots, $liste ) ) {
			return $cle;
		}
	}
	return $defaut;
}

/** Chemin relatif (sans domaine ni barres de début et de fin) d'une adresse du site. */
function aurea_chemin( $url ) {
	$chemin = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	$base   = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base && 0 === strpos( $chemin, $base . '/' ) ) {
		$chemin = substr( $chemin, strlen( $base ) + 1 );
	}
	return $chemin;
}

/** Slugs des pages de la nouvelle architecture (gardées). */
function aurea_slugs_gardes() {
	return array_merge( array_keys( aurea_pages_a_creer() ), array( 'conseils' ) );
}

/**
 * Ce que le nettoyage ferait, sans rien modifier.
 *
 * @return array { articles[], pages[], termes[], redirections_accueil[] }
 *         chaque élément : id, titre, adresses (anciens chemins), cible (clé aurea_url)
 */
function aurea_nettoyage_apercu() {
	$installe = (int) get_option( 'aurea_installe_le' );
	$apercu   = array( 'articles' => array(), 'pages' => array(), 'termes' => array(), 'redirections_accueil' => array() );

	$du_theme = array();
	foreach ( array_keys( aurea_pages() ) as $cle ) {
		if ( 0 === strpos( $cle, 'article:' ) ) {
			$du_theme[] = substr( $cle, 8 );
		}
	}
	$articles = get_posts( array( 'post_type' => 'post', 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'numberposts' => -1 ) );
	foreach ( $articles as $a ) {
		$recent = $installe && strtotime( $a->post_modified_gmt . ' UTC' ) > $installe; // écrit ou retouché depuis l'installation
		if ( get_post_meta( $a->ID, '_aurea_version', true ) || $recent || in_array( $a->post_name, $du_theme, true ) ) {
			continue; // articles du thème, ou écrits depuis l'installation
		}
		$chemins = array_unique( array_filter( array( $a->post_name, aurea_chemin( get_permalink( $a ) ) ) ) );
		$apercu['articles'][] = array( 'id' => $a->ID, 'titre' => get_the_title( $a ), 'adresses' => $chemins, 'cible' => aurea_cible_pour( $a->post_name . ' ' . $a->post_title ) );
	}

	$gardes = aurea_slugs_gardes();
	$speciales = array_filter( array( (int) get_option( 'page_on_front' ), (int) get_option( 'page_for_posts' ) ) );
	foreach ( get_pages( array( 'post_status' => 'publish,draft,pending,private' ) ) as $p ) {
		if ( in_array( $p->post_name, $gardes, true ) || in_array( $p->ID, $speciales, true ) || get_post_meta( $p->ID, '_aurea_version', true ) ) {
			continue;
		}
		$apercu['pages'][] = array( 'id' => $p->ID, 'titre' => get_the_title( $p ), 'adresses' => array_filter( array( 'publish' === $p->post_status ? aurea_chemin( get_permalink( $p ) ) : '' ) ), 'cible' => aurea_cible_pour( $p->post_name . ' ' . $p->post_title, 'accueil' ) );
	}

	// Rubriques et mots clés qui n'auront plus d'article (la rubrique par défaut reste)
	$restants = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'post__not_in' => wp_list_pluck( $apercu['articles'], 'id' ) ?: array( 0 ) ) );
	foreach ( array( 'category', 'post_tag' ) as $tax ) {
		foreach ( get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) ) as $t ) {
			if ( 'category' === $tax && (int) get_option( 'default_category' ) === $t->term_id ) {
				continue;
			}
			$utilise = $restants && get_objects_in_term( $t->term_id, $tax ) && array_intersect( (array) get_objects_in_term( $t->term_id, $tax ), $restants );
			if ( $utilise ) {
				continue;
			}
			$apercu['termes'][] = array( 'id' => $t->term_id, 'taxonomie' => $tax, 'titre' => $t->name, 'adresses' => array_unique( array( $t->slug, aurea_chemin( get_term_link( $t ) ) ) ), 'cible' => aurea_cible_pour( $t->slug . ' ' . $t->name, 'accueil' ) );
		}
	}

	// Redirections Rank Math existantes qui envoient vers l'accueil
	global $wpdb;
	$table = $wpdb->prefix . 'rank_math_redirections';
	if ( aurea_rank_math_actif() && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$accueil = untrailingslashit( home_url( '/' ) );
		foreach ( $wpdb->get_results( "SELECT id, sources, url_to FROM {$table} WHERE status = 'active'", ARRAY_A ) as $r ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
			if ( untrailingslashit( (string) $r['url_to'] ) !== $accueil && '/' !== $r['url_to'] ) {
				continue;
			}
			$sources = (array) maybe_unserialize( $r['sources'] );
			$motifs  = implode( ' ', wp_list_pluck( $sources, 'pattern' ) );
			$cible   = aurea_cible_pour( $motifs );
			if ( 'conseils' !== $cible ) {
				$apercu['redirections_accueil'][] = array( 'id' => (int) $r['id'], 'titre' => $motifs, 'adresses' => wp_list_pluck( $sources, 'pattern' ), 'cible' => $cible, 'sources' => $sources );
			}
		}
	}
	return $apercu;
}

/** Ajoute une redirection 301 (Rank Math si possible, sinon liste gérée par le thème). */
function aurea_ajouter_redirection( $chemin, $cle ) {
	$chemin = trim( $chemin, '/' );
	$cible  = aurea_url( $cle );
	if ( '' === $chemin || aurea_chemin( $cible ) === $chemin || in_array( $chemin, aurea_slugs_gardes(), true ) ) {
		return false; // une nouvelle page occupe déjà cette adresse
	}
	if ( aurea_redirections_rank_math() ) {
		if ( \RankMath\Redirections\DB::match_redirections( $chemin ) ) {
			return false;
		}
		$r = \RankMath\Redirections\Redirection::from( array(
			'sources'     => array( array( 'pattern' => $chemin, 'comparison' => 'exact' ) ),
			'url_to'      => $cible,
			'header_code' => '301',
		) );
		return (bool) $r->save();
	}
	$extra            = (array) get_option( 'aurea_redirections_extra', array() );
	$extra[ $chemin ] = $cle;
	update_option( 'aurea_redirections_extra', $extra, false );
	return true;
}

/** Exécute le nettoyage décrit par l'aperçu. */
function aurea_nettoyer() {
	$a        = aurea_nettoyage_apercu();
	$compte   = array( 'articles' => 0, 'pages' => 0, 'termes' => 0, 'redirections' => 0, 'reorientees' => 0 );
	foreach ( array( 'articles', 'pages' ) as $type ) {
		foreach ( $a[ $type ] as $e ) {
			foreach ( $e['adresses'] as $chemin ) {
				$compte['redirections'] += (int) aurea_ajouter_redirection( $chemin, $e['cible'] );
			}
			if ( wp_trash_post( $e['id'] ) ) {
				$compte[ $type ]++;
			}
		}
	}
	foreach ( $a['termes'] as $e ) {
		foreach ( $e['adresses'] as $chemin ) {
			$compte['redirections'] += (int) aurea_ajouter_redirection( $chemin, $e['cible'] );
		}
		if ( ! is_wp_error( wp_delete_term( $e['id'], $e['taxonomie'] ) ) ) {
			$compte['termes']++;
		}
	}
	if ( $a['redirections_accueil'] && class_exists( '\RankMath\Redirections\DB' ) ) {
		foreach ( $a['redirections_accueil'] as $e ) {
			\RankMath\Redirections\DB::update( array( 'id' => $e['id'], 'sources' => $e['sources'], 'url_to' => aurea_url( $e['cible'] ), 'header_code' => '301', 'status' => 'active' ) );
			$compte['reorientees']++;
		}
	}
	// La page de confidentialité de WordPress devient la nouvelle
	$conf = aurea_page_par_slug( 'politique-confidentialite' );
	if ( $conf ) {
		update_option( 'wp_page_for_privacy_policy', $conf->ID );
	}
	update_option( 'aurea_nettoye_le', time() );
	return sprintf(
		'Base propre : %d articles et %d pages mis à la corbeille (récupérables 30 jours), %d rubriques et mots clés supprimés, %d redirections ajoutées, %d redirections vers l’accueil réorientées.',
		$compte['articles'], $compte['pages'], $compte['termes'], $compte['redirections'], $compte['reorientees']
	);
}

/* Redirections ajoutées par le nettoyage quand Rank Math n'est pas actif. */
add_filter( 'aurea_plan_redirections', function ( $plan ) {
	return array_merge( (array) get_option( 'aurea_redirections_extra', array() ), $plan );
} );

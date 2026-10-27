<?php
/**
 * Articles du blog livrés avec le thème (contenu/articles/<slug>.html, réglages dans contenu/articles.json).
 * Un article est créé s'il n'existe pas, et mis à jour s'il vient du thème ; un article écrit ou modifié
 * à la main dans WordPress (sans la marque du thème) n'est jamais remplacé.
 */
defined( 'ABSPATH' ) || exit;

/** Réglages des articles livrés, triés dans l'ordre éditorial. */
function aurea_articles_livres() {
	static $liste = null;
	if ( null === $liste ) {
		$fichier = AUREA_DIR . '/contenu/articles.json';
		$liste   = is_readable( $fichier ) ? (array) json_decode( (string) file_get_contents( $fichier ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
		uasort( $liste, function ( $a, $b ) {
			return ( $a['ordre'] ?? 0 ) <=> ( $b['ordre'] ?? 0 );
		} );
	}
	return $liste;
}

/** Remplace les images livrées ({{img_id:…}}, {{img_url:…}}, {{img_alt:…}}) par celles de la médiathèque. */
function aurea_articles_images( $html, $images ) {
	return preg_replace_callback( '/\{\{img_(id|url|alt):([a-z0-9\/._-]+)\}\}/', function ( $m ) use ( $images ) {
		$alt = (string) ( $images[ $m[2] ] ?? '' );
		$id  = function_exists( 'aurea_image_theme' ) ? aurea_image_theme( $m[2], $alt ) : 0;
		if ( 'id' === $m[1] ) {
			return (string) (int) $id;
		}
		if ( 'url' === $m[1] ) {
			return esc_url( $id ? (string) wp_get_attachment_image_url( $id, 'large' ) : AUREA_URI . '/assets/' . $m[2] );
		}
		return esc_attr( $alt );
	}, $html );
}

/**
 * Crée ou met à jour les articles livrés.
 *
 * @param bool $forcer Réécrit aussi les articles du thème déjà à jour.
 * @return string Compte rendu.
 */
function aurea_installer_articles( $forcer = false ) {
	$compte = array( 'crees' => 0, 'maj' => 0, 'a_jour' => 0, 'gardes' => 0 );
	$a_ecrire = array();
	$maintenant = time();
	kses_remove_filters();
	// 1. Créer les articles absents (les liens entre articles ne se résolvent qu'une fois tous créés)
	foreach ( aurea_articles_livres() as $slug => $a ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $post ) {
			$du_theme = get_post_meta( $post->ID, '_aurea_article', true ) || get_post_meta( $post->ID, '_aurea_version', true );
			if ( ! $du_theme && '' !== trim( wp_strip_all_tags( $post->post_content ) ) ) {
				$compte['gardes']++;
				continue;
			}
			if ( ! $forcer && get_post_meta( $post->ID, '_aurea_article', true ) === AUREA_VERSION ) {
				$compte['a_jour']++;
				continue;
			}
			$a_ecrire[ $slug ] = $post->ID;
			$compte['maj']++;
			continue;
		}
		list( $nom_cat, $slug_cat ) = $a['categorie'];
		$cat = term_exists( $slug_cat, 'category' ) ?: wp_insert_term( $nom_cat, 'category', array( 'slug' => $slug_cat ) );
		$id  = wp_insert_post( wp_slash( array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $a['titre'],
			'post_name'     => $slug,
			'post_content'  => '',
			'post_date'     => wp_date( 'Y-m-d H:i:s', $maintenant - 60 * (int) $a['ordre'] ),
			'post_category' => is_array( $cat ) ? array( (int) $cat['term_id'] ) : array(),
		) ), true );
		if ( ! is_wp_error( $id ) ) {
			$a_ecrire[ $slug ] = $id;
			$compte['crees']++;
		}
	}
	// 2. Écrire le contenu, les images et les réglages Rank Math
	foreach ( $a_ecrire as $slug => $id ) {
		$a       = aurea_articles_livres()[ $slug ];
		$fichier = AUREA_DIR . '/contenu/articles/' . $slug . '.html';
		if ( ! is_readable( $fichier ) ) {
			continue;
		}
		$contenu = aurea_remplacer( aurea_articles_images( (string) file_get_contents( $fichier ), (array) $a['images'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		list( $nom_cat, $slug_cat ) = $a['categorie'];
		$cat = term_exists( $slug_cat, 'category' ) ?: wp_insert_term( $nom_cat, 'category', array( 'slug' => $slug_cat ) );
		$cat_id = is_array( $cat ) ? (int) $cat['term_id'] : 0;
		wp_update_post( wp_slash( array(
			'ID'            => $id,
			'post_title'    => $a['titre'],
			'post_content'  => $contenu,
			'post_excerpt'  => $a['extrait'],
			'post_category' => $cat_id ? array( $cat_id ) : array(),
		) ) );
		$une = aurea_image_theme( $a['image'], (string) ( $a['images'][ $a['image'] ] ?? '' ) );
		if ( $une ) {
			set_post_thumbnail( $id, $une );
		}
		update_post_meta( $id, 'rank_math_focus_keyword', $a['mot_cle'] );
		update_post_meta( $id, 'rank_math_title', $a['titre_seo'] );
		update_post_meta( $id, 'rank_math_description', $a['description'] );
		if ( $cat_id ) {
			update_post_meta( $id, 'rank_math_primary_category', $cat_id );
		}
		update_post_meta( $id, '_aurea_article', AUREA_VERSION );
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $id, 'fr' ); // le blog est en français
		}
	}
	kses_init_filters();
	return sprintf( 'Articles : %d créés, %d mis à jour, %d déjà à jour, %d articles personnels gardés.', $compte['crees'], $compte['maj'], $compte['a_jour'], $compte['gardes'] );
}

/* Après chaque mise à jour du thème : articles livrés installés ou mis à jour une fois. */
add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ! current_user_can( 'publish_posts' ) || get_option( 'aurea_articles_version' ) === AUREA_VERSION ) {
		return;
	}
	update_option( 'aurea_articles_version', AUREA_VERSION );
	set_transient( 'aurea_articles_message', aurea_installer_articles(), 300 );
} );
add_action( 'admin_notices', function () {
	$message = get_transient( 'aurea_articles_message' );
	if ( $message ) {
		delete_transient( 'aurea_articles_message' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>Aurea Media</strong> : ' . esc_html( $message ) . '</p></div>';
	}
} );

<?php
/**
 * Études de cas des réalisations : textes livrés avec le thème (contenu/etudes/<cle>.html) et réglages Rank Math
 * (contenu/etudes.json). Une fiche reçoit son étude si elle est vide, ou si son texte vient déjà du thème ;
 * un texte écrit à la main dans WordPress n'est jamais remplacé.
 */
defined( 'ABSPATH' ) || exit;

/** Réglages SEO des études, par clé de réalisation. */
function aurea_etudes_reglages() {
	static $reglages = null;
	if ( null === $reglages ) {
		$fichier  = AUREA_DIR . '/contenu/etudes.json';
		$reglages = is_readable( $fichier ) ? (array) json_decode( (string) file_get_contents( $fichier ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $reglages;
}

/** Clé d'étude d'une fiche : clé Aurea Studio, sinon slug de la fiche rapproché par l'adresse du site client. */
function aurea_etude_cle( $r ) {
	$reglages = aurea_etudes_reglages();
	$cle      = function_exists( 'aurea_cle_real' ) ? aurea_cle_real( $r ) : '';
	if ( isset( $reglages[ $cle ] ) ) {
		return $cle;
	}
	$domaine = preg_replace( '#^www\.#', '', (string) wp_parse_url( (string) ( $r['url'] ?? '' ), PHP_URL_HOST ) );
	foreach ( array_keys( $reglages ) as $c ) {
		$f = AUREA_DIR . '/contenu/etudes/' . $c . '.html';
		if ( $domaine && is_readable( $f ) && false !== strpos( (string) file_get_contents( $f ), '//' . $domaine ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			return $c;
		}
	}
	return '';
}

/**
 * Écrit les études de cas dans les fiches.
 *
 * @param bool $forcer Remplace aussi les textes du thème déjà à jour.
 * @return string Compte rendu.
 */
function aurea_installer_etudes( $forcer = false ) {
	if ( ! function_exists( 'aurea_realisations' ) ) {
		return 'Aurea Studio n’est pas actif : études de cas non installées.';
	}
	$ancienne_havel = trim( wp_strip_all_tags( aurea_contenu_livre( 'etude-havel-trading' ) ) );
	$compte         = array( 'ecrites' => 0, 'a_jour' => 0, 'gardees' => 0 );
	kses_remove_filters();
	foreach ( aurea_realisations( array( 'tout' => true ) ) as $r ) {
		$cle = aurea_etude_cle( $r );
		$f   = AUREA_DIR . '/contenu/etudes/' . $cle . '.html';
		if ( ! $cle || ! is_readable( $f ) ) {
			continue;
		}
		$actuel   = trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $r['id'] ) ) );
		$du_theme = (bool) get_post_meta( $r['id'], '_aurea_etude', true ) || ( $ancienne_havel && $actuel === $ancienne_havel );
		if ( '' !== $actuel && ! $du_theme ) {
			$compte['gardees']++;
			continue;
		}
		if ( ! $forcer && get_post_meta( $r['id'], '_aurea_etude', true ) === AUREA_VERSION ) {
			$compte['a_jour']++;
			continue;
		}
		$reg = aurea_etudes_reglages()[ $cle ];
		wp_update_post( wp_slash( array(
			'ID'           => $r['id'],
			'post_content' => aurea_remplacer( (string) file_get_contents( $f ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions
			'post_excerpt' => $reg['extrait'] ?? '',
		) ) );
		update_post_meta( $r['id'], 'rank_math_focus_keyword', $reg['mot_cle'] ?? '' );
		update_post_meta( $r['id'], 'rank_math_title', $reg['titre'] ?? '' );
		update_post_meta( $r['id'], 'rank_math_description', $reg['description'] ?? '' );
		if ( ! empty( $reg['lieu'] ) ) {
			update_post_meta( $r['id'], '_real_lieu', $reg['lieu'] );
		}
		update_post_meta( $r['id'], '_aurea_etude', AUREA_VERSION );
		$compte['ecrites']++;
	}
	kses_init_filters();
	if ( class_exists( '\\RankMath\\Sitemap\\Cache' ) && is_callable( array( '\\RankMath\\Sitemap\\Cache', 'invalidate_storage' ) ) ) {
		\RankMath\Sitemap\Cache::invalidate_storage();
	}
	return sprintf( 'Études de cas : %d écrites, %d déjà à jour, %d textes personnels gardés.', $compte['ecrites'], $compte['a_jour'], $compte['gardees'] );
}

/* Après chaque mise à jour du thème : études de cas installées ou mises à jour une fois. */
add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ! current_user_can( 'edit_posts' ) || ! function_exists( 'aurea_realisations' ) || get_option( 'aurea_etudes_version' ) === AUREA_VERSION ) {
		return;
	}
	update_option( 'aurea_etudes_version', AUREA_VERSION );
	set_transient( 'aurea_etudes_message', aurea_installer_etudes(), 300 );
} );
add_action( 'admin_notices', function () {
	$message = get_transient( 'aurea_etudes_message' );
	if ( $message ) {
		delete_transient( 'aurea_etudes_message' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>Aurea Media</strong> : ' . esc_html( $message ) . '</p></div>';
	}
} );

<?php
/**
 * Redirections 301 des anciennes adresses (plan de l'audit SEO d'octobre 2026).
 *
 * À l'installation, elles sont créées dans Rank Math (Rank Math → Redirections), où elles se
 * suivent et se modifient. Tant que le module Redirections de Rank Math n'est pas actif, le thème
 * les applique lui-même.
 *
 * Les anciens articles (aurea-media.fr/mon-article/) passent sous /conseils/mon-article/ :
 * une adresse introuvable qui correspond au slug d'un article publié y est redirigée.
 */

defined( 'ABSPATH' ) || exit;

/** Ancienne adresse (sans domaine) → clé de la nouvelle page (voir aurea_url()). */
function aurea_redirections() {
	return array(
		'creation-de-site-vitrine-paris'                       => 'creation-site-vitrine',
		'agence-web-site-vitrine'                              => 'creation-site-vitrine',
		'agence-web-creation-site-vitrine'                     => 'creation-site-vitrine',
		'creation-site-vitrine-professionnel'                  => 'creation-site-vitrine',
		'sites-vitrines-sur-mesure'                            => 'creation-site-vitrine',
		'creation-de-site-e-commerce-paris'                    => 'creation-site-ecommerce',
		'branding-identite'                                    => 'logo-identite-visuelle',
		'branding-identite-visuelle'                           => 'logo-identite-visuelle',
		'maquettes-de-presentation'                            => 'maquettes-presentation',
		'refonte-site-vitrine'                                 => 'refonte-site-internet',
		'agence-web-a-paris'                                   => 'agence-web-paris',
		'choisir-agence-web-paris'                             => 'agence-web-paris',
		'chosir-agence-web-paris'                              => 'agence-web-paris',
		'site-internet-boulogne-billancourt'                   => 'agence-web-hauts-de-seine',
		'creation-de-site-e-commerce-boulogne'                 => 'agence-web-hauts-de-seine',
		'agence-web-collectivites-boulogne-billancourt'        => 'agence-web-hauts-de-seine',
		'agence-web-boulogne-billancourt'                      => 'agence-web-hauts-de-seine',
		'creation-site-internet-clamart'                       => 'agence-web-hauts-de-seine',
		'agence-seo-clamart'                                   => 'agence-web-hauts-de-seine',
		'agence-web-clamart'                                   => 'agence-web-hauts-de-seine',
		'site-vitrine-avocat'                                  => 'site-internet-avocat',
		'site-internet-avocat-2025'                            => 'site-internet-avocat',
		'site-vitrine-expert-comptable'                        => 'site-internet-avocat',
		'site-vitrine-notaire'                                 => 'site-internet-avocat',
		'site-vitrine-medecin'                                 => 'site-internet-professions-sante',
		'site-vitrine-kinesitherapeute'                        => 'site-internet-professions-sante',
		'site-vitrine-osteopathe-kine'                         => 'site-internet-professions-sante',
		'site-vitrine-psychologue'                             => 'site-internet-professions-sante',
		'site-vitrine-medecin-esthetique'                      => 'site-internet-professions-sante',
		'site-vitrine-architecte'                              => 'site-internet-architecte',
		'site-vitrine-architecte-interieur'                    => 'site-internet-architecte',
		'site-vitrine-restaurant'                              => 'site-internet-restaurant',
		'pourquoi-site-internet-restaurant'                    => 'site-internet-restaurant',
		'site-vitrine-pour-artisan'                            => 'site-internet-artisan-btp',
		'site-vitrine-artisan-renovateur-en-batiment'          => 'site-internet-artisan-btp',
		'site-vitrine-plombier'                                => 'site-internet-artisan-btp',
		'site-vitrine-electricien'                             => 'site-internet-artisan-btp',
		'site-vitrine-chauffagiste-pac'                        => 'site-internet-artisan-btp',
		'site-vitrine-serrurier'                               => 'site-internet-artisan-btp',
		'site-vitrine-artisan'                                 => 'site-internet-artisan-btp',
		'site-internet-artisan'                                => 'site-internet-artisan-btp',
		'pourquoi-site-internet-artisan'                       => 'site-internet-artisan-btp',
		'site-vitrine-coach-consultant'                        => 'site-internet-coach-consultant',
		'site-internet-coach-therapeute'                       => 'site-internet-coach-consultant',
		'prix-site-vitrine-paris'                              => 'tarifs',
		'creation-site-vitrine-tpe-paris'                      => 'tarifs',
		'prix-site-vitrine-2025'                               => 'tarifs',
		'realisations-agence-web'                              => 'realisations',
		'contact-agence'                                       => 'contact',
		'contact-agence-web'                                   => 'contact',
		'contact-projet-web'                                   => 'contact',
		'nos-services'                                         => 'accueil',
	);
}

/** Les redirections sont elles gérées par Rank Math ? */
function aurea_redirections_rank_math() {
	return aurea_rank_math_actif() && class_exists( '\RankMath\Redirections\Redirection' ) && in_array( 'redirections', (array) get_option( 'rank_math_modules', array() ), true );
}

/**
 * Crée les redirections dans Rank Math (les adresses déjà redirigées sont laissées telles quelles).
 *
 * @return array { ajoutees, existantes } ou WP_Error si Rank Math n'est pas prêt.
 */
function aurea_installer_redirections() {
	if ( ! aurea_rank_math_actif() || ! class_exists( '\RankMath\Redirections\Redirection' ) ) {
		return new WP_Error( 'aurea_redirections', 'Rank Math n’est pas actif : les redirections sont appliquées par le thème en attendant.' );
	}
	$modules = (array) get_option( 'rank_math_modules', array() );
	if ( ! in_array( 'redirections', $modules, true ) ) {
		$modules[] = 'redirections';
		update_option( 'rank_math_modules', $modules );
		if ( class_exists( '\RankMath\Installer' ) && method_exists( '\RankMath\Installer', 'create_tables' ) ) {
			\RankMath\Installer::create_tables( array( 'redirections' ) );
		}
	}
	$ajoutees = 0;
	$existantes = 0;
	$accueil = untrailingslashit( home_url( '/' ) );
	foreach ( apply_filters( 'aurea_plan_redirections', aurea_redirections() ) as $ancienne => $cle ) {
		$existe = class_exists( '\RankMath\Redirections\DB' ) ? \RankMath\Redirections\DB::match_redirections( $ancienne ) : false;
		if ( $existe ) {
			// Une ancienne redirection vers l'accueil est réorientée vers la bonne page.
			if ( ! empty( $existe['id'] ) && in_array( untrailingslashit( (string) $existe['url_to'] ), array( $accueil, '' ), true ) && 'accueil' !== $cle ) {
				\RankMath\Redirections\DB::update( array( 'id' => $existe['id'], 'sources' => $existe['sources'], 'url_to' => aurea_url( $cle ), 'header_code' => '301', 'status' => 'active' ) );
			}
			$existantes++;
			continue;
		}
		$r = \RankMath\Redirections\Redirection::from( array(
			'sources'     => array( array( 'pattern' => $ancienne, 'comparison' => 'exact' ) ),
			'url_to'      => aurea_url( $cle ),
			'header_code' => '301',
		) );
		if ( $r->save() ) {
			$ajoutees++;
		}
	}
	update_option( 'aurea_redirections_rank_math', 1 );
	return compact( 'ajoutees', 'existantes' );
}

/* Sans Rank Math (ou avant l'installation) : le thème redirige lui-même. */
add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$chemin = trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$base   = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base && 0 === strpos( $chemin, $base ) ) {
		$chemin = trim( substr( $chemin, strlen( $base ) ), '/' );
	}
	if ( '' === $chemin ) {
		return;
	}
	$plan = apply_filters( 'aurea_plan_redirections', aurea_redirections() );
	if ( isset( $plan[ $chemin ] ) && ! get_option( 'aurea_redirections_rank_math' ) ) {
		wp_safe_redirect( aurea_url( $plan[ $chemin ] ), 301 );
		exit;
	}
	// Ancien article à la racine : /mon-article/ → /conseils/mon-article/
	if ( false === strpos( $chemin, '/' ) ) {
		$article = get_page_by_path( sanitize_title( $chemin ), OBJECT, 'post' );
		if ( $article && 'publish' === $article->post_status ) {
			wp_safe_redirect( get_permalink( $article ), 301 );
			exit;
		}
	}
}, 1 );

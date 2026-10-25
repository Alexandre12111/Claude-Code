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

/* ------------------------------------------------------------------ */
/* Réparation des redirections héritées de l'ancien site (Rank Math)   */
/* ------------------------------------------------------------------ */

/**
 * Remet en ordre les redirections de Rank Math laissées par l'ancien site :
 * - les pages introuvables ne sont plus envoyées vers l'accueil (Google y voit des « soft 404 ») ;
 * - une redirection dont l'adresse de départ est devenue une vraie page (ex. /creation-site-vitrine/,
 *   autrefois une rubrique) est désactivée : la page s'affiche enfin ;
 * - une redirection vers l'accueil est réorientée vers la page la plus proche par son sujet ;
 * - les chaînes (A → B → C) deviennent directes (A → C).
 *
 * Lancée automatiquement une fois après chaque mise à jour du thème, et par l'étape Redirections.
 *
 * @return string Compte rendu.
 */
function aurea_reparer_redirections() {
	global $wpdb;
	if ( ! aurea_rank_math_actif() ) {
		return 'Rank Math n’est pas actif : rien à réparer.';
	}
	// 1. Plus de redirection de toutes les erreurs 404 vers l'accueil
	$general  = get_option( 'rank-math-options-general' );
	$fallback = 0;
	if ( is_array( $general ) && isset( $general['redirections_fallback'] ) && 'default' !== $general['redirections_fallback'] ) {
		$general['redirections_fallback'] = 'default';
		update_option( 'rank-math-options-general', $general );
		$fallback = 1;
	}
	// Titre SEO des études de cas (« Havel Trading | Aurea Media » est trop court et peu parlant)
	$titres = get_option( 'rank-math-options-titles' );
	if ( is_array( $titres ) && in_array( $titres['pt_aurea_realisation_title'] ?? '', array( '', '%title% %sep% %sitename%', '%title% %page% %sep% %sitename%' ), true ) ) {
		$titres['pt_aurea_realisation_title']       = 'Site %title% : étude de cas %sep% Aurea Media';
		$titres['pt_aurea_realisation_description'] = '%excerpt%';
		update_option( 'rank-math-options-titles', $titres );
	}
	$table = $wpdb->prefix . 'rank_math_redirections';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $fallback ? 'Pages introuvables : plus de redirection vers l’accueil.' : 'Aucune redirection Rank Math à réparer.';
	}
	$lignes  = $wpdb->get_results( "SELECT id, sources, url_to, header_code FROM {$table} WHERE status = 'active'", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	$accueil = untrailingslashit( set_url_scheme( home_url( '/' ), 'https' ) );
	$plan    = apply_filters( 'aurea_plan_redirections', aurea_redirections() );
	$compte  = array( 'liberees' => 0, 'reorientees' => 0, 'chaines' => 0 );
	$maj     = function ( $id, $champs ) use ( $wpdb, $table ) {
		$wpdb->update( $table, $champs + array( 'updated' => current_time( 'mysql' ) ), array( 'id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	};
	$sources = array(); // chemin exact → id, pour repérer les chaînes
	foreach ( $lignes as $i => $r ) {
		$r['sources']   = (array) maybe_unserialize( $r['sources'] );
		$lignes[ $i ]   = $r;
		foreach ( $r['sources'] as $s ) {
			if ( 'exact' === ( $s['comparison'] ?? '' ) ) {
				$sources[ trim( (string) $s['pattern'], '/' ) ] = $i;
			}
		}
	}
	foreach ( $lignes as $i => $r ) {
		$motifs = array_map( function ( $s ) {
			return trim( (string) ( $s['pattern'] ?? '' ), '/' );
		}, $r['sources'] );
		// 2. L'adresse de départ est une vraie page du site : la redirection l'écrasait
		$vivante = false;
		foreach ( $r['sources'] as $s ) {
			$chemin = trim( (string) ( $s['pattern'] ?? '' ), '/' );
			if ( 'exact' === ( $s['comparison'] ?? '' ) && $chemin && ( get_page_by_path( $chemin ) || ( function_exists( 'aurea_slugs_gardes' ) && in_array( $chemin, aurea_slugs_gardes(), true ) ) || 'realisations' === $chemin ) ) {
				$vivante = true;
			}
		}
		if ( $vivante ) {
			$maj( $r['id'], array( 'status' => 'inactive' ) );
			$compte['liberees']++;
			unset( $lignes[ $i ] );
			continue;
		}
		// 3. Vers l'accueil : réorientée vers la page la plus proche
		$vers = untrailingslashit( set_url_scheme( (string) $r['url_to'], 'https' ) );
		if ( in_array( $vers, array( $accueil, '', '/' ), true ) || untrailingslashit( (string) $r['url_to'] ) === '' ) {
			$cle = '';
			foreach ( $motifs as $m ) {
				if ( isset( $plan[ $m ] ) ) {
					$cle = $plan[ $m ];
				}
			}
			if ( ! $cle && function_exists( 'aurea_cible_pour' ) ) {
				$cle = aurea_cible_pour( implode( ' ', $motifs ), '' );
			}
			if ( $cle && 'accueil' !== $cle ) {
				$maj( $r['id'], array( 'url_to' => aurea_url( $cle ) ) );
				$lignes[ $i ]['url_to'] = aurea_url( $cle );
				$compte['reorientees']++;
			}
		}
	}
	// 4. Chaînes : la destination est elle même redirigée → on vise directement la fin
	foreach ( $lignes as $i => $r ) {
		$vers  = $r['url_to'];
		$tours = 0;
		while ( $tours++ < 5 ) {
			$chemin = function_exists( 'aurea_chemin' ) ? aurea_chemin( $vers ) : trim( (string) wp_parse_url( $vers, PHP_URL_PATH ), '/' );
			if ( '' === $chemin || ! isset( $sources[ $chemin ], $lignes[ $sources[ $chemin ] ] ) || $sources[ $chemin ] === $i ) {
				break;
			}
			$vers = $lignes[ $sources[ $chemin ] ]['url_to'];
		}
		if ( $vers !== $r['url_to'] && $vers ) {
			$maj( $r['id'], array( 'url_to' => $vers ) );
			$compte['chaines']++;
		}
	}
	// Cache des redirections de Rank Math : vidé, il se reconstruit tout seul
	$cache = $wpdb->prefix . 'rank_math_redirections_cache';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cache ) ) === $cache ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( "DELETE FROM {$cache}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
	}
	$s = function ( $n ) {
		return $n > 1 ? 's' : '';
	};
	return sprintf(
		'Redirections réparées : %s%d redirection%s masquant une vraie page désactivée%s, %d redirection%s vers l’accueil réorientée%s, %d chaîne%s raccourcie%s.',
		$fallback ? 'pages introuvables plus redirigées vers l’accueil, ' : '',
		$compte['liberees'], $s( $compte['liberees'] ), $s( $compte['liberees'] ),
		$compte['reorientees'], $s( $compte['reorientees'] ), $s( $compte['reorientees'] ),
		$compte['chaines'], $s( $compte['chaines'] ), $s( $compte['chaines'] )
	);
}

/* Après chaque mise à jour du thème : réparation automatique (une seule fois par version). */
add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_options' ) || get_option( 'aurea_reparation_version' ) === AUREA_VERSION || ! aurea_rank_math_actif() ) {
		return;
	}
	update_option( 'aurea_reparation_version', AUREA_VERSION );
	set_transient( 'aurea_reparation_message', aurea_reparer_redirections(), HOUR_IN_SECONDS );
} );
add_action( 'admin_notices', function () {
	$m = get_transient( 'aurea_reparation_message' );
	if ( $m && current_user_can( 'manage_options' ) ) {
		delete_transient( 'aurea_reparation_message' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>Thème Aurea :</strong> ' . esc_html( $m ) . '</p></div>';
	}
} );

/* Erreur 404 restante : adresse reconnue par son sujet → page la plus proche ; sinon, vraie page 404. */
add_action( 'template_redirect', function () {
	if ( ! is_404() || ! function_exists( 'aurea_cible_pour' ) ) {
		return;
	}
	$chemin = function_exists( 'aurea_chemin' ) ? aurea_chemin( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( '' === $chemin || substr_count( $chemin, '/' ) > 3 || preg_match( '/\.(php|xml|txt|js|css|png|jpe?g|webp|svg|ico|gif|pdf|zip)$/i', $chemin ) || 0 === strpos( $chemin, 'wp-' ) ) {
		return;
	}
	$cle = aurea_cible_pour( str_replace( '/', ' ', $chemin ), '' );
	if ( $cle && aurea_chemin( aurea_url( $cle ) ) !== $chemin ) {
		wp_safe_redirect( aurea_url( $cle ), 301 );
		exit;
	}
}, 5 );

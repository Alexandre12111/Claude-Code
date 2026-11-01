<?php
/**
 * Plan du site et robots.txt : toujours disponibles pour Google et Bing.
 *
 * - Plan du site (Rank Math) : si les règles d'adresses de WordPress sont régénérées sans celles
 *   du plan (une extension, une mise à jour…), sitemap_index.xml répond 404 jusqu'au prochain
 *   « Enregistrer » dans Réglages → Permaliens. Le thème le détecte et les régénère tout seul.
 * - robots.txt : l'hébergeur sert un fichier par défaut (« Crawl-delay: 10 », sans plan du site)
 *   tant qu'aucun fichier robots.txt n'existe à la racine du site. Réglages maison → Entretien
 *   permet de l'écrire en un clic.
 */

defined( 'ABSPATH' ) || exit;

/** Vrai si le plan du site de Rank Math est activé. */
function schiesser_plan_actif() {
	return class_exists( '\RankMath\Helper' ) && method_exists( '\RankMath\Helper', 'is_module_active' ) && \RankMath\Helper::is_module_active( 'sitemap' );
}

/** Vrai si les règles d'adresses enregistrées contiennent celles du plan du site. */
function schiesser_regles_plan_ok() {
	$regles = get_option( 'rewrite_rules' );
	if ( ! is_array( $regles ) || ! $regles ) {
		return true; // pas encore générées : WordPress les génère au prochain affichage
	}
	foreach ( array_keys( $regles ) as $motif ) {
		if ( 0 === strpos( (string) $motif, 'sitemap_index' ) ) {
			return true;
		}
	}
	return false;
}

/*
 * Garde : sur une page du site (toutes les extensions chargées, Rank Math compris), si les règles
 * du plan manquent, on régénère les règles. Au plus une fois toutes les 10 minutes.
 */
add_action( 'wp_loaded', function () {
	if ( is_admin() || wp_doing_cron() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( ! schiesser_plan_actif() || schiesser_regles_plan_ok() || get_transient( 'schiesser_plan_garde' ) ) {
		return;
	}
	set_transient( 'schiesser_plan_garde', 1, 10 * MINUTE_IN_SECONDS );
	flush_rewrite_rules( false );
	$n = (array) get_option( 'schiesser_plan_repare', array() );
	update_option( 'schiesser_plan_repare', array( 'nombre' => (int) ( $n['nombre'] ?? 0 ) + 1, 'date' => current_time( 'mysql' ) ), false );
}, 99 );

/** Contenu conseillé du fichier robots.txt. */
function schiesser_robots_contenu() {
	return "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: " . home_url( '/sitemap_index.xml' ) . "\n";
}

/** Chemin du fichier robots.txt à la racine du site. */
function schiesser_robots_fichier() {
	if ( ! function_exists( 'get_home_path' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	return trailingslashit( get_home_path() ) . 'robots.txt';
}

/** Ce que Google voit vraiment : code et contenu d'une adresse du site (écran Entretien seulement). */
function schiesser_seo_verifier( $chemin ) {
	$r = wp_remote_get( home_url( $chemin ), array( 'timeout' => 10, 'redirection' => 3, 'headers' => array( 'Cache-Control' => 'no-cache' ) ) );
	if ( is_wp_error( $r ) ) {
		return array( 'code' => 0, 'corps' => '', 'erreur' => $r->get_error_message() );
	}
	return array( 'code' => (int) wp_remote_retrieve_response_code( $r ), 'corps' => (string) wp_remote_retrieve_body( $r ), 'erreur' => '' );
}

add_action( 'admin_post_schiesser_plan_reparer', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_plan_reparer' );
	flush_rewrite_rules( true );
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache(); // WP Super Cache
	}
	wp_safe_redirect( schiesser_url_entretien( array( 'fait' => 'plan' ) ) . '#referencement' );
	exit;
} );

add_action( 'admin_post_schiesser_robots_ecrire', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_robots_ecrire' );
	$ok = false !== @file_put_contents( schiesser_robots_fichier(), schiesser_robots_contenu() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
	wp_safe_redirect( schiesser_url_entretien( array( 'fait' => $ok ? 'robots' : 'robots-erreur' ) ) . '#referencement' );
	exit;
} );

/** Carte « Référencement » de l'écran Entretien. */
function schiesser_entretien_referencement() {
	schiesser_carte_debut( 'Plan du site et robots.txt', 'Ce que Google et Bing lisent en premier : la liste des pages (plan du site) et les consignes du fichier robots.txt.', 'search' );
	echo '<div id="referencement"></div>';

	// Plan du site
	if ( ! schiesser_plan_actif() ) {
		schiesser_entretien_etat( 'alerte', 'warning', '<strong>Plan du site désactivé.</strong> Rank Math → Tableau de bord → Modules : activez « Plan du site ».' );
	} else {
		$plan = schiesser_seo_verifier( '/sitemap_index.xml' );
		$ok   = 200 === $plan['code'] && false !== strpos( $plan['corps'], '<sitemap' );
		$rep  = (array) get_option( 'schiesser_plan_repare', array() );
		schiesser_entretien_etat( $ok ? 'ok' : 'alerte', $ok ? 'yes-alt' : 'warning', $ok
			? '<strong>Plan du site disponible</strong> : <a href="' . esc_url( home_url( '/sitemap_index.xml' ) ) . '" target="_blank" rel="noopener">sitemap_index.xml</a>. Adresse à donner à Google Search Console et à Bing.' . ( ! empty( $rep['nombre'] ) ? ' Le thème l’a remis en service automatiquement ' . (int) $rep['nombre'] . ' fois (dernière fois le ' . esc_html( mysql2date( 'j F Y à H:i', $rep['date'] ) ) . ').' : '' )
			: '<strong>Plan du site indisponible</strong> (réponse ' . (int) $plan['code'] . ( $plan['erreur'] ? ', ' . esc_html( $plan['erreur'] ) : '' ) . '). Le bouton ci-dessous régénère les adresses du site et vide le cache.' );
		if ( ! $ok ) {
			echo '<p><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_plan_reparer' ), 'schiesser_plan_reparer' ) ) . '">Réparer le plan du site</a></p>';
		}
	}

	// robots.txt
	$robots  = schiesser_seo_verifier( '/robots.txt' );
	$fichier = schiesser_robots_fichier();
	$existe  = file_exists( $fichier );
	$sitemap = false !== stripos( $robots['corps'], 'sitemap:' );
	$delai   = false !== stripos( $robots['corps'], 'crawl-delay' );
	$bon     = 200 === $robots['code'] && $sitemap && ! $delai;
	if ( 0 === $robots['code'] ) {
		schiesser_entretien_etat( '', 'info', 'Vérification du robots.txt impossible depuis le serveur (' . esc_html( $robots['erreur'] ) . '). Ouvrez <a href="' . esc_url( home_url( '/robots.txt' ) ) . '" target="_blank" rel="noopener">robots.txt</a> pour le voir.' );
	} else {
		schiesser_entretien_etat( $bon ? 'ok' : 'alerte', $bon ? 'yes-alt' : 'warning', $bon
		? '<strong>robots.txt correct</strong> : il indique le plan du site et ne ralentit pas les moteurs.'
		: '<strong>robots.txt à corriger</strong> :' . ( $delai ? ' « Crawl-delay » ralentit Bing (et Copilot) ;' : '' ) . ( $sitemap ? '' : ' le plan du site n’y est pas indiqué ;' ) . ( $existe ? ' le fichier existe à la racine du site mais son contenu n’est pas celui conseillé.' : ' c’est le fichier par défaut de l’hébergeur, car aucun fichier robots.txt n’existe à la racine du site.' ) );
	}
	echo '<details style="margin:0 0 14px"><summary>Contenu actuel et contenu conseillé</summary><div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:10px">'
		. '<pre style="flex:1;min-width:240px;margin:0;padding:12px;background:#f6f7f7;white-space:pre-wrap">' . esc_html( trim( $robots['corps'] ) ?: '(vide)' ) . '</pre>'
		. '<pre style="flex:1;min-width:240px;margin:0;padding:12px;background:#f0f6f1;white-space:pre-wrap">' . esc_html( schiesser_robots_contenu() ) . '</pre></div></details>';
	if ( ! $bon && 0 !== $robots['code'] ) {
		if ( wp_is_writable( dirname( $fichier ) ) && ( ! $existe || wp_is_writable( $fichier ) ) ) {
			echo '<p><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_robots_ecrire' ), 'schiesser_robots_ecrire' ) ) . '">' . ( $existe ? 'Remplacer le fichier robots.txt' : 'Créer le fichier robots.txt' ) . '</a></p>';
		} else {
			echo '<p>Le site ne peut pas écrire à sa racine : déposez un fichier <code>robots.txt</code> avec le contenu conseillé dans le dossier du site (gestionnaire de fichiers de l’hébergeur ou FTP).</p>';
		}
	}
	schiesser_carte_fin();
}

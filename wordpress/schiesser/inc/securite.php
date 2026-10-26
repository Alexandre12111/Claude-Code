<?php
/**
 * Sécurité et rapidité, sans extension.
 *
 * - Connexion : 5 essais de mot de passe ratés en 15 minutes bloquent la connexion
 *   depuis cette adresse pendant 15 minutes ; messages d'erreur neutres (ils ne disent
 *   pas si l'identifiant existe) ; liste des comptes cachée aux visiteurs.
 * - XML-RPC (ancienne porte d'entrée des robots) désactivé.
 * - En-têtes de sécurité : pas d'affichage du site dans un cadre étranger, type de fichier
 *   respecté, adresse d'origine discrète, caméra et micro refusés.
 * - Rapidité : la grande photo du haut de page est demandée au navigateur dès le début
 *   du chargement (préchargement), avant même la lecture de la page.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Connexion                                                           */
/* ------------------------------------------------------------------ */

define( 'SCHIESSER_ESSAIS_MAX', 5 );
define( 'SCHIESSER_ESSAIS_DUREE', 15 * MINUTE_IN_SECONDS );

function schiesser_cle_essais() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return 'schiesser_essais_' . md5( $ip . wp_salt( 'auth' ) );
}

add_action( 'wp_login_failed', function () {
	$cle = schiesser_cle_essais();
	set_transient( $cle, (int) get_transient( $cle ) + 1, SCHIESSER_ESSAIS_DUREE );
} );

add_filter( 'authenticate', function ( $utilisateur, $identifiant ) {
	if ( '' === (string) $identifiant ) {
		return $utilisateur; // simple affichage de la page de connexion
	}
	if ( (int) get_transient( schiesser_cle_essais() ) >= SCHIESSER_ESSAIS_MAX ) {
		return new WP_Error( 'schiesser_trop_essais', '<strong>Trop de tentatives de connexion.</strong> Par sécurité, la connexion est bloquée depuis cette adresse pendant 15 minutes. Mot de passe oublié ? Utilisez le lien sous le formulaire.' );
	}
	return $utilisateur;
}, 30, 2 );

add_action( 'wp_login', function () {
	delete_transient( schiesser_cle_essais() );
} );

/* Messages neutres : on ne révèle pas si l'identifiant ou l'e-mail existe. */
add_filter( 'login_errors', function ( $message ) {
	global $errors;
	$codes = is_wp_error( $errors ) ? $errors->get_error_codes() : array();
	if ( array_intersect( $codes, array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
		$reste = SCHIESSER_ESSAIS_MAX - (int) get_transient( schiesser_cle_essais() );
		return '<strong>Identifiant ou mot de passe incorrect.</strong>' . ( $reste > 0 && $reste < SCHIESSER_ESSAIS_MAX ? ' Encore ' . $reste . ' essai' . ( $reste > 1 ? 's' : '' ) . ' avant un blocage de 15 minutes.' : '' );
	}
	return $message;
} );

/* XML-RPC : inutile pour ce site, et très visé par les robots. */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' ); // aucune méthode, même anonyme (pingback, listMethods…)
add_filter( 'wp_headers', function ( $h ) {
	unset( $h['X-Pingback'] );
	return $h;
} );

/* Liste des comptes : pas d'accès pour les visiteurs (ni /?author=1, ni l'API). */
add_action( 'template_redirect', function () {
	if ( ! is_user_logged_in() && isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
	if ( is_author() && ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 ); // avant la redirection de WordPress vers /author/identifiant/, qui dévoilerait l'identifiant
add_filter( 'rest_endpoints', function ( $routes ) {
	if ( ! is_user_logged_in() ) {
		unset( $routes['/wp/v2/users'], $routes['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $routes;
} );

/* Version de WordPress : pas affichée dans le code des pages. */
remove_action( 'wp_head', 'wp_generator' );

/* ------------------------------------------------------------------ */
/* En-têtes de sécurité                                                */
/* ------------------------------------------------------------------ */

add_action( 'send_headers', function () {
	if ( headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), payment=(), usb=()' );
} );

/* ------------------------------------------------------------------ */
/* Préchargement de la grande photo du haut de page                    */
/* ------------------------------------------------------------------ */

/** Première photo « hero » de la page affichée : [ id, adresse de secours ]. */
function schiesser_photo_hero() {
	if ( ! is_singular() ) {
		return null;
	}
	$post = get_post();
	if ( ! $post || false === strpos( $post->post_content, '<!-- wp:schiesser/hero' ) ) {
		return null;
	}
	foreach ( parse_blocks( $post->post_content ) as $b ) {
		if ( 'schiesser/hero' === $b['blockName'] ) {
			return array( (int) ( $b['attrs']['imageId'] ?? 0 ), (string) ( $b['attrs']['imageUrl'] ?? '' ) );
		}
		if ( $b['blockName'] && 'core/spacer' !== $b['blockName'] ) {
			return null; // la photo n'est pas en haut de page : rien à précharger
		}
	}
	return null;
}

add_action( 'wp_head', function () {
	$h = schiesser_photo_hero();
	if ( ! $h ) {
		return;
	}
	list( $id, $url ) = $h;
	if ( $id && wp_attachment_is_image( $id ) ) {
		$src    = wp_get_attachment_image_url( $id, 'full' );
		$srcset = wp_get_attachment_image_srcset( $id, 'full' );
		echo '<link rel="preload" as="image" href="' . esc_url( $src ) . '"' . ( $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="100vw"' : '' ) . ' fetchpriority="high">' . "\n";
	} elseif ( $url ) {
		echo '<link rel="preload" as="image" href="' . esc_url( $url ) . '" fetchpriority="high">' . "\n";
	}
}, 2 );

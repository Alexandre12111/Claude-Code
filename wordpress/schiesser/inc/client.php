<?php
/**
 * Expérience client, sans commande en ligne (les commandes se font par téléphone).
 *
 * - « Appeler pour commander » qui suit les horaires : ouvert, « Appeler maintenant » ;
 *   fermé, « Fermé · ouvre demain à 7 h 30 » et « Laisser un message » (formulaire prérempli).
 * - « Ma sélection » : le visiteur met des produits de côté (cœur), puis appelle ou envoie
 *   la liste par le formulaire. Gardée dans son navigateur, sans compte ni cookie.
 * - Partage : WhatsApp, e-mail, copier le lien (et le partage du téléphone quand il existe).
 * - Navigation plus fluide : transitions entre les pages et pages suivantes préparées à l'avance.
 *
 * Comportements : assets/js/site.js. Styles : assets/css/schiesser.css.
 */

defined( 'ABSPATH' ) || exit;

/** Page du formulaire de contact (celle qui contient le bloc « Formulaire de contact »). */
function schiesser_page_contact_url() {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}
	$page = get_page_by_path( 'contact' );
	if ( ! $page || false === strpos( $page->post_content, 'wp:schiesser/formulaire' ) ) {
		$trouve = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 1, 's' => 'wp:schiesser/formulaire', 'search_columns' => array( 'post_content' ) ) );
		$page   = $trouve ? $trouve[0] : $page;
	}
	return $url = $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * Boutons de commande d'un produit.
 *
 * @param array  $p     Données du produit (nom, id, prix, unite, url, image).
 * @param string $forme 'fiche' (page du produit, fiche rapide) ou 'barre' (barre du téléphone).
 */
function schiesser_boutons_commande( $p, $forme = 'fiche' ) {
	$tel     = schiesser_reglage( 'telephone' );
	$etat    = function_exists( 'schiesser_barre_mobile_etat' ) ? schiesser_barre_mobile_etat() : array( true, '' );
	$message = add_query_arg( 'produit', rawurlencode( $p['nom'] ?? '' ), schiesser_page_contact_url() ) . '#ecrire';
	$court   = 'barre' === $forme;
	$html    = '<div class="cmd cmd--' . esc_attr( $forme ) . ' js-cmd' . ( $tel ? '' : ' cmd--sans-tel' ) . '" data-produit="' . esc_attr( $p['nom'] ?? '' ) . '">';
	if ( $tel ) {
		$html .= '<div class="cmd-ouvert js-cmd-ouvert"' . ( $etat[0] ? '' : ' hidden' ) . '><a class="btn btn-kir" href="' . esc_url( schiesser_lien_tel() ) . '"><span>' . ( $court ? 'Appeler' : 'Appeler pour commander' ) . '</span> <span class="a" aria-hidden="true">→</span></a>'
			. ( $court ? '' : '<p class="cmd-aide">Nous sommes ouverts : ' . esc_html( schiesser_reglage( 'telephone' ) ) . '</p>' ) . '</div>';
	}
	$html .= '<div class="cmd-ferme js-cmd-ferme"' . ( $tel && $etat[0] ? ' hidden' : '' ) . '>'
		. ( $tel && ! $court ? '<p class="cmd-etat"><span class="cmd-led" aria-hidden="true"></span><span class="js-cmd-prochain">' . esc_html( $etat[1] ) . '</span></p>' : '' )
		. '<a class="btn btn-kir js-cmd-message" href="' . esc_url( $message ) . '"><span>Laisser un message</span> <span class="a" aria-hidden="true">→</span></a>'
		. ( $tel && ! $court ? '<a class="cmd-tel" href="' . esc_url( schiesser_lien_tel() ) . '">' . esc_html( $tel ) . '</a>' : '' )
		. '</div>';
	if ( ! empty( $p['nom'] ) || 'fiche' === $forme ) {
		$html .= schiesser_bouton_selection( $p, $court );
	}
	return $html . '</div>';
}

/** Bouton « cœur » : ajoute le produit à « Ma sélection ». */
function schiesser_bouton_selection( $p, $court = false ) {
	return '<button type="button" class="cmd-sel js-sel" aria-pressed="false"'
		. ' data-id="' . esc_attr( (string) ( $p['id'] ?? '' ) ) . '" data-nom="' . esc_attr( $p['nom'] ?? '' ) . '" data-prix="' . esc_attr( $p['prix'] ?? '' ) . '"'
		. ' data-unite="' . esc_attr( $p['unite'] ?? '' ) . '" data-url="' . esc_attr( $p['url'] ?? '' ) . '">'
		. '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>'
		. '<span class="js-sel-texte">' . ( $court ? 'Garder' : 'Ajouter à ma sélection' ) . '</span></button>';
}

/** Boutons de partage. $url et $titre peuvent être remplacés par le script (fiche rapide). */
function schiesser_partage( $url, $titre, $classe = '' ) {
	$texte = $titre . ' · ' . $url;
	$icone = function ( $d ) {
		return '<svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true" focusable="false"><path d="' . $d . '" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	};
	return '<div class="partage js-partage ' . esc_attr( $classe ) . '" data-url="' . esc_url( $url ) . '" data-titre="' . esc_attr( $titre ) . '">'
		. '<span class="partage-k">Envoyer à un ami</span>'
		. '<button type="button" class="partage-b js-partage-natif" hidden>' . $icone( 'M12 3v12 M7 8l5-5 5 5 M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6' ) . '<span>Partager</span></button>'
		. '<a class="partage-b js-partage-wa" href="' . esc_url( 'https://wa.me/?text=' . rawurlencode( $texte ) ) . '" target="_blank" rel="noopener">' . $icone( 'M4 20l1.3-3.9A8 8 0 1 1 8 19z M9 9.5c0 3 2.5 5.5 5.5 5.5l1.2-1.4-2-1-1 .8a4 4 0 0 1-2.1-2.1l.8-1-1-2z' ) . '<span>WhatsApp</span></a>'
		. '<a class="partage-b js-partage-mail" href="' . esc_url( 'mailto:?subject=' . rawurlencode( $titre ) . '&body=' . rawurlencode( $texte ) ) . '">' . $icone( 'M3 6h18v12H3z M3 7l9 6 9-6' ) . '<span>E-mail</span></a>'
		. '<button type="button" class="partage-b js-partage-copier">' . $icone( 'M9 9h10v11H9z M5 15V4h10' ) . '<span class="js-partage-copier-texte">Copier le lien</span></button>'
		. '</div>';
}

/* Données du site pour les scripts : téléphone et page de contact (« Ma sélection », boutons de commande). */
add_action( 'wp_enqueue_scripts', function () {
	wp_add_inline_script( 'schiesser-site', 'window.SCHIESSER=window.SCHIESSER||{};SCHIESSER.tel=' . wp_json_encode( schiesser_reglage( 'telephone' ) ? schiesser_lien_tel() : '' )
		. ';SCHIESSER.telAffiche=' . wp_json_encode( (string) schiesser_reglage( 'telephone' ) )
		. ';SCHIESSER.contact=' . wp_json_encode( schiesser_page_contact_url() ) . ';', 'before' );
}, 20 );

/* ------------------------------------------------------------------ */
/* Navigation plus fluide                                              */
/* ------------------------------------------------------------------ */

/*
 * Pages suivantes préparées à l'avance : quand le visiteur survole ou touche un lien,
 * le navigateur commence à charger la page (règles de préchargement, navigateurs récents).
 */
add_action( 'wp_footer', function () {
	if ( is_user_logged_in() || version_compare( get_bloginfo( 'version' ), '6.8', '>=' ) ) {
		return; // WordPress 6.8 le fait lui-même ; les comptes connectés naviguent sans préchargement
	}
	$regles = array(
		'prefetch' => array( array(
			'source'    => 'document',
			'where'     => array( 'and' => array(
				array( 'href_matches' => '/*' ),
				array( 'not' => array( 'href_matches' => array( '/wp-*', '/*\\?*', '/*.pdf' ) ) ),
				array( 'not' => array( 'selector_matches' => '[target=_blank],[download],.no-prefetch' ) ),
			) ),
			'eagerness' => 'moderate',
		) ),
	);
	echo '<script type="speculationrules">' . wp_json_encode( $regles, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
} );

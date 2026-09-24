<?php
/**
 * Codes courts : les informations des « Réglages de la maison » insérables
 * n'importe où (bloc « Code court », bloc « HTML personnalisé », texte…).
 * Elles restent toujours à jour : on les modifie une fois, dans les réglages.
 *
 * [schiesser_horaires]   tableau des horaires, jour courant mis en avant
 * [schiesser_statut]     « Ouvert · 07:30–18:30 » en direct
 * [schiesser_horaires_phrase] « du lundi au vendredi de 7 h 30 à 18 h 30, … » (pour un texte)
 * [schiesser_adresse]    adresse sur deux lignes
 * [schiesser_telephone]  numéro cliquable
 * [schiesser_email]      adresse e-mail cliquable
 * [schiesser_itineraire] bouton « Itinéraire » vers Google Maps
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	add_shortcode( 'schiesser_horaires', 'schiesser_sc_horaires' );
	add_shortcode( 'schiesser_statut', 'schiesser_sc_statut' );
	add_shortcode( 'schiesser_horaires_phrase', 'schiesser_sc_horaires_phrase' );
	add_shortcode( 'schiesser_adresse', 'schiesser_sc_adresse' );
	add_shortcode( 'schiesser_telephone', 'schiesser_sc_telephone' );
	add_shortcode( 'schiesser_email', 'schiesser_sc_email' );
	add_shortcode( 'schiesser_itineraire', 'schiesser_sc_itineraire' );
} );

function schiesser_sc_horaires() {
	$aujourdhui = (int) wp_date( 'w' );
	$tous       = schiesser_reglage( 'horaires' );
	$html       = '<dl class="s-horaires-liste">';
	foreach ( schiesser_jours() as $n => $jour ) {
		$h      = $tous[ $n ];
		$classe = $n === $aujourdhui ? ' is-aujourdhui' : '';
		$texte  = ! empty( $h['ferme'] )
			? 'Fermé'
			: '<time>' . esc_html( $h['ouverture'] ) . '</time> – <time>' . esc_html( $h['fermeture'] ) . '</time>';
		$html  .= '<div class="hrow' . $classe . '"><dt class="d"' . ( $classe ? ' data-today="Aujourd\'hui"' : '' ) . '>' . esc_html( $jour ) . '</dt><dd class="h">' . $texte . '</dd></div>';
	}
	$html .= '</dl>' . schiesser_html_jours_particuliers();
	if ( schiesser_reglage( 'horaires_note' ) ) {
		$html .= '<p class="s-horaires-note">' . esc_html( schiesser_reglage( 'horaires_note' ) ) . '</p>';
	}
	return $html;
}

function schiesser_sc_statut() {
	return '<span class="s-statut" data-nosnippet><span class="led js-led"></span><span class="js-statut">Aujourd’hui</span> · <span class="js-heures">' . esc_html( schiesser_horaires_du_jour() ) . '</span></span>';
}

function schiesser_sc_horaires_phrase() {
	return esc_html( schiesser_horaires_phrase() );
}

function schiesser_sc_adresse() {
	$l = schiesser_adresse_lignes();
	return '<span class="s-adresse">' . esc_html( $l[0] ) . '<br>' . esc_html( $l[1] ) . '</span>';
}

function schiesser_sc_telephone() {
	$tel = schiesser_reglage( 'telephone' );
	return $tel ? '<a class="s-lien-contact" href="' . esc_url( schiesser_lien_tel() ) . '">' . esc_html( $tel ) . '</a>' : '';
}

function schiesser_sc_email() {
	$email = schiesser_reglage( 'email' );
	return $email ? '<a class="s-lien-contact" href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>' : '';
}

function schiesser_sc_itineraire( $atts ) {
	$a   = shortcode_atts( array( 'texte' => 'Itinéraire' ), $atts, 'schiesser_itineraire' );
	$url = schiesser_reglage( 'lien_maps' );
	return $url ? '<a class="btn btn-kir" href="' . esc_url( $url ) . '" target="_blank" rel="noopener"><span>' . esc_html( $a['texte'] ) . '</span> <span class="a" aria-hidden="true">↗</span></a>' : '';
}

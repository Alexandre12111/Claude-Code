<?php
/**
 * Coordonnées de l'agence : Apparence → Personnaliser → Aurea Media.
 *
 * Une seule saisie, reprise dans l'en-tête, le pied de page, les boutons d'appel des pages importées,
 * le formulaire de contact et les données structurées lues par Google.
 */

defined( 'ABSPATH' ) || exit;

/** Champs et valeurs par défaut. */
function aurea_champs_coordonnees() {
	return array(
		'tel'          => array( 'Téléphone (format international, sans espace)', '+33651287761' ),
		'tel_affiche'  => array( 'Téléphone affiché', '06 51 28 77 61' ),
		'email'        => array( 'E-mail', 'alexandre.coury@aurea-media.fr' ),
		'email_devis'  => array( 'E-mail qui reçoit les demandes de devis (vide : le même)', '' ),
		'rue'          => array( 'Adresse', '60 rue François Ier' ),
		'cp'           => array( 'Code postal', '75008' ),
		'ville'        => array( 'Ville', 'Paris' ),
		'siren'        => array( 'SIREN', '992 693 473' ),
		'fondation'    => array( 'Année de création', '2021' ),
		'linkedin'     => array( 'Page LinkedIn de l’agence', 'https://www.linkedin.com/company/aureamedia/' ),
		'linkedin_moi' => array( 'Profil LinkedIn du fondateur', 'https://www.linkedin.com/in/alexandre-coury/' ),
		'google'       => array( 'Lien vers la fiche Google Business Profile', 'https://www.google.com/search?kgmid=/g/11mcqnsjrx' ),
		'fondateur'    => array( 'Nom du fondateur', 'Alexandre Coury' ),
	);
}

/** Coordonnées enregistrées (valeurs par défaut si rien n'est saisi). */
function aurea_coordonnees() {
	$c = array();
	foreach ( aurea_champs_coordonnees() as $cle => $champ ) {
		$valeur    = get_theme_mod( 'aurea_' . $cle, '' );
		$c[ $cle ] = '' !== $valeur ? $valeur : $champ[1];
	}
	$c['email_devis'] = $c['email_devis'] ?: $c['email'];
	// +33651287761 → +33 6 51 28 77 61
	$c['tel_inter'] = preg_match( '/^\+33(\d)(\d{2})(\d{2})(\d{2})(\d{2})$/', $c['tel'], $m ) ? "+33 $m[1] $m[2] $m[3] $m[4] $m[5]" : $c['tel'];
	return $c;
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'aurea', array(
		'title'       => 'Aurea Media',
		'priority'    => 30,
		'description' => 'Coordonnées reprises dans l’en-tête, le pied de page, le formulaire de devis et les données lues par Google. Les pages déjà importées gardent le numéro du moment de l’import (modifiable dans l’éditeur).',
	) );
	foreach ( aurea_champs_coordonnees() as $cle => $champ ) {
		$wp_customize->add_setting( 'aurea_' . $cle, array(
			'default'           => '',
			'sanitize_callback' => in_array( $cle, array( 'linkedin', 'linkedin_moi', 'google' ), true ) ? 'esc_url_raw' : ( 0 === strpos( $cle, 'email' ) ? 'sanitize_email' : 'sanitize_text_field' ),
		) );
		$wp_customize->add_control( 'aurea_' . $cle, array(
			'label'       => $champ[0],
			'section'     => 'aurea',
			'type'        => 0 === strpos( $cle, 'email' ) ? 'email' : ( in_array( $cle, array( 'linkedin', 'linkedin_moi', 'google' ), true ) ? 'url' : 'text' ),
			'input_attrs' => array( 'placeholder' => $champ[1] ),
		) );
	}
} );

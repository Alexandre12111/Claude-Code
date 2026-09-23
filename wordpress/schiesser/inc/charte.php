<?php
/**
 * Charte graphique : couleurs et typographie modifiables depuis l'administration.
 *
 * Réservé aux administrateurs (onglet « Charte graphique » des Réglages maison).
 * Les valeurs deviennent des variables CSS (--vert, --serif…) utilisées partout,
 * sur le site comme dans l'éditeur, et la palette de l'éditeur suit la charte :
 * le client ne peut choisir que les couleurs de la maison.
 *
 * Même option : réglages « Avancé » (habillage de l'administration,
 * commentaires, code personnalisé).
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_CHARTE = 'schiesser_charte';

/**
 * Couleurs de base : clé CSS => [ libellé, valeur d'origine, rôle, slug de palette ].
 * Les autres nuances (fonds alternés, filets, survols…) sont calculées par la feuille de style.
 */
function schiesser_couleurs_charte() {
	return array(
		'paper'      => array( 'Fond clair', '#F1EADE', 'Fond principal des pages.', 'papier' ),
		'paper-deep' => array( 'Fond sable', '#E4D8C3', 'Sections alternées, passe-partout des photos.', 'sable' ),
		'ink'        => array( 'Titres', '#2A1C12', 'Grands titres et noms de produits.', 'encre' ),
		'text'       => array( 'Texte courant', '#3A2818', 'Paragraphes, menu, pied de page.', 'texte' ),
		'ink-soft'   => array( 'Texte secondaire', '#6B5A47', 'Notes, légendes, descriptions.', 'texte-secondaire' ),
		'vert'       => array( 'Vert maison', '#23503B', 'Boutons principaux, liens, numéros, badges.', 'vert' ),
		'mint'       => array( 'Menthe', '#8CC5A6', 'Accents sur les fonds sombres.', 'menthe' ),
		'dark'       => array( 'Chocolat', '#271B12', 'Sections sombres, bandeau du haut.', 'chocolat' ),
	);
}

/**
 * Polices proposées. Chaque famille a été vérifiée sur Google Fonts.
 * Bodoni Moda et Inter (charte d'origine) sont hébergées dans le thème.
 */
function schiesser_polices() {
	return array(
		'titres' => array(
			'bodoni-moda'        => array( 'Bodoni Moda', 'Bodoni+Moda:ital,opsz,wght@0,6..96,400..600;1,6..96,400..600', "Didot,'Times New Roman',serif", 'Didone à fort contraste (charte d\'origine)', true ),
			'playfair-display'   => array( 'Playfair Display', 'Playfair+Display:ital,wght@0,400..600;1,400..600', "Georgia,'Times New Roman',serif", 'Élégante et lisible, très polyvalente', false ),
			'cormorant-garamond' => array( 'Cormorant Garamond', 'Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500', "Garamond,Georgia,serif", 'Classique et raffinée, très fine', false ),
			'fraunces'           => array( 'Fraunces', 'Fraunces:ital,opsz,wght@0,9..144,400..600;1,9..144,400..600', "Georgia,serif", 'Chaleureuse, un peu gourmande', false ),
			'eb-garamond'        => array( 'EB Garamond', 'EB+Garamond:ital,wght@0,400..600;1,400..600', "Garamond,Georgia,serif", 'Garamond traditionnel, sobre', false ),
			'dm-serif-display'   => array( 'DM Serif Display', 'DM+Serif+Display:ital@0;1', "Georgia,serif", 'Affichage contrasté, plus compact', false ),
		),
		'texte'  => array(
			'inter'         => array( 'Inter', 'Inter:wght@400..600', "system-ui,-apple-system,'Segoe UI',sans-serif", 'Neutre et très lisible (charte d\'origine)', true ),
			'dm-sans'       => array( 'DM Sans', 'DM+Sans:ital,opsz,wght@0,9..40,400..600;1,9..40,400..600', "system-ui,sans-serif", 'Géométrique douce', false ),
			'work-sans'     => array( 'Work Sans', 'Work+Sans:wght@400..600', "system-ui,sans-serif", 'Légèrement plus large', false ),
			'manrope'       => array( 'Manrope', 'Manrope:wght@400..600', "system-ui,sans-serif", 'Moderne et arrondie', false ),
			'source-sans-3' => array( 'Source Sans 3', 'Source+Sans+3:wght@400..600', "system-ui,sans-serif", 'Humaniste, confortable en lecture', false ),
			'jost'          => array( 'Jost', 'Jost:wght@400..600', "system-ui,sans-serif", 'Géométrique, esprit années 1920', false ),
		),
	);
}

function schiesser_charte_defaut() {
	$couleurs = array();
	foreach ( schiesser_couleurs_charte() as $cle => $c ) {
		$couleurs[ $cle ] = $c[1];
	}
	return array(
		'couleurs'         => $couleurs,
		'titres'           => 'bodoni-moda',
		'texte'            => 'inter',
		'echelle'          => '1',
		'habillage_admin'  => 1,
		'commentaires_off' => 1,
		'code_head'        => '',
		'code_footer'      => '',
	);
}

/** Charte courante, complétée par les valeurs d'origine. */
function schiesser_charte() {
	$defaut = schiesser_charte_defaut();
	$c      = wp_parse_args( (array) get_option( SCHIESSER_CHARTE, array() ), $defaut );
	$c['couleurs'] = wp_parse_args( (array) $c['couleurs'], $defaut['couleurs'] );
	return $c;
}

/* ------------------------------------------------------------------ */
/* Contraste (pour prévenir l'administrateur d'un choix peu lisible)    */
/* ------------------------------------------------------------------ */

function schiesser_luminance( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$l = array();
	foreach ( array( 0, 2, 4 ) as $i ) {
		$c   = hexdec( substr( $hex, $i, 2 ) ) / 255;
		$l[] = $c <= 0.03928 ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
	}
	return 0.2126 * $l[0] + 0.7152 * $l[1] + 0.0722 * $l[2];
}

function schiesser_contraste( $a, $b ) {
	$la = schiesser_luminance( $a );
	$lb = schiesser_luminance( $b );
	return ( max( $la, $lb ) + 0.05 ) / ( min( $la, $lb ) + 0.05 );
}

/**
 * Paires de couleurs à vérifier : [ couleur texte, couleur fond, libellé, seuil ].
 * Seuil 4,5 : texte courant (norme WCAG AA). 3 : grands titres.
 */
function schiesser_verifier_contrastes( $couleurs ) {
	$paires = array(
		array( $couleurs['text'], $couleurs['paper'], 'Texte courant sur le fond clair', 4.5 ),
		array( $couleurs['ink-soft'], $couleurs['paper'], 'Texte secondaire sur le fond clair', 4.5 ),
		array( $couleurs['ink'], $couleurs['paper-deep'], 'Titres sur le fond sable', 3 ),
		array( '#FFFFFF', $couleurs['vert'], 'Texte blanc des boutons verts', 4.5 ),
		array( $couleurs['vert'], $couleurs['paper'], 'Liens verts sur le fond clair', 4.5 ),
		array( $couleurs['paper'], $couleurs['dark'], 'Texte clair sur les sections chocolat', 4.5 ),
		array( $couleurs['mint'], $couleurs['dark'], 'Accents menthe sur les sections chocolat', 3 ),
	);
	$alertes = array();
	foreach ( $paires as $p ) {
		$ratio = schiesser_contraste( $p[0], $p[1] );
		if ( $ratio < $p[3] ) {
			$alertes[] = sprintf( '%s : contraste %s (minimum conseillé %s).', $p[2], number_format_i18n( $ratio, 1 ), number_format_i18n( $p[3], 1 ) );
		}
	}
	return $alertes;
}

/* ------------------------------------------------------------------ */
/* Enregistrement et nettoyage                                         */
/* ------------------------------------------------------------------ */

add_action( 'admin_init', function () {
	register_setting( 'schiesser_reglages_groupe', SCHIESSER_CHARTE, array(
		'type'              => 'array',
		'sanitize_callback' => 'schiesser_nettoyer_charte',
		'default'           => schiesser_charte_defaut(),
	) );
} );

function schiesser_nettoyer_charte( $entree ) {
	$actuel = schiesser_charte();
	// Seuls les administrateurs modifient la charte ; pour les autres, rien ne change.
	if ( ! current_user_can( 'manage_options' ) || ! is_array( $entree ) ) {
		return $actuel;
	}

	$defaut = schiesser_charte_defaut();
	$propre = $actuel;

	if ( ! empty( $entree['reinitialiser'] ) ) {
		$propre['couleurs'] = $defaut['couleurs'];
		$propre['titres']   = $defaut['titres'];
		$propre['texte']    = $defaut['texte'];
		$propre['echelle']  = $defaut['echelle'];
		add_settings_error( SCHIESSER_CHARTE, 'charte-reinit', 'La charte d\'origine a été rétablie.', 'updated' );
		return $propre;
	}

	foreach ( schiesser_couleurs_charte() as $cle => $c ) {
		$v                          = sanitize_hex_color( $entree['couleurs'][ $cle ] ?? '' );
		$propre['couleurs'][ $cle ] = $v ? strtoupper( $v ) : $c[1];
	}

	$polices          = schiesser_polices();
	$propre['titres'] = isset( $polices['titres'][ $entree['titres'] ?? '' ] ) ? $entree['titres'] : $defaut['titres'];
	$propre['texte']  = isset( $polices['texte'][ $entree['texte'] ?? '' ] ) ? $entree['texte'] : $defaut['texte'];
	$propre['echelle'] = in_array( $entree['echelle'] ?? '1', array( '0.9', '1', '1.1' ), true ) ? $entree['echelle'] : '1';

	$propre['habillage_admin']  = empty( $entree['habillage_admin'] ) ? 0 : 1;
	$propre['commentaires_off'] = empty( $entree['commentaires_off'] ) ? 0 : 1;

	// Le code personnalisé n'est accepté que des comptes autorisés à publier du HTML brut.
	if ( current_user_can( 'unfiltered_html' ) ) {
		$propre['code_head']   = (string) ( $entree['code_head'] ?? '' );
		$propre['code_footer'] = (string) ( $entree['code_footer'] ?? '' );
	}

	foreach ( schiesser_verifier_contrastes( $propre['couleurs'] ) as $i => $alerte ) {
		add_settings_error( SCHIESSER_CHARTE, 'contraste-' . $i, 'Lisibilité : ' . $alerte, 'warning' );
	}
	return $propre;
}

/* ------------------------------------------------------------------ */
/* Application sur le site et dans l'éditeur                           */
/* ------------------------------------------------------------------ */

/** Adresse Google Fonts des polices choisies (vide si les polices hébergées suffisent). */
function schiesser_url_google_fonts( $charte = null ) {
	$charte  = $charte ?: schiesser_charte();
	$polices = schiesser_polices();
	$familles = array();
	foreach ( array( 'titres', 'texte' ) as $role ) {
		$p = $polices[ $role ][ $charte[ $role ] ] ?? null;
		if ( $p && ! $p[4] ) {
			$familles[] = 'family=' . $p[1];
		}
	}
	return $familles ? 'https://fonts.googleapis.com/css2?' . implode( '&', $familles ) . '&display=swap' : '';
}

/** Variables CSS correspondant aux réglages (seules les différences avec l'origine sont écrites). */
function schiesser_css_charte() {
	$c       = schiesser_charte();
	$defaut  = schiesser_charte_defaut();
	$polices = schiesser_polices();
	$vars    = array();

	foreach ( $c['couleurs'] as $cle => $valeur ) {
		if ( strtoupper( $valeur ) !== strtoupper( $defaut['couleurs'][ $cle ] ) ) {
			$vars[] = '--' . $cle . ':' . $valeur;
		}
	}
	if ( $c['titres'] !== $defaut['titres'] ) {
		$p      = $polices['titres'][ $c['titres'] ];
		$vars[] = "--serif:'" . $p[0] . "'," . $p[2];
	}
	if ( $c['texte'] !== $defaut['texte'] ) {
		$p      = $polices['texte'][ $c['texte'] ];
		$vars[] = "--sans:'" . $p[0] . "'," . $p[2];
	}
	if ( '1' !== (string) $c['echelle'] ) {
		$vars[] = '--echelle-titres:' . $c['echelle'];
	}
	return $vars ? ':root{' . implode( ';', $vars ) . '}' : '';
}

/* Les styles sont enregistrés dans setup.php ; on y ajoute la charte (site + éditeur). */
add_action( 'init', function () {
	$css = schiesser_css_charte();
	if ( $css ) {
		wp_add_inline_style( 'schiesser-site', $css );
	}
}, 20 );

/* Palette de l'éditeur = couleurs de la charte (le client ne peut pas en inventer d'autres). */
add_filter( 'wp_theme_json_data_theme', function ( $theme_json ) {
	$c       = schiesser_charte()['couleurs'];
	$palette = array();
	foreach ( schiesser_couleurs_charte() as $cle => $def ) {
		$palette[] = array( 'slug' => $def[3], 'name' => $def[0], 'color' => $c[ $cle ] );
	}
	$palette[] = array( 'slug' => 'blanc', 'name' => 'Blanc', 'color' => '#FFFFFF' );

	return $theme_json->update_with( array(
		'version'  => 2,
		'settings' => array( 'color' => array( 'palette' => $palette ) ),
	) );
} );

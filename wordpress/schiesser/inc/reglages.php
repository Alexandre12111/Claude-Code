<?php
/**
 * « Réglages de la maison » : données.
 *
 * Les informations saisies ici une seule fois alimentent tout le site :
 * bandeau du haut (ouvert/fermé), pied de page, codes courts [schiesser_…],
 * données structurées pour Google (adresse, horaires, position).
 * L'écran d'administration est dans inc/page-reglages.php.
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_OPTION = 'schiesser_reglages';

/** Jours dans l'ordre d'affichage (clé = numéro JavaScript, 0 = dimanche). */
function schiesser_jours() {
	return array(
		1 => 'Lundi',
		2 => 'Mardi',
		3 => 'Mercredi',
		4 => 'Jeudi',
		5 => 'Vendredi',
		6 => 'Samedi',
		0 => 'Dimanche',
	);
}

/** Pays proposés pour l'adresse (code ISO => nom). */
function schiesser_pays() {
	return array(
		'CH' => 'Suisse',
		'FR' => 'France',
		'DE' => 'Allemagne',
		'AT' => 'Autriche',
		'BE' => 'Belgique',
		'LU' => 'Luxembourg',
		'IT' => 'Italie',
	);
}

/** Types d'établissement pour Google (schema.org). */
function schiesser_types_etablissement() {
	return array(
		'Bakery,CafeOrCoffeeShop' => 'Confiserie avec salon de thé',
		'Bakery'                  => 'Confiserie, pâtisserie, boulangerie',
		'CafeOrCoffeeShop'        => 'Café, salon de thé',
		'FoodEstablishment'       => 'Commerce alimentaire',
		'Store'                   => 'Magasin',
	);
}

/** Valeurs par défaut, reprises de la maquette. */
function schiesser_reglages_defaut() {
	$horaires = array();
	foreach ( schiesser_jours() as $n => $nom ) {
		$horaires[ $n ] = array( 'ouverture' => '07:30', 'fermeture' => '18:30', 'ferme' => 0 );
	}
	$horaires[6] = array( 'ouverture' => '08:00', 'fermeture' => '18:00', 'ferme' => 0 );
	$horaires[0] = array( 'ouverture' => '09:00', 'fermeture' => '17:00', 'ferme' => 0 );

	return array(
		// Coordonnées
		'telephone'          => '+41 61 261 60 77',
		'email'              => 'info@confiserie-schiesser.ch',
		'rue'                => 'Marktplatz 19',
		'code_postal'        => '4051',
		'ville'              => 'Basel',
		'region'             => 'Basel-Stadt',
		'pays'               => 'CH',
		'mention'            => 'Confiserie & Tea Room · Marktplatz Basel · seit 1870',
		'lien_maps'          => 'https://www.google.com/maps/search/?api=1&query=Confiserie+Schiesser%2C+Marktplatz+19%2C+4051+Basel',
		'carte_google'       => '', // adresse d'intégration Google Maps (facultatif)
		'carte_au_clic'      => 0,  // carte Google affichée seulement après un clic
		// Horaires
		'horaires'           => $horaires,
		'horaires_note'      => 'An Feiertagen können die Öffnungszeiten abweichen. Im Zweifel genügt ein Anruf.',
		'feries'             => schiesser_feries_defaut(), // jours fériés de Bâle (inc/horaires.php)
		'exceptions'         => array(),                  // fermetures et horaires exceptionnels datés
		'annonces'           => array(),                  // bandeau d'annonce (inc/annonces.php)
		'annonce_feries'     => 1,
		'barre_mobile'       => 1,
		'allergenes_note'    => 'In unserer Backstube werden auch Gluten, Nüsse, Milch und Eier verarbeitet: Spuren sind möglich. Unser Team gibt Ihnen gerne Auskunft.',
		// Accueil et pied de page
		'vitrine'            => array( 'Läckerli', 'Truffes', 'Fruchtwähe' ),
		'presentation'       => 'Die Confiserie Schiesser wurde 1870 am Basler Marktplatz gegründet. Läckerli, Pralinen, Torten und Gebäck entstehen in der eigenen Backstube; im ersten Stock liegt der Tea Room, das älteste Kaffeehaus der Schweiz.',
		'instagram'          => '',
		'carte_pdf'          => '',
		'carte_pdf_fr'       => '',
		'carte_pdf_en'       => '',
		'facebook'           => '',
		// Établissement (SEO)
		'nom_etablissement'  => 'Confiserie Schiesser',
		'type_etablissement' => 'Bakery,CafeOrCoffeeShop',
		'gamme_prix'         => 'CHF 2–30',
		'annee_fondation'    => '1870',
		'latitude'           => '47.5584',
		'longitude'          => '7.5878',
		'page_boutique'      => 0,
		'raison_sociale'     => '',
		'numero_ide'         => '',
		'profils'            => '',
		// Avis Google (inc/avis.php)
		'avis_cle'           => '', // clé API Google (Places API)
		'avis_lieu'          => '', // identifiant du lieu (Place ID)
		'avis_note'          => '', // note moyenne saisie à la main (sans clé API)
		'avis_nombre'        => '', // nombre d'avis saisi à la main
	);
}

/**
 * Lit un réglage (avec repli sur la valeur par défaut).
 */
function schiesser_reglage( $cle ) {
	$valeurs = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), schiesser_reglages_defaut() );
	$v       = isset( $valeurs[ $cle ] ) ? $valeurs[ $cle ] : '';
	// Site en français ou en anglais (Polylang) : textes de présentation traduits (inc/i18n.php).
	if ( ! is_admin() && did_action( 'wp' ) && function_exists( 'schiesser_reglage_traduit' ) && in_array( $cle, schiesser_reglages_traduisibles(), true ) ) {
		$v = is_array( $v ) ? array_map( 'schiesser_reglage_traduit', $v ) : schiesser_reglage_traduit( $v );
	}
	return $v;
}

/** Adresse sur deux lignes : [ « Marktplatz », « 4001 Basel, Suisse » ]. */
function schiesser_adresse_lignes() {
	$noms = array(
		'de' => array( 'CH' => 'Schweiz', 'FR' => 'Frankreich', 'DE' => 'Deutschland', 'AT' => 'Österreich', 'BE' => 'Belgien', 'LU' => 'Luxemburg', 'IT' => 'Italien' ),
		'en' => array( 'CH' => 'Switzerland', 'FR' => 'France', 'DE' => 'Germany', 'AT' => 'Austria', 'BE' => 'Belgium', 'LU' => 'Luxembourg', 'IT' => 'Italy' ),
	);
	$code = schiesser_reglage( 'pays' );
	$l    = is_admin() ? 'fr' : ( function_exists( 'schiesser_langue' ) ? schiesser_langue() : 'de' );
	$pays = $noms[ $l ][ $code ] ?? ( schiesser_pays()[ $code ] ?? '' ); // le site public affiche le pays dans sa langue
	$l2   = trim( schiesser_reglage( 'code_postal' ) . ' ' . schiesser_reglage( 'ville' ) );
	return array( schiesser_reglage( 'rue' ), $pays ? $l2 . ', ' . $pays : $l2 );
}

/** Lien tel: propre à partir du numéro affiché. */
function schiesser_lien_tel() {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', schiesser_reglage( 'telephone' ) );
}

/** Horaires au format attendu par le script du site : { 1: [7.5, 18.5], … }. */
function schiesser_horaires_js() {
	$sortie = array();
	foreach ( (array) schiesser_reglage( 'horaires' ) as $jour => $h ) {
		if ( ! empty( $h['ferme'] ) ) {
			$sortie[ $jour ] = null;
			continue;
		}
		$sortie[ $jour ] = array( schiesser_heure_decimale( $h['ouverture'] ), schiesser_heure_decimale( $h['fermeture'] ) );
	}
	return $sortie;
}

function schiesser_heure_decimale( $hhmm ) {
	$p = explode( ':', (string) $hhmm );
	return (int) $p[0] + ( isset( $p[1] ) ? (int) $p[1] / 60 : 0 );
}

/** Jours de la semaine pour le site, dans la langue de la page (l'administration garde schiesser_jours()). */
function schiesser_jours_site( $langue = null ) {
	$langue = $langue ?: ( function_exists( 'schiesser_langue' ) ? schiesser_langue() : 'de' );
	$noms   = array(
		'de' => array( 1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag', 5 => 'Freitag', 6 => 'Samstag', 0 => 'Sonntag' ),
		'fr' => array( 1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 0 => 'dimanche' ),
		'en' => array( 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday' ),
	);
	return $noms[ $langue ] ?? $noms['de'];
}

/** Horaires du jour dans le fuseau du site, ex. « 7.30–18.30 Uhr », ou « Heute geschlossen ». */
function schiesser_horaires_du_jour() {
	$p = schiesser_horaires_date( current_datetime()->format( 'Y-m-d' ) ); // jours fériés compris
	return $p ? schiesser_uhr( schiesser_heure_hhmm( $p[0] ), false ) . '–' . schiesser_uhr( schiesser_heure_hhmm( $p[1] ) ) : schiesser_t( 'Heute geschlossen' );
}

/** 7.5 → « 07:30 » */
function schiesser_heure_hhmm( $x ) {
	$m = (int) round( fmod( $x, 1 ) * 60 );
	return sprintf( '%02d:%02d', floor( $x ) + intdiv( $m, 60 ), $m % 60 );
}

/**
 * Heure à la française : « 07:30 » → « 7 h 30 », « 08:00 » → « 8 h » (administration).
 * Sur le site, l'heure s'écrit à la suisse : schiesser_uhr() (« 7.30 Uhr »).
 */
function schiesser_heure_fr( $hhmm ) {
	$p = explode( ':', (string) $hhmm );
	$h = (int) $p[0];
	$m = isset( $p[1] ) ? (int) $p[1] : 0;
	return $m ? sprintf( "%d\u{00A0}h\u{00A0}%02d", $h, $m ) : sprintf( "%d\u{00A0}h", $h );
}

/**
 * Horaires en une phrase, dans la langue de la page. Ex. « Montag bis Freitag von 7.30 bis 18.30 Uhr,
 * Samstag von 8 bis 18 Uhr und Sonntag von 9 bis 17 Uhr » ; « du lundi au vendredi de 7 h 30 à 18 h 30… ».
 */
function schiesser_horaires_phrase() {
	$l       = function_exists( 'schiesser_langue' ) ? schiesser_langue() : 'de';
	$noms    = schiesser_jours_site( $l );
	$tous    = schiesser_reglage( 'horaires' );
	$groupes = array();
	foreach ( schiesser_jours() as $n => $nom ) {
		$h = $tous[ $n ];
		if ( ! empty( $h['ferme'] ) ) {
			$texte = schiesser_t( 'geschlossen' );
		} elseif ( 'fr' === $l ) {
			$texte = 'de ' . schiesser_uhr( $h['ouverture'] ) . ' à ' . schiesser_uhr( $h['fermeture'] );
		} elseif ( 'en' === $l ) {
			$texte = schiesser_uhr( $h['ouverture'] ) . '–' . schiesser_uhr( $h['fermeture'] );
		} else {
			$texte = 'von ' . schiesser_uhr( $h['ouverture'], false ) . ' bis ' . schiesser_uhr( $h['fermeture'] );
		}
		$der = count( $groupes ) - 1;
		if ( $der >= 0 && $groupes[ $der ]['texte'] === $texte ) {
			$groupes[ $der ]['fin'] = $noms[ $n ];
		} else {
			$groupes[] = array( 'debut' => $noms[ $n ], 'fin' => null, 'texte' => $texte );
		}
	}
	$parties = array();
	foreach ( $groupes as $g ) {
		if ( 'fr' === $l ) {
			$jours = $g['fin'] ? 'du ' . $g['debut'] . ' au ' . $g['fin'] : 'le ' . $g['debut'];
		} elseif ( 'en' === $l ) {
			$jours = $g['fin'] ? $g['debut'] . ' to ' . $g['fin'] : $g['debut'];
		} else {
			$jours = $g['fin'] ? $g['debut'] . ' bis ' . $g['fin'] : $g['debut'];
		}
		$parties[] = $jours . ' ' . $g['texte'];
	}
	$dernier = array_pop( $parties );
	$et      = array( 'de' => ' und ', 'fr' => ' et ', 'en' => ' and ' )[ $l ] ?? ' und ';
	return $parties ? implode( ', ', $parties ) . $et . $dernier : (string) $dernier;
}

/** Résumé lisible, par ex. « Mo–Fr 7.30–18.30 · Sa 8–18 · So 9–17 » (dans la langue de la page). */
function schiesser_horaires_resume() {
	$l       = ( ! is_admin() && function_exists( 'schiesser_langue' ) ) ? schiesser_langue() : 'de';
	$tous    = array(
		'de' => array( 1 => 'Mo', 2 => 'Di', 3 => 'Mi', 4 => 'Do', 5 => 'Fr', 6 => 'Sa', 0 => 'So' ),
		'fr' => array( 1 => 'lun', 2 => 'mar', 3 => 'mer', 4 => 'jeu', 5 => 'ven', 6 => 'sam', 0 => 'dim' ),
		'en' => array( 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 0 => 'Sun' ),
	);
	$courts  = $tous[ $l ] ?? $tous['de'];
	$groupes = array();
	$h_tous  = schiesser_reglage( 'horaires' );
	foreach ( schiesser_jours() as $n => $nom ) {
		$h     = $h_tous[ $n ];
		$texte = ! empty( $h['ferme'] ) ? schiesser_t( 'geschlossen', $l ) : schiesser_uhr( $h['ouverture'], false, $l ) . '–' . schiesser_uhr( $h['fermeture'], false, $l );
		$der   = count( $groupes ) - 1;
		if ( $der >= 0 && $groupes[ $der ]['texte'] === $texte ) {
			$groupes[ $der ]['fin'] = $courts[ $n ];
		} else {
			$groupes[] = array( 'debut' => $courts[ $n ], 'fin' => null, 'texte' => $texte );
		}
	}
	$parties = array();
	foreach ( $groupes as $g ) {
		$parties[] = ( $g['fin'] ? $g['debut'] . '–' . $g['fin'] : $g['debut'] ) . ' ' . $g['texte'];
	}
	return implode( ' · ', $parties );
}

/* ------------------------------------------------------------------ */
/* Enregistrement et nettoyage                                         */
/* ------------------------------------------------------------------ */

add_action( 'admin_init', function () {
	register_setting( 'schiesser_reglages_groupe', SCHIESSER_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'schiesser_nettoyer_reglages',
		'default'           => schiesser_reglages_defaut(),
	) );
} );

/* Les gérants du site (rôle Éditeur ou Gérant) peuvent enregistrer cette page, pas seulement l'administrateur. */
add_filter( 'option_page_capability_schiesser_reglages_groupe', function () {
	return 'edit_pages';
} );

function schiesser_nettoyer_reglages( $entree ) {
	$defaut = schiesser_reglages_defaut();
	if ( ! is_array( $entree ) ) {
		return wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), $defaut );
	}
	$propre = $defaut;

	$textes = array( 'telephone', 'rue', 'code_postal', 'ville', 'region', 'mention', 'horaires_note', 'nom_etablissement', 'gamme_prix', 'annee_fondation', 'raison_sociale', 'numero_ide' );
	foreach ( $textes as $cle ) {
		$propre[ $cle ] = isset( $entree[ $cle ] ) ? sanitize_text_field( $entree[ $cle ] ) : '';
	}
	$propre['email']        = isset( $entree['email'] ) ? sanitize_email( $entree['email'] ) : '';
	$propre['presentation'] = isset( $entree['presentation'] ) ? sanitize_textarea_field( $entree['presentation'] ) : '';
	// Profils officiels : une adresse par ligne (Google, Tripadvisor, Wikidata…).
	$profils = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) ( $entree['profils'] ?? '' ) ) as $ligne ) {
		$url = esc_url_raw( trim( $ligne ) );
		if ( $url ) {
			$profils[] = $url;
		}
	}
	$propre['profils'] = implode( "\n", $profils );
	foreach ( array( 'instagram', 'facebook', 'carte_pdf', 'carte_pdf_fr', 'carte_pdf_en' ) as $cle ) {
		$propre[ $cle ] = isset( $entree[ $cle ] ) ? esc_url_raw( $entree[ $cle ] ) : '';
	}
	$propre['lien_maps']     = schiesser_nettoyer_lien_maps( $entree['lien_maps'] ?? '', $propre['ville'] );
	$propre['carte_google']  = schiesser_nettoyer_carte_google( $entree['carte_google'] ?? '' );
	$propre['carte_au_clic'] = empty( $entree['carte_au_clic'] ) ? 0 : 1;
	// Avis Google : la clé n'est affichée qu'aux administrateurs ; absente du formulaire, elle est conservée.
	$avant                 = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), $defaut );
	$propre['avis_cle']    = array_key_exists( 'avis_cle', $entree ) ? preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $entree['avis_cle'] ) : $avant['avis_cle'];
	$propre['avis_lieu']   = array_key_exists( 'avis_lieu', $entree ) ? preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) $entree['avis_lieu'] ) : $avant['avis_lieu'];
	$note                  = str_replace( ',', '.', trim( (string) ( $entree['avis_note'] ?? '' ) ) );
	$propre['avis_note']   = ( is_numeric( $note ) && $note >= 1 && $note <= 5 ) ? (string) round( (float) $note, 1 ) : '';
	$propre['avis_nombre'] = absint( $entree['avis_nombre'] ?? 0 ) ?: '';
	$propre['pays']               = isset( schiesser_pays()[ $entree['pays'] ?? '' ] ) ? $entree['pays'] : 'CH';
	$propre['type_etablissement'] = isset( schiesser_types_etablissement()[ $entree['type_etablissement'] ?? '' ] ) ? $entree['type_etablissement'] : $defaut['type_etablissement'];
	$propre['page_boutique']      = absint( $entree['page_boutique'] ?? 0 );

	foreach ( array( 'latitude', 'longitude' ) as $cle ) {
		$v              = str_replace( ',', '.', trim( (string) ( $entree[ $cle ] ?? '' ) ) );
		$propre[ $cle ] = is_numeric( $v ) ? $v : '';
	}

	$propre['vitrine'] = array();
	foreach ( (array) ( $entree['vitrine'] ?? array() ) as $ligne ) {
		$ligne = sanitize_text_field( $ligne );
		if ( '' !== $ligne ) {
			$propre['vitrine'][] = $ligne;
		}
	}

	foreach ( schiesser_jours() as $n => $nom ) {
		$h = $entree['horaires'][ $n ] ?? array();
		$propre['horaires'][ $n ] = array(
			'ouverture' => schiesser_nettoyer_heure( $h['ouverture'] ?? '', $propre['horaires'][ $n ]['ouverture'] ),
			'fermeture' => schiesser_nettoyer_heure( $h['fermeture'] ?? '', $propre['horaires'][ $n ]['fermeture'] ),
			'ferme'     => empty( $h['ferme'] ) ? 0 : 1,
		);
	}
	$propre['feries']     = schiesser_nettoyer_feries( $entree['feries'] ?? array() );
	$propre['exceptions'] = schiesser_nettoyer_exceptions( $entree['exceptions'] ?? array() );
	$propre['annonces']       = schiesser_nettoyer_annonces( $entree['annonces'] ?? array() );
	$propre['annonce_feries'] = empty( $entree['annonce_feries'] ) ? 0 : 1;
	$propre['barre_mobile']   = empty( $entree['barre_mobile'] ) ? 0 : 1;
	$propre['allergenes_note'] = sanitize_textarea_field( $entree['allergenes_note'] ?? '' );
	return $propre;
}

/**
 * Lien Google Maps : un lien de recherche Google (google.com/search?q=…, copié depuis
 * la fiche affichée dans les résultats) devient un lien Google Maps propre, sans
 * les paramètres de suivi. Les liens « Partager » de Google Maps sont gardés tels quels.
 */
function schiesser_nettoyer_lien_maps( $url, $ville = '' ) {
	$url = esc_url_raw( trim( (string) $url ) );
	if ( '' === $url ) {
		return '';
	}
	$parties = wp_parse_url( $url );
	$hote    = strtolower( $parties['host'] ?? '' );
	if ( preg_match( '/(^|\.)google\.[a-z.]+$/', $hote ) && '/search' === ( $parties['path'] ?? '' ) ) {
		parse_str( (string) ( $parties['query'] ?? '' ), $params );
		$q = trim( (string) ( $params['q'] ?? '' ) );
		if ( '' !== $q ) {
			if ( $ville && false === stripos( $q, $ville ) ) {
				$q .= ' ' . $ville;
			}
			return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $q );
		}
	}
	return $url;
}

/**
 * Carte Google Maps à intégrer : on accepte le code « Intégrer une carte » de Google Maps
 * (<iframe src="https://www.google.com/maps/embed?pb=…">) ou son adresse seule.
 */
function schiesser_nettoyer_carte_google( $code ) {
	$code = trim( (string) $code );
	if ( preg_match( '/src=["\']([^"\']+)["\']/i', $code, $m ) ) {
		$code = html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' );
	}
	$url     = esc_url_raw( $code );
	$parties = wp_parse_url( $url );
	$hote    = strtolower( $parties['host'] ?? '' );
	$chemin  = (string) ( $parties['path'] ?? '' );
	if ( 'https' === ( $parties['scheme'] ?? '' ) && preg_match( '/(^|\.)google\.[a-z.]+$/', $hote ) && 0 === strpos( $chemin, '/maps' ) ) {
		return $url;
	}
	return '';
}

function schiesser_nettoyer_heure( $valeur, $defaut ) {
	return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', (string) $valeur ) ? $valeur : $defaut;
}

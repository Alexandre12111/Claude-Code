<?php
/**
 * Maillage interne : section « Weiter im Haus » en bas des pages principales.
 *
 * Trois cartes vers les autres pages de la maison, avec un texte de lien descriptif
 * (bon pour les visiteurs et pour Google). La page en cours et les pages non publiées sont ignorées.
 */

defined( 'ABSPATH' ) || exit;

/** Pages proposées : clé => [ surtitre, texte du lien, phrase ]. */
function schiesser_maillage_pages() {
	return array(
		'boutique'   => array( 'Erdgeschoss', 'Confiserie in Basel', 'Basler Läckerli, Pralinen, Torten und Gebäck aus der eigenen Backstube.' ),
		'salon'      => array( 'Erster Stock', 'Tea Room am Marktplatz', 'Frühstück, Kaffee, Tee und hausgemachte Schokolade im ältesten Kaffeehaus der Schweiz.' ),
		'histoire'   => array( 'Seit 1870', 'Geschichte des Hauses', 'Vom Glarner Konditor Rudolf Schiesser bis heute, mit Bildern aus dem Archiv.' ),
		'visiter'    => array( 'Marktplatz 19', 'Öffnungszeiten & Anfahrt', 'Gegenüber dem Rathaus, wenige Schritte vom Tram: so finden Sie uns.' ),
		'entreprise' => array( 'Für Firmen', 'Firmengeschenke aus Basel', 'Pralinen- und Läckerli-Boxen für Ihre Kundschaft und Ihr Team.' ),
		'contact'    => array( 'Bestellen', 'Kontakt & Bestellung', 'Torten, Geschenkboxen und Reservationen: wir beraten Sie gerne.' ),
	);
}

/** Ordre de proposition selon la page en cours (les plus utiles d'abord). */
function schiesser_maillage_ordre( $cle ) {
	$ordres = array(
		'accueil'    => array( 'salon', 'histoire', 'entreprise' ),
		'boutique'   => array( 'salon', 'entreprise', 'visiter' ),
		'salon'      => array( 'boutique', 'histoire', 'visiter' ),
		'histoire'   => array( 'salon', 'boutique', 'visiter' ),
		'visiter'    => array( 'salon', 'boutique', 'contact' ),
		'contact'    => array( 'boutique', 'entreprise', 'visiter' ),
		'entreprise' => array( 'boutique', 'contact', 'histoire' ),
	);
	return $ordres[ $cle ] ?? array();
}

/** Clé de la page affichée (accueil, boutique, salon…), ou ''. */
function schiesser_maillage_cle_courante() {
	if ( is_front_page() ) {
		return 'accueil';
	}
	if ( ! is_page() ) {
		return '';
	}
	$id = get_queried_object_id();
	foreach ( array_keys( schiesser_adresses_pages() ) as $cle ) {
		$p = schiesser_page( $cle );
		if ( $p && (int) $p->ID === (int) $id ) {
			return $cle;
		}
	}
	return '';
}

function schiesser_html_maillage() {
	$cle   = schiesser_maillage_cle_courante();
	$infos = schiesser_maillage_pages();
	$cartes = '';
	$n      = 0;
	foreach ( schiesser_maillage_ordre( $cle ) as $cible ) {
		$p = schiesser_page( $cible );
		if ( ! $p || ! isset( $infos[ $cible ] ) ) {
			continue;
		}
		++$n;
		list( $sur, $titre, $texte ) = array_map( 'schiesser_t', $infos[ $cible ] );
		$cartes .= '<a class="ml-carte" href="' . esc_url( get_permalink( $p ) ) . '">'
			. '<span class="ml-no" aria-hidden="true">' . sprintf( '%02d', $n ) . '</span>'
			. '<span class="ml-k">' . esc_html( $sur ) . '</span>'
			. '<span class="ml-t">' . esc_html( $titre ) . '</span>'
			. '<span class="ml-d">' . esc_html( $texte ) . '</span>'
			. '<span class="ml-a" aria-hidden="true">→</span></a>';
	}
	if ( $n < 2 ) {
		return '';
	}
	return '<nav class="maillage" aria-labelledby="maillage-titre"><div class="wrap">'
		. '<div class="ml-tete rv"><p class="eyebrow">' . esc_html( schiesser_t( 'Weiter im Haus' ) ) . '</p><h2 id="maillage-titre" class="ml-titre">Confiserie, Tea Room <em>' . esc_html( schiesser_t( 'und mehr' ) ) . '</em></h2></div>'
		. '<div class="ml-grille rv">' . $cartes . '</div></div></nav>';
}

add_action( 'schiesser_avant_pied', function () {
	echo schiesser_html_maillage(); // phpcs:ignore WordPress.Security.EscapeOutput
} );

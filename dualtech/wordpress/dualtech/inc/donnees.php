<?php
/**
 * Partenaires, intervenants et programme.
 *
 * Source : l'extension DualTech Studio (menu DualTech de l'administration). Tant qu'elle n'est pas active
 * ou que ses listes sont vides, le site affiche les données livrées avec le thème (contenu/donnees.json),
 * pour qu'aucune page ne soit jamais vide.
 */

defined( 'ABSPATH' ) || exit;

/** Données livrées avec le thème. */
function dualtech_donnees_livrees() {
	static $d = null;
	if ( null === $d ) {
		$d = json_decode( (string) @file_get_contents( DUALTECH_DIR . '/contenu/donnees.json' ), true ) ?: array(); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
	}
	return $d;
}

/** Libellés des catégories d'intervenants et des formats de séance (comme dans la maquette). */
function dualtech_univers() {
	return function_exists( 'dts_univers' ) ? dts_univers() : array( 'innovation' => 'Innovation', 'institutions' => 'Institutions', 'industrie' => 'Industrie', 'finance' => 'Financement et marchés', 'recherche' => 'Recherche' );
}
function dualtech_types() {
	return array( 'keynote' => 'Keynote', 'table' => 'Table ronde', 'atelier' => 'Ateliers', 'networking' => 'Networking', 'autres' => 'Allocution', 'temps-fort' => 'Temps fort' );
}

/** Intervenants (id => id, nom, fonction, orga, univers, photo). */
function dt_intervenants() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	if ( function_exists( 'dualtech_intervenants' ) && dualtech_intervenants() ) {
		return $cache = dualtech_intervenants();
	}
	$cache = array();
	foreach ( dualtech_donnees_livrees()['intervenants'] ?? array() as $i ) {
		$cache[ $i['cle'] ] = array( 'id' => $i['cle'], 'nom' => $i['nom'], 'fonction' => $i['fonction'], 'orga' => $i['orga'], 'univers' => $i['univers'], 'photo' => DUALTECH_URI . '/assets/' . $i['photo'] );
	}
	return $cache;
}

/** Séances rangées par heure (debut « 09h30 », intervenants et moderateurs = ids des intervenants). */
function dt_seances() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	if ( function_exists( 'dualtech_seances' ) && dualtech_seances() ) {
		return $cache = dualtech_seances();
	}
	$cache = array();
	foreach ( dualtech_donnees_livrees()['seances'] ?? array() as $s ) {
		$cache[] = array(
			'id' => $s['cle'], 'titre' => $s['titre'], 'debut' => str_replace( ':', 'h', $s['debut'] ), 'fin' => str_replace( ':', 'h', $s['fin'] ),
			'type' => $s['type'], 'note' => $s['note'], 'intervenants' => $s['intervenants'], 'moderateurs' => $s['moderateurs'],
			'temps_fort' => ! empty( $s['temps_fort'] ), 'resume' => $s['resume'],
		);
	}
	usort( $cache, function ( $a, $b ) {
		return strcmp( $a['debut'], $b['debut'] );
	} );
	return $cache;
}

/** Séances d'un intervenant : intervention ou modération (rôle affiché dans sa fiche). */
function dt_seances_de( $id ) {
	$sortie = array();
	foreach ( dt_seances() as $s ) {
		$dans = function ( $liste ) use ( $id ) {
			return in_array( (string) $id, array_map( 'strval', (array) $liste ), true );
		};
		if ( $dans( $s['intervenants'] ) ) {
			$sortie[] = $s + array( 'role' => 'intervention' );
		} elseif ( $dans( $s['moderateurs'] ) ) {
			$sortie[] = $s + array( 'role' => 'modération' );
		}
	}
	return $sortie;
}

/** Partenaires (d'une rubrique ou de plusieurs), dans l'ordre d'affichage. */
function dt_partenaires( $rubriques = null ) {
	$rubriques = $rubriques ? (array) $rubriques : null;
	if ( function_exists( 'dualtech_partenaires' ) && dualtech_partenaires() ) {
		return dualtech_partenaires( $rubriques );
	}
	$sortie = array();
	foreach ( dualtech_donnees_livrees()['partenaires'] ?? array() as $p ) {
		if ( $rubriques && ! array_intersect( $rubriques, $p['rubriques'] ) ) {
			continue;
		}
		$sortie[] = array( 'id' => $p['cle'], 'nom' => $p['nom'], 'logo' => $p['logo'] ? DUALTECH_URI . '/assets/' . $p['logo'] : '', 'description' => $p['description'], 'lien' => $p['lien'], 'rubriques' => $p['rubriques'] );
	}
	return $sortie;
}

/** Nom d'une rubrique de partenaires (tel que renommé dans DualTech Studio, sinon celui de la maquette). */
function dt_nom_rubrique( $slug ) {
	$noms = array( 'institutions' => 'Institutions publiques', 'financement' => 'Financement et marchés', 'ecosystemes' => 'Écosystèmes industriels et technologiques', 'medias' => 'Communication et médias' );
	if ( function_exists( 'dualtech_rubriques' ) ) {
		$plugin = dualtech_rubriques();
		if ( ! empty( $plugin[ $slug ] ) ) {
			return $plugin[ $slug ];
		}
	}
	return $noms[ $slug ] ?? $slug;
}

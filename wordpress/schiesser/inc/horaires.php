<?php
/**
 * Jours fériés de Bâle et horaires exceptionnels.
 *
 * - Les jours fériés (Nouvel An, Pâques, Ascension, Pentecôte, 1er août, Noël…) et les trois
 *   jours de la Fasnacht sont calculés chaque année, sans rien saisir.
 *   Pour chacun, Réglages maison → Coordonnées et horaires : horaires habituels, fermé
 *   ou horaires spéciaux.
 * - Fermetures et horaires exceptionnels à des dates choisies (vacances, inventaire…).
 *
 * Tout le site en tient compte : état « Ouvert / Fermé » en direct, bandeau, barre du
 * téléphone, tableau des horaires (« Jours particuliers à venir ») et fiche envoyée à Google.
 */

defined( 'ABSPATH' ) || exit;

/** Dimanche de Pâques (calendrier grégorien, algorithme de Meeus), sans l'extension « calendar » de PHP. */
function schiesser_paques( $annee ) {
	$a = $annee % 19;
	$b = intdiv( $annee, 100 );
	$c = $annee % 100;
	$d = intdiv( $b, 4 );
	$e = $b % 4;
	$f = intdiv( $b + 8, 25 );
	$g = intdiv( $b - $f + 1, 3 );
	$h = ( 19 * $a + $b - $d - $g + 15 ) % 30;
	$i = intdiv( $c, 4 );
	$k = $c % 4;
	$l = ( 32 + 2 * $e + 2 * $i - $h - $k ) % 7;
	$m = intdiv( $a + 11 * $h + 22 * $l, 451 );
	$mois = intdiv( $h + $l - 7 * $m + 114, 31 );
	$jour = ( ( $h + $l - 7 * $m + 114 ) % 31 ) + 1;
	return new DateTimeImmutable( sprintf( '%04d-%02d-%02d', $annee, $mois, $jour ), wp_timezone() );
}

/**
 * Jours fériés et jours particuliers de Bâle.
 * clé => [ nom, date fixe « mois-jour » ou décalage en jours depuis Pâques, réglage proposé ].
 */
function schiesser_feries_liste() {
	return array(
		'nouvel-an'         => array( 'Nouvel An', '01-01', 'ferme' ),
		'fasnacht-lundi'    => array( 'Fasnacht : lundi (Morgestraich)', -41, 'normal' ),
		'fasnacht-mardi'    => array( 'Fasnacht : mardi', -40, 'normal' ),
		'fasnacht-mercredi' => array( 'Fasnacht : mercredi', -39, 'normal' ),
		'vendredi-saint'    => array( 'Vendredi saint', -2, 'ferme' ),
		'paques'            => array( 'Pâques', 0, 'normal' ),
		'lundi-paques'      => array( 'Lundi de Pâques', 1, 'ferme' ),
		'fete-travail'      => array( 'Fête du travail', '05-01', 'ferme' ),
		'ascension'         => array( 'Ascension', 39, 'ferme' ),
		'pentecote'         => array( 'Pentecôte', 49, 'normal' ),
		'lundi-pentecote'   => array( 'Lundi de Pentecôte', 50, 'ferme' ),
		'fete-nationale'    => array( 'Fête nationale', '08-01', 'ferme' ),
		'veille-noel'       => array( 'Veille de Noël', '12-24', 'special' ),
		'noel'              => array( 'Noël', '12-25', 'ferme' ),
		'saint-etienne'     => array( 'Saint-Étienne', '12-26', 'ferme' ),
		'saint-sylvestre'   => array( 'Saint-Sylvestre', '12-31', 'special' ),
	);
}

/** Réglages proposés pour chaque jour férié (modifiables dans Réglages maison). */
function schiesser_feries_defaut() {
	$d = array();
	foreach ( schiesser_feries_liste() as $cle => $f ) {
		$d[ $cle ] = array( 'mode' => $f[2], 'ouverture' => '07:30', 'fermeture' => '16:00' );
	}
	return $d;
}

/** Date d'un jour férié pour une année (Y-m-d). */
function schiesser_ferie_date( $cle, $annee ) {
	$f = schiesser_feries_liste()[ $cle ] ?? null;
	if ( ! $f ) {
		return '';
	}
	if ( is_string( $f[1] ) ) {
		return $annee . '-' . $f[1];
	}
	return schiesser_paques( $annee )->modify( ( $f[1] >= 0 ? '+' : '' ) . $f[1] . ' days' )->format( 'Y-m-d' );
}

/** Jours fériés d'une année : Y-m-d => clé. */
function schiesser_feries_annee( $annee ) {
	static $memo = array();
	if ( ! isset( $memo[ $annee ] ) ) {
		$memo[ $annee ] = array();
		foreach ( array_keys( schiesser_feries_liste() ) as $cle ) {
			$memo[ $annee ][ schiesser_ferie_date( $cle, $annee ) ] = $cle;
		}
	}
	return $memo[ $annee ];
}

/**
 * Jour particulier (férié réglé autrement que d'habitude, ou date exceptionnelle).
 *
 * @param string $ymd Date Y-m-d (fuseau du site).
 * @return array|null [ 'motif' => …, 'plage' => null (fermé) ou [ '07:30', '16:00' ] ]
 */
function schiesser_jour_particulier( $ymd ) {
	// 1. Dates exceptionnelles saisies (prioritaires).
	foreach ( (array) schiesser_reglage( 'exceptions' ) as $x ) {
		if ( ! empty( $x['debut'] ) && $ymd >= $x['debut'] && $ymd <= ( $x['fin'] ?: $x['debut'] ) ) {
			return array(
				'motif' => $x['motif'] ?: ( 'ferme' === $x['mode'] ? 'Fermeture exceptionnelle' : 'Horaires exceptionnels' ),
				'plage' => 'ferme' === $x['mode'] ? null : array( $x['ouverture'], $x['fermeture'] ),
			);
		}
	}
	// 2. Jours fériés.
	$cle = schiesser_feries_annee( (int) substr( $ymd, 0, 4 ) )[ $ymd ] ?? null;
	if ( $cle ) {
		$r = wp_parse_args( (array) ( schiesser_reglage( 'feries' )[ $cle ] ?? array() ), schiesser_feries_defaut()[ $cle ] );
		if ( 'ferme' === $r['mode'] ) {
			return array( 'motif' => schiesser_feries_liste()[ $cle ][0], 'plage' => null );
		}
		if ( 'special' === $r['mode'] ) {
			return array( 'motif' => schiesser_feries_liste()[ $cle ][0], 'plage' => array( $r['ouverture'], $r['fermeture'] ) );
		}
	}
	return null;
}

/**
 * Horaires d'une date précise, jours particuliers compris.
 *
 * @return array|null [ ouverture, fermeture ] en heures décimales, ou null si fermé.
 */
function schiesser_horaires_date( $ymd ) {
	$p = schiesser_jour_particulier( $ymd );
	if ( $p ) {
		return $p['plage'] ? array( schiesser_heure_decimale( $p['plage'][0] ), schiesser_heure_decimale( $p['plage'][1] ) ) : null;
	}
	$jour = (int) ( new DateTimeImmutable( $ymd, wp_timezone() ) )->format( 'w' );
	return schiesser_horaires_js()[ $jour ] ?? null;
}

/**
 * Jours particuliers des prochains jours.
 *
 * @return array[] [ 'date' => Y-m-d, 'motif' => …, 'plage' => null|[o, f] ]
 */
function schiesser_jours_particuliers( $nb_jours = 60, $depuis = null ) {
	$d     = $depuis ? new DateTimeImmutable( $depuis, wp_timezone() ) : current_datetime()->setTime( 0, 0 );
	$liste = array();
	for ( $k = 0; $k < $nb_jours; $k++ ) {
		$ymd = $d->modify( '+' . $k . ' days' )->format( 'Y-m-d' );
		$p   = schiesser_jour_particulier( $ymd );
		if ( $p ) {
			$liste[] = array( 'date' => $ymd ) + $p;
		}
	}
	return $liste;
}

/** Jours particuliers pour les scripts du site : { "2026-04-06": { h: null|[7.5, 16], m: "Lundi de Pâques" } }. */
function schiesser_particuliers_js() {
	$sortie = array();
	foreach ( schiesser_jours_particuliers( 45, current_datetime()->modify( '-1 day' )->format( 'Y-m-d' ) ) as $p ) {
		$sortie[ $p['date'] ] = array(
			'h' => $p['plage'] ? array( schiesser_heure_decimale( $p['plage'][0] ), schiesser_heure_decimale( $p['plage'][1] ) ) : null,
			'm' => $p['motif'],
		);
	}
	return (object) $sortie;
}

/** « lundi 6 avril » */
function schiesser_date_fr( $ymd, $format = 'l j F' ) {
	return wp_date( $format, ( new DateTimeImmutable( $ymd . ' 12:00', wp_timezone() ) )->getTimestamp() );
}

/** Liste « Jours particuliers à venir » sous les tableaux d'horaires. */
function schiesser_html_jours_particuliers( $nb_jours = 45, $max = 5 ) {
	$liste = array_slice( schiesser_jours_particuliers( $nb_jours ), 0, $max );
	if ( ! $liste ) {
		return '';
	}
	$html = '<div class="h-particuliers" data-nosnippet><p class="h-part-titre">Jours particuliers à venir</p><ul>';
	foreach ( $liste as $p ) {
		$html .= '<li><span class="h-part-jour"><strong>' . esc_html( ucfirst( schiesser_date_fr( $p['date'] ) ) ) . '</strong> · ' . esc_html( $p['motif'] ) . '</span>'
			. '<span class="h-part-h">' . ( $p['plage'] ? esc_html( $p['plage'][0] . ' – ' . $p['plage'][1] ) : 'Fermé' ) . '</span></li>';
	}
	return $html . '</ul></div>';
}

/** Données structurées : horaires spéciaux des 90 prochains jours (Google les affiche sur la fiche). */
function schiesser_schema_horaires_speciaux() {
	$sortie = array();
	foreach ( schiesser_jours_particuliers( 90 ) as $p ) {
		$sortie[] = array(
			'@type'        => 'OpeningHoursSpecification',
			'validFrom'    => $p['date'],
			'validThrough' => $p['date'],
			'opens'        => $p['plage'] ? $p['plage'][0] : '00:00',
			'closes'       => $p['plage'] ? $p['plage'][1] : '00:00', // 00:00–00:00 : fermé toute la journée
		);
	}
	return $sortie;
}

/* ------------------------------------------------------------------ */
/* Réglages maison : nettoyage et écran                                */
/* ------------------------------------------------------------------ */

function schiesser_nettoyer_feries( $entree ) {
	$propre = schiesser_feries_defaut();
	foreach ( $propre as $cle => $def ) {
		$e = (array) ( $entree[ $cle ] ?? array() );
		if ( ! $e ) {
			continue;
		}
		$propre[ $cle ] = array(
			'mode'      => in_array( $e['mode'] ?? '', array( 'normal', 'ferme', 'special' ), true ) ? $e['mode'] : $def['mode'],
			'ouverture' => schiesser_nettoyer_heure( $e['ouverture'] ?? '', $def['ouverture'] ),
			'fermeture' => schiesser_nettoyer_heure( $e['fermeture'] ?? '', $def['fermeture'] ),
		);
	}
	return $propre;
}

function schiesser_nettoyer_exceptions( $entree ) {
	$hier   = current_datetime()->modify( '-1 day' )->format( 'Y-m-d' );
	$propre = array();
	foreach ( (array) $entree as $x ) {
		$debut = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $x['debut'] ?? '' ) ) ? $x['debut'] : '';
		if ( ! $debut ) {
			continue;
		}
		$fin = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $x['fin'] ?? '' ) ) ? $x['fin'] : $debut;
		if ( $fin < $debut ) {
			list( $debut, $fin ) = array( $fin, $debut );
		}
		if ( $fin < $hier ) {
			continue; // période passée : retirée automatiquement
		}
		$propre[] = array(
			'debut'     => $debut,
			'fin'       => $fin,
			'mode'      => 'special' === ( $x['mode'] ?? '' ) ? 'special' : 'ferme',
			'ouverture' => schiesser_nettoyer_heure( $x['ouverture'] ?? '', '08:00' ),
			'fermeture' => schiesser_nettoyer_heure( $x['fermeture'] ?? '', '16:00' ),
			'motif'     => sanitize_text_field( $x['motif'] ?? '' ),
		);
	}
	usort( $propre, function ( $a, $b ) {
		return strcmp( $a['debut'], $b['debut'] );
	} );
	return array_slice( $propre, 0, 20 );
}

/** Carte « Jours fériés et fermetures exceptionnelles » (onglet Coordonnées et horaires). */
function schiesser_carte_jours_particuliers( $o, $r ) {
	$feries = wp_parse_args( (array) ( $r['feries'] ?? array() ), schiesser_feries_defaut() );
	$auj    = current_datetime()->format( 'Y-m-d' );
	$annee  = (int) current_datetime()->format( 'Y' );
	$modes  = array( 'normal' => 'Horaires habituels', 'ferme' => 'Fermé', 'special' => 'Horaires spéciaux' );

	schiesser_carte_debut( 'Jours fériés et fermetures exceptionnelles', 'Les jours fériés de Bâle et la Fasnacht sont calculés automatiquement chaque année. Choisissez ce que fait la maison ces jours-là : le statut « Ouvert / Fermé », le tableau des horaires, la barre du téléphone et la fiche Google suivent.', 'calendar-alt' );
	?>
	<table class="s-horaires s-feries">
		<thead><tr><th scope="col">Jour</th><th scope="col">Prochaine date</th><th scope="col">Ce jour-là</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th></tr></thead>
		<tbody>
		<?php foreach ( schiesser_feries_liste() as $cle => $f ) :
			$date = schiesser_ferie_date( $cle, $annee );
			if ( $date < $auj ) {
				$date = schiesser_ferie_date( $cle, $annee + 1 );
			}
			$v   = $feries[ $cle ];
			$nom = $o . '[feries][' . $cle . ']';
			?>
			<tr class="js-ferie<?php echo 'special' === $v['mode'] ? ' is-special' : ''; ?>">
				<th scope="row"><?php echo esc_html( $f[0] ); ?></th>
				<td><?php echo esc_html( ucfirst( schiesser_date_fr( $date, 'D j M Y' ) ) ); ?></td>
				<td><select name="<?php echo esc_attr( $nom ); ?>[mode]" class="js-ferie-mode" aria-label="<?php echo esc_attr( $f[0] . ' : ce jour-là' ); ?>">
					<?php foreach ( $modes as $m => $l ) : ?><option value="<?php echo esc_attr( $m ); ?>" <?php selected( $v['mode'], $m ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
				</select></td>
				<td><input type="time" name="<?php echo esc_attr( $nom ); ?>[ouverture]" value="<?php echo esc_attr( $v['ouverture'] ); ?>" aria-label="<?php echo esc_attr( $f[0] . ', ouverture' ); ?>"></td>
				<td><input type="time" name="<?php echo esc_attr( $nom ); ?>[fermeture]" value="<?php echo esc_attr( $v['fermeture'] ); ?>" aria-label="<?php echo esc_attr( $f[0] . ', fermeture' ); ?>"></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<p class="s-aide">Les heures ne comptent que pour « Horaires spéciaux ». Les réglages proposés sont une base : vérifiez les avec la maison.</p>

	<h3 class="s-sous-titre">Dates exceptionnelles</h3>
	<p class="s-aide">Vacances, inventaire, horaires réduits… Une ligne par période. Les périodes passées s'effacent toutes seules.</p>
	<table class="s-horaires s-exceptions">
		<thead><tr><th scope="col">Du</th><th scope="col">Au</th><th scope="col">Ces jours-là</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Motif (affiché sur le site)</th></tr></thead>
		<tbody>
		<?php
		$lignes = array_values( (array) ( $r['exceptions'] ?? array() ) );
		$total  = count( $lignes ) + 3;
		for ( $i = 0; $i < $total; $i++ ) :
			$x   = $lignes[ $i ] ?? array( 'debut' => '', 'fin' => '', 'mode' => 'ferme', 'ouverture' => '08:00', 'fermeture' => '16:00', 'motif' => '' );
			$nom = $o . '[exceptions][' . $i . ']';
			?>
			<tr class="js-ferie<?php echo 'special' === $x['mode'] ? ' is-special' : ''; ?>">
				<td><input type="date" name="<?php echo esc_attr( $nom ); ?>[debut]" value="<?php echo esc_attr( $x['debut'] ); ?>" aria-label="Premier jour"></td>
				<td><input type="date" name="<?php echo esc_attr( $nom ); ?>[fin]" value="<?php echo esc_attr( $x['fin'] ); ?>" aria-label="Dernier jour (vide : un seul jour)"></td>
				<td><select name="<?php echo esc_attr( $nom ); ?>[mode]" class="js-ferie-mode" aria-label="Ces jours-là">
					<option value="ferme" <?php selected( $x['mode'], 'ferme' ); ?>>Fermé</option>
					<option value="special" <?php selected( $x['mode'], 'special' ); ?>>Horaires spéciaux</option>
				</select></td>
				<td><input type="time" name="<?php echo esc_attr( $nom ); ?>[ouverture]" value="<?php echo esc_attr( $x['ouverture'] ); ?>" aria-label="Ouverture"></td>
				<td><input type="time" name="<?php echo esc_attr( $nom ); ?>[fermeture]" value="<?php echo esc_attr( $x['fermeture'] ); ?>" aria-label="Fermeture"></td>
				<td><input type="text" name="<?php echo esc_attr( $nom ); ?>[motif]" value="<?php echo esc_attr( $x['motif'] ); ?>" placeholder="Ex. Vacances annuelles" aria-label="Motif"></td>
			</tr>
		<?php endfor; ?>
		</tbody>
	</table>

	<?php
	$venir = schiesser_jours_particuliers( 60 );
	echo '<p class="s-resume">Dans les 60 prochains jours : ';
	if ( $venir ) {
		echo '<strong>' . esc_html( implode( ' · ', array_map( function ( $p ) {
			return ucfirst( schiesser_date_fr( $p['date'], 'D j M' ) ) . ' ' . ( $p['plage'] ? $p['plage'][0] . '–' . $p['plage'][1] : 'fermé' ) . ' (' . $p['motif'] . ')';
		}, array_slice( $venir, 0, 6 ) ) ) ) . '</strong>';
	} else {
		echo 'aucun jour particulier, horaires habituels.';
	}
	echo '</p>';
	schiesser_carte_fin();
}

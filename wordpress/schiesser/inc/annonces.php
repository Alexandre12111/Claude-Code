<?php
/**
 * Bandeau d'annonce programmable et barre d'action du téléphone.
 *
 * - Bandeau : un message avec une date de début et de fin (« Commandes de Pâques ouvertes »),
 *   affiché tout en haut du site pendant cette période seulement. En option, les jours
 *   fériés et fermetures des 7 prochains jours sont annoncés automatiquement.
 * - Barre du téléphone : « Appeler », « Itinéraire » et l'état d'ouverture, fixés en bas
 *   de l'écran sur les petits écrans.
 *
 * Réglages : Réglages maison → Accueil et pied de page.
 */

defined( 'ABSPATH' ) || exit;

function schiesser_annonces_styles() {
	return array( 'vert' => 'Vert maison', 'sombre' => 'Chocolat', 'sable' => 'Sable' );
}

function schiesser_nettoyer_annonces( $entree ) {
	$hier   = current_datetime()->modify( '-1 day' )->format( 'Y-m-d' );
	$propre = array();
	foreach ( (array) $entree as $a ) {
		$texte = sanitize_text_field( $a['texte'] ?? '' );
		if ( '' === $texte ) {
			continue;
		}
		$date  = '/^\d{4}-\d{2}-\d{2}$/';
		$debut = preg_match( $date, (string) ( $a['debut'] ?? '' ) ) ? $a['debut'] : '';
		$fin   = preg_match( $date, (string) ( $a['fin'] ?? '' ) ) ? $a['fin'] : '';
		if ( $debut && $fin && $fin < $debut ) {
			list( $debut, $fin ) = array( $fin, $debut );
		}
		if ( $fin && $fin < $hier ) {
			continue; // annonce terminée : retirée automatiquement
		}
		$propre[] = array(
			'texte' => mb_substr( $texte, 0, 180 ),
			'lien'  => esc_url_raw( $a['lien'] ?? '' ),
			'debut' => $debut,
			'fin'   => $fin,
			'style' => isset( schiesser_annonces_styles()[ $a['style'] ?? '' ] ) ? $a['style'] : 'vert',
		);
	}
	return array_slice( $propre, 0, 8 );
}

/**
 * Annonces à afficher aujourd'hui : celles saisies, puis les jours particuliers des 7 prochains jours.
 *
 * @return array[] [ texte, lien, style, fin (Y-m-d ou '') ]
 */
function schiesser_annonces_actives() {
	$auj    = current_datetime()->format( 'Y-m-d' );
	$liste  = array();
	foreach ( (array) schiesser_reglage( 'annonces' ) as $a ) {
		if ( ( ! $a['debut'] || $a['debut'] <= $auj ) && ( ! $a['fin'] || $a['fin'] >= $auj ) ) {
			$liste[] = $a;
		}
	}
	if ( schiesser_reglage( 'annonce_feries' ) && function_exists( 'schiesser_jours_particuliers' ) ) {
		$jours = schiesser_jours_particuliers( 8 );
		// Jours qui se suivent avec le même motif et les mêmes horaires : regroupés (« sam. 26 – dim. 27 sept. »).
		$groupes = array();
		foreach ( $jours as $p ) {
			$g = count( $groupes ) - 1;
			if ( $g >= 0 && $groupes[ $g ]['motif'] === $p['motif'] && $groupes[ $g ]['plage'] === $p['plage']
				&& ( new DateTimeImmutable( $groupes[ $g ]['fin'] ) )->modify( '+1 day' )->format( 'Y-m-d' ) === $p['date'] ) {
				$groupes[ $g ]['fin'] = $p['date'];
			} else {
				$groupes[] = $p + array( 'fin' => $p['date'] );
			}
		}
		$groupes = array_slice( $groupes, 0, 3 );
		if ( $groupes ) {
			$jours   = $groupes;
			$parties = array();
			foreach ( $groupes as $p ) {
				$jour      = function ( $d ) use ( $auj ) {
					return $d === $auj ? 'aujourd’hui' : schiesser_date_fr( $d, 'D j M' );
				};
				$quand     = ucfirst( $p['date'] === $p['fin'] ? $jour( $p['date'] ) : $jour( $p['date'] ) . ' – ' . $jour( $p['fin'] ) );
				$parties[] = $quand . ' (' . $p['motif'] . ') : ' . ( $p['plage'] ? 'ouvert de ' . schiesser_heure_fr( $p['plage'][0] ) . ' à ' . schiesser_heure_fr( $p['plage'][1] ) : 'fermé' );
			}
			$liste[] = array(
				'texte' => implode( ' · ', $parties ),
				'lien'  => '',
				'style' => 'sable',
				'fin'   => end( $jours )['fin'],
				'auto'  => true,
			);
		}
	}
	return array_slice( $liste, 0, 3 );
}

/** Bandeau tout en haut de la page (appelé par header.php). */
add_action( 'schiesser_avant_entete', function () {
	$annonces = schiesser_annonces_actives();
	if ( ! $annonces ) {
		return;
	}
	echo '<div class="annonces" role="region" aria-label="Annonces" data-nosnippet>';
	foreach ( $annonces as $a ) {
		$cle = substr( md5( $a['texte'] ), 0, 10 );
		$fin = $a['fin'] ? ( new DateTimeImmutable( $a['fin'] . ' 23:59:59', wp_timezone() ) )->getTimestamp() * 1000 : 0;
		echo '<div class="annonce annonce--' . esc_attr( $a['style'] ) . '" data-cle="' . esc_attr( $cle ) . '"' . ( $fin ? ' data-fin="' . esc_attr( (string) $fin ) . '"' : '' ) . '><div class="wrap">';
		echo '<p>' . ( ! empty( $a['auto'] ) ? '<span class="annonce-k">Horaires</span> ' : '' );
		echo $a['lien'] ? '<a href="' . esc_url( $a['lien'] ) . '">' . esc_html( $a['texte'] ) . ' <span aria-hidden="true">→</span></a>' : esc_html( $a['texte'] );
		echo '</p><button type="button" class="annonce-fermer" aria-label="Masquer cette annonce">×</button></div></div>';
	}
	echo '</div>';
	// Annonce masquée par le visiteur (pour cette visite) ou périmée (page gardée en cache) : retirée avant affichage.
	echo "<script>(function(){var n=Date.now();document.querySelectorAll('.annonce').forEach(function(a){var f=+a.getAttribute('data-fin')||0,m=false;try{m=sessionStorage.getItem('annonce-'+a.getAttribute('data-cle'))==='1'}catch(e){}if(m||(f&&n>f))a.remove();a.querySelector('.annonce-fermer')&&a.querySelector('.annonce-fermer').addEventListener('click',function(){try{sessionStorage.setItem('annonce-'+a.getAttribute('data-cle'),'1')}catch(e){}a.remove()})})})();</script>\n";
} );

/* ------------------------------------------------------------------ */
/* Barre d'action du téléphone                                         */
/* ------------------------------------------------------------------ */

function schiesser_barre_mobile_active() {
	if ( ! schiesser_reglage( 'barre_mobile' ) || is_admin() ) {
		return false;
	}
	// Les fiches produits ont déjà leur barre (prix, commander).
	if ( is_singular( SCHIESSER_PRODUIT ) || ( function_exists( 'is_product' ) && is_product() ) ) {
		return false;
	}
	return true;
}

add_filter( 'body_class', function ( $classes ) {
	if ( schiesser_barre_mobile_active() ) {
		$classes[] = 'a-barre-mobile';
	}
	return $classes;
} );

/** Texte d'état de la barre : « Ouvert jusqu'à 18 h 30 », « Fermé · ouvre demain à 8 h »… (recalculé en direct par site.js). */
function schiesser_barre_mobile_etat() {
	$now = current_datetime();
	$h   = schiesser_horaires_date( $now->format( 'Y-m-d' ) );
	$t   = (int) $now->format( 'G' ) + (int) $now->format( 'i' ) / 60;
	if ( $h && $t >= $h[0] && $t < $h[1] ) {
		return array( true, 'Ouvert jusqu’à ' . schiesser_heure_fr( schiesser_heure_hhmm( $h[1] ) ) );
	}
	for ( $k = 0; $k <= 14; $k++ ) {
		$jour = $now->modify( '+' . $k . ' days' );
		$x    = schiesser_horaires_date( $jour->format( 'Y-m-d' ) );
		if ( $x && ( $k > 0 || $t < $x[0] ) ) {
			$quand = 0 === $k ? '' : ( 1 === $k ? 'demain ' : wp_date( 'l', $jour->getTimestamp() ) . ' ' );
			return array( false, 'Fermé · ouvre ' . $quand . 'à ' . schiesser_heure_fr( schiesser_heure_hhmm( $x[0] ) ) );
		}
	}
	return array( false, 'Fermé' );
}

add_action( 'wp_footer', function () {
	if ( ! schiesser_barre_mobile_active() ) {
		return;
	}
	$tel   = schiesser_reglage( 'telephone' );
	$liens = schiesser_mq_liens_maison();
	$etat  = schiesser_barre_mobile_etat();
	$visite = get_page_by_path( 'nous-visiter' );
	$icone = function ( $d ) {
		return '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="' . $d . '" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	};
	echo '<nav class="barre-mobile" aria-label="Accès rapide">';
	if ( $tel ) {
		echo '<a class="bm-lien" href="' . esc_url( schiesser_lien_tel() ) . '">' . $icone( 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2' ) . '<span>Appeler</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '<a class="bm-lien" href="' . esc_url( $liens['itineraire'] ) . '" target="_blank" rel="noopener">' . $icone( 'M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5' ) . '<span>Itinéraire</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	$etat_html = '<span class="bm-led js-bm-led' . ( $etat[0] ? '' : ' shut' ) . '"></span><span class="js-bm-texte">' . esc_html( $etat[1] ) . '</span>';
	echo $visite
		? '<a class="bm-etat" href="' . esc_url( get_permalink( $visite ) ) . '" data-nosnippet>' . $etat_html . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput
		: '<span class="bm-etat" data-nosnippet>' . $etat_html . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</nav>';
} );

/* ------------------------------------------------------------------ */
/* Réglages maison → Accueil et pied de page                           */
/* ------------------------------------------------------------------ */

function schiesser_carte_annonces( $o, $r ) {
	schiesser_carte_debut( 'Bandeau d’annonce', 'Un message tout en haut du site, pendant la période choisie seulement : « Commandes de Pâques ouvertes », « Fermé le 1er août »… Sans date de fin, il reste affiché jusqu’à ce que vous l’effaciez. Les annonces terminées s’effacent toutes seules.', 'megaphone' );
	?>
	<table class="s-horaires s-exceptions s-annonces">
		<thead><tr><th scope="col">Message</th><th scope="col">Lien (facultatif)</th><th scope="col">Du</th><th scope="col">Au</th><th scope="col">Couleur</th></tr></thead>
		<tbody>
		<?php
		$lignes = array_values( (array) ( $r['annonces'] ?? array() ) );
		$total  = count( $lignes ) + 2;
		for ( $i = 0; $i < $total; $i++ ) :
			$a   = $lignes[ $i ] ?? array( 'texte' => '', 'lien' => '', 'debut' => '', 'fin' => '', 'style' => 'vert' );
			$nom = $o . '[annonces][' . $i . ']';
			?>
			<tr class="is-special">
				<td><input type="text" name="<?php echo esc_attr( $nom ); ?>[texte]" value="<?php echo esc_attr( $a['texte'] ); ?>" placeholder="<?php echo 0 === $i ? 'Ex. Commandes de Pâques ouvertes jusqu’au 2 avril' : ''; ?>" aria-label="Message" maxlength="180"></td>
				<td><input type="url" name="<?php echo esc_attr( $nom ); ?>[lien]" value="<?php echo esc_attr( $a['lien'] ); ?>" placeholder="https://…" aria-label="Lien"></td>
				<td><input type="date" name="<?php echo esc_attr( $nom ); ?>[debut]" value="<?php echo esc_attr( $a['debut'] ); ?>" aria-label="Afficher à partir du"></td>
				<td><input type="date" name="<?php echo esc_attr( $nom ); ?>[fin]" value="<?php echo esc_attr( $a['fin'] ); ?>" aria-label="Afficher jusqu’au"></td>
				<td><select name="<?php echo esc_attr( $nom ); ?>[style]" aria-label="Couleur">
					<?php foreach ( schiesser_annonces_styles() as $v => $l ) : ?><option value="<?php echo esc_attr( $v ); ?>" <?php selected( $a['style'], $v ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
				</select></td>
			</tr>
		<?php endfor; ?>
		</tbody>
	</table>
	<div class="s-grille">
		<?php
		schiesser_champ( $o, 'annonce_feries', $r['annonce_feries'], array( 'libelle' => 'Annoncer automatiquement les jours fériés et fermetures, 7 jours avant', 'type' => 'toggle', 'aide' => 'Par exemple « Lundi 6 avril (Lundi de Pâques) : fermé ». Réglages des jours fériés : onglet Coordonnées et horaires.', 'classe' => 's-large' ) );
		schiesser_champ( $o, 'barre_mobile', $r['barre_mobile'], array( 'libelle' => 'Barre « Appeler · Itinéraire · Ouvert » en bas de l’écran du téléphone', 'type' => 'toggle', 'aide' => 'Toujours visible sur les téléphones, sauf sur les fiches produits qui ont leur propre barre.', 'classe' => 's-large' ) );
		?>
	</div>
	<?php
	schiesser_carte_fin();
}

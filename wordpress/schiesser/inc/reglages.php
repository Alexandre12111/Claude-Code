<?php
/**
 * Page « Réglages de la maison ».
 *
 * Les informations saisies ici une seule fois alimentent tout le site :
 * bandeau du haut (ouvert/fermé), pied de page, blocs d'horaires, etc.
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

/** Valeurs par défaut, reprises de la maquette. */
function schiesser_reglages_defaut() {
	$horaires = array();
	foreach ( schiesser_jours() as $n => $nom ) {
		$horaires[ $n ] = array( 'ouverture' => '07:30', 'fermeture' => '18:30', 'ferme' => 0 );
	}
	$horaires[6] = array( 'ouverture' => '08:00', 'fermeture' => '18:00', 'ferme' => 0 );
	$horaires[0] = array( 'ouverture' => '09:00', 'fermeture' => '17:00', 'ferme' => 0 );

	return array(
		'telephone'     => '+41 61 261 60 77',
		'email'         => 'info@confiserie-schiesser.ch',
		'adresse_1'     => 'Marktplatz',
		'adresse_2'     => '4001 Basel, Suisse',
		'mention'       => 'Confiserie fondée à Basel · Marktplatz',
		'horaires'      => $horaires,
		'horaires_note' => 'Les horaires peuvent varier les jours fériés. En cas de doute, un appel suffit.',
		'vitrine'       => array( 'Läckerli', 'Truffes', 'Tarte du jour' ),
		'presentation'  => "Confiserie fondée à Basel. Une maison, un savoir-faire, la même place depuis plus d'un siècle.",
		'instagram'     => '',
		'facebook'      => '',
	);
}

/**
 * Lit un réglage (avec repli sur la valeur par défaut).
 */
function schiesser_reglage( $cle ) {
	$valeurs = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), schiesser_reglages_defaut() );
	return isset( $valeurs[ $cle ] ) ? $valeurs[ $cle ] : '';
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

/** Résumé lisible, par ex. « Lun–Ven 07:30–18:30 · Sam 08:00–18:00 ». */
function schiesser_horaires_resume() {
	$courts  = array( 1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 0 => 'Dim' );
	$groupes = array();
	foreach ( schiesser_jours() as $n => $nom ) {
		$h     = schiesser_reglage( 'horaires' )[ $n ];
		$texte = ! empty( $h['ferme'] ) ? 'fermé' : $h['ouverture'] . '–' . $h['fermeture'];
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
/* Administration                                                      */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	add_menu_page(
		'Réglages de la maison',
		'Réglages maison',
		'edit_pages',
		'schiesser-reglages',
		'schiesser_page_reglages',
		'dashicons-store',
		3
	);
} );

add_action( 'admin_init', function () {
	register_setting( 'schiesser_reglages_groupe', SCHIESSER_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'schiesser_nettoyer_reglages',
		'default'           => schiesser_reglages_defaut(),
	) );
} );

/* Les éditeurs (le client) peuvent enregistrer cette page, pas seulement l'administrateur. */
add_filter( 'option_page_capability_schiesser_reglages_groupe', function () {
	return 'edit_pages';
} );

function schiesser_nettoyer_reglages( $entree ) {
	$propre  = schiesser_reglages_defaut();
	$entree  = is_array( $entree ) ? $entree : array();
	$textes  = array( 'telephone', 'adresse_1', 'adresse_2', 'mention', 'horaires_note' );
	foreach ( $textes as $cle ) {
		$propre[ $cle ] = isset( $entree[ $cle ] ) ? sanitize_text_field( $entree[ $cle ] ) : '';
	}
	$propre['email']        = isset( $entree['email'] ) ? sanitize_email( $entree['email'] ) : '';
	$propre['presentation'] = isset( $entree['presentation'] ) ? sanitize_textarea_field( $entree['presentation'] ) : '';
	$propre['instagram']    = isset( $entree['instagram'] ) ? esc_url_raw( $entree['instagram'] ) : '';
	$propre['facebook']     = isset( $entree['facebook'] ) ? esc_url_raw( $entree['facebook'] ) : '';

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
	return $propre;
}

function schiesser_nettoyer_heure( $valeur, $defaut ) {
	return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', (string) $valeur ) ? $valeur : $defaut;
}

function schiesser_page_reglages() {
	$r   = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), schiesser_reglages_defaut() );
	$nom = SCHIESSER_OPTION;
	?>
	<div class="wrap schiesser-admin">
		<h1>Réglages de la maison</h1>
		<p class="description">Ces informations apparaissent automatiquement sur tout le site : bandeau du haut, pied de page, horaires, contact.</p>
		<?php settings_errors(); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'schiesser_reglages_groupe' ); ?>

			<h2>Coordonnées</h2>
			<table class="form-table" role="presentation">
				<tr><th><label for="s-tel">Téléphone</label></th>
					<td><input id="s-tel" class="regular-text" type="text" name="<?php echo esc_attr( $nom ); ?>[telephone]" value="<?php echo esc_attr( $r['telephone'] ); ?>"></td></tr>
				<tr><th><label for="s-mail">E-mail</label></th>
					<td><input id="s-mail" class="regular-text" type="email" name="<?php echo esc_attr( $nom ); ?>[email]" value="<?php echo esc_attr( $r['email'] ); ?>"></td></tr>
				<tr><th><label for="s-a1">Adresse (ligne 1)</label></th>
					<td><input id="s-a1" class="regular-text" type="text" name="<?php echo esc_attr( $nom ); ?>[adresse_1]" value="<?php echo esc_attr( $r['adresse_1'] ); ?>"></td></tr>
				<tr><th><label for="s-a2">Adresse (ligne 2)</label></th>
					<td><input id="s-a2" class="regular-text" type="text" name="<?php echo esc_attr( $nom ); ?>[adresse_2]" value="<?php echo esc_attr( $r['adresse_2'] ); ?>"></td></tr>
				<tr><th><label for="s-men">Mention du bandeau</label></th>
					<td><input id="s-men" class="large-text" type="text" name="<?php echo esc_attr( $nom ); ?>[mention]" value="<?php echo esc_attr( $r['mention'] ); ?>">
					<p class="description">Texte affiché à droite du bandeau sombre, tout en haut du site.</p></td></tr>
			</table>

			<h2>Horaires d'ouverture</h2>
			<table class="widefat striped schiesser-horaires" style="max-width:620px">
				<thead><tr><th>Jour</th><th>Ouverture</th><th>Fermeture</th><th>Fermé toute la journée</th></tr></thead>
				<tbody>
				<?php foreach ( schiesser_jours() as $n => $jour ) : $h = $r['horaires'][ $n ]; ?>
					<tr>
						<td><strong><?php echo esc_html( $jour ); ?></strong></td>
						<td><input type="time" name="<?php echo esc_attr( $nom ); ?>[horaires][<?php echo (int) $n; ?>][ouverture]" value="<?php echo esc_attr( $h['ouverture'] ); ?>"></td>
						<td><input type="time" name="<?php echo esc_attr( $nom ); ?>[horaires][<?php echo (int) $n; ?>][fermeture]" value="<?php echo esc_attr( $h['fermeture'] ); ?>"></td>
						<td><label><input type="checkbox" value="1" name="<?php echo esc_attr( $nom ); ?>[horaires][<?php echo (int) $n; ?>][ferme]" <?php checked( ! empty( $h['ferme'] ) ); ?>> Fermé</label></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<table class="form-table" role="presentation">
				<tr><th><label for="s-hn">Remarque sur les horaires</label></th>
					<td><input id="s-hn" class="large-text" type="text" name="<?php echo esc_attr( $nom ); ?>[horaires_note]" value="<?php echo esc_attr( $r['horaires_note'] ); ?>"></td></tr>
			</table>

			<h2>En vitrine aujourd'hui</h2>
			<p class="description">Jusqu'à 4 produits mis en avant dans la bande sous le hero de l'accueil. Laissez une ligne vide pour la masquer.</p>
			<table class="form-table" role="presentation">
				<?php for ( $i = 0; $i < 4; $i++ ) : ?>
				<tr><th><label for="s-v<?php echo (int) $i; ?>">Produit <?php echo (int) $i + 1; ?></label></th>
					<td><input id="s-v<?php echo (int) $i; ?>" class="regular-text" type="text" name="<?php echo esc_attr( $nom ); ?>[vitrine][]" value="<?php echo esc_attr( $r['vitrine'][ $i ] ?? '' ); ?>"></td></tr>
				<?php endfor; ?>
			</table>

			<h2>Pied de page</h2>
			<table class="form-table" role="presentation">
				<tr><th><label for="s-pres">Présentation</label></th>
					<td><textarea id="s-pres" class="large-text" rows="3" name="<?php echo esc_attr( $nom ); ?>[presentation]"><?php echo esc_textarea( $r['presentation'] ); ?></textarea></td></tr>
				<tr><th><label for="s-ig">Lien Instagram</label></th>
					<td><input id="s-ig" class="regular-text" type="url" placeholder="https://www.instagram.com/…" name="<?php echo esc_attr( $nom ); ?>[instagram]" value="<?php echo esc_attr( $r['instagram'] ); ?>"></td></tr>
				<tr><th><label for="s-fb">Lien Facebook</label></th>
					<td><input id="s-fb" class="regular-text" type="url" placeholder="https://www.facebook.com/…" name="<?php echo esc_attr( $nom ); ?>[facebook]" value="<?php echo esc_attr( $r['facebook'] ); ?>"></td></tr>
			</table>

			<?php submit_button( 'Enregistrer les réglages' ); ?>
		</form>
	</div>
	<?php
}

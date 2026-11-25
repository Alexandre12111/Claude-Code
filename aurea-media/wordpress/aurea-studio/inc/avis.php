<?php
/**
 * Avis Google : la note, le nombre d'avis et les derniers avis de la fiche Google Business Profile.
 *
 * Source automatique : l'API Google Places (New), avec une clé API et l'identifiant du lieu
 * (Place ID), saisis dans Aurea Studio → Avis Google. Google renvoie la note moyenne, le nombre
 * total d'avis et au plus 5 avis ; la réponse est gardée 12 heures et la dernière réponse valide
 * est conservée si Google ne répond pas.
 * Avis ajoutés à la main : pour montrer d'autres avis que les 5 renvoyés par Google.
 *
 * La note et le nombre d'avis (aurea_avis_resume()) servent partout sur le site : hero, section
 * Avis, pied de page. Pas de données structurées « Review » ni « AggregateRating » : Google ignore
 * les avis qu'une entreprise publie sur son propre site.
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_AVIS_OPTION', 'aurea_avis_reglages' );
define( 'AUREA_AVIS_CACHE', 'aurea_avis_google' );
define( 'AUREA_AVIS_DERNIER', 'aurea_avis_google_dernier' );

function aurea_avis_reglages() {
	return wp_parse_args( get_option( AUREA_AVIS_OPTION, array() ), array(
		'cle'          => '',
		'lieu'         => '',
		'nombre'       => 6,
		'note_min'     => 4,
		'note_secours' => '4.9',
		'total_secours'=> 39,
		'manuels'      => array(),
	) );
}

/* ------------------------------------------------------------------ */
/* Appel à Google                                                      */
/* ------------------------------------------------------------------ */

/**
 * Données de la fiche Google, depuis le cache si possible.
 *
 * @return array|WP_Error { note, total, avis[], lien, ecrire, date }
 */
function aurea_avis_google( $forcer = false ) {
	$r = aurea_avis_reglages();
	if ( ! $r['cle'] || ! $r['lieu'] ) {
		return new WP_Error( 'aurea_avis_reglages', 'Renseignez la clé API et l’identifiant du lieu.' );
	}
	if ( ! $forcer ) {
		$cache = get_transient( AUREA_AVIS_CACHE );
		if ( is_array( $cache ) ) {
			return $cache;
		}
	}

	$reponse = wp_remote_get( 'https://places.googleapis.com/v1/places/' . rawurlencode( $r['lieu'] ) . '?languageCode=fr&regionCode=FR', array(
		'timeout' => 10,
		'headers' => array(
			'X-Goog-Api-Key'   => $r['cle'],
			'X-Goog-FieldMask' => 'displayName,rating,userRatingCount,googleMapsUri,reviews',
		),
	) );
	if ( is_wp_error( $reponse ) ) {
		return aurea_avis_secours( $reponse );
	}
	$code  = wp_remote_retrieve_response_code( $reponse );
	$corps = json_decode( wp_remote_retrieve_body( $reponse ), true );
	if ( 200 !== $code || ! is_array( $corps ) ) {
		$message = $corps['error']['message'] ?? 'Réponse inattendue de Google (code ' . $code . ').';
		return aurea_avis_secours( new WP_Error( 'aurea_avis_google', $message ) );
	}

	$avis = array();
	foreach ( (array) ( $corps['reviews'] ?? array() ) as $a ) {
		$texte = $a['originalText']['text'] ?? ( $a['text']['text'] ?? '' );
		if ( ! $texte ) {
			continue;
		}
		$avis[] = array(
			'nom'    => $a['authorAttribution']['displayName'] ?? 'Client Google',
			'profil' => $a['authorAttribution']['uri'] ?? '',
			'photo'  => $a['authorAttribution']['photoUri'] ?? '',
			'note'   => (int) ( $a['rating'] ?? 5 ),
			'texte'  => $texte,
			'date'   => isset( $a['publishTime'] ) ? substr( $a['publishTime'], 0, 10 ) : '',
			'quand'  => $a['relativePublishTimeDescription'] ?? '',
			'lien'   => $a['googleMapsUri'] ?? '',
			'source' => 'google',
		);
	}
	$donnees = array(
		'nom'    => $corps['displayName']['text'] ?? '',
		'note'   => isset( $corps['rating'] ) ? round( (float) $corps['rating'], 1 ) : null,
		'total'  => (int) ( $corps['userRatingCount'] ?? 0 ),
		'avis'   => $avis,
		'lien'   => $corps['googleMapsUri'] ?? '',
		'ecrire' => 'https://search.google.com/local/writereview?placeid=' . rawurlencode( $r['lieu'] ),
		'date'   => current_time( 'mysql' ),
	);
	set_transient( AUREA_AVIS_CACHE, $donnees, 12 * HOUR_IN_SECONDS );
	update_option( AUREA_AVIS_DERNIER, $donnees, false );
	return $donnees;
}

/**
 * Si Google ne répond pas : la dernière réponse valide, sinon l'erreur.
 */
function aurea_avis_secours( WP_Error $erreur ) {
	$dernier = get_option( AUREA_AVIS_DERNIER );
	if ( is_array( $dernier ) ) {
		$dernier['erreur'] = $erreur->get_error_message();
		set_transient( AUREA_AVIS_CACHE, $dernier, HOUR_IN_SECONDS ); // Nouvel essai dans une heure.
		return $dernier;
	}
	return $erreur;
}

/* Rafraîchissement automatique deux fois par jour, pour que les visiteurs n'attendent jamais Google. */
add_action( 'aurea_avis_rafraichir', function () {
	aurea_avis_google( true );
} );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'aurea_avis_rafraichir' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', 'aurea_avis_rafraichir' );
	}
} );

/* ------------------------------------------------------------------ */
/* Lecture pour le thème                                               */
/* ------------------------------------------------------------------ */

/**
 * Note et nombre d'avis, pour le hero, le pied de page, les pages services…
 *
 * @return array { note (float), note_txt ("4,9"), total (int), lien, ecrire }
 */
function aurea_avis_resume() {
	$r = aurea_avis_reglages();
	$g = aurea_avis_google();
	$g = is_wp_error( $g ) ? array() : $g;
	$note  = isset( $g['note'] ) ? (float) $g['note'] : (float) str_replace( ',', '.', $r['note_secours'] );
	$total = ! empty( $g['total'] ) ? (int) $g['total'] : (int) $r['total_secours'];
	return array(
		'note'     => $note,
		'note_txt' => number_format_i18n( $note, 1 ),
		'total'    => $total,
		'lien'     => $g['lien'] ?? '',
		'ecrire'   => $g['ecrire'] ?? '',
	);
}

/**
 * Avis à afficher : ceux de Google (note minimale respectée), puis ceux ajoutés à la main.
 */
function aurea_avis( $nombre = null ) {
	$r      = aurea_avis_reglages();
	$nombre = $nombre ?? (int) $r['nombre'];
	$g      = aurea_avis_google();
	$avis   = is_wp_error( $g ) ? array() : array_values( array_filter( $g['avis'], function ( $a ) use ( $r ) {
		return $a['note'] >= (int) $r['note_min'];
	} ) );
	foreach ( (array) $r['manuels'] as $m ) {
		if ( ! empty( $m['texte'] ) ) {
			$avis[] = array( 'nom' => $m['nom'], 'profil' => '', 'photo' => '', 'note' => (int) $m['note'], 'texte' => $m['texte'], 'date' => $m['date'], 'quand' => aurea_avis_mois( $m['date'] ), 'lien' => '', 'source' => 'manuel' );
		}
	}
	return array_slice( $avis, 0, max( 1, $nombre ) );
}

function aurea_avis_mois( $date ) {
	$t = $date ? strtotime( $date ) : false;
	return $t ? ucfirst( date_i18n( 'F Y', $t ) ) : '';
}

/**
 * Bloc d'avis prêt à afficher. Le thème Aurea déclare add_theme_support( 'aurea-avis' ) et
 * fournit ses styles ; sinon le plugin charge une feuille minimale.
 */
function aurea_avis_html( $args = array() ) {
	$args = wp_parse_args( $args, array( 'nombre' => null, 'classe' => '' ) );
	$avis = aurea_avis( $args['nombre'] );
	if ( ! $avis ) {
		return '';
	}
	if ( ! current_theme_supports( 'aurea-avis' ) ) {
		wp_enqueue_style( 'aurea-avis', AUREA_STUDIO_URL . 'assets/css/avis.css', array(), AUREA_STUDIO_VERSION );
	}
	$resume = aurea_avis_resume();
	$g      = '<svg class="aurea-avis-g" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.4a4.6 4.6 0 0 1-2 3v2.5h3.2c1.9-1.7 3-4.3 3-7.3Z"/><path fill="#34A853" d="M12 22c2.7 0 5-.9 6.6-2.4l-3.2-2.5c-.9.6-2 1-3.4 1-2.6 0-4.8-1.8-5.6-4.1H3.1v2.6A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.4 14c-.2-.6-.3-1.3-.3-2s.1-1.4.3-2V7.4H3.1A10 10 0 0 0 2 12c0 1.6.4 3.2 1.1 4.6L6.4 14Z"/><path fill="#EA4335" d="M12 6c1.5 0 2.8.5 3.8 1.5l2.9-2.9A10 10 0 0 0 3.1 7.4L6.4 10C7.2 7.7 9.4 6 12 6Z"/></svg>';
	$etoiles = function ( $n ) {
		return '<span class="aurea-avis-etoiles" role="img" aria-label="' . esc_attr( $n . ' sur 5' ) . '">' . str_repeat( '★', $n ) . '<span>' . str_repeat( '★', 5 - $n ) . '</span></span>';
	};

	$html  = '<div class="aurea-avis ' . esc_attr( $args['classe'] ) . '">';
	$html .= '<div class="aurea-avis-score">' . $g . '<b>' . esc_html( $resume['note_txt'] ) . '</b><div>' . $etoiles( (int) round( $resume['note'] ) ) . '<span>' . esc_html( sprintf( '%d avis Google', $resume['total'] ) ) . '</span></div>';
	if ( $resume['lien'] ) {
		$html .= '<a class="aurea-avis-tous" href="' . esc_url( $resume['lien'] ) . '" target="_blank" rel="noopener">Voir tous les avis</a>';
	}
	$html .= '</div><div class="aurea-avis-liste">';
	foreach ( $avis as $a ) {
		$initiale = mb_strtoupper( mb_substr( $a['nom'], 0, 1 ) );
		$avatar   = $a['photo']
			? '<img src="' . esc_url( $a['photo'] ) . '" alt="" width="42" height="42" loading="lazy" referrerpolicy="no-referrer">'
			: '<i aria-hidden="true">' . esc_html( $initiale ) . '</i>';
		$nom = $a['profil'] ? '<a href="' . esc_url( $a['profil'] ) . '" target="_blank" rel="noopener nofollow">' . esc_html( $a['nom'] ) . '</a>' : esc_html( $a['nom'] );
		$html .= '<blockquote class="aurea-avis-carte"><div class="aurea-avis-haut">' . $etoiles( $a['note'] ) . ( 'google' === $a['source'] ? $g : '' ) . '</div>';
		$html .= '<p>' . esc_html( wp_trim_words( $a['texte'], 60, '…' ) ) . '</p>';
		$html .= '<footer>' . $avatar . '<div>' . $nom . '<span>' . esc_html( $a['quand'] ) . '</span></div></footer></blockquote>';
	}
	$html .= '</div>';
	if ( $resume['ecrire'] ) {
		$html .= '<p class="aurea-avis-ecrire"><a href="' . esc_url( $resume['ecrire'] ) . '" target="_blank" rel="noopener">Vous êtes client ? Laissez un avis sur Google</a></p>';
	}
	return $html . '</div>';
}

add_shortcode( 'aurea_avis', function ( $atts ) {
	$atts = shortcode_atts( array( 'nombre' => null ), $atts, 'aurea_avis' );
	return aurea_avis_html( array( 'nombre' => $atts['nombre'] ? (int) $atts['nombre'] : null ) );
} );

/* ------------------------------------------------------------------ */
/* Écran de réglages                                                   */
/* ------------------------------------------------------------------ */

add_action( 'admin_enqueue_scripts', function () {
	if ( 'aurea-avis' !== ( $_GET['page'] ?? '' ) ) {
		return;
	}
	wp_enqueue_style( 'aurea-studio', AUREA_STUDIO_URL . 'assets/admin/studio.css', array(), AUREA_STUDIO_VERSION );
	wp_enqueue_style( 'aurea-avis', AUREA_STUDIO_URL . 'assets/css/avis.css', array(), AUREA_STUDIO_VERSION );
	wp_enqueue_script( 'aurea-avis-admin', AUREA_STUDIO_URL . 'assets/admin/avis.js', array(), AUREA_STUDIO_VERSION, true );
} );

add_action( 'admin_post_aurea_avis', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Vous n’avez pas le droit de modifier ces réglages.' );
	}
	check_admin_referer( 'aurea_avis' );
	$r = aurea_avis_reglages();

	if ( isset( $_POST['enregistrer'] ) ) {
		$cle = trim( sanitize_text_field( wp_unslash( $_POST['cle'] ?? '' ) ) );
		if ( '' !== $cle && false === strpos( $cle, '•' ) ) {
			$r['cle'] = $cle; // Un champ resté masqué garde l'ancienne clé.
		}
		if ( ! empty( $_POST['effacer_cle'] ) ) {
			$r['cle'] = '';
		}
		$r['lieu']          = trim( sanitize_text_field( wp_unslash( $_POST['lieu'] ?? '' ) ) );
		$r['nombre']        = min( 12, max( 1, (int) ( $_POST['nombre'] ?? 6 ) ) );
		$r['note_min']      = min( 5, max( 1, (int) ( $_POST['note_min'] ?? 4 ) ) );
		$r['note_secours']  = str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['note_secours'] ?? '4.9' ) ) );
		$r['total_secours'] = max( 0, (int) ( $_POST['total_secours'] ?? 0 ) );
		$r['manuels']       = array();
		$noms = (array) ( $_POST['m_nom'] ?? array() );
		foreach ( $noms as $i => $nom ) {
			$texte = sanitize_textarea_field( wp_unslash( $_POST['m_texte'][ $i ] ?? '' ) );
			if ( '' === trim( $texte ) ) {
				continue;
			}
			$r['manuels'][] = array(
				'nom'   => sanitize_text_field( wp_unslash( $nom ) ) ?: 'Client',
				'note'  => min( 5, max( 1, (int) ( $_POST['m_note'][ $i ] ?? 5 ) ) ),
				'texte' => $texte,
				'date'  => sanitize_text_field( wp_unslash( $_POST['m_date'][ $i ] ?? '' ) ),
			);
		}
		update_option( AUREA_AVIS_OPTION, $r, false );
	}
	delete_transient( AUREA_AVIS_CACHE );
	$test = aurea_avis_google( true );
	$etat = is_wp_error( $test ) ? 'erreur' : ( empty( $test['erreur'] ) ? 'ok' : 'ancien' );
	wp_safe_redirect( add_query_arg( array( 'page' => 'aurea-avis', 'etat' => $etat ), admin_url( 'admin.php' ) ) );
	exit;
} );

function aurea_avis_ecran() {
	$r    = aurea_avis_reglages();
	$g    = aurea_avis_google();
	$etat = sanitize_key( $_GET['etat'] ?? '' );
	echo '<div class="wrap aurea-studio">';
	aurea_studio_entete( 'Avis Google', 'Votre note et vos derniers avis Google, affichés sur le site et mis à jour automatiquement deux fois par jour.' );

	if ( is_wp_error( $g ) ) {
		$classe = 'is-attente';
		$texte  = $g->get_error_message();
	} elseif ( ! empty( $g['erreur'] ) ) {
		$classe = 'is-attente';
		$texte  = 'Google ne répond pas (' . $g['erreur'] . '). Le site affiche les avis récupérés le ' . mysql2date( 'j F Y à H:i', $g['date'] ) . '.';
	} else {
		$classe = 'is-ok';
		$texte  = 'Connecté à « ' . $g['nom'] . ' » : ' . number_format_i18n( (float) $g['note'], 1 ) . ' sur 5, ' . $g['total'] . ' avis. Dernière mise à jour le ' . mysql2date( 'j F Y à H:i', $g['date'] ) . '.';
	}
	if ( 'erreur' === $etat && is_wp_error( $g ) ) {
		$classe = 'is-erreur';
	}
	echo '<div class="aurea-statut ' . esc_attr( $classe ) . '"><span></span><p>' . esc_html( $texte ) . '</p></div>';
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="aurea-form">
		<input type="hidden" name="action" value="aurea_avis">
		<?php wp_nonce_field( 'aurea_avis' ); ?>

		<section class="aurea-panneau">
			<h2>Connexion à votre fiche Google</h2>
			<div class="aurea-champs-2">
				<div class="aurea-champ">
					<label for="aurea-cle">Clé API Google</label>
					<input type="password" id="aurea-cle" name="cle" autocomplete="off" value="<?php echo $r['cle'] ? esc_attr( str_repeat( '•', 12 ) . substr( $r['cle'], -4 ) ) : ''; ?>" placeholder="AIza…">
					<small>Créée dans Google Cloud Console, avec l’API « Places API (New) » activée. Limitez la clé à cette API.</small>
					<?php if ( $r['cle'] ) : ?><label class="aurea-case"><input type="checkbox" name="effacer_cle" value="1"> Effacer la clé</label><?php endif; ?>
				</div>
				<div class="aurea-champ">
					<label for="aurea-lieu">Identifiant du lieu (Place ID)</label>
					<input type="text" id="aurea-lieu" name="lieu" value="<?php echo esc_attr( $r['lieu'] ); ?>" placeholder="ChIJ…">
					<small>Trouvez le vôtre avec l’outil <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener">Place ID Finder</a> de Google, en cherchant « Aurea Media ».</small>
				</div>
			</div>
		</section>

		<section class="aurea-panneau">
			<h2>Affichage</h2>
			<div class="aurea-champs-4">
				<label>Avis affichés<input type="number" name="nombre" min="1" max="12" value="<?php echo (int) $r['nombre']; ?>"></label>
				<label>Note minimale<select name="note_min"><?php for ( $n = 5; $n >= 1; $n-- ) { echo '<option value="' . $n . '"' . selected( (int) $r['note_min'], $n, false ) . '>' . $n . ' étoile' . ( $n > 1 ? 's' : '' ) . ' et plus</option>'; } ?></select></label>
				<label>Note si Google est indisponible<input type="text" name="note_secours" value="<?php echo esc_attr( $r['note_secours'] ); ?>"></label>
				<label>Nombre d’avis si Google est indisponible<input type="number" name="total_secours" min="0" value="<?php echo (int) $r['total_secours']; ?>"></label>
			</div>
			<p class="aurea-note">Google ne transmet que vos 5 avis les plus pertinents. Pour en montrer d’autres, ajoutez les ci-dessous.</p>
		</section>

		<section class="aurea-panneau">
			<h2>Avis ajoutés à la main</h2>
			<div id="aurea-manuels">
				<?php
				$manuels = $r['manuels'] ?: array();
				$manuels[] = array( 'nom' => '', 'note' => 5, 'texte' => '', 'date' => '' );
				foreach ( $manuels as $m ) :
					?>
					<div class="aurea-manuel">
						<label>Nom<input type="text" name="m_nom[]" value="<?php echo esc_attr( $m['nom'] ); ?>" placeholder="Prénom N."></label>
						<label>Note<select name="m_note[]"><?php for ( $n = 5; $n >= 1; $n-- ) { echo '<option' . selected( (int) $m['note'], $n, false ) . '>' . $n . '</option>'; } ?></select></label>
						<label>Date<input type="month" name="m_date[]" value="<?php echo esc_attr( $m['date'] ); ?>"></label>
						<label class="is-large">Avis<textarea name="m_texte[]" rows="2" placeholder="Texte de l’avis, copié depuis Google"><?php echo esc_textarea( $m['texte'] ); ?></textarea></label>
						<button type="button" class="aurea-lien is-danger" data-retirer>Retirer</button>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="aurea-btn" id="aurea-manuel-ajouter">Ajouter un avis</button>
		</section>

		<div class="aurea-barre is-bas">
			<button type="submit" name="actualiser" value="1" class="aurea-btn">Actualiser depuis Google</button>
			<button type="submit" name="enregistrer" value="1" class="aurea-btn is-noir">Enregistrer et tester</button>
		</div>
	</form>

	<section class="aurea-panneau is-sombre">
		<h2>Aperçu sur le site</h2>
		<?php echo aurea_avis_html() ?: '<p class="aurea-note">Aucun avis à afficher pour l’instant.</p>'; ?>
	</section>
	<?php
	echo '</div>';
}

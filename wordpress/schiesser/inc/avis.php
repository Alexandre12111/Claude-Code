<?php
/**
 * Avis clients : bloc « Avis clients » (étoiles, note moyenne, quelques commentaires).
 *
 * Deux sources, au choix :
 * - automatique : les avis de la fiche Google, avec une clé Google (Places API) et
 *   l'identifiant du lieu, saisis dans Réglages maison → Fiche Google et SEO.
 *   Google renvoie la note, le nombre d'avis et jusqu'à 5 avis récents ;
 *   la réponse est gardée 12 heures pour ne pas interroger Google à chaque visite.
 * - manuelle : menu « Avis clients », un avis par fiche (nom, note, texte, date),
 *   plus la note moyenne et le nombre d'avis saisis dans Réglages maison.
 *
 * Pas de données structurées « Review » : Google ignore les avis qu'une entreprise
 * publie sur son propre site (et peut le considérer comme trompeur).
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_AVIS', 'schiesser_avis' );

/* ------------------------------------------------------------------ */
/* Avis saisis à la main                                               */
/* ------------------------------------------------------------------ */

add_action( 'init', function () {
	register_post_type( SCHIESSER_AVIS, array(
		'labels'          => array(
			'name'               => 'Avis clients',
			'singular_name'      => 'Avis client',
			'menu_name'          => 'Avis clients',
			'add_new'            => 'Ajouter un avis',
			'add_new_item'       => 'Ajouter un avis client',
			'edit_item'          => 'Modifier l’avis',
			'new_item'           => 'Nouvel avis',
			'search_items'       => 'Rechercher un avis',
			'not_found'          => 'Aucun avis pour l’instant.',
			'not_found_in_trash' => 'Aucun avis dans la corbeille.',
			'all_items'          => 'Tous les avis',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_position'   => 27,
		'menu_icon'       => 'dashicons-star-filled',
		'supports'        => array( 'title', 'page-attributes' ),
		'capability_type' => 'page',
		'map_meta_cap'    => true,
	) );
} );

add_filter( 'enter_title_here', function ( $texte, $post ) {
	return SCHIESSER_AVIS === $post->post_type ? 'Nom de la personne (ex. Marie L.)' : $texte;
}, 10, 2 );

add_action( 'add_meta_boxes_' . SCHIESSER_AVIS, function () {
	add_meta_box( 'schiesser-avis', 'L’avis', 'schiesser_avis_boite', SCHIESSER_AVIS, 'normal', 'high' );
} );

function schiesser_avis_champs( $id ) {
	return array(
		'note'   => (int) get_post_meta( $id, '_avis_note', true ) ?: 5,
		'texte'  => (string) get_post_meta( $id, '_avis_texte', true ),
		'date'   => (string) get_post_meta( $id, '_avis_date', true ),
		'source' => (string) get_post_meta( $id, '_avis_source', true ) ?: 'Google',
		'lien'   => (string) get_post_meta( $id, '_avis_lien', true ),
	);
}

function schiesser_avis_boite( $post ) {
	$c = schiesser_avis_champs( $post->ID );
	wp_nonce_field( 'schiesser_avis', 'schiesser_avis_nonce' );
	?>
	<p><label for="avis-note"><strong>Note</strong></label><br>
		<select id="avis-note" name="avis_note">
			<?php for ( $n = 5; $n >= 1; $n-- ) : ?>
				<option value="<?php echo (int) $n; ?>" <?php selected( $c['note'], $n ); ?>><?php echo esc_html( str_repeat( '★', $n ) . str_repeat( '☆', 5 - $n ) . ' (' . $n . ' sur 5)' ); ?></option>
			<?php endfor; ?>
		</select></p>
	<p><label for="avis-texte"><strong>Texte de l’avis</strong></label><br>
		<textarea id="avis-texte" name="avis_texte" rows="6" class="large-text" placeholder="Copiez ici le commentaire, tel qu’il a été publié."><?php echo esc_textarea( $c['texte'] ); ?></textarea></p>
	<p><label for="avis-date"><strong>Date</strong></label><br>
		<input id="avis-date" type="text" name="avis_date" value="<?php echo esc_attr( $c['date'] ); ?>" placeholder="Ex. mars 2026" class="regular-text"></p>
	<p><label for="avis-source"><strong>Publié sur</strong></label><br>
		<select id="avis-source" name="avis_source">
			<?php foreach ( array( 'Google', 'Tripadvisor', 'Facebook', 'Livre d’or' ) as $s ) : ?>
				<option <?php selected( $c['source'], $s ); ?>><?php echo esc_html( $s ); ?></option>
			<?php endforeach; ?>
		</select></p>
	<p><label for="avis-lien"><strong>Lien vers l’avis</strong> (facultatif)</label><br>
		<input id="avis-lien" type="url" name="avis_lien" value="<?php echo esc_attr( $c['lien'] ); ?>" class="large-text" placeholder="https://…"></p>
	<p class="description">Ne publiez que de vrais avis, avec l’accord de leur auteur si possible, et sans les modifier. L’ordre d’affichage se règle avec « Ordre » (colonne de droite), du plus petit au plus grand.</p>
	<?php
}

add_action( 'save_post_' . SCHIESSER_AVIS, function ( $id ) {
	if ( ! isset( $_POST['schiesser_avis_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['schiesser_avis_nonce'] ), 'schiesser_avis' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_autosave( $id ) ) {
		return;
	}
	update_post_meta( $id, '_avis_note', max( 1, min( 5, absint( $_POST['avis_note'] ?? 5 ) ) ) );
	update_post_meta( $id, '_avis_texte', sanitize_textarea_field( wp_unslash( $_POST['avis_texte'] ?? '' ) ) );
	update_post_meta( $id, '_avis_date', sanitize_text_field( wp_unslash( $_POST['avis_date'] ?? '' ) ) );
	$source = sanitize_text_field( wp_unslash( $_POST['avis_source'] ?? 'Google' ) );
	update_post_meta( $id, '_avis_source', in_array( $source, array( 'Google', 'Tripadvisor', 'Facebook', 'Livre d’or' ), true ) ? $source : 'Google' );
	update_post_meta( $id, '_avis_lien', esc_url_raw( wp_unslash( $_POST['avis_lien'] ?? '' ) ) );
} );

add_filter( 'manage_' . SCHIESSER_AVIS . '_posts_columns', function ( $cols ) {
	return array(
		'cb'         => $cols['cb'],
		'title'      => 'Auteur',
		'avis_note'  => 'Note',
		'avis_texte' => 'Avis',
		'date'       => $cols['date'],
	);
} );
add_action( 'manage_' . SCHIESSER_AVIS . '_posts_custom_column', function ( $col, $id ) {
	$c = schiesser_avis_champs( $id );
	if ( 'avis_note' === $col ) {
		echo '<span style="color:#b8913a;letter-spacing:1px" aria-label="' . esc_attr( $c['note'] . ' sur 5' ) . '">' . esc_html( str_repeat( '★', $c['note'] ) . str_repeat( '☆', 5 - $c['note'] ) ) . '</span>';
	} elseif ( 'avis_texte' === $col ) {
		echo esc_html( wp_trim_words( $c['texte'], 22 ) );
	}
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* Avis Google (Places API)                                            */
/* ------------------------------------------------------------------ */

/**
 * Avis de la fiche Google, gardés 12 heures.
 *
 * @param bool $forcer Interroger Google même si une réponse récente est gardée.
 * @return array|WP_Error|null null si la clé ou le lieu ne sont pas saisis.
 */
function schiesser_avis_google( $forcer = false ) {
	$cle  = trim( (string) schiesser_reglage( 'avis_cle' ) );
	$lieu = trim( (string) schiesser_reglage( 'avis_lieu' ) );
	if ( '' === $cle || '' === $lieu ) {
		return null;
	}
	$langue    = substr( get_locale(), 0, 2 ) ?: 'fr';
	$transient = 'schiesser_avis_' . md5( $cle . '|' . $lieu . '|' . $langue );
	$garde     = $forcer ? false : get_transient( $transient );
	if ( false !== $garde ) {
		return is_array( $garde ) && isset( $garde['erreur'] ) ? new WP_Error( 'schiesser_avis', $garde['erreur'] ) : $garde;
	}

	$reponse = wp_remote_get( 'https://places.googleapis.com/v1/places/' . rawurlencode( $lieu ) . '?languageCode=' . rawurlencode( $langue ), array(
		'timeout' => 8,
		'headers' => array(
			'X-Goog-Api-Key'   => $cle,
			'X-Goog-FieldMask' => 'rating,userRatingCount,reviews,googleMapsUri,displayName',
		),
	) );
	$code   = is_wp_error( $reponse ) ? 0 : (int) wp_remote_retrieve_response_code( $reponse );
	$corps  = is_wp_error( $reponse ) ? null : json_decode( wp_remote_retrieve_body( $reponse ), true );
	if ( 200 !== $code || ! is_array( $corps ) ) {
		$message = is_wp_error( $reponse ) ? $reponse->get_error_message() : ( $corps['error']['message'] ?? 'réponse inattendue (code ' . $code . ')' );
		set_transient( $transient, array( 'erreur' => $message ), 30 * MINUTE_IN_SECONDS ); // on réessaie dans 30 minutes
		return new WP_Error( 'schiesser_avis', $message );
	}

	$avis = array();
	foreach ( (array) ( $corps['reviews'] ?? array() ) as $a ) {
		$texte = $a['text']['text'] ?? ( $a['originalText']['text'] ?? '' );
		$avis[] = array(
			'auteur' => sanitize_text_field( $a['authorAttribution']['displayName'] ?? '' ),
			'note'   => max( 1, min( 5, (int) ( $a['rating'] ?? 5 ) ) ),
			'texte'  => sanitize_textarea_field( $texte ),
			'date'   => sanitize_text_field( $a['relativePublishTimeDescription'] ?? '' ),
			'lien'   => esc_url_raw( $a['googleMapsUri'] ?? ( $a['authorAttribution']['uri'] ?? '' ) ),
			'source' => 'Google',
		);
	}
	$donnees = array(
		'note'   => isset( $corps['rating'] ) ? round( (float) $corps['rating'], 1 ) : null,
		'nombre' => isset( $corps['userRatingCount'] ) ? (int) $corps['userRatingCount'] : null,
		'lien'   => esc_url_raw( $corps['googleMapsUri'] ?? '' ),
		'avis'   => $avis,
		'source' => 'google',
		'le'     => time(),
	);
	set_transient( $transient, $donnees, 12 * HOUR_IN_SECONDS );
	return $donnees;
}

/**
 * Avis à afficher : Google si la connexion est réglée et répond, sinon les avis saisis à la main.
 *
 * @return array { note, nombre, lien, ecrire, avis[], source }
 */
function schiesser_avis_donnees() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}
	$lieu   = trim( (string) schiesser_reglage( 'avis_lieu' ) );
	$fiche  = schiesser_reglage( 'lien_maps' );
	$ecrire = $lieu ? 'https://search.google.com/local/writereview?placeid=' . rawurlencode( $lieu ) : '';

	$google = schiesser_avis_google();
	if ( is_array( $google ) && ( $google['avis'] || $google['note'] ) ) {
		$d = $google + array( 'ecrire' => $ecrire );
		$d['lien'] = $d['lien'] ?: $fiche;
		return $d;
	}

	$avis = array();
	foreach ( get_posts( array(
		'post_type'   => SCHIESSER_AVIS,
		'post_status' => 'publish',
		'numberposts' => 30,
		'orderby'     => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	) ) as $p ) {
		$c = schiesser_avis_champs( $p->ID );
		if ( '' === trim( $c['texte'] ) ) {
			continue;
		}
		$avis[] = array(
			'auteur' => get_the_title( $p ),
			'note'   => $c['note'],
			'texte'  => $c['texte'],
			'date'   => $c['date'],
			'lien'   => $c['lien'],
			'source' => $c['source'],
		);
	}
	$note = schiesser_reglage( 'avis_note' );
	$d    = array(
		'note'   => '' !== $note ? (float) $note : null,
		'nombre' => schiesser_reglage( 'avis_nombre' ) ? (int) schiesser_reglage( 'avis_nombre' ) : null,
		'lien'   => $fiche,
		'ecrire' => $ecrire,
		'avis'   => $avis,
		'source' => 'manuel',
	);
	return $d;
}

/* ------------------------------------------------------------------ */
/* Rendu du bloc « Avis clients »                                      */
/* ------------------------------------------------------------------ */

/** Étoiles : une rangée remplie selon la note (4,5 → quatre étoiles et demie). */
function schiesser_avis_etoiles( $note, $classe = '' ) {
	$note = max( 0, min( 5, (float) $note ) );
	$lue  = str_replace( '.', ',', (string) round( $note, 1 ) );
	return '<span class="avis-etoiles ' . esc_attr( $classe ) . '" style="--note:' . round( $note / 5 * 100, 1 ) . '%" role="img" aria-label="' . esc_attr( $lue . ' sur 5' ) . '"></span>';
}

/** Coupe un texte trop long sur un mot entier. */
function schiesser_avis_couper( $texte, $max ) {
	$texte = trim( preg_replace( '/\s+/u', ' ', (string) $texte ) );
	if ( $max < 60 || mb_strlen( $texte ) <= $max ) {
		return array( $texte, false );
	}
	$court = mb_substr( $texte, 0, $max );
	$court = preg_replace( '/\s+\S*$/u', '', $court );
	return array( rtrim( $court, " ,;:.–-" ) . '…', true );
}

function schiesser_mq_rendu_avis( $a ) {
	$d        = schiesser_avis_donnees();
	$note_min = max( 1, min( 5, (int) ( $a['noteMin'] ?? 4 ) ) );
	$nombre   = max( 1, min( 12, (int) ( $a['nombre'] ?? 3 ) ) );
	$liste    = array_slice( array_values( array_filter( $d['avis'], function ( $x ) use ( $note_min ) {
		return $x['note'] >= $note_min && '' !== trim( $x['texte'] );
	} ) ), 0, $nombre );

	if ( ! $liste && ! $d['note'] ) {
		return ! empty( $a['apercu'] ) ? '<p class="sch-ed-note">Aucun avis à afficher pour l’instant. Ajoutez des avis dans le menu « Avis clients », ou reliez la fiche Google dans Réglages maison → Fiche Google et SEO.</p>' : '';
	}

	$google = 'google' === $d['source'];
	$html   = '<div class="avis rv">';

	if ( ! empty( $a['resume'] ) && $d['note'] ) {
		$html .= '<div class="avis-resume">'
			. '<p class="avis-moyenne"><span class="avis-chiffre">' . esc_html( number_format_i18n( $d['note'], 1 ) ) . '</span>' . schiesser_avis_etoiles( $d['note'], 'avis-etoiles--grand' ) . '</p>'
			. ( $d['nombre'] ? '<p class="avis-nombre">' . esc_html( number_format_i18n( $d['nombre'] ) . ' avis Google' ) . '</p>' : '' )
			. '<div class="avis-actions">'
			. schiesser_mq_bouton( $a['lienTexte'] ?? '', $d['lien'], 'sombre' === ( $a['fond'] ?? '' ) ? 'btn-ghost' : 'btn-line' )
			. ( $d['ecrire'] ? schiesser_mq_bouton( $a['avisTexte'] ?? '', $d['ecrire'], 'btn-kir' ) : '' )
			. '</div></div>';
	}

	if ( $liste ) {
		$html .= '<ul class="avis-liste avis-liste--' . count( $liste ) . '">';
		foreach ( $liste as $x ) {
			list( $texte, $coupe ) = schiesser_avis_couper( $x['texte'], (int) ( $a['longueur'] ?? 260 ) );
			$initiale = mb_strtoupper( mb_substr( trim( $x['auteur'] ) ?: '?', 0, 1 ) );
			$meta     = array_filter( array( $x['date'], $x['source'] ) );
			$html    .= '<li class="avis-carte">'
				. schiesser_avis_etoiles( $x['note'] )
				. '<blockquote class="avis-texte"><p>' . esc_html( $texte ) . '</p></blockquote>'
				. ( $coupe && $x['lien'] ? '<a class="avis-suite" href="' . esc_url( $x['lien'] ) . '" target="_blank" rel="noopener">Lire la suite<span class="screen-reader-text"> de l’avis de ' . esc_html( $x['auteur'] ) . '</span></a>' : '' )
				. '<p class="avis-auteur"><span class="avis-initiale" aria-hidden="true">' . esc_html( $initiale ) . '</span>'
				. '<span><strong>' . esc_html( $x['auteur'] ) . '</strong>' . ( $meta ? '<span class="avis-date">' . esc_html( implode( ' · ', $meta ) ) . '</span>' : '' ) . '</span></p>'
				. '</li>';
		}
		$html .= '</ul>';
	}

	if ( $google ) {
		$html .= '<p class="avis-mention">Avis publiés sur Google, affichés tels quels.</p>';
	}
	return schiesser_mq_section( $a, $html . '</div>', 'sec-avis' );
}

/* ------------------------------------------------------------------ */
/* Réglages maison → Fiche Google et SEO : carte « Avis Google »       */
/* ------------------------------------------------------------------ */

function schiesser_carte_avis_reglages( $o, $r ) {
	schiesser_carte_debut( 'Avis Google', 'Pour le bloc « Avis clients » : note moyenne, étoiles et quelques commentaires. Deux possibilités : relier la fiche Google (les avis se mettent à jour tout seuls), ou saisir vous-même quelques avis dans le menu <a href="' . esc_url( admin_url( 'edit.php?post_type=' . SCHIESSER_AVIS ) ) . '">Avis clients</a>.', 'star-filled' );

	// État de la connexion Google.
	if ( $r['avis_cle'] && $r['avis_lieu'] ) {
		$g = schiesser_avis_google( isset( $_GET['avis_test'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( is_wp_error( $g ) ) {
			echo '<p class="s-etat s-etat--alerte"><span class="dashicons dashicons-warning" aria-hidden="true"></span> <strong>Google ne répond pas comme prévu :</strong> ' . esc_html( $g->get_error_message() ) . '. En attendant, le site affiche les avis saisis à la main.</p>';
		} elseif ( is_array( $g ) ) {
			echo '<p class="s-etat s-etat--ok"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span> <strong>Fiche Google reliée</strong> : '
				. esc_html( ( $g['note'] ? number_format_i18n( $g['note'], 1 ) . ' ★' : 'pas encore de note' ) . ( $g['nombre'] ? ', ' . number_format_i18n( $g['nombre'] ) . ' avis' : '' ) . ', ' . count( $g['avis'] ) . ' commentaires récupérés.' )
				. ' Mise à jour automatique toutes les 12 heures.</p>';
		}
		echo '<p><a class="button" href="' . esc_url( add_query_arg( 'avis_test', 1 ) . '#etablissement' ) . '">Actualiser maintenant</a></p>';
	} else {
		echo '<p class="s-etat"><span class="dashicons dashicons-info" aria-hidden="true"></span> Fiche Google non reliée : le site affiche les avis du menu « Avis clients » et la note saisie ci-dessous.</p>';
	}

	echo '<div class="s-grille">';
	if ( current_user_can( 'manage_options' ) ) {
		schiesser_champ( $o, 'avis_cle', $r['avis_cle'], array(
			'libelle'   => 'Clé API Google (Places API)',
			'type'      => 'password',
			'attributs' => 'autocomplete="off" spellcheck="false"',
			'aide'      => 'Dans la <a href="https://console.cloud.google.com/apis/library/places.googleapis.com" target="_blank" rel="noopener">console Google Cloud</a> : activer « Places API (New) », puis Identifiants → Créer une clé, et la limiter à cette API. L’usage d’un petit site reste en principe dans le quota gratuit de Google.',
			'classe'    => 's-large',
		) );
	}
	schiesser_champ( $o, 'avis_lieu', $r['avis_lieu'], array(
		'libelle'     => 'Identifiant du lieu Google (Place ID)',
		'placeholder' => 'ChIJ…',
		'aide'        => 'À trouver avec l’outil de Google <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener">Place ID Finder</a> : tapez « Confiserie Schiesser Basel » et copiez l’identifiant. Il sert aussi au bouton « Laisser un avis ».',
		'classe'      => 's-large',
	) );
	schiesser_champ( $o, 'avis_note', $r['avis_note'], array( 'libelle' => 'Note moyenne (sans clé Google)', 'placeholder' => '4,7', 'classe' => 's-court', 'aide' => 'Recopiez la note de la fiche Google, sur 5.' ) );
	schiesser_champ( $o, 'avis_nombre', $r['avis_nombre'], array( 'libelle' => 'Nombre d’avis', 'type' => 'number', 'placeholder' => '320', 'classe' => 's-court', 'attributs' => 'min="0"' ) );
	echo '</div>';
	schiesser_carte_fin();
}

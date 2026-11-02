<?php
/**
 * Demandes reçues : les formulaires du site (contact, partenariat, workshop) arrivent ici.
 *
 * Chaque envoi est d'abord enregistré dans WordPress (DualTech → Demandes reçues), puis un e-mail est
 * tenté vers l'adresse des Réglages. Si l'e-mail ne part pas (serveur sans envoi d'e-mails configuré),
 * la demande n'est pas perdue : elle reste consultable, avec la mention « e-mail non envoyé ».
 *
 * Protection contre les robots sans case à cocher : champ piège invisible, délai minimal de saisie,
 * 5 envois par heure au plus depuis une même adresse IP (l'adresse n'est pas conservée).
 */

defined( 'ABSPATH' ) || exit;

/** Formulaires du site : champs acceptés (nom => libellé) et champs obligatoires. */
function dts_formulaires() {
	return array(
		'contact'     => array(
			'nom'          => 'Formulaire de contact',
			'champs'       => array( 'nom' => 'Nom et prénom', 'email' => 'Adresse e-mail', 'organisation' => 'Organisation', 'sujet' => 'Sujet', 'message' => 'Message' ),
			'obligatoires' => array( 'nom', 'email', 'sujet', 'message' ),
		),
		'partenariat' => array(
			'nom'          => 'Demande de partenariat',
			'champs'       => array( 'nom' => 'Nom et prénom', 'organisation' => 'Organisation', 'email' => 'Adresse e-mail professionnelle', 'telephone' => 'Téléphone', 'nature' => 'Nature du partenariat envisagé', 'message' => 'Présentation de la demande' ),
			'obligatoires' => array( 'nom', 'organisation', 'email', 'nature' ),
		),
		'workshop'    => array(
			'nom'          => 'Proposition de workshop',
			'champs'       => array( 'prenom' => 'Prénom', 'nom' => 'Nom', 'telephone' => 'Numéro de téléphone', 'email' => 'Adresse e-mail', 'structure' => 'Nom de la structure', 'thematique' => 'Thématique du workshop proposé', 'thematique_a_definir' => 'Thématique encore à définir' ),
			'obligatoires' => array( 'prenom', 'nom', 'telephone', 'email', 'structure' ),
		),
	);
}

function dts_statuts() {
	return array( 'nouvelle' => 'Nouvelle', 'en-cours' => 'En cours', 'traitee' => 'Traitée' );
}

/* ------------------------------------------------------------------ */
/* Sur le site : champs cachés et réception                            */
/* ------------------------------------------------------------------ */

/** Champs cachés à placer dans le formulaire : [dualtech_formulaire type="contact"]. */
add_shortcode( 'dualtech_formulaire', function ( $atts ) {
	$atts = shortcode_atts( array( 'type' => 'contact' ), $atts, 'dualtech_formulaire' );
	$type = sanitize_key( $atts['type'] );
	return '<input type="hidden" name="action" value="dualtech_formulaire">'
		. '<input type="hidden" name="dts_type" value="' . esc_attr( $type ) . '">'
		. '<input type="hidden" name="dts_t" value="' . esc_attr( (string) time() ) . '">'
		. '<input type="hidden" name="dts_page" value="' . esc_attr( (string) get_queried_object_id() ) . '">'
		. '<span class="dts-piege" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden"><label>Site web <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></span>';
} );

add_action( 'admin_post_nopriv_dualtech_formulaire', 'dts_recevoir' );
add_action( 'admin_post_dualtech_formulaire', 'dts_recevoir' );

function dts_recevoir() {
	// phpcs:disable WordPress.Security.NonceVerification -- formulaire public, protégé par piège, délai et limite d'envois
	$type   = sanitize_key( wp_unslash( $_POST['dts_type'] ?? '' ) );
	$forms  = dts_formulaires();
	$page   = (int) ( $_POST['dts_page'] ?? 0 );
	$retour = $page ? get_permalink( $page ) : wp_get_referer();
	$retour = $retour ?: home_url( '/' );
	$fin    = function ( $etat ) use ( $retour, $type ) {
		wp_safe_redirect( add_query_arg( array( 'envoi' => $etat, 'formulaire' => $type ), $retour ) . '#formulaire-' . $type );
		exit;
	};
	if ( ! isset( $forms[ $type ] ) ) {
		$fin( 'erreur' );
	}
	// Robots : champ piège rempli ou envoi en moins de 3 secondes → on fait comme si tout allait bien.
	if ( ! empty( $_POST['site_web'] ) || ( time() - (int) ( $_POST['dts_t'] ?? 0 ) ) < 3 ) {
		$fin( 'ok' );
	}
	$ip_cle = 'dts_envois_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) . wp_salt() );
	$envois = (int) get_transient( $ip_cle );
	if ( $envois >= 5 ) {
		$fin( 'limite' );
	}

	$f       = $forms[ $type ];
	$valeurs = array();
	foreach ( $f['champs'] as $cle => $libelle ) {
		$brut = wp_unslash( $_POST[ $cle ] ?? '' );
		if ( 'thematique_a_definir' === $cle ) {
			$valeurs[ $cle ] = $brut ? 'Oui' : '';
		} elseif ( in_array( $cle, array( 'message', 'thematique' ), true ) ) {
			$valeurs[ $cle ] = sanitize_textarea_field( $brut );
		} elseif ( 'email' === $cle ) {
			$valeurs[ $cle ] = sanitize_email( $brut );
		} else {
			$valeurs[ $cle ] = sanitize_text_field( $brut );
		}
	}
	foreach ( $f['obligatoires'] as $cle ) {
		if ( '' === $valeurs[ $cle ] ) {
			$fin( 'incomplet' );
		}
	}
	if ( ! is_email( $valeurs['email'] ) || ( 'workshop' === $type && '' === $valeurs['thematique'] && '' === $valeurs['thematique_a_definir'] ) ) {
		$fin( 'incomplet' );
	}
	// phpcs:enable

	$nom   = trim( ( $valeurs['prenom'] ?? '' ) . ' ' . $valeurs['nom'] );
	$orga  = $valeurs['organisation'] ?? ( $valeurs['structure'] ?? '' );
	$titre = $nom . ( $orga ? ' · ' . $orga : '' );
	$id    = wp_insert_post( array(
		'post_type'   => DTS_DEMANDE,
		'post_status' => 'publish',
		'post_title'  => $titre,
	), true );
	if ( is_wp_error( $id ) ) {
		$fin( 'erreur' );
	}
	update_post_meta( $id, '_dt_formulaire', $type );
	update_post_meta( $id, '_dt_champs', $valeurs );
	update_post_meta( $id, '_dt_statut', 'nouvelle' );
	update_post_meta( $id, '_dt_page', $page );
	set_transient( $ip_cle, $envois + 1, HOUR_IN_SECONDS );

	update_post_meta( $id, '_dt_email_envoye', dts_envoyer_email( $id, $type, $valeurs ) ? '1' : '0' );
	$fin( 'ok' );
}

/** E-mail de notification (« Répondre » écrit directement à l'expéditeur). */
function dts_envoyer_email( $id, $type, $valeurs ) {
	$a = get_option( 'dts_email_reception' ) ?: get_option( 'admin_email' );
	if ( ! is_email( $a ) ) {
		return false;
	}
	$f      = dts_formulaires()[ $type ];
	$lignes = array();
	foreach ( $f['champs'] as $cle => $libelle ) {
		if ( '' !== ( $valeurs[ $cle ] ?? '' ) ) {
			$lignes[] = $libelle . " :\n" . $valeurs[ $cle ] . "\n";
		}
	}
	$lignes[] = 'Voir la demande dans WordPress : ' . admin_url( 'post.php?post=' . $id . '&action=edit' );
	$entetes  = array( 'Reply-To: ' . str_replace( array( "\r", "\n" ), '', ( $valeurs['nom'] ?? '' ) ) . ' <' . $valeurs['email'] . '>' );
	return (bool) wp_mail( $a, '[DualTech] ' . $f['nom'] . ' : ' . get_the_title( $id ), implode( "\n", $lignes ), $entetes );
}

/** Message de confirmation affiché dans le formulaire après l'envoi. */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_script( 'dualtech-formulaires', DTS_URL . 'assets/formulaires.js', array(), DTS_VERSION, true );
} );

/* ------------------------------------------------------------------ */
/* Administration : liste, fiche, suivi, export                        */
/* ------------------------------------------------------------------ */

function dts_demandes_nouvelles() {
	static $n = null;
	if ( null === $n ) {
		$n = count( get_posts( array( 'post_type' => DTS_DEMANDE, 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_dt_statut', 'meta_value' => 'nouvelle' ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	return $n;
}

add_filter( 'manage_' . DTS_DEMANDE . '_posts_columns', function ( $cols ) {
	return array( 'cb' => $cols['cb'], 'dts_statut' => 'Statut', 'title' => 'Expéditeur', 'dts_formulaire' => 'Formulaire', 'dts_email' => 'Contact', 'dts_extrait' => 'Demande', 'date' => 'Reçue le' );
} );
add_action( 'manage_' . DTS_DEMANDE . '_posts_custom_column', function ( $col, $id ) {
	$v = (array) get_post_meta( $id, '_dt_champs', true );
	switch ( $col ) {
		case 'dts_statut':
			$s = (string) get_post_meta( $id, '_dt_statut', true ) ?: 'nouvelle';
			echo '<span class="dts-statut dts-statut--' . esc_attr( $s ) . '">' . esc_html( dts_statuts()[ $s ] ?? $s ) . '</span>';
			break;
		case 'dts_formulaire':
			$t = (string) get_post_meta( $id, '_dt_formulaire', true );
			echo '<span class="dts-badge dts-badge--' . esc_attr( $t ) . '">' . esc_html( dts_formulaires()[ $t ]['nom'] ?? $t ) . '</span>';
			break;
		case 'dts_email':
			if ( ! empty( $v['email'] ) ) {
				echo '<a href="mailto:' . esc_attr( $v['email'] ) . '">' . esc_html( $v['email'] ) . '</a>';
			}
			if ( ! empty( $v['telephone'] ) ) {
				echo '<br>' . esc_html( $v['telephone'] );
			}
			if ( '0' === get_post_meta( $id, '_dt_email_envoye', true ) ) {
				echo '<br><span class="dts-aide">E-mail de notification non envoyé</span>';
			}
			break;
		case 'dts_extrait':
			$texte = $v['message'] ?? ( $v['thematique'] ?? '' );
			$texte = $texte ?: ( $v['sujet'] ?? ( $v['nature'] ?? '' ) );
			if ( ! $texte && ! empty( $v['thematique_a_definir'] ) ) {
				$texte = 'Thématique à définir avec l’équipe';
			}
			echo esc_html( wp_trim_words( $texte, 18 ) );
			break;
	}
}, 10, 2 );

/* Filtre par formulaire et par statut, au-dessus de la liste */
add_action( 'restrict_manage_posts', function ( $type ) {
	if ( DTS_DEMANDE !== $type ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification
	$f = sanitize_key( wp_unslash( $_GET['dts_formulaire'] ?? '' ) );
	$s = sanitize_key( wp_unslash( $_GET['dts_statut'] ?? '' ) );
	// phpcs:enable
	echo '<select name="dts_formulaire"><option value="">Tous les formulaires</option>';
	foreach ( dts_formulaires() as $cle => $form ) {
		echo '<option value="' . esc_attr( $cle ) . '"' . selected( $f, $cle, false ) . '>' . esc_html( $form['nom'] ) . '</option>';
	}
	echo '</select><select name="dts_statut"><option value="">Tous les statuts</option>';
	foreach ( dts_statuts() as $cle => $nom ) {
		echo '<option value="' . esc_attr( $cle ) . '"' . selected( $s, $cle, false ) . '>' . esc_html( $nom ) . '</option>';
	}
	echo '</select>';
} );
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || DTS_DEMANDE !== $q->get( 'post_type' ) ) {
		return;
	}
	$meta = array();
	// phpcs:disable WordPress.Security.NonceVerification
	foreach ( array( 'dts_formulaire' => '_dt_formulaire', 'dts_statut' => '_dt_statut' ) as $get => $cle ) {
		$v = sanitize_key( wp_unslash( $_GET[ $get ] ?? '' ) );
		if ( $v ) {
			$meta[] = array( 'key' => $cle, 'value' => $v );
		}
	}
	// phpcs:enable
	if ( $meta ) {
		$q->set( 'meta_query', $meta );
	}
} );

/* Bouton d'export au-dessus de la liste */
add_action( 'manage_posts_extra_tablenav', function ( $ou ) {
	if ( 'top' !== $ou || DTS_DEMANDE !== get_current_screen()->post_type ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=dts_export' ), 'dts_export' );
	echo '<div class="alignleft actions"><a class="button" href="' . esc_url( $url ) . '">Exporter en CSV (Excel)</a></div>';
} );

/* Actions rapides : marquer comme traitée */
add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( DTS_DEMANDE !== $post->post_type ) {
		return $actions;
	}
	unset( $actions['inline hide-if-no-js'] );
	$actions = array( 'edit' => '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">Lire</a>' ) + $actions;
	if ( 'traitee' !== get_post_meta( $post->ID, '_dt_statut', true ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=dts_traitee&id=' . $post->ID ), 'dts_traitee_' . $post->ID );
		$actions['dts_traitee'] = '<a href="' . esc_url( $url ) . '">Marquer comme traitée</a>';
	}
	return $actions;
}, 10, 2 );
add_action( 'admin_post_dts_traitee', function () {
	$id = (int) ( $_GET['id'] ?? 0 );
	check_admin_referer( 'dts_traitee_' . $id );
	if ( current_user_can( 'edit_post', $id ) && DTS_DEMANDE === get_post_type( $id ) ) {
		update_post_meta( $id, '_dt_statut', 'traitee' );
	}
	wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit.php?post_type=' . DTS_DEMANDE ) );
	exit;
} );

/* Fiche d'une demande : lecture, suivi, réponse */
add_action( 'add_meta_boxes_' . DTS_DEMANDE, function ( $post ) {
	remove_meta_box( 'submitdiv', DTS_DEMANDE, 'side' );
	add_meta_box( 'dts-demande', 'Contenu de la demande', 'dts_demande_boite', DTS_DEMANDE, 'normal', 'high' );
	add_meta_box( 'dts-suivi', 'Suivi', 'dts_suivi_boite', DTS_DEMANDE, 'side', 'high' );
	// Ouvrir une nouvelle demande la passe « en cours »
	if ( 'nouvelle' === get_post_meta( $post->ID, '_dt_statut', true ) ) {
		update_post_meta( $post->ID, '_dt_statut', 'en-cours' );
	}
} );

function dts_demande_boite( $post ) {
	$type = (string) get_post_meta( $post->ID, '_dt_formulaire', true );
	$f    = dts_formulaires()[ $type ] ?? array( 'nom' => $type, 'champs' => array() );
	$v    = (array) get_post_meta( $post->ID, '_dt_champs', true );
	echo '<p><span class="dts-badge dts-badge--' . esc_attr( $type ) . '">' . esc_html( $f['nom'] ) . '</span> reçue le ' . esc_html( get_the_date( 'j F Y à H\hi', $post ) ) . '</p><table class="dts-fiche">';
	foreach ( $f['champs'] as $cle => $libelle ) {
		if ( '' === ( $v[ $cle ] ?? '' ) ) {
			continue;
		}
		$valeur = 'email' === $cle ? '<a href="mailto:' . esc_attr( $v[ $cle ] ) . '">' . esc_html( $v[ $cle ] ) . '</a>' : nl2br( esc_html( $v[ $cle ] ) );
		echo '<tr><th>' . esc_html( $libelle ) . '</th><td>' . $valeur . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</table>';
	$page = (int) get_post_meta( $post->ID, '_dt_page', true );
	if ( $page ) {
		echo '<p class="dts-aide">Envoyée depuis la page « ' . esc_html( get_the_title( $page ) ) . ' ».</p>';
	}
	if ( '0' === get_post_meta( $post->ID, '_dt_email_envoye', true ) ) {
		echo '<p class="dts-alerte">L’e-mail de notification n’a pas pu partir (envoi d’e-mails à configurer sur le serveur). La demande est bien enregistrée ici.</p>';
	}
}

function dts_suivi_boite( $post ) {
	wp_nonce_field( 'dts_suivi', 'dts_suivi' );
	echo '<input type="hidden" name="post_status" value="publish">'; // la boîte « Publier » est retirée : la demande reste publiée
	$s = (string) get_post_meta( $post->ID, '_dt_statut', true ) ?: 'nouvelle';
	$v = (array) get_post_meta( $post->ID, '_dt_champs', true );
	dts_champ( 'dts_statut', 'Statut', $s, 'select', '', dts_statuts() );
	dts_champ( 'dts_note', 'Note interne', (string) get_post_meta( $post->ID, '_dt_note_interne', true ), 'textarea', 'Visible seulement dans l’administration.' );
	echo '<p><button type="submit" class="button button-primary">Enregistrer le suivi</button></p>';
	if ( ! empty( $v['email'] ) ) {
		$sujet = rawurlencode( 'DualTech 2026 : votre demande' );
		echo '<p><a class="button" href="mailto:' . esc_attr( $v['email'] ) . '?subject=' . esc_attr( $sujet ) . '">Répondre par e-mail</a></p>';
	}
	echo '<p><a class="dts-supprimer" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">Mettre à la corbeille</a></p>';
}

add_action( 'save_post_' . DTS_DEMANDE, function ( $post_id ) {
	if ( ! dts_peut_enregistrer( $post_id, 'dts_suivi' ) ) {
		return;
	}
	$s = sanitize_key( wp_unslash( $_POST['dts_statut'] ?? '' ) );
	if ( isset( dts_statuts()[ $s ] ) ) {
		update_post_meta( $post_id, '_dt_statut', $s );
	}
	update_post_meta( $post_id, '_dt_note_interne', sanitize_textarea_field( wp_unslash( $_POST['dts_note'] ?? '' ) ) );
} );

/* Le titre d'une demande n'est pas modifiable */
add_action( 'admin_head', function () {
	$ecran = get_current_screen();
	if ( $ecran && DTS_DEMANDE === $ecran->post_type && 'post' === $ecran->base ) {
		echo '<style>#titlediv #title{pointer-events:none;background:#f6f7f7}</style>';
	}
} );

/* Export CSV (séparateur point-virgule, lisible directement dans Excel) */
add_action( 'admin_post_dts_export', function () {
	check_admin_referer( 'dts_export' );
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( 'Accès refusé.' );
	}
	$forms  = dts_formulaires();
	$champs = array();
	foreach ( $forms as $f ) {
		$champs += $f['champs'];
	}
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=demandes-dualtech-' . gmdate( 'Y-m-d' ) . '.csv' );
	$sortie = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fwrite( $sortie, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fputcsv( $sortie, array_merge( array( 'Reçue le', 'Formulaire', 'Statut' ), array_values( $champs ), array( 'Note interne' ) ), ';' );
	foreach ( get_posts( array( 'post_type' => DTS_DEMANDE, 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $p ) {
		$v     = (array) get_post_meta( $p->ID, '_dt_champs', true );
		$ligne = array( get_the_date( 'Y-m-d H:i', $p ), $forms[ get_post_meta( $p->ID, '_dt_formulaire', true ) ]['nom'] ?? '', dts_statuts()[ get_post_meta( $p->ID, '_dt_statut', true ) ] ?? '' );
		foreach ( array_keys( $champs ) as $cle ) {
			$ligne[] = $v[ $cle ] ?? '';
		}
		$ligne[] = (string) get_post_meta( $p->ID, '_dt_note_interne', true );
		fputcsv( $sortie, $ligne, ';' );
	}
	fclose( $sortie ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
} );

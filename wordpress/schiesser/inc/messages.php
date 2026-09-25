<?php
/**
 * Messages reçus : chaque message du formulaire de contact est gardé dans l'administration.
 *
 * - Aucun message perdu si l'e-mail n'arrive pas (hébergeur qui bloque, courrier indésirable).
 * - Statuts : Nouveau, Lu, Traité, et « Spam probable » pour les envois suspects
 *   (gardés pour contrôle, sans e-mail).
 * - Protection des données (nLPD) : les messages sont effacés automatiquement après 12 mois,
 *   les spams après 30 jours.
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_MESSAGE', 'schiesser_message' );

function schiesser_messages_statuts() {
	return array(
		'nouveau' => 'Nouveau',
		'lu'      => 'Lu',
		'traite'  => 'Traité',
		'spam'    => 'Spam probable',
	);
}

add_action( 'init', function () {
	register_post_type( SCHIESSER_MESSAGE, array(
		'labels'          => array(
			'name'               => 'Messages reçus',
			'singular_name'      => 'Message',
			'menu_name'          => 'Messages reçus',
			'edit_item'          => 'Message reçu',
			'search_items'       => 'Rechercher dans les messages',
			'not_found'          => 'Aucun message pour l’instant.',
			'not_found_in_trash' => 'Aucun message dans la corbeille.',
			'all_items'          => 'Tous les messages',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_position'   => 26,
		'menu_icon'       => 'dashicons-email-alt',
		'supports'        => false,
		'capability_type' => 'page',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ), // pas de bouton « Ajouter » : les messages viennent du site
		'map_meta_cap'    => true,
	) );
} );

/** Enregistre un message (appelé par le formulaire de contact). */
function schiesser_message_enregistrer( $d ) {
	$id = wp_insert_post( array(
		'post_type'    => SCHIESSER_MESSAGE,
		'post_status'  => 'private',
		'post_title'   => wp_strip_all_tags( $d['nom'] ) . ' · ' . wp_trim_words( $d['message'], 8, '…' ),
		'post_content' => '',
	), true );
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	foreach ( array( 'nom', 'email', 'telephone', 'message', 'page', 'statut', 'raison' ) as $k ) {
		update_post_meta( $id, '_m_' . $k, (string) ( $d[ $k ] ?? '' ) );
	}
	return $id;
}

function schiesser_message_champs( $id ) {
	$d = array();
	foreach ( array( 'nom', 'email', 'telephone', 'message', 'page', 'statut', 'raison', 'mail' ) as $k ) {
		$d[ $k ] = (string) get_post_meta( $id, '_m_' . $k, true );
	}
	$d['statut'] = isset( schiesser_messages_statuts()[ $d['statut'] ] ) ? $d['statut'] : 'nouveau';
	return $d;
}

function schiesser_messages_nouveaux() {
	$q = new WP_Query( array(
		'post_type'      => SCHIESSER_MESSAGE,
		'post_status'    => 'private',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_m_statut', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => 'nouveau', // phpcs:ignore WordPress.DB.SlowDBQuery
		'no_found_rows'  => false,
	) );
	return (int) $q->found_posts;
}

/* Pastille du menu : nombre de nouveaux messages. */
add_action( 'admin_menu', function () {
	global $menu;
	$n = schiesser_messages_nouveaux();
	if ( ! $n ) {
		return;
	}
	foreach ( (array) $menu as $i => $m ) {
		if ( isset( $m[2] ) && 'edit.php?post_type=' . SCHIESSER_MESSAGE === $m[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . (int) $n . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
	}
}, 99 );

/* ------------------------------------------------------------------ */
/* Liste des messages                                                  */
/* ------------------------------------------------------------------ */

add_filter( 'manage_' . SCHIESSER_MESSAGE . '_posts_columns', function () {
	return array(
		'cb'        => '<input type="checkbox">',
		'm_statut'  => 'Statut',
		'm_de'      => 'De',
		'm_message' => 'Message',
		'date'      => 'Reçu le',
	);
} );

add_action( 'manage_' . SCHIESSER_MESSAGE . '_posts_custom_column', function ( $col, $id ) {
	$d = schiesser_message_champs( $id );
	if ( 'm_statut' === $col ) {
		echo '<span class="s-msg-statut s-msg-statut--' . esc_attr( $d['statut'] ) . '">' . esc_html( schiesser_messages_statuts()[ $d['statut'] ] ) . '</span>';
		if ( '0' === $d['mail'] ) {
			echo '<br><span class="s-msg-alerte">E-mail non parti</span>';
		}
	} elseif ( 'm_de' === $col ) {
		echo '<strong><a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( $d['nom'] ) . '</a></strong><br>';
		echo '<a href="' . esc_url( 'mailto:' . $d['email'] ) . '">' . esc_html( $d['email'] ) . '</a>';
		echo $d['telephone'] ? '<br>' . esc_html( $d['telephone'] ) : '';
	} elseif ( 'm_message' === $col ) {
		echo esc_html( wp_trim_words( $d['message'], 30, '…' ) );
	}
}, 10, 2 );

/* Actions rapides sur chaque ligne. */
add_filter( 'post_row_actions', function ( $actions, $post ) {
	if ( SCHIESSER_MESSAGE !== $post->post_type ) {
		return $actions;
	}
	$d = schiesser_message_champs( $post->ID );
	$a = array( 'ouvrir' => '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">Lire</a>' );
	if ( $d['email'] ) {
		$a['repondre'] = '<a href="' . esc_url( schiesser_message_lien_reponse( $d ) ) . '">Répondre</a>';
	}
	foreach ( array( 'traite' => 'Marquer comme traité', 'spam' => 'Spam', 'nouveau' => 'Remettre en nouveau' ) as $s => $l ) {
		if ( $d['statut'] !== $s ) {
			$a[ 'statut-' . $s ] = '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_message_statut&id=' . $post->ID . '&statut=' . $s ), 'schiesser_message_statut' ) ) . '">' . esc_html( $l ) . '</a>';
		}
	}
	if ( isset( $actions['trash'] ) ) {
		$a['trash'] = $actions['trash'];
	}
	return $a;
}, 10, 2 );

function schiesser_message_lien_reponse( $d ) {
	return 'mailto:' . rawurlencode( $d['email'] ) . '?subject=' . rawurlencode( 'Re : votre message à ' . ( schiesser_reglage( 'nom_etablissement' ) ?: get_bloginfo( 'name' ) ) )
		. '&body=' . rawurlencode( "Bonjour " . $d['nom'] . ",\n\n\n\n---\nVotre message :\n" . $d['message'] );
}

add_action( 'admin_post_schiesser_message_statut', function () {
	$id     = absint( $_GET['id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
	$statut = sanitize_key( $_GET['statut'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	check_admin_referer( 'schiesser_message_statut' );
	if ( ! $id || ! current_user_can( 'edit_post', $id ) || ! isset( schiesser_messages_statuts()[ $statut ] ) ) {
		wp_die( 'Action impossible.' );
	}
	update_post_meta( $id, '_m_statut', $statut );
	wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit.php?post_type=' . SCHIESSER_MESSAGE ) );
	exit;
} );

/* Filtres par statut au-dessus de la liste (Nouveaux, Traités, Spam…). */
add_filter( 'views_edit-' . SCHIESSER_MESSAGE, function ( $vues ) {
	$courant = sanitize_key( $_GET['m_statut'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	$propres = array( 'tous' => '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . SCHIESSER_MESSAGE ) ) . '"' . ( '' === $courant ? ' class="current"' : '' ) . '>Tous</a>' );
	foreach ( schiesser_messages_statuts() as $s => $l ) {
		$propres[ $s ] = '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . SCHIESSER_MESSAGE . '&m_statut=' . $s ) ) . '"' . ( $courant === $s ? ' class="current"' : '' ) . '>' . esc_html( $l ) . '</a>';
	}
	if ( isset( $vues['trash'] ) ) {
		$propres['trash'] = $vues['trash'];
	}
	return $propres;
} );

add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || SCHIESSER_MESSAGE !== $q->get( 'post_type' ) ) {
		return;
	}
	$statut = sanitize_key( $_GET['m_statut'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( schiesser_messages_statuts()[ $statut ] ) ) {
		$q->set( 'meta_key', '_m_statut' );
		$q->set( 'meta_value', $statut );
	} elseif ( 'trash' !== $q->get( 'post_status' ) ) {
		// Par défaut, les spams probables ne se mêlent pas aux vrais messages.
		$q->set( 'meta_query', array( array( 'key' => '_m_statut', 'value' => 'spam', 'compare' => '!=' ) ) );
	}
} );

/* ------------------------------------------------------------------ */
/* Lecture d'un message                                                */
/* ------------------------------------------------------------------ */

add_action( 'add_meta_boxes_' . SCHIESSER_MESSAGE, function ( $post ) {
	remove_meta_box( 'submitdiv', SCHIESSER_MESSAGE, 'side' );
	add_meta_box( 'schiesser_message', 'Message', 'schiesser_message_boite', SCHIESSER_MESSAGE, 'normal', 'high' );
	add_meta_box( 'schiesser_message_statut', 'Suivi', 'schiesser_message_boite_statut', SCHIESSER_MESSAGE, 'side', 'high' );
	// Ouvrir un nouveau message le marque comme lu.
	if ( 'nouveau' === get_post_meta( $post->ID, '_m_statut', true ) ) {
		update_post_meta( $post->ID, '_m_statut', 'lu' );
	}
} );

function schiesser_message_boite( $post ) {
	$d = schiesser_message_champs( $post->ID );
	?>
	<table class="form-table s-msg-fiche" role="presentation">
		<tr><th scope="row">Nom</th><td><?php echo esc_html( $d['nom'] ); ?></td></tr>
		<tr><th scope="row">E-mail</th><td><a href="<?php echo esc_url( 'mailto:' . $d['email'] ); ?>"><?php echo esc_html( $d['email'] ); ?></a></td></tr>
		<?php if ( $d['telephone'] ) : ?><tr><th scope="row">Téléphone</th><td><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $d['telephone'] ) ); ?>"><?php echo esc_html( $d['telephone'] ); ?></a></td></tr><?php endif; ?>
		<tr><th scope="row">Reçu le</th><td><?php echo esc_html( get_the_date( 'l j F Y, H:i', $post ) ); ?><?php echo $d['page'] ? ' · depuis <a href="' . esc_url( $d['page'] ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $d['page'], PHP_URL_PATH ) ?: '/' ) . '</a>' : ''; ?></td></tr>
		<tr><th scope="row">Message</th><td><div class="s-msg-texte"><?php echo nl2br( esc_html( $d['message'] ) ); ?></div></td></tr>
	</table>
	<?php if ( 'spam' === $d['statut'] && $d['raison'] ) : ?>
		<p class="description">Classé « spam probable » : <?php echo esc_html( $d['raison'] ); ?>. Si c'est un vrai message, choisissez « Nouveau » ou « Lu » à droite.</p>
	<?php endif; ?>
	<?php if ( '0' === $d['mail'] ) : ?>
		<p class="s-msg-alerte">L'e-mail de ce message n'a pas pu partir. Installez une extension d'envoi d'e-mails (par exemple FluentSMTP ou WP Mail SMTP) pour les recevoir aussi dans votre boîte.</p>
	<?php endif; ?>
	<p><a class="button button-primary" href="<?php echo esc_url( schiesser_message_lien_reponse( $d ) ); ?>">Répondre par e-mail</a></p>
	<?php
}

function schiesser_message_boite_statut( $post ) {
	$d = schiesser_message_champs( $post->ID );
	wp_nonce_field( 'schiesser_message', 'schiesser_message_nonce' );
	?>
	<p><label for="m-statut"><strong>Statut</strong></label><br>
	<select id="m-statut" name="m_statut">
		<?php foreach ( schiesser_messages_statuts() as $s => $l ) : ?>
			<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $d['statut'], $s ); ?>><?php echo esc_html( $l ); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p><button type="submit" class="button button-primary">Enregistrer</button></p>
	<p><a class="submitdelete" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>">Mettre à la corbeille</a></p>
	<p class="description">Les messages sont effacés automatiquement après 12 mois (protection des données).</p>
	<?php
}

add_action( 'save_post_' . SCHIESSER_MESSAGE, function ( $id ) {
	if ( ! isset( $_POST['schiesser_message_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['schiesser_message_nonce'] ), 'schiesser_message' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$s = sanitize_key( $_POST['m_statut'] ?? '' );
	if ( isset( schiesser_messages_statuts()[ $s ] ) ) {
		update_post_meta( $id, '_m_statut', $s );
	}
} );

/* Le message reste privé quand on enregistre (le formulaire n'a pas de bouton « Publier »). */
add_filter( 'wp_insert_post_data', function ( $data ) {
	if ( SCHIESSER_MESSAGE === $data['post_type'] && 'trash' !== $data['post_status'] ) {
		$data['post_status'] = 'private';
	}
	return $data;
} );

add_action( 'admin_head', function () {
	$e = get_current_screen();
	if ( ! $e || SCHIESSER_MESSAGE !== $e->post_type ) {
		return;
	}
	echo '<style>.s-msg-statut{display:inline-block;padding:2px 8px;border-radius:10px;font-size:12px;font-weight:600;background:#f0f0f1;color:#50575e}'
		. '.s-msg-statut--nouveau{background:#23503B;color:#fff}.s-msg-statut--traite{background:#e7f0ea;color:#23503B}.s-msg-statut--spam{background:#fcf0f1;color:#8a2424}'
		. '.s-msg-alerte{color:#8a2424;font-weight:600}.s-msg-texte{max-width:680px;font-size:15px;line-height:1.6;padding:12px 16px;background:#f6f7f7;border-left:3px solid #23503B}'
		. '.column-m_statut{width:130px}.column-m_de{width:240px}.post-type-schiesser_message .page-title-action{display:none}</style>';
} );

/* ------------------------------------------------------------------ */
/* Effacement automatique (nLPD)                                       */
/* ------------------------------------------------------------------ */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'schiesser_messages_menage' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'schiesser_messages_menage' );
	}
} );

add_action( 'schiesser_messages_menage', function () {
	foreach ( array( array( '12 months ago', '!=' ), array( '30 days ago', '=' ) ) as $r ) {
		$vieux = get_posts( array(
			'post_type'   => SCHIESSER_MESSAGE,
			'post_status' => array( 'private', 'trash' ),
			'numberposts' => 200,
			'fields'      => 'ids',
			'date_query'  => array( array( 'before' => $r[0] ) ),
			'meta_query'  => array( array( 'key' => '_m_statut', 'value' => 'spam', 'compare' => $r[1] ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
		foreach ( $vieux as $id ) {
			wp_delete_post( $id, true );
		}
	}
} );

<?php
/**
 * Réglages : adresse qui reçoit les demandes, essai d'envoi d'e-mail.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_dts_reglages', function () {
	check_admin_referer( 'dts_reglages' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	$email = sanitize_email( wp_unslash( $_POST['dts_email_reception'] ?? '' ) );
	update_option( 'dts_email_reception', is_email( $email ) ? $email : '' );
	$etat = 'enregistre';
	if ( ! empty( $_POST['dts_tester'] ) ) {
		$a    = get_option( 'dts_email_reception' ) ?: get_option( 'admin_email' );
		$etat = wp_mail( $a, '[DualTech] E-mail de test', "Cet e-mail confirme que le site DualTech peut envoyer les demandes reçues.\n\n" . home_url( '/' ) ) ? 'test-ok' : 'test-ko';
	}
	wp_safe_redirect( admin_url( 'admin.php?page=dualtech-reglages&etat=' . $etat ) );
	exit;
} );

function dts_reglages_ecran() {
	$etat  = sanitize_key( wp_unslash( $_GET['etat'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$email = (string) get_option( 'dts_email_reception' );
	echo '<div class="wrap dts">';
	dts_entete( 'Réglages', 'Où arrivent les demandes envoyées par les formulaires du site.' );
	$messages = array(
		'enregistre' => array( 'success', 'Réglages enregistrés.' ),
		'test-ok'    => array( 'success', 'E-mail de test envoyé. Vérifiez la boîte de réception (et les indésirables).' ),
		'test-ko'    => array( 'error', 'L’e-mail de test n’est pas parti : le serveur n’envoie pas encore d’e-mails. Les demandes restent enregistrées dans DualTech → Demandes reçues. Pour activer l’envoi, installez une extension SMTP (par exemple « WP Mail SMTP ») avec le compte e-mail de DualTech.' ),
	);
	if ( isset( $messages[ $etat ] ) ) {
		echo '<div class="notice notice-' . esc_attr( $messages[ $etat ][0] ) . '"><p>' . esc_html( $messages[ $etat ][1] ) . '</p></div>';
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="dts-panneau">';
	wp_nonce_field( 'dts_reglages' );
	echo '<input type="hidden" name="action" value="dts_reglages">';
	dts_champ( 'dts_email_reception', 'Adresse qui reçoit les demandes', $email, 'email', 'Vide : l’adresse d’administration du site (' . get_option( 'admin_email' ) . '). Chaque demande est de toute façon enregistrée dans « Demandes reçues ».' );
	echo '<p><button class="button button-primary" type="submit">Enregistrer</button> <button class="button" type="submit" name="dts_tester" value="1">Enregistrer et envoyer un e-mail de test</button></p></form>';
	echo '<div class="dts-panneau"><h2>Formulaires du site</h2><ul class="dts-liste">';
	foreach ( dts_formulaires() as $f ) {
		echo '<li>' . esc_html( $f['nom'] ) . '</li>';
	}
	echo '</ul><p class="dts-aide">Protégés contre les robots sans case à cocher : champ piège invisible, délai minimal de saisie et 5 envois par heure au plus depuis une même connexion.</p></div></div>';
}

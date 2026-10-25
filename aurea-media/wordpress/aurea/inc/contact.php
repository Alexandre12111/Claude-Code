<?php
/**
 * Formulaire de devis de la page Contact.
 *
 * Le formulaire de la maquette est envoyé à admin-post.php (action « aurea_contact ») :
 * - contrôles : jeton de sécurité, champ piège invisible pour les robots, délai minimal de saisie,
 *   limite de 5 envois par heure et par adresse IP ;
 * - la demande est envoyée par e-mail (Personnaliser → Aurea Media → e-mail des devis), avec
 *   « Répondre » qui écrit directement au prospect ;
 * - une copie est gardée dans l'administration (Messages reçus), même si l'e-mail n'arrive pas.
 * Après l'envoi, le visiteur revient sur la page avec le message de confirmation.
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_MESSAGE', 'aurea_message' );

add_action( 'init', function () {
	register_post_type( AUREA_MESSAGE, array(
		'labels'          => array(
			'name'          => 'Messages reçus',
			'singular_name' => 'Message',
			'menu_name'     => 'Messages reçus',
			'all_items'     => 'Messages reçus',
			'edit_item'     => 'Message',
			'not_found'     => 'Aucun message pour le moment.',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 26,
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

/** Champs cachés du formulaire : action, jeton, champ piège, heure d'affichage. */
add_shortcode( 'aurea_contact_jeton', function () {
	return '<input type="hidden" name="action" value="aurea_contact">'
		. wp_nonce_field( 'aurea_contact', 'aurea_jeton', true, false )
		. '<input type="hidden" name="aurea_t" value="' . esc_attr( time() ) . '">'
		. '<input type="hidden" name="aurea_page" value="' . esc_attr( get_queried_object_id() ) . '">'
		. '<p class="f-piege" aria-hidden="true"><label>Site web <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></p>';
} );

add_action( 'admin_post_nopriv_aurea_contact', 'aurea_contact_envoyer' );
add_action( 'admin_post_aurea_contact', 'aurea_contact_envoyer' );

function aurea_contact_envoyer() {
	$page   = isset( $_POST['aurea_page'] ) ? (int) $_POST['aurea_page'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$retour = $page ? get_permalink( $page ) : aurea_url( 'contact' );
	$fin    = function ( $etat ) use ( $retour ) {
		wp_safe_redirect( add_query_arg( 'devis', $etat, $retour ) . '#form-contact' );
		exit;
	};

	if ( ! isset( $_POST['aurea_jeton'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aurea_jeton'] ) ), 'aurea_contact' ) ) {
		$fin( 'expire' );
	}
	// Robots : champ piège rempli, ou formulaire envoyé moins de 3 secondes après l'affichage.
	$t = isset( $_POST['aurea_t'] ) ? (int) $_POST['aurea_t'] : 0;
	if ( ! empty( $_POST['site_web'] ) || ( $t && time() - $t < 3 ) ) {
		$fin( 'ok' ); // on ne dit rien au robot
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$cle = 'aurea_contact_' . md5( $ip );
	$nb  = (int) get_transient( $cle );
	if ( $nb >= 5 ) {
		$fin( 'limite' );
	}

	$nom     = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
	$mail    = sanitize_email( wp_unslash( $_POST['mail'] ?? '' ) );
	$tel     = sanitize_text_field( wp_unslash( $_POST['tel'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	$besoins = array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) ( $_POST['besoin'] ?? array() ) ) );
	if ( ! $nom || ! is_email( $mail ) || ! $message ) {
		$fin( 'incomplet' );
	}
	set_transient( $cle, $nb + 1, HOUR_IN_SECONDS );

	$lignes = array(
		'Nom : ' . $nom,
		'E-mail : ' . $mail,
		'Téléphone : ' . ( $tel ?: 'non renseigné' ),
		'Besoin : ' . ( $besoins ? implode( ', ', $besoins ) : 'non précisé' ),
		'',
		$message,
		'',
		'Envoyé depuis ' . $retour,
	);
	$texte = implode( "\n", $lignes );

	// Copie dans l'administration
	wp_insert_post( array(
		'post_type'    => AUREA_MESSAGE,
		'post_status'  => 'private',
		'post_title'   => $nom . ( $besoins ? ' · ' . implode( ', ', $besoins ) : '' ),
		'post_content' => $texte,
	) );

	$c      = aurea_coordonnees();
	$sujet  = 'Nouvelle demande de devis : ' . $nom;
	$entete = array( 'Reply-To: ' . $nom . ' <' . $mail . '>' );
	$envoye = wp_mail( $c['email_devis'], $sujet, $texte, $entete );
	$fin( $envoye ? 'ok' : 'ok-copie' );
}

/* Message affiché après l'envoi (le formulaire de la maquette a déjà son encart de confirmation). */
add_action( 'wp_footer', function () {
	if ( ! isset( $_GET['devis'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$etat     = sanitize_key( wp_unslash( $_GET['devis'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$messages = array(
		'ok'        => array( true, 'Merci, votre demande est bien envoyée.', 'Réponse personnelle sous 24h ouvrées.' ),
		'ok-copie'  => array( true, 'Merci, votre demande est bien enregistrée.', 'Réponse personnelle sous 24h ouvrées.' ),
		'incomplet' => array( false, 'Il manque une information.', 'Renseignez votre nom, une adresse e-mail valide et votre projet.' ),
		'expire'    => array( false, 'La page a expiré.', 'Rechargez la page puis renvoyez votre demande.' ),
		'limite'    => array( false, 'Trop de demandes envoyées.', 'Réessayez dans une heure, ou appelez nous directement.' ),
	);
	if ( ! isset( $messages[ $etat ] ) ) {
		return;
	}
	list( $ok, $titre, $texte ) = $messages[ $etat ];
	?>
	<script>
	(function () {
		var f = document.getElementById('form-contact'); if (!f) return;
		var b = f.querySelector('.f-ok'); if (!b) return;
		b.querySelector('b').textContent = <?php echo wp_json_encode( $titre ); ?>;
		b.querySelector('div span').textContent = <?php echo wp_json_encode( $texte ); ?>;
		<?php if ( ! $ok ) : ?>b.classList.add('is-erreur'); b.querySelector('.ci').classList.remove('ok');<?php endif; ?>
		b.hidden = false;
		<?php if ( $ok ) : ?>f.querySelector('button[type=submit]').disabled = true;<?php endif; ?>
	})();
	</script>
	<?php
}, 30 );

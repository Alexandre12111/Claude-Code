<?php
/**
 * Écran « Aujourd'hui » : tout ce qui change d'un jour à l'autre, sur un seul écran
 * (pratique aussi sur téléphone).
 *
 * - État en direct (ouvert ou fermé, horaires du jour, jours particuliers à venir).
 * - Nouveaux messages du formulaire.
 * - Vitrine du jour, suggestion du Tea Room.
 * - Produits « épuisés aujourd'hui » : cochés le matin, ils reviennent tout seuls le lendemain.
 * - Bandeau d'annonce : ajouter ou retirer un message en quelques secondes.
 */

defined( 'ABSPATH' ) || exit;

define( 'SCHIESSER_OPTION_EPUISES', 'schiesser_epuises' );

/* ------------------------------------------------------------------ */
/* Produits épuisés aujourd'hui                                        */
/* ------------------------------------------------------------------ */

/** Identifiants épuisés aujourd'hui (la liste d'hier ne compte plus). */
function schiesser_epuises() {
	static $ids = null;
	if ( null === $ids ) {
		$o   = (array) get_option( SCHIESSER_OPTION_EPUISES, array() );
		$ids = ( $o['date'] ?? '' ) === current_datetime()->format( 'Y-m-d' ) ? array_map( 'intval', (array) ( $o['ids'] ?? array() ) ) : array();
	}
	return $ids;
}

function schiesser_est_epuise( $id ) {
	return in_array( (int) $id, schiesser_epuises(), true );
}

/* ------------------------------------------------------------------ */
/* Menu et enregistrement                                              */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	$n = function_exists( 'schiesser_messages_nouveaux' ) ? schiesser_messages_nouveaux() : 0;
	add_menu_page(
		'Aujourd’hui',
		'Aujourd’hui' . ( $n ? ' <span class="awaiting-mod"><span class="pending-count">' . (int) $n . '</span></span>' : '' ),
		'edit_pages',
		'schiesser-aujourdhui',
		'schiesser_page_aujourdhui',
		'dashicons-coffee',
		1
	);
} );

add_action( 'admin_post_schiesser_aujourdhui', function () {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_aujourdhui' );
	$e = wp_unslash( $_POST );

	// Vitrine du jour et bandeau d'annonce : dans les Réglages maison (même nettoyage que l'écran complet).
	$r            = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), schiesser_reglages_defaut() );
	$r['vitrine'] = array_slice( array_map( 'sanitize_text_field', (array) ( $e['vitrine'] ?? array() ) ), 0, 4 );
	$annonces     = array_values( (array) $r['annonces'] );
	foreach ( array_map( 'intval', (array) ( $e['retirer_annonce'] ?? array() ) ) as $i ) {
		unset( $annonces[ $i ] );
	}
	$n = (array) ( $e['annonce'] ?? array() );
	if ( '' !== trim( (string) ( $n['texte'] ?? '' ) ) ) {
		$annonces[] = $n;
	}
	$r['annonces'] = array_values( $annonces );
	update_option( SCHIESSER_OPTION, $r );

	// Suggestion du jour du Tea Room.
	if ( isset( $e['suggestion'] ) && defined( 'SCHIESSER_TEAROOM' ) ) {
		$id = absint( $e['suggestion'] );
		if ( $id && SCHIESSER_TEAROOM === get_post_type( $id ) ) {
			schiesser_tearoom_definir_suggestion( $id, true );
		} elseif ( ! $id ) {
			foreach ( get_posts( array( 'post_type' => SCHIESSER_TEAROOM, 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_t_suggestion' ) ) as $autre ) { // phpcs:ignore WordPress.DB.SlowDBQuery
				delete_post_meta( $autre, '_t_suggestion' );
			}
		}
	}

	// Épuisés aujourd'hui.
	update_option( SCHIESSER_OPTION_EPUISES, array(
		'date' => current_datetime()->format( 'Y-m-d' ),
		'ids'  => array_values( array_unique( array_map( 'absint', (array) ( $e['epuises'] ?? array() ) ) ) ),
	), false );

	wp_safe_redirect( admin_url( 'admin.php?page=schiesser-aujourdhui&enregistre=1' ) );
	exit;
} );

/* ------------------------------------------------------------------ */
/* L'écran                                                             */
/* ------------------------------------------------------------------ */

function schiesser_page_aujourdhui() {
	$r        = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), schiesser_reglages_defaut() );
	$etat     = function_exists( 'schiesser_barre_mobile_etat' ) ? schiesser_barre_mobile_etat() : array( false, '' );
	$auj      = current_datetime();
	$part     = function_exists( 'schiesser_jour_particulier' ) ? schiesser_jour_particulier( $auj->format( 'Y-m-d' ) ) : null;
	$reglages = admin_url( 'admin.php?page=schiesser-reglages' );
	?>
	<div class="wrap s-admin s-auj">
		<div class="s-entete">
			<div class="s-entete-logo" aria-hidden="true"><span>Confiserie ◆ Tea-Room</span><strong>Schiesser</strong></div>
			<div class="s-entete-texte">
				<h1>Aujourd’hui, <?php echo esc_html( wp_date( 'l j F' ) ); ?></h1>
				<p>Ce qui change d’un jour à l’autre, sur un seul écran. Tout s’enregistre d’un clic en bas de la page.</p>
			</div>
			<a class="button s-voir" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">Voir le site <span aria-hidden="true">↗</span></a>
		</div>
		<hr class="wp-header-end">

		<?php if ( isset( $_GET['enregistre'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><strong>C’est enregistré.</strong> Le site est à jour.</p></div>
		<?php endif; ?>

		<div class="s-auj-haut">
			<div class="s-carte s-auj-etat<?php echo $etat[0] ? ' is-ouvert' : ''; ?>">
				<div class="s-carte-corps">
					<p class="s-auj-k">En ce moment</p>
					<p class="s-auj-statut"><span class="s-auj-led" aria-hidden="true"></span><?php echo esc_html( $etat[1] ); ?></p>
					<p class="s-auj-detail">Aujourd’hui : <strong><?php echo esc_html( schiesser_horaires_du_jour() ); ?></strong><?php echo $part ? ' · ' . esc_html( $part['motif'] ) : ''; ?></p>
					<?php
					$venir = function_exists( 'schiesser_jours_particuliers' ) ? array_slice( schiesser_jours_particuliers( 21, $auj->modify( '+1 day' )->format( 'Y-m-d' ) ), 0, 3 ) : array();
					foreach ( $venir as $p ) {
						echo '<p class="s-auj-detail">' . esc_html( ucfirst( schiesser_date_fr( $p['date'], 'l j F' ) ) . ' : ' . ( $p['plage'] ? $p['plage'][0] . '–' . $p['plage'][1] : 'fermé' ) . ' (' . $p['motif'] . ')' ) . '</p>';
					}
					?>
					<p><a href="<?php echo esc_url( $reglages . '#coordonnees' ); ?>">Horaires, jours fériés et fermetures →</a></p>
				</div>
			</div>

			<div class="s-carte s-auj-messages">
				<div class="s-carte-corps">
					<?php
					$nouveaux = function_exists( 'schiesser_messages_nouveaux' ) ? schiesser_messages_nouveaux() : 0;
					$derniers = defined( 'SCHIESSER_MESSAGE' ) ? get_posts( array(
						'post_type'   => SCHIESSER_MESSAGE,
						'post_status' => 'private',
						'numberposts' => 4,
						'meta_key'    => '_m_statut', // phpcs:ignore WordPress.DB.SlowDBQuery
						'meta_value'  => 'nouveau', // phpcs:ignore WordPress.DB.SlowDBQuery
					) ) : array();
					?>
					<p class="s-auj-k">Messages</p>
					<p class="s-auj-statut"><?php echo $nouveaux ? esc_html( $nouveaux . ' nouveau' . ( $nouveaux > 1 ? 'x' : '' ) . ' message' . ( $nouveaux > 1 ? 's' : '' ) ) : 'Aucun nouveau message'; ?></p>
					<?php if ( $derniers ) : ?>
						<ul class="s-auj-liste">
							<?php foreach ( $derniers as $m ) : $d = schiesser_message_champs( $m->ID ); ?>
								<li><a href="<?php echo esc_url( get_edit_post_link( $m->ID ) ); ?>"><strong><?php echo esc_html( $d['nom'] ); ?></strong> · <?php echo esc_html( wp_trim_words( $d['message'], 9, '…' ) ); ?></a> <span><?php echo esc_html( human_time_diff( get_post_time( 'U', true, $m ) ) ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=schiesser_message' ) ); ?>">Tous les messages →</a></p>
				</div>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="s-auj-form">
			<input type="hidden" name="action" value="schiesser_aujourdhui">
			<?php wp_nonce_field( 'schiesser_aujourdhui' ); ?>

			<?php schiesser_carte_debut( 'En vitrine aujourd’hui', 'Les produits du jour, dans la bande sous la grande photo de l’accueil. Laissez vide pour masquer une ligne.', 'store' ); ?>
			<div class="s-grille">
				<?php for ( $i = 0; $i < 4; $i++ ) : ?>
					<div class="s-champ"><label for="s-auj-vitrine-<?php echo (int) $i; ?>">Produit <?php echo (int) ( $i + 1 ); ?></label>
						<input type="text" id="s-auj-vitrine-<?php echo (int) $i; ?>" name="vitrine[]" value="<?php echo esc_attr( $r['vitrine'][ $i ] ?? '' ); ?>" placeholder="<?php echo 0 === $i ? 'Ex. Läckerli' : ''; ?>"></div>
				<?php endfor; ?>
			</div>
			<?php schiesser_carte_fin(); ?>

			<?php
			$plats = defined( 'SCHIESSER_TEAROOM' ) ? get_posts( array( 'post_type' => SCHIESSER_TEAROOM, 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ) : array();
			$sugg  = 0;
			foreach ( $plats as $p ) {
				if ( get_post_meta( $p->ID, '_t_suggestion', true ) ) {
					$sugg = $p->ID;
				}
			}
			schiesser_carte_debut( 'Suggestion du Tea Room', 'Mise en avant dans l’encadré « La suggestion du jour » de la carte du salon.', 'food' );
			?>
			<div class="s-champ"><label for="s-auj-sugg">Suggestion du jour</label>
				<select id="s-auj-sugg" name="suggestion">
					<option value="0">Aucune suggestion</option>
					<?php foreach ( $plats as $p ) : ?>
						<option value="<?php echo (int) $p->ID; ?>" <?php selected( $sugg, $p->ID ); ?>><?php echo esc_html( get_the_title( $p ) ); ?></option>
					<?php endforeach; ?>
				</select></div>
			<?php schiesser_carte_fin(); ?>

			<?php
			schiesser_carte_debut( 'Épuisé aujourd’hui', 'Cochez ce qui n’est plus disponible : le site affiche « Épuisé aujourd’hui ». Demain matin, tout redevient disponible tout seul.', 'dismiss' );
			$groupes = array(
				'Boutique' => get_posts( array( 'post_type' => SCHIESSER_PRODUIT, 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) ),
				'Tea Room' => $plats,
			);
			foreach ( $groupes as $titre => $liste ) :
				if ( ! $liste ) {
					continue;
				}
				?>
				<fieldset class="s-auj-epuises">
					<legend><?php echo esc_html( $titre ); ?></legend>
					<?php foreach ( $liste as $p ) : ?>
						<label class="s-auj-case"><input type="checkbox" name="epuises[]" value="<?php echo (int) $p->ID; ?>" <?php checked( schiesser_est_epuise( $p->ID ) ); ?>><span><?php echo esc_html( get_the_title( $p ) ); ?></span></label>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>
			<?php schiesser_carte_fin(); ?>

			<?php schiesser_carte_debut( 'Bandeau d’annonce', 'Un message tout en haut du site pendant quelques jours. Il disparaît tout seul après la date de fin.', 'megaphone' ); ?>
			<?php
			$auj_ymd = $auj->format( 'Y-m-d' );
			$actives = array_values( (array) $r['annonces'] );
			if ( $actives ) :
				?>
				<ul class="s-auj-annonces">
					<?php foreach ( $actives as $i => $a ) :
						$en_cours = ( ! $a['debut'] || $a['debut'] <= $auj_ymd ) && ( ! $a['fin'] || $a['fin'] >= $auj_ymd );
						?>
						<li><label><input type="checkbox" name="retirer_annonce[]" value="<?php echo (int) $i; ?>"> Retirer</label>
							<span class="s-auj-annonce-texte"><?php echo esc_html( $a['texte'] ); ?></span>
							<span class="s-auj-annonce-dates"><?php echo esc_html( ( $en_cours ? 'Affichée' : 'Programmée' ) . ( $a['debut'] ? ' du ' . schiesser_date_fr( $a['debut'], 'j M' ) : '' ) . ( $a['fin'] ? ' jusqu’au ' . schiesser_date_fr( $a['fin'], 'j M' ) : ' sans date de fin' ) ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="s-grille">
				<div class="s-champ s-large"><label for="s-auj-annonce">Nouveau message</label>
					<input type="text" id="s-auj-annonce" name="annonce[texte]" maxlength="180" placeholder="Ex. Commandes de Pâques ouvertes jusqu’au 2 avril"></div>
				<div class="s-champ"><label for="s-auj-du">Du</label><input type="date" id="s-auj-du" name="annonce[debut]" value="<?php echo esc_attr( $auj_ymd ); ?>"></div>
				<div class="s-champ"><label for="s-auj-au">Au (compris)</label><input type="date" id="s-auj-au" name="annonce[fin]"></div>
				<div class="s-champ"><label for="s-auj-lien">Lien (facultatif)</label><input type="url" id="s-auj-lien" name="annonce[lien]" placeholder="https://…"></div>
				<div class="s-champ"><label for="s-auj-style">Couleur</label><select id="s-auj-style" name="annonce[style]">
					<?php foreach ( schiesser_annonces_styles() as $v => $l ) : ?><option value="<?php echo esc_attr( $v ); ?>"><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
				</select></div>
			</div>
			<?php schiesser_carte_fin(); ?>

			<div class="s-auj-barre"><button type="submit" class="button button-primary button-hero">Enregistrer</button></div>
		</form>

		<p class="s-auj-liens">
			<a href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">Médiathèque</a> ·
			<?php if ( current_user_can( 'edit_theme_options' ) ) : ?><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">Menus du site</a> · <?php endif; ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=schiesser-guide' ) ); ?>">Guide du site</a> ·
			<a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">Mon profil</a>
		</p>
	</div>
	<?php
}

add_action( 'admin_head', function () {
	$e = get_current_screen();
	if ( ! $e || 'toplevel_page_schiesser-aujourdhui' !== $e->id ) {
		return;
	}
	echo '<style>
	.s-auj-haut{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:0}
	.s-auj-haut .s-carte{margin-bottom:18px}
	.s-auj-k{margin:0 0 4px;font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--s-doux)}
	.s-auj-statut{display:flex;align-items:center;gap:10px;margin:0 0 8px;font-family:var(--s-serif);font-size:24px;line-height:1.2;color:var(--s-encre)}
	.s-auj-led{width:12px;height:12px;border-radius:50%;box-shadow:inset 0 0 0 2px var(--s-doux);flex-shrink:0}
	.s-auj-etat.is-ouvert .s-auj-led{background:var(--s-vert);box-shadow:0 0 0 4px color-mix(in srgb,var(--s-vert) 18%,transparent)}
	.s-auj-detail{margin:2px 0;color:var(--s-texte)}
	.s-auj-liste{margin:0 0 10px}.s-auj-liste li{display:flex;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px solid var(--s-filet)}
	.s-auj-liste span{color:var(--s-doux);white-space:nowrap;font-size:12px}
	.s-auj-epuises{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px;margin:0 0 18px;padding:0;border:0}
	.s-auj-epuises legend{font-weight:600;margin-bottom:8px;color:var(--s-encre)}
	.s-auj-case{display:flex;align-items:center;gap:10px;min-height:44px;padding:6px 12px;border:1px solid var(--s-filet);border-radius:4px;background:#fff;cursor:pointer}
	.s-auj-case:has(input:checked){border-color:#b32d2e;background:#fcf0f1}
	.s-auj-case:has(input:checked) span{text-decoration:line-through;color:#8a2424}
	.s-auj-annonces{margin:0 0 18px}.s-auj-annonces li{display:grid;grid-template-columns:auto 1fr;gap:2px 14px;padding:10px 0;border-bottom:1px solid var(--s-filet)}
	.s-auj-annonce-texte{font-weight:600;color:var(--s-encre)}.s-auj-annonce-dates{grid-column:2;color:var(--s-doux);font-size:12px}
	.s-auj-annonces label{grid-row:span 2;align-self:center}
	.s-auj-barre{position:sticky;bottom:0;z-index:5;padding:14px 0;background:linear-gradient(transparent,#f0f0f1 30%)}
	.s-auj-barre .button-hero{min-width:220px}
	.s-auj-liens{margin-top:10px;color:var(--s-doux)}
	@media(max-width:782px){.s-auj-haut{grid-template-columns:1fr}.s-auj-barre .button-hero{width:100%}.s-auj .s-entete-logo{display:none}.s-auj .s-carte-corps{padding:16px}}
	</style>';
} );

/* Le gérant arrive directement sur « Aujourd'hui » après la connexion. */
add_filter( 'login_redirect', function ( $url, $demande, $user ) {
	if ( $user instanceof WP_User && in_array( 'schiesser_gerant', (array) $user->roles, true ) && ( ! $demande || admin_url() === $demande || false !== strpos( (string) $demande, 'wp-admin/index.php' ) ) ) {
		return admin_url( 'admin.php?page=schiesser-aujourdhui' );
	}
	return $url;
}, 10, 3 );

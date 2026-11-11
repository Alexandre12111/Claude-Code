<?php
/**
 * Plugin Name:       Schiesser · Cookies et Google Tag Manager
 * Description:       Bandeau de consentement aux cookies (allemand, français, anglais), Google Tag Manager et Consent Mode v2 de Google. Les balises marketing (Google Ads) attendent l'accord du visiteur. Réglages : Réglages maison → Cookies et suivi.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Confiserie Schiesser
 * License:           GPL-2.0-or-later
 * Text Domain:       schiesser-cookies
 *
 * Fonctionnement :
 * - Dans le <head> de chaque page, avant tout autre script : Consent Mode v2 de Google avec tout
 *   refusé par défaut (publicité et statistiques), puis le choix déjà enregistré du visiteur.
 * - Mode « attendre » (par défaut) : Google Tag Manager n'est chargé qu'après un accord
 *   (marketing ou statistiques). Sans accord, aucune requête vers Google.
 * - Mode « avancé » : Google Tag Manager est chargé tout de suite, les balises Google fonctionnent
 *   sans cookie tant que le visiteur n'a pas accepté (mesure modélisée de Google Ads).
 * - Le choix est gardé dans un cookie nécessaire « schiesser_consentement » (durée réglable).
 * - Tout est décidé dans le navigateur : compatible avec le cache de page (WP Super Cache).
 * - Clics utiles pour Google Ads envoyés au dataLayer : appel, e-mail, itinéraire, WhatsApp.
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_COOKIES_VERSION = '1.0.0';
const SCHIESSER_COOKIES_OPTION  = 'schiesser_cookies';

/* ------------------------------------------------------------------ */
/* Réglages                                                            */
/* ------------------------------------------------------------------ */

function schiesser_cookies_langues() {
	return array( 'de' => 'Allemand', 'fr' => 'Français', 'en' => 'Anglais' );
}

/** Textes du bandeau : clé => [ libellé admin, de, fr, en, type ]. */
function schiesser_cookies_champs_textes() {
	return array(
		'titre'      => array( 'Titre', 'Cookies auf dieser Website', 'Cookies sur ce site', 'Cookies on this website', 'input' ),
		'texte'      => array( 'Texte', 'Wir verwenden notwendige Cookies, damit die Website funktioniert. Mit Ihrer Einwilligung nutzen wir zusätzlich Cookies von Google, um unsere Werbung zu messen und zu verbessern. Sie können Ihre Wahl jederzeit unter «Cookie-Einstellungen» am Seitenende ändern.', 'Nous utilisons des cookies nécessaires au fonctionnement du site. Avec votre accord, nous utilisons aussi des cookies de Google pour mesurer et améliorer nos annonces. Vous pouvez modifier votre choix à tout moment via « Paramètres des cookies » en bas de page.', 'We use necessary cookies to make this website work. With your consent, we also use cookies from Google to measure and improve our advertising. You can change your choice at any time via “Cookie settings” at the bottom of the page.', 'textarea' ),
		'accepter'   => array( 'Bouton « tout accepter »', 'Alle akzeptieren', 'Tout accepter', 'Accept all', 'input' ),
		'refuser'    => array( 'Bouton « refuser »', 'Nur notwendige', 'Nécessaires uniquement', 'Necessary only', 'input' ),
		'choisir'    => array( 'Bouton « personnaliser »', 'Einstellungen', 'Personnaliser', 'Customise', 'input' ),
		'enregistrer'=> array( 'Bouton « enregistrer »', 'Auswahl speichern', 'Enregistrer mon choix', 'Save my choice', 'input' ),
		'nec_titre'  => array( 'Nécessaires : titre', 'Notwendig', 'Nécessaires', 'Necessary', 'input' ),
		'nec_texte'  => array( 'Nécessaires : texte', 'Sprache, Ihre Cookie-Wahl und die Sicherheit der Website.', 'Langue, votre choix concernant les cookies et sécurité du site.', 'Language, your cookie choice and website security.', 'input' ),
		'toujours'   => array( 'Mention « toujours actifs »', 'Immer aktiv', 'Toujours actifs', 'Always active', 'input' ),
		'stats_titre'=> array( 'Statistiques : titre', 'Statistik', 'Statistiques', 'Statistics', 'input' ),
		'stats_texte'=> array( 'Statistiques : texte', 'Hilft uns zu verstehen, wie die Website genutzt wird (Google Analytics).', 'Nous aide à comprendre comment le site est utilisé (Google Analytics).', 'Helps us understand how the website is used (Google Analytics).', 'input' ),
		'mark_titre' => array( 'Marketing : titre', 'Marketing', 'Marketing', 'Marketing', 'input' ),
		'mark_texte' => array( 'Marketing : texte', 'Misst den Erfolg unserer Anzeigen bei Google und ermöglicht passende Werbung (Google Ads).', 'Mesure l’efficacité de nos annonces sur Google et permet une publicité adaptée (Google Ads).', 'Measures the success of our Google ads and enables relevant advertising (Google Ads).', 'input' ),
		'lien'       => array( 'Lien vers la politique', 'Datenschutzerklärung', 'Politique de confidentialité', 'Privacy policy', 'input' ),
		'pied'       => array( 'Lien du pied de page', 'Cookie-Einstellungen', 'Paramètres des cookies', 'Cookie settings', 'input' ),
	);
}

function schiesser_cookies_defaut() {
	$textes = array();
	foreach ( schiesser_cookies_champs_textes() as $k => $c ) {
		$textes[ $k ] = array( 'de' => $c[1], 'fr' => $c[2], 'en' => $c[3] );
	}
	return array(
		'actif'       => 1,
		'gtm'         => 'GTM-W4PXVFCR',
		'mode'        => 'attendre',
		'duree'       => 180,
		'stats'       => 0,
		'marketing'   => 1,
		'evenements'  => 1,
		'textes'      => $textes,
		'version'     => 1,
	);
}

function schiesser_cookies_reglages() {
	$d = schiesser_cookies_defaut();
	$r = wp_parse_args( (array) get_option( SCHIESSER_COOKIES_OPTION, array() ), $d );
	foreach ( $d['textes'] as $k => $v ) {
		$r['textes'][ $k ] = wp_parse_args( (array) ( $r['textes'][ $k ] ?? array() ), $v );
	}
	return $r;
}

function schiesser_cookies_langue() {
	if ( function_exists( 'schiesser_langue' ) ) {
		return schiesser_langue();
	}
	$l = function_exists( 'pll_current_language' ) ? substr( (string) pll_current_language( 'slug' ), 0, 2 ) : '';
	return isset( schiesser_cookies_langues()[ $l ] ) ? $l : 'de';
}

/** Texte dans la langue de la page, repli sur l'allemand. */
function schiesser_cookies_t( $r, $cle, $l ) {
	$v = trim( (string) ( $r['textes'][ $cle ][ $l ] ?? '' ) );
	return '' !== $v ? $v : trim( (string) ( $r['textes'][ $cle ]['de'] ?? '' ) );
}

function schiesser_cookies_nettoyer( $in ) {
	$in  = (array) $in;
	$old = schiesser_cookies_reglages();
	$gtm = strtoupper( trim( (string) ( $in['gtm'] ?? '' ) ) );
	$out = array(
		'actif'      => empty( $in['actif'] ) ? 0 : 1,
		'gtm'        => preg_match( '/^GTM-[A-Z0-9]{4,12}$/', $gtm ) ? $gtm : '',
		'mode'       => 'avance' === ( $in['mode'] ?? '' ) ? 'avance' : 'attendre',
		'duree'      => max( 30, min( 395, (int) ( $in['duree'] ?? 180 ) ) ),
		'stats'      => empty( $in['stats'] ) ? 0 : 1,
		'marketing'  => empty( $in['marketing'] ) ? 0 : 1,
		'evenements' => empty( $in['evenements'] ) ? 0 : 1,
		// WordPress peut nettoyer deux fois au premier enregistrement : on garde la plus grande version vue.
		'version'    => max( (int) $old['version'], (int) ( $in['version'] ?? 0 ) ),
	);
	foreach ( schiesser_cookies_champs_textes() as $k => $c ) {
		foreach ( schiesser_cookies_langues() as $l => $nom ) {
			$v = $in['textes'][ $k ][ $l ] ?? '';
			$out['textes'][ $k ][ $l ] = 'textarea' === $c[4] ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
		}
	}
	if ( ! empty( $in['remettre'] ) ) {
		$out['version']++; // tous les visiteurs doivent choisir de nouveau
	}
	return $out;
}

add_action( 'admin_init', function () {
	register_setting( 'schiesser_cookies_groupe', SCHIESSER_COOKIES_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'schiesser_cookies_nettoyer',
		'default'           => schiesser_cookies_defaut(),
	) );
} );

add_action( 'update_option_' . SCHIESSER_COOKIES_OPTION, function () {
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache(); // le bandeau et le code GTM sont dans les pages en cache
	}
} );

/* ------------------------------------------------------------------ */
/* Écran d'administration                                              */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	global $admin_page_hooks;
	$parent = isset( $admin_page_hooks['schiesser-reglages'] ) ? 'schiesser-reglages' : 'options-general.php';
	add_submenu_page( $parent, 'Cookies et suivi', 'Cookies et suivi', 'manage_options', 'schiesser-cookies', 'schiesser_cookies_page' );
}, 21 );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $liens ) {
	array_unshift( $liens, '<a href="' . esc_url( admin_url( 'admin.php?page=schiesser-cookies' ) ) . '">Réglages</a>' );
	return $liens;
} );

function schiesser_cookies_page() {
	$r = schiesser_cookies_reglages();
	$n = SCHIESSER_COOKIES_OPTION;
	?>
	<div class="wrap">
		<h1>Cookies et suivi</h1>
		<p style="max-width:760px">Le bandeau demande l’accord des visiteurs avant toute balise publicitaire. Google Tag Manager reçoit leur choix avec le <strong>Consent Mode v2</strong> de Google (obligatoire pour Google Ads en Suisse et dans l’Union européenne). Le choix est gardé dans un cookie nécessaire, et un lien du pied de page permet de le modifier.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'schiesser_cookies_groupe' ); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Module</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[actif]" value="1" <?php checked( $r['actif'] ); ?>> Afficher le bandeau et charger Google Tag Manager</label></td></tr>
				<tr><th scope="row"><label for="sc-gtm">Conteneur Google Tag Manager</label></th><td><input id="sc-gtm" type="text" class="regular-text code" name="<?php echo esc_attr( $n ); ?>[gtm]" value="<?php echo esc_attr( $r['gtm'] ); ?>" placeholder="GTM-XXXXXXX"><p class="description">Identifiant du conteneur (Google Tag Manager → Administration). Vide : pas de Google Tag Manager, le bandeau reste.</p></td></tr>
				<tr><th scope="row">Chargement de Google</th><td>
					<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[mode]" value="attendre" <?php checked( $r['mode'], 'attendre' ); ?>> <strong>Attendre l’accord</strong> (conseillé) : Google Tag Manager n’est chargé qu’après un « oui ». Sans accord, aucune donnée ne part vers Google.</label><br>
					<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[mode]" value="avance" <?php checked( $r['mode'], 'avance' ); ?>> <strong>Consent Mode avancé</strong> : Google Tag Manager est chargé tout de suite, les balises Google envoient des signaux sans cookie tant que le visiteur n’a pas accepté (meilleures statistiques Google Ads, mais des données partent vers Google avant l’accord). À mentionner dans la politique de confidentialité.</label>
				</td></tr>
				<tr><th scope="row">Catégories proposées</th><td>
					<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[marketing]" value="1" <?php checked( $r['marketing'] ); ?>> Marketing (Google Ads : <code>ad_storage</code>, <code>ad_user_data</code>, <code>ad_personalization</code>)</label><br>
					<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[stats]" value="1" <?php checked( $r['stats'] ); ?>> Statistiques (Google Analytics : <code>analytics_storage</code>), à cocher seulement si Google Analytics est ajouté dans Google Tag Manager</label>
				</td></tr>
				<tr><th scope="row"><label for="sc-duree">Durée du choix</label></th><td><input id="sc-duree" type="number" min="30" max="395" class="small-text" name="<?php echo esc_attr( $n ); ?>[duree]" value="<?php echo esc_attr( $r['duree'] ); ?>"> jours, puis le bandeau revient (6 mois conseillés, 13 mois au plus)</td></tr>
				<tr><th scope="row">Conversions</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[evenements]" value="1" <?php checked( $r['evenements'] ); ?>> Envoyer à Google Tag Manager les clics utiles : <code>clic_telephone</code>, <code>clic_email</code>, <code>clic_itineraire</code>, <code>clic_whatsapp</code></label><p class="description">Ces événements servent de déclencheurs pour les conversions Google Ads. Ils ne sont transmis à Google que si le visiteur a accepté.</p></td></tr>
				<tr><th scope="row">Redemander</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[remettre]" value="1"> Redemander leur choix à tous les visiteurs (par exemple après l’ajout d’un nouveau service)</label></td></tr>
			</table>
			<h2 class="title">Textes du bandeau</h2>
			<table class="widefat striped" style="max-width:1150px">
				<thead><tr><th style="width:170px"></th><?php foreach ( schiesser_cookies_langues() as $l => $nom ) : ?><th><?php echo esc_html( $nom ); ?></th><?php endforeach; ?></tr></thead>
				<tbody>
				<?php foreach ( schiesser_cookies_champs_textes() as $k => $c ) : ?>
					<tr><th scope="row"><?php echo esc_html( $c[0] ); ?></th>
					<?php foreach ( schiesser_cookies_langues() as $l => $nom ) : ?>
						<td>
						<?php if ( 'textarea' === $c[4] ) : ?>
							<textarea class="widefat" rows="4" aria-label="<?php echo esc_attr( $c[0] . ' ' . $nom ); ?>" name="<?php echo esc_attr( $n . '[textes][' . $k . '][' . $l . ']' ); ?>"><?php echo esc_textarea( $r['textes'][ $k ][ $l ] ); ?></textarea>
						<?php else : ?>
							<input type="text" class="widefat" aria-label="<?php echo esc_attr( $c[0] . ' ' . $nom ); ?>" name="<?php echo esc_attr( $n . '[textes][' . $k . '][' . $l . ']' ); ?>" value="<?php echo esc_attr( $r['textes'][ $k ][ $l ] ); ?>">
						<?php endif; ?>
						</td>
					<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">Une case vide en français ou en anglais reprend le texte allemand.</p>
			<?php submit_button( 'Enregistrer' ); ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------ */
/* Site                                                                */
/* ------------------------------------------------------------------ */

function schiesser_cookies_actif() {
	if ( is_admin() || is_feed() || is_embed() || wp_doing_ajax() ) {
		return false;
	}
	return (bool) schiesser_cookies_reglages()['actif'];
}

/** Réglages transmis au navigateur. */
function schiesser_cookies_config( $r ) {
	return array(
		'gtm'   => $r['gtm'],
		'mode'  => $r['mode'],
		'v'     => (int) $r['version'],
		'duree' => (int) $r['duree'],
		'stats' => (int) $r['stats'],
		'mark'  => (int) $r['marketing'],
		'evt'   => (int) $r['evenements'],
	);
}

/*
 * Tout en haut du <head> : Consent Mode v2 (tout refusé par défaut), choix déjà enregistré,
 * puis Google Tag Manager si le mode ou le choix le permet.
 */
add_action( 'wp_head', function () {
	if ( ! schiesser_cookies_actif() ) {
		return;
	}
	$c = schiesser_cookies_config( schiesser_cookies_reglages() );
	?>
<script id="schiesser-consentement">
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});
gtag('set','ads_data_redaction',true);
(function(C){
var S={C:C,charge:false};window.schiesserCookies=S;
S.lire=function(){var m=document.cookie.match(/(?:^|;\s*)schiesser_consentement=([^;]+)/);if(!m)return null;try{var o=JSON.parse(decodeURIComponent(m[1]));return o&&o.v===C.v?o:null;}catch(e){return null;}};
S.signaux=function(o){var m=o&&o.m&&C.mark?'granted':'denied',s=o&&o.s&&C.stats?'granted':'denied';return{ad_storage:m,ad_user_data:m,ad_personalization:m,analytics_storage:s};};
S.charger=function(){if(S.charge||!C.gtm)return;S.charge=true;dataLayer.push({'gtm.start':new Date().getTime(),event:'gtm.js'});var j=document.createElement('script');j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+C.gtm;var f=document.getElementsByTagName('script')[0];f.parentNode.insertBefore(j,f);};
var o=S.lire();S.choix=o;
if(o){gtag('consent','update',S.signaux(o));}else{document.documentElement.classList.add('cookies-attente');}
if(C.mode==='avance'||(o&&((o.m&&C.mark)||(o.s&&C.stats))))S.charger();
})(<?php echo wp_json_encode( $c ); ?>);
</script>
	<?php
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! schiesser_cookies_actif() ) {
		return;
	}
	$base = plugin_dir_url( __FILE__ ) . 'assets/';
	wp_enqueue_style( 'schiesser-cookies', $base . 'cookies.css', array(), SCHIESSER_COOKIES_VERSION );
	wp_enqueue_script( 'schiesser-cookies', $base . 'cookies.js', array(), SCHIESSER_COOKIES_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
} );

/** Adresse de la politique de confidentialité dans la langue de la page. */
function schiesser_cookies_url_politique() {
	if ( function_exists( 'schiesser_page' ) ) {
		$p = schiesser_page( 'datenschutz' );
		if ( $p ) {
			return get_permalink( $p );
		}
	}
	return get_privacy_policy_url();
}

/** Lien « Cookie-Einstellungen » (pied de page du thème, ou n'importe où avec le code court). */
function schiesser_cookies_lien() {
	if ( ! schiesser_cookies_actif() ) {
		return '';
	}
	$r = schiesser_cookies_reglages();
	return '<a href="#cookie-einstellungen" class="js-cookies-reglages" role="button">' . esc_html( schiesser_cookies_t( $r, 'pied', schiesser_cookies_langue() ) ) . '</a>';
}
add_action( 'schiesser_liens_legaux', function () {
	echo schiesser_cookies_lien(); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans la fonction
} );
add_shortcode( 'schiesser_cookies', 'schiesser_cookies_lien' );

add_action( 'wp_footer', function () {
	if ( ! schiesser_cookies_actif() ) {
		return;
	}
	$r   = schiesser_cookies_reglages();
	$l   = schiesser_cookies_langue();
	$t   = function ( $k ) use ( $r, $l ) {
		return esc_html( schiesser_cookies_t( $r, $k, $l ) );
	};
	$url = schiesser_cookies_url_politique();
	?>
<section class="sck" id="sck" hidden aria-labelledby="sck-titre" data-nosnippet>
	<div class="sck-boite">
		<p class="sck-titre" id="sck-titre"><?php echo $t( 'titre' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		<p class="sck-texte"><?php echo $t( 'texte' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php if ( $url ) : ?> <a href="<?php echo esc_url( $url ); ?>"><?php echo $t( 'lien' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php endif; ?></p>
		<div class="sck-choix" id="sck-choix" hidden>
			<div class="sck-cat"><div><strong><?php echo $t( 'nec_titre' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong><span><?php echo $t( 'nec_texte' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></div><em><?php echo $t( 'toujours' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></em></div>
			<?php if ( $r['stats'] ) : ?>
			<label class="sck-cat"><div><strong><?php echo $t( 'stats_titre' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong><span><?php echo $t( 'stats_texte' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></div><input type="checkbox" class="sck-bascule" data-cat="s"></label>
			<?php endif; ?>
			<?php if ( $r['marketing'] ) : ?>
			<label class="sck-cat"><div><strong><?php echo $t( 'mark_titre' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong><span><?php echo $t( 'mark_texte' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></div><input type="checkbox" class="sck-bascule" data-cat="m"></label>
			<?php endif; ?>
		</div>
		<div class="sck-boutons">
			<button type="button" class="sck-b sck-b--plein" data-sck="tout"><?php echo $t( 'accepter' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<button type="button" class="sck-b sck-b--plein" data-sck="rien"><?php echo $t( 'refuser' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<button type="button" class="sck-b sck-b--lien" data-sck="choisir" aria-expanded="false" aria-controls="sck-choix"><?php echo $t( 'choisir' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<button type="button" class="sck-b sck-b--plein" data-sck="enregistrer" hidden><?php echo $t( 'enregistrer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</div>
	</div>
</section>
	<?php
}, 5 );

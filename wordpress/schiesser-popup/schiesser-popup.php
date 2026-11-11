<?php
/**
 * Plugin Name:       Schiesser · Fenêtre des horaires
 * Description:       Petite fenêtre qui affiche les heures d'ouverture de la confiserie à l'arrivée sur le site. Titre, texte, fréquence d'apparition, délai et période se règlent dans Réglages maison → Fenêtre des horaires.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Confiserie Schiesser
 * License:           GPL-2.0-or-later
 * Text Domain:       schiesser-popup
 *
 * Les horaires viennent des Réglages maison du thème Schiesser (jours de la semaine, jours fériés,
 * remarque) : une seule saisie pour tout le site. Sans le thème, un texte libre peut les remplacer.
 *
 * La fenêtre est écrite dans chaque page et c'est le navigateur qui décide de l'afficher : elle reste
 * correcte avec le cache de page (WP Super Cache). Le navigateur retient seulement la date du dernier
 * affichage (stockage local, aucun cookie, aucune donnée personnelle, rien n'est envoyé au serveur).
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_POPUP_VERSION = '1.1.0';
const SCHIESSER_POPUP_OPTION  = 'schiesser_popup';

/* ------------------------------------------------------------------ */
/* Réglages                                                            */
/* ------------------------------------------------------------------ */

function schiesser_popup_langues() {
	return array( 'de' => 'Allemand', 'fr' => 'Français', 'en' => 'Anglais' );
}

function schiesser_popup_frequences() {
	return array(
		'session'  => 'Une fois par visite (jusqu’à la fermeture du navigateur)',
		'jour'     => 'Une fois par jour',
		'jours'    => 'Une fois tous les … jours',
		'une_fois' => 'Une seule fois (jusqu’à la prochaine remise à zéro)',
		'toujours' => 'À chaque page (pour tester seulement)',
	);
}

function schiesser_popup_defaut() {
	return array(
		'actif'     => 1,
		'frequence' => 'jour',
		'jours'     => 7,
		'delai'     => 2,
		'pages'     => 'tout',
		'du'        => '',
		'au'        => '',
		'source'    => 'auto',
		'titre'     => array( 'de' => 'Öffnungszeiten', 'fr' => 'Horaires d’ouverture', 'en' => 'Opening hours' ),
		'texte'     => array( 'de' => '', 'fr' => '', 'en' => '' ),
		'libre'     => array( 'de' => '', 'fr' => '', 'en' => '' ),
		'bouton'    => array( 'de' => 'Schliessen', 'fr' => 'Fermer', 'en' => 'Close' ),
		'version'   => 1,
	);
}

function schiesser_popup_reglages() {
	$r = wp_parse_args( (array) get_option( SCHIESSER_POPUP_OPTION, array() ), schiesser_popup_defaut() );
	foreach ( array( 'titre', 'texte', 'libre', 'bouton' ) as $k ) {
		$r[ $k ] = wp_parse_args( (array) $r[ $k ], schiesser_popup_defaut()[ $k ] );
	}
	return $r;
}

/** Langue de la page affichée : de, fr ou en. */
function schiesser_popup_langue() {
	if ( function_exists( 'schiesser_langue' ) ) {
		return schiesser_langue();
	}
	$l = function_exists( 'pll_current_language' ) ? substr( (string) pll_current_language( 'slug' ), 0, 2 ) : '';
	return isset( schiesser_popup_langues()[ $l ] ) ? $l : 'de';
}

/** Texte dans la langue de la page, repli sur l'allemand. */
function schiesser_popup_texte( $r, $cle, $l ) {
	$v = trim( (string) ( $r[ $cle ][ $l ] ?? '' ) );
	return '' !== $v ? $v : trim( (string) ( $r[ $cle ]['de'] ?? '' ) );
}

function schiesser_popup_nettoyer( $in ) {
	$in  = (array) $in;
	$old = schiesser_popup_reglages();
	$d   = schiesser_popup_defaut();
	$out = array(
		'actif'     => empty( $in['actif'] ) ? 0 : 1,
		'frequence' => isset( schiesser_popup_frequences()[ $in['frequence'] ?? '' ] ) ? $in['frequence'] : $d['frequence'],
		'jours'     => max( 1, min( 365, (int) ( $in['jours'] ?? $d['jours'] ) ) ),
		'delai'     => max( 0, min( 30, (int) ( $in['delai'] ?? $d['delai'] ) ) ),
		'pages'     => in_array( $in['pages'] ?? '', array( 'tout', 'accueil' ), true ) ? $in['pages'] : 'tout',
		'du'        => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $in['du'] ?? '' ) ) ? $in['du'] : '',
		'au'        => preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $in['au'] ?? '' ) ) ? $in['au'] : '',
		'source'    => ( 'libre' === ( $in['source'] ?? '' ) ) ? 'libre' : 'auto',
		// WordPress peut nettoyer deux fois au premier enregistrement : on garde la plus grande version vue.
		'version'   => max( (int) $old['version'], (int) ( $in['version'] ?? 0 ) ),
	);
	foreach ( schiesser_popup_langues() as $l => $nom ) {
		$out['titre'][ $l ]  = sanitize_text_field( $in['titre'][ $l ] ?? '' );
		$out['texte'][ $l ]  = sanitize_textarea_field( $in['texte'][ $l ] ?? '' );
		$out['libre'][ $l ]  = sanitize_textarea_field( $in['libre'][ $l ] ?? '' );
		$out['bouton'][ $l ] = sanitize_text_field( $in['bouton'][ $l ] ?? '' );
	}
	if ( ! empty( $in['remettre'] ) ) {
		$out['version']++; // tous les visiteurs revoient la fenêtre
	}
	return $out;
}

add_action( 'admin_init', function () {
	register_setting( 'schiesser_popup_groupe', SCHIESSER_POPUP_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'schiesser_popup_nettoyer',
		'default'           => schiesser_popup_defaut(),
	) );
} );

// Réglages enregistrés : la fenêtre est écrite dans les pages en cache, on vide le cache.
add_action( 'update_option_' . SCHIESSER_POPUP_OPTION, function () {
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}
} );

/* ------------------------------------------------------------------ */
/* Écran d'administration                                              */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	global $admin_page_hooks;
	$parent = isset( $admin_page_hooks['schiesser-reglages'] ) ? 'schiesser-reglages' : 'options-general.php';
	add_submenu_page( $parent, 'Fenêtre des horaires', 'Fenêtre des horaires', 'edit_pages', 'schiesser-popup', 'schiesser_popup_page' );
}, 20 );

// Les responsables de contenu (edit_pages) peuvent enregistrer, comme pour les Réglages maison.
add_filter( 'option_page_capability_schiesser_popup_groupe', function () {
	return 'edit_pages';
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $liens ) {
	array_unshift( $liens, '<a href="' . esc_url( admin_url( 'admin.php?page=schiesser-popup' ) ) . '">Réglages</a>' );
	return $liens;
} );

function schiesser_popup_page() {
	$r     = schiesser_popup_reglages();
	$n     = SCHIESSER_POPUP_OPTION;
	$theme = function_exists( 'schiesser_reglage' );
	?>
	<div class="wrap">
		<h1>Fenêtre des horaires</h1>
		<p style="max-width:720px">Une petite fenêtre montre les heures d’ouverture à l’arrivée sur le site. Elle se ferme avec la croix, le bouton, la touche Échap ou un clic à côté. Le navigateur du visiteur retient seulement quand il l’a vue (sans cookie), pour respecter la fréquence choisie.</p>
		<p><a class="button" href="<?php echo esc_url( add_query_arg( 'apercu_horaires', '1', home_url( '/' ) ) ); ?>" target="_blank" rel="noopener">Voir la fenêtre sur le site</a></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'schiesser_popup_groupe' ); ?>
			<h2 class="title">Affichage</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Fenêtre</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[actif]" value="1" <?php checked( $r['actif'] ); ?>> Afficher la fenêtre des horaires sur le site</label></td></tr>
				<tr><th scope="row"><label for="sp-freq">Fréquence</label></th><td>
					<select id="sp-freq" name="<?php echo esc_attr( $n ); ?>[frequence]">
						<?php foreach ( schiesser_popup_frequences() as $k => $lib ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $r['frequence'], $k ); ?>><?php echo esc_html( $lib ); ?></option>
						<?php endforeach; ?>
					</select>
					<label style="margin-left:8px">Tous les <input type="number" min="1" max="365" class="small-text" name="<?php echo esc_attr( $n ); ?>[jours]" value="<?php echo esc_attr( $r['jours'] ); ?>"> jours</label>
					<p class="description">« Tous les … jours » utilise le nombre de jours à droite. « À chaque page » est fait pour vérifier un changement, pas pour les visiteurs.</p>
				</td></tr>
				<tr><th scope="row"><label for="sp-delai">Délai d’apparition</label></th><td><input id="sp-delai" type="number" min="0" max="30" class="small-text" name="<?php echo esc_attr( $n ); ?>[delai]" value="<?php echo esc_attr( $r['delai'] ); ?>"> secondes après l’ouverture de la page</td></tr>
				<tr><th scope="row">Pages</th><td>
					<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[pages]" value="tout" <?php checked( $r['pages'], 'tout' ); ?>> Sur tout le site (à la première page vue)</label><br>
					<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[pages]" value="accueil" <?php checked( $r['pages'], 'accueil' ); ?>> Seulement sur la page d’accueil</label>
				</td></tr>
				<tr><th scope="row">Période</th><td>
					du <input type="date" name="<?php echo esc_attr( $n ); ?>[du]" value="<?php echo esc_attr( $r['du'] ); ?>"> au <input type="date" name="<?php echo esc_attr( $n ); ?>[au]" value="<?php echo esc_attr( $r['au'] ); ?>">
					<p class="description">Facultatif, par exemple pour annoncer les horaires des fêtes. Vide : toute l’année.</p>
				</td></tr>
				<tr><th scope="row">Remise à zéro</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[remettre]" value="1"> Montrer de nouveau la fenêtre à tous les visiteurs (par exemple après un changement d’horaires)</label></td></tr>
			</table>

			<h2 class="title">Contenu</h2>
			<table class="form-table" role="presentation">
				<tr><th scope="row">Horaires</th><td>
					<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[source]" value="auto" <?php checked( $r['source'], 'auto' ); ?> <?php disabled( ! $theme ); ?>> Horaires des Réglages maison (jours, jours fériés à venir et remarque), traduits automatiquement</label><br>
					<label><input type="radio" name="<?php echo esc_attr( $n ); ?>[source]" value="libre" <?php checked( $r['source'] === 'libre' || ! $theme ); ?>> Texte libre (ci-dessous, une ligne par jour)</label>
					<?php if ( $theme ) : ?><p class="description">Pour changer les heures : <a href="<?php echo esc_url( admin_url( 'admin.php?page=schiesser-reglages' ) ); ?>">Réglages maison</a>. La fenêtre suit automatiquement.</p><?php endif; ?>
				</td></tr>
			</table>
			<table class="widefat striped" style="max-width:1100px">
				<thead><tr><th style="width:150px"></th><?php foreach ( schiesser_popup_langues() as $l => $nom ) : ?><th><?php echo esc_html( $nom ); ?></th><?php endforeach; ?></tr></thead>
				<tbody>
				<?php
				$champs = array(
					'titre'  => array( 'Titre', 'input' ),
					'texte'  => array( 'Petit texte (facultatif)', 'textarea' ),
					'libre'  => array( 'Horaires en texte libre', 'textarea' ),
					'bouton' => array( 'Bouton', 'input' ),
				);
				foreach ( $champs as $k => $c ) :
					?>
					<tr><th scope="row"><?php echo esc_html( $c[0] ); ?></th>
					<?php foreach ( schiesser_popup_langues() as $l => $nom ) : ?>
						<td>
						<?php if ( 'input' === $c[1] ) : ?>
							<input type="text" class="widefat" aria-label="<?php echo esc_attr( $c[0] . ' ' . $nom ); ?>" name="<?php echo esc_attr( $n . '[' . $k . '][' . $l . ']' ); ?>" value="<?php echo esc_attr( $r[ $k ][ $l ] ); ?>">
						<?php else : ?>
							<textarea class="widefat" rows="<?php echo 'libre' === $k ? 7 : 2; ?>" aria-label="<?php echo esc_attr( $c[0] . ' ' . $nom ); ?>" name="<?php echo esc_attr( $n . '[' . $k . '][' . $l . ']' ); ?>"><?php echo esc_textarea( $r[ $k ][ $l ] ); ?></textarea>
						<?php endif; ?>
						</td>
					<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">Une case vide en français ou en anglais reprend le texte allemand. Texte libre, exemple : « Montag bis Samstag: 8–18 Uhr » puis « Sonntag: 11.30–18 Uhr ».</p>
			<?php submit_button( 'Enregistrer' ); ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------ */
/* Site                                                                */
/* ------------------------------------------------------------------ */

/** Doit-on écrire la fenêtre dans cette page ? */
function schiesser_popup_a_afficher( $r ) {
	if ( is_admin() || is_feed() || is_embed() || is_customize_preview() || wp_doing_ajax() ) {
		return false;
	}
	if ( ! empty( $_GET['apercu_horaires'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return true;
	}
	if ( ! $r['actif'] || ( 'accueil' === $r['pages'] && ! is_front_page() ) ) {
		return false;
	}
	$jour = current_datetime()->format( 'Y-m-d' );
	return ! ( $r['au'] && $jour > $r['au'] ); // période terminée (le début est vérifié par le navigateur)
}

/** Liste des horaires, depuis les Réglages maison du thème. */
function schiesser_popup_html_horaires( $r, $l ) {
	if ( 'libre' === $r['source'] || ! function_exists( 'schiesser_reglage' ) || ! function_exists( 'schiesser_jours_site' ) ) {
		$lignes = array_filter( array_map( 'trim', explode( "\n", schiesser_popup_texte( $r, 'libre', $l ) ) ) );
		if ( ! $lignes ) {
			return '';
		}
		$html = '<ul class="spop-libre">';
		foreach ( $lignes as $ligne ) {
			$p     = preg_split( '/\s*:\s+/u', $ligne, 2 ); // « Montag: 8–18 Uhr » → deux colonnes
			$html .= 2 === count( $p ) ? '<li><span>' . esc_html( $p[0] ) . '</span><span>' . esc_html( $p[1] ) . '</span></li>' : '<li><span>' . esc_html( $ligne ) . '</span></li>';
		}
		return $html . '</ul>';
	}
	$tous = (array) schiesser_reglage( 'horaires' );
	$t    = function ( $s ) use ( $l ) {
		return function_exists( 'schiesser_t' ) ? schiesser_t( $s, $l ) : $s;
	};
	$html = '<dl class="spop-liste">';
	foreach ( schiesser_jours_site( $l ) as $n => $jour ) {
		$h     = $tous[ $n ] ?? array();
		$texte = ! empty( $h['ferme'] ) || empty( $h['ouverture'] )
			? esc_html( $t( 'Geschlossen' ) )
			: esc_html( schiesser_uhr( $h['ouverture'], false, $l ) . '–' . schiesser_uhr( $h['fermeture'], true, $l ) );
		$html .= '<div class="spop-j" data-j="' . (int) $n . '"><dt>' . esc_html( $jour ) . '<span class="spop-auj">' . esc_html( $t( 'Heute' ) ) . '</span></dt><dd>' . $texte . '</dd></div>';
	}
	$html .= '</dl>';
	if ( function_exists( 'schiesser_html_jours_particuliers' ) ) {
		$html .= schiesser_html_jours_particuliers( 21, 3 ); // fêtes des trois prochaines semaines
	}
	$note = schiesser_reglage( 'horaires_note' );
	if ( $note ) {
		$html .= '<p class="spop-note">' . esc_html( $note ) . '</p>';
	}
	return $html;
}

add_action( 'wp_footer', function () {
	$r = schiesser_popup_reglages();
	if ( ! schiesser_popup_a_afficher( $r ) ) {
		return;
	}
	$l        = schiesser_popup_langue();
	$horaires = schiesser_popup_html_horaires( $r, $l );
	if ( '' === $horaires ) {
		return;
	}
	$titre  = schiesser_popup_texte( $r, 'titre', $l );
	$texte  = schiesser_popup_texte( $r, 'texte', $l );
	$bouton = schiesser_popup_texte( $r, 'bouton', $l );
	$fermer = array( 'de' => 'Schliessen', 'fr' => 'Fermer', 'en' => 'Close' )[ $l ] ?? 'Schliessen';
	$donnees = array(
		'freq'  => $r['frequence'],
		'jours' => (int) $r['jours'],
		'delai' => (int) $r['delai'],
		'v'     => (int) $r['version'],
		'du'    => $r['du'],
		'au'    => $r['au'],
	);
	?>
<div class="spop" id="spop" hidden data-reglages="<?php echo esc_attr( wp_json_encode( $donnees ) ); ?>" data-nosnippet>
	<div class="spop-fond" data-fermer></div>
	<div class="spop-boite" role="dialog" aria-modal="true" aria-labelledby="spop-titre" tabindex="-1">
		<button type="button" class="spop-x" data-fermer aria-label="<?php echo esc_attr( $fermer ); ?>"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none"/></svg></button>
		<p class="spop-titre" id="spop-titre"><?php echo esc_html( $titre ); ?></p>
		<?php if ( $texte ) : ?><p class="spop-texte"><?php echo nl2br( esc_html( $texte ) ); ?></p><?php endif; ?>
		<div class="spop-horaires"><?php echo $horaires; // phpcs:ignore WordPress.Security.EscapeOutput -- échappé plus haut ?></div>
		<?php if ( $bouton ) : ?><button type="button" class="spop-ok" data-fermer><?php echo esc_html( $bouton ); ?></button><?php endif; ?>
	</div>
</div>
	<?php
}, 30 );

add_action( 'wp_enqueue_scripts', function () {
	$r = schiesser_popup_reglages();
	if ( ! schiesser_popup_a_afficher( $r ) ) {
		return;
	}
	$base = plugin_dir_url( __FILE__ ) . 'assets/';
	wp_enqueue_style( 'schiesser-popup', $base . 'popup.css', array(), SCHIESSER_POPUP_VERSION );
	wp_enqueue_script( 'schiesser-popup', $base . 'popup.js', array(), SCHIESSER_POPUP_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
}, 30 );

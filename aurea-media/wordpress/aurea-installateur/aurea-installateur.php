<?php
/**
 * Plugin Name:       Aurea Installateur
 * Description:       Installe le thème Aurea Media et l'extension Aurea Studio même quand l'hébergeur limite la taille des fichiers envoyés (erreur « 413 Request Entity Too Large ») : le fichier est envoyé par petits morceaux, puis installé par WordPress.
 * Version:           1.0.1
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * Author URI:        https://aurea-media.fr/
 * License:           Propriétaire
 * Text Domain:       aurea-installateur
 *
 * Outils → Installer Aurea : choisir aurea.zip (et aurea-studio.zip), le reste est automatique.
 * Un thème ou une extension déjà présents sont remplacés par la nouvelle version (mise à jour).
 * L'extension peut être supprimée une fois l'installation terminée.
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_INST_PAGE', 'aurea-installateur' );

/** Dossier temporaire des fichiers reçus (protégé, vidé après chaque installation). */
function aurea_inst_dossier() {
	$dossier = trailingslashit( wp_upload_dir()['basedir'] ) . 'aurea-installateur/';
	if ( ! is_dir( $dossier ) ) {
		wp_mkdir_p( $dossier );
		file_put_contents( $dossier . 'index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		file_put_contents( $dossier . '.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $dossier;
}

function aurea_inst_autorise() {
	return current_user_can( 'install_themes' ) || current_user_can( 'install_plugins' );
}

/** Chemin du fichier en cours de réception, d'après le jeton envoyé par le navigateur. */
function aurea_inst_fichier( $jeton ) {
	return preg_match( '/^[a-f0-9]{24}$/', $jeton ) ? aurea_inst_dossier() . $jeton . '.zip' : '';
}

add_action( 'admin_menu', function () {
	add_management_page( 'Installer Aurea', 'Installer Aurea', 'install_themes', AUREA_INST_PAGE, 'aurea_inst_ecran' );
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $liens ) {
	array_unshift( $liens, '<a href="' . esc_url( admin_url( 'tools.php?page=' . AUREA_INST_PAGE ) ) . '">Installer Aurea</a>' );
	return $liens;
} );

/* ------------------------------------------------------------------ */
/* Réception par morceaux                                              */
/* ------------------------------------------------------------------ */

add_action( 'wp_ajax_aurea_inst_morceau', function () {
	check_ajax_referer( 'aurea_inst' );
	if ( ! aurea_inst_autorise() ) {
		wp_send_json_error( 'Vous n’avez pas le droit d’installer des thèmes ou des extensions.', 403 );
	}
	$fichier = aurea_inst_fichier( sanitize_key( wp_unslash( $_POST['jeton'] ?? '' ) ) );
	$debut   = isset( $_POST['debut'] ) ? (int) $_POST['debut'] : -1;
	if ( ! $fichier || $debut < 0 || empty( $_FILES['morceau']['tmp_name'] ) || ! is_uploaded_file( $_FILES['morceau']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		wp_send_json_error( 'Morceau de fichier invalide.' );
	}
	$recu = file_exists( $fichier ) ? filesize( $fichier ) : 0;
	if ( 0 === $debut ) {
		$recu = 0;
		file_put_contents( $fichier, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	} elseif ( $debut !== $recu ) {
		wp_send_json_success( array( 'recu' => $recu ) ); // le navigateur reprend où le serveur en est
	}
	$donnees = file_get_contents( $_FILES['morceau']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.Security.ValidatedSanitizedInput
	file_put_contents( $fichier, $donnees, FILE_APPEND ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	clearstatcache( true, $fichier );
	wp_send_json_success( array( 'recu' => filesize( $fichier ) ) );
} );

/* ------------------------------------------------------------------ */
/* Installation                                                        */
/* ------------------------------------------------------------------ */

/**
 * Lien d'activation signé, utilisable tel quel par le navigateur.
 * (wp_nonce_url() encode « & » en « &amp; » pour le HTML : dans un lien créé en JavaScript,
 * la clé de sécurité était alors perdue et WordPress répondait « Le lien suivi est expiré ».)
 */
function aurea_inst_lien( $url, $action ) {
	return add_query_arg( '_wpnonce', wp_create_nonce( $action ), $url );
}

/** Thème ou extension ? D'après le contenu de l'archive. */
function aurea_inst_type( $fichier ) {
	if ( class_exists( 'ZipArchive' ) ) {
		$zip = new ZipArchive();
		if ( true === $zip->open( $fichier ) ) {
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$nom = $zip->getNameIndex( $i );
				if ( preg_match( '#^[^/]+/style\.css$#', $nom ) && false !== strpos( (string) $zip->getFromIndex( $i, 8192 ), 'Theme Name:' ) ) {
					$zip->close();
					return 'theme';
				}
			}
			for ( $i = 0; $i < $zip->numFiles; $i++ ) {
				$nom = $zip->getNameIndex( $i );
				if ( preg_match( '#^[^/]+/[^/]+\.php$#', $nom ) && false !== strpos( (string) $zip->getFromIndex( $i, 8192 ), 'Plugin Name:' ) ) {
					$zip->close();
					return 'extension';
				}
			}
			$zip->close();
		}
		return '';
	}
	return 'inconnu'; // sans ZipArchive : on essaie thème puis extension
}

add_action( 'wp_ajax_aurea_inst_terminer', function () {
	check_ajax_referer( 'aurea_inst' );
	if ( ! aurea_inst_autorise() ) {
		wp_send_json_error( 'Vous n’avez pas le droit d’installer des thèmes ou des extensions.', 403 );
	}
	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
		wp_send_json_error( 'Votre site interdit l’installation de fichiers (réglage DISALLOW_FILE_MODS de wp-config.php).' );
	}
	$fichier = aurea_inst_fichier( sanitize_key( wp_unslash( $_POST['jeton'] ?? '' ) ) );
	$taille  = isset( $_POST['taille'] ) ? (int) $_POST['taille'] : 0;
	clearstatcache();
	if ( ! $fichier || ! file_exists( $fichier ) || filesize( $fichier ) !== $taille ) {
		wp_send_json_error( 'Le fichier n’est pas arrivé en entier. Recommencez l’envoi.' );
	}
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/theme.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	if ( ! WP_Filesystem() ) {
		@unlink( $fichier ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		wp_send_json_error( 'Votre hébergeur demande des identifiants FTP pour écrire les fichiers : installez le thème depuis le gestionnaire de fichiers de l’hébergeur, ou ajoutez define( \'FS_METHOD\', \'direct\' ); dans wp-config.php.' );
	}

	$type    = aurea_inst_type( $fichier );
	$essais  = 'inconnu' === $type ? array( 'theme', 'extension' ) : array( $type );
	$message = 'Cette archive n’est ni un thème ni une extension WordPress.';
	foreach ( array_filter( $essais ) as $essai ) {
		$skin      = new WP_Ajax_Upgrader_Skin();
		$upgrader  = 'theme' === $essai ? new Theme_Upgrader( $skin ) : new Plugin_Upgrader( $skin );
		$copie     = $fichier . '.' . $essai . '.zip';
		copy( $fichier, $copie ); // l'installation supprime l'archive : on garde l'original pour un second essai
		$resultat  = $upgrader->install( $copie, array( 'overwrite_package' => true ) );
		if ( $resultat && ! is_wp_error( $resultat ) && ! $skin->get_errors()->has_errors() ) {
			@unlink( $fichier ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			@unlink( $copie ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( 'theme' === $essai ) {
				$theme = $upgrader->theme_info();
				$css   = $theme ? $theme->get_stylesheet() : '';
				wp_send_json_success( array(
					'message'    => sprintf( 'Thème « %s » installé.', $theme ? $theme->get( 'Name' ) : $css ),
					'actif'      => get_stylesheet() === $css,
					'type'       => 'theme',
					'cible'      => $css,
					'activation' => $css ? aurea_inst_lien( admin_url( 'themes.php?action=activate&stylesheet=' . rawurlencode( $css ) ), 'switch-theme_' . $css ) : '',
				) );
			}
			$plugin = $upgrader->plugin_info();
			wp_send_json_success( array(
				'message'    => sprintf( 'Extension « %s » installée.', $plugin ? get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false )['Name'] : '' ),
				'actif'      => $plugin && is_plugin_active( $plugin ),
				'type'       => 'extension',
				'cible'      => $plugin,
				'activation' => $plugin ? aurea_inst_lien( admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin ) ), 'activate-plugin_' . $plugin ) : '',
			) );
		}
		@unlink( $copie ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$erreurs = is_wp_error( $resultat ) ? $resultat : $skin->get_errors();
		if ( $erreurs->has_errors() ) {
			$message = wp_strip_all_tags( implode( ' ', $erreurs->get_error_messages() ) );
		}
	}
	@unlink( $fichier ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	wp_send_json_error( $message );
} );

/* Activation sur place (sans quitter l'écran), pour enchaîner extension puis thème. */
add_action( 'wp_ajax_aurea_inst_activer', function () {
	check_ajax_referer( 'aurea_inst' );
	$type  = sanitize_key( wp_unslash( $_POST['type'] ?? '' ) );
	$cible = sanitize_text_field( wp_unslash( $_POST['cible'] ?? '' ) );
	if ( 'theme' === $type ) {
		$theme = wp_get_theme( $cible );
		if ( ! current_user_can( 'switch_themes' ) || ! $theme->exists() || ! $theme->is_allowed() ) {
			wp_send_json_error( 'Impossible d’activer ce thème.' );
		}
		switch_theme( $theme->get_stylesheet() );
		wp_send_json_success( array( 'message' => 'Thème activé.', 'suite' => admin_url( 'themes.php?page=aurea-installation' ) ) );
	}
	if ( 'extension' === $type ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( ! current_user_can( 'activate_plugins' ) || ! file_exists( WP_PLUGIN_DIR . '/' . $cible ) || validate_file( $cible ) ) {
			wp_send_json_error( 'Impossible d’activer cette extension.' );
		}
		$r = activate_plugin( $cible );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( wp_strip_all_tags( $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'Extension activée.' ) );
	}
	wp_send_json_error( 'Demande inconnue.' );
} );

/* Fichiers temporaires de plus d'un jour : supprimés (envoi abandonné). */
add_action( 'admin_init', function () {
	if ( wp_rand( 1, 20 ) > 1 ) {
		return;
	}
	foreach ( (array) glob( aurea_inst_dossier() . '*.zip' ) as $f ) {
		if ( $f && filemtime( $f ) < time() - DAY_IN_SECONDS ) {
			@unlink( $f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
	}
} );

/* ------------------------------------------------------------------ */
/* Écran                                                               */
/* ------------------------------------------------------------------ */

function aurea_inst_ecran() {
	?>
	<div class="wrap aurea-inst">
		<h1>Installer Aurea</h1>
		<p class="aurea-inst-intro">Choisissez <code>aurea.zip</code> (le thème) et, si besoin, <code>aurea-studio.zip</code> (l’extension). Les fichiers sont envoyés par petits morceaux, ce qui contourne la limite de taille de votre hébergeur, puis installés par WordPress. Une version déjà installée est remplacée par la nouvelle.</p>
		<label class="aurea-inst-zone" id="aurea-inst-zone">
			<input type="file" id="aurea-inst-fichiers" accept=".zip,application/zip" multiple>
			<strong>Choisir les fichiers .zip</strong>
			<span>ou les déposer ici</span>
		</label>
		<div id="aurea-inst-liste" class="aurea-inst-liste" aria-live="polite"></div>
		<p class="aurea-inst-suite">Ensuite : cliquez sur « Activer » sous Aurea Studio, puis sous le thème, et continuez vers <strong>Apparence → Installation Aurea</strong>. Vous pourrez supprimer cette extension une fois l’installation terminée.</p>
	</div>
	<style>
		.aurea-inst{max-width:820px}
		.aurea-inst-intro{font-size:14px;color:#50575e}
		.aurea-inst-zone{display:grid;place-items:center;gap:6px;margin:18px 0;padding:36px;border:2px dashed #C9A961;border-radius:12px;background:#fffdf8;cursor:pointer;text-align:center}
		.aurea-inst-zone.is-survol{background:#fbf3df}
		.aurea-inst-zone input{position:absolute;opacity:0;width:1px;height:1px}
		.aurea-inst-zone strong{font-size:16px}.aurea-inst-zone span{color:#646970}
		.aurea-inst-liste{display:grid;gap:10px}
		.aurea-inst-item{padding:14px 16px;background:#fff;border:1px solid #dcdcde;border-radius:10px}
		.aurea-inst-item b{display:block;margin-bottom:8px}
		.aurea-inst-barre{height:8px;border-radius:99px;background:#f0f0f1;overflow:hidden}
		.aurea-inst-barre i{display:block;height:100%;width:0;background:#C9A961;transition:width .2s}
		.aurea-inst-etat{margin:8px 0 0;color:#50575e}
		.aurea-inst-item.is-ok{border-color:#00a32a}.aurea-inst-item.is-erreur{border-color:#d63638}
		.aurea-inst-item.is-erreur .aurea-inst-etat{color:#d63638}
	</style>
	<script>
	(function () {
		var ajax = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var nonce = <?php echo wp_json_encode( wp_create_nonce( 'aurea_inst' ) ); ?>;
		var liste = document.getElementById('aurea-inst-liste');
		var zone = document.getElementById('aurea-inst-zone');
		var champ = document.getElementById('aurea-inst-fichiers');
		var file = [];
		var enCours = false;

		function jeton() {
			var a = new Uint8Array(12); crypto.getRandomValues(a);
			return Array.prototype.map.call(a, function (x) { return ('0' + x.toString(16)).slice(-2); }).join('');
		}
		function envoyer(donnees) {
			donnees.append('_ajax_nonce', nonce);
			return fetch(ajax, { method: 'POST', body: donnees, credentials: 'same-origin' }).then(function (r) {
				if (r.status === 413) { var e = new Error('413'); e.code = 413; throw e; }
				return r.json();
			});
		}
		async function traiter(f) {
			var item = document.createElement('div'); item.className = 'aurea-inst-item';
			item.innerHTML = '<b></b><div class="aurea-inst-barre"><i></i></div><p class="aurea-inst-etat">Envoi…</p>';
			item.querySelector('b').textContent = f.name + ' (' + (f.size / 1048576).toFixed(1) + ' Mo)';
			liste.appendChild(item);
			var barre = item.querySelector('i'), etat = item.querySelector('.aurea-inst-etat');
			var id = jeton(), debut = 0, morceau = 512 * 1024;
			try {
				while (debut < f.size) {
					var d = new FormData();
					d.append('action', 'aurea_inst_morceau'); d.append('jeton', id); d.append('debut', debut);
					d.append('morceau', f.slice(debut, debut + morceau), 'morceau');
					var r;
					try { r = await envoyer(d); }
					catch (e) {
						if (e.code === 413 && morceau > 32 * 1024) { morceau = Math.floor(morceau / 2); continue; } // limite très basse : morceaux plus petits
						throw e;
					}
					if (!r || !r.success) throw new Error(r && r.data ? r.data : 'Envoi refusé.');
					debut = r.data.recu;
					barre.style.width = Math.round(debut / f.size * 90) + '%';
				}
				etat.textContent = 'Installation…';
				var fin = new FormData();
				fin.append('action', 'aurea_inst_terminer'); fin.append('jeton', id); fin.append('taille', f.size);
				var res = await envoyer(fin);
				barre.style.width = '100%';
				if (!res.success) throw new Error(res.data || 'Installation impossible.');
				item.classList.add('is-ok');
				etat.textContent = res.data.message + ' ';
				if (res.data.activation && !res.data.actif) {
					var a = document.createElement('a'); a.className = 'button button-primary'; a.href = res.data.activation; a.textContent = 'Activer';
					a.addEventListener('click', function (ev) {
						ev.preventDefault(); a.textContent = 'Activation…'; a.setAttribute('aria-disabled', 'true');
						var d = new FormData(); d.append('action', 'aurea_inst_activer'); d.append('type', res.data.type); d.append('cible', res.data.cible);
						envoyer(d).then(function (r) {
							if (!r.success) throw new Error(r.data || 'Activation impossible.');
							a.replaceWith(document.createTextNode(r.data.message + ' '));
							if (r.data.suite) {
								var s = document.createElement('a'); s.className = 'button button-primary'; s.href = r.data.suite; s.textContent = 'Continuer : Installation Aurea';
								etat.appendChild(s);
							}
						}).catch(function () { window.location.href = res.data.activation; }); // secours : le lien signé classique
					});
					etat.appendChild(a);
				} else if (res.data.actif) {
					etat.appendChild(document.createTextNode('(déjà actif : la nouvelle version est en place)'));
				}
			} catch (e) {
				item.classList.add('is-erreur');
				etat.textContent = e.message === '413' ? 'L’hébergeur refuse même les petits morceaux : installez le fichier par son gestionnaire de fichiers ou par FTP.' : e.message;
			}
		}
		async function suivant() {
			if (enCours) return; enCours = true;
			while (file.length) { await traiter(file.shift()); }
			enCours = false;
		}
		function ajouter(fichiers) {
			Array.prototype.forEach.call(fichiers, function (f) { if (/\.zip$/i.test(f.name)) file.push(f); });
			suivant();
		}
		champ.addEventListener('change', function () { ajouter(champ.files); champ.value = ''; });
		['dragenter', 'dragover'].forEach(function (t) { zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.add('is-survol'); }); });
		['dragleave', 'drop'].forEach(function (t) { zone.addEventListener(t, function (e) { e.preventDefault(); zone.classList.remove('is-survol'); }); });
		zone.addEventListener('drop', function (e) { ajouter(e.dataTransfer.files); });
	})();
	</script>
	<?php
}

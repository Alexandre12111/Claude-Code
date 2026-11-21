<?php
/**
 * Apparence → Installation DualTech : met le site en place en un clic, par étapes.
 *
 * 1. Partenaires, intervenants et programme copiés dans DualTech Studio (par lots, images dans la médiathèque).
 * 2. Pages de la maquette créées, ou remplacées si elles existent déjà (révision gardée, Elementor retiré).
 * 3. Réglages WordPress : page d'accueil, permaliens, fuseau horaire, nom du site.
 * 4. Rank Math : titres, descriptions et mots clés de chaque page, réglages généraux.
 *
 * Chaque étape peut être relancée : rien n'est créé en double, et les partenaires, intervenants
 * et séances déjà présents ne sont jamais modifiés (les changements faits dans DualTech Studio sont gardés).
 */

defined( 'ABSPATH' ) || exit;

/** État du site pour l'écran d'installation. */
function dualtech_etat() {
	return array(
		'studio'    => function_exists( 'dts_importer_lot' ),
		'rank_math' => dualtech_rank_math_installe(),
		'pages'     => count( array_filter( array_keys( dualtech_pages() ), 'dualtech_page_par_slug' ) ),
		'installe'  => (int) get_option( 'dualtech_installe_le' ),
	);
}

/** Étape 2 : crée ou remplace une page de la maquette. */
function dualtech_installer_page( $slug ) {
	$info = dualtech_pages()[ $slug ] ?? null;
	if ( ! $info ) {
		return 'Page inconnue.';
	}
	kses_remove_filters(); // le HTML de la maquette (sections, SVG, formulaires) est gardé tel quel
	$contenu = dualtech_contenu_livre( basename( $info['fichier'], '.html' ) );
	$page    = dualtech_page_par_slug( $slug );
	$donnees = array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_title'     => $info['titre'],
		'post_name'      => $slug,
		'post_content'   => wp_slash( $contenu ),
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	);
	if ( $page ) {
		$donnees['ID'] = $page->ID;
		$id            = wp_update_post( $donnees, true );
	} else {
		$id = wp_insert_post( $donnees, true );
	}
	kses_init_filters();
	if ( is_wp_error( $id ) ) {
		return $id->get_error_message();
	}
	// Ancienne page faite avec Elementor : le contenu de la maquette prend le relais
	foreach ( array( '_elementor_edit_mode', '_elementor_data', '_elementor_page_settings', '_elementor_css', '_elementor_template_type', '_elementor_version' ) as $meta ) {
		delete_post_meta( $id, $meta );
	}
	update_post_meta( $id, '_wp_page_template', 'default' );
	$seo = array(
		'rank_math_title'         => $info['seo_titre'],
		'rank_math_description'   => $info['seo_description'],
		'rank_math_focus_keyword' => $info['mot_cle'],
		'rank_math_robots'        => array( 'index' ),
		'rank_math_rich_snippet'  => 'off', // données structurées fournies par le thème
	);
	$og = dualtech_image_partage();
	if ( $og ) { // visuel de partage 1200 × 630 (sinon Rank Math prend la première image de la page)
		$seo['rank_math_facebook_image']    = wp_get_attachment_url( $og );
		$seo['rank_math_facebook_image_id'] = $og;
	}
	foreach ( $seo as $cle => $valeur ) {
		update_post_meta( $id, $cle, $valeur );
	}
	return ( $page ? 'Remplacée : ' : 'Créée : ' ) . $info['titre'];
}

/** Visuel de partage du thème, copié dans la médiathèque (identifiant, ou 0). Recopié si le thème en livre une nouvelle version. */
function dualtech_image_partage() {
	static $id = null;
	if ( null !== $id ) {
		return $id;
	}
	$fichier = DUALTECH_DIR . '/assets/img/og-dualtech-2026.jpg';
	$empreinte = is_readable( $fichier ) ? md5_file( $fichier ) : '';
	$actuel    = (int) get_option( 'dualtech_image_partage' );
	if ( $actuel && get_post( $actuel ) && get_post_meta( $actuel, '_dt_empreinte', true ) === $empreinte ) {
		return $id = $actuel;
	}
	if ( ! $empreinte ) {
		return $id = 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$copie = wp_tempnam( 'og-dualtech-2026.jpg' );
	copy( $fichier, $copie );
	$nouveau = media_handle_sideload( array( 'name' => 'og-dualtech-2026.jpg', 'tmp_name' => $copie ), 0, 'DualTech 2026, le rendez-vous incontournable de l’industrie duale' );
	if ( is_wp_error( $nouveau ) ) {
		@unlink( $copie ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return $id = $actuel;
	}
	update_post_meta( $nouveau, '_dt_empreinte', $empreinte );
	update_post_meta( $nouveau, '_wp_attachment_image_alt', 'Logo DualTech : le rendez-vous incontournable de l’industrie duale, 26 novembre 2026, Campus Cyber, La Défense' );
	update_option( 'dualtech_image_partage', $nouveau );
	return $id = (int) $nouveau;
}

/** Étape 3 : réglages WordPress. */
function dualtech_installer_reglages() {
	$accueil = dualtech_page_par_slug( 'accueil' );
	if ( $accueil ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $accueil->ID );
	}
	$confidentialite = dualtech_page_par_slug( 'politique-de-confidentialite' );
	if ( $confidentialite ) {
		update_option( 'wp_page_for_privacy_policy', $confidentialite->ID ); // page reconnue par WordPress (Réglages → Confidentialité)
	}
	update_option( 'blogname', 'DualTech' );
	update_option( 'blogdescription', 'Le rendez vous de l’industrie duale' );
	update_option( 'timezone_string', 'Europe/Paris' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'blog_public', 1 );
	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules( false );
	update_option( 'dualtech_installe_le', time() );
	return 'Accueil défini, adresses en /nom-de-la-page/, fuseau Europe/Paris, commentaires fermés.';
}

/** Met à jour seulement certaines clés d'une option Rank Math (le reste est conservé). */
function dualtech_rank_math_option( $option, $valeurs ) {
	$actuel = get_option( $option );
	update_option( $option, array_merge( is_array( $actuel ) ? $actuel : array(), $valeurs ) );
}

/** Étape 4 : Rank Math. */
function dualtech_installer_rank_math() {
	if ( dualtech_rank_math_installe() && ! dualtech_rank_math() ) {
		add_option( 'rank_math_registration_skip', true ); // comme « Passer » dans l'assistant : usage sans compte Rank Math
	}
	if ( ! dualtech_rank_math_installe() ) {
		return 'Rank Math SEO n’est pas actif : installez le (gratuit), puis relancez cette étape. Sans lui, le thème affiche lui même titres et descriptions.';
	}
	foreach ( array_keys( dualtech_pages() ) as $slug ) {
		$page = dualtech_page_par_slug( $slug );
		$info = dualtech_pages()[ $slug ];
		if ( $page && ! get_post_meta( $page->ID, 'rank_math_title', true ) ) {
			update_post_meta( $page->ID, 'rank_math_title', $info['seo_titre'] );
			update_post_meta( $page->ID, 'rank_math_description', $info['seo_description'] );
			update_post_meta( $page->ID, 'rank_math_focus_keyword', $info['mot_cle'] );
		}
	}
	dualtech_rank_math_option( 'rank-math-options-titles', array(
		'title_separator'              => '|',
		'knowledgegraph_type'          => 'company',
		'knowledgegraph_name'          => 'DualTech',
		'website_name'                 => 'DualTech',
		'open_graph_image'             => dualtech_image_partage() ? wp_get_attachment_url( dualtech_image_partage() ) : '',
		'open_graph_image_id'          => dualtech_image_partage(),
		'url'                          => home_url( '/' ),
		'disable_author_archives'      => 'on',
		'disable_date_archives'        => 'on',
		'noindex_empty_taxonomies'     => 'on',
		'pt_page_default_rich_snippet' => 'off',
	) );
	dualtech_rank_math_option( 'rank-math-options-general', array(
		'breadcrumbs'              => 'off', // fil d'Ariane dessiné par le thème
		'attachment_redirect_urls' => 'on',
	) );
	dualtech_rank_math_option( 'rank-math-options-sitemap', array(
		'authors_sitemap'       => 'off',
		'pt_attachment_sitemap' => 'off',
		'pt_post_sitemap'       => 'off',
	) );
	$modules = (array) get_option( 'rank_math_modules', array() );
	foreach ( array( 'sitemap', 'rich-snippet' ) as $m ) {
		if ( ! in_array( $m, $modules, true ) ) {
			$modules[] = $m;
		}
	}
	update_option( 'rank_math_modules', array_values( $modules ) );
	return 'Titres, descriptions et mots clés des ' . count( dualtech_pages() ) . ' pages en place, séparateur « | », plan du site actif.';
}

/* Étapes appelées par l'écran d'installation. */
add_action( 'wp_ajax_dualtech_installer', function () {
	check_ajax_referer( 'dualtech_installer' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Droits insuffisants.' ) );
	}
	$etape = sanitize_key( $_POST['etape'] ?? '' );
	$debut = absint( $_POST['debut'] ?? 0 );
	switch ( $etape ) {
		case 'partenaires':
		case 'intervenants':
		case 'seances':
			if ( ! function_exists( 'dts_importer_lot' ) ) {
				wp_send_json_error( array( 'message' => 'Activez d’abord l’extension DualTech Studio.' ) );
			}
			$r = dts_importer_lot( dualtech_donnees_livrees(), DUALTECH_DIR . '/assets', $etape, $debut, 6 );
			wp_send_json_success( $r + array( 'message' => sprintf( '%d sur %d', min( $debut + 6, $r['total'] ), $r['total'] ) ) );
			break;
		case 'pages':
			if ( 0 === $debut && '/%postname%/' !== get_option( 'permalink_structure' ) ) {
				update_option( 'permalink_structure', '/%postname%/' ); // liens entre pages en /nom-de-la-page/
				flush_rewrite_rules( false );
			}
			$slugs = array_keys( dualtech_pages() );
			$slug  = $slugs[ $debut ] ?? null;
			if ( ! $slug ) {
				wp_send_json_success( array( 'message' => 'Terminé', 'suivant' => null ) );
			}
			wp_send_json_success( array( 'message' => dualtech_installer_page( $slug ), 'suivant' => $debut + 1 < count( $slugs ) ? $debut + 1 : null ) );
			break;
		case 'reglages':
			wp_send_json_success( array( 'message' => dualtech_installer_reglages(), 'suivant' => null ) );
			break;
		case 'rank_math':
			wp_send_json_success( array( 'message' => dualtech_installer_rank_math(), 'suivant' => null, 'avertissement' => ! dualtech_rank_math_installe() ) );
			break;
	}
	wp_send_json_error( array( 'message' => 'Étape inconnue.' ) );
} );

/* Écran Apparence → Installation DualTech. */
add_action( 'admin_menu', function () {
	add_theme_page( 'Installation DualTech', 'Installation DualTech', 'manage_options', 'dualtech-installation', 'dualtech_installation_ecran' );
} );

/* Après l'activation du thème : direction l'écran d'installation. */
add_action( 'after_switch_theme', function () {
	update_option( 'dualtech_ouvrir_installation', 1 );
} );
add_action( 'admin_init', function () {
	if ( get_option( 'dualtech_ouvrir_installation' ) && current_user_can( 'manage_options' ) && ! wp_doing_ajax() ) {
		delete_option( 'dualtech_ouvrir_installation' );
		wp_safe_redirect( admin_url( 'themes.php?page=dualtech-installation' ) );
		exit;
	}
} );

function dualtech_installation_ecran() {
	$e = dualtech_etat();
	$n = count( dualtech_pages() );
	?>
	<div class="wrap" style="max-width:860px">
		<h1>Installation DualTech</h1>
		<p style="font-size:14px">Met en place le site de la maquette : partenaires, intervenants et programme dans le menu <strong>DualTech</strong>, les <?php echo (int) $n; ?> pages, les réglages et le référencement. L’installation peut être relancée sans risque : rien n’est créé en double et vos modifications dans DualTech sont conservées. Seul le contenu des pages est remplacé (une révision de l’ancienne version est gardée).</p>

		<table class="widefat striped" style="margin:20px 0">
			<tbody>
				<tr><td style="width:34px"><?php echo $e['studio'] ? '✅' : '⚠️'; ?></td><td><strong>Extension DualTech Studio</strong><br><?php echo $e['studio'] ? 'Active : partenaires, intervenants, programme et demandes reçues sont gérés dans le menu DualTech.' : 'Inactive : Extensions → DualTech Studio → Activer, puis revenez ici.'; ?></td></tr>
				<tr><td><?php echo $e['rank_math'] ? '✅' : '⚠️'; ?></td><td><strong>Rank Math SEO</strong><br><?php echo $e['rank_math'] ? 'Actif.' : 'Inactif : Extensions → Ajouter → « Rank Math SEO » → Installer → Activer. Recommandé (sans lui, le thème gère titres et descriptions).'; ?></td></tr>
				<tr><td><?php echo $e['pages'] === $n ? '✅' : '•'; ?></td><td><strong>Pages</strong><br><?php echo (int) $e['pages'] . ' sur ' . (int) $n . ' déjà en place.'; ?><?php echo $e['installe'] ? ' Dernière installation le ' . esc_html( wp_date( 'j F Y à H\hi', $e['installe'] ) ) . '.' : ''; ?></td></tr>
			</tbody>
		</table>

		<p><button type="button" class="button button-primary button-hero" id="dt-lancer" <?php disabled( ! $e['studio'] ); ?>>Lancer l’installation</button></p>
		<ol id="dt-journal" style="font-size:14px;line-height:1.8"></ol>
		<p id="dt-fin" hidden style="font-size:15px"><strong>C’est prêt.</strong> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank">Voir le site</a> · <a href="<?php echo esc_url( admin_url( 'admin.php?page=dualtech-studio' ) ); ?>">Ouvrir le menu DualTech</a></p>
	</div>
	<script>
	(() => {
		const btn = document.getElementById('dt-lancer'), journal = document.getElementById('dt-journal');
		const etapes = [['partenaires', 'Partenaires'], ['intervenants', 'Intervenants'], ['seances', 'Programme'], ['pages', 'Pages'], ['reglages', 'Réglages WordPress'], ['rank_math', 'Rank Math SEO']];
		const appel = async (etape, debut) => {
			const corps = new URLSearchParams({ action: 'dualtech_installer', _ajax_nonce: <?php echo wp_json_encode( wp_create_nonce( 'dualtech_installer' ) ); ?>, etape, debut });
			const r = await fetch(ajaxurl, { method: 'POST', body: corps, credentials: 'same-origin' });
			const j = await r.json().catch(() => ({ success: false, data: { message: 'Réponse illisible du serveur (' + r.status + ').' } }));
			if (!j.success) throw new Error(j.data && j.data.message || 'Erreur');
			return j.data;
		};
		btn.addEventListener('click', async () => {
			btn.disabled = true; journal.innerHTML = '';
			try {
				for (const [etape, nom] of etapes) {
					const li = document.createElement('li'); journal.appendChild(li);
					let debut = 0, d;
					do {
						li.textContent = nom + ' : en cours…';
						d = await appel(etape, debut);
						li.textContent = nom + ' : ' + d.message;
						debut = d.suivant;
					} while (debut !== null && debut !== undefined);
					li.textContent = (d.avertissement ? '⚠️ ' : '✅ ') + nom + (etape === 'pages' || etape.match(/^(partenaires|intervenants|seances)$/) ? ' : terminé' : ' : ' + d.message);
				}
				document.getElementById('dt-fin').hidden = false;
			} catch (e) {
				const li = document.createElement('li'); li.style.color = '#b32d2e'; li.textContent = '❌ ' + e.message + ' Relancez l’installation pour reprendre.'; journal.appendChild(li);
			}
			btn.disabled = false;
		});
	})();
	</script>
	<?php
}

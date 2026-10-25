<?php
/**
 * Apparence → Installation Aurea : la mise en place du site en un clic.
 *
 * Étapes (chacune peut être relancée sans risque, rien n'est supprimé) :
 * 1. Extensions : Aurea Studio et Rank Math SEO actifs.
 * 2. Contenus Aurea Studio : réalisations et logos livrés avec l'extension.
 * 3. Pages : les 21 pages de la maquette, la page Conseils, l'article « Prix d'un site vitrine »,
 *    l'étude de cas Havel Trading, avec leur titre SEO, leur description et leur mot clé Rank Math.
 *    Une ancienne page au même nom est remplacée (l'ancienne version reste dans les révisions) ;
 *    une page déjà importée par le thème n'est retouchée que si l'option est cochée.
 * 4. Réglages WordPress : page d'accueil, page des articles, adresses /conseils/…, fuseau horaire.
 * 5. Rank Math : séparateur, organisation, schémas par défaut, plan du site, fil d'Ariane.
 * 6. Redirections 301 des anciennes adresses.
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_INSTALL', 'aurea-installation' );

/* ------------------------------------------------------------------ */
/* État de l'installation                                              */
/* ------------------------------------------------------------------ */

/** Pages de la maquette (contenu/pages.json, celles qui ont un fichier de contenu). */
function aurea_pages_a_creer() {
	return array_filter( aurea_pages(), function ( $p ) {
		return ! empty( $p['fichier'] );
	} );
}

function aurea_page_par_slug( $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'page' );
	return $p && 'trash' !== $p->post_status ? $p : null;
}

function aurea_etat_installation() {
	$pages  = aurea_pages_a_creer();
	$faites = array_filter( array_keys( $pages ), 'aurea_page_par_slug' );
	$accueil = aurea_page_par_slug( 'accueil' );
	$conseils = aurea_page_par_slug( 'conseils' );
	return array(
		'studio'       => function_exists( 'aurea_realisations' ),
		'rank_math'    => aurea_rank_math_actif(),
		'realisations' => function_exists( 'aurea_realisations' ) ? count( aurea_realisations( array( 'tout' => true ) ) ) : 0,
		'logos'        => function_exists( 'aurea_logos' ) ? count( aurea_logos() ) : 0,
		'pages'        => count( $faites ),
		'pages_total'  => count( $pages ),
		'reglages'     => $accueil && $conseils && 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) === $accueil->ID && (int) get_option( 'page_for_posts' ) === $conseils->ID && '/conseils/%postname%/' === get_option( 'permalink_structure' ),
		'rank_reglee'  => (bool) get_option( 'aurea_rank_math_regle' ),
		'redirections' => aurea_redirections_rank_math() && get_option( 'aurea_redirections_rank_math' ),
	);
}

/* ------------------------------------------------------------------ */
/* Étapes                                                              */
/* ------------------------------------------------------------------ */

/** Étape 2 : réalisations et logos d'Aurea Studio. */
function aurea_installer_contenus_studio() {
	if ( ! function_exists( 'aurea_real_importer' ) ) {
		return 'Aurea Studio (version 0.3 ou plus) doit être actif pour importer les réalisations et les logos.';
	}
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$r = aurea_real_importer();
	$l = function_exists( 'aurea_logos_importer' ) ? aurea_logos_importer() : 0;
	return sprintf( 'Réalisations ajoutées : %d. Logos ajoutés : %d.', $r, $l );
}

/**
 * Étape 2 par petits lots (utilisée par « Tout installer ») : quelques images par requête,
 * pour ne jamais dépasser le temps d'exécution autorisé par l'hébergeur.
 *
 * @return array { message, restant }
 */
function aurea_installer_studio_lot() {
	if ( ! function_exists( 'aurea_real_importer' ) || ! function_exists( 'aurea_real_a_importer' ) ) {
		return array( 'message' => 'Aurea Studio n’est pas actif : les réalisations et les logos seront importés quand il le sera.', 'restant' => 0, 'avertissement' => true );
	}
	@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$avant_r = count( aurea_real_a_importer() );
	$avant_l = function_exists( 'aurea_logos_a_importer' ) ? count( aurea_logos_a_importer() ) : 0;
	if ( $avant_r ) {
		$ajout = aurea_real_importer( 3 );
		$quoi  = 'réalisations';
	} elseif ( $avant_l ) {
		$ajout = aurea_logos_importer( 6 );
		$quoi  = 'logos';
	} else {
		return array( 'message' => 'Réalisations et logos déjà importés.', 'restant' => 0 );
	}
	$restant = count( aurea_real_a_importer() ) + ( function_exists( 'aurea_logos_a_importer' ) ? count( aurea_logos_a_importer() ) : 0 );
	return array( 'message' => sprintf( '%d %s importés, %d images restantes.', $ajout, $quoi, $restant ), 'restant' => $restant, 'progres' => $ajout > 0 );
}

/** Image livrée avec le thème copiée dans la médiathèque (une seule fois). */
function aurea_image_theme( $fichier, $alt ) {
	$deja = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_aurea_source', 'meta_value' => $fichier ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $deja ) {
		return (int) $deja[0];
	}
	$source = AUREA_DIR . '/assets/' . $fichier;
	if ( ! file_exists( $source ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$copie = wp_tempnam( basename( $fichier ) );
	copy( $source, $copie );
	$id = media_handle_sideload( array( 'name' => basename( $fichier ), 'tmp_name' => $copie ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $copie ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_aurea_source', $fichier );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/** Réglages Rank Math d'une page ou d'un article (sans écraser ce qui a été saisi, sauf demande). */
function aurea_seo_meta( $id, $info, $forcer ) {
	$champs = array(
		'rank_math_title'         => $info['seo_titre'] ?? '',
		'rank_math_description'   => $info['seo_description'] ?? '',
		'rank_math_focus_keyword' => $info['mot_cle'] ?? '',
	);
	foreach ( $champs as $cle => $valeur ) {
		if ( $valeur && ( $forcer || '' === (string) get_post_meta( $id, $cle, true ) ) ) {
			update_post_meta( $id, $cle, $valeur );
		}
	}
}

/**
 * Étape 3 : pages, page Conseils, article, étude de cas.
 *
 * Une ancienne page du site qui porte le même nom (Contact, L'agence…) est remplacée par la nouvelle
 * (son ancienne version reste dans les révisions). Une page déjà importée par le thème n'est pas
 * retouchée, pour garder vos modifications, sauf si $remplacer est demandé.
 *
 * @param bool $remplacer Remettre aussi le texte de la maquette dans les pages déjà importées.
 */
function aurea_installer_pages( $remplacer = false ) {
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( ! get_option( 'aurea_installe_le' ) ) {
		update_option( 'aurea_installe_le', time() ); // les articles écrits après cette date ne sont jamais retirés par le nettoyage
	}
	kses_remove_filters(); // le HTML de la maquette (icônes SVG, formulaire) doit rester intact
	$crees = 0;
	$maj   = 0;
	$gardees = 0;
	$ordre = 0;
	foreach ( aurea_pages_a_creer() as $slug => $p ) {
		$contenu = aurea_contenu_livre( basename( $p['fichier'], '.html' ) );
		$page    = aurea_page_par_slug( $slug );
		$donnees = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p['titre'],
			'post_name'    => $slug,
			'post_content' => $contenu,
			'post_excerpt' => $p['extrait'] ?? '',
			'menu_order'   => $ordre++,
		);
		if ( $page && ! $remplacer && get_post_meta( $page->ID, '_aurea_version', true ) ) {
			$gardees++;
			aurea_seo_meta( $page->ID, $p, false );
			continue;
		}
		if ( $page ) {
			$donnees['ID'] = $page->ID;
			$id = wp_update_post( wp_slash( $donnees ), true );
			$maj++;
		} else {
			$id = wp_insert_post( wp_slash( $donnees ), true );
			$crees++;
		}
		if ( ! is_wp_error( $id ) ) {
			aurea_seo_meta( $id, $p, true );
			update_post_meta( $id, '_aurea_version', AUREA_VERSION );
		}
	}

	// Page des articles « Conseils » (son contenu n'est pas affiché : la liste des articles la remplace)
	$conseils = aurea_page_par_slug( 'conseils' );
	if ( ! $conseils ) {
		$id_c = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Conseils', 'post_name' => 'conseils', 'menu_order' => $ordre ) );
		$crees++;
	} else {
		$id_c = $conseils->ID;
	}
	aurea_seo_meta( $id_c, aurea_pages()['conseils'] ?? array(), false );

	// Article « Combien coûte un site vitrine en 2026 ? »
	$a = aurea_pages()['article:prix-site-vitrine'] ?? null;
	if ( $a && ! get_page_by_path( 'prix-site-vitrine', OBJECT, 'post' ) ) {
		$cat = term_exists( 'Prix', 'category' ) ?: wp_insert_term( 'Prix', 'category', array( 'slug' => 'prix' ) );
		$id_a = wp_insert_post( wp_slash( array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $a['titre'],
			'post_name'     => 'prix-site-vitrine',
			'post_excerpt'  => $a['extrait'] ?? '',
			'post_content'  => aurea_contenu_livre( 'article-prix-site-vitrine' ),
			'post_category' => is_array( $cat ) ? array( (int) $cat['term_id'] ) : array(),
		) ), true );
		if ( ! is_wp_error( $id_a ) ) {
			aurea_seo_meta( $id_a, $a, true );
			update_post_meta( $id_a, '_aurea_version', AUREA_VERSION );
			$img = aurea_image_theme( 'realisations/verisol.webp', 'Site vitrine sur mesure du Groupe Verisol' );
			if ( $img ) {
				set_post_thumbnail( $id_a, $img );
			}
			$crees++;
		}
	}

	// Étude de cas Havel Trading (si sa fiche existe et n'a pas encore de texte)
	if ( function_exists( 'aurea_realisations' ) ) {
		foreach ( aurea_realisations( array( 'tout' => true ) ) as $r ) {
			if ( 'havel-trading' === aurea_cle_real( $r ) && ! aurea_a_etude( $r ) ) {
				wp_update_post( wp_slash( array( 'ID' => $r['id'], 'post_content' => aurea_contenu_livre( 'etude-havel-trading' ) ) ) );
			}
		}
	}
	kses_init_filters();
	return sprintf( 'Pages créées : %d. Anciennes pages remplacées : %d. Pages déjà importées gardées telles quelles : %d.', $crees, $maj, $gardees );
}

/** Étape 4 : réglages WordPress. */
function aurea_installer_reglages_wp() {
	$accueil  = aurea_page_par_slug( 'accueil' );
	$conseils = aurea_page_par_slug( 'conseils' );
	if ( ! $accueil || ! $conseils ) {
		return 'Créez d’abord les pages (étape 3).';
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $accueil->ID );
	update_option( 'page_for_posts', $conseils->ID );
	update_option( 'blogname', 'Aurea Media' );
	if ( in_array( get_option( 'blogdescription' ), array( '', 'Just another WordPress site', 'Un site utilisant WordPress' ), true ) ) {
		update_option( 'blogdescription', 'Agence web sur mesure' );
	}
	if ( in_array( get_option( 'timezone_string' ), array( '', 'UTC' ), true ) ) {
		update_option( 'timezone_string', 'Europe/Paris' );
	}
	update_option( 'date_format', 'j F Y' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	// Articles sous /conseils/, rubriques sous /conseils/categorie/ : les anciennes adresses d'articles
	// sont redirigées automatiquement (inc/redirections.php).
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/conseils/%postname%/' );
	$wp_rewrite->set_category_base( 'conseils/categorie' );
	$wp_rewrite->set_tag_base( 'conseils/sujet' );
	// Les rubriques gardent leur ancienne structure jusqu'à ce qu'elles soient réenregistrées
	// (comme le fait l'écran Réglages → Permaliens) ; les règles sont aussi régénérées au prochain chargement.
	create_initial_taxonomies();
	flush_rewrite_rules( false );
	update_option( 'aurea_regenerer_regles', 1 );
	return 'Accueil, page des articles, adresses /conseils/… et fuseau horaire réglés.';
}

add_action( 'init', function () {
	if ( get_option( 'aurea_regenerer_regles' ) ) {
		delete_option( 'aurea_regenerer_regles' );
		flush_rewrite_rules( false );
	}
}, 99 );

/** Met à jour seulement certaines clés d'une option Rank Math (le reste de ses réglages est conservé). */
function aurea_rank_math_option( $option, $valeurs ) {
	$actuel = get_option( $option );
	if ( ! is_array( $actuel ) ) {
		return false;
	}
	update_option( $option, array_merge( $actuel, $valeurs ) );
	return true;
}

/** Étape 5 : réglages Rank Math. */
function aurea_installer_rank_math() {
	if ( ! aurea_rank_math_actif() ) {
		return 'Installez et activez Rank Math SEO (gratuit), puis relancez cette étape.';
	}
	$c     = aurea_coordonnees();
	$pages = aurea_pages();
	$logo  = aurea_image_theme( 'logo-aurea-media.png', 'Aurea Media' );
	$og    = aurea_image_theme( 'og-aurea-media.jpg', 'Aurea Media, agence web sur mesure' );
	aurea_rank_math_option( 'rank-math-options-titles', array_filter( array(
		'title_separator'                          => '|',
		'knowledgegraph_type'                      => 'company',
		'knowledgegraph_name'                      => 'Aurea Media',
		'website_name'                             => 'Aurea Media',
		'knowledgegraph_logo'                      => $logo ? wp_get_attachment_url( $logo ) : '',
		'knowledgegraph_logo_id'                   => $logo,
		'open_graph_image'                         => $og ? wp_get_attachment_url( $og ) : '',
		'open_graph_image_id'                      => $og,
		'url'                                      => home_url( '/' ),
		'local_business_type'                      => 'ProfessionalService',
		'phone'                                    => $c['tel'],
		'email'                                    => $c['email'],
		'social_url_facebook'                      => '',
		'social_additional_profiles'               => implode( "\n", array_filter( array( $c['linkedin'], $c['google'] ) ) ),
		'disable_author_archives'                  => 'on',
		'disable_date_archives'                    => 'on',
		'noindex_empty_taxonomies'                 => 'on',
		'pt_page_default_rich_snippet'             => 'off',
		'pt_post_default_rich_snippet'             => 'article',
		'pt_post_default_article_type'             => 'BlogPosting',
		'pt_aurea_realisation_default_rich_snippet' => 'off',
		'pt_aurea_realisation_archive_title'       => $pages['realisations']['seo_titre'] ?? '',
		'pt_aurea_realisation_archive_description' => $pages['realisations']['seo_description'] ?? '',
	), function ( $v ) {
		return '' !== $v && 0 !== $v;
	} ) );
	aurea_rank_math_option( 'rank-math-options-general', array(
		'breadcrumbs'              => 'off', // le fil d'Ariane est dessiné par le thème, et décrit dans les données structurées
		'attachment_redirect_urls' => 'on',
		'strip_category_base'      => 'off',
	) );
	aurea_rank_math_option( 'rank-math-options-sitemap', array(
		'authors_sitemap'              => 'off',
		'pt_attachment_sitemap'        => 'off',
		'pt_aurea_realisation_sitemap' => 'on',
		'tax_post_tag_sitemap'         => 'off',
	) );
	$modules = (array) get_option( 'rank_math_modules', array() );
	foreach ( array( 'sitemap', 'rich-snippet', 'redirections' ) as $m ) {
		if ( ! in_array( $m, $modules, true ) ) {
			$modules[] = $m;
		}
	}
	update_option( 'rank_math_modules', array_values( $modules ) );
	update_option( 'aurea_rank_math_regle', time() );
	return 'Rank Math réglé : organisation Aurea Media, séparateur « | », pas de schéma Article sur les pages, plan du site sans auteurs ni images jointes.';
}

/** Étape 6 : redirections. */
function aurea_installer_redirections_etape() {
	$r = aurea_installer_redirections();
	if ( is_wp_error( $r ) ) {
		return $r->get_error_message();
	}
	return sprintf( 'Redirections ajoutées dans Rank Math : %d (déjà présentes : %d).', $r['ajoutees'], $r['existantes'] );
}

/* ------------------------------------------------------------------ */
/* Écran                                                               */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	add_theme_page( 'Installation Aurea', 'Installation Aurea', 'manage_options', AUREA_INSTALL, 'aurea_ecran_installation' );
} );

/* « Tout installer » : une requête par étape (et par lot d'images), avec un compte rendu affiché au fur et à mesure. */
add_action( 'wp_ajax_aurea_installer_etape', function () {
	check_ajax_referer( 'aurea_installer' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Accès refusé.', 403 );
	}
	$etape = sanitize_key( wp_unslash( $_POST['etape'] ?? '' ) );
	try {
		switch ( $etape ) {
			case 'studio':
				wp_send_json_success( aurea_installer_studio_lot() );
				break;
			case 'pages':
				wp_send_json_success( array( 'message' => aurea_installer_pages( ! empty( $_POST['remplacer'] ) ) ) );
				break;
			case 'reglages':
				wp_send_json_success( array( 'message' => aurea_installer_reglages_wp() ) );
				break;
			case 'rank_math':
				wp_send_json_success( array( 'message' => aurea_installer_rank_math(), 'avertissement' => ! aurea_rank_math_actif() ) );
				break;
			case 'redirections':
				wp_send_json_success( array( 'message' => aurea_installer_redirections_etape(), 'avertissement' => ! aurea_rank_math_actif() ) );
				break;
		}
	} catch ( Throwable $e ) {
		wp_send_json_error( 'Erreur pendant l’étape : ' . $e->getMessage() );
	}
	wp_send_json_error( 'Étape inconnue.' );
} );

add_action( 'admin_post_aurea_installer', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'aurea_installer' );
	$etape     = sanitize_key( wp_unslash( $_POST['etape'] ?? '' ) );
	$remplacer = ! empty( $_POST['remplacer'] );
	$messages  = array();
	$etapes    = array(
		'studio'       => 'aurea_installer_contenus_studio',
		'pages'        => function () use ( $remplacer ) {
			return aurea_installer_pages( $remplacer );
		},
		'reglages'     => 'aurea_installer_reglages_wp',
		'rank_math'    => 'aurea_installer_rank_math',
		'redirections' => 'aurea_installer_redirections_etape',
		'nettoyage'    => 'aurea_nettoyer',
	);
	foreach ( $etapes as $nom => $fonction ) {
		if ( ( 'tout' === $etape && 'nettoyage' !== $nom ) || $nom === $etape ) {
			$messages[] = call_user_func( $fonction );
		}
	}
	set_transient( 'aurea_installation_messages', $messages, 120 );
	wp_safe_redirect( admin_url( 'themes.php?page=' . AUREA_INSTALL ) );
	exit;
} );

function aurea_bouton_etape( $etape, $libelle, $principal = false, $extra = '' ) {
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="aurea-install-form">
		<?php wp_nonce_field( 'aurea_installer' ); ?>
		<input type="hidden" name="action" value="aurea_installer">
		<input type="hidden" name="etape" value="<?php echo esc_attr( $etape ); ?>">
		<?php echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<button class="button <?php echo $principal ? 'button-primary button-hero' : ''; ?>"><?php echo esc_html( $libelle ); ?></button>
	</form>
	<?php
}

function aurea_ecran_installation() {
	$e        = aurea_etat_installation();
	$messages = get_transient( 'aurea_installation_messages' );
	delete_transient( 'aurea_installation_messages' );
	$ligne = function ( $ok, $titre, $texte, $etape = '', $bouton = '', $extra = '' ) {
		echo '<div class="aurea-install-etape ' . ( $ok ? 'is-ok' : 'is-afaire' ) . '"><span class="aurea-install-pastille">' . ( $ok ? '✓' : '•' ) . '</span><div><h3>' . esc_html( $titre ) . '</h3><p>' . wp_kses_post( $texte ) . '</p></div>';
		if ( $etape ) {
			aurea_bouton_etape( $etape, $bouton, false, $extra );
		}
		echo '</div>';
	};
	?>
	<div class="wrap aurea-install">
		<h1>Installation du thème Aurea Media</h1>
		<p class="aurea-install-intro">Chaque étape peut être relancée sans risque : rien n’est supprimé, et une page déjà modifiée n’est remplacée que si vous le demandez (l’ancienne version reste dans ses révisions).</p>
		<?php if ( $messages ) : ?>
			<div class="notice notice-success"><?php foreach ( $messages as $m ) : ?><p><?php echo esc_html( $m ); ?></p><?php endforeach; ?></div>
		<?php endif; ?>

		<div class="aurea-tout">
			<?php aurea_bouton_etape( 'tout', 'Tout installer', true ); ?>
			<ol class="aurea-tout-suivi" id="aurea-tout-suivi" hidden></ol>
		</div>
		<script>
		(function () {
			var form = document.querySelector('.aurea-tout form');
			if (!form || !window.fetch) return; // sans JavaScript : envoi classique du formulaire
			var suivi = document.getElementById('aurea-tout-suivi');
			var nonce = form.querySelector('[name=_wpnonce]').value;
			var etapes = [['studio', 'Réalisations et logos (par lots)'], ['pages', 'Pages, article et étude de cas'], ['reglages', 'Réglages WordPress'], ['rank_math', 'Rank Math SEO'], ['redirections', 'Redirections 301']];
			function appel(etape) {
				var d = new FormData(); d.append('action', 'aurea_installer_etape'); d.append('etape', etape); d.append('_ajax_nonce', nonce);
				return fetch(ajaxurl, { method: 'POST', body: d, credentials: 'same-origin' }).then(function (r) {
					return r.text().then(function (t) {
						try { return JSON.parse(t); } catch (e) { throw new Error(r.status >= 500 || !t ? 'le serveur a interrompu l’étape (délai dépassé ou erreur ' + r.status + ')' : 'réponse inattendue du serveur'); }
					});
				});
			}
			form.addEventListener('submit', async function (e) {
				e.preventDefault();
				var bouton = form.querySelector('button'); bouton.disabled = true; bouton.textContent = 'Installation en cours…';
				suivi.hidden = false; suivi.innerHTML = '';
				var erreurs = 0;
				for (var i = 0; i < etapes.length; i++) {
					var li = document.createElement('li'); li.className = 'en-cours'; li.innerHTML = '<b></b> <span>…</span>';
					li.querySelector('b').textContent = etapes[i][1]; suivi.appendChild(li);
					var texte = li.querySelector('span');
					try {
						var tours = 0, r;
						do {
							r = await appel(etapes[i][0]);
							if (!r.success) throw new Error(r.data || 'échec');
							texte.textContent = r.data.message;
							tours++;
						} while (etapes[i][0] === 'studio' && r.data.restant > 0 && r.data.progres && tours < 40);
						if (etapes[i][0] === 'studio' && r.data.restant > 0) throw new Error('certaines images n’ont pas pu être importées (' + r.data.restant + ' restantes) : relancez « Importer les réalisations et les logos »');
						li.className = r.data.avertissement ? 'attention' : 'ok';
					} catch (err) {
						li.className = 'erreur'; texte.textContent = 'Erreur : ' + err.message; erreurs++;
						if (etapes[i][0] === 'pages') break; // sans les pages, la suite n'a pas de sens
					}
				}
				bouton.textContent = erreurs ? 'Relancer l’installation' : 'Installation terminée';
				bouton.disabled = false;
				if (!erreurs) { var a = document.createElement('p'); a.innerHTML = '<a class="button button-primary" href="' + <?php echo wp_json_encode( home_url( '/' ) ); ?> + '" target="_blank">Voir le site</a> <a class="button" href="' + <?php echo wp_json_encode( admin_url( 'themes.php?page=' . AUREA_INSTALL ) ); ?> + '">Actualiser cet écran</a>'; suivi.after(a); }
			});
		})();
		</script>

		<div class="aurea-install-liste">
			<?php
			$ligne( $e['studio'] && $e['rank_math'], '1. Extensions',
				( $e['studio'] ? 'Aurea Studio est actif.' : '<strong>Aurea Studio</strong> n’est pas actif : Extensions → Ajouter → Téléverser <code>aurea-studio.zip</code>.' ) . ' '
				. ( $e['rank_math'] ? 'Rank Math SEO est actif.' : '<strong>Rank Math SEO</strong> n’est pas actif : Extensions → Ajouter → rechercher « Rank Math », installer et activer (assistant de configuration : mode Avancé, type « Entreprise »).' ) );
			$ligne( $e['realisations'] > 0 && $e['logos'] > 0, '2. Réalisations et logos',
				sprintf( '%d réalisations et %d logos dans Aurea Studio.', $e['realisations'], $e['logos'] ),
				'studio', 'Importer les réalisations et les logos' );
			$ligne( $e['pages'] === $e['pages_total'], '3. Pages du site',
				sprintf( '%d pages sur %d créées, avec leur titre SEO, leur description et leur mot clé Rank Math. Ajoute aussi la page Conseils, l’article « Combien coûte un site vitrine en 2026 ? » et l’étude de cas Havel Trading.', $e['pages'], $e['pages_total'] ),
				'pages', 'Créer les pages', '<label class="aurea-install-option"><input type="checkbox" name="remplacer" value="1"> Remettre le texte de la maquette dans les pages déjà importées</label>' );
			$ligne( $e['reglages'], '4. Réglages WordPress',
				'Page d’accueil, page des articles « Conseils », adresses des articles en <code>/conseils/nom-de-l-article/</code> (les anciennes adresses sont redirigées), fuseau horaire de Paris.',
				'reglages', 'Appliquer les réglages' );
			$ligne( $e['rank_reglee'], '5. Rank Math SEO',
				'Organisation « Aurea Media » (agence, logo, image de partage), séparateur « | », pas de schéma Article sur les pages, plan du site XML sans pages auteur. Les données structurées détaillées (services, prix, fondateur, fil d’Ariane) sont ajoutées par le thème.',
				'rank_math', 'Régler Rank Math' );
			$ligne( $e['redirections'], '6. Redirections 301',
				sprintf( '%d anciennes adresses redirigées vers les nouvelles pages (plan de l’audit SEO). Elles se gèrent ensuite dans Rank Math → Redirections.', count( aurea_redirections() ) ),
				'redirections', 'Créer les redirections' );
			?>
		</div>

		<?php aurea_ecran_nettoyage(); ?>

		<h2>Ensuite</h2>
		<ol class="aurea-install-suite">
			<li><a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=aurea' ) ); ?>">Vérifiez vos coordonnées</a> (téléphone, e-mail, adresse, LinkedIn).</li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=aurea-avis' ) ); ?>">Reliez vos avis Google</a> dans Aurea Studio (clé d’API et identifiant du lieu).</li>
			<li>Complétez les <a href="<?php echo esc_url( aurea_url( 'mentions-legales' ) ); ?>" target="_blank">mentions légales</a> et la <a href="<?php echo esc_url( aurea_url( 'politique-confidentialite' ) ); ?>" target="_blank">politique de confidentialité</a> (hébergeur, forme juridique : passages entre crochets).</li>
			<li>Envoyez le plan du site à Google : Search Console → Sitemaps → <code><?php echo esc_html( home_url( '/sitemap_index.xml' ) ); ?></code></li>
			<li>Faites un essai du formulaire de devis depuis la page <a href="<?php echo esc_url( aurea_url( 'contact' ) ); ?>" target="_blank">Contact</a> (copie dans « Messages reçus »).</li>
		</ol>
	</div>
	<style>
		.aurea-install{max-width:980px}
		.aurea-install-intro{font-size:14px;color:#50575e;max-width:720px}
		.aurea-install .button-hero{margin:14px 0 22px}
		.aurea-install-liste{display:grid;gap:12px}
		.aurea-install-etape{display:grid;grid-template-columns:34px 1fr auto;gap:14px;align-items:center;padding:18px 20px;background:#fff;border:1px solid #dcdcde;border-radius:10px}
		.aurea-install-etape h3{margin:0 0 4px;font-size:15px}
		.aurea-install-etape p{margin:0;color:#50575e}
		.aurea-install-pastille{width:30px;height:30px;border-radius:50%;display:grid;place-items:center;font-weight:700;background:#f0f0f1;color:#8c8f94}
		.is-ok .aurea-install-pastille{background:#C9A961;color:#0A0A0A}
		.aurea-install-form{display:grid;gap:8px;justify-items:end}
		.aurea-install-option{font-size:12px;color:#50575e}
		.aurea-install-suite li{margin-bottom:8px}
		.aurea-tout-suivi{margin:0 0 22px;padding:14px 18px 14px 36px;background:#fff;border:1px solid #dcdcde;border-radius:10px}
		.aurea-tout-suivi li{margin:6px 0}.aurea-tout-suivi li.ok b::after{content:" ✓";color:#00a32a}
		.aurea-tout-suivi li.attention b::after{content:" !";color:#dba617}.aurea-tout-suivi li.erreur{color:#d63638}
		.aurea-tout-suivi li.en-cours span{color:#646970}
		.aurea-apercu{margin-top:14px;padding:18px 20px;background:#fff;border:1px solid #dcdcde;border-radius:10px}
		.aurea-apercu h3{margin:18px 0 8px}.aurea-apercu table{margin-bottom:6px}.aurea-apercu code{font-size:11px}
		@media (max-width:782px){.aurea-install-etape{grid-template-columns:34px 1fr}.aurea-install-form{grid-column:1/-1;justify-items:start}}
	</style>
	<?php
}

/** Étape 7 : base propre, avec aperçu détaillé avant toute action. */
function aurea_ecran_nettoyage() {
	$fait = get_option( 'aurea_nettoye_le' );
	echo '<h2>Base propre</h2>';
	echo '<div class="aurea-install-etape ' . ( $fait ? 'is-ok' : 'is-afaire' ) . '"><span class="aurea-install-pastille">' . ( $fait ? '✓' : '7' ) . '</span><div><h3>7. Retirer les anciens contenus</h3><p>Les anciens articles et les pages qui ne font pas partie du nouveau site vont à la corbeille (récupérables 30 jours). Chaque adresse retirée est redirigée vers la page la plus proche par son sujet, et les anciennes redirections vers l’accueil sont réorientées. Les articles écrits après l’installation ne sont jamais touchés.</p></div>';
	echo '<a class="button" href="' . esc_url( admin_url( 'themes.php?page=' . AUREA_INSTALL . '&apercu=1#apercu' ) ) . '">Voir ce qui sera retiré</a></div>';
	if ( empty( $_GET['apercu'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$a     = aurea_nettoyage_apercu();
	$total = count( $a['articles'] ) + count( $a['pages'] ) + count( $a['termes'] ) + count( $a['redirections_accueil'] );
	echo '<div id="apercu" class="aurea-apercu">';
	if ( ! $total ) {
		echo '<p><strong>Rien à retirer : la base est propre.</strong></p></div>';
		return;
	}
	$groupes = array(
		'articles'             => 'Articles mis à la corbeille',
		'pages'                => 'Pages mises à la corbeille',
		'termes'               => 'Rubriques et mots clés supprimés',
		'redirections_accueil' => 'Redirections vers l’accueil réorientées',
	);
	foreach ( $groupes as $cle => $titre ) {
		if ( ! $a[ $cle ] ) {
			continue;
		}
		echo '<h3>' . esc_html( $titre ) . ' (' . count( $a[ $cle ] ) . ')</h3><table class="widefat striped"><thead><tr><th>Contenu</th><th>Ancienne adresse</th><th>Redirigée vers</th></tr></thead><tbody>';
		foreach ( $a[ $cle ] as $e ) {
			$adresses = array_map( function ( $x ) {
				return '<code>/' . esc_html( trim( $x, '/' ) ) . '/</code>';
			}, array_unique( $e['adresses'] ) );
			$vers     = aurea_chemin( aurea_url( $e['cible'] ) );
			echo '<tr><td>' . esc_html( $e['titre'] ) . '</td><td>' . implode( ' ', $adresses ) . '</td><td><code>/' . esc_html( $vers ? $vers . '/' : '' ) . '</code></td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</tbody></table>';
	}
	echo '<p>Pensez à faire une sauvegarde avant. Les contenus restent dans la corbeille 30 jours, et les redirections se modifient ensuite dans Rank Math → Redirections.</p>';
	aurea_bouton_etape( 'nettoyage', 'Mettre à la corbeille et rediriger', true );
	echo '<script>document.querySelector(".aurea-apercu form").addEventListener("submit",function(e){if(!confirm("Mettre ces contenus à la corbeille et créer les redirections ?"))e.preventDefault();});</script>';
	echo '</div>';
}

/* Juste après l'activation du thème : direction l'écran d'installation. */
add_action( 'after_switch_theme', function () {
	update_option( 'aurea_ouvrir_installation', 1 );
} );
add_action( 'admin_init', function () {
	if ( ! get_option( 'aurea_ouvrir_installation' ) || wp_doing_ajax() || ! current_user_can( 'manage_options' ) || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	delete_option( 'aurea_ouvrir_installation' );
	if ( ( $_GET['page'] ?? '' ) !== AUREA_INSTALL ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( admin_url( 'themes.php?page=' . AUREA_INSTALL ) );
		exit;
	}
} );

/* Tant que l'installation n'est pas faite : un rappel sur le tableau de bord et l'écran des thèmes. */
add_action( 'admin_notices', function () {
	$ecran = get_current_screen();
	if ( ! current_user_can( 'manage_options' ) || ! $ecran || ! in_array( $ecran->id, array( 'dashboard', 'themes' ), true ) ) {
		return;
	}
	$e = aurea_etat_installation();
	if ( $e['pages'] === $e['pages_total'] && $e['reglages'] ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>Thème Aurea Media :</strong> terminez la mise en place du site (pages, réglages, Rank Math, redirections). <a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=' . AUREA_INSTALL ) ) . '">Installation Aurea</a></p></div>';
} );

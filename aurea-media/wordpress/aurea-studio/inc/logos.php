<?php
/**
 * Logos clients : le bandeau « Ils nous ont confié leur image en ligne ».
 *
 * Un écran unique (Aurea Studio → Logos clients) pour tout gérer sans ouvrir de fiche :
 * ajouter des logos depuis la médiathèque, les ranger par glisser déposer, changer le nom,
 * le lien, masquer un logo ou l'afficher dans ses couleurs d'origine.
 *
 * Par défaut, un logo s'affiche en silhouette crème qui passe en or au survol : le fichier
 * sert de masque (CSS mask), il doit donc avoir un fond transparent (PNG ou WebP).
 * Un logo sur fond plein (JPG) s'affiche dans ses couleurs d'origine.
 *
 * Sur le site : aurea_logos_html() dans le thème, ou le shortcode [aurea_logos].
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_LOGO', 'aurea_logo' );

/* ------------------------------------------------------------------ */
/* Stockage : un logo = un contenu privé                               */
/* ------------------------------------------------------------------ */

add_action( 'init', function () {
	register_post_type( AUREA_LOGO, array(
		'labels'          => array( 'name' => 'Logos clients', 'singular_name' => 'Logo client' ),
		'public'          => false,
		'show_ui'         => false,
		'show_in_rest'    => false,
		'supports'        => array( 'title', 'page-attributes' ),
		'capability_type' => 'page',
		'map_meta_cap'    => true,
	) );
	add_shortcode( 'aurea_logos', function ( $atts ) {
		$atts = shortcode_atts( array( 'titre' => 'Ils nous ont confié leur image en ligne' ), $atts, 'aurea_logos' );
		return aurea_logos_html( array( 'titre' => $atts['titre'] ) );
	} );
} );

/**
 * Tous les logos, dans l'ordre du bandeau.
 *
 * @param bool $visibles_seulement Ne garder que les logos affichés sur le site.
 */
function aurea_logos( $visibles_seulement = false ) {
	$posts = get_posts( array(
		'post_type'      => AUREA_LOGO,
		'post_status'    => $visibles_seulement ? 'publish' : array( 'publish', 'draft' ),
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
	) );
	$logos = array();
	foreach ( $posts as $p ) {
		$image = (int) get_post_meta( $p->ID, '_logo_image', true );
		$logos[] = array(
			'id'      => $p->ID,
			'nom'     => $p->post_title,
			'image'   => $image,
			'url'     => $image ? (string) wp_get_attachment_image_url( $image, 'medium' ) : '',
			'opaque'  => $image ? 'image/jpeg' === get_post_mime_type( $image ) : false,
			'lien'    => (string) get_post_meta( $p->ID, '_logo_lien', true ),
			'couleur' => '1' === get_post_meta( $p->ID, '_logo_couleur', true ),
			'visible' => 'publish' === $p->post_status,
		);
	}
	return $logos;
}

/* ------------------------------------------------------------------ */
/* Affichage sur le site                                               */
/* ------------------------------------------------------------------ */

/**
 * Bandeau défilant des logos visibles.
 *
 * Le thème Aurea déclare add_theme_support( 'aurea-logos' ) et fournit ses propres styles ;
 * sinon, le plugin charge une feuille de style minimale.
 */
function aurea_logos_html( $args = array() ) {
	$args  = wp_parse_args( $args, array( 'titre' => 'Ils nous ont confié leur image en ligne', 'classe' => '' ) );
	$logos = array_filter( aurea_logos( true ), function ( $l ) { return $l['url']; } );
	if ( ! $logos ) {
		return '';
	}
	if ( ! current_theme_supports( 'aurea-logos' ) ) {
		wp_enqueue_style( 'aurea-logos', AUREA_STUDIO_URL . 'assets/css/logos.css', array(), AUREA_STUDIO_VERSION );
	}

	$items = '';
	foreach ( $logos as $l ) {
		$items .= aurea_logo_item( $l );
	}
	// La liste est répétée une fois pour que le défilement boucle sans saut ; la copie est cachée aux lecteurs d'écran.
	$copie = '<div class="aurea-logos-copie" aria-hidden="true" inert>' . $items . '</div>';

	$html  = '<section class="aurea-logos ' . esc_attr( $args['classe'] ) . '" aria-label="' . esc_attr( $args['titre'] ) . '">';
	$html .= $args['titre'] ? '<p class="aurea-logos-titre">' . esc_html( $args['titre'] ) . '</p>' : '';
	$html .= '<div class="aurea-logos-rang">' . $items . $copie . '</div></section>';
	return $html;
}

function aurea_logo_item( $l ) {
	$nom = esc_attr( $l['nom'] );
	if ( $l['couleur'] || $l['opaque'] ) {
		$visuel = '<img class="aurea-logo is-couleur" src="' . esc_url( $l['url'] ) . '" alt="' . $nom . '" loading="lazy" decoding="async">';
	} else {
		$visuel = '<span class="aurea-logo" role="img" aria-label="' . $nom . '" style="--logo:url(\'' . esc_url( $l['url'] ) . '\')"></span>';
	}
	if ( $l['lien'] ) {
		return '<a class="aurea-logo-lien" href="' . esc_url( $l['lien'] ) . '" target="_blank" rel="noopener">' . $visuel . '</a>';
	}
	return $visuel;
}

/* ------------------------------------------------------------------ */
/* Écran d'administration                                              */
/* ------------------------------------------------------------------ */

add_action( 'admin_enqueue_scripts', function () {
	if ( 'aurea-logos' !== ( $_GET['page'] ?? '' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'aurea-studio', AUREA_STUDIO_URL . 'assets/admin/studio.css', array(), AUREA_STUDIO_VERSION );
	wp_enqueue_style( 'aurea-logos', AUREA_STUDIO_URL . 'assets/css/logos.css', array(), AUREA_STUDIO_VERSION );
	wp_enqueue_script( 'aurea-logos-admin', AUREA_STUDIO_URL . 'assets/admin/logos.js', array( 'jquery', 'jquery-ui-sortable' ), AUREA_STUDIO_VERSION, true );
	wp_localize_script( 'aurea-logos-admin', 'aureaLogos', array(
		'ajax'      => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'aurea_logos' ),
		'logos'     => aurea_logos(),
		'aImporter' => count( aurea_logos_a_importer() ),
	) );
} );

function aurea_logos_ecran() {
	echo '<div class="wrap aurea-studio">';
	aurea_studio_entete( 'Logos clients', 'Le bandeau « Ils nous ont confié leur image en ligne » de l’accueil. Glissez les logos pour changer leur ordre.' );
	?>
	<div class="aurea-apercu" aria-label="Aperçu du bandeau sur le site">
		<span class="aurea-apercu-label">Aperçu sur le site</span>
		<div id="aurea-apercu-zone"></div>
	</div>

	<div class="aurea-barre">
		<button type="button" class="aurea-btn is-or" id="aurea-ajouter">
			<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
			Ajouter des logos
		</button>
		<button type="button" class="aurea-btn" id="aurea-importer" hidden>Importer les logos du site actuel</button>
		<span class="aurea-compteur" id="aurea-compteur"></span>
		<span class="aurea-etat" id="aurea-etat" role="status" aria-live="polite"></span>
		<button type="button" class="aurea-btn is-noir" id="aurea-enregistrer" disabled>Enregistrer</button>
	</div>

	<ul class="aurea-grille" id="aurea-grille"></ul>
	<p class="aurea-vide" id="aurea-vide" hidden>Aucun logo pour l’instant. Cliquez sur « Ajouter des logos » et choisissez un ou plusieurs fichiers dans la médiathèque.</p>

	<details class="aurea-aide">
		<summary>Quel fichier utiliser ?</summary>
		<p>Un <strong>PNG à fond transparent</strong>, environ 400 px de large. Le logo s’affiche en silhouette crème, puis en or au survol, pour un bandeau homogène.</p>
		<p>Si le logo n’a pas de fond transparent (JPG, logo dans un cadre), cochez <strong>Couleurs d’origine</strong> : il s’affiche tel quel.</p>
	</details>
	<?php
	echo '</div>';
}

/* ------------------------------------------------------------------ */
/* Enregistrement (AJAX)                                               */
/* ------------------------------------------------------------------ */

add_action( 'wp_ajax_aurea_logos_enregistrer', function () {
	check_ajax_referer( 'aurea_logos' );
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_send_json_error( 'Vous n’avez pas le droit de modifier les logos.', 403 );
	}

	$logos     = json_decode( wp_unslash( $_POST['logos'] ?? '[]' ), true );
	$supprimes = json_decode( wp_unslash( $_POST['supprimes'] ?? '[]' ), true );
	if ( ! is_array( $logos ) || ! is_array( $supprimes ) ) {
		wp_send_json_error( 'Données illisibles, rechargez la page.', 400 );
	}

	foreach ( $supprimes as $id ) {
		$id = (int) $id;
		if ( $id && AUREA_LOGO === get_post_type( $id ) ) {
			wp_delete_post( $id, true );
		}
	}

	foreach ( array_values( $logos ) as $rang => $l ) {
		$id    = (int) ( $l['id'] ?? 0 );
		$image = (int) ( $l['image'] ?? 0 );
		if ( $id && AUREA_LOGO !== get_post_type( $id ) ) {
			continue;
		}
		if ( ! $image || ! wp_attachment_is_image( $image ) ) {
			continue;
		}
		$donnees = array(
			'post_type'   => AUREA_LOGO,
			'post_title'  => sanitize_text_field( $l['nom'] ?? '' ) ?: get_the_title( $image ),
			'post_status' => empty( $l['visible'] ) ? 'draft' : 'publish',
			'menu_order'  => $rang,
		);
		if ( $id ) {
			$donnees['ID'] = $id;
			wp_update_post( $donnees );
		} else {
			$id = wp_insert_post( $donnees );
		}
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_logo_image', $image );
		update_post_meta( $id, '_logo_lien', esc_url_raw( $l['lien'] ?? '' ) );
		update_post_meta( $id, '_logo_couleur', empty( $l['couleur'] ) ? '0' : '1' );
	}

	wp_send_json_success( aurea_logos() );
} );

/* ------------------------------------------------------------------ */
/* Import des logos du site actuel (livrés avec le plugin)             */
/* ------------------------------------------------------------------ */

/**
 * Logos fournis dans assets/logos, avec leur nom affiché.
 */
function aurea_logos_fournis() {
	return array(
		'havel-trading'         => 'Havel Trading',
		'sunrock'               => 'Sunrock',
		'dual-tech'             => 'Dual Tech',
		'groupe-verisol'        => 'Groupe Verisol',
		'inas'                  => 'INAS',
		'portugal-expert'       => 'Portugal Expert Properties',
		'novexia'               => 'Novexia Construction',
		'uapc'                  => 'UAPC',
		'indra'                 => 'Indra Associates',
		'enzym'                 => 'Enzym',
		'tcm-grand-est'         => 'TCM Grand Est',
		'institut-la-raison'    => 'Institut La Raison',
		'xpert-design'          => 'Xpert Design',
		'centre-franco-iranien' => 'Centre Franco Iranien',
		'duchentyls'            => 'Domaine Duchentyls',
		'cercle-la-raison'      => 'Cercle La Raison',
		'tayeb'                 => 'Tayeb Benabderrahmane',
		'eddy-bilou'            => 'Eddy Bilou',
	);
}

/**
 * Logos fournis qui ne sont pas encore dans la médiathèque.
 */
function aurea_logos_a_importer() {
	$deja = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => '_aurea_logo_source',
	) );
	$sources = array_map( function ( $id ) { return get_post_meta( $id, '_aurea_logo_source', true ); }, $deja );
	return array_diff_key( aurea_logos_fournis(), array_flip( $sources ) );
}

/**
 * Importe les logos livrés avec l'extension qui ne sont pas encore dans la médiathèque.
 * Utilisé par le bouton de l'écran Logos clients et par l'installation du thème Aurea.
 *
 * @return int Nombre de logos ajoutés.
 */
function aurea_logos_importer() {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$rang    = count( aurea_logos() );
	$ajoutes = 0;
	foreach ( aurea_logos_a_importer() as $fichier => $nom ) {
		$source = AUREA_STUDIO_DIR . 'assets/logos/' . $fichier . '.png';
		if ( ! file_exists( $source ) ) {
			continue;
		}
		// media_handle_sideload déplace le fichier : on lui donne une copie.
		$copie = wp_tempnam( $fichier . '.png' );
		copy( $source, $copie );
		$image = media_handle_sideload( array( 'name' => 'logo-' . $fichier . '.png', 'tmp_name' => $copie ), 0, 'Logo ' . $nom );
		if ( is_wp_error( $image ) ) {
			@unlink( $copie );
			continue;
		}
		update_post_meta( $image, '_aurea_logo_source', $fichier );
		update_post_meta( $image, '_wp_attachment_image_alt', $nom );
		$id = wp_insert_post( array(
			'post_type'   => AUREA_LOGO,
			'post_title'  => $nom,
			'post_status' => 'publish',
			'menu_order'  => $rang++,
		) );
		update_post_meta( $id, '_logo_image', $image );
		update_post_meta( $id, '_logo_couleur', '0' );
		$ajoutes++;
	}

	return $ajoutes;
}

add_action( 'wp_ajax_aurea_logos_importer', function () {
	check_ajax_referer( 'aurea_logos' );
	if ( ! current_user_can( 'upload_files' ) || ! current_user_can( 'edit_pages' ) ) {
		wp_send_json_error( 'Vous n’avez pas le droit d’ajouter des fichiers.', 403 );
	}
	$ajoutes = aurea_logos_importer();
	wp_send_json_success( array( 'logos' => aurea_logos(), 'ajoutes' => $ajoutes ) );
} );

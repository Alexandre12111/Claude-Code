<?php
/**
 * Réalisations : la source unique de tous les projets affichés sur le site.
 *
 * Chaque réalisation a sa fiche publique (/realisations/nom-du-projet/, étude de cas indexable)
 * et alimente automatiquement, via aurea_realisations() :
 * - le mur de captures du haut de l'accueil (« Mur du hero ») ;
 * - le portfolio défilant de l'accueil (« À la une ») ;
 * - les galeries des pages services (par type) et des pages secteurs (par secteur) ;
 * - la page /realisations/.
 * Modifier, masquer ou réordonner une réalisation ici met donc tout le site à jour.
 *
 * Écran rapide : Aurea Studio → Réalisations (ordre par glisser déposer, interrupteurs, champs
 * principaux). La fiche complète (texte de l'étude de cas, SEO Rank Math) reste l'éditeur WordPress.
 */

defined( 'ABSPATH' ) || exit;

define( 'AUREA_REAL', 'aurea_realisation' );
define( 'AUREA_REAL_TYPE', 'aurea_type' );
define( 'AUREA_REAL_SECTEUR', 'aurea_secteur' );

/* ------------------------------------------------------------------ */
/* Contenu, types et secteurs                                          */
/* ------------------------------------------------------------------ */

add_action( 'init', 'aurea_real_enregistrer' );

function aurea_real_enregistrer() {
	register_post_type( AUREA_REAL, array(
		'labels'          => array(
			'name'               => 'Réalisations',
			'singular_name'      => 'Réalisation',
			'add_new'            => 'Ajouter',
			'add_new_item'       => 'Ajouter une réalisation',
			'edit_item'          => 'Modifier la réalisation',
			'view_item'          => 'Voir l’étude de cas',
			'all_items'          => 'Fiches complètes',
			'search_items'       => 'Rechercher une réalisation',
			'not_found'          => 'Aucune réalisation.',
			'not_found_in_trash' => 'Aucune réalisation dans la corbeille.',
		),
		'public'          => true,
		'show_ui'         => true,
		'show_in_menu'    => false,
		'show_in_rest'    => true,
		'has_archive'     => 'realisations',
		'rewrite'         => array( 'slug' => 'realisations', 'with_front' => false ),
		'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		'capability_type' => 'page',
		'map_meta_cap'    => true,
	) );

	$commun = array( 'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'hierarchical' => true, 'show_admin_column' => true, 'rewrite' => false );
	register_taxonomy( AUREA_REAL_TYPE, AUREA_REAL, $commun + array( 'labels' => array( 'name' => 'Types de projet', 'singular_name' => 'Type de projet' ) ) );
	register_taxonomy( AUREA_REAL_SECTEUR, AUREA_REAL, $commun + array( 'labels' => array( 'name' => 'Secteurs', 'singular_name' => 'Secteur' ) ) );

	foreach ( array( '_real_url', '_real_lieu', '_real_accroche' ) as $cle ) {
		register_post_meta( AUREA_REAL, $cle, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'auth_callback' => function () { return current_user_can( 'edit_pages' ); } ) );
	}
	foreach ( array( '_real_une', '_real_mur' ) as $cle ) {
		register_post_meta( AUREA_REAL, $cle, array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'auth_callback' => function () { return current_user_can( 'edit_pages' ); } ) );
	}
	register_post_meta( AUREA_REAL, '_real_mobile', array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true, 'auth_callback' => function () { return current_user_can( 'edit_pages' ); } ) );
}

/**
 * Types et secteurs proposés d'office (le slug sert aux pages services et secteurs du thème).
 */
function aurea_real_termes_de_base() {
	return array(
		AUREA_REAL_TYPE    => array(
			'site-vitrine'   => 'Site vitrine',
			'site-ecommerce' => 'Site e-commerce',
			'refonte'        => 'Refonte',
			'identite'       => 'Identité visuelle',
			'motion-design'  => 'Motion design',
			'maquettes'      => 'Maquettes de présentation',
		),
		AUREA_REAL_SECTEUR => array(
			'avocat'         => 'Avocats et droit',
			'sante'          => 'Santé',
			'architecte'     => 'Architecture et immobilier',
			'restaurant'     => 'Restauration et hôtellerie',
			'artisan-btp'    => 'Artisans et BTP',
			'coach'          => 'Coachs et consultants',
			'industrie'      => 'Industrie et énergie',
			'commerce'       => 'Commerce et beauté',
			'evenementiel'   => 'Événementiel et institutions',
			'creatifs'       => 'Photographes et créatifs',
		),
	);
}

function aurea_real_creer_termes() {
	foreach ( aurea_real_termes_de_base() as $tax => $termes ) {
		foreach ( $termes as $slug => $nom ) {
			if ( ! term_exists( $slug, $tax ) ) {
				wp_insert_term( $nom, $tax, array( 'slug' => $slug ) );
			}
		}
	}
}

/* ------------------------------------------------------------------ */
/* Lecture pour le thème                                               */
/* ------------------------------------------------------------------ */

/**
 * Réalisations publiées, dans l'ordre choisi.
 *
 * @param array $args {
 *     @type bool   $une     Seulement celles « À la une ».
 *     @type bool   $mur     Seulement celles du mur du hero.
 *     @type string $type    Slug de type (site-vitrine, site-ecommerce…).
 *     @type string $secteur Slug de secteur (avocat, sante…).
 *     @type int    $nombre  Nombre maximum (-1 : toutes).
 *     @type bool   $tout    Inclure les brouillons (écran d'administration).
 * }
 */
function aurea_realisations( $args = array() ) {
	$args  = wp_parse_args( $args, array( 'une' => false, 'mur' => false, 'type' => '', 'secteur' => '', 'nombre' => -1, 'tout' => false ) );
	$query = array(
		'post_type'      => AUREA_REAL,
		'post_status'    => $args['tout'] ? array( 'publish', 'draft', 'pending' ) : 'publish',
		'posts_per_page' => (int) $args['nombre'],
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'meta_query'     => array(),
		'tax_query'      => array(),
	);
	foreach ( array( 'une', 'mur' ) as $drapeau ) {
		if ( $args[ $drapeau ] ) {
			$query['meta_query'][] = array( 'key' => '_real_' . $drapeau, 'value' => '1' );
		}
	}
	if ( $args['type'] ) {
		$query['tax_query'][] = array( 'taxonomy' => AUREA_REAL_TYPE, 'field' => 'slug', 'terms' => $args['type'] );
	}
	if ( $args['secteur'] ) {
		$query['tax_query'][] = array( 'taxonomy' => AUREA_REAL_SECTEUR, 'field' => 'slug', 'terms' => $args['secteur'] );
	}
	return array_map( 'aurea_realisation', get_posts( $query ) );
}

/**
 * Une réalisation sous forme de tableau simple.
 */
function aurea_realisation( $post ) {
	$post   = get_post( $post );
	$image  = (int) get_post_thumbnail_id( $post );
	$mobile = (int) get_post_meta( $post->ID, '_real_mobile', true );
	$terme  = function ( $tax ) use ( $post ) {
		$t = get_the_terms( $post, $tax );
		return $t && ! is_wp_error( $t ) ? array( 'slug' => $t[0]->slug, 'nom' => $t[0]->name ) : array( 'slug' => '', 'nom' => '' );
	};
	$url = (string) get_post_meta( $post->ID, '_real_url', true );
	return array(
		'id'       => $post->ID,
		'titre'    => get_the_title( $post ),
		'fiche'    => get_permalink( $post ),
		'url'      => $url,
		'domaine'  => $url ? preg_replace( '#^www\.#', '', (string) wp_parse_url( $url, PHP_URL_HOST ) ) : '',
		'accroche' => (string) get_post_meta( $post->ID, '_real_accroche', true ),
		'lieu'     => (string) get_post_meta( $post->ID, '_real_lieu', true ),
		'type'     => $terme( AUREA_REAL_TYPE ),
		'secteur'  => $terme( AUREA_REAL_SECTEUR ),
		'image'    => $image,
		'image_url'=> $image ? (string) wp_get_attachment_image_url( $image, 'large' ) : '',
		'mobile'   => $mobile,
		'une'      => '1' === (string) get_post_meta( $post->ID, '_real_une', true ),
		'mur'      => '1' === (string) get_post_meta( $post->ID, '_real_mur', true ),
		'visible'  => 'publish' === $post->post_status,
	);
}

/* ------------------------------------------------------------------ */
/* Écran rapide                                                        */
/* ------------------------------------------------------------------ */

add_action( 'admin_enqueue_scripts', function () {
	if ( AUREA_STUDIO_MENU !== ( $_GET['page'] ?? '' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'aurea-studio', AUREA_STUDIO_URL . 'assets/admin/studio.css', array(), AUREA_STUDIO_VERSION );
	wp_enqueue_script( 'aurea-realisations-admin', AUREA_STUDIO_URL . 'assets/admin/realisations.js', array( 'jquery', 'jquery-ui-sortable' ), AUREA_STUDIO_VERSION, true );
	$termes = array();
	foreach ( array( 'type' => AUREA_REAL_TYPE, 'secteur' => AUREA_REAL_SECTEUR ) as $cle => $tax ) {
		$termes[ $cle ] = array_map( function ( $t ) { return array( 'slug' => $t->slug, 'nom' => $t->name ); }, get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) ) );
	}
	wp_localize_script( 'aurea-realisations-admin', 'aureaReal', array(
		'ajax'      => admin_url( 'admin-ajax.php' ),
		'nonce'     => wp_create_nonce( 'aurea_realisations' ),
		'items'     => aurea_realisations( array( 'tout' => true ) ),
		'termes'    => $termes,
		'nouvelle'  => admin_url( 'post-new.php?post_type=' . AUREA_REAL ),
		'aImporter' => count( aurea_real_a_importer() ),
	) );
} );

function aurea_realisations_ecran() {
	echo '<div class="wrap aurea-studio">';
	aurea_studio_entete( 'Réalisations', 'Tous les projets du site. Ce que vous changez ici se met à jour partout : haut de l’accueil, portfolio, pages services et secteurs, page Réalisations.' );
	?>
	<div class="aurea-barre">
		<button type="button" class="aurea-btn is-or" id="aurea-r-ajouter">
			<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
			Ajouter une réalisation
		</button>
		<button type="button" class="aurea-btn" id="aurea-r-importer" hidden>Importer les projets du site actuel</button>
		<label class="aurea-filtre">Afficher
			<select id="aurea-r-filtre">
				<option value="">Toutes</option>
				<option value="une">À la une</option>
				<option value="mur">Dans le mur du hero</option>
				<option value="masque">Masquées</option>
			</select>
		</label>
		<span class="aurea-compteur" id="aurea-r-compteur"></span>
		<span class="aurea-etat" id="aurea-r-etat" role="status" aria-live="polite"></span>
		<button type="button" class="aurea-btn is-noir" id="aurea-r-enregistrer" disabled>Enregistrer</button>
	</div>
	<ul class="aurea-grille is-real" id="aurea-r-grille"></ul>
	<p class="aurea-vide" id="aurea-r-vide" hidden>Aucune réalisation. Cliquez sur « Ajouter une réalisation » et choisissez la capture d’écran du site.</p>
	<details class="aurea-aide">
		<summary>Comment ça marche ?</summary>
		<p><strong>Capture</strong> : une image de la page d’accueil du site client, 1600 × 1000 px environ (le haut de l’image est montré en priorité). <strong>Capture mobile</strong> (facultative) : utilisée dans les maquettes de téléphone.</p>
		<p><strong>À la une</strong> : apparaît dans le portfolio défilant de l’accueil. <strong>Mur du hero</strong> : apparaît dans le mur de captures en haut de l’accueil. <strong>Visible</strong> : décocher retire la réalisation de tout le site sans la supprimer.</p>
		<p><strong>Type</strong> et <strong>secteur</strong> rangent le projet dans les galeries des pages services (Site vitrine, E-commerce…) et des pages secteurs (Avocats, Santé…).</p>
		<p>« Fiche complète » ouvre l’éditeur WordPress pour écrire l’étude de cas et régler le référencement dans Rank Math.</p>
	</details>
	<?php
	echo '</div>';
}

/* ------------------------------------------------------------------ */
/* Enregistrement (AJAX)                                               */
/* ------------------------------------------------------------------ */

add_action( 'wp_ajax_aurea_realisations_enregistrer', function () {
	check_ajax_referer( 'aurea_realisations' );
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_send_json_error( 'Vous n’avez pas le droit de modifier les réalisations.', 403 );
	}
	$items     = json_decode( wp_unslash( $_POST['items'] ?? '[]' ), true );
	$supprimes = json_decode( wp_unslash( $_POST['supprimes'] ?? '[]' ), true );
	if ( ! is_array( $items ) || ! is_array( $supprimes ) ) {
		wp_send_json_error( 'Données illisibles, rechargez la page.', 400 );
	}

	foreach ( $supprimes as $id ) {
		$id = (int) $id;
		if ( $id && AUREA_REAL === get_post_type( $id ) && current_user_can( 'delete_post', $id ) ) {
			wp_trash_post( $id ); // Récupérable dans la corbeille des fiches complètes.
		}
	}

	foreach ( array_values( $items ) as $rang => $r ) {
		$id = (int) ( $r['id'] ?? 0 );
		if ( $id && ( AUREA_REAL !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) ) {
			continue;
		}
		$donnees = array(
			'post_type'   => AUREA_REAL,
			'post_title'  => sanitize_text_field( $r['titre'] ?? '' ) ?: 'Nouvelle réalisation',
			'post_status' => empty( $r['visible'] ) ? 'draft' : 'publish',
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
		$image = (int) ( $r['image'] ?? 0 );
		if ( $image && wp_attachment_is_image( $image ) ) {
			set_post_thumbnail( $id, $image );
		}
		$mobile = (int) ( $r['mobile'] ?? 0 );
		update_post_meta( $id, '_real_mobile', $mobile && wp_attachment_is_image( $mobile ) ? $mobile : 0 );
		update_post_meta( $id, '_real_url', esc_url_raw( $r['url'] ?? '' ) );
		update_post_meta( $id, '_real_lieu', sanitize_text_field( $r['lieu'] ?? '' ) );
		update_post_meta( $id, '_real_accroche', sanitize_textarea_field( $r['accroche'] ?? '' ) );
		update_post_meta( $id, '_real_une', empty( $r['une'] ) ? '0' : '1' );
		update_post_meta( $id, '_real_mur', empty( $r['mur'] ) ? '0' : '1' );
		foreach ( array( 'type' => AUREA_REAL_TYPE, 'secteur' => AUREA_REAL_SECTEUR ) as $cle => $tax ) {
			$slug = sanitize_title( $r[ $cle ] ?? '' );
			wp_set_object_terms( $id, $slug ? array( $slug ) : array(), $tax );
		}
	}

	wp_send_json_success( aurea_realisations( array( 'tout' => true ) ) );
} );

/* ------------------------------------------------------------------ */
/* Import des projets du site actuel (captures livrées avec le plugin) */
/* ------------------------------------------------------------------ */

function aurea_real_fournies() {
	return array(
		'havel-trading'   => array( 'Havel Trading', 'https://haveltrading.com/', 'site-vitrine', 'industrie', 'Genève', 'Négoce d’énergie indépendant : une présence institutionnelle sobre, à la hauteur des marchés européens.', true, true ),
		'sunrock'         => array( 'Sunrock', 'https://www.sunrock.fr/', 'site-ecommerce', 'industrie', 'France', 'Catalogue balistique et tactique pour les acteurs de la Défense et du maintien de l’ordre.', true, true ),
		'portugal-expert' => array( 'Portugal Expert Properties', 'https://portugalexpertproperties.com/', 'site-vitrine', 'architecte', 'Lisbonne', 'Résidences de luxe sur mesure au sud de Lisbonne, mises en scène comme un magazine.', true, true ),
		'eddy-bilou'      => array( 'Eddy Bilou', 'https://eddybilouphotography.com/', 'site-vitrine', 'creatifs', 'Paris', 'Une galerie cinématographique pour un photographe de mariage issu de la mode.', true, true ),
		'verisol'         => array( 'Groupe Verisol', 'https://groupe-verisol.fr/', 'site-vitrine', 'industrie', 'France', 'Architecture claire pour présenter les filiales d’un groupe de la transition énergétique.', true, true ),
		'perle-hair'      => array( 'Perle Hair', 'https://perlehair.fr/', 'site-ecommerce', 'commerce', 'France', 'Boutique d’extensions et de soins capillaires, pensée pour l’achat sur mobile.', true, true ),
		'novexia'         => array( 'Novexia Construction', 'https://novexia-construction.fr/', 'site-vitrine', 'artisan-btp', 'Occitanie', 'Entreprise du bâtiment : construction, rénovation, isolation et extensions de maisons.', false, true ),
		'xpert-medical'   => array( 'Xpert Medical', 'https://xpert-medical.fr/', 'site-vitrine', 'sante', 'France', 'Réseau de centres médicaux pluridisciplinaires, pour un accès rapide et simplifié aux soins.', false, true ),
		'dual-tech'       => array( 'Dual Tech', 'https://dualtech-event.eu/', 'site-vitrine', 'evenementiel', 'Europe', 'Salon européen de l’innovation duale et de la souveraineté technologique.', false, true ),
		'villa-moniris'   => array( 'Villa Moniris', 'https://villamoniris.fr/', 'site-vitrine', 'restaurant', 'La Crau', 'Propriété de charme pour 14 personnes, avec réservation par espace et galerie immersive.', false, true ),
		'barber-korner'   => array( 'Barber Korner', 'https://barber-korner.fr/', 'site-vitrine', 'commerce', 'Paris', 'Salon de barbier traditionnel avec prise de rendez vous en ligne.', false, true ),
		'laurence-paoli'  => array( 'Maître Laurence Paoli', 'https://avocat-paoli.fr/', 'site-vitrine', 'avocat', 'Essonne', 'Avocate spécialisée en droit de la famille, des personnes et de leur patrimoine.', false, false ),
		'normande-isolation' => array( 'Normande d’Isolation', 'https://normande-disolation.com/', 'site-vitrine', 'industrie', 'Normandie', 'Isolation industrielle : calorifugeage, acoustique, protection incendie et échafaudages.', false, false ),
		'tcm-grand-est'   => array( 'TCM Grand Est', 'https://tcm-grandest.fr/', 'site-vitrine', 'industrie', 'Grand Est', 'Travaux industriels sur mesure en soudure, tuyauterie et mécanique.', false, false ),
		'sultan'          => array( 'Sultan Conseil & Leviers', 'https://sultan.cl/', 'site-vitrine', 'coach', 'International', 'Cabinet indépendant pour les transformations à fort enjeu, entre politique, économie et géopolitique.', false, false ),
		'enzym'           => array( 'Enzym', 'https://enzymco.fr/', 'site-vitrine', 'coach', 'France', 'Coaching et formation pour dirigeants, managers et particuliers.', false, false ),
		'inas'            => array( 'INAS', 'https://inas-france.fr/', 'site-vitrine', 'evenementiel', 'Paris', 'Think tank indépendant dédié à la souveraineté nationale et européenne.', false, false ),
		'iff-paris'       => array( 'Centre Franco Iranien', 'https://iff-paris.com/', 'site-vitrine', 'evenementiel', 'Paris', 'Association qui crée des ponts culturels, économiques et scientifiques entre la France et l’Iran.', false, false ),
		'cercle-la-raison' => array( 'Cercle La Raison', 'https://raison-cercle.fr/', 'site-vitrine', 'restaurant', 'Bordeaux', 'Cercle privé de réservation d’événements d’exception, dans un cadre confidentiel.', false, false ),
		'duchentyls'      => array( 'Domaine Duchentyls', 'https://domaine-duchentyls.fr/', 'site-vitrine', 'restaurant', 'France', 'Lieu de charme pour séjours, réceptions privées et événements en pleine nature.', false, false ),
		'massy'           => array( 'Changer d’Air à Massy', 'https://massy-changer-dair.fr/', 'site-vitrine', 'evenementiel', 'Massy', 'Plateforme de campagne municipale pour les élections de Massy 2026.', false, false ),
		'tayeb'           => array( 'Affaire Tayeb Benabderrahmane', 'https://affaire-benabderrahmane.com/', 'site-vitrine', 'evenementiel', 'International', 'Site biographique consacré à l’affaire Tayeb Benabderrahmane.', false, false ),
		'maryes'          => array( 'Maryes Vidéo', 'https://maryesvideaste.com/', 'site-vitrine', 'creatifs', 'Paris', 'Vidéaste de mariage à l’approche naturelle, discrète et émotionnelle.', false, true ),
	);
}

function aurea_real_a_importer() {
	$deja = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_aurea_real_source' ) );
	$sources = array_map( function ( $id ) { return get_post_meta( $id, '_aurea_real_source', true ); }, $deja );
	return array_diff_key( aurea_real_fournies(), array_flip( $sources ) );
}

/**
 * Copie une image livrée avec le plugin dans la médiathèque.
 */
function aurea_studio_importer_image( $chemin, $nom_fichier, $titre ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	if ( ! file_exists( $chemin ) ) {
		return 0;
	}
	// media_handle_sideload déplace le fichier : on lui donne une copie.
	$copie = wp_tempnam( $nom_fichier );
	copy( $chemin, $copie );
	$id = media_handle_sideload( array( 'name' => $nom_fichier, 'tmp_name' => $copie ), 0, $titre );
	if ( is_wp_error( $id ) ) {
		@unlink( $copie );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $titre );
	return $id;
}

/**
 * Importe les réalisations livrées avec l'extension (captures, textes, types, secteurs).
 * Utilisé par le bouton de l'écran Réalisations et par l'installation du thème Aurea.
 *
 * @return int Nombre de réalisations ajoutées.
 */
function aurea_real_importer() {
	aurea_real_creer_termes();
	$rang    = count( aurea_realisations( array( 'tout' => true ) ) );
	$ajoutes = 0;
	foreach ( aurea_real_a_importer() as $fichier => $r ) {
		list( $titre, $url, $type, $secteur, $lieu, $accroche, $une, $mur ) = $r;
		$image = aurea_studio_importer_image( AUREA_STUDIO_DIR . 'assets/realisations/' . $fichier . '.webp', 'realisation-' . $fichier . '.webp', 'Site ' . $titre . ' réalisé par Aurea Media' );
		if ( ! $image ) {
			continue;
		}
		update_post_meta( $image, '_aurea_real_source', $fichier );
		$id = wp_insert_post( array(
			'post_type'    => AUREA_REAL,
			'post_title'   => $titre,
			'post_excerpt' => $accroche,
			'post_status'  => 'publish',
			'menu_order'   => $rang++,
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		set_post_thumbnail( $id, $image );
		update_post_meta( $id, '_real_cle', $fichier );
		update_post_meta( $id, '_real_url', $url );
		update_post_meta( $id, '_real_lieu', $lieu );
		update_post_meta( $id, '_real_accroche', $accroche );
		update_post_meta( $id, '_real_une', $une ? '1' : '0' );
		update_post_meta( $id, '_real_mur', $mur ? '1' : '0' );
		wp_set_object_terms( $id, array( $type ), AUREA_REAL_TYPE );
		wp_set_object_terms( $id, array( $secteur ), AUREA_REAL_SECTEUR );
		$mobile_src = AUREA_STUDIO_DIR . 'assets/realisations/' . $fichier . '-mobile.webp';
		if ( file_exists( $mobile_src ) ) {
			update_post_meta( $id, '_real_mobile', aurea_studio_importer_image( $mobile_src, 'realisation-' . $fichier . '-mobile.webp', 'Site ' . $titre . ' sur mobile' ) );
		}
		$ajoutes++;
	}
	flush_rewrite_rules( false );
	return $ajoutes;
}

add_action( 'wp_ajax_aurea_realisations_importer', function () {
	check_ajax_referer( 'aurea_realisations' );
	if ( ! current_user_can( 'upload_files' ) || ! current_user_can( 'edit_pages' ) ) {
		wp_send_json_error( 'Vous n’avez pas le droit d’ajouter des fichiers.', 403 );
	}
	$ajoutes = aurea_real_importer();
	wp_send_json_success( array( 'items' => aurea_realisations( array( 'tout' => true ) ), 'ajoutes' => $ajoutes ) );
} );

/* Adresse /realisations/ active dès l'activation du plugin. */
register_activation_hook( AUREA_STUDIO_DIR . 'aurea-studio.php', function () {
	aurea_real_enregistrer();
	aurea_real_creer_termes();
	flush_rewrite_rules( false );
} );

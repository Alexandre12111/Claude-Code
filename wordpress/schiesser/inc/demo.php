<?php
/**
 * Import du contenu de démonstration (en un clic depuis l'administration).
 *
 * Crée les catégories, les 8 produits de la maquette, les pages
 * (Accueil, La boutique, Tea Room, Notre histoire, Nous visiter, Contact)
 * avec leurs blocs, et le menu principal. Ne crée rien en double.
 */

defined( 'ABSPATH' ) || exit;

/* Réglages par défaut dès l'activation du thème */
add_action( 'after_switch_theme', function () {
	if ( false === get_option( SCHIESSER_OPTION ) ) {
		add_option( SCHIESSER_OPTION, schiesser_reglages_defaut() );
	}
} );

add_action( 'admin_notices', function () {
	if ( get_option( 'schiesser_demo_importee' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$ecran = get_current_screen();
	if ( ! $ecran || ! in_array( $ecran->id, array( 'dashboard', 'themes', 'toplevel_page_schiesser-reglages', 'edit-schiesser_produit' ), true ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo' ), 'schiesser_import_demo' );
	?>
	<div class="notice notice-info">
		<p><strong>Thème Schiesser :</strong> importer le contenu de démonstration (8 produits, 6 pages avec leurs blocs, menu principal). Les photos sont téléchargées depuis Unsplash, cela peut prendre une minute.</p>
		<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>">Importer le contenu de démonstration</a></p>
	</div>
	<?php
} );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['schiesser_demo'] ) && 'ok' === $_GET['schiesser_demo'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success is-dismissible"><p>Contenu de démonstration importé. <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank">Voir le site</a> · <a href="' . esc_url( admin_url( 'edit.php?post_type=schiesser_produit' ) ) . '">Voir les produits</a></p></div>';
	}
} );

add_action( 'admin_post_schiesser_import_demo', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_import_demo' );
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	schiesser_importer_demo();
	update_option( 'schiesser_demo_importee', 1 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=schiesser_produit&schiesser_demo=ok' ) );
	exit;
} );

/**
 * Télécharge une photo Unsplash dans la médiathèque.
 * Renvoie l'identifiant de l'image, ou 0 en cas d'échec (le site reste utilisable).
 */
function schiesser_demo_image( $photo, $titre ) {
	$existant = get_posts( array(
		'post_type'   => 'attachment',
		'meta_key'    => '_schiesser_source', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'  => $photo, // phpcs:ignore WordPress.DB.SlowDBQuery
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $existant ) {
		return (int) $existant[0];
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$url = 'https://images.unsplash.com/photo-' . $photo . '?q=80&w=1800&auto=format&fit=crop&fm=jpg';
	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}
	$fichier = array(
		'name'     => sanitize_file_name( sanitize_title( $titre ) . '.jpg' ),
		'tmp_name' => $tmp,
	);
	$id = media_handle_sideload( $fichier, 0, $titre );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_schiesser_source', $photo );
	update_post_meta( $id, '_wp_attachment_image_alt', $titre );
	return (int) $id;
}

/** Bloc sérialisé, prêt à être enregistré dans une page. */
function schiesser_demo_bloc( $nom, $attributs ) {
	return serialize_block( array(
		'blockName'    => $nom,
		'attrs'        => $attributs,
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	) );
}

function schiesser_demo_hero( $attributs, $photo ) {
	$id = schiesser_demo_image( $photo, wp_strip_all_tags( $attributs['titre'] ) );
	if ( $id ) {
		$attributs['imageId']  = $id;
		$attributs['imageUrl'] = wp_get_attachment_image_url( $id, 'full' );
	}
	return schiesser_demo_bloc( 'schiesser/hero', $attributs );
}

function schiesser_importer_demo() {
	/* Catégories */
	$cats = array();
	foreach ( array( 'Chocolats', 'Biscuits', 'Pâtisserie', 'Coffrets' ) as $nom ) {
		$t = term_exists( $nom, SCHIESSER_CATEGORIE );
		if ( ! $t ) {
			$t = wp_insert_term( $nom, SCHIESSER_CATEGORIE );
		}
		$cats[] = is_array( $t ) ? (int) $t['term_id'] : (int) $t;
	}

	/* Produits de la maquette */
	$produits = array(
		array( 'Läckerli de Basel', 1, 'CHF 6.50', 'les 100 g', 'Signature', 'vert', '1606313564200-e75d5e30476c',
			"Miel, amandes et épices, cuits puis glacés à la main. La spécialité de la ville, telle qu'on la fait ici depuis toujours.",
			array( array( 'Composition', 'Miel, amandes, noisettes, épices, kirsch' ), array( 'Format', 'Sachet de 100 g ou 250 g' ), array( 'Conservation', 'Plusieurs semaines au sec' ), array( 'Voyage', 'Excellent, se transporte sans risque' ) ),
			'Un thé noir corsé ou un café crème', 'Miel et amandes de la région, épices sélectionnées' ),
		array( 'Truffes maison', 0, 'CHF 2.20', 'la pièce', '', 'vert', '1548907040-4baa42d10919',
			"Ganache pure, roulée chaque matin dans le cacao amer. Elles ne se gardent pas longtemps, c'est bon signe.",
			array( array( 'Composition', 'Couverture, crème, cacao amer' ), array( 'Format', 'À la pièce ou en boîte' ), array( 'Conservation', 'Quelques jours au frais' ), array( 'Voyage', 'Sur de courtes distances' ) ),
			'Un espresso court, sans sucre', 'Couverture maison, crème fraîche du jour' ),
		array( 'Pralinés assortis', 0, 'CHF 2.60', 'la pièce', '', 'vert', '1481391319762-47dff72954d9',
			"Une collection de bouchées, trempées et décorées une à une. L'assortiment change au fil des saisons.",
			array( array( 'Composition', 'Selon la pièce, voir en boutique' ), array( 'Format', 'À la pièce ou en coffret' ), array( 'Conservation', 'Deux à trois semaines' ), array( 'Voyage', 'Bon, en emballage renforcé' ) ),
			'Un vin doux ou un thé fumé', 'Fruits secs et couvertures travaillés en atelier' ),
		array( 'Tablettes de la maison', 0, 'CHF 9.50', "l'unité", '', 'vert', '1464195244916-405fa0a82545',
			"Notre couverture coulée en tablettes, nature ou aux éclats d'amandes. Simple, et c'est bien là tout l'exercice.",
			array( array( 'Composition', "Couverture maison, éclats d'amandes" ), array( 'Format', 'Tablette de 100 g' ), array( 'Conservation', 'Plusieurs mois au sec' ), array( 'Voyage', 'Idéal' ) ),
			'À croquer seul, à température ambiante', 'Fèves sélectionnées, conchage en atelier' ),
		array( "Biscuits d'amande", 1, 'CHF 7.80', 'les 150 g', '', 'vert', '1556910103-1c02745aae4d',
			'Cuits au four le matin, croquants dehors et tendres au cœur. On les voit sortir depuis le comptoir.',
			array( array( 'Composition', "Amandes, sucre, blancs d'œufs" ), array( 'Format', 'Sachet de 150 g' ), array( 'Conservation', 'Deux semaines en boîte' ), array( 'Voyage', 'Très bon' ) ),
			'Un thé vert léger', "Amandes entières, sucre et blancs d'œufs, rien d'autre" ),
		array( 'Marrons glacés', 0, 'CHF 4.80', 'la pièce', 'En saison', 'menthe', '1519915028121-7d3463d20b13',
			"Confits lentement, un classique d'hiver présenté en coffret. Disponibles seulement quelques mois par an.",
			array( array( 'Composition', 'Châtaignes, sirop de sucre, vanille' ), array( 'Format', 'À la pièce ou en coffret' ), array( 'Conservation', 'Quelques semaines' ), array( 'Voyage', 'Bon, à protéger du choc' ) ),
			'Un chocolat chaud, en hiver', 'Châtaignes confites plusieurs jours en maison' ),
		array( 'Gâteau de la maison', 2, 'Sur commande', 'à partir de 6 personnes', '', 'vert', '1565958011703-44f9829ba187',
			"Pièce d'anniversaire ou de fête, montée et décorée sur demande. Nous en discutons ensemble, en boutique ou par téléphone.",
			array( array( 'Composition', 'Selon votre choix' ), array( 'Format', 'De 6 à 40 personnes' ), array( 'Délai', "Quelques jours d'avance" ), array( 'Retrait', 'En boutique uniquement' ) ),
			'Selon la pièce, nous vous conseillons', 'Ingrédients frais, montage le jour du retrait' ),
		array( 'Coffret découverte', 3, 'CHF 28.—', '16 pièces', 'Cadeau', 'vert', '1549007994-cb92caebd54b',
			'Une sélection composée à la main dans nos vitrines, emballée pour offrir. Le meilleur moyen de tout goûter.',
			array( array( 'Composition', 'Assortiment de la maison' ), array( 'Format', '9, 16 ou 25 pièces' ), array( 'Conservation', 'Deux à trois semaines' ), array( 'Personnalisation', 'Message ou logo possible' ) ),
			"Se partage, et c'est bien là l'idée", 'Assortiment composé à la main en boutique' ),
	);

	foreach ( $produits as $ordre => $p ) {
		if ( get_page_by_path( sanitize_title( $p[0] ), OBJECT, SCHIESSER_PRODUIT ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'   => SCHIESSER_PRODUIT,
			'post_status' => 'publish',
			'post_title'  => $p[0],
			'post_name'   => sanitize_title( $p[0] ),
			'menu_order'  => $ordre + 1,
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		wp_set_object_terms( $id, array( $cats[ $p[1] ] ), SCHIESSER_CATEGORIE );
		update_post_meta( $id, '_s_prix', $p[2] );
		update_post_meta( $id, '_s_unite', $p[3] );
		update_post_meta( $id, '_s_badge', $p[4] );
		update_post_meta( $id, '_s_badge_style', $p[5] );
		update_post_meta( $id, '_s_description', $p[7] );
		update_post_meta( $id, '_s_fiche', $p[8] );
		update_post_meta( $id, '_s_accord', $p[9] );
		update_post_meta( $id, '_s_origine', $p[10] );
		$img = schiesser_demo_image( $p[6], $p[0] );
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
	}

	/* Pages */
	$pages = array(
		array( 'Accueil', 'accueil', schiesser_demo_hero( array(
			'hauteur' => 'accueil', 'ariane' => false, 'sceau' => true,
			'surtitre' => 'Maison fondée à Basel · depuis 1870',
			'titre' => "Le goût précis<br>d'une <em>maison bâloise</em>",
			'texte' => 'Confiserie, café et gestes faits main au cœur du Marktplatz.',
			'bouton1Texte' => 'Découvrir les créations', 'bouton1Lien' => home_url( '/boutique/' ),
			'bouton2Texte' => 'Nous rendre visite', 'bouton2Lien' => home_url( '/nous-visiter/' ),
		), '1481391319762-47dff72954d9' ) ),
		array( 'La boutique', 'boutique', schiesser_demo_hero( array(
			'hauteur' => 'page',
			'surtitre' => 'Rez-de-chaussée · Marktplatz',
			'titre' => "Ce que l'on<br><em>emporte avec soi</em>",
			'texte' => "Chocolats, pralinés, Läckerli et coffrets, préparés dans l'atelier qui travaille juste derrière le comptoir.",
			'bouton1Texte' => 'Voir les produits', 'bouton1Lien' => '#catalogue',
		), '1509440159596-0249088772ff' ) . "\n\n" . schiesser_demo_bloc( 'schiesser/produits', array() ) ),
		array( 'Tea Room', 'tea-room', schiesser_demo_hero( array(
			'hauteur' => 'page',
			'surtitre' => 'Premier étage · depuis 1920',
			'titre' => 'Le salon<br><em>au-dessus de la place</em>',
			'texte' => "On traverse la boutique, on monte l'escalier, et le bruit du Marktplatz s'éloigne. Ici, le temps ralentit depuis un siècle.",
		), '1445116572660-236099ec97a0' ) ),
		array( 'Notre histoire', 'notre-histoire', schiesser_demo_hero( array(
			'hauteur' => 'page',
			'surtitre' => 'Depuis 1870 · Marktplatz',
			'titre' => 'Une maison,<br>une place,<br><em>un siècle et demi</em>',
			'texte' => "L'histoire d'une confiserie bâloise qui n'a jamais déménagé, et dont les gestes se transmettent depuis cinq générations.",
		), '1517244683847-7456b63c5969' ) ),
		array( 'Nous visiter', 'nous-visiter', schiesser_demo_hero( array(
			'hauteur' => 'compacte',
			'surtitre' => 'Marktplatz · 4001 Basel',
			'titre' => 'Au centre de tout,<br><em>sur la place</em>',
			'texte' => "À deux pas du tram et de l'hôtel de ville. Voici quand nous rendre visite, et comment nous rejoindre.",
		), '1509440159596-0249088772ff' ) ),
		array( 'Contact', 'contact', schiesser_demo_hero( array(
			'hauteur' => 'compacte',
			'surtitre' => 'Une question, une commande, un projet',
			'titre' => 'Écrivez-nous,<br><em>nous répondons</em>',
			'texte' => 'Un mot, un appel ou une visite au Marktplatz. Nous sommes toujours joignables.',
		), '1509440159596-0249088772ff' ) ),
	);

	$ids = array();
	foreach ( $pages as $ordre => $p ) {
		$existante = get_page_by_path( $p[1] );
		if ( $existante ) {
			$ids[ $p[1] ] = $existante->ID;
			continue;
		}
		$ids[ $p[1] ] = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p[0],
			'post_name'    => $p[1],
			'post_content' => wp_slash( $p[2] ), // wp_insert_post retire les barres obliques : on les protège
			'menu_order'   => $ordre,
		) );
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['accueil'] );

	/* Menu principal */
	$menu = wp_get_nav_menu_object( 'Menu principal' );
	$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Menu principal' );
	if ( ! is_wp_error( $menu_id ) && ! wp_get_nav_menu_items( $menu_id ) ) {
		foreach ( array( 'boutique', 'tea-room', 'notre-histoire', 'nous-visiter', 'contact' ) as $slug ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $ids[ $slug ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}
		$positions              = get_theme_mod( 'nav_menu_locations', array() );
		$positions['principal'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $positions );
	}

	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();
}

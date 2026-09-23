<?php
/**
 * Contenu de démonstration, importé en un clic depuis l'administration.
 *
 * Crée (ou met à jour) : les catégories, les 8 produits avec leur page,
 * les 6 pages du site composées de sections, le menu principal, et les
 * réglages SEO de chaque page (titre, description, mot-clé principal),
 * lus par Rank Math dès qu'il est installé.
 *
 * Les textes reprennent la maquette : ils sont à relire et à adapter
 * (dates, chiffres, prix) avant la mise en ligne.
 */

defined( 'ABSPATH' ) || exit;

/* Réglages par défaut dès l'activation du thème */
add_action( 'after_switch_theme', function () {
	if ( false === get_option( SCHIESSER_OPTION ) ) {
		add_option( SCHIESSER_OPTION, schiesser_reglages_defaut() );
	}
} );

/* ------------------------------------------------------------------ */
/* Avis et actions dans l'administration                               */
/* ------------------------------------------------------------------ */

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$ecran = get_current_screen();
	if ( ! $ecran || ! in_array( $ecran->id, array( 'dashboard', 'themes', 'toplevel_page_schiesser-reglages', 'edit-schiesser_produit', 'edit-page' ), true ) ) {
		return;
	}
	$importee = get_option( 'schiesser_demo_importee' );
	$version  = (string) get_option( 'schiesser_demo_version', $importee ? '0.1.0' : '' );
	if ( $importee && version_compare( $version, '0.2.0', '>=' ) ) {
		return;
	}
	if ( ! $importee ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo' ), 'schiesser_import_demo' );
		?>
		<div class="notice notice-info">
			<p><strong>Thème Schiesser :</strong> importer le contenu de démonstration (8 produits avec leur page, 6 pages composées de sections, menu principal, réglages SEO). Les photos sont téléchargées depuis Unsplash, cela peut prendre une minute.</p>
			<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>">Importer le contenu de démonstration</a></p>
		</div>
		<?php
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo&remplacer=1' ), 'schiesser_import_demo' );
	?>
	<div class="notice notice-info">
		<p><strong>Thème Schiesser 0.2 :</strong> un nouveau contenu de démonstration est disponible (sections modifiables, questions fréquentes, pages produits, titres et descriptions SEO). La mise à jour <strong>remplace le contenu</strong> des 6 pages et des 8 produits de démonstration.</p>
		<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>" onclick="return confirm('Remplacer le contenu des pages et produits de démonstration ?');">Mettre à jour le contenu de démonstration</a></p>
	</div>
	<?php
} );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['schiesser_demo'] ) && 'ok' === $_GET['schiesser_demo'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success is-dismissible"><p>Contenu de démonstration importé. <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank">Voir le site</a> · <a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">Voir les pages</a> · <a href="' . esc_url( admin_url( 'edit.php?post_type=schiesser_produit' ) ) . '">Voir les produits</a></p></div>';
	}
} );

add_action( 'admin_post_schiesser_import_demo', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_import_demo' );
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	schiesser_importer_demo( ! empty( $_GET['remplacer'] ) );
	wp_safe_redirect( admin_url( 'edit.php?post_type=page&schiesser_demo=ok' ) );
	exit;
} );

/* ------------------------------------------------------------------ */
/* Outils                                                              */
/* ------------------------------------------------------------------ */

/**
 * Télécharge une photo Unsplash dans la médiathèque (une seule fois).
 * Renvoie l'identifiant de l'image, ou 0 en cas d'échec (le site reste utilisable).
 *
 * @param string $photo Identifiant Unsplash.
 * @param string $nom   Nom du fichier (sans extension), ex. « lackerli-de-bale ».
 * @param string $alt   Texte alternatif : décrit l'image pour Google et les lecteurs d'écran.
 */
function schiesser_demo_image( $photo, $nom, $alt ) {
	$existant = get_posts( array(
		'post_type'   => 'attachment',
		'meta_key'    => '_schiesser_source', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'  => $photo, // phpcs:ignore WordPress.DB.SlowDBQuery
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $existant ) {
		$id = (int) $existant[0];
		if ( '' === (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		}
		return $id;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( 'https://images.unsplash.com/photo-' . $photo . '?q=80&w=2000&auto=format&fit=crop&fm=jpg', 60 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => sanitize_file_name( $nom . '.jpg' ), 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_schiesser_source', $photo );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/** Bloc Hero avec sa photo. */
function schiesser_demo_hero( $attrs, $photo, $nom, $alt ) {
	$id = schiesser_demo_image( $photo, $nom, $alt );
	if ( $id ) {
		$attrs['imageId']  = $id;
		$attrs['imageUrl'] = (string) wp_get_attachment_image_url( $id, 'full' );
		$attrs['imageAlt'] = $alt;
	}
	return schiesser_bm( 'schiesser/hero', $attrs );
}

/** Image « cadre vitrine » placée dans une section. */
function schiesser_demo_photo( $photo, $nom, $alt, $legende = '' ) {
	$id = schiesser_demo_image( $photo, $nom, $alt );
	return schiesser_bm_image( $id, $id ? (string) wp_get_attachment_image_url( $id, 'large' ) : '', $alt, 'vitrine', $legende );
}

/** Enregistre titre SEO, description et mots-clés (champs de Rank Math). */
function schiesser_demo_seo( $post_id, $seo ) {
	update_post_meta( $post_id, 'rank_math_title', $seo['titre'] );
	update_post_meta( $post_id, 'rank_math_description', $seo['description'] );
	update_post_meta( $post_id, 'rank_math_focus_keyword', implode( ',', $seo['mots_cles'] ) );
}

/* ------------------------------------------------------------------ */
/* Produits                                                            */
/* ------------------------------------------------------------------ */

/**
 * Les 8 produits de la maquette.
 * Clés : nom, anciens (adresses de la version 0.1), categorie, prix, unite, badge, style, photo, alt,
 * description, fiche, accord, origine, texte (page du produit), seo.
 */
function schiesser_demo_produits() {
	return array(
		array(
			'nom' => 'Läckerli de Bâle', 'anciens' => array( 'lackerli-de-basel', 'laeckerli-de-basel' ), 'categorie' => 'Biscuits',
			'prix' => 'CHF 6.50', 'unite' => 'les 100 g', 'badge' => 'Signature', 'style' => 'vert',
			'photo' => '1606313564200-e75d5e30476c', 'alt' => 'Läckerli de Bâle glacés, découpés en rectangles',
			'description' => "Miel, amandes et épices, cuits puis glacés à la main. La spécialité de la ville, telle qu'on la fait ici depuis toujours.",
			'fiche' => array( array( 'Composition', 'Miel, amandes, noisettes, épices, kirsch' ), array( 'Format', 'Sachet de 100 g ou 250 g' ), array( 'Conservation', 'Plusieurs semaines au sec' ), array( 'Voyage', 'Excellent, se transporte sans risque' ) ),
			'accord' => 'Un thé noir corsé ou un café crème', 'origine' => 'Miel et amandes de la région, épices sélectionnées',
			'texte' => array(
				"Le Läckerli de Bâle est le biscuit de la ville. À la confiserie, la pâte au miel, aux amandes et aux épices est cuite sur plaque, puis glacée au sucre et découpée en rectangles réguliers, à la main.",
				"Sa texture ferme s'attendrit en quelques jours : il se garde longtemps et voyage très bien. C'est le souvenir de Bâle que l'on emporte le plus volontiers, en sachet ou en boîte à offrir.",
			),
			'seo' => array( 'titre' => 'Läckerli de Bâle | Biscuit au miel fait main – Schiesser', 'description' => 'Läckerli de Bâle faits main au Marktplatz : miel, amandes et épices, cuits puis glacés à la main. Sachets de 100 g ou 250 g, parfaits à offrir.', 'mots_cles' => array( 'Läckerli de Bâle', 'Basler Läckerli', 'spécialité de Bâle' ) ),
		),
		array(
			'nom' => 'Truffes maison', 'anciens' => array(), 'categorie' => 'Chocolats',
			'prix' => 'CHF 2.20', 'unite' => 'la pièce', 'badge' => '', 'style' => 'vert',
			'photo' => '1548907040-4baa42d10919', 'alt' => 'Truffes au chocolat roulées dans le cacao amer',
			'description' => "Ganache pure, roulée chaque matin dans le cacao amer. Elles ne se gardent pas longtemps, c'est bon signe.",
			'fiche' => array( array( 'Composition', 'Couverture, crème, cacao amer' ), array( 'Format', 'À la pièce ou en boîte' ), array( 'Conservation', 'Quelques jours au frais' ), array( 'Voyage', 'Sur de courtes distances' ) ),
			'accord' => 'Un espresso court, sans sucre', 'origine' => 'Couverture maison, crème fraîche du jour',
			'texte' => array(
				"Nos truffes au chocolat sont préparées chaque matin : une ganache à la crème fraîche, façonnée à la main puis roulée dans le cacao amer.",
				"Sans conservateur, elles se dégustent dans les jours qui suivent. Choisissez-les à la pièce au comptoir, ou faites composer une boîte.",
			),
			'seo' => array( 'titre' => 'Truffes au chocolat | Roulées chaque matin – Schiesser', 'description' => 'Truffes au chocolat faites chaque matin à Bâle : ganache à la crème fraîche roulée dans le cacao amer. À la pièce ou en boîte, au Marktplatz.', 'mots_cles' => array( 'truffes au chocolat', 'truffes Bâle' ) ),
		),
		array(
			'nom' => 'Pralinés assortis', 'anciens' => array(), 'categorie' => 'Chocolats',
			'prix' => 'CHF 2.60', 'unite' => 'la pièce', 'badge' => '', 'style' => 'vert',
			'photo' => '1481391319762-47dff72954d9', 'alt' => 'Assortiment de pralinés au chocolat décorés à la main',
			'description' => "Une collection de bouchées, trempées et décorées une à une. L'assortiment change au fil des saisons.",
			'fiche' => array( array( 'Composition', 'Selon la pièce, voir en boutique' ), array( 'Format', 'À la pièce ou en coffret' ), array( 'Conservation', 'Deux à trois semaines' ), array( 'Voyage', 'Bon, en emballage renforcé' ) ),
			'accord' => 'Un vin doux ou un thé fumé', 'origine' => 'Fruits secs et couvertures travaillés en atelier',
			'texte' => array(
				"Pralinés artisanaux, trempés et décorés un à un dans notre atelier : fruits secs, ganaches parfumées, pâtes d'amande. L'assortiment suit les saisons.",
				"Au comptoir, on les choisit à la pièce et l'équipe compose la boîte avec vous. Pour offrir, pensez au coffret découverte.",
			),
			'seo' => array( 'titre' => 'Pralinés artisanaux | Assortiment de saison – Schiesser', 'description' => "Pralinés artisanaux trempés et décorés à la main à Bâle : un assortiment qui change au fil des saisons, à la pièce ou en coffret à offrir.", 'mots_cles' => array( 'pralinés artisanaux', 'pralinés Bâle' ) ),
		),
		array(
			'nom' => 'Tablettes de la maison', 'anciens' => array(), 'categorie' => 'Chocolats',
			'prix' => 'CHF 9.50', 'unite' => "l'unité", 'badge' => '', 'style' => 'vert',
			'photo' => '1464195244916-405fa0a82545', 'alt' => "Tablette de chocolat artisanale aux éclats d'amandes",
			'description' => "Notre couverture coulée en tablettes, nature ou aux éclats d'amandes. Simple, et c'est bien là tout l'exercice.",
			'fiche' => array( array( 'Composition', "Couverture maison, éclats d'amandes" ), array( 'Format', 'Tablette de 100 g' ), array( 'Conservation', 'Plusieurs mois au sec' ), array( 'Voyage', 'Idéal' ) ),
			'accord' => 'À croquer seul, à température ambiante', 'origine' => 'Fèves sélectionnées, conchage en atelier',
			'texte' => array(
				"Une tablette de chocolat artisanale, coulée dans notre atelier à partir de la couverture de la maison, nature ou parsemée d'éclats d'amandes.",
				"Elle se conserve plusieurs mois et voyage sans crainte : l'idée cadeau la plus simple à rapporter de Bâle.",
			),
			'seo' => array( 'titre' => 'Tablette de chocolat artisanale | Faite main – Schiesser', 'description' => "Tablette de chocolat artisanale coulée à Bâle, nature ou aux éclats d'amandes. Se conserve plusieurs mois : le cadeau idéal à rapporter.", 'mots_cles' => array( 'tablette de chocolat artisanale', 'chocolat Bâle' ) ),
		),
		array(
			'nom' => "Biscuits d'amande", 'anciens' => array(), 'categorie' => 'Biscuits',
			'prix' => 'CHF 7.80', 'unite' => 'les 150 g', 'badge' => '', 'style' => 'vert',
			'photo' => '1556910103-1c02745aae4d', 'alt' => "Biscuits aux amandes dorés à la sortie du four",
			'description' => 'Cuits au four le matin, croquants dehors et tendres au cœur. On les voit sortir depuis le comptoir.',
			'fiche' => array( array( 'Composition', "Amandes, sucre, blancs d'œufs" ), array( 'Format', 'Sachet de 150 g' ), array( 'Conservation', 'Deux semaines en boîte' ), array( 'Voyage', 'Très bon' ) ),
			'accord' => 'Un thé vert léger', 'origine' => "Amandes entières, sucre et blancs d'œufs, rien d'autre",
			'texte' => array(
				"Trois ingrédients seulement pour ces biscuits aux amandes : des amandes, du sucre et des blancs d'œufs. Ils cuisent le matin, à la vue des clients.",
				"Croquants à l'extérieur, moelleux au cœur, ils accompagnent parfaitement un thé au salon de l'étage.",
			),
			'seo' => array( 'titre' => "Biscuits aux amandes | Cuits chaque matin – Schiesser", 'description' => "Biscuits aux amandes cuits chaque matin à Bâle : amandes, sucre et blancs d'œufs, rien d'autre. Croquants dehors, tendres au cœur, en sachet de 150 g.", 'mots_cles' => array( 'biscuits aux amandes', "biscuits d'amande" ) ),
		),
		array(
			'nom' => 'Marrons glacés', 'anciens' => array(), 'categorie' => 'Chocolats',
			'prix' => 'CHF 4.80', 'unite' => 'la pièce', 'badge' => 'En saison', 'style' => 'menthe',
			'photo' => '1519915028121-7d3463d20b13', 'alt' => 'Marrons glacés présentés en coffret',
			'description' => "Confits lentement, un classique d'hiver présenté en coffret. Disponibles seulement quelques mois par an.",
			'fiche' => array( array( 'Composition', 'Châtaignes, sirop de sucre, vanille' ), array( 'Format', 'À la pièce ou en coffret' ), array( 'Conservation', 'Quelques semaines' ), array( 'Voyage', 'Bon, à protéger du choc' ) ),
			'accord' => 'Un chocolat chaud, en hiver', 'origine' => 'Châtaignes confites plusieurs jours en maison',
			'texte' => array(
				"Nos marrons glacés sont confits pendant plusieurs jours dans un sirop vanillé, puis glacés un à un. Un travail lent, réservé à la saison froide.",
				"Ils arrivent en vitrine à l'automne et disparaissent avec l'hiver : pensez à les réserver pour les fêtes.",
			),
			'seo' => array( 'titre' => 'Marrons glacés | Confits lentement, en saison – Schiesser', 'description' => "Marrons glacés confits plusieurs jours dans un sirop vanillé, glacés un à un. Disponibles en saison à la Confiserie Schiesser, à la pièce ou en coffret.", 'mots_cles' => array( 'marrons glacés', 'marrons glacés Bâle' ) ),
		),
		array(
			'nom' => 'Gâteau de la maison', 'anciens' => array(), 'categorie' => 'Pâtisserie',
			'prix' => 'Sur commande', 'unite' => 'à partir de 6 personnes', 'badge' => '', 'style' => 'vert',
			'photo' => '1565958011703-44f9829ba187', 'alt' => "Gâteau d'anniversaire décoré sur commande",
			'description' => "Pièce d'anniversaire ou de fête, montée et décorée sur demande. Nous en discutons ensemble, en boutique ou par téléphone.",
			'fiche' => array( array( 'Composition', 'Selon votre choix' ), array( 'Format', 'De 6 à 40 personnes' ), array( 'Délai', "Quelques jours d'avance" ), array( 'Retrait', 'En boutique uniquement' ) ),
			'accord' => 'Selon la pièce, nous vous conseillons', 'origine' => 'Ingrédients frais, montage le jour du retrait',
			'texte' => array(
				"Anniversaire, mariage, fête de famille : nous préparons votre gâteau sur commande à Bâle, de 6 à 40 personnes. Parfums, décor et message se choisissent ensemble.",
				"Prévoyez quelques jours d'avance, davantage en période de fêtes. Le gâteau est monté le jour même et se retire en boutique, au Marktplatz.",
			),
			'seo' => array( 'titre' => 'Gâteau sur commande à Bâle | Fêtes et anniversaires', 'description' => "Gâteau sur commande à Bâle pour anniversaires, mariages et fêtes, de 6 à 40 personnes. Parfums et décor à choisir ensemble, retrait au Marktplatz.", 'mots_cles' => array( 'gâteau sur commande à Bâle', "gâteau d'anniversaire Bâle" ) ),
		),
		array(
			'nom' => 'Coffret découverte', 'anciens' => array(), 'categorie' => 'Coffrets',
			'prix' => 'CHF 28.—', 'unite' => '16 pièces', 'badge' => 'Cadeau', 'style' => 'vert',
			'photo' => '1549007994-cb92caebd54b', 'alt' => 'Coffret de chocolats assortis emballé pour offrir',
			'description' => 'Une sélection composée à la main dans nos vitrines, emballée pour offrir. Le meilleur moyen de tout goûter.',
			'fiche' => array( array( 'Composition', 'Assortiment de la maison' ), array( 'Format', '9, 16 ou 25 pièces' ), array( 'Conservation', 'Deux à trois semaines' ), array( 'Personnalisation', 'Message ou logo possible' ) ),
			'accord' => "Se partage, et c'est bien là l'idée", 'origine' => 'Assortiment composé à la main en boutique',
			'texte' => array(
				"Le coffret de chocolats pour tout goûter : pralinés, truffes et spécialités de saison, choisis dans nos vitrines et rangés à la main.",
				"Trois formats, de 9 à 25 pièces. Pour les cadeaux d'entreprise, nous ajoutons volontiers votre message ou votre logo.",
			),
			'seo' => array( 'titre' => 'Coffret de chocolats | Assortiment à offrir – Schiesser', 'description' => "Coffret de chocolats composé à la main à Bâle : pralinés, truffes et spécialités de saison, de 9 à 25 pièces. Message ou logo possible pour offrir.", 'mots_cles' => array( 'coffret de chocolats', 'cadeau chocolat Bâle' ) ),
		),
	);
}

/* ------------------------------------------------------------------ */
/* Pages                                                               */
/* ------------------------------------------------------------------ */

/**
 * Les pages : titre, adresse, anciennes adresses, contenu (blocs) et réglages SEO.
 */
function schiesser_demo_pages() {
	$u = function ( $chemin ) {
		return home_url( $chemin );
	};

	$appel = function ( $titre, $texte, $b1, $b2 = null ) {
		$boutons = array( array( $b1[0], $b1[1], 'creme' ) );
		if ( $b2 ) {
			$boutons[] = array( $b2[0], $b2[1], 'contour' );
		}
		return schiesser_bm_section(
			array( 'entete' => false, 'fond' => 'sombre', 'centre' => true, 'largeur' => 'lecture' ),
			schiesser_bm_p( 'Au plaisir de vous recevoir', 'surtitre', true )
			. schiesser_bm_titre( $titre, 2 )
			. schiesser_bm_p( $texte, '', true )
			. schiesser_bm_boutons( $boutons, true )
		);
	};

	$horaires_adresse = function ( $numero, $titre ) {
		return schiesser_bm_section(
			array( 'numero' => $numero, 'titre' => $titre, 'note' => 'Sur le Marktplatz, côté hôtel de ville, à deux pas du tram.', 'fond' => 'sable', 'ancre' => 'horaires' ),
			schiesser_bm_colonnes( array(
				schiesser_bm_titre( "Horaires d'ouverture", 3 ) . schiesser_bm_code_court( '[schiesser_horaires]' ),
				schiesser_bm_titre( 'Adresse', 3 )
				. schiesser_bm_p( '<strong>Confiserie Schiesser</strong><br>[schiesser_adresse]' )
				. schiesser_bm_p( 'Téléphone : [schiesser_telephone]<br>E-mail : [schiesser_email]' )
				. schiesser_bm_code_court( '[schiesser_itineraire texte="Itinéraire sur Google Maps"]' ),
			) )
		);
	};

	/* ============ Accueil ============ */
	$accueil = schiesser_demo_hero( array(
		'hauteur'      => 'accueil',
		'ariane'       => false,
		'sceau'        => true,
		'surtitre'     => 'Maison fondée à Bâle · depuis 1870',
		'titre'        => "Le goût précis<br>d'une <em>confiserie à&nbsp;Bâle</em>",
		'texte'        => 'Läckerli, truffes et pralinés faits main au cœur du Marktplatz, et un salon de thé à l’étage.',
		'bouton1Texte' => 'Découvrir les créations',
		'bouton1Lien'  => $u( '/boutique/' ),
		'bouton2Texte' => 'Nous rendre visite',
		'bouton2Lien'  => $u( '/nous-visiter/' ),
	), '1481391319762-47dff72954d9', 'confiserie-a-bale-pralines', 'Pralinés au chocolat en vitrine à la Confiserie Schiesser de Bâle' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'Une confiserie à&nbsp;Bâle, <em>depuis 1870</em>', 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Confiserie à Bâle depuis 1870, la maison Schiesser n’a jamais quitté le Marktplatz. On y prépare chaque jour, à la main, les spécialités qui font sa réputation.', 'chapeau' )
		. schiesser_bm_p( 'Au rez-de-chaussée, la boutique et son atelier visible : <a href="' . esc_url( $u( '/boutique/' ) ) . '">Läckerli de Bâle, truffes, pralinés et coffrets</a>. À l’étage, <a href="' . esc_url( $u( '/salon-de-the/' ) ) . '">le salon de thé</a> et ses fenêtres sur la place. Une adresse, deux façons de goûter la maison.' )
	)
	. schiesser_bm( 'schiesser/produits', array(
		'numero'    => '02',
		'titre'     => 'Les <em>créations</em> de la maison',
		'note'      => 'Le catalogue de la maison. Chaque pièce porte un numéro, comme au comptoir.',
		'ancre'     => 'creations',
		'fond'      => 'clair',
		'limite'    => 4,
		'filtres'   => false,
		'lienTexte' => 'Voir toute la boutique',
		'lienUrl'   => $u( '/boutique/' ),
	) )
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Deux maisons, <em>une adresse</em>', 'note' => 'La boutique au rez-de-chaussée, le salon à l’étage. On traverse l’une pour rejoindre l’autre.' ),
		schiesser_bm_colonnes( array(
			schiesser_demo_photo( '1509440159596-0249088772ff', 'boutique-confiserie-marktplatz-bale', 'Vitrine de la boutique de la Confiserie Schiesser au Marktplatz' )
			. schiesser_bm_p( 'Rez-de-chaussée', 'surtitre' )
			. schiesser_bm_titre( 'La boutique', 3 )
			. schiesser_bm_p( 'Chocolats, pralinés, Läckerli et coffrets, préparés dans l’atelier qui travaille juste derrière le comptoir.' )
			. schiesser_bm_boutons( array( array( 'Voir la boutique', $u( '/boutique/' ), 'lien' ) ) ),
			schiesser_demo_photo( '1445116572660-236099ec97a0', 'salon-de-the-bale', 'Salon de thé de la Confiserie Schiesser, au premier étage' )
			. schiesser_bm_p( 'Premier étage', 'surtitre' )
			. schiesser_bm_titre( 'Le salon de thé', 3 )
			. schiesser_bm_p( 'On monte l’escalier, le bruit de la place s’éloigne. Chocolat chaud maison et pâtisserie du jour, face au Marktplatz.' )
			. schiesser_bm_boutons( array( array( 'Découvrir le salon', $u( '/salon-de-the/' ), 'lien' ) ) ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Le geste, <em>visible</em>', 'note' => 'Rien n’est caché : l’atelier travaille à la vue de tous.', 'fond' => 'alterne' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'La cuisson', 3 ) . schiesser_bm_p( 'Le miel et les amandes travaillés au cuivre, comme au premier jour de la maison.' ),
			schiesser_bm_titre( 'Le façonnage', 3 ) . schiesser_bm_p( 'Chaque pièce est coupée, roulée ou trempée à la main, sans raccourci.' ),
			schiesser_bm_titre( 'La finition', 3 ) . schiesser_bm_p( 'Glaçage, décor et contrôle. C’est la finition qui distingue une maison.' ),
			schiesser_bm_titre( 'La vitrine', 3 ) . schiesser_bm_p( 'Le matin même, tout rejoint la vitrine et le salon du Marktplatz.' ),
		), 'filets' )
	)
	. $horaires_adresse( '05', 'Nous <em>trouver</em>' )
	. schiesser_bm_section(
		array( 'numero' => '06', 'titre' => 'Questions <em>fréquentes</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Où acheter des Läckerli à Bâle ?', 'Dans notre boutique du Marktplatz, où les Läckerli de Bâle sont préparés et glacés à la main. Ils se vendent en sachets de 100 g ou 250 g et voyagent très bien.' )
		. schiesser_bm_question( 'Faut-il réserver pour le salon de thé ?', 'Non, l’accès est libre. Nous conseillons simplement de réserver à partir de six personnes.' )
		. schiesser_bm_question( 'Peut-on commander un gâteau sur mesure ?', 'Oui, avec quelques jours d’avance. Écrivez-nous ou passez en boutique pour en discuter avec l’équipe.' )
	)
	. $appel( 'Passez nous voir au <em>Marktplatz</em>', 'La vitrine est remontée chaque matin, et le salon vous attend à l’étage.', array( 'Horaires et accès', $u( '/nous-visiter/' ) ), array( 'Nous écrire', $u( '/contact/' ) ) );

	/* ============ La boutique ============ */
	$boutique = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'surtitre'     => 'Rez-de-chaussée · Marktplatz',
		'titre'        => "Chocolats à&nbsp;Bâle,<br><em>faits main sur place</em>",
		'texte'        => 'Chocolats, pralinés, Läckerli et coffrets, préparés dans l’atelier qui travaille juste derrière le comptoir.',
		'bouton1Texte' => 'Voir les produits',
		'bouton1Lien'  => '#catalogue',
		'bouton2Texte' => 'Passer commande',
		'bouton2Lien'  => '#commander',
	), '1509440159596-0249088772ff', 'boutique-confiserie-marktplatz-bale', 'Vitrine de la boutique de la Confiserie Schiesser au Marktplatz' )
	. schiesser_bm( 'schiesser/produits', array( 'numero' => '01', 'titre' => 'Les produits de la <em>boutique</em>', 'note' => 'Cliquez un produit pour ouvrir sa fiche détaillée.' ) )
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Bon à <em>savoir</em>', 'note' => 'Conservation, transport et commandes particulières.', 'fond' => 'clair' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Conservation', 3 ) . schiesser_bm_p( 'À l’abri de la chaleur et de la lumière. Nos chocolats se gardent quelques semaines, les pâtisseries se dégustent le jour même.' ),
			schiesser_bm_titre( 'Voyager avec', 3 ) . schiesser_bm_p( 'Demandez un emballage renforcé si vous prenez l’avion ou le train. Les Läckerli et les coffrets supportent très bien le trajet.' ),
			schiesser_bm_titre( 'Gâteaux sur commande', 3 ) . schiesser_bm_p( 'Anniversaires, mariages, fêtes de famille. Prévoyez quelques jours d’avance, davantage en période chargée.' ),
		), 'filets' )
		. schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Cadeaux d’entreprise', 3 ) . schiesser_bm_p( 'Coffrets personnalisés pour vos clients ou vos équipes, avec la possibilité d’ajouter votre message.' ),
			schiesser_bm_titre( 'Paiement', 3 ) . schiesser_bm_p( 'Cartes, sans contact et espèces en francs suisses. Les euros sont acceptés en boutique.' ),
			schiesser_bm_titre( 'Sortie du four', 3 ) . schiesser_bm_p( 'Tout est remonté en vitrine le matin même. Venir tôt reste le meilleur moyen d’avoir le plus grand choix.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Passer <em>commande</em>', 'note' => 'Trois manières de repartir avec vos produits.', 'fond' => 'alterne', 'ancre' => 'commander' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'En boutique', 3 ) . schiesser_bm_p( 'Poussez la porte au Marktplatz. Tout ce qui est en vitrine part immédiatement, sans commande préalable.' ) . schiesser_bm_boutons( array( array( 'Horaires et accès', $u( '/nous-visiter/' ), 'lien' ) ) ),
			schiesser_bm_titre( 'Par téléphone', 3 ) . schiesser_bm_p( 'Pour un gâteau, un grand coffret ou une commande d’entreprise, appelez-nous pendant les heures d’ouverture : [schiesser_telephone]' ),
			schiesser_bm_titre( 'Par e-mail', 3 ) . schiesser_bm_p( 'Envoyez-nous votre composition ou votre demande détaillée, nous revenons vers vous sous un à deux jours ouvrés : [schiesser_email]' ),
		), 'cartes' )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Questions <em>fréquentes</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Les produits voyagent-ils bien ?', 'Nos coffrets sont conçus pour être emportés. Demandez un emballage renforcé si vous prenez l’avion ou le train.' )
		. schiesser_bm_question( 'Livrez-vous ou expédiez-vous les commandes ?', 'Les commandes se retirent en boutique. Écrivez-nous pour toute demande particulière, nous verrons ensemble ce qui est possible.' )
		. schiesser_bm_question( 'Proposez-vous des cadeaux d’entreprise ?', 'Oui, nous composons des coffrets personnalisés pour vos clients ou vos équipes, avec votre message. Précisez les quantités et la date souhaitée.' )
		. schiesser_bm_question( 'Vos produits conviennent-ils aux allergies ?', 'Nos ateliers travaillent les fruits à coque, le lait, le gluten et l’œuf : nous ne pouvons donc exclure les traces. Indiquez-nous vos contraintes, nous vous orienterons.' )
	);

	/* ============ Salon de thé ============ */
	$carte = function ( $titre, $plats ) {
		$lignes = array();
		foreach ( $plats as $p ) {
			$lignes[] = '<strong>' . $p[0] . '</strong> · ' . $p[1] . '<br>' . $p[2];
		}
		return schiesser_bm_titre( $titre, 3 ) . schiesser_bm_liste( $lignes, 'filets' );
	};
	$salon = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'surtitre'     => 'Premier étage · Marktplatz',
		'titre'        => "Un salon de thé à&nbsp;Bâle,<br><em>au-dessus de la place</em>",
		'texte'        => 'On traverse la boutique, on monte l’escalier, et le bruit du Marktplatz s’éloigne. Ici, le temps ralentit.',
		'bouton1Texte' => 'Voir la carte',
		'bouton1Lien'  => '#carte',
	), '1445116572660-236099ec97a0', 'salon-de-the-bale', 'Salon de thé de la Confiserie Schiesser, au premier étage' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'Monter à <em>l’étage</em>', 'note' => 'Quelques marches séparent la boutique du salon. Elles font toute la différence.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_p( 'Notre salon de thé à Bâle est un lieu que l’on ne découvre qu’en poussant la porte, puis en levant les yeux.', 'chapeau' )
			. schiesser_bm_p( 'On y accède en traversant la boutique, entre les vitrines et l’odeur de l’atelier tout proche. En haut, tout change : les fenêtres donnent sur le Marktplatz, les tables sont les mêmes depuis des générations, et l’on vient y passer une heure plutôt que quelques minutes.' )
			. schiesser_bm_boutons( array( array( 'Horaires du salon', $u( '/nous-visiter/#horaires' ), 'lien' ) ) ),
			schiesser_demo_photo( '1600891964092-4316c288032e', 'salon-de-the-marktplatz', 'Tables du salon de thé près des fenêtres donnant sur le Marktplatz' ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'La carte du <em>salon</em>', 'note' => 'Carte susceptible d’évoluer selon la saison et les arrivages.', 'fond' => 'clair', 'ancre' => 'carte' ),
		schiesser_bm_colonnes( array(
			$carte( 'Cafés et chocolats', array(
				array( 'Chocolat chaud maison', 'CHF 7.50', 'Préparé à partir de notre couverture, à l’ancienne.' ),
				array( 'Café crème', 'CHF 5.20', 'Torréfaction sélectionnée pour la maison, servi en porcelaine.' ),
				array( 'Cappuccino', 'CHF 6.—', 'Mousse dense, servi avec un praliné de la boutique.' ),
				array( 'Espresso', 'CHF 4.50', 'Court et franc, comme il se doit.' ),
			) ) . $carte( 'Thés et infusions', array(
				array( 'Thé noir de saison', 'CHF 6.50', 'Sélection changeante, servie en théière.' ),
				array( 'Thé vert', 'CHF 6.50', 'Infusion douce, à l’eau frémissante.' ),
				array( 'Infusion maison', 'CHF 6.—', 'Mélange de plantes composé pour le salon.' ),
			) ),
			$carte( 'Pâtisseries', array(
				array( 'Part du jour', 'CHF 7.80', 'La pâtisserie sortie du four le matin même.' ),
				array( 'Gâteau au chocolat', 'CHF 8.20', 'Dense et peu sucré, servi à température.' ),
				array( 'Tarte de saison', 'CHF 7.50', 'Selon les fruits du marché, juste en face.' ),
				array( 'Assortiment de pralinés', 'CHF 9.—', 'Trois pièces choisies dans la vitrine du bas.' ),
			) ) . $carte( 'Salé et glaces', array(
				array( 'Petite salade', 'CHF 12.50', 'Feuilles de saison, vinaigrette maison.' ),
				array( 'Croque du salon', 'CHF 14.—', 'Pain de campagne, servi chaud.' ),
				array( 'Coupe glacée', 'CHF 10.50', 'Glaces maison, chantilly montée à la commande.' ),
			) ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Le salon en <em>détail</em>' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Les fenêtres', 3 ) . schiesser_bm_p( 'Elles donnent directement sur le Marktplatz. Les meilleures tables sont celles qui les longent.' ),
			schiesser_bm_titre( 'Les boiseries', 3 ) . schiesser_bm_p( 'D’origine, entretenues plutôt que remplacées. Elles portent les marques de chaque génération.' ),
			schiesser_bm_titre( 'Le service', 3 ) . schiesser_bm_p( 'Porcelaine, plateaux d’argent et gestes appris sur le tas. Rien n’a été modernisé pour le plaisir de moderniser.' ),
			schiesser_bm_titre( 'Les pâtisseries', 3 ) . schiesser_bm_p( 'Elles montent de l’atelier au fil de la journée. Ce qui est en vitrine en bas se retrouve dans les assiettes en haut.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Questions <em>fréquentes</em>', 'largeur' => 'lecture', 'fond' => 'alterne' ),
		schiesser_bm_question( 'Faut-il réserver pour le salon de thé ?', 'Non, l’accès se fait librement. Nous conseillons simplement la réservation à partir de six personnes.' )
		. schiesser_bm_question( 'Peut-on privatiser le salon ?', 'C’est possible selon les périodes et les horaires. Contactez-nous avec votre date, le nombre de personnes et le type d’accueil souhaité.' )
	)
	. $appel( 'Une table vous <em>attend</em>', 'Poussez la porte de la boutique, traversez jusqu’à l’escalier et montez. Il y a presque toujours une place près d’une fenêtre.', array( 'Horaires et accès', $u( '/nous-visiter/' ) ), array( 'Réserver pour un groupe', $u( '/contact/' ) ) );

	/* ============ Notre histoire ============ */
	$histoire = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'surtitre'     => 'Depuis 1870 · Marktplatz',
		'titre'        => "Une confiserie historique<br><em>à Bâle depuis 1870</em>",
		'texte'        => 'L’histoire d’une confiserie bâloise qui n’a jamais déménagé, et dont les gestes se transmettent depuis cinq générations.',
	), '1517244683847-7456b63c5969', 'confiserie-historique-bale', 'Façade historique de la Confiserie Schiesser sur le Marktplatz de Bâle' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'L’<em>origine</em>', 'note' => 'Une seule histoire, racontée simplement. Le reste se goûte sur place.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_p( 'Tout commence en 1870 par une décision simple : ouvrir une confiserie historique à Bâle, sur la plus belle place de la ville, et n’en plus jamais bouger.', 'chapeau' )
			. schiesser_bm_p( 'Le four est installé à l’arrière, la vitrine donne sur le Marktplatz. Très vite, la maison se fait un nom sur quelques pièces seulement, travaillées à la main du matin au soir : du miel, des amandes, des épices, et le temps qu’il faut.' )
			. schiesser_bm_p( 'Un siècle et demi plus tard, la vitrine a changé de visage et les outils se sont affinés, mais la logique est restée la même. On cuit le matin, on vend le jour même, et l’on continue de faire devant les clients ce que d’autres font en coulisses.' ),
			schiesser_demo_photo( '1587248720327-8eb72564be1e', 'transmission-atelier-confiserie', 'Transmission des gestes entre deux générations dans l’atelier' ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'La <em>chronologie</em>', 'note' => 'Les grandes étapes de la maison.', 'fond' => 'clair', 'largeur' => 'lecture' ),
		schiesser_bm_liste( array(
			'<strong>1870 · La fondation.</strong> La confiserie ouvre ses portes au cœur de Bâle. Une adresse, un four, une ambition simple : la précision du goût.',
			'<strong>1920 · Le salon de thé.</strong> La maison ouvre son salon au premier étage, qui devient un lieu de rendez-vous des Bâlois.',
			'<strong>1948 · L’atelier.</strong> Les cuivres et les outils de cette époque servent encore aujourd’hui.',
			'<strong>1975 · La transmission.</strong> Le savoir-faire passe d’une génération à la suivante. Les recettes restent, la main se transmet.',
			'<strong>Aujourd’hui · Toujours au Marktplatz.</strong> Même adresse, mêmes gestes, une nouvelle génération de gourmands.',
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Ce qui n’a pas <em>changé</em>', 'note' => 'Trois principes, tenus depuis la première fournée.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Le fait main', 3 ) . schiesser_bm_p( 'Chaque pièce est coupée, roulée ou trempée à la main. Aucune étape n’est déléguée à une machine.' ),
			schiesser_bm_titre( 'Le jour même', 3 ) . schiesser_bm_p( 'On cuit le matin, on vend dans la journée. Ce qui n’est pas assez bon ne rejoint pas la vitrine.' ),
			schiesser_bm_titre( 'À vue', 3 ) . schiesser_bm_p( 'L’atelier reste visible depuis la boutique. Ce que nous faisons, nous le faisons devant vous.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'entete' => false, 'fond' => 'alterne', 'centre' => true, 'largeur' => 'lecture' ),
		schiesser_bm_citation( 'Cinq générations, une seule adresse, et un savoir-faire transmis sans interruption depuis la première fournée.', 'Confiserie Schiesser' )
	)
	. $appel( 'Venez écrire le prochain <em>chapitre</em>', 'La meilleure façon de comprendre cette maison reste encore de pousser la porte, au Marktplatz.', array( 'Nous visiter', $u( '/nous-visiter/' ) ), array( 'Découvrir la boutique', $u( '/boutique/' ) ) );

	/* ============ Nous visiter ============ */
	$visiter = schiesser_demo_hero( array(
		'hauteur'      => 'compacte',
		'surtitre'     => 'Marktplatz · 4001 Bâle',
		'titre'        => "Une confiserie au Marktplatz,<br><em>au centre de tout</em>",
		'texte'        => 'À deux pas du tram et de l’hôtel de ville : voici nos horaires, et comment nous rejoindre.',
		'bouton1Texte' => 'Horaires',
		'bouton1Lien'  => '#horaires',
		'bouton2Texte' => 'Itinéraire',
		'bouton2Lien'  => schiesser_reglage( 'lien_maps' ),
	), '1517244683847-7456b63c5969', 'confiserie-historique-bale', 'Façade historique de la Confiserie Schiesser sur le Marktplatz de Bâle' )
	. $horaires_adresse( '01', 'Horaires et <em>adresse</em>' )
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Venir <em>jusqu’à nous</em>', 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Notre confiserie au Marktplatz se trouve au centre historique de Bâle, face à l’hôtel de ville.', 'chapeau' )
		. schiesser_bm_liste( array(
			'<strong>En tram :</strong> arrêt « Marktplatz », juste devant la place (horaires sur <a href="https://www.bvb.ch/fr/">bvb.ch</a>).',
			'<strong>À pied :</strong> le Marktplatz est en zone piétonne, l’accès se fait depuis les rues voisines.',
			'<strong>En voiture :</strong> les parkings couverts du centre sont à quelques minutes à pied.',
		) )
		. schiesser_bm_p( 'Pour prolonger la visite de la vieille ville, l’office du tourisme propose ses idées de promenade sur <a href="https://www.basel.com/fr">basel.com</a>.' )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Bon à <em>savoir</em>', 'note' => 'Les informations pratiques avant de pousser la porte.', 'fond' => 'clair' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Paiement', 3 ) . schiesser_bm_p( 'Cartes, sans contact et espèces en francs suisses. Les euros sont acceptés en boutique.' ),
			schiesser_bm_titre( 'Langues', 3 ) . schiesser_bm_p( 'Nous vous accueillons en allemand, français et anglais.' ),
			schiesser_bm_titre( 'Accessibilité', 3 ) . schiesser_bm_p( 'Boutique de plain-pied depuis la place. N’hésitez pas à nous prévenir pour un accueil facilité.' ),
		), 'filets' )
		. schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'À emporter', 3 ) . schiesser_bm_p( 'Tous nos produits se prennent à l’emporter, avec un emballage adapté au voyage.' ),
			schiesser_bm_titre( 'Coffrets cadeaux', 3 ) . schiesser_bm_p( 'Composés à la main sur place, avec emballage soigné pour offrir.' ),
			schiesser_bm_titre( 'Le meilleur moment', 3 ) . schiesser_bm_p( 'Le matin en semaine, pour le plus grand choix et un salon plus calme.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Questions <em>fréquentes</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Faut-il réserver pour le salon de thé ?', 'Non, l’accès se fait librement. Nous conseillons simplement la réservation à partir de six personnes.' )
		. schiesser_bm_question( 'Y a-t-il un parking à proximité ?', 'Le Marktplatz est en zone piétonne. Les parkings couverts du centre restent à quelques minutes à pied.' )
		. schiesser_bm_question( 'Les produits voyagent-ils bien ?', 'Nos coffrets sont conçus pour être emportés. Demandez un emballage renforcé si vous prenez l’avion ou le train.' )
	)
	. $appel( 'À très bientôt au <em>Marktplatz</em>', 'Nous sommes là du matin au soir, la vitrine est remontée chaque jour et le salon vous attend à l’étage.', array( 'Découvrir la boutique', $u( '/boutique/' ) ), array( 'Nous écrire', $u( '/contact/' ) ) );

	/* ============ Contact ============ */
	$contact = schiesser_demo_hero( array(
		'hauteur'      => 'compacte',
		'surtitre'     => 'Une question, une commande, un projet',
		'titre'        => "Contacter la<br><em>Confiserie Schiesser</em>",
		'texte'        => 'Un message, un appel ou une visite au Marktplatz : nous répondons à chaque demande.',
		'bouton1Texte' => 'Écrire un e-mail',
		'bouton1Lien'  => 'mailto:' . schiesser_reglage( 'email' ),
		'bouton2Texte' => 'Appeler',
		'bouton2Lien'  => schiesser_lien_tel(),
	), '1509440159596-0249088772ff', 'boutique-confiserie-marktplatz-bale', 'Vitrine de la boutique de la Confiserie Schiesser au Marktplatz' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'Nous <em>joindre</em>', 'note' => 'Une seule adresse, une seule équipe, pour la boutique comme pour le salon.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_p( 'Pour contacter la Confiserie Schiesser, une commande, une question ou une réservation de groupe : écrivez-nous ou appelez-nous, l’équipe de la maison vous répond.', 'chapeau' )
			. schiesser_bm_p( 'Par e-mail, comptez un à deux jours ouvrés. Pour une demande urgente, mieux vaut nous appeler pendant les heures d’ouverture.' ),
			schiesser_bm_liste( array(
				'<strong>Téléphone :</strong> [schiesser_telephone]',
				'<strong>E-mail :</strong> [schiesser_email]',
				'<strong>Adresse :</strong> [schiesser_adresse]',
				'<strong>Aujourd’hui :</strong> [schiesser_statut]',
			), 'filets' )
			. schiesser_bm_boutons( array( array( 'Écrire un e-mail', 'mailto:' . schiesser_reglage( 'email' ), 'vert' ), array( 'Itinéraire', schiesser_reglage( 'lien_maps' ), 'contour' ) ) ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Avant de nous <em>écrire</em>', 'note' => 'La réponse s’y trouve peut-être déjà.', 'largeur' => 'lecture', 'fond' => 'clair' ),
		schiesser_bm_question( 'Sous quel délai répondez-vous ?', 'Comptez un à deux jours ouvrés par e-mail. Pour une demande urgente, mieux vaut nous appeler pendant les heures d’ouverture.' )
		. schiesser_bm_question( 'Combien de temps à l’avance commander un gâteau ?', 'Quelques jours suffisent pour la plupart des pièces. Pour une commande importante ou une date de forte affluence, prévoyez davantage.' )
		. schiesser_bm_question( 'Peut-on réserver une table au salon de thé ?', 'Le salon est accessible librement. La réservation est conseillée à partir de six personnes ou pour un événement privé.' )
		. schiesser_bm_question( 'Proposez-vous des cadeaux d’entreprise ?', 'Oui, nous composons des coffrets personnalisés pour vos clients ou vos équipes, avec la possibilité d’ajouter votre message. Écrivez-nous en précisant les quantités et la date souhaitée.' )
		. schiesser_bm_question( 'Où en est la boutique en ligne ?', 'Elle arrive prochainement. En attendant, tout se commande par téléphone ou par e-mail, et se retire en boutique au Marktplatz.' )
	)
	. $appel( 'Le plus simple, c’est de pousser la <em>porte</em>', 'Nous sommes là du matin au soir, la vitrine est remontée chaque jour et le salon vous attend à l’étage.', array( 'Horaires et accès', $u( '/nous-visiter/' ) ) );

	/* ============ Mentions légales (brouillon à compléter) ============ */
	$mentions = schiesser_bm_section(
		array( 'entete' => false, 'largeur' => 'lecture' ),
		schiesser_bm_p( 'À compléter avant la mise en ligne, puis publier cette page : elle apparaîtra automatiquement dans le pied de page.', 'chapeau' )
		. schiesser_bm_titre( 'Éditeur du site', 2 )
		. schiesser_bm_p( 'Raison sociale, forme juridique, adresse du siège, numéro IDE (CHE-…), personne responsable, contact.' )
		. schiesser_bm_titre( 'Hébergement', 2 )
		. schiesser_bm_p( 'Nom et adresse de l’hébergeur du site.' )
		. schiesser_bm_titre( 'Crédits photos', 2 )
		. schiesser_bm_p( 'Photographies : à compléter.' )
	);

	return array(
		array( 'titre' => 'Accueil', 'slug' => 'accueil', 'anciens' => array(), 'contenu' => $accueil,
			'seo' => array( 'titre' => 'Confiserie à Bâle | Chocolats et salon de thé – Schiesser', 'description' => 'Confiserie à Bâle depuis 1870 sur le Marktplatz : Läckerli, truffes et pralinés faits main, et un salon de thé à l’étage. Venez nous rendre visite.', 'mots_cles' => array( 'confiserie à Bâle', 'salon de thé Bâle', 'Läckerli de Bâle', 'chocolats Bâle' ) ) ),
		array( 'titre' => 'La boutique', 'slug' => 'boutique', 'anciens' => array(), 'contenu' => $boutique,
			'seo' => array( 'titre' => 'Chocolats à Bâle | Läckerli, pralinés, coffrets – Schiesser', 'description' => 'Chocolats à Bâle faits main : Läckerli, truffes, pralinés et coffrets cadeaux préparés dans notre atelier du Marktplatz. Découvrez la boutique.', 'mots_cles' => array( 'chocolats à Bâle', 'Läckerli de Bâle', 'pralinés Bâle', 'coffret cadeau Bâle' ) ) ),
		array( 'titre' => 'Tea Room', 'slug' => 'salon-de-the', 'anciens' => array( 'tea-room' ), 'contenu' => $salon,
			'seo' => array( 'titre' => 'Salon de thé à Bâle | Tea Room du Marktplatz – Schiesser', 'description' => 'Salon de thé à Bâle, au premier étage de la confiserie : chocolat chaud maison, pâtisseries du jour et vue sur le Marktplatz. Sans réservation.', 'mots_cles' => array( 'salon de thé à Bâle', 'tea room Bâle', 'chocolat chaud Bâle' ) ) ),
		array( 'titre' => 'Notre histoire', 'slug' => 'notre-histoire', 'anciens' => array(), 'contenu' => $histoire,
			'seo' => array( 'titre' => 'Confiserie historique à Bâle | Depuis 1870 – Schiesser', 'description' => 'Confiserie historique à Bâle fondée en 1870 sur le Marktplatz : cinq générations, une seule adresse et des gestes transmis à la main. Notre histoire.', 'mots_cles' => array( 'confiserie historique à Bâle', 'histoire Confiserie Schiesser' ) ) ),
		array( 'titre' => 'Nous visiter', 'slug' => 'nous-visiter', 'anciens' => array(), 'contenu' => $visiter,
			'seo' => array( 'titre' => 'Confiserie au Marktplatz | Horaires et accès – Schiesser', 'description' => 'Confiserie au Marktplatz de Bâle, à deux pas du tram : horaires d’ouverture à jour, adresse, accès et conseils pratiques pour préparer votre visite.', 'mots_cles' => array( 'confiserie au Marktplatz', 'horaires Confiserie Schiesser', 'Marktplatz Bâle' ) ) ),
		array( 'titre' => 'Contact', 'slug' => 'contact', 'anciens' => array(), 'contenu' => $contact,
			'seo' => array( 'titre' => 'Contacter la Confiserie Schiesser | Commandes à Bâle', 'description' => 'Contacter la Confiserie Schiesser à Bâle : commande de gâteau, coffret cadeau, réservation de groupe ou simple question. Écrivez-nous ou appelez-nous.', 'mots_cles' => array( 'contacter la Confiserie Schiesser', 'commande gâteau Bâle' ) ) ),
		array( 'titre' => 'Mentions légales', 'slug' => 'mentions-legales', 'anciens' => array(), 'contenu' => $mentions, 'statut' => 'draft',
			'seo' => array( 'titre' => 'Mentions légales | Confiserie Schiesser', 'description' => 'Mentions légales du site de la Confiserie Schiesser, confiserie et salon de thé au Marktplatz de Bâle : éditeur, hébergement et crédits.', 'mots_cles' => array( 'mentions légales' ) ) ),
	);
}

/* ------------------------------------------------------------------ */
/* Import                                                              */
/* ------------------------------------------------------------------ */

/**
 * @param bool $remplacer Met à jour le contenu des pages et produits existants (version 0.1 → 0.2).
 */
function schiesser_importer_demo( $remplacer = false ) {
	/* Catégories */
	$cats = array();
	foreach ( array( 'Chocolats', 'Biscuits', 'Pâtisserie', 'Coffrets' ) as $nom ) {
		$t = term_exists( $nom, SCHIESSER_CATEGORIE );
		if ( ! $t ) {
			$t = wp_insert_term( $nom, SCHIESSER_CATEGORIE );
		}
		if ( ! is_wp_error( $t ) ) {
			$cats[ $nom ] = is_array( $t ) ? (int) $t['term_id'] : (int) $t;
		}
	}

	/* Produits */
	foreach ( schiesser_demo_produits() as $ordre => $p ) {
		$slug    = sanitize_title( $p['nom'] );
		$produit = get_page_by_path( $slug, OBJECT, SCHIESSER_PRODUIT );
		foreach ( $p['anciens'] as $ancien ) {
			$produit = $produit ?: get_page_by_path( $ancien, OBJECT, SCHIESSER_PRODUIT );
		}
		if ( $produit && ! $remplacer ) {
			continue;
		}
		$donnees = array(
			'post_type'    => SCHIESSER_PRODUIT,
			'post_status'  => 'publish',
			'post_title'   => $p['nom'],
			'post_name'    => $slug,
			'post_content' => wp_slash( implode( "\n\n", array_map( 'schiesser_bm_p', $p['texte'] ) ) ),
			'menu_order'   => $ordre + 1,
		);
		if ( $produit ) {
			$donnees['ID'] = $produit->ID;
			$id            = wp_update_post( $donnees );
		} else {
			$id = wp_insert_post( $donnees );
		}
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		if ( isset( $cats[ $p['categorie'] ] ) ) {
			wp_set_object_terms( $id, array( $cats[ $p['categorie'] ] ), SCHIESSER_CATEGORIE );
		}
		foreach ( array( 'prix', 'unite', 'badge', 'description', 'accord', 'origine' ) as $cle ) {
			update_post_meta( $id, '_s_' . $cle, $p[ $cle ] );
		}
		update_post_meta( $id, '_s_badge_style', $p['style'] );
		update_post_meta( $id, '_s_fiche', $p['fiche'] );
		schiesser_demo_seo( $id, $p['seo'] );
		$img = schiesser_demo_image( $p['photo'], $slug, $p['alt'] );
		if ( $img ) {
			set_post_thumbnail( $id, $img );
		}
	}

	/* Pages */
	$ids = array();
	foreach ( schiesser_demo_pages() as $ordre => $p ) {
		$page = get_page_by_path( $p['slug'] );
		foreach ( $p['anciens'] as $ancien ) {
			$page = $page ?: get_page_by_path( $ancien );
		}
		if ( $page && ! $remplacer ) {
			$ids[ $p['slug'] ] = $page->ID;
			continue;
		}
		$donnees = array(
			'post_type'    => 'page',
			'post_status'  => $p['statut'] ?? 'publish',
			'post_title'   => $p['titre'],
			'post_name'    => $p['slug'],
			'post_content' => wp_slash( $p['contenu'] ), // wp_insert_post retire les barres obliques : on les protège
			'menu_order'   => $ordre,
		);
		if ( $page ) {
			$donnees['ID']          = $page->ID;
			$donnees['post_status'] = $page->post_status;
			if ( $page->post_name !== $p['slug'] && ! in_array( $page->post_name, (array) get_post_meta( $page->ID, '_schiesser_ancien_slug' ), true ) ) {
				// L'ancienne adresse (ex. /tea-room/) redirigera vers la nouvelle.
				add_post_meta( $page->ID, '_schiesser_ancien_slug', $page->post_name );
			}
			$ids[ $p['slug'] ] = wp_update_post( $donnees );
		} else {
			$ids[ $p['slug'] ] = wp_insert_post( $donnees );
		}
		if ( $ids[ $p['slug'] ] && ! is_wp_error( $ids[ $p['slug'] ] ) ) {
			schiesser_demo_seo( $ids[ $p['slug'] ], $p['seo'] );
			// Anciennes adresses connues (ex. /tea-room/ de la version 0.1) : elles redirigent vers la page.
			foreach ( $p['anciens'] as $ancien ) {
				if ( ! in_array( $ancien, (array) get_post_meta( $ids[ $p['slug'] ], '_schiesser_ancien_slug' ), true ) ) {
					add_post_meta( $ids[ $p['slug'] ], '_schiesser_ancien_slug', $ancien );
				}
			}
		}
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['accueil'] );

	/* Contenus d'exemple de WordPress jamais modifiés : retirés (inutiles, et présents dans le plan du site). */
	foreach ( array( array( 'hello-world', 'post' ), array( 'bonjour-tout-le-monde', 'post' ), array( 'sample-page', 'page' ), array( 'page-d-exemple', 'page' ) ) as $exemple ) {
		$p = get_page_by_path( $exemple[0], OBJECT, $exemple[1] );
		if ( $p && $p->post_modified_gmt === $p->post_date_gmt ) {
			wp_delete_post( $p->ID, true );
		}
	}

	/* Gamme de prix : l'ancienne valeur par défaut « CHF » (sans chiffres) devient une vraie fourchette. */
	$reglages = (array) get_option( SCHIESSER_OPTION, array() );
	if ( isset( $reglages['gamme_prix'] ) && 'CHF' === trim( $reglages['gamme_prix'] ) ) {
		$reglages['gamme_prix'] = schiesser_reglages_defaut()['gamme_prix'];
		update_option( SCHIESSER_OPTION, $reglages );
	}

	/* Slogan du site (titre de l'onglet, données pour Google), s'il n'a jamais été changé */
	$slogan = get_option( 'blogdescription' );
	if ( '' === $slogan || in_array( $slogan, array( 'Just another WordPress site', 'Un site utilisant WordPress', 'Un site utilisant WordPress.' ), true ) ) {
		update_option( 'blogdescription', 'Confiserie et salon de thé à Bâle depuis 1870' );
	}

	/* Menu principal */
	$menu    = wp_get_nav_menu_object( 'Menu principal' );
	$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Menu principal' );
	if ( ! is_wp_error( $menu_id ) && ! wp_get_nav_menu_items( $menu_id ) ) {
		foreach ( array( 'boutique', 'salon-de-the', 'notre-histoire', 'nous-visiter', 'contact' ) as $slug ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-object-id' => $ids[ $slug ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}
	}
	if ( ! is_wp_error( $menu_id ) ) {
		$positions              = get_theme_mod( 'nav_menu_locations', array() );
		$positions['principal'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $positions );
	}

	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();

	update_option( 'schiesser_demo_importee', 1 );
	update_option( 'schiesser_demo_version', SCHIESSER_VERSION );
}

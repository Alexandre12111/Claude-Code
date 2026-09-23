<?php
/**
 * Contenu de démonstration, importé en un clic depuis l'administration.
 *
 * Crée (ou met à jour) : les catégories, les 8 produits avec leur page,
 * les 7 pages du site composées de sections (et le brouillon des mentions
 * légales), les menus de l'en-tête et du pied de page, et les réglages SEO
 * de chaque page (titre, description, mot-clé principal), lus par Rank Math
 * dès qu'il est installé.
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
	if ( $importee && version_compare( $version, '0.2.1', '>=' ) ) {
		return;
	}
	if ( ! $importee ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo' ), 'schiesser_import_demo' );
		?>
		<div class="notice notice-info">
			<p><strong>Thème Schiesser :</strong> importer le contenu de démonstration (8 produits avec leur page, 7 pages composées de sections, menus, réglages SEO). Les photos sont téléchargées depuis Unsplash, cela peut prendre une minute.</p>
			<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>">Importer le contenu de démonstration</a></p>
		</div>
		<?php
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo&remplacer=1' ), 'schiesser_import_demo' );
	?>
	<div class="notice notice-info">
		<p><strong>Thème Schiesser 0.2 :</strong> un nouveau contenu de démonstration est disponible (textes optimisés pour le référencement, page « Cadeaux d’entreprise », questions fréquentes, pages produits). La mise à jour <strong>remplace le contenu</strong> des pages et des 8 produits de démonstration.</p>
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

/** Lien interne pour les textes de démonstration. */
function schiesser_demo_lien( $chemin, $texte ) {
	return '<a href="' . esc_url( home_url( $chemin ) ) . '">' . $texte . '</a>';
}

/**
 * Adresse de la page d'un produit, calculée comme WordPress calcule son slug
 * (« Läckerli » donne « lackerli » en français, « laeckerli » en allemand).
 */
function schiesser_demo_url_produit( $nom ) {
	return '/produits/' . sanitize_title( $nom ) . '/';
}

/** Texte d'une fiche : liste de paragraphes ; une entrée « ### Titre » devient un intertitre H3. */
function schiesser_demo_texte( $elements ) {
	$html = '';
	foreach ( $elements as $e ) {
		$html .= 0 === strpos( $e, '### ' ) ? schiesser_bm_titre( substr( $e, 4 ), 3 ) : schiesser_bm_p( $e );
	}
	return $html;
}

/**
 * Les 8 produits de la maquette.
 * Clés : nom, anciens (adresses des versions précédentes), categorie, prix, unite, badge, style, photo, alt,
 * description, fiche, accord, origine, texte (page du produit), seo.
 */
function schiesser_demo_produits() {
	$l = 'schiesser_demo_lien';
	return array(
		array(
			'nom' => 'Läckerli de Bâle', 'anciens' => array( 'lackerli-de-basel', 'laeckerli-de-basel' ), 'categorie' => 'Biscuits',
			'prix' => 'CHF 6.50', 'unite' => 'les 100 g', 'badge' => 'Signature', 'style' => 'vert',
			'photo' => '1606313564200-e75d5e30476c', 'alt' => 'Läckerli de Bâle glacés, découpés en rectangles',
			'description' => 'Nos Läckerli de Bâle au miel, aux amandes et aux épices sont cuits puis glacés à la main. La spécialité de la ville, telle qu’on la fait ici depuis toujours.',
			'fiche' => array( array( 'Composition', 'Farine de blé, miel, sucre, amandes, noisettes, écorces confites, épices, kirsch' ), array( 'Format', 'Sachet de 100 g ou 250 g' ), array( 'Conservation', 'Plusieurs semaines au sec' ), array( 'Voyage', 'Excellent, se transporte sans risque' ) ),
			'accord' => 'Un thé noir corsé ou un café crème', 'origine' => 'Préparés, glacés et découpés dans l’atelier du Marktplatz',
			'texte' => array(
				'Le Läckerli de Bâle (Basler Läckerli en allemand) est le biscuit emblématique de la ville : une pâte au miel, aux amandes et aux épices, relevée de kirsch, cuite sur plaque puis recouverte d’un fin glaçage au sucre.',
				'### Comment nous préparons nos Läckerli de Bâle',
				'La pâte est étalée sur plaque et cuite dans l’atelier du Marktplatz. Encore tiède, elle reçoit son glaçage, puis elle est découpée à la main en rectangles réguliers.',
				'### Conservation et transport',
				'Leur texture ferme s’attendrit en quelques jours. Gardés au sec dans leur sachet fermé, ils se conservent plusieurs semaines et voyagent sans risque : c’est le souvenir de Bâle le plus facile à rapporter.',
				'### Formats et prix',
				'Sachet de 100 g : CHF 6.50. Le sachet de 250 g est disponible en boutique. Pour un cadeau plus varié, pensez au ' . $l( schiesser_demo_url_produit( 'Coffret de chocolats' ), 'coffret de chocolats' ) . ' ou à nos ' . $l( schiesser_demo_url_produit( 'Biscuits aux amandes' ), 'biscuits aux amandes' ) . ', et dégustez-les au ' . $l( '/salon-de-the/', 'salon de thé' ) . ' avec un thé noir corsé.',
				'Allergènes : gluten (blé), fruits à coque. Contient de l’alcool (kirsch). La liste complète des ingrédients est disponible au comptoir. Pour en savoir plus sur cette spécialité bâloise, voir le <a href="https://www.patrimoineculinaire.ch/">Patrimoine culinaire suisse</a>.',
			),
			'seo' => array( 'titre' => 'Läckerli de Bâle | Biscuit au miel fait main – Schiesser', 'description' => 'Läckerli de Bâle préparés au Marktplatz : miel, amandes et épices, cuits sur plaque puis glacés et découpés à la main. Sachets de 100 g ou 250 g à offrir.', 'mots_cles' => array( 'Läckerli de Bâle', 'Basler Läckerli', 'spécialité de Bâle' ) ),
		),
		array(
			'nom' => 'Truffes au chocolat', 'anciens' => array( 'truffes-maison' ), 'categorie' => 'Chocolats',
			'prix' => 'CHF 2.20', 'unite' => 'la pièce', 'badge' => '', 'style' => 'vert',
			'photo' => '1548907040-4baa42d10919', 'alt' => 'Truffes au chocolat roulées dans le cacao amer',
			'description' => 'Des truffes au chocolat à la ganache pure, roulées chaque matin dans le cacao amer. Elles ne se gardent pas longtemps, c’est bon signe.',
			'fiche' => array( array( 'Composition', 'Chocolat de couverture, crème, cacao amer' ), array( 'Format', 'À la pièce ou en boîte' ), array( 'Conservation', 'Quelques jours au frais' ), array( 'Voyage', 'Sur de courtes distances' ) ),
			'accord' => 'Un espresso court, sans sucre', 'origine' => 'Crème fraîche du jour',
			'texte' => array(
				'Nos truffes au chocolat sont préparées chaque matin dans l’atelier du Marktplatz : une ganache à la crème fraîche, façonnée à la main puis roulée dans le cacao amer.',
				'### Pourquoi elles se dégustent vite',
				'Préparées à la crème fraîche, nos truffes gardent tout leur fondant quelques jours seulement. Conservez-les au frais dans leur boîte fermée, et sortez-les une demi-heure avant de les servir.',
				'### À la pièce ou en boîte',
				'Au comptoir, on les choisit à la pièce (CHF 2.20) et l’équipe compose la boîte avec vous, avec nos ' . $l( schiesser_demo_url_produit( 'Pralinés artisanaux' ), 'pralinés artisanaux' ) . ' si vous le souhaitez. Pour offrir, le ' . $l( schiesser_demo_url_produit( 'Coffret de chocolats' ), 'coffret de chocolats' ) . ' réunit truffes, pralinés et spécialités de saison.',
				'### Avec quoi les déguster',
				'Un espresso court, sans sucre, ou un chocolat chaud maison au ' . $l( '/salon-de-the/', 'salon de thé' ) . ', juste à l’étage.',
				'Allergènes : lait, soja selon le chocolat de couverture. Préparées dans un atelier qui travaille aussi les fruits à coque, le gluten et l’œuf.',
			),
			'seo' => array( 'titre' => 'Truffes au chocolat | Roulées chaque matin – Schiesser', 'description' => 'Truffes au chocolat faites chaque matin à Bâle : ganache à la crème fraîche roulée dans le cacao amer. À la pièce ou en boîte, au Marktplatz.', 'mots_cles' => array( 'truffes au chocolat', 'truffes Bâle' ) ),
		),
		array(
			'nom' => 'Pralinés artisanaux', 'anciens' => array( 'pralines-assortis' ), 'categorie' => 'Chocolats',
			'prix' => 'CHF 2.60', 'unite' => 'la pièce', 'badge' => '', 'style' => 'vert',
			'photo' => '1481391319762-47dff72954d9', 'alt' => 'Assortiment de pralinés artisanaux décorés à la main',
			'description' => 'Des pralinés artisanaux trempés et décorés un à un dans notre atelier. L’assortiment change au fil des saisons.',
			'fiche' => array( array( 'Composition', 'Selon la pièce : fruits secs, ganaches, pâtes d’amande' ), array( 'Format', 'À la pièce ou en coffret' ), array( 'Conservation', 'Deux à trois semaines' ), array( 'Voyage', 'Bon, en emballage renforcé' ) ),
			'accord' => 'Un vin doux ou un thé fumé', 'origine' => 'Trempés et décorés à la main dans l’atelier',
			'texte' => array(
				'Nos pralinés artisanaux sont trempés et décorés un à un dans l’atelier : pralinés aux fruits secs, ganaches parfumées, pâtes d’amande. L’assortiment suit les saisons.',
				'### Composer sa boîte',
				'Au comptoir, on les choisit à la pièce (CHF 2.60) et l’équipe compose la boîte avec vous. Pour offrir sans hésiter, notre ' . $l( schiesser_demo_url_produit( 'Coffret de chocolats' ), 'coffret de chocolats' ) . ' existe en 9, 16 ou 25 pièces. Au ' . $l( '/salon-de-the/', 'salon de thé' ) . ', trois pralinés accompagnent volontiers un café.',
				'### Conservation',
				'Deux à trois semaines, à l’abri de la chaleur et de la lumière.',
				'Allergènes : selon la pièce (fruits à coque, lait, soja, parfois gluten). La liste de chaque praliné est disponible au comptoir.',
			),
			'seo' => array( 'titre' => 'Pralinés artisanaux | Assortiment de saison – Schiesser', 'description' => 'Pralinés artisanaux trempés et décorés à la main dans notre atelier de Bâle : un assortiment qui change au fil des saisons, à la pièce ou en coffret.', 'mots_cles' => array( 'pralinés artisanaux', 'pralinés Bâle' ) ),
		),
		array(
			'nom' => 'Tablette de chocolat artisanale', 'anciens' => array( 'tablettes-de-la-maison' ), 'categorie' => 'Chocolats',
			'prix' => 'CHF 9.50', 'unite' => "l'unité", 'badge' => '', 'style' => 'vert',
			'photo' => '1464195244916-405fa0a82545', 'alt' => 'Tablette de chocolat artisanale aux éclats d’amandes',
			'description' => 'Une tablette de chocolat artisanale coulée dans notre atelier, nature ou aux éclats d’amandes. Simple, et c’est bien là tout l’exercice.',
			'fiche' => array( array( 'Composition', 'Chocolat de couverture, éclats d’amandes selon la version' ), array( 'Format', 'Tablette de 100 g' ), array( 'Conservation', 'Plusieurs mois au sec' ), array( 'Voyage', 'Idéal' ) ),
			'accord' => 'À croquer seul, à température ambiante', 'origine' => 'Coulée et moulée dans l’atelier du Marktplatz',
			'texte' => array(
				'Chaque tablette de chocolat artisanale est coulée dans notre atelier, nature ou parsemée d’éclats d’amandes. Simple, et c’est bien là tout l’exercice.',
				'### Conservation et voyage',
				'Elle se garde plusieurs mois au sec, à l’abri de la chaleur et de la lumière, et voyage sans crainte. C’est l’idée cadeau la plus simple à rapporter de Bâle, avec nos ' . $l( schiesser_demo_url_produit( 'Läckerli de Bâle' ), 'Läckerli de Bâle' ) . '.',
				'### Format et prix',
				'Tablette de 100 g : CHF 9.50, nature ou aux éclats d’amandes.',
				'Allergènes : amandes pour la version aux éclats, lait et soja selon le chocolat. La liste complète est disponible au comptoir.',
			),
			'seo' => array( 'titre' => 'Tablette de chocolat artisanale | Faite main – Schiesser', 'description' => 'Tablette de chocolat artisanale coulée dans notre atelier de Bâle, nature ou aux éclats d’amandes. Elle se garde plusieurs mois : le cadeau idéal à rapporter.', 'mots_cles' => array( 'tablette de chocolat artisanale', 'tablette de chocolat Bâle' ) ),
		),
		array(
			'nom' => 'Biscuits aux amandes', 'anciens' => array( 'biscuits-damande' ), 'categorie' => 'Biscuits',
			'prix' => 'CHF 7.80', 'unite' => 'les 150 g', 'badge' => '', 'style' => 'vert',
			'photo' => '1556910103-1c02745aae4d', 'alt' => 'Biscuits aux amandes dorés à la sortie du four',
			'description' => 'Des biscuits aux amandes cuits chaque matin, croquants dehors et tendres au cœur. Trois ingrédients, rien d’autre.',
			'fiche' => array( array( 'Composition', 'Amandes, sucre, blancs d’œufs' ), array( 'Format', 'Sachet de 150 g' ), array( 'Conservation', 'Deux semaines en boîte' ), array( 'Voyage', 'Très bon' ) ),
			'accord' => 'Un thé vert léger', 'origine' => 'Amandes entières, sucre et blancs d’œufs, rien d’autre',
			'texte' => array(
				'Trois ingrédients seulement pour nos biscuits aux amandes : des amandes, du sucre et des blancs d’œufs. Ils cuisent chaque matin, et on les voit sortir du four depuis le comptoir.',
				'### Croquants dehors, tendres au cœur',
				'Une cuisson courte leur donne une fine croûte dorée et un cœur moelleux. Ils se gardent deux semaines en boîte fermée, à l’abri de l’humidité.',
				'### Avec quoi les déguster',
				'Un thé vert léger au ' . $l( '/salon-de-the/', 'salon de thé' ) . ' de l’étage, ou en cadeau avec nos ' . $l( schiesser_demo_url_produit( 'Läckerli de Bâle' ), 'Läckerli de Bâle' ) . ', l’autre biscuit de la maison.',
				'Sachet de 150 g : CHF 7.80. Allergènes : amandes, œuf. Notre atelier travaille aussi le blé : des traces de gluten sont possibles.',
			),
			'seo' => array( 'titre' => 'Biscuits aux amandes | Cuits chaque matin – Schiesser', 'description' => 'Biscuits aux amandes cuits chaque matin à Bâle : amandes, sucre et blancs d’œufs, rien d’autre. Croquants dehors, tendres au cœur, en sachet de 150 g.', 'mots_cles' => array( 'biscuits aux amandes', 'biscuits Bâle' ) ),
		),
		array(
			'nom' => 'Marrons glacés', 'anciens' => array(), 'categorie' => 'Confiseries',
			'prix' => 'CHF 4.80', 'unite' => 'la pièce', 'badge' => 'En saison', 'style' => 'menthe',
			'photo' => '1519915028121-7d3463d20b13', 'alt' => 'Marrons glacés présentés en coffret',
			'description' => 'Des marrons glacés confits lentement, un classique d’hiver présenté en coffret. Disponibles seulement quelques mois par an.',
			'fiche' => array( array( 'Composition', 'Châtaignes, sucre, vanille' ), array( 'Format', 'À la pièce ou en coffret' ), array( 'Conservation', 'Quelques semaines' ), array( 'Voyage', 'Bon, à protéger du choc' ) ),
			'accord' => 'Un chocolat chaud, en hiver', 'origine' => 'Châtaignes confites plusieurs jours dans l’atelier',
			'texte' => array(
				'Nos marrons glacés sont confits pendant plusieurs jours dans un sirop vanillé, puis glacés un à un. Un travail lent, réservé à la saison froide.',
				'### Quand les trouver',
				'Ils arrivent en vitrine avec l’automne et restent disponibles pendant l’hiver, dans la limite des stocks. Pour les fêtes de fin d’année, réservez-les par ' . $l( '/contact/', 'téléphone ou e-mail' ) . '.',
				'### À la pièce ou en coffret',
				'CHF 4.80 la pièce, ou en coffret à offrir. Ils se conservent quelques semaines au sec, à l’abri de la chaleur. Pour un assortiment plus large, voyez aussi notre ' . $l( schiesser_demo_url_produit( 'Coffret de chocolats' ), 'coffret de chocolats' ) . '.',
			),
			'seo' => array( 'titre' => 'Marrons glacés | Confits lentement, en saison – Schiesser', 'description' => 'Marrons glacés confits plusieurs jours dans un sirop vanillé, glacés un à un. Disponibles en saison à la Confiserie Schiesser, à la pièce ou en coffret.', 'mots_cles' => array( 'marrons glacés', 'marrons glacés Bâle' ) ),
		),
		array(
			'nom' => 'Gâteau sur commande', 'anciens' => array( 'gateau-de-la-maison' ), 'categorie' => 'Pâtisserie',
			'prix' => 'Sur commande', 'unite' => 'de 6 à 40 personnes', 'badge' => '', 'style' => 'vert',
			'photo' => '1565958011703-44f9829ba187', 'alt' => 'Gâteau d’anniversaire décoré sur commande à Bâle',
			'description' => 'Votre gâteau sur commande à Bâle, pour un anniversaire, un mariage ou une fête de famille. Nous le composons ensemble, en boutique ou par téléphone.',
			'fiche' => array( array( 'Parfums', 'À choisir ensemble, selon la saison' ), array( 'Format', 'De 6 à 40 personnes' ), array( 'Délai', 'Quelques jours d’avance' ), array( 'Retrait', 'En boutique, au Marktplatz' ) ),
			'accord' => '', 'origine' => 'Ingrédients frais, montage le jour du retrait',
			'texte' => array(
				'Anniversaire, mariage, fête de famille : nous préparons votre gâteau sur commande à Bâle, de 6 à 40 personnes. Parfums, décor et message se choisissent ensemble.',
				'### Parfums et décors',
				'Le parfum se choisit avec l’équipe, selon la saison et vos goûts. Le décor peut porter un prénom, un message ou suivre un thème.',
				'### Délais et retrait',
				'Commandez quelques jours à l’avance, davantage pour un mariage ou pendant les fêtes. Le gâteau est monté le jour même et se retire en boutique, au Marktplatz, aux heures d’ouverture.',
				'### Comment commander',
				'Passez en boutique, appelez-nous au [schiesser_telephone] ou ' . $l( '/contact/', 'écrivez-nous' ) . ' en indiquant la date, le nombre de personnes, le parfum souhaité et les éventuelles allergies. Pour fêter l’occasion sur place, le ' . $l( '/salon-de-the/', 'salon de thé' ) . ' peut être privatisé selon les périodes.',
			),
			'seo' => array( 'titre' => 'Gâteau sur commande à Bâle | Anniversaires – Schiesser', 'description' => 'Gâteau sur commande à Bâle pour anniversaires, mariages et fêtes, de 6 à 40 personnes. Parfums et décor à choisir ensemble, retrait au Marktplatz.', 'mots_cles' => array( 'gâteau sur commande', 'gâteau d’anniversaire Bâle' ) ),
		),
		array(
			'nom' => 'Coffret de chocolats', 'anciens' => array( 'coffret-decouverte' ), 'categorie' => 'Coffrets',
			'prix' => 'CHF 28.00', 'unite' => '16 pièces', 'badge' => 'Cadeau', 'style' => 'vert',
			'photo' => '1549007994-cb92caebd54b', 'alt' => 'Coffret de chocolats assortis emballé pour offrir',
			'description' => 'Un coffret de chocolats composé à la main dans nos vitrines et emballé pour offrir. Le meilleur moyen de tout goûter.',
			'fiche' => array( array( 'Composition', 'Pralinés, truffes et spécialités de saison' ), array( 'Format', '9, 16 ou 25 pièces' ), array( 'Conservation', 'Deux à trois semaines (truffes : quelques jours)' ), array( 'Personnalisation', 'Message ou logo possible' ) ),
			'accord' => 'Se partage, et c’est bien là l’idée', 'origine' => 'Composé à la main en boutique',
			'texte' => array(
				'Le coffret de chocolats pour tout goûter : ' . $l( schiesser_demo_url_produit( 'Pralinés artisanaux' ), 'pralinés artisanaux' ) . ', ' . $l( schiesser_demo_url_produit( 'Truffes au chocolat' ), 'truffes au chocolat' ) . ' et spécialités de saison, choisis dans nos vitrines et rangés à la main.',
				'### Trois formats',
				'9, 16 ou 25 pièces, chaque coffret emballé pour offrir. Le coffret de 16 pièces coûte CHF 28.00 ; les autres formats sont indiqués en boutique. En hiver, quelques ' . $l( schiesser_demo_url_produit( 'Marrons glacés' ), 'marrons glacés' ) . ' peuvent compléter l’assortiment.',
				'### Cadeaux d’entreprise',
				'Pour vos clients ou vos équipes, nous ajoutons votre message ou votre logo. Tout est expliqué sur la page ' . $l( '/cadeaux-entreprise/', 'cadeaux d’entreprise' ) . '.',
				'### Conservation',
				'Deux à trois semaines à l’abri de la chaleur et de la lumière. Les truffes à la crème fraîche se dégustent en premier, dans les jours qui suivent.',
				'Allergènes : fruits à coque, lait, soja, gluten selon les pièces. La liste complète est disponible au comptoir.',
			),
			'seo' => array( 'titre' => 'Coffret de chocolats | Assortiment à offrir – Schiesser', 'description' => 'Coffret de chocolats composé à la main à Bâle : pralinés, truffes et spécialités de saison, de 9 à 25 pièces. Message ou logo possible pour offrir.', 'mots_cles' => array( 'coffret de chocolats', 'coffret cadeau Bâle' ) ),
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
	$l = 'schiesser_demo_lien';

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
			array( 'numero' => $numero, 'titre' => $titre, 'note' => 'Sur le Marktplatz, à deux pas de l’hôtel de ville et du tram.', 'fond' => 'sable', 'ancre' => 'horaires' ),
			schiesser_bm_colonnes( array(
				schiesser_bm_titre( 'Horaires d’ouverture', 3 ) . schiesser_bm_code_court( '[schiesser_horaires]' ),
				schiesser_bm_titre( 'Adresse', 3 )
				. schiesser_bm_p( '<strong>Confiserie Schiesser</strong><br>[schiesser_adresse]' )
				. schiesser_bm_p( 'Téléphone : [schiesser_telephone]<br>E-mail : [schiesser_email]' )
				. schiesser_bm_code_court( '[schiesser_itineraire texte="Itinéraire sur Google Maps"]' ),
			) )
		);
	};

	$devis = schiesser_mailto( 'Demande de devis : cadeaux d’entreprise', array(
		'Entreprise :',
		'Nombre de coffrets et format (9, 16 ou 25 pièces) :',
		'Date de remise souhaitée :',
		'Message ou logo à faire figurer :',
		'Adresse de facturation :',
		'Nom et téléphone :',
	) );
	$message = schiesser_mailto( 'Demande', array( 'Votre demande :', '', 'Nom et téléphone :' ) );

	/* ============ Accueil ============ */
	$accueil = schiesser_demo_hero( array(
		'hauteur'      => 'accueil',
		'ariane'       => false,
		'sceau'        => true,
		'surtitre'     => 'Maison fondée à Bâle · depuis 1870',
		'titre'        => 'Le goût précis <br>d’une <em>confiserie à&nbsp;Bâle</em>',
		'texte'        => 'Läckerli, truffes et pralinés faits main au cœur du Marktplatz, et un salon de thé à l’étage.',
		'bouton1Texte' => 'Découvrir les créations',
		'bouton1Lien'  => $u( '/boutique/' ),
		'bouton2Texte' => 'Nous rendre visite',
		'bouton2Lien'  => $u( '/nous-visiter/' ),
	), '1481391319762-47dff72954d9', 'confiserie-a-bale-pralines', 'Pralinés et chocolats en vitrine à la Confiserie Schiesser, confiserie à Bâle' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'La Confiserie Schiesser <em>en bref</em>', 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Confiserie à Bâle depuis 1870, la maison Schiesser est installée sur le Marktplatz, au cœur de la vieille ville. On y prépare chaque jour, à la main, les spécialités qui font sa réputation.', 'chapeau' )
		. schiesser_bm_p( 'Au rez-de-chaussée, ' . $l( '/boutique/', 'la boutique' ) . ' et son atelier visible : Läckerli de Bâle, truffes, pralinés, tablettes et coffrets. À l’étage, ' . $l( '/salon-de-the/', 'le salon de thé' ) . ' sert chocolat chaud maison et pâtisseries du jour, face à la place. La confiserie est ouverte [schiesser_horaires_phrase]. Et derrière chaque vitrine, plus de 150 ans d’' . $l( '/notre-histoire/', 'histoire' ) . '.' )
	)
	. schiesser_bm( 'schiesser/produits', array(
		'numero'    => '02',
		'titre'     => 'Les <em>créations</em> de la maison',
		'note'      => 'Quatre spécialités de la maison, avec leur prix. Toute la gamme est à découvrir dans la boutique.',
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
			schiesser_demo_photo( '1509440159596-0249088772ff', 'boutique-confiserie-marktplatz-bale', 'Vitrine de chocolats faits main à Bâle, boutique de la Confiserie Schiesser au Marktplatz' )
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
	. schiesser_bm_section(
		array( 'numero' => '05', 'titre' => 'Offrir un peu <em>de Bâle</em>', 'note' => 'Des cadeaux qui voyagent bien, composés à la main.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Des Läckerli de Bâle', 3 ) . schiesser_bm_p( 'Le souvenir le plus facile à rapporter : ils se gardent plusieurs semaines et voyagent sans risque.' ) . schiesser_bm_boutons( array( array( 'Voir les Läckerli', $u( schiesser_demo_url_produit( 'Läckerli de Bâle' ) ), 'lien' ) ) ),
			schiesser_bm_titre( 'Un coffret de chocolats', 3 ) . schiesser_bm_p( 'Neuf, seize ou vingt-cinq pièces choisies dans nos vitrines, emballées pour offrir.' ) . schiesser_bm_boutons( array( array( 'Voir le coffret', $u( schiesser_demo_url_produit( 'Coffret de chocolats' ) ), 'lien' ) ) ),
			schiesser_bm_titre( 'Pour les entreprises', 3 ) . schiesser_bm_p( 'Des coffrets à votre message ou à votre logo, pour vos clients et vos équipes.' ) . schiesser_bm_boutons( array( array( 'Cadeaux d’entreprise', $u( '/cadeaux-entreprise/' ), 'lien' ) ) ),
		), 'cartes' )
	)
	. $horaires_adresse( '06', 'Nous <em>trouver</em>' )
	. schiesser_bm_section(
		array( 'numero' => '07', 'titre' => 'Questions fréquentes sur la <em>confiserie</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Où acheter des Läckerli à Bâle ?', 'À la Confiserie Schiesser, sur le Marktplatz, au cœur de la vieille ville. Nos ' . $l( schiesser_demo_url_produit( 'Läckerli de Bâle' ), 'Läckerli de Bâle' ) . ' sont cuits, glacés et découpés à la main dans l’atelier de la maison, puis vendus en sachets de 100 g ou 250 g. Ils se gardent plusieurs semaines et voyagent très bien.' )
		. schiesser_bm_question( 'Quels sont les horaires de la confiserie ?', 'La confiserie est ouverte [schiesser_horaires_phrase]. Les horaires peuvent varier les jours fériés : consultez la page ' . $l( '/nous-visiter/', 'Nous visiter' ) . ' ou appelez-nous en cas de doute.' )
		. schiesser_bm_question( 'Peut-on commander un gâteau sur mesure ?', 'Oui. Nous réalisons des ' . $l( schiesser_demo_url_produit( 'Gâteau sur commande' ), 'gâteaux sur commande' ) . ' de 6 à 40 personnes, avec quelques jours d’avance. Passez en boutique ou ' . $l( '/contact/', 'écrivez-nous' ) . ' pour choisir ensemble le parfum et le décor.' )
		. schiesser_bm_question( 'Que rapporter de Bâle comme souvenir gourmand ?', 'Des Läckerli de Bâle, qui se conservent plusieurs semaines et voyagent sans risque, ou une tablette de chocolat de la maison, qui se garde plusieurs mois. Nous composons aussi des coffrets de 9, 16 ou 25 pièces, emballés pour le voyage.' )
	)
	. $appel( 'Passez nous voir au <em>Marktplatz</em>', 'La vitrine est remontée chaque matin, et le salon vous attend à l’étage.', array( 'Horaires et accès', $u( '/nous-visiter/' ) ), array( 'Nous écrire', $u( '/contact/' ) ) );

	/* ============ La boutique ============ */
	$boutique = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'surtitre'     => 'Rez-de-chaussée · Marktplatz',
		'titre'        => 'Chocolats à&nbsp;Bâle, <br><em>faits main sur place</em>',
		'texte'        => 'Nos chocolats à Bâle sortent de l’atelier du Marktplatz, à quelques mètres du comptoir : truffes du matin, pralinés de saison, tablettes et coffrets, aux côtés des Läckerli de la maison.',
		'bouton1Texte' => 'Voir les produits',
		'bouton1Lien'  => '#catalogue',
		'bouton2Texte' => 'Passer commande',
		'bouton2Lien'  => '#commander',
	), '1509440159596-0249088772ff', 'boutique-confiserie-marktplatz-bale', 'Vitrine de chocolats faits main à Bâle, boutique de la Confiserie Schiesser au Marktplatz' )
	. schiesser_bm( 'schiesser/produits', array( 'numero' => '01', 'titre' => 'Les produits de la <em>boutique</em>', 'note' => 'Chocolats, biscuits, confiseries, pâtisserie et coffrets : les spécialités de la maison et leur prix. Chaque fiche détaille la composition, la conservation et le transport.' ) )
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Nos chocolats à Bâle, <em>de l’atelier au comptoir</em>', 'largeur' => 'lecture', 'fond' => 'clair' ),
		schiesser_bm_p( 'Nos ' . $l( schiesser_demo_url_produit( 'Truffes au chocolat' ), 'truffes au chocolat' ) . ' sont roulées chaque matin, nos ' . $l( schiesser_demo_url_produit( 'Pralinés artisanaux' ), 'pralinés artisanaux' ) . ' trempés un à un et nos ' . $l( schiesser_demo_url_produit( 'Tablette de chocolat artisanale' ), 'tablettes' ) . ' coulées dans l’atelier. Tout se choisit à la pièce, au poids ou en ' . $l( schiesser_demo_url_produit( 'Coffret de chocolats' ), 'coffret de chocolats' ) . ', et tout ce qui est en vitrine part immédiatement, sans commande préalable.', 'chapeau' )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Bon à <em>savoir</em>', 'note' => 'Conservation, transport et commandes particulières.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Conservation', 3 ) . schiesser_bm_p( 'À l’abri de la chaleur et de la lumière. Les tablettes se gardent plusieurs mois, les pralinés deux à trois semaines, les truffes quelques jours. Les pâtisseries se dégustent le jour même.' ),
			schiesser_bm_titre( 'Voyager avec', 3 ) . schiesser_bm_p( 'Demandez un emballage renforcé si vous prenez l’avion ou le train. Les Läckerli et les coffrets supportent très bien le trajet.' ),
			schiesser_bm_titre( 'Gâteaux sur commande', 3 ) . schiesser_bm_p( 'Anniversaires, mariages, fêtes de famille : notre ' . $l( schiesser_demo_url_produit( 'Gâteau sur commande' ), 'gâteau sur commande' ) . ' se prépare pour 6 à 40 personnes. Prévoyez quelques jours d’avance.' ),
		), 'filets' )
		. schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Cadeaux d’entreprise', 3 ) . schiesser_bm_p( 'Nos coffrets se personnalisent avec votre message ou votre logo, pour vos clients ou vos équipes : voir ' . $l( '/cadeaux-entreprise/', 'cadeaux d’entreprise' ) . '.' ),
			schiesser_bm_titre( 'Retrait immédiat', 3 ) . schiesser_bm_p( 'Tout ce qui est en vitrine part immédiatement. Les commandes particulières se retirent en boutique, aux heures d’ouverture.' ),
			schiesser_bm_titre( 'Sortie du four', 3 ) . schiesser_bm_p( 'Nos ' . $l( schiesser_demo_url_produit( 'Biscuits aux amandes' ), 'biscuits aux amandes' ) . ' et les pâtisseries rejoignent la vitrine le matin même, puis les assiettes du ' . $l( '/salon-de-the/', 'salon de thé' ) . '. Venir tôt reste le meilleur moyen d’avoir le plus grand choix.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Passer <em>commande</em>', 'note' => 'Trois manières de repartir avec vos produits.', 'fond' => 'alterne', 'ancre' => 'commander' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'En boutique', 3 ) . schiesser_bm_p( 'Poussez la porte au Marktplatz. Tout ce qui est en vitrine part immédiatement, sans commande préalable.' ) . schiesser_bm_boutons( array( array( 'Horaires et accès', $u( '/nous-visiter/' ), 'lien' ) ) ),
			schiesser_bm_titre( 'Par téléphone', 3 ) . schiesser_bm_p( 'Pour un gâteau, un grand coffret ou une commande d’entreprise, appelez-nous pendant les heures d’ouverture : [schiesser_telephone]' ),
			schiesser_bm_titre( 'Par e-mail', 3 ) . schiesser_bm_p( 'Envoyez-nous votre composition ou votre demande détaillée, nous revenons vers vous sous un à deux jours ouvrés : [schiesser_email]' ),
		), 'cartes' )
	)
	. schiesser_bm_section(
		array( 'numero' => '05', 'titre' => 'Questions fréquentes sur nos <em>chocolats</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Les produits voyagent-ils bien ?', 'Nos coffrets sont conçus pour être emportés. Demandez un emballage renforcé si vous prenez l’avion ou le train : les Läckerli, les tablettes et les coffrets supportent très bien le trajet.' )
		. schiesser_bm_question( 'Livrez-vous ou expédiez-vous les commandes ?', 'Les commandes se retirent en boutique, au Marktplatz. Écrivez-nous pour toute demande particulière, nous verrons ensemble ce qui est possible.' )
		. schiesser_bm_question( 'Vos chocolats contiennent-ils de l’alcool ?', 'Certaines spécialités, oui : nos Läckerli de Bâle contiennent du kirsch. Demandez conseil au comptoir, l’équipe vous indique les pièces sans alcool.' )
		. schiesser_bm_question( 'Vos produits conviennent-ils aux allergies ?', 'Nos ateliers travaillent les fruits à coque, le lait, le soja, le gluten et l’œuf : nous ne pouvons donc exclure les traces. La liste des ingrédients de chaque produit est disponible au comptoir, et nous vous orientons volontiers.' )
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
		'titre'        => 'Un salon de thé à&nbsp;Bâle, <br><em>au-dessus de la place</em>',
		'texte'        => 'On traverse la boutique, on monte l’escalier, et le bruit du Marktplatz s’éloigne. Ici, le temps ralentit.',
		'bouton1Texte' => 'Voir la carte',
		'bouton1Lien'  => '#carte',
		'bouton2Texte' => 'Venir au salon',
		'bouton2Lien'  => '#venir',
	), '1445116572660-236099ec97a0', 'salon-de-the-bale', 'Salon de thé de la Confiserie Schiesser, au premier étage' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'Monter à <em>l’étage</em>', 'note' => 'Quelques marches séparent la boutique du salon. Elles font toute la différence.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_p( 'Notre salon de thé à Bâle est un lieu que l’on ne découvre qu’en poussant la porte, puis en levant les yeux. Il occupe le premier étage de la Confiserie Schiesser, juste au-dessus de la boutique, et ses fenêtres donnent sur le Marktplatz.', 'chapeau' )
			. schiesser_bm_p( 'On y accède en traversant la boutique, entre les vitrines et l’odeur de l’atelier tout proche. En haut, tout change : on sert le chocolat chaud maison et les pâtisseries du jour, et l’on vient y passer une heure plutôt que quelques minutes. Beaucoup l’appellent simplement le Tea Room.' )
			. schiesser_bm_boutons( array( array( 'Venir au salon', '#venir', 'lien' ) ) ),
			schiesser_demo_photo( '1600891964092-4316c288032e', 'salon-de-the-marktplatz', 'Tables du salon de thé près des fenêtres donnant sur le Marktplatz' ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'La carte du <em>salon</em>', 'note' => 'Carte susceptible d’évoluer selon la saison et les arrivages.', 'fond' => 'clair', 'ancre' => 'carte' ),
		schiesser_bm_colonnes( array(
			$carte( 'Cafés et chocolats', array(
				array( 'Chocolat chaud maison', 'CHF 7.50', 'Préparé à partir de notre chocolat de couverture, à l’ancienne. En hiver, avec un ' . $l( schiesser_demo_url_produit( 'Marrons glacés' ), 'marron glacé' ) . '.' ),
				array( 'Café crème', 'CHF 5.20', 'Torréfaction sélectionnée pour la maison, servi en porcelaine.' ),
				array( 'Cappuccino', 'CHF 6.00', 'Mousse dense, servi avec un praliné de la boutique.' ),
				array( 'Espresso', 'CHF 4.50', 'Court et franc, comme il se doit.' ),
			) ) . $carte( 'Thés et infusions', array(
				array( 'Thé noir de saison', 'CHF 6.50', 'Sélection changeante, servie en théière.' ),
				array( 'Thé vert', 'CHF 6.50', 'Infusion douce, à l’eau frémissante.' ),
				array( 'Infusion maison', 'CHF 6.00', 'Mélange de plantes composé pour le salon.' ),
			) ),
			$carte( 'Pâtisseries', array(
				array( 'Part du jour', 'CHF 7.80', 'La pâtisserie sortie du four le matin même.' ),
				array( 'Gâteau au chocolat', 'CHF 8.20', 'Dense et peu sucré, servi à température.' ),
				array( 'Tarte de saison', 'CHF 7.50', 'Selon les fruits du marché, juste en face.' ),
				array( 'Assortiment de pralinés', 'CHF 9.00', 'Trois ' . $l( schiesser_demo_url_produit( 'Pralinés artisanaux' ), 'pralinés artisanaux' ) . ' choisis dans la vitrine du bas.' ),
			) ) . $carte( 'Salé et glaces', array(
				array( 'Petite salade', 'CHF 12.50', 'Feuilles de saison, vinaigrette maison.' ),
				array( 'Croque du salon', 'CHF 14.00', 'Pain de campagne, servi chaud.' ),
				array( 'Coupe glacée', 'CHF 10.50', 'Glaces maison, chantilly montée à la commande.' ),
			) ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Le salon en <em>détail</em>' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Les fenêtres', 3 ) . schiesser_bm_p( 'Elles donnent directement sur le Marktplatz. Les meilleures tables sont celles qui les longent.' ),
			schiesser_bm_titre( 'Les boiseries', 3 ) . schiesser_bm_p( 'Entretenues plutôt que remplacées, elles portent les marques de chaque génération, que raconte ' . $l( '/notre-histoire/', 'notre histoire' ) . '.' ),
			schiesser_bm_titre( 'Le service', 3 ) . schiesser_bm_p( 'Porcelaine, plateaux et gestes appris sur le tas. Rien n’a été modernisé pour le plaisir de moderniser.' ),
			schiesser_bm_titre( 'Les pâtisseries', 3 ) . schiesser_bm_p( 'Elles montent de l’atelier au fil de la journée. Ce qui est en vitrine en bas se retrouve dans les assiettes en haut.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Venir au <em>salon de thé</em>', 'largeur' => 'lecture', 'fond' => 'alterne', 'ancre' => 'venir' ),
		schiesser_bm_liste( array(
			'<strong>Horaires :</strong> le salon suit les horaires de la confiserie, [schiesser_horaires_phrase].',
			'<strong>Accès :</strong> par l’escalier, en traversant la boutique du ' . $l( '/nous-visiter/', 'Marktplatz' ) . '.',
			'<strong>Groupes :</strong> réservation conseillée dès six personnes, privatisation possible selon les périodes.',
			'<strong>Paiement :</strong> cartes, sans contact, espèces en francs suisses et en euros.',
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '05', 'titre' => 'Questions sur notre salon de thé <em>à Bâle</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Faut-il réserver pour le salon de thé ?', 'Non, l’accès se fait librement. Nous conseillons simplement la réservation à partir de six personnes.' )
		. schiesser_bm_question( 'Peut-on privatiser le salon ?', 'C’est possible selon les périodes et les horaires. ' . $l( '/contact/', 'Contactez-nous' ) . ' avec votre date, le nombre de personnes et le type d’accueil souhaité.' )
		. schiesser_bm_question( 'Le salon sert-il des plats salés ?', 'Oui, une courte carte salée accompagne les pâtisseries : petite salade de saison et croque du salon, servi chaud.' )
	)
	. $appel( 'Une table vous <em>attend</em>', 'Poussez la porte de la boutique, traversez jusqu’à l’escalier et montez. Il y a presque toujours une place près d’une fenêtre.', array( 'Horaires et accès', $u( '/nous-visiter/' ) ), array( 'Réserver pour un groupe', $u( '/contact/' ) ) );

	/* ============ Notre histoire ============ */
	$histoire = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'surtitre'     => 'Depuis 1870 · Marktplatz',
		'titre'        => 'Une confiserie historique <br><em>à Bâle depuis 1870</em>',
		'texte'        => 'L’histoire d’une confiserie bâloise ouverte en 1870 sur le Marktplatz, et dont les gestes se transmettent de génération en génération.',
		'bouton1Texte' => 'Découvrir la boutique',
		'bouton1Lien'  => $u( '/boutique/' ),
	), '1517244683847-7456b63c5969', 'confiserie-historique-bale', 'Façade historique de la Confiserie Schiesser sur le Marktplatz de Bâle' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'L’<em>origine</em>', 'note' => 'Une seule histoire, racontée simplement. Le reste se goûte sur place.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_p( 'En 1870, une confiserie ouvre ses portes sur le Marktplatz, la plus belle place de Bâle. Plus de 150 ans plus tard, cette confiserie historique à Bâle y accueille toujours ses clients.', 'chapeau' )
			. schiesser_bm_p( 'Le four est installé à l’arrière, la vitrine donne sur la place. Très vite, la maison se fait un nom sur quelques pièces seulement, travaillées à la main du matin au soir : du miel, des amandes, des épices, et le temps qu’il faut. C’est encore la base de nos ' . $l( schiesser_demo_url_produit( 'Läckerli de Bâle' ), 'Läckerli de Bâle' ) . '.' )
			. schiesser_bm_p( 'Un siècle et demi plus tard, la vitrine a changé de visage et les outils se sont affinés, mais la logique est restée la même. On cuit le matin, on vend le jour même, et l’on continue de faire devant les clients ce que d’autres font en coulisses.' ),
			schiesser_demo_photo( '1587248720327-8eb72564be1e', 'transmission-atelier-confiserie', 'Transmission des gestes entre deux générations dans l’atelier' ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'La chronologie, <em>de 1870 à aujourd’hui</em>', 'note' => 'Les grandes étapes de la maison.', 'fond' => 'clair', 'largeur' => 'lecture' ),
		schiesser_bm_liste( array(
			'<strong>1870 · La fondation.</strong> La confiserie ouvre ses portes au cœur de Bâle. Une adresse, un four, une ambition simple : la précision du goût.',
			'<strong>1920 · Le salon de thé.</strong> La maison ouvre son ' . $l( '/salon-de-the/', 'salon de thé' ) . ' au premier étage, qui devient un lieu de rendez-vous des Bâlois.',
			'<strong>1948 · L’atelier.</strong> Les cuivres et les outils de cette époque servent encore aujourd’hui.',
			'<strong>1975 · La transmission.</strong> Le savoir-faire passe d’une génération à la suivante. Les recettes restent, la main se transmet.',
			'<strong>Aujourd’hui · Toujours au Marktplatz.</strong> Mêmes gestes, une nouvelle génération de gourmands.',
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Ce qui n’a pas <em>changé</em>', 'note' => 'Trois principes, tenus depuis la première fournée.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Le fait main', 3 ) . schiesser_bm_p( 'Découper, rouler, tremper, décorer : les gestes qui font la différence restent faits à la main, comme au premier jour.' ),
			schiesser_bm_titre( 'Le jour même', 3 ) . schiesser_bm_p( 'Pâtisseries et truffes sont préparées le matin pour être vendues dans la journée. Ce qui n’est pas assez bon ne rejoint pas la vitrine.' ),
			schiesser_bm_titre( 'À vue', 3 ) . schiesser_bm_p( 'L’atelier reste visible depuis la boutique. Ce que nous faisons, nous le faisons devant vous.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'entete' => false, 'fond' => 'alterne', 'centre' => true, 'largeur' => 'lecture' ),
		schiesser_bm_citation( 'Des gestes transmis de génération en génération, depuis la première fournée.', 'Confiserie Schiesser' )
	)
	. $appel( 'Venez écrire le prochain <em>chapitre</em>', 'La meilleure façon de comprendre cette maison reste encore de pousser la porte, au Marktplatz.', array( 'Nous visiter', $u( '/nous-visiter/' ) ), array( 'Découvrir la boutique', $u( '/boutique/' ) ) );

	/* ============ Nous visiter ============ */
	$visiter = schiesser_demo_hero( array(
		'hauteur'      => 'compacte',
		'surtitre'     => 'Marktplatz · Bâle',
		'titre'        => 'Une confiserie au Marktplatz, <br><em>au centre de tout</em>',
		'texte'        => 'Notre confiserie au Marktplatz vous accueille à deux pas du tram et de l’hôtel de ville. Voici nos horaires et comment nous rejoindre.',
		'bouton1Texte' => 'Horaires',
		'bouton1Lien'  => '#horaires',
		'bouton2Texte' => 'Itinéraire',
		'bouton2Lien'  => schiesser_reglage( 'lien_maps' ),
	), '1517244683847-7456b63c5969', 'confiserie-historique-bale', 'Façade historique de la Confiserie Schiesser sur le Marktplatz de Bâle' )
	. $horaires_adresse( '01', 'Horaires et <em>adresse</em>' )
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Venir <em>jusqu’à nous</em>', 'largeur' => 'lecture' ),
		schiesser_bm_p( 'La Confiserie Schiesser se trouve sur le Marktplatz, au centre historique de Bâle, à deux pas de l’hôtel de ville (Rathaus).', 'chapeau' )
		. schiesser_bm_liste( array(
			'<strong>En tram :</strong> arrêt « Marktplatz », juste devant la place (horaires sur <a href="https://www.bvb.ch/fr/">bvb.ch</a>).',
			'<strong>Depuis la gare CFF :</strong> quelques arrêts de tram jusqu’au Marktplatz, ou une vingtaine de minutes à pied.',
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
			schiesser_bm_titre( 'Accessibilité', 3 ) . schiesser_bm_p( 'La boutique est de plain-pied depuis la place. Le salon de thé se trouve au premier étage, accessible par un escalier. Prévenez-nous pour un accueil facilité.' ),
		), 'filets' )
		. schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'À emporter', 3 ) . schiesser_bm_p( 'Tous nos produits se prennent à l’emporter, avec un emballage adapté au voyage.' ),
			schiesser_bm_titre( 'Coffrets cadeaux', 3 ) . schiesser_bm_p( 'Notre ' . $l( schiesser_demo_url_produit( 'Coffret de chocolats' ), 'coffret de chocolats' ) . ' est composé à la main sur place, avec un emballage soigné pour offrir.' ),
			schiesser_bm_titre( 'Le meilleur moment', 3 ) . schiesser_bm_p( 'Le matin en semaine, pour le plus grand choix et un salon plus calme.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Questions pratiques <em>avant votre visite</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Y a-t-il un parking à proximité ?', 'Le Marktplatz est en zone piétonne. Les parkings couverts du centre restent à quelques minutes à pied.' )
		. schiesser_bm_question( 'Peut-on payer en euros ?', 'Oui, les euros sont acceptés en boutique. Nous acceptons aussi les cartes, le paiement sans contact et les espèces en francs suisses.' )
		. schiesser_bm_question( 'Où déguster sur place ?', 'Au ' . $l( '/salon-de-the/', 'salon de thé' ) . ' du premier étage, ouvert aux horaires de la confiserie : chocolat chaud maison, pâtisseries du jour et petite carte salée.' )
	)
	. $appel( 'À très bientôt au <em>Marktplatz</em>', 'Nous sommes là du matin au soir, la vitrine est remontée chaque jour et le salon vous attend à l’étage.', array( 'Découvrir la boutique', $u( '/boutique/' ) ), array( 'Nous écrire', $u( '/contact/' ) ) );

	/* ============ Contact ============ */
	$contact = schiesser_demo_hero( array(
		'hauteur'      => 'compacte',
		'surtitre'     => 'Une question, une commande, un projet',
		'titre'        => 'Contacter la <br><em>Confiserie Schiesser</em>',
		'texte'        => 'Un message, un appel ou une visite au Marktplatz : nous répondons à chaque demande.',
		'bouton1Texte' => 'Écrire un e-mail',
		'bouton1Lien'  => $message,
		'bouton2Texte' => 'Appeler',
		'bouton2Lien'  => schiesser_lien_tel(),
	), '1509440159596-0249088772ff', 'boutique-confiserie-marktplatz-bale', 'Vitrine de chocolats faits main à Bâle, boutique de la Confiserie Schiesser au Marktplatz' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'Nous <em>joindre</em>', 'note' => 'Une équipe, pour la boutique comme pour le salon.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_p( 'Pour contacter la Confiserie Schiesser, une commande, une question ou une réservation de groupe : écrivez-nous ou appelez-nous, l’équipe de la maison vous répond.', 'chapeau' )
			. schiesser_bm_p( 'Par e-mail, comptez un à deux jours ouvrés. Pour une demande urgente, mieux vaut nous appeler pendant les heures d’ouverture.' ),
			schiesser_bm_liste( array(
				'<strong>Téléphone :</strong> [schiesser_telephone]',
				'<strong>E-mail :</strong> [schiesser_email]',
				'<strong>Adresse :</strong> [schiesser_adresse]',
				'<strong>Aujourd’hui :</strong> [schiesser_statut]',
			), 'filets' )
			. schiesser_bm_boutons( array( array( 'Écrire un e-mail', $message, 'vert' ), array( 'Itinéraire', schiesser_reglage( 'lien_maps' ), 'contour' ) ) ),
		) )
	)
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Avant de nous <em>écrire</em>', 'note' => 'La réponse s’y trouve peut-être déjà.', 'largeur' => 'lecture', 'fond' => 'clair' ),
		schiesser_bm_question( 'Sous quel délai répondez-vous ?', 'Comptez un à deux jours ouvrés par e-mail. Pour une demande urgente, mieux vaut nous appeler pendant les heures d’ouverture.' )
		. schiesser_bm_question( 'Combien de temps à l’avance commander un gâteau ?', 'Quelques jours suffisent pour la plupart des pièces, davantage pour une commande importante ou une date de forte affluence. Tout est détaillé sur la page ' . $l( schiesser_demo_url_produit( 'Gâteau sur commande' ), 'gâteau sur commande' ) . '.' )
		. schiesser_bm_question( 'Peut-on réserver une table au salon de thé ?', 'Le salon est accessible librement. La réservation est conseillée à partir de six personnes ou pour un événement privé : indiquez-nous la date, l’heure et le nombre de personnes. Plus d’informations sur la page du ' . $l( '/salon-de-the/', 'salon de thé' ) . '.' )
		. schiesser_bm_question( 'Proposez-vous des cadeaux d’entreprise ?', 'Oui, nous composons des coffrets de chocolats personnalisés pour vos clients ou vos équipes, avec votre message ou votre logo. Voir la page ' . $l( '/cadeaux-entreprise/', 'cadeaux d’entreprise' ) . '.' )
		. schiesser_bm_question( 'Peut-on commander à distance ?', 'Oui, par téléphone ou par e-mail. Les commandes se retirent ensuite en boutique, au Marktplatz, aux heures d’ouverture.' )
	)
	. $appel( 'Le plus simple, c’est de pousser la <em>porte</em>', 'La vitrine est remontée chaque jour et le salon vous attend à l’étage.', array( 'Horaires et accès', $u( '/nous-visiter/' ) ) );

	/* ============ Cadeaux d'entreprise ============ */
	$entreprise = schiesser_demo_hero( array(
		'hauteur'      => 'compacte',
		'surtitre'     => 'Entreprises · clients et équipes',
		'titre'        => 'Cadeaux d’entreprise <br><em>faits main à&nbsp;Bâle</em>',
		'texte'        => 'Remerciez vos clients et vos équipes avec des coffrets composés à la main dans notre atelier du Marktplatz, depuis 1870.',
		'bouton1Texte' => 'Demander un devis',
		'bouton1Lien'  => $devis,
		'bouton2Texte' => 'Appeler',
		'bouton2Lien'  => schiesser_lien_tel(),
	), '1549007994-cb92caebd54b', 'cadeaux-entreprise-bale-coffrets', 'Coffrets de chocolats emballés pour des cadeaux d’entreprise à Bâle' )
	. schiesser_bm_section(
		array( 'numero' => '01', 'titre' => 'Nos coffrets <em>pour les entreprises</em>', 'note' => 'Des cadeaux d’entreprise à Bâle qui voyagent bien et se gardent.' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Coffret de chocolats', 3 ) . schiesser_bm_p( '9, 16 ou 25 pièces : pralinés, truffes et spécialités de saison, emballés pour offrir.' ) . schiesser_bm_boutons( array( array( 'Voir le coffret', $u( schiesser_demo_url_produit( 'Coffret de chocolats' ) ), 'lien' ) ) ),
			schiesser_bm_titre( 'Läckerli de Bâle', 3 ) . schiesser_bm_p( 'La spécialité de la ville, en sachets : un cadeau qui voyage et se garde plusieurs semaines.' ) . schiesser_bm_boutons( array( array( 'Voir les Läckerli', $u( schiesser_demo_url_produit( 'Läckerli de Bâle' ) ), 'lien' ) ) ),
			schiesser_bm_titre( 'Tablettes de chocolat', 3 ) . schiesser_bm_p( 'Une tablette de chocolat artisanale, nature ou aux éclats d’amandes, facile à glisser dans un colis.' ) . schiesser_bm_boutons( array( array( 'Voir la tablette', $u( schiesser_demo_url_produit( 'Tablette de chocolat artisanale' ) ), 'lien' ) ) ),
		), 'cartes' )
	)
	. schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Votre message, <em>votre logo</em>', 'largeur' => 'lecture', 'fond' => 'clair' ),
		schiesser_bm_p( 'Chaque coffret peut porter votre message ou votre logo : une attention personnelle, composée à la main dans nos vitrines.', 'chapeau' )
		. schiesser_bm_p( 'Dites-nous ce que vous souhaitez faire figurer : nous vous proposons la présentation la plus adaptée à vos quantités et à votre date. Offrir un coffret Schiesser, c’est aussi offrir un peu de l’' . $l( '/notre-histoire/', 'histoire de Bâle' ) . ', celle d’une confiserie fondée en 1870.' )
	)
	. schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Comment <em>ça se passe</em>' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( '1. Vous nous écrivez', 3 ) . schiesser_bm_p( 'Quantités, formats et date de remise souhaitée, par e-mail ou par téléphone.' ),
			schiesser_bm_titre( '2. Nous vous répondons', 3 ) . schiesser_bm_p( 'Sous un à deux jours ouvrés, avec une proposition et un prix.' ),
			schiesser_bm_titre( '3. Nous préparons', 3 ) . schiesser_bm_p( 'Vos coffrets sont composés au plus près de la date de remise, puis retirés au Marktplatz.' ),
		), 'filets' )
	)
	. schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Pour un devis <em>rapide</em>', 'largeur' => 'lecture', 'fond' => 'alterne' ),
		schiesser_bm_p( 'Indiquez-nous simplement :' )
		. schiesser_bm_liste( array( 'le nombre de coffrets et le format (9, 16 ou 25 pièces) ;', 'la date de remise souhaitée ;', 'le message ou le logo à faire figurer ;', 'l’adresse de facturation ;', 'le nom et le téléphone de la personne à contacter.' ) )
		. schiesser_bm_boutons( array( array( 'Demander un devis', $devis, 'vert' ), array( 'Nous appeler', schiesser_lien_tel(), 'contour' ) ) )
	)
	. schiesser_bm_section(
		array( 'numero' => '05', 'titre' => 'Questions sur les cadeaux <em>d’entreprise</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Peut-on goûter avant de commander ?', 'Oui : au ' . $l( '/salon-de-the/', 'salon de thé' ) . ', l’assortiment de trois pralinés donne un aperçu de la vitrine. Vous pouvez aussi passer en boutique et composer une petite boîte.' )
		. schiesser_bm_question( 'Combien de temps à l’avance commander ?', 'Écrivez-nous dès que la date est connue. Quelques jours suffisent pour de petites quantités ; pour les fêtes de fin d’année, mieux vaut s’y prendre plusieurs semaines à l’avance.' )
		. schiesser_bm_question( 'Les coffrets voyagent-ils bien ?', 'Oui, ils sont conçus pour être emportés, et nous prévoyons un emballage renforcé sur demande.' )
		. schiesser_bm_question( 'Livrez-vous les commandes ?', 'Les commandes se retirent en boutique, au Marktplatz. Pour une demande particulière, écrivez-nous : nous verrons ensemble ce qui est possible.' )
	);

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
			'seo' => array( 'titre' => 'Confiserie à Bâle | Chocolats et salon de thé – Schiesser', 'description' => 'Confiserie à Bâle depuis 1870 sur le Marktplatz : Läckerli, truffes et pralinés faits main, et un salon de thé à l’étage. Venez nous rendre visite.', 'mots_cles' => array( 'confiserie à Bâle', 'confiserie Bâle', 'Confiserie Schiesser' ) ) ),
		array( 'titre' => 'La boutique', 'slug' => 'boutique', 'anciens' => array(), 'contenu' => $boutique,
			'seo' => array( 'titre' => 'Chocolats à Bâle | Faits main au Marktplatz – Schiesser', 'description' => 'Chocolats à Bâle faits main : truffes, pralinés, tablettes, Läckerli et coffrets cadeaux préparés dans notre atelier du Marktplatz. Découvrez la boutique.', 'mots_cles' => array( 'chocolats à Bâle', 'chocolaterie Bâle' ) ) ),
		array( 'titre' => 'Salon de thé', 'slug' => 'salon-de-the', 'anciens' => array( 'tea-room' ), 'contenu' => $salon,
			'seo' => array( 'titre' => 'Salon de thé à Bâle | Tea Room du Marktplatz – Schiesser', 'description' => 'Salon de thé à Bâle, au premier étage de la confiserie : chocolat chaud maison, pâtisseries du jour et vue sur le Marktplatz. Sans réservation.', 'mots_cles' => array( 'salon de thé à Bâle', 'tea room Bâle', 'chocolat chaud Bâle' ) ) ),
		array( 'titre' => 'Notre histoire', 'slug' => 'notre-histoire', 'anciens' => array(), 'contenu' => $histoire,
			'seo' => array( 'titre' => 'Confiserie historique à Bâle | Depuis 1870 – Schiesser', 'description' => 'Confiserie historique à Bâle fondée en 1870 sur le Marktplatz : une maison, une place et des gestes transmis de génération en génération. Notre histoire.', 'mots_cles' => array( 'confiserie historique à Bâle', 'histoire Confiserie Schiesser' ) ) ),
		array( 'titre' => 'Nous visiter', 'slug' => 'nous-visiter', 'anciens' => array(), 'contenu' => $visiter,
			'seo' => array( 'titre' => 'Confiserie au Marktplatz | Horaires et accès – Schiesser', 'description' => 'Confiserie au Marktplatz de Bâle, à deux pas du tram et de l’hôtel de ville : horaires d’ouverture, adresse, accès et conseils pratiques pour votre visite.', 'mots_cles' => array( 'confiserie au Marktplatz', 'horaires Confiserie Schiesser', 'Marktplatz Bâle' ) ) ),
		array( 'titre' => 'Contact', 'slug' => 'contact', 'anciens' => array(), 'contenu' => $contact,
			'seo' => array( 'titre' => 'Contacter la Confiserie Schiesser | Commandes à Bâle', 'description' => 'Contacter la Confiserie Schiesser à Bâle : commande de gâteau, coffret cadeau, réservation de groupe ou simple question. Écrivez-nous ou appelez-nous.', 'mots_cles' => array( 'contacter la Confiserie Schiesser', 'téléphone Confiserie Schiesser' ) ) ),
		array( 'titre' => 'Cadeaux d’entreprise', 'slug' => 'cadeaux-entreprise', 'anciens' => array(), 'contenu' => $entreprise,
			'seo' => array( 'titre' => 'Cadeaux d’entreprise à Bâle | Coffrets maison – Schiesser', 'description' => 'Cadeaux d’entreprise à Bâle : coffrets de chocolats et Läckerli faits main pour vos clients et vos équipes, à votre message ou à votre logo. Devis rapide.', 'mots_cles' => array( 'cadeaux d’entreprise à Bâle', 'coffret cadeau entreprise', 'cadeau client Bâle' ) ) ),
		array( 'titre' => 'Mentions légales', 'slug' => 'mentions-legales', 'anciens' => array(), 'contenu' => $mentions, 'statut' => 'draft',
			'seo' => array( 'titre' => 'Mentions légales | Confiserie Schiesser', 'description' => 'Mentions légales du site de la Confiserie Schiesser, confiserie et salon de thé au Marktplatz de Bâle : éditeur du site, hébergement et crédits photos.', 'mots_cles' => array( 'mentions légales' ) ) ),
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
	foreach ( array( 'Chocolats', 'Biscuits', 'Confiseries', 'Pâtisserie', 'Coffrets' ) as $nom ) {
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
			'post_content' => wp_slash( schiesser_demo_texte( $p['texte'] ) ),
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

	/*
	 * Anciennes valeurs par défaut jamais modifiées : remplacées par les nouvelles.
	 * Gamme de prix « CHF » (sans chiffres), présentation et mention de la version 0.1.
	 */
	$reglages = (array) get_option( SCHIESSER_OPTION, array() );
	$defaut   = schiesser_reglages_defaut();
	$anciens  = array(
		'gamme_prix'   => 'CHF',
		'presentation' => "Confiserie fondée à Bâle. Une maison, un savoir-faire, la même place depuis plus d'un siècle.",
		'mention'      => 'Confiserie fondée à Bâle · Marktplatz',
	);
	$change   = false;
	foreach ( $anciens as $cle => $ancien ) {
		if ( isset( $reglages[ $cle ] ) && $ancien === trim( $reglages[ $cle ] ) ) {
			$reglages[ $cle ] = $defaut[ $cle ];
			$change           = true;
		}
	}
	if ( $change ) {
		update_option( SCHIESSER_OPTION, $reglages );
	}

	/* Slogan du site (titre de l'onglet, données pour Google), s'il n'a jamais été changé */
	$slogan = get_option( 'blogdescription' );
	if ( '' === $slogan || in_array( $slogan, array( 'Just another WordPress site', 'Un site utilisant WordPress', 'Un site utilisant WordPress.' ), true ) ) {
		update_option( 'blogdescription', 'Confiserie et salon de thé à Bâle depuis 1870' );
	}

	/* Menus : principal (en-tête) et pied de page (colonne « Explorer »), créés s'ils sont vides */
	$menus = array(
		'principal' => array( 'Menu principal', array( 'boutique', 'salon-de-the', 'notre-histoire', 'nous-visiter', 'contact' ) ),
		'pied'      => array( 'Menu du pied de page', array( 'boutique', 'salon-de-the', 'cadeaux-entreprise', 'notre-histoire', 'nous-visiter', 'contact' ) ),
	);
	$positions = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus as $position => $menu_def ) {
		$menu    = wp_get_nav_menu_object( $menu_def[0] );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $menu_def[0] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		if ( ! wp_get_nav_menu_items( $menu_id ) ) {
			foreach ( $menu_def[1] as $slug ) {
				if ( empty( $ids[ $slug ] ) || is_wp_error( $ids[ $slug ] ) ) {
					continue;
				}
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-object-id' => $ids[ $slug ],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}
		}
		if ( empty( $positions[ $position ] ) || 'principal' === $position ) {
			$positions[ $position ] = $menu_id;
		}
	}
	set_theme_mod( 'nav_menu_locations', $positions );

	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();

	update_option( 'schiesser_demo_importee', 1 );
	update_option( 'schiesser_demo_version', SCHIESSER_VERSION );
}

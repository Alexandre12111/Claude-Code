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
	if ( $importee && version_compare( $version, '0.3.0', '>=' ) ) {
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
		<p><strong>Thème Schiesser 0.3 :</strong> les pages sont maintenant composées avec les blocs de la maquette (bandeau « Ouvert maintenant », catalogue, étages, ligne du temps, carte, carte du salon, archives, avant et après, itinéraires, formulaire de contact…). La mise à jour <strong>remplace le contenu</strong> des pages et des 8 produits de démonstration : les textes et réglages SEO optimisés sont conservés.</p>
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
			'accroche' => 'Miel, amandes, épices, kirsch',
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
			'accroche' => 'Ganache pure, cacao amer',
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
			'accroche' => 'Trempés et décorés à la main',
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
			'accroche' => 'Nature ou aux éclats d’amandes',
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
			'accroche' => 'Amandes, sucre, blancs d’œufs',
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
			'accroche' => 'Confits lentement, en saison',
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
			'accroche' => 'Monté sur commande',
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
			'prix' => 'CHF 28.—', 'unite' => '16 pièces', 'badge' => 'Cadeau', 'style' => 'vert',
			'accroche' => 'Sélection composée à la main',
			'photo' => '1549007994-cb92caebd54b', 'alt' => 'Coffret de chocolats assortis emballé pour offrir',
			'description' => 'Un coffret de chocolats composé à la main dans nos vitrines et emballé pour offrir. Le meilleur moyen de tout goûter.',
			'fiche' => array( array( 'Composition', 'Pralinés, truffes et spécialités de saison' ), array( 'Format', '9, 16 ou 25 pièces' ), array( 'Conservation', 'Deux à trois semaines (truffes : quelques jours)' ), array( 'Personnalisation', 'Message ou logo possible' ) ),
			'accord' => 'Se partage, et c’est bien là l’idée', 'origine' => 'Composé à la main en boutique',
			'texte' => array(
				'Le coffret de chocolats pour tout goûter : ' . $l( schiesser_demo_url_produit( 'Pralinés artisanaux' ), 'pralinés artisanaux' ) . ', ' . $l( schiesser_demo_url_produit( 'Truffes au chocolat' ), 'truffes au chocolat' ) . ' et spécialités de saison, choisis dans nos vitrines et rangés à la main.',
				'### Trois formats',
				'9, 16 ou 25 pièces, chaque coffret emballé pour offrir. Le coffret de 16 pièces coûte CHF 28.— ; les autres formats sont indiqués en boutique. En hiver, quelques ' . $l( schiesser_demo_url_produit( 'Marrons glacés' ), 'marrons glacés' ) . ' peuvent compléter l’assortiment.',
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
 * Les pages, composées avec les blocs de la maquette, dans l'ordre de la maquette.
 * Titre, adresse, anciennes adresses, contenu (blocs) et réglages SEO.
 */
function schiesser_demo_pages() {
	$b  = 'schiesser_bm';
	$l  = 'schiesser_demo_lien';
	$pr = 'schiesser_demo_url_produit';
	$u  = function ( $chemin ) {
		return home_url( $chemin );
	};
	// Photo d'un bloc : identifiant, adresse et texte alternatif.
	$img = function ( $photo, $nom, $alt, $prefixe = 'image' ) {
		$id = schiesser_demo_image( $photo, $nom, $alt );
		return array(
			$prefixe . 'Id'  => $id,
			$prefixe . 'Url' => $id ? (string) wp_get_attachment_image_url( $id, 'large' ) : '',
			$prefixe . 'Alt' => $alt,
		);
	};
	// Plusieurs éléments du même type.
	$enfants = function ( $nom, $liste ) {
		$html = '';
		foreach ( $liste as $attrs ) {
			$html .= schiesser_bm( $nom, $attrs ) . "\n";
		}
		return $html;
	};
	$ligne = function ( $libelle, $auto = '', $valeur = '', $detail = '' ) {
		return schiesser_bm( 'schiesser/ligne', array_filter( array( 'libelle' => $libelle, 'auto' => $auto, 'valeur' => $valeur, 'detail' => $detail ) ) ) . "\n";
	};
	$q = function ( $question, $reponse ) {
		return schiesser_bm( 'schiesser/question', array( 'question' => $question, 'reponse' => $reponse ) ) . "\n";
	};
	$live = function ( $c1, $c2 ) {
		return schiesser_bm( 'schiesser/bandeau-live', array(
			'c1Libelle' => $c1[0], 'c1Valeur' => $c1[1], 'c1Detail' => $c1[2], 'c1Auto' => $c1[3],
			'c2Libelle' => $c2[0], 'c2Valeur' => $c2[1], 'c2Detail' => $c2[2], 'c2Auto' => $c2[3],
		) );
	};
	$sep = schiesser_bm( 'schiesser/separateur' );
	// Identifiants des produits (déjà importés), pour une sélection dans le catalogue.
	$ids_produits = function ( $noms ) {
		$ids = array();
		foreach ( $noms as $nom ) {
			$p = get_page_by_path( sanitize_title( $nom ), OBJECT, SCHIESSER_PRODUIT );
			if ( $p ) {
				$ids[] = $p->ID;
			}
		}
		return implode( ',', $ids );
	};

	$P = array(
		'lack'     => '1606313564200-e75d5e30476c',
		'truf'     => '1548907040-4baa42d10919',
		'pral'     => '1481391319762-47dff72954d9',
		'cake'     => '1565958011703-44f9829ba187',
		'marr'     => '1519915028121-7d3463d20b13',
		'coff'     => '1549007994-cb92caebd54b',
		'tabl'     => '1464195244916-405fa0a82545',
		'bisc'     => '1556910103-1c02745aae4d',
		'boutique' => '1509440159596-0249088772ff',
		'salon'    => '1445116572660-236099ec97a0',
		'facade'   => '1517244683847-7456b63c5969',
		'tables'   => '1600891964092-4316c288032e',
		'relais'   => '1587248720327-8eb72564be1e',
		'jour'     => '1551024506-0bccd828d307',
	);
	$tel   = schiesser_reglage( 'telephone' );
	$devis = schiesser_mailto( 'Demande de devis : cadeaux d’entreprise', array(
		'Entreprise :',
		'Nombre de coffrets et format (9, 16 ou 25 pièces) :',
		'Date de remise souhaitée :',
		'Message ou logo à faire figurer :',
		'Adresse de facturation :',
		'Nom et téléphone :',
	) );

	/* ================= Accueil ================= */
	$accueil = schiesser_demo_hero( array(
		'hauteur'      => 'accueil',
		'ariane'       => false,
		'sceau'        => true,
		'surtitre'     => 'Maison fondée à Bâle · depuis 1870',
		'titre'        => 'Le goût précis <br>d’une <em>confiserie à&nbsp;Bâle</em>',
		'texte'        => 'Läckerli, truffes et pralinés faits main au cœur du Marktplatz, et un salon de thé à l’étage.',
		'bouton1Texte' => 'Découvrir les créations',
		'bouton1Lien'  => '#creations',
		'bouton2Texte' => 'Nous rendre visite',
		'bouton2Lien'  => '#visiter',
	), $P['pral'], 'confiserie-a-bale-pralines', 'Pralinés et chocolats en vitrine à la Confiserie Schiesser, confiserie à Bâle' )
	. $b( 'schiesser/vitrine-jour' )
	. $b( 'schiesser/catalogue', array( 'numero' => '01', 'titre' => 'Les créations', 'note' => 'Le catalogue de la maison. Survolez une création pour la voir, cliquez pour sa fiche.', 'ancre' => 'creations',
		'produits' => $ids_produits( array( 'Läckerli de Bâle', 'Truffes au chocolat', 'Pralinés artisanaux', 'Gâteau sur commande', 'Marrons glacés', 'Coffret de chocolats' ) ) ) )
	. $b( 'schiesser/etages', array( 'numero' => '02', 'titre' => 'Deux maisons, une adresse', 'note' => 'La boutique au rez-de-chaussée, le salon à l’étage. On traverse l’une pour rejoindre l’autre.', 'fond' => 'alterne' ),
		$enfants( 'schiesser/etage', array(
			array( 'numero' => '0', 'surtitre' => 'Rez-de-chaussée', 'titre' => 'La boutique', 'texte' => 'Chocolats, pralinés et Läckerli à emporter, avec l’atelier qui travaille juste derrière le comptoir.', 'lienTexte' => 'Voir les produits', 'lienUrl' => $u( '/boutique/' ) ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La boutique et ses vitrines au rez-de-chaussée' ),
			array( 'numero' => '1', 'surtitre' => 'Premier étage', 'titre' => 'Le salon de thé', 'texte' => 'Café, thé et pâtisseries au salon, avec les fenêtres qui donnent directement sur la place.', 'lienTexte' => 'Monter à l’étage', 'lienUrl' => $u( '/salon-de-the/' ) ) + $img( $P['salon'], 'salon-de-the-bale', 'Le salon de thé au premier étage, fenêtres sur le Marktplatz' ),
		) ) )
	. $sep
	. $b( 'schiesser/savoir-faire', array( 'numero' => '03', 'titre' => 'Le geste, visible', 'note' => 'Rien n’est caché. Faites défiler : chaque étape se dévoile à son tour.', 'fond' => 'clair', 'ancre' => 'savoir' ),
		$enfants( 'schiesser/geste', array(
			array( 'titre' => 'La cuisson', 'texte' => 'Le miel et les amandes travaillés au cuivre, comme au premier jour de la maison.' ) + $img( $P['bisc'], 'biscuits-aux-amandes', 'Cuisson du miel et des amandes dans l’atelier' ),
			array( 'titre' => 'Le façonnage', 'texte' => 'Chaque pièce est coupée, roulée ou trempée à la main, sans raccourci.' ) + $img( $P['relais'], 'transmission-atelier-confiserie', 'Façonnage à la main dans l’atelier' ),
			array( 'titre' => 'La finition', 'texte' => 'Glaçage, décor et contrôle. C’est la finition qui distingue une maison.' ) + $img( $P['tabl'], 'tablette-de-chocolat-artisanale', 'Finition et décor des chocolats' ),
			array( 'titre' => 'La vitrine', 'texte' => 'Le matin même, tout rejoint la vitrine et le salon du Marktplatz.' ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La vitrine de la boutique au Marktplatz' ),
		) ) )
	. $b( 'schiesser/frise', array( 'numero' => '04', 'titre' => 'La ligne du temps', 'note' => 'Faites défiler les époques. Glissez ou utilisez les flèches.', 'ancre' => 'histoire', 'affichage' => 'cartes', 'indication' => 'Glissez pour explorer →' ),
		$enfants( 'schiesser/date', array(
			array( 'annee' => '1870', 'titre' => 'La fondation', 'texte' => 'La confiserie ouvre au cœur de Bâle. Une adresse, un four, la précision du goût.' ) + $img( $P['facade'], 'confiserie-historique-bale', 'La confiserie à ses débuts' ),
			array( 'annee' => '1920', 'titre' => 'Le salon de thé', 'texte' => 'La maison ouvre son salon et devient un rendez-vous bâlois.' ) + $img( $P['salon'], 'salon-de-the-bale', 'Le salon de thé au premier étage' ),
			array( 'annee' => '1975', 'titre' => 'La transmission', 'texte' => 'Le savoir-faire passe d’une génération à la suivante.' ) + $img( $P['tables'], 'salon-de-the-marktplatz', 'Les tables du salon de thé' ),
			array( 'annee' => 'Aujourd’hui', 'titre' => 'Toujours au Marktplatz', 'texte' => 'Mêmes gestes, une nouvelle génération de gourmands. ' . $l( '/notre-histoire/', 'Lire notre histoire' ) . '.' ) + $img( $P['jour'], 'confiserie-schiesser-aujourdhui', 'La Confiserie Schiesser aujourd’hui' ),
		) ) )
	. $b( 'schiesser/adresse', array( 'numero' => '05', 'titre' => 'Nous trouver', 'note' => 'En plein Marktplatz, à deux pas du tram.', 'fond' => 'alterne', 'ancre' => 'visiter' ),
		$ligne( 'Adresse', 'adresse' )
		. $ligne( 'Horaires', 'horaires' )
		. $ligne( 'Groupes', '', 'Accueil sur réservation', 'Café, dégustations, coffrets' )
		. $ligne( 'Contact', 'contact' ) )
	. $b( 'schiesser/intro', array( 'numero' => '06', 'titre' => 'La maison en bref', 'lead' => 'Confiserie à Bâle depuis 1870, la maison Schiesser est installée sur le Marktplatz, au cœur de la vieille ville.' ),
		schiesser_bm_p( 'Au rez-de-chaussée, ' . $l( '/boutique/', 'la boutique' ) . ' et son atelier visible : Läckerli de Bâle, truffes, pralinés, tablettes et coffrets. À l’étage, ' . $l( '/salon-de-the/', 'le salon de thé' ) . ' sert chocolat chaud maison et pâtisseries du jour, face à la place.' )
		. schiesser_bm_p( 'La confiserie est ouverte [schiesser_horaires_phrase]. Et derrière chaque vitrine, plus de 150 ans d’' . $l( '/notre-histoire/', 'histoire' ) . '.' ) )
	. $b( 'schiesser/faq', array( 'numero' => '07', 'titre' => 'Questions fréquentes sur la confiserie', 'note' => 'L’essentiel avant de passer nous voir.', 'fond' => 'clair' ),
		$q( 'Où acheter des Läckerli à Bâle ?', 'À la Confiserie Schiesser, sur le Marktplatz, au cœur de la vieille ville. Nos ' . $l( $pr( 'Läckerli de Bâle' ), 'Läckerli de Bâle' ) . ' sont cuits, glacés et découpés à la main dans l’atelier de la maison, puis vendus en sachets de 100 g ou 250 g. Ils se gardent plusieurs semaines et voyagent très bien.' )
		. $q( 'Quels sont les horaires de la confiserie ?', 'La confiserie est ouverte [schiesser_horaires_phrase]. Les horaires peuvent varier les jours fériés : consultez la page ' . $l( '/nous-visiter/', 'Nous visiter' ) . ' ou appelez-nous en cas de doute.' )
		. $q( 'Peut-on commander un gâteau sur mesure ?', 'Oui. Nous réalisons des ' . $l( $pr( 'Gâteau sur commande' ), 'gâteaux sur commande' ) . ' de 6 à 40 personnes, avec quelques jours d’avance. Passez en boutique ou ' . $l( '/contact/', 'écrivez-nous' ) . ' pour choisir ensemble le parfum et le décor.' )
		. $q( 'Que rapporter de Bâle comme souvenir gourmand ?', 'Des Läckerli de Bâle, qui se conservent plusieurs semaines et voyagent sans risque, ou une tablette de chocolat de la maison, qui se garde plusieurs mois. Nous composons aussi des ' . $l( $pr( 'Coffret de chocolats' ), 'coffrets de chocolats' ) . ' de 9, 16 ou 25 pièces, emballés pour le voyage.' ) );

	/* ================= La boutique ================= */
	$boutique = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'surtitre'     => 'Rez-de-chaussée · Marktplatz',
		'titre'        => 'Chocolats à&nbsp;Bâle, <br><em>faits main sur place</em>',
		'texte'        => 'Nos chocolats à Bâle, pralinés, Läckerli et coffrets sont préparés dans l’atelier qui travaille juste derrière le comptoir.',
		'bouton1Texte' => 'Voir les produits',
		'bouton1Lien'  => '#catalogue',
		'bouton2Texte' => 'La boutique en images',
		'bouton2Lien'  => '#images',
	), $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'Vitrine de chocolats faits main à Bâle, boutique de la Confiserie Schiesser au Marktplatz' )
	. $live( array( 'Achat', 'Sur place, au comptoir', 'Boutique en ligne bientôt disponible', '' ), array( 'Commander', '', 'Gâteaux et coffrets sur demande', 'telephone' ) )
	. $b( 'schiesser/produits', array( 'numero' => '01', 'titre' => 'Les produits de la boutique', 'note' => 'Cliquez un produit pour ouvrir sa fiche détaillée.' ) )
	. $b( 'schiesser/galerie', array( 'numero' => '02', 'titre' => 'La boutique en images', 'note' => 'De la vitrine à l’atelier, tout se passe au même endroit.', 'fond' => 'sombre', 'marque' => true, 'ancre' => 'images' ),
		$enfants( 'schiesser/vue', array(
			array( 'titre' => 'La vitrine', 'texte' => 'Remontée chaque matin avant l’ouverture. Ce que vous y voyez est sorti du four ou de l’atelier quelques heures plus tôt.' ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La vitrine de la boutique, remontée chaque matin' ),
			array( 'titre' => 'Le comptoir', 'texte' => 'On y choisit à la pièce, on demande conseil, on fait composer une boîte. Rien n’est présélectionné à l’avance.' ) + $img( $P['pral'], 'confiserie-a-bale-pralines', 'Le comptoir et ses pralinés choisis à la pièce' ),
			array( 'titre' => 'L’atelier', 'texte' => 'Il travaille juste derrière la boutique. Les cuivres, les plaques et les gestes se devinent depuis la salle.' ) + $img( $P['bisc'], 'biscuits-aux-amandes', 'L’atelier de la confiserie' ),
			array( 'titre' => 'La finition', 'texte' => 'Glaçage, trempage, décor. C’est l’étape la plus lente, et celle qui distingue vraiment une maison d’une autre.' ) + $img( $P['tabl'], 'tablette-de-chocolat-artisanale', 'Finition des chocolats à la main' ),
			array( 'titre' => 'L’emballage', 'texte' => 'Chaque boîte est garnie et fermée à la main. Demandez un emballage renforcé si vous prenez l’avion ou le train.' ) + $img( $P['coff'], 'coffret-de-chocolats', 'Coffret de chocolats emballé à la main' ),
			array( 'titre' => 'La façade', 'texte' => 'Sur le Marktplatz, au cœur de la vieille ville. La devanture a changé de visage, la maison est restée.' ) + $img( $P['facade'], 'confiserie-historique-bale', 'La façade de la Confiserie Schiesser sur le Marktplatz' ),
		) ) )
	. $sep
	. $b( 'schiesser/infos', array( 'numero' => '03', 'titre' => 'Bon à savoir', 'note' => 'Conservation, transport et commandes particulières.', 'fond' => 'sable' ),
		$enfants( 'schiesser/info', array(
			array( 'icone' => 'conservation', 'titre' => 'Conservation', 'texte' => 'À l’abri de la chaleur et de la lumière. Les tablettes se gardent plusieurs mois, les pralinés deux à trois semaines, les truffes quelques jours.' ),
			array( 'icone' => 'avion', 'titre' => 'Voyager avec', 'texte' => 'Demandez un emballage renforcé si vous prenez l’avion ou le train. Les Läckerli et les coffrets supportent très bien le trajet.' ),
			array( 'icone' => 'gateau', 'titre' => 'Gâteaux sur commande', 'texte' => 'Anniversaires, mariages, fêtes de famille : notre ' . $l( $pr( 'Gâteau sur commande' ), 'gâteau sur commande' ) . ' se prépare pour 6 à 40 personnes, avec quelques jours d’avance.' ),
			array( 'icone' => 'coffret', 'titre' => 'Cadeaux d’entreprise', 'texte' => 'Des coffrets à votre message ou à votre logo, pour vos clients ou vos équipes : voir ' . $l( '/cadeaux-entreprise/', 'cadeaux d’entreprise' ) . '.' ),
			array( 'icone' => 'paiement', 'titre' => 'Paiement', 'texte' => 'Cartes, sans contact et espèces en francs suisses. Les euros sont acceptés en boutique.' ),
			array( 'icone' => 'horloge', 'titre' => 'Sortie du four', 'texte' => 'Nos ' . $l( $pr( 'Biscuits aux amandes' ), 'biscuits aux amandes' ) . ' et les pâtisseries rejoignent la vitrine le matin même. Venir tôt reste le meilleur moyen d’avoir le plus grand choix.' ),
		) ) )
	. $b( 'schiesser/cartes', array( 'numero' => '04', 'titre' => 'Passer commande', 'note' => 'Trois manières de repartir avec vos produits.', 'fond' => 'clair', 'ancre' => 'commander', 'modele' => 'commande' ),
		$enfants( 'schiesser/carte', array(
			array( 'titre' => 'En boutique', 'texte' => 'Poussez la porte au Marktplatz. Tout ce qui est en vitrine part immédiatement, sans commande préalable.', 'boutonTexte' => 'Comment venir', 'boutonLien' => $u( '/nous-visiter/' ) ),
			array( 'titre' => 'Par téléphone', 'texte' => 'Pour un gâteau, un grand coffret ou une commande d’entreprise, appelez-nous pendant les heures d’ouverture.', 'boutonTexte' => $tel, 'boutonLien' => schiesser_lien_tel() ),
			array( 'titre' => 'Par e-mail', 'texte' => 'Envoyez-nous votre composition ou votre demande détaillée, nous revenons vers vous sous un à deux jours ouvrés.', 'boutonTexte' => 'Nous écrire', 'boutonLien' => $u( '/contact/' ), 'sombre' => true ),
		) ) )
	. $b( 'schiesser/intro', array( 'numero' => '05', 'titre' => 'Nos chocolats, de l’atelier au comptoir', 'lead' => 'Tout ce qui est en vitrine part immédiatement, à la pièce, au poids ou en coffret.' ),
		schiesser_bm_p( 'Nos ' . $l( $pr( 'Truffes au chocolat' ), 'truffes au chocolat' ) . ' sont roulées chaque matin, nos ' . $l( $pr( 'Pralinés artisanaux' ), 'pralinés artisanaux' ) . ' trempés un à un et nos ' . $l( $pr( 'Tablette de chocolat artisanale' ), 'tablettes' ) . ' coulées dans l’atelier.' )
		. schiesser_bm_p( 'Pour offrir, le ' . $l( $pr( 'Coffret de chocolats' ), 'coffret de chocolats' ) . ' réunit pralinés, truffes et spécialités de saison, et nos ' . $l( $pr( 'Läckerli de Bâle' ), 'Läckerli de Bâle' ) . ' restent le souvenir le plus facile à rapporter.' ) )
	. $b( 'schiesser/faq', array( 'numero' => '06', 'titre' => 'Questions fréquentes sur nos chocolats', 'note' => 'Transport, commandes et allergies.', 'fond' => 'clair' ),
		$q( 'Les produits voyagent-ils bien ?', 'Nos coffrets sont conçus pour être emportés. Demandez un emballage renforcé si vous prenez l’avion ou le train : les Läckerli, les tablettes et les coffrets supportent très bien le trajet.' )
		. $q( 'Livrez-vous ou expédiez-vous les commandes ?', 'Les commandes se retirent en boutique, au Marktplatz. Écrivez-nous pour toute demande particulière, nous verrons ensemble ce qui est possible.' )
		. $q( 'Vos chocolats contiennent-ils de l’alcool ?', 'Certaines spécialités, oui : nos Läckerli de Bâle contiennent du kirsch. Demandez conseil au comptoir, l’équipe vous indique les pièces sans alcool.' )
		. $q( 'Vos produits conviennent-ils aux allergies ?', 'Nos ateliers travaillent les fruits à coque, le lait, le soja, le gluten et l’œuf : nous ne pouvons donc exclure les traces. La liste des ingrédients de chaque produit est disponible au comptoir.' ) );

	/* ================= Salon de thé ================= */
	$menu = function ( $nom, $photo, $fichier, $alt, $plats ) use ( $img ) {
		$html = '';
		foreach ( $plats as $p ) {
			$html .= schiesser_bm( 'schiesser/plat', array_filter( array( 'nom' => $p[0], 'prix' => $p[1], 'description' => $p[2], 'mention' => $p[3] ?? '' ) ) ) . "\n";
		}
		return schiesser_bm( 'schiesser/rubrique', array( 'nom' => $nom ) + $img( $photo, $fichier, $alt ), $html ) . "\n";
	};
	$salon = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'filtre'       => 'sepia-leger',
		'surtitre'     => 'Premier étage · Marktplatz',
		'titre'        => 'Un salon de thé à&nbsp;Bâle, <br><em>au-dessus de la place</em>',
		'texte'        => 'On traverse la boutique, on monte l’escalier, et le bruit du Marktplatz s’éloigne. Ici, le temps ralentit.',
		'bouton1Texte' => 'Voir la carte',
		'bouton1Lien'  => '#carte',
		'bouton2Texte' => 'Nous rejoindre',
		'bouton2Lien'  => '#venir',
	), $P['salon'], 'salon-de-the-bale', 'Salon de thé de la Confiserie Schiesser, au premier étage' )
	. $live( array( 'Le salon', 'Premier étage', 'Accès par la boutique', '' ), array( 'Réserver', '', 'Conseillé dès six personnes', 'telephone' ) )
	. $b( 'schiesser/intro', array( 'numero' => '01', 'titre' => 'Monter à l’étage', 'note' => 'Quelques marches séparent la boutique du salon. Elles font toute la différence.', 'ancre' => 'salon', 'suite' => true, 'lead' => 'Un lieu que l’on ne découvre qu’en poussant la porte, puis en levant les yeux.' ),
		schiesser_bm_p( 'Notre salon de thé à Bâle occupe le premier étage de la Confiserie Schiesser. On y accède en traversant la boutique, entre les vitrines et l’odeur de l’atelier tout proche.' )
		. schiesser_bm_p( 'En haut, tout change. Les fenêtres donnent sur le Marktplatz, et l’on vient y passer une heure plutôt que quelques minutes. Beaucoup l’appellent simplement le Tea Room.' ) )
	. $b( 'schiesser/ascension', array(),
		$enfants( 'schiesser/palier', array(
			array( 'niveau' => '0', 'libelle' => 'Boutique', 'surtitre' => 'Rez-de-chaussée', 'titre' => 'On traverse la boutique', 'texte' => 'Les vitrines, le comptoir, l’atelier qui travaille juste derrière. Tout le monde passe par là, et c’est voulu.' ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La boutique au rez-de-chaussée' ),
			array( 'niveau' => '½', 'libelle' => 'Escalier', 'surtitre' => 'L’escalier', 'titre' => 'On monte l’escalier', 'texte' => 'Quelques marches de bois, une rampe polie par les mains. Le bruit de la place s’atténue à mesure que l’on grimpe.' ) + $img( $P['facade'], 'confiserie-historique-bale', 'L’escalier vers le salon de thé' ),
			array( 'niveau' => '1', 'libelle' => 'Salon', 'surtitre' => 'Premier étage', 'titre' => 'On s’installe', 'texte' => 'Une table près de la fenêtre, la place en contrebas, une tasse qui arrive. Le reste peut attendre.' ) + $img( $P['salon'], 'salon-de-the-bale', 'Le salon de thé au premier étage' ),
		) ) )
	. $b( 'schiesser/encart', array( 'numero' => '02', 'titre' => 'La doyenne', 'note' => 'Un salon qui sert le café depuis plus d’un siècle.', 'fond' => 'sombre', 'surtitre' => 'Une singularité suisse', 'encartTitre' => 'Le plus ancien café de Suisse', 'sousTitre' => 'Toujours au premier étage, au-dessus de la boutique', 'texte' => 'Le salon a ouvert avec la maison et accueille ses habitués depuis. Les tables, les fenêtres et l’escalier sont restés à leur place, et l’on y sert le café comme au premier jour.', 'mention' => 'Mention à confirmer avec la maison avant publication.' ) + $img( $P['salon'], 'salon-de-the-bale', 'Le salon historique au premier étage de la Confiserie Schiesser' ),
		$enfants( 'schiesser/chiffre', array(
			array( 'valeur' => '1870', 'libelle' => 'Ouverture de la maison au Marktplatz' ),
			array( 'valeur' => '1920', 'libelle' => 'Le salon prend sa forme actuelle à l’étage' ),
			array( 'valeur' => 'Auj.', 'libelle' => 'Des générations plus tard, les habitués sont toujours là' ),
		) ) )
	. $b( 'schiesser/carte-salon', array( 'numero' => '03', 'titre' => 'La carte du salon de thé', 'note' => 'Ce qui se déguste à l’étage, assis, sans se presser.', 'fond' => 'alterne', 'ancre' => 'carte', 'mention' => 'Carte susceptible d’évoluer selon la saison et les arrivages.', 'sSurtitre' => 'La suggestion du jour', 'sTitre' => 'Chocolat chaud & part du jour', 'sTexte' => 'Notre chocolat chaud maison, accompagné de la pâtisserie sortie du four le matin même.', 'sPied' => 'Servi toute la journée', 'sPrix' => 'CHF 14.—' ) + $img( $P['cake'], 'gateau-sur-commande', 'Chocolat chaud et part du jour au salon de thé', 's' ),
		$menu( 'Cafés & chocolats', $P['bisc'], 'biscuits-aux-amandes', 'Cafés et chocolats chauds servis au salon', array(
			array( 'Café crème', 'CHF 5.20', 'Torréfaction sélectionnée pour la maison, servi en porcelaine.' ),
			array( 'Chocolat chaud maison', 'CHF 7.50', 'Préparé à partir de notre chocolat de couverture, à l’ancienne. En hiver, avec un ' . $l( $pr( 'Marrons glacés' ), 'marron glacé' ) . '.', 'Signature' ),
			array( 'Cappuccino', 'CHF 6.—', 'Mousse dense, servi avec un praliné de la boutique.' ),
			array( 'Espresso', 'CHF 4.50', 'Court et franc, comme il se doit.' ),
		) )
		. $menu( 'Thés & infusions', $P['salon'], 'salon-de-the-bale', 'Théières et service à thé du salon', array(
			array( 'Thé noir de saison', 'CHF 6.50', 'Sélection changeante, servie en théière.' ),
			array( 'Thé vert', 'CHF 6.50', 'Infusion douce, à l’eau frémissante.' ),
			array( 'Infusion maison', 'CHF 6.—', 'Mélange de plantes composé pour le salon.' ),
		) )
		. $menu( 'Pâtisseries', $P['cake'], 'gateau-sur-commande', 'Pâtisseries du jour présentées à l’étage', array(
			array( 'Part du jour', 'CHF 7.80', 'La pâtisserie sortie du four le matin même.', 'Chaque jour' ),
			array( 'Gâteau au chocolat', 'CHF 8.20', 'Dense et peu sucré, servi à température.' ),
			array( 'Tarte de saison', 'CHF 7.50', 'Selon les fruits du marché, juste en face.' ),
			array( 'Assortiment de pralinés', 'CHF 9.—', 'Trois ' . $l( $pr( 'Pralinés artisanaux' ), 'pralinés artisanaux' ) . ' choisis dans la vitrine du bas.' ),
		) )
		. $menu( 'Salé & glaces', $P['marr'], 'marrons-glaces', 'Assiettes salées et coupes glacées du salon', array(
			array( 'Petite salade', 'CHF 12.50', 'Feuilles de saison, vinaigrette maison.' ),
			array( 'Croque du salon', 'CHF 14.—', 'Pain de campagne, servi chaud.' ),
			array( 'Coupe glacée', 'CHF 10.50', 'Glaces maison, chantilly montée à la commande.', 'En saison' ),
			array( 'Boule de glace', 'CHF 3.80', 'À l’unité, parfums du jour.' ),
		) ) )
	. $b( 'schiesser/panneaux', array( 'numero' => '04', 'titre' => 'Le salon en détail', 'note' => 'Survolez chaque panneau pour l’ouvrir.' ),
		$enfants( 'schiesser/panneau', array(
			array( 'titre' => 'Les fenêtres', 'texte' => 'Elles donnent directement sur le Marktplatz. Les meilleures tables sont celles qui les longent.' ) + $img( $P['salon'], 'salon-de-the-bale', 'Les fenêtres du salon sur le Marktplatz' ),
			array( 'titre' => 'Les boiseries', 'texte' => 'Entretenues plutôt que remplacées, elles portent les marques de chaque génération, que raconte ' . $l( '/notre-histoire/', 'notre histoire' ) . '.' ) + $img( $P['facade'], 'confiserie-historique-bale', 'Les boiseries du salon' ),
			array( 'titre' => 'Le service', 'texte' => 'Porcelaine, plateaux et gestes appris sur le tas. Rien n’a été modernisé pour le plaisir de moderniser.' ) + $img( $P['tabl'], 'tablette-de-chocolat-artisanale', 'Le service au salon de thé' ),
			array( 'titre' => 'Les pâtisseries', 'texte' => 'Elles montent de l’atelier au fil de la journée. Ce qui est en vitrine en bas se retrouve dans les assiettes en haut.' ) + $img( $P['truf'], 'truffes-au-chocolat', 'Les pâtisseries et chocolats servis au salon' ),
		) ) )
	. $sep
	. $b( 'schiesser/moments', array( 'numero' => '05', 'titre' => 'À quelle heure venir', 'note' => 'Le salon change de visage au fil de la journée. Choisissez votre moment.', 'fond' => 'sombre', 'marque' => true ),
		$enfants( 'schiesser/moment', array(
			array( 'heure' => '07:30', 'libelle' => 'Le matin', 'titre' => 'Le premier café', 'sousTitre' => 'Quand tout sort du four', 'texte' => 'La ville s’installe, les étals du marché se montent en bas. En haut, le salon est presque vide et les pâtisseries arrivent encore tièdes.', 'conseil' => 'Un café crème et la part du jour' ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'Le salon au petit matin' ),
			array( 'heure' => '11:00', 'libelle' => 'La matinée', 'titre' => 'L’heure calme', 'sousTitre' => 'Le moment que nous préférons', 'texte' => 'Entre le petit-déjeuner et le déjeuner, les tables se libèrent et la lumière traverse les fenêtres. C’est le meilleur créneau pour s’attarder.', 'conseil' => 'Un thé de saison et une tarte' ) + $img( $P['salon'], 'salon-de-the-bale', 'Le salon en fin de matinée' ),
			array( 'heure' => '14:00', 'libelle' => 'L’après-midi', 'titre' => 'Le goûter', 'sousTitre' => 'Le salon au complet', 'texte' => 'C’est l’heure où le salon ressemble le plus à ce qu’il a toujours été : animé, joyeux, avec des plateaux qui circulent entre les tables.', 'conseil' => 'Un chocolat chaud et des pralinés' ) + $img( $P['cake'], 'gateau-sur-commande', 'Le goûter au salon de thé' ),
			array( 'heure' => '16:30', 'libelle' => 'La fin de journée', 'titre' => 'La dernière tasse', 'sousTitre' => 'Avant que la place se vide', 'texte' => 'La lumière baisse sur le Marktplatz, le service ralentit. On prend une dernière coupe glacée en regardant la place se dépeupler.', 'conseil' => 'Une coupe glacée et un espresso' ) + $img( $P['tabl'], 'tablette-de-chocolat-artisanale', 'Le salon en fin de journée' ),
		) ) )
	. $b( 'schiesser/venir', array( 'numero' => '06', 'titre' => 'Passer nous voir', 'note' => 'Le salon est accessible librement, sans réservation.', 'fond' => 'clair', 'ancre' => 'venir', 'encartTitre' => 'Une table vous attend', 'texte' => 'Poussez la porte de la boutique, traversez jusqu’à l’escalier et montez. Il y a presque toujours une place près d’une fenêtre.', 'b1Texte' => 'Nous écrire', 'b1Lien' => $u( '/contact/' ), 'b2Texte' => 'Comment venir', 'b2Lien' => $u( '/nous-visiter/' ) ) + $img( $P['salon'], 'salon-de-the-bale', 'Une table près de la fenêtre au salon de thé' ),
		$ligne( 'Adresse', 'adresse', '', 'Premier étage, accès par la boutique' )
		. $ligne( 'Horaires', 'horaires' )
		. $ligne( 'Réservation', '', 'Non nécessaire', 'Conseillée dès six personnes ou pour un événement privé' )
		. $ligne( 'Groupes', '', 'Sur demande', 'Dégustations et privatisation du salon' ) )
	. $b( 'schiesser/faq', array( 'numero' => '07', 'titre' => 'Questions sur notre salon de thé à Bâle', 'note' => 'Réservation, groupes et carte.' ),
		$q( 'Faut-il réserver pour le salon de thé ?', 'Non, l’accès se fait librement. Nous conseillons simplement la réservation à partir de six personnes.' )
		. $q( 'Peut-on privatiser le salon ?', 'C’est possible selon les périodes et les horaires. ' . $l( '/contact/', 'Contactez-nous' ) . ' avec votre date, le nombre de personnes et le type d’accueil souhaité.' )
		. $q( 'Le salon sert-il des plats salés ?', 'Oui, une courte carte salée accompagne les pâtisseries : petite salade de saison et croque du salon, servi chaud.' ) );

	/* ================= Notre histoire ================= */
	$histoire = schiesser_demo_hero( array(
		'hauteur'     => 'page',
		'filtre'      => 'sepia',
		'progression' => true,
		'surtitre'    => 'Depuis 1870 · Marktplatz',
		'titre'       => 'Une confiserie historique <br><em>à Bâle depuis 1870</em>',
		'texte'       => 'L’histoire d’une confiserie bâloise ouverte en 1870 sur le Marktplatz, et dont les gestes se transmettent de génération en génération.',
	), $P['facade'], 'confiserie-historique-bale', 'Façade historique de la Confiserie Schiesser sur le Marktplatz de Bâle' )
	. $b( 'schiesser/chiffres', array(),
		$enfants( 'schiesser/chiffre', array(
			array( 'valeur' => '1870', 'libelle' => 'Année de fondation' ),
			array( 'valeur' => '150+', 'libelle' => 'Années de savoir-faire' ),
			array( 'valeur' => '1', 'libelle' => 'Adresse au Marktplatz' ),
			array( 'valeur' => '100 %', 'libelle' => 'Fait main' ),
		) ) )
	. $b( 'schiesser/plaque', array( 'nombre' => '150', 'libelle' => 'ans de maison', 'dates' => '1870 · 2020', 'texte' => 'Un siècle et demi de savoir-faire, transmis de génération en génération depuis la première fournée.' ) )
	. $b( 'schiesser/origine', array( 'numero' => '01', 'titre' => 'L’origine', 'note' => 'Une seule histoire, racontée simplement. Le reste se goûte sur place.', 'legende' => 'Archive · la maison, Marktplatz', 'figure' => 'Fig. 01' ) + $img( $P['salon'], 'salon-de-the-bale', 'La maison Schiesser sur le Marktplatz' ),
		schiesser_bm_p( 'En 1870, une confiserie ouvre ses portes sur le Marktplatz, la plus belle place de Bâle. Plus de 150 ans plus tard, cette confiserie historique à Bâle y accueille toujours ses clients.' )
		. schiesser_bm_p( 'Le four est installé à l’arrière, la vitrine donne sur la place. Très vite, la maison se fait un nom sur quelques pièces seulement, travaillées à la main du matin au soir : du miel, des amandes, des épices, et le temps qu’il faut. C’est encore la base de nos ' . $l( $pr( 'Läckerli de Bâle' ), 'Läckerli de Bâle' ) . '.' )
		. $b( 'schiesser/exergue', array( 'texte' => 'Une adresse que l’on garde oblige à ne jamais décevoir ceux qui reviennent.', 'auteur' => 'La maison Schiesser' ) )
		. schiesser_bm_p( 'Un siècle et demi plus tard, la vitrine a changé de visage et les outils se sont affinés, mais la logique est restée la même. On cuit le matin, on vend le jour même, et l’on continue de faire devant les clients ce que d’autres font en coulisses.' ) )
	. $b( 'schiesser/frise', array( 'numero' => '02', 'titre' => 'La chronologie', 'note' => 'Choisissez une date, ou laissez défiler l’histoire.', 'ancre' => 'chronologie', 'affichage' => 'onglets', 'indication' => 'Cliquez une date pour explorer' ),
		$enfants( 'schiesser/date', array(
			array( 'annee' => '1870', 'libelle' => 'Fondation', 'titre' => 'La fondation', 'texte' => 'La confiserie ouvre ses portes au cœur de Bâle. Une adresse, un four, une ambition simple : la précision du goût.', 'retenir' => 'La maison s’installe sur le Marktplatz, où elle accueille toujours ses clients.' ) + $img( $P['facade'], 'confiserie-historique-bale', 'La confiserie en 1870' ),
			array( 'annee' => '1920', 'libelle' => 'Le salon', 'titre' => 'Le salon de thé', 'texte' => 'La maison ouvre son ' . $l( '/salon-de-the/', 'salon de thé' ) . ' et devient un rendez-vous bâlois. On y vient pour un café, on en repart avec une boîte.', 'retenir' => 'Le salon accueille alors les habitués du marché, dès le matin.' ) + $img( $P['salon'], 'salon-de-the-bale', 'Le salon de thé en 1920' ),
			array( 'annee' => '1948', 'libelle' => 'L’atelier', 'titre' => 'L’atelier', 'texte' => 'Les cuivres et les outils se transmettent. L’atelier prend sa forme définitive, à quelques pas de la vitrine.', 'retenir' => 'Certains ustensiles de cette époque servent encore aujourd’hui.' ) + $img( $P['bisc'], 'biscuits-aux-amandes', 'L’atelier en 1948' ),
			array( 'annee' => '1975', 'libelle' => 'Transmission', 'titre' => 'La transmission', 'texte' => 'Le savoir-faire passe d’une génération à la suivante. Les recettes restent, la main se transmet.', 'retenir' => 'Chaque génération forme la suivante directement à l’atelier.' ) + $img( $P['tables'], 'salon-de-the-marktplatz', 'La transmission du savoir-faire' ),
			array( 'annee' => '2005', 'libelle' => 'La vitrine', 'titre' => 'La vitrine', 'texte' => 'La boutique se réinvente sans se renier. Une présentation plus claire, les mêmes gestes derrière.', 'retenir' => 'La vitrine est remontée chaque matin, à la main.' ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La vitrine de la boutique' ),
			array( 'annee' => 'Aujourd’hui', 'libelle' => 'Marktplatz', 'titre' => 'Toujours au Marktplatz', 'texte' => 'Mêmes gestes, une nouvelle génération de gourmands. L’atelier reste visible depuis la boutique.', 'retenir' => 'Venez voir par vous-même : ' . $l( '/nous-visiter/', 'horaires et accès' ) . '.' ) + $img( $P['jour'], 'confiserie-schiesser-aujourdhui', 'La Confiserie Schiesser aujourd’hui' ),
		) ) )
	. $b( 'schiesser/archives', array( 'numero' => '03', 'titre' => 'Les archives', 'note' => 'Quelques pièces sorties des tiroirs de la maison. Cliquez pour agrandir.', 'fond' => 'clair', 'credit' => 'Photos d’illustration, à remplacer par le fonds photographique de la maison.' ),
		$enfants( 'schiesser/archive', array(
			array( 'titre' => 'La façade', 'meta' => '1870 · Photographie', 'description' => 'La devanture d’origine sur le Marktplatz, telle qu’elle apparaissait aux premières années de la maison.' ) + $img( $P['facade'], 'confiserie-historique-bale', 'La devanture d’origine sur le Marktplatz' ),
			array( 'titre' => 'Le personnel', 'meta' => '1895 · Photographie', 'description' => 'L’équipe de la maison devant la boutique.' ) + $img( $P['tables'], 'salon-de-the-marktplatz', 'L’équipe de la maison devant la boutique' ),
			array( 'titre' => 'Le salon', 'meta' => '1920 · Photographie', 'description' => 'L’ouverture du salon de thé transforme la confiserie en lieu de rendez-vous pour les Bâlois.' ) + $img( $P['salon'], 'salon-de-the-bale', 'L’ouverture du salon de thé au premier étage' ),
			array( 'titre' => 'La vitrine', 'meta' => '1932 · Photographie', 'description' => 'La présentation des pièces en vitrine.' ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La présentation des pièces en vitrine' ),
			array( 'titre' => 'Les cuivres', 'meta' => '1948 · Atelier', 'description' => 'Les bassines de cuivre de l’atelier, utilisées pour la cuisson du miel et des amandes.' ) + $img( $P['bisc'], 'biscuits-aux-amandes', 'Les bassines de cuivre de l’atelier' ),
			array( 'titre' => 'Le glaçage', 'meta' => '1955 · Atelier', 'description' => 'Le geste du glaçage, à la main.' ) + $img( $P['tabl'], 'tablette-de-chocolat-artisanale', 'Le geste du glaçage, à la main' ),
			array( 'titre' => 'Les emballages', 'meta' => '1961 · Document', 'description' => 'Les premiers coffrets de la maison.' ) + $img( $P['coff'], 'coffret-de-chocolats', 'Les premiers coffrets de la maison' ),
			array( 'titre' => 'La relève', 'meta' => '1975 · Atelier', 'description' => 'Le passage de témoin entre deux générations, à l’atelier, autour des mêmes gestes.' ) + $img( $P['relais'], 'transmission-atelier-confiserie', 'Le passage de témoin entre deux générations' ),
			array( 'titre' => 'Le comptoir', 'meta' => '1988 · Photographie', 'description' => 'Le comptoir et ses présentoirs.' ) + $img( $P['pral'], 'confiserie-a-bale-pralines', 'Le comptoir et ses présentoirs' ),
			array( 'titre' => 'Les pralinés', 'meta' => '1999 · Atelier', 'description' => 'Le trempage des pralinés, pièce par pièce.' ) + $img( $P['truf'], 'truffes-au-chocolat', 'Le trempage des pralinés, pièce par pièce' ),
			array( 'titre' => 'La place', 'meta' => '2010 · Photographie', 'description' => 'Le marché installé devant la maison.' ) + $img( $P['marr'], 'marrons-glaces', 'Le marché installé devant la maison' ),
			array( 'titre' => 'Les 150 ans', 'meta' => '2020 · Document', 'description' => 'L’anniversaire de la maison, cent cinquante ans.' ) + $img( $P['jour'], 'confiserie-schiesser-aujourdhui', 'L’anniversaire des cent cinquante ans de la maison' ),
		) ) )
	. $b( 'schiesser/avant-apres', array( 'numero' => '04', 'titre' => 'Hier et aujourd’hui', 'note' => 'La même maison, à un siècle d’écart. Faites glisser le curseur.' ),
		$enfants( 'schiesser/comparaison', array(
			array( 'onglet' => 'La façade', 'avantLibelle' => '1870', 'apresLibelle' => 'Aujourd’hui' ) + $img( $P['facade'], 'confiserie-historique-bale', 'La façade autrefois', 'avant' ) + $img( $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La façade aujourd’hui', 'apres' ),
			array( 'onglet' => 'La vitrine', 'avantLibelle' => '1920', 'apresLibelle' => 'Aujourd’hui' ) + $img( $P['salon'], 'salon-de-the-bale', 'La vitrine autrefois', 'avant' ) + $img( $P['pral'], 'confiserie-a-bale-pralines', 'La vitrine aujourd’hui', 'apres' ),
			array( 'onglet' => 'L’atelier', 'avantLibelle' => '1948', 'apresLibelle' => 'Aujourd’hui' ) + $img( $P['tables'], 'salon-de-the-marktplatz', 'L’atelier autrefois', 'avant' ) + $img( $P['bisc'], 'biscuits-aux-amandes', 'L’atelier aujourd’hui', 'apres' ),
		) ) )
	. $sep
	. $b( 'schiesser/cartes', array( 'numero' => '05', 'titre' => 'Ce qui n’a pas changé', 'note' => 'Trois principes, tenus depuis la première fournée.', 'modele' => 'principes', 'fond' => 'papier' ),
		$enfants( 'schiesser/carte', array(
			array( 'titre' => 'Le fait main', 'texte' => 'Découper, rouler, tremper, décorer : les gestes qui font la différence restent faits à la main, comme au premier jour.' ),
			array( 'titre' => 'Le jour même', 'texte' => 'On cuit le matin, on vend dans la journée. Ce qui n’est pas assez bon ne rejoint pas la vitrine.' ),
			array( 'titre' => 'À vue', 'texte' => 'L’atelier reste visible depuis la boutique. Ce que nous faisons, nous le faisons devant vous.' ),
		) ) )
	. $b( 'schiesser/appel', array( 'surtitre' => 'La suite se passe sur place', 'titre' => 'Venez écrire le prochain chapitre avec nous', 'texte' => 'La meilleure façon de comprendre cette maison reste encore de pousser la porte, au Marktplatz.', 'b1Texte' => 'Nous rendre visite', 'b1Lien' => $u( '/nous-visiter/' ), 'b2Texte' => 'Voir le savoir-faire', 'b2Lien' => $u( '/#savoir' ) ) );

	/* ================= Nous visiter ================= */
	$trajet = function ( $attrs, $etapes ) {
		$html = '';
		foreach ( $etapes as $e ) {
			$html .= schiesser_bm( 'schiesser/etape', array_filter( array( 'icone' => $e[0], 'titre' => $e[1], 'texte' => $e[2], 'duree' => $e[3] ?? '' ) ) ) . "\n";
		}
		return schiesser_bm( 'schiesser/trajet', $attrs, $html ) . "\n";
	};
	$visiter = schiesser_demo_hero( array(
		'hauteur'  => 'compacte',
		'surtitre' => 'Marktplatz · Bâle',
		'titre'    => 'Une confiserie au Marktplatz, <br><em>au centre de tout</em>',
		'texte'    => 'Notre confiserie au Marktplatz vous accueille à deux pas du tram et de l’hôtel de ville. Voici quand nous rendre visite, et comment nous rejoindre.',
	), $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'La boutique de la Confiserie Schiesser au Marktplatz' )
	. $live( array( 'Aujourd’hui', '', '', 'aujourdhui' ), array( 'Nous joindre', '', '', 'contact' ) )
	. $b( 'schiesser/plan-horaires', array( 'numero' => '01', 'titre' => 'Où nous trouver', 'note' => 'Sur le Marktplatz, côté hôtel de ville. Carte et horaires en un coup d’œil.', 'ancre' => 'horaires' ) )
	. $b( 'schiesser/affluence', array( 'numero' => '02', 'titre' => 'Le meilleur moment', 'note' => 'Affluence habituelle en boutique. Choisissez un jour.', 'fond' => 'sable' ) )
	. $b( 'schiesser/trajets', array( 'numero' => '03', 'titre' => 'Composez votre trajet', 'note' => 'Dites-nous d’où vous partez, nous traçons le chemin étape par étape.', 'fond' => 'sombre', 'marque' => true, 'ancre' => 'acces' ),
		$trajet( array( 'icone' => 'train', 'depart' => 'Gare Basel SBB', 'detail' => 'La gare principale (CFF)', 'titre' => 'Depuis la gare Basel SBB', 'sousTitre' => 'Le trajet le plus direct pour arriver en ville.', 'duree' => '12', 'changements' => '0', 'marche' => '4', 'astuce' => 'Horaires des trams sur <a href="https://www.bvb.ch/fr/">bvb.ch</a>. Beaucoup d’hôtels bâlois offrent la carte de transport à leurs clients : pensez à la demander à la réception.' ), array(
			array( 'train', 'Basel SBB', 'Sortez côté centre-ville, les quais de tram sont juste devant la gare.' ),
			array( 'tram', 'Tram direction centre', 'Montez dans un tram desservant le Marktplatz. Les écrans en tête de quai indiquent la destination.', '≈ 8 min' ),
			array( 'marche', 'Arrêt Marktplatz', 'Descendez sur la place. Notre vitrine se trouve côté hôtel de ville.', '≈ 1 min' ),
			array( 'boutique', 'Confiserie Schiesser', 'Vous y êtes. Poussez la porte, la vitrine vient d’être remontée.' ),
		) )
		. $trajet( array( 'icone' => 'avion', 'depart' => 'EuroAirport', 'detail' => 'Aéroport de Bâle-Mulhouse', 'titre' => 'Depuis l’EuroAirport', 'sousTitre' => 'Bus puis tram, sans quitter le centre des yeux.', 'duree' => '40', 'changements' => '1', 'marche' => '5', 'astuce' => 'Prenez bien la sortie suisse de l’aéroport, la sortie française mène de l’autre côté de la frontière.' ), array(
			array( 'avion', 'EuroAirport', 'Sortez côté suisse du terminal et rejoignez les quais de bus.' ),
			array( 'bus', 'Bus vers le centre', 'Prenez le bus reliant l’aéroport à la gare Basel SBB.', '≈ 20 min' ),
			array( 'tram', 'Basel SBB, puis Marktplatz', 'Changez pour un tram desservant le Marktplatz.', '≈ 10 min' ),
			array( 'boutique', 'Confiserie Schiesser', 'La boutique donne directement sur la place.' ),
		) )
		. $trajet( array( 'icone' => 'marche', 'depart' => 'La vieille ville', 'detail' => 'Vous êtes déjà en centre-ville', 'titre' => 'Depuis la vieille ville', 'sousTitre' => 'Quelques minutes de marche, tout est piéton.', 'duree' => '8', 'changements' => '0', 'marche' => '8', 'astuce' => 'Le matin, les étals du marché s’installent devant la boutique : c’est le meilleur moment pour flâner. Idées de promenade sur <a href="https://www.basel.com/fr">basel.com</a>.' ), array(
			array( 'marche', 'Vieille ville', 'Depuis les ruelles du centre, rejoignez la Freie Strasse.' ),
			array( 'marche', 'Freie Strasse', 'Remontez la rue commerçante en direction du nord.', '≈ 6 min' ),
			array( 'boutique', 'Marktplatz', 'La rue débouche sur la place. Nous sommes sur votre gauche.', '≈ 2 min' ),
		) )
		. $trajet( array( 'icone' => 'voiture', 'depart' => 'En voiture', 'detail' => 'Depuis l’autoroute', 'titre' => 'En voiture', 'sousTitre' => 'Le Marktplatz est piéton, on se gare aux abords.', 'duree' => '20', 'changements' => '0', 'marche' => '6', 'astuce' => 'La circulation est restreinte autour de la place. Si vous le pouvez, le tram reste plus simple et plus rapide.' ), array(
			array( 'voiture', 'Sortie autoroute', 'Suivez la direction du centre-ville de Bâle.' ),
			array( 'voiture', 'Parking du centre', 'Garez-vous dans l’un des parkings couverts proches du centre.', '≈ 15 min' ),
			array( 'marche', 'À pied jusqu’à la place', 'Rejoignez le Marktplatz par les rues piétonnes.', '≈ 6 min' ),
			array( 'boutique', 'Confiserie Schiesser', 'Vous débouchez directement face à la boutique.' ),
		) ) )
	. $b( 'schiesser/infos', array( 'numero' => '04', 'titre' => 'Bon à savoir', 'note' => 'Les informations pratiques avant de pousser la porte.', 'fond' => 'sable' ),
		$enfants( 'schiesser/info', array(
			array( 'icone' => 'paiement', 'titre' => 'Paiement', 'texte' => 'Cartes, sans contact et espèces en francs suisses. Les euros sont acceptés en boutique.' ),
			array( 'icone' => 'langues', 'titre' => 'Langues', 'texte' => 'Nous vous accueillons en allemand, français et anglais.' ),
			array( 'icone' => 'accessibilite', 'titre' => 'Accessibilité', 'texte' => 'La boutique est de plain-pied depuis la place. Le salon de thé se trouve au premier étage, accessible par un escalier. Prévenez-nous pour un accueil facilité.' ),
			array( 'icone' => 'emporter', 'titre' => 'À emporter', 'texte' => 'Tous nos produits se prennent à l’emporter, avec un emballage adapté au voyage.' ),
			array( 'icone' => 'coffret', 'titre' => 'Coffrets cadeaux', 'texte' => 'Notre ' . $l( $pr( 'Coffret de chocolats' ), 'coffret de chocolats' ) . ' est composé à la main sur place, avec un emballage soigné pour offrir.' ),
			array( 'icone' => 'repere', 'titre' => 'Zone piétonne', 'texte' => 'Le Marktplatz est piéton : l’accès se fait à pied depuis les rues voisines ou par le tram.' ),
		) ) )
	. $sep
	. $b( 'schiesser/devanture', array( 'numero' => '05', 'titre' => 'Reconnaître la maison', 'note' => 'Voici ce que vous cherchez en arrivant sur la place.', 'fond' => 'clair', 'surtitre' => 'Marktplatz · côté hôtel de ville', 'encartTitre' => 'La devanture', 'texte' => 'Les vitrines donnent directement sur la place. L’entrée se fait par la boutique, et l’escalier vers le salon se trouve au fond.' ) + $img( $P['facade'], 'confiserie-historique-bale', 'La devanture de la Confiserie Schiesser sur le Marktplatz à Bâle' ) )
	. $b( 'schiesser/faq', array( 'numero' => '06', 'titre' => 'Questions pratiques avant votre visite', 'note' => 'L’essentiel avant votre venue.' ),
		$q( 'Y a-t-il un parking à proximité ?', 'Le Marktplatz est en zone piétonne. Les parkings couverts du centre restent à quelques minutes à pied.' )
		. $q( 'Peut-on payer en euros ?', 'Oui, les euros sont acceptés en boutique. Nous acceptons aussi les cartes, le paiement sans contact et les espèces en francs suisses.' )
		. $q( 'Où déguster sur place ?', 'Au ' . $l( '/salon-de-the/', 'salon de thé' ) . ' du premier étage, ouvert aux horaires de la confiserie : chocolat chaud maison, pâtisseries du jour et petite carte salée.' )
		. $q( 'Les produits voyagent-ils bien ?', 'Nos coffrets sont conçus pour être emportés. Demandez un emballage renforcé si vous prenez l’avion ou le train.' )
		. $q( 'Que visiter autour du Marktplatz ?', 'L’hôtel de ville est juste en face, et la vieille ville commence au pied de la place. L’office du tourisme propose ses idées de promenade sur <a href="https://www.basel.com/fr">basel.com</a>.' ) );

	/* ================= Contact ================= */
	$contact = schiesser_demo_hero( array(
		'hauteur'  => 'compacte',
		'surtitre' => 'Une question, une commande, un projet',
		'titre'    => 'Contacter la <br><em>Confiserie Schiesser</em>',
		'texte'    => 'Un mot, un appel ou une visite au Marktplatz : nous répondons à chaque demande.',
	), $P['boutique'], 'boutique-confiserie-marktplatz-bale', 'Le comptoir de la boutique de la Confiserie Schiesser' )
	. $live( array( 'Par téléphone', '', 'Aux heures d’ouverture', 'telephone' ), array( 'Par e-mail', '', 'Réponse sous 1 à 2 jours ouvrés', 'email' ) )
	. $b( 'schiesser/formulaire', array( 'numero' => '01', 'titre' => 'Écrivez-nous', 'note' => 'Un message simple, nous vous répondons sous un à deux jours ouvrés.', 'ancre' => 'ecrire' ),
		$ligne( 'Par téléphone', 'telephone', '', 'Aux heures d’ouverture, le plus direct pour une urgence.' )
		. $ligne( 'Par e-mail', 'email', '', 'Réponse sous un à deux jours ouvrés.' )
		. $ligne( 'En boutique', 'adresse-ligne', '', 'Le plus simple reste encore de pousser la porte.' ) )
	. $b( 'schiesser/fiche-contact', array( 'numero' => '02', 'titre' => 'Nous joindre', 'note' => 'Une seule adresse, une seule équipe, pour la boutique comme pour le salon.', 'fond' => 'sable', 'surtitre' => 'Marktplatz · Bâle', 'encartTitre' => 'Confiserie Schiesser', 'texte' => 'La boutique au rez-de-chaussée, le salon de thé à l’étage. Pour contacter la Confiserie Schiesser, une commande, une question ou une réservation de groupe : écrivez-nous ou appelez-nous, l’équipe de la maison vous répond.' ),
		$ligne( 'E-mail', 'email' )
		. $ligne( 'Téléphone', 'telephone' )
		. $ligne( 'Adresse', 'adresse-ligne' )
		. $ligne( 'Pour', '', 'Commandes, gâteaux, coffrets, groupes, presse' ) )
	. $b( 'schiesser/faq', array( 'numero' => '03', 'titre' => 'Avant de nous écrire', 'note' => 'La réponse s’y trouve peut-être déjà.', 'fond' => 'clair' ),
		$q( 'Sous quel délai répondez-vous ?', 'Comptez un à deux jours ouvrés par e-mail. Pour une demande urgente, mieux vaut nous appeler pendant les heures d’ouverture.' )
		. $q( 'Combien de temps à l’avance commander un gâteau ?', 'Quelques jours suffisent pour la plupart des pièces, davantage pour une commande importante ou une date de forte affluence. Tout est détaillé sur la page ' . $l( $pr( 'Gâteau sur commande' ), 'gâteau sur commande' ) . '.' )
		. $q( 'Peut-on réserver une table au salon de thé ?', 'Le salon est accessible librement. La réservation est conseillée à partir de six personnes ou pour un événement privé. Plus d’informations sur la page du ' . $l( '/salon-de-the/', 'salon de thé' ) . '.' )
		. $q( 'Livrez-vous ou expédiez-vous les commandes ?', 'Les commandes se retirent en boutique, au Marktplatz. Écrivez-nous pour toute demande particulière, nous verrons ensemble ce qui est possible.' )
		. $q( 'Proposez-vous des cadeaux d’entreprise ?', 'Oui, nous composons des coffrets de chocolats personnalisés pour vos clients ou vos équipes, avec votre message ou votre logo. Voir la page ' . $l( '/cadeaux-entreprise/', 'cadeaux d’entreprise' ) . '.' )
		. $q( 'Vos produits conviennent-ils aux allergies ?', 'Nos ateliers travaillent les fruits à coque, le lait, le soja, le gluten et l’œuf : nous ne pouvons donc exclure les traces. Indiquez-nous vos contraintes, nous vous orienterons vers ce qui convient le mieux.' )
		. $q( 'Où en est la boutique en ligne ?', 'Elle arrive prochainement. En attendant, tout se commande par téléphone ou par e-mail, et se retire en boutique au Marktplatz.' ) )
	. $b( 'schiesser/appel', array( 'surtitre' => 'Le plus simple reste encore', 'titre' => 'De pousser la porte, au Marktplatz', 'texte' => 'Nous sommes là du matin au soir, la vitrine est remontée chaque jour et le salon vous attend à l’étage.', 'b1Texte' => 'Comment nous rejoindre', 'b1Lien' => $u( '/nous-visiter/' ), 'b2Texte' => 'Nous appeler', 'b2Lien' => schiesser_lien_tel() ) );

	/* ================= Cadeaux d'entreprise ================= */
	$entreprise = schiesser_demo_hero( array(
		'hauteur'      => 'compacte',
		'surtitre'     => 'Entreprises · clients et équipes',
		'titre'        => 'Cadeaux d’entreprise <br><em>faits main à&nbsp;Bâle</em>',
		'texte'        => 'Remerciez vos clients et vos équipes avec des coffrets composés à la main dans notre atelier du Marktplatz, depuis 1870.',
		'bouton1Texte' => 'Demander un devis',
		'bouton1Lien'  => $devis,
		'bouton2Texte' => 'Appeler',
		'bouton2Lien'  => schiesser_lien_tel(),
	), $P['coff'], 'cadeaux-entreprise-bale-coffrets', 'Coffrets de chocolats emballés pour des cadeaux d’entreprise à Bâle' )
	. $live( array( 'Devis', 'Sous un à deux jours ouvrés', 'Par e-mail ou par téléphone', '' ), array( 'Nous appeler', '', 'Aux heures d’ouverture', 'telephone' ) )
	. $b( 'schiesser/cartes', array( 'numero' => '01', 'titre' => 'Nos coffrets pour les entreprises', 'note' => 'Des cadeaux d’entreprise à Bâle qui voyagent bien et se gardent.', 'modele' => 'commande', 'fond' => 'papier' ),
		$enfants( 'schiesser/carte', array(
			array( 'titre' => 'Coffret de chocolats', 'texte' => '9, 16 ou 25 pièces : pralinés, truffes et spécialités de saison, emballés pour offrir.', 'boutonTexte' => 'Voir le coffret', 'boutonLien' => $u( $pr( 'Coffret de chocolats' ) ) ),
			array( 'titre' => 'Läckerli de Bâle', 'texte' => 'La spécialité de la ville, en sachets : un cadeau qui voyage et se garde plusieurs semaines.', 'boutonTexte' => 'Voir les Läckerli', 'boutonLien' => $u( $pr( 'Läckerli de Bâle' ) ) ),
			array( 'titre' => 'Tablettes de chocolat', 'texte' => 'Une tablette de chocolat artisanale, nature ou aux éclats d’amandes, facile à glisser dans un colis.', 'boutonTexte' => 'Voir la tablette', 'boutonLien' => $u( $pr( 'Tablette de chocolat artisanale' ) ), 'sombre' => true ),
		) ) )
	. $b( 'schiesser/intro', array( 'numero' => '02', 'titre' => 'Votre message, votre logo', 'fond' => 'clair', 'lead' => 'Chaque coffret peut porter votre message ou votre logo : une attention personnelle, composée à la main dans nos vitrines.' ),
		schiesser_bm_p( 'Dites-nous ce que vous souhaitez faire figurer : nous vous proposons la présentation la plus adaptée à vos quantités et à votre date.' )
		. schiesser_bm_p( 'Offrir un coffret Schiesser, c’est aussi offrir un peu de l’' . $l( '/notre-histoire/', 'histoire de Bâle' ) . ', celle d’une confiserie fondée en 1870.' ) )
	. $b( 'schiesser/cartes', array( 'numero' => '03', 'titre' => 'Comment ça se passe', 'note' => 'Trois étapes, sans formulaire compliqué.', 'modele' => 'principes', 'fond' => 'papier' ),
		$enfants( 'schiesser/carte', array(
			array( 'titre' => 'Vous nous écrivez', 'texte' => 'Quantités, formats et date de remise souhaitée, par e-mail ou par téléphone.' ),
			array( 'titre' => 'Nous vous répondons', 'texte' => 'Sous un à deux jours ouvrés, avec une proposition et un prix.' ),
			array( 'titre' => 'Nous préparons', 'texte' => 'Vos coffrets sont composés au plus près de la date de remise, puis retirés au Marktplatz.' ),
		) ) )
	. $b( 'schiesser/infos', array( 'numero' => '04', 'titre' => 'Pour un devis rapide', 'note' => 'Indiquez-nous simplement ces quelques informations.', 'fond' => 'sable' ),
		$enfants( 'schiesser/info', array(
			array( 'icone' => 'coffret', 'titre' => 'Quantités et formats', 'texte' => 'Le nombre de coffrets et le format souhaité : 9, 16 ou 25 pièces.' ),
			array( 'icone' => 'horloge', 'titre' => 'Date de remise', 'texte' => 'Pour les fêtes de fin d’année, écrivez-nous plusieurs semaines à l’avance.' ),
			array( 'icone' => 'langues', 'titre' => 'Message ou logo', 'texte' => 'Le texte ou le logo à faire figurer sur les coffrets.' ),
			array( 'icone' => 'paiement', 'titre' => 'Facturation', 'texte' => 'L’adresse de facturation et la personne à contacter.' ),
		) ) )
	. $b( 'schiesser/faq', array( 'numero' => '05', 'titre' => 'Questions sur les cadeaux d’entreprise', 'note' => 'Goûter, délais, transport.' ),
		$q( 'Peut-on goûter avant de commander ?', 'Oui : au ' . $l( '/salon-de-the/', 'salon de thé' ) . ', l’assortiment de trois pralinés donne un aperçu de la vitrine. Vous pouvez aussi passer en boutique et composer une petite boîte.' )
		. $q( 'Combien de temps à l’avance commander ?', 'Écrivez-nous dès que la date est connue. Quelques jours suffisent pour de petites quantités ; pour les fêtes de fin d’année, mieux vaut s’y prendre plusieurs semaines à l’avance.' )
		. $q( 'Les coffrets voyagent-ils bien ?', 'Oui, ils sont conçus pour être emportés, et nous prévoyons un emballage renforcé sur demande.' )
		. $q( 'Livrez-vous les commandes ?', 'Les commandes se retirent en boutique, au Marktplatz. Pour une demande particulière, écrivez-nous : nous verrons ensemble ce qui est possible.' ) )
	. $b( 'schiesser/appel', array( 'surtitre' => 'Parlons de vos coffrets', 'titre' => 'Un devis sous un à deux jours ouvrés', 'texte' => 'Dites-nous vos quantités et votre date : nous vous répondons avec une proposition et un prix.', 'b1Texte' => 'Demander un devis', 'b1Lien' => $devis, 'b2Texte' => 'Nous appeler', 'b2Lien' => schiesser_lien_tel() ) );

	/* ================= Mentions légales (brouillon à compléter) ================= */
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
 * @param bool $remplacer Met à jour le contenu des pages et produits existants (nouvelle version du thème).
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
		foreach ( array( 'prix', 'unite', 'badge', 'accroche', 'description', 'accord', 'origine' ) as $cle ) {
			update_post_meta( $id, '_s_' . $cle, $p[ $cle ] ?? '' );
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

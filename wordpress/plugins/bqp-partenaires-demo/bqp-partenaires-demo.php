<?php
/**
 * Plugin Name:       BQP Partenaires : démo
 * Description:       Crée et supprime des partenaires fictifs pour tester le design des blocs et des fiches. À retirer après les tests.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * License:           GPL-2.0-or-later
 */

/**
 * BQP Partenaires : partenaires de démonstration.
 *
 * Crée 17 partenaires fictifs répartis dans les cinq catégories, avec logos
 * générés, fiches détaillées complètes et quelques cas particuliers (un
 * partenaire sans logo, un sans fiche, un dans deux catégories), pour tester
 * le design des blocs et de la fenêtre. Un bouton supprime tout ce qui a été
 * créé, et rien d'autre.
 *
 * Nécessite le snippet BQP Partenaires 2.0 ou plus. À supprimer après les tests.
 *
 * Version : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. L'écran « Partenaires de démo »
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'bqpd_menu', 20 );
function bqpd_menu() {
	if ( ! post_type_exists( 'bqp_partenaire' ) ) {
		return;
	}

	add_submenu_page( 'edit.php?post_type=bqp_partenaire', 'Partenaires de démo', 'Partenaires de démo', 'manage_options', 'bqp-demo', 'bqpd_render_page' );
}

function bqpd_ids( $type = 'bqp_partenaire' ) {
	return get_posts(
		array(
			'post_type'   => $type,
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_bqp_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

function bqpd_render_page() {
	if ( ! function_exists( 'bqp_partner_data' ) ) {
		echo '<div class="wrap"><h1>Partenaires de démo</h1><p>Ce snippet a besoin de BQP Partenaires 2.0 ou plus, activé.</p></div>';
		return;
	}

	$count = count( bqpd_ids() );
	$msg   = isset( $_GET['bqpd'] ) ? sanitize_key( wp_unslash( $_GET['bqpd'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$done  = isset( $_GET['n'] ) ? absint( $_GET['n'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$cats  = array();

	foreach ( bqpd_partners() as $partner ) {
		foreach ( $partner['cats'] as $cat ) {
			$cats[ $cat ] = isset( $cats[ $cat ] ) ? $cats[ $cat ] + 1 : 1;
		}
	}

	echo '<div class="wrap bqp-help">';
	echo '<h1 class="bqp-help__title">Partenaires de démo</h1>';
	echo '<p class="bqp-help__intro">De faux partenaires pour tester le design des blocs et de la fiche détaillée. Tout ce qui est créé ici se supprime d\'un clic, sans toucher à vos vrais partenaires.</p>';

	if ( 'created' === $msg ) {
		echo '<div class="notice notice-success"><p>' . (int) $done . ' partenaires de démonstration créés. Ouvrez la page qui contient <code>[bqp_partenaires]</code> pour voir le résultat.</p></div>';
	} elseif ( 'deleted' === $msg ) {
		echo '<div class="notice notice-success"><p>Partenaires de démonstration supprimés, avec leurs logos.</p></div>';
	}

	echo '<h2 class="bqp-help__subtitle">Ce qui est créé</h2><ul class="bqp-help__list">';
	echo '<li><strong>' . count( bqpd_partners() ) . ' partenaires fictifs</strong> : ';
	$parts = array();
	foreach ( $cats as $slug => $n ) {
		$term    = get_term_by( 'slug', $slug, 'bqp_partenaire_cat' );
		$parts[] = $n . ' ' . esc_html( $term ? $term->name : $slug );
	}
	echo implode( ', ', $parts ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<li>Un <strong>logo</strong> généré pour chacun (sauf un, pour voir les initiales), et une <strong>fiche détaillée</strong> complète : partenaire depuis, localisation, domaines de collaboration, « Notre partenariat », présentation, site et LinkedIn</li>';
	echo '<li>Des cas particuliers : un partenaire sans fiche (il reste une carte simple), un partenaire présent dans deux blocs</li>';
	echo '<li>Les noms, textes et liens sont fictifs. Les liens mènent vers <code>example.org</code>, un domaine réservé aux exemples.</li>';
	echo '</ul>';

	echo '<h2 class="bqp-help__subtitle">' . ( $count ? (int) $count . ' partenaires de démo en place' : 'Aucun partenaire de démo pour le moment' ) . '</h2><p style="display:flex;gap:10px;flex-wrap:wrap;">';
	echo '<a class="button button-primary button-hero" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bqpd_create' ), 'bqpd_create' ) ) . '">' . ( $count ? 'Recréer les partenaires de démo' : 'Créer les partenaires de démo' ) . '</a>';
	if ( $count ) {
		echo '<a class="button button-hero" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bqpd_delete' ), 'bqpd_delete' ) ) . '" onclick="return window.confirm(\'Supprimer tous les partenaires de démonstration ?\');">Tout supprimer</a>';
	}
	echo '</p></div>';
}

add_action( 'admin_post_bqpd_create', 'bqpd_handle_create' );
function bqpd_handle_create() {
	check_admin_referer( 'bqpd_create' );

	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'bqp_partner_data' ) ) {
		wp_die( 'Action non autorisée.' );
	}

	bqpd_delete_all();
	$n = bqpd_create_all();

	wp_safe_redirect( admin_url( 'edit.php?post_type=bqp_partenaire&page=bqp-demo&bqpd=created&n=' . $n ) );
	exit;
}

add_action( 'admin_post_bqpd_delete', 'bqpd_handle_delete' );
function bqpd_handle_delete() {
	check_admin_referer( 'bqpd_delete' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}

	bqpd_delete_all();

	wp_safe_redirect( admin_url( 'edit.php?post_type=bqp_partenaire&page=bqp-demo&bqpd=deleted' ) );
	exit;
}

/* -------------------------------------------------------------------------
 * 2. Les partenaires fictifs
 * ---------------------------------------------------------------------- */

/**
 * Nom, catégories, couleur et forme du logo, puis la fiche.
 */
function bqpd_partners() {
	return array(
		/* Académiques */
		array(
			'name'   => 'Institut Corvelle d\'Études Stratégiques',
			'cats'   => array( 'academiques' ),
			'logo'   => array( '#1D3A6E', 'shield' ),
			'lieu'   => 'Paris, France',
			'depuis' => 2018,
			'dom'    => 'Jury du prix, Séminaires de recherche, Accueil de doctorants',
			'desc'   => 'Institut de recherche consacré aux questions de souveraineté industrielle et de sécurité économique.',
			'part'   => 'L\'Institut Corvelle siège au jury de la Bourse depuis sa création et ouvre chaque année son séminaire de recherche aux lauréats.',
			'pres'   => '<p>Fondé en 1994, l\'Institut Corvelle réunit une quarantaine de chercheurs en économie, en droit et en relations internationales. Ses travaux portent sur les chaînes de valeur stratégiques et la politique industrielle européenne.</p><h4>Chiffres clés</h4><ul><li>42 chercheurs permanents</li><li>15 doctorants accueillis chaque année</li><li>Une revue trimestrielle, les Cahiers Corvelle</li></ul>',
			'li'     => true,
		),
		array(
			'name'   => 'Université de Sainte-Albine',
			'cats'   => array( 'academiques' ),
			'logo'   => array( '#24527A', 'circle' ),
			'lieu'   => 'Lyon, France',
			'depuis' => 2020,
			'dom'    => 'Cours ouverts, Co-direction de thèses',
			'desc'   => 'Université pluridisciplinaire reconnue pour son master en intelligence économique.',
			'part'   => 'Les lauréats de la Bourse peuvent suivre gratuitement les cours du master Intelligence économique et souveraineté.',
			'pres'   => '<p>L\'Université de Sainte-Albine accueille 28 000 étudiants sur trois campus. Son département d\'économie industrielle collabore avec la Bourse sur l\'encadrement des travaux de recherche des lauréats.</p>',
			'li'     => false,
		),
		array(
			'name'   => 'École Arbrevin de Management Industriel',
			'cats'   => array( 'academiques' ),
			'logo'   => array( '#2E4A8C', 'square' ),
			'lieu'   => 'Grenoble, France',
			'depuis' => 2021,
			'dom'    => 'Ateliers lauréats, Études de cas',
			'desc'   => 'École de management tournée vers l\'industrie et les filières technologiques.',
			'part'   => 'Chaque printemps, l\'École Arbrevin accueille un atelier où les lauréats présentent leurs travaux à ses étudiants.',
			'pres'   => '<p>L\'École Arbrevin forme des cadres pour l\'industrie depuis 1962. Elle a développé une collection d\'études de cas sur la réindustrialisation, dont plusieurs s\'appuient sur les travaux de lauréats de la Bourse.</p>',
			'li'     => true,
		),
		array(
			'name'   => 'Centre Halvard de Recherche sur l\'Énergie',
			'cats'   => array( 'academiques', 'institutionnels' ),
			'logo'   => array( '#1E6B5C', 'hexagon' ),
			'lieu'   => 'Toulouse, France',
			'depuis' => 2022,
			'dom'    => 'Données énergétiques, Expertise, Colloques',
			'desc'   => 'Centre public de recherche sur la sécurité des approvisionnements énergétiques.',
			'part'   => 'Le Centre Halvard met ses bases de données à disposition des lauréats qui travaillent sur l\'énergie et co-organise un colloque annuel.',
			'pres'   => '<p>Établissement public rattaché à plusieurs ministères, le Centre Halvard étudie les dépendances énergétiques de la France et de l\'Europe. Il figure dans deux blocs : académique par ses travaux, institutionnel par son statut.</p>',
			'li'     => false,
		),

		/* Institutionnels */
		array(
			'name'   => 'Agence Morvane pour l\'Industrie',
			'cats'   => array( 'institutionnels' ),
			'logo'   => array( '#B4561C', 'diamond' ),
			'lieu'   => 'Rennes, France',
			'depuis' => 2019,
			'dom'    => 'Soutien financier, Mise en relation, Visites de sites',
			'desc'   => 'Agence régionale qui accompagne les projets industriels et les filières d\'avenir.',
			'part'   => 'L\'Agence Morvane finance une partie de la dotation du prix et organise pour les lauréats des visites de sites de production.',
			'pres'   => '<p>L\'Agence Morvane accompagne chaque année plus de 300 entreprises industrielles dans leurs projets d\'implantation, d\'innovation et d\'export.</p><ul><li>300 entreprises accompagnées par an</li><li>12 filières prioritaires</li></ul>',
			'li'     => true,
		),
		array(
			'name'   => 'Observatoire des Dépendances Stratégiques',
			'cats'   => array( 'institutionnels' ),
			'logo'   => array( '#9C4A16', 'circle' ),
			'lieu'   => 'Paris, France',
			'depuis' => 2017,
			'dom'    => 'Notes de conjoncture, Publications communes',
			'desc'   => 'Organisme public d\'analyse des vulnérabilités des chaînes d\'approvisionnement.',
			'part'   => 'L\'Observatoire publie chaque année une note commune avec la Bourse sur les dépendances industrielles françaises.',
			'pres'   => '<p>Créé pour éclairer la décision publique, l\'Observatoire recense les produits et composants pour lesquels la France dépend d\'un nombre réduit de fournisseurs étrangers.</p>',
			'li'     => false,
		),
		array(
			'name'   => 'Conseil des Filières d\'Avenir',
			'cats'   => array( 'institutionnels' ),
			'logo'   => null,
			'lieu'   => 'Strasbourg, France',
			'depuis' => 2023,
			'dom'    => 'Parrainage, Événements',
			'desc'   => 'Instance de concertation entre l\'État, les régions et les industriels.',
			'part'   => 'Le Conseil parraine la cérémonie de remise du prix et invite les lauréats à ses assises annuelles.',
			'pres'   => '<p>Le Conseil des Filières d\'Avenir réunit les représentants de dix-huit filières industrielles. Ce partenaire n\'a pas de logo : ses initiales s\'affichent à la place.</p>',
			'li'     => false,
		),

		/* Entreprises */
		array(
			'name'   => 'Aldoria Industries',
			'cats'   => array( 'entreprises' ),
			'logo'   => array( '#7A1022', 'square' ),
			'lieu'   => 'Lille, France',
			'depuis' => 2016,
			'dom'    => 'Mécénat, Accueil de lauréats, Jury du prix',
			'desc'   => 'Groupe industriel spécialisé dans les équipements de précision pour l\'aéronautique.',
			'part'   => 'Mécène historique de la Bourse, Aldoria Industries accueille chaque année un lauréat pour un stage de recherche de six mois.',
			'pres'   => '<p>Aldoria Industries emploie 4 200 personnes sur onze sites en France. Le groupe a fait le choix de maintenir l\'essentiel de sa production sur le territoire.</p><h4>En bref</h4><ul><li>4 200 salariés</li><li>11 sites industriels en France</li><li>Mécène de la Bourse depuis 2016</li></ul>',
			'li'     => true,
		),
		array(
			'name'   => 'Groupe Ferrandel',
			'cats'   => array( 'entreprises' ),
			'logo'   => array( '#3B3F46', 'hexagon' ),
			'lieu'   => 'Nantes, France',
			'depuis' => 2019,
			'dom'    => 'Mécénat, Études de terrain',
			'desc'   => 'Entreprise familiale de construction navale et de chaudronnerie industrielle.',
			'part'   => 'Le Groupe Ferrandel ouvre ses chantiers aux lauréats qui travaillent sur la filière navale et soutient la publication de leurs travaux.',
			'pres'   => '<p>Fondé en 1921, le Groupe Ferrandel est resté une entreprise familiale. Il conçoit et fabrique des navires de service et des équipements de chaudronnerie lourde.</p>',
			'li'     => false,
		),
		array(
			'name'   => 'Néréis Énergie',
			'cats'   => array( 'entreprises' ),
			'logo'   => array( '#1B7F8C', 'circle' ),
			'lieu'   => 'Marseille, France',
			'depuis' => 2021,
			'dom'    => 'Prix spécial énergie, Mentorat',
			'desc'   => 'Producteur indépendant d\'énergies marines et de stockage.',
			'part'   => 'Néréis Énergie dote un prix spécial consacré à la souveraineté énergétique et propose un mentorat aux lauréats.',
			'pres'   => '<p>Néréis Énergie développe des parcs hydroliens et des solutions de stockage d\'électricité en Méditerranée.</p>',
			'li'     => true,
		),
		array(
			'name'   => 'Valtec Composites',
			'cats'   => array( 'entreprises' ),
			'logo'   => array( '#5B6B2E', 'diamond' ),
			'lieu'   => '',
			'depuis' => 0,
			'dom'    => '',
			'desc'   => '',
			'part'   => '',
			'pres'   => '',
			'li'     => false,
		),

		/* Associations */
		array(
			'name'   => 'Cercle Aubrac pour la Souveraineté',
			'cats'   => array( 'associations' ),
			'logo'   => array( '#1F5561', 'shield' ),
			'lieu'   => 'Paris, France',
			'depuis' => 2018,
			'dom'    => 'Conférences, Débats, Diffusion des travaux',
			'desc'   => 'Cercle de réflexion qui réunit chefs d\'entreprise, chercheurs et hauts fonctionnaires.',
			'part'   => 'Le Cercle Aubrac invite chaque lauréat à présenter ses travaux lors d\'un dîner débat devant ses membres.',
			'pres'   => '<p>Le Cercle Aubrac organise une dizaine de conférences par an sur la souveraineté économique, l\'industrie et l\'énergie. Ses membres s\'engagent à titre personnel.</p>',
			'li'     => true,
		),
		array(
			'name'   => 'Les Ateliers de la Production',
			'cats'   => array( 'associations' ),
			'logo'   => array( '#2F6E6A', 'square' ),
			'lieu'   => 'Saint-Étienne, France',
			'depuis' => 2020,
			'dom'    => 'Témoignages, Visites, Réseau d\'entrepreneurs',
			'desc'   => 'Association d\'entrepreneurs qui défend les savoir-faire industriels régionaux.',
			'part'   => 'Les Ateliers mettent les lauréats en relation avec des chefs d\'entreprise prêts à témoigner pour leurs recherches.',
			'pres'   => '<p>L\'association fédère 180 petites et moyennes entreprises industrielles et organise chaque année les Journées de la production.</p>',
			'li'     => false,
		),
		array(
			'name'   => 'Club Horizon Industrie',
			'cats'   => array( 'associations' ),
			'logo'   => array( '#3D7A8A', 'circle' ),
			'lieu'   => 'Lyon, France',
			'depuis' => 2022,
			'dom'    => 'Jeunes professionnels, Mentorat',
			'desc'   => 'Réseau de jeunes ingénieurs et économistes engagés pour l\'industrie.',
			'part'   => 'Le Club Horizon relaie l\'appel à candidatures de la Bourse auprès de ses 900 membres.',
			'pres'   => '<p>Le Club Horizon Industrie rassemble des professionnels de moins de 35 ans autour de rencontres, de visites et de groupes de travail.</p>',
			'li'     => true,
		),

		/* Médias */
		array(
			'name'   => 'Le Fil Économique',
			'cats'   => array( 'medias' ),
			'logo'   => array( '#5A2A4F', 'square' ),
			'lieu'   => 'Paris, France',
			'depuis' => 2019,
			'dom'    => 'Tribunes, Entretiens, Annonce du palmarès',
			'desc'   => 'Quotidien en ligne consacré à l\'économie et à l\'industrie.',
			'part'   => 'Le Fil Économique publie chaque année les tribunes des lauréats et annonce le palmarès de la Bourse.',
			'pres'   => '<p>Le Fil Économique réunit une rédaction de trente journalistes et une lettre quotidienne lue par 60 000 abonnés.</p>',
			'li'     => true,
		),
		array(
			'name'   => 'Revue Altitudes',
			'cats'   => array( 'medias' ),
			'logo'   => array( '#6E3A63', 'circle' ),
			'lieu'   => 'Paris, France',
			'depuis' => 2021,
			'dom'    => 'Publication d\'articles de fond',
			'desc'   => 'Revue trimestrielle de géopolitique et de stratégie.',
			'part'   => 'La Revue Altitudes consacre chaque année un dossier aux travaux primés par la Bourse.',
			'pres'   => '<p>Fondée en 2008, la Revue Altitudes publie des analyses longues signées par des chercheurs, des diplomates et des industriels.</p>',
			'li'     => false,
		),
		array(
			'name'   => 'Onde Méridienne',
			'cats'   => array( 'medias' ),
			'logo'   => array( '#7D3C6B', 'hexagon' ),
			'lieu'   => 'Montpellier, France',
			'depuis' => 2023,
			'dom'    => 'Podcasts, Émissions',
			'desc'   => 'Radio régionale et studio de podcasts sur l\'économie des territoires.',
			'part'   => 'Onde Méridienne produit une série de podcasts où les lauréats racontent leurs recherches.',
			'pres'   => '<p>Onde Méridienne diffuse ses programmes sur la bande FM et en podcast. Sa série consacrée aux lauréats de la Bourse compte déjà douze épisodes.</p>',
			'li'     => false,
		),
	);
}

/* -------------------------------------------------------------------------
 * 3. Les logos : des logotypes vectoriels générés
 * ---------------------------------------------------------------------- */

function bqpd_initials( $name ) {
	$out  = '';
	$skip = array( 'le', 'la', 'les', 'de', 'des', 'du', 'pour', 'l', 'd' );
	foreach ( preg_split( '/[\s\'’-]+/u', $name ) as $word ) {
		if ( '' !== $word && ! in_array( mb_strtolower( $word ), $skip, true ) ) {
			$out .= mb_strtoupper( mb_substr( $word, 0, 1 ) );
		}
	}

	return mb_substr( $out ? $out : mb_substr( $name, 0, 2 ), 0, 2 );
}

function bqpd_logo_svg( $name, $color, $shape ) {
	$mark = array(
		'circle'  => '<circle cx="60" cy="70" r="44" fill="' . $color . '"/>',
		'square'  => '<rect x="16" y="26" width="88" height="88" rx="18" fill="' . $color . '"/>',
		'hexagon' => '<path d="M60 22l42 24v48l-42 24-42-24V46z" fill="' . $color . '"/>',
		'diamond' => '<path d="M60 20l50 50-50 50-50-50z" fill="' . $color . '"/>',
		'shield'  => '<path d="M60 20l42 14v34c0 26-18 42-42 52-24-10-42-26-42-52V34z" fill="' . $color . '"/>',
	);

	// Nom sur deux lignes au plus, coupé entre les mots.
	$words = explode( ' ', $name );
	$lines = array( '' );
	foreach ( $words as $word ) {
		$i = count( $lines ) - 1;
		if ( '' !== $lines[ $i ] && mb_strlen( $lines[ $i ] . ' ' . $word ) > 18 && $i < 1 ) {
			$lines[] = $word;
		} else {
			$lines[ $i ] = trim( $lines[ $i ] . ' ' . $word );
		}
	}

	$text = '';
	$y    = 1 === count( $lines ) ? 80 : 64;
	foreach ( $lines as $k => $line ) {
		$text .= '<text x="128" y="' . ( $y + $k * 30 ) . '" font-family="Georgia, \'Times New Roman\', serif" font-size="25" font-weight="700" fill="#1f1f1f">' . esc_html( $line ) . '</text>';
	}

	return '<svg xmlns="http://www.w3.org/2000/svg" width="420" height="140" viewBox="0 0 420 140">'
		. $mark[ $shape ]
		. '<text x="60" y="79" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="28" font-weight="700" fill="#fff" letter-spacing="1">' . esc_html( bqpd_initials( $name ) ) . '</text>'
		. $text
		. '<rect x="128" y="' . ( $y + ( count( $lines ) - 1 ) * 30 + 14 ) . '" width="46" height="3" fill="' . $color . '"/>'
		. '</svg>';
}

/**
 * Enregistre le logo dans la médiathèque. Les dimensions sont déclarées
 * pour que WordPress l'affiche comme une image.
 */
function bqpd_attach_logo( $name, $svg ) {
	// Écrit directement : la médiathèque refuse le SVG par défaut, et ce
	// fichier généré ici ne contient que des formes et du texte.
	$dir = wp_upload_dir();
	if ( ! empty( $dir['error'] ) || ! wp_mkdir_p( $dir['path'] ) ) {
		return 0;
	}

	$filename = wp_unique_filename( $dir['path'], 'demo-partenaire-' . sanitize_title( $name ) . '.svg' );
	$upload   = array( 'file' => trailingslashit( $dir['path'] ) . $filename );

	if ( false === file_put_contents( $upload['file'], $svg ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_title'     => 'Logo ' . $name . ' (démo)',
			'post_mime_type' => 'image/svg+xml',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}

	$file = _wp_relative_upload_path( $upload['file'] );
	$base = wp_basename( $upload['file'] );

	wp_update_attachment_metadata(
		$id,
		array(
			'width'  => 420,
			'height' => 140,
			'file'   => $file,
			'sizes'  => array(
				'full' => array( 'file' => $base, 'width' => 420, 'height' => 140, 'mime-type' => 'image/svg+xml' ),
			),
		)
	);
	update_post_meta( $id, '_bqp_demo', 1 );

	return (int) $id;
}

/* -------------------------------------------------------------------------
 * 4. Création et suppression : uniquement ce qui porte la marque « démo »
 * ---------------------------------------------------------------------- */

function bqpd_create_all() {
	$n = 0;

	// Les catégories doivent exister (créées par BQP Partenaires).
	if ( function_exists( 'bqp_maybe_seed_terms' ) ) {
		bqp_maybe_seed_terms();
	}

	foreach ( bqpd_partners() as $i => $partner ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'bqp_partenaire',
				'post_status' => 'publish',
				'post_title'  => $partner['name'],
				'menu_order'  => $i,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		$slug = sanitize_title( $partner['name'] );
		$meta = array(
			'_bqp_demo'         => 1,
			'_bqp_url'          => 'https://example.org/' . $slug,
			'_bqp_description'  => $partner['desc'],
			'_bqp_lieu'         => $partner['lieu'],
			'_bqp_depuis'       => $partner['depuis'],
			'_bqp_domaines'     => $partner['dom'],
			'_bqp_partenariat'  => $partner['part'],
			'_bqp_presentation' => $partner['pres'],
			'_bqp_linkedin'     => $partner['li'] ? 'https://example.org/linkedin/' . $slug : '',
		);

		if ( $partner['logo'] ) {
			$meta['_bqp_logo_id'] = bqpd_attach_logo( $partner['name'], bqpd_logo_svg( $partner['name'], $partner['logo'][0], $partner['logo'][1] ) );
		}

		foreach ( $meta as $key => $value ) {
			if ( '' !== (string) $value && 0 !== $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}

		$terms = array();
		foreach ( $partner['cats'] as $cat ) {
			$term = get_term_by( 'slug', $cat, 'bqp_partenaire_cat' );
			if ( $term ) {
				$terms[] = (int) $term->term_id;
			}
		}
		wp_set_object_terms( $post_id, $terms, 'bqp_partenaire_cat' );

		$n++;
	}

	return $n;
}

function bqpd_delete_all() {
	foreach ( bqpd_ids( array( 'bqp_partenaire', 'attachment' ) ) as $id ) {
		'attachment' === get_post_type( $id ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
	}
}

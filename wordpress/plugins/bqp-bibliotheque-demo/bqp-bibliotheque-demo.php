<?php
/**
 * Plugin Name:       BQP Bibliothèque : démo
 * Description:       Crée et supprime des documents fictifs pour tester le design de la bibliothèque. À retirer après les tests.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * License:           GPL-2.0-or-later
 */

/**
 * BQP Bibliothèque : documents de démonstration.
 *
 * Crée une soixantaine de faux documents répartis dans toute l'arborescence
 * du client (blocs 1 à 5), avec auteurs, lauréats, partenaires, couvertures,
 * PDF, vidéos et niveaux d'accès variés, pour tester le design. Un bouton
 * supprime tout ce qui a été créé, et rien d'autre.
 *
 * Nécessite le snippet BQP Bibliothèque 3.0 ou plus. À supprimer après les tests.
 *
 * Version : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. L'écran « Documents de démo »
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'bqbd_menu', 20 );
function bqbd_menu() {
	if ( ! post_type_exists( 'bqb_document' ) ) {
		return;
	}

	add_submenu_page( 'edit.php?post_type=bqb_document', 'Documents de démo', 'Documents de démo', 'manage_options', 'bqb-demo', 'bqbd_render_page' );
}

function bqbd_count() {
	$ids = get_posts(
		array(
			'post_type'   => 'bqb_document',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_bqb_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);

	return count( $ids );
}

function bqbd_render_page() {
	if ( ! function_exists( 'bqb_taxonomies' ) || ! function_exists( 'bqb_guess_nature' ) ) {
		echo '<div class="wrap"><h1>Documents de démo</h1><p>Ce snippet a besoin de BQP Bibliothèque 3.0 ou plus, activé.</p></div>';
		return;
	}

	$count = bqbd_count();
	$msg   = isset( $_GET['bqbd'] ) ? sanitize_key( wp_unslash( $_GET['bqbd'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$done  = isset( $_GET['n'] ) ? absint( $_GET['n'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

	$html  = '<div class="wrap bqb-wrap">';
	$html .= bqb_admin_banner( 'Documents de démo', 'De faux documents pour tester le design des blocs, du catalogue, de l\'annuaire et des fiches. Tout ce qui est créé ici se supprime d\'un clic.' );

	if ( 'created' === $msg ) {
		$html .= '<div class="bqb-notice">' . (int) $done . ' documents de démonstration créés. Ouvrez la page Bibliothèque pour voir le résultat.</div>';
	} elseif ( 'deleted' === $msg ) {
		$html .= '<div class="bqb-notice">Documents de démonstration supprimés, avec leurs personnes, organisations, images et PDF.</div>';
	}

	$html .= '<section class="bqb-card"><h2 class="bqb-card__title">Ce qui est créé</h2><ul style="margin:0 0 18px 18px;list-style:disc;line-height:1.8;">';
	$html .= '<li><strong>' . count( bqbd_documents() ) . ' documents</strong> : au moins un par élément des blocs 1 à 4 (1.1 Articles, 3.1 Cahiers…), plusieurs dans les rubriques à regroupements (2.2 Travaux primés par lauréat et par année, 4.2 par partenaire…)</li>';
	$html .= '<li>Des <strong>thèmes</strong> dans les cinq thématiques du bloc 5, secteurs et pays compris</li>';
	$html .= '<li><strong>' . count( bqbd_people() ) . ' personnes</strong> fictives (6 lauréats de 2021 à 2025, des experts, une représentante de partenaire) et <strong>' . count( bqbd_orgs() ) . ' organisations</strong> fictives</li>';
	$html .= '<li>Des années de 1968 à 2026, les quatre niveaux d\'accès, des <strong>couvertures</strong> générées, un <strong>PDF</strong> à télécharger, des <strong>vidéos</strong>, des liens externes, des mots-clés et des documents associés</li>';
	$html .= '<li>Une biographie provisoire pour Jean-Michel Quatrepoint si la sienne est vide, pour tester la page dédiée du bloc 1</li>';
	$html .= '</ul><p class="bqb-settings__hint" style="margin:0;">Les documents de démo sont marqués <strong>noindex</strong> et retirés des sitemaps : Google ne les indexe pas, même si le site est en ligne. Leurs textes indiquent qu\'il s\'agit d\'exemples fictifs.</p></section>';

	$html .= '<section class="bqb-card"><h2 class="bqb-card__title">' . ( $count ? (int) $count . ' documents de démo en place' : 'Aucun document de démo pour le moment' ) . '</h2><p style="display:flex;gap:10px;flex-wrap:wrap;margin:0;">';

	$html .= '<a class="button button-primary button-hero" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bqbd_create' ), 'bqbd_create' ) ) . '">' . ( $count ? 'Recréer les documents de démo' : 'Créer les documents de démo' ) . '</a>';

	if ( $count ) {
		$html .= '<a class="button button-hero" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bqbd_delete' ), 'bqbd_delete' ) ) . '" onclick="return window.confirm(\'Supprimer tous les documents de démonstration ?\');">Tout supprimer</a>';
	}

	$html .= '</p></section></div>';

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( 'admin_post_bqbd_create', 'bqbd_handle_create' );
function bqbd_handle_create() {
	check_admin_referer( 'bqbd_create' );

	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'bqb_guess_nature' ) ) {
		wp_die( 'Action non autorisée.' );
	}

	bqbd_delete_all();
	$n = bqbd_create_all();

	wp_safe_redirect( admin_url( 'edit.php?post_type=bqb_document&page=bqb-demo&bqbd=created&n=' . $n ) );
	exit;
}

add_action( 'admin_post_bqbd_delete', 'bqbd_handle_delete' );
function bqbd_handle_delete() {
	check_admin_referer( 'bqbd_delete' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}

	bqbd_delete_all();

	wp_safe_redirect( admin_url( 'edit.php?post_type=bqb_document&page=bqb-demo&bqbd=deleted' ) );
	exit;
}

/* -------------------------------------------------------------------------
 * 2. Les données fictives
 * ---------------------------------------------------------------------- */

/**
 * Personnes fictives : nom => rôles, année du prix, prix, biographie.
 */
function bqbd_people() {
	return array(
		'Camille Rostand'         => array( array( 'laureat', 'auteur' ), 2021, 'Grand Prix Jean-Michel Quatrepoint', 'Économiste, spécialiste des industries électroniques et des politiques technologiques européennes.' ),
		'Julien Marchetti'        => array( array( 'laureat', 'auteur' ), 2022, 'Bourse Jeunes Chercheurs', 'Docteur en génie chimique, il travaille sur la relocalisation des productions pharmaceutiques.' ),
		'Inès Haddad'             => array( array( 'laureat', 'auteur' ), 2023, 'Grand Prix Jean-Michel Quatrepoint', 'Géographe, elle étudie les ports, la logistique et la compétition entre façades maritimes.' ),
		'Mathieu Lefranc'         => array( array( 'laureat', 'auteur' ), 2024, 'Prix de l\'Essai et du Débat public', 'Haut fonctionnaire, auteur d\'essais sur l\'État actionnaire et la politique industrielle.' ),
		'Sophie Berthelot'        => array( array( 'laureat', 'auteur' ), 2025, 'Grand Prix Jean-Michel Quatrepoint', 'Agronome, elle analyse les filières agricoles et la sécurité des approvisionnements.' ),
		'Yann Kervella'           => array( array( 'laureat' ), 2025, 'Bourse Jeunes Chercheurs', 'Ingénieur, il consacre ses recherches à l\'industrie spatiale européenne.' ),
		'Claire Bonnefoy'         => array( array( 'expert', 'auteur' ), 0, '', 'Spécialiste des relations économiques internationales et des régimes de sanctions.' ),
		'Pierre-Henri Lavergne'   => array( array( 'expert' ), 0, '', 'Ancien négociateur dans les instances de normalisation internationales.' ),
		'Nadia Benali'            => array( array( 'expert', 'auteur' ), 0, '', 'Géologue et analyste des marchés de minerais critiques.' ),
		'François Delorme'        => array( array( 'expert', 'auteur' ), 0, '', 'Directeur d\'un centre de formation industrielle, spécialiste des compétences techniques.' ),
		'Élise Carpentier'        => array( array( 'partenaire' ), 0, '', 'Déléguée générale d\'une fondation partenaire de la Bourse.' ),
	);
}

/**
 * Organisations fictives : nom => type.
 */
function bqbd_orgs() {
	return array(
		'Observatoire des chaînes de valeur'          => 'partenaire',
		'Fondation Horizons Productifs'               => 'partenaire',
		'Institut Rhône-Alpes d\'études stratégiques' => 'etablissement',
		'Haut Conseil des filières stratégiques'      => 'institution',
		'Éditions du Belvédère'                       => 'editeur',
		'La Revue des territoires productifs'         => 'media',
		'Groupe Arvor Industries'                     => 'entreprise',
	);
}

/**
 * Les documents. c : collections, y/m : année et mois, p : personnes,
 * o : organisations, th : thèmes, n : nature (sinon déduite de la rubrique),
 * a : accès, x : options (cover, pdf, video, lien), k : mots-clés,
 * ed : édition du prix, e : résumé.
 */
function bqbd_documents() {
	$jmq = 'Jean-Michel Quatrepoint';

	return array(
		// 1.1 Écrits de Jean-Michel Quatrepoint.
		array( 'c' => array( 'ecrits-livres-et-ouvrages' ), 't' => 'Produire en France, un choix de puissance', 'y' => 2011, 'm' => 3, 'p' => array( $jmq ), 'o' => array( 'Éditions du Belvédère' ), 'th' => array( 'reindustrialisation', 'souverainete-industrielle' ), 'x' => array( 'cover', 'pdf' ), 'k' => array( 'désindustrialisation', 'politique industrielle' ), 'e' => 'Un essai sur trente ans de désindustrialisation et sur les conditions d\'un retour de la production en France.' ),
		array( 'c' => array( 'ecrits-livres-et-ouvrages' ), 't' => 'L\'Europe désarmée face aux marchés', 'y' => 2014, 'p' => array( $jmq ), 'o' => array( 'Éditions du Belvédère' ), 'th' => array( 'europe', 'souverainete-financiere' ), 'x' => array( 'cover' ), 'e' => 'Comment l\'Union européenne a renoncé aux instruments de sa puissance économique, et ce qu\'il faudrait pour les retrouver.' ),
		array( 'c' => array( 'ecrits-articles' ), 't' => 'Le crépuscule des champions nationaux', 'y' => 2009, 'm' => 11, 'p' => array( $jmq ), 'th' => array( 'grands-groupes', 'entreprises-strategiques' ), 'x' => array( 'lien' ), 'e' => 'Rachats, démantèlements, délocalisations : retour sur la disparition de plusieurs fleurons industriels français.' ),
		array( 'c' => array( 'ecrits-articles' ), 't' => 'Pourquoi l\'État doit redevenir stratège', 'y' => 2016, 'm' => 5, 'p' => array( $jmq ), 'th' => array( 'politique-publique', 'administration' ), 'e' => 'Plaidoyer pour une planification souple, capable de fixer un cap industriel sur plusieurs décennies.' ),
		array( 'c' => array( 'ecrits-articles' ), 't' => 'Énergie : la facture de l\'imprévoyance', 'y' => 2019, 'm' => 9, 'p' => array( $jmq ), 'th' => array( 'energie', 'souverainete-energetique' ), 'e' => 'Comment des choix énergétiques de court terme ont fragilisé la compétitivité industrielle du pays.' ),
		array( 'c' => array( 'ecrits-chroniques' ), 't' => 'La dette et la souveraineté', 'y' => 2012, 'm' => 2, 'p' => array( $jmq ), 'th' => array( 'finances-publiques', 'souverainete-financiere' ), 'e' => 'Chronique hebdomadaire : quand l\'endettement public réduit les marges de manœuvre stratégiques d\'un État.' ),
		array( 'c' => array( 'ecrits-chroniques' ), 't' => 'L\'Allemagne, modèle ou mirage ?', 'y' => 2013, 'm' => 6, 'p' => array( $jmq ), 'th' => array( 'europe', 'pme-eti' ), 'e' => 'Chronique hebdomadaire : ce que la France peut réellement apprendre du Mittelstand allemand.' ),
		array( 'c' => array( 'ecrits-tribunes' ), 't' => 'Pour une préférence européenne dans les marchés publics', 'y' => 2018, 'm' => 4, 'p' => array( $jmq ), 'th' => array( 'regulation', 'europe', 'commerce' ), 'k' => array( 'commande publique' ), 'e' => 'Tribune pour que la commande publique européenne serve enfin les industries du continent.' ),
		array( 'c' => array( 'ecrits-notes' ), 't' => 'Note sur le financement des PME industrielles', 'y' => 2007, 'p' => array( $jmq ), 'th' => array( 'pme-eti', 'capital' ), 'x' => array( 'pdf' ), 'e' => 'Note de travail sur les difficultés de financement des PME industrielles et les pistes pour y remédier.' ),
		array( 'c' => array( 'ecrits-rapports' ), 't' => 'Rapport sur les dépendances technologiques françaises', 'y' => 2020, 'm' => 10, 'p' => array( $jmq ), 'th' => array( 'dependances', 'souverainete-technologique', 'numerique' ), 'a' => 'adherents', 'x' => array( 'pdf' ), 'e' => 'Inventaire des technologies critiques pour lesquelles la France dépend de fournisseurs étrangers.' ),
		array( 'c' => array( 'ecrits-entretiens' ), 't' => '« L\'industrie est une affaire de temps long »', 'y' => 2017, 'm' => 1, 'p' => array( $jmq ), 'th' => array( 'industrie', 'politique-publique' ), 'x' => array( 'video' ), 'e' => 'Grand entretien sur la politique industrielle, la mondialisation et le rôle de l\'État.' ),
		array( 'c' => array( 'ecrits-conferences-et-interventions' ), 't' => 'Souveraineté et mondialisation', 'y' => 2015, 'm' => 11, 'p' => array( $jmq ), 'th' => array( 'mondialisation', 'souverainete-economique' ), 'x' => array( 'video' ), 'e' => 'Conférence donnée devant des étudiants sur les rapports de force économiques entre nations.' ),

		// 1.2 Documents de son fonds documentaire.
		array( 'c' => array( 'fonds-archives-de-presse' ), 't' => 'Dossier de presse : la privatisation des télécoms', 'y' => 1997, 'th' => array( 'telecoms', 'entreprises-strategiques' ), 'a' => 'sur-place', 'e' => 'Coupures de presse rassemblées au moment de l\'ouverture du capital de l\'opérateur historique.' ),
		array( 'c' => array( 'fonds-archives-de-presse' ), 't' => 'Coupures de presse sur la crise sidérurgique', 'y' => 1984, 'th' => array( 'territoires', 'travail' ), 'x' => array( 'cover' ), 'e' => 'Articles de presse régionale et nationale sur la fermeture des hauts fourneaux lorrains.' ),
		array( 'c' => array( 'fonds-rapports-et-etudes' ), 't' => 'Étude sur l\'avenir de la filière nucléaire', 'y' => 2006, 'th' => array( 'nucleaire', 'filieres' ), 'o' => array( 'Haut Conseil des filières stratégiques' ), 'x' => array( 'pdf' ), 'e' => 'Étude conservée dans le fonds : scénarios pour le renouvellement du parc et le maintien des compétences.' ),
		array( 'c' => array( 'fonds-documents-institutionnels' ), 't' => 'Compte rendu d\'audition parlementaire sur l\'industrie', 'y' => 2012, 'm' => 7, 'o' => array( 'Haut Conseil des filières stratégiques' ), 'th' => array( 'politique-publique' ), 'e' => 'Compte rendu d\'une audition consacrée à la désindustrialisation et aux outils de l\'État.' ),
		array( 'c' => array( 'fonds-documents-dentreprises' ), 't' => 'Plaquette stratégique d\'un groupe industriel', 'y' => 1999, 'o' => array( 'Groupe Arvor Industries' ), 'th' => array( 'grands-groupes' ), 'a' => 'partenaires', 'e' => 'Document interne présentant la stratégie d\'un groupe industriel à l\'aube des années 2000.' ),
		array( 'c' => array( 'fonds-documents-economiques-et-industriels' ), 't' => 'Tableaux de bord de la production manufacturière', 'y' => 2008, 'th' => array( 'industrie', 'filieres' ), 'x' => array( 'pdf' ), 'e' => 'Séries statistiques annotées sur la production manufacturière française de 1990 à 2008.' ),
		array( 'c' => array( 'fonds-documents-geopolitiques' ), 't' => 'Cartographie des routes de l\'énergie', 'y' => 2010, 'th' => array( 'energie', 'geopolitique', 'russie' ), 'x' => array( 'cover' ), 'e' => 'Cartes et notes sur les gazoducs, oléoducs et routes maritimes de l\'énergie vers l\'Europe.' ),
		array( 'c' => array( 'fonds-sources-historiques' ), 't' => 'Notes manuscrites sur la politique industrielle des années 1960', 'y' => 1968, 'th' => array( 'politique-publique', 'industrie' ), 'a' => 'sur-place', 'e' => 'Notes de lecture et de reportage sur les grands programmes industriels de l\'après-guerre.' ),
		array( 'c' => array( 'fonds-documents-de-travail' ), 't' => 'Plans et brouillons d\'un essai inachevé', 'y' => 2021, 'th' => array( 'souverainete' ), 'a' => 'adherents', 'e' => 'Documents de travail préparatoires : plans successifs, fiches de lecture et notes.' ),
		array( 'c' => array( 'fonds-autres-documents-conserves' ), 't' => 'Carnets de reportage', 'y' => 1992, 'th' => array( 'mondialisation' ), 'e' => 'Carnets tenus lors de reportages en Asie et aux États-Unis au début des années 1990.' ),

		// 2.2 Travaux primés.
		array( 'c' => array( 'travaux-primes' ), 't' => 'Les semi-conducteurs, talon d\'Achille européen', 'y' => 2021, 'm' => 6, 'ed' => 2021, 'n' => 'etude', 'p' => array( 'Camille Rostand' ), 'th' => array( 'numerique', 'europe', 'dependances', 'souverainete-technologique' ), 'x' => array( 'cover', 'pdf' ), 'k' => array( 'semi-conducteurs' ), 'e' => 'Pourquoi l\'Europe a perdu la bataille des puces, et à quelles conditions elle pourrait revenir dans la course.' ),
		array( 'c' => array( 'travaux-primes' ), 't' => 'Relocaliser la chimie fine : conditions et limites', 'y' => 2022, 'm' => 6, 'ed' => 2022, 'n' => 'etude', 'p' => array( 'Julien Marchetti' ), 'th' => array( 'sante', 'reindustrialisation' ), 'x' => array( 'pdf' ), 'e' => 'Une analyse des principes actifs pharmaceutiques qu\'il serait possible, ou non, de produire à nouveau en France.' ),
		array( 'c' => array( 'travaux-primes' ), 't' => 'Les ports français dans la compétition mondiale', 'y' => 2023, 'm' => 6, 'ed' => 2023, 'n' => 'livre-ou-ouvrage', 'p' => array( 'Inès Haddad' ), 'th' => array( 'transports', 'commerce', 'mondialisation' ), 'x' => array( 'cover' ), 'e' => 'Pourquoi les ports français ont perdu du terrain face à Anvers et Rotterdam, et comment le reconquérir.' ),
		array( 'c' => array( 'travaux-primes' ), 't' => 'L\'État actionnaire, bilan de quarante ans', 'y' => 2024, 'm' => 6, 'ed' => 2024, 'n' => 'etude', 'p' => array( 'Mathieu Lefranc' ), 'th' => array( 'capital', 'politique-publique', 'finances-publiques' ), 'e' => 'Nationalisations, privatisations, participations : ce que l\'État a fait de ses entreprises depuis 1982.' ),
		array( 'c' => array( 'travaux-primes' ), 't' => 'Souveraineté alimentaire et filières agricoles', 'y' => 2025, 'm' => 6, 'ed' => 2025, 'n' => 'etude', 'p' => array( 'Sophie Berthelot' ), 'th' => array( 'agroalimentaire', 'souverainete-alimentaire', 'territoires' ), 'x' => array( 'cover', 'pdf' ), 'e' => 'Cartographie des filières agricoles françaises les plus dépendantes des importations.' ),
		array( 'c' => array( 'travaux-primes' ), 't' => 'Le spatial européen face au New Space', 'y' => 2025, 'm' => 6, 'ed' => 2025, 'n' => 'note-de-recherche', 'p' => array( 'Yann Kervella' ), 'th' => array( 'spatial', 'etats-unis', 'innovation' ), 'e' => 'Comment les lanceurs réutilisables américains bousculent le modèle industriel européen.' ),

		// 2.3 Travaux ultérieurs.
		array( 'c' => array( 'travaux-ulterieurs' ), 't' => 'Puces et puissance, trois ans après', 'y' => 2024, 'm' => 3, 'n' => 'article', 'p' => array( 'Camille Rostand' ), 'th' => array( 'numerique', 'chine' ), 'e' => 'Suite des travaux primés en 2021 : ce qui a changé depuis le lancement des grands plans européens.' ),
		array( 'c' => array( 'travaux-ulterieurs' ), 't' => 'Façades maritimes et réindustrialisation', 'y' => 2025, 'm' => 2, 'n' => 'etude', 'p' => array( 'Inès Haddad' ), 'th' => array( 'territoires', 'reindustrialisation' ), 'e' => 'Les zones portuaires comme terrains d\'implantation des nouvelles usines.' ),
		array( 'c' => array( 'travaux-ulterieurs' ), 't' => 'Médicaments essentiels : la carte des dépendances', 'y' => 2026, 'm' => 1, 'n' => 'rapport', 'p' => array( 'Julien Marchetti' ), 'th' => array( 'sante', 'dependances' ), 'a' => 'adherents', 'x' => array( 'pdf' ), 'e' => 'Liste commentée des médicaments essentiels dont la production dépend d\'un seul pays.' ),

		// 2.4 Publications associées.
		array( 'c' => array( 'associees-articles' ), 't' => 'Nourrir la France en 2040', 'y' => 2025, 'm' => 10, 'p' => array( 'Sophie Berthelot' ), 'o' => array( 'La Revue des territoires productifs' ), 'th' => array( 'souverainete-alimentaire' ), 'x' => array( 'lien' ), 'e' => 'Article de prospective sur les besoins alimentaires et les capacités de production à l\'horizon 2040.' ),
		array( 'c' => array( 'associees-ouvrages' ), 't' => 'Le capitalisme d\'État à la française', 'y' => 2025, 'm' => 4, 'p' => array( 'Mathieu Lefranc' ), 'o' => array( 'Éditions du Belvédère' ), 'th' => array( 'capital', 'politique-publique' ), 'x' => array( 'cover' ), 'e' => 'Un essai tiré des travaux primés en 2024, enrichi de nombreux entretiens.' ),
		array( 'c' => array( 'associees-entretiens' ), 't' => 'L\'Europe peut-elle rester une puissance spatiale ?', 'y' => 2026, 'm' => 2, 'p' => array( 'Yann Kervella' ), 'th' => array( 'spatial', 'europe' ), 'x' => array( 'video' ), 'e' => 'Entretien filmé avec le lauréat 2025 sur l\'avenir des lanceurs européens.' ),
		array( 'c' => array( 'associees-autres-travaux' ), 't' => 'Podcast : la bataille des puces', 'y' => 2023, 'm' => 9, 'p' => array( 'Camille Rostand' ), 'th' => array( 'numerique' ), 'x' => array( 'lien' ), 'e' => 'Série audio en quatre épisodes consacrée à l\'industrie des semi-conducteurs.' ),

		// 3.1 Types de publications et 3.2 Collections éditoriales.
		array( 'c' => array( 'types-etudes' ), 't' => 'La base industrielle de défense française', 'y' => 2023, 'm' => 5, 'p' => array( 'Claire Bonnefoy' ), 'th' => array( 'defense', 'entreprises-strategiques' ), 'x' => array( 'cover', 'pdf' ), 'e' => 'État des lieux des entreprises de défense françaises, de leurs fournisseurs et de leurs fragilités.' ),
		array( 'c' => array( 'types-notes-de-recherche' ), 't' => 'L\'hydrogène, promesse ou mirage', 'y' => 2024, 'm' => 3, 'p' => array( 'Nadia Benali' ), 'th' => array( 'energie', 'innovation' ), 'e' => 'Note de recherche sur les usages industriels de l\'hydrogène et leur coût réel.' ),
		array( 'c' => array( 'types-cahiers', 'collections-les-cahiers-de-la-bourse-jean-michel-quatrepoint' ), 't' => 'Cahier n°1 : la France face à la guerre économique', 'y' => 2023, 'm' => 11, 'p' => array( 'Claire Bonnefoy', 'Pierre-Henri Lavergne' ), 'th' => array( 'guerre-economique', 'influence', 'chine' ), 'x' => array( 'cover', 'pdf' ), 'k' => array( 'intelligence économique' ), 'e' => 'Premier numéro des Cahiers : cartographie des offensives économiques visant les entreprises françaises.' ),
		array( 'c' => array( 'types-cahiers', 'collections-les-cahiers-de-la-bourse-jean-michel-quatrepoint' ), 't' => 'Cahier n°2 : reconstruire les filières', 'y' => 2024, 'm' => 11, 'p' => array( 'François Delorme', 'Mathieu Lefranc' ), 'th' => array( 'filieres', 'reindustrialisation' ), 'a' => 'adherents', 'x' => array( 'cover' ), 'e' => 'Deuxième numéro des Cahiers : comment reconstituer une filière industrielle complète.' ),
		array( 'c' => array( 'types-cahiers', 'collections-les-cahiers-de-la-bourse-jean-michel-quatrepoint' ), 't' => 'Cahier n°3 : l\'Europe et ses dépendances', 'y' => 2025, 'm' => 11, 'p' => array( 'Nadia Benali', 'Camille Rostand' ), 'th' => array( 'europe', 'dependances', 'matieres-premieres' ), 'x' => array( 'cover', 'pdf' ), 'e' => 'Troisième numéro des Cahiers : les dépendances européennes, secteur par secteur.' ),
		array( 'c' => array( 'types-rapports' ), 't' => 'Rapport annuel sur la souveraineté économique 2025', 'y' => 2025, 'm' => 12, 'th' => array( 'souverainete-economique', 'dependances' ), 'x' => array( 'pdf' ), 'e' => 'Les indicateurs clés de la souveraineté économique française, mis à jour chaque année.' ),
		array( 'c' => array( 'types-dossiers-thematiques' ), 't' => 'L\'énergie nucléaire, trente ans de choix', 'y' => 2024, 'm' => 9, 'th' => array( 'nucleaire', 'energie', 'politique-publique' ), 'x' => array( 'cover' ), 'e' => 'Dossier thématique réunissant articles, chronologies et documents sur la politique nucléaire.' ),
		array( 'c' => array( 'types-tribunes' ), 't' => 'Pour une commande publique stratégique', 'y' => 2025, 'm' => 3, 'p' => array( 'Claire Bonnefoy', 'François Delorme' ), 'th' => array( 'regulation', 'politique-publique' ), 'e' => 'Tribune collective pour que l\'achat public soutienne les productions stratégiques.' ),
		array( 'c' => array( 'types-entretiens' ), 't' => 'Entretien avec le président de la Bourse', 'y' => 2024, 'm' => 1, 'th' => array( 'souverainete' ), 'x' => array( 'video' ), 'e' => 'Les missions de la Bourse, ses prix et ses projets, présentés par son président.' ),
		array( 'c' => array( 'types-actes-de-colloques-et-conferences' ), 't' => 'Actes du colloque « Produire en Europe »', 'y' => 2024, 'm' => 10, 'p' => array( 'Pierre-Henri Lavergne', 'Inès Haddad', 'Sophie Berthelot' ), 'th' => array( 'europe', 'reindustrialisation' ), 'x' => array( 'pdf' ), 'e' => 'Les interventions du colloque annuel de la Bourse, consacré à la production industrielle en Europe.' ),
		array( 'c' => array( 'types-syntheses' ), 't' => 'Les chiffres clés de l\'industrie française', 'y' => 2026, 'm' => 1, 'th' => array( 'grands-groupes', 'pme-eti' ), 'e' => 'Synthèse de quatre pages : emploi, production, investissement et commerce extérieur.' ),
		array( 'c' => array( 'types-publications-institutionnelles' ), 't' => 'Rapport d\'activité 2025 de la Bourse', 'y' => 2026, 'm' => 3, 'th' => array(), 'x' => array( 'pdf' ), 'e' => 'Le bilan de l\'année : prix remis, publications, colloques et partenariats.' ),
		array( 'c' => array( 'collections-autres-collections' ), 't' => 'Repères : le vocabulaire de la souveraineté', 'y' => 2025, 'm' => 5, 'th' => array( 'souverainete', 'formation' ), 'n' => 'synthese', 'e' => 'Premier volume de la série Repères : cinquante notions expliquées simplement.' ),

		// 4.1 Contributions d'experts.
		array( 'c' => array( 'contributions-dexperts' ), 't' => 'Sanctions et contre-sanctions : la Russie et l\'Europe', 'y' => 2022, 'm' => 9, 'n' => 'article', 'p' => array( 'Claire Bonnefoy' ), 'th' => array( 'russie', 'geopolitique', 'europe' ), 'e' => 'Ce que les sanctions ont révélé des interdépendances entre l\'Europe et la Russie.' ),
		array( 'c' => array( 'contributions-dexperts' ), 't' => 'Normes techniques, l\'arme discrète', 'y' => 2023, 'm' => 2, 'n' => 'note', 'p' => array( 'Pierre-Henri Lavergne' ), 'th' => array( 'normes', 'influence' ), 'e' => 'Comment la normalisation internationale est devenue un terrain de compétition économique.' ),
		array( 'c' => array( 'contributions-dexperts' ), 't' => 'Minerais critiques : la course est lancée', 'y' => 2024, 'm' => 6, 'n' => 'etude', 'p' => array( 'Nadia Benali' ), 'th' => array( 'matieres-premieres', 'chine' ), 'x' => array( 'pdf', 'cover' ), 'e' => 'Lithium, cobalt, terres rares : état des lieux des approvisionnements européens.' ),
		array( 'c' => array( 'contributions-dexperts' ), 't' => 'Former les techniciens de demain', 'y' => 2025, 'm' => 9, 'n' => 'article', 'p' => array( 'François Delorme' ), 'th' => array( 'competences', 'formation', 'travail' ), 'e' => 'Pénurie de techniciens : les leviers pour répondre aux besoins de l\'industrie.' ),

		// 4.2 Publications de partenaires.
		array( 'c' => array( 'publications-de-partenaires' ), 't' => 'Baromètre des chaînes de valeur 2023', 'y' => 2023, 'm' => 12, 'n' => 'rapport', 'o' => array( 'Observatoire des chaînes de valeur' ), 'th' => array( 'mondialisation', 'dependances' ), 'x' => array( 'pdf' ), 'e' => 'Édition 2023 du baromètre annuel des chaînes d\'approvisionnement industrielles.' ),
		array( 'c' => array( 'publications-de-partenaires' ), 't' => 'Baromètre des chaînes de valeur 2025', 'y' => 2025, 'm' => 12, 'n' => 'rapport', 'o' => array( 'Observatoire des chaînes de valeur' ), 'th' => array( 'mondialisation', 'dependances' ), 'x' => array( 'pdf' ), 'e' => 'Édition 2025 du baromètre annuel des chaînes d\'approvisionnement industrielles.' ),
		array( 'c' => array( 'publications-de-partenaires' ), 't' => 'Livre blanc : l\'usine de demain', 'y' => 2024, 'm' => 4, 'n' => 'livre-ou-ouvrage', 'o' => array( 'Fondation Horizons Productifs' ), 'th' => array( 'innovation', 'pme-eti' ), 'x' => array( 'cover' ), 'e' => 'Robotique, sobriété, compétences : le portrait de l\'usine française en 2035.' ),
		array( 'c' => array( 'publications-de-partenaires' ), 't' => 'Les territoires d\'industrie, cinq ans après', 'y' => 2025, 'm' => 6, 'n' => 'article', 'o' => array( 'La Revue des territoires productifs' ), 'th' => array( 'territoires' ), 'x' => array( 'lien' ), 'e' => 'Enquête dans cinq territoires qui ont misé sur l\'industrie.' ),

		// 4.3 Entretiens.
		array( 'c' => array( 'entretiens-experts' ), 't' => '« La norme est un champ de bataille »', 'y' => 2024, 'm' => 5, 'p' => array( 'Pierre-Henri Lavergne' ), 'th' => array( 'normes', 'guerre-economique' ), 'x' => array( 'video' ), 'e' => 'Entretien filmé sur les coulisses de la normalisation internationale.' ),
		array( 'c' => array( 'entretiens-laureats' ), 't' => 'Entretien avec Inès Haddad, lauréate 2023', 'y' => 2024, 'm' => 2, 'p' => array( 'Inès Haddad' ), 'th' => array( 'transports' ), 'e' => 'La lauréate revient sur ses recherches et sur ce que le prix a changé pour elle.' ),
		array( 'c' => array( 'entretiens-partenaires' ), 't' => 'Ce que les entreprises attendent de l\'État', 'y' => 2025, 'm' => 7, 'p' => array( 'Élise Carpentier' ), 'o' => array( 'Fondation Horizons Productifs' ), 'th' => array( 'politique-publique', 'pme-eti' ), 'e' => 'Entretien avec la déléguée générale d\'une fondation partenaire.' ),

		// 4.4 Tribunes invitées.
		array( 'c' => array( 'tribunes-invitees' ), 't' => 'L\'Europe doit sécuriser ses métaux', 'y' => 2025, 'm' => 1, 'p' => array( 'Nadia Benali' ), 'th' => array( 'matieres-premieres', 'europe' ), 'e' => 'Tribune pour une politique européenne de stocks stratégiques de métaux.' ),
		array( 'c' => array( 'tribunes-invitees' ), 't' => 'Réindustrialiser, c\'est d\'abord former', 'y' => 2026, 'm' => 2, 'p' => array( 'François Delorme' ), 'th' => array( 'formation', 'reindustrialisation' ), 'e' => 'Tribune : sans compétences, aucune usine ne rouvrira durablement.' ),

		// 4.5 Études partenaires.
		array( 'c' => array( 'etudes-partenaires' ), 't' => 'Les ETI industrielles en Auvergne-Rhône-Alpes', 'y' => 2024, 'm' => 11, 'o' => array( 'Institut Rhône-Alpes d\'études stratégiques' ), 'th' => array( 'pme-eti', 'territoires' ), 'x' => array( 'pdf' ), 'e' => 'Portrait statistique et qualitatif des entreprises de taille intermédiaire de la région.' ),
		array( 'c' => array( 'etudes-partenaires' ), 't' => 'Automatisation et emploi industriel', 'y' => 2025, 'm' => 4, 'o' => array( 'Fondation Horizons Productifs' ), 'th' => array( 'travail', 'innovation' ), 'a' => 'partenaires', 'e' => 'Effets de la robotisation sur l\'emploi dans cent usines françaises.' ),

		// 4.6 Documents institutionnels.
		array( 'c' => array( 'documents-institutionnels' ), 't' => 'Feuille de route des filières stratégiques', 'y' => 2024, 'm' => 6, 'o' => array( 'Haut Conseil des filières stratégiques' ), 'th' => array( 'filieres', 'politique-publique' ), 'x' => array( 'pdf' ), 'e' => 'Objectifs et calendrier fixés pour les principales filières industrielles.' ),
		array( 'c' => array( 'documents-institutionnels' ), 't' => 'Avis sur la commande publique', 'y' => 2025, 'm' => 9, 'n' => 'note', 'o' => array( 'Haut Conseil des filières stratégiques' ), 'th' => array( 'regulation' ), 'e' => 'Avis consultatif sur l\'usage de la commande publique comme levier industriel.' ),
	);
}

/**
 * Documents associés, par titres : ils se répondent sur leurs fiches.
 */
function bqbd_links() {
	return array(
		array( 'Les semi-conducteurs, talon d\'Achille européen', 'Puces et puissance, trois ans après', 'Podcast : la bataille des puces' ),
		array( 'Cahier n°1 : la France face à la guerre économique', 'Cahier n°2 : reconstruire les filières', 'Cahier n°3 : l\'Europe et ses dépendances' ),
		array( 'L\'État actionnaire, bilan de quarante ans', 'Le capitalisme d\'État à la française' ),
		array( 'Baromètre des chaînes de valeur 2023', 'Baromètre des chaînes de valeur 2025' ),
	);
}

/* -------------------------------------------------------------------------
 * 3. Création
 * ---------------------------------------------------------------------- */

function bqbd_term( $name, $taxonomy ) {
	$term = get_term_by( 'name', $name, $taxonomy );

	if ( $term ) {
		return (int) $term->term_id;
	}

	$made = wp_insert_term( $name, $taxonomy );

	if ( is_wp_error( $made ) ) {
		return 0;
	}

	update_term_meta( (int) $made['term_id'], 'bqb_demo', 1 );

	return (int) $made['term_id'];
}

function bqbd_ids_by_slug( $slugs, $taxonomy ) {
	$ids = array();

	foreach ( (array) $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term ) {
			$ids[] = (int) $term->term_id;
		}
	}

	return $ids;
}

/**
 * Enregistre un fichier dans la médiathèque, marqué comme démo.
 */
function bqbd_attach( $filename, $bytes, $mime, $title ) {
	$upload = wp_upload_bits( $filename, null, $bytes );

	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_title'     => $title,
			'post_mime_type' => $mime,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}

	update_post_meta( $id, '_bqb_demo', 1 );

	if ( 0 === strpos( $mime, 'image/' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	}

	return (int) $id;
}

/**
 * Un PDF d'une page, écrit à la main : rien à télécharger ailleurs.
 */
function bqbd_pdf() {
	$text    = 'BT /F1 22 Tf 72 760 Td (Bourse Jean-Michel Quatrepoint) Tj 0 -34 Td /F1 14 Tf (Document de demonstration, contenu fictif.) Tj ET';
	$objects = array(
		'<< /Type /Catalog /Pages 2 0 R >>',
		'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
		'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
		"<< /Length " . strlen( $text ) . " >>\nstream\n" . $text . "\nendstream",
		'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
	);

	// Un peu de volume, pour afficher une taille réaliste (« 180 Ko »).
	$pdf     = "%PDF-1.4\n" . str_repeat( '% Bourse Jean-Michel Quatrepoint, document de demonstration.' . "\n", 3000 );
	$offsets = array();

	foreach ( $objects as $i => $object ) {
		$offsets[] = strlen( $pdf );
		$pdf      .= ( $i + 1 ) . " 0 obj\n" . $object . "\nendobj\n";
	}

	$xref = strlen( $pdf );
	$pdf .= "xref\n0 " . ( count( $objects ) + 1 ) . "\n0000000000 65535 f \n";

	foreach ( $offsets as $offset ) {
		$pdf .= sprintf( "%010d 00000 n \n", $offset );
	}

	return $pdf . "trailer\n<< /Size " . ( count( $objects ) + 1 ) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
}

/**
 * Une couverture de livre abstraite, aux couleurs du site (GD).
 */
function bqbd_cover( $hex, $variant ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return '';
	}

	$w  = 600;
	$h  = 800;
	$im = imagecreatetruecolor( $w, $h );
	$r  = hexdec( substr( $hex, 1, 2 ) );
	$g  = hexdec( substr( $hex, 3, 2 ) );
	$b  = hexdec( substr( $hex, 5, 2 ) );

	// Dégradé vertical, du plus clair au plus sombre.
	for ( $y = 0; $y < $h; $y++ ) {
		$k = 1.12 - ( $y / $h ) * 0.45;
		imageline( $im, 0, $y, $w, $y, imagecolorallocate( $im, min( 255, (int) ( $r * $k ) ), min( 255, (int) ( $g * $k ) ), min( 255, (int) ( $b * $k ) ) ) );
	}

	$white = imagecolorallocatealpha( $im, 255, 255, 255, 20 );
	$soft  = imagecolorallocatealpha( $im, 255, 255, 255, 95 );
	$gold  = imagecolorallocate( $im, 199, 90, 24 );

	imagesetthickness( $im, 2 );
	imagerectangle( $im, 34, 34, $w - 35, $h - 35, $soft );

	// Motif différent selon la variante.
	if ( 0 === $variant % 3 ) {
		for ( $i = 0; $i < 9; $i++ ) {
			imagearc( $im, $w - 60, 150, 120 + $i * 60, 120 + $i * 60, 90, 270, $soft );
		}
	} elseif ( 1 === $variant % 3 ) {
		for ( $i = 0; $i < 12; $i++ ) {
			imageline( $im, 34 + $i * 50, $h - 35, $w - 35, 300 + $i * 40, $soft );
		}
	} else {
		imagefilledellipse( $im, $w / 2, 250, 260, 260, imagecolorallocatealpha( $im, 255, 255, 255, 105 ) );
		imageellipse( $im, $w / 2, 250, 300, 300, $soft );
	}

	// Filet et barres de titre.
	imagefilledrectangle( $im, 70, 470, 130, 474, $gold );
	imagefilledrectangle( $im, 70, 500, 470, 526, $white );
	imagefilledrectangle( $im, 70, 542, 380, 568, $white );
	imagefilledrectangle( $im, 70, 610, 250, 620, $soft );
	imagefilledrectangle( $im, 70, $h - 110, 210, $h - 102, $soft );

	ob_start();
	imagepng( $im );
	$png = ob_get_clean();
	imagedestroy( $im );

	return $png;
}

function bqbd_paragraphs( $title, $nature ) {
	$contexts = array(
		'Ce document s\'inscrit dans les travaux que la Bourse consacre à la souveraineté économique et industrielle. Il éclaire un moment, une filière ou un débat, et le replace dans une perspective de long terme.',
		'Le texte est présenté ici avec son contexte de publication, sa date et ses auteurs, afin de faciliter sa consultation et sa citation. Les documents associés, en bas de page, prolongent la lecture.',
		'Sa lecture croise plusieurs thèmes de la bibliothèque : l\'industrie, les dépendances stratégiques, le rôle de l\'État et la place de la France en Europe.',
	);

	return '<p><em>Exemple fictif, créé pour tester l\'affichage de la bibliothèque. Il sera supprimé avant la mise en ligne.</em></p>'
		. '<h2>Contexte</h2><p>' . esc_html( $contexts[ crc32( $title ) % 3 ] ) . '</p>'
		. '<p>' . esc_html( $contexts[ ( crc32( $title ) + 1 ) % 3 ] ) . '</p>'
		. '<h2>Notice éditoriale</h2><p>' . esc_html( ucfirst( $nature ) ) . ' présenté dans sa version intégrale. La mise en page d\'origine a été conservée.</p>';
}

function bqbd_create_all() {
	$palette = bqb_palette();
	$colors  = array( $palette['bordeaux'], $palette['navy'], $palette['orange'], $palette['plum'], $palette['teal'], '#8A6A3A' );
	$covers  = array();

	foreach ( $colors as $i => $hex ) {
		$png = bqbd_cover( $hex, $i );
		if ( $png ) {
			$covers[] = bqbd_attach( 'bqb-demo-couverture-' . ( $i + 1 ) . '.png', $png, 'image/png', 'Couverture de démonstration ' . ( $i + 1 ) );
		}
	}

	$covers = array_values( array_filter( $covers ) );
	$pdf    = bqbd_attach( 'bqb-demo-document.pdf', bqbd_pdf(), 'application/pdf', 'Document de démonstration' );

	// Personnes et organisations fictives.
	foreach ( bqbd_people() as $name => $info ) {
		$id = bqbd_term( $name, 'bqb_personne' );
		// Une vraie fiche du même nom n'est jamais modifiée.
		if ( ! $id || ! get_term_meta( $id, 'bqb_demo', true ) ) {
			continue;
		}
		update_term_meta( $id, 'bqb_roles', $info[0] );
		update_term_meta( $id, 'bqb_bio', $info[3] );
		if ( $info[1] ) {
			update_term_meta( $id, 'bqb_annee_prix', $info[1] );
			update_term_meta( $id, 'bqb_prix_nom', $info[2] );
		}
	}

	foreach ( bqbd_orgs() as $name => $type ) {
		$id = bqbd_term( $name, 'bqb_organisation' );
		if ( $id && get_term_meta( $id, 'bqb_demo', true ) ) {
			update_term_meta( $id, 'bqb_type_orga', $type );
		}
	}

	// Biographie provisoire de Jean-Michel Quatrepoint, pour la page dédiée.
	$jmq = get_term_by( 'slug', 'jean-michel-quatrepoint', 'bqb_personne' );
	if ( $jmq && '' === (string) get_term_meta( $jmq->term_id, 'bqb_bio', true ) ) {
		update_term_meta( $jmq->term_id, 'bqb_bio', 'Texte provisoire de démonstration : la biographie de Jean-Michel Quatrepoint s\'affichera ici, avec son parcours, ses fonctions et ses principaux ouvrages.' );
		update_option( 'bqbd_jmq_bio', 1, false );
	}

	$levels = array_keys( bqb_access_levels() );
	$made   = array();
	$i      = 0;

	foreach ( bqbd_documents() as $doc ) {
		$i++;
		$nature_name = isset( $doc['n'] ) ? $doc['n'] : 'document';

		$id = wp_insert_post(
			array(
				'post_type'    => 'bqb_document',
				'post_status'  => 'publish',
				'post_title'   => $doc['t'],
				'post_excerpt' => $doc['e'],
				'post_content' => bqbd_paragraphs( $doc['t'], str_replace( '-', ' ', $nature_name ) ),
			),
			true
		);

		if ( is_wp_error( $id ) ) {
			continue;
		}

		$made[ $doc['t'] ] = $id;
		$year              = (int) $doc['y'];
		$month             = isset( $doc['m'] ) ? (int) $doc['m'] : 0;
		$access            = isset( $doc['a'] ) && in_array( $doc['a'], $levels, true ) ? $doc['a'] : 'public';
		$extras            = isset( $doc['x'] ) ? $doc['x'] : array();

		update_post_meta( $id, '_bqb_demo', 1 );
		update_post_meta( $id, 'rank_math_robots', array( 'noindex', 'nofollow' ) );
		update_post_meta( $id, '_bqb_annee', $year );
		update_post_meta( $id, '_bqb_acces', $access );
		update_post_meta( $id, '_bqb_date_tri', sprintf( '%04d-%02d-01', $year, $month ? $month : 1 ) );

		if ( $month ) {
			update_post_meta( $id, '_bqb_mois', $month );
		}
		if ( ! empty( $doc['ed'] ) ) {
			update_post_meta( $id, '_bqb_edition', (int) $doc['ed'] );
		}
		if ( in_array( 'pdf', $extras, true ) && $pdf ) {
			update_post_meta( $id, '_bqb_fichier', $pdf );
		}
		if ( in_array( 'video', $extras, true ) ) {
			// Court métrage libre de droits (Blender Foundation), pour tester le lecteur.
			update_post_meta( $id, '_bqb_lien', 'https://www.youtube.com/watch?v=aqz-KE-bpKQ' );
		} elseif ( in_array( 'lien', $extras, true ) ) {
			update_post_meta( $id, '_bqb_lien', 'https://www.example.org/bibliotheque-demo' );
		}
		if ( in_array( 'cover', $extras, true ) && $covers ) {
			set_post_thumbnail( $id, $covers[ $i % count( $covers ) ] );
		}

		$authors = isset( $doc['p'] ) ? $doc['p'] : array();
		$ref     = ( $authors ? implode( ', ', $authors ) . ', ' : '' ) . '« ' . $doc['t'] . ' », ' . $year . '.';
		update_post_meta( $id, '_bqb_reference', $ref );

		if ( 0 === $i % 4 ) {
			update_post_meta( $id, '_bqb_droits', '© Bourse Jean-Michel Quatrepoint, reproduction soumise à autorisation.' );
		}

		wp_set_object_terms( $id, bqbd_ids_by_slug( $doc['c'], 'bqb_collection' ), 'bqb_collection' );
		wp_set_object_terms( $id, bqbd_ids_by_slug( isset( $doc['th'] ) ? $doc['th'] : array(), 'bqb_theme' ), 'bqb_theme' );
		wp_set_object_terms( $id, array_filter( array_map( function ( $n ) { return bqbd_term( $n, 'bqb_personne' ); }, $authors ) ), 'bqb_personne' );
		wp_set_object_terms( $id, array_filter( array_map( function ( $n ) { return bqbd_term( $n, 'bqb_organisation' ); }, isset( $doc['o'] ) ? $doc['o'] : array() ) ), 'bqb_organisation' );
		wp_set_object_terms( $id, array_filter( array_map( function ( $n ) { return bqbd_term( $n, 'bqb_motcle' ); }, isset( $doc['k'] ) ? $doc['k'] : array() ) ), 'bqb_motcle' );

		// Nature : celle indiquée, sinon celle de la rubrique, comme à la saisie.
		$nature = isset( $doc['n'] ) ? bqbd_ids_by_slug( array( $doc['n'] ), 'bqb_nature' ) : array_filter( array( bqb_guess_nature( $id ) ) );
		wp_set_object_terms( $id, $nature, 'bqb_nature' );
		update_post_meta( $id, '_bqb_nature_auto', isset( $doc['n'] ) ? 0 : 1 );
	}

	foreach ( bqbd_links() as $group ) {
		foreach ( $group as $title ) {
			if ( empty( $made[ $title ] ) ) {
				continue;
			}
			$others = array();
			foreach ( $group as $other ) {
				if ( $other !== $title && ! empty( $made[ $other ] ) ) {
					$others[] = $made[ $other ];
				}
			}
			update_post_meta( $made[ $title ], '_bqb_associes', $others );
		}
	}

	if ( function_exists( 'bqb_bump_cache' ) ) {
		bqb_bump_cache();
	}

	return count( $made );
}

/* -------------------------------------------------------------------------
 * 4. Suppression : uniquement ce qui porte la marque « démo »
 * ---------------------------------------------------------------------- */

function bqbd_delete_all() {
	$posts = get_posts(
		array(
			'post_type'   => array( 'bqb_document', 'attachment' ),
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_bqb_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);

	foreach ( $posts as $id ) {
		'attachment' === get_post_type( $id ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
	}

	foreach ( array( 'bqb_personne', 'bqb_organisation', 'bqb_motcle' ) as $taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_key'   => 'bqb_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term_id ) {
			wp_delete_term( (int) $term_id, $taxonomy );
		}
	}

	if ( get_option( 'bqbd_jmq_bio' ) ) {
		$jmq = get_term_by( 'slug', 'jean-michel-quatrepoint', 'bqb_personne' );
		if ( $jmq ) {
			delete_term_meta( $jmq->term_id, 'bqb_bio' );
		}
		delete_option( 'bqbd_jmq_bio' );
	}

	if ( function_exists( 'bqb_bump_cache' ) ) {
		bqb_bump_cache();
	}
}

/* -------------------------------------------------------------------------
 * 5. Jamais indexés : noindex et hors des sitemaps
 * ---------------------------------------------------------------------- */

add_filter( 'wp_robots', 'bqbd_robots' );
function bqbd_robots( $robots ) {
	if ( is_singular( 'bqb_document' ) && get_post_meta( get_queried_object_id(), '_bqb_demo', true ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}

	return $robots;
}

add_filter( 'wp_sitemaps_posts_query_args', 'bqbd_sitemap_args', 10, 2 );
function bqbd_sitemap_args( $args, $post_type ) {
	if ( 'bqb_document' === $post_type ) {
		$args['meta_query'] = array( array( 'key' => '_bqb_demo', 'compare' => 'NOT EXISTS' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	return $args;
}

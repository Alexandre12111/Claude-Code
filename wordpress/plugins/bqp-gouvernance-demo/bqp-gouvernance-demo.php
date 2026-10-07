<?php
/**
 * Plugin Name:       BQP Gouvernance : démo
 * Description:       Crée et supprime des membres fictifs pour tester le design de la gouvernance. À retirer après les tests.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * License:           GPL-2.0-or-later
 */

/**
 * BQP Gouvernance : membres de démonstration.
 *
 * Crée 11 membres fictifs (Bureau et Conseil scientifique) avec portraits
 * générés, fiches détaillées complètes, CV en PDF et quelques cas
 * particuliers (un membre sans photo, un sans fiche, un dans les deux
 * instances), pour tester le design des cartes et de la fenêtre. Un bouton
 * supprime tout ce qui a été créé, et rien d'autre.
 *
 * Nécessite le snippet BQP Gouvernance 1.3 ou plus. À supprimer après les tests.
 *
 * Version : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. L'écran « Membres de démo »
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'bqgd_menu', 20 );
function bqgd_menu() {
	if ( ! post_type_exists( 'bqg_membre' ) ) {
		return;
	}

	add_submenu_page( 'edit.php?post_type=bqg_membre', 'Membres de démo', 'Membres de démo', 'manage_options', 'bqg-demo', 'bqgd_render_page' );
}

function bqgd_ids( $type = 'bqg_membre' ) {
	return get_posts(
		array(
			'post_type'   => $type,
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_bqg_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

function bqgd_render_page() {
	if ( ! function_exists( 'bqg_render_profile' ) ) {
		echo '<div class="wrap"><h1>Membres de démo</h1><p>Ce snippet a besoin de BQP Gouvernance 1.3 ou plus, activé.</p></div>';
		return;
	}

	$count = count( bqgd_ids() );
	$msg   = isset( $_GET['bqgd'] ) ? sanitize_key( wp_unslash( $_GET['bqgd'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$done  = isset( $_GET['n'] ) ? absint( $_GET['n'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	$cats  = array();

	foreach ( bqgd_members() as $member ) {
		foreach ( $member['cats'] as $cat ) {
			$cats[ $cat ] = isset( $cats[ $cat ] ) ? $cats[ $cat ] + 1 : 1;
		}
	}

	echo '<div class="wrap bqg-help">';
	echo '<h1 class="bqg-help__title">Membres de démo</h1>';
	echo '<p class="bqg-help__intro">De faux membres pour tester le design des instances repliées et de la fiche détaillée. Tout ce qui est créé ici se supprime d\'un clic, sans toucher à vos vrais membres.</p>';

	if ( 'created' === $msg ) {
		echo '<div class="notice notice-success"><p>' . (int) $done . ' membres de démonstration créés. Ouvrez la page qui contient <code>[bqp_gouvernance]</code> pour voir le résultat.</p></div>';
	} elseif ( 'deleted' === $msg ) {
		echo '<div class="notice notice-success"><p>Membres de démonstration supprimés, avec leurs portraits et leurs CV.</p></div>';
	}

	$parts = array();
	foreach ( $cats as $slug => $n ) {
		$term    = get_term_by( 'slug', $slug, 'bqg_membre_cat' );
		$parts[] = $n . ' ' . esc_html( $term ? $term->name : $slug );
	}

	echo '<h2 class="bqg-help__subtitle">Ce qui est créé</h2><ul class="bqg-help__list">';
	echo '<li><strong>' . count( bqgd_members() ) . ' membres fictifs</strong> : ' . implode( ', ', $parts ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<li>Un <strong>portrait</strong> généré pour chacun (sauf un, pour voir les initiales), une fonction, une description et une <strong>fiche détaillée</strong> : profession, rôle dans l\'association, parcours, lien et, pour certains, un <strong>CV en PDF</strong></li>';
	echo '<li>Des cas particuliers : un membre sans fiche (carte simple), un membre présent dans les deux instances</li>';
	echo '<li>Les noms, textes et liens sont fictifs. Les liens mènent vers <code>example.org</code>, un domaine réservé aux exemples.</li>';
	echo '</ul>';

	echo '<h2 class="bqg-help__subtitle">' . ( $count ? (int) $count . ' membres de démo en place' : 'Aucun membre de démo pour le moment' ) . '</h2><p style="display:flex;gap:10px;flex-wrap:wrap;">';
	echo '<a class="button button-primary button-hero" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bqgd_create' ), 'bqgd_create' ) ) . '">' . ( $count ? 'Recréer les membres de démo' : 'Créer les membres de démo' ) . '</a>';
	if ( $count ) {
		echo '<a class="button button-hero" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bqgd_delete' ), 'bqgd_delete' ) ) . '" onclick="return window.confirm(\'Supprimer tous les membres de démonstration ?\');">Tout supprimer</a>';
	}
	echo '</p></div>';
}

add_action( 'admin_post_bqgd_create', 'bqgd_handle_create' );
function bqgd_handle_create() {
	check_admin_referer( 'bqgd_create' );

	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'bqg_render_profile' ) ) {
		wp_die( 'Action non autorisée.' );
	}

	bqgd_delete_all();
	$n = bqgd_create_all();

	wp_safe_redirect( admin_url( 'edit.php?post_type=bqg_membre&page=bqg-demo&bqgd=created&n=' . $n ) );
	exit;
}

add_action( 'admin_post_bqgd_delete', 'bqgd_handle_delete' );
function bqgd_handle_delete() {
	check_admin_referer( 'bqgd_delete' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Action non autorisée.' );
	}

	bqgd_delete_all();

	wp_safe_redirect( admin_url( 'edit.php?post_type=bqg_membre&page=bqg-demo&bqgd=deleted' ) );
	exit;
}

/* -------------------------------------------------------------------------
 * 2. Les membres fictifs
 * ---------------------------------------------------------------------- */

function bqgd_members() {
	return array(
		/* Bureau */
		array(
			'name'   => 'Hélène Marchand',
			'cats'   => array( 'bureau' ),
			'photo'  => array( '#7A3B46', '#E9D6CF' ),
			'role'   => 'Présidente',
			'desc'   => 'Ancienne directrice de la stratégie d\'un groupe industriel, elle préside l\'association depuis 2021.',
			'pro'    => 'Administratrice de sociétés, ancienne directrice de la stratégie',
			'eng'    => 'Elle anime le Bureau, représente l\'association auprès des partenaires et préside la cérémonie de remise du prix.',
			'cv'     => '<h4>Parcours</h4><ul><li>Directrice de la stratégie d\'un groupe industriel (2010 à 2020)</li><li>Conseillère au sein d\'un cabinet ministériel (2005 à 2009)</li><li>Ingénieure, diplômée d\'une grande école</li></ul><h4>Engagements</h4><p>Membre de plusieurs conseils d\'administration dans l\'industrie et l\'énergie.</p>',
			'pdf'    => true,
			'link'   => true,
		),
		array(
			'name'   => 'Antoine Delcourt',
			'cats'   => array( 'bureau', 'conseil-scientifique' ),
			'photo'  => array( '#3E4A5C', '#D9DEE6' ),
			'role'   => 'Vice-président',
			'desc'   => 'Professeur d\'économie industrielle, il fait le lien entre le Bureau et le Conseil scientifique.',
			'pro'    => 'Professeur d\'économie industrielle',
			'eng'    => 'Il siège dans les deux instances : il prépare le calendrier du prix avec le Bureau et coordonne l\'évaluation des candidatures avec le Conseil scientifique.',
			'cv'     => '<ul><li>Professeur des universités depuis 2008</li><li>Auteur de trois ouvrages sur la politique industrielle</li><li>Ancien expert auprès d\'une commission parlementaire</li></ul>',
			'pdf'    => true,
			'link'   => false,
		),
		array(
			'name'   => 'Camille Rousset',
			'cats'   => array( 'bureau' ),
			'photo'  => array( '#5A4636', '#EADFD3' ),
			'role'   => 'Secrétaire générale',
			'desc'   => 'Avocate en droit des affaires, elle veille au bon fonctionnement de l\'association.',
			'pro'    => 'Avocate associée en droit des affaires',
			'eng'    => 'Elle organise les assemblées, tient les registres et suit les conventions signées avec les partenaires.',
			'cv'     => '<p>Avocate au barreau depuis 2006, spécialisée en droit des sociétés et en contrôle des investissements étrangers.</p>',
			'pdf'    => false,
			'link'   => true,
		),
		array(
			'name'   => 'Bernard Lefèvre',
			'cats'   => array( 'bureau' ),
			'photo'  => null,
			'role'   => 'Trésorier',
			'desc'   => 'Expert-comptable, il suit les comptes de l\'association et la dotation du prix.',
			'pro'    => 'Expert-comptable et commissaire aux comptes',
			'eng'    => 'Il prépare le budget annuel, suit les dons des mécènes et présente les comptes à l\'assemblée générale.',
			'cv'     => '',
			'pdf'    => false,
			'link'   => false,
		),
		array(
			'name'   => 'Inès Garnier',
			'cats'   => array( 'bureau' ),
			'photo'  => array( '#2F5D62', '#D5E6E4' ),
			'role'   => 'Déléguée aux lauréats',
			'desc'   => 'Consultante en intelligence économique, elle accompagne les lauréats tout au long de l\'année.',
			'pro'    => 'Consultante en intelligence économique',
			'eng'    => 'Elle suit chaque promotion de lauréats, organise leurs rencontres avec les partenaires et anime le réseau des anciens.',
			'cv'     => '<ul><li>Consultante indépendante depuis 2015</li><li>Lauréate de la Bourse en 2014</li></ul>',
			'pdf'    => false,
			'link'   => true,
		),

		/* Conseil scientifique */
		array(
			'name'   => 'Jean-Luc Varenne',
			'cats'   => array( 'conseil-scientifique' ),
			'photo'  => array( '#4B3B5C', '#E2DAEA' ),
			'role'   => 'Président du Conseil scientifique',
			'desc'   => 'Historien de l\'économie, spécialiste de la politique industrielle française depuis 1945.',
			'pro'    => 'Historien de l\'économie, directeur de recherche',
			'eng'    => 'Il préside le jury, arbitre les délibérations et garantit l\'indépendance de l\'évaluation.',
			'cv'     => '<h4>Publications</h4><ul><li>Histoire de la planification industrielle</li><li>Les grands programmes et l\'État stratège</li></ul>',
			'pdf'    => true,
			'link'   => false,
		),
		array(
			'name'   => 'Sophie Albrecht',
			'cats'   => array( 'conseil-scientifique' ),
			'photo'  => array( '#1F4E6B', '#D3E2EC' ),
			'role'   => 'Membre du Conseil scientifique',
			'desc'   => 'Chercheuse en géopolitique de l\'énergie et des matières premières.',
			'pro'    => 'Chercheuse en géopolitique de l\'énergie',
			'eng'    => 'Elle évalue les travaux consacrés à l\'énergie et aux matières premières, et suit les lauréats de ces domaines.',
			'cv'     => '<p>Docteure en géographie, elle a travaillé dix ans dans le secteur de l\'énergie avant de rejoindre la recherche publique.</p>',
			'pdf'    => false,
			'link'   => true,
		),
		array(
			'name'   => 'Marc Ollivier',
			'cats'   => array( 'conseil-scientifique' ),
			'photo'  => array( '#4A5240', '#E0E4D8' ),
			'role'   => 'Membre du Conseil scientifique',
			'desc'   => 'Ingénieur, ancien responsable de programmes industriels de défense.',
			'pro'    => 'Ingénieur, ancien responsable de programmes de défense',
			'eng'    => 'Il apporte au jury son expérience des filières de défense et de l\'aéronautique.',
			'cv'     => '',
			'pdf'    => false,
			'link'   => false,
		),
		array(
			'name'   => 'Nadia Benali',
			'cats'   => array( 'conseil-scientifique' ),
			'photo'  => array( '#6B3A2E', '#EEDCD5' ),
			'role'   => 'Membre du Conseil scientifique',
			'desc'   => 'Économiste, spécialiste des chaînes de valeur mondiales et des dépendances stratégiques.',
			'pro'    => 'Économiste, maîtresse de conférences',
			'eng'    => 'Elle coordonne l\'évaluation des travaux sur le commerce international et les dépendances.',
			'cv'     => '<ul><li>Maîtresse de conférences depuis 2016</li><li>Coautrice d\'un rapport sur les dépendances industrielles</li></ul>',
			'pdf'    => false,
			'link'   => false,
		),
		array(
			'name'   => 'Paul-Henri Duval',
			'cats'   => array( 'conseil-scientifique' ),
			'photo'  => array( '#384B57', '#D7E0E5' ),
			'role'   => 'Membre du Conseil scientifique',
			'desc'   => 'Ancien diplomate, spécialiste des relations économiques internationales.',
			'pro'    => 'Ancien diplomate',
			'eng'    => 'Il éclaire le jury sur la dimension internationale des travaux et ouvre son réseau aux lauréats.',
			'cv'     => '',
			'pdf'    => false,
			'link'   => false,
		),
		array(
			'name'   => 'Élodie Fontaine',
			'cats'   => array( 'conseil-scientifique' ),
			'photo'  => array( '#5C4A3A', '#E8E0D6' ),
			'role'   => 'Membre du Conseil scientifique',
			'desc'   => '',
			'pro'    => '',
			'eng'    => '',
			'cv'     => '',
			'pdf'    => false,
			'link'   => false,
		),
	);
}

/* -------------------------------------------------------------------------
 * 3. Portraits et CV générés
 * ---------------------------------------------------------------------- */

/**
 * Un portrait stylisé (silhouette sur fond dégradé), au format des cartes.
 */
function bqgd_portrait( $dark, $light ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return '';
	}

	$w  = 600;
	$h  = 750;
	$im = imagecreatetruecolor( $w, $h );
	$c  = function ( $hex ) {
		return array( hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ) );
	};
	$a  = $c( $light );
	$b  = $c( $dark );

	// Fond : dégradé du clair vers une teinte plus soutenue.
	for ( $y = 0; $y < $h; $y++ ) {
		$k = $y / $h * 0.35;
		imageline( $im, 0, $y, $w, $y, imagecolorallocate( $im, (int) ( $a[0] + ( $b[0] - $a[0] ) * $k ), (int) ( $a[1] + ( $b[1] - $a[1] ) * $k ), (int) ( $a[2] + ( $b[2] - $a[2] ) * $k ) ) );
	}

	$body = imagecolorallocate( $im, $b[0], $b[1], $b[2] );
	$skin = imagecolorallocate( $im, min( 255, $b[0] + 70 ), min( 255, $b[1] + 70 ), min( 255, $b[2] + 70 ) );
	$halo = imagecolorallocatealpha( $im, 255, 255, 255, 90 );

	imagefilledellipse( $im, $w / 2, 300, 360, 360, $halo );
	imagefilledellipse( $im, $w / 2, $h + 40, 520, 520, $body );
	imagefilledrectangle( $im, $w / 2 - 50, 380, $w / 2 + 50, 520, $skin );
	imagefilledellipse( $im, $w / 2, 300, 210, 250, $skin );
	imagefilledellipse( $im, $w / 2, 210, 230, 130, $body );

	ob_start();
	imagepng( $im );
	$png = ob_get_clean();
	imagedestroy( $im );

	return $png;
}

/**
 * Un CV d'une page, écrit à la main : rien à télécharger ailleurs.
 */
function bqgd_pdf( $name ) {
	$ascii   = remove_accents( $name );
	$text    = 'BT /F1 24 Tf 72 760 Td (' . $ascii . ') Tj 0 -34 Td /F1 13 Tf (Curriculum vitae de demonstration, contenu fictif.) Tj 0 -22 Td (Bourse Jean-Michel Quatrepoint) Tj ET';
	$objects = array(
		'<< /Type /Catalog /Pages 2 0 R >>',
		'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
		'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
		"<< /Length " . strlen( $text ) . " >>\nstream\n" . $text . "\nendstream",
		'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
	);

	// Un peu de volume, pour afficher une taille réaliste.
	$pdf     = "%PDF-1.4\n" . str_repeat( '% Curriculum vitae de demonstration.' . "\n", 2500 );
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
 * Enregistre un fichier dans la médiathèque, marqué comme démo.
 */
function bqgd_attach( $filename, $bytes, $mime, $title ) {
	if ( '' === $bytes ) {
		return 0;
	}

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

	update_post_meta( $id, '_bqg_demo', 1 );

	if ( 0 === strpos( $mime, 'image/' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	}

	return (int) $id;
}

/* -------------------------------------------------------------------------
 * 4. Création et suppression : uniquement ce qui porte la marque « démo »
 * ---------------------------------------------------------------------- */

function bqgd_create_all() {
	$n = 0;

	// Les instances doivent exister (créées par BQP Gouvernance).
	if ( function_exists( 'bqg_maybe_seed_terms' ) ) {
		bqg_maybe_seed_terms();
	}

	foreach ( bqgd_members() as $i => $member ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'bqg_membre',
				'post_status' => 'publish',
				'post_title'  => $member['name'],
				'menu_order'  => $i,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		$slug = sanitize_title( $member['name'] );
		$meta = array(
			'_bqg_demo'        => 1,
			'_bqg_fonction'    => $member['role'],
			'_bqg_description' => $member['desc'],
			'_bqg_profession'  => $member['pro'],
			'_bqg_engagement'  => $member['eng'],
			'_bqg_cv_texte'    => $member['cv'],
			'_bqg_lien'        => $member['link'] ? 'https://example.org/linkedin/' . $slug : '',
		);

		if ( $member['photo'] ) {
			$meta['_bqg_photo_id'] = bqgd_attach( 'demo-portrait-' . $slug . '.png', bqgd_portrait( $member['photo'][0], $member['photo'][1] ), 'image/png', 'Portrait ' . $member['name'] . ' (démo)' );
		}

		if ( $member['pdf'] ) {
			$meta['_bqg_cv_id'] = bqgd_attach( 'cv-demo-' . $slug . '.pdf', bqgd_pdf( $member['name'] ), 'application/pdf', 'CV ' . $member['name'] . ' (démo)' );
		}

		foreach ( $meta as $key => $value ) {
			if ( '' !== (string) $value && 0 !== $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}

		$terms = array();
		foreach ( $member['cats'] as $cat ) {
			$term = get_term_by( 'slug', $cat, 'bqg_membre_cat' );
			if ( $term ) {
				$terms[] = (int) $term->term_id;
			}
		}
		wp_set_object_terms( $post_id, $terms, 'bqg_membre_cat' );

		$n++;
	}

	return $n;
}

function bqgd_delete_all() {
	foreach ( bqgd_ids( array( 'bqg_membre', 'attachment' ) ) as $id ) {
		'attachment' === get_post_type( $id ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
	}
}

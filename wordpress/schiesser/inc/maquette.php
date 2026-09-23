<?php
/**
 * Blocs de la maquette.
 *
 * Chaque section caractéristique de la maquette (catalogue des créations,
 * deux étages, savoir-faire, ligne du temps, galerie, carte du salon,
 * ascension, archives, comparateur, plan, affluence, trajets, formulaire…)
 * est un bloc que l'on modifie dans l'éditeur, sans code.
 *
 * Le HTML produit reprend exactement celui de la maquette : ses styles
 * (assets/css/schiesser.css) et ses animations (assets/js/maquette.js)
 * s'appliquent tels quels.
 *
 * Deux sortes de blocs :
 * - les blocs « section » : une section complète, avec son en-tête
 *   (N°, titre, note), son fond et son ancre ;
 * - les blocs « élément » : un étage, une étape, une date, une photo…
 *   On les ajoute, les déplace ou les supprime comme n'importe quel bloc.
 *
 * Édition : assets/js/blocs-maquette.js.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Déclaration des blocs                                               */
/* ------------------------------------------------------------------ */

/** Attributs WordPress à partir de valeurs par défaut (le type est déduit de la valeur). */
function schiesser_mq_attributs( $defauts ) {
	$attrs = array();
	foreach ( $defauts as $cle => $valeur ) {
		if ( is_bool( $valeur ) ) {
			$type = 'boolean';
		} elseif ( is_int( $valeur ) ) {
			$type = 'integer';
		} elseif ( is_array( $valeur ) ) {
			$type = 'object';
		} else {
			$type = 'string';
		}
		$attrs[ $cle ] = array(
			'type'    => $type,
			'default' => $valeur,
		);
	}
	return $attrs;
}

/** Réglages communs aux blocs « section » : en-tête, fond, ancre, filigrane. */
function schiesser_mq_attr_section( $fond = 'papier' ) {
	return array(
		'align'  => 'full',
		'numero' => '',
		'titre'  => '',
		'note'   => '',
		'fond'   => $fond,
		'ancre'  => '',
		'marque' => false,
	);
}

/** Une photo : identifiant dans la médiathèque, adresse, texte alternatif. */
function schiesser_mq_attr_image( $prefixe = 'image' ) {
	return array(
		$prefixe . 'Id'  => 0,
		$prefixe . 'Url' => '',
		$prefixe . 'Alt' => '',
	);
}

/**
 * Tous les blocs de la maquette.
 * nom => [ titre, description, icône, parent (blocs « élément »), attributs, rendu ].
 */
function schiesser_mq_blocs() {
	$s = 'schiesser_mq_attr_section';
	$i = 'schiesser_mq_attr_image';
	return array(
		/* ----- bandeaux ----- */
		'bandeau-live'   => array( 'Bandeau « ouvert / fermé »', 'Bandeau sombre sous la grande photo : état en direct (ouvert, fermé, fermeture dans…) et deux informations utiles.', 'clock', null,
			array( 'align' => 'full', 'c1Libelle' => 'Achat', 'c1Valeur' => 'Sur place, au comptoir', 'c1Detail' => '', 'c1Lien' => '', 'c1Auto' => '', 'c2Libelle' => 'Commander', 'c2Valeur' => '', 'c2Detail' => '', 'c2Lien' => '', 'c2Auto' => 'telephone' ) ),
		'vitrine-jour'   => array( 'Vitrine du jour', 'Bandeau « En vitrine aujourd’hui » : les produits du jour se modifient dans Réglages maison.', 'store', null,
			array( 'align' => 'full', 'titre' => 'En vitrine aujourd’hui' ) ),
		'chiffres'       => array( 'Chiffres clés', 'Bandeau de chiffres (année de fondation, générations…).', 'chart-bar', null,
			array( 'align' => 'full' ) ),
		'plaque'         => array( 'Plaque anniversaire', 'Plaque encadrée avec sceau, grand chiffre, dates et texte.', 'awards', null,
			array( 'align' => 'full', 'nombre' => '150', 'libelle' => 'ans de maison', 'dates' => '1870 · 2020', 'texte' => '' ) ),
		'separateur'     => array( 'Séparateur losanges', 'Trois losanges entre deux sections.', 'minus', null,
			array( 'align' => 'full' ) ),
		'appel'          => array( 'Appel final', 'Grande section sombre centrée : surtitre, titre, texte et deux boutons.', 'megaphone', null,
			array( 'align' => 'full', 'ancre' => '', 'surtitre' => '', 'titre' => '', 'texte' => '', 'b1Texte' => '', 'b1Lien' => '', 'b2Texte' => '', 'b2Lien' => '' ) ),

		/* ----- sections ----- */
		'catalogue'      => array( 'Catalogue des créations', 'Liste numérotée des produits avec aperçu photo au survol. Les produits viennent du menu Produits.', 'list-view', null,
			$s() + array( 'limite' => 6, 'source' => 'auto', 'produits' => '', 'apercu' => false ) ),
		'etages'         => array( 'Deux étages (cartes photo)', 'Grandes cartes photo avec un grand chiffre (0, 1) : la boutique, le salon…', 'building', null,
			$s( 'alterne' ) ),
		'savoir-faire'   => array( 'Savoir-faire (étapes au défilement)', 'Étapes numérotées : la photo change à mesure que l’on fait défiler.', 'hammer', null,
			$s( 'clair' ) ),
		'frise'          => array( 'Ligne du temps', 'Dates clés sur fond sombre : cartes à faire glisser, ou chronologie à onglets.', 'backup', null,
			array( 'align' => 'full', 'numero' => '', 'titre' => '', 'note' => '', 'ancre' => '', 'affichage' => 'cartes', 'indication' => 'Glissez pour explorer →', 'retenirLibelle' => 'À retenir' ) ),
		'adresse'        => array( 'Nous trouver (carte et adresse)', 'Carte interactive et fiche : adresse, horaires en direct, groupes, contact, boutons.', 'location', null,
			$s( 'alterne' ) + array( 'lieu' => '', 'carte' => true, 'b1Texte' => 'Nous écrire', 'b1Lien' => '', 'b2Texte' => 'Itinéraire', 'b2Lien' => '' ) ),
		'galerie'        => array( 'Galerie (visionneuse)', 'Grande photo avec légende, flèches et vignettes.', 'format-gallery', null,
			$s( 'sombre' ) ),
		'infos'          => array( 'Bon à savoir (pictogrammes)', 'Grille d’informations pratiques, chacune avec un pictogramme.', 'info-outline', null,
			$s( 'sable' ) ),
		'cartes'         => array( 'Cartes numérotées', 'Cartes 01, 02, 03 : « Passer commande » (avec bouton) ou « Principes ».', 'screenoptions', null,
			$s( 'clair' ) + array( 'modele' => 'commande' ) ),
		'intro'          => array( 'Introduction (accroche et texte)', 'Grande phrase en italique à gauche, paragraphes à droite.', 'editor-quote', null,
			$s() + array( 'lead' => '', 'suite' => false ) ),
		'ascension'      => array( 'Montée à l’étage (défilement)', 'Grande scène sombre : en faisant défiler, on passe d’un palier à l’autre (boutique, escalier, salon).', 'arrow-up-alt', null,
			array( 'align' => 'full', 'indication' => 'Faites défiler pour monter' ) ),
		'encart'         => array( 'Encart d’exception', 'Photo, surtitre, titre, sous-titre, texte, dates et mention : « La doyenne ».', 'star-filled', null,
			$s( 'sombre' ) + $i() + array( 'surtitre' => '', 'encartTitre' => '', 'sousTitre' => '', 'texte' => '', 'mention' => '' ) ),
		'carte-salon'    => array( 'Carte du salon (onglets)', 'Photo par rubrique, onglets, plats avec prix, suggestion du jour.', 'food', null,
			$s( 'alterne' ) + array( 'mention' => '', 'sSurtitre' => 'La suggestion du jour', 'sTitre' => '', 'sTexte' => '', 'sPied' => '', 'sPrix' => '' ) + $i( 's' ) ),
		'panneaux'       => array( 'Panneaux photo (dépliants)', 'Panneaux photo qui s’ouvrent au survol ou au clic.', 'columns', null,
			$s() + array( 'indication' => 'Cliquez ou survolez pour explorer' ) ),
		'moments'        => array( 'Moments de la journée', 'Choix d’un moment (07:30, 11:00…) : photo, heure, texte et conseil.', 'clock', null,
			$s( 'sombre' ) + array( 'conseilLibelle' => 'Ce que nous conseillons' ) ),
		'venir'          => array( 'Passer nous voir (photo et infos)', 'Photo, titre, texte, lignes d’informations et deux boutons.', 'location-alt', null,
			$s( 'clair' ) + $i() + array( 'encartTitre' => '', 'texte' => '', 'b1Texte' => '', 'b1Lien' => '', 'b2Texte' => '', 'b2Lien' => '' ) ),
		'origine'        => array( 'Récit avec lettrine et photo', 'Texte avec lettrine et citation en exergue, photo d’archive légendée.', 'book', null,
			$s() + $i() + array( 'legende' => '', 'figure' => 'Fig. 01' ) ),
		'archives'       => array( 'Archives (agrandissables)', 'Grille de photos d’archive, agrandies au clic.', 'images-alt2', null,
			$s( 'clair' ) + array( 'credit' => '' ) ),
		'avant-apres'    => array( 'Hier et aujourd’hui (comparateur)', 'Deux photos superposées, un curseur à faire glisser.', 'image-flip-horizontal', null,
			array( 'align' => 'full', 'numero' => '', 'titre' => '', 'note' => '', 'ancre' => '', 'indication' => 'Glissez pour comparer' ) ),
		'plan-horaires'  => array( 'Plan et horaires', 'Carte interactive avec la fiche de la maison, tableau des horaires.', 'calendar-alt', null,
			$s() + array( 'carte' => true, 'titreHoraires' => 'Nos horaires', 'b1Texte' => 'Itinéraire', 'b2Texte' => 'Agrandir', 'apercu' => false ) ),
		'affluence'      => array( 'Affluence (meilleur moment)', 'Graphique d’affluence par jour et par heure, heures calmes et animées calculées automatiquement.', 'chart-area', null,
			$s( 'sable' ) + array( 'debut' => 8, 'donnees' => schiesser_mq_affluence_defaut(), 'conseil' => 'Le matin, tout sort du four', 'apercu' => false ) ),
		'trajets'        => array( 'Composez votre trajet', 'Points de départ et itinéraires détaillés, étape par étape.', 'car', null,
			$s( 'sombre' ) + array( 'departLibelle' => 'Je pars de', 'boutonTexte' => 'Ouvrir l’itinéraire' ) ),
		'devanture'      => array( 'Grande photo légendée', 'Grande photo avec surtitre, titre et texte : « Reconnaître la maison ».', 'format-image', null,
			$s( 'clair' ) + $i() + array( 'surtitre' => '', 'encartTitre' => '', 'texte' => '' ) ),
		'faq'            => array( 'Questions fréquentes', 'Questions dépliables. Elles sont aussi transmises à Google (données structurées).', 'editor-help', null,
			$s() ),
		'formulaire'     => array( 'Formulaire de contact', 'Formulaire (nom, e-mail, téléphone, message) envoyé à l’adresse des Réglages maison, et moyens de contact à côté.', 'email', null,
			$s() + array( 'lNom' => 'Nom', 'lEmail' => 'E-mail', 'lTel' => 'Téléphone', 'lMessage' => 'Votre message', 'boutonTexte' => 'Envoyer', 'mentionLegale' => 'Vos informations servent uniquement à traiter votre demande.', 'okTitre' => 'Votre message est envoyé', 'okTexte' => 'Merci. Nous vous répondons sous un à deux jours ouvrés.' ) ),
		'fiche-contact'  => array( 'Fiche de contact', 'Encadré : surtitre, nom, texte et lignes (e-mail, téléphone, adresse…).', 'id', null,
			$s( 'sable' ) + array( 'surtitre' => '', 'encartTitre' => '', 'texte' => '' ) ),

		/* ----- éléments ----- */
		'chiffre'        => array( 'Chiffre', 'Un chiffre et son libellé.', 'editor-ol', array( 'schiesser/chiffres', 'schiesser/encart' ),
			array( 'valeur' => '', 'libelle' => '' ) ),
		'etage'          => array( 'Étage', 'Carte photo avec grand chiffre, titre, texte et lien.', 'building', array( 'schiesser/etages' ),
			$i() + array( 'numero' => '0', 'surtitre' => '', 'titre' => '', 'texte' => '', 'lienTexte' => '', 'lienUrl' => '' ) ),
		'geste'          => array( 'Étape du savoir-faire', 'Titre, texte et photo d’une étape.', 'yes', array( 'schiesser/savoir-faire' ),
			$i() + array( 'titre' => '', 'texte' => '' ) ),
		'date'           => array( 'Date', 'Une année, un titre, un texte, une photo.', 'calendar', array( 'schiesser/frise' ),
			$i() + array( 'annee' => '', 'libelle' => '', 'titre' => '', 'texte' => '', 'retenir' => '' ) ),
		'vue'            => array( 'Photo de galerie', 'Une photo, son titre et sa légende.', 'format-image', array( 'schiesser/galerie' ),
			$i() + array( 'titre' => '', 'texte' => '' ) ),
		'info'           => array( 'Information', 'Pictogramme, titre et texte.', 'info-outline', array( 'schiesser/infos' ),
			array( 'icone' => 'horloge', 'titre' => '', 'texte' => '' ) ),
		'carte'          => array( 'Carte numérotée', 'Titre, texte et bouton facultatif.', 'screenoptions', array( 'schiesser/cartes' ),
			array( 'titre' => '', 'texte' => '', 'boutonTexte' => '', 'boutonLien' => '', 'sombre' => false ) ),
		'palier'         => array( 'Palier', 'Un niveau de la montée : chiffre, libellé, titre, texte, photo.', 'arrow-up-alt2', array( 'schiesser/ascension' ),
			$i() + array( 'niveau' => '0', 'libelle' => '', 'surtitre' => '', 'titre' => '', 'texte' => '' ) ),
		'rubrique'       => array( 'Rubrique de la carte', 'Nom de la rubrique (onglet), photo et plats.', 'category', array( 'schiesser/carte-salon' ),
			$i() + array( 'nom' => '' ) ),
		'plat'           => array( 'Plat', 'Nom, prix, description et mention (Signature, En saison…).', 'carrot', array( 'schiesser/rubrique' ),
			array( 'nom' => '', 'prix' => '', 'description' => '', 'mention' => '' ) ),
		'panneau'        => array( 'Panneau photo', 'Photo, titre et texte d’un panneau.', 'format-image', array( 'schiesser/panneaux' ),
			$i() + array( 'titre' => '', 'texte' => '' ) ),
		'moment'         => array( 'Moment', 'Heure, libellé, photo, titre, sous-titre, texte et conseil.', 'clock', array( 'schiesser/moments' ),
			$i() + array( 'heure' => '', 'libelle' => '', 'titre' => '', 'sousTitre' => '', 'texte' => '', 'conseil' => '' ) ),
		'ligne'          => array( 'Ligne d’information', 'Libellé et valeur (adresse, horaires, téléphone… automatiques si vous le souhaitez).', 'editor-justify', array( 'schiesser/venir', 'schiesser/formulaire', 'schiesser/fiche-contact', 'schiesser/adresse' ),
			array( 'libelle' => '', 'valeur' => '', 'detail' => '', 'lien' => '', 'auto' => '' ) ),
		'exergue'        => array( 'Citation en exergue', 'Citation en italique avec son auteur.', 'format-quote', array( 'schiesser/origine' ),
			array( 'texte' => '', 'auteur' => '' ) ),
		'archive'        => array( 'Photo d’archive', 'Photo, titre, date et description (affichée en grand).', 'format-image', array( 'schiesser/archives' ),
			$i() + array( 'titre' => '', 'meta' => '', 'description' => '' ) ),
		'comparaison'    => array( 'Comparaison', 'Onglet, photo d’hier et photo d’aujourd’hui.', 'image-flip-horizontal', array( 'schiesser/avant-apres' ),
			$i( 'avant' ) + $i( 'apres' ) + array( 'onglet' => '', 'avantLibelle' => '', 'apresLibelle' => 'Aujourd’hui', 'vieillir' => true ) ),
		'trajet'         => array( 'Point de départ', 'Point de départ et son itinéraire.', 'location', array( 'schiesser/trajets' ),
			array( 'icone' => 'train', 'depart' => '', 'detail' => '', 'titre' => '', 'sousTitre' => '', 'duree' => '', 'changements' => '', 'marche' => '', 'astuce' => '' ) ),
		'etape'          => array( 'Étape du trajet', 'Pictogramme, titre, texte et durée.', 'arrow-right-alt', array( 'schiesser/trajet' ),
			array( 'icone' => 'tram', 'titre' => '', 'texte' => '', 'duree' => '' ) ),
		'question'       => array( 'Question', 'Une question et sa réponse.', 'editor-help', array( 'schiesser/faq' ),
			array( 'question' => '', 'reponse' => '' ) ),
	);
}

add_action( 'init', function () {
	wp_register_script(
		'schiesser-blocs-maquette',
		SCHIESSER_URI . '/assets/js/blocs-maquette.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-i18n', 'wp-server-side-render' ),
		SCHIESSER_VERSION,
		true
	);
	wp_register_style( 'schiesser-editeur-maquette', SCHIESSER_URI . '/assets/css/editeur-maquette.css', array(), SCHIESSER_VERSION );

	foreach ( schiesser_mq_blocs() as $nom => $b ) {
		$args = array(
			'api_version'          => 3,
			'title'                => $b[0],
			'description'          => $b[1],
			'icon'                 => $b[2],
			'category'             => 'schiesser',
			'attributes'           => schiesser_mq_attributs( $b[4] ),
			'supports'             => array(
				'html'  => false,
				'align' => isset( $b[4]['align'] ) ? array( 'full' ) : false,
			),
			'editor_script_handles' => array( 'schiesser-blocs-maquette' ),
			'editor_style_handles'  => array( 'schiesser-editeur-maquette' ),
			'style_handles'         => array( 'schiesser-site' ),
			'render_callback'      => function ( $attributes, $content, $block ) use ( $nom ) {
				$fonction = 'schiesser_mq_rendu_' . str_replace( '-', '_', $nom );
				return function_exists( $fonction ) ? $fonction( $attributes, $content, $block ) : '';
			},
		);
		if ( $b[3] ) {
			$args['parent'] = $b[3];
		}
		register_block_type( 'schiesser/' . $nom, $args );
	}
}, 20 );

/* Données des Réglages maison utiles aux aperçus de l'éditeur. */
add_action( 'enqueue_block_editor_assets', function () {
	$e      = schiesser_mq_etat();
	$pictos = array();
	foreach ( schiesser_mq_pictos() as $cle => $p ) {
		$pictos[ $cle ] = array( $p[0], $p[1] );
	}
	// Produits proposés dans le bloc Catalogue (les fiches WooCommerce s'il est actif).
	$produits = array();
	$types    = array( SCHIESSER_PRODUIT );
	if ( function_exists( 'schiesser_woo_actif' ) && schiesser_woo_actif() ) {
		$types[] = 'product';
	}
	foreach ( get_posts( array(
		'post_type'   => $types,
		'post_status' => 'publish',
		'numberposts' => 200,
		'orderby'     => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) ) as $p ) {
		$produits[] = array(
			'id'  => $p->ID,
			'nom' => html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ) . ( 'product' === $p->post_type ? ' (WooCommerce)' : '' ),
		);
	}
	wp_localize_script( 'schiesser-blocs-maquette', 'SCHIESSER_ED', array(
		'produits'     => $produits,
		'pictos'       => $pictos,
		'vitrine'      => array_values( array_filter( array_map( 'trim', (array) schiesser_reglage( 'vitrine' ) ) ) ),
		'rue'          => schiesser_reglage( 'rue' ),
		'ville'        => schiesser_reglage( 'ville' ),
		'adresseLigne' => implode( ', ', array_filter( schiesser_adresse_lignes() ) ),
		'telephone'    => schiesser_reglage( 'telephone' ),
		'email'        => schiesser_reglage( 'email' ),
		'aujourdhui'   => $e['aujourdhui'],
		'demain'       => $e['demain'],
	) );
} );

/* ------------------------------------------------------------------ */
/* Outils de rendu                                                     */
/* ------------------------------------------------------------------ */

/** Texte d'un bloc : mise en forme simple et liens autorisés, codes courts exécutés. */
function schiesser_mq_texte( $html ) {
	$propre = wp_kses( (string) $html, array(
		'a'      => array( 'href' => true, 'target' => true, 'rel' => true ),
		'em'     => array(),
		'strong' => array(),
		'b'      => array(),
		'i'      => array(),
		'br'     => array(),
	) );
	return do_shortcode( $propre );
}

/** Texte sans aucune balise (attributs HTML, légendes). */
function schiesser_mq_brut( $html ) {
	return schiesser_texte_brut( (string) $html );
}

/** Numéro sur deux chiffres : 1 → « 01 ». */
function schiesser_mq_num( $n ) {
	return str_pad( (string) $n, 2, '0', STR_PAD_LEFT );
}

/** Année mise en forme comme dans la maquette : « 18<span>70</span> ». */
function schiesser_mq_annee( $annee ) {
	$annee = trim( wp_strip_all_tags( (string) $annee ) );
	if ( preg_match( '/^\d{4}$/', $annee ) ) {
		return substr( $annee, 0, 2 ) . '<span>' . substr( $annee, 2 ) . '</span>';
	}
	return esc_html( $annee );
}

/** Éléments (blocs enfants) d'un bloc, avec leurs réglages. */
function schiesser_mq_enfants( $block ) {
	$liste = array();
	if ( $block instanceof WP_Block && $block->inner_blocks ) {
		foreach ( $block->inner_blocks as $enfant ) {
			$liste[] = $enfant;
		}
	}
	return $liste;
}

/**
 * Photo d'un bloc : responsive si elle vient de la médiathèque, sinon adresse simple.
 *
 * @param array  $a       Réglages du bloc.
 * @param string $prefixe Préfixe des réglages (image, avant, apres, s).
 * @param string $taille  Taille WordPress.
 * @param array  $attrs   Attributs HTML supplémentaires (class, data-…).
 */
function schiesser_mq_image( $a, $prefixe = 'image', $taille = 'large', $attrs = array() ) {
	$id  = (int) ( $a[ $prefixe . 'Id' ] ?? 0 );
	$url = (string) ( $a[ $prefixe . 'Url' ] ?? '' );
	$alt = trim( wp_strip_all_tags( (string) ( $a[ $prefixe . 'Alt' ] ?? '' ) ) );
	if ( '' === $alt && $id ) {
		$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	}
	if ( array_key_exists( 'alt', $attrs ) ) {
		$alt = $attrs['alt'];
	}
	$attrs = array_merge( array( 'loading' => 'lazy', 'decoding' => 'async' ), $attrs, array( 'alt' => $alt ) );
	if ( $id && wp_attachment_is_image( $id ) ) {
		return wp_get_attachment_image( $id, $taille, false, $attrs );
	}
	if ( '' === $url ) {
		return '';
	}
	$html = '<img src="' . esc_url( $url ) . '"';
	foreach ( $attrs as $cle => $valeur ) {
		$html .= ' ' . esc_attr( $cle ) . '="' . esc_attr( $valeur ) . '"';
	}
	return $html . '>';
}

/** Adresse d'une photo (pour les agrandissements). */
function schiesser_mq_image_url( $a, $prefixe = 'image', $taille = 'full' ) {
	$id = (int) ( $a[ $prefixe . 'Id' ] ?? 0 );
	if ( $id && wp_attachment_is_image( $id ) ) {
		return (string) wp_get_attachment_image_url( $id, $taille );
	}
	return (string) ( $a[ $prefixe . 'Url' ] ?? '' );
}

/** Le bloc a-t-il une photo ? */
function schiesser_mq_a_image( $a, $prefixe = 'image' ) {
	return ! empty( $a[ $prefixe . 'Id' ] ) || ! empty( $a[ $prefixe . 'Url' ] );
}

/** Fonds proposés : nom => classe de la maquette. */
function schiesser_mq_fonds() {
	return array(
		'papier'  => '',
		'clair'   => 'bg-soft',
		'alterne' => 'bg-alt',
		'sable'   => 'bg-deep',
		'sombre'  => 'bg-ink',
	);
}

/** Filigrane « S » des sections sombres. */
function schiesser_mq_marque() {
	return '<span class="x-mark" aria-hidden="true"><svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="30" fill="none" stroke="currentColor" stroke-width="1"/><text x="32" y="43" text-anchor="middle" font-family="Bodoni Moda, serif" font-weight="600" font-size="31" fill="currentColor">S</text></svg></span>';
}

/** Petit sceau « S » (plaque, page 404). */
function schiesser_mq_sceau() {
	return '<span class="x-seal" aria-hidden="true"><svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="30" fill="none" stroke="currentColor" stroke-width="1"/><circle cx="32" cy="32" r="25.5" fill="none" stroke="currentColor" stroke-width="0.6" stroke-dasharray="1 3"/><text x="32" y="42.5" text-anchor="middle" font-family="Bodoni Moda, serif" font-weight="600" font-size="30" fill="currentColor">S</text></svg></span>';
}

/** En-tête de section : N° ——— / titre / note. */
function schiesser_mq_entete( $a ) {
	$titre  = trim( wp_strip_all_tags( (string) ( $a['titre'] ?? '' ) ) );
	$numero = trim( (string) ( $a['numero'] ?? '' ) );
	if ( '' === $titre && '' === $numero ) {
		return '';
	}
	$html = '<div class="sec-head rv"><span class="idx' . ( '' === $numero ? ' idx--vide' : '' ) . '"><i class="x-diamond"></i>' . esc_html( $numero ) . '</span>';
	if ( '' !== $titre ) {
		$html .= '<h2>' . schiesser_kses_titre( $a['titre'] ) . '</h2>';
	}
	if ( '' !== trim( (string) ( $a['note'] ?? '' ) ) ) {
		$html .= '<p class="note">' . schiesser_kses_titre( $a['note'] ) . '</p>';
	}
	return $html . '</div>';
}

/**
 * Section complète de la maquette : <section class="sec …"><div class="wrap"> en-tête + contenu.
 *
 * @param array  $a       Réglages du bloc (fond, ancre, numéro, titre, note, filigrane).
 * @param string $contenu HTML du contenu.
 * @param string $classes Classes supplémentaires de la section.
 */
function schiesser_mq_section( $a, $contenu, $classes = '' ) {
	if ( ! empty( $a['apercu'] ) ) {
		return $contenu; // aperçu dans l'éditeur : l'en-tête y est déjà modifiable
	}
	$fonds  = schiesser_mq_fonds();
	$fond   = isset( $fonds[ $a['fond'] ?? '' ] ) ? $a['fond'] : 'papier';
	$attrs  = array( 'class' => trim( 'sec ' . $fonds[ $fond ] . ' ' . $classes ) );
	$ancre  = sanitize_title( $a['ancre'] ?? '' );
	if ( '' !== $ancre ) {
		$attrs['id'] = $ancre;
	}
	$marque = ( 'sombre' === $fond && ! empty( $a['marque'] ) ) ? schiesser_mq_marque() : '';
	return '<section ' . get_block_wrapper_attributes( $attrs ) . '>' . $marque . '<div class="wrap">' . schiesser_mq_entete( $a ) . $contenu . '</div></section>';
}

/** Bouton de la maquette : btn-kir (vert), btn-line (contour), btn-solid (crème), btn-ghost (contour clair). */
function schiesser_mq_bouton( $texte, $lien, $classe = 'btn-line', $fleche = false ) {
	$texte = trim( wp_strip_all_tags( (string) $texte ) );
	if ( '' === $texte ) {
		return '';
	}
	$lien    = (string) $lien;
	$externe = 0 === strpos( $lien, 'http' ) && false === strpos( $lien, (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	return '<a href="' . esc_url( $lien ?: '#' ) . '" class="btn ' . esc_attr( $classe ) . '"' . ( $externe ? ' target="_blank" rel="noopener"' : '' ) . '>'
		. ( $fleche ? '<span>' . esc_html( $texte ) . '</span> <span class="a" aria-hidden="true">→</span>' : esc_html( $texte ) )
		. '</a>';
}

/** Lien de courriel, d'itinéraire et de carte à partir des Réglages maison. */
function schiesser_mq_liens_maison() {
	$email = schiesser_reglage( 'email' );
	$adr   = trim( implode( ' ', array_filter( array( schiesser_reglage( 'nom_etablissement' ), schiesser_reglage( 'rue' ), schiesser_reglage( 'code_postal' ), schiesser_reglage( 'ville' ) ) ) ) );
	$lat   = schiesser_reglage( 'latitude' );
	$lng   = schiesser_reglage( 'longitude' );
	$cible = ( is_numeric( $lat ) && is_numeric( $lng ) ) ? $lat . ',' . $lng : $adr;
	return array(
		'email'      => $email ? 'mailto:' . $email : '',
		'itineraire' => 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $cible ),
		'carte'      => schiesser_reglage( 'lien_maps' ) ?: 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $adr ),
	);
}

/**
 * État d'ouverture actuel (heure de la boutique) : ouvert ou non, compte à rebours.
 *
 * @return array{ouvert:bool, texte:string, duree:string, aujourdhui:string, demain:string, fermeture:string}
 */
function schiesser_mq_etat() {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}
	$h    = schiesser_horaires_js();
	$now  = current_datetime();
	$jour = (int) $now->format( 'w' );
	$heure = (int) $now->format( 'G' ) + (int) $now->format( 'i' ) / 60;
	$fmt  = function ( $x ) {
		return sprintf( '%02d:%02d', floor( $x ), round( fmod( $x, 1 ) * 60 ) );
	};
	$plage  = $h[ $jour ] ?? null;
	$ouvert = $plage && $heure >= $plage[0] && $heure < $plage[1];
	if ( $ouvert ) {
		$minutes = (int) round( ( $plage[1] - $heure ) * 60 );
		$texte   = 'Fermeture dans';
	} else {
		$minutes = null;
		if ( $plage && $heure < $plage[0] ) {
			$minutes = (int) round( ( $plage[0] - $heure ) * 60 );
		} else {
			for ( $k = 1; $k <= 7; $k++ ) {
				$suivant = $h[ ( $jour + $k ) % 7 ] ?? null;
				if ( $suivant ) {
					$minutes = (int) round( ( 24 - $heure + 24 * ( $k - 1 ) + $suivant[0] ) * 60 );
					break;
				}
			}
		}
		$texte = 'Ouverture dans';
	}
	$duree = '';
	if ( null !== $minutes ) {
		$hh    = intdiv( $minutes, 60 );
		$duree = $hh ? $hh . 'h' . sprintf( '%02d', $minutes % 60 ) : ( $minutes % 60 ) . ' min';
	}
	$demain = $h[ ( $jour + 1 ) % 7 ] ?? null;
	return $memo = array(
		'ouvert'     => (bool) $ouvert,
		'texte'      => $texte,
		'duree'      => $duree,
		'aujourdhui' => $plage ? $fmt( $plage[0] ) . ' – ' . $fmt( $plage[1] ) : 'Fermé aujourd’hui',
		'demain'     => 'Demain · ' . ( $demain ? $fmt( $demain[0] ) . ' – ' . $fmt( $demain[1] ) : 'fermé' ),
		'fermeture'  => $plage ? $fmt( $plage[1] ) : '',
		'plage'      => $plage ? $fmt( $plage[0] ) . '–' . $fmt( $plage[1] ) : '',
	);
}

/** « Ouvert · 18:30 » / « Fermé » (fiche de l'accueil). */
function schiesser_mq_etat_court() {
	$e = schiesser_mq_etat();
	return $e['ouvert'] ? 'Ouvert · ' . $e['fermeture'] : 'Fermé';
}

/** « Ouvert · 07:30–18:30 » (carte, tableau des horaires). */
function schiesser_mq_etat_plage() {
	$e = schiesser_mq_etat();
	return ( $e['ouvert'] ? 'Ouvert' : 'Fermé' ) . ( $e['plage'] ? ' · ' . $e['plage'] : '' );
}

/**
 * Valeur d'une ligne d'information : saisie, ou automatique depuis les Réglages maison.
 *
 * @return array{0:string,1:string} [ valeur HTML, détail HTML ]
 */
function schiesser_mq_ligne_valeurs( $a, $contexte = '' ) {
	$tel    = schiesser_reglage( 'telephone' );
	$email  = schiesser_reglage( 'email' );
	$adr    = schiesser_adresse_lignes();
	$detail = trim( (string) ( $a['detail'] ?? '' ) ) !== '' ? schiesser_mq_texte( $a['detail'] ) : '';
	$lien_t = $tel ? '<a href="' . esc_url( schiesser_lien_tel() ) . '">' . esc_html( $tel ) . '</a>' : '';
	$lien_e = $email ? '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>' : '';
	switch ( $a['auto'] ?? '' ) {
		case 'adresse':
			return array( esc_html( $adr[0] ), $detail ?: esc_html( $adr[1] ) );
		case 'adresse-ligne':
			return array( esc_html( implode( ', ', array_filter( array( $adr[0], trim( schiesser_reglage( 'code_postal' ) . ' ' . schiesser_reglage( 'ville' ) ) ) ) ) ), $detail );
		case 'horaires':
			$etat = 'vinfo' === $contexte
				? '<span class="live js-live-court' . ( schiesser_mq_etat()['ouvert'] ? '' : ' shut' ) . '" data-nosnippet>' . esc_html( schiesser_mq_etat_court() ) . '</span>'
				: '<span class="js-aujourdhui" data-nosnippet>' . esc_html( schiesser_mq_etat()['aujourdhui'] ) . '</span> ';
			return array( $etat, $detail ?: esc_html( schiesser_horaires_resume() ) );
		case 'contact':
			return array( $lien_t, $detail ?: $lien_e );
		case 'telephone':
			return array( $lien_t, $detail );
		case 'email':
			return array( $lien_e, $detail );
	}
	$valeur = schiesser_mq_texte( $a['valeur'] ?? '' );
	if ( ! empty( $a['lien'] ) && '' !== $valeur ) {
		$valeur = '<a href="' . esc_url( $a['lien'] ) . '">' . wp_strip_all_tags( $valeur ) . '</a>';
	}
	return array( $valeur, $detail );
}

/* ------------------------------------------------------------------ */
/* Pictogrammes                                                        */
/* ------------------------------------------------------------------ */

/** Pictogrammes de la maquette (tracés SVG) : nom => [ libellé, tracé ]. */
function schiesser_mq_pictos() {
	return array(
		'conservation'  => array( 'Conservation', '<path d="M12 3v18M5 8l7-5 7 5v8l-7 5-7-5z"/>' ),
		'avion'         => array( 'Voyage, avion', '<path d="M10 20l2-5 2 5 1.5-1-1-5.5 6-2.5a1.6 1.6 0 000-3l-6 2.5-4-4.5-1.5.6 2 4.6-3.5 1.4-2-1.6-1.2.6 2 3 3 1"/>' ),
		'gateau'        => array( 'Gâteau', '<path d="M4 20h16M4 20v-6a2 2 0 012-2h12a2 2 0 012 2v6M8 12V9M12 12V9M16 12V9"/>' ),
		'coffret'       => array( 'Coffret, cadeau', '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M8 6V4h8v2M3 11h18"/>' ),
		'paiement'      => array( 'Paiement', '<rect x="2.5" y="6" width="19" height="12" rx="2"/><path d="M2.5 10h19"/>' ),
		'horloge'       => array( 'Horloge', '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>' ),
		'langues'       => array( 'Langues', '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/>' ),
		'accessibilite' => array( 'Accessibilité', '<circle cx="12" cy="12" r="9"/><path d="M9 12h6M12 9v6"/>' ),
		'emporter'      => array( 'À emporter', '<path d="M6 3h12v5a6 6 0 01-12 0zM9 14h6M12 14v5M9 21h6"/>' ),
		'repere'        => array( 'Repère, lieu', '<path d="M12 21s-7-4.5-7-10a7 7 0 0114 0c0 5.5-7 10-7 10z"/><circle cx="12" cy="11" r="2.5"/>' ),
		'train'         => array( 'Train', '<rect x="4" y="4" width="16" height="12" rx="2"/><path d="M4 11h16M7 20l2-4M17 20l-2-4"/>' ),
		'tram'          => array( 'Tram', '<rect x="5" y="3" width="14" height="14" rx="2"/><path d="M5 9h14M8 17l-2 4M16 17l2 4M9 21h6"/>' ),
		'marche'        => array( 'Marche', '<circle cx="13" cy="4" r="1.8"/><path d="M11 21l1.5-6-3-2.5 1-4.5 3.5 2 3 1M9.5 12.5L7 16M15.5 15l1.5 6"/>' ),
		'voiture'       => array( 'Voiture', '<path d="M5 17h14M4 13l1.6-4.4A2 2 0 017.5 7h9a2 2 0 011.9 1.6L20 13v4a1 1 0 01-1 1h-1a1 1 0 01-1-1v-1H7v1a1 1 0 01-1 1H5a1 1 0 01-1-1z"/>' ),
		'bus'           => array( 'Bus', '<rect x="4" y="4" width="16" height="13" rx="2"/><path d="M4 11h16M7 21l1.5-4M17 21l-1.5-4"/>' ),
		'boutique'      => array( 'Boutique', '<path d="M4 9h16v10a1 1 0 01-1 1H5a1 1 0 01-1-1zM4 9l1.5-5h13L20 9M9 20v-6h6v6"/>' ),
	);
}

function schiesser_mq_picto( $nom ) {
	$p = schiesser_mq_pictos();
	$t = $p[ $nom ][1] ?? $p['horloge'][1];
	return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $t . '</svg>';
}

/** Affluence par défaut (maquette) : jour => 11 valeurs de 0 à 100, de 8 h à 18 h. */
function schiesser_mq_affluence_defaut() {
	return array(
		'1' => array( 30, 50, 40, 55, 68, 60, 72, 66, 52, 40, 26 ),
		'2' => array( 32, 52, 42, 56, 70, 62, 74, 68, 54, 42, 28 ),
		'3' => array( 34, 54, 44, 58, 72, 64, 76, 70, 56, 44, 30 ),
		'4' => array( 36, 56, 46, 60, 74, 66, 78, 72, 58, 46, 32 ),
		'5' => array( 40, 62, 50, 66, 80, 72, 86, 80, 64, 52, 36 ),
		'6' => array( 45, 70, 60, 78, 88, 82, 95, 90, 74, 58, 0 ),
		'0' => array( 0, 0, 35, 48, 60, 66, 72, 62, 48, 0, 0 ),
	);
}

/* ------------------------------------------------------------------ */
/* Rendu des bandeaux                                                  */
/* ------------------------------------------------------------------ */

function schiesser_mq_rendu_bandeau_live( $a ) {
	$e    = schiesser_mq_etat();
	$html = '<section ' . get_block_wrapper_attributes( array( 'class' => 'live' ) ) . '><div class="wrap">';
	$html .= '<div class="lv-main" data-nosnippet><span class="lv-dot js-lv-dot' . ( $e['ouvert'] ? '' : ' shut' ) . '"></span>'
		. '<span class="lv-state js-lv-etat">' . ( $e['ouvert'] ? 'Ouvert' : 'Fermé' ) . '</span>'
		. '<span class="lv-count js-lv-compte">' . esc_html( $e['texte'] ) . ' <b>' . esc_html( $e['duree'] ) . '</b></span></div>';
	foreach ( array( 1, 2 ) as $n ) {
		$auto = $a[ 'c' . $n . 'Auto' ] ?? '';
		if ( 'aujourdhui' === $auto ) {
			$valeur = '<span class="js-aujourdhui" data-nosnippet>' . esc_html( $e['aujourdhui'] ) . '</span>';
			$detail = '<span class="js-demain" data-nosnippet>' . esc_html( $e['demain'] ) . '</span>';
		} else {
			list( $valeur, $detail ) = schiesser_mq_ligne_valeurs( array(
				'valeur' => $a[ 'c' . $n . 'Valeur' ] ?? '',
				'detail' => $a[ 'c' . $n . 'Detail' ] ?? '',
				'lien'   => $a[ 'c' . $n . 'Lien' ] ?? '',
				'auto'   => $auto,
			) );
		}
		if ( '' === trim( wp_strip_all_tags( $a[ 'c' . $n . 'Libelle' ] ?? '' ) ) && '' === $valeur ) {
			continue;
		}
		$html .= '<div class="lv-cell"><div class="k">' . esc_html( schiesser_mq_brut( $a[ 'c' . $n . 'Libelle' ] ?? '' ) ) . '</div><div class="v">' . $valeur . ( $detail ? '<small>' . $detail . '</small>' : '' ) . '</div></div>';
	}
	return $html . '</div></section>';
}

function schiesser_mq_rendu_vitrine_jour( $a ) {
	$produits = array_values( array_filter( array_map( 'trim', (array) schiesser_reglage( 'vitrine' ) ) ) );
	if ( ! $produits ) {
		return '';
	}
	$jours = array( 'dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi' );
	$html  = '<div ' . get_block_wrapper_attributes( array( 'class' => 'vband' ) ) . '><div class="wrap">';
	$html .= '<div class="vt"><span class="dot"></span><b>' . esc_html( schiesser_mq_brut( $a['titre'] ) ) . '</b></div><ul>';
	foreach ( $produits as $i => $p ) {
		$html .= '<li><b>' . schiesser_mq_num( $i + 1 ) . '</b>' . esc_html( $p ) . '</li>';
	}
	$html .= '</ul><div class="vday js-jour" data-nosnippet>' . esc_html( $jours[ (int) current_datetime()->format( 'w' ) ] ) . '</div>';
	return $html . '</div></div>';
}

function schiesser_mq_rendu_chiffres( $a, $content, $block ) {
	$items = '';
	foreach ( schiesser_mq_enfants( $block ) as $c ) {
		$items .= '<div class="sb"><div class="n">' . esc_html( schiesser_mq_brut( $c->attributes['valeur'] ) ) . '</div><div class="l">' . esc_html( schiesser_mq_brut( $c->attributes['libelle'] ) ) . '</div></div>';
	}
	return $items ? '<div ' . get_block_wrapper_attributes( array( 'class' => 'sband x-dia' ) ) . '><div class="wrap">' . $items . '</div></div>' : '';
}

function schiesser_mq_rendu_plaque( $a ) {
	return '<section ' . get_block_wrapper_attributes( array( 'class' => 'sec-plate' ) ) . '><div class="wrap"><div class="x-plate rv">'
		. '<div class="rule">' . schiesser_mq_sceau() . '</div>'
		. '<div class="n">' . esc_html( schiesser_mq_brut( $a['nombre'] ) ) . '</div>'
		. '<div class="l">' . esc_html( schiesser_mq_brut( $a['libelle'] ) ) . '</div>'
		. ( '' !== trim( $a['dates'] ) ? '<div class="d">' . esc_html( schiesser_mq_brut( $a['dates'] ) ) . '</div>' : '' )
		. ( '' !== trim( $a['texte'] ) ? '<p>' . schiesser_mq_texte( $a['texte'] ) . '</p>' : '' )
		. '</div></div></section>';
}

function schiesser_mq_rendu_separateur() {
	return '<div ' . get_block_wrapper_attributes( array( 'class' => 'wrap' ) ) . ' aria-hidden="true"><div class="x-sep"><i class="x-diamond"></i><i class="x-diamond"></i><i class="x-diamond"></i></div></div>';
}

function schiesser_mq_rendu_appel( $a ) {
	$attrs = array( 'class' => 'cta' );
	$ancre = sanitize_title( $a['ancre'] ?? '' );
	if ( $ancre ) {
		$attrs['id'] = $ancre;
	}
	$html = '<section ' . get_block_wrapper_attributes( $attrs ) . '><div class="wrap">';
	if ( '' !== trim( $a['surtitre'] ) ) {
		$html .= '<p class="eyebrow">' . esc_html( schiesser_mq_brut( $a['surtitre'] ) ) . '</p>';
	}
	$html .= '<h2>' . schiesser_kses_titre( $a['titre'] ) . '</h2>';
	if ( '' !== trim( $a['texte'] ) ) {
		$html .= '<p>' . schiesser_mq_texte( $a['texte'] ) . '</p>';
	}
	$b = schiesser_mq_bouton( $a['b1Texte'], $a['b1Lien'], 'btn-solid', true ) . schiesser_mq_bouton( $a['b2Texte'], $a['b2Lien'], 'btn-ghost' );
	if ( $b ) {
		$html .= '<div class="cta-actions">' . $b . '</div>';
	}
	return $html . '</div></section>';
}

/* ------------------------------------------------------------------ */
/* Rendu des sections : accueil                                        */
/* ------------------------------------------------------------------ */

/**
 * Produits du catalogue : la sélection faite dans le bloc (dans cet ordre),
 * sinon les premiers produits du menu Produits. Un produit transféré dans
 * WooCommerce est remplacé par sa fiche WooCommerce.
 */
function schiesser_mq_produits_catalogue( $a ) {
	$ids = array_filter( array_map( 'intval', explode( ',', (string) ( $a['produits'] ?? '' ) ) ) );
	if ( ! $ids ) {
		return function_exists( 'schiesser_liste_produits' ) ? schiesser_liste_produits( ( $a['source'] ?? '' ) ?: 'auto', max( 0, (int) ( $a['limite'] ?? 6 ) ) ) : array();
	}
	$woo   = function_exists( 'schiesser_woo_actif' ) && schiesser_woo_actif() && function_exists( 'wc_get_product' );
	$liste = array();
	foreach ( array_unique( $ids ) as $id ) {
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}
		if ( SCHIESSER_PRODUIT === $post->post_type ) {
			$wc = $woo ? wc_get_product( (int) get_post_meta( $id, '_schiesser_wc_id', true ) ) : null;
			if ( $wc && 'publish' === $wc->get_status() ) {
				$liste[] = schiesser_donnees_produit_wc( $wc );
			} elseif ( 'publish' === $post->post_status ) {
				$liste[] = schiesser_donnees_produit( $post );
			}
		} elseif ( 'product' === $post->post_type && $woo && 'publish' === $post->post_status ) {
			$wc = wc_get_product( $id );
			if ( $wc ) {
				$liste[] = schiesser_donnees_produit_wc( $wc );
			}
		}
	}
	return $liste;
}

function schiesser_mq_rendu_catalogue( $a ) {
	$produits = array_values( schiesser_mq_produits_catalogue( $a ) );
	if ( ! $produits ) {
		return '';
	}
	$lignes = '';
	$images = '';
	foreach ( $produits as $i => $p ) {
		$no       = schiesser_mq_num( $i + 1 );
		$accroche = trim( (string) ( $p['accroche'] ?? '' ) ) ?: (string) $p['categorie'];
		$vignette = $p['image_id'] ? wp_get_attachment_image( $p['image_id'], 'medium_large', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
		$lignes  .= '<a class="cat-row" data-img="' . $i . '" data-nom="' . esc_attr( $p['nom'] ) . '" data-no="Pl. ' . esc_attr( $no ) . '" href="' . esc_url( $p['url'] ) . '">'
			. '<span class="n">' . esc_html( $no ) . '</span>'
			. '<span class="nm">' . esc_html( $p['nom'] ) . ( $accroche ? '<small>' . esc_html( $accroche ) . '</small>' : '' ) . '</span>'
			. '<span class="go" aria-hidden="true">→</span>'
			. '<span class="cat-thumb ph" data-label="' . esc_attr( $p['nom'] ) . '">' . $vignette . '</span></a>';
		if ( $p['image_id'] ) {
			$images .= wp_get_attachment_image( $p['image_id'], 'large', false, array(
				'alt'      => '',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'data-i'   => (string) $i,
				'class'    => 0 === $i ? 'on' : '',
				'sizes'    => '(min-width: 900px) 440px, 100vw',
			) );
		}
	}
	$premier = $produits[0];
	$contenu = '<div class="cat"><div class="cat-list rv">' . $lignes . '</div>'
		. '<div class="cat-preview" aria-hidden="true"><div class="pv ph" data-label="Aperçu">' . $images . '</div>'
		. '<div class="cap"><span class="js-cap-nom">' . esc_html( $premier['nom'] ) . '</span><span class="js-cap-no">Pl. 01</span></div></div></div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_etages( $a, $content, $block ) {
	$cartes = '';
	foreach ( schiesser_mq_enfants( $block ) as $c ) {
		$e     = $c->attributes;
		$lien  = trim( (string) $e['lienUrl'] );
		$tag   = $lien ? 'a' : 'div';
		$cartes .= '<' . $tag . ' class="x-floor"' . ( $lien ? ' href="' . esc_url( $lien ) . '"' : '' ) . '>'
			. schiesser_mq_image( $e, 'image', 'large', array( 'sizes' => '(min-width: 820px) 50vw, 100vw' ) )
			. '<span class="x-num" aria-hidden="true">' . esc_html( schiesser_mq_brut( $e['numero'] ) ) . '</span>'
			. '<span class="x-fc">'
			. ( '' !== trim( $e['surtitre'] ) ? '<span class="x-lv">' . esc_html( schiesser_mq_brut( $e['surtitre'] ) ) . '</span>' : '' )
			. '<span class="x-ft">' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</span>'
			. ( '' !== trim( $e['texte'] ) ? '<span class="x-fd">' . esc_html( schiesser_mq_brut( $e['texte'] ) ) . '</span>' : '' )
			. ( '' !== trim( $e['lienTexte'] ) ? '<span class="x-fb"><span>' . esc_html( schiesser_mq_brut( $e['lienTexte'] ) ) . '</span> <span aria-hidden="true">→</span></span>' : '' )
			. '</span></' . $tag . '>';
	}
	return schiesser_mq_section( $a, '<div class="x-floors rv">' . $cartes . '</div>' );
}

function schiesser_mq_rendu_savoir_faire( $a, $content, $block ) {
	$etapes = schiesser_mq_enfants( $block );
	if ( ! $etapes ) {
		return '';
	}
	$pile  = '';
	$liste = '';
	foreach ( $etapes as $i => $c ) {
		$e      = $c->attributes;
		$pile  .= schiesser_mq_image( $e, 'image', 'large', array( 'alt' => '', 'data-s' => (string) $i, 'class' => 0 === $i ? 'on' : '', 'sizes' => '(min-width: 900px) 50vw, 100vw' ) );
		$liste .= '<div class="cstep' . ( 0 === $i ? ' active' : '' ) . '" data-s="' . $i . '"><div class="cn">' . schiesser_mq_num( $i + 1 ) . '</div>'
			. '<h3>' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</h3>'
			. ( '' !== trim( $e['texte'] ) ? '<p>' . schiesser_mq_texte( $e['texte'] ) . '</p>' : '' )
			. ( schiesser_mq_a_image( $e ) ? '<div class="cthumb ph" data-label="' . esc_attr( schiesser_mq_brut( $e['titre'] ) ) . '">' . schiesser_mq_image( $e, 'image', 'medium_large' ) . '</div>' : '' )
			. '</div>';
	}
	$contenu = '<div class="craft-grid"><div class="craft-media" aria-hidden="true"><div class="cm-stack ph" data-label="Atelier">' . $pile . '</div>'
		. '<div class="cm-num"><span class="js-cm-num">01</span> / ' . schiesser_mq_num( count( $etapes ) ) . '</div></div>'
		. '<div class="craft-steps">' . $liste . '</div></div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_frise( $a, $content, $block ) {
	$dates = schiesser_mq_enfants( $block );
	if ( ! $dates ) {
		return '';
	}
	$attrs = array( 'class' => 'tl' );
	$ancre = sanitize_title( $a['ancre'] ?? '' );
	if ( $ancre ) {
		$attrs['id'] = $ancre;
	}
	$tete = schiesser_mq_entete( $a );

	if ( 'onglets' !== $a['affichage'] ) {
		$cartes = '';
		foreach ( $dates as $c ) {
			$e       = $c->attributes;
			$cartes .= '<div class="tl-card ph" data-label="' . esc_attr( schiesser_mq_brut( $e['annee'] ) ) . '">'
				. schiesser_mq_image( $e, 'image', 'medium_large', array( 'alt' => '' ) )
				. '<div class="tc"><div class="ty">' . schiesser_mq_annee( $e['annee'] ) . '</div>'
				. '<div class="tt">' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</div>'
				. ( '' !== trim( $e['texte'] ) ? '<div class="tx">' . schiesser_mq_texte( $e['texte'] ) . '</div>' : '' )
				. '</div></div>';
		}
		return '<section ' . get_block_wrapper_attributes( $attrs ) . '><div class="sec wrap tl-sec">' . $tete
			. '<div class="tl-scroll rv js-tl-scroll" tabindex="0" role="region" aria-label="' . esc_attr( wp_strip_all_tags( schiesser_mq_brut( $a['titre'] ) ) ?: 'Ligne du temps' ) . '">' . $cartes . '</div>'
			. '<div class="tl-hint"><span>' . esc_html( schiesser_mq_brut( $a['indication'] ) ) . '</span><div class="tl-arrows"><button type="button" class="js-tl-prec" aria-label="Époque précédente">←</button><button type="button" class="js-tl-suiv" aria-label="Époque suivante">→</button></div></div>'
			. '</div></section>';
	}

	$images = '';
	$textes = '';
	$noeuds = '';
	foreach ( $dates as $i => $c ) {
		$e       = $c->attributes;
		$annee   = schiesser_mq_brut( $e['annee'] );
		$images .= schiesser_mq_image( $e, 'image', 'large', array( 'alt' => '', 'data-e' => (string) $i, 'class' => 0 === $i ? 'on' : '' ) );
		$textes .= '<div class="tl-txt rv' . ( $i ? ' is-off' : '' ) . '" data-e="' . $i . '"><div class="ty">' . schiesser_mq_annee( $annee ) . '</div>'
			. '<div class="tt">' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</div>'
			. ( '' !== trim( $e['texte'] ) ? '<div class="td">' . schiesser_mq_texte( $e['texte'] ) . '</div>' : '' )
			. ( '' !== trim( $e['retenir'] ) ? '<div class="tf"><b>' . esc_html( schiesser_mq_brut( $a['retenirLibelle'] ) ) . '</b> · <span>' . schiesser_mq_texte( $e['retenir'] ) . '</span></div>' : '' )
			. '</div>';
		$noeuds .= '<button type="button" class="tl-node' . ( 0 === $i ? ' on' : '' ) . '" data-e="' . $i . '" data-annee="' . esc_attr( $annee ) . '" aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '">'
			. '<span class="bar"></span><span class="ny">' . esc_html( $annee ) . '</span><span class="nl">' . esc_html( schiesser_mq_brut( $e['libelle'] ?: $e['titre'] ) ) . '</span></button>';
	}
	$premiere = schiesser_mq_brut( $dates[0]->attributes['annee'] );
	return '<section ' . get_block_wrapper_attributes( $attrs ) . '><div class="sec wrap">' . $tete
		. '<div class="tl-stage"><div class="tl-media ph rv" data-label="Archive">' . $images . '<span class="stamp js-tl-stamp">' . esc_html( $premiere ) . '</span></div>' . $textes . '</div>'
		. '<div class="tl-rail rv"><div class="tl-track">' . $noeuds . '</div>'
		. '<div class="tl-nav"><span class="hint">' . esc_html( schiesser_mq_brut( $a['indication'] ) ) . '</span><div class="arrows"><button type="button" class="ar js-tl-prec" aria-label="Date précédente">←</button><button type="button" class="ar js-tl-suiv" aria-label="Date suivante">→</button></div></div></div>'
		. '</div></section>';
}

/** Données de la carte interactive (coordonnées des Réglages maison). */
function schiesser_mq_carte_attrs( $zoom = 16, $style = 'osm' ) {
	$lat   = schiesser_reglage( 'latitude' );
	$lng   = schiesser_reglage( 'longitude' );
	$liens = schiesser_mq_liens_maison();
	if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
		return ' data-lien="' . esc_url( $liens['carte'] ) . '"';
	}
	return ' data-lat="' . esc_attr( $lat ) . '" data-lng="' . esc_attr( $lng ) . '" data-zoom="' . (int) $zoom . '" data-style="' . esc_attr( $style ) . '"'
		. ' data-nom="' . esc_attr( schiesser_reglage( 'nom_etablissement' ) ?: get_bloginfo( 'name' ) ) . '" data-lien="' . esc_url( $liens['carte'] ) . '"';
}

function schiesser_mq_rendu_adresse( $a, $content, $block ) {
	$liens  = schiesser_mq_liens_maison();
	$lignes = '';
	foreach ( schiesser_mq_enfants( $block ) as $c ) {
		list( $v, $d ) = schiesser_mq_ligne_valeurs( $c->attributes, 'vinfo' );
		$lignes       .= '<div class="vrow"><span class="k">' . esc_html( schiesser_mq_brut( $c->attributes['libelle'] ) ) . '</span><span class="v">' . $v . ( $d ? '<small>' . $d . '</small>' : '' ) . '</span></div>';
	}
	$lieu    = trim( (string) $a['lieu'] ) ?: trim( schiesser_reglage( 'rue' ) . ', ' . schiesser_reglage( 'ville' ), ', ' );
	$boutons = schiesser_mq_bouton( $a['b1Texte'], $a['b1Lien'] ?: $liens['email'], 'btn-kir', true ) . schiesser_mq_bouton( $a['b2Texte'], $a['b2Lien'] ?: $liens['itineraire'], 'btn-line' );
	$carte   = $a['carte']
		? '<div class="vmap js-carte"' . schiesser_mq_carte_attrs( 16, 'osm' ) . '><a class="mapfallback" href="' . esc_url( $liens['carte'] ) . '" target="_blank" rel="noopener">Ouvrir dans Google Maps →</a></div>'
		: '<div class="vmap"><a class="mapfallback" href="' . esc_url( $liens['carte'] ) . '" target="_blank" rel="noopener">Ouvrir dans Google Maps →</a></div>';
	$contenu = '<div class="visit rv">' . $carte . '<div class="vinfo"><h3>' . esc_html( $lieu ) . '</h3>' . $lignes
		. ( $boutons ? '<div class="vcta">' . $boutons . '</div>' : '' ) . '</div></div>';
	return schiesser_mq_section( $a, $contenu );
}

/* ------------------------------------------------------------------ */
/* Rendu des sections : boutique                                       */
/* ------------------------------------------------------------------ */

function schiesser_mq_rendu_galerie( $a, $content, $block ) {
	$vues = schiesser_mq_enfants( $block );
	if ( ! $vues ) {
		return '';
	}
	$n       = count( $vues );
	$images  = '';
	$legendes = '';
	$vignettes = '';
	foreach ( $vues as $i => $c ) {
		$e          = $c->attributes;
		$images    .= schiesser_mq_image( $e, 'image', 'full', array( 'data-g' => (string) $i, 'class' => 0 === $i ? 'on' : '', 'sizes' => '(min-width: 1200px) 1140px, 100vw' ) );
		$legendes  .= '<div class="gv-cap' . ( $i ? ' is-off' : '' ) . '" data-g="' . $i . '"><div class="gn">' . schiesser_mq_num( $i + 1 ) . ' / ' . schiesser_mq_num( $n ) . '</div>'
			. '<div class="gt">' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</div>'
			. ( '' !== trim( $e['texte'] ) ? '<div class="gd">' . schiesser_mq_texte( $e['texte'] ) . '</div>' : '' ) . '</div>';
		$vignettes .= '<button type="button" class="gv-th' . ( 0 === $i ? ' on' : '' ) . '" data-g="' . $i . '" aria-label="' . esc_attr( 'Photo ' . ( $i + 1 ) . ' : ' . schiesser_mq_brut( $e['titre'] ) ) . '">'
			. schiesser_mq_image( $e, 'image', 'medium', array( 'alt' => '' ) ) . '</button>';
	}
	$contenu = '<div class="gv rv js-gv"><div class="gv-main ph" data-label="Galerie">' . $images . '<div class="gv-veil"></div>' . $legendes
		. '<div class="gv-arrows"><button type="button" class="js-gv-prec" aria-label="Photo précédente">←</button><button type="button" class="js-gv-suiv" aria-label="Photo suivante">→</button></div></div>'
		. '<div class="gv-thumbs">' . $vignettes . '</div></div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_infos( $a, $content, $block ) {
	$cellules = '';
	foreach ( schiesser_mq_enfants( $block ) as $c ) {
		$e         = $c->attributes;
		$cellules .= '<div class="pcell"><span class="pi">' . schiesser_mq_picto( $e['icone'] ) . '</span><div>'
			. '<h3>' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</h3>'
			. ( '' !== trim( $e['texte'] ) ? '<p>' . schiesser_mq_texte( $e['texte'] ) . '</p>' : '' ) . '</div></div>';
	}
	return schiesser_mq_section( $a, '<div class="prac rv">' . $cellules . '</div>' );
}

function schiesser_mq_rendu_cartes( $a, $content, $block ) {
	$cartes = '';
	foreach ( schiesser_mq_enfants( $block ) as $i => $c ) {
		$e = $c->attributes;
		if ( 'principes' === $a['modele'] ) {
			$cartes .= '<div class="val"><div class="vn">' . schiesser_mq_num( $i + 1 ) . '</div><h3>' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</h3>'
				. ( '' !== trim( $e['texte'] ) ? '<p>' . schiesser_mq_texte( $e['texte'] ) . '</p>' : '' ) . '</div>';
			continue;
		}
		$lien    = trim( (string) $e['boutonLien'] );
		$bouton  = schiesser_mq_bouton( $e['boutonTexte'], $lien, $e['sombre'] ? 'btn-solid' : 'btn-line', ! empty( $e['sombre'] ) );
		$cartes .= '<div class="oc' . ( $e['sombre'] ? ' oc-dark' : '' ) . '"><span class="on">' . schiesser_mq_num( $i + 1 ) . '</span>'
			. '<h3>' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</h3>'
			. ( '' !== trim( $e['texte'] ) ? '<p>' . schiesser_mq_texte( $e['texte'] ) . '</p>' : '' )
			. ( $bouton ? '<div class="ob">' . $bouton . '</div>' : '' ) . '</div>';
	}
	return schiesser_mq_section( $a, '<div class="' . ( 'principes' === $a['modele'] ? 'vals' : 'ord' ) . ' rv">' . $cartes . '</div>' );
}

/* ------------------------------------------------------------------ */
/* Rendu des sections : salon de thé                                   */
/* ------------------------------------------------------------------ */

function schiesser_mq_rendu_intro( $a, $content ) {
	$contenu = '<div class="asc-intro rv">'
		. ( '' !== trim( wp_strip_all_tags( $a['lead'] ) ) ? '<p class="lead">' . schiesser_mq_texte( $a['lead'] ) . '</p>' : '' )
		. '<div class="body">' . $content . '</div></div>';
	return schiesser_mq_section( $a, $contenu, $a['suite'] ? 'sec--suivi' : '' );
}

function schiesser_mq_rendu_ascension( $a, $content, $block ) {
	$paliers = schiesser_mq_enfants( $block );
	if ( ! $paliers ) {
		return '';
	}
	$n       = count( $paliers );
	$images  = '';
	$noeuds  = '';
	$textes  = '';
	foreach ( $paliers as $i => $c ) {
		$e       = $c->attributes;
		$images .= schiesser_mq_image( $e, 'image', 'full', array( 'alt' => '', 'data-a' => (string) $i, 'class' => 0 === $i ? 'on' : '', 'sizes' => '100vw' ) );
		$haut    = $n > 1 ? round( $i / ( $n - 1 ) * 100, 2 ) : 0;
		$noeuds .= '<button type="button" class="asc-node' . ( 0 === $i ? ' on' : '' ) . '" data-a="' . $i . '" style="top:' . $haut . '%" aria-label="' . esc_attr( schiesser_mq_brut( $e['libelle'] ?: $e['titre'] ) ) . '">'
			. '<span class="dot">' . esc_html( schiesser_mq_brut( $e['niveau'] ) ) . '</span><span class="lb">' . esc_html( schiesser_mq_brut( $e['libelle'] ) ) . '</span></button>';
		$textes .= '<div class="asc-copy' . ( $i ? ' is-off' : '' ) . '" data-a="' . $i . '"><div class="asc-floor"><span class="fn">' . esc_html( schiesser_mq_brut( $e['niveau'] ) ) . '</span><span class="fk">' . esc_html( schiesser_mq_brut( $e['surtitre'] ) ) . '</span></div>'
			. '<h3>' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</h3>'
			. ( '' !== trim( $e['texte'] ) ? '<p>' . schiesser_mq_texte( $e['texte'] ) . '</p>' : '' ) . '</div>';
	}
	return '<section ' . get_block_wrapper_attributes( array( 'class' => 'sec sec--suite' ) ) . '><div class="wrap"><div class="ascend js-ascend">'
		. '<div class="asc-stage"><div class="asc-layers">' . $images . '</div><div class="asc-veil"></div><div class="asc-grain"></div>'
		. '<div class="asc-in"><div class="asc-rail"><span class="track"></span><span class="fill js-asc-fill"></span><span class="cab js-asc-cab"></span>' . $noeuds . '</div>'
		. '<div class="asc-copies">' . $textes . '</div></div>'
		. '<div class="asc-hint js-asc-hint"><span>' . esc_html( schiesser_mq_brut( $a['indication'] ) ) . '</span><span class="arw"></span></div>'
		. '<div class="asc-bar"><span class="js-asc-bar"></span></div></div>'
		. '</div></div></section>';
}

function schiesser_mq_rendu_encart( $a, $content, $block ) {
	$dates = '';
	foreach ( schiesser_mq_enfants( $block ) as $c ) {
		$dates .= '<div class="dy-d"><div class="y">' . esc_html( schiesser_mq_brut( $c->attributes['valeur'] ) ) . '</div><div class="t">' . esc_html( schiesser_mq_brut( $c->attributes['libelle'] ) ) . '</div></div>';
	}
	$contenu = '<div class="dy rv"><div class="dy-top"><div class="dy-im">' . schiesser_mq_image( $a, 'image', 'large' ) . '</div><div class="dy-tx">'
		. ( '' !== trim( $a['surtitre'] ) ? '<span class="dy-k">' . esc_html( schiesser_mq_brut( $a['surtitre'] ) ) . '</span>' : '' )
		. '<h3>' . esc_html( schiesser_mq_brut( $a['encartTitre'] ) ) . '</h3>'
		. ( '' !== trim( $a['sousTitre'] ) ? '<div class="dy-sub">' . esc_html( schiesser_mq_brut( $a['sousTitre'] ) ) . '</div>' : '' )
		. ( '' !== trim( $a['texte'] ) ? '<p>' . schiesser_mq_texte( $a['texte'] ) . '</p>' : '' )
		. '</div></div>'
		. ( $dates ? '<div class="dy-dates">' . $dates . '</div>' : '' )
		. ( '' !== trim( $a['mention'] ) ? '<div class="dy-note">' . esc_html( schiesser_mq_brut( $a['mention'] ) ) . '</div>' : '' )
		. '</div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_carte_salon( $a, $content, $block ) {
	$rubriques = schiesser_mq_enfants( $block );
	if ( ! $rubriques ) {
		return '';
	}
	$photos = '';
	$onglets = '';
	$listes = '';
	foreach ( $rubriques as $i => $r ) {
		$e        = $r->attributes;
		$nom      = schiesser_mq_brut( $e['nom'] );
		$photos  .= schiesser_mq_image( $e, 'image', 'full', array( 'data-c' => (string) $i, 'class' => 0 === $i ? 'on' : '', 'sizes' => '(min-width: 1200px) 1140px, 100vw' ) );
		$onglets .= '<button type="button" class="mtab' . ( 0 === $i ? ' on' : '' ) . '" data-c="' . $i . '" aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '">' . esc_html( $nom ) . '</button>';
		$plats    = '';
		foreach ( schiesser_mq_enfants( $r ) as $k => $p ) {
			$q      = $p->attributes;
			$plats .= '<div class="mi" style="animation-delay:' . esc_attr( round( $k * 0.05, 2 ) ) . 's"><div class="mi-top"><span class="mi-nm">' . esc_html( schiesser_mq_brut( $q['nom'] ) ) . '</span><span class="mi-dots"></span><span class="mi-pr">' . esc_html( schiesser_mq_brut( $q['prix'] ) ) . '</span></div>'
				. ( '' !== trim( $q['description'] ) ? '<div class="mi-d">' . schiesser_mq_texte( $q['description'] ) . '</div>' : '' )
				. ( '' !== trim( $q['mention'] ) ? '<span class="mi-tag">' . esc_html( schiesser_mq_brut( $q['mention'] ) ) . '</span>' : '' ) . '</div>';
		}
		$listes .= '<div class="mn-list' . ( $i ? ' is-off' : '' ) . '" data-c="' . $i . '"><h3 class="screen-reader-text">' . esc_html( $nom ) . '</h3>' . $plats . '</div>';
	}
	$suggestion = '';
	if ( '' !== trim( $a['sTitre'] ) ) {
		$suggestion = '<div class="sugg"><div class="si ph" data-label="' . esc_attr( schiesser_mq_brut( $a['sSurtitre'] ) ) . '">' . schiesser_mq_image( $a, 's', 'medium_large' ) . '</div><div class="sb">'
			. '<div class="sk">' . esc_html( schiesser_mq_brut( $a['sSurtitre'] ) ) . '</div><div class="sh">' . esc_html( schiesser_mq_brut( $a['sTitre'] ) ) . '</div>'
			. ( '' !== trim( $a['sTexte'] ) ? '<p class="sp">' . schiesser_mq_texte( $a['sTexte'] ) . '</p>' : '' )
			. '<div class="sf"><span>' . esc_html( schiesser_mq_brut( $a['sPied'] ) ) . '</span><b>' . esc_html( schiesser_mq_brut( $a['sPrix'] ) ) . '</b></div></div></div>';
	}
	$contenu = '<div class="rv js-carte-salon">'
		. '<div class="x-menupic">' . $photos . '<span class="x-mc js-mn-legende">' . esc_html( schiesser_mq_brut( $rubriques[0]->attributes['nom'] ) ) . '</span></div>'
		. '<div class="mn-tabs">' . $onglets . '</div>'
		. '<div class="mn"><div class="mn-listes">' . $listes . '</div><aside class="mn-side">' . $suggestion
		. ( '' !== trim( $a['mention'] ) ? '<p class="mn-note">' . esc_html( schiesser_mq_brut( $a['mention'] ) ) . '</p>' : '' ) . '</aside></div></div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_panneaux( $a, $content, $block ) {
	$panneaux = '';
	foreach ( schiesser_mq_enfants( $block ) as $i => $c ) {
		$e         = $c->attributes;
		$panneaux .= '<div class="gp ph' . ( 0 === $i ? ' on' : '' ) . '" data-g="' . $i . '" data-label="' . esc_attr( schiesser_mq_brut( $e['titre'] ) ) . '" tabindex="0">'
			. schiesser_mq_image( $e, 'image', 'large' )
			. '<div class="gp-c"><div class="gp-n">' . schiesser_mq_num( $i + 1 ) . '</div><div class="gp-t">' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</div>'
			. ( '' !== trim( $e['texte'] ) ? '<div class="gp-d">' . schiesser_mq_texte( $e['texte'] ) . '</div>' : '' ) . '</div></div>';
	}
	$contenu = '<div class="gal rv js-gal">' . $panneaux . '</div>'
		. ( '' !== trim( $a['indication'] ) ? '<p class="gal-hint">' . esc_html( schiesser_mq_brut( $a['indication'] ) ) . '</p>' : '' );
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_moments( $a, $content, $block ) {
	$moments = schiesser_mq_enfants( $block );
	if ( ! $moments ) {
		return '';
	}
	$images = '';
	$textes = '';
	$noeuds = '';
	foreach ( $moments as $i => $c ) {
		$e       = $c->attributes;
		$images .= schiesser_mq_image( $e, 'image', 'large', array( 'alt' => '', 'data-m' => (string) $i, 'class' => 0 === $i ? 'on' : '' ) );
		$textes .= '<div class="mo-txt' . ( $i ? ' is-off' : '' ) . '" data-m="' . $i . '"><div class="mh">' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</div>'
			. ( '' !== trim( $e['sousTitre'] ) ? '<div class="ms">' . esc_html( schiesser_mq_brut( $e['sousTitre'] ) ) . '</div>' : '' )
			. ( '' !== trim( $e['texte'] ) ? '<div class="md">' . schiesser_mq_texte( $e['texte'] ) . '</div>' : '' )
			. ( '' !== trim( $e['conseil'] ) ? '<div class="mp"><div class="k">' . esc_html( schiesser_mq_brut( $a['conseilLibelle'] ) ) . '</div><div class="v">' . esc_html( schiesser_mq_brut( $e['conseil'] ) ) . '</div></div>' : '' )
			. '</div>';
		$noeuds .= '<button type="button" class="mo-node' . ( 0 === $i ? ' on' : '' ) . '" data-m="' . $i . '" data-heure="' . esc_attr( schiesser_mq_brut( $e['heure'] ) ) . '" aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '">'
			. '<span class="bar"></span><span class="nh">' . esc_html( schiesser_mq_brut( $e['heure'] ) ) . '</span><span class="nl">' . esc_html( schiesser_mq_brut( $e['libelle'] ) ) . '</span></button>';
	}
	$contenu = '<div class="js-moments"><div class="mo rv"><div class="mo-media ph" data-label="Le salon">' . $images . '<span class="mo-clock js-mo-heure">' . esc_html( schiesser_mq_brut( $moments[0]->attributes['heure'] ) ) . '</span></div>'
		. $textes . '</div><div class="mo-rail rv">' . $noeuds . '</div></div>';
	return schiesser_mq_section( $a, $contenu );
}

/** Lignes d'informations d'une section (« .vn-row », « .dl », « .xs »…). */
function schiesser_mq_lignes( $block, $modele ) {
	$html = '';
	foreach ( schiesser_mq_enfants( $block ) as $c ) {
		$l = $c->attributes;
		list( $v, $d ) = schiesser_mq_ligne_valeurs( $l, $modele );
		$k = esc_html( schiesser_mq_brut( $l['libelle'] ) );
		if ( 'dl' === $modele ) {
			$html .= '<div class="dl"><span class="k">' . $k . '</span><span>' . $v . '</span></div>';
		} elseif ( 'xs' === $modele ) {
			$html .= '<div class="xs"><div class="k">' . $k . '</div><div class="v">' . $v . '</div>' . ( $d ? '<div class="s">' . $d . '</div>' : '' ) . '</div>';
		} else {
			$html .= '<div class="vn-row"><span class="k">' . $k . '</span><span class="v">' . $v . ( $d ? '<small>' . $d . '</small>' : '' ) . '</span></div>';
		}
	}
	return $html;
}

function schiesser_mq_rendu_venir( $a, $content, $block ) {
	$boutons = schiesser_mq_bouton( $a['b1Texte'], $a['b1Lien'], 'btn-kir', true ) . schiesser_mq_bouton( $a['b2Texte'], $a['b2Lien'], 'btn-line' );
	$contenu = '<div class="vn rv"><div class="vn-im ph" data-label="Photo">' . schiesser_mq_image( $a, 'image', 'large' ) . '</div><div class="vn-tx">'
		. '<h3>' . esc_html( schiesser_mq_brut( $a['encartTitre'] ) ) . '</h3>'
		. ( '' !== trim( $a['texte'] ) ? '<p>' . schiesser_mq_texte( $a['texte'] ) . '</p>' : '' )
		. '<div class="vn-rows">' . schiesser_mq_lignes( $block, 'vn' ) . '</div>'
		. ( $boutons ? '<div class="vn-cta">' . $boutons . '</div>' : '' ) . '</div></div>';
	return schiesser_mq_section( $a, $contenu );
}

/* ------------------------------------------------------------------ */
/* Rendu des sections : histoire                                       */
/* ------------------------------------------------------------------ */

function schiesser_mq_rendu_origine( $a, $content ) {
	// Lettrine : le premier paragraphe reçoit la classe « first » de la maquette.
	$content = preg_replace_callback( '/<p(\s[^>]*)?>/', function ( $m ) {
		$attrs = $m[1] ?? '';
		if ( preg_match( '/class="([^"]*)"/', $attrs ) ) {
			return '<p' . preg_replace( '/class="([^"]*)"/', 'class="first $1"', $attrs, 1 ) . '>';
		}
		return '<p class="first"' . $attrs . '>';
	}, $content, 1 );
	$legende = trim( wp_strip_all_tags( $a['legende'] ) );
	$contenu = '<div class="origin"><div class="origin-txt rv">' . $content . '</div>'
		. '<figure class="origin-fig rv"><div class="im ph" data-label="Archive">' . schiesser_mq_image( $a, 'image', 'large' ) . '</div>'
		. ( $legende || $a['figure'] ? '<figcaption><span>' . esc_html( $legende ) . '</span><span>' . esc_html( schiesser_mq_brut( $a['figure'] ) ) . '</span></figcaption>' : '' )
		. '</figure></div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_exergue( $a ) {
	return '<div class="pull"><q class="serif">' . schiesser_mq_texte( $a['texte'] ) . '</q>'
		. ( '' !== trim( $a['auteur'] ) ? '<div class="who">' . esc_html( schiesser_mq_brut( $a['auteur'] ) ) . '</div>' : '' ) . '</div>';
}

function schiesser_mq_rendu_archives( $a, $content, $block ) {
	$archives = schiesser_mq_enfants( $block );
	if ( ! $archives ) {
		return '';
	}
	$n    = count( $archives );
	$grille = '';
	foreach ( $archives as $i => $c ) {
		$e       = $c->attributes;
		$grille .= '<button type="button" class="arch rv" data-a="' . $i . '" data-image="' . esc_url( schiesser_mq_image_url( $e ) ) . '" data-titre="' . esc_attr( schiesser_mq_brut( $e['titre'] ) ) . '" data-texte="' . esc_attr( schiesser_mq_brut( $e['description'] ?: $e['imageAlt'] ) ) . '" aria-label="' . esc_attr( 'Agrandir : ' . schiesser_mq_brut( $e['titre'] ) ) . '">'
			. '<span class="aim ph" data-label="Archive ' . schiesser_mq_num( $i + 1 ) . '">' . schiesser_mq_image( $e, 'image', 'medium_large' ) . '</span>'
			. '<span class="acap"><b>' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</b><span>' . esc_html( schiesser_mq_brut( $e['meta'] ) ) . '</span></span></button>';
	}
	$boite = '<div class="lightbox js-lb" role="dialog" aria-modal="true" aria-label="Archive agrandie" data-total="' . $n . '">'
		. '<button type="button" class="lb-close js-lb-fermer" aria-label="Fermer">✕</button>'
		. '<div class="lb-inner"><div class="lb-im ph" data-label="Archive"><img class="js-lb-img" alt="" decoding="async"></div>'
		. '<div class="lb-tx"><span class="lbi js-lb-num"></span><h3 class="js-lb-titre"></h3><p class="js-lb-texte"></p>'
		. '<div class="lb-nav"><button type="button" class="js-lb-prec" aria-label="Archive précédente">←</button><button type="button" class="js-lb-suiv" aria-label="Archive suivante">→</button></div></div></div></div>';
	$contenu = '<div class="arch-grid js-archives">' . $grille . '</div>'
		. ( '' !== trim( $a['credit'] ) ? '<p class="x-credit">' . esc_html( schiesser_mq_brut( $a['credit'] ) ) . '</p>' : '' );
	return schiesser_mq_section( $a, $contenu . $boite );
}

function schiesser_mq_rendu_avant_apres( $a, $content, $block ) {
	$comparaisons = schiesser_mq_enfants( $block );
	if ( ! $comparaisons ) {
		return '';
	}
	$attrs = array( 'class' => 'tn' );
	$ancre = sanitize_title( $a['ancre'] ?? '' );
	if ( $ancre ) {
		$attrs['id'] = $ancre;
	}
	$onglets = '';
	foreach ( $comparaisons as $i => $c ) {
		$e = $c->attributes;
		if ( count( $comparaisons ) < 2 ) {
			break;
		}
		$onglets .= '<button type="button" class="x-cmptab' . ( 0 === $i ? ' on' : '' ) . '" data-k="' . $i . '" aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '"'
			. ' data-avant="' . esc_url( schiesser_mq_image_url( $e, 'avant', 'large' ) ) . '" data-avant-alt="' . esc_attr( schiesser_mq_brut( $e['avantAlt'] ) ) . '"'
			. ' data-apres="' . esc_url( schiesser_mq_image_url( $e, 'apres', 'large' ) ) . '" data-apres-alt="' . esc_attr( schiesser_mq_brut( $e['apresAlt'] ) ) . '"'
			. ' data-l="' . esc_attr( schiesser_mq_brut( $e['avantLibelle'] ) ) . '" data-r="' . esc_attr( schiesser_mq_brut( $e['apresLibelle'] ) ) . '" data-vieillir="' . ( $e['vieillir'] ? '1' : '0' ) . '">'
			. esc_html( schiesser_mq_brut( $e['onglet'] ) ) . '</button>';
	}
	$p = $comparaisons[0]->attributes;
	$contenu = ( $onglets ? '<div class="x-cmptabs rv">' . $onglets . '</div>' : '' )
		. '<div class="compare rv js-compare">'
		. schiesser_mq_image( $p, 'apres', 'large', array( 'class' => 'after' ) )
		. '<span class="cmp-tag r">' . esc_html( schiesser_mq_brut( $p['apresLibelle'] ) ) . '</span>'
		. '<div class="clip">' . schiesser_mq_image( $p, 'avant', 'large', array( 'class' => $p['vieillir'] ? 'is-vieilli' : '' ) ) . '<span class="cmp-tag l">' . esc_html( schiesser_mq_brut( $p['avantLibelle'] ) ) . '</span></div>'
		. '<div class="cmp-handle" role="slider" tabindex="0" aria-label="Comparer hier et aujourd’hui" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">⇄</div></div>'
		. ( '' !== trim( $a['indication'] ) ? '<p class="cmp-hint">' . esc_html( schiesser_mq_brut( $a['indication'] ) ) . '</p>' : '' );
	return '<section ' . get_block_wrapper_attributes( $attrs ) . '><div class="sec wrap">' . schiesser_mq_entete( $a ) . $contenu . '</div></section>';
}

/* ------------------------------------------------------------------ */
/* Rendu des sections : visite                                         */
/* ------------------------------------------------------------------ */

function schiesser_mq_rendu_plan_horaires( $a ) {
	$liens = schiesser_mq_liens_maison();
	$adr   = schiesser_adresse_lignes();
	$etat  = schiesser_mq_etat();
	$carte = '<div class="mapbox">'
		. '<div class="mapel' . ( $a['carte'] ? ' js-carte' : '' ) . '"' . ( $a['carte'] ? schiesser_mq_carte_attrs( 17, 'carto' ) . ' data-zoom-perso="1"' : '' ) . '><a class="mapfallback" href="' . esc_url( $liens['carte'] ) . '" target="_blank" rel="noopener">Ouvrir dans Google Maps →</a></div>'
		. '<div class="mapcard"><div class="mt">' . esc_html( schiesser_reglage( 'nom_etablissement' ) ?: get_bloginfo( 'name' ) ) . '</div>'
		. '<div class="ma">' . esc_html( $adr[0] ) . '<br>' . esc_html( $adr[1] ) . '</div>'
		. '<div class="ms js-etat-plage' . ( $etat['ouvert'] ? '' : ' shut' ) . '" data-nosnippet><i></i><span>' . esc_html( schiesser_mq_etat_plage() ) . '</span></div>'
		. '<div class="mb">' . schiesser_mq_bouton( $a['b1Texte'], $liens['itineraire'], 'btn-kir' ) . schiesser_mq_bouton( $a['b2Texte'], $liens['carte'], 'btn-line' ) . '</div></div>'
		. ( $a['carte'] ? '<div class="mapzoom"><button type="button" class="js-zoom-moins" aria-label="Zoom arrière">−</button><button type="button" class="js-zoom-plus" aria-label="Zoom avant">+</button><button type="button" class="js-zoom-centre" aria-label="Recentrer la carte">⌖</button></div>' : '' )
		. '</div>';

	$tous   = schiesser_reglage( 'horaires' );
	$auj    = (int) current_datetime()->format( 'w' );
	$lignes = '';
	foreach ( schiesser_jours() as $n => $jour ) {
		$h       = $tous[ $n ] ?? array();
		$lignes .= '<div class="hrow' . ( $n === $auj ? ' today' : '' ) . '" data-j="' . $n . '"><span class="d" data-today="Aujourd’hui">' . esc_html( $jour ) . '</span><span class="h">'
			. ( ! empty( $h['ferme'] ) ? 'Fermé' : esc_html( ( $h['ouverture'] ?? '' ) . ' – ' . ( $h['fermeture'] ?? '' ) ) ) . '</span></div>';
	}
	$note     = schiesser_reglage( 'horaires_note' );
	$horaires = '<div class="hourspanel"><div class="hp-head"><span class="hp-k">' . esc_html( schiesser_mq_brut( $a['titreHoraires'] ) ) . '</span><span class="hp-live js-etat-plage' . ( $etat['ouvert'] ? '' : ' shut' ) . '" data-nosnippet><span>' . esc_html( schiesser_mq_etat_plage() ) . '</span></span></div>'
		. '<div class="htable js-htable">' . $lignes . '</div>'
		. ( $note ? '<p class="hnote">' . esc_html( $note ) . '</p>' : '' ) . '</div>';
	return schiesser_mq_section( $a, '<div class="findus rv">' . $carte . $horaires . '</div>' );
}

function schiesser_mq_rendu_affluence( $a ) {
	$donnees = is_array( $a['donnees'] ) ? $a['donnees'] : schiesser_mq_affluence_defaut();
	$debut   = max( 0, min( 23, (int) $a['debut'] ) );
	$courts  = array( 1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 0 => 'Dim' );
	$horaires = (array) schiesser_reglage( 'horaires' );
	// Jours fermés dans les Réglages maison : aucune affluence ce jour-là.
	$propre = array();
	foreach ( $courts as $j => $c ) {
		$valeurs = array_map( 'intval', (array) ( $donnees[ (string) $j ] ?? array() ) );
		if ( ! empty( $horaires[ $j ]['ferme'] ) ) {
			$valeurs = array_fill( 0, max( 1, count( $valeurs ) ), 0 );
		}
		$propre[ (string) $j ] = $valeurs;
	}
	$auj   = (int) current_datetime()->format( 'w' );
	$jours = '';
	foreach ( $courts as $j => $c ) {
		$jours .= '<button type="button" class="dchip' . ( $j === $auj ? ' on' : '' ) . '" data-j="' . $j . '" aria-pressed="' . ( $j === $auj ? 'true' : 'false' ) . '">' . esc_html( $c ) . '</button>';
	}
	// Graphique du jour (sans JavaScript, il reste lisible).
	$barres = '';
	$ouvert = array();
	foreach ( $propre[ (string) $auj ] as $k => $v ) {
		$h       = $debut + $k;
		$niveau  = 0 === $v ? 'Fermé' : ( $v > 75 ? 'Très animé' : ( $v > 50 ? 'Animé' : 'Calme' ) );
		$barres .= '<div class="bar" style="height:' . max( $v, 4 ) . '%"><span class="bv">' . esc_html( $niveau ) . '</span><span class="bl">' . $h . 'h</span></div>';
		if ( $v > 0 ) {
			$ouvert[ $h ] = $v;
		}
	}
	$calme = $ouvert ? array_search( min( $ouvert ), $ouvert, true ) : null;
	$anime = $ouvert ? array_search( max( $ouvert ), $ouvert, true ) : null;
	$contenu = '<div class="rv js-affluence" data-debut="' . $debut . '" data-donnees="' . esc_attr( wp_json_encode( $propre ) ) . '">'
		. '<div class="aff-days">' . $jours . '</div>'
		. '<div class="aff"><div class="chart-wrap"><div class="chart js-aff-chart" role="img" aria-label="Affluence habituelle heure par heure">' . $barres . '</div></div>'
		. '<div class="aff-side">'
		. '<div class="ai"><div class="k">Le plus calme</div><div class="v js-aff-calme">' . ( null !== $calme ? $calme . 'h – ' . ( $calme + 1 ) . 'h' : 'Fermé' ) . '</div></div>'
		. '<div class="ai"><div class="k">Le plus animé</div><div class="v js-aff-anime">' . ( null !== $anime ? $anime . 'h – ' . ( $anime + 1 ) . 'h' : 'Fermé' ) . '</div></div>'
		. ( '' !== trim( $a['conseil'] ) ? '<div class="ai"><div class="k">Notre conseil</div><div class="v">' . esc_html( schiesser_mq_brut( $a['conseil'] ) ) . '</div></div>' : '' )
		. '</div></div></div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_trajets( $a, $content, $block ) {
	$trajets = schiesser_mq_enfants( $block );
	if ( ! $trajets ) {
		return '';
	}
	$liens   = schiesser_mq_liens_maison();
	$departs = '';
	$details = '';
	foreach ( $trajets as $i => $t ) {
		$e        = $t->attributes;
		$departs .= '<button type="button" class="ochip' . ( 0 === $i ? ' on' : '' ) . '" data-t="' . $i . '" aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '"><span class="oi">' . schiesser_mq_picto( $e['icone'] ) . '</span>'
			. '<span><span class="ot">' . esc_html( schiesser_mq_brut( $e['depart'] ) ) . '</span><span class="od">' . esc_html( schiesser_mq_brut( $e['detail'] ) ) . '</span></span></button>';
		$etapes   = schiesser_mq_enfants( $t );
		$route    = '';
		foreach ( $etapes as $k => $s ) {
			$q      = $s->attributes;
			$classe = 0 === $k ? ' start' : ( count( $etapes ) - 1 === $k ? ' end' : '' );
			$route .= '<div class="leg' . $classe . '"><div class="lmark"><div class="lnode">' . schiesser_mq_picto( $q['icone'] ) . '</div><div class="lline' . ( 'marche' === $q['icone'] ? ' dashed' : '' ) . '"></div></div>'
				. '<div class="lbody"><div class="lt">' . esc_html( schiesser_mq_brut( $q['titre'] ) ) . '</div>'
				. ( '' !== trim( $q['texte'] ) ? '<div class="ld">' . schiesser_mq_texte( $q['texte'] ) . '</div>' : '' )
				. ( '' !== trim( $q['duree'] ) ? '<div class="lmeta">' . esc_html( schiesser_mq_brut( $q['duree'] ) ) . '</div>' : '' ) . '</div></div>';
		}
		$kpi      = function ( $libelle, $valeur, $unite ) {
			return '' === trim( (string) $valeur ) ? '' : '<div class="kpi"><div class="kk">' . esc_html( $libelle ) . '</div><div class="kv">' . esc_html( $valeur ) . ( $unite ? '<em>' . esc_html( $unite ) . '</em>' : '' ) . '</div></div>';
		};
		$details .= '<div class="jp-main' . ( $i ? ' is-off' : '' ) . '" data-t="' . $i . '"><div class="jp-top"><div><div class="jt">' . esc_html( schiesser_mq_brut( $e['titre'] ) ) . '</div>'
			. ( '' !== trim( $e['sousTitre'] ) ? '<div class="js">' . esc_html( schiesser_mq_brut( $e['sousTitre'] ) ) . '</div>' : '' ) . '</div>'
			. '<div class="jp-kpis">' . $kpi( 'Durée', $e['duree'], 'min' ) . $kpi( 'Changements', $e['changements'], '' ) . $kpi( 'Marche', $e['marche'], 'min' ) . '</div></div>'
			. '<div class="route route-anim">' . $route . '</div>'
			. '<div class="jp-foot">' . ( '' !== trim( $e['astuce'] ) ? '<div class="jp-tip"><span class="bulb" aria-hidden="true">i</span><span>' . schiesser_mq_texte( $e['astuce'] ) . '</span></div>' : '<div></div>' )
			. schiesser_mq_bouton( $a['boutonTexte'], $liens['itineraire'], 'btn-solid', true ) . '</div></div>';
	}
	$contenu = '<div class="jp rv js-trajets"><div class="jp-side"><div class="jl">' . esc_html( schiesser_mq_brut( $a['departLibelle'] ) ) . '</div><div class="orig">' . $departs . '</div></div>' . $details . '</div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_devanture( $a ) {
	$contenu = '<div class="x-front rv">' . schiesser_mq_image( $a, 'image', 'full', array( 'sizes' => '(min-width: 1200px) 1140px, 100vw' ) ) . '<div class="x-frc">'
		. ( '' !== trim( $a['surtitre'] ) ? '<div class="x-frk">' . esc_html( schiesser_mq_brut( $a['surtitre'] ) ) . '</div>' : '' )
		. '<div class="x-frt">' . esc_html( schiesser_mq_brut( $a['encartTitre'] ) ) . '</div>'
		. ( '' !== trim( $a['texte'] ) ? '<div class="x-frd">' . schiesser_mq_texte( $a['texte'] ) . '</div>' : '' ) . '</div></div>';
	return schiesser_mq_section( $a, $contenu );
}

function schiesser_mq_rendu_faq( $a, $content, $block ) {
	$questions = '';
	foreach ( schiesser_mq_enfants( $block ) as $c ) {
		$q = $c->attributes;
		if ( '' === trim( wp_strip_all_tags( $q['question'] ) ) ) {
			continue;
		}
		$questions .= '<details class="fq"><summary><span class="q">' . esc_html( schiesser_mq_brut( $q['question'] ) ) . '</span><span class="pm" aria-hidden="true">+</span></summary>'
			. '<div class="ans"><p>' . schiesser_mq_texte( $q['reponse'] ) . '</p></div></details>';
	}
	return $questions ? schiesser_mq_section( $a, '<div class="faq rv js-faq">' . $questions . '</div>' ) : '';
}

/* ------------------------------------------------------------------ */
/* Rendu des sections : contact                                        */
/* ------------------------------------------------------------------ */

function schiesser_mq_rendu_formulaire( $a, $content, $block ) {
	static $n = 0;
	$n++;
	$id     = 'xf' . $n;
	$etat   = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$maintenant = time();
	$jeton  = $maintenant . '.' . wp_hash( 'schiesser_contact' . $maintenant );
	$champ  = function ( $nom, $type, $libelle, $requis, $auto ) use ( $id ) {
		$balise = 'textarea' === $type
			? '<textarea id="' . $id . '-' . $nom . '" name="' . $nom . '"' . ( $requis ? ' required' : '' ) . ' rows="4"></textarea>'
			: '<input type="' . $type . '" id="' . $id . '-' . $nom . '" name="' . $nom . '"' . ( $requis ? ' required' : '' ) . ' autocomplete="' . $auto . '">';
		return '<div class="xg">' . $balise . '<label for="' . $id . '-' . $nom . '">' . esc_html( schiesser_mq_brut( $libelle ) ) . ( $requis ? ' <em>*</em>' : '' ) . '</label><span class="bar"></span></div>';
	};
	$form = '<form class="xf-form js-contact" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
		. '<input type="hidden" name="action" value="schiesser_contact"><input type="hidden" name="jeton" value="' . esc_attr( $jeton ) . '">'
		. '<input type="hidden" name="retour" value="' . esc_url( get_permalink() ) . '">'
		. '<div class="xf-hp" aria-hidden="true"><label>Laissez ce champ vide <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></div>'
		. '<div class="xrow two">' . $champ( 'nom', 'text', $a['lNom'], true, 'name' ) . $champ( 'email', 'email', $a['lEmail'], true, 'email' ) . '</div>'
		. $champ( 'telephone', 'tel', $a['lTel'], false, 'tel' )
		. $champ( 'message', 'textarea', $a['lMessage'], true, 'off' )
		. '<div class="xf-send"><button class="btn btn-kir" type="submit"><span>' . esc_html( schiesser_mq_brut( $a['boutonTexte'] ) ) . '</span> <span class="a" aria-hidden="true">→</span></button>'
		. '<p class="xf-note">' . esc_html( schiesser_mq_brut( $a['mentionLegale'] ) ) . '</p></div>'
		. '<div class="xf-ok' . ( 'ok' === $etat ? ' on' : '' ) . '" role="status"><span class="ck" aria-hidden="true">✓</span><div><b>' . esc_html( schiesser_mq_brut( $a['okTitre'] ) ) . '</b><p>' . esc_html( schiesser_mq_brut( $a['okTexte'] ) ) . '</p></div></div>'
		. ( 'erreur' === $etat ? '<div class="xf-err" role="alert">Le message n’a pas pu être envoyé. Écrivez-nous directement à ' . esc_html( schiesser_reglage( 'email' ) ) . ' ou appelez-nous.</div>' : '' )
		. ( 'incomplet' === $etat ? '<div class="xf-err" role="alert">Merci de renseigner votre nom, une adresse e-mail valide et votre message.</div>' : '' )
		. '</form>';
	$cote = schiesser_mq_lignes( $block, 'xs' );
	return schiesser_mq_section( $a, '<div class="xf rv">' . $form . ( $cote ? '<aside class="xf-side">' . $cote . '</aside>' : '' ) . '</div>' );
}

function schiesser_mq_rendu_fiche_contact( $a, $content, $block ) {
	$contenu = '<div class="deps rv"><div class="dep">'
		. ( '' !== trim( $a['surtitre'] ) ? '<span class="dfloor">' . esc_html( schiesser_mq_brut( $a['surtitre'] ) ) . '</span>' : '' )
		. '<h3>' . esc_html( schiesser_mq_brut( $a['encartTitre'] ) ) . '</h3>'
		. ( '' !== trim( $a['texte'] ) ? '<p>' . schiesser_mq_texte( $a['texte'] ) . '</p>' : '' )
		. '<div class="dlines">' . schiesser_mq_lignes( $block, 'dl' ) . '</div></div></div>';
	return schiesser_mq_section( $a, $contenu );
}

/**
 * Envoi du formulaire de contact : à l'adresse des Réglages maison, par wp_mail().
 * Protection sans cookie ni captcha : champ piège, délai minimal, limite par adresse IP.
 */
function schiesser_mq_envoi_contact() {
	$retour = isset( $_POST['retour'] ) ? esc_url_raw( wp_unslash( $_POST['retour'] ) ) : home_url( '/' ); // phpcs:ignore WordPress.Security.NonceVerification
	$retour = wp_validate_redirect( $retour, home_url( '/' ) );
	$aller  = function ( $etat ) use ( $retour ) {
		wp_safe_redirect( add_query_arg( 'contact', $etat, $retour ) . '#ecrire' );
		exit;
	};
	// phpcs:disable WordPress.Security.NonceVerification
	$piege = isset( $_POST['site_web'] ) ? trim( (string) wp_unslash( $_POST['site_web'] ) ) : '';
	$jeton = isset( $_POST['jeton'] ) ? (string) wp_unslash( $_POST['jeton'] ) : '';
	$nom   = isset( $_POST['nom'] ) ? sanitize_text_field( wp_unslash( $_POST['nom'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$tel   = isset( $_POST['telephone'] ) ? sanitize_text_field( wp_unslash( $_POST['telephone'] ) ) : '';
	$msg   = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	// phpcs:enable

	// Robot : champ piège rempli, ou formulaire envoyé en moins de 3 secondes. On fait comme si tout allait bien.
	list( $moment, $signature ) = array_pad( explode( '.', $jeton, 2 ), 2, '' );
	if ( '' !== $piege || ! hash_equals( wp_hash( 'schiesser_contact' . $moment ), $signature ) || time() - (int) $moment < 3 ) {
		$aller( 'ok' );
	}
	if ( '' === $nom || ! is_email( $email ) || '' === $msg ) {
		$aller( 'incomplet' );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$cle = 'schiesser_contact_' . md5( $ip );
	if ( get_transient( $cle ) ) {
		$aller( 'ok' );
	}
	set_transient( $cle, 1, MINUTE_IN_SECONDS );

	$dest  = schiesser_reglage( 'email' ) ?: get_option( 'admin_email' );
	$sujet = 'Message depuis le site · ' . $nom;
	$corps = "Nom : $nom\nE-mail : $email\n" . ( $tel ? "Téléphone : $tel\n" : '' ) . "\nMessage :\n$msg\n\n--\nEnvoyé depuis " . home_url( '/' );
	$ok    = wp_mail( $dest, $sujet, $corps, array( 'Reply-To: ' . $nom . ' <' . $email . '>' ) );
	$aller( $ok ? 'ok' : 'erreur' );
}
add_action( 'admin_post_nopriv_schiesser_contact', 'schiesser_mq_envoi_contact' );
add_action( 'admin_post_schiesser_contact', 'schiesser_mq_envoi_contact' );

/* ------------------------------------------------------------------ */
/* Scripts du site                                                     */
/* ------------------------------------------------------------------ */

add_action( 'wp_enqueue_scripts', function () {
	wp_register_script( 'schiesser-maquette', SCHIESSER_URI . '/assets/js/maquette.js', array( 'schiesser-site' ), SCHIESSER_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'leaflet', SCHIESSER_URI . '/assets/vendor/leaflet/leaflet.js', array(), '1.9.4', array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_style( 'leaflet', SCHIESSER_URI . '/assets/vendor/leaflet/leaflet.css', array(), '1.9.4' );

	$post = get_post();
	if ( ! $post || false === strpos( $post->post_content, '<!-- wp:schiesser/' ) ) {
		return;
	}
	wp_enqueue_script( 'schiesser-maquette' );
	// La bibliothèque de carte n'est chargée que sur les pages qui affichent une carte.
	if ( has_block( 'schiesser/adresse', $post ) || has_block( 'schiesser/plan-horaires', $post ) ) {
		wp_enqueue_script( 'leaflet' );
		wp_enqueue_style( 'leaflet' );
	}
} );

<?php
/**
 * Compositions (sections toutes prêtes) et petits outils pour écrire des blocs.
 *
 * Dans l'éditeur : bouton + → onglet « Compositions » → catégorie « Schiesser ».
 * Chaque composition s'insère en un clic, puis chaque texte se modifie
 * directement et chaque image se remplace avec le bouton « Remplacer ».
 *
 * Les fonctions schiesser_bm_…() produisent le code exact des blocs WordPress ;
 * elles servent aussi à l'import du contenu de démonstration (inc/demo.php).
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Écriture des blocs                                                  */
/* ------------------------------------------------------------------ */

/** Un bloc quelconque (commentaires de bloc + HTML enregistré). */
function schiesser_bm( $nom, $attrs = array(), $html = '' ) {
	return get_comment_delimited_block_content( $nom, $attrs, $html );
}

/** Ajoute des attributs de classe (className) si un style est demandé. */
function schiesser_bm_style( $attrs, $style ) {
	if ( $style ) {
		$attrs['className'] = 'is-style-' . $style;
	}
	return $attrs;
}

function schiesser_bm_p( $texte, $style = '', $centre = false ) {
	$attrs   = schiesser_bm_style( array(), $style );
	$classes = array();
	if ( $centre ) {
		$attrs['align'] = 'center';
		$classes[]      = 'has-text-align-center';
	}
	if ( $style ) {
		$classes[] = 'is-style-' . $style;
	}
	$classe = $classes ? ' class="' . implode( ' ', $classes ) . '"' : '';
	return schiesser_bm( 'paragraph', $attrs, '<p' . $classe . '>' . $texte . '</p>' );
}

function schiesser_bm_titre( $texte, $niveau = 2, $style = '' ) {
	$attrs = schiesser_bm_style( 2 === $niveau ? array() : array( 'level' => $niveau ), $style );
	$class = 'wp-block-heading' . ( $style ? ' is-style-' . $style : '' );
	return schiesser_bm( 'heading', $attrs, '<h' . $niveau . ' class="' . $class . '">' . $texte . '</h' . $niveau . '>' );
}

function schiesser_bm_liste( $elements, $style = '' ) {
	$items = '';
	foreach ( $elements as $e ) {
		$items .= schiesser_bm( 'list-item', array(), '<li>' . $e . '</li>' );
	}
	$classe = $style ? ' class="is-style-' . $style . '"' : '';
	return schiesser_bm( 'list', schiesser_bm_style( array(), $style ), '<ul' . $classe . '>' . $items . '</ul>' );
}

/**
 * Image. Sans adresse, l'éditeur affiche « Choisir une image » (et rien n'apparaît sur le site).
 */
function schiesser_bm_image( $id = 0, $url = '', $alt = '', $style = '', $legende = '' ) {
	$attrs = array();
	if ( $id ) {
		$attrs['id'] = (int) $id;
	}
	$attrs['sizeSlug']        = 'large';
	$attrs['linkDestination'] = 'none';
	$attrs                    = schiesser_bm_style( $attrs, $style );

	$img = '<img' . ( $url ? ' src="' . esc_url( $url ) . '"' : '' ) . ' alt="' . esc_attr( $alt ) . '"' . ( $id ? ' class="wp-image-' . (int) $id . '"' : '' ) . '/>';
	$fig = '<figure class="wp-block-image size-large' . ( $style ? ' is-style-' . $style : '' ) . '">' . $img
		. ( $legende ? '<figcaption class="wp-element-caption">' . $legende . '</figcaption>' : '' ) . '</figure>';
	return schiesser_bm( 'image', $attrs, $fig );
}

/**
 * Boutons : liste de [ texte, lien, style ] (style : vert, contour, creme, lien).
 */
function schiesser_bm_boutons( $boutons, $centre = false ) {
	$html = '';
	foreach ( $boutons as $b ) {
		$style  = ( $b[2] ?? 'vert' ) === 'vert' ? '' : $b[2];
		$html  .= schiesser_bm(
			'button',
			schiesser_bm_style( array(), $style ),
			'<div class="wp-block-button' . ( $style ? ' is-style-' . $style : '' ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $b[1] ) . '">' . $b[0] . '</a></div>'
		);
	}
	$attrs = $centre ? array( 'layout' => array( 'type' => 'flex', 'justifyContent' => 'center' ) ) : array();
	return schiesser_bm( 'buttons', $attrs, '<div class="wp-block-buttons">' . $html . '</div>' );
}

/** Colonnes : liste de contenus (blocs déjà écrits), une entrée par colonne. */
function schiesser_bm_colonnes( $colonnes, $style = '' ) {
	$html = '';
	foreach ( $colonnes as $contenu ) {
		$html .= schiesser_bm( 'column', array(), '<div class="wp-block-column">' . $contenu . '</div>' );
	}
	return schiesser_bm( 'columns', schiesser_bm_style( array(), $style ), '<div class="wp-block-columns' . ( $style ? ' is-style-' . $style : '' ) . '">' . $html . '</div>' );
}

/** Question fréquente dépliable (bloc « Détails ») : la réponse est un ou plusieurs paragraphes. */
function schiesser_bm_question( $question, $reponse ) {
	return schiesser_bm( 'details', array(), '<details class="wp-block-details"><summary>' . $question . '</summary>' . schiesser_bm_p( $reponse ) . '</details>' );
}

function schiesser_bm_separateur( $style = 'losanges' ) {
	return schiesser_bm( 'separator', schiesser_bm_style( array(), $style ), '<hr class="wp-block-separator has-alpha-channel-opacity' . ( $style ? ' is-style-' . $style : '' ) . '"/>' );
}

function schiesser_bm_citation( $texte, $auteur = '', $style = 'grande' ) {
	return schiesser_bm(
		'quote',
		schiesser_bm_style( array(), $style ),
		'<blockquote class="wp-block-quote' . ( $style ? ' is-style-' . $style : '' ) . '">' . schiesser_bm_p( $texte ) . ( $auteur ? '<cite>' . $auteur . '</cite>' : '' ) . '</blockquote>'
	);
}

function schiesser_bm_groupe( $contenu, $style = '' ) {
	return schiesser_bm(
		'group',
		schiesser_bm_style( array( 'layout' => array( 'type' => 'constrained' ) ), $style ),
		'<div class="wp-block-group' . ( $style ? ' is-style-' . $style : '' ) . '">' . $contenu . '</div>'
	);
}

function schiesser_bm_code_court( $code ) {
	return schiesser_bm( 'shortcode', array(), $code );
}

function schiesser_bm_html( $code ) {
	return schiesser_bm( 'html', array(), $code );
}

/** Section maison : réglages (numero, titre, note, fond, largeur, centre, ancre, entete) + contenu. */
function schiesser_bm_section( $attrs, $contenu ) {
	return schiesser_bm( 'schiesser/section', $attrs, $contenu );
}

/* ------------------------------------------------------------------ */
/* Compositions                                                        */
/* ------------------------------------------------------------------ */

add_action( 'init', function () {
	register_block_pattern_category( 'schiesser', array(
		'label'       => 'Schiesser',
		'description' => 'Sections toutes prêtes aux couleurs de la maison.',
	) );

	foreach ( schiesser_compositions() as $slug => $c ) {
		register_block_pattern( 'schiesser/' . $slug, array_merge( array(
			'categories'    => array( 'schiesser' ),
			'viewportWidth' => 1400,
		), $c ) );
	}
} );

/* Les compositions de WordPress (générales, en anglais) sont retirées : seules celles de la maison restent. */
add_action( 'after_setup_theme', function () {
	remove_theme_support( 'core-block-patterns' );
} );
add_filter( 'should_load_remote_block_patterns', '__return_false' );

function schiesser_compositions() {
	$boutique = '/boutique/';
	$contact  = '/contact/';

	$texte_photo = function ( $ancre = '' ) use ( $boutique ) {
		return schiesser_bm_section(
			array_filter( array( 'numero' => '01', 'titre' => 'Un titre qui donne <em>envie</em>', 'note' => 'Une phrase courte pour situer la section.', 'ancre' => $ancre ) ),
			schiesser_bm_colonnes( array(
			schiesser_bm_p( 'Une introduction de deux ou trois lignes : ce que l’on trouve ici, pour qui, et pourquoi c’est différent.', 'chapeau' )
			. schiesser_bm_p( 'Un paragraphe de détail. Cliquez sur ce texte pour l’écrire. Pour mettre un mot en valeur, sélectionnez-le puis cliquez sur B (gras) ou I (italique).' )
			. schiesser_bm_boutons( array( array( 'En savoir plus', $boutique, 'lien' ) ) ),
			schiesser_bm_image( 0, '', '', 'vitrine' ),
			) )
		);
	};

	$trois_points = schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Bon à <em>savoir</em>', 'fond' => 'clair' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Premier point', 3 ) . schiesser_bm_p( 'Deux lignes pour expliquer ce point. Restez simple et concret.' ),
			schiesser_bm_titre( 'Deuxième point', 3 ) . schiesser_bm_p( 'Deux lignes pour expliquer ce point. Restez simple et concret.' ),
			schiesser_bm_titre( 'Troisième point', 3 ) . schiesser_bm_p( 'Deux lignes pour expliquer ce point. Restez simple et concret.' ),
		), 'filets' )
	);

	$cartes = schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Trois façons de <em>commander</em>', 'fond' => 'alterne' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'En boutique', 3 ) . schiesser_bm_p( 'Tous les jours au Marktplatz, sans réservation.' ) . schiesser_bm_boutons( array( array( 'Nous visiter', '/nous-visiter/', 'lien' ) ) ),
			schiesser_bm_titre( 'Par téléphone', 3 ) . schiesser_bm_p( 'Pour une commande de fête ou un grand coffret : [schiesser_telephone]' ) . schiesser_bm_boutons( array( array( 'Nous appeler', $contact, 'lien' ) ) ),
			schiesser_bm_titre( 'Par e-mail', 3 ) . schiesser_bm_p( 'Écrivez-nous, nous répondons sous 24 heures ouvrées.' ) . schiesser_bm_boutons( array( array( 'Écrire', $contact, 'lien' ) ) ),
		), 'cartes' )
	);

	$faq = schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Questions <em>fréquentes</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Première question que posent vos clients ?', 'Une réponse claire en deux ou trois phrases.' )
		. schiesser_bm_question( 'Deuxième question ?', 'Une réponse claire en deux ou trois phrases.' )
		. schiesser_bm_question( 'Troisième question ?', 'Une réponse claire en deux ou trois phrases.' )
	);

	$horaires = schiesser_bm_section(
		array( 'numero' => '05', 'titre' => 'Horaires et <em>adresse</em>', 'note' => 'Mis à jour automatiquement depuis les Réglages maison.', 'ancre' => 'horaires' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Horaires', 3 ) . schiesser_bm_code_court( '[schiesser_horaires]' ),
			schiesser_bm_titre( 'Adresse', 3 ) . schiesser_bm_p( '[schiesser_adresse]' ) . schiesser_bm_p( '[schiesser_telephone] · [schiesser_email]' ) . schiesser_bm_code_court( '[schiesser_itineraire texte="Itinéraire"]' ),
		) )
	);

	$appel = schiesser_bm_section(
		array( 'entete' => false, 'fond' => 'sombre', 'centre' => true, 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Au plaisir de vous recevoir', 'surtitre', true )
		. schiesser_bm_titre( 'Passez nous <em>voir</em>', 2 )
		. schiesser_bm_p( 'Une phrase qui invite à venir ou à commander.', '', true )
		. schiesser_bm_boutons( array( array( 'Nous visiter', '/nous-visiter/', 'creme' ), array( 'Nous écrire', $contact, 'contour' ) ), true )
	);

	$citation = schiesser_bm_section(
		array( 'entete' => false, 'fond' => 'clair', 'centre' => true, 'largeur' => 'lecture' ),
		schiesser_bm_citation( 'Une phrase forte, un souvenir, un avis de client.', 'Nom, fonction' )
	);

	$lecture = schiesser_bm_section(
		array( 'numero' => '06', 'titre' => 'Un texte de <em>lecture</em>', 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Un chapeau d’introduction, un peu plus grand que le texte courant.', 'chapeau' )
		. schiesser_bm_p( 'Le texte courant. Appuyez sur Entrée pour créer un nouveau paragraphe, tapez / pour chercher un bloc (image, liste, citation…).' )
		. schiesser_bm_titre( 'Un sous-titre', 3 )
		. schiesser_bm_liste( array( 'Premier élément', 'Deuxième élément', 'Troisième élément' ) )
		. schiesser_bm_separateur()
		. schiesser_bm_p( 'Un dernier paragraphe pour conclure.' )
	);

	$html = schiesser_bm_section(
		array( 'numero' => '07', 'titre' => 'Réserver une <em>table</em>', 'note' => 'Zone de code : collez ici un widget externe (réservation, carte, avis…).' ),
		schiesser_bm_p( 'Le texte au-dessus du widget reste modifiable normalement.' )
		. schiesser_bm_html( "<!-- Collez ici le code fourni par le service (réservation, carte, avis…) -->\n<div class=\"s-widget\">Le widget s'affichera ici.</div>" )
	);

	$selection = schiesser_bm( 'schiesser/produits', array(
		'numero'    => '01',
		'titre'     => 'En <em>vitrine</em>',
		'note'      => 'Une sélection de la maison.',
		'ancre'     => 'selection',
		'limite'    => 4,
		'filtres'   => false,
		'lienTexte' => 'Voir toute la boutique',
	) );

	$compos = array(
		'texte-photo'       => array( 'title' => 'Texte et photo', 'description' => 'Un titre, un texte, un lien, et une photo encadrée.', 'content' => $texte_photo(), 'keywords' => array( 'image', 'texte', 'colonnes' ) ),
		'trois-points'      => array( 'title' => 'Trois points forts (grille à filets)', 'description' => 'Trois colonnes séparées par des filets fins.', 'content' => $trois_points, 'keywords' => array( 'colonnes', 'avantages' ) ),
		'trois-cartes'      => array( 'title' => 'Trois cartes', 'description' => 'Trois encadrés avec un titre, un texte et un lien.', 'content' => $cartes, 'keywords' => array( 'cartes', 'colonnes' ) ),
		'questions'         => array( 'title' => 'Questions fréquentes', 'description' => 'Questions dépliables : Google les lit comme une FAQ.', 'content' => $faq, 'keywords' => array( 'faq', 'questions' ) ),
		'horaires-adresse'  => array( 'title' => 'Horaires et adresse', 'description' => 'Toujours à jour : les informations viennent des Réglages maison.', 'content' => $horaires, 'keywords' => array( 'horaires', 'adresse', 'contact' ) ),
		'appel'             => array( 'title' => 'Appel final (fond chocolat)', 'description' => 'Grand titre centré et deux boutons.', 'content' => $appel, 'keywords' => array( 'cta', 'bouton' ) ),
		'citation'          => array( 'title' => 'Grande citation', 'description' => 'Une phrase mise en valeur.', 'content' => $citation, 'keywords' => array( 'citation', 'avis' ) ),
		'texte-lecture'     => array( 'title' => 'Texte de lecture', 'description' => 'Texte long, largeur confortable.', 'content' => $lecture, 'keywords' => array( 'texte', 'article' ) ),
		'zone-html'         => array( 'title' => 'Zone de code (widget externe)', 'description' => 'Pour coller un code de réservation, de carte ou d’avis.', 'content' => $html, 'keywords' => array( 'html', 'code', 'widget' ) ),
		'selection-produits' => array( 'title' => 'Sélection de produits', 'description' => 'Les 4 premiers produits et un bouton vers la boutique.', 'content' => $selection, 'keywords' => array( 'produits', 'boutique' ) ),
	);

	/* Modèles proposés à la création d'une page (fenêtre « Choisir une composition »). */
	$hero = function ( $titre, $texte, $hauteur = 'page' ) {
		return schiesser_bm( 'schiesser/hero', array(
			'surtitre'     => 'Confiserie Schiesser · Bâle',
			'titre'        => $titre,
			'texte'        => $texte,
			'bouton1Texte' => 'Découvrir',
			'bouton1Lien'  => '#section-1',
			'bouton2Texte' => '',
			'hauteur'      => $hauteur,
		) );
	};
	$compos['page-complete'] = array(
		'title'       => 'Page complète : grande photo, sections, appel final',
		'description' => 'Idéal pour une nouvelle page de présentation.',
		'content'     => $hero( 'Le titre de la <em>page</em>', 'Une phrase d’introduction qui contient le sujet principal de la page.' )
			. $texte_photo( 'section-1' ) . $trois_points . $appel,
		'blockTypes'  => array( 'core/post-content' ),
		'postTypes'   => array( 'page' ),
	);
	$compos['page-simple'] = array(
		'title'       => 'Page simple : titre et texte',
		'description' => 'Mentions légales, conditions, page d’information.',
		'content'     => schiesser_bm_section( array( 'entete' => false, 'largeur' => 'lecture' ),
			schiesser_bm_p( 'Écrivez votre texte ici. Le titre de la page s’affiche automatiquement au-dessus.', 'chapeau' )
			. schiesser_bm_titre( 'Un sous-titre', 2 )
			. schiesser_bm_p( 'Un paragraphe.' ) ),
		'blockTypes'  => array( 'core/post-content' ),
		'postTypes'   => array( 'page' ),
	);
	return $compos;
}

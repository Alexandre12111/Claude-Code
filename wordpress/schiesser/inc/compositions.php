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
	$boutique = '/confiserie/';
	$contact  = '/kontakt/';
	$besuch   = '/besuch/';
	// Textes des compositions en allemand : ils s'affichent sur le site (les noms des compositions restent en français).

	$texte_photo = function ( $ancre = '' ) use ( $boutique ) {
		return schiesser_bm_section(
			array_filter( array( 'numero' => '01', 'titre' => 'Ein Titel, der <em>Lust</em> macht', 'note' => 'Ein kurzer Satz, der den Abschnitt einordnet.', 'ancre' => $ancre ) ),
			schiesser_bm_colonnes( array(
			schiesser_bm_p( 'Eine Einleitung in zwei, drei Zeilen: was man hier findet, für wen, und was es besonders macht.', 'chapeau' )
			. schiesser_bm_p( 'Ein Absatz mit Details. Hier steht Ihr Text.' )
			. schiesser_bm_boutons( array( array( 'Mehr erfahren', $boutique, 'lien' ) ) ),
			schiesser_bm_image( 0, '', '', 'vitrine' ),
			) )
		);
	};

	$trois_points = schiesser_bm_section(
		array( 'numero' => '02', 'titre' => 'Gut zu <em>wissen</em>', 'fond' => 'clair' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Erster Punkt', 3 ) . schiesser_bm_p( 'Deux lignes pour expliquer ce point. Restez simple et concret.' ),
			schiesser_bm_titre( 'Zweiter Punkt', 3 ) . schiesser_bm_p( 'Deux lignes pour expliquer ce point. Restez simple et concret.' ),
			schiesser_bm_titre( 'Dritter Punkt', 3 ) . schiesser_bm_p( 'Deux lignes pour expliquer ce point. Restez simple et concret.' ),
		), 'filets' )
	);

	$cartes = schiesser_bm_section(
		array( 'numero' => '03', 'titre' => 'Drei Wege zum <em>Bestellen</em>', 'fond' => 'alterne' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Im Laden', 3 ) . schiesser_bm_p( 'Jeden Tag am Marktplatz, ohne Reservation.' ) . schiesser_bm_boutons( array( array( 'Besuch planen', $besuch, 'lien' ) ) ),
			schiesser_bm_titre( 'Per Telefon', 3 ) . schiesser_bm_p( 'Für eine Festtagsbestellung oder ein grosses Geschenk: [schiesser_telephone]' ) . schiesser_bm_boutons( array( array( 'Anrufen', $contact, 'lien' ) ) ),
			schiesser_bm_titre( 'Per E-Mail', 3 ) . schiesser_bm_p( 'Schreiben Sie uns, wir antworten innerhalb eines Werktags.' ) . schiesser_bm_boutons( array( array( 'Schreiben', $contact, 'lien' ) ) ),
		), 'cartes' )
	);

	$faq = schiesser_bm_section(
		array( 'numero' => '04', 'titre' => 'Häufige <em>Fragen</em>', 'largeur' => 'lecture' ),
		schiesser_bm_question( 'Erste Frage Ihrer Gäste?', 'Eine klare Antwort in zwei, drei Sätzen.' )
		. schiesser_bm_question( 'Zweite Frage?', 'Eine klare Antwort in zwei, drei Sätzen.' )
		. schiesser_bm_question( 'Dritte Frage?', 'Eine klare Antwort in zwei, drei Sätzen.' )
	);

	$horaires = schiesser_bm_section(
		array( 'numero' => '05', 'titre' => 'Öffnungszeiten und <em>Adresse</em>', 'note' => 'Immer aktuell.', 'ancre' => 'oeffnungszeiten' ),
		schiesser_bm_colonnes( array(
			schiesser_bm_titre( 'Öffnungszeiten', 3 ) . schiesser_bm_code_court( '[schiesser_horaires]' ),
			schiesser_bm_titre( 'Adresse', 3 ) . schiesser_bm_p( '[schiesser_adresse]' ) . schiesser_bm_p( '[schiesser_telephone] · [schiesser_email]' ) . schiesser_bm_code_court( '[schiesser_itineraire texte="Route planen"]' ),
		) )
	);

	$appel = schiesser_bm_section(
		array( 'entete' => false, 'fond' => 'sombre', 'centre' => true, 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Wir freuen uns auf Sie', 'surtitre', true )
		. schiesser_bm_titre( 'Besuchen Sie <em>uns</em>', 2 )
		. schiesser_bm_p( 'Ein Satz, der zum Vorbeikommen oder Bestellen einlädt.', '', true )
		. schiesser_bm_boutons( array( array( 'Besuch planen', $besuch, 'creme' ), array( 'Schreiben Sie uns', $contact, 'contour' ) ), true )
	);

	$citation = schiesser_bm_section(
		array( 'entete' => false, 'fond' => 'clair', 'centre' => true, 'largeur' => 'lecture' ),
		schiesser_bm_citation( 'Ein starker Satz, eine Erinnerung, eine Stimme eines Gastes.', 'Name, Funktion' )
	);

	$lecture = schiesser_bm_section(
		array( 'numero' => '06', 'titre' => 'Ein <em>Lesetext</em>', 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Ein Einleitungssatz, etwas grösser als der Fliesstext.', 'chapeau' )
		. schiesser_bm_p( 'Der Fliesstext. Hier steht Ihr Text.' )
		. schiesser_bm_titre( 'Ein Zwischentitel', 3 )
		. schiesser_bm_liste( array( 'Erster Punkt', 'Zweiter Punkt', 'Dritter Punkt' ) )
		. schiesser_bm_separateur()
		. schiesser_bm_p( 'Ein letzter Absatz zum Abschluss.' )
	);

	$html = schiesser_bm_section(
		array( 'numero' => '07', 'titre' => 'Einen Tisch <em>reservieren</em>', 'note' => 'Reservieren Sie bequem online.' ),
		schiesser_bm_p( 'Ein kurzer Text über dem Widget.' )
		. schiesser_bm_html( "<!-- Collez ici le code fourni par le service (réservation, carte, avis…) -->\n<div class=\"s-widget\">Hier erscheint das Widget.</div>" )
	);

	$selection = schiesser_bm( 'schiesser/produits', array(
		'numero'    => '01',
		'titre'     => 'In der <em>Vitrine</em>',
		'note'      => 'Eine Auswahl des Hauses.',
		'ancre'     => 'selection',
		'limite'    => 4,
		'filtres'   => false,
		'lienTexte' => 'Ganzes Sortiment ansehen',
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
			'surtitre'     => 'Confiserie Schiesser · Basel',
			'titre'        => $titre,
			'texte'        => $texte,
			'bouton1Texte' => 'Entdecken',
			'bouton1Lien'  => '#section-1',
			'bouton2Texte' => '',
			'hauteur'      => $hauteur,
		) );
	};
	$compos['page-complete'] = array(
		'title'       => 'Page complète : grande photo, sections, appel final',
		'description' => 'Idéal pour une nouvelle page de présentation.',
		'content'     => $hero( 'Der Titel der <em>Seite</em>', 'Ein Einleitungssatz mit dem Hauptthema der Seite.' )
			. $texte_photo( 'section-1' ) . $trois_points . $appel,
		'blockTypes'  => array( 'core/post-content' ),
		'postTypes'   => array( 'page' ),
	);
	$compos['page-simple'] = array(
		'title'       => 'Page simple : titre et texte',
		'description' => 'Mentions légales, conditions, page d’information.',
		'content'     => schiesser_bm_section( array( 'entete' => false, 'largeur' => 'lecture' ),
			schiesser_bm_p( 'Hier steht Ihr Text. Der Seitentitel erscheint automatisch darüber.', 'chapeau' )
			. schiesser_bm_titre( 'Ein Zwischentitel', 2 )
			. schiesser_bm_p( 'Ein Absatz.' ) ),
		'blockTypes'  => array( 'core/post-content' ),
		'postTypes'   => array( 'page' ),
	);
	return $compos;
}

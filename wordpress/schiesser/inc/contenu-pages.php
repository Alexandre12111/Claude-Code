<?php
/**
 * Pages du site, en allemand (textes et réglages SEO), composées avec les blocs de la maquette.
 *
 * Les faits historiques viennent de la carte du Tea Room fournie par la maison (fondation en 1870
 * par le confiseur glaronais Rudolf Schiesser ; façade néogothique, Tea Room et « Rathstübli »
 * aménagés par son fils Hans ; plus ancien café de Suisse). Les deux photos d'archive (1889, 1900)
 * viennent du même document.
 *
 * Import : inc/demo.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Photo d'archive livrée avec le thème (assets/images), copiée une fois dans la médiathèque.
 */
function schiesser_demo_image_theme( $fichier, $alt ) {
	$existant = get_posts( array(
		'post_type'   => 'attachment',
		'meta_key'    => '_schiesser_source', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'  => 'theme:' . $fichier, // phpcs:ignore WordPress.DB.SlowDBQuery
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $existant ) {
		return (int) $existant[0];
	}
	$source = SCHIESSER_DIR . '/assets/images/' . $fichier;
	if ( ! file_exists( $source ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $fichier );
	copy( $source, $tmp );
	$id = media_handle_sideload( array( 'name' => $fichier, 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_schiesser_source', 'theme:' . $fichier );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/**
 * Les pages : titre, adresse, anciennes adresses (redirigées), contenu (blocs) et SEO.
 */
function schiesser_demo_pages() {
	$b  = 'schiesser_bm';
	$l  = 'schiesser_demo_lien';
	$u  = function ( $chemin ) {
		return home_url( $chemin );
	};
	// Photo de démonstration (Unsplash) ou photo d'archive du thème (« theme:fichier.jpg »).
	$img = function ( $photo, $nom, $alt, $prefixe = 'image' ) {
		$id = 0 === strpos( $photo, 'theme:' ) ? schiesser_demo_image_theme( substr( $photo, 6 ), $alt ) : schiesser_demo_image( $photo, $nom, $alt );
		return array(
			$prefixe . 'Id'  => $id,
			$prefixe . 'Url' => $id ? (string) wp_get_attachment_image_url( $id, 'large' ) : '',
			$prefixe . 'Alt' => $alt,
		);
	};
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
	$ids_produits = function ( $noms ) {
		$ids = array();
		foreach ( $noms as $nom ) {
			$p = schiesser_posts_allemands( array( 'post_type' => SCHIESSER_PRODUIT, 'title' => $nom, 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids' ) );
			if ( $p ) {
				$ids[] = $p[0];
			}
		}
		return implode( ',', $ids );
	};

	$P = array(
		'pral'     => '1481391319762-47dff72954d9',
		'truf'     => '1548907040-4baa42d10919',
		'cake'     => '1565958011703-44f9829ba187',
		'coff'     => '1549007994-cb92caebd54b',
		'tabl'     => '1464195244916-405fa0a82545',
		'bisc'     => '1556910103-1c02745aae4d',
		'boutique' => '1509440159596-0249088772ff',
		'salon'    => '1445116572660-236099ec97a0',
		'facade'   => '1517244683847-7456b63c5969',
		'tables'   => '1600891964092-4316c288032e',
		'relais'   => '1587248720327-8eb72564be1e',
		'jour'     => '1551024506-0bccd828d307',
		'a1889'    => 'theme:archiv-fassade-patisserie-leckerly.jpg',
		'neubau'   => 'theme:archiv-schiesserhaus-marktplatz.jpg',
		'schaufenster' => 'theme:archiv-schaufenster-seit-1870.jpg',
		'theke'    => 'theme:archiv-verkaufstheke-confiserie.jpg',
		'torten'   => 'theme:archiv-konditoren-torten.jpg',
		'backstube' => 'theme:archiv-backstube-confiserie.jpg',
		'kasse'    => 'theme:archiv-kasse-tea-room.jpg',
		'a1900'    => 'theme:schiesser-marktplatz-1900.jpg',
	);
	$alt1889 = 'Fassade der Confiserie Schiesser um 1889 mit den Schriftzügen Patisserie, Leckerly und Schiesser Confiseur';
	$alt1900 = 'Markttag vor der Confiserie Schiesser am Basler Marktplatz um 1900';
	$alt_neubau       = 'Das Schiesserhaus am Marktplatz mit Café, Tea-Room und Confiserie, davor der Markt';
	$alt_schaufenster = 'Historisches Schaufenster der Confiserie Schiesser mit dem Schriftzug «Seit 1870 Schiesser»';
	$alt_theke        = 'Historische Verkaufstheke der Confiserie Schiesser mit Pralinen in der Vitrine';
	$alt_torten       = 'Drei Konditoren der Confiserie Schiesser dekorieren Torten in der Backstube';
	$alt_backstube    = 'Junger Konditor in der historischen Backstube der Confiserie Schiesser';
	$alt_kasse        = 'Kassiererin am Buffet des Tea Room der Confiserie Schiesser, Archivbild';

	$url_confiserie = $u( '/confiserie/' );
	$url_tearoom    = $u( '/tea-room/' );
	$url_geschichte = $u( '/geschichte/' );
	$url_besuch     = $u( '/besuch/' );
	$url_kontakt    = $u( '/kontakt/' );
	$url_firmen     = $u( '/firmengeschenke/' );
	$devis          = schiesser_mailto( 'Offertanfrage: Firmengeschenke', array(
		'Firma:',
		'Anzahl und Format (z. B. Pralinen 9er- oder 16er-Box, Läckerli-Box, Bonbonnière):',
		'Gewünschtes Übergabedatum:',
		'Botschaft oder Logo:',
		'Rechnungsadresse:',
		'Name und Telefon:',
	) );

	/* ================= Startseite ================= */
	$startseite = schiesser_demo_hero( array(
		'hauteur'      => 'accueil',
		'ariane'       => false,
		'sceau'        => true,
		'surtitre'     => 'Das älteste Kaffeehaus der Schweiz · seit 1870',
		'titre'        => 'Confiserie in Basel, <br><em>seit 1870 am&nbsp;Marktplatz</em>',
		'texte'        => 'Confiserie mit eigener Backstube im Erdgeschoss, Tea Room und «Rathstübli» im ersten Stock, direkt gegenüber dem Rathaus.',
		'bouton1Texte' => 'Unser Sortiment',
		'bouton1Lien'  => '#kreationen',
		'bouton2Texte' => 'Besuchen Sie uns',
		'bouton2Lien'  => '#besuch',
	), $P['pral'], 'confiserie-basel-pralinen', 'Pralinen und Schokolade in der Vitrine der Confiserie Schiesser in Basel' )
		. $b( 'schiesser/vitrine-jour', array( 'titre' => 'Heute in der Vitrine' ) )
		. $b( 'schiesser/catalogue', array( 'numero' => '01', 'titre' => 'Unser Sortiment', 'note' => 'Läckerli, Pralinen, Torten und Gebäck, alles aus der eigenen Backstube.', 'ancre' => 'kreationen',
			'produits' => $ids_produits( array( 'Basler Läckerli', 'Pralinen', 'Truffes-Cake', 'Bonbonnière', 'Marrons glacés', 'Zopf' ) ) ) )
		. $b( 'schiesser/etages', array( 'numero' => '02', 'titre' => 'Zwei Etagen, ein Haus', 'note' => 'Im Erdgeschoss die Confiserie, im ersten Stock der Tea Room. Wer hinauf will, geht durch den Laden.', 'fond' => 'alterne' ),
			$enfants( 'schiesser/etage', array(
				array( 'numero' => '0', 'surtitre' => 'Erdgeschoss', 'titre' => 'Die Confiserie', 'texte' => 'Läckerli, Pralinen, Torten und frisches Gebäck zum Mitnehmen, direkt aus der eigenen Backstube.', 'lienTexte' => 'Zur Confiserie', 'lienUrl' => $url_confiserie ) + $img( $P['boutique'], 'confiserie-marktplatz-basel', 'Der Laden der Confiserie Schiesser im Erdgeschoss' ),
				array( 'numero' => '1', 'surtitre' => 'Erster Stock', 'titre' => 'Tea Room & Rathstübli', 'texte' => 'Kaffee, Tee, hausgemachte Schokolade und Frühstück, mit Blick auf den Marktplatz und das Rathaus.', 'lienTexte' => 'Zum Tea Room', 'lienUrl' => $url_tearoom ) + $img( $P['salon'], 'tea-room-basel', 'Der Tea Room im ersten Stock mit Fenstern zum Marktplatz' ),
			) ) )
		. $sep
		. $b( 'schiesser/savoir-faire', array( 'numero' => '03', 'titre' => 'Handwerk, das man sieht', 'note' => 'Vieles entsteht wie damals im Haus, zum Teil nach Rezepten aus den Gründerjahren.', 'fond' => 'clair', 'ancre' => 'handwerk' ),
			$enfants( 'schiesser/geste', array(
				array( 'titre' => 'Backen', 'texte' => 'Honig, Mandeln und Gewürze für die Läckerli, Butter und Mehl für die Gipfeli: gebacken wird im eigenen Haus.' ) + $img( $P['backstube'], '', $alt_backstube ),
				array( 'titre' => 'Formen', 'texte' => 'Jedes Stück wird von Hand geschnitten, gerollt oder getunkt, ohne Abkürzung.' ) + $img( $P['torten'], '', $alt_torten ),
				array( 'titre' => 'Veredeln', 'texte' => 'Glasur, Dekor und Kontrolle: das Finish macht den Unterschied.' ) + $img( $P['tabl'], 'schokolade-handgemacht-basel', 'Veredeln der Schokolade von Hand' ),
				array( 'titre' => 'Die Vitrine', 'texte' => 'Am Morgen wandert alles in die Vitrine am Marktplatz und hinauf in den Tea Room.' ) + $img( $P['schaufenster'], '', $alt_schaufenster ),
			) ) )
		. $b( 'schiesser/frise', array( 'numero' => '04', 'titre' => 'Die Zeitleiste', 'note' => 'Blättern Sie durch die Geschichte des Hauses. Ziehen oder mit den Pfeilen.', 'ancre' => 'geschichte', 'affichage' => 'cartes', 'indication' => 'Zum Entdecken ziehen →', 'retenirLibelle' => 'Gut zu wissen' ),
			$enfants( 'schiesser/date', array(
				array( 'annee' => '1870', 'titre' => 'Die Gründung', 'texte' => 'Der Glarner Konditor Rudolf Schiesser eröffnet seine Confiserie am Basler Marktplatz, gegenüber dem Rathaus.' ) + $img( $P['a1889'], '', $alt1889 ),
				array( 'annee' => '1889', 'titre' => 'Patisserie, Leckerly', 'texte' => 'Auf der Fassade steht schon, wofür das Haus bekannt ist: «Patisserie», «Leckerly», «Schiesser Confiseur».' ) + $img( $P['a1889'], '', $alt1889 ),
				array( 'annee' => '1900', 'titre' => 'Mitten im Markt', 'texte' => 'Vor dem Haus die Marktstände, im Haus die Confiserie. Der Markt findet bis heute vor unseren Fenstern statt.' ) + $img( $P['a1900'], '', $alt1900 ),
				array( 'annee' => 'Nach 1900', 'titre' => 'Tea Room und Rathstübli', 'texte' => 'Hans Schiesser gibt dem Haus die neugotische Fassade nach dem Vorbild des Rathauses und richtet im ersten Stock Tea Room und «Rathstübli» ein.' ) + $img( $P['salon'], 'tea-room-basel', 'Der Tea Room im ersten Stock' ),
				array( 'annee' => 'Heute', 'titre' => 'Immer noch am Marktplatz', 'texte' => 'Kaffeehaus, Tea Room und Confiserie mit eigener Backstube unter einem Dach. ' . $l( '/geschichte/', 'Unsere Geschichte lesen' ) . '.' ) + $img( $P['jour'], 'confiserie-schiesser-heute', 'Die Confiserie Schiesser heute' ),
			) ) )
		. $b( 'schiesser/adresse', array( 'numero' => '05', 'titre' => 'So finden Sie uns', 'note' => 'Am Marktplatz, direkt gegenüber dem Rathaus.', 'fond' => 'alterne', 'ancre' => 'besuch', 'b1Texte' => 'Schreiben Sie uns', 'b2Texte' => 'Route' ),
			$ligne( 'Adresse', 'adresse' )
			. $ligne( 'Öffnungszeiten', 'horaires' )
			. $ligne( 'Gruppen', '', 'Auf Reservation', 'Kaffee, Degustationen, Geschenkboxen' )
			. $ligne( 'Kontakt', 'contact' ) )
		. $b( 'schiesser/intro', array( 'numero' => '06', 'titre' => 'Das Haus in Kürze', 'lead' => 'Seit 1870 am Basler Marktplatz: Confiserie, Kaffeehaus und Tea Room unter einem Dach.' ),
			schiesser_bm_p( 'Im Erdgeschoss die ' . $l( '/confiserie/', 'Confiserie' ) . ' mit eigener Backstube: Basler Läckerli, Pralinen, Torten und frisches Gebäck. Im ersten Stock der ' . $l( '/tea-room/', 'Tea Room' ) . ' und das «Rathstübli», wo Baslerinnen und Basler wie Gäste aus aller Welt seit über hundert Jahren debattieren und geniessen.' )
			. schiesser_bm_p( 'Wir sind für Sie da: [schiesser_horaires_phrase]. Und hinter jeder Vitrine stecken über 150 Jahre ' . $l( '/geschichte/', 'Geschichte' ) . '.' ) )
		. $b( 'schiesser/faq', array( 'numero' => '07', 'titre' => 'Häufige Fragen zur Confiserie', 'note' => 'Das Wichtigste vor Ihrem Besuch.', 'fond' => 'clair' ),
			$q( 'Wo kauft man Basler Läckerli in Basel?', 'In der ' . $l( '/confiserie/', 'Confiserie Schiesser am Marktplatz' ) . ', gegenüber dem Rathaus. Unsere Basler Läckerli gibt es zu 100 g, 200 g und 500 g sowie in der Geschenkbox. Sie halten mehrere Wochen und reisen sehr gut.' )
			. $q( 'Wann ist die Confiserie geöffnet?', 'Wir sind für Sie da: [schiesser_horaires_phrase]. An Feiertagen können die Zeiten abweichen: siehe ' . $l( '/besuch/', 'Besuch' ) . ', oder rufen Sie uns an.' )
			. $q( 'Ist das wirklich das älteste Kaffeehaus der Schweiz?', 'Das Haus wurde 1870 gegründet und vereint Kaffeehaus, Tea Room und Confiserie mit eigener Backstube unter einem Dach: eine Verbindung, die in der Schweiz wohl nirgends sonst so weitgehend im Original erhalten ist. Mehr dazu in unserer ' . $l( '/geschichte/', 'Geschichte' ) . '.' )
			. $q( 'Was bringt man aus Basel als süsses Souvenir mit?', 'Basler Läckerli, die mehrere Wochen halten, oder unsere Pralinen in der 9er- oder 16er-Box. Für grosse Geschenke gibt es die Bonbonnière bis 850 g, für Ihre Kundschaft unsere ' . $l( '/firmengeschenke/', 'Firmengeschenke aus Basel' ) . '.' ) );

	/* ================= Confiserie ================= */
	$confiserie = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'surtitre'     => 'Erdgeschoss · Marktplatz',
		'titre'        => 'Confiserie in&nbsp;Basel, <br><em>aus der eigenen Backstube</em>',
		'texte'        => 'Läckerli, Pralinen, Torten und frisches Gebäck, hergestellt in unserer Backstube am Marktplatz.',
		'bouton1Texte' => 'Zum Sortiment',
		'bouton1Lien'  => '#sortiment',
		'bouton2Texte' => 'Der Laden in Bildern',
		'bouton2Lien'  => '#bilder',
	), $P['boutique'], 'confiserie-marktplatz-basel', 'Vitrine mit handgemachten Pralinen in der Confiserie Schiesser am Marktplatz Basel' )
		. $live( array( 'Einkauf', 'Vor Ort, an der Theke', 'Onlineshop folgt', '' ), array( 'Bestellen', '', 'Torten und Geschenkboxen auf Anfrage', 'telephone' ) )
		. $b( 'schiesser/produits', array( 'numero' => '01', 'titre' => 'Unser Sortiment', 'note' => 'Klicken Sie auf ein Produkt: alle Formate und Preise.', 'ancre' => 'sortiment', 'lienTexte' => '' ) )
		. $b( 'schiesser/galerie', array( 'numero' => '02', 'titre' => 'Der Laden in Bildern', 'note' => 'Von der Vitrine bis zur Backstube: alles an einem Ort.', 'fond' => 'sombre', 'marque' => true, 'ancre' => 'bilder' ),
			$enfants( 'schiesser/vue', array(
				array( 'titre' => 'Die Vitrine', 'texte' => 'Jeden Morgen neu bestückt. Was Sie sehen, kommt wenige Stunden vorher aus der Backstube.' ) + $img( $P['schaufenster'], '', $alt_schaufenster ),
				array( 'titre' => 'Die Theke', 'texte' => 'Hier wählt man Stück für Stück, lässt sich beraten und eine Box zusammenstellen.' ) + $img( $P['theke'], '', $alt_theke ),
				array( 'titre' => 'Die Backstube', 'texte' => 'Sie arbeitet direkt hinter dem Laden, wie seit den Gründerjahren.' ) + $img( $P['backstube'], '', $alt_backstube ),
				array( 'titre' => 'Das Finish', 'texte' => 'Glasieren, tunken, dekorieren: der langsamste Schritt, und der, der ein Haus ausmacht.' ) + $img( $P['torten'], '', $alt_torten ),
				array( 'titre' => 'Die Verpackung', 'texte' => 'Jede Box wird von Hand gefüllt und verschlossen. Für Flug oder Zug verpacken wir auf Wunsch besonders sicher.' ) + $img( $P['coff'], 'geschenkbox-pralinen', 'Pralinenbox, von Hand verpackt' ),
				array( 'titre' => 'Die Fassade', 'texte' => 'Am Marktplatz, gegenüber dem Rathaus. «Schiesser Confiseur» stand schon 1889 darauf.' ) + $img( $P['a1889'], '', $alt1889 ),
			) ) )
		. $sep
		. $b( 'schiesser/infos', array( 'numero' => '03', 'titre' => 'Gut zu wissen', 'note' => 'Haltbarkeit, Transport und besondere Bestellungen.', 'fond' => 'sable' ),
			$enfants( 'schiesser/info', array(
				array( 'icone' => 'conservation', 'titre' => 'Haltbarkeit', 'texte' => 'Kühl, trocken und lichtgeschützt lagern. Schokoladentafeln halten mehrere Monate, Pralinen zwei bis drei Wochen, Truffes wenige Tage.' ),
				array( 'icone' => 'avion', 'titre' => 'Auf Reisen', 'texte' => 'Läckerli, Tafeln und Geschenkboxen reisen sehr gut. Fragen Sie nach einer bruchsicheren Verpackung für Flug oder Zug.' ),
				array( 'icone' => 'gateau', 'titre' => 'Torten auf Bestellung', 'texte' => 'Truffes-Cake, Fruchttorte oder Rüblitorte für Ihren Anlass: rufen Sie uns an, wir beraten Sie gerne.' ),
				array( 'icone' => 'coffret', 'titre' => 'Firmengeschenke', 'texte' => 'Pralinen und Läckerli mit Ihrer Botschaft oder Ihrem Logo: siehe ' . $l( '/firmengeschenke/', 'Firmengeschenke' ) . '.' ),
				array( 'icone' => 'paiement', 'titre' => 'Bezahlung', 'texte' => 'Karte, kontaktlos und bar in Schweizer Franken. Euro werden im Laden angenommen.' ),
				array( 'icone' => 'horloge', 'titre' => 'Frisch aus dem Ofen', 'texte' => 'Gipfeli, Zopf und Patisserie kommen am Morgen in die Vitrine. Wer früh kommt, hat die grösste Auswahl.' ),
			) ) )
		. $b( 'schiesser/cartes', array( 'numero' => '04', 'titre' => 'So bestellen Sie', 'note' => 'Drei Wege zu Ihren Lieblingsstücken.', 'fond' => 'clair', 'ancre' => 'bestellen', 'modele' => 'commande' ),
			$enfants( 'schiesser/carte', array(
				array( 'titre' => 'Im Laden', 'texte' => 'Kommen Sie an den Marktplatz. Alles aus der Vitrine nehmen Sie sofort mit, ohne Vorbestellung.', 'boutonTexte' => 'Anfahrt', 'boutonLien' => $url_besuch ),
				array( 'titre' => 'Per Telefon', 'texte' => 'Für Torten, grosse Boxen oder Firmenbestellungen rufen Sie uns während der Öffnungszeiten an.', 'boutonTexte' => schiesser_reglage( 'telephone' ), 'boutonLien' => schiesser_lien_tel() ),
				array( 'titre' => 'Per E-Mail', 'texte' => 'Schicken Sie uns Ihre Wunschzusammenstellung, wir melden uns innert ein bis zwei Arbeitstagen.', 'boutonTexte' => 'Schreiben Sie uns', 'boutonLien' => $url_kontakt, 'sombre' => true ),
			) ) )
		. $b( 'schiesser/intro', array( 'numero' => '05', 'titre' => 'Von der Backstube an die Theke', 'lead' => 'Alles aus der Vitrine gibt es sofort: pro Stück, nach Gewicht oder in der Box.' ),
			schiesser_bm_p( 'Pralinen einzeln, pro 100 g oder in der 4er-, 9er- und 16er-Box; Basler Läckerli von 100 bis 500 g; Schokotafeln von 37 bis 83 % Kakao.' )
			. schiesser_bm_p( 'Zum Verschenken: die Bonbonnière von 280 bis 850 g, Marrons glacés, Marzipanfrüchte und die Läckerli-Box. Und oben im ' . $l( '/tea-room/', 'Tea Room' ) . ' gibt es zum Tee hausgemachte Pralinen.' ) )
		. $b( 'schiesser/faq', array( 'numero' => '06', 'titre' => 'Häufige Fragen zu unserer Confiserie', 'note' => 'Transport, Bestellung und Allergien.', 'fond' => 'clair' ),
			$q( 'Reisen die Produkte gut?', 'Unsere Geschenkboxen sind zum Mitnehmen gemacht. Fragen Sie nach einer bruchsicheren Verpackung, wenn Sie fliegen oder mit dem Zug reisen: Läckerli, Tafeln und Boxen überstehen die Reise sehr gut. Für Firmen gibt es ' . $l( '/firmengeschenke/', 'Geschenkboxen mit Ihrem Logo' ) . '.' )
			. $q( 'Liefern oder versenden Sie Bestellungen?', 'Bestellungen holen Sie im Laden am Marktplatz ab. Für besondere Wünsche ' . $l( '/kontakt/', 'schreiben Sie uns' ) . ', wir finden gemeinsam eine Lösung. ' . $l( '/besuch/', 'Öffnungszeiten und Anfahrt' ) . ' finden Sie hier.' )
			. $q( 'Enthalten Ihre Pralinen Alkohol?', 'Einige Spezialitäten ja, zum Beispiel die Liqueur-Stängeli. Fragen Sie an der Theke, wir zeigen Ihnen die Stücke ohne Alkohol. Probieren können Sie unsere Pralinen auch im ' . $l( '/tea-room/', 'Tea Room im ersten Stock' ) . ', zu jedem Kännchen Tee.' )
			. $q( 'Sind Ihre Produkte für Allergiker geeignet?', 'In unserer Backstube werden Nüsse, Milch, Soja, Gluten und Ei verarbeitet; Spuren lassen sich deshalb nicht ausschliessen. Die Zutaten jedes Produkts erfahren Sie an der Theke.' ) );

	/* ================= Tea Room ================= */
	$tearoom = schiesser_demo_hero( array(
		'hauteur'      => 'page',
		'filtre'       => 'sepia-leger',
		'surtitre'     => 'Erster Stock · Marktplatz',
		'titre'        => 'Tea Room in&nbsp;Basel, <br><em>über dem Marktplatz</em>',
		'texte'        => 'Durch den Laden, die Treppe hinauf, und der Lärm des Marktplatzes bleibt unten. Hier sitzen Sie im ältesten Kaffeehaus der Schweiz.',
		'bouton1Texte' => 'Karte ansehen (PDF)',
		'bouton1Lien'  => '#karte-pdf', // remplacé par le PDF de la carte (inc/carte-druck.php)
		'bouton2Texte' => 'Zur Karte',
		'bouton2Lien'  => '#karte',
	), $P['salon'], 'tea-room-basel', 'Der Tea Room der Confiserie Schiesser im ersten Stock am Marktplatz Basel' )
		. $live( array( 'Der Tea Room', 'Erster Stock', 'Zugang durch den Laden', '' ), array( 'Reservieren', '', 'Empfohlen ab sechs Personen', 'telephone' ) )
		. $b( 'schiesser/carte-salon', array( 'numero' => '01', 'titre' => 'Die Karte des Tea Room', 'note' => 'Kaffee, Tee, hausgemachte Schokolade, Frühstück, Hausgemachtes, Desserts und Drinks. Die ganze Karte gibt es auch als PDF.', 'fond' => 'alterne', 'ancre' => 'karte', 'mention' => 'Preise in CHF. Änderungen vorbehalten.', 'sSurtitre' => 'Empfehlung des Hauses', 'sPied' => 'Den ganzen Tag' ) )
		. $b( 'schiesser/intro', array( 'numero' => '02', 'titre' => 'Hinauf in den ersten Stock', 'note' => 'Ein paar Stufen trennen Laden und Tea Room. Sie machen den Unterschied.', 'ancre' => 'tea-room', 'suite' => true, 'lead' => 'Ein Ort, den man erst entdeckt, wenn man die Tür aufstösst und den Blick hebt.' ),
			schiesser_bm_p( 'Sie sitzen im ältesten Kaffeehaus der Schweiz, gegründet 1870. Tea Room und «Rathstübli» liegen im ersten Stock des Schiesser am Marktplatz, direkt gegenüber dem Rathaus.' )
			. schiesser_bm_p( 'Eingerichtet hat sie Hans Schiesser, der Sohn des Gründers. Seit über hundert Jahren debattieren und geniessen hier Baslerinnen und Basler wie Gäste aus aller Welt.' ) )
		. $b( 'schiesser/ascension', array( 'indication' => 'Scrollen Sie, um hinaufzusteigen' ),
			$enfants( 'schiesser/palier', array(
				array( 'niveau' => '0', 'libelle' => 'Laden', 'surtitre' => 'Erdgeschoss', 'titre' => 'Durch den Laden', 'texte' => 'Vitrinen, Theke und die Backstube gleich dahinter. Alle kommen hier vorbei, und das ist gewollt.' ) + $img( $P['boutique'], 'confiserie-marktplatz-basel', 'Der Laden im Erdgeschoss' ),
				array( 'niveau' => '½', 'libelle' => 'Treppe', 'surtitre' => 'Die Treppe', 'titre' => 'Die Treppe hinauf', 'texte' => 'Ein paar Stufen, ein von vielen Händen polierter Handlauf. Der Lärm des Platzes wird leiser.' ) + $img( $P['facade'], 'treppe-tea-room', 'Die Treppe zum Tea Room' ),
				array( 'niveau' => '1', 'libelle' => 'Tea Room', 'surtitre' => 'Erster Stock', 'titre' => 'Ankommen', 'texte' => 'Ein Tisch am Fenster, unten der Platz, gegenüber das Rathaus. Der Rest kann warten.' ) + $img( $P['salon'], 'tea-room-basel', 'Der Tea Room im ersten Stock' ),
			) ) )
		. $b( 'schiesser/encart', array( 'numero' => '03', 'titre' => 'Seit 1870', 'note' => 'Kaffeehaus, Tea Room und Confiserie unter einem Dach.', 'fond' => 'sombre', 'surtitre' => 'Eine Schweizer Besonderheit', 'encartTitre' => 'Das älteste Kaffeehaus der Schweiz', 'sousTitre' => 'Im ersten Stock, über der Confiserie', 'texte' => 'Kaffeehaus, Tea Room und Confiserie mit eigener Backstube unter einem Dach: eine Verbindung, die in der Schweiz wohl nirgends sonst so weitgehend im Original erhalten ist.', 'mention' => '' ) + $img( $P['a1889'], '', $alt1889 ),
			$enfants( 'schiesser/chiffre', array(
				array( 'valeur' => '1870', 'libelle' => 'Gründung am Marktplatz' ),
				array( 'valeur' => '1.', 'libelle' => 'Stock: Tea Room und Rathstübli' ),
				array( 'valeur' => '100+', 'libelle' => 'Jahre Kaffeehauskultur' ),
			) ) )
		. $b( 'schiesser/panneaux', array( 'numero' => '04', 'titre' => 'Der Tea Room im Detail', 'note' => 'Fahren Sie über die Bilder oder tippen Sie darauf.', 'indication' => 'Zum Öffnen klicken oder darüberfahren' ),
			$enfants( 'schiesser/panneau', array(
				array( 'titre' => 'Die Fenster', 'texte' => 'Sie gehen direkt auf den Marktplatz und das Rathaus. Die schönsten Tische stehen an ihnen.' ) + $img( $P['salon'], 'tea-room-basel', 'Die Fenster des Tea Room zum Marktplatz' ),
				array( 'titre' => 'Das Rathstübli', 'texte' => 'Ein Raum mit eigener Geschichte, eingerichtet von Hans Schiesser, dem Sohn des Gründers.' ) + $img( $P['tables'], 'rathstuebli-basel', 'Das Rathstübli im ersten Stock' ),
				array( 'titre' => 'Der Service', 'texte' => 'Tee kommt frisch aufgegossen im Kännchen, mit Zitrone, Honig und hausgemachten Pralinen.' ) + $img( $P['tabl'], 'tee-service-tea-room', 'Tee-Service im Tea Room' ),
				array( 'titre' => 'Die Verkaufstheke', 'texte' => 'Hier warten täglich frische Pâtisserie, Torten, Früchtewähen und feines Hausgebäck.' ) + $img( $P['theke'], '', $alt_theke ),
			) ) )
		. $sep
		. $b( 'schiesser/moments', array( 'numero' => '05', 'titre' => 'Wann kommen?', 'note' => 'Der Tea Room verändert sich im Lauf des Tages. Wählen Sie Ihren Moment.', 'fond' => 'sombre', 'marque' => true, 'conseilLibelle' => 'Unsere Empfehlung' ),
			$enfants( 'schiesser/moment', array(
				array( 'heure' => '07:30', 'libelle' => 'Der Morgen', 'titre' => 'Frühstück', 'sousTitre' => 'Wenn alles aus dem Ofen kommt', 'texte' => 'Unten werden die Marktstände aufgebaut, oben duftet es nach frischen Buttergipfeli.', 'conseil' => 'Das Rathaus-Frühstück, dazu ein Cüpli' ) + $img( $P['boutique'], 'confiserie-marktplatz-basel', 'Der Tea Room am frühen Morgen' ),
				array( 'heure' => '11:00', 'libelle' => 'Der Vormittag', 'titre' => 'Die ruhige Stunde', 'sousTitre' => 'Unser Lieblingsmoment', 'texte' => 'Zwischen Frühstück und Mittag werden Tische frei, und das Licht fällt durch die Fenster. Die beste Zeit zum Verweilen.', 'conseil' => 'Ein Kännchen Darjeeling mit Pralinen' ) + $img( $P['salon'], 'tea-room-basel', 'Der Tea Room am Vormittag' ),
				array( 'heure' => '14:00', 'libelle' => 'Der Nachmittag', 'titre' => 'Kaffee und Kuchen', 'sousTitre' => 'Der Tea Room ist voll', 'texte' => 'Die Stunde, in der der Tea Room am meisten ist, was er immer war: lebendig, fröhlich, mit Tabletts zwischen den Tischen.', 'conseil' => 'Die Trio-Degustation der hausgemachten Schokoladen' ) + $img( $P['cake'], 'kaffee-und-kuchen-basel', 'Kaffee und Kuchen im Tea Room' ),
				array( 'heure' => '16:30', 'libelle' => 'Der späte Nachmittag', 'titre' => 'Die letzte Tasse', 'sousTitre' => 'Bevor sich der Platz leert', 'texte' => 'Das Licht wird weicher über dem Marktplatz. Zeit für eine Coupe oder einen Schiesser Eiscafé.', 'conseil' => 'Der Schiesser Eiscafé nach altem Rezept' ) + $img( $P['tabl'], 'schokolade-handgemacht-basel', 'Der Tea Room am späten Nachmittag' ),
			) ) )
		. $b( 'schiesser/venir', array( 'numero' => '06', 'titre' => 'Vorbeikommen', 'note' => 'Der Tea Room ist frei zugänglich, ohne Reservation.', 'fond' => 'clair', 'ancre' => 'vorbeikommen', 'encartTitre' => 'Ein Tisch wartet auf Sie', 'texte' => 'Treten Sie in den Laden, gehen Sie bis zur Treppe und steigen Sie hinauf. Meist ist ein Platz am Fenster frei.', 'b1Texte' => 'Schreiben Sie uns', 'b1Lien' => $url_kontakt, 'b2Texte' => 'Anfahrt', 'b2Lien' => $url_besuch ) + $img( $P['salon'], 'tea-room-basel', 'Ein Tisch am Fenster im Tea Room' ),
			$ligne( 'Adresse', 'adresse', '', 'Erster Stock, Zugang durch den Laden' )
			. $ligne( 'Öffnungszeiten', 'horaires' )
			. $ligne( 'Reservation', '', 'Nicht nötig', 'Empfohlen ab sechs Personen oder für einen privaten Anlass' )
			. $ligne( 'Gruppen', '', 'Auf Anfrage', 'Degustationen und private Anlässe' ) )
		. $b( 'schiesser/faq', array( 'numero' => '07', 'titre' => 'Fragen zu unserem Tea Room in Basel', 'note' => 'Reservation, Gruppen und Karte.' ),
			$q( 'Muss man im Tea Room reservieren?', 'Nein, der Zugang ist frei. Ab sechs Personen empfehlen wir eine Reservation: ' . $l( '/kontakt/', 'rufen Sie uns an oder schreiben Sie uns' ) . '. ' . $l( '/besuch/', 'Öffnungszeiten und Anfahrt zum Marktplatz' ) . '.' )
			. $q( 'Kann man den Tea Room privat mieten?', 'Je nach Zeit und Saison ist das möglich. ' . $l( '/kontakt/', 'Kontaktieren Sie uns' ) . ' mit Datum, Anzahl Personen und Ihren Wünschen.' )
			. $q( 'Gibt es auch etwas Herzhaftes?', 'Ja: Tagessuppe und Tomatencreme mit Hausbrot, hausgemachte Quiches, Salatteller und Hauspasteten-Teller, dazu Schinkengipfel und Wurstwegge aus unserer ' . $l( '/confiserie/', 'Backstube am Marktplatz' ) . '.' )
			. $q( 'Kann man bei Ihnen frühstücken?', 'Ja: Marktplatz-, Schiesser- und Rathaus-Frühstück mit hausgemachten Buttergipfeln und frisch gepresstem Orangensaft, auf Wunsch mit einem Cüpli Prosecco oder Champagner. Seit wann hier gefrühstückt wird, erzählt unsere ' . $l( '/geschichte/', 'Geschichte seit 1870' ) . '.' ) );

	/* ================= Geschichte ================= */
	$geschichte = schiesser_demo_hero( array(
		'hauteur'     => 'page',
		'filtre'      => 'sepia',
		'progression' => true,
		'surtitre'    => 'Seit 1870 · Marktplatz',
		'titre'       => 'Seit 1870 <br><em>am Basler Marktplatz</em>',
		'texte'       => 'Die Geschichte einer Basler Confiserie, die ein Glarner Konditor gründete und die bis heute Kaffeehaus, Tea Room und Backstube unter einem Dach vereint.',
	), $P['a1900'], '', $alt1900 )
		. $b( 'schiesser/chiffres', array(),
			$enfants( 'schiesser/chiffre', array(
				array( 'valeur' => '1870', 'libelle' => 'Gründungsjahr' ),
				array( 'valeur' => '150+', 'libelle' => 'Jahre Handwerk' ),
				array( 'valeur' => '1', 'libelle' => 'Adresse am Marktplatz' ),
				array( 'valeur' => '2', 'libelle' => 'Etagen: Confiserie und Tea Room' ),
			) ) )
		. $b( 'schiesser/plaque', array( 'nombre' => '150', 'libelle' => 'Jahre Schiesser', 'dates' => '1870 · 2020', 'texte' => 'Anderthalb Jahrhunderte Handwerk, von Generation zu Generation weitergegeben.' ) )
		. $b( 'schiesser/origine', array( 'numero' => '01', 'titre' => 'Der Anfang', 'note' => 'Eine Geschichte, einfach erzählt. Der Rest lässt sich vor Ort geniessen.', 'legende' => 'Archiv · die Fassade um 1889', 'figure' => 'Abb. 01' ) + $img( $P['a1889'], '', $alt1889 ),
			schiesser_bm_p( 'Der Glarner Konditor Rudolf Schiesser legte 1870 den Grundstein: eine ' . $l( '/confiserie/', 'Confiserie am Basler Marktplatz' ) . ', direkt ' . $l( '/besuch/', 'gegenüber dem Rathaus' ) . '.' )
			. schiesser_bm_p( 'Schon früh stand auf der Fassade, wofür das Haus bekannt wurde: «Patisserie», «Leckerly», «Schiesser Confiseur». Gebacken wurde im Haus, verkauft an der Theke, der Markt lag direkt vor der Tür. Die ' . $l( '/confiserie/', 'Basler Läckerli' ) . ' gehören bis heute dazu.' )
			. $b( 'schiesser/exergue', array( 'texte' => 'Was wir Ihnen servieren, entsteht wie damals im Haus, zum Teil nach Rezepten aus den Gründerjahren.', 'auteur' => 'Confiserie Schiesser' ) )
			. schiesser_bm_p( 'Sein Sohn Hans gab dem Haus die neugotische Fassade nach dem Vorbild des Rathauses und richtete im ersten Stock den ' . $l( '/tea-room/', 'Tea Room' ) . ' und das «Rathstübli» ein. Seither debattieren und geniessen dort Baslerinnen und Basler wie Gäste aus aller Welt.' ) )
		. $b( 'schiesser/frise', array( 'numero' => '02', 'titre' => 'Die Chronik', 'note' => 'Wählen Sie ein Datum oder lassen Sie die Geschichte laufen.', 'ancre' => 'chronik', 'affichage' => 'onglets', 'indication' => 'Klicken Sie auf ein Datum', 'retenirLibelle' => 'Gut zu wissen' ),
			$enfants( 'schiesser/date', array(
				array( 'annee' => '1870', 'libelle' => 'Gründung', 'titre' => 'Die Gründung', 'texte' => 'Rudolf Schiesser, Konditor aus Glarus, eröffnet seine Confiserie am Basler Marktplatz.', 'retenir' => 'Das Haus steht bis heute an derselben Adresse, gegenüber dem Rathaus.' ) + $img( $P['a1889'], '', $alt1889 ),
				array( 'annee' => '1889', 'libelle' => 'Die Fassade', 'titre' => 'Patisserie und Leckerly', 'texte' => 'Die Aufnahme von 1889 zeigt die Fassade mit «Patisserie», «Leckerly» und «Schiesser Confiseur», in den Fenstern das Personal des Hauses.', 'retenir' => 'Läckerli gehören seit den Anfängen zum Haus.' ) + $img( $P['a1889'], '', $alt1889 ),
				array( 'annee' => '1900', 'libelle' => 'Der Markt', 'titre' => 'Mitten im Markt', 'texte' => 'Um 1900: vor dem Haus die Marktstände, dahinter die Confiserie.', 'retenir' => 'Der Markt findet bis heute direkt vor unseren Fenstern statt.' ) + $img( $P['a1900'], '', $alt1900 ),
				array( 'annee' => 'Nach 1900', 'libelle' => 'Zweite Generation', 'titre' => 'Tea Room und Rathstübli', 'texte' => 'Hans Schiesser gibt dem Haus die neugotische Fassade nach dem Vorbild des Rathauses und richtet im ersten Stock Tea Room und «Rathstübli» ein.', 'retenir' => 'Seit über hundert Jahren ein Treffpunkt für Basel.' ) + $img( $P['neubau'], '', $alt_neubau ),
				array( 'annee' => 'Heute', 'libelle' => 'Marktplatz', 'titre' => 'Immer noch am Marktplatz', 'texte' => 'Kaffeehaus, Tea Room und Confiserie mit eigener Backstube unter einem Dach, wohl nirgends sonst in der Schweiz so im Original erhalten.', 'retenir' => 'Überzeugen Sie sich selbst: ' . $l( '/besuch/', 'Öffnungszeiten und Anfahrt' ) . '.' ) + $img( $P['jour'], 'confiserie-schiesser-heute', 'Die Confiserie Schiesser heute' ),
			) ) )
		. $b( 'schiesser/archives', array( 'numero' => '03', 'titre' => 'Aus dem Archiv', 'note' => 'Aufnahmen aus der Geschichte des Hauses. Klicken Sie zum Vergrössern.', 'fond' => 'clair', 'credit' => 'Archivbilder: Confiserie Schiesser.' ),
			$enfants( 'schiesser/archive', array(
				array( 'titre' => 'Die Fassade', 'meta' => '1889 · Fotografie', 'description' => 'Die Fassade mit den Schriftzügen «Patisserie», «Leckerly» und «Schiesser Confiseur»; in den Fenstern das Personal des Hauses.' ) + $img( $P['a1889'], '', $alt1889 ),
				array( 'titre' => 'Der Marktplatz', 'meta' => 'Um 1900 · Fotografie', 'description' => 'Markttag vor dem Haus: Stände, Körbe und Karren, dahinter die Confiserie.' ) + $img( $P['a1900'], '', $alt1900 ),
				array( 'titre' => 'Das Schiesserhaus', 'meta' => 'Archiv · Fotografie', 'description' => 'Das neue Haus am Marktplatz mit Café, Tea-Room und Confiserie; davor der Markt.' ) + $img( $P['neubau'], '', $alt_neubau ),
				array( 'titre' => 'Das Schaufenster', 'meta' => 'Archiv · Fotografie', 'description' => '«Seit 1870 Schiesser»: Läckerli, Geschenkdosen und eine Lokomotive als Blickfang.' ) + $img( $P['schaufenster'], '', $alt_schaufenster ),
				array( 'titre' => 'Die Verkaufstheke', 'meta' => 'Archiv · Fotografie', 'description' => 'Pralinen unter Glas, die Waage auf der Theke, Dosen und Tafeln in den Regalen.' ) + $img( $P['theke'], '', $alt_theke ),
				array( 'titre' => 'Die Backstube', 'meta' => 'Archiv · Fotografie', 'description' => 'Drei Konditoren dekorieren Torten: Handarbeit, wie bis heute.' ) + $img( $P['torten'], '', $alt_torten ),
				array( 'titre' => 'Am Rührwerk', 'meta' => 'Archiv · Fotografie', 'description' => 'Ein junger Konditor an der Arbeit, neben den grossen Rührmaschinen der Backstube.' ) + $img( $P['backstube'], '', $alt_backstube ),
				array( 'titre' => 'Am Buffet', 'meta' => 'Archiv · Fotografie', 'description' => 'An der Kasse des Tea Room, zwischen Holztäfer und Kronleuchter.' ) + $img( $P['kasse'], '', $alt_kasse ),
			) ) )
		. $b( 'schiesser/avant-apres', array( 'numero' => '04', 'titre' => 'Gestern und heute', 'note' => 'Dasselbe Haus, im Abstand von über einem Jahrhundert. Ziehen Sie den Regler.', 'indication' => 'Zum Vergleichen ziehen' ),
			$enfants( 'schiesser/comparaison', array(
				array( 'onglet' => 'Die Fassade', 'avantLibelle' => '1889', 'apresLibelle' => 'Heute', 'vieillir' => false ) + $img( $P['a1889'], '', $alt1889, 'avant' ) + $img( $P['boutique'], 'confiserie-marktplatz-basel', 'Die Confiserie heute', 'apres' ),
				array( 'onglet' => 'Der Marktplatz', 'avantLibelle' => 'Damals', 'apresLibelle' => 'Heute', 'vieillir' => false ) + $img( $P['neubau'], '', $alt_neubau, 'avant' ) + $img( $P['jour'], 'confiserie-schiesser-heute', 'Der Marktplatz heute', 'apres' ),
			) ) )
		. $sep
		. $b( 'schiesser/cartes', array( 'numero' => '05', 'titre' => 'Was sich nicht geändert hat', 'note' => 'Drei Grundsätze, seit dem ersten Tag.', 'modele' => 'principes', 'fond' => 'papier' ),
			$enfants( 'schiesser/carte', array(
				array( 'titre' => 'Handarbeit', 'texte' => 'Schneiden, rollen, tunken, dekorieren: die entscheidenden Handgriffe bleiben Handarbeit.' ),
				array( 'titre' => 'Aus dem eigenen Haus', 'texte' => 'Was wir servieren, entsteht im Haus, zum Teil nach Rezepten aus den Gründerjahren.' ),
				array( 'titre' => 'Am selben Ort', 'texte' => 'Seit 1870 am Marktplatz, gegenüber dem Rathaus. Die Adresse ist geblieben.' ),
			) ) )
		. $b( 'schiesser/appel', array( 'surtitre' => 'Der Rest ist vor Ort', 'titre' => 'Schreiben Sie das nächste Kapitel mit uns', 'texte' => 'Am besten versteht man dieses Haus, wenn man die Tür am Marktplatz aufstösst.', 'b1Texte' => 'Besuchen Sie uns', 'b1Lien' => $url_besuch, 'b2Texte' => 'Das Handwerk entdecken', 'b2Lien' => $u( '/#handwerk' ) ) );

	/* ================= Besuch ================= */
	$trajet = function ( $attrs, $etapes ) {
		$html = '';
		foreach ( $etapes as $e ) {
			$html .= schiesser_bm( 'schiesser/etape', array_filter( array( 'icone' => $e[0], 'titre' => $e[1], 'texte' => $e[2], 'duree' => $e[3] ?? '' ) ) ) . "\n";
		}
		return schiesser_bm( 'schiesser/trajet', $attrs, $html ) . "\n";
	};
	$besuch = schiesser_demo_hero( array(
		'hauteur'  => 'compacte',
		'surtitre' => 'Marktplatz · Basel',
		'titre'    => 'Confiserie am Marktplatz, <br><em>mitten in Basel</em>',
		'texte'    => 'Direkt gegenüber dem Rathaus, wenige Schritte vom Tram. Hier finden Sie Öffnungszeiten und Anfahrt.',
	), $P['boutique'], 'confiserie-marktplatz-basel', 'Der Laden der Confiserie Schiesser am Marktplatz Basel' )
		. $live( array( 'Heute', '', '', 'aujourdhui' ), array( 'Kontakt', '', '', 'contact' ) )
		. $b( 'schiesser/plan-horaires', array( 'numero' => '01', 'titre' => 'So finden Sie uns', 'note' => 'Am Marktplatz, gegenüber dem Rathaus. Karte und Öffnungszeiten auf einen Blick.', 'ancre' => 'oeffnungszeiten', 'titreHoraires' => 'Öffnungszeiten', 'b1Texte' => 'Route', 'b2Texte' => 'Vergrössern' ) )
		. $b( 'schiesser/affluence', array( 'numero' => '02', 'titre' => 'Der beste Moment', 'note' => 'Übliche Auslastung im Laden. Wählen Sie einen Tag.', 'fond' => 'sable', 'conseil' => 'Am Morgen kommt alles frisch aus dem Ofen' ) )
		. $b( 'schiesser/trajets', array( 'numero' => '03', 'titre' => 'Ihr Weg zu uns', 'note' => 'Sagen Sie uns, woher Sie kommen: wir zeigen den Weg Schritt für Schritt.', 'fond' => 'sombre', 'marque' => true, 'ancre' => 'anfahrt', 'departLibelle' => 'Ich komme von', 'boutonTexte' => 'Route öffnen' ),
			$trajet( array( 'icone' => 'train', 'depart' => 'Bahnhof Basel SBB', 'detail' => 'Der Hauptbahnhof', 'titre' => 'Vom Bahnhof Basel SBB', 'sousTitre' => 'Der direkteste Weg in die Stadt.', 'duree' => '12', 'changements' => '0', 'marche' => '4', 'astuce' => 'Fahrpläne auf <a href="https://www.bvb.ch/">bvb.ch</a>. Viele Basler Hotels geben ihren Gästen ein Ticket für den öffentlichen Verkehr: fragen Sie an der Rezeption.' ), array(
				array( 'train', 'Basel SBB', 'Beim Ausgang Richtung Stadt liegen die Tramhaltestellen direkt vor dem Bahnhof.' ),
				array( 'tram', 'Tram Richtung Zentrum', 'Steigen Sie in ein Tram, das am Marktplatz hält. Die Anzeigen an der Haltestelle zeigen das Ziel.', '≈ 8 Min.' ),
				array( 'marche', 'Haltestelle Marktplatz', 'Aussteigen auf dem Platz. Unsere Vitrine liegt gegenüber dem Rathaus.', '≈ 1 Min.' ),
				array( 'boutique', 'Confiserie Schiesser', 'Sie sind da. Treten Sie ein, die Vitrine ist frisch bestückt.' ),
			) )
			. $trajet( array( 'icone' => 'avion', 'depart' => 'EuroAirport', 'detail' => 'Flughafen Basel-Mulhouse', 'titre' => 'Vom EuroAirport', 'sousTitre' => 'Mit Bus und Tram ins Zentrum.', 'duree' => '40', 'changements' => '1', 'marche' => '5', 'astuce' => 'Nehmen Sie den Schweizer Ausgang des Flughafens; der französische führt auf die andere Seite der Grenze.' ), array(
				array( 'avion', 'EuroAirport', 'Verlassen Sie das Terminal auf der Schweizer Seite und gehen Sie zu den Bushaltestellen.' ),
				array( 'bus', 'Bus ins Zentrum', 'Nehmen Sie den Bus zum Bahnhof Basel SBB.', '≈ 20 Min.' ),
				array( 'tram', 'Basel SBB, dann Marktplatz', 'Steigen Sie in ein Tram zum Marktplatz um.', '≈ 10 Min.' ),
				array( 'boutique', 'Confiserie Schiesser', 'Der Laden liegt direkt am Platz.' ),
			) )
			. $trajet( array( 'icone' => 'marche', 'depart' => 'Altstadt', 'detail' => 'Sie sind schon im Zentrum', 'titre' => 'Aus der Altstadt', 'sousTitre' => 'Wenige Gehminuten, alles Fussgängerzone.', 'duree' => '8', 'changements' => '0', 'marche' => '8', 'astuce' => 'Am Morgen werden vor dem Laden die Marktstände aufgebaut: die schönste Zeit zum Bummeln. Ideen für Spaziergänge auf <a href="https://www.basel.com/de">basel.com</a>.' ), array(
				array( 'marche', 'Altstadt', 'Aus den Gassen der Altstadt gehen Sie zur Freien Strasse.' ),
				array( 'marche', 'Freie Strasse', 'Folgen Sie der Einkaufsstrasse Richtung Marktplatz.', '≈ 6 Min.' ),
				array( 'boutique', 'Marktplatz', 'Die Strasse mündet auf den Platz. Wir sind gegenüber dem Rathaus.', '≈ 2 Min.' ),
			) )
			. $trajet( array( 'icone' => 'voiture', 'depart' => 'Mit dem Auto', 'detail' => 'Von der Autobahn', 'titre' => 'Mit dem Auto', 'sousTitre' => 'Der Marktplatz ist Fussgängerzone, parkiert wird in der Nähe.', 'duree' => '20', 'changements' => '0', 'marche' => '6', 'astuce' => 'Rund um den Platz ist der Verkehr eingeschränkt. Wenn möglich ist das Tram einfacher und schneller.' ), array(
				array( 'voiture', 'Autobahnausfahrt', 'Folgen Sie der Richtung Basel Zentrum.' ),
				array( 'voiture', 'Parkhaus im Zentrum', 'Parkieren Sie in einem der Parkhäuser nahe der Innenstadt.', '≈ 15 Min.' ),
				array( 'marche', 'Zu Fuss zum Platz', 'Durch die Fussgängerzone zum Marktplatz.', '≈ 6 Min.' ),
				array( 'boutique', 'Confiserie Schiesser', 'Sie stehen direkt vor dem Laden.' ),
			) ) )
		. $b( 'schiesser/infos', array( 'numero' => '04', 'titre' => 'Gut zu wissen', 'note' => 'Praktisches vor Ihrem Besuch.', 'fond' => 'sable' ),
			$enfants( 'schiesser/info', array(
				array( 'icone' => 'paiement', 'titre' => 'Bezahlung', 'texte' => 'Karte, kontaktlos und bar in Schweizer Franken. Euro werden im Laden angenommen.' ),
				array( 'icone' => 'langues', 'titre' => 'Sprachen', 'texte' => 'Wir empfangen Sie auf Deutsch, Französisch und Englisch.' ),
				array( 'icone' => 'accessibilite', 'titre' => 'Barrierefreiheit', 'texte' => 'Der Laden ist vom Platz aus ebenerdig. Der Tea Room liegt im ersten Stock und ist über eine Treppe erreichbar. Sagen Sie uns Bescheid, wir helfen gerne.' ),
				array( 'icone' => 'emporter', 'titre' => 'Zum Mitnehmen', 'texte' => 'Alle Produkte gibt es zum Mitnehmen, auf Wunsch reisefest verpackt.' ),
				array( 'icone' => 'coffret', 'titre' => 'Geschenkboxen', 'texte' => 'Pralinen, Läckerli und Bonbonnière, von Hand zusammengestellt und schön verpackt.' ),
				array( 'icone' => 'repere', 'titre' => 'Fussgängerzone', 'texte' => 'Der Marktplatz ist Fussgängerzone: zu Fuss aus den Nachbarstrassen oder mit dem Tram.' ),
			) ) )
		. $sep
		. $b( 'schiesser/devanture', array( 'numero' => '05', 'titre' => 'Das Haus erkennen', 'note' => 'Danach halten Sie Ausschau, wenn Sie auf den Platz kommen.', 'fond' => 'clair', 'surtitre' => 'Marktplatz · gegenüber dem Rathaus', 'encartTitre' => 'Die Fassade', 'texte' => 'Die neugotische Fassade nach dem Vorbild des Rathauses, die Vitrinen direkt am Platz. Der Eingang führt durch den Laden, die Treppe zum Tea Room liegt hinten.' ) + $img( $P['facade'], 'fassade-confiserie-schiesser', 'Die Fassade der Confiserie Schiesser am Marktplatz Basel' ) )
		. $b( 'schiesser/faq', array( 'numero' => '06', 'titre' => 'Praktische Fragen vor Ihrem Besuch', 'note' => 'Das Wichtigste vor Ihrem Besuch.' ),
			$q( 'Gibt es Parkplätze in der Nähe?', 'Der Marktplatz ist Fussgängerzone. Die Parkhäuser der Innenstadt liegen wenige Gehminuten entfernt. Fragen vor Ihrem Besuch? ' . $l( '/kontakt/', 'Kontaktieren Sie uns' ) . '.' )
			. $q( 'Kann man mit Euro bezahlen?', 'Ja, Euro werden im Laden angenommen. Ausserdem Karten, kontaktlos und bar in Schweizer Franken.' )
			. $q( 'Wo kann man vor Ort etwas geniessen?', 'Im ' . $l( '/tea-room/', 'Tea Room' ) . ' im ersten Stock, während der Öffnungszeiten der Confiserie: Kaffee, Tee, hausgemachte Schokolade, Frühstück und Desserts.' )
			. $q( 'Reisen die Produkte gut?', 'Unsere Geschenkboxen sind zum Mitnehmen gemacht. Fragen Sie nach einer bruchsicheren Verpackung, wenn Sie fliegen oder mit dem Zug reisen. Alle Spezialitäten finden Sie in unserer ' . $l( '/confiserie/', 'Confiserie in Basel' ) . '.' )
			. $q( 'Was gibt es rund um den Marktplatz zu sehen?', 'Das Rathaus liegt direkt gegenüber, die Altstadt beginnt gleich nebenan. Ideen für Spaziergänge auf <a href="https://www.basel.com/de">basel.com</a>.' ) );

	/* ================= Kontakt ================= */
	$kontakt = schiesser_demo_hero( array(
		'hauteur'  => 'compacte',
		'surtitre' => 'Eine Frage, eine Bestellung, ein Anlass',
		'titre'    => 'Kontakt zur <br><em>Confiserie Schiesser</em>',
		'texte'    => 'Eine Nachricht, ein Anruf oder ein Besuch am Marktplatz: wir beantworten jede Anfrage.',
	), $P['boutique'], 'confiserie-marktplatz-basel', 'Die Theke der Confiserie Schiesser' )
		. $live( array( 'Per Telefon', '', 'Während der Öffnungszeiten', 'telephone' ), array( 'Per E-Mail', '', 'Antwort innert 1 bis 2 Arbeitstagen', 'email' ) )
		. $b( 'schiesser/formulaire', array( 'numero' => '01', 'titre' => 'Schreiben Sie uns', 'note' => 'Eine kurze Nachricht genügt, wir antworten innert ein bis zwei Arbeitstagen.', 'ancre' => 'schreiben',
			'lNom' => 'Name', 'lEmail' => 'E-Mail', 'lTel' => 'Telefon', 'lMessage' => 'Ihre Nachricht', 'boutonTexte' => 'Senden', 'mentionLegale' => 'Ihre Angaben verwenden wir nur, um Ihre Anfrage zu bearbeiten.', 'okTitre' => 'Ihre Nachricht ist unterwegs', 'okTexte' => 'Vielen Dank. Wir antworten innert ein bis zwei Arbeitstagen.' ),
			$ligne( 'Per Telefon', 'telephone', '', 'Während der Öffnungszeiten, am schnellsten bei dringenden Fragen.' )
			. $ligne( 'Per E-Mail', 'email', '', 'Antwort innert ein bis zwei Arbeitstagen.' )
			. $ligne( 'Im Laden', 'adresse-ligne', '', 'Am einfachsten: kommen Sie vorbei.' ) )
		. $b( 'schiesser/fiche-contact', array( 'numero' => '02', 'titre' => 'Erreichen Sie uns', 'note' => 'Eine Adresse, ein Team, für Confiserie und Tea Room.', 'fond' => 'sable', 'surtitre' => 'Marktplatz · Basel', 'encartTitre' => 'Confiserie Schiesser', 'texte' => 'Im Erdgeschoss die Confiserie, im ersten Stock der Tea Room. Für eine Bestellung, eine Frage oder eine Gruppenreservation: schreiben Sie uns oder rufen Sie an, unser Team antwortet Ihnen.' ),
			$ligne( 'E-Mail', 'email' )
			. $ligne( 'Telefon', 'telephone' )
			. $ligne( 'Adresse', 'adresse-ligne' )
			. $ligne( 'Für', '', 'Bestellungen, Torten, Geschenkboxen, Gruppen, Presse' ) )
		. $b( 'schiesser/faq', array( 'numero' => '03', 'titre' => 'Bevor Sie uns schreiben', 'note' => 'Vielleicht steht die Antwort schon hier.', 'fond' => 'clair' ),
			$q( 'Wie schnell antworten Sie?', 'Per E-Mail innert ein bis zwei Arbeitstagen. Bei dringenden Anliegen rufen Sie uns am besten während der Öffnungszeiten an.' )
			. $q( 'Wie früh sollte man eine Torte bestellen?', 'Für die meisten Torten genügen wenige Tage, für grosse Bestellungen oder an Festtagen etwas mehr. Rufen Sie uns an, wir beraten Sie gerne. Unser Sortiment an Torten, Läckerli und Pralinen finden Sie in der ' . $l( '/confiserie/', 'Confiserie' ) . '.' )
			. $q( 'Kann man im Tea Room einen Tisch reservieren?', 'Der Tea Room ist frei zugänglich. Ab sechs Personen oder für einen privaten Anlass empfehlen wir eine Reservation. Mehr dazu auf der Seite ' . $l( '/tea-room/', 'Tea Room' ) . '.' )
			. $q( 'Liefern oder versenden Sie Bestellungen?', 'Bestellungen holen Sie im Laden am Marktplatz ab. Für besondere Wünsche schreiben Sie uns, wir finden gemeinsam eine Lösung.' )
			. $q( 'Bieten Sie Firmengeschenke an?', 'Ja, wir stellen Pralinen- und Läckerli-Boxen für Ihre Kundschaft oder Ihr Team zusammen, mit Ihrer Botschaft oder Ihrem Logo. Siehe ' . $l( '/firmengeschenke/', 'Firmengeschenke' ) . '.' )
			. $q( 'Gibt es einen Onlineshop?', 'Er folgt in Kürze. Bis dahin bestellen Sie telefonisch oder per E-Mail und holen alles im Laden am Marktplatz ab: ' . $l( '/besuch/', 'Öffnungszeiten und Anfahrt' ) . '.' ) )
		. $b( 'schiesser/appel', array( 'surtitre' => 'Am einfachsten ist es immer noch', 'titre' => 'Kommen Sie an den Marktplatz', 'texte' => 'Wir sind vom Morgen bis am Abend für Sie da, die Vitrine ist jeden Tag frisch bestückt und oben wartet der Tea Room.', 'b1Texte' => 'So finden Sie uns', 'b1Lien' => $url_besuch, 'b2Texte' => 'Rufen Sie uns an', 'b2Lien' => schiesser_lien_tel() ) );

	/* ================= Firmengeschenke ================= */
	$firmen = schiesser_demo_hero( array(
		'hauteur'      => 'compacte',
		'surtitre'     => 'Für Firmen · Kundschaft und Team',
		'titre'        => 'Firmengeschenke <br><em>aus Basel, von Hand gemacht</em>',
		'texte'        => 'Danken Sie Ihrer Kundschaft und Ihrem Team mit Pralinen und Läckerli aus unserer Backstube am Marktplatz, seit 1870.',
		'bouton1Texte' => 'Offerte anfragen',
		'bouton1Lien'  => $devis,
		'bouton2Texte' => 'Anrufen',
		'bouton2Lien'  => schiesser_lien_tel(),
	), $P['coff'], 'firmengeschenke-basel-pralinen', 'Pralinenboxen als Firmengeschenke aus Basel' )
		. $live( array( 'Offerte', 'Innert 1 bis 2 Arbeitstagen', 'Per E-Mail oder Telefon', '' ), array( 'Rufen Sie uns an', '', 'Während der Öffnungszeiten', 'telephone' ) )
		. $b( 'schiesser/cartes', array( 'numero' => '01', 'titre' => 'Unsere Geschenke für Firmen', 'note' => 'Firmengeschenke aus Basel, die gut reisen und lange halten.', 'modele' => 'commande', 'fond' => 'papier' ),
			$enfants( 'schiesser/carte', array(
				array( 'titre' => 'Pralinen-Box', 'texte' => '4er-, 9er- oder 16er-Box mit Pralinen aus eigener Produktion, schön verpackt. Auch als Basler Pralinen.', 'boutonTexte' => 'Zum Sortiment', 'boutonLien' => $url_confiserie . '#sortiment' ),
				array( 'titre' => 'Basler Läckerli', 'texte' => 'Die Spezialität der Stadt, in der Läckerli-Box oder zu 200 und 500 g: ein Geschenk, das reist und mehrere Wochen hält.', 'boutonTexte' => 'Zum Sortiment', 'boutonLien' => $url_confiserie . '#sortiment' ),
				array( 'titre' => 'Bonbonnière', 'texte' => 'Das grosse Geschenk: 280, 500 oder 850 g feinste Confiserie.', 'boutonTexte' => 'Offerte anfragen', 'boutonLien' => $devis, 'sombre' => true ),
			) ) )
		. $b( 'schiesser/intro', array( 'numero' => '02', 'titre' => 'Ihre Botschaft, Ihr Logo', 'fond' => 'clair', 'lead' => 'Jede Box kann Ihre Botschaft oder Ihr Logo tragen: eine persönliche Aufmerksamkeit, von Hand zusammengestellt.' ),
			schiesser_bm_p( 'Sagen Sie uns, was darauf stehen soll: wir schlagen Ihnen die passende Präsentation für Ihre Menge und Ihr Datum vor.' )
			. schiesser_bm_p( 'Mit einem Geschenk von Schiesser verschenken Sie auch ein Stück ' . $l( '/geschichte/', 'Basler Geschichte' ) . ': die einer Confiserie, die seit 1870 am Marktplatz steht.' ) )
		. $b( 'schiesser/cartes', array( 'numero' => '03', 'titre' => 'So läuft es ab', 'note' => 'Drei Schritte, ohne kompliziertes Formular.', 'modele' => 'principes', 'fond' => 'papier' ),
			$enfants( 'schiesser/carte', array(
				array( 'titre' => 'Sie schreiben uns', 'texte' => 'Menge, Format und gewünschtes Übergabedatum, per E-Mail oder Telefon.' ),
				array( 'titre' => 'Wir antworten', 'texte' => 'Innert ein bis zwei Arbeitstagen, mit einem Vorschlag und einem Preis.' ),
				array( 'titre' => 'Wir bereiten vor', 'texte' => 'Ihre Geschenke werden kurz vor dem Übergabedatum zusammengestellt und am Marktplatz abgeholt.' ),
			) ) )
		. $b( 'schiesser/infos', array( 'numero' => '04', 'titre' => 'Für eine schnelle Offerte', 'note' => 'Nennen Sie uns einfach diese Angaben.', 'fond' => 'sable' ),
			$enfants( 'schiesser/info', array(
				array( 'icone' => 'coffret', 'titre' => 'Menge und Format', 'texte' => 'Anzahl Geschenke und gewünschtes Format: Pralinen-Box, Läckerli-Box oder Bonbonnière.' ),
				array( 'icone' => 'horloge', 'titre' => 'Übergabedatum', 'texte' => 'Für die Festtage am Jahresende schreiben Sie uns am besten einige Wochen im Voraus.' ),
				array( 'icone' => 'langues', 'titre' => 'Botschaft oder Logo', 'texte' => 'Der Text oder das Logo, das auf den Geschenken stehen soll.' ),
				array( 'icone' => 'paiement', 'titre' => 'Rechnung', 'texte' => 'Die Rechnungsadresse und eine Kontaktperson.' ),
			) ) )
		. $b( 'schiesser/faq', array( 'numero' => '05', 'titre' => 'Fragen zu Firmengeschenken', 'note' => 'Degustation, Fristen, Transport.' ),
			$q( 'Kann man vor der Bestellung probieren?', 'Ja: im ' . $l( '/tea-room/', 'Tea Room' ) . ' gibt es zum Tee hausgemachte Pralinen, und im Laden stellen Sie gerne eine kleine Box zusammen.' )
			. $q( 'Wie früh sollte man bestellen?', '' . $l( '/kontakt/', 'Schreiben Sie uns' ) . ', sobald das Datum feststeht. Für kleine Mengen genügen wenige Tage; für die Festtage besser einige Wochen im Voraus.' )
			. $q( 'Reisen die Geschenke gut?', 'Ja, sie sind zum Mitnehmen gemacht, und auf Wunsch verpacken wir sie besonders sicher. Basler Läckerli halten mehrere Wochen: mehr dazu in der ' . $l( '/confiserie/', 'Confiserie' ) . '.' )
			. $q( 'Liefern Sie?', 'Bestellungen holen Sie im Laden am Marktplatz ab. Für besondere Wünsche schreiben Sie uns, wir finden gemeinsam eine Lösung.' ) )
		. $b( 'schiesser/appel', array( 'surtitre' => 'Sprechen wir über Ihre Geschenke', 'titre' => 'Eine Offerte innert ein bis zwei Arbeitstagen', 'texte' => 'Nennen Sie uns Menge und Datum: wir antworten mit einem Vorschlag und einem Preis.', 'b1Texte' => 'Offerte anfragen', 'b1Lien' => $devis, 'b2Texte' => 'Rufen Sie uns an', 'b2Lien' => schiesser_lien_tel() ) );

	/* ================= Impressum (Entwurf) ================= */
	$impressum = schiesser_bm_section(
		array( 'entete' => false, 'largeur' => 'lecture' ),
		schiesser_bm_p( 'Vor der Veröffentlichung ergänzen und dann publizieren: die Seite erscheint automatisch in der Fusszeile.', 'chapeau' )
		. schiesser_bm_titre( 'Betreiberin der Website', 2 )
		. schiesser_bm_p( 'Firma, Rechtsform, Adresse, UID-Nummer (CHE-…), verantwortliche Person, Kontakt.' )
		. schiesser_bm_titre( 'Hosting', 2 )
		. schiesser_bm_p( 'Name und Adresse des Hosting-Anbieters.' )
		. schiesser_bm_titre( 'Bildnachweis', 2 )
		. schiesser_bm_p( 'Archivbilder: Confiserie Schiesser. Weitere Fotos: zu ergänzen.' )
	);

	return array(
		array( 'titre' => 'Startseite', 'slug' => 'startseite', 'anciens' => array( 'accueil' ), 'contenu' => $startseite,
			'seo' => array( 'titre' => 'Confiserie in Basel | Tea Room seit 1870 – Schiesser', 'description' => 'Confiserie in Basel seit 1870: Läckerli, Pralinen und Patisserie aus der eigenen Backstube, Tea Room im ersten Stock. Am Marktplatz, gegenüber dem Rathaus.', 'mots_cles' => array( 'Confiserie Basel', 'Tea Room Basel', 'Confiserie Schiesser', 'Kaffeehaus Basel', 'Basler Läckerli' ) ) ),
		array( 'titre' => 'Confiserie', 'slug' => 'confiserie', 'anciens' => array( 'boutique' ), 'contenu' => $confiserie,
			'seo' => array( 'titre' => 'Läckerli & Pralinen Basel | Confiserie Schiesser', 'description' => 'Basler Läckerli, Pralinen, Torten und frisches Gebäck aus der eigenen Backstube am Marktplatz: das Sortiment der Confiserie Schiesser in Basel.', 'mots_cles' => array( 'Läckerli Basel', 'Pralinen Basel', 'Basler Läckerli kaufen', 'Confiserie Marktplatz Basel', 'Torten Basel' ) ) ),
		array( 'titre' => 'Tea Room', 'slug' => 'tea-room', 'anciens' => array( 'salon-de-the' ), 'contenu' => $tearoom,
			'seo' => array( 'titre' => 'Tea Room Basel | Ältestes Kaffeehaus der Schweiz – Schiesser', 'description' => 'Tea Room in Basel im ältesten Kaffeehaus der Schweiz: Kaffee, Tee, hausgemachte Schokolade, Frühstück und Coupes im ersten Stock am Marktplatz.', 'mots_cles' => array( 'Tea Room Basel', 'Kaffeehaus Basel', 'Frühstück Basel Marktplatz', 'ältestes Kaffeehaus der Schweiz', 'Café Marktplatz Basel' ) ) ),
		array( 'titre' => 'Geschichte', 'slug' => 'geschichte', 'anciens' => array( 'notre-histoire' ), 'contenu' => $geschichte,
			'seo' => array( 'titre' => 'Geschichte seit 1870 | Basler Confiserie – Schiesser', 'description' => 'Seit 1870 am Basler Marktplatz: wie der Glarner Konditor Rudolf Schiesser die Confiserie gründete und sein Sohn Hans Tea Room und Rathstübli einrichtete.', 'mots_cles' => array( 'Basler Confiserie seit 1870', 'Confiserie Schiesser Geschichte', 'Rudolf Schiesser', 'Rathstübli Basel', 'Geschichte Marktplatz Basel' ) ) ),
		array( 'titre' => 'Besuch', 'slug' => 'besuch', 'anciens' => array( 'nous-visiter' ), 'contenu' => $besuch,
			'seo' => array( 'titre' => 'Öffnungszeiten & Anfahrt | Confiserie Marktplatz Basel', 'description' => 'Öffnungszeiten, Adresse und Anfahrt der Confiserie Schiesser am Marktplatz Basel, gegenüber dem Rathaus und wenige Schritte vom Tram.', 'mots_cles' => array( 'Confiserie Marktplatz Basel', 'Öffnungszeiten Confiserie Schiesser', 'Confiserie Schiesser Adresse', 'Anfahrt Marktplatz Basel', 'Confiserie beim Rathaus Basel' ) ) ),
		array( 'titre' => 'Kontakt', 'slug' => 'kontakt', 'anciens' => array( 'contact' ), 'contenu' => $kontakt,
			'seo' => array( 'titre' => 'Kontakt & Bestellung | Confiserie Schiesser Basel', 'description' => 'Kontakt zur Confiserie Schiesser in Basel: Torten und Geschenkboxen bestellen, Gruppen anmelden oder eine Frage stellen. Schreiben Sie uns oder rufen Sie an.', 'mots_cles' => array( 'Confiserie Schiesser Kontakt', 'Torte bestellen Basel', 'Pralinen bestellen Basel', 'Tea Room Basel reservieren', 'Geschenkbox Basel' ) ) ),
		array( 'titre' => 'Firmengeschenke', 'slug' => 'firmengeschenke', 'anciens' => array( 'cadeaux-entreprise' ), 'contenu' => $firmen,
			'seo' => array( 'titre' => 'Firmengeschenke Basel | Pralinen & Läckerli – Schiesser', 'description' => 'Firmengeschenke aus Basel: Pralinen, Läckerli und Bonbonnières aus der eigenen Backstube, mit Ihrer Botschaft oder Ihrem Logo. Schnelle Offerte.', 'mots_cles' => array( 'Firmengeschenke Basel', 'Kundengeschenke Basel', 'Pralinen Firmengeschenk', 'Läckerli Geschenkbox', 'Weihnachtsgeschenke Firmen Basel' ) ) ),
		array( 'titre' => 'Impressum', 'slug' => 'impressum', 'anciens' => array( 'mentions-legales' ), 'contenu' => $impressum, 'statut' => 'draft',
			'seo' => array( 'titre' => 'Impressum | Confiserie Schiesser', 'description' => 'Impressum der Website der Confiserie Schiesser, Confiserie und Tea Room am Marktplatz Basel: Betreiberin, Hosting und Bildnachweis.', 'mots_cles' => array( 'Impressum' ) ) ),
	);
}

<?php
/**
 * Liste de prix de la boutique (« Preisliste ») et carte du Tea Room (« Getränke Karte »),
 * telles que fournies par la maison, en allemand.
 *
 * - Boutique : les formats d'un même produit (Läckerli 100 g, 200 g, 500 g…) sont réunis dans
 *   une seule fiche, avec le détail des prix dans la fiche. Pas encore de photos : la liste reste
 *   masquée sur le site (« Produktliste folgt in Kürze ») jusqu'au clic sur « Afficher la liste ».
 * - Tea Room : rubriques et plats avec prix et descriptions de la carte imprimée.
 *
 * Import : inc/demo.php (bouton de l'administration). Les produits ajoutés à la main ne sont
 * jamais effacés.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Produits de la boutique, par rubrique.
 * [ nom, formats [ [ libellé du format, prix ], … ], accroche ]
 * Un seul format sans libellé : prix unique.
 */
function schiesser_preisliste() {
	return array(
		'Läckerli & Basler Spezialitäten' => array(
			array( 'Basler Läckerli', array( array( '100 g', '8.90' ), array( '200 g', '17.50' ), array( '500 g', '39.50' ) ), 'Die Basler Spezialität' ),
			array( 'Läckerli-Box', array( array( '150 g', '14.50' ), array( '150 g, Plexi-Box', '12.50' ) ), 'Zum Verschenken' ),
			array( 'Brunsli-Stäb', array( array( '', '4.50' ) ), '' ),
			array( 'Änisstäb', array( array( '', '4.50' ) ), '' ),
			array( 'Wäppli', array( array( '4er', '18.50' ) ), '' ),
			array( 'Fastenwähe', array( array( '', '4.20' ) ), 'In der Fasnachtszeit' ),
		),
		'Pralinen & Confiserie' => array(
			array( 'Pralinen', array( array( 'pro 100 g', '17.—' ), array( '4er-Box', '14.50' ), array( '9er-Box', '23.50' ), array( '16er-Box', '38.50' ) ), 'Aus der eigenen Produktion' ),
			array( 'Basler Pralinen', array( array( '9er-Box', '22.50' ), array( '9er, Plexi-Box', '19.80' ) ), '' ),
			array( 'Bonbonnière', array( array( '280 g', '56.50' ), array( '500 g', '94.50' ), array( '850 g', '155.50' ) ), 'Das grosse Geschenk' ),
			array( 'Liqueur-Stängeli', array( array( 'pro 100 g', '17.—' ) ), '' ),
			array( 'Griots', array( array( 'Stück', '2.30' ) ), '' ),
			array( 'Marrons glacés', array( array( 'Stück', '3.90' ), array( '6er', '24.40' ) ), '' ),
			array( 'Marzipanfrüchte', array( array( 'Stück', '5.80' ), array( '4er', '23.20' ) ), '' ),
			array( 'Marzipanrolle', array( array( '', '12.50' ) ), '' ),
			array( 'Orangenschnitz', array( array( 'pro 100 g', '15.—' ), array( '200 g', '29.—' ), array( '300 g', '44.—' ) ), '' ),
			array( 'Florentiner', array( array( 'pro 100 g', '14.50' ) ), '' ),
			array( 'Konfekt', array( array( 'pro 100 g', '14.50' ), array( '200 g', '26.—' ), array( '300 g', '38.—' ) ), '' ),
			array( 'Bettmümpfeli', array( array( 'pro 100 g', '11.50' ) ), '' ),
			array( 'Schokomandel', array( array( '200 g', '18.50' ) ), '' ),
		),
		'Schokolade' => array(
			array( 'Schokotafel', array( array( '37 %', '9.50' ), array( '38 %', '11.50' ), array( '42 %', '11.50' ), array( '64 %', '13.50' ), array( '70 %', '13.50' ), array( '83 %', '15.50' ) ), 'Von mild bis intensiv' ),
			array( 'Rathaustafel Milch', array( array( '', '14.50' ) ), '' ),
			array( 'Non Pareil', array( array( 'dunkel, milch, weiss oder ruby, 100 g', '11.90' ), array( 'dunkel, milch, weiss oder ruby, 200 g', '19.90' ) ), 'In vier Sorten' ),
			array( 'Sackmesser', array( array( 'Stück', '4.50' ), array( '5er', '19.90' ), array( 'Dose', '28.50' ) ), '' ),
			array( 'Zoo Tierli', array( array( '150 g', '13.50' ) ), '' ),
			array( 'Schoggi-S', array( array( '', '3.90' ) ), '' ),
			array( 'Branchli', array( array( '', '4.50' ) ), '' ),
			array( 'Noiseta', array( array( '175 ml', '13.50' ) ), '' ),
			array( 'Schokopulver', array( array( '160 g', '17.90' ) ), '' ),
		),
		'Torten & Patisserie' => array(
			array( 'Truffes-Cake', array( array( 'klein', '19.90' ), array( 'gross', '28.90' ) ), '' ),
			array( 'Schwarzwälder Tortenstück', array( array( '', '7.60' ) ), '' ),
			array( 'Truffesschnitte', array( array( '', '5.60' ) ), '' ),
			array( 'Truffes-Makröndli', array( array( '', '4.50' ) ), '' ),
			array( 'Vermicelltörtli', array( array( '', '5.60' ) ), '' ),
			array( 'Zitronenroulade', array( array( '', '5.60' ) ), '' ),
			array( 'Cremeschnitte', array( array( '', '5.60' ) ), '' ),
			array( 'Holländerli', array( array( '', '5.50' ) ), '' ),
			array( 'Japonais', array( array( '', '5.60' ) ), '' ),
			array( 'Linzerli', array( array( '', '5.50' ) ), '' ),
			array( 'Nusstortli', array( array( '', '6.50' ) ), '' ),
			array( 'Punschkugel', array( array( '', '5.60' ) ), '' ),
			array( 'Schoggiflach', array( array( '', '5.60' ) ), '' ),
			array( 'Fruchttorte', array( array( 'Stück', '7.60' ) ), '' ),
			array( 'Rüblitorte', array( array( 'Stück', '7.60' ) ), '' ),
			array( 'Fruchtwähe', array( array( '', '6.90' ) ), '' ),
			array( 'Apfelstrudel', array( array( '', '10.20' ) ), '' ),
		),
		'Aus der Backstube' => array(
			array( 'Zopf', array( array( 'klein', '6.50' ), array( 'gross', '9.80' ) ), '' ),
			array( 'Buttergipfeli', array( array( '', '2.90' ) ), '' ),
			array( 'Brioche', array( array( 'Brötli', '2.90' ), array( 'klein', '6.50' ), array( 'gross', '9.50' ) ), '' ),
			array( 'Pain au Chocolat', array( array( '', '4.60' ) ), '' ),
			array( 'Schoggiweggli', array( array( '', '4.20' ) ), '' ),
			array( 'Zuckerweggli', array( array( '', '3.90' ) ), '' ),
			array( 'Birnenwegge', array( array( 'klein', '5.50' ), array( 'gross', '7.90' ) ), '' ),
			array( 'Silserli mit Butter', array( array( '', '4.50' ) ), '' ),
		),
		'Salziges & Apéro' => array(
			array( 'Silserli belegt', array( array( 'mit Schinken, Thon, Ei oder Käse', '5.20' ) ), '' ),
			array( 'Belegte Brötli', array( array( 'Schinken, Spargel, Sellerie oder Ei', '6.50' ), array( 'Thon', '7.50' ), array( 'Tatar oder Lachs', '8.50' ) ), '' ),
			array( 'Schinkengipfeli', array( array( '', '4.80' ) ), '' ),
			array( 'Wurstwegge', array( array( '', '4.80' ) ), '' ),
			array( 'Hauspastetli', array( array( '', '6.80' ) ), '' ),
			array( 'Pastetli', array( array( '2er', '5.80' ) ), '' ),
			array( 'Speckkugel', array( array( '', '6.80' ) ), '' ),
			array( 'Salzwähe', array( array( '', '7.80' ) ), '' ),
			array( 'Birchermüsli', array( array( '', '14.20' ) ), 'Hausgemacht' ),
			array( 'Apéro-Gebäck', array( array( 'pro 100 g', '15.—' ) ), '' ),
			array( 'Sunneredli', array( array( 'Salz oder Kümmel', '12.80' ) ), '' ),
			array( 'Baslerstäbli', array( array( 'Mohn oder Käse', '12.80' ) ), '' ),
			array( 'Salz-Mandeln', array( array( '100 g', '9.90' ), array( '200 g', '17.80' ) ), '' ),
		),
		'Guetzli & Gebäck' => array(
			array( 'Hüppen', array( array( 'Zitrone oder Schokolade', '12.50' ) ), '' ),
			array( 'Sablé', array( array( 'Schokolade oder Vanille', '12.50' ) ), '' ),
			array( 'Ziegeli', array( array( '', '11.50' ) ), '' ),
		),
		'Glace' => array(
			array( 'Glace', array( array( 'klein', '6.50' ), array( 'gross', '18.—' ) ), 'Zum Mitnehmen' ),
		),
	);
}

/** Prix au format de la maison : « 8.90 » → « CHF 8.90 », « 17.— » → « CHF 17.— ». */
function schiesser_chf( $prix ) {
	return 'CHF ' . $prix;
}

/**
 * Carte du Tea Room.
 * Rubrique => [ photo (identifiant de démonstration), plats [ nom, prix, description, mention ] ].
 */
function schiesser_getraenkekarte() {
	return array(
		array( 'nom' => 'Kaffee', 'photo' => 'tables', 'alt' => 'Kaffee im Tea Room der Confiserie Schiesser', 'plats' => array(
			array( 'Kaffee', 'CHF 5.90', '' ),
			array( 'Espresso', 'CHF 5.90', '' ),
			array( 'Doppio', 'CHF 7.90', '' ),
			array( 'Ristretto', 'CHF 5.90', '' ),
			array( 'Espresso Macchiato', 'CHF 6.50', '' ),
			array( 'Milchkaffee', 'CHF 6.50', '' ),
			array( 'Cappuccino', 'CHF 7.20', '' ),
			array( 'Flat White', 'CHF 7.90', '' ),
			array( 'Caffè freddo', 'CHF 6.50', '' ),
			array( 'Kaffee Mélange', 'CHF 7.50', '' ),
			array( 'Latte Macchiato', 'CHF 7.90', '' ),
			array( 'Irish Coffee', 'CHF 14.50', 'Mit 2 cl Jameson Whiskey 40°, Schlagrahm.' ),
			array( 'Kaffee Lutz', 'CHF 12.50', 'Mit 2 cl Zwetschgen 37°.' ),
			array( 'Kaffee Fertig', 'CHF 12.50', 'Mit 2 cl Kernobst 37.5°.' ),
			array( 'Extras zum Kaffee', 'ab CHF 1.—', 'Laktosefreie Milch 1.00 · Hafermilch 1.50 · Shot Espresso 2.50 · Schokoladentopping 1.50 · Caramelsirup 1.50 · 2 cl Baileys 17° 4.50 · 2 cl Amaretto 28° 4.50 · 2 cl Rum 37.5° 6.50' ),
		) ),
		array( 'nom' => 'Tee', 'photo' => 'salon', 'alt' => 'Tee im Kännchen, serviert im Tea Room', 'plats' => array(
			array( 'Portion Tee im Kännchen', 'CHF 12.20', 'Frisch aufgegossen, serviert mit Zitrone, Honig und hausgemachten Pralinen. Zur Auswahl: Earl Grey (der Klassische mit Bergamotte), Eisenkraut (zitroniger Geschmack), Darjeeling (der Champagner unter den Tees), Sencha (japanischer Grüntee), Nana-Minze aus Ägypten, Früchte-Beeren (Apfel, Orange, Hibiskus, Hagebutte, Schlehdorn), Ginger Lemongrass.' ),
			array( 'Jasmin Silver Needle Yin Zhen', 'CHF 15.20', 'Weisser Tee mit allerfeinsten Blattknospen und frisch gepflückten Jasminblüten. Herkunft Yunnan, China.', 'Rarität' ),
			array( 'Benifuki Black', 'CHF 15.60', 'Eine sehr seltene Spezialität: tiefaromatischer schwarzer Langblatttee mit hohem Anteil an vulkanischen Mineralien. Herkunft Kagoshima, Japan.', 'Rarität' ),
			array( 'Kabusecha Matcha-Iri', 'CHF 16.20', 'Blend aus halbbeschattetem Kabusecha und einer Prise Matcha. Herkunft Kyoto, Japan.', 'Rarität' ),
			array( 'Tasse Tee', 'CHF 6.20', 'Frisch aufgegossen. Earl Grey, Darjeeling, Sencha, Minze, Früchte-Beeren oder Ginger Lemongrass.' ),
			array( 'Chai Latte', 'CHF 8.50', 'Schwarzer Tee mit Zimt, Ingwer, Kardamom und Nelken, aufgegossen mit heisser Milch und Milchschaum.' ),
		) ),
		array( 'nom' => 'Heisse Schokolade', 'photo' => 'truf', 'alt' => 'Hausgemachte heisse Schokolade', 'plats' => array(
			array( 'Dunkle Schokolade Grand Cru 65 %', 'CHF 8.20', 'Black Maracaibo, Herkunft Madagaskar. Aus eigener Produktion, nach altem Rezept.', 'Hausgemacht' ),
			array( 'Milchschokolade Grand Cru 42 %', 'CHF 8.20', 'Milk Seriz, Herkunft Ecuador.', 'Hausgemacht' ),
			array( 'Weisse Schokolade Grand Cru 37 %', 'CHF 8.20', 'White Nuit blanche, Herkunft Bolivien.', 'Hausgemacht' ),
			array( 'Trio-Degustation', 'CHF 14.80', 'Drei handgefertigte heisse Schokoladen: intensive Dunkelschokolade, cremige Milchschokolade und zartsüsse weisse Schokolade.', 'Empfehlung' ),
			array( 'Extras zur Schokolade', 'ab CHF 1.50', 'Schlagrahm 1.50 · Honig 2.00 · Shot Espresso 2.50 · 2 cl Baileys 17° 5.00 · 2 cl Amaretto 28° 5.00' ),
		) ),
		array( 'nom' => 'Frühstück', 'photo' => 'boutique', 'alt' => 'Frühstück mit hausgemachten Buttergipfeli', 'plats' => array(
			array( 'Marktplatz-Frühstück', 'CHF 14.80', 'Zwei hausgemachte Buttergipfel, Butter, Honig, Konfitüre und 2 dl frisch gepresster Orangensaft.' ),
			array( 'Schiesser-Frühstück', 'CHF 18.50', 'Zwei hausgemachte Buttergipfel, frisches Brot, 2 Butter, Honig, 2 Konfitüren und 2 dl frisch gepresster Orangensaft.' ),
			array( 'Rathaus-Frühstück', 'CHF 29.50', 'Zwei hausgemachte Buttergipfel, frisches Brot, 2 Butter, Honig, 2 Konfitüren, Rührei mit gebratenem Speck und 2 dl frisch gepresster Orangensaft.', 'Empfehlung' ),
			array( 'Cüpli zum Frühstück', 'CHF 8.90', 'Prosecco CHF 8.90 oder Champagner CHF 15.50: ein Privileg für unsere Frühstücksgäste.' ),
			array( 'Hausgemachtes Birchermüesli', 'CHF 14.20', 'Mit frischen Früchten, Joghurt, Honig und Granola.' ),
			array( 'Extras zum Frühstück', 'ab CHF 6.50', 'Rührei 2 Eier 8.20, 4 Eier 15.90 · Gebratener Speck 50 g 6.50 · Schinken 50 g 6.50. Unsere Eier sowie Fleisch- und Wurstwaren kommen ausschliesslich aus Schweizer Produktion.' ),
		) ),
		array( 'nom' => 'Suppen & Hausgemachtes', 'photo' => 'cake', 'alt' => 'Hausgemachte Quiche im Tea Room', 'plats' => array(
			array( 'Tomatencreme', 'CHF 12.50', 'Mit frischem Hausbrot.' ),
			array( 'Tagessuppe', 'CHF 12.50', 'Mit frischem Hausbrot.', 'Täglich' ),
			array( 'Schinkengipfel oder Wurstweggen', 'CHF 4.80', 'Auf Wunsch mit gemischtem Salat CHF 14.50.' ),
			array( 'Quiche Lorraine', 'CHF 7.80', 'Auf Wunsch mit gemischtem Salat CHF 18.90.' ),
			array( 'Quiche mit Ziegenkäse und Spinat', 'CHF 7.80', 'Auf Wunsch mit gemischtem Salat CHF 18.90.' ),
			array( 'Quiche mit Gemüse und Kräutern', 'CHF 7.80', 'Auf Wunsch mit gemischtem Salat CHF 18.90.' ),
			array( 'Quiche mit Kürbis und Lauch', 'CHF 7.80', 'Auf Wunsch mit gemischtem Salat CHF 18.90.' ),
			array( 'Gemischter Salatteller', 'CHF 17.50', 'Bunter Salat mit gekochtem Ei.' ),
			array( 'Hauspasteten-Teller', 'CHF 23.50', 'Mit Cumberlandsauce und Salat.' ),
		) ),
		array( 'nom' => 'Dessert & Glace', 'photo' => 'marr', 'alt' => 'Coupe und hausgemachte Glace im Tea Room', 'plats' => array(
			array( 'Original Schiesser Läckerli-Brownie', 'CHF 12.80', 'Hausgemachter Basler Läckerli-Brownie: traditionelles Schiesser Läckerli und feinste Grand-Cru-Edelschokolade.', 'Spezialität' ),
			array( 'Hausgemachte Schokoladenmousse', 'CHF 11.80', 'Aus unserer Grand-Cru-Couverture 65 %.' ),
			array( 'Hausgemachter Apfelstrudel', 'CHF 10.20', 'Mit lauwarmer Vanillesauce. Kugel Vanilleglace + 4.50, Portion Schlagrahm + 1.00.' ),
			array( 'Hausgemachte Glace', 'CHF 5.50', 'Pro Kugel: Vanille, Erdbeere, Himbeere, Chocolat, Stracciatella, Pistache, Mocca, Mango, Banane, Citro, Heidelbeere. Schlagrahm + 1.50.' ),
			array( 'Frappé', 'CHF 9.80', 'Aroma nach Wahl.' ),
			array( 'Affogato', 'CHF 10.50', 'Eine Kugel Vanilleglace mit heissem Espresso. Mit Amaretto CHF 13.50.' ),
			array( 'Schiesser Eiscafé', 'CHF 14.80', 'Unsere Moccaglace stellen wir nach einem alten Rezept her: seit 1870 ohne Unterbruch.', 'Seit 1870' ),
			array( 'Café Negus', 'CHF 11.80', 'Eine Kugel Moccaglace, feinster Whiskylikör und Schlagrahm; den heissen Espresso giessen Sie selbst darüber.' ),
			array( 'Coupe Dänemark', 'CHF 15.50', 'Vanilleglace mit hausgemachter Schokoladensauce, Schlagrahm und Mandelsplittern.' ),
			array( 'Coupe Banana', 'CHF 15.50', 'Hausgemachte Bananen- und Vanilleglace mit frischer Banane, Schokoladensauce und Schlagrahm.' ),
			array( 'Coupe Framboise', 'CHF 15.80', 'Himbeer- und Vanilleglace, frische Himbeeren und Schlagrahm.' ),
			array( 'Portion Vermicelles', 'CHF 13.80', 'Marronipüree, Meringue und Schlagrahm.' ),
			array( 'Coupe Nesselrode', 'CHF 15.80', 'Vanilleglace, Vermicelles, Meringue, Schlagrahm und Mandelsplitter.' ),
		) ),
		array( 'nom' => 'Kalte Getränke', 'photo' => 'jour', 'alt' => 'Kalte Getränke und hausgemachter Eistee', 'plats' => array(
			array( 'Hausgemachter Eistee', 'CHF 6.20', 'Eine erfrischende Mischung aus Hibiskus, Mango und Maracuja.', 'Hausgemacht' ),
			array( 'Mineralwasser Rhäzünser', 'CHF 6.20', '33 cl, mit Kohlensäure.' ),
			array( 'Mineralwasser Arkina', 'CHF 6.20', '33 cl, ohne Kohlensäure.' ),
			array( 'Apfelschorle', 'CHF 6.20', '33 cl.' ),
			array( 'Rivella rot oder blau', 'CHF 6.20', '33 cl.' ),
			array( 'Coca-Cola oder Coca-Cola Zero', 'CHF 6.20', '33 cl.' ),
			array( 'Alpinesse Tonic oder Bitter Lemon', 'CHF 6.20', '20 cl.' ),
			array( 'Sanbitter', 'CHF 6.20', '10 cl.' ),
			array( 'Orangensaft frisch gepresst', 'CHF 7.80', '20 cl.' ),
			array( 'Citron pressé', 'CHF 7.80', '20 cl, kalt oder warm.' ),
			array( 'Feldschlösschen Hopfenperle', 'CHF 6.80', '33 cl.' ),
			array( 'Feldschlösschen alkoholfrei', 'CHF 6.80', '33 cl. Auch als Panaché alkoholfrei.' ),
		) ),
		array( 'nom' => 'Wein, Cüpli & Drinks', 'photo' => 'relais', 'alt' => 'Cüpli und Aperitif im Tea Room', 'plats' => array(
			array( 'Chardonnay IGT', 'CHF 8.50', 'Corte Giara Allegrini, Italien, Veneto. 1 dl 8.50 · 2 dl 17.00 · Flasche 59.50.' ),
			array( 'Primitivo Salento IGP', 'CHF 9.50', 'Voro, Italien, Masseria Pietrosa. 1 dl 9.50 · 2 dl 19.00 · Flasche 69.50.' ),
			array( 'Gespritzter Weisswein', 'CHF 8.50', '2 dl, sauer (Mineralwasser) oder süss (Citro).' ),
			array( 'Waggis', 'CHF 8.50', '2 dl, Weisswein und Tonic.', 'Basler Klassiker' ),
			array( 'Cüpli Prosecco DOC', 'CHF 12.—', '1 dl. Le Calle, Extra Dry, Italien.' ),
			array( 'Cüpli Champagner AOC', 'CHF 18.50', '1 dl. Mailly, Grand Cru Brut Réserve, Frankreich.' ),
			array( 'Hugo, Aperol, Limoncello oder Campari Spritz', 'CHF 14.50', '' ),
			array( 'Campari Orange', 'CHF 15.50', '' ),
			array( 'Hendrick’s Gin Tonic', 'CHF 18.50', '' ),
			array( 'Tito’s Vodka Lemon oder Orange', 'CHF 18.50', '' ),
			array( 'Whisky, Gin, Vodka, Rum', 'ab CHF 13.50', '4 cl. Dalwhinnie 15 Years 43° 14.50 · Hendrick’s 41.4° 14.50 · Tito’s 40° 13.50 · Flor de Caña 12 Years 37.5° 13.50.' ),
			array( 'Liköre, Cognac, Grappa, Eau de vie', 'ab CHF 8.50', 'Baileys 17° oder Amaretto 28°, 4 cl 8.50 · Cognac Bisquit 42°, 2 cl 15.50 · Grappa Brunello 41°, 2 cl 13.50 · Cru Réserva 41°, 2 cl 15.50 · Baselbieter Kirsch 42°, 2 cl 9.50 · Poire Williams 43°, 2 cl 12.50 · Apricotine 40°, 2 cl 12.50.' ),
		) ),
	);
}

/** Suggestion du jour proposée à l'import : la spécialité de la maison. */
function schiesser_getraenkekarte_suggestion() {
	return 'Original Schiesser Läckerli-Brownie';
}

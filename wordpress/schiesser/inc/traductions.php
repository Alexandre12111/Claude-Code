<?php
/**
 * Traductions des textes du thème : allemand (clé) => [ français, anglais ].
 *
 * Utilisées par schiesser_t() (inc/i18n.php) quand Polylang affiche une page en français ou en anglais.
 * Le contenu des pages, des produits et de la carte a, lui, ses propres traductions (inc/contenu-traductions.php),
 * créées par l'import et modifiables page par page dans WordPress.
 */

defined( 'ABSPATH' ) || exit;

/** Textes du thème (PHP et scripts). */
function schiesser_traductions_base() {
	return array(
		/* ----- état de la maison, horaires ----- */
		'Geöffnet'                                   => array( 'Ouvert', 'Open' ),
		'Geschlossen'                                => array( 'Fermé', 'Closed' ),
		'geschlossen'                                => array( 'fermé', 'closed' ),
		'Heute geschlossen'                          => array( 'Fermé aujourd’hui', 'Closed today' ),
		'Geöffnet bis %s'                            => array( 'Ouvert jusqu’à %s', 'Open until %s' ),
		'Geschlossen · öffnet um %s'                 => array( 'Fermé · ouvre à %s', 'Closed · opens at %s' ),
		'Geschlossen · öffnet %1$s um %2$s'          => array( 'Fermé · ouvre %1$s à %2$s', 'Closed · opens %1$s at %2$s' ),
		'geöffnet von %1$s bis %2$s'                 => array( 'ouvert de %1$s à %2$s', 'open from %1$s to %2$s' ),
		'am %s'                                      => array( '%s', 'on %s' ),
		'morgen'                                     => array( 'demain', 'tomorrow' ),
		'Morgen'                                     => array( 'Demain', 'Tomorrow' ),
		'heute'                                      => array( 'aujourd’hui', 'today' ),
		'Heute'                                      => array( 'Aujourd’hui', 'Today' ),
		'Schliesst in'                               => array( 'Ferme dans', 'Closes in' ),
		'Öffnet in'                                  => array( 'Ouvre dans', 'Opens in' ),
		'Std.'                                       => array( 'h', 'h' ),
		'Min.'                                       => array( 'min', 'min' ),
		'Öffnungszeiten'                             => array( 'Horaires', 'Opening hours' ),
		'Öffnungszeiten heute'                       => array( 'Horaires du jour', 'Today’s opening hours' ),
		'Öffnungszeiten & Anfahrt'                   => array( 'Horaires & accès', 'Opening hours & directions' ),
		'Besondere Öffnungszeiten'                   => array( 'Horaires particuliers', 'Special opening hours' ),
		'Ausnahmsweise geschlossen'                  => array( 'Exceptionnellement fermé', 'Exceptionally closed' ),
		'Wir sind jetzt für Sie da:'                 => array( 'Nous sommes là pour vous :', 'We are here for you now:' ),
		'Ab morgen wieder da.'                       => array( 'De retour dès demain.', 'Back tomorrow.' ),
		'Ab morgen wieder da: Rufen Sie an und reservieren Sie.' => array( 'De retour dès demain : appelez-nous pour réserver.', 'Back tomorrow: call us to reserve.' ),

		/* ----- affluence ----- */
		'Am belebtesten'                             => array( 'Le plus animé', 'Busiest' ),
		'Am ruhigsten'                               => array( 'Le plus calme', 'Quietest' ),
		'Sehr belebt'                                => array( 'Très animé', 'Very busy' ),
		'Belebt'                                     => array( 'Animé', 'Busy' ),
		'Ruhig'                                      => array( 'Calme', 'Quiet' ),
		'Übliche Besucherzahl Stunde für Stunde'     => array( 'Affluence habituelle heure par heure', 'Usual visitor numbers hour by hour' ),
		'Am Morgen ist alles ofenfrisch'             => array( 'Le matin, tout sort du four', 'In the morning everything is fresh from the oven' ),

		/* ----- navigation, en-tête, pied ----- */
		'Startseite'                                 => array( 'Accueil', 'Home' ),
		'Zur Startseite'                             => array( 'Retour à l’accueil', 'Back to the home page' ),
		'Hauptmenü'                                  => array( 'Menu principal', 'Main menu' ),
		'Menü'                                       => array( 'Menu', 'Menu' ),
		'Schnellzugriff'                             => array( 'Accès rapide', 'Quick access' ),
		'Zum Inhalt springen'                        => array( 'Aller au contenu', 'Skip to content' ),
		'Brotkrümelnavigation'                       => array( 'Fil d’Ariane', 'Breadcrumb' ),
		'Auf dieser Seite'                           => array( 'Sur cette page', 'On this page' ),
		'Sprache'                                    => array( 'Langue', 'Language' ),
		'Adresse & Kontakt'                          => array( 'Adresse & contact', 'Address & contact' ),
		'Datenschutz'                                => array( 'Confidentialité', 'Privacy' ),
		'Seit'                                       => array( 'Depuis', 'Since' ),
		'Seit 1870'                                  => array( 'Depuis 1870', 'Since 1870' ),
		'CONFISERIE · TEA-ROOM · MARKTPLATZ BASEL · SEIT 1870 ·' => array( 'CONFISERIE · SALON DE THÉ · MARKTPLATZ BÂLE · DEPUIS 1870 ·', 'CONFECTIONERY · TEA ROOM · MARKTPLATZ BASEL · SINCE 1870 ·' ),
		'Seit 1870 · Marktplatz Basel'               => array( 'Depuis 1870 · Marktplatz de Bâle', 'Since 1870 · Marktplatz Basel' ),
		'Confiserie'                                 => array( 'Confiserie', 'Confectionery' ),
		'Onlineshop'                                 => array( 'Boutique en ligne', 'Online shop' ),
		'Hinweise'                                   => array( 'Informations', 'Notices' ),
		'Hinweis ausblenden'                         => array( 'Masquer l’information', 'Hide notice' ),
		'Weiter im Haus'                             => array( 'À découvrir aussi', 'More from the house' ),
		'und mehr'                                   => array( 'et plus encore', 'and more' ),
		'Weiterlesen'                                => array( 'Lire la suite', 'Read more' ),
		'Seite %d'                                   => array( 'Page %d', 'Page %d' ),
		'Seite nicht gefunden'                       => array( 'Page introuvable', 'Page not found' ),
		'Diese Tür führt ins Leere'                  => array( 'Cette porte ne mène nulle part', 'This door leads nowhere' ),
		'Diese Seite gibt es nicht oder nicht mehr. Das Haus selbst hat seine Adresse nie gewechselt: am Marktplatz in Basel.' => array( 'Cette page n’existe pas ou plus. La maison, elle, n’a jamais changé d’adresse : sur le Marktplatz de Bâle.', 'This page does not exist or no longer exists. The house itself has never moved: it is still on the Marktplatz in Basel.' ),
		'Suchergebnisse für «%s»'                    => array( 'Résultats pour « %s »', 'Search results for “%s”' ),
		'Suche: %s'                                  => array( 'Recherche : %s', 'Search: %s' ),
		'Keine Treffer. Versuchen Sie ein anderes Wort oder gehen Sie zur' => array( 'Aucun résultat. Essayez un autre mot ou revenez à l’', 'No results. Try another word or go to the' ),

		/* ----- maillage interne ----- */
		'Erdgeschoss'                                => array( 'Rez-de-chaussée', 'Ground floor' ),
		'Confiserie in Basel'                        => array( 'Confiserie à Bâle', 'Confectionery in Basel' ),
		'Basler Läckerli, Pralinen, Torten und Gebäck aus der eigenen Backstube.' => array( 'Läckerli de Bâle, pralinés, gâteaux et viennoiseries de notre propre atelier.', 'Basel Läckerli, pralines, cakes and pastries from our own bakehouse.' ),
		'Erster Stock'                               => array( 'Premier étage', 'First floor' ),
		'Tea Room am Marktplatz'                     => array( 'Salon de thé sur le Marktplatz', 'Tea Room on the Marktplatz' ),
		'Frühstück, Kaffee, Tee und hausgemachte Schokolade im ältesten Kaffeehaus der Schweiz.' => array( 'Petit-déjeuner, café, thé et chocolat maison dans le plus ancien café de Suisse.', 'Breakfast, coffee, tea and homemade hot chocolate in Switzerland’s oldest coffee house.' ),
		'Geschichte des Hauses'                      => array( 'Histoire de la maison', 'History of the house' ),
		'Vom Glarner Konditor Rudolf Schiesser bis heute, mit Bildern aus dem Archiv.' => array( 'Du confiseur glaronais Rudolf Schiesser à aujourd’hui, avec des images d’archives.', 'From the Glarus confectioner Rudolf Schiesser to the present day, with pictures from the archive.' ),
		'Marktplatz 19'                              => array( 'Marktplatz 19', 'Marktplatz 19' ),
		'Gegenüber dem Rathaus, wenige Schritte vom Tram: so finden Sie uns.' => array( 'Face à l’Hôtel de Ville, à quelques pas du tram : comment nous trouver.', 'Opposite the Town Hall, a few steps from the tram: how to find us.' ),
		'Für Firmen'                                 => array( 'Pour les entreprises', 'For companies' ),
		'Firmengeschenke aus Basel'                  => array( 'Cadeaux d’entreprise de Bâle', 'Corporate gifts from Basel' ),
		'Pralinen- und Läckerli-Boxen für Ihre Kundschaft und Ihr Team.' => array( 'Boîtes de pralinés et de Läckerli pour vos clients et votre équipe.', 'Praline and Läckerli boxes for your clients and your team.' ),
		'Bestellen'                                  => array( 'Commander', 'Order' ),
		'Kontakt & Bestellung'                       => array( 'Contact & commande', 'Contact & orders' ),
		'Torten, Geschenkboxen und Reservationen: wir beraten Sie gerne.' => array( 'Gâteaux, coffrets cadeaux et réservations : nous vous conseillons volontiers.', 'Cakes, gift boxes and reservations: we are happy to help.' ),

		/* ----- commande, Merkliste ----- */
		'Anrufen'                                    => array( 'Appeler', 'Call' ),
		'Anrufen:'                                   => array( 'Appeler :', 'Call:' ),
		'Anrufen (%s)'                               => array( 'Appeler (%s)', 'Call (%s)' ),
		'Telefonisch bestellen'                      => array( 'Commander par téléphone', 'Order by phone' ),
		'Per Nachricht senden'                       => array( 'Envoyer par message', 'Send as a message' ),
		'Merken'                                     => array( 'Garder', 'Save' ),
		'Auf meine Merkliste'                        => array( 'Ajouter à ma sélection', 'Add to my list' ),
		'Auf der Merkliste'                          => array( 'Dans ma sélection', 'On my list' ),
		'Merkliste'                                  => array( 'Sélection', 'My list' ),
		'Meine Merkliste'                            => array( 'Ma sélection', 'My list' ),
		'Ihre Liste, um telefonisch oder per Nachricht zu bestellen. Sie bleibt in diesem Browser gespeichert.' => array( 'Votre liste pour commander par téléphone ou par message. Elle reste enregistrée dans ce navigateur.', 'Your list for ordering by phone or by message. It stays saved in this browser.' ),
		'Ihre Merkliste ist leer.'                   => array( 'Votre sélection est vide.', 'Your list is empty.' ),
		'Liste kopieren'                             => array( 'Copier la liste', 'Copy list' ),
		'Liste kopieren:'                            => array( 'Copier la liste :', 'Copy list:' ),
		'Liste kopiert'                              => array( 'Liste copiée', 'List copied' ),
		'Liste leeren'                               => array( 'Vider la liste', 'Clear list' ),
		'Merkliste leeren?'                          => array( 'Vider la sélection ?', 'Clear the list?' ),
		'Eins weniger:'                              => array( 'Un de moins :', 'One less:' ),
		'Eins mehr:'                                 => array( 'Un de plus :', 'One more:' ),
		'Entfernen:'                                 => array( 'Retirer :', 'Remove:' ),
		'Bestellung:'                                => array( 'Commande :', 'Order:' ),
		'Menge oder Format:'                         => array( 'Quantité ou format :', 'Quantity or size:' ),
		'Name und Telefon:'                          => array( 'Nom et téléphone :', 'Name and phone:' ),
		'Gewünschtes Abholdatum:'                    => array( 'Date de retrait souhaitée :', 'Preferred pick-up date:' ),
		'Meine Telefonnummer:'                       => array( 'Mon numéro de téléphone :', 'My phone number:' ),
		'Abholung im Laden,'                         => array( 'Retrait en boutique,', 'Pick-up in the shop,' ),
		'Grüezi'                                     => array( 'Bonjour', 'Hello' ),
		'Ich möchte gerne bestellen:'                => array( 'Je souhaite commander :', 'I would like to order:' ),
		'Vielen Dank!'                               => array( 'Merci beaucoup !', 'Thank you very much!' ),
		'Vielen Dank und bis bald'                   => array( 'Merci et à bientôt', 'Thank you and see you soon' ),
		'In den Warenkorb'                           => array( 'Ajouter au panier', 'Add to basket' ),

		/* ----- partage ----- */
		'Teilen'                                     => array( 'Partager', 'Share' ),
		'Weiterempfehlen'                            => array( 'Recommander', 'Recommend' ),
		'Link kopieren'                              => array( 'Copier le lien', 'Copy link' ),
		'Link kopiert'                               => array( 'Lien copié', 'Link copied' ),

		/* ----- boutique, produits ----- */
		'Produkt'                                    => array( 'produit', 'product' ),
		'Produkte'                                   => array( 'produits', 'products' ),
		'Preis'                                      => array( 'Prix', 'Price' ),
		'Alle'                                       => array( 'Tous', 'All' ),
		'Entdecken'                                  => array( 'Découvrir', 'Discover' ),
		'Details ansehen'                            => array( 'Voir le détail', 'View details' ),
		'Nr.'                                        => array( 'N°', 'No.' ),
		'Produkt suchen'                             => array( 'Rechercher un produit', 'Search for a product' ),
		'Suchen: Praliné, Kirsch, Läckerli…'         => array( 'Rechercher : praliné, kirsch, Läckerli…', 'Search: praline, kirsch, Läckerli…' ),
		'Nach Kategorie filtern'                     => array( 'Filtrer par catégorie', 'Filter by category' ),
		'Kein Produkt passt zu dieser Suche. Versuchen Sie ein anderes Wort oder rufen Sie uns an: Wir machen auch vieles auf Bestellung.' => array( 'Aucun produit ne correspond à cette recherche. Essayez un autre mot ou appelez-nous : nous réalisons aussi beaucoup de choses sur commande.', 'No product matches this search. Try another word or give us a call: we also make many things to order.' ),
		'Kein Produkt passt zu dieser Auswahl. Produkte ohne Allergenangaben werden beim Filtern ausgeblendet.' => array( 'Aucun produit ne correspond à cette sélection. Les produits sans information sur les allergènes sont masqués par le filtre.', 'No product matches this selection. Products without allergen information are hidden when filtering.' ),
		'Produktliste folgt in Kürze'                => array( 'La liste des produits arrive bientôt', 'Product list coming soon' ),
		'Die vollständige Produktliste mit Fotos und Preisen ist bald online. Bis dahin finden Sie alles an der Theke am Marktplatz, oder Sie bestellen bequem per Telefon.' => array( 'La liste complète des produits, avec photos et prix, sera bientôt en ligne. D’ici là, vous trouvez tout au comptoir du Marktplatz, ou vous commandez simplement par téléphone.', 'The full product list with photos and prices will soon be online. Until then you will find everything at our counter on the Marktplatz, or you can simply order by phone.' ),
		'Alle Spezialitäten finden Sie schon heute an der Theke am Marktplatz.' => array( 'Toutes nos spécialités vous attendent dès aujourd’hui au comptoir du Marktplatz.', 'All our specialities are already waiting for you at the counter on the Marktplatz.' ),
		'Weitere Spezialitäten'                      => array( 'Autres spécialités', 'More specialities' ),
		'Ganzes Sortiment ansehen'                   => array( 'Voir tout l’assortiment', 'See the full range' ),
		'Zum ganzen Sortiment'                       => array( 'Tout l’assortiment', 'The full range' ),
		'Zur Produktseite'                           => array( 'Voir la fiche du produit', 'Go to the product page' ),
		'Heute ausverkauft'                          => array( 'Épuisé aujourd’hui', 'Sold out today' ),
		'Heute ausverkauft.'                         => array( 'Épuisé aujourd’hui.', 'Sold out today.' ),
		'Heute in der Vitrine'                       => array( 'Aujourd’hui en vitrine', 'In the window today' ),
		'Vitrine'                                    => array( 'vitrine', 'window' ),
		'Aus derselben'                              => array( 'De la même', 'From the same' ),
		'im Detail'                                  => array( 'en détail', 'in detail' ),
		'Von Hand gemacht:'                          => array( 'Fait main :', 'Handmade:' ),
		'Passt zu'                                   => array( 'Se marie avec', 'Pairs with' ),
		'Herkunft'                                   => array( 'Origine', 'Origin' ),
		'Vorheriges Produkt'                         => array( 'Produit précédent', 'Previous product' ),
		'Nächstes Produkt'                           => array( 'Produit suivant', 'Next product' ),
		'Unsere Spezialitäten'                       => array( 'Nos spécialités', 'Our specialities' ),
		'Klicken Sie auf ein Produkt für alle Details.' => array( 'Cliquez sur un produit pour voir tous les détails.', 'Click on a product for all the details.' ),

		/* ----- allergènes et régimes ----- */
		'Allergene'                                  => array( 'Allergènes', 'Allergens' ),
		'Allergene und Ernährung'                    => array( 'Allergènes et alimentation', 'Allergens and diet' ),
		'Allergien'                                  => array( 'Allergies', 'Allergies' ),
		'Spuren'                                     => array( 'Traces', 'Traces' ),
		'Geeignet'                                   => array( 'Convient', 'Suitable' ),
		'Ohne %s'                                    => array( 'Sans %s', 'No %s' ),
		'Enthält keines der 14 deklarationspflichtigen Allergene.' => array( 'Ne contient aucun des 14 allergènes à déclaration obligatoire.', 'Contains none of the 14 allergens that must be declared.' ),
		'Keines der 14 deklarationspflichtigen Allergene' => array( 'Aucun des 14 allergènes à déclaration obligatoire', 'None of the 14 allergens that must be declared' ),
		'Gluten'                                     => array( 'Gluten', 'Gluten' ),
		'Eier'                                       => array( 'Œufs', 'Eggs' ),
		'Milch (Laktose)'                            => array( 'Lait (lactose)', 'Milk (lactose)' ),
		'Milch'                                      => array( 'lait', 'milk' ),
		'Schalenfrüchte (Mandeln, Haselnüsse, Nüsse…)' => array( 'Fruits à coque (amandes, noisettes, noix…)', 'Tree nuts (almonds, hazelnuts, walnuts…)' ),
		'Nüsse'                                      => array( 'fruits à coque', 'nuts' ),
		'Erdnüsse'                                   => array( 'Arachides', 'Peanuts' ),
		'Soja'                                       => array( 'Soja', 'Soy' ),
		'Sesam'                                      => array( 'Sésame', 'Sesame' ),
		'Sulfite'                                    => array( 'Sulfites', 'Sulphites' ),
		'Sellerie'                                   => array( 'Céleri', 'Celery' ),
		'Senf'                                       => array( 'Moutarde', 'Mustard' ),
		'Fisch'                                      => array( 'Poisson', 'Fish' ),
		'Krebstiere'                                 => array( 'Crustacés', 'Crustaceans' ),
		'Weichtiere'                                 => array( 'Mollusques', 'Molluscs' ),
		'Lupinen'                                    => array( 'Lupin', 'Lupin' ),
		'Alkohol (Kirsch, Likör…)'                   => array( 'Alcool (kirsch, liqueur…)', 'Alcohol (kirsch, liqueur…)' ),
		'Alkohol'                                    => array( 'alcool', 'alcohol' ),
		'Vegetarisch'                                => array( 'Végétarien', 'Vegetarian' ),
		'Vegan'                                      => array( 'Végane', 'Vegan' ),
		'Glutenfrei'                                 => array( 'Sans gluten', 'Gluten-free' ),
		'Laktosefrei'                                => array( 'Sans lactose', 'Lactose-free' ),

		/* ----- jours fériés ----- */
		'Neujahr'                                    => array( 'Nouvel An', 'New Year’s Day' ),
		'Fasnachtsmontag (Morgestraich)'             => array( 'Lundi de Fasnacht (Morgestraich)', 'Fasnacht Monday (Morgestraich)' ),
		'Fasnachtsdienstag'                          => array( 'Mardi de Fasnacht', 'Fasnacht Tuesday' ),
		'Fasnachtsmittwoch'                          => array( 'Mercredi de Fasnacht', 'Fasnacht Wednesday' ),
		'Karfreitag'                                 => array( 'Vendredi saint', 'Good Friday' ),
		'Ostern'                                     => array( 'Pâques', 'Easter' ),
		'Ostermontag'                                => array( 'Lundi de Pâques', 'Easter Monday' ),
		'Tag der Arbeit'                             => array( 'Fête du travail', 'Labour Day' ),
		'Auffahrt'                                   => array( 'Ascension', 'Ascension Day' ),
		'Pfingsten'                                  => array( 'Pentecôte', 'Whitsun' ),
		'Pfingstmontag'                              => array( 'Lundi de Pentecôte', 'Whit Monday' ),
		'Bundesfeier'                                => array( 'Fête nationale', 'Swiss National Day' ),
		'Heiligabend'                                => array( 'Veille de Noël', 'Christmas Eve' ),
		'Weihnachten'                                => array( 'Noël', 'Christmas Day' ),
		'Stephanstag'                                => array( 'Saint-Étienne', 'St Stephen’s Day' ),
		'Silvester'                                  => array( 'Saint-Sylvestre', 'New Year’s Eve' ),

		/* ----- carte du Tea Room ----- */
		'Die Karte'                                  => array( 'La carte', 'The menu' ),
		'Die Karte des Tea Room'                     => array( 'La carte du salon de thé', 'The Tea Room menu' ),
		'Karte des Tea Room'                         => array( 'Carte du salon de thé', 'Tea Room menu' ),
		'Karte Tea Room'                             => array( 'Carte du salon de thé', 'Tea Room menu' ),
		'Rubriken der Karte'                         => array( 'Rubriques de la carte', 'Menu sections' ),
		'Die ganze Karte als PDF'                    => array( 'Toute la carte en PDF', 'The whole menu as a PDF' ),
		'Zum Lesen, Drucken oder Teilen'             => array( 'À lire, imprimer ou partager', 'To read, print or share' ),
		'Unser Tipp'                                 => array( 'Notre conseil', 'Our tip' ),
		'Empfehlung des Hauses'                      => array( 'Recommandation de la maison', 'House recommendation' ),
		'Das älteste Kaffeehaus der Schweiz'         => array( 'Le plus ancien café de Suisse', 'Switzerland’s oldest coffee house' ),
		'Alle Preise in CHF inkl. MwSt. · Auskunft zu Allergenen erhalten Sie gerne bei unserem Team.' => array( 'Tous les prix en CHF, TVA comprise · Notre équipe vous renseigne volontiers sur les allergènes.', 'All prices in CHF incl. VAT · Our team will gladly inform you about allergens.' ),

		/* ----- cartes, accès, trajets ----- */
		'Route'                                      => array( 'Itinéraire', 'Directions' ),
		'Route planen'                               => array( 'Calculer l’itinéraire', 'Plan your route' ),
		'Route öffnen'                               => array( 'Ouvrir l’itinéraire', 'Open route' ),
		'Ich starte in'                              => array( 'Je pars de', 'I am starting from' ),
		'Dauer'                                      => array( 'Durée', 'Duration' ),
		'Umsteigen'                                  => array( 'Changements', 'Changes' ),
		'Fussweg'                                    => array( 'À pied', 'Walk' ),
		'In Google Maps öffnen'                      => array( 'Ouvrir dans Google Maps', 'Open in Google Maps' ),
		'Google-Maps-Karte anzeigen'                 => array( 'Afficher la carte Google Maps', 'Show the Google map' ),
		'Google-Maps-Karte:'                         => array( 'Carte Google Maps :', 'Google map:' ),
		'Die Karte wird von Google bereitgestellt: Beim Anzeigen wird Ihre IP-Adresse an Google übermittelt.' => array( 'La carte est fournie par Google : en l’affichant, votre adresse IP est transmise à Google.', 'The map is provided by Google: when it is displayed, your IP address is sent to Google.' ),
		'Karte zentrieren'                           => array( 'Recentrer la carte', 'Centre the map' ),
		'Vergrössern'                                => array( 'Agrandir', 'Zoom in' ),
		'Vergrössern:'                               => array( 'Agrandir :', 'Enlarge:' ),
		'Verkleinern'                                => array( 'Réduire', 'Zoom out' ),

		/* ----- galeries, histoire, archives ----- */
		'Archiv'                                     => array( 'Archive', 'Archive' ),
		'Archivbild vergrössert'                     => array( 'Image d’archive agrandie', 'Enlarged archive picture' ),
		'Vorheriges Archivbild'                      => array( 'Image d’archive précédente', 'Previous archive picture' ),
		'Nächstes Archivbild'                        => array( 'Image d’archive suivante', 'Next archive picture' ),
		'Vorheriges Foto'                            => array( 'Photo précédente', 'Previous photo' ),
		'Nächstes Foto'                              => array( 'Photo suivante', 'Next photo' ),
		'Vorheriges Datum'                           => array( 'Date précédente', 'Previous date' ),
		'Nächstes Datum'                             => array( 'Date suivante', 'Next date' ),
		'Vorherige Epoche'                           => array( 'Époque précédente', 'Previous era' ),
		'Nächste Epoche'                             => array( 'Époque suivante', 'Next era' ),
		'Zeitleiste'                                 => array( 'Chronologie', 'Timeline' ),
		'Früher und heute vergleichen'               => array( 'Comparer hier et aujourd’hui', 'Compare then and now' ),
		'Backstube'                                  => array( 'Atelier', 'Bakehouse' ),
		'Schliessen'                                 => array( 'Fermer', 'Close' ),
		'Vorschau'                                   => array( 'Aperçu', 'Preview' ),
		'Ansehen'                                    => array( 'Voir', 'View' ),
		'Ziehen'                                     => array( 'Glisser', 'Drag' ),
		'Öffnen'                                     => array( 'Ouvrir', 'Open' ),
		'Jahre Tradition'                            => array( 'ans de tradition', 'years of tradition' ),
		'Ziehen zum Entdecken →'                     => array( 'Glissez pour découvrir →', 'Drag to explore →' ),
		'Gut zu wissen'                              => array( 'Bon à savoir', 'Good to know' ),
		'Scrollen Sie, um hinaufzusteigen'           => array( 'Faites défiler pour monter', 'Scroll to go upstairs' ),
		'Klicken oder darüberfahren zum Entdecken'   => array( 'Cliquez ou survolez pour découvrir', 'Click or hover to explore' ),
		'Ziehen zum Vergleichen'                     => array( 'Glissez pour comparer', 'Drag to compare' ),
		'Abb. 01'                                    => array( 'Fig. 01', 'Fig. 01' ),

		/* ----- bandeau, plan ----- */
		'Einkauf'                                    => array( 'Achats', 'Shopping' ),
		'Vor Ort, an der Theke'                      => array( 'Sur place, au comptoir', 'In store, at the counter' ),

		/* ----- avis ----- */
		'%s Google-Bewertungen'                      => array( '%s avis Google', '%s Google reviews' ),
		'%s von 5 Sternen'                           => array( '%s étoiles sur 5', '%s out of 5 stars' ),
		'Bewertung von %s'                           => array( 'Avis de %s', 'Review by %s' ),
		'Bewertungen von Google, unverändert wiedergegeben.' => array( 'Avis Google, reproduits sans modification.', 'Google reviews, reproduced unchanged.' ),
		'Alle Bewertungen ansehen'                   => array( 'Voir tous les avis', 'See all reviews' ),
		'Bewertung schreiben'                        => array( 'Écrire un avis', 'Write a review' ),
		'Gästebuch'                                  => array( 'Livre d’or', 'Guestbook' ),

		/* ----- formulaire ----- */
		'Name'                                       => array( 'Nom', 'Name' ),
		'E-Mail'                                     => array( 'E-mail', 'Email' ),
		'Telefon'                                    => array( 'Téléphone', 'Phone' ),
		'Ihre Nachricht'                             => array( 'Votre message', 'Your message' ),
		'Senden'                                     => array( 'Envoyer', 'Send' ),
		'Nachricht senden'                           => array( 'Envoyer le message', 'Send message' ),
		'Schreiben Sie uns'                          => array( 'Écrivez-nous', 'Write to us' ),
		'Dieses Feld leer lassen'                    => array( 'Laisser ce champ vide', 'Leave this field empty' ),
		'Ihre Angaben dienen ausschliesslich der Bearbeitung Ihrer Anfrage.' => array( 'Vos données servent uniquement à traiter votre demande.', 'Your details are used solely to handle your request.' ),
		'Ihre Nachricht ist unterwegs'               => array( 'Votre message est en route', 'Your message is on its way' ),
		'Vielen Dank. Wir antworten Ihnen innerhalb von ein bis zwei Werktagen.' => array( 'Merci beaucoup. Nous vous répondons sous un à deux jours ouvrables.', 'Thank you. We will reply within one or two working days.' ),
		'Bitte geben Sie Ihren Namen, eine gültige E-Mail-Adresse und Ihre Nachricht an.' => array( 'Merci d’indiquer votre nom, une adresse e-mail valide et votre message.', 'Please enter your name, a valid email address and your message.' ),
		'Die Nachricht konnte nicht gesendet werden. Schreiben Sie uns direkt an %s oder rufen Sie uns an.' => array( 'Le message n’a pas pu être envoyé. Écrivez-nous directement à %s ou appelez-nous.', 'The message could not be sent. Write to us directly at %s or give us a call.' ),
		'Von dieser Verbindung wurde gerade eine Nachricht gesendet. Bitte warten Sie eine Minute, bevor Sie eine weitere senden.' => array( 'Un message vient d’être envoyé depuis cette connexion. Merci d’attendre une minute avant d’en envoyer un autre.', 'A message has just been sent from this connection. Please wait a minute before sending another one.' ),

		/* ----- Réglages maison : textes fournis par défaut ----- */
		'Confiserie & Tea Room · Marktplatz Basel · seit 1870' => array( 'Confiserie & salon de thé · Marktplatz de Bâle · depuis 1870', 'Confectionery & Tea Room · Marktplatz Basel · since 1870' ),
		'An Feiertagen können die Öffnungszeiten abweichen. Im Zweifel genügt ein Anruf.' => array( 'Les horaires peuvent varier les jours fériés. En cas de doute, un appel suffit.', 'Opening hours may differ on public holidays. If in doubt, just give us a call.' ),
		'In unserer Backstube werden auch Gluten, Nüsse, Milch und Eier verarbeitet: Spuren sind möglich. Unser Team gibt Ihnen gerne Auskunft.' => array( 'Notre atelier travaille aussi le gluten, les fruits à coque, le lait et les œufs : des traces sont possibles. Notre équipe vous renseigne volontiers.', 'Our bakehouse also handles gluten, nuts, milk and eggs: traces are possible. Our team will gladly advise you.' ),
		'Die Confiserie Schiesser wurde 1870 am Basler Marktplatz gegründet. Läckerli, Pralinen, Torten und Gebäck entstehen in der eigenen Backstube; im ersten Stock liegt der Tea Room, das älteste Kaffeehaus der Schweiz.' => array( 'La Confiserie Schiesser a été fondée en 1870 sur le Marktplatz de Bâle. Läckerli, pralinés, gâteaux et viennoiseries naissent dans notre propre atelier ; au premier étage se trouve le salon de thé, le plus ancien café de Suisse.', 'Confiserie Schiesser was founded in 1870 on the Marktplatz in Basel. Läckerli, pralines, cakes and pastries are made in our own bakehouse; on the first floor is the Tea Room, Switzerland’s oldest coffee house.' ),
		'Läckerli'                                   => array( 'Läckerli', 'Läckerli' ),
		'Truffes'                                    => array( 'Truffes', 'Truffles' ),
		'Fruchtwähe'                                 => array( 'Tarte aux fruits', 'Fruit tart' ),
	);
}

/** Textes utilisés par les scripts (site.js, maquette.js, boutique.js) : envoyés à la page dans sa langue. */
function schiesser_traductions_js() {
	return array(
		'Geöffnet', 'Geschlossen', 'geschlossen', 'Heute geschlossen', 'Geöffnet bis %s', 'Geschlossen · öffnet um %s',
		'Geschlossen · öffnet %1$s um %2$s', 'am %s', 'morgen', 'Morgen', 'Schliesst in', 'Öffnet in', 'Std.', 'Min.',
		'Sehr belebt', 'Belebt', 'Ruhig', 'Archiv', 'Ansehen', 'Ziehen', 'Öffnen', 'Entdecken',
		'Merkliste', 'Meine Merkliste', 'Schliessen', 'Ihre Liste, um telefonisch oder per Nachricht zu bestellen. Sie bleibt in diesem Browser gespeichert.',
		'Telefonisch bestellen', 'Per Nachricht senden', 'Liste kopieren', 'Liste kopieren:', 'Liste kopiert', 'Liste leeren', 'Merkliste leeren?',
		'Auf der Merkliste', 'Merken', 'Auf meine Merkliste', 'Eins weniger:', 'Eins mehr:', 'Entfernen:', 'Ihre Merkliste ist leer.', 'Anrufen (%s)',
		'Link kopieren', 'Link kopiert', 'Produkte', 'Produkt', 'Preis',
		'Grüezi', 'Ich möchte gerne bestellen:', 'Gewünschtes Abholdatum:', 'Meine Telefonnummer:', 'Vielen Dank!',
	);
}

/** Mots des libellés de formats (« pro 100 g », « klein », « mit Schinken… ») : remplacés dans le texte. */
function schiesser_traductions_mots() {
	return array(
		'mit Schinken, Thon, Ei oder Käse'       => array( 'jambon, thon, œuf ou fromage', 'with ham, tuna, egg or cheese' ),
		'Schinken, Spargel, Sellerie oder Ei'    => array( 'jambon, asperges, céleri ou œuf', 'ham, asparagus, celery or egg' ),
		'Tatar oder Lachs'                       => array( 'tartare ou saumon', 'tartare or salmon' ),
		'dunkel, milch, weiss oder ruby'         => array( 'noir, lait, blanc ou ruby', 'dark, milk, white or ruby' ),
		'Salz oder Kümmel'                       => array( 'sel ou cumin', 'salt or caraway' ),
		'Mohn oder Käse'                         => array( 'pavot ou fromage', 'poppy seed or cheese' ),
		'Zitrone oder Schokolade'                => array( 'citron ou chocolat', 'lemon or chocolate' ),
		'Schokolade oder Vanille'                => array( 'chocolat ou vanille', 'chocolate or vanilla' ),
		'Plexi-Box'                              => array( 'boîte en plexiglas', 'Perspex box' ),
		'pro 100 g'                              => array( 'les 100 g', 'per 100 g' ),
		'Stück'                                  => array( 'pièce', 'piece' ),
		'Brötli'                                 => array( 'petit pain', 'roll' ),
		'klein'                                  => array( 'petit', 'small' ),
		'gross'                                  => array( 'grand', 'large' ),
		'Dose'                                   => array( 'boîte', 'tin' ),
		'Thon'                                   => array( 'thon', 'tuna' ),
		'Formaten'                               => array( 'formats', 'sizes' ),
	);
}

/**
 * Dictionnaires par langue : fr, en (textes du thème), fr-js, en-js (scripts), fr-mots, en-mots (formats).
 */
function schiesser_traductions() {
	static $t = null;
	if ( null !== $t ) {
		return $t;
	}
	$t = array();
	foreach ( array( 'fr' => 0, 'en' => 1 ) as $l => $i ) {
		$t[ $l ] = array();
		foreach ( schiesser_traductions_base() as $de => $tr ) {
			$t[ $l ][ $de ] = $tr[ $i ];
		}
		$t[ $l . '-js' ] = array();
		foreach ( schiesser_traductions_js() as $de ) {
			if ( isset( $t[ $l ][ $de ] ) ) {
				$t[ $l . '-js' ][ $de ] = $t[ $l ][ $de ];
			}
		}
		$t[ $l . '-mots' ] = array();
		foreach ( schiesser_traductions_mots() as $de => $tr ) {
			$t[ $l . '-mots' ][ $de ] = $tr[ $i ];
		}
	}
	return $t;
}

<?php
/**
 * Référencement naturel (SEO).
 *
 * Rank Math SEO est l'outil principal : titres, descriptions, mots-clés,
 * plan du site, redirections. Le thème le complète automatiquement :
 * - la fiche établissement (type, adresse, horaires, position) vient des
 *   « Réglages maison » : une seule saisie, jamais de double emploi ;
 * - chaque page reçoit le bon type (Contact, À propos, Collection, Produit) ;
 * - pages produits : données « Product » (prix en CHF, photo, marque) ;
 * - page boutique : liste des produits (ItemList) ;
 * - questions dépliables : données « FAQPage » ;
 * - fil d'Ariane (BreadcrumbList).
 *
 * Sans Rank Math (site en préparation), le thème publie lui-même un titre,
 * une description, les balises de partage et ces mêmes données structurées.
 */

defined( 'ABSPATH' ) || exit;

function schiesser_rank_math_actif() {
	return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
}

/** Devise des prix : celle de WooCommerce s'il est installé, sinon le franc suisse. */
function schiesser_devise() {
	return function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'CHF';
}

/* ------------------------------------------------------------------ */
/* Fiche établissement                                                 */
/* ------------------------------------------------------------------ */

/** Logo de la maison : logo du site (Apparence → Personnaliser → Identité du site), sinon icône du site. */
function schiesser_logo_maison() {
	$logo = (int) get_theme_mod( 'custom_logo' );
	if ( $logo && wp_get_attachment_image_url( $logo, 'full' ) ) {
		return wp_get_attachment_image_url( $logo, 'full' );
	}
	return (string) get_site_icon_url( 512 );
}

/** Image représentative de la maison : photo du Hero de l'accueil, sinon le logo. */
function schiesser_image_maison() {
	$accueil = (int) get_option( 'page_on_front' );
	$photo   = $accueil ? schiesser_image_page( $accueil ) : '';
	return $photo ?: schiesser_logo_maison();
}

/**
 * Données structurées de l'établissement (schema.org LocalBusiness).
 * Même identifiant que l'organisation de Rank Math : les deux se complètent.
 */
function schiesser_schema_etablissement() {
	$r     = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), schiesser_reglages_defaut() );
	$types = array_values( array_filter( array_map( 'trim', explode( ',', (string) $r['type_etablissement'] ) ) ) );
	$types = $types ?: array( 'LocalBusiness' );

	$schema = array(
		'@type' => 1 === count( $types ) ? $types[0] : $types,
		'@id'   => home_url( '/#organization' ),
		'name'  => $r['nom_etablissement'] ?: get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);
	if ( $r['presentation'] ) {
		$schema['description'] = $r['presentation'];
	}
	$image = schiesser_image_maison();
	if ( $image ) {
		$schema['image'] = $image;
	}
	$logo = schiesser_logo_maison();
	if ( $logo ) {
		$schema['logo'] = $logo;
	}
	if ( $r['telephone'] ) {
		$schema['telephone'] = $r['telephone'];
	}
	if ( $r['email'] ) {
		$schema['email'] = $r['email'];
	}
	$schema['address'] = array_filter( array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => $r['rue'],
		'postalCode'      => $r['code_postal'],
		'addressLocality' => $r['ville'],
		'addressRegion'   => $r['region'] ?? '',
		'addressCountry'  => $r['pays'],
	) );
	if ( is_numeric( $r['latitude'] ) && is_numeric( $r['longitude'] ) ) {
		$schema['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) $r['latitude'],
			'longitude' => (float) $r['longitude'],
		);
	}
	if ( $r['lien_maps'] ) {
		$schema['hasMap'] = $r['lien_maps'];
	}

	// Horaires : les jours aux mêmes heures sont regroupés.
	$noms     = array( 0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday' );
	$groupes  = array();
	foreach ( schiesser_jours() as $n => $jour ) {
		$h = $r['horaires'][ $n ] ?? null;
		if ( ! $h || ! empty( $h['ferme'] ) ) {
			continue;
		}
		$cle                        = $h['ouverture'] . '-' . $h['fermeture'];
		$groupes[ $cle ]['jours'][] = $noms[ $n ];
		$groupes[ $cle ]['h']       = $h;
	}
	foreach ( $groupes as $g ) {
		$schema['openingHoursSpecification'][] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $g['jours'],
			'opens'     => $g['h']['ouverture'],
			'closes'    => $g['h']['fermeture'],
		);
	}

	// Gamme de prix : une fourchette (« CHF 2–30 ») ou des symboles (« $$ ») ; un code devise seul ne veut rien dire.
	if ( preg_match( '/\d|\$/', (string) $r['gamme_prix'] ) ) {
		$schema['priceRange'] = $r['gamme_prix'];
	}
	if ( 'CH' === $r['pays'] ) {
		$schema['currenciesAccepted'] = 'CHF';
	}
	if ( $r['annee_fondation'] ) {
		$schema['foundingDate'] = $r['annee_fondation'];
	}
	$reseaux = array_values( array_filter( array( $r['instagram'], $r['facebook'] ) ) );
	if ( $reseaux ) {
		$schema['sameAs'] = $reseaux;
	}
	return $schema;
}

/* ------------------------------------------------------------------ */
/* Informations sur la page affichée                                   */
/* ------------------------------------------------------------------ */

/** Blocs de la page affichée (analysés une seule fois). */
function schiesser_blocs_page( $post = null ) {
	static $cache = array();
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	if ( ! isset( $cache[ $post->ID ] ) ) {
		$cache[ $post->ID ] = has_blocks( $post->post_content ) ? parse_blocks( $post->post_content ) : array();
	}
	return $cache[ $post->ID ];
}

/** Photo principale d'une page : celle du Hero, sinon l'image mise en avant. */
function schiesser_image_page( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	if ( SCHIESSER_PRODUIT === $post->post_type && has_post_thumbnail( $post ) ) {
		return (string) get_the_post_thumbnail_url( $post, 'full' );
	}
	$hero = schiesser_trouver_bloc( 'schiesser/hero', schiesser_blocs_page( $post ) );
	if ( $hero ) {
		$id = (int) ( $hero['attrs']['imageId'] ?? 0 );
		if ( $id && wp_get_attachment_image_url( $id, 'full' ) ) {
			return wp_get_attachment_image_url( $id, 'full' );
		}
		if ( ! empty( $hero['attrs']['imageUrl'] ) ) {
			return $hero['attrs']['imageUrl'];
		}
	}
	return has_post_thumbnail( $post ) ? (string) get_the_post_thumbnail_url( $post, 'full' ) : '';
}

/** Type schema.org de la page affichée. */
function schiesser_type_page() {
	if ( is_front_page() ) {
		return 'WebPage';
	}
	if ( is_singular( SCHIESSER_PRODUIT ) ) {
		return 'ItemPage';
	}
	if ( is_search() ) {
		return 'SearchResultsPage';
	}
	$types = array(
		'shop'    => 'CollectionPage',
		'contact' => 'ContactPage',
		'story'   => 'AboutPage',
	);
	return $types[ schiesser_cle_page() ] ?? 'WebPage';
}

/** Titre SEO saisi dans Rank Math (utilisé aussi quand Rank Math est absent). */
function schiesser_titre_seo( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$titre = (string) get_post_meta( $post->ID, 'rank_math_title', true );
	if ( '' === $titre ) {
		return '';
	}
	// Variables de Rank Math les plus courantes.
	$titre = strtr( $titre, array(
		'%title%'    => get_the_title( $post ),
		'%sitename%' => get_bloginfo( 'name' ),
		'%sep%'      => '|',
		'%page%'     => '',
	) );
	return trim( preg_replace( '/%[a-z_]+%/', '', $titre ) );
}

/** Description de la page : Rank Math, sinon description du produit, sinon texte du Hero, sinon extrait. */
function schiesser_description_page( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return get_bloginfo( 'description' );
	}
	$desc = (string) get_post_meta( $post->ID, 'rank_math_description', true );
	if ( '' === $desc && SCHIESSER_PRODUIT === $post->post_type ) {
		$desc = (string) get_post_meta( $post->ID, '_s_description', true );
	}
	if ( '' === $desc ) {
		$hero = schiesser_trouver_bloc( 'schiesser/hero', schiesser_blocs_page( $post ) );
		$desc = $hero ? schiesser_texte_brut( $hero['attrs']['texte'] ?? '' ) : '';
	}
	if ( '' === $desc ) {
		$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 30, '…' );
	}
	$desc = schiesser_texte_brut( $desc );
	return mb_strlen( $desc ) > 160 ? rtrim( mb_substr( $desc, 0, 157 ) ) . '…' : $desc;
}

/* ------------------------------------------------------------------ */
/* Données structurées propres à la page                               */
/* ------------------------------------------------------------------ */

/** Fil d'Ariane : Accueil → (Boutique →) Page. */
function schiesser_schema_ariane() {
	if ( is_front_page() || ! is_singular() ) {
		return null;
	}
	$url      = get_permalink();
	$elements = array( array( 'Accueil', home_url( '/' ) ) );
	if ( is_singular( SCHIESSER_PRODUIT ) ) {
		$boutique = schiesser_url_boutique();
		if ( untrailingslashit( $boutique ) !== untrailingslashit( home_url( '/' ) ) ) {
			$id_page    = url_to_postid( $boutique );
			$elements[] = array( $id_page ? get_the_title( $id_page ) : 'Boutique', $boutique );
		}
	}
	foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $parent ) {
		$elements[] = array( get_the_title( $parent ), get_permalink( $parent ) );
	}
	$elements[] = array( get_the_title(), $url );

	$liste = array();
	foreach ( $elements as $i => $e ) {
		$liste[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => wp_strip_all_tags( $e[0] ),
			'item'     => $e[1],
		);
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $url . '#breadcrumb',
		'itemListElement' => $liste,
	);
}

/** Produit « maison » (page /produits/…). */
function schiesser_schema_produit( $post ) {
	$p      = schiesser_donnees_produit( $post );
	$schema = array(
		'@type'       => 'Product',
		'@id'         => $p['url'] . '#product',
		'name'        => $p['nom'],
		'url'         => $p['url'],
		'description' => $p['description'] ?: schiesser_description_page( $post ),
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => schiesser_reglage( 'nom_etablissement' ) ?: get_bloginfo( 'name' ),
		),
	);
	if ( $p['image_id'] ) {
		$schema['image'] = wp_get_attachment_image_url( $p['image_id'], 'full' );
	}
	if ( $p['categorie'] ) {
		$schema['category'] = $p['categorie'];
	}
	foreach ( $p['fiche'] as $ligne ) {
		if ( ! empty( $ligne[0] ) && ! empty( $ligne[1] ) ) {
			$schema['additionalProperty'][] = array(
				'@type' => 'PropertyValue',
				'name'  => $ligne[0],
				'value' => $ligne[1],
			);
		}
	}
	// Google exige un prix pour décrire un produit : sans prix chiffré (« Sur commande »), pas de données Product.
	// Astuce pour le client : saisir « Dès CHF 45 » active les données avec un prix de départ.
	$prix = schiesser_prix_numerique( $p['prix'] );
	if ( null === $prix || $prix <= 0 ) {
		return null;
	}
	$offre = array(
		'@type'         => 'Offer',
		'url'           => $p['url'],
		'price'         => number_format( $prix, 2, '.', '' ),
		'priceCurrency' => schiesser_devise(),
		'availability'  => 'https://schema.org/InStock',
		'itemCondition' => 'https://schema.org/NewCondition',
		'seller'        => array( '@id' => home_url( '/#organization' ) ),
	);
	if ( preg_match( '/\b(dès|des|à partir|a partir|ab)\b/iu', (string) $p['prix'] ) ) {
		// prix de départ : « Dès CHF 45 »
		$offre = array(
			'@type'         => 'AggregateOffer',
			'url'           => $p['url'],
			'lowPrice'      => number_format( $prix, 2, '.', '' ),
			'priceCurrency' => schiesser_devise(),
			'offerCount'    => 1,
			'availability'  => 'https://schema.org/InStock',
		);
	}
	$schema['offers'] = $offre;
	return $schema;
}

/** Liste des produits présentés sur la page (bloc « Grille des produits »). */
function schiesser_schema_liste_produits( $post ) {
	$bloc = schiesser_trouver_bloc( 'schiesser/produits', schiesser_blocs_page( $post ) );
	if ( ! $bloc ) {
		return null;
	}
	$produits = schiesser_liste_produits( $bloc['attrs']['source'] ?? 'auto', (int) ( $bloc['attrs']['limite'] ?? 0 ) );
	if ( ! $produits ) {
		return null;
	}
	$elements = array();
	foreach ( array_values( $produits ) as $i => $p ) {
		$elements[] = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'url'      => $p['url'],
			'name'     => $p['nom'],
		);
	}
	return array(
		'@type'           => 'ItemList',
		'@id'             => get_permalink( $post ) . '#produits',
		'name'            => schiesser_texte_brut( $bloc['attrs']['titre'] ?? 'Produits' ),
		'numberOfItems'   => count( $elements ),
		'itemListElement' => $elements,
	);
}

/** Questions dépliables (bloc « Détails ») → FAQPage. */
function schiesser_schema_faq( $post ) {
	$questions = array();
	foreach ( schiesser_tous_les_blocs( 'core/details', schiesser_blocs_page( $post ) ) as $b ) {
		if ( ! preg_match( '#<summary[^>]*>(.*?)</summary>#s', $b['innerHTML'], $m ) ) {
			continue;
		}
		$reponse = '';
		foreach ( $b['innerBlocks'] as $enfant ) {
			$reponse .= ' ' . render_block( $enfant );
		}
		$question = schiesser_texte_brut( $m[1] );
		$reponse  = schiesser_texte_brut( $reponse );
		if ( '' !== $question && '' !== $reponse ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $reponse,
				),
			);
		}
	}
	if ( ! $questions ) {
		return null;
	}
	return array(
		'@type'      => 'FAQPage',
		'@id'        => get_permalink( $post ) . '#faq',
		'mainEntity' => $questions,
	);
}

/** Informations de base du nœud « page » (utilisées sans Rank Math, et pour compléter Rank Math). */
function schiesser_schema_page_complements() {
	if ( ! is_singular() ) {
		return array();
	}
	$id    = get_queried_object_id();
	$desc  = schiesser_description_page( $id );
	$image = schiesser_image_page( $id );
	$page  = array(
		'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
		'about'      => array( '@id' => home_url( '/#organization' ) ),
		'inLanguage' => get_bloginfo( 'language' ),
	);
	if ( $desc ) {
		$page['description'] = $desc;
	}
	if ( $image ) {
		$page['primaryImageOfPage'] = array( '@type' => 'ImageObject', 'url' => $image );
	}
	return $page;
}

/** Toutes les données propres à la page affichée. */
function schiesser_schemas_page() {
	$sortie = array();
	if ( ! is_singular() ) {
		return $sortie;
	}
	$post = get_queried_object();

	$ariane = schiesser_schema_ariane();
	if ( $ariane ) {
		$sortie['breadcrumb'] = $ariane;
	}
	if ( is_singular( SCHIESSER_PRODUIT ) ) {
		$produit = schiesser_schema_produit( $post );
		if ( $produit ) {
			$sortie['product'] = $produit;
		}
	}
	$liste = schiesser_schema_liste_produits( $post );
	if ( $liste ) {
		$sortie['itemlist'] = $liste;
	}
	$faq = schiesser_schema_faq( $post );
	if ( $faq ) {
		$sortie['faq'] = $faq;
	}
	return $sortie;
}

/* ------------------------------------------------------------------ */
/* Avec Rank Math : on complète son graphe                             */
/* ------------------------------------------------------------------ */

/** Un schéma a-t-il été choisi à la main pour cette page dans Rank Math (onglet « Schema ») ? */
function schiesser_schema_rank_math_choisi( $post_id ) {
	foreach ( array_keys( (array) get_post_meta( $post_id ) ) as $cle ) {
		if ( 0 === strpos( $cle, 'rank_math_schema_' ) ) {
			return true;
		}
	}
	return false;
}

add_filter( 'rank_math/json_ld', function ( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}
	$etab     = schiesser_schema_etablissement();
	$fusionne = false;
	$page_cle = null;

	foreach ( $data as $cle => $entite ) {
		if ( ! is_array( $entite ) || empty( $entite['@id'] ) ) {
			continue;
		}
		if ( $entite['@id'] === $etab['@id'] ) {
			// Rank Math garde son logo s'il en a un ; le thème apporte type, adresse, horaires, position.
			if ( isset( $entite['logo'] ) ) {
				unset( $etab['logo'] );
			}
			$data[ $cle ] = array_merge( $entite, $etab );
			$fusionne     = true;
		}
		if ( '#webpage' === substr( $entite['@id'], -8 ) ) {
			$page_cle = $cle;
		}
	}
	if ( ! $fusionne ) {
		$data['schiesser_etablissement'] = $etab;
	}

	// Pages et produits : Rank Math ajoute par défaut un « Article », qui ne décrit pas une page
	// Contact ou Boutique. On le retire, sauf si un schéma a été choisi à la main dans Rank Math.
	$choisi = is_singular() && schiesser_schema_rank_math_choisi( get_queried_object_id() );
	if ( is_singular( array( 'page', SCHIESSER_PRODUIT ) ) && ! $choisi ) {
		$a_retirer = array( 'Article', 'BlogPosting', 'NewsArticle' );
		if ( is_singular( SCHIESSER_PRODUIT ) ) {
			$a_retirer[] = 'Product'; // le thème décrit lui-même le produit (prix, photo, fiche)
		}
		foreach ( $data as $cle => $entite ) {
			$types = is_array( $entite ) ? (array) ( $entite['@type'] ?? array() ) : array();
			if ( array_intersect( $types, $a_retirer ) ) {
				unset( $data[ $cle ] );
			}
		}
	}

	$deja_ariane = false;
	foreach ( $data as $entite ) {
		if ( is_array( $entite ) && 'BreadcrumbList' === ( $entite['@type'] ?? '' ) ) {
			$deja_ariane = true;
		}
	}
	foreach ( schiesser_schemas_page() as $cle => $entite ) {
		if ( ( 'breadcrumb' === $cle && $deja_ariane ) || ( 'product' === $cle && $choisi ) ) {
			continue;
		}
		$data[ 'schiesser_' . $cle ] = $entite;
	}

	if ( null !== $page_cle ) {
		$data[ $page_cle ]['@type'] = schiesser_type_page();
		// Rank Math garde ses valeurs ; le thème ne comble que les informations manquantes.
		$data[ $page_cle ] += schiesser_schema_page_complements();
		if ( ! $deja_ariane && isset( $data['schiesser_breadcrumb'] ) ) {
			$data[ $page_cle ]['breadcrumb'] = array( '@id' => $data['schiesser_breadcrumb']['@id'] );
		}
		if ( isset( $data['schiesser_product'] ) ) {
			$data[ $page_cle ]['mainEntity'] = array( '@id' => $data['schiesser_product']['@id'] );
		}
	}
	return $data;
}, 99 );

/* ------------------------------------------------------------------ */
/* Sans Rank Math : le thème prend le relais                           */
/* ------------------------------------------------------------------ */

/* Titre de l'onglet : le titre SEO saisi (champ Rank Math), sinon « Page | Nom du site ». */
add_filter( 'pre_get_document_title', function ( $titre ) {
	if ( schiesser_rank_math_actif() || ! is_singular() ) {
		return $titre;
	}
	return schiesser_titre_seo( get_queried_object_id() ) ?: $titre;
} );
add_filter( 'document_title_separator', function ( $sep ) {
	return schiesser_rank_math_actif() ? $sep : '|';
} );

add_action( 'wp_head', function () {
	if ( schiesser_rank_math_actif() ) {
		return;
	}
	$site  = get_bloginfo( 'name' );
	$titre = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	$desc  = is_singular() ? schiesser_description_page( get_queried_object_id() ) : get_bloginfo( 'description' );
	$image = is_singular() ? schiesser_image_page( get_queried_object_id() ) : '';
	$image = $image ?: schiesser_image_maison();

	echo "\n<!-- SEO du thème Schiesser (remplacé automatiquement par Rank Math dès son activation) -->\n";
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	$og = array(
		'og:locale'      => get_locale(),
		'og:type'        => is_singular( SCHIESSER_PRODUIT ) ? 'product' : ( is_front_page() ? 'website' : 'article' ),
		'og:title'       => $titre,
		'og:description' => $desc,
		'og:url'         => $url,
		'og:site_name'   => $site,
		'og:image'       => $image,
	);
	foreach ( array_filter( $og ) as $propriete => $valeur ) {
		echo '<meta property="' . esc_attr( $propriete ) . '" content="' . esc_attr( $valeur ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";

	// Données structurées : établissement, site, page, puis données propres à la page.
	$graphe   = array( schiesser_schema_etablissement() );
	$graphe[] = array(
		'@type'      => 'WebSite',
		'@id'        => home_url( '/#website' ),
		'url'        => home_url( '/' ),
		'name'       => $site,
		'inLanguage' => get_bloginfo( 'language' ),
		'publisher'  => array( '@id' => home_url( '/#organization' ) ),
	);
	$propres = schiesser_schemas_page();
	if ( is_singular() ) {
		$page = array(
			'@type'      => schiesser_type_page(),
			'@id'        => $url . '#webpage',
			'url'        => $url,
			'name'       => $titre,
			'isPartOf'   => array( '@id' => home_url( '/#website' ) ),
			'about'      => array( '@id' => home_url( '/#organization' ) ),
			'inLanguage' => get_bloginfo( 'language' ),
		);
		if ( $desc ) {
			$page['description'] = $desc;
		}
		if ( $image ) {
			$page['primaryImageOfPage'] = array( '@type' => 'ImageObject', 'url' => $image );
		}
		if ( isset( $propres['breadcrumb'] ) ) {
			$page['breadcrumb'] = array( '@id' => $propres['breadcrumb']['@id'] );
		}
		if ( isset( $propres['product'] ) ) {
			$page['mainEntity'] = array( '@id' => $propres['product']['@id'] );
		}
		$graphe[] = $page;
	}
	foreach ( $propres as $entite ) {
		$graphe[] = $entite;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graphe ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}, 2 );

/* Pages sans intérêt pour Google : recherche interne, page introuvable, archives vides. */
add_filter( 'wp_robots', function ( $robots ) {
	global $wp_query;
	$archive_vide = is_archive() && $wp_query && 0 === (int) $wp_query->post_count;
	if ( ! schiesser_rank_math_actif() && ( is_search() || is_404() || $archive_vide ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
} );

/* Plan du site de WordPress (sans Rank Math) : pas de pages « auteur », inutiles pour une confiserie. */
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $nom ) {
	return 'users' === $nom ? false : $provider;
}, 10, 2 );

<?php
/**
 * Référencement naturel (SEO) et moteurs de réponse (GEO).
 *
 * Rank Math SEO est l'outil principal : titres, descriptions, mots clés, plan du site XML,
 * redirections. Le thème le complète automatiquement, sans double emploi :
 * - l'agence (ProfessionalService) reçoit adresse, téléphone, zone desservie, fondateur, profils ;
 * - le fondateur (Person) est décrit et relié à l'agence (signal d'expertise E-E-A-T) ;
 * - chaque page service, secteur ou locale reçoit son « Service » avec le prix de départ ;
 * - pages À propos, Contact, Réalisations : le bon type de page ; étude de cas : « CreativeWork » ;
 * - fil d'Ariane (BreadcrumbList) sur toutes les pages intérieures ;
 * - pas de FAQPage, HowTo ni note d'avis auto déclarée (sans effet ou ignorés par Google) ;
 * - fiches de réalisation sans étude de cas : noindex (contenu trop mince), liens suivis ;
 * - /llms.txt rédigé à partir des pages du site, robots.txt qui autorise les robots de citation IA.
 *
 * Sans Rank Math (site en préparation), le thème publie lui-même titre, description, balises de
 * partage et données structurées. Rank Math les remplace automatiquement dès son activation.
 */

defined( 'ABSPATH' ) || exit;

function aurea_rank_math_actif() {
	return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
}

/** Informations SEO d'une page livrée avec le thème (contenu/pages.json), d'après son slug. */
function aurea_info_page( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type ) {
		return null;
	}
	$pages = aurea_pages();
	$slug  = (int) get_option( 'page_on_front' ) === $post->ID ? 'accueil' : $post->post_name;
	return $pages[ $slug ] ?? null;
}

function aurea_id( $ancre ) {
	return home_url( '/#' . $ancre );
}

/* ------------------------------------------------------------------ */
/* Entités                                                             */
/* ------------------------------------------------------------------ */

function aurea_schema_agence() {
	$c     = aurea_coordonnees();
	$logo  = (int) get_theme_mod( 'custom_logo' );
	$agence = array(
		'@type'        => 'ProfessionalService',
		'@id'          => aurea_id( 'organization' ),
		'name'         => 'Aurea Media',
		'url'          => home_url( '/' ),
		'logo'         => $logo ? wp_get_attachment_image_url( $logo, 'full' ) : AUREA_URI . '/assets/logo-aurea-media.png',
		'image'        => AUREA_URI . '/assets/og-aurea-media.jpg',
		'description'  => 'Agence web indépendante : création de sites vitrines et e-commerce sur mesure, identité visuelle, motion design et présentations, pour les indépendants, TPE et PME en France et en Europe.',
		'telephone'    => $c['tel'],
		'email'        => $c['email'],
		'priceRange'   => '€€',
		'foundingDate' => $c['fondation'],
		'address'      => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $c['rue'],
			'postalCode'      => $c['cp'],
			'addressLocality' => $c['ville'],
			'addressRegion'   => 'Île-de-France',
			'addressCountry'  => 'FR',
		),
		'areaServed'   => array(
			array( '@type' => 'Country', 'name' => 'France' ),
			array( '@type' => 'AdministrativeArea', 'name' => 'Île-de-France' ),
			array( '@type' => 'Place', 'name' => 'Europe' ),
		),
		'founder'      => array( '@id' => aurea_id( 'fondateur' ) ),
		'knowsAbout'   => array( 'Création de site vitrine', 'Création de site e-commerce', 'WordPress', 'WooCommerce', 'Référencement naturel', 'Identité visuelle', 'Motion design' ),
		'sameAs'       => array_values( array_filter( array( $c['linkedin'], $c['google'] ) ) ),
	);
	if ( $c['siren'] ) {
		$agence['identifier'] = array( '@type' => 'PropertyValue', 'propertyID' => 'SIREN', 'value' => str_replace( ' ', '', $c['siren'] ) );
	}
	return $agence;
}

function aurea_schema_fondateur() {
	$c = aurea_coordonnees();
	return array(
		'@type'    => 'Person',
		'@id'      => aurea_id( 'fondateur' ),
		'name'     => $c['fondateur'],
		'jobTitle' => 'Fondateur, développeur et designer web',
		'image'    => AUREA_URI . '/assets/alexandre-coury.webp',
		'worksFor' => array( '@id' => aurea_id( 'organization' ) ),
		'sameAs'   => array_values( array_filter( array( $c['linkedin_moi'] ) ) ),
	);
}

/** Fil d'Ariane de la page affichée : [[nom, url], …] (la dernière étape est la page elle même). */
function aurea_etapes_ariane() {
	if ( is_front_page() ) {
		return array();
	}
	if ( is_page() ) {
		$info = aurea_info_page();
		if ( $info && ! empty( $info['fil'] ) ) {
			return array_map( function ( $e ) {
				return array( $e[0], $e[1] ? home_url( $e[1] ) : get_permalink() );
			}, $info['fil'] );
		}
		return array( array( get_the_title(), get_permalink() ) );
	}
	if ( is_singular( 'post' ) ) {
		return array( array( 'Conseils', aurea_url( 'conseils' ) ), array( get_the_title(), get_permalink() ) );
	}
	if ( is_singular( 'aurea_realisation' ) ) {
		return array( array( 'Réalisations', aurea_url( 'realisations' ) ), array( get_the_title(), get_permalink() ) );
	}
	if ( is_post_type_archive( 'aurea_realisation' ) ) {
		return array( array( 'Réalisations', aurea_url( 'realisations' ) ) );
	}
	if ( is_home() ) {
		return array( array( 'Conseils', aurea_url( 'conseils' ) ) );
	}
	if ( is_category() || is_tag() ) {
		return array( array( 'Conseils', aurea_url( 'conseils' ) ), array( single_term_title( '', false ), get_term_link( get_queried_object() ) ) );
	}
	return array();
}

function aurea_url_courante() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_post_type_archive( 'aurea_realisation' ) ) {
		return aurea_url( 'realisations' );
	}
	if ( is_home() ) {
		return aurea_url( 'conseils' );
	}
	if ( is_category() || is_tag() ) {
		return get_term_link( get_queried_object() );
	}
	return home_url( '/' );
}

/** Données propres à la page affichée (ajoutées au graphe de Rank Math ou au graphe du thème). */
function aurea_schemas_page() {
	$url  = aurea_url_courante();
	$sort = array();

	$etapes = aurea_etapes_ariane();
	if ( $etapes ) {
		$elements = array( array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => home_url( '/' ) ) );
		foreach ( $etapes as $i => $e ) {
			$elements[] = array( '@type' => 'ListItem', 'position' => $i + 2, 'name' => wp_strip_all_tags( $e[0] ), 'item' => $e[1] );
		}
		$sort['breadcrumb'] = array( '@type' => 'BreadcrumbList', '@id' => $url . '#ariane', 'itemListElement' => $elements );
	}

	$info = is_page() ? aurea_info_page() : null;
	if ( $info && in_array( $info['type'], array( 'service', 'local' ), true ) ) {
		$local   = 'local' === $info['type'];
		$service = array(
			'@type'       => 'Service',
			'@id'         => $url . '#service',
			'name'        => $info['service'],
			'serviceType' => $info['service'],
			'provider'    => array( '@id' => aurea_id( 'organization' ) ),
			'url'         => $url,
			'areaServed'  => $local
				? array( array( '@type' => 'AdministrativeArea', 'name' => false !== strpos( $url, 'hauts-de-seine' ) ? 'Hauts-de-Seine' : 'Paris' ), array( '@type' => 'AdministrativeArea', 'name' => 'Île-de-France' ) )
				: array( array( '@type' => 'Country', 'name' => 'France' ), array( '@type' => 'Place', 'name' => 'Europe' ) ),
		);
		if ( ! empty( $info['prix'] ) ) {
			$service['offers'] = array(
				'@type'              => 'Offer',
				'priceCurrency'      => 'EUR',
				'priceSpecification' => array( '@type' => 'PriceSpecification', 'minPrice' => (int) $info['prix'], 'priceCurrency' => 'EUR', 'valueAddedTaxIncluded' => false ),
			);
		}
		$sort['service'] = $service;
	}

	if ( is_singular( 'aurea_realisation' ) && function_exists( 'aurea_realisation' ) ) {
		$r    = aurea_realisation( get_queried_object() );
		$cree = array(
			'@type'   => 'CreativeWork',
			'@id'     => $url . '#realisation',
			'name'    => 'Site ' . $r['titre'],
			'creator' => array( '@id' => aurea_id( 'organization' ) ),
			'genre'   => $r['type']['nom'],
		);
		if ( $r['url'] ) {
			$cree['about'] = array( '@type' => 'Organization', 'name' => $r['titre'], 'url' => $r['url'] );
			$cree['url']   = $r['url'];
		}
		if ( $r['image_url'] ) {
			$cree['image'] = $r['image_url'];
		}
		if ( $r['accroche'] ) {
			$cree['description'] = $r['accroche'];
		}
		$sort['realisation'] = $cree;
	}

	if ( is_post_type_archive( 'aurea_realisation' ) && function_exists( 'aurea_realisations' ) ) {
		$items = array();
		foreach ( aurea_realisations() as $i => $r ) {
			$items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $r['titre'], 'url' => aurea_a_etude( $r ) || ! $r['url'] ? $r['fiche'] : $r['url'] );
		}
		$sort['liste'] = array( '@type' => 'ItemList', '@id' => $url . '#liste', 'itemListElement' => $items );
	}
	return $sort;
}

/** Type de la page (WebPage, AboutPage, ContactPage, CollectionPage). */
function aurea_type_page() {
	$info = is_page() ? aurea_info_page() : null;
	$type = $info['type'] ?? '';
	if ( 'apropos' === $type ) {
		return 'AboutPage';
	}
	if ( 'contact' === $type ) {
		return 'ContactPage';
	}
	if ( is_post_type_archive( 'aurea_realisation' ) || is_home() || is_category() ) {
		return 'CollectionPage';
	}
	return 'WebPage';
}

/* ------------------------------------------------------------------ */
/* Avec Rank Math : on complète son graphe                             */
/* ------------------------------------------------------------------ */

/** Un schéma a-t-il été choisi à la main dans Rank Math (onglet « Schema ») pour cette page ? */
function aurea_schema_choisi( $post_id ) {
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
	$agence   = aurea_schema_agence();
	$fusionne = false;
	$page_cle = null;
	foreach ( $data as $cle => $entite ) {
		if ( ! is_array( $entite ) || empty( $entite['@id'] ) ) {
			continue;
		}
		if ( '#organization' === substr( $entite['@id'], -13 ) ) {
			if ( isset( $entite['logo'] ) ) {
				unset( $agence['logo'] ); // le logo choisi dans Rank Math prime
			}
			$data[ $cle ] = array_merge( $entite, $agence, array( '@id' => $entite['@id'] ) );
			$fusionne     = true;
		}
		if ( '#webpage' === substr( $entite['@id'], -8 ) ) {
			$page_cle = $cle;
		}
	}
	if ( ! $fusionne ) {
		$data['aurea_agence'] = $agence;
	}
	$data['aurea_fondateur'] = aurea_schema_fondateur();

	// Pages : Rank Math ajoute par défaut un « Article », qui ne décrit pas une page service.
	$choisi = is_singular() && aurea_schema_choisi( get_queried_object_id() );
	if ( is_singular( array( 'page', 'aurea_realisation' ) ) && ! $choisi ) {
		foreach ( $data as $cle => $entite ) {
			$types = is_array( $entite ) ? (array) ( $entite['@type'] ?? array() ) : array();
			if ( array_intersect( $types, array( 'Article', 'BlogPosting', 'NewsArticle' ) ) ) {
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
	$propres = aurea_schemas_page();
	foreach ( $propres as $cle => $entite ) {
		if ( 'breadcrumb' === $cle && $deja_ariane ) {
			continue;
		}
		$data[ 'aurea_' . $cle ] = $entite;
	}
	// Sans schéma de page choisi, Rank Math ne publie ni WebSite ni WebPage : le thème les ajoute,
	// pour relier la page à son service, à son fil d'Ariane et au site.
	if ( null === $page_cle && ( is_singular() || is_post_type_archive( 'aurea_realisation' ) || is_home() || is_category() ) ) {
		$a_site = false;
		foreach ( $data as $entite ) {
			if ( is_array( $entite ) && '#website' === substr( (string) ( $entite['@id'] ?? '' ), -8 ) ) {
				$a_site = true;
			}
		}
		if ( ! $a_site ) {
			$data['aurea_site'] = array( '@type' => 'WebSite', '@id' => aurea_id( 'website' ), 'url' => home_url( '/' ), 'name' => 'Aurea Media', 'inLanguage' => 'fr-FR', 'publisher' => array( '@id' => aurea_id( 'organization' ) ) );
		}
		$url  = aurea_url_courante();
		$desc = aurea_description_seo();
		$data['aurea_page'] = array_filter( array(
			'@type'        => 'WebPage',
			'@id'          => $url . '#webpage',
			'url'          => $url,
			'name'         => wp_get_document_title(),
			'description'  => $desc,
			'inLanguage'   => 'fr-FR',
			'isPartOf'     => array( '@id' => aurea_id( 'website' ) ),
			'about'        => array( '@id' => aurea_id( 'organization' ) ),
			'dateModified' => is_singular() ? get_the_modified_date( 'c' ) : '',
		) );
		$page_cle = 'aurea_page';
	}
	if ( null !== $page_cle ) {
		$data[ $page_cle ]['@type'] = aurea_type_page();
		if ( ! $deja_ariane && isset( $propres['breadcrumb'] ) ) {
			$data[ $page_cle ]['breadcrumb'] = array( '@id' => $propres['breadcrumb']['@id'] );
		}
		foreach ( array( 'service', 'realisation', 'liste' ) as $principal ) {
			if ( isset( $propres[ $principal ] ) ) {
				$data[ $page_cle ]['mainEntity'] = array( '@id' => $propres[ $principal ]['@id'] );
			}
		}
		if ( 'AboutPage' === $data[ $page_cle ]['@type'] ) {
			$data[ $page_cle ]['mainEntity'] = array( '@id' => aurea_id( 'fondateur' ) );
		}
	}
	return $data;
}, 99 );

/* Fiches de réalisation sans étude de cas : pas d'indexation (contenu trop mince), liens suivis. */
function aurea_fiche_mince() {
	return is_singular( 'aurea_realisation' ) && '' === trim( wp_strip_all_tags( (string) get_post_field( 'post_content', get_queried_object_id() ) ) );
}
add_filter( 'rank_math/frontend/robots', function ( $robots ) {
	if ( aurea_fiche_mince() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
} );

/* Plan du site Rank Math : sans les fiches de réalisation non indexées (signal cohérent pour Google). */
add_filter( 'rank_math/sitemap/entry', function ( $url, $type, $objet ) {
	// Rank Math transmet une ligne brute de la base (pas toujours un WP_Post) : on relit l'article par son ID
	$id   = is_object( $objet ) ? (int) ( $objet->ID ?? 0 ) : 0;
	$post = $id ? get_post( $id ) : null;
	if ( $post && 'aurea_realisation' === $post->post_type && '' === trim( wp_strip_all_tags( (string) $post->post_content ) ) ) {
		return false;
	}
	return $url;
}, 10, 3 );

/* Une fois par version : cache du plan du site Rank Math vidé, pour appliquer le filtre ci dessus. */
add_action( 'init', function () {
	if ( get_option( 'aurea_sitemap_version' ) === AUREA_VERSION || ! is_callable( array( '\\RankMath\\Sitemap\\Cache', 'invalidate_storage' ) ) ) {
		return;
	}
	update_option( 'aurea_sitemap_version', AUREA_VERSION );
	\RankMath\Sitemap\Cache::invalidate_storage();
}, 100 );

/* ------------------------------------------------------------------ */
/* Sans Rank Math : le thème prend le relais                           */
/* ------------------------------------------------------------------ */

function aurea_titre_seo() {
	if ( is_singular() ) {
		$t = (string) get_post_meta( get_queried_object_id(), 'rank_math_title', true );
		if ( $t ) {
			return wp_strip_all_tags( str_replace( array( '%sep%', '%sitename%', '%title%' ), array( '|', get_bloginfo( 'name' ), get_the_title() ), $t ) );
		}
	}
	$pages = aurea_pages();
	if ( is_post_type_archive( 'aurea_realisation' ) ) {
		return $pages['realisations']['seo_titre'] ?? '';
	}
	if ( is_home() ) {
		return $pages['conseils']['seo_titre'] ?? '';
	}
	return '';
}

function aurea_description_seo() {
	if ( is_singular() ) {
		$d = (string) get_post_meta( get_queried_object_id(), 'rank_math_description', true );
		return $d ?: wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 28, '…' );
	}
	$pages = aurea_pages();
	if ( is_post_type_archive( 'aurea_realisation' ) ) {
		return $pages['realisations']['seo_description'] ?? '';
	}
	if ( is_home() ) {
		return $pages['conseils']['seo_description'] ?? '';
	}
	return get_bloginfo( 'description' );
}

add_filter( 'pre_get_document_title', function ( $titre ) {
	return aurea_rank_math_actif() ? $titre : ( aurea_titre_seo() ?: $titre );
}, 20 );
add_filter( 'document_title_separator', function ( $sep ) {
	return aurea_rank_math_actif() ? $sep : '|';
} );

add_action( 'wp_head', function () {
	if ( aurea_rank_math_actif() ) {
		return;
	}
	$titre = wp_get_document_title();
	$desc  = aurea_description_seo();
	$url   = aurea_url_courante();
	$image = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'full' ) : AUREA_URI . '/assets/og-aurea-media.jpg';
	echo "\n<!-- SEO du thème Aurea (remplacé automatiquement par Rank Math dès son activation) -->\n";
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( ! is_singular() ) {
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n"; // WordPress l'ajoute déjà sur les pages et articles
	}
	$og = array(
		'og:locale'      => 'fr_FR',
		'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
		'og:site_name'   => 'Aurea Media',
		'og:title'       => $titre,
		'og:description' => $desc,
		'og:url'         => $url,
		'og:image'       => $image,
	);
	foreach ( array_filter( $og ) as $p => $v ) {
		echo '<meta property="' . esc_attr( $p ) . '" content="' . esc_attr( $v ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	$graphe   = array( aurea_schema_agence(), aurea_schema_fondateur() );
	$graphe[] = array( '@type' => 'WebSite', '@id' => aurea_id( 'website' ), 'url' => home_url( '/' ), 'name' => 'Aurea Media', 'inLanguage' => 'fr-FR', 'publisher' => array( '@id' => aurea_id( 'organization' ) ) );
	$propres  = aurea_schemas_page();
	$page     = array( '@type' => aurea_type_page(), '@id' => $url . '#webpage', 'url' => $url, 'name' => $titre, 'inLanguage' => 'fr-FR', 'isPartOf' => array( '@id' => aurea_id( 'website' ) ) );
	if ( $desc ) {
		$page['description'] = $desc;
	}
	if ( isset( $propres['breadcrumb'] ) ) {
		$page['breadcrumb'] = array( '@id' => $propres['breadcrumb']['@id'] );
	}
	foreach ( array( 'service', 'realisation', 'liste' ) as $principal ) {
		if ( isset( $propres[ $principal ] ) ) {
			$page['mainEntity'] = array( '@id' => $propres[ $principal ]['@id'] );
		}
	}
	if ( is_singular( 'post' ) ) {
		$propres['article'] = array(
			'@type'            => 'BlogPosting',
			'@id'              => $url . '#article',
			'headline'         => get_the_title(),
			'description'      => $desc,
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'author'           => array( '@id' => aurea_id( 'fondateur' ) ),
			'publisher'        => array( '@id' => aurea_id( 'organization' ) ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'image'            => $image,
			'inLanguage'       => 'fr-FR',
		);
	}
	$graphe[] = $page;
	foreach ( $propres as $entite ) {
		$graphe[] = $entite;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graphe ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}, 2 );

add_filter( 'wp_robots', function ( $robots ) {
	if ( aurea_rank_math_actif() ) {
		return $robots;
	}
	if ( is_search() || is_404() || aurea_fiche_mince() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	} else {
		$robots['max-image-preview'] = 'large';
	}
	return $robots;
} );

/* ------------------------------------------------------------------ */
/* robots.txt et llms.txt                                              */
/* ------------------------------------------------------------------ */

/**
 * robots.txt virtuel de WordPress : les robots de citation des moteurs IA sont autorisés
 * explicitement. Le contenu saisi dans Rank Math (Réglages généraux → Modifier robots.txt) reste prioritaire.
 */
add_filter( 'robots_txt', function ( $sortie, $public ) {
	if ( ! $public || false !== stripos( $sortie, 'OAI-SearchBot' ) ) {
		return $sortie;
	}
	$sortie .= "\n# Moteurs de réponse IA : citations dans ChatGPT Search, Claude et Perplexity\n";
	foreach ( array( 'OAI-SearchBot', 'Claude-SearchBot', 'PerplexityBot' ) as $robot ) {
		$sortie .= "User-agent: $robot\nAllow: /\n\n";
	}
	if ( false === stripos( $sortie, 'Sitemap:' ) ) {
		$sortie .= 'Sitemap: ' . home_url( aurea_rank_math_actif() ? '/sitemap_index.xml' : '/wp-sitemap.xml' ) . "\n";
	}
	return $sortie;
}, 99, 2 );

/** /llms.txt : présentation de l'agence pour les moteurs de réponse, tenue à jour avec le site. */
function aurea_llms_txt() {
	$c      = aurea_coordonnees();
	$pages  = aurea_pages();
	$avis   = function_exists( 'aurea_avis_resume' ) ? aurea_avis_resume() : null;
	$nb     = function_exists( 'aurea_realisations' ) ? count( aurea_realisations() ) : 0;
	$lien   = function ( $slug, $texte ) use ( $pages ) {
		$url = aurea_url( $slug );
		$d   = $pages[ $slug ]['seo_description'] ?? '';
		return "- [$texte]($url)" . ( $d ? " : $d" : '' ) . "\n";
	};
	$t  = "# Aurea Media\n\n";
	$t .= '> Agence web indépendante fondée en ' . $c['fondation'] . ' à ' . $c['ville'] . ' par ' . $c['fondateur'] . '. Création de sites vitrines (dès 1 000 € HT) et de sites e-commerce WooCommerce (dès 1 650 € HT) sur mesure, identité visuelle, motion design et présentations, pour les indépendants, TPE et PME en France et en Europe. Mise en ligne en 4 semaines en moyenne.';
	if ( $avis ) {
		$t .= ' Note Google : ' . $avis['note_txt'] . ' sur 5 (' . $avis['total'] . ' avis).';
	}
	$t .= "\n\n## Services\n";
	foreach ( $pages as $slug => $p ) {
		if ( 'service' === ( $p['type'] ?? '' ) && 0 !== strpos( $slug, 'site-internet-' ) ) {
			$t .= $lien( $slug, $p['titre'] );
		}
	}
	$t .= "\n## Secteurs et zones\n";
	foreach ( $pages as $slug => $p ) {
		if ( 0 === strpos( $slug, 'site-internet-' ) || 'local' === ( $p['type'] ?? '' ) ) {
			$t .= $lien( $slug, $p['titre'] );
		}
	}
	$t .= "\n## Preuves\n";
	$t .= '- [Réalisations](' . aurea_url( 'realisations' ) . ')' . ( $nb ? " : $nb sites présentés, avec leur secteur et leur lien." : '' ) . "\n";
	$t .= $lien( 'tarifs', 'Tarifs' ) . $lien( 'agence', 'L’agence' );
	$articles = get_posts( array( 'numberposts' => 15, 'post_status' => 'publish' ) );
	if ( $articles ) {
		$t .= "\n## Guides\n";
		foreach ( $articles as $a ) {
			$t .= '- [' . get_the_title( $a ) . '](' . get_permalink( $a ) . ")\n";
		}
	}
	$t .= "\n## Contact\n- Téléphone : " . $c['tel_inter'] . "\n- E-mail : " . $c['email'] . "\n- Adresse : " . $c['rue'] . ', ' . $c['cp'] . ' ' . $c['ville'] . "\n- [Demander un devis](" . aurea_url( 'contact' ) . ") : réponse personnelle sous 24 heures.\n";
	return $t;
}

/* Servi avant tout le reste (y compris le llms.txt automatique de Rank Math, moins complet). */
add_action( 'init', function () {
	$chemin = wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$base   = rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base . '/llms.txt' !== $chemin || ! get_option( 'blog_public' ) ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	echo aurea_llms_txt(); // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}, 0 );

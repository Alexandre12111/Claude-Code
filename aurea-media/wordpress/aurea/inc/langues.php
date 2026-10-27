<?php
/**
 * Version anglaise du site, avec l'extension Polylang.
 *
 * Le français reste la langue principale (adresses sans préfixe) ; l'anglais vit sous /en/.
 * - Les pages anglaises sont produites par wordpress/outils/traduire.py (contenu/en/*.html,
 *   contenu/en/pages.json, parts/en/*.html) et installées automatiquement dès que Polylang est actif
 *   avec l'anglais : chaque page anglaise est reliée à sa page française (balises hreflang de Polylang).
 * - Les réalisations (Aurea Studio), le blog et les études de cas restent en français : ils ne sont pas
 *   traduits par Polylang, et les cartes de projets s'affichent en anglais sur les pages anglaises.
 * - Sans Polylang, rien ne change : le site reste entièrement en français.
 */

defined( 'ABSPATH' ) || exit;

function aurea_polylang() {
	return function_exists( 'pll_current_language' ) && function_exists( 'pll_set_post_language' ) && function_exists( 'pll_languages_list' );
}

/** Polylang est actif et l'anglais a été ajouté (Langues → Langues). */
function aurea_en_dispo() {
	return aurea_polylang() && in_array( 'en', (array) pll_languages_list( array( 'fields' => 'slug' ) ), true );
}

/** Langue du contenu en cours : 'fr' ou 'en'. Dans l'administration, toujours le français (sauf langue forcée). */
function aurea_langue() {
	if ( ! empty( $GLOBALS['aurea_langue_forcee'] ) ) {
		return $GLOBALS['aurea_langue_forcee'];
	}
	if ( is_admin() || ! aurea_polylang() ) {
		return 'fr';
	}
	return 'en' === pll_current_language( 'slug' ) ? 'en' : 'fr';
}

/** Exécute $fn dans une langue donnée (installation des pages anglaises). */
function aurea_dans_langue( $langue, $fn ) {
	$avant                         = $GLOBALS['aurea_langue_forcee'] ?? '';
	$GLOBALS['aurea_langue_forcee'] = $langue;
	try {
		return $fn();
	} finally {
		$GLOBALS['aurea_langue_forcee'] = $avant;
	}
}

/** Code de langue pour les données structurées et les balises de partage. */
function aurea_code_langue( $format = 'schema' ) {
	$en = 'en' === aurea_langue();
	if ( 'og' === $format ) {
		return $en ? 'en_GB' : 'fr_FR';
	}
	return $en ? 'en-GB' : 'fr-FR';
}

/* ------------------------------------------------------------------ */
/* Textes du thème                                                     */
/* ------------------------------------------------------------------ */

/** Texte du thème dans la langue de la page (le français sert de clé). */
function aurea_t( $fr ) {
	static $en = null;
	if ( 'en' !== aurea_langue() ) {
		return $fr;
	}
	if ( null === $en ) {
		$en = array(
			'Accueil'                                     => 'Home',
			'Fil d’Ariane'                                => 'Breadcrumb',
			'Réalisations'                                => 'Portfolio',
			'Voir l’étude de cas'                         => 'View the case study',
			'Voir le site'                                => 'Visit the website',
			'Site %s réalisé par Aurea Media'             => '%s website designed by Aurea Media',
			'Toutes les réalisations'                     => 'View all projects',
			'Découvrez tous nos projets.'                 => 'Discover all our projects.',
			'Et %d autres projets à découvrir.'           => 'And %d more projects to discover.',
			'Afficher les %d %s'                          => 'Show all %d %s',
			'sites vitrines'                              => 'business websites',
			'boutiques en ligne'                          => 'online stores',
			'projets'                                     => 'projects',
			'Ils nous font confiance'                     => 'They trust us',
			'Ils nous ont confié leur image en ligne'     => 'They trusted us with their online presence',
			'%s sur 5'                                    => '%s out of 5',
			'Note moyenne sur %d avis publiés sur notre fiche Google.' => 'Average rating from %d reviews published on our Google Business Profile.',
			'Lire les avis sur Google'                    => 'Read the reviews on Google',
			'%d avis vérifiés'                            => '%d verified reviews',
			'Erreur 404'                                  => 'Error 404',
			'Cette page n’existe <span class="gold">plus</span>.' => 'This page no longer <span class="gold">exists</span>.',
			'Elle a peut être changé d’adresse. Voici les pages les plus consultées.' => 'It may have moved. Here are our most visited pages.',
			'Retour à l’accueil'                          => 'Back to home',
			'Création de site vitrine'                    => 'Custom website design',
			'Contact'                                     => 'Contact',
			'Merci, votre demande est bien envoyée.'      => 'Thank you, your request has been sent.',
			'Merci, votre demande est bien enregistrée.'  => 'Thank you, your request has been received.',
			'Réponse personnelle sous 24h ouvrées.'       => 'Personal reply within 24 working hours.',
			'Il manque une information.'                  => 'Some information is missing.',
			'Renseignez votre nom, une adresse e-mail valide et votre projet.' => 'Please enter your name, a valid email address and your project.',
			'La page a expiré.'                           => 'The page has expired.',
			'Rechargez la page puis renvoyez votre demande.' => 'Reload the page, then send your request again.',
			'Trop de demandes envoyées.'                  => 'Too many requests sent.',
			'Réessayez dans une heure, ou appelez nous directement.' => 'Try again in an hour, or call us directly.',
			'Langue'                                      => 'Language',
			'Avis précédents'                             => 'Previous reviews',
			'Avis suivants'                               => 'Next reviews',
			'Agence web indépendante : création de sites vitrines et e-commerce sur mesure, identité visuelle, motion design et présentations, pour les indépendants, TPE et PME en France et en Europe.' => 'Independent web design agency: custom business and e-commerce websites, brand identity, motion design and presentations for freelancers, small businesses and SMEs in France and across Europe.',
			'Fondateur, développeur et designer web'      => 'Founder, web developer and designer',
			'Création de site e-commerce'                 => 'E-commerce website design',
			'Référencement naturel'                       => 'Search engine optimisation',
			'Identité visuelle'                           => 'Brand identity',
			'Motion design'                               => 'Motion design',
		);
	}
	return $en[ $fr ] ?? $fr;
}

/** Nom d'un type ou d'un secteur de réalisation dans la langue de la page. */
function aurea_nom_terme( $terme ) {
	if ( 'en' !== aurea_langue() || empty( $terme['slug'] ) ) {
		return $terme['nom'] ?? '';
	}
	$noms = array(
		'site-vitrine'   => 'Business website',
		'site-ecommerce' => 'E-commerce website',
		'refonte'        => 'Redesign',
		'identite'       => 'Brand identity',
		'motion-design'  => 'Motion design',
		'maquettes'      => 'Presentation design',
		'avocat'         => 'Law and legal',
		'sante'          => 'Healthcare',
		'architecte'     => 'Architecture and real estate',
		'restaurant'     => 'Restaurants and hospitality',
		'artisan-btp'    => 'Trades and construction',
		'coach'          => 'Coaches and consultants',
		'industrie'      => 'Industry and energy',
		'commerce'       => 'Retail and beauty',
		'evenementiel'   => 'Events and institutions',
		'creatifs'       => 'Photographers and creatives',
	);
	return $noms[ $terme['slug'] ] ?? $terme['nom'];
}

/**
 * Accroche et lieu d'une réalisation en anglais : traduction des textes livrés avec Aurea Studio.
 * Un texte modifié dans Aurea Studio n'a pas de traduction connue : il reste affiché tel quel.
 */
function aurea_real_en( $texte ) {
	if ( 'en' !== aurea_langue() ) {
		return $texte;
	}
	$en = array(
		'Négoce d’énergie indépendant : une présence institutionnelle sobre, à la hauteur des marchés européens.' => 'Independent energy trading: a sober institutional presence that lives up to European markets.',
		'Catalogue balistique et tactique pour les acteurs de la Défense et du maintien de l’ordre.' => 'Ballistic and tactical catalogue for defence and law enforcement professionals.',
		'Résidences de luxe sur mesure au sud de Lisbonne, mises en scène comme un magazine.' => 'Bespoke luxury residences south of Lisbon, showcased like a magazine.',
		'Une galerie cinématographique pour un photographe de mariage issu de la mode.' => 'A cinematic gallery for a wedding photographer with a fashion background.',
		'Architecture claire pour présenter les filiales d’un groupe de la transition énergétique.' => 'A clear structure presenting the subsidiaries of an energy transition group.',
		'Boutique d’extensions et de soins capillaires, pensée pour l’achat sur mobile.' => 'An online store for hair extensions and hair care, designed for mobile shopping.',
		'Entreprise du bâtiment : construction, rénovation, isolation et extensions de maisons.' => 'Building company: construction, renovation, insulation and house extensions.',
		'Réseau de centres médicaux pluridisciplinaires, pour un accès rapide et simplifié aux soins.' => 'A network of multidisciplinary medical centres, for fast and simple access to care.',
		'Salon européen de l’innovation duale et de la souveraineté technologique.' => 'European trade show for dual-use innovation and technological sovereignty.',
		'Propriété de charme pour 14 personnes, avec réservation par espace et galerie immersive.' => 'A charming estate for 14 guests, with booking by space and an immersive gallery.',
		'Salon de barbier traditionnel avec prise de rendez vous en ligne.' => 'Traditional barber shop with online appointment booking.',
		'Avocate spécialisée en droit de la famille, des personnes et de leur patrimoine.' => 'Attorney specialising in family law, personal law and estate matters.',
		'Isolation industrielle : calorifugeage, acoustique, protection incendie et échafaudages.' => 'Industrial insulation: thermal lagging, acoustics, fire protection and scaffolding.',
		'Travaux industriels sur mesure en soudure, tuyauterie et mécanique.' => 'Custom industrial works in welding, piping and mechanical engineering.',
		'Cabinet indépendant pour les transformations à fort enjeu, entre politique, économie et géopolitique.' => 'Independent firm for high-stakes transformations, at the crossroads of politics, economics and geopolitics.',
		'Coaching et formation pour dirigeants, managers et particuliers.' => 'Coaching and training for executives, managers and individuals.',
		'Think tank indépendant dédié à la souveraineté nationale et européenne.' => 'Independent think tank dedicated to national and European sovereignty.',
		'Association qui crée des ponts culturels, économiques et scientifiques entre la France et l’Iran.' => 'An association building cultural, economic and scientific bridges between France and Iran.',
		'Cercle privé de réservation d’événements d’exception, dans un cadre confidentiel.' => 'A private club for booking exceptional events in a confidential setting.',
		'Lieu de charme pour séjours, réceptions privées et événements en pleine nature.' => 'A charming venue for stays, private receptions and events in the heart of nature.',
		'Plateforme de campagne municipale pour les élections de Massy 2026.' => 'Municipal campaign platform for the 2026 Massy elections.',
		'Site biographique consacré à l’affaire Tayeb Benabderrahmane.' => 'Biographical website dedicated to the Tayeb Benabderrahmane case.',
		'Vidéaste de mariage à l’approche naturelle, discrète et émotionnelle.' => 'Wedding videographer with a natural, discreet and emotional approach.',
		'Genève'      => 'Geneva',
		'Lisbonne'    => 'Lisbon',
		'Normandie'   => 'Normandy',
		'Suisse'      => 'Switzerland',
		'Belgique'    => 'Belgium',
	);
	return $en[ $texte ] ?? $texte;
}

/* ------------------------------------------------------------------ */
/* Sélecteur de langue (en-tête)                                       */
/* ------------------------------------------------------------------ */

/** FR / EN : vers la traduction de la page affichée, sinon vers l'accueil de l'autre langue. */
function aurea_langues_html() {
	if ( ! aurea_en_dispo() || ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}
	$langues = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
	if ( ! is_array( $langues ) || count( $langues ) < 2 ) {
		return '';
	}
	// Page des réalisations : la version anglaise est une page, la française une archive
	$autre = aurea_traduction_portfolio();
	$liens = '';
	foreach ( array( 'fr', 'en' ) as $code ) {
		if ( empty( $langues[ $code ] ) ) {
			continue;
		}
		$l   = $langues[ $code ];
		$url = $autre[ $code ] ?? $l['url'];
		$liens .= sprintf(
			'<a href="%s" lang="%s" hreflang="%s"%s>%s</a>',
			esc_url( $url ),
			esc_attr( $code ),
			esc_attr( $code ),
			! empty( $l['current_lang'] ) ? ' aria-current="true"' : '',
			esc_html( strtoupper( $code ) )
		);
	}
	return '<div class="langues" role="group" aria-label="' . esc_attr( aurea_t( 'Langue' ) ) . '">' . $liens . '</div>';
}

/* ------------------------------------------------------------------ */
/* Polylang : ce qui est traduit, ce qui ne l'est pas                  */
/* ------------------------------------------------------------------ */

/* Les réalisations et leurs types et secteurs restent communs aux deux langues (aucun doublon à gérer). */
add_filter( 'pll_get_post_types', function ( $types ) {
	unset( $types['aurea_realisation'] );
	return $types;
}, 20 );
add_filter( 'pll_get_taxonomies', function ( $taxos ) {
	unset( $taxos['aurea_type'], $taxos['aurea_secteur'] );
	return $taxos;
}, 20 );

/** Page des réalisations : archive en français, page « web-design-portfolio » en anglais. */
function aurea_traduction_portfolio() {
	$slug = aurea_slug_en( 'realisations' );
	$en   = $slug ? get_page_by_path( $slug ) : null;
	if ( ! $en ) {
		return array();
	}
	$sur_en = is_page( $en->ID );
	$sur_fr = is_post_type_archive( 'aurea_realisation' );
	if ( ! $sur_en && ! $sur_fr ) {
		return array();
	}
	return array(
		'fr' => get_post_type_archive_link( 'aurea_realisation' ),
		'en' => get_permalink( $en ),
	);
}

/* x-default : la version française sert de version par défaut (visiteurs d'autres langues). */
add_filter( 'pll_rel_hreflang_attributes', function ( $hreflangs ) {
	if ( is_array( $hreflangs ) && isset( $hreflangs['fr'] ) && ! isset( $hreflangs['x-default'] ) ) {
		$hreflangs['x-default'] = $hreflangs['fr'];
	}
	return $hreflangs;
} );

/* Balises hreflang de cette paire (Polylang ne les connaît pas : l'archive n'est pas une page traduite). */
add_action( 'wp_head', function () {
	if ( ! aurea_en_dispo() ) {
		return;
	}
	$paire = aurea_traduction_portfolio();
	if ( ! $paire ) {
		return;
	}
	foreach ( array( 'fr' => 'fr', 'en' => 'en', 'x-default' => 'fr' ) as $hreflang => $code ) {
		echo '<link rel="alternate" href="' . esc_url( $paire[ $code ] ) . '" hreflang="' . esc_attr( $hreflang ) . '">' . "\n";
	}
}, 3 );

/* ------------------------------------------------------------------ */
/* Installation des pages anglaises                                    */
/* ------------------------------------------------------------------ */

/**
 * Contenus sans langue (pages, articles, rubriques) : rangés en français, la langue par défaut.
 * Sans langue, Polylang les masquerait des listes du site (blog, plan du site).
 */
function aurea_langue_fr_par_defaut() {
	if ( ! aurea_polylang() || ! function_exists( 'pll_get_post_language' ) ) {
		return;
	}
	$ids = get_posts( array( 'post_type' => array( 'page', 'post' ), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'suppress_filters' => true ) );
	foreach ( $ids as $id ) {
		if ( ! pll_get_post_language( $id ) && 'en' !== get_post_meta( $id, '_aurea_langue', true ) ) {
			pll_set_post_language( $id, 'fr' );
		}
	}
	if ( function_exists( 'pll_get_term_language' ) && function_exists( 'pll_set_term_language' ) ) {
		foreach ( get_terms( array( 'taxonomy' => array( 'category', 'post_tag' ), 'hide_empty' => false, 'fields' => 'ids', 'lang' => '' ) ) as $t ) {
			if ( ! pll_get_term_language( $t ) ) {
				pll_set_term_language( $t, 'fr' );
			}
		}
	}
}

/**
 * Crée les pages anglaises absentes et les relie à leur page française.
 * Les pages anglaises déjà installées sont mises à jour par aurea_maj_pages_auto() (si elles n'ont pas
 * été retouchées à la main), comme les pages françaises.
 *
 * @return string Compte rendu.
 */
function aurea_installer_pages_en() {
	if ( ! aurea_en_dispo() ) {
		return 'Version anglaise : ajoutez d’abord l’anglais dans Langues → Langues (extension Polylang).';
	}
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	aurea_langue_fr_par_defaut();
	kses_remove_filters();
	$crees   = 0;
	$ordre   = 100;
	$occupes = array();
	foreach ( aurea_pages( 'en' ) as $slug => $p ) {
		$page = aurea_page_par_slug( $slug );
		if ( $page && 'en' !== get_post_meta( $page->ID, '_aurea_langue', true ) ) {
			$occupes[] = $slug; // une ancienne page porte déjà ce nom : elle n'est jamais transformée
			continue;
		}
		if ( ! $page ) {
			$contenu = aurea_dans_langue( 'en', function () use ( $p ) {
				return aurea_contenu_livre( preg_replace( '/\.html$/', '', $p['fichier'] ), false );
			} );
			$id = wp_insert_post( wp_slash( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p['titre'],
				'post_name'    => $slug,
				'post_content' => $contenu,
				'post_excerpt' => $p['extrait'] ?? '',
				'menu_order'   => $ordre++,
			) ), true );
			if ( is_wp_error( $id ) ) {
				continue;
			}
			aurea_seo_meta( $id, $p, true );
			update_post_meta( $id, '_aurea_langue', 'en' );
			update_post_meta( $id, '_aurea_version', AUREA_VERSION );
			update_post_meta( $id, '_aurea_contenu_md5', md5( (string) get_post_field( 'post_content', $id ) ) );
			$crees++;
		} else {
			$id = $page->ID;
			aurea_seo_meta( $id, $p, false );
		}
		pll_set_post_language( $id, 'en' );
		// Relie la page anglaise à sa page française (la page des réalisations est une archive : pas de lien)
		$fr = 'accueil' === $p['source'] ? get_post( (int) get_option( 'page_on_front' ) ) : aurea_page_par_slug( $p['source'] );
		if ( $fr && 'realisations' !== $p['source'] && function_exists( 'pll_save_post_translations' ) ) {
			pll_save_post_translations( array( 'fr' => $fr->ID, 'en' => $id ) );
		}
	}
	kses_init_filters();
	// Page vide « Accueil - English » créée par l'assistant de Polylang : doublon mince de l'accueil anglais
	$vides = 0;
	foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 50, 'lang' => 'en', 'meta_query' => array( array( 'key' => '_aurea_langue', 'compare' => 'NOT EXISTS' ) ) ) ) as $vide ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( false !== stripos( $vide->post_title, 'English' ) && strlen( trim( wp_strip_all_tags( $vide->post_content ) ) ) < 40 && ! get_post_meta( $vide->ID, '_elementor_data', true ) ) {
			wp_trash_post( $vide->ID ); // reste récupérable dans la corbeille
			$vides++;
		}
	}
	// Accueil anglais à l'adresse /en/ (et non /en/home-en/) : réglage Polylang « l'URL de la page
	// d'accueil contient le code de langue au lieu du nom de la page »
	$reglages = get_option( 'polylang' );
	if ( is_array( $reglages ) && empty( $reglages['redirect_lang'] ) ) {
		$reglages['redirect_lang'] = 1;
		update_option( 'polylang', $reglages );
		if ( function_exists( 'PLL' ) && PLL() ) { // le cache des langues est recalculé avec le nouveau réglage
			PLL()->options['redirect_lang'] = 1;
			if ( ! empty( PLL()->model->options ) && is_array( PLL()->model->options ) ) {
				PLL()->model->options['redirect_lang'] = 1;
			}
			if ( ! empty( PLL()->links_model->options ) && is_array( PLL()->links_model->options ) ) {
				PLL()->links_model->options['redirect_lang'] = 1;
			}
		}
		flush_rewrite_rules( false );
	}
	aurea_vider_cache_polylang();
	$msg = sprintf( 'Version anglaise : %d pages créées, %d pages au total.', $crees, count( aurea_pages( 'en' ) ) );
	if ( $vides ) {
		$msg .= sprintf( ' %d page anglaise vide (créée par Polylang) mise à la corbeille.', $vides );
	}
	if ( $occupes ) {
		$msg .= ' Adresses déjà prises par d’anciennes pages (à renommer ou supprimer, puis relancer) : ' . implode( ', ', $occupes ) . '.';
	}
	return $msg;
}

/** Polylang garde en cache la page d'accueil de chaque langue : à recalculer après la création des pages. */
function aurea_vider_cache_polylang() {
	if ( ! function_exists( 'PLL' ) || ! PLL() || empty( PLL()->model ) ) {
		return;
	}
	$modele = PLL()->model;
	if ( isset( $modele->languages ) && is_object( $modele->languages ) && method_exists( $modele->languages, 'clean_cache' ) ) {
		$modele->languages->clean_cache();
	} elseif ( method_exists( $modele, 'clean_languages_cache' ) ) {
		$modele->clean_languages_cache();
	}
	delete_transient( 'pll_languages_list' );
}

/* Dès que Polylang et l'anglais sont prêts, puis à chaque version du thème : pages anglaises installées. */
add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || ! current_user_can( 'edit_pages' ) || ! aurea_en_dispo() || ! get_option( 'aurea_installe_le' ) || get_option( 'aurea_en_version' ) === AUREA_VERSION ) {
		return;
	}
	update_option( 'aurea_en_version', AUREA_VERSION );
	set_transient( 'aurea_en_message', aurea_installer_pages_en(), 300 );
}, 20 );

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$m = get_transient( 'aurea_en_message' );
	if ( $m ) {
		delete_transient( 'aurea_en_message' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>Aurea Media</strong> : ' . esc_html( $m ) . '</p></div>';
	} elseif ( aurea_polylang() && ! aurea_en_dispo() ) {
		echo '<div class="notice notice-info"><p><strong>Aurea Media</strong> : pour publier la version anglaise du site, ajoutez le français (langue par défaut) puis l’anglais dans <a href="' . esc_url( admin_url( 'admin.php?page=mlang' ) ) . '">Langues → Langues</a>. Les pages anglaises seront créées automatiquement.</p></div>';
	}
} );

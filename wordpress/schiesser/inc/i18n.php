<?php
/**
 * Site en trois langues avec Polylang : allemand (langue principale), français, anglais.
 *
 * - Sans Polylang, tout reste en allemand.
 * - Avec Polylang, chaque page, produit et article du Tea Room a sa traduction (créée par l'import),
 *   et les textes du thème (boutons, horaires, jours fériés, formulaire…) suivent la langue de la page :
 *   schiesser_t( 'Texte allemand' ) renvoie sa traduction (inc/traductions.php).
 * - Prix, photos et allergènes sont communs aux trois langues : modifiés dans une langue,
 *   ils sont recopiés dans les deux autres.
 */

defined( 'ABSPATH' ) || exit;

/** Adresse de l'accueil dans la langue de la page. */
function schiesser_url_accueil() {
	return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
}

/** Polylang est-il actif ? */
function schiesser_polylang() {
	return function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) && function_exists( 'pll_languages_list' );
}

/** Langues prises en charge par le thème. */
function schiesser_langues_site() {
	return array(
		'de' => array( 'Deutsch', 'de_CH', 'de-CH' ),
		'fr' => array( 'Français', 'fr_FR', 'fr-CH' ),
		'en' => array( 'English', 'en_GB', 'en-GB' ),
	);
}

/** Langue de la page affichée : « de », « fr » ou « en ». */
function schiesser_langue() {
	if ( schiesser_polylang() ) {
		$l = pll_current_language( 'slug' );
		if ( $l ) {
			$l = substr( (string) $l, 0, 2 );
			return isset( schiesser_langues_site()[ $l ] ) ? $l : 'de';
		}
	}
	return 'de';
}

/** Langues réellement créées dans Polylang (slugs « de », « fr », « en »). */
function schiesser_langues_actives() {
	if ( ! schiesser_polylang() ) {
		return array( 'de' );
	}
	$liste = array();
	foreach ( (array) pll_languages_list( array( 'fields' => 'slug' ) ) as $slug ) {
		$l = substr( (string) $slug, 0, 2 );
		if ( isset( schiesser_langues_site()[ $l ] ) ) {
			$liste[ $l ] = (string) $slug;
		}
	}
	return $liste ?: array( 'de' => 'de' );
}

/** Slug Polylang d'une langue du thème (« de » → « de » ou « de-ch » selon la configuration). */
function schiesser_slug_polylang( $l ) {
	$actives = schiesser_langues_actives();
	return $actives[ $l ] ?? $l;
}

/**
 * Traduction d'un texte du thème écrit en allemand.
 *
 * @param string      $de     Texte allemand (clé du dictionnaire).
 * @param string|null $langue Langue voulue (par défaut : celle de la page).
 */
function schiesser_t( $de, $langue = null ) {
	$langue = $langue ?: schiesser_langue();
	if ( 'de' === $langue || '' === (string) $de ) {
		return $de;
	}
	$d = schiesser_dictionnaire( $langue );
	return $d[ $de ] ?? $de;
}

/** Comme schiesser_t(), avec des valeurs à insérer (sprintf). */
function schiesser_tf( $de, ...$valeurs ) {
	return vsprintf( schiesser_t( $de ), $valeurs );
}

/**
 * Libellés de prix et de formats (« ab CHF 8.90 », « 3 Formate », « 9er-Box »…) :
 * enregistrés une seule fois en allemand, affichés dans la langue de la page.
 */
function schiesser_t_libelle( $texte ) {
	$texte = (string) $texte;
	$l     = schiesser_langue();
	if ( 'de' === $l || '' === $texte ) {
		return $texte;
	}
	$direct = schiesser_t( $texte );
	if ( $direct !== $texte ) {
		return $direct;
	}
	$fr = 'fr' === $l;
	// « ab CHF 8.90 »
	$texte = preg_replace( '/^ab\s+/u', $fr ? 'dès ' : 'from ', $texte );
	// « 3 Formate »
	$texte = preg_replace( '/^(\d+) Formate$/u', $fr ? '$1 formats' : '$1 sizes', $texte );
	// « 9er-Box », « 4er », « 9er, Plexi-Box »
	$texte = preg_replace_callback( '/\b(\d+)er(-Box)?\b/u', function ( $m ) use ( $fr ) {
		if ( ! empty( $m[2] ) ) {
			return $fr ? 'boîte de ' . $m[1] : 'box of ' . $m[1];
		}
		return $fr ? 'lot de ' . $m[1] : 'pack of ' . $m[1];
	}, $texte );
	// mots courants des formats
	$mots = schiesser_dictionnaire( $l . '-mots' );
	return $mots ? strtr( $texte, $mots ) : $texte;
}

/** Dictionnaire d'une langue (inc/traductions.php). */
function schiesser_dictionnaire( $langue ) {
	static $cache = array();
	if ( ! isset( $cache[ $langue ] ) ) {
		$tout             = function_exists( 'schiesser_traductions' ) ? schiesser_traductions() : array();
		$cache[ $langue ] = $tout[ $langue ] ?? array();
	}
	return $cache[ $langue ];
}

/* ------------------------------------------------------------------ */
/* Polylang : contenus traduisibles                                    */
/* ------------------------------------------------------------------ */

/* Produits, carte du Tea Room et leurs catégories : traduisibles (sans réglage à faire dans Polylang). */
add_filter( 'pll_get_post_types', function ( $types ) {
	foreach ( array( 'schiesser_produit', 'schiesser_tearoom' ) as $t ) {
		$types[ $t ] = $t;
	}
	return $types;
}, 10, 2 );
add_filter( 'pll_get_taxonomies', function ( $tax ) {
	foreach ( array( 'schiesser_categorie', 'schiesser_rubrique' ) as $t ) {
		$tax[ $t ] = $t;
	}
	return $tax;
}, 10, 2 );

/** Traduction d'un contenu dans la langue de la page (ou l'original s'il n'est pas traduit). */
function schiesser_post_traduit( $id, $langue = null ) {
	if ( ! $id || ! schiesser_polylang() ) {
		return (int) $id;
	}
	$langue = $langue ?: schiesser_langue();
	$t      = pll_get_post( (int) $id, schiesser_slug_polylang( $langue ) );
	return $t ? (int) $t : (int) $id;
}

/** Toutes les versions d'un contenu (lui compris) : identifiants. */
function schiesser_ids_traductions( $id ) {
	$id = (int) $id;
	if ( ! $id || ! schiesser_polylang() || ! function_exists( 'pll_get_post_translations' ) ) {
		return array( $id );
	}
	return array_values( array_unique( array_merge( array( $id ), array_map( 'intval', (array) pll_get_post_translations( $id ) ) ) ) );
}

/** Version allemande d'un contenu (ou lui-même). */
function schiesser_id_principal( $id ) {
	return schiesser_post_traduit( $id, 'de' );
}

/** Arguments de requête limitant l'administration aux contenus allemands (sans doublon par langue). */
function schiesser_args_langue_principale() {
	return schiesser_polylang() ? array( 'lang' => schiesser_slug_polylang( 'de' ) ) : array();
}

/** Contenu allemand (ou encore sans langue) : vrai ; traduction française ou anglaise : faux. */
function schiesser_est_allemand( $id ) {
	if ( ! function_exists( 'pll_get_post_language' ) ) {
		return true;
	}
	$l = (string) pll_get_post_language( (int) $id );
	return '' === $l || 'de' === substr( $l, 0, 2 );
}

/**
 * get_posts() limité aux contenus allemands, y compris ceux qui n'ont pas encore de langue
 * (contenu importé avant l'installation de Polylang).
 */
function schiesser_posts_allemands( $args ) {
	$nombre = (int) ( $args['numberposts'] ?? 5 );
	$args   = array_merge( $args, array( 'lang' => '', 'numberposts' => -1 ) );
	$liste  = array_values( array_filter( get_posts( $args ), function ( $p ) {
		return schiesser_est_allemand( is_object( $p ) ? $p->ID : $p );
	} ) );
	return $nombre > 0 ? array_slice( $liste, 0, $nombre ) : $liste;
}

/* Prix, formats, photos et allergènes : communs à toutes les langues (recopiés à l'enregistrement). */
function schiesser_metas_communes( $type ) {
	if ( 'schiesser_produit' === $type ) {
		return array( '_thumbnail_id', '_s_prix', '_s_unite', '_s_fiche', '_s_badge_style', '_s_allergenes', '_s_regimes', '_s_sans_allergene', '_s_preisliste' );
	}
	if ( 'schiesser_tearoom' === $type ) {
		return array( '_thumbnail_id', '_t_prix', '_t_allergenes', '_t_regimes', '_t_sans_allergene' );
	}
	return array();
}

function schiesser_synchroniser_traductions( $post_id ) {
	static $en_cours = false;
	if ( $en_cours || ! schiesser_polylang() || ! function_exists( 'pll_get_post_translations' ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	$type  = get_post_type( $post_id );
	$cles  = schiesser_metas_communes( $type );
	$trads = (array) pll_get_post_translations( $post_id );
	if ( ! $cles || count( $trads ) < 2 ) {
		return;
	}
	$en_cours = true;
	foreach ( $trads as $autre ) {
		if ( (int) $autre === (int) $post_id ) {
			continue;
		}
		foreach ( $cles as $cle ) {
			$v = get_post_meta( $post_id, $cle, true );
			if ( '' === $v || array() === $v ) {
				delete_post_meta( $autre, $cle );
			} else {
				update_post_meta( $autre, $cle, $v );
			}
		}
		// L'ordre d'affichage suit aussi.
		wp_update_post( array( 'ID' => $autre, 'menu_order' => (int) get_post_field( 'menu_order', $post_id ) ) );
	}
	$en_cours = false;
}
add_action( 'save_post_schiesser_produit', 'schiesser_synchroniser_traductions', 50 );
add_action( 'save_post_schiesser_tearoom', 'schiesser_synchroniser_traductions', 50 );

/* Textes des Réglages maison (présentation, vitrine…) : traduisibles dans Langues → Traductions de Polylang. */
add_action( 'init', function () {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	foreach ( schiesser_reglages_traduisibles() as $cle ) {
		$v = schiesser_reglage( $cle );
		foreach ( (array) $v as $texte ) {
			if ( is_string( $texte ) && '' !== trim( $texte ) ) {
				pll_register_string( 'schiesser_' . $cle, $texte, 'Confiserie Schiesser', 'presentation' === $cle );
			}
		}
	}
}, 20 );

function schiesser_reglages_traduisibles() {
	return array( 'presentation', 'mention', 'horaires_note', 'allergenes_note', 'vitrine' );
}

/**
 * Texte d'un réglage dans la langue de la page : la traduction saisie dans Polylang,
 * sinon celle du thème pour les textes fournis par défaut.
 */
function schiesser_reglage_traduit( $texte ) {
	if ( ! is_string( $texte ) || 'de' === schiesser_langue() ) {
		return $texte;
	}
	if ( function_exists( 'pll__' ) ) {
		$t = pll__( $texte );
		if ( $t !== $texte ) {
			return $t;
		}
	}
	return schiesser_t( $texte );
}

/* ------------------------------------------------------------------ */
/* Rubriques et catégories : un même nom dans les trois langues          */
/* ------------------------------------------------------------------ */

/**
 * Nom allemand (de référence) d'une rubrique ou d'une catégorie, à partir de son nom dans
 * n'importe quelle langue : « Café », « Coffee » et « Kaffee » donnent « Kaffee ».
 *
 * @param string $nom  Nom affiché du terme.
 * @param string $type 'carte' (Tea Room) ou 'boutique'.
 * @return string Nom allemand, ou '' s'il n'est pas connu (rubrique créée à la main).
 */
function schiesser_rubrique_reference( $nom, $type = 'carte' ) {
	static $index = array();
	if ( ! isset( $index[ $type ] ) ) {
		$index[ $type ] = array();
		$dico           = 'carte' === $type
			? ( function_exists( 'schiesser_tr_carte' ) ? schiesser_tr_carte() : array() )
			: ( function_exists( 'schiesser_tr_boutique' ) ? schiesser_tr_boutique() : array() );
		$noms           = 'carte' === $type
			? ( function_exists( 'schiesser_getraenkekarte' ) ? wp_list_pluck( schiesser_getraenkekarte(), 'nom' ) : array() )
			: ( function_exists( 'schiesser_preisliste' ) ? array_keys( schiesser_preisliste() ) : array() );
		foreach ( $noms as $de ) {
			foreach ( array_merge( array( $de ), (array) ( $dico[ $de ] ?? array() ) ) as $variante ) {
				$index[ $type ][ mb_strtolower( trim( $variante ) ) ] = $de;
			}
		}
	}
	return $index[ $type ][ mb_strtolower( trim( html_entity_decode( (string) $nom, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) ) ] ?? '';
}

/** Nom d'une rubrique ou catégorie dans la langue de la page (ou $langue). */
function schiesser_rubrique_nom_langue( $nom, $type = 'carte', $langue = null ) {
	$nom = html_entity_decode( (string) $nom, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$de  = schiesser_rubrique_reference( $nom, $type );
	if ( '' === $de ) {
		return $nom;
	}
	$langue = $langue ?: schiesser_langue();
	if ( 'de' === $langue ) {
		return $de;
	}
	$dico = 'carte' === $type ? schiesser_tr_carte() : schiesser_tr_boutique();
	return schiesser_tr( $dico, $de, $langue );
}

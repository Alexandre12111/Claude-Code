<?php
/**
 * Plugin Name:       Schiesser · Export et import du site
 * Description:       Exporte tout le contenu du site (pages, produits, carte du Tea Room, menus, réglages, médias) dans un fichier JSON, puis réimporte un fichier modifié avec un aperçu des changements et un retour en arrière possible.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Confiserie Schiesser
 * License:           GPL-2.0-or-later
 *
 * Écran : Outils → Export Schiesser (administrateurs seulement).
 *
 * Export : un fichier JSON lisible (pages et produits avec leur contenu en blocs, méta, langues
 * Polylang, rubriques, menus, médias sans les fichiers, réglages du thème, de Polylang et de Rank Math),
 * pour tout le site ou une partie (une page dans ses trois langues, avec son aperçu HTML ; la boutique ;
 * la carte du Tea Room ; les réglages). Chaque export contient un mode d'emploi et la liste des blocs,
 * pour que Claude puisse préparer seul un fichier d'import (voir claude-projet-schiesser.md).
 * Ne sont pas exportés : comptes utilisateurs, messages reçus, commandes, et les secrets
 * (clés API, mots de passe), remplacés par « ***masqué*** ».
 *
 * Import : le même format, complet ou réduit aux éléments modifiés. Chaque élément est comparé
 * au site ; seules les différences sont appliquées, après un aperçu. Un élément modifié sur le site
 * depuis l'export est signalé et laissé tel quel, sauf demande contraire. Le dernier import peut
 * être annulé (l'état d'avant est gardé).
 */

defined( 'ABSPATH' ) || exit;

const SCHIESSER_EX_FORMAT     = 'schiesser-export';
const SCHIESSER_EX_ATTENTE    = 'schiesser_export_import_attente';
const SCHIESSER_EX_SAUVEGARDE = 'schiesser_export_import_sauvegarde';
const SCHIESSER_EX_RAPPORT    = 'schiesser_export_import_rapport';
const SCHIESSER_EX_MASQUE     = '***masqué***';

/* ------------------------------------------------------------------ */
/* Ce qui est exporté                                                  */
/* ------------------------------------------------------------------ */

/** Types de contenus exportés : tous, sauf les fichiers techniques et les données personnelles. */
function schiesser_ex_type_exporte( $type ) {
	$exclus = array( 'attachment', 'revision', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_font_family', 'wp_font_face', 'schiesser_message' );
	return ! in_array( $type, $exclus, true ) && ! preg_match( '/order|message|subscri|submission|entr(y|ies)|(^|_)logs?$|flamingo|feedback|inbound/i', $type );
}

/** Méta techniques sans intérêt (verrous d'édition, corbeille…). */
function schiesser_ex_meta_exclue( $cle ) {
	return in_array( $cle, array( '_edit_lock', '_edit_last', '_encloseme', '_pingme', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug', '_wp_old_date', '_wp_attachment_metadata', '_wp_attached_file' ), true );
}

/** Réglages exportés et modifiables par l'import. */
function schiesser_ex_option_exportee( $nom ) {
	if ( in_array( $nom, array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts', 'permalink_structure', 'WPLANG', 'timezone_string', 'date_format', 'time_format', 'start_of_week', 'site_icon', 'sticky_posts', 'polylang' ), true ) ) {
		return true;
	}
	if ( preg_match( '/sauvegarde|_attente$|transient|^schiesser_export_/', $nom ) ) {
		return false;
	}
	return 0 === strpos( $nom, 'schiesser' ) || 0 === strpos( $nom, 'rank-math-options-' );
}

/** Clé d'un secret (clé API, mot de passe, jeton…) : sa valeur n'est jamais exportée. */
function schiesser_ex_cle_secrete( $cle ) {
	return (bool) preg_match( '/(^|[_\-])(cle|key|pass|passwd|password|secret|token|apikey|licen[cs]e)([_\-]|$)/i', (string) $cle );
}

function schiesser_ex_masquer( $v, $cle = '' ) {
	if ( is_object( $v ) ) {
		$v = get_object_vars( $v );
	}
	if ( is_array( $v ) ) {
		foreach ( $v as $k => $x ) {
			$v[ $k ] = schiesser_ex_masquer( $x, $k );
		}
		return $v;
	}
	return ( schiesser_ex_cle_secrete( $cle ) && is_scalar( $v ) && '' !== (string) $v ) ? SCHIESSER_EX_MASQUE : $v;
}

/** À l'import : une valeur masquée garde la valeur actuelle du site. */
function schiesser_ex_demasquer( $nouveau, $actuel ) {
	if ( SCHIESSER_EX_MASQUE === $nouveau ) {
		return $actuel;
	}
	if ( is_array( $nouveau ) ) {
		$actuel = is_object( $actuel ) ? get_object_vars( $actuel ) : ( is_array( $actuel ) ? $actuel : array() );
		foreach ( $nouveau as $k => $x ) {
			$nouveau[ $k ] = schiesser_ex_demasquer( $x, $actuel[ $k ] ?? null );
		}
	}
	return $nouveau;
}

/** Méta d'un contenu ou d'une rubrique : une valeur, ou { "__plusieurs": [...] } si la clé en a plusieurs. */
function schiesser_ex_metas( $brut ) {
	$out = array();
	foreach ( (array) $brut as $cle => $valeurs ) {
		if ( schiesser_ex_meta_exclue( $cle ) ) {
			continue;
		}
		$valeurs     = array_values( array_map( 'maybe_unserialize', (array) $valeurs ) );
		$out[ $cle ] = 1 === count( $valeurs ) ? $valeurs[0] : array( '__plusieurs' => $valeurs );
	}
	ksort( $out );
	return schiesser_ex_masquer( $out );
}

function schiesser_ex_polylang() {
	return function_exists( 'pll_get_post_language' ) && function_exists( 'pll_get_post_translations' );
}

/** Taxonomies propres à Polylang ou techniques, laissées de côté. */
function schiesser_ex_taxonomie_exportee( $tax ) {
	return ! in_array( $tax, array( 'language', 'term_language', 'post_translations', 'term_translations', 'post_format', 'wp_theme', 'wp_template_part_area', 'wp_pattern_category', 'link_category' ), true );
}

/* ------------------------------------------------------------------ */
/* Export                                                              */
/* ------------------------------------------------------------------ */

function schiesser_ex_contenu( WP_Post $p ) {
	$termes = array();
	foreach ( get_object_taxonomies( $p->post_type ) as $tax ) {
		if ( schiesser_ex_taxonomie_exportee( $tax ) ) {
			$t               = get_the_terms( $p, $tax );
			$termes[ $tax ] = is_array( $t ) ? array_map( 'intval', wp_list_pluck( $t, 'term_id' ) ) : array();
		}
	}
	return array(
		'ID'          => $p->ID,
		'type'        => $p->post_type,
		'statut'      => $p->post_status,
		'titre'       => $p->post_title,
		'slug'        => $p->post_name,
		'lien'        => 'publish' === $p->post_status ? get_permalink( $p ) : '',
		'langue'      => schiesser_ex_polylang() ? (string) pll_get_post_language( $p->ID ) : '',
		'traductions' => schiesser_ex_polylang() ? (object) pll_get_post_translations( $p->ID ) : new stdClass(),
		'parent'      => $p->post_parent,
		'ordre'       => $p->menu_order,
		'date'        => $p->post_date,
		'modifie_le'  => $p->post_modified_gmt,
		'extrait'     => $p->post_excerpt,
		'contenu'     => $p->post_content,
		'termes'      => (object) $termes,
		'meta'        => (object) schiesser_ex_metas( get_post_meta( $p->ID ) ),
	);
}

/**
 * Parties du site que l'on peut exporter seules : tout, une page (avec ses traductions),
 * la boutique, la carte du Tea Room, les réglages.
 */
function schiesser_ex_portees() {
	$p = array( 'tout' => 'Tout le site' );
	$q = new WP_Query( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true, 'lang' => '' ) );
	$vues = array();
	foreach ( $q->posts as $page ) {
		$tr = schiesser_ex_polylang() ? pll_get_post_translations( $page->ID ) : array();
		$id = (int) ( $tr['de'] ?? ( $tr ? reset( $tr ) : $page->ID ) ); // une entrée par page, au nom de sa version allemande
		if ( isset( $vues[ $id ] ) ) {
			continue;
		}
		$vues[ $id ] = true;
		$p[ 'page:' . $id ] = 'Page « ' . get_the_title( $id ) . ' »' . ( count( $tr ) > 1 ? ' (' . implode( ', ', array_map( 'strtoupper', array_keys( array_merge( array_intersect_key( array( 'de' => 0, 'fr' => 0, 'en' => 0 ), $tr ), $tr ) ) ) ) . ')' : '' );
	}
	if ( post_type_exists( 'schiesser_produit' ) ) {
		$p['type:schiesser_produit'] = 'Produits de la boutique';
	}
	if ( post_type_exists( 'schiesser_tearoom' ) ) {
		$p['type:schiesser_tearoom'] = 'Carte du Tea Room (produits)';
	}
	$p['reglages'] = 'Réglages du site seulement (charte, CSS personnalisé, coordonnées)';
	return $p;
}

/** Aperçu HTML d'une page tel que le thème l'affiche, allégé (pour écrire du CSS). */
function schiesser_ex_apercu_html( WP_Post $p ) {
	global $post;
	$ancien = $post;
	$post   = $p; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	setup_postdata( $p );
	// Polylang : les textes du thème dans la langue de la page.
	$pll    = function_exists( 'PLL' ) && isset( PLL()->model ) ? PLL() : null;
	$langue = $pll ? $pll->curlang : null;
	if ( $pll && schiesser_ex_polylang() ) {
		$pll->curlang = $pll->model->get_language( pll_get_post_language( $p->ID ) ) ?: $langue;
	}
	$html = (string) apply_filters( 'the_content', $p->post_content );
	if ( $pll ) {
		$pll->curlang = $langue;
	}
	$post = $ancien; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	wp_reset_postdata();
	$html = preg_replace( '#<svg\b[^>]*>.*?</svg>#s', '<svg/>', $html );
	$html = preg_replace( '#\s(srcset|sizes|style|decoding|loading|fetchpriority)="[^"]*"#', '', $html );
	return trim( preg_replace( "/\n\s*\n+/", "\n", $html ) );
}

/** Médias cités par des contenus (images des blocs, photos à la une). */
function schiesser_ex_medias_cites( array $contenus ) {
	$ids = array();
	foreach ( $contenus as $c ) {
		preg_match_all( '/"(?:\w*[iI]mage)?Id":(\d+)/', (string) $c['contenu'], $m );
		$ids = array_merge( $ids, array_map( 'intval', $m[1] ) );
		$meta = (array) $c['meta'];
		if ( ! empty( $meta['_thumbnail_id'] ) ) {
			$ids[] = (int) $meta['_thumbnail_id'];
		}
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/** Les blocs présents dans les contenus exportés : titre, rôle et réglages possibles (avec leur valeur par défaut). */
function schiesser_ex_catalogue_blocs( array $contenus ) {
	$noms = array();
	foreach ( $contenus as $c ) {
		preg_match_all( '#<!-- wp:([a-z0-9-]+/[a-z0-9-]+)#', (string) $c['contenu'], $m );
		$noms = array_merge( $noms, $m[1] );
	}
	$noms = array_unique( $noms );
	sort( $noms );
	$out = array();
	foreach ( $noms as $nom ) {
		$type = WP_Block_Type_Registry::get_instance()->get_registered( $nom );
		if ( ! $type || 0 !== strpos( $nom, 'schiesser/' ) ) {
			continue;
		}
		$reglages = array();
		foreach ( (array) $type->attributes as $cle => $def ) {
			if ( in_array( $cle, array( 'lock', 'metadata', 'className', 'align', 'apercu', 'style' ), true ) ) {
				continue;
			}
			$reglages[ $cle ] = array_key_exists( 'default', $def ) ? $def['default'] : ( $def['type'] ?? '' );
		}
		$out[ $nom ] = array( 'titre' => (string) $type->title, 'role' => (string) $type->description, 'parent' => $type->parent ? implode( ', ', (array) $type->parent ) : '', 'reglages_et_valeurs_par_defaut' => (object) $reglages );
	}
	return (object) $out;
}

/** Mode d'emploi joint à chaque export, pour Claude (ou toute personne) qui prépare un fichier d'import. */
function schiesser_ex_mode_emploi() {
	return array(
		'Ce fichier est un export du site WordPress de la Confiserie Schiesser (thème « Schiesser », fait sur mesure), créé par l’extension « Schiesser · Export et import du site ». Il sert à préparer des changements qui seront réimportés sur le site.',
		'RÉSULTAT ATTENDU : un fichier JSON au même format, avec seulement ce qui change : {"format":"schiesser-export","version":1,"contenus":[...],"options":{...}}. Nom conseillé : schiesser-import-AAAA-MM-JJ.json. Joindre un résumé des changements en français. Ne pas renvoyer les champs en lecture seule : apercu_html, lien, traductions, blocs, mode_emploi_pour_claude.',
		'CONTENUS : pour chaque contenu modifié, garder ID, type, titre, langue et modifie_le TELS QUELS (modifie_le permet à l’import de repérer une page modifiée sur le site depuis l’export : elle est alors laissée telle quelle), puis donner les champs changés : contenu, titre, extrait, slug, statut, ordre, meta, termes. Un champ absent ne change pas.',
		'LANGUES : le site existe en allemand (langue principale, « de »), français (« fr ») et anglais (« en ») ; chaque langue est une page à part, reliée aux autres (champ traductions). Sauf demande contraire, faire le même changement dans les trois langues, en traduisant les textes.',
		'BLOCS : le champ contenu contient des blocs WordPress. Les blocs du thème s’écrivent <!-- wp:schiesser/nom {"réglage":"valeur"} /--> ou, s’ils ont des éléments dedans, <!-- wp:schiesser/nom {...} --> ... <!-- /wp:schiesser/nom -->. Leurs textes sont dans les réglages JSON. La liste « blocs » donne, pour chaque bloc présent, son rôle et ses réglages avec leur valeur par défaut. Modifier les réglages, sans toucher au reste du texte.',
		'ÉCRITURE DES RÉGLAGES : JSON compact (sans espaces), accents tels quels, et dans les valeurs : < devient <, > devient >, & devient &, un guillemet devient ", -- devient -- (comme le fait WordPress). Un réglage absent prend sa valeur par défaut : pour vider un texte (par exemple supprimer un bouton), mettre "" au lieu de retirer la clé.',
		'ASTUCES DU THÈME : masquer une section sans la supprimer : "afficherDu":"2099-12-31T00:00" (retirer la clé pour la réafficher). Fond d’une section : "fond" = papier, clair, alterne, sable ou sombre (vert sapin). Un bouton dont le texte est vide n’apparaît pas. Les numéros de chapitre (réglage numero) ne sont plus affichés par le thème. Liens : adresses complètes du site (voir le champ lien des pages).',
		'IMAGES : un bloc montre une image par imageId (numéro du média), imageUrl et imageAlt. Utiliser un média de la liste « medias ». Une nouvelle photo ne peut pas venir de ce fichier : la personne l’ajoute d’abord dans la Médiathèque, puis donne son numéro ou son nom de fichier, ou refait un export.',
		'APPARENCE SUR TOUT LE SITE (couleur, espacement, élément à cacher partout…) : utiliser le CSS personnalisé, dans options.schiesser_charte.css_perso. Renvoyer {"options":{"schiesser_charte":{"css_perso":"…"}}} avec le CSS existant suivi des ajouts : les autres réglages de la charte ne bougent pas. Les classes à viser se lisent dans apercu_html (page exportée seule). Couleurs de la charte en variables CSS : var(--vert) vert sapin #174633, var(--dark) chocolat #271B12, var(--mint) beige #E4D3B4, var(--paper) crème #F1EADE, var(--paper-deep) sable #E4D8C3, var(--ink) titres, var(--text) texte. Commenter chaque ajout (/* … */) en français.',
		'RÉGLAGES : seuls les réglages dont le nom commence par schiesser, rank-math-options- et quelques réglages WordPress (nom du site, page d’accueil…) sont acceptés. Pour un réglage en tableau, ne donner que les clés à changer : elles sont fusionnées avec l’existant. Ne jamais modifier une valeur ***masqué*** (clé secrète) : elle est toujours conservée.',
		'AUTRES OPÉRATIONS : supprimer un contenu : {"ID":123,"supprimer":true} (il va à la corbeille). Créer un contenu : sans ID, avec type, titre, statut, contenu, langue et traduction_de (ID de la version allemande). Une méta à null est supprimée. Référencement Rank Math : méta rank_math_title, rank_math_description, rank_math_focus_keyword (titre 50 à 60 caractères, description 140 à 160).',
		'LIMITES : ce fichier ne change pas le fonctionnement du thème (nouveau type de bloc, PHP, PDF de la carte du Tea Room, logo). Si la demande l’exige, le dire clairement : il faut une mise à jour du thème par le développeur.',
		'POUR LA PERSONNE QUI IMPORTE : Outils → Export Schiesser → « Importer un fichier modifié » → vérifier l’aperçu → « Appliquer ». En cas de souci : « Annuler le dernier import ». Puis vider le cache (Réglages → WP Super Cache → Supprimer le cache).',
		'AVANT DE RENDRE LE FICHIER : vérifier que le JSON est valide, que chaque bloc modifié s’ouvre et se ferme correctement, et que seuls les réglages voulus changent.',
	);
}

function schiesser_ex_donnees( $portee = 'tout' ) {
	global $wpdb;
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$portees = schiesser_ex_portees();
	$portee  = isset( $portees[ $portee ] ) ? $portee : 'tout';
	$tout    = 'tout' === $portee;

	$langues = array();
	if ( function_exists( 'pll_languages_list' ) ) {
		$slugs   = pll_languages_list( array( 'fields' => 'slug' ) );
		$locales = pll_languages_list( array( 'fields' => 'locale' ) );
		$noms    = pll_languages_list( array( 'fields' => 'name' ) );
		foreach ( (array) $slugs as $i => $slug ) {
			$langues[] = array( 'slug' => $slug, 'locale' => $locales[ $i ] ?? '', 'nom' => $noms[ $i ] ?? '', 'defaut' => function_exists( 'pll_default_language' ) && pll_default_language() === $slug );
		}
	}
	$extensions = array();
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	foreach ( get_plugins() as $fichier => $info ) {
		if ( is_plugin_active( $fichier ) ) {
			$extensions[] = $info['Name'] . ' ' . $info['Version'];
		}
	}
	$theme = wp_get_theme();

	// Pages, produits, carte du Tea Room, menus, blocs réutilisables…
	$args = array(
		'post_type'      => array_values( array_filter( get_post_types(), 'schiesser_ex_type_exporte' ) ),
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => -1,
		'orderby'        => array( 'type' => 'ASC', 'ID' => 'ASC' ),
		'no_found_rows'  => true,
		'lang'           => '', // Polylang : toutes les langues
	);
	if ( 0 === strpos( $portee, 'page:' ) ) {
		$id                = (int) substr( $portee, 5 );
		$args['post_type'] = 'page';
		$args['post__in']  = array_values( schiesser_ex_polylang() ? ( pll_get_post_translations( $id ) ?: array( $id ) ) : array( $id ) );
	} elseif ( 0 === strpos( $portee, 'type:' ) ) {
		$args['post_type'] = substr( $portee, 5 );
	}
	$contenus = 'reglages' === $portee ? array() : array_map( 'schiesser_ex_contenu', ( new WP_Query( $args ) )->posts );
	if ( 0 === strpos( $portee, 'page:' ) ) {
		foreach ( $contenus as $k => $c ) {
			$contenus[ $k ]['apercu_html'] = schiesser_ex_apercu_html( get_post( $c['ID'] ) ); // lecture seule
		}
	}

	// Rubriques, catégories, menus.
	$taxonomies = array();
	$garder     = $tout ? null : array_unique( array_merge( array(), ...array_map( function ( $c ) {
		return array_keys( (array) $c['termes'] );
	}, $contenus ?: array( array( 'termes' => array() ) ) ) ) );
	foreach ( get_taxonomies() as $tax ) {
		if ( ! schiesser_ex_taxonomie_exportee( $tax ) || ( null !== $garder && ! in_array( $tax, $garder, true ) ) || ( 0 === strpos( $portee, 'page:' ) ) ) {
			continue;
		}
		$termes = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'orderby' => 'term_id', 'lang' => '' ) );
		if ( is_wp_error( $termes ) || ! $termes ) {
			continue;
		}
		foreach ( $termes as $t ) {
			$taxonomies[ $tax ][] = array(
				'id'          => $t->term_id,
				'nom'         => $t->name,
				'slug'        => $t->slug,
				'parent'      => $t->parent,
				'description' => $t->description,
				'nombre'      => $t->count,
				'langue'      => function_exists( 'pll_get_term_language' ) ? (string) pll_get_term_language( $t->term_id ) : '',
				'traductions' => function_exists( 'pll_get_term_translations' ) ? (object) pll_get_term_translations( $t->term_id ) : new stdClass(),
				'meta'        => (object) schiesser_ex_metas( get_term_meta( $t->term_id ) ),
			);
		}
	}

	// Médias : les informations, pas les fichiers (pour une page : ceux qu'elle montre).
	$medias = array();
	$margs  = array( 'post_type' => 'attachment', 'post_status' => array( 'inherit', 'private' ), 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true, 'lang' => '' );
	if ( ! $tout ) {
		$margs['post__in'] = schiesser_ex_medias_cites( $contenus ) ?: array( 0 );
	}
	foreach ( ( new WP_Query( $margs ) )->posts as $m ) {
		$info     = wp_get_attachment_metadata( $m->ID );
		$medias[] = array(
			'ID'          => $m->ID,
			'fichier'     => (string) get_post_meta( $m->ID, '_wp_attached_file', true ),
			'url'         => wp_get_attachment_url( $m->ID ),
			'type_mime'   => $m->post_mime_type,
			'largeur'     => (int) ( $info['width'] ?? 0 ),
			'hauteur'     => (int) ( $info['height'] ?? 0 ),
			'titre'       => $m->post_title,
			'alt'         => (string) get_post_meta( $m->ID, '_wp_attachment_image_alt', true ),
			'legende'     => $m->post_excerpt,
			'description' => $m->post_content,
			'parent'      => $m->post_parent,
			'langue'      => schiesser_ex_polylang() ? (string) pll_get_post_language( $m->ID ) : '',
			'meta'        => (object) schiesser_ex_metas( get_post_meta( $m->ID ) ),
		);
	}

	// Réglages (export partiel : la charte, avec le CSS personnalisé, et les réglages de la maison).
	$noms = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'schiesser%' OR option_name LIKE 'rank-math-options-%' ORDER BY option_name" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$noms = $tout || 'reglages' === $portee ? array_merge( array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts', 'permalink_structure', 'WPLANG', 'timezone_string', 'date_format', 'time_format', 'start_of_week', 'site_icon', 'sticky_posts', 'polylang' ), $noms ) : array( 'schiesser_charte', 'schiesser_reglages' );
	$options = array();
	foreach ( array_unique( $noms ) as $nom ) {
		if ( schiesser_ex_option_exportee( $nom ) && false !== get_option( $nom, false ) ) {
			$options[ $nom ] = schiesser_ex_masquer( get_option( $nom ), $nom );
		}
	}
	// Charte jamais enregistrée : ses valeurs d'origine, pour que le CSS personnalisé y figure.
	if ( ! isset( $options['schiesser_charte'] ) && function_exists( 'schiesser_charte' ) ) {
		$options['schiesser_charte'] = schiesser_ex_masquer( schiesser_charte(), 'schiesser_charte' );
	}

	$d = array(
		'format'                  => SCHIESSER_EX_FORMAT,
		'version'                 => 1,
		'portee'                  => $portees[ $portee ],
		'genere_le'               => gmdate( 'Y-m-d H:i:s' ),
		'mode_emploi_pour_claude' => schiesser_ex_mode_emploi(),
		'site'                    => array(
			'nom'        => get_bloginfo( 'name' ),
			'adresse'    => home_url( '/' ),
			'wordpress'  => get_bloginfo( 'version' ),
			'php'        => PHP_VERSION,
			'theme'      => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
			'extensions' => $extensions,
			'langues'    => $langues,
		),
		'options'                 => (object) $options,
		'theme_mods'              => (object) ( $tout ? schiesser_ex_masquer( (array) get_theme_mods() ) : array() ),
		'taxonomies'              => (object) $taxonomies,
		'contenus'                => $contenus,
		'medias'                  => $medias,
		'blocs'                   => schiesser_ex_catalogue_blocs( $contenus ),
	);
	if ( ! $tout ) {
		unset( $d['theme_mods'] );
	}
	return $d;
}

/* ------------------------------------------------------------------ */
/* Import : comparaison avec le site                                   */
/* ------------------------------------------------------------------ */

/** Valeurs comparables : tout en texte, fins de ligne unifiées. */
function schiesser_ex_norm( $v ) {
	if ( is_object( $v ) ) {
		$v = get_object_vars( $v );
	}
	if ( is_array( $v ) ) {
		return array_map( 'schiesser_ex_norm', $v );
	}
	if ( is_bool( $v ) ) {
		return $v ? '1' : '';
	}
	return str_replace( "\r\n", "\n", (string) $v );
}

function schiesser_ex_egal( $a, $b ) {
	return wp_json_encode( schiesser_ex_norm( $a ) ) === wp_json_encode( schiesser_ex_norm( $b ) );
}

/** Valeur actuelle d'une méta, au même format que l'export (null si absente). */
function schiesser_ex_meta_actuelle( $genre, $id, $cle ) {
	$valeurs = array_values( array_map( 'maybe_unserialize', (array) get_metadata( $genre, $id, $cle, false ) ) );
	if ( ! $valeurs ) {
		return null;
	}
	return 1 === count( $valeurs ) ? $valeurs[0] : array( '__plusieurs' => $valeurs );
}

/** Méta à changer : [ clé => nouvelle valeur | null (à supprimer) ], et les lignes de l'aperçu. */
function schiesser_ex_metas_a_changer( $genre, $id, $metas, &$details ) {
	$out = array();
	foreach ( (array) $metas as $cle => $v ) {
		$cle = (string) $cle;
		if ( schiesser_ex_meta_exclue( $cle ) ) {
			continue;
		}
		$actuel = $id ? schiesser_ex_meta_actuelle( $genre, $id, $cle ) : null;
		$v      = schiesser_ex_demasquer( is_object( $v ) ? get_object_vars( $v ) : $v, $actuel );
		if ( null === $v ? null !== $actuel : ( null === $actuel || ! schiesser_ex_egal( $actuel, $v ) ) ) {
			$out[ $cle ] = $v;
			$details[]   = array( 'Méta ' . $cle, $actuel, $v );
		}
	}
	return $out;
}

/** Rubriques : identifiants, ou slugs (résolus au moment d'appliquer). */
function schiesser_ex_ids_termes( $liste, $tax ) {
	$ids = array();
	foreach ( (array) $liste as $t ) {
		if ( is_numeric( $t ) ) {
			$ids[] = (int) $t;
		} else {
			$terme = get_term_by( 'slug', (string) $t, $tax );
			if ( $terme ) {
				$ids[] = (int) $terme->term_id;
			}
		}
	}
	sort( $ids );
	return $ids;
}

function schiesser_ex_libelle( $p ) {
	$type = get_post_type_object( $p->post_type );
	$l    = schiesser_ex_polylang() ? pll_get_post_language( $p->ID ) : '';
	return ( $type ? $type->labels->singular_name : $p->post_type ) . ' « ' . ( $p->post_title ?: '(sans titre)' ) . ' »' . ( $l ? ' ' . strtoupper( $l ) : '' ) . ' #' . $p->ID;
}

/** Lignes de l'aperçu pour un réglage : une par champ changé quand c'est un tableau. */
function schiesser_ex_details_valeur( $avant, $apres ) {
	if ( ! is_array( $avant ) || ! is_array( $apres ) ) {
		return array( array( 'Valeur', $avant, $apres ) );
	}
	$details = array();
	foreach ( array_unique( array_merge( array_keys( $avant ), array_keys( $apres ) ) ) as $k ) {
		if ( ! array_key_exists( $k, $avant ) || ! array_key_exists( $k, $apres ) || ! schiesser_ex_egal( $avant[ $k ], $apres[ $k ] ) ) {
			$details[] = array( (string) $k, $avant[ $k ] ?? null, $apres[ $k ] ?? null );
		}
	}
	return $details ?: array( array( 'Valeur', $avant, $apres ) );
}

/**
 * Opérations à faire pour amener le site à l'état du fichier.
 *
 * @return array{operations: array, erreurs: string[]}
 */
function schiesser_ex_plan( array $d ) {
	$ops     = array();
	$erreurs = array();
	$champs  = array( 'titre' => 'post_title', 'slug' => 'post_name', 'contenu' => 'post_content', 'extrait' => 'post_excerpt', 'statut' => 'post_status', 'parent' => 'post_parent', 'ordre' => 'menu_order' );
	$noms    = array( 'post_title' => 'Titre', 'post_name' => 'Slug', 'post_content' => 'Contenu', 'post_excerpt' => 'Extrait', 'post_status' => 'Statut', 'post_parent' => 'Parent', 'menu_order' => 'Ordre' );

	// Rubriques, catégories, menus.
	foreach ( (array) ( $d['taxonomies'] ?? array() ) as $tax => $termes ) {
		if ( ! taxonomy_exists( $tax ) || ! schiesser_ex_taxonomie_exportee( $tax ) ) {
			$erreurs[] = 'Taxonomie inconnue : ' . $tax;
			continue;
		}
		foreach ( (array) $termes as $t ) {
			$t       = (array) $t;
			$details = array();
			$id      = (int) ( $t['id'] ?? 0 );
			if ( $id ) {
				$actuel = get_term( $id, $tax );
				if ( ! $actuel || is_wp_error( $actuel ) ) {
					$erreurs[] = 'Rubrique introuvable : ' . $tax . ' #' . $id;
					continue;
				}
				$ch = array();
				foreach ( array( 'nom' => 'name', 'slug' => 'slug', 'description' => 'description', 'parent' => 'parent' ) as $k => $champ ) {
					if ( array_key_exists( $k, $t ) && ! schiesser_ex_egal( $actuel->$champ, $t[ $k ] ) ) {
						$ch[ $champ ] = $t[ $k ];
						$details[]    = array( ucfirst( $k ), $actuel->$champ, $t[ $k ] );
					}
				}
				$metas = schiesser_ex_metas_a_changer( 'term', $id, $t['meta'] ?? array(), $details );
				if ( $details ) {
					$ops[] = array( 'genre' => 'terme', 'action' => 'modifier', 'taxonomie' => $tax, 'id' => $id, 'champs' => $ch, 'metas' => $metas, 'objet' => 'Rubrique « ' . $actuel->name . ' » (' . $tax . ') #' . $id, 'details' => $details );
				}
			} elseif ( ! empty( $t['nom'] ) ) {
				$metas = schiesser_ex_metas_a_changer( 'term', 0, $t['meta'] ?? array(), $details );
				$ops[] = array( 'genre' => 'terme', 'action' => 'creer', 'taxonomie' => $tax, 'donnees' => $t, 'metas' => $metas, 'objet' => 'Nouvelle rubrique « ' . $t['nom'] . ' » (' . $tax . ')', 'details' => $details );
			}
		}
	}

	// Pages, produits, plats, menus…
	foreach ( (array) ( $d['contenus'] ?? array() ) as $c ) {
		$c       = (array) $c;
		$id      = (int) ( $c['ID'] ?? 0 );
		$details = array();
		if ( $id ) {
			$p = get_post( $id );
			if ( ! $p || 'trash' === $p->post_status ) {
				$erreurs[] = 'Contenu introuvable : #' . $id . ( isset( $c['titre'] ) ? ' « ' . $c['titre'] . ' »' : '' );
				continue;
			}
			if ( isset( $c['type'] ) && $c['type'] !== $p->post_type ) {
				$erreurs[] = 'Type différent pour #' . $id . ' : ' . $c['type'] . ' dans le fichier, ' . $p->post_type . ' sur le site.';
				continue;
			}
			$conflit = ! empty( $c['modifie_le'] ) && $p->post_modified_gmt > (string) $c['modifie_le'];
			if ( ! empty( $c['supprimer'] ) ) {
				$ops[] = array( 'genre' => 'contenu', 'action' => 'corbeille', 'id' => $id, 'objet' => schiesser_ex_libelle( $p ), 'conflit' => $conflit, 'details' => array() );
				continue;
			}
			$ch = array();
			foreach ( $champs as $k => $champ ) {
				if ( array_key_exists( $k, $c ) && ! schiesser_ex_egal( $p->$champ, $c[ $k ] ) ) {
					$ch[ $champ ] = $c[ $k ];
					$details[]    = array( $noms[ $champ ], $p->$champ, $c[ $k ] );
				}
			}
			$metas  = schiesser_ex_metas_a_changer( 'post', $id, $c['meta'] ?? array(), $details );
			$termes = array();
			foreach ( (array) ( $c['termes'] ?? array() ) as $tax => $liste ) {
				if ( ! taxonomy_exists( $tax ) || ! schiesser_ex_taxonomie_exportee( $tax ) ) {
					continue;
				}
				$avant = wp_get_object_terms( $id, $tax, array( 'fields' => 'ids' ) );
				$avant = is_wp_error( $avant ) ? array() : array_map( 'intval', $avant );
				sort( $avant );
				if ( $avant !== schiesser_ex_ids_termes( $liste, $tax ) ) {
					$termes[ $tax ] = (array) $liste;
					$details[]      = array( 'Rubriques (' . $tax . ')', implode( ', ', $avant ), implode( ', ', (array) $liste ) );
				}
			}
			if ( $details ) {
				$ops[] = array( 'genre' => 'contenu', 'action' => 'modifier', 'id' => $id, 'champs' => $ch, 'metas' => $metas, 'termes' => $termes, 'objet' => schiesser_ex_libelle( $p ), 'conflit' => $conflit, 'details' => $details );
			}
		} elseif ( ! empty( $c['type'] ) ) {
			if ( ! post_type_exists( $c['type'] ) ) {
				$erreurs[] = 'Type de contenu inconnu : ' . $c['type'];
				continue;
			}
			// Même fichier importé deux fois : pas de doublon.
			$existant = get_posts( array( 'post_type' => $c['type'], 'title' => (string) ( $c['titre'] ?? '' ), 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'numberposts' => 5, 'lang' => '' ) );
			foreach ( $existant as $x ) {
				if ( empty( $c['langue'] ) || ! schiesser_ex_polylang() || pll_get_post_language( $x->ID ) === $c['langue'] ) {
					$erreurs[] = 'Déjà sur le site, pas recréé : ' . schiesser_ex_libelle( $x );
					continue 2;
				}
			}
			$metas = schiesser_ex_metas_a_changer( 'post', 0, $c['meta'] ?? array(), $details );
			$ops[] = array( 'genre' => 'contenu', 'action' => 'creer', 'donnees' => $c, 'metas' => $metas, 'objet' => 'Nouveau contenu (' . $c['type'] . ') « ' . ( $c['titre'] ?? '' ) . ' »' . ( ! empty( $c['langue'] ) ? ' ' . strtoupper( $c['langue'] ) : '' ), 'details' => $details );
		}
	}

	// Réglages.
	foreach ( (array) ( $d['options'] ?? array() ) as $nom => $v ) {
		$nom = (string) $nom;
		if ( ! schiesser_ex_option_exportee( $nom ) ) {
			$erreurs[] = 'Réglage non modifiable par l\'import : ' . $nom;
			continue;
		}
		$actuel = get_option( $nom, null );
		$v      = schiesser_ex_demasquer( is_object( $v ) ? json_decode( wp_json_encode( $v ), true ) : $v, $actuel );
		// Réglage en tableau : les clés données sont fusionnées avec l'existant (les autres ne bougent pas).
		if ( is_array( $actuel ) && is_array( $v ) && $v && array_keys( $v ) !== range( 0, count( $v ) - 1 ) ) {
			$v = array_replace( $actuel, $v );
		}
		if ( null === $actuel || ! schiesser_ex_egal( $actuel, $v ) ) {
			$ops[] = array( 'genre' => 'option', 'nom' => $nom, 'valeur' => $v, 'objet' => 'Réglage ' . $nom, 'details' => schiesser_ex_details_valeur( $actuel, $v ) );
		}
	}
	$mods = get_theme_mods();
	foreach ( (array) ( $d['theme_mods'] ?? array() ) as $nom => $v ) {
		$actuel = $mods[ $nom ] ?? null;
		$v      = schiesser_ex_demasquer( is_object( $v ) ? json_decode( wp_json_encode( $v ), true ) : $v, $actuel );
		if ( null === $actuel || ! schiesser_ex_egal( $actuel, $v ) ) {
			$ops[] = array( 'genre' => 'theme_mod', 'nom' => (string) $nom, 'valeur' => $v, 'objet' => 'Personnalisation du thème : ' . $nom, 'details' => schiesser_ex_details_valeur( $actuel, $v ) );
		}
	}

	// Médias : titre, texte alternatif, légende, description.
	foreach ( (array) ( $d['medias'] ?? array() ) as $m ) {
		$m  = (array) $m;
		$id = (int) ( $m['ID'] ?? 0 );
		$p  = $id ? get_post( $id ) : null;
		if ( ! $p || 'attachment' !== $p->post_type ) {
			if ( $id ) {
				$erreurs[] = 'Média introuvable : #' . $id;
			}
			continue;
		}
		$details = array();
		$ch      = array();
		foreach ( array( 'titre' => 'post_title', 'legende' => 'post_excerpt', 'description' => 'post_content' ) as $k => $champ ) {
			if ( array_key_exists( $k, $m ) && ! schiesser_ex_egal( $p->$champ, $m[ $k ] ) ) {
				$ch[ $champ ] = $m[ $k ];
				$details[]    = array( ucfirst( $k ), $p->$champ, $m[ $k ] );
			}
		}
		$meta = (array) ( $m['meta'] ?? array() );
		if ( array_key_exists( 'alt', $m ) ) {
			$meta['_wp_attachment_image_alt'] = $m['alt'];
		}
		$metas = schiesser_ex_metas_a_changer( 'post', $id, $meta, $details );
		if ( $details ) {
			$ops[] = array( 'genre' => 'contenu', 'action' => 'modifier', 'id' => $id, 'champs' => $ch, 'metas' => $metas, 'termes' => array(), 'objet' => 'Média « ' . $p->post_title . ' » #' . $id, 'conflit' => false, 'details' => $details );
		}
	}

	return array( 'operations' => $ops, 'erreurs' => $erreurs );
}

/* ------------------------------------------------------------------ */
/* Import : appliquer et annuler                                       */
/* ------------------------------------------------------------------ */

function schiesser_ex_ecrire_metas( $genre, $id, $metas ) {
	foreach ( (array) $metas as $cle => $v ) {
		if ( null === $v ) {
			delete_metadata( $genre, $id, $cle );
		} elseif ( is_array( $v ) && array_key_exists( '__plusieurs', $v ) ) {
			delete_metadata( $genre, $id, $cle );
			foreach ( (array) $v['__plusieurs'] as $x ) {
				add_metadata( $genre, $id, $cle, wp_slash( $x ) );
			}
		} else {
			update_metadata( $genre, $id, $cle, wp_slash( $v ) );
		}
	}
}

/** État d'avant des méta qui vont changer : [ clé => liste des valeurs ] (liste vide = absente). */
function schiesser_ex_metas_avant( $genre, $id, $metas ) {
	$avant = array();
	foreach ( array_keys( (array) $metas ) as $cle ) {
		$avant[ $cle ] = array_values( array_map( 'maybe_unserialize', (array) get_metadata( $genre, $id, $cle, false ) ) );
	}
	return $avant;
}

function schiesser_ex_restaurer_metas( $genre, $id, $avant ) {
	foreach ( (array) $avant as $cle => $valeurs ) {
		delete_metadata( $genre, $id, $cle );
		foreach ( (array) $valeurs as $v ) {
			add_metadata( $genre, $id, $cle, wp_slash( $v ) );
		}
	}
}

/**
 * Applique le fichier. Les éléments modifiés sur le site depuis l'export sont laissés tels quels,
 * sauf si $forcer. L'état d'avant est gardé pour « Annuler le dernier import ».
 */
function schiesser_ex_appliquer( array $d, $forcer = false ) {
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$plan  = schiesser_ex_plan( $d );
	$bilan = array( 'faits' => 0, 'ignores' => 0, 'erreurs' => $plan['erreurs'] );
	$ops   = array();
	foreach ( $plan['operations'] as $op ) {
		if ( ! empty( $op['conflit'] ) && ! $forcer ) {
			++$bilan['ignores'];
			$bilan['erreurs'][] = 'Laissé tel quel (modifié sur le site depuis l\'export) : ' . $op['objet'];
			continue;
		}
		$ops[] = $op;
	}

	// 1. L'état d'avant, enregistré avant tout changement.
	$s    = array( 'date' => current_time( 'mysql' ), 'nombre' => count( $ops ), 'contenus' => array(), 'corbeille' => array(), 'crees' => array(), 'options' => array(), 'theme_mods' => array(), 'termes' => array(), 'termes_crees' => array() );
	$mods = get_theme_mods();
	foreach ( $ops as $op ) {
		if ( 'contenu' === $op['genre'] && 'modifier' === $op['action'] ) {
			$p      = get_post( $op['id'] );
			$champs = array();
			foreach ( array_keys( $op['champs'] ) as $champ ) {
				$champs[ $champ ] = $p->$champ;
			}
			$termes = array();
			foreach ( array_keys( $op['termes'] ) as $tax ) {
				$termes[ $tax ] = wp_get_object_terms( $op['id'], $tax, array( 'fields' => 'ids' ) );
			}
			$s['contenus'][ $op['id'] ] = array( 'champs' => $champs, 'metas' => schiesser_ex_metas_avant( 'post', $op['id'], $op['metas'] ), 'termes' => $termes );
		} elseif ( 'contenu' === $op['genre'] && 'corbeille' === $op['action'] ) {
			$s['corbeille'][ $op['id'] ] = get_post_status( $op['id'] );
		} elseif ( 'option' === $op['genre'] ) {
			$s['options'][ $op['nom'] ] = array( 'existe' => null !== get_option( $op['nom'], null ), 'valeur' => get_option( $op['nom'] ) );
		} elseif ( 'theme_mod' === $op['genre'] ) {
			$s['theme_mods'][ $op['nom'] ] = array( 'existe' => array_key_exists( $op['nom'], (array) $mods ), 'valeur' => $mods[ $op['nom'] ] ?? null );
		} elseif ( 'terme' === $op['genre'] && 'modifier' === $op['action'] ) {
			$t                          = get_term( $op['id'], $op['taxonomie'] );
			$s['termes'][ $op['id'] ] = array( 'taxonomie' => $op['taxonomie'], 'champs' => array( 'name' => $t->name, 'slug' => $t->slug, 'description' => $t->description, 'parent' => $t->parent ), 'metas' => schiesser_ex_metas_avant( 'term', $op['id'], $op['metas'] ) );
		}
	}
	update_option( SCHIESSER_EX_SAUVEGARDE, $s, false );

	// 2. Les changements (contenus enregistrés tels quels, blocs compris).
	kses_remove_filters();
	foreach ( $ops as $op ) {
		$r = schiesser_ex_operation( $op, $s );
		if ( is_wp_error( $r ) ) {
			$bilan['erreurs'][] = $op['objet'] . ' : ' . $r->get_error_message();
		} else {
			++$bilan['faits'];
		}
	}
	kses_init();
	update_option( SCHIESSER_EX_SAUVEGARDE, $s, false ); // avec les contenus et rubriques créés
	if ( isset( $s['options']['permalink_structure'] ) ) {
		flush_rewrite_rules();
	}
	return $bilan;
}

/** Une opération ; $s reçoit les éléments créés (pour pouvoir annuler). */
function schiesser_ex_operation( array $op, array &$s ) {
	switch ( $op['genre'] . ':' . ( $op['action'] ?? '' ) ) {
		case 'terme:creer':
			$t = $op['donnees'];
			$r = wp_insert_term( (string) $t['nom'], $op['taxonomie'], array( 'slug' => (string) ( $t['slug'] ?? '' ), 'description' => (string) ( $t['description'] ?? '' ), 'parent' => (int) ( $t['parent'] ?? 0 ) ) );
			if ( is_wp_error( $r ) ) {
				return $r;
			}
			$id                  = (int) $r['term_id'];
			$s['termes_crees'][] = array( $id, $op['taxonomie'] );
			schiesser_ex_ecrire_metas( 'term', $id, $op['metas'] );
			if ( ! empty( $t['langue'] ) && function_exists( 'pll_set_term_language' ) ) {
				pll_set_term_language( $id, $t['langue'] );
				if ( ! empty( $t['traduction_de'] ) && function_exists( 'pll_save_term_translations' ) ) {
					$tr                  = pll_get_term_translations( (int) $t['traduction_de'] );
					$tr[ $t['langue'] ] = $id;
					pll_save_term_translations( $tr );
				}
			}
			return true;
		case 'terme:modifier':
			if ( $op['champs'] ) {
				$r = wp_update_term( $op['id'], $op['taxonomie'], wp_slash( $op['champs'] ) );
				if ( is_wp_error( $r ) ) {
					return $r;
				}
			}
			schiesser_ex_ecrire_metas( 'term', $op['id'], $op['metas'] );
			return true;
		case 'contenu:creer':
			$c  = $op['donnees'];
			$id = wp_insert_post( wp_slash( array(
				'post_type'    => $c['type'],
				'post_status'  => $c['statut'] ?? 'draft',
				'post_title'   => (string) ( $c['titre'] ?? '' ),
				'post_name'    => (string) ( $c['slug'] ?? '' ),
				'post_content' => (string) ( $c['contenu'] ?? '' ),
				'post_excerpt' => (string) ( $c['extrait'] ?? '' ),
				'post_parent'  => (int) ( $c['parent'] ?? 0 ),
				'menu_order'   => (int) ( $c['ordre'] ?? 0 ),
			) ), true );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			$s['crees'][] = $id;
			schiesser_ex_ecrire_metas( 'post', $id, $op['metas'] );
			foreach ( (array) ( $c['termes'] ?? array() ) as $tax => $liste ) {
				wp_set_object_terms( $id, schiesser_ex_ids_termes( $liste, $tax ), $tax );
			}
			if ( ! empty( $c['langue'] ) && function_exists( 'pll_set_post_language' ) ) {
				pll_set_post_language( $id, $c['langue'] );
				if ( ! empty( $c['traduction_de'] ) && function_exists( 'pll_save_post_translations' ) ) {
					$tr                  = pll_get_post_translations( (int) $c['traduction_de'] );
					$tr[ $c['langue'] ] = $id;
					pll_save_post_translations( $tr );
				}
			}
			return true;
		case 'contenu:modifier':
			if ( $op['champs'] ) {
				$r = wp_update_post( wp_slash( array( 'ID' => $op['id'] ) + $op['champs'] ), true );
				if ( is_wp_error( $r ) ) {
					return $r;
				}
			}
			schiesser_ex_ecrire_metas( 'post', $op['id'], $op['metas'] );
			foreach ( $op['termes'] as $tax => $liste ) {
				wp_set_object_terms( $op['id'], schiesser_ex_ids_termes( $liste, $tax ), $tax );
			}
			clean_post_cache( $op['id'] );
			return true;
		case 'contenu:corbeille':
			return wp_trash_post( $op['id'] ) ? true : new WP_Error( 'corbeille', 'mise à la corbeille impossible' );
		case 'option:':
			update_option( $op['nom'], $op['valeur'] );
			return true;
		case 'theme_mod:':
			set_theme_mod( $op['nom'], $op['valeur'] );
			return true;
	}
	return new WP_Error( 'inconnu', 'opération inconnue' );
}

/** Remet le site dans l'état d'avant le dernier import. */
function schiesser_ex_annuler() {
	$s = get_option( SCHIESSER_EX_SAUVEGARDE );
	if ( ! is_array( $s ) ) {
		return false;
	}
	kses_remove_filters();
	foreach ( (array) $s['contenus'] as $id => $o ) {
		if ( $o['champs'] ) {
			wp_update_post( wp_slash( array( 'ID' => (int) $id ) + $o['champs'] ) );
		}
		schiesser_ex_restaurer_metas( 'post', (int) $id, $o['metas'] );
		foreach ( (array) $o['termes'] as $tax => $ids ) {
			wp_set_object_terms( (int) $id, array_map( 'intval', (array) $ids ), $tax );
		}
		clean_post_cache( (int) $id );
	}
	foreach ( (array) $s['corbeille'] as $id => $statut ) {
		wp_untrash_post( (int) $id );
		wp_update_post( array( 'ID' => (int) $id, 'post_status' => $statut ) );
	}
	foreach ( (array) $s['crees'] as $id ) {
		wp_trash_post( (int) $id );
	}
	foreach ( (array) $s['options'] as $nom => $o ) {
		$o['existe'] ? update_option( $nom, $o['valeur'] ) : delete_option( $nom );
	}
	foreach ( (array) $s['theme_mods'] as $nom => $o ) {
		$o['existe'] ? set_theme_mod( $nom, $o['valeur'] ) : remove_theme_mod( $nom );
	}
	foreach ( (array) $s['termes'] as $id => $o ) {
		wp_update_term( (int) $id, $o['taxonomie'], wp_slash( $o['champs'] ) );
		schiesser_ex_restaurer_metas( 'term', (int) $id, $o['metas'] );
	}
	foreach ( (array) $s['termes_crees'] as $t ) {
		wp_delete_term( (int) $t[0], $t[1] );
	}
	kses_init();
	if ( isset( $s['options']['permalink_structure'] ) ) {
		flush_rewrite_rules();
	}
	delete_option( SCHIESSER_EX_SAUVEGARDE );
	return true;
}

/* ------------------------------------------------------------------ */
/* Écran Outils → Export Schiesser                                     */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	add_management_page( 'Export et import du site', 'Export Schiesser', 'manage_options', 'schiesser-export', 'schiesser_ex_page' );
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $liens ) {
	array_unshift( $liens, '<a href="' . esc_url( admin_url( 'tools.php?page=schiesser-export' ) ) . '">Ouvrir</a>' );
	return $liens;
} );

function schiesser_ex_url( $args = array() ) {
	return add_query_arg( $args, admin_url( 'tools.php?page=schiesser-export' ) );
}

function schiesser_ex_verifier( $action ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( $action );
}

add_action( 'admin_post_schiesser_ex_exporter', function () {
	schiesser_ex_verifier( 'schiesser_ex_exporter' );
	$portee = sanitize_text_field( wp_unslash( $_POST['portee'] ?? 'tout' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$json   = wp_json_encode( schiesser_ex_donnees( $portee ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE );
	$suffixe = 'tout' === $portee ? '' : '-' . sanitize_title( 0 === strpos( $portee, 'page:' ) ? get_post_field( 'post_name', (int) substr( $portee, 5 ) ) : str_replace( array( 'type:', 'schiesser_' ), '', $portee ) );
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="schiesser-export' . $suffixe . '-' . wp_date( 'Y-m-d-Hi' ) . '.json"' );
	header( 'Content-Length: ' . strlen( $json ) );
	echo $json; // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
} );

add_action( 'admin_post_schiesser_ex_envoyer', function () {
	schiesser_ex_verifier( 'schiesser_ex_envoyer' );
	$f = $_FILES['fichier'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$d = ( $f && UPLOAD_ERR_OK === $f['error'] && is_uploaded_file( $f['tmp_name'] ) ) ? json_decode( (string) file_get_contents( $f['tmp_name'] ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! is_array( $d ) || SCHIESSER_EX_FORMAT !== ( $d['format'] ?? '' ) ) {
		wp_safe_redirect( schiesser_ex_url( array( 'erreur' => 'fichier' ) ) );
		exit;
	}
	update_option( SCHIESSER_EX_ATTENTE, array( 'nom' => sanitize_file_name( $f['name'] ), 'recu' => current_time( 'mysql' ), 'donnees' => $d ), false );
	wp_safe_redirect( schiesser_ex_url() );
	exit;
} );

add_action( 'admin_post_schiesser_ex_appliquer', function () {
	schiesser_ex_verifier( 'schiesser_ex_appliquer' );
	$a = get_option( SCHIESSER_EX_ATTENTE );
	if ( is_array( $a ) ) {
		$bilan = schiesser_ex_appliquer( $a['donnees'], ! empty( $_POST['forcer'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		update_option( SCHIESSER_EX_RAPPORT, array( 'genre' => 'import', 'nom' => $a['nom'] ) + $bilan, false );
		delete_option( SCHIESSER_EX_ATTENTE );
	}
	wp_safe_redirect( schiesser_ex_url() );
	exit;
} );

add_action( 'admin_post_schiesser_ex_abandonner', function () {
	schiesser_ex_verifier( 'schiesser_ex_abandonner' );
	delete_option( SCHIESSER_EX_ATTENTE );
	wp_safe_redirect( schiesser_ex_url() );
	exit;
} );

add_action( 'admin_post_schiesser_ex_annuler', function () {
	schiesser_ex_verifier( 'schiesser_ex_annuler' );
	if ( schiesser_ex_annuler() ) {
		update_option( SCHIESSER_EX_RAPPORT, array( 'genre' => 'annulation' ), false );
	}
	wp_safe_redirect( schiesser_ex_url() );
	exit;
} );

/** Valeur courte pour l'aperçu. */
function schiesser_ex_extrait( $v ) {
	if ( null === $v ) {
		return '(absent)';
	}
	if ( is_array( $v ) || is_object( $v ) ) {
		$v = wp_json_encode( $v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	$v = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $v ) ) );
	return '' === $v ? '(vide)' : ( mb_strlen( $v ) > 160 ? mb_substr( $v, 0, 160 ) . '…' : $v );
}

/** Différences de contenu : seulement les passages changés (un bloc par ligne), avec deux lignes autour. */
function schiesser_ex_diff( $avant, $apres ) {
	$lignes = function ( $t ) {
		return explode( "\n", preg_replace( '/(?<!\n)(<!-- \/?wp:)/', "\n$1", str_replace( "\r\n", "\n", (string) $t ) ) );
	};
	$a = $lignes( $avant );
	$b = $lignes( $apres );
	$debut = 0;
	while ( $debut < count( $a ) && $debut < count( $b ) && $a[ $debut ] === $b[ $debut ] ) {
		++$debut;
	}
	$fa = count( $a ) - 1;
	$fb = count( $b ) - 1;
	while ( $fa >= $debut && $fb >= $debut && $a[ $fa ] === $b[ $fb ] ) {
		--$fa;
		--$fb;
	}
	$d = max( 0, $debut - 2 );
	return wp_text_diff( implode( "\n", array_slice( $a, $d, $fa + 3 - $d ) ), implode( "\n", array_slice( $b, $d, $fb + 3 - $d ) ), array( 'show_split_view' => true ) );
}

function schiesser_ex_bouton( $action, $libelle, $classe = 'button', $extra = '', $confirmer = '' ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin:0 8px 8px 0"' . ( $confirmer ? ' onsubmit="return confirm(\'' . esc_js( $confirmer ) . '\');"' : '' ) . '>';
	wp_nonce_field( $action );
	echo '<input type="hidden" name="action" value="' . esc_attr( $action ) . '">' . $extra; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<button class="' . esc_attr( $classe ) . '">' . esc_html( $libelle ) . '</button></form>';
}

function schiesser_ex_page() {
	$attente    = get_option( SCHIESSER_EX_ATTENTE );
	$sauvegarde = get_option( SCHIESSER_EX_SAUVEGARDE );
	$rapport    = get_option( SCHIESSER_EX_RAPPORT );
	if ( $rapport ) {
		delete_option( SCHIESSER_EX_RAPPORT );
	}
	?>
	<style>
		.sx-carte{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px 24px;margin:0 0 18px;max-width:1100px}
		.sx-carte h2{margin:0 0 6px;font-size:17px}
		.sx-carte>p:first-of-type{margin-top:0;color:#50575e;max-width:80ch}
		.sx-table{border-collapse:collapse;width:100%;margin:12px 0}
		.sx-table th,.sx-table td{border-top:1px solid #f0f0f1;padding:8px 10px;text-align:left;vertical-align:top}
		.sx-table th{background:#f6f7f7;font-weight:600}
		.sx-table td.c{width:150px;color:#50575e}
		.sx-avant{color:#8a2424}.sx-apres{color:#1a5e20}
		.sx-badge{display:inline-block;padding:1px 8px;border-radius:9px;font-size:11px;font-weight:600;background:#f0f0f1;margin-left:6px}
		.sx-badge.conflit{background:#fcf0e3;color:#8a4b00}
		.sx-diff table.diff{width:100%;border-collapse:collapse;font-size:12px;margin-top:6px}
		.sx-diff table.diff td{padding:2px 6px;vertical-align:top;word-break:break-word}
		.sx-diff .diff-deletedline{background:#fcdede}.sx-diff .diff-addedline{background:#e6f5e6}
		.sx-diff del{background:#f6a9a9;text-decoration:none}.sx-diff ins{background:#a9e2a9;text-decoration:none}
	</style>
	<div class="wrap">
		<h1>Export et import du site</h1>
		<?php
		if ( 'fichier' === sanitize_key( $_GET['erreur'] ?? '' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-error"><p>Ce fichier n’est pas un export Schiesser (fichier JSON créé par cet outil).</p></div>';
		}
		if ( is_array( $rapport ) && 'annulation' === $rapport['genre'] ) {
			echo '<div class="notice notice-success"><p><strong>Import annulé :</strong> le site a retrouvé son état d’avant le dernier import.</p></div>';
		} elseif ( is_array( $rapport ) ) {
			echo '<div class="notice ' . ( $rapport['erreurs'] ? 'notice-warning' : 'notice-success' ) . '"><p><strong>Import terminé (' . esc_html( $rapport['nom'] ) . ') :</strong> ' . (int) $rapport['faits'] . ' élément(s) mis à jour' . ( $rapport['ignores'] ? ', ' . (int) $rapport['ignores'] . ' laissé(s) tel(s) quel(s)' : '' ) . '. L’état d’avant est gardé : bouton « Annuler le dernier import » plus bas.</p>';
			if ( $rapport['erreurs'] ) {
				echo '<ul style="list-style:disc;margin-left:20px">';
				foreach ( $rapport['erreurs'] as $e ) {
					echo '<li>' . esc_html( $e ) . '</li>';
				}
				echo '</ul>';
			}
			echo '</div>';
		}

		if ( is_array( $attente ) ) {
			schiesser_ex_apercu( $attente );
		}
		?>
		<div class="sx-carte">
			<h2>1. Exporter le site</h2>
			<p>Un fichier JSON lisible par Claude, avec son mode d’emploi : pages (blocs compris) dans les trois langues, produits, carte du Tea Room, informations des médias (sans les fichiers) et réglages. Pour demander un changement à Claude, exportez seulement la page concernée : le fichier est petit et contient aussi l’aperçu HTML de la page, utile pour les retouches de style. Les comptes, les messages reçus et les clés secrètes ne sont jamais exportés.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'schiesser_ex_exporter' ); ?>
				<input type="hidden" name="action" value="schiesser_ex_exporter">
				<label for="sx-portee" class="screen-reader-text">Partie du site à exporter</label>
				<select name="portee" id="sx-portee" style="min-width:320px;max-width:100%">
					<?php foreach ( schiesser_ex_portees() as $cle => $nom ) : ?>
						<option value="<?php echo esc_attr( $cle ); ?>"><?php echo esc_html( $nom ); ?></option>
					<?php endforeach; ?>
				</select>
				<button class="button button-primary">Télécharger l’export</button>
			</form>
		</div>
		<div class="sx-carte">
			<h2>2. Importer un fichier modifié</h2>
			<p>Un aperçu montre chaque changement avant de l’appliquer. Rien n’est modifié tant que vous n’avez pas cliqué sur « Appliquer ».</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'schiesser_ex_envoyer' ); ?>
				<input type="hidden" name="action" value="schiesser_ex_envoyer">
				<input type="file" name="fichier" accept=".json,application/json" required>
				<button class="button">Voir l’aperçu</button>
			</form>
		</div>
		<?php if ( is_array( $sauvegarde ) ) : ?>
			<div class="sx-carte">
				<h2>3. Annuler le dernier import</h2>
				<p>Import du <?php echo esc_html( mysql2date( 'j F Y à H:i', $sauvegarde['date'] ) ); ?> (<?php echo (int) $sauvegarde['nombre']; ?> élément(s)). Remet les textes, réglages et rubriques comme avant ; les contenus créés vont à la corbeille.</p>
				<?php schiesser_ex_bouton( 'schiesser_ex_annuler', 'Annuler le dernier import', 'button', '', 'Remettre le site dans son état d’avant le dernier import ?' ); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

function schiesser_ex_apercu( array $a ) {
	$d        = $a['donnees'];
	$plan     = schiesser_ex_plan( $d );
	$ops      = $plan['operations'];
	$conflits = count( array_filter( $ops, function ( $o ) {
		return ! empty( $o['conflit'] );
	} ) );
	$actions  = array( 'modifier' => 'Modifié', 'creer' => 'Créé', 'corbeille' => 'Mis à la corbeille' );
	echo '<div class="sx-carte" style="border-color:#2271b1;border-width:2px">';
	echo '<h2>Aperçu de l’import : ' . esc_html( $a['nom'] ) . '</h2>';
	echo '<p>Fichier créé le ' . esc_html( (string) ( $d['genere_le'] ?? '?' ) ) . ' (UTC)' . ( ! empty( $d['site']['adresse'] ) && untrailingslashit( $d['site']['adresse'] ) !== untrailingslashit( home_url( '/' ) ) ? ', <strong>depuis un autre site : ' . esc_html( $d['site']['adresse'] ) . '</strong>' : '' ) . '. <strong>' . count( $ops ) . ' élément(s) à changer</strong>' . ( $conflits ? ', dont ' . (int) $conflits . ' modifié(s) sur le site depuis l’export' : '' ) . '.</p>';
	foreach ( $plan['erreurs'] as $e ) {
		echo '<p class="sx-avant">⚠ ' . esc_html( $e ) . '</p>';
	}
	if ( $ops ) {
		echo '<table class="sx-table"><thead><tr><th>Élément</th><th>Changements</th></tr></thead><tbody>';
		foreach ( $ops as $op ) {
			echo '<tr><td style="width:30%"><strong>' . esc_html( $op['objet'] ) . '</strong>';
			echo '<span class="sx-badge">' . esc_html( $actions[ $op['action'] ?? '' ] ?? 'Modifié' ) . '</span>';
			if ( ! empty( $op['conflit'] ) ) {
				echo '<span class="sx-badge conflit">modifié sur le site depuis l’export</span>';
			}
			echo '</td><td>';
			if ( ! $op['details'] ) {
				echo '<span class="description">' . ( 'creer' === ( $op['action'] ?? '' ) ? 'Nouveau contenu.' : '' ) . '</span>';
			}
			echo '<table class="sx-table" style="margin:0">';
			foreach ( $op['details'] as $l ) {
				echo '<tr><td class="c">' . esc_html( $l[0] ) . '</td><td>';
				if ( 'Contenu' === $l[0] && is_string( $l[1] ) && is_string( $l[2] ) ) {
					echo '<details class="sx-diff"><summary>Texte des blocs modifié : voir les différences</summary>' . schiesser_ex_diff( $l[1], $l[2] ) . '</details>'; // phpcs:ignore WordPress.Security.EscapeOutput
				} else {
					echo '<span class="sx-avant">' . esc_html( schiesser_ex_extrait( $l[1] ) ) . '</span><br><span class="sx-apres">→ ' . esc_html( schiesser_ex_extrait( $l[2] ) ) . '</span>';
				}
				echo '</td></tr>';
			}
			echo '</table></td></tr>';
		}
		echo '</tbody></table>';
		$forcer = $conflits ? '<label style="display:block;margin:0 0 10px"><input type="checkbox" name="forcer" value="1"> Remplacer aussi les éléments modifiés sur le site depuis l’export</label>' : '';
		schiesser_ex_bouton( 'schiesser_ex_appliquer', 'Appliquer ces changements', 'button button-primary', $forcer );
	} else {
		echo '<p>Le site est déjà dans l’état du fichier : rien à changer.</p>';
	}
	schiesser_ex_bouton( 'schiesser_ex_abandonner', 'Abandonner cet import' );
	echo '</div>';
}

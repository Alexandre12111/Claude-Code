<?php
/**
 * Mises à jour des pages livrées avec le thème, appliquées au site réel : corrections de textes relues
 * par le client (document Word) et changements de mise en page (sections masquées, fonds, liens).
 *
 * Chaque fichier corrections/*.json est un lot, appliqué ou annulé à part (Réglages maison → Mises à jour
 * des pages). Les lots de textes (construits par wordpress/textes/outils/construire-corrections.py)
 * listent, pour chaque texte : la page, l'emplacement du bloc, le réglage, la langue, l'ancien texte
 * et le nouveau. L'outil :
 *
 * - retrouve chaque texte dans la page de sa langue, d'abord à son emplacement, sinon d'après son
 *   ancien texte (sections déplacées ou ajoutées) ; un texte déjà retouché sur le site est laissé tel quel ;
 * - ne remplace que le texte : photos, mise en page et réglages des blocs ne bougent pas, et les
 *   liens internes de l'ancien texte sont remis sur les mêmes mots ;
 * - supprime les éléments vidés par le client, masque les sections demandées (réversible) ;
 * - met à jour les titres et descriptions Google et la présentation s'ils n'ont pas été retouchés ;
 * - « sections » : masque une section d'après son type de bloc (réversible : « Afficher à partir du ») ;
 * - « attributs » : change un réglage de bloc (fond, lien d'un bouton…) s'il a encore sa valeur d'avant ;
 *   « {page:confiserie} » devient l'adresse de cette page dans la langue de la page modifiée ;
 * - garde une copie de tout ce qu'il change : un bouton remet l'état d'avant.
 */

defined( 'ABSPATH' ) || exit;

/** Lots livrés avec le thème (corrections/*.json), du plus ancien au plus récent : version => données. */
function schiesser_corr_lots() {
	static $lots = null;
	if ( null === $lots ) {
		$lots = array();
		foreach ( (array) glob( SCHIESSER_DIR . '/corrections/*.json' ) as $f ) {
			$d = json_decode( (string) file_get_contents( $f ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( is_array( $d ) && ! empty( $d['version'] ) ) {
				$lots[ (string) $d['version'] ] = $d;
			}
		}
		uasort( $lots, function ( $a, $b ) {
			return strcmp( (string) ( $a['date'] ?? '' ), (string) ( $b['date'] ?? '' ) );
		} );
	}
	return $lots;
}

/** Lots déjà appliqués : version => [ date, compte ]. */
function schiesser_corr_appliques() {
	return (array) get_option( 'schiesser_corrections_appliquees', array() );
}

/** Données d'un lot : celui demandé, sinon le premier pas encore appliqué, sinon le plus récent. */
function schiesser_corr_donnees( $version = '' ) {
	$lots = schiesser_corr_lots();
	if ( '' !== (string) $version ) {
		return $lots[ $version ] ?? array();
	}
	foreach ( $lots as $v => $d ) {
		if ( ! isset( schiesser_corr_appliques()[ $v ] ) ) {
			return $d;
		}
	}
	return $lots ? end( $lots ) : array();
}

/** Rapport unique d'avant la 0.19 : rangé avec le lot appliqué le plus anciennement qui n'a pas le sien. */
function schiesser_corr_migrer_rapport() {
	$ancien = get_option( 'schiesser_corrections_rapport', null );
	if ( ! is_array( $ancien ) ) {
		return;
	}
	$rapports = (array) get_option( 'schiesser_corrections_rapports', array() );
	$faits    = schiesser_corr_appliques();
	uasort( $faits, function ( $a, $b ) {
		return strcmp( (string) ( $a['date'] ?? '' ), (string) ( $b['date'] ?? '' ) );
	} );
	foreach ( array_keys( $faits ) as $v ) {
		if ( ! isset( $rapports[ $v ] ) ) {
			$rapports[ $v ] = $ancien;
			break;
		}
	}
	update_option( 'schiesser_corrections_rapports', $rapports, false );
	delete_option( 'schiesser_corrections_rapport' );
}

/** Rapport enregistré quand un lot a été appliqué. */
function schiesser_corr_rapport( $version ) {
	schiesser_corr_migrer_rapport();
	return (array) ( ( (array) get_option( 'schiesser_corrections_rapports', array() ) )[ $version ] ?? array() );
}

/** Lot appliqué le plus récemment (le seul qu'on peut annuler sans défaire un lot appliqué après lui). */
function schiesser_corr_dernier_applique() {
	$faits = schiesser_corr_appliques();
	uasort( $faits, function ( $a, $b ) {
		return strcmp( (string) ( $b['date'] ?? '' ), (string) ( $a['date'] ?? '' ) );
	} );
	return (string) array_key_first( $faits );
}

/** Forme de comparaison d'un texte : sans balises, espaces, guillemets et apostrophes unifiés. */
function schiesser_corr_norm( $s ) {
	$s = preg_replace( '#<br\s*/?>#i', ' ', (string) $s );
	$s = preg_replace( '#<[^>]+>#', '', $s );
	$s = html_entity_decode( $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$s = str_replace( array( "\u{00A0}", '“', '”', '„', '«', '»', '‹', '›', '’', '‘' ), array( ' ', '"', '"', '"', '"', '"', '"', '"', "'", "'" ), $s );
	return trim( preg_replace( '/\s+/u', ' ', $s ) );
}

/* ------------------------------------------------------------------ */
/* Pages                                                               */
/* ------------------------------------------------------------------ */

/** Pages du site pour une adresse allemande : langue => identifiant. */
function schiesser_corr_pages( $slug ) {
	static $cache = array();
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}
	$de = 0;
	foreach ( get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ), 'numberposts' => -1, 'fields' => 'ids', 'lang' => '' ) ) as $id ) {
		if ( function_exists( 'schiesser_est_allemand' ) ? schiesser_est_allemand( $id ) : true ) {
			$de = (int) $id;
			break;
		}
	}
	$pages = $de ? array( 'de' => $de ) : array();
	if ( $de && function_exists( 'pll_get_post_translations' ) ) {
		foreach ( (array) pll_get_post_translations( $de ) as $l => $id ) {
			$pages[ substr( (string) $l, 0, 2 ) ] = (int) $id;
		}
	}
	// Traduction non reliée : son adresse habituelle.
	$meta = function_exists( 'schiesser_tr_pages' ) ? schiesser_tr_pages() : array();
	foreach ( array( 'fr', 'en' ) as $l ) {
		if ( empty( $pages[ $l ] ) && ! empty( $meta[ $slug ][ $l ][1] ) ) {
			$p = get_posts( array( 'post_type' => 'page', 'name' => $meta[ $slug ][ $l ][1], 'post_status' => array( 'publish', 'draft', 'private', 'pending' ), 'numberposts' => 1, 'fields' => 'ids', 'lang' => '' ) );
			if ( $p ) {
				$pages[ $l ] = (int) $p[0];
			}
		}
	}
	return $cache[ $slug ] = $pages;
}

/* ------------------------------------------------------------------ */
/* Blocs                                                               */
/* ------------------------------------------------------------------ */

/** Tous les blocs (sans les blocs vides) : chemin « 0.3.1 » => bloc, dans l'ordre. */
function schiesser_corr_index( $blocs, $base = '' ) {
	$out = array();
	$i   = 0;
	foreach ( $blocs as $b ) {
		if ( empty( $b['blockName'] ) ) {
			continue;
		}
		$chemin         = '' === $base ? (string) $i : $base . '.' . $i;
		$out[ $chemin ] = $b;
		if ( ! empty( $b['innerBlocks'] ) ) {
			$out += schiesser_corr_index( $b['innerBlocks'], $chemin );
		}
		++$i;
	}
	return $out;
}

/** Référence vers un bloc d'après son chemin. */
function &schiesser_corr_bloc( &$blocs, $chemin ) {
	$rien = null;
	$cur  = &$blocs;
	$bloc = &$rien;
	foreach ( explode( '.', (string) $chemin ) as $n ) {
		$i = 0;
		$trouve = false;
		foreach ( $cur as $k => &$b ) {
			if ( empty( $b['blockName'] ) ) {
				continue;
			}
			if ( $i === (int) $n ) {
				$bloc   = &$b;
				$trouve = true;
				break;
			}
			++$i;
		}
		unset( $b );
		if ( ! $trouve ) {
			$rien = null;
			return $rien;
		}
		$cur = &$bloc['innerBlocks'];
	}
	return $bloc;
}

/** Texte d'un bloc pour un réglage (« contenu » : le paragraphe lui-même). */
function schiesser_corr_valeur( $b, $cle ) {
	if ( 'contenu' === $cle ) {
		return preg_replace( '#^\s*<(p|h[1-6]|li)[^>]*>|</(p|h[1-6]|li)>\s*$#', '', (string) $b['innerHTML'] );
	}
	return isset( $b['attrs'][ $cle ] ) && is_string( $b['attrs'][ $cle ] ) ? $b['attrs'][ $cle ] : '';
}

/** Remet sur les mêmes mots les liens de l'ancien texte (le document Word ne les garde pas). */
function schiesser_corr_liens( $ancien, $nouveau, &$perdus, &$raccourcis = array() ) {
	if ( ! preg_match_all( '#<a\s[^>]*>(.*?)</a>#is', $ancien, $liens, PREG_SET_ORDER ) ) {
		return $nouveau;
	}
	$equivalents = array( 'salon de thé' => 'Tea Room', 'confectionery' => 'Confiserie', 'bakehouse' => 'bakery', 'Pralinen' => 'Pralinés' );
	foreach ( $liens as $lien ) {
		if ( false !== stripos( $nouveau, $lien[0] ) ) {
			continue; // déjà présent
		}
		$mots      = trim( wp_strip_all_tags( $lien[1] ) );
		$candidats = array( $mots );
		foreach ( $equivalents as $de => $vers ) {
			if ( false !== stripos( $mots, $de ) ) {
				$candidats[] = str_ireplace( $de, $vers, $mots );
			}
		}
		// Puis le début de la formule (« Confiserie am Basler Marktplatz » → « Confiserie »), mot significatif seulement.
		// (le mot principal est en tête : d'abord les débuts, du plus long au plus court, puis les fins).
		$debuts = array();
		$fins   = array();
		foreach ( $candidats as $c ) {
			$liste = preg_split( '/\s+/u', $c );
			for ( $n = count( $liste ) - 1; $n >= 1; $n-- ) {
				$debuts[] = rtrim( implode( ' ', array_slice( $liste, 0, $n ) ), ' ,;:' );
				$fins[]   = ltrim( implode( ' ', array_slice( $liste, -$n ) ), ' ,;:' );
			}
		}
		foreach ( array_merge( $debuts, $fins ) as $morceau ) {
			if ( mb_strlen( $morceau ) >= 5 && ! in_array( $morceau, $candidats, true ) ) {
				$candidats[] = $morceau;
			}
		}
		$pose = false;
		foreach ( $candidats as $c ) {
			if ( '' === $c ) {
				continue;
			}
			// Première occurrence hors balise.
			$parts = preg_split( '#(<[^>]+>)#', $nouveau, -1, PREG_SPLIT_DELIM_CAPTURE );
			$dans_lien = false;
			foreach ( $parts as $k => $part ) {
				if ( '' !== $part && '<' === $part[0] ) {
					$dans_lien = preg_match( '#^<a\s#i', $part ) ? true : ( preg_match( '#^</a>#i', $part ) ? false : $dans_lien );
					continue;
				}
				$pos = $dans_lien ? false : mb_stripos( $part, $c );
				if ( false !== $pos ) {
					$trouve      = mb_substr( $part, $pos, mb_strlen( $c ) );
					$ouverture   = preg_replace( '#>.*$#s', '>', $lien[0] );
					$parts[ $k ] = mb_substr( $part, 0, $pos ) . $ouverture . $trouve . '</a>' . mb_substr( $part, $pos + mb_strlen( $c ) );
					$pose        = true;
					break;
				}
			}
			if ( $pose ) {
				$nouveau = implode( '', $parts );
				if ( $c !== $mots ) {
					$raccourcis[] = '« ' . $c . ' » (au lieu de « ' . $mots . ' »)';
				}
				break;
			}
		}
		if ( ! $pose ) {
			$perdus[] = $mots;
		}
	}
	return $nouveau;
}

/** Écrit un texte dans un bloc. */
function schiesser_corr_ecrire( &$b, $cle, $valeur ) {
	if ( 'contenu' === $cle ) {
		$ouverture       = preg_match( '#^\s*(<(p|h[1-6]|li)[^>]*>)#', (string) $b['innerHTML'], $m ) ? $m[1] : '<p>';
		$fermeture       = '</' . ( $m[2] ?? 'p' ) . '>';
		$b['innerHTML']    = $ouverture . $valeur . $fermeture;
		$b['innerContent'] = array( $b['innerHTML'] );
		return;
	}
	$b['attrs'][ $cle ] = $valeur;
}

/**
 * Cherche le bloc d'un texte : à son emplacement s'il y porte l'ancien texte, sinon le premier bloc
 * du même type qui le porte (et pas encore utilisé). Renvoie [ chemin, état ] ; état : « a_faire »,
 * « deja » (nouveau texte déjà en place) ou « introuvable ».
 */
function schiesser_corr_trouver( $index, $c, $ancien, $nouveau, $utilises ) {
	$a = schiesser_corr_norm( $ancien );
	$n = schiesser_corr_norm( $nouveau );
	$ordre = array();
	if ( isset( $index[ $c['chemin'] ] ) ) {
		$ordre[] = $c['chemin'];
	}
	foreach ( $index as $chemin => $b ) {
		if ( $chemin !== $c['chemin'] ) {
			$ordre[] = (string) $chemin;
		}
	}
	foreach ( array( 'a_faire' => $a, 'deja' => $n ) as $etat => $cible ) {
		foreach ( $ordre as $chemin ) {
			$b = $index[ $chemin ];
			if ( $b['blockName'] !== $c['bloc'] || isset( $utilises[ $chemin . '|' . $c['cle'] ] ) ) {
				continue;
			}
			if ( schiesser_corr_norm( schiesser_corr_valeur( $b, $c['cle'] ) ) === $cible ) {
				return array( $chemin, $etat );
			}
		}
	}
	return array( null, 'introuvable' );
}

/** Supprime un bloc (et sa place dans le contenu de son parent). */
function schiesser_corr_supprimer_bloc( &$blocs, $chemin ) {
	$parties = explode( '.', (string) $chemin );
	$rang    = (int) array_pop( $parties );
	$parent  = null;
	if ( $parties ) {
		$parent = &schiesser_corr_bloc( $blocs, implode( '.', $parties ) );
		if ( null === $parent ) {
			return false;
		}
		$liste = &$parent['innerBlocks'];
	} else {
		$liste = &$blocs;
	}
	// Clé du bloc (rang parmi les blocs non vides) et sa position dans la liste.
	$i = 0;
	$cle = null;
	foreach ( $liste as $k => $b ) {
		if ( empty( $b['blockName'] ) ) {
			continue;
		}
		if ( $i === $rang ) {
			$cle = $k;
			break;
		}
		++$i;
	}
	if ( null === $cle ) {
		return false;
	}
	$position = array_search( $cle, array_keys( $liste ), true );
	unset( $liste[ $cle ] );
	$liste = array_values( $liste );
	// Dans le contenu du parent, chaque bloc intérieur occupe une place « null », dans l'ordre.
	if ( null !== $parent ) {
		$n = 0;
		foreach ( $parent['innerContent'] as $j => $morceau ) {
			if ( null !== $morceau ) {
				continue;
			}
			if ( $n === $position ) {
				array_splice( $parent['innerContent'], $j, 1 );
				break;
			}
			++$n;
		}
	}
	return true;
}

/** Numéros des sections visibles (01, 02…) remis à la suite après une section masquée. */
function schiesser_corr_renumeroter( &$blocs ) {
	$n = 0;
	foreach ( $blocs as &$b ) {
		$visible = function_exists( 'schiesser_bloc_visible' ) ? schiesser_bloc_visible( (array) $b['attrs'] ) : empty( $b['attrs']['afficherDu'] );
		if ( empty( $b['blockName'] ) || ! $visible || ! isset( $b['attrs']['numero'] ) || ! preg_match( '/^\d{2}$/', (string) $b['attrs']['numero'] ) ) {
			continue;
		}
		$b['attrs']['numero'] = str_pad( (string) ++$n, 2, '0', STR_PAD_LEFT );
	}
	unset( $b );
}

/** Valeur d'un réglage de bloc : la sienne, sinon celle par défaut du type de bloc. */
function schiesser_corr_attribut( $b, $cle ) {
	if ( array_key_exists( $cle, (array) $b['attrs'] ) ) {
		return $b['attrs'][ $cle ];
	}
	$type = WP_Block_Type_Registry::get_instance()->get_registered( (string) $b['blockName'] );
	return $type && isset( $type->attributes[ $cle ]['default'] ) ? $type->attributes[ $cle ]['default'] : '';
}

/** Valeur d'une opération pour une langue (texte commun ou { de, fr, en }), adresses de pages remplacées. */
function schiesser_corr_valeur_langue( $v, $l ) {
	if ( is_array( $v ) ) {
		$v = $v[ $l ] ?? null;
	}
	if ( ! is_string( $v ) ) {
		return null;
	}
	return preg_replace_callback( '/\{page:([a-z0-9-]+)\}/', function ( $m ) use ( $l ) {
		$id = schiesser_corr_pages( $m[1] )[ $l ] ?? 0;
		return $id ? (string) get_permalink( $id ) : $m[0];
	}, $v );
}

/** Le bloc remplit-il les conditions d'une opération (« si » : réglage => valeur, commune ou { de, fr, en }) ? */
function schiesser_corr_conditions( $b, $si, $l ) {
	foreach ( (array) $si as $cle => $valeur ) {
		$attendu = schiesser_corr_valeur_langue( $valeur, $l );
		if ( null === $attendu || schiesser_corr_norm( (string) schiesser_corr_attribut( $b, $cle ) ) !== schiesser_corr_norm( $attendu ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Produits et plats dont un texte français ou anglais est redevenu allemand (avant la 0.19, enregistrer la
 * version allemande recopiait ses textes dans les autres langues), avec la traduction du thème quand elle existe.
 *
 * @return array Lignes [ id, langue, métadonnée, texte allemand, traduction, nom ].
 */
function schiesser_corr_produits_a_retraduire() {
	if ( ! function_exists( 'pll_get_post_translations' ) || ! function_exists( 'schiesser_tr' ) || ! function_exists( 'schiesser_posts_allemands' ) ) {
		return array();
	}
	$out   = array();
	$types = array(
		SCHIESSER_PRODUIT => array( function_exists( 'schiesser_tr_boutique' ) ? schiesser_tr_boutique() : array(), array( '_s_accroche', '_s_description', '_s_badge', '_s_accord', '_s_origine' ) ),
		SCHIESSER_TEAROOM => array( function_exists( 'schiesser_tr_carte' ) ? schiesser_tr_carte() : array(), array( '_t_description', '_t_mention' ) ),
	);
	foreach ( $types as $type => $t ) {
		list( $dico, $cles ) = $t;
		foreach ( schiesser_posts_allemands( array( 'post_type' => $type, 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ), 'numberposts' => -1 ) ) as $p ) {
			foreach ( (array) pll_get_post_translations( $p->ID ) as $slug => $id ) {
				$l = substr( (string) $slug, 0, 2 );
				if ( 'de' === $l || (int) $id === (int) $p->ID ) {
					continue;
				}
				foreach ( $cles as $cle ) {
					$de = (string) get_post_meta( $p->ID, $cle, true );
					if ( '' === trim( $de ) || (string) get_post_meta( $id, $cle, true ) !== $de ) {
						continue;
					}
					$tr = schiesser_tr( $dico, $de, $l );
					if ( $tr !== $de ) {
						$out[] = array( (int) $id, $l, $cle, $de, $tr, html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ) );
					}
				}
			}
		}
	}
	return $out;
}

/** Photo de la médiathèque d'après le nom de son fichier. */
function schiesser_corr_photo( $fichier ) {
	global $wpdb;
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id ASC LIMIT 1", '%' . $wpdb->esc_like( $fichier ) . '%' ) );
	return $id && wp_attachment_is_image( (int) $id ) ? (int) $id : 0;
}

/* ------------------------------------------------------------------ */
/* Exécution (aperçu ou application)                                   */
/* ------------------------------------------------------------------ */

/**
 * @param bool   $appliquer false : aperçu, rien n'est écrit.
 * @param string $version   Lot à traiter (vide : voir schiesser_corr_donnees()).
 * @return array Rapport : lignes [ type, ref, langue, état, détail ] et compteurs.
 */
function schiesser_corr_executer( $appliquer = false, $version = '' ) {
	$d       = schiesser_corr_donnees( $version );
	$rapport = array( 'lignes' => array(), 'compte' => array(), 'pages' => array() );
	$note    = function ( $type, $ref, $l, $etat, $detail = '' ) use ( &$rapport ) {
		$rapport['lignes'][]         = array( $type, $ref, $l, $etat, $detail );
		$rapport['compte'][ $etat ] = ( $rapport['compte'][ $etat ] ?? 0 ) + 1;
	};
	if ( ! $d ) {
		return $rapport;
	}
	$sauvegarde = $appliquer ? (array) get_option( 'schiesser_corrections_sauvegarde', array() ) : array();
	$version    = (string) $d['version'];

	// Regroupement par page et par langue.
	$travail = array();
	foreach ( (array) $d['textes'] as $c ) {
		$travail[ $c['page'] ][ $c['langue'] ]['textes'][] = $c;
	}
	foreach ( array( 'supprimer', 'masquer' ) as $type ) {
		foreach ( (array) ( $d[ $type ] ?? array() ) as $c ) {
			foreach ( (array) $c['ancien'] as $l => $ancien ) {
				$travail[ $c['page'] ][ $l ][ $type ][] = array_merge( $c, array( 'langue' => $l, 'ancien' => $ancien ) );
			}
		}
	}
	// Sections (d'après leur type de bloc) et réglages de blocs : sur chaque langue du site.
	foreach ( array( 'sections', 'attributs' ) as $type ) {
		foreach ( (array) ( $d[ $type ] ?? array() ) as $c ) {
			foreach ( (array) ( $c['langues'] ?? array( 'de', 'fr', 'en' ) ) as $l ) {
				$travail[ $c['page'] ][ $l ][ $type ][] = $c;
			}
		}
	}
	if ( ! empty( $d['comparateur_1889'] ) ) {
		foreach ( array( 'de', 'fr', 'en' ) as $l ) {
			$travail[ $d['comparateur_1889']['page'] ][ $l ]['comparateur'] = $d['comparateur_1889'];
		}
	}

	foreach ( $travail as $slug => $par_langue ) {
		$pages = schiesser_corr_pages( $slug );
		foreach ( $par_langue as $l => $t ) {
			$id = $pages[ $l ] ?? 0;
			if ( ! $id ) {
				foreach ( array( 'textes' => 'texte', 'sections' => 'section', 'attributs' => 'reglage-bloc' ) as $cle => $type ) {
					foreach ( (array) ( $t[ $cle ] ?? array() ) as $c ) {
						$note( $type, $c['ref'], $l, 'introuvable', 'Page « ' . $slug . ' » introuvable en ' . strtoupper( $l ) );
					}
				}
				continue;
			}
			$avant    = (string) get_post_field( 'post_content', $id );
			$blocs    = parse_blocks( $avant );
			$change   = false;
			$utilises = array();

			// 1. Textes
			foreach ( (array) ( $t['textes'] ?? array() ) as $c ) {
				$index = schiesser_corr_index( $blocs );
				list( $chemin, $etat ) = schiesser_corr_trouver( $index, $c, $c['ancien'], $c['nouveau'], $utilises );
				if ( 'a_faire' === $etat ) {
					$utilises[ $chemin . '|' . $c['cle'] ] = true;
					$b       = &schiesser_corr_bloc( $blocs, $chemin );
					$perdus     = array();
					$raccourcis = array();
					$valeur     = empty( $c['sans_liens'] ) ? schiesser_corr_liens( schiesser_corr_valeur( $b, $c['cle'] ), $c['nouveau'], $perdus, $raccourcis ) : $c['nouveau'];
					schiesser_corr_ecrire( $b, $c['cle'], $valeur );
					unset( $b );
					$change = true;
					$note( 'texte', $c['ref'], $l, 'applique', trim( ( $perdus ? 'Lien retiré (mots absents du nouveau texte) : ' . implode( ', ', $perdus ) . '. ' : '' ) . ( $raccourcis ? 'Lien posé sur ' . implode( ', ', $raccourcis ) . '.' : '' ) ) );
				} elseif ( 'deja' === $etat ) {
					$utilises[ $chemin . '|' . $c['cle'] ] = true;
					$note( 'texte', $c['ref'], $l, 'deja', '' );
				} else {
					$note( 'texte', $c['ref'], $l, 'introuvable', 'Texte déjà retouché sur le site : laissé tel quel. Nouveau texte : ' . wp_strip_all_tags( $c['nouveau'] ) );
				}
			}

			// 2. Éléments vidés par le client : supprimés.
			foreach ( (array) ( $t['supprimer'] ?? array() ) as $c ) {
				list( $chemin, $etat ) = schiesser_corr_trouver( schiesser_corr_index( $blocs ), $c, $c['ancien'], "\x00", array() );
				if ( 'a_faire' === $etat && schiesser_corr_supprimer_bloc( $blocs, $chemin ) ) {
					$change = true;
					$note( 'supprimer', $c['ref'], $l, 'applique', $c['raison'] );
				} else {
					$note( 'supprimer', $c['ref'], $l, 'deja', $c['raison'] . ' (déjà retiré)' );
				}
			}

			// 3. Sections masquées (réversible : réglage « Afficher à partir du »).
			foreach ( (array) ( $t['masquer'] ?? array() ) as $c ) {
				list( $chemin, $etat ) = schiesser_corr_trouver( schiesser_corr_index( $blocs ), $c, $c['ancien'], "\x00", array() );
				$racine = null !== $chemin ? explode( '.', $chemin )[0] : null;
				$b      = null !== $racine ? schiesser_corr_bloc( $blocs, $racine ) : null;
				if ( $b && empty( $b['attrs']['afficherDu'] ) ) {
					$s                         = &schiesser_corr_bloc( $blocs, $racine );
					$s['attrs']['afficherDu'] = '2099-12-31T00:00';
					unset( $s );
					schiesser_corr_renumeroter( $blocs );
					$change = true;
					$note( 'masquer', $c['ref'], $l, 'applique', $c['raison'] );
				} else {
					$note( 'masquer', $c['ref'], $l, $b ? 'deja' : 'introuvable', $c['raison'] );
				}
			}

			// 4. Sections d'après leur type de bloc (masquées de façon réversible).
			foreach ( (array) ( $t['sections'] ?? array() ) as $c ) {
				$rang  = (int) ( $c['rang'] ?? 0 );
				$i     = 0;
				$cible = null;
				foreach ( $blocs as $k => $b ) {
					if ( (string) $b['blockName'] === $c['bloc'] && $i++ === $rang ) {
						$cible = $k;
						break;
					}
				}
				if ( null === $cible ) {
					$note( 'section', $c['ref'], $l, 'introuvable', $c['raison'] . ' (section absente de la page)' );
				} elseif ( function_exists( 'schiesser_bloc_visible' ) && ! schiesser_bloc_visible( (array) $blocs[ $cible ]['attrs'] ) ) {
					$note( 'section', $c['ref'], $l, 'deja', $c['raison'] . ' (déjà masquée)' );
				} else {
					$blocs[ $cible ]['attrs']['afficherDu'] = '2099-12-31T00:00';
					unset( $blocs[ $cible ]['attrs']['afficherAu'] );
					schiesser_corr_renumeroter( $blocs );
					$change = true;
					$note( 'section', $c['ref'], $l, 'applique', $c['raison'] );
				}
			}

			// 5. Réglages de blocs (fond, lien…), seulement s'ils ont encore leur valeur d'avant.
			foreach ( (array) ( $t['attributs'] ?? array() ) as $c ) {
				$ancien  = schiesser_corr_valeur_langue( $c['ancien'], $l );
				$nouveau = schiesser_corr_valeur_langue( $c['nouveau'], $l );
				if ( null === $ancien || null === $nouveau || false !== strpos( $nouveau, '{page:' ) ) {
					$note( 'reglage-bloc', $c['ref'], $l, 'introuvable', $c['raison'] . ' (valeur ou page introuvable dans cette langue)' );
					continue;
				}
				$vus      = 0;
				$faits    = 0;
				$deja     = 0;
				$retouche = '';
				$autres   = 0; // blocs du bon type qui ne remplissent pas la condition « si »
				foreach ( schiesser_corr_index( $blocs ) as $chemin => $b ) {
					if ( $b['blockName'] !== $c['bloc'] ) {
						continue;
					}
					if ( ! schiesser_corr_conditions( $b, $c['si'] ?? array(), $l ) ) {
						++$autres;
						continue;
					}
					$rang = $vus++;
					if ( isset( $c['rang'] ) && (int) $c['rang'] !== $rang ) {
						continue; // un seul bloc visé : le n-ième de ce type
					}
					$actuel = (string) schiesser_corr_attribut( $b, $c['cle'] );
					if ( schiesser_corr_norm( $actuel ) === schiesser_corr_norm( $nouveau ) ) {
						++$deja;
					} elseif ( schiesser_corr_norm( $actuel ) === schiesser_corr_norm( $ancien ) ) {
						$s                     = &schiesser_corr_bloc( $blocs, $chemin );
						$s['attrs'][ $c['cle'] ] = $nouveau;
						unset( $s );
						++$faits;
					} else {
						$retouche = $actuel;
					}
				}
				if ( $faits ) {
					$change = true;
					$note( 'reglage-bloc', $c['ref'], $l, 'applique', $c['raison'] );
				} elseif ( $deja ) {
					$note( 'reglage-bloc', $c['ref'], $l, 'deja', $c['raison'] );
				} elseif ( '' !== $retouche ) {
					$note( 'reglage-bloc', $c['ref'], $l, 'introuvable', $c['raison'] . ' (retouché sur le site : « ' . $retouche . ' », laissé tel quel)' );
				} elseif ( $autres && ! empty( $c['si'] ) ) {
					$note( 'reglage-bloc', $c['ref'], $l, 'introuvable', $c['raison'] . ' (laissé tel quel : le bloc a été retouché sur le site)' );
				} else {
					$note( 'reglage-bloc', $c['ref'], $l, 'introuvable', $c['raison'] . ' (bloc absent de la page)' );
				}
			}

			// 6. Comparateur : photo de 1889.
			if ( ! empty( $t['comparateur'] ) ) {
				$photo = schiesser_corr_photo( $t['comparateur']['fichier'] );
				$fait  = false;
				foreach ( schiesser_corr_index( $blocs ) as $chemin => $b ) {
					if ( 'schiesser/comparaison' !== $b['blockName'] ) {
						continue;
					}
					if ( ! $photo ) {
						$note( 'photo', 'H-091', $l, 'introuvable', 'Photo de 1889 absente de la médiathèque' );
					} elseif ( (int) ( $b['attrs']['avantId'] ?? 0 ) === $photo && false !== strpos( (string) ( $b['attrs']['avantUrl'] ?? '' ), $t['comparateur']['fichier'] ) ) {
						$note( 'photo', 'H-091', $l, 'deja', $t['comparateur']['raison'] );
					} else {
						$s                       = &schiesser_corr_bloc( $blocs, $chemin );
						$s['attrs']['avantId']  = $photo;
						$s['attrs']['avantUrl'] = (string) wp_get_attachment_image_url( $photo, 'large' );
						unset( $s );
						$change = true;
						$note( 'photo', 'H-091', $l, 'applique', $t['comparateur']['raison'] );
					}
					$fait = true;
					break; // premier onglet seulement
				}
				if ( ! $fait ) {
					$note( 'photo', 'H-091', $l, 'introuvable', 'Comparateur absent de la page' );
				}
			}

			if ( $change ) {
				$rapport['pages'][ $id ] = get_the_title( $id ) . ' (' . strtoupper( $l ) . ')';
				if ( $appliquer ) {
					if ( ! isset( $sauvegarde[ $version ]['pages'][ $id ] ) ) {
						$sauvegarde[ $version ]['pages'][ $id ] = $avant;
					}
					wp_update_post( array( 'ID' => $id, 'post_content' => wp_slash( serialize_blocks( $blocs ) ) ) );
				}
			}
		}
	}

	// 7. Titres et descriptions Google (Rank Math), seulement s'ils sont encore ceux du thème.
	foreach ( (array) ( $d['seo'] ?? array() ) as $s ) {
		$id = schiesser_corr_pages( $s['page'] )[ $s['langue'] ] ?? 0;
		$actuel = $id ? (string) get_post_meta( $id, $s['champ'], true ) : '';
		if ( ! $id ) {
			$note( 'google', $s['page'] . ' · ' . $s['champ'], $s['langue'], 'introuvable', 'Page introuvable' );
		} elseif ( $actuel === $s['nouveau'] ) {
			$note( 'google', $s['page'] . ' · ' . $s['champ'], $s['langue'], 'deja', $s['nouveau'] );
		} elseif ( $actuel === $s['ancien'] || '' === $actuel ) {
			if ( $appliquer ) {
				if ( ! isset( $sauvegarde[ $version ]['metas'][ $id ][ $s['champ'] ] ) ) {
					$sauvegarde[ $version ]['metas'][ $id ][ $s['champ'] ] = $actuel;
				}
				update_post_meta( $id, $s['champ'], $s['nouveau'] );
			}
			$note( 'google', $s['page'] . ' · ' . $s['champ'], $s['langue'], 'applique', $s['nouveau'] );
		} else {
			$note( 'google', $s['page'] . ' · ' . $s['champ'], $s['langue'], 'introuvable', 'Retouché à la main : laissé tel quel. Proposition : ' . $s['nouveau'] );
		}
	}

	// 8. Présentation de la maison (Réglages), si c'est encore le texte du thème.
	foreach ( (array) ( $d['reglages'] ?? array() ) as $r ) {
		$reglages = (array) get_option( SCHIESSER_OPTION, array() );
		$actuel   = (string) ( $reglages[ $r['cle'] ] ?? '' );
		if ( $actuel === $r['nouveau'] ) {
			$note( 'reglage', $r['cle'], 'de', 'deja', '' );
		} elseif ( $actuel === $r['ancien'] ) {
			if ( $appliquer ) {
				$sauvegarde[ $version ]['reglages'][ $r['cle'] ] = $actuel;
				$reglages[ $r['cle'] ]                          = $r['nouveau'];
				update_option( SCHIESSER_OPTION, $reglages );
			}
			$note( 'reglage', $r['cle'], 'de', 'applique', $r['nouveau'] );
		} else {
			$note( 'reglage', $r['cle'], 'de', 'introuvable', 'Retouchée à la main dans les Réglages : laissée telle quelle.' );
		}
	}

	// 9. Produits et plats : textes français et anglais redevenus allemands, remis d'après les traductions du thème.
	if ( ! empty( $d['produits_langues'] ) ) {
		$liste = schiesser_corr_produits_a_retraduire();
		foreach ( $liste as $x ) {
			list( $id, $l, $cle, $de, $tr, $nom ) = $x;
			if ( $appliquer ) {
				if ( ! isset( $sauvegarde[ $version ]['metas'][ $id ][ $cle ] ) ) {
					$sauvegarde[ $version ]['metas'][ $id ][ $cle ] = $de;
				}
				update_post_meta( $id, $cle, $tr );
			}
			$note( 'produit', $nom, $l, 'applique', 'Texte resté en allemand remplacé par sa traduction : ' . wp_strip_all_tags( $tr ) );
		}
		if ( ! $liste ) {
			$note( 'produit', 'Produits', 'fr', 'deja', (string) $d['produits_langues']['raison'] . ' : aucun texte resté en allemand.' );
		}
	}

	if ( $appliquer ) {
		schiesser_corr_migrer_rapport(); // avant d'ajouter le rapport de ce lot
		update_option( 'schiesser_corrections_sauvegarde', $sauvegarde, false );
		update_option( 'schiesser_corrections_appliquees', array( $version => array( 'date' => current_time( 'mysql' ), 'compte' => $rapport['compte'] ) ) + schiesser_corr_appliques(), false );
		update_option( 'schiesser_corrections_rapports', array( $version => $rapport ) + (array) get_option( 'schiesser_corrections_rapports', array() ), false );
		if ( function_exists( 'schiesser_carte_tearoom' ) ) {
			schiesser_carte_tearoom( true );
		}
	}
	return $rapport;
}

/** Remet l'état d'avant un lot (pages, titres Google, présentation). */
function schiesser_corr_annuler( $version ) {
	$sauvegarde = (array) get_option( 'schiesser_corrections_sauvegarde', array() );
	if ( empty( $sauvegarde[ $version ] ) ) {
		return 0;
	}
	$n = 0;
	foreach ( (array) ( $sauvegarde[ $version ]['pages'] ?? array() ) as $id => $contenu ) {
		wp_update_post( array( 'ID' => (int) $id, 'post_content' => wp_slash( $contenu ) ) );
		++$n;
	}
	foreach ( (array) ( $sauvegarde[ $version ]['metas'] ?? array() ) as $id => $metas ) {
		foreach ( $metas as $cle => $valeur ) {
			update_post_meta( (int) $id, $cle, $valeur );
		}
	}
	if ( ! empty( $sauvegarde[ $version ]['reglages'] ) ) {
		$reglages = (array) get_option( SCHIESSER_OPTION, array() );
		update_option( SCHIESSER_OPTION, array_merge( $reglages, $sauvegarde[ $version ]['reglages'] ) );
	}
	unset( $sauvegarde[ $version ] );
	update_option( 'schiesser_corrections_sauvegarde', $sauvegarde, false );
	$faites = schiesser_corr_appliques();
	unset( $faites[ $version ] );
	update_option( 'schiesser_corrections_appliquees', $faites, false );
	schiesser_corr_migrer_rapport();
	$rapports = (array) get_option( 'schiesser_corrections_rapports', array() );
	unset( $rapports[ $version ] );
	update_option( 'schiesser_corrections_rapports', $rapports, false );
	return $n;
}

/* ------------------------------------------------------------------ */
/* Administration                                                      */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	if ( schiesser_corr_lots() ) {
		add_submenu_page( 'schiesser-reglages', 'Mises à jour des pages', 'Mises à jour des pages', 'manage_options', 'schiesser-corrections', 'schiesser_corr_page' );
	}
}, 30 );

/** Résumé d'un lot en une phrase. */
function schiesser_corr_resume( $d ) {
	if ( ! empty( $d['resume'] ) ) {
		return (string) $d['resume'];
	}
	return count( (array) ( $d['textes'] ?? array() ) ) . ' textes à mettre à jour dans les pages (allemand, français et anglais réunis). Vos photos et votre mise en page ne bougent pas.';
}

/* Avis : mises à jour prêtes, pas encore appliquées. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$ecran = get_current_screen();
	if ( ! $ecran || ! in_array( $ecran->id, array( 'dashboard', 'edit-page', 'toplevel_page_schiesser-reglages' ), true ) ) {
		return;
	}
	$a_faire = array_diff_key( schiesser_corr_lots(), schiesser_corr_appliques() );
	if ( ! $a_faire ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>Mises à jour des pages prêtes à appliquer :</strong></p><ul style="list-style:disc;margin-left:20px">';
	foreach ( $a_faire as $d ) {
		echo '<li><strong>' . esc_html( $d['titre'] ) . '.</strong> ' . esc_html( schiesser_corr_resume( $d ) ) . '</li>';
	}
	echo '</ul><p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=schiesser-corrections&lot=' . rawurlencode( (string) array_key_first( $a_faire ) ) ) ) . '">Voir et appliquer</a></p></div>';
} );

add_action( 'admin_post_schiesser_corrections', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_corrections' );
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$action  = sanitize_key( $_POST['faire'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	$version = sanitize_text_field( wp_unslash( $_POST['lot'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$etat    = '';
	if ( isset( schiesser_corr_lots()[ $version ] ) ) {
		if ( 'appliquer' === $action && ! isset( schiesser_corr_appliques()[ $version ] ) ) {
			schiesser_corr_executer( true, $version );
			$etat = 'applique';
		} elseif ( 'annuler' === $action && schiesser_corr_dernier_applique() === $version ) {
			schiesser_corr_annuler( $version );
			$etat = 'annule';
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=schiesser-corrections&lot=' . rawurlencode( $version ) . '&etat=' . $etat ) );
	exit;
} );

function schiesser_corr_page() {
	$lots    = schiesser_corr_lots();
	$faites  = schiesser_corr_appliques();
	$demande = sanitize_text_field( wp_unslash( $_GET['lot'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$d       = schiesser_corr_donnees( isset( $lots[ $demande ] ) ? $demande : '' );
	$version = (string) ( $d['version'] ?? '' );
	$fait    = isset( $faites[ $version ] );
	$rapport = $fait ? schiesser_corr_rapport( $version ) : schiesser_corr_executer( false, $version );
	$dernier = schiesser_corr_dernier_applique();
	$etats   = array( 'applique' => array( $fait ? 'Appliqué' : 'À appliquer', '#174633' ), 'deja' => array( 'Déjà en place', '#646970' ), 'introuvable' => array( 'À vérifier', '#b32d2e' ) );
	$types   = array( 'texte' => 'Texte', 'supprimer' => 'Élément retiré', 'masquer' => 'Section masquée', 'section' => 'Section masquée', 'reglage-bloc' => 'Réglage de bloc', 'produit' => 'Produit (langue)', 'photo' => 'Photo', 'google' => 'Google', 'reglage' => 'Réglage' );
	$etat    = sanitize_key( $_GET['etat'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	$lien    = function ( $v ) {
		return admin_url( 'admin.php?page=schiesser-corrections&lot=' . rawurlencode( $v ) );
	};
	?>
	<div class="wrap">
		<h1>Mises à jour des pages</h1>
		<?php if ( 'applique' === $etat ) : ?><div class="notice notice-success"><p><strong>Mise à jour appliquée.</strong> Les pages ont été mises à jour ; une copie de l’état d’avant est gardée (bouton en bas de page).</p></div><?php endif; ?>
		<?php if ( 'annule' === $etat ) : ?><div class="notice notice-warning"><p>Les pages, titres Google et la présentation ont retrouvé leur état d’avant cette mise à jour.</p></div><?php endif; ?>
		<p style="max-width:860px">Chaque mise à jour livrée avec le thème s’applique ici, sur les pages en allemand, français et anglais. Seul ce qui est listé change : vos photos et vos retouches restent les vôtres, et un élément que vous avez déjà modifié sur le site est laissé tel quel et signalé « À vérifier ». Une copie de l’état d’avant est gardée.</p>
		<?php if ( count( $lots ) > 1 ) : ?>
			<table class="widefat striped" style="max-width:1200px;margin:18px 0 28px">
				<thead><tr><th>Mise à jour</th><th style="width:110px">Livrée le</th><th style="width:230px">État</th><th style="width:90px"></th></tr></thead>
				<tbody>
				<?php foreach ( $lots as $v => $x ) : ?>
					<tr<?php echo $v === $version ? ' style="box-shadow:inset 3px 0 0 #174633"' : ''; ?>>
						<td><strong><?php echo esc_html( $x['titre'] ); ?></strong><br><small><?php echo esc_html( schiesser_corr_resume( $x ) ); ?></small></td>
						<td><?php echo esc_html( ! empty( $x['date'] ) ? mysql2date( 'd.m.Y', $x['date'] ) : '' ); ?></td>
						<td><?php echo isset( $faites[ $v ] ) ? '<span style="color:#174633">✓ Appliquée le ' . esc_html( mysql2date( 'd.m.Y à H:i', $faites[ $v ]['date'] ) ) . '</span>' : '<strong style="color:#b26200">À appliquer</strong>'; ?></td>
						<td><?php echo $v === $version ? '<em>affichée</em>' : '<a href="' . esc_url( $lien( $v ) ) . '">Voir</a>'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<h2><?php echo esc_html( $d['titre'] ?? '' ); ?></h2>
		<p style="max-width:860px"><?php echo esc_html( schiesser_corr_resume( $d ) ); ?></p>
		<p>
			<?php foreach ( $etats as $cle => $e ) : ?>
				<span style="display:inline-block;margin-right:18px"><strong style="color:<?php echo esc_attr( $e[1] ); ?>"><?php echo esc_html( $rapport['compte'][ $cle ] ?? 0 ); ?></strong> <?php echo esc_html( $e[0] ); ?></span>
			<?php endforeach; ?>
			<?php if ( ! empty( $rapport['pages'] ) ) : ?><br><small><?php echo esc_html( ( $fait ? 'Pages mises à jour : ' : 'Pages concernées : ' ) . implode( ' · ', $rapport['pages'] ) ); ?></small><?php endif; ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:16px 0">
			<input type="hidden" name="action" value="schiesser_corrections">
			<input type="hidden" name="lot" value="<?php echo esc_attr( $version ); ?>">
			<?php wp_nonce_field( 'schiesser_corrections' ); ?>
			<?php if ( ! $fait ) : ?>
				<button class="button button-primary button-hero" name="faire" value="appliquer" onclick="return confirm('Appliquer cette mise à jour sur les pages du site (allemand, français, anglais) ?');">Appliquer cette mise à jour</button>
			<?php else : ?>
				<p><strong>Appliquée le <?php echo esc_html( mysql2date( 'd.m.Y à H:i', $faites[ $version ]['date'] ) ); ?>.</strong></p>
			<?php endif; ?>
		</form>
		<table class="widefat striped" style="max-width:1200px">
			<thead><tr><th style="width:90px">Réf.</th><th style="width:60px">Langue</th><th style="width:130px">Type</th><th style="width:120px">État</th><th>Détail</th></tr></thead>
			<tbody>
			<?php
			$lignes = (array) ( $rapport['lignes'] ?? array() );
			usort( $lignes, function ( $a, $b ) {
				$o = array( 'introuvable' => 0, 'applique' => 1, 'deja' => 2 );
				return array( $o[ $a[3] ] ?? 3, $a[1], $a[2] ) <=> array( $o[ $b[3] ] ?? 3, $b[1], $b[2] );
			} );
			foreach ( $lignes as $x ) :
				?>
				<tr><td><code><?php echo esc_html( $x[1] ); ?></code></td><td><?php echo esc_html( strtoupper( $x[2] ) ); ?></td><td><?php echo esc_html( $types[ $x[0] ] ?? $x[0] ); ?></td>
					<td><strong style="color:<?php echo esc_attr( $etats[ $x[3] ][1] ?? '#000' ); ?>"><?php echo esc_html( $etats[ $x[3] ][0] ?? $x[3] ); ?></strong></td><td><?php echo esc_html( $x[4] ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php if ( $fait && ! empty( get_option( 'schiesser_corrections_sauvegarde', array() )[ $version ] ) ) : ?>
			<?php if ( $dernier === $version ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:24px">
					<input type="hidden" name="action" value="schiesser_corrections">
					<input type="hidden" name="lot" value="<?php echo esc_attr( $version ); ?>">
					<?php wp_nonce_field( 'schiesser_corrections' ); ?>
					<button class="button" name="faire" value="annuler" onclick="return confirm('Remettre les pages, les titres Google et la présentation dans leur état d’avant cette mise à jour ? Les retouches faites depuis sur ces pages seraient perdues.');">Annuler cette mise à jour (état d’avant)</button>
				</form>
			<?php else : ?>
				<p style="margin-top:24px;color:#646970">Pour annuler cette mise à jour, annulez d’abord la plus récente : « <?php echo esc_html( $lots[ $dernier ]['titre'] ?? $dernier ); ?> ».</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

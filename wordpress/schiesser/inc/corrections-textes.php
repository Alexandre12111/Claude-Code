<?php
/**
 * Corrections de textes relues par le client (document Word), appliquées au site réel.
 *
 * Le fichier corrections/textes-*.json (construit par wordpress/textes/outils/construire-corrections.py)
 * liste, pour chaque texte : la page, l'emplacement du bloc, le réglage, la langue, l'ancien texte
 * et le nouveau. L'outil (Schiesser → Corrections de textes) :
 *
 * - retrouve chaque texte dans la page de sa langue, d'abord à son emplacement, sinon d'après son
 *   ancien texte (sections déplacées ou ajoutées) ; un texte déjà retouché sur le site est laissé tel quel ;
 * - ne remplace que le texte : photos, mise en page et réglages des blocs ne bougent pas, et les
 *   liens internes de l'ancien texte sont remis sur les mêmes mots ;
 * - supprime les éléments vidés par le client, masque les sections demandées (réversible) ;
 * - met à jour les titres et descriptions Google et la présentation s'ils n'ont pas été retouchés ;
 * - garde une copie de tout ce qu'il change : un bouton remet l'état d'avant.
 */

defined( 'ABSPATH' ) || exit;

/** Fichier de corrections livré avec le thème (le plus récent). */
function schiesser_corr_fichier() {
	$fichiers = glob( SCHIESSER_DIR . '/corrections/textes-*.json' );
	if ( ! $fichiers ) {
		return null;
	}
	sort( $fichiers );
	return end( $fichiers );
}

function schiesser_corr_donnees() {
	static $d = null;
	if ( null === $d ) {
		$f = schiesser_corr_fichier();
		$d = $f ? json_decode( (string) file_get_contents( $f ), true ) : array();
	}
	return is_array( $d ) ? $d : array();
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
		if ( empty( $b['blockName'] ) || ! empty( $b['attrs']['afficherDu'] ) || ! isset( $b['attrs']['numero'] ) || ! preg_match( '/^\d{2}$/', (string) $b['attrs']['numero'] ) ) {
			continue;
		}
		$b['attrs']['numero'] = str_pad( (string) ++$n, 2, '0', STR_PAD_LEFT );
	}
	unset( $b );
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
 * @param bool $appliquer false : aperçu, rien n'est écrit.
 * @return array Rapport : lignes [ type, ref, langue, état, détail ] et compteurs.
 */
function schiesser_corr_executer( $appliquer = false ) {
	$d       = schiesser_corr_donnees();
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
				foreach ( (array) ( $t['textes'] ?? array() ) as $c ) {
					$note( 'texte', $c['ref'], $l, 'introuvable', 'Page « ' . $slug . ' » introuvable en ' . strtoupper( $l ) );
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

			// 4. Comparateur : photo de 1889.
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

	// 5. Titres et descriptions Google (Rank Math), seulement s'ils sont encore ceux du thème.
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

	// 6. Présentation de la maison (Réglages), si c'est encore le texte du thème.
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

	if ( $appliquer ) {
		update_option( 'schiesser_corrections_sauvegarde', $sauvegarde, false );
		update_option( 'schiesser_corrections_appliquees', array( $version => array( 'date' => current_time( 'mysql' ), 'compte' => $rapport['compte'] ) ) + (array) get_option( 'schiesser_corrections_appliquees', array() ), false );
		update_option( 'schiesser_corrections_rapport', $rapport, false );
		if ( function_exists( 'schiesser_carte_tearoom' ) ) {
			schiesser_carte_tearoom( true );
		}
	}
	return $rapport;
}

/** Remet l'état d'avant les corrections (pages, titres Google, présentation). */
function schiesser_corr_annuler() {
	$d          = schiesser_corr_donnees();
	$version    = (string) ( $d['version'] ?? '' );
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
	$faites = (array) get_option( 'schiesser_corrections_appliquees', array() );
	unset( $faites[ $version ] );
	update_option( 'schiesser_corrections_appliquees', $faites, false );
	delete_option( 'schiesser_corrections_rapport' );
	return $n;
}

/* ------------------------------------------------------------------ */
/* Administration                                                      */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	if ( schiesser_corr_fichier() ) {
		add_submenu_page( 'schiesser-reglages', 'Corrections de textes', 'Corrections de textes', 'manage_options', 'schiesser-corrections', 'schiesser_corr_page' );
	}
}, 30 );

/* Avis : corrections prêtes, pas encore appliquées. */
add_action( 'admin_notices', function () {
	$d = schiesser_corr_donnees();
	if ( ! $d || ! current_user_can( 'manage_options' ) || isset( ( (array) get_option( 'schiesser_corrections_appliquees', array() ) )[ $d['version'] ] ) ) {
		return;
	}
	$ecran = get_current_screen();
	if ( ! $ecran || ! in_array( $ecran->id, array( 'dashboard', 'edit-page', 'toplevel_page_schiesser-reglages' ), true ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>' . esc_html( $d['titre'] ) . ' :</strong> ' . esc_html( count( $d['textes'] ) ) . ' textes à mettre à jour dans les pages (allemand, français et anglais réunis). Vos photos et votre mise en page ne bougent pas. <a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=schiesser-corrections' ) ) . '">Voir et appliquer</a></p></div>';
} );

add_action( 'admin_post_schiesser_corrections', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_corrections' );
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$action = sanitize_key( $_POST['faire'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'appliquer' === $action ) {
		schiesser_corr_executer( true );
		$etat = 'applique';
	} elseif ( 'annuler' === $action ) {
		schiesser_corr_annuler();
		$etat = 'annule';
	} else {
		$etat = '';
	}
	wp_safe_redirect( admin_url( 'admin.php?page=schiesser-corrections&etat=' . $etat ) );
	exit;
} );

function schiesser_corr_page() {
	$d = schiesser_corr_donnees();
	$faites  = (array) get_option( 'schiesser_corrections_appliquees', array() );
	$fait    = isset( $faites[ $d['version'] ?? '' ] );
	$rapport = $fait ? (array) get_option( 'schiesser_corrections_rapport', array() ) : schiesser_corr_executer( false );
	$etats   = array( 'applique' => array( $fait ? 'Appliqué' : 'À appliquer', '#174633' ), 'deja' => array( 'Déjà en place', '#646970' ), 'introuvable' => array( 'À vérifier', '#b32d2e' ) );
	$types   = array( 'texte' => 'Texte', 'supprimer' => 'Élément retiré', 'masquer' => 'Section masquée', 'photo' => 'Photo', 'google' => 'Google', 'reglage' => 'Réglage' );
	$etat    = sanitize_key( $_GET['etat'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1><?php echo esc_html( $d['titre'] ?? 'Corrections de textes' ); ?></h1>
		<?php if ( 'applique' === $etat ) : ?><div class="notice notice-success"><p><strong>Corrections appliquées.</strong> Les pages ont été mises à jour ; une copie de l’état d’avant est gardée (bouton en bas de page).</p></div><?php endif; ?>
		<?php if ( 'annule' === $etat ) : ?><div class="notice notice-warning"><p>Les pages, titres Google et la présentation ont retrouvé leur état d’avant les corrections.</p></div><?php endif; ?>
		<p style="max-width:860px">Chaque texte est cherché dans la page de sa langue : à son emplacement, sinon d’après son ancien texte. Seul le texte change : photos, mise en page et réglages des blocs restent les vôtres, et les liens internes sont remis sur les mêmes mots. Un texte que vous avez déjà retouché sur le site est laissé tel quel et signalé « À vérifier ».</p>
		<p>
			<?php foreach ( $etats as $cle => $e ) : ?>
				<span style="display:inline-block;margin-right:18px"><strong style="color:<?php echo esc_attr( $e[1] ); ?>"><?php echo esc_html( $rapport['compte'][ $cle ] ?? 0 ); ?></strong> <?php echo esc_html( $e[0] ); ?></span>
			<?php endforeach; ?>
			<?php if ( ! empty( $rapport['pages'] ) ) : ?><br><small><?php echo esc_html( ( $fait ? 'Pages mises à jour : ' : 'Pages concernées : ' ) . implode( ' · ', $rapport['pages'] ) ); ?></small><?php endif; ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:16px 0">
			<input type="hidden" name="action" value="schiesser_corrections">
			<?php wp_nonce_field( 'schiesser_corrections' ); ?>
			<?php if ( ! $fait ) : ?>
				<button class="button button-primary button-hero" name="faire" value="appliquer" onclick="return confirm('Appliquer les corrections sur les pages du site (allemand, français, anglais) ?');">Appliquer les corrections</button>
			<?php else : ?>
				<p><strong>Appliquées le <?php echo esc_html( mysql2date( 'd.m.Y à H:i', $faites[ $d['version'] ]['date'] ) ); ?>.</strong></p>
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
		<?php if ( $fait && ! empty( get_option( 'schiesser_corrections_sauvegarde', array() )[ $d['version'] ] ) ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:24px">
				<input type="hidden" name="action" value="schiesser_corrections">
				<?php wp_nonce_field( 'schiesser_corrections' ); ?>
				<button class="button" name="faire" value="annuler" onclick="return confirm('Remettre les pages, les titres Google et la présentation dans leur état d’avant les corrections ? Les retouches faites depuis sur ces pages seraient perdues.');">Annuler les corrections (état d’avant)</button>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

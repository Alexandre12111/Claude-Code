<?php
/**
 * Remise en ordre des langues (Polylang).
 *
 * Cas typique : à l'installation, Polylang propose la langue du tableau de bord (souvent le
 * français) comme langue par défaut et l'attribue à tous les contenus existants. Les produits,
 * la carte du Tea Room et les pages, écrits en allemand, se retrouvent alors marqués « Français »,
 * sans version allemande ni anglaise.
 *
 * La réparation retrouve la vraie langue de chaque contenu d'après son nom (allemand, français ou
 * anglais), relie les trois versions entre elles, met les doublons à la corbeille (récupérables)
 * puis crée les traductions qui manquent.
 */

defined( 'ABSPATH' ) || exit;

/** Titre lisible d'un contenu (entités décodées). */
function schiesser_rl_titre( $id ) {
	return trim( html_entity_decode( (string) get_post_field( 'post_title', $id ), ENT_QUOTES, 'UTF-8' ) );
}

/** Langue Polylang d'un contenu, sur deux lettres (« » si aucune). */
function schiesser_rl_langue( $id, $terme = false ) {
	$l = $terme
		? ( function_exists( 'pll_get_term_language' ) ? (string) pll_get_term_language( $id ) : '' )
		: ( function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : '' );
	return substr( $l, 0, 2 );
}

/**
 * Langue la plus probable d'un candidat : son nom s'il n'existe que dans une langue ; sinon, pour
 * un nom identique dans plusieurs langues (« Espresso », « Glace »), la langue de sa catégorie,
 * le suffixe de son adresse (-fr, -en) ou, en dernier recours, sa langue actuelle.
 */
function schiesser_rl_indice( $id, $titre, $noms, $terme = false ) {
	$langues = array_keys( $noms, $titre, true );
	if ( 1 === count( $langues ) ) {
		return $langues[0];
	}
	if ( $terme ) {
		$t    = get_term( $id );
		$slug = $t && ! is_wp_error( $t ) ? $t->slug : '';
	} else {
		$taxo = get_post_type( $id ) === SCHIESSER_TEAROOM ? SCHIESSER_RUBRIQUE : SCHIESSER_CATEGORIE;
		foreach ( (array) wp_get_object_terms( $id, $taxo, array( 'fields' => 'ids', 'lang' => '' ) ) as $t ) {
			$l = schiesser_rl_langue( (int) $t, true );
			if ( in_array( $l, $langues, true ) ) {
				return $l;
			}
		}
		$slug = (string) get_post_field( 'post_name', $id );
	}
	foreach ( $langues as $l ) {
		if ( 'de' !== $l && preg_match( '/-' . $l . '(-\d+)?$/', $slug ) ) {
			return $l;
		}
	}
	$actuelle = schiesser_rl_langue( $id, $terme );
	return in_array( $actuelle, $langues, true ) && 'fr' !== $actuelle ? $actuelle : 'de';
}

/**
 * Choisit une version par langue parmi des candidats.
 *
 * @param array $candidats id => titre.
 * @param array $noms      langue => nom attendu.
 * @param bool  $terme     Termes (catégories) plutôt que contenus.
 * @return array langue => id.
 */
function schiesser_rl_choisir( $candidats, $noms, $terme = false ) {
	$choix   = array();
	$indices = array();
	foreach ( $candidats as $id => $titre ) {
		$indices[ $id ] = schiesser_rl_indice( $id, $titre, $noms, $terme );
	}
	// 1. Le nom et l'indice de langue concordent (déjà dans la bonne langue de préférence).
	foreach ( $noms as $l => $nom ) {
		$retenu = 0;
		foreach ( $candidats as $id => $titre ) {
			if ( $titre !== $nom || $indices[ $id ] !== $l || in_array( $id, $choix, true ) ) {
				continue;
			}
			if ( ! $retenu || schiesser_rl_langue( $id, $terme ) === $l ) {
				$retenu = $id;
			}
			if ( schiesser_rl_langue( $id, $terme ) === $l ) {
				break;
			}
		}
		if ( $retenu ) {
			$choix[ $l ] = $retenu;
		}
	}
	// 2. Le nom seul : la langue sera corrigée (le plus ancien d'abord).
	foreach ( $noms as $l => $nom ) {
		if ( isset( $choix[ $l ] ) ) {
			continue;
		}
		foreach ( $candidats as $id => $titre ) {
			if ( $titre === $nom && ! in_array( $id, $choix, true ) ) {
				$choix[ $l ] = $id;
				break;
			}
		}
	}
	return $choix;
}

/** Applique les langues et relie les versions d'un contenu. */
function schiesser_rl_relier( $choix, $terme = false ) {
	if ( ! $choix ) {
		return 0;
	}
	$groupe   = array();
	$corriges = 0;
	foreach ( $choix as $l => $id ) {
		$slug = schiesser_slug_polylang( $l );
		if ( schiesser_rl_langue( $id, $terme ) !== $l ) {
			$terme ? pll_set_term_language( $id, $slug ) : pll_set_post_language( $id, $slug );
			++$corriges;
		}
		$groupe[ $slug ] = (int) $id;
	}
	if ( count( $groupe ) > 1 ) {
		$terme ? pll_save_term_translations( $groupe ) : pll_save_post_translations( $groupe );
	}
	return $corriges;
}

/** Tous les contenus d'un type, toutes langues : id => titre. */
function schiesser_rl_contenus( $type ) {
	$liste = array();
	foreach ( get_posts( array( 'post_type' => $type, 'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ), 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'orderby' => 'ID', 'order' => 'ASC' ) ) as $id ) {
		$liste[ (int) $id ] = schiesser_rl_titre( $id );
	}
	return $liste;
}

/** Noms d'un élément dans les langues actives : langue => nom. */
function schiesser_rl_noms( $dico, $de, $langues ) {
	$noms = array( 'de' => $de );
	foreach ( $langues as $l ) {
		$noms[ $l ] = schiesser_tr( $dico, $de, $l );
	}
	return $noms;
}

/** Catégories ou rubriques : langue retrouvée d'après le nom. Renvoie de => id du terme allemand. */
function schiesser_rl_termes( $taxonomie, $noms_de, $dico, $langues, &$bilan ) {
	$tous = get_terms( array( 'taxonomy' => $taxonomie, 'hide_empty' => false, 'lang' => '', 'orderby' => 'term_id' ) );
	$tous = is_wp_error( $tous ) ? array() : $tous;
	$out  = array();
	foreach ( $noms_de as $de ) {
		$noms       = schiesser_rl_noms( $dico, $de, $langues );
		$candidats  = array();
		foreach ( $tous as $t ) {
			$nom = html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' );
			if ( in_array( $nom, $noms, true ) ) {
				$candidats[ (int) $t->term_id ] = $nom;
			}
		}
		$choix            = schiesser_rl_choisir( $candidats, $noms, true );
		$bilan['termes'] += schiesser_rl_relier( $choix, true );
		$out[ $de ]       = $choix;
	}
	return $out;
}

/**
 * Réparation complète. Renvoie un bilan (nombre de langues corrigées, doublons à la corbeille).
 */
function schiesser_reparer_langues( $creer_traductions = true ) {
	$bilan   = array( 'contenus' => 0, 'termes' => 0, 'corbeille' => 0, 'pages' => 0 );
	$langues = function_exists( 'schiesser_langues_a_traduire' ) ? schiesser_langues_a_traduire() : array();
	if ( ! $langues || ! function_exists( 'pll_set_post_language' ) ) {
		return $bilan;
	}

	/* ---- Boutique ---- */
	$dico      = schiesser_tr_boutique();
	$categories = schiesser_rl_termes( SCHIESSER_CATEGORIE, array_keys( schiesser_preisliste() ), $dico, $langues, $bilan );
	$produits  = schiesser_rl_contenus( SCHIESSER_PRODUIT );
	foreach ( schiesser_preisliste() as $rubrique => $liste ) {
		foreach ( $liste as $p ) {
			$noms      = schiesser_rl_noms( $dico, $p[0], $langues );
			$candidats = array_filter( $produits, function ( $titre ) use ( $noms ) {
				return in_array( $titre, $noms, true );
			} );
			$bilan     = schiesser_rl_traiter( $candidats, $noms, $produits, $bilan );
		}
	}

	/* ---- Carte du Tea Room (un même nom peut exister dans deux rubriques : on reste dans la rubrique) ---- */
	$dico     = schiesser_tr_carte();
	$karte    = schiesser_getraenkekarte();
	$rubriques = schiesser_rl_termes( SCHIESSER_RUBRIQUE, wp_list_pluck( $karte, 'nom' ), $dico, $langues, $bilan );
	$plats    = schiesser_rl_contenus( SCHIESSER_TEAROOM );
	foreach ( $karte as $r ) {
		$termes = array_values( $rubriques[ $r['nom'] ] ?? array() );
		foreach ( $r['plats'] as $p ) {
			$noms      = schiesser_rl_noms( $dico, $p[0], $langues );
			$candidats = array();
			foreach ( $plats as $id => $titre ) {
				if ( ! in_array( $titre, $noms, true ) ) {
					continue;
				}
				$siens = (array) wp_get_object_terms( $id, SCHIESSER_RUBRIQUE, array( 'fields' => 'ids', 'lang' => '' ) );
				if ( ! $siens || ! $termes || array_intersect( array_map( 'intval', $siens ), $termes ) ) {
					$candidats[ $id ] = $titre;
				}
			}
			$bilan = schiesser_rl_traiter( $candidats, $noms, $plats, $bilan );
		}
	}

	/* ---- Pages (d'après leur adresse ; jamais mises à la corbeille) ---- */
	$meta = schiesser_tr_pages();
	$ids  = array();
	foreach ( schiesser_demo_pages() as $p ) {
		$slugs = array( 'de' => $p['slug'] );
		foreach ( $langues as $l ) {
			if ( ! empty( $meta[ $p['slug'] ][ $l ][1] ) ) {
				$slugs[ $l ] = $meta[ $p['slug'] ][ $l ][1];
			}
		}
		$choix = array();
		foreach ( $slugs as $l => $slug ) {
			$trouves = get_posts( array( 'post_type' => 'page', 'name' => $slug, 'post_status' => array( 'publish', 'draft', 'private', 'pending' ), 'numberposts' => -1, 'fields' => 'ids', 'lang' => '', 'orderby' => 'ID', 'order' => 'ASC' ) );
			if ( $trouves ) {
				$choix[ $l ] = (int) $trouves[0];
			}
		}
		$bilan['pages'] += schiesser_rl_relier( $choix );
		if ( isset( $choix['de'] ) ) {
			$ids[ $p['slug'] ] = $choix['de'];
		}
	}

	/* ---- Traductions manquantes (FR, EN) ---- */
	if ( $creer_traductions ) {
		schiesser_importer_langues( $ids, false );
	}
	/* ---- Chaque produit et chaque plat dans la rubrique de sa langue (pas « Kaffee » sur un plat français) ---- */
	$bilan['termes'] += schiesser_rl_rattacher();
	update_option( 'schiesser_langues_reparees', time() );
	return $bilan;
}

/** Contenu allemand créé par l'import : toujours « Deutsch », même si Polylang a une autre langue par défaut. */
function schiesser_forcer_allemand( $id, $terme = false ) {
	if ( ! $id || is_wp_error( $id ) || ! function_exists( 'schiesser_langues_a_traduire' ) || ! schiesser_langues_a_traduire() ) {
		return;
	}
	$de = schiesser_slug_polylang( 'de' );
	if ( $terme && function_exists( 'pll_set_term_language' ) ) {
		pll_set_term_language( (int) $id, $de );
	} elseif ( ! $terme && function_exists( 'pll_set_post_language' ) ) {
		pll_set_post_language( (int) $id, $de );
	}
}

/** Relie les versions d'un produit ou d'un plat ; les autres exemplaires du même nom partent à la corbeille. */
function schiesser_rl_traiter( $candidats, $noms, &$tous, $bilan ) {
	if ( ! $candidats ) {
		return $bilan;
	}
	// Si deux langues portent le même nom (ex. « Espresso »), chaque exemplaire garde une langue.
	$choix              = schiesser_rl_choisir( $candidats, $noms );
	$bilan['contenus'] += schiesser_rl_relier( $choix );
	foreach ( array_diff( array_keys( $candidats ), array_values( $choix ) ) as $doublon ) {
		if ( wp_trash_post( $doublon ) ) {
			++$bilan['corbeille'];
		}
	}
	// Déjà traités : ne servent plus de candidats pour un autre élément du même nom.
	foreach ( array_keys( $candidats ) as $id ) {
		unset( $tous[ $id ] );
	}
	return $bilan;
}

/** Les langues sont-elles en ordre ? Nombre de produits et de plats par langue (toutes versions). */
function schiesser_rl_diagnostic() {
	$langues = function_exists( 'schiesser_langues_a_traduire' ) ? schiesser_langues_a_traduire() : array();
	if ( ! $langues ) {
		return array();
	}
	$compte = array();
	foreach ( array( SCHIESSER_PRODUIT, SCHIESSER_TEAROOM ) as $type ) {
		foreach ( array_merge( array( 'de' ), $langues ) as $l ) {
			$compte[ $type ][ $l ] = 0;
		}
		foreach ( schiesser_rl_contenus( $type ) as $id => $titre ) {
			$l = schiesser_rl_langue( $id );
			if ( isset( $compte[ $type ][ $l ] ) ) {
				++$compte[ $type ][ $l ];
			}
		}
	}
	return $compte;
}

/**
 * Produits et plats rangés dans une catégorie ou rubrique d'une autre langue.
 *
 * @param bool $corriger Les rattacher à la traduction de la rubrique dans leur langue.
 * @return int Nombre de contenus concernés.
 */
function schiesser_rl_rattacher( $corriger = true ) {
	if ( ! function_exists( 'pll_get_term' ) || ! schiesser_langues_a_traduire() ) {
		return 0;
	}
	$n = 0;
	foreach ( array( SCHIESSER_PRODUIT => array( SCHIESSER_CATEGORIE, 'boutique' ), SCHIESSER_TEAROOM => array( SCHIESSER_RUBRIQUE, 'carte' ) ) as $type => $infos ) {
		list( $taxo, $genre ) = $infos;
		// Termes connus : nom de référence (allemand) => langue => terme.
		$par_ref = array();
		$tous    = get_terms( array( 'taxonomy' => $taxo, 'hide_empty' => false, 'lang' => '', 'orderby' => 'term_id' ) );
		foreach ( is_wp_error( $tous ) ? array() : $tous as $t ) {
			$ref = schiesser_rubrique_reference( $t->name, $genre );
			$lt  = schiesser_rl_langue( $t->term_id, true ) ?: 'de';
			if ( $ref && ! isset( $par_ref[ $ref ][ $lt ] ) ) {
				$par_ref[ $ref ][ $lt ] = (int) $t->term_id;
			}
		}
		foreach ( array_keys( schiesser_rl_contenus( $type ) ) as $id ) {
			$l = schiesser_rl_langue( $id );
			if ( '' === $l ) {
				continue;
			}
			$avant = (array) wp_get_object_terms( $id, $taxo, array( 'lang' => '' ) );
			$apres = array();
			foreach ( $avant as $t ) {
				$lt  = schiesser_rl_langue( $t->term_id, true );
				$ref = schiesser_rubrique_reference( $t->name, $genre );
				if ( $ref ) {
					// Rubrique connue : sa version dans la langue du contenu (créée au besoin).
					if ( empty( $par_ref[ $ref ][ $l ] ) && $corriger && ! empty( $par_ref[ $ref ]['de'] ) && 'de' !== $l ) {
						$nouveau = schiesser_traduire_terme( $par_ref[ $ref ]['de'], $taxo, $l, schiesser_rubrique_nom_langue( $ref, $genre, $l ) );
						if ( $nouveau ) {
							$par_ref[ $ref ][ $l ] = $nouveau;
						}
					}
					$apres[] = $par_ref[ $ref ][ $l ] ?? (int) $t->term_id;
				} elseif ( '' === $lt || $lt === $l ) {
					$apres[] = (int) $t->term_id;
				} else {
					$tr      = (int) pll_get_term( $t->term_id, schiesser_slug_polylang( $l ) );
					$apres[] = $tr ?: (int) $t->term_id;
				}
			}
			$avant = array_map( 'intval', wp_list_pluck( $avant, 'term_id' ) );
			$apres = array_values( array_unique( $apres ) );
			sort( $avant );
			sort( $apres );
			if ( $apres && $avant !== $apres ) {
				++$n;
				if ( $corriger ) {
					wp_set_object_terms( $id, $apres, $taxo );
				}
			}
		}
	}
	return $n;
}

/** Vrai si une langue a nettement moins de contenus que l'allemand (ou l'allemand aucun). */
function schiesser_rl_desordre( $compte ) {
	foreach ( $compte as $par_langue ) {
		$de = $par_langue['de'] ?? 0;
		foreach ( $par_langue as $l => $n ) {
			if ( ( 'de' === $l && 0 === $n && array_sum( $par_langue ) > 0 ) || ( 'de' !== $l && $n !== $de ) ) {
				return true;
			}
		}
	}
	return false;
}

/* L'état des langues et le bouton de réparation sont dans Réglages maison → Entretien (inc/entretien.php). */
add_action( 'admin_post_schiesser_reparer_langues', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_reparer_langues' );
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$b = schiesser_reparer_langues();
	wp_safe_redirect( schiesser_url_entretien( array( 'fait' => 'langues', 'b' => implode( '-', array( $b['contenus'], $b['termes'], $b['corbeille'], $b['pages'] ) ) ) ) );
	exit;
} );


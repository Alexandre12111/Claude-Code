<?php
/**
 * Import des versions française et anglaise (avec Polylang).
 *
 * Appelé à la fin de l'import du contenu allemand (inc/demo.php), seulement si Polylang est actif
 * et que les langues Français et/ou English y ont été créées. Pour chaque page, produit, plat du
 * Tea Room, catégorie et rubrique allemands, la traduction est créée (ou mise à jour) puis reliée
 * à l'original : le sélecteur de langue passe ainsi d'une version à l'autre.
 *
 * Textes : inc/contenu-traductions.php.
 */

defined( 'ABSPATH' ) || exit;

/** Langues à créer : celles du thème présentes dans Polylang, sauf l'allemand. */
function schiesser_langues_a_traduire() {
	if ( ! schiesser_polylang() || ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) {
		return array();
	}
	$actives = schiesser_langues_actives();
	if ( ! isset( $actives['de'] ) ) {
		return array(); // l'allemand doit exister : c'est la langue principale
	}
	return array_values( array_diff( array_keys( $actives ), array( 'de' ) ) );
}

/** Traduction d'un texte d'un dictionnaire allemand => [ fr, en ] (le texte allemand s'il manque). */
function schiesser_tr( $dico, $de, $l ) {
	$i = 'fr' === $l ? 0 : 1;
	return isset( $dico[ $de ][ $i ] ) ? $dico[ $de ][ $i ] : $de;
}

/** Donne la langue allemande à un contenu qui n'en a pas encore. */
function schiesser_langue_de_si_vide( $id, $terme = false ) {
	$de = schiesser_slug_polylang( 'de' );
	if ( $terme ) {
		if ( function_exists( 'pll_get_term_language' ) && ! pll_get_term_language( $id ) ) {
			pll_set_term_language( $id, $de );
		}
		return;
	}
	if ( function_exists( 'pll_get_post_language' ) && ! pll_get_post_language( $id ) ) {
		pll_set_post_language( $id, $de );
	}
}

/**
 * Crée ou met à jour la traduction d'un contenu.
 *
 * @param int    $id_de   Contenu allemand.
 * @param string $l       Langue (fr, en).
 * @param array  $donnees Champs de wp_insert_post (titre, adresse…).
 * @param array  $metas   Métadonnées propres à la langue.
 * @return int Identifiant de la traduction (0 en cas d'échec).
 */
function schiesser_traduire_contenu( $id_de, $l, $donnees, $metas = array() ) {
	schiesser_langue_de_si_vide( $id_de );
	$slug_l = schiesser_slug_polylang( $l );
	$id     = (int) pll_get_post( $id_de, $slug_l );
	$base   = array(
		'post_type'   => get_post_type( $id_de ),
		'post_status' => get_post_status( $id_de ),
		'menu_order'  => (int) get_post_field( 'menu_order', $id_de ),
	);
	$donnees = array_merge( $base, $donnees );
	if ( $id && get_post( $id ) ) {
		$donnees['ID']          = $id;
		$donnees['post_status'] = get_post_status( $id ); // un brouillon reste un brouillon
		wp_update_post( wp_slash( $donnees ) );
	} else {
		$id = (int) wp_insert_post( wp_slash( $donnees ) );
		if ( ! $id ) {
			return 0;
		}
	}
	pll_set_post_language( $id, $slug_l );
	$trads        = function_exists( 'pll_get_post_translations' ) ? (array) pll_get_post_translations( $id_de ) : array();
	$trads[ schiesser_slug_polylang( 'de' ) ] = $id_de;
	$trads[ $slug_l ]                         = $id;
	pll_save_post_translations( $trads );
	foreach ( $metas as $cle => $valeur ) {
		update_post_meta( $id, $cle, $valeur );
	}
	// Prix, photos, allergènes : repris de l'original.
	$communes = array_merge( schiesser_metas_communes( get_post_type( $id_de ) ), array( '_s_preisliste', '_t_suggestion' ) );
	foreach ( array_unique( $communes ) as $cle ) {
		$v = get_post_meta( $id_de, $cle, true );
		if ( '_thumbnail_id' === $cle && get_post_meta( $id, $cle, true ) ) {
			continue; // photo déjà choisie sur cette version : on n'y touche pas
		}
		if ( '' === $v || array() === $v ) {
			delete_post_meta( $id, $cle );
		} else {
			update_post_meta( $id, $cle, $v );
		}
	}
	return $id;
}

/** Crée ou met à jour la traduction d'une catégorie ou d'une rubrique (photo et ordre repris). */
function schiesser_traduire_terme( $term_id, $taxonomie, $l, $nom ) {
	if ( ! function_exists( 'pll_get_term' ) || ! function_exists( 'pll_set_term_language' ) ) {
		return 0;
	}
	schiesser_langue_de_si_vide( $term_id, true );
	$slug_l = schiesser_slug_polylang( $l );
	$id     = (int) pll_get_term( $term_id, $slug_l );
	if ( $id && get_term( $id, $taxonomie ) ) {
		wp_update_term( $id, $taxonomie, array( 'name' => $nom ) );
	} else {
		$r = wp_insert_term( $nom, $taxonomie, array( 'slug' => sanitize_title( $nom . '-' . $l ) ) );
		if ( is_wp_error( $r ) ) {
			return 0;
		}
		$id = (int) $r['term_id'];
	}
	pll_set_term_language( $id, $slug_l );
	$trads = function_exists( 'pll_get_term_translations' ) ? (array) pll_get_term_translations( $term_id ) : array();
	$trads[ schiesser_slug_polylang( 'de' ) ] = $term_id;
	$trads[ $slug_l ]                         = $id;
	pll_save_term_translations( $trads );
	foreach ( array( 'photo', 'ordre' ) as $cle ) {
		$v = get_term_meta( $term_id, $cle, true );
		if ( 'photo' === $cle && get_term_meta( $id, $cle, true ) ) {
			continue; // photo de rubrique déjà choisie dans cette langue
		}
		if ( '' !== $v ) {
			update_term_meta( $id, $cle, $v );
		}
	}
	return $id;
}

/** Traductions des termes d'un contenu allemand, dans la langue $l. */
function schiesser_termes_traduits( $id_de, $taxonomie, $l ) {
	$ids = array();
	foreach ( (array) wp_get_object_terms( $id_de, $taxonomie, array( 'fields' => 'ids' ) ) as $t ) {
		$tr = function_exists( 'pll_get_term' ) ? (int) pll_get_term( (int) $t, schiesser_slug_polylang( $l ) ) : 0;
		if ( $tr ) {
			$ids[] = $tr;
		}
	}
	return $ids;
}

/* ------------------------------------------------------------------ */
/* Produits de la boutique                                             */
/* ------------------------------------------------------------------ */

function schiesser_importer_preisliste_langues( $langues ) {
	$dico   = schiesser_tr_boutique();
	$themes = schiesser_tr_themes_seo();
	foreach ( schiesser_preisliste() as $rubrique => $produits ) {
		$cat = get_term_by( 'name', $rubrique, SCHIESSER_CATEGORIE );
		if ( ! $cat ) {
			continue;
		}
		foreach ( $langues as $l ) {
			schiesser_traduire_terme( $cat->term_id, SCHIESSER_CATEGORIE, $l, schiesser_tr( $dico, $rubrique, $l ) );
		}
		foreach ( $produits as $p ) {
			list( $nom, $formats, $accroche ) = $p;
			$de = schiesser_posts_allemands( array( 'post_type' => SCHIESSER_PRODUIT, 'title' => $nom, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'numberposts' => 1, 'fields' => 'ids' ) );
			if ( ! $de ) {
				continue;
			}
			$id_de = (int) $de[0];
			foreach ( $langues as $l ) {
				$fr       = 'fr' === $l;
				$nom_l    = schiesser_tr( $dico, $nom, $l );
				$existant = (int) pll_get_post( $id_de, schiesser_slug_polylang( $l ) );
				$metas    = array( '_s_accroche' => '' === $accroche ? '' : schiesser_tr( $dico, $accroche, $l ) );
				// SEO de base, dans la langue (remplacé seulement s'il est encore automatique).
				$prix   = (string) get_post_meta( $id_de, '_s_prix', true );
				$prix_l = preg_replace( '/^ab\s+/u', $fr ? 'dès ' : 'from ', $prix );
				$titre  = $nom_l . ( $fr ? ' | Confiserie Schiesser, Marktplatz Bâle' : ' | Confiserie Schiesser, Marktplatz Basel' );
				if ( mb_strlen( $titre ) > 60 ) {
					$titre = $nom_l . ( $fr ? ' | Confiserie Schiesser Bâle' : ' | Confiserie Schiesser Basel' );
				}
				$n    = count( $formats );
				$desc = $fr
					? $nom_l . ' de la Confiserie Schiesser, sur le Marktplatz de Bâle : ' . ( 1 === $n ? $prix_l : 'en ' . $n . ' formats, ' . $prix_l ) . '. En boutique, commande par téléphone.'
					: $nom_l . ' from Confiserie Schiesser on the Marktplatz in Basel: ' . ( 1 === $n ? $prix_l : 'in ' . $n . ' sizes, ' . $prix_l ) . '. Available in the shop, order by phone.';
				if ( mb_strlen( $desc ) < 140 ) {
					$desc = $fr ? str_replace( '. En boutique', '. Fait main, en boutique', $desc ) : str_replace( '. Available', '. Handmade, available', $desc );
				}
				$ancien = $existant ? (string) get_post_meta( $existant, 'rank_math_title', true ) : '';
				if ( ! $existant || '' === $ancien || $ancien === $titre ) {
					$theme = $themes[ $rubrique ][ $fr ? 1 : 2 ] ?? ( $fr ? 'confiserie Bâle' : 'confectionery Basel' );
					$ville = $fr ? 'Bâle' : 'Basel';
					$mots  = array( $nom_l, false !== mb_stripos( $nom_l, $ville ) ? $nom_l . ( $fr ? ' cadeau' : ' gift' ) : $nom_l . ' ' . $ville, ( $fr ? 'acheter ' : 'buy ' ) . $nom_l, $theme, 'Confiserie Schiesser ' . $nom_l );
					$metas['rank_math_title']         = $titre;
					$metas['rank_math_description']   = $desc;
					$metas['rank_math_focus_keyword'] = implode( ',', array_slice( array_unique( $mots ), 0, 5 ) );
				}
				$donnees = array( 'post_title' => $nom_l );
				if ( $existant ) {
					unset( $donnees['post_title'] ); // le nom peut avoir été retouché à la main : on n'y touche plus
				} else {
					$donnees['post_name'] = sanitize_title( $nom_l );
				}
				$id = schiesser_traduire_contenu( $id_de, $l, $donnees, $metas );
				if ( $id ) {
					wp_set_object_terms( $id, schiesser_termes_traduits( $id_de, SCHIESSER_CATEGORIE, $l ), SCHIESSER_CATEGORIE );
				}
			}
		}
	}
}

/* ------------------------------------------------------------------ */
/* Carte du Tea Room                                                   */
/* ------------------------------------------------------------------ */

function schiesser_importer_tearoom_langues( $langues ) {
	$dico = schiesser_tr_carte();
	foreach ( schiesser_getraenkekarte() as $r ) {
		$rub = get_term_by( 'name', $r['nom'], SCHIESSER_RUBRIQUE );
		if ( ! $rub ) {
			continue;
		}
		foreach ( $langues as $l ) {
			schiesser_traduire_terme( $rub->term_id, SCHIESSER_RUBRIQUE, $l, schiesser_tr( $dico, $r['nom'], $l ) );
		}
		$plats_de = schiesser_posts_allemands( array( 'post_type' => SCHIESSER_TEAROOM, 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => -1, 'tax_query' => array( array( 'taxonomy' => SCHIESSER_RUBRIQUE, 'terms' => $rub->term_id ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		foreach ( $r['plats'] as $p ) {
			$id_de = 0;
			foreach ( $plats_de as $x ) {
				if ( html_entity_decode( $x->post_title, ENT_QUOTES, 'UTF-8' ) === $p[0] ) {
					$id_de = (int) $x->ID;
					break;
				}
			}
			if ( ! $id_de ) {
				continue;
			}
			foreach ( $langues as $l ) {
				$existant = (int) pll_get_post( $id_de, schiesser_slug_polylang( $l ) );
				$donnees  = $existant ? array() : array( 'post_title' => schiesser_tr( $dico, $p[0], $l ) );
				$metas    = array(
					'_t_description' => '' === $p[2] ? '' : schiesser_tr( $dico, $p[2], $l ),
					'_t_mention'     => empty( $p[3] ) ? '' : schiesser_tr( $dico, $p[3], $l ),
				);
				if ( $existant ) {
					$donnees['post_title'] = get_post_field( 'post_title', $existant );
				}
				$id = schiesser_traduire_contenu( $id_de, $l, $donnees, $metas );
				if ( $id ) {
					wp_set_object_terms( $id, schiesser_termes_traduits( $id_de, SCHIESSER_RUBRIQUE, $l ), SCHIESSER_RUBRIQUE );
				}
			}
		}
	}
}

/* ------------------------------------------------------------------ */
/* Pages                                                               */
/* ------------------------------------------------------------------ */

/**
 * Contenu d'une page traduit : chaque texte des blocs (attributs et paragraphes) est remplacé
 * par sa version française ou anglaise ; les liens internes restent à corriger (voir plus bas).
 */
function schiesser_traduire_blocs( $html, $l ) {
	$h     = untrailingslashit( home_url() );
	$dico  = schiesser_tr_pages_textes();
	$extra = schiesser_tr_mailto( $l );
	$t     = function ( $s ) use ( $dico, $l, $h, $extra ) {
		if ( isset( $extra[ $s ] ) ) {
			return $extra[ $s ];
		}
		$k = str_replace( $h, '{H}', $s );
		return isset( $dico[ $k ] ) ? schiesser_tr( $dico, $k, $l ) : $s;
	};
	$parcourir = function ( $blocs ) use ( &$parcourir, $t, $l ) {
		foreach ( $blocs as &$b ) {
			foreach ( (array) $b['attrs'] as $k => $v ) {
				if ( is_string( $v ) ) {
					if ( 'produits' === $k && preg_match( '/^[\d,\s]+$/', $v ) ) { // produits choisis : leurs traductions
						$b['attrs'][ $k ] = implode( ',', array_map( function ( $id ) use ( $l ) {
							return schiesser_post_traduit( (int) $id, $l );
						}, array_filter( array_map( 'trim', explode( ',', $v ) ) ) ) );
					} else {
						$b['attrs'][ $k ] = $t( $v );
					}
				} elseif ( is_array( $v ) ) {
					array_walk_recursive( $v, function ( &$x ) use ( $t ) {
						if ( is_string( $x ) ) {
							$x = $t( $x );
						}
					} );
					$b['attrs'][ $k ] = $v;
				}
			}
			if ( ! empty( $b['innerContent'] ) ) {
				foreach ( $b['innerContent'] as $i => $morceau ) {
					if ( is_string( $morceau ) && '' !== trim( $morceau ) ) {
						$tr = $t( trim( $morceau ) );
						if ( $tr !== trim( $morceau ) ) {
							$b['innerContent'][ $i ] = str_replace( trim( $morceau ), $tr, $morceau );
						}
					}
				}
			}
			if ( ! empty( $b['innerBlocks'] ) ) {
				$b['innerBlocks'] = $parcourir( $b['innerBlocks'] );
			}
		}
		return $blocs;
	};
	$html = serialize_blocks( $parcourir( parse_blocks( $html ) ) );
	return str_replace( $h, '{H}', $html );
}

/** Lien « Demander une offre » (e-mail prérempli) des cadeaux d'entreprise, dans chaque langue. */
function schiesser_tr_mailto( $l ) {
	$email = schiesser_reglage( 'email' );
	if ( ! $email ) {
		return array();
	}
	$faire = function ( $sujet, $lignes, $bonjour, $merci ) use ( $email ) {
		return 'mailto:' . $email . '?subject=' . rawurlencode( $sujet ) . '&body=' . rawurlencode( $bonjour . "\r\n\r\n" . implode( "\r\n", $lignes ) . "\r\n\r\n" . $merci . "\r\n" );
	};
	$de = $faire( 'Offertanfrage: Firmengeschenke', array(
		'Firma:',
		'Anzahl und Format (z. B. Pralinen 9er- oder 16er-Box, Läckerli-Box, Bonbonnière):',
		'Gewünschtes Übergabedatum:',
		'Botschaft oder Logo:',
		'Rechnungsadresse:',
		'Name und Telefon:',
	), 'Grüezi', 'Vielen Dank und bis bald' );
	$fr = $faire( 'Demande d’offre : cadeaux d’entreprise', array(
		'Entreprise :',
		'Quantité et format (p. ex. boîte de 9 ou 16 pralinés, boîte de Läckerli, bonbonnière) :',
		'Date de remise souhaitée :',
		'Message ou logo :',
		'Adresse de facturation :',
		'Nom et téléphone :',
	), 'Bonjour', 'Merci et à bientôt' );
	$en = $faire( 'Quote request: corporate gifts', array(
		'Company:',
		'Quantity and format (e.g. box of 9 or 16 pralines, Läckerli box, bonbonnière):',
		'Preferred hand-over date:',
		'Message or logo:',
		'Billing address:',
		'Name and phone:',
	), 'Hello', 'Thank you and see you soon' );
	return array( $de => 'fr' === $l ? $fr : $en );
}

/**
 * Pages traduites, puis liens internes : {H}/confiserie/ devient l'adresse de la page traduite.
 *
 * @param array $ids Pages allemandes : adresse => identifiant.
 */
function schiesser_importer_pages_langues( $ids, $langues, $remplacer ) {
	$meta    = schiesser_tr_pages();
	$pages   = array();
	foreach ( schiesser_demo_pages() as $p ) {
		$pages[ $p['slug'] ] = $p;
	}
	$crees = array(); // langue => adresse allemande => identifiant
	foreach ( $langues as $l ) {
		$slug_l = schiesser_slug_polylang( $l );
		foreach ( $pages as $slug => $p ) {
			$id_de = (int) ( $ids[ $slug ] ?? 0 );
			if ( ! $id_de || is_wp_error( $id_de ) || empty( $meta[ $slug ][ $l ] ) ) {
				continue;
			}
			list( $titre, $adresse, $seo ) = $meta[ $slug ][ $l ];
			$existant = (int) pll_get_post( $id_de, $slug_l );
			if ( $existant && ! $remplacer ) {
				$crees[ $l ][ $slug ] = $existant;
				continue;
			}
			// L'adresse française était une « ancienne adresse » de la page allemande : elle appartient désormais à la page française.
			foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_schiesser_ancien_slug', 'meta_value' => $adresse, 'lang' => '' ) ) as $ancienne ) { // phpcs:ignore WordPress.DB.SlowDBQuery
				delete_post_meta( $ancienne, '_schiesser_ancien_slug', $adresse );
			}
			$id = schiesser_traduire_contenu( $id_de, $l, array(
				'post_title'   => $titre,
				'post_name'    => $adresse,
				'post_content' => schiesser_traduire_blocs( $p['contenu'], $l ),
			) );
			if ( $id ) {
				schiesser_demo_seo( $id, $seo );
				$crees[ $l ][ $slug ] = $id;
			}
		}
	}
	// Liens internes vers les pages de la même langue.
	foreach ( $crees as $l => $liste ) {
		$remplacements = array();
		foreach ( $liste as $slug => $id ) {
			$remplacements[ '{H}/' . $slug . '/' ] = get_permalink( $id );
		}
		$remplacements['{H}'] = untrailingslashit( home_url() );
		foreach ( $liste as $id ) {
			$contenu = (string) get_post_field( 'post_content', $id );
			if ( false !== strpos( $contenu, '{H}' ) ) {
				wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => strtr( $contenu, $remplacements ) ) ) );
			}
		}
	}
	return $crees;
}

/** Menus : le même menu pour chaque langue (les pages y sont remplacées par leur traduction). */
function schiesser_menus_langues( $langues ) {
	$options = get_option( 'polylang' );
	if ( ! is_array( $options ) ) {
		return;
	}
	$positions = get_theme_mod( 'nav_menu_locations', array() );
	$theme     = get_stylesheet();
	foreach ( array( 'principal', 'pied' ) as $position ) {
		if ( empty( $positions[ $position ] ) ) {
			continue;
		}
		foreach ( array_merge( array( 'de' ), $langues ) as $l ) {
			$slug_l = schiesser_slug_polylang( $l );
			if ( empty( $options['nav_menus'][ $theme ][ $position ][ $slug_l ] ) ) {
				$options['nav_menus'][ $theme ][ $position ][ $slug_l ] = (int) $positions[ $position ];
			}
		}
	}
	update_option( 'polylang', $options );
}

/**
 * Import complet des traductions.
 *
 * @param array $ids       Pages allemandes : adresse => identifiant.
 * @param bool  $remplacer Remplacer le texte des pages déjà traduites.
 * @return array Langues importées.
 */
function schiesser_importer_langues( $ids, $remplacer = false ) {
	$langues = schiesser_langues_a_traduire();
	if ( ! $langues ) {
		return array();
	}
	// Contenus allemands : langue « Deutsch » s'ils n'en ont pas encore.
	foreach ( get_posts( array( 'post_type' => array( SCHIESSER_PRODUIT, SCHIESSER_TEAROOM ), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_s_preisliste', 'lang' => '' ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		schiesser_langue_de_si_vide( $id );
	}
	foreach ( $ids as $id ) {
		if ( $id && ! is_wp_error( $id ) ) {
			schiesser_langue_de_si_vide( (int) $id );
		}
	}
	schiesser_importer_preisliste_langues( $langues );
	schiesser_importer_tearoom_langues( $langues );
	schiesser_importer_pages_langues( $ids, $langues, $remplacer );
	schiesser_menus_langues( $langues );
	if ( function_exists( 'schiesser_carte_tearoom' ) ) {
		schiesser_carte_tearoom( true );
	}
	update_option( 'schiesser_langues_importees', $langues );
	return $langues;
}

<?php
/**
 * Contenu du site (en allemand), importé en un clic depuis l'administration.
 *
 * Crée (ou met à jour) : la liste de prix de la boutique, la carte du Tea Room,
 * les 7 pages du site composées de sections (et le brouillon de l'Impressum),
 * les menus de l'en-tête et du pied de page, et les réglages SEO de chaque page
 * (titre, description, mot-clé principal), lus par Rank Math.
 *
 * Textes : inc/contenu-pages.php. Produits et carte : inc/contenu-produits.php.
 */

defined( 'ABSPATH' ) || exit;

/* Réglages par défaut dès l'activation du thème */
add_action( 'after_switch_theme', function () {
	if ( false === get_option( SCHIESSER_OPTION ) ) {
		add_option( SCHIESSER_OPTION, schiesser_reglages_defaut() );
	}
} );

/* ------------------------------------------------------------------ */
/* Avis et actions dans l'administration                               */
/* ------------------------------------------------------------------ */

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$ecran = get_current_screen();
	if ( ! $ecran || ! in_array( $ecran->id, array( 'dashboard', 'themes', 'toplevel_page_schiesser-reglages', 'edit-schiesser_produit', 'edit-page' ), true ) ) {
		return;
	}
	$importee = get_option( 'schiesser_demo_importee' );
	$version  = (string) get_option( 'schiesser_demo_version', $importee ? '0.1.0' : '' );
	$langues_manquantes = array_diff( schiesser_langues_a_traduire(), (array) get_option( 'schiesser_langues_importees', array() ) );
	if ( $importee && version_compare( $version, '0.11.0', '>=' ) && ! $langues_manquantes ) {
		return;
	}
	if ( ! $importee ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo' ), 'schiesser_import_demo' );
		?>
		<div class="notice notice-info">
			<p><strong>Thème Schiesser :</strong> importer le contenu du site en allemand (7 pages, liste de prix de la boutique, carte du Tea Room, menus, réglages SEO). Les photos d’illustration sont téléchargées depuis Unsplash, cela peut prendre une minute.</p>
			<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>">Importer le contenu du site</a></p>
		</div>
		<?php
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo&remplacer=1' ), 'schiesser_import_demo' );
	?>
	<div class="notice notice-info">
		<p><strong>Thème Schiesser 0.11 : site en trois langues.</strong> L’allemand reste la langue principale. <?php if ( schiesser_langues_a_traduire() ) : ?>Polylang est actif : la mise à jour crée aussi les versions <strong><?php echo esc_html( implode( ' et ', array_map( function ( $l ) { return 'fr' === $l ? 'française' : 'anglaise'; }, schiesser_langues_a_traduire() ) ) ); ?></strong> des 8 pages, des 70 produits et de la carte du Tea Room (78 plats), reliées à la version allemande pour le sélecteur de langue.<?php else : ?>Pour les versions <strong>française et anglaise</strong>, installez d’abord l’extension <strong>Polylang</strong> et créez les langues Deutsch (langue par défaut), Français et English (voir le Guide), puis revenez ici.<?php endif; ?> La mise à jour remplace le texte des pages par la version du thème ; les photos de la médiathèque, les prix et les produits ajoutés à la main sont conservés.</p>
		<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>" onclick="return confirm('Remplacer le contenu des pages par la version du thème (et créer les traductions si Polylang est actif) ?');">Mettre à jour le contenu des pages</a></p>
	</div>
	<?php
} );

add_action( 'admin_notices', function () {
	if ( isset( $_GET['schiesser_demo'] ) && 'ok' === $_GET['schiesser_demo'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success is-dismissible"><p>Contenu du site importé' . ( get_option( 'schiesser_langues_importees' ) ? ' (allemand, avec les versions ' . esc_html( implode( ', ', array_map( 'strtoupper', (array) get_option( 'schiesser_langues_importees' ) ) ) ) . ')' : ' (en allemand)' ) . '. <a href="' . esc_url( home_url( '/' ) ) . '" target="_blank">Voir le site</a> · <a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">Voir les pages</a> · <a href="' . esc_url( admin_url( 'edit.php?post_type=schiesser_produit' ) ) . '">Voir les produits</a></p></div>';
	}
} );

add_action( 'admin_post_schiesser_import_demo', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Accès refusé.' );
	}
	check_admin_referer( 'schiesser_import_demo' );
	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	schiesser_importer_demo( ! empty( $_GET['remplacer'] ) );
	wp_safe_redirect( admin_url( 'edit.php?post_type=page&schiesser_demo=ok' ) );
	exit;
} );

/* ------------------------------------------------------------------ */
/* Outils                                                              */
/* ------------------------------------------------------------------ */

/**
 * Télécharge une photo Unsplash dans la médiathèque (une seule fois).
 * Renvoie l'identifiant de l'image, ou 0 en cas d'échec (le site reste utilisable).
 *
 * @param string $photo Identifiant Unsplash.
 * @param string $nom   Nom du fichier (sans extension), ex. « lackerli-de-bale ».
 * @param string $alt   Texte alternatif : décrit l'image pour Google et les lecteurs d'écran.
 */
function schiesser_demo_image( $photo, $nom, $alt ) {
	if ( 0 === strpos( $photo, 'theme:' ) ) {
		return schiesser_demo_image_theme( substr( $photo, 6 ), $alt ); // photo d'archive livrée avec le thème
	}
	$existant = get_posts( array(
		'post_type'   => 'attachment',
		'meta_key'    => '_schiesser_source', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'  => $photo, // phpcs:ignore WordPress.DB.SlowDBQuery
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $existant ) {
		$id = (int) $existant[0];
		// Photo déjà importée : texte alternatif remplacé une fois par sa version allemande (le site est en allemand).
		if ( '' === (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) || 'de' !== get_post_meta( $id, '_schiesser_alt_langue', true ) ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $alt );
			update_post_meta( $id, '_schiesser_alt_langue', 'de' );
		}
		return $id;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( 'https://images.unsplash.com/photo-' . $photo . '?q=80&w=2000&auto=format&fit=crop&fm=jpg', 60 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => sanitize_file_name( $nom . '.jpg' ), 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_schiesser_source', $photo );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_schiesser_alt_langue', 'de' );
	return (int) $id;
}

/** Bloc Hero avec sa photo. */
function schiesser_demo_hero( $attrs, $photo, $nom, $alt ) {
	$id = schiesser_demo_image( $photo, $nom, $alt );
	if ( $id ) {
		$attrs['imageId']  = $id;
		$attrs['imageUrl'] = (string) wp_get_attachment_image_url( $id, 'full' );
		$attrs['imageAlt'] = $alt;
	}
	return schiesser_bm( 'schiesser/hero', $attrs );
}

/** Image « cadre vitrine » placée dans une section. */
function schiesser_demo_photo( $photo, $nom, $alt, $legende = '' ) {
	$id = schiesser_demo_image( $photo, $nom, $alt );
	return schiesser_bm_image( $id, $id ? (string) wp_get_attachment_image_url( $id, 'large' ) : '', $alt, 'vitrine', $legende );
}

/** Enregistre titre SEO, description et mots-clés (champs de Rank Math). */
function schiesser_demo_seo( $post_id, $seo ) {
	update_post_meta( $post_id, 'rank_math_title', $seo['titre'] );
	update_post_meta( $post_id, 'rank_math_description', $seo['description'] );
	update_post_meta( $post_id, 'rank_math_focus_keyword', implode( ',', $seo['mots_cles'] ) );
}

/* ------------------------------------------------------------------ */
/* Produits de la boutique (liste de prix de la maison)                */
/* ------------------------------------------------------------------ */

/** Lien interne pour les textes des pages. */
function schiesser_demo_lien( $chemin, $texte ) {
	return '<a href="' . esc_url( home_url( $chemin ) ) . '">' . $texte . '</a>';
}

/** Anciens produits de démonstration (en français) : remplacés par la liste de prix. */
function schiesser_demo_anciens_produits() {
	return array( 'Läckerli de Bâle', 'Truffes au chocolat', 'Pralinés artisanaux', 'Tablette de chocolat artisanale', 'Biscuits aux amandes', 'Marrons glacés', 'Gâteau sur commande', 'Coffret de chocolats' );
}

/**
 * Crée ou met à jour les produits de la liste de prix (inc/contenu-produits.php).
 * Un produit déjà présent (même nom) est mis à jour ; ceux ajoutés à la main restent intacts.
 */
function schiesser_importer_preisliste() {
	// Les produits de démonstration en français partent à la corbeille (récupérables).
	foreach ( schiesser_demo_anciens_produits() as $nom ) {
		foreach ( get_posts( array( 'post_type' => SCHIESSER_PRODUIT, 'title' => $nom, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'lang' => '' ) ) as $id ) {
			if ( ! get_post_meta( $id, '_s_preisliste', true ) ) { // « Marrons glacés » existe aussi dans la liste de prix : celui-là reste
				wp_trash_post( $id );
			}
		}
	}
	foreach ( array( 'Chocolats', 'Biscuits', 'Confiseries', 'Pâtisserie', 'Coffrets' ) as $ancienne ) {
		$t = get_term_by( 'name', $ancienne, SCHIESSER_CATEGORIE );
		if ( $t && 0 === (int) $t->count ) {
			wp_delete_term( $t->term_id, SCHIESSER_CATEGORIE );
		}
	}

	$photos_categories = array(
		'Läckerli & Basler Spezialitäten' => array( '1556910103-1c02745aae4d', 'Basler Läckerli und Spezialitäten aus der Confiserie Schiesser' ),
		'Pralinen & Confiserie'           => array( '1481391319762-47dff72954d9', 'Handgemachte Pralinen der Confiserie Schiesser in Basel' ),
		'Schokolade'                      => array( '1464195244916-405fa0a82545', 'Schokolade aus der Confiserie Schiesser' ),
		'Torten & Patisserie'             => array( '1565958011703-44f9829ba187', 'Torten und Patisserie aus der eigenen Backstube' ),
		'Aus der Backstube'               => array( '1509440159596-0249088772ff', 'Frisches Gebäck aus der Backstube am Marktplatz' ),
		'Salziges & Apéro'                => array( '1600891964092-4316c288032e', 'Salziges Gebäck und Apéro der Confiserie Schiesser' ),
		'Guetzli & Gebäck'                => array( '1549007994-cb92caebd54b', 'Guetzli und Gebäck aus Basel' ),
		'Glace'                           => array( '1551024506-0bccd828d307', 'Hausgemachte Glace der Confiserie Schiesser' ),
	);
	$ordre = 0;
	foreach ( schiesser_preisliste() as $rubrique => $produits ) {
		$t = term_exists( $rubrique, SCHIESSER_CATEGORIE ) ?: wp_insert_term( $rubrique, SCHIESSER_CATEGORIE );
		if ( is_wp_error( $t ) ) {
			continue;
		}
		$cat = (int) ( is_array( $t ) ? $t['term_id'] : $t );
		// Photo d'illustration de la catégorie : montrée sur l'accueil tant que les produits n'ont pas leur photo.
		if ( ! get_term_meta( $cat, 'photo', true ) && isset( $photos_categories[ $rubrique ] ) ) {
			$photo = schiesser_demo_image( $photos_categories[ $rubrique ][0], 'confiserie-basel-' . sanitize_title( $rubrique ), $photos_categories[ $rubrique ][1] );
			if ( $photo ) {
				update_term_meta( $cat, 'photo', $photo );
			}
		}
		foreach ( $produits as $p ) {
			++$ordre;
			list( $nom, $formats, $accroche ) = $p;
			$existant = schiesser_posts_allemands( array( 'post_type' => SCHIESSER_PRODUIT, 'title' => $nom, 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'numberposts' => 1 ) );
			$donnees  = array(
				'post_type'   => SCHIESSER_PRODUIT,
				'post_status' => $existant ? $existant[0]->post_status : 'publish',
				'post_title'  => $nom,
				'menu_order'  => $ordre,
			);
			if ( $existant ) {
				$donnees['ID'] = $existant[0]->ID;
				$id            = wp_update_post( $donnees );
			} else {
				$id = wp_insert_post( $donnees );
			}
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			wp_set_object_terms( $id, array( $cat ), SCHIESSER_CATEGORIE );

			// Prix affiché : prix unique, ou « ab CHF … » quand il y a plusieurs formats.
			$prix = array_map( function ( $f ) {
				return (float) str_replace( '—', '0', $f[1] );
			}, $formats );
			if ( 1 === count( $formats ) ) {
				$affiche = schiesser_chf( $formats[0][1] );
				$unite   = $formats[0][0];
				$fiche   = array();
			} else {
				$min     = array_keys( $prix, min( $prix ) )[0];
				$affiche = 'ab ' . schiesser_chf( $formats[ $min ][1] );
				$unite   = count( $formats ) . ' Formate';
				$fiche   = array_map( function ( $f ) {
					return array( $f[0], schiesser_chf( $f[1] ) );
				}, $formats );
			}
			update_post_meta( $id, '_s_prix', $affiche );
			update_post_meta( $id, '_s_unite', $unite );
			update_post_meta( $id, '_s_accroche', $accroche );
			update_post_meta( $id, '_s_fiche', $fiche );
			update_post_meta( $id, '_s_preisliste', 1 );
			if ( '' === (string) get_post_meta( $id, '_s_badge_style', true ) ) {
				update_post_meta( $id, '_s_badge_style', 'vert' );
			}
			// SEO de base (modifiable dans Rank Math) : nom, maison, ville. Remplacé seulement s'il n'a pas été modifié à la main.
			$titre_seo = $nom . ' | Confiserie Schiesser am Marktplatz Basel';
			if ( mb_strlen( $titre_seo ) > 60 ) {
				$titre_seo = $nom . ' | Confiserie Schiesser Basel';
			}
			$desc_seo = $nom . ' aus der Confiserie Schiesser am Marktplatz Basel: ' . ( 1 === count( $formats ) ? $affiche : 'in ' . count( $formats ) . ' Formaten, ' . $affiche ) . '. Im Laden erhältlich, Bestellung per Telefon.';
			if ( mb_strlen( $desc_seo ) < 140 ) {
				$desc_seo = str_replace( '. Im Laden', '. Von Hand gemacht, im Laden', $desc_seo );
			}
			$ancien = (string) get_post_meta( $id, 'rank_math_title', true );
			if ( '' === $ancien || $nom . ' | Confiserie Schiesser Basel' === $ancien || $titre_seo === $ancien ) { // réglages encore automatiques
				update_post_meta( $id, 'rank_math_title', $titre_seo );
				update_post_meta( $id, 'rank_math_description', $desc_seo );
				// Mot-clé principal : le nom du produit ; secondaire : avec la ville, si elle n'y est pas déjà.
				$themes = array(
					'Läckerli & Basler Spezialitäten' => 'Basler Spezialitäten',
					'Pralinen & Confiserie'           => 'Confiserie Basel',
					'Schokolade'                      => 'Schokolade Basel',
					'Torten & Patisserie'             => 'Patisserie Basel',
					'Aus der Backstube'               => 'Gebäck Basel',
					'Salziges & Apéro'                => 'Apéro Basel',
					'Guetzli & Gebäck'                => 'Guetzli Basel',
					'Glace'                           => 'Glace Basel',
				);
				$mots = array( $nom, preg_match( '/bas(el|ler)/iu', $nom ) ? $nom . ' Geschenk' : $nom . ' Basel', $nom . ' kaufen', $themes[ $rubrique ] ?? 'Confiserie Basel', 'Confiserie Schiesser ' . $nom );
				update_post_meta( $id, 'rank_math_focus_keyword', implode( ',', array_slice( array_unique( $mots ), 0, 5 ) ) ); // Rank Math : 5 mots-clés, le premier est le principal
			}
		}
	}
}

/* ------------------------------------------------------------------ */
/* Carte du Tea Room                                                   */
/* ------------------------------------------------------------------ */

/**
 * Remplit le menu « Produits Tea Room » avec la carte de la maison (inc/contenu-produits.php).
 * Les plats de l'ancienne carte de démonstration partent à la corbeille ; ceux ajoutés à la main restent.
 */
function schiesser_importer_tearoom() {
	if ( ! function_exists( 'schiesser_tearoom_creer' ) ) {
		return;
	}
	foreach ( get_posts( array( 'post_type' => SCHIESSER_TEAROOM, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_t_demo', 'lang' => '' ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
		wp_trash_post( $id );
	}
	foreach ( array( 'Cafés & chocolats', 'Thés & infusions', 'Pâtisseries', 'Salé & glaces' ) as $ancienne ) {
		$t = get_term_by( 'name', $ancienne, SCHIESSER_RUBRIQUE );
		if ( $t ) {
			wp_delete_term( $t->term_id, SCHIESSER_RUBRIQUE );
		}
	}
	$photos = array(
		'tables'   => '1600891964092-4316c288032e',
		'salon'    => '1445116572660-236099ec97a0',
		'truf'     => '1548907040-4baa42d10919',
		'boutique' => '1509440159596-0249088772ff',
		'cake'     => '1565958011703-44f9829ba187',
		'marr'     => '1519915028121-7d3463d20b13',
		'jour'     => '1551024506-0bccd828d307',
		'relais'   => '1587248720327-8eb72564be1e',
	);
	$suggestion = 0;
	foreach ( schiesser_getraenkekarte() as $i => $r ) {
		$t = term_exists( $r['nom'], SCHIESSER_RUBRIQUE ) ?: wp_insert_term( $r['nom'], SCHIESSER_RUBRIQUE );
		if ( is_wp_error( $t ) ) {
			continue;
		}
		$term_id = (int) ( is_array( $t ) ? $t['term_id'] : $t );
		update_term_meta( $term_id, 'ordre', $i + 1 );
		$photo = schiesser_demo_image( $photos[ $r['photo'] ], 'tea-room-' . sanitize_title( $r['nom'] ), $r['alt'] ); // photo déjà importée : texte alternatif mis en allemand
		if ( $photo && ! get_term_meta( $term_id, 'photo', true ) ) {
			update_term_meta( $term_id, 'photo', $photo );
		}
		foreach ( $r['plats'] as $k => $p ) {
			$existant = schiesser_posts_allemands( array( 'post_type' => SCHIESSER_TEAROOM, 'title' => $p[0], 'post_status' => array( 'publish', 'draft', 'private' ), 'numberposts' => 1, 'fields' => 'ids', 'tax_query' => array( array( 'taxonomy' => SCHIESSER_RUBRIQUE, 'terms' => $term_id ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( $existant ) {
				$id = $existant[0];
				update_post_meta( $id, '_t_prix', $p[1] );
				update_post_meta( $id, '_t_description', $p[2] );
				update_post_meta( $id, '_t_mention', $p[3] ?? '' );
				wp_update_post( array( 'ID' => $id, 'menu_order' => $k + 1 ) );
				wp_set_object_terms( $id, array( $term_id ), SCHIESSER_RUBRIQUE );
			} else {
				$id = schiesser_tearoom_creer( array(
					'nom'         => $p[0],
					'prix'        => $p[1],
					'description' => $p[2],
					'mention'     => $p[3] ?? '',
					'ordre'       => $k + 1,
					'rubrique'    => $term_id,
				) );
			}
			if ( $id && schiesser_getraenkekarte_suggestion() === $p[0] ) {
				$suggestion = $id;
			}
		}
	}
	if ( $suggestion ) {
		schiesser_tearoom_definir_suggestion( $suggestion, true );
		if ( ! has_post_thumbnail( $suggestion ) ) {
			$photo = schiesser_demo_image( $photos['cake'], 'laeckerli-brownie', 'Original Schiesser Läckerli-Brownie' );
			if ( $photo ) {
				set_post_thumbnail( $suggestion, $photo );
			}
		}
	}
	schiesser_carte_tearoom( true );
}


/* ------------------------------------------------------------------ */
/* Import                                                              */
/* ------------------------------------------------------------------ */

/**
 * @param bool $remplacer Met à jour le contenu des pages et produits existants (nouvelle version du thème).
 */
function schiesser_importer_demo( $remplacer = false ) {
	/* Produits de la boutique (liste de prix) et carte du Tea Room */
	schiesser_importer_preisliste();
	schiesser_importer_tearoom();

	/* Pages */
	$ids = array();
	foreach ( schiesser_demo_pages() as $ordre => $p ) {
		$page = schiesser_demo_page_allemande( get_page_by_path( $p['slug'] ) );
		foreach ( $p['anciens'] as $ancien ) {
			$page = $page ?: schiesser_demo_page_allemande( get_page_by_path( $ancien ) );
		}
		if ( $page && ! $remplacer ) {
			$ids[ $p['slug'] ] = $page->ID;
			continue;
		}
		if ( $page ) {
			delete_post_meta( $page->ID, '_schiesser_ancien_slug', $p['slug'] ); // la nouvelle adresse n'est plus une « ancienne » adresse
		}
		$donnees = array(
			'post_type'    => 'page',
			'post_status'  => $p['statut'] ?? 'publish',
			'post_title'   => $p['titre'],
			'post_name'    => $p['slug'],
			'post_content' => wp_slash( $p['contenu'] ), // wp_insert_post retire les barres obliques : on les protège
			'menu_order'   => $ordre,
		);
		if ( $page ) {
			$donnees['ID']          = $page->ID;
			$donnees['post_status'] = $page->post_status;
			if ( $page->post_name !== $p['slug'] && ! in_array( $page->post_name, (array) get_post_meta( $page->ID, '_schiesser_ancien_slug' ), true ) ) {
				// L'ancienne adresse (ex. /tea-room/) redirigera vers la nouvelle.
				add_post_meta( $page->ID, '_schiesser_ancien_slug', $page->post_name );
			}
			$ids[ $p['slug'] ] = wp_update_post( $donnees );
		} else {
			$ids[ $p['slug'] ] = wp_insert_post( $donnees );
		}
		if ( $ids[ $p['slug'] ] && ! is_wp_error( $ids[ $p['slug'] ] ) ) {
			schiesser_demo_seo( $ids[ $p['slug'] ], $p['seo'] );
			// Anciennes adresses connues (ex. /tea-room/ de la version 0.1) : elles redirigent vers la page.
			// Avec Polylang et le français, les adresses françaises appartiennent aux pages françaises.
			foreach ( in_array( 'fr', schiesser_langues_a_traduire(), true ) ? array() : $p['anciens'] as $ancien ) {
				if ( ! in_array( $ancien, (array) get_post_meta( $ids[ $p['slug'] ], '_schiesser_ancien_slug' ), true ) ) {
					add_post_meta( $ids[ $p['slug'] ], '_schiesser_ancien_slug', $ancien );
				}
			}
		}
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['startseite'] );

	/* Contenus d'exemple de WordPress jamais modifiés : retirés (inutiles, et présents dans le plan du site). */
	foreach ( array( array( 'hello-world', 'post' ), array( 'bonjour-tout-le-monde', 'post' ), array( 'sample-page', 'page' ), array( 'page-d-exemple', 'page' ) ) as $exemple ) {
		$p = get_page_by_path( $exemple[0], OBJECT, $exemple[1] );
		if ( $p && $p->post_modified_gmt === $p->post_date_gmt ) {
			wp_delete_post( $p->ID, true );
		}
	}

	/*
	 * Anciennes valeurs par défaut jamais modifiées : remplacées par les nouvelles.
	 * Gamme de prix « CHF » (sans chiffres), présentation et mention de la version 0.1.
	 */
	$reglages = (array) get_option( SCHIESSER_OPTION, array() );
	$defaut   = schiesser_reglages_defaut();
	$anciens  = array(
		'gamme_prix'      => array( 'CHF' ),
		'presentation'    => array( "Confiserie fondée à Bâle. Une maison, un savoir-faire, la même place depuis plus d'un siècle.", 'La Confiserie Schiesser est une confiserie artisanale fondée en 1870 sur le Marktplatz de Bâle. Läckerli de Bâle, truffes, pralinés et coffrets y sont préparés à la main, et son salon de thé accueille les visiteurs au premier étage.' ),
		'mention'         => array( 'Confiserie fondée à Bâle · Marktplatz', 'Confiserie à Bâle · Marktplatz · depuis 1870' ),
		'horaires_note'   => array( 'Les horaires peuvent varier les jours fériés. En cas de doute, un appel suffit.' ),
		'allergenes_note' => array( 'Nos créations sont préparées dans un atelier qui travaille aussi le gluten, les fruits à coque, le lait et les œufs : des traces sont possibles. Notre équipe vous renseigne volontiers.' ),
	);
	$change   = false;
	foreach ( $anciens as $cle => $valeurs ) {
		if ( isset( $reglages[ $cle ] ) && in_array( trim( (string) $reglages[ $cle ] ), $valeurs, true ) ) {
			$reglages[ $cle ] = $defaut[ $cle ];
			$change           = true;
		}
	}
	// Vitrine du jour de démonstration (en français) : remplacée par la version allemande.
	if ( isset( $reglages['vitrine'] ) && array( 'Läckerli', 'Truffes', 'Tarte du jour' ) === array_values( (array) $reglages['vitrine'] ) ) {
		$reglages['vitrine'] = $defaut['vitrine'];
		$change              = true;
	}
	if ( $change ) {
		update_option( SCHIESSER_OPTION, $reglages );
	}

	/* Slogan du site (titre de l'onglet, données pour Google), s'il n'a jamais été changé */
	$slogan = get_option( 'blogdescription' );
	if ( '' === $slogan || in_array( $slogan, array( 'Just another WordPress site', 'Un site utilisant WordPress', 'Un site utilisant WordPress.', 'Confiserie et salon de thé à Bâle depuis 1870' ), true ) ) {
		update_option( 'blogdescription', 'Confiserie und Tea Room in Basel seit 1870' );
	}

	/* Menus : principal (en-tête) et pied de page (colonne « Explorer »), créés s'ils sont vides */
	$menus = array(
		'principal' => array( 'Menu principal', array( 'confiserie', 'tea-room', 'geschichte', 'besuch', 'kontakt' ) ),
		'pied'      => array( 'Menu du pied de page', array( 'confiserie', 'tea-room', 'firmengeschenke', 'geschichte', 'besuch', 'kontakt' ) ),
	);
	$positions = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus as $position => $menu_def ) {
		$menu    = wp_get_nav_menu_object( $menu_def[0] );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $menu_def[0] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		if ( ! wp_get_nav_menu_items( $menu_id ) ) {
			foreach ( $menu_def[1] as $slug ) {
				if ( empty( $ids[ $slug ] ) || is_wp_error( $ids[ $slug ] ) ) {
					continue;
				}
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-object-id' => $ids[ $slug ],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				) );
			}
		}
		if ( empty( $positions[ $position ] ) || 'principal' === $position ) {
			$positions[ $position ] = $menu_id;
		}
	}
	set_theme_mod( 'nav_menu_locations', $positions );

	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();

	/* Versions française et anglaise (Polylang) */
	schiesser_importer_langues( $ids, $remplacer );

	update_option( 'schiesser_demo_importee', 1 );
	update_option( 'schiesser_demo_version', SCHIESSER_VERSION );
}

/** Page de la langue allemande (ou sans langue) ; null pour une traduction. */
function schiesser_demo_page_allemande( $page ) {
	if ( ! $page || ! function_exists( 'pll_get_post_language' ) ) {
		return $page;
	}
	$l = (string) pll_get_post_language( $page->ID );
	return ( '' === $l || 'de' === substr( $l, 0, 2 ) ) ? $page : null;
}

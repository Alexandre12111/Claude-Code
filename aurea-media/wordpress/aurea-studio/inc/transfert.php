<?php
/**
 * Réalisations : export et import (Aurea Studio → Import / export).
 *
 * Export : un fichier .zip avec realisations.json (toutes les fiches : textes, type, secteur, options,
 * étude de cas, réglages Rank Math) et, si demandé, les captures (images/<clé>.webp, images/<clé>-mobile.webp).
 * Import : le même format, modifié ou complété (nouveau site, nouvelle étude de cas, nouvelles captures).
 * - Une fiche est retrouvée par sa clé, puis par son slug, puis par le domaine de son site.
 * - Seuls les champs présents dans le fichier sont modifiés ; un champ absent reste tel quel.
 * - Une fiche absente du fichier n'est jamais supprimée (sauf "supprimer": true, mise à la corbeille).
 * - « Simuler » montre ce qui changerait, sans rien enregistrer.
 */
defined( 'ABSPATH' ) || exit;

define( 'AUREA_TRANSFERT_FORMAT', 1 );

add_action( 'admin_menu', function () {
	add_submenu_page( AUREA_STUDIO_MENU, 'Import / export des réalisations', 'Import / export', 'edit_pages', 'aurea-transfert', 'aurea_transfert_ecran' );
}, 15 );

/** Clé d'une fiche (nom de ses captures), sinon son slug. */
function aurea_transfert_cle( $id ) {
	$cle = (string) get_post_meta( $id, '_real_cle', true );
	$img = (int) get_post_thumbnail_id( $id );
	if ( ! $cle && $img ) {
		$cle = (string) get_post_meta( $img, '_aurea_real_source', true );
	}
	return $cle ?: (string) get_post_field( 'post_name', $id );
}

/** Une fiche sous forme de tableau exportable. */
function aurea_transfert_fiche( $post ) {
	$r = aurea_realisation( $post );
	return array(
		'cle'         => aurea_transfert_cle( $r['id'] ),
		'slug'        => get_post_field( 'post_name', $r['id'] ),
		'titre'       => $r['titre'],
		'url'         => $r['url'],
		'lieu'        => $r['lieu'],
		'accroche'    => $r['accroche'],
		'accroche_en' => (string) get_post_meta( $r['id'], '_real_accroche_en', true ),
		'type'        => $r['type']['slug'],
		'secteur'     => $r['secteur']['slug'],
		'une'         => $r['une'],
		'mur'         => $r['mur'],
		'visible'     => $r['visible'],
		'ordre'       => (int) get_post_field( 'menu_order', $r['id'] ),
		'extrait'     => (string) get_post_field( 'post_excerpt', $r['id'] ),
		'etude_html'  => (string) get_post_field( 'post_content', $r['id'] ),
		'seo'         => array(
			'titre'       => (string) get_post_meta( $r['id'], 'rank_math_title', true ),
			'description' => (string) get_post_meta( $r['id'], 'rank_math_description', true ),
			'mot_cle'     => (string) get_post_meta( $r['id'], 'rank_math_focus_keyword', true ),
		),
	);
}

/* ------------------------------------------------------------------ */
/* Export                                                              */
/* ------------------------------------------------------------------ */

add_action( 'admin_post_aurea_transfert_export', function () {
	if ( ! current_user_can( 'edit_pages' ) || ! check_admin_referer( 'aurea_transfert_export' ) ) {
		wp_die( 'Lien expiré.' );
	}
	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_die( 'L’hébergement ne permet pas de créer un fichier .zip (extension PHP ZipArchive absente).' );
	}
	$captures = ! empty( $_POST['captures'] );
	$fiches   = array();
	$zip      = new ZipArchive();
	$fichier  = wp_tempnam( 'realisations-aurea' );
	$zip->open( $fichier, ZipArchive::OVERWRITE );
	foreach ( get_posts( array( 'post_type' => AUREA_REAL, 'post_status' => array( 'publish', 'draft', 'pending' ), 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $p ) {
		$f = aurea_transfert_fiche( $p );
		if ( $captures ) {
			foreach ( array( 'image' => (int) get_post_thumbnail_id( $p ), 'mobile' => (int) get_post_meta( $p->ID, '_real_mobile', true ) ) as $champ => $img ) {
				$source = $img ? get_attached_file( $img ) : '';
				if ( $source && file_exists( $source ) ) {
					$nom = 'images/' . sanitize_file_name( $f['cle'] ) . ( 'mobile' === $champ ? '-mobile' : '' ) . '.' . strtolower( pathinfo( $source, PATHINFO_EXTENSION ) );
					$zip->addFile( $source, $nom );
					$f[ $champ ] = $nom;
				}
			}
		}
		$fiches[] = $f;
	}
	$zip->addFromString( 'realisations.json', wp_json_encode( array(
		'format'       => AUREA_TRANSFERT_FORMAT,
		'site'         => home_url( '/' ),
		'date'         => current_time( 'c' ),
		'types'        => wp_list_pluck( get_terms( array( 'taxonomy' => AUREA_REAL_TYPE, 'hide_empty' => false ) ), 'name', 'slug' ),
		'secteurs'     => wp_list_pluck( get_terms( array( 'taxonomy' => AUREA_REAL_SECTEUR, 'hide_empty' => false ) ), 'name', 'slug' ),
		'realisations' => $fiches,
	), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	$zip->close();
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="realisations-aurea-' . wp_date( 'Y-m-d' ) . '.zip"' );
	header( 'Content-Length: ' . filesize( $fichier ) );
	readfile( $fichier ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	@unlink( $fichier ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	exit;
} );

/* ------------------------------------------------------------------ */
/* Import                                                              */
/* ------------------------------------------------------------------ */

/** Fiche existante qui correspond à une ligne du fichier (clé, slug, puis domaine du site). */
function aurea_transfert_trouver( $f ) {
	$posts = get_posts( array( 'post_type' => AUREA_REAL, 'post_status' => array( 'publish', 'draft', 'pending' ), 'numberposts' => -1 ) );
	foreach ( array( 'cle', 'slug', 'domaine' ) as $critere ) {
		foreach ( $posts as $p ) {
			if ( 'cle' === $critere && ! empty( $f['cle'] ) && aurea_transfert_cle( $p->ID ) === $f['cle'] ) {
				return $p->ID;
			}
			if ( 'slug' === $critere && ! empty( $f['slug'] ) && $p->post_name === $f['slug'] ) {
				return $p->ID;
			}
			if ( 'domaine' === $critere && ! empty( $f['url'] ) ) {
				$a = preg_replace( '#^www\.#', '', (string) wp_parse_url( $f['url'], PHP_URL_HOST ) );
				$b = preg_replace( '#^www\.#', '', (string) wp_parse_url( (string) get_post_meta( $p->ID, '_real_url', true ), PHP_URL_HOST ) );
				if ( $a && $a === $b ) {
					return $p->ID;
				}
			}
		}
	}
	return 0;
}

/**
 * Importe un fichier realisations.json (et ses images).
 *
 * @param string $dossier  Dossier contenant realisations.json.
 * @param bool   $simuler  Ne rien enregistrer, seulement décrire les changements.
 * @return string[] Compte rendu, une ligne par fiche.
 */
function aurea_transfert_importer( $dossier, $simuler ) {
	$donnees = json_decode( (string) file_get_contents( $dossier . '/realisations.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! is_array( $donnees ) || ! isset( $donnees['realisations'] ) || ! is_array( $donnees['realisations'] ) ) {
		return array( 'Fichier realisations.json illisible ou au mauvais format.' );
	}
	aurea_real_creer_termes();
	// Nouveaux types ou secteurs déclarés dans le fichier
	foreach ( array( 'types' => AUREA_REAL_TYPE, 'secteurs' => AUREA_REAL_SECTEUR ) as $cle => $tax ) {
		foreach ( (array) ( $donnees[ $cle ] ?? array() ) as $slug => $nom ) {
			if ( ! term_exists( sanitize_title( $slug ), $tax ) ) {
				$simuler || wp_insert_term( sanitize_text_field( $nom ), $tax, array( 'slug' => sanitize_title( $slug ) ) );
			}
		}
	}
	$rapport = array();
	if ( ! $simuler ) {
		kses_remove_filters(); // l'étude de cas peut contenir des blocs HTML du thème
	}
	foreach ( $donnees['realisations'] as $f ) {
		if ( ! is_array( $f ) || ( empty( $f['titre'] ) && empty( $f['cle'] ) && empty( $f['slug'] ) ) ) {
			continue;
		}
		$id    = aurea_transfert_trouver( $f );
		$nom   = $f['titre'] ?? ( $id ? get_the_title( $id ) : $f['cle'] );
		$avant = $id ? aurea_transfert_fiche( $id ) : array();

		if ( ! empty( $f['supprimer'] ) ) {
			if ( $id ) {
				$simuler || wp_trash_post( $id );
				$rapport[] = '« ' . $nom . ' » : mise à la corbeille.';
			}
			continue;
		}

		// Ce qui change, champ par champ (seuls les champs présents dans le fichier comptent)
		$changes = array();
		foreach ( array( 'titre', 'url', 'lieu', 'accroche', 'accroche_en', 'type', 'secteur', 'une', 'mur', 'visible', 'ordre', 'extrait', 'etude_html', 'slug' ) as $champ ) {
			if ( array_key_exists( $champ, $f ) && ( ! $id || (string) $f[ $champ ] !== (string) ( $avant[ $champ ] ?? '' ) ) ) {
				$changes[] = $champ;
			}
		}
		foreach ( array( 'titre', 'description', 'mot_cle' ) as $champ ) {
			if ( isset( $f['seo'][ $champ ] ) && ( ! $id || (string) $f['seo'][ $champ ] !== (string) ( $avant['seo'][ $champ ] ?? '' ) ) ) {
				$changes[] = 'seo.' . $champ;
			}
		}
		$images = array();
		foreach ( array( 'image', 'mobile' ) as $champ ) {
			if ( ! empty( $f[ $champ ] ) ) {
				$chemin = $dossier . '/' . ltrim( str_replace( '..', '', (string) $f[ $champ ] ), '/' );
				if ( is_readable( $chemin ) && preg_match( '/\.(webp|jpe?g|png)$/i', $chemin ) ) {
					// Une capture identique à celle déjà en place n'est pas réimportée
					$actuelle = $id ? ( 'image' === $champ ? (int) get_post_thumbnail_id( $id ) : (int) get_post_meta( $id, '_real_mobile', true ) ) : 0;
					$fichier  = $actuelle ? get_attached_file( $actuelle ) : '';
					if ( ! $fichier || ! file_exists( $fichier ) || md5_file( $fichier ) !== md5_file( $chemin ) ) {
						$images[ $champ ] = $chemin;
						$changes[]        = 'image' === $champ ? 'capture' : 'capture mobile';
					}
				} else {
					$changes[] = ( 'image' === $champ ? 'capture' : 'capture mobile' ) . ' introuvable dans le zip';
				}
			}
		}
		if ( ! $changes ) {
			$rapport[] = '« ' . $nom . ' » : déjà à jour.';
			continue;
		}
		$rapport[] = '« ' . $nom . ' » : ' . ( $id ? 'mise à jour' : 'nouvelle fiche' ) . ' (' . implode( ', ', array_unique( $changes ) ) . ').';
		if ( $simuler ) {
			continue;
		}

		// Enregistrement
		$post = array( 'post_type' => AUREA_REAL );
		if ( array_key_exists( 'titre', $f ) ) {
			$post['post_title'] = sanitize_text_field( $f['titre'] );
		}
		if ( array_key_exists( 'slug', $f ) && $f['slug'] ) {
			$post['post_name'] = sanitize_title( $f['slug'] );
		}
		if ( array_key_exists( 'visible', $f ) ) {
			$post['post_status'] = $f['visible'] ? 'publish' : 'draft';
		}
		if ( array_key_exists( 'ordre', $f ) ) {
			$post['menu_order'] = (int) $f['ordre'];
		}
		if ( array_key_exists( 'extrait', $f ) ) {
			$post['post_excerpt'] = sanitize_textarea_field( $f['extrait'] );
		}
		if ( array_key_exists( 'etude_html', $f ) ) {
			$post['post_content'] = function_exists( 'aurea_remplacer' ) ? aurea_remplacer( (string) $f['etude_html'] ) : (string) $f['etude_html'];
		}
		if ( $id ) {
			$post['ID'] = $id;
			wp_update_post( wp_slash( $post ) );
		} else {
			$post += array( 'post_status' => 'publish', 'post_title' => 'Nouvelle réalisation', 'menu_order' => count( aurea_realisations( array( 'tout' => true ) ) ) );
			$id    = wp_insert_post( wp_slash( $post ) );
			if ( ! $id || is_wp_error( $id ) ) {
				array_pop( $rapport );
				$rapport[] = '« ' . $nom . ' » : création impossible.';
				continue;
			}
			if ( ! empty( $f['cle'] ) ) {
				update_post_meta( $id, '_real_cle', sanitize_title( $f['cle'] ) );
			}
			if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_is_translated_post_type' ) && pll_is_translated_post_type( AUREA_REAL ) ) {
				pll_set_post_language( $id, 'fr' );
			}
		}
		if ( array_key_exists( 'etude_html', $f ) ) {
			delete_post_meta( $id, '_aurea_etude' ); // étude importée : le thème ne la remplace plus
		}
		$metas = array( 'url' => '_real_url', 'lieu' => '_real_lieu', 'accroche' => '_real_accroche', 'accroche_en' => '_real_accroche_en' );
		foreach ( $metas as $champ => $meta ) {
			if ( array_key_exists( $champ, $f ) ) {
				update_post_meta( $id, $meta, 'url' === $champ ? esc_url_raw( $f[ $champ ] ) : sanitize_textarea_field( $f[ $champ ] ) );
			}
		}
		foreach ( array( 'une', 'mur' ) as $champ ) {
			if ( array_key_exists( $champ, $f ) ) {
				update_post_meta( $id, '_real_' . $champ, $f[ $champ ] ? '1' : '0' );
			}
		}
		foreach ( array( 'type' => AUREA_REAL_TYPE, 'secteur' => AUREA_REAL_SECTEUR ) as $champ => $tax ) {
			if ( array_key_exists( $champ, $f ) ) {
				wp_set_object_terms( $id, $f[ $champ ] ? array( sanitize_title( $f[ $champ ] ) ) : array(), $tax );
			}
		}
		$seo = array( 'titre' => 'rank_math_title', 'description' => 'rank_math_description', 'mot_cle' => 'rank_math_focus_keyword' );
		foreach ( $seo as $champ => $meta ) {
			if ( isset( $f['seo'][ $champ ] ) ) {
				update_post_meta( $id, $meta, sanitize_text_field( $f['seo'][ $champ ] ) );
			}
		}
		$titre = get_the_title( $id );
		$cle   = aurea_transfert_cle( $id );
		foreach ( $images as $champ => $chemin ) {
			$ext = strtolower( pathinfo( $chemin, PATHINFO_EXTENSION ) );
			$img = aurea_studio_importer_image( $chemin, 'realisation-' . $cle . ( 'mobile' === $champ ? '-mobile' : '' ) . '-' . substr( md5_file( $chemin ), 0, 6 ) . '.' . $ext, 'Site ' . $titre . ( 'mobile' === $champ ? ' sur mobile' : ' réalisé par Aurea Media' ) );
			if ( ! $img ) {
				continue;
			}
			update_post_meta( $img, '_aurea_real_source', $cle );
			if ( 'image' === $champ ) {
				set_post_thumbnail( $id, $img );
			} else {
				update_post_meta( $id, '_real_mobile', $img );
			}
			update_post_meta( $id, '_aurea_captures_import', 1 ); // les captures livrées avec l'extension ne les remplacent plus
		}
	}
	if ( ! $simuler ) {
		kses_init_filters();
		flush_rewrite_rules( false );
		if ( class_exists( '\\RankMath\\Sitemap\\Cache' ) && is_callable( array( '\\RankMath\\Sitemap\\Cache', 'invalidate_storage' ) ) ) {
			\RankMath\Sitemap\Cache::invalidate_storage();
		}
	}
	return $rapport ?: array( 'Aucune réalisation dans le fichier.' );
}

/* ------------------------------------------------------------------ */
/* Écran                                                               */
/* ------------------------------------------------------------------ */

function aurea_transfert_ecran() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$rapport = array();
	$simule  = false;
	if ( isset( $_POST['aurea_transfert_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['aurea_transfert_nonce'] ), 'aurea_transfert_import' ) && ! empty( $_FILES['fichier']['tmp_name'] ) ) {
		$simule = ! empty( $_POST['simuler'] );
		$nom    = strtolower( sanitize_file_name( wp_unslash( $_FILES['fichier']['name'] ?? '' ) ) );
		$tmp    = $_FILES['fichier']['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		$dossier = trailingslashit( get_temp_dir() ) . 'aurea-transfert-' . wp_generate_password( 8, false );
		wp_mkdir_p( $dossier );
		if ( '.json' === substr( $nom, -5 ) ) {
			copy( $tmp, $dossier . '/realisations.json' );
			$ok = true;
		} elseif ( '.zip' === substr( $nom, -4 ) ) {
			$ok = unzip_file( $tmp, $dossier );
		} else {
			$ok = new WP_Error( 'format', 'Choisissez un fichier .zip ou .json.' );
		}
		if ( is_wp_error( $ok ) ) {
			$rapport[] = $ok->get_error_message();
		} else {
			$json    = is_readable( $dossier . '/realisations.json' ) ? $dossier . '/realisations.json' : ( glob( $dossier . '/*/realisations.json' )[0] ?? '' );
			$rapport = $json ? aurea_transfert_importer( dirname( $json ), $simule ) : array( 'realisations.json introuvable dans le fichier.' );
		}
		global $wp_filesystem;
		if ( $wp_filesystem ) {
			$wp_filesystem->delete( $dossier, true );
		}
	}
	echo '<div class="wrap aurea-studio">';
	aurea_studio_entete( 'Import / export des réalisations', 'Exportez toutes vos réalisations dans un fichier, faites le modifier ou compléter (nouveau site, étude de cas, captures), puis réimportez le.' );
	if ( $rapport ) {
		echo '<div class="notice ' . ( $simule ? 'notice-info' : 'notice-success' ) . '"><p><strong>' . ( $simule ? 'Simulation : rien n’a été enregistré.' : 'Import terminé.' ) . '</strong></p><ul style="list-style:disc;margin-left:20px">';
		foreach ( $rapport as $ligne ) {
			echo '<li>' . esc_html( $ligne ) . '</li>';
		}
		echo '</ul></div>';
	}
	?>
	<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px;max-width:1000px;margin-top:20px">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:24px;border:1px solid #dcdcde;border-radius:12px;background:#fff">
			<h2 style="margin-top:0">1. Exporter</h2>
			<p>Un fichier <code>.zip</code> avec toutes les réalisations : textes, type, secteur, options, étude de cas et réglages Rank Math.</p>
			<p><label><input type="checkbox" name="captures" value="1" checked> Inclure les captures d’écran (fichier plus lourd)</label></p>
			<input type="hidden" name="action" value="aurea_transfert_export">
			<?php wp_nonce_field( 'aurea_transfert_export' ); ?>
			<p><button class="button button-primary">Télécharger l’export</button></p>
		</form>
		<form method="post" enctype="multipart/form-data" style="padding:24px;border:2px dashed #C9A961;border-radius:12px;background:#fffdf8">
			<h2 style="margin-top:0">2. Importer</h2>
			<p>Le fichier <code>.zip</code> (ou <code>realisations.json</code>) modifié. Seuls les champs présents dans le fichier sont changés, et aucune réalisation n’est supprimée sans <code>"supprimer": true</code>.</p>
			<?php wp_nonce_field( 'aurea_transfert_import', 'aurea_transfert_nonce' ); ?>
			<p><input type="file" name="fichier" accept=".zip,.json,application/zip,application/json" required></p>
			<p><label><input type="checkbox" name="simuler" value="1" checked> Simuler d’abord (voir les changements sans rien enregistrer)</label></p>
			<p><button class="button button-primary">Importer</button></p>
		</form>
	</div>
	<details class="aurea-aide" style="max-width:1000px;margin-top:20px">
		<summary>Format du fichier</summary>
		<p><code>realisations.json</code> contient une liste <code>realisations</code>. Chaque réalisation : <code>cle</code> (nom des captures), <code>titre</code>, <code>url</code>, <code>lieu</code>, <code>accroche</code>, <code>accroche_en</code>, <code>type</code> (site-vitrine, site-ecommerce…), <code>secteur</code> (avocat, sante…), <code>une</code>, <code>mur</code>, <code>visible</code>, <code>ordre</code>, <code>extrait</code>, <code>etude_html</code> (texte de l’étude de cas), <code>seo</code> (titre, description, mot_cle), <code>image</code> et <code>mobile</code> (chemins des captures dans le zip, par exemple <code>images/mon-client.webp</code>).</p>
		<p>Une étude de cas importée n’est plus remplacée par les mises à jour du thème. Des captures importées ne sont plus remplacées par celles livrées avec l’extension.</p>
	</details>
	<?php
	echo '</div>';
}

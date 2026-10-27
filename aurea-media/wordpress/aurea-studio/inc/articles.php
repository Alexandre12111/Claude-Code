<?php
/**
 * Import de packs d'articles : un petit fichier .zip (articles.json, articles/<slug>.html, images/<fichier>)
 * crée ou met à jour des articles, avec images, rubrique, réglages Rank Math et date de publication.
 * Une date future programme l'article : WordPress le publie tout seul le jour venu.
 * Un article écrit ou modifié à la main dans WordPress (sans la marque Aurea) n'est jamais remplacé.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_submenu_page( AUREA_STUDIO_MENU, 'Importer des articles', 'Importer des articles', 'publish_posts', 'aurea-articles', 'aurea_articles_ecran' );
}, 20 );

/** Image du pack importée dans la médiathèque (une seule fois par fichier). */
function aurea_pack_image( $chemin, $nom, $alt ) {
	$cle  = 'conseils/' . $nom;
	$deja = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_aurea_source', 'meta_value' => $cle ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $deja ) {
		return (int) $deja[0];
	}
	if ( ! is_readable( $chemin ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$copie = wp_tempnam( $nom );
	copy( $chemin, $copie );
	$id = media_handle_sideload( array( 'name' => $nom, 'tmp_name' => $copie ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $copie ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_aurea_source', $cle );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/** Adresse d'une page du site pour les liens {{url:…}} (fonction du thème si présent). */
function aurea_pack_url( $cle ) {
	if ( function_exists( 'aurea_url' ) ) {
		return aurea_url( $cle );
	}
	if ( 0 === strpos( $cle, 'article:' ) ) {
		$p = get_page_by_path( substr( $cle, 8 ), OBJECT, 'post' );
		return $p ? get_permalink( $p ) : home_url( '/' );
	}
	if ( 0 === strpos( $cle, 'realisation:' ) ) {
		return home_url( '/realisations/' . substr( $cle, 12 ) . '/' );
	}
	return 'accueil' === $cle ? home_url( '/' ) : home_url( '/' . $cle . '/' );
}

/**
 * Importe un pack déjà décompressé.
 *
 * @param string $dossier Dossier contenant articles.json.
 * @return array Lignes du compte rendu.
 */
function aurea_pack_importer( $dossier ) {
	$reglages = json_decode( (string) file_get_contents( $dossier . '/articles.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! is_array( $reglages ) ) {
		return array( 'Fichier articles.json illisible.' );
	}
	uasort( $reglages, function ( $a, $b ) {
		return ( $a['ordre'] ?? 0 ) <=> ( $b['ordre'] ?? 0 );
	} );
	$version = 'pack-' . gmdate( 'Ymd-His' );
	$ids     = array();
	$rapport = array();
	kses_remove_filters();
	// 1. Créer ou repérer les articles (les liens entre articles se résolvent une fois tous créés)
	foreach ( $reglages as $slug => $a ) {
		$slug = sanitize_title( $slug );
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $post && ! get_post_meta( $post->ID, '_aurea_article', true ) && ! get_post_meta( $post->ID, '_aurea_version', true ) && '' !== trim( wp_strip_all_tags( $post->post_content ) ) ) {
			$rapport[] = '« ' . $post->post_title . ' » existe déjà et a été modifié à la main : gardé tel quel.';
			continue;
		}
		$ids[ $slug ] = $post ? $post->ID : wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => sanitize_text_field( $a['titre'] ), 'post_name' => $slug ), true );
	}
	// 2. Contenu, images, rubrique, réglages Rank Math, date
	foreach ( $ids as $slug => $id ) {
		$a = $reglages[ $slug ];
		if ( is_wp_error( $id ) || ! is_readable( $dossier . '/articles/' . $slug . '.html' ) ) {
			$rapport[] = '« ' . $slug . ' » : fichier manquant, ignoré.';
			continue;
		}
		$images  = (array) ( $a['images'] ?? array() );
		$contenu = (string) file_get_contents( $dossier . '/articles/' . $slug . '.html' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$contenu = preg_replace_callback( '/\{\{img_(id|url|alt):conseils\/([a-z0-9._-]+)\}\}/', function ( $m ) use ( $dossier, $images ) {
			$alt = (string) ( $images[ 'conseils/' . $m[2] ] ?? '' );
			$img = aurea_pack_image( $dossier . '/images/' . $m[2], $m[2], $alt );
			if ( 'id' === $m[1] ) {
				return (string) $img;
			}
			return 'url' === $m[1] ? esc_url( (string) wp_get_attachment_image_url( $img, 'large' ) ) : esc_attr( $alt );
		}, $contenu );
		$contenu = preg_replace_callback( '/\{\{url:([a-z0-9:-]+)\}\}/', function ( $m ) {
			return esc_url( aurea_pack_url( $m[1] ) );
		}, $contenu );
		$contenu = str_replace( '{{accueil}}', esc_url( home_url( '/' ) ), $contenu );

		list( $nom_cat, $slug_cat ) = $a['categorie'];
		$cat    = term_exists( $slug_cat, 'category' ) ?: wp_insert_term( $nom_cat, 'category', array( 'slug' => $slug_cat ) );
		$cat_id = is_array( $cat ) ? (int) $cat['term_id'] : 0;

		$champs = array(
			'ID'            => $id,
			'post_title'    => sanitize_text_field( $a['titre'] ),
			'post_content'  => $contenu,
			'post_excerpt'  => sanitize_text_field( $a['extrait'] ?? '' ),
			'post_category' => $cat_id ? array( $cat_id ) : array(),
		);
		$date   = ! empty( $a['date'] ) ? strtotime( $a['date'] . ' ' . wp_timezone_string() ) : 0;
		$actuel = get_post_status( $id );
		if ( $date && $date > time() ) {
			$champs['post_status']   = 'future';
			$champs['post_date']     = wp_date( 'Y-m-d H:i:s', $date );
			$champs['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $date );
			$champs['edit_date']     = true; // sans ce drapeau, WordPress ignore la date d'un brouillon et le publie aussitôt
			$etat = 'programmé le ' . wp_date( 'j F Y à H\hi', $date );
		} elseif ( 'publish' !== $actuel ) {
			$champs['post_status']   = 'publish';
			$champs['post_date']     = current_time( 'mysql' );
			$champs['post_date_gmt'] = current_time( 'mysql', true );
			$champs['edit_date']     = true;
			$etat = 'publié';
		} else {
			$etat = 'mis à jour';
		}
		wp_update_post( wp_slash( $champs ) );
		if ( ! empty( $a['image'] ) ) {
			$nom = basename( $a['image'] );
			$une = aurea_pack_image( $dossier . '/images/' . $nom, $nom, (string) ( $images[ $a['image'] ] ?? '' ) );
			if ( $une ) {
				set_post_thumbnail( $id, $une );
			}
		}
		update_post_meta( $id, 'rank_math_focus_keyword', sanitize_text_field( $a['mot_cle'] ?? '' ) );
		update_post_meta( $id, 'rank_math_title', sanitize_text_field( $a['titre_seo'] ?? '' ) );
		update_post_meta( $id, 'rank_math_description', sanitize_text_field( $a['description'] ?? '' ) );
		if ( $cat_id ) {
			update_post_meta( $id, 'rank_math_primary_category', $cat_id );
		}
		update_post_meta( $id, '_aurea_article', $version );
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $id, 'fr' ); // avec Polylang : les articles du blog sont en français
		}
		$rapport[] = '« ' . $a['titre'] . ' » : ' . $etat . '.';
	}
	kses_init_filters();
	return $rapport;
}

function aurea_articles_ecran() {
	if ( ! current_user_can( 'publish_posts' ) ) {
		return;
	}
	$rapport = array();
	if ( isset( $_POST['aurea_pack_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['aurea_pack_nonce'] ), 'aurea_pack' ) && ! empty( $_FILES['aurea_pack']['tmp_name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		$nom = sanitize_file_name( wp_unslash( $_FILES['aurea_pack']['name'] ?? '' ) );
		if ( '.zip' !== strtolower( substr( $nom, -4 ) ) ) {
			$rapport[] = 'Choisissez un fichier .zip.';
		} else {
			$dossier = trailingslashit( get_temp_dir() ) . 'aurea-pack-' . wp_generate_password( 8, false );
			$ok      = unzip_file( $_FILES['aurea_pack']['tmp_name'], $dossier ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( is_wp_error( $ok ) ) {
				$rapport[] = 'Archive illisible : ' . $ok->get_error_message();
			} else {
				$json    = is_readable( $dossier . '/articles.json' ) ? $dossier . '/articles.json' : ( glob( $dossier . '/*/articles.json' )[0] ?? '' );
				$rapport = $json ? aurea_pack_importer( dirname( $json ) ) : array( 'Ce fichier n’est pas un pack d’articles Aurea (articles.json introuvable).' );
			}
			global $wp_filesystem;
			if ( $wp_filesystem ) {
				$wp_filesystem->delete( $dossier, true );
			}
		}
	}
	$prochains = get_posts( array( 'post_status' => 'future', 'numberposts' => 20, 'orderby' => 'date', 'order' => 'ASC' ) );
	?>
	<div class="wrap">
		<h1>Importer des articles</h1>
		<p>Déposez un pack d’articles (<code>.zip</code>) préparé pour votre site : textes, images, rubrique et réglages Rank Math sont importés en une fois. Les articles datés dans le futur sont programmés et se publient tout seuls.</p>
		<?php if ( $rapport ) : ?>
			<div class="notice notice-success"><ul style="list-style:disc;margin-left:20px">
				<?php foreach ( $rapport as $ligne ) : ?><li><?php echo esc_html( $ligne ); ?></li><?php endforeach; ?>
			</ul></div>
		<?php endif; ?>
		<form method="post" enctype="multipart/form-data" style="margin:20px 0;padding:24px;border:2px dashed #C9A961;border-radius:12px;background:#fffdf8;max-width:640px">
			<?php wp_nonce_field( 'aurea_pack', 'aurea_pack_nonce' ); ?>
			<p><input type="file" name="aurea_pack" accept=".zip,application/zip" required></p>
			<p><button class="button button-primary">Importer le pack</button></p>
		</form>
		<h2>Articles programmés</h2>
		<?php if ( $prochains ) : ?>
			<table class="widefat striped" style="max-width:760px"><thead><tr><th>Date de publication</th><th>Article</th></tr></thead><tbody>
				<?php foreach ( $prochains as $p ) : ?>
					<tr><td><?php echo esc_html( get_the_date( 'j F Y, H\hi', $p ) ); ?></td><td><a href="<?php echo esc_url( get_edit_post_link( $p ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></td></tr>
				<?php endforeach; ?>
			</tbody></table>
		<?php else : ?>
			<p>Aucun article programmé pour le moment.</p>
		<?php endif; ?>
	</div>
	<?php
}

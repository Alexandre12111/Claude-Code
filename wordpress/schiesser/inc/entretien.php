<?php
/**
 * Écran « Entretien du site » (Réglages maison → Entretien).
 *
 * Les outils de mise en place ou de dépannage (langues des contenus, pack de langue allemand,
 * transfert de la carte du Tea Room, contenu livré avec le thème, mises à jour des pages) sont
 * réunis ici. Ils n'apparaissent plus en avis sur le tableau de bord ni sur les listes.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_submenu_page( 'schiesser-reglages', 'Entretien du site', 'Entretien', 'manage_options', 'schiesser-entretien', 'schiesser_page_entretien' );
}, 31 );

/** Adresse de l'écran Entretien, avec d'éventuels paramètres (résultat d'une action). */
function schiesser_url_entretien( $args = array() ) {
	return add_query_arg( $args, admin_url( 'admin.php?page=schiesser-entretien' ) );
}

/** Ligne d'état : 'ok' (vert), 'alerte' (orange) ou '' (neutre). */
function schiesser_entretien_etat( $type, $icone, $html ) {
	echo '<div class="s-etat' . ( $type ? ' s-etat--' . esc_attr( $type ) : '' ) . '"><span class="dashicons dashicons-' . esc_attr( $icone ) . '" aria-hidden="true"></span><div>' . wp_kses_post( $html ) . '</div></div>';
}

/** Résultat de l'action qui vient d'être faite (après redirection). */
function schiesser_entretien_resultat() {
	$fait = sanitize_key( $_GET['fait'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'langues' === $fait ) {
		$b  = array_map( 'intval', explode( '-', sanitize_text_field( wp_unslash( $_GET['b'] ?? '' ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$pl = function ( $n, $un, $plusieurs ) {
			return $n . ' ' . ( $n > 1 ? $plusieurs : $un );
		};
		$texte = '<strong>Langues remises en ordre.</strong> ' . esc_html( $pl( $b[0] ?? 0, 'produit ou plat', 'produits ou plats' ) . ' et ' . $pl( $b[3] ?? 0, 'page', 'pages' ) . ' remis dans leur langue, ' . $pl( $b[1] ?? 0, 'catégorie corrigée', 'catégories corrigées' ) . ', ' . $pl( $b[2] ?? 0, 'doublon mis à la corbeille', 'doublons mis à la corbeille' ) . ' (récupérables). Les traductions manquantes ont été créées.' );
	} elseif ( 'langue-de' === $fait ) {
		$texte = 'Langue allemande installée : le site public est entièrement en allemand, l’administration reste en français.';
	} elseif ( 'langue-de-erreur' === $fait ) {
		$texte = 'Le pack de langue n’a pas pu être téléchargé. Autre possibilité : Réglages → Général → Langue du site → « Deutsch (Schweiz) », enregistrer, puis remettre « Français » (le pack reste installé).';
	} elseif ( 'carte' === $fait ) {
		$texte = sprintf( 'Carte transférée : %d rubriques et %d produits. Le bloc « Carte du salon » de la page affiche maintenant ces produits.', (int) ( $_GET['r'] ?? 0 ), (int) ( $_GET['p'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification
	} elseif ( 'carte-erreur' === $fait ) {
		$texte = 'Aucune carte à transférer : elle est peut-être déjà dans « Produits Tea Room ».';
	} elseif ( 'carte-garder' === $fait ) {
		$texte = 'C’est noté : la carte reste saisie dans la page.';
	} else {
		return;
	}
	$classe = in_array( $fait, array( 'langue-de-erreur', 'carte-erreur' ), true ) ? 'notice-error' : 'notice-success';
	echo '<div class="notice ' . esc_attr( $classe ) . ' is-dismissible"><p>' . wp_kses_post( $texte ) . '</p></div>';
}

function schiesser_page_entretien() {
	$importee = (bool) get_option( 'schiesser_demo_importee' );
	$noms     = array( 'de' => 'Deutsch', 'fr' => 'Français', 'en' => 'English' );
	$ligne    = function ( $par_langue ) use ( $noms ) {
		$out = array();
		foreach ( $par_langue as $l => $n ) {
			$out[] = ( $noms[ $l ] ?? strtoupper( $l ) ) . ' ' . $n;
		}
		return implode( ' · ', $out );
	};
	?>
	<div class="wrap s-admin">
		<h1>Entretien du site</h1>
		<p class="s-sous-titre">Les outils de mise en place et de dépannage. Ils servent rarement : tant que tout est en ordre, il n’y a rien à faire ici.</p>
		<hr class="wp-header-end">
		<?php
		schiesser_entretien_resultat();

		/* Langues des contenus (Polylang) */
		schiesser_carte_debut( 'Langues des produits et des pages', 'Chaque produit, plat et page doit être rangé dans sa langue et relié à ses traductions.', 'translation' );
		if ( ! schiesser_polylang() ) {
			schiesser_entretien_etat( '', 'info', 'Polylang n’est pas actif : le site s’affiche en allemand seulement.' );
		} elseif ( ! isset( schiesser_langues_actives()['de'] ) ) {
			schiesser_entretien_etat( 'alerte', 'warning', '<strong>La langue Deutsch manque dans Polylang.</strong> Le contenu du site est écrit en allemand : créez la langue <strong>Deutsch</strong> (de_CH) dans <a href="' . esc_url( admin_url( 'admin.php?page=mlang' ) ) . '">Langues</a>, de préférence comme langue par défaut (étoile), avec Français et English. Revenez ensuite ici pour remettre chaque contenu dans sa langue.' );
		} elseif ( ! $importee ) {
			schiesser_entretien_etat( '', 'info', 'Le contenu du site n’est pas encore importé (voir plus bas).' );
		} else {
			$compte   = schiesser_rl_diagnostic();
			$melanges = $compte ? schiesser_rl_rattacher( false ) : 0;
			$detail   = $compte ? 'Boutique : ' . esc_html( $ligne( $compte[ SCHIESSER_PRODUIT ] ) ) . '<br>Carte du Tea Room : ' . esc_html( $ligne( $compte[ SCHIESSER_TEAROOM ] ) ) : '';
			if ( ! $compte || ( ! schiesser_rl_desordre( $compte ) && ! $melanges ) ) {
				schiesser_entretien_etat( 'ok', 'yes-alt', '<strong>Les langues sont en ordre.</strong>' . ( $detail ? '<br>' . $detail : '' ) );
			} else {
				schiesser_entretien_etat( 'alerte', 'warning', '<strong>Les langues ne sont pas toutes en ordre.</strong><br>' . $detail . ( $melanges ? '<br>' . esc_html( $melanges . ( $melanges > 1 ? ' produits ou plats sont rangés' : ' produit ou plat est rangé' ) . ' dans une rubrique d’une autre langue (ex. « Kaffee » sur la carte française).' ) : '' ) );
				?>
				<p>La réparation retrouve la vraie langue de chaque produit, plat et page d’après son nom, relie les versions allemande, française et anglaise, met les doublons à la corbeille (récupérables) et crée les traductions qui manquent. Vos prix, photos et textes retouchés sont conservés.</p>
				<p><a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_reparer_langues' ), 'schiesser_reparer_langues' ) ); ?>">Réparer les langues</a></p>
				<?php
			}
		}
		schiesser_carte_fin();

		/* Pack de langue allemand de WordPress */
		schiesser_carte_debut( 'Textes de WordPress en allemand', 'Le thème affiche déjà tout le site en allemand. Le pack de langue traduit en plus les rares textes de WordPress et des extensions (Rank Math, WooCommerce) visibles par les visiteurs.', 'admin-site-alt3' );
		if ( file_exists( WP_LANG_DIR . '/' . SCHIESSER_LANGUE_SITE . '.mo' ) ) {
			schiesser_entretien_etat( 'ok', 'yes-alt', '<strong>Pack de langue allemand (Suisse) installé.</strong> L’administration reste en français.' );
		} else {
			schiesser_entretien_etat( '', 'info', 'Pack de langue allemand (Suisse) pas encore installé. Facultatif : l’administration reste en français dans tous les cas.' );
			if ( current_user_can( 'install_languages' ) ) {
				echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_langue_de' ), 'schiesser_langue_de' ) ) . '">Installer la langue allemande</a></p>';
			}
		}
		schiesser_carte_fin();

		/* Carte du Tea Room */
		schiesser_carte_debut( 'Carte du Tea Room', 'La carte se gère dans « Produits Tea Room », comme les produits de la boutique.', 'coffee' );
		$carte = schiesser_carte_tearoom();
		if ( $carte['nombre'] > 0 ) {
			schiesser_entretien_etat( 'ok', 'yes-alt', '<strong>' . (int) $carte['nombre'] . ' produits dans Produits Tea Room.</strong> <a href="' . esc_url( admin_url( 'edit.php?post_type=' . SCHIESSER_TEAROOM ) ) . '">Voir la carte</a>' );
		} else {
			$trouve = schiesser_carte_dans_une_page();
			if ( ! $trouve ) {
				schiesser_entretien_etat( '', 'info', 'Aucune carte pour l’instant. <a href="' . esc_url( admin_url( 'post-new.php?post_type=' . SCHIESSER_TEAROOM ) ) . '">Ajouter un produit Tea Room</a>' );
			} else {
				schiesser_entretien_etat( '', 'info', 'La carte est encore saisie dans la page « ' . esc_html( get_the_title( $trouve['page'] ) ) . ' » (' . (int) $trouve['rubriques'] . ' rubriques, ' . (int) $trouve['plats'] . ' produits)' . ( get_option( 'schiesser_carte_garder' ) ? ', comme vous l’avez choisi.' : '.' ) . ' Transférée dans Produits Tea Room, elle se gère comme la boutique ; la page l’affiche automatiquement, avec les mêmes textes et photos.' );
				echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_transferer_carte' ), 'schiesser_transferer_carte' ) ) . '">Transférer la carte dans Produits Tea Room</a></p>';
			}
		}
		schiesser_carte_fin();

		/* Contenu livré avec le thème */
		schiesser_carte_debut( 'Contenu du site', 'Pages, produits, carte, menus et réglages SEO livrés avec le thème.', 'admin-page' );
		$version  = (string) get_option( 'schiesser_demo_version', $importee ? '0.1.0' : '' );
		$langues  = (array) get_option( 'schiesser_langues_importees', array() );
		$manquent = array_diff( schiesser_langues_a_traduire(), $langues );
		if ( ! $importee ) {
			schiesser_entretien_etat( 'alerte', 'warning', '<strong>Contenu pas encore importé.</strong> 7 pages, liste de prix de la boutique, carte du Tea Room, menus et réglages SEO, en allemand. Les photos d’illustration sont téléchargées depuis Unsplash, cela peut prendre une minute.' );
			echo '<p><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo' ), 'schiesser_import_demo' ) ) . '">Importer le contenu du site</a></p>';
		} elseif ( version_compare( $version, '0.11.1', '<' ) || $manquent ) {
			$noms_manquent = implode( ' et ', array_map( function ( $l ) {
				return 'fr' === $l ? 'française' : 'anglaise';
			}, $manquent ) );
			schiesser_entretien_etat( 'alerte', 'warning', '<strong>' . ( $manquent ? ( count( $manquent ) > 1 ? 'Versions ' : 'Version ' ) . esc_html( $noms_manquent ) . ' à créer.' : 'Contenu à mettre à jour (site en trois langues).' ) . '</strong> La mise à jour remplace le texte des pages par la version du thème et crée les traductions, reliées à la version allemande. Les photos de la médiathèque, les prix et les produits ajoutés à la main sont conservés.' );
			echo '<p><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=schiesser_import_demo&remplacer=1' ), 'schiesser_import_demo' ) ) . '" onclick="return confirm(\'Remplacer le contenu des pages par la version du thème (et créer les traductions) ?\');">Mettre à jour le contenu des pages</a></p>';
		} else {
			schiesser_entretien_etat( 'ok', 'yes-alt', '<strong>Contenu en place</strong>, en allemand' . ( $langues ? ' avec les versions ' . esc_html( implode( ', ', array_map( 'strtoupper', $langues ) ) ) : '' ) . '.' );
		}
		schiesser_carte_fin();

		/* Mises à jour des pages livrées avec le thème */
		$lots = schiesser_corr_lots();
		if ( $lots ) {
			schiesser_carte_debut( 'Mises à jour des pages', 'Textes relus et changements de mise en page livrés avec le thème, appliqués aux trois langues.', 'update' );
			$a_faire = array_diff_key( $lots, schiesser_corr_appliques() );
			if ( $a_faire ) {
				$liste = '';
				foreach ( $a_faire as $d ) {
					$liste .= '<li>' . esc_html( $d['titre'] ) . '</li>';
				}
				schiesser_entretien_etat( 'alerte', 'update', '<strong>' . count( $a_faire ) . ( count( $a_faire ) > 1 ? ' mises à jour prêtes' : ' mise à jour prête' ) . ' à appliquer.</strong><ul>' . $liste . '</ul>' );
				echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=schiesser-corrections&lot=' . rawurlencode( (string) array_key_first( $a_faire ) ) ) ) . '">Voir et appliquer</a></p>';
			} else {
				schiesser_entretien_etat( 'ok', 'yes-alt', '<strong>Toutes les mises à jour sont appliquées.</strong> <a href="' . esc_url( admin_url( 'admin.php?page=schiesser-corrections' ) ) . '">Voir le détail</a>' );
			}
			schiesser_carte_fin();
		}
		?>
	</div>
	<?php
}

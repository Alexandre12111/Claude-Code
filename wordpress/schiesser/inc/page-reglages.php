<?php
/**
 * Écran « Réglages de la maison » (administration).
 *
 * Un seul formulaire, présenté en onglets :
 * - Coordonnées et horaires     (gérant du site)
 * - Accueil et pied de page     (gérant du site)
 * - Fiche établissement / SEO   (gérant du site)
 * - Charte graphique            (administrateur)
 * - Avancé                      (administrateur)
 * - Boutique en ligne           (administrateur, voir inc/woocommerce.php)
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page(
		'Réglages de la maison',
		'Réglages maison',
		'edit_pages',
		'schiesser-reglages',
		'schiesser_page_reglages',
		'dashicons-store',
		3
	);
	add_submenu_page( 'schiesser-reglages', 'Réglages de la maison', 'Réglages', 'edit_pages', 'schiesser-reglages', 'schiesser_page_reglages' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'toplevel_page_schiesser-reglages' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'schiesser-reglages', SCHIESSER_URI . '/assets/admin/reglages.js', array( 'jquery', 'wp-color-picker' ), SCHIESSER_VERSION, true );

	// Aperçu des polices proposées (uniquement sur cet écran).
	if ( current_user_can( 'manage_options' ) ) {
		$familles = array();
		foreach ( schiesser_polices() as $role ) {
			foreach ( $role as $p ) {
				$familles[] = 'family=' . $p[1];
			}
		}
		wp_enqueue_style( 'schiesser-apercu-polices', 'https://fonts.googleapis.com/css2?' . implode( '&', $familles ) . '&display=swap', array(), null );
	}
} );

/* ------------------------------------------------------------------ */
/* Petits composants de formulaire                                     */
/* ------------------------------------------------------------------ */

/**
 * Affiche un champ.
 *
 * @param string $option  Nom de l'option (schiesser_reglages ou schiesser_charte).
 * @param string $cle     Clé du champ (peut contenir des crochets, ex. couleurs][vert).
 * @param mixed  $valeur  Valeur actuelle.
 * @param array  $args    libelle, aide, type, options, placeholder, classe, attributs.
 */
function schiesser_champ( $option, $cle, $valeur, $args ) {
	$a    = wp_parse_args( $args, array( 'libelle' => '', 'aide' => '', 'type' => 'text', 'options' => array(), 'placeholder' => '', 'classe' => '', 'attributs' => '', 'id' => '' ) );
	$nom  = $option . '[' . $cle . ']';
	$id   = $a['id'] ?: 's-' . sanitize_html_class( str_replace( array( '][', '[', ']' ), '-', $cle ) );
	echo '<div class="s-champ ' . esc_attr( $a['classe'] ) . '">';

	if ( 'toggle' === $a['type'] ) {
		echo '<label class="s-toggle" for="' . esc_attr( $id ) . '">';
		echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $nom ) . '" value="1" ' . checked( ! empty( $valeur ), true, false ) . '>';
		echo '<span class="s-toggle-piste" aria-hidden="true"></span><span class="s-toggle-texte">' . esc_html( $a['libelle'] ) . '</span></label>';
		if ( $a['aide'] ) {
			echo '<p class="s-aide">' . wp_kses_post( $a['aide'] ) . '</p>';
		}
		echo '</div>';
		return;
	}

	echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $a['libelle'] ) . '</label>';
	switch ( $a['type'] ) {
		case 'textarea':
		case 'code':
			echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $nom ) . '" rows="' . ( 'code' === $a['type'] ? 7 : 3 ) . '" class="' . ( 'code' === $a['type'] ? 'code s-code' : '' ) . '" placeholder="' . esc_attr( $a['placeholder'] ) . '" ' . $a['attributs'] . '>' . esc_textarea( $valeur ) . '</textarea>'; // phpcs:ignore WordPress.Security.EscapeOutput
			break;
		case 'select':
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $nom ) . '" ' . $a['attributs'] . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
			foreach ( $a['options'] as $v => $l ) {
				echo '<option value="' . esc_attr( $v ) . '" ' . selected( (string) $valeur, (string) $v, false ) . '>' . esc_html( $l ) . '</option>';
			}
			echo '</select>';
			break;
		default:
			echo '<input id="' . esc_attr( $id ) . '" type="' . esc_attr( $a['type'] ) . '" name="' . esc_attr( $nom ) . '" value="' . esc_attr( $valeur ) . '" placeholder="' . esc_attr( $a['placeholder'] ) . '" ' . $a['attributs'] . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	if ( $a['aide'] ) {
		echo '<p class="s-aide">' . wp_kses_post( $a['aide'] ) . '</p>';
	}
	echo '</div>';
}

function schiesser_carte_debut( $titre, $intro = '', $icone = '' ) {
	echo '<div class="s-carte"><div class="s-carte-tete">';
	if ( $icone ) {
		echo '<span class="dashicons dashicons-' . esc_attr( $icone ) . '" aria-hidden="true"></span>';
	}
	echo '<div><h2>' . esc_html( $titre ) . '</h2>';
	if ( $intro ) {
		echo '<p>' . wp_kses_post( $intro ) . '</p>';
	}
	echo '</div></div><div class="s-carte-corps">';
}

function schiesser_carte_fin() {
	echo '</div></div>';
}

/* ------------------------------------------------------------------ */
/* L'écran                                                             */
/* ------------------------------------------------------------------ */

function schiesser_page_reglages() {
	$r      = wp_parse_args( (array) get_option( SCHIESSER_OPTION, array() ), schiesser_reglages_defaut() );
	$o      = SCHIESSER_OPTION;
	$admin  = current_user_can( 'manage_options' );
	$onglets = array(
		'coordonnees'   => array( 'Coordonnées et horaires', 'clock' ),
		'accueil'       => array( 'Accueil et pied de page', 'admin-home' ),
		'etablissement' => array( 'Fiche Google et SEO', 'search' ),
	);
	if ( $admin ) {
		$onglets['charte']   = array( 'Charte graphique', 'art' );
		$onglets['avance']   = array( 'Avancé', 'admin-tools' );
		$onglets['boutique'] = array( 'Boutique en ligne', 'cart' );
	}
	?>
	<div class="wrap s-admin">
		<div class="s-entete">
			<div class="s-entete-logo" aria-hidden="true"><span>Confiserie ◆ Tea-Room</span><strong>Schiesser</strong></div>
			<div class="s-entete-texte">
				<h1>Réglages de la maison</h1>
				<p>Saisies une seule fois, ces informations se mettent à jour partout sur le site : bandeau « Ouvert / Fermé », pied de page, horaires, fiche Google.</p>
			</div>
			<a class="button s-voir" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">Voir le site <span aria-hidden="true">↗</span></a>
		</div>

		<?php settings_errors(); ?>

		<nav class="s-onglets" aria-label="Sections des réglages">
			<?php $premier = true; foreach ( $onglets as $cle => $o_ ) : ?>
				<a href="#<?php echo esc_attr( $cle ); ?>" class="s-onglet<?php echo $premier ? ' is-actif' : ''; ?>" data-onglet="<?php echo esc_attr( $cle ); ?>">
					<span class="dashicons dashicons-<?php echo esc_attr( $o_[1] ); ?>" aria-hidden="true"></span><?php echo esc_html( $o_[0] ); ?>
				</a>
			<?php $premier = false; endforeach; ?>
		</nav>

		<form method="post" action="options.php" class="s-formulaire" novalidate>
			<?php settings_fields( 'schiesser_reglages_groupe' ); ?>

			<!-- ============ Coordonnées et horaires ============ -->
			<section class="s-panneau" id="panneau-coordonnees" data-panneau="coordonnees">
				<?php schiesser_carte_debut( 'Coordonnées', 'Affichées dans le pied de page, les pages Contact et Nous visiter, et transmises à Google.', 'location' ); ?>
				<div class="s-grille">
					<?php
					schiesser_champ( $o, 'telephone', $r['telephone'], array( 'libelle' => 'Téléphone', 'placeholder' => '+41 61 000 00 00' ) );
					schiesser_champ( $o, 'email', $r['email'], array( 'libelle' => 'E-mail', 'type' => 'email' ) );
					schiesser_champ( $o, 'rue', $r['rue'], array( 'libelle' => 'Rue et numéro', 'placeholder' => 'Marktplatz 19' ) );
					schiesser_champ( $o, 'code_postal', $r['code_postal'], array( 'libelle' => 'Code postal', 'classe' => 's-court' ) );
					schiesser_champ( $o, 'ville', $r['ville'], array( 'libelle' => 'Ville' ) );
					schiesser_champ( $o, 'region', $r['region'], array( 'libelle' => 'Canton ou région', 'placeholder' => 'Basel-Stadt' ) );
					schiesser_champ( $o, 'pays', $r['pays'], array( 'libelle' => 'Pays', 'type' => 'select', 'options' => schiesser_pays() ) );
					schiesser_champ( $o, 'lien_maps', $r['lien_maps'], array( 'libelle' => 'Lien Google Maps', 'type' => 'url', 'aide' => 'Utilisé par les boutons « Itinéraire ». Astuce : sur Google Maps, cherchez la confiserie, puis « Partager » et copiez le lien.', 'classe' => 's-large' ) );
					schiesser_champ( $o, 'mention', $r['mention'], array( 'libelle' => 'Mention du bandeau du haut', 'aide' => 'Petit texte affiché à droite du bandeau sombre, tout en haut du site.', 'classe' => 's-large' ) );
					?>
				</div>
				<?php schiesser_carte_fin(); ?>

				<?php schiesser_carte_debut( 'Horaires d\'ouverture', 'Servent au statut « Ouvert / Fermé » affiché en direct sur le site, au bloc des horaires et à la fiche Google.', 'clock' ); ?>
				<table class="s-horaires">
					<thead><tr><th scope="col">Jour</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Fermé</th></tr></thead>
					<tbody>
					<?php foreach ( schiesser_jours() as $n => $jour ) : $h = $r['horaires'][ $n ]; ?>
						<tr class="<?php echo ! empty( $h['ferme'] ) ? 'is-ferme' : ''; ?>" data-jour="<?php echo (int) $n; ?>">
							<th scope="row"><?php echo esc_html( $jour ); ?></th>
							<td><input type="time" aria-label="<?php echo esc_attr( $jour . ', ouverture' ); ?>" name="<?php echo esc_attr( $o ); ?>[horaires][<?php echo (int) $n; ?>][ouverture]" value="<?php echo esc_attr( $h['ouverture'] ); ?>"></td>
							<td><input type="time" aria-label="<?php echo esc_attr( $jour . ', fermeture' ); ?>" name="<?php echo esc_attr( $o ); ?>[horaires][<?php echo (int) $n; ?>][fermeture]" value="<?php echo esc_attr( $h['fermeture'] ); ?>"></td>
							<td><label class="s-toggle s-toggle--mini"><input type="checkbox" value="1" name="<?php echo esc_attr( $o ); ?>[horaires][<?php echo (int) $n; ?>][ferme]" <?php checked( ! empty( $h['ferme'] ) ); ?>><span class="s-toggle-piste" aria-hidden="true"></span><span class="screen-reader-text"><?php echo esc_html( $jour ); ?> fermé</span></label></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p class="s-actions-ligne">
					<button type="button" class="button js-copier-lundi"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span> Copier les horaires du lundi sur mardi à vendredi</button>
				</p>
				<p class="s-resume">Résumé affiché sur le site : <strong class="js-resume-horaires"><?php echo esc_html( schiesser_horaires_resume() ); ?></strong></p>
				<div class="s-grille">
					<?php schiesser_champ( $o, 'horaires_note', $r['horaires_note'], array( 'libelle' => 'Remarque sur les horaires', 'classe' => 's-large' ) ); ?>
				</div>
				<?php schiesser_carte_fin(); ?>
			</section>

			<!-- ============ Accueil et pied de page ============ -->
			<section class="s-panneau" id="panneau-accueil" data-panneau="accueil" hidden>
				<?php schiesser_carte_debut( 'En vitrine aujourd\'hui', 'Jusqu\'à 4 produits mis en avant dans la bande sous la grande image de l\'accueil. Laissez vide pour masquer une ligne.', 'star-filled' ); ?>
				<div class="s-grille">
					<?php for ( $i = 0; $i < 4; $i++ ) {
						schiesser_champ( $o, 'vitrine][', $r['vitrine'][ $i ] ?? '', array( 'libelle' => 'Produit ' . ( $i + 1 ), 'placeholder' => 0 === $i ? 'Ex. Läckerli' : '', 'id' => 's-vitrine-' . $i ) );
					} ?>
				</div>
				<?php schiesser_carte_fin(); ?>

				<?php schiesser_carte_debut( 'Pied de page', 'Le petit texte de présentation et les liens vers les réseaux sociaux (laissez vide pour masquer une icône).', 'align-center' ); ?>
				<div class="s-grille">
					<?php
					schiesser_champ( $o, 'presentation', $r['presentation'], array( 'libelle' => 'Présentation', 'type' => 'textarea', 'classe' => 's-large' ) );
					schiesser_champ( $o, 'instagram', $r['instagram'], array( 'libelle' => 'Lien Instagram', 'type' => 'url', 'placeholder' => 'https://www.instagram.com/…' ) );
					schiesser_champ( $o, 'facebook', $r['facebook'], array( 'libelle' => 'Lien Facebook', 'type' => 'url', 'placeholder' => 'https://www.facebook.com/…' ) );
					?>
				</div>
				<?php schiesser_carte_fin(); ?>
			</section>

			<!-- ============ Fiche Google et SEO ============ -->
			<section class="s-panneau" id="panneau-etablissement" data-panneau="etablissement" hidden>
				<?php schiesser_carte_debut( 'Fiche établissement pour Google', 'Ces informations complètent les coordonnées et les horaires dans les données structurées lues par Google (type d\'établissement, position, ancienneté).', 'search' ); ?>
				<div class="s-grille">
					<?php
					schiesser_champ( $o, 'nom_etablissement', $r['nom_etablissement'], array( 'libelle' => 'Nom de l\'établissement' ) );
					schiesser_champ( $o, 'type_etablissement', $r['type_etablissement'], array( 'libelle' => 'Type d\'établissement', 'type' => 'select', 'options' => schiesser_types_etablissement() ) );
					schiesser_champ( $o, 'gamme_prix', $r['gamme_prix'], array( 'libelle' => 'Gamme de prix', 'aide' => 'Prix habituels, ex. « CHF 2–30 ». Laisser vide si vous préférez ne rien indiquer.', 'classe' => 's-court' ) );
					schiesser_champ( $o, 'annee_fondation', $r['annee_fondation'], array( 'libelle' => 'Année de fondation', 'classe' => 's-court' ) );
					schiesser_champ( $o, 'latitude', $r['latitude'], array( 'libelle' => 'Latitude', 'aide' => 'Sur Google Maps : clic droit sur la boutique, le premier chiffre.', 'classe' => 's-court' ) );
					schiesser_champ( $o, 'longitude', $r['longitude'], array( 'libelle' => 'Longitude', 'aide' => 'Le second chiffre.', 'classe' => 's-court' ) );
					$pages = array( 0 => 'Automatique (page « boutique »)' );
					foreach ( get_pages() as $p ) {
						$pages[ $p->ID ] = $p->post_title;
					}
					schiesser_champ( $o, 'page_boutique', $r['page_boutique'], array( 'libelle' => 'Page de la boutique', 'type' => 'select', 'options' => $pages, 'aide' => 'Page vers laquelle renvoient les fiches produits (« Retour à la boutique », fil d\'Ariane).' ) );
					?>
				</div>
				<?php schiesser_carte_fin(); ?>

				<?php schiesser_carte_debut( 'Référencement (SEO)', '', 'chart-line' ); ?>
				<?php if ( 0 !== strpos( get_locale(), 'fr' ) ) : ?>
					<p class="s-etat s-etat--alerte"><span class="dashicons dashicons-translation" aria-hidden="true"></span> <strong>La langue du site n'est pas le français</strong> (<?php echo esc_html( get_locale() ); ?>) : Google risque de mal comprendre les pages.
					<?php if ( current_user_can( 'manage_options' ) ) : ?><a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>">Réglages → Général → Langue du site : Français</a><?php endif; ?></p>
				<?php endif; ?>
				<?php if ( schiesser_rank_math_actif() ) : ?>
					<p class="s-etat s-etat--ok"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span> <strong>Rank Math SEO est actif.</strong> Titres, descriptions et mots-clés se règlent dans chaque page (panneau Rank Math de l'éditeur). Le thème complète automatiquement les données structurées de Rank Math avec cette fiche, les produits et les questions fréquentes.</p>
				<?php else : ?>
					<p class="s-etat s-etat--alerte"><span class="dashicons dashicons-warning" aria-hidden="true"></span> <strong>Rank Math SEO n'est pas encore installé.</strong> En attendant, le thème publie lui-même les titres, descriptions et données structurées.
					<?php if ( current_user_can( 'install_plugins' ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=rank+math+seo&tab=search&type=term' ) ); ?>">Installer Rank Math (gratuit)</a>
					<?php endif; ?></p>
				<?php endif; ?>
				<details class="s-details">
					<summary>Voir la fiche envoyée à Google (données structurées)</summary>
					<pre class="s-json"><?php echo esc_html( wp_json_encode( schiesser_schema_etablissement(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); ?></pre>
					<p class="s-aide">Pour vérifier une page : <a href="https://search.google.com/test/rich-results" target="_blank" rel="noopener">test des résultats enrichis de Google</a>.</p>
				</details>
				<?php schiesser_carte_fin(); ?>
			</section>

			<?php if ( $admin ) : $c = schiesser_charte(); $ch = SCHIESSER_CHARTE; ?>
			<!-- ============ Charte graphique ============ -->
			<section class="s-panneau" id="panneau-charte" data-panneau="charte" hidden>
				<?php $alertes = schiesser_verifier_contrastes( $c['couleurs'] ); if ( $alertes ) : ?>
					<div class="s-etat s-etat--alerte"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><div><strong>Lisibilité à surveiller</strong><ul><?php foreach ( $alertes as $a ) { echo '<li>' . esc_html( $a ) . '</li>'; } ?></ul></div></div>
				<?php endif; ?>

				<?php schiesser_carte_debut( 'Couleurs', 'Huit couleurs de base : toutes les nuances du site (fonds alternés, filets, survols) en sont calculées automatiquement. La palette proposée au client dans l\'éditeur suit ces couleurs.', 'art' ); ?>
				<div class="s-couleurs">
					<?php foreach ( schiesser_couleurs_charte() as $cle => $def ) : ?>
						<div class="s-couleur">
							<label for="s-couleur-<?php echo esc_attr( $cle ); ?>"><?php echo esc_html( $def[0] ); ?></label>
							<input type="text" class="js-couleur" id="s-couleur-<?php echo esc_attr( $cle ); ?>" name="<?php echo esc_attr( $ch ); ?>[couleurs][<?php echo esc_attr( $cle ); ?>]" value="<?php echo esc_attr( $c['couleurs'][ $cle ] ); ?>" data-default-color="<?php echo esc_attr( $def[1] ); ?>">
							<p class="s-aide"><?php echo esc_html( $def[2] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
				<?php schiesser_carte_fin(); ?>

				<?php schiesser_carte_debut( 'Typographie', 'Une police pour les titres, une pour le texte. Les polices d\'origine sont hébergées sur le site ; les autres sont chargées depuis Google Fonts.', 'editor-textcolor' ); ?>
				<div class="s-grille">
					<?php
					$opt = array();
					foreach ( schiesser_polices()['titres'] as $k => $p ) {
						$opt[ $k ] = $p[0] . ' · ' . $p[3];
					}
					schiesser_champ( $ch, 'titres', $c['titres'], array( 'libelle' => 'Police des titres', 'type' => 'select', 'options' => $opt, 'attributs' => 'class="js-police" data-cible="titres"' ) );
					$opt = array();
					foreach ( schiesser_polices()['texte'] as $k => $p ) {
						$opt[ $k ] = $p[0] . ' · ' . $p[3];
					}
					schiesser_champ( $ch, 'texte', $c['texte'], array( 'libelle' => 'Police du texte', 'type' => 'select', 'options' => $opt, 'attributs' => 'class="js-police" data-cible="texte"' ) );
					schiesser_champ( $ch, 'echelle', $c['echelle'], array( 'libelle' => 'Taille des grands titres', 'type' => 'select', 'options' => array( '0.9' => 'Plus petite', '1' => 'Normale', '1.1' => 'Plus grande' ) ) );
					?>
				</div>
				<?php
				$noms = array();
				foreach ( schiesser_polices() as $role => $liste ) {
					foreach ( $liste as $k => $p ) {
						$noms[ $role ][ $k ] = "'" . $p[0] . "'," . $p[2];
					}
				}
				?>
				<div class="s-apercu-polices js-apercu-polices" data-polices="<?php echo esc_attr( wp_json_encode( $noms ) ); ?>">
					<span class="s-apercu-surtitre">Aperçu · Confiserie à Bâle depuis 1870</span>
					<span class="s-apercu-titre">Le goût précis d'une <em>maison bâloise</em></span>
					<span class="s-apercu-texte">Läckerli, truffes et pralinés faits main, et un tea room à l'étage avec vue sur le Marktplatz.</span>
					<span class="s-apercu-bouton">Découvrir les créations →</span>
				</div>
				<p class="s-actions-ligne">
					<button type="submit" class="button" name="<?php echo esc_attr( $ch ); ?>[reinitialiser]" value="1" onclick="return confirm('Rétablir les couleurs et polices d\'origine ?');"><span class="dashicons dashicons-image-rotate" aria-hidden="true"></span> Rétablir la charte d'origine</button>
				</p>
				<?php schiesser_carte_fin(); ?>
			</section>

			<!-- ============ Avancé ============ -->
			<section class="s-panneau" id="panneau-avance" data-panneau="avance" hidden>
				<?php schiesser_carte_debut( 'Administration', '', 'admin-appearance' ); ?>
				<?php
				schiesser_champ( $ch, 'habillage_admin', $c['habillage_admin'], array( 'libelle' => 'Habillage Schiesser de l\'administration', 'type' => 'toggle', 'aide' => 'Menu chocolat et vert maison pour tous les comptes. Désactivé : chacun garde les couleurs choisies dans son profil.' ) );
				schiesser_champ( $ch, 'commentaires_off', $c['commentaires_off'], array( 'libelle' => 'Désactiver les commentaires', 'type' => 'toggle', 'aide' => 'Recommandé pour un site vitrine : supprime les commentaires et le spam associé.' ) );
				?>
				<?php schiesser_carte_fin(); ?>

				<?php schiesser_carte_debut( 'Code personnalisé', 'Pour ajouter un outil externe sur toutes les pages : statistiques (Matomo, Google Analytics), pixel, vérification de propriété… Pour une seule page, utilisez plutôt le bloc « HTML personnalisé » dans l\'éditeur.', 'editor-code' ); ?>
				<?php if ( current_user_can( 'unfiltered_html' ) ) : ?>
					<div class="s-grille">
						<?php
						schiesser_champ( $ch, 'code_head', $c['code_head'], array( 'libelle' => 'Code dans l\'en-tête (<head>)', 'type' => 'code', 'classe' => 's-large', 'placeholder' => '<!-- Collez ici le code fourni par l\'outil -->' ) );
						schiesser_champ( $ch, 'code_footer', $c['code_footer'], array( 'libelle' => 'Code en fin de page (avant </body>)', 'type' => 'code', 'classe' => 's-large' ) );
						?>
					</div>
					<p class="s-aide"><span class="dashicons dashicons-shield" aria-hidden="true"></span> Collez uniquement du code provenant d'un service de confiance : il s'exécute sur toutes les pages.</p>
				<?php else : ?>
					<p class="s-aide">Votre compte n'est pas autorisé à ajouter du code.</p>
				<?php endif; ?>
				<?php schiesser_carte_fin(); ?>
			</section>

			<!-- ============ Boutique en ligne ============ -->
			<section class="s-panneau" id="panneau-boutique" data-panneau="boutique" hidden>
				<?php if ( function_exists( 'schiesser_panneau_boutique' ) ) { schiesser_panneau_boutique(); } ?>
			</section>
			<?php endif; ?>

			<div class="s-barre">
				<span class="s-barre-etat js-etat-modif" hidden>Modifications non enregistrées</span>
				<?php submit_button( 'Enregistrer les réglages', 'primary large', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}

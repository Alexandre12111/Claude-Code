<?php
/**
 * Administration pensée pour une personne qui ne code pas :
 * - habillage aux couleurs de la maison (schéma de couleurs « Schiesser »)
 * - tableau de bord avec raccourcis et statut du jour
 * - page « Guide du site »
 * - page de connexion à la marque
 * - rôle « Gérant(e) du site »
 * - commentaires désactivés (réglable)
 * - code personnalisé en en-tête / pied de page (réglable)
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Habillage                                                           */
/* ------------------------------------------------------------------ */

add_action( 'admin_init', function () {
	wp_admin_css_color(
		'schiesser',
		'Schiesser',
		SCHIESSER_URI . '/assets/admin/colors-schiesser.css',
		array( '#271B12', '#1C130C', '#23503B', '#8CC5A6' ),
		array( 'base' => '#D9CEBD', 'focus' => '#8CC5A6', 'current' => '#fff' )
	);
} );

/* Habillage imposé à tous les comptes, sauf si l'option est désactivée dans Réglages → Avancé. */
add_filter( 'get_user_option_admin_color', function ( $couleur ) {
	return schiesser_charte()['habillage_admin'] ? 'schiesser' : $couleur;
} );

add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_style( 'schiesser-admin', SCHIESSER_URI . '/assets/admin/admin.css', array(), SCHIESSER_VERSION );
	$c = schiesser_charte()['couleurs'];
	wp_add_inline_style( 'schiesser-admin', sprintf(
		':root{--s-vert:%s;--s-menthe:%s;--s-choco:%s;--s-papier:%s;--s-sable:%s;--s-encre:%s;--s-texte:%s;--s-doux:%s}',
		$c['vert'], $c['mint'], $c['dark'], $c['paper'], $c['paper-deep'], $c['ink'], $c['text'], $c['ink-soft']
	) );
} );

/* Pied de page de l'administration */
add_filter( 'admin_footer_text', function () {
	return 'Site de la <strong>Confiserie Schiesser</strong> · <a href="' . esc_url( admin_url( 'admin.php?page=schiesser-guide' ) ) . '">Guide du site</a>';
} );

/* ------------------------------------------------------------------ */
/* Tableau de bord                                                     */
/* ------------------------------------------------------------------ */

/* Le grand panneau « Bienvenue dans WordPress » laisse la place au widget de la maison. */
add_action( 'admin_init', function () {
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
} );

add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget( 'schiesser_accueil', 'Votre site Schiesser', 'schiesser_widget_accueil' );

	// Le widget d'accueil en premier.
	global $wp_meta_boxes;
	$normal = $wp_meta_boxes['dashboard']['normal']['core'] ?? array();
	if ( isset( $normal['schiesser_accueil'] ) ) {
		$widget = array( 'schiesser_accueil' => $normal['schiesser_accueil'] );
		unset( $normal['schiesser_accueil'] );
		$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $widget, $normal ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}

	// Moins de bruit : actualités WordPress, brouillon rapide, et pour les non-administrateurs l'activité.
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	if ( ! current_user_can( 'manage_options' ) ) {
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
	}
}, 5 );

function schiesser_widget_accueil() {
	$accueil  = (int) get_option( 'page_on_front' );
	$produits = wp_count_posts( SCHIESSER_PRODUIT );
	$nb       = (int) ( $produits->publish ?? 0 );
	$h        = schiesser_reglage( 'horaires' )[ (int) wp_date( 'w' ) ] ?? null;
	$statut   = ( ! $h || ! empty( $h['ferme'] ) ) ? 'Fermé aujourd\'hui' : 'Aujourd\'hui : ' . $h['ouverture'] . ' – ' . $h['fermeture'];

	$tuiles = array(
		array( 'clock', 'Modifier les horaires', 'Horaires, fermetures, téléphone', admin_url( 'admin.php?page=schiesser-reglages#coordonnees' ), 'edit_pages' ),
		array( 'plus-alt', 'Ajouter un produit', $nb . ' produit' . ( $nb > 1 ? 's' : '' ) . ' en ligne', admin_url( 'post-new.php?post_type=' . SCHIESSER_PRODUIT ), 'edit_posts' ),
		array( 'admin-home', 'Modifier la page d\'accueil', 'Textes, photos, sections', $accueil ? get_edit_post_link( $accueil, 'raw' ) : admin_url( 'edit.php?post_type=page' ), 'edit_pages' ),
		array( 'admin-page', 'Toutes les pages', 'Boutique, Tea Room, Histoire…', admin_url( 'edit.php?post_type=page' ), 'edit_pages' ),
		array( 'menu', 'Modifier le menu', 'Ordre et noms des liens', admin_url( 'nav-menus.php' ), 'edit_theme_options' ),
		array( 'format-image', 'Photos', 'Médiathèque', admin_url( 'upload.php' ), 'upload_files' ),
		array( 'star-filled', 'Vitrine du jour', 'Produits mis en avant sur l\'accueil', admin_url( 'admin.php?page=schiesser-reglages#accueil' ), 'edit_pages' ),
		array( 'sos', 'Guide du site', 'Pas à pas, sans code', admin_url( 'admin.php?page=schiesser-guide' ), 'edit_pages' ),
	);
	?>
	<div class="s-accueil">
		<div class="s-accueil-statut">
			<span class="s-accueil-point" aria-hidden="true"></span>
			<span><?php echo esc_html( $statut ); ?></span>
			<a class="s-accueil-site" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener">Voir le site ↗</a>
		</div>
		<div class="s-tuiles">
			<?php foreach ( $tuiles as $t ) : if ( ! current_user_can( $t[4] ) ) { continue; } ?>
				<a class="s-tuile" href="<?php echo esc_url( $t[3] ); ?>">
					<span class="dashicons dashicons-<?php echo esc_attr( $t[0] ); ?>" aria-hidden="true"></span>
					<strong><?php echo esc_html( $t[1] ); ?></strong>
					<span><?php echo esc_html( $t[2] ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/* ------------------------------------------------------------------ */
/* Guide du site                                                       */
/* ------------------------------------------------------------------ */

add_action( 'admin_menu', function () {
	add_submenu_page( 'schiesser-reglages', 'Guide du site', 'Guide du site', 'edit_pages', 'schiesser-guide', 'schiesser_page_guide' );
}, 20 );

function schiesser_page_guide() {
	$lien = function ( $url, $texte ) {
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $texte ) . '</a>';
	};
	$fiches = array(
		array( 'clock', 'Changer les horaires ou le téléphone', array(
			'Menu ' . $lien( admin_url( 'admin.php?page=schiesser-reglages' ), 'Réglages maison' ) . ', onglet « Coordonnées et horaires ».',
			'Modifiez les heures, cochez « Fermé » pour un jour de fermeture, puis <strong>Enregistrer les réglages</strong>.',
			'Tout le site se met à jour : bandeau « Ouvert / Fermé », pied de page, pages Visiter et Contact, fiche Google.',
		) ),
		array( 'cart', 'Ajouter ou modifier un produit', array(
			'Menu ' . $lien( admin_url( 'edit.php?post_type=' . SCHIESSER_PRODUIT ), 'Produits' ) . ', puis « Ajouter un produit » (ou cliquez sur un produit existant).',
			'Nom, prix, unité, catégorie (à droite), photo (« Photo du produit », à droite), description courte et fiche détaillée.',
			'Le texte long (zone d\'édition du haut) apparaît sur la page du produit : 2 ou 3 paragraphes suffisent.',
			'Ordre d\'affichage : champ « Ordre » (1 = premier). Cliquez sur <strong>Publier</strong> ou <strong>Mettre à jour</strong>.',
		) ),
		array( 'edit-page', 'Modifier le texte ou la photo d\'une page', array(
			'Menu ' . $lien( admin_url( 'edit.php?post_type=page' ), 'Pages' ) . ', survolez la page puis « Modifier ».',
			'Cliquez directement sur un texte pour l\'écrire. Pour une photo, cliquez sur le bloc puis sur « Photo » dans la petite barre d\'outils.',
			'Les réglages d\'un bloc (hauteur, style des boutons, fond de section…) sont dans le panneau de droite. S\'il est caché : icône en haut à droite.',
			'Cliquez sur <strong>Mettre à jour</strong> en haut à droite.',
		) ),
		array( 'plus-alt', 'Ajouter une section', array(
			'Dans une page, cliquez sur le <strong>+</strong> entre deux sections, cherchez « Section » (catégorie Schiesser).',
			'Choisissez le fond (clair, sable, sombre…) à droite, écrivez le titre, puis ajoutez du contenu avec le <strong>+</strong> : paragraphes, images, colonnes, boutons, questions fréquentes…',
			'Plus rapide : onglet « Compositions » de l\'outil d\'ajout, catégorie Schiesser, pour insérer une section toute prête.',
		) ),
		array( 'art', 'Changer la couleur d\'un mot ou d\'un bouton', array(
			'Un mot : sélectionnez-le, puis dans la barre d\'outils flèche ▾ → « Surligner » : seules les couleurs de la maison sont proposées.',
			'Un bouton : cliquez dessus, panneau de droite → « Styles » : Vert maison, Contour, Crème (sur fond sombre) ou Lien fléché.',
			'Un paragraphe, une liste, des colonnes, une image : même rubrique « Styles » (chapeau, surtitre, grille à filets, cartes, cadre vitrine…).',
			'La grande photo (bloc Hero) : panneau de droite → « Boutons » pour leur style, « Mise en page » pour la couleur des mots en italique du titre.',
			'Les couleurs de tout le site : Réglages maison → Charte graphique (administrateur).',
		) ),
		array( 'editor-code', 'Ajouter du code (widget, carte, vidéo)', array(
			'Dans une page : bouton <strong>+</strong> → « HTML personnalisé » pour coller un code fourni par un service (réservation, carte, avis…).',
			'Pour une vidéo YouTube ou un lien Instagram : bloc « Intégrer », collez simplement l\'adresse.',
			'Pour un code à placer sur toutes les pages (statistiques) : Réglages maison → Avancé (administrateur).',
			'Astuce : pour qu\'un texte reste modifiable sans code, écrivez-le dans des blocs Paragraphe, pas dans le HTML.',
		) ),
		array( 'search', 'Référencement (Rank Math)', array(
			'Dans chaque page ou produit, ouvrez le panneau Rank Math (icône en haut à droite de l\'éditeur).',
			'Renseignez le <strong>mot-clé principal</strong>, le <strong>titre SEO</strong> (50 à 60 caractères) et la <strong>méta description</strong> (140 à 160 caractères).',
			'Visez un score de 80 ou plus, sans chercher 100 à tout prix. Les photos doivent avoir un texte alternatif (médiathèque).',
		) ),
		array( 'warning', 'À éviter', array(
			'Supprimer le bloc Hero d\'une page : il porte le titre principal lu par Google (il est protégé contre la suppression).',
			'Envoyer des photos très lourdes : visez 2500 pixels de large au maximum (WordPress crée les autres tailles).',
			'Modifier les réglages de Rank Math ou des extensions sans en parler à l\'agence.',
		) ),
	);
	?>
	<div class="wrap s-admin s-guide">
		<div class="s-entete">
			<div class="s-entete-logo" aria-hidden="true"><span>Confiserie ◆ Tea-Room</span><strong>Schiesser</strong></div>
			<div class="s-entete-texte">
				<h1>Guide du site</h1>
				<p>Les gestes du quotidien, pas à pas. Aucun code n'est nécessaire.</p>
			</div>
		</div>
		<div class="s-guide-grille">
			<?php foreach ( $fiches as $f ) : ?>
				<div class="s-carte">
					<div class="s-carte-tete"><span class="dashicons dashicons-<?php echo esc_attr( $f[0] ); ?>" aria-hidden="true"></span><div><h2><?php echo esc_html( $f[1] ); ?></h2></div></div>
					<div class="s-carte-corps"><ol>
						<?php foreach ( $f[2] as $etape ) { echo '<li>' . wp_kses_post( $etape ) . '</li>'; } ?>
					</ol></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/* ------------------------------------------------------------------ */
/* Page de connexion                                                   */
/* ------------------------------------------------------------------ */

add_action( 'login_enqueue_scripts', function () {
	$c = schiesser_charte()['couleurs'];
	?>
	<style>
		@font-face{font-family:'Bodoni Moda';font-weight:400 900;src:url(<?php echo esc_url( SCHIESSER_URI . '/assets/fonts/bodoni-moda.woff2' ); ?>) format('woff2')}
		body.login{background:<?php echo esc_html( $c['paper'] ); ?>}
		.login h1 a{background:none!important;width:auto!important;height:auto!important;text-indent:0!important;font:500 30px/1 'Bodoni Moda',Didot,serif;letter-spacing:.2em;text-transform:uppercase;color:<?php echo esc_html( $c['ink'] ); ?>!important}
		.login h1 a::before{content:"Confiserie ◆ Tea-Room";display:block;font:600 9px/1 system-ui,sans-serif;letter-spacing:.32em;color:<?php echo esc_html( $c['ink-soft'] ); ?>;margin-bottom:10px}
		.login form{border:1px solid rgba(58,40,24,.14);border-radius:6px;box-shadow:0 20px 40px -30px rgba(39,27,18,.5)}
		.wp-core-ui .button-primary{background:<?php echo esc_html( $c['vert'] ); ?>;border-color:<?php echo esc_html( $c['vert'] ); ?>}
		.wp-core-ui .button-primary:hover,.wp-core-ui .button-primary:focus{background:<?php echo esc_html( $c['dark'] ); ?>;border-color:<?php echo esc_html( $c['dark'] ); ?>}
		.login #backtoblog a,.login #nav a{color:<?php echo esc_html( $c['text'] ); ?>}
		input[type=text]:focus,input[type=password]:focus{border-color:<?php echo esc_html( $c['vert'] ); ?>!important;box-shadow:0 0 0 1px <?php echo esc_html( $c['vert'] ); ?>!important}
	</style>
	<?php
} );
add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );
add_filter( 'login_headertext', function () {
	return 'Schiesser';
} );

/* ------------------------------------------------------------------ */
/* Rôle « Gérant(e) du site »                                           */
/* ------------------------------------------------------------------ */

/**
 * Éditeur + gestion du menu. Pas d'accès aux extensions, aux thèmes ni aux réglages techniques.
 * Créé (ou mis à jour) automatiquement à chaque nouvelle version du thème.
 */
add_action( 'admin_init', function () {
	if ( get_option( 'schiesser_version_roles' ) === SCHIESSER_VERSION ) {
		return;
	}
	$editeur = get_role( 'editor' );
	if ( $editeur ) {
		remove_role( 'schiesser_gerant' );
		add_role( 'schiesser_gerant', 'Gérant(e) du site', array_merge( $editeur->capabilities, array( 'edit_theme_options' => true ) ) );
	}
	update_option( 'schiesser_version_roles', SCHIESSER_VERSION );
} );

/* Le gérant voit le menu, mais pas les écrans techniques de l'apparence (thèmes, personnaliser, widgets). */
add_action( 'admin_menu', function () {
	if ( current_user_can( 'manage_options' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	remove_submenu_page( 'themes.php', 'themes.php' );
	remove_submenu_page( 'themes.php', 'widgets.php' );
	remove_submenu_page( 'themes.php', 'site-editor.php' );
	remove_menu_page( 'tools.php' );
}, 999 );

/* Accès direct par l'adresse : les écrans techniques renvoient au tableau de bord. */
add_action( 'load-themes.php', function () {
	if ( ! current_user_can( 'switch_themes' ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
} );

/* ------------------------------------------------------------------ */
/* Commentaires désactivés                                             */
/* ------------------------------------------------------------------ */

add_action( 'init', function () {
	if ( ! schiesser_charte()['commentaires_off'] ) {
		return;
	}
	foreach ( get_post_types() as $type ) {
		if ( post_type_supports( $type, 'comments' ) ) {
			remove_post_type_support( $type, 'comments' );
			remove_post_type_support( $type, 'trackbacks' );
		}
	}
	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open', '__return_false', 20 );
	add_filter( 'comments_array', '__return_empty_array', 10 );
	add_action( 'admin_menu', function () {
		remove_menu_page( 'edit-comments.php' );
	} );
	add_action( 'admin_bar_menu', function ( $barre ) {
		$barre->remove_node( 'comments' );
	}, 999 );
	add_action( 'wp_dashboard_setup', function () {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	} );
}, 100 );

/* ------------------------------------------------------------------ */
/* Code personnalisé (Réglages → Avancé)                               */
/* ------------------------------------------------------------------ */

add_action( 'wp_head', function () {
	$code = schiesser_charte()['code_head'];
	if ( $code ) {
		echo "\n<!-- Code personnalisé (Réglages maison) -->\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- saisi par un administrateur autorisé (unfiltered_html)
	}
}, 99 );

add_action( 'wp_footer', function () {
	$code = schiesser_charte()['code_footer'];
	if ( $code ) {
		echo "\n<!-- Code personnalisé (Réglages maison) -->\n" . $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}, 99 );

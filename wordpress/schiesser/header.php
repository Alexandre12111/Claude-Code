<?php
/**
 * En-tête : bandeau d'état, logo, menu principal.
 */
defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenu">Aller au contenu</a>
<?php do_action( 'schiesser_avant_entete' ); // bandeau d'annonce (inc/annonces.php) ?>

<div class="pg on" id="page-<?php echo esc_attr( schiesser_cle_page() ); ?>">

<div class="meta" role="region" aria-label="Ouverture du jour">
	<div class="wrap">
		<div class="l">
			<span class="status" data-nosnippet><span class="led js-led"></span><span class="js-statut">Aujourd’hui</span> · <span class="js-heures"><?php echo esc_html( schiesser_horaires_du_jour() ); ?></span></span>
			<span class="js-date" data-nosnippet></span>
		</div>
		<div class="r"><?php echo esc_html( schiesser_reglage( 'mention' ) ); ?></div>
	</div>
</div>

<header class="site-header">
	<div class="wrap hbar">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>, accueil">
			<?php schiesser_logo(); ?>
		</a>
		<nav class="main" id="nav" aria-label="Menu principal">
			<?php foreach ( schiesser_liens_menu() as $lien ) : ?>
				<a href="<?php echo esc_url( $lien['url'] ); ?>"<?php echo $lien['actif'] ? ' class="on" aria-current="page"' : ''; ?>><?php echo esc_html( $lien['titre'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="hact">
			<?php
			// Sélecteur de langue de la maquette (FR · DE · EN) : affiché dès que Polylang gère plusieurs langues.
			if ( function_exists( 'pll_the_languages' ) ) {
				$langues = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0 ) );
				if ( is_array( $langues ) && count( $langues ) > 1 ) {
					echo '<nav class="lang" aria-label="Langue">';
					foreach ( $langues as $l ) {
						echo '<a href="' . esc_url( $l['url'] ) . '" lang="' . esc_attr( $l['locale'] ? str_replace( '_', '-', $l['locale'] ) : $l['slug'] ) . '" hreflang="' . esc_attr( $l['slug'] ) . '"' . ( $l['current_lang'] ? ' class="on" aria-current="true"' : '' ) . '>' . esc_html( strtoupper( $l['slug'] ) ) . '</a>';
					}
					echo '</nav>';
				}
			}
			?>
			<?php if ( function_exists( 'schiesser_lien_panier' ) ) { schiesser_lien_panier(); } ?>
			<button class="burger" type="button" aria-label="Menu" aria-controls="nav" aria-expanded="false"><i></i><i></i><i></i></button>
		</div>
	</div>
</header>

<main id="contenu" class="site-main">

<?php
/**
 * Page /realisations/ : tous les projets d'Aurea Studio, avec les filtres par type.
 * La mise en page vient de la maquette (contenu/archive-realisations.html).
 */
defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="contenu">' . do_shortcode( aurea_contenu_livre( 'archive-realisations' ) ) . '</main>'; // phpcs:ignore WordPress.Security.EscapeOutput
get_footer();

<?php
/**
 * Page introuvable : en-tête de page de la maquette et liens utiles.
 */
defined( 'ABSPATH' ) || exit;

get_header();
dualtech_entete_simple( 'Page introuvable', 'L’adresse demandée n’existe pas ou a changé. Les informations sur DualTech 2026 sont à portée de clic.' );
echo '<section class="sec sec--blanc"><div class="wrap"><div class="actions">'
	. '<a class="btn btn--primaire" href="' . esc_url( dualtech_url( 'accueil' ) ) . '">Retour à l’accueil</a>'
	. '<a class="btn btn--ghost" href="' . esc_url( dualtech_url( 'programme-2026' ) ) . '">Voir le programme</a>'
	. '<a class="btn btn--ghost" href="' . esc_url( dualtech_url( 'inscription' ) ) . '">S’inscrire</a></div></div></section>';
get_footer();

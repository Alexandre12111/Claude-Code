<?php
/**
 * Mise en page « texte » (mentions légales, politique de confidentialité, nouvelle page) :
 * en-tête de page sombre de la maquette, puis le texte de l'éditeur sur fond blanc.
 */
defined( 'ABSPATH' ) || exit;

dualtech_entete_simple( get_the_title(), has_excerpt() ? get_the_excerpt() : '' );
echo '<section class="sec sec--blanc"><div class="wrap prose">';
the_content();
echo '</div></section>';

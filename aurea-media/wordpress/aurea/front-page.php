<?php
/**
 * Accueil : la page choisie comme page d'accueil (Réglages → Lecture), sinon la liste des articles.
 */
defined( 'ABSPATH' ) || exit;

if ( 'page' !== get_option( 'show_on_front' ) ) {
	require AUREA_DIR . '/home.php';
	return;
}
require AUREA_DIR . '/page.php';

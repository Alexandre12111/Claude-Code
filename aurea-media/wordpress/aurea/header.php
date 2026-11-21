<?php
/**
 * En-tête : <head> géré par WordPress et Rank Math, puis l'en-tête de la maquette (parts/entete.html).
 */
defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
wp_body_open();
aurea_partie( 'entete' );

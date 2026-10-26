<?php
/**
 * Page introuvable.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="x-404-wp">
	<div class="x-404">
		<?php echo schiesser_mq_sceau(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="n">404</div>
		<h1><?php echo esc_html( schiesser_t( 'Diese Tür führt ins Leere' ) ); ?></h1>
		<p><?php echo esc_html( schiesser_t( 'Diese Seite gibt es nicht oder nicht mehr. Das Haus selbst hat seine Adresse nie gewechselt: am Marktplatz in Basel.' ) ); ?></p>
		<a class="b" href="<?php echo esc_url( schiesser_url_accueil() ); ?>"><span><?php echo esc_html( schiesser_t( 'Zur Startseite' ) ); ?></span> <span aria-hidden="true">→</span></a>
	</div>
</section>
<?php
get_footer();

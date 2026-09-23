<?php
/**
 * Page introuvable.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="x-404-wp">
	<div class="x-404">
		<span class="x-seal" aria-hidden="true"><svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="30" fill="none" stroke="currentColor" stroke-width="1"/><circle cx="32" cy="32" r="25.5" fill="none" stroke="currentColor" stroke-width="0.6" stroke-dasharray="1 3"/><text x="32" y="42.5" text-anchor="middle" font-family="Bodoni Moda, serif" font-weight="600" font-size="30" fill="currentColor">S</text></svg></span>
		<div class="n">404</div>
		<h1>La porte est ailleurs</h1>
		<p>Cette page n'existe pas, ou n'existe plus. La maison, elle, n'a jamais changé d'adresse : Marktplatz, à Bâle.</p>
		<a class="b" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>Revenir à l'accueil</span> <span aria-hidden="true">→</span></a>
	</div>
</section>
<?php
get_footer();

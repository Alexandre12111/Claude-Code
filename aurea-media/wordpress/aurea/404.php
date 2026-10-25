<?php
/**
 * Page introuvable.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="contenu">
<section class="phero phero--court page-404">
	<div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
	<div class="wrap">
		<span class="eyebrow rv">Erreur 404</span>
		<h1 class="split-words">Cette page n’existe <span class="gold">plus</span>.</h1>
		<p class="lead rv" style="margin-top:22px">Elle a peut être changé d’adresse. Voici les pages les plus consultées.</p>
		<div class="cta-row rv" style="margin-top:34px">
			<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">Retour à l’accueil <svg><use href="#i-arrow"/></svg></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( aurea_url( 'creation-site-vitrine' ) ); ?>">Création de site vitrine</a>
			<a class="btn btn--ghost" href="<?php echo esc_url( aurea_url( 'realisations' ) ); ?>">Réalisations</a>
			<a class="btn btn--ghost" href="<?php echo esc_url( aurea_url( 'contact' ) ); ?>">Contact</a>
		</div>
	</div>
</section>
</main>
<?php
get_footer();

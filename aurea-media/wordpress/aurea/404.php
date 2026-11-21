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
		<span class="eyebrow rv"><?php echo esc_html( aurea_t( 'Erreur 404' ) ); ?></span>
		<h1 class="split-words"><?php echo wp_kses( aurea_t( 'Cette page n’existe <span class="gold">plus</span>.' ), array( 'span' => array( 'class' => true ) ) ); ?></h1>
		<p class="lead rv" style="margin-top:22px"><?php echo esc_html( aurea_t( 'Elle a peut être changé d’adresse. Voici les pages les plus consultées.' ) ); ?></p>
		<div class="cta-row rv" style="margin-top:34px">
			<a class="btn" href="<?php echo esc_url( aurea_url( 'accueil' ) ); ?>"><?php echo esc_html( aurea_t( 'Retour à l’accueil' ) ); ?> <svg><use href="#i-arrow"/></svg></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( aurea_url( 'creation-site-vitrine' ) ); ?>"><?php echo esc_html( aurea_t( 'Création de site vitrine' ) ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( aurea_url( 'realisations' ) ); ?>"><?php echo esc_html( aurea_t( 'Réalisations' ) ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( aurea_url( 'contact' ) ); ?>"><?php echo esc_html( aurea_t( 'Contact' ) ); ?></a>
		</div>
	</div>
</section>
</main>
<?php
get_footer();

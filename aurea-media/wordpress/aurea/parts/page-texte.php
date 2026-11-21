<?php
/**
 * Mise en page « texte » : en-tête court, puis le contenu de l'éditeur dans une colonne de lecture.
 */
defined( 'ABSPATH' ) || exit;
?>
<main id="contenu">
<header class="phero phero--court">
	<div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
	<div class="wrap wrap--etroit">
		<?php echo aurea_ariane( array( array( get_the_title(), '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<h1 class="split-words"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?>
			<p class="lead rv" style="margin-top:22px"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</div>
</header>
<div class="sec paper article-zone">
	<div class="wrap wrap--etroit"><div class="prose"><?php the_content(); ?></div></div>
</div>
</main>

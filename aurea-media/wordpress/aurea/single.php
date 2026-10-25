<?php
/**
 * Article du blog : en-tête avec auteur et date de mise à jour, sommaire automatique,
 * contenu de l'éditeur, encadré auteur, puis trois articles à lire ensuite.
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	$c        = aurea_coordonnees();
	$rub      = aurea_rubrique();
	$sommaire = aurea_sommaire();
	?>
<main id="contenu">
<article class="article">
<header class="phero phero--court article-tete">
	<div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
	<div class="wrap wrap--etroit">
		<?php echo aurea_ariane( array( array( 'Conseils', aurea_url( 'conseils' ) ), array( get_the_title(), '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="eyebrow rv"><?php echo esc_html( ( $rub ? $rub->name . ' · ' : '' ) . aurea_temps_lecture() . ' min de lecture' ); ?></span>
		<h1 class="split-words"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?>
			<p class="lead rv" style="margin-top:22px"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
		<div class="auteur rv"><img src="<?php echo esc_url( AUREA_URI . '/assets/alexandre-coury.webp' ); ?>" alt="" width="1024" height="800"><span><b><?php echo esc_html( $c['fondateur'] ); ?></b>Développeur web, fondateur d’Aurea Media · Mis à jour le <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( get_the_modified_date( 'j F Y' ) ); ?></time></span></div>
	</div>
</header>
<div class="sec paper article-zone">
	<div class="wrap article-grille">
		<aside class="art-toc">
			<?php if ( count( $sommaire ) > 1 ) : ?>
				<span class="eyebrow">Sommaire</span>
				<?php foreach ( $sommaire as $s ) : ?>
					<a href="#<?php echo esc_attr( $s[0] ); ?>"><?php echo esc_html( $s[1] ); ?></a>
				<?php endforeach; ?>
			<?php endif; ?>
			<div class="art-toc-cta"><b>Un projet de site ?</b><span>Devis gratuit sous 24h.</span><a class="btn" href="<?php echo esc_url( aurea_url( 'contact' ) ); ?>">Demander un devis</a></div>
		</aside>
		<div class="prose">
			<?php the_content(); ?>
			<aside class="auteur-bloc">
				<img src="<?php echo esc_url( AUREA_URI . '/assets/alexandre-coury.webp' ); ?>" alt="<?php echo esc_attr( $c['fondateur'] ); ?>" width="1024" height="800" loading="lazy">
				<div>
					<b><?php echo esc_html( $c['fondateur'] ); ?></b>
					<p>Développeur et designer web, fondateur d’Aurea Media en <?php echo esc_html( $c['fondation'] ); ?>. Il conçoit des sites vitrines et e-commerce sur mesure pour les indépendants, TPE et PME, de l’audit à la mise en ligne.</p>
					<?php if ( $c['linkedin_moi'] ) : ?>
						<a href="<?php echo esc_url( $c['linkedin_moi'] ); ?>" target="_blank" rel="noopener me">Profil LinkedIn</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( aurea_url( 'agence' ) ); ?>">L’agence</a>
				</div>
			</aside>
		</div>
	</div>
</div>
</article>
	<?php
	$autres = new WP_Query( array(
		'post__not_in'        => array( get_the_ID() ),
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'cat'                 => $rub ? $rub->term_id : 0,
	) );
	if ( $autres->post_count < 3 ) {
		$autres = new WP_Query( array( 'post__not_in' => array( get_the_ID() ), 'posts_per_page' => 3, 'ignore_sticky_posts' => true ) );
	}
	if ( $autres->have_posts() ) :
		?>
<section class="sec">
	<div class="wrap">
		<div class="sec-hd"><div><span class="eyebrow rv">À lire aussi</span><h2 class="split-words">Pour aller <span class="gold">plus loin</span>.</h2></div></div>
		<div class="arts">
			<?php
			while ( $autres->have_posts() ) {
				$autres->the_post();
				get_template_part( 'parts/carte-article' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
		<?php
	endif;
	echo '</main>';
}
get_footer();

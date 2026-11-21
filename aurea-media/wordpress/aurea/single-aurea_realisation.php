<?php
/**
 * Fiche d'une réalisation (étude de cas), alimentée par Aurea Studio.
 *
 * Sans texte d'étude de cas, la fiche reste consultable mais n'est pas indexée (inc/seo.php).
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) {
	the_post();
	$r     = function_exists( 'aurea_realisation' ) ? aurea_realisation( get_post() ) : null;
	$etude = '' !== trim( wp_strip_all_tags( get_the_content() ) );
	?>
<main id="contenu">
<section class="phero phero--court">
	<div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
	<div class="wrap">
		<?php echo aurea_ariane( array( array( 'Réalisations', aurea_url( 'realisations' ) ), array( get_the_title(), '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="eyebrow rv"><?php echo esc_html( 'Étude de cas' . ( $r && $r['type']['nom'] ? ' · ' . $r['type']['nom'] : '' ) ); ?></span>
		<h1 class="split-words"><?php the_title(); ?></h1>
		<?php if ( $r && $r['accroche'] ) : ?>
			<p class="lead rv" style="margin-top:22px"><?php echo esc_html( $r['accroche'] ); ?></p>
		<?php endif; ?>
		<?php if ( $r ) : ?>
		<div class="cas-infos rv">
			<div><small>Client</small><b><?php the_title(); ?></b></div>
			<?php if ( $r['lieu'] ) : ?><div><small>Lieu</small><b><?php echo esc_html( $r['lieu'] ); ?></b></div><?php endif; ?>
			<?php if ( $r['secteur']['nom'] ) : ?><div><small>Secteur</small><b><?php echo esc_html( $r['secteur']['nom'] ); ?></b></div><?php endif; ?>
			<?php if ( $r['type']['nom'] ) : ?><div><small>Projet</small><b><?php echo esc_html( $r['type']['nom'] ); ?></b></div><?php endif; ?>
			<?php if ( $r['url'] ) : ?><a class="btn btn--ghost" href="<?php echo esc_url( $r['url'] ); ?>" target="_blank" rel="noopener">Voir le site <svg><use href="#i-out"/></svg></a><?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
	<?php if ( $r && $r['image'] ) : ?>
<section class="sec" style="padding-top:10px">
	<div class="wrap">
		<div class="cas-hero rv">
			<div class="laptop"><div class="laptop-screen"><div class="laptop-view"><?php echo str_replace( '<img ', '<img class="is-on" ', aurea_img_real( $r, 'Page d’accueil du site ' . $r['titre'], false, 'full' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div><div class="laptop-base"></div></div>
		</div>
	</div>
</section>
	<?php endif; ?>
	<?php if ( $etude ) : ?>
<section class="sec paper">
	<div class="gridbg" aria-hidden="true"></div>
	<div class="wrap">
		<div class="cas-texte">
			<?php $sommaire = aurea_sommaire(); ?>
			<aside class="cas-sommaire rv">
				<?php if ( $sommaire ) : ?>
					<span class="eyebrow">Sommaire</span>
					<?php foreach ( $sommaire as $s ) : ?>
						<a href="#<?php echo esc_attr( $s[0] ); ?>"><?php echo esc_html( $s[1] ); ?></a>
					<?php endforeach; ?>
				<?php endif; ?>
			</aside>
			<div class="cas-corps prose"><?php echo aurea_ancres_h2( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
	</div>
</section>
	<?php endif; ?>
	<?php if ( $r && $r['image'] ) : ?>
<section class="sec">
	<div class="wrap">
		<div class="sec-hd"><div><span class="eyebrow rv">Sur tous les écrans</span><h2 class="split-words">Pensé pour le <span class="gold">mobile</span> d’abord.</h2></div></div>
		<div class="cas-ecrans rv">
			<?php
			// Page complète du site client, qui défile au survol (capture longue livrée avec le thème)
			$cle_cap = function_exists( 'aurea_cle_real' ) ? aurea_cle_real( $r ) : '';
			$long    = $cle_cap ? AUREA_DIR . '/assets/realisations/' . $cle_cap . '-long.webp' : '';
			$taille  = ( $long && file_exists( $long ) ) ? @getimagesize( $long ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( $taille && $taille[1] > $taille[0] ) :
				?>
			<div class="browser browser--defile"><?php echo aurea_barre( $r['domaine'], 'div' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div class="defile"><img src="<?php echo esc_url( AUREA_URI . '/assets/realisations/' . $cle_cap . '-long.webp' ); ?>" alt="<?php echo esc_attr( 'Page d’accueil complète du site ' . $r['titre'] ); ?>" width="<?php echo (int) $taille[0]; ?>" height="<?php echo (int) $taille[1]; ?>" loading="lazy" decoding="async"></div><span class="defile-aide">Survolez pour parcourir la page</span></div>
			<?php else : ?>
			<div class="browser"><?php echo aurea_barre( $r['domaine'], 'div' ) . aurea_img_real( $r, 'Site ' . $r['titre'] . ' réalisé par Aurea Media' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
			<div class="phone"><div class="phone-view">
				<?php
				if ( $r['mobile'] ) {
					echo wp_get_attachment_image( $r['mobile'], 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) );
				} else {
					echo aurea_img_real( $r ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div></div>
		</div>
	</div>
</section>
	<?php endif; ?>
	<?php
	if ( function_exists( 'aurea_realisations' ) ) {
		$autres = array_slice( array_values( array_filter( aurea_realisations( array( 'type' => $r['type']['slug'] ?? '' ) ), function ( $x ) {
			return get_the_ID() !== $x['id'];
		} ) ), 0, 3 );
		if ( $autres ) {
			echo '<section class="sec paper"><div class="gridbg" aria-hidden="true"></div><div class="wrap"><div class="sec-hd"><div><span class="eyebrow rv">Projet suivant</span><h2 class="split-words">D’autres <span class="gold">réalisations</span>.</h2></div></div><div class="gal">';
			foreach ( $autres as $x ) {
				echo aurea_carte_projet( $x, 'secteur' ); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</div></div></section>';
		}
	}
	echo aurea_contenu_livre( 'cas-appel' ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</main>';
}
get_footer();

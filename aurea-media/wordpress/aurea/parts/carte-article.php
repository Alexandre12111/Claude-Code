<?php
/**
 * Carte d'un article (liste des conseils, « À lire aussi »).
 *
 * @var array $args { une: bool (grande carte « À la une »), titre: h2|h3 }
 */
defined( 'ABSPATH' ) || exit;

$une     = ! empty( $args['une'] );
$rub     = aurea_rubrique();
$etiquet = trim( ( $une ? 'À la une · ' : '' ) . ( $rub ? $rub->name . ' · ' : '' ) . aurea_temps_lecture() . ' min' );
$image   = has_post_thumbnail()
	? get_the_post_thumbnail( null, $une ? 'large' : 'aurea-carte', array( 'alt' => '', 'loading' => $une ? 'eager' : 'lazy' ) )
	: '<img src="' . esc_url( AUREA_URI . '/assets/realisations/havel-trading.webp' ) . '" alt="" width="1200" height="620" loading="lazy">';
?>
<a class="<?php echo $une ? 'art-une' : 'art'; ?> rv" href="<?php the_permalink(); ?>">
	<div class="art-img"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<div class="art-txt">
		<span class="art-cat"><?php echo esc_html( $etiquet ); ?></span>
		<?php if ( $une ) : ?>
			<h2><?php the_title(); ?></h2>
		<?php else : ?>
			<h3><?php the_title(); ?></h3>
		<?php endif; ?>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php if ( $une ) : ?>
			<div class="auteur"><img src="<?php echo esc_url( AUREA_URI . '/assets/alexandre-coury.webp' ); ?>" alt="" width="1024" height="800"><span><b><?php echo esc_html( aurea_coordonnees()['fondateur'] ); ?></b>Mis à jour le <?php echo esc_html( get_the_modified_date( 'j F Y' ) ); ?></span></div>
		<?php else : ?>
			<span class="link">Lire l’article <svg><use href="#i-arrow"/></svg></span>
		<?php endif; ?>
	</div>
</a>

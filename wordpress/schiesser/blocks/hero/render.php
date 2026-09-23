<?php
/**
 * Bloc Hero : rendu sur le site.
 *
 * @var array $attributes Valeurs saisies dans l'éditeur.
 */

defined( 'ABSPATH' ) || exit;

$a       = $attributes;
$hauteur = in_array( $a['hauteur'] ?? 'page', array( 'accueil', 'page', 'compacte' ), true ) ? $a['hauteur'] : 'page';
$image   = $a['imageId'] ? wp_get_attachment_image_url( $a['imageId'], 'full' ) : '';
$image   = $image ?: ( $a['imageUrl'] ?? '' );
$alt     = $a['imageId'] ? get_post_meta( $a['imageId'], '_wp_attachment_image_alt', true ) : ( $a['imageAlt'] ?? '' );

$boutons = array();
if ( ! empty( $a['bouton1Texte'] ) ) {
	$boutons[] = array( $a['bouton1Texte'], $a['bouton1Lien'] ?: '#', 'btn btn-solid', true );
}
if ( ! empty( $a['bouton2Texte'] ) ) {
	$boutons[] = array( $a['bouton2Texte'], $a['bouton2Lien'] ?: '#', 'btn btn-ghost', false );
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'hero hero--' . $hauteur ) ); ?>>
	<div class="hero-media">
		<?php if ( $image ) : ?>
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $alt ); ?>" fetchpriority="high">
		<?php endif; ?>
	</div>
	<div class="hero-copy">
		<div class="hero-inner">
			<?php if ( ! empty( $a['ariane'] ) && ! is_front_page() ) : ?>
				<div class="crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a> · <span><?php echo esc_html( get_the_title() ); ?></span></div>
			<?php endif; ?>

			<?php if ( ! empty( $a['surtitre'] ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( wp_strip_all_tags( $a['surtitre'] ) ); ?></p>
			<?php endif; ?>

			<h1><?php echo schiesser_kses_titre( $a['titre'] ?: get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>

			<?php if ( ! empty( $a['texte'] ) ) : ?>
				<p class="hero-sub"><?php echo schiesser_kses_titre( $a['texte'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>

			<?php if ( $boutons ) : ?>
				<div class="hero-actions">
					<?php foreach ( $boutons as $b ) : ?>
						<a class="<?php echo esc_attr( $b[2] ); ?>" href="<?php echo esc_url( $b[1] ); ?>">
							<span><?php echo esc_html( wp_strip_all_tags( $b[0] ) ); ?></span><?php if ( $b[3] ) : ?> <span class="a" aria-hidden="true">→</span><?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			if ( ! empty( $a['sceau'] ) ) {
				echo schiesser_sceau_svg(); // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</div>
	</div>
</section>

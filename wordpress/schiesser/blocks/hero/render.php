<?php
/**
 * Bloc Hero : rendu sur le site.
 *
 * @var array $attributes Valeurs saisies dans l'éditeur.
 */

defined( 'ABSPATH' ) || exit;

$a       = $attributes;
$hauteur = in_array( $a['hauteur'] ?? 'page', array( 'accueil', 'page', 'compacte' ), true ) ? $a['hauteur'] : 'page';
$accent  = in_array( $a['accent'] ?? 'creme', array( 'creme', 'menthe', 'blanc' ), true ) ? $a['accent'] : 'creme';
$styles  = array( 'creme' => 'btn btn-solid', 'vert' => 'btn btn-kir', 'contour' => 'btn btn-ghost' );

/* Texte alternatif : celui saisi dans le bloc, sinon celui de la médiathèque. */
$alt = trim( (string) ( $a['imageAlt'] ?? '' ) );
if ( '' === $alt && ! empty( $a['imageId'] ) ) {
	$alt = (string) get_post_meta( $a['imageId'], '_wp_attachment_image_alt', true );
}

$boutons = array();
foreach ( array( 1, 2 ) as $n ) {
	$texte = trim( wp_strip_all_tags( $a[ 'bouton' . $n . 'Texte' ] ?? '' ) );
	if ( '' === $texte ) {
		continue;
	}
	$style     = $a[ 'bouton' . $n . 'Style' ] ?? ( 1 === $n ? 'creme' : 'contour' );
	$boutons[] = array(
		'texte'  => $texte,
		'lien'   => $a[ 'bouton' . $n . 'Lien' ] ?: '#',
		'classe' => $styles[ $style ] ?? $styles['creme'],
		'fleche' => 1 === $n,
	);
}

$filtre  = in_array( $a['filtre'] ?? '', array( 'sepia-leger', 'sepia' ), true ) ? ' hero--' . $a['filtre'] : '';
$classes = 'hero hero--' . $hauteur . ' hero--accent-' . $accent . $filtre;
?>
<?php if ( ! empty( $a['progression'] ) ) : ?><div class="prog" aria-hidden="true"></div><?php endif; ?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $classes ) ); ?>>
	<div class="hero-media">
		<?php
		if ( ! empty( $a['imageId'] ) && wp_attachment_is_image( $a['imageId'] ) ) {
			// Image responsive : le navigateur choisit la bonne taille (bien plus léger sur mobile).
			echo wp_get_attachment_image( $a['imageId'], 'full', false, array(
				'alt'           => $alt,
				'sizes'         => '100vw',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
			) );
		} elseif ( ! empty( $a['imageUrl'] ) ) {
			echo '<img src="' . esc_url( $a['imageUrl'] ) . '" alt="' . esc_attr( $alt ) . '" fetchpriority="high">';
		}
		?>
	</div>
	<div class="hero-copy">
		<div class="hero-inner">
			<?php if ( ! empty( $a['ariane'] ) && ! is_front_page() ) : ?>
				<nav class="crumb" aria-label="Fil d'Ariane">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a>
					<span aria-hidden="true">·</span>
					<span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
				</nav>
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
						<a class="<?php echo esc_attr( $b['classe'] ); ?>" href="<?php echo esc_url( $b['lien'] ); ?>">
							<span><?php echo esc_html( $b['texte'] ); ?></span><?php if ( $b['fleche'] ) : ?> <span class="a" aria-hidden="true">→</span><?php endif; ?>
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

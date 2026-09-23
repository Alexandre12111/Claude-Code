<?php
/**
 * Bloc Section : rendu sur le site.
 *
 * @var array  $attributes Réglages de la section.
 * @var string $content    Contenu libre (blocs WordPress placés à l'intérieur).
 */

defined( 'ABSPATH' ) || exit;

$a     = $attributes;
$fonds = array(
	'papier' => '',
	'clair'  => 'bg-soft',
	'alterne' => 'bg-alt',
	'sable'  => 'bg-deep',
	'sombre' => 'bg-ink',
);
$fond    = isset( $fonds[ $a['fond'] ?? '' ] ) ? $a['fond'] : 'papier';
$classes = trim( 'sec sec-libre sec--' . $fond . ' ' . $fonds[ $fond ] );
$corps   = 'sec-body sec-body--' . ( 'lecture' === ( $a['largeur'] ?? '' ) ? 'lecture' : 'large' ) . ( ! empty( $a['centre'] ) ? ' sec-body--centre' : '' );
$ancre   = sanitize_title( $a['ancre'] ?? '' );
$entete  = ! empty( $a['entete'] ) && ( '' !== trim( wp_strip_all_tags( $a['titre'] ?? '' ) ) || '' !== trim( $a['numero'] ?? '' ) );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $classes, 'id' => $ancre ?: null ) ); ?>>
	<div class="wrap">
		<?php if ( $entete ) : ?>
			<div class="sec-head rv<?php echo ! empty( $a['centre'] ) ? ' sec-head--centre' : ''; ?>">
				<span class="idx<?php echo '' === trim( $a['numero'] ?? '' ) ? ' idx--vide' : ''; ?>"><i class="x-diamond"></i><?php echo esc_html( $a['numero'] ?? '' ); ?></span>
				<?php if ( '' !== trim( wp_strip_all_tags( $a['titre'] ?? '' ) ) ) : ?>
					<h2><?php echo schiesser_kses_titre( $a['titre'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $a['note'] ) ) : ?>
					<p class="note"><?php echo schiesser_kses_titre( $a['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="<?php echo esc_attr( $corps ); ?>">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- contenu des blocs, déjà filtré par WordPress ?>
		</div>
	</div>
</section>

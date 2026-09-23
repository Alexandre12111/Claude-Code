<?php
/**
 * Bandeau de titre des pages sans grande photo (Hero) :
 * fil d'Ariane, surtitre, titre principal (H1) et courte introduction.
 *
 * @var array $args titre, surtitre, intro, ariane (liste de [ libellé, lien ]), h1 (true par défaut).
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array(
	'titre'    => get_the_title(),
	'surtitre' => '',
	'intro'    => '',
	'ariane'   => array(),
	'h1'       => true,
) );
$balise = $args['h1'] ? 'h1' : 'p';
?>
<section class="titre-page">
	<div class="wrap">
		<nav class="crumb crumb--clair" aria-label="Fil d'Ariane">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a>
			<?php foreach ( $args['ariane'] as $lien ) : ?>
				<span aria-hidden="true">·</span><a href="<?php echo esc_url( $lien[1] ); ?>"><?php echo esc_html( $lien[0] ); ?></a>
			<?php endforeach; ?>
			<span aria-hidden="true">·</span><span aria-current="page"><?php echo esc_html( wp_strip_all_tags( $args['titre'] ) ); ?></span>
		</nav>
		<?php if ( $args['surtitre'] ) : ?>
			<p class="eyebrow"><?php echo esc_html( $args['surtitre'] ); ?></p>
		<?php endif; ?>
		<<?php echo $balise; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="titre-page-h"><?php echo esc_html( wp_strip_all_tags( $args['titre'] ) ); ?></<?php echo $balise; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<?php if ( $args['intro'] ) : ?>
			<p class="titre-page-intro"><?php echo esc_html( $args['intro'] ); ?></p>
		<?php endif; ?>
	</div>
</section>

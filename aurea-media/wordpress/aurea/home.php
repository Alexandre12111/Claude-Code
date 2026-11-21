<?php
/**
 * Blog « Conseils » (page des articles), rubriques et archives.
 */
defined( 'ABSPATH' ) || exit;

get_header();
$premiere = ! is_paged() && is_home();
?>
<main id="contenu">
<?php
if ( is_home() ) {
	echo aurea_contenu_livre( 'blog-entete' ); // phpcs:ignore WordPress.Security.EscapeOutput
} else {
	$titre = is_category() || is_tag() ? single_term_title( '', false ) : ( is_search() ? 'Recherche : ' . get_search_query() : get_the_archive_title() );
	$desc  = is_category() || is_tag() ? term_description() : '';
	?>
	<section class="phero phero--court">
		<div class="gridbg" aria-hidden="true"></div><div class="orb orb--a" aria-hidden="true"></div>
		<div class="wrap">
			<?php echo aurea_ariane( array( array( 'Conseils', aurea_url( 'conseils' ) ), array( $titre, '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="eyebrow rv">Conseils</span>
			<h1 class="split-words"><?php echo esc_html( $titre ); ?></h1>
			<?php if ( $desc ) : ?>
				<div class="lead rv" style="margin-top:24px"><?php echo wp_kses_post( $desc ); ?></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
?>
<section class="sec" style="padding-top:10px">
	<div class="wrap">
		<?php
		if ( have_posts() ) {
			if ( $premiere ) {
				the_post();
				get_template_part( 'parts/carte-article', null, array( 'une' => true ) );
			}
			$rubriques = get_categories( array( 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_category' ) ) ) );
			if ( $rubriques ) {
				echo '<nav class="filtres rv" style="margin-top:56px" aria-label="Rubriques">';
				echo '<a class="filtre' . ( is_home() ? ' is-on' : '' ) . '" href="' . esc_url( aurea_url( 'conseils' ) ) . '">Tous</a>';
				foreach ( $rubriques as $r ) {
					echo '<a class="filtre' . ( is_category( $r->term_id ) ? ' is-on' : '' ) . '" href="' . esc_url( get_category_link( $r ) ) . '">' . esc_html( $r->name ) . '</a>';
				}
				echo '</nav>';
			}
			echo '<div class="arts">';
			while ( have_posts() ) {
				the_post();
				get_template_part( 'parts/carte-article' );
			}
			echo '</div>';
			$pagination = paginate_links( array( 'type' => 'list', 'prev_text' => '← Plus récents', 'next_text' => 'Plus anciens →', 'mid_size' => 1 ) );
			if ( $pagination ) {
				echo '<nav class="pagination" aria-label="Pages">' . $pagination . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		} else {
			echo '<p class="lead">Aucun article pour le moment.</p>';
		}
		?>
	</div>
</section>
<?php echo aurea_contenu_livre( 'blog-appel' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</main>
<?php
get_footer();

<?php
/**
 * Bloc « Grille des produits » : rendu sur le site.
 * Les produits viennent du menu « Produits » ; rien n'est saisi ici.
 *
 * @var array $attributes Valeurs saisies dans l'éditeur.
 */

defined( 'ABSPATH' ) || exit;

$a       = $attributes;
$apercu  = ! empty( $a['apercu'] ); // aperçu dans l'éditeur : grille seule
$requete = new WP_Query( array(
	'post_type'      => SCHIESSER_PRODUIT,
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	'no_found_rows'  => true,
) );

$produits = array();
foreach ( $requete->posts as $p ) {
	$produits[] = schiesser_donnees_produit( $p );
}

$categories = get_terms( array(
	'taxonomy'   => SCHIESSER_CATEGORIE,
	'hide_empty' => true,
	'orderby'    => 'term_id',
) );
$categories = is_wp_error( $categories ) ? array() : $categories;

/* Données de la fiche détaillée, lues par assets/js/boutique.js */
$fiches = array();
foreach ( $produits as $p ) {
	$lignes = $p['fiche'];
	if ( $p['accord'] ) {
		$lignes[] = array( 'Accord', $p['accord'], 1 );
	}
	if ( $p['origine'] ) {
		$lignes[] = array( 'Origine', $p['origine'], 1 );
	}
	$fiches[] = array(
		'nom'         => $p['nom'],
		'categorie'   => $p['categorie'],
		'description' => $p['description'],
		'prix'        => $p['prix'],
		'unite'       => $p['unite'],
		'image'       => $p['image'],
		'alt'         => $p['alt'] ?: $p['nom'],
		'lignes'      => $lignes,
	);
}

$pluriel = count( $produits ) > 1 ? 'produits' : 'produit';

ob_start();
?>
<div class="js-boutique">
	<?php if ( ! $produits ) : ?>
		<p class="boutique-vide">Aucun produit pour le moment. Ajoutez-en depuis le menu <strong>Produits</strong> de l'administration.</p>
	<?php else : ?>
		<div class="shop-bar">
			<div class="shop-filters" role="group" aria-label="Filtrer par catégorie">
				<button type="button" class="fchip on" data-filtre="">Tout</button>
				<?php foreach ( $categories as $cat ) : ?>
					<button type="button" class="fchip" data-filtre="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></button>
				<?php endforeach; ?>
			</div>
			<div class="shop-count" aria-live="polite"><b class="js-nombre"><?php echo (int) count( $produits ); ?></b> <span class="js-libelle"><?php echo esc_html( $pluriel ); ?></span></div>
		</div>

		<div class="shop-grid">
			<?php foreach ( $produits as $i => $p ) : ?>
				<button type="button" class="card" data-i="<?php echo (int) $i; ?>" data-categories="<?php echo esc_attr( implode( ' ', $p['categories'] ) ); ?>" style="animation-delay:<?php echo esc_attr( $i * 0.04 ); ?>s">
					<span class="card-im">
						<?php if ( $p['image'] ) : ?>
							<img src="<?php echo esc_url( $p['image'] ); ?>" alt="<?php echo esc_attr( $p['alt'] ); ?>" loading="lazy" decoding="async">
						<?php endif; ?>
						<span class="tint"></span>
						<?php if ( $p['badge'] ) : ?>
							<span class="card-tag<?php echo 'menthe' === $p['badge_style'] ? ' gold' : ''; ?>"><?php echo esc_html( $p['badge'] ); ?></span>
						<?php endif; ?>
						<span class="card-see">Voir la fiche</span>
					</span>
					<span class="card-body">
						<span class="card-plate">Pl. <?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="card-name"><?php echo esc_html( $p['nom'] ); ?></span>
						<?php if ( $p['prix'] ) : ?><span class="card-price"><?php echo esc_html( $p['prix'] ); ?></span><?php endif; ?>
						<?php if ( $p['unite'] ) : ?><span class="card-unit"><?php echo esc_html( $p['unite'] ); ?></span><?php endif; ?>
					</span>
				</button>
			<?php endforeach; ?>
		</div>

		<?php if ( ! $apercu ) : ?>
			<script type="application/json" class="js-boutique-data"><?php echo wp_json_encode( $fiches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>

			<div class="sheet js-sheet" role="dialog" aria-modal="true" aria-label="Fiche produit" hidden>
				<div class="sh-wrap">
					<div class="sh-in">
						<div class="sh-im">
							<img class="js-sh-img" src="" alt="">
							<button type="button" class="sh-close js-sh-fermer" aria-label="Fermer">✕</button>
						</div>
						<div class="sh-tx">
							<span class="sh-k js-sh-cat"></span>
							<h3 class="js-sh-nom"></h3>
							<p class="sd js-sh-desc"></p>
							<div class="sh-rows js-sh-lignes"></div>
							<div class="sh-price"><span class="pk js-sh-unite"></span><span class="pv js-sh-prix"></span></div>
							<div class="sh-foot">
								<a class="btn btn-kir" href="<?php echo esc_url( 'mailto:' . schiesser_reglage( 'email' ) ); ?>"><span>Commander</span> <span class="a" aria-hidden="true">→</span></a>
								<div class="sh-nav">
									<button type="button" class="js-sh-prec" aria-label="Produit précédent">←</button>
									<button type="button" class="js-sh-suiv" aria-label="Produit suivant">→</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php
$grille = ob_get_clean();

if ( $apercu ) {
	echo $grille; // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}

$ancre = sanitize_title( $a['ancre'] ?? 'catalogue' );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'sec', 'id' => $ancre ?: null ) ); ?>>
	<div class="wrap">
		<div class="sec-head rv">
			<span class="idx"><i class="x-diamond"></i><?php echo esc_html( $a['numero'] ); ?></span>
			<h2><?php echo schiesser_kses_titre( $a['titre'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php if ( $a['note'] ) : ?><p class="note"><?php echo schiesser_kses_titre( $a['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endif; ?>
		</div>
		<?php echo $grille; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>

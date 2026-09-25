<?php
/**
 * Page d'un produit (/produits/nom-du-produit/).
 * Tout vient du formulaire du menu « Produits » : photo, nom, prix,
 * fiche détaillée et présentation détaillée (zone de texte principale).
 */
defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	$p        = schiesser_donnees_produit( get_post() );
	$boutique = schiesser_url_boutique();
	$id_page  = url_to_postid( $boutique );
	$nom_b    = $id_page ? get_the_title( $id_page ) : 'La boutique';
	$email    = schiesser_reglage( 'email' );
	$tel      = schiesser_reglage( 'telephone' );
	$lignes   = $p['fiche'];
	if ( $p['accord'] ) {
		$lignes[] = array( 'Accord', $p['accord'], 1 );
	}
	if ( $p['origine'] ) {
		$lignes[] = array( 'Origine', $p['origine'], 1 );
	}
	$lies = schiesser_produits_lies( get_the_ID(), 4 );
	?>
	<section class="produit">
		<div class="wrap produit-grille">
			<div class="produit-photo">
				<?php
				if ( $p['image_id'] ) {
					echo schiesser_image( $p['image_id'], 'large', array( // phpcs:ignore WordPress.Security.EscapeOutput
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(min-width: 900px) 50vw, 100vw',
					), $p['nom'] );
				}
				?>
				<?php if ( $p['badge'] ) : ?>
					<span class="card-tag<?php echo 'menthe' === $p['badge_style'] ? ' gold' : ''; ?>"><?php echo esc_html( $p['badge'] ); ?></span>
				<?php endif; ?>
			</div>

			<div class="produit-texte">
				<nav class="crumb crumb--clair" aria-label="Fil d'Ariane">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a>
					<span aria-hidden="true">·</span>
					<a href="<?php echo esc_url( $boutique ); ?>"><?php echo esc_html( $nom_b ); ?></a>
					<span aria-hidden="true">·</span>
					<span aria-current="page"><?php echo esc_html( $p['nom'] ); ?></span>
				</nav>
				<?php if ( $p['categorie'] ) : ?>
					<span class="sh-k"><?php echo esc_html( $p['categorie'] ); ?></span>
				<?php endif; ?>
				<h1><?php echo esc_html( $p['nom'] ); ?></h1>
				<?php if ( $p['description'] ) : ?>
					<p class="produit-desc"><?php echo esc_html( $p['description'] ); ?></p>
				<?php endif; ?>

				<?php if ( $lignes ) : ?>
					<div class="sh-rows">
						<?php foreach ( $lignes as $l ) : if ( empty( $l[0] ) && empty( $l[1] ) ) { continue; } ?>
							<div class="sh-row<?php echo ! empty( $l[2] ) ? ' x-extra' : ''; ?>"><span class="k"><?php echo esc_html( $l[0] ); ?></span><span><?php echo esc_html( $l[1] ); ?></span></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $p['al'] ) && ( $p['al']['renseigne'] || $p['al']['regimes'] ) ) : ?>
					<div class="produit-al">
						<p class="produit-al-k">Allergènes et régimes</p>
						<?php
						echo schiesser_allergenes_pictos( $p['al'], true ); // phpcs:ignore WordPress.Security.EscapeOutput
						if ( $p['al']['renseigne'] && ! $p['al']['allergenes'] ) {
							echo '<p class="produit-al-t">Ne contient aucun des 14 allergènes à déclarer.</p>';
						}
						if ( '' !== $p['al']['traces'] ) {
							echo '<p class="produit-al-t">' . esc_html( $p['al']['traces'] ) . '</p>';
						}
						echo schiesser_allergenes_note(); // phpcs:ignore WordPress.Security.EscapeOutput
						?>
					</div>
				<?php endif; ?>

				<?php if ( $p['prix'] ) : ?>
					<div class="sh-price"><span class="pk">Prix<?php echo $p['unite'] ? ' · ' . esc_html( $p['unite'] ) : ''; ?></span><span class="pv"><?php echo esc_html( $p['prix'] ); ?></span></div>
				<?php endif; ?>

				<?php if ( schiesser_est_epuise( $p['id'] ) ) : ?>
					<p class="produit-epuise"><strong>Épuisé aujourd’hui.</strong> De retour dès demain : appelez pour le réserver.</p>
				<?php endif; ?>
				<div class="produit-actions">
					<?php echo schiesser_boutons_commande( $p ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
				<p class="produit-note"><i class="x-diamond" aria-hidden="true"></i> Fait main par la <?php echo esc_html( schiesser_reglage( 'nom_etablissement' ) ?: get_bloginfo( 'name' ) ); ?>. Retrait en boutique, <?php echo esc_html( implode( ', ', array_filter( schiesser_adresse_lignes() ) ) ); ?> · <?php echo esc_html( schiesser_horaires_resume() ); ?></p>
				<?php echo schiesser_partage( $p['url'], $p['nom'] . ' · ' . ( schiesser_reglage( 'nom_etablissement' ) ?: get_bloginfo( 'name' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</section>

	<?php if ( $email || $tel ) : ?>
		<?php // Sur mobile : prix et commande toujours à portée de pouce, tant que les boutons de la fiche ne sont pas à l'écran. ?>
		<div class="produit-barre js-produit-barre">
			<?php if ( $p['prix'] ) : ?>
				<span class="produit-barre-prix"><span class="pv"><?php echo esc_html( $p['prix'] ); ?></span><?php if ( $p['unite'] ) : ?><span class="pk"><?php echo esc_html( $p['unite'] ); ?></span><?php endif; ?></span>
			<?php endif; ?>
			<?php echo schiesser_boutons_commande( $p, 'barre' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
		<section class="sec bg-soft">
			<div class="wrap">
				<div class="sec-head rv">
					<span class="idx"><i class="x-diamond"></i>01</span>
					<h2><?php echo esc_html( $p['nom'] ); ?>, <em>en détail</em></h2>
				</div>
				<div class="sec-body sec-body--lecture">
					<?php the_content(); ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $lies ) : ?>
		<section class="sec">
			<div class="wrap">
				<div class="sec-head rv">
					<span class="idx"><i class="x-diamond"></i><?php echo '' !== trim( wp_strip_all_tags( get_the_content() ) ) ? '02' : '01'; ?></span>
					<h2>Dans la même <em>vitrine</em></h2>
					<p class="note"><a class="lien-fleche" href="<?php echo esc_url( $boutique ); ?>">Voir toute la boutique <span aria-hidden="true">→</span></a></p>
				</div>
				<div class="shop-grid">
					<?php foreach ( $lies as $i => $lie ) { schiesser_carte_produit( $lie, $i, false ); } ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;
get_footer();

<?php
/**
 * Bloc « Grille des produits » : rendu sur le site.
 * Les produits viennent du menu « Produits » (ou de WooCommerce une fois installé) ;
 * rien n'est saisi ici. Chaque carte est un lien vers la page du produit :
 * un clic ouvre la fiche rapide, un clic molette (ou Ctrl+clic) ouvre la page.
 *
 * @var array $attributes Valeurs saisies dans l'éditeur.
 */

defined( 'ABSPATH' ) || exit;

$a        = $attributes;
$apercu   = ! empty( $a['apercu'] ); // aperçu dans l'éditeur : grille seule
$limite   = max( 0, (int) ( $a['limite'] ?? 0 ) );
$source   = in_array( $a['source'] ?? 'auto', array( 'auto', 'maison', 'woocommerce' ), true ) ? $a['source'] : 'auto';
$produits = schiesser_liste_produits( $source, $limite );

if ( ! $produits && ! $apercu && ! current_user_can( 'edit_posts' ) ) {
	return; // aucun produit : la section n'apparaît pas aux visiteurs
}

/* Filtres : les catégories des produits affichés, dans l'ordre où elles apparaissent. */
$categories = array();
foreach ( $produits as $p ) {
	foreach ( $p['categories'] as $slug => $nom ) {
		$categories[ $slug ] = $nom;
	}
}
$filtres = ! empty( $a['filtres'] ) && count( $categories ) > 1;

/* Données de la fiche rapide, lues par assets/js/boutique.js */
$fiches = array();
foreach ( $produits as $p ) {
	$lignes = array();
	foreach ( $p['fiche'] as $l ) {
		$lignes[] = array( $l[0] ?? '', $l[1] ?? '', 0 );
	}
	if ( $p['accord'] ) {
		$lignes[] = array( 'Accord', $p['accord'], 1 );
	}
	if ( $p['origine'] ) {
		$lignes[] = array( 'Origine', $p['origine'], 1 );
	}
	if ( ! empty( $p['al'] ) ) {
		if ( $p['al']['renseigne'] ) {
			$lignes[] = array( 'Allergènes', schiesser_allergenes_texte( $p['al'] ), 1 );
		}
		if ( $p['al']['regimes'] ) {
			$lignes[] = array( 'Convient', implode( ', ', array_map( function ( $k ) {
				return schiesser_regimes_liste()[ $k ][0];
			}, $p['al']['regimes'] ) ), 1 );
		}
		if ( '' !== $p['al']['traces'] ) {
			$lignes[] = array( 'Traces', $p['al']['traces'], 1 );
		}
	}
	$fiches[] = array(
		'nom'         => $p['nom'],
		'url'         => $p['url'],
		'categorie'   => $p['categorie'],
		'description' => $p['description'],
		'prix'        => $p['prix'],
		'unite'       => $p['unite'],
		'image'       => $p['image'],
		'alt'         => $p['alt'],
		'lignes'      => $lignes,
		'panier'      => $p['achetable'] ? $p['panier'] : '',
	);
}

$pluriel = count( $produits ) > 1 ? 'produits' : 'produit';
$email   = schiesser_reglage( 'email' );
$id_nom  = 'fiche-nom-' . ( sanitize_title( $a['ancre'] ?? '' ) ?: 'produits' );

ob_start();
?>
<div class="js-boutique" data-email="<?php echo esc_attr( $email ); ?>">
	<?php if ( ! $produits ) : ?>
		<p class="boutique-vide">Aucun produit publié pour le moment. Ajoutez-en depuis le menu <strong>Produits</strong> de l'administration : ils apparaîtront ici automatiquement.</p>
	<?php else : ?>
		<?php if ( $filtres ) : ?>
			<div class="shop-bar">
				<div class="shop-filters" role="group" aria-label="Filtrer par catégorie">
					<button type="button" class="fchip on" data-filtre="" aria-pressed="true">Tout</button>
					<?php foreach ( $categories as $slug => $nom ) : ?>
						<button type="button" class="fchip" data-filtre="<?php echo esc_attr( $slug ); ?>" aria-pressed="false"><?php echo esc_html( $nom ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="shop-count" aria-live="polite"><b class="js-nombre"><?php echo (int) count( $produits ); ?></b> <span class="js-libelle"><?php echo esc_html( $pluriel ); ?></span></div>
			</div>
		<?php endif; ?>

		<?php
		if ( ! $apercu ) {
			echo schiesser_allergenes_filtres( array_filter( array_column( $produits, 'al' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
		<div class="shop-grid">
			<?php foreach ( $produits as $i => $p ) { schiesser_carte_produit( $p, $i, ! $apercu ); } ?>
		</div>

		<?php if ( ! $apercu ) : ?>
			<script type="application/json" class="js-boutique-data"><?php echo wp_json_encode( $fiches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>

			<div class="sheet js-sheet" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id_nom ); ?>" hidden>
				<div class="sh-wrap">
					<div class="sh-in">
						<div class="sh-im">
							<img class="js-sh-img" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt="" decoding="async">
							<button type="button" class="sh-close js-sh-fermer" aria-label="Fermer la fiche">✕</button>
						</div>
						<div class="sh-tx">
							<span class="sh-k js-sh-cat"></span>
							<p class="sh-nom js-sh-nom" id="<?php echo esc_attr( $id_nom ); ?>"></p>
							<p class="sd js-sh-desc"></p>
							<div class="sh-rows js-sh-lignes"></div>
							<div class="sh-price"><span class="pk js-sh-unite"></span><span class="pv js-sh-prix"></span></div>
							<div class="sh-foot">
								<div class="sh-actions">
									<a class="btn btn-kir js-sh-action" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><span class="js-sh-action-texte">Commander</span> <span class="a" aria-hidden="true">→</span></a>
									<a class="sh-lien js-sh-page" href="#">Voir la page du produit</a>
								</div>
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

$fonds  = array( 'papier' => '', 'clair' => 'bg-soft', 'alterne' => 'bg-alt', 'sable' => 'bg-deep' );
$fond   = isset( $fonds[ $a['fond'] ?? '' ] ) ? $a['fond'] : 'papier';
$ancre  = sanitize_title( $a['ancre'] ?? 'catalogue' );
$titre  = trim( wp_strip_all_tags( $a['titre'] ?? '' ) );
$lien   = trim( wp_strip_all_tags( $a['lienTexte'] ?? '' ) );
$classe = trim( 'sec sec-produits ' . $fonds[ $fond ] );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => $classe, 'id' => $ancre ?: null ) ); ?>>
	<div class="wrap">
		<?php if ( '' !== $titre || '' !== trim( $a['numero'] ?? '' ) ) : ?>
			<div class="sec-head rv">
				<span class="idx<?php echo '' === trim( $a['numero'] ?? '' ) ? ' idx--vide' : ''; ?>"><i class="x-diamond"></i><?php echo esc_html( $a['numero'] ?? '' ); ?></span>
				<?php if ( '' !== $titre ) : ?><h2><?php echo schiesser_kses_titre( $a['titre'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2><?php endif; ?>
				<?php if ( ! empty( $a['note'] ) ) : ?><p class="note"><?php echo schiesser_kses_titre( $a['note'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endif; ?>
			</div>
		<?php endif; ?>
		<?php echo $grille; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( '' !== $lien ) : ?>
			<div class="shop-tout">
				<a class="btn btn-line" href="<?php echo esc_url( $a['lienUrl'] ?: schiesser_url_boutique() ); ?>"><span><?php echo esc_html( $lien ); ?></span> <span class="a" aria-hidden="true">→</span></a>
			</div>
		<?php endif; ?>
	</div>
</section>

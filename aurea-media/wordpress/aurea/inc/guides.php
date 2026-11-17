<?php
/**
 * Maillage « hub and spoke » : chaque page piliers (services, secteurs, pages locales) affiche
 * ses guides du blog dans une section « Pour aller plus loin », juste avant l'appel final.
 * Seuls les articles publiés apparaissent ; à défaut, la section est complétée par les derniers articles.
 */
defined( 'ABSPATH' ) || exit;

/** Page (slug FR) => articles conseillés, du plus pertinent au moins pertinent. */
function aurea_guides_carte() {
	return apply_filters(
		'aurea_guides_carte',
		array(
			'creation-site-vitrine'          => array( 'prix-site-vitrine', 'pages-indispensables-site-vitrine', 'site-vitrine-ou-site-e-commerce' ),
			'creation-site-ecommerce'        => array( 'prix-boutique-en-ligne', 'woocommerce-ou-shopify', 'site-vitrine-ou-site-e-commerce' ),
			'refonte-site-internet'          => array( 'refonte-site-referencement', 'prix-refonte-site-internet', 'choisir-agence-web' ),
			'logo-identite-visuelle'         => array( 'refonte-identite-visuelle', 'choisir-agence-web', 'pages-indispensables-site-vitrine' ),
			'tarifs'                         => array( 'prix-site-vitrine', 'prix-boutique-en-ligne', 'prix-refonte-site-internet', 'prix-maintenance-site-wordpress' ),
			'agence-web-paris'               => array( 'choisir-agence-web', 'referencement-local-google', 'fiche-google-business-profile' ),
			'agence-web-hauts-de-seine'      => array( 'referencement-local-google', 'fiche-google-business-profile', 'choisir-agence-web' ),
			'site-internet-artisan-btp'      => array( 'site-internet-artisan', 'referencement-local-google', 'fiche-google-business-profile' ),
			'site-internet-restaurant'       => array( 'fiche-google-business-profile', 'referencement-local-google', 'prix-site-vitrine' ),
			'site-internet-avocat'           => array( 'pages-indispensables-site-vitrine', 'referencement-local-google', 'prix-site-vitrine' ),
			'site-internet-professions-sante' => array( 'referencement-local-google', 'fiche-google-business-profile', 'pages-indispensables-site-vitrine' ),
			'site-internet-architecte'       => array( 'pages-indispensables-site-vitrine', 'prix-site-vitrine', 'choisir-agence-web' ),
			'site-internet-coach-consultant' => array( 'pages-indispensables-site-vitrine', 'referencement-local-google', 'prix-site-vitrine' ),
			'site-internet-collectivites'    => array( 'site-internet-mairie-obligations', 'prix-maintenance-site-wordpress', 'refonte-site-referencement' ),
			'agence'                         => array( 'choisir-agence-web', 'prix-site-vitrine', 'refonte-site-referencement' ),
		)
	);
}

/** Articles publiés à afficher pour une page (3 au plus). */
function aurea_guides_pour( $slug ) {
	$carte = aurea_guides_carte();
	if ( empty( $carte[ $slug ] ) ) {
		return array();
	}
	$ids = array();
	foreach ( $carte[ $slug ] as $s ) {
		$p = get_page_by_path( $s, OBJECT, 'post' );
		if ( $p && 'publish' === $p->post_status ) {
			$ids[] = $p->ID;
		}
		if ( count( $ids ) >= 3 ) {
			break;
		}
	}
	if ( count( $ids ) < 3 ) {
		$ids = array_merge(
			$ids,
			get_posts(
				array(
					'post_type'        => 'post',
					'posts_per_page'   => 3 - count( $ids ),
					'post__not_in'     => $ids ? $ids : array( 0 ),
					'fields'           => 'ids',
					'suppress_filters' => true,
					'lang'             => '',
				)
			)
		);
	}
	return $ids;
}

/** HTML de la section « Pour aller plus loin » pour la page courante (FR uniquement). */
function aurea_section_guides() {
	if ( ! is_page() || ( function_exists( 'aurea_langue' ) && 'fr' !== aurea_langue() ) ) {
		return '';
	}
	$ids = aurea_guides_pour( get_post_field( 'post_name', get_queried_object_id() ) );
	if ( ! $ids ) {
		return '';
	}
	$q = new WP_Query(
		array(
			'post_type'        => 'post',
			'post__in'         => $ids,
			'orderby'          => 'post__in',
			'posts_per_page'   => count( $ids ),
			'suppress_filters' => true,
			'lang'             => '',
		)
	);
	if ( ! $q->have_posts() ) {
		return '';
	}
	ob_start();
	?>
<section class="sec guides" aria-labelledby="guides-titre">
	<div class="wrap">
		<div class="sec-hd"><div><span class="eyebrow rv">Guides et conseils</span><h2 id="guides-titre" class="split-words">Pour aller <span class="gold">plus loin</span>.</h2></div><p class="lead rv">Nos guides pour préparer votre projet : prix, contenus, référencement et choix des outils.</p></div>
		<div class="arts">
			<?php
			while ( $q->have_posts() ) {
				$q->the_post();
				get_template_part( 'parts/carte-article' );
			}
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/** Insère la section avant l'appel final du contenu de la page (ou à la fin à défaut). */
function aurea_inserer_guides( $html ) {
	$guides = aurea_section_guides();
	if ( '' === $guides ) {
		return $html;
	}
	$pos = strrpos( $html, '<section class="sec final' );
	return false === $pos ? $html . $guides : substr( $html, 0, $pos ) . $guides . "\n" . substr( $html, $pos );
}

<?php
/**
 * Blocs alimentés par Aurea Studio, insérés dans les pages sous forme de codes courts.
 *
 * Ils reproduisent le HTML de la maquette avec les données de l'extension : ajouter, masquer ou
 * réordonner une réalisation, un logo ou un avis dans Aurea Studio met à jour toutes les pages.
 *
 * [aurea_mur]                         mur de captures du haut de l'accueil (réalisations « Mur du hero »)
 * [aurea_portfolio]                   portfolio défilant de l'accueil (réalisations « À la une »)
 * [aurea_projets …]                   grille de projets : cles="a,b,c" | type="site-vitrine" | secteur="sante",
 *                                     nombre, meta="secteur|type", plus="6" (bouton « Afficher les… »),
 *                                     filtre="1" (page Réalisations), classe="gal--2"
 * [aurea_logos_bandeau]               bandeau « Ils nous ont confié leur image en ligne »
 * [aurea_avis_cartes nombre="3"]      cartes d'avis Google
 * [aurea_avis_note]                   note Google et nombre d'avis
 * [aurea_contact_jeton]               champs cachés du formulaire de devis (sécurité, anti spam)
 *
 * Sans Aurea Studio, ces codes n'affichent rien (le reste de la page reste intact).
 */

defined( 'ABSPATH' ) || exit;

function aurea_studio_actif() {
	return function_exists( 'aurea_realisations' );
}

/** Image d'une réalisation (taille large, dimensions réelles pour éviter les décalages). */
function aurea_img_real( $r, $alt = '', $lazy = true, $taille = 'large' ) {
	if ( empty( $r['image'] ) ) {
		return '';
	}
	$src = wp_get_attachment_image_src( $r['image'], $taille );
	if ( ! $src ) {
		return '';
	}
	$srcset = wp_get_attachment_image_srcset( $r['image'], $taille );
	return sprintf(
		'<img src="%s"%s sizes="(max-width: 680px) 92vw, 640px" alt="%s" width="%d" height="%d"%s decoding="async">',
		esc_url( $src[0] ),
		$srcset ? ' srcset="' . esc_attr( $srcset ) . '"' : '',
		esc_attr( $alt ),
		(int) $src[1],
		(int) $src[2],
		$lazy ? ' loading="lazy"' : ''
	);
}

/** Barre de navigateur avec le domaine du site. */
function aurea_barre( $domaine, $balise = 'span' ) {
	return '<' . $balise . ' class="browser-bar"><i></i><i></i><i></i><span>' . esc_html( $domaine ) . '</span></' . $balise . '>';
}

/** Clé d'une réalisation (nom de la capture livrée avec Aurea Studio, sinon son slug). */
function aurea_cle_real( $r ) {
	$cle = (string) get_post_meta( $r['id'], '_real_cle', true );
	if ( ! $cle && $r['image'] ) {
		$cle = (string) get_post_meta( $r['image'], '_aurea_real_source', true );
	}
	return $cle ?: get_post_field( 'post_name', $r['id'] );
}

/** Une réalisation a-t-elle une étude de cas rédigée (sa fiche mérite alors le lien) ? */
function aurea_a_etude( $r ) {
	return '' !== trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $r['id'] ) ) );
}

/** Carte d'un projet, identique à la maquette. */
function aurea_carte_projet( $r, $meta = 'type', $classes = 'proj rv', $delai = 0 ) {
	$etude  = aurea_a_etude( $r );
	$lien   = $etude || ! $r['url'] ? $r['fiche'] : $r['url'];
	$cible  = $etude || ! $r['url'] ? '' : ' target="_blank" rel="noopener"';
	$action = $etude ? 'Voir l’étude de cas' : 'Voir le site';
	$texte  = 'secteur' === $meta ? $r['secteur']['nom'] : trim( $r['type']['nom'] . ( $r['lieu'] ? ' · ' . $r['lieu'] : '' ) );
	$style  = $delai ? ' style="--d:' . esc_attr( $delai ) . 's"' : '';
	return sprintf(
		'<article class="%s" data-type="%s" data-secteur="%s"%s><a class="browser proj-img" href="%s"%s>%s%s<span class="visit">%s <svg width="16" height="16"><use href="#i-out"/></svg></span></a><div class="proj-meta"><h3>%s</h3><span>%s</span></div><p>%s</p></article>',
		esc_attr( $classes ),
		esc_attr( $r['type']['nom'] ),
		esc_attr( $r['secteur']['nom'] ),
		$style,
		esc_url( $lien ),
		$cible,
		aurea_barre( $r['domaine'] ),
		aurea_img_real( $r, 'Site ' . $r['titre'] . ' réalisé par Aurea Media' ),
		esc_html( $action ),
		esc_html( $r['titre'] ),
		esc_html( $texte ),
		esc_html( $r['accroche'] )
	);
}

/* ------------------------------------------------------------------ */
/* Mur de captures (hero de l'accueil)                                 */
/* ------------------------------------------------------------------ */

add_shortcode( 'aurea_mur', function () {
	if ( ! aurea_studio_actif() ) {
		return '';
	}
	$reals = array_values( array_filter( aurea_realisations( array( 'mur' => true ) ), function ( $r ) {
		return $r['image'];
	} ) );
	if ( ! $reals ) {
		return '';
	}
	// Quatre colonnes, remplies à tour de rôle ; chaque colonne est répétée pour défiler sans fin.
	$colonnes = array( array(), array(), array(), array() );
	$n        = max( 12, count( $reals ) );
	for ( $i = 0; $i < $n; $i++ ) {
		$colonnes[ $i % 4 ][] = $reals[ $i % count( $reals ) ];
	}
	$html = '';
	foreach ( $colonnes as $c => $col ) {
		$tuiles = '';
		foreach ( $col as $r ) {
			$tuiles .= '<div class="tile"><span class="tile-bar"><i></i><i></i><i></i><b>' . esc_html( $r['domaine'] ) . '</b></span>' . aurea_img_real( $r, '', $c > 1, 'medium_large' ) . '</div>';
		}
		$html .= '<div class="wall-col">' . $tuiles . $tuiles . '</div>';
	}
	return $html;
} );

/* ------------------------------------------------------------------ */
/* Portfolio défilant (accueil)                                        */
/* ------------------------------------------------------------------ */

add_shortcode( 'aurea_portfolio', function () {
	if ( ! aurea_studio_actif() ) {
		return '';
	}
	$html = '';
	foreach ( aurea_realisations( array( 'une' => true ) ) as $r ) {
		$r['type']['nom'] = 'Site e-commerce' === $r['type']['nom'] ? 'E-commerce' : $r['type']['nom'];
		$html            .= aurea_carte_projet( $r, 'type', 'proj' );
	}
	$total = count( aurea_realisations() );
	$html .= '<div class="work-end"><h3>' . esc_html( $total > 10 ? 'Et ' . ( $total - count( aurea_realisations( array( 'une' => true ) ) ) ) . ' autres projets à découvrir.' : 'Découvrez tous nos projets.' ) . '</h3><a class="btn" href="' . esc_url( aurea_url( 'realisations' ) ) . '">Toutes les réalisations <svg><use href="#i-arrow"/></svg></a></div>';
	return $html;
} );

/* ------------------------------------------------------------------ */
/* Grille de projets                                                   */
/* ------------------------------------------------------------------ */

add_shortcode( 'aurea_projets', function ( $atts ) {
	if ( ! aurea_studio_actif() ) {
		return '';
	}
	$a = shortcode_atts( array( 'cles' => '', 'type' => '', 'secteur' => '', 'nombre' => -1, 'meta' => 'type', 'plus' => 0, 'filtre' => '', 'classe' => '', 'fiche' => '' ), $atts );
	if ( $a['cles'] ) {
		$toutes = array();
		foreach ( aurea_realisations() as $r ) {
			$toutes[ aurea_cle_real( $r ) ] = $r;
		}
		$reals = array();
		foreach ( array_map( 'trim', explode( ',', $a['cles'] ) ) as $cle ) {
			if ( isset( $toutes[ $cle ] ) ) {
				$reals[] = $toutes[ $cle ];
			}
		}
	} else {
		$reals = aurea_realisations( array( 'type' => $a['type'], 'secteur' => $a['secteur'], 'nombre' => (int) $a['nombre'] ) );
	}
	if ( ! $reals ) {
		return '';
	}
	$plus    = (int) $a['plus'];
	$classes = trim( 'gal ' . ( $a['filtre'] ? 'gal--filtre ' : '' ) . ( $plus && count( $reals ) > $plus ? 'gal--plus ' : '' ) . $a['classe'] );
	$html    = '<div class="' . esc_attr( $classes ) . '">';
	foreach ( $reals as $i => $r ) {
		$html .= aurea_carte_projet( $r, $a['meta'], 'proj rv' . ( $plus && $i >= $plus ? ' is-plus' : '' ) );
	}
	$html .= '</div>';
	if ( $plus && count( $reals ) > $plus ) {
		$quoi  = 'site-vitrine' === $a['type'] ? 'sites vitrines' : ( 'site-ecommerce' === $a['type'] ? 'boutiques en ligne' : 'projets' );
		$html .= '<div class="plus-wrap"><button type="button" class="btn btn--ghost" data-plus>Afficher les ' . count( $reals ) . ' ' . esc_html( $quoi ) . ' <svg><use href="#i-arrow"/></svg></button></div>';
	}
	return $html;
} );

/* ------------------------------------------------------------------ */
/* Logos clients                                                       */
/* ------------------------------------------------------------------ */

add_shortcode( 'aurea_logos_bandeau', function () {
	if ( ! function_exists( 'aurea_logos' ) ) {
		return '';
	}
	$logos = array_filter( aurea_logos( true ), function ( $l ) {
		return $l['url'];
	} );
	if ( ! $logos ) {
		return '';
	}
	$rang = '';
	foreach ( $logos as $l ) {
		// Silhouette crème (masque) par défaut, ou logo dans ses couleurs d'origine (option d'Aurea Studio)
		$style  = '--src:url(' . esc_url( $l['url'] ) . ')';
		$classe = 'logo-it' . ( $l['couleur'] ? ' is-couleur' : '' );
		$rang  .= $l['lien']
			? '<a class="' . $classe . '" href="' . esc_url( $l['lien'] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $l['nom'] ) . '" style="' . esc_attr( $style ) . '"></a>'
			: '<span class="' . $classe . '" role="img" aria-label="' . esc_attr( $l['nom'] ) . '" style="' . esc_attr( $style ) . '"></span>';
	}
	// Le bandeau défile en boucle : la deuxième série est masquée aux lecteurs d'écran.
	$copie = preg_replace( array( '/ role="img" aria-label="[^"]*"/', '/ aria-label="[^"]*"/', '/<a /' ), array( '', '', '<a tabindex="-1" ' ), $rang );
	return '<section class="logos" aria-label="Ils nous font confiance"><p>Ils nous ont confié leur image en ligne</p><div class="logos-row">' . $rang . '<span class="logos-copie" aria-hidden="true">' . $copie . '</span></div></section>';
} );

/* ------------------------------------------------------------------ */
/* Avis Google                                                         */
/* ------------------------------------------------------------------ */

add_shortcode( 'aurea_avis_cartes', function ( $atts ) {
	if ( ! function_exists( 'aurea_avis' ) ) {
		return '';
	}
	$a    = shortcode_atts( array( 'nombre' => null ), $atts );
	$avis = aurea_avis( null === $a['nombre'] ? null : (int) $a['nombre'] );
	if ( ! $avis ) {
		// En attendant les avis (API Google ou avis ajoutés dans Aurea Studio) : un renvoi propre vers la fiche Google
		$fiche = function_exists( 'aurea_coordonnees' ) ? ( aurea_coordonnees()['google'] ?? '' ) : '';
		$r     = function_exists( 'aurea_avis_resume' ) ? aurea_avis_resume() : array( 'note_txt' => '4,9', 'total' => 39 );
		return $fiche ? '<div class="reviews-vide rv"><svg class="g" aria-hidden="true"><use href="#i-g"/></svg><div><b>' . esc_html( $r['note_txt'] ) . ' sur 5</b><span class="stars" aria-hidden="true">★★★★★</span><p>' . esc_html( sprintf( 'Note moyenne sur %d avis publiés sur notre fiche Google.', $r['total'] ) ) . '</p></div><a class="btn btn--ghost" href="' . esc_url( $fiche ) . '" target="_blank" rel="noopener">Lire les avis sur Google <svg><use href="#i-out"/></svg></a></div>' : '';
	}
	$html = '<div class="reviews">';
	foreach ( $avis as $i => $v ) {
		$note  = max( 1, min( 5, (int) $v['note'] ) );
		$html .= sprintf(
			'<blockquote class="review rv"%s><span class="review-guillemet" aria-hidden="true">“</span><div class="top"><span class="stars" role="img" aria-label="%s">%s</span><svg class="g" aria-hidden="true"><use href="#i-g"/></svg></div><p>%s</p><footer><i>%s</i><div>%s<span>%s</span></div></footer></blockquote>',
			$i % 3 ? ' style="--d:.' . ( $i % 3 ) . 's"' : '',
			esc_attr( $note . ' sur 5' ),
			str_repeat( '★', $note ),
			esc_html( $v['texte'] ),
			esc_html( mb_strtoupper( mb_substr( $v['nom'], 0, 1 ) ) ),
			esc_html( $v['nom'] ),
			esc_html( $v['quand'] )
		);
	}
	return $html . '</div>';
} );

add_shortcode( 'aurea_avis_note', function () {
	if ( ! function_exists( 'aurea_avis_resume' ) ) {
		return '';
	}
	$r     = aurea_avis_resume();
	$total = sprintf( '%d avis vérifiés', $r['total'] );
	$total = $r['lien'] ? '<a href="' . esc_url( $r['lien'] ) . '" target="_blank" rel="noopener">' . esc_html( $total ) . '</a>' : esc_html( $total );
	return '<div class="g-score rv"><svg width="34" height="34" aria-hidden="true"><use href="#i-g"/></svg><b>' . esc_html( $r['note_txt'] ) . '</b><div><span class="stars" aria-hidden="true">★★★★★</span><p class="lead" style="font-size:.9rem">' . $total . '</p></div></div>';
} );

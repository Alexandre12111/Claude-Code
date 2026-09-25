<?php
/**
 * Allergènes et régimes, pour les produits de la boutique et les plats du Tea Room.
 *
 * - Dans chaque fiche : les 14 allergènes à déclarer (droit suisse, comme dans l'UE),
 *   l'alcool, les régimes (végétarien, végane, sans gluten, sans lactose) et les traces possibles.
 * - Sur le site : pictogrammes sur la page du produit, dans la fiche rapide et dans la carte
 *   du salon, et filtres « Sans gluten », « Sans fruits à coque », « Végane »… sur la boutique
 *   et la carte. Un produit dont les allergènes ne sont pas renseignés est masqué dès qu'un
 *   filtre est actif : on ne promet jamais « sans » par défaut.
 * - Une remarque générale (Réglages maison) rappelle que l'équipe renseigne volontiers.
 */

defined( 'ABSPATH' ) || exit;

/** clé => [ nom, nom court (filtres « Sans … »), tracé du pictogramme (24 × 24) ]. */
function schiesser_allergenes_liste() {
	return array(
		'gluten'      => array( 'Gluten', 'gluten', 'M12 21V8 M12 8c-2-1-3-3-3-5 2 1 3 3 3 5z M12 8c2-1 3-3 3-5-2 1-3 3-3 5z M12 13c-2-1-3.5-3-3.5-5 2 .8 3.5 3 3.5 5z M12 13c2-1 3.5-3 3.5-5-2 .8-3.5 3-3.5 5z M12 18c-2-1-3.5-3-3.5-5 2 .8 3.5 3 3.5 5z M12 18c2-1 3.5-3 3.5-5-2 .8-3.5 3-3.5 5z' ),
		'oeufs'       => array( 'Œufs', 'œufs', 'M12 3c-3.5 0-6 5.5-6 10a6 6 0 0 0 12 0c0-4.5-2.5-10-6-10z' ),
		'lait'        => array( 'Lait (lactose)', 'lait', 'M9 3h6 M9.5 3v3L7 10v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V10l-2.5-4V3 M7 13h10' ),
		'fruits-coque' => array( 'Fruits à coque (amandes, noisettes, noix…)', 'fruits à coque', 'M12 8c-4 0-6 3-6 6.5S9 21 12 21s6-3 6-6.5S16 8 12 8z M6.5 10.5C7 6 9 4 12 4s5 2 5.5 6.5 M12 4V2.5' ),
		'arachides'   => array( 'Arachides', 'arachides', 'M9 3.5a4 4 0 0 1 5.5 5.5 4 4 0 0 0 0 5.5A4 4 0 1 1 9 20a4 4 0 0 0 0-5.5A4 4 0 0 1 9 3.5z' ),
		'soja'        => array( 'Soja', 'soja', 'M5 19C5 11 11 5 19 5c0 8-6 14-14 14z M9 12.5a1.5 1.5 0 1 0 0 .1 M12 9.5a1.5 1.5 0 1 0 0 .1 M15 6.8a1.2 1.2 0 1 0 0 .1' ),
		'sesame'      => array( 'Sésame', 'sésame', 'M8 7c1 0 1.5 1.5 1.5 2.5S9 11 8 11s-1.5-.5-1.5-1.5S7 7 8 7z M15 5c1 0 1.5 1.5 1.5 2.5S16 9 15 9s-1.5-.5-1.5-1.5S14 5 15 5z M11 13c1 0 1.5 1.5 1.5 2.5S12 17 11 17s-1.5-.5-1.5-1.5S10 13 11 13z M17 13c1 0 1.5 1.5 1.5 2.5S18 17 17 17s-1.5-.5-1.5-1.5S16 13 17 13z' ),
		'sulfites'    => array( 'Sulfites', 'sulfites', 'M8 3h8l-1 7a3 3 0 0 1-6 0z M12 13v7 M8.5 21h7' ),
		'celeri'      => array( 'Céleri', 'céleri', 'M9 21c0-6 1-11 3-17 M15 21c0-6-1-11-3-17 M12 4c-3 0-5 2-5 4 2 0 4-1 5-4zm0 0c3 0 5 2 5 4-2 0-4-1-5-4z' ),
		'moutarde'    => array( 'Moutarde', 'moutarde', 'M9 3h6v3H9z M8 6h8l1 3v10a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V9z M9.5 13h5' ),
		'poisson'     => array( 'Poisson', 'poisson', 'M3 12c3-4 7-5 10-5 3 0 5 2 7 5-2 3-4 5-7 5-3 0-7-1-10-5z M20 12l2-3v6z M9 11.5v.1' ),
		'crustaces'   => array( 'Crustacés', 'crustacés', 'M6 14c0-4 3-7 7-7 3 0 5 2 5 5 0 4-3 6-6 6H9 M9 18l-2 2 M11 18v3 M18 9l2-3 M16 7l1-3' ),
		'mollusques'  => array( 'Mollusques', 'mollusques', 'M12 20L4 9c3-4 13-4 16 0z M12 20L9 7 M12 20V6 M12 20l3-13' ),
		'lupin'       => array( 'Lupin', 'lupin', 'M12 21V10 M12 10c-1.5 0-2.5-1-2.5-2.5S10.5 5 12 5s2.5 1 2.5 2.5S13.5 10 12 10z M12 5c-1 0-1.5-.8-1.5-1.5S11 2 12 2s1.5.8 1.5 1.5S13 5 12 5z M8 15c2 0 4 1 4 3 M16 13c-2 0-4 1-4 3' ),
		'alcool'      => array( 'Alcool (kirsch, liqueur…)', 'alcool', 'M7 3h10c0 5-2 8-5 8s-5-3-5-8z M12 11v8 M8 21h8 M7.5 6h9' ),
	);
}

/** clé => [ nom, tracé du pictogramme ]. */
function schiesser_regimes_liste() {
	return array(
		'vegetarien'   => array( 'Végétarien', 'M5 19C5 11 11 5 19 5c0 8-6 14-14 14z M5 19l8-8' ),
		'vegane'       => array( 'Végane', 'M5 19C5 11 11 5 19 5c0 8-6 14-14 14z M5 19l8-8 M9 9.5l3 6 3-6' ),
		'sans-gluten'  => array( 'Sans gluten', 'M12 21V9 M12 9c-2-1-3-3-3-5 2 1 3 3 3 5z M12 9c2-1 3-3 3-5-2 1-3 3-3 5z M12 15c-2-1-3.5-3-3.5-5 2 .8 3.5 3 3.5 5z M12 15c2-1 3.5-3 3.5-5-2 .8-3.5 3-3.5 5z M4 4l16 16' ),
		'sans-lactose' => array( 'Sans lactose', 'M9 3h6 M9.5 3v3L7 10v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V10l-2.5-4V3 M4 4l16 16' ),
	);
}

function schiesser_picto_allergene( $trace, $titre ) {
	$sens = '' !== $titre ? 'role="img" aria-label="' . esc_attr( $titre ) . '"><title>' . esc_html( $titre ) . '</title>' : 'aria-hidden="true" focusable="false">'; // sans titre : décoratif (le nom est écrit à côté)
	return '<svg class="al-svg" viewBox="0 0 24 24" width="20" height="20" ' . $sens . '<path d="' . esc_attr( $trace ) . '" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Données « allergènes » d'un produit ou d'un plat.
 *
 * @param int    $post_id
 * @param string $prefixe '_s_' (boutique) ou '_t_' (Tea Room).
 * @return array{allergenes:string[], regimes:string[], traces:string, renseigne:bool}
 */
function schiesser_allergenes_de( $post_id, $prefixe ) {
	$al = array_values( array_intersect( (array) get_post_meta( $post_id, $prefixe . 'allergenes', true ), array_keys( schiesser_allergenes_liste() ) ) );
	$re = array_values( array_intersect( (array) get_post_meta( $post_id, $prefixe . 'regimes', true ), array_keys( schiesser_regimes_liste() ) ) );
	return array(
		'allergenes' => $al,
		'regimes'    => $re,
		'traces'     => (string) get_post_meta( $post_id, $prefixe . 'traces', true ),
		'renseigne'  => $al || (bool) get_post_meta( $post_id, $prefixe . 'sans_allergene', true ),
	);
}

/** Attributs data-… lus par les filtres (boutique.js, maquette.js). */
function schiesser_allergenes_attrs( $d ) {
	if ( empty( $d['renseigne'] ) && empty( $d['regimes'] ) ) {
		return '';
	}
	return ' data-al="' . esc_attr( implode( ' ', $d['allergenes'] ) ) . '" data-re="' . esc_attr( implode( ' ', $d['regimes'] ) ) . '"' . ( $d['renseigne'] ? ' data-al-ok="1"' : '' );
}

/** Pictogrammes (allergènes puis régimes). $detail : avec le nom à côté (page du produit). */
function schiesser_allergenes_pictos( $d, $detail = false ) {
	$liste = schiesser_allergenes_liste();
	$reg   = schiesser_regimes_liste();
	$html  = '';
	foreach ( $d['allergenes'] as $k ) {
		$html .= '<li class="al al--allergene">' . schiesser_picto_allergene( $liste[ $k ][2], $detail ? '' : $liste[ $k ][0] ) . ( $detail ? '<span>' . esc_html( $liste[ $k ][0] ) . '</span>' : '' ) . '</li>';
	}
	foreach ( $d['regimes'] as $k ) {
		$html .= '<li class="al al--regime">' . schiesser_picto_allergene( $reg[ $k ][1], $detail ? '' : $reg[ $k ][0] ) . ( $detail ? '<span>' . esc_html( $reg[ $k ][0] ) . '</span>' : '' ) . '</li>';
	}
	return $html ? '<ul class="al-liste' . ( $detail ? ' al-liste--detail' : '' ) . '">' . $html . '</ul>' : '';
}

/** Texte « Gluten, œufs, lait » (fiche rapide, données structurées). */
function schiesser_allergenes_texte( $d ) {
	$liste = schiesser_allergenes_liste();
	$noms  = array_map( function ( $k ) use ( $liste ) {
		return $liste[ $k ][0];
	}, $d['allergenes'] );
	if ( ! $noms && ! empty( $d['renseigne'] ) ) {
		return 'Aucun des 14 allergènes à déclarer';
	}
	return implode( ', ', $noms );
}

/**
 * Filtres « Sans … » et régimes, d'après ce que contiennent vraiment les produits affichés.
 *
 * @param array[] $items Données allergènes des produits (schiesser_allergenes_de).
 */
function schiesser_allergenes_filtres( $items ) {
	$renseignes = array_filter( $items, function ( $d ) {
		return ! empty( $d['renseigne'] ) || ! empty( $d['regimes'] );
	} );
	if ( ! $renseignes ) {
		return '';
	}
	$presents = array();
	$regimes  = array();
	foreach ( $renseignes as $d ) {
		$presents = array_merge( $presents, $d['allergenes'] );
		$regimes  = array_merge( $regimes, $d['regimes'] );
	}
	$ordre   = array( 'gluten', 'fruits-coque', 'arachides', 'lait', 'oeufs', 'soja', 'sesame', 'alcool', 'sulfites', 'celeri', 'moutarde', 'lupin', 'poisson', 'crustaces', 'mollusques' );
	$liste   = schiesser_allergenes_liste();
	$boutons = '';
	// « Sans gluten » vaut aussi pour les produits marqués « sans gluten » (le régime) : un seul bouton.
	if ( in_array( 'sans-gluten', $regimes, true ) ) {
		$presents[] = 'gluten';
	}
	$regimes = array_diff( $regimes, array( 'sans-gluten' ) );
	foreach ( $ordre as $k ) {
		if ( in_array( $k, $presents, true ) ) {
			$boutons .= '<button type="button" class="fchip fchip--al" data-sans="' . esc_attr( $k ) . '" aria-pressed="false">Sans ' . esc_html( $liste[ $k ][1] ) . '</button>';
		}
	}
	foreach ( schiesser_regimes_liste() as $k => $r ) {
		if ( in_array( $k, $regimes, true ) ) {
			$boutons .= '<button type="button" class="fchip fchip--al" data-regime="' . esc_attr( $k ) . '" aria-pressed="false">' . schiesser_picto_allergene( $r[1], '' ) . esc_html( $r[0] ) . '</button>';
		}
	}
	if ( '' === $boutons ) {
		return '';
	}
	return '<div class="al-filtres js-al-filtres"><div class="al-filtres-in" role="group" aria-label="Allergènes et régimes"><span class="al-filtres-k">Allergies</span>' . $boutons . '</div>'
		. '<p class="al-filtres-vide js-al-vide" hidden>Aucun produit ne correspond. Les produits dont les allergènes ne sont pas encore renseignés sont masqués pendant le filtrage.</p></div>';
}

/** Remarque générale sur les allergènes (Réglages maison), sous les listes. */
function schiesser_allergenes_note() {
	$n = trim( (string) schiesser_reglage( 'allergenes_note' ) );
	return '' !== $n ? '<p class="al-note">' . esc_html( $n ) . '</p>' : '';
}

/* ------------------------------------------------------------------ */
/* Saisie : boîte « Allergènes et régimes »                            */
/* ------------------------------------------------------------------ */

add_action( 'add_meta_boxes', function () {
	foreach ( array( SCHIESSER_PRODUIT => '_s_', SCHIESSER_TEAROOM => '_t_' ) as $type => $prefixe ) {
		add_meta_box( 'schiesser_allergenes', 'Allergènes et régimes', function ( $post ) use ( $prefixe ) {
			schiesser_boite_allergenes( $post, $prefixe );
		}, $type, 'normal', 'default' );
	}
} );

function schiesser_boite_allergenes( $post, $prefixe ) {
	$d = schiesser_allergenes_de( $post->ID, $prefixe );
	wp_nonce_field( 'schiesser_allergenes', 'schiesser_allergenes_nonce' );
	?>
	<p class="description" style="margin-top:0">Cochez ce que contient le produit. Ces informations s'affichent avec des pictogrammes et servent aux filtres « Sans gluten », « Végane »… du site. Vérifiez les avec la recette : un client allergique s'y fiera.</p>
	<fieldset class="s-al-grille">
		<legend class="screen-reader-text">Allergènes</legend>
		<?php foreach ( schiesser_allergenes_liste() as $k => $a ) : ?>
			<label class="s-al-case"><input type="checkbox" name="al[allergenes][]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $d['allergenes'], true ) ); ?>><?php echo schiesser_picto_allergene( $a[2], '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $a[0] ); ?></span></label>
		<?php endforeach; ?>
	</fieldset>
	<p><label><input type="checkbox" name="al[sans_allergene]" value="1" <?php checked( (bool) get_post_meta( $post->ID, $prefixe . 'sans_allergene', true ) ); ?>> <strong>Ne contient aucun de ces allergènes</strong></label>
		<br><span class="description">À cocher pour qu'un produit sans allergène apparaisse avec les filtres « Sans … ». Sans rien de coché, le produit est considéré comme « non renseigné ».</span></p>
	<fieldset class="s-al-grille s-al-grille--regimes">
		<legend><strong>Régimes</strong></legend>
		<?php foreach ( schiesser_regimes_liste() as $k => $r ) : ?>
			<label class="s-al-case"><input type="checkbox" name="al[regimes][]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, $d['regimes'], true ) ); ?>><?php echo schiesser_picto_allergene( $r[1], '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $r[0] ); ?></span></label>
		<?php endforeach; ?>
	</fieldset>
	<p><label for="al-traces"><strong>Traces possibles</strong> (facultatif)</label><br>
		<input type="text" id="al-traces" name="al[traces]" value="<?php echo esc_attr( $d['traces'] ); ?>" class="large-text" placeholder="Ex. Peut contenir des traces de fruits à coque et d'arachides."></p>
	<?php
}

foreach ( array( 'schiesser_produit' => '_s_', 'schiesser_tearoom' => '_t_' ) as $schiesser_type => $schiesser_prefixe ) {
	add_action( 'save_post_' . $schiesser_type, function ( $post_id ) use ( $schiesser_prefixe ) {
		if ( ! isset( $_POST['schiesser_allergenes_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['schiesser_allergenes_nonce'] ), 'schiesser_allergenes' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$e  = isset( $_POST['al'] ) ? (array) wp_unslash( $_POST['al'] ) : array();
		$al = array_values( array_intersect( array_map( 'sanitize_key', (array) ( $e['allergenes'] ?? array() ) ), array_keys( schiesser_allergenes_liste() ) ) );
		$re = array_values( array_intersect( array_map( 'sanitize_key', (array) ( $e['regimes'] ?? array() ) ), array_keys( schiesser_regimes_liste() ) ) );
		update_post_meta( $post_id, $schiesser_prefixe . 'allergenes', $al );
		update_post_meta( $post_id, $schiesser_prefixe . 'regimes', $re );
		update_post_meta( $post_id, $schiesser_prefixe . 'sans_allergene', ( ! $al && ! empty( $e['sans_allergene'] ) ) ? 1 : 0 );
		update_post_meta( $post_id, $schiesser_prefixe . 'traces', sanitize_text_field( $e['traces'] ?? '' ) );
	} );
}

add_action( 'admin_head', function () {
	$ecran = get_current_screen();
	if ( ! $ecran || ! in_array( $ecran->post_type, array( SCHIESSER_PRODUIT, SCHIESSER_TEAROOM ), true ) || 'post' !== $ecran->base ) {
		return;
	}
	echo '<style>.s-al-grille{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:6px 16px;margin:12px 0;border:0;padding:0}'
		. '.s-al-grille--regimes{margin-top:18px}.s-al-grille legend{margin-bottom:8px}'
		. '.s-al-case{display:flex;align-items:center;gap:8px;padding:6px 8px;border:1px solid #dcdcde;border-radius:4px;background:#fff;cursor:pointer}'
		. '.s-al-case:has(input:checked){border-color:#23503B;background:#f0f6f2}.s-al-case .al-svg{color:#23503B;flex-shrink:0}</style>';
} );

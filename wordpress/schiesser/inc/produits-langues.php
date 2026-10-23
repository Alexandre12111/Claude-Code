<?php
/**
 * Un produit dans les trois langues, sur la même page.
 *
 * Sous les détails d'un produit de la boutique ou d'un plat du Tea Room, l'encadré « Dans les autres
 * langues » montre ses autres versions (Polylang) et les modifie sans changer de page : nom, accroche,
 * description, badge, mention… et, depuis la version allemande, le libellé des formats et de l'unité
 * dans chaque langue (sinon traduits automatiquement). Tout s'enregistre avec le produit ; une version
 * qui manque encore est créée dès qu'on lui donne un nom.
 *
 * Les prix, photos, allergènes et l'ordre restent communs aux trois langues (inc/i18n.php).
 */

defined( 'ABSPATH' ) || exit;

/** Textes propres à chaque langue : clé => [ libellé, type de champ, métadonnée ] (« titre » : le nom). */
function schiesser_prodl_champs( $type ) {
	if ( SCHIESSER_TEAROOM === $type ) {
		return array(
			'titre'       => array( 'Nom', 'text', '' ),
			'description' => array( 'Description', 'textarea', '_t_description' ),
			'mention'     => array( 'Mention', 'text', '_t_mention' ),
		);
	}
	return array(
		'titre'       => array( 'Nom du produit', 'text', '' ),
		'accroche'    => array( 'Accroche', 'text', '_s_accroche' ),
		'description' => array( 'Description courte', 'textarea', '_s_description' ),
		'badge'       => array( 'Badge', 'text', '_s_badge' ),
		'accord'      => array( 'Accord', 'text', '_s_accord' ),
		'origine'     => array( 'Origine', 'text', '_s_origine' ),
	);
}

/** Langue d'un contenu (« de », « fr », « en ») ; sans langue : l'allemand. */
function schiesser_prodl_langue( $id ) {
	$l = function_exists( 'pll_get_post_language' ) ? substr( (string) pll_get_post_language( (int) $id ), 0, 2 ) : '';
	return '' !== $l ? $l : 'de';
}

/** Autres langues du site, dans l'ordre du sélecteur (DE · EN · FR). */
function schiesser_prodl_autres( $courante ) {
	$actives = schiesser_polylang() ? schiesser_langues_actives() : array();
	return array_values( array_filter( array( 'de', 'en', 'fr' ), function ( $l ) use ( $actives, $courante ) {
		return $l !== $courante && isset( $actives[ $l ] );
	} ) );
}

add_action( 'add_meta_boxes', function ( $type ) {
	if ( ! in_array( $type, array( SCHIESSER_PRODUIT, SCHIESSER_TEAROOM ), true ) || ! schiesser_polylang() || ! function_exists( 'pll_get_post' ) ) {
		return;
	}
	add_meta_box( 'schiesser_produit_langues', 'Dans les autres langues', 'schiesser_prodl_boite', $type, 'apres_titre', 'default' );
} );

function schiesser_prodl_boite( $post ) {
	$courante = schiesser_prodl_langue( $post->ID );
	$autres   = schiesser_prodl_autres( $courante );
	$noms     = schiesser_langues_site();
	$versions = array( 'de' => 'allemande', 'fr' => 'française', 'en' => 'anglaise' );
	if ( ! $autres ) {
		echo '<p class="description">Une seule langue sur le site pour le moment.</p>';
		return;
	}
	wp_nonce_field( 'schiesser_prodl', 'schiesser_prodl_nonce' );
	// Formats et unité : écrits une fois en allemand, leur libellé se règle ici depuis la version allemande.
	$formats = array();
	if ( SCHIESSER_PRODUIT === $post->post_type && 'de' === $courante ) {
		$unite = trim( (string) get_post_meta( $post->ID, '_s_unite', true ) );
		if ( '' !== $unite ) {
			$formats[] = array( $unite, 'Unité', '' );
		}
		foreach ( (array) get_post_meta( $post->ID, '_s_fiche', true ) as $ligne ) {
			$de = trim( (string) ( $ligne[0] ?? '' ) );
			if ( '' !== $de ) {
				$formats[] = array( $de, 'Format', trim( (string) ( $ligne[1] ?? '' ) ) );
			}
		}
	}
	?>
	<p class="description s-pl-intro">Le même <?php echo SCHIESSER_TEAROOM === $post->post_type ? 'plat' : 'produit'; ?> dans les autres langues du site : modifiez ses textes ici, ils s’enregistrent avec cette page. Prix, photo et allergènes sont communs aux trois langues.</p>
	<div class="s-pl-grille">
		<?php
		foreach ( $autres as $l ) :
			$id       = (int) pll_get_post( $post->ID, schiesser_slug_polylang( $l ) );
			$nom      = $noms[ $l ][0] ?? strtoupper( $l );
			$propres  = $id ? (array) get_post_meta( $id, '_s_libelles', true ) : array();
			$champ    = function ( $cle ) use ( $l ) {
				return 'prodl[' . $l . '][' . $cle . ']';
			};
			?>
			<fieldset class="s-pl-langue" lang="<?php echo esc_attr( $l ); ?>">
				<legend><span class="s-pl-code"><?php echo esc_html( strtoupper( $l ) ); ?></span> <?php echo esc_html( $nom ); ?>
					<?php if ( $id ) : ?><a class="s-pl-ouvrir" href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>">Ouvrir cette version</a><?php endif; ?>
				</legend>
				<?php if ( ! $id ) : ?>
					<p class="s-pl-vide"><?php echo esc_html( 'Pas encore de version ' . ( $versions[ $l ] ?? $nom ) . ( 'de' === $courante ? ' : donnez-lui un nom, elle sera créée à l’enregistrement.' : ' : créez-la depuis la version allemande.' ) ); ?></p>
				<?php endif; ?>
				<?php if ( $id || 'de' === $courante ) : ?>
					<?php
					foreach ( schiesser_prodl_champs( $post->post_type ) as $cle => $c ) :
						$valeur = $id ? ( '' === $c[2] ? html_entity_decode( get_post_field( 'post_title', $id ), ENT_QUOTES, 'UTF-8' ) : (string) get_post_meta( $id, $c[2], true ) ) : '';
						$source = '' === $c[2] ? html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ) : (string) get_post_meta( $post->ID, $c[2], true );
						// Texte resté en allemand (les noms et badges peuvent l'être volontairement : Espresso, Signature…).
						$encore = $id && ! in_array( $cle, array( 'titre', 'badge' ), true ) && '' !== $valeur && $valeur === $source && 'de' !== $l && 'de' === $courante;
						$cache  = in_array( $cle, array( 'accord', 'origine' ), true ) && '' === $valeur && '' === $source;
						if ( $cache ) {
							continue; // champs peu utilisés : seulement s'ils sont remplis quelque part
						}
						?>
						<p class="s-pl-champ">
							<label for="<?php echo esc_attr( 'prodl-' . $l . '-' . $cle ); ?>"><?php echo esc_html( $c[0] ); ?>
								<?php if ( $encore ) : ?><span class="s-pl-alerte">identique à l’allemand</span><?php endif; ?>
							</label>
							<?php if ( 'textarea' === $c[1] ) : ?>
								<textarea id="<?php echo esc_attr( 'prodl-' . $l . '-' . $cle ); ?>" name="<?php echo esc_attr( $champ( $cle ) ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $valeur ); ?></textarea>
							<?php else : ?>
								<input id="<?php echo esc_attr( 'prodl-' . $l . '-' . $cle ); ?>" type="text" name="<?php echo esc_attr( $champ( $cle ) ); ?>" value="<?php echo esc_attr( $valeur ); ?>" class="large-text">
							<?php endif; ?>
						</p>
					<?php endforeach; ?>
					<?php if ( $formats ) : ?>
						<div class="s-pl-formats">
							<p class="s-pl-sous-titre">Formats et unité <span>(vide : traduction automatique, en gris)</span></p>
							<?php foreach ( $formats as $i => $f ) : ?>
								<p class="s-pl-format">
									<label for="<?php echo esc_attr( 'prodl-' . $l . '-format-' . $i ); ?>"><?php echo esc_html( $f[1] ); ?> <code><?php echo esc_html( $f[0] ); ?></code><?php echo '' !== $f[2] ? ' <span class="s-pl-prix">' . esc_html( $f[2] ) . '</span>' : ''; ?></label>
									<input type="hidden" name="<?php echo esc_attr( $champ( 'sources' ) . '[' . $i . ']' ); ?>" value="<?php echo esc_attr( $f[0] ); ?>">
									<input id="<?php echo esc_attr( 'prodl-' . $l . '-format-' . $i ); ?>" type="text" name="<?php echo esc_attr( $champ( 'libelles' ) . '[' . $i . ']' ); ?>" value="<?php echo esc_attr( (string) ( $propres[ $f[0] ] ?? '' ) ); ?>" placeholder="<?php echo esc_attr( schiesser_t_libelle( $f[0], $l ) ); ?>" class="large-text">
								</p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</fieldset>
		<?php endforeach; ?>
	</div>
	<?php if ( SCHIESSER_PRODUIT === $post->post_type && 'de' !== $courante ) : ?>
		<p class="description">Formats, prix et unité : communs aux trois langues, écrits en allemand. Leur libellé dans chaque langue se règle depuis la version allemande.</p>
	<?php endif; ?>
	<?php
}

/** Enregistre les autres langues avec le produit (après la recopie des prix et photos : priorité 60). */
function schiesser_prodl_enregistrer( $post_id ) {
	static $fait = false;
	if ( $fait || ! isset( $_POST['schiesser_prodl_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['schiesser_prodl_nonce'] ), 'schiesser_prodl' ) ) {
		return;
	}
	if ( (int) ( $_POST['post_ID'] ?? 0 ) !== (int) $post_id || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return; // seulement le produit du formulaire, pas ses versions enregistrées pendant la même requête
	}
	$fait      = true;
	$type      = get_post_type( $post_id );
	$taxonomie = SCHIESSER_TEAROOM === $type ? SCHIESSER_RUBRIQUE : SCHIESSER_CATEGORIE;
	$courante  = schiesser_prodl_langue( $post_id );
	$entree    = isset( $_POST['prodl'] ) ? (array) wp_unslash( $_POST['prodl'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	foreach ( schiesser_prodl_autres( $courante ) as $l ) {
		if ( empty( $entree[ $l ] ) || ! is_array( $entree[ $l ] ) ) {
			continue;
		}
		$v     = $entree[ $l ];
		$titre = sanitize_text_field( (string) ( $v['titre'] ?? '' ) );
		$metas = array();
		foreach ( schiesser_prodl_champs( $type ) as $cle => $c ) {
			if ( '' === $c[2] || ! isset( $v[ $cle ] ) ) {
				continue;
			}
			$x = (string) $v[ $cle ];
			if ( 'textarea' !== $c[1] ) {
				$x = sanitize_text_field( $x );
			} else {
				$x = SCHIESSER_TEAROOM === $type ? trim( wp_kses( $x, schiesser_tearoom_kses() ) ) : sanitize_textarea_field( $x );
			}
			$metas[ $c[2] ] = $x;
		}
		$id = (int) pll_get_post( $post_id, schiesser_slug_polylang( $l ) );
		if ( ! $id ) {
			// Version manquante : créée depuis l'allemand dès qu'elle a un nom (prix, photo, allergènes repris).
			if ( 'de' !== $courante || '' === $titre || ! function_exists( 'schiesser_traduire_contenu' ) ) {
				continue;
			}
			$id = schiesser_traduire_contenu( $post_id, $l, array( 'post_title' => $titre ), $metas );
			if ( $id ) {
				wp_set_object_terms( $id, schiesser_termes_traduits( $post_id, $taxonomie, $l ), $taxonomie );
			}
		} else {
			if ( '' !== $titre && html_entity_decode( get_post_field( 'post_title', $id ), ENT_QUOTES, 'UTF-8' ) !== $titre ) {
				wp_update_post( wp_slash( array( 'ID' => $id, 'post_title' => $titre ) ) );
			}
			foreach ( $metas as $cle => $x ) {
				update_post_meta( $id, $cle, $x );
			}
		}
		// Libellés des formats et de l'unité dans cette langue (seulement ceux qui diffèrent de la traduction automatique).
		if ( $id && 'de' === $courante && SCHIESSER_PRODUIT === $type && isset( $v['sources'] ) ) {
			$carte = array();
			foreach ( (array) $v['sources'] as $i => $source ) {
				$source = sanitize_text_field( (string) $source );
				$propre = sanitize_text_field( (string) ( $v['libelles'][ $i ] ?? '' ) );
				if ( '' !== $source && '' !== $propre && schiesser_t_libelle( $source, $l ) !== $propre ) {
					$carte[ $source ] = $propre;
				}
			}
			if ( $carte ) {
				update_post_meta( $id, '_s_libelles', $carte );
			} else {
				delete_post_meta( $id, '_s_libelles' );
			}
		}
	}
}
add_action( 'save_post_' . SCHIESSER_PRODUIT, 'schiesser_prodl_enregistrer', 60 );
add_action( 'save_post_' . SCHIESSER_TEAROOM, 'schiesser_prodl_enregistrer', 60 );

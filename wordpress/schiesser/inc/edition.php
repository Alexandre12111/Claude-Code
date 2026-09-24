<?php
/**
 * Modifier le site plus facilement.
 *
 * - Sections programmées : sur chaque bloc, « Afficher du … au … » (panneau de droite).
 *   En dehors de la période, le bloc n'est pas envoyé aux visiteurs.
 * - « Modifier cette section » : une fois connecté, un bouton apparaît au survol de chaque
 *   section du site et ouvre l'éditeur sur ce bloc précis.
 * - Point d'intérêt des photos : dans la médiathèque, un clic sur l'endroit important de la
 *   photo ; les recadrages (téléphone, cartes) le gardent toujours visible.
 * - Menu d'administration épuré pour le rôle « Gérant(e) du site ».
 *
 * Éditeur : assets/js/edition.js.
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Sections programmées                                                */
/* ------------------------------------------------------------------ */

/* Deux réglages ajoutés à tous les blocs (y compris ceux de WordPress). */
add_filter( 'register_block_type_args', function ( $args ) {
	$args['attributes'] = array_merge( isset( $args['attributes'] ) ? (array) $args['attributes'] : array(), array(
		'afficherDu' => array( 'type' => 'string' ),
		'afficherAu' => array( 'type' => 'string' ),
	) );
	return $args;
}, 20 );

/** Le bloc est-il à afficher maintenant ? (dates « AAAA-MM-JJTHH:MM », heure de Bâle) */
function schiesser_bloc_visible( $attrs ) {
	$du = (string) ( $attrs['afficherDu'] ?? '' );
	$au = (string) ( $attrs['afficherAu'] ?? '' );
	if ( '' === $du && '' === $au ) {
		return true;
	}
	$maintenant = current_datetime()->format( 'Y-m-d\TH:i' );
	if ( '' !== $du && $maintenant < substr( $du, 0, 16 ) ) {
		return false;
	}
	if ( '' !== $au && $maintenant > ( 10 === strlen( $au ) ? $au . 'T23:59' : substr( $au, 0, 16 ) ) ) {
		return false;
	}
	return true;
}

add_filter( 'pre_render_block', function ( $pre, $bloc, $parent = null ) {
	if ( ! empty( $bloc['attrs'] ) && ! schiesser_bloc_visible( $bloc['attrs'] ) ) {
		if ( null === $parent && ! empty( $bloc['blockName'] ) ) {
			schiesser_section_numero(); // la section masquée garde sa place dans la numérotation
		}
		return ''; // hors période : rien n'est envoyé (ni texte, ni photo)
	}
	return $pre;
}, 10, 3 );

/* ------------------------------------------------------------------ */
/* « Modifier cette section » sur le site                              */
/* ------------------------------------------------------------------ */

function schiesser_peut_modifier_ici() {
	static $ok = null;
	if ( null === $ok ) {
		$ok = is_user_logged_in() && is_singular() && ! is_customize_preview() && current_user_can( 'edit_post', get_queried_object_id() )
			&& 'post' !== get_post_type( get_queried_object_id() );
	}
	return $ok;
}

/**
 * Numéro de la section suivante (blocs de premier niveau du contenu de la page affichée,
 * dans l'ordre de l'éditeur), ou null hors de ce contenu.
 */
function schiesser_section_numero() {
	static $n = -1;
	static $post_id = 0;
	if ( is_admin() || ! schiesser_peut_modifier_ici() || ! in_the_loop() || get_the_ID() !== get_queried_object_id() ) {
		return null;
	}
	if ( get_the_ID() !== $post_id ) {
		$post_id = get_the_ID();
		$n       = -1;
	}
	return ++$n;
}

add_filter( 'render_block_data', function ( $bloc, $source, $parent ) {
	if ( null !== $parent || empty( $bloc['blockName'] ) ) {
		return $bloc;
	}
	$n = schiesser_section_numero();
	if ( null !== $n ) {
		$bloc['attrs']['__schSection'] = $n;
	}
	return $bloc;
}, 10, 3 );

add_filter( 'render_block', function ( $html, $bloc ) {
	if ( ! isset( $bloc['attrs']['__schSection'] ) || '' === trim( $html ) ) {
		return $html;
	}
	// Le bouton va sur l'élément principal du bloc (sa classe wp-block-…), sinon sur la première balise.
	$p = new WP_HTML_Tag_Processor( $html );
	if ( ! $p->next_tag( array( 'class_name' => 'wp-block-' . str_replace( array( 'core/', '/' ), array( '', '-' ), $bloc['blockName'] ) ) ) ) {
		$p = new WP_HTML_Tag_Processor( $html );
		if ( ! $p->next_tag() ) {
			return $html;
		}
	}
	$p->set_attribute( 'data-sch-section', (string) (int) $bloc['attrs']['__schSection'] );
	$p->set_attribute( 'data-sch-nom', schiesser_nom_bloc( $bloc['blockName'] ) );
	return $p->get_updated_html();
}, 5, 2 );

function schiesser_nom_bloc( $nom ) {
	$type = WP_Block_Type_Registry::get_instance()->get_registered( $nom );
	return $type && $type->title ? $type->title : $nom;
}

add_action( 'wp_footer', function () {
	if ( ! schiesser_peut_modifier_ici() ) {
		return;
	}
	$lien = get_edit_post_link( get_queried_object_id(), 'url' );
	if ( ! $lien ) {
		return;
	}
	?>
	<style>
		[data-sch-section]{position:relative}
		.sch-modifier{position:absolute;top:10px;right:10px;z-index:900;display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:4px;background:#1e1e1e;color:#fff;font:600 12px/1.2 system-ui,sans-serif;text-decoration:none;box-shadow:0 6px 18px rgba(0,0,0,.25);opacity:0;transform:translateY(-4px);transition:opacity .2s,transform .2s;pointer-events:none}
		[data-sch-section]:hover>.sch-modifier,.sch-modifier:focus{opacity:1;transform:none;pointer-events:auto}
		[data-sch-section]:hover{outline:2px dashed rgba(35,80,59,.55);outline-offset:-2px}
		.sch-modifier:hover{background:#23503B;color:#fff}
		@media(hover:none){.sch-modifier{opacity:1;pointer-events:auto;transform:none;padding:6px 10px}}
	</style>
	<script>
	(function(){
		var base=<?php echo wp_json_encode( $lien ); ?>;
		document.querySelectorAll('[data-sch-section]').forEach(function(s){
			if(getComputedStyle(s).position==='static')s.style.position='relative';
			var a=document.createElement('a');
			a.className='sch-modifier';
			a.href=base+(base.indexOf('?')<0?'?':'&')+'schiesser_bloc='+s.getAttribute('data-sch-section');
			a.innerHTML='<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>Modifier cette section';
			a.title='Modifier : '+(s.getAttribute('data-sch-nom')||'');
			s.appendChild(a);
		});
	})();
	</script>
	<?php
}, 30 );

/* ------------------------------------------------------------------ */
/* Point d'intérêt des photos                                          */
/* ------------------------------------------------------------------ */

/** Point d'intérêt d'une image : « 50% 30% », ou '' (centre). */
function schiesser_point_image( $id ) {
	$p = (string) get_post_meta( (int) $id, '_schiesser_point', true );
	return preg_match( '/^\d{1,3}(\.\d+)?% \d{1,3}(\.\d+)?%$/', $p ) ? $p : '';
}

/* Toutes les images de la médiathèque affichées sur le site gardent leur point d'intérêt visible. */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $image ) {
	$p = schiesser_point_image( $image->ID );
	if ( $p ) {
		$attr['style'] = trim( ( isset( $attr['style'] ) ? rtrim( $attr['style'], '; ' ) . ';' : '' ) . 'object-position:' . $p );
	}
	return $attr;
}, 10, 2 );

/* Images insérées dans le contenu (blocs Image, Couverture…) : même chose. */
add_filter( 'wp_content_img_tag', function ( $img, $contexte, $id ) {
	$p = $id ? schiesser_point_image( $id ) : '';
	if ( ! $p || false !== strpos( $img, 'object-position' ) ) {
		return $img;
	}
	$t = new WP_HTML_Tag_Processor( $img );
	if ( $t->next_tag( 'img' ) ) {
		$style = trim( (string) $t->get_attribute( 'style' ) );
		$t->set_attribute( 'style', ( $style ? rtrim( $style, '; ' ) . ';' : '' ) . 'object-position:' . $p );
		return $t->get_updated_html();
	}
	return $img;
}, 10, 3 );

/* Champ « Point d'intérêt » dans la fenêtre de la médiathèque : un clic sur la photo. */
add_filter( 'attachment_fields_to_edit', function ( $champs, $post ) {
	if ( ! wp_attachment_is_image( $post->ID ) ) {
		return $champs;
	}
	$p   = schiesser_point_image( $post->ID ) ?: '50% 50%';
	$src = wp_get_attachment_image_url( $post->ID, 'medium' );
	list( $x, $y ) = array_map( 'floatval', explode( ' ', str_replace( '%', '', $p ) ) );
	$champs['schiesser_point'] = array(
		'label' => 'Point d’intérêt',
		'input' => 'html',
		'html'  => '<div class="sch-point js-sch-point"><div class="sch-point-cadre"><img src="' . esc_url( $src ) . '" alt="" draggable="false"><span class="sch-point-cible" style="left:' . $x . '%;top:' . $y . '%"></span></div>'
			. '<input type="hidden" class="js-sch-point-valeur" name="attachments[' . (int) $post->ID . '][schiesser_point]" value="' . esc_attr( $p ) . '">'
			. '<p class="description">Cliquez sur l’endroit important de la photo (un visage, la vitrine…) : il restera visible quand la photo est recadrée, sur téléphone notamment. <button type="button" class="button-link js-sch-point-centre">Revenir au centre</button></p></div>',
	);
	return $champs;
}, 10, 2 );

add_filter( 'attachment_fields_to_save', function ( $post, $donnees ) {
	if ( isset( $donnees['schiesser_point'] ) ) {
		$p = sanitize_text_field( $donnees['schiesser_point'] );
		if ( '50% 50%' === $p || ! preg_match( '/^\d{1,3}(\.\d+)?% \d{1,3}(\.\d+)?%$/', $p ) ) {
			delete_post_meta( $post['ID'], '_schiesser_point' );
		} else {
			update_post_meta( $post['ID'], '_schiesser_point', $p );
		}
	}
	return $post;
}, 10, 2 );

/* Le point d'intérêt est aussi transmis à l'éditeur (aperçu des blocs). */
add_action( 'rest_api_init', function () {
	register_rest_field( 'attachment', 'schiesser_point', array(
		'get_callback'    => function ( $a ) {
			return schiesser_point_image( $a['id'] );
		},
		'update_callback' => function ( $valeur, $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				return new WP_Error( 'rest_forbidden', 'Modification impossible.', array( 'status' => 403 ) );
			}
			$v = sanitize_text_field( (string) $valeur );
			if ( '' === $v || '50% 50%' === $v ) {
				delete_post_meta( $post->ID, '_schiesser_point' );
			} elseif ( preg_match( '/^\d{1,3}(\.\d+)?% \d{1,3}(\.\d+)?%$/', $v ) ) {
				update_post_meta( $post->ID, '_schiesser_point', $v );
			}
			return true;
		},
		'schema'       => array( 'type' => 'string', 'context' => array( 'view', 'edit' ) ),
	) );
} );

add_action( 'admin_enqueue_scripts', function () {
	wp_add_inline_style( 'schiesser-admin', '.sch-point-cadre{position:relative;display:inline-block;max-width:100%;cursor:crosshair;line-height:0}'
		. '.sch-point-cadre img{max-width:100%;max-height:260px;height:auto;user-select:none}'
		. '.sch-point-cible{position:absolute;width:26px;height:26px;margin:-13px 0 0 -13px;border:2px solid #fff;border-radius:50%;box-shadow:0 0 0 2px #23503B,0 2px 8px rgba(0,0,0,.4);background:rgba(35,80,59,.35);pointer-events:none}' );
	wp_add_inline_script( 'jquery-core', "jQuery(function($){\n"
		. "function poser(c,x,y){var v=c.find('.js-sch-point-valeur');c.find('.sch-point-cible').css({left:x+'%',top:y+'%'});v.val(x+'% '+y+'%').trigger('change');}\n"
		. "$(document).on('click','.js-sch-point .sch-point-cadre',function(e){var r=this.getBoundingClientRect();var x=Math.round((e.clientX-r.left)/r.width*100),y=Math.round((e.clientY-r.top)/r.height*100);poser($(this).closest('.js-sch-point'),Math.max(0,Math.min(100,x)),Math.max(0,Math.min(100,y)));});\n"
		. "$(document).on('click','.js-sch-point-centre',function(){poser($(this).closest('.js-sch-point'),50,50);});\n"
		. '});' );
} );

add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_script( 'schiesser-edition', SCHIESSER_URI . '/assets/js/edition.js', array( 'wp-hooks', 'wp-compose', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-blocks', 'wp-data', 'wp-core-data', 'wp-api-fetch', 'wp-dom-ready' ), SCHIESSER_VERSION, true );
} );

/* ------------------------------------------------------------------ */
/* Menu épuré pour le rôle « Gérant(e) du site »                       */
/* ------------------------------------------------------------------ */

function schiesser_est_gerant() {
	$u = wp_get_current_user();
	return $u && in_array( 'schiesser_gerant', (array) $u->roles, true ) && ! current_user_can( 'manage_options' );
}

/** Menus gardés, dans cet ordre. */
function schiesser_menu_gerant() {
	return array(
		'schiesser-aujourdhui',
		'edit.php?post_type=page',
		'edit.php?post_type=schiesser_produit',
		'edit.php?post_type=schiesser_tearoom',
		'edit.php?post_type=schiesser_message',
		'edit.php?post_type=schiesser_avis',
		'schiesser-reglages',
	);
}

add_action( 'admin_menu', function () {
	if ( ! schiesser_est_gerant() ) {
		return;
	}
	global $menu;
	foreach ( (array) $menu as $m ) {
		if ( ! empty( $m[2] ) && ! in_array( $m[2], schiesser_menu_gerant(), true ) ) {
			remove_menu_page( $m[2] );
		}
	}
}, 1000 );

add_filter( 'custom_menu_order', function ( $v ) {
	return schiesser_est_gerant() ? true : $v;
} );
add_filter( 'menu_order', function ( $ordre ) {
	return schiesser_est_gerant() ? schiesser_menu_gerant() : $ordre;
} );

/* Le tableau de bord de WordPress renvoie le gérant sur « Aujourd'hui ». */
add_action( 'load-index.php', function () {
	if ( schiesser_est_gerant() ) {
		wp_safe_redirect( admin_url( 'admin.php?page=schiesser-aujourdhui' ) );
		exit;
	}
} );

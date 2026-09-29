<?php
/**
 * Plugin Name:       BQP Recherche
 * Description:       Barre de recherche à placer n'importe où avec [bqp_recherche], avec suggestions pendant la frappe.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Aurea Media
 * License:           GPL-2.0-or-later
 */

/**
 * BQP Recherche : une barre de recherche à placer n'importe où, par shortcode.
 *
 * [bqp_recherche] cherche dans la bibliothèque (résultats dans le catalogue
 * de la page Bibliothèque) et propose des suggestions pendant la frappe :
 * documents, auteurs, thèmes et collections. [bqp_recherche cible="site"]
 * cherche dans tout le site WordPress.
 *
 * Compatible Code Snippets (Run everywhere). Fonctionne seul ; avec
 * BQP Bibliothèque, il cherche par défaut dans la bibliothèque.
 *
 * Version : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BQR_VERSION' ) ) {
	define( 'BQR_VERSION', '1.0.0' );
}

/* -------------------------------------------------------------------------
 * 1. Le shortcode
 * ---------------------------------------------------------------------- */

add_action( 'init', 'bqr_register' );
function bqr_register() {
	add_shortcode( 'bqp_recherche', 'bqr_shortcode' );
}

/**
 * La bibliothèque est-elle disponible (plugin BQP Bibliothèque actif, avec
 * une page Bibliothèque) ?
 */
function bqr_has_library() {
	return function_exists( 'bqb_library_link' ) && '' !== bqb_library_link();
}

function bqr_shortcode( $atts ) {
	static $instance = 0;
	$instance++;

	$atts = shortcode_atts(
		array(
			'cible'       => bqr_has_library() ? 'bibliotheque' : 'site',
			'placeholder' => '',
			'bouton'      => 'Rechercher',
			'style'       => 'plein',
			'suggestions' => 'oui',
			'titre'       => '',
			'largeur'     => '',
		),
		$atts,
		'bqp_recherche'
	);

	$library = ( 'site' !== strtolower( $atts['cible'] ) ) && bqr_has_library();
	$style   = in_array( strtolower( $atts['style'] ), array( 'plein', 'compact', 'sombre' ), true ) ? strtolower( $atts['style'] ) : 'plein';
	$action  = $library ? bqb_library_link() : home_url( '/' );
	$name    = $library ? 'f_q' : 's';
	$value   = isset( $_GET[ $name ] ) ? sanitize_text_field( wp_unslash( $_GET[ $name ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$holder  = '' !== $atts['placeholder'] ? $atts['placeholder'] : ( $library ? 'Rechercher un document, un auteur, un thème…' : 'Rechercher sur le site…' );
	$id      = 'bqr-' . $instance;
	$suggest = ( 'non' !== strtolower( $atts['suggestions'] ) );
	$width   = preg_match( '/^\d{1,4}(px|%|rem|em|vw)$/', trim( $atts['largeur'] ) ) ? trim( $atts['largeur'] ) : '';

	if ( $suggest ) {
		wp_enqueue_script( 'bqr' );
	}

	$html  = bqr_inline_css();
	$html .= '<form role="search" method="get" class="bqr bqr--' . esc_attr( $style ) . '" action="' . esc_url( $action ) . '"'
		. ( $width ? ' style="max-width:' . esc_attr( $width ) . ';"' : '' )
		. ( $suggest ? ' data-bqr data-endpoint="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" data-scope="' . ( $library ? 'bibliotheque' : 'site' ) . '"' : '' ) . '>';

	if ( '' !== trim( $atts['titre'] ) ) {
		$html .= '<label class="bqr__title" for="' . esc_attr( $id ) . '">' . esc_html( $atts['titre'] ) . '</label>';
	} else {
		$html .= '<label class="bqr__sr" for="' . esc_attr( $id ) . '">' . esc_html( $library ? 'Rechercher dans la bibliothèque' : 'Rechercher sur le site' ) . '</label>';
	}

	$html .= '<div class="bqr__field">';
	$html .= '<svg class="bqr__icon" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" fill="currentColor"><path d="M9 3a6 6 0 104.47 10l3.77 3.76 1.06-1.06-3.77-3.77A6 6 0 009 3zm0 1.5a4.5 4.5 0 110 9 4.5 4.5 0 010-9z"/></svg>';
	$html .= '<input type="search" id="' . esc_attr( $id ) . '" class="bqr__input" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $holder ) . '" autocomplete="off" spellcheck="false"'
		. ( $suggest ? ' role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '-list"' : '' ) . ' />';

	$label = trim( $atts['bouton'] );
	$html .= '<button type="submit" class="bqr__btn' . ( '' === $label ? ' is-icon' : '' ) . '"' . ( '' === $label ? ' aria-label="Rechercher"' : '' ) . '>'
		. ( '' === $label ? '<svg viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" fill="currentColor"><path d="M9 3a6 6 0 104.47 10l3.77 3.76 1.06-1.06-3.77-3.77A6 6 0 009 3zm0 1.5a4.5 4.5 0 110 9 4.5 4.5 0 010-9z"/></svg>' : esc_html( $label ) )
		. '</button>';
	$html .= '</div>';

	if ( $suggest ) {
		$html .= '<div class="bqr__panel" id="' . esc_attr( $id ) . '-list" role="listbox" aria-label="Suggestions" hidden></div>';
		$html .= '<p class="bqr__sr" aria-live="polite" data-bqr-status></p>';
	}

	$html .= '</form>';

	return $html;
}

/* -------------------------------------------------------------------------
 * 2. Les suggestions pendant la frappe
 * ---------------------------------------------------------------------- */

add_action( 'wp_ajax_bqr_suggest', 'bqr_suggest' );
add_action( 'wp_ajax_nopriv_bqr_suggest', 'bqr_suggest' );
function bqr_suggest() {
	$q     = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$scope = isset( $_GET['scope'] ) ? sanitize_key( wp_unslash( $_GET['scope'] ) ) : 'bibliotheque'; // phpcs:ignore WordPress.Security.NonceVerification
	$q     = trim( function_exists( 'mb_substr' ) ? mb_substr( $q, 0, 80 ) : substr( $q, 0, 80 ) );

	if ( ( function_exists( 'mb_strlen' ) ? mb_strlen( $q ) : strlen( $q ) ) < 2 ) {
		wp_send_json_success( array( 'groups' => array(), 'all' => '' ) );
	}

	$library = ( 'site' !== $scope ) && bqr_has_library();
	$groups  = $library ? bqr_suggest_library( $q ) : bqr_suggest_site( $q );
	$all     = $library ? add_query_arg( 'f_q', rawurlencode( $q ), bqb_library_url() ) . '#bqb-catalogue' : add_query_arg( 's', rawurlencode( $q ), home_url( '/' ) );

	wp_send_json_success( array( 'groups' => $groups, 'all' => $all ) );
}

function bqr_suggest_library( $q ) {
	$groups = array();

	// Documents, dans cet ordre : titre, puis auteur trouvé, puis résumé.
	// Le texte intégral reste cherché par « Voir tous les résultats ».
	$ids    = array();
	$add    = function ( $args ) use ( &$ids ) {
		if ( count( $ids ) >= 5 ) {
			return;
		}
		$query = new WP_Query(
			array_merge(
				array(
					'post_type'           => 'bqb_document',
					'post_status'         => 'publish',
					'posts_per_page'      => 5 - count( $ids ),
					'post__not_in'        => $ids ? $ids : array( 0 ),
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
				),
				$args
			)
		);
		$ids = array_merge( $ids, array_map( 'intval', $query->posts ) );
	};

	$add( array( 's' => $q, 'search_columns' => array( 'post_title' ) ) );

	$persons = get_terms( array( 'taxonomy' => 'bqb_personne', 'search' => $q, 'fields' => 'ids', 'hide_empty' => true, 'number' => 5 ) );
	if ( $persons && ! is_wp_error( $persons ) ) {
		$add( array( 'tax_query' => array( array( 'taxonomy' => 'bqb_personne', 'terms' => array_map( 'intval', $persons ) ) ), 'orderby' => 'date' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	$add( array( 's' => $q, 'search_columns' => array( 'post_excerpt' ) ) );

	$items = array();
	foreach ( $ids as $post_id ) {
		$post    = get_post( $post_id );
		$meta    = array();
		$natures = get_the_terms( $post->ID, 'bqb_nature' );
		$authors = get_the_terms( $post->ID, 'bqb_personne' );
		$year    = get_post_meta( $post->ID, '_bqb_annee', true );

		if ( $natures && ! is_wp_error( $natures ) ) {
			$meta[] = $natures[0]->name;
		}
		if ( $year ) {
			$meta[] = (string) (int) $year;
		}
		if ( $authors && ! is_wp_error( $authors ) ) {
			$meta[] = $authors[0]->name;
		}

		$items[] = array(
			'label' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'meta'  => implode( ' · ', $meta ),
			'url'   => get_permalink( $post ),
		);
	}

	if ( $items ) {
		$groups[] = array( 'title' => 'Documents', 'items' => $items );
	}

	// Personnes et organisations : les lauréats sans document restent trouvables.
	$people = array();
	foreach ( array( 'bqb_personne' => false, 'bqb_organisation' => true ) as $taxonomy => $hide_empty ) {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'search' => $q, 'number' => 3, 'hide_empty' => $hide_empty ) );
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			$url = function_exists( 'bqb_term_url' ) ? bqb_term_url( $term ) : get_term_link( $term );
			if ( ! $url || is_wp_error( $url ) ) {
				continue;
			}
			$people[] = array(
				'label' => $term->name,
				'meta'  => ( 'bqb_personne' === $taxonomy ? 'Personne' : 'Organisation' ) . ( $term->count ? ' · ' . sprintf( _n( '%s document', '%s documents', $term->count ), $term->count ) : '' ),
				'url'   => $url,
			);
		}
	}

	if ( $people ) {
		$groups[] = array( 'title' => 'Auteurs et organisations', 'items' => array_slice( $people, 0, 4 ) );
	}

	// Thèmes et collections qui ont des documents.
	$topics = array();
	foreach ( array( 'bqb_theme' => 'Thème', 'bqb_collection' => 'Collection' ) as $taxonomy => $kind ) {
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'search' => $q, 'number' => 3, 'hide_empty' => true ) );
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			$url    = function_exists( 'bqb_term_url' ) ? bqb_term_url( $term ) : get_term_link( $term );
			$number = function_exists( 'bqb_term_number' ) ? bqb_term_number( $term ) : '';
			if ( ! $url || is_wp_error( $url ) ) {
				continue;
			}
			$topics[] = array(
				'label' => ( $number ? $number . ' ' : '' ) . $term->name,
				'meta'  => $kind,
				'url'   => $url,
			);
		}
	}

	if ( $topics ) {
		$groups[] = array( 'title' => 'Thèmes et collections', 'items' => array_slice( $topics, 0, 4 ) );
	}

	return $groups;
}

function bqr_suggest_site( $q ) {
	$types = get_post_types( array( 'public' => true, 'exclude_from_search' => false ), 'objects' );
	unset( $types['attachment'] );

	$query = new WP_Query(
		array(
			'post_type'           => array_keys( $types ),
			'post_status'         => 'publish',
			's'                   => $q,
			'posts_per_page'      => 7,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		)
	);

	$items = array();
	foreach ( $query->posts as $post ) {
		$type    = isset( $types[ $post->post_type ] ) ? $types[ $post->post_type ]->labels->singular_name : '';
		$items[] = array(
			'label' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'meta'  => 'bqb_document' === $post->post_type ? 'Bibliothèque' : $type,
			'url'   => get_permalink( $post ),
		);
	}

	return $items ? array( array( 'title' => 'Pages et contenus', 'items' => $items ) ) : array();
}

/* -------------------------------------------------------------------------
 * 3. Styles, aux couleurs du site, protégés de ceux du thème
 * ---------------------------------------------------------------------- */

/**
 * Les styles sont écrits une fois, avec la première barre de la page :
 * pas d'affichage sans style, même dans un en-tête Elementor.
 */
function bqr_inline_css() {
	static $done = false;

	if ( $done ) {
		return '';
	}

	$done = true;

	return '<style id="bqr-css">
	.bqr{--bqr-accent:#74041C;--bqr-dark:#31020C;--bqr-ink:#2f2f2f;--bqr-muted:#6f6f6f;--bqr-line:#E3DEDB;
		position:relative;width:100%;margin:0;font-family:"Inter","Montserrat",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;box-sizing:border-box;}
	.bqr *{box-sizing:border-box;}
	.bqr__sr{position:absolute !important;width:1px;height:1px;margin:-1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);border:0;}
	.bqr__title{display:block;margin:0 0 10px;font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--bqr-accent);}
	.bqr__field{position:relative;display:flex;align-items:stretch;gap:0;border:1px solid var(--bqr-line);border-radius:4px;background:#fff;box-shadow:0 10px 30px -24px rgba(49,2,12,.6);transition:border-color .2s,box-shadow .2s;}
	.bqr__field:focus-within{border-color:var(--bqr-accent);box-shadow:0 0 0 3px rgba(116,4,28,.12),0 10px 30px -24px rgba(49,2,12,.6);}
	.bqr__icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--bqr-muted);pointer-events:none;}
	.bqr .bqr__input,.bqr .bqr__input:focus{flex:1;min-width:0;width:100% !important;height:auto !important;margin:0 !important;padding:15px 14px 15px 46px !important;border:0 !important;border-radius:4px 0 0 4px !important;background:transparent !important;box-shadow:none !important;outline:none !important;
		font-family:inherit !important;font-size:.98rem !important;line-height:1.3 !important;color:var(--bqr-ink) !important;-webkit-appearance:none;appearance:none;}
	.bqr__input::placeholder{color:#8d8a88;opacity:1;}
	.bqr__input::-webkit-search-cancel-button{-webkit-appearance:none;}
	.bqr .bqr__btn,.bqr .bqr__btn:hover,.bqr .bqr__btn:focus{flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;gap:8px;margin:4px !important;padding:0 22px !important;min-height:44px;border:0 !important;border-radius:3px !important;
		background:var(--bqr-accent) !important;background-image:linear-gradient(180deg,var(--bqr-accent),var(--bqr-dark) 170%) !important;color:#fff !important;
		font-family:inherit !important;font-size:.74rem !important;font-weight:700 !important;letter-spacing:.1em !important;text-transform:uppercase !important;line-height:1 !important;box-shadow:none !important;cursor:pointer;transition:filter .2s;}
	.bqr .bqr__btn:hover{filter:brightness(1.12);}
	.bqr .bqr__btn:focus-visible{outline:2px solid var(--bqr-accent) !important;outline-offset:2px;}
	.bqr .bqr__btn.is-icon{padding:0 14px !important;min-width:44px;}

	/* Compact : pour un en-tête ou une barre latérale */
	.bqr--compact .bqr__field{border-radius:999px;box-shadow:none;}
	.bqr--compact .bqr__icon{left:14px;}
	.bqr--compact .bqr__input,.bqr--compact .bqr__input:focus{padding:10px 12px 10px 40px !important;font-size:.88rem !important;border-radius:999px 0 0 999px !important;}
	.bqr--compact .bqr__btn,.bqr--compact .bqr__btn:hover,.bqr--compact .bqr__btn:focus{min-height:34px;margin:3px !important;padding:0 16px !important;border-radius:999px !important;font-size:.68rem !important;}

	/* Sombre : sur un fond bordeaux ou une image */
	.bqr--sombre .bqr__title{color:rgba(255,255,255,.85);}
	.bqr--sombre .bqr__field{border-color:rgba(255,255,255,.35);background:rgba(255,255,255,.1);box-shadow:none;backdrop-filter:blur(6px);}
	.bqr--sombre .bqr__field:focus-within{border-color:#fff;box-shadow:0 0 0 3px rgba(255,255,255,.2);}
	.bqr--sombre .bqr__icon{color:rgba(255,255,255,.75);}
	.bqr--sombre .bqr__input,.bqr--sombre .bqr__input:focus{color:#fff !important;}
	.bqr--sombre .bqr__input::placeholder{color:rgba(255,255,255,.7);}
	.bqr--sombre .bqr__btn,.bqr--sombre .bqr__btn:hover,.bqr--sombre .bqr__btn:focus{background:#fff !important;background-image:none !important;color:var(--bqr-accent) !important;}

	/* Suggestions */
	.bqr__panel{position:absolute;z-index:9999;left:0;right:0;top:calc(100% + 8px);max-height:min(70vh,520px);overflow-y:auto;padding:8px;border:1px solid var(--bqr-line);border-radius:6px;background:#fff;box-shadow:0 24px 50px -24px rgba(49,2,12,.45);text-align:left;}
	.bqr__panel[hidden]{display:none !important;}
	.bqr__group{padding:6px 0;}
	.bqr__group + .bqr__group{border-top:1px solid var(--bqr-line);}
	.bqr__gtitle{margin:4px 10px 6px;font-size:.64rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--bqr-muted);}
	.bqr a.bqr__item{display:flex;flex-direction:column;gap:2px;padding:9px 10px;border-radius:4px;color:var(--bqr-ink) !important;text-decoration:none !important;}
	.bqr a.bqr__item:hover,.bqr a.bqr__item.is-active{background:#F7F3F2;}
	.bqr__label{font-family:"Cormorant Garamond","Playfair Display",Georgia,serif;font-size:1.08rem;font-weight:600;line-height:1.25;color:var(--bqr-dark);}
	.bqr__label mark{padding:0;background:none;color:var(--bqr-accent);text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:3px;}
	.bqr__meta{font-size:.76rem;color:var(--bqr-muted);}
	.bqr a.bqr__all{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:6px 0 0;padding:12px 10px;border-top:1px solid var(--bqr-line);border-radius:0 0 4px 4px;font-size:.74rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--bqr-accent) !important;text-decoration:none !important;}
	.bqr a.bqr__all:hover,.bqr a.bqr__all.is-active{background:#F7F3F2;}
	.bqr__empty{margin:0;padding:14px 10px;font-size:.88rem;color:var(--bqr-muted);}
	.bqr.is-loading .bqr__icon{animation:bqr-pulse 1s ease-in-out infinite;}
	@keyframes bqr-pulse{50%{opacity:.35;}}
	@media (max-width:560px){
		.bqr--plein .bqr__btn{padding:0 14px !important;}
		.bqr__panel{max-height:60vh;}
	}
	</style>';
}

/* -------------------------------------------------------------------------
 * 4. Le script : suggestions, clavier, accessibilité
 * ---------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', 'bqr_register_script' );
function bqr_register_script() {
	wp_register_script( 'bqr', false, array(), BQR_VERSION, true );
	wp_add_inline_script( 'bqr', bqr_js() );
}

function bqr_js() {
	return <<<'JS'
(function(){
	function esc(s){ return String(s).replace(/[&<>"']/g, function(c){ return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function fold(s){ return String(s).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, ''); }

	// Met en valeur le texte tapé dans chaque suggestion, accents compris.
	function highlight(label, q){
		var i = fold(label).indexOf(fold(q));
		if (!q || i < 0) { return esc(label); }
		return esc(label.slice(0, i)) + '<mark>' + esc(label.slice(i, i + q.length)) + '</mark>' + esc(label.slice(i + q.length));
	}

	function setup(form){
		var input  = form.querySelector('.bqr__input');
		var panel  = form.querySelector('.bqr__panel');
		var status = form.querySelector('[data-bqr-status]');
		if (!input || !panel || !window.fetch) { return; }

		var cache = {}, timer = null, ctrl = null, active = -1, last = '';

		function links(){ return Array.prototype.slice.call(panel.querySelectorAll('a')); }

		function close(){
			panel.hidden = true;
			input.setAttribute('aria-expanded', 'false');
			input.removeAttribute('aria-activedescendant');
			active = -1;
		}

		function move(step){
			var all = links();
			if (!all.length) { return; }
			if (active >= 0 && all[active]) { all[active].classList.remove('is-active'); all[active].setAttribute('aria-selected', 'false'); }
			active = (active + step + all.length) % all.length;
			all[active].classList.add('is-active');
			all[active].setAttribute('aria-selected', 'true');
			input.setAttribute('aria-activedescendant', all[active].id);
			all[active].scrollIntoView({ block: 'nearest' });
		}

		function render(data, q){
			var html = '', n = 0, uid = input.id;
			(data.groups || []).forEach(function(group){
				html += '<div class="bqr__group" role="group" aria-label="' + esc(group.title) + '"><p class="bqr__gtitle">' + esc(group.title) + '</p>';
				group.items.forEach(function(item){
					html += '<a class="bqr__item" role="option" aria-selected="false" id="' + uid + '-o' + (n++) + '" href="' + esc(item.url) + '">'
						+ '<span class="bqr__label">' + highlight(item.label, q) + '</span>'
						+ (item.meta ? '<span class="bqr__meta">' + esc(item.meta) + '</span>' : '') + '</a>';
				});
				html += '</div>';
			});
			if (!n) { html = '<p class="bqr__empty">Aucune suggestion pour « ' + esc(q) + ' ».</p>'; }
			if (data.all) {
				html += '<a class="bqr__all" role="option" aria-selected="false" id="' + uid + '-all" href="' + esc(data.all) + '"><span>Voir tous les résultats pour « ' + esc(q) + ' »</span><span aria-hidden="true">→</span></a>';
			}
			panel.innerHTML = html;
			panel.hidden = false;
			input.setAttribute('aria-expanded', 'true');
			active = -1;
			if (status) { status.textContent = n ? n + ' suggestion' + (n > 1 ? 's' : '') + ' disponible' + (n > 1 ? 's' : '') + '.' : 'Aucune suggestion.'; }
		}

		function lookup(q){
			if (cache[q]) { render(cache[q], q); return; }
			if (ctrl && ctrl.abort) { ctrl.abort(); }
			ctrl = window.AbortController ? new AbortController() : null;
			form.classList.add('is-loading');
			var url = form.getAttribute('data-endpoint') + '?action=bqr_suggest&scope=' + encodeURIComponent(form.getAttribute('data-scope')) + '&q=' + encodeURIComponent(q);
			fetch(url, { credentials: 'same-origin', signal: ctrl ? ctrl.signal : undefined })
				.then(function(r){ return r.json(); })
				.then(function(r){
					form.classList.remove('is-loading');
					if (!r || !r.success) { return; }
					cache[q] = r.data;
					if (input.value.trim() === q) { render(r.data, q); }
				})
				.catch(function(){ form.classList.remove('is-loading'); });
		}

		input.addEventListener('input', function(){
			var q = input.value.trim();
			clearTimeout(timer);
			if (q.length < 2) { close(); last = q; return; }
			if (q === last && !panel.hidden) { return; }
			last = q;
			timer = setTimeout(function(){ lookup(q); }, 180);
		});

		input.addEventListener('keydown', function(e){
			if ('ArrowDown' === e.key) { if (panel.hidden && input.value.trim().length > 1) { lookup(input.value.trim()); } else { move(1); } e.preventDefault(); }
			else if ('ArrowUp' === e.key) { move(-1); e.preventDefault(); }
			else if ('Escape' === e.key) { if (!panel.hidden) { close(); e.preventDefault(); } }
			else if ('Enter' === e.key && active >= 0 && links()[active]) { e.preventDefault(); window.location.href = links()[active].href; }
		});

		input.addEventListener('focus', function(){ if (panel.innerHTML && input.value.trim().length > 1) { panel.hidden = false; input.setAttribute('aria-expanded', 'true'); } });
		document.addEventListener('click', function(e){ if (!form.contains(e.target)) { close(); } });

		// Rien à envoyer si le champ est vide.
		form.addEventListener('submit', function(e){ if (!input.value.trim()) { e.preventDefault(); input.focus(); } });
	}

	function boot(){ Array.prototype.forEach.call(document.querySelectorAll('form[data-bqr]'), setup); }
	if ('loading' === document.readyState) { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
JS;
}

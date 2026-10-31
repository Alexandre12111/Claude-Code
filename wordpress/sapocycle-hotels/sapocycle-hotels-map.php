<?php
/**
 * Plugin Name:  SapoCycle · Carte des hôtels
 * Description:  Carte des hôtels membres SapoCycle (FR / DE / EN), une carte par pays, recherche, smileys, gestion des hôtels et import Excel depuis l'administration.
 * Version:      1.0.0
 * Author:       SapoCycle
 * Text Domain:  sapo-hotels
 * Requires PHP: 7.4
 *
 * Utilisation :
 *   [sapocycle_hotels]                 → toutes les cartes, un onglet par pays
 *   [sapocycle_hotels country="CH"]    → uniquement la carte de la Suisse
 *   Attributs optionnels : height="600" lang="fr|de|en" search="yes|no" legend="yes|no"
 *
 * Compatible avec le plugin « Code Snippets » (snippet PHP, « Exécuter partout »)
 * ou installable comme extension classique (dossier wp-content/plugins/).
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}
if ( class_exists( 'Sapo_Hotels_Map' ) ) {
	return;
}

final class Sapo_Hotels_Map {

	const VERSION      = '1.0.0';
	const SLUG         = 'sapo-hotels';
	const OPT_DATA     = 'sapo_hotels_data';
	const OPT_BACKUP   = 'sapo_hotels_backup';
	const OPT_SETTINGS = 'sapo_hotels_settings';
	const OPT_UPDATED  = 'sapo_hotels_updated';
	const NONCE        = 'sapo_hotels_admin';
	const OPT_IMPORTS  = 'sapo_hotels_imports';

	/** Les trois pays du site : chacun a son fichier Excel et ses trois shortcodes (FR, DE, EN). */
	const MAP_COUNTRIES = array(
		'CH' => 'suisse',
		'FR' => 'france',
		'DE' => 'allemagne',
	);

	const LEAFLET_VER = '1.9.4';
	const CLUSTER_VER = '1.5.3';

	/** Chemins SVG des smileys (repris des icônes actuelles du site). */
	const PIN_PATH  = 'M25.3,64.9 L32.5,38.6 L32.8,38.4 C39.1,34.9 43,28.3 43,21 C43,10 34,1 23,1 C12,1 3,10 3,21 C3,29.4 8.2,36.8 15.9,39.7 L16.4,39.9 L25.3,64.9 Z';
	const FACE_PATH = 'M12.6,12.2 C12.7,12.2 12.7,12.3 12.8,12.3 C12.8,12.3 12.8,12.3 12.8,12.3 C12.7,12.1 12.8,11.8 13.1,11.7 C13.1,11.6 13.2,11.6 13.3,11.6 C13.5,11.6 13.6,11.7 13.7,11.8 C14,11.5 14.5,11.4 14.9,11.4 C15,11.4 15,11.4 15.1,11.4 C15.1,11.4 15.2,11.4 15.2,11.5 C15.5,11.5 15.8,11.7 16.1,11.9 C16.4,12.2 16.4,12.7 16.3,13.1 C16.3,13.1 16.3,13.2 16.3,13.3 C16.3,13.3 16.3,13.3 16.4,13.3 C16.4,13.3 16.4,13.3 16.4,13.3 C16.7,13.5 16.7,13.9 16.6,14.2 C16.1,15.1 15.1,15.4 14.2,14.8 C14.1,14.8 14.1,14.7 14,14.6 C13.7,14.6 13.5,14.5 13.3,14.4 C12.6,13.9 11.8,11.7 12.6,12.2 M10.7,16.6 C10.7,16.7 10.7,16.8 10.8,16.8 L11,17.3 L11.7,17.6 C11.9,17.7 12.1,17.7 12.3,17.8 C12.4,17.9 12.5,17.9 12.5,17.9 C12.8,18.1 13.1,18.2 13.6,18.3 C13.7,18.3 13.8,18.3 13.9,18.3 C14,18.3 14.1,18.3 14.3,18.3 C15.4,18.3 16.5,17.8 17.4,17 C17.8,16.7 18.1,16.4 18.5,16 C18.8,15.5 19,15 19.1,14.6 L19.1,14.5 C19.3,14.1 19.4,13.4 19.2,12.8 C19.3,12.6 19.4,12.2 19.2,11.7 C19.2,11.7 19.2,11.6 19.1,11.4 L19.3,11.2 C19.1,10.9 18.9,10.7 18.7,10.4 L18.5,10.6 C18.5,10.5 18.4,10.4 18.4,10.3 L18.3,10 C17.9,9.1 17.1,8.5 16.2,8.5 C16,8.4 15.8,8.4 15.5,8.4 C15.1,8.3 14.6,8.3 14.1,8.3 L14,8.3 C13.1,8.3 12.1,8.5 11.3,9.4 L11.2,9.5 C10.8,9.9 10.2,10.6 10.1,11.5 C9.6,11.9 9.5,12.6 9.5,12.9 C9.5,12.9 9.4,13 9.4,13 C9.2,13.9 9.4,14.7 9.9,15.2 C10,15.8 10.3,16.2 10.7,16.6 M35.7,21.4 C35.7,21.3 35.6,21.3 35.6,21.3 C35.4,21.3 35.3,21.2 35.2,21 C35.1,20.9 35,20.8 35,20.7 C35,20.6 34.9,20.6 34.9,20.6 C34.8,20.5 34.8,20.4 34.8,20.3 C34.6,20.4 34.5,20.5 34.3,20.6 C33.9,20.6 33.6,20.6 33.2,20.6 C33.2,20.7 33.1,20.6 33,20.6 L33,20.7 C33,20.7 33,20.7 33,20.8 C32.8,21 32.4,21.5 32.5,22.2 C32.5,22.6 32.4,22.9 32.2,23.5 C32.1,23.9 32.1,24.3 32,24.6 C32,24.7 32,24.9 32,25 C32,25.1 31.9,25.3 31.9,25.5 C31.7,26.1 31.6,26.6 31.3,26.9 L31,27.2 L30.9,27.5 C30.7,28.3 30.1,28.8 29.3,29.5 C29.1,29.7 28.9,29.9 28.7,30 C28.3,30.4 27.7,30.7 27.2,31.1 C26.9,31.2 26.7,31.3 26.4,31.5 C26.4,31.5 26.4,31.5 26.4,31.5 C25.7,31.5 25,31.7 24.5,31.8 C24.2,31.9 23.9,32 23.7,32 C23.4,32.1 22.9,32.1 22.5,32.1 L22.3,32.1 C22,31.8 21.6,31.7 21.2,31.7 C21.1,31.7 21,31.7 20.9,31.7 L20.7,31.7 C20.6,31.7 20.6,31.6 20.5,31.6 C19.9,31.1 19.1,30.9 18.3,30.9 L17.8,30.5 C17.4,30.2 17.1,29.9 16.8,29.6 C16.5,29.5 16.4,29.2 16.1,28.9 C15.8,28.4 15.4,27.8 14.8,27.4 C14.7,27.3 14.6,27.3 14.5,27.2 C14.5,26.9 14.5,26.7 14.4,26.4 C14.3,26.1 14.1,25.8 14,25.5 C14,25.3 13.9,25.2 13.8,25 C13.4,24 13.3,23.2 13.2,22.4 L13.2,22.1 C13,22.1 12.8,21.9 12.8,21.6 C12.7,21.6 12.7,21.4 12.7,21.3 L10.9,20.7 C10.8,20.8 10.6,20.9 10.4,20.9 C10.3,20.9 10.3,20.9 10.2,20.9 C10.2,20.9 10.2,20.9 10.2,20.9 L9.9,21.5 C9.4,22.3 9.1,23.3 9.6,24.6 C9.7,24.7 9.7,24.9 9.6,25.2 C9.6,25.5 9.6,25.7 9.6,25.9 L9.5,27 L10.1,27.2 C10.1,27.7 10.2,28.2 10.6,28.6 C10.8,28.8 11,29.1 11.1,29.3 C11.6,30 12.2,30.8 13,31.4 C13.1,31.5 13.2,31.6 13.2,32 L13.3,32.7 L13.9,33 C14.1,33.1 14.3,33.2 14.5,33.3 C14.8,33.4 15.1,33.5 15.3,33.7 C16.1,34.3 17,34.8 18.1,35.1 C18.4,35.2 18.7,35.3 19,35.4 C19.4,35.6 19.8,35.7 20.2,35.9 C20.9,36 21.8,36.2 22.6,36.3 C22.8,36.3 23,36.3 23.1,36.3 C23.6,36.3 24.1,36.2 24.6,36 C24.6,36 24.7,36 24.7,36 L24.9,36.1 C25,36.1 25.1,36.1 25.2,36.1 L25.3,36.1 C25.7,36 26.3,36 26.7,35.4 L27.2,35.5 C27.2,35.5 27.3,35.5 27.3,35.5 C27.7,35.5 28,35.3 28.3,35.1 C28.4,35 28.7,34.9 29,34.8 C29.6,34.5 30.4,34.2 30.9,33.3 C30.9,33.3 31,33.3 31,33.3 C31.7,32.8 32.3,32.3 32.7,31.8 C32.9,31.6 33.1,31.5 33.2,31.3 C33.7,30.8 34.1,30.1 34.2,29.4 C34.5,29.2 34.8,28.9 34.9,28.6 C35.2,27.7 35.5,26.9 35.8,26.1 C36,25.7 35.9,25.3 35.8,25.1 C35.9,24.8 36,24.6 36,24.3 C35.9,24.2 35.8,24.1 35.8,23.8 L35.8,23.5 C35.8,23.2 36,23 36.2,23 C36.3,22.8 36.3,22.7 36.3,22.5 C36.3,22.3 36.3,22.2 36.3,22 L36.3,22 C36.2,21.9 36,21.8 35.9,21.7 C35.8,21.6 35.7,21.5 35.7,21.4 M29.4,11.5 C29.5,11.5 29.6,11.5 29.6,11.4 C29.8,11.2 30,11.1 30.3,11.1 C30.4,11.2 30.5,11.2 30.5,11.2 C30.6,11.1 30.8,11 31,11 C31.2,11 31.4,11.1 31.4,11.3 L31.4,11.3 C31.6,11.3 31.8,11.4 32,11.4 C32.2,11.4 32.4,11.5 32.5,11.6 C32.9,11.7 33,12.3 32.7,12.5 C32.8,12.6 32.8,12.7 32.8,12.8 C32.8,13.1 32.6,13.4 32.3,13.5 C32.2,13.8 32,14 31.7,14.2 C31.2,14.7 30.4,15 29.7,14.5 C28.6,13.9 28.2,12.2 29.4,11.5 M27.9,16.1 C28,16.2 28.1,16.3 28.2,16.4 C28.3,16.6 28.7,16.9 29.2,17 C29.4,17 29.5,17 29.8,17.1 C30.1,17.2 30.5,17.3 30.9,17.3 C31,17.3 31.1,17.3 31.2,17.3 C31.5,17.3 31.9,17.2 32.3,17 C33.9,16.4 35.3,15.4 35.3,12.6 L35.3,12.5 L34.5,12.6 L35.3,12.4 C35.3,12.2 35.3,12 35.2,11.7 C34.9,10.1 34.1,9.1 32.9,8.7 C31.5,8.2 30.3,8.2 29.1,8.5 C28.5,8.6 28,9 27.7,9.3 C26.1,10.8 25.9,13.4 27.1,15.1 C27.4,15.5 27.7,15.8 27.9,16.1';

	private static $instance_count = 0;

	/* =====================================================================
	 * Bootstrap
	 * ===================================================================== */

	public static function sapo_init() {
		add_action( 'init', array( __CLASS__, 'sapo_register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'sapo_register_front_assets' ) );
		add_action( 'admin_menu', array( __CLASS__, 'sapo_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'sapo_admin_assets' ) );

		foreach ( array( 'save', 'delete', 'import', 'geocode', 'settings', 'restore' ) as $a ) {
			add_action( 'wp_ajax_sapo_hotels_' . $a, array( __CLASS__, 'sapo_ajax_' . $a ) );
		}
		add_action( 'admin_post_sapo_hotels_export', array( __CLASS__, 'sapo_download_export' ) );
	}

	public static function sapo_capability() {
		return apply_filters( 'sapo_hotels_capability', 'edit_pages' );
	}

	/* =====================================================================
	 * Données
	 * ===================================================================== */

	public static function sapo_get_hotels() {
		$data = get_option( self::OPT_DATA, array() );
		return is_array( $data ) ? array_values( $data ) : array();
	}

	private static function sapo_save_hotels( array $hotels, $backup = false ) {
		if ( $backup ) {
			update_option( self::OPT_BACKUP, array( 'time' => time(), 'data' => self::sapo_get_hotels() ), false );
		}
		usort(
			$hotels,
			function ( $a, $b ) {
				return strcasecmp( remove_accents( $a['name'] ), remove_accents( $b['name'] ) );
			}
		);
		update_option( self::OPT_DATA, array_values( $hotels ), false );
		update_option( self::OPT_UPDATED, time(), false );
	}

	public static function sapo_default_settings() {
		return array(
			'tiles'         => 'soft',
			'tiles_url'     => '',
			'tiles_attr'    => '',
			'height'        => 620,
			'cluster'       => 1,
			'legend_member' => array(
				'fr' => 'Hôtel membre',
				'de' => 'Mitgliedshotel',
				'en' => 'Member hotel',
			),
			'legend_premium' => array(
				'fr' => 'Les Hôtels Premium qui nous font une donation',
				'de' => 'Premium-Hotels, die uns mit einer Spende unterstützen',
				'en' => 'Premium Hotels that make a donation to us',
			),
			'columns'       => array_fill_keys( array_keys( self::MAP_COUNTRIES ), self::sapo_default_columns() ),
			'header_row'    => array_fill_keys( array_keys( self::MAP_COUNTRIES ), 1 ),
		);
	}

	/** Colonnes Excel lues à l'import (lettre de colonne, vide = non utilisée). */
	public static function sapo_default_columns() {
		return array(
			'name'    => 'E',
			'address' => 'I',
			'zip'     => 'J',
			'city'    => 'K',
			'canton'  => 'L',
			'website' => 'M',
		);
	}

	public static function sapo_column_labels( $country = 'CH' ) {
		$region = array(
			'CH' => 'Canton',
			'FR' => 'Région ou département',
			'DE' => 'Land (Bundesland)',
		);
		return array(
			'name'    => 'Nom de l\'hôtel',
			'address' => 'Adresse',
			'zip'     => 'CH' === $country ? 'NPA' : ( 'DE' === $country ? 'PLZ (code postal)' : 'Code postal' ),
			'city'    => 'Ville',
			'canton'  => isset( $region[ $country ] ) ? $region[ $country ] : 'Région',
			'website' => 'Site internet',
		);
	}

	public static function sapo_map_country_code( $v ) {
		$c = self::sapo_country_code( $v );
		return isset( self::MAP_COUNTRIES[ $c ] ) ? $c : '';
	}

	private static function sapo_col_index( $letter ) {
		$letter = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) $letter ) );
		if ( '' === $letter || strlen( $letter ) > 3 ) {
			return null;
		}
		$i = 0;
		foreach ( str_split( $letter ) as $ch ) {
			$i = $i * 26 + ( ord( $ch ) - 64 );
		}
		return $i - 1;
	}

	private static function sapo_sanitize_columns( $in ) {
		$out = array();
		foreach ( self::sapo_default_columns() as $field => $def ) {
			$v             = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) ( is_array( $in ) && isset( $in[ $field ] ) ? $in[ $field ] : $def ) ) );
			$out[ $field ] = strlen( $v ) <= 3 ? $v : $def;
		}
		if ( '' === $out['name'] ) {
			$out['name'] = 'E';
		}
		return $out;
	}

	public static function sapo_get_settings() {
		$saved = get_option( self::OPT_SETTINGS, array() );
		$def   = self::sapo_default_settings();
		if ( ! is_array( $saved ) ) {
			return $def;
		}
		// Colonnes et ligne de titres : un réglage par pays (migration de l'ancien format unique).
		$cols = isset( $saved['columns'] ) && is_array( $saved['columns'] ) ? $saved['columns'] : array();
		if ( isset( $cols['name'] ) ) {
			$cols = array_fill_keys( array_keys( self::MAP_COUNTRIES ), $cols );
		}
		$head = isset( $saved['header_row'] ) ? $saved['header_row'] : 1;
		if ( ! is_array( $head ) ) {
			$head = array_fill_keys( array_keys( self::MAP_COUNTRIES ), $head ? 1 : 0 );
		}
		$saved['columns']    = array();
		$saved['header_row'] = array();
		foreach ( array_keys( self::MAP_COUNTRIES ) as $c ) {
			$saved['columns'][ $c ]    = self::sapo_sanitize_columns( isset( $cols[ $c ] ) ? $cols[ $c ] : self::sapo_default_columns() );
			$saved['header_row'][ $c ] = ( ! isset( $head[ $c ] ) || $head[ $c ] ) ? 1 : 0;
		}
		foreach ( $def as $k => $v ) {
			if ( 'columns' === $k || 'header_row' === $k ) {
				continue;
			}
			if ( ! isset( $saved[ $k ] ) ) {
				$saved[ $k ] = $v;
			} elseif ( is_array( $v ) ) {
				$saved[ $k ] = array_merge( $v, (array) $saved[ $k ] );
			}
		}
		return $saved;
	}

	/** Normalise une chaîne pour les comparaisons (minuscules, sans accents). */
	private static function sapo_norm( $s ) {
		$s = remove_accents( wp_strip_all_tags( (string) $s ) );
		$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s, 'UTF-8' ) : strtolower( $s );
		$s = preg_replace( '/[^a-z0-9]+/u', ' ', $s );
		return trim( preg_replace( '/\s+/', ' ', $s ) );
	}

	private static function sapo_hotel_key( array $h ) {
		return ( isset( $h['country'] ) ? $h['country'] : 'CH' ) . '|' . self::sapo_norm( $h['name'] ) . '|' . self::sapo_norm( $h['zip'] );
	}

	private static function sapo_new_id() {
		return 'h' . strtolower( wp_generate_password( 10, false, false ) );
	}

	private static function sapo_clean_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}
		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . ltrim( $url, '/' );
		}
		return esc_url_raw( $url );
	}

	private static function sapo_to_bool( $v, $default = false ) {
		if ( is_bool( $v ) ) {
			return $v;
		}
		$v = self::sapo_norm( $v );
		if ( '' === $v ) {
			return $default;
		}
		if ( in_array( $v, array( '1', 'oui', 'yes', 'ja', 'si', 'x', 'true', 'vrai', 'wahr', 'premium', 'y', 'o', 'actif', 'active', 'aktiv' ), true ) ) {
			return true;
		}
		if ( in_array( $v, array( '0', 'non', 'no', 'nein', 'false', 'faux', 'n', 'inactif', 'inactive', 'inaktiv' ), true ) ) {
			return false;
		}
		return $default;
	}

	private static function sapo_to_coord( $v, $max ) {
		$v = str_replace( array( ' ', ',' ), array( '', '.' ), trim( (string) $v ) );
		if ( '' === $v || ! is_numeric( $v ) ) {
			return null;
		}
		$f = (float) $v;
		if ( abs( $f ) > $max || 0.0 === $f ) {
			return null;
		}
		return round( $f, 6 );
	}

	/** Nettoie et valide un hôtel (depuis le formulaire ou l'import). */
	private static function sapo_sanitize_hotel( array $raw, array $base = array() ) {
		$h = array_merge(
			array(
				'id'      => '',
				'name'    => '',
				'address' => '',
				'zip'     => '',
				'city'    => '',
				'canton'  => '',
				'country' => 'CH',
				'website' => '',
				'lat'     => null,
				'lng'     => null,
				'smiley'  => 'green',
				'premium' => false,
				'active'  => true,
			),
			$base
		);

		foreach ( array( 'name', 'address', 'zip', 'city' ) as $f ) {
			if ( array_key_exists( $f, $raw ) ) {
				$h[ $f ] = sanitize_text_field( html_entity_decode( (string) $raw[ $f ], ENT_QUOTES, 'UTF-8' ) );
			}
		}
		// Retire un éventuel ✳️ saisi dans le nom (le statut Premium est un champ dédié).
		if ( preg_match( '/^\s*(\x{2733}\x{FE0F}?|\*)\s*/u', $h['name'] ) ) {
			$h['name']    = trim( preg_replace( '/^\s*(\x{2733}\x{FE0F}?|\*)\s*/u', '', $h['name'] ) );
			$h['premium'] = true;
		}
		if ( preg_match( '/^\d+\.0+$/', $h['zip'] ) ) {
			$h['zip'] = (string) (int) $h['zip'];
		}
		if ( array_key_exists( 'country', $raw ) ) {
			$h['country'] = self::sapo_country_code( $raw['country'] );
		}
		if ( array_key_exists( 'canton', $raw ) ) {
			$h['canton'] = self::sapo_canton_value( $raw['canton'], $h['country'] );
		}
		if ( array_key_exists( 'website', $raw ) ) {
			$h['website'] = self::sapo_clean_url( $raw['website'] );
		}
		if ( array_key_exists( 'lat', $raw ) ) {
			$h['lat'] = self::sapo_to_coord( $raw['lat'], 90 );
		}
		if ( array_key_exists( 'lng', $raw ) ) {
			$h['lng'] = self::sapo_to_coord( $raw['lng'], 180 );
		}
		if ( null === $h['lat'] || null === $h['lng'] ) {
			$h['lat'] = null;
			$h['lng'] = null;
		}
		$h['smiley'] = 'green'; // Un seul type de marqueur : le smiley vert.
		if ( array_key_exists( 'premium', $raw ) ) {
			$p = trim( (string) $raw['premium'] );
			if ( preg_match( '/\x{2733}/u', $p ) ) {
				$h['premium'] = true;
			} elseif ( '' !== $p ) {
				$h['premium'] = self::sapo_to_bool( $p, false );
			}
		}
		if ( array_key_exists( 'active', $raw ) ) {
			$h['active'] = self::sapo_to_bool( $raw['active'], true );
		}
		if ( '' === $h['id'] ) {
			$h['id'] = self::sapo_new_id();
		}
		return $h;
	}

	/* =====================================================================
	 * Pays et cantons
	 * ===================================================================== */

	public static function sapo_countries() {
		return array(
			'CH' => array( 'fr' => 'Suisse', 'de' => 'Schweiz', 'en' => 'Switzerland', 'alt' => array( 'svizzera', 'svizra', 'suisse', 'schweiz', 'switzerland', 'swiss', 'ch', 'che' ) ),
			'LI' => array( 'fr' => 'Liechtenstein', 'de' => 'Liechtenstein', 'en' => 'Liechtenstein', 'alt' => array( 'li', 'lie' ) ),
			'FR' => array( 'fr' => 'France', 'de' => 'Frankreich', 'en' => 'France', 'alt' => array( 'fr', 'fra' ) ),
			'DE' => array( 'fr' => 'Allemagne', 'de' => 'Deutschland', 'en' => 'Germany', 'alt' => array( 'de', 'deu', 'ger' ) ),
			'IT' => array( 'fr' => 'Italie', 'de' => 'Italien', 'en' => 'Italy', 'alt' => array( 'it', 'ita', 'italia' ) ),
			'AT' => array( 'fr' => 'Autriche', 'de' => 'Österreich', 'en' => 'Austria', 'alt' => array( 'at', 'aut', 'osterreich', 'oesterreich' ) ),
			'BE' => array( 'fr' => 'Belgique', 'de' => 'Belgien', 'en' => 'Belgium', 'alt' => array( 'be', 'bel' ) ),
			'LU' => array( 'fr' => 'Luxembourg', 'de' => 'Luxemburg', 'en' => 'Luxembourg', 'alt' => array( 'lu', 'lux' ) ),
			'NL' => array( 'fr' => 'Pays-Bas', 'de' => 'Niederlande', 'en' => 'Netherlands', 'alt' => array( 'nl', 'nld', 'holland', 'hollande' ) ),
			'ES' => array( 'fr' => 'Espagne', 'de' => 'Spanien', 'en' => 'Spain', 'alt' => array( 'es', 'esp', 'espana' ) ),
			'PT' => array( 'fr' => 'Portugal', 'de' => 'Portugal', 'en' => 'Portugal', 'alt' => array( 'pt', 'prt' ) ),
			'GB' => array( 'fr' => 'Royaume-Uni', 'de' => 'Vereinigtes Königreich', 'en' => 'United Kingdom', 'alt' => array( 'gb', 'uk', 'gbr', 'angleterre', 'england', 'grossbritannien' ) ),
			'IE' => array( 'fr' => 'Irlande', 'de' => 'Irland', 'en' => 'Ireland', 'alt' => array( 'ie', 'irl' ) ),
			'MC' => array( 'fr' => 'Monaco', 'de' => 'Monaco', 'en' => 'Monaco', 'alt' => array( 'mc', 'mco' ) ),
			'GR' => array( 'fr' => 'Grèce', 'de' => 'Griechenland', 'en' => 'Greece', 'alt' => array( 'gr', 'grc' ) ),
			'MA' => array( 'fr' => 'Maroc', 'de' => 'Marokko', 'en' => 'Morocco', 'alt' => array( 'ma', 'mar' ) ),
			'US' => array( 'fr' => 'États-Unis', 'de' => 'Vereinigte Staaten', 'en' => 'United States', 'alt' => array( 'us', 'usa' ) ),
			'CA' => array( 'fr' => 'Canada', 'de' => 'Kanada', 'en' => 'Canada', 'alt' => array( 'ca', 'can' ) ),
		);
	}

	private static function sapo_country_code( $v ) {
		$n = self::sapo_norm( $v );
		if ( '' === $n ) {
			return 'CH';
		}
		foreach ( self::sapo_countries() as $code => $c ) {
			if ( $n === strtolower( $code ) || in_array( $n, $c['alt'], true )
				|| $n === self::sapo_norm( $c['fr'] ) || $n === self::sapo_norm( $c['de'] ) || $n === self::sapo_norm( $c['en'] ) ) {
				return $code;
			}
		}
		if ( preg_match( '/^[a-z]{2}$/', $n ) ) {
			return strtoupper( $n );
		}
		return 'CH';
	}

	public static function sapo_country_name( $code, $lang ) {
		$c = self::sapo_countries();
		return isset( $c[ $code ][ $lang ] ) ? $c[ $code ][ $lang ] : $code;
	}

	public static function sapo_cantons() {
		return array(
			'AG' => array( 'Argovie', 'Aargau', 'Aargau' ),
			'AI' => array( 'Appenzell Rhodes-Intérieures', 'Appenzell Innerrhoden', 'Appenzell Innerrhoden' ),
			'AR' => array( 'Appenzell Rhodes-Extérieures', 'Appenzell Ausserrhoden', 'Appenzell Ausserrhoden' ),
			'BE' => array( 'Berne', 'Bern', 'Bern' ),
			'BL' => array( 'Bâle-Campagne', 'Basel-Landschaft', 'Basel-Landschaft' ),
			'BS' => array( 'Bâle-Ville', 'Basel-Stadt', 'Basel-Stadt' ),
			'FR' => array( 'Fribourg', 'Freiburg', 'Fribourg' ),
			'GE' => array( 'Genève', 'Genf', 'Geneva' ),
			'GL' => array( 'Glaris', 'Glarus', 'Glarus' ),
			'GR' => array( 'Grisons', 'Graubünden', 'Grisons' ),
			'JU' => array( 'Jura', 'Jura', 'Jura' ),
			'LU' => array( 'Lucerne', 'Luzern', 'Lucerne' ),
			'NE' => array( 'Neuchâtel', 'Neuenburg', 'Neuchâtel' ),
			'NW' => array( 'Nidwald', 'Nidwalden', 'Nidwalden' ),
			'OW' => array( 'Obwald', 'Obwalden', 'Obwalden' ),
			'SG' => array( 'Saint-Gall', 'St. Gallen', 'St. Gallen' ),
			'SH' => array( 'Schaffhouse', 'Schaffhausen', 'Schaffhausen' ),
			'SO' => array( 'Soleure', 'Solothurn', 'Solothurn' ),
			'SZ' => array( 'Schwytz', 'Schwyz', 'Schwyz' ),
			'TG' => array( 'Thurgovie', 'Thurgau', 'Thurgau' ),
			'TI' => array( 'Tessin', 'Tessin', 'Ticino' ),
			'UR' => array( 'Uri', 'Uri', 'Uri' ),
			'VD' => array( 'Vaud', 'Waadt', 'Vaud' ),
			'VS' => array( 'Valais', 'Wallis', 'Valais' ),
			'ZG' => array( 'Zoug', 'Zug', 'Zug' ),
			'ZH' => array( 'Zurich', 'Zürich', 'Zurich' ),
		);
	}

	/** Pour la Suisse : accepte « ZH », « Zurich », « Zürich »… et renvoie le code. */
	private static function sapo_canton_value( $v, $country ) {
		$v = sanitize_text_field( (string) $v );
		if ( 'CH' !== $country || '' === $v ) {
			return $v;
		}
		$n = self::sapo_norm( $v );
		if ( 'th' === $n ) {
			return 'TG'; // Ancienne abréviation erronée de Thurgovie.
		}
		foreach ( self::sapo_cantons() as $code => $names ) {
			if ( $n === strtolower( $code ) ) {
				return $code;
			}
			foreach ( $names as $name ) {
				if ( $n === self::sapo_norm( $name ) ) {
					return $code;
				}
			}
		}
		return $v;
	}

	public static function sapo_canton_label( $canton, $country, $lang ) {
		if ( 'CH' === $country ) {
			$c = self::sapo_cantons();
			if ( isset( $c[ $canton ] ) ) {
				$i = array( 'fr' => 0, 'de' => 1, 'en' => 2 );
				return $c[ $canton ][ $i[ $lang ] ];
			}
		}
		return $canton;
	}

	/* =====================================================================
	 * Icônes
	 * ===================================================================== */

	public static function sapo_smiley_svg( $color = 'green', $w = 42, $h = 69 ) {
		$fill   = 'red' === $color ? '#D0786D' : '#A2BD2F';
		$stroke = 'red' === $color ? '#92352A' : '#5B6D12';
		return '<svg xmlns="http://www.w3.org/2000/svg" width="' . (int) $w . '" height="' . (int) $h . '" viewBox="0 0 42 69" aria-hidden="true" focusable="false"><g fill="none" fill-rule="evenodd" transform="translate(-2)"><path fill="' . $fill . '" stroke="' . $stroke . '" stroke-width="2" d="' . self::PIN_PATH . '"/><path fill="#FFF" d="' . self::FACE_PATH . '"/></g></svg>';
	}

	public static function sapo_smiley_data_uri( $color ) {
		return 'data:image/svg+xml;base64,' . base64_encode( self::sapo_smiley_svg( $color ) );
	}

	public static function sapo_premium_svg( $size = 18 ) {
		return '<svg class="sapo-hm-premium-icon" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect width="24" height="24" rx="5" fill="#8DB33A"/><g stroke="#fff" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14M7.05 7.05l9.9 9.9M16.95 7.05l-9.9 9.9"/></g></svg>';
	}

	/* =====================================================================
	 * Langues
	 * ===================================================================== */

	public static function sapo_current_lang( $forced = '' ) {
		$forced = strtolower( (string) $forced );
		if ( in_array( $forced, array( 'fr', 'de', 'en' ), true ) ) {
			return $forced;
		}
		$lang = apply_filters( 'wpml_current_language', null );
		if ( ! $lang && function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language( 'slug' );
		}
		if ( ! $lang ) {
			$lang = substr( determine_locale(), 0, 2 );
		}
		$lang = strtolower( (string) $lang );
		return in_array( $lang, array( 'fr', 'de', 'en' ), true ) ? $lang : 'en';
	}

	public static function sapo_strings( $lang ) {
		$s = array(
			'fr' => array(
				'search'       => 'Rechercher un hôtel, une ville, un NPA…',
				'search_label' => 'Rechercher un hôtel',
				'clear'        => 'Effacer la recherche',
				'all_cantons'  => 'Tous les cantons',
				'all_regions'  => 'Toutes les régions',
				'filter_label' => 'Filtrer par canton',
				'count_one'    => '1 hôtel',
				'count_many'   => '%d hôtels',
				'none'         => 'Aucun hôtel ne correspond à votre recherche.',
				'reset'        => 'Réinitialiser',
				'elsewhere'    => 'Résultats dans un autre pays :',
				'visit'        => 'Site internet',
				'canton'       => 'Canton',
				'region'       => 'Région',
				'premium'      => 'Hôtel Premium donateur',
				'countries'    => 'Choisir un pays',
				'list'         => 'Liste des hôtels',
				'map'          => 'Carte des hôtels membres',
				'show'         => 'Voir sur la carte',
				'zoom_hint'    => 'Cliquez sur la carte pour zoomer avec la molette',
				'new_tab'      => '(nouvel onglet)',
				'empty'        => 'Aucun hôtel pour le moment.',
			),
			'de' => array(
				'search'       => 'Hotel, Ort oder PLZ suchen…',
				'search_label' => 'Hotel suchen',
				'clear'        => 'Suche löschen',
				'all_cantons'  => 'Alle Kantone',
				'all_regions'  => 'Alle Regionen',
				'filter_label' => 'Nach Kanton filtern',
				'count_one'    => '1 Hotel',
				'count_many'   => '%d Hotels',
				'none'         => 'Kein Hotel entspricht Ihrer Suche.',
				'reset'        => 'Zurücksetzen',
				'elsewhere'    => 'Treffer in einem anderen Land:',
				'visit'        => 'Webseite',
				'canton'       => 'Kanton',
				'region'       => 'Region',
				'premium'      => 'Premium-Hotel mit Spende',
				'countries'    => 'Land wählen',
				'list'         => 'Hotelliste',
				'map'          => 'Karte der Mitgliedshotels',
				'show'         => 'Auf der Karte zeigen',
				'zoom_hint'    => 'Klicken Sie auf die Karte, um mit dem Mausrad zu zoomen',
				'new_tab'      => '(neuer Tab)',
				'empty'        => 'Noch keine Hotels.',
			),
			'en' => array(
				'search'       => 'Search a hotel, city or ZIP code…',
				'search_label' => 'Search a hotel',
				'clear'        => 'Clear search',
				'all_cantons'  => 'All cantons',
				'all_regions'  => 'All regions',
				'filter_label' => 'Filter by canton',
				'count_one'    => '1 hotel',
				'count_many'   => '%d hotels',
				'none'         => 'No hotel matches your search.',
				'reset'        => 'Reset',
				'elsewhere'    => 'Results in another country:',
				'visit'        => 'Website',
				'canton'       => 'Canton',
				'region'       => 'Region',
				'premium'      => 'Premium donor hotel',
				'countries'    => 'Choose a country',
				'list'         => 'Hotel list',
				'map'          => 'Map of member hotels',
				'show'         => 'Show on map',
				'zoom_hint'    => 'Click the map to zoom with the scroll wheel',
				'new_tab'      => '(new tab)',
				'empty'        => 'No hotels yet.',
			),
		);
		return $s[ $lang ];
	}

	/* =====================================================================
	 * Front : shortcode
	 * ===================================================================== */

	/** Les 9 shortcodes : [sapocycle_suisse_fr], [sapocycle_suisse_de], [sapocycle_suisse_en], [sapocycle_france_fr]… */
	public static function sapo_shortcode_names() {
		$out = array();
		foreach ( self::MAP_COUNTRIES as $code => $slug ) {
			foreach ( array( 'fr', 'de', 'en' ) as $lang ) {
				$out[ 'sapocycle_' . $slug . '_' . $lang ] = array( $code, $lang );
			}
		}
		return $out;
	}

	public static function sapo_register_shortcode() {
		add_shortcode( 'sapocycle_hotels', array( __CLASS__, 'sapo_shortcode' ) );
		foreach ( self::sapo_shortcode_names() as $tag => $cl ) {
			add_shortcode(
				$tag,
				function ( $atts ) use ( $cl ) {
					$atts            = (array) $atts;
					$atts['country'] = $cl[0];
					$atts['lang']    = $cl[1];
					return self::sapo_shortcode( $atts );
				}
			);
		}
	}

	public static function sapo_register_front_assets() {
		wp_register_style( 'sapo-leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/' . self::LEAFLET_VER . '/leaflet.min.css', array(), self::LEAFLET_VER );
		wp_register_style( 'sapo-leaflet-cluster', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/' . self::CLUSTER_VER . '/MarkerCluster.min.css', array( 'sapo-leaflet' ), self::CLUSTER_VER );
		wp_register_script( 'sapo-leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/' . self::LEAFLET_VER . '/leaflet.min.js', array(), self::LEAFLET_VER, true );
		wp_register_script( 'sapo-leaflet-cluster', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/' . self::CLUSTER_VER . '/leaflet.markercluster.min.js', array( 'sapo-leaflet' ), self::CLUSTER_VER, true );

		wp_register_style( 'sapo-hotels', false, array( 'sapo-leaflet', 'sapo-leaflet-cluster' ), self::VERSION );
		wp_add_inline_style( 'sapo-hotels', self::sapo_front_css() );
		wp_register_script( 'sapo-hotels', false, array( 'sapo-leaflet', 'sapo-leaflet-cluster' ), self::VERSION, true );
		wp_add_inline_script( 'sapo-hotels', self::sapo_front_js() );

		// Chargement anticipé des styles dans le <head> si le shortcode est présent.
		if ( is_singular() ) {
			$post = get_post();
			if ( $post ) {
				foreach ( array_merge( array( 'sapocycle_hotels' ), array_keys( self::sapo_shortcode_names() ) ) as $tag ) {
					if ( has_shortcode( $post->post_content, $tag ) ) {
						wp_enqueue_style( 'sapo-hotels' );
						break;
					}
				}
			}
		}
	}

	public static function sapo_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'country' => '',
				'lang'    => '',
				'height'  => '',
				'search'  => 'yes',
				'legend'  => 'yes',
			),
			$atts,
			'sapocycle_hotels'
		);

		if ( ! wp_style_is( 'sapo-hotels', 'registered' ) ) {
			self::sapo_register_front_assets();
		}
		wp_enqueue_style( 'sapo-hotels' );
		wp_enqueue_script( 'sapo-hotels' );

		$lang     = self::sapo_current_lang( $atts['lang'] );
		$t        = self::sapo_strings( $lang );
		$settings = self::sapo_get_settings();
		$height   = absint( $atts['height'] ? $atts['height'] : $settings['height'] );
		$height   = max( 320, min( 1200, $height ? $height : 620 ) );

		$wanted = array_filter( array_map( array( __CLASS__, 'sapo_country_code' ), array_filter( array_map( 'trim', explode( ',', $atts['country'] ) ) ) ) );

		$hotels    = array();
		$countries = array();
		foreach ( self::sapo_get_hotels() as $h ) {
			if ( empty( $h['active'] ) || null === $h['lat'] || null === $h['lng'] ) {
				continue;
			}
			if ( $wanted && ! in_array( $h['country'], $wanted, true ) ) {
				continue;
			}
			$hotels[] = array(
				'i' => $h['id'],
				'n' => $h['name'],
				'a' => $h['address'],
				'z' => $h['zip'],
				'c' => $h['city'],
				'k' => $h['canton'],
				'kl' => self::sapo_canton_label( $h['canton'], $h['country'], $lang ),
				'p' => $h['country'],
				'w' => $h['website'],
				'la' => (float) $h['lat'],
				'lo' => (float) $h['lng'],
				's' => 0,
				'pr' => empty( $h['premium'] ) ? 0 : 1,
			);
			$countries[ $h['country'] ] = isset( $countries[ $h['country'] ] ) ? $countries[ $h['country'] ] + 1 : 1;
		}

		// Ordre des onglets : ordre demandé, sinon Suisse d'abord puis par nombre d'hôtels.
		if ( $wanted ) {
			$order = array_values( array_intersect( $wanted, array_keys( $countries ) ) );
		} else {
			arsort( $countries );
			$order = array_keys( $countries );
			if ( isset( $countries['CH'] ) ) {
				$order = array_merge( array( 'CH' ), array_diff( $order, array( 'CH' ) ) );
			}
		}
		$country_list = array();
		foreach ( $order as $code ) {
			$country_list[] = array(
				'code'  => $code,
				'name'  => self::sapo_country_name( $code, $lang ),
				'count' => $countries[ $code ],
			);
		}

		$osm  = array( 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>' );
		$tile = $osm;
		if ( 'custom' === $settings['tiles'] && '' !== $settings['tiles_url'] ) {
			$tile = array( $settings['tiles_url'], '' !== $settings['tiles_attr'] ? $settings['tiles_attr'] : $osm[1] );
		}
		$soft = 'soft' === $settings['tiles'];

		$config = array(
			'lang'      => $lang,
			'hotels'    => $hotels,
			'countries' => $country_list,
			'cantons'   => self::sapo_cantons_for_front( $lang ),
			't'         => $t,
			'tiles'     => $tile,
			'cluster'   => ! empty( $settings['cluster'] ),
			'icons'     => array(
				'green' => self::sapo_smiley_data_uri( 'green' ),
			),
		);

		++self::$instance_count;
		$uid       = 'sapo-hm-' . self::$instance_count;
		$show_tabs = count( $country_list ) > 1;

		ob_start();
		?>
		<div class="sapo-hm<?php echo $soft ? ' sapo-hm--soft' : ''; ?>" id="<?php echo esc_attr( $uid ); ?>" style="--sapo-hm-h:<?php echo (int) $height; ?>px" data-lang="<?php echo esc_attr( $lang ); ?>">
			<div class="sapo-hm__data" hidden data-config="<?php echo esc_attr( wp_json_encode( $config, JSON_UNESCAPED_UNICODE ) ); ?>"></div>

			<?php if ( ! $hotels ) : ?>
				<p class="sapo-hm__empty-all"><?php echo esc_html( $t['empty'] ); ?></p>
			<?php else : ?>

			<?php if ( $show_tabs ) : ?>
			<div class="sapo-hm__tabs" role="tablist" aria-label="<?php echo esc_attr( $t['countries'] ); ?>">
				<?php foreach ( $country_list as $i => $c ) : ?>
					<button type="button" role="tab" class="sapo-hm__tab" id="<?php echo esc_attr( $uid . '-tab-' . $c['code'] ); ?>" aria-controls="<?php echo esc_attr( $uid . '-panel' ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>" data-country="<?php echo esc_attr( $c['code'] ); ?>">
						<span><?php echo esc_html( $c['name'] ); ?></span>
						<span class="sapo-hm__tab-count"><?php echo (int) $c['count']; ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<div class="sapo-hm__panel" id="<?php echo esc_attr( $uid . '-panel' ); ?>" <?php echo $show_tabs ? 'role="tabpanel"' : ''; ?>>
				<?php if ( 'no' !== $atts['search'] ) : ?>
				<div class="sapo-hm__toolbar">
					<div class="sapo-hm__search">
						<svg class="sapo-hm__search-icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.6-3.6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						<label class="sapo-hm__sr" for="<?php echo esc_attr( $uid . '-q' ); ?>"><?php echo esc_html( $t['search_label'] ); ?></label>
						<input type="search" id="<?php echo esc_attr( $uid . '-q' ); ?>" class="sapo-hm__input" placeholder="<?php echo esc_attr( $t['search'] ); ?>" autocomplete="off" spellcheck="false" enterkeyhint="search">
						<button type="button" class="sapo-hm__clear" hidden aria-label="<?php echo esc_attr( $t['clear'] ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
						</button>
					</div>
					<div class="sapo-hm__select-wrap">
						<label class="sapo-hm__sr" for="<?php echo esc_attr( $uid . '-k' ); ?>"><?php echo esc_html( $t['filter_label'] ); ?></label>
						<select id="<?php echo esc_attr( $uid . '-k' ); ?>" class="sapo-hm__select"></select>
						<svg class="sapo-hm__chev" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</div>
					<p class="sapo-hm__count" aria-live="polite"></p>
				</div>
				<?php endif; ?>

				<div class="sapo-hm__body">
					<div class="sapo-hm__list-wrap">
						<ul class="sapo-hm__list" aria-label="<?php echo esc_attr( $t['list'] ); ?>"></ul>
						<div class="sapo-hm__none" hidden>
							<p><?php echo esc_html( $t['none'] ); ?></p>
							<div class="sapo-hm__elsewhere" hidden></div>
							<button type="button" class="sapo-hm__reset"><?php echo esc_html( $t['reset'] ); ?></button>
						</div>
					</div>
					<div class="sapo-hm__map-wrap">
						<div class="sapo-hm__map" role="region" aria-label="<?php echo esc_attr( $t['map'] ); ?>"></div>
						<div class="sapo-hm__hint" aria-hidden="true"><?php echo esc_html( $t['zoom_hint'] ); ?></div>
					</div>
				</div>
			</div>

			<?php if ( 'no' !== $atts['legend'] ) : ?>
			<ul class="sapo-hm__legend">
				<?php
				$leg = array(
					array( self::sapo_smiley_svg( 'green', 16, 26 ), $settings['legend_member'][ $lang ] ),
					array( self::sapo_premium_svg( 18 ), $settings['legend_premium'][ $lang ] ),
				);
				foreach ( $leg as $l ) :
					if ( '' === trim( (string) $l[1] ) ) {
						continue;
					}
					?>
					<li><?php echo $l[0]; // phpcs:ignore WordPress.Security.EscapeOutput -- SVG interne. ?><span><?php echo esc_html( $l[1] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function sapo_cantons_for_front( $lang ) {
		$out = array();
		foreach ( array_keys( self::sapo_cantons() ) as $code ) {
			$out[ $code ] = self::sapo_canton_label( $code, 'CH', $lang );
		}
		return $out;
	}

	/* =====================================================================
	 * Front : CSS
	 * ===================================================================== */

	private static function sapo_front_css() {
		return <<<'CSS'
.sapo-hm{--sapo-blue:#0069B4;--sapo-blue-2:#0175D6;--sapo-sky:#2EA3F2;--sapo-green:#A2BD2F;--sapo-green-d:#5B6D12;--sapo-text:#3D3D3D;--sapo-muted:#666B73;--sapo-bg:#F4F4F4;--sapo-line:#E3E5E8;--sapo-white:#fff;--sapo-r:6px;--sapo-head:'Montserrat',Helvetica,Arial,Lucida,sans-serif;--sapo-body:'Source Sans Pro',Helvetica,Arial,Lucida,sans-serif;font-family:var(--sapo-body);color:var(--sapo-text);font-size:16px;line-height:1.5;margin:0 0 24px;-webkit-font-smoothing:antialiased}
.sapo-hm *,.sapo-hm *::before,.sapo-hm *::after{box-sizing:border-box}
.sapo-hm__sr{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.sapo-hm button{font-family:inherit}
.sapo-hm :focus-visible{outline:2px solid var(--sapo-sky);outline-offset:2px}

/* Onglets pays */
.sapo-hm__tabs{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 16px;padding:0}
.sapo-hm__tab{display:inline-flex;align-items:center;gap:10px;min-height:44px;padding:8px 18px;border:1px solid var(--sapo-line);border-radius:999px;background:var(--sapo-white);color:var(--sapo-text);font-family:var(--sapo-head);font-size:13px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;transition:background-color .2s ease,border-color .2s ease,color .2s ease}
.sapo-hm__tab:hover{border-color:var(--sapo-blue);color:var(--sapo-blue)}
.sapo-hm__tab[aria-selected="true"]{background:var(--sapo-blue);border-color:var(--sapo-blue);color:#fff}
.sapo-hm__tab-count{display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:22px;padding:0 7px;border-radius:999px;background:var(--sapo-bg);color:var(--sapo-text);font-size:12px;letter-spacing:0}
.sapo-hm__tab[aria-selected="true"] .sapo-hm__tab-count{background:rgba(255,255,255,.2);color:#fff}

/* Barre de recherche */
.sapo-hm__toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:0 0 16px}
.sapo-hm__search{position:relative;flex:1 1 320px;min-width:0}
.sapo-hm__search-icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--sapo-muted);pointer-events:none}
.sapo-hm .sapo-hm__input{width:100%;height:52px;margin:0;padding:0 48px 0 48px;border:1px solid var(--sapo-line);border-radius:var(--sapo-r);background:var(--sapo-white);color:var(--sapo-text);font-family:var(--sapo-body);font-size:17px;line-height:52px;box-shadow:none;-webkit-appearance:none;appearance:none;transition:border-color .2s ease,box-shadow .2s ease}
.sapo-hm .sapo-hm__input::placeholder{color:#8A8F96;opacity:1}
.sapo-hm .sapo-hm__input:focus{outline:none;border-color:var(--sapo-blue);box-shadow:0 0 0 3px rgba(0,105,180,.15)}
.sapo-hm__input::-webkit-search-cancel-button{display:none}
.sapo-hm__clear{position:absolute;right:6px;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;padding:0;border:0;border-radius:50%;background:transparent;color:var(--sapo-muted);cursor:pointer;transition:background-color .2s ease,color .2s ease}
.sapo-hm__clear:hover{background:var(--sapo-bg);color:var(--sapo-text)}
.sapo-hm__select-wrap{position:relative;flex:0 1 240px}
.sapo-hm .sapo-hm__select{width:100%;height:52px;margin:0;padding:0 40px 0 16px;border:1px solid var(--sapo-line);border-radius:var(--sapo-r);background:var(--sapo-white);color:var(--sapo-text);font-family:var(--sapo-body);font-size:16px;cursor:pointer;-webkit-appearance:none;appearance:none;transition:border-color .2s ease}
.sapo-hm .sapo-hm__select:hover,.sapo-hm .sapo-hm__select:focus{border-color:var(--sapo-blue);outline:none}
.sapo-hm__chev{position:absolute;right:16px;top:50%;transform:translateY(-50%);color:var(--sapo-muted);pointer-events:none}
.sapo-hm__count{flex:0 0 auto;margin:0 0 0 auto!important;padding:0!important;font-family:var(--sapo-head);font-size:12px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--sapo-muted)}

/* Corps : liste + carte */
.sapo-hm__body{display:grid;grid-template-columns:minmax(300px,380px) 1fr;height:var(--sapo-hm-h,620px);border:1px solid var(--sapo-line);border-radius:var(--sapo-r);overflow:hidden;background:var(--sapo-white)}
.sapo-hm__list-wrap{position:relative;min-height:0;border-right:1px solid var(--sapo-line);background:var(--sapo-white)}
.sapo-hm__list{height:100%;margin:0!important;padding:0!important;list-style:none!important;overflow-y:auto;overscroll-behavior:contain;scrollbar-width:thin;scrollbar-color:#C9D6E3 transparent}
.sapo-hm__list::-webkit-scrollbar{width:8px}
.sapo-hm__list::-webkit-scrollbar-thumb{background:#C9D6E3;border-radius:8px}
.sapo-hm__item{position:relative;margin:0!important;padding:0!important;list-style:none!important;border-bottom:1px solid var(--sapo-line)}
.sapo-hm__item::before{display:none!important}
.sapo-hm__card{display:grid;grid-template-columns:22px 1fr;gap:4px 14px;width:100%;padding:18px 20px 18px 18px;border:0;border-left:3px solid transparent;background:transparent;color:inherit;text-align:left;cursor:pointer;transition:background-color .2s ease,border-color .2s ease}
.sapo-hm__card:hover{background:#F7F9FB}
.sapo-hm__item.is-active .sapo-hm__card{background:#EEF5FB;border-left-color:var(--sapo-blue)}
.sapo-hm__pin{grid-row:1/span 3;padding-top:2px}
.sapo-hm__pin svg{display:block;width:18px;height:auto}
.sapo-hm__name{display:flex;align-items:flex-start;gap:8px;margin:0;font-family:var(--sapo-head);font-size:15px;font-weight:700;line-height:1.35;color:var(--sapo-blue)}
.sapo-hm__name .sapo-hm-premium-icon{flex:0 0 auto;margin-top:1px}
.sapo-hm__addr{margin:0;font-size:15px;line-height:1.45;color:var(--sapo-text)}
.sapo-hm__meta{display:flex;flex-wrap:wrap;align-items:center;gap:6px 12px;margin:4px 0 0;font-size:14px;color:var(--sapo-muted)}
.sapo-hm__chip{display:inline-flex;align-items:center;height:22px;padding:0 8px;border-radius:4px;background:var(--sapo-bg);font-family:var(--sapo-head);font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--sapo-text)}
.sapo-hm__web{position:absolute;right:12px;top:14px;display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:50%;color:var(--sapo-blue-2);transition:background-color .2s ease}
.sapo-hm__web:hover{background:#E3EEF8}
.sapo-hm__item.has-web .sapo-hm__card{padding-right:52px}
.sapo-hm mark{background:#FFF1A8;color:inherit;padding:0 1px;border-radius:2px}
.sapo-hm__none{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;padding:32px;text-align:center;color:var(--sapo-muted)}
.sapo-hm__none[hidden]{display:none}
.sapo-hm__none p{margin:0;padding:0}
.sapo-hm__elsewhere{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;font-size:14px}
.sapo-hm__elsewhere[hidden]{display:none}
.sapo-hm__elsewhere span{width:100%}
.sapo-hm__elsewhere button,.sapo-hm__reset{min-height:44px;padding:10px 18px;border:0;border-radius:var(--sapo-r);background:var(--sapo-sky);color:#fff;font-family:var(--sapo-head);font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;cursor:pointer;transition:background-color .2s ease}
.sapo-hm__elsewhere button{background:var(--sapo-blue)}
.sapo-hm__elsewhere button:hover,.sapo-hm__reset:hover{background:var(--sapo-blue-2)}

/* Carte */
.sapo-hm__map-wrap{position:relative;min-height:0}
.sapo-hm__map{position:absolute;inset:0;background:#E9EEF2;z-index:0}
.sapo-hm__hint{position:absolute;left:50%;bottom:28px;transform:translate(-50%,8px);padding:8px 14px;border-radius:999px;background:rgba(61,61,61,.88);color:#fff;font-size:13px;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .25s ease,transform .25s ease;z-index:500}
.sapo-hm__hint.is-visible{opacity:1;transform:translate(-50%,0)}
.sapo-hm .leaflet-container{font-family:var(--sapo-body);font-size:14px}
.sapo-hm--soft .leaflet-tile-pane{filter:grayscale(.55) saturate(.85) brightness(1.04) contrast(.92)}
.sapo-hm .leaflet-bar{border:1px solid var(--sapo-line);border-radius:var(--sapo-r);box-shadow:none;overflow:hidden}
.sapo-hm .leaflet-bar a{width:36px;height:36px;line-height:36px;color:var(--sapo-text);border-bottom-color:var(--sapo-line)}
.sapo-hm .leaflet-bar a:hover{background:var(--sapo-bg);color:var(--sapo-blue)}
.sapo-hm .leaflet-control-attribution{font-size:11px;background:rgba(255,255,255,.85)}
.sapo-hm .leaflet-marker-icon.sapo-hm-marker{transition:filter .2s ease}
.sapo-hm .leaflet-marker-icon.sapo-hm-marker.is-active{filter:drop-shadow(0 4px 6px rgba(0,0,0,.25))}
.sapo-hm-cluster{display:flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:var(--sapo-blue);border:3px solid #fff;color:#fff;font-family:var(--sapo-head);font-size:13px;font-weight:700;box-shadow:0 0 0 1px rgba(0,105,180,.35)}
.sapo-hm-cluster.is-md{width:46px;height:46px;font-size:14px}
.sapo-hm-cluster.is-lg{width:54px;height:54px;font-size:15px}

/* Popup */
.sapo-hm .leaflet-popup-content-wrapper{border-radius:var(--sapo-r);box-shadow:0 8px 28px rgba(0,0,0,.14);padding:0}
.sapo-hm .leaflet-popup-content{margin:0;width:280px!important;line-height:1.45}
.sapo-hm .leaflet-popup-tip{box-shadow:none}
.sapo-hm .leaflet-container a.leaflet-popup-close-button{top:8px;right:8px;width:32px;height:32px;font-size:20px;line-height:30px;color:var(--sapo-muted)}
.sapo-hm__pop{padding:18px 20px 16px}
.sapo-hm__pop-badge{display:flex!important;align-items:center;gap:8px;margin:0 28px 8px 0!important;font-size:13px!important;font-weight:600;color:#4D6B0F!important}
.sapo-hm__pop h3{margin:0 24px 8px 0!important;padding:0!important;font-family:var(--sapo-head)!important;font-size:16px!important;font-weight:700!important;line-height:1.3!important;color:var(--sapo-blue)!important}
.sapo-hm__pop p{margin:0!important;padding:0!important;font-size:15px;color:var(--sapo-text)}
.sapo-hm__pop .sapo-hm__meta{margin-top:8px}
.sapo-hm__pop-cta{display:flex;align-items:center;justify-content:center;gap:8px;min-height:44px;margin-top:14px;padding:10px 16px;border-radius:var(--sapo-r);background:var(--sapo-sky);color:#fff!important;font-family:var(--sapo-head);font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;text-decoration:none!important;transition:background-color .2s ease}
.sapo-hm__pop-cta:hover{background:var(--sapo-blue-2)}

/* Légende */
.sapo-hm__legend{display:flex;flex-wrap:wrap;gap:12px 28px;margin:16px 0 0!important;padding:0!important;list-style:none!important;font-size:15px;color:var(--sapo-text)}
.sapo-hm__legend li{display:flex;align-items:center;gap:10px;margin:0!important;padding:0!important;list-style:none!important}
.sapo-hm__legend li::before{display:none!important}
.sapo-hm__legend svg{flex:0 0 auto}
.sapo-hm__empty-all{padding:32px;border:1px dashed var(--sapo-line);border-radius:var(--sapo-r);text-align:center;color:var(--sapo-muted)}

/* Responsive */
@media (max-width:980px){
 .sapo-hm__body{grid-template-columns:1fr;grid-template-rows:min(56vh,440px) auto;height:auto}
 .sapo-hm__map-wrap{order:-1;height:min(56vh,440px)}
 .sapo-hm__list-wrap{border-right:0;border-top:1px solid var(--sapo-line);min-height:180px}
 .sapo-hm__list{max-height:440px}
 .sapo-hm__select-wrap{flex:1 1 200px}
 .sapo-hm__count{width:100%;margin:0!important}
}
@media (max-width:480px){
 .sapo-hm__tab{padding:8px 14px;font-size:12px}
 .sapo-hm .sapo-hm__input,.sapo-hm .sapo-hm__select{height:48px;font-size:16px}
 .sapo-hm .leaflet-popup-content{width:240px!important}
}
@media (prefers-reduced-motion:reduce){.sapo-hm *{transition:none!important;animation:none!important}}
CSS;
	}

	/* =====================================================================
	 * Front : JS
	 * ===================================================================== */

	private static function sapo_front_js() {
		return <<<'JS'
(function(){
"use strict";
var reduce=window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches;
function norm(s){return String(s||"").normalize("NFD").replace(/[\u0300-\u036f]/g,"").toLowerCase().replace(/ß/g,"ss").replace(/[^a-z0-9]+/g," ").trim();}
function esc(s){return String(s==null?"":s).replace(/[&<>"']/g,function(c){return{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c];});}
function host(u){try{return new URL(u).hostname.replace(/^www\./,"");}catch(e){return u;}}
function hl(text,terms){var t=esc(text);if(!terms.length)return t;var plain=String(text||""),n=plain.normalize("NFD").replace(/[\u0300-\u036f]/g,"").toLowerCase();var marks=[];terms.forEach(function(term){if(term.length<2)return;var i=n.indexOf(term);while(i>-1){if(i===0||!/[a-z0-9]/.test(n.charAt(i-1)))marks.push([i,i+term.length]);i=n.indexOf(term,i+term.length);}});if(!marks.length||n.length!==plain.length)return t;marks.sort(function(a,b){return a[0]-b[0];});var out="",pos=0;marks.forEach(function(m){if(m[0]<pos)return;out+=esc(plain.slice(pos,m[0]))+"<mark>"+esc(plain.slice(m[0],m[1]))+"</mark>";pos=m[1];});return out+esc(plain.slice(pos));}
var PREMIUM='<svg class="sapo-hm-premium-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect width="24" height="24" rx="5" fill="#8DB33A"/><g stroke="#fff" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14M7.05 7.05l9.9 9.9M16.95 7.05l-9.9 9.9"/></g></svg>';
var EXT='<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

function Map_(root){
  var cfg;try{cfg=JSON.parse(root.querySelector(".sapo-hm__data").getAttribute("data-config"));}catch(e){return;}
  if(!cfg.hotels||!cfg.hotels.length||!window.L)return;
  var t=cfg.t,L=window.L;
  var $=function(s){return root.querySelector(s);};
  var input=$(".sapo-hm__input"),clear=$(".sapo-hm__clear"),sel=$(".sapo-hm__select"),count=$(".sapo-hm__count"),list=$(".sapo-hm__list"),none=$(".sapo-hm__none"),elsewhere=$(".sapo-hm__elsewhere"),hint=$(".sapo-hm__hint"),mapEl=$(".sapo-hm__map");
  var tabs=[].slice.call(root.querySelectorAll(".sapo-hm__tab"));
  var country=cfg.countries.length?cfg.countries[0].code:"CH";
  cfg.hotels.forEach(function(h){h._s=" "+norm([h.n,h.a,h.z,h.c,h.k,h.kl,h.w?host(h.w).replace(/\./g," "):""].join(" "));});
  cfg.hotels.sort(function(a,b){return a.n.localeCompare(b.n,cfg.lang,{sensitivity:"base"});});

  var map=L.map(mapEl,{scrollWheelZoom:false,zoomControl:true,attributionControl:true,worldCopyJump:true,tap:true});
  L.tileLayer(cfg.tiles[0],{attribution:cfg.tiles[1],maxZoom:19,subdomains:"abcd"}).addTo(map);
  map.zoomControl.setPosition("topright");
  map.on("click focus",function(){map.scrollWheelZoom.enable();});
  map.on("mouseout",function(){map.scrollWheelZoom.disable();});
  var hintTimer;mapEl.addEventListener("wheel",function(){if(map.scrollWheelZoom.enabled()||!hint)return;hint.classList.add("is-visible");clearTimeout(hintTimer);hintTimer=setTimeout(function(){hint.classList.remove("is-visible");},1400);},{passive:true});

  function icon(h,big){var w=big?38:28,hh=Math.round(w*69/42);return L.icon({iconUrl:cfg.icons.green,iconSize:[w,hh],iconAnchor:[Math.round(w*23.3/42),hh],popupAnchor:[0,-hh+4],className:"sapo-hm-marker"+(big?" is-active":"")});}
  var layer=(cfg.cluster&&L.markerClusterGroup)?L.markerClusterGroup({showCoverageOnHover:false,maxClusterRadius:42,spiderfyOnMaxZoom:true,disableClusteringAtZoom:15,iconCreateFunction:function(c){var n=c.getChildCount();var cls=n>=25?" is-lg":n>=10?" is-md":"";var s=n>=25?54:n>=10?46:40;return L.divIcon({html:'<div class="sapo-hm-cluster'+cls+'"><span>'+n+'</span></div>',className:"",iconSize:[s,s]});}}):L.layerGroup();
  layer.addTo(map);

  function popup(h){
    var meta=(h.k?'<span class="sapo-hm__chip">'+esc(h.k)+'</span>':'')+(h.kl&&h.kl!==h.k?'<span>'+esc((h.p==="CH"?t.canton:t.region)+" "+h.kl)+'</span>':'');
    return '<div class="sapo-hm__pop">'+(h.pr?'<p class="sapo-hm__pop-badge">'+PREMIUM+esc(t.premium)+'</p>':'')+'<h3>'+esc(h.n)+'</h3><p>'+esc(h.a)+'</p><p>'+esc([h.z,h.c].filter(Boolean).join(" "))+'</p>'+(meta?'<p class="sapo-hm__meta">'+meta+'</p>':'')+(h.w?'<a class="sapo-hm__pop-cta" href="'+esc(h.w)+'" target="_blank" rel="noopener">'+esc(t.visit)+EXT+'<span class="sapo-hm__sr"> '+esc(t.new_tab)+'</span></a>':'')+'</div>';
  }
  cfg.hotels.forEach(function(h){
    h._m=L.marker([h.la,h.lo],{icon:icon(h,false),title:h.n,alt:h.n,riseOnHover:true,keyboard:true});
    h._m.bindPopup(function(){return popup(h);},{maxWidth:300,minWidth:240,autoPanPadding:[24,24],closeButton:true});
    h._m.on("click",function(){activate(h,false);});
    h._m.on("popupclose",function(){if(active===h)deactivate();});
  });

  var active=null,visible=[],first=true;
  function deactivate(){if(!active)return;active._m.setIcon(icon(active,false));if(active._li)active._li.classList.remove("is-active");active=null;}
  function activate(h,fromList){
    if(active&&active!==h)deactivate();
    active=h;h._m.setIcon(icon(h,true));h._m.setZIndexOffset(1000);
    if(h._li){h._li.classList.add("is-active");if(!fromList){var top=h._li.offsetTop-list.clientHeight/2+h._li.offsetHeight/2;list.scrollTo({top:Math.max(0,top),behavior:reduce?"auto":"smooth"});}}
  }
  function focusHotel(h){
    activate(h,true);
    var go=function(){h._m.openPopup();};
    if(layer.zoomToShowLayer){var z=Math.max(map.getZoom(),13);if(reduce){map.setView([h.la,h.lo],z);}else{map.flyTo([h.la,h.lo],z,{duration:.6});}map.once("moveend",function(){layer.zoomToShowLayer(h._m,go);});}
    else{map.setView([h.la,h.lo],Math.max(map.getZoom(),13),{animate:!reduce});go();}
  }

  function fillCantons(){
    if(!sel)return;
    var seen={},opts=[];
    cfg.hotels.forEach(function(h){if(h.p===country&&h.k&&!seen[h.k]){seen[h.k]=1;opts.push({v:h.k,l:h.kl&&h.kl!==h.k?h.kl+" ("+h.k+")":h.k});}});
    opts.sort(function(a,b){return a.l.localeCompare(b.l,cfg.lang);});
    sel.innerHTML='<option value="">'+esc(country==="CH"?t.all_cantons:t.all_regions)+'</option>'+opts.map(function(o){return'<option value="'+esc(o.v)+'">'+esc(o.l)+'</option>';}).join("");
    sel.parentNode.style.display=opts.length>1?"":"none";
  }

  function terms(){return norm(input?input.value:"").split(" ").filter(Boolean);}
  function match(h,ts,k){if(k&&h.k!==k)return false;for(var i=0;i<ts.length;i++){if(h._s.indexOf(" "+ts[i])===-1)return false;}return true;}

  function render(fit){
    var ts=terms(),k=sel?sel.value:"";
    if(clear)clear.hidden=!(input&&input.value);
    visible=cfg.hotels.filter(function(h){return h.p===country&&match(h,ts,k);});
    deactivate();map.closePopup();
    layer.clearLayers();
    var ms=visible.map(function(h){return h._m;});
    if(layer.addLayers)layer.addLayers(ms);else ms.forEach(function(m){layer.addLayer(m);});
    var html="";
    visible.forEach(function(h,i){
      var meta=(h.k?'<span class="sapo-hm__chip" title="'+esc(h.kl||h.k)+'">'+esc(h.k)+'</span>':'')+(h.w?'<span>'+esc(host(h.w))+'</span>':'');
      html+='<li class="sapo-hm__item'+(h.w?' has-web':'')+'" data-i="'+i+'"><button type="button" class="sapo-hm__card" aria-label="'+esc(h.n+", "+[h.a,h.z,h.c].filter(Boolean).join(" ")+". "+t.show)+'"><span class="sapo-hm__pin" aria-hidden="true"><img src="'+cfg.icons.green+'" alt="" width="18" height="30"></span><span class="sapo-hm__name">'+(h.pr?PREMIUM:'')+'<span>'+hl(h.n,ts)+'</span></span><span class="sapo-hm__addr">'+hl(h.a,ts)+'<br>'+hl([h.z,h.c].filter(Boolean).join(" "),ts)+'</span>'+(meta?'<span class="sapo-hm__meta">'+meta+'</span>':'')+'</button>'+(h.w?'<a class="sapo-hm__web" href="'+esc(h.w)+'" target="_blank" rel="noopener" aria-label="'+esc(t.visit+" "+h.n+" "+t.new_tab)+'">'+EXT+'</a>':'')+'</li>';
    });
    list.innerHTML=html;
    [].forEach.call(list.children,function(li){var h=visible[+li.getAttribute("data-i")];h._li=li;li.firstChild.addEventListener("click",function(){focusHotel(h);});});
    list.scrollTop=0;
    var n=visible.length;
    if(count)count.textContent=n===1?t.count_one:t.count_many.replace("%d",n);
    none.hidden=n>0;list.hidden=n===0;
    if(!n&&elsewhere){
      var other={};cfg.hotels.forEach(function(h){if(h.p!==country&&match(h,ts,""))other[h.p]=(other[h.p]||0)+1;});
      var keys=Object.keys(other);
      elsewhere.hidden=!keys.length||!ts.length;
      elsewhere.innerHTML=keys.length?'<span>'+esc(t.elsewhere)+'</span>'+keys.map(function(c){var cc=cfg.countries.filter(function(x){return x.code===c;})[0];return'<button type="button" data-c="'+esc(c)+'">'+esc((cc?cc.name:c)+" ("+other[c]+")")+'</button>';}).join(""):"";
      [].forEach.call(elsewhere.querySelectorAll("button"),function(b){b.addEventListener("click",function(){var keep=input.value;setCountry(b.getAttribute("data-c"),true);input.value=keep;render(true);});});
    }
    if(fit!==false){
      var pts=(n?visible:cfg.hotels.filter(function(h){return h.p===country;})).map(function(h){return[h.la,h.lo];});
      if(pts.length===1)map.setView(pts[0],13,{animate:!first&&!reduce});
      else if(pts.length)map.fitBounds(L.latLngBounds(pts),{padding:[36,36],maxZoom:13,animate:!first&&!reduce});
      first=false;
    }
  }

  function setCountry(c,focusTab){
    country=c;
    tabs.forEach(function(b){var on=b.getAttribute("data-country")===c;b.setAttribute("aria-selected",on?"true":"false");b.tabIndex=on?0:-1;if(on&&focusTab)b.focus();});
    if(sel)sel.value="";
    if(input)input.value="";
    fillCantons();render(true);
  }
  tabs.forEach(function(b,i){
    b.addEventListener("click",function(){setCountry(b.getAttribute("data-country"),false);});
    b.addEventListener("keydown",function(e){var d=e.key==="ArrowRight"?1:e.key==="ArrowLeft"?-1:0;if(e.key==="Home")d=-i;if(e.key==="End")d=tabs.length-1-i;if(!d)return;e.preventDefault();var nb=tabs[(i+d+tabs.length)%tabs.length];setCountry(nb.getAttribute("data-country"),true);});
  });

  var deb;
  if(input){
    input.addEventListener("input",function(){clearTimeout(deb);deb=setTimeout(function(){render(true);},160);});
    input.addEventListener("keydown",function(e){if(e.key==="Escape"&&input.value){input.value="";render(true);}if(e.key==="Enter"&&visible.length===1){e.preventDefault();focusHotel(visible[0]);}});
  }
  if(clear)clear.addEventListener("click",function(){input.value="";render(true);input.focus();});
  if(sel)sel.addEventListener("change",function(){render(true);});
  var reset=$(".sapo-hm__reset");if(reset)reset.addEventListener("click",function(){if(input)input.value="";if(sel)sel.value="";render(true);if(input)input.focus();});

  fillCantons();render(true);
  if("ResizeObserver" in window){new ResizeObserver(function(){map.invalidateSize();}).observe(mapEl);}
}
function boot(){[].forEach.call(document.querySelectorAll(".sapo-hm"),function(el){if(el.__sapo)return;el.__sapo=1;Map_(el);});}
if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",boot);else boot();
window.addEventListener("load",boot);
})();
JS;
	}

	/* =====================================================================
	 * Administration : menu et page
	 * ===================================================================== */

	public static function sapo_admin_menu() {
		add_menu_page(
			'Carte des hôtels SapoCycle',
			'Carte des hôtels',
			self::sapo_capability(),
			self::SLUG,
			array( __CLASS__, 'sapo_render_admin' ),
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 42 69"><g transform="translate(-2)"><path fill="#a7aaad" d="' . self::PIN_PATH . '"/><path fill="#1d2327" d="' . self::FACE_PATH . '"/></g></svg>' ),
			26
		);
	}

	public static function sapo_admin_assets( $hook ) {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style( 'sapo-leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/' . self::LEAFLET_VER . '/leaflet.min.css', array(), self::LEAFLET_VER );
		wp_enqueue_script( 'sapo-leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/' . self::LEAFLET_VER . '/leaflet.min.js', array(), self::LEAFLET_VER, true );

		wp_register_style( 'sapo-hotels-admin', false, array( 'sapo-leaflet' ), self::VERSION );
		wp_enqueue_style( 'sapo-hotels-admin' );
		wp_add_inline_style( 'sapo-hotels-admin', self::sapo_admin_css() );

		wp_register_script( 'sapo-hotels-admin', false, array( 'sapo-leaflet' ), self::VERSION, true );
		wp_enqueue_script( 'sapo-hotels-admin' );

		$countries = array();
		foreach ( self::sapo_countries() as $code => $c ) {
			$countries[ $code ] = $c['fr'];
		}
		$cantons = array();
		foreach ( self::sapo_cantons() as $code => $n ) {
			$cantons[ $code ] = $n[0];
		}
		$backup = get_option( self::OPT_BACKUP );
		$data   = array(
			'ajax'      => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( self::NONCE ),
			'export'    => wp_nonce_url( admin_url( 'admin-post.php?action=sapo_hotels_export' ), 'sapo_hotels_export' ),
			'template'  => wp_nonce_url( admin_url( 'admin-post.php?action=sapo_hotels_export&template=1' ), 'sapo_hotels_export' ),
			'hotels'    => self::sapo_get_hotels(),
			'settings'  => self::sapo_get_settings(),
			'countries' => $countries,
			'cantons'   => $cantons,
			'colLabels' => array_combine( array_keys( self::MAP_COUNTRIES ), array_map( array( __CLASS__, 'sapo_column_labels' ), array_keys( self::MAP_COUNTRIES ) ) ),
			'mapCountries' => array_keys( self::MAP_COUNTRIES ),
			'imports'   => array_map( function ( $t ) { return wp_date( 'd.m.Y à H:i', $t ); }, (array) get_option( self::OPT_IMPORTS, array() ) ),
			'icons'     => array(
				'green' => self::sapo_smiley_data_uri( 'green' ),
			),
			'backup'    => ( is_array( $backup ) && ! empty( $backup['time'] ) ) ? wp_date( 'd.m.Y à H:i', $backup['time'] ) : '',
			'updated'   => get_option( self::OPT_UPDATED ) ? wp_date( 'd.m.Y à H:i', get_option( self::OPT_UPDATED ) ) : '',
		);
		wp_add_inline_script( 'sapo-hotels-admin', 'window.SAPO_HOTELS=' . wp_json_encode( $data, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE ) . ';', 'before' );
		wp_add_inline_script( 'sapo-hotels-admin', self::sapo_admin_js() );
	}

	public static function sapo_render_admin() {
		if ( ! current_user_can( self::sapo_capability() ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'sapo-hotels' ) );
		}
		$premium = self::sapo_premium_svg( 16 );
		?>
		<div class="wrap sapo-a" id="sapo-a">
			<h1 class="sapo-a__sr">Carte des hôtels SapoCycle</h1>
			<header class="sapo-a__head">
				<div class="sapo-a__brand">
					<span class="sapo-a__logo"><?php echo self::sapo_smiley_svg( 'green', 26, 43 ); // phpcs:ignore ?></span>
					<div>
						<p class="sapo-a__title">Carte des hôtels</p>
						<p class="sapo-a__sub">Gérez les hôtels membres affichés sur la carte, en français, allemand et anglais. <span data-updated></span></p>
					</div>
				</div>
				<div class="sapo-a__head-actions">
					<button type="button" class="sapo-btn sapo-btn--ghost" data-goto-import><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4m0 0-4 4m4-4 4 4M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Importer un fichier Excel</button>
					<button type="button" class="sapo-btn sapo-btn--primary" data-add><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>Ajouter un hôtel</button>
				</div>
			</header>

			<section class="sapo-a__stats" aria-label="Statistiques">
				<div class="sapo-stat"><span class="sapo-stat__n" data-stat="total">0</span><span class="sapo-stat__l">Hôtels</span></div>
				<div class="sapo-stat"><span class="sapo-stat__n" data-stat="visible">0</span><span class="sapo-stat__l">Visibles sur la carte</span></div>
				<div class="sapo-stat"><span class="sapo-stat__n" data-stat="premium">0</span><span class="sapo-stat__l"><?php echo $premium; // phpcs:ignore ?> Premium donateurs</span></div>
				<div class="sapo-stat"><span class="sapo-stat__n" data-stat="countries">0</span><span class="sapo-stat__l">Pays</span></div>
				<div class="sapo-stat sapo-stat--warn" data-stat-box="nogeo"><span class="sapo-stat__n" data-stat="nogeo">0</span><span class="sapo-stat__l">Sans coordonnées</span><button type="button" class="sapo-link" data-geocode-all>Géolocaliser</button></div>
			</section>

			<nav class="sapo-a__tabs" role="tablist" aria-label="Sections">
				<button type="button" role="tab" class="sapo-a__tab" aria-selected="true" data-tab="hotels">Hôtels</button>
				<button type="button" role="tab" class="sapo-a__tab" aria-selected="false" data-tab="import">Import Excel</button>
				<button type="button" role="tab" class="sapo-a__tab" aria-selected="false" data-tab="settings">Intégration et réglages</button>
			</nav>

			<!-- ============ HÔTELS ============ -->
			<section class="sapo-a__panel" data-panel="hotels">
				<div class="sapo-a__toolbar">
					<div class="sapo-field sapo-field--search">
						<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.6-3.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						<label class="sapo-a__sr" for="sapo-q">Rechercher</label>
						<input id="sapo-q" type="search" placeholder="Rechercher par nom, ville, NPA, canton…" data-q>
					</div>
					<label class="sapo-a__sr" for="sapo-fc">Pays</label>
					<select id="sapo-fc" data-fcountry><option value="">Tous les pays</option></select>
					<label class="sapo-a__sr" for="sapo-fs">Filtre</label>
					<select id="sapo-fs" data-fstatus>
						<option value="">Tous les hôtels</option>
						<option value="premium">Premium donateurs</option>
						<option value="hidden">Masqués de la carte</option>
						<option value="nogeo">Sans coordonnées</option>
						<option value="incomplete">Infos incomplètes</option>
					</select>
					<span class="sapo-a__count" data-count></span>
				</div>

				<div class="sapo-bulk" data-bulk hidden>
					<span data-bulk-n></span>
					<button type="button" class="sapo-btn sapo-btn--small" data-bulk-act="show">Afficher sur la carte</button>
					<button type="button" class="sapo-btn sapo-btn--small" data-bulk-act="hide">Masquer</button>
					<button type="button" class="sapo-btn sapo-btn--small sapo-btn--danger" data-bulk-act="delete">Supprimer</button>
				</div>

				<div class="sapo-table-wrap">
					<table class="sapo-table">
						<thead>
							<tr>
								<th class="sapo-col-check"><input type="checkbox" data-check-all aria-label="Tout sélectionner"></th>
								<th class="sapo-col-icon"><span class="sapo-a__sr">Smiley</span></th>
								<th><button type="button" class="sapo-sort" data-sort="name">Hôtel</button></th>
								<th>Adresse</th>
								<th><button type="button" class="sapo-sort" data-sort="city">NPA et ville</button></th>
								<th><button type="button" class="sapo-sort" data-sort="canton">Canton</button></th>
								<th>Site internet</th>
								<th class="sapo-col-status">Carte</th>
								<th class="sapo-col-actions"><span class="sapo-a__sr">Actions</span></th>
							</tr>
						</thead>
						<tbody data-rows></tbody>
					</table>
					<div class="sapo-empty" data-empty hidden>
						<p><strong>Aucun hôtel trouvé.</strong></p>
						<p>Modifiez votre recherche, ajoutez un hôtel ou importez un fichier Excel.</p>
					</div>
				</div>
			</section>

			<!-- ============ IMPORT ============ -->
			<section class="sapo-a__panel" data-panel="import" hidden>
				<div class="sapo-country-switch" role="tablist" aria-label="Pays du fichier Excel">
					<?php
					$flags = array( 'CH' => '<svg width="20" height="20" viewBox="0 0 32 32" aria-hidden="true"><rect width="32" height="32" rx="6" fill="#DA291C"/><path d="M13 7h6v6h6v6h-6v6h-6v-6H7v-6h6z" fill="#fff"/></svg>', 'FR' => '<svg width="20" height="20" viewBox="0 0 32 32" aria-hidden="true"><clipPath id="sfr"><rect width="32" height="32" rx="6"/></clipPath><g clip-path="url(#sfr)"><path fill="#002654" d="M0 0h11v32H0z"/><path fill="#fff" d="M11 0h10v32H11z"/><path fill="#CE1126" d="M21 0h11v32H21z"/></g></svg>', 'DE' => '<svg width="20" height="20" viewBox="0 0 32 32" aria-hidden="true"><clipPath id="sde"><rect width="32" height="32" rx="6"/></clipPath><g clip-path="url(#sde)"><path fill="#000" d="M0 0h32v11H0z"/><path fill="#DD0000" d="M0 11h32v10H0z"/><path fill="#FFCE00" d="M0 21h32v11H0z"/></g></svg>' );
					$first = true;
					foreach ( self::MAP_COUNTRIES as $code => $slug ) :
						?>
						<button type="button" role="tab" class="sapo-country" data-imp-country="<?php echo esc_attr( $code ); ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>">
							<?php echo $flags[ $code ]; // phpcs:ignore ?>
							<span class="sapo-country__t"><strong><?php echo esc_html( self::sapo_country_name( $code, 'fr' ) ); ?></strong><small data-imp-meta="<?php echo esc_attr( $code ); ?>"></small></span>
						</button>
						<?php
						$first = false;
					endforeach;
					?>
				</div>
				<div class="sapo-grid">
					<div class="sapo-card">
						<h2 data-imp-title>Hôtels en Suisse</h2>
						<p class="sapo-muted" data-imp-sub></p>
						<form data-import-form>
							<label class="sapo-drop" data-drop>
								<input type="file" accept=".xlsx,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv" data-file>
								<svg width="40" height="40" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4m0 0-4 4m4-4 4 4M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
								<span class="sapo-drop__t" data-drop-text></span>
								<span class="sapo-drop__s">Formats acceptés : .xlsx ou .csv</span>
							</label>

							<fieldset class="sapo-modes">
								<legend>Mode d'import</legend>
								<label class="sapo-mode">
									<input type="radio" name="mode" value="replace" checked>
									<span><strong>Remplacer la liste</strong> (recommandé)<br><small data-mode-replace></small></span>
								</label>
								<label class="sapo-mode">
									<input type="radio" name="mode" value="merge">
									<span><strong>Fusionner</strong><br><small>Met à jour les hôtels existants (même nom et code postal) et ajoute les nouveaux. Rien n'est supprimé.</small></span>
								</label>
							</fieldset>
							<fieldset class="sapo-colmap">
								<legend>Colonnes lues dans le fichier Excel</legend>
								<div class="sapo-colmap__grid">
									<?php foreach ( self::sapo_column_labels( 'CH' ) as $field => $label ) : ?>
										<label class="sapo-colmap__item"><span data-col-label="<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $label ); ?></span><input name="<?php echo esc_attr( 'col_' . $field ); ?>" maxlength="3" autocomplete="off" spellcheck="false" aria-label="<?php echo esc_attr( 'Lettre de colonne : ' . $label ); ?>"></label>
									<?php endforeach; ?>
								</div>
								<label class="sapo-check"><input type="checkbox" name="header_row" value="1"> La ligne 1 contient les titres des colonnes (elle est ignorée)</label>
							</fieldset>
							<label class="sapo-check"><input type="checkbox" name="geocode" value="1" checked> Géolocaliser automatiquement les adresses (latitude et longitude)</label>
							<div class="sapo-actions">
								<button type="submit" class="sapo-btn sapo-btn--primary" data-import-btn disabled>Importer le fichier</button>
								<a class="sapo-btn sapo-btn--ghost" data-export href="#"><svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span data-export-label>Exporter la liste actuelle</span></a>
								<button type="button" class="sapo-btn sapo-btn--ghost" data-restore hidden>Annuler le dernier import</button>
							</div>
						</form>
						<div class="sapo-result" data-result hidden></div>
						<div class="sapo-progress" data-progress hidden>
							<div class="sapo-progress__top"><span data-progress-label>Géolocalisation…</span><span data-progress-n></span></div>
							<div class="sapo-progress__bar"><span data-progress-bar></span></div>
							<p class="sapo-muted sapo-small">Les adresses sont localisées une par une (environ 1 seconde par hôtel). Gardez cette page ouverte.</p>
						</div>
					</div>

					<div class="sapo-card sapo-card--soft">
						<h2>Format du fichier</h2>
						<p class="sapo-muted">Chaque pays a son propre fichier Excel. L'import lit uniquement les colonnes indiquées à gauche (par défaut E, I, J, K, L et M) ; les autres colonnes sont ignorées. Seule la première feuille du classeur est lue.</p>
						<p><a class="sapo-btn sapo-btn--ghost sapo-btn--small" data-template href="#"><svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Télécharger le modèle Excel</a></p>
						<table class="sapo-doc">
							<thead><tr><th>Colonne</th><th>Contenu</th></tr></thead>
							<tbody data-coldoc></tbody>
						</table>
						<ul class="sapo-notes">
							<li>Importer un pays ne modifie jamais les hôtels des deux autres pays.</li>
							<li>Le nom est obligatoire : une ligne sans nom est ignorée.</li>
							<li>Un nom qui commence par ✳️ marque l'hôtel comme <?php echo $premium; // phpcs:ignore ?> Premium donateur.</li>
							<li>Suisse : canton en code (ZH, VD…) ou en toutes lettres. France et Allemagne : région libre (Île-de-France, Bayern…).</li>
							<li>Les coordonnées d'un hôtel déjà connu (même nom, code postal et adresse) sont conservées. Les autres adresses sont géolocalisées.</li>
						</ul>
					</div>
				</div>
			</section>

			<!-- ============ RÉGLAGES ============ -->
			<section class="sapo-a__panel" data-panel="settings" hidden>
				<div class="sapo-grid">
					<div class="sapo-card sapo-card--wide">
						<h2>Intégrer les cartes</h2>
						<p class="sapo-muted">Un shortcode par pays et par langue. Collez-le dans la page Hôtels correspondante (module Texte ou Code de Divi).</p>
						<div class="sapo-sc">
							<div class="sapo-sc__h"></div><div class="sapo-sc__h">Français</div><div class="sapo-sc__h">Deutsch</div><div class="sapo-sc__h">English</div>
							<?php foreach ( self::MAP_COUNTRIES as $code => $slug ) : ?>
								<div class="sapo-sc__k"><?php echo $flags[ $code ]; // phpcs:ignore ?><?php echo esc_html( self::sapo_country_name( $code, 'fr' ) ); ?></div>
								<?php foreach ( array( 'fr', 'de', 'en' ) as $l ) : $tag = '[sapocycle_' . $slug . '_' . $l . ']'; ?>
									<button type="button" class="sapo-sc__b" data-copy="<?php echo esc_attr( $tag ); ?>" title="Copier"><code><?php echo esc_html( $tag ); ?></code><svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3" fill="none" stroke="currentColor" stroke-width="2"/></svg></button>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</div>
						<p class="sapo-muted sapo-small">Cliquez sur un shortcode pour le copier. Options possibles : <code>height="620"</code> hauteur en pixels, <code>search="no"</code> masquer la recherche, <code>legend="no"</code> masquer la légende. Exemple : <code>[sapocycle_suisse_fr height="700"]</code>.</p>
					</div>

					<form class="sapo-card" data-settings-form>
						<h2>Réglages de la carte</h2>
						<div class="sapo-row">
							<label class="sapo-label" for="sapo-tiles">Style du fond de carte</label>
							<select id="sapo-tiles" name="tiles">
								<option value="soft">OpenStreetMap adouci (recommandé)</option>
								<option value="osm">OpenStreetMap standard</option>
								<option value="custom">Fournisseur personnalisé (MapTiler, Stadia, CARTO…)</option>
							</select>
						</div>
						<div class="sapo-row" data-custom-tiles>
							<label class="sapo-label" for="sapo-tiles-url">URL des tuiles</label>
							<input id="sapo-tiles-url" name="tiles_url" placeholder="https://api.maptiler.com/maps/pastel/{z}/{x}/{y}.png?key=VOTRE_CLE">
							<label class="sapo-label" for="sapo-tiles-attr" style="margin-top:10px">Mention de copyright</label>
							<input id="sapo-tiles-attr" name="tiles_attr" placeholder="&copy; MapTiler &copy; OpenStreetMap">
						</div>
						<div class="sapo-row sapo-row--2">
							<div>
								<label class="sapo-label" for="sapo-height">Hauteur sur ordinateur (px)</label>
								<input id="sapo-height" type="number" name="height" min="320" max="1200" step="10">
							</div>
							<div>
								<span class="sapo-label">Regroupement des smileys</span>
								<label class="sapo-switch"><input type="checkbox" name="cluster" value="1"><span></span>Regrouper les hôtels proches</label>
							</div>
						</div>
						<h3>Textes de la légende</h3>
						<p class="sapo-muted sapo-small">Laissez un champ vide pour masquer cette ligne de la légende.</p>
						<div class="sapo-legend-grid">
							<?php
							$legend_rows = array(
								'legend_member'  => array( '<span class="sapo-inline-pin" data-pin="green"></span>Smiley vert', 'Smiley vert' ),
								'legend_premium' => array( $premium . 'Premium donateur', 'Premium' ),
							);
							foreach ( $legend_rows as $key => $lr ) :
								?>
								<div class="sapo-legend-item">
									<div class="sapo-legend-k"><?php echo $lr[0]; // phpcs:ignore ?></div>
									<?php foreach ( array( 'fr' => 'FR', 'de' => 'DE', 'en' => 'EN' ) as $l => $ll ) : ?>
										<label class="sapo-legend-in"><span><?php echo esc_html( $ll ); ?></span><input name="<?php echo esc_attr( $key . '[' . $l . ']' ); ?>" aria-label="<?php echo esc_attr( $lr[1] . ' ' . $ll ); ?>"></label>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="sapo-actions"><button type="submit" class="sapo-btn sapo-btn--primary">Enregistrer les réglages</button></div>
					</form>
				</div>
			</section>

			<!-- ============ TIROIR D'ÉDITION ============ -->
			<div class="sapo-drawer" data-drawer hidden>
				<div class="sapo-drawer__overlay" data-close></div>
				<form class="sapo-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="sapo-drawer-title" data-edit-form novalidate>
					<header class="sapo-drawer__head">
						<h2 id="sapo-drawer-title" data-drawer-title>Modifier l'hôtel</h2>
						<button type="button" class="sapo-icon-btn" data-close aria-label="Fermer"><svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
					</header>
					<div class="sapo-drawer__body">
						<input type="hidden" name="id">
						<div class="sapo-row">
							<label class="sapo-label" for="sapo-e-name">Nom de l'hôtel <span class="sapo-req">obligatoire</span></label>
							<input id="sapo-e-name" name="name" required autocomplete="off">
							<p class="sapo-error" data-err="name" hidden>Le nom est obligatoire.</p>
						</div>
						<div class="sapo-row">
							<label class="sapo-label" for="sapo-e-address">Adresse</label>
							<input id="sapo-e-address" name="address" autocomplete="off">
						</div>
						<div class="sapo-row sapo-row--zip">
							<div><label class="sapo-label" for="sapo-e-zip">NPA</label><input id="sapo-e-zip" name="zip" inputmode="numeric" autocomplete="off"></div>
							<div><label class="sapo-label" for="sapo-e-city">Ville</label><input id="sapo-e-city" name="city" autocomplete="off"></div>
						</div>
						<div class="sapo-row sapo-row--2">
							<div><label class="sapo-label" for="sapo-e-country">Pays</label><select id="sapo-e-country" name="country"></select></div>
							<div>
								<label class="sapo-label" for="sapo-e-canton" data-canton-label>Canton</label>
								<select id="sapo-e-canton" name="canton_select"></select>
								<input id="sapo-e-canton-txt" name="canton_text" hidden autocomplete="off" aria-label="Région">
							</div>
						</div>
						<div class="sapo-row">
							<label class="sapo-label" for="sapo-e-website">Site internet</label>
							<div class="sapo-input-action"><input id="sapo-e-website" name="website" type="url" placeholder="https://" autocomplete="off"><a href="#" target="_blank" rel="noopener" class="sapo-icon-btn" data-open-web aria-label="Ouvrir le site"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a></div>
						</div>

												<div class="sapo-row sapo-row--2">
							<label class="sapo-switch"><input type="checkbox" name="premium" value="1"><span></span><?php echo $premium; // phpcs:ignore ?> Premium donateur</label>
							<label class="sapo-switch"><input type="checkbox" name="active" value="1"><span></span>Visible sur la carte</label>
						</div>

						<div class="sapo-row">
							<div class="sapo-label-line"><span class="sapo-label">Position sur la carte</span><button type="button" class="sapo-link" data-locate>Localiser depuis l'adresse</button></div>
							<div class="sapo-minimap" data-minimap></div>
							<p class="sapo-muted sapo-small">Faites glisser le smiley pour ajuster la position, ou cliquez sur la carte.</p>
							<div class="sapo-row--2 sapo-coords">
								<div><label class="sapo-label" for="sapo-e-lat">Latitude</label><input id="sapo-e-lat" name="lat" inputmode="decimal" autocomplete="off"></div>
								<div><label class="sapo-label" for="sapo-e-lng">Longitude</label><input id="sapo-e-lng" name="lng" inputmode="decimal" autocomplete="off"></div>
							</div>
						</div>
					</div>
					<footer class="sapo-drawer__foot">
						<button type="button" class="sapo-btn sapo-btn--danger-ghost" data-delete>Supprimer</button>
						<span class="sapo-spacer"></span>
						<button type="button" class="sapo-btn sapo-btn--ghost" data-close>Annuler</button>
						<button type="submit" class="sapo-btn sapo-btn--primary" data-save>Enregistrer</button>
					</footer>
				</form>
			</div>

			<div class="sapo-toasts" aria-live="polite" data-toasts></div>
		</div>
		<?php
	}

	/* =====================================================================
	 * Administration : AJAX
	 * ===================================================================== */

	private static function sapo_check_ajax() {
		if ( ! current_user_can( self::sapo_capability() ) || ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'Session expirée ou droits insuffisants. Rechargez la page.' ), 403 );
		}
	}

	public static function sapo_ajax_save() {
		self::sapo_check_ajax();
		$raw = isset( $_POST['hotel'] ) ? json_decode( wp_unslash( $_POST['hotel'] ), true ) : null; // phpcs:ignore
		if ( ! is_array( $raw ) ) {
			wp_send_json_error( array( 'message' => 'Données invalides.' ) );
		}
		$hotels = self::sapo_get_hotels();
		$found  = false;
		foreach ( $hotels as $i => $h ) {
			if ( ! empty( $raw['id'] ) && $h['id'] === $raw['id'] ) {
				$hotels[ $i ] = self::sapo_sanitize_hotel( $raw, $h );
				$saved        = $hotels[ $i ];
				$found        = true;
				break;
			}
		}
		if ( ! $found ) {
			unset( $raw['id'] );
			$saved    = self::sapo_sanitize_hotel( $raw );
			$hotels[] = $saved;
		}
		if ( '' === $saved['name'] ) {
			wp_send_json_error( array( 'message' => 'Le nom de l\'hôtel est obligatoire.' ) );
		}
		self::sapo_save_hotels( $hotels );
		wp_send_json_success( array( 'hotel' => $saved, 'hotels' => self::sapo_get_hotels() ) );
	}

	public static function sapo_ajax_delete() {
		self::sapo_check_ajax();
		$ids = isset( $_POST['ids'] ) ? array_map( 'sanitize_text_field', (array) json_decode( wp_unslash( $_POST['ids'] ), true ) ) : array(); // phpcs:ignore
		$act = isset( $_POST['act'] ) ? sanitize_key( $_POST['act'] ) : 'delete';
		$hotels = self::sapo_get_hotels();
		if ( 'delete' === $act ) {
			$hotels = array_values( array_filter( $hotels, function ( $h ) use ( $ids ) { return ! in_array( $h['id'], $ids, true ); } ) );
		} else {
			foreach ( $hotels as $i => $h ) {
				if ( in_array( $h['id'], $ids, true ) ) {
					$hotels[ $i ]['active'] = ( 'show' === $act );
				}
			}
		}
		self::sapo_save_hotels( $hotels );
		wp_send_json_success( array( 'hotels' => self::sapo_get_hotels() ) );
	}

	public static function sapo_ajax_restore() {
		self::sapo_check_ajax();
		$backup = get_option( self::OPT_BACKUP );
		if ( ! is_array( $backup ) || ! isset( $backup['data'] ) ) {
			wp_send_json_error( array( 'message' => 'Aucune sauvegarde disponible.' ) );
		}
		self::sapo_save_hotels( (array) $backup['data'] );
		delete_option( self::OPT_BACKUP );
		wp_send_json_success( array( 'hotels' => self::sapo_get_hotels() ) );
	}

	public static function sapo_ajax_settings() {
		self::sapo_check_ajax();
		$in  = isset( $_POST['settings'] ) ? json_decode( wp_unslash( $_POST['settings'] ), true ) : array(); // phpcs:ignore
		$def = self::sapo_default_settings();
		$out = array(
			'tiles'      => in_array( $in['tiles'] ?? '', array( 'soft', 'osm', 'custom' ), true ) ? $in['tiles'] : 'soft',
			'tiles_url'  => preg_match( '#^https://[^\s"<>]+\{z\}[^\s"<>]*$#', trim( (string) ( $in['tiles_url'] ?? '' ) ) ) ? trim( $in['tiles_url'] ) : '',
			'tiles_attr' => wp_kses( (string) ( $in['tiles_attr'] ?? '' ), array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) ),
			'height'  => max( 320, min( 1200, absint( $in['height'] ?? 620 ) ) ),
			'cluster' => empty( $in['cluster'] ) ? 0 : 1,
		);
		$current           = self::sapo_get_settings();
		$out['columns']    = $current['columns'];
		$out['header_row'] = $current['header_row'];
		foreach ( array( 'legend_member', 'legend_premium' ) as $k ) {
			foreach ( array( 'fr', 'de', 'en' ) as $l ) {
				$out[ $k ][ $l ] = sanitize_text_field( $in[ $k ][ $l ] ?? $def[ $k ][ $l ] );
			}
		}
		update_option( self::OPT_SETTINGS, $out, false );
		wp_send_json_success( array( 'settings' => self::sapo_get_settings() ) );
	}

	/** Géolocalise un hôtel via Nominatim (OpenStreetMap). */
	public static function sapo_ajax_geocode() {
		self::sapo_check_ajax();
		$id     = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		$preview = ! empty( $_POST['preview'] );
		if ( $preview ) {
			$h = self::sapo_sanitize_hotel(
				array(
					'name'    => wp_unslash( $_POST['name'] ?? '' ),    // phpcs:ignore
					'address' => wp_unslash( $_POST['address'] ?? '' ), // phpcs:ignore
					'zip'     => wp_unslash( $_POST['zip'] ?? '' ),     // phpcs:ignore
					'city'    => wp_unslash( $_POST['city'] ?? '' ),    // phpcs:ignore
					'country' => wp_unslash( $_POST['country'] ?? 'CH' ), // phpcs:ignore
				)
			);
			$pos = self::sapo_geocode( $h );
			if ( ! $pos ) {
				wp_send_json_error( array( 'message' => 'Adresse introuvable. Placez le smiley manuellement sur la carte.' ) );
			}
			wp_send_json_success( $pos );
		}

		$hotels = self::sapo_get_hotels();
		foreach ( $hotels as $i => $h ) {
			if ( $h['id'] !== $id ) {
				continue;
			}
			$pos = self::sapo_geocode( $h );
			if ( ! $pos ) {
				wp_send_json_error( array( 'message' => 'Adresse introuvable : ' . $h['name'], 'id' => $id ) );
			}
			$hotels[ $i ]['lat'] = $pos['lat'];
			$hotels[ $i ]['lng'] = $pos['lng'];
			self::sapo_save_hotels( $hotels );
			wp_send_json_success( array( 'hotel' => $hotels[ $i ], 'fast' => ! empty( $pos['fast'] ) ) );
		}
		wp_send_json_error( array( 'message' => 'Hôtel introuvable.' ) );
	}

	/** Coordonnées déjà connues dans WP Store Locator (ancienne carte du site), si l'extension est encore là. */
	private static function sapo_wpsl_position( array $h ) {
		static $index = null;
		if ( null === $index ) {
			$index = array();
			if ( post_type_exists( 'wpsl_stores' ) || get_posts( array( 'post_type' => 'wpsl_stores', 'numberposts' => 1, 'post_status' => 'any', 'fields' => 'ids', 'suppress_filters' => true ) ) ) {
				$ids = get_posts( array( 'post_type' => 'wpsl_stores', 'numberposts' => -1, 'post_status' => 'any', 'fields' => 'ids', 'suppress_filters' => true ) );
				foreach ( $ids as $id ) {
					$lat = get_post_meta( $id, 'wpsl_lat', true );
					$lng = get_post_meta( $id, 'wpsl_lng', true );
					if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
						continue;
					}
					$title = html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' );
					$title = preg_replace( '/^\s*\x{2733}\x{FE0F}?\s*/u', '', $title );
					$title = preg_replace( '/\s*\([A-Z]{2}(\s*[^)]*)?\)\s*$/u', '', $title );
					$pos   = array( 'lat' => round( (float) $lat, 6 ), 'lng' => round( (float) $lng, 6 ) );
					$index[ 'n:' . self::sapo_norm( $title ) ] = $pos;
					$addr = self::sapo_norm( get_post_meta( $id, 'wpsl_address', true ) . ' ' . get_post_meta( $id, 'wpsl_city', true ) );
					if ( '' !== $addr ) {
						$index[ 'a:' . $addr ] = $pos;
					}
				}
			}
		}
		$n = 'n:' . self::sapo_norm( $h['name'] );
		if ( isset( $index[ $n ] ) ) {
			return $index[ $n ];
		}
		$a = 'a:' . self::sapo_norm( $h['address'] . ' ' . $h['city'] );
		return isset( $index[ $a ] ) ? $index[ $a ] : null;
	}

	private static function sapo_geocode( array $h ) {
		$known = self::sapo_wpsl_position( $h );
		if ( $known ) {
			return $known + array( 'fast' => true );
		}
		$cc      = strtolower( $h['country'] ? $h['country'] : 'CH' );
		$queries = array();
		if ( $h['address'] && ( $h['zip'] || $h['city'] ) ) {
			$queries[] = array( 'street' => $h['address'], 'postalcode' => $h['zip'], 'city' => $h['city'] );
			$queries[] = array( 'q' => trim( $h['address'] . ', ' . $h['zip'] . ' ' . $h['city'] ) );
		}
		if ( $h['name'] && $h['city'] ) {
			$queries[] = array( 'q' => $h['name'] . ', ' . $h['city'] );
		}
		if ( $h['zip'] || $h['city'] ) {
			$queries[] = array( 'q' => trim( $h['zip'] . ' ' . $h['city'] ) );
		}
		foreach ( $queries as $n => $q ) {
			if ( $n > 0 ) {
				usleep( 1100000 ); // Politique d'usage Nominatim : 1 requête par seconde maximum.
			}
			$q   = array_filter( $q );
			$url = add_query_arg(
				array_merge(
					$q,
					array(
						'format'       => 'jsonv2',
						'limit'        => 1,
						'countrycodes' => $cc,
					)
				),
				'https://nominatim.openstreetmap.org/search'
			);
			$res = wp_remote_get(
				$url,
				array(
					'timeout'    => 12,
					'user-agent' => 'SapoCycle-Hotels-Map/' . self::VERSION . ' (' . home_url() . ')',
					'headers'    => array( 'Accept-Language' => 'fr,de,en' ),
				)
			);
			if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
				continue;
			}
			$body = json_decode( wp_remote_retrieve_body( $res ), true );
			if ( ! empty( $body[0]['lat'] ) ) {
				return array(
					'lat' => round( (float) $body[0]['lat'], 6 ),
					'lng' => round( (float) $body[0]['lon'], 6 ),
				);
			}
		}
		return null;
	}

	/* =====================================================================
	 * Import Excel / CSV
	 * ===================================================================== */

	public static function sapo_ajax_import() {
		self::sapo_check_ajax();
		if ( empty( $_FILES['file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) { // phpcs:ignore
			wp_send_json_error( array( 'message' => 'Aucun fichier reçu.' ) );
		}
		$name = sanitize_file_name( wp_unslash( $_FILES['file']['name'] ) ); // phpcs:ignore
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		$tmp  = $_FILES['file']['tmp_name']; // phpcs:ignore
		$mode    = ( isset( $_POST['mode'] ) && 'merge' === $_POST['mode'] ) ? 'merge' : 'replace';
		$country = self::sapo_map_country_code( isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '' );
		if ( '' === $country ) {
			wp_send_json_error( array( 'message' => 'Choisissez le pays du fichier (Suisse, France ou Allemagne).' ) );
		}

		// Enregistre la correspondance des colonnes de ce pays pour les prochains imports.
		$settings = self::sapo_get_settings();
		if ( isset( $_POST['columns'] ) ) {
			$settings['columns'][ $country ] = self::sapo_sanitize_columns( json_decode( wp_unslash( $_POST['columns'] ), true ) ); // phpcs:ignore
		}
		if ( isset( $_POST['header_row'] ) ) {
			$settings['header_row'][ $country ] = '1' === $_POST['header_row'] ? 1 : 0;
		}
		update_option( self::OPT_SETTINGS, $settings, false );

		try {
			if ( 'xlsx' === $ext ) {
				$rows = self::sapo_read_xlsx( $tmp );
			} elseif ( 'csv' === $ext || 'txt' === $ext ) {
				$rows = self::sapo_read_csv( $tmp );
			} elseif ( 'xls' === $ext ) {
				throw new Exception( 'Le format .xls (Excel 97-2003) n\'est pas pris en charge. Dans Excel : Fichier › Enregistrer sous › Classeur Excel (.xlsx).' );
			} else {
				throw new Exception( 'Format non reconnu. Utilisez un fichier .xlsx ou .csv.' );
			}
			$result = self::sapo_import_rows( $rows, $mode, $settings['columns'][ $country ], ! empty( $settings['header_row'][ $country ] ), $country );
			$result['country']     = $country;
			$result['columns_map'] = $settings['columns'][ $country ];
			$result['header_row']  = (int) $settings['header_row'][ $country ];
			$imports               = (array) get_option( self::OPT_IMPORTS, array() );
			$imports[ $country ]   = time();
			update_option( self::OPT_IMPORTS, $imports, false );
			$result['imported']    = wp_date( 'd.m.Y à H:i' );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
		wp_send_json_success( $result );
	}

	private static function sapo_import_rows( array $rows, $mode, $columns = null, $has_header = true, $country = 'CH' ) {
		$cols = array();
		foreach ( (array) ( $columns ? $columns : self::sapo_default_columns() ) as $field => $letter ) {
			$i = self::sapo_col_index( $letter );
			if ( null !== $i ) {
				$cols[ $field ] = $i;
			}
		}
		if ( ! isset( $cols['name'] ) ) {
			throw new Exception( 'Indiquez la lettre de la colonne contenant le nom de l\'hôtel.' );
		}
		$first_line = 1;
		if ( $has_header && $rows ) {
			array_shift( $rows );
			$first_line = 2;
		}
		// Sécurité : ignore une ligne de titres restée en tête (« Nom », « Name », « Hotel »…).
		if ( $rows ) {
			$probe = self::sapo_norm( $rows[0][ $cols['name'] ] ?? '' );
			if ( in_array( $probe, array( 'nom', 'name', 'hotel', 'hotels', 'nom de l hotel', 'hotelname', 'hotel name' ), true ) ) {
				array_shift( $rows );
				++$first_line;
			}
		}
		if ( ! $rows ) {
			throw new Exception( 'Le fichier ne contient aucune ligne d\'hôtel.' );
		}

		// Seuls les hôtels du pays importé sont concernés : les autres pays restent intacts.
		$all      = self::sapo_get_hotels();
		$others   = array_values( array_filter( $all, function ( $h ) use ( $country ) { return $h['country'] !== $country; } ) );
		$existing = array_values( array_filter( $all, function ( $h ) use ( $country ) { return $h['country'] === $country; } ) );
		$by_key   = array();
		foreach ( $existing as $h ) {
			$by_key[ self::sapo_hotel_key( $h ) ] = $h;
		}

		$warnings = array();
		$incoming = array();
		$seen     = array();
		foreach ( $rows as $idx => $r ) {
			$line = $idx + $first_line;
			$raw  = array();
			foreach ( $cols as $field => $i ) {
				$raw[ $field ] = isset( $r[ $i ] ) ? $r[ $i ] : '';
			}
			if ( '' === trim( (string) $raw['name'] ) ) {
				if ( array_filter( $r, function ( $v ) { return '' !== trim( (string) $v ); } ) ) {
					$warnings[] = 'Ligne ' . $line . ' ignorée : nom manquant.';
				}
				continue;
			}
			$h   = self::sapo_sanitize_hotel( $raw, array( 'country' => $country ) );
			$key = self::sapo_hotel_key( $h );
			if ( isset( $seen[ $key ] ) ) {
				$warnings[] = 'Ligne ' . $line . ' ignorée : « ' . $h['name'] . ' » apparaît deux fois dans le fichier.';
				continue;
			}
			$seen[ $key ] = true;
			if ( '' === $h['address'] && '' === $h['city'] ) {
				$warnings[] = 'Ligne ' . $line . ' : « ' . $h['name'] . ' » n\'a ni adresse ni ville.';
			}
			$incoming[ $key ] = array( 'hotel' => $h, 'raw' => $raw );
		}
		if ( ! $incoming ) {
			throw new Exception( 'Aucun hôtel trouvé dans la colonne ' . strtoupper( (string) ( $columns['name'] ?? 'E' ) ) . '. Vérifiez les lettres de colonnes.' );
		}

		$added   = 0;
		$updated = 0;
		$result  = array();

		foreach ( $incoming as $key => $item ) {
			$h = $item['hotel'];
			if ( isset( $by_key[ $key ] ) ) {
				$old = $by_key[ $key ];
				if ( 'merge' === $mode ) {
					// Seules les cellules remplies écrasent les valeurs existantes.
					$raw = array_filter( $item['raw'], function ( $v ) { return '' !== trim( (string) $v ); } );
					$h   = self::sapo_sanitize_hotel( $raw, $old );
				} else {
					$h['id']     = $old['id'];
					$h['active'] = $old['active']; // Le fichier ne gère pas la visibilité : on garde le choix fait dans l'admin.
				}
				// Conserve les coordonnées si l'adresse n'a pas changé et que le fichier n'en fournit pas.
				if ( null === $h['lat'] && null !== $old['lat'] && self::sapo_norm( $old['address'] . $old['zip'] . $old['city'] ) === self::sapo_norm( $h['address'] . $h['zip'] . $h['city'] ) ) {
					$h['lat'] = $old['lat'];
					$h['lng'] = $old['lng'];
				}
				++$updated;
			} else {
				++$added;
			}
			$result[ $key ] = $h;
		}

		$removed = 0;
		if ( 'merge' === $mode ) {
			foreach ( $existing as $old ) {
				$k = self::sapo_hotel_key( $old );
				if ( ! isset( $result[ $k ] ) ) {
					$result[ $k ] = $old;
				}
			}
		} else {
			foreach ( $existing as $old ) {
				if ( ! isset( $result[ self::sapo_hotel_key( $old ) ] ) ) {
					++$removed;
				}
			}
		}

		self::sapo_save_hotels( array_merge( $others, array_values( $result ) ), true );
		$hotels = self::sapo_get_hotels();
		$nogeo  = array_values(
			array_map(
				function ( $h ) { return $h['id']; },
				array_filter( $hotels, function ( $h ) use ( $country ) { return null === $h['lat'] && $h['country'] === $country; } )
			)
		);

		return array(
			'added'    => $added,
			'updated'  => $updated,
			'removed'  => $removed,
			'total'    => count( array_filter( $hotels, function ( $h ) use ( $country ) { return $h['country'] === $country; } ) ),
			'warnings' => array_slice( $warnings, 0, 50 ),
			'nogeo'    => $nogeo,
			'columns'  => array_keys( $cols ),
			'preview'  => array_slice( array_map( function ( $x ) { return $x['hotel']; }, array_values( $incoming ) ), 0, 3 ),
			'hotels'   => $hotels,
			'backup'   => wp_date( 'd.m.Y à H:i' ),
		);
	}

	/** Lecture d'un fichier .xlsx sans bibliothèque externe (première feuille). */
	public static function sapo_read_xlsx( $path ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			throw new Exception( 'L\'extension PHP ZipArchive est requise pour lire les fichiers .xlsx. Utilisez un fichier .csv ou contactez votre hébergeur.' );
		}
		$zip = new ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			throw new Exception( 'Impossible d\'ouvrir le fichier Excel. Est-ce bien un fichier .xlsx ?' );
		}
		$prev = libxml_use_internal_errors( true );

		$shared = array();
		$ss     = $zip->getFromName( 'xl/sharedStrings.xml' );
		if ( false !== $ss ) {
			$xml = simplexml_load_string( $ss, 'SimpleXMLElement', LIBXML_NONET | LIBXML_COMPACT );
			if ( $xml ) {
				foreach ( $xml->si as $si ) {
					$shared[] = self::sapo_xlsx_text( $si );
				}
			}
		}

		$sheet = 'xl/worksheets/sheet1.xml';
		$wb    = $zip->getFromName( 'xl/workbook.xml' );
		$rels  = $zip->getFromName( 'xl/_rels/workbook.xml.rels' );
		if ( $wb && $rels ) {
			$wbx = simplexml_load_string( $wb, 'SimpleXMLElement', LIBXML_NONET );
			$rx  = simplexml_load_string( $rels, 'SimpleXMLElement', LIBXML_NONET );
			if ( $wbx && $rx && isset( $wbx->sheets->sheet[0] ) ) {
				$first = $wbx->sheets->sheet[0];
				$rid   = '';
				foreach ( $first->attributes( 'r', true ) as $k => $v ) {
					if ( 'id' === $k ) {
						$rid = (string) $v;
					}
				}
				if ( '' === $rid ) {
					foreach ( $first->getNamespaces( true ) as $ns ) {
						$a = $first->attributes( $ns );
						if ( isset( $a['id'] ) ) {
							$rid = (string) $a['id'];
						}
					}
				}
				foreach ( $rx->Relationship as $r ) {
					if ( (string) $r['Id'] === $rid ) {
						$target = (string) $r['Target'];
						$sheet  = 0 === strpos( $target, '/' ) ? ltrim( $target, '/' ) : 'xl/' . preg_replace( '#^\./#', '', $target );
					}
				}
			}
		}
		$sx = $zip->getFromName( $sheet );
		$zip->close();
		if ( false === $sx ) {
			libxml_use_internal_errors( $prev );
			throw new Exception( 'Feuille de calcul introuvable dans le fichier.' );
		}
		$xml = simplexml_load_string( $sx, 'SimpleXMLElement', LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE );
		libxml_use_internal_errors( $prev );
		if ( ! $xml || ! isset( $xml->sheetData ) ) {
			throw new Exception( 'Feuille de calcul illisible.' );
		}

		$rows = array();
		$auto = 0;
		foreach ( $xml->sheetData->row as $row ) {
			$ri  = isset( $row['r'] ) ? (int) $row['r'] - 1 : $auto;
			$auto = $ri + 1;
			$cells = array();
			$ci    = 0;
			foreach ( $row->c as $c ) {
				if ( isset( $c['r'] ) && preg_match( '/^([A-Z]+)/', (string) $c['r'], $m ) ) {
					$ci = 0;
					foreach ( str_split( $m[1] ) as $ch ) {
						$ci = $ci * 26 + ( ord( $ch ) - 64 );
					}
					--$ci;
				}
				$type = (string) $c['t'];
				if ( 's' === $type ) {
					$v = $shared[ (int) $c->v ] ?? '';
				} elseif ( 'inlineStr' === $type ) {
					$v = self::sapo_xlsx_text( $c->is );
				} elseif ( 'b' === $type ) {
					$v = '1' === (string) $c->v ? 'oui' : 'non';
				} else {
					$v = (string) $c->v;
					if ( is_numeric( $v ) && false !== stripos( $v, 'E' ) ) {
						$v = rtrim( rtrim( sprintf( '%.10F', (float) $v ), '0' ), '.' );
					}
				}
				$cells[ $ci ] = trim( $v );
				++$ci;
			}
			if ( $cells ) {
				$max = max( array_keys( $cells ) );
				$line = array();
				for ( $i = 0; $i <= $max; $i++ ) {
					$line[] = $cells[ $i ] ?? '';
				}
				$rows[ $ri ] = $line;
			}
		}
		ksort( $rows );
		return array_values( $rows );
	}

	private static function sapo_xlsx_text( $node ) {
		if ( ! $node ) {
			return '';
		}
		if ( isset( $node->t ) ) {
			return (string) $node->t;
		}
		$s = '';
		if ( isset( $node->r ) ) {
			foreach ( $node->r as $run ) {
				$s .= (string) $run->t;
			}
		}
		return $s;
	}

	public static function sapo_read_csv( $path ) {
		$content = file_get_contents( $path ); // phpcs:ignore
		if ( false === $content || '' === trim( $content ) ) {
			throw new Exception( 'Le fichier est vide.' );
		}
		$content = preg_replace( '/^\xEF\xBB\xBF/', '', $content );
		if ( function_exists( 'mb_check_encoding' ) && ! mb_check_encoding( $content, 'UTF-8' ) ) {
			$content = mb_convert_encoding( $content, 'UTF-8', 'Windows-1252' );
		}
		$first = strtok( $content, "\n" );
		$delim = ';';
		$best  = 0;
		foreach ( array( ';', ',', "\t" ) as $d ) {
			$c = substr_count( $first, $d );
			if ( $c > $best ) {
				$best  = $c;
				$delim = $d;
			}
		}
		$fh = fopen( 'php://temp', 'r+' ); // phpcs:ignore
		fwrite( $fh, $content ); // phpcs:ignore
		rewind( $fh );
		$rows = array();
		while ( false !== ( $r = fgetcsv( $fh, 0, $delim, '"', '\\' ) ) ) {
			$rows[] = array_map( 'trim', $r );
		}
		fclose( $fh ); // phpcs:ignore
		return $rows;
	}

	/* =====================================================================
	 * Export Excel (.xlsx généré sans bibliothèque)
	 * ===================================================================== */

	public static function sapo_download_export() {
		if ( ! current_user_can( self::sapo_capability() ) || ! check_admin_referer( 'sapo_hotels_export' ) ) {
			wp_die( 'Accès refusé.' );
		}
		$template = ! empty( $_GET['template'] );
		$country  = self::sapo_map_country_code( isset( $_GET['country'] ) ? sanitize_text_field( wp_unslash( $_GET['country'] ) ) : 'CH' );
		$country  = $country ? $country : 'CH';
		$settings = self::sapo_get_settings();
		$map      = array();
		foreach ( $settings['columns'][ $country ] as $field => $letter ) {
			$i = self::sapo_col_index( $letter );
			if ( null !== $i ) {
				$map[ $field ] = $i;
			}
		}
		$width  = $map ? max( $map ) + 1 : 1;
		$labels = self::sapo_column_labels( $country );
		$line   = function ( $values ) use ( $map, $width ) {
			$row = array_fill( 0, $width, '' );
			foreach ( $map as $field => $i ) {
				$row[ $i ] = isset( $values[ $field ] ) ? $values[ $field ] : '';
			}
			return $row;
		};
		$rows = array();
		if ( ! empty( $settings['header_row'][ $country ] ) ) {
			$rows[] = $line( $labels );
		}
		$examples = array(
			'CH' => array( '✳️ Exemple Hôtel du Lac', 'Quai du Mont-Blanc 1', '1201', 'Genève', 'GE', 'https://www.exemple.ch' ),
			'FR' => array( '✳️ Exemple Hôtel de la Paix', '1 rue de la Paix', '75002', 'Paris', 'Île-de-France', 'https://www.exemple.fr' ),
			'DE' => array( '✳️ Beispiel Hotel am See', 'Seestraße 1', '10117', 'Berlin', 'Berlin', 'https://www.beispiel.de' ),
		);
		$ex     = $examples[ $country ];
		$hotels = $template ? array(
			self::sapo_sanitize_hotel(
				array(
					'name'    => $ex[0],
					'address' => $ex[1],
					'zip'     => $ex[2],
					'city'    => $ex[3],
					'canton'  => $ex[4],
					'website' => $ex[5],
				),
				array( 'country' => $country )
			),
		) : array_values( array_filter( self::sapo_get_hotels(), function ( $h ) use ( $country ) { return $h['country'] === $country; } ) );
		foreach ( $hotels as $h ) {
			$rows[] = $line(
				array(
					'name'    => ( empty( $h['premium'] ) ? '' : "\u{2733}\u{FE0F} " ) . $h['name'],
					'address' => $h['address'],
					'zip'     => $h['zip'],
					'city'    => $h['city'],
					'canton'  => $h['canton'],
					'website' => $h['website'],
				)
			);
		}
		$widths = array_fill( 0, $width, 4 );
		$size   = array( 'name' => 44, 'address' => 34, 'zip' => 9, 'city' => 22, 'canton' => 10, 'website' => 44 );
		foreach ( $map as $field => $i ) {
			$widths[ $i ] = $size[ $field ];
		}
		$file = self::sapo_build_xlsx( $rows, $widths, ! empty( $settings['header_row'][ $country ] ) );
		$slug = self::MAP_COUNTRIES[ $country ];
		$name = $template ? 'modele-hotels-sapocycle-' . $slug . '.xlsx' : 'hotels-sapocycle-' . $slug . '-' . wp_date( 'Y-m-d' ) . '.xlsx';
		nocache_headers();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );
		header( 'Content-Length: ' . filesize( $file ) );
		readfile( $file ); // phpcs:ignore
		@unlink( $file ); // phpcs:ignore
		exit;
	}

	public static function sapo_build_xlsx( array $rows, array $widths = array(), $has_header = true ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			wp_die( 'L\'extension PHP ZipArchive est requise pour générer le fichier Excel.' );
		}
		$x = function ( $s ) {
			return htmlspecialchars( (string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
		};
		$col = function ( $i ) {
			$s = '';
			for ( $i++; $i > 0; $i = intdiv( $i - 1, 26 ) ) {
				$s = chr( 65 + ( $i - 1 ) % 26 ) . $s;
			}
			return $s;
		};
		$last  = $col( max( 0, count( $rows[0] ) - 1 ) );
		$pane  = $has_header ? '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>' : '';
		$sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0">' . $pane . '</sheetView></sheetViews><cols>';
		foreach ( $widths as $i => $w ) {
			$sheet .= '<col min="' . ( $i + 1 ) . '" max="' . ( $i + 1 ) . '" width="' . (float) $w . '" customWidth="1"/>';
		}
		$sheet .= '</cols><sheetData>';
		foreach ( $rows as $r => $row ) {
			$sheet .= '<row r="' . ( $r + 1 ) . '">';
			foreach ( array_values( $row ) as $c => $v ) {
				$ref = $col( $c ) . ( $r + 1 );
				$st  = ( 0 === $r && $has_header ) ? ' s="1"' : '';
				if ( is_float( $v ) || is_int( $v ) ) {
					$sheet .= '<c r="' . $ref . '"' . $st . '><v>' . $v . '</v></c>';
				} elseif ( '' !== (string) $v ) {
					$sheet .= '<c r="' . $ref . '" t="inlineStr"' . $st . '><is><t xml:space="preserve">' . $x( $v ) . '</t></is></c>';
				}
			}
			$sheet .= '</row>';
		}
		$sheet .= '</sheetData>' . ( $has_header ? '<autoFilter ref="A1:' . $last . count( $rows ) . '"/>' : '' ) . '</worksheet>';

		$files = array(
			'[Content_Types].xml'        => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
			'_rels/.rels'                => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
			'xl/workbook.xml'            => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Hotels" sheetId="1" r:id="rId1"/></sheets><definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">Hotels!$A$1:$' . $last . '$' . count( $rows ) . '</definedName></definedNames></workbook>',
			'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
			'xl/styles.xml'              => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0069B4"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>',
			'xl/worksheets/sheet1.xml'   => $sheet,
		);
		$tmp = wp_tempnam( 'sapo-hotels.xlsx' );
		$zip = new ZipArchive();
		$zip->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		foreach ( $files as $p => $c ) {
			$zip->addFromString( $p, $c );
		}
		$zip->close();
		return $tmp;
	}

	/* =====================================================================
	 * Administration : CSS
	 * ===================================================================== */

	private static function sapo_admin_css() {
		return <<<'CSS'
.sapo-a{--b:#0069B4;--b2:#0175D6;--sky:#2EA3F2;--g:#A2BD2F;--gd:#5E7F12;--r:#C0392B;--t:#1F2328;--m:#5F6670;--line:#E2E5E9;--bg:#F6F7F9;--w:#fff;--rad:10px;max-width:1360px;margin:20px 20px 40px 0;color:var(--t);font-size:14px}
.sapo-a *{box-sizing:border-box}
.sapo-a__sr{position:absolute!important;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;margin:-1px;padding:0;border:0}
.sapo-a :focus-visible{outline:2px solid var(--sky);outline-offset:2px}
.sapo-a__head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;margin:0 0 20px}
.sapo-a__brand{display:flex;align-items:center;gap:16px}
.sapo-a__logo{display:flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:14px;background:#EEF5FB}
.sapo-a__title{margin:0;font-size:24px;font-weight:700;line-height:1.2;color:var(--b)}
.sapo-a__sub{margin:4px 0 0;color:var(--m);font-size:14px}
.sapo-a__head-actions{display:flex;flex-wrap:wrap;gap:8px}
.sapo-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:8px 16px;border:1px solid var(--line);border-radius:8px;background:var(--w);color:var(--t);font-size:14px;font-weight:600;line-height:1.2;text-decoration:none;cursor:pointer;transition:background-color .15s ease,border-color .15s ease,color .15s ease}
.sapo-btn:hover{border-color:#C8CDD3;background:var(--bg);color:var(--t)}
.sapo-btn:disabled{opacity:.5;cursor:not-allowed}
.sapo-btn--primary{background:var(--b);border-color:var(--b);color:#fff}
.sapo-btn--primary:hover{background:#005A9C;border-color:#005A9C;color:#fff}
.sapo-btn--ghost{background:var(--w)}
.sapo-btn--small{min-height:32px;padding:5px 12px;font-size:13px}
.sapo-btn--danger{color:var(--r);border-color:#F1C9C4}
.sapo-btn--danger:hover{background:#FDF1EF;color:var(--r);border-color:#E8A79F}
.sapo-btn--danger-ghost{border-color:transparent;background:transparent;color:var(--r)}
.sapo-btn--danger-ghost:hover{background:#FDF1EF;color:var(--r);border-color:transparent}
.sapo-btn.is-loading{position:relative;color:transparent!important;pointer-events:none}
.sapo-btn.is-loading::after{content:"";position:absolute;width:16px;height:16px;border:2px solid currentColor;border-color:#fff #fff transparent transparent;border-radius:50%;animation:sapo-spin .7s linear infinite}
.sapo-btn--ghost.is-loading::after{border-color:var(--b) var(--b) transparent transparent}
@keyframes sapo-spin{to{transform:rotate(360deg)}}
.sapo-link{padding:0;border:0;background:none;color:var(--b2);font-weight:600;font-size:13px;cursor:pointer;text-decoration:underline;text-underline-offset:2px}
.sapo-link:hover{color:var(--b)}
.sapo-icon-btn{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;padding:0;border:0;border-radius:8px;background:transparent;color:var(--m);cursor:pointer;text-decoration:none}
.sapo-icon-btn:hover{background:var(--bg);color:var(--t)}

.sapo-a__stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:0 0 20px}
.sapo-stat{display:flex;flex-direction:column;gap:2px;padding:16px 18px;border:1px solid var(--line);border-radius:var(--rad);background:var(--w)}
.sapo-stat__n{font-size:26px;font-weight:700;line-height:1.1;color:var(--t);font-variant-numeric:tabular-nums}
.sapo-stat__l{display:flex;align-items:center;gap:6px;color:var(--m);font-size:13px}
.sapo-stat--warn.is-on{border-color:#F3D19C;background:#FFF9EE}
.sapo-stat--warn.is-on .sapo-stat__n{color:#9A5B00}
.sapo-stat--warn .sapo-link{align-self:flex-start;margin-top:4px}
.sapo-stat--warn:not(.is-on) .sapo-link{display:none}

.sapo-a__tabs{display:flex;gap:4px;margin:0 0 16px;border-bottom:1px solid var(--line)}
.sapo-a__tab{position:relative;padding:12px 16px;border:0;background:none;color:var(--m);font-size:14px;font-weight:600;cursor:pointer}
.sapo-a__tab:hover{color:var(--t)}
.sapo-a__tab[aria-selected="true"]{color:var(--b)}
.sapo-a__tab[aria-selected="true"]::after{content:"";position:absolute;left:12px;right:12px;bottom:-1px;height:3px;border-radius:3px 3px 0 0;background:var(--b)}

.sapo-a__toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:0 0 12px}
.sapo-a input[type=search],.sapo-a input[type=text],.sapo-a input[type=url],.sapo-a input[type=number],.sapo-a input:not([type]),.sapo-a select{min-height:40px;padding:8px 12px;border:1px solid #D3D8DE;border-radius:8px;background:var(--w);color:var(--t);font-size:14px;line-height:1.4;box-shadow:none;max-width:none}
.sapo-a select{padding-right:32px}
.sapo-a input:focus,.sapo-a select:focus{border-color:var(--b);box-shadow:0 0 0 3px rgba(0,105,180,.14);outline:none}
.sapo-field--search{position:relative;flex:1 1 320px}
.sapo-field--search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--m)}
.sapo-field--search input{width:100%;padding-left:38px!important}
.sapo-a__count{margin-left:auto;color:var(--m);font-size:13px}
.sapo-bulk{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0 0 12px;padding:10px 14px;border-radius:var(--rad);background:#EEF5FB;color:var(--b);font-weight:600}
.sapo-bulk[hidden]{display:none}
.sapo-bulk span{margin-right:8px}

.sapo-table-wrap{border:1px solid var(--line);border-radius:var(--rad);background:var(--w);overflow:auto}
.sapo-table{width:100%;border-collapse:collapse}
.sapo-table th{position:sticky;top:0;z-index:1;padding:12px 12px;border-bottom:1px solid var(--line);background:#FAFBFC;color:var(--m);font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;text-align:left;white-space:nowrap}
.sapo-table td{padding:12px;border-bottom:1px solid #EEF0F2;vertical-align:middle}
.sapo-table tbody tr{cursor:pointer;transition:background-color .12s ease}
.sapo-table tbody tr:hover{background:#F7FAFD}
.sapo-table tbody tr.is-hidden td{opacity:.55}
.sapo-table tbody tr.is-hidden td.sapo-col-check,.sapo-table tbody tr.is-hidden td.sapo-col-actions,.sapo-table tbody tr.is-hidden td.sapo-col-status{opacity:1}
.sapo-table tbody tr:last-child td{border-bottom:0}
.sapo-sort{padding:0;border:0;background:none;color:inherit;font:inherit;letter-spacing:inherit;text-transform:inherit;cursor:pointer}
.sapo-sort::after{content:"";display:inline-block;margin-left:6px;border:4px solid transparent;border-top-color:#B5BBC3;vertical-align:-2px;opacity:.6}
.sapo-sort.is-asc::after{border-top-color:transparent;border-bottom-color:var(--b);vertical-align:2px;opacity:1}
.sapo-sort.is-desc::after{border-top-color:var(--b);opacity:1}
.sapo-col-check{width:36px}
.sapo-col-icon{width:28px}
.sapo-col-icon img{display:block}
.sapo-col-status{width:90px}
.sapo-col-actions{width:84px;text-align:right;white-space:nowrap}
.sapo-name{display:flex;align-items:center;gap:8px;font-weight:600;color:var(--t)}
.sapo-name svg{flex:0 0 auto}
.sapo-cell-muted{color:var(--m)}
.sapo-web{color:var(--b2);text-decoration:none;max-width:220px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle}
.sapo-web:hover{text-decoration:underline}
.sapo-missing{color:#B26A00;font-size:12px}
.sapo-tag{display:inline-flex;align-items:center;gap:6px;height:24px;padding:0 9px;border-radius:999px;font-size:12px;font-weight:600;white-space:nowrap}
.sapo-tag::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor}
.sapo-tag--ok{background:#EEF6E3;color:#4D6B0F}
.sapo-tag--off{background:#F1F2F4;color:#5F6670}
.sapo-tag--warn{background:#FFF3DF;color:#9A5B00}
.sapo-chip{display:inline-block;padding:2px 7px;border-radius:5px;background:var(--bg);font-size:12px;font-weight:600;color:var(--t)}
.sapo-empty{padding:48px 24px;text-align:center;color:var(--m)}
.sapo-empty p{margin:4px 0}

.sapo-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,1fr);gap:20px;align-items:start}
.sapo-card{padding:24px;border:1px solid var(--line);border-radius:var(--rad);background:var(--w)}
.sapo-card--soft{background:#FAFBFC}
.sapo-card h2{margin:0 0 6px;font-size:18px;color:var(--t)}
.sapo-card h3{margin:22px 0 4px;font-size:14px}
.sapo-muted{color:var(--m);margin:0 0 16px}
.sapo-small{font-size:12.5px}
.sapo-drop{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;min-height:170px;padding:24px;border:2px dashed #C9D3DE;border-radius:12px;background:#FAFCFE;color:var(--b);text-align:center;cursor:pointer;transition:border-color .15s ease,background-color .15s ease}
.sapo-drop:hover,.sapo-drop.is-over{border-color:var(--b);background:#EEF5FB}
.sapo-drop.has-file{border-style:solid;border-color:var(--g);background:#F6FAEC;color:var(--gd)}
.sapo-drop input{position:absolute;inset:0;opacity:0;cursor:pointer}
.sapo-drop__t{font-size:15px;font-weight:600;color:var(--t)}
.sapo-drop__s{font-size:12.5px;color:var(--m)}
.sapo-modes{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:16px 0 12px;padding:0;border:0}
.sapo-modes legend{margin:0 0 8px;font-weight:600}
.sapo-mode{display:flex;gap:10px;padding:12px 14px;border:1px solid var(--line);border-radius:10px;cursor:pointer;transition:border-color .15s ease,background-color .15s ease}
.sapo-mode:has(input:checked){border-color:var(--b);background:#F3F8FD}
.sapo-mode input{margin-top:3px}
.sapo-mode small{color:var(--m);font-size:12.5px;line-height:1.4}
.sapo-check{display:flex;align-items:center;gap:8px;margin:0 0 16px}
.sapo-country-switch{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:0 0 16px}
.sapo-country{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid var(--line);border-radius:var(--rad);background:#fff;text-align:left;cursor:pointer;transition:border-color .15s ease,background-color .15s ease,box-shadow .15s ease}
.sapo-country:hover{border-color:#C8CDD3}
.sapo-country[aria-selected="true"]{border-color:var(--b);background:#F3F8FD;box-shadow:inset 0 0 0 1px var(--b)}
.sapo-country svg{flex:0 0 auto;border-radius:6px}
.sapo-country__t{display:flex;flex-direction:column;gap:2px}
.sapo-country__t strong{font-size:15px;color:var(--t)}
.sapo-country__t small{font-size:12.5px;color:var(--m)}
.sapo-card--wide{grid-column:1/-1}
.sapo-sc{display:grid;grid-template-columns:150px repeat(3,minmax(0,1fr));gap:8px;align-items:center;margin:0 0 14px}
.sapo-sc__h{font-size:12px;font-weight:600;color:var(--m);text-transform:uppercase;letter-spacing:.04em}
.sapo-sc__k{display:flex;align-items:center;gap:8px;font-weight:600}
.sapo-sc__k svg{border-radius:4px}
.sapo-sc__b{display:flex;align-items:center;justify-content:space-between;gap:6px;min-height:40px;padding:8px 10px;border:1px solid var(--line);border-radius:8px;background:#FAFBFC;color:var(--m);cursor:pointer;transition:border-color .15s ease,background-color .15s ease}
.sapo-sc__b:hover{border-color:var(--b);background:#F3F8FD;color:var(--b)}
.sapo-sc__b code{padding:0;background:none;font-size:12px;font-weight:600;color:var(--b);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sapo-colmap{margin:16px 0;padding:14px 16px 2px;border:1px solid var(--line);border-radius:10px;background:#FAFBFC}
.sapo-colmap legend{padding:0 6px;font-weight:600}
.sapo-colmap__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px 12px;margin:0 0 12px}
.sapo-colmap__item{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 6px 6px 12px;border:1px solid var(--line);border-radius:8px;background:#fff;font-weight:600;font-size:13px}
.sapo-a .sapo-colmap__item input{width:52px;min-height:34px;padding:4px;text-align:center;font-weight:700;text-transform:uppercase;color:var(--b)}
.sapo-notes{margin:14px 0 0 18px;list-style:disc;color:var(--m);font-size:12.5px}
.sapo-notes li{margin:0 0 4px}
.sapo-notes svg{vertical-align:-3px}
.sapo-preview-wrap{overflow-x:auto}
.sapo-preview{width:100%;margin:10px 0 0;border-collapse:collapse;font-size:12.5px;background:#fff}
.sapo-preview th,.sapo-preview td{padding:6px 8px;border-bottom:1px solid var(--line);text-align:left}
.sapo-preview th{background:#EEF3F8;color:var(--m);font-size:11px;text-transform:uppercase;letter-spacing:.04em}
.sapo-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}
.sapo-result{margin-top:20px;padding:16px;border-radius:10px;background:#F3F8FD;border:1px solid #D4E6F6}
.sapo-result.is-error{background:#FDF1EF;border-color:#F1C9C4;color:#8E2A1E}
.sapo-result h3{margin:0 0 10px;font-size:15px}
.sapo-result__nums{display:flex;flex-wrap:wrap;gap:8px 20px;margin:0 0 8px}
.sapo-result__nums b{font-size:18px;margin-right:4px}
.sapo-result ul{margin:8px 0 0 18px;list-style:disc;color:#7A4B00;font-size:12.5px;max-height:160px;overflow:auto}
.sapo-progress{margin-top:16px}
.sapo-progress[hidden]{display:none}
.sapo-progress__top{display:flex;justify-content:space-between;margin-bottom:6px;font-weight:600}
.sapo-progress__bar{height:8px;border-radius:8px;background:#E7ECF1;overflow:hidden}
.sapo-progress__bar span{display:block;height:100%;width:0;border-radius:8px;background:var(--g);transition:width .3s ease}
.sapo-progress p{margin:8px 0 0}
.sapo-doc{width:100%;border-collapse:collapse;font-size:13px}
.sapo-doc th,.sapo-doc td{padding:8px 6px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}
.sapo-doc th{color:var(--m);font-size:12px;text-transform:uppercase;letter-spacing:.04em}
.sapo-doc code{padding:1px 6px;border-radius:4px;background:#EEF1F4;font-size:12px}
.sapo-doc svg{vertical-align:-3px}
.sapo-req{display:inline-block;margin-left:4px;padding:0 6px;border-radius:4px;background:#FDECEA;color:var(--r);font-size:11px;font-weight:600;vertical-align:1px}
.sapo-inline-pin{display:inline-block;width:11px;height:18px;background-size:contain;background-repeat:no-repeat;vertical-align:-4px;margin-right:4px}
.sapo-code{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 10px;padding:12px 14px;border:1px solid var(--line);border-radius:10px;background:#FAFBFC}
.sapo-code code{display:block;margin:0 0 2px;background:none;padding:0;font-size:14px;color:var(--b);font-weight:600}
.sapo-code span{color:var(--m);font-size:12.5px}
.sapo-row{margin:0 0 16px}
.sapo-row[hidden]{display:none}
.sapo-row--2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sapo-row--zip{display:grid;grid-template-columns:120px 1fr;gap:12px}
.sapo-row input:not([type=checkbox]):not([type=radio]),.sapo-row select{width:100%}
.sapo-label{display:block;margin:0 0 6px;font-weight:600;font-size:13px;color:var(--t)}
.sapo-label-line{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.sapo-label-line .sapo-label{margin:0}
.sapo-error{margin:6px 0 0;color:var(--r);font-size:12.5px}
.sapo-a .is-invalid{border-color:var(--r)!important}
.sapo-input-action{display:flex;gap:6px;align-items:center}
.sapo-input-action input{flex:1}
.sapo-switch{display:flex;align-items:center;gap:10px;min-height:40px;font-weight:600;cursor:pointer;user-select:none}
.sapo-switch input{position:absolute;opacity:0;width:1px;height:1px}
.sapo-switch > span:first-of-type{position:relative;flex:0 0 auto;width:40px;height:24px;border-radius:999px;background:#C9CFD6;transition:background-color .15s ease}
.sapo-switch > span:first-of-type::after{content:"";position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.2);transition:transform .15s ease}
.sapo-switch input:checked + span{background:var(--g)}
.sapo-switch input:checked + span::after{transform:translateX(16px)}
.sapo-switch input:focus-visible + span{outline:2px solid var(--sky);outline-offset:2px}
.sapo-seg{display:inline-flex;padding:4px;border-radius:10px;background:var(--bg);gap:4px}
.sapo-seg label{position:relative;cursor:pointer}
.sapo-seg input{position:absolute;opacity:0}
.sapo-seg span{display:inline-flex;align-items:center;gap:8px;min-height:36px;padding:6px 16px;border-radius:7px;font-weight:600;color:var(--m);transition:background-color .15s ease,color .15s ease}
.sapo-seg input:checked + span{background:#fff;color:var(--t);box-shadow:0 1px 2px rgba(0,0,0,.08)}
.sapo-seg input:focus-visible + span{outline:2px solid var(--sky)}
.sapo-legend-grid{display:grid;gap:14px}
.sapo-legend-item{display:grid;gap:6px;padding:14px;border:1px solid var(--line);border-radius:10px;background:#FAFBFC}
.sapo-legend-k{display:flex;align-items:center;gap:6px;margin-bottom:2px;font-weight:600;font-size:13px}
.sapo-legend-in{display:flex;align-items:center;gap:8px}
.sapo-legend-in span{flex:0 0 30px;font-size:11px;font-weight:700;color:var(--m);letter-spacing:.06em}
.sapo-legend-in input{flex:1;width:100%}

.sapo-drawer{position:fixed;inset:0;z-index:100000}
.sapo-drawer[hidden]{display:none}
.sapo-drawer__overlay{position:absolute;inset:0;background:rgba(17,24,39,.35);animation:sapo-fade .2s ease}
.sapo-drawer__panel{position:absolute;top:0;right:0;bottom:0;display:flex;flex-direction:column;width:min(520px,100%);background:#fff;box-shadow:-12px 0 32px rgba(0,0,0,.12);animation:sapo-slide .25s cubic-bezier(.2,.8,.2,1)}
@keyframes sapo-fade{from{opacity:0}}
@keyframes sapo-slide{from{transform:translateX(32px);opacity:0}}
.sapo-drawer__head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid var(--line)}
.sapo-drawer__head h2{margin:0;font-size:18px}
.sapo-drawer__body{flex:1;overflow-y:auto;padding:20px 24px 8px}
.sapo-drawer__foot{display:flex;align-items:center;gap:8px;padding:14px 24px;border-top:1px solid var(--line);background:#FAFBFC}
.sapo-spacer{flex:1}
.sapo-minimap{height:220px;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:#E9EEF2}
.sapo-coords{margin-top:10px}
.sapo-toasts{position:fixed;right:24px;bottom:24px;z-index:100001;display:flex;flex-direction:column;gap:8px}
.sapo-toast{display:flex;align-items:center;gap:10px;min-width:260px;max-width:420px;padding:12px 16px;border-radius:10px;background:#1F2328;color:#fff;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,.18);animation:sapo-slide .2s ease}
.sapo-toast--ok::before{content:"";width:8px;height:8px;border-radius:50%;background:var(--g)}
.sapo-toast--err{background:#8E2A1E}
@media (max-width:900px){.sapo-sc{grid-template-columns:1fr}.sapo-sc__h{display:none}.sapo-sc__k{margin-top:8px}}
@media (max-width:1100px){.sapo-a__stats{grid-template-columns:repeat(3,minmax(0,1fr))}.sapo-grid{grid-template-columns:1fr}}
@media (max-width:782px){.sapo-country-switch{grid-template-columns:1fr}.sapo-colmap__grid{grid-template-columns:repeat(2,minmax(0,1fr))}.sapo-a{margin-right:10px}.sapo-a__stats{grid-template-columns:repeat(2,minmax(0,1fr))}.sapo-modes{grid-template-columns:1fr}.sapo-table th:nth-child(4),.sapo-table td:nth-child(4),.sapo-table th:nth-child(7),.sapo-table td:nth-child(7){display:none}}
@media (prefers-reduced-motion:reduce){.sapo-a *{animation:none!important;transition:none!important}}
CSS;
	}

	/* =====================================================================
	 * Administration : JS
	 * ===================================================================== */

	private static function sapo_admin_js() {
		return <<<'JS'
(function(){
"use strict";
var D=window.SAPO_HOTELS;if(!D)return;
var root=document.getElementById("sapo-a");if(!root)return;
var $=function(s,c){return(c||root).querySelector(s);},$$=function(s,c){return[].slice.call((c||root).querySelectorAll(s));};
var hotels=D.hotels||[],sortKey="name",sortDir=1,selected={};
function esc(s){return String(s==null?"":s).replace(/[&<>"']/g,function(c){return{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c];});}
function norm(s){return String(s||"").normalize("NFD").replace(/[\u0300-\u036f]/g,"").toLowerCase();}
function host(u){try{return new URL(u).hostname.replace(/^www\./,"");}catch(e){return u;}}
var PREMIUM='<svg width="16" height="16" viewBox="0 0 24 24" aria-label="Premium"><rect width="24" height="24" rx="5" fill="#8DB33A"/><g stroke="#fff" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14M7.05 7.05l9.9 9.9M16.95 7.05l-9.9 9.9"/></g></svg>';
var PIN=function(c,w){w=w||16;return'<img src="'+D.icons.green+'" alt="" width="'+w+'" height="'+Math.round(w*69/42)+'">';};

$$("[data-pin]").forEach(function(el){el.style.backgroundImage="url('"+D.icons[el.getAttribute("data-pin")]+"')";});
$$("[data-pin-img]").forEach(function(el){el.src=D.icons[el.getAttribute("data-pin-img")];});
$("[data-goto-import]").addEventListener("click",function(){$('.sapo-a__tab[data-tab="import"]').click();});

function toast(msg,err){var el=document.createElement("div");el.className="sapo-toast "+(err?"sapo-toast--err":"sapo-toast--ok");el.textContent=msg;$("[data-toasts]").appendChild(el);setTimeout(function(){el.remove();},err?6000:3200);}
function post(action,data){var fd=data instanceof FormData?data:new FormData();if(!(data instanceof FormData)&&data)Object.keys(data).forEach(function(k){fd.append(k,data[k]);});fd.append("action","sapo_hotels_"+action);fd.append("nonce",D.nonce);
  return fetch(D.ajax,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json().catch(function(){throw new Error("Réponse invalide du serveur ("+r.status+").");});}).then(function(j){if(!j||!j.success)throw new Error(j&&j.data&&j.data.message?j.data.message:"Une erreur est survenue.");return j.data;});}
function busy(btn,on){if(btn){btn.classList.toggle("is-loading",on);btn.disabled=on;}}

/* ---------- Onglets ---------- */
$$(".sapo-a__tab").forEach(function(tab){tab.addEventListener("click",function(){$$(".sapo-a__tab").forEach(function(t){t.setAttribute("aria-selected",t===tab?"true":"false");});$$(".sapo-a__panel").forEach(function(p){p.hidden=p.getAttribute("data-panel")!==tab.getAttribute("data-tab");});try{localStorage.setItem("sapoHotelsTab",tab.getAttribute("data-tab"));}catch(e){}});});
try{var st=localStorage.getItem("sapoHotelsTab");if(st){var tb=$('.sapo-a__tab[data-tab="'+st+'"]');if(tb)tb.click();}}catch(e){}

/* ---------- Statistiques ---------- */
function stats(){
  var c={};hotels.forEach(function(h){c[h.country]=1;});
  var nogeo=hotels.filter(function(h){return h.lat===null;}).length;
  $('[data-stat="total"]').textContent=hotels.length;
  $('[data-stat="visible"]').textContent=hotels.filter(function(h){return h.active&&h.lat!==null;}).length;
  $('[data-stat="premium"]').textContent=hotels.filter(function(h){return h.premium;}).length;
  $('[data-stat="countries"]').textContent=Object.keys(c).length;
  $('[data-stat="nogeo"]').textContent=nogeo;
  $('[data-stat-box="nogeo"]').classList.toggle("is-on",nogeo>0);
  var fc=$("[data-fcountry]"),cur=fc.value,cnt=function(k){return hotels.filter(function(h){return h.country===k;}).length;};
  fc.innerHTML='<option value="">Tous les pays ('+hotels.length+')</option>'+D.mapCountries.map(function(k){return'<option value="'+esc(k)+'">'+esc(D.countries[k]||k)+' ('+cnt(k)+')</option>';}).join("");
  fc.value=cur;
  D.mapCountries.forEach(function(k){var m=$('[data-imp-meta="'+k+'"]');if(m)m.textContent=cnt(k)+" hôtel"+(cnt(k)>1?"s":"")+(D.imports&&D.imports[k]?" · import du "+D.imports[k]:" · aucun import");});
  if(D.updated)$("[data-updated]").textContent="Dernière mise à jour : "+D.updated+".";
}

/* ---------- Tableau ---------- */
function filtered(){
  var q=norm($("[data-q]").value).split(/\s+/).filter(Boolean),fc=$("[data-fcountry]").value,fs=$("[data-fstatus]").value;
  return hotels.filter(function(h){
    if(fc&&h.country!==fc)return false;
    if(fs==="premium"&&!h.premium)return false;
    if(fs==="hidden"&&h.active)return false;
    if(fs==="nogeo"&&h.lat!==null)return false;
    if(fs==="incomplete"&&h.address&&h.zip&&h.city&&h.canton&&h.website)return false;
    var s=norm([h.name,h.address,h.zip,h.city,h.canton,D.cantons[h.canton]||"",D.countries[h.country]||"",h.website].join(" "));
    return q.every(function(t){return s.indexOf(t)>-1;});
  }).sort(function(a,b){var x=sortKey==="city"?(a.city||"")+a.zip:a[sortKey]||"",y=sortKey==="city"?(b.city||"")+b.zip:b[sortKey]||"";return sortDir*String(x).localeCompare(String(y),"fr",{sensitivity:"base"});});
}
function render(){
  var list=filtered(),tb=$("[data-rows]");
  tb.innerHTML=list.map(function(h){
    var status=h.lat===null?'<span class="sapo-tag sapo-tag--warn">À localiser</span>':h.active?'<span class="sapo-tag sapo-tag--ok">Visible</span>':'<span class="sapo-tag sapo-tag--off">Masqué</span>';
    var miss=function(v,l){return v?esc(v):'<span class="sapo-missing">'+l+' manquant</span>';};
    return '<tr data-id="'+esc(h.id)+'" class="'+(h.active?"":"is-hidden")+'">'+
      '<td class="sapo-col-check"><input type="checkbox" data-sel aria-label="Sélectionner '+esc(h.name)+'"'+(selected[h.id]?" checked":"")+'></td>'+
      '<td class="sapo-col-icon">'+PIN("green",14)+'</td>'+
      '<td><span class="sapo-name">'+(h.premium?PREMIUM:'')+esc(h.name)+'</span>'+(h.country!=="CH"?'<span class="sapo-cell-muted"> · '+esc(D.countries[h.country]||h.country)+'</span>':'')+'</td>'+
      '<td class="sapo-cell-muted">'+miss(h.address,"Adresse")+'</td>'+
      '<td>'+(h.zip?esc(h.zip)+" ":'<span class="sapo-missing">NPA manquant</span> ')+esc(h.city)+'</td>'+
      '<td>'+(h.canton?'<span class="sapo-chip" title="'+esc(D.cantons[h.canton]||h.canton)+'">'+esc(h.canton)+'</span>':'<span class="sapo-missing">—</span>')+'</td>'+
      '<td>'+(h.website?'<a class="sapo-web" href="'+esc(h.website)+'" target="_blank" rel="noopener" data-stop>'+esc(host(h.website))+'</a>':'<span class="sapo-missing">—</span>')+'</td>'+
      '<td class="sapo-col-status">'+status+'</td>'+
      '<td class="sapo-col-actions"><button type="button" class="sapo-icon-btn" data-edit aria-label="Modifier '+esc(h.name)+'"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4Zm11-15 4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></button><button type="button" class="sapo-icon-btn" data-del aria-label="Supprimer '+esc(h.name)+'"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button></td></tr>';
  }).join("");
  $("[data-empty]").hidden=list.length>0;
  $("[data-count]").textContent=list.length+" / "+hotels.length+" hôtels";
  $$(".sapo-sort").forEach(function(b){b.classList.toggle("is-asc",b.getAttribute("data-sort")===sortKey&&sortDir===1);b.classList.toggle("is-desc",b.getAttribute("data-sort")===sortKey&&sortDir===-1);});
  bulkUI();stats();
}
function byId(id){for(var i=0;i<hotels.length;i++)if(hotels[i].id===id)return hotels[i];return null;}
$("[data-rows]").addEventListener("click",function(e){
  var tr=e.target.closest("tr");if(!tr)return;var h=byId(tr.getAttribute("data-id"));
  if(e.target.closest("[data-stop]"))return;
  if(e.target.matches("[data-sel]")){if(e.target.checked)selected[h.id]=1;else delete selected[h.id];bulkUI();return;}
  if(e.target.closest("[data-del]")){remove([h.id],"« "+h.name+" »");return;}
  openDrawer(h);
});
var deb;$("[data-q]").addEventListener("input",function(){clearTimeout(deb);deb=setTimeout(render,120);});
$("[data-fcountry]").addEventListener("change",render);$("[data-fstatus]").addEventListener("change",render);
$$(".sapo-sort").forEach(function(b){b.addEventListener("click",function(){var k=b.getAttribute("data-sort");if(sortKey===k)sortDir=-sortDir;else{sortKey=k;sortDir=1;}render();});});
$("[data-check-all]").addEventListener("change",function(e){filtered().forEach(function(h){if(e.target.checked)selected[h.id]=1;else delete selected[h.id];});render();});
function bulkUI(){var n=Object.keys(selected).filter(byId).length;$("[data-bulk]").hidden=!n;$("[data-bulk-n]").textContent=n+(n>1?" hôtels sélectionnés":" hôtel sélectionné");var all=$("[data-check-all]");var f=filtered();all.checked=f.length>0&&f.every(function(h){return selected[h.id];});}
$$("[data-bulk-act]").forEach(function(b){b.addEventListener("click",function(){var ids=Object.keys(selected).filter(byId),act=b.getAttribute("data-bulk-act");if(!ids.length)return;
  if(act==="delete"){remove(ids,ids.length+" hôtels");return;}
  busy(b,true);post("delete",{ids:JSON.stringify(ids),act:act}).then(function(d){hotels=d.hotels;selected={};render();toast(act==="show"?"Hôtels affichés sur la carte.":"Hôtels masqués de la carte.");}).catch(function(e){toast(e.message,true);}).finally(function(){busy(b,false);});});});
function remove(ids,label){if(!window.confirm("Supprimer définitivement "+label+" ?"))return;post("delete",{ids:JSON.stringify(ids),act:"delete"}).then(function(d){hotels=d.hotels;ids.forEach(function(i){delete selected[i];});render();closeDrawer();toast(ids.length>1?ids.length+" hôtels supprimés.":"Hôtel supprimé.");}).catch(function(e){toast(e.message,true);});}

/* ---------- Tiroir d'édition ---------- */
var drawer=$("[data-drawer]"),form=$("[data-edit-form]"),mini=null,miniMarker=null,lastFocus=null;
var fCountry=form.elements.country,fCanton=form.elements.canton_select,fCantonTxt=form.elements.canton_text;
fCountry.innerHTML=D.mapCountries.map(function(k){return'<option value="'+k+'">'+esc(D.countries[k])+'</option>';}).join("");
fCanton.innerHTML='<option value="">Choisir un canton</option>'+Object.keys(D.cantons).map(function(k){return'<option value="'+k+'">'+k+" · "+esc(D.cantons[k])+'</option>';}).join("");
function cantonMode(){var ch=fCountry.value==="CH";fCanton.hidden=!ch;fCantonTxt.hidden=ch;$("[data-canton-label]").textContent=ch?"Canton":"Région";$("[data-canton-label]").htmlFor=ch?"sapo-e-canton":"sapo-e-canton-txt";}
fCountry.addEventListener("change",cantonMode);
function setPos(lat,lng,pan){form.elements.lat.value=lat==null?"":(+lat).toFixed(6);form.elements.lng.value=lng==null?"":(+lng).toFixed(6);if(!mini)return;
  if(lat==null){if(miniMarker){mini.removeLayer(miniMarker);miniMarker=null;}return;}
  var ll=[+lat,+lng];if(!miniMarker){miniMarker=L.marker(ll,{draggable:true,icon:L.icon({iconUrl:D.icons[smiley()],iconSize:[30,49],iconAnchor:[17,49]})}).addTo(mini);miniMarker.on("dragend",function(){var p=miniMarker.getLatLng();setPos(p.lat,p.lng,false);});}else miniMarker.setLatLng(ll);
  if(pan)mini.setView(ll,Math.max(mini.getZoom(),15));}
function smiley(){return "green";}
["lat","lng"].forEach(function(n){form.elements[n].addEventListener("change",function(){var a=parseFloat(form.elements.lat.value.replace(",",".")),b=parseFloat(form.elements.lng.value.replace(",","."));if(!isNaN(a)&&!isNaN(b))setPos(a,b,true);});});
form.elements.website.addEventListener("input",function(){var v=form.elements.website.value.trim();$("[data-open-web]").href=v?(/^https?:\/\//i.test(v)?v:"https://"+v):"#";});

function openDrawer(h){
  lastFocus=document.activeElement;
  var isNew=!h;h=h||{id:"",name:"",address:"",zip:"",city:"",canton:"",country:$("[data-fcountry]").value||"CH",website:"",lat:null,lng:null,smiley:"green",premium:false,active:true};
  $("[data-drawer-title]").textContent=isNew?"Ajouter un hôtel":"Modifier l'hôtel";
  $("[data-delete]").hidden=isNew;
  ["id","name","address","zip","city","website"].forEach(function(k){form.elements[k].value=h[k]||"";});
  form.elements.website.dispatchEvent(new Event("input"));
  if(!D.countries[h.country]){var o=document.createElement("option");o.value=h.country;o.textContent=h.country;fCountry.appendChild(o);}
  fCountry.value=h.country||"CH";cantonMode();
  if(h.country==="CH"&&!D.cantons[h.canton]&&h.canton){var o2=document.createElement("option");o2.value=h.canton;o2.textContent=h.canton;fCanton.appendChild(o2);}
  fCanton.value=h.country==="CH"?h.canton||"":"";fCantonTxt.value=h.country==="CH"?"":h.canton||"";
  form.elements.premium.checked=!!h.premium;form.elements.active.checked=!!h.active;
  $$(".is-invalid",form).forEach(function(i){i.classList.remove("is-invalid");});$('[data-err="name"]').hidden=true;
  drawer.hidden=false;document.body.style.overflow="hidden";
  setTimeout(function(){
    if(window.L&&!mini){mini=L.map($("[data-minimap]"),{scrollWheelZoom:true}).setView([46.8,8.2],7);L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:"© OpenStreetMap",maxZoom:19}).addTo(mini);mini.on("click",function(e){setPos(e.latlng.lat,e.latlng.lng,false);});}
    if(mini){mini.invalidateSize();if(miniMarker){mini.removeLayer(miniMarker);miniMarker=null;}if(h.lat!==null&&h.lat!==undefined){setPos(h.lat,h.lng,true);}else{setPos(null,null);mini.setView([46.8,8.2],7);}}
    else setPos(h.lat,h.lng);
    form.elements.name.focus();
  },60);
}
function closeDrawer(){if(drawer.hidden)return;drawer.hidden=true;document.body.style.overflow="";if(lastFocus&&lastFocus.focus)lastFocus.focus();}
$$("[data-close]",drawer).forEach(function(b){b.addEventListener("click",closeDrawer);});
document.addEventListener("keydown",function(e){if(drawer.hidden)return;if(e.key==="Escape"){closeDrawer();}
  if(e.key==="Tab"){var f=$$('input:not([hidden]):not([type=hidden]),select:not([hidden]),button:not([hidden]),a[href]',form).filter(function(x){return x.offsetParent!==null;});if(!f.length)return;var a=f[0],z=f[f.length-1];if(e.shiftKey&&document.activeElement===a){e.preventDefault();z.focus();}else if(!e.shiftKey&&document.activeElement===z){e.preventDefault();a.focus();}}});
$("[data-add]").addEventListener("click",function(){openDrawer(null);});
$("[data-delete]").addEventListener("click",function(){var h=byId(form.elements.id.value);if(h)remove([h.id],"« "+h.name+" »");});
$("[data-locate]").addEventListener("click",function(){var b=this;if(!form.elements.address.value&&!form.elements.city.value&&!form.elements.zip.value){toast("Renseignez d'abord l'adresse, le NPA ou la ville.",true);return;}
  b.disabled=true;b.textContent="Recherche…";
  post("geocode",{preview:1,name:form.elements.name.value,address:form.elements.address.value,zip:form.elements.zip.value,city:form.elements.city.value,country:fCountry.value}).then(function(p){setPos(p.lat,p.lng,true);toast("Adresse localisée. Vérifiez la position puis enregistrez.");}).catch(function(e){toast(e.message,true);}).finally(function(){b.disabled=false;b.textContent="Localiser depuis l'adresse";});});
form.addEventListener("submit",function(e){
  e.preventDefault();
  var name=form.elements.name.value.trim();
  if(!name){form.elements.name.classList.add("is-invalid");$('[data-err="name"]').hidden=false;form.elements.name.focus();return;}
  var lat=form.elements.lat.value.trim(),lng=form.elements.lng.value.trim();
  var h={id:form.elements.id.value,name:name,address:form.elements.address.value,zip:form.elements.zip.value,city:form.elements.city.value,country:fCountry.value,canton:fCountry.value==="CH"?fCanton.value:fCantonTxt.value,website:form.elements.website.value,smiley:smiley(),premium:form.elements.premium.checked?"oui":"non",active:form.elements.active.checked?"oui":"non",lat:lat,lng:lng};
  var btn=$("[data-save]");busy(btn,true);
  var isNew=!h.id;
  post("save",{hotel:JSON.stringify(h)}).then(function(d){hotels=d.hotels;render();closeDrawer();toast(isNew?"Hôtel ajouté.":"Modifications enregistrées.");
    if(d.hotel.lat===null&&(d.hotel.address||d.hotel.city)){geocodeIds([d.hotel.id]);}
  }).catch(function(err){toast(err.message,true);}).finally(function(){busy(btn,false);});
});

/* ---------- Géolocalisation en série ---------- */
var geoRunning=false;
function geocodeIds(ids){
  if(geoRunning||!ids.length)return Promise.resolve();
  geoRunning=true;var box=$("[data-progress]"),bar=$("[data-progress-bar]"),lab=$("[data-progress-n]"),done=0,fails=[];
  box.hidden=false;bar.style.width="0%";lab.textContent="0 / "+ids.length;
  var i=0;
  return new Promise(function(resolve){
    (function next(){
      if(i>=ids.length){geoRunning=false;$("[data-progress-label]").textContent=fails.length?"Géolocalisation terminée avec "+fails.length+" adresse(s) introuvable(s)":"Géolocalisation terminée";
        if(fails.length){toast(fails.length+" adresse(s) introuvable(s). Filtrez « Sans coordonnées » pour les placer à la main.",true);}else toast("Toutes les adresses ont été localisées.");
        setTimeout(function(){if(!geoRunning)box.hidden=true;},6000);resolve();return;}
      var id=ids[i++];$("[data-progress-label]").textContent="Géolocalisation…";
      var fast=false;
      post("geocode",{id:id}).then(function(d){fast=!!d.fast;for(var k=0;k<hotels.length;k++)if(hotels[k].id===d.hotel.id)hotels[k]=d.hotel;}).catch(function(){fails.push(id);}).finally(function(){done++;bar.style.width=Math.round(done/ids.length*100)+"%";lab.textContent=done+" / "+ids.length;if(done%10===0||done===ids.length||!fast)render();setTimeout(next,fast?60:1100);});
    })();
  });
}
$("[data-geocode-all]").addEventListener("click",function(){var ids=hotels.filter(function(h){return h.lat===null;}).map(function(h){return h.id;});$('.sapo-a__tab[data-tab="import"]').click();geocodeIds(ids);});

/* ---------- Import (un fichier par pays) ---------- */
var drop=$("[data-drop]"),file=$("[data-file]"),impBtn=$("[data-import-btn]"),result=$("[data-result]");
var impForm=$("[data-import-form]"),impCountry="CH",COLS=Object.keys(D.colLabels.CH);
var IN={CH:"en Suisse",FR:"en France",DE:"en Allemagne"};
function countIn(c){return hotels.filter(function(h){return h.country===c;}).length;}
function colDoc(){var L=D.colLabels[impCountry];$("[data-coldoc]").innerHTML=COLS.map(function(k){var v=(impForm.elements["col_"+k].value||"").toUpperCase();return'<tr><td><code>'+(v?esc(v):"non lue")+'</code></td><td>'+esc(L[k])+(k==="name"?' <span class="sapo-req">obligatoire</span>':'')+'</td></tr>';}).join("");}
COLS.forEach(function(k){var i=impForm.elements["col_"+k];i.addEventListener("input",function(){i.value=i.value.replace(/[^a-z]/gi,"").toUpperCase();colDoc();});});
function setImpCountry(c){
  impCountry=c;var L=D.colLabels[c],name=D.countries[c];
  $$("[data-imp-country]").forEach(function(b){b.setAttribute("aria-selected",b.getAttribute("data-imp-country")===c?"true":"false");});
  $("[data-imp-title]").textContent="Hôtels "+IN[c];
  $("[data-imp-sub]").textContent="Déposez le fichier Excel des hôtels "+IN[c]+" : les cartes "+name+" en français, allemand et anglais se mettent à jour immédiatement.";
  $("[data-mode-replace]").textContent="Le fichier devient la liste complète des hôtels "+IN[c]+". Les hôtels "+IN[c]+" absents du fichier sont retirés.";
  COLS.forEach(function(k){impForm.elements["col_"+k].value=(D.settings.columns[c]&&D.settings.columns[c][k])||"";$('[data-col-label="'+k+'"]').textContent=L[k];impForm.elements["col_"+k].setAttribute("aria-label","Lettre de colonne : "+L[k]);});
  impForm.elements.header_row.checked=!!+(D.settings.header_row||{})[c];
  $("[data-export]").href=D.export+"&country="+c;$("[data-export-label]").textContent="Exporter les hôtels "+IN[c]+" ("+countIn(c)+")";
  $("[data-template]").href=D.template+"&country="+c;
  result.hidden=true;colDoc();showFile();
}
$$("[data-imp-country]").forEach(function(b){b.addEventListener("click",function(){setImpCountry(b.getAttribute("data-imp-country"));});});
function setFile(f){if(!f)return;var dt=new DataTransfer();dt.items.add(f);file.files=dt.files;showFile();}
function showFile(){var f=file.files[0];drop.classList.toggle("has-file",!!f);$("[data-drop-text]").innerHTML=f?"<strong>"+esc(f.name)+"</strong> prêt à importer pour : "+esc(D.countries[impCountry]):"Glissez le fichier des hôtels "+esc(IN[impCountry])+" ici ou <u>parcourez</u>";impBtn.disabled=!f;impBtn.textContent=f?"Importer pour : "+D.countries[impCountry]:"Importer le fichier";}
file.addEventListener("change",showFile);
["dragenter","dragover"].forEach(function(ev){drop.addEventListener(ev,function(e){e.preventDefault();drop.classList.add("is-over");});});
["dragleave","drop"].forEach(function(ev){drop.addEventListener(ev,function(e){e.preventDefault();drop.classList.remove("is-over");});});
drop.addEventListener("drop",function(e){if(e.dataTransfer.files[0])setFile(e.dataTransfer.files[0]);});
impForm.addEventListener("submit",function(e){
  e.preventDefault();var f=file.files[0];if(!f)return;
  var frm=this,c=impCountry,mode=frm.elements.mode.value,n=countIn(c);
  if(mode==="replace"&&n&&!window.confirm("Remplacer les "+n+" hôtels "+IN[c]+" par le contenu du fichier « "+f.name+" » ?\n\nLes hôtels des autres pays ne sont pas touchés. Une sauvegarde est conservée : vous pourrez annuler cet import."))return;
  var cols={};COLS.forEach(function(k){cols[k]=(frm.elements["col_"+k].value||"").trim().toUpperCase();});
  if(!/^[A-Z]{1,3}$/.test(cols.name)){toast("Indiquez la lettre de la colonne du nom de l'hôtel.",true);frm.elements.col_name.focus();return;}
  var fd=new FormData();fd.append("file",f);fd.append("mode",mode);fd.append("country",c);
  fd.append("columns",JSON.stringify(cols));fd.append("header_row",frm.elements.header_row.checked?"1":"0");
  var geo=frm.elements.geocode.checked;
  busy(impBtn,true);result.hidden=true;result.classList.remove("is-error");
  post("import",fd).then(function(d){
    hotels=d.hotels;selected={};D.backup=d.backup;restoreUI();
    D.settings.columns[c]=d.columns_map;D.settings.header_row[c]=d.header_row;D.imports=D.imports||{};D.imports[c]=d.imported;render();
    var L=D.colLabels[c];
    var prev=d.preview&&d.preview.length?'<p><strong>Aperçu des premières lignes lues :</strong></p><div class="sapo-preview-wrap"><table class="sapo-preview"><thead><tr>'+COLS.map(function(k){return"<th>"+esc(L[k])+" ("+esc(d.columns_map[k]||"?")+")</th>";}).join("")+'</tr></thead><tbody>'+d.preview.map(function(h){return"<tr>"+COLS.map(function(k){return"<td>"+esc(h[k]||"")+"</td>";}).join("")+"</tr>";}).join("")+'</tbody></table></div>':'';
    result.innerHTML='<h3>Import terminé : '+esc(D.countries[c])+'</h3><div class="sapo-result__nums"><span><b>'+d.total+'</b>hôtels '+IN[c]+'</span><span><b>'+d.added+'</b>ajoutés</span><span><b>'+d.updated+'</b>mis à jour</span>'+(mode==="replace"?'<span><b>'+d.removed+'</b>retirés</span>':'')+'<span><b>'+d.nogeo.length+'</b>à géolocaliser</span></div>'+(d.warnings.length?'<p><strong>Points à vérifier :</strong></p><ul>'+d.warnings.map(function(w){return"<li>"+esc(w)+"</li>";}).join("")+'</ul>':'')+prev;
    result.hidden=false;toast("Import réussi : les cartes "+D.countries[c]+" sont à jour.");
    file.value="";showFile();setImpCountry(c);result.hidden=false;
    if(geo&&d.nogeo.length)geocodeIds(d.nogeo);
  }).catch(function(err){result.classList.add("is-error");result.innerHTML="<h3>Import impossible</h3><p>"+esc(err.message)+"</p>";result.hidden=false;}).finally(function(){busy(impBtn,false);showFile();});
});
function restoreUI(){var b=$("[data-restore]");b.hidden=!D.backup;b.title=D.backup?"Revenir à la liste d'avant l'import du "+D.backup:"";}
$("[data-restore]").addEventListener("click",function(){var b=this;if(!window.confirm("Revenir à la liste des hôtels d'avant le dernier import ("+D.backup+") ?"))return;busy(b,true);
  post("restore",{}).then(function(d){hotels=d.hotels;D.backup="";restoreUI();render();toast("Dernier import annulé.");result.hidden=true;}).catch(function(e){toast(e.message,true);}).finally(function(){busy(b,false);});});

/* ---------- Réglages ---------- */
var sf=$("[data-settings-form]");
function tilesUI(){$("[data-custom-tiles]").hidden=sf.elements.tiles.value!=="custom";}
sf.elements.tiles.addEventListener("change",tilesUI);
function fillSettings(s){sf.elements.tiles.value=s.tiles;sf.elements.tiles_url.value=s.tiles_url||"";sf.elements.tiles_attr.value=s.tiles_attr||"";tilesUI();sf.elements.height.value=s.height;sf.elements.cluster.checked=!!+s.cluster;["legend_member","legend_premium"].forEach(function(k){["fr","de","en"].forEach(function(l){sf.elements[k+"["+l+"]"].value=(s[k]&&s[k][l])||"";});});}
fillSettings(D.settings);
sf.addEventListener("submit",function(e){e.preventDefault();var s={tiles:sf.elements.tiles.value,tiles_url:sf.elements.tiles_url.value,tiles_attr:sf.elements.tiles_attr.value,height:sf.elements.height.value,cluster:sf.elements.cluster.checked?1:0};
  ["legend_member","legend_premium"].forEach(function(k){s[k]={};["fr","de","en"].forEach(function(l){s[k][l]=sf.elements[k+"["+l+"]"].value;});});
  var b=sf.querySelector("[type=submit]");busy(b,true);post("settings",{settings:JSON.stringify(s)}).then(function(d){fillSettings(d.settings);toast("Réglages enregistrés.");}).catch(function(e){toast(e.message,true);}).finally(function(){busy(b,false);});});
$$("[data-copy]").forEach(function(b){b.addEventListener("click",function(){var v=b.getAttribute("data-copy");(navigator.clipboard?navigator.clipboard.writeText(v):Promise.reject()).then(function(){toast("Shortcode copié.");}).catch(function(){window.prompt("Copiez le shortcode :",v);});});});

restoreUI();render();setImpCountry("CH");
})();
JS;
	}
}

Sapo_Hotels_Map::sapo_init();

<?php
/**
 * Montants : toujours en centimes entiers. Aucun nombre à virgule flottante dans les calculs.
 * Les taux (20, 5.5, 2.1…) sont convertis en centièmes de pour cent (entiers).
 * Arrondi commercial au centime : demi éloigné de zéro.
 */
defined( 'ABSPATH' ) || exit;

class AC_Money {

	/** Division entière arrondie au plus proche, demi éloigné de zéro. */
	public static function div_round( int $a, int $b ): int {
		if ( 0 === $b ) {
			return 0;
		}
		$neg = ( $a < 0 ) !== ( $b < 0 );
		$a   = abs( $a );
		$b   = abs( $b );
		$q   = intdiv( $a, $b );
		if ( ( $a % $b ) * 2 >= $b ) {
			$q++;
		}
		return $neg ? -$q : $q;
	}

	/** « 12,34 », « 1 234.5 », « -0,1 » ou un entier en euros → centimes. */
	public static function to_cents( $value ): int {
		if ( null === $value || '' === $value ) {
			return 0;
		}
		if ( is_int( $value ) ) {
			return $value * 100;
		}
		$s = str_replace( array( "\u{00a0}", "\u{202f}", ' ', '€' ), '', trim( (string) $value ) );
		if ( false !== strpos( $s, ',' ) && false !== strpos( $s, '.' ) ) {
			$s = str_replace( '.', '', $s );
		}
		$s = str_replace( ',', '.', $s );
		if ( ! preg_match( '/^([+-]?)(\d*)(?:\.(\d*))?$/', $s, $m ) || ( '' === $m[2] && ( ! isset( $m[3] ) || '' === $m[3] ) ) ) {
			throw new AC_Error( 'Montant invalide : ' . $value );
		}
		$int  = (int) ( '' === $m[2] ? '0' : $m[2] );
		$frac = isset( $m[3] ) ? $m[3] : '';
		$cents = $int * 100 + (int) str_pad( substr( $frac, 0, 2 ), 2, '0' );
		if ( strlen( $frac ) > 2 && (int) $frac[2] >= 5 ) {
			$cents++;
		}
		return '-' === $m[1] ? -$cents : $cents;
	}

	/** Taux « 5,5 » → 550 (centièmes de pour cent). */
	public static function rate_bp( $rate ): int {
		if ( null === $rate || '' === $rate ) {
			return 0;
		}
		return self::to_cents( (string) $rate );
	}

	/** Normalise un taux en texte : « 5,50 » → « 5.5 », « 20.0 » → « 20 ». */
	public static function rate_str( $rate ): string {
		$bp = self::rate_bp( $rate );
		$s  = intdiv( abs( $bp ), 100 ) . ( $bp % 100 ? '.' . rtrim( str_pad( (string) ( abs( $bp ) % 100 ), 2, '0', STR_PAD_LEFT ), '0' ) : '' );
		return ( $bp < 0 ? '-' : '' ) . $s;
	}

	/** cents × pct / 100, arrondi au centime. */
	public static function pct_of( int $cents, $pct ): int {
		return self::div_round( $cents * self::rate_bp( $pct ), 10000 );
	}

	public static function vat_from_ht( int $ht, $rate ): int {
		return self::pct_of( $ht, $rate );
	}

	/** Décompose un TTC en [HT, TVA] avec HT + TVA = TTC. */
	public static function split_ttc( int $ttc, $rate ): array {
		$bp = self::rate_bp( $rate );
		if ( 0 === $bp ) {
			return array( $ttc, 0 );
		}
		$ht = self::div_round( $ttc * 10000, 10000 + $bp );
		return array( $ht, $ttc - $ht );
	}

	/** total × part / whole arrondi. */
	public static function prorata( int $total, int $part, int $whole ): int {
		return 0 === $whole ? 0 : self::div_round( $total * $part, $whole );
	}

	/** 1 234,56 € */
	public static function fmt( $cents ): string {
		$c   = (int) $cents;
		$neg = $c < 0;
		$c   = abs( $c );
		return ( $neg ? '-' : '' ) . number_format( intdiv( $c, 100 ), 0, ',', ' ' ) . ',' . str_pad( (string) ( $c % 100 ), 2, '0', STR_PAD_LEFT ) . ' €';
	}

	/** 1234,56 (FEC, exports) */
	public static function plain( $cents ): string {
		$c   = (int) $cents;
		$neg = $c < 0;
		$c   = abs( $c );
		return ( $neg ? '-' : '' ) . intdiv( $c, 100 ) . ',' . str_pad( (string) ( $c % 100 ), 2, '0', STR_PAD_LEFT );
	}

	/** Valeur numérique en euros pour les tableurs (affichage uniquement). */
	public static function eur( $cents ): float {
		return round( ( (int) $cents ) / 100, 2 );
	}
}

<?php
/**
 * Calendrier des obligations (TVA, DES, CFE, IS, liasse, approbation, greffe) avec rappels par e-mail,
 * et préparation de la déclaration européenne de services (DES) pour les clients professionnels de l'UE.
 */
defined( 'ABSPATH' ) || exit;

class AC_Calendar {

	const EU = array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' );

	const COUNTRIES = array( 'FR' => 'France', 'AT' => 'Autriche', 'BE' => 'Belgique', 'BG' => 'Bulgarie', 'CY' => 'Chypre', 'CZ' => 'Tchéquie', 'DE' => 'Allemagne',
		'DK' => 'Danemark', 'EE' => 'Estonie', 'ES' => 'Espagne', 'FI' => 'Finlande', 'GR' => 'Grèce', 'HR' => 'Croatie', 'HU' => 'Hongrie', 'IE' => 'Irlande',
		'IT' => 'Italie', 'LT' => 'Lituanie', 'LU' => 'Luxembourg', 'LV' => 'Lettonie', 'MT' => 'Malte', 'NL' => 'Pays-Bas', 'PL' => 'Pologne', 'PT' => 'Portugal',
		'RO' => 'Roumanie', 'SE' => 'Suède', 'SI' => 'Slovénie', 'SK' => 'Slovaquie', 'CH' => 'Suisse', 'GB' => 'Royaume-Uni', 'US' => 'États-Unis', 'CA' => 'Canada',
		'MA' => 'Maroc', 'TN' => 'Tunisie', 'DZ' => 'Algérie', 'AE' => 'Émirats arabes unis', 'XX' => 'Autre pays hors UE' );

	// ------------------------------------------------------------ jours ouvrés
	private static function easter( int $y ): int {
		$a = $y % 19; $b = intdiv( $y, 100 ); $c = $y % 100; $d = intdiv( $b, 4 ); $e = $b % 4; $f = intdiv( $b + 8, 25 ); $g = intdiv( $b - $f + 1, 3 );
		$h = ( 19 * $a + $b - $d - $g + 15 ) % 30; $i = intdiv( $c, 4 ); $k = $c % 4; $l = ( 32 + 2 * $e + 2 * $i - $h - $k ) % 7; $m = intdiv( $a + 11 * $h + 22 * $l, 451 );
		$month = intdiv( $h + $l - 7 * $m + 114, 31 ); $day = ( ( $h + $l - 7 * $m + 114 ) % 31 ) + 1;
		return gmmktime( 12, 0, 0, $month, $day, $y );
	}

	public static function is_business_day( string $date ): bool {
		$t = strtotime( "$date 12:00:00 UTC" );
		if ( (int) gmdate( 'N', $t ) >= 6 ) {
			return false;
		}
		$y   = (int) gmdate( 'Y', $t );
		$e   = self::easter( $y );
		$off = array( "$y-01-01", "$y-05-01", "$y-05-08", "$y-07-14", "$y-08-15", "$y-11-01", "$y-11-11", "$y-12-25",
			gmdate( 'Y-m-d', $e + 86400 ), gmdate( 'Y-m-d', $e + 39 * 86400 ), gmdate( 'Y-m-d', $e + 50 * 86400 ) );
		return ! in_array( $date, $off, true );
	}

	/** n-ième jour ouvré du mois (n=1 : premier jour ouvré). */
	public static function nth_business_day( string $ym, int $n ): string {
		$d = "$ym-01";
		$c = 0;
		while ( true ) {
			if ( self::is_business_day( $d ) && ++$c === $n ) {
				return $d;
			}
			$d = gmdate( 'Y-m-d', strtotime( "$d +1 day" ) );
		}
	}

	/** n-ième jour ouvré qui suit une date. */
	public static function business_day_after( string $date, int $n ): string {
		$d = $date;
		$c = 0;
		while ( $c < $n ) {
			$d = gmdate( 'Y-m-d', strtotime( "$d +1 day" ) );
			if ( self::is_business_day( $d ) ) {
				$c++;
			}
		}
		return $d;
	}

	private static function add_months( string $date, int $n ): string {
		return ( new DateTime( $date ) )->modify( 'first day of this month' )->modify( sprintf( "%+d month", $n ) )->format( 'Y-m' );
	}

	// ------------------------------------------------------------ DES
	public static function is_eu( ?string $country ): bool {
		return in_array( strtoupper( (string) $country ), self::EU, true );
	}

	/** Prestations de services à des clients professionnels de l'UE sur un mois (base de la DES). */
	public static function des_month( string $ym ): array {
		$rows = AC_DB::q( "SELECT s.id, s.number, s.issue_date, s.ht_cents, s.vat_cents, s.vat_regime, s.kind, p.id client_id, p.name, p.vat_number, p.country
			FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id WHERE s.status<>'annulee' AND s.nature='services' AND DATE_FORMAT(s.issue_date,'%Y-%m')=? ORDER BY s.issue_date",
			array( $ym ) );
		$lines  = array();
		$issues = array();
		foreach ( $rows as $r ) {
			$eu = self::is_eu( $r['country'] );
			if ( ! ( 'autoliquidation_ue' === $r['vat_regime'] || ( $eu && 0 === (int) $r['vat_cents'] ) ) ) {
				continue;
			}
			if ( ! $eu ) {
				$issues[] = "Facture {$r['number']} ({$r['name']}) : régime « client UE » mais pays du client non renseigné.";
				continue;
			}
			$vat = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $r['vat_number'] ) );
			if ( '' === $vat ) {
				$issues[] = "Client {$r['name']} : numéro de TVA intracommunautaire manquant (obligatoire pour la DES et pour facturer sans TVA).";
			}
			$k = $r['client_id'];
			$lines[ $k ] = $lines[ $k ] ?? array( 'client_id' => (int) $k, 'client' => $r['name'], 'country' => strtoupper( (string) $r['country'] ), 'vat_number' => $vat,
				'ht_cents' => 0, 'invoices' => array() );
			$lines[ $k ]['ht_cents'] += ( 'avoir' === $r['kind'] ? -1 : 1 ) * (int) $r['ht_cents'];
			$lines[ $k ]['invoices'][] = $r['number'];
		}
		$lines = array_values( $lines );
		foreach ( $lines as &$l ) {
			$l['montant_euros'] = (int) round( $l['ht_cents'] / 100 ); // la DES se déclare en euros, sans centimes
		}
		unset( $l );
		return array( 'month' => $ym, 'lines' => $lines, 'issues' => array_values( array_unique( $issues ) ),
			'due' => self::nth_business_day( self::add_months( "$ym-01", 1 ), 10 ), 'total_euros' => array_sum( array_column( $lines, 'montant_euros' ) ) );
	}

	/** Mois de l'exercice (et du précédent) comportant des prestations à déclarer en DES. */
	public static function des_months(): array {
		$out = array();
		foreach ( AC_DB::q( "SELECT DISTINCT DATE_FORMAT(s.issue_date,'%Y-%m') m FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id
			WHERE s.nature='services' AND (s.vat_regime='autoliquidation_ue' OR (s.vat_cents=0 AND p.country IN ('" . implode( "','", self::EU ) . "'))) ORDER BY m" ) as $r ) {
			$out[] = $r['m'];
		}
		return $out;
	}

	public static function des_csv( string $ym ): string {
		$m   = self::des_month( $ym );
		$out = "numero_tva_client;pays;client;montant_ht_euros;factures\n";
		foreach ( $m['lines'] as $l ) {
			$out .= implode( ';', array( $l['vat_number'], $l['country'], str_replace( ';', ',', $l['client'] ), $l['montant_euros'], implode( ' ', $l['invoices'] ) ) ) . "\n";
		}
		return "\xEF\xBB\xBF" . $out;
	}

	/** Contrôle d'un numéro de TVA intracommunautaire sur VIES (Commission européenne). */
	public static function vies( string $vat ): array {
		$vat = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $vat ) );
		if ( ! preg_match( '/^([A-Z]{2})([A-Z0-9]{2,13})$/', $vat, $m ) ) {
			throw new AC_Error( 'Numéro de TVA invalide : il commence par le code pays (ex. BG123456789).' );
		}
		$cc = 'GR' === $m[1] ? 'EL' : $m[1];
		$r  = wp_remote_get( "https://ec.europa.eu/taxation_customs/vies/rest-api/ms/$cc/vat/{$m[2]}", array( 'timeout' => 15 ) );
		if ( is_wp_error( $r ) || 200 !== wp_remote_retrieve_response_code( $r ) ) {
			throw new AC_Error( 'Le service VIES de la Commission européenne ne répond pas : réessayez plus tard.' );
		}
		$j = json_decode( wp_remote_retrieve_body( $r ), true ) ?: array();
		return array( 'valid' => ! empty( $j['isValid'] ), 'name' => $j['name'] ?? null, 'address' => $j['address'] ?? null, 'vat_number' => $vat );
	}

	// ------------------------------------------------------------ échéances
	private static function done_map(): array {
		return (array) get_option( AC_DB::opt( 'calendar_done' ), array() );
	}

	public static function set_done( string $key, bool $done ): void {
		$m = self::done_map();
		if ( $done ) {
			$m[ $key ] = AC_DB::today();
		} else {
			unset( $m[ $key ] );
		}
		update_option( AC_DB::opt( 'calendar_done' ), $m, false );
	}

	/** Liste des obligations entre deux dates, avec leur état (faite, en retard, bientôt, à venir). */
	public static function items( string $from, string $to ): array {
		$tax   = AC_DB::get_setting( 'tax' );
		$co    = AC_DB::get_setting( 'company' );
		$today = AC_DB::today();
		$items = array();
		$add   = function ( string $key, string $due, string $title, string $note, string $link = '', array $extra = array() ) use ( &$items, $from, $to ) {
			if ( $due >= $from && $due <= $to ) {
				$items[] = array_merge( array( 'key' => $key, 'due' => $due, 'title' => $title, 'note' => $note, 'link' => $link ), $extra );
			}
		};
		$start = substr( $from, 0, 7 );
		$fys   = AC_DB::q( 'SELECT * FROM {fiscal_years} ORDER BY start_date' );
		$first = $fys ? $fys[0]['start_date'] : $from;
		// TVA
		$vat_from = $tax['vat_start_date'] ?: $first;
		for ( $m = self::add_months( "$start-01", -1 ); $m <= substr( $to, 0, 7 ); $m = self::add_months( "$m-01", 1 ) ) {
			$next = self::add_months( "$m-01", 1 );
			if ( 'reel_normal' === $tax['vat_regime'] && "$m-31" >= $vat_from ) {
				$quarter = 'trimestrielle' === $tax['vat_periodicity'];
				if ( ! $quarter || in_array( (int) substr( $m, 5, 2 ), array( 3, 6, 9, 12 ), true ) ) {
					$pstart = $quarter ? self::add_months( "$m-01", -2 ) . '-01' : "$m-01";
					$pend   = gmdate( 'Y-m-t', strtotime( "$m-01" ) );
					$label  = $quarter ? 'TVA du trimestre ' . self::add_months( "$m-01", -2 ) . ' à ' . $m : 'TVA de ' . date_i18n( 'F Y', strtotime( "$m-15" ) );
					$filed  = AC_DB::q1( "SELECT status FROM {vat_returns} WHERE period_start=? AND period_end=? AND status='deposee'", array( max( $pstart, $vat_from ), $pend ) );
					$add( "tva:$m", "$next-15", "Déclaration $label (CA3) et paiement", 'Date limite entre le 15 et le 24 selon votre entreprise : elle est indiquée dans votre espace professionnel impots.gouv. Le rappel est calé sur le 15 pour garder de la marge. Préparez la déclaration dans la page TVA.',
						'#/tva', array( 'auto_done' => (bool) $filed, 'category' => 'TVA' ) );
				}
			} elseif ( 'reel_simplifie' === $tax['vat_regime'] ) {
				$mm = (int) substr( $m, 5, 2 );
				if ( 7 === $mm || 12 === $mm ) {
					$add( "tva-acompte:$m", "$m-15", 'Acompte de TVA (régime simplifié)', 'Entre le 15 et le 24 : 55 % en juillet, 40 % en décembre de la TVA de l\'année précédente, si elle dépasse 1 000 €.', '#/tva', array( 'category' => 'TVA' ) );
				}
			}
			// DES : prestations de services à des clients professionnels de l'UE
			$des = self::des_month( $m );
			if ( $des['lines'] ) {
				$add( "des:$m", $des['due'], 'Déclaration européenne de services (DES) de ' . date_i18n( 'F Y', strtotime( "$m-15" ) ),
					count( $des['lines'] ) . ' client(s) UE, ' . $des['total_euros'] . ' € HT. À déposer en ligne sur douane.gouv.fr avant le 10e jour ouvré du mois suivant.', '#/echeances?des=' . $m,
					array( 'category' => 'DES' ) );
			}
		}
		// CFE
		$created = $co['creation_date'] ?: $first;
		$cy      = (int) substr( $created, 0, 4 );
		for ( $y = (int) substr( $from, 0, 4 ); $y <= (int) substr( $to, 0, 4 ); $y++ ) {
			if ( $y === $cy ) {
				$add( "cfe-decl:$y", "$y-12-31", 'Déclaration initiale de CFE (1447-C-SD)', "À déposer l'année de la création sur l'espace professionnel impots.gouv. La CFE n'est pas due l'année de création.", 'https://www.impots.gouv.fr/professionnel', array( 'category' => 'CFE' ) );
			} elseif ( $y > $cy ) {
				$add( "cfe:$y", "$y-12-15", 'Paiement de la CFE ' . $y, "Avis disponible fin novembre dans l'espace professionnel impots.gouv. " . ( $y === $cy + 1 ? 'Deuxième année : base réduite de moitié.' : '' ) . ' Classez le paiement en « Impôts et taxes ».',
					'https://www.impots.gouv.fr/professionnel', array( 'category' => 'CFE' ) );
			}
		}
		// Clôture, liasse, IS, approbation, greffe (pour chaque exercice)
		foreach ( $fys as $fy ) {
			$end  = $fy['end_date'];
			$ey   = (int) substr( $end, 0, 4 );
			$civ  = '12-31' === substr( $end, 5 );
			$liasse = $civ ? self::business_day_after( ( $ey + 1 ) . '-05-01', 2 ) : self::nth_business_day( self::add_months( $end, 3 ), 1 );
			$label  = 'exercice clos le ' . date_i18n( 'd/m/Y', strtotime( $end ) );
			$add( "liasse:{$fy['id']}", $liasse, "Liasse fiscale 2065 et 2033 ($label)", 'Télédéclaration ; 15 jours supplémentaires sont généralement accordés pour un dépôt en ligne. Valeurs à reporter : page Clôture et exports.', '#/cloture', array( 'category' => 'IS' ) );
			$solde = $civ ? ( $ey + 1 ) . '-05-15' : self::add_months( $end, 4 ) . '-15'; // exercice civil : 15 mai
			$add( "is-solde:{$fy['id']}", $solde, "Solde de l'IS (2572-SD) ($label)", "Relevé de solde à déposer et payer, même si aucun impôt n'est dû.", '#/cloture', array( 'category' => 'IS' ) );
			$appr = gmdate( 'Y-m-t', strtotime( self::add_months( $end, 6 ) . '-01' ) );
			$add( "approbation:{$fy['id']}", $appr, "Approbation des comptes par l'associé unique ($label)", "Dans les 6 mois de la clôture. Associé unique président : le dépôt au greffe des comptes signés vaut approbation.", '#/cloture', array( 'category' => 'Juridique' ) );
			$greffe = gmdate( 'Y-m-t', strtotime( self::add_months( $appr, 2 ) . '-01' ) );
			$add( "greffe:{$fy['id']}", $greffe, "Dépôt des comptes au greffe ($label)", '2 mois après l\'approbation pour un dépôt en ligne (guichet unique INPI). Déclaration de confidentialité possible pour une micro entreprise.', 'https://procedures.inpi.fr', array( 'category' => 'Juridique' ) );
			if ( (int) $tax['previous_is_cents'] > 300000 ) {
				foreach ( array( 3, 6, 9, 12 ) as $k => $mo ) {
					$d = ( $ey + 1 ) . '-' . str_pad( (string) $mo, 2, '0', STR_PAD_LEFT ) . '-15';
					$add( "is-acompte:{$fy['id']}:$mo", $d, 'Acompte d\'IS n°' . ( $k + 1 ), "L'IS de l'exercice précédent dépasse 3 000 € : acompte trimestriel (2571-SD).", '#/cloture', array( 'category' => 'IS' ) );
				}
			}
		}
		$done = self::done_map();
		foreach ( $items as &$it ) {
			$it['done']    = ! empty( $it['auto_done'] ) || isset( $done[ $it['key'] ] );
			$it['done_at'] = $done[ $it['key'] ] ?? null;
			$days          = (int) floor( ( strtotime( $it['due'] ) - strtotime( $today ) ) / 86400 );
			$it['days']    = $days;
			$it['state']   = $it['done'] ? 'faite' : ( $days < 0 ? 'en_retard' : ( $days <= 15 ? 'bientot' : 'a_venir' ) );
		}
		unset( $it );
		usort( $items, function ( $a, $b ) { return strcmp( $a['due'], $b['due'] ); } );
		return $items;
	}

	/** Rappels par e-mail : 7 jours avant (réglable) puis le jour même, une seule fois chacun. */
	public static function send_reminders( bool $force_test = false ): array {
		$s = AC_DB::get_setting( 'reminders' );
		if ( empty( $s['enabled'] ) && ! $force_test ) {
			return array( 'sent' => 0 );
		}
		$to    = sanitize_email( $s['email'] ?: get_option( 'admin_email' ) );
		$today = AC_DB::today();
		$sent  = (array) get_option( AC_DB::opt( 'calendar_reminded' ), array() );
		$due   = array();
		foreach ( self::items( gmdate( 'Y-m-d', strtotime( "$today -60 days" ) ), gmdate( 'Y-m-d', strtotime( "$today +60 days" ) ) ) as $it ) {
			if ( $it['done'] ) {
				continue;
			}
			$stage = $it['days'] <= 0 ? 'jour' : ( $it['days'] <= (int) $s['days_before'] ? 'avant' : null );
			if ( $stage && ( $force_test || empty( $sent[ $it['key'] . ':' . $stage ] ) ) ) {
				$due[]                                 = $it;
				$sent[ $it['key'] . ':' . $stage ] = $today;
			}
		}
		if ( ! $due && ! $force_test ) {
			return array( 'sent' => 0 );
		}
		$url  = AC_Admin::url() . '#/echeances';
		$body = $due ? "Bonjour,\n\nÉchéances comptables et fiscales d'Aurea Media :\n\n" : "Bonjour,\n\nCeci est un e-mail de test : aucune échéance dans les prochains jours.\n";
		foreach ( $due as $it ) {
			$when  = $it['days'] < 0 ? 'EN RETARD de ' . abs( $it['days'] ) . ' jour(s)' : ( 0 === $it['days'] ? "AUJOURD'HUI" : 'dans ' . $it['days'] . ' jour(s)' );
			$body .= '• ' . date_i18n( 'd/m/Y', strtotime( $it['due'] ) ) . " ($when) : {$it['title']}\n  {$it['note']}\n\n";
		}
		$body .= "\nOuvrir le calendrier : $url\n\nAurea Compta";
		$ok = wp_mail( $to, ( $due ? '[Aurea Compta] ' . count( $due ) . ' échéance(s) à traiter' : '[Aurea Compta] Test des rappels' ), $body );
		if ( $ok && ! $force_test ) {
			update_option( AC_DB::opt( 'calendar_reminded' ), $sent, false );
		}
		return array( 'sent' => $ok ? count( $due ) : 0, 'to' => $to, 'ok' => (bool) $ok );
	}
}

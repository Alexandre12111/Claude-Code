<?php
/**
 * États calculés depuis le grand livre : balance, grand livre, journal, compte de résultat, bilan, créances et dettes.
 */
defined( 'ABSPATH' ) || exit;

class AC_Reports {

	private static function filter( ?string $start, ?string $end ): array {
		$sql = '';
		$p   = array();
		if ( $start ) {
			$sql .= ' AND e.date>=?';
			$p[]  = $start;
		}
		if ( $end ) {
			$sql .= ' AND e.date<=?';
			$p[]  = $end;
		}
		return array( $sql, $p );
	}

	public static function trial_balance( ?string $start, ?string $end ): array {
		list( $f, $p ) = self::filter( $start, $end );
		$rows = AC_DB::q( 'SELECT l.account, a.label, SUM(l.debit_cents) debit, SUM(l.credit_cents) credit FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id
			LEFT JOIN {accounts} a ON a.number=l.account WHERE 1=1' . $f . ' GROUP BY l.account, a.label ORDER BY l.account', $p );
		foreach ( $rows as &$r ) {
			$r['solde']           = $r['debit'] - $r['credit'];
			$r['solde_debiteur']  = max( $r['solde'], 0 );
			$r['solde_crediteur'] = max( -$r['solde'], 0 );
		}
		unset( $r );
		$d = array_sum( array_column( $rows, 'debit' ) );
		$c = array_sum( array_column( $rows, 'credit' ) );
		return array( 'rows' => $rows, 'totals' => array( 'debit' => $d, 'credit' => $c, 'equilibre' => $d === $c ) );
	}

	public static function general_ledger( ?string $start, ?string $end, ?string $account = null ): array {
		list( $f, $p ) = self::filter( $start, $end );
		if ( $account ) {
			$f  .= ' AND l.account LIKE ?';
			$p[] = $account . '%';
		}
		$rows = AC_DB::q( 'SELECT l.*, e.date, e.journal, e.number, e.piece_ref, e.status, e.label entry_label, e.document_id, a.label account_label
			FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id LEFT JOIN {accounts} a ON a.number=l.account WHERE 1=1' . $f . ' ORDER BY l.account, e.date, e.id', $p );
		$out = array();
		$i   = -1;
		foreach ( $rows as $r ) {
			if ( $i < 0 || $out[ $i ]['account'] !== $r['account'] ) {
				$out[] = array( 'account' => $r['account'], 'label' => $r['account_label'], 'lines' => array(), 'debit' => 0, 'credit' => 0 );
				$i++;
			}
			$out[ $i ]['debit']  += $r['debit_cents'];
			$out[ $i ]['credit'] += $r['credit_cents'];
			$r['cumul']           = $out[ $i ]['debit'] - $out[ $i ]['credit'];
			$out[ $i ]['lines'][] = $r;
		}
		foreach ( $out as &$o ) {
			$o['solde'] = $o['debit'] - $o['credit'];
		}
		return $out;
	}

	public static function journal( ?string $start, ?string $end, ?string $journal = null ): array {
		list( $f, $p ) = self::filter( $start, $end );
		if ( $journal ) {
			$f  .= ' AND e.journal=?';
			$p[] = $journal;
		}
		$entries = AC_DB::q( 'SELECT e.* FROM {entries} e WHERE 1=1' . $f . ' ORDER BY e.date, e.journal, e.id', $p );
		if ( ! $entries ) {
			return array();
		}
		$ids   = implode( ',', array_map( 'intval', array_column( $entries, 'id' ) ) );
		$lines = array();
		foreach ( AC_DB::q( "SELECT l.*, a.label account_label FROM {entry_lines} l LEFT JOIN {accounts} a ON a.number=l.account WHERE entry_id IN ($ids) ORDER BY l.id" ) as $l ) {
			$lines[ $l['entry_id'] ][] = $l;
		}
		foreach ( $entries as &$e ) {
			$e['lines'] = $lines[ $e['id'] ] ?? array();
		}
		return $entries;
	}

	const IS_SECTIONS = array(
		array( 'produits_exploitation', "Produits d'exploitation", array( '70', '71', '72', '74', '75', '781' ) ),
		array( 'charges_exploitation', "Charges d'exploitation", array( '60', '61', '62', '63', '64', '65', '681' ) ),
		array( 'produits_financiers', 'Produits financiers', array( '76', '786' ) ),
		array( 'charges_financieres', 'Charges financières', array( '66', '686' ) ),
		array( 'produits_exceptionnels', 'Produits exceptionnels', array( '77', '787' ) ),
		array( 'charges_exceptionnelles', 'Charges exceptionnelles', array( '67', '687' ) ),
		array( 'impot', 'Impôt sur les bénéfices', array( '69' ) ),
	);

	public static function income_statement( ?string $start, ?string $end ): array {
		$tb       = self::trial_balance( $start, $end )['rows'];
		$sections = array();
		$s        = array();
		foreach ( self::IS_SECTIONS as list( $key, $label, $prefixes ) ) {
			$lines = array();
			foreach ( $tb as $r ) {
				if ( in_array( $r['account'][0], array( '6', '7' ), true ) ) {
					foreach ( $prefixes as $px ) {
						if ( 0 === strpos( $r['account'], $px ) ) {
							$lines[] = array( 'account' => $r['account'], 'label' => $r['label'], 'amount' => '7' === $r['account'][0] ? -$r['solde'] : $r['solde'] );
							break;
						}
					}
				}
			}
			$total            = array_sum( array_column( $lines, 'amount' ) );
			$sections[ $key ] = array( 'label' => $label, 'lines' => $lines, 'total' => $total );
			$s[ $key ]        = $total;
		}
		$expl = $s['produits_exploitation'] - $s['charges_exploitation'];
		$fin  = $s['produits_financiers'] - $s['charges_financieres'];
		$exc  = $s['produits_exceptionnels'] - $s['charges_exceptionnelles'];
		$ca   = 0;
		foreach ( $sections['produits_exploitation']['lines'] as $l ) {
			if ( 0 === strpos( $l['account'], '70' ) ) {
				$ca += $l['amount'];
			}
		}
		return array( 'sections' => $sections, 'resultat_exploitation' => $expl, 'resultat_financier' => $fin, 'resultat_exceptionnel' => $exc,
			'resultat_avant_impot' => $expl + $fin + $exc, 'impot' => $s['impot'], 'resultat_net' => $expl + $fin + $exc - $s['impot'],
			'total_produits' => $s['produits_exploitation'] + $s['produits_financiers'] + $s['produits_exceptionnels'],
			'total_charges' => $s['charges_exploitation'] + $s['charges_financieres'] + $s['charges_exceptionnelles'] + $s['impot'], 'chiffre_affaires' => $ca );
	}

	private static function classify( string $a, int $solde ): ?array {
		$deb = $solde > 0;
		$p   = function ( $x ) use ( $a ) {
			return 0 === strpos( $a, $x );
		};
		if ( $p( '109' ) ) return array( 'actif', 'Capital souscrit non appelé' );
		if ( $p( '28' ) || $p( '29' ) ) return array( 'actif', 'Amortissements et dépréciations' );
		if ( $p( '20' ) ) return array( 'actif', 'Immobilisations incorporelles' );
		if ( $p( '21' ) || $p( '23' ) ) return array( 'actif', 'Immobilisations corporelles' );
		if ( $p( '26' ) || $p( '27' ) ) return array( 'actif', 'Immobilisations financières' );
		if ( $p( '3' ) ) return array( 'actif', 'Stocks' );
		if ( $p( '101' ) ) return array( 'passif', 'Capital social' );
		if ( $p( '106' ) ) return array( 'passif', 'Réserves' );
		if ( $p( '11' ) ) return array( 'passif', 'Report à nouveau' );
		if ( $p( '12' ) ) return array( 'passif', "Résultat de l'exercice" );
		if ( $p( '16' ) ) return array( 'passif', 'Emprunts et dettes auprès des établissements de crédit' );
		if ( $p( '1' ) ) return array( 'passif', 'Autres fonds propres et provisions' );
		if ( $p( '486' ) ) return array( 'actif', "Charges constatées d'avance" );
		if ( $p( '487' ) ) return array( 'passif', "Produits constatés d'avance" );
		if ( $p( '455' ) ) return $deb ? array( 'actif', 'Autres créances (compte courant débiteur)' ) : array( 'passif', "Emprunts et dettes financières divers (compte courant d'associé)" );
		if ( $p( '41' ) ) return $deb ? array( 'actif', 'Créances clients' ) : array( 'passif', 'Avances et acomptes reçus / clients créditeurs' );
		if ( $p( '40' ) ) return $deb ? array( 'actif', 'Autres créances' ) : array( 'passif', 'Dettes fournisseurs' );
		if ( $p( '42' ) || $p( '43' ) || $p( '44' ) ) return $deb ? array( 'actif', 'Autres créances (État, TVA)' ) : array( 'passif', 'Dettes fiscales et sociales' );
		if ( $p( '4' ) ) return $deb ? array( 'actif', 'Autres créances' ) : array( 'passif', 'Autres dettes' );
		if ( $p( '5' ) ) return $deb ? array( 'actif', 'Disponibilités' ) : array( 'passif', 'Concours bancaires courants' );
		return null;
	}

	const ACTIF_ORDER  = array( 'Capital souscrit non appelé', 'Immobilisations incorporelles', 'Immobilisations corporelles', 'Immobilisations financières',
		'Amortissements et dépréciations', 'Stocks', 'Créances clients', 'Autres créances (État, TVA)', 'Autres créances', 'Autres créances (compte courant débiteur)',
		'Disponibilités', "Charges constatées d'avance" );
	const PASSIF_ORDER = array( 'Capital social', 'Réserves', 'Report à nouveau', "Résultat de l'exercice", 'Autres fonds propres et provisions',
		'Emprunts et dettes auprès des établissements de crédit', "Emprunts et dettes financières divers (compte courant d'associé)",
		'Avances et acomptes reçus / clients créditeurs', 'Dettes fournisseurs', 'Dettes fiscales et sociales', 'Autres dettes', 'Concours bancaires courants',
		"Produits constatés d'avance" );

	public static function balance_sheet( array $fy ): array {
		$tb     = self::trial_balance( $fy['start_date'], $fy['end_date'] )['rows'];
		$sides  = array( 'actif' => array(), 'passif' => array() );
		$result = 0;
		foreach ( $tb as $r ) {
			if ( in_array( $r['account'][0], array( '6', '7' ), true ) ) {
				$result -= $r['solde'];
				continue;
			}
			if ( ! $r['solde'] ) {
				continue;
			}
			$c = self::classify( $r['account'], $r['solde'] );
			if ( ! $c ) {
				continue;
			}
			list( $side, $rub ) = $c;
			$amount = 'actif' === $side ? $r['solde'] : -$r['solde'];
			$sides[ $side ][ $rub ] = $sides[ $side ][ $rub ] ?? array( 'total' => 0, 'accounts' => array() );
			$sides[ $side ][ $rub ]['total'] += $amount;
			$sides[ $side ][ $rub ]['accounts'][] = array( 'account' => $r['account'], 'label' => $r['label'], 'amount' => $amount );
		}
		$k = "Résultat de l'exercice";
		$sides['passif'][ $k ] = $sides['passif'][ $k ] ?? array( 'total' => 0, 'accounts' => array() );
		$sides['passif'][ $k ]['total'] += $result;
		$sides['passif'][ $k ]['accounts'][] = array( 'account' => '6/7', 'label' => 'Produits moins charges', 'amount' => $result );
		$order = function ( $d, $o ) {
			$out = array();
			foreach ( $o as $rub ) {
				if ( isset( $d[ $rub ] ) ) {
					$out[] = array( 'rubrique' => $rub ) + $d[ $rub ];
				}
			}
			foreach ( $d as $rub => $v ) {
				if ( ! in_array( $rub, $o, true ) ) {
					$out[] = array( 'rubrique' => $rub ) + $v;
				}
			}
			return $out;
		};
		$ta = array_sum( array_column( $sides['actif'], 'total' ) );
		$tp = array_sum( array_column( $sides['passif'], 'total' ) );
		return array( 'actif' => $order( $sides['actif'], self::ACTIF_ORDER ), 'passif' => $order( $sides['passif'], self::PASSIF_ORDER ),
			'total_actif' => $ta, 'total_passif' => $tp, 'equilibre' => $ta === $tp, 'resultat' => $result );
	}

	public static function receivables_payables( string $end ): array {
		$rows  = AC_DB::q( "SELECT l.account, l.aux, MAX(l.aux_label) aux_label, SUM(l.debit_cents-l.credit_cents) solde FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id
			WHERE e.date<=? AND (l.account LIKE '40%' OR l.account LIKE '41%' OR l.account LIKE '42%' OR l.account LIKE '43%' OR l.account LIKE '44%' OR l.account LIKE '455%'
			OR l.account LIKE '486%' OR l.account LIKE '487%') GROUP BY l.account, l.aux HAVING solde<>0 ORDER BY l.account", array( $end ) );
		$names = array();
		foreach ( AC_DB::q( 'SELECT id, name, kind FROM {parties}' ) as $p ) {
			$names[ sprintf( '%s%04d', 'client' === $p['kind'] ? 'C' : 'F', $p['id'] ) ] = $p['name'];
		}
		$labels = array_column( AC_DB::q( 'SELECT number, label FROM {accounts}' ), 'label', 'number' );
		$cre    = array();
		$det    = array();
		foreach ( $rows as $r ) {
			$r['tiers'] = $names[ $r['aux'] ?? '' ] ?? ( $r['aux_label'] ?: '' );
			$r['label'] = $labels[ $r['account'] ] ?? '';
			if ( $r['solde'] > 0 ) {
				$cre[] = $r;
			} else {
				$r['solde'] = -$r['solde'];
				$det[]      = $r;
			}
		}
		return array( 'creances' => $cre, 'dettes' => $det, 'note' => "Toutes les créances et dettes sont à moins d'un an, sauf indication contraire." );
	}
}

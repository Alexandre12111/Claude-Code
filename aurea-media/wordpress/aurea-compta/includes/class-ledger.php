<?php
/**
 * Moteur d'écritures en partie double.
 *
 * * chaque écriture est équilibrée (refus sinon) ;
 * * une écriture « brouillon » est régénérée quand sa source change ;
 * * une écriture « validée » n'est jamais modifiée : contrepassation datée et motivée,
 *   puis nouvelle écriture. Les écritures validées sont chaînées par empreinte SHA-256 :
 *   toute modification directe en base est détectée par le contrôle d'intégrité.
 */
defined( 'ABSPATH' ) || exit;

class AC_Line {
	public $account, $debit = 0, $credit = 0, $label, $aux, $aux_label, $vat_code, $vat_base, $lettrage;

	public function __construct( string $account, int $debit = 0, int $credit = 0, array $o = array() ) {
		$this->account = $account;
		$this->debit   = $debit;
		$this->credit  = $credit;
		foreach ( $o as $k => $v ) {
			$this->$k = $v;
		}
	}
}

/** Ligne au débit (montant négatif : basculée au crédit). */
function ac_d( string $account, int $amount, array $o = array() ): AC_Line {
	return $amount >= 0 ? new AC_Line( $account, $amount, 0, $o ) : new AC_Line( $account, 0, -$amount, $o );
}

/** Ligne au crédit (montant négatif : basculée au débit). */
function ac_c( string $account, int $amount, array $o = array() ): AC_Line {
	return $amount >= 0 ? new AC_Line( $account, 0, $amount, $o ) : new AC_Line( $account, -$amount, 0, $o );
}

class AC_Ledger {

	public static function fiscal_year_for( string $date ): ?array {
		return AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE start_date<=? AND end_date>=?', array( $date, $date ) );
	}

	public static function check_period( string $date, ?string $reason = null ): void {
		$fy = self::fiscal_year_for( $date );
		if ( ! $fy ) {
			throw new AC_Error( "Aucun exercice comptable ne couvre la date $date. Créez l'exercice dans Paramètres." );
		}
		if ( 'closed' === $fy['status'] ) {
			throw new AC_Error( "L'exercice {$fy['start_date']} au {$fy['end_date']} est clôturé : aucune écriture ne peut y être ajoutée." );
		}
		if ( AC_DB::q1( 'SELECT month FROM {month_closings} WHERE month=?', array( substr( $date, 0, 7 ) ) ) ) {
			AC_DB::audit( 'regularisation_mois_cloture', 'periode', null, null, array( 'mois' => substr( $date, 0, 7 ) ),
				$reason ?: 'Écriture ajoutée sur un mois déjà vérifié et clôturé' );
		}
	}

	/** @param AC_Line[] $lines */
	public static function create_entry( string $journal, string $date, string $label, array $lines, array $o = array() ): ?int {
		if ( ! isset( AC_Rules::journals()[ $journal ] ) ) {
			throw new AC_Error( "Journal inconnu $journal" );
		}
		$clean = array();
		foreach ( $lines as $l ) {
			if ( $l->debit < 0 || $l->credit < 0 ) {
				throw new AC_Error( 'Montant négatif dans une ligne' );
			}
			if ( $l->debit && $l->credit ) {
				$net       = $l->debit - $l->credit;
				$l->debit  = max( $net, 0 );
				$l->credit = max( -$net, 0 );
			}
			if ( 0 === $l->debit && 0 === $l->credit ) {
				continue;
			}
			$clean[] = $l;
		}
		if ( ! $clean ) {
			return null;
		}
		$td = array_sum( array_map( function ( $l ) { return $l->debit; }, $clean ) );
		$tc = array_sum( array_map( function ( $l ) { return $l->credit; }, $clean ) );
		if ( $td !== $tc ) {
			throw new AC_Error( "Écriture déséquilibrée « $label » : débit " . AC_Money::fmt( $td ) . ' / crédit ' . AC_Money::fmt( $tc ) );
		}
		self::check_period( $date, $o['reason'] ?? null );
		foreach ( $clean as $l ) {
			if ( ! AC_DB::q1( 'SELECT number FROM {accounts} WHERE number=?', array( $l->account ) ) ) {
				throw new AC_Error( "Compte {$l->account} absent du plan comptable" );
			}
		}
		$id = AC_DB::insert( 'entries', array(
			'journal' => $journal, 'date' => $date, 'label' => mb_substr( $label, 0, 250 ), 'piece_ref' => isset( $o['piece_ref'] ) ? mb_substr( (string) $o['piece_ref'], 0, 99 ) : null,
			'piece_date' => $o['piece_date'] ?? $date, 'document_id' => $o['document_id'] ?? null, 'source_type' => $o['source_type'] ?? null,
			'source_id' => $o['source_id'] ?? null, 'status' => 'brouillon', 'created_at' => AC_DB::now(),
		) );
		foreach ( $clean as $l ) {
			AC_DB::insert( 'entry_lines', array(
				'entry_id' => $id, 'account' => $l->account, 'aux' => $l->aux, 'aux_label' => $l->aux_label,
				'label' => mb_substr( (string) ( $l->label ?: $label ), 0, 250 ), 'debit_cents' => $l->debit, 'credit_cents' => $l->credit,
				'vat_code' => $l->vat_code, 'vat_base_cents' => $l->vat_base, 'lettrage' => $l->lettrage,
			) );
		}
		if ( ( $o['status'] ?? 'brouillon' ) === 'valide' ) {
			self::validate_entry( $id );
		}
		return $id;
	}

	private static function entry_hash( array $e, array $lines, string $prev ): string {
		$parts = array( $prev, $e['journal'], $e['number'], $e['date'], $e['label'], $e['piece_ref'] );
		foreach ( $lines as $l ) {
			$parts[] = $l['account'] . '|' . $l['debit_cents'] . '|' . $l['credit_cents'];
		}
		return hash( 'sha256', implode( "\n", array_map( 'strval', $parts ) ) );
	}

	public static function validate_entry( int $id ): void {
		$e = AC_DB::q1( 'SELECT * FROM {entries} WHERE id=?', array( $id ) );
		if ( ! $e ) {
			throw new AC_Error( 'Écriture introuvable' );
		}
		if ( 'valide' === $e['status'] ) {
			return;
		}
		$t = AC_DB::q1( 'SELECT SUM(debit_cents) d, SUM(credit_cents) c FROM {entry_lines} WHERE entry_id=?', array( $id ) );
		if ( (int) $t['d'] !== (int) $t['c'] || ! (int) $t['d'] ) {
			throw new AC_Error( 'Écriture déséquilibrée ou vide : validation impossible' );
		}
		$fy = self::fiscal_year_for( $e['date'] );
		if ( ! $fy || 'closed' === $fy['status'] ) {
			throw new AC_Error( 'Exercice clôturé ou inexistant' );
		}
		$prefix = $e['journal'] . substr( $fy['end_date'], 0, 4 ) . '-';
		$last   = AC_DB::val( 'SELECT number FROM {entries} WHERE number LIKE ? ORDER BY number DESC LIMIT 1', array( $prefix . '%' ) );
		$seq    = $last ? (int) substr( $last, strrpos( $last, '-' ) + 1 ) + 1 : 1;
		$e['number'] = $prefix . str_pad( (string) $seq, 5, '0', STR_PAD_LEFT );
		$last_v = AC_DB::q1( "SELECT vseq, hash FROM {entries} WHERE status='valide' ORDER BY vseq DESC LIMIT 1" );
		$prev   = $last_v ? (string) $last_v['hash'] : '';
		$lines  = AC_DB::q( 'SELECT * FROM {entry_lines} WHERE entry_id=? ORDER BY id', array( $id ) );
		AC_DB::exec( "UPDATE {entries} SET status='valide', number=?, validated_at=?, vseq=?, hash=? WHERE id=?",
			array( $e['number'], AC_DB::now(), ( $last_v ? (int) $last_v['vseq'] : 0 ) + 1, self::entry_hash( $e, $lines, $prev ), $id ) );
		AC_DB::audit( 'validation', 'ecriture', $id, null, array( 'numero' => $e['number'] ) );
	}

	/** Contrepassation (sens inverse), elle même validée. */
	public static function reverse_entry( int $id, ?string $date, string $reason ): int {
		$e = AC_DB::q1( 'SELECT * FROM {entries} WHERE id=?', array( $id ) );
		if ( $e['reversed_by'] ) {
			return (int) $e['reversed_by'];
		}
		$d  = $date ?: $e['date'];
		$fy = self::fiscal_year_for( $d );
		if ( ! $fy || 'closed' === $fy['status'] ) {
			$d = AC_DB::today();
		}
		$lines = array();
		foreach ( AC_DB::q( 'SELECT * FROM {entry_lines} WHERE entry_id=? ORDER BY id', array( $id ) ) as $l ) {
			$lines[] = new AC_Line( $l['account'], $l['credit_cents'], $l['debit_cents'], array(
				'label' => $l['label'], 'aux' => $l['aux'], 'aux_label' => $l['aux_label'], 'vat_code' => $l['vat_code'],
				'vat_base' => null === $l['vat_base_cents'] ? null : -$l['vat_base_cents'], 'lettrage' => $l['lettrage'],
			) );
		}
		$rev = self::create_entry( $e['journal'], $d, mb_substr( 'Contrepassation ' . ( $e['number'] ?: $e['id'] ) . ' : ' . $e['label'], 0, 250 ), $lines, array(
			'piece_ref' => $e['piece_ref'], 'document_id' => $e['document_id'], 'source_type' => $e['source_type'] . ':contrepassation',
			'source_id' => $e['source_id'], 'status' => 'valide', 'reason' => $reason,
		) );
		AC_DB::exec( 'UPDATE {entries} SET reversed_by=? WHERE id=?', array( $rev, $id ) );
		AC_DB::exec( 'UPDATE {entries} SET reversal_of=? WHERE id=?', array( $id, $rev ) );
		AC_DB::audit( 'contrepassation', 'ecriture', $id, null, array( 'contrepassation' => $rev ), $reason );
		return $rev;
	}

	/** Supprime les brouillons d'une source, contrepasse ses écritures validées. */
	public static function clear_source( string $type, int $id, string $reason, ?string $date = null ): void {
		foreach ( AC_DB::q( 'SELECT * FROM {entries} WHERE source_type=? AND source_id=? AND reversed_by IS NULL', array( $type, $id ) ) as $e ) {
			if ( 'brouillon' === $e['status'] ) {
				$snap = AC_DB::q( 'SELECT account,debit_cents,credit_cents FROM {entry_lines} WHERE entry_id=?', array( $e['id'] ) );
				AC_DB::exec( 'DELETE FROM {entry_lines} WHERE entry_id=?', array( $e['id'] ) );
				AC_DB::exec( 'DELETE FROM {entries} WHERE id=?', array( $e['id'] ) );
				AC_DB::audit( 'suppression_brouillon', 'ecriture', $e['id'], array( 'entete' => $e, 'lignes' => $snap ), null, $reason );
			} else {
				self::reverse_entry( $e['id'], $date, $reason );
			}
		}
	}

	public static function source_entries( string $type, int $id ): array {
		return AC_DB::q( 'SELECT * FROM {entries} WHERE source_type=? AND source_id=? AND reversed_by IS NULL ORDER BY id', array( $type, $id ) );
	}

	public static function entry_with_lines( int $id ): ?array {
		$e = AC_DB::q1( 'SELECT * FROM {entries} WHERE id=?', array( $id ) );
		if ( $e ) {
			$e['lines'] = AC_DB::q( 'SELECT l.*, a.label AS account_label FROM {entry_lines} l LEFT JOIN {accounts} a ON a.number=l.account WHERE entry_id=? ORDER BY l.id', array( $id ) );
		}
		return $e;
	}

	/** Solde débiteur (débit − crédit) des comptes commençant par le préfixe. */
	public static function balance( string $prefix, ?string $to = null, ?string $from = null ): int {
		$sql = 'SELECT COALESCE(SUM(l.debit_cents - l.credit_cents),0) FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id WHERE l.account LIKE ?';
		$p   = array( $prefix . '%' );
		if ( $to ) {
			$sql .= ' AND e.date<=?';
			$p[]  = $to;
		}
		if ( $from ) {
			$sql .= ' AND e.date>=?';
			$p[]  = $from;
		}
		return AC_DB::ival( $sql, $p );
	}

	/** Contrôle d'intégrité : équilibre de chaque écriture et chaîne d'empreintes des écritures validées. */
	public static function check_integrity(): array {
		$bad = AC_DB::q( 'SELECT e.id, e.label, SUM(l.debit_cents) d, SUM(l.credit_cents) c FROM {entries} e JOIN {entry_lines} l ON l.entry_id=e.id GROUP BY e.id, e.label HAVING d<>c' );
		$t   = AC_DB::q1( 'SELECT COALESCE(SUM(debit_cents),0) debit, COALESCE(SUM(credit_cents),0) credit FROM {entry_lines}' );
		$tampered = array();
		$prev     = '';
		foreach ( AC_DB::q( "SELECT * FROM {entries} WHERE status='valide' ORDER BY vseq" ) as $e ) {
			$lines = AC_DB::q( 'SELECT * FROM {entry_lines} WHERE entry_id=? ORDER BY id', array( $e['id'] ) );
			if ( self::entry_hash( $e, $lines, $prev ) !== $e['hash'] ) {
				$tampered[] = $e['number'];
			}
			$prev = (string) $e['hash'];
		}
		return array( 'ok' => ! $bad && (int) $t['debit'] === (int) $t['credit'] && ! $tampered, 'unbalanced' => $bad,
			'tampered' => $tampered, 'total_debit' => (int) $t['debit'], 'total_credit' => (int) $t['credit'] );
	}
}

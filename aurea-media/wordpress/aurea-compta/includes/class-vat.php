<?php
/**
 * Moteur TVA : périodes, calcul, rubriques CA3 / CA12, validation, report de crédit.
 *
 * Le calcul part des comptes de TVA du grand livre (445710, 445200, 445620, 445660, 445662),
 * mouvementés uniquement à la date d'exigibilité : chaque montant renvoie à ses pièces.
 * Les numéros de lignes reprennent le formulaire 3310-CA3-SD : vérifier la notice de l'année.
 */
defined( 'ABSPATH' ) || exit;

class AC_Vat {

	const RATE_LINES = array( '20' => array( '08', 'Taux normal 20 %' ), '10' => array( '9B', 'Taux réduit 10 %' ),
		'5.5' => array( '09', 'Taux réduit 5,5 %' ), '2.1' => array( 'P2', 'Taux particulier 2,1 %' ) );

	private static function rate_of( ?string $code ): string {
		$r = preg_replace( '/[^0-9.]/', '', (string) $code );
		return '' === $r ? '0' : $r;
	}

	private static function period_end( int $y, int $m, int $step ): string {
		return ( new DateTime( sprintf( '%04d-%02d-01', $y, $m ) ) )->modify( '+' . $step . ' month -1 day' )->format( 'Y-m-d' );
	}

	public static function periods( int $year ): array {
		$tax = AC_DB::get_setting( 'tax' );
		$per = 'reel_simplifie' === $tax['vat_regime'] ? 'annuelle' : $tax['vat_periodicity'];
		$out = array();
		if ( 'annuelle' === $per ) {
			$fy  = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE YEAR(end_date)=?', array( $year ) );
			$s   = $fy ? $fy['start_date'] : "$year-01-01";
			$e   = $fy ? $fy['end_date'] : "$year-12-31";
			$out[] = array( 'start' => $s, 'end' => $e, 'label' => "Exercice $year", 'form' => 'CA12' );
		} else {
			$step = 'mensuelle' === $per ? 1 : 3;
			for ( $m = 1; $m <= 12; $m += $step ) {
				$out[] = array( 'start' => sprintf( '%04d-%02d-01', $year, $m ), 'end' => self::period_end( $year, $m, $step ),
					'label' => 1 === $step ? sprintf( '%02d/%04d', $m, $year ) : 'T' . ( intdiv( $m - 1, 3 ) + 1 ) . " $year", 'form' => 'CA3' );
			}
		}
		foreach ( $out as &$p ) {
			$r = AC_DB::q1( 'SELECT id, status FROM {vat_returns} WHERE period_start=? AND period_end=?', array( $p['start'], $p['end'] ) );
			$p['return_id'] = $r ? $r['id'] : null;
			$p['status']    = $r ? $r['status'] : null;
		}
		return $out;
	}

	private static function lines( string $start, string $end, array $accounts ): array {
		$in = implode( ',', array_map( function ( $a ) { return "'" . esc_sql( $a ) . "'"; }, $accounts ) );
		return AC_DB::q( "SELECT l.*, e.date, e.journal, e.number, e.label entry_label, e.piece_ref, e.source_type, e.source_id, e.status, e.document_id
			FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id WHERE e.date BETWEEN ? AND ? AND IFNULL(e.source_type,'') NOT LIKE ? AND l.account IN ($in)",
			array( $start, $end, 'tva_declaration%' ) );
	}

	private static function piece( array $r ): ?string {
		$tag = (string) $r['lettrage'];
		if ( preg_match( '/^V(\d+)$/', $tag, $m ) ) {
			$v = AC_DB::val( 'SELECT number FROM {sales_invoices} WHERE id=?', array( (int) $m[1] ) );
			return $v ?: $r['piece_ref'];
		}
		if ( preg_match( '/^D(\d+)$/', $tag, $m ) ) {
			$x = AC_DB::q1( 'SELECT invoice_number, supplier_name FROM {expenses} WHERE id=?', array( (int) $m[1] ) );
			return $x ? ( $x['invoice_number'] ?: $x['supplier_name'] ) : $r['piece_ref'];
		}
		return $r['piece_ref'];
	}

	private static function detail( array $rows, int $sign ): array {
		return array_map( function ( $r ) use ( $sign ) {
			return array( 'date' => $r['date'], 'libelle' => $r['entry_label'], 'piece' => self::piece( $r ), 'source_type' => $r['source_type'],
				'source_id' => $r['source_id'], 'document_id' => $r['document_id'], 'base_cents' => (int) $r['vat_base_cents'],
				'taxe_cents' => $sign * ( $r['credit_cents'] - $r['debit_cents'] ), 'provisoire' => 'brouillon' === $r['status'] );
		}, $rows );
	}

	public static function compute( string $start, string $end ): array {
		$collected = self::lines( $start, $end, array( '445710' ) );
		$autoliq   = self::lines( $start, $end, array( '445200' ) );
		$ded_imm   = self::lines( $start, $end, array( '445620' ) );
		$ded_oth   = self::lines( $start, $end, array( '445660', '445662' ) );
		$bases     = AC_DB::q( "SELECT l.*, e.date, e.label entry_label, e.piece_ref, e.source_type, e.source_id, e.document_id, e.status FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id
			WHERE e.date BETWEEN ? AND ? AND l.vat_code IN ('E1','E2') AND IFNULL(e.source_type,'') NOT LIKE ?", array( $start, $end, 'tva_declaration%' ) );
		$L   = array();
		$put = function ( $code, $label, $base = null, $tax = null, $det = array() ) use ( &$L ) {
			$L[ $code ] = array( 'code' => $code, 'libelle' => $label, 'base_cents' => $base, 'taxe_cents' => $tax, 'detail' => $det );
		};
		$sumb = function ( $rows ) {
			return array_sum( array_map( function ( $r ) { return (int) $r['vat_base_cents']; }, $rows ) );
		};
		$filt = function ( $rows, $pfx ) {
			return array_values( array_filter( $rows, function ( $r ) use ( $pfx ) { return 0 === strpos( (string) $r['vat_code'], $pfx ); } ) );
		};
		$put( 'A1', 'Ventes, prestations de services', $sumb( $collected ), null, self::detail( $collected, 1 ) );
		$ue  = $filt( $autoliq, 'AL_UE' );
		$hue = $filt( $autoliq, 'AL_HUE' );
		$put( 'A3', 'Achats de prestations de services intracommunautaires (art. 283-2 CGI)', $sumb( $ue ), null, self::detail( $ue, 1 ) );
		$put( 'B4', "Achats de biens ou prestations auprès d'un assujetti non établi en France (hors UE)", $sumb( $hue ), null, self::detail( $hue, 1 ) );
		$e1 = $filt( $bases, 'E1' );
		$e2 = $filt( $bases, 'E2' );
		$put( 'E1', 'Exportations hors UE', $sumb( $e1 ), null, self::detail( $e1, 1 ) );
		$put( 'E2', 'Autres opérations non imposables (dont prestations intracommunautaires B2B)', $sumb( $e2 ), null, self::detail( $e2, 1 ) );
		$by = array();
		foreach ( array_merge( $collected, $autoliq ) as $r ) {
			$rt = self::rate_of( $r['vat_code'] );
			$by[ $rt ] = $by[ $rt ] ?? array( 'base' => 0, 'tax' => 0, 'rows' => array() );
			$by[ $rt ]['base'] += (int) $r['vat_base_cents'];
			$by[ $rt ]['tax']  += $r['credit_cents'] - $r['debit_cents'];
			$by[ $rt ]['rows'][] = $r;
		}
		uksort( $by, function ( $a, $b ) { return AC_Money::rate_bp( $b ) <=> AC_Money::rate_bp( $a ); } );
		$brute = 0;
		foreach ( $by as $rt => $g ) {
			list( $code, $label ) = self::RATE_LINES[ $rt ] ?? array( "T$rt", "Taux $rt %" );
			$put( $code, $label, $g['base'], $g['tax'], self::detail( $g['rows'], 1 ) );
			$brute += $g['tax'];
		}
		$put( '16', 'Total de la TVA brute due', null, $brute );
		$put( '17', 'Dont TVA sur acquisitions intracommunautaires (biens)', null, 0 );
		$t19 = array_sum( array_map( function ( $r ) { return $r['debit_cents'] - $r['credit_cents']; }, $ded_imm ) );
		$t20 = array_sum( array_map( function ( $r ) { return $r['debit_cents'] - $r['credit_cents']; }, $ded_oth ) );
		$put( '19', 'TVA déductible : biens constituant des immobilisations', null, $t19, self::detail( $ded_imm, -1 ) );
		$put( '20', 'TVA déductible : autres biens et services', null, $t20, self::detail( $ded_oth, -1 ) );
		$adj = AC_DB::q1( 'SELECT data FROM {vat_returns} WHERE period_start=? AND period_end=?', array( $start, $end ) );
		$t21 = $adj ? (int) ( json_decode( $adj['data'], true )['ajustements']['21'] ?? 0 ) : 0;
		$put( '21', 'Autre TVA à déduire (régularisations)', null, $t21 );
		$prev = AC_DB::q1( "SELECT * FROM {vat_returns} WHERE period_end<? AND status IN ('validee','deposee') ORDER BY period_end DESC LIMIT 1", array( $start ) );
		$t22  = $prev ? (int) ( json_decode( $prev['data'], true )['lignes']['27']['taxe_cents'] ?? 0 ) : 0;
		$put( '22', 'Report du crédit apparaissant ligne 27 de la précédente déclaration', null, $t22 );
		$t23 = $t19 + $t20 + $t21 + $t22;
		$put( '23', 'Total TVA déductible', null, $t23 );
		if ( $brute >= $t23 ) {
			$put( '28', 'TVA nette due (ligne 16 - ligne 23)', null, $brute - $t23 );
			$put( '25', 'Crédit de TVA (ligne 23 - ligne 16)', null, 0 );
			$put( '27', 'Crédit à reporter', null, 0 );
		} else {
			$put( '28', 'TVA nette due', null, 0 );
			$put( '25', 'Crédit de TVA (ligne 23 - ligne 16)', null, $t23 - $brute );
			$put( '27', 'Crédit à reporter (sauf demande de remboursement ligne 26)', null, $t23 - $brute );
		}
		$put( '32', 'Total à payer', null, $L['28']['taxe_cents'] );
		$nonrec = AC_DB::q( "SELECT id, date, supplier_name, label, vat_cents, vat_recovery FROM {expenses} WHERE date BETWEEN ? AND ? AND vat_regime='fr' AND vat_cents>0 AND vat_recovery<100",
			array( $start, $end ) );
		$nr = 0;
		foreach ( $nonrec as $n ) {
			$nr += $n['vat_cents'] - AC_Money::pct_of( $n['vat_cents'], $n['vat_recovery'] );
		}
		$drafts = 0;
		foreach ( array( 'A1', '19', '20' ) as $k ) {
			foreach ( $L[ $k ]['detail'] as $dd ) {
				$drafts += $dd['provisoire'] ? 1 : 0;
			}
		}
		return array( 'start' => $start, 'end' => $end, 'lignes' => $L, 'tva_brute_cents' => $brute, 'tva_deductible_cents' => $t23,
			'a_payer_cents' => $L['28']['taxe_cents'], 'credit_cents' => $L['25']['taxe_cents'], 'non_recuperable_cents' => $nr, 'non_recuperable' => $nonrec,
			'tva_collectee_en_attente_cents' => -AC_Ledger::balance( '445870', $end ), 'tva_deductible_en_attente_cents' => AC_Ledger::balance( '445860', $end ),
			'lignes_provisoires' => $drafts );
	}

	public static function ca12_view( array $data, string $year_end ): array {
		$L        = $data['lignes'];
		$acomptes = AC_Ledger::balance( '445810', $year_end );
		$net      = $data['tva_brute_cents'] - $data['tva_deductible_cents'];
		$base     = max( $net + $L['19']['taxe_cents'], 0 );
		$jul      = AC_Rules::get( 'ca12_acompte_juillet_pct', $year_end );
		$dec      = AC_Rules::get( 'ca12_acompte_decembre_pct', $year_end );
		return array(
			'rubriques' => array(
				array( 'libelle' => 'Opérations imposables (base HT)', 'montant_cents' => $L['A1']['base_cents'] ),
				array( 'libelle' => 'TVA brute due', 'montant_cents' => $data['tva_brute_cents'] ),
				array( 'libelle' => 'TVA déductible sur immobilisations', 'montant_cents' => $L['19']['taxe_cents'] ),
				array( 'libelle' => 'TVA déductible sur autres biens et services', 'montant_cents' => $L['20']['taxe_cents'] ),
				array( 'libelle' => 'Total TVA déductible', 'montant_cents' => $data['tva_deductible_cents'] ),
				array( 'libelle' => "Acomptes versés dans l'année", 'montant_cents' => $acomptes ),
				array( 'libelle' => $net - $acomptes >= 0 ? 'Solde à payer' : 'Crédit de TVA', 'montant_cents' => abs( $net - $acomptes ) ),
			),
			'acomptes_annee_suivante' => $base <= AC_Rules::get( 'ca12_seuil_acomptes_cents', $year_end ) ? array() : array(
				array( 'echeance' => 'juillet', 'pct' => $jul, 'montant_cents' => AC_Money::pct_of( $base, $jul ) ),
				array( 'echeance' => 'décembre', 'pct' => $dec, 'montant_cents' => AC_Money::pct_of( $base, $dec ) ),
			),
			'note' => "Déclaration annuelle CA12 (3517-S-SD) à déposer au plus tard le 2e jour ouvré suivant le 1er mai (exercice civil). Acomptes semestriels de 55 % (juillet) et 40 % (décembre) de la TVA due de l'année précédente, si celle ci dépasse 1 000 €.",
		);
	}

	private static function strip( array $L ): array {
		$out = array();
		foreach ( $L as $k => $v ) {
			unset( $v['detail'] );
			$out[ $k ] = $v;
		}
		return $out;
	}

	/** « Préparer ma déclaration » : contrôles + calcul + enregistrement. */
	public static function prepare( string $start, string $end ): array {
		$data   = self::compute( $start, $end );
		$issues = AC_Checks::period_checks( $start, $end );
		$block  = count( array_filter( $issues, function ( $i ) { return 'bloquant' === $i['niveau']; } ) );
		$form   = 'reel_simplifie' === AC_DB::get_setting( 'tax' )['vat_regime'] ? 'CA12' : 'CA3';
		$payload = array( 'lignes' => self::strip( $data['lignes'] ), 'a_payer_cents' => $data['a_payer_cents'], 'credit_cents' => $data['credit_cents'],
			'anomalies' => $issues, 'definitive' => ! $block && ! $data['lignes_provisoires'] );
		if ( 'CA12' === $form ) {
			$payload['ca12'] = self::ca12_view( $data, $end );
		}
		$existing = AC_DB::q1( 'SELECT * FROM {vat_returns} WHERE period_start=? AND period_end=?', array( $start, $end ) );
		if ( $existing && 'preparee' !== $existing['status'] ) {
			return array( 'return' => $existing, 'data' => $data, 'issues' => $issues, 'form' => $form, 'locked' => true );
		}
		if ( $existing ) {
			$payload['ajustements'] = json_decode( $existing['data'], true )['ajustements'] ?? array();
			AC_DB::exec( 'UPDATE {vat_returns} SET data=?, form=?, created_at=? WHERE id=?', array( wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ), $form, AC_DB::now(), $existing['id'] ) );
			$rid = $existing['id'];
		} else {
			$rid = AC_DB::insert( 'vat_returns', array( 'period_start' => $start, 'period_end' => $end, 'form' => $form,
				'data' => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ), 'created_at' => AC_DB::now() ) );
		}
		return array( 'return' => AC_DB::q1( 'SELECT * FROM {vat_returns} WHERE id=?', array( $rid ) ), 'data' => $data, 'issues' => $issues, 'form' => $form,
			'statut' => $payload['definitive'] ? 'définitive' : 'provisoire (contrôles à terminer)' );
	}

	/** Valide : écritures de la période validées, TVA soldée vers 445510 ou 445670. */
	public static function validate( int $id, bool $force = false ): array {
		$r = AC_DB::q1( 'SELECT * FROM {vat_returns} WHERE id=?', array( $id ) );
		if ( 'preparee' !== $r['status'] ) {
			throw new AC_Error( 'Déclaration déjà validée.' );
		}
		$res   = self::prepare( $r['period_start'], $r['period_end'] );
		$block = count( array_filter( $res['issues'], function ( $i ) { return 'bloquant' === $i['niveau']; } ) );
		if ( $block && ! $force ) {
			throw new AC_Error( "$block anomalie(s) bloquante(s) à corriger avant validation." );
		}
		$data = $res['data'];
		$L    = $data['lignes'];
		return AC_DB::tx( function () use ( $r, $id, $data, $L, $block ) {
			foreach ( AC_DB::q( "SELECT id FROM {entries} WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date, id", array( $r['period_start'], $r['period_end'] ) ) as $e ) {
				AC_Ledger::validate_entry( $e['id'] );
			}
			$net   = function ( $acc, $sign ) use ( $r ) {
				return array_sum( array_map( function ( $x ) use ( $sign ) { return $sign * ( $x['credit_cents'] - $x['debit_cents'] ); }, self::lines( $r['period_start'], $r['period_end'], array( $acc ) ) ) );
			};
			$lines = array( ac_d( '445710', $net( '445710', 1 ) ), ac_d( '445200', $net( '445200', 1 ) ) );
			foreach ( array( '445620', '445660', '445662' ) as $acc ) {
				$lines[] = ac_c( $acc, $net( $acc, -1 ) );
			}
			if ( $L['22']['taxe_cents'] ) {
				$lines[] = ac_c( '445670', $L['22']['taxe_cents'], array( 'label' => 'Imputation du crédit antérieur' ) );
			}
			if ( $L['21']['taxe_cents'] ) {
				$lines[] = ac_c( '445800', $L['21']['taxe_cents'], array( 'label' => 'Régularisation' ) );
			}
			if ( $data['a_payer_cents'] ) {
				$lines[] = ac_c( '445510', $data['a_payer_cents'], array( 'label' => 'TVA à décaisser' ) );
			}
			if ( $data['credit_cents'] ) {
				$lines[] = ac_d( '445670', $data['credit_cents'], array( 'label' => 'Crédit de TVA à reporter' ) );
			}
			$eid = AC_Ledger::create_entry( 'OD', $r['period_end'], "Déclaration de TVA {$r['period_start']} au {$r['period_end']}", $lines, array(
				'piece_ref' => 'TVA' . substr( $r['period_end'], 0, 7 ), 'source_type' => 'tva_declaration', 'source_id' => $id, 'status' => 'valide' ) );
			$stored = json_decode( $r['data'], true );
			$stored = array_merge( $stored, array( 'lignes' => self::strip( $L ), 'a_payer_cents' => $data['a_payer_cents'], 'credit_cents' => $data['credit_cents'],
				'validation_forcee' => (bool) $block ) );
			AC_DB::exec( "UPDATE {vat_returns} SET status='validee', entry_id=?, validated_at=?, data=? WHERE id=?", array( $eid, AC_DB::now(), wp_json_encode( $stored, JSON_UNESCAPED_UNICODE ), $id ) );
			AC_DB::audit( 'validation', 'declaration_tva', $id, null, array( 'a_payer' => $data['a_payer_cents'], 'credit' => $data['credit_cents'] ),
				$block ? 'Validation forcée malgré anomalies' : null );
			return array( 'ok' => true, 'entry_id' => $eid, 'a_payer' => AC_Money::fmt( $data['a_payer_cents'] ), 'credit' => AC_Money::fmt( $data['credit_cents'] ) );
		} );
	}

	public static function mark_filed( int $id ): void {
		AC_DB::exec( "UPDATE {vat_returns} SET status='deposee', filed_at=? WHERE id=? AND status='validee'", array( AC_DB::now(), $id ) );
		AC_DB::audit( 'depot', 'declaration_tva', $id, null, array( 'deposee_le' => AC_DB::now() ) );
	}

	public static function set_adjustment( int $id, string $line, int $cents ): void {
		$r = AC_DB::q1( 'SELECT * FROM {vat_returns} WHERE id=?', array( $id ) );
		if ( 'preparee' !== $r['status'] ) {
			throw new AC_Error( 'Déclaration validée : ajustement impossible.' );
		}
		$d = json_decode( $r['data'], true );
		$d['ajustements'][ $line ] = $cents;
		AC_DB::exec( 'UPDATE {vat_returns} SET data=? WHERE id=?', array( wp_json_encode( $d, JSON_UNESCAPED_UNICODE ), $id ) );
		AC_DB::audit( 'ajustement', 'declaration_tva', $id, null, array( $line => $cents ) );
	}

	public static function current_estimate(): array {
		$today = AC_DB::today();
		$ps    = self::periods( (int) substr( $today, 0, 4 ) );
		$cur   = $ps[0] ?? null;
		foreach ( $ps as $p ) {
			if ( $p['start'] <= $today && $today <= $p['end'] ) {
				$cur = $p;
			}
		}
		if ( ! $cur ) {
			return array();
		}
		$d = self::compute( $cur['start'], $cur['end'] );
		return array( 'periode' => $cur['label'], 'start' => $cur['start'], 'end' => $cur['end'], 'a_payer_cents' => $d['a_payer_cents'], 'credit_cents' => $d['credit_cents'] );
	}
}

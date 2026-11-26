<?php
/**
 * Génération automatique des écritures à partir des pièces.
 *
 * Exigibilité de la TVA (CGI art. 269) :
 * * prestations de services : TVA exigible à l'encaissement (sauf option débits) :
 *   d'abord en 445870 « TVA collectée en attente », puis virée en 445710 au prorata
 *   de chaque encaissement ;
 * * ventes de biens (ou option débits) : TVA exigible à la facture (445710) ;
 * * achats de services : TVA déductible au paiement (445860 puis 445660) ;
 *   achats de biens et immobilisations : dès la facture (445660 / 445620).
 */
defined( 'ABSPATH' ) || exit;

class AC_Posting {

	public static function vat_code( string $prefix, $rate ): string {
		return $prefix . AC_Money::rate_str( $rate ?: '0' );
	}

	// ------------------------------------------------------------ ventes
	public static function invoice_vat_on_cash( array $inv ): bool {
		$tax = AC_DB::get_setting( 'tax' );
		return 'services' === $inv['nature'] && empty( $tax['vat_on_debits'] ) && 'fr' === $inv['vat_regime'];
	}

	public static function post_sales_invoice( int $id, string $reason = 'Mise à jour de la facture' ): void {
		$inv = AC_DB::q1( 'SELECT * FROM {sales_invoices} WHERE id=?', array( $id ) );
		AC_Ledger::clear_source( 'vente', $id, $reason );
		if ( ! $inv || 'emise' !== $inv['status'] ) {
			return;
		}
		$client = AC_DB::q1( 'SELECT * FROM {parties} WHERE id=?', array( $inv['client_id'] ) );
		$sign   = 'avoir' === $inv['kind'] ? -1 : 1;
		$aux    = sprintf( 'C%04d', $client['id'] );
		$tag    = 'V' . $inv['id'];
		$on_cash = self::invoice_vat_on_cash( $inv );
		$groups = array();
		foreach ( AC_DB::q( 'SELECT * FROM {sales_invoice_lines} WHERE invoice_id=?', array( $id ) ) as $ln ) {
			$k = $ln['account'] . '|' . $ln['vat_rate'];
			$groups[ $k ] = $groups[ $k ] ?? array( 'account' => $ln['account'], 'rate' => $ln['vat_rate'], 'ht' => 0, 'vat' => 0 );
			$groups[ $k ]['ht']  += $ln['ht_cents'];
			$groups[ $k ]['vat'] += $ln['vat_cents'];
		}
		$non_taxed = array( 'exonere' => 'E2', 'autoliquidation_ue' => 'E2', 'export' => 'biens' === $inv['nature'] ? 'E1' : 'E2' )[ $inv['vat_regime'] ] ?? null;
		$lines     = array();
		foreach ( $groups as $g ) {
			$lines[] = ac_c( $g['account'], $sign * $g['ht'], array( 'vat_code' => $non_taxed, 'vat_base' => $non_taxed ? $sign * $g['ht'] : null ) );
			if ( ! $g['vat'] ) {
				continue;
			}
			$code = self::vat_code( 'C', $g['rate'] );
			if ( 'avoir' === $inv['kind'] && $on_cash ) {
				$orig = $inv['credited_invoice_id'];
				$pend = $orig ? self::pending( '445870', 'V' . $orig, $code ) : 0;
				$part = max( 0, min( $g['vat'], $pend ) );
				if ( $part ) {
					$lines[] = ac_d( '445870', $part, array( 'vat_code' => $code, 'lettrage' => 'V' . $orig ) );
				}
				$rest = $g['vat'] - $part;
				if ( $rest ) {
					$lines[] = ac_d( '445710', $rest, array( 'vat_code' => $code, 'vat_base' => -AC_Money::prorata( $g['ht'], $rest, $g['vat'] ) ) );
				}
			} elseif ( $on_cash ) {
				$lines[] = ac_c( '445870', $sign * $g['vat'], array( 'vat_code' => $code, 'lettrage' => $tag ) );
			} else {
				$lines[] = ac_c( '445710', $sign * $g['vat'], array( 'vat_code' => $code, 'vat_base' => $sign * $g['ht'] ) );
			}
		}
		$lines[] = ac_d( AC_Rules::CLIENTS, $sign * $inv['ttc_cents'], array( 'aux' => $aux, 'aux_label' => $client['name'], 'lettrage' => $tag ) );
		AC_Ledger::create_entry( 'VT', $inv['issue_date'], ( $sign < 0 ? 'Avoir ' : 'Facture ' ) . $inv['number'] . ' ' . $client['name'], $lines, array(
			'piece_ref' => $inv['number'], 'document_id' => $inv['document_id'], 'source_type' => 'vente', 'source_id' => $id, 'reason' => $reason,
		) );
	}

	/** Solde restant en attente (crédit − débit pour 445870, débit − crédit pour 445860). */
	private static function pending( string $account, string $tag, string $code ): int {
		$r = AC_DB::ival( 'SELECT COALESCE(SUM(credit_cents-debit_cents),0) FROM {entry_lines} WHERE account=? AND lettrage=? AND vat_code=?', array( $account, $tag, $code ) );
		return '445870' === $account ? $r : -$r;
	}

	/** TVA déjà rendue exigible par les règlements (écritures de banque). */
	private static function moved( string $account, string $tag, string $code ): int {
		$r = AC_DB::ival( "SELECT COALESCE(SUM(l.debit_cents-l.credit_cents),0) FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id
			WHERE l.account=? AND l.lettrage=? AND l.vat_code=? AND e.source_type LIKE 'banque%'", array( $account, $tag, $code ) );
		return '445870' === $account ? $r : -$r;
	}

	private static function paid_until( string $type, int $id, array $tx ): int {
		return AC_DB::ival( 'SELECT COALESCE(SUM(a.amount_cents),0) FROM {allocations} a JOIN {bank_transactions} t ON t.id=a.bank_transaction_id
			WHERE a.target_type=? AND a.target_id=? AND (t.date < ? OR (t.date=? AND t.id<=?))', array( $type, $id, $tx['date'], $tx['date'], $tx['id'] ) );
	}

	private static function invoice_vat_moves( array $inv, array $tx ): array {
		if ( 'facture' !== $inv['kind'] || ! self::invoice_vat_on_cash( $inv ) ) {
			return array();
		}
		$tag = 'V' . $inv['id'];
		$ids = array( (int) $inv['id'] );
		foreach ( AC_DB::q( "SELECT id FROM {sales_invoices} WHERE credited_invoice_id=? AND status='emise'", array( $inv['id'] ) ) as $a ) {
			$ids[] = (int) $a['id'];
		}
		$in      = implode( ',', $ids );
		$by_code = AC_DB::q( "SELECT l.vat_rate, SUM(CASE WHEN s.kind='avoir' THEN -l.vat_cents ELSE l.vat_cents END) vat_cents,
			SUM(CASE WHEN s.kind='avoir' THEN -l.ht_cents ELSE l.ht_cents END) ht_cents FROM {sales_invoice_lines} l JOIN {sales_invoices} s ON s.id=l.invoice_id
			WHERE l.invoice_id IN ($in) GROUP BY l.vat_rate" );
		$ttc_net = AC_DB::ival( "SELECT SUM(CASE WHEN kind='avoir' THEN -ttc_cents ELSE ttc_cents END) FROM {sales_invoices} WHERE id IN ($in)" );
		$paid    = self::paid_until( 'vente', (int) $inv['id'], $tx );
		$out     = array();
		foreach ( $by_code as $row ) {
			if ( ! $row['vat_cents'] ) {
				continue;
			}
			$code = self::vat_code( 'C', $row['vat_rate'] );
			$exig = $paid >= $ttc_net ? $row['vat_cents'] : AC_Money::prorata( $row['vat_cents'], max( $paid, 0 ), $ttc_net );
			$move = max( 0, min( $exig - self::moved( '445870', $tag, $code ), self::pending( '445870', $tag, $code ) ) );
			if ( $move ) {
				$out[] = ac_d( '445870', $move, array( 'vat_code' => $code, 'lettrage' => $tag, 'label' => "TVA exigible encaissement {$inv['number']}" ) );
				$out[] = ac_c( '445710', $move, array( 'vat_code' => $code, 'lettrage' => $tag, 'vat_base' => AC_Money::prorata( $row['ht_cents'], $move, $row['vat_cents'] ),
					'label' => "TVA collectée encaissement {$inv['number']}" ) );
			}
		}
		return $out;
	}

	// ------------------------------------------------------------ dépenses
	public static function expense_counterpart( array $exp, ?array $cat ): string {
		if ( 'perso' === $exp['payment_method'] || ( $cat && 'personnel' === $cat['rule'] ) ) {
			return AC_Rules::ASSOCIATE;
		}
		return ( $cat && $cat['is_asset'] ) ? AC_Rules::SUPPLIERS_ASSETS : AC_Rules::SUPPLIERS;
	}

	/** [TVA récupérable, TVA non récupérable] */
	public static function expense_vat_split( array $exp ): array {
		if ( 'sans_tva' === $exp['vat_regime'] ) {
			return array( 0, 0 );
		}
		$rec = AC_Money::pct_of( (int) $exp['vat_cents'], $exp['vat_recovery'] );
		return array( $rec, $exp['vat_cents'] - $rec );
	}

	public static function category( $id ): ?array {
		return $id ? AC_DB::q1( 'SELECT * FROM {categories} WHERE id=?', array( (int) $id ) ) : null;
	}

	public static function post_expense( int $id, string $reason = 'Mise à jour de la dépense' ): void {
		$exp = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( $id ) );
		AC_Ledger::clear_source( 'depense', $id, $reason );
		if ( ! $exp ) {
			return;
		}
		$cat = self::category( $exp['category_id'] );
		if ( $cat && 'personnel' === $cat['rule'] ) {
			return; // dépense privée : pas de charge, le paiement débite le compte courant (455)
		}
		$account = $exp['account'] ?: ( $cat ? $cat['account'] : AC_Rules::SUSPENSE );
		if ( $exp['fixed_asset_id'] ) {
			$fa = AC_DB::q1( 'SELECT account FROM {fixed_assets} WHERE id=?', array( $exp['fixed_asset_id'] ) );
			if ( $fa ) {
				$account = $fa['account'];
			}
		}
		$is_asset  = ( $cat && $cat['is_asset'] ) || '2' === $account[0];
		$cp        = self::expense_counterpart( $exp, $cat );
		$sup       = $exp['supplier_name'] ?: 'Fournisseur';
		$aux       = AC_Rules::ASSOCIATE === $cp ? 'ASSOCIE' : ( $exp['supplier_id'] ? sprintf( 'F%04d', $exp['supplier_id'] ) : null );
		$aux_label = AC_Rules::ASSOCIATE === $cp ? 'Associé unique' : $sup;
		$tag       = 'D' . $exp['id'];
		$rate      = $exp['vat_rate'] ?: '20';
		$o_cp      = array( 'aux' => $aux, 'aux_label' => $aux_label, 'lettrage' => $tag );
		$lines     = array();
		if ( in_array( $exp['vat_regime'], array( 'autoliquidation_ue', 'autoliquidation_hors_ue' ), true ) ) {
			$auto  = $exp['vat_cents'] ?: AC_Money::pct_of( (int) $exp['ht_cents'], $rate );
			$rec   = AC_Money::pct_of( (int) $auto, $exp['vat_recovery'] );
			$code  = self::vat_code( 'autoliquidation_ue' === $exp['vat_regime'] ? 'AL_UE' : 'AL_HUE', $rate );
			$lines = array(
				ac_d( $account, $exp['ht_cents'] + $auto - $rec ),
				ac_d( AC_Rules::VAT_DEDUCTIBLE_AUTOLIQ, $rec, array( 'vat_code' => self::vat_code( $is_asset ? 'I' : 'D', $rate ), 'vat_base' => $exp['ht_cents'] ) ),
				ac_c( AC_Rules::VAT_DUE_AUTOLIQ, $auto, array( 'vat_code' => $code, 'vat_base' => $exp['ht_cents'] ) ),
				ac_c( $cp, $exp['ht_cents'], $o_cp ),
			);
		} elseif ( 'sans_tva' === $exp['vat_regime'] || ! $exp['vat_cents'] ) {
			$lines = array( ac_d( $account, $exp['ttc_cents'] ), ac_c( $cp, $exp['ttc_cents'], $o_cp ) );
		} else {
			list( $rec, $nonrec ) = self::expense_vat_split( $exp );
			if ( $is_asset ) {
				$vat_acc = AC_Rules::VAT_DEDUCTIBLE_ASSETS;
				$code    = self::vat_code( 'I', $rate );
			} elseif ( 'biens' === $exp['nature'] || 'perso' === $exp['payment_method'] || $exp['ttc_cents'] < 0 ) {
				// biens, note de frais déjà payée, ou remboursement fournisseur (avoir encaissé) : TVA régularisée immédiatement
				$vat_acc = AC_Rules::VAT_DEDUCTIBLE;
				$code    = self::vat_code( 'D', $rate );
			} else {
				$vat_acc = AC_Rules::VAT_DEDUCTIBLE_PENDING;
				$code    = self::vat_code( 'D', $rate );
			}
			$pending = AC_Rules::VAT_DEDUCTIBLE_PENDING === $vat_acc;
			$base    = $exp['vat_cents'] ? AC_Money::prorata( (int) $exp['ht_cents'], $rec, (int) $exp['vat_cents'] ) : 0;
			$lines   = array(
				ac_d( $account, $exp['ht_cents'] + $nonrec ),
				ac_d( $vat_acc, $rec, array( 'vat_code' => $code, 'vat_base' => $pending ? null : $base, 'lettrage' => $pending ? $tag : null ) ),
				ac_c( $cp, $exp['ttc_cents'], $o_cp ),
			);
		}
		AC_Ledger::create_entry( 'AC', $exp['date'], mb_substr( "$sup {$exp['label']}", 0, 200 ), $lines, array(
			'piece_ref' => $exp['invoice_number'] ?: 'DEP' . $exp['id'], 'document_id' => $exp['document_id'],
			'source_type' => 'depense', 'source_id' => $id, 'reason' => $reason,
		) );
	}

	private static function expense_vat_moves( array $exp, array $tx ): array {
		$tag = 'D' . $exp['id'];
		list( $rec ) = self::expense_vat_split( $exp );
		if ( ! $rec ) {
			return array();
		}
		$paid = -self::paid_until( 'depense', (int) $exp['id'], $tx );
		$out  = array();
		foreach ( AC_DB::q( "SELECT vat_code FROM {entry_lines} WHERE account='445860' AND lettrage=? GROUP BY vat_code", array( $tag ) ) as $row ) {
			$code = $row['vat_code'];
			$exig = $paid >= $exp['ttc_cents'] ? $rec : AC_Money::prorata( $rec, max( $paid, 0 ), (int) $exp['ttc_cents'] );
			$move = max( 0, min( $exig - self::moved( '445860', $tag, $code ), self::pending( '445860', $tag, $code ) ) );
			if ( $move ) {
				$lbl   = 'TVA déductible au paiement ' . ( $exp['supplier_name'] ?: '' );
				$out[] = ac_c( '445860', $move, array( 'vat_code' => $code, 'lettrage' => $tag, 'label' => $lbl ) );
				$out[] = ac_d( '445660', $move, array( 'vat_code' => $code, 'lettrage' => $tag, 'label' => $lbl,
					'vat_base' => AC_Money::prorata( (int) $exp['ht_cents'], $move, (int) $exp['vat_cents'] ) ) );
			}
		}
		return $out;
	}

	// ------------------------------------------------------------ banque
	public static function post_bank_transaction( int $id, string $reason = 'Mise à jour du rapprochement' ): void {
		$tx = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( $id ) );
		AC_Ledger::clear_source( 'banque', $id, $reason );
		if ( ! $tx || 'ignore' === $tx['status'] ) {
			return;
		}
		$lines     = array( ac_d( AC_Rules::BANK, $tx['amount_cents'] ) );
		$allocated = 0;
		$doc_id    = null;
		foreach ( AC_DB::q( 'SELECT * FROM {allocations} WHERE bank_transaction_id=? ORDER BY id', array( $id ) ) as $a ) {
			$amt        = $a['amount_cents'];
			$allocated += $amt;
			switch ( $a['target_type'] ) {
				case 'vente':
					$inv     = AC_DB::q1( 'SELECT * FROM {sales_invoices} WHERE id=?', array( $a['target_id'] ) );
					$lines[] = ac_c( AC_Rules::CLIENTS, $amt, array( 'aux' => sprintf( 'C%04d', $inv['client_id'] ), 'lettrage' => 'V' . $inv['id'], 'label' => "Règlement {$inv['number']}" ) );
					$lines   = array_merge( $lines, self::invoice_vat_moves( $inv, $tx ) );
					$doc_id  = $doc_id ?: $inv['document_id'];
					break;
				case 'depense':
					$exp     = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( $a['target_id'] ) );
					$cat     = self::category( $exp['category_id'] );
					$cp      = self::expense_counterpart( array_merge( $exp, array( 'payment_method' => 'qonto' ) ), $cat );
					$aux     = AC_Rules::ASSOCIATE === $cp ? 'ASSOCIE' : ( $exp['supplier_id'] ? sprintf( 'F%04d', $exp['supplier_id'] ) : null );
					$lines[] = ac_c( $cp, $amt, array( 'aux' => $aux, 'lettrage' => 'D' . $exp['id'], 'label' => 'Paiement ' . ( $exp['supplier_name'] ?: $exp['label'] ) ) );
					if ( AC_Rules::ASSOCIATE !== $cp ) {
						$lines = array_merge( $lines, self::expense_vat_moves( $exp, $tx ) );
					}
					$doc_id = $doc_id ?: $exp['document_id'];
					break;
				case 'associe':
					$lines[] = ac_c( AC_Rules::ASSOCIATE, $amt, array( 'aux' => 'ASSOCIE', 'aux_label' => 'Associé unique',
						'label' => $amt > 0 ? 'Apport en compte courant' : 'Remboursement compte courant' ) );
					break;
				case 'capital':
					$lines[] = ac_c( '101300', $amt, array( 'label' => 'Libération du capital social' ) );
					break;
				case 'tva':
					$lines[] = ac_c( $a['account'] ?: AC_Rules::VAT_TO_PAY, $amt, array( 'label' => $amt < 0 ? 'TVA réglée au Trésor' : 'Remboursement de crédit de TVA' ) );
					break;
				case 'is':
					$lines[] = ac_c( '444000', $amt, array( 'label' => 'Impôt sur les sociétés' ) );
					break;
				case 'virement_interne':
					$lines[] = ac_c( '580000', $amt );
					break;
				case 'compte':
					if ( ! $a['account'] ) {
						throw new AC_Error( "Compte manquant pour l'imputation" );
					}
					$lines[] = ac_c( $a['account'], $amt );
					break;
			}
		}
		$rest = $tx['amount_cents'] - $allocated;
		if ( $rest ) {
			$lines[] = ac_c( AC_Rules::SUSPENSE, $rest, array( 'label' => 'Opération à justifier' ) );
		}
		AC_Ledger::create_entry( 'BQ', $tx['date'], mb_substr( $tx['counterparty'] ?: ( $tx['label'] ?: 'Opération bancaire' ), 0, 200 ), $lines, array(
			'piece_ref' => mb_substr( $tx['reference'] ?: $tx['external_id'], 0, 40 ), 'document_id' => $doc_id,
			'source_type' => 'banque', 'source_id' => $id, 'reason' => $reason,
		) );
		$status = ( 0 === $rest && 0 !== $allocated ) ? 'rapproche' : ( $tx['suggestion'] ? 'propose' : 'a_traiter' );
		if ( $tx['status'] !== $status ) {
			AC_DB::exec( 'UPDATE {bank_transactions} SET status=? WHERE id=?', array( $status, $id ) );
		}
	}

	public static function repost_bank_for( string $type, int $id, string $reason ): void {
		foreach ( AC_DB::q( 'SELECT DISTINCT bank_transaction_id FROM {allocations} WHERE target_type=? AND target_id=?', array( $type, $id ) ) as $r ) {
			self::post_bank_transaction( (int) $r['bank_transaction_id'], $reason );
		}
	}

	// ------------------------------------------------------------ associé / capital
	public static function post_associate_movement( int $id, string $reason = 'Mise à jour du mouvement associé' ): void {
		$mv = AC_DB::q1( 'SELECT * FROM {associate_movements} WHERE id=?', array( $id ) );
		AC_Ledger::clear_source( 'associe', $id, $reason );
		if ( $mv && 'capital_non_appele' === $mv['type'] ) {
			AC_Ledger::create_entry( 'OD', $mv['date'], 'Capital souscrit non appelé',
				array( ac_d( '109000', $mv['amount_cents'] ), ac_c( '101100', $mv['amount_cents'] ) ),
				array( 'piece_ref' => 'STATUTS', 'source_type' => 'associe', 'source_id' => $id, 'reason' => $reason ) );
		}
	}
}

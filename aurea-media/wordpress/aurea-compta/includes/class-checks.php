<?php
/**
 * Contrôles : vérification du mois, avant déclaration de TVA, avant clôture.
 * Niveaux : bloquant (interdit de présenter un document comme définitif), a_verifier, info.
 */
defined( 'ABSPATH' ) || exit;

class AC_Checks {

	public static function d( ?string $s ): string {
		return $s && strlen( $s ) >= 10 ? substr( $s, 8, 2 ) . '/' . substr( $s, 5, 2 ) . '/' . substr( $s, 0, 4 ) : (string) $s;
	}

	private static function issue( string $lvl, string $type, string $msg, ?string $link = null, $amount = null ): array {
		return array( 'niveau' => $lvl, 'type' => $type, 'message' => $msg, 'lien' => $link, 'montant_cents' => $amount );
	}

	public static function period_checks( string $start, string $end ): array {
		$out = array();
		$e   = array( 'AC_Money', 'fmt' );
		foreach ( AC_DB::q( "SELECT * FROM {bank_transactions} WHERE date BETWEEN ? AND ? AND status IN ('a_traiter','propose') ORDER BY date", array( $start, $end ) ) as $t ) {
			$out[] = self::issue( 'bloquant', 'operation_non_rapprochee', 'Opération du ' . self::d( $t['date'] ) . ' « ' . ( $t['counterparty'] ?: $t['label'] ) . ' » (' . $e( $t['amount_cents'] ) . ') non catégorisée.',
				"#/banque?tx={$t['id']}", $t['amount_cents'] );
		}
		$s471 = AC_Ledger::balance( '471', $end );
		if ( $s471 ) {
			$out[] = self::issue( 'bloquant', 'compte_attente', "Le compte d'attente 471 n'est pas soldé (" . $e( $s471 ) . ').', '#/banque', $s471 );
		}
		foreach ( AC_DB::q( 'SELECT x.*, c.rule, c.code, c.is_asset FROM {expenses} x LEFT JOIN {categories} c ON c.id=x.category_id WHERE x.date BETWEEN ? AND ?', array( $start, $end ) ) as $x ) {
			$who  = (string) $x['supplier_name'];
			$lab  = AC_Services::norm_name( $x['label'] ) !== AC_Services::norm_name( $who ) ? $x['label'] : '';
			$name = trim( "$who $lab" ) . ' du ' . self::d( $x['date'] );
			$link = "#/depenses?id={$x['id']}";
			if ( AC_Services::needs_document( $x ) ) {
				$out[] = self::issue( ( $x['vat_cents'] && 'fr' === $x['vat_regime'] && $x['vat_recovery'] ) ? 'bloquant' : 'a_verifier', 'justificatif_manquant',
					"Justificatif manquant : $name.", $link, $x['ttc_cents'] );
			}
			if ( ! $x['category_id'] ) {
				$out[] = self::issue( 'bloquant', 'non_categorisee', "Dépense non catégorisée : $name.", $link );
			}
			if ( in_array( $x['status'], array( 'propose_ia', 'importe' ), true ) ) {
				$out[] = self::issue( 'a_verifier', 'proposition_ia', "Données proposées par l'IA à confirmer : $name.", $link );
			} elseif ( 'a_valider' === $x['status'] ) {
				$out[] = self::issue( 'a_verifier', 'depense_a_valider', "Dépense à valider : $name.", $link );
			}
			$cat = $x['category_id'] ? array( 'rule' => $x['rule'], 'is_asset' => $x['is_asset'] ) : null;
			foreach ( AC_Services::fiscal_warnings( $x, $cat ) as $w ) {
				if ( false !== strpos( $w, 'Catégorie' ) || false !== strpos( $w, 'Sans facture' ) ) {
					continue;
				}
				$out[] = self::issue( 0 === strpos( $w, 'TVA incohérente' ) ? 'bloquant' : 'a_verifier', 'traitement_fiscal', "$name : $w", $link );
			}
			if ( 'personnel' === $x['rule'] ) {
				$out[] = self::issue( 'info', 'depense_personnelle', "Dépense personnelle imputée au compte courant : $name.", $link );
			}
			if ( 'qonto' === $x['payment_method'] && 0 === AC_Services::expense_paid( $x ) ) {
				$out[] = self::issue( 'a_verifier', 'depense_sans_paiement', "Dépense payée par Qonto mais non rapprochée d'une opération : $name.", $link );
			}
		}
		foreach ( AC_DB::q( "SELECT a.id ida, b.id idb, a.supplier_name, a.ttc_cents, a.date FROM {expenses} a JOIN {expenses} b ON a.id<b.id
			AND a.ttc_cents=b.ttc_cents AND ABS(DATEDIFF(a.date,b.date))<=3 AND IFNULL(a.supplier_name,'')=IFNULL(b.supplier_name,'') WHERE a.date BETWEEN ? AND ?", array( $start, $end ) ) as $d ) {
			$out[] = self::issue( 'a_verifier', 'doublon', "Doublon possible : {$d['supplier_name']} " . $e( $d['ttc_cents'] ) . ' le ' . self::d( $d['date'] ) . " (dépenses {$d['ida']} et {$d['idb']}).",
				"#/depenses?id={$d['idb']}" );
		}
		foreach ( AC_DB::q( 'SELECT document_id, COUNT(*) n FROM {expenses} WHERE document_id IS NOT NULL AND date BETWEEN ? AND ? GROUP BY document_id HAVING n>1', array( $start, $end ) ) as $d ) {
			$out[] = self::issue( 'a_verifier', 'doublon', "Le même justificatif est rattaché à {$d['n']} dépenses.", '#/depenses' );
		}
		foreach ( AC_DB::q( 'SELECT s.*, p.name client, p.vat_number client_vat FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id WHERE s.issue_date BETWEEN ? AND ?', array( $start, $end ) ) as $inv ) {
			$link = "#/recettes?id={$inv['id']}";
			if ( 'fr' === $inv['vat_regime'] ) {
				foreach ( AC_DB::q( 'SELECT * FROM {sales_invoice_lines} WHERE invoice_id=?', array( $inv['id'] ) ) as $l ) {
					if ( abs( AC_Money::vat_from_ht( $l['ht_cents'], $l['vat_rate'] ) - $l['vat_cents'] ) > 1 ) {
						$out[] = self::issue( 'bloquant', 'tva_incoherente', "TVA incohérente sur la facture {$inv['number']}.", $link );
					}
				}
			}
			if ( 'autoliquidation_ue' === $inv['vat_regime'] && ! $inv['client_vat'] ) {
				$out[] = self::issue( 'a_verifier', 'mention_facture', "Facture {$inv['number']} en autoliquidation : numéro de TVA du client manquant.", $link );
			}
			if ( ! $inv['document_id'] ) {
				$out[] = self::issue( 'info', 'pdf_facture', "Facture {$inv['number']} sans PDF archivé.", $link );
			}
		}
		foreach ( AC_DB::q( "SELECT s.*, p.name client FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id WHERE s.kind='facture' AND s.status='emise' AND s.due_date<? AND s.issue_date<=?",
			array( AC_DB::today(), $end ) ) as $inv ) {
			$st = AC_Services::invoice_status( $inv );
			if ( $st['remaining_cents'] > 0 ) {
				$out[] = self::issue( 'info', 'facture_echue', "Facture {$inv['number']} ({$inv['client']}) échue, reste dû " . $e( $st['remaining_cents'] ) . '.', "#/recettes?id={$inv['id']}", $st['remaining_cents'] );
			}
		}
		foreach ( AC_DB::q( "SELECT * FROM {associate_movements} WHERE bank_transaction_id IS NULL AND type IN ('apport','remboursement','capital_libere') AND date BETWEEN ? AND ?", array( $start, $end ) ) as $m ) {
			$out[] = self::issue( 'a_verifier', 'apport_non_rapproche', ucfirst( $m['type'] ) . ' du ' . self::d( $m['date'] ) . ' (' . $e( $m['amount_cents'] ) . ") non rapproché d'un virement Qonto.", '#/associe' );
		}
		$integ = AC_Ledger::check_integrity();
		if ( ! $integ['ok'] ) {
			$out[] = self::issue( 'bloquant', 'integrite', $integ['tampered']
				? 'Écritures validées modifiées hors de l\'application : ' . implode( ', ', $integ['tampered'] ) . '.'
				: count( $integ['unbalanced'] ) . ' écriture(s) déséquilibrée(s).', '#/comptabilite' );
		}
		$drafts = AC_DB::ival( "SELECT COUNT(*) FROM {entries} WHERE status='brouillon' AND date BETWEEN ? AND ?", array( $start, $end ) );
		if ( $drafts ) {
			$out[] = self::issue( 'info', 'brouillons', "$drafts écriture(s) provisoire(s) seront validées lors de la clôture de la période.", '#/comptabilite' );
		}
		$rec = self::bank_reconciliation( $end );
		if ( null !== $rec['ecart_cents'] && 0 !== $rec['ecart_cents'] ) {
			$out[] = self::issue( 'bloquant', 'ecart_rapprochement', 'Écart de rapprochement bancaire au ' . self::d( $rec['date_reference'] ) . ' : ' . $e( $rec['ecart_cents'] )
				. ' (banque ' . $e( $rec['solde_banque_cents'] ) . ', comptabilité ' . $e( $rec['solde_comptable_cents'] ) . ').', '#/banque' );
		}
		$s455 = AC_Ledger::balance( '455', $end );
		if ( $s455 > 0 ) {
			$out[] = self::issue( 'bloquant', 'compte_courant_debiteur', "Compte courant d'associé débiteur de " . $e( $s455 ) . " : situation interdite pour le président d'une SAS, à régulariser.", '#/associe', $s455 );
		}
		$order = array( 'bloquant' => 0, 'a_verifier' => 1, 'info' => 2 );
		usort( $out, function ( $a, $b ) use ( $order ) {
			return $order[ $a['niveau'] ] <=> $order[ $b['niveau'] ];
		} );
		return $out;
	}

	/** Compare le solde du 512 au dernier solde bancaire connu (Qonto ou relevé saisi). */
	public static function bank_reconciliation( string $end ): array {
		$bank = null;
		$ref  = null;
		$last = AC_DB::q1( "SELECT date FROM {bank_transactions} WHERE date<=? AND source='qonto' ORDER BY date DESC LIMIT 1", array( $end ) );
		if ( $last ) {
			$bals = array();
			foreach ( AC_DB::q( "SELECT raw FROM {bank_transactions} WHERE date=? AND source='qonto'", array( $last['date'] ) ) as $r ) {
				$raw = json_decode( (string) $r['raw'], true ) ?: array();
				if ( isset( $raw['settled_balance_cents'] ) ) {
					$bals[] = array( (string) ( $raw['settled_at'] ?? '' ), (int) $raw['settled_balance_cents'] );
				}
			}
			if ( $bals ) {
				sort( $bals );
				$bank = end( $bals )[1];
				$ref  = $last['date'];
			}
		}
		$manual = AC_DB::get_setting( 'bank_statement' );
		if ( ! empty( $manual['date'] ) && $manual['date'] <= $end && ( null === $ref || $manual['date'] >= $ref ) ) {
			$bank = (int) $manual['balance_cents'];
			$ref  = $manual['date'];
		}
		$book = AC_Ledger::balance( '512', $ref ?: $end );
		return array( 'date_reference' => $ref, 'solde_banque_cents' => $bank, 'solde_comptable_cents' => $book, 'ecart_cents' => null === $bank ? null : $bank - $book );
	}

	public static function month_bounds( string $month ): array {
		$s = $month . '-01';
		return array( $s, ( new DateTime( $s ) )->modify( 'last day of this month' )->format( 'Y-m-d' ) );
	}

	public static function verify_month( string $month ): array {
		list( $s, $e ) = self::month_bounds( $month );
		$issues = self::period_checks( $s, $e );
		$block  = count( array_filter( $issues, function ( $i ) { return 'bloquant' === $i['niveau']; } ) );
		return array( 'month' => $month, 'start' => $s, 'end' => $e, 'issues' => $issues, 'blocking' => $block, 'ok' => ! $block,
			'closed' => AC_DB::q1( 'SELECT * FROM {month_closings} WHERE month=?', array( $month ) ) );
	}

	public static function close_month( string $month, bool $force = false ): array {
		$res = self::verify_month( $month );
		if ( $res['blocking'] && ! $force ) {
			throw new AC_Error( 'Des anomalies bloquantes restent à corriger avant de clôturer le mois.' );
		}
		AC_DB::tx( function () use ( $res, $month, $force ) {
			foreach ( AC_DB::q( "SELECT id FROM {entries} WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date, id", array( $res['start'], $res['end'] ) ) as $e ) {
				AC_Ledger::validate_entry( $e['id'] );
			}
			foreach ( AC_DB::q( 'SELECT document_id FROM {expenses} WHERE date BETWEEN ? AND ?', array( $res['start'], $res['end'] ) ) as $x ) {
				AC_Services::lock_document( $x['document_id'] );
			}
			AC_DB::exec( 'REPLACE INTO {month_closings}(month,closed_at,report) VALUES(?,?,?)', array( $month, AC_DB::now(), wp_json_encode( $res['issues'], JSON_UNESCAPED_UNICODE ) ) );
			AC_DB::audit( 'cloture_mois', 'periode', null, null, array( 'mois' => $month, 'anomalies_restantes' => count( $res['issues'] ) ),
				$force && $res['blocking'] ? 'Clôture forcée' : null );
		} );
		return self::verify_month( $month );
	}
}

<?php
/**
 * Tests de scénario sur données fictives, dans des tables séparées (préfixe « actest_ ») :
 * jamais mélangées avec les données réelles.
 *
 * Lancement : WP_LOAD=/chemin/vers/wp-load.php php tests/run.php
 */
define( 'AC_TABLE_PREFIX', 'actest_' );
// AC_MOCK=1 : intégrations Qonto et IA testées contre des serveurs simulés (tests/mock-server.php sur le port 8099).
if ( getenv( 'AC_MOCK' ) ) {
	define( 'AC_QONTO_BASE_URL', 'http://127.0.0.1:8099' );
	define( 'AC_ANTHROPIC_BASE_URL', 'http://127.0.0.1:8099' );
}
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require getenv( 'WP_LOAD' ) ?: dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! defined( 'AC_VERSION' ) ) {
	require dirname( __DIR__ ) . '/aurea-compta.php';
}
wp_set_current_user( 1 );

$GLOBALS['ac_fail'] = 0;
$GLOBALS['ac_pass'] = 0;
function ok( $cond, $msg ) {
	if ( ! $cond ) {
		throw new Exception( "Échec : $msg" );
	}
}
function eq( $a, $b, $msg ) {
	if ( $a !== $b ) {
		throw new Exception( "Échec : $msg (obtenu " . var_export( $a, true ) . ', attendu ' . var_export( $b, true ) . ')' );
	}
}
function raises( callable $fn, $msg ) {
	try {
		$fn();
	} catch ( AC_Error $e ) {
		return;
	}
	throw new Exception( "Échec : exception attendue : $msg" );
}
function setup() {
	AC_DB::drop_all();
	foreach ( array( 's_company', 's_tax', 's_confirmed', 's_bank_statement', 's_invoicing', 's_ai', 's_qonto', 'secrets', 'schema' ) as $o ) {
		delete_option( AC_DB::opt( $o ) );
	}
	AC_DB::install();
	AC_DB::exec( 'DELETE FROM {fiscal_years}' );
	AC_DB::insert( 'fiscal_years', array( 'start_date' => '2026-01-01', 'end_date' => '2026-12-31' ) );
	update_option( AC_DB::opt( 's_company' ), array_merge( AC_DB::get_setting( 'company' ), array( 'siren' => '123456789', 'associate_name' => 'Alexandre Coury', 'capital_cents' => 100000 ) ), false );
	update_option( AC_DB::opt( 's_tax' ), array_merge( AC_DB::get_setting( 'tax' ), array( 'vat_regime' => 'reel_normal', 'vat_periodicity' => 'mensuelle' ) ), false );
	update_option( AC_DB::opt( 's_confirmed' ), array( 'company' => true, 'tax' => true ), false );
}
function test( $name, callable $fn ) {
	setup();
	try {
		$fn();
		$r = AC_Ledger::check_integrity();
		ok( $r['ok'], 'intégrité du grand livre ' . wp_json_encode( $r ) );
		$GLOBALS['ac_pass']++;
		echo "  ok  $name\n";
	} catch ( Throwable $e ) {
		$GLOBALS['ac_fail']++;
		echo "ÉCHEC $name\n      " . $e->getMessage() . "\n      " . basename( $e->getFile() ) . ':' . $e->getLine() . "\n";
	}
}
function tx( $ext, $d, $amount, $who, $op = 'card' ) {
	AC_Services::import_transactions( array( array( 'external_id' => $ext, 'date' => $d, 'amount_cents' => $amount, 'label' => $who, 'counterparty' => $who, 'operation_type' => $op ) ), 'qonto' );
	return AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE external_id=?', array( $ext ) );
}
function doc( $name ) {
	return AC_Services::store_document( $name, "%PDF-1.4 justificatif de test $name" );
}
function cat( $code ) {
	return AC_DB::q1( 'SELECT id FROM {categories} WHERE code=?', array( $code ) )['id'];
}
function bal( $acc, $to = null, $from = null ) {
	return AC_Ledger::balance( $acc, $to, $from );
}
function lines_of( $type, $id ) {
	$out = array();
	foreach ( AC_Ledger::source_entries( $type, $id ) as $e ) {
		foreach ( AC_DB::q( 'SELECT account, debit_cents, credit_cents FROM {entry_lines} WHERE entry_id=? ORDER BY account', array( $e['id'] ) ) as $l ) {
			$out[] = $l['account'] . ':' . $l['debit_cents'] . ':' . $l['credit_cents'];
		}
	}
	sort( $out );
	return $out;
}

echo "Aurea Compta : tests\n";

test( 'Arrondis sans erreur', function () {
	eq( AC_Money::to_cents( '0,1' ) + AC_Money::to_cents( '0.2' ), 30, '0,1 + 0,2' );
	eq( AC_Money::to_cents( '1 234,56' ), 123456, 'espaces' );
	eq( AC_Money::to_cents( '-0,005' ), -1, 'demi éloigné de zéro' );
	for ( $ttc = 1; $ttc < 5000; $ttc++ ) {
		list( $ht, $v ) = AC_Money::split_ttc( $ttc, '20' );
		ok( $ht + $v === $ttc, "HT + TVA = TTC pour $ttc" );
	}
	eq( AC_Money::vat_from_ht( 333, '5.5' ), 18, 'TVA 5,5 %' );
	eq( AC_Money::fmt( -123456 ), '-1 234,56 €', 'format' );
} );

test( 'Facture client avec TVA exigible à l\'encaissement', function () {
	$r = AC_Services::save_sales_invoice( array( 'client_name' => 'Client Alpha', 'issue_date' => '2026-01-10', 'due_date' => '2026-02-10',
		'lines' => array( array( 'description' => 'Site vitrine', 'quantity' => '1', 'unit_price_cents' => 100000, 'vat_rate' => '20' ) ) ) );
	eq( $r['number'], 'F2026-0001', 'numéro' );
	eq( lines_of( 'vente', $r['id'] ), array( '411000:120000:0', '445870:0:20000', '706000:0:100000' ), 'écriture' );
	eq( AC_Vat::compute( '2026-01-01', '2026-01-31' )['tva_brute_cents'], 0, 'pas de TVA avant encaissement' );
} );

test( 'Numérotation des factures unique et continue', function () {
	AC_Services::save_sales_invoice( array( 'client_name' => 'A', 'issue_date' => '2026-01-10', 'number' => 'F2026-0001', 'lines' => array( array( 'unit_price_cents' => 1000 ) ) ) );
	raises( function () {
		AC_Services::save_sales_invoice( array( 'client_name' => 'A', 'issue_date' => '2026-01-11', 'number' => 'F2026-0001', 'lines' => array( array( 'unit_price_cents' => 1000 ) ) ) );
	}, 'numéro en double' );
	$w = AC_Services::save_sales_invoice( array( 'client_name' => 'A', 'issue_date' => '2026-01-12', 'number' => 'F2026-0005', 'lines' => array( array( 'unit_price_cents' => 1000 ) ) ) )['warnings'];
	ok( $w && false !== strpos( $w[0], 'séquence' ), 'rupture de séquence signalée' );
} );

test( 'Facture fournisseur avec TVA et paiement Qonto', function () {
	$t = tx( 't1', '2026-01-15', -12000, 'Logiciel Pro SAS' );
	$r = AC_Services::classify_transaction( $t['id'], array( 'type' => 'depense_nouvelle', 'category_id' => cat( 'logiciels' ), 'vat_rate' => '20', 'vat_cents' => 2000, 'document_id' => doc( 'l.pdf' ) ) );
	$x = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( $r['id'] ) );
	eq( array( $x['ht_cents'], $x['vat_cents'], $x['ttc_cents'] ), array( 10000, 2000, 12000 ), 'montants' );
	eq( bal( '651' ), 10000, 'charge' );
	eq( bal( '445660' ), 2000, 'TVA déductible au paiement' );
	eq( bal( '445860' ), 0, 'plus rien en attente' );
	eq( bal( '401' ), 0, 'fournisseur soldé' );
	eq( bal( '512' ), -12000, 'banque' );
	eq( AC_DB::val( 'SELECT status FROM {bank_transactions} WHERE id=?', array( $t['id'] ) ), 'rapproche', 'opération rapprochée' );
} );

test( 'Dépense sans TVA récupérable', function () {
	$t = tx( 't2', '2026-02-03', -5500, 'SNCF Voyageurs' );
	$r = AC_Services::classify_transaction( $t['id'], array( 'type' => 'depense_nouvelle', 'category_id' => cat( 'deplacements' ), 'vat_rate' => '10', 'vat_cents' => 500 ) );
	AC_Services::save_expense( array( 'vat_recovery' => 0 ), $r['id'] );
	eq( bal( '625100' ), 5500, 'charge TTC' );
	eq( bal( '4456' ), 0, 'aucune TVA déductible' );
} );

test( 'Cadeau au delà du seuil de 73 €', function () {
	$r = AC_Services::save_expense( array( 'date' => '2026-03-01', 'supplier_name' => 'Cave', 'label' => 'Coffret', 'ttc_cents' => 12000, 'vat_rate' => '20',
		'category_id' => cat( 'cadeaux' ), 'payment_method' => 'a_payer' ) );
	eq( AC_DB::q1( 'SELECT vat_recovery FROM {expenses} WHERE id=?', array( $r['id'] ) )['vat_recovery'], 0, 'TVA non récupérable' );
	eq( bal( '623400' ), 12000, 'charge TTC' );
} );

test( 'Note de frais payée personnellement puis remboursée', function () {
	AC_Services::save_expense( array( 'date' => '2026-01-20', 'supplier_name' => 'Figma', 'label' => 'Abonnement', 'ttc_cents' => 3000, 'vat_rate' => '20',
		'category_id' => cat( 'logiciels' ), 'payment_method' => 'perso', 'document_id' => doc( 'ndf.pdf' ) ) );
	eq( bal( '455' ), -3000, 'la société doit 30 €' );
	eq( bal( '445660' ), 500, 'TVA déductible' );
	$s = AC_Services::associate_summary();
	eq( array( $s['avances_frais_cents'], $s['du_par_la_societe_cents'] ), array( 3000, 3000 ), 'synthèse' );
	$t = tx( 't3', '2026-02-10', -3000, 'Alexandre Coury', 'transfer' );
	eq( json_decode( $t['suggestion'], true )['type'], 'associe', 'proposition remboursement' );
	AC_Services::apply_suggestion( $t['id'] );
	$s = AC_Services::associate_summary();
	eq( array( $s['remboursements_cents'], $s['solde_cents'] ), array( 3000, 0 ), 'remboursé' );
	eq( bal( '651' ), 2500, 'charge comptée une seule fois' );
} );

test( 'Apport et remboursement en compte courant', function () {
	$t = tx( 'a1', '2026-01-05', 500000, 'Alexandre Coury', 'income' );
	AC_Services::apply_suggestion( $t['id'] );
	$t2 = tx( 'a2', '2026-06-05', -150000, 'Alexandre Coury', 'transfer' );
	AC_Services::classify_transaction( $t2['id'], array( 'type' => 'associe' ) );
	$s = AC_Services::associate_summary();
	eq( array( $s['apports_cents'], $s['remboursements_cents'], $s['solde_cents'] ), array( 500000, 150000, 350000 ), 'compte courant' );
	$inc = AC_Reports::income_statement( '2026-01-01', '2026-12-31' );
	eq( array( $inc['total_produits'], $inc['total_charges'] ), array( 0, 0 ), 'ni produit ni charge' );
} );

test( 'Apport saisi puis rapproché du virement', function () {
	$r = AC_Services::add_associate_movement( array( 'type' => 'apport', 'date' => '2026-03-01', 'amount_cents' => 20000 ) );
	ok( ! $r['matched'], 'pas encore de virement' );
	eq( AC_Services::associate_summary()['solde_cents'], 0, 'rien en comptabilité avant le virement' );
	$t = tx( 'a3', '2026-03-02', 20000, 'Virement perso', 'transfer' );
	AC_Services::link_movement( $r['id'], $t['id'] );
	eq( AC_Services::associate_summary()['apports_cents'], 20000, 'apport comptabilisé' );
} );

test( 'Capital distinct du compte courant', function () {
	$t = tx( 'c1', '2026-01-02', 100000, 'Dépôt de capital', 'transfer' );
	AC_Services::classify_transaction( $t['id'], array( 'type' => 'capital' ) );
	eq( bal( '1013' ), -100000, 'capital' );
	eq( bal( '455' ), 0, 'pas de compte courant' );
} );

test( 'Facture payée partiellement puis avoir', function () {
	$inv = AC_Services::save_sales_invoice( array( 'client_name' => 'Client Beta', 'issue_date' => '2026-01-10',
		'lines' => array( array( 'description' => 'Refonte', 'unit_price_cents' => 100000, 'vat_rate' => '20' ) ) ) );
	$t1 = tx( 'p1', '2026-02-05', 60000, 'Client Beta', 'transfer' );
	eq( json_decode( $t1['suggestion'], true )['invoice_id'], $inv['id'], 'facture proposée' );
	AC_Services::classify_transaction( $t1['id'], array( 'type' => 'vente', 'invoice_id' => $inv['id'] ) );
	$i  = AC_DB::q1( 'SELECT * FROM {sales_invoices} WHERE id=?', array( $inv['id'] ) );
	$st = AC_Services::invoice_status( $i );
	eq( array( $st['payment_status'], $st['remaining_cents'] ), array( 'partielle', 60000 ), 'paiement partiel' );
	$feb = AC_Vat::compute( '2026-02-01', '2026-02-28' );
	eq( array( $feb['tva_brute_cents'], $feb['lignes']['A1']['base_cents'] ), array( 10000, 50000 ), 'TVA au prorata de l\'encaissement' );
	$av = AC_Services::save_sales_invoice( array( 'kind' => 'avoir', 'credited_invoice_id' => $inv['id'], 'issue_date' => '2026-02-20',
		'lines' => array( array( 'description' => 'Remise', 'unit_price_cents' => 20000, 'vat_rate' => '20' ) ) ) );
	eq( $av['number'], 'AV2026-0001', 'numéro d\'avoir' );
	eq( AC_Services::invoice_status( $i )['remaining_cents'], 36000, 'reste dû après avoir' );
	$t2 = tx( 'p2', '2026-03-03', 36000, 'Client Beta', 'transfer' );
	AC_Services::classify_transaction( $t2['id'], array( 'type' => 'vente', 'invoice_id' => $inv['id'] ) );
	eq( AC_Services::invoice_status( $i )['payment_status'], 'payee', 'payée' );
	eq( AC_Vat::compute( '2026-01-01', '2026-12-31' )['tva_brute_cents'], 16000, 'TVA nette de l\'avoir' );
	eq( array( bal( '445870' ), bal( '411' ), bal( '706' ) ), array( 0, 0, -80000 ), 'comptes soldés' );
} );

test( 'Vente de biens : TVA à la facturation', function () {
	AC_Services::save_sales_invoice( array( 'client_name' => 'Gamma', 'issue_date' => '2026-04-02', 'nature' => 'biens',
		'lines' => array( array( 'unit_price_cents' => 50000, 'vat_rate' => '20', 'account' => '707000' ) ) ) );
	eq( AC_Vat::compute( '2026-04-01', '2026-04-30' )['tva_brute_cents'], 10000, 'TVA exigible' );
} );

test( 'Autoliquidation fournisseur UE', function () {
	$t = tx( 'u1', '2026-05-03', -5000, 'Google Ireland Ltd' );
	AC_Services::classify_transaction( $t['id'], array( 'type' => 'depense_nouvelle', 'category_id' => cat( 'communication' ), 'vat_regime' => 'autoliquidation_ue', 'vat_rate' => '20', 'document_id' => doc( 'g.pdf' ) ) );
	$m = AC_Vat::compute( '2026-05-01', '2026-05-31' );
	eq( array( $m['lignes']['A3']['base_cents'], $m['tva_brute_cents'], $m['lignes']['20']['taxe_cents'], $m['a_payer_cents'] ), array( 5000, 1000, 1000, 0 ), 'déclaration' );
	eq( bal( '623100' ), 5000, 'charge HT' );
} );

test( 'Immobilisation amortissable', function () {
	$t = tx( 'i1', '2026-04-01', -180000, 'Apple Store' );
	AC_Services::classify_transaction( $t['id'], array( 'type' => 'depense_nouvelle', 'category_id' => cat( 'immobilisation' ), 'vat_rate' => '20', 'nature' => 'biens',
		'label' => 'MacBook Pro', 'document_id' => doc( 'mac.pdf' ) ) );
	$fa = AC_DB::q1( 'SELECT * FROM {fixed_assets}' );
	eq( array( $fa['cost_cents'], $fa['duration_years'] ), array( 150000, 3 ), 'fiche' );
	eq( array( bal( '2183' ), bal( '445620' ) ), array( 150000, 30000 ), 'immobilisation et TVA' );
	$fy    = AC_Closing::fiscal_year();
	$sched = AC_Services::post_depreciation( $fy['id'] );
	$dot   = $sched[0]['dotation_cents'];
	eq( $dot, (int) round( 150000 * 275 / 1096 ), 'prorata temporis' );
	eq( array( bal( '6811' ), bal( '28183' ) ), array( $dot, -$dot ), 'écriture' );
	$total = 0;
	for ( $y = 2026; $y <= 2029; $y++ ) {
		$total += AC_Services::depreciation_for_year( $fa, "$y-01-01", "$y-12-31" )['dotation_cents'];
	}
	eq( $total, 150000, 'amortissement total' );
	eq( AC_Vat::compute( '2026-04-01', '2026-04-30' )['lignes']['19']['taxe_cents'], 30000, 'ligne 19' );
} );

test( 'Écriture validée protégée et correction tracée', function () {
	$r = AC_Services::save_expense( array( 'date' => '2026-01-20', 'supplier_name' => 'X', 'label' => 'Y', 'ttc_cents' => 1200, 'vat_rate' => '20',
		'category_id' => cat( 'fournitures' ), 'payment_method' => 'a_payer' ) );
	$e = AC_Ledger::source_entries( 'depense', $r['id'] )[0];
	AC_Ledger::validate_entry( $e['id'] );
	AC_Services::save_expense( array( 'ttc_cents' => 2400 ), $r['id'], 'Montant corrigé' );
	$rev = AC_DB::q1( 'SELECT * FROM {entries} WHERE reversal_of=?', array( $e['id'] ) );
	ok( $rev && 'valide' === $rev['status'], 'contrepassation validée' );
	eq( bal( '606400' ), 2000, 'nouvelle charge' );
	ok( (bool) AC_DB::q1( "SELECT id FROM {audit_log} WHERE action='contrepassation'" ), 'historique' );
	// Modification directe en base : détectée
	AC_DB::exec( 'UPDATE {entry_lines} SET debit_cents=debit_cents+1 WHERE entry_id=? AND debit_cents>0 LIMIT 1', array( $e['id'] ) );
	AC_DB::exec( 'UPDATE {entry_lines} SET credit_cents=credit_cents+1 WHERE entry_id=? AND credit_cents>0 LIMIT 1', array( $e['id'] ) );
	$i = AC_Ledger::check_integrity();
	ok( ! $i['ok'] && $i['tampered'], 'falsification détectée' );
	AC_DB::exec( 'UPDATE {entry_lines} SET debit_cents=debit_cents-1 WHERE entry_id=? AND debit_cents>0 LIMIT 1', array( $e['id'] ) );
	AC_DB::exec( 'UPDATE {entry_lines} SET credit_cents=credit_cents-1 WHERE entry_id=? AND credit_cents>0 LIMIT 1', array( $e['id'] ) );
} );

test( 'Import idempotent et doublons entre sources', function () {
	$it = array( 'external_id' => 'qonto:abc', 'date' => '2026-01-03', 'amount_cents' => -999, 'label' => 'Frais', 'counterparty' => 'Qonto', 'operation_type' => 'qonto_fee' );
	eq( AC_Services::import_transactions( array( $it ), 'qonto' )['imported'], 1, 'import' );
	eq( AC_Services::import_transactions( array( $it ), 'qonto' )['duplicates'], 1, 'même identifiant' );
	eq( AC_Services::import_transactions( array( array_merge( $it, array( 'external_id' => 'csv:1' ) ) ), 'csv' )['duplicates'], 1, 'même opération par CSV' );
	eq( json_decode( AC_DB::val( 'SELECT suggestion FROM {bank_transactions}' ), true )['type'], 'depense_nouvelle', 'frais bancaires proposés' );
} );

test( 'Import CSV Qonto', function () {
	if ( ! class_exists( 'AC_Qonto' ) ) {
		return;
	}
	$items = AC_Qonto::parse_csv( "Date de l'opération (UTC);Montant total (TTC);Nom de la contrepartie;Libellé;Statut\n15/01/2026 10:00;-120,00;Logiciel Pro;Abonnement;Exécuté\n16/01/2026;1 200,50;Client;Facture F1;Exécuté\n" );
	eq( array( $items[0]['date'], $items[0]['amount_cents'], $items[1]['date'], $items[1]['amount_cents'] ), array( '2026-01-15', -12000, '2026-01-16', 120050 ), 'lecture' );
} );

function build_year() {
	$t = tx( 'y0', '2026-01-02', 100000, 'Dépôt de capital', 'transfer' );
	AC_Services::classify_transaction( $t['id'], array( 'type' => 'capital' ) );
	$a = tx( 'y1', '2026-01-05', 500000, 'Alexandre Coury', 'income' );
	AC_Services::apply_suggestion( $a['id'] );
	AC_Services::save_sales_invoice( array( 'client_name' => 'Client Alpha', 'issue_date' => '2026-01-10', 'due_date' => '2026-02-10',
		'lines' => array( array( 'description' => 'Site', 'unit_price_cents' => 300000, 'vat_rate' => '20' ) ), 'document_id' => doc( 'F1.pdf' ) ) );
	$p = tx( 'y2', '2026-01-25', 360000, 'Client Alpha', 'transfer' );
	AC_Services::apply_suggestion( $p['id'] );
	$s = tx( 'y3', '2026-01-15', -12000, 'Logiciel Pro' );
	AC_Services::classify_transaction( $s['id'], array( 'type' => 'depense_nouvelle', 'category_id' => cat( 'logiciels' ), 'vat_rate' => '20', 'vat_cents' => 2000, 'document_id' => doc( 'logi.pdf' ) ) );
	AC_Services::save_expense( array( 'date' => '2026-01-20', 'supplier_name' => 'Figma', 'label' => 'Abonnement', 'ttc_cents' => 3000, 'vat_rate' => '20',
		'category_id' => cat( 'logiciels' ), 'payment_method' => 'perso', 'document_id' => doc( 'figma.pdf' ) ) );
	$f = tx( 'y4', '2026-01-31', -900, 'Qonto', 'qonto_fee' );
	AC_Services::apply_suggestion( $f['id'] );
	$fee = AC_DB::q1( 'SELECT id FROM {expenses} WHERE ttc_cents=900' );
	AC_Services::save_expense( array( 'document_id' => doc( 'qonto.pdf' ) ), $fee['id'] );
}

test( 'Déclaration de TVA mensuelle et report de crédit', function () {
	build_year();
	$jan = AC_Vat::prepare( '2026-01-01', '2026-01-31' );
	$L   = $jan['data']['lignes'];
	eq( array( $L['08']['base_cents'], $L['08']['taxe_cents'] ), array( 300000, 60000 ), 'ligne 08' );
	eq( $L['20']['taxe_cents'], 2000 + 500 + 150, 'ligne 20' );
	eq( $jan['data']['a_payer_cents'], 60000 - 2650, 'à payer' );
	eq( $L['08']['detail'][0]['piece'], 'F2026-0001', 'pièce retrouvée' );
	AC_Vat::validate( $jan['return']['id'], true );
	eq( array( bal( '44571', '2026-01-31' ), bal( '44551', '2026-01-31' ) ), array( 0, -( 60000 - 2650 ) ), 'TVA soldée' );
	$t = tx( 'v1', '2026-02-15', -( 60000 - 2650 ), 'DGFIP TVA', 'direct_debit' );
	AC_Services::classify_transaction( $t['id'], array( 'type' => 'tva' ) );
	eq( bal( '44551' ), 0, 'TVA payée' );
	AC_Services::save_expense( array( 'date' => '2026-02-10', 'supplier_name' => 'Bureau', 'label' => 'Fournitures', 'ttc_cents' => 2400, 'vat_rate' => '20',
		'category_id' => cat( 'fournitures' ), 'payment_method' => 'perso', 'document_id' => doc( 'b.pdf' ) ) );
	$feb = AC_Vat::prepare( '2026-02-01', '2026-02-28' );
	eq( $feb['data']['credit_cents'], 400, 'crédit de février' );
	AC_Vat::validate( $feb['return']['id'], true );
	$mar = AC_Vat::compute( '2026-03-01', '2026-03-31' );
	eq( array( $mar['lignes']['22']['taxe_cents'], $mar['credit_cents'] ), array( 400, 400 ), 'report du crédit' );
	if ( class_exists( 'AC_Exports' ) ) {
		$names = AC_Exports::zip_names( AC_Exports::vat_dossier( '2026-01-01', '2026-01-31' ) );
		ok( in_array( 'tva/recapitulatif-tva.pdf', $names, true ) && in_array( 'tva/tva-detail.xlsx', $names, true ), 'dossier TVA' );
		ok( (bool) preg_grep( '#^justificatifs/#', $names ), 'justificatifs dans le dossier' );
	}
} );

test( 'Rapprochement bancaire', function () {
	build_year();
	$solde = AC_DB::ival( 'SELECT SUM(amount_cents) FROM {bank_transactions}' );
	eq( bal( '512' ), $solde, 'solde comptable = banque' );
	update_option( AC_DB::opt( 's_bank_statement' ), array( 'date' => '2026-01-31', 'balance_cents' => $solde ), false );
	eq( AC_Checks::bank_reconciliation( '2026-01-31' )['ecart_cents'], 0, 'aucun écart' );
	update_option( AC_DB::opt( 's_bank_statement' ), array( 'date' => '2026-01-31', 'balance_cents' => $solde + 100 ), false );
	ok( in_array( 'ecart_rapprochement', array_column( AC_Checks::verify_month( '2026-01' )['issues'], 'type' ), true ), 'écart signalé' );
} );

test( 'Vérification et clôture du mois', function () {
	build_year();
	$t   = tx( 'z1', '2026-01-28', -4200, 'Inconnu' );
	$res = AC_Checks::verify_month( '2026-01' );
	ok( ! $res['ok'] && in_array( 'operation_non_rapprochee', array_column( $res['issues'], 'type' ), true ), 'opération non rapprochée' );
	raises( function () { AC_Checks::close_month( '2026-01' ); }, 'clôture refusée' );
	AC_Services::classify_transaction( $t['id'], array( 'type' => 'depense_nouvelle', 'category_id' => cat( 'fournitures' ), 'vat_regime' => 'sans_tva', 'document_id' => doc( 'z.pdf' ) ) );
	$res = AC_Checks::verify_month( '2026-01' );
	ok( $res['ok'], 'mois sans anomalie bloquante ' . wp_json_encode( array_filter( $res['issues'], function ( $i ) { return 'bloquant' === $i['niveau']; } ), JSON_UNESCAPED_UNICODE ) );
	AC_Checks::close_month( '2026-01' );
	eq( AC_DB::ival( "SELECT COUNT(*) FROM {entries} WHERE status='brouillon' AND date<='2026-01-31'" ), 0, 'écritures validées' );
	if ( class_exists( 'AC_Exports' ) ) {
		ok( in_array( 'ecritures/livre-journal.xlsx', AC_Exports::zip_names( AC_Exports::month_dossier( '2026-01' ) ), true ), 'dossier mensuel' );
	}
} );

test( 'Clôture annuelle complète', function () {
	build_year();
	$t = tx( 'i1', '2026-04-01', -180000, 'Apple Store' );
	AC_Services::classify_transaction( $t['id'], array( 'type' => 'depense_nouvelle', 'category_id' => cat( 'immobilisation' ), 'vat_rate' => '20', 'nature' => 'biens',
		'label' => 'MacBook Pro', 'document_id' => doc( 'mac.pdf' ) ) );
	$r = tx( 'r1', '2026-06-05', -100000, 'Alexandre Coury', 'transfer' );
	AC_Services::apply_suggestion( $r['id'] );
	foreach ( AC_DB::q( 'SELECT id FROM {expenses}' ) as $e ) {
		AC_Services::save_expense( array( 'status' => 'valide' ), $e['id'] );
	}
	$prep = AC_Closing::prepare();
	ok( $prep['bilan']['equilibre'], 'bilan équilibré' );
	$inc = $prep['compte_de_resultat'];
	$dot = (int) round( 150000 * 275 / 1096 );
	eq( $inc['chiffre_affaires'], 300000, 'chiffre d\'affaires' );
	eq( $inc['resultat_avant_impot'], 300000 - 10000 - 2500 - 750 - $dot, 'résultat' );
	$rf = intdiv( $inc['resultat_avant_impot'], 100 ) * 100;
	eq( $prep['impot']['is_estime_cents'], (int) round( $rf * 15 / 100 ), 'IS au taux réduit' );
	eq( $inc['impot'], $prep['impot']['is_estime_cents'], 'IS comptabilisé' );
	eq( $prep['bloquant'], 0, 'aucune anomalie bloquante ' . wp_json_encode( array_column( array_filter( $prep['anomalies'], function ( $i ) { return 'bloquant' === $i['niveau']; } ), 'message' ), JSON_UNESCAPED_UNICODE ) );
	ok( $prep['obligations']['micro_entreprise'], 'micro entreprise' );
	$fy = $prep['exercice'];
	if ( class_exists( 'AC_Exports' ) ) {
		list( $name, $fec ) = AC_Exports::fec( $fy );
		eq( $name, '123456789FEC20261231.txt', 'nom du FEC' );
		$rows = array_map( function ( $l ) { return explode( "\t", $l ); }, array_values( array_filter( explode( "\r\n", $fec ) ) ) );
		eq( $rows[0][0], 'JournalCode', 'en tête FEC' );
		ok( ! array_filter( $rows, function ( $r ) { return 18 !== count( $r ); } ), '18 colonnes' );
		$d = 0;
		$c = 0;
		foreach ( array_slice( $rows, 1 ) as $r ) {
			$d += (int) str_replace( ',', '', $r[11] );
			$c += (int) str_replace( ',', '', $r[12] );
		}
		eq( $d, $c, 'FEC équilibré' );
		$names = AC_Exports::zip_names( AC_Exports::annual_dossier( $fy ) );
		foreach ( array( 'etats-financiers/bilan.pdf', 'etats-financiers/compte-de-resultat.pdf', 'livres/balance-generale.xlsx', 'livres/grand-livre.xlsx',
			'etats-financiers/immobilisations-amortissements.pdf', 'etats-financiers/creances-dettes.pdf', "fec/$name", 'etats-financiers/projet-decision-associe-unique.pdf' ) as $f ) {
			ok( in_array( $f, $names, true ), "$f dans le dossier annuel" );
		}
	}
	$res = AC_Closing::close( $fy['id'] );
	eq( $res['resultat_cents'], $inc['resultat_net'], 'résultat de clôture' );
	$nxt = $res['exercice_suivant'];
	eq( bal( '512', $nxt['start_date'], $nxt['start_date'] ), bal( '512', $fy['end_date'] ), 'à nouveau banque' );
	raises( function () {
		AC_Services::save_expense( array( 'date' => '2026-06-01', 'supplier_name' => 'Z', 'label' => 'Z', 'ttc_cents' => 100, 'category_id' => cat( 'autres' ), 'payment_method' => 'a_payer' ) );
	}, 'exercice clôturé verrouillé' );
	AC_Closing::allocate_result( $fy['id'], 5000 );
	eq( bal( '1061' ), -5000, 'réserve légale' );
} );

test( 'Assistant sans IA', function () {
	if ( ! class_exists( 'AC_Ai' ) ) {
		return;
	}
	build_year();
	$r = AC_Ai::ask( 'Combien la société me doit-elle ?' );
	ok( 'local' === $r['mode'] && false !== strpos( str_replace( "\u{202f}", ' ', $r['answer'] ), '5 030,00' ), 'réponse : ' . $r['answer'] );
} );

test( 'Facture électronique Factur-X lue sans IA', function () {
	if ( ! class_exists( 'AC_Ai' ) ) {
		return;
	}
	$xml = '<?xml version="1.0"?><rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100" xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">'
		. '<rsm:ExchangedDocument><ram:ID>FA-42</ram:ID><ram:TypeCode>380</ram:TypeCode><ram:IssueDateTime><udt:DateTimeString format="102">20260115</udt:DateTimeString></ram:IssueDateTime></rsm:ExchangedDocument>'
		. '<rsm:SupplyChainTradeTransaction><ram:ApplicableHeaderTradeAgreement><ram:SellerTradeParty><ram:Name>Hébergeur SAS</ram:Name><ram:PostalTradeAddress><ram:CountryID>FR</ram:CountryID></ram:PostalTradeAddress></ram:SellerTradeParty>'
		. '<ram:BuyerTradeParty><ram:Name>Aurea Media</ram:Name></ram:BuyerTradeParty></ram:ApplicableHeaderTradeAgreement><ram:ApplicableHeaderTradeSettlement><ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>'
		. '<ram:ApplicableTradeTax><ram:CalculatedAmount>2.00</ram:CalculatedAmount><ram:BasisAmount>10.00</ram:BasisAmount><ram:RateApplicablePercent>20</ram:RateApplicablePercent></ram:ApplicableTradeTax>'
		. '<ram:SpecifiedTradeSettlementHeaderMonetarySummation><ram:TaxBasisTotalAmount>10.00</ram:TaxBasisTotalAmount><ram:TaxTotalAmount currencyID="EUR">2.00</ram:TaxTotalAmount><ram:GrandTotalAmount>12.00</ram:GrandTotalAmount></ram:SpecifiedTradeSettlementHeaderMonetarySummation>'
		. '</ram:ApplicableHeaderTradeSettlement></rsm:SupplyChainTradeTransaction></rsm:CrossIndustryInvoice>';
	$d = AC_Ai::parse_cii( $xml );
	eq( array( $d['supplier_name'], $d['invoice_number'], $d['date'], $d['total_ttc'], $d['vat_regime'] ), array( 'Hébergeur SAS', 'FA-42', '2026-01-15', '12.00', 'fr' ), 'lecture Factur-X' );
} );

test( 'Sauvegarde complète et restauration', function () {
	if ( ! class_exists( 'AC_Backup' ) ) {
		return;
	}
	build_year();
	$n       = AC_DB::ival( 'SELECT COUNT(*) FROM {entries}' );
	$archive = AC_Backup::full_export();
	AC_DB::exec( 'DELETE FROM {entry_lines}' );
	AC_DB::exec( 'DELETE FROM {entries}' );
	$r = AC_Backup::restore( $archive );
	ok( $r['ok'] && $r['documents'] >= 4, 'restauration' );
	eq( AC_DB::ival( 'SELECT COUNT(*) FROM {entries}' ), $n, 'écritures restaurées' );
	ok( false === strpos( $archive, 'secret' ), 'aucune clé dans l\'archive' );
} );

if ( getenv( 'AC_MOCK' ) ) {
	if ( ! function_exists( 'aurea_compta_ia_autoload' ) && file_exists( dirname( __DIR__, 2 ) . '/aurea-compta-ia/aurea-compta-ia.php' ) ) {
		require dirname( __DIR__, 2 ) . '/aurea-compta-ia/aurea-compta-ia.php';
	}
	$mock_log = sys_get_temp_dir() . '/ac-mock.log';
	$last     = function ( $uri_part ) use ( $mock_log ) {
		$hit = null;
		foreach ( file( $mock_log ) as $l ) {
			$j = json_decode( $l, true );
			if ( false !== strpos( $j['uri'], $uri_part ) ) {
				$hit = $j;
			}
		}
		return $hit;
	};

	test( 'Synchronisation Qonto (serveur simulé)', function () use ( $last ) {
		@unlink( sys_get_temp_dir() . '/ac-mock.log' );
		AC_DB::save_secret( 'qonto_login', 'aurea-media-1234' );
		AC_DB::save_secret( 'qonto_secret_key', 'cle-secrete' );
		$r = AC_Qonto::sync();
		eq( array( $r['imported'], $r['attachments'], $r['balance_cents'] ), array( 2, 1, 450000 ), 'import' );
		eq( AC_Qonto::sync( true )['duplicates'], 2, 'pas de doublon à la resynchronisation' );
		$req = $last( '/v2/transactions' );
		eq( $req['headers']['Authorization'], 'aurea-media-1234:cle-secrete', 'authentification par clé API' );
		ok( false !== strpos( $req['uri'], 'status%5B%5D=completed' ) && false !== strpos( $req['uri'], 'bank_account_id=acc-1' ), 'paramètres' );
		$t = AC_DB::q1( "SELECT * FROM {bank_transactions} WHERE external_id='qonto:uuid-1'" );
		eq( array( $t['amount_cents'], $t['date'], $t['qonto_vat_cents'], $t['qonto_vat_rate'] ), array( -12000, '2026-03-02', 2000, '20' ), 'opération' );
		eq( AC_DB::val( "SELECT qonto_vat_rate FROM {bank_transactions} WHERE external_id='qonto:uuid-2'" ), null, 'taux -1 ignoré' );
		eq( AC_DB::ival( "SELECT COUNT(*) FROM {documents} WHERE external_id='qonto-att:att-1'" ), 1, 'justificatif Qonto téléchargé' );
		$rec = AC_Checks::bank_reconciliation( '2026-03-31' );
		eq( array( $rec['date_reference'], $rec['solde_banque_cents'] ), array( '2026-03-03', 488000 ), 'solde Qonto après la dernière opération utilisé pour le rapprochement' );
	} );

	test( 'Lecture d\'un justificatif par Claude et rapprochement automatique (serveur simulé)', function () use ( $last ) {
		ok( AC_Ai::sdk_installed(), 'extension IA présente' );
		AC_DB::save_secret( 'anthropic_api_key', 'sk-ant-test' );
		update_option( AC_DB::opt( 's_ai' ), array_merge( AC_DB::get_setting( 'ai' ), array( 'enabled' => true ) ), false );
		tx( 'qonto:adobe', '2026-03-02', -12000, 'Adobe' );
		$doc = AC_Services::store_document( 'adobe.pdf', '%PDF-1.4 facture scannee' );
		$r   = AC_Ai::process_document( $doc );
		eq( $r['status'], 'rattache', 'rattachement automatique' );
		$x = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( $r['expense_id'] ) );
		eq( array( $x['ht_cents'], $x['vat_cents'], $x['document_id'], $x['status'], $x['invoice_number'] ), array( 10000, 2000, $doc, 'propose_ia', 'A-77' ), 'dépense proposée' );
		$req = $last( '/v1/messages' );
		eq( $req['body']['model'], 'claude-opus-5-5', 'modèle' );
		eq( $req['body']['fallbacks'], 'default', 'repli serveur' );
		ok( false !== strpos( $req['headers']['anthropic-beta'] ?? '', 'server-side-fallback-2026-07-01' ), 'en tête beta' );
		eq( $req['body']['messages'][0]['content'][0]['source']['media_type'], 'application/pdf', 'document PDF' );
		eq( $req['body']['output_config']['format']['type'], 'json_schema', 'sortie structurée' );
		$a = AC_Ai::ask( 'Combien la société me doit-elle ?' );
		eq( array( $a['mode'], $a['tools'] ), array( 'ia', array( 'compte_courant_associe' ) ), 'assistant avec outil' );
		$req = $last( '/v1/messages' );
		eq( $req['body']['messages'][2]['content'][0]['type'], 'tool_result', 'résultat d\'outil renvoyé' );
		eq( $req['body']['messages'][2]['content'][0]['tool_use_id'], 'toolu_1', 'identifiant d\'outil' );
	} );
}

// Nettoyage : les tables de test sont supprimées.
AC_DB::drop_all();
echo "\n{$GLOBALS['ac_pass']} réussi(s), {$GLOBALS['ac_fail']} échec(s)\n";
exit( $GLOBALS['ac_fail'] ? 1 : 0 );

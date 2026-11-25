<?php
/** Serveurs simulés Qonto et Anthropic pour les tests d'intégration (php -S 127.0.0.1:8099 tests/mock-server.php). */
$uri  = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$body = file_get_contents( 'php://input' );
$log  = sys_get_temp_dir() . '/ac-mock.log';
file_put_contents( $log, json_encode( array( 'method' => $_SERVER['REQUEST_METHOD'], 'uri' => $_SERVER['REQUEST_URI'], 'headers' => getallheaders(), 'body' => json_decode( $body, true ) ) ) . "\n", FILE_APPEND );
header( 'Content-Type: application/json' );
if ( '/v2/organization' === $uri ) {
	echo json_encode( array( 'organization' => array( 'name' => 'Aurea Media', 'bank_accounts' => array( array( 'id' => 'acc-1', 'iban' => 'FR76', 'balance_cents' => 450000, 'main' => true, 'status' => 'active' ) ) ) ) );
} elseif ( '/v2/transactions' === $uri ) {
	$page = (int) ( $_GET['page'] ?? 1 );
	$tx   = 1 === $page
		? array( array( 'id' => 'uuid-1', 'amount_cents' => 12000, 'side' => 'debit', 'operation_type' => 'card', 'status' => 'completed', 'label' => 'ADOBE', 'clean_counterparty_name' => 'Adobe',
			'settled_at' => '2026-03-02T10:00:00Z', 'emitted_at' => '2026-03-01T10:00:00Z', 'vat_amount_cents' => 2000, 'vat_rate' => 20, 'attachment_ids' => array( 'att-1' ), 'settled_balance_cents' => 438000 ) )
		: array( array( 'id' => 'uuid-2', 'amount_cents' => 50000, 'side' => 'credit', 'operation_type' => 'transfer', 'status' => 'completed', 'label' => 'CLIENT X',
			'settled_at' => '2026-03-03T10:00:00Z', 'emitted_at' => '2026-03-03T10:00:00Z', 'vat_rate' => -1, 'attachment_ids' => array(), 'settled_balance_cents' => 488000 ) );
	echo json_encode( array( 'transactions' => $tx, 'meta' => array( 'current_page' => $page, 'next_page' => 1 === $page ? 2 : null ) ) );
} elseif ( '/v2/attachments/att-1' === $uri ) {
	echo json_encode( array( 'attachment' => array( 'id' => 'att-1', 'file_name' => 'facture-adobe.pdf', 'url' => 'http://127.0.0.1:8099/files/att-1' ) ) );
} elseif ( '/files/att-1' === $uri ) {
	header( 'Content-Type: application/pdf' );
	echo '%PDF-1.4 facture adobe simulee';
} elseif ( '/v1/messages' === $uri ) {
	$req = json_decode( $body, true );
	$has_tools  = ! empty( $req['tools'] );
	$has_result = false;
	foreach ( $req['messages'] as $m ) {
		foreach ( (array) ( is_array( $m['content'] ) ? $m['content'] : array() ) as $c ) {
			$has_result = $has_result || ( 'tool_result' === ( $c['type'] ?? '' ) );
		}
	}
	$base = array( 'id' => 'msg_1', 'type' => 'message', 'role' => 'assistant', 'model' => $req['model'], 'stop_details' => null, 'stop_sequence' => null,
		'usage' => array( 'input_tokens' => 10, 'output_tokens' => 10 ) );
	if ( $has_tools && ! $has_result ) {
		echo json_encode( $base + array( 'stop_reason' => 'tool_use', 'content' => array( array( 'type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'compte_courant_associe', 'input' => new stdClass() ) ) ) );
	} elseif ( $has_tools ) {
		echo json_encode( $base + array( 'stop_reason' => 'end_turn', 'content' => array( array( 'type' => 'text', 'text' => 'La société vous doit 5 030,00 € (compte 455).' ) ) ) );
	} else {
		echo json_encode( $base + array( 'stop_reason' => 'end_turn', 'content' => array( array( 'type' => 'text', 'text' => json_encode( array(
			'document_type' => 'facture', 'supplier_name' => 'Adobe', 'supplier_country' => 'IE', 'supplier_vat_number' => '', 'customer_name' => 'Aurea Media',
			'invoice_number' => 'A-77', 'date' => '2026-03-01', 'currency' => 'EUR', 'total_ht' => '100.00', 'total_vat' => '20.00', 'total_ttc' => '120.00',
			'vat_lines' => array( array( 'rate' => '20', 'base' => '100.00', 'vat' => '20.00' ) ), 'vat_regime' => 'fr', 'nature' => 'services',
			'description' => 'Abonnement Creative Cloud', 'category_code' => 'logiciels', 'is_asset_candidate' => false, 'anomalies' => array(), 'confidence' => 0.97 ) ) ) ) ) );
	}
} else {
	http_response_code( 404 );
	echo '{}';
}

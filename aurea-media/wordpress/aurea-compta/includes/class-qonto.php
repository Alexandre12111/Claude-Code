<?php
/**
 * Synchronisation Qonto par l'API officielle (Business API v2) et import CSV de secours.
 *
 * * Authentification par clé API : en-tête « Authorization: {identifiant}:{clé secrète} ».
 * * GET /v2/organization : comptes et soldes. GET /v2/transactions : opérations paginées,
 *   filtre updated_at_from pour ne récupérer que les nouveautés. GET /v2/attachments/{id} :
 *   justificatif (adresse valable 30 minutes). Aucun scraping.
 */
defined( 'ABSPATH' ) || exit;

class AC_Qonto {

	const BASES = array( 'production' => 'https://thirdparty.qonto.com', 'sandbox' => 'https://thirdparty-sandbox.staging.qonto.co' );

	private static function headers(): array {
		$s = AC_DB::secrets();
		if ( empty( $s['qonto_login'] ) || empty( $s['qonto_secret_key'] ) ) {
			throw new AC_Error( 'Identifiant et clé secrète Qonto non configurés (Paramètres, onglet Qonto).' );
		}
		$h = array( 'Authorization' => $s['qonto_login'] . ':' . $s['qonto_secret_key'], 'Accept' => 'application/json' );
		if ( 'sandbox' === AC_DB::get_setting( 'qonto' )['environment'] && ! empty( $s['qonto_staging_token'] ) ) {
			$h['X-Qonto-Staging-Token'] = $s['qonto_staging_token'];
		}
		return $h;
	}

	private static function get( string $path, array $params = array() ): array {
		$base = defined( 'AC_QONTO_BASE_URL' ) ? AC_QONTO_BASE_URL : ( self::BASES[ AC_DB::get_setting( 'qonto' )['environment'] ] ?? self::BASES['production'] ); // constante réservée aux tests
		$qs   = '';
		foreach ( $params as $k => $v ) {
			foreach ( (array) $v as $vv ) {
				$qs .= ( $qs ? '&' : '?' ) . rawurlencode( $k ) . '=' . rawurlencode( (string) $vv );
			}
		}
		for ( $attempt = 0; $attempt < 4; $attempt++ ) {
			$r = wp_remote_get( $base . $path . $qs, array( 'headers' => self::headers(), 'timeout' => 30 ) );
			if ( is_wp_error( $r ) ) {
				throw new AC_Error( 'Qonto injoignable : ' . $r->get_error_message() );
			}
			$code = wp_remote_retrieve_response_code( $r );
			if ( in_array( $code, array( 429, 503 ), true ) ) {
				sleep( min( 10, (int) wp_remote_retrieve_header( $r, 'retry-after' ) ?: 2 ) );
				continue;
			}
			if ( 401 === $code ) {
				throw new AC_Error( "Qonto refuse l'authentification : vérifiez l'identifiant et la clé API." );
			}
			if ( $code >= 400 ) {
				throw new AC_Error( "Erreur Qonto $code : " . mb_substr( wp_remote_retrieve_body( $r ), 0, 200 ) );
			}
			return json_decode( wp_remote_retrieve_body( $r ), true ) ?: array();
		}
		throw new AC_Error( 'Qonto indisponible (trop de requêtes), réessayez plus tard.' );
	}

	public static function organization(): array {
		return self::get( '/v2/organization' )['organization'] ?? array();
	}

	public static function normalize( array $t, ?string $account = null ): array {
		$amount = isset( $t['amount_cents'] ) ? (int) $t['amount_cents'] : AC_Money::to_cents( (string) ( $t['amount'] ?? 0 ) );
		$rate   = $t['vat_rate'] ?? null;
		return array(
			'external_id' => 'qonto:' . $t['id'], 'bank_account' => $t['bank_account_id'] ?? $account,
			'date' => substr( $t['settled_at'] ?: $t['emitted_at'], 0, 10 ), 'emitted_at' => $t['emitted_at'] ?? null,
			'amount_cents' => 'credit' === ( $t['side'] ?? '' ) ? $amount : -$amount, 'label' => $t['label'] ?? null,
			'counterparty' => ( $t['clean_counterparty_name'] ?? '' ) ?: ( $t['label'] ?? null ), 'operation_type' => $t['operation_type'] ?? null,
			'reference' => $t['reference'] ?? null, 'note' => $t['note'] ?? null,
			'vat_cents' => ! empty( $t['vat_amount_cents'] ) ? (int) $t['vat_amount_cents'] : null,
			'vat_rate' => ( null === $rate || -1 === $rate || '-1' === $rate ) ? null : (string) $rate,
			'attachment_ids' => $t['attachment_ids'] ?? array(), 'raw' => $t,
		);
	}

	/** Synchronise les nouvelles opérations (ou tout l'historique). */
	public static function sync( bool $full = false ): array {
		$q       = AC_DB::get_setting( 'qonto' );
		$started = current_time( 'c' );
		$org     = self::organization();
		$accounts = array_values( array_filter( $org['bank_accounts'] ?? array(), function ( $a ) { return 'closed' !== ( $a['status'] ?? 'active' ); } ) );
		if ( $q['bank_account_id'] ) {
			$accounts = array_values( array_filter( $accounts, function ( $a ) use ( $q ) { return $a['id'] === $q['bank_account_id']; } ) ) ?: $accounts;
		}
		$main = null;
		foreach ( $accounts as $a ) {
			$main = $main ?: ( ! empty( $a['main'] ) ? $a : null );
		}
		$main = $main ?: ( $accounts[0] ?? null );
		if ( ! $main ) {
			throw new AC_Error( 'Aucun compte bancaire actif trouvé.' );
		}
		$params = array( 'bank_account_id' => $main['id'], 'status[]' => 'completed', 'sort_by' => 'updated_at:asc', 'per_page' => 100,
			'includes[]' => array( 'vat_details', 'attachments' ) );
		if ( $q['last_sync'] && ! $full ) {
			$params['updated_at_from'] = gmdate( 'c', strtotime( $q['last_sync'] ) - 3600 );
		}
		$items = array();
		$page  = 1;
		do {
			$params['page'] = $page;
			$data           = self::get( '/v2/transactions', $params );
			foreach ( $data['transactions'] ?? array() as $t ) {
				if ( ! empty( $t['settled_at'] ) || ! empty( $t['emitted_at'] ) ) {
					$items[] = self::normalize( $t, $main['id'] );
				}
			}
			$page = $data['meta']['next_page'] ?? null;
		} while ( $page );
		$res  = AC_Services::import_transactions( $items, 'qonto' );
		$docs = self::fetch_attachments( 15 );
		$q    = array_merge( $q, array( 'last_sync' => $started, 'balance_cents' => $main['balance_cents'] ?? null, 'balance_at' => $started, 'iban' => $main['iban'] ?? null ) );
		update_option( AC_DB::opt( 's_qonto' ), $q, false );
		update_option( AC_DB::opt( 's_bank_statement' ), array( 'date' => substr( current_time( 'Y-m-d' ), 0, 10 ), 'balance_cents' => $main['balance_cents'] ?? null, 'source' => 'qonto' ), false );
		return $res + array( 'attachments' => $docs['fetched'], 'attachments_remaining' => $docs['remaining'], 'balance_cents' => $main['balance_cents'] ?? null );
	}

	/** Justificatifs Qonto pas encore téléchargés : [opération, identifiant de pièce]. */
	public static function pending_attachments(): array {
		$known = array();
		foreach ( AC_DB::q( "SELECT external_id FROM {documents} WHERE external_id LIKE 'qonto-att:%'" ) as $d ) {
			$known[ substr( $d['external_id'], 10 ) ] = true;
		}
		$out = array();
		foreach ( AC_DB::q( "SELECT * FROM {bank_transactions} WHERE source='qonto' AND attachment_ids IS NOT NULL AND attachment_ids<>'[]' ORDER BY date DESC" ) as $t ) {
			foreach ( json_decode( (string) $t['attachment_ids'], true ) ?: array() as $att ) {
				if ( ! isset( $known[ $att ] ) ) {
					$out[] = array( $t, (string) $att );
				}
			}
		}
		return $out;
	}

	/**
	 * Télécharge les justificatifs déposés dans Qonto, les relie à leur opération et à la dépense correspondante.
	 * Traitement par lots (budget de temps) pour rester sous la durée maximale d'exécution de l'hébergement.
	 */
	public static function fetch_attachments( int $budget_seconds = 20 ): array {
		$start = microtime( true );
		$n     = 0;
		$errors = 0;
		$todo  = self::pending_attachments();
		foreach ( $todo as list( $t, $att ) ) {
			if ( microtime( true ) - $start > $budget_seconds ) {
				break;
			}
			try {
				$a   = self::get( "/v2/attachments/$att" )['attachment'] ?? array();
				$url = html_entity_decode( (string) ( $a['url'] ?? '' ), ENT_QUOTES );
				$r   = $url ? wp_remote_get( $url, array( 'timeout' => 30 ) ) : null;
				if ( ! $r || is_wp_error( $r ) || 200 !== wp_remote_retrieve_response_code( $r ) ) {
					$errors++;
					continue;
				}
				AC_DB::tx( function () use ( $a, $att, $r, $t ) {
					$doc = AC_Services::store_document( ( $a['file_name'] ?? '' ) ?: "$att.pdf", wp_remote_retrieve_body( $r ), 'justificatif', $t['date'], $t['counterparty'], 'qonto', "qonto-att:$att" );
					// Pièce déjà présente (même fichier) : on la marque comme venant de Qonto pour ne plus la retélécharger.
					AC_DB::exec( 'UPDATE {documents} SET external_id=IFNULL(external_id, ?), bank_transaction_id=IFNULL(bank_transaction_id, ?) WHERE id=?', array( "qonto-att:$att", $t['id'], $doc ) );
					if ( ! AC_DB::q1( 'SELECT id FROM {documents} WHERE external_id=?', array( "qonto-att:$att" ) ) ) {
						AC_DB::insert( 'documents', array( 'path' => "alias:$doc", 'sha256' => hash( 'sha256', "alias-$att-$doc" ), 'kind' => 'alias', 'source' => 'qonto',
							'external_id' => "qonto-att:$att", 'bank_transaction_id' => $t['id'], 'uploaded_at' => AC_DB::now() ) );
					}
					$exp = AC_DB::q1( "SELECT x.id FROM {expenses} x JOIN {allocations} al ON al.target_type='depense' AND al.target_id=x.id WHERE al.bank_transaction_id=? AND x.document_id IS NULL", array( $t['id'] ) );
					if ( $exp ) {
						AC_Services::save_expense( array( 'document_id' => $doc ), $exp['id'], 'Justificatif Qonto rattaché' );
					}
				} );
				$n++;
			} catch ( AC_Error $e ) {
				$errors++;
				continue;
			}
		}
		$linked = AC_Services::relink_documents();
		return array( 'fetched' => $n, 'errors' => $errors, 'linked' => $linked, 'remaining' => max( 0, count( $todo ) - $n - $errors ) );
	}

	/** Synchronisation automatique si l'intervalle est écoulé (tâche planifiée ou ouverture de l'application). */
	public static function maybe_sync(): ?array {
		$q = AC_DB::get_setting( 'qonto' );
		if ( empty( $q['enabled'] ) || get_transient( AC_DB::opt( 'sync_lock' ) ) ) {
			return null;
		}
		$due = ! $q['last_sync'] || time() - strtotime( $q['last_sync'] ) > max( 15, (int) $q['auto_sync_minutes'] ) * 60;
		if ( ! $due ) {
			return null;
		}
		set_transient( AC_DB::opt( 'sync_lock' ), 1, 300 );
		try {
			$r = self::sync();
			delete_option( AC_DB::opt( 'sync_error' ) );
			return $r;
		} catch ( Throwable $e ) {
			update_option( AC_DB::opt( 'sync_error' ), $e->getMessage(), false );
			return null;
		} finally {
			delete_transient( AC_DB::opt( 'sync_lock' ) );
		}
	}

	// ------------------------------------------------------------ CSV de secours
	const CSV_ALIASES = array(
		'date' => array( "date de l'opération (utc)", 'date de règlement (utc)', 'settlement date (utc)', 'date (utc)', 'date', 'settled_at', 'operation date' ),
		'amount' => array( 'montant total (ttc)', 'total amount (incl. vat)', 'montant', 'amount', 'montant (ttc)' ),
		'debit' => array( 'débit', 'debit' ), 'credit' => array( 'crédit', 'credit' ),
		'counterparty' => array( 'nom de la contrepartie', 'contrepartie', 'counterparty name', 'counterparty', 'tiers', 'bénéficiaire' ),
		'label' => array( 'libellé', 'label', 'description', 'référence', 'reference' ), 'reference' => array( 'référence', 'reference' ),
		'id' => array( 'id de la transaction', 'transaction id', 'id', 'identifiant' ), 'vat' => array( 'montant de la tva', 'vat amount', 'tva' ),
		'status' => array( 'statut', 'status' ), 'type' => array( "type d'opération", 'operation type', 'type' ),
	);

	public static function parse_csv( string $content ): array {
		$content = preg_replace( '/^\xEF\xBB\xBF/', '', $content );
		$sample  = substr( $content, 0, 4000 );
		$delim   = substr_count( $sample, ';' ) > substr_count( $sample, ',' ) ? ';' : ',';
		$h       = fopen( 'php://temp', 'w+' );
		fwrite( $h, $content );
		rewind( $h );
		$head = fgetcsv( $h, 0, $delim, '"', '' );
		if ( ! $head ) {
			throw new AC_Error( 'CSV vide.' );
		}
		$idx = array();
		foreach ( $head as $i => $name ) {
			$idx[ mb_strtolower( trim( $name ) ) ] = $i;
		}
		$col = array();
		foreach ( self::CSV_ALIASES as $k => $aliases ) {
			$col[ $k ] = null;
			foreach ( $aliases as $a ) {
				if ( isset( $idx[ $a ] ) ) {
					$col[ $k ] = $idx[ $a ];
					break;
				}
			}
		}
		if ( null === $col['date'] || ( null === $col['amount'] && null === $col['debit'] && null === $col['credit'] ) ) {
			throw new AC_Error( 'Colonnes « date » et « montant » introuvables dans le CSV.' );
		}
		$get   = function ( $row, $k ) use ( $col ) { return null === $col[ $k ] ? null : trim( (string) ( $row[ $col[ $k ] ] ?? '' ) ); };
		$items = array();
		$seen  = array();
		while ( ( $row = fgetcsv( $h, 0, $delim, '"', '' ) ) !== false ) {
			if ( in_array( mb_strtolower( (string) $get( $row, 'status' ) ), array( 'declined', 'refusée', 'refusé', 'annulée', 'reversed' ), true ) ) {
				continue;
			}
			$raw = (string) $get( $row, 'date' );
			if ( '' === $raw ) {
				continue;
			}
			$d = null;
			foreach ( array( 'Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y' ) as $fmt ) {
				$dt = DateTime::createFromFormat( '!' . $fmt, substr( $raw, 0, 10 ) );
				if ( $dt ) {
					$d = $dt->format( 'Y-m-d' );
					break;
				}
			}
			if ( ! $d ) {
				continue;
			}
			$amount = null !== $col['amount'] ? AC_Money::to_cents( $get( $row, 'amount' ) )
				: AC_Money::to_cents( $get( $row, 'credit' ) ?: '0' ) - abs( AC_Money::to_cents( $get( $row, 'debit' ) ?: '0' ) );
			$label = (string) $get( $row, 'label' );
			$cp    = $get( $row, 'counterparty' ) ?: $label;
			$ext   = $get( $row, 'id' );
			if ( ! $ext ) {
				$key          = "$d|$amount|$cp|$label";
				$seen[ $key ] = ( $seen[ $key ] ?? 0 ) + 1;
				$ext          = 'csv:' . substr( sha1( $key . '|' . $seen[ $key ] ), 0, 20 );
			} else {
				$ext = "qontocsv:$ext";
			}
			$vat     = $get( $row, 'vat' );
			$items[] = array( 'external_id' => $ext, 'date' => $d, 'amount_cents' => $amount, 'label' => $label ?: $cp, 'counterparty' => $cp,
				'reference' => $get( $row, 'reference' ), 'vat_cents' => $vat ? abs( AC_Money::to_cents( $vat ) ) : null, 'operation_type' => $get( $row, 'type' ) ?: null,
				'raw' => $row );
		}
		return $items;
	}
}

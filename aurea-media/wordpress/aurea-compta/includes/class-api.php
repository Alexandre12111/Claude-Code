<?php
/**
 * API REST (wp-json/aurea/v1/...), réservée aux administrateurs connectés.
 * Les chemins reprennent ceux de l'interface : /api/xxx côté navigateur → aurea/v1/xxx.
 */
defined( 'ABSPATH' ) || exit;

class AC_Api {

	const NS = 'aurea/v1';

	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes(): void {
		register_rest_route( self::NS, '/(?P<path>.+)', array(
			'methods'             => 'GET, POST, PUT, DELETE',
			'callback'            => array( __CLASS__, 'dispatch' ),
			'permission_callback' => function () {
				return current_user_can( AC_CAP );
			},
		) );
	}

	/** Table des routes : [méthode, motif, fonction]. */
	private static function table(): array {
		return array(
			array( 'GET', 'overview', 'overview' ),
			array( 'GET', 'settings', 'get_settings' ),
			array( 'PUT', 'settings/(\w+)', 'put_setting' ),
			array( 'PUT', 'secrets', 'put_secrets' ),
			array( 'GET', 'fiscal-years', 'fiscal_years' ),
			array( 'POST', 'fiscal-years', 'add_fiscal_year' ),
			array( 'GET', 'categories', 'categories' ),
			array( 'POST', 'categories', 'save_category' ),
			array( 'GET', 'accounts', 'accounts' ),
			array( 'POST', 'accounts', 'add_account' ),
			array( 'GET', 'parties', 'parties' ),
			array( 'GET', 'transactions', 'transactions' ),
			array( 'POST', 'transactions', 'add_transaction' ),
			array( 'POST', 'transactions/accept-all', 'accept_all' ),
			array( 'GET', 'transactions/groups', 'tx_groups' ),
			array( 'POST', 'transactions/bulk', 'tx_bulk' ),
			array( 'POST', 'transactions/(\d+)/document', 'tx_document' ),
			array( 'POST', 'expenses/(\d+)/document', 'expense_document' ),
			array( 'POST', 'qonto/attachments', 'qonto_attachments' ),
			array( 'POST', 'reprise', 'reprise' ),
			array( 'GET', 'mileage', 'mileage' ),
			array( 'POST', 'mileage', 'mileage_add' ),
			array( 'DELETE', 'mileage/(\d+)', 'mileage_delete' ),
			array( 'GET', 'mileage/pdf', 'mileage_pdf' ),
			array( 'GET', 'transactions/(\d+)', 'transaction' ),
			array( 'POST', 'transactions/(\d+)/classify', 'classify' ),
			array( 'POST', 'transactions/(\d+)/accept', 'accept' ),
			array( 'POST', 'qonto/sync', 'qonto_sync' ),
			array( 'POST', 'qonto/auto', 'qonto_auto' ),
			array( 'POST', 'qonto/test', 'qonto_test' ),
			array( 'POST', 'import/csv', 'import_csv' ),
			array( 'POST', 'documents', 'upload' ),
			array( 'GET', 'documents', 'documents' ),
			array( 'GET', 'documents/(\d+)/file', 'document_file' ),
			array( 'POST', 'documents/(\d+)/process', 'process_doc' ),
			array( 'POST', 'documents/(\d+)/confirm', 'confirm_doc' ),
			array( 'DELETE', 'documents/(\d+)', 'delete_doc' ),
			array( 'GET', 'invoices', 'invoices' ),
			array( 'GET', 'invoices/next-number', 'next_number' ),
			array( 'GET', 'invoices/(\d+)', 'invoice' ),
			array( 'POST', 'invoices', 'create_invoice' ),
			array( 'PUT', 'invoices/(\d+)', 'update_invoice' ),
			array( 'DELETE', 'invoices/(\d+)', 'delete_invoice' ),
			array( 'GET', 'expenses', 'expenses' ),
			array( 'GET', 'expenses/(\d+)', 'expense' ),
			array( 'POST', 'expenses', 'create_expense' ),
			array( 'PUT', 'expenses/(\d+)', 'update_expense' ),
			array( 'POST', 'expenses/(\d+)/validate', 'validate_expense' ),
			array( 'DELETE', 'expenses/(\d+)', 'delete_expense' ),
			array( 'GET', 'associate', 'associate' ),
			array( 'POST', 'associate/movements', 'add_movement' ),
			array( 'POST', 'associate/movements/(\d+)/link', 'link_movement' ),
			array( 'DELETE', 'associate/movements/(\d+)', 'delete_movement' ),
			array( 'GET', 'assets', 'assets' ),
			array( 'POST', 'assets', 'add_asset' ),
			array( 'POST', 'assets/(\d+)/dispose', 'dispose' ),
			array( 'GET', 'vat/periods', 'vat_periods' ),
			array( 'GET', 'vat/compute', 'vat_compute' ),
			array( 'POST', 'vat/prepare', 'vat_prepare' ),
			array( 'POST', 'vat/returns/(\d+)/validate', 'vat_validate' ),
			array( 'POST', 'vat/returns/(\d+)/filed', 'vat_filed' ),
			array( 'POST', 'vat/returns/(\d+)/adjust', 'vat_adjust' ),
			array( 'GET', 'vat/export', 'vat_export' ),
			array( 'GET', 'entries', 'entries' ),
			array( 'POST', 'entries', 'add_entry' ),
			array( 'POST', 'entries/validate', 'validate_entries' ),
			array( 'POST', 'entries/(\d+)/reverse', 'reverse' ),
			array( 'GET', 'reports/([\w-]+)', 'report' ),
			array( 'GET', 'audit', 'audit' ),
			array( 'GET', 'tax/estimate', 'tax_estimate' ),
			array( 'GET', 'month/([\d-]+)/check', 'month_check' ),
			array( 'POST', 'month/([\d-]+)/close', 'month_close' ),
			array( 'GET', 'month/([\d-]+)/export', 'month_export' ),
			array( 'GET', 'closing/prepare', 'closing_prepare' ),
			array( 'POST', 'closing/(\d+)/close', 'closing_close' ),
			array( 'POST', 'closing/(\d+)/allocate', 'closing_allocate' ),
			array( 'GET', 'closing/(\d+)/export', 'closing_export' ),
			array( 'GET', 'closing/(\d+)/fec', 'closing_fec' ),
			array( 'GET', 'closing/(\d+)/statements/([\w-]+)', 'closing_statement' ),
			array( 'GET', 'backups', 'backups' ),
			array( 'POST', 'backups', 'backup_now' ),
			array( 'GET', 'backup/export', 'full_export' ),
			array( 'POST', 'backup/restore', 'restore' ),
			array( 'POST', 'assistant', 'assistant' ),
			array( 'GET', 'search', 'search' ),
		);
	}

	public static function dispatch( WP_REST_Request $req ) {
		$path   = trim( $req['path'], '/' );
		$method = $req->get_method();
		foreach ( self::table() as list( $m, $pattern, $fn ) ) {
			if ( $m === $method && preg_match( "#^$pattern$#", $path, $args ) ) {
				array_shift( $args );
				try {
					$out = call_user_func_array( array( __CLASS__, 'r_' . $fn ), array_merge( array( $req ), $args ) );
					return rest_ensure_response( $out );
				} catch ( AC_Error $e ) {
					return new WP_REST_Response( array( 'detail' => $e->getMessage() ), 400 );
				} catch ( Throwable $e ) {
					error_log( 'Aurea Compta : ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine() );
					return new WP_REST_Response( array( 'detail' => 'Erreur inattendue : ' . $e->getMessage() ), 500 );
				}
			}
		}
		return new WP_REST_Response( array( 'detail' => 'Introuvable' ), 404 );
	}

	// ------------------------------------------------------------ utilitaires
	private static function body( WP_REST_Request $r ): array {
		return (array) ( $r->get_json_params() ?: $r->get_body_params() ?: array() );
	}

	private static function q( WP_REST_Request $r, string $k, $d = null ) {
		$v = $r->get_param( $k );
		return null === $v || '' === $v ? $d : $v;
	}

	/** Envoi d'un fichier (PDF, ZIP, justificatif) puis arrêt. */
	private static function send( string $content, string $mime, string $name, bool $inline = false ) {
		nocache_headers();
		header( 'Content-Type: ' . $mime );
		header( 'Content-Length: ' . strlen( $content ) );
		header( 'Content-Disposition: ' . ( $inline ? 'inline' : 'attachment' ) . '; filename="' . str_replace( '"', '', $name ) . '"' );
		header( 'X-Content-Type-Options: nosniff' );
		echo $content; // phpcs:ignore
		exit;
	}

	private static function uploaded( WP_REST_Request $r ): array {
		$f = $r->get_file_params()['file'] ?? null;
		if ( ! $f || UPLOAD_ERR_OK !== $f['error'] ) {
			throw new AC_Error( 'Fichier non reçu (taille maximale de votre hébergement : ' . size_format( wp_max_upload_size() ) . ').' );
		}
		return array( $f['name'], file_get_contents( $f['tmp_name'] ) );
	}

	// ------------------------------------------------------------ vue d'ensemble et paramètres
	public static function r_overview( $r ) {
		$fy   = AC_Closing::fiscal_year();
		$inc  = AC_Reports::income_statement( $fy['start_date'], $fy['end_date'] );
		$q    = AC_DB::get_setting( 'qonto' );
		$a    = AC_Services::associate_summary();
		$book = AC_Ledger::balance( '512' );
		$conf = AC_DB::get_setting( 'confirmed' );
		return array(
			'exercice' => $fy,
			'solde_bancaire_cents' => isset( $q['balance_cents'] ) && null !== $q['balance_cents'] ? (int) $q['balance_cents'] : $book,
			'solde_source' => isset( $q['balance_cents'] ) && null !== $q['balance_cents'] ? 'Qonto au ' . wp_date( 'd/m/Y H:i', strtotime( $q['balance_at'] ) ) : 'comptabilité',
			'solde_comptable_cents' => $book, 'recettes_cents' => $inc['chiffre_affaires'], 'depenses_cents' => $inc['total_charges'] - $inc['impot'],
			'resultat_cents' => $inc['resultat_avant_impot'], 'tva' => AC_Vat::current_estimate(),
			'apports_non_rembourses_cents' => $a['du_par_la_societe_cents'], 'compte_courant_cents' => $a['solde_cents'],
			'a_verifier' => array(
				'operations' => AC_DB::ival( "SELECT COUNT(*) FROM {bank_transactions} WHERE status IN ('a_traiter','propose')" ),
				'depenses' => AC_DB::ival( "SELECT COUNT(*) FROM {expenses} WHERE status IN ('a_valider','propose_ia','importe')" ),
				'justificatifs_manquants' => AC_DB::ival( "SELECT COUNT(*) FROM {expenses} x LEFT JOIN {categories} c ON c.id=x.category_id WHERE x.document_id IS NULL AND IFNULL(c.rule,'') NOT IN ('personnel','ik')" ),
				'pieces_non_traitees' => AC_DB::ival( "SELECT COUNT(*) FROM {documents} d WHERE d.kind='justificatif' AND d.bank_transaction_id IS NULL AND d.id NOT IN (SELECT document_id FROM {expenses} WHERE document_id IS NOT NULL)
					AND d.id NOT IN (SELECT document_id FROM {sales_invoices} WHERE document_id IS NOT NULL)" ),
			),
			'parametres_provisoires' => array_keys( array_filter( $conf, function ( $v ) { return ! $v; } ) ),
			'sync' => array( 'enabled' => ! empty( $q['enabled'] ), 'last_sync' => $q['last_sync'], 'last_error' => get_option( AC_DB::opt( 'sync_error' ) ) ?: null ),
			'mode_demo' => false,
		);
	}

	public static function r_get_settings( $r ) {
		$out = array();
		foreach ( array_keys( AC_DB::defaults() ) as $k ) {
			$out[ $k ] = AC_DB::get_setting( $k );
		}
		$out['secrets']       = AC_DB::masked_secrets();
		$out['ia_extension']  = AC_Ai::sdk_installed();
		$out['app_url']       = home_url( '/compta/' );
		$out['upload_max']    = size_format( wp_max_upload_size() );
		return $out;
	}

	public static function r_put_setting( $r, $key ) {
		if ( ! isset( AC_DB::defaults()[ $key ] ) ) {
			throw new AC_Error( 'Paramètre inconnu' );
		}
		$v = self::body( $r );
		AC_DB::tx( function () use ( $key, $v ) {
			AC_DB::set_setting( $key, $v );
			if ( in_array( $key, array( 'company', 'tax' ), true ) ) {
				$c         = AC_DB::get_setting( 'confirmed' );
				$c[ $key ] = true;
				update_option( AC_DB::opt( 's_confirmed' ), $c, false );
			}
			if ( 'tax' === $key ) {
				foreach ( AC_DB::q( 'SELECT id FROM {sales_invoices}' ) as $i ) {
					AC_Posting::post_sales_invoice( $i['id'], 'Changement de régime de TVA' );
				}
				foreach ( AC_DB::q( "SELECT DISTINCT bank_transaction_id t FROM {allocations} WHERE target_type='vente'" ) as $t ) {
					AC_Posting::post_bank_transaction( (int) $t['t'], 'Changement de régime de TVA' );
				}
			}
		} );
		return array( 'ok' => true );
	}

	public static function r_put_secrets( $r ) {
		foreach ( self::body( $r ) as $k => $v ) {
			if ( in_array( $k, AC_DB::SECRET_KEYS, true ) && null !== $v ) {
				AC_DB::save_secret( $k, $v );
			}
		}
		return AC_DB::masked_secrets();
	}

	public static function r_fiscal_years( $r ) {
		return AC_DB::q( 'SELECT * FROM {fiscal_years} ORDER BY start_date' );
	}

	public static function r_add_fiscal_year( $r ) {
		$d = self::body( $r );
		if ( empty( $d['start_date'] ) || empty( $d['end_date'] ) || $d['end_date'] <= $d['start_date'] ) {
			throw new AC_Error( "Dates d'exercice invalides." );
		}
		if ( ! empty( $d['replace_id'] ) ) {
			$fy = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE id=?', array( (int) $d['replace_id'] ) );
			if ( 'closed' === $fy['status'] ) {
				throw new AC_Error( 'Exercice clôturé.' );
			}
			AC_DB::exec( 'UPDATE {fiscal_years} SET start_date=?, end_date=? WHERE id=?', array( $d['start_date'], $d['end_date'], $fy['id'] ) );
			AC_DB::audit( 'modification', 'exercice', $fy['id'], $fy, $d );
		} else {
			if ( AC_DB::q1( 'SELECT id FROM {fiscal_years} WHERE NOT (end_date<? OR start_date>?)', array( $d['start_date'], $d['end_date'] ) ) ) {
				throw new AC_Error( 'Cet exercice chevauche un exercice existant.' );
			}
			AC_DB::insert( 'fiscal_years', array( 'start_date' => $d['start_date'], 'end_date' => $d['end_date'] ) );
		}
		return array( 'ok' => true );
	}

	public static function r_categories( $r ) {
		return AC_DB::q( 'SELECT * FROM {categories} ORDER BY is_asset, name' );
	}

	public static function r_save_category( $r ) {
		$d = self::body( $r );
		if ( ! empty( $d['id'] ) ) {
			$before = AC_DB::q1( 'SELECT * FROM {categories} WHERE id=?', array( (int) $d['id'] ) );
			AC_DB::update( 'categories', array( 'name' => $d['name'], 'account' => $d['account'], 'vat_recovery' => (int) $d['vat_recovery'],
				'deductible' => (int) $d['deductible'], 'active' => (int) ( $d['active'] ?? 1 ) ), (int) $d['id'] );
			AC_DB::audit( 'modification', 'categorie', (int) $d['id'], $before, $d );
		} else {
			AC_DB::insert( 'categories', array( 'code' => AC_Services::slug( $d['name'] ) . '-' . wp_rand( 100, 999 ), 'name' => $d['name'], 'account' => $d['account'],
				'vat_recovery' => (int) ( $d['vat_recovery'] ?? 100 ), 'deductible' => (int) ( $d['deductible'] ?? 100 ), 'rule' => 'standard' ) );
		}
		return array( 'ok' => true );
	}

	public static function r_accounts( $r ) {
		return AC_DB::q( 'SELECT * FROM {accounts} ORDER BY number' );
	}

	public static function r_add_account( $r ) {
		$d = self::body( $r );
		if ( ! ctype_digit( (string) ( $d['number'] ?? '' ) ) ) {
			throw new AC_Error( 'Numéro de compte invalide.' );
		}
		AC_DB::exec( 'REPLACE INTO {accounts}(number,label,custom) VALUES(?,?,1)', array( str_pad( $d['number'], 6, '0' ), $d['label'] ) );
		return array( 'ok' => true );
	}

	public static function r_parties( $r ) {
		$k = self::q( $r, 'kind' );
		return $k ? AC_DB::q( 'SELECT * FROM {parties} WHERE kind=? ORDER BY name', array( $k ) ) : AC_DB::q( 'SELECT * FROM {parties} ORDER BY name' );
	}

	// ------------------------------------------------------------ banque
	public static function r_transactions( $r ) {
		$sql = 'SELECT id, source, external_id, date, amount_cents, label, counterparty, operation_type, reference, note, qonto_vat_cents, qonto_vat_rate, status, suggestion, comment, attachment_ids FROM {bank_transactions} WHERE 1=1';
		$p   = array();
		$st  = self::q( $r, 'status' );
		if ( 'a_verifier' === $st ) {
			$sql .= " AND status IN ('a_traiter','propose')";
		} elseif ( $st ) {
			$sql .= ' AND status=?';
			$p[]  = $st;
		}
		if ( $m = self::q( $r, 'month' ) ) {
			$sql .= " AND DATE_FORMAT(date,'%Y-%m')=?";
			$p[]  = $m;
		}
		if ( $s = self::q( $r, 'q' ) ) {
			$sql .= ' AND (label LIKE ? OR counterparty LIKE ? OR reference LIKE ? OR ABS(amount_cents)=?)';
			$like = '%' . $s . '%';
			$amt  = preg_match( '/^[\d\s.,-]+$/', $s ) ? abs( AC_Money::to_cents( $s ) ) : -1;
			array_push( $p, $like, $like, $like, $amt );
		}
		$rows   = AC_DB::q( $sql . ' ORDER BY date DESC, id DESC LIMIT 1000', $p );
		$allocs = array();
		foreach ( AC_DB::q( "SELECT a.*, x.document_id exp_doc, x.label exp_label, x.status exp_status, c.name category FROM {allocations} a
			LEFT JOIN {expenses} x ON a.target_type='depense' AND x.id=a.target_id LEFT JOIN {categories} c ON c.id=x.category_id" ) as $a ) {
			$allocs[ $a['bank_transaction_id'] ][] = $a;
		}
		$docs = array();
		foreach ( AC_DB::q( "SELECT id, bank_transaction_id FROM {documents} WHERE bank_transaction_id IS NOT NULL AND kind<>'alias' ORDER BY id" ) as $d ) {
			$docs[ $d['bank_transaction_id'] ] = $docs[ $d['bank_transaction_id'] ] ?? $d['id'];
		}
		foreach ( $rows as &$t ) {
			$t['suggestion']  = $t['suggestion'] ? json_decode( $t['suggestion'], true ) : null;
			$t['allocations'] = $allocs[ $t['id'] ] ?? array();
			$t['document_id'] = $docs[ $t['id'] ] ?? null;
			foreach ( $t['allocations'] as $a ) {
				$t['document_id'] = $t['document_id'] ?: ( $a['exp_doc'] ?: null );
			}
			// Justificatif attendu ? (pas pour un apport, un virement interne, le capital, la TVA ou l'IS)
			$types               = array_column( $t['allocations'], 'target_type' );
			$t['needs_document'] = $t['amount_cents'] < 0 && ! array_intersect( $types, array( 'associe', 'capital', 'tva', 'is', 'virement_interne' ) );
			$t['attachment_count'] = count( json_decode( (string) ( $t['attachment_ids'] ?: '[]' ), true ) ?: array() );
			unset( $t['attachment_ids'] );
		}
		return $rows;
	}

	public static function r_transaction( $r, $id ) {
		$t = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( (int) $id ) );
		if ( ! $t ) {
			throw new AC_Error( 'Opération introuvable' );
		}
		$t['suggestion'] = $t['suggestion'] ? json_decode( $t['suggestion'], true ) : null;
		$t['raw']        = json_decode( (string) $t['raw'], true );
		$allocs          = AC_DB::q( 'SELECT * FROM {allocations} WHERE bank_transaction_id=?', array( (int) $id ) );
		$entries         = array();
		foreach ( AC_Ledger::source_entries( 'banque', (int) $id ) as $e ) {
			$entries[] = AC_Ledger::entry_with_lines( $e['id'] );
		}
		foreach ( $allocs as &$a ) {
			if ( in_array( $a['target_type'], array( 'depense', 'vente' ), true ) ) {
				$a['target'] = AC_DB::q1( 'SELECT * FROM {' . ( 'depense' === $a['target_type'] ? 'expenses' : 'sales_invoices' ) . '} WHERE id=?', array( $a['target_id'] ) );
				foreach ( AC_Ledger::source_entries( $a['target_type'], $a['target_id'] ) as $e ) {
					$entries[] = AC_Ledger::entry_with_lines( $e['id'] );
				}
			}
		}
		$t['allocations'] = $allocs;
		$t['entries']     = $entries;
		$docs             = array_unique( array_filter( array_column( $entries, 'document_id' ) ) );
		$t['documents']   = $docs ? AC_DB::q( 'SELECT * FROM {documents} WHERE id IN (' . implode( ',', array_map( 'intval', $docs ) ) . ')' ) : array();
		return $t;
	}

	public static function r_classify( $r, $id ) {
		return AC_Services::classify_transaction( (int) $id, self::body( $r ) );
	}

	public static function r_accept( $r, $id ) {
		return AC_Services::apply_suggestion( (int) $id );
	}

	public static function r_accept_all( $r ) {
		$n = 0;
		foreach ( AC_DB::q( "SELECT id, suggestion FROM {bank_transactions} WHERE status='propose'" ) as $t ) {
			$s = json_decode( (string) $t['suggestion'], true );
			if ( $s && $s['confidence'] >= 0.9 && in_array( $s['type'], array( 'depense', 'vente', 'depense_nouvelle' ), true ) && ( 'depense_nouvelle' !== $s['type'] || ! empty( $s['category_id'] ) ) ) {
				AC_Services::apply_suggestion( $t['id'] );
				$n++;
			}
		}
		return array( 'accepted' => $n );
	}

	public static function r_add_transaction( $r ) {
		$d = self::body( $r );
		return AC_Services::import_transactions( array( array( 'external_id' => 'manuel:' . wp_generate_uuid4(), 'date' => $d['date'],
			'amount_cents' => AC_Services::cents( $d['amount_cents'] ), 'label' => $d['label'] ?? '', 'counterparty' => $d['label'] ?? '' ) ), 'manuel' );
	}

	public static function r_tx_groups( $r ) {
		return AC_Services::transaction_groups();
	}

	public static function r_tx_bulk( $r ) {
		$d = self::body( $r );
		$p = (array) ( $d['payload'] ?? array() );
		if ( in_array( $p['type'] ?? '', array( 'depense_nouvelle', 'remboursement_fournisseur' ), true ) ) {
			$p['compute_vat'] = true;
		}
		return AC_Services::bulk_classify( (array) ( $d['ids'] ?? array() ), $p, ! empty( $d['remember'] ) );
	}

	public static function r_tx_document( $r, $id ) {
		list( $name, $content ) = self::uploaded( $r );
		$tx  = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( (int) $id ) );
		$doc = AC_DB::tx( function () use ( $name, $content, $tx ) { return AC_Services::store_document( $name, $content, 'justificatif', $tx['date'], $tx['counterparty'] ); } );
		AC_Services::attach_to_transaction( (int) $id, $doc );
		return array( 'document_id' => $doc );
	}

	public static function r_expense_document( $r, $id ) {
		list( $name, $content ) = self::uploaded( $r );
		$x   = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( (int) $id ) );
		$doc = AC_DB::tx( function () use ( $name, $content, $x ) { return AC_Services::store_document( $name, $content, 'justificatif', $x['date'], $x['supplier_name'] ); } );
		return AC_Services::save_expense( array( 'document_id' => $doc ), (int) $id, 'Justificatif ajouté' );
	}

	public static function r_qonto_attachments( $r ) {
		return AC_Qonto::fetch_attachments( 20 );
	}

	public static function r_reprise( $r ) {
		$b = self::body( $r );
		@set_time_limit( 60 );
		return AC_Reprise::run( (array) ( $b['data'] ?? array() ), (int) ( $b['offset'] ?? 0 ), ! empty( $b['dry'] ), ! isset( $b['validate'] ) || ! empty( $b['validate'] ) );
	}

	public static function r_mileage( $r ) {
		return AC_Mileage::summary( (int) self::q( $r, 'year', substr( AC_DB::today(), 0, 4 ) ) );
	}

	public static function r_mileage_add( $r ) {
		return AC_Mileage::add( self::body( $r ) );
	}

	public static function r_mileage_delete( $r, $id ) {
		AC_Mileage::delete( (int) $id );
		return array( 'ok' => true );
	}

	public static function r_mileage_pdf( $r ) {
		$y = (int) self::q( $r, 'year', substr( AC_DB::today(), 0, 4 ) );
		self::send( AC_Mileage::pdf( $y ), 'application/pdf', "frais-kilometriques-$y.pdf", true );
	}

	public static function r_qonto_sync( $r ) {
		return AC_Qonto::sync( (bool) self::q( $r, 'full', false ) );
	}

	public static function r_qonto_auto( $r ) {
		return array( 'result' => AC_Qonto::maybe_sync() );
	}

	public static function r_qonto_test( $r ) {
		$org = AC_Qonto::organization();
		return array( 'name' => $org['name'] ?? ( $org['legal_name'] ?? '' ), 'accounts' => array_map( function ( $a ) {
			return array( 'id' => $a['id'], 'name' => $a['name'] ?? '', 'iban' => $a['iban'] ?? '', 'balance_cents' => $a['balance_cents'] ?? null, 'main' => ! empty( $a['main'] ) );
		}, $org['bank_accounts'] ?? array() ) );
	}

	public static function r_import_csv( $r ) {
		list( , $content ) = self::uploaded( $r );
		return AC_Services::import_transactions( AC_Qonto::parse_csv( $content ), 'csv' );
	}

	// ------------------------------------------------------------ pièces
	public static function r_upload( $r ) {
		list( $name, $content ) = self::uploaded( $r );
		$kind = self::q( $r, 'kind', 'justificatif' );
		$doc  = AC_DB::tx( function () use ( $name, $content, $kind ) { return AC_Services::store_document( $name, $content, $kind ); } );
		$res  = array( 'document_id' => $doc );
		if ( 'false' !== (string) self::q( $r, 'process', 'true' ) && 'justificatif' === $kind ) {
			try {
				$res = array_merge( $res, AC_DB::tx( function () use ( $doc ) { return AC_Ai::process_document( $doc ); } ) );
			} catch ( AC_Error $e ) {
				$res += array( 'status' => 'erreur', 'message' => $e->getMessage() );
			}
		}
		return $res;
	}

	public static function r_documents( $r ) {
		$sql = 'SELECT d.*, (SELECT MIN(id) FROM {expenses} WHERE document_id=d.id) expense_id, (SELECT MIN(id) FROM {sales_invoices} WHERE document_id=d.id) invoice_id FROM {documents} d WHERE 1=1';
		$p   = array();
		if ( $s = self::q( $r, 'q' ) ) {
			$sql .= ' AND (d.original_name LIKE ? OR d.party_name LIKE ? OR d.doc_date LIKE ? OR d.amount_cents=?)';
			array_push( $p, "%$s%", "%$s%", "%$s%", preg_match( '/^[\d\s.,-]+$/', $s ) ? AC_Money::to_cents( $s ) : -1 );
		}
		$rows = AC_DB::q( str_replace( 'WHERE 1=1', "WHERE d.kind<>'alias'", $sql ) . ' ORDER BY d.id DESC LIMIT 500', $p );
		if ( self::q( $r, 'unlinked' ) ) {
			$rows = array_values( array_filter( $rows, function ( $d ) { return ! $d['expense_id'] && ! $d['invoice_id'] && ! $d['bank_transaction_id'] && 'justificatif' === $d['kind']; } ) );
		}
		foreach ( $rows as &$d ) {
			$d['ai_data'] = $d['ai_data'] ? json_decode( $d['ai_data'], true ) : null;
		}
		return $rows;
	}

	public static function r_document_file( $r, $id ) {
		$d = AC_DB::q1( 'SELECT * FROM {documents} WHERE id=?', array( (int) $id ) );
		if ( ! $d || ! file_exists( AC_Services::document_path( $d ) ) ) {
			throw new AC_Error( 'Pièce introuvable' );
		}
		self::send( file_get_contents( AC_Services::document_path( $d ) ), $d['mime'] ?: 'application/octet-stream', $d['original_name'] ?: basename( $d['path'] ), true );
	}

	public static function r_process_doc( $r, $id ) {
		return AC_DB::tx( function () use ( $id ) { return AC_Ai::process_document( (int) $id ); } );
	}

	public static function r_confirm_doc( $r, $id ) {
		$p = self::body( $r );
		switch ( $p['action'] ?? '' ) {
			case 'transaction':
				return AC_Services::classify_transaction( (int) $p['transaction_id'], array_merge( (array) ( $p['expense'] ?? array() ), array( 'type' => 'depense_nouvelle', 'document_id' => (int) $id ) ) );
			case 'expense':
				return AC_Services::save_expense( array( 'document_id' => (int) $id ), (int) $p['expense_id'], 'Justificatif rattaché' );
			case 'new_expense':
				return AC_Services::save_expense( array_merge( (array) $p['expense'], array( 'document_id' => (int) $id ) ), null, 'Dépense créée depuis un justificatif' );
		}
		throw new AC_Error( 'Action inconnue' );
	}

	public static function r_delete_doc( $r, $id ) {
		$d    = AC_DB::q1( 'SELECT * FROM {documents} WHERE id=?', array( (int) $id ) );
		$used = AC_DB::val( 'SELECT id FROM {expenses} WHERE document_id=? UNION SELECT id FROM {sales_invoices} WHERE document_id=? UNION SELECT id FROM {entries} WHERE document_id=?', array( (int) $id, (int) $id, (int) $id ) );
		if ( $d['locked'] || $used ) {
			throw new AC_Error( 'Pièce rattachée à la comptabilité : suppression interdite.' );
		}
		AC_DB::exec( 'DELETE FROM {documents} WHERE id=?', array( (int) $id ) );
		AC_DB::audit( 'suppression', 'piece', (int) $id, $d, null );
		@unlink( AC_Services::document_path( $d ) );
		return array( 'ok' => true );
	}

	// ------------------------------------------------------------ recettes
	public static function r_invoices( $r ) {
		$rows = AC_DB::q( 'SELECT s.*, p.name client FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id ORDER BY s.issue_date DESC, s.number DESC' );
		foreach ( $rows as &$i ) {
			$i += AC_Services::invoice_status( $i );
		}
		return $rows;
	}

	public static function r_next_number( $r ) {
		return array( 'number' => AC_Services::next_invoice_number( self::q( $r, 'kind', 'facture' ), self::q( $r, 'issue_date', AC_DB::today() ) ) );
	}

	public static function r_invoice( $r, $id ) {
		$i = AC_DB::q1( 'SELECT s.*, p.name client, p.vat_number client_vat, p.country client_country FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id WHERE s.id=?', array( (int) $id ) );
		if ( ! $i ) {
			throw new AC_Error( 'Facture introuvable' );
		}
		$i += AC_Services::invoice_status( $i );
		$i['lines']        = AC_DB::q( 'SELECT * FROM {sales_invoice_lines} WHERE invoice_id=?', array( (int) $id ) );
		$i['payments']     = AC_DB::q( "SELECT a.*, t.date, t.label FROM {allocations} a JOIN {bank_transactions} t ON t.id=a.bank_transaction_id WHERE a.target_type='vente' AND a.target_id=?", array( (int) $id ) );
		$i['credit_notes'] = AC_DB::q( 'SELECT id, number, ttc_cents, issue_date FROM {sales_invoices} WHERE credited_invoice_id=?', array( (int) $id ) );
		$i['entries']      = array_map( function ( $e ) { return AC_Ledger::entry_with_lines( $e['id'] ); }, AC_Ledger::source_entries( 'vente', (int) $id ) );
		return $i;
	}

	public static function r_create_invoice( $r ) {
		return AC_Services::save_sales_invoice( self::body( $r ) );
	}

	public static function r_update_invoice( $r, $id ) {
		$d = self::body( $r );
		return AC_Services::save_sales_invoice( $d, (int) $id, ( $d['reason'] ?? '' ) ?: 'Modification de la facture' );
	}

	public static function r_delete_invoice( $r, $id ) {
		AC_Services::delete_sales_invoice( (int) $id );
		return array( 'ok' => true );
	}

	// ------------------------------------------------------------ dépenses
	public static function r_expenses( $r ) {
		$sql = 'SELECT x.*, c.name category, c.rule FROM {expenses} x LEFT JOIN {categories} c ON c.id=x.category_id WHERE 1=1';
		$p   = array();
		if ( $m = self::q( $r, 'month' ) ) {
			$sql .= " AND DATE_FORMAT(x.date,'%Y-%m')=?";
			$p[]  = $m;
		}
		if ( $s = self::q( $r, 'status' ) ) {
			$sql .= ' AND x.status=?';
			$p[]  = $s;
		}
		if ( $pm = self::q( $r, 'payment' ) ) {
			$sql .= ' AND x.payment_method=?';
			$p[]  = $pm;
		}
		$rows = AC_DB::q( $sql . ' ORDER BY x.date DESC, x.id DESC LIMIT 2000', $p );
		foreach ( $rows as &$x ) {
			$x['paid_cents'] = AC_Services::expense_paid( $x );
		}
		return $rows;
	}

	public static function r_expense( $r, $id ) {
		$x = AC_DB::q1( 'SELECT x.*, c.name category, c.rule, c.help category_help, c.is_asset FROM {expenses} x LEFT JOIN {categories} c ON c.id=x.category_id WHERE x.id=?', array( (int) $id ) );
		if ( ! $x ) {
			throw new AC_Error( 'Dépense introuvable' );
		}
		$cat                 = AC_Posting::category( $x['category_id'] );
		$x['warnings']       = AC_Services::fiscal_warnings( $x, $cat );
		$x['rule_explanation'] = $cat ? ( AC_Rules::explanations()[ $cat['rule'] ] ?? null ) : null;
		$x['paid_cents']     = AC_Services::expense_paid( $x );
		$x['payments']       = AC_DB::q( "SELECT a.*, t.date, t.label FROM {allocations} a JOIN {bank_transactions} t ON t.id=a.bank_transaction_id WHERE a.target_type='depense' AND a.target_id=?", array( (int) $id ) );
		$entries             = array();
		foreach ( AC_Ledger::source_entries( 'depense', (int) $id ) as $e ) {
			$entries[] = AC_Ledger::entry_with_lines( $e['id'] );
		}
		foreach ( $x['payments'] as $pm ) {
			foreach ( AC_Ledger::source_entries( 'banque', $pm['bank_transaction_id'] ) as $e ) {
				$entries[] = AC_Ledger::entry_with_lines( $e['id'] );
			}
		}
		$x['entries']  = $entries;
		$x['document'] = $x['document_id'] ? AC_DB::q1( 'SELECT * FROM {documents} WHERE id=?', array( $x['document_id'] ) ) : null;
		$x['history']  = AC_DB::q( "SELECT * FROM {audit_log} WHERE object_type='depense' AND object_id=? ORDER BY id DESC", array( (int) $id ) );
		return $x;
	}

	public static function r_create_expense( $r ) {
		return AC_Services::save_expense( self::body( $r ) );
	}

	public static function r_update_expense( $r, $id ) {
		$d      = self::body( $r );
		$reason = ( $d['reason'] ?? '' ) ?: 'Modification de la dépense';
		unset( $d['reason'] );
		return AC_Services::save_expense( $d, (int) $id, $reason );
	}

	public static function r_validate_expense( $r, $id ) {
		$res = AC_Services::save_expense( array( 'status' => 'valide' ), (int) $id, 'Validation de la dépense' );
		AC_DB::tx( function () use ( $id ) {
			foreach ( AC_Ledger::source_entries( 'depense', (int) $id ) as $e ) {
				AC_Ledger::validate_entry( $e['id'] );
			}
		} );
		return $res;
	}

	public static function r_delete_expense( $r, $id ) {
		AC_Services::delete_expense( (int) $id );
		return array( 'ok' => true );
	}

	// ------------------------------------------------------------ associé
	public static function r_associate( $r ) {
		$s                   = AC_Services::associate_summary();
		$s['movements_list'] = AC_DB::q( 'SELECT * FROM {associate_movements} ORDER BY date DESC, id DESC' );
		$s['perso_expenses'] = AC_DB::q( "SELECT x.*, c.name category FROM {expenses} x LEFT JOIN {categories} c ON c.id=x.category_id WHERE x.payment_method='perso' ORDER BY date DESC" );
		$s['personal_paid']  = AC_DB::q( "SELECT x.*, c.name category FROM {expenses} x JOIN {categories} c ON c.id=x.category_id WHERE c.rule='personnel' ORDER BY date DESC" );
		return $s;
	}

	public static function r_add_movement( $r ) {
		return AC_Services::add_associate_movement( self::body( $r ) );
	}

	public static function r_link_movement( $r, $id ) {
		AC_Services::link_movement( (int) $id, (int) self::body( $r )['transaction_id'] );
		return array( 'ok' => true );
	}

	public static function r_delete_movement( $r, $id ) {
		AC_Services::delete_associate_movement( (int) $id );
		return array( 'ok' => true );
	}

	// ------------------------------------------------------------ immobilisations
	public static function r_assets( $r ) {
		$fy   = AC_Closing::fiscal_year();
		$rows = AC_DB::q( 'SELECT * FROM {fixed_assets} ORDER BY acquisition_date' );
		foreach ( $rows as &$a ) {
			$a += AC_Services::depreciation_for_year( $a, $fy['start_date'], $fy['end_date'] );
		}
		return array( 'exercice' => $fy, 'assets' => $rows, 'types' => AC_Rules::asset_types() );
	}

	public static function r_add_asset( $r ) {
		$d = self::body( $r );
		return AC_Services::save_fixed_asset( $d, ! empty( $d['id'] ) ? (int) $d['id'] : null );
	}

	public static function r_dispose( $r, $id ) {
		$d = self::body( $r );
		return AC_Services::dispose_asset( (int) $id, $d['date'], AC_Services::cents( $d['price_cents'] ?? 0 ) );
	}

	// ------------------------------------------------------------ TVA
	public static function r_vat_periods( $r ) {
		return AC_Vat::periods( (int) self::q( $r, 'year', substr( AC_DB::today(), 0, 4 ) ) );
	}

	public static function r_vat_compute( $r ) {
		return AC_Vat::compute( self::q( $r, 'start' ), self::q( $r, 'end' ) );
	}

	public static function r_vat_prepare( $r ) {
		$d = self::body( $r );
		return AC_Vat::prepare( $d['start'], $d['end'] );
	}

	public static function r_vat_validate( $r, $id ) {
		return AC_Vat::validate( (int) $id, (bool) self::q( $r, 'force', false ) );
	}

	public static function r_vat_filed( $r, $id ) {
		AC_Vat::mark_filed( (int) $id );
		return array( 'ok' => true );
	}

	public static function r_vat_adjust( $r, $id ) {
		$d = self::body( $r );
		AC_Vat::set_adjustment( (int) $id, (string) $d['line'], AC_Services::cents( $d['amount_cents'] ) );
		return array( 'ok' => true );
	}

	public static function r_vat_export( $r ) {
		$s = self::q( $r, 'start' );
		$e = self::q( $r, 'end' );
		self::send( AC_Exports::vat_dossier( $s, $e ), 'application/zip', "tva-$s-$e.zip" );
	}

	// ------------------------------------------------------------ comptabilité
	public static function r_entries( $r ) {
		$rows = AC_Reports::journal( self::q( $r, 'start' ), self::q( $r, 'end' ), self::q( $r, 'journal' ) );
		if ( $st = self::q( $r, 'status' ) ) {
			$rows = array_values( array_filter( $rows, function ( $e ) use ( $st ) { return $e['status'] === $st; } ) );
		}
		return array_slice( $rows, -1500 );
	}

	public static function r_add_entry( $r ) {
		return array( 'id' => AC_Closing::manual_entry( self::body( $r ) ) );
	}

	public static function r_validate_entries( $r ) {
		$d = self::body( $r );
		return AC_DB::tx( function () use ( $d ) {
			$n = 0;
			foreach ( AC_DB::q( "SELECT id FROM {entries} WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date, id", array( $d['start'], $d['end'] ) ) as $e ) {
				AC_Ledger::validate_entry( $e['id'] );
				$n++;
			}
			return array( 'validated' => $n );
		} );
	}

	public static function r_reverse( $r, $id ) {
		$d = self::body( $r );
		$e = AC_DB::q1( 'SELECT * FROM {entries} WHERE id=?', array( (int) $id ) );
		if ( 'manuel' !== $e['source_type'] ) {
			throw new AC_Error( 'Écriture générée automatiquement : corrigez plutôt sa source (dépense, facture, opération).' );
		}
		return array( 'id' => AC_DB::tx( function () use ( $id, $d ) { return AC_Ledger::reverse_entry( (int) $id, $d['date'] ?? null, ( $d['reason'] ?? '' ) ?: 'Correction' ); } ) );
	}

	public static function r_report( $r, $name ) {
		$fy    = AC_Closing::fiscal_year( self::q( $r, 'fy_id' ) );
		$start = self::q( $r, 'start', $fy['start_date'] );
		$end   = self::q( $r, 'end', $fy['end_date'] );
		switch ( $name ) {
			case 'balance':
				return AC_Reports::trial_balance( $start, $end );
			case 'grand-livre':
				return AC_Reports::general_ledger( $start, $end, self::q( $r, 'account' ) );
			case 'resultat':
				return AC_Reports::income_statement( $start, $end );
			case 'bilan':
				return AC_Reports::balance_sheet( $fy );
			case 'integrite':
				return AC_Ledger::check_integrity();
			case 'rapprochement':
				return AC_Checks::bank_reconciliation( $end );
		}
		throw new AC_Error( 'Rapport inconnu' );
	}

	public static function r_audit( $r ) {
		return AC_DB::q( 'SELECT * FROM {audit_log} ORDER BY id DESC LIMIT ?', array( (int) self::q( $r, 'limit', 300 ) ) );
	}

	public static function r_tax_estimate( $r ) {
		return AC_Tax::estimate( AC_Closing::fiscal_year( self::q( $r, 'fy_id' ) ) );
	}

	public static function r_month_check( $r, $m ) {
		return AC_Checks::verify_month( $m );
	}

	public static function r_month_close( $r, $m ) {
		return AC_Checks::close_month( $m, (bool) self::q( $r, 'force', false ) );
	}

	public static function r_month_export( $r, $m ) {
		self::send( AC_Exports::month_dossier( $m ), 'application/zip', "dossier-$m.zip" );
	}

	// ------------------------------------------------------------ clôture
	public static function r_closing_prepare( $r ) {
		return AC_Closing::prepare( self::q( $r, 'fy_id' ) );
	}

	public static function r_closing_close( $r, $id ) {
		return AC_Closing::close( (int) $id, (bool) self::q( $r, 'force', false ) );
	}

	public static function r_closing_allocate( $r, $id ) {
		$d = self::body( $r );
		return array( 'entry_id' => AC_Closing::allocate_result( (int) $id, AC_Services::cents( $d['legal_reserve_cents'] ?? 0 ), AC_Services::cents( $d['dividends_cents'] ?? 0 ), $d['date'] ?? null ) );
	}

	public static function r_closing_export( $r, $id ) {
		$fy = AC_Closing::fiscal_year( $id );
		self::send( AC_Exports::annual_dossier( $fy ), 'application/zip', 'dossier-annuel-' . substr( $fy['end_date'], 0, 4 ) . '.zip' );
	}

	public static function r_closing_fec( $r, $id ) {
		list( $name, $content ) = AC_Exports::fec( AC_Closing::fiscal_year( $id ) );
		self::send( $content, 'text/plain; charset=utf-8', $name );
	}

	public static function r_closing_statement( $r, $id, $name ) {
		$files = AC_Exports::statements( AC_Closing::fiscal_year( $id ) );
		$name .= '.pdf';
		if ( ! isset( $files[ $name ] ) ) {
			throw new AC_Error( 'Document inconnu' );
		}
		self::send( $files[ $name ], 'application/pdf', $name, true );
	}

	// ------------------------------------------------------------ sauvegardes, assistant, recherche
	public static function r_backups( $r ) {
		return AC_Backup::list();
	}

	public static function r_backup_now( $r ) {
		return array( 'file' => AC_Backup::backup( 'manuelle' ) );
	}

	public static function r_full_export( $r ) {
		self::send( AC_Backup::full_export(), 'application/zip', 'aurea-compta-export-' . AC_DB::today() . '.zip' );
	}

	public static function r_restore( $r ) {
		list( , $content ) = self::uploaded( $r );
		return AC_Backup::restore( $content );
	}

	public static function r_assistant( $r ) {
		return AC_Ai::ask( (string) ( self::body( $r )['question'] ?? '' ) );
	}

	public static function r_search( $r ) {
		$s    = (string) self::q( $r, 'q', '' );
		$like = "%$s%";
		$amt  = preg_match( '/\d/', $s ) && preg_match( '/^[\d\s.,-]+$/', $s ) ? abs( AC_Money::to_cents( $s ) ) : -1;
		return array(
			'transactions' => AC_DB::q( 'SELECT id, date, counterparty, label, amount_cents, status FROM {bank_transactions} WHERE label LIKE ? OR counterparty LIKE ? OR date LIKE ? OR ABS(amount_cents)=? LIMIT 20', array( $like, $like, $like, $amt ) ),
			'depenses' => AC_DB::q( 'SELECT id, date, supplier_name, label, ttc_cents, status FROM {expenses} WHERE supplier_name LIKE ? OR label LIKE ? OR date LIKE ? OR ttc_cents=? LIMIT 20', array( $like, $like, $like, $amt ) ),
			'factures' => AC_DB::q( 'SELECT s.id, s.number, s.issue_date, p.name client, s.ttc_cents FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id WHERE s.number LIKE ? OR p.name LIKE ? OR s.issue_date LIKE ? OR s.ttc_cents=? LIMIT 20', array( $like, $like, $like, $amt ) ),
			'pieces' => AC_DB::q( "SELECT id, original_name, party_name, doc_date, amount_cents FROM {documents} WHERE kind<>'alias' AND (original_name LIKE ? OR party_name LIKE ? OR doc_date LIKE ? OR amount_cents=?) LIMIT 20", array( $like, $like, $like, $amt ) ),
		);
	}
}

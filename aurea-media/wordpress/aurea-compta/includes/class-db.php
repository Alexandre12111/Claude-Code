<?php
/**
 * Base de données (tables MySQL de WordPress), paramètres, secrets chiffrés, historique.
 */
defined( 'ABSPATH' ) || exit;

class AC_Error extends Exception {}

class AC_DB {

	const SCHEMA_VERSION = 1;

	/** Colonnes converties en entiers à la lecture ($wpdb renvoie des chaînes). */
	const INT_PATTERN = '/(_cents|^id$|_id$|^vat_recovery$|^deductible$|^locked$|^is_asset$|^active$|^duration_years$|^size$|^custom$|_count$|^n$|^debit$|^credit$|^solde$|^total$|^seq$|^vseq$)/';

	private static $depth = 0;

	public static function prefix(): string {
		global $wpdb;
		return $wpdb->prefix . ( defined( 'AC_TABLE_PREFIX' ) ? AC_TABLE_PREFIX : 'ac_' );
	}

	public static function t( string $name ): string {
		return self::prefix() . $name;
	}

	/** Remplace les « ? » par les valeurs échappées. */
	public static function prep( string $sql, array $params = array() ): string {
		global $wpdb;
		if ( ! $params ) {
			return $sql;
		}
		$i   = 0;
		$out = preg_replace_callback( '/\?/', function () use ( &$i, $params, $wpdb ) {
			$v = $params[ $i++ ] ?? null;
			if ( null === $v ) {
				return 'NULL';
			}
			if ( is_bool( $v ) ) {
				return $v ? '1' : '0';
			}
			if ( is_int( $v ) ) {
				return (string) $v;
			}
			return "'" . esc_sql( (string) $v ) . "'";
		}, $sql );
		return $out;
	}

	/** {table} → nom complet. */
	private static function tables( string $sql ): string {
		return preg_replace_callback( '/\{(\w+)\}/', function ( $m ) {
			return self::t( $m[1] );
		}, $sql );
	}

	public static function cast( array $row ): array {
		foreach ( $row as $k => $v ) {
			if ( null !== $v && is_string( $v ) && preg_match( self::INT_PATTERN, $k ) && preg_match( '/^-?\d+$/', $v ) ) {
				$row[ $k ] = (int) $v;
			}
		}
		return $row;
	}

	public static function q( string $sql, array $params = array() ): array {
		global $wpdb;
		$rows = $wpdb->get_results( self::prep( self::tables( $sql ), $params ), ARRAY_A );
		self::check_error();
		return array_map( array( __CLASS__, 'cast' ), $rows ?: array() );
	}

	public static function q1( string $sql, array $params = array() ): ?array {
		$r = self::q( $sql, $params );
		return $r ? $r[0] : null;
	}

	public static function val( string $sql, array $params = array() ) {
		global $wpdb;
		$v = $wpdb->get_var( self::prep( self::tables( $sql ), $params ) );
		self::check_error();
		return $v;
	}

	public static function ival( string $sql, array $params = array() ): int {
		return (int) self::val( $sql, $params );
	}

	public static function exec( string $sql, array $params = array() ): int {
		global $wpdb;
		$r = $wpdb->query( self::prep( self::tables( $sql ), $params ) );
		self::check_error();
		return (int) $r;
	}

	public static function insert( string $table, array $data ): int {
		global $wpdb;
		$cols = array_keys( $data );
		$sql  = 'INSERT INTO ' . self::t( $table ) . ' (`' . implode( '`,`', $cols ) . '`) VALUES (' . implode( ',', array_fill( 0, count( $cols ), '?' ) ) . ')';
		$wpdb->query( self::prep( $sql, array_values( $data ) ) );
		self::check_error();
		return (int) $wpdb->insert_id;
	}

	public static function update( string $table, array $data, int $id ): void {
		if ( ! $data ) {
			return;
		}
		$set = implode( ',', array_map( function ( $c ) {
			return "`$c`=?";
		}, array_keys( $data ) ) );
		self::exec( 'UPDATE ' . self::t( $table ) . " SET $set WHERE id=?", array_merge( array_values( $data ), array( $id ) ) );
	}

	private static function check_error(): void {
		global $wpdb;
		if ( $wpdb->last_error ) {
			$e = $wpdb->last_error;
			$wpdb->last_error = '';
			throw new AC_Error( 'Erreur de base de données : ' . $e );
		}
	}

	/** Transaction (réentrante) : tout ou rien. */
	public static function tx( callable $fn ) {
		global $wpdb;
		if ( 0 === self::$depth ) {
			$wpdb->query( 'START TRANSACTION' );
		}
		self::$depth++;
		try {
			$r = $fn();
			self::$depth--;
			if ( 0 === self::$depth ) {
				$wpdb->query( 'COMMIT' );
			}
			return $r;
		} catch ( Throwable $e ) {
			self::$depth--;
			if ( 0 === self::$depth ) {
				$wpdb->query( 'ROLLBACK' );
			}
			throw $e;
		}
	}

	public static function now(): string {
		return current_time( 'Y-m-d H:i:s' );
	}

	public static function today(): string {
		return current_time( 'Y-m-d' );
	}

	// ------------------------------------------------------------ schéma
	public static function install(): void {
		global $wpdb;
		$c   = $wpdb->get_charset_collate();
		$p   = self::prefix();
		$sql = array(
			"CREATE TABLE IF NOT EXISTS {$p}fiscal_years (id BIGINT AUTO_INCREMENT PRIMARY KEY, start_date DATE NOT NULL, end_date DATE NOT NULL,
				status VARCHAR(10) NOT NULL DEFAULT 'open', closed_at DATETIME NULL, summary LONGTEXT NULL) $c",
			"CREATE TABLE IF NOT EXISTS {$p}accounts (number VARCHAR(12) PRIMARY KEY, label VARCHAR(191) NOT NULL, custom TINYINT NOT NULL DEFAULT 0) $c",
			"CREATE TABLE IF NOT EXISTS {$p}categories (id BIGINT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(60) UNIQUE, name VARCHAR(191) NOT NULL,
				account VARCHAR(12) NOT NULL, vat_recovery INT NOT NULL DEFAULT 100, deductible INT NOT NULL DEFAULT 100,
				rule VARCHAR(30) NOT NULL DEFAULT 'standard', is_asset TINYINT NOT NULL DEFAULT 0, help TEXT NULL, active TINYINT NOT NULL DEFAULT 1) $c",
			"CREATE TABLE IF NOT EXISTS {$p}parties (id BIGINT AUTO_INCREMENT PRIMARY KEY, kind VARCHAR(10) NOT NULL, name VARCHAR(191) NOT NULL,
				siren VARCHAR(20) NULL, vat_number VARCHAR(30) NULL, country VARCHAR(4) DEFAULT 'FR', email VARCHAR(191) NULL, address TEXT NULL,
				created_at DATETIME NOT NULL, KEY kind_name (kind, name)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}documents (id BIGINT AUTO_INCREMENT PRIMARY KEY, path VARCHAR(255) NOT NULL, original_name VARCHAR(255) NULL,
				sha256 CHAR(64) NOT NULL UNIQUE, mime VARCHAR(100) NULL, size BIGINT NULL, kind VARCHAR(40) NOT NULL DEFAULT 'justificatif',
				doc_date DATE NULL, party_name VARCHAR(191) NULL, amount_cents BIGINT NULL, source VARCHAR(20) NOT NULL DEFAULT 'upload',
				external_id VARCHAR(191) NULL, ai_status VARCHAR(20) NULL, ai_data LONGTEXT NULL, locked TINYINT NOT NULL DEFAULT 0, uploaded_at DATETIME NOT NULL,
				KEY external_id (external_id)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}bank_transactions (id BIGINT AUTO_INCREMENT PRIMARY KEY, source VARCHAR(20) NOT NULL, external_id VARCHAR(191) NOT NULL UNIQUE,
				bank_account VARCHAR(100) NULL, date DATE NOT NULL, emitted_at VARCHAR(40) NULL, amount_cents BIGINT NOT NULL, label VARCHAR(255) NULL,
				counterparty VARCHAR(255) NULL, operation_type VARCHAR(40) NULL, reference VARCHAR(255) NULL, note TEXT NULL, qonto_vat_cents BIGINT NULL,
				qonto_vat_rate VARCHAR(10) NULL, attachment_ids TEXT NULL, kind VARCHAR(30) NULL, status VARCHAR(12) NOT NULL DEFAULT 'a_traiter',
				suggestion TEXT NULL, comment TEXT NULL, raw LONGTEXT NULL, imported_at DATETIME NOT NULL, KEY date (date)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}sales_invoices (id BIGINT AUTO_INCREMENT PRIMARY KEY, number VARCHAR(60) NOT NULL UNIQUE, kind VARCHAR(10) NOT NULL DEFAULT 'facture',
				credited_invoice_id BIGINT NULL, client_id BIGINT NOT NULL, issue_date DATE NOT NULL, due_date DATE NULL, nature VARCHAR(10) NOT NULL DEFAULT 'services',
				vat_regime VARCHAR(30) NOT NULL DEFAULT 'fr', ht_cents BIGINT NOT NULL, vat_cents BIGINT NOT NULL, ttc_cents BIGINT NOT NULL,
				status VARCHAR(12) NOT NULL DEFAULT 'emise', document_id BIGINT NULL, notes TEXT NULL, external_id VARCHAR(191) NULL, created_at DATETIME NOT NULL) $c",
			"CREATE TABLE IF NOT EXISTS {$p}sales_invoice_lines (id BIGINT AUTO_INCREMENT PRIMARY KEY, invoice_id BIGINT NOT NULL, description VARCHAR(255) NOT NULL,
				quantity VARCHAR(20) NOT NULL DEFAULT '1', unit_price_cents BIGINT NOT NULL, vat_rate VARCHAR(10) NOT NULL DEFAULT '20',
				account VARCHAR(12) NOT NULL DEFAULT '706000', ht_cents BIGINT NOT NULL, vat_cents BIGINT NOT NULL, KEY invoice_id (invoice_id)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}expenses (id BIGINT AUTO_INCREMENT PRIMARY KEY, date DATE NOT NULL, supplier_id BIGINT NULL, supplier_name VARCHAR(191) NULL,
				label VARCHAR(255) NOT NULL, invoice_number VARCHAR(100) NULL, ht_cents BIGINT NOT NULL, vat_cents BIGINT NOT NULL DEFAULT 0, ttc_cents BIGINT NOT NULL,
				vat_rate VARCHAR(10) NULL, vat_regime VARCHAR(30) NOT NULL DEFAULT 'fr', vat_recovery INT NOT NULL DEFAULT 100, deductible INT NOT NULL DEFAULT 100,
				nature VARCHAR(10) NOT NULL DEFAULT 'services', category_id BIGINT NULL, account VARCHAR(12) NULL, payment_method VARCHAR(10) NOT NULL DEFAULT 'qonto',
				beneficiary VARCHAR(191) NULL, document_id BIGINT NULL, status VARCHAR(12) NOT NULL DEFAULT 'a_valider', source VARCHAR(20) NOT NULL DEFAULT 'manuel',
				fixed_asset_id BIGINT NULL, comment TEXT NULL, created_at DATETIME NOT NULL, KEY date (date)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}allocations (id BIGINT AUTO_INCREMENT PRIMARY KEY, bank_transaction_id BIGINT NOT NULL, target_type VARCHAR(20) NOT NULL,
				target_id BIGINT NULL, account VARCHAR(12) NULL, amount_cents BIGINT NOT NULL, created_at DATETIME NOT NULL,
				KEY tx (bank_transaction_id), KEY target (target_type, target_id)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}associate_movements (id BIGINT AUTO_INCREMENT PRIMARY KEY, date DATE NOT NULL, type VARCHAR(30) NOT NULL,
				amount_cents BIGINT NOT NULL, comment TEXT NULL, bank_transaction_id BIGINT NULL, expense_id BIGINT NULL, created_at DATETIME NOT NULL) $c",
			"CREATE TABLE IF NOT EXISTS {$p}fixed_assets (id BIGINT AUTO_INCREMENT PRIMARY KEY, label VARCHAR(255) NOT NULL, account VARCHAR(12) NOT NULL DEFAULT '218300',
				acquisition_date DATE NOT NULL, in_service_date DATE NOT NULL, cost_cents BIGINT NOT NULL, duration_years INT NOT NULL, method VARCHAR(20) NOT NULL DEFAULT 'lineaire',
				expense_id BIGINT NULL, disposal_date DATE NULL, disposal_price_cents BIGINT NULL, created_at DATETIME NOT NULL) $c",
			"CREATE TABLE IF NOT EXISTS {$p}entries (id BIGINT AUTO_INCREMENT PRIMARY KEY, journal VARCHAR(4) NOT NULL, number VARCHAR(30) NULL, date DATE NOT NULL,
				label VARCHAR(255) NOT NULL, piece_ref VARCHAR(100) NULL, piece_date DATE NULL, document_id BIGINT NULL, source_type VARCHAR(40) NULL, source_id BIGINT NULL,
				status VARCHAR(10) NOT NULL DEFAULT 'brouillon', reversal_of BIGINT NULL, reversed_by BIGINT NULL, validated_at DATETIME NULL, vseq BIGINT NULL, hash CHAR(64) NULL,
				created_at DATETIME NOT NULL, KEY source (source_type, source_id), KEY date (date), KEY number (number)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}entry_lines (id BIGINT AUTO_INCREMENT PRIMARY KEY, entry_id BIGINT NOT NULL, account VARCHAR(12) NOT NULL, aux VARCHAR(20) NULL,
				aux_label VARCHAR(191) NULL, label VARCHAR(255) NULL, debit_cents BIGINT NOT NULL DEFAULT 0, credit_cents BIGINT NOT NULL DEFAULT 0,
				vat_code VARCHAR(20) NULL, vat_base_cents BIGINT NULL, lettrage VARCHAR(20) NULL, KEY entry_id (entry_id), KEY account (account), KEY lettrage (lettrage)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}vat_returns (id BIGINT AUTO_INCREMENT PRIMARY KEY, period_start DATE NOT NULL, period_end DATE NOT NULL, form VARCHAR(10) NOT NULL,
				status VARCHAR(12) NOT NULL DEFAULT 'preparee', data LONGTEXT NOT NULL, entry_id BIGINT NULL, created_at DATETIME NOT NULL, validated_at DATETIME NULL,
				filed_at DATETIME NULL, UNIQUE KEY period (period_start, period_end)) $c",
			"CREATE TABLE IF NOT EXISTS {$p}month_closings (month CHAR(7) PRIMARY KEY, closed_at DATETIME NOT NULL, report LONGTEXT NULL) $c",
			"CREATE TABLE IF NOT EXISTS {$p}audit_log (id BIGINT AUTO_INCREMENT PRIMARY KEY, ts DATETIME NOT NULL, user_id BIGINT NULL, action VARCHAR(40) NOT NULL,
				object_type VARCHAR(40) NOT NULL, object_id BIGINT NULL, `before` LONGTEXT NULL, `after` LONGTEXT NULL, reason TEXT NULL) $c",
			"CREATE TABLE IF NOT EXISTS {$p}supplier_rules (pattern VARCHAR(191) PRIMARY KEY, category_id BIGINT NULL, vat_regime VARCHAR(30) NULL, updated_at DATETIME NOT NULL) $c",
		);
		foreach ( $sql as $s ) {
			$wpdb->query( $s );
		}
		foreach ( AC_Rules::accounts() as $a ) {
			$wpdb->query( self::prep( "INSERT IGNORE INTO {$p}accounts(number,label) VALUES(?,?)", $a ) );
		}
		if ( 0 === self::ival( 'SELECT COUNT(*) FROM {categories}' ) ) {
			foreach ( AC_Rules::categories() as $cat ) {
				self::insert( 'categories', array(
					'code' => $cat['code'], 'name' => $cat['name'], 'account' => $cat['account'], 'vat_recovery' => $cat['vat_recovery'],
					'deductible' => $cat['deductible'], 'rule' => $cat['rule'], 'is_asset' => $cat['is_asset'] ?? 0, 'help' => $cat['help'] ?? null,
				) );
			}
		}
		if ( 0 === self::ival( 'SELECT COUNT(*) FROM {fiscal_years}' ) ) {
			$y = (int) current_time( 'Y' );
			self::insert( 'fiscal_years', array( 'start_date' => "$y-01-01", 'end_date' => "$y-12-31" ) );
		}
		self::docs_dir();
		update_option( self::opt( 'schema' ), self::SCHEMA_VERSION, false );
	}

	/** Suppression complète (utilisée par les tests uniquement). */
	public static function drop_all(): void {
		global $wpdb;
		foreach ( $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( self::prefix() ) . '%' ) ) as $t ) {
			$wpdb->query( "DROP TABLE `$t`" );
		}
	}

	// ------------------------------------------------------------ paramètres
	public static function opt( string $key ): string {
		return 'aurea_compta_' . ( defined( 'AC_TABLE_PREFIX' ) ? AC_TABLE_PREFIX : '' ) . $key;
	}

	public static function defaults(): array {
		return array(
			'company'   => array( 'name' => 'Aurea Media', 'legal_form' => 'SASU', 'siren' => '', 'siret' => '', 'vat_number' => '', 'address' => '',
				'naf' => '', 'capital_cents' => 100000, 'capital_fully_paid' => true, 'held_by_natural_persons_75' => true,
				'associate_name' => '', 'associate_aliases' => array(), 'creation_date' => '' ),
			'tax'       => array( 'vat_regime' => 'reel_normal', 'vat_periodicity' => 'mensuelle', 'vat_start_date' => '', 'vat_on_debits' => false,
				'main_activity' => 'services', 'is_regime' => 'reel_simplifie', 'previous_is_cents' => 0, 'loss_carryforward_cents' => 0, 'salaried_count' => 0 ),
			'invoicing' => array( 'prefix' => 'F', 'credit_prefix' => 'AV', 'pattern' => '{prefix}{year}-{seq}', 'payment_terms_days' => 30, 'legal_mentions' => '' ),
			'qonto'     => array( 'enabled' => false, 'environment' => 'production', 'auto_sync_minutes' => 60, 'bank_account_id' => '', 'last_sync' => null ),
			'ai'        => array( 'enabled' => false, 'model' => 'claude-opus-5-5', 'auto_link_threshold' => 0.9 ),
			'bank_statement' => array(),
			'confirmed' => array( 'company' => false, 'tax' => false ),
		);
	}

	public static function get_setting( string $key ): array {
		$d   = self::defaults()[ $key ] ?? array();
		$val = get_option( self::opt( 's_' . $key ), null );
		return is_array( $val ) ? array_merge( $d, $val ) : $d;
	}

	public static function set_setting( string $key, array $value ): void {
		$before = self::get_setting( $key );
		update_option( self::opt( 's_' . $key ), $value, false );
		self::audit( 'modification', 'parametres', null, $before, $value );
	}

	// ------------------------------------------------------------ secrets (chiffrés avec les clés de WordPress)
	const SECRET_KEYS = array( 'qonto_login', 'qonto_secret_key', 'qonto_staging_token', 'anthropic_api_key' );

	private static function key(): string {
		return hash( 'sha256', wp_salt( 'auth' ) . '|aurea-compta', true );
	}

	public static function secrets(): array {
		$enc = get_option( self::opt( 'secrets' ), '' );
		if ( ! $enc || ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
			return array();
		}
		$raw = base64_decode( $enc );
		$dec = sodium_crypto_secretbox_open( substr( $raw, 24 ), substr( $raw, 0, 24 ), self::key() );
		return $dec ? ( json_decode( $dec, true ) ?: array() ) : array();
	}

	public static function save_secret( string $key, ?string $value ): void {
		if ( ! in_array( $key, self::SECRET_KEYS, true ) ) {
			throw new AC_Error( 'Clé inconnue' );
		}
		$s = self::secrets();
		if ( $value ) {
			$s[ $key ] = trim( $value );
		} else {
			unset( $s[ $key ] );
		}
		$nonce = random_bytes( 24 );
		update_option( self::opt( 'secrets' ), base64_encode( $nonce . sodium_crypto_secretbox( wp_json_encode( $s ), $nonce, self::key() ) ), false );
	}

	public static function masked_secrets(): array {
		$s   = self::secrets();
		$out = array();
		foreach ( self::SECRET_KEYS as $k ) {
			$out[ $k ] = ! empty( $s[ $k ] ) ? '•••• ' . substr( $s[ $k ], -4 ) : null;
		}
		return $out;
	}

	// ------------------------------------------------------------ historique
	public static function audit( string $action, string $type, $id = null, $before = null, $after = null, $reason = null ): void {
		self::insert( 'audit_log', array(
			'ts'          => self::now(),
			'user_id'     => get_current_user_id() ?: null,
			'action'      => $action,
			'object_type' => $type,
			'object_id'   => $id,
			'before'      => null === $before ? null : wp_json_encode( $before, JSON_UNESCAPED_UNICODE ),
			'after'       => null === $after ? null : wp_json_encode( $after, JSON_UNESCAPED_UNICODE ),
			'reason'      => $reason,
		) );
	}

	// ------------------------------------------------------------ dossier des pièces (privé)
	public static function docs_dir(): string {
		$name = get_option( self::opt( 'docs_dir' ) );
		if ( ! $name ) {
			$name = 'aurea-compta-' . wp_generate_password( 20, false, false );
			update_option( self::opt( 'docs_dir' ), $name, false );
		}
		$up  = wp_upload_dir( null, false );
		$dir = trailingslashit( $up['basedir'] ) . $name;
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		if ( ! file_exists( "$dir/.htaccess" ) ) {
			file_put_contents( "$dir/.htaccess", "Require all denied\nDeny from all\n" );
			file_put_contents( "$dir/index.php", "<?php // Silence.\n" );
			file_put_contents( "$dir/web.config", '<configuration><system.webServer><authorization><deny users="*" /></authorization></system.webServer></configuration>' );
		}
		return $dir;
	}
}

<?php
/**
 * Sauvegarde automatique, export complet et restauration (tables + justificatifs, sans les clés API).
 */
defined( 'ABSPATH' ) || exit;

class AC_Backup {

	const TABLES = array( 'fiscal_years', 'accounts', 'categories', 'parties', 'documents', 'bank_transactions', 'sales_invoices', 'sales_invoice_lines',
		'expenses', 'allocations', 'associate_movements', 'fixed_assets', 'entries', 'entry_lines', 'vat_returns', 'month_closings', 'audit_log', 'supplier_rules', 'mileage' );
	const SETTINGS = array( 'company', 'tax', 'invoicing', 'qonto', 'ai', 'bank_statement', 'confirmed', 'vehicle', 'reminders' );
	const KEEP = 30;

	private static function dump(): string {
		$data = array( 'app' => 'aurea-compta', 'version' => AC_VERSION, 'schema' => AC_DB::SCHEMA_VERSION, 'date' => current_time( 'c' ), 'tables' => array(), 'settings' => array() );
		foreach ( self::TABLES as $t ) {
			global $wpdb;
			$data['tables'][ $t ] = $wpdb->get_results( 'SELECT * FROM ' . AC_DB::t( $t ), ARRAY_A ) ?: array();
		}
		foreach ( self::SETTINGS as $s ) {
			$data['settings'][ $s ] = get_option( AC_DB::opt( 's_' . $s ), null );
		}
		return wp_json_encode( $data, JSON_UNESCAPED_UNICODE );
	}

	private static function dir(): string {
		$d = AC_DB::docs_dir() . '/_sauvegardes';
		wp_mkdir_p( $d );
		return $d;
	}

	/** Copie de la base dans le dossier privé, 30 dernières conservées. */
	public static function backup( string $reason = 'auto' ): string {
		$name = 'aurea-' . current_time( 'Ymd-His' ) . "-$reason.json.gz";
		file_put_contents( self::dir() . "/$name", gzencode( self::dump(), 6 ) );
		$files = glob( self::dir() . '/aurea-*.json.gz' );
		sort( $files );
		foreach ( array_slice( $files, 0, max( 0, count( $files ) - self::KEEP ) ) as $old ) {
			@unlink( $old );
		}
		return $name;
	}

	public static function daily(): void {
		if ( ! glob( self::dir() . '/aurea-' . current_time( 'Ymd' ) . '*' ) ) {
			self::backup( 'quotidienne' );
		}
	}

	public static function list(): array {
		$out = array();
		foreach ( array_reverse( glob( self::dir() . '/aurea-*.json.gz' ) ?: array() ) as $f ) {
			$out[] = array( 'name' => basename( $f ), 'size' => filesize( $f ), 'date' => gmdate( 'c', filemtime( $f ) ) );
		}
		return $out;
	}

	/** Archive complète : données + justificatifs. */
	public static function full_export(): string {
		$files = array( 'donnees.json' => self::dump(), 'manifest.json' => wp_json_encode( array( 'app' => 'aurea-compta', 'date' => current_time( 'c' ), 'schema' => AC_DB::SCHEMA_VERSION ) ) );
		$base  = AC_DB::docs_dir();
		$it    = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $it as $f ) {
			$rel = substr( $f->getPathname(), strlen( $base ) + 1 );
			if ( $f->isFile() && 0 !== strpos( $rel, '_sauvegardes' ) && ! in_array( $rel, array( '.htaccess', 'index.php', 'web.config' ), true ) ) {
				$files[ 'documents/' . $rel ] = array( 'file' => $f->getPathname() );
			}
		}
		return AC_Xlsx::zip( $files );
	}

	/** Restaure une archive d'export complet (la situation actuelle est sauvegardée avant). */
	public static function restore( string $archive ): array {
		$tmp = tempnam( get_temp_dir(), 'ac-restore' );
		file_put_contents( $tmp, $archive );
		$z = new ZipArchive();
		if ( true !== $z->open( $tmp ) || false === $z->locateName( 'donnees.json' ) || false === $z->locateName( 'manifest.json' ) ) {
			@unlink( $tmp );
			throw new AC_Error( 'Archive invalide : donnees.json ou manifest.json manquant.' );
		}
		$data = json_decode( $z->getFromName( 'donnees.json' ), true );
		if ( ! is_array( $data ) || 'aurea-compta' !== ( $data['app'] ?? '' ) ) {
			$z->close();
			@unlink( $tmp );
			throw new AC_Error( "Ce fichier n'est pas une sauvegarde Aurea Compta." );
		}
		self::backup( 'avant-restauration' );
		global $wpdb;
		AC_DB::tx( function () use ( $data, $wpdb ) {
			foreach ( self::TABLES as $t ) {
				$wpdb->query( 'DELETE FROM ' . AC_DB::t( $t ) );
				foreach ( $data['tables'][ $t ] ?? array() as $row ) {
					$wpdb->insert( AC_DB::t( $t ), $row );
				}
			}
			foreach ( self::SETTINGS as $s ) {
				if ( isset( $data['settings'][ $s ] ) ) {
					update_option( AC_DB::opt( 's_' . $s ), $data['settings'][ $s ], false );
				}
			}
		} );
		$base = realpath( AC_DB::docs_dir() );
		$n    = 0;
		for ( $i = 0; $i < $z->numFiles; $i++ ) {
			$name = $z->getNameIndex( $i );
			if ( 0 !== strpos( $name, 'documents/' ) || '/' === substr( $name, -1 ) || false !== strpos( $name, '..' ) ) {
				continue;
			}
			$target = $base . '/' . substr( $name, 10 );
			wp_mkdir_p( dirname( $target ) );
			file_put_contents( $target, $z->getFromIndex( $i ) );
			$n++;
		}
		$z->close();
		@unlink( $tmp );
		AC_DB::audit( 'restauration', 'sauvegarde', null, null, array( 'date_archive' => $data['date'] ?? null, 'documents' => $n ) );
		return array( 'ok' => true, 'documents' => $n );
	}
}

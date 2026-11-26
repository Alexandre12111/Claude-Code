<?php
/**
 * Opérations métier : tiers, pièces, factures, dépenses, banque, associé, immobilisations.
 */
defined( 'ABSPATH' ) || exit;

class AC_Services {

	// ============================================================ utilitaires
	public static function slug( ?string $s, int $n = 40 ): string {
		$s = sanitize_title( remove_accents( (string) $s ) );
		return substr( $s, 0, $n ) ?: 'inconnu';
	}

	public static function norm_name( ?string $s ): string {
		$s = strtoupper( remove_accents( (string) $s ) );
		$s = preg_replace( '/\b(SAS|SASU|SARL|SA|EURL|LTD|LIMITED|INC|GMBH|BV|SRL|SL|IRELAND|FRANCE|EUROPE)\b/', ' ', $s );
		$s = preg_replace( '/[^A-Z0-9 ]/', ' ', $s );
		return trim( preg_replace( '/\s+/', ' ', $s ) );
	}

	public static function name_similarity( ?string $a, ?string $b ): float {
		$a = self::norm_name( $a );
		$b = self::norm_name( $b );
		if ( '' === $a || '' === $b ) {
			return 0.0;
		}
		if ( $a === $b || false !== strpos( $b, $a ) || false !== strpos( $a, $b ) ) {
			return 1.0;
		}
		$ta = array_unique( explode( ' ', $a ) );
		$tb = array_unique( explode( ' ', $b ) );
		return count( array_intersect( $ta, $tb ) ) / max( 1, min( count( $ta ), count( $tb ) ) );
	}

	public static function cents( $v ): int {
		if ( null === $v || '' === $v ) {
			return 0;
		}
		return is_int( $v ) ? $v : AC_Money::to_cents( $v );
	}

	private static function has( array $d, string $k ): bool {
		return array_key_exists( $k, $d ) && null !== $d[ $k ] && '' !== $d[ $k ];
	}

	// ============================================================ tiers
	public static function get_or_create_party( string $kind, ?string $name, array $kw = array() ): int {
		$name = trim( (string) $name ) ?: ( 'client' === $kind ? 'Client' : 'Fournisseur' );
		foreach ( AC_DB::q( 'SELECT id, name FROM {parties} WHERE kind=?', array( $kind ) ) as $p ) {
			if ( self::norm_name( $p['name'] ) === self::norm_name( $name ) && self::norm_name( $name ) ) {
				return $p['id'];
			}
		}
		$id = AC_DB::insert( 'parties', array( 'kind' => $kind, 'name' => $name, 'siren' => $kw['siren'] ?? null, 'vat_number' => $kw['vat_number'] ?? null,
			'country' => $kw['country'] ?? 'FR', 'email' => $kw['email'] ?? null, 'address' => $kw['address'] ?? null, 'created_at' => AC_DB::now() ) );
		AC_DB::audit( 'creation', 'tiers', $id, null, array( 'kind' => $kind, 'name' => $name ) );
		return $id;
	}

	// ============================================================ pièces justificatives
	private static function doc_rel( string $kind, string $d, ?string $party, string $sha, string $ext, ?string $name = null ): string {
		return substr( $d, 0, 4 ) . '/' . substr( $d, 5, 2 ) . '/' . self::slug( $kind, 20 ) . '/' . self::slug( $party ?: 'divers', 30 ) . '/'
			. $d . '_' . self::slug( $party ?: ( $name ?: 'piece' ), 30 ) . '_' . substr( $sha, 0, 8 ) . $ext;
	}

	public static function store_document( string $filename, string $content, string $kind = 'justificatif', ?string $doc_date = null,
		?string $party = null, string $source = 'upload', ?string $external_id = null ): int {
		$sha = hash( 'sha256', $content );
		$ex  = AC_DB::q1( 'SELECT id FROM {documents} WHERE sha256=?', array( $sha ) );
		if ( $ex ) {
			return $ex['id'];
		}
		$ext  = strtolower( '.' . pathinfo( $filename ?: 'piece', PATHINFO_EXTENSION ) );
		$ext  = '.' === $ext ? '.bin' : $ext;
		$mime = wp_check_filetype( $filename ?: '' )['type'] ?: 'application/octet-stream';
		$d    = $doc_date ?: AC_DB::today();
		$rel  = self::doc_rel( $kind, $d, $party, $sha, $ext, pathinfo( (string) $filename, PATHINFO_FILENAME ) );
		$full = AC_DB::docs_dir() . '/' . $rel;
		wp_mkdir_p( dirname( $full ) );
		file_put_contents( $full, $content );
		$id = AC_DB::insert( 'documents', array( 'path' => $rel, 'original_name' => $filename, 'sha256' => $sha, 'mime' => $mime, 'size' => strlen( $content ),
			'kind' => $kind, 'doc_date' => $doc_date, 'party_name' => $party, 'source' => $source, 'external_id' => $external_id, 'uploaded_at' => AC_DB::now() ) );
		AC_DB::audit( 'ajout', 'piece', $id, null, array( 'fichier' => $filename, 'chemin' => $rel ) );
		return $id;
	}

	/** Range la pièce selon exercice / mois / type / tiers. */
	public static function refile_document( int $id, ?string $kind = null, ?string $doc_date = null, ?string $party = null ): void {
		$doc = AC_DB::q1( 'SELECT * FROM {documents} WHERE id=?', array( $id ) );
		if ( ! $doc ) {
			return;
		}
		$kind  = $kind ?: $doc['kind'];
		$d     = $doc_date ?: ( $doc['doc_date'] ?: substr( $doc['uploaded_at'], 0, 10 ) );
		$party = $party ?: $doc['party_name'];
		$ext   = '.' . pathinfo( $doc['path'], PATHINFO_EXTENSION );
		$rel   = self::doc_rel( $kind, $d, $party, $doc['sha256'], $ext );
		$base  = AC_DB::docs_dir() . '/';
		if ( $rel !== $doc['path'] && file_exists( $base . $doc['path'] ) ) {
			wp_mkdir_p( dirname( $base . $rel ) );
			rename( $base . $doc['path'], $base . $rel );
		}
		AC_DB::exec( 'UPDATE {documents} SET path=?, kind=?, doc_date=?, party_name=? WHERE id=?', array( $rel, $kind, $d, $party, $id ) );
	}

	public static function document_path( array $doc ): string {
		return AC_DB::docs_dir() . '/' . $doc['path'];
	}

	public static function lock_document( $id ): void {
		if ( $id ) {
			AC_DB::exec( 'UPDATE {documents} SET locked=1 WHERE id=?', array( (int) $id ) );
		}
	}

	// ============================================================ factures clients
	public static function next_invoice_number( string $kind, string $issue_date ): string {
		$inv    = AC_DB::get_setting( 'invoicing' );
		$prefix = 'avoir' === $kind ? $inv['credit_prefix'] : $inv['prefix'];
		$year   = substr( $issue_date, 0, 4 );
		$seq    = 0;
		foreach ( AC_DB::q( 'SELECT number FROM {sales_invoices} WHERE number LIKE ?', array( "$prefix$year-%" ) ) as $r ) {
			if ( preg_match( '/(\d+)$/', $r['number'], $m ) ) {
				$seq = max( $seq, (int) $m[1] );
			}
		}
		return str_replace( array( '{prefix}', '{year}', '{seq}' ), array( $prefix, $year, str_pad( (string) ( $seq + 1 ), 4, '0', STR_PAD_LEFT ) ), $inv['pattern'] );
	}

	/** Numéro unique, séquence chronologique continue (CGI ann. II art. 242 nonies A). */
	public static function check_invoice_number( string $number, string $issue_date, ?int $exclude = null ): array {
		$w = array();
		if ( AC_DB::q1( 'SELECT id FROM {sales_invoices} WHERE number=? AND id<>?', array( $number, $exclude ?: 0 ) ) ) {
			throw new AC_Error( "Le numéro $number est déjà utilisé : chaque facture doit avoir un numéro unique." );
		}
		if ( preg_match( '/^(.*?)(\d+)$/', $number, $m ) ) {
			list( , $prefix, $n ) = $m;
			$n    = (int) $n;
			$seqs = array();
			foreach ( AC_DB::q( 'SELECT number, issue_date FROM {sales_invoices} WHERE number LIKE ? AND id<>?', array( $prefix . '%', $exclude ?: 0 ) ) as $r ) {
				if ( preg_match( '/^(.*?)(\d+)$/', $r['number'], $mm ) && $mm[1] === $prefix ) {
					$seqs[ (int) $mm[2] ] = $r['issue_date'];
				}
			}
			if ( $seqs ) {
				if ( $n > max( array_keys( $seqs ) ) + 1 ) {
					$w[] = "Rupture de séquence : le dernier numéro de la série est $prefix" . max( array_keys( $seqs ) ) . '.';
				}
				foreach ( $seqs as $k => $d ) {
					if ( ( $k < $n && $d > $issue_date ) || ( $k > $n && $d < $issue_date ) ) {
						$w[] = 'Ordre chronologique non respecté : une facture de numéro inférieur porte une date postérieure.';
						break;
					}
				}
			}
		}
		return $w;
	}

	private static function compute_lines( array $lines ): array {
		$out = array();
		foreach ( $lines as $ln ) {
			$qty_bp = AC_Money::rate_bp( (string) ( $ln['quantity'] ?? '1' ) ?: '1' );
			$unit   = self::cents( $ln['unit_price_cents'] ?? ( isset( $ln['unit_price'] ) ? AC_Money::to_cents( $ln['unit_price'] ) : 0 ) );
			$ht     = isset( $ln['ht_cents'] ) && '' !== $ln['ht_cents'] ? self::cents( $ln['ht_cents'] ) : AC_Money::div_round( $qty_bp * $unit, 100 );
			$vr     = AC_Money::rate_str( $ln['vat_rate'] ?? '20' );
			$vat    = AC_Money::vat_from_ht( $ht, $vr );
			if ( self::has( $ln, 'vat_cents' ) ) {
				$given = self::cents( $ln['vat_cents'] );
				if ( abs( $given - $vat ) > 1 ) {
					throw new AC_Error( 'TVA incohérente sur la ligne « ' . ( $ln['description'] ?? '' ) . " » : $given ≠ $vat centimes attendus." );
				}
				$vat = $given;
			}
			$out[] = array( 'description' => ( $ln['description'] ?? '' ) ?: 'Prestation', 'quantity' => AC_Money::rate_str( $ln['quantity'] ?? '1' ),
				'unit_price_cents' => $unit, 'vat_rate' => $vr, 'account' => ( $ln['account'] ?? '' ) ?: '706000', 'ht_cents' => $ht, 'vat_cents' => $vat );
		}
		return $out;
	}

	public static function save_sales_invoice( array $data, ?int $id = null, string $reason = 'Saisie facture' ): array {
		return AC_DB::tx( function () use ( $data, $id, $reason ) {
			$kind      = $data['kind'] ?? 'facture';
			$issue     = $data['issue_date'];
			$client_id = ! empty( $data['client_id'] ) ? (int) $data['client_id'] : self::get_or_create_party( 'client', $data['client_name'] ?? '',
				array( 'vat_number' => $data['client_vat_number'] ?? null, 'country' => $data['client_country'] ?? null ) );
			$regime = $data['vat_regime'] ?? 'fr';
			$lines  = self::compute_lines( $data['lines'] ?? array() );
			if ( ! $lines ) {
				throw new AC_Error( 'Une facture doit comporter au moins une ligne.' );
			}
			if ( 'fr' !== $regime ) {
				foreach ( $lines as &$l ) {
					$l['vat_rate']  = '0';
					$l['vat_cents'] = 0;
				}
				unset( $l );
			}
			$ht     = array_sum( array_column( $lines, 'ht_cents' ) );
			$vat    = array_sum( array_column( $lines, 'vat_cents' ) );
			$before = null;
			if ( $id ) {
				$before = AC_DB::q1( 'SELECT * FROM {sales_invoices} WHERE id=?', array( $id ) );
				if ( AC_DB::q1( "SELECT id FROM {entries} WHERE source_type='vente' AND source_id=? AND status='valide'", array( $id ) ) ) {
					throw new AC_Error( 'Cette facture est comptabilisée et validée : elle ne peut plus être modifiée. Émettez un avoir.' );
				}
			}
			$number   = ( $data['number'] ?? '' ) ?: ( $before ? $before['number'] : self::next_invoice_number( $kind, $issue ) );
			$warnings = self::check_invoice_number( $number, $issue, $id );
			$credited = ! empty( $data['credited_invoice_id'] ) ? (int) $data['credited_invoice_id'] : null;
			if ( 'avoir' === $kind ) {
				if ( ! $credited ) {
					throw new AC_Error( "Un avoir doit référencer la facture qu'il corrige." );
				}
				$orig    = AC_DB::q1( 'SELECT * FROM {sales_invoices} WHERE id=?', array( $credited ) );
				$already = AC_DB::ival( "SELECT COALESCE(SUM(ttc_cents),0) FROM {sales_invoices} WHERE credited_invoice_id=? AND id<>? AND status='emise'", array( $credited, $id ?: 0 ) );
				if ( $ht + $vat + $already > $orig['ttc_cents'] ) {
					throw new AC_Error( "Le total des avoirs dépasse le montant de la facture d'origine." );
				}
				$client_id = $orig['client_id'];
			}
			$row = array( 'number' => $number, 'kind' => $kind, 'credited_invoice_id' => $credited, 'client_id' => $client_id, 'issue_date' => $issue,
				'due_date' => ( $data['due_date'] ?? '' ) ?: null, 'nature' => $data['nature'] ?? 'services', 'vat_regime' => $regime, 'ht_cents' => $ht,
				'vat_cents' => $vat, 'ttc_cents' => $ht + $vat, 'status' => $data['status'] ?? 'emise', 'document_id' => ( $data['document_id'] ?? null ) ?: null,
				'notes' => $data['notes'] ?? null );
			if ( $id ) {
				AC_DB::update( 'sales_invoices', $row, $id );
				AC_DB::exec( 'DELETE FROM {sales_invoice_lines} WHERE invoice_id=?', array( $id ) );
			} else {
				$id = AC_DB::insert( 'sales_invoices', $row + array( 'created_at' => AC_DB::now() ) );
			}
			foreach ( $lines as $l ) {
				AC_DB::insert( 'sales_invoice_lines', $l + array( 'invoice_id' => $id ) );
			}
			AC_DB::audit( $before ? 'modification' : 'creation', 'facture_client', $id, $before, AC_DB::q1( 'SELECT * FROM {sales_invoices} WHERE id=?', array( $id ) ), $reason );
			if ( ! empty( $data['document_id'] ) ) {
				$c = AC_DB::q1( 'SELECT name FROM {parties} WHERE id=?', array( $client_id ) );
				self::refile_document( (int) $data['document_id'], 'facture-client', $issue, $c['name'] );
			}
			AC_Posting::post_sales_invoice( $id, $reason );
			if ( 'avoir' === $kind ) {
				AC_Posting::repost_bank_for( 'vente', $credited, $reason );
			}
			AC_Posting::repost_bank_for( 'vente', $id, $reason );
			return array( 'id' => $id, 'number' => $number, 'warnings' => $warnings );
		} );
	}

	public static function invoice_status( array $inv ): array {
		$paid = AC_DB::ival( "SELECT COALESCE(SUM(amount_cents),0) FROM {allocations} WHERE target_type='vente' AND target_id=?", array( $inv['id'] ) );
		if ( 'avoir' === $inv['kind'] ) {
			$rem = -$inv['ttc_cents'] - $paid;
			return array( 'paid_cents' => $paid, 'credited_cents' => 0, 'remaining_cents' => $rem, 'payment_status' => ( 0 === $rem && $paid ) ? 'rembourse' : 'impute' );
		}
		$credited = AC_DB::ival( "SELECT COALESCE(SUM(ttc_cents),0) FROM {sales_invoices} WHERE credited_invoice_id=? AND status='emise'", array( $inv['id'] ) );
		$rem      = $inv['ttc_cents'] - $credited - $paid;
		$st       = 'emise' !== $inv['status'] ? $inv['status'] : ( $rem <= 0 ? 'payee' : ( $paid > 0 ? 'partielle' : 'impayee' ) );
		return array( 'paid_cents' => $paid, 'credited_cents' => $credited, 'remaining_cents' => $rem, 'payment_status' => $st );
	}

	public static function delete_sales_invoice( int $id ): void {
		AC_DB::tx( function () use ( $id ) {
			$inv = AC_DB::q1( 'SELECT * FROM {sales_invoices} WHERE id=?', array( $id ) );
			if ( 'brouillon' !== $inv['status'] ) {
				throw new AC_Error( 'Une facture émise ne se supprime pas : émettez un avoir.' );
			}
			AC_DB::exec( 'DELETE FROM {sales_invoice_lines} WHERE invoice_id=?', array( $id ) );
			AC_DB::exec( 'DELETE FROM {sales_invoices} WHERE id=?', array( $id ) );
			AC_DB::audit( 'suppression', 'facture_client', $id, $inv, null );
		} );
	}

	// ============================================================ dépenses
	public static function fiscal_warnings( array $exp, ?array $cat ): array {
		$w = array();
		$d = ( $exp['date'] ?? '' ) ?: AC_DB::today();
		if ( ! $cat ) {
			return array( 'Catégorie non renseignée.' );
		}
		$ttc = (int) ( $exp['ttc_cents'] ?? 0 );
		$ht  = (int) ( $exp['ht_cents'] ?? 0 );
		$vat = (int) ( $exp['vat_cents'] ?? 0 );
		$rec = (int) ( $exp['vat_recovery'] ?? 100 );
		if ( 'cadeau' === $cat['rule'] ) {
			if ( $ttc > AC_Rules::get( 'tva_seuil_cadeaux_ttc_cents', $d ) && $rec > 0 ) {
				$w[] = "Cadeau de plus de 73 € TTC : la TVA n'est pas récupérable (sauf si plusieurs bénéficiaires, à préciser).";
			}
			if ( empty( $exp['beneficiary'] ) ) {
				$w[] = 'Cadeau : indiquez le bénéficiaire (relevé des frais généraux).';
			}
		}
		if ( 'deplacement' === $cat['rule'] && $vat && $rec > 0 ) {
			$w[] = 'Transport de personnes et hébergement du dirigeant : TVA en principe non récupérable.';
		}
		if ( in_array( $cat['rule'], array( 'repas', 'invitation' ), true ) && empty( $exp['comment'] ) ) {
			$w[] = "Repas : précisez l'objet et les participants en commentaire pour justifier l'intérêt de l'entreprise.";
		}
		if ( 'materiel' === $cat['rule'] && $ht >= AC_Rules::get( 'seuil_immobilisation_ht_cents', $d ) ) {
			$w[] = 'Matériel de 500 € HT ou plus : à enregistrer en immobilisation.';
		}
		if ( 'personnel' === $cat['rule'] ) {
			$w[] = "Dépense personnelle : non déductible, imputée au compte courant d'associé.";
		}
		if ( 'fr' === ( $exp['vat_regime'] ?? 'fr' ) && $vat && $ht ) {
			$r = $exp['vat_rate'] ?? '';
			if ( $r && ! in_array( $r, array( '0', '-1' ), true ) ) {
				$expected = AC_Money::vat_from_ht( $ht, $r );
				if ( abs( $expected - $vat ) > max( 2, intdiv( $ht, 1000 ) ) ) {
					$w[] = sprintf( 'TVA incohérente : %s pour un taux de %s %% (attendu %s).', AC_Money::fmt( $vat ), $r, AC_Money::fmt( $expected ) );
				}
			}
		}
		if ( 'fr' === ( $exp['vat_regime'] ?? 'fr' ) && $vat && empty( $exp['document_id'] ) ) {
			$w[] = "Sans facture, la TVA n'est pas récupérable : ajoutez le justificatif.";
		}
		return $w;
	}

	public static function expense_amounts( array $d ): array {
		$regime = ( $d['vat_regime'] ?? '' ) ?: 'fr';
		$r      = AC_Money::rate_str( ( $d['vat_rate'] ?? '' ) ?: ( 'sans_tva' === $regime ? '0' : '20' ) );
		$ht     = self::has( $d, 'ht_cents' ) ? self::cents( $d['ht_cents'] ) : null;
		$vat    = self::has( $d, 'vat_cents' ) ? self::cents( $d['vat_cents'] ) : null;
		$ttc    = self::has( $d, 'ttc_cents' ) ? self::cents( $d['ttc_cents'] ) : null;
		if ( 'sans_tva' === $regime ) {
			$a = null !== $ttc ? $ttc : (int) $ht;
			return array( 'ht_cents' => $a, 'vat_cents' => 0, 'ttc_cents' => $a, 'vat_rate' => '0' );
		}
		if ( 0 === strpos( $regime, 'autoliquidation' ) ) {
			$base = null !== $ht ? $ht : (int) $ttc;
			return array( 'ht_cents' => $base, 'vat_cents' => $vat ?: AC_Money::vat_from_ht( $base, $r ), 'ttc_cents' => $base, 'vat_rate' => $r );
		}
		if ( null !== $ttc && null !== $ht && null === $vat ) {
			$vat = $ttc - $ht;
		}
		if ( null !== $ttc && null !== $vat && null === $ht ) {
			$ht = $ttc - $vat;
		}
		if ( null !== $ht && null !== $vat ) {
			$ttc = null === $ttc ? $ht + $vat : $ttc;
			if ( $ht + $vat !== $ttc ) {
				throw new AC_Error( 'HT + TVA ≠ TTC (' . ( $ht + $vat ) . " ≠ $ttc centimes)." );
			}
			return array( 'ht_cents' => $ht, 'vat_cents' => $vat, 'ttc_cents' => $ttc, 'vat_rate' => $r );
		}
		if ( null !== $ttc ) {
			list( $ht, $vat ) = AC_Money::split_ttc( $ttc, $r );
			return array( 'ht_cents' => $ht, 'vat_cents' => $vat, 'ttc_cents' => $ttc, 'vat_rate' => $r );
		}
		if ( null !== $ht ) {
			$vat = AC_Money::vat_from_ht( $ht, $r );
			return array( 'ht_cents' => $ht, 'vat_cents' => $vat, 'ttc_cents' => $ht + $vat, 'vat_rate' => $r );
		}
		throw new AC_Error( 'Montant manquant.' );
	}

	const EXPENSE_FIELDS = array( 'date', 'supplier_id', 'supplier_name', 'label', 'invoice_number', 'ht_cents', 'vat_cents', 'ttc_cents', 'vat_rate',
		'vat_regime', 'vat_recovery', 'deductible', 'nature', 'category_id', 'account', 'payment_method', 'beneficiary', 'document_id', 'status', 'source', 'comment' );

	private static function asset_cost( array $e ): int {
		return $e['ht_cents'] + ( 'fr' === $e['vat_regime'] ? $e['vat_cents'] - AC_Money::pct_of( (int) $e['vat_cents'], $e['vat_recovery'] ) : 0 );
	}

	public static function save_expense( array $data, ?int $id = null, string $reason = 'Saisie dépense' ): array {
		return AC_DB::tx( function () use ( $data, $id, $reason ) {
			$before = $id ? AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( $id ) ) : null;
			$m      = $before ?: array();
			foreach ( $data as $k => $v ) {
				if ( in_array( $k, self::EXPENSE_FIELDS, true ) ) {
					$m[ $k ] = $v;
				}
			}
			if ( array_intersect( array_keys( $data ), array( 'ht_cents', 'vat_cents', 'ttc_cents', 'vat_rate', 'vat_regime' ) ) ) {
				$src = array( 'ht_cents' => $data['ht_cents'] ?? null, 'vat_cents' => $data['vat_cents'] ?? null, 'ttc_cents' => $data['ttc_cents'] ?? null );
				if ( $before && ! self::has( $src, 'ht_cents' ) && ! self::has( $src, 'vat_cents' ) && ! self::has( $src, 'ttc_cents' ) ) {
					$src = array( 'ht_cents' => $before['ht_cents'], 'vat_cents' => $before['vat_cents'], 'ttc_cents' => $before['ttc_cents'] );
				}
				$m = array_merge( $m, self::expense_amounts( $src + array( 'vat_rate' => $m['vat_rate'] ?? null, 'vat_regime' => $m['vat_regime'] ?? null ) ) );
			}
			$cat = AC_Posting::category( $m['category_id'] ?? null );
			if ( $cat && ( ! $before || ( array_key_exists( 'category_id', $data ) && (int) $data['category_id'] !== (int) $before['category_id'] ) ) ) {
				if ( ! array_key_exists( 'vat_recovery', $data ) ) {
					$m['vat_recovery'] = $cat['vat_recovery'];
				}
				if ( ! array_key_exists( 'deductible', $data ) ) {
					$m['deductible'] = $cat['deductible'];
				}
				if ( ! array_key_exists( 'account', $data ) ) {
					$m['account'] = null;
				}
				if ( 0 === $cat['vat_recovery'] && 'fr' === ( $m['vat_regime'] ?? 'fr' ) && empty( $m['vat_cents'] ) ) {
					$m['vat_regime'] = 'sans_tva';
				}
			}
			if ( $cat && 'cadeau' === $cat['rule'] && ! array_key_exists( 'vat_recovery', $data )
				&& ( $m['ttc_cents'] ?? 0 ) > AC_Rules::get( 'tva_seuil_cadeaux_ttc_cents', $m['date'] ) ) {
				$m['vat_recovery'] = 0;
			}
			if ( $cat && 'personnel' === $cat['rule'] ) {
				$m['deductible']   = 0;
				$m['vat_recovery'] = 0;
			}
			$m += array( 'vat_regime' => 'fr', 'vat_recovery' => 100, 'deductible' => 100, 'nature' => 'services', 'payment_method' => 'qonto',
				'status' => 'a_valider', 'source' => 'manuel' );
			foreach ( array( 'category_id', 'supplier_id', 'document_id' ) as $k ) {
				$m[ $k ] = ! empty( $m[ $k ] ) ? (int) $m[ $k ] : null;
			}
			foreach ( array( 'vat_recovery', 'deductible' ) as $k ) {
				$m[ $k ] = (int) $m[ $k ];
			}
			if ( empty( $m['supplier_id'] ) && ! empty( $m['supplier_name'] ) ) {
				$m['supplier_id'] = self::get_or_create_party( 'supplier', $m['supplier_name'] );
			}
			if ( empty( $m['label'] ) ) {
				$m['label'] = $cat ? $cat['name'] : 'Dépense';
			}
			$row = array();
			foreach ( self::EXPENSE_FIELDS as $k ) {
				if ( array_key_exists( $k, $m ) ) {
					$row[ $k ] = '' === $m[ $k ] ? null : $m[ $k ];
				}
			}
			if ( empty( $row['date'] ) ) {
				throw new AC_Error( 'Date manquante.' );
			}
			if ( $id ) {
				if ( 'valide' === $before['status'] && ( $data['status'] ?? '' ) !== 'valide' ) {
					$reason .= " (modification d'une dépense validée)";
				}
				AC_DB::update( 'expenses', $row, $id );
			} else {
				$id = AC_DB::insert( 'expenses', $row + array( 'created_at' => AC_DB::now() ) );
			}
			$after = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( $id ) );
			AC_DB::audit( $before ? 'modification' : 'creation', 'depense', $id, $before, $after, $reason );
			if ( $cat && $cat['is_asset'] && ! $after['fixed_asset_id'] && $after['ttc_cents'] > 0 ) {
				$acc   = $after['account'] ?: $cat['account'];
				$types = AC_Rules::asset_types();
				$fa    = AC_DB::insert( 'fixed_assets', array( 'label' => $after['label'], 'account' => $acc, 'acquisition_date' => $after['date'],
					'in_service_date' => $after['date'], 'cost_cents' => self::asset_cost( $after ), 'duration_years' => $types[ $acc ]['years'] ?? 3,
					'expense_id' => $id, 'created_at' => AC_DB::now() ) );
				AC_DB::exec( 'UPDATE {expenses} SET fixed_asset_id=? WHERE id=?', array( $fa, $id ) );
			} elseif ( $after['fixed_asset_id'] ) {
				AC_DB::exec( 'UPDATE {fixed_assets} SET cost_cents=?, acquisition_date=? WHERE id=?', array( self::asset_cost( $after ), $after['date'], $after['fixed_asset_id'] ) );
			}
			if ( $after['document_id'] ) {
				self::refile_document( $after['document_id'], 'facture-fournisseur', $after['date'], $after['supplier_name'] );
			}
			if ( 'valide' === $after['status'] ) {
				self::lock_document( $after['document_id'] );
			}
			if ( $after['supplier_name'] && $after['category_id'] && $after['ttc_cents'] > 0 && ( ! $cat || 'ik' !== $cat['rule'] ) ) {
				AC_DB::exec( 'INSERT INTO {supplier_rules}(pattern,category_id,vat_regime,updated_at) VALUES(?,?,?,?)
					ON DUPLICATE KEY UPDATE category_id=VALUES(category_id), vat_regime=VALUES(vat_regime), updated_at=VALUES(updated_at)',
					array( mb_substr( self::norm_name( $after['supplier_name'] ), 0, 190 ), $after['category_id'], $after['vat_regime'], AC_DB::now() ) );
			}
			AC_Posting::post_expense( $id, $reason );
			AC_Posting::repost_bank_for( 'depense', $id, $reason );
			return array( 'id' => $id, 'warnings' => self::fiscal_warnings( $after, $cat ) );
		} );
	}

	public static function delete_expense( int $id, string $reason = 'Suppression de la dépense' ): void {
		AC_DB::tx( function () use ( $id, $reason ) {
			$exp = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( $id ) );
			if ( 'valide' === $exp['status'] ) {
				throw new AC_Error( 'Dépense validée : elle ne peut pas être supprimée (modifiez la, la correction sera tracée).' );
			}
			$txs = AC_DB::q( "SELECT DISTINCT bank_transaction_id t FROM {allocations} WHERE target_type='depense' AND target_id=?", array( $id ) );
			AC_DB::exec( "DELETE FROM {allocations} WHERE target_type='depense' AND target_id=?", array( $id ) );
			AC_Ledger::clear_source( 'depense', $id, $reason );
			if ( $exp['fixed_asset_id'] ) {
				foreach ( AC_DB::q( 'SELECT id FROM {fiscal_years}' ) as $fy ) {
					AC_Ledger::clear_source( 'amortissement', $fy['id'] * 1000000 + $exp['fixed_asset_id'], $reason );
				}
				AC_Ledger::clear_source( 'sortie_immo', $exp['fixed_asset_id'], $reason );
				AC_DB::exec( 'DELETE FROM {fixed_assets} WHERE id=?', array( $exp['fixed_asset_id'] ) );
			}
			AC_DB::exec( 'DELETE FROM {expenses} WHERE id=?', array( $id ) );
			AC_DB::audit( 'suppression', 'depense', $id, $exp, null, $reason );
			foreach ( $txs as $t ) {
				AC_Posting::post_bank_transaction( (int) $t['t'], $reason );
			}
		} );
	}

	public static function expense_paid( array $exp ): int {
		if ( 'perso' === $exp['payment_method'] ) {
			return (int) $exp['ttc_cents'];
		}
		return -AC_DB::ival( "SELECT COALESCE(SUM(amount_cents),0) FROM {allocations} WHERE target_type='depense' AND target_id=?", array( $exp['id'] ) );
	}

	// ============================================================ banque
	public static function import_transactions( array $items, string $source ): array {
		return AC_DB::tx( function () use ( $items, $source ) {
			$new = 0;
			$dup = 0;
			foreach ( $items as $it ) {
				if ( AC_DB::q1( 'SELECT id FROM {bank_transactions} WHERE external_id=?', array( $it['external_id'] ) ) ) {
					$dup++;
					continue;
				}
				$twin = AC_DB::q1( 'SELECT id FROM {bank_transactions} WHERE date=? AND amount_cents=? AND source<>? AND (counterparty=? OR label=?)',
					array( $it['date'], $it['amount_cents'], $source, $it['counterparty'] ?? null, $it['label'] ?? null ) );
				if ( $twin ) {
					$dup++;
					AC_DB::audit( 'doublon_ignore', 'operation_bancaire', $twin['id'], null, array( 'external_id' => $it['external_id'], 'source' => $source ) );
					continue;
				}
				$tid = AC_DB::insert( 'bank_transactions', array(
					'source' => $source, 'external_id' => $it['external_id'], 'bank_account' => $it['bank_account'] ?? null, 'date' => $it['date'],
					'emitted_at' => $it['emitted_at'] ?? null, 'amount_cents' => (int) $it['amount_cents'], 'label' => isset( $it['label'] ) ? mb_substr( (string) $it['label'], 0, 250 ) : null,
					'counterparty' => isset( $it['counterparty'] ) ? mb_substr( (string) $it['counterparty'], 0, 250 ) : null, 'operation_type' => $it['operation_type'] ?? null,
					'reference' => isset( $it['reference'] ) ? mb_substr( (string) $it['reference'], 0, 250 ) : null, 'note' => $it['note'] ?? null,
					'qonto_vat_cents' => $it['vat_cents'] ?? null, 'qonto_vat_rate' => $it['vat_rate'] ?? null, 'attachment_ids' => wp_json_encode( $it['attachment_ids'] ?? array() ),
					'raw' => wp_json_encode( $it['raw'] ?? array() ), 'imported_at' => AC_DB::now(),
				) );
				$new++;
				$sug = self::suggest_for_transaction( $tid );
				AC_Posting::post_bank_transaction( $tid, 'Import bancaire' );
				if ( $sug && ! empty( $sug['auto'] ) && ! empty( $sug['category_id'] ) ) {
					try {
						self::classify_transaction( $tid, $sug, 'Règle automatique (abonnement récurrent)' );
					} catch ( AC_Error $e ) {
						// la proposition reste à valider manuellement
					}
				}
			}
			return array( 'imported' => $new, 'duplicates' => $dup );
		} );
	}

	private static function associate_names(): array {
		$c = AC_DB::get_setting( 'company' );
		return array_values( array_filter( array_merge( array( $c['associate_name'] ?? '' ), (array) ( $c['associate_aliases'] ?? array() ) ) ) );
	}

	public static function suggest_for_transaction( int $id ): ?array {
		$tx  = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( $id ) );
		$who = $tx['counterparty'] ?: ( $tx['label'] ?: '' );
		$amt = $tx['amount_cents'];
		$sug = null;
		$is_associate = false;
		foreach ( self::associate_names() as $n ) {
			if ( self::name_similarity( $who, $n ) >= 0.99 ) {
				$is_associate = true;
			}
		}
		if ( $is_associate && in_array( $tx['operation_type'], array( null, 'transfer', 'income', 'swift_income', 'other' ), true ) ) {
			$sug = array( 'type' => 'associe', 'confidence' => 0.8, 'explanation' => $amt > 0
				? "Virement reçu de l'associé : apport en compte courant probable."
				: "Virement vers l'associé : remboursement de compte courant probable (à confirmer : ce n'est ni une dépense ni une rémunération)." );
		} elseif ( 'qonto_fee' === $tx['operation_type'] ) {
			$cat = AC_DB::q1( "SELECT id FROM {categories} WHERE code='banque'" );
			$sug = array( 'type' => 'depense_nouvelle', 'category_id' => $cat ? $cat['id'] : null, 'confidence' => 0.95, 'explanation' => 'Frais Qonto : frais bancaires.' );
		} elseif ( preg_match( '/\b(DGFIP|TRESOR PUBLIC|IMPOTS?\s?GOUV|SIE)\b/', self::norm_name( $who . ' ' . $tx['label'] ) ) ) {
			$sug = array( 'type' => false !== strpos( self::norm_name( $tx['label'] . ' ' . $tx['reference'] ), 'TVA' ) ? 'tva' : 'impot', 'confidence' => 0.6,
				'explanation' => "Paiement à l'administration fiscale : TVA, IS ou CFE à préciser." );
		} elseif ( $amt > 0 ) {
			$best = null;
			foreach ( AC_DB::q( "SELECT s.*, p.name client FROM {sales_invoices} s JOIN {parties} p ON p.id=s.client_id WHERE s.kind='facture' AND s.status='emise'" ) as $inv ) {
				$st = self::invoice_status( $inv );
				if ( $st['remaining_cents'] <= 0 ) {
					continue;
				}
				$gap   = $st['remaining_cents'] - $amt;
				$fee   = $gap > 0 && $gap <= max( 100, intdiv( $st['remaining_cents'] * 6, 100 ) ); // écart compatible avec une commission de paiement (6 % maximum)
				$score = $st['remaining_cents'] === $amt ? 0.6 : ( $fee ? 0.55 : ( $amt < $st['remaining_cents'] ? 0.25 : 0 ) );
				$score += 0.3 * self::name_similarity( $who, $inv['client'] );
				if ( $inv['number'] && false !== strpos( str_replace( ' ', '', $tx['reference'] . $tx['label'] ), str_replace( ' ', '', $inv['number'] ) ) ) {
					$score += 0.4;
				}
				if ( $score > 0.5 && ( ! $best || $score > $best[0] ) ) {
					$best = array( $score, $inv );
				}
			}
			if ( $best ) {
				$rem = self::invoice_status( $best[1] )['remaining_cents'];
				$sug = array( 'type' => 'vente', 'invoice_id' => $best[1]['id'], 'confidence' => min( $best[0], 1.0 ),
					'explanation' => "Règlement probable de la facture {$best[1]['number']} ({$best[1]['client']})." );
				if ( $rem > $amt && $rem - $amt <= max( 100, intdiv( $rem * 6, 100 ) ) ) {
					$sug['commission_cents'] = $rem - $amt;
					$sug['explanation']     .= ' Écart de ' . AC_Money::fmt( $rem - $amt ) . ' : commission de la plateforme de paiement probable.';
				}
			} elseif ( $rule = self::supplier_rule( $who ) ) {
				$sug = array( 'type' => 'remboursement_fournisseur', 'category_id' => $rule['category_id'], 'vat_regime' => $rule['vat_regime'], 'vat_rate' => $rule['vat_rate'],
					'confidence' => 0.7, 'explanation' => 'Crédit venant d\'un fournisseur connu : remboursement (avoir) probable.' );
			}
		} else {
			$best = null;
			foreach ( AC_DB::q( "SELECT * FROM {expenses} WHERE payment_method IN ('qonto','a_payer') AND ttc_cents=?", array( -$amt ) ) as $exp ) {
				if ( self::expense_paid( $exp ) >= $exp['ttc_cents'] ) {
					continue;
				}
				$days = abs( ( strtotime( $exp['date'] ) - strtotime( $tx['date'] ) ) / 86400 );
				if ( $days > 45 ) {
					continue;
				}
				$score = 0.55 + 0.35 * self::name_similarity( $who, $exp['supplier_name'] ) + ( $days <= 5 ? 0.1 : 0 );
				if ( ! $best || $score > $best[0] ) {
					$best = array( $score, $exp );
				}
			}
			if ( $best ) {
				$sug = array( 'type' => 'depense', 'expense_id' => $best[1]['id'], 'confidence' => min( $best[0], 1.0 ),
					'explanation' => "Paiement probable de la dépense « {$best[1]['supplier_name']} {$best[1]['label']} »." );
			} else {
				if ( $r = self::supplier_rule( $who ) ) {
					$sug = array( 'type' => 'depense_nouvelle', 'category_id' => $r['category_id'], 'vat_regime' => $r['vat_regime'], 'vat_rate' => $r['vat_rate'],
						'compute_vat' => (bool) $r['vat_rate'], 'confidence' => $r['auto'] ? 0.95 : 0.75, 'auto' => (bool) $r['auto'],
						'explanation' => $r['auto'] ? 'Abonnement récurrent : catégorie et TVA appliquées automatiquement.' : 'Fournisseur déjà connu : même catégorie que précédemment.' );
				}
			}
		}
		AC_DB::exec( "UPDATE {bank_transactions} SET suggestion=?, status=CASE WHEN status='a_traiter' AND ? IS NOT NULL THEN 'propose' ELSE status END WHERE id=?",
			array( $sug ? wp_json_encode( $sug, JSON_UNESCAPED_UNICODE ) : null, $sug ? 1 : null, $id ) );
		return $sug;
	}

	/** Affecte une opération bancaire. Types : depense_nouvelle, depense, vente, associe, capital, tva, is, virement_interne, compte, doublon. */
	public static function classify_transaction( int $id, array $p, string $reason = 'Rapprochement bancaire' ): array {
		return AC_DB::tx( function () use ( $id, $p, $reason ) {
			$tx = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( $id ) );
			if ( ! $tx ) {
				throw new AC_Error( 'Opération introuvable' );
			}
			$t      = $p['type'];
			$before = AC_DB::q( 'SELECT * FROM {allocations} WHERE bank_transaction_id=?', array( $id ) );
			// Dépense déjà créée depuis cette opération : réutilisée (pas de doublon), ou supprimée si l'opération change de nature.
			$own_expense = null;
			if ( empty( $p['append'] ) ) {
				$own_expense = AC_DB::q1( "SELECT x.* FROM {allocations} a JOIN {expenses} x ON x.id=a.target_id WHERE a.target_type='depense' AND a.bank_transaction_id=?
					AND x.source IN ('qonto','banque','ia') AND x.payment_method='qonto' AND x.date=? ORDER BY x.id LIMIT 1", array( $id, $tx['date'] ) );
				if ( $own_expense && ! in_array( $t, array( 'depense_nouvelle', 'remboursement_fournisseur' ), true ) ) {
					if ( 'valide' === $own_expense['status'] ) {
						throw new AC_Error( "Cette opération est liée à une dépense validée : modifiez ou annulez d'abord la dépense." );
					}
					AC_DB::exec( 'DELETE FROM {allocations} WHERE bank_transaction_id=?', array( $id ) );
					self::delete_expense( (int) $own_expense['id'], 'Opération reclassée' );
					$own_expense = null;
				}
			}
			$tx_doc = AC_DB::val( 'SELECT id FROM {documents} WHERE bank_transaction_id=? ORDER BY id LIMIT 1', array( $id ) );
			if ( empty( $p['append'] ) ) {
				AC_DB::exec( 'DELETE FROM {allocations} WHERE bank_transaction_id=?', array( $id ) );
				AC_DB::exec( 'DELETE FROM {associate_movements} WHERE bank_transaction_id=?', array( $id ) );
			}
			$amt    = self::has( $p, 'amount_cents' ) ? self::cents( $p['amount_cents'] ) : $tx['amount_cents'];
			$result = array();
			$alloc  = function ( $type, $target = null, $amount = null, $account = null ) use ( $id, $amt ) {
				AC_DB::insert( 'allocations', array( 'bank_transaction_id' => $id, 'target_type' => $type, 'target_id' => $target, 'account' => $account,
					'amount_cents' => null === $amount ? $amt : $amount, 'created_at' => AC_DB::now() ) );
			};
			switch ( $t ) {
				case 'depense_nouvelle':
				case 'remboursement_fournisseur':
					$refund = 'remboursement_fournisseur' === $t;
					if ( ! $refund && $amt >= 0 ) {
						throw new AC_Error( 'Une dépense correspond à un débit bancaire.' );
					}
					if ( $refund && $amt <= 0 ) {
						throw new AC_Error( 'Un remboursement de fournisseur correspond à un crédit bancaire.' );
					}
					if ( $refund && ( $cat_r = AC_Posting::category( $p['category_id'] ?? null ) ) && $cat_r['is_asset'] ) {
						throw new AC_Error( "Remboursement d'une immobilisation : enregistrez une sortie d'immobilisation." );
					}
					$data = array( 'date' => $tx['date'], 'supplier_name' => ( $p['supplier_name'] ?? '' ) ?: ( $tx['counterparty'] ?: $tx['label'] ),
						'label' => ( $p['label'] ?? '' ) ?: ( $refund ? 'Remboursement ' . ( $tx['counterparty'] ?: '' ) : ( $tx['label'] ?: 'Dépense' ) ), 'ttc_cents' => -$amt,
						'vat_rate' => ( $p['vat_rate'] ?? '' ) ?: ( ! in_array( $tx['qonto_vat_rate'], array( null, '-1' ), true ) ? $tx['qonto_vat_rate'] : null ),
						'vat_regime' => ( $p['vat_regime'] ?? '' ) ?: 'fr', 'category_id' => $p['category_id'] ?? null, 'nature' => ( $p['nature'] ?? '' ) ?: 'services',
						'payment_method' => 'qonto', 'document_id' => ( $p['document_id'] ?? null ) ?: ( $own_expense['document_id'] ?? null ) ?: $tx_doc,
						'comment' => $p['comment'] ?? null, 'beneficiary' => $p['beneficiary'] ?? null,
						'source' => 'qonto' === $tx['source'] ? 'qonto' : 'banque', 'status' => ! empty( $p['validate'] ) ? 'valide' : 'a_valider', 'invoice_number' => $p['invoice_number'] ?? null );
					// Taux choisi explicitement (catégorisation en lot) : la TVA est recalculée depuis le taux, sans reprendre celle de Qonto.
					$v = self::has( $p, 'vat_cents' ) ? $p['vat_cents'] : ( empty( $p['compute_vat'] ) ? $tx['qonto_vat_cents'] : null );
					if ( null !== $v && '' !== $v ) {
						$data['vat_cents'] = $refund ? -abs( (int) self::cents( $v ) ) : $v;
					} elseif ( $own_expense ) {
						$data['vat_cents'] = null;
						$data['ht_cents']  = null;
					}
					AC_DB::exec( 'DELETE FROM {allocations} WHERE bank_transaction_id=?', array( $id ) );
					$result = self::save_expense( $data, $own_expense ? (int) $own_expense['id'] : null, $reason );
					$alloc( 'depense', $result['id'] );
					break;
				case 'depense':
					$alloc( 'depense', (int) $p['expense_id'] );
					$exp = AC_DB::q1( 'SELECT * FROM {expenses} WHERE id=?', array( (int) $p['expense_id'] ) );
					if ( $tx_doc && ! $exp['document_id'] ) {
						self::save_expense( array( 'document_id' => (int) $tx_doc ), $exp['id'], 'Justificatif Qonto rattaché' );
					}
					if ( 'a_payer' === $exp['payment_method'] ) {
						AC_DB::exec( "UPDATE {expenses} SET payment_method='qonto' WHERE id=?", array( $exp['id'] ) );
						AC_Posting::post_expense( $exp['id'], $reason );
					}
					break;
				case 'vente':
					// Commission de la plateforme de paiement : la facture est soldée pour son montant total,
					// la commission est une dépense distincte (frais bancaires). La TVA collectée reste due sur le total facturé.
					$commission = self::has( $p, 'commission_cents' ) ? abs( self::cents( $p['commission_cents'] ) ) : 0;
					$list       = ! empty( $p['invoices'] ) ? $p['invoices'] : array( array( 'invoice_id' => $p['invoice_id'], 'amount_cents' => $amt + $commission ) );
					foreach ( $list as $it ) {
						$alloc( 'vente', (int) $it['invoice_id'], self::has( $it, 'amount_cents' ) ? self::cents( $it['amount_cents'] ) : $amt + $commission );
					}
					if ( $commission ) {
						$bank_cat = AC_DB::q1( "SELECT id FROM {categories} WHERE code='banque'" );
						$fee      = self::save_expense( array( 'date' => $tx['date'], 'supplier_name' => ( $p['provider'] ?? '' ) ?: 'Plateforme de paiement',
							'label' => 'Commission sur encaissement', 'ttc_cents' => $commission, 'vat_regime' => 'sans_tva', 'category_id' => $bank_cat ? $bank_cat['id'] : null,
							'payment_method' => 'qonto', 'source' => 'banque', 'status' => 'a_valider', 'comment' => 'Retenue par la plateforme sur le paiement client' ), null, $reason );
						$alloc( 'depense', $fee['id'], -$commission );
					}
					break;
				case 'associe':
				case 'capital':
					$mtype = 'capital' === $t ? 'capital_libere' : ( $tx['amount_cents'] > 0 ? 'apport' : 'remboursement' );
					$mv    = AC_DB::insert( 'associate_movements', array( 'date' => $tx['date'], 'type' => $mtype, 'amount_cents' => abs( $amt ),
						'comment' => $p['comment'] ?? null, 'bank_transaction_id' => $id, 'created_at' => AC_DB::now() ) );
					$alloc( $t, $mv );
					break;
				case 'tva':
					$alloc( 'tva', null, $amt, ( $p['account'] ?? '' ) ?: ( $amt < 0 ? AC_Rules::VAT_TO_PAY : AC_Rules::VAT_CREDIT ) );
					break;
				case 'is':
					$alloc( 'is', null, $amt, '444000' );
					break;
				case 'impot':
					throw new AC_Error( 'Précisez : TVA, IS (444) ou CFE (dépense catégorie Impôts et taxes).' );
				case 'virement_interne':
					$alloc( 'virement_interne', null, $amt, '580000' );
					break;
				case 'compte':
					if ( ! AC_DB::q1( 'SELECT number FROM {accounts} WHERE number=?', array( $p['account'] ?? '' ) ) ) {
						throw new AC_Error( 'Compte inconnu' );
					}
					$alloc( 'compte', null, $amt, $p['account'] );
					break;
				case 'doublon':
					AC_DB::exec( "UPDATE {bank_transactions} SET status='ignore', comment=? WHERE id=?", array( ( $p['comment'] ?? '' ) ?: 'Doublon', $id ) );
					break;
				case 'aucun':
					break;
				default:
					throw new AC_Error( "Type d'affectation inconnu : $t" );
			}
			$total = AC_DB::ival( 'SELECT COALESCE(SUM(amount_cents),0) FROM {allocations} WHERE bank_transaction_id=?', array( $id ) );
			if ( abs( $total ) > abs( $tx['amount_cents'] ) || ( $total && ( $total > 0 ) !== ( $tx['amount_cents'] > 0 ) ) ) {
				// (une affectation peut dépasser l'opération si une commission vient en déduction : seul le total compte)
				throw new AC_Error( "Le montant affecté dépasse le montant de l'opération." );
			}
			if ( ! empty( $p['comment'] ) ) {
				AC_DB::exec( 'UPDATE {bank_transactions} SET comment=? WHERE id=?', array( $p['comment'], $id ) );
			}
			AC_DB::audit( 'rapprochement', 'operation_bancaire', $id, $before, AC_DB::q( 'SELECT * FROM {allocations} WHERE bank_transaction_id=?', array( $id ) ), $reason );
			AC_Posting::post_bank_transaction( $id, $reason );
			if ( ! empty( $p['validate'] ) && ! empty( $result['id'] ) ) {
				foreach ( array_merge( AC_Ledger::source_entries( 'depense', (int) $result['id'] ), AC_Ledger::source_entries( 'banque', $id ) ) as $e ) {
					AC_Ledger::validate_entry( $e['id'] );
				}
			}
			return array( 'ok' => true ) + $result;
		} );
	}

	public static function supplier_rule( ?string $who ): ?array {
		foreach ( AC_DB::q( 'SELECT * FROM {supplier_rules} ORDER BY auto DESC' ) as $r ) {
			if ( $r['pattern'] && self::name_similarity( $who, $r['pattern'] ) >= 0.99 ) {
				return $r;
			}
		}
		return null;
	}

	public static function remember_rule( string $who, $category_id, string $regime, ?string $rate, bool $auto ): void {
		AC_DB::exec( 'INSERT INTO {supplier_rules}(pattern,category_id,vat_regime,vat_rate,auto,updated_at) VALUES(?,?,?,?,?,?)
			ON DUPLICATE KEY UPDATE category_id=VALUES(category_id), vat_regime=VALUES(vat_regime), vat_rate=VALUES(vat_rate), auto=VALUES(auto), updated_at=VALUES(updated_at)',
			array( mb_substr( self::norm_name( $who ), 0, 190 ), $category_id ? (int) $category_id : null, $regime, $rate, $auto ? 1 : 0, AC_DB::now() ) );
	}

	/** Opérations à traiter regroupées par tiers (abonnements récurrents). */
	public static function transaction_groups(): array {
		$groups = array();
		foreach ( AC_DB::q( "SELECT id, counterparty, label, amount_cents, date FROM {bank_transactions} WHERE status IN ('a_traiter','propose') ORDER BY date DESC" ) as $t ) {
			$name = $t['counterparty'] ?: $t['label'];
			$key  = self::norm_name( $name ) . ( $t['amount_cents'] < 0 ? '|D' : '|C' );
			if ( '' === self::norm_name( $name ) ) {
				continue;
			}
			$groups[ $key ] = $groups[ $key ] ?? array( 'name' => $name, 'debit' => $t['amount_cents'] < 0, 'ids' => array(), 'total_cents' => 0, 'last' => $t['date'] );
			$groups[ $key ]['ids'][]        = $t['id'];
			$groups[ $key ]['total_cents'] += $t['amount_cents'];
		}
		$out = array();
		foreach ( $groups as $g ) {
			if ( count( $g['ids'] ) >= 2 && $g['debit'] ) { // prélèvements récurrents uniquement
				$g['count'] = count( $g['ids'] );
				$r          = self::supplier_rule( $g['name'] );
				$g['rule']  = $r;
				$out[]      = $g;
			}
		}
		usort( $out, function ( $a, $b ) { return $b['count'] <=> $a['count']; } );
		return $out;
	}

	/** Catégorisation en lot (par exemple tous les prélèvements Ionos en hébergement, TVA 20 %). */
	public static function bulk_classify( array $ids, array $payload, bool $remember = false ): array {
		$done   = 0;
		$errors = array();
		$names  = array();
		foreach ( array_unique( array_map( 'intval', $ids ) ) as $id ) {
			$tx = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( $id ) );
			if ( ! $tx ) {
				continue;
			}
			$p = $payload;
			if ( 'depense_nouvelle' === ( $p['type'] ?? '' ) && $tx['amount_cents'] > 0 ) {
				$p['type'] = 'remboursement_fournisseur';
			}
			unset( $p['supplier_name'], $p['label'] );
			try {
				self::classify_transaction( $id, $p, 'Catégorisation en lot' );
				$names[ self::norm_name( $tx['counterparty'] ?: $tx['label'] ) ] = $tx['counterparty'] ?: $tx['label'];
				$done++;
			} catch ( AC_Error $e ) {
				$errors[] = AC_Checks::d( $tx['date'] ) . ' ' . ( $tx['counterparty'] ?: $tx['label'] ) . ' : ' . $e->getMessage();
			}
		}
		if ( $remember && in_array( $payload['type'] ?? '', array( 'depense_nouvelle', 'remboursement_fournisseur' ), true ) ) {
			foreach ( $names as $n ) {
				self::remember_rule( $n, $payload['category_id'] ?? null, ( $payload['vat_regime'] ?? '' ) ?: 'fr', ( $payload['vat_rate'] ?? '' ) ?: null, true );
			}
		}
		// Les opérations restantes de ces fournisseurs (remboursements, nouveaux prélèvements) reçoivent une nouvelle proposition.
		foreach ( AC_DB::q( "SELECT id FROM {bank_transactions} WHERE status IN ('a_traiter','propose')" ) as $t ) {
			self::suggest_for_transaction( (int) $t['id'] );
		}
		return array( 'done' => $done, 'errors' => $errors );
	}

	/** Ajoute un justificatif à une opération bancaire (et à la dépense qui lui est liée). */
	public static function attach_to_transaction( int $tx_id, int $doc_id ): void {
		AC_DB::tx( function () use ( $tx_id, $doc_id ) {
			AC_DB::exec( 'UPDATE {documents} SET bank_transaction_id=? WHERE id=?', array( $tx_id, $doc_id ) );
			foreach ( AC_DB::q( "SELECT x.id FROM {allocations} a JOIN {expenses} x ON x.id=a.target_id WHERE a.target_type='depense' AND a.bank_transaction_id=? AND x.document_id IS NULL",
				array( $tx_id ) ) as $x ) {
				self::save_expense( array( 'document_id' => $doc_id ), $x['id'], 'Justificatif ajouté' );
			}
			AC_DB::audit( 'ajout_justificatif', 'operation_bancaire', $tx_id, null, array( 'document' => $doc_id ) );
		} );
	}

	public static function apply_suggestion( int $id ): array {
		$tx  = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( $id ) );
		$sug = $tx['suggestion'] ? json_decode( $tx['suggestion'], true ) : null;
		if ( ! $sug ) {
			throw new AC_Error( 'Aucune proposition pour cette opération.' );
		}
		return self::classify_transaction( $id, $sug, 'Proposition acceptée' );
	}

	// ============================================================ associé
	public static function add_associate_movement( array $d ): array {
		return AC_DB::tx( function () use ( $d ) {
			$t      = $d['type'];
			$amount = self::cents( $d['amount_cents'] ?? 0 );
			if ( $amount <= 0 ) {
				throw new AC_Error( 'Le montant doit être positif.' );
			}
			if ( ! empty( $d['bank_transaction_id'] ) ) {
				$tid = (int) $d['bank_transaction_id'];
				self::classify_transaction( $tid, array( 'type' => 'capital_libere' === $t ? 'capital' : 'associe', 'comment' => $d['comment'] ?? null ) );
				$mv = AC_DB::q1( 'SELECT id FROM {associate_movements} WHERE bank_transaction_id=?', array( $tid ) );
				return array( 'id' => $mv['id'], 'matched' => true );
			}
			$mv = AC_DB::insert( 'associate_movements', array( 'date' => $d['date'], 'type' => $t, 'amount_cents' => $amount, 'comment' => $d['comment'] ?? null,
				'created_at' => AC_DB::now() ) );
			AC_DB::audit( 'creation', 'mouvement_associe', $mv, null, $d );
			AC_Posting::post_associate_movement( $mv );
			$sign  = in_array( $t, array( 'apport', 'capital_libere' ), true ) ? 1 : -1;
			$cands = AC_DB::q( "SELECT * FROM {bank_transactions} WHERE amount_cents=? AND status IN ('a_traiter','propose')
				AND date BETWEEN DATE_SUB(?, INTERVAL 10 DAY) AND DATE_ADD(?, INTERVAL 10 DAY)", array( $sign * $amount, $d['date'], $d['date'] ) );
			return array( 'id' => $mv, 'matched' => false, 'candidates' => $cands );
		} );
	}

	public static function link_movement( int $mv_id, int $tx_id ): void {
		AC_DB::tx( function () use ( $mv_id, $tx_id ) {
			$mv   = AC_DB::q1( 'SELECT * FROM {associate_movements} WHERE id=?', array( $mv_id ) );
			$tx   = AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( $tx_id ) );
			$sign = in_array( $mv['type'], array( 'apport', 'capital_libere' ), true ) ? 1 : -1;
			if ( $tx['amount_cents'] !== $sign * $mv['amount_cents'] ) {
				throw new AC_Error( 'Le montant du virement ne correspond pas.' );
			}
			AC_DB::exec( 'DELETE FROM {allocations} WHERE bank_transaction_id=?', array( $tx_id ) );
			AC_DB::exec( 'UPDATE {associate_movements} SET bank_transaction_id=?, date=? WHERE id=?', array( $tx_id, $tx['date'], $mv_id ) );
			AC_DB::insert( 'allocations', array( 'bank_transaction_id' => $tx_id, 'target_type' => 'capital_libere' === $mv['type'] ? 'capital' : 'associe',
				'target_id' => $mv_id, 'amount_cents' => $tx['amount_cents'], 'created_at' => AC_DB::now() ) );
			AC_DB::audit( 'rapprochement', 'mouvement_associe', $mv_id, null, array( 'operation' => $tx_id ) );
			AC_Posting::post_bank_transaction( $tx_id );
		} );
	}

	public static function delete_associate_movement( int $id ): void {
		AC_DB::tx( function () use ( $id ) {
			$mv = AC_DB::q1( 'SELECT * FROM {associate_movements} WHERE id=?', array( $id ) );
			if ( $mv['bank_transaction_id'] ) {
				AC_DB::exec( "DELETE FROM {allocations} WHERE bank_transaction_id=? AND target_type IN ('associe','capital') AND target_id=?", array( $mv['bank_transaction_id'], $id ) );
			}
			AC_DB::exec( 'DELETE FROM {associate_movements} WHERE id=?', array( $id ) );
			AC_Ledger::clear_source( 'associe', $id, 'Suppression du mouvement associé' );
			AC_DB::audit( 'suppression', 'mouvement_associe', $id, $mv, null );
			if ( $mv['bank_transaction_id'] ) {
				AC_Posting::post_bank_transaction( $mv['bank_transaction_id'] );
			}
		} );
	}

	/** Situation du compte courant d'associé, calculée depuis le grand livre (compte 455). */
	public static function associate_summary( ?string $to = null ): array {
		$rows = AC_DB::q( 'SELECT e.date, e.label, e.journal, e.source_type, e.source_id, l.debit_cents, l.credit_cents, l.label line_label
			FROM {entry_lines} l JOIN {entries} e ON e.id=l.entry_id WHERE l.account LIKE ?' . ( $to ? ' AND e.date<=?' : '' ) . ' ORDER BY e.date, e.id',
			$to ? array( '455%', $to ) : array( '455%' ) );
		$sum = function ( $col, $bank ) use ( $rows ) {
			$s = 0;
			foreach ( $rows as $r ) {
				if ( ( 'banque' === $r['source_type'] ) === $bank ) {
					$s += $r[ $col ];
				}
			}
			return $s;
		};
		$contrib  = $sum( 'credit_cents', true );
		$advances = $sum( 'credit_cents', false );
		$repaid   = $sum( 'debit_cents', true );
		$balance  = array_sum( array_column( $rows, 'credit_cents' ) ) - array_sum( array_column( $rows, 'debit_cents' ) );
		$pers     = AC_DB::ival( "SELECT COALESCE(SUM(a.amount_cents),0) FROM {allocations} a JOIN {expenses} x ON x.id=a.target_id JOIN {categories} c ON c.id=x.category_id
			WHERE a.target_type='depense' AND c.rule='personnel'" );
		return array(
			'apports_cents' => $contrib, 'avances_frais_cents' => $advances, 'total_injecte_cents' => $contrib + $advances,
			'remboursements_cents' => $repaid, 'depenses_perso_payees_par_societe_cents' => -$pers, 'autres_debits_cents' => $sum( 'debit_cents', false ),
			'solde_cents' => $balance, 'du_par_la_societe_cents' => max( $balance, 0 ),
			'sens' => $balance > 0 ? 'créditeur (la société vous doit cette somme)' : ( $balance < 0
				? "débiteur : vous devez cette somme à la société. Un compte courant débiteur est interdit pour le président d'une SAS (C. com. L225-43 et L227-1) : régularisez rapidement."
				: 'soldé' ),
			'mouvements' => $rows,
			'en_attente_de_virement' => AC_DB::q( "SELECT * FROM {associate_movements} WHERE bank_transaction_id IS NULL AND type IN ('apport','remboursement','capital_libere')" ),
			'capital_social_cents' => (int) AC_DB::get_setting( 'company' )['capital_cents'],
			'capital_libere_comptabilise_cents' => -AC_DB::ival( "SELECT COALESCE(SUM(debit_cents-credit_cents),0) FROM {entry_lines} WHERE account='101300'" ),
		);
	}

	// ============================================================ immobilisations
	private static function add_years( string $d, int $y ): string {
		$dt  = new DateTime( $d );
		$day = min( (int) $dt->format( 'd' ), 28 );
		return ( new DateTime( sprintf( '%04d-%s-%02d', (int) $dt->format( 'Y' ) + $y, $dt->format( 'm' ), $day ) ) )->modify( '-1 day' )->format( 'Y-m-d' );
	}

	private static function days_incl( string $a, string $b ): int {
		return (int) ( new DateTime( $a ) )->diff( new DateTime( $b ) )->format( '%r%a' ) + 1;
	}

	/** Amortissement linéaire au prorata temporis (jours réels). */
	public static function depreciation_for_year( array $a, string $fy_start, string $fy_end ): array {
		$start    = $a['in_service_date'];
		$life_end = self::add_years( $start, (int) $a['duration_years'] );
		$end      = $a['disposal_date'] ? min( $life_end, $a['disposal_date'] ) : $life_end;
		$cost     = (int) $a['cost_cents'];
		$total    = self::days_incl( $start, $life_end );
		$cumul    = function ( string $until ) use ( $start, $end, $cost, $total, $a ) {
			if ( $until < $start ) {
				return 0;
			}
			if ( $until >= $end && ! $a['disposal_date'] ) {
				return $cost;
			}
			return min( $cost, AC_Money::prorata( $cost, self::days_incl( $start, min( $until, $end ) ), $total ) );
		};
		$before = $cumul( ( new DateTime( $fy_start ) )->modify( '-1 day' )->format( 'Y-m-d' ) );
		$after  = $cumul( $fy_end );
		return array( 'dotation_cents' => $after - $before, 'cumul_debut_cents' => $before, 'cumul_fin_cents' => $after, 'vnc_fin_cents' => $cost - $after );
	}

	private static function dep_account( string $acc ): string {
		return AC_Rules::asset_types()[ $acc ]['dep_account'] ?? '281880';
	}

	public static function post_depreciation( int $fy_id, string $reason = 'Dotations aux amortissements' ): array {
		$fy  = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE id=?', array( $fy_id ) );
		$out = array();
		foreach ( AC_DB::q( 'SELECT * FROM {fixed_assets} WHERE in_service_date<=?', array( $fy['end_date'] ) ) as $a ) {
			$sid = $fy_id * 1000000 + $a['id'];
			AC_Ledger::clear_source( 'amortissement', $sid, $reason );
			$dep = self::depreciation_for_year( $a, $fy['start_date'], $fy['end_date'] );
			if ( $dep['dotation_cents'] > 0 && 'closed' !== $fy['status'] ) {
				$date = $a['disposal_date'] ? min( $fy['end_date'], $a['disposal_date'] ) : $fy['end_date'];
				AC_Ledger::create_entry( 'OD', $date, "Dotation amortissement {$a['label']}",
					array( ac_d( '681100', $dep['dotation_cents'] ), ac_c( self::dep_account( $a['account'] ), $dep['dotation_cents'] ) ),
					array( 'piece_ref' => 'IMMO' . $a['id'], 'source_type' => 'amortissement', 'source_id' => $sid, 'reason' => $reason ) );
			}
			$out[] = $a + $dep;
		}
		return $out;
	}

	public static function dispose_asset( int $id, string $date, int $price = 0 ): array {
		return AC_DB::tx( function () use ( $id, $date, $price ) {
			AC_DB::exec( 'UPDATE {fixed_assets} SET disposal_date=?, disposal_price_cents=? WHERE id=?', array( $date, $price, $id ) );
			$a  = AC_DB::q1( 'SELECT * FROM {fixed_assets} WHERE id=?', array( $id ) );
			$fy = AC_Ledger::fiscal_year_for( $date );
			self::post_depreciation( $fy['id'], "Amortissement jusqu'à la sortie" );
			$dep = self::depreciation_for_year( $a, $fy['start_date'], $date );
			AC_Ledger::clear_source( 'sortie_immo', $id, "Sortie d'immobilisation" );
			AC_Ledger::create_entry( 'OD', $date, "Sortie d'immobilisation {$a['label']}", array(
				ac_d( self::dep_account( $a['account'] ), $dep['cumul_fin_cents'] ), ac_d( '675000', $a['cost_cents'] - $dep['cumul_fin_cents'] ),
				ac_c( $a['account'], $a['cost_cents'] ) ), array( 'piece_ref' => 'IMMO' . $id, 'source_type' => 'sortie_immo', 'source_id' => $id ) );
			AC_DB::audit( 'sortie', 'immobilisation', $id, null, array( 'date' => $date, 'prix' => $price ) );
			return array( 'vnc_cents' => $a['cost_cents'] - $dep['cumul_fin_cents'],
				'note' => 'Le prix de cession encaissé se rapproche dans Banque en l\'imputant au compte 775000.' );
		} );
	}

	public static function save_fixed_asset( array $d, ?int $id = null ): array {
		return AC_DB::tx( function () use ( $d, $id ) {
			$acc  = ( $d['account'] ?? '' ) ?: '218300';
			$vals = array( 'label' => $d['label'], 'account' => $acc, 'acquisition_date' => $d['acquisition_date'],
				'in_service_date' => ( $d['in_service_date'] ?? '' ) ?: $d['acquisition_date'], 'cost_cents' => self::cents( $d['cost_cents'] ),
				'duration_years' => (int) ( ( $d['duration_years'] ?? 0 ) ?: ( AC_Rules::asset_types()[ $acc ]['years'] ?? 3 ) ) );
			if ( $id ) {
				$before = AC_DB::q1( 'SELECT * FROM {fixed_assets} WHERE id=?', array( $id ) );
				AC_DB::update( 'fixed_assets', $vals, $id );
				AC_DB::audit( 'modification', 'immobilisation', $id, $before, $d );
				if ( $before['expense_id'] ) {
					AC_Posting::post_expense( $before['expense_id'], "Modification de l'immobilisation" );
				}
			} else {
				$id = AC_DB::insert( 'fixed_assets', $vals + array( 'created_at' => AC_DB::now() ) );
				AC_DB::audit( 'creation', 'immobilisation', $id, null, $d );
			}
			return array( 'id' => $id );
		} );
	}
}

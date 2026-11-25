<?php
/**
 * Lecture des justificatifs et assistant.
 *
 * L'IA n'est jamais indispensable : les calculs sont faits par du code déterministe.
 * 1. Factur-X / ZUGFeRD (XML embarqué dans le PDF, format de la facturation électronique) : lecture exacte sans IA.
 * 2. Claude (SDK officiel Anthropic, fourni par l'extension complémentaire « Aurea Compta IA ») si une clé est configurée.
 */
defined( 'ABSPATH' ) || exit;

class AC_Ai {

	const NS = array( 'rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
		'ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
		'udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100' );

	public static function sdk_installed(): bool {
		return function_exists( 'aurea_compta_ia_autoload' );
	}

	public static function available(): bool {
		return self::sdk_installed() && ! empty( AC_DB::secrets()['anthropic_api_key'] ) && ! empty( AC_DB::get_setting( 'ai' )['enabled'] );
	}

	private static function model(): string {
		return AC_DB::get_setting( 'ai' )['model'] ?: 'claude-opus-5-5';
	}

	/** Appel Messages API (SDK officiel) avec repli automatique côté serveur en cas de refus. */
	private static function call( array $args ) {
		aurea_compta_ia_autoload();
		// AC_ANTHROPIC_BASE_URL : réservé aux tests (serveur simulé).
		$client = new \Anthropic\Client( apiKey: AC_DB::secrets()['anthropic_api_key'], baseUrl: defined( 'AC_ANTHROPIC_BASE_URL' ) ? AC_ANTHROPIC_BASE_URL : null );
		try {
			$resp = $client->beta->messages->create( ...array_merge( array( 'model' => self::model(), 'betas' => array( 'server-side-fallback-2026-07-01' ),
				'fallbacks' => 'default' ), $args ) );
		} catch ( \Anthropic\Core\Exceptions\AuthenticationException $e ) {
			throw new AC_Error( 'Clé API Anthropic invalide.' );
		} catch ( \Anthropic\Core\Exceptions\RateLimitException $e ) {
			throw new AC_Error( 'Limite de requêtes atteinte, réessayez dans une minute.' );
		} catch ( \Anthropic\Core\Exceptions\BadRequestException $e ) {
			throw new AC_Error( "Requête refusée par l'API : " . $e->getMessage() );
		} catch ( \Anthropic\Core\Exceptions\APIStatusException $e ) {
			throw new AC_Error( 'Erreur du service IA.' );
		} catch ( \Anthropic\Core\Exceptions\APIConnectionException $e ) {
			throw new AC_Error( 'Service IA injoignable : la saisie manuelle reste possible.' );
		}
		if ( 'refusal' === $resp->stopReason ) {
			throw new AC_Error( "L'IA a refusé de traiter ce document." );
		}
		return $resp;
	}

	// ------------------------------------------------------------ Factur-X
	public static function facturx_xml( string $pdf ): ?string {
		// Fichier XML embarqué (EmbeddedFile) : recherche des flux contenant une facture CII.
		if ( ! preg_match_all( '/stream\r?\n(.*?)\r?\nendstream/s', $pdf, $m ) ) {
			return null;
		}
		foreach ( $m[1] as $stream ) {
			foreach ( array( $stream, @gzuncompress( $stream ), @gzinflate( $stream ) ) as $s ) {
				if ( $s && false !== strpos( $s, 'CrossIndustryInvoice' ) ) {
					return $s;
				}
			}
		}
		return null;
	}

	public static function parse_cii( string $xml ): array {
		$x = new SimpleXMLElement( $xml );
		foreach ( self::NS as $p => $ns ) {
			$x->registerXPathNamespace( $p, $ns );
		}
		$t   = function ( $path ) use ( $x ) {
			$r = $x->xpath( $path );
			return $r ? trim( (string) $r[0] ) : '';
		};
		$raw = $t( '//rsm:ExchangedDocument/ram:IssueDateTime/udt:DateTimeString' );
		$vat_lines = array();
		foreach ( $x->xpath( '//ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax' ) ?: array() as $tt ) {
			$tt->registerXPathNamespace( 'ram', self::NS['ram'] );
			$g = function ( $p ) use ( $tt ) {
				$r = $tt->xpath( "ram:$p" );
				return $r ? (string) $r[0] : '0';
			};
			$vat_lines[] = array( 'rate' => $g( 'RateApplicablePercent' ), 'base' => $g( 'BasisAmount' ), 'vat' => $g( 'CalculatedAmount' ) );
		}
		$country = $t( '//ram:SellerTradeParty/ram:PostalTradeAddress/ram:CountryID' );
		$vat     = $t( '//ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TaxTotalAmount' ) ?: '0';
		$fr      = in_array( $country, array( 'FR', '' ), true );
		return array( 'document_type' => '381' === $t( '//rsm:ExchangedDocument/ram:TypeCode' ) ? 'avoir' : 'facture',
			'supplier_name' => $t( '//ram:SellerTradeParty/ram:Name' ), 'supplier_country' => $country,
			'supplier_vat_number' => $t( '//ram:SellerTradeParty/ram:SpecifiedTaxRegistration/ram:ID' ), 'customer_name' => $t( '//ram:BuyerTradeParty/ram:Name' ),
			'invoice_number' => $t( '//rsm:ExchangedDocument/ram:ID' ), 'date' => strlen( $raw ) >= 8 ? substr( $raw, 0, 4 ) . '-' . substr( $raw, 4, 2 ) . '-' . substr( $raw, 6, 2 ) : '',
			'currency' => $t( '//ram:InvoiceCurrencyCode' ) ?: 'EUR', 'total_ht' => $t( '//ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:TaxBasisTotalAmount' ),
			'total_vat' => $vat, 'total_ttc' => $t( '//ram:SpecifiedTradeSettlementHeaderMonetarySummation/ram:GrandTotalAmount' ), 'vat_lines' => $vat_lines,
			'vat_regime' => $fr ? ( 0.0 !== (float) $vat ? 'fr' : 'sans_tva' ) : 'autoliquidation_ue', 'nature' => 'services',
			'description' => $t( '//ram:IncludedSupplyChainTradeLineItem/ram:SpecifiedTradeProduct/ram:Name' ), 'category_code' => '', 'is_asset_candidate' => false,
			'anomalies' => array(), 'confidence' => 1.0, 'source' => 'facturx' );
	}

	// ------------------------------------------------------------ Claude
	private static function schema(): array {
		$s = array( 'type' => 'string' );
		return array( 'type' => 'object', 'additionalProperties' => false, 'properties' => array(
			'document_type' => array( 'type' => 'string', 'enum' => array( 'facture', 'avoir', 'ticket', 'note_de_frais', 'releve', 'autre' ) ),
			'supplier_name' => $s, 'supplier_country' => $s, 'supplier_vat_number' => $s, 'customer_name' => $s, 'invoice_number' => $s,
			'date' => array( 'type' => 'string', 'description' => 'AAAA-MM-JJ' ), 'currency' => $s,
			'total_ht' => array( 'type' => 'string', 'description' => 'Nombre décimal avec un point, ex. 100.00' ), 'total_vat' => $s, 'total_ttc' => $s,
			'vat_lines' => array( 'type' => 'array', 'items' => array( 'type' => 'object', 'additionalProperties' => false,
				'properties' => array( 'rate' => $s, 'base' => $s, 'vat' => $s ), 'required' => array( 'rate', 'base', 'vat' ) ) ),
			'vat_regime' => array( 'type' => 'string', 'enum' => array( 'fr', 'sans_tva', 'autoliquidation_ue', 'autoliquidation_hors_ue' ) ),
			'nature' => array( 'type' => 'string', 'enum' => array( 'services', 'biens' ) ), 'description' => $s, 'category_code' => $s,
			'is_asset_candidate' => array( 'type' => 'boolean' ), 'anomalies' => array( 'type' => 'array', 'items' => $s ),
			'confidence' => array( 'type' => 'number' ),
		), 'required' => array( 'document_type', 'supplier_name', 'supplier_country', 'supplier_vat_number', 'customer_name', 'invoice_number', 'date', 'currency',
			'total_ht', 'total_vat', 'total_ttc', 'vat_lines', 'vat_regime', 'nature', 'description', 'category_code', 'is_asset_candidate', 'anomalies', 'confidence' ) );
	}

	public static function extract_with_claude( string $content, string $mime ): array {
		$cats = array();
		foreach ( AC_DB::q( 'SELECT code, name FROM {categories} WHERE active=1' ) as $c ) {
			$cats[] = "{$c['code']} = {$c['name']}";
		}
		$company = AC_DB::get_setting( 'company' );
		$b64     = base64_encode( $content );
		if ( 'application/pdf' === $mime ) {
			$block = array( 'type' => 'document', 'source' => array( 'type' => 'base64', 'mediaType' => 'application/pdf', 'data' => $b64 ) );
		} elseif ( in_array( $mime, array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ), true ) ) {
			$block = array( 'type' => 'image', 'source' => array( 'type' => 'base64', 'mediaType' => $mime, 'data' => $b64 ) );
		} else {
			throw new AC_Error( 'Format non pris en charge (PDF, JPG, PNG).' );
		}
		$prompt = "Tu lis un justificatif comptable reçu par la société {$company['name']} ({$company['legal_form']}, France). "
			. 'Extrais les informations demandées en recopiant exactement les montants imprimés (nombres décimaux avec un point). '
			. "Si une information n'apparaît pas, laisse une chaîne vide ; n'invente rien. "
			. 'vat_regime : « fr » si la facture porte de la TVA française ; « autoliquidation_ue » pour un fournisseur établi dans un autre pays de l\'UE qui facture '
			. 'sans TVA (autoliquidation, reverse charge) ; « autoliquidation_hors_ue » pour un fournisseur hors UE qui facture sans TVA ; « sans_tva » pour une opération '
			. 'exonérée ou un ticket sans TVA. Choisis category_code parmi : ' . implode( '; ', $cats ) . '. '
			. 'is_asset_candidate : vrai pour un bien durable de plus de 500 € HT. anomalies : signale en français ce qui pose problème (destinataire autre que '
			. 'la société, TVA incohérente, document illisible, ticket sans TVA détaillée, devise étrangère, dépense d\'apparence personnelle). '
			. 'confidence : ta confiance globale dans l\'extraction.';
		$resp = self::call( array( 'maxTokens' => 4000, 'outputConfig' => array( 'effort' => 'medium', 'format' => array( 'type' => 'json_schema', 'schema' => self::schema() ) ),
			'messages' => array( array( 'role' => 'user', 'content' => array( $block, array( 'type' => 'text', 'text' => $prompt ) ) ) ) ) );
		foreach ( $resp->content as $b ) {
			if ( 'text' === $b->type ) {
				$data           = json_decode( $b->text, true ) ?: array();
				$data['source'] = 'ia';
				return $data;
			}
		}
		throw new AC_Error( "L'IA n'a pas renvoyé de résultat." );
	}

	// ------------------------------------------------------------ contrôles déterministes
	private static function c( $v ): ?int {
		try {
			return ( null === $v || '' === $v ) ? null : AC_Money::to_cents( (string) $v );
		} catch ( AC_Error $e ) {
			return null;
		}
	}

	public static function normalize( array $d ): array {
		$ht  = self::c( $d['total_ht'] ?? null );
		$tva = self::c( $d['total_vat'] ?? null );
		$ttc = self::c( $d['total_ttc'] ?? null );
		$an  = (array) ( $d['anomalies'] ?? array() );
		if ( null !== $ttc && null !== $ht && null === $tva ) {
			$tva = $ttc - $ht;
		}
		if ( null !== $ttc && null !== $tva && null === $ht ) {
			$ht = $ttc - $tva;
		}
		if ( null !== $ht && null !== $tva && null === $ttc ) {
			$ttc = $ht + $tva;
		}
		if ( null !== $ht && null !== $tva && null !== $ttc && $ht + $tva !== $ttc ) {
			$an[] = 'HT + TVA (' . AC_Money::fmt( $ht + $tva ) . ') ≠ TTC (' . AC_Money::fmt( $ttc ) . ').';
		}
		$rates = array_values( array_unique( array_filter( array_map( function ( $l ) { return AC_Money::rate_str( str_replace( '%', '', (string) ( $l['rate'] ?? '' ) ) ); },
			(array) ( $d['vat_lines'] ?? array() ) ), function ( $r ) { return '0' !== $r; } ) ) );
		$rate  = 1 === count( $rates ) ? $rates[0] : ( $rates ? 'multi' : '' );
		if ( $rate && 'multi' !== $rate && $ht && null !== $tva ) {
			$exp = AC_Money::vat_from_ht( $ht, $rate );
			if ( abs( $exp - $tva ) > max( 2, intdiv( $ht, 500 ) ) ) {
				$an[] = 'TVA ' . AC_Money::fmt( $tva ) . " incohérente avec le taux $rate % (attendu " . AC_Money::fmt( $exp ) . ').';
			}
		}
		if ( 'multi' === $rate ) {
			$an[] = 'Plusieurs taux de TVA : vérifiez la ventilation.';
		}
		$company = AC_DB::get_setting( 'company' );
		if ( ! empty( $d['customer_name'] ) && AC_Services::name_similarity( $d['customer_name'], $company['name'] ) < 0.5 ) {
			$an[] = "La facture est adressée à « {$d['customer_name']} » et non à la société : TVA non récupérable sans facture au nom de la société.";
		}
		if ( ! in_array( strtoupper( (string) ( $d['currency'] ?? 'EUR' ) ), array( 'EUR', '€', '' ), true ) ) {
			$an[] = "Facture en {$d['currency']} : vérifiez le montant débité en euros.";
		}
		$cat = AC_DB::q1( 'SELECT * FROM {categories} WHERE code=?', array( (string) ( $d['category_code'] ?? '' ) ) );
		if ( ! empty( $d['is_asset_candidate'] ) && $ht >= 50000 ) {
			$cat = AC_DB::q1( "SELECT * FROM {categories} WHERE code='immobilisation'" ) ?: $cat;
		}
		return array_merge( $d, array( 'ht_cents' => $ht, 'vat_cents' => $tva, 'ttc_cents' => $ttc,
			'vat_rate' => ( '' !== $rate && 'multi' !== $rate ) ? $rate : ( $tva ? '20' : '0' ),
			'category_id' => $cat ? $cat['id'] : null, 'category_name' => $cat ? $cat['name'] : null, 'anomalies' => $an ) );
	}

	public static function find_matches( array $ex ): array {
		if ( empty( $ex['ttc_cents'] ) ) {
			return array();
		}
		$sign = 'avoir' === ( $ex['document_type'] ?? '' ) ? 1 : -1;
		$d0   = preg_match( '/^\d{4}-\d\d-\d\d$/', (string) ( $ex['date'] ?? '' ) ) ? $ex['date'] : AC_DB::today();
		$out  = array();
		foreach ( AC_DB::q( 'SELECT * FROM {bank_transactions} WHERE amount_cents=? AND date BETWEEN DATE_SUB(?, INTERVAL 15 DAY) AND DATE_ADD(?, INTERVAL 45 DAY)',
			array( $sign * $ex['ttc_cents'], $d0, $d0 ) ) as $t ) {
			$linked = AC_DB::q1( "SELECT x.id, x.document_id FROM {allocations} a JOIN {expenses} x ON x.id=a.target_id WHERE a.target_type='depense' AND a.bank_transaction_id=?", array( $t['id'] ) );
			if ( $linked && $linked['document_id'] ) {
				continue;
			}
			$days  = abs( ( strtotime( $t['date'] ) - strtotime( $d0 ) ) / 86400 );
			$score = 0.5 + 0.35 * AC_Services::name_similarity( $t['counterparty'] ?: $t['label'], $ex['supplier_name'] ?? '' ) + ( $days <= 3 ? 0.15 : ( $days <= 10 ? 0.05 : 0 ) );
			$out[] = array( 'transaction' => $t, 'score' => round( min( $score, 1 ), 2 ), 'expense_id' => $linked ? $linked['id'] : null );
		}
		usort( $out, function ( $a, $b ) { return $b['score'] <=> $a['score']; } );
		return $out;
	}

	public static function expense_payload( array $ex, int $doc ): array {
		$p = array( 'type' => 'depense_nouvelle', 'supplier_name' => $ex['supplier_name'] ?? null, 'label' => ( $ex['description'] ?? '' ) ?: ( $ex['supplier_name'] ?? null ),
			'category_id' => $ex['category_id'] ?? null, 'vat_regime' => ( $ex['vat_regime'] ?? '' ) ?: 'fr', 'vat_rate' => $ex['vat_rate'] ?? null,
			'nature' => ( $ex['nature'] ?? '' ) ?: 'services', 'document_id' => $doc, 'invoice_number' => $ex['invoice_number'] ?? null,
			'comment' => implode( '; ', $ex['anomalies'] ?? array() ) ?: null );
		if ( null !== ( $ex['vat_cents'] ?? null ) && 'fr' === $p['vat_regime'] ) {
			$p['vat_cents'] = $ex['vat_cents'];
		}
		return $p;
	}

	/** Lit un justificatif, propose la dépense et la rapproche automatiquement si certain. */
	public static function process_document( int $doc_id ): array {
		$doc     = AC_DB::q1( 'SELECT * FROM {documents} WHERE id=?', array( $doc_id ) );
		$content = (string) file_get_contents( AC_Services::document_path( $doc ) );
		$raw     = null;
		if ( 'application/pdf' === $doc['mime'] && ( $xml = self::facturx_xml( $content ) ) ) {
			try {
				$raw = self::parse_cii( $xml );
			} catch ( Throwable $e ) {
				$raw = null;
			}
		}
		if ( ! $raw ) {
			if ( ! self::available() ) {
				AC_DB::exec( "UPDATE {documents} SET ai_status='manuel' WHERE id=?", array( $doc_id ) );
				return array( 'status' => 'manuel', 'message' => 'IA non configurée : complétez la dépense manuellement.', 'document_id' => $doc_id );
			}
			$raw = self::extract_with_claude( $content, $doc['mime'] );
		}
		$ex      = self::normalize( $raw );
		$matches = self::find_matches( $ex );
		AC_DB::exec( "UPDATE {documents} SET ai_status='extrait', ai_data=?, doc_date=?, party_name=?, amount_cents=? WHERE id=?", array(
			wp_json_encode( $ex, JSON_UNESCAPED_UNICODE ), preg_match( '/^\d{4}-\d\d-\d\d$/', (string) ( $ex['date'] ?? '' ) ) ? $ex['date'] : null,
			$ex['supplier_name'] ?? null, $ex['ttc_cents'], $doc_id ) );
		$th      = (float) AC_DB::get_setting( 'ai' )['auto_link_threshold'];
		$certain = $matches && $matches[0]['score'] >= $th && ( 1 === count( $matches ) || $matches[1]['score'] < $matches[0]['score'] - 0.2 )
			&& (float) ( $ex['confidence'] ?? 0 ) >= $th && ! $ex['anomalies'] && in_array( $ex['document_type'] ?? '', array( 'facture', 'ticket', 'note_de_frais' ), true );
		$res = array( 'status' => 'a_confirmer', 'extraction' => $ex, 'matches' => array_slice( $matches, 0, 5 ), 'document_id' => $doc_id );
		if ( $certain ) {
			$m = $matches[0];
			if ( $m['expense_id'] ) {
				AC_Services::save_expense( array( 'document_id' => $doc_id, 'invoice_number' => $ex['invoice_number'] ?? null, 'status' => 'propose_ia' ), $m['expense_id'], 'Justificatif rattaché automatiquement' );
				$res['expense_id'] = $m['expense_id'];
			} else {
				$r = AC_Services::classify_transaction( $m['transaction']['id'], self::expense_payload( $ex, $doc_id ), 'Rapprochement automatique justificatif' );
				AC_DB::exec( "UPDATE {expenses} SET status='propose_ia', source='ia' WHERE id=?", array( $r['id'] ) );
				$res['expense_id'] = $r['id'];
			}
			$res['status']         = 'rattache';
			$res['transaction_id'] = $m['transaction']['id'];
		}
		return $res;
	}

	// ------------------------------------------------------------ assistant
	private static function tools_impl(): array {
		$fy = function () { return AC_Closing::fiscal_year(); };
		return array(
			'depenses' => function ( $in ) {
				list( $s, $e ) = AC_Checks::month_bounds( ( $in['month'] ?? '' ) ?: substr( AC_DB::today(), 0, 7 ) );
				$rows = AC_DB::q( "SELECT x.date, x.supplier_name, x.label, x.ht_cents, x.vat_cents, x.ttc_cents, c.name categorie FROM {expenses} x
					LEFT JOIN {categories} c ON c.id=x.category_id WHERE x.date BETWEEN ? AND ? AND IFNULL(c.rule,'')<>'personnel'", array( $s, $e ) );
				if ( ! empty( $in['category'] ) ) {
					$rows = array_values( array_filter( $rows, function ( $r ) use ( $in ) { return false !== stripos( (string) $r['categorie'], $in['category'] ); } ) );
				}
				return array( 'periode' => array( $s, $e ), 'nombre' => count( $rows ), 'total_ttc_cents' => array_sum( array_column( $rows, 'ttc_cents' ) ),
					'total_ht_cents' => array_sum( array_column( $rows, 'ht_cents' ) ), 'detail' => array_slice( $rows, 0, 50 ) );
			},
			'compte_courant_associe' => function () {
				$a = AC_Services::associate_summary();
				unset( $a['mouvements'] );
				return $a;
			},
			'tva' => function ( $in ) {
				if ( ! empty( $in['start'] ) && ! empty( $in['end'] ) ) {
					$d = AC_Vat::compute( $in['start'], $in['end'] );
					return array( 'periode' => array( $in['start'], $in['end'] ), 'tva_brute_cents' => $d['tva_brute_cents'], 'tva_deductible_cents' => $d['tva_deductible_cents'],
						'a_payer_cents' => $d['a_payer_cents'], 'credit_cents' => $d['credit_cents'] );
				}
				return array( 'periode_en_cours' => AC_Vat::current_estimate(),
					'tva_reglee_au_tresor_cents' => -AC_DB::ival( "SELECT COALESCE(SUM(amount_cents),0) FROM {allocations} WHERE target_type='tva'" ) );
			},
			'resultat' => function () use ( $fy ) {
				$f   = $fy();
				$inc = AC_Reports::income_statement( $f['start_date'], $f['end_date'] );
				return array( 'exercice' => array( $f['start_date'], $f['end_date'] ), 'produits_cents' => $inc['total_produits'], 'charges_cents' => $inc['total_charges'],
					'resultat_cents' => $inc['resultat_net'], 'chiffre_affaires_cents' => $inc['chiffre_affaires'], 'is_estime_cents' => AC_Tax::estimate( $f )['is_estime_cents'] );
			},
			'points_a_traiter' => function () use ( $fy ) {
				$f      = $fy();
				$issues = AC_Checks::period_checks( $f['start_date'], $f['end_date'] );
				return array( 'nombre' => count( $issues ), 'points' => array_map( function ( $i ) { return array( 'niveau' => $i['niveau'], 'message' => $i['message'] ); },
					array_slice( $issues, 0, 40 ) ) );
			},
			'tresorerie' => function () {
				return array( 'solde_comptable_banque_cents' => AC_Ledger::balance( '512' ), 'qonto_cents' => AC_DB::get_setting( 'qonto' )['balance_cents'] ?? null );
			},
		);
	}

	const TOOLS = array(
		array( 'name' => 'depenses', 'description' => "Dépenses professionnelles d'un mois (AAAA-MM), éventuellement filtrées par catégorie. Montants en centimes.",
			'inputSchema' => array( 'type' => 'object', 'properties' => array( 'month' => array( 'type' => 'string' ), 'category' => array( 'type' => 'string' ) ) ) ),
		array( 'name' => 'compte_courant_associe', 'description' => "Apports, remboursements et solde du compte courant d'associé (centimes). Solde positif : la société doit à l'associé.",
			'inputSchema' => array( 'type' => 'object', 'properties' => array() ) ),
		array( 'name' => 'tva', 'description' => "TVA d'une période (start, end au format AAAA-MM-JJ) ou de la période en cours, et TVA déjà réglée.",
			'inputSchema' => array( 'type' => 'object', 'properties' => array( 'start' => array( 'type' => 'string' ), 'end' => array( 'type' => 'string' ) ) ) ),
		array( 'name' => 'resultat', 'description' => "Résultat comptable provisoire de l'exercice et IS estimé (centimes).",
			'inputSchema' => array( 'type' => 'object', 'properties' => array() ) ),
		array( 'name' => 'points_a_traiter', 'description' => "Justificatifs manquants, opérations à catégoriser, anomalies de l'exercice.",
			'inputSchema' => array( 'type' => 'object', 'properties' => array() ) ),
		array( 'name' => 'tresorerie', 'description' => 'Solde bancaire.', 'inputSchema' => array( 'type' => 'object', 'properties' => array() ) ),
	);

	public static function ask( string $question ): array {
		$tools = self::tools_impl();
		if ( ! self::available() ) {
			return self::local_answer( $question, $tools );
		}
		$company  = AC_DB::get_setting( 'company' );
		$system   = "Tu es l'assistant comptable de la SASU {$company['name']}. Réponds en français, simplement, pour un dirigeant non comptable. "
			. 'Utilise uniquement les données renvoyées par les outils (montants en centimes à convertir en euros). Explique brièvement le calcul et sa source. '
			. "Si une donnée manque, dis le : n'invente jamais de chiffre. Tu ne peux rien modifier : tu peux proposer une écriture ou une catégorie, l'utilisateur la valide. "
			. 'Aujourd\'hui : ' . AC_DB::today() . '.';
		$messages = array( array( 'role' => 'user', 'content' => $question ) );
		$used     = array();
		for ( $i = 0; $i < 8; $i++ ) {
			$resp = self::call( array( 'maxTokens' => 4000, 'system' => $system, 'tools' => self::TOOLS, 'messages' => $messages, 'outputConfig' => array( 'effort' => 'medium' ) ) );
			if ( 'tool_use' !== $resp->stopReason ) {
				$text = '';
				foreach ( $resp->content as $b ) {
					$text .= 'text' === $b->type ? $b->text : '';
				}
				return array( 'answer' => $text, 'tools' => $used, 'mode' => 'ia' );
			}
			$messages[] = array( 'role' => 'assistant', 'content' => $resp->content );
			$results    = array();
			foreach ( $resp->content as $b ) {
				if ( 'tool_use' === $b->type ) {
					try {
						$out       = ( $tools[ $b->name ] )( (array) $b->input );
						$results[] = array( 'type' => 'tool_result', 'toolUseID' => $b->id, 'content' => wp_json_encode( $out, JSON_UNESCAPED_UNICODE ) );
					} catch ( Throwable $e ) {
						$results[] = array( 'type' => 'tool_result', 'toolUseID' => $b->id, 'content' => $e->getMessage(), 'isError' => true );
					}
					$used[] = $b->name;
				}
			}
			$messages[] = array( 'role' => 'user', 'content' => $results );
		}
		return array( 'answer' => "Je n'ai pas pu terminer l'analyse.", 'tools' => $used, 'mode' => 'ia' );
	}

	/** Réponses sans IA aux questions courantes (calculs déterministes). */
	public static function local_answer( string $question, array $tools ): array {
		$q = AC_Services::norm_name( $question );
		$f = array( 'AC_Money', 'fmt' );
		$has = function ( array $words ) use ( $q ) {
			foreach ( $words as $w ) {
				if ( false !== strpos( $q, $w ) ) {
					return true;
				}
			}
			return false;
		};
		if ( $has( array( 'INJECTE', 'APPORT', 'DOIT', 'COMPTE COURANT', 'REMBOURS' ) ) ) {
			$a   = $tools['compte_courant_associe']();
			$txt = 'Vous avez apporté ' . $f( $a['apports_cents'] ) . ' par virement et avancé ' . $f( $a['avances_frais_cents'] ) . ' de frais payés personnellement, soit '
				. $f( $a['total_injecte_cents'] ) . '. La société vous a remboursé ' . $f( $a['remboursements_cents'] ) . '. Solde du compte courant : '
				. $f( abs( $a['solde_cents'] ) ) . ", {$a['sens']}. (Source : compte 455 du grand livre.)";
		} elseif ( $has( array( 'TVA' ) ) ) {
			$t   = $tools['tva']( array() );
			$p   = $t['periode_en_cours'];
			$txt = 'Période ' . ( $p['periode'] ?? '' ) . ' : TVA estimée à payer ' . $f( $p['a_payer_cents'] ?? 0 ) . ( ! empty( $p['credit_cents'] ) ? ', crédit ' . $f( $p['credit_cents'] ) : '' )
				. '. TVA déjà réglée au Trésor : ' . $f( $t['tva_reglee_au_tresor_cents'] ) . '. (Source : comptes 4457, 4456 et 4452.)';
		} elseif ( $has( array( 'DEPENSE' ) ) ) {
			$d   = $tools['depenses']( array() );
			$txt = "Dépenses du {$d['periode'][0]} au {$d['periode'][1]} : {$d['nombre']} dépense(s), " . $f( $d['total_ttc_cents'] ) . ' TTC (' . $f( $d['total_ht_cents'] ) . ' HT).';
		} elseif ( $has( array( 'RESULTAT', 'BENEFICE', 'PERTE' ) ) ) {
			$r   = $tools['resultat']();
			$txt = "Résultat provisoire de l'exercice : " . $f( $r['resultat_cents'] ) . ' (produits ' . $f( $r['produits_cents'] ) . ', charges ' . $f( $r['charges_cents'] )
				. '). IS estimé : ' . $f( $r['is_estime_cents'] ) . '.';
		} elseif ( $has( array( 'MANQU', 'CATEGORIS', 'A FAIRE', 'VERIFIER', 'JUSTIFICATIF' ) ) ) {
			$a   = $tools['points_a_traiter']();
			$txt = "{$a['nombre']} point(s) à traiter :\n" . implode( "\n", array_map( function ( $p ) { return '• ' . $p['message']; }, array_slice( $a['points'], 0, 15 ) ) );
		} elseif ( $has( array( 'SOLDE', 'TRESORERIE', 'BANQUE' ) ) ) {
			$txt = 'Solde bancaire comptable : ' . $f( $tools['tresorerie']()['solde_comptable_banque_cents'] ) . '.';
		} else {
			$txt = 'Sans IA configurée, je réponds aux questions sur : apports et compte courant, TVA, dépenses du mois, résultat, points à traiter, solde bancaire.';
		}
		return array( 'answer' => $txt, 'tools' => array(), 'mode' => 'local' );
	}
}

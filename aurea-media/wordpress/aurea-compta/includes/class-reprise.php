<?php
/**
 * Reprise d'un dossier tenu ailleurs (Dougs) : chaque opération du fichier de reprise est retrouvée
 * dans les opérations Qonto (date, montant, libellé) puis catégorisée comme dans l'ancien outil.
 * Les opérations déjà traitées ne sont jamais modifiées. Les trajets deviennent des frais kilométriques.
 */
defined( 'ABSPATH' ) || exit;

class AC_Reprise {

	const FORMAT = 'aurea-compta-reprise';

	public static function check_file( $data ): array {
		if ( ! is_array( $data ) || ( $data['format'] ?? '' ) !== self::FORMAT || ! isset( $data['operations'] ) || ! is_array( $data['operations'] ) ) {
			throw new AC_Error( "Ce fichier n'est pas un fichier de reprise Aurea Compta." );
		}
		return $data;
	}

	/** Opération Qonto correspondante : même montant, date à 4 jours près, libellé le plus proche. */
	public static function match( array $op, array $used ): ?array {
		$amt  = (int) $op['amount_cents'];
		$d    = $op['date'];
		$best = null;
		$rows = AC_DB::q( "SELECT * FROM {bank_transactions} WHERE amount_cents=? AND date BETWEEN DATE_SUB(?, INTERVAL 4 DAY) AND DATE_ADD(?, INTERVAL 4 DAY) AND status<>'ignore'",
			array( $amt, $d, $d ) );
		foreach ( $rows as $t ) {
			$c = (string) $t['comment'];
			if ( isset( $used[ $t['id'] ] ) || ( false !== strpos( $c, 'Dougs #' ) && false === strpos( $c, 'Dougs #' . $op['ref'] . ' ' ) && $c !== 'Dougs #' . $op['ref'] ) ) {
				continue; // déjà rapprochée d'une autre opération Dougs
			}
			$days  = abs( ( strtotime( $t['date'] ) - strtotime( $d ) ) / 86400 );
			$score = AC_Services::name_similarity( $t['counterparty'] ?: $t['label'], $op['label'] ?? '' ) - $days * 0.1;
			if ( false !== strpos( $c, 'Dougs #' . $op['ref'] ) ) {
				$score += 10; // déjà rapprochée de cette opération lors d'un passage précédent
			} elseif ( in_array( $t['status'], array( 'a_traiter', 'propose' ), true ) ) {
				$score += 1;
			}
			if ( ! $best || $score > $best[0] ) {
				$best = array( $score, $t );
			}
		}
		return $best ? $best[1] : null;
	}

	private static function category_id( string $code ): int {
		$c = AC_DB::q1( 'SELECT id FROM {categories} WHERE code=?', array( $code ) );
		if ( ! $c ) {
			throw new AC_Error( "Catégorie inconnue : $code" );
		}
		return (int) $c['id'];
	}

	private static function validate_sources( array $sources ): void {
		foreach ( $sources as list( $type, $id ) ) {
			foreach ( AC_Ledger::source_entries( $type, (int) $id ) as $e ) {
				AC_Ledger::validate_entry( (int) $e['id'] );
			}
		}
	}

	/**
	 * Traite le fichier à partir de $offset, dans la limite de $budget secondes (hébergement mutualisé).
	 * $dry : simple contrôle, rien n'est écrit.
	 */
	public static function run( array $data, int $offset = 0, bool $dry = false, bool $validate = true, float $budget = 20.0 ): array {
		self::check_file( $data );
		$start = microtime( true );
		$ops   = array_values( $data['operations'] );
		$res   = array( 'total' => count( $ops ), 'next_offset' => null, 'classees' => 0, 'deja_traitees' => 0, 'a_verifier' => array(), 'introuvables' => array(),
			'erreurs' => array(), 'trajets' => 0, 'regles' => 0 );
		$used  = array();
		$fail  = function ( $op, $msg ) use ( &$res ) {
			$res['erreurs'][] = array( 'ref' => $op['ref'], 'date' => $op['date'], 'amount_cents' => (int) $op['amount_cents'], 'label' => $op['label'] ?? '', 'message' => $msg );
		};
		for ( $i = $offset; $i < count( $ops ); $i++ ) {
			if ( microtime( true ) - $start > $budget ) {
				$res['next_offset'] = $i;
				return $res;
			}
			$op = $ops[ $i ];
			$a  = $op['action'] ?? array();
			$tx = self::match( $op, $used );
			if ( ! $tx ) {
				$res['introuvables'][] = array( 'ref' => $op['ref'], 'date' => $op['date'], 'amount_cents' => (int) $op['amount_cents'], 'label' => $op['label'] ?? '', 'memo' => $op['memo'] ?? '' );
				continue;
			}
			$used[ $tx['id'] ] = true;
			$note = 'Dougs #' . $op['ref'] . ( ! empty( $op['memo'] ) ? ' · ' . $op['memo'] : '' );
			if ( 'verifier' === ( $a['type'] ?? '' ) ) {
				$res['a_verifier'][] = array( 'id' => (int) $tx['id'], 'date' => $tx['date'], 'amount_cents' => (int) $tx['amount_cents'], 'label' => $op['label'] ?? '',
					'reason' => $a['reason'] ?? '', 'dougs_category' => $op['dougs_category'] ?? '' );
				if ( ! $dry && in_array( $tx['status'], array( 'a_traiter', 'propose' ), true ) ) {
					AC_DB::exec( 'UPDATE {bank_transactions} SET comment=? WHERE id=?', array( mb_substr( $note . ' · À vérifier : ' . ( $a['reason'] ?? '' ), 0, 1000 ), $tx['id'] ) );
				}
				continue;
			}
			if ( ! in_array( $tx['status'], array( 'a_traiter', 'propose' ), true ) ) {
				$res['deja_traitees']++;
				continue;
			}
			if ( $dry ) {
				$res['classees']++;
				continue;
			}
			try {
				AC_DB::tx( function () use ( $op, $a, $tx, $note, $validate, &$res ) {
					$id      = (int) $tx['id'];
					$sources = array( array( 'banque', $id ) );
					switch ( $a['type'] ) {
						case 'depense_nouvelle':
						case 'remboursement_fournisseur':
							$cat = self::category_id( $a['category'] );
							$p   = array( 'type' => $a['type'], 'category_id' => $cat, 'vat_regime' => $a['vat_regime'] ?? 'fr', 'vat_rate' => $a['vat_rate'] ?? null,
								'label' => $a['label'] ?? null, 'comment' => $note, 'validate' => $validate );
							if ( isset( $a['vat_cents'] ) && 'sans_tva' !== $p['vat_regime'] ) {
								$p['vat_cents'] = (int) $a['vat_cents'];
							} elseif ( 'sans_tva' === $p['vat_regime'] ) {
								$p['vat_cents'] = 0;
							}
							AC_Services::classify_transaction( $id, $p, 'Reprise Dougs' );
							if ( ! empty( $a['remember'] ) && $tx['amount_cents'] < 0 ) {
								AC_Services::remember_rule( $tx['counterparty'] ?: $tx['label'], $cat, $p['vat_regime'], $p['vat_rate'], true );
								$res['regles']++;
							}
							break;
						case 'vente':
							$gross = (int) $a['gross_cents'];
							$vat   = (int) ( $a['vat_cents'] ?? 0 );
							$inv   = AC_DB::q1( 'SELECT id FROM {sales_invoices} WHERE external_id=?', array( 'dougs:' . $op['ref'] ) );
							if ( $inv ) {
								$inv_id = (int) $inv['id'];
							} else {
								$saved  = AC_Services::save_sales_invoice( array( 'number' => 'DOUGS-' . $op['ref'], 'issue_date' => $tx['date'], 'client_name' => $a['client'] ?? 'Client',
									'vat_regime' => $a['vat_regime'] ?? 'fr', 'nature' => 'services',
									'notes' => 'Encaissement repris de Dougs : facture émise hors du logiciel. ' . ( $a['description'] ?? '' ),
									'lines' => array( array( 'description' => mb_substr( ( $a['description'] ?? '' ) ?: 'Prestation de services', 0, 250 ), 'quantity' => '1',
										'unit_price_cents' => $gross - $vat, 'vat_rate' => $a['vat_rate'] ?? '20' ) ) ), null, 'Reprise Dougs' );
								$inv_id = (int) $saved['id'];
								AC_DB::exec( 'UPDATE {sales_invoices} SET external_id=? WHERE id=?', array( 'dougs:' . $op['ref'], $inv_id ) );
							}
							$p = array( 'type' => 'vente', 'invoice_id' => $inv_id, 'comment' => $note );
							if ( ! empty( $a['commission_cents'] ) ) {
								$p['commission_cents'] = (int) $a['commission_cents'];
								$p['provider']         = $a['provider'] ?? null;
							}
							AC_Services::classify_transaction( $id, $p, 'Reprise Dougs' );
							$sources[] = array( 'vente', $inv_id );
							foreach ( AC_DB::q( "SELECT target_id FROM {allocations} WHERE bank_transaction_id=? AND target_type='depense'", array( $id ) ) as $al ) {
								if ( $validate ) {
									AC_Services::save_expense( array( 'status' => 'valide' ), (int) $al['target_id'], 'Reprise Dougs' );
								}
								$sources[] = array( 'depense', (int) $al['target_id'] );
							}
							break;
						case 'associe':
						case 'capital':
						case 'tva':
							AC_Services::classify_transaction( $id, array( 'type' => $a['type'], 'comment' => $note ), 'Reprise Dougs' );
							break;
						default:
							throw new AC_Error( "Type d'opération inconnu : " . ( $a['type'] ?? '' ) );
					}
					if ( $validate ) {
						self::validate_sources( $sources );
					}
				} );
				$res['classees']++;
			} catch ( Throwable $e ) {
				$fail( $op, $e->getMessage() );
			}
		}
		if ( ! $dry ) {
			$res['trajets'] = self::import_trips( $data['trips'] ?? array(), $validate );
		} else {
			$res['trajets'] = count( $data['trips'] ?? array() );
		}
		return $res;
	}

	/** Trajets repris (motif, distance) : le montant est recalculé au barème, cumul annuel compris. */
	public static function import_trips( array $trips, bool $validate ): int {
		$n = 0;
		usort( $trips, function ( $a, $b ) { return strcmp( $a['date'], $b['date'] ); } );
		foreach ( $trips as $t ) {
			$purpose = mb_substr( ( $t['purpose'] ?? '' ) ?: 'Déplacement professionnel', 0, 250 );
			if ( AC_DB::q1( 'SELECT id FROM {mileage} WHERE date=? AND purpose=?', array( $t['date'], $purpose ) ) ) {
				continue;
			}
			$r = AC_Mileage::add( array( 'date' => $t['date'], 'from_place' => 'Siège (reprise Dougs)', 'to_place' => 'Rendez-vous professionnel', 'purpose' => $purpose,
				'km' => (string) $t['km'], 'round_trip' => false ) );
			if ( $validate ) {
				AC_Services::save_expense( array( 'status' => 'valide' ), (int) $r['expense_id'], 'Reprise Dougs' );
				self::validate_sources( array( array( 'depense', (int) $r['expense_id'] ) ) );
			}
			$n++;
		}
		return $n;
	}
}

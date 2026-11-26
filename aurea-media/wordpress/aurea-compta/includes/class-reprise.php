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
	public static function run( array $data, int $offset = 0, bool $dry = false, bool $validate = true, float $budget = 20.0, array $align = array() ): array {
		self::check_file( $data );
		$start = microtime( true );
		$ops   = array_values( $data['operations'] );
		$res   = array( 'total' => count( $ops ), 'next_offset' => null, 'classees' => 0, 'deja_traitees' => 0, 'a_verifier' => array(), 'introuvables' => array(),
			'erreurs' => array(), 'trajets' => 0, 'regles' => 0, 'corrigees' => 0, 'differences' => array(), 'alignees' => 0 );
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
				// Déjà reprise : on corrige la TVA si le fichier de reprise a été amélioré depuis (tickets à plusieurs taux, etc.).
				$fix = self::vat_fix( $op, $a, $tx );
				if ( $fix ) {
					if ( ! $dry ) {
						try {
							AC_DB::tx( function () use ( $fix, $validate, $tx ) {
								AC_Services::save_expense( $fix['data'], $fix['id'], 'Reprise Dougs : TVA corrigée' );
								if ( $validate && 'valide' === $fix['status'] ) {
									self::validate_sources( array( array( 'depense', $fix['id'] ), array( 'banque', (int) $tx['id'] ) ) );
								}
							} );
						} catch ( Throwable $e ) {
							$fail( $op, $e->getMessage() );
							continue;
						}
					}
					$res['corrigees'] = ( $res['corrigees'] ?? 0 ) + 1;
					continue;
				}
				// Classement différent de l'ancien outil : signalé, réaligné si l'utilisateur l'a demandé pour cette opération.
				$diff = self::classification_diff( $a, $tx );
				if ( $diff ) {
					if ( ! $dry && in_array( (string) $op['ref'], array_map( 'strval', $align ), true ) ) {
						try {
							AC_DB::tx( function () use ( $op, $a, $tx, $note, $validate, &$res ) {
								self::realign( $op, $a, $tx, $note, $validate, $res );
							} );
							$res['alignees']++;
						} catch ( Throwable $e ) {
							$fail( $op, $e->getMessage() );
						}
						continue;
					}
					$res['differences'][] = array( 'ref' => $op['ref'], 'id' => (int) $tx['id'], 'date' => $tx['date'], 'amount_cents' => (int) $tx['amount_cents'],
						'label' => $op['label'] ?? '', 'memo' => $op['memo'] ?? '', 'app' => $diff['app'], 'dougs' => $diff['dougs'] );
				}
				$res['deja_traitees']++;
				continue;
			}
			if ( $dry ) {
				$res['classees']++;
				continue;
			}
			try {
				AC_DB::tx( function () use ( $op, $a, $tx, $note, $validate, &$res ) {
					self::apply( $op, $a, $tx, $note, $validate, $res );
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

	const LABELS = array( 'vente' => 'Encaissement client', 'associe' => "Compte courant d'associé", 'capital' => 'Capital', 'tva' => 'Paiement de TVA',
		'is' => 'Impôt sur les sociétés', 'virement_interne' => 'Virement interne' );

	private static function category_name( ?string $code ): string {
		return (string) AC_DB::val( 'SELECT name FROM {categories} WHERE code=?', array( (string) $code ) );
	}

	/** Compare le classement actuel de l'opération avec celui de l'ancien outil. Null si identique. */
	public static function classification_diff( array $a, array $tx ): ?array {
		$t = $a['type'] ?? '';
		if ( in_array( $t, array( '', 'verifier' ), true ) ) {
			return null;
		}
		$al    = AC_DB::q( "SELECT a.target_type, a.account, c.code, c.name FROM {allocations} a LEFT JOIN {expenses} x ON a.target_type='depense' AND x.id=a.target_id
			LEFT JOIN {categories} c ON c.id=x.category_id WHERE a.bank_transaction_id=? ORDER BY a.id", array( $tx['id'] ) );
		$types = array_values( array_unique( array_column( $al, 'target_type' ) ) );
		$ok    = false;
		if ( in_array( $t, array( 'depense_nouvelle', 'remboursement_fournisseur' ), true ) ) {
			$ok     = array( 'depense' ) === $types && ( $al[0]['code'] ?? '' ) === ( $a['category'] ?? '' );
			$expect = ( 'remboursement_fournisseur' === $t ? 'Remboursement reçu' : 'Dépense' ) . ' · ' . self::category_name( $a['category'] ?? '' );
		} elseif ( 'vente' === $t ) {
			$ok     = in_array( 'vente', $types, true );
			$expect = self::LABELS['vente'];
		} elseif ( 'compte' === $t ) {
			$ok     = array( 'compte' ) === $types && ( $al[0]['account'] ?? '' ) === (string) $a['account'];
			$expect = 'Compte ' . $a['account'] . ( '445660' === (string) $a['account'] ? ' (TVA déductible)' : '' );
		} else {
			$ok     = array( $t ) === $types;
			$expect = self::LABELS[ $t ] ?? $t;
		}
		if ( $ok ) {
			return null;
		}
		$now = array();
		foreach ( $al as $x ) {
			$now[] = 'depense' === $x['target_type'] ? ( $tx['amount_cents'] > 0 ? 'Remboursement reçu' : 'Dépense' ) . ' · ' . ( $x['name'] ?: 'sans catégorie' )
				: ( 'compte' === $x['target_type'] ? 'Compte ' . $x['account'] : ( self::LABELS[ $x['target_type'] ] ?? $x['target_type'] ) );
		}
		return array( 'app' => $now ? implode( ', ', array_unique( $now ) ) : 'Non catégorisée', 'dougs' => $expect );
	}

	/** Réaligne une opération déjà traitée sur le classement de l'ancien outil (à la demande de l'utilisateur ; tout est tracé). */
	private static function realign( array $op, array $a, array $tx, string $note, bool $validate, array &$res ): void {
		$id  = (int) $tx['id'];
		$own = AC_DB::q( "SELECT x.* FROM {allocations} al JOIN {expenses} x ON x.id=al.target_id WHERE al.target_type='depense' AND al.bank_transaction_id=?
			AND x.source IN ('qonto','banque','ia') AND x.payment_method='qonto'", array( $id ) );
		$dep = in_array( $a['type'], array( 'depense_nouvelle', 'remboursement_fournisseur' ), true );
		if ( $dep && 1 === count( $own ) && array( 'depense' ) === array_values( array_unique( array_column( AC_DB::q( 'SELECT target_type FROM {allocations} WHERE bank_transaction_id=?', array( $id ) ), 'target_type' ) ) ) ) {
			// Même nature, autre catégorie : la dépense est modifiée (correction tracée, écritures contre passées puis revalidées).
			$x    = $own[0];
			$data = array( 'category_id' => self::category_id( $a['category'] ), 'vat_regime' => $a['vat_regime'] ?? 'fr', 'vat_rate' => $a['vat_rate'] ?? null,
				'ttc_cents' => (int) $x['ttc_cents'], 'vat_cents' => 'sans_tva' === ( $a['vat_regime'] ?? 'fr' ) ? 0 : ( $x['ttc_cents'] < 0 ? -1 : 1 ) * abs( (int) ( $a['vat_cents'] ?? 0 ) ) );
			AC_Services::save_expense( $data, (int) $x['id'], 'Alignement sur Dougs' );
			if ( $validate && 'valide' === $x['status'] ) {
				self::validate_sources( array( array( 'depense', (int) $x['id'] ), array( 'banque', $id ) ) );
			}
			return;
		}
		// Autre nature : la dépense créée depuis cette opération est annulée (contre passation de ses écritures), puis l'opération est reclassée.
		foreach ( $own as $x ) {
			if ( 'valide' === $x['status'] ) {
				AC_Services::save_expense( array( 'status' => 'a_valider' ), (int) $x['id'], 'Alignement sur Dougs' );
			}
			AC_Services::delete_expense( (int) $x['id'], 'Alignement sur Dougs : opération reclassée' );
		}
		self::apply( $op, $a, AC_DB::q1( 'SELECT * FROM {bank_transactions} WHERE id=?', array( $id ) ), $note, $validate, $res );
	}

	/** Applique à l'opération bancaire la catégorisation du fichier de reprise. */
	private static function apply( array $op, array $a, array $tx, string $note, bool $validate, array &$res ): void {
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
			case 'compte':
				AC_Services::classify_transaction( $id, array( 'type' => 'compte', 'account' => (string) $a['account'], 'comment' => $note ), 'Reprise Dougs' );
				break;
			default:
				throw new AC_Error( "Type d'opération inconnu : " . ( $a['type'] ?? '' ) );
		}
		if ( $validate ) {
			self::validate_sources( $sources );
		}
	}

	/** Dépense issue de la reprise dont la TVA diffère du fichier : données de correction, sinon null. */
	private static function vat_fix( array $op, array $a, array $tx ): ?array {
		if ( ! in_array( $a['type'] ?? '', array( 'depense_nouvelle', 'remboursement_fournisseur' ), true ) || false === strpos( (string) $tx['comment'], 'Dougs #' . $op['ref'] ) ) {
			return null;
		}
		$x = AC_DB::q1( "SELECT x.* FROM {expenses} x JOIN {allocations} al ON al.target_type='depense' AND al.target_id=x.id WHERE al.bank_transaction_id=? ORDER BY x.id LIMIT 1", array( $tx['id'] ) );
		if ( ! $x ) {
			return null;
		}
		$regime = $a['vat_regime'] ?? 'fr';
		$vat    = 'sans_tva' === $regime ? 0 : (int) ( $a['vat_cents'] ?? 0 );
		if ( 'remboursement_fournisseur' === $a['type'] ) {
			$vat = -abs( $vat );
		}
		if ( (int) $x['vat_cents'] === $vat && $x['vat_regime'] === $regime ) {
			return null;
		}
		return array( 'id' => (int) $x['id'], 'status' => $x['status'], 'data' => array( 'vat_regime' => $regime, 'vat_rate' => $a['vat_rate'] ?? null,
			'vat_cents' => $vat, 'ttc_cents' => (int) $x['ttc_cents'] ) );
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

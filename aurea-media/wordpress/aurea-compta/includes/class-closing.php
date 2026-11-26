<?php
/**
 * Clôture annuelle : contrôles, inventaire, IS, états, obligations, à nouveaux, affectation.
 */
defined( 'ABSPATH' ) || exit;

class AC_Closing {

	public static function fiscal_year( $id = null ): array {
		if ( $id ) {
			$fy = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE id=?', array( (int) $id ) );
			if ( $fy ) {
				return $fy;
			}
		}
		$t = AC_DB::today();
		return AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE start_date<=? AND end_date>=?', array( $t, $t ) )
			?: AC_DB::q1( "SELECT * FROM {fiscal_years} WHERE status='open' ORDER BY start_date LIMIT 1" )
			?: AC_DB::q1( 'SELECT * FROM {fiscal_years} ORDER BY start_date DESC LIMIT 1' );
	}

	public static function obligations( array $fy, array $inc, array $bs ): array {
		$taxs  = AC_DB::get_setting( 'tax' );
		$micro = AC_Rules::get( 'micro_seuils', $fy['end_date'] );
		$is_micro = $bs['total_actif'] <= $micro['bilan_cents'] && $inc['chiffre_affaires'] <= $micro['ca_cents'] && (int) $taxs['salaried_count'] <= $micro['salaries'];
		$rsi   = 'reel_simplifie' === $taxs['is_regime'];
		$appr  = ( new DateTime( $fy['end_date'] ) )->modify( '+6 month' )->format( 'd/m/Y' );
		$items = array(
			array( 'obligation' => 'Déclaration de résultat n°2065-SD et annexes ' . ( $rsi ? '2033-A à 2033-G (régime simplifié)' : '2050 à 2059 (régime normal)' ),
				'echeance' => '2e jour ouvré suivant le 1er mai pour un exercice civil, sinon 3 mois après la clôture',
				'note' => "Télétransmission obligatoire (EDI-TDFC via un partenaire ou l'expert comptable, ou mode EFI sur l'espace professionnel lorsqu'il est ouvert à votre situation)." ),
			array( 'obligation' => "Relevé de solde d'IS n°2572-SD et paiement du solde", 'echeance' => '15 du 4e mois suivant la clôture', 'note' => '' ),
			array( 'obligation' => 'Comptes annuels : bilan, compte de résultat' . ( $is_micro ? '' : ', annexe' ), 'echeance' => 'Établis par le président à la clôture',
				'note' => $is_micro ? "Petite société sous les seuils micro (sens comptable) : dispense d'annexe." : 'Annexe obligatoire (société au delà des seuils micro).' ),
			array( 'obligation' => "Approbation des comptes par l'associé unique et décision d'affectation du résultat", 'echeance' => "Dans les 6 mois de la clôture ($appr)",
				'note' => "Lorsque l'associé unique personne physique est lui même président, le dépôt au greffe de l'inventaire et des comptes annuels signés dans ce délai vaut approbation (C. com. L227-9). La décision est répertoriée dans le registre des décisions." ),
			array( 'obligation' => 'Dépôt des comptes annuels au greffe (Guichet unique INPI)', 'echeance' => "1 mois après l'approbation (2 mois par voie électronique)",
				'note' => $is_micro ? 'Déclaration de confidentialité possible : société sous les seuils micro au sens comptable (C. com. L232-25).' : 'Confidentialité du compte de résultat possible pour les petites entreprises.' ),
			array( 'obligation' => 'Rapport de gestion', 'echeance' => '-', 'note' => 'Dispense pour les petites entreprises (C. com. L232-1 IV).' ),
			array( 'obligation' => 'Fichier des écritures comptables (FEC)', 'echeance' => 'À produire en cas de contrôle fiscal', 'note' => "Généré automatiquement dans l'export annuel." ),
		);
		if ( 'reel_simplifie' === $taxs['vat_regime'] ) {
			$items[] = array( 'obligation' => 'Déclaration annuelle de TVA CA12', 'echeance' => '2e jour ouvré suivant le 1er mai', 'note' => '' );
		}
		$items[] = array( 'obligation' => 'Déclaration de CFE (si création ou changement) et paiement de la CFE', 'echeance' => '15 décembre', 'note' => '' );
		$items[] = array( 'obligation' => 'Conservation : pièces justificatives et livres 10 ans (C. com. L123-22), documents fiscaux 6 ans (LPF L102 B)', 'echeance' => '-', 'note' => '' );
		return array( 'micro_entreprise' => $is_micro, 'regime_is' => $rsi ? 'réel simplifié' : 'réel normal', 'liste' => $items,
			'liasse' => $rsi ? '2065 + 2033-A à 2033-G' : '2065 + 2050 à 2059' );
	}

	public static function liasse_2033( array $inc, array $bs ): array {
		$a   = function ( $rub ) use ( $bs ) {
			foreach ( $bs['actif'] as $x ) {
				if ( $x['rubrique'] === $rub ) {
					return $x['total'];
				}
			}
			return 0;
		};
		$p   = function ( $rub ) use ( $bs ) {
			foreach ( $bs['passif'] as $x ) {
				if ( $x['rubrique'] === $rub ) {
					return $x['total'];
				}
			}
			return 0;
		};
		$s   = $inc['sections'];
		$acc = function ( $sec, $pre ) use ( $s ) {
			$t = 0;
			foreach ( $s[ $sec ]['lines'] as $l ) {
				$t += 0 === strpos( $l['account'], $pre ) ? $l['amount'] : 0;
			}
			return $t;
		};
		return array(
			'2033-A (bilan simplifié)' => array(
				array( 'Immobilisations incorporelles et corporelles (brut)', $a( 'Immobilisations incorporelles' ) + $a( 'Immobilisations corporelles' ) ),
				array( 'Amortissements', -$a( 'Amortissements et dépréciations' ) ),
				array( 'Immobilisations financières', $a( 'Immobilisations financières' ) ),
				array( 'Clients et comptes rattachés', $a( 'Créances clients' ) ),
				array( 'Autres créances', $a( 'Autres créances' ) + $a( 'Autres créances (État, TVA)' ) + $a( 'Autres créances (compte courant débiteur)' ) ),
				array( 'Disponibilités', $a( 'Disponibilités' ) ),
				array( "Charges constatées d'avance", $a( "Charges constatées d'avance" ) ),
				array( 'Total actif', $bs['total_actif'] ),
				array( 'Capital social', $p( 'Capital social' ) ),
				array( 'Réserve légale et autres réserves', $p( 'Réserves' ) ),
				array( 'Report à nouveau', $p( 'Report à nouveau' ) ),
				array( "Résultat de l'exercice", $p( "Résultat de l'exercice" ) ),
				array( "Emprunts et dettes assimilées (dont compte courant d'associé)", $p( "Emprunts et dettes financières divers (compte courant d'associé)" ) + $p( 'Emprunts et dettes auprès des établissements de crédit' ) ),
				array( 'Fournisseurs et comptes rattachés', $p( 'Dettes fournisseurs' ) ),
				array( 'Dettes fiscales et sociales', $p( 'Dettes fiscales et sociales' ) ),
				array( 'Autres dettes', $p( 'Autres dettes' ) + $p( 'Avances et acomptes reçus / clients créditeurs' ) + $p( 'Concours bancaires courants' ) ),
				array( "Produits constatés d'avance", $p( "Produits constatés d'avance" ) ),
				array( 'Total passif', $bs['total_passif'] ),
			),
			'2033-B (compte de résultat simplifié)' => array(
				array( 'Ventes de marchandises', $acc( 'produits_exploitation', '707' ) ),
				array( 'Production vendue (services)', $acc( 'produits_exploitation', '706' ) + $acc( 'produits_exploitation', '708' ) ),
				array( 'Autres produits', $acc( 'produits_exploitation', '75' ) + $acc( 'produits_exploitation', '74' ) ),
				array( 'Achats de marchandises et matières', $acc( 'charges_exploitation', '60' ) ),
				array( 'Autres charges externes', $acc( 'charges_exploitation', '61' ) + $acc( 'charges_exploitation', '62' ) ),
				array( 'Impôts, taxes et versements assimilés', $acc( 'charges_exploitation', '63' ) ),
				array( 'Rémunérations du personnel', $acc( 'charges_exploitation', '641' ) ),
				array( 'Charges sociales', $acc( 'charges_exploitation', '645' ) ),
				array( 'Dotations aux amortissements', $acc( 'charges_exploitation', '681' ) ),
				array( 'Autres charges', $acc( 'charges_exploitation', '65' ) ),
				array( "Résultat d'exploitation", $inc['resultat_exploitation'] ),
				array( 'Produits financiers', $s['produits_financiers']['total'] ),
				array( 'Charges financières', $s['charges_financieres']['total'] ),
				array( 'Produits exceptionnels', $s['produits_exceptionnels']['total'] ),
				array( 'Charges exceptionnelles', $s['charges_exceptionnelles']['total'] ),
				array( 'Impôt sur les bénéfices', $inc['impot'] ),
				array( 'Bénéfice ou perte', $inc['resultat_net'] ),
			),
			'note' => "Reprise des montants calculés. Les numéros de cases du formulaire de l'année sont à vérifier sur la notice 2033-NOT-SD.",
		);
	}

	const QUESTIONS = array(
		array( 'id' => 'fnp', 'question' => "Avez-vous reçu après la clôture des factures de fournisseurs concernant l'exercice (prestations déjà réalisées) ?",
			'aide' => 'Elles doivent être rattachées à l\'exercice : saisissez-les à leur date de facture avec le paiement « À payer ».' ),
		array( 'id' => 'fae', 'question' => "Avez-vous réalisé des prestations sur l'exercice qui ne sont pas encore facturées ?",
			'aide' => 'Créez la facture à sa date réelle, ou une écriture « Clients, factures à établir » (418100) en opérations diverses.' ),
		array( 'id' => 'cca', 'question' => "Avez-vous payé des charges qui concernent en partie l'exercice suivant (abonnement annuel, assurance) ?",
			'aide' => "Charges constatées d'avance : saisissez une écriture d'inventaire (486000) pour la part de l'exercice suivant." ),
		array( 'id' => 'pca', 'question' => "Avez-vous facturé des prestations qui seront réalisées sur l'exercice suivant ?",
			'aide' => "Produits constatés d'avance : écriture d'inventaire (487000)." ),
		array( 'id' => 'stock', 'question' => 'La société détient-elle des stocks (marchandises) à la clôture ?', 'aide' => 'Pour une activité de services, en principe non.' ),
		array( 'id' => 'litiges', 'question' => 'Existe-t-il un litige ou un risque probable nécessitant une provision ?', 'aide' => 'Provision pour risques (151).' ),
		array( 'id' => 'remuneration', 'question' => "Le président a-t-il été rémunéré sur l'exercice (hors remboursements de compte courant) ?",
			'aide' => "La rémunération du président relève de la paie (DSN) et ne se confond pas avec un remboursement d'apport." ),
	);

	public static function manual_entry( array $d ): int {
		$lines = array();
		foreach ( $d['lines'] as $l ) {
			$lines[] = new AC_Line( $l['account'], AC_Services::cents( $l['debit_cents'] ?? 0 ), AC_Services::cents( $l['credit_cents'] ?? 0 ), array( 'label' => $l['label'] ?? null ) );
		}
		return AC_DB::tx( function () use ( $d, $lines ) {
			$id = AC_Ledger::create_entry( $d['journal'] ?? 'OD', $d['date'], $d['label'], $lines, array( 'piece_ref' => ( $d['piece_ref'] ?? '' ) ?: 'INV', 'source_type' => 'manuel' ) );
			AC_DB::audit( 'creation', 'ecriture_manuelle', $id, null, $d );
			return $id;
		} );
	}

	/** « Préparer mon bilan annuel ». */
	public static function prepare( $fy_id = null ): array {
		$fy     = self::fiscal_year( $fy_id );
		$closed = 'closed' === $fy['status'];
		$assets = $closed ? array() : AC_DB::tx( function () use ( $fy ) { return AC_Services::post_depreciation( $fy['id'] ); } );
		if ( $closed ) {
			foreach ( AC_DB::q( 'SELECT * FROM {fixed_assets} WHERE in_service_date<=?', array( $fy['end_date'] ) ) as $a ) {
				$assets[] = $a + AC_Services::depreciation_for_year( $a, $fy['start_date'], $fy['end_date'] );
			}
		}
		$issues = AC_Checks::period_checks( $fy['start_date'], $fy['end_date'] );
		if ( ! $closed ) {
			AC_DB::tx( function () use ( $fy ) {
				AC_Ledger::clear_source( 'is', $fy['id'], "Recalcul de l'IS" );
				$est = AC_Tax::estimate( $fy );
				if ( $est['is_estime_cents'] > 0 ) {
					AC_Ledger::create_entry( 'OD', $fy['end_date'], "Impôt sur les sociétés de l'exercice",
						array( ac_d( '695000', $est['is_estime_cents'] ), ac_c( '444000', $est['is_estime_cents'] ) ),
						array( 'piece_ref' => 'IS' . substr( $fy['end_date'], 0, 4 ), 'source_type' => 'is', 'source_id' => $fy['id'] ) );
				}
			} );
		}
		$is_est  = AC_Tax::estimate( $fy );
		$inc     = AC_Reports::income_statement( $fy['start_date'], $fy['end_date'] );
		$bs      = AC_Reports::balance_sheet( $fy );
		$company = AC_DB::get_setting( 'company' );
		$conf    = AC_DB::get_setting( 'confirmed' );
		$missing = array();
		if ( empty( $company['siren'] ) ) {
			$missing[] = 'Numéro SIREN (Paramètres) : obligatoire pour le FEC et la liasse.';
		}
		if ( empty( $conf['tax'] ) ) {
			$missing[] = 'Régimes fiscaux à confirmer dans Paramètres (valeurs provisoires utilisées).';
		}
		if ( empty( $company['associate_name'] ) ) {
			$missing[] = "Nom de l'associé unique (Paramètres) : utilisé pour reconnaître les apports.";
		}
		$block   = count( array_filter( $issues, function ( $i ) { return 'bloquant' === $i['niveau']; } ) );
		$result  = $inc['resultat_net'];
		$capital = (int) $company['capital_cents'];
		$legal   = -AC_Ledger::balance( '1061', $fy['end_date'] );
		$to_legal = $result > 0 ? min( intdiv( $result * 5, 100 ), max( 0, intdiv( $capital, 10 ) - $legal ) ) : 0;
		return array(
			'exercice' => $fy, 'anomalies' => $issues, 'bloquant' => $block, 'informations_manquantes' => $missing,
			'definitif' => ! $block && ! $missing && ! $closed,
			'statut' => $closed ? 'Exercice clôturé' : ( ! $block && ! $missing ? 'Prêt à clôturer' : 'Provisoire : contrôles ou informations à compléter' ),
			'immobilisations' => $assets, 'impot' => $is_est, 'compte_de_resultat' => $inc, 'bilan' => $bs,
			'obligations' => self::obligations( $fy, $inc, $bs ), 'liasse' => self::liasse_2033( $inc, $bs ), 'questions' => self::QUESTIONS,
			'affectation_proposee' => array( 'resultat_cents' => $result, 'reserve_legale_cents' => $to_legal, 'report_a_nouveau_cents' => $result - $to_legal,
				'note' => '5 % du bénéfice en réserve légale jusqu\'à 10 % du capital (C. com. L232-10). Le solde peut aller en report à nouveau ou être distribué.' ),
			'creances_dettes' => AC_Reports::receivables_payables( $fy['end_date'] ),
		);
	}

	/** Clôture définitive : validation de toutes les écritures, à nouveaux, verrouillage. */
	public static function close( int $fy_id, bool $force = false ): array {
		$prep = self::prepare( $fy_id );
		if ( 'closed' === $prep['exercice']['status'] ) {
			throw new AC_Error( 'Exercice déjà clôturé.' );
		}
		if ( ( $prep['bloquant'] || $prep['informations_manquantes'] ) && ! $force ) {
			throw new AC_Error( 'Clôture impossible : anomalies bloquantes ou informations manquantes.' );
		}
		$fy = $prep['exercice'];
		return AC_DB::tx( function () use ( $fy, $prep, $force ) {
			foreach ( AC_DB::q( "SELECT id FROM {entries} WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date, id", array( $fy['start_date'], $fy['end_date'] ) ) as $e ) {
				AC_Ledger::validate_entry( $e['id'] );
			}
			$s   = ( new DateTime( $fy['end_date'] ) )->modify( '+1 day' )->format( 'Y-m-d' );
			$nxt = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE start_date=?', array( $s ) );
			if ( ! $nxt ) {
				$e = ( new DateTime( $s ) )->modify( '+1 year -1 day' )->format( 'Y-m-d' );
				AC_DB::insert( 'fiscal_years', array( 'start_date' => $s, 'end_date' => $e ) );
				$nxt = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE start_date=?', array( $s ) );
			}
			$lines  = array();
			$result = 0;
			foreach ( AC_Reports::trial_balance( $fy['start_date'], $fy['end_date'] )['rows'] as $r ) {
				if ( in_array( $r['account'][0], array( '6', '7' ), true ) ) {
					$result -= $r['solde'];
					continue;
				}
				if ( $r['solde'] ) {
					$lines[] = new AC_Line( $r['account'], max( $r['solde'], 0 ), max( -$r['solde'], 0 ), array( 'label' => 'À nouveau' ) );
				}
			}
			if ( $result > 0 ) {
				$lines[] = ac_c( '120000', $result, array( 'label' => "Résultat de l'exercice précédent" ) );
			} elseif ( $result < 0 ) {
				$lines[] = ac_d( '129000', -$result, array( 'label' => "Résultat de l'exercice précédent" ) );
			}
			AC_Ledger::clear_source( 'a_nouveau', $nxt['id'], 'Recalcul des à nouveaux' );
			$an = AC_Ledger::create_entry( 'AN', $nxt['start_date'], 'À nouveaux exercice ' . substr( $nxt['start_date'], 0, 4 ), $lines,
				array( 'piece_ref' => 'AN', 'source_type' => 'a_nouveau', 'source_id' => $nxt['id'], 'status' => 'valide' ) );
			AC_DB::exec( "UPDATE {fiscal_years} SET status='closed', closed_at=?, summary=? WHERE id=?", array( AC_DB::now(),
				wp_json_encode( array( 'resultat_cents' => $result, 'total_bilan_cents' => $prep['bilan']['total_actif'], 'is_cents' => $prep['impot']['is_estime_cents'] ) ), $fy['id'] ) );
			AC_DB::audit( 'cloture_exercice', 'exercice', $fy['id'], null, array( 'resultat' => $result ),
				$force && ( $prep['bloquant'] || $prep['informations_manquantes'] ) ? 'Clôture forcée' : null );
			return array( 'ok' => true, 'resultat_cents' => $result, 'a_nouveaux_entry' => $an, 'exercice_suivant' => $nxt );
		} );
	}

	/** Affectation du résultat décidée par l'associé unique (écriture sur l'exercice suivant). */
	public static function allocate_result( int $fy_id, int $legal, int $div = 0, ?string $date = null ): int {
		$fy     = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE id=?', array( $fy_id ) );
		$result = (int) ( json_decode( (string) $fy['summary'], true )['resultat_cents'] ?? 0 );
		$nxt    = AC_DB::q1( 'SELECT * FROM {fiscal_years} WHERE start_date>? ORDER BY start_date LIMIT 1', array( $fy['end_date'] ) );
		return AC_DB::tx( function () use ( $fy, $fy_id, $result, $nxt, $legal, $div, $date ) {
			AC_Ledger::clear_source( 'affectation', $fy_id, "Nouvelle décision d'affectation" );
			if ( $result >= 0 ) {
				$ran = $result - $legal - $div;
				if ( $ran < 0 ) {
					throw new AC_Error( 'Affectation supérieure au bénéfice.' );
				}
				$lines = array( ac_d( '120000', $result ), ac_c( '106100', $legal ), ac_c( '457000', $div ), ac_c( '110000', $ran ) );
			} else {
				$lines = array( ac_d( '119000', -$result ), ac_c( '129000', -$result ) );
			}
			return AC_Ledger::create_entry( 'OD', $date ?: $nxt['start_date'], 'Affectation du résultat ' . substr( $fy['end_date'], 0, 4 ), $lines,
				array( 'piece_ref' => 'AFF' . substr( $fy['end_date'], 0, 4 ), 'source_type' => 'affectation', 'source_id' => $fy_id ) );
		} );
	}
}

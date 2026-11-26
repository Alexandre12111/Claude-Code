<?php
/**
 * Indemnités kilométriques : trajets professionnels avec un véhicule personnel (ex. Honda Forza 350).
 *
 * Calcul selon le barème fiscal (règle datée « bareme_ik »). Le barème s'applique à la distance
 * totale de l'année : chaque trajet est valorisé à la différence entre le barème appliqué au cumul
 * après et avant ce trajet. Le total de l'année est donc toujours exactement celui du barème.
 * Chaque trajet crée une note de frais sans TVA, inscrite au crédit du compte courant (455).
 */
defined( 'ABSPATH' ) || exit;

class AC_Mileage {

	const TYPES = array( 'moto' => 'Moto ou scooter de plus de 50 cm³', 'cyclo' => 'Cyclomoteur (50 cm³ au plus)', 'voiture' => 'Voiture' );
	const CV    = array( 'moto' => array( '1-2' => '1 ou 2 CV', '3-5' => '3, 4 ou 5 CV', '6+' => 'plus de 5 CV' ), 'cyclo' => array( 'all' => 'Toutes puissances' ),
		'voiture' => array( '3' => '3 CV et moins', '4' => '4 CV', '5' => '5 CV', '6' => '6 CV', '7+' => '7 CV et plus' ) );

	/** Montant du barème (centimes) pour une distance annuelle (km × 10). */
	public static function bareme( string $type, string $cv, int $km_x10, string $date ): int {
		$table   = AC_Rules::get( 'bareme_ik', $date );
		$tranches = $table[ $type ][ 'cyclo' === $type ? 'all' : $cv ] ?? null;
		if ( ! $tranches ) {
			throw new AC_Error( 'Puissance du véhicule inconnue pour le barème kilométrique.' );
		}
		foreach ( $tranches as list( $max, $coef, $fixed ) ) {
			if ( null === $max || $km_x10 <= $max * 10 ) {
				return AC_Money::div_round( $km_x10 * (int) round( (float) $coef * 1000 ), 100 ) + (int) $fixed;
			}
		}
		return 0;
	}

	public static function km_x10( $km ): int {
		return (int) round( (float) str_replace( ',', '.', (string) $km ) * 10 );
	}

	/** Recalcule tous les trajets d'un véhicule pour l'année (ordre chronologique). */
	public static function recompute( string $vehicle, int $year ): void {
		$cum = 0;
		foreach ( AC_DB::q( 'SELECT * FROM {mileage} WHERE vehicle=? AND YEAR(date)=? ORDER BY date, id', array( $vehicle, $year ) ) as $t ) {
			$km     = $t['km_x10'] * ( $t['round_trip'] ? 2 : 1 );
			$amount = self::bareme( $t['vehicle_type'], $t['cv'], $cum + $km, $t['date'] ) - self::bareme( $t['vehicle_type'], $t['cv'], $cum, $t['date'] );
			$cum   += $km;
			if ( $amount !== (int) $t['amount_cents'] ) {
				AC_DB::exec( 'UPDATE {mileage} SET amount_cents=? WHERE id=?', array( $amount, $t['id'] ) );
				if ( $t['expense_id'] ) {
					AC_Services::save_expense( array( 'ttc_cents' => $amount, 'vat_regime' => 'sans_tva' ), (int) $t['expense_id'], 'Recalcul du barème kilométrique (cumul annuel)' );
				}
			}
		}
	}

	public static function add( array $d ): array {
		$v = AC_DB::get_setting( 'vehicle' );
		if ( empty( $d['date'] ) || empty( $d['from_place'] ) || empty( $d['to_place'] ) || empty( $d['purpose'] ) ) {
			throw new AC_Error( 'Date, départ, arrivée et motif professionnel sont obligatoires (justification fiscale).' );
		}
		$km = self::km_x10( $d['km'] ?? 0 );
		if ( $km <= 0 ) {
			throw new AC_Error( 'Distance invalide.' );
		}
		return AC_DB::tx( function () use ( $d, $v, $km ) {
			$round = ! empty( $d['round_trip'] );
			$cat   = AC_DB::q1( "SELECT id FROM {categories} WHERE code='ik'" );
			$total = $km * ( $round ? 2 : 1 );
			$label = 'IK ' . $d['from_place'] . ( $round ? ' ⇄ ' : ' → ' ) . $d['to_place'] . ' (' . rtrim( rtrim( number_format( $total / 10, 1, ',', '' ), '0' ), ',' ) . ' km)';
			$exp   = AC_Services::save_expense( array( 'date' => $d['date'], 'supplier_name' => 'Indemnités kilométriques', 'label' => mb_substr( $label, 0, 250 ),
				'ttc_cents' => 1, 'vat_regime' => 'sans_tva', 'category_id' => $cat ? $cat['id'] : null, 'payment_method' => 'perso', 'source' => 'ik',
				'status' => 'a_valider', 'comment' => $d['purpose'] . ' · ' . $v['name'] ), null, 'Trajet professionnel' );
			$id = AC_DB::insert( 'mileage', array( 'date' => $d['date'], 'vehicle' => $v['name'], 'vehicle_type' => $v['type'], 'cv' => $v['cv'],
				'from_place' => $d['from_place'], 'to_place' => $d['to_place'], 'purpose' => $d['purpose'], 'km_x10' => $km, 'round_trip' => $round ? 1 : 0,
				'amount_cents' => 0, 'expense_id' => $exp['id'], 'created_at' => AC_DB::now() ) );
			self::recompute( $v['name'], (int) substr( $d['date'], 0, 4 ) );
			return array( 'id' => $id, 'expense_id' => $exp['id'], 'amount_cents' => (int) AC_DB::val( 'SELECT amount_cents FROM {mileage} WHERE id=?', array( $id ) ) );
		} );
	}

	public static function delete( int $id ): void {
		AC_DB::tx( function () use ( $id ) {
			$t = AC_DB::q1( 'SELECT * FROM {mileage} WHERE id=?', array( $id ) );
			if ( ! $t ) {
				return;
			}
			AC_DB::exec( 'DELETE FROM {mileage} WHERE id=?', array( $id ) );
			if ( $t['expense_id'] ) {
				AC_Services::delete_expense( (int) $t['expense_id'], 'Trajet supprimé' );
			}
			self::recompute( $t['vehicle'], (int) substr( $t['date'], 0, 4 ) );
		} );
	}

	public static function summary( int $year ): array {
		$v     = AC_DB::get_setting( 'vehicle' );
		$trips = AC_DB::q( 'SELECT m.*, x.status expense_status FROM {mileage} m LEFT JOIN {expenses} x ON x.id=m.expense_id WHERE YEAR(m.date)=? ORDER BY m.date DESC, m.id DESC', array( $year ) );
		$km    = 0;
		foreach ( $trips as $t ) {
			$km += $t['km_x10'] * ( $t['round_trip'] ? 2 : 1 );
		}
		$amount = array_sum( array_column( $trips, 'amount_cents' ) );
		$places = array();
		foreach ( AC_DB::q( 'SELECT from_place p FROM {mileage} UNION SELECT to_place FROM {mileage}' ) as $p ) {
			$places[] = $p['p'];
		}
		$rate = null;
		try {
			$rate = self::bareme( $v['type'], $v['cv'], 10000, $year . '-12-31' ) / 1000; // indicatif : centimes par km sur la 1re tranche
		} catch ( AC_Error $e ) {
			$rate = null;
		}
		return array( 'year' => $year, 'vehicle' => $v, 'types' => self::TYPES, 'cv_options' => self::CV, 'trips' => $trips, 'km_x10' => $km, 'amount_cents' => $amount,
			'rate_cents_per_km' => $rate, 'places' => array_values( array_unique( $places ) ),
			'rules' => array(
				'Barème fiscal des indemnités kilométriques : le montant dépend de la distance totale de l\'année, il est recalculé à chaque trajet.',
				'Pour chaque trajet : date, départ, arrivée, distance et motif professionnel (client, rendez vous). Ce relevé tient lieu de justificatif.',
				'Le véhicule doit vous appartenir. La société ne doit pas payer en plus le carburant, l\'entretien ou l\'assurance de ce véhicule.',
				'Le barème est mis à jour chaque année par l\'administration : vérifiez la puissance fiscale (carte grise, case P.6).',
			) );
	}

	public static function pdf( int $year ): string {
		$s    = self::summary( $year );
		$rows = array( array( 'Date', 'Trajet', 'Motif', 'Km', 'Montant' ) );
		foreach ( array_reverse( $s['trips'] ) as $t ) {
			$rows[] = array( AC_Checks::d( $t['date'] ), $t['from_place'] . ( $t['round_trip'] ? ' ⇄ ' : ' → ' ) . $t['to_place'], $t['purpose'],
				number_format( $t['km_x10'] * ( $t['round_trip'] ? 2 : 1 ) / 10, 1, ',', ' ' ), AC_Money::fmt( $t['amount_cents'] ) );
		}
		$rows[] = array( '', 'Total', '', number_format( $s['km_x10'] / 10, 1, ',', ' ' ), AC_Money::fmt( $s['amount_cents'] ) );
		return AC_Pdf::build( "État des frais kilométriques $year", array(
			array( 'p', 'Véhicule : ' . $s['vehicle']['name'] . ' (' . ( self::TYPES[ $s['vehicle']['type'] ] ?? '' ) . ', ' . ( self::CV[ $s['vehicle']['type'] ][ $s['vehicle']['cv'] ] ?? '' ) . '). Barème fiscal en vigueur.' ),
			array( 'table', $rows, array( 1.2, 3.2, 3.2, 0.9, 1.3 ), array( 3, 4 ) ),
			array( 'p', 'Indemnités remboursées par la société au dirigeant pour l\'utilisation de son véhicule personnel à des fins professionnelles. Fait le ____________, signature : ____________' ),
		) );
	}
}

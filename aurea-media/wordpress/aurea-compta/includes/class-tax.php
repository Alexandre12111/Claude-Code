<?php
/**
 * Estimation de l'impôt sur les sociétés (CGI art. 219).
 * Taux réduit 15 % jusqu'à 42 500 € (proratisé si l'exercice ne dure pas 12 mois) si CA HT < 10 M€,
 * capital entièrement libéré et détenu à 75 % au moins par des personnes physiques ; 25 % au delà.
 */
defined( 'ABSPATH' ) || exit;

class AC_Tax {

	public static function months( string $start, string $end ): int {
		return ( (int) substr( $end, 0, 4 ) - (int) substr( $start, 0, 4 ) ) * 12 + (int) substr( $end, 5, 2 ) - (int) substr( $start, 5, 2 ) + 1;
	}

	public static function estimate( array $fy ): array {
		$inc     = AC_Reports::income_statement( $fy['start_date'], $fy['end_date'] );
		$rc      = $inc['resultat_avant_impot'];
		$company = AC_DB::get_setting( 'company' );
		$taxs    = AC_DB::get_setting( 'tax' );
		$reint   = array();
		foreach ( AC_DB::q( "SELECT x.*, c.name cat FROM {expenses} x LEFT JOIN {categories} c ON c.id=x.category_id
			WHERE x.date BETWEEN ? AND ? AND x.deductible<100 AND IFNULL(c.rule,'')<>'personnel' AND x.fixed_asset_id IS NULL", array( $fy['start_date'], $fy['end_date'] ) ) as $x ) {
			$nonrec = 'fr' === $x['vat_regime'] ? $x['vat_cents'] - AC_Money::pct_of( $x['vat_cents'], $x['vat_recovery'] ) : 0;
			$charge = $x['ht_cents'] + $nonrec;
			$part   = $charge - AC_Money::pct_of( $charge, $x['deductible'] );
			if ( $part ) {
				$reint[] = array( 'libelle' => ( $x['cat'] ?: 'Dépense' ) . ' : ' . trim( $x['supplier_name'] . ' ' . $x['label'] ) . ' (' . ( 100 - $x['deductible'] ) . ' % non déductible)',
					'montant_cents' => $part, 'source' => "#/depenses?id={$x['id']}" );
			}
		}
		$amendes = AC_Ledger::balance( '6712', $fy['end_date'], $fy['start_date'] );
		$already = 0;
		foreach ( $reint as $r ) {
			$already += false !== strpos( $r['libelle'], 'Amendes' ) ? $r['montant_cents'] : 0;
		}
		if ( $amendes - $already > 0 ) {
			$reint[] = array( 'libelle' => 'Amendes et pénalités (CGI art. 39-2)', 'montant_cents' => $amendes - $already );
		}
		$before   = $rc + array_sum( array_column( $reint, 'montant_cents' ) );
		$deficit  = (int) $taxs['loss_carryforward_cents'];
		$imput    = 0;
		if ( $before > 0 && $deficit > 0 ) {
			$cap   = 100000000 + max( 0, intdiv( $before - 100000000, 2 ) );
			$imput = min( $deficit, $before, $cap );
		}
		$rf      = $before - $imput;
		$rf_eur  = $rf > 0 ? intdiv( $rf, 100 ) * 100 : $rf;
		$on      = $fy['end_date'];
		$months  = self::months( $fy['start_date'], $fy['end_date'] );
		$excl    = array();
		if ( $inc['chiffre_affaires'] >= intdiv( AC_Rules::get( 'is_ca_max_taux_reduit_cents', $on ) * $months, 12 ) ) {
			$excl[] = "chiffre d'affaires supérieur à 10 M€";
		}
		if ( empty( $company['capital_fully_paid'] ) ) {
			$excl[] = 'capital non entièrement libéré';
		}
		if ( empty( $company['held_by_natural_persons_75'] ) ) {
			$excl[] = 'capital non détenu à 75 % par des personnes physiques';
		}
		$plafond = AC_Money::prorata( AC_Rules::get( 'is_plafond_taux_reduit_cents', $on ), $months, 12 );
		$tr      = AC_Rules::get( 'is_taux_reduit', $on );
		$tn      = AC_Rules::get( 'is_taux_normal', $on );
		$tranches = array();
		if ( $rf_eur > 0 ) {
			if ( ! $excl ) {
				$b1         = min( $rf_eur, $plafond );
				$tranches[] = array( 'libelle' => "$tr % jusqu'à " . number_format( $plafond / 100, 0, ',', ' ' ) . ' €', 'base_cents' => $b1, 'impot_cents' => AC_Money::pct_of( $b1, $tr ) );
				if ( $rf_eur > $plafond ) {
					$tranches[] = array( 'libelle' => "$tn % au delà", 'base_cents' => $rf_eur - $plafond, 'impot_cents' => AC_Money::pct_of( $rf_eur - $plafond, $tn ) );
				}
			} else {
				$tranches[] = array( 'libelle' => "$tn %", 'base_cents' => $rf_eur, 'impot_cents' => AC_Money::pct_of( $rf_eur, $tn ) );
			}
		}
		$is    = array_sum( array_column( $tranches, 'impot_cents' ) );
		$paid  = -AC_DB::ival( "SELECT COALESCE(SUM(a.amount_cents),0) FROM {allocations} a JOIN {bank_transactions} t ON t.id=a.bank_transaction_id WHERE a.target_type='is' AND t.date BETWEEN ? AND ?",
			array( $fy['start_date'], $fy['end_date'] ) );
		$prev  = (int) $taxs['previous_is_cents'];
		$sched = array();
		if ( $prev > AC_Rules::get( 'is_seuil_acomptes_cents', $on ) ) {
			foreach ( array( '03-15', '06-15', '09-15', '12-15' ) as $m ) {
				$sched[] = array( 'echeance' => substr( $fy['end_date'], 0, 4 ) . "-$m", 'montant_cents' => intdiv( $prev, 4 ) );
			}
		}
		return array(
			'resultat_comptable_cents' => $rc, 'reintegrations' => $reint, 'deductions' => array(), 'deficits_imputes_cents' => $imput,
			'resultat_fiscal_cents' => $rf, 'resultat_fiscal_arrondi_cents' => $rf_eur, 'taux_reduit_eligible' => ! $excl, 'taux_reduit_motifs_exclusion' => $excl,
			'plafond_taux_reduit_cents' => $plafond, 'tranches' => $tranches, 'is_estime_cents' => $is, 'acomptes_payes_cents' => $paid, 'acomptes_prevus' => $sched,
			'solde_estime_cents' => $is - $paid,
			'echeance_solde' => '15 du 4e mois suivant la clôture (15 mai pour un exercice civil), avec le relevé de solde 2572.',
			'remarques' => array(
				'Estimation calculée sur les écritures enregistrées : elle évolue avec chaque opération.',
				"Pas d'acompte pour le premier exercice, ni si l'IS de l'exercice précédent est inférieur à 3 000 €.",
				"La trésorerie disponible n'est pas le bénéfice : le résultat tient compte des factures non encaissées, des dettes et des amortissements.",
			),
		);
	}
}

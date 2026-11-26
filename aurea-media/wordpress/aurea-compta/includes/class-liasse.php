<?php
/**
 * Guide de liasse : pour chaque formulaire (2065, 2033-A à G, 2572), les montants à saisir case par case,
 * en euros entiers, dans l'ordre des formulaires, avec des contrôles de cohérence.
 * Les codes de case sont les repères des formulaires 2033-SD ; le libellé fait foi si la présentation évolue.
 */
defined( 'ABSPATH' ) || exit;

class AC_Liasse {

	/** Arrondi à l'euro (la liasse se remplit en euros, sans centimes). */
	private static function eur( int $cents ): int {
		return (int) round( $cents / 100 );
	}

	/** Montant d'un compte selon son préfixe et son sens. */
	private static function sum( array $tb, array $prefixes, string $sens = 'debit', array $exclude = array() ): int {
		$t = 0;
		foreach ( $tb as $r ) {
			$acc = (string) $r['account'];
			$hit = false;
			foreach ( $prefixes as $p ) {
				if ( 0 === strpos( $acc, $p ) ) {
					$hit = true;
					break;
				}
			}
			foreach ( $exclude as $p ) {
				if ( 0 === strpos( $acc, $p ) ) {
					$hit = false;
				}
			}
			if ( ! $hit ) {
				continue;
			}
			$s = (int) $r['solde'];
			if ( 'debit' === $sens ) {
				$t += max( 0, $s );
			} elseif ( 'credit' === $sens ) {
				$t += max( 0, -$s );
			} elseif ( 'net_debit' === $sens ) {
				$t += $s;
			} else {
				$t -= $s; // net_credit
			}
		}
		return $t;
	}

	private static function line( string $code, string $label, ?int $value, string $note = '' ): array {
		return array( 'code' => $code, 'label' => $label, 'value' => $value, 'note' => $note );
	}

	public static function guide( array $fy ): array {
		$tb      = AC_Reports::trial_balance( $fy['start_date'], $fy['end_date'] )['rows'];
		$inc     = AC_Reports::income_statement( $fy['start_date'], $fy['end_date'] );
		$est     = AC_Tax::estimate( $fy );
		$company = AC_DB::get_setting( 'company' );
		$taxs    = AC_DB::get_setting( 'tax' );
		$months  = AC_Tax::months( $fy['start_date'], $fy['end_date'] );
		$S       = function ( $p, $sens = 'debit', $ex = array() ) use ( $tb ) { return self::eur( self::sum( $tb, (array) $p, $sens, $ex ) ); };

		// ---------------- 2033-B : compte de résultat simplifié
		$b = array(
			'210' => $S( '707', 'net_credit' ),
			'214' => $S( array( '701', '702', '703' ), 'net_credit' ),
			'218' => $S( array( '704', '705', '706', '708', '709' ), 'net_credit' ),
			'222' => $S( '713', 'net_credit' ),
			'224' => $S( '72', 'net_credit' ),
			'226' => $S( '74', 'net_credit' ),
			'230' => $S( array( '75', '781', '791' ), 'net_credit' ),
			'234' => $S( '607', 'net_debit' ),
			'236' => $S( '6037', 'net_debit' ),
			'238' => $S( array( '601', '602' ), 'net_debit' ),
			'240' => $S( array( '6031', '6032' ), 'net_debit' ),
			'242' => $S( array( '604', '605', '606', '608', '609', '61', '62' ), 'net_debit' ),
			'244' => $S( '63', 'net_debit' ),
			'250' => $S( array( '641', '644' ), 'net_debit' ),
			'252' => $S( array( '645', '646', '647', '648' ), 'net_debit' ),
			'254' => $S( array( '6811', '6812' ), 'net_debit' ),
			'256' => $S( array( '6815', '6816', '6817' ), 'net_debit' ),
			'262' => $S( '65', 'net_debit' ),
			'280' => $S( '76', 'net_credit' ),
			'290' => $S( '77', 'net_credit' ),
			'294' => $S( '66', 'net_debit' ),
			'300' => $S( '67', 'net_debit' ),
			'306' => $S( '69', 'net_debit' ),
		);
		$b['232'] = $b['210'] + $b['214'] + $b['218'] + $b['222'] + $b['224'] + $b['226'] + $b['230'];
		$b['264'] = $b['234'] + $b['236'] + $b['238'] + $b['240'] + $b['242'] + $b['244'] + $b['250'] + $b['252'] + $b['254'] + $b['256'] + $b['262'];
		$b['270'] = $b['232'] - $b['264'];
		$b['310'] = $b['270'] + $b['280'] + $b['290'] - $b['294'] - $b['300'] - $b['306'];
		$reint    = array();
		foreach ( $est['reintegrations'] as $r ) {
			$reint[] = self::line( '', $r['libelle'], self::eur( $r['montant_cents'] ), 'Cadre « Réintégrations » de la 2033-B : choisissez la ligne correspondant à la nature de la charge.' );
		}
		if ( $b['306'] ) {
			$reint[] = self::line( '', "Impôt sur les sociétés comptabilisé (non déductible)", $b['306'], 'À réintégrer : l\'IS n\'est pas une charge déductible.' );
		}
		$rf  = $b['310'] + array_sum( array_column( $reint, 'value' ) ) - self::eur( (int) $est['deficits_imputes_cents'] );
		$bf  = max( 0, $rf );
		$df  = max( 0, -$rf );

		// ---------------- 2033-A : bilan simplifié
		$vat_net = self::eur( self::sum( $tb, array( '445' ), 'net_debit' ) ); // comptes de TVA compensés : crédit (actif) ou dette (passif)
		$a = array(
			'010' => $S( '207' ), '012' => $S( '2807', 'credit' ),
			'014' => $S( '20', 'debit', array( '207' ) ), '016' => $S( '280', 'credit', array( '2807' ) ),
			'028' => $S( array( '21', '22', '23' ) ), '030' => $S( array( '281', '282', '290', '291' ), 'credit' ),
			'040' => $S( array( '26', '27' ) ), '042' => $S( array( '296', '297' ), 'credit' ),
			'050' => $S( array( '31', '32', '33', '34', '35' ) ), '052' => $S( array( '391', '392', '393', '394', '395' ), 'credit' ),
			'060' => $S( '37' ), '062' => $S( '397', 'credit' ),
			'064' => $S( '4091' ),
			'068' => $S( '41' ), '070' => $S( '491', 'credit' ),
			'072' => $S( array( '40', '42', '43', '44', '45', '46', '47', '48' ), 'debit', array( '4091', '486', '487', '445' ) ) + max( 0, $vat_net ),
			'074' => $S( array( '495', '496' ), 'credit' ),
			'080' => $S( '50' ), '082' => $S( '59', 'credit' ),
			'084' => $S( array( '51', '52', '53', '54', '58' ) ),
			'092' => $S( '486' ),
		);
		$brut  = array( '010', '014', '028', '040', '050', '060', '064', '068', '072', '080', '084', '092' );
		$amort = array( '012', '016', '030', '042', '052', '062', '070', '074', '082' );
		$a['110'] = array_sum( array_map( function ( $k ) use ( $a ) { return $a[ $k ]; }, $brut ) );
		$a['112'] = array_sum( array_map( function ( $k ) use ( $a ) { return $a[ $k ]; }, $amort ) );
		$net_actif = $a['110'] - $a['112'];

		$p = array(
			'120' => $S( array( '101', '108' ), 'net_credit' ),
			'124' => $S( '105', 'net_credit' ),
			'126' => $S( '1061', 'net_credit' ),
			'130' => $S( array( '1062', '1064' ), 'net_credit' ),
			'132' => $S( array( '1063', '1068', '13' ), 'net_credit' ),
			'134' => $S( '11', 'net_credit' ),
			'136' => $b['310'] + $S( '12', 'net_credit' ),
			'140' => $S( '14', 'net_credit' ),
			'154' => $S( '15', 'net_credit' ),
			'156' => $S( array( '16', '17', '51', '52' ), 'credit' ),
			'164' => $S( array( '4191', '41' ), 'credit' ),
			'166' => $S( '40', 'credit', array( '4091' ) ),
			'169' => $S( '455', 'credit' ),
			'172' => $S( array( '42', '43', '44', '45', '46', '47', '48' ), 'credit', array( '486', '487', '4191', '445' ) ) + max( 0, -$vat_net ),
			'174' => $S( '487', 'net_credit' ),
		);
		$p['142'] = $p['120'] + $p['124'] + $p['126'] + $p['130'] + $p['132'] + $p['134'] + $p['136'] + $p['140'];
		$p['176'] = $p['156'] + $p['164'] + $p['166'] + $p['172'] + $p['174'];
		$p['180'] = $p['142'] + $p['154'] + $p['176'];
		$ecart    = $net_actif - $p['180'];
		$arrondi  = '';
		if ( $ecart && abs( $ecart ) <= 3 ) {
			// écart d'arrondi à l'euro : ajusté sur les autres dettes, comme le font les logiciels comptables
			$p['172'] += $ecart;
			$p['176'] += $ecart;
			$p['180'] += $ecart;
			$arrondi   = "Écart d'arrondi de $ecart € reporté sur « Autres dettes » (case 172).";
		}

		// ---------------- formulaires
		$forms   = array();
		$forms[] = array( 'id' => '2065', 'title' => '2065-SD : déclaration de résultat', 'intro' => "Formulaire principal. Saisissez d'abord l'identification, puis reportez le résultat fiscal calculé sur la 2033-B.",
			'lines' => array(
				self::line( '', 'Dénomination', null, (string) ( $company['name'] ?? '' ) ),
				self::line( '', 'SIREN', null, (string) ( $company['siren'] ?: 'À renseigner dans Paramètres' ) ),
				self::line( '', 'Exercice', null, 'Du ' . date_i18n( 'd/m/Y', strtotime( $fy['start_date'] ) ) . ' au ' . date_i18n( 'd/m/Y', strtotime( $fy['end_date'] ) ) . " ($months mois)" ),
				self::line( '', 'Régime d\'imposition', null, 'Réel simplifié (RSI), impôt sur les sociétés' ),
				self::line( '', 'Activité', null, 'Création et hébergement de sites internet, conseil en marketing et communication' ),
				self::line( '', 'Résultat fiscal : bénéfice', $bf ?: null, 'Report de la case 370 de la 2033-B.' ),
				self::line( '', 'Résultat fiscal : déficit', $df ?: null, 'Report de la case 372 de la 2033-B.' ),
				self::line( '', 'Taux réduit de 15 %', null, $est['taux_reduit_eligible'] ? 'Cochez : capital entièrement libéré et détenu à 75 % au moins par des personnes physiques.' : 'Non applicable : ' . implode( ', ', $est['taux_reduit_motifs_exclusion'] ) ),
			) );
		$forms[] = array( 'id' => '2033A', 'title' => '2033-A : bilan simplifié', 'intro' => 'Montants en euros entiers. Le formulaire en ligne calcule les colonnes « net » et les totaux : comparez avec les totaux ci-dessous.',
			'lines' => array_merge(
				array_values( array_filter( array(
					self::line( '010', 'Fonds commercial (brut)', $a['010'] ), self::line( '012', 'Fonds commercial (amortissements)', $a['012'] ),
					self::line( '014', 'Autres immobilisations incorporelles (brut)', $a['014'] ), self::line( '016', 'Autres immobilisations incorporelles (amortissements)', $a['016'] ),
					self::line( '028', 'Immobilisations corporelles (brut)', $a['028'] ), self::line( '030', 'Immobilisations corporelles (amortissements)', $a['030'] ),
					self::line( '040', 'Immobilisations financières (brut)', $a['040'] ),
					self::line( '050', 'Stocks de matières premières', $a['050'] ), self::line( '060', 'Stocks de marchandises', $a['060'] ),
					self::line( '064', 'Avances et acomptes versés sur commandes', $a['064'] ),
					self::line( '068', 'Clients et comptes rattachés', $a['068'] ), self::line( '070', 'Clients (provisions)', $a['070'] ),
					self::line( '072', 'Autres créances (dont crédit de TVA)', $a['072'], $vat_net > 0 ? "Dont crédit de TVA : $vat_net €." : '' ),
					self::line( '080', 'Valeurs mobilières de placement', $a['080'] ),
					self::line( '084', 'Disponibilités (Qonto)', $a['084'] ),
					self::line( '092', "Charges constatées d'avance", $a['092'] ),
				), function ( $l ) { return 0 !== $l['value']; } ) ),
				array( self::line( '110', 'Total actif (brut)', $a['110'], 'Calculé par le formulaire : vérifiez.' ), self::line( '112', 'Total actif (amortissements)', $a['112'], 'Calculé par le formulaire.' ) ),
				array_values( array_filter( array(
					self::line( '120', 'Capital social', $p['120'] ), self::line( '124', 'Écarts de réévaluation', $p['124'] ), self::line( '126', 'Réserve légale', $p['126'] ),
					self::line( '130', 'Réserves réglementées', $p['130'] ), self::line( '132', 'Autres réserves', $p['132'] ),
					self::line( '134', 'Report à nouveau', $p['134'], $p['134'] < 0 ? 'Montant négatif : précédé du signe moins.' : '' ),
				), function ( $l ) { return 0 !== $l['value']; } ) ),
				array( self::line( '136', "Résultat de l'exercice", $p['136'], $p['136'] < 0 ? 'Perte : saisissez le montant précédé du signe moins.' : 'Doit être égal à la case 310 de la 2033-B.' ) ),
				array_values( array_filter( array(
					self::line( '140', 'Provisions réglementées', $p['140'] ), self::line( '154', 'Provisions pour risques et charges', $p['154'] ),
					self::line( '156', 'Emprunts et dettes assimilées', $p['156'] ), self::line( '164', 'Avances et acomptes reçus', $p['164'] ),
					self::line( '166', 'Fournisseurs et comptes rattachés', $p['166'] ),
					self::line( '169', "dont comptes courants d'associés", $p['169'], 'Sous-détail de la ligne « Autres dettes » : ce que la société vous doit.' ),
					self::line( '172', 'Autres dettes (compte courant, TVA, IS…)', $p['172'], $arrondi ),
					self::line( '174', "Produits constatés d'avance", $p['174'] ),
				), function ( $l ) { return 0 !== $l['value']; } ) ),
				array( self::line( '180', 'Total passif', $p['180'], 'Calculé par le formulaire : doit être égal au total actif net (' . $net_actif . ' €).' ) )
			) );
		$blines  = array();
		foreach ( array( '210' => 'Ventes de marchandises', '214' => 'Production vendue (biens)', '218' => 'Production vendue (services) : votre chiffre d\'affaires', '222' => 'Production stockée',
			'224' => 'Production immobilisée', '226' => "Subventions d'exploitation", '230' => 'Autres produits', '232' => "Total des produits d'exploitation",
			'234' => 'Achats de marchandises', '236' => 'Variation de stock (marchandises)', '238' => 'Achats de matières premières', '240' => 'Variation de stock (matières)',
			'242' => 'Autres achats et charges externes (hébergement, logiciels, restaurants, loyer, honoraires, frais bancaires, frais kilométriques…)', '244' => 'Impôts, taxes et versements assimilés',
			'250' => 'Rémunérations du personnel', '252' => 'Charges sociales', '254' => 'Dotations aux amortissements', '256' => 'Dotations aux provisions', '262' => 'Autres charges',
			'264' => "Total des charges d'exploitation", '270' => "Résultat d'exploitation", '280' => 'Produits financiers', '290' => 'Produits exceptionnels', '294' => 'Charges financières',
			'300' => 'Charges exceptionnelles', '306' => 'Impôt sur les bénéfices', '310' => 'Bénéfice ou perte' ) as $k => $l ) {
			if ( $b[ $k ] || in_array( $k, array( '218', '232', '242', '264', '270', '310' ), true ) ) {
				$note = in_array( $k, array( '232', '264', '270', '310' ), true ) ? 'Calculé par le formulaire : vérifiez.' . ( '310' === $k && $b['310'] < 0 ? ' Perte : signe moins.' : '' ) : '';
				if ( '262' === (string) $k && $S( '651', 'net_debit' ) ) {
					$note = 'Dont ' . $S( '651', 'net_debit' ) . ' € d\'abonnements logiciels, comptabilisés en redevances de licences (compte 651).';
				}
				$blines[] = self::line( (string) $k, $l, $b[ $k ], $note );
			}
		}
		$blines  = array_merge( $blines, $reint ? $reint : array( self::line( '', 'Réintégrations', null, "Aucune réintégration détectée (pas d'amende, de charge non déductible ni d'IS comptabilisé)." ) ),
			array( self::line( '370', 'Résultat fiscal : bénéfice', $bf ?: null ), self::line( '372', 'Résultat fiscal : déficit', $df ?: null ) ) );
		$forms[] = array( 'id' => '2033B', 'title' => '2033-B : compte de résultat simplifié', 'intro' => 'Produits et charges hors TVA, en euros entiers. Les lignes non listées restent vides.', 'lines' => $blines );
		$assets  = AC_DB::q( 'SELECT * FROM {fixed_assets} WHERE acquisition_date<=? AND (disposal_date IS NULL OR disposal_date>=?)', array( $fy['end_date'], $fy['start_date'] ) );
		$forms[] = array( 'id' => '2033C', 'title' => '2033-C : immobilisations et amortissements', 'intro' => $assets ? 'Reprenez le tableau des immobilisations de l\'export (immobilisations-amortissements.pdf), ligne par ligne.' : 'Aucune immobilisation : laissez le formulaire vide.',
			'lines' => array_map( function ( $x ) { return self::line( '', $x['label'] . ' (acquise le ' . date_i18n( 'd/m/Y', strtotime( $x['acquisition_date'] ) ) . ')', self::eur( (int) $x['cost_cents'] ), 'Valeur brute' ); }, $assets ) );
		$prev_def = self::eur( (int) $taxs['loss_carryforward_cents'] );
		$imput    = self::eur( (int) $est['deficits_imputes_cents'] );
		$forms[] = array( 'id' => '2033D', 'title' => '2033-D : provisions, amortissements dérogatoires et déficits', 'intro' => 'Seul le cadre des déficits vous concerne.',
			'lines' => array(
				self::line( '982', "Déficits restant à reporter au titre de l'exercice précédent", $prev_def ),
				self::line( '983', "Déficits imputés sur le résultat de l'exercice", $imput ),
				self::line( '984', 'Déficits reportables', $prev_def - $imput ),
				self::line( '860', "Déficit de l'exercice", $df ),
				self::line( '870', 'Total des déficits restant à reporter', $prev_def - $imput + $df, 'À reprendre l\'an prochain dans Paramètres (déficit reportable).' ),
			) );
		$forms[] = array( 'id' => '2033E', 'title' => '2033-E : valeur ajoutée et effectifs', 'intro' => $inc['chiffre_affaires'] < 15250000 ? "Non requis : chiffre d'affaires inférieur à 152 500 €." : 'Requis : reprenez la production et les consommations de la 2033-B.', 'lines' => array() );
		$assoc   = (string) ( $company['associate_name'] ?: 'Associé unique' );
		$forms[] = array( 'id' => '2033F', 'title' => '2033-F : composition du capital social', 'intro' => 'Liste des associés détenant au moins 10 % du capital.',
			'lines' => array(
				self::line( '', 'Associé', null, "$assoc, personne physique résidant en France" ),
				self::line( '', 'Pourcentage de détention', 100, '%' ),
				self::line( '', 'Capital social', self::eur( (int) $company['capital_cents'] ), '€' ),
			) );
		$forms[] = array( 'id' => '2033G', 'title' => '2033-G : filiales et participations', 'intro' => 'Aucune filiale ni participation : cochez « néant » ou laissez vide.', 'lines' => array() );
		$forms[] = array( 'id' => '2572', 'title' => "2572-SD : relevé de solde de l'impôt sur les sociétés", 'intro' => "À déposer même si aucun impôt n'est dû (au plus tard le 15 mai pour un exercice clos le 31 décembre).",
			'lines' => array(
				self::line( '', 'Résultat fiscal', $rf, $rf < 0 ? 'Déficit : aucun impôt.' : '' ),
				self::line( '', 'Impôt au taux réduit et au taux normal', self::eur( (int) $est['is_estime_cents'] ), implode( ' ; ', array_map( function ( $t ) { return $t['libelle']; }, $est['tranches'] ) ) ),
				self::line( '', 'Acomptes déjà versés', self::eur( (int) $est['acomptes_payes_cents'] ) ),
				self::line( '', 'Solde à payer', max( 0, self::eur( (int) $est['solde_estime_cents'] ) ) ),
			) );

		// ---------------- contrôles
		$checks = array(
			array( 'label' => 'Bilan équilibré (actif net = passif)', 'ok' => $net_actif === $p['180'], 'detail' => "Actif $net_actif € · passif {$p['180']} €" ),
			array( 'label' => 'Résultat identique au bilan (136) et au compte de résultat (310)', 'ok' => $p['136'] === $b['310'] || $S( '12', 'net_credit' ) !== 0, 'detail' => "{$p['136']} € et {$b['310']} €" ),
			array( 'label' => "Compte d'attente 471 soldé", 'ok' => 0 === self::sum( $tb, array( '471' ), 'net_debit' ), 'detail' => 'Toutes les opérations doivent être catégorisées.' ),
			array( 'label' => 'SIREN renseigné', 'ok' => ! empty( $company['siren'] ), 'detail' => 'Paramètres > Société' ),
			array( 'label' => "Chiffre d'affaires identique à la comptabilité", 'ok' => abs( $b['210'] + $b['214'] + $b['218'] - self::eur( (int) $inc['chiffre_affaires'] ) ) <= 1, 'detail' => self::eur( (int) $inc['chiffre_affaires'] ) . ' €' ),
		);
		return array( 'exercice' => $fy, 'forms' => $forms, 'controles' => $checks, 'resultat_fiscal' => $rf,
			'avertissement' => "Codes de case : repères des formulaires 2033-SD. Si la présentation du formulaire de l'année diffère, le libellé fait foi. Montants en euros entiers, sans centimes." );
	}
}

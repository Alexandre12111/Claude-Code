<?php
/**
 * Exports : FEC, PDF, Excel, CSV, dossiers ZIP (mois, TVA, exercice).
 */
defined( 'ABSPATH' ) || exit;

class AC_Exports {

	const FEC_COLUMNS = array( 'JournalCode', 'JournalLib', 'EcritureNum', 'EcritureDate', 'CompteNum', 'CompteLib', 'CompAuxNum', 'CompAuxLib', 'PieceRef',
		'PieceDate', 'EcritureLib', 'Debit', 'Credit', 'EcritureLet', 'DateLet', 'ValidDate', 'Montantdevise', 'Idevise' );

	private static function party_names(): array {
		$names = array( 'ASSOCIE' => AC_DB::get_setting( 'company' )['associate_name'] ?: 'Associé unique' );
		foreach ( AC_DB::q( 'SELECT id, name, kind FROM {parties}' ) as $p ) {
			$names[ sprintf( '%s%04d', 'client' === $p['kind'] ? 'C' : 'F', $p['id'] ) ] = $p['name'];
		}
		return $names;
	}

	/** Fichier des écritures comptables (LPF art. A47 A-1) : [nom, contenu]. */
	public static function fec( array $fy ): array {
		$siren = str_replace( ' ', '', AC_DB::get_setting( 'company' )['siren'] ?: '000000000' );
		$rows  = AC_DB::q( "SELECT e.*, l.account, l.aux, l.aux_label, l.label line_label, l.debit_cents, l.credit_cents, a.label account_label
			FROM {entries} e JOIN {entry_lines} l ON l.entry_id=e.id LEFT JOIN {accounts} a ON a.number=l.account
			WHERE e.date BETWEEN ? AND ? ORDER BY CASE e.journal WHEN 'AN' THEN 0 ELSE 1 END, e.date, e.number, e.id, l.id", array( $fy['start_date'], $fy['end_date'] ) );
		$names = self::party_names();
		$d8    = function ( $s ) { return str_replace( '-', '', substr( (string) $s, 0, 10 ) ); };
		$cl    = function ( $s ) { return str_replace( array( "\t", "\n", "\r" ), ' ', (string) $s ); };
		$out   = implode( "\t", self::FEC_COLUMNS ) . "\r\n";
		foreach ( $rows as $r ) {
			$out .= implode( "\t", array( $r['journal'], AC_Rules::journals()[ $r['journal'] ] ?? $r['journal'], $r['number'] ?: sprintf( 'PROV%06d', $r['id'] ),
				$d8( $r['date'] ), $r['account'], $cl( $r['account_label'] ), $r['aux'] ?: '', $r['aux'] ? $cl( $names[ $r['aux'] ] ?? $r['aux_label'] ) : '',
				$cl( $r['piece_ref'] ?: $r['id'] ), $d8( $r['piece_date'] ?: $r['date'] ), $cl( $r['line_label'] ?: $r['label'] ), AC_Money::plain( $r['debit_cents'] ),
				AC_Money::plain( $r['credit_cents'] ), '', '', $d8( $r['validated_at'] ), '', '' ) ) . "\r\n";
		}
		return array( $siren . 'FEC' . str_replace( '-', '', $fy['end_date'] ) . '.txt', $out );
	}

	public static function csv( array $rows ): string {
		$h = fopen( 'php://temp', 'w+' );
		foreach ( $rows as $r ) {
			fputcsv( $h, array_values( $r ), ';', '"', '' );
		}
		rewind( $h );
		return "\xEF\xBB\xBF" . stream_get_contents( $h );
	}

	private static function e( $c ): float {
		return AC_Money::eur( $c );
	}

	public static function zip_names( string $zip ): array {
		$tmp = tempnam( get_temp_dir(), 'ac' );
		file_put_contents( $tmp, $zip );
		$z = new ZipArchive();
		$z->open( $tmp );
		$names = array();
		for ( $i = 0; $i < $z->numFiles; $i++ ) {
			$names[] = $z->getNameIndex( $i );
		}
		$z->close();
		@unlink( $tmp );
		return $names;
	}

	// ------------------------------------------------------------ états annuels
	public static function statements( array $fy, ?array $prep = null ): array {
		$prep   = $prep ?: AC_Closing::prepare( $fy['id'] );
		$inc    = $prep['compte_de_resultat'];
		$bs     = $prep['bilan'];
		$f      = array( 'AC_Money', 'fmt' );
		$status = 'closed' === $fy['status'] ? 'Exercice clôturé.' : ( $prep['definitif'] ? 'Prêt à clôturer.' : 'DOCUMENT PROVISOIRE : ' . $prep['statut'] . '.' );
		$period = "Exercice du {$fy['start_date']} au {$fy['end_date']}.";
		$side   = function ( $rows, $total, $label ) use ( $f ) {
			$t = array( array( 'Rubrique', 'Montant' ) );
			foreach ( $rows as $a ) {
				$t[] = array( $a['rubrique'], $f( $a['total'] ) );
			}
			$t[] = array( "Total $label", $f( $total ) );
			return $t;
		};
		$files = array();
		$files['bilan.pdf'] = AC_Pdf::build( 'Bilan', array( array( 'p', "$period $status" ), array( 'h', 'Actif' ), array( 'table', $side( $bs['actif'], $bs['total_actif'], 'actif' ), array( 3, 1 ), array( 1 ) ),
			array( 'h', 'Passif' ), array( 'table', $side( $bs['passif'], $bs['total_passif'], 'passif' ), array( 3, 1 ), array( 1 ) ) ) );
		$blocks = array( array( 'p', "$period $status" ) );
		foreach ( $inc['sections'] as $sec ) {
			if ( ! $sec['lines'] ) {
				continue;
			}
			$t = array( array( 'Compte', 'Libellé', 'Montant' ) );
			foreach ( $sec['lines'] as $l ) {
				$t[] = array( $l['account'], $l['label'], $f( $l['amount'] ) );
			}
			$t[]      = array( '', 'Total', $f( $sec['total'] ) );
			$blocks[] = array( 'h', $sec['label'] );
			$blocks[] = array( 'table', $t, array( 1, 4, 1.4 ), array( 2 ) );
		}
		$blocks[] = array( 'h', 'Synthèse' );
		$blocks[] = array( 'table', array( array( 'Synthèse', 'Montant' ), array( "Résultat d'exploitation", $f( $inc['resultat_exploitation'] ) ),
			array( 'Résultat financier', $f( $inc['resultat_financier'] ) ), array( 'Résultat exceptionnel', $f( $inc['resultat_exceptionnel'] ) ),
			array( 'Impôt sur les bénéfices', $f( $inc['impot'] ) ), array( 'Résultat net', $f( $inc['resultat_net'] ) ) ), array( 3, 1 ), array( 1 ) );
		$files['compte-de-resultat.pdf'] = AC_Pdf::build( 'Compte de résultat', $blocks );
		$t = array( array( 'Bien', 'Compte', 'Mise en service', 'Durée', 'Valeur brute', 'Dotation', 'Cumul', 'VNC' ) );
		foreach ( $prep['immobilisations'] as $a ) {
			$t[] = array( $a['label'], $a['account'], $a['in_service_date'], $a['duration_years'] . ' ans', $f( $a['cost_cents'] ), $f( $a['dotation_cents'] ),
				$f( $a['cumul_fin_cents'] ), $f( $a['vnc_fin_cents'] ) );
		}
		$files['immobilisations-amortissements.pdf'] = AC_Pdf::build( 'Tableau des immobilisations et des amortissements', array( array( 'p', $period ),
			array( 'table', $t, array( 3, 1.2, 1.4, 0.9, 1.4, 1.4, 1.4, 1.4 ), array( 4, 5, 6, 7 ) ) ) );
		$cd = $prep['creances_dettes'];
		$ct = function ( $rows ) use ( $f ) {
			$t = array( array( 'Compte', 'Tiers', 'Montant' ) );
			foreach ( $rows as $r ) {
				$t[] = array( $r['account'], $r['tiers'] ?: $r['label'], $f( $r['solde'] ) );
			}
			return $t;
		};
		$files['creances-dettes.pdf'] = AC_Pdf::build( 'État des créances et des dettes', array( array( 'p', "Au {$fy['end_date']}. {$cd['note']}" ),
			array( 'h', 'Créances' ), array( 'table', $ct( $cd['creances'] ), array( 1, 3, 1.2 ), array( 2 ) ),
			array( 'h', 'Dettes' ), array( 'table', $ct( $cd['dettes'] ), array( 1, 3, 1.2 ), array( 2 ) ) ) );
		$i = $prep['impot'];
		$t = array( array( 'Élément', 'Montant' ), array( 'Résultat comptable avant impôt', $f( $i['resultat_comptable_cents'] ) ) );
		foreach ( $i['reintegrations'] as $r ) {
			$t[] = array( 'Réintégration : ' . $r['libelle'], $f( $r['montant_cents'] ) );
		}
		$t[] = array( 'Déficits antérieurs imputés', $f( -$i['deficits_imputes_cents'] ) );
		$t[] = array( 'Résultat fiscal', $f( $i['resultat_fiscal_cents'] ) );
		foreach ( $i['tranches'] as $tr ) {
			$t[] = array( $tr['libelle'], $f( $tr['impot_cents'] ) );
		}
		$t[] = array( 'IS estimé', $f( $i['is_estime_cents'] ) );
		$t[] = array( 'Acomptes versés', $f( $i['acomptes_payes_cents'] ) );
		$t[] = array( 'Solde estimé', $f( $i['solde_estime_cents'] ) );
		$files['impot-societes.pdf'] = AC_Pdf::build( "Détermination de l'impôt sur les sociétés", array( array( 'p', $period ), array( 'table', $t, array( 3, 1 ), array( 1 ) ) ) );
		$blocks = array( array( 'p', $prep['liasse']['note'] ) );
		foreach ( $prep['liasse'] as $k => $v ) {
			if ( 'note' === $k ) {
				continue;
			}
			$t = array( array( 'Poste', 'Montant' ) );
			foreach ( $v as $row ) {
				$t[] = array( $row[0], $f( $row[1] ) );
			}
			$blocks[] = array( 'h', $k );
			$blocks[] = array( 'table', $t, array( 3, 1 ), array( 1 ) );
		}
		$files['elements-liasse-fiscale.pdf'] = AC_Pdf::build( 'Éléments pour la liasse fiscale (2065 et annexes)', $blocks );
		$g      = AC_Liasse::guide( $fy );
		$blocks = array( array( 'p', $g['avertissement'] ) );
		$t      = array( array( 'Contrôle', 'Résultat', 'Détail' ) );
		foreach ( $g['controles'] as $c ) {
			$t[] = array( $c['label'], $c['ok'] ? 'OK' : 'À corriger', $c['detail'] );
		}
		$blocks[] = array( 'table', $t, array( 2.4, 0.8, 2 ) );
		foreach ( $g['forms'] as $form ) {
			$blocks[] = array( 'h', $form['title'] );
			$blocks[] = array( 'p', $form['intro'] );
			if ( $form['lines'] ) {
				$t = array( array( 'Case', 'Libellé', 'À saisir', 'Remarque' ) );
				foreach ( $form['lines'] as $l ) {
					$t[] = array( $l['code'] ?: '-', $l['label'], null === $l['value'] ? '' : number_format( $l['value'], 0, ',', ' ' ), $l['note'] );
				}
				$blocks[] = array( 'table', $t, array( 0.5, 2.6, 0.9, 2.2 ), array( 2 ) );
			}
		}
		$files['guide-liasse.pdf'] = AC_Pdf::build( 'Guide de liasse fiscale, case par case', $blocks );
		$ob = $prep['obligations'];
		$t  = array( array( 'Obligation', 'Échéance', 'Remarque' ) );
		foreach ( $ob['liste'] as $o ) {
			$t[] = array( $o['obligation'], $o['echeance'], $o['note'] ?? '' );
		}
		$files['obligations-annuelles.pdf'] = AC_Pdf::build( 'Obligations annuelles applicables', array(
			array( 'p', "Régime d'imposition : {$ob['regime_is']}. Petite société sous les seuils micro (sens comptable) : " . ( $ob['micro_entreprise'] ? 'oui' : 'non' ) . '.' ),
			array( 'table', $t, array( 2, 1.3, 2 ) ) ) );
		$aff     = $prep['affectation_proposee'];
		$company = AC_DB::get_setting( 'company' );
		$files['projet-decision-associe-unique.pdf'] = AC_Pdf::build( "Projet de décision de l'associé unique", array(
			array( 'p', "L'associé unique de la société {$company['name']}, {$company['legal_form']} au capital de " . $f( $company['capital_cents'] )
				. ", après avoir pris connaissance des comptes de l'exercice clos le {$fy['end_date']}, décide :" ),
			array( 'p', "Première décision : approbation des comptes annuels de l'exercice faisant apparaître un résultat de " . $f( $aff['resultat_cents'] ) . '.' ),
			array( 'p', 'Deuxième décision : affectation du résultat : réserve légale ' . $f( $aff['reserve_legale_cents'] ) . ', report à nouveau ' . $f( $aff['report_a_nouveau_cents'] ) . '.' ),
			array( 'p', 'Troisième décision : rappel des dividendes distribués au titre des trois exercices précédents : néant (à compléter).' ),
			array( 'p', "Fait le ____________, l'associé unique : ____________" ),
			array( 'p', 'Projet généré automatiquement à adapter. ' . $aff['note'] ) ) );
		return $files;
	}

	public static function ledger_files( string $start, string $end ): array {
		$f   = array( 'AC_Money', 'fmt' );
		$tb  = AC_Reports::trial_balance( $start, $end );
		$bal = array( array( 'Compte', 'Libellé', 'Débit', 'Crédit', 'Solde débiteur', 'Solde créditeur' ) );
		$balp = array( $bal[0] );
		foreach ( $tb['rows'] as $r ) {
			$bal[]  = array( $r['account'], $r['label'], self::e( $r['debit'] ), self::e( $r['credit'] ), self::e( $r['solde_debiteur'] ), self::e( $r['solde_crediteur'] ) );
			$balp[] = array( $r['account'], $r['label'], $f( $r['debit'] ), $f( $r['credit'] ), $r['solde_debiteur'] ? $f( $r['solde_debiteur'] ) : '', $r['solde_crediteur'] ? $f( $r['solde_crediteur'] ) : '' );
		}
		$bal[]  = array( 'Total', '', self::e( $tb['totals']['debit'] ), self::e( $tb['totals']['credit'] ), '', '' );
		$balp[] = array( 'Total', '', $f( $tb['totals']['debit'] ), $f( $tb['totals']['credit'] ), '', '' );
		$gl = array( array( 'Compte', 'Libellé compte', 'Date', 'Journal', 'N° écriture', 'Pièce', 'Libellé', 'Débit', 'Crédit', 'Solde cumulé', 'Statut' ) );
		foreach ( AC_Reports::general_ledger( $start, $end ) as $a ) {
			foreach ( $a['lines'] as $l ) {
				$gl[] = array( $a['account'], $a['label'], $l['date'], $l['journal'], $l['number'] ?: 'provisoire', $l['piece_ref'], $l['label'],
					self::e( $l['debit_cents'] ), self::e( $l['credit_cents'] ), self::e( $l['cumul'] ), $l['status'] );
			}
		}
		$jr  = array( array( 'Date', 'Journal', 'N° écriture', 'Pièce', 'Compte', 'Tiers', 'Libellé', 'Débit', 'Crédit', 'Statut' ) );
		$jrp = array( array( 'Date', 'Jal', 'N°', 'Pièce', 'Compte', 'Libellé', 'Débit', 'Crédit' ) );
		foreach ( AC_Reports::journal( $start, $end ) as $e ) {
			foreach ( $e['lines'] as $l ) {
				$jr[]  = array( $e['date'], $e['journal'], $e['number'] ?: 'provisoire', $e['piece_ref'], $l['account'], $l['aux'] ?: '', $l['label'],
					self::e( $l['debit_cents'] ), self::e( $l['credit_cents'] ), $e['status'] );
				$jrp[] = array( $e['date'], $e['journal'], $e['number'] ?: 'prov.', $e['piece_ref'], $l['account'], $l['label'],
					$l['debit_cents'] ? $f( $l['debit_cents'] ) : '', $l['credit_cents'] ? $f( $l['credit_cents'] ) : '' );
			}
		}
		return array(
			'balance-generale.xlsx' => AC_Xlsx::build( array( 'Balance' => $bal ) ), 'grand-livre.xlsx' => AC_Xlsx::build( array( 'Grand livre' => $gl ) ),
			'livre-journal.xlsx' => AC_Xlsx::build( array( 'Journal' => $jr ) ), 'balance-generale.csv' => self::csv( $bal ), 'grand-livre.csv' => self::csv( $gl ),
			'livre-journal.csv' => self::csv( $jr ),
			'balance-generale.pdf' => AC_Pdf::build( "Balance générale du $start au $end", array( array( 'table', $balp, array( 1, 3, 1.3, 1.3, 1.3, 1.3 ), array( 2, 3, 4, 5 ) ) ) ),
			'livre-journal.pdf' => AC_Pdf::build( "Livre journal du $start au $end", array( array( 'table', $jrp, array( 1.3, 0.6, 1.6, 1.3, 1, 3.4, 1.3, 1.3 ), array( 6, 7 ) ) ) ),
		);
	}

	public static function vat_files( string $start, string $end ): array {
		$f    = array( 'AC_Money', 'fmt' );
		$res  = AC_Vat::prepare( $start, $end );
		$data = $res['data'];
		$L    = $data['lignes'];
		$t    = array( array( 'Ligne', 'Libellé', 'Base HT', 'Taxe' ) );
		foreach ( $L as $code => $l ) {
			if ( ! $l['base_cents'] && ! $l['taxe_cents'] && ! in_array( (string) $code, array( '16', '23', '28', '32' ), true ) ) {
				continue;
			}
			$t[] = array( $code, $l['libelle'], null !== $l['base_cents'] ? $f( $l['base_cents'] ) : '', null !== $l['taxe_cents'] ? $f( $l['taxe_cents'] ) : '' );
		}
		$blocks = array( array( 'p', "Période du $start au $end. Formulaire {$res['form']}. Statut : " . ( $res['statut'] ?? $res['return']['status'] ) . '.' ),
			array( 'table', $t, array( 0.6, 4, 1.3, 1.3 ), array( 2, 3 ) ) );
		if ( 'CA12' === $res['form'] ) {
			$c  = AC_Vat::ca12_view( $data, $end );
			$ct = array( array( 'Rubrique', 'Montant' ) );
			foreach ( $c['rubriques'] as $r ) {
				$ct[] = array( $r['libelle'], $f( $r['montant_cents'] ) );
			}
			$blocks[] = array( 'h', 'Récapitulatif CA12' );
			$blocks[] = array( 'table', $ct, array( 3, 1 ), array( 1 ) );
			$blocks[] = array( 'p', $c['note'] );
		}
		if ( $data['non_recuperable_cents'] ) {
			$blocks[] = array( 'p', 'TVA non récupérable sur la période (incluse dans les charges) : ' . $f( $data['non_recuperable_cents'] ) . '.' );
		}
		if ( $res['issues'] ) {
			$it = array( array( 'Niveau', 'Message' ) );
			foreach ( $res['issues'] as $i ) {
				$it[] = array( $i['niveau'], $i['message'] );
			}
			$blocks[] = array( 'h', 'Anomalies à vérifier' );
			$blocks[] = array( 'table', $it, array( 1, 6 ) );
		}
		$blocks[] = array( 'p', "Reportez ces montants sur votre espace professionnel impots.gouv.fr. Aucune déclaration ni paiement n'est transmis automatiquement." );
		$det = array( array( 'Ligne', 'Date', 'Libellé', 'Pièce', 'Base HT', 'Taxe', 'Provisoire' ) );
		$sum = array( array( 'Ligne', 'Libellé', 'Base HT', 'Taxe' ) );
		foreach ( $L as $code => $l ) {
			$sum[] = array( (string) $code, $l['libelle'], null !== $l['base_cents'] ? self::e( $l['base_cents'] ) : '', null !== $l['taxe_cents'] ? self::e( $l['taxe_cents'] ) : '' );
			foreach ( $l['detail'] as $dd ) {
				$det[] = array( (string) $code, $dd['date'], $dd['libelle'], $dd['piece'], self::e( $dd['base_cents'] ), self::e( $dd['taxe_cents'] ), $dd['provisoire'] ? 'oui' : '' );
			}
		}
		return array( 'recapitulatif-tva.pdf' => AC_Pdf::build( "Déclaration de TVA {$res['form']}", $blocks ),
			'tva-detail.xlsx' => AC_Xlsx::build( array( 'Rubriques' => $sum, 'Détail' => $det ) ), 'tva-detail.csv' => self::csv( $det ) );
	}

	private static function documents( string $start, string $end ): array {
		$out = array();
		foreach ( AC_DB::q( 'SELECT * FROM {documents} WHERE id IN (SELECT document_id FROM {entries} WHERE date BETWEEN ? AND ? AND document_id IS NOT NULL
			UNION SELECT document_id FROM {expenses} WHERE date BETWEEN ? AND ? AND document_id IS NOT NULL
			UNION SELECT document_id FROM {sales_invoices} WHERE issue_date BETWEEN ? AND ? AND document_id IS NOT NULL)', array( $start, $end, $start, $end, $start, $end ) ) as $d ) {
			$out[ 'justificatifs/' . $d['path'] ] = array( 'file' => AC_Services::document_path( $d ) );
		}
		return $out;
	}

	private static function prefix( string $p, array $files ): array {
		$out = array();
		foreach ( $files as $k => $v ) {
			$out[ $p . $k ] = $v;
		}
		return $out;
	}

	public static function vat_dossier( string $start, string $end ): string {
		$lf = self::ledger_files( $start, $end );
		return AC_Xlsx::zip( self::prefix( 'tva/', self::vat_files( $start, $end ) )
			+ self::prefix( 'ecritures/', array( 'livre-journal.xlsx' => $lf['livre-journal.xlsx'], 'livre-journal.pdf' => $lf['livre-journal.pdf'] ) )
			+ self::documents( $start, $end ) );
	}

	public static function month_dossier( string $month ): string {
		list( $s, $e ) = AC_Checks::month_bounds( $month );
		$res = AC_Checks::verify_month( $month );
		$it  = array( array( 'Niveau', 'Message' ) );
		foreach ( $res['issues'] as $i ) {
			$it[] = array( $i['niveau'], $i['message'] );
		}
		$files = self::prefix( 'ecritures/', self::ledger_files( $s, $e ) );
		$files['controles.pdf'] = AC_Pdf::build( "Vérification du mois $month", array( array( 'p', $res['issues'] ? count( $res['issues'] ) . ' point(s) relevé(s).' : 'Aucune anomalie.' ),
			array( 'table', $it, array( 1, 6 ) ) ) );
		$bank = array( array( 'Date', 'Tiers', 'Libellé', 'Montant', 'Statut', 'Commentaire' ) );
		foreach ( AC_DB::q( 'SELECT * FROM {bank_transactions} WHERE date BETWEEN ? AND ? ORDER BY date', array( $s, $e ) ) as $t ) {
			$bank[] = array( $t['date'], $t['counterparty'], $t['label'], self::e( $t['amount_cents'] ), $t['status'], $t['comment'] );
		}
		$exp = array( array( 'Date', 'Fournisseur', 'Libellé', 'HT', 'TVA', 'TTC', 'Catégorie', 'Paiement', 'Justificatif', 'Statut' ) );
		foreach ( AC_DB::q( 'SELECT x.*, c.name cat FROM {expenses} x LEFT JOIN {categories} c ON c.id=x.category_id WHERE x.date BETWEEN ? AND ? ORDER BY date', array( $s, $e ) ) as $x ) {
			$exp[] = array( $x['date'], $x['supplier_name'], $x['label'], self::e( $x['ht_cents'] ), self::e( $x['vat_cents'] ), self::e( $x['ttc_cents'] ), $x['cat'],
				$x['payment_method'], $x['document_id'] ? 'oui' : 'non', $x['status'] );
		}
		$files['banque.xlsx']   = AC_Xlsx::build( array( 'Opérations' => $bank ) );
		$files['depenses.xlsx'] = AC_Xlsx::build( array( 'Dépenses' => $exp ) );
		return AC_Xlsx::zip( $files + self::documents( $s, $e ) );
	}

	public static function annual_dossier( array $fy ): string {
		$prep  = AC_Closing::prepare( $fy['id'] );
		$fy    = $prep['exercice'];
		$files = self::prefix( 'etats-financiers/', self::statements( $fy, $prep ) ) + self::prefix( 'livres/', self::ledger_files( $fy['start_date'], $fy['end_date'] ) );
		list( $name, $content ) = self::fec( $fy );
		$files[ "fec/$name" ]        = $content;
		$files['fec/LISEZMOI.txt'] = "Fichier des écritures comptables au format texte, séparateur tabulation, encodage UTF-8, montants avec virgule décimale (LPF art. A47 A-1).\n";
		foreach ( AC_DB::q( 'SELECT * FROM {vat_returns} WHERE period_start>=? AND period_end<=? ORDER BY period_start', array( $fy['start_date'], $fy['end_date'] ) ) as $r ) {
			if ( 'preparee' === $r['status'] && 'closed' === $fy['status'] ) {
				continue;
			}
			$files += self::prefix( 'tva/' . substr( $r['period_start'], 0, 7 ) . '/', self::vat_files( $r['period_start'], $r['period_end'] ) );
		}
		$ann = AC_Vat::compute( $fy['start_date'], $fy['end_date'] );
		$t   = array( array( 'Ligne', 'Libellé', 'Base', 'Taxe' ) );
		foreach ( $ann['lignes'] as $c => $l ) {
			$t[] = array( (string) $c, $l['libelle'], null !== $l['base_cents'] ? self::e( $l['base_cents'] ) : '', null !== $l['taxe_cents'] ? self::e( $l['taxe_cents'] ) : '' );
		}
		$files['tva/recapitulatif-annuel.xlsx'] = AC_Xlsx::build( array( 'TVA annuelle' => $t ) );
		$inv = array( array( 'Date', 'Journal', 'N°', 'Libellé', 'Compte', 'Débit', 'Crédit' ) );
		foreach ( AC_DB::q( "SELECT e.*, l.account, l.debit_cents, l.credit_cents FROM {entries} e JOIN {entry_lines} l ON l.entry_id=e.id
			WHERE e.date=? AND e.source_type IN ('amortissement','is','manuel') ORDER BY e.id, l.id", array( $fy['end_date'] ) ) as $r ) {
			$inv[] = array( $r['date'], $r['journal'], $r['number'], $r['label'], $r['account'], self::e( $r['debit_cents'] ), self::e( $r['credit_cents'] ) );
		}
		$files['ecritures-de-cloture.xlsx'] = AC_Xlsx::build( array( 'Inventaire' => $inv ) );
		$files['controles.json']            = wp_json_encode( array( 'statut' => $prep['statut'], 'anomalies' => $prep['anomalies'],
			'informations_manquantes' => $prep['informations_manquantes'] ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		$as  = AC_Services::associate_summary( $fy['end_date'] );
		$cc  = array( array( 'Date', 'Libellé', 'Débit', 'Crédit' ) );
		foreach ( $as['mouvements'] as $m ) {
			$cc[] = array( $m['date'], $m['line_label'] ?: $m['label'], self::e( $m['debit_cents'] ), self::e( $m['credit_cents'] ) );
		}
		$cc[] = array( '', 'Solde (créditeur = dû par la société)', '', self::e( $as['solde_cents'] ) );
		$files['compte-courant-associe.xlsx'] = AC_Xlsx::build( array( '455' => $cc ) );
		return AC_Xlsx::zip( $files + self::documents( $fy['start_date'], $fy['end_date'] ) );
	}
}

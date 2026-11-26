<?php
/**
 * Référentiel comptable et fiscal, versionné par date d'application.
 *
 * Chaque règle porte une période de validité : un exercice est toujours calculé avec
 * les règles en vigueur à ses dates. Pour une évolution réglementaire, ajouter une
 * entrée datée et fermer la précédente (valid_to), sans jamais la supprimer.
 */
defined( 'ABSPATH' ) || exit;

class AC_Rules {

	const VAT_COLLECTED = '445710';
	const VAT_COLLECTED_PENDING = '445870';
	const VAT_DEDUCTIBLE = '445660';
	const VAT_DEDUCTIBLE_ASSETS = '445620';
	const VAT_DEDUCTIBLE_PENDING = '445860';
	const VAT_DEDUCTIBLE_AUTOLIQ = '445662';
	const VAT_DUE_AUTOLIQ = '445200';
	const VAT_TO_PAY = '445510';
	const VAT_CREDIT = '445670';
	const BANK = '512000';
	const CLIENTS = '411000';
	const SUPPLIERS = '401000';
	const SUPPLIERS_ASSETS = '404000';
	const ASSOCIATE = '455000';
	const SUSPENSE = '471000';

	public static function rules() {
		return array(
			// Barème kilométrique (deux roues motorisés et voitures), revenus 2024, reconduit en 2025.
			// Tranches : [plafond km, coefficient, fixe en centimes] ; à vérifier chaque année (BOI-BAREME-000001).
			'bareme_ik' => array(
				array(
					'valid_from' => '2024-01-01',
					'valid_to' => null,
					'value' => array(
						'cyclo' => array( 'all' => array( array( 3000, '0.315', 0 ), array( 6000, '0.079', 71100 ), array( null, '0.198', 0 ) ) ),
						'moto' => array(
							'1-2' => array( array( 3000, '0.395', 0 ), array( 6000, '0.099', 89100 ), array( null, '0.248', 0 ) ),
							'3-5' => array( array( 3000, '0.468', 0 ), array( 6000, '0.082', 115800 ), array( null, '0.275', 0 ) ),
							'6+' => array( array( 3000, '0.606', 0 ), array( 6000, '0.079', 158300 ), array( null, '0.343', 0 ) ),
						),
						'voiture' => array(
							'3' => array( array( 5000, '0.529', 0 ), array( 20000, '0.316', 106500 ), array( null, '0.370', 0 ) ),
							'4' => array( array( 5000, '0.606', 0 ), array( 20000, '0.340', 133000 ), array( null, '0.407', 0 ) ),
							'5' => array( array( 5000, '0.636', 0 ), array( 20000, '0.357', 139500 ), array( null, '0.427', 0 ) ),
							'6' => array( array( 5000, '0.665', 0 ), array( 20000, '0.374', 145700 ), array( null, '0.447', 0 ) ),
							'7+' => array( array( 5000, '0.697', 0 ), array( 20000, '0.394', 151500 ), array( null, '0.470', 0 ) ),
						),
					),
				),
			),
		'is_taux_normal' => array(
			array(
				'valid_from' => '2022-01-01',
				'valid_to' => null,
				'value' => '25',
			),
		),
		'is_taux_reduit' => array(
			array(
				'valid_from' => '2022-01-01',
				'valid_to' => null,
				'value' => '15',
			),
		),
		'is_plafond_taux_reduit_cents' => array(
			array(
				'valid_from' => '2023-01-01',
				'valid_to' => null,
				'value' => 4250000,
			),
			array(
				'valid_from' => '2022-01-01',
				'valid_to' => '2022-12-31',
				'value' => 3812000,
			),
		),
		'is_ca_max_taux_reduit_cents' => array(
			array(
				'valid_from' => '2021-01-01',
				'valid_to' => null,
				'value' => 1000000000,
			),
		),
		'is_seuil_acomptes_cents' => array(
			array(
				'valid_from' => '2000-01-01',
				'valid_to' => null,
				'value' => 300000,
			),
		),
		'tva_taux' => array(
			array(
				'valid_from' => '2014-01-01',
				'valid_to' => null,
				'value' => array(
					'20',
					'10',
					'5.5',
					'2.1',
					'0',
				),
			),
		),
		'tva_seuil_cadeaux_ttc_cents' => array(
			array(
				'valid_from' => '2020-01-01',
				'valid_to' => null,
				'value' => 7300,
			),
		),
		'seuil_immobilisation_ht_cents' => array(
			array(
				'valid_from' => '2001-01-01',
				'valid_to' => null,
				'value' => 50000,
			),
		),
		'ca12_acompte_juillet_pct' => array(
			array(
				'valid_from' => '2015-01-01',
				'valid_to' => null,
				'value' => '55',
			),
		),
		'ca12_acompte_decembre_pct' => array(
			array(
				'valid_from' => '2015-01-01',
				'valid_to' => null,
				'value' => '40',
			),
		),
		'ca12_seuil_acomptes_cents' => array(
			array(
				'valid_from' => '2015-01-01',
				'valid_to' => null,
				'value' => 100000,
			),
		),
		'micro_seuils' => array(
			array(
				'valid_from' => '2024-01-01',
				'valid_to' => null,
				'value' => array(
					'bilan_cents' => 45000000,
					'ca_cents' => 90000000,
					'salaries' => 10,
				),
			),
		),
	);
	}

	/** Valeur d'une règle en vigueur à une date (AAAA-MM-JJ). */
	public static function get( string $name, string $on ) {
		$d = substr( $on, 0, 10 );
		foreach ( self::rules()[ $name ] as $r ) {
			if ( $r['valid_from'] <= $d && ( null === $r['valid_to'] || $d <= $r['valid_to'] ) ) {
				return $r['value'];
			}
		}
		throw new AC_Error( "Aucune règle $name en vigueur au $d" );
	}

	public static function accounts() {
		return array(
		array(
			'101300',
			'Capital souscrit, appelé, versé',
		),
		array(
			'101100',
			'Capital souscrit, non appelé',
		),
		array(
			'106100',
			'Réserve légale',
		),
		array(
			'106800',
			'Autres réserves',
		),
		array(
			'110000',
			'Report à nouveau (solde créditeur)',
		),
		array(
			'119000',
			'Report à nouveau (solde débiteur)',
		),
		array(
			'120000',
			'Résultat de l\'exercice (bénéfice)',
		),
		array(
			'129000',
			'Résultat de l\'exercice (perte)',
		),
		array(
			'109000',
			'Actionnaires : capital souscrit, non appelé',
		),
		array(
			'205000',
			'Logiciels, concessions, brevets',
		),
		array(
			'215400',
			'Matériel industriel',
		),
		array(
			'218300',
			'Matériel de bureau et informatique',
		),
		array(
			'218400',
			'Mobilier',
		),
		array(
			'218800',
			'Immobilisations corporelles diverses',
		),
		array(
			'280500',
			'Amortissements logiciels',
		),
		array(
			'281830',
			'Amortissements matériel de bureau et informatique',
		),
		array(
			'281840',
			'Amortissements mobilier',
		),
		array(
			'281880',
			'Amortissements immobilisations diverses',
		),
		array(
			'275000',
			'Dépôts et cautionnements versés',
		),
		array(
			'401000',
			'Fournisseurs',
		),
		array(
			'404000',
			'Fournisseurs d\'immobilisations',
		),
		array(
			'408000',
			'Fournisseurs, factures non parvenues',
		),
		array(
			'411000',
			'Clients',
		),
		array(
			'418100',
			'Clients, factures à établir',
		),
		array(
			'419100',
			'Clients, avances et acomptes reçus',
		),
		array(
			'421000',
			'Personnel, rémunérations dues',
		),
		array(
			'431000',
			'Sécurité sociale',
		),
		array(
			'444000',
			'État, impôt sur les bénéfices',
		),
		array(
			'445510',
			'TVA à décaisser',
		),
		array(
			'445200',
			'TVA due intracommunautaire et autoliquidée',
		),
		array(
			'445620',
			'TVA déductible sur immobilisations',
		),
		array(
			'445660',
			'TVA déductible sur autres biens et services',
		),
		array(
			'445662',
			'TVA déductible autoliquidée (UE et hors UE)',
		),
		array(
			'445670',
			'Crédit de TVA à reporter',
		),
		array(
			'445710',
			'TVA collectée',
		),
		array(
			'445860',
			'TVA sur factures non parvenues / déductible en attente',
		),
		array(
			'445870',
			'TVA collectée en attente d\'encaissement',
		),
		array(
			'445800',
			'TVA à régulariser',
		),
		array(
			'445810',
			'Acomptes de TVA (régime simplifié)',
		),
		array(
			'447000',
			'Autres impôts, taxes et versements assimilés',
		),
		array(
			'455000',
			'Associé, compte courant',
		),
		array(
			'456200',
			'Apporteurs, capital appelé non versé',
		),
		array(
			'457000',
			'Associés, dividendes à payer',
		),
		array(
			'467000',
			'Autres comptes débiteurs ou créditeurs',
		),
		array(
			'471000',
			'Compte d\'attente',
		),
		array(
			'486000',
			'Charges constatées d\'avance',
		),
		array(
			'487000',
			'Produits constatés d\'avance',
		),
		array(
			'512000',
			'Banque Qonto',
		),
		array(
			'580000',
			'Virements internes',
		),
		array(
			'601000',
			'Achats stockés, matières premières',
		),
		array(
			'604000',
			'Achats de prestations de services',
		),
		array(
			'606100',
			'Fournitures non stockables (eau, énergie)',
		),
		array(
			'606300',
			'Fournitures d\'entretien et petit équipement',
		),
		array(
			'606400',
			'Fournitures administratives',
		),
		array(
			'611000',
			'Sous-traitance générale',
		),
		array(
			'613200',
			'Locations immobilières',
		),
		array(
			'613500',
			'Locations mobilières',
		),
		array(
			'615600',
			'Maintenance',
		),
		array(
			'616000',
			'Primes d\'assurance',
		),
		array(
			'618100',
			'Documentation générale',
		),
		array(
			'618500',
			'Frais de colloques, séminaires, conférences',
		),
		array(
			'622600',
			'Honoraires',
		),
		array(
			'622700',
			'Frais d\'actes et de contentieux',
		),
		array(
			'623100',
			'Annonces et insertions',
		),
		array(
			'623400',
			'Cadeaux à la clientèle',
		),
		array(
			'623600',
			'Catalogues et imprimés',
		),
		array(
			'623800',
			'Publicité, pourboires, dons courants',
		),
		array(
			'625100',
			'Voyages et déplacements',
		),
		array(
			'625600',
			'Missions',
		),
		array(
			'625700',
			'Réceptions',
		),
		array(
			'626000',
			'Frais postaux et de télécommunications',
		),
		array(
			'627000',
			'Services bancaires et assimilés',
		),
		array(
			'628100',
			'Concours divers (cotisations)',
		),
		array(
			'651000',
			'Redevances pour concessions, logiciels et abonnements',
		),
		array(
			'635110',
			'Cotisation foncière des entreprises',
		),
		array(
			'637000',
			'Autres impôts, taxes (autres organismes)',
		),
		array(
			'641000',
			'Rémunérations du personnel',
		),
		array(
			'641100',
			'Rémunération du dirigeant',
		),
		array(
			'645000',
			'Charges de sécurité sociale et de prévoyance',
		),
		array(
			'661000',
			'Charges d\'intérêts',
		),
		array(
			'666000',
			'Pertes de change',
		),
		array(
			'671200',
			'Pénalités, amendes fiscales et pénales',
		),
		array(
			'671800',
			'Autres charges exceptionnelles sur opérations de gestion',
		),
		array(
			'675000',
			'Valeurs comptables des éléments d\'actif cédés',
		),
		array(
			'681100',
			'Dotations aux amortissements des immobilisations',
		),
		array(
			'695000',
			'Impôts sur les bénéfices',
		),
		array(
			'706000',
			'Prestations de services',
		),
		array(
			'707000',
			'Ventes de marchandises',
		),
		array(
			'708000',
			'Produits des activités annexes',
		),
		array(
			'708500',
			'Ports et frais accessoires facturés',
		),
		array(
			'709000',
			'Rabais, remises et ristournes accordés',
		),
		array(
			'758000',
			'Produits divers de gestion courante',
		),
		array(
			'766000',
			'Gains de change',
		),
		array(
			'768000',
			'Autres produits financiers',
		),
		array(
			'775000',
			'Produits des cessions d\'éléments d\'actif',
		),
		array(
			'778000',
			'Autres produits exceptionnels',
		),
	);
	}

	public static function categories() {
		return array(
			array(
				'code' => 'ik',
				'name' => 'Indemnités kilométriques (véhicule personnel)',
				'account' => '625100',
				'vat_recovery' => 0,
				'deductible' => 100,
				'rule' => 'ik',
				'help' => 'Trajets professionnels avec votre véhicule personnel, remboursés selon le barème fiscal. Sans TVA. Incompatible avec la prise en charge par la société du carburant ou de l\'assurance du même véhicule.',
			),
		array(
			'code' => 'repas',
			'name' => 'Restaurants et repas professionnels',
			'account' => '625600',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'repas',
			'help' => 'Repas d\'affaires ou en déplacement. TVA récupérable si la facture est au nom de la société et la dépense dans l\'intérêt de l\'entreprise. Un repas personnel n\'est pas déductible.',
		),
		array(
			'code' => 'invitations',
			'name' => 'Invitations et relations clients',
			'account' => '625700',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'invitation',
			'help' => 'Repas ou réception avec clients ou prospects. Noter le nom des invités et l\'objet en commentaire.',
		),
		array(
			'code' => 'cadeaux',
			'name' => 'Cadeaux clients',
			'account' => '623400',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'cadeau',
			'help' => 'TVA récupérable seulement si la valeur unitaire TTC ne dépasse pas 73 € par bénéficiaire et par an. Indiquer le bénéficiaire.',
		),
		array(
			'code' => 'logiciels',
			'name' => 'Abonnements logiciels',
			'account' => '651000',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'logiciel',
			'help' => 'Abonnements SaaS. Fournisseur étranger sans TVA française : autoliquidation (TVA due et déductible).',
		),
		array(
			'code' => 'hebergement',
			'name' => 'Hébergement et noms de domaine',
			'account' => '626000',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'standard',
		),
		array(
			'code' => 'communication',
			'name' => 'Communication et publicité',
			'account' => '623100',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'standard',
		),
		array(
			'code' => 'materiel',
			'name' => 'Matériel informatique (petit équipement)',
			'account' => '606300',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'materiel',
			'help' => 'Matériel de moins de 500 € HT. Au delà, enregistrer une immobilisation.',
		),
		array(
			'code' => 'deplacements',
			'name' => 'Déplacements professionnels',
			'account' => '625100',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'deplacement',
			'help' => 'Train, avion, hôtel. TVA non récupérable sur le transport de personnes et sur l\'hébergement des dirigeants (CGI ann. II art. 206 IV).',
		),
		array(
			'code' => 'banque',
			'name' => 'Frais bancaires',
			'account' => '627000',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'standard',
			'help' => 'Abonnement Qonto (avec TVA). Les commissions sur opérations sont souvent exonérées.',
		),
		array(
			'code' => 'prestations',
			'name' => 'Prestations externes',
			'account' => '611000',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'standard',
		),
		array(
			'code' => 'honoraires',
			'name' => 'Honoraires (expert comptable, avocat)',
			'account' => '622600',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'standard',
		),
		array(
			'code' => 'fournitures',
			'name' => 'Fournitures',
			'account' => '606400',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'standard',
		),
		array(
			'code' => 'assurances',
			'name' => 'Assurances',
			'account' => '616000',
			'vat_recovery' => 0,
			'deductible' => 100,
			'rule' => 'sans_tva',
			'help' => 'Les primes d\'assurance sont exonérées de TVA.',
		),
		array(
			'code' => 'impots',
			'name' => 'Impôts et taxes (CFE, etc.)',
			'account' => '635110',
			'vat_recovery' => 0,
			'deductible' => 100,
			'rule' => 'sans_tva',
		),
		array(
			'code' => 'amendes',
			'name' => 'Amendes et pénalités',
			'account' => '671200',
			'vat_recovery' => 0,
			'deductible' => 0,
			'rule' => 'non_deductible',
			'help' => 'Jamais déductibles (CGI art. 39-2). Réintégrées dans le résultat fiscal.',
		),
		array(
			'code' => 'autres',
			'name' => 'Autres charges',
			'account' => '618100',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'standard',
		),
		array(
			'code' => 'immobilisation',
			'name' => 'Immobilisations',
			'account' => '218300',
			'vat_recovery' => 100,
			'deductible' => 100,
			'rule' => 'immobilisation',
			'is_asset' => 1,
			'help' => 'Bien durable de plus de 500 € HT : amorti sur sa durée d\'utilisation. TVA déductible sur immobilisations.',
		),
		array(
			'code' => 'personnel',
			'name' => 'Dépense personnelle (non professionnelle)',
			'account' => '455000',
			'vat_recovery' => 0,
			'deductible' => 0,
			'rule' => 'personnel',
			'help' => 'Dépense privée payée avec le compte de la société : ce n\'est pas une charge. Elle est inscrite au débit du compte courant d\'associé (la société avance l\'argent à l\'associé).',
		),
	);
	}

	public static function explanations() {
		return array(
			'ik' => 'Indemnités kilométriques : barème fiscal, sans TVA, inscrites au crédit de votre compte courant (la société vous les doit).',
		'repas' => 'Repas professionnel : charge déductible si intérêt de l\'entreprise justifié ; TVA récupérable sur facture au nom de la société.',
		'invitation' => 'Invitation clients : déductible et TVA récupérable si justifiée (nom des invités, objet).',
		'cadeau' => 'Cadeau client : TVA récupérable uniquement si valeur TTC par bénéficiaire et par an ≤ 73 €.',
		'logiciel' => 'Abonnement logiciel : charge déductible, TVA récupérable ; autoliquidation si fournisseur étranger.',
		'materiel' => 'Petit matériel (< 500 € HT) : charge immédiate.',
		'deplacement' => 'Transport de personnes : TVA non récupérable (sauf exceptions).',
		'standard' => 'Charge professionnelle courante : déductible, TVA récupérable sur facture conforme.',
		'sans_tva' => 'Opération sans TVA.',
		'non_deductible' => 'Charge non déductible fiscalement : réintégrée au résultat fiscal.',
		'immobilisation' => 'Immobilisation : amortie ; TVA déductible sur immobilisations (ligne 19 CA3).',
		'personnel' => 'Dépense personnelle : non déductible, imputée au compte courant d\'associé.',
	);
	}

	public static function journals() {
		return array(
		'AC' => 'Journal des achats',
		'VT' => 'Journal des ventes',
		'BQ' => 'Journal de banque Qonto',
		'OD' => 'Opérations diverses',
		'AN' => 'À nouveaux',
	);
	}

	public static function asset_types() {
		return array(
		'218300' => array(
			'label' => 'Matériel informatique',
			'years' => 3,
			'dep_account' => '281830',
		),
		'218400' => array(
			'label' => 'Mobilier',
			'years' => 10,
			'dep_account' => '281840',
		),
		'205000' => array(
			'label' => 'Logiciel acquis',
			'years' => 1,
			'dep_account' => '280500',
		),
		'218800' => array(
			'label' => 'Autre équipement',
			'years' => 5,
			'dep_account' => '281880',
		),
		'215400' => array(
			'label' => 'Matériel professionnel',
			'years' => 5,
			'dep_account' => '281880',
		),
	);
	}
}

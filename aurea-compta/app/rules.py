"""Référentiel comptable et fiscal, versionné par date d'application.

Chaque règle fiscale porte une période de validité (valid_from / valid_to).
Les calculs d'un exercice utilisent la version en vigueur à la date concernée :
une évolution réglementaire future ne modifie jamais un exercice passé.
Pour ajouter une nouvelle règle : ajouter une entrée avec sa date de début
et fermer la précédente (valid_to), sans supprimer l'ancienne.
"""
from datetime import date

# --------------------------------------------------------------- règles datées
RULES = {
    # Impôt sur les sociétés (CGI art. 219)
    "is_taux_normal": [
        {"valid_from": "2022-01-01", "valid_to": None, "value": "25"},
    ],
    "is_taux_reduit": [
        {"valid_from": "2022-01-01", "valid_to": None, "value": "15"},
    ],
    "is_plafond_taux_reduit_cents": [  # bénéfice imposé à 15 % (12 mois)
        {"valid_from": "2023-01-01", "valid_to": None, "value": 4_250_000},
        {"valid_from": "2022-01-01", "valid_to": "2022-12-31", "value": 3_812_000},
    ],
    "is_ca_max_taux_reduit_cents": [  # CA HT < 10 M€
        {"valid_from": "2021-01-01", "valid_to": None, "value": 1_000_000_000},
    ],
    "is_seuil_acomptes_cents": [  # pas d'acompte si IS N-1 <= 3 000 €
        {"valid_from": "2000-01-01", "valid_to": None, "value": 300_000},
    ],
    # TVA (CGI art. 278 et suivants)
    "tva_taux": [
        {"valid_from": "2014-01-01", "valid_to": None, "value": ["20", "10", "5.5", "2.1", "0"]},
    ],
    # Cadeaux clients : TVA récupérable si valeur unitaire TTC <= seuil (CGI ann. II art. 206 IV 2 3°)
    "tva_seuil_cadeaux_ttc_cents": [
        {"valid_from": "2020-01-01", "valid_to": None, "value": 7_300},
    ],
    # Seuil d'immobilisation tolérée en charge (BOI-BIC-CHG-20-30-10 §10)
    "seuil_immobilisation_ht_cents": [
        {"valid_from": "2001-01-01", "valid_to": None, "value": 50_000},
    ],
    # Acomptes de TVA CA12 (CGI art. 1692 bis, 287-3)
    "ca12_acompte_juillet_pct": [{"valid_from": "2015-01-01", "valid_to": None, "value": "55"}],
    "ca12_acompte_decembre_pct": [{"valid_from": "2015-01-01", "valid_to": None, "value": "40"}],
    "ca12_seuil_acomptes_cents": [{"valid_from": "2015-01-01", "valid_to": None, "value": 100_000}],
    # Seuils micro-entreprise comptable (C. com. L123-16-1) : dispense d'annexe, confidentialité
    "micro_seuils": [
        {"valid_from": "2024-01-01", "valid_to": None,
         "value": {"bilan_cents": 45_000_000, "ca_cents": 90_000_000, "salaries": 10}},
    ],
}


def get_rule(name: str, on: str | date):
    d = on.isoformat() if isinstance(on, date) else str(on)[:10]
    for r in RULES[name]:
        if r["valid_from"] <= d and (r["valid_to"] is None or d <= r["valid_to"]):
            return r["value"]
    raise KeyError(f"Aucune règle {name} en vigueur au {d}")


# --------------------------------------------------------------- plan comptable
# Extrait du Plan comptable général utile à une SASU de services.
ACCOUNTS = [
    ("101300", "Capital souscrit, appelé, versé"),
    ("101100", "Capital souscrit, non appelé"),
    ("106100", "Réserve légale"),
    ("106800", "Autres réserves"),
    ("110000", "Report à nouveau (solde créditeur)"),
    ("119000", "Report à nouveau (solde débiteur)"),
    ("120000", "Résultat de l'exercice (bénéfice)"),
    ("129000", "Résultat de l'exercice (perte)"),
    ("109000", "Actionnaires : capital souscrit, non appelé"),
    ("205000", "Logiciels, concessions, brevets"),
    ("215400", "Matériel industriel"),
    ("218300", "Matériel de bureau et informatique"),
    ("218400", "Mobilier"),
    ("218800", "Immobilisations corporelles diverses"),
    ("280500", "Amortissements logiciels"),
    ("281830", "Amortissements matériel de bureau et informatique"),
    ("281840", "Amortissements mobilier"),
    ("281880", "Amortissements immobilisations diverses"),
    ("275000", "Dépôts et cautionnements versés"),
    ("401000", "Fournisseurs"),
    ("404000", "Fournisseurs d'immobilisations"),
    ("408000", "Fournisseurs, factures non parvenues"),
    ("411000", "Clients"),
    ("418100", "Clients, factures à établir"),
    ("419100", "Clients, avances et acomptes reçus"),
    ("421000", "Personnel, rémunérations dues"),
    ("431000", "Sécurité sociale"),
    ("444000", "État, impôt sur les bénéfices"),
    ("445510", "TVA à décaisser"),
    ("445200", "TVA due intracommunautaire et autoliquidée"),
    ("445620", "TVA déductible sur immobilisations"),
    ("445660", "TVA déductible sur autres biens et services"),
    ("445662", "TVA déductible autoliquidée (UE et hors UE)"),
    ("445670", "Crédit de TVA à reporter"),
    ("445710", "TVA collectée"),
    ("445860", "TVA sur factures non parvenues / déductible en attente"),
    ("445870", "TVA collectée en attente d'encaissement"),
    ("445800", "TVA à régulariser"),
    ("445810", "Acomptes de TVA (régime simplifié)"),
    ("447000", "Autres impôts, taxes et versements assimilés"),
    ("455000", "Associé, compte courant"),
    ("456200", "Apporteurs, capital appelé non versé"),
    ("457000", "Associés, dividendes à payer"),
    ("467000", "Autres comptes débiteurs ou créditeurs"),
    ("471000", "Compte d'attente"),
    ("486000", "Charges constatées d'avance"),
    ("487000", "Produits constatés d'avance"),
    ("512000", "Banque Qonto"),
    ("580000", "Virements internes"),
    ("601000", "Achats stockés, matières premières"),
    ("604000", "Achats de prestations de services"),
    ("606100", "Fournitures non stockables (eau, énergie)"),
    ("606300", "Fournitures d'entretien et petit équipement"),
    ("606400", "Fournitures administratives"),
    ("611000", "Sous-traitance générale"),
    ("613200", "Locations immobilières"),
    ("613500", "Locations mobilières"),
    ("615600", "Maintenance"),
    ("616000", "Primes d'assurance"),
    ("618100", "Documentation générale"),
    ("618500", "Frais de colloques, séminaires, conférences"),
    ("622600", "Honoraires"),
    ("622700", "Frais d'actes et de contentieux"),
    ("623100", "Annonces et insertions"),
    ("623400", "Cadeaux à la clientèle"),
    ("623600", "Catalogues et imprimés"),
    ("623800", "Publicité, pourboires, dons courants"),
    ("625100", "Voyages et déplacements"),
    ("625600", "Missions"),
    ("625700", "Réceptions"),
    ("626000", "Frais postaux et de télécommunications"),
    ("627000", "Services bancaires et assimilés"),
    ("628100", "Concours divers (cotisations)"),
    ("651000", "Redevances pour concessions, logiciels et abonnements"),
    ("635110", "Cotisation foncière des entreprises"),
    ("637000", "Autres impôts, taxes (autres organismes)"),
    ("641000", "Rémunérations du personnel"),
    ("641100", "Rémunération du dirigeant"),
    ("645000", "Charges de sécurité sociale et de prévoyance"),
    ("661000", "Charges d'intérêts"),
    ("666000", "Pertes de change"),
    ("671200", "Pénalités, amendes fiscales et pénales"),
    ("671800", "Autres charges exceptionnelles sur opérations de gestion"),
    ("675000", "Valeurs comptables des éléments d'actif cédés"),
    ("681100", "Dotations aux amortissements des immobilisations"),
    ("695000", "Impôts sur les bénéfices"),
    ("706000", "Prestations de services"),
    ("707000", "Ventes de marchandises"),
    ("708000", "Produits des activités annexes"),
    ("708500", "Ports et frais accessoires facturés"),
    ("709000", "Rabais, remises et ristournes accordés"),
    ("758000", "Produits divers de gestion courante"),
    ("766000", "Gains de change"),
    ("768000", "Autres produits financiers"),
    ("775000", "Produits des cessions d'éléments d'actif"),
    ("778000", "Autres produits exceptionnels"),
]

# Comptes portant de la TVA : sens et rôle dans la déclaration.
VAT_COLLECTED = "445710"
VAT_COLLECTED_PENDING = "445870"
VAT_DEDUCTIBLE = "445660"
VAT_DEDUCTIBLE_ASSETS = "445620"
VAT_DEDUCTIBLE_PENDING = "445860"
VAT_DEDUCTIBLE_AUTOLIQ = "445662"
VAT_DUE_AUTOLIQ = "445200"
VAT_TO_PAY = "445510"
VAT_CREDIT = "445670"
BANK = "512000"
CLIENTS = "411000"
SUPPLIERS = "401000"
SUPPLIERS_ASSETS = "404000"
ASSOCIATE = "455000"
SUSPENSE = "471000"

# --------------------------------------------------------------- catégories
# vat_recovery : % de TVA récupérable par défaut ; deductible : % de charge fiscalement déductible.
# rule : code de règle fiscale appliquée (contrôles et explications).
DEFAULT_CATEGORIES = [
    {"code": "repas", "name": "Restaurants et repas professionnels", "account": "625600",
     "vat_recovery": 100, "deductible": 100, "rule": "repas",
     "help": "Repas d'affaires ou en déplacement. TVA récupérable si la facture est au nom de la société et la dépense dans l'intérêt de l'entreprise. Un repas personnel n'est pas déductible."},
    {"code": "invitations", "name": "Invitations et relations clients", "account": "625700",
     "vat_recovery": 100, "deductible": 100, "rule": "invitation",
     "help": "Repas ou réception avec clients ou prospects. Noter le nom des invités et l'objet en commentaire."},
    {"code": "cadeaux", "name": "Cadeaux clients", "account": "623400",
     "vat_recovery": 100, "deductible": 100, "rule": "cadeau",
     "help": "TVA récupérable seulement si la valeur unitaire TTC ne dépasse pas 73 € par bénéficiaire et par an. Indiquer le bénéficiaire."},
    {"code": "logiciels", "name": "Abonnements logiciels", "account": "651000",
     "vat_recovery": 100, "deductible": 100, "rule": "logiciel",
     "help": "Abonnements SaaS. Fournisseur étranger sans TVA française : autoliquidation (TVA due et déductible)."},
    {"code": "hebergement", "name": "Hébergement et noms de domaine", "account": "626000",
     "vat_recovery": 100, "deductible": 100, "rule": "standard"},
    {"code": "communication", "name": "Communication et publicité", "account": "623100",
     "vat_recovery": 100, "deductible": 100, "rule": "standard"},
    {"code": "materiel", "name": "Matériel informatique (petit équipement)", "account": "606300",
     "vat_recovery": 100, "deductible": 100, "rule": "materiel",
     "help": "Matériel de moins de 500 € HT. Au delà, enregistrer une immobilisation."},
    {"code": "deplacements", "name": "Déplacements professionnels", "account": "625100",
     "vat_recovery": 100, "deductible": 100, "rule": "deplacement",
     "help": "Train, avion, hôtel. TVA non récupérable sur le transport de personnes et sur l'hébergement des dirigeants (CGI ann. II art. 206 IV)."},
    {"code": "banque", "name": "Frais bancaires", "account": "627000",
     "vat_recovery": 100, "deductible": 100, "rule": "standard",
     "help": "Abonnement Qonto (avec TVA). Les commissions sur opérations sont souvent exonérées."},
    {"code": "prestations", "name": "Prestations externes", "account": "611000",
     "vat_recovery": 100, "deductible": 100, "rule": "standard"},
    {"code": "honoraires", "name": "Honoraires (expert comptable, avocat)", "account": "622600",
     "vat_recovery": 100, "deductible": 100, "rule": "standard"},
    {"code": "fournitures", "name": "Fournitures", "account": "606400",
     "vat_recovery": 100, "deductible": 100, "rule": "standard"},
    {"code": "assurances", "name": "Assurances", "account": "616000",
     "vat_recovery": 0, "deductible": 100, "rule": "sans_tva",
     "help": "Les primes d'assurance sont exonérées de TVA."},
    {"code": "impots", "name": "Impôts et taxes (CFE, etc.)", "account": "635110",
     "vat_recovery": 0, "deductible": 100, "rule": "sans_tva"},
    {"code": "amendes", "name": "Amendes et pénalités", "account": "671200",
     "vat_recovery": 0, "deductible": 0, "rule": "non_deductible",
     "help": "Jamais déductibles (CGI art. 39-2). Réintégrées dans le résultat fiscal."},
    {"code": "autres", "name": "Autres charges", "account": "618100",
     "vat_recovery": 100, "deductible": 100, "rule": "standard"},
    {"code": "immobilisation", "name": "Immobilisations", "account": "218300",
     "vat_recovery": 100, "deductible": 100, "rule": "immobilisation", "is_asset": 1,
     "help": "Bien durable de plus de 500 € HT : amorti sur sa durée d'utilisation. TVA déductible sur immobilisations."},
    {"code": "personnel", "name": "Dépense personnelle (non professionnelle)", "account": "455000",
     "vat_recovery": 0, "deductible": 0, "rule": "personnel",
     "help": "Dépense privée payée avec le compte de la société : ce n'est pas une charge. Elle est inscrite au débit du compte courant d'associé (la société avance l'argent à l'associé)."},
]

# Libellés des règles appliquées, affichés dans l'interface.
RULE_EXPLANATIONS = {
    "repas": "Repas professionnel : charge déductible si intérêt de l'entreprise justifié ; TVA récupérable sur facture au nom de la société.",
    "invitation": "Invitation clients : déductible et TVA récupérable si justifiée (nom des invités, objet).",
    "cadeau": "Cadeau client : TVA récupérable uniquement si valeur TTC par bénéficiaire et par an ≤ 73 €.",
    "logiciel": "Abonnement logiciel : charge déductible, TVA récupérable ; autoliquidation si fournisseur étranger.",
    "materiel": "Petit matériel (< 500 € HT) : charge immédiate.",
    "deplacement": "Transport de personnes : TVA non récupérable (sauf exceptions).",
    "standard": "Charge professionnelle courante : déductible, TVA récupérable sur facture conforme.",
    "sans_tva": "Opération sans TVA.",
    "non_deductible": "Charge non déductible fiscalement : réintégrée au résultat fiscal.",
    "immobilisation": "Immobilisation : amortie ; TVA déductible sur immobilisations (ligne 19 CA3).",
    "personnel": "Dépense personnelle : non déductible, imputée au compte courant d'associé.",
}

JOURNALS = {
    "AC": "Journal des achats",
    "VT": "Journal des ventes",
    "BQ": "Journal de banque Qonto",
    "OD": "Opérations diverses",
    "AN": "À nouveaux",
}

# Durées d'amortissement usuelles (années), à titre indicatif.
ASSET_TYPES = {
    "218300": {"label": "Matériel informatique", "years": 3, "dep_account": "281830"},
    "218400": {"label": "Mobilier", "years": 10, "dep_account": "281840"},
    "205000": {"label": "Logiciel acquis", "years": 1, "dep_account": "280500"},
    "218800": {"label": "Autre équipement", "years": 5, "dep_account": "281880"},
    "215400": {"label": "Matériel professionnel", "years": 5, "dep_account": "281880"},
}

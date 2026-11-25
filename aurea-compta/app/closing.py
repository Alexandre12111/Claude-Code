"""Clôture annuelle : contrôles, inventaire, IS, états, obligations, à nouveaux."""
import json
from datetime import date, timedelta

from . import checks, db, ledger, reports, rules, services, tax
from .ledger import AccountingError, C, D


def fiscal_year(conn, fy_id=None):
    if fy_id:
        return db.q1(conn, "SELECT * FROM fiscal_years WHERE id=?", (fy_id,))
    today = date.today().isoformat()
    return (db.q1(conn, "SELECT * FROM fiscal_years WHERE start_date<=? AND end_date>=?", (today, today))
            or db.q1(conn, "SELECT * FROM fiscal_years WHERE status='open' ORDER BY start_date LIMIT 1")
            or db.q1(conn, "SELECT * FROM fiscal_years ORDER BY start_date DESC LIMIT 1"))


def obligations(conn, fy, inc, bs) -> dict:
    """Obligations annuelles réellement applicables à la SASU."""
    taxs = db.get_setting(conn, "tax")
    company = db.get_setting(conn, "company")
    micro = rules.get_rule("micro_seuils", fy["end_date"])
    is_micro = (bs["total_actif"] <= micro["bilan_cents"] and inc["chiffre_affaires"] <= micro["ca_cents"]
                and (taxs.get("salaried_count") or 0) <= micro["salaries"])
    rsi = taxs.get("is_regime", "reel_simplifie") == "reel_simplifie"
    end = date.fromisoformat(fy["end_date"])
    approval_deadline = (end + timedelta(days=183)).isoformat()
    items = [
        {"obligation": f"Déclaration de résultat n°2065-SD et annexes {'2033-A à 2033-G (régime simplifié)' if rsi else '2050 à 2059 (régime normal)'}",
         "echeance": "2e jour ouvré suivant le 1er mai pour un exercice civil, sinon 3 mois après la clôture",
         "note": "Télétransmission obligatoire (EDI-TDFC via un partenaire ou l'expert comptable, ou mode EFI sur l'espace professionnel lorsqu'il est ouvert à votre situation)."},
        {"obligation": "Relevé de solde d'IS n°2572-SD et paiement du solde", "echeance": "15 du 4e mois suivant la clôture"},
        {"obligation": "Comptes annuels : bilan, compte de résultat" + ("" if is_micro else ", annexe"),
         "echeance": "Établis par le président à la clôture",
         "note": "Micro entreprise : dispense d'annexe." if is_micro else "Annexe obligatoire (entreprise au delà des seuils micro)."},
        {"obligation": "Approbation des comptes par l'associé unique et décision d'affectation du résultat",
         "echeance": f"Dans les 6 mois de la clôture ({approval_deadline})",
         "note": "Lorsque l'associé unique personne physique est lui même président, le dépôt au greffe de l'inventaire et des comptes annuels signés dans ce délai vaut approbation (C. com. L227-9). La décision est répertoriée dans le registre des décisions."},
        {"obligation": "Dépôt des comptes annuels au greffe (Guichet unique INPI)", "echeance": "1 mois après l'approbation (2 mois par voie électronique)",
         "note": "Déclaration de confidentialité possible pour les micro entreprises (C. com. L232-25)." if is_micro else "Confidentialité du compte de résultat possible pour les petites entreprises."},
        {"obligation": "Rapport de gestion", "echeance": "-",
         "note": "Dispense pour les petites entreprises (C. com. L232-1 IV)."},
        {"obligation": "Fichier des écritures comptables (FEC)", "echeance": "À produire en cas de contrôle fiscal", "note": "Généré automatiquement dans l'export annuel."},
    ]
    if taxs.get("vat_regime") == "reel_simplifie":
        items.append({"obligation": "Déclaration annuelle de TVA CA12", "echeance": "2e jour ouvré suivant le 1er mai"})
    items.append({"obligation": "Déclaration de CFE (si création ou changement) et paiement de la CFE", "echeance": "15 décembre"})
    items.append({"obligation": "Conservation : pièces justificatives et livres 10 ans (C. com. L123-22), documents fiscaux 6 ans (LPF L102 B)", "echeance": "-"})
    return {"micro_entreprise": is_micro, "regime_is": "réel simplifié" if rsi else "réel normal", "liste": items,
            "liasse": "2065 + 2033-A à 2033-G" if rsi else "2065 + 2050 à 2059"}


def liasse_2033(inc, bs) -> dict:
    """Montants clés à reporter sur la liasse simplifiée (2033-A bilan, 2033-B résultat)."""
    def actif(rub):
        return next((x["total"] for x in bs["actif"] if x["rubrique"] == rub), 0)

    def passif(rub):
        return next((x["total"] for x in bs["passif"] if x["rubrique"] == rub), 0)
    s = inc["sections"]
    acc = lambda sec, pre: sum(l["amount"] for l in s[sec]["lines"] if l["account"].startswith(pre))
    return {
        "2033-A (bilan simplifié)": [
            ("Immobilisations incorporelles et corporelles (brut)", actif("Immobilisations incorporelles") + actif("Immobilisations corporelles")),
            ("Amortissements", -actif("Amortissements et dépréciations")),
            ("Immobilisations financières", actif("Immobilisations financières")),
            ("Clients et comptes rattachés", actif("Créances clients")),
            ("Autres créances", actif("Autres créances") + actif("Autres créances (État, TVA)") + actif("Autres créances (compte courant débiteur)")),
            ("Disponibilités", actif("Disponibilités")),
            ("Charges constatées d'avance", actif("Charges constatées d'avance")),
            ("Total actif", bs["total_actif"]),
            ("Capital social", passif("Capital social")),
            ("Réserve légale et autres réserves", passif("Réserves")),
            ("Report à nouveau", passif("Report à nouveau")),
            ("Résultat de l'exercice", passif("Résultat de l'exercice")),
            ("Emprunts et dettes assimilées (dont compte courant d'associé)", passif("Emprunts et dettes financières divers (compte courant d'associé)") + passif("Emprunts et dettes auprès des établissements de crédit")),
            ("Fournisseurs et comptes rattachés", passif("Dettes fournisseurs")),
            ("Dettes fiscales et sociales", passif("Dettes fiscales et sociales")),
            ("Autres dettes", passif("Autres dettes") + passif("Avances et acomptes reçus / clients créditeurs") + passif("Concours bancaires courants")),
            ("Produits constatés d'avance", passif("Produits constatés d'avance")),
            ("Total passif", bs["total_passif"]),
        ],
        "2033-B (compte de résultat simplifié)": [
            ("Ventes de marchandises", acc("produits_exploitation", "707")),
            ("Production vendue (services)", acc("produits_exploitation", "706") + acc("produits_exploitation", "708")),
            ("Autres produits", acc("produits_exploitation", "75") + acc("produits_exploitation", "74")),
            ("Achats de marchandises et matières", acc("charges_exploitation", "60")),
            ("Autres charges externes", acc("charges_exploitation", "61") + acc("charges_exploitation", "62")),
            ("Impôts, taxes et versements assimilés", acc("charges_exploitation", "63")),
            ("Rémunérations du personnel", acc("charges_exploitation", "641")),
            ("Charges sociales", acc("charges_exploitation", "645")),
            ("Dotations aux amortissements", acc("charges_exploitation", "681")),
            ("Autres charges", acc("charges_exploitation", "65")),
            ("Résultat d'exploitation", inc["resultat_exploitation"]),
            ("Produits financiers", inc["sections"]["produits_financiers"]["total"]),
            ("Charges financières", inc["sections"]["charges_financieres"]["total"]),
            ("Produits exceptionnels", inc["sections"]["produits_exceptionnels"]["total"]),
            ("Charges exceptionnelles", inc["sections"]["charges_exceptionnelles"]["total"]),
            ("Impôt sur les bénéfices", inc["impot"]),
            ("Bénéfice ou perte", inc["resultat_net"]),
        ],
        "note": "Reprise des montants calculés. Les numéros de cases du formulaire de l'année sont à vérifier sur la notice 2033-NOT-SD.",
    }


QUESTIONS = [
    {"id": "fnp", "question": "Avez-vous reçu après la clôture des factures de fournisseurs concernant l'exercice (prestations déjà réalisées) ?",
     "aide": "Elles doivent être rattachées à l'exercice : saisissez-les à leur date de facture avec le paiement « À payer »."},
    {"id": "fae", "question": "Avez-vous réalisé des prestations sur l'exercice qui ne sont pas encore facturées ?",
     "aide": "Créez la facture à sa date réelle, ou une écriture « Clients, factures à établir » (418100) en opérations diverses."},
    {"id": "cca", "question": "Avez-vous payé des charges qui concernent en partie l'exercice suivant (abonnement annuel, assurance) ?",
     "aide": "Charges constatées d'avance : saisissez une écriture d'inventaire (486000) pour la part de l'exercice suivant."},
    {"id": "pca", "question": "Avez-vous facturé des prestations qui seront réalisées sur l'exercice suivant ?",
     "aide": "Produits constatés d'avance : écriture d'inventaire (487000)."},
    {"id": "stock", "question": "La société détient-elle des stocks (marchandises) à la clôture ?",
     "aide": "Pour une activité de services, en principe non."},
    {"id": "litiges", "question": "Existe-t-il un litige ou un risque probable nécessitant une provision ?", "aide": "Provision pour risques (151)."},
    {"id": "remuneration", "question": "Le président a-t-il été rémunéré sur l'exercice (hors remboursements de compte courant) ?",
     "aide": "La rémunération du président relève de la paie (DSN) et ne se confond pas avec un remboursement d'apport."},
]


def manual_inventory_entry(conn, data) -> int:
    """Écriture d'inventaire saisie (CCA, PCA, FNP, FAE...), toujours équilibrée."""
    lines = []
    for l in data["lines"]:
        lines.append(ledger.Line(l["account"], debit=services.cents(l.get("debit_cents") or 0),
                                 credit=services.cents(l.get("credit_cents") or 0), label=l.get("label")))
    with db.tx(conn):
        eid = ledger.create_entry(conn, data.get("journal", "OD"), data["date"], data["label"], lines,
                                  piece_ref=data.get("piece_ref") or "INV", source_type="manuel", source_id=None)
        db.audit(conn, "creation", "ecriture_manuelle", eid, None, data)
    return eid


def prepare(conn, fy_id=None) -> dict:
    """« Préparer mon bilan annuel »."""
    fy = fiscal_year(conn, fy_id)
    with db.tx(conn):
        assets = services.post_depreciation(conn, fy["id"])
    issues = checks.period_checks(conn, fy["start_date"], fy["end_date"])
    is_est = tax.estimate(conn, fy)
    with db.tx(conn):
        ledger.clear_source(conn, "is", fy["id"], "Recalcul de l'IS")
        if is_est["is_estime_cents"] > 0:
            ledger.create_entry(conn, "OD", fy["end_date"], "Impôt sur les sociétés de l'exercice",
                                [D("695000", is_est["is_estime_cents"]), C("444000", is_est["is_estime_cents"])],
                                piece_ref=f"IS{fy['end_date'][:4]}", source_type="is", source_id=fy["id"])
    inc = reports.income_statement(conn, fy["start_date"], fy["end_date"])
    bs = reports.balance_sheet(conn, fy)
    company = db.get_setting(conn, "company")
    confirmed = db.get_setting(conn, "confirmed")
    missing = []
    if not company.get("siren"):
        missing.append("Numéro SIREN (Paramètres) : obligatoire pour le FEC et la liasse.")
    if not confirmed.get("tax"):
        missing.append("Régimes fiscaux à confirmer dans Paramètres (valeurs provisoires utilisées).")
    if not company.get("associate_name"):
        missing.append("Nom de l'associé unique (Paramètres) : utilisé pour reconnaître les apports.")
    blocking = [i for i in issues if i["niveau"] == "bloquant"]
    result = inc["resultat_net"]
    capital = company.get("capital_cents", 0)
    legal_reserve = ledger.account_balance(conn, "1061", fy["end_date"]) * -1
    to_legal = 0
    if result > 0:
        to_legal = min(result * 5 // 100, max(0, capital // 10 - legal_reserve))
    return {
        "exercice": fy, "anomalies": issues, "bloquant": len(blocking), "informations_manquantes": missing,
        "definitif": not blocking and not missing and fy["status"] != "closed",
        "statut": "Prêt à clôturer" if not blocking and not missing else "Provisoire : contrôles ou informations à compléter",
        "immobilisations": assets, "impot": is_est, "compte_de_resultat": inc, "bilan": bs,
        "obligations": obligations(conn, fy, inc, bs), "liasse": liasse_2033(inc, bs), "questions": QUESTIONS,
        "affectation_proposee": {"resultat_cents": result, "reserve_legale_cents": to_legal,
                                 "report_a_nouveau_cents": result - to_legal,
                                 "note": "5 % du bénéfice en réserve légale jusqu'à 10 % du capital (C. com. L232-10). Le solde peut aller en report à nouveau ou être distribué."},
        "creances_dettes": reports.receivables_payables(conn, fy["end_date"]),
    }


def close(conn, fy_id: int, force=False) -> dict:
    """Clôture définitive : validation de toutes les écritures, à nouveaux, verrouillage."""
    prep = prepare(conn, fy_id)
    if (prep["bloquant"] or prep["informations_manquantes"]) and not force:
        raise AccountingError("Clôture impossible : anomalies bloquantes ou informations manquantes.")
    fy = prep["exercice"]
    with db.tx(conn):
        for e in db.q(conn, "SELECT id FROM entries WHERE status='brouillon' AND date BETWEEN ? AND ? ORDER BY date,id", (fy["start_date"], fy["end_date"])):
            ledger.validate_entry(conn, e["id"])
        nxt = db.q1(conn, "SELECT * FROM fiscal_years WHERE start_date=?", ((date.fromisoformat(fy["end_date"]) + timedelta(days=1)).isoformat(),))
        if not nxt:
            s = date.fromisoformat(fy["end_date"]) + timedelta(days=1)
            e = date(s.year + 1, s.month, s.day) - timedelta(days=1)
            conn.execute("INSERT INTO fiscal_years(start_date,end_date) VALUES(?,?)", (s.isoformat(), e.isoformat()))
            nxt = db.q1(conn, "SELECT * FROM fiscal_years WHERE start_date=?", (s.isoformat(),))
        # À nouveaux : soldes des comptes de bilan, résultat en 120 / 129 en attente d'affectation
        tb = reports.trial_balance(conn, fy["start_date"], fy["end_date"])["rows"]
        lines, result = [], 0
        for r in tb:
            if r["account"][0] in "67":
                result += -r["solde"]
                continue
            if r["solde"]:
                lines.append(ledger.Line(r["account"], debit=max(r["solde"], 0), credit=max(-r["solde"], 0), label="À nouveau"))
        # Détail par tiers pour 411/401/455
        if result > 0:
            lines.append(C("120000", result, label="Résultat de l'exercice précédent"))
        elif result < 0:
            lines.append(D("129000", -result, label="Résultat de l'exercice précédent"))
        ledger.clear_source(conn, "a_nouveau", nxt["id"], "Recalcul des à nouveaux")
        an = ledger.create_entry(conn, "AN", nxt["start_date"], f"À nouveaux exercice {nxt['start_date'][:4]}", lines,
                                 piece_ref="AN", source_type="a_nouveau", source_id=nxt["id"], status="valide")
        conn.execute("UPDATE fiscal_years SET status='closed', closed_at=?, summary=? WHERE id=?",
                     (db.now(), json.dumps({"resultat_cents": result, "total_bilan_cents": prep["bilan"]["total_actif"],
                                            "is_cents": prep["impot"]["is_estime_cents"]}), fy["id"]))
        db.audit(conn, "cloture_exercice", "exercice", fy["id"], None, {"resultat": result},
                 "Clôture forcée" if force and (prep["bloquant"] or prep["informations_manquantes"]) else None)
    return {"ok": True, "resultat_cents": result, "a_nouveaux_entry": an, "exercice_suivant": nxt}


def allocate_result(conn, fy_id: int, legal_reserve_cents: int, dividends_cents: int = 0, decision_date: str | None = None) -> int:
    """Affectation du résultat décidée par l'associé unique (écriture sur l'exercice suivant)."""
    fy = db.q1(conn, "SELECT * FROM fiscal_years WHERE id=?", (fy_id,))
    summ = json.loads(fy["summary"] or "{}")
    result = summ.get("resultat_cents", 0)
    nxt = db.q1(conn, "SELECT * FROM fiscal_years WHERE start_date>? ORDER BY start_date LIMIT 1", (fy["end_date"],))
    d = decision_date or nxt["start_date"]
    with db.tx(conn):
        ledger.clear_source(conn, "affectation", fy_id, "Nouvelle décision d'affectation")
        if result >= 0:
            ran = result - legal_reserve_cents - dividends_cents
            if ran < 0:
                raise AccountingError("Affectation supérieure au bénéfice.")
            lines = [D("120000", result), C("106100", legal_reserve_cents), C("457000", dividends_cents), C("110000", ran)]
        else:
            lines = [D("119000", -result), C("129000", -result)]
        eid = ledger.create_entry(conn, "OD", d, f"Affectation du résultat {fy['end_date'][:4]}", lines,
                                  piece_ref=f"AFF{fy['end_date'][:4]}", source_type="affectation", source_id=fy_id)
    return eid

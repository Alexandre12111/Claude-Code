"""Scénario complet d'un exercice fictif (données de test isolées).

Couvre : facture client avec TVA, facture fournisseur avec TVA, dépense sans TVA
récupérable, note de frais payée personnellement, apport et remboursement en
compte courant, facture payée partiellement, avoir, immobilisation, rapprochement
bancaire, déclaration de TVA, clôture annuelle et exports.
"""
import io
import json
import zipfile

import pytest

from app import checks, closing, db, exports, ledger, reports, services, tax, vat
from app.ledger import AccountingError

PDF = b"%PDF-1.4 justificatif de test "


def tx(c, ext, d, amount, who, op="card"):
    services.import_transactions(c, [{"external_id": ext, "date": d, "amount_cents": amount, "label": who, "counterparty": who,
                                      "operation_type": op}], "qonto")
    return db.q1(c, "SELECT * FROM bank_transactions WHERE external_id=?", (ext,))


def doc(c, name):
    with db.tx(c):
        return services.store_document(c, name, PDF + name.encode())


def cat(c, code):
    return db.q1(c, "SELECT id FROM categories WHERE code=?", (code,))["id"]


def lines_of(c, source_type, source_id):
    out = []
    for e in ledger.source_entries(c, source_type, source_id):
        out += db.q(c, "SELECT account, debit_cents d, credit_cents cr, vat_code FROM entry_lines WHERE entry_id=?", (e["id"],))
    return out


def bal(c, acc, end=None, start=None):
    return ledger.account_balance(c, acc, end, start)


def assert_balanced(c):
    r = ledger.check_global_balance(c)
    assert r["ok"], r


# ---------------------------------------------------------------- briques
def test_facture_client_avec_tva_exigible_a_l_encaissement(conn):
    r = services.save_sales_invoice(conn, {"client_name": "Client Alpha", "issue_date": "2026-01-10", "due_date": "2026-02-10",
                                           "lines": [{"description": "Site vitrine", "quantity": "1", "unit_price_cents": 100000, "vat_rate": "20"}]})
    assert r["number"] == "F2026-0001"
    ls = lines_of(conn, "vente", r["id"])
    assert {(l["account"], l["d"], l["cr"]) for l in ls} == {("706000", 0, 100000), ("445870", 0, 20000), ("411000", 120000, 0)}
    # Avant encaissement : aucune TVA collectée exigible
    assert vat.compute(conn, "2026-01-01", "2026-01-31")["tva_brute_cents"] == 0
    assert_balanced(conn)


def test_facture_numerotation_unique(conn):
    services.save_sales_invoice(conn, {"client_name": "A", "issue_date": "2026-01-10", "number": "F2026-0001",
                                       "lines": [{"unit_price_cents": 1000, "vat_rate": "20"}]})
    with pytest.raises(AccountingError):
        services.save_sales_invoice(conn, {"client_name": "A", "issue_date": "2026-01-11", "number": "F2026-0001",
                                           "lines": [{"unit_price_cents": 1000, "vat_rate": "20"}]})
    w = services.save_sales_invoice(conn, {"client_name": "A", "issue_date": "2026-01-12", "number": "F2026-0005",
                                           "lines": [{"unit_price_cents": 1000, "vat_rate": "20"}]})["warnings"]
    assert any("séquence" in x for x in w)


def test_facture_fournisseur_avec_tva_et_paiement(conn):
    t = tx(conn, "t1", "2026-01-15", -12000, "Logiciel Pro SAS")
    d = doc(conn, "facture-logiciel.pdf")
    r = services.classify_transaction(conn, t["id"], {"type": "depense_nouvelle", "category_id": cat(conn, "logiciels"),
                                                      "vat_rate": "20", "vat_cents": 2000, "document_id": d})
    exp = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (r["id"],))
    assert (exp["ht_cents"], exp["vat_cents"], exp["ttc_cents"]) == (10000, 2000, 12000)
    assert bal(conn, "651") == 10000
    assert bal(conn, "445660") == 2000          # TVA déductible au paiement
    assert bal(conn, "445860") == 0
    assert bal(conn, "401") == 0                 # fournisseur soldé
    assert bal(conn, "512") == -12000
    assert db.q1(conn, "SELECT status FROM bank_transactions WHERE id=?", (t["id"],))["status"] == "rapproche"
    assert_balanced(conn)


def test_depense_sans_tva_recuperable(conn):
    t = tx(conn, "t2", "2026-02-03", -5500, "SNCF Voyageurs")
    r = services.classify_transaction(conn, t["id"], {"type": "depense_nouvelle", "category_id": cat(conn, "deplacements"),
                                                      "vat_rate": "10", "vat_cents": 500})
    services.save_expense(conn, {"vat_recovery": 0}, r["id"])
    assert bal(conn, "625100") == 5500
    assert bal(conn, "4456") == 0
    assert_balanced(conn)


def test_cadeau_au_dessus_du_seuil(conn):
    r = services.save_expense(conn, {"date": "2026-03-01", "supplier_name": "Cave", "label": "Coffret", "ttc_cents": 12000, "vat_rate": "20",
                                     "category_id": cat(conn, "cadeaux"), "payment_method": "a_payer"})
    exp = db.q1(conn, "SELECT * FROM expenses WHERE id=?", (r["id"],))
    assert exp["vat_recovery"] == 0
    assert bal(conn, "623400") == 12000


def test_note_de_frais_payee_personnellement_puis_remboursee(conn):
    d = doc(conn, "ndf.pdf")
    r = services.save_expense(conn, {"date": "2026-01-20", "supplier_name": "Figma", "label": "Abonnement", "ttc_cents": 3000,
                                     "vat_rate": "20", "category_id": cat(conn, "logiciels"), "payment_method": "perso", "document_id": d})
    assert bal(conn, "455") == -3000            # la société doit 30 €
    assert bal(conn, "445660") == 500
    s = services.associate_summary(conn)
    assert s["avances_frais_cents"] == 3000 and s["du_par_la_societe_cents"] == 3000
    t = tx(conn, "t3", "2026-02-10", -3000, "Alexandre Coury", "transfer")
    sug = json.loads(t["suggestion"])
    assert sug["type"] == "associe"
    services.apply_suggestion(conn, t["id"])
    s = services.associate_summary(conn)
    assert s["remboursements_cents"] == 3000 and s["solde_cents"] == 0
    # la charge n'est comptée qu'une fois
    assert bal(conn, "651") == 2500
    assert_balanced(conn)


def test_apport_et_remboursement_compte_courant(conn):
    t = tx(conn, "a1", "2026-01-05", 500000, "Alexandre Coury", "income")
    services.apply_suggestion(conn, t["id"])
    t2 = tx(conn, "a2", "2026-06-05", -150000, "Alexandre Coury", "transfer")
    services.classify_transaction(conn, t2["id"], {"type": "associe"})
    s = services.associate_summary(conn)
    assert (s["apports_cents"], s["remboursements_cents"], s["solde_cents"]) == (500000, 150000, 350000)
    # Ni produit ni charge
    inc = reports.income_statement(conn, "2026-01-01", "2026-12-31")
    assert inc["total_produits"] == 0 and inc["total_charges"] == 0
    assert_balanced(conn)


def test_apport_saisi_puis_rapproche(conn):
    r = services.add_associate_movement(conn, {"type": "apport", "date": "2026-03-01", "amount_cents": 20000, "comment": "Renfort"})
    assert r["matched"] is False
    assert services.associate_summary(conn)["solde_cents"] == 0       # pas encore sur la banque
    t = tx(conn, "a3", "2026-03-02", 20000, "Virement perso", "transfer")
    services.link_movement_to_transaction(conn, r["id"], t["id"])
    assert services.associate_summary(conn)["apports_cents"] == 20000


def test_capital_distinct_du_compte_courant(conn):
    t = tx(conn, "c1", "2026-01-02", 100000, "Dépôt de capital", "transfer")
    services.classify_transaction(conn, t["id"], {"type": "capital"})
    assert bal(conn, "1013") == -100000
    assert bal(conn, "455") == 0


def test_facture_payee_partiellement_et_avoir(conn):
    inv = services.save_sales_invoice(conn, {"client_name": "Client Beta", "issue_date": "2026-01-10",
                                             "lines": [{"description": "Refonte", "unit_price_cents": 100000, "vat_rate": "20"}]})
    t1 = tx(conn, "p1", "2026-02-05", 60000, "Client Beta", "transfer")
    assert json.loads(t1["suggestion"])["invoice_id"] == inv["id"]
    services.classify_transaction(conn, t1["id"], {"type": "vente", "invoice_id": inv["id"]})
    i = db.q1(conn, "SELECT * FROM sales_invoices WHERE id=?", (inv["id"],))
    st = services.invoice_status(conn, i)
    assert st["payment_status"] == "partielle" and st["remaining_cents"] == 60000
    feb = vat.compute(conn, "2026-02-01", "2026-02-28")
    assert feb["tva_brute_cents"] == 10000 and feb["lignes"]["A1"]["base_cents"] == 50000
    # Avoir de 240 € TTC
    av = services.save_sales_invoice(conn, {"kind": "avoir", "credited_invoice_id": inv["id"], "issue_date": "2026-02-20",
                                            "lines": [{"description": "Remise", "unit_price_cents": 20000, "vat_rate": "20"}]})
    assert av["number"] == "AV2026-0001"
    st = services.invoice_status(conn, i)
    assert st["remaining_cents"] == 36000
    t2 = tx(conn, "p2", "2026-03-03", 36000, "Client Beta", "transfer")
    services.classify_transaction(conn, t2["id"], {"type": "vente", "invoice_id": inv["id"]})
    assert services.invoice_status(conn, i)["payment_status"] == "payee"
    total = vat.compute(conn, "2026-01-01", "2026-12-31")
    assert total["tva_brute_cents"] == 16000               # 200 - 40 d'avoir
    assert bal(conn, "445870") == 0                         # plus rien en attente
    assert bal(conn, "411") == 0
    assert bal(conn, "706") == -80000
    assert_balanced(conn)


def test_vente_de_biens_tva_a_la_facture(conn):
    services.save_sales_invoice(conn, {"client_name": "Gamma", "issue_date": "2026-04-02", "nature": "biens",
                                       "lines": [{"unit_price_cents": 50000, "vat_rate": "20", "account": "707000"}]})
    assert vat.compute(conn, "2026-04-01", "2026-04-30")["tva_brute_cents"] == 10000


def test_autoliquidation_fournisseur_ue(conn):
    t = tx(conn, "u1", "2026-05-03", -5000, "Google Ireland Ltd")
    services.classify_transaction(conn, t["id"], {"type": "depense_nouvelle", "category_id": cat(conn, "communication"),
                                                  "vat_regime": "autoliquidation_ue", "vat_rate": "20", "document_id": doc(conn, "g.pdf")})
    m = vat.compute(conn, "2026-05-01", "2026-05-31")
    assert m["lignes"]["A3"]["base_cents"] == 5000
    assert m["tva_brute_cents"] == 1000 and m["lignes"]["20"]["taxe_cents"] == 1000 and m["a_payer_cents"] == 0
    assert bal(conn, "623100") == 5000
    assert_balanced(conn)


def test_immobilisation_amortissable(conn):
    t = tx(conn, "i1", "2026-04-01", -180000, "Apple Store")
    r = services.classify_transaction(conn, t["id"], {"type": "depense_nouvelle", "category_id": cat(conn, "immobilisation"),
                                                      "vat_rate": "20", "nature": "biens", "label": "MacBook Pro", "document_id": doc(conn, "mac.pdf")})
    fa = db.q1(conn, "SELECT * FROM fixed_assets")
    assert fa["cost_cents"] == 150000 and fa["duration_years"] == 3
    assert bal(conn, "2183") == 150000 and bal(conn, "445620") == 30000
    fy = closing.fiscal_year(conn)
    with db.tx(conn):
        sched = services.post_depreciation(conn, fy["id"])
    dot = sched[0]["dotation_cents"]
    assert dot == round(150000 * 275 / 1096)               # prorata temporis jours réels
    assert bal(conn, "6811") == dot and bal(conn, "28183") == -dot
    # cumul sur toute la durée = valeur brute
    total = sum(services.depreciation_for_year(fa, f"{y}-01-01", f"{y}-12-31")["dotation_cents"] for y in range(2026, 2030))
    assert total == 150000
    m = vat.compute(conn, "2026-04-01", "2026-04-30")
    assert m["lignes"]["19"]["taxe_cents"] == 30000
    assert_balanced(conn)


def test_ecriture_validee_protegee(conn):
    r = services.save_expense(conn, {"date": "2026-01-20", "supplier_name": "X", "label": "Y", "ttc_cents": 1200, "vat_rate": "20",
                                     "category_id": cat(conn, "fournitures"), "payment_method": "a_payer"})
    e = ledger.source_entries(conn, "depense", r["id"])[0]
    with db.tx(conn):
        ledger.validate_entry(conn, e["id"])
    import sqlite3
    with pytest.raises(sqlite3.DatabaseError):
        conn.execute("UPDATE entry_lines SET debit_cents=1 WHERE entry_id=?", (e["id"],))
    with pytest.raises(sqlite3.DatabaseError):
        conn.execute("DELETE FROM entries WHERE id=?", (e["id"],))
    # Correction : contrepassation + nouvelle écriture, tracées
    services.save_expense(conn, {"ttc_cents": 2400}, r["id"], "Montant corrigé")
    rev = db.q1(conn, "SELECT * FROM entries WHERE reversal_of=?", (e["id"],))
    assert rev and rev["status"] == "valide"
    assert bal(conn, "606400") == 2000
    assert db.q1(conn, "SELECT * FROM audit_log WHERE action='contrepassation'")
    assert_balanced(conn)


def test_import_idempotent_et_doublons(conn):
    item = {"external_id": "qonto:abc", "date": "2026-01-03", "amount_cents": -999, "label": "Frais", "counterparty": "Qonto", "operation_type": "qonto_fee"}
    assert services.import_transactions(conn, [item], "qonto")["imported"] == 1
    assert services.import_transactions(conn, [item], "qonto")["duplicates"] == 1
    twin = dict(item, external_id="csv:1")
    assert services.import_transactions(conn, [twin], "csv")["duplicates"] == 1
    t = db.q1(conn, "SELECT * FROM bank_transactions")
    assert json.loads(t["suggestion"])["type"] == "depense_nouvelle"


def test_csv_qonto():
    from app import qonto
    data = ("Date de l'opération (UTC);Montant total (TTC);Nom de la contrepartie;Libellé;Statut\n"
            "15/01/2026 10:00;-120,00;Logiciel Pro;Abonnement;Exécuté\n"
            "16/01/2026;1 200,50;Client;Facture F1;Exécuté\n").encode()
    items = qonto.parse_csv(data)
    assert [(i["date"], i["amount_cents"]) for i in items] == [("2026-01-15", -12000), ("2026-01-16", 120050)]


def test_arrondis_sans_erreur():
    from app.money import split_ttc, to_cents, vat_from_ht
    assert to_cents("0,1") + to_cents("0.2") == 30
    for ttc in range(1, 5000):
        ht, v = split_ttc(ttc, "20")
        assert ht + v == ttc
    assert vat_from_ht(333, "5.5") == 18


# ---------------------------------------------------------------- scénario annuel
def build_year(c):
    tx_cap = tx(c, "y0", "2026-01-02", 100000, "Dépôt de capital", "transfer")
    services.classify_transaction(c, tx_cap["id"], {"type": "capital"})
    a = tx(c, "y1", "2026-01-05", 500000, "Alexandre Coury", "income")
    services.apply_suggestion(c, a["id"])
    inv = services.save_sales_invoice(c, {"client_name": "Client Alpha", "issue_date": "2026-01-10", "due_date": "2026-02-10",
                                          "lines": [{"description": "Site", "unit_price_cents": 300000, "vat_rate": "20"}],
                                          "document_id": doc(c, "F2026-0001.pdf")})
    p = tx(c, "y2", "2026-01-25", 360000, "Client Alpha", "transfer")
    services.apply_suggestion(c, p["id"])
    s = tx(c, "y3", "2026-01-15", -12000, "Logiciel Pro")
    services.classify_transaction(c, s["id"], {"type": "depense_nouvelle", "category_id": cat(c, "logiciels"), "vat_rate": "20",
                                               "vat_cents": 2000, "document_id": doc(c, "logi.pdf")})
    services.save_expense(c, {"date": "2026-01-20", "supplier_name": "Figma", "label": "Abonnement", "ttc_cents": 3000, "vat_rate": "20",
                              "category_id": cat(c, "logiciels"), "payment_method": "perso", "document_id": doc(c, "figma.pdf")})
    f = tx(c, "y4", "2026-01-31", -900, "Qonto", "qonto_fee")
    services.apply_suggestion(c, f["id"])
    fee = db.q1(c, "SELECT id FROM expenses WHERE ttc_cents=900")
    services.save_expense(c, {"document_id": doc(c, "qonto.pdf")}, fee["id"])
    return inv


def test_declaration_tva_mensuelle_et_report_de_credit(conn):
    build_year(conn)
    jan = vat.prepare(conn, "2026-01-01", "2026-01-31")
    L = jan["data"]["lignes"]
    assert L["08"]["base_cents"] == 300000 and L["08"]["taxe_cents"] == 60000
    assert L["20"]["taxe_cents"] == 2000 + 500 + 150
    assert jan["data"]["a_payer_cents"] == 60000 - 2650
    # Chaque montant renvoie à ses pièces
    assert L["08"]["detail"][0]["piece"] == "F2026-0001"
    r = vat.validate(conn, jan["return"]["id"], force=True)
    assert bal(conn, "44571", "2026-01-31") == 0
    assert bal(conn, "44551", "2026-01-31") == -(60000 - 2650)
    # Paiement de la TVA en février
    t = tx(conn, "v1", "2026-02-15", -(60000 - 2650), "DGFIP TVA", "direct_debit")
    services.classify_transaction(conn, t["id"], {"type": "tva"})
    assert bal(conn, "44551") == 0
    # Février : crédit (dépense seule), reporté en mars
    services.save_expense(conn, {"date": "2026-02-10", "supplier_name": "Bureau", "label": "Fournitures", "ttc_cents": 2400, "vat_rate": "20",
                                 "category_id": cat(conn, "fournitures"), "payment_method": "perso", "document_id": doc(conn, "b.pdf")})
    feb = vat.prepare(conn, "2026-02-01", "2026-02-28")
    assert feb["data"]["credit_cents"] == 400
    vat.validate(conn, feb["return"]["id"], force=True)
    mar = vat.compute(conn, "2026-03-01", "2026-03-31")
    assert mar["lignes"]["22"]["taxe_cents"] == 400 and mar["credit_cents"] == 400
    assert_balanced(conn)
    z = zipfile.ZipFile(io.BytesIO(exports.vat_dossier(conn, "2026-01-01", "2026-01-31")))
    assert "tva/recapitulatif-tva.pdf" in z.namelist() and "tva/tva-detail.xlsx" in z.namelist()
    assert any(n.startswith("justificatifs/") for n in z.namelist())


def test_rapprochement_bancaire(conn):
    build_year(conn)
    solde = sum(t["amount_cents"] for t in db.q(conn, "SELECT amount_cents FROM bank_transactions"))
    assert bal(conn, "512") == solde
    db.set_setting(conn, "bank_statement", {"date": "2026-01-31", "balance_cents": solde})
    assert checks.bank_reconciliation(conn, "2026-01-31")["ecart_cents"] == 0
    db.set_setting(conn, "bank_statement", {"date": "2026-01-31", "balance_cents": solde + 100})
    res = checks.verify_month(conn, "2026-01")
    assert any(i["type"] == "ecart_rapprochement" for i in res["issues"])


def test_verification_et_cloture_du_mois(conn):
    build_year(conn)
    t = tx(conn, "z1", "2026-01-28", -4200, "Inconnu")
    res = checks.verify_month(conn, "2026-01")
    assert not res["ok"] and any(i["type"] == "operation_non_rapprochee" for i in res["issues"])
    with pytest.raises(AccountingError):
        checks.close_month(conn, "2026-01")
    services.classify_transaction(conn, t["id"], {"type": "depense_nouvelle", "category_id": cat(conn, "fournitures"), "vat_regime": "sans_tva",
                                                  "document_id": doc(conn, "z.pdf")})
    res = checks.verify_month(conn, "2026-01")
    assert res["ok"], [i for i in res["issues"] if i["niveau"] == "bloquant"]
    checks.close_month(conn, "2026-01")
    assert conn.execute("SELECT COUNT(*) FROM entries WHERE status='brouillon' AND date<='2026-01-31'").fetchone()[0] == 0
    z = zipfile.ZipFile(io.BytesIO(exports.month_dossier(conn, "2026-01")))
    assert "ecritures/livre-journal.xlsx" in z.namelist()


def test_cloture_annuelle_complete(conn):
    build_year(conn)
    t = tx(conn, "i1", "2026-04-01", -180000, "Apple Store")
    services.classify_transaction(conn, t["id"], {"type": "depense_nouvelle", "category_id": cat(conn, "immobilisation"), "vat_rate": "20",
                                                  "nature": "biens", "label": "MacBook Pro", "document_id": doc(conn, "mac.pdf")})
    r = tx(conn, "r1", "2026-06-05", -100000, "Alexandre Coury", "transfer")
    services.apply_suggestion(conn, r["id"])
    for e in db.q(conn, "SELECT id FROM expenses"):
        services.save_expense(conn, {"status": "valide"}, e["id"])
    prep = closing.prepare(conn)
    assert prep["bilan"]["equilibre"], prep["bilan"]
    inc = prep["compte_de_resultat"]
    # CA 3000, charges : logiciels 100 + 25, frais 7,50, dotation
    dot = round(150000 * 275 / 1096)
    assert inc["chiffre_affaires"] == 300000
    assert inc["resultat_avant_impot"] == 300000 - 10000 - 2500 - 750 - dot
    is_est = prep["impot"]
    rf = (inc["resultat_avant_impot"] // 100) * 100
    assert is_est["is_estime_cents"] == round(rf * 15 / 100)
    assert inc["impot"] == is_est["is_estime_cents"]
    assert prep["bloquant"] == 0, [i for i in prep["anomalies"] if i["niveau"] == "bloquant"]
    assert prep["obligations"]["micro_entreprise"] is True
    fy = prep["exercice"]
    name, fec = exports.fec(conn, fy)
    assert name == "123456789FEC20261231.txt"
    rows = [l.split("\t") for l in fec.split("\r\n") if l]
    assert rows[0][0] == "JournalCode" and all(len(r) == 18 for r in rows)
    deb = sum(int(r[11].replace(",", "")) for r in rows[1:])
    cre = sum(int(r[12].replace(",", "")) for r in rows[1:])
    assert deb == cre
    z = zipfile.ZipFile(io.BytesIO(exports.annual_dossier(conn, fy)))
    names = z.namelist()
    for f in ("etats-financiers/bilan.pdf", "etats-financiers/compte-de-resultat.pdf", "livres/balance-generale.xlsx",
              "livres/grand-livre.xlsx", "etats-financiers/immobilisations-amortissements.pdf", "etats-financiers/creances-dettes.pdf",
              f"fec/{name}", "etats-financiers/projet-decision-associe-unique.pdf"):
        assert f in names, f
    res = closing.close(conn, fy["id"])
    assert res["resultat_cents"] == inc["resultat_net"]
    nxt = res["exercice_suivant"]
    an = ledger.entry_with_lines(conn, res["a_nouveaux_entry"])
    assert sum(l["debit_cents"] for l in an["lines"]) == sum(l["credit_cents"] for l in an["lines"])
    assert ledger.account_balance(conn, "512", nxt["start_date"], nxt["start_date"]) == bal(conn, "512", fy["end_date"])
    with pytest.raises(AccountingError):
        services.save_expense(conn, {"date": "2026-06-01", "supplier_name": "Z", "label": "Z", "ttc_cents": 100, "category_id": cat(conn, "autres"),
                                     "payment_method": "a_payer"})
    closing.allocate_result(conn, fy["id"], 5000)
    assert bal(conn, "1061") == -5000
    assert_balanced(conn)


def test_assistant_sans_ia(conn):
    from app import ai
    build_year(conn)
    r = ai.ask(conn, "Combien la société me doit-elle ?")
    assert r["mode"] == "local" and "5 030,00" in r["answer"].replace(" ", " ")


def test_facturx_extraction():
    from app import ai
    xml = """<?xml version='1.0'?>
<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100"
 xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
 xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">
<rsm:ExchangedDocument><ram:ID>FA-42</ram:ID><ram:TypeCode>380</ram:TypeCode>
<ram:IssueDateTime><udt:DateTimeString format="102">20260115</udt:DateTimeString></ram:IssueDateTime></rsm:ExchangedDocument>
<rsm:SupplyChainTradeTransaction><ram:ApplicableHeaderTradeAgreement>
<ram:SellerTradeParty><ram:Name>Hébergeur SAS</ram:Name><ram:PostalTradeAddress><ram:CountryID>FR</ram:CountryID></ram:PostalTradeAddress></ram:SellerTradeParty>
<ram:BuyerTradeParty><ram:Name>Aurea Media</ram:Name></ram:BuyerTradeParty></ram:ApplicableHeaderTradeAgreement>
<ram:ApplicableHeaderTradeSettlement><ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>
<ram:ApplicableTradeTax><ram:CalculatedAmount>2.00</ram:CalculatedAmount><ram:BasisAmount>10.00</ram:BasisAmount><ram:RateApplicablePercent>20</ram:RateApplicablePercent></ram:ApplicableTradeTax>
<ram:SpecifiedTradeSettlementHeaderMonetarySummation><ram:TaxBasisTotalAmount>10.00</ram:TaxBasisTotalAmount><ram:TaxTotalAmount currencyID="EUR">2.00</ram:TaxTotalAmount><ram:GrandTotalAmount>12.00</ram:GrandTotalAmount></ram:SpecifiedTradeSettlementHeaderMonetarySummation>
</ram:ApplicableHeaderTradeSettlement></rsm:SupplyChainTradeTransaction></rsm:CrossIndustryInvoice>""".encode()
    d = ai.parse_cii(xml)
    assert (d["supplier_name"], d["invoice_number"], d["date"], d["total_ttc"], d["vat_regime"]) == ("Hébergeur SAS", "FA-42", "2026-01-15", "12.00", "fr")


def test_sauvegarde_et_restauration(conn):
    from app import backup, config
    build_year(conn)
    n = conn.execute("SELECT COUNT(*) FROM entries").fetchone()[0]
    archive = backup.full_export()
    conn.execute("DELETE FROM month_closings")
    res = backup.restore(archive)
    assert res["ok"] and res["documents"] >= 4
    c2 = db.get_conn()
    assert c2.execute("SELECT COUNT(*) FROM entries").fetchone()[0] == n
    assert "secret" not in zipfile.ZipFile(io.BytesIO(archive)).namelist()

"""Jeu de données fictif pour découvrir l'application (dossier data-demo séparé)."""
from datetime import date

from . import config, db, services


def seed_if_empty():
    assert config.is_demo(), "Les données de démonstration ne s'installent que dans le dossier de démonstration."
    c = db.get_conn()
    if c.execute("SELECT COUNT(*) FROM bank_transactions").fetchone()[0]:
        return
    y = date.today().year
    c.execute("DELETE FROM fiscal_years")
    c.execute("INSERT INTO fiscal_years(start_date,end_date) VALUES(?,?)", (f"{y}-01-01", f"{y}-12-31"))
    db.set_setting(c, "company", {**db.get_setting(c, "company"), "siren": "000000000", "associate_name": "Jean Démo", "capital_cents": 100000})
    cat = lambda code: db.q1(c, "SELECT id FROM categories WHERE code=?", (code,))["id"]

    def tx(ext, d, amount, who, op="card"):
        services.import_transactions(c, [{"external_id": f"demo:{ext}", "date": f"{y}-{d}", "amount_cents": amount, "label": who,
                                          "counterparty": who, "operation_type": op}], "demo")
        return db.q1(c, "SELECT * FROM bank_transactions WHERE external_id=?", (f"demo:{ext}",))

    def pdf(name):
        with db.tx(c):
            return services.store_document(c, name, b"%PDF-1.4 piece de demonstration " + name.encode())

    t = tx("cap", "01-03", 100000, "Dépôt de capital", "transfer"); services.classify_transaction(c, t["id"], {"type": "capital"})
    t = tx("app", "01-05", 500000, "Jean Démo", "income"); services.apply_suggestion(c, t["id"])
    inv = services.save_sales_invoice(c, {"client_name": "Boulangerie Martin", "issue_date": f"{y}-01-12", "due_date": f"{y}-02-12",
                                          "lines": [{"description": "Création site vitrine WordPress", "unit_price_cents": 240000, "vat_rate": "20"}]})
    t = tx("p1", "02-02", 144000, "Boulangerie Martin", "transfer"); services.apply_suggestion(c, t["id"])
    services.save_sales_invoice(c, {"client_name": "Cabinet Durand", "issue_date": f"{y}-03-04", "due_date": f"{y}-04-04",
                                    "lines": [{"description": "Refonte et référencement", "unit_price_cents": 180000, "vat_rate": "20"}]})
    t = tx("s1", "01-15", -2400, "Hébergeur Web SAS")
    services.classify_transaction(c, t["id"], {"type": "depense_nouvelle", "category_id": cat("hebergement"), "vat_rate": "20", "document_id": pdf("hebergeur.pdf")})
    t = tx("s2", "02-03", -5000, "Google Ireland Ltd")
    services.classify_transaction(c, t["id"], {"type": "depense_nouvelle", "category_id": cat("communication"), "vat_regime": "autoliquidation_ue", "vat_rate": "20"})
    t = tx("s3", "02-20", -4850, "Restaurant Le Midi")
    services.classify_transaction(c, t["id"], {"type": "depense_nouvelle", "category_id": cat("invitations"), "vat_rate": "10", "comment": "Déjeuner client Cabinet Durand"})
    services.save_expense(c, {"date": f"{y}-02-10", "supplier_name": "Figma", "label": "Abonnement mensuel", "ttc_cents": 1440, "vat_rate": "20",
                              "category_id": cat("logiciels"), "payment_method": "perso"})
    t = tx("f1", "02-28", -1080, "Qonto", "qonto_fee"); services.apply_suggestion(c, t["id"])
    tx("u1", "03-05", -18990, "Boutique Informatique")
    tx("r1", "03-10", -50000, "Jean Démo", "transfer")

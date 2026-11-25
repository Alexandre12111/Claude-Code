"""Estimation de l'impôt sur les sociétés (CGI art. 219).

Résultat comptable -> réintégrations / déductions -> résultat fiscal -> IS.
Taux réduit de 15 % jusqu'à 42 500 € de bénéfice (proratisé si l'exercice
ne dure pas 12 mois) si : CA HT < 10 M€, capital entièrement libéré et détenu
à 75 % au moins par des personnes physiques. Le surplus est imposé à 25 %.
"""
from datetime import date

from . import db, ledger, reports, rules
from .money import pct_of, prorata


def months_between(start: str, end: str) -> int:
    s, e = date.fromisoformat(start), date.fromisoformat(end)
    return (e.year - s.year) * 12 + e.month - s.month + 1


def estimate(conn, fy: dict) -> dict:
    inc = reports.income_statement(conn, fy["start_date"], fy["end_date"])
    resultat_comptable = inc["resultat_avant_impot"]
    company = db.get_setting(conn, "company")
    taxs = db.get_setting(conn, "tax")
    reint = []
    # Charges non déductibles (part non déductible des dépenses)
    for x in db.q(conn, "SELECT x.*, c.name cat, c.rule FROM expenses x LEFT JOIN categories c ON c.id=x.category_id"
                        " WHERE x.date BETWEEN ? AND ? AND x.deductible<100 AND IFNULL(c.rule,'')<>'personnel'",
                  (fy["start_date"], fy["end_date"])):
        nonrec = x["vat_cents"] - pct_of(x["vat_cents"], x["vat_recovery"]) if x["vat_regime"] == "fr" else 0
        charge = x["ht_cents"] + nonrec
        if x["fixed_asset_id"]:
            continue
        part = charge - pct_of(charge, x["deductible"])
        if part:
            reint.append({"libelle": f"{x['cat'] or 'Dépense'} : {x['supplier_name'] or ''} {x['label']} ({100 - x['deductible']} % non déductible)",
                          "montant_cents": part, "source": f"#/depenses?id={x['id']}"})
    # Amendes et pénalités comptabilisées directement en 6712 sans dépense
    amendes = ledger.account_balance(conn, "6712", fy["end_date"], fy["start_date"])
    already = sum(r["montant_cents"] for r in reint if "Amendes" in r["libelle"])
    if amendes - already > 0:
        reint.append({"libelle": "Amendes et pénalités (CGI art. 39-2)", "montant_cents": amendes - already})
    deductions = []
    resultat_fiscal_avant_deficits = resultat_comptable + sum(r["montant_cents"] for r in reint) - sum(d["montant_cents"] for d in deductions)
    deficit = taxs.get("loss_carryforward_cents") or 0
    imputable = 0
    if resultat_fiscal_avant_deficits > 0 and deficit > 0:
        cap = 100_000_000 + max(0, (resultat_fiscal_avant_deficits - 100_000_000) // 2)
        imputable = min(deficit, resultat_fiscal_avant_deficits, cap)
    rf = resultat_fiscal_avant_deficits - imputable
    rf_euros = (rf // 100) * 100 if rf > 0 else rf   # arrondi à l'euro inférieur
    ca = inc["chiffre_affaires"]
    on = fy["end_date"]
    eligible_reasons = []
    if ca >= rules.get_rule("is_ca_max_taux_reduit_cents", on) * months_between(fy["start_date"], fy["end_date"]) // 12:
        eligible_reasons.append("chiffre d'affaires supérieur à 10 M€")
    if not company.get("capital_fully_paid", True):
        eligible_reasons.append("capital non entièrement libéré")
    if not company.get("held_by_natural_persons_75", True):
        eligible_reasons.append("capital non détenu à 75 % par des personnes physiques")
    eligible = not eligible_reasons
    months = months_between(fy["start_date"], fy["end_date"])
    plafond = prorata(rules.get_rule("is_plafond_taux_reduit_cents", on), months, 12)
    t_red, t_norm = rules.get_rule("is_taux_reduit", on), rules.get_rule("is_taux_normal", on)
    tranches = []
    is_total = 0
    if rf_euros > 0:
        if eligible:
            b1 = min(rf_euros, plafond)
            tranches.append({"libelle": f"{t_red} % jusqu'à {plafond/100:,.0f} €".replace(",", " "), "base_cents": b1, "impot_cents": pct_of(b1, t_red)})
            if rf_euros > plafond:
                b2 = rf_euros - plafond
                tranches.append({"libelle": f"{t_norm} % au delà", "base_cents": b2, "impot_cents": pct_of(b2, t_norm)})
        else:
            tranches.append({"libelle": f"{t_norm} %", "base_cents": rf_euros, "impot_cents": pct_of(rf_euros, t_norm)})
        is_total = sum(t["impot_cents"] for t in tranches)
    acomptes_payes = conn.execute("SELECT COALESCE(SUM(a.amount_cents),0) FROM allocations a JOIN bank_transactions t ON t.id=a.bank_transaction_id"
                                  " WHERE a.target_type='is' AND t.date BETWEEN ? AND ?", (fy["start_date"], fy["end_date"])).fetchone()[0] * -1
    prev_is = taxs.get("previous_is_cents") or 0
    seuil = rules.get_rule("is_seuil_acomptes_cents", on)
    acomptes_prevus = []
    if prev_is > seuil:
        y = int(fy["end_date"][:4])
        for m in ("03-15", "06-15", "09-15", "12-15"):
            acomptes_prevus.append({"echeance": f"{y}-{m}", "montant_cents": prev_is // 4})
    return {
        "resultat_comptable_cents": resultat_comptable,
        "reintegrations": reint,
        "deductions": deductions,
        "deficits_imputes_cents": imputable,
        "resultat_fiscal_cents": rf,
        "resultat_fiscal_arrondi_cents": rf_euros,
        "taux_reduit_eligible": eligible,
        "taux_reduit_motifs_exclusion": eligible_reasons,
        "plafond_taux_reduit_cents": plafond,
        "tranches": tranches,
        "is_estime_cents": is_total,
        "acomptes_payes_cents": acomptes_payes,
        "acomptes_prevus": acomptes_prevus,
        "solde_estime_cents": is_total - acomptes_payes,
        "echeance_solde": "15 du 4e mois suivant la clôture (15 mai pour un exercice civil), avec le relevé de solde 2572.",
        "remarques": [
            "Estimation calculée sur les écritures enregistrées : elle évolue avec chaque opération.",
            "Pas d'acompte pour le premier exercice, ni si l'IS de l'exercice précédent est inférieur à 3 000 €.",
            "La trésorerie disponible n'est pas le bénéfice : le résultat tient compte des factures non encaissées, des dettes et des amortissements.",
        ],
    }

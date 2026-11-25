"""Exports : FEC, Excel, CSV, PDF, dossiers ZIP (mois, TVA, exercice)."""
import csv
import io
import json
import zipfile
from datetime import date

from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill, Alignment
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import mm
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle

from . import checks, closing, config, db, reports, rules, services, tax, vat
from .money import fmt, plain

# ================================================================= FEC
FEC_COLUMNS = ["JournalCode", "JournalLib", "EcritureNum", "EcritureDate", "CompteNum", "CompteLib", "CompAuxNum",
               "CompAuxLib", "PieceRef", "PieceDate", "EcritureLib", "Debit", "Credit", "EcritureLet", "DateLet",
               "ValidDate", "Montantdevise", "Idevise"]


def fec(conn, fy) -> tuple[str, str]:
    """Fichier des écritures comptables (LPF art. A47 A-1). Retourne (nom, contenu)."""
    company = db.get_setting(conn, "company")
    siren = (company.get("siren") or "000000000").replace(" ", "")
    rows = db.q(conn, "SELECT e.*, l.account, l.aux, l.aux_label, l.label line_label, l.debit_cents, l.credit_cents, l.lettrage, a.label account_label"
                      " FROM entries e JOIN entry_lines l ON l.entry_id=e.id LEFT JOIN accounts a ON a.number=l.account"
                      " WHERE e.date BETWEEN ? AND ? ORDER BY CASE e.journal WHEN 'AN' THEN 0 ELSE 1 END, e.date, e.number, e.id, l.id",
                (fy["start_date"], fy["end_date"]))
    names = {f"C{p['id']:04d}": p["name"] for p in db.q(conn, "SELECT id,name FROM parties WHERE kind='client'")}
    names.update({f"F{p['id']:04d}": p["name"] for p in db.q(conn, "SELECT id,name FROM parties WHERE kind='supplier'")})
    names["ASSOCIE"] = company.get("associate_name") or "Associé unique"
    out = io.StringIO()
    out.write("\t".join(FEC_COLUMNS) + "\r\n")
    d8 = lambda s: (s or "")[:10].replace("-", "")
    clean = lambda s: (s or "").replace("\t", " ").replace("\n", " ").replace("\r", " ")
    for r in rows:
        num = r["number"] or f"PROV{r['id']:06d}"
        vals = [r["journal"], rules.JOURNALS.get(r["journal"], r["journal"]), num, d8(r["date"]), r["account"],
                clean(r["account_label"]), r["aux"] or "", clean(names.get(r["aux"] or "", r["aux_label"] or "")) if r["aux"] else "",
                clean(r["piece_ref"] or str(r["id"])), d8(r["piece_date"] or r["date"]), clean(r["line_label"] or r["label"]),
                plain(r["debit_cents"]), plain(r["credit_cents"]), "", "", d8(r["validated_at"]), "", ""]
        out.write("\t".join(vals) + "\r\n")
    return f"{siren}FEC{fy['end_date'].replace('-', '')}.txt", out.getvalue()


# ================================================================= Excel / CSV
def xlsx(sheets: dict[str, list[list]]) -> bytes:
    wb = Workbook()
    wb.remove(wb.active)
    for name, rows in sheets.items():
        ws = wb.create_sheet(name[:31])
        for i, row in enumerate(rows):
            ws.append(row)
            if i == 0:
                for c in ws[1]:
                    c.font = Font(bold=True, color="FFFFFF")
                    c.fill = PatternFill("solid", fgColor="1F2430")
        for col in ws.columns:
            width = max((len(str(c.value)) for c in col if c.value is not None), default=8)
            ws.column_dimensions[col[0].column_letter].width = min(max(10, width + 2), 60)
    buf = io.BytesIO()
    wb.save(buf)
    return buf.getvalue()


def csv_bytes(rows: list[list]) -> bytes:
    buf = io.StringIO()
    w = csv.writer(buf, delimiter=";")
    for r in rows:
        w.writerow(r)
    return ("﻿" + buf.getvalue()).encode("utf-8")


def eur(c):
    return round((c or 0) / 100, 2)


# ================================================================= PDF
_styles = getSampleStyleSheet()
_h1 = ParagraphStyle("h1", parent=_styles["Heading1"], fontSize=16, spaceAfter=6, textColor=colors.HexColor("#1F2430"))
_h2 = ParagraphStyle("h2", parent=_styles["Heading2"], fontSize=12, spaceBefore=10, spaceAfter=4, textColor=colors.HexColor("#1F2430"))
_p = ParagraphStyle("p", parent=_styles["BodyText"], fontSize=8.5, leading=11)
_small = ParagraphStyle("s", parent=_p, fontSize=7.5, textColor=colors.HexColor("#555555"))


def _table(rows, widths=None, num_cols=()):
    data = [[Paragraph(str(c), _p) if isinstance(c, str) and len(c) > 40 else c for c in r] for r in rows]
    t = Table(data, colWidths=widths, repeatRows=1)
    st = [("FONT", (0, 0), (-1, -1), "Helvetica", 8), ("FONT", (0, 0), (-1, 0), "Helvetica-Bold", 8),
          ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#EEF0F4")), ("LINEBELOW", (0, 0), (-1, -1), 0.25, colors.HexColor("#DDDDDD")),
          ("VALIGN", (0, 0), (-1, -1), "TOP")]
    for c in num_cols:
        st.append(("ALIGN", (c, 0), (c, -1), "RIGHT"))
    t.setStyle(TableStyle(st))
    return t


def pdf(title: str, blocks: list) -> bytes:
    """blocks : ("h", texte) | ("p", texte) | ("table", rows, widths, num_cols)"""
    buf = io.BytesIO()
    conn = db.get_conn()
    company = db.get_setting(conn, "company")
    doc = SimpleDocTemplate(buf, pagesize=A4, leftMargin=15 * mm, rightMargin=15 * mm, topMargin=15 * mm, bottomMargin=15 * mm,
                            title=title, author=company.get("name"))
    story = [Paragraph(title, _h1), Paragraph(f"{company.get('name')} {company.get('legal_form')} · SIREN {company.get('siren') or 'à renseigner'} · édité le {date.today().strftime('%d/%m/%Y')}", _small), Spacer(1, 6)]
    for b in blocks:
        if b[0] == "h":
            story.append(Paragraph(b[1], _h2))
        elif b[0] == "p":
            story.append(Paragraph(b[1], _p))
        elif b[0] == "table":
            story.append(_table(b[1], b[2] if len(b) > 2 else None, b[3] if len(b) > 3 else ()))
            story.append(Spacer(1, 4))
    doc.build(story)
    return buf.getvalue()


# ================================================================= états
def statements_pdf(conn, fy, prep=None) -> dict[str, bytes]:
    prep = prep or closing.prepare(conn, fy["id"])
    inc, bs = prep["compte_de_resultat"], prep["bilan"]
    status = "DOCUMENT PROVISOIRE : " + prep["statut"] if not prep["definitif"] and fy["status"] != "closed" else "Exercice clôturé" if fy["status"] == "closed" else "Prêt à clôturer"
    period = f"Exercice du {fy['start_date']} au {fy['end_date']}"
    files = {}
    # Bilan
    blocks = [("p", f"{period}. {status}"), ("h", "Actif")]
    blocks.append(("table", [["Rubrique", "Montant"]] + [[a["rubrique"], fmt(a["total"])] for a in bs["actif"]] + [["Total actif", fmt(bs["total_actif"])]], [130 * mm, 40 * mm], (1,)))
    blocks.append(("h", "Passif"))
    blocks.append(("table", [["Rubrique", "Montant"]] + [[a["rubrique"], fmt(a["total"])] for a in bs["passif"]] + [["Total passif", fmt(bs["total_passif"])]], [130 * mm, 40 * mm], (1,)))
    files["bilan.pdf"] = pdf("Bilan", blocks)
    # Compte de résultat
    blocks = [("p", f"{period}. {status}")]
    for key, sec in inc["sections"].items():
        if not sec["lines"]:
            continue
        blocks.append(("h", sec["label"]))
        blocks.append(("table", [["Compte", "Libellé", "Montant"]] + [[l["account"], l["label"] or "", fmt(l["amount"])] for l in sec["lines"]]
                       + [["", "Total", fmt(sec["total"])]], [25 * mm, 110 * mm, 35 * mm], (2,)))
    blocks.append(("table", [["Synthèse", "Montant"], ["Résultat d'exploitation", fmt(inc["resultat_exploitation"])],
                             ["Résultat financier", fmt(inc["resultat_financier"])], ["Résultat exceptionnel", fmt(inc["resultat_exceptionnel"])],
                             ["Impôt sur les bénéfices", fmt(inc["impot"])], ["Résultat net", fmt(inc["resultat_net"])]], [130 * mm, 40 * mm], (1,)))
    files["compte-de-resultat.pdf"] = pdf("Compte de résultat", blocks)
    # Immobilisations et amortissements
    rows = [["Bien", "Compte", "Mise en service", "Durée", "Valeur brute", "Dotation", "Cumul", "VNC"]]
    for a in prep["immobilisations"]:
        rows.append([a["label"], a["account"], a["in_service_date"], f"{a['duration_years']} ans", fmt(a["cost_cents"]),
                     fmt(a["dotation_cents"]), fmt(a["cumul_fin_cents"]), fmt(a["vnc_fin_cents"])])
    files["immobilisations-amortissements.pdf"] = pdf("Tableau des immobilisations et des amortissements",
                                                      [("p", period), ("table", rows, None, (4, 5, 6, 7))])
    # Créances et dettes
    cd = prep["creances_dettes"]
    files["creances-dettes.pdf"] = pdf("État des créances et des dettes", [
        ("p", f"Au {fy['end_date']}. {cd['note']}"), ("h", "Créances"),
        ("table", [["Compte", "Tiers", "Montant"]] + [[r["account"], r["tiers"] or r["label"] or "", fmt(r["solde"])] for r in cd["creances"]], None, (2,)),
        ("h", "Dettes"),
        ("table", [["Compte", "Tiers", "Montant"]] + [[r["account"], r["tiers"] or r["label"] or "", fmt(r["solde"])] for r in cd["dettes"]], None, (2,))])
    # IS
    i = prep["impot"]
    rows = [["Élément", "Montant"], ["Résultat comptable avant impôt", fmt(i["resultat_comptable_cents"])]]
    rows += [[f"Réintégration : {r['libelle']}", fmt(r["montant_cents"])] for r in i["reintegrations"]]
    rows += [["Déficits antérieurs imputés", fmt(-i["deficits_imputes_cents"])], ["Résultat fiscal", fmt(i["resultat_fiscal_cents"])]]
    rows += [[t["libelle"], fmt(t["impot_cents"])] for t in i["tranches"]]
    rows += [["IS estimé", fmt(i["is_estime_cents"])], ["Acomptes versés", fmt(i["acomptes_payes_cents"])], ["Solde estimé", fmt(i["solde_estime_cents"])]]
    files["impot-societes.pdf"] = pdf("Détermination de l'impôt sur les sociétés", [("p", period), ("table", rows, [130 * mm, 40 * mm], (1,))])
    # Liasse
    blocks = [("p", prep["liasse"]["note"])]
    for k, v in prep["liasse"].items():
        if k == "note":
            continue
        blocks += [("h", k), ("table", [["Poste", "Montant"]] + [[a, fmt(b)] for a, b in v], [130 * mm, 40 * mm], (1,))]
    files["elements-liasse-fiscale.pdf"] = pdf("Éléments pour la liasse fiscale (2065 et annexes)", blocks)
    # Obligations et décision de l'associé unique
    ob = prep["obligations"]
    files["obligations-annuelles.pdf"] = pdf("Obligations annuelles applicables", [
        ("p", f"Régime d'imposition : {ob['regime_is']}. Micro entreprise au sens comptable : {'oui' if ob['micro_entreprise'] else 'non'}."),
        ("table", [["Obligation", "Échéance", "Remarque"]] + [[o["obligation"], o["echeance"], o.get("note", "")] for o in ob["liste"]], [70 * mm, 40 * mm, 70 * mm])])
    aff = prep["affectation_proposee"]
    company = db.get_setting(conn, "company")
    files["projet-decision-associe-unique.pdf"] = pdf("Projet de décision de l'associé unique", [
        ("p", f"L'associé unique de la société {company.get('name')}, {company.get('legal_form')} au capital de {fmt(company.get('capital_cents', 0))}, "
              f"après avoir pris connaissance des comptes de l'exercice clos le {fy['end_date']}, décide :"),
        ("p", f"Première décision : approbation des comptes annuels de l'exercice faisant apparaître un résultat de {fmt(aff['resultat_cents'])}."),
        ("p", f"Deuxième décision : affectation du résultat : réserve légale {fmt(aff['reserve_legale_cents'])}, report à nouveau {fmt(aff['report_a_nouveau_cents'])}."),
        ("p", "Troisième décision : rappel des dividendes distribués au titre des trois exercices précédents : néant (à compléter)."),
        ("p", "Fait le ____________, l'associé unique : ____________"),
        ("p", "Projet généré automatiquement à adapter. " + aff["note"])])
    return files


def ledger_exports(conn, start, end) -> dict[str, bytes]:
    tb = reports.trial_balance(conn, start, end)
    gl = reports.general_ledger(conn, start, end)
    jr = reports.journal(conn, start, end)
    bal_rows = [["Compte", "Libellé", "Débit", "Crédit", "Solde débiteur", "Solde créditeur"]] + \
               [[r["account"], r["label"], eur(r["debit"]), eur(r["credit"]), eur(r["solde_debiteur"]), eur(r["solde_crediteur"])] for r in tb["rows"]] + \
               [["Total", "", eur(tb["totals"]["debit"]), eur(tb["totals"]["credit"]), "", ""]]
    gl_rows = [["Compte", "Libellé compte", "Date", "Journal", "N° écriture", "Pièce", "Libellé", "Débit", "Crédit", "Solde cumulé", "Statut"]]
    for acc in gl:
        for l in acc["lines"]:
            gl_rows.append([acc["account"], acc["label"], l["date"], l["journal"], l["number"] or "provisoire", l["piece_ref"], l["label"],
                            eur(l["debit_cents"]), eur(l["credit_cents"]), eur(l["cumul"]), l["status"]])
    jr_rows = [["Date", "Journal", "N° écriture", "Pièce", "Compte", "Tiers", "Libellé", "Débit", "Crédit", "Statut"]]
    for e in jr:
        for l in e["lines"]:
            jr_rows.append([e["date"], e["journal"], e["number"] or "provisoire", e["piece_ref"], l["account"], l["aux"] or "", l["label"],
                            eur(l["debit_cents"]), eur(l["credit_cents"]), e["status"]])
    files = {"balance-generale.xlsx": xlsx({"Balance": bal_rows}), "grand-livre.xlsx": xlsx({"Grand livre": gl_rows}),
             "livre-journal.xlsx": xlsx({"Journal": jr_rows}), "balance-generale.csv": csv_bytes(bal_rows),
             "grand-livre.csv": csv_bytes(gl_rows), "livre-journal.csv": csv_bytes(jr_rows)}
    files["balance-generale.pdf"] = pdf(f"Balance générale du {start} au {end}",
                                        [("table", [r[:2] + [fmt(int(round((x or 0) * 100))) if isinstance(x, (int, float)) else x for x in r[2:]] if i else r
                                                    for i, r in enumerate(bal_rows)], None, (2, 3, 4, 5))])
    files["livre-journal.pdf"] = pdf(f"Livre journal du {start} au {end}",
                                     [("table", [jr_rows[0][:9]] + [r[:7] + [fmt(int(round(r[7] * 100))), fmt(int(round(r[8] * 100)))] for r in jr_rows[1:]],
                                       [16 * mm, 10 * mm, 22 * mm, 18 * mm, 15 * mm, 13 * mm, 50 * mm, 18 * mm, 18 * mm], (7, 8))])
    return files


def vat_files(conn, start, end) -> dict[str, bytes]:
    res = vat.prepare(conn, start, end)
    data, form = res["data"], res["form"]
    L = data["lignes"]
    statut = res.get("statut", "")
    rows = [["Ligne", "Libellé", "Base HT", "Taxe"]]
    for code, l in L.items():
        if (l["base_cents"] or 0) == 0 and (l["taxe_cents"] or 0) == 0 and code not in ("16", "23", "28", "32"):
            continue
        rows.append([code, l["libelle"], fmt(l["base_cents"]) if l["base_cents"] is not None else "", fmt(l["taxe_cents"]) if l["taxe_cents"] is not None else ""])
    blocks = [("p", f"Période du {start} au {end}. Formulaire {form}. Statut : {statut}."), ("table", rows, [14 * mm, 100 * mm, 30 * mm, 30 * mm], (2, 3))]
    if form == "CA12":
        c = vat.ca12_view(conn, data, end)
        blocks += [("h", "Récapitulatif CA12"), ("table", [["Rubrique", "Montant"]] + [[r["libelle"], fmt(r["montant_cents"])] for r in c["rubriques"]], None, (1,)),
                   ("p", c["note"])]
    if data["non_recuperable_cents"]:
        blocks.append(("p", f"TVA non récupérable sur la période (incluse dans les charges) : {fmt(data['non_recuperable_cents'])}."))
    if res["issues"]:
        blocks += [("h", "Anomalies à vérifier"), ("table", [["Niveau", "Message"]] + [[i["niveau"], i["message"]] for i in res["issues"]], [25 * mm, 150 * mm])]
    blocks.append(("p", "Reportez ces montants sur votre espace professionnel impots.gouv.fr. Aucune déclaration ni paiement n'est transmis automatiquement."))
    files = {"recapitulatif-tva.pdf": pdf(f"Déclaration de TVA {form}", blocks)}
    det = [["Ligne", "Date", "Libellé", "Pièce", "Base HT", "Taxe", "Provisoire"]]
    for code, l in L.items():
        for d in l["detail"]:
            det.append([code, d["date"], d["libelle"], d["piece"], eur(d["base_cents"]), eur(d["taxe_cents"]), "oui" if d["provisoire"] else ""])
    summary = [["Ligne", "Libellé", "Base HT", "Taxe"]] + [[c, l["libelle"], eur(l["base_cents"]) if l["base_cents"] is not None else "", eur(l["taxe_cents"]) if l["taxe_cents"] is not None else ""] for c, l in L.items()]
    files["tva-detail.xlsx"] = xlsx({"Rubriques": summary, "Détail": det})
    files["tva-detail.csv"] = csv_bytes(det)
    return files


def _documents_for_period(conn, start, end):
    return db.q(conn, "SELECT DISTINCT d.* FROM documents d WHERE d.id IN ("
                      " SELECT document_id FROM entries WHERE date BETWEEN ? AND ? AND document_id IS NOT NULL"
                      " UNION SELECT document_id FROM expenses WHERE date BETWEEN ? AND ? AND document_id IS NOT NULL"
                      " UNION SELECT document_id FROM sales_invoices WHERE issue_date BETWEEN ? AND ? AND document_id IS NOT NULL)",
                (start, end, start, end, start, end))


def _add_documents(z, conn, start, end, prefix="justificatifs/"):
    n = 0
    for d in _documents_for_period(conn, start, end):
        p = services.document_path(d)
        if p.exists():
            z.write(p, prefix + d["path"])
            n += 1
    return n


def zip_bytes(files: dict[str, bytes], conn=None, docs_period=None) -> bytes:
    buf = io.BytesIO()
    with zipfile.ZipFile(buf, "w", zipfile.ZIP_DEFLATED) as z:
        for name, content in files.items():
            z.writestr(name, content)
        if conn and docs_period:
            _add_documents(z, conn, *docs_period)
    return buf.getvalue()


def vat_dossier(conn, start, end) -> bytes:
    files = {f"tva/{k}": v for k, v in vat_files(conn, start, end).items()}
    files.update({f"ecritures/{k}": v for k, v in ledger_exports(conn, start, end).items() if k.startswith("livre-journal")})
    return zip_bytes(files, conn, (start, end))


def month_dossier(conn, month) -> bytes:
    start, end = checks.month_bounds(month)
    res = checks.verify_month(conn, month)
    files = {f"ecritures/{k}": v for k, v in ledger_exports(conn, start, end).items()}
    files["controles.pdf"] = pdf(f"Vérification du mois {month}", [
        ("p", "Aucune anomalie." if not res["issues"] else f"{len(res['issues'])} point(s) relevé(s)."),
        ("table", [["Niveau", "Message"]] + [[i["niveau"], i["message"]] for i in res["issues"]], [25 * mm, 150 * mm])])
    txs = db.q(conn, "SELECT * FROM bank_transactions WHERE date BETWEEN ? AND ? ORDER BY date", (start, end))
    files["banque.xlsx"] = xlsx({"Opérations": [["Date", "Tiers", "Libellé", "Montant", "Statut", "Commentaire"]] +
                                 [[t["date"], t["counterparty"], t["label"], eur(t["amount_cents"]), t["status"], t["comment"]] for t in txs]})
    exps = db.q(conn, "SELECT x.*, c.name cat FROM expenses x LEFT JOIN categories c ON c.id=x.category_id WHERE x.date BETWEEN ? AND ? ORDER BY date", (start, end))
    files["depenses.xlsx"] = xlsx({"Dépenses": [["Date", "Fournisseur", "Libellé", "HT", "TVA", "TTC", "Catégorie", "Paiement", "Justificatif", "Statut"]] +
                                   [[x["date"], x["supplier_name"], x["label"], eur(x["ht_cents"]), eur(x["vat_cents"]), eur(x["ttc_cents"]), x["cat"],
                                     x["payment_method"], "oui" if x["document_id"] else "non", x["status"]] for x in exps]})
    return zip_bytes(files, conn, (start, end))


def annual_dossier(conn, fy) -> bytes:
    prep = closing.prepare(conn, fy["id"])
    files = {f"etats-financiers/{k}": v for k, v in statements_pdf(conn, fy, prep).items()}
    files.update({f"livres/{k}": v for k, v in ledger_exports(conn, fy["start_date"], fy["end_date"]).items()})
    name, content = fec(conn, fy)
    files[f"fec/{name}"] = content.encode("utf-8")
    files["fec/LISEZMOI.txt"] = ("Fichier des écritures comptables au format texte, séparateur tabulation, encodage UTF-8, "
                                 "montants avec virgule décimale (LPF art. A47 A-1).\n").encode("utf-8")
    for r in db.q(conn, "SELECT * FROM vat_returns WHERE period_start>=? AND period_end<=? ORDER BY period_start", (fy["start_date"], fy["end_date"])):
        for k, v in vat_files(conn, r["period_start"], r["period_end"]).items():
            files[f"tva/{r['period_start'][:7]}/{k}"] = v
    ann = vat.compute(conn, fy["start_date"], fy["end_date"])
    files["tva/recapitulatif-annuel.xlsx"] = xlsx({"TVA annuelle": [["Ligne", "Libellé", "Base", "Taxe"]] +
                                                   [[c, l["libelle"], eur(l["base_cents"]) if l["base_cents"] is not None else "", eur(l["taxe_cents"]) if l["taxe_cents"] is not None else ""] for c, l in ann["lignes"].items()]})
    cl = db.q(conn, "SELECT e.* FROM entries e WHERE e.date=? AND e.source_type IN ('amortissement','is','manuel') ORDER BY id", (fy["end_date"],))
    files["ecritures-de-cloture.xlsx"] = xlsx({"Inventaire": [["Date", "Journal", "N°", "Libellé", "Compte", "Débit", "Crédit"]] +
                                               [[e["date"], e["journal"], e["number"], e["label"], l["account"], eur(l["debit_cents"]), eur(l["credit_cents"])]
                                                for e in cl for l in db.q(conn, "SELECT * FROM entry_lines WHERE entry_id=?", (e["id"],))]})
    files["controles.json"] = json.dumps({"statut": prep["statut"], "anomalies": prep["anomalies"],
                                          "informations_manquantes": prep["informations_manquantes"]}, ensure_ascii=False, indent=2).encode()
    assoc = services.associate_summary(conn, fy["end_date"])
    files["compte-courant-associe.xlsx"] = xlsx({"455": [["Date", "Libellé", "Débit", "Crédit"]] +
                                                [[m["date"], m["line_label"] or m["label"], eur(m["debit_cents"]), eur(m["credit_cents"])] for m in assoc["mouvements"]] +
                                                [["", "Solde (créditeur = dû par la société)", "", eur(assoc["solde_cents"])]]})
    return zip_bytes(files, conn, (fy["start_date"], fy["end_date"]))

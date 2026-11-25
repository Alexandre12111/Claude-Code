# Architecture

## Choix techniques

| Besoin | Choix | Raison |
|---|---|---|
| Application locale, sans hébergement | Serveur Python FastAPI sur 127.0.0.1 + interface web | Aucun coût, fonctionne hors ligne, interface moderne dans le navigateur |
| Base de données | SQLite (fichier unique, mode WAL) | Fiable, sans serveur, sauvegarde par simple copie cohérente |
| Interface | HTML, CSS et JavaScript sans framework ni dépendance externe | Rien à compiler, rien à télécharger, fonctionne hors ligne, facile à maintenir |
| Montants | Entiers en centimes, `Decimal` pour les taux | Zéro erreur d'arrondi |
| Exports | openpyxl (Excel), reportlab (PDF), csv, zip | Bibliothèques stables et locales |
| Banque | API officielle Qonto Business API v2 + import CSV | Officiel, lecture seule |
| IA | API Anthropic (Claude), facultative | Lecture des justificatifs et assistant ; jamais dans les calculs |

## Modules (`app/`)

| Fichier | Rôle |
|---|---|
| `db.py` | Schéma, déclencheurs de protection, paramètres, historique |
| `money.py` | Calculs monétaires en centimes |
| `rules.py` | Plan comptable, catégories, règles fiscales datées |
| `ledger.py` | Écritures : création équilibrée, validation numérotée, contrepassation, contrôle d'intégrité |
| `posting.py` | Génération automatique des écritures depuis les pièces, exigibilité de la TVA |
| `services.py` | Tiers, pièces, factures, dépenses, banque, rapprochement, associé, immobilisations |
| `checks.py` | Contrôles (mois, TVA, clôture), rapprochement bancaire |
| `vat.py` | Périodes, calcul et rubriques CA3 / CA12, validation, report de crédit |
| `tax.py` | Estimation de l'IS |
| `reports.py` | Balance, grand livre, journal, compte de résultat, bilan, créances et dettes |
| `closing.py` | Préparation et clôture annuelle, obligations, liasse 2033, affectation |
| `exports.py` | FEC, PDF, Excel, CSV, dossiers ZIP |
| `qonto.py` | Synchronisation API et import CSV |
| `ai.py` | Factur-X, extraction par Claude, rapprochement, assistant |
| `backup.py` | Sauvegardes, export complet, restauration |
| `main.py` | API HTTP locale |

## Flux d'une information saisie une seule fois

```
Opération Qonto ──► bank_transactions ──► écriture BQ (512 / 471 tant que non affectée)
       │                                    │
       │ affectation (allocations)          ▼
       ├─► dépense ──► écriture AC (6xx, 4456x, 401 ou 455)  ──► TVA déductible
       ├─► facture client ──► VT (411, 706, 44587) ──► encaissement : 44587 → 44571 ──► TVA collectée
       ├─► apport / remboursement ──► 455
       ├─► capital ──► 1013
       └─► TVA, IS, virement interne, autre compte
                                            ▼
                              Grand livre ──► TVA, IS, bilan, FEC, exports
```

Le compte d'attente 471 garantit que le solde comptable du 512 est toujours égal au solde bancaire : tant qu'une opération n'est pas justifiée, elle reste visible en 471 et bloque la clôture.

## Schéma de la base

* `fiscal_years` exercices ; `accounts` plan comptable ; `categories` catégories de dépenses et règles ; `parties` clients et fournisseurs.
* `documents` justificatifs (empreinte SHA-256 unique, chemin rangé par exercice / mois / type / tiers, données extraites).
* `bank_transactions` opérations bancaires (identifiant externe unique, proposition d'affectation).
* `allocations` lien opération bancaire ↔ facture, dépense, associé, capital, TVA, IS, compte.
* `sales_invoices`, `sales_invoice_lines` factures et avoirs ; `expenses` dépenses et notes de frais.
* `associate_movements` apports, remboursements, capital ; `fixed_assets` immobilisations.
* `entries`, `entry_lines` écritures (source, pièce, statut, numéro, contrepassation, code TVA et base).
* `vat_returns` déclarations ; `month_closings` mois clôturés ; `audit_log` historique non modifiable ; `supplier_rules` apprentissage fournisseur → catégorie.

Protections dans la base : une écriture validée ne peut être ni modifiée ni supprimée, une pièce validée ne peut pas être supprimée, l'historique ne peut pas être modifié.

## Évolutions prévues par l'architecture

* **Facture électronique** : la réception via plateforme agréée alimentera `documents` (Factur-X, UBL, CII déjà lisibles pour le CII). L'émission pourra s'ajouter dans `services.save_sales_invoice` en générant un Factur-X.
* **Nouvelles règles fiscales** : ajouter une entrée datée dans `rules.RULES`.
* **Plusieurs comptes bancaires** : sous comptes 512xxx et champ `bank_account` déjà présent.

# Aurea Compta

Logiciel de comptabilité personnel de la SASU **Aurea Media**. Application locale, mono utilisateur, sans hébergement : une interface web servie par votre ordinateur (http://127.0.0.1:8765), une base SQLite et vos justificatifs rangés dans un dossier local.

## Ouvrir l'application (sans terminal)

Prérequis, une seule fois : Python 3.11 ou plus récent (https://www.python.org/downloads/, sous Windows cochez « Add Python to PATH »).

* **Mac** : double clic sur **Aurea Compta.app**. Au premier lancement, macOS peut bloquer une application téléchargée : ouvrez Réglages système, Confidentialité et sécurité, puis « Ouvrir quand même ». Glissez ensuite l'application dans le Dock.
* **Windows** : double clic sur **Aurea Compta.vbs**. Pour un raccourci sur le bureau : clic droit, Envoyer vers, Bureau.

La première ouverture installe tout automatiquement (1 à 2 minutes), ensuite l'application s'ouvre directement dans le navigateur sur http://127.0.0.1:8765. Si elle est déjà ouverte, le double clic affiche simplement la fenêtre. Pour l'arrêter : bouton ⏻ en bas du menu.

Sans Internet, tout fonctionne sauf la synchronisation Qonto et l'IA.

## Sur le téléphone

Paramètres, onglet **Téléphone** : choisissez un mot de passe, cochez « Autoriser l'accès depuis mon téléphone », puis scannez le QR code avec le téléphone. Ajoutez ensuite la page à l'écran d'accueil (iPhone : Partager, « Sur l'écran d'accueil » ; Android : menu ⋮, « Ajouter à l'écran d'accueil ») : elle s'ouvre comme une application, avec toutes vos données et vos justificatifs, et permet de photographier un ticket.

* L'ordinateur doit être allumé avec l'application ouverte : les données restent chez vous, sans hébergement.
* Même Wi-Fi : fonctionne directement. Hors de chez vous : installez Tailscale (gratuit) sur l'ordinateur et le téléphone avec le même compte ; une adresse chiffrée apparaît dans l'onglet Téléphone.
* Le mot de passe est exigé pour tout appareil autre que l'ordinateur (10 essais maximum par quart d'heure, session de 30 jours). Les clés API et le réglage d'accès ne se modifient que depuis l'ordinateur.

## Mode avancé (terminal)

```bash
python3 -m venv .venv && source .venv/bin/activate && pip install -r requirements.txt
python lancer.py            # vos données réelles (dossier ./data)
python lancer.py --demo     # données fictives pour découvrir (dossier ./data-demo)
```

## Premier démarrage

1. **Paramètres > Société** : SIREN, capital, nom de l'associé unique tel qu'il apparaît sur vos virements (sert à reconnaître vos apports et remboursements).
2. **Paramètres > Fiscalité** : régime de TVA, périodicité, option débits, régime d'IS. Tant que ce n'est pas confirmé, un bandeau signale que des valeurs provisoires sont utilisées.
3. **Paramètres > Exercices** : dates de votre exercice (le premier peut être plus long que 12 mois selon les statuts).
4. **Paramètres > Qonto** : identifiant et clé API (Qonto > Paramètres > Intégrations et partenariats > Clé API), puis « Importer tout l'historique ».
5. **Paramètres > IA** (facultatif) : clé API Anthropic pour la lecture automatique des justificatifs et l'assistant.

## Fonctionnement au quotidien

| Moment | Action |
|---|---|
| Chaque jour ou semaine | Banque : les opérations Qonto arrivent seules. Acceptez les propositions ou catégorisez en un clic. |
| Quand vous recevez une facture | Dépenses : glissez le PDF ou la photo. Il est lu, catégorisé et rapproché de l'opération Qonto. |
| Quand vous facturez | Recettes : créez la facture. Elle passe « payée » quand le virement du client est rapproché. |
| Quand vous avancez de l'argent | Apports personnels : le virement Qonto depuis votre compte est reconnu comme apport en compte courant. |
| Fin de mois | Comptabilité > Vérifier le mois : seuls les problèmes s'affichent, puis « Clôturer le mois » et dossier mensuel. |
| Période de TVA | TVA > Préparer ma déclaration : contrôles, rubriques CA3 ou CA12, export PDF, Excel et justificatifs. |
| Fin d'exercice | Clôture > Préparer mon bilan annuel, puis Exporter mon dossier annuel (états, FEC, liasse, justificatifs). |

## Ce que fait le moteur comptable

* **Partie double** : chaque pièce génère ses écritures (journaux AC, VT, BQ, OD, AN), toujours équilibrées (contrôle à chaque écriture et contrôle global).
* **Montants en centimes entiers** : aucun calcul en virgule flottante, arrondi commercial au centime, HT + TVA = TTC garanti.
* **Écritures provisoires puis validées** : une écriture validée est numérotée et protégée par la base elle même (déclencheurs SQLite) ; toute correction passe par une contrepassation tracée dans l'historique.
* **TVA selon l'exigibilité** : prestations de services à l'encaissement (sauf option débits), biens à la facturation ; TVA déductible sur services au paiement, sur biens et immobilisations à la facture ; autoliquidation des fournisseurs étrangers (A3, B4) ; avoirs ; report de crédit ; TVA non récupérable (cadeaux > 73 € TTC, transport de personnes, factures non conformes).
* **Compte courant d'associé (455)** : apports, frais avancés personnellement, remboursements, dépenses personnelles payées par la société. Un remboursement ne recompte jamais la dépense. Capital social traité à part (1013, 109/1011).
* **Distinction des trois notions** : charge comptable, charge fiscalement déductible (pourcentage par dépense, réintégré dans l'IS), TVA récupérable (pourcentage par dépense).
* **Immobilisations** : amortissement linéaire prorata temporis, dotations à la clôture, sorties.
* **IS** : résultat fiscal, réintégrations, déficits, taux réduit 15 % jusqu'à 42 500 € (proratisé) si conditions remplies, 25 % au delà, acomptes.
* **Règles versionnées** : `app/rules.py` porte les taux et seuils avec leur date d'application ; un ancien exercice reste calculé avec les règles de son époque.
* **Distinction des données** : importées, proposées par l'IA (badge violet), validées, écritures définitives.

## Qonto

Synchronisation par l'**API officielle Qonto Business API v2** (`https://thirdparty.qonto.com`), en lecture seule, avec la clé API de l'entreprise :

* `GET /v2/organization` : compte et solde ;
* `GET /v2/transactions` : opérations réglées, incrémentales (`updated_at_from`), sans doublon (identifiant Qonto unique) ;
* `GET /v2/attachments/{id}` : justificatifs déposés dans Qonto, téléchargés et rangés localement.

Synchronisation automatique toutes les 30 minutes quand l'application est ouverte, et bouton manuel. Secours : import du CSV exporté depuis Qonto (doublons détectés entre CSV et API). Aucun scraping.

Le **serveur MCP officiel de Qonto** (`https://mcp.qonto.com/mcp`) permet de brancher Qonto à un assistant IA externe (Claude Desktop, Claude Code). Il n'est pas nécessaire ici : l'assistant intégré interroge directement la comptabilité locale, déjà synchronisée, et ne peut rien modifier. À noter : le MCP Qonto autorise certaines écritures (cartes, factures), à réserver à un usage conscient.

## Sécurité et sauvegardes

* Clés API dans `data/secrets.json` (droits 600), jamais dans le code, jamais dans la base, jamais dans les exports.
* Sauvegarde automatique quotidienne de la base (`data/backups`, 30 dernières).
* Export complet (base + justificatifs) et restauration dans Clôture et exports.
* Historique non modifiable de toutes les modifications (Comptabilité > Historique).
* Les pièces validées ne peuvent pas être supprimées. Durées de conservation : 10 ans pour les pièces et livres comptables, 6 ans pour les documents fiscaux.

Pensez à copier régulièrement l'export complet hors de l'ordinateur (disque externe, cloud personnel).

## Tests

```bash
python -m pytest
```

Les tests utilisent une base temporaire, jamais vos données. Ils couvrent : facture client avec TVA, facture fournisseur avec TVA, dépense sans TVA récupérable, cadeau au delà du seuil, note de frais payée personnellement puis remboursée, apport et remboursement en compte courant, capital, facture payée partiellement, avoir, vente de biens, autoliquidation, immobilisation amortissable, protection des écritures validées, import idempotent et doublons, CSV Qonto, arrondis, déclaration de TVA avec report de crédit, rapprochement bancaire, vérification et clôture du mois, clôture annuelle complète (bilan équilibré, IS, FEC, dossier ZIP, à nouveaux, affectation), assistant sans IA, Factur-X, sauvegarde et restauration.

## Points à vérifier par vous

* **Formulaires officiels** : les numéros de lignes CA3 (3310-CA3-SD) et la présentation CA12 et 2033 sont repris des formulaires en vigueur ; vérifiez la notice de l'année avant report.
* **Télédéclarations** : l'application prépare les montants mais ne transmet rien et ne paie rien. La liasse fiscale (2065 et 2033) se télétransmet en EDI via un partenaire ou en mode EFI selon votre éligibilité.
* **Facturation électronique** : réception obligatoire depuis le 1er septembre 2026 via une plateforme agréée, émission obligatoire au 1er septembre 2027 pour les PME. Les factures Factur-X reçues sont déjà lues sans IA ; le branchement à votre plateforme agréée est prévu dans l'architecture (`app/ai.py`, `parse_cii`).
* En cas de doute sur un traitement fiscal, l'application le signale « à vérifier » : un avis d'expert comptable reste recommandé pour la première clôture.

Architecture détaillée : [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

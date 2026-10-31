# SapoCycle : carte des hôtels (Suisse, France, Allemagne × FR / DE / EN)

Module WordPress qui remplace la carte WP Store Locator de sapocycle.org.

* 3 pays gérés séparément : Suisse, France, Allemagne
* 1 fichier Excel par pays, importé depuis l'administration
* 9 shortcodes : 3 pays × 3 langues
* Le smiley vert du site comme marqueur, le ✳️ Premium devient une icône dédiée
* Barre de recherche (nom, adresse, code postal, ville, canton ou région) + filtre par canton ou région
* Fiche hôtel : nom, adresse, code postal, ville, canton ou région, site internet
* Menu **Carte des hôtels** dans l'administration : tableau, édition, import et export Excel par pays
* Aucune clé API : fond de carte et géolocalisation OpenStreetMap

## Fichiers

| Fichier | Rôle |
|---|---|
| `snippets/1-coeur.txt` à `snippets/7-admin-script.txt` | Les 7 snippets à coller dans Code Snippets |
| `sapocycle-hotels-map.php` | Le même module en un seul fichier (extension WordPress) |
| `sapocycle-hotels-map.zip` | L'extension installable, si l'hébergeur autorise l'envoi d'extensions |
| `hotels-sapocycle-suisse.xlsx` | Les 95 hôtels suisses actuels du site, nettoyés, au format colonnes E, I, J, K, L, M |
| `outils/split_snippets.py` | Régénère les 7 snippets à partir de `sapocycle-hotels-map.php` |
| `apercus/` | Captures de la carte et de l'administration |

## Installation avec Code Snippets (7 snippets)

Le module est découpé en 7 snippets plus petits, plus faciles à copier et à enregistrer. Pour **chacun** des 7 fichiers du dossier `snippets/` :

1. Snippets › Ajouter.
2. Type **PHP**.
3. Titre : reprendre le nom du fichier (par exemple « Carte hôtels 1 : cœur »).
4. Coller tout le contenu du fichier (télécharger le fichier puis Ctrl+A / Ctrl+C, ne pas copier depuis un aperçu).
5. **Exécuter le snippet partout**, puis Enregistrer et activer.

L'ordre n'a pas d'importance. Supprimer d'abord les anciens snippets « Carte des hôtels ».

Contrôle : le menu **Carte des hôtels** apparaît dès que le snippet 1 est actif. Tant qu'un snippet manque, une bannière rouge et la page du menu indiquent lequel (« Manquant »). Quand les 7 sont chargés, l'administration complète s'affiche.

Pour une installation en extension (si l'hébergeur l'autorise) : Extensions › Ajouter › Téléverser › `sapocycle-hotels-map.zip`. Ne pas utiliser les deux méthodes en même temps.

## Les 9 shortcodes

| Pays | Page FR | Page DE | Page EN |
|---|---|---|---|
| Suisse | `[sapocycle_suisse_fr]` | `[sapocycle_suisse_de]` | `[sapocycle_suisse_en]` |
| France | `[sapocycle_france_fr]` | `[sapocycle_france_de]` | `[sapocycle_france_en]` |
| Allemagne | `[sapocycle_allemagne_fr]` | `[sapocycle_allemagne_de]` | `[sapocycle_allemagne_en]` |

Chaque shortcode affiche uniquement les hôtels de son pays, avec les textes dans sa langue (recherche, filtres, légende, bouton « Site internet »). Il se colle dans un module Texte ou Code de Divi. Options : `height="620"`, `search="no"`, `legend="no"`. Ils sont aussi copiables en un clic dans l'onglet **Intégration et réglages**.

## Importer les fichiers Excel

Menu **Carte des hôtels** › onglet **Import Excel** :

1. Choisir le pays en haut (Suisse, France ou Allemagne).
2. Déposer le fichier Excel de ce pays.
3. Importer. Les 3 cartes du pays (FR, DE, EN) sont à jour immédiatement.

Importer un pays ne touche jamais les hôtels des deux autres pays.

Mode **Remplacer** (par défaut) : le fichier devient la liste complète des hôtels de ce pays.
Mode **Fusionner** : met à jour les hôtels existants (même nom + même code postal) et ajoute les nouveaux.
**Annuler le dernier import** restaure la liste d'avant le dernier import.
**Exporter les hôtels en…** télécharge la liste actuelle du pays au même format, prête à être modifiée puis réimportée.

### Colonnes lues

Par défaut, pour chacun des 3 fichiers :

| Colonne | Contenu |
|---|---|
| **E** | Nom de l'hôtel (obligatoire). Un nom qui commence par ✳️ = hôtel Premium donateur |
| **I** | Adresse |
| **J** | Code postal (NPA, PLZ) |
| **K** | Ville |
| **L** | Canton (Suisse : ZH, VD… ou nom complet), région (France), Land (Allemagne) |
| **M** | Site internet |

Les autres colonnes sont ignorées. Les lettres se règlent **pays par pays** dans l'onglet Import si un fichier est différent. La ligne 1 est considérée comme une ligne de titres et ignorée (case à cocher, réglable par pays).

Coordonnées GPS : celles d'un hôtel déjà connu (même nom, code postal et adresse) sont conservées. Pour les autres, le module reprend d'abord les coordonnées de l'ancienne carte WP Store Locator si elle est encore installée (instantané), sinon il géolocalise l'adresse via OpenStreetMap (environ 1 seconde par hôtel). **Gardez WP Store Locator actif jusqu'au premier import.** Une adresse introuvable se place à la main dans la fiche de l'hôtel en faisant glisser le smiley.

La case « Visible sur la carte » réglée dans l'administration est conservée d'un import à l'autre.

Formats : `.xlsx` ou `.csv` (le vieux `.xls` doit être réenregistré en `.xlsx`).

## Points à vérifier dans les données actuelles

Le fichier `hotels-sapocycle-suisse.xlsx` reprend les données du site avec ces corrections :

* « (ZH) », « (VD) »… retirés des noms et placés dans la colonne Canton
* Canton « TH » corrigé en « TG » (Thurgovie) pour 3 hôtels
* Canton ajouté pour Arosa Kulm (GR), Hotel Eiger Mürren (BE), Hotel Pestalozzi Lugano (TI)
* Doublon « Bellevue Palace, Bern » supprimé
* Lien cassé du Schweizerhof Zürich corrigé (`hotelCHerhof.com` › `hotelschweizerhof.com`)
* Paramètre publicitaire `gclid` retiré du lien Gstaad Palace

Restent à compléter (filtre « Infos incomplètes » dans l'administration) :

* 26 hôtels sans NPA
* 2 hôtels sans site internet : Airport Hotel Basel, La Réserve Genève

## Bon à savoir

* **Droits** : le menu est accessible aux éditeurs et administrateurs (capacité `edit_pages`).
* **Cookies** : la carte charge les tuiles OpenStreetMap et Leaflet depuis cdnjs. Si Complianz bloquait la carte Google, vérifiez qu'il ne bloque pas `tile.openstreetmap.org`, ou déclarez le service OpenStreetMap.
* **Fond de carte** : « OpenStreetMap adouci » par défaut. Un fournisseur avec clé (MapTiler, Stadia, CARTO) peut être renseigné dans Réglages.
* **Ancienne carte** : WP Store Locator peut être désactivé une fois le premier import terminé et la nouvelle carte en ligne.
* Les données sont stockées dans l'option WordPress `sapo_hotels_data`.

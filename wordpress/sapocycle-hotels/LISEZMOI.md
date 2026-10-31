# SapoCycle : carte des hôtels (FR / DE / EN)

Module WordPress qui remplace la carte WP Store Locator de la page Hôtels de sapocycle.org.

* Une carte par pays (onglets automatiques dès qu'un 2e pays apparaît dans la liste)
* Le smiley vert du site comme marqueur (plus de pin rouge), le ✳️ Premium devient une icône dédiée
* Barre de recherche (nom, adresse, NPA, ville, canton) + filtre par canton
* Fiche hôtel : nom, adresse, NPA, ville, canton, site internet
* Traduction automatique FR / DE / EN via WPML
* Menu **Carte des hôtels** dans l'administration : tableau, édition, import et export Excel
* Aucune clé API : fond de carte OpenStreetMap, géolocalisation OpenStreetMap (Nominatim)

## Fichiers

| Fichier | Rôle |
|---|---|
| `sapocycle-hotels-map.php` | Le module complet (un seul fichier) |
| `code-snippet-sans-balise-php.txt` | Le même code sans la ligne `<?php`, prêt à coller dans Code Snippets |
| `sapocycle-hotels-map.zip` | Le même code en extension WordPress installable |
| `hotels-sapocycle-import.xlsx` | Les 95 hôtels actuels du site, nettoyés, au format colonnes E, I, J, K, L, M |
| `apercus/` | Captures de la carte et de l'administration |

## Installation

### Option A : Code Snippets (demandé)

1. Extensions › Code Snippets › Ajouter.
2. Type **PHP**, titre « Carte des hôtels SapoCycle ».
3. Coller le contenu de `code-snippet-sans-balise-php.txt`.
4. Choisir **Exécuter le snippet partout**, puis Enregistrer et activer.

### Option B : extension classique (plus robuste)

Extensions › Ajouter › Téléverser une extension › `sapocycle-hotels-map.zip` › Activer.

N'utilisez qu'une seule des deux options à la fois.

## Mise en route

1. Menu **Carte des hôtels** › onglet **Import Excel** › déposer `hotels-sapocycle-import.xlsx` › Importer.
2. Dans la page Hôtels de chaque langue (FR, DE, EN), remplacer le module WP Store Locator par un module Texte ou Code Divi contenant :

   ```
   [sapocycle_hotels]
   ```

   Pour une seule carte de pays : `[sapocycle_hotels country="CH"]`.
   Options : `height="620"`, `lang="fr"`, `search="no"`, `legend="no"`.
3. Vider le cache (Divi, extension de cache, CDN) si la page ne change pas.

## Mettre la carte à jour

1. **Exporter en Excel** (bouton en haut à droite) pour récupérer la liste actuelle.
2. Modifier le fichier dans Excel.
3. **Import Excel** › déposer le fichier › Importer. La carte des trois langues est à jour immédiatement.

Mode **Remplacer** (par défaut) : le fichier devient la liste complète.
Mode **Fusionner** : met à jour les hôtels existants (même nom + même NPA) et ajoute les nouveaux.
Le bouton **Annuler le dernier import** restaure la liste précédente.

Les hôtels sans latitude/longitude sont géolocalisés automatiquement à partir de l'adresse (environ 1 seconde par hôtel). Une adresse introuvable se place à la main dans la fiche de l'hôtel en faisant glisser le smiley.

### Colonnes lues

L'import lit uniquement ces colonnes du fichier Excel (première feuille) :

| Colonne | Contenu |
|---|---|
| **E** | Nom de l'hôtel (obligatoire). Un nom qui commence par ✳️ = hôtel Premium donateur |
| **I** | Adresse |
| **J** | NPA |
| **K** | Ville |
| **L** | Canton (ZH, VD… ou nom complet) |
| **M** | Site internet |

Toutes les autres colonnes sont ignorées. Les lettres se changent dans l'onglet Import Excel si le fichier évolue. Par défaut la ligne 1 est considérée comme une ligne de titres et ignorée (case à cocher).

Coordonnées GPS : celles d'un hôtel déjà connu (même nom, NPA et adresse) sont conservées. Pour les autres, le module reprend d'abord les coordonnées de l'ancienne carte WP Store Locator si elle est encore installée (instantané), sinon il géolocalise l'adresse via OpenStreetMap (environ 1 seconde par hôtel). **Gardez donc WP Store Locator actif jusqu'au premier import.**

La case « Visible sur la carte » réglée dans l'administration est conservée d'un import à l'autre.

Formats : `.xlsx` ou `.csv` (le vieux `.xls` doit être réenregistré en `.xlsx`).

## Points à vérifier dans les données actuelles

Le fichier `hotels-sapocycle-import.xlsx` reprend les données du site avec ces corrections :

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

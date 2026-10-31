# SapoCycle : carte des hôtels (version 2, snippet unique)

Carte des hôtels membres SapoCycle pour la Suisse, la France et l'Allemagne, en français, allemand et anglais, avec un import Excel par pays. Le module tient dans **un seul snippet Code Snippets**.

Testé dans un vrai WordPress (7.1, PHP 8.3) avec Code Snippets 4.0 : validation et activation du snippet, import des 3 pays, localisation des adresses, modification d'un hôtel, export Excel, affichage des cartes publiques. Le site SapoCycle tourne en PHP 8.1 : je n'ai pas testé cette version, mais le code n'utilise rien qui demande plus récent.

## Fichiers

| Fichier | Rôle |
|---|---|
| `carte-hotels-snippet.txt` | **Le code à coller dans Code Snippets** |
| `sapocycle-carte-hotels.php` | Le même code en extension WordPress (si l'hébergeur l'autorise) |
| `hotels-sapocycle-suisse.xlsx` | Les 95 hôtels suisses actuels, au format colonnes E, I, J, K, L, M |
| `apercus/` | Captures de l'administration et des cartes |
| `tests/` | Scripts et fichiers utilisés pour les tests |

## Installation

1. Dans **Snippets › Tous les snippets**, supprimer tous les anciens snippets « Carte des hôtels » (y compris les 7 snippets et le snippet de test).
2. **Snippets › Ajouter**, type **PHP**, titre « Carte des hôtels SapoCycle ».
3. Télécharger `carte-hotels-snippet.txt`, l'ouvrir dans le Bloc-notes ou TextEdit, tout sélectionner (Ctrl+A), copier, coller dans le snippet. Le code fait 1 640 lignes. La première ligne est `/**`, la dernière est `}`, juste après la ligne `SapoCycle_Hotels::boot();`.
4. **Exécuter le snippet partout**, puis **Enregistrer et activer**.

Le menu **Carte des hôtels** apparaît à gauche, sous Commentaires.

## Les 9 shortcodes

| Pays | Page FR | Page DE | Page EN |
|---|---|---|---|
| Suisse | `[sapocycle_suisse_fr]` | `[sapocycle_suisse_de]` | `[sapocycle_suisse_en]` |
| France | `[sapocycle_france_fr]` | `[sapocycle_france_de]` | `[sapocycle_france_en]` |
| Allemagne | `[sapocycle_allemagne_fr]` | `[sapocycle_allemagne_de]` | `[sapocycle_allemagne_en]` |

À coller dans un module Texte ou Code de Divi. Option : `height="700"` pour la hauteur de la carte.

## Mettre à jour les hôtels

Menu **Carte des hôtels › Import Excel** :

1. Choisir le pays (Suisse, France, Allemagne).
2. Déposer le fichier Excel de ce pays et cliquer sur **Importer le fichier**.
3. Les 3 cartes du pays (FR, DE, EN) sont à jour immédiatement. Les autres pays ne sont pas touchés.

Colonnes lues (réglables par pays) : **E** nom, **I** adresse, **J** code postal, **K** ville, **L** canton ou région, **M** site internet. Les autres colonnes sont ignorées. Un nom qui commence par ✳️ = hôtel Premium donateur.

* **Remplacer** (par défaut) : le fichier devient la liste complète du pays.
* **Fusionner** : met à jour les hôtels existants et ajoute les nouveaux.
* **Annuler le dernier import** : revient à la liste d'avant.
* **Exporter** : télécharge la liste du pays au même format.

Les positions GPS sont reprises de l'ancienne carte WP Store Locator si elle est encore installée (instantané), sinon les adresses sont localisées via OpenStreetMap (environ 1 seconde par hôtel). Garder WP Store Locator actif jusqu'au premier import.

Onglet **Hôtels** : recherche, filtre par pays, modification d'un hôtel (adresse, site, Premium, visibilité, position du smiley sur une mini carte), suppression, ajout.

## Pourquoi les versions précédentes ne marchaient pas

* **7 snippets** : le validateur de Code Snippets ne reconnaît pas les « traits » PHP. Il prenait les méthodes pour des fonctions globales et refusait le snippet 2 (« Cannot redeclare function get_settings »).
* **Snippet unique précédent** : il passe le validateur. L'échec venait très probablement d'un collage incomplet.
* **Export Excel** : le lien de téléchargement était mal encodé et WordPress le refusait. Corrigé.

La version 2 est écrite pour ce validateur : une seule classe protégée par `class_exists`, sans trait, et elle a été enregistrée et activée avec le vrai Code Snippets avant livraison.

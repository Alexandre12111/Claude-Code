# BQP Bibliothèque

Bibliothèque numérique de la Bourse Jean-Michel Quatrepoint.

**Étape 1 livrée : le back-office.** Type de contenu Document, neuf taxonomies pré-remplies d'après l'arborescence validée, fiches Personnes et Organisations, fiche document complète. L'étape 2 (les 5 blocs, le catalogue filtré, la fiche publique) s'appuiera sur ces données sans rien changer à la saisie.

Indépendant des plugins Partenaires, Gouvernance et Formulaires : préfixe `bqb_`, aucun conflit.

## Installation

### Avec Code Snippets

1. Ouvrir `bqp-bibliotheque-code-snippets.txt`
2. Copier tout le contenu dans un nouveau snippet
3. Régler sur **Run everywhere**, enregistrer et activer
4. Ouvrir n'importe quelle page de l'administration : l'arborescence se crée

### En extension classique

Compresser le dossier en `.zip`, puis Extensions → Ajouter → Téléverser.

## Le principe : un modèle à facettes

Chaque document reçoit plusieurs étiquettes indépendantes. C'est leur combinaison qui le fait apparaître au bon endroit. Un même type (« Article », « Entretien ») revient dans plusieurs espaces de l'arborescence : il n'est saisi qu'une fois, dans la taxonomie Nature, et croisé avec la Collection.

| Taxonomie | Forme | Page publique | Rôle |
|---|---|---|---|
| Collections | arborescence | oui, `/collection/…` | d'où vient le document, espaces 1 à 4 |
| Natures | cases | non | ce qu'est le document |
| Thèmes | arborescence | oui, `/theme/…` | de quoi il parle, espace 5 |
| Secteurs | cases | non | secteurs stratégiques |
| Pays et territoires | cases | non | zones géographiques |
| Prix et bourses | cases | non | programme de la Bourse |
| Personnes | saisie + fiche | oui, `/personne/…` | auteurs, lauréats, experts |
| Organisations | saisie + fiche | oui, `/organisation/…` | partenaires, éditeurs, institutions |
| Mots-clés | saisie libre | non | recherche |

Les taxonomies sans page publique servent de filtres sans créer d'archives pauvres dans l'index Google.

## Ce qui est créé automatiquement

- **17 collections** : 4 espaces avec ordre et couleur, 13 sous-collections
- **21 natures**
- **34 thèmes** : Souveraineté, Industrie, Puissance et relations internationales, État et société, et leurs sous-thèmes
- **12 secteurs**, **5 pays et territoires**, **3 prix**
- la personne **Jean-Michel Quatrepoint**, rôle Auteur

Les doublons du schéma d'origine sont résolus : les secteurs stratégiques ne sont saisis que dans Secteurs, et Europe, États-Unis, Chine, Russie que dans Pays et territoires.

## La fiche d'un document

| Champ | Stockage | Note |
|---|---|---|
| Titre | natif | |
| Résumé | extrait natif | placé sous le titre, repris par Rank Math pour la meta description |
| Contexte et notice éditoriale | contenu natif | éditeur classique |
| Couverture ou visuel | image mise en avant | |
| Date | `_bqb_annee`, `_bqb_mois`, `_bqb_jour` | l'année seule suffit ; `_bqb_date_tri` est calculée pour le tri |
| Niveau d'accès | `_bqb_acces` | public, adhérents, partenaires, sur place |
| Édition du prix | `_bqb_edition` | travaux primés |
| Fichier | `_bqb_fichier` | pièce de la médiathèque |
| Lien externe | `_bqb_lien` | source, éditeur, vidéo |
| Référence bibliographique | `_bqb_reference` | |
| Mention de droits | `_bqb_droits` | |
| Documents associés | `_bqb_associes` | recherche par titre, sans recharger la page |

## Fiches des taxonomies

- **Collections** : ordre d'affichage, couleur du bloc
- **Thèmes** : ordre d'affichage
- **Personnes** : rôles (Auteur, Lauréat, Expert, Représentant d'un partenaire), photo, biographie, année du prix, prix obtenu, page personnelle
- **Organisations** : type, logo, site internet

## Dans la liste des documents

Colonnes couverture, collection, nature, année (triable), accès, résumé renseigné ou manquant, fichier ou lien. Filtres par collection, nature et thème.

## Points d'attention

- **Documents réservés** : un fichier de la médiathèque reste accessible par son adresse. Ne pas téléverser le fichier d'un document réservé avant l'étape qui protège les fichiers. Un avertissement s'affiche dans la fiche dès qu'un niveau d'accès autre que public est choisi.
- **Adresses des documents** : `/bibliotheque/titre-du-document/`. Ne pas créer de page enfant sous une page « Bibliothèque ». Si une fiche renvoie une erreur 404, Réglages › Permaliens › Enregistrer.
- **Code Snippets** n'a pas de hook d'activation : les règles d'adresses sont enregistrées une fois au premier chargement, puis à chaque changement de version.

## Testé

Dans un WordPress 6.8 réel, avec les quatre plugins BQP actifs ensemble : création de l'arborescence, saisie de documents par l'interface, enregistrement de toutes les taxonomies et de tous les champs, recherche des documents associés, fiche Personne, filtres et tri de la liste, page publique en HTTP 200, stabilité du résumé sur plusieurs enregistrements successifs.

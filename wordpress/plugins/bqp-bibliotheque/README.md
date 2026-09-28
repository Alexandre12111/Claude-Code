# BQP Bibliothèque

Bibliothèque numérique de la Bourse Jean-Michel Quatrepoint. Version 2.0.0.

Type de contenu Document, neuf familles de classement pré-remplies, gestion visuelle de l'arborescence, sélection simple des catégories dans chaque document, générateur de shortcodes, pages publiques (5 blocs, catalogue filtré, fiches, pages automatiques).

Indépendant des plugins Partenaires, Gouvernance et Formulaires : préfixe `bqb_`, aucun conflit.

## Installation

### Avec Code Snippets

1. Ouvrir `bqp-bibliotheque-code-snippets.txt`
2. Copier tout le contenu dans le snippet existant (il remplace la v1) ou dans un nouveau snippet
3. Régler sur **Run everywhere**, enregistrer et activer
4. Ouvrir n'importe quelle page de l'administration : l'arborescence et les réglages par défaut se créent
5. Si une adresse renvoie une erreur 404 : Réglages › Permaliens › Enregistrer

### En extension classique

Compresser le dossier en `.zip`, puis Extensions → Ajouter → Téléverser.

### Pages à créer

| Page | Contenu |
|---|---|
| Bibliothèque | `[bqp_bibliotheque]` |
| Recherche avancée | `[bqp_documents filtres="oui"]` |
| Auteurs et personnes | `[bqp_personnes famille="personnes"]` |
| Organisations | `[bqp_personnes famille="organisations"]` |

Puis Bibliothèque › Réglages : choisir ces pages dans les listes.

## Le principe : un modèle à facettes

Chaque document reçoit plusieurs étiquettes indépendantes. C'est leur combinaison qui le fait apparaître au bon endroit. Un même type (« Article », « Entretien ») revient dans plusieurs espaces : il n'est saisi qu'une fois, dans Nature, et croisé avec la Collection.

| Famille | Forme | Page publique | Rôle |
|---|---|---|---|
| Collections | arborescence | oui, `/collection/…` | d'où vient le document, blocs 1 à 4 |
| Natures | pastilles | non | ce qu'est le document |
| Thèmes | arborescence | oui, `/theme/…` | de quoi il parle, bloc 5 |
| Secteurs | pastilles | non | secteurs stratégiques |
| Pays et territoires | pastilles | non | zones géographiques |
| Prix et bourses | pastilles | non | programme de la Bourse |
| Personnes | recherche + fiche | oui, `/personne/…` | auteurs, lauréats, experts |
| Organisations | recherche + fiche | oui, `/organisation/…` | partenaires, éditeurs, institutions |
| Mots-clés | saisie libre | non | recherche |

Les neuf familles sont fixes dans le code. Les catégories à l'intérieur de chaque famille se créent, se renomment, se déplacent et se suppriment depuis l'administration.

## L'administration

Tous les écrans Bibliothèque reprennent la charte du site : bordeaux, marine, Cormorant Garamond pour les titres, Inter pour le texte. Le reste de l'administration WordPress n'est pas modifié.

### Arborescence

Un onglet par famille. Pour chaque catégorie :

- **Renommer** en cliquant sur le nom (l'adresse et les shortcodes existants restent valides, le slug ne change pas)
- **Ajouter** une catégorie racine ou une sous-catégorie
- **Déplacer** par glisser-déposer, l'ordre est enregistré immédiatement
- **Supprimer** avec confirmation (les documents ne sont pas supprimés, seulement détachés)
- **Panneau de détail** : description, couleur du bloc, ordre, page dédiée, vues proposées (par année, par lauréat, par thème…) avec libellés personnalisables
- **Copier le shortcode** de la catégorie en un clic
- Compteur de documents publiés, sous-catégories comprises
- Recherche instantanée dans l'arborescence

Les écrans natifs de WordPress pour ces familles sont masqués du menu : tout se fait depuis Arborescence.

### Classement (dans chaque document)

Un seul panneau remplace les neuf boîtes natives de WordPress :

- Collections et Thèmes : arbre dépliable avec cases à cocher et recherche
- Natures, Secteurs, Pays, Prix : pastilles cliquables
- Personnes, Organisations, Mots-clés : recherche avec suggestions, et création d'une nouvelle entrée sans quitter le document

### Générateur de shortcodes

Choisir une ou plusieurs catégories dans chaque famille, une année, un niveau d'accès, un regroupement, un tri, un nombre, les filtres visibles ou non. Le shortcode se construit en direct avec le nombre de documents qui correspondent aujourd'hui. Un bouton le copie.

Pour mettre à jour une page : remplacer le shortcode dans le widget Elementor, c'est tout.

### Réglages

- Afficher ou masquer les liens vides dans les 5 blocs (masqués par défaut)
- Nombre de documents par page
- Pages Bibliothèque, Recherche avancée, Personnes, Organisations, Contact
- Titre, texte et couleur du 5e bloc
- Fiches automatiques et pages automatiques des catégories, activables séparément

## Les shortcodes

### `[bqp_documents]`

Liste de documents. Plusieurs valeurs dans un même attribut : l'une OU l'autre. Plusieurs attributs : l'un ET l'autre.

| Attribut | Valeurs |
|---|---|
| `collection`, `nature`, `theme`, `secteur`, `pays`, `prix`, `personne`, `organisation`, `motcle` | slugs séparés par des virgules |
| `annee` | `2024` ou `2015-2020` |
| `acces` | `public`, `adherents`, `partenaires`, `sur-place` |
| `groupe` | `annee`, `personne`, `theme`, `organisation`, `nature`, `collection` |
| `tri` | `recent` (défaut), `ancien`, `titre` |
| `nombre` | nombre de documents par page |
| `filtres` | `oui` pour afficher la barre de filtres |
| `titre` | titre au-dessus de la liste |
| `vide` | message si aucun document |
| `pagination` | `non` pour la masquer |

Exemples :

```
[bqp_documents collection="travaux-primes" groupe="annee"]
[bqp_documents collection="ecrits-de-jean-michel-quatrepoint" theme="industrie" filtres="oui"]
[bqp_documents nature="entretien,conference-ou-intervention" annee="2020-2025" nombre="6" pagination="non"]
```

### `[bqp_bibliotheque]`

Les 5 blocs : 4 espaces de collections et le bloc thèmes en pleine largeur. Attributs `vides="oui|non"` (prioritaire sur le réglage) et `navigation="non"`.

### `[bqp_personnes]`

Annuaire. `famille="personnes|organisations"`, `role="laureat|auteur|expert|partenaire"`, `type="…"` pour les organisations.

### `[bqp_document_fiche]`

Fiche d'un document dans une mise en page Elementor. `id="123"` facultatif.

## Paramètres d'adresse

La barre de filtres utilise des paramètres préfixés pour ne pas entrer en conflit avec WordPress : `f_q`, `f_collection`, `f_nature`, `f_theme`, `f_secteur`, `f_pays`, `f_prix`, `f_personne`, `f_organisation`, `f_motcle`, `f_annee`, `f_tri`, `f_groupe`, et `pg` pour la page. Seule la première liste d'une page écoute l'adresse.

## Pages publiques automatiques

- **Fiche document** `/bibliotheque/titre/` : fil d'Ariane, badge de collection, résumé, boutons Télécharger, Regarder, Consulter ou Demander l'accès selon le niveau, bouton Citer, vidéo intégrée, informations, documents associés dans les deux sens
- **Collections, Thèmes, Personnes, Organisations** : page générée automatiquement, ou redirection 301 vers la page dédiée si elle est renseignée dans l'Arborescence

## Ce qui est créé automatiquement

- **17 collections** : 4 espaces avec ordre et couleur, 13 sous-collections, avec leurs natures et vues
- **21 natures**, **34 thèmes**, **12 secteurs**, **5 pays et territoires**, **3 prix**
- la personne **Jean-Michel Quatrepoint**, rôle Auteur

## Points d'attention

- **Documents réservés** : un fichier de la médiathèque reste accessible par son adresse. Ne pas téléverser le fichier d'un document réservé avant la protection des fichiers. Un avertissement s'affiche dans la fiche dès qu'un niveau autre que public est choisi.
- **Adresses** : ne pas créer de page enfant sous une page dont le slug est `bibliotheque`.
- **Cache** : les compteurs sont mis en cache et vidés à chaque enregistrement de document ou de catégorie.
- **Code Snippets** n'a pas de hook d'activation : les règles d'adresses sont enregistrées au premier chargement, puis à chaque changement de version.

## Testé

Dans un WordPress 6.8 réel avec le thème Hello Elementor et les quatre plugins BQP actifs :

- Arborescence : renommage (slug conservé), ajout racine et enfant, panneau de détail, glisser-déposer, suppression, recherche, copie du shortcode, vérifiés en base
- Classement : thèmes, personnes, organisation et pays créés depuis le document, vérifiés en base
- Générateur : shortcode et compteur en direct
- Réglages : affichage des liens vides activé puis visible sur le site
- Front : toutes les pages en HTTP 200, aucune erreur PHP ni JavaScript, liens du plugin en bordeaux malgré le rose du thème

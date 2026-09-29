# BQP Bibliothèque

Bibliothèque numérique de la Bourse Jean-Michel Quatrepoint. Version 2.1.0.

Type de contenu Document, neuf familles de classement pré-remplies, gestion visuelle de l'arborescence, sélection simple des catégories dans chaque document, générateur de shortcodes, et **une seule page Bibliothèque** qui réunit les 5 blocs, le catalogue filtré et l'annuaire des auteurs et organisations.

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

### Une seule page à créer

Créer une page « Bibliothèque » avec le shortcode `[bqp_bibliotheque]`. C'est tout : la page est détectée automatiquement (contenu ou widget Elementor), aucun réglage n'est nécessaire.

Elle contient, de haut en bas :

1. la recherche et les raccourcis (Tous les documents, Chronologie, Auteurs et personnes, Organisations et sources)
2. les 5 blocs
3. le catalogue (`#bqb-catalogue`) avec ses filtres, ou l'annuaire

Chaque lien des blocs, chaque filtre, chaque fiche de l'annuaire et chaque page de résultats met à jour le catalogue **sans recharger la page**. L'adresse change quand même (bouton retour, partage et favoris fonctionnent) ; sans JavaScript, les mêmes liens rechargent la page et descendent au catalogue.

Quand un sujet est choisi (collection, thème, personne, organisation), un en-tête le présente au-dessus des résultats : description, photo et biographie, logo, sous-catégories.

Mise à jour depuis la 2.0 : les pages Recherche avancée, Auteurs et Organisations peuvent être supprimées. Les anciennes adresses `/collection/…`, `/theme/…`, `/personne/…`, `/organisation/…` redirigent en 301 vers la page Bibliothèque filtrée et sortent des sitemaps (WordPress et Rank Math).

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
- Page Bibliothèque (détection automatique, ou choix manuel) et page Contact
- Titre, texte et couleur du 5e bloc
- Fiche automatique des documents
- Pages séparées pour les collections, thèmes, personnes et organisations : désactivées par défaut

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

La page Bibliothèque complète : recherche, 5 blocs, catalogue et annuaire.

| Attribut | Valeurs |
|---|---|
| `vides` | `oui` ou `non`, prioritaire sur le réglage |
| `navigation` | `non` pour masquer la recherche et les raccourcis |
| `catalogue` | `non` pour n'afficher que les blocs (par exemple sur l'accueil) ; leurs liens mènent alors à la page Bibliothèque |
| `titre_catalogue` | titre du catalogue, « Tous les documents » par défaut |

### `[bqp_personnes]`

Annuaire autonome, pour une autre page si besoin (la page Bibliothèque intègre déjà le sien). `famille="personnes|organisations"`, `role="laureat|auteur|expert|partenaire"`, `type="…"` pour les organisations.

### `[bqp_document_fiche]`

Fiche d'un document dans une mise en page Elementor. `id="123"` facultatif.

## Paramètres d'adresse

La barre de filtres utilise des paramètres préfixés pour ne pas entrer en conflit avec WordPress : `f_q`, `f_collection`, `f_nature`, `f_theme`, `f_secteur`, `f_pays`, `f_prix`, `f_personne`, `f_organisation`, `f_motcle`, `f_annee`, `f_tri`, `f_groupe`, `pg` pour la page, et pour l'annuaire `f_annuaire` (`personnes` ou `organisations`) et `f_role` (rôle ou type). Seule la première liste d'une page écoute l'adresse.

Les vues filtrées gardent l'adresse canonique de la page Bibliothèque : Google n'indexe qu'une page, pas une par combinaison de filtres.

## Pages publiques

- **Page Bibliothèque** : la seule page à créer
- **Fiche document** `/bibliotheque/titre/` : générée pour chaque document. Fil d'Ariane vers la page Bibliothèque filtrée, badge de collection, résumé, boutons Télécharger, Regarder, Consulter ou Demander l'accès selon le niveau, bouton Citer, vidéo intégrée, informations, documents associés dans les deux sens
- **Page dédiée** (facultatif) : une catégorie peut pointer vers une page Elementor choisie dans l'Arborescence ; ses liens et son ancienne adresse y mènent alors

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
- Réglages : affichage des liens vides activé puis visible sur le site ; détection automatique de la page Bibliothèque
- Page unique (2.1) : lien de bloc, filtre par liste, retrait d'une pastille, annuaire, lauréats, fiche personne, recherche, bouton retour, tous sans rechargement ; redirections 301 des anciennes adresses, filtres conservés ; exclusion des sitemaps ; filtres repliables sur mobile, sans débordement horizontal
- Front : toutes les pages en HTTP 200, aucune erreur PHP ni JavaScript, liens du plugin en bordeaux malgré le rose du thème

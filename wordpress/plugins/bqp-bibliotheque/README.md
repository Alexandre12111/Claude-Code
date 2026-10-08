# BQP Bibliothèque

Bibliothèque numérique de la Bourse Jean-Michel Quatrepoint. Version 3.6.0.

Type de contenu Document, classement qui reprend **à l'identique l'arborescence du client** (6 familles au lieu de 9), gestion visuelle de l'arborescence, sélection simple des catégories dans chaque document, générateur de shortcodes, et **une seule page Bibliothèque** qui réunit une barre de recherche simple ou avancée, les 5 espaces à déplier, le catalogue filtré et l'annuaire des auteurs et organisations.

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

1. **une seule barre de recherche**, avec un sélecteur « Recherche simple / Recherche avancée », et les accès rapides (Chronologie, Auteurs et personnes, Lauréats, Organisations et sources)
2. **les 5 espaces** (« Parcourir la bibliothèque »), à déplier
3. le catalogue (`#bqb-catalogue`) avec le nombre de résultats, le tri et les filtres actifs, ou l'annuaire

### La recherche : simple ou avancée

- **Simple** : un champ libre (titre, auteur, mot-clé). Les critères avancés ne sont pas envoyés.
- **Avancée** : le même champ, plus Collection / origine, Nature, Thème, Période (de… à…), Secteur, Pays / territoire, Auteur, Organisation. Un compteur sur le bouton indique le nombre de critères remplis ; « Afficher les résultats » lance la recherche, « Effacer les critères » repart de zéro.
- Le mode avancé s'ouvre de lui-même quand un critère est déjà actif (lien partagé, clic dans un espace) : la barre montre toujours ce qui filtre la liste.
- `[bqp_bibliotheque recherche="avancee"]` ouvre le mode avancé d'office.

### Les espaces : un clic, puis un autre

Les 5 grands espaces s'affichent en cartes, dans le même design que les catégories de partenaires et les instances de la gouvernance : bandeau teinté de la couleur de l'espace avec son numéro (« 01 ») sur fond plein et le nombre de documents en grand, puis le nom, la description, le nombre de rubriques, et en pied « Découvrir » (qui devient « Replier » en couleur quand l'espace est ouvert). Sur tablette, 2 ou 3 cartes par ligne ; sur mobile, des lignes compactes (numéro, nom, rubriques, documents). On descend dans l'arborescence au clic, sans rien charger :

1. **un espace** ouvre son panneau sous la rangée de cartes : description, bouton « Voir les N documents », et ses rubriques numérotées (1.1, 1.2…) ;
2. **une rubrique** se déplie : « Tous les documents », puis ses sous-catégories en pastilles et ses regroupements (« Par lauréat », « Par année ») ; une rubrique sans sous-catégorie mène directement à ses documents ;
3. **une sous-catégorie** filtre le catalogue.

Un seul espace est ouvert à la fois ; la croix ou la touche Échap le referme. L'espace « Explorer par thème » propose aussi « Affiner par » secteur, pays, période, nature et collection, qui ouvrent la recherche avancée sur le bon champ. Quand l'adresse contient déjà un filtre, l'espace et la rubrique correspondants sont ouverts dès le chargement, et l'élément en cours est mis en évidence.

Chaque lien des espaces, chaque recherche, chaque fiche de l'annuaire, chaque tri et chaque page de résultats met à jour le catalogue **sans recharger la page**. L'adresse change quand même (bouton retour, partage et favoris fonctionnent) ; sans JavaScript, les mêmes liens rechargent la page et descendent au catalogue.

Quand un sujet est choisi (collection, thème, personne, organisation), un en-tête le présente au-dessus des résultats : description, photo et biographie, logo, sous-catégories.

Mise à jour depuis la 2.0 : les pages Recherche avancée, Auteurs et Organisations peuvent être supprimées. Les anciennes adresses `/collection/…`, `/theme/…`, `/personne/…`, `/organisation/…` redirigent en 301 vers la page Bibliothèque filtrée et sortent des sitemaps (WordPress et Rank Math).

## Le principe : l'arborescence du client, à l'identique

Les blocs du site reproduisent le schéma « Arborescence de la bibliothèque numérique » : mêmes blocs, mêmes rubriques numérotées (1.1 à 4.6, 5.1 et 5.2), mêmes éléments, mêmes libellés.

| Famille | Contenu | Rôle |
|---|---|---|
| Collections | blocs 1 à 4 : espace › rubrique › élément | où se range le document |
| Thèmes | bloc 5 : les 5 thématiques principales et leurs sous-thèmes | de quoi il parle ; les secteurs stratégiques et les pays en font partie, comme dans le schéma |
| Natures | 22 natures | ce qu'est le document ; déduite automatiquement de la rubrique cochée |
| Personnes | fiche : rôles, photo, biographie, année et nom du prix | auteurs, lauréats, experts |
| Organisations | fiche : type, logo, site | partenaires, éditeurs, sources |
| Mots-clés | saisie libre | recherche |

Les familles Secteurs, Pays et Prix de la 2.x sont supprimées : les secteurs et les pays sont des sous-thèmes (« Secteurs stratégiques », « Puissance et relations internationales »), le prix d'un lauréat est un simple champ de sa fiche.

Dans les rubriques, deux sortes de liens, comme dans le schéma :

- **les éléments** (« Articles », « Chroniques », « Archives de presse »…) sont des catégories que l'on coche dans le document
- **les regroupements** (« Par lauréat », « Par année », « Par thématique », « Par partenaire »…) ne sont pas des catégories : ils affichent les documents de la rubrique regroupés

« Page dédiée : Jean-Michel Quatrepoint » (bloc 1) est tirée de sa fiche Personne : biographie, principaux travaux (par nature), bibliographie (par année), thématiques associées.

« 5.2 Autres filtres » ouvre directement le bon filtre du catalogue : secteur, pays, période, nature, collection.

## L'administration

Tous les écrans Bibliothèque reprennent la charte du site : bordeaux, marine, Cormorant Garamond pour les titres, Inter pour le texte. Le reste de l'administration WordPress n'est pas modifié.

### Arborescence

Un onglet par famille. Pour chaque catégorie :

- **Renommer** en cliquant sur le nom (l'adresse et les shortcodes existants restent valides, le slug ne change pas)
- **Ajouter** une catégorie racine ou une sous-catégorie
- **Déplacer** par glisser-déposer, l'ordre est enregistré immédiatement
- **Supprimer** avec confirmation (les documents ne sont pas supprimés, seulement détachés)
- **Numérotation du schéma** (1, 1.1…) affichée devant les blocs et les rubriques
- **Panneau de détail** : description, couleur du bloc, page dédiée ; pour une rubrique, la nature proposée et les liens de regroupement avec leurs libellés ; pour un sous-thème, son usage comme filtre Secteur ou Pays
- **Copier le shortcode** de la catégorie en un clic
- Compteur de documents publiés, sous-catégories comprises
- Recherche instantanée dans l'arborescence

Les écrans natifs de WordPress pour ces familles sont masqués du menu : tout se fait depuis Arborescence.

### Classement (dans chaque document)

Un seul panneau remplace les boîtes natives de WordPress :

- Collections : l'arbre du schéma, numéroté, chaque rubrique avec ses éléments sur une ligne
- Nature du document : « Automatique » par défaut (Articles sous 1.1 donne Article), ou choix manuel
- Thèmes : arbre avec recherche, secteurs et pays compris
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
- **Remettre l'arborescence du client** : recrée collections, thèmes et natures exactement comme le schéma (les documents perdent ce classement)

### Passage de la 2.x à la 3.0

Au premier passage dans l'administration : s'il n'y a encore aucun document, l'ancienne arborescence est remplacée par celle du client, et les familles Secteurs, Pays et Prix sont supprimées. S'il y a déjà des documents, rien n'est effacé : l'arborescence du client est ajoutée et un message invite à reclasser, ou à utiliser le bouton de Réglages.

## Les shortcodes

### `[bqp_documents]`

Liste de documents, en **cartes** par défaut : nature et année, titre, auteurs, résumé court, emplacement numéroté (« 1.1 › Articles ») et « Voir la fiche ». Toute la carte est cliquable.

- **6 documents**, puis un bouton **« Afficher plus de documents »** juste en dessous, qui ajoute les suivants sans recharger la page (sans JavaScript, il ouvre la page suivante)
- quand la liste porte sur **une seule collection, un seul thème, une personne, une organisation ou une nature**, un **en-tête** la présente, dans le même style que la page Bibliothèque : bandeau aux couleurs de l'espace, numéro (« 01 »), « Bibliothèque numérique · Espace 01 », titre, description, nombre de documents et de rubriques, **recherche limitée à ces documents** et lien « Explorer dans la bibliothèque »
- quand cette collection ou ce thème a des sous-catégories, les **rubriques s'affichent en cartes** : « Tout », puis chaque rubrique numérotée avec sa description et son nombre de documents ; la rubrique choisie se colore et pointe vers ses sous-catégories, proposées en pastilles dans une ligne « Affiner » ; sur mobile, les cartes défilent et la rubrique choisie reste visible
- au-dessus des documents : le **nombre de résultats** et **« Trier par »** (plus récents, plus anciens, titre)
- rubriques, recherche, tri et « Afficher plus » mettent la liste à jour **sur place**, sans recharger la page ; l'adresse change et le bouton retour du navigateur fonctionne

Exemple : `[bqp_documents collection="fonds-jean-michel-quatrepoint"]` donne l'en-tête « 01 Fonds Jean-Michel Quatrepoint » (22 documents, 2 rubriques), puis les cartes Tout (22), 1.1 Écrits de Jean-Michel Quatrepoint (12), 1.2 Documents de son fonds documentaire (10) ; sous 1.1, Livres et ouvrages, Articles, Chroniques…

Le titre de l'en-tête est le nom de la collection ; l'attribut `titre` le remplace. C'est un H2 : la page garde son H1.

Plusieurs valeurs dans un même attribut : l'une OU l'autre. Plusieurs attributs : l'un ET l'autre.

| Attribut | Valeurs |
|---|---|
| `collection`, `nature`, `theme`, `secteur`, `pays`, `personne`, `organisation`, `motcle` | identifiants séparés par des virgules (`secteur` et `pays` désignent des sous-thèmes) |
| `annee` | `2024` ou `2015-2020` |
| `acces` | `public`, `adherents`, `partenaires`, `sur-place` |
| `groupe` | `annee`, `personne`, `theme`, `organisation`, `nature`, `collection` |
| `tri` | `recent` (défaut), `ancien`, `titre` |
| `nombre` | documents affichés avant « Afficher plus » (6 par défaut en cartes) |
| `affichage` | `cartes` (défaut) ou `liste` |
| `entete` | `non` pour masquer l'en-tête |
| `recherche` | `non` pour retirer la recherche de l'en-tête |
| `lien` | `non` pour retirer le lien « Explorer dans la bibliothèque » |
| `sousfiltres` | `non` pour masquer les rubriques en cartes |
| `outils` | `non` pour masquer le nombre de résultats et le tri |
| `filtres` | `oui` pour afficher la barre de filtres complète |
| `titre` | titre de l'en-tête (ou titre simple au-dessus de la liste sans en-tête) |
| `vide` | message si aucun document |
| `pagination` | `non` pour masquer « Afficher plus » |

Exemples :

```
[bqp_documents collection="travaux-primes" groupe="annee"]
[bqp_documents collection="ecrits-de-jean-michel-quatrepoint" theme="industrie" filtres="oui"]
[bqp_documents nature="entretien,conference-ou-intervention" annee="2020-2025" nombre="6" pagination="non"]
```

### `[bqp_bibliotheque]`

La page Bibliothèque complète : recherche simple ou avancée, 5 espaces à déplier, catalogue et annuaire.

| Attribut | Valeurs |
|---|---|
| `vides` | `oui` ou `non`, prioritaire sur le réglage |
| `navigation` | `non` pour masquer la barre de recherche et les accès rapides |
| `recherche` | `simple` (par défaut) ou `avancee` : mode de la barre au chargement |
| `blocs` | `non` pour masquer les 5 espaces |
| `titre_blocs` | titre des espaces, « Parcourir la bibliothèque » par défaut |
| `catalogue` | `non` pour n'afficher que la recherche et les espaces ; leurs liens mènent alors à la page Bibliothèque |
| `titre_catalogue` | titre du catalogue, « Tous les documents » par défaut |

### `[bqp_vitrine]`

Pour l'accueil : la bibliothèque mise en avant, dans le même design que la page Bibliothèque.

- **bandeau bordeaux** : titre, texte, chiffres clés, champ de recherche (qui mène aux résultats de la page Bibliothèque), bouton « Explorer la bibliothèque » et lien « Recherche avancée » (la page Bibliothèque s'ouvre alors en mode avancé)
- **les espaces en cartes numérotées**, qui servent d'onglets et chevauchent le bas du bandeau : Nouveautés, puis les quatre collections (ou les cinq thématiques) avec leur nombre de documents ; la carte choisie se colore et pointe vers son panneau
- **le panneau de l'onglet** : présentation de l'espace, bouton « Voir les N documents », rubriques numérotées en raccourcis (1.1, 1.2…), puis **3 documents** en cartes « En savoir plus »
- changement d'onglet instantané (tout est déjà dans la page), navigation au clavier avec les flèches ; les onglets sans document sont masqués
- sur mobile, les cartes défilent horizontalement et les rubriques aussi : la page reste courte

| Attribut | Valeurs |
|---|---|
| `nombre` | documents par onglet, de 1 à 6 (3 par défaut) |
| `onglets` | `collections` (défaut) ou `themes` |
| `titre`, `texte`, `bouton` | textes de l'en-tête ; vide pour les masquer |
| `nouveautes` | `non` pour retirer l'onglet Nouveautés |
| `recherche` | `non` pour retirer le champ de recherche du bandeau |
| `rubriques` | `non` pour retirer les raccourcis vers les rubriques |

### `[bqp_personnes]`

Annuaire autonome, pour une autre page si besoin (la page Bibliothèque intègre déjà le sien). `famille="personnes|organisations"`, `role="laureat|auteur|expert|partenaire"`, `type="…"` pour les organisations.

### `[bqp_document_fiche]`

Fiche d'un document dans une mise en page Elementor. `id="123"` facultatif.

## Paramètres d'adresse

La barre de filtres utilise des paramètres préfixés pour ne pas entrer en conflit avec WordPress : `f_q`, `f_collection`, `f_nature`, `f_theme`, `f_secteur`, `f_pays`, `f_personne`, `f_organisation`, `f_motcle`, `f_annee`, `f_de` et `f_jusqua` (période de la recherche avancée), `f_tri`, `f_groupe`, `pg` pour la page, et pour l'annuaire `f_annuaire` (`personnes` ou `organisations`) et `f_role` (rôle ou type). Seule la première liste d'une page écoute l'adresse.

Les vues filtrées gardent l'adresse canonique de la page Bibliothèque : Google n'indexe qu'une page, pas une par combinaison de filtres.

## Pages publiques

- **Page Bibliothèque** : la seule page à créer
- **Fiche document** `/bibliotheque/titre/` : générée pour chaque document. Fil d'Ariane vers la page Bibliothèque filtrée, badge de collection, résumé, boutons Télécharger, Regarder, Consulter ou Demander l'accès selon le niveau, bouton Citer, vidéo intégrée, informations, documents associés dans les deux sens
- **Page dédiée** (facultatif) : une catégorie peut pointer vers une page Elementor choisie dans l'Arborescence ; ses liens et son ancienne adresse y mènent alors

## Ce qui est créé automatiquement

- **54 collections** : 4 blocs, 16 rubriques (1.1 à 4.6), 34 éléments, avec leur nature proposée et leurs liens de regroupement
- **51 thèmes** : Souveraineté (7), Industrie (7), Secteurs stratégiques (12), Puissance et relations internationales (10, dont 4 pays), État et société (10)
- **22 natures**
- la personne **Jean-Michel Quatrepoint**, rôle Auteur, reliée à la page dédiée du bloc 1

Identifiants des éléments : préfixe de la rubrique, car un même nom revient dans plusieurs rubriques (`ecrits-articles`, `associees-articles`, `types-cahiers`…).

## Points d'attention

- **Documents réservés** : un fichier de la médiathèque reste accessible par son adresse. Ne pas téléverser le fichier d'un document réservé avant la protection des fichiers. Un avertissement s'affiche dans la fiche dès qu'un niveau autre que public est choisi.
- **Adresses** : ne pas créer de page enfant sous une page dont le slug est `bibliotheque`.
- **Cache** : les compteurs sont mis en cache et vidés à chaque enregistrement de document ou de catégorie.
- **Code Snippets** n'a pas de hook d'activation : les règles d'adresses sont enregistrées au premier chargement, puis à chaque changement de version.

## Historique

- **3.6.0** : cartes des 5 espaces au design des Partenaires et de la Gouvernance (bandeau coloré, numéro sur fond plein, nombre de documents en grand, « Découvrir » / « Replier ») ; panneau ouvert avec le numéro de l'espace et un fond teinté ; grille adaptée aux tablettes
- **3.5.0** : `[bqp_documents]` au design de la page Bibliothèque : en-tête aux couleurs de l'espace (numéro, présentation, chiffres, recherche dans la collection, lien vers la bibliothèque), rubriques en cartes avec ligne « Affiner », nombre de résultats et tri ; recherche et tri sans rechargement ; options `entete`, `recherche`, `lien`, `outils`
- **3.4.0** : vitrine `[bqp_vitrine]` au design de la page Bibliothèque : bandeau bordeaux avec recherche et lien « Recherche avancée », espaces en cartes numérotées comme onglets, panneau avec rubriques en raccourcis et « Voir les N documents » ; le lien `#recherche-avancee` ouvre la page Bibliothèque en mode avancé
- **3.3.0** : page Bibliothèque simplifiée. Une seule barre de recherche en haut, avec un sélecteur « Recherche simple / Recherche avancée » (période de… à…, compteur de critères, ouverture automatique quand un filtre est actif) ; la barre de filtres du catalogue disparaît, remplacée par le nombre de résultats et « Trier par ». Les 5 blocs deviennent 5 cartes compactes à déplier : espace, puis rubriques, puis sous-catégories, ouverts d'avance selon le filtre en cours. Accès rapide « Lauréats ». Pastilles « Filtres actifs » restylées, lien « Effacer les filtres » quand aucun document ne correspond
- **3.2.0** : refonte du design. Page Bibliothèque : en-tête de recherche avec accès transversaux et chiffres clés ; blocs en 2 × 2 avec numéro, total, rubriques, éléments en pastilles, regroupements en liens, encart « Page dédiée » avec monogramme et « Explorer » en pied ; bloc 5 en grille de thématiques et raccourcis « Autres filtres ». Filtres : barre compacte, filtre actif en bordeaux, application immédiate partout, « Effacer les filtres » et « Trier par ». Fiche : en-tête pleine largeur (nature, rubrique numérotée, titre H1, auteurs avec initiales, date, accès, résumé, action principale, Partager), couverture ou couverture générée aux couleurs du bloc, texte à 70 caractères par ligne, encadré Informations qui reste visible, bloc « Citer », documents associés en cartes (sinon « Dans la même rubrique ») ; le titre du thème Hello est masqué pour garder un seul H1
- **3.1.0** : listes en cartes, « Afficher plus » à la place de la pagination, onglets de sous-catégories, shortcode `[bqp_vitrine]` pour l'accueil, nouvelles options dans le Générateur
- **3.0.1** : le bouton « Rechercher et filtrer », prévu pour le mobile, n'apparaît plus sur ordinateur (le thème Hello forçait son affichage) ; intertitres de la notice aux couleurs du site ; taille des fichiers arrondie (« 180 Ko »)
- **3.0.0** : arborescence du client à l'identique, 6 familles
- **2.1.0** : une seule page Bibliothèque
- **2.0.0** : Arborescence, Classement, Générateur, Réglages

Pour tester le design avec de faux documents : snippet `bqp-bibliotheque-demo`.

## Testé

Dans un WordPress 6.8 réel avec le thème Hello Elementor et les quatre plugins BQP actifs :

- Arborescence : renommage (slug conservé), ajout racine et enfant, panneau de détail, glisser-déposer, suppression, recherche, copie du shortcode, vérifiés en base
- Classement : thèmes, personnes, organisation et pays créés depuis le document, vérifiés en base
- Générateur : shortcode et compteur en direct
- Réglages : affichage des liens vides activé puis visible sur le site ; détection automatique de la page Bibliothèque
- Page unique (2.1) : lien de bloc, filtre par liste, retrait d'une pastille, annuaire, lauréats, fiche personne, recherche, bouton retour, tous sans rechargement ; redirections 301 des anciennes adresses, filtres conservés ; exclusion des sitemaps ; filtres repliables sur mobile, sans débordement horizontal
- Arborescence du client (3.0) : migration depuis la 2.1 vérifiée en base (54 collections, 51 thèmes, 22 natures, anciennes familles supprimées) ; blocs identiques au schéma, numérotation conservée quand des rubriques sont masquées ; nature automatique puis manuelle ; page dédiée, fiche par lauréat, liens 5.2 qui ouvrent le bon filtre ; fiche document avec Collection / origine, Thèmes, Secteurs, Pays séparés
- Listes et vitrine (3.1) : 6 cartes puis « Afficher plus » (6, 12… sur 22, sans rechargement), onglets 1.1 et 1.2 puis éléments, bouton retour, vitrine avec 5 onglets et « En savoir plus », catalogue en cartes, mode liste, vue groupée, mobile sans débordement
- Listes (3.5) : en-tête avec chiffres justes, cartes de rubriques, sous-catégories, recherche dans la collection (qui garde la rubrique choisie), tri, « Afficher plus », bouton retour, tous sans rechargement ; page Bibliothèque et vitrine inchangées ; mobile 390 px sans débordement ; aucune erreur JavaScript ni PHP
- Simplification (3.3) : ouverture d'un espace (panneau sous la bonne rangée, focus sur son titre), changement d'espace, fermeture par la croix et Échap ; dépliage d'une rubrique ; clic sur une sous-catégorie qui filtre le catalogue et la met en évidence ; passage simple / avancée, compteur de critères, recherche avancée avec période, tri conservé, recherche simple qui ignore les critères avancés ; lien partagé qui ouvre le bon espace et la bonne rubrique ; « Affiner par secteur » qui ouvre la recherche avancée sur ce champ ; bouton retour ; liste vide avec effacement ; mobile 390 px sans débordement ; [bqp_documents], [bqp_vitrine] et fiches inchangés ; aucune erreur JavaScript ni PHP
- Design (3.2) : liens des rubriques, pastilles d'éléments, regroupements, page dédiée, tuiles de thèmes et raccourcis 5.2 filtrent le catalogue sans rechargement ; filtre appliqué et mis en évidence, effacement ; un seul H1 sur la fiche ; Partager et Copier la référence ; couverture générée sans visuel ; mobile sans débordement
- Front : toutes les pages en HTTP 200, aucune erreur PHP ni JavaScript, liens du plugin en bordeaux malgré le rose du thème

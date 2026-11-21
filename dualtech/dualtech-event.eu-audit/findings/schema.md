# 4. Données structurées (90 / 100)

**Points positifs**
* JSON‑LD valide sur toutes les pages, un seul graphe par page (pas de doublon avec Rank Math).
* Event complet : nom, dates de début et de fin, lieu (Place avec adresse), statut, mode présentiel, image, description, offre gratuite (Offer à 0 €), organisateur, 34 intervenants (`performer`), programme en sous‑événements.
* Organization (INAS), GovernmentOrganization (Paris Ouest La Défense), WebSite, BreadcrumbList, ContactPage, CollectionPage avec la liste des intervenants.

**Problèmes**

| Gravité | Constat | Correction |
|---|---|---|
| Faible | `/hello-world/` et `/category/uncategorized/` produisent un schéma BlogPosting et CollectionPage sans intérêt. | Disparaît avec la suppression de ces pages. |
| Info | Le logo de l'organisateur n'est pas déclaré (`logo` de l'Organization INAS). | Ajouter l'URL du logo INAS dans le graphe. |

Test conseillé : https://search.google.com/test/rich-results sur l'accueil et la page Programme.

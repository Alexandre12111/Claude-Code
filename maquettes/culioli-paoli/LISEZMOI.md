# Maquette · Charles Culioli-Paoli, avocat

Maquette HTML statique (19 pages + plan SEO) servant de base au futur site WordPress.

## Générer
```
python3 build.py
```
Les contenus sont dans `content.py`, `pages_expertises.py` et `pages_lieux.py`. Le style est dans `assets/site.css`, les interactions dans `assets/site.js`.

## Direction artistique (v4, retenue)
« Éditorial parisien » : ivoire dominant, anthracite, bleu nuit en contrepoint (domaines, bandeaux de contact, pied de page), bronze réservé aux numéros. Cormorant Garamond pour les titres (H1 54 px, H2 37 px, H3 25 px) et Manrope pour le texte (16 px) et l'interface (13 px). Grille de 12 colonnes, compositions déséquilibrées, filets fins, aucun arrondi ni ombre. Audit, trois pistes comparées, design system et auto-évaluation : `direction-artistique.html`.

## Fonctionnalités
* Bouton « Urgence pénale » : le numéro ne s'affiche qu'après un clic, puis devient cliquable (tel:) et copiable.
* WhatsApp flottant (desktop) et barre d'actions fixe (mobile).
* Formulaire de contact avec validation, mention de confidentialité, sans pièce jointe.
* Filtres de la rubrique établissements pénitentiaires.
* JSON-LD (LegalService, Attorney, Person, Service, FAQPage, BreadcrumbList, Article), Open Graph, canonical, sitemap.xml, robots.txt.

## À valider avec Me Culioli-Paoli (repéré en rouge « à confirmer » dans la maquette)
* Inscription au Barreau de New York, date de prestation de serment.
* Adresse Paris 8e et bureau de Massy.
* Expériences (Fedida & Associés, Schapira Associés, Parquet de Paris, ministère de l'Économie) : dates et fonctions.
* Niveau de pratique des langues.
* Disponibilité 24h/24, numéro d'urgence, numéro WhatsApp, domaine du site.
* Affichage ou non du premier rendez-vous offert et des honoraires.
* Activité accidents du travail : à intégrer ou non.
* Textes de vision et de plaidoirie (formulations proposées).
* Régions proposées : Corse, Provence-Alpes-Côte d'Azur, Lyon.
* Photos : une séance professionnelle supplémentaire est recommandée (cabinet, détails, architecture).

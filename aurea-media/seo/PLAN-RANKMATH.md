# Plan Rank Math page par page

Généré par `construire-plan.py`. Le thème applique ces réglages à l'import (champs Rank Math `rank_math_title`, `rank_math_description`, `rank_math_focus_keyword`, `rank_math_robots`). Toutes les pages sont en index, follow, avec un seul H1 contenant le mot clé principal.

| URL | Mot clé principal | SEO Title (caractères) | Meta | Schema |
|---|---|---|---|---|
| / | agence web sur mesure | Agence web sur mesure : création de site internet \| Aurea (57) | 143 car. | ProfessionalService + WebSite |
| /creation-site-vitrine/ | création site vitrine | Création site vitrine sur mesure \| Dès 1 000 € HT, Aurea (56) | 144 car. | Service + Offer + BreadcrumbList |
| /creation-site-ecommerce/ | création site e-commerce | Création site e-commerce sur mesure \| WooCommerce, Aurea (56) | 143 car. | Service + Offer + BreadcrumbList |
| /refonte-site-internet/ | refonte site internet | Refonte site internet sans perdre son SEO \| Aurea Media (55) | 146 car. | Service + BreadcrumbList |
| /logo-identite-visuelle/ | création identité visuelle | Création identité visuelle et logo d'entreprise \| Aurea (55) | 147 car. | Service + BreadcrumbList |
| /motion-design/ | motion design entreprise | Motion design pour entreprise : vidéos animées \| Aurea (54) | 146 car. | Service + BreadcrumbList |
| /maquettes-presentation/ | création maquette présentation | Création de présentation et pitch deck sur mesure \| Aurea (57) | 140 car. | Service + BreadcrumbList |
| /site-internet-avocat/ | site internet avocat | Site internet avocat : création pour cabinet \| Aurea (52) | 140 car. | Service + BreadcrumbList |
| /site-internet-professions-sante/ | site internet professionnel de santé | Site internet professionnel de santé : médecin, kiné \| Aurea (60) | 150 car. | Service + BreadcrumbList |
| /site-internet-architecte/ | site internet architecte | Site internet architecte : portfolio sur mesure \| Aurea (55) | 147 car. | Service + BreadcrumbList |
| /site-internet-restaurant/ | site internet restaurant | Création site internet restaurant avec réservation \| Aurea (58) | 144 car. | Service + BreadcrumbList |
| /site-internet-artisan-btp/ | site internet artisan | Site internet artisan et BTP : plus de devis \| Aurea (52) | 137 car. | Service + BreadcrumbList |
| /site-internet-coach-consultant/ | site internet coach | Création site internet coach et consultant \| Aurea Media (56) | 147 car. | Service + BreadcrumbList |
| /agence-web-paris/ | agence web Paris | Agence web Paris : création de site internet sur mesure (55) | 144 car. | ProfessionalService + BreadcrumbList |
| /agence-web-hauts-de-seine/ | agence web Hauts de Seine | Agence web Hauts de Seine (92) : création de site \| Aurea (57) | 141 car. | ProfessionalService + BreadcrumbList |
| /agence-web-boulogne-billancourt/ | agence web Boulogne-Billancourt | Agence web Boulogne-Billancourt : création de site \| Aurea (58) | 145 car. | ProfessionalService + BreadcrumbList |
| /agence-web-clamart/ | agence web Clamart | Agence web Clamart : création de site internet \| Aurea Media (60) | 142 car. | ProfessionalService + BreadcrumbList |
| /realisations/ | réalisations agence web | Réalisations : sites vitrines et e-commerce \| Aurea Media (57) | 147 car. | CollectionPage + ItemList |
| /tarifs/ | prix site vitrine | Prix d'un site vitrine et e-commerce en 2026 \| Aurea Media (58) | 143 car. | WebPage + Offer + BreadcrumbList |
| /agence/ | agence web indépendante | Aurea Media, agence web indépendante fondée par A. Coury (56) | 137 car. | AboutPage + Person |
| /contact/ | contact agence web | Contact et devis gratuit sous 24h \| Aurea Media (47) | 144 car. | ContactPage |
| /conseils/ | conseils site internet | Conseils pour réussir son site internet \| Blog Aurea Media (58) | 135 car. | Blog + BreadcrumbList |

## Règles appliquées sur chaque page

* Mot clé principal au début du title, dans l'URL, le H1, les 100 premiers mots, au moins un H2 et le texte alternatif de l'image principale.
* Title entre 45 et 60 caractères, meta description entre 135 et 160 caractères, avec un appel à l'action.
* Contenu de 700 à 1 500 mots sur les pages services, secteurs et locales, écrit pour l'intention de recherche (pas de remplissage).
* 3 à 5 liens internes contextuels (colonne `liens_internes` du JSON) et 1 ou 2 liens externes de référence quand ils apportent de la valeur.
* Images en WebP avec dimensions fixes, nom de fichier descriptif et ALT qui décrit l'image.
* Schema adapté au contenu ; pas de HowTo ; pas d'AggregateRating auto déclaré ; FAQ affichée en contenu, sans balisage FAQPage.
* Fil d'Ariane Rank Math activé, sitemap Rank Math actif, pages /tag/ et archives d'auteur en noindex.
* Fiches réalisations : title « Site [Client] : étude de cas [type] | Aurea Media », mot clé « site [type] [secteur] », schema CreativeWork.

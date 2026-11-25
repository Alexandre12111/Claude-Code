# Audit SEO complet : aurea-media.fr

Date : 17 novembre 2026 (quatrième passage) · Thème en ligne : Aurea 3.1.9 · Corrections livrées : Aurea 3.2.0 · 80 adresses explorées

## Score de santé SEO : 91/100 en ligne, 92/100 attendu après le thème 3.2.0

| Catégorie | Poids | 27 oct. | 17 nov. (en ligne) | Après 3.2.0 |
|---|---|---|---|---|
| SEO technique | 22 % | 94 | 93 | 95 |
| Contenu et E-E-A-T | 23 % | 85 | 85 | 88 |
| SEO on-page | 20 % | 94 | 94 | 95 |
| Données structurées | 10 % | 88 | 88 | 88 |
| Performance | 10 % | 94 | 94 | 94 |
| Moteurs de réponse IA | 10 % | 88 | 88 | 90 |
| Images | 5 % | 94 | 94 | 94 |

## État du site en ligne (exploration du 17 novembre)
- 80 adresses : 79 en 200, une seule en 301 (/site-internet-artisan/, corrigé dans 3.1.10).
- 76 pages indexables : titres, descriptions, H1 uniques et canonical conformes partout ; aucune image sans texte alternatif ni dimensions.
- 8 articles de 1 538 à 1 752 mots ; pack 1 en cours de publication.
- Search Console (export du 17 novembre) : depuis la refonte, position moyenne passée d'environ 60 à environ 35, 7 à 8 clics par semaine. Les impressions viennent encore surtout d'anciennes pages en position 50 à 75.
- Demande sans page : environ 450 impressions sur « site internet collectivité / mairie » dans le 92, environ 750 sur « identité visuelle » en position 60 à 90.

## Constats et corrections

| Priorité | Constat | Correction | Version |
|---|---|---|---|
| Haute | Aucune page pilier (services, secteurs, pages locales) ne renvoie vers les articles : le blog ne transmet pas sa pertinence aux pages qui vendent | Section « Pour aller plus loin » sur 15 pages, 3 guides choisis par page, complétée par les derniers articles | 3.2.0 |
| Haute | Requêtes « site internet collectivité / mairie » sans page dédiée | Nouvelle page /site-internet-collectivites/ (environ 2 000 mots, obligations RGAA, achat public, références Dual Tech, INAS, Massy, FAQ, sources officielles) ; l'ancienne adresse « agence-web-collectivites-boulogne-billancourt » y redirige | 3.2.0 |
| Haute | Une nouvelle page du thème n'était pas créée lors d'une mise à jour (il fallait relancer l'installation) | Création automatique des pages ajoutées au thème ; une page existante n'est jamais touchée | 3.2.0 |
| Haute | Article « site-internet-artisan » bloqué par une redirection Rank Math ; « fiche-google-business-profile » non publié (programmation manquée) | Redirections désactivées à la publication, publication des articles en retard | 3.1.10 |
| Moyenne | /referencement-local-google/ ne reçoit que 3 liens internes | Désormais cité par 6 pages piliers via « Pour aller plus loin » | 3.2.0 |
| Moyenne | Page « L'agence » courte (342 mots) pour une page clé de confiance | Bloc « En bref » (fondateur, date, projets, clients) et section des secteurs avec liens | 3.2.0 |
| Moyenne | « refonte-identite-visuelle » redirigée vers la page logo alors que la requête a une intention informationnelle | Redirection retirée du plan : l'article du pack 2 occupe cette adresse le 8 décembre | 3.2.0 |
| Basse | Un article programmé qui cite un article publié plus tard obtient un lien « ?p=ID » (une redirection de plus) | Liens réécrits automatiquement vers l'adresse définitive à la publication | 3.2.0 |
| Basse | Liens des sections claires peu visibles (même couleur que le texte) | Couleur or foncé soulignée, contraste conforme | 3.2.0 |

## Nouvelle page collectivités (contrôle local, thème 3.2.0)
- Title 60 caractères, meta 155, un H1, slug court, mot clé principal « site internet collectivité » ; secondaires « création site internet mairie », « agence web collectivités ».
- Schéma : ProfessionalService, WebPage, BreadcrumbList, Service. Bloc « En bref » de 159 mots.
- 4 liens externes vers des sources officielles vérifiées (collectivites-locales.gouv.fr, accessibilite.numerique.gouv.fr, vie-publique.fr, Banque des Territoires).
- Lighthouse local : accessibilité 100, SEO 100, bonnes pratiques 96 (image d'administration WordPress absente en local), performance 85 sur le serveur de test mono processus.

## Limites
- Le thème 3.2.0 n'est pas encore installé : les gains sont mesurés en local.
- Pas de données terrain (CrUX) ni de liens entrants.

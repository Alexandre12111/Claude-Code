# Analyse SEO locale et Search Console : aurea-media.fr

Date : 17 novembre 2026 · Sources : export Search Console (16 août au 15 novembre 2026), site en ligne (thème 3.1.9), sitemap, redirections.

## Score SEO local : 62/100

| Dimension | Poids | Score |
|---|---|---|
| Fiche Google (signaux visibles) | 25 % | 65 |
| Avis et réputation | 20 % | 75 |
| SEO local des pages | 20 % | 75 |
| Cohérence NAP et citations | 15 % | 40 |
| Données structurées locales | 10 % | 75 |
| Autorité locale (liens, mentions) | 10 % | 25 |

**Type d'activité** : prestataire à zone desservie (fiche Google sans adresse affichée), avec une adresse de siège (60 rue François Ier, Paris 8e) sur le site et dans les mentions légales.
**Secteur** : agence web (services aux entreprises), pas de gabarit sectoriel spécifique.

## 1. Ce que disent tes données Search Console

### Les chiffres (3 derniers mois)
- 70 clics, 3 077 impressions, CTR 2,3 %, position moyenne vers 51.
- 33 % des clics viennent de la recherche de la marque (« aurea media » : 23 clics, position 2,6, CTR 32 %).
- Hors marque, aucune requête n'est en première page : les pages se trouvaient en moyenne entre la 5e et la 8e page de Google.

### Pourquoi ce n'est « pas fou »
- La refonte date de fin octobre : la période analysée montre surtout **l'ancien site**. Ses pages les plus vues (« refonte identité visuelle entreprise » : 549 impressions, « agence web collectivités Boulogne » : 418, « branding identité » : 362, « création site vitrine Paris » : 242) étaient en position 50 à 75, donc presque jamais cliquées.
- Le domaine est jeune et presque sans lien externe : sur des requêtes concurrentielles comme « création site vitrine Paris », Google place devant des annuaires et des agences installées.

### Les premiers effets de la refonte (encourageants)
| Semaine | Clics | Impressions | Position moyenne |
|---|---|---|---|
| Septembre (moyenne) | 2 | 175 | 47 |
| Octobre, avant la refonte | 1 | 160 | 60 |
| 26 octobre | 7 | 150 | 40,5 |
| 2 novembre | 8 | 162 | 37,6 |
| 9 novembre (semaine incomplète) | 2 | 171 | 35,2 |

La position moyenne passe de 60 à 35 en trois semaines, et les clics hebdomadaires remontent. Il faut compter **3 à 6 mois** pour que Google réévalue complètement la nouvelle structure.

### La demande réelle que révèle l'export
| Thème | Requêtes types | Impressions | Position | Page actuelle |
|---|---|---|---|---|
| Collectivités et mairies (92) | agence web collectivités Boulogne, Neuilly, Courbevoie, Levallois, Nanterre ; création site internet mairie | environ 450 | 28 à 75 | aucune page dédiée |
| Identité visuelle | création identité visuelle Paris, refonte logo entreprise, refonte identité visuelle | environ 750 | 60 à 90 | /logo-identite-visuelle/ |
| Site vitrine Paris | création site vitrine Paris (et variantes), TPE | environ 250 | 52 à 75 | accueil, /creation-site-vitrine/, /agence-web-paris/ |
| Local 92 | agence web Hauts-de-Seine, agence SEO Clamart | environ 100 | 24 | /agence-web-hauts-de-seine/ |

## 2. Problèmes trouvés et corrigés (thème 3.1.10)

| Priorité | Constat | Correction |
|---|---|---|
| Critique | L'article « Site internet pour artisan », publié le 10 novembre et présent dans le sitemap, était redirigé par Rank Math vers la page artisans (ancienne règle reprenant le même slug) : article inaccessible | Règle retirée ; à chaque publication, toute redirection qui partirait de l'adresse du nouveau contenu est désactivée automatiquement |
| Critique | Article « Fiche Google Business Profile », prévu le 17 novembre à 9 h, non publié (programmation manquée) | Les articles programmés en retard se publient seuls à la première visite (vérification toutes les 10 minutes) |
| Haute | « refonte identité visuelle entreprise » (549 impressions) et « refonte identité visuelle » redirigeaient vers la refonte de **site** | Redirigées vers /logo-identite-visuelle/ |
| Haute | /nos-conseils-web/ et /deleguer-sa-presence-linkedin/ en 404 | Redirigées vers /conseils/ |
| Moyenne | Anciennes pages Google My Business redirigées vers la page site vitrine | Redirigées vers l'article « Fiche Google Business Profile » (vers le blog en attendant sa publication) |
| Moyenne | « combien coûte un site e-commerce » redirigé vers la page service | Redirigé vers l'article « Prix d'une boutique en ligne » |
| Moyenne | « création site vitrine TPE Paris » redirigé vers les tarifs | Redirigé vers /creation-site-vitrine/ |

## 3. Fiche Google : liste de contrôle
| Élément | État |
|---|---|
| Fiche existante et reliée au site (identifiant Knowledge Graph, Place ID) | Oui |
| Avis affichés sur le site via l'API Google Places | Oui (43 avis, 4,9) |
| Adresse masquée (zone desservie) | Oui, conforme pour une activité sans bureau |
| Catégorie principale « Concepteur de sites Web » | À vérifier dans la fiche |
| 4 catégories secondaires (agence de marketing, graphiste, agence de publicité, service de conception de logo) | À vérifier |
| Services avec prix (site vitrine dès 1 000 €, e-commerce dès 1 650 €…) | À vérifier |
| Zone desservie : Paris, Hauts-de-Seine, communes clés (Boulogne, Issy, Neuilly, Levallois, Clamart, Nanterre) | À vérifier |
| Photos de réalisations, logo, photo du fondateur | À ajouter si absentes |
| Un post par semaine | À lancer |
| Lien du site : page /agence-web-paris/ plutôt que l'accueil | Conseillé (ne pas pointer la page la plus forte) |

## 4. Avis
- 43 avis Google, note 4,9 : très bon niveau.
- Régularité : viser au moins un nouvel avis toutes les 3 semaines (les positions locales baissent après de longues pauses).
- Répondre à chaque avis, en citant le service et la ville quand c'est naturel.
- Une seule plateforme : ajouter des avis sur un second site (Sortlist, La Fabrique du Net ou Malt).

## 5. Cohérence NAP
| Source | Nom | Adresse | Téléphone |
|---|---|---|---|
| Pied de page et contact | Aurea Media | 60 rue François Ier, 75008 Paris | 06 51 28 77 61 (+33 6 51 28 77 61) |
| Données structurées | Aurea Media | 60 rue François Ier, 75008 Paris | +33651287761 |
| Fiche Google | à confirmer | masquée (zone desservie) | à confirmer |

Le site est cohérent. Vérifie que la fiche Google porte exactement le nom « Aurea Media » (sans mots clés ajoutés) et le même numéro. L'adresse du siège reste sur le site (obligation légale) et masquée sur la fiche : c'est cohérent.

## 6. Citations
Aucune citation trouvée sur les annuaires. À créer avec le même nom, la même adresse et le même téléphone :
1. Bing Places (alimente ChatGPT et Copilot), import depuis la fiche Google.
2. Apple Business Connect (Plans).
3. PagesJaunes.
4. Annuaires d'agences : Sortlist, La Fabrique du Net, Codeur, Malt (profil relié à Aurea Media).
5. Facebook (page entreprise) et LinkedIn (déjà présent).

## 7. Données structurées locales
En place : ProfessionalService avec nom, adresse, téléphone, zone desservie (France, Île-de-France, Europe), fondateur, SIREN, sameAs LinkedIn et Knowledge Graph.
Améliorations possibles : nommer les communes dans `areaServed` sur la page Hauts-de-Seine ; ajouter Bing Places et les annuaires dans `sameAs` une fois les fiches créées. Pas de `geo` ni d'horaires : normal pour une activité sur rendez-vous sans bureau ouvert au public.

## 8. Les 10 actions prioritaires
1. **Critique** : installer le thème 3.1.10, puis vider le cache (article artisan accessible, article Google Business Profile publié, redirections corrigées).
2. **Critique** : dans la Search Console, inspecter et demander l'indexation de /site-internet-artisan/ et /fiche-google-business-profile/.
3. **Haute** : créer une page « Site internet pour collectivités et mairies » (environ 450 impressions déjà sans page dédiée), avec les références institutionnelles du portfolio.
4. **Haute** : renforcer /logo-identite-visuelle/ sur « création identité visuelle Paris » et « refonte identité visuelle », et ajouter un article « Refonte d'identité visuelle : quand et comment ».
5. **Haute** : compléter la fiche Google (catégories, services avec prix, zone desservie, photos, un post par semaine).
6. **Haute** : créer Bing Places et Apple Business Connect.
7. **Moyenne** : inscription sur 4 annuaires d'agences avec le même NAP.
8. **Moyenne** : un nouvel avis Google toutes les 3 semaines, avec réponse systématique.
9. **Moyenne** : liens internes depuis l'accueil et les articles vers /agence-web-hauts-de-seine/ (déjà en position 24 sur « agence web Hauts-de-Seine »).
10. **Basse** : nouvel export Search Console fin décembre pour mesurer l'effet de la refonte sur 2 mois complets.

## 9. Limites
Non évalué : positions réelles dans le pack local (grille géographique), statistiques de la fiche Google (appels, itinéraires), liens entrants complets, autorité de domaine. Outils utiles : BrightLocal ou Local Falcon (grille locale), Ahrefs ou Semrush (liens), statistiques de la fiche Google dans son tableau de bord.

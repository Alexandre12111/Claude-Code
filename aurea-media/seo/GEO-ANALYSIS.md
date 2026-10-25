# Analyse GEO : aurea-media.fr (site actuel et nouvelle maquette)

Analyse du 25 octobre 2026, menée avec le skill seo-geo. Principe de Google, repris tout au long : optimiser pour la recherche générative, c'est **du SEO**. Les signaux ci dessous sont des fondamentaux appliqués aux réponses des IA (AI Overviews, AI Mode, ChatGPT Search, Perplexity, Claude).

## Score de préparation GEO

| | Site actuel | Nouvelle maquette |
|---|---|---|
| **Score global** | **48 / 100** | **78 / 100** |
| Citabilité (25 %) | 45 | 85 |
| Structure lisible (20 %) | 65 | 85 |
| Multimédia (15 %) | 40 | 80 |
| Autorité et marque (20 %) | 35 | 55 |
| Accessibilité technique (20 %) | 60 | 85 |

Le plafond de la nouvelle maquette vient surtout de l'**autorité de marque**, qui se construit hors du site (voir plus bas).

### Par plateforme (nouvelle maquette)

| Plateforme | Score | Ce qui compte |
|---|---|---|
| Google AI Overviews | 70 | Corrélé au classement classique : dépend d'abord de la reprise de positions (plan Rank Math, redirections). |
| Google AI Mode | 75 | Fraîcheur et autorité d'entité : blocs datés, graphe JSON-LD relié (organisation, fondateur, services). |
| ChatGPT Search | 65 | Présence de l'entité ailleurs (LinkedIn, annuaires, mentions) et pages citables. |
| Perplexity | 60 | Validation communautaire (Reddit, forums) quasi absente aujourd'hui. |

## Accès des robots IA (site actuel)

robots.txt actuel : aucune règle spécifique, donc tout est autorisé par la ligne `User-agent: *`.

| Robot | Rôle | Statut |
|---|---|---|
| Googlebot | Recherche Google, AI Overviews et AI Mode | Autorisé |
| OAI-SearchBot | Citations dans ChatGPT Search | Autorisé |
| Claude-SearchBot | Citations dans la recherche de Claude | Autorisé |
| PerplexityBot | Recherche Perplexity | Autorisé |
| GPTBot | Entraînement OpenAI (sans effet sur ChatGPT Search) | Autorisé |
| ClaudeBot | Entraînement Anthropic (sans effet sur la recherche de Claude) | Autorisé |
| Google-Extended | Entraînement et ancrage Gemini (sans effet sur Google Search) | Autorisé |
| CCBot | Common Crawl, entraînement | Autorisé |
| Applebot-Extended | Entraînement Apple Intelligence (sans effet sur Siri et Spotlight) | Autorisé |

Nouveau `robots.txt` (fichier `seo/robots.txt`) : les trois robots de citation sont autorisés explicitement ; l'entraînement reste autorisé par défaut, avec la marche à suivre pour le refuser sans perdre les citations.

## llms.txt

Présent sur le site actuel, généré par Rank Math, mais il ne liste que des articles : aucune page service, aucun prix, aucune information sur l'entreprise. Nouveau fichier rédigé à la main (`seo/llms.txt`) : description de l'entreprise, services avec prix, preuves et contact.

Important : Google indique que `llms.txt` n'aide ni ne pénalise la recherche Google. Il peut servir à d'autres systèmes. Aucun poids de classement ne lui est attribué ici.

## Rendu côté serveur

Le site actuel et le futur thème servent le texte dans le HTML (WordPress, sans rendu JavaScript). Les robots IA, qui n'exécutent pas JavaScript, lisent donc tout le contenu. Dans la maquette, les animations ne cachent jamais le texte du HTML : il est seulement révélé visuellement.

## Citabilité : ce qui a été fait dans la maquette

* **Blocs « En bref »** en haut de 10 pages stratégiques, juste sous le hero (44 % des citations IA viennent du premier tiers de la page) : une question en H2, une réponse autonome de 139 à 151 mots (longueur optimale 134 à 167), puis les faits clés en liste de définitions. Chaque bloc donne une définition (« Un site vitrine est… »), un prix, un délai et une preuve.
* **Date de mise à jour et auteur** sur chaque bloc (le contenu de moins de 3 mois est environ 3 fois plus cité).
* **Titres en questions** dans les FAQ et les blocs, tableaux comparatifs (WooCommerce contre Shopify, site sur mesure contre site fait soi même, budgets du marché dans l'article).
* **Multimédia réel** : film de marque, présentation commerciale, captures de 23 sites, maquettes d'écrans.

## Données structurées (JSON-LD)

Un graphe relié par identifiants sur chaque page :

* `ProfessionalService` (l'agence), `Person` (Alexandre Coury, fondateur) et `WebSite`, partagés partout ;
* `Service` avec prix minimal sur les pages services, secteur et locale ;
* `BlogPosting` (auteur, dates) sur l'article, `CreativeWork` sur l'étude de cas, `CollectionPage`, `AboutPage`, `ContactPage` ;
* `BreadcrumbList` sur toutes les pages intérieures.

Volontairement absents : `FAQPage` (plus de résultat enrichi depuis mai 2026), `HowTo` (abandonné), `AggregateRating` sur l'entreprise (avis auto déclarés ignorés par Google). Les FAQ restent affichées en contenu.

## Présence de la marque hors du site

| Plateforme | Constat | Action |
|---|---|---|
| LinkedIn | Page entreprise et profil du fondateur | Publier chaque étude de cas, lier le site |
| Google Business Profile | Fiche active, 39 avis | Publications mensuelles, photos de projets, réponses aux avis |
| YouTube | Absent (signal le plus corrélé aux citations IA) | Publier le film de marque et de courtes vidéos « avant / après » de refontes |
| Reddit, forums | Absent | Répondre à des questions sur r/france_entreprises ou forums de TPE, sans publicité |
| Annuaires d'agences | À vérifier | La Fabrique du Net, Sortlist, Codeur : même fiche NAP partout |
| Wikipedia, Wikidata | Absent | Pas pertinent à ce stade (critères de notoriété) |

Les mentions de marque comptent environ 3 fois plus que les liens pour la visibilité dans les IA (étude Ahrefs, 75 000 marques).

## Les 5 changements à plus fort impact

1. **Mettre en ligne la nouvelle architecture avec les redirections** : les AI Overviews citent surtout des pages déjà bien classées. Sans positions, pas de citations.
2. **Garder les blocs « En bref » à jour** chaque trimestre (prix, nombre de projets, avis) et changer la date.
3. **Chaîne YouTube** avec le film de marque et des études de cas en vidéo.
4. **Une étude de cas complète par projet** (le module Réalisations le permet) : ce sont des contenus uniques, citables, que personne d'autre ne peut produire.
5. **Données propres** : publier une petite étude originale, par exemple « délai et budget moyens de 45 sites livrés », chiffres réels à l'appui. Les contenus de première main sont les plus cités.

## Suivi

* Search Console : impressions des pages services et apparitions dans les AI Overviews (rapport Performances, apparence dans les résultats).
* Requêtes de test mensuelles dans ChatGPT, Perplexity et Google AI Mode : « agence création site vitrine sur mesure », « prix site vitrine 2026 », « site internet avocat ».
* Échec si, trois mois après la mise en ligne, aucune page n'apparaît dans les réponses : priorité alors aux mentions externes (YouTube, annuaires, LinkedIn).

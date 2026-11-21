# Analyse GEO (moteurs de réponse IA) : aurea-media.fr

Date : 17 novembre 2026 (deuxième passage) · Thème en ligne : Aurea 3.2.0 · Correctifs livrés : Aurea 3.2.1
Méthode : robots.txt, llms.txt, rendu serveur, données structurées et blocs citables sur 6 pages clés, recherche des mentions de la marque.

Rappel de Google (guide d'optimisation IA, mai 2026) : optimiser pour les réponses IA reste du SEO. Les AI Overviews et le mode IA s'appuient sur l'index de Googlebot ; le fichier llms.txt n'aide ni ne pénalise dans Google.

## Score de préparation GEO : 82/100 (81 le 27 octobre)

| Critère | Poids | 27 oct. | 17 nov. |
|---|---|---|---|
| Citabilité des passages | 25 % | 88 | 90 |
| Structure et lisibilité | 20 % | 90 | 92 |
| Contenus multimédias | 15 % | 85 | 85 |
| Autorité et mentions de la marque | 20 % | 45 | 45 |
| Accessibilité technique | 20 % | 95 | 96 |

Le site lui même est presque au maximum de ce qu'il peut faire. Le point faible reste l'extérieur : la marque est quasi absente des sources tierces que les IA consultent.

## Par plateforme
| Plateforme | Score | Lecture |
|---|---|---|
| Google AI Overviews et mode IA | 82 | Passages clairs, données structurées, fraîcheur : prêt. Limité par les positions Google encore moyennes (environ 35) |
| ChatGPT Search | 60 | Accès autorisé ; dépend de l'index Bing et des mentions tierces, rares |
| Perplexity | 55 | Accès autorisé ; s'appuie beaucoup sur Reddit et les forums : aucune présence |
| Claude (recherche) | 65 | Accès autorisé, contenu clair ; dépend aussi des sources tierces |

## Accès des robots (robots.txt en ligne)
| Robot | Ce qu'il commande | Statut |
|---|---|---|
| Googlebot | Google Search, AI Overviews, mode IA | Autorisé (règle générale) |
| OAI-SearchBot | Citations dans ChatGPT Search | Autorisé explicitement |
| Claude-SearchBot | Citations dans la recherche de Claude | Autorisé explicitement |
| PerplexityBot | Recherche Perplexity | Autorisé explicitement |
| GPTBot | Entraînement des modèles OpenAI (pas ChatGPT Search) | Autorisé (règle générale) |
| ClaudeBot | Entraînement des modèles Anthropic (pas la recherche de Claude) | Autorisé (règle générale) |
| Google-Extended | Entraînement et ancrage Gemini, Vertex (pas Google Search) | Autorisé (règle générale) |
| CCBot, Applebot-Extended | Entraînement (Common Crawl, Apple Intelligence) | Autorisés (règle générale) |

Les robots d'entraînement relèvent d'un choix de licence, pas de visibilité : les laisser ouverts est cohérent pour une agence qui cherche à être connue.

## llms.txt
Présent (200, 65 lignes) : présentation, services, secteurs dont la nouvelle page collectivités, zones, articles. Aucun poids dans Google ; utile au mieux pour d'autres assistants. Rien à changer.

## Rendu serveur
WordPress rend tout le contenu côté serveur : textes, blocs « En bref », FAQ et données structurées sont lisibles sans JavaScript. Les animations ne masquent aucun texte au chargement.

## Passages citables (6 pages analysées en ligne)
| Page | Bloc « En bref » | H2 en question | Tableaux | Données structurées |
|---|---|---|---|---|
| Accueil | oui | 1 | 0 | ProfessionalService, Person, WebSite, WebPage |
| /site-internet-collectivites/ | oui (159 mots) | 1 | 1 | + Service, BreadcrumbList |
| /creation-site-vitrine/ | oui | 3 | 1 | + Service, BreadcrumbList |
| /agence/ | oui (147 mots, nouveau) | 1 | 0 | AboutPage, Person |
| /tarifs/ | oui | 1 | 0 → 1 en 3.2.1 | WebPage, BreadcrumbList |
| /prix-site-vitrine/ (article) | encart « En bref » | 2 | 2 | BlogPosting |

Les blocs « En bref » font tous 134 à 167 mots, sont placés juste après le haut de page (le premier tiers, d'où viennent la majorité des citations) et sont datés et signés.

## Mentions de la marque (recherche du 17 novembre)
- Aucune page tierce ne cite « Aurea Media » ni aurea-media.fr dans les résultats.
- Le nom est en concurrence avec des homonymes : Aurea Studio (vidéo IA), Aurea Productions (production), le groupe coté AUREA. Les IA risquent de confondre les entités tant que la marque n'est pas citée ailleurs.
- Un profil Malt « Alexandre Coury, développeur web à Paris » apparaît, sans lien vers Aurea Media.
- Présences confirmées : fiche Google (identifiant Knowledge Graph dans sameAs), pages LinkedIn de l'entreprise et du fondateur.

## Corrections faites (thème 3.2.1)
1. Page Tarifs : tableau comparatif des offres (prix, délai, public). Un tableau est le format le plus facilement repris par les IA pour une question de prix.
2. Schéma du fondateur : compétences ajoutées (développement WordPress, WooCommerce, webdesign, référencement, accessibilité numérique, identité visuelle, motion design), en français et en anglais.
3. Déjà livré en 3.2.0 : blocs « En bref » sur la page collectivités et « L'agence », maillage pages piliers vers guides.

## Les 5 changements au plus fort impact (à faire hors du site)
1. **Profil Malt** : nommer Aurea Media, mettre le lien du site et le même texte de présentation. Ensuite, ajouter son adresse dans le sameAs du fondateur (à me transmettre).
2. **Liens « site réalisé par Aurea Media »** dans le pied de page des sites clients (Dual Tech, INAS, Xpert Medical, etc.), avec leur accord : la mention tierce la plus naturelle et la plus crédible.
3. **Annuaires d'agences** : La Fabrique du Net, Sortlist, Codeur, Pages Jaunes, avec exactement le même nom, la même adresse et la même description.
4. **LinkedIn** : un post par article publié (pack 2 en décembre), avec le lien. Les publications LinkedIn sont indexées et associent le nom du fondateur à la marque.
5. **Une donnée originale** : par exemple « délai et prix moyen de nos 45 projets » ou l'évolution de la Search Console après la refonte, publiée en article. Les contenus avec des chiffres propres sont ceux que les IA citent en priorité.

## Recommandations de schéma
- Rien de bloquant. Le graphe est complet (organisation, fondateur, site, pages, services, fil d'Ariane, articles).
- Ajouter au sameAs de l'organisation et du fondateur chaque nouveau profil créé (Malt, annuaires), seulement une fois les profils à jour.
- Pas de FAQPage à ajouter : Google ne l'affiche plus en résultat enrichi depuis mai 2026.

## Reformulations suggérées
- Accueil : un seul H2 en question. Garder les titres actuels (ils servent la conversion) ; les questions sont déjà portées par le bloc « En bref » et la FAQ.
- Articles du pack 1 et 2 : déjà construits avec réponse directe en tête de section, tableaux et sources. À conserver comme standard.
- Mettre à jour la date et les chiffres du guide de prix chaque année (« en 2027 ») : la fraîcheur pèse fortement dans le mode IA.

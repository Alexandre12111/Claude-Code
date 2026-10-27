# Thème Aurea Media 1.0

Thème WordPress sur mesure, sans constructeur de pages. Le design est celui de la maquette (`aurea-media/maquette`), repris à l'identique.

Mise en place pas à pas : voir `wordpress/MISE-EN-PLACE.md`.

## Structure

| Fichier | Rôle |
|---|---|
| `functions.php` | Point d'entrée, liste des modules |
| `inc/gabarit.php` | En-tête et pied de page de la maquette, repères `{{url:…}}`, `{{theme}}`, adresses des pages |
| `inc/setup.php` | Réglages du thème, styles, scripts, polices, nettoyage de WordPress, commentaires désactivés |
| `inc/reglages.php` | Coordonnées (Personnaliser → Aurea Media) |
| `inc/shortcodes.php` | Blocs alimentés par Aurea Studio |
| `inc/contact.php` | Formulaire de devis : e-mail, copie dans « Messages reçus », anti spam |
| `inc/seo.php` | Rank Math (graphe complété), relais sans Rank Math, `llms.txt`, `robots.txt` |
| `inc/redirections.php` | 52 redirections 301 (dans Rank Math, sinon par le thème), anciens articles vers `/conseils/` |
| `inc/articles.php` | Compositions d'articles, sommaire, temps de lecture |
| `inc/installation.php` | Apparence → Installation Aurea |
| `inc/nettoyage.php` | Base propre : anciens articles et pages à la corbeille, chaque adresse redirigée vers la page la plus proche |
| `contenu/` | Pages de la maquette en blocs Gutenberg, `pages.json` (SEO de chaque page) |
| `parts/` | `entete.html`, `pied.html` (maquette), cartes et mise en page texte |

## Régénérer le contenu depuis la maquette

```
python3 wordpress/outils/exporter_theme.py <dossier des polices fontsource>
```

Le script réécrit `contenu/`, `parts/entete.html`, `parts/pied.html` et les ressources de `assets/` (sauf `wp.css` et `editeur.css`, propres à WordPress). Les pages déjà importées ne changent pas : relancez « Créer les pages » avec « Remplacer » pour les mettre à jour (révision conservée).

## Licences

Polices Bricolage Grotesque, Geist et Geist Mono : SIL Open Font License. Icônes Phosphor : MIT (`assets/LICENCE-phosphor-MIT.txt`).

## Version anglaise (Polylang)

1. Installez et activez l'extension gratuite **Polylang**.
2. Dans **Langues → Langues**, ajoutez **Français** (langue par défaut) puis **English** (en_GB conseillé).
3. Ouvrez n'importe quelle page de l'administration : le thème crée les 22 pages anglaises sous `/en/`,
   les relie à leurs pages françaises (balises hreflang) et règle l'accueil anglais sur `/en/`.

Le blog et les études de cas restent en français. Le sélecteur FR / EN apparaît dans l'en-tête dès que
l'anglais est actif. Les textes anglais se modifient comme les pages françaises ; pour les régénérer depuis
la maquette : `python3 wordpress/outils/exporter_theme.py` (la table de traduction est dans
`wordpress/traduction/`).

# BQP Recherche

Une barre de recherche à placer n'importe où (page, en-tête, pied de page, widget Elementor) avec le shortcode `[bqp_recherche]`.

Préfixe `bqr_` : aucun conflit avec les autres snippets BQP.

## Installation

1. Ouvrir `bqp-recherche-code-snippets.txt`
2. Copier tout le contenu dans un **nouveau** snippet
3. Régler sur **Run everywhere**, enregistrer et activer
4. Coller `[bqp_recherche]` dans un widget **Shortcode** d'Elementor

## Ce qu'elle fait

- Avec **BQP Bibliothèque** actif, elle cherche **dans la bibliothèque** : la recherche ouvre la page Bibliothèque sur le catalogue filtré (« nucléaire », 2 documents…)
- Sans la bibliothèque, ou avec `cible="site"`, elle utilise la recherche WordPress de tout le site
- **Suggestions pendant la frappe**, dès 2 lettres :
  - **Documents** : d'abord ceux dont le titre correspond, puis ceux des auteurs trouvés, puis ceux dont le résumé correspond ; avec nature, année et auteur
  - **Auteurs et organisations**, avec leur nombre de documents
  - **Thèmes et collections**, numérotés comme dans le schéma (« 1.1 Écrits de Jean-Michel Quatrepoint »)
  - le texte tapé est mis en valeur, accents compris
  - « Voir tous les résultats pour « … » » en bas
- **Clavier** : flèches pour parcourir, Entrée pour ouvrir, Échap pour fermer ; accessible aux lecteurs d'écran (combobox)
- Un envoi à vide est ignoré

## Options

| Attribut | Valeurs | Par défaut |
|---|---|---|
| `cible` | `bibliotheque` ou `site` | `bibliotheque` si BQP Bibliothèque est actif |
| `style` | `plein`, `compact` (en-tête, barre latérale), `sombre` (fond bordeaux ou image) | `plein` |
| `placeholder` | texte du champ | « Rechercher un document, un auteur, un thème… » |
| `bouton` | texte du bouton ; vide pour une simple loupe | `Rechercher` |
| `titre` | petit titre au-dessus | aucun |
| `largeur` | largeur maximale, par exemple `480px` ou `60%` | toute la largeur |
| `suggestions` | `non` pour les désactiver | `oui` |

Exemples :

```
[bqp_recherche]
[bqp_recherche style="compact" bouton="" largeur="360px"]
[bqp_recherche style="sombre" placeholder="Que cherchez-vous ?"]
[bqp_recherche cible="site" titre="Rechercher sur le site"]
```

## Technique

- Les styles sont écrits avec la première barre de la page : pas d'affichage sans style, même dans un en-tête Elementor ; ils sont protégés des couleurs du thème Hello
- Suggestions par `admin-ajax.php` (action `bqr_suggest`), en lecture seule, uniquement sur les contenus publiés ; les réponses sont mises en mémoire dans le navigateur, et une frappe rapide annule la requête précédente
- Requiert WordPress 6.2 ou plus (recherche par colonnes)

## Testé

Dans un WordPress 6.8 réel avec le thème Hello Elementor et BQP Bibliothèque 3.1 : trois styles, suggestions dans les deux modes, navigation au clavier, ouverture d'une suggestion avec Entrée, recherche vers le catalogue filtré, recherche WordPress (`?s=`), envoi à vide bloqué, mobile sans débordement, aucune erreur PHP ni JavaScript.

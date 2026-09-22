# BQP Formulaires

Créateur de formulaires de contact pour boursequatrepoint.fr. Chaque formulaire créé génère automatiquement son propre shortcode.

Indépendant des plugins **BQP Partenaires** et **BQP Gouvernance** : tous les noms sont préfixés `bqf_`, les trois snippets peuvent tourner ensemble.

## Installation

### Avec Code Snippets

1. Ouvrir `bqp-formulaires-code-snippets.txt`
2. Copier tout le contenu dans un nouveau snippet
3. Régler le snippet sur **Run everywhere**
4. Enregistrer et activer

Aucune balise `<?php` ni `?>`, aucun `register_activation_hook`.

### En extension classique

Compresser le dossier en `.zip`, puis Extensions → Ajouter → Téléverser.

À la première visite de l'administration, le formulaire **Manifester son intérêt** est créé automatiquement, prêt à l'emploi, avec ses six champs et l'adresse de réception `contact@boursequatrepoint.fr`.

## Créer un formulaire

1. **Formulaires → Créer un formulaire**. Le titre saisi s'affiche en haut du bloc.
2. **Réglages** : adresse de réception, objet de l'e-mail, introduction, libellé du bouton, message de confirmation, case de consentement facultative.
3. **Champs** : bouton *Ajouter un champ*, autant de fois que nécessaire. Chaque champ a un libellé, un type, une largeur (pleine ou demie) et peut être rendu obligatoire. Les flèches réordonnent, la croix supprime.
4. **Shortcode** : il apparaît dans l'encadré à droite, avec un bouton *Copier*. Il figure aussi dans la colonne *Shortcode* de la liste des formulaires.

### Types de champs disponibles

Texte court, E-mail, Téléphone, Site internet, Nombre, Texte long, Liste déroulante, Boutons radio, Case à cocher, Date.

Les listes déroulantes et les boutons radio font apparaître une zone **Choix proposés**, un par ligne.

## Options du shortcode

| Option | Rôle | Exemple |
|---|---|---|
| `id` | Le formulaire à afficher | `[bqp_formulaire id="12"]` |
| `titre` | Remplace ou masque le titre | `[bqp_formulaire id="12" titre="non"]` |
| `carte` | Carte blanche encadrée, oui ou non | `[bqp_formulaire id="12" carte="non"]` |
| `colonnes` | 1 ou 2 colonnes | `[bqp_formulaire id="12" colonnes="1"]` |

Sans `id`, le premier formulaire publié est affiché.

## Réception des messages

- L'e-mail part vers l'adresse réglée dans le formulaire, `contact@boursequatrepoint.fr` par défaut.
- L'expéditeur technique reste sur le domaine du site (`no-reply@boursequatrepoint.fr`) pour passer les filtres anti-spam ; l'adresse du visiteur est placée en **Reply-To**, donc répondre à l'e-mail répond directement à la personne.
- Chaque message est **aussi archivé** dans **Formulaires → Messages reçus**. Rien n'est perdu si un e-mail se perd en route.

Si les e-mails n'arrivent pas, la cause est presque toujours l'envoi côté hébergeur. Une extension SMTP (WP Mail SMTP, Fluent SMTP) règle le problème.

## Anti-spam

Deux protections sans captcha :

- un champ piège invisible qui doit rester vide ;
- un contrôle du temps de remplissage, un envoi en moins de 4 secondes est rejeté.

## Notes techniques

- Formulaires et messages ne génèrent aucune page publique.
- Envoi en POST puis redirection (schéma Post/Redirect/Get) : rafraîchir la page après envoi ne renvoie pas le message.
- Validation en deux temps, navigateur puis serveur, avec messages d'erreur en français.
- Les styles des champs sont en `!important` : ils résistent aux règles de formulaire du thème et d'Elementor.
- Le jeton de sécurité (nonce) a une durée de vie de 24 h. Si la page est servie par un cache très long, un visiteur peut voir « votre session a expiré » ; il suffit de recharger. Exclure du cache les pages contenant un formulaire évite ce cas.

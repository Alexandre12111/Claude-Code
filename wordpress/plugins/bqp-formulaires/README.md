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
3. **Champs** : bouton *Ajouter un champ*, autant de fois que nécessaire. Chaque champ a un libellé, un type, une largeur et peut être rendu obligatoire. Les flèches réordonnent, la croix supprime.
4. **Shortcode** : il apparaît dans l'encadré à droite, avec un bouton *Copier*. Il figure aussi dans la colonne *Shortcode* de la liste des formulaires.

### Plusieurs champs sur une même ligne

C'est le réglage **Largeur** de chaque champ :

| Largeur | Effet |
|---|---|
| Pleine largeur | 1 champ par ligne |
| Demie | 2 champs côte à côte |
| Tiers | 3 champs côte à côte |

Deux champs réglés sur *Demie* qui se suivent se placent automatiquement sur la même ligne, par exemple Nom et prénom à gauche et Adresse e-mail à droite. Trois champs en *Tiers* remplissent une ligne complète. Sur mobile, tout repasse sur une seule colonne.

### Types de champs disponibles

Texte court, E-mail, Téléphone, Site internet, Nombre, Texte long, Liste déroulante, Boutons radio, Case à cocher, Date, **Fichier à déposer**.

Les listes déroulantes et les boutons radio font apparaître une zone **Choix proposés**, un par ligne.

### Dépôt de fichier

Le type **Fichier à déposer** ajoute un bouton de sélection de fichier. La même zone sert alors à saisir les **extensions autorisées**, séparées par des virgules. Laissée vide, elle accepte : pdf, doc, docx, odt, rtf, txt, jpg, jpeg, png, webp.

Le poids maximum se règle dans les réglages du formulaire, 5 Mo par défaut.

Ce qui se passe à la réception :

- le fichier part **en pièce jointe** de l'e-mail ;
- il est aussi conservé avec le message archivé, téléchargeable depuis l'administration ;
- il est stocké sous un nom aléatoire dans `wp-content/uploads/bqp-formulaires/`, dossier fermé à l'accès direct par un `.htaccess`. Le téléchargement passe par un lien d'administration protégé par capacité et jeton ;
- supprimer un message supprime aussi ses pièces jointes.

Contrôles appliqués avant tout enregistrement : code d'erreur d'envoi, poids, extension (liste autorisée moins une liste noire d'exécutables toujours refusée : php, phtml, html, js, svg, exe, sh…), et correspondance réelle entre le contenu du fichier et son extension via `wp_check_filetype_and_ext`. Aucun fichier n'est déplacé tant qu'une autre erreur subsiste dans le formulaire, et les fichiers déjà déplacés sont supprimés si une erreur survient ensuite.

Si l'envoi dépasse la limite `post_max_size` du serveur, PHP vide la requête : un garde-fou détecte ce cas et affiche un message clair au lieu d'un échec silencieux.

## Formulaire à profils

Un formulaire peut proposer une barre de pastilles qui change les champs affichés, la phrase d'aide, le libellé du bouton et le destinataire. C'est ce qui reproduit la page Contact de la maquette.

Trois éléments :

1. **Un champ de type `Sélecteur de profil (pastilles)`.** Sa zone de liste contient les profils, un par ligne. Un seul par formulaire.
2. **Sur chaque autre champ, la zone `Afficher uniquement pour`.** Cochez les profils concernés. Aucune case cochée : le champ reste visible pour tout le monde.
3. **L'encadré `Profils et destinataires`.** Pour chaque profil : une adresse de réception, une phrase d'aide et un libellé de bouton. Laissés vides, ils reprennent les réglages généraux du formulaire.

Le formulaire **Contact** est créé automatiquement avec les sept profils Candidat, Adhérent, Donateur, Entreprise ou mécène, Partenaire, Presse et médias, Demande générale, et leurs champs spécifiques.

Points techniques :

- l'état initial est calculé côté serveur, le premier profil est déjà en place dans le HTML ;
- les champs masqués sont **désactivés**, pas seulement cachés : un champ obligatoire d'un autre profil ne bloque donc jamais l'envoi et n'est pas transmis ;
- côté serveur, les champs d'un autre profil ne sont ni exigés, ni validés, ni inclus dans l'e-mail ;
- l'objet de l'e-mail reçoit le profil en suffixe, et le profil est enregistré avec le message archivé.

## Options du shortcode

| Option | Rôle | Exemple |
|---|---|---|
| `id` | Le formulaire à afficher | `[bqp_formulaire id="12"]` |
| `titre` | Remplace ou masque le titre | `[bqp_formulaire id="12" titre="non"]` |
| `carte` | Carte blanche encadrée, oui ou non | `[bqp_formulaire id="12" carte="non"]` |
| `colonnes` | `1` force un champ par ligne, quelles que soient les largeurs réglées | `[bqp_formulaire id="12" colonnes="1"]` |

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

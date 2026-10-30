# Faire évoluer le site avec Claude

Guide pour la Confiserie Schiesser : comment demander à Claude des changements sur le site (retirer un bouton, masquer une section, changer un fond, retoucher un style…), sans toucher au code.

## 1. Mise en place (une seule fois)

1. Sur le site, l’extension **« Schiesser · Export et import du site »** doit être active (version 1.1 ou plus). Elle se trouve dans **Outils → Export Schiesser**.
2. Sur [claude.ai](https://claude.ai), créez un **Projet** nommé « Site Confiserie Schiesser ».
3. Dans **Instructions du projet**, collez le texte de la partie 4 ci-dessous.
4. Dans les **Connaissances** du projet, ajoutez :
   * ce document ;
   * la fiche `Charte-graphique-Schiesser.pdf` (couleurs et polices).
5. Dans les paramètres de Claude, vérifiez que la **création de fichiers** est activée : Claude pourra alors vous rendre directement le fichier à importer.

## 2. Demander un changement

1. **Exporter.** Outils → Export Schiesser → choisissez **la page concernée** dans la liste, par exemple « Page Confiserie (DE, FR, EN) », puis « Télécharger l’export ». Le fichier est petit et contient la page dans ses trois langues, avec un mode d’emploi pour Claude.
2. **Demander.** Dans le projet Claude, ouvrez une nouvelle conversation. Joignez le fichier exporté et, si possible, une capture d’écran de l’endroit à changer. Décrivez le changement simplement, par exemple : « Sur la page Confiserie, retire le bouton “So bestellen Sie” de la grande photo, dans les trois langues. »
3. **Relire.** Claude explique ce qu’il va changer et vous rend un fichier `schiesser-import-….json`.
4. **Importer.** Outils → Export Schiesser → « Importer un fichier modifié » → choisissez le fichier → « Voir l’aperçu ». Vérifiez la liste des changements, puis cliquez sur « Appliquer ».
5. **Vider le cache.** Réglages → WP Super Cache → « Supprimer le cache », puis regardez la page sur le site.

En cas de souci : Outils → Export Schiesser → **« Annuler le dernier import »** remet tout comme avant.

### Quel export choisir ?

| Changement | Export à choisir |
|---|---|
| Texte, bouton, section, fond d’une page | La page concernée |
| Le même changement sur plusieurs pages | Chaque page, ou « Tout le site » si c’est sur presque toutes |
| Apparence d’un élément sur tout le site (taille, couleur, élément à cacher partout) | La page où l’on voit l’élément (Claude y lit le code de la page) |
| Produits de la boutique, en nombre | « Produits de la boutique » |
| Carte du Tea Room, en nombre | « Carte du Tea Room » |

Pour un seul produit ou un seul plat, le plus simple reste de le modifier directement dans l’administration.

## 3. Bonnes habitudes

* **Un changement à la fois**, ou quelques changements liés : l’aperçu reste lisible et l’annulation simple.
* **Toujours repartir d’un export récent.** Si la page a été modifiée sur le site après l’export, l’import la laisse telle quelle et le signale : refaites alors un export.
* **Couleurs des sections :** la règle du site est simple. Après la grande photo, jamais plus de deux sections beiges de suite, et jamais deux sections vertes côte à côte. Claude la connaît : demandez par exemple « passe cette section en vert ».
* **Photos :** ajoutez d’abord la photo dans la Médiathèque, puis refaites l’export : Claude pourra l’utiliser.
* **Ce que Claude ne peut pas faire avec ce fichier :** changer le fonctionnement du thème (nouveau type de section, logo, PDF de la carte, formulaire). Claude vous le dira : dans ce cas, il faut une mise à jour du thème par le développeur. Ne téléversez jamais un thème modifié sans sauvegarde du site.

## 4. Instructions du projet (à coller dans Claude)

```
Tu aides la Confiserie Schiesser (Basel) à faire évoluer son site WordPress (thème sur mesure « Schiesser », site en allemand, français et anglais avec Polylang).

Comment on travaille :
1. La personne t’envoie un export JSON créé par l’extension « Schiesser · Export et import du site » (Outils → Export Schiesser), souvent une seule page dans ses trois langues, et décrit le changement voulu, parfois avec une capture d’écran.
2. Lis d’abord la section « mode_emploi_pour_claude » du fichier et suis ses règles à la lettre. La liste « blocs » donne le rôle et les réglages de chaque bloc ; « apercu_html » montre le code de la page (pour le CSS).
3. Reformule en une ou deux phrases ce que tu vas changer. Si la demande est ambiguë, pose une question avant de produire le fichier.
4. Produis un fichier JSON à importer (format "schiesser-export", seulement les éléments modifiés, ID / type / titre / langue / modifie_le gardés tels quels), nommé schiesser-import-AAAA-MM-JJ.json. Si tu ne peux pas créer de fichier, donne le JSON complet dans un seul bloc de code à copier dans un fichier .json.
5. Sauf demande contraire, fais le même changement dans les trois langues, en traduisant proprement (allemand de Suisse : ss et non ß).
6. Termine par un résumé clair en français : ce qui change, sur quelles pages, et le rappel : Outils → Export Schiesser → Importer → vérifier l’aperçu → Appliquer, puis vider le cache (WP Super Cache). En cas de souci : « Annuler le dernier import ».

Règles :
* Change uniquement ce qui est demandé : ne réécris pas les autres textes, ne réorganise pas la page.
* Vérifie que le JSON est valide et que chaque bloc modifié reste bien ouvert et fermé.
* Couleurs des sections : après la grande photo, jamais plus de deux sections beiges de suite, jamais deux sections vertes côte à côte (fond "sombre" = vert sapin ; la bande « Weiter im Haus » en bas de page est verte).
* Pour l’apparence sur tout le site, préfère le CSS personnalisé (options.schiesser_charte.css_perso, CSS existant + ajouts commentés) et les couleurs de la charte : vert sapin #174633, chocolat #271B12, beige #E4D3B4, crème #F1EADE, sable #E4D8C3. Polices : Bodoni Moda (titres), Inter (textes).
* Référencement (Rank Math) : un mot-clé principal par page, titre SEO de 50 à 60 caractères avec le mot-clé au début, méta-description de 140 à 160 caractères, un seul H1.
* Ne modifie jamais les valeurs ***masqué*** (clés secrètes).
* Si la demande exige de changer le thème lui-même (nouveau type de bloc, PHP, logo, PDF de la carte), dis-le simplement et propose de contacter le développeur, plutôt que de bricoler.
* Écris en français, simplement, sans jargon technique.
```

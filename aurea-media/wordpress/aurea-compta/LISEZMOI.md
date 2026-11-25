# Aurea Compta pour WordPress

La comptabilité de la SASU Aurea Media, directement dans votre site WordPress hébergé chez Ionos. Rien à installer sur l'ordinateur : elle s'ouvre depuis n'importe quel navigateur, sur ordinateur comme sur téléphone, à l'adresse **aurea-media.fr/compta**, avec votre compte administrateur WordPress.

## Les fichiers

| Fichier | Rôle |
|---|---|
| `aurea-compta.zip` | L'application (obligatoire, environ 150 Ko). |
| `aurea-compta-ia.zip` | Complément facultatif pour l'IA (lecture automatique des justificatifs, assistant). Contient le SDK officiel d'Anthropic (environ 6 Mo). |

## Installation (5 minutes)

0. **Sauvegardez le site** (sauvegarde Ionos ou UpdraftPlus), comme avant toute nouvelle extension.
1. Vérifiez la version de PHP : **Ionos > Hébergement > PHP**, choisissez **PHP 8.1 ou plus récent** (8.3 recommandé).
2. WordPress : **Extensions > Ajouter > Téléverser une extension**, choisissez `aurea-compta.zip`, installez, activez.
3. Facultatif, pour l'IA : même chose avec `aurea-compta-ia.zip`. Si WordPress affiche l'erreur « 413 Request Entity Too Large », passez par **Outils > Installer Aurea** (extension Aurea Installateur, qui envoie le fichier par morceaux).
4. Un menu **Aurea Compta** apparaît dans l'administration. L'application s'ouvre en plein écran.

## Premier démarrage

1. **Paramètres > Société** : SIREN, capital, nom de l'associé unique tel qu'il apparaît sur vos virements (sert à reconnaître vos apports et remboursements).
2. **Paramètres > Fiscalité** : régime de TVA, périodicité, option débits, régime d'IS. Tant que ce n'est pas confirmé, un bandeau signale des valeurs provisoires.
3. **Paramètres > Exercices** : dates de l'exercice (le premier peut durer plus de 12 mois selon vos statuts).
4. **Paramètres > Qonto** : identifiant et clé API (Qonto > Paramètres > Intégrations et partenariats > Clé API), cochez la synchronisation automatique, puis « Importer tout l'historique ».
5. **Paramètres > IA** (facultatif) : clé API Anthropic, puis cochez l'activation.

## Sur le téléphone

Ouvrez **aurea-media.fr/compta**, connectez vous, puis ajoutez la page à l'écran d'accueil (iPhone : Partager, « Sur l'écran d'accueil » ; Android : menu ⋮, « Ajouter à l'écran d'accueil »). Elle s'ouvre comme une application, avec toutes vos données et vos justificatifs, et permet de photographier un ticket.

## Sécurité

* Seuls les administrateurs WordPress accèdent à l'application et à son API.
* Activez la **double authentification** sur votre compte administrateur (extension gratuite « Two Factor » par exemple) et utilisez un mot de passe fort : ce compte ouvre désormais votre comptabilité.
* Les clés Qonto et Anthropic sont **chiffrées** avec les clés secrètes de votre WordPress, jamais affichées ni exportées.
* Les justificatifs sont rangés dans un dossier privé au nom aléatoire, interdit d'accès direct depuis le web ; ils ne sont servis qu'aux administrateurs connectés.
* Les écritures validées sont chaînées par empreinte : toute modification faite directement dans la base est détectée et bloque la clôture.

## Sauvegardes et conservation

* Sauvegarde automatique quotidienne des données (30 dernières conservées, dossier privé).
* **Clôture et exports > Exporter toutes les données** : archive complète (données et justificatifs) à copier hors du site, et restauration au même endroit.
* Désinstaller l'extension ne supprime rien : les livres et pièces doivent être conservés 10 ans.

## Synchronisation Qonto

Automatique toutes les heures (tâche planifiée WordPress) et à chaque ouverture de l'application, plus le bouton « Synchroniser Qonto ». Sur un site peu visité, les tâches planifiées de WordPress ne se déclenchent qu'à la visite suivante : ce n'est pas gênant, l'ouverture de l'application synchronise aussi.

## Tests (développement)

Sur un WordPress de test : `WP_LOAD=/chemin/wp-load.php php tests/run.php`. Les tests utilisent des tables séparées (préfixe `actest_`), supprimées à la fin : ils ne touchent jamais les données réelles. Avec `AC_MOCK=1` et `php -S 127.0.0.1:8099 tests/mock-server.php`, les intégrations Qonto et IA sont testées contre des serveurs simulés.

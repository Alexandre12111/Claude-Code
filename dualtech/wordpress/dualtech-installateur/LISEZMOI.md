# DualTech Installateur 1.0.0

Petite extension qui installe le thème DualTech et l'extension DualTech Studio quand l'hébergeur refuse les gros fichiers (erreur « 413 Request Entity Too Large »).

* Outils → Installer DualTech : choisir ou déposer `dualtech.zip` et `dualtech-studio.zip`.
* Les fichiers partent par morceaux de 512 Ko ; si l'hébergeur refuse encore, les morceaux sont réduits automatiquement (jusqu'à 32 Ko).
* WordPress installe ensuite l'archive comme un thème ou une extension (détection automatique) ; une version déjà installée est remplacée.
* Fichiers temporaires dans `wp-content/uploads/dualtech-installateur/` (dossier protégé), supprimés après l'installation ou au bout d'un jour.
* Droits requis : installer des thèmes ou des extensions. Peut être supprimée après usage.

# Aurea Installateur 1.0.1

* 1.0.1 : les boutons « Activer » activent l’extension et le thème sur place, sans quitter l’écran (la version 1.0 envoyait vers un lien mal signé : « Le lien suivi est expiré »).

Petite extension qui installe le thème Aurea Media et l'extension Aurea Studio quand l'hébergeur refuse les gros fichiers (erreur « 413 Request Entity Too Large » de nginx).

* Outils → Installer Aurea : choisir ou déposer `aurea.zip` et `aurea-studio.zip`.
* Les fichiers partent par morceaux de 512 Ko ; si l'hébergeur refuse encore, les morceaux sont réduits automatiquement (jusqu'à 32 Ko).
* WordPress installe ensuite l'archive comme un thème ou une extension (détection automatique) ; une version déjà installée est remplacée.
* Fichiers temporaires dans `wp-content/uploads/aurea-installateur/` (dossier protégé), supprimés après l'installation ou au bout d'un jour.
* Droits requis : installer des thèmes ou des extensions. Peut être supprimée après usage.

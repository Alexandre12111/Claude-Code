# Mise en place du site DualTech sur WordPress

Trois fichiers à envoyer dans WordPress :

| Fichier | Rôle |
|---|---|
| `dualtech.zip` | Le thème : design de la maquette à l'identique, 11 pages, SEO |
| `dualtech-studio.zip` | L'extension : menu **DualTech** à gauche (partenaires, intervenants, programme, demandes reçues) |
| `dualtech-installateur.zip` | Facultatif : envoie les deux fichiers ci‑dessus par petits morceaux si l'hébergeur refuse les gros fichiers (erreur 413) |

## 1. Avant de commencer

1. Faire une **sauvegarde complète** du site actuel (fichiers et base de données, par exemple avec UpdraftPlus ou la sauvegarde de l'hébergeur).
2. Idéalement, tester d'abord sur une copie du site (préproduction).

## 2. Installer l'extension et le thème

**Méthode simple** : Extensions → Ajouter → Téléverser → `dualtech-studio.zip` → Activer. Puis Apparence → Thèmes → Ajouter → Téléverser → `dualtech.zip` → Activer.

**Si l'hébergeur bloque l'envoi** (fichier trop lourd) : téléverser d'abord `dualtech-installateur.zip` (8 Ko) comme une extension et l'activer, puis Outils → **Installer DualTech** : déposer `dualtech.zip` et `dualtech-studio.zip`, cliquer sur « Activer » sous chacun.

## 3. Lancer l'installation (un clic)

À l'activation du thème, WordPress ouvre **Apparence → Installation DualTech**. Cliquer sur **Lancer l'installation** (environ 30 secondes) :

1. 36 partenaires, 34 intervenants et 25 séances du programme sont copiés dans le menu DualTech (logos et photos dans la médiathèque).
2. Les 11 pages de la maquette sont créées. Une page qui existe déjà avec la même adresse (par exemple `/programme-2026/`) est **remplacée** : l'ancienne version reste disponible dans ses révisions, et la mise en page Elementor de cette page est retirée.
3. Réglages : page d'accueil, adresses en `/nom-de-la-page/`, fuseau Europe/Paris, commentaires fermés.
4. Rank Math : titre SEO, meta description, mot clé principal et image de partage de chaque page.

L'installation peut être relancée sans risque : rien n'est créé en double, et ce qui a été modifié dans le menu DualTech est conservé.

## 4. Rank Math SEO

Installer **Rank Math SEO** (gratuit) avant l'étape 3, ou relancer l'installation après. Réglages appliqués automatiquement pour chaque page :

| Page | Mot clé principal |
|---|---|
| Accueil | salon industrie duale |
| L'événement | événement DualTech |
| Programme 2026 | programme DualTech 2026 |
| Intervenants 2026 | intervenants DualTech |
| Ateliers et workshops | workshop DualTech |
| Devenir partenaire | partenaire DualTech |
| Prix de l'innovation duale | prix de l'innovation duale |
| Infos pratiques | DualTech Campus Cyber |
| Rétrospective | rétrospective DualTech |
| Contact | contact DualTech |
| Inscription | inscription DualTech 2026 |

Titres d'environ 50 à 60 caractères, descriptions d'environ 140 à 160 caractères, un seul H1 par page. Les données structurées (événement, lieu, organisateurs, programme, intervenants) sont fournies par le thème et se mettent à jour toutes seules quand le menu DualTech change ; le schéma de Rank Math est coupé sur ces pages pour éviter les doublons. Visez un score de 80 ou plus, inutile de chercher 100.

## 5. Utiliser le menu DualTech (à gauche)

| Écran | Ce qu'on y fait |
|---|---|
| **Partenaires** | Ajouter un partenaire : nom, logo (image mise en avant), description affichée au clic, lien du site, rubrique(s) à cocher, ordre. |
| **— Rubriques** | Renommer les rubriques (Institutions publiques, Financement et marchés…). |
| **Intervenants** | Nom, fonction, organisation, catégorie (innovation, institutions, industrie, financement, recherche), photo carrée. L'ordre d'affichage se règle avec le champ « Ordre ». |
| **Programme** | Une séance par entrée : heure de début et de fin, format, intervenants et modérateurs choisis dans la liste, case « Temps fort » pour l'afficher sur la page L'événement. Les séances se rangent seules par heure. |
| **Demandes reçues** | Toutes les demandes des formulaires (contact, partenariat, workshops), même si l'e‑mail ne part pas. Pastille rouge = nouvelles demandes. Statut (nouvelle, en cours, traitée), note interne, bouton « Répondre par e‑mail », export CSV pour Excel. |
| **Réglages** | Adresse qui reçoit les notifications, bouton d'e‑mail de test. |

Toute modification apparaît immédiatement sur le site (accueil, programme, intervenants, partenaires, temps forts), avec le même design.

## 6. Recevoir les demandes par e‑mail

Les demandes sont **toujours enregistrées** dans DualTech → Demandes reçues. Pour recevoir aussi un e‑mail :

1. Installer **WP Mail SMTP** (gratuit) et le relier à la boîte d'envoi (Microsoft 365, Gmail, Brevo…).
2. DualTech → Réglages : saisir l'adresse de réception, cliquer sur « Envoyer un e‑mail de test ».

## 7. Après la mise en ligne

* **Elementor** n'est plus utilisé par ces pages : il peut être désactivé une fois le site vérifié (s'il ne sert pas ailleurs).
* Ajouter les pages **Mentions légales** et **Politique de confidentialité** (les formulaires collectent des données personnelles) : une page créée dans WordPress prend automatiquement la mise en page « texte » du site.
* Vider le cache (extension de cache, Cloudflare) puis vérifier les pages sur mobile.
* Search Console : soumettre `https://dualtech-event.eu/sitemap_index.xml`.
* L'extension DualTech Installateur peut être supprimée.

## Modifier les textes des pages

Les textes des pages (titres, paragraphes, FAQ) sont dans Pages → la page → bloc « HTML personnalisé ». Le menu, le pied de page et les blocs dynamiques (intervenants, programme, partenaires) se mettent à jour seuls. Pour une refonte plus importante, le thème se régénère depuis la maquette avec `outils/exporter_theme.py`.

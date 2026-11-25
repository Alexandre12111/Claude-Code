# Référence du site dualtech-event.eu

## Pages

| Page | Adresse | Mot clé Rank Math | Contenu dynamique (codes courts) |
|---|---|---|---|
| Accueil | `/` | industrie duale | `[dualtech_orateurs nombre="12"]` (12 premiers intervenants), `[dualtech_nombre_intervenants]` |
| L'événement | `/l-evenement/` | événement DualTech | `[dualtech_temps_forts]`, `[dualtech_temps_forts partie="regle"]`, `[dualtech_temps_forts partie="horloge"]`, `[dualtech_logos_defile]` |
| Programme 2026 | `/programme-2026/` | programme DualTech 2026 | `[dualtech_programme partie="filtres"]`, `[dualtech_programme]` |
| Intervenants 2026 | `/intervenants-2026/` | intervenants DualTech | `[dualtech_intervenants partie="mosaique"]`, `[dualtech_intervenants partie="filtres"]`, `[dualtech_intervenants]`, `[dualtech_nombre_intervenants]` |
| Ateliers & Workshops | `/ateliers-workshops/` | workshop DualTech | `[dualtech_formulaire type="workshop"]` |
| Devenir partenaire | `/devenir-partenaire/` | partenaire DualTech | `[dualtech_partenaires]`, `[dualtech_formulaire type="partenariat"]` |
| Prix de l'innovation duale | `/prix-de-linnovation-duale/` | prix de l'innovation duale | `[dualtech_logos rubrique="prix" libelle="…"]` |
| Infos pratiques | `/infos-pratiques/` | DualTech Campus Cyber | aucun |
| Rétrospective février 2026 | `/retrospective-fevrier-2026/` | rétrospective DualTech | `[dualtech_partenaires_desc rubrique="edition-2025"]` |
| Contact | `/contact/` | contact DualTech | `[dualtech_formulaire type="contact"]` |
| Inscription | `/inscription/` | inscription DualTech 2026 | aucun (inscription sur Luma : https://luma.com/wr8t4i35) |
| Mentions légales | `/mentions-legales/` | mentions légales DualTech | aucun |
| Politique de confidentialité | `/politique-de-confidentialite/` | politique de confidentialité DualTech | aucun |

Les formulaires commencent par `<form class="form form--2" id="formulaire-…" data-formulaire="…" action="…/wp-admin/admin-post.php" method="post">` suivi du code court `[dualtech_formulaire type="…"]`. On peut changer les textes des libellés (`<label>`), jamais les attributs `name`, `action`, ni le code court.

## Informations de référence

* Date : jeudi 26 novembre 2026, de 8h30 à 19h00. Lieu : Campus Cyber, 5-7 rue Bellini, 92800 Puteaux (La Défense). Accès : RER A, Transilien L, métro 1, tramway T2, station La Défense Grande Arche.
* 400 places, inscription gratuite sur demande validée par l'organisation, pièce d'identité à l'entrée.
* Éditeur : Institut National des Affaires Stratégiques (INAS), association loi 1901, 60 route des Gardes, 92190 Meudon, SIREN 924 691 637. Contact : contact@dualtech-event.eu. LinkedIn : https://www.linkedin.com/company/dualtech-event/
* Première édition : 5 février 2026 au Palais du Luxembourg (Sénat).

## Charte

* Couleurs : bleu nuit `#041226` (fonds sombres), bleu `#1B5FC1` (boutons, liens), bleu clair `#49A7FF` (mots mis en avant sur fond sombre), rouge `#DF2229` (petits repères), texte `#131A26`, gris `#5B6779`.
* Polices : Archivo (titres), Public Sans (textes). Elles sont chargées par le thème : ne jamais en ajouter.
* Typographie française : espace avant `:` `;` `?` `!` et à l'intérieur des guillemets « … ». Heures écrites `9h30`, dates `26 novembre 2026`.

## Blocs HTML réutilisables

Toujours copier un bloc qui existe déjà dans la page et le modifier. À défaut, utiliser ces modèles.

### Section avec titre
Fonds possibles : `sec--blanc`, `sec--papier` (gris très clair), `sec--marine` et `sec--nuit` (sombres). Alterner clair et sombre d'une section à l'autre.
```html
<section class="sec sec--blanc" aria-labelledby="titre-ma-section">
  <div class="wrap">
    <div class="tete-sec">
      <p class="surtitre">Petit titre au-dessus</p>
      <h2 class="h2 lignes" id="titre-ma-section"><span class="l"><span style="--i:0">Titre de la section</span></span></h2>
      <p class="lead">Phrase d'introduction de la section.</p>
    </div>
    <p>Texte de la section.</p>
  </div>
</section>
```
Pour un titre sur deux lignes : deux `<span class="l"><span style="--i:0">…</span></span>`, le second avec `--i:1`. Chaque `id` doit être unique dans la page.

### Bouton et lien
```html
<a class="btn btn--primaire" href="https://dualtech-event.eu/inscription/">S'inscrire</a>
<a class="btn btn--ghost" href="https://dualtech-event.eu/programme-2026/">Voir le programme</a>
<a class="lien-fleche" href="https://dualtech-event.eu/contact/">Nous contacter</a>
```
Plusieurs boutons côte à côte : les entourer de `<div class="actions">…</div>`. Un lien vers un autre site : ajouter `target="_blank" rel="noopener"`.

### Question de la FAQ
À ajouter à l'intérieur du bloc `<div class="faq" data-reveal>` existant :
```html
<details><summary>La question ?<span class="plus" aria-hidden="true"></span></summary><div class="rep"><p>La réponse, en deux ou trois phrases.</p></div></details>
```

### Carte (petit encadré)
```html
<article class="carte-b projecteur"><span class="etiq">Étiquette</span><h3 class="h3">Titre de la carte</h3><p>Texte court.</p></article>
```
Plusieurs cartes : les entourer de `<div class="cartes">…</div>`.

### Image
```html
<img src="ADRESSE-DE-L-IMAGE-DANS-LA-MEDIATHEQUE" alt="Description de l'image en une phrase" width="1600" height="900" loading="lazy">
```
Images en WebP ou JPG, 1 600 px de large au maximum, moins de 200 Ko. Toujours `width` et `height` (dimensions réelles) et un `alt` descriptif.

## Données gérées dans le menu DualTech (ne pas écrire dans les pages)

Partenaires (nom, logo, description, lien, rubriques, ordre), intervenants (nom, fonction, organisation, catégorie, photo, ordre), séances (horaires, format, intervenants, modérateurs, temps fort), demandes reçues. Si la personne veut changer l'un de ces éléments, toujours passer par le menu **DualTech**, jamais par le code de la page.

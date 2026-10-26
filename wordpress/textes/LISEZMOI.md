# Textes du site à faire relire par le client

* **Textes-pages-Confiserie-Schiesser.docx** (document envoyé au client) : les textes des 8 pages en allemand, français et anglais. Le client corrige directement dans la case de la langue (fond crème) ; chaque case est comparée au texte d'origine au retour.
* **Textes-site-Confiserie-Schiesser.docx** : version complète (pages, carte du Tea Room, produits) en allemand et en français. Chaque ligne a une référence (A-001 accueil, C confiserie, T Tea Room, H histoire, V visite, K contact, F cadeaux d'entreprise, I mentions légales, M carte, P produits).
* **textes-references.json** : pour chaque référence, la page, l'emplacement du bloc (chemin), le réglage du bloc et les textes allemand, français et anglais d'origine.

## Au retour du document

```
python3 -I outils/lire-modifications.py document-rempli.docx textes-references.json
```

Liste chaque texte modifié (référence, langue, ancien et nouveau texte, langues restées inchangées « à adapter ») et écrit `document-rempli-modifications.json` ; les « Autres demandes » sont listées à part. L'italique saisi dans Word devient `<em>…</em>`, un retour à la ligne `<br>`. Un document non modifié ne donne aucun changement (vérifié, aussi après un enregistrement par LibreOffice).

## Régénérer le document (après une mise à jour des textes du thème)

```
php outils/extraire.php textes-references.json      # avec le site de test WordPress chargé (chemin dans le script)
npm install docx@9 && node outils/generer.js textes-references.json Textes-pages-Confiserie-Schiesser.docx          # pages, DE · FR · EN
node outils/generer.js textes-references.json Textes-site-Confiserie-Schiesser.docx --tout   # avec carte et produits
```

## Retours du client

* **2026-10 · document v3** (`retours/2026-10-v3/`) : 215 textes et 36 commentaires. Corrections construites par `outils/construire-corrections.py` (décisions sur les commentaires écrites dans le script) puis appliquées par le thème 0.18 (Réglages maison → Mises à jour des pages depuis la 0.19). `textes-references.json` contient désormais les textes corrigés (v4) ; `textes-references-v3.json` garde la version envoyée au client. `Textes-pages-Confiserie-Schiesser.docx` est régénéré avec les textes à jour pour une prochaine relecture.

```
python3 -I outils/lire-modifications.py rendu.docx textes-references-v3.json
python3 -I outils/construire-corrections.py rendu-modifications.json textes-references-v3.json ../schiesser/corrections/textes-AAAA-MM-vN.json seo-ancien.json
```

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

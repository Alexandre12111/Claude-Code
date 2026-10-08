# Textes du site à faire relire par le client

* **Textes-site-Confiserie-Schiesser.docx** : tous les textes du site (8 pages, carte du Tea Room, produits de la boutique), en allemand et en français, avec une colonne « Modification » à remplir. Chaque ligne a une référence (A-001 accueil, C confiserie, T Tea Room, H histoire, V visite, K contact, F cadeaux d'entreprise, I mentions légales, M carte, P produits).
* **textes-references.json** : pour chaque référence, la page, l'emplacement du bloc (chemin), le réglage du bloc et les textes allemand et français d'origine.

## Au retour du document

```
python3 -I outils/lire-modifications.py document-rempli.docx textes-references.json
```

Liste chaque modification (référence, texte actuel, demande) et écrit `document-rempli-modifications.json` ; les « Autres demandes » sont listées à part. L'italique saisi dans Word devient `<em>…</em>`.

## Régénérer le document (après une mise à jour des textes du thème)

```
php outils/extraire.php textes-references.json      # avec le site de test WordPress chargé (chemin dans le script)
npm install docx@9 && node outils/generer.js textes-references.json Textes-site-Confiserie-Schiesser.docx
```

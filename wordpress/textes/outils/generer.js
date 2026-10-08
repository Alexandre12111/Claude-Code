// Document Word des textes du site Confiserie Schiesser, à faire relire et modifier par le client.
// Usage : node generer.js textes.json sortie.docx [--tout]
// Par défaut : textes des pages en allemand, français et anglais. --tout ajoute la carte du Tea Room et les produits.
const fs = require('fs');
const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell, WidthType, ShadingType,
  AlignmentType, HeadingLevel, PageOrientation, Footer, Header, PageNumber, BorderStyle,
  LevelFormat, PageBreak, VerticalAlign, TableLayoutType,
} = require('docx');

const data = JSON.parse(fs.readFileSync(process.argv[2], 'utf8'));
const TOUT = process.argv.includes('--tout');
const SAPIN = '174633', CHOCO = '3B2A20', GRIS = '6B625B', CREME = 'FBF6EC', LIGNE = 'D9CFC2', BEIGE = 'F3EDE4';
const POLICE = 'Arial';

/* Page A4 paysage : contenu de 15 398 DXA (marges de 1,27 cm). */
const LARGEUR = 16838 - 2 * 720;

/* ---------- texte : HTML simple → morceaux Word (italique, gras, retours à la ligne) ---------- */
function decoder(s) {
  return s.replace(/&nbsp;|&#160;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"').replace(/&#0?39;|&#8217;|&rsquo;/g, '’').replace(/&#8211;|&ndash;/g, '–')
    .replace(/&#(\d+);/g, (m, n) => String.fromCodePoint(+n));
}
function runs(html, base = {}) {
  const out = [];
  let italique = false, gras = false, lien = false;
  const morceaux = String(html || '').split(/(<[^>]+>)/);
  for (const m of morceaux) {
    if (!m) continue;
    if (m[0] === '<') {
      const t = m.toLowerCase();
      if (/^<(em|i)\b/.test(t)) italique = true;
      else if (/^<\/(em|i)>/.test(t)) italique = false;
      else if (/^<(strong|b)\b/.test(t)) gras = true;
      else if (/^<\/(strong|b)>/.test(t)) gras = false;
      else if (/^<a\b/.test(t)) lien = true;
      else if (/^<\/a>/.test(t)) lien = false;
      else if (/^<br/.test(t)) out.push(new TextRun({ break: 1, font: POLICE, size: base.size || 18 }));
      continue;
    }
    out.push(new TextRun({ text: decoder(m), font: POLICE, size: base.size || 18, color: base.color || CHOCO,
      italics: italique || !!base.italics, bold: gras || !!base.bold, underline: lien ? {} : undefined }));
  }
  return out.length ? out : [new TextRun({ text: '', font: POLICE, size: base.size || 18 })];
}
function brut(html) { return decoder(String(html || '').replace(/<br\s*\/?>/gi, ' ').replace(/<[^>]+>/g, '')).replace(/\s+/g, ' ').trim(); }
const p = (enfants, opts = {}) => new Paragraph({ children: Array.isArray(enfants) ? enfants : [enfants], spacing: { before: 0, after: 40 }, ...opts });
const t = (texte, o = {}) => new TextRun({ text: texte, font: POLICE, size: o.size || 20, color: o.color || CHOCO, bold: o.bold, italics: o.italics });

/* ---------- tableaux ---------- */
const bord = { style: BorderStyle.SINGLE, size: 4, color: LIGNE };
const bords = { top: bord, bottom: bord, left: bord, right: bord };
function cellule(paragraphes, largeur, opts = {}) {
  return new TableCell({
    children: paragraphes, width: { size: largeur, type: WidthType.DXA }, borders: bords,
    shading: opts.fond ? { fill: opts.fond, type: ShadingType.CLEAR, color: 'auto' } : undefined,
    margins: { top: 70, bottom: 70, left: 100, right: 100 }, verticalAlign: VerticalAlign.TOP,
  });
}
// editable : true = dernière colonne à remplir ; tableau d'indices = colonnes modifiables ; false = aucune.
function tableau(colonnes, titres, lignes, editable = true) {
  const modifiable = i => Array.isArray(editable) ? editable.includes(i) : (editable && i === titres.length - 1);
  const entete = new TableRow({
    tableHeader: true, cantSplit: true,
    children: titres.map((x, i) => cellule([p(t(x, { bold: true, color: 'FFFFFF', size: 18 }))], colonnes[i], { fond: modifiable(i) ? '2E5E49' : SAPIN })),
  });
  return new Table({
    width: { size: LARGEUR, type: WidthType.DXA }, columnWidths: colonnes, layout: TableLayoutType.FIXED,
    rows: [entete, ...lignes.map(cs => new TableRow({ cantSplit: true, children: cs.map((c, i) => cellule(c, colonnes[i], { fond: modifiable(i) ? CREME : undefined })) }))],
  });
}
const ref = r => [p(t(r, { bold: true, size: 17, color: SAPIN }))];
const vide = () => [p(t('', { size: 18 }))];

/* ---------- contenu ---------- */
const enfants = [];
const titreSection = (texte, niveau) => new Paragraph({ heading: niveau, children: [t(texte, { size: niveau === HeadingLevel.HEADING_1 ? 32 : 22, bold: true, color: SAPIN })], spacing: { before: niveau === HeadingLevel.HEADING_1 ? 0 : 280, after: 120 }, keepNext: true });

// Page de garde
enfants.push(
  new Paragraph({ spacing: { before: 1400, after: 120 }, children: [t('CONFISERIE SCHIESSER · BASEL', { size: 20, bold: true, color: SAPIN })] }),
  new Paragraph({ spacing: { after: 120 }, children: [t(TOUT ? 'Textes du site internet' : 'Textes des pages du site', { size: 56, bold: true })] }),
  new Paragraph({ spacing: { after: 600 }, children: [t('Texte der Website · relecture et modifications', { size: 28, color: GRIS })] }),
);
const consignes = TOUT ? [
  ['Mode d’emploi', [
    'Écrivez le nouveau texte dans la colonne de droite « Modification ». Si le texte actuel convient, laissez la case vide.',
    'Vous pouvez écrire en allemand ou en français : nous adaptons ensuite le texte dans les trois langues du site.',
    'Ne supprimez aucune ligne et gardez la colonne « Réf. » : elle nous permet de retrouver chaque texte sur le site.',
    'Pour une remarque qui n’est pas un texte (photo à changer, section à retirer…), écrivez dans la dernière partie « Autres demandes ».',
  ]],
] : [
  ['Mode d’emploi', [
    'Corrigez le texte directement dans la case de la langue concernée (colonnes « Deutsch », « Français », « English », sur fond crème). Ce qui est écrit dans chaque case est ce qui s’affichera sur le site dans cette langue.',
    'Si un texte convient, ne touchez à rien. Si vous ne corrigez qu’une langue, nous vous signalerons les traductions à adapter dans les autres.',
    'Ne supprimez aucune ligne et ne modifiez pas les colonnes « Réf. » et « Élément » : elles nous permettent de retrouver chaque texte sur le site.',
    'Les mots en italique s’affichent aussi en italique sur le site (par exemple dans les grands titres). Pour mettre un mot en italique, mettez le simplement en italique dans Word. Un retour à la ligne dans une case donne un retour à la ligne sur le site.',
    'Pour une remarque qui n’est pas un texte (photo à changer, section à retirer…), ajoutez un commentaire Word ou écrivez dans la dernière partie « Autres demandes ».',
  ]],
  ['Anleitung', [
    'Korrigieren Sie den Text direkt im Feld der jeweiligen Sprache (Spalten « Deutsch », « Français », « English », cremefarben). Was in einem Feld steht, erscheint so auf der Website in dieser Sprache.',
    'Passt ein Text, ändern Sie nichts. Korrigieren Sie nur eine Sprache, weisen wir Sie auf die anzupassenden Übersetzungen hin.',
    'Bitte keine Zeilen löschen und die Spalten « Réf. » und « Élément » nicht ändern: Damit finden wir jeden Text auf der Website wieder.',
    'Kursive Wörter erscheinen auch auf der Website kursiv (z. B. in den grossen Titeln). Für ein kursives Wort formatieren Sie es in Word einfach kursiv. Ein Zeilenumbruch im Feld ergibt einen Zeilenumbruch auf der Website.',
    'Für Hinweise, die keinen Text betreffen (Foto ersetzen, Abschnitt entfernen …), fügen Sie einen Word‑Kommentar ein oder schreiben Sie im letzten Teil « Autres demandes ».',
  ]],
];
for (const [titre, points] of consignes) {
  enfants.push(new Paragraph({ spacing: { before: 200, after: 100 }, children: [t(titre, { size: 24, bold: true, color: SAPIN })] }));
  points.forEach(x => enfants.push(new Paragraph({ numbering: { reference: 'puces', level: 0 }, spacing: { after: 80 }, children: [t(x, { size: 20 })] })));
}
enfants.push(new Paragraph({ spacing: { before: 300 }, children: [t(TOUT ? 'Textes du site au 8 octobre 2026. Les prix de la carte et des produits se modifient aussi ici. Pour les horaires, l’adresse ou le téléphone, écrivez le simplement dans « Autres demandes ». Les titres et descriptions Google (référencement) sont adaptés par nos soins.' : 'Textes des pages au 8 octobre 2026. La carte du Tea Room et les produits de la boutique feront l’objet d’un document séparé. Pour les horaires, l’adresse ou le téléphone, écrivez le simplement dans « Autres demandes ». Les titres et descriptions Google (référencement) sont adaptés par nos soins.', { size: 18, italics: true, color: GRIS })] }));

// Sommaire
enfants.push(new Paragraph({ children: [new PageBreak()] }), titreSection('Sommaire · Inhalt', HeadingLevel.HEADING_1));
const NOMS_PAGES = { A: 'Page d’accueil', C: 'Confiserie (boutique)', T: 'Tea Room (salon de thé)', H: 'Notre histoire', V: 'Nous rendre visite', K: 'Contact', F: 'Cadeaux d’entreprise', I: 'Mentions légales' };
const sommaire = [];
data.pages.forEach(pg => {
  const lignes = pg.sections.flatMap(s => s.lignes);
  sommaire.push([`${NOMS_PAGES[pg.code] || pg.titre_fr}`, `${pg.titre_de} · ${pg.titre_fr} · ${pg.titre_en}`, `${lignes[0].ref} à ${lignes[lignes.length - 1].ref}`]);
});
const derniere = l => l[l.length - 1].ref;
if (TOUT) {
  sommaire.push(['Carte du Tea Room', 'Getränke- und Speisekarte · Carte', `${data.carte[0].ref} à ${derniere(data.carte[data.carte.length - 1].plats)}`]);
  sommaire.push(['Produits de la boutique', 'Produkte · Produits', `${data.boutique[0].ref} à ${derniere(data.boutique[data.boutique.length - 1].produits)}`]);
}
sommaire.push(['Autres demandes', 'Weitere Wünsche', '']);
enfants.push(tableau([4200, 8200, 2998], ['Partie', 'Deutsch · Français · English', 'Références'],
  sommaire.map(r => [[p(t(r[0], { bold: true, size: 18 }))], [p(t(r[1], { size: 18 }))], [p(t(r[2], { size: 18, color: SAPIN }))]]), false));

// Pages
const COLS = [900, 1900, 4200, 4199, 4199];
const TITRES = ['Réf.', 'Élément', 'Deutsch', 'Français', 'English'];
data.pages.forEach(pg => {
  enfants.push(new Paragraph({ children: [new PageBreak()] }));
  enfants.push(titreSection(`${NOMS_PAGES[pg.code] || pg.titre_fr} · ${pg.titre_de}`, HeadingLevel.HEADING_1));
  enfants.push(p([t(`Deutsch « ${pg.titre_de} » (/${pg.slug}/) · Français « ${pg.titre_fr} » (/${pg.slug_fr}/) · English « ${pg.titre_en} » (/${pg.slug_en}/)${pg.statut === 'draft' ? ' · brouillon, pas encore publiée' : ''}`, { size: 18, color: GRIS, italics: true })], { spacing: { after: 160 } }));
  pg.sections.forEach((s, i) => {
    const nom = brut(s.titre);
    enfants.push(titreSection(`Section ${i + 1} · ${s.bloc}${nom ? ' · ' + nom : ''}`, HeadingLevel.HEADING_2));
    enfants.push(tableau(COLS, TITRES, s.lignes.map(l => [
      ref(l.ref), [p(t(l.element, { size: 17, color: GRIS }))], [p(runs(l.de))], [p(runs(l.fr))], [p(runs(l.en))],
    ]), [2, 3, 4]));
  });
});

// Carte du Tea Room
if (TOUT) {
enfants.push(new Paragraph({ children: [new PageBreak()] }));
enfants.push(titreSection('Carte du Tea Room · Getränke- und Speisekarte', HeadingLevel.HEADING_1));
enfants.push(p([t(`Noms, descriptions et prix de la carte, identiques sur le site et dans la carte en PDF. Suggestion de la maison actuelle : « ${data.suggestion} ». Pour ajouter ou retirer un plat, écrivez le dans la colonne « Modification » ou dans « Autres demandes ».`, { size: 18, color: GRIS, italics: true })], { spacing: { after: 160 } }));
const COLS_C = [1000, 4500, 4500, 1400, 3998];
const plat = (nom, desc, mention) => {
  const ps = [p(runs(nom, { bold: true }))];
  if (desc) ps.push(p(runs(desc, { size: 17, color: GRIS })));
  if (mention) ps.push(p(runs(mention, { size: 17, color: GRIS, italics: true })));
  return ps;
};
data.carte.forEach(r => {
  enfants.push(titreSection(`${r.de} · ${r.fr}`, HeadingLevel.HEADING_2));
  enfants.push(tableau(COLS_C, ['Réf.', 'Deutsch (actuel)', 'Français (actuel)', 'Prix', 'Modification'], [
    [ref(r.ref), [p(runs('Nom de la rubrique : ' + r.de, { size: 17, color: GRIS }))], [p(runs('Nom de la rubrique : ' + r.fr, { size: 17, color: GRIS }))], vide(), vide()],
    ...r.plats.map(x => [ref(x.ref), plat(x.nom_de, x.desc_de, x.mention_de), plat(x.nom_fr, x.desc_fr, x.mention_fr), [p(t(x.prix, { size: 18 }))], vide()]),
  ]));
});

// Produits de la boutique
enfants.push(new Paragraph({ children: [new PageBreak()] }));
enfants.push(titreSection('Produits de la boutique · Produkte', HeadingLevel.HEADING_1));
enfants.push(p([t('Nom du produit, phrase d’accroche (sous le nom) et formats avec leurs prix en CHF.', { size: 18, color: GRIS, italics: true })], { spacing: { after: 160 } }));
const COLS_P = [1000, 3700, 3700, 3000, 3998];
const produit = (nom, accroche) => accroche ? [p(runs(nom, { bold: true })), p(runs(accroche, { size: 17, color: GRIS }))] : [p(runs(nom, { bold: true }))];
data.boutique.forEach(r => {
  enfants.push(titreSection(`${r.de} · ${r.fr}`, HeadingLevel.HEADING_2));
  enfants.push(tableau(COLS_P, ['Réf.', 'Deutsch (actuel)', 'Français (actuel)', 'Formats et prix (CHF)', 'Modification'], [
    [ref(r.ref), [p(runs('Nom de la catégorie : ' + r.de, { size: 17, color: GRIS }))], [p(runs('Nom de la catégorie : ' + r.fr, { size: 17, color: GRIS }))], vide(), vide()],
    ...r.produits.map(x => [ref(x.ref), produit(x.nom_de, x.accroche_de), produit(x.nom_fr, x.accroche_fr),
      x.formats.map(f => p(t(`${f[0] ? f[0] + ' : ' : ''}${f[2]}`, { size: 18 }))), vide()]),
  ]));
});

}

// Autres demandes
enfants.push(new Paragraph({ children: [new PageBreak()] }));
enfants.push(titreSection('Autres demandes · Weitere Wünsche', HeadingLevel.HEADING_1));
enfants.push(p([t('Tout ce qui ne rentre pas dans les tableaux : nouveau texte à ajouter, section à supprimer, photo à changer, plat ou produit nouveau… Indiquez si possible la page et la référence la plus proche.', { size: 18, color: GRIS, italics: true })], { spacing: { after: 160 } }));
enfants.push(tableau([1000, 3000, 11398], ['N°', 'Page / Réf.', 'Demande'],
  Array.from({ length: 12 }, (_, i) => [[p(t(String(i + 1), { size: 18, color: SAPIN, bold: true }))], vide(), [p(t('', { size: 18 })), p(t('', { size: 18 }))]])));

const doc = new Document({
  creator: 'Confiserie Schiesser', title: TOUT ? 'Textes du site Confiserie Schiesser' : 'Textes des pages Confiserie Schiesser', description: 'Textes du site à relire et modifier',
  styles: {
    default: { document: { run: { font: POLICE, size: 20, color: CHOCO } } },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { size: 32, bold: true, font: POLICE, color: SAPIN }, paragraph: { spacing: { before: 0, after: 120 }, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { size: 22, bold: true, font: POLICE, color: SAPIN }, paragraph: { spacing: { before: 280, after: 120 }, outlineLevel: 1 } },
    ],
  },
  numbering: { config: [{ reference: 'puces', levels: [{ level: 0, format: LevelFormat.BULLET, text: '•', alignment: AlignmentType.LEFT, style: { paragraph: { indent: { left: 400, hanging: 260 } } } }] }] },
  sections: [{
    properties: { page: { size: { width: 11906, height: 16838, orientation: PageOrientation.LANDSCAPE }, margin: { top: 720, bottom: 720, left: 720, right: 720 } } },
    headers: { default: new Header({ children: [new Paragraph({ alignment: AlignmentType.RIGHT, children: [t('Confiserie Schiesser · Textes du site', { size: 16, color: GRIS })] })] }) },
    footers: { default: new Footer({ children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ children: ['Page ', PageNumber.CURRENT, ' / ', PageNumber.TOTAL_PAGES], font: POLICE, size: 16, color: GRIS })] })] }) },
    children: enfants,
  }],
});
Packer.toBuffer(doc).then(b => { fs.writeFileSync(process.argv[3], b); console.log('ok', process.argv[3], b.length); });

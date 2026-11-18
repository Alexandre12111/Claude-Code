// Mesure chaque ligne de texte HTML du one-pager (position, police, couleur) et rend le fond sans texte.
// Usage : node pptx_measure.cjs <page.html> <dossier_sortie> [sélecteur, .page par défaut]
// Un fichier lines_<n>.json et bg_<n>.png par élément trouvé (une diapositive chacun).
const { chromium } = require('playwright');
const fs = require('fs'), path = require('path');
(async () => {
  const [html, out, sel = '.page'] = process.argv.slice(2); fs.mkdirSync(out, { recursive: true });
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1400, height: 1300 }, deviceScaleFactor: 3 });
  await p.goto('file://' + path.resolve(html)); await p.waitForTimeout(600);
  const all = await p.evaluate((sel) => [...document.querySelectorAll(sel)].map(root => {
    const pg = root.getBoundingClientRect(), res = [];
    const tw = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    let n;
    while ((n = tw.nextNode())) {
      const el = n.parentElement;
      if (!n.data.trim() || el.closest('svg')) continue;
      const cs = getComputedStyle(el);
      if (cs.visibility === 'hidden' || cs.display === 'none') continue;
      const r = document.createRange(); let cur = null;
      for (let i = 0; i < n.data.length; i++) {
        r.setStart(n, i); r.setEnd(n, i + 1);
        const rc = r.getClientRects()[0]; if (!rc) continue;
        if (!cur || Math.abs(rc.top - cur.top) > rc.height * 0.5) {
          if (cur) res.push(cur);
          cur = { t: '', x: rc.left, top: rc.top, y: rc.top, r: rc.right, b: rc.bottom };
        }
        cur.t += n.data[i]; cur.r = Math.max(cur.r, rc.right); cur.b = Math.max(cur.b, rc.bottom); cur.x = Math.min(cur.x, rc.left);
      }
      if (cur) res.push(cur);
      const tt = cs.textTransform, op = parseFloat(cs.opacity), z = el.currentCSSZoom || 1;
      for (const L of res.filter(L => !L.done)) {
        L.done = true;
        let t = L.t.replace(/\s+$/, ''), lead = L.t.length - L.t.trimStart().length, trail = L.t.length - L.t.trimEnd().length;
        if (!t.trim()) { L.skip = true; continue; }
        if (tt === 'uppercase') t = t.toUpperCase();
        Object.assign(L, { t: t.trimStart(), lead, trail, x: L.x - pg.left, y: L.y - pg.top, w: L.r - L.x, h: L.b - L.y,
          font: cs.fontFamily.split(',')[0].replace(/["']/g, '').trim(), size: parseFloat(cs.fontSize) * z, weight: parseInt(cs.fontWeight),
          color: cs.color, ls: cs.letterSpacing === 'normal' ? 'normal' : (parseFloat(cs.letterSpacing) * z) + 'px', op });
      }
    }
    return res.filter(L => !L.skip);
  }), sel);
  all.forEach((lines, i) => fs.writeFileSync(path.join(out, `lines_${i}.json`), JSON.stringify(lines)));
  await p.addStyleTag({ content: `${sel} *{-webkit-text-fill-color:transparent !important;text-shadow:none !important} ${sel} svg *{-webkit-text-fill-color:initial !important}` });
  await p.waitForTimeout(200);
  const els = await p.locator(sel).all();
  for (let i = 0; i < els.length; i++) await els[i].screenshot({ path: path.join(out, `bg_${i}.png`) });
  console.log(all.map(l => l.length).join(' + '), 'lignes');
  await b.close();
})();

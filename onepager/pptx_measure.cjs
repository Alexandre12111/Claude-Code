// Mesure chaque ligne de texte HTML du one-pager (position, police, couleur) et rend le fond sans texte.
// Usage : node pptx_measure.cjs <page.html> <dossier_sortie>
const { chromium } = require('playwright');
const fs = require('fs'), path = require('path');
(async () => {
  const [html, out] = process.argv.slice(2); fs.mkdirSync(out, { recursive: true });
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 900, height: 1300 }, deviceScaleFactor: 3 });
  await p.goto('file://' + path.resolve(html)); await p.waitForTimeout(600);
  const lines = await p.evaluate(() => {
    const pg = document.querySelector('.page').getBoundingClientRect(), res = [];
    const tw = document.createTreeWalker(document.querySelector('.page'), NodeFilter.SHOW_TEXT);
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
      const tt = cs.textTransform, op = parseFloat(cs.opacity);
      for (const L of res.filter(L => !L.done)) {
        L.done = true;
        let t = L.t.replace(/\s+$/, ''), lead = L.t.length - L.t.trimStart().length, trail = L.t.length - L.t.trimEnd().length;
        if (!t.trim()) { L.skip = true; continue; }
        if (tt === 'uppercase') t = t.toUpperCase();
        Object.assign(L, { t: t.trimStart(), lead, trail, x: L.x - pg.left, y: L.y - pg.top, w: L.r - L.x, h: L.b - L.y,
          font: cs.fontFamily.split(',')[0].replace(/["']/g, '').trim(), size: parseFloat(cs.fontSize), weight: parseInt(cs.fontWeight),
          color: cs.color, ls: cs.letterSpacing, op });
      }
    }
    return res.filter(L => !L.skip);
  });
  fs.writeFileSync(path.join(out, 'lines.json'), JSON.stringify(lines));
  await p.addStyleTag({ content: '.page *{-webkit-text-fill-color:transparent !important;text-shadow:none !important} .page svg *{-webkit-text-fill-color:initial !important}' });
  await p.waitForTimeout(200);
  await p.locator('.page').screenshot({ path: path.join(out, 'bg.png') });
  console.log(lines.length, 'lignes');
  await b.close();
})();

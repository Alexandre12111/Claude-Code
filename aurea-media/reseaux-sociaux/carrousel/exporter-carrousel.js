// Exporte les 13 visuels du carrousel en PNG 1080 x 1350, plus un PDF pour LinkedIn (document en carrousel).
// Usage : node exporter-carrousel.js
const { chromium } = require('playwright'); const fs = require('fs'); const path = require('path');
const ICI = __dirname, RACINE = path.join(ICI, '../..'), OUT = path.join(ICI, 'export');
const types = { '.html': 'text/html', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.webp': 'image/webp', '.png': 'image/png', '.jpg': 'image/jpeg' };
(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1080, height: 1350 } });
  // servi en http pour que la barre d'état des iPhone puisse lire la couleur des captures
  await p.route('http://aurea.local/**', r => { const f = path.join(RACINE, decodeURIComponent(new URL(r.request().url()).pathname));
    if (!fs.existsSync(f)) return r.fulfill({ status: 404, body: '' }); r.fulfill({ status: 200, contentType: types[path.extname(f)] || 'application/octet-stream', body: fs.readFileSync(f) }); });
  p.on('pageerror', e => console.log('ERR', e.message));
  await p.goto('http://aurea.local/reseaux-sociaux/carrousel/carrousel.html'); await p.waitForFunction(() => window.pret); await p.evaluate(() => document.fonts.ready);
  const fichiers = [];
  for (let i = 1; i <= 13; i++) {
    const id = 'c' + String(i).padStart(2, '0');
    await p.evaluate(id => window.montrer(id), id); await p.waitForTimeout(80);
    const f = path.join(OUT, `carrousel-${String(i).padStart(2, '0')}.png`);
    await p.locator('#' + id).screenshot({ path: f }); fichiers.push(f); console.log(path.basename(f));
  }
  // PDF : une page par visuel (LinkedIn affiche les documents PDF en carrousel)
  const q = await b.newPage();
  await q.setContent(`<style>@page{size:1080px 1350px;margin:0}body{margin:0}img{display:block;width:1080px;height:1350px;page-break-after:always}</style>` + fichiers.map(f => `<img src="data:image/png;base64,${fs.readFileSync(f).toString('base64')}">`).join(''));
  await q.pdf({ path: path.join(OUT, 'carrousel-aurea-media.pdf'), width: '1080px', height: '1350px', printBackground: true });
  console.log('carrousel-aurea-media.pdf');
  await b.close();
})();

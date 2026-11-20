// Exporte chaque visuel du kit en PNG à ses dimensions exactes. Usage : node exporter-kit.js [dossier_sortie]
const { chromium } = require('playwright'); const fs = require('fs'); const path = require('path');
const ICI = __dirname, RACINE = path.join(ICI, '../..'), OUT = process.argv[2] || path.join(ICI, '../export');
const IDS = ['avatar', 'avatar-monogramme', 'avatar-clair', 'facebook-couverture', 'linkedin-page', 'linkedin-profil', 'une-realisations', 'une-avis', 'une-tarifs', 'une-methode', 'une-videos', 'une-contact', 'post-1-presentation', 'post-2-realisations', 'post-3-gaspard'];
const types = { '.html': 'text/html', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.webp': 'image/webp', '.png': 'image/png' };
(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1640, height: 1920 } });
  await p.route('http://kit.local/**', r => { const u = decodeURIComponent(new URL(r.request().url()).pathname);
    const f = u.startsWith('/aurea/') ? path.join(RACINE, u.slice(7)) : path.join(ICI, u);
    if (!fs.existsSync(f)) return r.fulfill({ status: 404, body: '' }); r.fulfill({ status: 200, contentType: types[path.extname(f)] || 'application/octet-stream', body: fs.readFileSync(f) }); });
  p.on('pageerror', e => console.log('ERR', e.message));
  await p.goto('http://kit.local/kit.html'); await p.waitForFunction(() => window.iconesPretes); await p.evaluate(() => document.fonts.ready);
  await p.evaluate(() => Promise.all([...document.images].map(i => i.decode().catch(() => {}))));
  for (const id of IDS) {
    const [w, h] = await p.evaluate(id => window.montrer(id), id);
    await p.waitForTimeout(80);
    await p.locator('#' + id).screenshot({ path: path.join(OUT, id + '.png') });
    console.log(id, w + 'x' + h);
  }
  await b.close();
})();

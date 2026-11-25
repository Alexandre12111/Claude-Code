// Rend la vidéo image par image. Usage : node exporter.js <dossier_images> [debut] [fin]   (indices d'images, 30 i/s)
// Aperçu : node exporter.js <dossier> --instants 1.0 3.6 8.2   (une image PNG par instant)
const { chromium } = require('playwright'); const fs = require('fs'); const path = require('path');
const ICI = __dirname, RACINE = path.join(ICI, '../../..'), OUT = process.argv[2];
const types = { '.html': 'text/html', '.svg': 'image/svg+xml', '.woff2': 'font/woff2', '.webp': 'image/webp', '.png': 'image/png', '.json': 'application/json', '.js': 'text/javascript' };
(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1080, height: 1920 } });
  await p.route('http://aurea.local/**', r => { const f = path.join(RACINE, decodeURIComponent(new URL(r.request().url()).pathname));
    if (!fs.existsSync(f)) return r.fulfill({ status: 404, body: '' }); r.fulfill({ status: 200, contentType: types[path.extname(f)] || 'application/octet-stream', body: fs.readFileSync(f) }); });
  p.on('pageerror', e => console.log('ERR', e.message));
  await p.goto('http://aurea.local/creations/audits/audit-01/video.html'); await p.waitForFunction(() => window.pret, null, { timeout: 60000 });
  if (process.argv[3] === '--instants') {
    for (const s of process.argv.slice(4)) { await p.evaluate(t => rendre(t), +s); await p.screenshot({ path: path.join(OUT, `t-${s}.png`) }); }
  } else {
    const N = Math.round(30 * await p.evaluate(() => window.DUREE));
    const a = +(process.argv[3] || 0), z = +(process.argv[4] || N);
    for (let i = a; i < z; i++) { await p.evaluate(t => rendre(t), i / 30); await p.screenshot({ path: path.join(OUT, `f${String(i).padStart(5, '0')}.jpg`), type: 'jpeg', quality: 93 }); }
  }
  await b.close();
})();

// Capture les sites conceptuels de la vidéo : ordinateur 1600 x 1000 et mobile 780 x 1688.
const { chromium } = require('playwright'); const path = require('path'); const fs = require('fs');
const ICI = __dirname, OUT = path.join(ICI, 'sites/captures');
const NOMS = process.argv.slice(2).length ? process.argv.slice(2) : ['halo', 'veloce', 'domaine-des-aubes', 'kinee', 'studio-brut'];
(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const b = await chromium.launch();
  for (const nom of NOMS) for (const [suf, vp, dpr] of [['', { width: 1440, height: 900 }, 1600 / 1440], ['-mobile', { width: 390, height: 844 }, 2]]) {
    const p = await b.newPage({ viewport: vp, deviceScaleFactor: dpr });
    await p.goto('file://' + path.join(ICI, 'sites', nom + '.html'), { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: path.join(OUT, nom + suf + '.png') }); await p.close();
  }
  await b.close();
})();

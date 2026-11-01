// Aperçu : rend quelques instants clés du teaser en images (contrôle avant export)
const { chromium } = require('playwright');
const [,, sortie, ...temps] = process.argv;
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1920, height: 1080 } });
  const errs = []; p.on('pageerror', (e) => errs.push(e.message));
  await p.goto('http://localhost:8767/teaser/teaser.html', { waitUntil: 'networkidle' });
  await p.evaluate(() => document.fonts.ready);
  for (const t of temps) { await p.evaluate((x) => window.rendre(x), +t); await p.screenshot({ path: `${sortie}/t${String(t).replace('.', '_')}.jpg`, type: 'jpeg', quality: 80 }); }
  if (errs.length) console.log('ERREURS', errs);
  await b.close();
})();

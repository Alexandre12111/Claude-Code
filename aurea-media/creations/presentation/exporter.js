// Exporte chaque diapositive de presentation-aurea.html en PNG 1600 x 900 (puis WebP par Python).
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1700, height: 1000 } });
  await p.goto('http://localhost:8766/creations/presentation/presentation-aurea.html', { waitUntil: 'networkidle' });
  for (let i = 1; i <= 5; i++) await p.locator('#s' + i).screenshot({ path: `slide-${i}.png` });
  await b.close();
})();

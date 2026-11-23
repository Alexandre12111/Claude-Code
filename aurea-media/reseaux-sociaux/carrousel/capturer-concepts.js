// Capture les sites conceptuels comme les vraies réalisations : ordinateur 1600 x 1000, mobile 780 x 1688.
// Usage : node capturer-concepts.js
const { chromium } = require('playwright'); const path = require('path');
const ICI = __dirname, OUT = path.join(ICI, 'concepts/captures');
(async () => {
  require('fs').mkdirSync(OUT, { recursive: true });
  const b = await chromium.launch();
  for (const nom of ['maison-sorel', 'braise', 'terre-de-gres']) {
    for (const [suf, vp, dpr] of [['', { width: 1440, height: 900 }, 1600 / 1440], ['-mobile', { width: 390, height: 844 }, 2]]) {
      const p = await b.newPage({ viewport: vp, deviceScaleFactor: dpr });
      await p.goto('file://' + path.join(ICI, 'concepts', nom + '.html'), { waitUntil: 'networkidle' });
      await p.evaluate(() => document.fonts.ready);
      await p.screenshot({ path: path.join(OUT, nom + suf + '.png') });
      await p.close(); console.log(nom + suf);
    }
  }
  await b.close();
})();

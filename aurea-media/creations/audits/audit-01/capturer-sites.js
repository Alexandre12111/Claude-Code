// Capture les deux versions du site en pleine page (ordinateur, 1600 px de large) et relève la position des éléments annotés.
const { chromium } = require('playwright'); const path = require('path'); const fs = require('fs');
const ICI = __dirname, OUT = path.join(ICI, 'sites/captures'), K = 1600 / 1440;
(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const b = await chromium.launch(); const pos = {};
  for (const nom of ['martin-avant', 'martin-apres']) {
    const p = await b.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: K });
    await p.goto('file://' + path.join(ICI, 'sites', nom + '.html'), { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
    await p.screenshot({ path: path.join(OUT, nom + '.png'), fullPage: true });
    pos[nom] = await p.evaluate(k => { const o = { hauteur: document.documentElement.scrollHeight * k };
      for (const e of document.querySelectorAll('[id]')) { const r = e.getBoundingClientRect(); o[e.id] = [r.x * k, (r.y + scrollY) * k, r.width * k, r.height * k].map(Math.round); } return o; }, K);
    await p.close();
  }
  fs.writeFileSync(path.join(OUT, 'positions.json'), JSON.stringify(pos, null, 1)); console.log(JSON.stringify(pos));
  await b.close();
})();

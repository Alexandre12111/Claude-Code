// Rendu image par image de composition/index.html.
//   node scripts/render.js info                                   durée de la vidéo (s)
//   node scripts/render.js stills <t1,t2,...> <dossier>            images fixes PNG
//   node scripts/render.js frames <fps> <début> <fin> <dossier>    images JPEG numérotées
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async () => {
  const [mode, a, b, c, d] = process.argv.slice(2);
  const browser = await chromium.launch({ args: ['--allow-file-access-from-files', '--font-render-hinting=none'] });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: 1 });
  page.on('pageerror', (e) => console.error('[page]', e.message));
  await page.goto('file://' + path.resolve(__dirname, '../composition/index.html'));
  await page.waitForFunction(() => window.__ready === true, null, { timeout: 180000 });
  const duration = await page.evaluate(() => window.__duration);
  const cdp = await page.context().newCDPSession(page);
  const shot = async (file, png) => {
    const { data } = await cdp.send('Page.captureScreenshot', png ? { format: 'png' } : { format: 'jpeg', quality: 95 });
    fs.writeFileSync(file, Buffer.from(data, 'base64'));
  };
  if (mode === 'info') {
    console.log(duration.toFixed(3));
  } else if (mode === 'stills') {
    fs.mkdirSync(b, { recursive: true });
    for (const t of a.split(',').map(Number)) {
      await page.evaluate((t) => window.__seek(t), t);
      await shot(path.join(b, `t_${t.toFixed(2).padStart(6, '0')}.png`), true);
    }
  } else if (mode === 'frames') {
    const fps = +a, start = +b, end = Math.min(+c, Math.ceil(duration * fps));
    fs.mkdirSync(d, { recursive: true });
    const t0 = Date.now();
    for (let f = start; f < end; f++) {
      await page.evaluate((t) => window.__seek(t), f / fps);
      await shot(path.join(d, `f_${String(f).padStart(5, '0')}.jpg`), false);
    }
    console.log(`images ${start} à ${end - 1} en ${((Date.now() - t0) / 1000).toFixed(1)} s`);
  }
  await browser.close();
})();

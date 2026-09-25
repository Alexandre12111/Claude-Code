// Capture une page de leyton.com/fr en tranches d'écran qui se chevauchent : chaque zone est
// photographiée pendant qu'elle est réellement affichée (images différées et animations terminées),
// puis stitch.py assemble les tranches en une page complète.
//
//   node scripts/capture.js <slug|home> <nom> <largeur> <hauteur> <échelle> [top]
//   « top » ne garde que le premier écran (pages expertises).
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const { setupContext, revealAll, WORK } = require('./lib');

(async () => {
  const [slug, name, w, h, dsf, mode] = process.argv.slice(2);
  const H = +h;
  const out = path.join(WORK, 'caps', name);
  fs.mkdirSync(out, { recursive: true });
  const browser = await chromium.launch();
  const ctx = await setupContext(browser, { width: +w, height: H, dsf: +dsf });
  const page = await ctx.newPage();
  const url = 'https://leyton.com/fr/' + (slug === 'home' ? '' : slug + '/');
  await page.goto(url, { waitUntil: 'networkidle', timeout: 90000 }).catch((e) => console.log('goto', e.message.split('\n')[0]));
  await page.waitForTimeout(2500);
  // Le site force un défilement fluide : on le coupe pour que chaque capture corresponde à sa position.
  await page.addStyleTag({ content: 'html, body, * { scroll-behavior: auto !important; }' });
  const stopMotion = () => page.evaluate(() => {
    // Les carrousels Swiper du site relancent leur autoplay : on les cale sur leur diapositive active et on les bloque,
    // sinon ils bougent entre deux tranches et les cartes se retrouvent décalées au raccord.
    for (const el of document.querySelectorAll('*')) {
      const sw = el.swiper;
      if (!sw || sw.__frozen) continue;
      try {
        if (sw.autoplay) { sw.autoplay.stop(); sw.autoplay.start = () => false; sw.autoplay.resume = () => {}; }
        if (sw.params && sw.params.autoplay) sw.params.autoplay.delay = 1e9;
        sw.slideTo(sw.activeIndex, 0, false);
        sw.allowSlideNext = false; sw.allowSlidePrev = false; sw.allowTouchMove = false;
        sw.__frozen = true;
      } catch (e) {}
    }
    // La vidéo du hero est masquée : sa dernière image (l'anneau Leyton) est recomposée dans prepare_assets.py.
    document.querySelectorAll('video').forEach((v) => { v.pause(); v.style.setProperty('visibility', 'hidden', 'important'); });
  });
  await stopMotion();
  await revealAll(page);
  await stopMotion();
  await page.waitForTimeout(800);

  const total = await page.evaluate(() => document.documentElement.scrollHeight);
  const step = Math.round(H * 0.6);
  const ys = [];
  for (let y = 0; y + H < total; y += step) ys.push(y);
  ys.push(Math.max(0, total - H));
  const meta = { name, url, W: +w, H, dsf: +dsf, total, caps: [] };
  let i = 0;
  for (const y of (mode === 'top' ? [0] : ys)) {
    await page.evaluate((y) => window.scrollTo({ top: y, behavior: 'instant' }), y);
    await page.waitForTimeout(i === 0 ? 800 : 1300);
    if (i === 1) {
      // Une fois le haut de page quitté, les éléments fixes sont masqués pour ne pas se répéter à chaque tranche.
      meta.hiddenFixed = await page.evaluate(() => {
        const hid = [];
        for (const el of document.querySelectorAll('body *')) {
          const cs = getComputedStyle(el);
          if ((cs.position === 'fixed' || cs.position === 'sticky') && cs.display !== 'none' && cs.visibility !== 'hidden') {
            const r = el.getBoundingClientRect();
            if (r.width > 0 && r.height > 0 && r.bottom > 0 && r.top < innerHeight) { el.style.setProperty('visibility', 'hidden', 'important'); hid.push(el.tagName + '.' + String(el.className).slice(0, 60)); }
          }
        }
        return hid;
      });
      await page.waitForTimeout(300);
    }
    const file = `${String(i).padStart(3, '0')}.png`;
    let sy, sy2, tries = 0;
    do {
      sy = await page.evaluate(() => Math.round(window.scrollY));
      await page.screenshot({ path: path.join(out, file) });
      sy2 = await page.evaluate(() => Math.round(window.scrollY));
      if (sy !== sy2) await page.waitForTimeout(500);
    } while (sy !== sy2 && ++tries < 5);
    if (sy !== y) console.log('  position demandée', y, 'obtenue', sy);
    meta.caps.push({ file, y: sy });
    i++;
  }
  fs.writeFileSync(path.join(out, 'meta.json'), JSON.stringify(meta, null, 1));
  console.log(name, 'hauteur', total, 'tranches', meta.caps.length);
  await browser.close();
})();

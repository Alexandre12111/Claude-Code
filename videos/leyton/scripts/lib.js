// Contexte navigateur commun aux captures de leyton.com/fr.
const path = require('path');

const ROOT = path.resolve(__dirname, '..');
const WORK = path.join(ROOT, 'work');
const ATLAS = path.join(ROOT, 'node_modules/world-atlas');

exports.ROOT = ROOT;
exports.WORK = WORK;

exports.setupContext = async (browser, { width = 1440, height = 900, dsf = 2 } = {}) => {
  const mobile = width < 600;
  const ctx = await browser.newContext({
    viewport: { width, height }, deviceScaleFactor: dsf, locale: 'fr-FR', isMobile: mobile, hasTouch: mobile,
    userAgent: mobile
      ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1'
      : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36',
  });
  // Carte du globe « Un partenaire unique sur tous vos marchés », servie depuis node_modules (même fichier que le CDN).
  await ctx.route('https://cdn.jsdelivr.net/npm/world-atlas@2/**', (r) => r.fulfill({ path: path.join(ATLAS, path.basename(new URL(r.request().url()).pathname)), contentType: 'application/json' }));
  // Le Chromium de Playwright ne décode pas le H.264 : les vidéos du hero sont remplacées par leurs copies VP9.
  await ctx.route(/hero-video-optimized-1\.mp4/, (r) => r.fulfill({ path: path.join(WORK, 'media/hero-video.webm'), contentType: 'video/webm' }));
  await ctx.route(/Leyton-Animation-Hq\.mp4/, (r) => r.fulfill({ path: path.join(WORK, 'media/anim-mobile.webm'), contentType: 'video/webm' }));
  await ctx.route(/googletagmanager|google-analytics|doubleclick|rum\.eu-west-3|ip2location/, (r) => r.abort());
  return ctx;
};

// Fait défiler lentement toute la page pour déclencher le chargement différé et les animations d'apparition.
exports.revealAll = async (page, { step = 280, wait = 420 } = {}) => {
  let H = await page.evaluate(() => document.documentElement.scrollHeight);
  for (let y = 0; y < H; y += step) {
    await page.evaluate((y) => window.scrollTo({ top: y, behavior: 'instant' }), y);
    await page.waitForTimeout(wait);
    H = await page.evaluate(() => document.documentElement.scrollHeight);
  }
  await page.waitForTimeout(1500);
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  await page.waitForTimeout(1500);
  return H;
};

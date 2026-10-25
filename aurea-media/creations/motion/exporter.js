// Capture motion-aurea.html image par image (30 i/s, 14 s, 1920 x 1080) puis encode avec ffmpeg :
// MP4 et WebM en 1080p (page Motion design) et en 720p (carte de l'accueil), plus une affiche.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const dossier = process.argv[2];
const run = c => execSync(`ffmpeg -y -loglevel error ${c}`);
(async () => {
  fs.rmSync(dossier, { recursive: true, force: true }); fs.mkdirSync(dossier, { recursive: true });
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1920, height: 1080 } });
  await p.goto('http://localhost:8766/creations/motion/motion-aurea.html', { waitUntil: 'networkidle' });
  await p.evaluate(() => document.fonts.ready);
  const fps = 30, duree = 14;
  for (let f = 0; f < fps * duree; f++) {
    await p.evaluate(t => window.rendre(t), f / fps);
    await p.screenshot({ path: `${dossier}/f${String(f).padStart(4, '0')}.jpg`, type: 'jpeg', quality: 95 });
  }
  await b.close();
  const src = `-framerate ${fps} -i ${dossier}/f%04d.jpg`;
  run(`${src} -c:v libx264 -pix_fmt yuv420p -crf 18 -preset slow -tune animation -movflags +faststart motion-aurea-media-1080.mp4`);
  run(`${src} -c:v libvpx-vp9 -b:v 0 -crf 30 -row-mt 1 -deadline good motion-aurea-media-1080.webm`);
  run(`${src} -vf scale=1280:720:flags=lanczos -c:v libx264 -pix_fmt yuv420p -crf 21 -preset slow -tune animation -movflags +faststart motion-aurea-media-720.mp4`);
  run(`${src} -vf scale=1280:720:flags=lanczos -c:v libvpx-vp9 -b:v 0 -crf 34 -row-mt 1 motion-aurea-media-720.webm`);
  run(`-i ${dossier}/f0255.jpg -q:v 2 motion-aurea-media-affiche.jpg`);
})();

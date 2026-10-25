// Capture motion-aurea.html image par image (30 i/s, 10 s) puis encode en MP4 et WebM avec ffmpeg.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const dossier = process.argv[2];
(async () => {
  fs.mkdirSync(dossier, { recursive: true });
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1280, height: 720 } });
  await p.goto('http://localhost:8766/creations/motion/motion-aurea.html', { waitUntil: 'networkidle' });
  await p.evaluate(() => document.fonts.ready);
  const fps = 30, duree = 10;
  for (let f = 0; f < fps * duree; f++) {
    await p.evaluate(t => window.rendre(t), f / fps);
    await p.screenshot({ path: `${dossier}/f${String(f).padStart(4, '0')}.jpg`, type: 'jpeg', quality: 92 });
  }
  await b.close();
  execSync(`ffmpeg -y -loglevel error -framerate ${fps} -i ${dossier}/f%04d.jpg -c:v libx264 -pix_fmt yuv420p -crf 23 -preset slow -movflags +faststart motion-aurea-media.mp4`);
  execSync(`ffmpeg -y -loglevel error -framerate ${fps} -i ${dossier}/f%04d.jpg -c:v libvpx-vp9 -b:v 0 -crf 36 -row-mt 1 motion-aurea-media.webm`);
  execSync(`ffmpeg -y -loglevel error -i ${dossier}/f0180.jpg -q:v 3 motion-aurea-media-affiche.jpg`);
})();

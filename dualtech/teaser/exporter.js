// Capture teaser.html image par image (30 i/s, durée de calage.json, 1920 x 1080), puis assemble avec la bande son (son.wav) :
// MP4 1080p (diffusion, réseaux sociaux), WebM 1080p (site), MP4 720p (léger) et une affiche.
// Usage : servir le dossier dualtech sur le port 8767, puis : node exporter.js <dossier-images>
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const dossier = process.argv[2];
const ici = __dirname;
const run = (c) => execSync(`ffmpeg -y -loglevel error ${c}`, { cwd: ici });
(async () => {
  fs.rmSync(dossier, { recursive: true, force: true }); fs.mkdirSync(dossier, { recursive: true });
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1920, height: 1080 } });
  await p.goto('http://localhost:8767/teaser/teaser.html', { waitUntil: 'networkidle' });
  await p.evaluate(() => document.fonts.ready);
  const fps = 30, duree = JSON.parse(fs.readFileSync(`${ici}/calage.json`)).duree;   // durée calée sur la voix
  for (let f = 0; f < Math.round(fps * duree); f++) {
    await p.evaluate((t) => window.rendre(t), f / fps);
    await p.screenshot({ path: `${dossier}/f${String(f).padStart(4, '0')}.jpg`, type: 'jpeg', quality: 94 });
    if (f % 150 === 0) console.log(`image ${f} / ${fps * duree}`);
  }
  await b.close();
  const src = `-framerate ${fps} -i ${dossier}/f%04d.jpg -i son.wav`;
  const audio = '-af loudnorm=I=-14:TP=-1:LRA=11';
  run(`${src} -map 0:v -map 1:a -c:v libx264 -pix_fmt yuv420p -crf 21 -preset slow -tune film -c:a aac -b:a 192k ${audio} -shortest -movflags +faststart teaser-dualtech-2026-1080.mp4`);
  run(`${src} -map 0:v -map 1:a -c:v libvpx-vp9 -b:v 0 -crf 31 -row-mt 1 -deadline good -c:a libopus -b:a 160k ${audio} -shortest teaser-dualtech-2026-1080.webm`);
  run(`${src} -map 0:v -map 1:a -vf scale=1280:720:flags=lanczos -c:v libx264 -pix_fmt yuv420p -crf 22 -preset slow -c:a aac -b:a 160k ${audio} -shortest -movflags +faststart teaser-dualtech-2026-720.mp4`);
  run(`-i ${dossier}/f${String(Math.round((duree - 3) * fps)).padStart(4, '0')}.jpg -q:v 2 teaser-dualtech-2026-affiche.jpg`);
  console.log('Export terminé');
})();

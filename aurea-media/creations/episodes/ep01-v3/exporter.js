// Export de l'épisode : rend episode.html image par image (30 i/s, 1080 x 1920), puis assemble avec son.wav.
// Aucun serveur : les fichiers du dossier sont servis par interception des requêtes.
// Usage : node exporter.js <dossier-images> [image-de-départ]
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs'), path = require('path');
const ICI = __dirname, dossier = process.argv[2], depart = +(process.argv[3] || 0), finImg = process.argv[4] ? +process.argv[4] : null;   // V5 : rendu par tranches en parallèle
const types = { '.html': 'text/html', '.js': 'application/javascript', '.json': 'application/json', '.woff2': 'font/woff2', '.png': 'image/png', '.webp': 'image/webp' };
(async () => {
  if (!depart && finImg === null) { fs.rmSync(dossier, { recursive: true, force: true }); }
  fs.mkdirSync(dossier, { recursive: true });
  const b = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader'] });
  const p = await b.newPage({ viewport: { width: 1080, height: 1920 } });
  // Three.js servi depuis node_modules (rendu hors ligne) ; le modèle 3D est dans creations/mascotte-3d
  const NM = process.env.THREE_NM || path.join(ICI, 'node_modules/three');
  await p.route('https://cdn.jsdelivr.net/**', r => {
    const f = path.join(NM, new URL(r.request().url()).pathname.replace(/^\/npm\/three@[^/]+/, ''));
    if (!fs.existsSync(f)) return r.fulfill({ status: 404, body: '' });
    r.fulfill({ status: 200, contentType: 'application/javascript', body: fs.readFileSync(f) });
  });
  await p.route('http://episode.local/**', r => {
    const u = decodeURIComponent(new URL(r.request().url()).pathname);
    const f = u.startsWith('/mascotte-3d/') ? path.join(ICI, '../..', u) : path.join(ICI, u);
    if (!fs.existsSync(f)) return r.fulfill({ status: 404, body: '' });
    r.fulfill({ status: 200, contentType: types[path.extname(f)] || 'application/octet-stream', body: fs.readFileSync(f) });
  });
  await p.goto('http://episode.local/episode.html');
  await p.waitForFunction(() => window.pret, null, { timeout: 60000 });
  await p.evaluate(() => document.fonts.ready);
  const fps = 30, duree = await p.evaluate(() => window.duree), total = Math.round(fps * duree);
  for (let f = depart; f < (finImg ?? total); f++) {
    await p.evaluate(t => window.rendre(t), f / fps);
    await p.screenshot({ path: `${dossier}/f${String(f).padStart(5, '0')}.jpg`, type: 'jpeg', quality: 95 });
    if (f % 100 === 0) console.log(`image ${f} / ${total}`);
  }
  await b.close();
  if (finImg !== null) { console.log('tranche terminée'); return; }
  const run = c => execSync(`ffmpeg -y -loglevel error ${c}`, { cwd: ICI });
  // V4 : gain fixe vers -14,5 LUFS puis limiteur sans remontée automatique (level=disabled) à -3 dBFS : aucune saturation après AAC
  const mes = execSync(`ffmpeg -hide_banner -i son.wav -af ebur128 -f null - 2>&1`, { cwd: ICI }).toString();
  const I = parseFloat(mes.match(/I:\s+(-?[\d.]+) LUFS/g).pop().match(/-?[\d.]+/)[0]);
  const ln = `volume=${(-14.5 - I).toFixed(2)}dB,alimiter=limit=0.708:attack=3:release=60:level=disabled,aresample=48000:resampler=soxr`;
  const src = `-framerate ${fps} -i ${dossier}/f%05d.jpg -i son.wav`;
  run(`${src} -map 0:v -map 1:a -c:v libx264 -pix_fmt yuv420p -crf 17 -preset slow -tune animation -c:a aac -b:a 256k -af ${ln} -shortest -movflags +faststart ep01-v5-1080x1920.mp4`);
  run(`-ss 0.9 -i ep01-v5-1080x1920.mp4 -frames:v 1 -q:v 2 ep01-v5-couverture.jpg`);
  console.log('Export terminé');
})();

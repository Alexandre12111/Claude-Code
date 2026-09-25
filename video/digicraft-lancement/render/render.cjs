// Rendu image par image : node render.cjs --stills 1,2.5 | --video --from 0 --to 61.2 --fps 60 --out ../out/x.mp4 [--scale 0.5]
const { chromium } = require('playwright');
const { spawn } = require('child_process');
const path = require('path');
const fs = require('fs');

const args = process.argv.slice(2);
const opt = (k, d) => { const i = args.indexOf('--' + k); return i >= 0 ? args[i + 1] : d; };
const has = (k) => args.includes('--' + k);
const scale = parseFloat(opt('scale', '1'));
const page_url = 'file://' + path.resolve(__dirname, '../src/index.html');

(async () => {
  const browser = await chromium.launch({ args: ['--disable-gpu-vsync', '--force-color-profile=srgb', '--font-render-hinting=none'] });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: scale });
  page.on('console', (m) => { if (m.type() === 'error' || m.type() === 'warning') console.error('[page]', m.text()); });
  page.on('pageerror', (e) => console.error('[pageerror]', e.message));
  await page.goto(page_url);
  await page.waitForFunction(() => window.READY === true, null, { timeout: 60000 });

  if (has('stills')) {
    const outDir = path.resolve(__dirname, opt('dir', '../out/stills'));
    fs.mkdirSync(outDir, { recursive: true });
    for (const t of opt('stills', '0').split(',').map(parseFloat)) {
      await page.evaluate((t) => window.render(t), t);
      const f = path.join(outDir, `t_${t.toFixed(2).padStart(6, '0')}.png`);
      await page.screenshot({ path: f, type: 'png' });
      console.log(f);
    }
  } else {
    const fps = parseFloat(opt('fps', '60'));
    const from = parseFloat(opt('from', '0'));
    const to = parseFloat(opt('to', '61.2'));
    const out = path.resolve(__dirname, opt('out', '../out/video.mp4'));
    const crf = opt('crf', '14');
    const n = Math.round((to - from) * fps);
    const ff = spawn('ffmpeg', ['-y', '-v', 'error', '-f', 'image2pipe', '-framerate', String(fps), '-c:v', 'png', '-i', '-',
      '-c:v', 'libx264', '-preset', opt('preset', 'medium'), '-crf', crf, '-pix_fmt', 'yuv420p', '-tune', 'animation',
      '-movflags', '+faststart', out], { stdio: ['pipe', 'inherit', 'inherit'] });
    const t0 = Date.now();
    for (let i = 0; i < n; i++) {
      const t = from + i / fps;
      await page.evaluate((t) => window.render(t), t);
      const buf = await page.screenshot({ type: 'png' });
      if (!ff.stdin.write(buf)) await new Promise((r) => ff.stdin.once('drain', r));
      if (i % 120 === 0) console.log(`frame ${i}/${n} t=${t.toFixed(2)} ${((Date.now() - t0) / 1000).toFixed(0)}s`);
    }
    ff.stdin.end();
    await new Promise((r) => ff.on('close', r));
    console.log('done', out, ((Date.now() - t0) / 1000).toFixed(0) + 's');
  }
  await browser.close();
})();

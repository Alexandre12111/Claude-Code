// Mesure la slide HTML (positions et textes) et exporte les éléments graphiques en PNG pour build_pptx.py.
const { chromium } = require('playwright');
const fs = require('fs'), path = require('path');
(async () => {
  const [html, out] = process.argv.slice(2);
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1920, height: 1080 }, deviceScaleFactor: 2 });
  await p.goto('file://' + html); await p.waitForTimeout(800);
  const R = (sel) => p.evaluate((sel) => [...document.querySelectorAll(sel)].map(e => { const r = e.getBoundingClientRect(); return { x: r.x, y: r.y, w: r.width, h: r.height, t: e.innerText }; }), sel);
  const M = {};
  for (const [k, s] of Object.entries({ logo: '.top svg', tag: '.tag', h1: 'h1', q: '.q', qb: '.q b', cards: '.c', ch: '.ch', kb: '.k b', ks: '.k span', at: '.at', ati: '.at i', atb: '.at b', atp: '.ats+p', fkb: '.fk b', fks: '.fk span', fkd: '.fk div+div', hq: '.hq', sei: '.se i', sel: '.se li', ft: '.ft span' })) M[k] = await R(s);
  M.cardcls = await p.evaluate(() => [...document.querySelectorAll('.c')].map(e => e.className));
  // fond seul
  await p.evaluate(() => { document.querySelectorAll('#s > *').forEach(e => e.style.visibility = 'hidden'); });
  await p.screenshot({ path: path.join(out, 'bg.png') });
  await p.evaluate(() => { document.querySelectorAll('#s > *').forEach(e => e.style.visibility = ''); });
  // logo et icônes, fond transparent
  const svgs = await p.evaluate(() => ({ logo: document.querySelector('.top svg').outerHTML, icons: [...document.querySelectorAll('.at i svg, .hq svg, .se i svg')].map(s => s.outerHTML) }));
  const q = await b.newPage({ deviceScaleFactor: 4 });
  async function shot(svg, file, w, h) { await q.setContent(`<html><body style="margin:0;background:transparent"><div id="z" style="width:${w}px;height:${h}px;display:flex">${svg.replace(/<svg /, `<svg style="width:${w}px;height:${h}px" `)}</div></body></html>`); await q.locator('#z').screenshot({ path: path.join(out, file), omitBackground: true }); }
  await shot(svgs.logo, 'logo.png', M.logo[0].w, M.logo[0].h);
  for (let i = 0; i < svgs.icons.length; i++) await shot(svgs.icons[i], `ic${i}.png`, 48, 48);
  M.nicons = svgs.icons.length;
  fs.writeFileSync(path.join(out, 'm.json'), JSON.stringify(M));
  await b.close();
})();

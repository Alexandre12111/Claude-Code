/* Intro et outro YouCraft pour les replays de webinaire (1920 × 1080). Rendu image par image : window.render(t). */
(function () {
  const W = 1920, H = 1080, MODE = window.MODE;
  const L = window.YC_LOGO;
  const clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
  const lerp = (a, b, t) => a + (b - a) * t;
  const P = (t, t0, d) => clamp((t - t0) / d);
  const E = {
    outCubic: (x) => 1 - Math.pow(1 - x, 3), outQuint: (x) => 1 - Math.pow(1 - x, 5), outExpo: (x) => (x >= 1 ? 1 : 1 - Math.pow(2, -10 * x)),
    inExpo: (x) => (x <= 0 ? 0 : Math.pow(2, 10 * x - 10)), inOutCubic: (x) => (x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2),
    inCubic: (x) => x * x * x, inOutSine: (x) => -(Math.cos(Math.PI * x) - 1) / 2,
  };
  const spring = (t, k = 170, c = 14) => { if (t <= 0) return 0; const w = Math.sqrt(k), z = c / (2 * w); const wd = w * Math.sqrt(Math.max(1e-6, 1 - z * z)); return 1 - Math.exp(-z * w * t) * (Math.cos(wd * t) + (z * w / wd) * Math.sin(wd * t)); };
  const $ = (tag, css, parent, html) => { const e = document.createElement(tag); if (css) Object.assign(e.style, css); if (html != null) e.innerHTML = html; (parent || stage).appendChild(e); return e; };
  const S = (e, o) => {
    const tr = `translate(${o.x || 0}px,${o.y || 0}px) rotate(${o.r || 0}deg) scale(${o.s == null ? 1 : o.s})`;
    e.style.transform = tr; if (o.o != null) e.style.opacity = o.o; if (o.b != null) e.style.filter = o.b > 0.05 ? `blur(${o.b}px)` : 'none';
  };
  const NS = 'http://www.w3.org/2000/svg';
  const svgEl = (tag, attrs, parent) => { const e = document.createElementNS(NS, tag); for (const k in attrs) e.setAttribute(k, attrs[k]); parent.appendChild(e); return e; };
  const icon = (n, s, c, sw = 2) => `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="${c}" stroke-width="${sw}" stroke-linecap="round" stroke-linejoin="round">${ICONS[n]}</svg>`;

  const stage = document.getElementById('stage');

  /* ---------- fond : encre, halo outremer, grille en perspective, particules ---------- */
  const bg = $('div', { position: 'absolute', inset: '0', background: '#0B0B0F' });
  const glowA = $('div', { position: 'absolute', left: '0', top: '0', width: '1500px', height: '1500px', marginLeft: '-750px', marginTop: '-750px', borderRadius: '50%', background: 'radial-gradient(closest-side, rgba(30,59,255,0.55), rgba(30,59,255,0.18) 55%, rgba(30,59,255,0))' });
  const glowB = $('div', { position: 'absolute', left: '0', top: '0', width: '900px', height: '900px', marginLeft: '-450px', marginTop: '-450px', borderRadius: '50%', background: 'radial-gradient(closest-side, rgba(215,255,63,0.16), rgba(215,255,63,0))' });
  const gridWrap = $('div', { position: 'absolute', inset: '0', perspective: '900px', overflow: 'hidden' });
  const grid = $('div', { position: 'absolute', left: '-1000px', width: '3920px', top: '560px', height: '1400px', transformOrigin: '50% 0',
    backgroundImage: 'linear-gradient(rgba(245,242,234,0.07) 1.5px, transparent 1.5px), linear-gradient(90deg, rgba(245,242,234,0.07) 1.5px, transparent 1.5px)', backgroundSize: '96px 96px',
    maskImage: 'linear-gradient(to bottom, rgba(0,0,0,0), rgba(0,0,0,1) 25%, rgba(0,0,0,0) 85%)', WebkitMaskImage: 'linear-gradient(to bottom, rgba(0,0,0,0), rgba(0,0,0,1) 25%, rgba(0,0,0,0) 85%)' }, gridWrap);
  let seed = 7; const rnd = () => ((seed = (seed * 16807) % 2147483647) / 2147483647);
  const parts = Array.from({ length: 42 }, () => ({ x: rnd() * W, y: rnd() * H, r: 1.5 + rnd() * 3, sp: 8 + rnd() * 26, ph: rnd() * 6.28, lime: rnd() < 0.3,
    e: $('div', { position: 'absolute', left: '0', top: '0', borderRadius: '50%' }) }));
  parts.forEach((p) => Object.assign(p.e.style, { width: p.r * 2 + 'px', height: p.r * 2 + 'px', background: p.lime ? '#D7FF3F' : '#8FA2FF' }));
  const aur = [['rgba(30,59,255,0.42)', 1300], ['rgba(111,134,255,0.28)', 1000], ['rgba(215,255,63,0.10)', 800]].map(([c, d]) =>
    $('div', { position: 'absolute', left: '0', top: '0', width: d + 'px', height: d * 0.62 + 'px', marginLeft: -d / 2 + 'px', marginTop: -d * 0.31 + 'px', borderRadius: '50%', background: `radial-gradient(closest-side, ${c}, rgba(0,0,0,0))`, filter: 'blur(30px)' }));
  const horizon = $('div', { position: 'absolute', left: '160px', width: '1600px', top: '0', height: '2px', background: 'linear-gradient(90deg, rgba(143,162,255,0), rgba(190,202,255,0.75), rgba(143,162,255,0))', boxShadow: '0 0 40px 8px rgba(79,107,255,0.35)' });
  const vign = $('div', { position: 'absolute', inset: '0', background: 'radial-gradient(ellipse at 50% 42%, rgba(0,0,0,0) 50%, rgba(0,0,0,0.7) 100%)' });
  const grainC = $('canvas', { position: 'absolute', inset: '0', width: '1920px', height: '1080px', opacity: '0.07', mixBlendMode: 'overlay', pointerEvents: 'none' });
  grainC.width = 480; grainC.height = 270; const gctx = grainC.getContext('2d'); const gimg = gctx.createImageData(480, 270);
  grid.style.opacity = '0.5';
  const bgUpdate = (t, k = 1, gx = 960, gy = 470, hy = 640) => {
    S(glowA, { x: gx + 60 * Math.sin(t * 0.5), y: gy + 30 * Math.cos(t * 0.4), s: lerp(0.6, 1, k) + 0.04 * Math.sin(t * 1.3), o: k * 0.85 });
    S(glowB, { x: gx + 260 * Math.cos(t * 0.35), y: gy - 120 + 80 * Math.sin(t * 0.45), o: 0.7 * k });
    S(aur[0], { x: 560 + 140 * Math.sin(t * 0.32), y: 260 + 60 * Math.cos(t * 0.27), r: 8 * Math.sin(t * 0.2), o: k });
    S(aur[1], { x: 1400 + 120 * Math.cos(t * 0.29), y: 330 + 70 * Math.sin(t * 0.33), r: -10 + 6 * Math.sin(t * 0.25), o: k });
    S(aur[2], { x: 1180 + 200 * Math.sin(t * 0.21), y: 860 + 40 * Math.cos(t * 0.3), o: k });
    S(horizon, { y: hy, o: k * (0.55 + 0.1 * Math.sin(t * 1.1)) });
    grid.style.transform = `rotateX(74deg) translateY(${(t * 40) % 96}px)`; grid.style.opacity = 0.45 * k;
    parts.forEach((p, i) => { const y = ((p.y - t * p.sp * 0.6) % H + H) % H; S(p.e, { x: p.x + 14 * Math.sin(t * 0.6 + p.ph), y, o: i % 2 ? 0 : k * (0.15 + 0.3 * (0.5 + 0.5 * Math.sin(t * 1.7 + p.ph))) }); });
    let sd = Math.floor(t * 60) * 9301 + 49297; const d = gimg.data;
    for (let i = 0; i < d.length; i += 4) { sd = (sd * 1103515245 + 12345) & 0x7fffffff; const v = sd & 255; d[i] = d[i + 1] = d[i + 2] = v; d[i + 3] = 255; }
    gctx.putImageData(gimg, 0, 0);
  };

  /* ---------- cartes en verre avec mini interface animée ---------- */
  const LIME = '#D7FF3F', UM = '#4F6BFF', ST = 'rgba(245,242,234,';
  function vis(kind, p) {
    const q = (a, d = 0.35) => E.outCubic(clamp((p - a) / d));
    let g = '';
    if (kind === 'crm') {
      [['Lead', 1.0, UM], ['Qualified', 0.74, UM], ['Proposal', 0.5, '#8FA2FF'], ['Won', 0.3, LIME]].forEach(([l, w, c], i) => {
        const y = 4 + i * 19, k = q(0.1 + i * 0.12);
        g += `<text x="0" y="${y + 11}" font-size="11" fill="${ST}0.55)" font-family="DM Sans">${l}</text><rect x="72" y="${y + 2}" width="${158 * w * k}" height="11" rx="5.5" fill="${c}" opacity="${i === 3 ? 1 : 0.85}"/>`;
      });
    } else if (kind === 'dash') {
      const pts = [52, 46, 49, 38, 41, 30, 33, 22, 25, 12], n = pts.length, xs = (i) => 4 + i * (222 / (n - 1));
      const m = Math.max(1, Math.floor(q(0.05, 0.75) * (n - 1) * 100) / 100), full = Math.floor(m);
      let d = `M${xs(0)} ${pts[0]}`; for (let i = 1; i <= full; i++) d += ` L${xs(i)} ${pts[i]}`;
      const fr = m - full; let ex = xs(full), ey = pts[full];
      if (full < n - 1 && fr > 0) { ex = lerp(xs(full), xs(full + 1), fr); ey = lerp(pts[full], pts[full + 1], fr); d += ` L${ex} ${ey}`; }
      [18, 36, 54].forEach((y) => (g += `<line x1="0" x2="230" y1="${y}" y2="${y}" stroke="${ST}0.08)" stroke-width="1"/>`));
      g += `<defs><linearGradient id="ar" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${UM}" stop-opacity="0.5"/><stop offset="1" stop-color="${UM}" stop-opacity="0"/></linearGradient></defs>`;
      g += `<path d="${d} L${ex} 72 L4 72 Z" fill="url(#ar)"/><path d="${d}" fill="none" stroke="#8FA2FF" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round"/>`;
      g += `<circle cx="${ex}" cy="${ey}" r="4.5" fill="${LIME}"/><circle cx="${ex}" cy="${ey}" r="${9 + 3 * Math.sin(p * 20)}" fill="${LIME}" opacity="0.25"/>`;
    } else if (kind === 'flow') {
      const X = [20, 115, 210];
      g += `<line x1="20" x2="${20 + 190 * q(0.1, 0.8)}" y1="36" y2="36" stroke="${UM}" stroke-width="2.5" stroke-dasharray="5 5"/>`;
      X.forEach((x, i) => { const k = spring((p - 0.15 - i * 0.22) * 1.6, 220, 14), done = p > 0.35 + i * 0.22;
        g += `<g transform="translate(${x} 36) scale(${Math.max(0.001, k)})"><circle r="17" fill="${done ? (i === 2 ? LIME : UM) : '#1A1D2B'}" stroke="${ST}0.25)" stroke-width="1.5"/>` +
          (done ? `<path d="M-6 0 L-1.5 4.5 L6.5 -4" fill="none" stroke="${i === 2 ? '#0B0B0F' : '#fff'}" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>` : '') + '</g>';
        g += `<text x="${x}" y="70" text-anchor="middle" font-size="10.5" fill="${ST}0.55)" font-family="DM Sans">${['Request', 'Review', 'Approved'][i]}</text>`; });
    } else if (kind === 'kanban') {
      [0, 1, 2].forEach((c) => { const x = c * 78;
        g += `<rect x="${x}" y="0" width="70" height="74" rx="7" fill="${ST}0.04)"/><rect x="${x + 8}" y="7" width="${[26, 34, 22][c]}" height="5" rx="2.5" fill="${ST}0.35)"/>`;
        const cards = [[1, 1, 1], [1, 1, 0], [1, 0, 0]][c];
        cards.forEach((on, r) => { if (!on) return; const k = E.outQuint(clamp((p - 0.1 - (c * 2 + r) * 0.07) / 0.4));
          g += `<g opacity="${k}" transform="translate(0 ${(1 - k) * 10})"><rect x="${x + 6}" y="${18 + r * 18}" width="58" height="14" rx="4" fill="${c === 2 ? 'rgba(215,255,63,0.9)' : 'rgba(79,107,255,0.55)'}"/></g>`; });
      });
    } else if (kind === 'portal') {
      [['Contract.pdf', 'Signed'], ['Report Q3.pdf', 'Shared'], ['Invoice 214.pdf', 'Paid']].forEach(([f, st], i) => { const k = E.outQuint(clamp((p - 0.1 - i * 0.14) / 0.4)), y = i * 25;
        g += `<g opacity="${k}" transform="translate(${(1 - k) * 18} 0)"><rect x="0" y="${y}" width="230" height="20" rx="6" fill="${ST}0.05)"/><rect x="7" y="${y + 4}" width="9" height="12" rx="2" fill="#8FA2FF"/>` +
          `<text x="24" y="${y + 14}" font-size="10.5" fill="${ST}0.8)" font-family="DM Sans">${f}</text><rect x="168" y="${y + 4}" width="54" height="12" rx="6" fill="${i === 0 ? 'rgba(215,255,63,0.18)' : 'rgba(79,107,255,0.25)'}"/>` +
          `<text x="195" y="${y + 13.2}" text-anchor="middle" font-size="9" font-weight="700" fill="${i === 0 ? LIME : '#AFC0FF'}" font-family="DM Sans">${st}</text></g>`; });
    }
    return `<svg width="230" height="76" viewBox="0 0 230 76" overflow="visible">${g}</svg>`;
  }
  function makeCards(parent, top) {
    const CARDS = [['users', 'CRM', 'crm'], ['layout-dashboard', 'Dashboards', 'dash'], ['list-checks', 'Workflows', 'flow'], ['folder-kanban', 'Project tracking', 'kanban'], ['globe', 'Client portals', 'portal']];
    const row = $('div', { position: 'absolute', left: '0', width: W + 'px', top: top + 'px', display: 'flex', justifyContent: 'center', gap: '26px', perspective: '1400px' }, parent);
    return CARDS.map(([ic, title, kind], i) => {
      const c = $('div', { position: 'relative', width: '278px', height: '186px', boxSizing: 'border-box', padding: '20px 22px', borderRadius: '26px', overflow: 'hidden',
        background: 'linear-gradient(160deg, rgba(255,255,255,0.11), rgba(255,255,255,0.025) 60%)', border: '1px solid rgba(255,255,255,0.13)',
        boxShadow: 'inset 0 1px 0 rgba(255,255,255,0.18), 0 30px 60px rgba(0,0,0,0.45), 0 0 0 1px rgba(0,0,0,0.2)', backdropFilter: 'blur(14px)', transformOrigin: '50% 100%' }, row);
      const ico = i === 4 ? 'linear-gradient(140deg,#E9FF7A,#C5F000)' : 'linear-gradient(140deg,#5D78FF,#1E3BFF)';
      $('div', { display: 'flex', alignItems: 'center', gap: '14px' }, c,
        `<span style="display:flex;width:46px;height:46px;border-radius:14px;background:${ico};align-items:center;justify-content:center;box-shadow:inset 0 1px 0 rgba(255,255,255,0.45),0 8px 18px ${i === 4 ? 'rgba(215,255,63,0.25)' : 'rgba(30,59,255,0.45)'}">${icon(ic, 24, i === 4 ? '#0B0B0F' : '#fff', 2.1)}</span>` +
        `<span style="font:700 25px 'Plus Jakarta Sans',sans-serif;letter-spacing:-0.02em;color:#F5F2EA;white-space:nowrap">${title}</span>`);
      const v = $('div', { marginTop: '18px', height: '76px' }, c);
      const sheen = $('div', { position: 'absolute', top: '-40px', left: '0', width: '120px', height: '280px', background: 'linear-gradient(100deg, rgba(255,255,255,0), rgba(255,255,255,0.16), rgba(255,255,255,0))', transform: 'translateX(-200px) rotate(18deg)' }, c);
      return { c, v, kind, sheen };
    });
  }
  const animCards = (cards, t, t0) => cards.forEach((k, i) => {
    const ts = t0 + i * 0.11, p = spring(t - ts, 160, 17), o = clamp((t - ts) * 4);
    k.c.style.transform = `translateY(${(1 - p) * 90}px) rotateX(${(1 - p) * 38}deg) scale(${lerp(0.9, 1, clamp(p))})`; k.c.style.opacity = o;
    k.v.innerHTML = vis(k.kind, clamp((t - ts - 0.25) / 1.6));
    const sp = (t - (t0 + 1.3 + i * 0.09)) / 0.9;
    k.sheen.style.transform = `translateX(${sp > 0 && sp < 1 ? lerp(-200, 420, E.inOutSine(sp)) : -200}px) rotate(18deg)`;
  });
  /* Reflet lumineux qui balaie le mot YouCraft. */
  function logoShine(lo, t, t0) {
    if (!lo.shine) {
      const svg = lo.wrap.querySelector('svg'), id = 'sh' + lo.h;
      const defs = svg.querySelector('defs');
      const gr = svgEl('linearGradient', { id, x1: 0, y1: 0, x2: 1, y2: 0, gradientUnits: 'userSpaceOnUse' }, defs);
      [[0, 0], [0.45, 0], [0.5, 0.85], [0.55, 0], [1, 0]].forEach(([o, a]) => svgEl('stop', { offset: o, 'stop-color': '#fff', 'stop-opacity': a }, gr));
      lo.shine = svgEl('g', {}, svg);
      L.glyphs.forEach((d) => svgEl('path', { d, fill: `url(#${id})`, transform: `translate(${L.tx} ${L.ty})` }, lo.shine));
      lo.shineGrad = gr;
    }
    const sp = clamp((t - t0) / 1.1), x = lerp(-300, L.w + 300, E.inOutSine(sp));
    lo.shineGrad.setAttribute('x1', x - 250); lo.shineGrad.setAttribute('x2', x + 250);
    lo.shine.setAttribute('opacity', sp > 0 && sp < 1 ? 1 : 0);
  }

  /* ---------- logo vectoriel en pièces ---------- */
  function makeLogo(parent, height) {
    const w = height * L.w / L.h;
    const wrap = $('div', { position: 'absolute', width: w + 'px', height: height + 'px' }, parent);
    const svg = svgEl('svg', { width: w, height, viewBox: `0 0 ${L.w} ${L.h}`, overflow: 'visible' }, wrap);
    const defs = svgEl('defs', {}, svg);
    const lg = svgEl('linearGradient', { id: 'lg' + height, x1: 0, y1: 0, x2: 1, y2: 1 }, defs);
    L.grad.forEach(([o, c]) => svgEl('stop', { offset: o, 'stop-color': c }, lg));
    const [bx, by, bw, bh] = L.sym.block;
    const g = (tx, ty) => svgEl('g', {}, svg);
    const dome = svgEl('g', {}, svg); svgEl('path', { d: L.sym.dome, fill: `url(#lg${height})` }, dome);
    const quarter = svgEl('g', {}, svg); svgEl('path', { d: L.sym.quarter, fill: '#F5F2EA' }, quarter);
    const block = svgEl('g', {}, svg); svgEl('rect', { x: bx, y: by, width: bw, height: bh, fill: '#E4F53C' }, block);
    const glyphs = L.glyphs.map((d) => { const gg = svgEl('g', {}, svg); svgEl('path', { d, fill: '#F5F2EA', transform: `translate(${L.tx} ${L.ty})` }, gg); return gg; });
    return { wrap, w, h: height, dome, quarter, block, glyphs, sym: [bx, by, bw, bh] };
  }
  const gT = (e, x, y, s = 1, r = 0, o = 1, cx = 0, cy = 0) => { e.setAttribute('transform', `translate(${x} ${y}) translate(${cx} ${cy}) rotate(${r}) scale(${s}) translate(${-cx} ${-cy})`); e.setAttribute('opacity', o); };
  /* Assemblage du symbole (t local en secondes) puis apparition du mot, lettre par lettre. */
  function animLogo(lo, t, wordAt = 0.75) {
    const pb = spring(t - 0.0, 210, 13), pd = spring(t - 0.12, 180, 15), pq = spring(t - 0.24, 180, 15);
    gT(lo.block, 0, (1 - pb) * -260, Math.max(0.001, pb), (1 - pb) * 90, clamp(t * 6), 203, 71);
    gT(lo.dome, (1 - pd) * -320, 0, 1, (1 - pd) * -40, clamp((t - 0.12) * 6), 78, 57);
    gT(lo.quarter, 0, (1 - pq) * 300, 1, (1 - pq) * 60, clamp((t - 0.24) * 6), 94, 183);
    lo.glyphs.forEach((g, i) => { const p = E.outQuint(P(t, wordAt + i * 0.045, 0.6)); gT(g, 0, (1 - p) * 120, 1, 0, p); });
  }

  /* ---------- textes ---------- */
  const TXT = { fontFamily: "'Plus Jakarta Sans', sans-serif", fontWeight: '800', color: '#F5F2EA', letterSpacing: '-0.035em', whiteSpace: 'nowrap' };
  function words(parent, html, css) {
    const box = $('div', Object.assign({ position: 'absolute', left: '0', width: W + 'px', textAlign: 'center' }, TXT, css), parent);
    const out = [];
    html.split(' ').forEach((wd, i, arr) => {
      const m = wd.match(/^\*(.*)\*$/);
      const sp = $('span', { display: 'inline-block', color: m ? '#D7FF3F' : 'inherit', willChange: 'transform' }, box, (m ? m[1] : wd));
      out.push(sp); if (i < arr.length - 1) box.appendChild(document.createTextNode(' '));
    });
    box._w = out; return box;
  }
  const riseWords = (box, t, t0, step = 0.08, d = 0.7, dy = 70) => box._w.forEach((w, i) => { const p = E.outQuint(P(t, t0 + i * step, d)); S(w, { y: (1 - p) * dy, o: p, b: (1 - p) * 10 }); });

  const scene = $('div', { position: 'absolute', inset: '0' });

  /* ================================================================= INTRO (7,2 s) */
  if (MODE === 'intro') {
    window.DURATION = 7.6;
    const camera = $('div', { position: 'absolute', inset: '0', transformOrigin: '960px 400px' }, scene);
    const lo = makeLogo(camera, 176);
    const LX = 960 - lo.w / 2, LY = 360 - lo.h / 2;
    lo.wrap.style.left = LX + 'px'; lo.wrap.style.top = LY + 'px';
    const tag = words(camera, 'Build the apps your teams need.', { top: '588px', fontSize: '66px' });
    const tag2 = words(camera, '*With* *zero* *code.*', { top: '664px', fontSize: '66px' });
    const cards = makeCards(camera, 790);
    const by = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '480px', textAlign: 'center', fontFamily: "'DM Sans', sans-serif", fontWeight: '700', fontSize: '22px', letterSpacing: '0.32em', textTransform: 'uppercase', color: 'rgba(245,242,234,0.55)' }, camera, 'by Leyton CognitX');
    // Rideau de sortie : le bloc citron du logo envahit l'écran, puis l'écran passe au noir.
    const wipe = $('div', { position: 'absolute', left: '0', top: '0', width: '100px', height: '100px', background: '#D7FF3F', transformOrigin: '50% 50%' }, scene);
    const wipe2 = $('div', { position: 'absolute', inset: '0', background: '#0B0B0F' }, scene);
    const flash = $('div', { position: 'absolute', inset: '0', background: 'radial-gradient(circle at 50% 44%, rgba(255,255,255,0.55), rgba(255,255,255,0) 45%)' }, scene);
    flash.style.background = 'radial-gradient(circle at 50% 33%, rgba(255,255,255,0.55), rgba(255,255,255,0) 45%)'; flash.style.backgroundPosition = '0 0';

    window.render = (t) => {
      bgUpdate(t, E.outCubic(P(t, 0, 1.0)), 960, 400, 1040);
      // Le symbole naît plus grand, au centre, puis le mot se dévoile et la composition se cale.
      const lt = t - 0.35;
      animLogo(lo, lt, 0.95);
      const settle = E.inOutCubic(P(t, 1.1, 0.9));
      S(lo.wrap, { s: lerp(1.25, 1, settle), x: lerp(lo.w * 0.36, 0, settle) * (1 - 0) });
      S(flash, { o: Math.max(0, 1 - Math.abs(t - 0.62) / 0.25) * 0.6 });
      by.style.opacity = E.outCubic(P(t, 1.9, 0.6)); S(by, { y: (1 - E.outCubic(P(t, 1.9, 0.6))) * 20, o: E.outCubic(P(t, 1.9, 0.6)) * 0.9 });
      riseWords(tag, t, 2.2, 0.06);
      riseWords(tag2, t, 2.62, 0.09);
      animCards(cards, t, 3.15);
      logoShine(lo, t, 1.85);
      // Légère poussée de caméra pendant toute la séquence, puis plongée vers le bloc citron.
      const push = 1 + 0.035 * E.inOutSine(P(t, 0.5, 6.0));
      S(camera, { s: push });
      // Sortie
      const [bx, by0, bw, bh] = lo.sym; const k = lo.h / L.h;
      const cx = LX + (bx + bw / 2) * k, cy = LY + (by0 + bh / 2) * k;
      const wp = E.inExpo(P(t, 6.35, 0.55));
      wipe.style.left = cx - 50 + 'px'; wipe.style.top = cy - 50 + 'px';
      S(wipe, { s: lerp(bw * k / 100 * push, 32, wp), r: lerp(0, 45, wp), o: t > 6.35 ? 1 : 0 });
      const w2 = E.inOutCubic(P(t, 7.0, 0.4));
      wipe2.style.clipPath = `inset(0 0 0 ${(1 - w2) * 100}%)`; wipe2.style.opacity = t > 7.0 ? 1 : 0;
    };
  }

  /* ================================================================= OUTRO (10 s) */
  if (MODE === 'outro') {
    window.DURATION = 10.0;
    const head = words(scene, 'Thank you for *watching.*', { top: '400px', fontSize: '110px' });
    const camera = $('div', { position: 'absolute', inset: '0', transformOrigin: '960px 420px' }, scene);
    const lo = makeLogo(camera, 150);
    const LX = 960 - lo.w / 2, LY = 300 - lo.h / 2;
    lo.wrap.style.left = LX + 'px'; lo.wrap.style.top = LY + 'px';
    const sub = words(camera, 'Build the apps your teams need. *With* *zero* *code.*', { top: '470px', fontSize: '54px' });
    const cta = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '620px', display: 'flex', justifyContent: 'center' }, camera,
      `<div style="position:relative;overflow:hidden;height:104px;padding:0 18px 0 48px;border-radius:52px;background:#D7FF3F;color:#0B0B0F;display:flex;align-items:center;gap:30px;font:800 42px 'Plus Jakarta Sans',sans-serif;letter-spacing:-0.02em;box-shadow:0 24px 60px rgba(215,255,63,0.25)">Start building on youcraft.tech<span style="display:flex;width:72px;height:72px;border-radius:50%;background:#0B0B0F;align-items:center;justify-content:center">${icon('arrow-up-right', 36, '#D7FF3F', 2.6)}</span><i style="position:absolute;top:-20px;left:0;width:130px;height:150px;background:linear-gradient(100deg,rgba(255,255,255,0),rgba(255,255,255,0.6),rgba(255,255,255,0))"></i></div>`);
    const btn = cta.firstChild, sheen = btn.querySelector('i');
    const ST = [['1,300+', 'users across the Leyton group'], ['500+', 'apps already deployed'], ['0', 'lines of code to write']];
    const stats = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '770px', display: 'flex', justifyContent: 'center', gap: '0' }, camera);
    stats.style.gap = '24px';
    const sts = ST.map(([n, l], i) => $('div', { padding: '22px 34px', minWidth: '300px', boxSizing: 'border-box', borderRadius: '24px', textAlign: 'left', background: 'linear-gradient(160deg, rgba(255,255,255,0.10), rgba(255,255,255,0.02) 60%)', border: '1px solid rgba(255,255,255,0.13)', boxShadow: 'inset 0 1px 0 rgba(255,255,255,0.16), 0 24px 50px rgba(0,0,0,0.4)' }, stats,
      `<div style="font:800 54px 'Plus Jakarta Sans',sans-serif;color:#D7FF3F;letter-spacing:-0.03em">${n}</div><div style="font:400 24px 'DM Sans',sans-serif;color:rgba(245,242,234,0.7);margin-top:4px">${l}</div>`));
    const by = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '985px', textAlign: 'center', fontFamily: "'DM Sans', sans-serif", fontWeight: '700', fontSize: '22px', letterSpacing: '0.32em', textTransform: 'uppercase', color: 'rgba(245,242,234,0.5)' }, camera, 'by Leyton CognitX');
    const intro = $('div', { position: 'absolute', inset: '0', background: '#D7FF3F' }, scene);
    const black = $('div', { position: 'absolute', inset: '0', background: '#0B0B0F' }, scene);

    window.render = (t) => {
      bgUpdate(t, 1, 960, 380, 1060);
      logoShine(lo, t, 4.0);
      // Entrée : rideau citron qui se retire (symétrique de la sortie de l'intro).
      const ip = E.inOutCubic(P(t, 0.0, 0.55));
      intro.style.clipPath = `inset(0 ${ip * 100}% 0 0)`; intro.style.opacity = ip < 1 ? 1 : 0;
      // Remerciement, puis il s'efface vers le haut.
      riseWords(head, t, 0.35, 0.1, 0.8, 90);
      const hout = E.inCubic(P(t, 2.3, 0.5));
      head.style.opacity = 1 - hout; head.style.transform = `translateY(${-hout * 80}px) scale(${1 - hout * 0.05})`; head.style.filter = hout > 0.02 ? `blur(${hout * 12}px)` : 'none';
      // Logo, promesse, appel à l'action, chiffres.
      animLogo(lo, t - 2.75, 0.85);
      const settle = E.inOutCubic(P(t, 3.35, 0.8));
      S(lo.wrap, { s: lerp(1.2, 1, settle), x: lerp(lo.w * 0.36, 0, settle) });
      riseWords(sub, t, 3.9, 0.07);
      const cp = spring(t - 4.7, 190, 15);
      S(cta, { y: (1 - cp) * 70, o: clamp((t - 4.7) * 5) });
      S(btn, { s: 1 + 0.025 * Math.max(0, Math.sin((t - 5.6) * 3.2)) * (t > 5.6 ? 1 : 0) });
      const sh = ((t - 5.4) % 2.2) / 1.0;
      sheen.style.transform = `translateX(${t > 5.4 && sh < 1 ? lerp(-200, 1000, E.inOutSine(sh)) : -200}px) skewX(-20deg)`;
      sts.forEach((s, i) => { const p = E.outQuint(P(t, 5.5 + i * 0.15, 0.7)); S(s, { y: (1 - p) * 40, o: p }); });
      const bp = E.outCubic(P(t, 6.1, 0.7)); S(by, { y: (1 - bp) * 20, o: bp * 0.9 });
      S(camera, { s: 1 + 0.03 * E.inOutSine(P(t, 2.8, 7)) });
      // Fondu au noir final.
      black.style.opacity = E.inOutSine(P(t, 9.1, 0.9));
    };
  }
  document.fonts.ready.then(() => { window.render(0); window.READY = true; });
})();

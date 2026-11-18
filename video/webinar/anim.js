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
  const vign = $('div', { position: 'absolute', inset: '0', background: 'radial-gradient(ellipse at 50% 45%, rgba(0,0,0,0) 55%, rgba(0,0,0,0.55) 100%)' });
  const bgUpdate = (t, k = 1, gx = 960, gy = 470) => {
    S(glowA, { x: gx + 60 * Math.sin(t * 0.5), y: gy + 30 * Math.cos(t * 0.4), s: lerp(0.6, 1, k) + 0.04 * Math.sin(t * 1.3), o: k });
    S(glowB, { x: gx + 260 * Math.cos(t * 0.35), y: gy - 120 + 80 * Math.sin(t * 0.45), o: 0.9 * k });
    grid.style.transform = `rotateX(72deg) translateY(${(t * 60) % 96}px)`; grid.style.opacity = 0.9 * k;
    parts.forEach((p) => { const y = ((p.y - t * p.sp) % H + H) % H; S(p.e, { x: p.x + 14 * Math.sin(t * 0.6 + p.ph), y, o: k * (0.25 + 0.35 * (0.5 + 0.5 * Math.sin(t * 1.7 + p.ph))) }); });
  };

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
    window.DURATION = 7.2;
    const camera = $('div', { position: 'absolute', inset: '0', transformOrigin: '960px 440px' }, scene);
    const lo = makeLogo(camera, 190);
    const LX = 960 - lo.w / 2, LY = 420 - lo.h / 2;
    lo.wrap.style.left = LX + 'px'; lo.wrap.style.top = LY + 'px';
    const tag = words(camera, 'Build the apps your teams need. *No* *code.*', { top: '630px', fontSize: '64px' });
    const CH = [['users', 'CRM'], ['layout-dashboard', 'Dashboards'], ['list-checks', 'Workflows'], ['folder-kanban', 'Project tracking'], ['globe', 'Client portals']];
    const row = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '770px', display: 'flex', justifyContent: 'center', gap: '22px' }, camera);
    const chips = CH.map(([i, t]) => $('div', { display: 'flex', alignItems: 'center', gap: '14px', padding: '16px 28px 16px 20px', borderRadius: '40px', background: 'rgba(245,242,234,0.06)', border: '1.5px solid rgba(245,242,234,0.16)',
      fontFamily: "'DM Sans', sans-serif", fontWeight: '500', fontSize: '30px', color: '#F5F2EA', backdropFilter: 'blur(6px)' }, row,
      `<span style="display:flex;width:44px;height:44px;border-radius:50%;background:#1E3BFF;align-items:center;justify-content:center">${icon(i, 24, '#fff', 2.2)}</span>${t}`));
    const by = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '548px', textAlign: 'center', fontFamily: "'DM Sans', sans-serif", fontWeight: '700', fontSize: '24px', letterSpacing: '0.32em', textTransform: 'uppercase', color: 'rgba(245,242,234,0.55)' }, camera, 'by Leyton CognitX');
    // Rideau de sortie : le bloc citron du logo envahit l'écran, puis l'écran passe au noir.
    const wipe = $('div', { position: 'absolute', left: '0', top: '0', width: '100px', height: '100px', background: '#D7FF3F', transformOrigin: '50% 50%' }, scene);
    const wipe2 = $('div', { position: 'absolute', inset: '0', background: '#0B0B0F' }, scene);
    const flash = $('div', { position: 'absolute', inset: '0', background: 'radial-gradient(circle at 50% 44%, rgba(255,255,255,0.55), rgba(255,255,255,0) 45%)' }, scene);
    flash.style.background = 'radial-gradient(circle at 50% 39%, rgba(255,255,255,0.55), rgba(255,255,255,0) 45%)';

    window.render = (t) => {
      bgUpdate(t, E.outCubic(P(t, 0, 1.0)), 960, 430);
      // Le symbole naît plus grand, au centre, puis le mot se dévoile et la composition se cale.
      const lt = t - 0.35;
      animLogo(lo, lt, 0.95);
      const settle = E.inOutCubic(P(t, 1.1, 0.9));
      S(lo.wrap, { s: lerp(1.25, 1, settle), x: lerp(lo.w * 0.36, 0, settle) * (1 - 0) });
      S(flash, { o: Math.max(0, 1 - Math.abs(t - 0.62) / 0.25) * 0.6 });
      by.style.opacity = E.outCubic(P(t, 1.9, 0.6)); S(by, { y: (1 - E.outCubic(P(t, 1.9, 0.6))) * 20, o: E.outCubic(P(t, 1.9, 0.6)) * 0.9 });
      riseWords(tag, t, 2.3, 0.07);
      chips.forEach((c, i) => { const p = spring(t - (3.25 + i * 0.12), 200, 16); S(c, { y: (1 - p) * 60, s: lerp(0.85, 1, clamp(p)), o: clamp((t - (3.25 + i * 0.12)) * 5) }); });
      // Légère poussée de caméra pendant toute la séquence, puis plongée vers le bloc citron.
      const push = 1 + 0.035 * E.inOutSine(P(t, 0.5, 5.5));
      S(camera, { s: push });
      // Sortie
      const [bx, by0, bw, bh] = lo.sym; const k = lo.h / L.h;
      const cx = LX + (bx + bw / 2) * k, cy = LY + (by0 + bh / 2) * k;
      const wp = E.inExpo(P(t, 5.95, 0.55));
      wipe.style.left = cx - 50 + 'px'; wipe.style.top = cy - 50 + 'px';
      S(wipe, { s: lerp(bw * k / 100 * push, 32, wp), r: lerp(0, 45, wp), o: t > 5.95 ? 1 : 0 });
      const w2 = E.inOutCubic(P(t, 6.6, 0.4));
      wipe2.style.clipPath = `inset(0 0 0 ${(1 - w2) * 100}%)`; wipe2.style.opacity = t > 6.6 ? 1 : 0;
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
    const sub = words(camera, 'Build the apps your teams need. *No* *code.*', { top: '480px', fontSize: '56px' });
    const cta = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '620px', display: 'flex', justifyContent: 'center' }, camera,
      `<div style="position:relative;overflow:hidden;height:104px;padding:0 18px 0 48px;border-radius:52px;background:#D7FF3F;color:#0B0B0F;display:flex;align-items:center;gap:30px;font:800 42px 'Plus Jakarta Sans',sans-serif;letter-spacing:-0.02em;box-shadow:0 24px 60px rgba(215,255,63,0.25)">Start building on youcraft.build<span style="display:flex;width:72px;height:72px;border-radius:50%;background:#0B0B0F;align-items:center;justify-content:center">${icon('arrow-up-right', 36, '#D7FF3F', 2.6)}</span><i style="position:absolute;top:-20px;left:0;width:130px;height:150px;background:linear-gradient(100deg,rgba(255,255,255,0),rgba(255,255,255,0.6),rgba(255,255,255,0))"></i></div>`);
    const btn = cta.firstChild, sheen = btn.querySelector('i');
    const ST = [['1,300+', 'users across the Leyton group'], ['500+', 'apps already deployed'], ['0', 'lines of code to write']];
    const stats = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '800px', display: 'flex', justifyContent: 'center', gap: '0' }, camera);
    const sts = ST.map(([n, l], i) => $('div', { padding: '0 54px', borderLeft: i ? '1.5px solid rgba(245,242,234,0.16)' : 'none', textAlign: 'left' }, stats,
      `<div style="font:800 54px 'Plus Jakarta Sans',sans-serif;color:#D7FF3F;letter-spacing:-0.03em">${n}</div><div style="font:400 24px 'DM Sans',sans-serif;color:rgba(245,242,234,0.7);margin-top:4px">${l}</div>`));
    const by = $('div', { position: 'absolute', left: '0', width: W + 'px', top: '980px', textAlign: 'center', fontFamily: "'DM Sans', sans-serif", fontWeight: '700', fontSize: '22px', letterSpacing: '0.32em', textTransform: 'uppercase', color: 'rgba(245,242,234,0.5)' }, camera, 'by Leyton CognitX');
    const intro = $('div', { position: 'absolute', inset: '0', background: '#D7FF3F' }, scene);
    const black = $('div', { position: 'absolute', inset: '0', background: '#0B0B0F' }, scene);

    window.render = (t) => {
      bgUpdate(t, 1, 960, 420);
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

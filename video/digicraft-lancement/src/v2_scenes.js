(function () {
  const { h, S, P, Ease, lerp, clamp, words, chars, revealWords, hideWords, icon, spring } = E;
  const el = (parent, style, html) => { const e = h('div', 'abs', parent, html); Object.assign(e.style, style); return e; };
  const slamChars = (T, arr, t0, st = 0.03, from = 1.9) => arr.forEach((c, i) => {
    const p = P(T, t0 + i * st, 0.5, Ease.outExpo);
    S(c, { s: lerp(from, 1, p), o: p > 0 ? clamp(p * 3) : 0, blur: (1 - p) * 18, y: (1 - p) * -20 });
  });
  const SLANT = 620;
  const panelClip = (T, tin, tout) => {
    const pi = Ease.inOutQuart(clamp((T - tin) / 0.32));
    const po = Ease.inOutQuart(clamp((T - tout) / 0.32));
    const W = 1920 + SLANT;
    const r = lerp(0, W, pi), l = lerp(0, W, po);
    return `polygon(${l}px 0, ${r}px 0, ${r - SLANT}px 1080px, ${l - SLANT}px 1080px)`;
  };

  // 1. Accroche : une idée qui attend.
  let a = {};
  SCENES.push({
    id: 'hook',
    build(root) {
      root.style.background = 'var(--offwhite)';
      a.grid = el(root, { inset: '-64px' }); a.grid.className = 'abs bg-grid-light';
      a.blob = el(root, { width: '1100px', height: '1100px', left: '400px', top: '-200px', background: 'rgba(46,139,192,0.10)' }); a.blob.className = 'blob';
      const t = el(root, { left: '0', width: '1920px', top: '250px', textAlign: 'center', fontWeight: '800', letterSpacing: '-0.035em', lineHeight: '0.98' });
      a.l1 = chars(el(t, { position: 'relative', fontSize: '230px', color: 'var(--navy)' }), 'Une idée');
      a.l2 = chars(el(t, { position: 'relative', fontSize: '230px', color: 'var(--blue)' }), "d'outil ?");
      a.t = t;
      const cards = [['Notes de frais', 'receipt', '#2E8BC0', 250, 170], ['Onboarding RH', 'user-check', '#8F8AFF', 1560, 150], ['Planning équipes', 'calendar', '#FF6633', 190, 860], ['Reporting mensuel', 'chart-column', '#022446', 1600, 880], ['Validation devis', 'badge-check', '#2E8BC0', 980, 110], ['Suivi des congés', 'plane', '#8F8AFF', 950, 960]];
      a.cards = cards.map(([lab, ic, col, x, y], i) => {
        const c = el(root, { left: x - 190 + 'px', top: y - 45 + 'px', display: 'flex', alignItems: 'center', gap: '16px', padding: '16px 28px 16px 16px', borderRadius: '22px', background: '#fff', boxShadow: '0 16px 40px rgba(2,36,70,0.12)', border: '1px solid #E6ECF2', whiteSpace: 'nowrap' },
          `<div style="width:60px;height:60px;border-radius:16px;background:${col}1A;display:flex;align-items:center;justify-content:center">${icon(ic, 32, 2.2, col)}</div><div style="font-size:30px;font-weight:700;color:#022446">${lab}</div>`);
        c._i = i; return c;
      });
      const w = el(root, { left: '0', width: '1920px', top: '215px', textAlign: 'center' });
      a.wait = w;
      a.lab = el(w, { position: 'relative', display: 'inline-flex', alignItems: 'center', gap: '14px', padding: '14px 28px', borderRadius: '40px', background: '#FFE8E0', color: '#C2410C', fontSize: '30px', fontWeight: '700', letterSpacing: '0.12em', textTransform: 'uppercase' }, `${icon('hourglass', 32, 2.4, '#FF6633')}<span>File d'attente informatique</span>`);
      a.hg = a.lab.querySelector('svg');
      a.cnt = el(w, { position: 'relative', fontSize: '300px', fontWeight: '900', color: 'var(--orange)', letterSpacing: '-0.04em', lineHeight: '1.05', marginTop: '10px' }, 'J+1');
      a.sub = words(el(w, { position: 'relative', fontSize: '96px', fontWeight: '800', color: 'var(--navy)', letterSpacing: '-0.02em' }), [{ t: 'Des' }, { t: 'mois' }, { t: "d'attente.", c: '' }]);
      a.bar = el(root, { left: '660px', top: '905px', width: '600px', height: '14px', borderRadius: '7px', background: '#E2E8F0', overflow: 'hidden' });
      a.fill = el(a.bar, { left: '0', top: '0', height: '14px', width: '600px', borderRadius: '7px', background: 'var(--orange)', transformOrigin: '0 50%' });
    },
    update(T) {
      S(a.grid, { x: -T * 10, y: -T * 6 });
      S(a.blob, { x: Math.sin(T) * 60 });
      slamChars(T, a.l1._c, 0.02, 0.035);
      slamChars(T, a.l2._c, 0.4, 0.035);
      const out = P(T, 1.45, 0.35, Ease.inExpo);
      S(a.t, { s: 1 - out * 0.3, o: 1 - out, blur: out * 20, y: -out * 120 });
      a.cards.forEach((c, i) => {
        const t0 = 0.55 + i * 0.07;
        const p = spring(T - t0, 240, 16);
        const g = P(T, 1.6, 1.8, Ease.inOutSine);
        const fall = P(T, 2.2 + i * 0.05, 1.8, Ease.inCubic);
        S(c, { s: Math.max(0.0001, p * (1 - 0.15 * g)), o: clamp((T - t0) * 8) * (1 - 0.4 * g), y: fall * 90 + Math.sin(T * 2 + i) * 6, r: (i % 2 ? 4 : -4) * g, f: `grayscale(${g})` });
      });
      const wi = P(T, 1.55, 0.45, Ease.outExpo);
      S(a.wait, { o: wi, s: lerp(1.25, 1, wi), blur: (1 - wi) * 16 });
      const days = Math.round(1 + 179 * Ease.inCubic(clamp((T - 1.7) / 1.8)));
      a.cnt.textContent = 'J+' + days;
      S(a.cnt, { s: 1 + 0.04 * Math.abs(Math.sin(T * 18)) * P(T, 1.7, 1.8) });
      S(a.hg, { r: T * 240 });
      revealWords(T, a.sub, 2.15, 0.09, 0.5);
      const bp = P(T, 1.8, 0.4);
      S(a.bar, { o: bp });
      S(a.fill, { sx: 0.03 + 0.02 * Math.abs(Math.sin(T * 5)) });
    },
  });

  // 2. Et si c'était aujourd'hui ?
  let q = {};
  SCENES.push({
    id: 'question',
    build(root) {
      q.root = root;
      root.classList.add('bg-navy');
      q.grid = el(root, { inset: '-64px' }); q.grid.className = 'abs bg-grid-dark';
      q.wm = h('img', 'abs', root); q.wm.src = '../assets/img/logo/x_watermark.png';
      Object.assign(q.wm.style, { width: '1300px', height: '1300px', left: '900px', top: '-200px', opacity: '0.5' });
      const t = el(root, { left: '0', width: '1920px', top: '300px', textAlign: 'center', fontWeight: '800', color: '#fff', letterSpacing: '-0.03em' });
      q.t = t;
      q.l1 = words(el(t, { position: 'relative', fontSize: '120px' }), "Et si c'était");
      q.l2 = chars(el(t, { position: 'relative', fontSize: '250px', color: 'var(--blue-light)', lineHeight: '1' }), "aujourd'hui ?");
      q.bands = E.slashBands(root);
    },
    update(T) {
      E.slash(T, 3.85, 0.45, q.root, q.bands);
      S(q.grid, { x: -T * 12 });
      S(q.wm, { r: T * 6, s: 1 + T * 0.01 });
      revealWords(T, q.l1, 4.0, 0.07, 0.45);
      slamChars(T, q.l2._c, 4.35, 0.03, 1.7);
      const z = P(T, 5.15, 0.4, Ease.inExpo);
      S(q.t, { s: 1 + z * 1.2, o: 1 - z, blur: z * 12 });
    },
  });

  // 3. Voici DigiCraft.
  let f = {};
  SCENES.push({
    id: 'flash',
    z: 20,
    build(root) {
      f.root = root;
      root.style.background = 'var(--offwhite)';
      f.grid = el(root, { inset: '-64px' }); f.grid.className = 'abs bg-grid-light';
      f.b1 = el(root, { width: '1000px', height: '1000px', left: '-200px', top: '-300px', background: 'rgba(143,138,255,0.18)' }); f.b1.className = 'blob';
      f.b2 = el(root, { width: '900px', height: '900px', right: '-200px', bottom: '-350px', background: 'rgba(46,139,192,0.15)' }); f.b2.className = 'blob';
      const lock = el(root, { left: '0', width: '1920px', top: '380px', height: '240px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '50px' });
      f.lock = lock;
      const ic = h('div', '', lock);
      Object.assign(ic.style, { position: 'relative', width: '200px', height: '200px', flex: 'none' });
      const D = (cx, cy, r) => {
        const side = r * Math.SQRT2 * 2;
        return el(ic, { left: cx * 2 - side / 2 + 'px', top: cy * 2 - side / 2 + 'px', width: side + 'px', height: side + 'px', borderRadius: side * 0.12 + 'px', background: 'linear-gradient(135deg,#B7A6FF,#6D5DF6)' });
      };
      f.dia = [D(50, 50, 17), D(50, 14, 9), D(86, 50, 9), D(50, 86, 9), D(14, 50, 9)];
      const word = h('div', '', lock);
      Object.assign(word.style, { fontSize: '210px', fontWeight: '800', letterSpacing: '-0.035em', color: 'var(--navy)', lineHeight: '1', paddingBottom: '22px' });
      f.digi = chars(word, 'Digi');
      f.craft = chars(word, 'Craft');
      f.craft.style.color = 'var(--violet)';
      f.by = el(root, { left: '0', width: '1920px', top: '680px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '20px', fontSize: '30px', fontWeight: '600', color: 'var(--gray-2)' }, `<span>par</span><img src="../assets/img/logo/leyton_cognitx_navy.png" style="height:96px">`);
      f.ring = el(root, { borderRadius: '50%', border: '26px solid var(--violet)', boxSizing: 'border-box' });
    },
    update(T) {
      const ip = P(T, 5.4, 0.3, Ease.inOutCubic);
      const R = lerp(0, 1250, ip);
      f.root.style.clipPath = ip < 1 ? `circle(${R}px at 960px 560px)` : 'none';
      Object.assign(f.ring.style, { left: 960 - R + 'px', top: 560 - R + 'px', width: 2 * R + 'px', height: 2 * R + 'px', display: ip > 0 && ip < 1 ? 'block' : 'none' });
      S(f.grid, { x: -T * 10 });
      S(f.b1, { x: Math.sin(T) * 80 });
      const dirs = [[0, 0], [0, -1], [1, 0], [0, 1], [-1, 0]];
      f.dia.forEach((d, i) => {
        const t0 = 5.5 + (i ? 0.05 + i * 0.03 : 0);
        const p = spring(T - t0, 260, 14);
        const [dx, dy] = dirs[i];
        S(d, { x: dx * (1 - p) * 300, y: dy * (1 - p) * 300, r: 45 + (1 - p) * 270, s: Math.max(0.0001, i ? 0.3 + 0.7 * p : p), o: clamp((T - t0) * 10) });
      });
      [...f.digi._c, ...f.craft._c].forEach((c, i) => {
        const p = P(T, 5.58 + i * 0.03, 0.5, Ease.outExpo);
        S(c, { x: (1 - p) * 120, o: p, blur: (1 - p) * 14 });
      });
      const bp = P(T, 6.0, 0.4, Ease.outQuint);
      S(f.by, { y: (1 - bp) * 30, o: bp * (1 - P(T, 6.7, 0.2)) });
      const z = P(T, 6.75, 0.5, Ease.inExpo);
      S(f.lock, { s: 1 + 0.04 * P(T, 5.6, 1.2, Ease.outSine) + z * 4, o: 1 - P(T, 6.95, 0.25), blur: z * 10 });
      S(f.root, { o: 1 - P(T, 6.95, 0.3, Ease.inCubic) });
    },
  });

  // 4. Titres superposés pendant la démo.
  let t = {};
  SCENES.push({
    id: 'titles',
    z: 30,
    build(root) {
      root.style.pointerEvents = 'none';
      t.d = el(root, { left: '80px', top: '820px', padding: '26px 44px', borderRadius: '28px', background: 'var(--navy)', boxShadow: '0 24px 60px rgba(2,36,70,0.35)', fontSize: '84px', fontWeight: '800', color: '#fff', letterSpacing: '-0.02em', whiteSpace: 'nowrap' });
      t.dW = words(t.d, [{ t: 'Vous' }, { t: 'décrivez.', c: 'hl-light' }]);
      t.dIc = el(t.d, { right: '-34px', top: '-34px', width: '76px', height: '76px', borderRadius: '38px', background: 'var(--violet)', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 10px 24px rgba(109,93,246,0.4)' }, icon('message-square', 38, 2.4, '#fff'));
      const b = el(root, { left: '86px', top: '300px', fontSize: '112px', fontWeight: '800', lineHeight: '1', letterSpacing: '-0.035em' });
      t.b1 = chars(el(b, { position: 'relative', color: 'var(--navy)' }), 'DigiCraft');
      t.b2 = chars(el(b, { position: 'relative', color: 'var(--blue)' }), 'construit.');
      t.bSub = el(b, { position: 'relative', marginTop: '26px', fontSize: '36px', fontWeight: '600', color: 'var(--gray)', letterSpacing: '0', display: 'flex', alignItems: 'center', gap: '12px' }, `${icon('eye', 34, 2.2, '#2E8BC0')}Sous vos yeux, en direct.`);
      t.b = b;

      t.panel = el(root, { inset: '0', background: 'linear-gradient(115deg,#011A33 0%,#022446 50%,#04305A 100%)' });
      t.band = el(root, { inset: '0', background: 'var(--blue)' });
      const mk = (lines) => {
        const w = el(t.panel, { left: '0', width: '1920px', top: '0', height: '1080px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '60px' });
        const z = el(w, { position: 'relative', fontSize: '520px', fontWeight: '900', color: 'var(--blue-light)', letterSpacing: '-0.05em', lineHeight: '1' }, '0');
        const tx = h('div', '', w);
        Object.assign(tx.style, { fontSize: '140px', fontWeight: '800', color: '#fff', lineHeight: '1.02', letterSpacing: '-0.03em' });
        const ls = lines.map((l) => words(tx, l));
        return { w, z, ls };
      };
      t.s1 = mk(['développeur.']);
      t.s2 = mk(['ticket', [{ t: 'à' }, { t: 'la' }, { t: 'DSI.', c: 'hl-light' }]]);

      const o = el(root, { left: '110px', top: '330px', fontSize: '150px', fontWeight: '800', lineHeight: '1', letterSpacing: '-0.04em' });
      t.o1 = chars(el(o, { position: 'relative', color: 'var(--navy)' }), 'En ligne.');
      t.o2 = chars(el(o, { position: 'relative', color: 'var(--blue)', fontSize: '112px', marginTop: '14px' }), 'Le jour même.');
      t.o = o;
    },
    update(T) {
      const din = P(T, 7.25, 0.45, Ease.outExpo), dout = P(T, 9.3, 0.3, Ease.inCubic);
      S(t.d, { x: (1 - din) * -500 - dout * 600, o: din > 0 ? 1 - dout : 0, r: (1 - din) * -6 });
      revealWords(T, t.dW, 7.35, 0.1, 0.45);
      S(t.dIc, { s: Math.max(0.0001, spring(T - 7.55, 300, 14)) });

      slamChars(T, t.b1._c, 9.62, 0.03, 1.6);
      slamChars(T, t.b2._c, 9.9, 0.03, 1.6);
      const sp = P(T, 10.45, 0.5, Ease.outQuint);
      S(t.bSub, { y: (1 - sp) * 30, o: sp });
      const bo = P(T, 12.2, 0.3, Ease.inCubic);
      S(t.b, { x: -bo * 300, o: 1 - bo, blur: bo * 10 });

      t.panel.style.clipPath = panelClip(T, 12.35, 14.72);
      const bi = Ease.inOutQuart(clamp((T - 12.3) / 0.32)), bo2 = Ease.inOutQuart(clamp((T - 14.67) / 0.32));
      const W = 1920 + SLANT;
      const r = lerp(0, W, bi), l = lerp(0, W, bo2);
      t.band.style.clipPath = `polygon(${l}px 0, ${r}px 0, ${r - SLANT}px 1080px, ${l - SLANT}px 1080px)`;
      t.band.style.display = (T > 12.3 && T < 12.75) || (T > 14.67 && T < 15.1) ? 'block' : 'none';
      t.panel.style.display = T > 12.35 && T < 15.1 ? 'block' : 'none';
      [[t.s1, 12.5, 13.45], [t.s2, 13.5, 20]].forEach(([s, a0, a1]) => {
        const on = T >= a0 && T < a1;
        s.w.style.display = on ? 'flex' : 'none';
        if (!on) return;
        const p = P(T, a0, 0.4, Ease.outExpo);
        S(s.z, { s: lerp(2.2, 1, p), blur: (1 - p) * 20, o: p, r: (1 - p) * -12 });
        s.ls.forEach((ln, i) => revealWords(T, ln, a0 + 0.08 + i * 0.1, 0.07, 0.4));
        S(s.w, { s: 1 + (T - a0) * 0.04 });
      });

      slamChars(T, t.o1._c, 15.25, 0.03, 1.6);
      t.o2._c.forEach((c, i) => {
        const p = P(T, 15.55 + i * 0.025, 0.45, Ease.outExpo);
        S(c, { y: (1 - p) * 60, o: p });
      });
      const oo = P(T, 16.75, 0.3, Ease.inCubic);
      S(t.o, { o: 1 - oo, x: -oo * 200 });
    },
  });
})();

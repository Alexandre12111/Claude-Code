// Scènes YouCraft : plans métier sur mesure (RH, supply chain, maintenance), modèles et mosaïque de dashboards
// métier, symbole YouCraft (apparition, « Meet YouCraft », galaxie qui forme le symbole, signature finale).
(function () {
  const { h, S, P, Ease, lerp, clamp, spring, icon, seeded } = E;
  const el = C.el;
  const INK = '#0B0B0F', STONE = '#F5F2EA', UM = '#1E3BFF', UMD = '#4F6BFF', LIME = '#D7FF3F';
  const L0 = window.YC_LOGO;
  const [bx, by, bw, bh] = L0.sym.block;

  // Symbole en trois calques (dôme, quart, bloc lime), centré dans une boîte carrée.
  let gid = 0;
  function symbol3(parent, size, dark) {
    const box = el(parent, { width: size + 'px', height: size + 'px' });
    const id = `s8g${++gid}`;
    const grad = `<linearGradient id="${id}" x1="0" y1="0" x2="1" y2="1">${L0.grad.map(([o, c]) => `<stop offset="${o}" stop-color="${c}"/>`).join('')}</linearGradient>`;
    const layer = (inner) => { const s = document.createElementNS('http://www.w3.org/2000/svg', 'svg'); s.setAttribute('viewBox', '0 0 248 247'); s.setAttribute('width', size); s.setAttribute('height', size); s.style.position = 'absolute'; s.style.left = '0'; s.style.top = '0'; s.style.overflow = 'visible'; s.innerHTML = inner; box.appendChild(s); return s; };
    const parts = [layer(`<defs>${grad}</defs><path d="${L0.sym.dome}" fill="url(#${id})"/>`), layer(`<path d="${L0.sym.quarter}" fill="${dark ? STONE : INK}"/>`), layer(`<rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="#E4F53C"/>`)];
    parts.forEach((p) => (p.style.transformOrigin = '50% 50%'));
    return { box, parts };
  }
  // Directions d'arrivée des trois pièces (comme l'animation officielle du kit).
  const DIRS = [[-1, -1], [-0.4, 1], [1, -0.4]];

  // ---------- Plans métier : RH, supply chain, maintenance ----------
  {
    const a = {};
    const shot = (root, img, drift) => {
      const g = el(root, { inset: '0', overflow: 'hidden' });
      const f = U.scenery(g, img, { drift, parts: false });
      el(g, { inset: '0', background: 'radial-gradient(120% 90% at 50% 40%, rgba(11,11,15,0.10) 40%, rgba(11,11,15,0.45) 100%)' });
      const cam = el(g, { inset: '0', perspective: '2200px' });
      const rig = el(cam, { inset: '0', transformStyle: 'preserve-3d' });
      return { g, f, rig };
    };
    replaceScene({
      id: 'cards',
      build(root) {
        a.A = shot(root, 'b50/soft_skyline', 1);
        const cA = el(a.A.rig, { left: '330px', top: '200px', width: '1260px', height: '680px' }); cA.className = 'glass ui';
        a.uA = U.bigPeople(cA);
        a.B = shot(root, 'b50/soft_citydusk', -1);
        const cB = el(a.B.rig, { left: '300px', top: '210px', width: '1320px', height: '660px' }); cB.className = 'glass ui';
        a.uB = U.bigFleet(cB);
        a.Cc = shot(root, 'b50/soft_citynight', 1);
        const cC = el(a.Cc.rig, { left: '260px', top: '170px', width: '1400px', height: '740px' }); cC.className = 'glass ui';
        a.uC = U.bigPlants(cC);
        a.sweep = U.sweep(root);
      },
      update(T) {
        U.whip(a.A.g, T, 3.08, 4.6, 1);
        U.whip(a.B.g, T, 4.62, 6.2, 1);
        U.whip(a.Cc.g, T, 6.22, 7.72, 1);
        a.A.f(T); a.B.f(T); a.Cc.f(T);
        const cam = (rig, t0) => { const u = T - t0; S(rig, { s: 1.04 + u * 0.035, ry: lerp(-7, 5, clamp(u / 1.8)), rx: 4 - u * 1.5, y: -u * 10 }); };
        cam(a.A.rig, 3.3); cam(a.B.rig, 4.65); cam(a.Cc.rig, 6.25);
        a.uA(T, 3.3); a.uB(T, 4.7); a.uC(T, 6.25);
        // Entrée du plan A en zoom, passage B → C en zoom traversant.
        if (T < 3.75) { const p = P(T, 3.05, 0.55, Ease.outCubic); S(a.A.g, { s: lerp(1.35, 1, p), o: clamp(p * 2), blur: (1 - p) * 20 }); }
        if (T > 5.9 && T < 6.8) {
          const p = P(T, 5.95, 0.32, Ease.inCubic);
          S(a.B.g, { s: 1 + p * 0.6, o: 1 - p, blur: p * 24 });
          const q = P(T, 6.1, 0.45, Ease.outCubic);
          S(a.Cc.g, { s: lerp(0.8, 1, q), o: q, blur: (1 - q) * 18 });
        }
        a.sweep(T, 5.98, 0.45);
      },
    });
  }

  // ---------- Modèles en carrousel : aperçus métier ----------
  {
    const a = {};
    const TPL = [['users', 'HR onboarding', 'People', 0], ['map-pin', 'Fleet tracking', 'Supply chain', 1], ['gauge', 'Maintenance planner', 'Operations', 3], ['target', 'Sales pipeline', 'Sales', 4],
      ['layers', 'Warehouse stock', 'Logistics', 2], ['calendar', 'Shift planning', 'Operations', 5], ['user-plus', 'Recruitment', 'People', 7], ['receipt', 'Expense approvals', 'Finance', 10]];
    replaceScene({
      id: 'templates',
      build(root) {
        a.root = root;
        a.bg = U.scenery(root, 'b49/gradient_deep', { drift: 0.4, parts: false, tint: 'rgba(11,11,15,0.30)' });
        a.cam = el(root, { inset: '0', perspective: '2000px' });
        a.cards = TPL.map(([ic, t, d, k], i) => {
          const c = el(a.cam, { left: '720px', top: '250px', width: '480px', height: '560px', borderRadius: '30px', background: '#FBFAF6', boxShadow: '0 40px 100px rgba(11,11,15,0.45)', overflow: 'hidden' });
          c.className = 'abs ui';
          const pv = el(c, { left: '0', right: '0', top: '0', height: '300px', background: '#F1EEE7', borderBottom: '1px solid #E6E1D6', overflow: 'hidden' });
          const mt = U.dash(pv, k, false);
          Object.assign(mt.style, { boxShadow: 'none', border: 'none', borderRadius: '0' });
          S(mt, { x: 23, y: 3, s: 1.55 });
          const dark = i % 2 === 1;
          el(c, { left: '0', right: '0', top: '300px', bottom: '0', background: dark ? INK : '#FBFAF6' });
          el(c, { left: '30px', top: '330px', width: '56px', height: '56px', borderRadius: '16px', background: dark ? LIME : UM, display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 30, 2.2, dark ? INK : '#fff'));
          el(c, { left: '30px', top: '404px', fontFamily: 'Plus Jakarta Sans', fontSize: '30px', fontWeight: '800', letterSpacing: '-0.02em', color: dark ? STONE : INK }, t);
          el(c, { left: '30px', top: '448px', fontSize: '19px', color: dark ? '#B5B0A5' : '#77746E' }, `${d} · template`);
          el(c, { left: '30px', top: '494px', display: 'flex', gap: '8px' }).append(U.badge(h('div'), 'Ready to use', 'ok'));
          return c;
        });
        a.title = el(root, { left: '0', width: '1920px', top: '120px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontSize: '56px', fontWeight: '800', letterSpacing: '-0.03em', color: STONE, textShadow: '0 6px 30px rgba(11,11,15,0.5)' }, 'Start from an idea, or a template.');
      },
      update(T) {
        a.root.style.opacity = String(P(T, 16.8, 0.3, Ease.inOutSine));
        a.bg(T);
        const pos = lerp(-0.6, 2.4, Ease.inOutSine(clamp((T - 16.8) / 1.65)));
        a.cards.forEach((c, i) => {
          const d = i - pos;
          S(c, { x: d * 520, z: -Math.abs(d) * 140, ry: -d * 16, o: clamp(1.7 - Math.abs(d) * 0.4) });
          c.style.zIndex = String(100 - Math.round(Math.abs(d) * 10));
        });
        const tp = P(T, 16.95, 0.5, Ease.outQuint), out = P(T, 18.15, 0.3, Ease.inOutSine);
        S(a.title, { o: tp * (1 - out), y: (1 - tp) * 20 });
        a.cam.style.opacity = String(1 - out * 0.6);
      },
    });
  }

  // ---------- Mosaïque de dashboards métier ----------
  {
    const a = {};
    replaceScene({
      id: 'grid',
      build(root) {
        a.root = root;
        a.bg = U.scenery(root, 'b47/waves_white', { drift: 0.3, parts: false });
        el(root, { inset: '0', background: 'rgba(245,242,234,0.55)' });
        a.bands = E.slashBands(root, C.BANDS.navy);
        a.cam = el(root, { inset: '0', transformOrigin: '50% 50%' });
        const TW = 540, TH = 360, GX = 40, GY = 36, X0 = (1920 - 3 * TW - 2 * GX) / 2, Y0 = 130;
        a.tiles = [[0, false], [1, false], [3, true], [4, true], [2, false], [5, false]].map(([k, dark], i) => {
          const t = el(a.cam, { left: X0 + (i % 3) * (TW + GX) + 'px', top: Y0 + Math.floor(i / 3) * (TH + GY) + 'px', width: TW + 'px', height: TH + 'px', borderRadius: '26px', overflow: 'hidden', boxShadow: '0 30px 70px rgba(11,11,15,0.22)' });
          const mt = U.dash(t, k, dark);
          Object.assign(mt.style, { boxShadow: 'none', border: 'none', borderRadius: '0' });
          S(mt, { x: 5, y: 0, s: 1.895 });
          t.style.background = dark ? '#16161C' : '#FBFAF6';
          return t;
        });
        a.feat = el(root, { left: '0', width: '1920px', top: '946px', display: 'flex', justifyContent: 'center', gap: '16px' });
        a.pills = [['lock', 'Secure sign-in'], ['shield-check', 'Roles & permissions'], ['server', 'Hosted in Europe'], ['rocket', 'One-click publish']].map(([ic, t]) => {
          const p = h('span', 'pill ui', a.feat, `${icon(ic, 18, 2.2, LIME)}${t}`);
          Object.assign(p.style, { background: INK, color: STONE, height: '50px', padding: '0 24px', fontSize: '18px', borderRadius: '25px', boxShadow: '0 16px 40px rgba(11,11,15,0.25)' });
          return p;
        });
      },
      update(T) {
        a.bg(T);
        E.slash(T, 37.0, 0.55, a.root, a.bands);
        const u = T - 37.0;
        a.tiles.forEach((t, i) => { const p = spring(T - 37.05 - i * 0.07, 180, 17); S(t, { s: Math.max(0.001, 0.85 + 0.15 * p), o: clamp(p * 1.5), y: (1 - p) * 60 + Math.sin(T * 1.1 + i) * 4 }); });
        a.pills.forEach((p, i) => { const k = P(T, 38.0 + i * 0.22, 0.55, Ease.outBack); S(p, { y: (1 - k) * 40, o: clamp(k), s: Math.max(0.001, 0.9 + 0.1 * k) }); });
        const out = P(T, 40.45, 0.3, Ease.inCubic);
        S(a.cam, { s: 0.96 + u * 0.022 - out * 0.3, o: 1 - out });
        a.feat.style.opacity = String(1 - out);
      },
    });
  }

  // ---------- Apparition du symbole sur fond Ink ----------
  {
    const a = {};
    replaceScene({
      id: 'symbol',
      build(root) {
        el(root, { inset: '0', background: INK });
        a.glow = C.glow(root, 900, 'rgba(30,59,255,0.45)', 0);
        a.glow2 = C.glow(root, 600, 'rgba(215,255,63,0.10)', 0);
        a.s = symbol3(root, 300, true);
        Object.assign(a.s.box.style, { left: '810px', top: '390px' });
      },
      update(T) {
        a.s.parts.forEach((p, i) => {
          const t0 = 9.66 + i * 0.08;
          const k = spring(T - t0, 200, 17);
          S(p, { x: DIRS[i][0] * (1 - k) * 360, y: DIRS[i][1] * (1 - k) * 360, s: Math.max(0.001, lerp(0.6, 1, k)), o: clamp((T - t0) * 6) });
        });
        const u = clamp((T - 10.3) / 0.45);
        if (u > 0 && u < 1) S(a.s.parts[2], { s: 1 + 0.16 * Math.sin(Math.PI * u), o: 1 });
        const gp = P(T, 9.7, 0.9);
        S(a.glow, { x: 960, y: 540, o: gp * (0.8 + 0.2 * Math.sin(T * 7)), s: 0.7 + 0.4 * gp });
        S(a.glow2, { x: 1040, y: 470, o: gp * 0.8, s: 0.6 + 0.5 * gp });
      },
    });
  }

  // ---------- « Meet YouCraft » ----------
  {
    const a = {};
    replaceScene({
      id: 'meet',
      build(root) {
        a.clip = el(root, { inset: '0' });
        el(a.clip, { inset: '0', background: STONE });
        a.g1 = C.glow(a.clip, 1400, 'rgba(223,232,240,0.7)', 1);
        a.g2 = C.glow(a.clip, 1100, 'rgba(30,59,255,0.08)', 1);
        a.z = el(a.clip, { inset: '0', transformOrigin: '50% 50%' });
        a.row = el(a.z, { left: '0', width: '1920px', top: '420px', display: 'flex', justifyContent: 'center' });
        a.L = C.ycLogo(a.row, 190);
        a.sym = [a.L.dome, a.L.quarter, a.L.block];
        const k = a.L.k;
        a.sym.forEach((p) => (p.style.transformOrigin = `${124 * k}px ${123.5 * k}px`));
        a.by = el(a.z, { left: '0', width: '1920px', top: '680px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '22px', fontFamily: 'DM Sans', fontSize: '30px', fontWeight: '500', color: '#77746E' });
      },
      update(T) {
        const R = lerp(0, 1400, Ease.inOutCubic(clamp((T - 10.7) / 0.5)));
        a.clip.style.clipPath = `circle(${R.toFixed(1)}px at 960px 540px)`;
        S(a.g1, { x: 700 + Math.sin(T) * 80, y: 380 });
        S(a.g2, { x: 1300, y: 720 + Math.cos(T) * 60 });
        if (a.off === undefined) { S(a.z, {}); const r = a.L.sym.getBoundingClientRect(); if (r.width) a.off = 960 - (r.left + r.width / 2); }
        const sp = P(T, 10.85, 0.75, Ease.inOutQuint);
        a.sym.forEach((p) => S(p, { x: (1 - sp) * (a.off || 0), s: lerp(1.25, 1, sp) }));
        a.L.glyphs.forEach((c, i) => { const p = P(T, 11.1 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 60, o: p, blur: (1 - p) * 8 }); });
        const bp = P(T, 11.6, 0.7, Ease.outQuint);
        S(a.by, { y: (1 - bp) * 24, o: bp });
        const zo = P(T, 12.35, 0.4, Ease.inCubic);
        S(a.z, { s: 1 + (T - 10.7) * 0.025 + zo * 0.15, o: 1 - zo, blur: zo * 10 });
      },
    });
  }

  // ---------- Galaxie de dashboards métier qui forme le symbole ----------
  {
    const a = {};
    const LABELS = ['Accueil des recrues', 'Suivi de flotte', 'Stock entrepôt', 'Maintenance', 'Pipeline commercial', 'Planning des équipes', 'Suivi des livraisons', 'Recrutement', 'Bilan carbone', 'Support informatique', 'Notes de frais', 'Audits de site', 'Portail client', 'Reporting direction'];
    const SIZE = 880, K = SIZE / 248, X0 = 960 - SIZE / 2, Y0 = 540 - (247 * K) / 2;
    // Emplacements : grille de cases au format des dashboards, gardées si leur centre tombe dans le symbole.
    const SLOTS = [];
    {
      const cv = document.createElement('canvas').getContext('2d');
      const shapes = [new Path2D(L0.sym.dome), new Path2D(L0.sym.quarter)];
      const blk = new Path2D(); blk.rect(bx, by, bw, bh); shapes.push(blk);
      const NC = 11, NR = 16, cw = 248 / NC, ch = 247 / NR;
      for (let j = 0; j < NR; j++) for (let i = 0; i < NC; i++) {
        const cx = (i + 0.5) * cw, cy = (j + 0.5) * ch;
        const which = shapes.findIndex((s) => cv.isPointInPath(s, cx, cy));
        if (which >= 0) SLOTS.push({ x: X0 + cx * K, y: Y0 + cy * K, s: (cw * K - 6) / 280, sy: (ch * K - 6) / 190, which });
      }
    }
    replaceScene({
      id: 'galaxy',
      build(root) {
        a.root = root;
        a.bg = U.scenery(root, 'b50/bokeh_citydusk', { drift: 0.3, parts: false });
        el(root, { inset: '0', background: 'rgba(11,11,15,0.62)' });
        a.dim = el(root, { inset: '0', background: INK, opacity: '0' });
        a.glow = C.glow(root, 1500, 'rgba(30,59,255,0.5)', 0);
        a.sp = el(root, { inset: '0' });
        const r = seeded(404);
        const n = SLOTS.length + 8;
        a.items = Array.from({ length: n }, (_, i) => {
          const t = U.dash(a.sp, i, i % 3 === 1);
          t.style.transformOrigin = '50% 50%';
          const ang = r() * Math.PI * 2, rad = 160 + Math.pow(r(), 0.7) * 1100;
          return { t, x: Math.cos(ang) * rad * 1.5, y: Math.sin(ang) * rad * 0.85, z: r() * 2400, rr: (r() - 0.5) * 20, slot: SLOTS[i] || null, lab: i < LABELS.length ? LABELS[i] : null };
        });
        a.items.forEach((it) => { if (it.lab) { it.l = el(a.sp, { left: '0', top: '0' }); it.l.className = 'lbl'; it.l.textContent = it.lab; } });
        a.sym = el(root, { left: X0 + 'px', top: Y0 + 'px', width: SIZE + 'px', height: 247 * K + 'px', opacity: '0' }, window.dcIcon(SIZE, 'gal', 'dark'));
      },
      update(T) {
        a.root.style.opacity = String(P(T, 40.6, 0.3, Ease.inOutSine));
        a.bg(T);
        const u = clamp((T - 40.6) / 3.0);
        const D = lerp(-500, 1300, Ease.inOutSine(u));
        const rot = (T - 40.6) * 0.06;
        const morph = P(T, 45.05, 0.45, Ease.inOutSine);
        a.items.forEach((it, i) => {
          const zz = it.z + D + 900;
          const k = 900 / Math.max(60, zz);
          const cr = Math.cos(rot), sr = Math.sin(rot);
          const x = it.x * cr - it.y * sr * 0.6, y = it.y * cr + it.x * sr * 0.3;
          const gx = 960 + x * k, gy = 540 + y * k;
          const fade = clamp((zz - 250) / 300) * P(T, 40.65 + (i % 10) * 0.05, 0.5);
          const ts = 42.9 + (i % 12) * 0.045;
          const c = Ease.inOutCubic(clamp((T - ts) / 1.25));
          let X, Y, Sx, Sy, Rr, O;
          if (it.slot) {
            X = lerp(gx, it.slot.x, c); Y = lerp(gy, it.slot.y, c); Sx = lerp(k, it.slot.s, c); Sy = lerp(k, it.slot.sy, c); Rr = lerp(it.rr, 0, c); O = lerp(fade, 1, c);
          } else {
            X = gx + (gx - 960) * c * 2; Y = gy + (gy - 540) * c * 2; Sx = Sy = k * (1 + c); Rr = it.rr; O = fade * (1 - c);
          }
          const tr = `translate3d(${(X - 140).toFixed(2)}px,${(Y - 95).toFixed(2)}px,0) rotate(${Rr.toFixed(2)}deg) scale(${Sx.toFixed(4)},${Sy.toFixed(4)})`;
          it.t.style.transform = tr;
          it.t.style.opacity = String(clamp(O * (1 - morph)).toFixed(3));
          const bl = c > 0 && c < 1 ? Math.sin(c * Math.PI) * 4 : 0;
          it.t.style.filter = bl > 0.05 ? `blur(${bl.toFixed(2)}px)` : 'none';
          it.t.style.zIndex = String(c > 0.5 ? 9000 + i : Math.round(8000 - zz));
          if (it.l) {
            const lp = P(T, 41.0 + (i % 14) * 0.14, 0.5) * (1 - P(T, 42.8, 0.3));
            S(it.l, { x: gx + (280 * k) / 2 + 14, y: gy - 12, o: lp * fade });
            it.l.style.zIndex = '9999';
          }
        });
        const hold = P(T, 44.1, 0.8);
        S(a.glow, { x: 960, y: 540, o: hold * 0.7, s: 0.8 + 0.2 * hold });
        S(a.sym, { o: morph, s: 1 + morph * 0.02 });
        a.dim.style.opacity = String(P(T, 44.6, 1.0, Ease.inOutSine));
      },
    });
  }

  // ---------- Signature : le symbole se resserre, se pose dans le logo, « Let's make it real. » ----------
  {
    const a = {};
    replaceScene({
      id: 'finale',
      build(root) {
        el(root, { inset: '0', background: INK });
        a.g = C.glow(root, 1500, 'rgba(30,59,255,0.38)', 1);
        a.g2 = C.glow(root, 1000, 'rgba(215,255,63,0.08)', 1);
        a.rings = [0, 1].map(() => el(root, { left: '0', top: '0', width: '600px', height: '600px', borderRadius: '50%', border: '2px solid rgba(245,242,234,0.35)', opacity: '0' }));
        a.row = el(root, { inset: '0' });
        a.line = el(a.row, { left: '0', width: '1920px', top: '360px', display: 'flex', justifyContent: 'center' });
        a.L = C.ycLogo(a.line, 170, true);
        a.sym = [a.L.dome, a.L.quarter, a.L.block];
        a.k = a.L.k;
        a.sym.forEach((p) => (p.style.transformOrigin = `${124 * a.k}px ${123.5 * a.k}px`));
        a.q = el(root, { left: '0', width: '1920px', top: '800px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontSize: '58px', fontWeight: '700', letterSpacing: '-0.02em', color: STONE }, `Vous <span style="color:${UMD}">l’imaginez ?</span>`);
        a.tag = el(root, { left: '0', width: '1920px', top: '600px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontSize: '50px', fontWeight: '700', letterSpacing: '-0.02em', color: STONE }, `Donnons-lui <span style="color:${LIME}">vie.</span>`);
        a.by = el(root, { left: '0', width: '1920px', top: '730px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '20px', fontFamily: 'DM Sans', fontSize: '26px', fontWeight: '500', color: 'rgba(245,242,234,0.7)' });
        a.black = el(root, { inset: '0', background: '#000', opacity: '0' });
      },
      update(T) {
        if (a.off === undefined) { const r = a.L.sym.getBoundingClientRect(); if (r.width) a.off = [960 - (r.left + r.width / 2), 540 - (r.top + r.height / 2)]; }
        const [ox, oy] = a.off || [0, 0];
        S(a.g, { x: 1300, y: 760, s: 0.8 + 0.06 * Math.sin(T * 1.5), o: P(T, 45.6, 1.0) });
        S(a.g2, { x: 1500, y: 260, s: 0.8, o: P(T, 45.8, 1.0) });
        // Taille : symbole de la galaxie (880 px de large) → 300 px → taille du logo final.
        const w0 = 248 * a.k;
        const shrink = Ease.inOutCubic(clamp((T - 45.6) / 0.65));
        const settle = Ease.inOutQuint(clamp((T - 47.85) / 0.8));
        const sc = lerp(lerp(880 / w0, 300 / w0, shrink), 1, settle);
        // Les trois pièces s'écartent puis se reposent une fois (rebond).
        const b = T - 46.2;
        const w = b > 0 ? 60 * Math.exp(-b * 4) * Math.sin(b * 12) : 0;
        a.sym.forEach((p, i) => {
          const pulse = b > 0 && i === 2 ? 1 + 0.2 * Math.exp(-b * 6) * Math.sin(b * 16) : 1;
          S(p, { x: ox * (1 - settle) + DIRS[i][0] * w, y: oy * (1 - settle) + DIRS[i][1] * w, s: sc * pulse });
        });
        a.rings.forEach((r, i) => { const k = (T - 46.2 - i * 0.25) / 1.2; S(r, { x: 660, y: 240, s: 0.4 + clamp(k) * 1.6, o: k > 0 && k < 1 ? (1 - k) * 0.7 : 0 }); });
        const qp = P(T, 46.35, 0.7, Ease.outCubic) * (1 - P(T, 47.55, 0.35));
        S(a.q, { o: qp, y: (1 - P(T, 46.35, 0.7, Ease.outCubic)) * 20, blur: (1 - clamp(qp * 1.5)) * 6 });
        a.L.glyphs.forEach((c, i) => { const p = P(T, 48.15 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 60, o: p, blur: (1 - p) * 8 }); });
        const tp = P(T, 48.45, 0.8, Ease.outCubic);
        S(a.tag, { o: tp, y: (1 - tp) * 24, blur: (1 - tp) * 10 });
        const bp = P(T, 49.7, 0.7, Ease.outQuint);
        S(a.by, { o: bp, y: (1 - bp) * 20 });
        S(a.row, { s: 1 + Math.max(0, T - 48) * 0.006 });
        a.black.style.opacity = String(P(T, 53.2, 0.8, Ease.inOutSine));
      },
    });
  }

  // ---------- Messages à l'écran : appel à l'action en Lime ----------
  {
    const sc = SCENES.find((s) => s.id === 'kinetic');
    const ob = sc.build;
    sc.build = function (root) {
      ob(root);
      [...root.querySelectorAll('.ui')].filter((n) => n.textContent.includes('Demandez votre démo')).forEach((b) => Object.assign(b.style, { background: LIME, color: INK, boxShadow: '0 20px 60px rgba(215,255,63,0.25)' }));
      root.querySelectorAll('.nh').forEach((n) => Object.assign(n.style, { background: 'rgba(11,11,15,0.86)', color: STONE, border: '1.5px solid rgba(245,242,234,0.14)' }));
    };
  }
})();

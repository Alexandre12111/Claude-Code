// V2 : ouverture en profondeur, plans métier avec photos et fonds alternés, galaxie de dashboards qui forme le logo, final au symbole.
(function () {
  const { h, S, P, Ease, lerp, clamp, spring, icon, seeded } = E;
  const el = C.el;
  const DIRS = [[0, 0], [0, -1], [1, 0], [0, 1], [-1, 0]];

  // ---------- Ouverture ----------
  {
    const a = {};
    replaceScene({
      id: 'intro',
      build(root) {
        a.bg = U.aurora(root, 'light', 1);
        a.sp = el(root, { inset: '0' });
        const r = seeded(21);
        a.tiles = Array.from({ length: 16 }, (_, i) => {
          const t = U.dash(a.sp, i * 5 + 1, i % 4 === 3);
          t.style.transformOrigin = '50% 50%';
          const ang = (i / 16) * Math.PI * 2 + r() * 0.3, rad = 820 + r() * 480;
          return { t, x: Math.cos(ang) * rad * 1.35, y: Math.sin(ang) * rad * 0.75, z: 200 + r() * 2200, rr: (r() - 0.5) * 14 };
        });
        a.txt = el(root, { inset: '0', transformOrigin: '50% 50%', zIndex: '20000' });
        a.l1 = el(a.txt, { left: '0', width: '1920px', top: '360px', textAlign: 'center', fontFamily: 'Outfit', fontWeight: '500', fontSize: '74px', color: '#0B2545' });
        a.w1 = 'Imagine being able to build'.split(' ').map((t) => { const s = h('span', '', a.l1, t); s.style.display = 'inline-block'; s.style.margin = '0 11px'; return s; });
        a.l2 = el(a.txt, { left: '0', width: '1920px', top: '470px', textAlign: 'center', fontSize: '210px', lineHeight: '1.05' });
        a.l2.className = 'abs nh';
        a.c2 = E.chars(a.l2, 'any app');
        a.c2._c.forEach((c) => c.classList.add('dcgrad'));
        a.c2._c.forEach((c) => (c.style.padding = '0.1em 0.04em'));
        a.sweep = U.sweep(root);
        a.flash = el(root, { inset: '0', background: '#fff', opacity: '0' });
      },
      update(T) {
        a.bg(T);
        const warp = Ease.inExpo(clamp((T - 2.25) / 0.9));
        const D = T * 140 + warp * 2600;
        a.tiles.forEach((it, i) => {
          const zz = it.z - D + 400;
          if (zz < 60) { it.t.style.opacity = '0'; return; }
          const k = 900 / zz;
          const blur = Math.min(18, Math.abs(zz - 900) / 120) + warp * 10;
          S(it.t, { x: 960 + it.x * k - 140, y: 540 + it.y * k - 95, s: k, r: it.rr, o: P(T, 0.1 + i * 0.05, 0.6) * clamp((zz - 60) / 200) * clamp((k - 0.45) / 0.15), blur });
          it.t.style.zIndex = String(Math.round(5000 - zz));
        });
        const tw = [0.3, 0.72, 0.95, 1.12, 1.32];
        a.w1.forEach((w, i) => { const p = P(T, tw[i], 0.7, Ease.outQuint); S(w, { y: (1 - p) * 30, o: p, blur: (1 - p) * 10 }); });
        a.c2._c.forEach((c, i) => { const p = P(T, (i < 4 ? 1.62 : 1.86) + (i % 4) * 0.04, 0.6, Ease.outExpo); S(c, { s: lerp(1.8, 1, p), o: clamp(p * 3), blur: (1 - p) * 14 }); });
        S(a.txt, { s: 1 + T * 0.015 + warp * 2.2, o: 1 - warp * 1.2, blur: warp * 16 });
        a.sweep(T, 2.75, 0.45);
        a.flash.style.opacity = String(Math.max(0, P(T, 2.95, 0.15) - P(T, 3.1, 0.3)) * 0.9);
      },
    });
  }

  // ---------- Plans métier : fonds alternés et photos ----------
  {
    const sc = SCENES.find((s) => s.id === 'cards');
    const ob = sc.build, ou = sc.update;
    const v = {};
    sc.build = function (root) {
      const of = U.field;
      const MAP = {
        dusk: (g) => U.scenery(g, 'mountains_dawn'),
        violet: (g) => U.scenery(g, 'hills_mist', { drift: -1 }),
        sky: (g) => U.scenery(g, 'clouds_sea'),
      };
      U.field = (g, pal) => MAP[pal](g);
      ob(root);
      U.field = of;
      v.shots = [...root.children];
      v.sweep = U.sweep(root);
    };
    sc.update = function (T) {
      ou(T);
      // Entrée du plan A en zoom (sortie de l'ouverture), passage B → C en zoom traversant.
      const [A, B, Cc] = v.shots;
      if (T < 3.75) { const p = P(T, 3.05, 0.55, Ease.outCubic); S(A, { s: lerp(1.35, 1, p), o: clamp(p * 2), blur: (1 - p) * 20 }); }
      if (T > 5.9 && T < 6.8) {
        const p = P(T, 5.95, 0.32, Ease.inCubic);
        S(B, { s: 1 + p * 0.6, o: 1 - p, blur: p * 24 });
        const q = P(T, 6.1, 0.45, Ease.outCubic);
        S(Cc, { s: lerp(0.8, 1, q), o: q, blur: (1 - q) * 18 });
      }
      v.sweep(T, 5.98, 0.45);
    };
  }

  // ---------- Galaxie de dashboards qui forme le logo ----------
  {
    const a = {};
    const LABELS = ['Client portal', 'Budget tracker', 'Audit log', 'Sales cockpit', 'HR onboarding', 'Purchase requests', 'Project tracker', 'Quality checks', 'Contract review', 'Field reports', 'Training hub', 'Partner portal', 'Asset register', 'Board reporting'];
    const K = 11, R2 = Math.SQRT1_2;
    // Emplacements : grille tournée à 45° dans chacun des 5 losanges du symbole.
    const SLOTS = [];
    [[50, 50, 17, 4, 6], [50, 14, 9, 2, 3], [86, 50, 9, 2, 3], [50, 86, 9, 2, 3], [14, 50, 9, 2, 3]].forEach(([cx, cy, r, nc, nr]) => {
      const L = r * Math.SQRT2 * K, X = 960 + (cx - 50) * K, Y = 540 + (cy - 50) * K;
      const cw = L / nc, ch = L / nr;
      for (let j = 0; j < nr; j++) for (let i = 0; i < nc; i++) {
        const u = -L / 2 + (i + 0.5) * cw, w = -L / 2 + (j + 0.5) * ch;
        SLOTS.push({ x: X + (u - w) * R2, y: Y + (u + w) * R2, s: (cw - 5) / 280 });
      }
    });
    replaceScene({
      id: 'galaxy',
      build(root) {
        a.root = root;
        a.bg = U.aurora(root, 'dark', 9);
        a.dim = el(root, { inset: '0', background: '#000', opacity: '0' });
        a.glow = C.glow(root, 1500, 'rgba(46,139,192,0.6)', 0);
        a.sp = el(root, { inset: '0' });
        const r = seeded(404);
        const n = SLOTS.length + 8;
        a.items = Array.from({ length: n }, (_, i) => {
          const t = U.dash(a.sp, i, i % 5 === 2);
          t.style.transformOrigin = '50% 50%';
          const ang = r() * Math.PI * 2, rad = 160 + Math.pow(r(), 0.7) * 1100;
          return { t, x: Math.cos(ang) * rad * 1.5, y: Math.sin(ang) * rad * 0.85, z: r() * 2400, rr: (r() - 0.5) * 20, slot: SLOTS[i] || null, lab: i < LABELS.length ? LABELS[i] : null };
        });
        a.items.forEach((it) => { if (it.lab) { it.l = el(a.sp, { left: '0', top: '0' }); it.l.className = 'lbl'; it.l.textContent = it.lab; } });
        a.sym = U.symbol(root, 1100);
        Object.assign(a.sym.style, { left: '410px', top: '-10px', opacity: '0' });
        a.sym._d.forEach((d) => (d.style.boxShadow = '0 0 80px rgba(46,139,192,0.6)'));
      },
      update(T) {
        a.root.style.opacity = String(P(T, 40.6, 0.3, Ease.inOutSine));
        a.bg(T);
        const u = clamp((T - 40.6) / 3.0);
        const D = lerp(-500, 1300, Ease.inOutSine(u));
        const rot = (T - 40.6) * 0.06;
        a.items.forEach((it, i) => {
          const zz = it.z + D + 900;
          const k = 900 / Math.max(60, zz);
          const cr = Math.cos(rot), sr = Math.sin(rot);
          const x = it.x * cr - it.y * sr * 0.6, y = it.y * cr + it.x * sr * 0.3;
          const gx = 960 + x * k, gy = 540 + y * k;
          const fade = clamp((zz - 250) / 300) * P(T, 40.65 + (i % 10) * 0.05, 0.5);
          // Convergence vers le logo.
          const ts = 42.9 + (i % 12) * 0.045;
          const c = Ease.inOutCubic(clamp((T - ts) / 1.25));
          let X, Y, Sc, Rr, O;
          if (it.slot) {
            X = lerp(gx, it.slot.x, c); Y = lerp(gy, it.slot.y, c); Sc = lerp(k, it.slot.s, c); Rr = lerp(it.rr, 45, c); O = lerp(fade, 1, c);
          } else {
            X = gx + (gx - 960) * c * 2; Y = gy + (gy - 540) * c * 2; Sc = k * (1 + c); Rr = it.rr; O = fade * (1 - c);
          }
          const morph = P(T, 45.05, 0.45, Ease.inOutSine);
          S(it.t, { x: X - 140, y: Y - 95, s: Sc, r: Rr, o: O * (1 - morph), blur: c > 0 && c < 1 ? Math.sin(c * Math.PI) * 4 : 0 });
          it.t.style.zIndex = String(c > 0.5 ? 9000 + i : Math.round(8000 - zz));
          if (it.l) {
            const lp = P(T, 41.0 + (i % 14) * 0.14, 0.5) * (1 - P(T, 42.8, 0.3));
            S(it.l, { x: gx + (280 * k) / 2 + 14, y: gy - 12, o: lp * fade });
            it.l.style.zIndex = '9999';
          }
        });
        const hold = P(T, 44.1, 0.8);
        S(a.glow, { x: 960, y: 540, o: hold * 0.8, s: 0.8 + 0.2 * hold });
        const morph = P(T, 45.05, 0.45, Ease.inOutSine);
        S(a.sym, { o: morph, s: 1 + morph * 0.02 });
        a.dim.style.opacity = String(P(T, 44.6, 1.0, Ease.inOutSine));
      },
    });
  }

  // ---------- Final : le symbole se resserre, rebondit, puis signe DigiCraft ----------
  {
    const a = {};
    replaceScene({
      id: 'finale',
      build(root) {
        el(root, { inset: '0', background: '#000' });
        a.g = C.glow(root, 1500, 'rgba(15,88,133,0.6)', 1);
        a.g2 = C.glow(root, 1000, 'rgba(109,93,246,0.35)', 1);
        a.rings = [0, 1].map(() => el(root, { left: '0', top: '0', width: '600px', height: '600px', borderRadius: '50%', border: '2px solid rgba(143,198,231,0.6)', opacity: '0' }));
        a.row = el(root, { inset: '0' });
        a.lk = U.lockup(a.row, 170, true);
        Object.assign(a.lk.style, { position: 'absolute', left: '0', width: '1920px', top: '360px', justifyContent: 'center' });
        a.q = el(root, { left: '0', width: '1920px', top: '800px', textAlign: 'center', fontFamily: 'Outfit', fontSize: '58px', fontWeight: '500', color: '#fff' }, 'Can you <span class="dcgrad-l">imagine it?</span>');
        a.tag = el(root, { left: '0', width: '1920px', top: '600px', textAlign: 'center', fontFamily: 'Outfit', fontSize: '50px', fontWeight: '500' }, '<span class="dcgrad-l">Let’s make it real.</span>');
        a.by = el(root, { left: '0', width: '1920px', top: '730px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '20px', fontFamily: 'Montserrat', fontSize: '26px', fontWeight: '600', color: 'rgba(255,255,255,0.7)' });
        h('span', '', a.by, 'by');
        C.leytonLogo(a.by, 80, true);
        a.black = el(root, { inset: '0', background: '#000', opacity: '0' });
      },
      update(T) {
        if (a.off === undefined) { const r = a.lk._sym.getBoundingClientRect(); if (r.width) a.off = [960 - (r.left + r.width / 2), 540 - (r.top + r.height / 2)]; }
        const [ox, oy] = a.off || [0, 0];
        S(a.g, { x: 960, y: 540, s: 0.8 + 0.06 * Math.sin(T * 1.5), o: P(T, 45.6, 1.0) });
        S(a.g2, { x: 1100, y: 560, s: 0.8, o: P(T, 45.8, 1.0) * 0.9 });
        // Taille : 1100 px (logo de dashboards) → 300 px → taille du logo final.
        const shrink = Ease.inOutCubic(clamp((T - 45.6) / 0.65));
        const settle = Ease.inOutQuint(clamp((T - 47.85) / 0.8));
        const sc = lerp(lerp(1100 / 170, 300 / 170, shrink), 1, settle);
        S(a.lk._sym, { x: ox * (1 - settle), y: oy * (1 - settle), s: sc });
        // Rebond des losanges, comme à l'apparition du symbole au début.
        const b = T - 46.2;
        a.lk._sym._d.forEach((d, i) => {
          const pulse = b > 0 && !i ? 1 + 0.25 * Math.exp(-b * 6) * Math.sin(b * 18) : 1;
          const w = b > 0 ? 90 * Math.exp(-b * 4) * Math.sin(b * 14) : 0;
          S(d, { x: DIRS[i][0] * w, y: DIRS[i][1] * w, r: 45 + (b > 0 ? 90 * Ease.inOutCubic(clamp(b / 0.7)) : 0), s: pulse });
        });
        a.rings.forEach((r, i) => { const k = (T - 46.2 - i * 0.25) / 1.2; S(r, { x: 660, y: 240, s: 0.4 + clamp(k) * 1.6, o: k > 0 && k < 1 ? (1 - k) * 0.7 : 0 }); });
        const qp = P(T, 46.35, 0.7, Ease.outCubic) * (1 - P(T, 47.55, 0.35));
        S(a.q, { o: qp, y: (1 - P(T, 46.35, 0.7, Ease.outCubic)) * 20, blur: (1 - clamp(qp * 1.5)) * 6 });
        a.lk._ch.forEach((c, i) => { const p = P(T, 48.15 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 60, o: p, blur: (1 - p) * 8 }); });
        const tp = P(T, 48.45, 0.8, Ease.outCubic);
        S(a.tag, { o: tp, y: (1 - tp) * 24, blur: (1 - tp) * 10 });
        const bp = P(T, 49.7, 0.7, Ease.outQuint);
        S(a.by, { o: bp, y: (1 - bp) * 20 });
        S(a.row, { s: 1 + Math.max(0, T - 48) * 0.006 });
        a.black.style.opacity = String(P(T, 53.2, 0.8, Ease.inOutSine));
      },
    });
  }
})();

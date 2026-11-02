// Applications complètes à l'écran : plans métier (RH, tour de contrôle logistique, maintenance) dans une fenêtre de
// navigateur avec travelling vers la zone en direct, carrousel de modèles et mosaïque faits des mêmes écrans.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon } = E;
  const el = C.el;
  const INK = '#0B0B0F', STONE = '#F5F2EA', UM = '#1E3BFF', LIME = '#D7FF3F';
  const A = U.APP;

  // Fenêtre de navigateur sobre contenant un écran d'application à l'échelle k.
  function browser(parent, x, y, k, url, bar = 44) {
    const w = A.W * k, hh = A.H * k + bar;
    const r = el(parent, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px', borderRadius: Math.max(8, 14 * k / 0.9) + 'px', overflow: 'hidden', background: '#fff', boxShadow: '0 40px 120px rgba(11,11,15,0.38), 0 8px 24px rgba(11,11,15,0.14)' });
    const b = el(r, { left: '0', top: '0', width: w + 'px', height: bar + 'px', background: '#F3F3F1', borderBottom: '1px solid #E7E6E2' });
    const d = bar * 0.27;
    ['#FF5F57', '#FEBC2E', '#28C840'].forEach((c, i) => el(b, { left: bar * 0.45 + i * d * 1.75 + 'px', top: bar / 2 - d / 2 + 'px', width: d + 'px', height: d + 'px', borderRadius: '50%', background: c }));
    if (bar > 30) el(b, { left: '50%', top: bar * 0.18 + 'px', transform: 'translateX(-50%)', height: bar * 0.64 + 'px', minWidth: '420px', padding: '0 16px', borderRadius: '8px', background: '#fff', border: '1px solid #E7E6E2', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', fontFamily: 'DM Sans', fontSize: '14px', color: '#6B6B70' }, `${icon('lock', 13, 2.2, '#9A9AA0')}${url}`);
    const body = el(r, { left: '0', top: bar + 'px', width: w + 'px', height: A.H * k + 'px', overflow: 'hidden' });
    return { r, body };
  }
  const mount = (body, make, k) => { const s = make(body); s.root.style.transform = `scale(${k})`; return s; };

  // ---------- Plans métier ----------
  {
    const a = {};
    const K = 0.89;
    const shot = (root, img, drift, make, url, hot) => {
      const g = el(root, { inset: '0', overflow: 'hidden' });
      const f = U.scenery(g, img, { drift, parts: false });
      el(g, { inset: '0', background: 'radial-gradient(120% 90% at 50% 40%, rgba(11,11,15,0.12) 40%, rgba(11,11,15,0.5) 100%)' });
      const cam = el(g, { inset: '0', perspective: '2400px' });
      const rig = el(cam, { inset: '0', transformStyle: 'preserve-3d' });
      const B = browser(rig, 960 - (A.W * K) / 2, 540 - (A.H * K + 44) / 2, K, url);
      const s = mount(B.body, make, K);
      // Point de la fenêtre vers lequel la caméra avance (coordonnées écran).
      rig.style.transformOrigin = `${960 - (A.W * K) / 2 + hot[0] * K}px ${540 - (A.H * K + 44) / 2 + 44 + hot[1] * K}px`;
      return { g, f, rig, s };
    };
    replaceScene({
      id: 'cards',
      build(root) {
        a.A = shot(root, 'b50/soft_skyline', 1, A.people, 'people.northwind.youcraft.app/onboarding', [1150, 520]);
        a.B = shot(root, 'b50/soft_citydusk', -1, A.fleet, 'control-tower.northwind.youcraft.app/live-map', [820, 430]);
        a.Cc = shot(root, 'b50/soft_citynight', 1, A.plants, 'asset-monitor.northwind.youcraft.app/sensors/PR-LY-004', [900, 420]);
        a.sweep = U.sweep(root);
      },
      update(T) {
        U.whip(a.A.g, T, 3.08, 4.6, 1);
        U.whip(a.B.g, T, 4.62, 6.2, 1);
        U.whip(a.Cc.g, T, 6.22, 7.72, 1);
        a.A.f(T); a.B.f(T); a.Cc.f(T);
        // Légère inclinaison qui se redresse, puis avancée vers la zone en direct.
        const cam = (rig, t0) => { const u = T - t0; S(rig, { s: lerp(0.97, 1.2, Ease.inOutSine(clamp(u / 1.6))), ry: lerp(-6, 1, Ease.outCubic(clamp(u / 1.2))), rx: lerp(5, 0, Ease.outCubic(clamp(u / 1.2))) }); };
        cam(a.A.rig, 3.2); cam(a.B.rig, 4.62); cam(a.Cc.rig, 6.15);
        a.A.s.update(T, 3.25); a.B.s.update(T, 4.66); a.Cc.s.update(T, 6.2);
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

  // ---------- Modèles : aperçus d'écrans réels ----------
  {
    const a = {};
    const TPL = [['users', 'HR onboarding', 'People', A.people], ['map-pin', 'Fleet control tower', 'Supply chain', A.fleet], ['gauge', 'Asset monitoring', 'Operations', A.plants], ['target', 'Sales pipeline', 'Sales', A.crm],
      ['layers', 'Warehouse inventory', 'Logistics', A.stock], ['calendar', 'Shift planning', 'Operations', A.shifts], ['briefcase', 'Client portal', 'Consulting', null]];
    replaceScene({
      id: 'templates',
      build(root) {
        a.root = root;
        a.bg = U.scenery(root, 'b49/gradient_deep', { drift: 0.4, parts: false, tint: 'rgba(11,11,15,0.30)' });
        a.cam = el(root, { inset: '0', perspective: '2000px' });
        a.cards = TPL.map(([ic, t, d, make], i) => {
          const c = el(a.cam, { left: '720px', top: '250px', width: '480px', height: '560px', borderRadius: '22px', background: '#FBFAF6', boxShadow: '0 40px 100px rgba(11,11,15,0.45)', overflow: 'hidden' });
          c.className = 'abs ui';
          const pv = el(c, { left: '0', right: '0', top: '0', height: '300px', overflow: 'hidden', borderBottom: '1px solid #E7E6E2' });
          if (make) mount(pv, make, 0.3);
          else { const p = A.portal(pv, true); p.v.style.transform = 'scale(0.4068)'; }
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

  // ---------- Mosaïque : six applications en direct ----------
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
        const k = 540 / A.W, TW = 540, TH = A.H * k + 22, GX = 40, GY = 36, X0 = (1920 - 3 * TW - 2 * GX) / 2, Y0 = 122;
        a.tiles = [A.people, A.fleet, A.plants, A.crm, A.stock, A.shifts].map((make, i) => {
          const B = browser(a.cam, X0 + (i % 3) * (TW + GX), Y0 + Math.floor(i / 3) * (TH + GY), k, '', 22);
          const s = mount(B.body, make, k);
          return { t: B.r, s };
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
        a.tiles.forEach(({ t, s }, i) => { const p = E.spring(T - 37.05 - i * 0.07, 180, 17); S(t, { s: Math.max(0.001, 0.85 + 0.15 * p), o: clamp(p * 1.5), y: (1 - p) * 60 + Math.sin(T * 1.1 + i) * 4 }); s.update(T, 37.2 + i * 0.07); });
        a.pills.forEach((p, i) => { const k = P(T, 38.0 + i * 0.22, 0.55, Ease.outBack); S(p, { y: (1 - k) * 40, o: clamp(k), s: Math.max(0.001, 0.9 + 0.1 * k) }); });
        const out = P(T, 40.45, 0.3, Ease.inCubic);
        S(a.cam, { s: 0.96 + u * 0.022 - out * 0.3, o: 1 - out });
        a.feat.style.opacity = String(1 - out);
      },
    });
  }
})();

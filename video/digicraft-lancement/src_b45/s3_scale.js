// Ampleur : idées métier en rafale, mosaïque d'apps, galaxie d'apps, orbe, signature.
(function () {
  const { h, S, P, Ease, lerp, clamp, spring, icon, seeded } = E;
  const el = C.el;

  // ---------- Rafale de demandes ----------
  {
    const a = {};
    const LIST = [
      ['Expense approval workflow for the finance team', ['#0F5885', '#2E8BC0', '#8FC6E7']],
      ['Sales pipeline tracker for our EMEA region', ['#2A1F6B', '#6D5DF6', '#E2D4FF']],
      ['Supplier audit platform for 40 production sites', ['#063B3A', '#0EA5A4', '#BFE3F7']],
      ['Group-wide ESG reporting hub', ['#011A33', '#3B6FD8', '#B7A6FF']],
      ['Maintenance planning app for our plants', ['#3A1D5C', '#A855F7', '#8FC6E7']],
    ];
    const T0 = 30.0, D = 1.43;
    SCENES.push({
      id: 'prompts',
      build(root) {
        a.root = root;
        a.bgs = LIST.map(([, c], k) => {
          const g = el(root, { inset: '0', background: c[0] });
          const r = seeded(31 + k * 7);
          g._b = Array.from({ length: 8 }, (_, i) => ({ e: C.glow(g, 500 + r() * 900, i % 2 ? c[1] : c[2], 0.7 + r() * 0.3), x: r() * 1920, y: r() * 1080, k: r() * 6 }));
          return g;
        });
        a.parts = U.particles(root, 'rgba(255,255,255,0.8)', 40, 3);
        a.bar = el(root, { left: '450px', top: '494px', width: '1020px', height: '92px', borderRadius: '24px', background: 'rgba(8,20,36,0.55)', border: '1.5px solid rgba(255,255,255,0.22)', boxShadow: '0 30px 80px rgba(0,0,0,0.35)' });
        a.txt = el(a.bar, { left: '34px', top: '28px', right: '100px', fontFamily: 'DM Sans', fontSize: '28px', color: '#fff', whiteSpace: 'nowrap', overflow: 'hidden' });
        a.go = el(a.bar, { right: '20px', top: '20px', width: '52px', height: '52px', borderRadius: '16px', background: 'rgba(255,255,255,0.16)', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('arrow-up-right', 24, 2.4, '#fff'));
      },
      update(T) {
        a.root.style.opacity = String(P(T, 30.0, 0.2));
        const k = Math.min(LIST.length - 1, Math.max(0, Math.floor((T - T0) / D)));
        a.bgs.forEach((g, i) => {
          const tin = T0 + i * D;
          const o = i === 0 ? 1 : P(T, tin - 0.15, 0.3, Ease.inOutSine);
          g.style.display = T >= tin - 0.2 && T < tin + D + 0.3 ? 'block' : 'none';
          g.style.opacity = String(o);
          g._b.forEach((b) => S(b.e, { x: b.x + Math.sin(T * 0.6 + b.k) * 160, y: b.y + Math.cos(T * 0.5 + b.k) * 110, s: 1 + 0.1 * Math.sin(T + b.k) }));
        });
        a.parts(T);
        const tk = T0 + k * D;
        U.typeText(a.txt, LIST[k][0], T, tk + 0.12, 38, true);
        const sw = T - tk;
        S(a.bar, { s: 1 + 0.02 * Math.exp(-sw * 6), y: Math.sin(T * 1.2) * 4 });
        const go = T - (tk + 1.18);
        a.go.style.background = go > 0 ? 'linear-gradient(135deg,#2E8BC0,#6D5DF6)' : 'rgba(255,255,255,0.16)';
        S(a.go, { s: go > 0 && go < 0.3 ? 1 - 0.12 * Math.sin(go / 0.3 * Math.PI) : 1 });
        const out = P(T, 36.85, 0.3, Ease.inCubic);
        a.root.style.filter = out > 0 ? `blur(${(out * 30).toFixed(1)}px)` : 'none';
      },
    });
  }

  // ---------- Mosaïque d'apps ----------
  {
    const a = {};
    SCENES.push({
      id: 'grid',
      build(root) {
        a.root = root;
        a.bg = U.aurora(root, 'light', 8);
        a.g1 = C.glow(root, 10, 'rgba(0,0,0,0)', 0);
        a.g2 = C.glow(root, 10, 'rgba(0,0,0,0)', 0);
        a.bands = E.slashBands(root, C.BANDS.navy);
        a.cam = el(root, { inset: '0', transformOrigin: '50% 50%' });
        const TW = 540, TH = 360, GX = 40, GY = 36, X0 = (1920 - 3 * TW - 2 * GX) / 2, Y0 = 130;
        const T = (i, bg) => { const t = el(a.cam, { left: X0 + (i % 3) * (TW + GX) + 'px', top: Y0 + Math.floor(i / 3) * (TH + GY) + 'px', width: TW + 'px', height: TH + 'px', background: bg }); t.className = 'tile ui'; return t; };
        const title = (t, s, col = '#0B2545') => el(t, { left: '30px', top: '26px', fontSize: '22px', fontWeight: '700', color: col }, s);
        a.tiles = [];
        // 1. Pipeline commercial
        { const t = T(0, 'linear-gradient(160deg,#E7E3FF,#CFC7FF)'); title(t, 'Sales pipeline');
          ['Lead', 'Proposal', 'Won'].forEach((c, j) => { const col = el(t, { left: 30 + j * 166 + 'px', top: '76px', width: '150px', height: '256px', borderRadius: '14px', background: 'rgba(255,255,255,0.55)' });
            el(col, { left: '14px', top: '12px', fontSize: '14px', fontWeight: '700', color: '#6D5DF6' }, c);
            for (let q = 0; q < 3 - j % 2; q++) { const cd = el(col, { left: '10px', top: 42 + q * 68 + 'px', width: '130px', height: '58px', borderRadius: '10px', background: '#fff' }); el(cd, { left: '10px', top: '10px', width: 70 + q * 12 + 'px', height: '9px', borderRadius: '5px', background: '#CBD5E1' }); el(cd, { left: '10px', top: '30px', fontSize: '13px', fontWeight: '700', color: '#0B2545' }, ['€42k', '€18k', '€96k'][q]); } });
          a.tiles.push(t); }
        // 2. Budget
        { const t = T(1, 'linear-gradient(160deg,#0B4471,#022446)'); title(t, 'Budget tracker', '#fff');
          a.bud = el(t, { left: '30px', top: '80px', fontFamily: 'Outfit', fontSize: '64px', fontWeight: '600', color: '#fff' }, '€1.24M');
          el(t, { left: '30px', top: '164px', fontSize: '16px', color: '#8FC6E7' }, '72% of annual budget used');
          a.bb = U.bars(t, 30, 200, 480, 130, [0.4, 0.55, 0.5, 0.7, 0.62, 0.85, 0.78, 0.95], '#8FC6E7'); a.tiles.push(t); }
        // 3. Projets
        { const t = T(2, 'linear-gradient(160deg,#DDF0FB,#B8DDF2)'); title(t, 'Projects delivered');
          el(t, { left: '60px', top: '90px', width: '220px', height: '220px' }, '<svg width="220" height="220" viewBox="0 0 220 220"><circle cx="110" cy="110" r="86" fill="none" stroke="rgba(255,255,255,0.8)" stroke-width="26"/><circle class="dn" cx="110" cy="110" r="86" fill="none" stroke="#2E8BC0" stroke-width="26" stroke-linecap="round" stroke-dasharray="540" stroke-dashoffset="540" transform="rotate(-90 110 110)"/></svg>');
          a.dn = t.querySelector('.dn'); a.dnv = el(t, { left: '60px', width: '220px', top: '178px', textAlign: 'center', fontSize: '38px', fontWeight: '700', color: '#022446' }, '0%');
          [['On time', '#2E8BC0'], ['At risk', '#F59E0B'], ['Late', '#EF4444']].forEach(([l, c], j) => el(t, { left: '320px', top: 130 + j * 50 + 'px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '17px', fontWeight: '600', color: '#334155' }, `<span style="width:14px;height:14px;border-radius:4px;background:${c}"></span>${l}`));
          a.tiles.push(t); }
        // 4. Audit
        { const t = T(3, 'linear-gradient(160deg,#DDF5F1,#BCE9E2)'); title(t, 'Site audit checklist');
          a.ac = ['Safety equipment checked', 'Fire exits clear', 'Training records up to date', 'Waste sorted correctly'].map((s, j) => { const r = el(t, { left: '30px', top: 82 + j * 64 + 'px', width: '480px', height: '52px', borderRadius: '12px', background: 'rgba(255,255,255,0.7)', display: 'flex', alignItems: 'center', gap: '12px', padding: '0 14px', fontSize: '16px', color: '#134E4A', fontWeight: '600' });
            const b = h('span', '', r); Object.assign(b.style, { width: '26px', height: '26px', borderRadius: '8px', background: '#fff', border: '2px solid #5EC4B6', display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none' }); h('span', '', r, s); return b; });
          a.tiles.push(t); }
        // 5. Dépenses
        { const t = T(4, 'linear-gradient(160deg,#CFE2DC,#A9CBC1)'); title(t, 'Team expenses');
          a.exp = el(t, { left: '30px', top: '78px', fontFamily: 'Outfit', fontSize: '58px', fontWeight: '600', color: '#0B2545' }, '€0');
          el(t, { left: '30px', top: '154px', fontSize: '16px', color: '#335C52' }, 'This month · 38 requests approved');
          el(t, { left: '30px', top: '200px', width: '480px', height: '130px' }, '<svg width="480" height="130" viewBox="0 0 480 130"><path class="sl" d="M0 110 L60 96 L120 102 L180 70 L240 80 L300 52 L360 60 L420 30 L480 22" fill="none" stroke="#0B6B5C" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="620" stroke-dashoffset="620"/></svg>');
          a.sl = t.querySelector('.sl'); a.tiles.push(t); }
        // 6. Opérations
        { const t = T(5, 'linear-gradient(160deg,#ECE4FF,#D6C8FF)'); title(t, 'Operations cockpit');
          a.gg = [['Uptime', 0.97, '#6D5DF6'], ['Output', 0.82, '#2E8BC0'], ['Quality', 0.91, '#0EA5A4']].map(([l, v, c], j) => { const g = el(t, { left: 30 + j * 166 + 'px', top: '96px', width: '150px', height: '220px' });
            g.innerHTML = `<svg width="150" height="150" viewBox="0 0 150 150"><circle cx="75" cy="75" r="58" fill="none" stroke="rgba(255,255,255,0.75)" stroke-width="16"/><circle class="gc" cx="75" cy="75" r="58" fill="none" stroke="${c}" stroke-width="16" stroke-linecap="round" stroke-dasharray="364" stroke-dashoffset="364" transform="rotate(-90 75 75)"/></svg><div style="position:absolute;left:0;width:150px;top:58px;text-align:center;font-size:26px;font-weight:700;color:#0B2545" class="gv">0%</div><div style="position:absolute;left:0;width:150px;top:170px;text-align:center;font-size:16px;font-weight:600;color:#4C3F99">${l}</div>`;
            return { c: g.querySelector('.gc'), t: g.querySelector('.gv'), v }; });
          a.tiles.push(t); }
        // Atouts intégrés.
        a.feat = el(root, { left: '0', width: '1920px', top: '946px', display: 'flex', justifyContent: 'center', gap: '16px' });
        a.pills = [['lock', 'Secure sign-in'], ['shield-check', 'Roles & permissions'], ['server', 'Hosted in Europe'], ['rocket', 'One-click publish']].map(([ic, t]) => {
          const p = h('span', 'pill ui', a.feat, `${icon(ic, 18, 2.2, '#8FC6E7')}${t}`);
          Object.assign(p.style, { background: '#022446', color: '#fff', height: '50px', padding: '0 24px', fontSize: '18px', borderRadius: '25px', boxShadow: '0 16px 40px rgba(1,36,70,0.25)' });
          return p;
        });
      },
      update(T) {
        a.bg(T);
        E.slash(T, 37.0, 0.55, a.root, a.bands);
        S(a.g1, { x: 500 + Math.sin(T * 0.5) * 120, y: 300 });
        S(a.g2, { x: 1450, y: 760 + Math.cos(T * 0.4) * 90 });
        const u = T - 37.0;
        S(a.cam, { s: 0.96 + u * 0.022, y: -u * 6 });
        a.tiles.forEach((t, i) => { const p = spring(T - 37.05 - i * 0.07, 180, 17); S(t, { s: Math.max(0.001, 0.85 + 0.15 * p), o: clamp(p * 1.5), y: (1 - p) * 60 + Math.sin(T * 1.1 + i) * 4 }); });
        const q = P(T, 37.35, 1.3, Ease.outCubic);
        a.bud.textContent = '€' + (1.24 * q).toFixed(2) + 'M';
        U.growBars(a.bb, T, 37.35);
        a.dn.setAttribute('stroke-dashoffset', String(540 * (1 - 0.86 * q)));
        a.dnv.textContent = Math.round(86 * q) + '%';
        a.ac.forEach((b, j) => { const on = T > 37.6 + j * 0.25; b.style.background = on ? '#0EA5A4' : '#fff'; b.style.borderColor = on ? '#0EA5A4' : '#5EC4B6'; b.innerHTML = on ? icon('check', 16, 3, '#fff') : ''; });
        a.exp.textContent = '€' + Math.round(18420 * q).toLocaleString('en-US');
        a.sl.setAttribute('stroke-dashoffset', String(620 * (1 - q)));
        a.gg.forEach((g) => { g.c.setAttribute('stroke-dashoffset', String(364 * (1 - g.v * q))); g.t.textContent = Math.round(g.v * 100 * q) + '%'; });
        a.pills.forEach((p, i) => { const k = P(T, 38.0 + i * 0.22, 0.55, Ease.outBack); S(p, { y: (1 - k) * 40, o: clamp(k), s: Math.max(0.001, 0.9 + 0.1 * k) }); });
        const out = P(T, 40.45, 0.3, Ease.inCubic);
        S(a.cam, { s: 0.96 + u * 0.022 - out * 0.3, o: 1 - out });
        a.feat.style.opacity = String(1 - out);
      },
    });
  }

  // ---------- Galaxie d'apps ----------
  {
    const a = {};
    const LABELS = ['Client portal', 'Budget tracker', 'Audit log', 'Sales cockpit', 'HR onboarding', 'Purchase requests', 'Project tracker', 'Quality checks', 'Contract review', 'Field reports', 'Training hub', 'Partner portal', 'Asset register', 'Board reporting'];
    const PALS = [['#E7E3FF', '#6D5DF6'], ['#DDF0FB', '#2E8BC0'], ['#DDF5F1', '#0EA5A4'], ['#0B4471', '#8FC6E7'], ['#ECE4FF', '#A855F7'], ['#FFFFFF', '#2E8BC0'], ['#CFE2DC', '#0B6B5C']];
    SCENES.push({
      id: 'galaxy',
      build(root) {
        a.root = root;
        el(root, { inset: '0', background: 'radial-gradient(1200px 800px at 50% 50%, #06213C 0%, #010A14 70%, #000 100%)' });
        a.sp = el(root, { inset: '0' });
        const r = seeded(404);
        a.items = Array.from({ length: 46 }, (_, i) => {
          const [bg, ac] = PALS[i % PALS.length];
          const w = 230 + r() * 170, hh = w * (0.6 + r() * 0.35);
          const t = el(a.sp, { left: '0', top: '0', width: w + 'px', height: hh + 'px', borderRadius: '18px', background: bg, overflow: 'hidden', boxShadow: '0 20px 50px rgba(0,0,0,0.45)', transformOrigin: '0 0' });
          el(t, { left: '0', right: '0', top: '0', height: '30px', background: ac, opacity: '0.9' });
          for (let q = 0; q < 3; q++) el(t, { left: '16px', top: 46 + q * 24 + 'px', width: (0.35 + r() * 0.5) * w + 'px', height: '10px', borderRadius: '5px', background: ac, opacity: String(0.25 + 0.2 * q) });
          const nb = 5 + Math.floor(r() * 4);
          for (let q = 0; q < nb; q++) { const bh = (0.2 + r() * 0.5) * hh * 0.5; el(t, { left: 16 + q * ((w - 32) / nb) + 'px', bottom: '14px', width: ((w - 32) / nb) * 0.62 + 'px', height: bh + 'px', borderRadius: '5px', background: ac, opacity: '0.8' }); }
          const ang = r() * Math.PI * 2, rad = 120 + Math.pow(r(), 0.7) * 1250;
          return { t, w, hh, x: Math.cos(ang) * rad * 1.5, y: Math.sin(ang) * rad * 0.9, z: r() * 2600, lab: i < LABELS.length ? LABELS[i] : null };
        });
        a.items.forEach((it) => { if (it.lab) { it.l = el(a.sp, { left: '0', top: '0' }); it.l.className = 'lbl'; it.l.textContent = it.lab; } });
      },
      update(T) {
        a.root.style.opacity = String(P(T, 40.6, 0.3, Ease.inOutSine));
        const u = clamp((T - 40.6) / 5.1);
        const D = lerp(-700, 2200, Ease.inOutSine(u));
        const rot = (T - 40.6) * 0.05;
        a.items.forEach((it, i) => {
          const zz = it.z + D + 900;
          const k = 900 / Math.max(60, zz);
          const cr = Math.cos(rot), sr = Math.sin(rot);
          const x = it.x * cr - it.y * sr * 0.6, y = it.y * cr + it.x * sr * 0.3;
          const sx = 960 + x * k - (it.w * k) / 2, sy = 540 + y * k - (it.hh * k) / 2;
          const fade = clamp((zz - 250) / 300) * P(T, 40.65 + (i % 10) * 0.05, 0.5);
          S(it.t, { x: sx, y: sy, s: k, o: fade });
          it.t.style.zIndex = String(Math.round(10000 - zz));
          if (it.l) {
            const lp = P(T, 41.0 + (i % 14) * 0.22, 0.5);
            S(it.l, { x: sx + it.w * k + 14, y: sy + 6, o: lp * fade, s: 1 });
            it.l.style.zIndex = String(Math.round(10001 - zz));
          }
        });
        const out = P(T, 45.45, 0.3, Ease.inCubic);
        a.root.style.filter = out > 0 ? `blur(${(out * 24).toFixed(1)}px)` : 'none';
      },
    });
  }

  // ---------- Orbe ----------
  {
    const a = {};
    SCENES.push({
      id: 'orb',
      build(root) {
        a.root = root;
        el(root, { inset: '0', background: 'linear-gradient(160deg,#BFD8F2 0%,#DCD3FF 55%,#F2EEFF 100%)' });
        a.halo = C.glow(root, 1500, 'rgba(255,255,255,0.75)', 1);
        a.rings = [0, 1, 2].map(() => { const r = el(root, { left: '0', top: '0', width: '600px', height: '600px', borderRadius: '50%', border: '2px solid rgba(255,255,255,0.7)' }); return r; });
        a.orb = el(root, { left: '810px', top: '330px', width: '300px', height: '300px', borderRadius: '50%', background: 'radial-gradient(circle at 38% 32%, #FFFFFF 0%, #E2D4FF 22%, #8F7CF5 55%, #2E8BC0 100%)', boxShadow: '0 0 160px rgba(109,93,246,0.55), 0 0 60px rgba(46,139,192,0.45)' });
        a.txt = el(root, { left: '0', width: '1920px', top: '760px', textAlign: 'center', fontFamily: 'Outfit', fontSize: '58px', fontWeight: '500', color: '#0B2545' }, 'Can you <span class="dcgrad">imagine it?</span>');
      },
      update(T) {
        a.root.style.opacity = String(P(T, 45.6, 0.3, Ease.inOutSine));
        const u = T - 45.6;
        S(a.halo, { x: 960, y: 480, s: 0.8 + 0.1 * Math.sin(T * 2) });
        S(a.orb, { s: lerp(0.4, 1, Ease.outBack(clamp(u / 0.9))) + 0.03 * Math.sin(T * 4), r: u * 30 });
        a.rings.forEach((r, i) => { const k = ((u + i * 0.55) % 1.65) / 1.65; S(r, { x: 660, y: 180, s: 0.4 + k * 1.4, o: (1 - k) * 0.8 }); });
        const tp = P(T, 46.3, 0.7, Ease.outCubic);
        S(a.txt, { o: tp, y: (1 - tp) * 20, blur: (1 - tp) * 10 });
        const out = P(T, 47.6, 0.35, Ease.inCubic);
        S(a.orb, { s: (lerp(0.4, 1, Ease.outBack(clamp(u / 0.9))) + 0.03 * Math.sin(T * 4)) * (1 + out * 5), o: 1 });
        a.txt.style.opacity = String(tp * (1 - out));
      },
    });
  }

  // ---------- Signature ----------
  {
    const a = {};
    SCENES.push({
      id: 'end',
      build(root) {
        a.root = root;
        el(root, { inset: '0', background: '#000' });
        a.g = C.glow(root, 1500, 'rgba(15,88,133,0.55)', 1);
        a.g2 = C.glow(root, 1000, 'rgba(109,93,246,0.30)', 1);
        a.lk = U.lockup(root, 170, true);
        Object.assign(a.lk.style, { position: 'absolute', left: '0', width: '1920px', top: '360px', justifyContent: 'center' });
        a.tag = el(root, { left: '0', width: '1920px', top: '600px', textAlign: 'center', fontFamily: 'Outfit', fontSize: '50px', fontWeight: '500' }, '<span class="dcgrad-l">Let’s make it real.</span>');
        a.by = el(root, { left: '0', width: '1920px', top: '730px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '20px', fontFamily: 'Montserrat', fontSize: '26px', fontWeight: '600', color: 'rgba(255,255,255,0.7)' });
        h('span', '', a.by, 'by');
        C.leytonLogo(a.by, 80, true);
        a.black = el(root, { inset: '0', background: '#000', opacity: '0' });
      },
      update(T) {
        const u = T - 47.8;
        a.root.style.opacity = String(P(T, 47.8, 0.3));
        S(a.g, { x: 960, y: 520, s: 0.8 + 0.06 * Math.sin(T * 1.5), o: P(T, 48.0, 1.2) });
        S(a.g2, { x: 1100, y: 560, s: 0.8, o: P(T, 48.2, 1.2) * 0.9 });
        const dirs = [[0, 0], [0, -1], [1, 0], [0, 1], [-1, 0]];
        a.lk._sym._d.forEach((d, i) => { const t0 = 48.05 + (i ? 0.08 + i * 0.04 : 0); const p = spring(T - t0, 210, 15); S(d, { x: dirs[i][0] * (1 - p) * 220, y: dirs[i][1] * (1 - p) * 220, r: 45 + (1 - p) * 180, s: Math.max(0.001, p), o: clamp((T - t0) * 7) }); });
        a.lk._ch.forEach((c, i) => { const p = P(T, 48.3 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 60, o: p, blur: (1 - p) * 8 }); });
        const tp = P(T, 48.45, 0.8, Ease.outCubic);
        S(a.tag, { o: tp, y: (1 - tp) * 24, blur: (1 - tp) * 10 });
        const bp = P(T, 49.7, 0.7, Ease.outQuint);
        S(a.by, { o: bp, y: (1 - bp) * 20 });
        S(a.lk, { s: 1 + u * 0.006 });
        a.black.style.opacity = String(P(T, 53.2, 0.8, Ease.inOutSine));
      },
    });
  }
})();

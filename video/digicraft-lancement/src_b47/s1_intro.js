// Ouverture : « Imagine… any app », trois apps métier, « Just by describing it », symbole, « Meet DigiCraft ».
(function () {
  const { h, S, P, Ease, lerp, clamp, spring, icon } = E;
  const el = C.el;

  // ---------- 1. Intro texte ----------
  {
    const a = {};
    SCENES.push({
      id: 'intro',
      build(root) {
        a.white = el(root, { inset: '0', background: '#F7FAFC' });
        a.z = el(root, { inset: '0', transformOrigin: '50% 50%' });
        const mk = (white) => {
          const row = el(a.z, { left: '0', width: '1920px', top: '470px', display: 'flex', justifyContent: 'center', fontFamily: 'Outfit', fontWeight: '500', fontSize: '76px', letterSpacing: '-0.01em', color: '#0B2545', whiteSpace: 'pre' });
          const A = h('span', '', row);
          const ws = 'Imagine being able to build '.split(' ').filter(Boolean).map((t) => { const s = h('span', '', A, t + ' '); s.style.display = 'inline-block'; return s; });
          const B = h('span', white ? '' : 'dcgrad', row);
          B.style.display = 'inline-block';
          const any = h('span', '', B, 'any '); const app = h('span', '', B, 'app');
          [any, app].forEach((s) => (s.style.display = 'inline-block'));
          if (white) { A.style.visibility = 'hidden'; B.style.color = '#fff'; }
          return { row, ws, B, any, app };
        };
        a.t = mk(false);
        // Vague de couleur qui monte et recouvre l'écran.
        a.wave = el(root, { left: '-400px', width: '2720px', top: '0', height: '2400px', borderRadius: '50% 50% 0 0 / 22% 22% 0 0', overflow: 'hidden' });
        a.wf = el(a.wave, { left: '400px', top: '0', width: '1920px', height: '1080px' });
        a.field = U.field(a.wf, 'dawn', 1);
        a.zw = el(a.wave, { left: '400px', top: '0', width: '1920px', height: '1080px', transformOrigin: '50% 50%' });
        a.w = (() => { const s = a.z; a.z = a.zw; const r = mk(true); a.z = s; return r; })();
      },
      update(T) {
        const tw = [0.3, 0.72, 0.95, 1.12, 1.32];
        a.t.ws.forEach((w, i) => { const p = P(T, tw[i], 0.7, Ease.outQuint); S(w, { y: (1 - p) * 26, o: p, blur: (1 - p) * 10 }); });
        [[a.t.any, 1.62], [a.t.app, 1.86]].forEach(([w, t]) => { const p = P(T, t, 0.8, Ease.outQuint); S(w, { y: (1 - p) * 26, o: p, blur: (1 - p) * 12 }); });
        // Montée de la vague (2,2 → 2,9) puis le mot « any app » glisse à droite.
        const wp = P(T, 2.15, 0.85, Ease.inOutCubic);
        const top = lerp(1150, -700, wp);
        a.wave.style.top = top + 'px';
        a.wf.style.top = -top + 'px'; a.zw.style.top = -top + 'px';
        a.field(T, 1.4);
        const gp = P(T, 2.3, 1.0, Ease.inOutCubic);
        const fade = P(T, 2.15, 0.5, Ease.outCubic);
        a.t.ws.forEach((w) => (w.style.opacity = String(clamp(1 - fade))));
        [a.t, a.w].forEach((L) => S(L.B, { x: gp * 330, s: 1 + gp * 0.12 }));
        const zo = P(T, 3.1, 0.35, Ease.inCubic);
        S(a.zw, { s: 1 + zo * 0.25, o: 1 - zo, blur: zo * 14 });
        S(a.z, { s: 1 + T * 0.012 });
      },
    });
  }

  // ---------- 2. Trois apps métier ----------
  {
    const a = {};
    const shot = (root, pal, seed) => {
      const g = el(root, { inset: '0', overflow: 'hidden' });
      const f = U.field(g, pal, seed);
      const cam = el(g, { inset: '0', perspective: '2200px' });
      const rig = el(cam, { inset: '0', transformStyle: 'preserve-3d' });
      return { g, f, rig };
    };
    SCENES.push({
      id: 'cards',
      build(root) {
        // A : onboarding RH (« for any team »)
        a.A = shot(root, 'dusk', 0);
        {
          const c = el(a.A.rig, { left: '330px', top: '200px', width: '1260px', height: '680px' }); c.className = 'glass ui';
          el(c, { left: '44px', top: '36px', fontSize: '15px', fontWeight: '700', letterSpacing: '0.16em', color: '#6D5DF6' }, 'PEOPLE · ONBOARDING');
          el(c, { left: '44px', top: '64px', fontFamily: 'Outfit', fontSize: '44px', fontWeight: '600', color: '#0B2545' }, 'Q4 new joiners');
          el(c, { right: '44px', top: '58px' }, '').appendChild(U.badge(h('div'), '12 starting this month', 'vio'));
          a.rowsA = [['Sarah Martin', 'Consultant · Paris', '#2E8BC0', 0.92], ['Tom Becker', 'Data analyst · Lyon', '#6D5DF6', 0.66], ['Inès Duval', 'Project lead · Lille', '#0EA5A4', 0.48], ['Marc Petit', 'Tax advisor · Nantes', '#F59E0B', 0.3]].map(([n, r, col, v], i) => {
            const y = 160 + i * 112;
            const row = el(c, { left: '44px', top: y + 'px', width: '700px', height: '96px' }); row.className = 'card';
            U.avatar(row, 18, 22, 52, n.split(' ').map((s) => s[0]).join(''), col);
            el(row, { left: '88px', top: '20px', fontSize: '20px', fontWeight: '700' }, n);
            el(row, { left: '88px', top: '50px', fontSize: '15px', color: '#64748B' }, r);
            const pr = U.progress(row, 420, 42, 200, col);
            const pct = el(row, { left: '636px', top: '34px', fontSize: '16px', fontWeight: '700', color: '#334155' });
            return { row, pr, pct, v };
          });
          const tk = el(c, { left: '790px', top: '160px', width: '426px', height: '448px' }); tk.className = 'card';
          el(tk, { left: '26px', top: '22px', fontSize: '19px', fontWeight: '700' }, 'Week one checklist');
          a.checks = ['Contract signed', 'Laptop delivered', 'Accounts created', 'Team intro booked', 'Training plan shared'].map((t, i) => {
            const r = el(tk, { left: '26px', top: 74 + i * 72 + 'px', width: '374px', height: '56px', display: 'flex', alignItems: 'center', gap: '14px', fontSize: '17px', color: '#334155' });
            const box = h('span', '', r); Object.assign(box.style, { width: '28px', height: '28px', borderRadius: '8px', border: '2px solid #CBD5E1', display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none' });
            const tick = h('span', '', box, icon('check', 18, 3, '#fff')); tick.style.display = 'flex';
            h('span', '', r, t);
            return { box, tick };
          });
          a.cA = c;
        }
        // B : circuit de validation des notes de frais (« for any process »)
        a.B = shot(root, 'violet', 2);
        {
          const c = el(a.B.rig, { left: '300px', top: '210px', width: '1320px', height: '660px' }); c.className = 'glass ui';
          el(c, { left: '48px', top: '38px', fontSize: '15px', fontWeight: '700', letterSpacing: '0.16em', color: '#2E8BC0' }, 'FINANCE · APPROVALS');
          el(c, { left: '48px', top: '66px', fontFamily: 'Outfit', fontSize: '44px', fontWeight: '600', color: '#0B2545' }, 'Expense approval flow');
          const steps = [['file-plus', 'Submitted'], ['user-check', 'Manager review'], ['landmark', 'Finance check'], ['wallet', 'Reimbursed']];
          a.line = el(c, { left: '150px', top: '236px', width: '1020px', height: '6px', borderRadius: '3px', background: '#E2E8F0' });
          a.lineF = el(a.line, { left: '0', top: '0', bottom: '0', width: '1020px', borderRadius: '3px', background: 'linear-gradient(90deg,#2E8BC0,#6D5DF6)', transformOrigin: '0 50%' });
          a.nodes = steps.map(([ic, t], i) => {
            const x = 150 + i * 340;
            const n = el(c, { left: x - 46 + 'px', top: '193px', width: '92px', height: '92px', borderRadius: '28px', background: '#fff', border: '2px solid #E2E8F0', display: 'flex', alignItems: 'center', justifyContent: 'center' });
            n.innerHTML = icon(ic, 38, 2, '#94A3B8');
            el(c, { left: x - 120 + 'px', width: '240px', top: '300px', textAlign: 'center', fontSize: '19px', fontWeight: '700', color: '#334155' }, t);
            return n;
          });
          const rq = el(c, { left: '48px', top: '388px', width: '1224px', height: '220px' }); rq.className = 'card';
          U.avatar(rq, 30, 40, 64, 'JL', '#6D5DF6');
          el(rq, { left: '116px', top: '40px', fontSize: '24px', fontWeight: '700' }, 'Client workshop · travel & dinner');
          el(rq, { left: '116px', top: '78px', fontSize: '17px', color: '#64748B' }, 'Julien Leroy · Consulting · submitted today');
          el(rq, { left: '116px', top: '126px', display: 'flex', gap: '10px' }).append(U.badge(h('div'), 'Receipt attached', 'info'), U.badge(h('div'), 'Within policy', 'ok'));
          el(rq, { right: '300px', top: '44px', fontSize: '40px', fontWeight: '700', color: '#0B2545' }, '€420.00');
          a.approve = el(rq, { right: '36px', top: '120px', width: '220px', height: '60px', borderRadius: '16px', background: 'linear-gradient(90deg,#2E8BC0,#6D5DF6)', color: '#fff', fontSize: '20px', fontWeight: '700', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px' }, `${icon('check', 22, 2.6, '#fff')}Approve`);
          a.cB = c;
        }
        // C : pilotage groupe (« at any scale »)
        a.Cc = shot(root, 'sky', 4);
        {
          const c = el(a.Cc.rig, { left: '260px', top: '170px', width: '1400px', height: '740px' }); c.className = 'glass ui';
          el(c, { left: '48px', top: '38px', fontSize: '15px', fontWeight: '700', letterSpacing: '0.16em', color: '#2E8BC0' }, 'GROUP · PERFORMANCE');
          el(c, { left: '48px', top: '66px', fontFamily: 'Outfit', fontSize: '44px', fontWeight: '600', color: '#0B2545' }, 'All entities, one view');
          el(c, { right: '48px', top: '74px', display: 'flex', gap: '10px' }).append(U.badge(h('div'), '14 countries', 'info'), U.badge(h('div'), 'Live data', 'ok'));
          a.kC = [['Revenue', '€', 48.2, 'M', '+6.2%', '#16A34A', 'trending-up'], ['Sites', '', 42, '', '+3', '#2E8BC0', 'map-pin'], ['Employees', '', 12400, '', '+4.1%', '#6D5DF6', 'users'], ['On-time delivery', '', 96.4, '%', '+1.8 pts', '#0EA5A4', 'gauge']]
            .map(([l, pre, v, suf, d, col, ic], i) => ({ c: U.kpi(c, 48 + i * 330, 150, 306, l, '', d, col, ic), pre, v, suf }));
          const ch = el(c, { left: '48px', top: '296px', width: '860px', height: '400px' }); ch.className = 'card';
          el(ch, { left: '26px', top: '22px', fontSize: '19px', fontWeight: '700' }, 'Revenue by month');
          a.path = el(ch, { left: '26px', top: '80px', width: '808px', height: '290px' },
            '<svg width="808" height="290" viewBox="0 0 808 290"><defs><linearGradient id="lgC" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2E8BC0" stop-opacity="0.28"/><stop offset="1" stop-color="#2E8BC0" stop-opacity="0"/></linearGradient></defs><path class="ar" d="M0 240 C80 230 120 200 180 205 S300 150 360 160 S480 110 540 118 S660 60 720 52 L808 30 L808 290 L0 290 Z" fill="url(#lgC)"/><path class="ln" d="M0 240 C80 230 120 200 180 205 S300 150 360 160 S480 110 540 118 S660 60 720 52 L808 30" fill="none" stroke="#2E8BC0" stroke-width="5" stroke-linecap="round" stroke-dasharray="1100" stroke-dashoffset="1100"/></svg>');
          const rg = el(c, { left: '932px', top: '296px', width: '420px', height: '400px' }); rg.className = 'card';
          el(rg, { left: '26px', top: '22px', fontSize: '19px', fontWeight: '700' }, 'By region');
          a.reg = [['France', 0.92], ['DACH', 0.74], ['UK & Ireland', 0.61], ['Iberia', 0.48], ['Benelux', 0.4]].map(([n, v], i) => {
            el(rg, { left: '26px', top: 80 + i * 60 + 'px', fontSize: '16px', color: '#334155', fontWeight: '600' }, n);
            return { pr: U.progress(rg, 170, 88 + i * 60, 220, '#6D5DF6'), v };
          });
          a.cC = c;
        }
      },
      update(T) {
        // Passages en volet horizontal avec flou de mouvement.
        U.whip(a.A.g, T, 3.08, 4.6, 1);
        U.whip(a.B.g, T, 4.62, 6.2, 1);
        U.whip(a.Cc.g, T, 6.22, 7.72, 1);
        a.A.f(T); a.B.f(T); a.Cc.f(T);
        const cam = (rig, t0) => { const u = T - t0; S(rig, { s: 1.04 + u * 0.035, ry: lerp(-7, 5, clamp(u / 1.8)), rx: 4 - u * 1.5, y: -u * 10 }); };
        cam(a.A.rig, 3.3); cam(a.B.rig, 4.65); cam(a.Cc.rig, 6.25);
        a.rowsA.forEach((r, i) => { const v = r.v * P(T, 3.45 + i * 0.08, 1.1, Ease.outCubic); r.pr(v); r.pct.textContent = Math.round(v * 100) + '%'; S(r.row, { y: (1 - P(T, 3.3 + i * 0.06, 0.6, Ease.outQuint)) * 50, o: P(T, 3.3 + i * 0.06, 0.4) }); });
        a.checks.forEach((k, i) => { const on = T > 3.6 + i * 0.18; k.box.style.background = on ? '#16A34A' : '#fff'; k.box.style.borderColor = on ? '#16A34A' : '#CBD5E1'; S(k.tick, { s: on ? spring(T - 3.6 - i * 0.18, 300, 14) : 0.001 }); });
        const lp = P(T, 4.85, 1.1, Ease.inOutCubic);
        S(a.lineF, { sx: Math.max(0.001, lp) });
        a.nodes.forEach((n, i) => {
          const on = lp >= i / 3 - 0.001;
          n.style.background = on ? 'linear-gradient(135deg,#2E8BC0,#6D5DF6)' : '#fff';
          n.style.borderColor = on ? 'transparent' : '#E2E8F0';
          n.innerHTML = n.innerHTML.replace(/stroke="[^"]+"/, `stroke="${on ? '#fff' : '#94A3B8'}"`);
          const t = 4.85 + (i / 3) * 1.1;
          S(n, { s: 1 + 0.12 * Math.exp(-(T - t) * 8) * (T > t ? 1 : 0) });
        });
        const ap = T - 5.75;
        S(a.approve, { s: ap > 0 ? 1 - 0.08 * Math.exp(-ap * 10) * Math.sin(ap * 30) : 1 });
        a.kC.forEach((k, i) => {
          const p = P(T, 6.35 + i * 0.07, 1.0, Ease.outCubic);
          const v = k.v * p;
          k.c.querySelector('.v').textContent = k.pre + (k.v % 1 ? v.toFixed(1) : Math.round(v).toLocaleString('en-US')) + k.suf;
          S(k.c, { y: (1 - P(T, 6.25 + i * 0.06, 0.6, Ease.outQuint)) * 40 });
        });
        const dp = P(T, 6.45, 1.2, Ease.inOutCubic);
        a.path.querySelector('.ln').setAttribute('stroke-dashoffset', String(1100 * (1 - dp)));
        a.path.querySelector('.ar').style.opacity = String(dp);
        a.reg.forEach((r, i) => r.pr(r.v * P(T, 6.5 + i * 0.07, 0.9, Ease.outCubic)));
      },
    });
  }

  // ---------- 3. « Just by describing it. » ----------
  {
    const a = {};
    SCENES.push({
      id: 'think',
      build(root) {
        a.root = root;
        a.scn = U.scenery(root, 'b47/lavender_lake', { drift: 0.3, from: 1.0, to: 1.07 });
        a.sun = C.glow(root, 2200, 'rgba(20,10,60,0.35)', 1);
        a.sun2 = C.glow(root, 10, 'rgba(0,0,0,0)', 0);
        a.hz = el(root, { left: '0', right: '0', height: '10px', top: '1200px' });
        a.line = el(root, { left: '0', width: '1920px', top: '440px', textAlign: 'center', fontFamily: 'Outfit', fontWeight: '400', fontSize: '70px', color: '#fff', letterSpacing: '0.005em' });
        a.w = ['Just', 'by', 'describing', 'it.'].map((t) => { const s = h('span', '', a.line, t); s.style.display = 'inline-block'; s.style.margin = '0 12px'; return s; });
        a.black = el(root, { inset: '0', background: '#000', opacity: '0' });
      },
      update(T) {
        const u = T - 7.75;
        a.scn(T);
        a.root.style.opacity = String(P(T, 7.75, 0.3, Ease.inOutSine));
        S(a.sun, { x: 960, y: lerp(1500, 1260, clamp(u / 2)), s: 1 + u * 0.05 });
        S(a.sun2, { x: 960, y: lerp(1420, 1180, clamp(u / 2)), s: 1 + u * 0.04 });
        S(a.hz, { y: -u * 30 });
        [7.82, 8.05, 8.3, 8.62].forEach((t, i) => { const p = P(T, t, 0.8, Ease.outCubic); S(a.w[i], { o: p, blur: (1 - p) * 14, y: (1 - p) * 10 }); });
        a.black.style.opacity = String(P(T, 9.25, 0.45, Ease.inOutCubic));
        a.line.style.opacity = String(1 - P(T, 9.15, 0.4));
      },
    });
  }

  // ---------- 4. Symbole sur fond noir ----------
  {
    const a = {};
    SCENES.push({
      id: 'symbol',
      build(root) {
        el(root, { inset: '0', background: '#000' });
        a.glow = C.glow(root, 900, 'rgba(46,139,192,0.55)', 0);
        a.glow2 = C.glow(root, 600, 'rgba(109,93,246,0.45)', 0);
        a.sym = U.symbol(root, 300);
        Object.assign(a.sym.style, { left: '810px', top: '390px' });
      },
      update(T) {
        const dirs = [[0, 0], [0, -1], [1, 0], [0, 1], [-1, 0]];
        a.sym._d.forEach((d, i) => {
          const t0 = 9.68 + (i ? 0.1 + i * 0.05 : 0);
          const p = spring(T - t0, 200, 16);
          S(d, { x: dirs[i][0] * (1 - p) * 420, y: dirs[i][1] * (1 - p) * 420, r: 45 + (1 - p) * 180, s: Math.max(0.001, p), o: clamp((T - t0) * 6) });
        });
        const gp = P(T, 9.7, 0.9);
        S(a.sym, { r: lerp(-90, 0, P(T, 9.68, 0.9, Ease.outQuint)), s: 1 + 0.04 * Math.sin(T * 6) * gp });
        S(a.glow, { x: 960, y: 540, o: gp * (0.8 + 0.2 * Math.sin(T * 7)), s: 0.7 + 0.4 * gp });
        S(a.glow2, { x: 960, y: 540, o: gp * 0.8, s: 0.6 + 0.5 * gp });
      },
    });
  }

  // ---------- 5. « Meet DigiCraft » ----------
  {
    const a = {};
    SCENES.push({
      id: 'meet',
      build(root) {
        a.clip = el(root, { inset: '0' });
        el(a.clip, { inset: '0', background: '#F7FAFC' });
        a.g1 = C.glow(a.clip, 1400, 'rgba(143,198,231,0.45)', 1);
        a.g2 = C.glow(a.clip, 1100, 'rgba(183,166,255,0.35)', 1);
        a.z = el(a.clip, { inset: '0', transformOrigin: '50% 50%' });
        a.lk = U.lockup(a.z, 190);
        Object.assign(a.lk.style, { position: 'absolute', left: '0', width: '1920px', top: '420px', justifyContent: 'center' });
        a.by = el(a.z, { left: '0', width: '1920px', top: '680px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '22px', fontFamily: 'Montserrat', fontSize: '30px', fontWeight: '600', color: '#6B7B88' });
        h('span', '', a.by, 'by');
        C.leytonLogo(a.by, 92);
      },
      update(T) {
        const R = lerp(0, 1400, Ease.inOutCubic(clamp((T - 10.7) / 0.5)));
        a.clip.style.clipPath = `circle(${R.toFixed(1)}px at 960px 540px)`;
        S(a.g1, { x: 700 + Math.sin(T) * 80, y: 380 });
        S(a.g2, { x: 1300, y: 720 + Math.cos(T) * 60 });
        // Le symbole part du centre puis le mot se dévoile.
        if (a.off === undefined) { S(a.lk._sym, {}); S(a.z, {}); const r = a.lk._sym.getBoundingClientRect(); if (r.width) a.off = 960 - (r.left + r.width / 2); }
        const sp = P(T, 10.85, 0.75, Ease.inOutQuint);
        const off = a.off || 0;
        S(a.lk._sym, { x: (1 - sp) * off, s: lerp(1.25, 1, sp) });
        a.lk._ch.forEach((c, i) => { const p = P(T, 11.1 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 60, o: p, blur: (1 - p) * 8 }); });
        const bp = P(T, 11.6, 0.7, Ease.outQuint);
        S(a.by, { y: (1 - bp) * 24, o: bp });
        const zo = P(T, 12.35, 0.4, Ease.inCubic);
        S(a.z, { s: 1 + (T - 10.7) * 0.025 + zo * 0.15, o: 1 - zo, blur: zo * 10 });
      },
    });
  }
})();

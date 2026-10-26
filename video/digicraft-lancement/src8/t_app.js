// Tutoriel : l'application DigiCraft pas à pas (étapes 1 à 4 + usage mobile).
(function () {
  const { h, S, P, Ease, lerp, clamp, words, revealWords, hideWords, icon, spring, fmt } = E;
  const U = window.UI;
  const { el, px, els, BW, BH, CH, PROMPT } = U;
  const ASK = 'Add a headcount chart by department.';
  const BLUE = '#2E8BC0';
  let a = {};

  // Légendes : [début, fin, numéro d'étape (0 = bonus), titre, description]
  const CAPS = [
    [4.95, 14.15, 1, ['Describe what', 'you need'], 'Type your request in plain language, or start from a template.'],
    [14.45, 26.15, 2, ['DigiCraft', 'builds it'], 'Screens, data, logic and code are created and tested, live.'],
    [26.45, 35.75, 3, ['Refine by', 'chatting'], 'Ask for changes in your own words. The preview updates instantly.'],
    [36.05, 45.35, 4, ['Publish', 'and share'], 'Go live in one click, then choose who can access your app.'],
    [45.65, 53.3, 0, ['Use it', 'anywhere'], 'On desktop and mobile, hosted securely in Europe.'],
  ];

  // Caméra sur le navigateur : [t, x, y, échelle]
  const B0 = [330, 30, 0.62];
  const CAM = [
    [4.35, 330, 900, 0.55], [5.45, ...B0], [7.0, ...B0], [7.9, 290, -76, 0.9], [11.9, 290, -76, 0.9], [12.6, 250, -60, 0.87],
    [13.9, ...B0], [14.5, ...B0], [15.2, 698, 40, 0.9], [17.3, 698, 40, 0.9], [18.2, 40, -28, 0.85], [20.7, 40, -28, 0.85], [21.7, ...B0],
    [26.4, ...B0], [27.1, 805, -256, 1.0], [30.3, 805, -256, 1.0], [31.0, 727, 125, 0.95], [31.9, 727, 125, 0.95], [32.7, 61, -90, 1.0],
    [35.4, 61, -90, 1.0], [36.3, -340, 169, 1.0], [38.6, -340, 169, 1.0], [39.4, 40, -5, 0.85], [44.8, 40, -5, 0.85], [45.9, 250, 60, 0.58],
  ];
  function cam(T) {
    if (T <= CAM[0][0]) return CAM[0].slice(1);
    for (let i = 1; i < CAM.length; i++) {
      if (T <= CAM[i][0]) {
        const [t0, ...v0] = CAM[i - 1], [t1, ...v1] = CAM[i];
        const p = Ease.inOutCubic(clamp((T - t0) / (t1 - t0)));
        return v0.map((v, k) => lerp(v, v1[k], p));
      }
    }
    return CAM[CAM.length - 1].slice(1);
  }

  // Curseur : segments [t0, t1, x0, y0, x1, y1] en coordonnées du navigateur, clics [t].
  const CUR = [
    [6.0, 6.8, 1320, 860, 602, 758], [7.25, 7.75, 602, 758, 520, 522], [11.9, 12.55, 900, 700, 1160, 639],
    [27.0, 27.55, 420, 760, 250, 886], [36.35, 37.1, 1200, 320, 1480, 91], [40.9, 41.65, 1000, 700, 1262, 450],
  ];
  const VIS = [[6.0, 8.0], [11.9, 13.1], [27.0, 27.9], [36.35, 38.2], [40.9, 42.9]];
  const CLICKS = [7.8, 12.7, 27.6, 37.2, 41.75];

  SCENES.push({
    id: 'app',
    build(root) {
      a.root = root;
      a.bg = C.warmBg(root, 1);
      a.gl = C.glow(root, 1900, 'rgba(126,190,230,0.45)', 0);
      a.ring = C.ringFx(root);

      // --- navigateur (même maquette que la vidéo de lancement)
      const persp = el(root, { inset: '0', perspective: '2600px' });
      const br = el(persp, px({ left: 960 - BW / 2, top: 540 - BH / 2, width: BW, height: BH, borderRadius: 22, overflow: 'hidden', background: '#fff', boxShadow: '0 60px 140px rgba(2,36,70,0.22), 0 10px 30px rgba(2,36,70,0.10)' }));
      a.br = br;
      const chrome = el(br, px({ left: 0, top: 0, width: BW, height: CH, background: 'linear-gradient(#F8F9FB,#EEF0F3)', borderBottom: '1px solid #E3E6EA' }));
      ['#FF5F57', '#FEBC2E', '#28C840'].forEach((c, i) => el(chrome, px({ left: 26 + i * 26, top: 21, width: 14, height: 14, borderRadius: 7, background: c })));
      el(chrome, px({ left: BW / 2 - 260, top: 11, width: 520, height: 34, borderRadius: 17, background: '#fff', border: '1px solid #E3E6EA', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontSize: 17, color: '#4B5563' }), `${icon('lock', 15, 2.2, '#6B7280')}digicraft.leyton-cognitx.com`);
      const content = el(br, px({ left: 0, top: CH, width: BW, height: BH - CH, overflow: 'hidden' }));
      a.home = U.buildHome(content);
      a.build = U.buildBuild(content);
      a.cursor = el(br, px({ left: 0, top: 0, zIndex: 30 }), U.cursorSvg());
      a.clickFx = el(br, px({ left: 0, top: 0, width: 60, height: 60, marginLeft: -30, marginTop: -30, borderRadius: 30, border: `3px solid ${BLUE}`, zIndex: 29 }));

      // --- étape 3 : champ de discussion, nouvelle demande et nouvelle carte
      const left = els.bubble.parentElement;
      a.chatIn = el(left, px({ left: 22, bottom: 22, width: 426, height: 64, borderRadius: 16, border: `2px solid ${BLUE}`, background: '#fff', display: 'flex', alignItems: 'center', padding: '0 16px', fontFamily: 'DM Sans', fontSize: 18, color: '#1F2937', zIndex: 3 }));
      a.chatTxt = h('span', '', a.chatIn);
      a.chatSend = el(a.chatIn, px({ right: 10, top: 10, width: 44, height: 44, borderRadius: 12, background: BLUE, display: 'flex', alignItems: 'center', justifyContent: 'center' }), icon('send', 20, 2.2, '#fff'));
      a.old = [els.bubble, ...els.steps, els.status];
      a.q2 = el(left, px({ left: 22, top: 22, width: 426, padding: '16px 18px', borderRadius: 16, background: '#EAF4FB', fontFamily: 'DM Sans', fontSize: 17, lineHeight: 1.45, color: '#0F3A57' }), ASK);
      a.st2 = [['sparkles', 'Updating your app', ''], ['file-code', 'Updated', 'app/dashboard/page.tsx'], ['circle-check', 'Preview updated', '']].map(([ic, verb, code], i) =>
        el(left, px({ left: 22, top: 110 + i * 50, width: 430, height: 40, display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'DM Sans', fontSize: 18, color: '#374151' }),
          `${icon(ic, 20, 2, ic === 'circle-check' ? '#16A34A' : '#6B7280')}<span style="font-weight:600">${verb}</span>${code ? `<span style="font-family:'JetBrains Mono';font-size:14px;background:#F3F4F6;border-radius:6px;padding:3px 8px;color:#4B5563">${code}</span>` : ''}`));
      const app = els.chart.parentElement;
      a.card = el(app, px({ left: 238, top: 226, width: 390, height: 300, borderRadius: 14, border: `2px solid ${BLUE}`, background: '#fff', zIndex: 5 }));
      el(a.card, px({ left: 18, top: 16, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 18, color: '#0B2545' }), 'Headcount by department');
      a.hbars = [['Finance', 0.62], ['Sales', 0.9], ['HR', 0.38], ['IT', 0.56], ['Operations', 0.74]].map(([lab, v], i) => {
        el(a.card, px({ left: 18, top: 62 + i * 44, width: 90, fontFamily: 'DM Sans', fontSize: 14, color: '#4B5563' }), lab);
        return el(a.card, px({ left: 110, top: 62 + i * 44, width: v * 240, height: 22, borderRadius: 6, background: i === 1 ? BLUE : '#9FD0EE', transformOrigin: '0 50%' }));
      });
      a.hl = el(app, px({ left: 230, top: 218, width: 406, height: 316, borderRadius: 18, boxShadow: `0 0 0 4px rgba(46,139,192,0.55), 0 0 40px rgba(46,139,192,0.45)`, zIndex: 6 }));

      // --- étape 4 : fenêtre de partage
      const bv = a.build;
      a.dim = el(bv, { inset: '0', background: 'rgba(1,30,50,0.35)', zIndex: 8 });
      const m = el(bv, px({ left: 715, top: 262, width: 640, height: 430, borderRadius: 22, background: '#fff', boxShadow: '0 40px 90px rgba(1,30,50,0.35)', zIndex: 9, fontFamily: 'DM Sans' }));
      a.modal = m;
      el(m, px({ left: 30, top: 26, fontWeight: 700, fontSize: 26, color: '#0B2545' }), 'Share “CEO KPIs”');
      el(m, px({ right: 28, top: 30 }), icon('x', 24, 2.2, '#9CA3AF'));
      el(m, px({ left: 30, top: 92, width: 450, height: 56, borderRadius: 12, border: '1px solid #E5E7EB', display: 'flex', alignItems: 'center', gap: 10, padding: '0 14px', fontSize: 17, color: '#374151' }), `${icon('link', 20, 2, BLUE)}digicraft.leyton-cognitx.com/ceo-kpis`);
      a.copy = el(m, px({ left: 492, top: 92, width: 120, height: 56, borderRadius: 12, background: BLUE, color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, fontWeight: 700, fontSize: 17, overflow: 'hidden' }));
      a.copyA = el(a.copy, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }), 'Copy link');
      a.copyB = el(a.copy, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, background: '#16A34A' }), `${icon('check', 18, 3, '#fff')}Copied`);
      el(m, px({ left: 30, top: 176, fontWeight: 700, fontSize: 16, color: '#6B7280', letterSpacing: '0.08em' }), 'WHO HAS ACCESS');
      a.acc = [['users', 'Leadership team', 'Can edit'], ['landmark', 'Finance department', 'Can view'], ['globe', 'Rest of the company', 'No access']].map(([ic, who, right], i) =>
        el(m, px({ left: 30, top: 210 + i * 62, width: 580, height: 52, display: 'flex', alignItems: 'center', gap: 14, fontSize: 18, color: '#1F2937' }),
          `<div style="width:40px;height:40px;border-radius:12px;background:#EAF4FB;display:flex;align-items:center;justify-content:center">${icon(ic, 20, 2, BLUE)}</div><div style="flex:1;font-weight:600">${who}</div><div style="padding:6px 14px;border-radius:10px;background:${i === 2 ? '#F3F4F6' : '#EAF4FB'};color:${i === 2 ? '#6B7280' : '#0F5885'};font-weight:700;font-size:15px">${right}</div>`));
      el(m, px({ left: 30, bottom: 22, display: 'flex', alignItems: 'center', gap: 8, fontSize: 15, color: '#6B7280' }), `${icon('shield-check', 18, 2, '#16A34A')}Hosted in Europe · every access is logged`);

      // --- étape 5 : téléphone
      a.phone = U.buildPhone(root);

      // --- légendes
      a.scrim = el(root, { left: '0', top: '0', width: '1100px', height: '1080px', background: 'linear-gradient(90deg, rgba(244,249,253,1) 0%, rgba(244,249,253,0.99) 58%, rgba(244,249,253,0.85) 72%, rgba(244,249,253,0) 100%)' });
      a.caps = CAPS.map(([t0, t1, n, title, desc]) => {
        const g = el(root, { left: '100px', top: '0', width: '600px', height: '1080px' });
        const chip = el(g, { left: '0', top: '330px', height: '46px', padding: '0 20px 0 8px', borderRadius: '23px', background: '#fff', boxShadow: '0 10px 26px rgba(1,45,72,0.10)', display: 'flex', alignItems: 'center', gap: '12px', fontSize: '19px', fontWeight: '700', color: '#012D48', letterSpacing: '0.06em' },
          `<span style="width:32px;height:32px;border-radius:16px;background:${BLUE};color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:17px">${n || '+'}</span>${n ? `STEP ${n} OF 4` : 'BONUS'}`);
        const tt = el(g, { left: '0', top: '400px', width: '600px', fontSize: '76px', lineHeight: '1.04', color: '#012D48' });
        tt.classList.add('nh');
        const line = title.map((l, k) => words(tt, l.split(' ').map((w) => ({ t: w, c: k === 1 ? 'o' : '' }))));
        const d = el(g, { left: '0', top: '0', width: '540px', fontSize: '28px', fontWeight: '500', lineHeight: '1.4', color: '#4A5568' }, desc);
        return { g, chip, tt, line, d, t0, t1 };
      });
      // suivi des étapes en bas à gauche
      a.track = el(root, { left: '100px', top: '960px', display: 'flex', gap: '10px', alignItems: 'center' });
      a.dots = [1, 2, 3, 4].map((n) => {
        const d = h('div', '', a.track);
        Object.assign(d.style, { height: '10px', width: '36px', borderRadius: '5px', background: 'rgba(1,45,72,0.14)', transition: 'none' });
        return d;
      });
    },
    update(T) {
      // ouverture circulaire depuis l'intro
      const ip = P(T, 4.35, 0.55, Ease.inOutCubic);
      const R = lerp(0, 1250, ip);
      a.root.style.clipPath = ip < 1 ? `circle(${R}px at 960px 540px)` : 'none';
      a.ring(960, 540, ip < 1 ? R : 0, 22, T * 120, 1);
      a.bg(T);

      // navigateur
      let [x, y, s] = cam(T);
      const ent = P(T, 4.35, 1.1, Ease.outExpo);
      const rx = lerp(26, 0, ent);
      const dim = P(T, 45.6, 0.8, Ease.inOutCubic);
      S(a.br, { x, y, s, rx, o: 1 - dim * 0.7, blur: dim * 8 });
      S(a.gl, { x: 960 + x * 0.9, y: 560 + y, s: s * 1.1, o: 0.8 * ent * (1 - dim * 0.5) });

      // vue accueil puis construction
      const sw = P(T, 13.0, 0.55, Ease.inOutCubic);
      S(a.home, { o: 1 - sw, y: -sw * 60 });
      a.home.style.display = sw >= 1 ? 'none' : 'block';
      a.build.style.display = sw > 0 ? 'block' : 'none';
      S(a.build, { o: sw, y: (1 - sw) * 60 });

      // étape 1 : modèles puis saisie
      (els.chips || []).forEach((c, i) => {
        const hv = i === 0 ? P(T, 6.75, 0.25) * (1 - P(T, 7.35, 0.3)) : 0;
        c.style.borderColor = hv > 0.01 ? `rgba(46,139,192,${hv})` : '#E9E5F5';
        S(c, { s: 1 + 0.05 * hv, y: -4 * hv });
      });
      const n = Math.round(clamp((T - 7.95) / (11.6 - 7.95)) * PROMPT.length);
      const caretOn = T > 7.8 && T < 12.7 && (T < 11.6 || Math.floor(T * 2.4) % 2 === 0);
      els.ta.innerHTML = PROMPT.slice(0, n) + `<span style="display:inline-block;width:2px;height:32px;background:#7C3AED;vertical-align:-6px;margin-left:1px;opacity:${caretOn ? 1 : 0}"></span>`;
      els.ph.style.opacity = n > 0 ? '0' : '1';
      const cp = P(T, 12.7, 0.08) * (1 - P(T, 12.8, 0.2, Ease.outBack));
      S(els.create, { s: 1 - cp * 0.07 });
      const rp = P(T, 12.72, 0.5);
      S(els.ripple, { s: 1 + rp * 24, o: T > 12.72 ? 1 - rp : 0 });

      // étape 2 : construction en direct
      const bb = P(T, 13.3, 0.6, Ease.outQuint);
      const oldOut = P(T, 30.25, 0.35, Ease.inCubic);
      S(els.bubble, { y: (1 - bb) * 260 - oldOut * 40, s: lerp(1.3, 1, bb), o: bb * (1 - oldOut) });
      els.bubble.style.transformOrigin = '0 0';
      els.steps.forEach((r) => { const p = P(T, r._t0, 0.45, Ease.outQuint); S(r, { x: (1 - p) * -40, y: -oldOut * 40, o: p * (1 - oldOut) }); });
      const ready = P(T, 21.0, 0.35);
      const stIn = P(T, 14.3, 0.4, Ease.outQuint);
      S(els.status, { y: (1 - stIn) * 30 - oldOut * 40, o: stIn * (1 - oldOut) });
      S(els.stA, { o: 1 - ready });
      S(els.stB, { o: ready, s: 0.96 + 0.04 * ready });
      els.eq.forEach((b, i) => S(b, { sy: 0.3 + 0.7 * Math.abs(Math.sin(T * 7 + i * 1.3)) }));
      const prog = P(T, 14.5, 6.4, Ease.inOutSine);
      S(els.prog, { sx: Math.max(0.0001, prog), o: 1 - P(T, 21.0, 0.4) });
      S(els.spin, { r: T * 360 });
      S(els.fast, { o: P(T, 14.6, 0.4) * (1 - P(T, 21.0, 0.4)) });
      S(els.skel, { o: P(T, 14.8, 0.4) * (1 - P(T, 19.4, 0.6, Ease.inOutCubic)) });
      const pop = (e, t0, dy = 24) => { const p = P(T, t0, 0.55, Ease.outQuint); S(e, { y: (1 - p) * dy, o: p }); };
      const sp = P(T, 15.8, 0.6, Ease.outExpo);
      S(els.side, { x: (1 - sp) * -210, o: sp > 0 ? 1 : 0 });
      pop(els.head, 16.2);
      S(els.exportBtn, { s: Math.max(0.0001, spring(T - 17.0, 240, 15)), o: T > 17.0 ? 1 : 0 });
      pop(els.table, 16.6);
      els.rows.forEach((r, i) => { pop(r, 16.75 + i * 0.1, 30); S(r._badge, { s: Math.max(0.0001, spring(T - (17.1 + i * 0.1), 260, 14)) }); });
      els.kpis.forEach((k, i) => {
        pop(k, 18.0 + i * 0.1, 30);
        const cnt = P(T, 18.15 + i * 0.1, 1.1, Ease.outCubic) * k._v;
        k._val.textContent = (k._pre || '') + (k._v % 1 ? cnt.toFixed(1) : Math.round(cnt).toLocaleString('en-US')) + k._suf;
      });
      pop(els.chart, 18.5);
      els.bars.forEach((b, i) => S(b, { sy: Math.max(0.0001, P(T, 18.6 + i * 0.07, 0.6, Ease.outBack)) }));

      // étape 3 : demande de modification dans le chat
      const inOn = P(T, 27.55, 0.25);
      S(a.chatIn, { o: inOn });
      const m = Math.round(clamp((T - 27.8) / (30.0 - 27.8)) * ASK.length);
      const sent = T > 30.25;
      const caret2 = T > 27.6 && !sent && (T < 30.0 || Math.floor(T * 2.4) % 2 === 0);
      a.chatTxt.innerHTML = (sent ? '' : ASK.slice(0, m)) + `<span style="display:inline-block;width:2px;height:22px;background:${BLUE};vertical-align:-4px;margin-left:1px;opacity:${caret2 ? 1 : 0}"></span>`;
      S(a.chatSend, { s: 1 - 0.12 * P(T, 30.15, 0.06) * (1 - P(T, 30.22, 0.15)) });
      const q = P(T, 30.4, 0.55, Ease.outQuint);
      S(a.q2, { y: (1 - q) * 500, o: q });
      a.st2.forEach((r, i) => { const p = P(T, [30.9, 31.4, 32.2][i], 0.45, Ease.outQuint); S(r, { x: (1 - p) * -40, o: p }); });
      const cd = spring(T - 32.4, 200, 15);
      S(a.card, { s: Math.max(0.0001, 0.85 + 0.15 * cd), o: clamp((T - 32.4) * 6) });
      a.hbars.forEach((b, i) => S(b, { sx: Math.max(0.0001, P(T, 32.6 + i * 0.08, 0.6, Ease.outBack)) }));
      const hl = T > 32.4 ? Math.exp(-(T - 32.4) * 1.2) * (0.6 + 0.4 * Math.sin((T - 32.4) * 6)) : 0;
      S(a.hl, { o: hl, s: 1 + 0.02 * hl });

      // étape 4 : publier puis partager
      const pubP = P(T, 37.3, 0.3, Ease.inOutCubic);
      S(els.pubB, { o: pubP, y: (1 - pubP) * 20 });
      S(els.pubA, { o: 1 - pubP, y: -pubP * 20 });
      S(els.publish, { s: 1 - (P(T, 37.2, 0.08) * (1 - P(T, 37.3, 0.2, Ease.outBack))) * 0.08 });
      const ts = spring(T - 37.6, 200, 17);
      S(els.toast, { y: (1 - ts) * -60, s: 0.9 + 0.1 * ts, o: T > 37.6 ? clamp((T - 37.6) * 6) * (1 - P(T, 38.9, 0.3)) : 0 });
      const md = P(T, 39.1, 0.4) * (1 - P(T, 45.3, 0.4));
      S(a.dim, { o: md });
      const mp = spring(T - 39.2, 210, 17);
      S(a.modal, { y: (1 - mp) * 60, s: Math.max(0.0001, 0.92 + 0.08 * mp), o: clamp((T - 39.2) * 6) * (1 - P(T, 45.3, 0.4)) });
      a.acc.forEach((r, i) => { const p = P(T, 39.6 + i * 0.3, 0.45, Ease.outQuint); S(r, { x: (1 - p) * 30, o: p }); });
      const cpy = P(T, 41.8, 0.25);
      S(a.copyB, { o: cpy, y: (1 - cpy) * 20 });
      S(a.copyA, { o: 1 - cpy });
      S(a.copy, { s: 1 - 0.06 * P(T, 41.75, 0.06) * (1 - P(T, 41.82, 0.15)) });

      // curseur et clics
      const vis = VIS.some(([t0, t1]) => T >= t0 && T < t1);
      let cx = 0, cy = 0;
      for (const [t0, t1, x0, y0, x1, y1] of CUR) {
        if (T >= t0 - 0.001) { const p = Ease.inOutCubic(clamp((T - t0) / (t1 - t0))); cx = lerp(x0, x1, p); cy = lerp(y0, y1, p); }
      }
      let press = 0, ck = null;
      for (const t of CLICKS) { if (T >= t - 0.05 && T < t + 0.5) { press = Math.max(press, P(T, t - 0.05, 0.05) * (1 - P(T, t + 0.05, 0.12))); ck = t; } }
      a.cursor.style.display = vis ? 'block' : 'none';
      S(a.cursor, { x: cx - 12, y: cy - 8, s: 1 - press * 0.18 });
      const cr = ck === null ? 1 : P(T, ck, 0.45, Ease.outCubic);
      a.clickFx.style.display = ck !== null && vis ? 'block' : 'none';
      S(a.clickFx, { x: cx, y: cy, s: 0.3 + cr * 1.2, o: 1 - cr });

      // étape 5 : téléphone
      const pe = P(T, 45.9, 1.0, Ease.outExpo);
      S(a.phone, { x: 60, y: (1 - pe) * 900, r: lerp(12, -4, pe) + Math.sin(T * 0.8) * 0.6, o: pe > 0 ? 1 : 0 });
      const tapT = 49.8, tp = P(T, tapT, 0.55, Ease.outCubic);
      S(els.tap, { s: 1 + tp * 22, o: T > tapT ? 1 - tp : 0 });
      const psent = T > tapT + 0.15, valid = T > 50.6;
      els.sendTxt.innerHTML = psent ? `${icon('check', 24, 3, '#fff')}Shared with the board` : `${icon('send', 22, 2.2, '#fff')}Share with the board`;
      const mix = P(T, tapT + 0.05, 0.3, Ease.inOutCubic);
      els.send.style.background = `rgb(${Math.round(lerp(1, 22, mix))},${Math.round(lerp(45, 163, mix))},${Math.round(lerp(72, 74, mix))})`;
      const [lab, col, bgc] = valid ? ['Targets met', '#15803D', '#DCFCE7'] : psent ? ['Sent to the board', '#B45309', '#FEF3C7'] : ['Live update', '#4B5563', '#F3F4F6'];
      els.stChip.innerHTML = `${icon(valid ? 'circle-check' : psent ? 'send' : 'activity', 17, 2.4, col)}${lab}`;
      Object.assign(els.stChip.style, { background: bgc, color: col });
      const nt = spring(T - 48.2, 190, 17);
      S(els.notif, { y: (1 - nt) * -130, o: T > 48.2 ? 1 : 0 });

      // légendes
      S(a.scrim, { o: P(T, 4.9, 0.5) });
      a.caps.forEach((c) => {
        const on = T >= c.t0 - 0.05 && T < c.t1 + 0.5;
        c.g.style.display = on ? 'block' : 'none';
        if (!on) return;
        if (!c.m) { c.m = true; c.d.style.top = 400 + c.tt.offsetHeight + 26 + 'px'; }
        const ci = P(T, c.t0, 0.5, Ease.outQuint), out = P(T, c.t1, 0.4, Ease.inCubic);
        S(c.chip, { x: (1 - ci) * -40, o: ci * (1 - out) });
        c.line.forEach((ln, k) => { revealWords(T, ln, c.t0 + 0.1 + k * 0.16, 0.07, 0.6); hideWords(T, ln, c.t1, 0.03, 0.4); });
        const di = P(T, c.t0 + 0.45, 0.6, Ease.outQuint);
        S(c.d, { y: (1 - di) * 24 - out * 20, o: di * (1 - out) });
      });
      const cur = CAPS.findIndex(([t0, t1]) => T >= t0 && T < t1 + 0.3);
      const stepN = cur >= 0 ? CAPS[cur][2] : 0;
      a.dots.forEach((d, i) => {
        const act = stepN === i + 1, done = stepN === 0 || stepN > i + 1;
        d.style.background = act ? BLUE : done ? 'rgba(46,139,192,0.45)' : 'rgba(1,45,72,0.14)';
        d.style.width = act ? '64px' : '36px';
      });
      S(a.track, { o: P(T, 5.2, 0.5) * (1 - P(T, 52.4, 0.4)) });
    },
  });
})();

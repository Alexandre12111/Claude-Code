// Vidéo YouCraft personnalisée pour le groupe KMC (chambres télécom et boîtes de branchement).
// Montage : chaque scène occupe le créneau calculé à partir de la voix off (window.CT, généré par audio/build_kmc.py) ;
// les apparitions se calent sur les mots prononcés. Écrans d'application construits avec les briques de app_yc.js (U.APPK).
(function () {
  const { h, S, P, Ease, lerp, clamp, icon, spring } = E;
  const el = C.el;
  const A = U.APPK;
  const INK = '#0B0B0F', STONE = '#F5F2EA', PAPER = '#FBFAF6', UM = '#1E3BFF', UMD = '#4F6BFF', LIME = '#D7FF3F';
  const TX = '#111113', TX2 = '#6B6B70', TX3 = '#9A9AA0', BD = '#E7E6E2', OK = '#1C8C4E', WARN = '#D9890F', BAD = '#CF3B3B';
  const CT = window.CT, SC = CT.scenes;
  const lines = (name) => CT.lines.filter((l) => l.scene === name);
  const L = (name, i) => lines(name)[i] || { start: SC[name][0] + 1 + i * 3 };
  const wordAt = (name, w, fb) => { for (const l of lines(name)) { const x = l.words.find((v) => v[0].includes(w)); if (x) return l.start + x[1]; } return fb; };
  const title = (p, txt, top = 120, fs = 64, col = INK) => el(p, { left: '0', width: '1920px', top: top + 'px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: fs + 'px', letterSpacing: '-0.03em', lineHeight: '1.08', color: col }, txt);
  const kicker = (p, txt, top, col = UM) => el(p, { left: '0', width: '1920px', top: top + 'px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '24px', letterSpacing: '0.28em', color: col }, txt);
  const rise = (e, T, t0, d = 0.6, dy = 30) => { const p = P(T, t0, d, Ease.outQuint); S(e, { y: (1 - p) * dy, o: p, blur: (1 - p) * 6 }); return p; };
  const pop = (e, T, t0, k = 220, z = 17, extra = {}) => { const p = spring(T - t0, k, z); S(e, { s: Math.max(0.001, 0.85 + 0.15 * p), o: clamp(p * 1.4), ...extra }); return p; };
  const fmtT = (s) => { s = Math.max(0, Math.floor(s)); return `${Math.floor(s / 3600)} h ${String(Math.floor((s % 3600) / 60)).padStart(2, '0')}`; };
  const fmtC = (s) => { s = Math.max(0, Math.floor(s)); return `${String(Math.floor(s / 3600)).padStart(2, '0')}:${String(Math.floor((s % 3600) / 60)).padStart(2, '0')}:${String(s % 60).padStart(2, '0')}`; };

  function browser(parent, x, y, k, url, W = A.W, H = A.H) {
    const bar = 44, w = W * k;
    const r = el(parent, { left: x + 'px', top: y + 'px', width: w + 'px', height: H * k + bar + 'px', borderRadius: '14px', overflow: 'hidden', background: '#fff', boxShadow: '0 50px 140px rgba(11,11,15,0.45), 0 8px 24px rgba(11,11,15,0.15)' });
    const b = el(r, { left: '0', top: '0', width: w + 'px', height: bar + 'px', background: '#F3F3F1', borderBottom: '1px solid #E7E6E2' });
    ['#FF5F57', '#FEBC2E', '#28C840'].forEach((c, i) => el(b, { left: 20 + i * 21 + 'px', top: '16px', width: '12px', height: '12px', borderRadius: '6px', background: c }));
    el(b, { left: '50%', top: '8px', transform: 'translateX(-50%)', height: '28px', minWidth: '440px', padding: '0 16px', borderRadius: '8px', background: '#fff', border: '1px solid #E7E6E2', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', fontFamily: 'DM Sans', fontSize: '14px', color: '#6B6B70', whiteSpace: 'nowrap' }, `${icon('lock', 13, 2.2, '#9A9AA0')}${url}`);
    const body = el(r, { left: '0', top: bar + 'px', width: w + 'px', height: H * k + 'px', overflow: 'hidden' });
    return { r, body, bar };
  }
  const chip = (p, txt, ic, dark = true) => el(p, { left: '0', top: '0', height: '58px', padding: '0 24px', borderRadius: '29px', background: dark ? 'rgba(11,11,15,0.9)' : PAPER, color: dark ? STONE : INK,
    display: 'flex', alignItems: 'center', gap: '12px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '22px', whiteSpace: 'nowrap', boxShadow: '0 18px 50px rgba(11,11,15,0.35)', border: dark ? '1px solid rgba(245,242,234,0.14)' : '1px solid #E6E1D6' },
    `<span style="width:30px;height:30px;border-radius:15px;background:${LIME};display:flex;align-items:center;justify-content:center">${icon(ic, 17, 2.4, INK)}</span>${txt}`);
  const tag = (p, k, t, sub, dark = true) => {
    const g = el(p, { left: '110px', top: '64px', zIndex: '20' });
    el(g, { left: '0', top: '0', height: '40px', padding: '0 18px', borderRadius: '20px', background: UM, color: '#fff', display: 'flex', alignItems: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '18px', letterSpacing: '0.04em', whiteSpace: 'nowrap' }, k);
    el(g, { left: '0', top: '52px', padding: '10px 24px 12px', borderRadius: '18px', background: 'rgba(11,11,15,0.9)', boxShadow: '0 20px 60px rgba(11,11,15,0.35)', whiteSpace: 'nowrap', color: STONE },
      `<div style="font-family:'Plus Jakarta Sans';font-weight:800;font-size:40px;letter-spacing:-0.03em">${t}</div>` + (sub ? `<div style="font-family:'DM Sans';font-size:20px;color:#B5B0A5;margin-top:2px">${sub}</div>` : ''));
    return g;
  };

  // Chambre télécom en perspective isométrique (caisson ouvert et couvercle), tracée au trait.
  function chamber(parent, x, y, size, col = LIME, col2 = UMD) {
    const q = (X, Y, Z) => [(X - Y) * 0.866, (X + Y) * 0.5 - Z];
    const pts = (arr) => arr.map((p) => q(...p).map((v) => v.toFixed(1)).join(',')).join(' ');
    const W = 100, D = 70, Hh = 62, t = 7, lid = 20;
    const P0 = [[0, 0, 0], [W, 0, 0], [W, D, 0], [0, D, 0]], P1 = P0.map(([a, b]) => [a, b, Hh]);
    const inner = [[t, t, Hh], [W - t, t, Hh], [W - t, D - t, Hh], [t, D - t, Hh]];
    const innerB = inner.map(([a, b]) => [a, b, 12]);
    const L1 = [[0, 0, Hh + lid + 28], [W, 0, Hh + lid + 28], [W, D, Hh + lid + 28], [0, D, Hh + lid + 28]], L0 = L1.map(([a, b, c]) => [a, b, c - 9]);
    const seg = (a, b, c, w = 2.2, cls = '') => `<path class="d ${cls}" pathLength="1" d="M${q(...a).join(' ')} L${q(...b).join(' ')}" stroke="${c}" stroke-width="${w}" fill="none" stroke-linecap="round"/>`;
    let s = '';
    const box = (A0, A1, c, w) => { for (let i = 0; i < 4; i++) { s += seg(A0[i], A0[(i + 1) % 4], c, w); s += seg(A1[i], A1[(i + 1) % 4], c, w); s += seg(A0[i], A1[i], c, w); } };
    box(P0, P1, col, 2.4);
    for (let i = 0; i < 4; i++) { s += seg(inner[i], inner[(i + 1) % 4], col, 1.6); s += seg(inner[i], innerB[i], col, 1.2); }
    s += seg(innerB[0], innerB[1], col, 1.2) + seg(innerB[1], innerB[2], col, 1.2);
    // Fourreaux d'entrée sur la face avant.
    [[30, 34], [70, 34]].forEach(([cx, cz]) => {
      const c = []; for (let k = 0; k <= 24; k++) { const a = (k / 24) * Math.PI * 2; c.push(q(cx + Math.cos(a) * 9, 0, cz + Math.sin(a) * 9)); }
      s += `<path class="d" pathLength="1" d="M${c.map((p) => p.join(' ')).join(' L')}" stroke="${col2}" stroke-width="2" fill="none"/>`;
    });
    box(L0, L1, col2, 2.2);
    s += seg([30, 35, Hh + lid + 28], [45, 35, Hh + lid + 28], col2, 3) + seg([55, 35, Hh + lid + 28], [70, 35, Hh + lid + 28], col2, 3);
    const k = size / 180;
    const g = el(parent, { left: x + 'px', top: y + 'px', width: size + 'px', height: size + 'px' });
    g.innerHTML = `<svg width="${size}" height="${size}" viewBox="-75 -110 180 180" style="overflow:visible">${s}</svg>`;
    const ds = [...g.querySelectorAll('.d')];
    ds.forEach((d) => { d.style.strokeDasharray = '1'; });
    return { g, draw(u, lidLift = 0) { ds.forEach((d, i) => { d.style.strokeDashoffset = String(1 - clamp(u * 1.6 - (i / ds.length) * 0.6)); }); } };
  }

  // ---------- Application KMC : atelier de moulage ----------
  const STEPS = { moul: ['Moulage', 'info'], chauf: ['Chauffe', 'warn'], sech: ['Séchage', 'info'], dem: ['Démoulage possible', 'ok'], lub: ['Lubrification', 'neu'], pret: ['Prêt', 'ok'] };
  const MOLDS = [
    ['M-01', 'Chambre L1T', 'sech', 0.74, 5400, 57], ['M-03', 'Chambre L2T', 'chauf', 0.41, 9100, 61], ['M-04', 'Boîte de branchement 30', 'dem', 1, 0, 34],
    ['M-07', 'Chambre L1T', 'dem', 1, 0, 32], ['M-09', 'Chambre L3T', 'sech', 0.28, 15800, 59], ['M-12', 'Chambre L2T', 'sech', 0.62, 6720, 58],
    ['M-14', 'Boîte de branchement 40', 'moul', 0.55, 1300, 22], ['M-15', 'Chambre L3T', 'chauf', 0.18, 12900, 63], ['M-18', 'Chambre L2T', 'lub', 0.6, 620, 25],
    ['M-20', 'Chambre L1T', 'pret', 1, 0, 21], ['M-22', 'Boîte de branchement 30', 'sech', 0.86, 2200, 55], ['M-24', 'Chambre L2T', 'moul', 0.3, 2600, 22],
  ];
  const KMCNAV = [['layout-dashboard', 'Atelier'], ['layers', 'Moules', '24'], ['timer', 'Cycles en cours', '18'], ['gauge', 'Étuves'], ['circle-alert', 'Alertes', '!2'], ['calendar', 'Planning'], ['chart-column', 'Rapports'], ['users', 'Équipes'], ['settings', 'Paramètres']];
  function kpiK(p, x, y, w, label, val, delta, good, sp, ic) {
    const c = A.card(p, x, y, w, 104);
    el(c, { left: '18px', top: '16px', display: 'flex', alignItems: 'center', gap: '7px', fontSize: '12.5px', fontWeight: '500', color: TX2 }, `${icon(ic, 14, 2, TX3)}${label}`);
    const n = el(c, { left: '18px', top: '42px', fontSize: '27px', fontWeight: '700', color: TX, letterSpacing: '-0.02em', fontVariantNumeric: 'tabular-nums' }, val);
    el(c, { left: '18px', top: '78px', fontSize: '12px', fontWeight: '600', color: good ? OK : BAD }, `${delta} <span style="color:${TX3};font-weight:500">vs hier</span>`);
    el(c, { right: '16px', top: '44px' }, A.spark(sp, 96, 34, good ? OK : BAD));
    return { c, n };
  }
  const TEMPS = (seed, base, amp) => { const r = E.seeded(seed); return Array.from({ length: 40 }, (_, i) => base + amp * Math.sin(i / 6 + seed) + (r() - 0.5) * 2.2); };
  function atelier(parent, opt = {}) {
    const s = A.shell(parent, { app: ['KMC Production', 'layers'], mark: UM, org: 'Groupe KMC', user: [22, 'Karim Benali'], active: 0, nav: KMCNAV, crumbs: ['Usine', 'Atelier de moulage', 'Aujourd’hui'], title: 'Atelier de moulage',
      sub: A.live('En direct') + `<span style="color:${TX3}">·</span>18 moules en cycle<span style="color:${TX3}">·</span>équipe du matin`, actions: A.btn('Aujourd’hui', false, 'calendar') + A.btn('Filtres', false, 'list-checks') + A.btn('Nouveau cycle', true, 'plus') });
    const m = s.main;
    const K = [kpiK(m, 28, 92, 318, 'Moules en cycle', '18 / 24', '+2', true, [12, 14, 13, 15, 16, 15, 17, 18], 'layers'), kpiK(m, 362, 92, 318, 'Pièces démoulées aujourd’hui', '46', '+8', true, [30, 34, 33, 38, 40, 39, 43, 46], 'circle-check'),
      kpiK(m, 696, 92, 318, 'Temps moyen de séchage', '6 h 40', '−12 min', true, [7.4, 7.2, 7.1, 7, 6.9, 6.9, 6.8, 6.7], 'hourglass'), kpiK(m, 1030, 92, 318, 'Occupation des moules', '82 %', '+4 pts', true, [70, 72, 74, 75, 77, 79, 80, 82], 'gauge')];
    const gc = A.card(m, 28, 216, 880, 712, 'Moules en cycle', `<span style="display:flex;gap:6px">${['Tous 18', 'À démouler 2', 'En séchage 5'].map((t, i) => `<span style="height:26px;padding:0 10px;border-radius:6px;display:flex;align-items:center;font-size:12px;font-weight:600;${i === 0 ? `background:${TX};color:#fff` : `background:#F1F1EF;color:${TX2}`}">${t}</span>`).join('')}</span>`);
    const tiles = MOLDS.map(([id, prod, st, pr, rem, temp], i) => {
      const x = 18 + (i % 3) * 284, y = 52 + Math.floor(i / 3) * 162;
      const c = el(gc, { left: x + 'px', top: y + 'px', width: '270px', height: '150px', borderRadius: '10px', border: `1px solid ${BD}`, background: '#fff' });
      el(c, { left: '14px', top: '12px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '15px', fontWeight: '700' }, `<span style="width:28px;height:28px;border-radius:7px;background:#EEF0FF;display:flex;align-items:center;justify-content:center">${icon('layers', 14, 2, UM)}</span>${id}`);
      el(c, { left: '14px', top: '46px', fontSize: '12.5px', color: TX2 }, prod);
      const bd = el(c, { right: '12px', top: '14px' }, A.badge(STEPS[st][0], STEPS[st][1]));
      const pb = el(c, { left: '14px', top: '78px' }, A.bar(pr, st === 'dem' || st === 'pret' ? OK : st === 'chauf' ? WARN : UM, 242, 7));
      const tm = el(c, { left: '14px', top: '100px', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '12px', color: TX2 }, `${icon('clock', 13, 2, TX3)}<span class="tt" style="font-weight:700;color:${TX};font-variant-numeric:tabular-nums"></span>`);
      el(c, { right: '14px', top: '100px', display: 'flex', alignItems: 'center', gap: '5px', fontSize: '12px', fontWeight: '600', color: temp > 60 ? WARN : TX2 }, `${icon('gauge', 13, 2, temp > 60 ? WARN : TX3)}${temp} °C`);
      const tt = tm.querySelector('.tt');
      const txt = (r) => (st === 'dem' ? 'Séchage terminé' : st === 'pret' ? 'Prêt pour un cycle' : st === 'lub' ? 'Reste ' + Math.ceil(r / 60) + ' min' : 'Reste ' + fmtC(r));
      if (st === 'dem') { c.style.borderColor = '#9BD3B1'; c.style.boxShadow = '0 0 0 3px rgba(28,140,78,0.10)'; }
      return { c, tt, rem, txt, st, bd, id };
    });
    const tc = A.card(m, 924, 216, 424, 330, 'Températures des étuves', `<span style="display:flex;gap:10px;font-size:11.5px">${[['Étuve 1', UM], ['Étuve 2', WARN], ['Étuve 3', '#7C6CF0']].map(([n, c]) => `<span style="display:flex;align-items:center;gap:5px"><span style="width:8px;height:8px;border-radius:2px;background:${c}"></span>${n}</span>`).join('')}</span>`);
    const ch = A.lineChart(tc, 8, 50, 408, 270, { series: [{ v: TEMPS(2, 56, 3), c: UM }, { v: TEMPS(5, 60, 3.5), c: WARN }, { v: TEMPS(9, 52, 2.5), c: '#7C6CF0' }], xl: ['06:00', '', '', '', '', '', '', '', '08:00', '', '', '', '', '', '', '', '10:00', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Maint.'], ymin: 15, ymax: 75, yt: 4, fmt: (v) => Math.round(v) + '°', thr: 65, area: false });
    const fc = A.card(m, 924, 562, 424, 366, 'Activité de l’atelier', A.live('En direct'));
    const FEED = [[22, 'Karim B.', 'a démoulé <b>M-20</b> · Chambre L1T', 'il y a 2 min', 'circle-check', OK], [1, 'Mateo G.', 'a lancé le séchage de <b>M-22</b>', 'il y a 9 min', 'hourglass', UM], [14, 'Léa B.', 'a validé la lubrification de <b>M-16</b>', 'il y a 14 min', 'check', OK],
      [17, 'Kwame M.', 'a coulé <b>M-14</b> · Boîte de branchement 40', 'il y a 21 min', 'layers', UM], [0, 'Alerte', '<b>Étuve 2</b> proche du seuil : 63 °C', 'il y a 26 min', 'circle-alert', WARN]];
    const feed = FEED.map(([k, n, t, w, ic, c], j) => el(fc, { left: '18px', top: 52 + j * 62 + 'px', width: '388px', height: '54px', display: 'flex', alignItems: 'center', gap: '12px', fontSize: '12.5px', borderBottom: j < 4 ? '1px solid #F0EFEC' : 'none' },
      `<span style="width:32px;height:32px;border-radius:16px;background:${c}1A;display:flex;align-items:center;justify-content:center;flex:none">${icon(ic, 15, 2.2, c)}</span><span style="display:flex;flex-direction:column;line-height:1.35"><span><b style="font-weight:600">${n}</b> ${t}</span><span style="color:${TX3};font-size:11.5px">${w}</span></span>`));
    // Toast d'alerte (scène « modif »).
    const toast = el(s.root, { right: '28px', top: '72px', width: '420px', height: '86px', borderRadius: '12px', background: '#fff', border: `1px solid #F2C5C5`, boxShadow: '0 20px 50px rgba(207,59,59,0.20)', opacity: '0', zIndex: '9' },
      `<div style="position:absolute;left:16px;top:18px;width:40px;height:40px;border-radius:10px;background:#FCEBEB;display:flex;align-items:center;justify-content:center">${icon('bell-ring', 20, 2.2, BAD)}</div><div style="position:absolute;left:70px;top:16px;font-size:14px;font-weight:700;color:${BAD}">Alerte · Étuve 2 à 66 °C</div><div style="position:absolute;left:70px;top:40px;font-size:12.5px;color:${TX2};width:330px">Seuil de 65 °C dépassé · chef d’équipe prévenu</div>`);
    return {
      root: s.root, tiles, toast,
      update(T, t0) {
        K.forEach((k, i) => { const p = P(T, t0 + i * 0.06, 0.5); S(k.c, { o: p, y: (1 - p) * 10 }); });
        tiles.forEach((t, i) => {
          const p = P(T, t0 + 0.15 + i * 0.04, 0.45); S(t.c, { o: p, y: (1 - p) * 12 });
          t.tt.textContent = t.txt(t.rem - Math.max(0, T - t0) * 1);
          if (t.st === 'dem') t.c.style.boxShadow = `0 0 0 ${3 + 2 * (0.5 + 0.5 * Math.sin(T * 4))}px rgba(28,140,78,${0.08 + 0.08 * (0.5 + 0.5 * Math.sin(T * 4))})`;
        });
        ch.draw(0.35 + 0.65 * clamp((T - t0) / 6));
        feed.forEach((f, j) => { const p = P(T, t0 + 0.4 + j * 0.08, 0.4); S(f, { o: p, x: (1 - p) * 12 }); });
        s.subEl.querySelector('.lv').style.opacity = String(0.55 + 0.45 * Math.sin(T * 6));
      },
    };
  }

  // ---------- Application KMC : fiche d'un moule ----------
  function fiche(parent) {
    const s = A.shell(parent, { app: ['KMC Production', 'layers'], mark: UM, org: 'Groupe KMC', user: [22, 'Karim Benali'], active: 1, nav: KMCNAV, crumbs: ['Usine', 'Moules', 'M-12'], title: 'Moule M-12 · Chambre L2T',
      sub: A.badge('Séchage en cours', 'info') + `<span style="color:${TX3}">·</span>Cycle n° 1 284<span style="color:${TX3}">·</span>démarré à 08:10 par Mateo G.`, actions: A.btn('Historique', false, 'history') + A.btn('Imprimer la fiche', false, 'download') + A.btn('Valider l’étape', true, 'check') });
    const m = s.main;
    const sc = A.card(m, 28, 92, 1320, 150, 'Cycle en cours');
    const ST5 = [['Moulage', '08:10', 'layers', 1], ['Chauffe', '08:45', 'gauge', 1], ['Séchage', 'en cours', 'hourglass', 0.62], ['Démoulage', 'prévu 12:20', 'upload', 0], ['Lubrification', '—', 'sparkles', 0]];
    const segs = [];
    ST5.forEach(([n, t, ic, v], i) => {
      const x = 40 + i * 256;
      const done = v === 1, cur = v > 0 && v < 1;
      el(sc, { left: x + 'px', top: '52px', width: '40px', height: '40px', borderRadius: '20px', background: done ? OK : cur ? UM : '#F1F1EF', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: cur ? '0 0 0 6px rgba(30,59,255,0.12)' : 'none' }, icon(done ? 'check' : ic, 18, 2.4, done || cur ? '#fff' : TX3));
      el(sc, { left: x + 52 + 'px', top: '52px', fontSize: '14px', fontWeight: '700', color: v > 0 ? TX : TX2 }, n);
      el(sc, { left: x + 52 + 'px', top: '74px', fontSize: '12.5px', color: cur ? UM : TX2, fontWeight: cur ? '600' : '400' }, cur ? 'Reste 1 h 52 · 62 %' : t);
      if (i < 4) segs.push([el(sc, { left: x + 52 + 'px', top: '110px', width: '190px', height: '5px', borderRadius: '3px', background: '#EEEEEB', overflow: 'hidden' }, `<span class="bf" style="display:block;height:100%;width:0%;background:${i < 2 ? OK : UM};border-radius:3px"></span>`), i < 1 ? 1 : i === 1 ? 1 : i === 2 ? 0.62 : 0]);
    });
    const tc = A.card(m, 28, 258, 860, 380, 'Température du cycle', `<span style="font-size:12px">Sonde moule · relevé toutes les 5 min</span>`);
    const tv = Array.from({ length: 48 }, (_, i) => (i < 6 ? 20 + i * 2 : i < 14 ? 32 + (i - 6) * 3.4 : 58 + Math.sin(i / 3) * 1.6 + (i > 40 ? -(i - 40) * 0.6 : 0)));
    const ch = A.lineChart(tc, 8, 50, 844, 320, { series: [{ v: tv, c: UM }], xl: ['08:10', '', '', '', '', '', '', '', '', '', '', '', '09:10', '', '', '', '', '', '', '', '', '', '', '', '10:10', '', '', '', '', '', '', '', '', '', '', '', '11:10', '', '', '', '', '', '', '', '', '', '', '12:10'], ymin: 10, ymax: 75, yt: 5, fmt: (v) => Math.round(v) + ' °C', thr: 65 });
    const lc = A.card(m, 904, 258, 444, 380, 'Contrôle de lubrification', `<span style="font-size:12px">Avant le cycle suivant</span>`);
    const CK = ['Nettoyage complet du moule', 'Contrôle visuel des parois', 'Application de l’huile de démoulage', 'Vérification des inserts et fourreaux', 'Validation du chef d’équipe'];
    const checks = CK.map((t, j) => {
      const r = el(lc, { left: '18px', top: 56 + j * 60 + 'px', width: '408px', height: '50px', borderRadius: '8px', border: `1px solid ${BD}`, display: 'flex', alignItems: 'center', gap: '12px', padding: '0 14px', fontSize: '13.5px' },
        `<span class="cb" style="width:22px;height:22px;border-radius:6px;border:2px solid ${BD};display:flex;align-items:center;justify-content:center;flex:none"></span><span class="lt">${t}</span>`);
      return r;
    });
    const hc = A.card(m, 28, 654, 1320, 274, 'Historique des cycles', A.btn('Exporter', false, 'download'));
    const HR = [['n° 1 283', 'hier · 13:05', '6 h 35', '61 °C', A.who(1, 'Mateo García', 'Équipe du matin'), A.badge('Conforme', 'ok')], ['n° 1 282', 'hier · 05:40', '6 h 50', '62 °C', A.who(14, 'Léa Bernard', 'Équipe de nuit'), A.badge('Conforme', 'ok')],
      ['n° 1 281', '3 nov. · 21:15', '7 h 05', '66 °C', A.who(17, 'Kwame Mensah', 'Équipe du soir'), A.badge('Seuil dépassé', 'warn')], ['n° 1 280', '3 nov. · 13:30', '6 h 40', '60 °C', A.who(22, 'Karim Benali', 'Équipe du matin'), A.badge('Conforme', 'ok')]];
    const TB = A.table(hc, 0, 50, 1320, [['CYCLE', 150], ['DÉBUT', 190], ['SÉCHAGE', 170], ['TEMP. MAX', 170], ['OPÉRATEUR', 400], ['CONTRÔLE', 200]], HR, 52);
    return {
      root: s.root,
      update(T, t0, tl) {
        segs.forEach(([g, v], i) => { g.querySelector('.bf').style.width = (100 * v * P(T, t0 + 0.2 + i * 0.15, 0.6, Ease.outCubic)).toFixed(1) + '%'; });
        ch.draw(clamp((T - t0 - 0.2) / 3));
        checks.forEach((r, j) => {
          const on = T > (tl || t0 + 2) + j * 0.35 && j < 4;
          const cb = r.querySelector('.cb');
          cb.style.background = on ? OK : '#fff'; cb.style.borderColor = on ? OK : BD; cb.innerHTML = on ? icon('check', 14, 3, '#fff') : '';
          r.querySelector('.lt').style.color = on ? TX2 : TX;
        });
        TB.R.forEach((r, j) => { const p = P(T, t0 + 0.3 + j * 0.06, 0.4); S(r, { o: p }); });
      },
    };
  }

  // Caméra sur une application : plan large, puis points [instant, x, y, zoom] en coordonnées de l'application.
  function appScene(name, make, url, kTag, ttl, sub, keys, callouts, K = 0.72) {
    return {
      build(root) {
        const a = (this.a = {});
        a.bg = C.navyBg(root, name.length);
        a.cam = el(root, { inset: '0', transformOrigin: '0 0' });
        a.B = browser(a.cam, 960 - (A.W * K) / 2, 250, K, url);
        a.app = make(a.B.body); a.app.root.style.transform = `scale(${K})`;
        a.ox = 960 - (A.W * K) / 2; a.oy = 250 + 44;
        a.tag = tag(root, kTag, ttl, sub);
        a.co = callouts.map(([t, ic]) => chip(root, t, ic, true));
      },
      update(T, s, e) {
        const a = this.a; a.bg(T);
        rise(a.tag, T, s + 0.1, 0.7, 20);
        const wp = P(T, s + 0.15, 0.9, Ease.outCubic);
        // Points de caméra : [mot ou instant, x, y, zoom] ; on passe de l'un à l'autre en 1,3 s.
        const KS = [[s, 800, 500, 0.94 + 0.06 * wp]].concat(keys.map(([w, x, y, z, fb]) => [typeof w === 'number' ? s + w : wordAt(name, w, s + fb) - 0.5, x, y, z]));
        let cx = KS[0][1], cy = KS[0][2], Z = KS[0][3];
        for (let i = 1; i < KS.length; i++) { const u = Ease.inOutSine(clamp((T - KS[i][0]) / 1.3)); cx = lerp(cx, KS[i][1], u); cy = lerp(cy, KS[i][2], u); Z = lerp(Z, KS[i][3], u); }
        // Le point (cx, cy) de l'application est ramené au centre de l'image.
        const fx = a.ox + cx * K, fy = a.oy + cy * K;
        const wide = KS.length === 1 || T < KS[1][0];
        const tx = 960 - fx * Z, ty = 620 - fy * Z + (1 - wp) * 80;
        const tx0 = 960 * (1 - Z), ty0 = 650 * (1 - Z) + (1 - wp) * 80;
        const u0 = KS.length > 1 ? Ease.inOutSine(clamp((T - KS[1][0]) / 1.3)) : 0;
        a.cam.style.transform = `translate(${lerp(tx0, tx, u0).toFixed(1)}px,${lerp(ty0, ty, u0).toFixed(1)}px) scale(${Z.toFixed(4)})`;
        a.cam.style.opacity = String(wp);
        a.app.update(T, s + 0.6, this.tl ? this.tl() : undefined);
        a.co.forEach((c, i) => {
          const [, , w, x, y, fb] = callouts[i];
          const t = wordAt(name, w, s + (fb || 2 + i * 1.5));
          const p = spring(T - t, 220, 17);
          S(c, { x, y: y + (1 - clamp(p)) * 20, s: Math.max(0.001, 0.8 + 0.2 * p), o: clamp(p * 1.4) });
        });
      },
    };
  }

  // Fenêtre de l'atelier YouCraft : discussion à gauche, aperçu de l'application à droite.
  function builder(root, x, y, w, hh) {
    const r = el(root, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px', borderRadius: '20px', overflow: 'hidden', background: '#FBFAF6', boxShadow: '0 50px 140px rgba(11,11,15,0.30), 0 8px 24px rgba(11,11,15,0.10)', border: '1px solid #E6E1D6', fontFamily: 'DM Sans' });
    const top = el(r, { left: '0', top: '0', width: w + 'px', height: '58px', background: '#fff', borderBottom: '1px solid #ECE8DF' });
    const lg = el(top, { left: '22px', top: '14px', height: '30px' }); C.ycLogo(lg, 26, false);
    el(top, { left: '190px', top: '19px', fontSize: '15px', color: TX2 }, `<span style="color:${TX3}">/</span>&nbsp; <b style="color:${TX}">KMC Production</b> &nbsp;${A.badge('Brouillon', 'neu', false)}`);
    el(top, { right: '22px', top: '12px', display: 'flex', gap: '10px' }, A.btn('Aperçu', false, 'eye') + A.btn('Publier', true, 'send'));
    const chat = el(r, { left: '0', top: '58px', width: '560px', height: hh - 58 + 'px', borderRight: '1px solid #ECE8DF', background: '#FBFAF6' });
    const pv = el(r, { left: '560px', top: '58px', width: w - 560 + 'px', height: hh - 58 + 'px', background: '#EFECE4' });
    const input = el(chat, { left: '24px', bottom: '24px', top: 'auto', width: '512px', height: '64px', borderRadius: '16px', background: '#fff', border: '1px solid #E6E1D6', boxShadow: '0 6px 20px rgba(11,11,15,0.06)', display: 'flex', alignItems: 'center', padding: '0 18px', fontSize: '16px', color: TX3 },
      `Décrivez une modification…<span style="margin-left:auto;width:36px;height:36px;border-radius:10px;background:${UM};display:flex;align-items:center;justify-content:center">${icon('send-horizontal', 17, 2.2, '#fff')}</span>`);
    return { r, chat, pv, input };
  }
  const userMsg = (p, top, w = 470) => {
    const b = el(p, { right: '24px', top: top + 'px', width: w + 'px', padding: '16px 20px', borderRadius: '18px 18px 4px 18px', background: INK, color: STONE, fontSize: '19px', lineHeight: '1.45' });
    const t = h('span', '', b); return { b, t };
  };
  const botMsg = (p, top, html) => el(p, { left: '24px', top: top + 'px', width: '500px', display: 'flex', gap: '12px', fontSize: '17px', lineHeight: '1.45', color: TX },
    `<span style="width:34px;height:34px;border-radius:10px;background:${LIME};display:flex;align-items:center;justify-content:center;flex:none">${icon('sparkles', 17, 2.2, INK)}</span><span>${html}</span>`);

  // ---------- Scènes ----------
  const OWN = {};

  OWN.intro = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 3);
      a.lg = el(root, { left: '110px', top: '80px', height: '60px' }); a.L = C.ycLogo(a.lg, 54, true);
      a.k = kicker(root, 'PRÉPARÉ POUR LE GROUPE KMC', 300, LIME);
      a.t = title(root, 'Bonjour à toute l’équipe <span style="color:#4F6BFF">KMC</span>.', 360, 96, STONE);
      a.s = el(root, { left: '0', width: '1920px', top: '500px', textAlign: 'center', fontFamily: 'DM Sans', fontSize: '36px', color: '#B5B0A5' }, 'Ce que YouCraft peut apporter à votre production.');
      a.ch = chamber(root, 790, 580, 340);
    },
    update(T, s) {
      const a = this.a; a.bg(T);
      C.ycLogoAnim(a.L, T, s + 0.1); a.L.glyphs.forEach((c, i) => { const p = P(T, s + 0.4 + i * 0.03, 0.6, Ease.outQuint); S(c, { y: (1 - p) * 30, o: p }); });
      rise(a.k, T, s + 0.3, 0.6, 16); rise(a.t, T, s + 0.5, 0.8, 40);
      rise(a.s, T, L('intro', 1).start, 0.7, 20);
      a.ch.draw(P(T, s + 0.8, 3.5, Ease.inOutSine));
      S(a.ch.g, { y: Math.sin(T * 0.8) * 6, o: P(T, s + 0.8, 0.5) });
    },
  };

  OWN.procede = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 2);
      a.k = kicker(root, 'VOTRE PROCÉDÉ', 92);
      a.t = title(root, 'Un cycle, quatre étapes, chaque moule.', 140, 60);
      const cx = 960, cy = 575, R = 260;
      a.ring = el(root, { left: cx - R + 'px', top: cy - R + 'px', width: R * 2 + 'px', height: R * 2 + 'px' });
      a.ring.innerHTML = `<svg width="${R * 2}" height="${R * 2}" style="overflow:visible"><circle cx="${R}" cy="${R}" r="${R}" fill="none" stroke="#DCD7CB" stroke-width="3" stroke-dasharray="2 12" stroke-linecap="round"/><circle class="arc" cx="${R}" cy="${R}" r="${R}" fill="none" stroke="${UM}" stroke-width="5" stroke-linecap="round" pathLength="1" stroke-dasharray="1" stroke-dashoffset="1" transform="rotate(-90 ${R} ${R})"/></svg>`;
      a.dot = el(root, { left: '0', top: '0', width: '26px', height: '26px', borderRadius: '13px', background: LIME, border: `5px solid ${INK}`, boxShadow: '0 6px 20px rgba(11,11,15,0.3)' });
      const ST = [['layers', 'Moulage', 'Coulage dans le moule'], ['hourglass', 'Chauffe et séchage', 'Temps et températures'], ['upload', 'Démoulage', 'Dès que la pièce est prête'], ['sparkles', 'Lubrification', 'Moule prêt pour le cycle suivant']];
      const POS = [[cx, cy - R], [cx + R, cy], [cx, cy + R], [cx - R, cy]];
      a.nodes = ST.map(([ic, t, d], i) => {
        const [x, y] = POS[i];
        const lx = i === 1 ? x - 40 : i === 3 ? x - 340 : x - 190;
        const c = el(root, { left: lx + 'px', top: y - 56 + 'px', width: '380px', height: '112px', borderRadius: '24px', background: '#fff', border: '1px solid #E6E1D6', boxShadow: '0 24px 60px rgba(11,11,15,0.12)', fontFamily: 'DM Sans', color: INK });
        c._ic = el(c, { left: '20px', top: '20px', width: '72px', height: '72px', borderRadius: '20px', background: '#F1EEE7', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 34, 2.2, UM));
        el(c, { left: '112px', top: '24px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '28px', letterSpacing: '-0.02em', whiteSpace: 'nowrap' }, t);
        el(c, { left: '112px', top: '64px', fontSize: '19px', color: '#6F6C66', whiteSpace: 'nowrap' }, d);
        el(c, { right: '18px', top: '16px', width: '30px', height: '30px', borderRadius: '15px', background: INK, color: LIME, fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '15px', display: 'flex', alignItems: 'center', justifyContent: 'center' }, String(i + 1));
        return c;
      });
      a.q = ['Où en est le moule M-12 ?', 'Quand peut-on démouler ?', 'Quelle température dans l’étuve ?'].map((t, i) => {
        const c = el(root, { left: '0', top: '0', width: '1920px', display: 'flex', justifyContent: 'center' });
        el(c, { position: 'relative', left: 'auto', top: 'auto', height: '64px', padding: '0 26px', borderRadius: '32px', background: INK, color: STONE, display: 'flex', alignItems: 'center', gap: '12px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '24px', whiteSpace: 'nowrap', boxShadow: '0 20px 50px rgba(11,11,15,0.3)' }, `${icon('search', 20, 2.4, LIME)}${t}`);
        c.style.top = 520 + i * 0 + 'px'; return c;
      });
      a.cx = cx; a.cy = cy; a.R = R;
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      rise(a.k, T, s + 0.1, 0.6, 16); rise(a.t, T, s + 0.25, 0.7, 30);
      const l1 = L('procede', 1).start, l2 = L('procede', 2).start;
      const words = ['moulage', 'chauffe', 'demoulage', 'lubrification'];
      const tw = words.map((w, i) => wordAt('procede', w, l1 + 0.3 + i * 1.4));
      a.nodes.forEach((c, i) => {
        const p = spring(T - (i === 0 ? Math.min(tw[0], L('procede', 0).start + 0.8) : tw[i]) + 0.2, 200, 17);
        S(c, { s: Math.max(0.001, 0.8 + 0.2 * p), o: clamp(p * 1.4) });
        const act = T >= tw[i] - 0.2 && (i === 3 ? T < l2 : T < tw[i + 1] - 0.2);
        c.style.borderColor = act ? UM : '#E6E1D6';
        c._ic.style.background = act ? UM : '#F1EEE7'; c._ic.firstChild && (c._ic.firstChild.style.stroke = act ? '#fff' : UM);
      });
      // Arc de progression, puis point qui tourne en boucle (le cycle recommence).
      const prog = clamp((T - tw[0] + 0.2) / (tw[3] + 1 - tw[0]));
      a.ring.querySelector('.arc').style.strokeDashoffset = String(1 - prog);
      const ang = -Math.PI / 2 + (T < tw[3] + 1 ? prog : 1 + (T - tw[3] - 1) * 0.22) * Math.PI * 2;
      S(a.dot, { x: a.cx + Math.cos(ang) * a.R - 13, y: a.cy + Math.sin(ang) * a.R - 13, o: P(T, tw[0] - 0.3, 0.3) });
      // Questions au centre de la boucle.
      a.q.forEach((c, i) => {
        const t0 = l2 + 0.2 + i * 1.5, t1 = i < 2 ? t0 + 1.5 : e;
        const p = P(T, t0, 0.35, Ease.outCubic) * (1 - P(T, t1 - 0.25, 0.25));
        S(c, { y: (1 - p) * 14, o: p, s: 0.95 + 0.05 * p });
      });
    },
  };

  OWN.logo = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 5);
      a.line = el(root, { left: '0', width: '1920px', top: '360px', display: 'flex', justifyContent: 'center' });
      a.L = C.ycLogo(a.line, 170, true);
      a.t = el(root, { left: '0', width: '1920px', top: '610px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '700', fontSize: '52px', letterSpacing: '-0.02em', color: STONE }, 'Votre suivi de production, prêt en quelques <span style="color:#D7FF3F">heures</span>.');
    },
    update(T, s) {
      const a = this.a; a.bg(T);
      C.ycLogoAnim(a.L, T, s + 0.05);
      a.L.glyphs.forEach((c, i) => { const p = P(T, s + 0.35 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 50, o: p }); });
      rise(a.t, T, s + 0.9, 0.8);
    },
  };

  const PROMPT = 'Je veux suivre chaque moule, du coulage au démoulage, avec les temps de séchage et les températures de chauffe. Les opérateurs doivent être prévenus dès qu’une pièce peut être démoulée.';
  OWN.decrire = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 6);
      a.B = builder(root, 110, 150, 1700, 840);
      a.u = userMsg(a.B.chat, 30);
      a.bot = botMsg(a.B.chat, 330, 'Je construis votre application <b>KMC Production</b> :');
      a.items = [['panels-top-left', 'Écrans', 'Atelier, fiches moules, vue mobile', 'ecrans'], ['table-2', 'Données', 'Moules, cycles, relevés de température', 'donnees'], ['bell-ring', 'Alertes', 'Fin de séchage, seuils de température', 'alertes'], ['shield-check', 'Droits d’accès', 'Responsables, chefs d’équipe, opérateurs', 'droits']].map(([ic, t, d, w], i) => {
        const r = el(a.B.chat, { left: '70px', top: 380 + i * 74 + 'px', width: '466px', height: '62px', borderRadius: '12px', background: '#fff', border: '1px solid #E6E1D6', display: 'flex', alignItems: 'center', gap: '14px', padding: '0 16px', fontSize: '15px', color: TX2 },
          `<span class="st" style="width:30px;height:30px;border-radius:15px;display:flex;align-items:center;justify-content:center;flex:none"></span><span style="display:flex;flex-direction:column;line-height:1.3"><b style="color:${TX};font-size:16px">${t}</b>${d}</span><span style="margin-left:auto">${icon(ic, 20, 2, TX3)}</span>`);
        r._w = w; return r;
      });
      // Aperçu : maquette grise qui se remplit, puis l'application réelle.
      const pvW = 1700 - 560, pvH = 840 - 58, K = 0.66;
      a.frame = el(a.B.pv, { left: (pvW - A.W * K) / 2 + 'px', top: (pvH - A.H * K) / 2 + 'px', width: A.W * K + 'px', height: A.H * K + 'px', borderRadius: '10px', overflow: 'hidden', background: '#F7F7F5', boxShadow: '0 20px 60px rgba(11,11,15,0.14)' });
      const sk = (x, y, w, hh) => el(a.frame, { left: x * K + 'px', top: y * K + 'px', width: w * K + 'px', height: hh * K + 'px', borderRadius: '8px', background: 'linear-gradient(90deg,#ECEBE7,#F4F3F0,#ECEBE7)', backgroundSize: '200% 100%' });
      a.sk = [sk(0, 0, 224, 1000), sk(224, 0, 1376, 56), sk(252, 148, 318, 104), sk(586, 148, 318, 104), sk(920, 148, 318, 104), sk(1254, 148, 318, 104), sk(252, 272, 880, 712), sk(1148, 272, 424, 330), sk(1148, 618, 424, 366)];
      a.appBox = el(a.frame, { inset: '0', opacity: '0' });
      a.app = atelier(a.appBox); a.app.root.style.transform = `scale(${K})`;
      a.lbl = el(a.B.pv, { left: '24px', top: '18px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '14px', fontWeight: '600', color: TX2, fontFamily: 'DM Sans' }, `${icon('loader-circle', 16, 2.2, UM)}<span class="lt">Construction en cours…</span>`);
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      const wp = P(T, s + 0.1, 0.8, Ease.outCubic); S(a.B.r, { y: (1 - wp) * 60, o: wp });
      const t1 = L('decrire', 1).start - 0.6, t2 = L('decrire', 2).start;
      const typed = clamp((T - (s + 1.0)) / Math.max(2, t2 - 0.8 - (s + 1.0)));
      const n = Math.floor(typed * PROMPT.length);
      a.u.t.innerHTML = PROMPT.slice(0, n) + (T > s + 0.8 && typed < 1 ? '<span class="caret"></span>' : '');
      S(a.u.b, { o: P(T, s + 0.7, 0.3) });
      rise(a.bot, T, t2, 0.4, 10);
      a.items.forEach((r, i) => {
        const t = wordAt('decrire', r._w, t2 + 1 + i * 0.8);
        rise(r, T, t2 + 0.4 + i * 0.25, 0.4, 10);
        const done = T > t + 0.3;
        const st = r.querySelector('.st');
        st.style.background = done ? OK : '#EEF0FF';
        st.innerHTML = done ? icon('check', 15, 3, '#fff') : icon('loader-circle', 15, 2.4, UM);
        if (!done) st.firstChild.style.transform = `rotate(${T * 300}deg)`;
      });
      a.sk.forEach((b, i) => { const p = P(T, t1 + 0.6 + i * 0.35, 0.4); S(b, { o: p, s: 0.97 + 0.03 * p }); b.style.backgroundPosition = `${(T * 80) % 200}% 0`; });
      const tApp = Math.max(t2 + 3.2, e - 3.2);
      const ap = P(T, tApp, 0.8, Ease.inOutSine);
      a.appBox.style.opacity = String(ap);
      a.app.update(T, tApp);
      a.lbl.querySelector('.lt').textContent = ap > 0.5 ? 'Application prête · KMC Production' : 'Construction en cours…';
      a.lbl.firstChild.style.transform = `rotate(${ap > 0.5 ? 0 : T * 300}deg)`;
    },
  };

  OWN.atelier = appScene('atelier', (b) => atelier(b), 'production.kmc.youcraft.fr/atelier', 'VOTRE APPLICATION', 'Atelier de moulage', 'Chaque moule, son étape, son temps restant',
    [['moule', 560, 560, 1.32, 1.5], ['temperatures', 1250, 400, 1.5, 6]],
    [['Étape de chaque moule', 'layers', 'etape', 1230, 290, 3], ['Temps restant avant démoulage', 'clock', 'restant', 1160, 390, 4], ['Températures en direct', 'gauge', 'direct', 220, 300, 8], ['Cycles enregistrés', 'history', 'enregistre', 240, 400, 10]]);

  OWN.fiche = appScene('fiche', (b) => fiche(b), 'production.kmc.youcraft.fr/moules/M-12', 'FICHE MOULE', 'Moule M-12 · Chambre L2T', 'Historique, température et lubrification',
    [['historique', 660, 820, 1.25, 2], ['temperature', 480, 470, 1.35, 4], ['lubrification', 1150, 450, 1.45, 6]],
    [['Historique des cycles', 'history', 'historique', 1200, 300, 2], ['Courbe de température', 'activity', 'temperature', 1200, 400, 4], ['Contrôle de lubrification', 'list-checks', 'lubrification', 200, 300, 6]]);
  OWN.fiche.tl = () => wordAt('fiche', 'lubrification', SC.fiche[0] + 6) + 0.6;

  // Téléphone de l'opérateur et tableau de bord partagé.
  OWN.mobile = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 8);
      a.tag = tag(root, 'DANS L’ATELIER', 'Prévenu au bon moment', 'Sur le téléphone des opérateurs');
      const ph = (a.ph = el(root, { left: '360px', top: '150px', width: '420px', height: '860px', borderRadius: '64px', background: '#0B0B0F', boxShadow: '0 60px 140px rgba(0,0,0,0.5), inset 0 0 0 2px #2A2A31' }));
      const sc = (a.sc = el(ph, { left: '16px', top: '16px', width: '388px', height: '828px', borderRadius: '50px', overflow: 'hidden', background: '#F7F7F5', fontFamily: 'DM Sans', color: TX }));
      el(sc, { left: '144px', top: '12px', width: '100px', height: '30px', borderRadius: '15px', background: '#0B0B0F', zIndex: '5' });
      el(sc, { left: '30px', top: '16px', fontSize: '15px', fontWeight: '700' }, '09:41');
      // Écran de l'application.
      el(sc, { left: '0', top: '56px', width: '388px', height: '64px', borderBottom: `1px solid ${BD}`, background: '#fff', display: 'flex', alignItems: 'center', gap: '10px', padding: '0 20px', fontSize: '17px', fontWeight: '700' }, `${icon('arrow-left', 20, 2.2, TX2)}Moule M-07<span style="margin-left:auto">${A.badge('Chambre L1T', 'neu', false)}</span>`);
      a.st = el(sc, { left: '20px', top: '140px', width: '348px', height: '120px', borderRadius: '16px', background: '#fff', border: `1px solid ${BD}` });
      el(a.st, { left: '18px', top: '16px', fontSize: '13px', color: TX2 }, 'Étape en cours');
      a.stv = el(a.st, { left: '18px', top: '40px', fontSize: '24px', fontWeight: '800', fontFamily: 'Plus Jakarta Sans', letterSpacing: '-0.02em' }, 'Démoulage possible');
      a.stb = el(a.st, { left: '18px', top: '82px', fontSize: '13px', color: OK, fontWeight: '600', display: 'flex', alignItems: 'center', gap: '6px' }, `${icon('circle-check', 14, 2.4, OK)}Séchage terminé à 09:38 · 6 h 32`);
      [['Température pièce', '32 °C', 'gauge'], ['Cycle', 'n° 2 117', 'history'], ['Opérateur', 'Karim B.', 'user']].forEach(([l, v, ic], i) => el(sc, { left: '20px', top: 280 + i * 64 + 'px', width: '348px', height: '54px', borderRadius: '12px', background: '#fff', border: `1px solid ${BD}`, display: 'flex', alignItems: 'center', gap: '12px', padding: '0 16px', fontSize: '15px', color: TX2 }, `${icon(ic, 17, 2, TX3)}${l}<b style="margin-left:auto;color:${TX}">${v}</b>`));
      a.btn = el(sc, { left: '20px', top: '700px', width: '348px', height: '72px', borderRadius: '18px', background: UM, color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px', fontSize: '20px', fontWeight: '700', boxShadow: '0 14px 30px rgba(30,59,255,0.3)' }, `${icon('check', 22, 2.6, '#fff')}<span class="bt">Valider le démoulage</span>`);
      a.rip = el(sc, { left: '194px', top: '736px', width: '20px', height: '20px', marginLeft: '-10px', marginTop: '-10px', borderRadius: '50%', background: 'rgba(255,255,255,0.5)', opacity: '0' });
      a.finger = el(sc, { left: '210px', top: '740px', width: '56px', height: '56px', marginLeft: '-28px', marginTop: '-28px', borderRadius: '50%', background: 'rgba(11,11,15,0.25)', border: '3px solid rgba(255,255,255,0.9)', opacity: '0' });
      // Notification.
      a.nt = el(sc, { left: '12px', top: '0', width: '364px', height: '96px', borderRadius: '22px', background: 'rgba(255,255,255,0.96)', boxShadow: '0 18px 40px rgba(11,11,15,0.28)', zIndex: '6' },
        `<div style="position:absolute;left:14px;top:16px;width:44px;height:44px;border-radius:12px;background:${UM};display:flex;align-items:center;justify-content:center">${icon('layers', 22, 2.2, '#fff')}</div><div style="position:absolute;left:70px;top:14px;font-size:13px;color:${TX2};width:280px">KMC PRODUCTION · maintenant</div><div style="position:absolute;left:70px;top:34px;font-size:15px;font-weight:700;width:280px">Moule M-07 : démoulage possible</div><div style="position:absolute;left:70px;top:56px;font-size:13.5px;color:${TX2};width:280px">Séchage terminé · Chambre L1T</div>`);
      // Tableau de bord partagé.
      const d = (a.d = el(root, { left: '880px', top: '300px', width: '900px', height: '560px', borderRadius: '24px', background: '#fff', boxShadow: '0 50px 120px rgba(0,0,0,0.4)', fontFamily: 'DM Sans', color: TX, overflow: 'hidden' }));
      el(d, { left: '28px', top: '24px', fontSize: '22px', fontWeight: '800', fontFamily: 'Plus Jakarta Sans', letterSpacing: '-0.02em' }, 'Atelier de moulage');
      el(d, { left: '28px', top: '60px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '14px', color: TX2 }, A.live('Mis à jour en direct pour toute l’équipe'));
      a.viewers = el(d, { right: '28px', top: '28px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '13px', color: TX2 }, `<span style="display:flex">${[22, 1, 14, 17].map((k, i) => `<span style="margin-left:${i ? -10 : 0}px">${A.photo(k, 32, '#fff')}</span>`).join('')}</span>4 personnes connectées`);
      const T4 = [['M-03', 'Chambre L2T', 'chauf'], ['M-07', 'Chambre L1T', 'dem'], ['M-12', 'Chambre L2T', 'sech'], ['M-20', 'Chambre L1T', 'pret'], ['M-22', 'Boîte de branchement 30', 'sech'], ['M-24', 'Chambre L2T', 'moul']];
      a.tiles = T4.map(([id, pr, st], i) => {
        const c = el(d, { left: 28 + (i % 3) * 284 + 'px', top: 110 + Math.floor(i / 3) * 210 + 'px', width: '268px', height: '190px', borderRadius: '14px', border: `1px solid ${BD}`, background: '#FBFBFA' });
        el(c, { left: '18px', top: '18px', fontSize: '20px', fontWeight: '800' }, id);
        el(c, { left: '18px', top: '50px', fontSize: '14px', color: TX2 }, pr);
        c._b = el(c, { left: '18px', top: '90px' }, A.badge(STEPS[st][0], STEPS[st][1]));
        el(c, { left: '18px', top: '136px' }, A.bar(st === 'dem' || st === 'pret' ? 1 : 0.5, st === 'dem' || st === 'pret' ? OK : UM, 232, 8));
        return c;
      });
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      rise(a.tag, T, s + 0.1, 0.7, 20);
      const pp = P(T, s + 0.2, 0.8, Ease.outCubic); S(a.ph, { y: (1 - pp) * 120, o: pp });
      const dp = P(T, s + 0.5, 0.8, Ease.outCubic); S(a.d, { x: (1 - dp) * 120, o: dp });
      const tn = wordAt('mobile', 'telephone', L('mobile', 0).start + 2) - 0.3;
      const np = spring(T - tn, 200, 16); const tOff = L('mobile', 1).start - 0.2;
      S(a.nt, { y: lerp(-110, 52, clamp(np)) - P(T, tOff, 0.4, Ease.inCubic) * 160, o: clamp(np * 2) });
      const tTap = wordAt('mobile', 'geste', L('mobile', 1).start + 1.6) - 0.1;
      const fp = P(T, tTap - 0.6, 0.5, Ease.outCubic) * (1 - P(T, tTap + 0.5, 0.3));
      S(a.finger, { o: fp, s: T > tTap && T < tTap + 0.25 ? 0.85 : 1, y: (1 - P(T, tTap - 0.6, 0.5, Ease.outCubic)) * 120 });
      const rp = P(T, tTap, 0.6, Ease.outCubic); S(a.rip, { s: 1 + rp * 22, o: (1 - rp) * (T > tTap ? 0.9 : 0) });
      const done = T > tTap + 0.1;
      a.btn.style.background = done ? OK : UM; a.btn.querySelector('.bt').textContent = done ? 'Démoulage validé' : 'Valider le démoulage';
      a.stv.textContent = done ? 'Lubrification' : 'Démoulage possible';
      const t7 = a.tiles[1];
      t7._b.innerHTML = done ? A.badge('Lubrification', 'neu') : A.badge(STEPS.dem[0], 'ok');
      const fl = P(T, tTap + 0.35, 0.3) * (1 - P(T, tTap + 1.6, 0.6));
      t7.style.boxShadow = `0 0 0 ${4 * fl}px rgba(30,59,255,${0.35 * fl})`; t7.style.borderColor = fl > 0.1 ? UM : BD;
      a.tiles.forEach((c, i) => { const p = P(T, s + 0.8 + i * 0.07, 0.4); S(c, { o: p, y: (1 - p) * 12 }); });
    },
  };

  // Modification demandée en langage naturel, en ligne en quelques minutes.
  OWN.modif = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 9);
      a.B = builder(root, 110, 150, 1700, 840);
      botMsg(a.B.chat, 30, 'Votre application <b>KMC Production</b> est en ligne. Que voulez-vous ajouter ?');
      a.u = userMsg(a.B.chat, 130);
      a.bot = botMsg(a.B.chat, 360, 'C’est fait : la règle est active, et le chef d’équipe est prévenu à chaque dépassement.');
      a.rule = el(a.B.chat, { left: '70px', top: '470px', width: '466px', height: '150px', borderRadius: '14px', background: '#fff', border: '1px solid #E6E1D6', fontSize: '15px', color: TX2 },
        `<div style="position:absolute;left:18px;top:16px;display:flex;align-items:center;gap:10px;font-weight:700;color:${TX};font-size:16px">${icon('bell-ring', 18, 2.2, BAD)}Nouvelle alerte</div><div style="position:absolute;left:18px;top:52px;line-height:1.6">Si <b style="color:${TX}">Étuve 2</b> &gt; <b style="color:${TX}">65 °C</b><br>Prévenir : <b style="color:${TX}">chef d’équipe</b> · application et téléphone</div>`);
      a.live = el(a.B.chat, { left: '70px', top: '640px', height: '40px', padding: '0 16px', borderRadius: '20px', background: '#E7F6EC', color: OK, display: 'flex', alignItems: 'center', gap: '8px', fontSize: '15px', fontWeight: '700' }, `${icon('circle-check', 16, 2.4, OK)}En ligne · publié en 3 minutes`);
      const pvW = 1700 - 560, pvH = 840 - 58, K = 0.66;
      a.frame = el(a.B.pv, { left: (pvW - A.W * K) / 2 + 'px', top: (pvH - A.H * K) / 2 + 'px', width: A.W * K + 'px', height: A.H * K + 'px', borderRadius: '10px', overflow: 'hidden', boxShadow: '0 20px 60px rgba(11,11,15,0.14)' });
      a.app = atelier(a.frame); a.app.root.style.transform = `scale(${K})`;
      a.MSG = 'Ajoute une alerte si l’étuve 2 dépasse 65 °C, et préviens le chef d’équipe.';
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      const wp = P(T, s + 0.1, 0.7, Ease.outCubic); S(a.B.r, { y: (1 - wp) * 60, o: wp });
      a.app.update(T, s - 5);
      const tq = wordAt('modif', 'demandez', s + 4) - 0.3;
      const typed = clamp((T - (s + 0.7)) / Math.max(1.5, tq - s - 0.9));
      a.u.t.innerHTML = a.MSG.slice(0, Math.floor(typed * a.MSG.length)) + (typed < 1 && T > s + 0.6 ? '<span class="caret"></span>' : '');
      S(a.u.b, { o: P(T, s + 0.5, 0.3) });
      const tl = wordAt('modif', 'ligne', tq + 1.6) - 0.6;
      rise(a.bot, T, tq + 0.3, 0.4, 10); rise(a.rule, T, tq + 0.6, 0.5, 14);
      const lp = spring(T - tl, 220, 15); S(a.live, { s: Math.max(0.001, 0.8 + 0.2 * lp), o: clamp(lp * 1.4) });
      const tp = spring(T - tl - 0.5, 200, 16); S(a.app.toast, { x: (1 - clamp(tp)) * 60, o: clamp(tp * 1.4) });
    },
  };

  // L'outil existant complété : données reliées, nouveaux modules, puis le gain de temps.
  OWN.existant = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 11);
      a.t = title(root, 'Vous ne repartez pas de <span style="color:#1E3BFF">zéro</span>.', 90, 64);
      const B = (a.old = browser(root, 110, 230, 0.34, 'suivi-moulage · version interne', 1600, 1000));
      const o = el(B.body, { inset: '0', background: '#fff', fontFamily: 'Times New Roman, serif', color: '#000', padding: '18px' });
      el(o, { left: '18px', top: '14px', fontSize: '26px', fontWeight: '700' }, 'Suivi moulage');
      let tb = '<table style="border-collapse:collapse;font-size:15px;width:500px">' + '<tr>' + ['Moule', 'Début', 'Fin prévue', 'Temp.'].map((x) => `<th style="border:1px solid #999;padding:4px 8px;background:#ddd">${x}</th>`).join('') + '</tr>';
      [['M-01', '08:10', '14:50', '57'], ['M-03', '09:05', '16:20', '61'], ['M-07', '03:06', '09:38', '32'], ['M-12', '08:10', '12:20', '58'], ['M-14', '10:40', '?', ''], ['M-22', '07:55', '13:30', '55']].forEach((r) => (tb += '<tr>' + r.map((x) => `<td style="border:1px solid #999;padding:4px 8px">${x}</td>`).join('') + '</tr>'));
      el(o, { left: '18px', top: '60px' }, tb + '</table>');
      a.oldL = el(root, { left: '110px', top: '628px', width: '544px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '22px', color: '#6F6C66' }, 'Votre outil actuel');
      // Données au centre, modules YouCraft à droite.
      a.db = el(root, { left: '800px', top: '330px', width: '200px', height: '200px', borderRadius: '50px', background: INK, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '10px', color: STONE, fontFamily: 'DM Sans', fontWeight: '700', fontSize: '22px', boxShadow: '0 30px 80px rgba(11,11,15,0.3)' }, `${icon('server', 64, 1.8, LIME)}Vos données`);
      a.lnk = el(root, { left: '0', top: '0', width: '1920px', height: '1080px' });
      const MODS = [['layout-dashboard', 'Tableau de bord en direct'], ['bell-ring', 'Alertes et notifications'], ['smartphone', 'Vue mobile pour l’atelier'], ['calendar', 'Planning des moules'], ['chart-column', 'Rapports de production']];
      a.mods = MODS.map(([ic, t], i) => {
        const c = el(root, { left: '1180px', top: 200 + i * 98 + 'px', width: '620px', height: '80px', borderRadius: '20px', background: '#fff', border: '1px solid #E6E1D6', boxShadow: '0 18px 44px rgba(11,11,15,0.10)', display: 'flex', alignItems: 'center', gap: '18px', padding: '0 22px', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '24px', color: INK },
          `<span style="width:48px;height:48px;border-radius:14px;background:${i === 0 ? UM : '#EEF0FF'};display:flex;align-items:center;justify-content:center">${icon(ic, 24, 2.2, i === 0 ? '#fff' : UM)}</span>${t}<span style="margin-left:auto;height:30px;padding:0 12px;border-radius:15px;background:${LIME};font-size:15px;display:flex;align-items:center">Ajouté</span>`);
        return c;
      });
      let sv = `<svg width="1920" height="1080" style="position:absolute;left:0;top:0;overflow:visible">`;
      sv += `<path class="ln" pathLength="1" d="M654 430 C720 430 740 430 800 430" stroke="${UM}" stroke-width="4" fill="none" stroke-dasharray="1" stroke-dashoffset="1"/>`;
      MODS.forEach((_, i) => { const y = 240 + i * 98; sv += `<path class="ln" pathLength="1" d="M1000 430 C1090 430 1090 ${y} 1180 ${y}" stroke="${UM}" stroke-width="3" fill="none" stroke-dasharray="1" stroke-dashoffset="1" opacity="0.6"/>`; });
      a.lnk.innerHTML = sv + '</svg>';
      // Gain de temps.
      a.race = el(root, { left: '260px', top: '780px', width: '1400px', height: '200px', fontFamily: 'DM Sans' });
      el(a.race, { left: '0', top: '0', fontSize: '20px', fontWeight: '700', letterSpacing: '0.12em', color: '#6F6C66' }, 'DÉVELOPPEMENT CLASSIQUE');
      el(a.race, { right: '0', top: '-6px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', color: INK }, 'Des mois');
      a.b1 = el(a.race, { left: '0', top: '40px', width: '1400px', height: '18px', borderRadius: '9px', background: 'repeating-linear-gradient(-45deg,#CFCABE 0 8px,#DED9CE 8px 16px)', transformOrigin: '0 50%' });
      el(a.race, { left: '0', top: '100px', fontSize: '20px', fontWeight: '700', letterSpacing: '0.12em', color: UM }, 'AVEC YOUCRAFT');
      el(a.race, { right: '0', top: '94px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', color: UM }, 'Quelques jours');
      a.b2 = el(a.race, { left: '0', top: '140px', width: '130px', height: '18px', borderRadius: '9px', background: UM, boxShadow: '0 0 0 8px rgba(30,59,255,0.12)', transformOrigin: '0 50%' });
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      rise(a.t, T, s + 0.1, 0.7);
      const l1 = L('existant', 1).start, l2 = L('existant', 2).start;
      const op = P(T, s + 0.6, 0.7, Ease.outCubic); S(a.old.r, { y: (1 - op) * 40, o: op }); S(a.oldL, { o: op });
      const tc = wordAt('existant', 'connectant', l1 + 2.5) - 0.4;
      const dp = spring(T - tc, 200, 16); S(a.db, { s: Math.max(0.001, 0.7 + 0.3 * dp), o: clamp(dp * 1.4) });
      const ln = [...a.lnk.querySelectorAll('.ln')];
      ln.forEach((p, i) => (p.style.strokeDashoffset = String(1 - P(T, tc + 0.2 + (i ? 0.4 + i * 0.12 : 0), 0.6, Ease.outCubic))));
      a.mods.forEach((c, i) => { const p = spring(T - (wordAt('existant', 'completer', l1 + 1) + 0.2 + i * 0.22), 200, 17); S(c, { x: (1 - clamp(p)) * 60, o: clamp(p * 1.4) }); });
      // Troisième phrase : le réseau remonte un peu, la comparaison apparaît.
      const rp = P(T, l2 - 0.3, 0.6, Ease.outCubic);
      S(a.race, { y: (1 - rp) * 40, o: rp });
      const tm = wordAt('existant', 'mois', l2 + 1), tj = wordAt('existant', 'jours', l2 + 2.5);
      S(a.b1, { sx: Math.max(0.001, P(T, tm - 0.6, 1.4, Ease.inOutSine)) });
      S(a.b2, { sx: Math.max(0.001, P(T, tj - 0.3, 0.5, Ease.outBack)) });
    },
  };

  // Présentation commerciale générée : invite, diapositive animée, vignettes.
  OWN.prez = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 12);
      a.tag = tag(root, 'PRÉSENTATIONS', 'Une présentation en quelques minutes', 'Claire, moderne et dynamique');
      a.pr = el(root, { left: '880px', top: '96px', width: '930px', height: '70px', borderRadius: '35px', background: 'rgba(245,242,234,0.08)', border: '1px solid rgba(245,242,234,0.18)', display: 'flex', alignItems: 'center', gap: '14px', padding: '0 12px 0 26px', fontFamily: 'DM Sans', fontSize: '20px', whiteSpace: 'nowrap', overflow: 'hidden', color: STONE },
        `${icon('wand-sparkles', 22, 2.2, LIME)}<span class="pt"></span><span style="margin-left:auto;height:48px;padding:0 20px;border-radius:24px;background:${LIME};color:${INK};display:flex;align-items:center;font-weight:700;font-size:18px">Générer</span>`);
      a.PT = 'Présentation commerciale : nos chambres télécom composite';
      const SW = 1280, SH = 720;
      a.stage = el(root, { left: '110px', top: '250px', width: SW + 'px', height: SH + 'px', borderRadius: '22px', overflow: 'hidden', boxShadow: '0 50px 140px rgba(0,0,0,0.5)', background: '#0F1722' });
      const slide = (bg) => el(a.stage, { inset: '0', background: bg, fontFamily: 'DM Sans', opacity: '0' });
      // Diapositive 1 : titre.
      const s1 = slide('radial-gradient(circle at 78% 30%, #23406B 0%, #0F1722 62%)');
      el(s1, { left: '80px', top: '86px', height: '40px', padding: '0 16px', borderRadius: '20px', background: 'rgba(242,140,40,0.16)', color: '#F6A04D', fontWeight: '700', fontSize: '18px', letterSpacing: '0.1em', display: 'flex', alignItems: 'center' }, 'GROUPE KMC');
      s1._t = el(s1, { left: '80px', top: '160px', width: '640px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '66px', lineHeight: '1.05', letterSpacing: '-0.035em', color: '#fff' }, 'Chambres télécom <span style="color:#F6A04D">composite</span>');
      el(s1, { left: '80px', top: '420px', width: '560px', fontSize: '26px', lineHeight: '1.45', color: '#AFC0D6' }, 'Des solutions robustes et légères pour vos réseaux de télécommunication.');
      s1._c = chamber(s1, 760, 120, 460, '#F6A04D', '#8FB4E6');
      // Diapositive 2 : atouts.
      const s2 = slide('#F6F4EF');
      el(s2, { left: '80px', top: '70px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '54px', letterSpacing: '-0.03em', color: '#0F1722' }, 'Pourquoi le composite ?');
      s2._c = [['shield-check', 'Résistantes', 'Conçues pour durer sur le terrain'], ['zap', 'Rapides à poser', 'Moins de manutention sur chantier'], ['trending-down', 'Plus légères', 'Transport et pose facilités']].map(([ic, t, d], i) => {
        const c = el(s2, { left: 80 + i * 380 + 'px', top: '220px', width: '350px', height: '400px', borderRadius: '24px', background: '#fff', boxShadow: '0 20px 50px rgba(15,23,34,0.10)' });
        el(c, { left: '34px', top: '40px', width: '90px', height: '90px', borderRadius: '26px', background: i === 1 ? '#F28C28' : '#0F1722', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 44, 2, '#fff'));
        el(c, { left: '34px', top: '170px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '36px', letterSpacing: '-0.02em', color: '#0F1722' }, t);
        el(c, { left: '34px', top: '226px', width: '290px', fontSize: '23px', lineHeight: '1.4', color: '#5B6472' }, d);
        return c;
      });
      // Diapositive 3 : la gamme.
      const s3 = slide('#0F1722');
      el(s3, { left: '80px', top: '70px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '54px', letterSpacing: '-0.03em', color: '#fff' }, 'Notre gamme');
      s3._c = [['Chambre L1T', 'Raccordement'], ['Chambre L2T', 'Distribution'], ['Chambre L3T', 'Réseau structurant'], ['Boîtes de branchement', 'Abonnés']].map(([t, d], i) => {
        const c = el(s3, { left: 80 + i * 285 + 'px', top: '210px', width: '262px', height: '420px', borderRadius: '22px', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.12)' });
        const ch2 = chamber(c, 40, 50, 180, i === 3 ? '#F6A04D' : '#8FB4E6', '#F6A04D'); ch2.draw(1); c._ch = ch2;
        el(c, { left: '26px', top: '290px', width: '220px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '26px', color: '#fff', lineHeight: '1.15' }, t);
        el(c, { left: '26px', top: '360px', fontSize: '19px', color: '#AFC0D6' }, d);
        return c;
      });
      a.sl = [s1, s2, s3];
      a.th = a.sl.map((_, i) => {
        const c = el(root, { left: '1450px', top: 250 + i * 250 + 'px', width: '360px', height: '220px', borderRadius: '14px', overflow: 'hidden', border: '3px solid rgba(245,242,234,0.15)', background: ['#16243A', '#F6F4EF', '#0F1722'][i] });
        el(c, { left: '20px', top: '20px', width: ['200px', '230px', '160px'][i], height: '18px', borderRadius: '9px', background: i === 1 ? '#0F1722' : '#fff', opacity: '0.8' });
        [0, 1, 2].forEach((j) => el(c, { left: 20 + j * 110 + 'px', top: '80px', width: '96px', height: '110px', borderRadius: '10px', background: i === 1 ? '#fff' : 'rgba(255,255,255,0.10)' }));
        return c;
      });
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      rise(a.tag, T, s + 0.1, 0.7, 20);
      rise(a.pr, T, s + 0.3, 0.6, 20);
      const typed = clamp((T - s - 0.8) / 2.2);
      a.pr.querySelector('.pt').innerHTML = a.PT.slice(0, Math.floor(typed * a.PT.length)) + (typed < 1 ? '<span class="caret"></span>' : '');
      const t0 = L('prez', 1).start - 0.4, span = Math.max(4.5, e - t0 - 0.4) / 3;
      const sp = P(T, s + 3.0, 0.8, Ease.outCubic); S(a.stage, { y: (1 - sp) * 60, o: sp, s: 0.96 + 0.04 * sp });
      a.sl.forEach((sl, i) => {
        const a0 = i === 0 ? s + 3.0 : t0 + i * span, a1 = i === 2 ? e + 5 : t0 + (i + 1) * span;
        const pin = i === 0 ? 1 : P(T, a0, 0.7, Ease.outQuint), pout = P(T, a1, 0.7, Ease.inOutQuint);
        sl.style.opacity = String(T >= a0 && T < a1 + 0.7 ? Math.min(pin, 1) : 0);
        S(sl, { x: (1 - pin) * 220 - pout * 260, s: 1 - pout * 0.04 });
        sl.style.zIndex = String(i);
        if (i === 0) { sl._c.draw(P(T, s + 3.2, 2.4, Ease.inOutSine)); rise(sl._t, T, s + 3.3, 0.8, 30); }
        if (i > 0) sl._c.forEach((c, j) => { const p = spring(T - a0 - 0.3 - j * 0.15, 200, 17); S(c, { y: (1 - clamp(p)) * 60, o: clamp(p * 1.4) }); });
        const on = T >= a0 && T < a1;
        a.th[i].style.borderColor = on ? LIME : 'rgba(245,242,234,0.15)';
        S(a.th[i], { o: P(T, s + 3.2 + i * 0.15, 0.4), s: on ? 1.03 : 1 });
      });
    },
  };

  // Site vitrine moderne : page d'accueil qui défile, puis version mobile.
  OWN.site = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 13);
      a.tag = tag(root, 'SITE INTERNET', 'Une vitrine moderne', 'Pour présenter votre gamme');
      const PW = 1600, PH = 1000, K = 0.86;
      a.B = browser(root, 960 - (PW * K) / 2, 200, K, 'groupe-kmc · aperçu du nouveau site', PW, PH * 0.94);
      const pg = (a.pg = el(a.B.body, { left: '0', top: '0', width: PW + 'px', height: '1900px', transformOrigin: '0 0', transform: `scale(${K})`, background: '#fff', fontFamily: 'DM Sans', color: '#0F1722' }));
      const nav = el(pg, { left: '0', top: '0', width: PW + 'px', height: '84px', borderBottom: '1px solid #EEF0F3', background: '#fff' });
      el(nav, { left: '60px', top: '22px', display: 'flex', alignItems: 'center', gap: '12px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', letterSpacing: '-0.02em' }, `<span style="width:40px;height:40px;border-radius:10px;background:#0F1722;display:flex;align-items:center;justify-content:center">${icon('layers', 22, 2.2, '#F6A04D')}</span>KMC`);
      el(nav, { left: '520px', top: '30px', display: 'flex', gap: '44px', fontSize: '18px', fontWeight: '600', color: '#3A4352' }, ['Chambres télécom', 'Boîtes de branchement', 'Réalisations', 'Le groupe', 'Contact'].map((x) => `<span>${x}</span>`).join(''));
      el(nav, { right: '60px', top: '20px', height: '44px', padding: '0 22px', borderRadius: '22px', background: '#F28C28', color: '#fff', display: 'flex', alignItems: 'center', fontWeight: '700', fontSize: '17px' }, 'Demander un devis');
      const hero = el(pg, { left: '0', top: '84px', width: PW + 'px', height: '700px', background: 'radial-gradient(circle at 80% 40%, #23406B 0%, #0F1722 65%)', overflow: 'hidden' });
      el(hero, { left: '80px', top: '120px', height: '38px', padding: '0 16px', borderRadius: '19px', background: 'rgba(242,140,40,0.16)', color: '#F6A04D', fontWeight: '700', fontSize: '16px', letterSpacing: '0.1em', display: 'flex', alignItems: 'center' }, 'RÉSEAUX TÉLÉCOM');
      a.h1 = el(hero, { left: '80px', top: '186px', width: '780px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '68px', lineHeight: '1.04', letterSpacing: '-0.035em', color: '#fff' }, 'Chambres télécom et boîtes de branchement <span style="color:#F6A04D">composites</span>');
      el(hero, { left: '80px', top: '460px', width: '640px', fontSize: '24px', lineHeight: '1.5', color: '#AFC0D6' }, 'Des solutions robustes et légères, pensées pour la pose et pour durer.');
      el(hero, { left: '80px', top: '570px', display: 'flex', gap: '16px' }, `<span style="height:60px;padding:0 28px;border-radius:30px;background:#F28C28;color:#fff;display:flex;align-items:center;font-weight:700;font-size:19px">Découvrir la gamme</span><span style="height:60px;padding:0 28px;border-radius:30px;border:2px solid rgba(255,255,255,0.4);color:#fff;display:flex;align-items:center;font-weight:700;font-size:19px">Demander un devis</span>`);
      a.hc = chamber(hero, 940, 110, 560, '#F6A04D', '#8FB4E6');
      el(pg, { left: '80px', top: '860px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '46px', letterSpacing: '-0.03em' }, 'Notre gamme');
      a.cards = [['Chambre L1T', 'Raccordement des abonnés'], ['Chambre L2T', 'Réseaux de distribution'], ['Chambre L3T', 'Réseaux structurants'], ['Boîtes de branchement', 'Du réseau à l’abonné']].map(([t, d], i) => {
        const c = el(pg, { left: 80 + i * 365 + 'px', top: '950px', width: '345px', height: '420px', borderRadius: '24px', background: '#F6F7F9', border: '1px solid #EEF0F3' });
        const cc = chamber(c, 70, 40, 210, '#0F1722', '#F28C28'); cc.draw(1);
        el(c, { left: '28px', top: '290px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '27px' }, t);
        el(c, { left: '28px', top: '332px', fontSize: '18px', color: '#5B6472' }, d);
        el(c, { left: '28px', top: '370px', fontSize: '16px', fontWeight: '700', color: '#F28C28' }, 'Voir la fiche →');
        return c;
      });
      const band = el(pg, { left: '80px', top: '1440px', width: '1440px', height: '180px', borderRadius: '28px', background: '#0F1722', color: '#fff' });
      [['shield-check', 'Robustesse'], ['zap', 'Pose rapide'], ['trending-down', 'Légèreté'], ['handshake', 'Accompagnement']].forEach(([ic, t], i) => el(band, { left: 60 + i * 350 + 'px', top: '60px', display: 'flex', alignItems: 'center', gap: '16px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '26px' }, `<span style="width:60px;height:60px;border-radius:18px;background:rgba(242,140,40,0.18);display:flex;align-items:center;justify-content:center">${icon(ic, 28, 2, '#F6A04D')}</span>${t}`));
      // Téléphone : version mobile.
      a.ph = el(root, { left: '1480px', top: '330px', width: '300px', height: '620px', borderRadius: '48px', background: '#0B0B0F', boxShadow: '0 50px 120px rgba(0,0,0,0.45)' });
      const ms = el(a.ph, { left: '12px', top: '12px', width: '276px', height: '596px', borderRadius: '38px', overflow: 'hidden', background: 'radial-gradient(circle at 70% 30%, #23406B 0%, #0F1722 70%)', fontFamily: 'DM Sans' });
      el(ms, { left: '22px', top: '50px', display: 'flex', alignItems: 'center', gap: '8px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '20px', color: '#fff' }, `<span style="width:28px;height:28px;border-radius:7px;background:#fff;display:flex;align-items:center;justify-content:center">${icon('layers', 15, 2.2, '#F28C28')}</span>KMC`);
      el(ms, { left: '22px', top: '120px', width: '232px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', lineHeight: '1.08', color: '#fff', letterSpacing: '-0.03em' }, 'Chambres télécom <span style="color:#F6A04D">composites</span>');
      const mc = chamber(ms, 40, 300, 200, '#F6A04D', '#8FB4E6'); mc.draw(1);
      el(ms, { left: '22px', top: '520px', width: '232px', height: '50px', borderRadius: '25px', background: '#F28C28', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: '700', fontSize: '16px' }, 'Découvrir la gamme');
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      rise(a.tag, T, s + 0.1, 0.7, 20);
      const wp = P(T, s + 0.2, 0.8, Ease.outCubic); S(a.B.r, { y: (1 - wp) * 60, o: wp });
      a.hc.draw(P(T, s + 0.6, 2.2, Ease.inOutSine));
      rise(a.h1, T, s + 0.6, 0.8, 30);
      const tv = wordAt('site', 'presenter', s + 3) - 0.3;
      const sc = P(T, tv, 2.4, Ease.inOutCubic);
      a.pg.style.transform = `translateY(${(-sc * 760 * 0.86).toFixed(1)}px) scale(0.86)`;
      a.cards.forEach((c, i) => { const p = P(T, tv + 0.9 + i * 0.12, 0.5); S(c, { o: p, y: (1 - p) * 30 }); });
      const pp = spring(T - Math.max(tv + 2.2, e - 3.2), 180, 17); S(a.ph, { x: (1 - clamp(pp)) * 200, o: clamp(pp * 1.4), r: (1 - clamp(pp)) * 6 });
    },
  };

  OWN.securite = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 7);
      a.t = title(root, 'Sécurisé, et déjà <span style="color:#D7FF3F">adopté</span>.', 110, 66, STONE);
      a.cards = [['lock', 'Connexion protégée', 'Authentification des utilisateurs'], ['shield-check', 'Droits d’accès par rôle', 'Chacun voit ce qui le concerne'], ['server', 'Hébergé en Europe', 'Données hébergées en Europe']].map(([ic, t, d], i) => {
        const c = el(root, { left: 180 + i * 540 + 'px', top: '270px', width: '480px', height: '300px', borderRadius: '28px', background: 'rgba(245,242,234,0.05)', border: '1px solid rgba(245,242,234,0.14)', fontFamily: 'DM Sans', color: STONE });
        el(c, { left: '40px', top: '40px', width: '80px', height: '80px', borderRadius: '24px', background: i === 1 ? LIME : UM, display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 42, 2.2, i === 1 ? INK : '#fff'));
        el(c, { left: '40px', top: '150px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '32px', letterSpacing: '-0.02em', width: '420px' }, t);
        el(c, { left: '40px', top: '204px', fontSize: '22px', color: '#B5B0A5', width: '400px', lineHeight: '1.35' }, d);
        return c;
      });
      a.nums = [['750', 'utilisateurs'], ['350', 'applications déployées']].map(([n, l], i) => {
        const c = el(root, { left: 420 + i * 600 + 'px', top: '640px', width: '480px', textAlign: 'center', fontFamily: 'DM Sans', color: STONE });
        c._n = el(c, { left: '0', top: '0', width: '480px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '130px', letterSpacing: '-0.05em', color: i ? LIME : '#fff' }, n);
        el(c, { left: '0', top: '160px', width: '480px', fontSize: '28px', color: '#B5B0A5' }, (i ? '' : 'plus de ') + l);
        c._v = +n; return c;
      });
    },
    update(T, s) {
      const a = this.a; a.bg(T);
      rise(a.t, T, s + 0.1, 0.7);
      a.cards.forEach((c, i) => { const t = wordAt('securite', ['connexion', 'roles', 'hebergees'][i], s + 1 + i); const p = spring(T - t + 0.15, 200, 17); S(c, { y: (1 - clamp(p)) * 60, s: 0.92 + 0.08 * p, o: clamp(p * 1.3) }); });
      const l1 = L('securite', 1).start;
      a.nums.forEach((c, i) => { const t = wordAt('securite', i ? 'trois' : 'sept', l1 + 1 + i * 2) - 0.2; const p = P(T, t, 1.2, Ease.outCubic); S(c, { o: P(T, t, 0.3), y: (1 - p) * 30 }); c._n.textContent = String(Math.round(c._v * p)); });
    },
  };

  OWN.fin = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 9);
      a.chips = [['layout-dashboard', 'Suivi de production'], ['presentation', 'Présentations'], ['globe', 'Site internet']].map(([ic, t], i) => { const c = chip(root, t, ic, true); Object.assign(c.style, { height: '72px', fontSize: '28px', borderRadius: '36px' }); c._x = [430, 830, 1190][i]; return c; });
      a.d = el(root, { left: '0', width: '1920px', top: '330px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '700', fontSize: '46px', letterSpacing: '-0.02em', color: STONE }, 'Tout commence par une simple <span style="color:#4F6BFF">description</span>.');
      a.line = el(root, { left: '0', width: '1920px', top: '480px', display: 'flex', justifyContent: 'center' });
      a.L = C.ycLogo(a.line, 170, true);
      a.t2 = el(root, { left: '0', width: '1920px', top: '730px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '60px', letterSpacing: '-0.03em', color: STONE }, 'Donnons <span style="color:#D7FF3F">vie</span> à vos idées.');
      a.k = el(root, { left: '0', width: '1920px', top: '960px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '22px', letterSpacing: '0.2em', color: '#8F8C86' }, 'PRÉPARÉ POUR LE GROUPE KMC');
      a.black = el(root, { inset: '0', background: '#000', opacity: '0' });
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      a.chips.forEach((c, i) => { const p = spring(T - s - 0.3 - i * 0.35, 200, 17); S(c, { x: c._x, y: 170 + (1 - clamp(p)) * 30, o: clamp(p * 1.4), s: Math.max(0.001, 0.85 + 0.15 * p) }); });
      rise(a.d, T, wordAt('fin', 'description', s + 3) - 0.5, 0.7);
      const tl = L('fin', 1).start;
      C.ycLogoAnim(a.L, T, tl - 0.3);
      a.L.glyphs.forEach((c, i) => { const p = P(T, tl + 0.1 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 50, o: p }); });
      rise(a.t2, T, tl + 0.8, 0.8); rise(a.k, T, tl + 1.4, 0.8, 10);
      a.black.style.opacity = String(P(T, e - 0.9, 0.9, Ease.inOutSine));
    },
  };

  // ---------- Montage ----------
  const stage = document.getElementById('kmc');
  const NAMES = Object.keys(SC);
  const R = {};
  NAMES.forEach((n) => { const r = el(stage, { inset: '0', display: 'none' }); OWN[n].build(r); R[n] = r; });
  // Fondu enchaîné court entre scènes : la scène suivante apparaît sur 0,35 s.
  const subs = document.getElementById('subs');
  const sb = el(subs, { left: '0', width: '1920px', bottom: '40px', top: 'auto', display: 'flex', justifyContent: 'center' });
  const sbox = h('div', '', sb);
  Object.assign(sbox.style, { maxWidth: '1500px', padding: '12px 28px 14px', borderRadius: '18px', background: 'rgba(11,11,15,0.86)', color: STONE, fontFamily: 'DM Sans', fontWeight: '500', fontSize: '36px', lineHeight: '1.3', textAlign: 'center', opacity: '0' });
  let cur = [];
  window.render = function (t) {
    const i = Math.max(0, NAMES.findIndex((n) => t >= SC[n][0] && t < SC[n][1]));
    const name = t >= CT.END ? NAMES[NAMES.length - 1] : NAMES[i];
    const prev = NAMES[NAMES.indexOf(name) - 1];
    const [s, e] = SC[name];
    const fade = prev ? clamp((t - s) / 0.35) : 1;
    const show = fade < 1 ? [prev, name] : [name];
    NAMES.forEach((n) => { const on = show.includes(n); if ((R[n].style.display !== 'none') !== on) R[n].style.display = on ? 'block' : 'none'; });
    if (fade < 1) { OWN[prev].update(t, SC[prev][0], SC[prev][1]); R[prev].style.zIndex = '1'; }
    OWN[name].update(t, s, e); R[name].style.zIndex = '2'; R[name].style.opacity = String(Ease.inOutSine(fade));
    const cue = CT.subs.find(([a, b]) => t >= a && t < b);
    if (cue) { if (sbox._t !== cue[2]) { sbox.innerHTML = cue[2]; sbox._t = cue[2]; } const f = Math.min(P(t, cue[0], 0.12), 1 - P(t, cue[1] - 0.12, 0.12)); sbox.style.opacity = String(f); }
    else sbox.style.opacity = '0';
  };
  Promise.all([document.fonts.ready, ...[...document.images].map((im) => (im.complete ? 1 : new Promise((r) => { im.onload = r; im.onerror = r; })))]).then(() => {
    window.render(parseFloat(new URLSearchParams(location.search).get('t') || '0'));
    window.READY = true;
  });
})();

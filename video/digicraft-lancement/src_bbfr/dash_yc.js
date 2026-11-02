// Dashboards métier sur mesure (charte YouCraft) : portraits illustrés, flotte de camions et retards, santé machines,
// stocks, planning d'équipes. Remplace les mini dashboards génériques (U.dash) et les avatars à initiales (U.avatar).
(function () {
  const { h, S, P, Ease, lerp, clamp, icon, seeded } = E;
  const el = C.el;
  const INK = '#0B0B0F', INK2 = '#16161C', STONE = '#F5F2EA', PAPER = '#FBFAF6', UM = '#1E3BFF', UMD = '#4F6BFF', LIME = '#D7FF3F';
  const MUTED = '#77746E', LINE = '#E6E1D6', MIST = '#DFE8F0';
  const OK = ['#E3F5EA', '#16834A'], WARN = ['#FFF1D6', '#B86A00'], BAD = ['#FDE5E5', '#D93A3F'], INFO = ['#E8ECFF', UM], NEU = ['#F1EEE7', '#55534E'];
  let uid = 0;

  // ---------- Portraits illustrés ----------
  const SKIN = ['#F3D5C0', '#E9BD9A', '#D19A70', '#A86F4C', '#71462E', '#F6DECB'];
  const HAIR = ['#2A1C14', '#5B3A22', '#8C5A2E', '#D2A65E', '#141414', '#8A8780', '#A4532C'];
  const BG = ['#E8ECFF', '#ECE8DF', '#DFE8F0', '#F0F7CF', '#E6E1D6', '#E3E8FF'];
  // Distribution : [prénom nom, rôle · ville, peau, cheveux, coiffure, haut, fond, lunettes, barbe]
  const CAST = [
    ['Sarah Martin', 'Consultant · Paris', 0, 1, 'long', UM, 0, 0, 0],
    ['Tom Becker', 'Data analyst · Munich', 5, 3, 'short', INK, 1, 1, 0],
    ['Inès Duval', 'Project lead · Lille', 2, 0, 'bun', '#55534E', 3, 0, 0],
    ['Marc Petit', 'Tax advisor · Nantes', 0, 0, 'buzz', UMD, 4, 0, 1],
    ['Amara Okafor', 'HR partner · London', 4, 4, 'curly', LIME, 2, 0, 0],
    ['Lucas Moreau', 'Engineer · Lyon', 2, 1, 'side', INK, 5, 0, 0],
    ['Yuki Tanaka', 'Designer · Amsterdam', 5, 4, 'bob', UM, 1, 0, 0],
    ['Karim Benali', 'Technician · Lyon', 3, 4, 'short', '#55534E', 3, 0, 1],
    ['Elena Rossi', 'Sales lead · Milan', 0, 0, 'long', INK, 4, 1, 0],
    ['David Cohen', 'Finance · Paris', 1, 5, 'bald', UMD, 0, 0, 1],
    ['Priya Nair', 'Analyst · Dublin', 3, 4, 'long', LIME, 5, 0, 0],
    ['Jonas Weber', 'Ops manager · Leipzig', 5, 3, 'side', UM, 2, 0, 0],
    ['Fatou Diallo', 'Supply planner · Bordeaux', 4, 4, 'bun', UM, 1, 0, 0],
    ['Paul Lambert', 'Driver · Lyon', 1, 2, 'buzz', INK, 3, 0, 0],
    ['Sofia Alves', 'Buyer · Porto', 2, 6, 'bob', '#55534E', 0, 1, 0],
    ['Mateo García', 'Fleet lead · Madrid', 3, 0, 'curly', UMD, 4, 0, 1],
  ];
  const shade = (hex, k) => { const n = parseInt(hex.slice(1), 16); const c = (v) => Math.max(0, Math.min(255, Math.round(v * k))); return `rgb(${c(n >> 16)},${c((n >> 8) & 255)},${c(n & 255)})`; };
  function face(size, k) {
    const [, , sk, hc, st, sh, bg, gl, bd] = CAST[((k % CAST.length) + CAST.length) % CAST.length];
    const s = SKIN[sk], hr = HAIR[hc], id = `ycf${++uid}`;
    const back = { long: 'M27 46 Q25 18 50 17 Q75 18 73 46 L77 88 Q50 98 23 88 Z', bob: 'M28 45 Q27 19 50 18 Q73 19 72 45 L74 67 Q50 74 26 67 Z' }[st] || '';
    const front = {
      short: 'M32 44 Q30 21 50 20 Q70 21 68 44 Q67 32 58 29 Q48 33 38 30 Q33 34 32 44 Z',
      side: 'M32 45 Q29 20 52 19 Q72 21 68 42 Q64 29 46 30 Q38 31 32 45 Z',
      buzz: 'M33 40 Q33 23 50 23 Q67 23 67 40 Q60 30 50 30 Q40 30 33 40 Z',
      long: 'M32 47 Q31 21 50 20 Q69 21 68 47 Q65 30 50 29 Q37 30 34 41 Z',
      bob: 'M32 46 Q31 21 50 20 Q69 21 68 46 Q66 31 47 30 Q37 32 32 46 Z',
      bun: 'M32 43 Q31 22 50 21 Q69 22 68 43 Q64 30 50 29 Q36 30 32 43 Z',
      curly: '', bald: '',
    }[st];
    let curls = '';
    if (st === 'curly') for (let i = 0; i < 9; i++) { const a = Math.PI * (1.05 + i * 0.11); curls += `<circle cx="${(50 + Math.cos(a) * 19).toFixed(1)}" cy="${(39 + Math.sin(a) * 19).toFixed(1)}" r="7.2" fill="${hr}"/>`; }
    const bun = st === 'bun' ? `<circle cx="50" cy="16" r="8" fill="${hr}"/>` : '';
    const glasses = gl ? `<g fill="none" stroke="${INK}" stroke-width="1.7"><rect x="37.5" y="41" width="11" height="9" rx="3.5"/><rect x="51.5" y="41" width="11" height="9" rx="3.5"/><path d="M48.5 45 L51.5 45"/></g>` : '';
    const beard = bd ? `<path d="M33 47 Q34 67 50 68 Q66 67 67 47 Q64 59 50 60 Q36 59 33 47 Z" fill="${hr}"/>` : '';
    return `<svg width="${size}" height="${size}" viewBox="0 0 100 100" style="display:block"><defs><clipPath id="${id}"><circle cx="50" cy="50" r="50"/></clipPath></defs><g clip-path="url(#${id})">`
      + `<rect width="100" height="100" fill="${BG[bg]}"/>${back ? `<path d="${back}" fill="${hr}"/>` : ''}`
      + `<path d="M6 106 Q9 76 36 70 L64 70 Q91 76 94 106 Z" fill="${sh}"/><path d="M41 70 L50 81 L59 70 Z" fill="${shade(s, 0.9)}"/>`
      + `<rect x="42" y="55" width="16" height="17" rx="6" fill="${shade(s, 0.86)}"/>`
      + `<ellipse cx="32.5" cy="47" rx="3.6" ry="5.2" fill="${shade(s, 0.95)}"/><ellipse cx="67.5" cy="47" rx="3.6" ry="5.2" fill="${shade(s, 0.95)}"/>`
      + `<ellipse cx="50" cy="44" rx="17.5" ry="21" fill="${s}"/>${bun}${curls}${front ? `<path d="${front}" fill="${hr}"/>` : ''}${beard}`
      + `<path d="M40 40.5 Q43.5 39 47 40.5 M53 40.5 Q56.5 39 60 40.5" fill="none" stroke="${shade(hr === HAIR[3] ? '#8C5A2E' : hr, 1)}" stroke-width="1.7" stroke-linecap="round"/>`
      + `<circle cx="43.5" cy="45.5" r="1.9" fill="#2A211C"/><circle cx="56.5" cy="45.5" r="1.9" fill="#2A211C"/>`
      + `<path d="M45 55 Q50 59 55 55" fill="none" stroke="${shade(s, 0.62)}" stroke-width="1.9" stroke-linecap="round"/>${glasses}</g></svg>`;
  }
  // Portraits photo (Unsplash, voir assets/img/people/CREDITS.txt) associés à la distribution.
  const PH = [0, 3, 2, 7, 9, 10, 6, 22, 4, 5, 16, 15, 21, 20, 13, 1];
  const img = (k) => `<img src="../assets/img/people/p${String(PH[((k % PH.length) + PH.length) % PH.length]).padStart(2, '0')}.jpg" style="width:100%;height:100%;object-fit:cover;display:block">`;
  window.ycFace = (d, k) => `<div style="width:${d}px;height:${d}px;border-radius:50%;overflow:hidden">${img(k)}</div>`;
  const person = (k) => CAST[((k % CAST.length) + CAST.length) % CAST.length];
  const faceEl = (p, x, y, d, k, ring = null) => el(p, { left: x + 'px', top: y + 'px', width: d + 'px', height: d + 'px', borderRadius: '50%', overflow: 'hidden', boxShadow: ring ? `0 0 0 ${Math.max(2, d * 0.06)}px ${ring}` : 'none' }, img(k));
  // Les avatars à initiales du film deviennent des portraits (même emplacement, même taille).
  U.avatar = (p, x, y, d, ini) => faceEl(p, x, y, d, [...ini].reduce((a, c) => a + c.charCodeAt(0), 0));

  // ---------- Camion (vue de côté) ----------
  const truck = (w, col = UM, cab = INK) => `<svg width="${w}" height="${w * 0.5}" viewBox="0 0 64 32" style="display:block"><rect x="1" y="5" width="40" height="19" rx="3" fill="${col}"/><path d="M43 10 H54 L61 18 V24 H43 Z" fill="${cab}"/><path d="M46 12.5 H53 L57.5 17.5 H46 Z" fill="${MIST}"/><circle cx="12" cy="26" r="4.6" fill="${INK}"/><circle cx="12" cy="26" r="1.8" fill="${STONE}"/><circle cx="32" cy="26" r="4.6" fill="${INK}"/><circle cx="32" cy="26" r="1.8" fill="${STONE}"/><circle cx="52" cy="26" r="4.6" fill="${INK}"/><circle cx="52" cy="26" r="1.8" fill="${STONE}"/></svg>`;
  window.ycTruck = truck;

  // ---------- Petites briques ----------
  const pill = (t, k, fs = 11) => `<span style="display:inline-flex;align-items:center;height:${fs * 2}px;padding:0 ${fs * 0.8}px;border-radius:${fs}px;background:${k[0]};color:${k[1]};font-size:${fs}px;font-weight:700;white-space:nowrap">${t}</span>`;
  const bar = (v, c, w, hh = 7, bg = '#ECE8DF') => `<span style="display:block;width:${w}px;height:${hh}px;border-radius:${hh / 2}px;background:${bg};overflow:hidden;flex:none"><span style="display:block;width:${v * 100}%;height:100%;background:${c};border-radius:${hh / 2}px"></span></span>`;
  // Point sur un tracé SVG (polyligne) à la fraction u.
  function along(pts, u) {
    let L = 0; const seg = [];
    for (let i = 1; i < pts.length; i++) { const d = Math.hypot(pts[i][0] - pts[i - 1][0], pts[i][1] - pts[i - 1][1]); seg.push(d); L += d; }
    let t = clamp(u) * L;
    for (let i = 0; i < seg.length; i++) {
      if (t <= seg[i] || i === seg.length - 1) { const k = seg[i] ? t / seg[i] : 0; const a = pts[i], b = pts[i + 1]; return [lerp(a[0], b[0], k), lerp(a[1], b[1], k), Math.atan2(b[1] - a[1], b[0] - a[0])]; }
      t -= seg[i];
    }
    return pts[0];
  }
  const poly = (pts) => 'M' + pts.map((p) => p.join(' ')).join(' L');

  // ---------- Mini dashboards (280 × 190) ----------
  function base(parent, dark, title, ic, accent) {
    const t = el(parent, { left: '0', top: '0', width: '280px', height: '190px', borderRadius: '9px', overflow: 'hidden', background: dark ? INK2 : '#FFFFFF', border: dark ? '1px solid rgba(245,242,234,0.10)' : '1px solid #E7E6E2', boxShadow: '0 18px 48px rgba(11,11,15,0.30)', transformOrigin: '0 0', fontFamily: 'DM Sans', color: dark ? STONE : '#111113' });
    el(t, { left: '14px', top: '12px', display: 'flex', alignItems: 'center', gap: '7px', fontSize: '12px', fontWeight: '600', color: dark ? STONE : '#111113' }, `${icon(ic, 13, 2, dark ? '#8F8C86' : '#9A9AA0')}${title}`);
    el(t, { right: '12px', top: '10px' }, icon('ellipsis', 15, 2, '#9A9AA0'));
    return t;
  }
  const big = (t, x, y, v, fs = 24) => el(t, { left: x + 'px', top: y + 3 + 'px', fontFamily: 'DM Sans', fontSize: fs + 'px', fontWeight: '700', letterSpacing: '-0.02em' }, v);
  const MINI = [
    // 0. Équipe : nouveaux arrivants en portraits
    (t, d) => {
      big(t, 14, 36, '18'); el(t, { left: '56px', top: '46px', fontSize: '12px', color: d ? '#B5B0A5' : MUTED }, 'arrivées ce mois-ci');
      [0, 4, 6, 1, 10].forEach((k, i) => faceEl(t, 14 + i * 31, 82, 42, k, d ? INK2 : PAPER));
      el(t, { left: '172px', top: '82px', width: '42px', height: '42px', borderRadius: '21px', background: d ? '#2A2A31' : '#ECE8DF', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '13px', fontWeight: '700' }, '+13');
      el(t, { left: '14px', top: '144px', width: '252px', fontSize: '11.5px', fontWeight: '600', color: d ? '#B5B0A5' : MUTED, display: 'flex', justifyContent: 'space-between' }, '<span>Prêts dès le jour J</span><span style="color:' + (d ? STONE : INK) + '">94%</span>');
      el(t, { left: '14px', top: '164px' }, bar(0.94, UM, 252, 7, d ? '#2A2A31' : '#ECE8DF'));
    },
    // 1. Flotte en direct : carte, itinéraire, camion en retard
    (t, d) => {
      const m = el(t, { left: '12px', top: '40px', width: '256px', height: '138px', borderRadius: '11px', background: d ? '#1E1E25' : '#EEF1F4', overflow: 'hidden' });
      const R = [[14, 112], [70, 92], [118, 60], [176, 50], [238, 22]];
      m.innerHTML = `<svg width="256" height="138" viewBox="0 0 256 138"><path d="M0 128 Q60 104 120 116 T256 96" stroke="${d ? '#2E2E36' : '#DDE2E7'}" stroke-width="9" fill="none"/><path d="M30 0 Q60 70 40 138" stroke="${d ? '#2E2E36' : '#DDE2E7'}" stroke-width="6" fill="none"/><path d="${poly(R)}" stroke="${UM}" stroke-width="3.5" fill="none" stroke-dasharray="7 5" stroke-linecap="round"/>${R.map(([x, y], i) => (i === 0 || i === R.length - 1 ? `<circle cx="${x}" cy="${y}" r="5" fill="${i ? INK : UM}" stroke="#fff" stroke-width="2"/>` : '')).join('')}</svg>`;
      const [x, y] = along(R, 0.55);
      el(m, { left: x - 15 + 'px', top: y - 15 + 'px', width: '30px', height: '30px', borderRadius: '15px', background: '#fff', boxShadow: `0 0 0 4px ${BAD[1]}44`, display: 'flex', alignItems: 'center', justifyContent: 'center' }, truck(22, BAD[1]));
      el(m, { left: x + 18 + 'px', top: y - 30 + 'px', padding: '4px 7px', borderRadius: '7px', background: INK, color: STONE, fontSize: '10px', fontWeight: '700', whiteSpace: 'nowrap' }, `TR-2041 · <span style="color:#FF8A8D">+35 min</span>`);
      el(t, { right: '34px', top: '13px', fontSize: '11px', fontWeight: '700', color: d ? '#B5B0A5' : MUTED }, '38 camions');
    },
    // 2. Stock entrepôt : niveaux par référence et seuil de réassort
    (t, d) => {
      big(t, 14, 36, '3', 26); el(t, { left: '36px', top: '46px', fontSize: '12px', color: BAD[1], fontWeight: '700' }, 'réf. sous le seuil');
      const V = [0.82, 0.64, 0.22, 0.71, 0.18, 0.9, 0.3, 0.55], th = 0.34;
      V.forEach((v, i) => { const hh = 92 * v; el(t, { left: 16 + i * 31 + 'px', top: 172 - hh + 'px', width: '22px', height: hh + 'px', borderRadius: '5px 5px 2px 2px', background: v < th ? BAD[1] : i % 3 ? UMD : UM, opacity: v < th ? '1' : '0.9' }); });
      el(t, { left: '12px', top: 172 - 92 * th + 'px', width: '256px', height: '0', borderTop: `2px dashed ${d ? '#B5B0A5' : INK}` });
      el(t, { right: '14px', top: 152 - 92 * th + 'px', fontSize: '9.5px', fontWeight: '700', color: d ? '#B5B0A5' : MUTED }, 'seuil');
    },
    // 3. Santé des lignes de production
    (t, d) => {
      big(t, 14, 34, '87,4 %'); el(t, { left: '112px', top: '45px', fontSize: '12px', color: d ? '#B5B0A5' : MUTED }, 'rendement global');
      const r = seeded(31);
      for (let row = 0; row < 3; row++) {
        el(t, { left: '14px', top: 88 + row * 31 + 'px', fontSize: '10.5px', fontWeight: '700', color: d ? '#B5B0A5' : MUTED }, ['Presse', 'Soudure', 'Peinture'][row]);
        for (let c = 0; c < 9; c++) { const v = r(); const col = row === 1 && c === 6 ? BAD[1] : v < 0.14 ? WARN[1] : '#2FB36D'; el(t, { left: 58 + c * 23 + 'px', top: 84 + row * 31 + 'px', width: '19px', height: '22px', borderRadius: '5px', background: col, opacity: col === '#2FB36D' ? String(0.55 + v * 0.45) : '1' }); }
      }
    },
    // 4. Pipeline commercial en entonnoir, avec les responsables
    (t, d) => {
      big(t, 14, 34, '12,6 M€');
      [['Prospect', 1, 8], ['Qualifié', 0.78, 0], ['Proposition', 0.56, 5], ['Gagné', 0.34, 11]].forEach(([l, w, k], i) => {
        el(t, { left: '14px', top: 76 + i * 28 + 'px', width: 170 * w + 'px', height: '22px', borderRadius: '6px', background: i === 3 ? LIME : i ? UMD : UM, opacity: i === 3 ? '1' : String(1 - i * 0.12), color: i === 3 ? INK : '#fff', fontSize: '10.5px', fontWeight: '700', display: 'flex', alignItems: 'center', padding: '0 8px' }, l);
        faceEl(t, 238, 75 + i * 28, 24, k);
        el(t, { left: '192px', top: 80 + i * 28 + 'px', fontSize: '10.5px', fontWeight: '700', color: d ? '#B5B0A5' : MUTED }, ['42', '27', '14', '9'][i]);
      });
    },
    // 5. Planning des équipes (portraits et créneaux)
    (t, d) => {
      'LMMJVSD'.split('').forEach((c, i) => el(t, { left: 62 + i * 29 + 'px', top: '40px', width: '24px', textAlign: 'center', fontSize: '10px', fontWeight: '700', color: d ? '#B5B0A5' : MUTED }, c));
      const G = ['MMEE-OO', 'EEMM-MO', 'OMMEEM-', 'MEO-MME'];
      [7, 13, 2, 15].forEach((k, j) => {
        faceEl(t, 22, 58 + j * 31, 26, k);
        G[j].split('').forEach((s, i) => el(t, { left: 62 + i * 29 + 'px', top: 60 + j * 31 + 'px', width: '24px', height: '22px', borderRadius: '6px', background: s === 'M' ? UM : s === 'E' ? (d ? '#3A3A44' : INK) : s === 'O' ? (d ? '#2A2A31' : '#ECE8DF') : 'transparent', border: s === '-' ? `1.5px dashed ${BAD[1]}` : 'none' }));
      });
    },
    // 6. Livraisons à l'heure (courbe et camion)
    (t, d) => {
      big(t, 14, 34, '96,4 %'); el(t, { left: '112px', top: '45px', fontSize: '12px', color: OK[1], fontWeight: '700' }, '+1,8 pt');
      const pts = [[0, 70], [30, 62], [62, 66], [94, 48], [126, 52], [158, 34], [190, 38], [222, 22]];
      el(t, { left: '14px', top: '84px', width: '252px', height: '96px' }, `<svg width="252" height="96" viewBox="0 0 252 96"><path d="${poly(pts)} L222 96 L0 96 Z" fill="${UM}" opacity="0.12"/><path d="${poly(pts)}" stroke="${UM}" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>`);
      el(t, { left: '218px', top: '84px' }, truck(40, UM));
    },
    // 7. Recrutement : candidats et étapes
    (t, d) => {
      el(t, { right: '34px', top: '13px', fontSize: '11px', fontWeight: '700', color: d ? '#B5B0A5' : MUTED }, '7 postes ouverts');
      [[10, 'Priya Nair', 'Offre', OK], [5, 'Lucas Moreau', 'Entretien', INFO], [14, 'Sofia Alves', 'Présélection', NEU]].forEach(([k, n, s, c], j) => {
        faceEl(t, 14, 44 + j * 47, 36, k);
        el(t, { left: '58px', top: 48 + j * 47 + 'px', fontSize: '12.5px', fontWeight: '700' }, n);
        el(t, { left: '58px', top: 64 + j * 47 + 'px', fontSize: '10.5px', color: d ? '#B5B0A5' : MUTED }, ['Data analyst', 'Ingénieur', 'Acheteuse'][j]);
        el(t, { right: '14px', top: 52 + j * 47 + 'px' }, pill(s, c, 10));
      });
    },
    // 8. Émissions par site
    (t, d) => {
      big(t, 14, 34, '−12%'); el(t, { left: '92px', top: '45px', fontSize: '12px', color: d ? '#B5B0A5' : MUTED }, 'CO₂ vs l’an dernier');
      [['Lyon', 0.86], ['Turin', 0.64], ['Leipzig', 0.52], ['Porto', 0.33]].forEach(([n, v], j) => {
        el(t, { left: '14px', top: 84 + j * 25 + 'px', fontSize: '11px', fontWeight: '600', color: d ? '#B5B0A5' : MUTED }, n);
        el(t, { left: '74px', top: 88 + j * 25 + 'px' }, bar(v, j ? UMD : UM, 190, 9, d ? '#2A2A31' : '#ECE8DF'));
      });
    },
    // 9. Support informatique
    (t, d) => {
      big(t, 14, 34, '1,284'); el(t, { left: '102px', top: '45px', fontSize: '12px', color: OK[1], fontWeight: '700' }, '−12 % cette semaine');
      const V = [0.5, 0.62, 0.58, 0.8, 0.7, 0.54, 0.44, 0.38, 0.3];
      V.forEach((v, i) => el(t, { left: 16 + i * 28 + 'px', top: 176 - 78 * v + 'px', width: '18px', height: 78 * v + 'px', borderRadius: '5px 5px 2px 2px', background: i === V.length - 1 ? LIME : UM, opacity: i === V.length - 1 ? '1' : '0.75' }));
    },
    // 10. Notes de frais à valider
    (t, d) => {
      el(t, { right: '34px', top: '13px', fontSize: '11px', fontWeight: '700', color: d ? '#B5B0A5' : MUTED }, '12 à valider');
      [[0, 'Dîner client', '420 €'], [7, 'Train Lyon–Turin', '186 €'], [9, 'Licence logiciel', '1 240 €']].forEach(([k, n, v], j) => {
        const r = el(t, { left: '12px', top: 42 + j * 47 + 'px', width: '256px', height: '40px', borderRadius: '10px', background: d ? '#1E1E25' : '#F3F0E9' });
        faceEl(r, 7, 6, 28, k);
        el(r, { left: '43px', top: '11px', fontSize: '12px', fontWeight: '700' }, n);
        el(r, { left: '150px', top: '11px', fontSize: '12px', fontWeight: '800' }, v);
        el(r, { right: '7px', top: '8px', height: '24px', padding: '0 9px', borderRadius: '12px', background: j ? (d ? '#2A2A31' : '#fff') : LIME, color: INK, fontSize: '10.5px', fontWeight: '700', display: 'flex', alignItems: 'center' }, j ? 'Revoir' : 'Valider');
      });
    },
    // 11. Audits de site
    (t, d) => {
      faceEl(t, 228, 8, 38, 2);
      ['Protections vérifiées', 'Issues de secours dégagées', 'Formations à jour', 'Tri des déchets'].forEach((s, j) => {
        const on = j < 3;
        el(t, { left: '14px', top: 48 + j * 34 + 'px', width: '252px', height: '28px', display: 'flex', alignItems: 'center', gap: '9px', fontSize: '12px', fontWeight: '600' },
          `<span style="width:20px;height:20px;border-radius:6px;flex:none;display:flex;align-items:center;justify-content:center;background:${on ? UM : 'transparent'};border:${on ? 'none' : `2px solid ${d ? '#55534E' : '#D3CEC3'}`}">${on ? icon('check', 13, 3, '#fff') : ''}</span>${s}`);
      });
    },
  ];
  const MINI_META = [['Nouvelles recrues', 'users', UM], ['Flotte · en direct', 'map-pin', INK], ['Stock entrepôt', 'layers', UM], ['Santé des lignes', 'gauge', INK], ['Pipeline commercial', 'target', UM], ['Planning équipes', 'calendar', INK],
    ['Livraisons à l’heure', 'clock', UM], ['Recrutement', 'user-plus', INK], ['CO₂ par site', 'activity', LIME], ['Tickets informatiques', 'ticket', UM], ['Notes de frais', 'receipt', LIME], ['Audits de site', 'clipboard-check', UM]];
  U.dash = function (parent, i, dark = false) {
    const k = ((i % MINI.length) + MINI.length) % MINI.length;
    const [title, ic, ac] = MINI_META[k];
    const t = base(parent, dark, title, ic, dark && ac === INK ? UM : ac);
    MINI[k](t, dark);
    return t;
  };
  U.miniCount = MINI.length;
  U.miniTitle = (k) => MINI_META[k][0];

  // ---------- Grand dashboard A : équipe RH, nouveaux arrivants ----------
  U.bigPeople = function (c) {
    el(c, { left: '44px', top: '36px', fontSize: '15px', fontWeight: '700', letterSpacing: '0.16em', color: UM }, 'PEOPLE · ONBOARDING');
    el(c, { left: '44px', top: '62px', fontFamily: 'Plus Jakarta Sans', fontSize: '44px', fontWeight: '800', letterSpacing: '-0.03em', color: INK }, 'Q4 new joiners');
    el(c, { right: '44px', top: '58px', display: 'flex', gap: '10px' }, pill('12 starting this month', INFO, 14) + pill('3 teams hiring', NEU, 14));
    const ST = [['Ready', OK], ['Laptop pending', WARN], ['Ready', OK], ['Contract to sign', BAD], ['Ready', OK], ['Accounts pending', WARN], ['Ready', OK], ['Ready', OK]];
    const PR = [0.92, 0.66, 0.84, 0.3, 1, 0.58, 0.88, 0.74];
    const cards = [0, 1, 2, 3, 4, 5, 6, 10].map((k, i) => {
      const [n, r] = person(k);
      const x = 44 + (i % 4) * 186, y = 140 + Math.floor(i / 4) * 236;
      const cd = el(c, { left: x + 'px', top: y + 'px', width: '172px', height: '222px', borderRadius: '18px', background: '#fff', border: `1px solid ${LINE}` });
      faceEl(cd, 46, 18, 80, k, i === 0 ? UM : null);
      el(cd, { left: '0', width: '172px', top: '108px', textAlign: 'center', fontSize: '16px', fontWeight: '700', color: INK }, n);
      el(cd, { left: '0', width: '172px', top: '130px', textAlign: 'center', fontSize: '12.5px', color: MUTED }, r);
      el(cd, { left: '0', width: '172px', top: '156px', display: 'flex', justifyContent: 'center' }, pill(ST[i][0], ST[i][1], 11));
      const pr = el(cd, { left: '18px', top: '196px', width: '136px', height: '7px', borderRadius: '4px', background: '#ECE8DF', overflow: 'hidden' });
      const f = el(pr, { left: '0', top: '0', bottom: '0', width: '136px', borderRadius: '4px', background: PR[i] === 1 ? '#2FB36D' : UM, transformOrigin: '0 50%' });
      return { cd, f, v: PR[i] };
    });
    // Fiche de la première arrivée : semaine d'accueil et parrain.
    const tk = el(c, { left: '800px', top: '140px', width: '416px', height: '472px', borderRadius: '20px', background: INK, color: STONE });
    faceEl(tk, 26, 24, 64, 0, LIME);
    el(tk, { left: '106px', top: '30px', fontSize: '20px', fontWeight: '700' }, 'Sarah Martin');
    el(tk, { left: '106px', top: '58px', fontSize: '14px', color: '#B5B0A5' }, 'Starts Monday 3 November');
    el(tk, { left: '26px', top: '112px', fontSize: '13px', fontWeight: '700', letterSpacing: '0.14em', color: '#8F8C86' }, 'WEEK ONE');
    const checks = ['Contract signed', 'Laptop delivered', 'Accounts created', 'Team lunch booked', 'Training plan shared'].map((t, i) => {
      const r = el(tk, { left: '26px', top: 140 + i * 50 + 'px', width: '364px', height: '40px', display: 'flex', alignItems: 'center', gap: '14px', fontSize: '16px', color: STONE });
      const box = h('span', '', r); Object.assign(box.style, { width: '26px', height: '26px', borderRadius: '8px', border: '2px solid #55534E', display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none' });
      const tick = h('span', '', box, icon('check', 17, 3, INK)); tick.style.display = 'flex';
      h('span', '', r, t);
      return { box, tick };
    });
    const bd = el(tk, { left: '26px', top: '402px', width: '364px', height: '50px', borderRadius: '14px', background: '#1E1E25', display: 'flex', alignItems: 'center', gap: '12px', padding: '0 12px', fontSize: '14px', color: '#B5B0A5' });
    bd.appendChild(faceEl(h('div'), 0, 0, 32, 1)); bd.lastChild.style.position = 'relative';
    h('span', '', bd, 'Buddy · <b style="color:#F5F2EA">Tom Becker</b>');
    return (T, t0) => {
      cards.forEach((k, i) => { const p = P(T, t0 + i * 0.05, 0.6, Ease.outQuint); S(k.cd, { y: (1 - p) * 46, o: clamp(p * 1.6) }); S(k.f, { sx: Math.max(0.001, k.v * P(T, t0 + 0.2 + i * 0.05, 1.0, Ease.outCubic)) }); });
      checks.forEach((k, i) => { const on = T > t0 + 0.35 + i * 0.17; k.box.style.background = on ? LIME : 'transparent'; k.box.style.borderColor = on ? LIME : '#55534E'; S(k.tick, { s: on ? E.spring(T - t0 - 0.35 - i * 0.17, 300, 14) : 0.001 }); });
      S(tk, { x: (1 - P(T, t0 + 0.1, 0.7, Ease.outQuint)) * 60, o: P(T, t0 + 0.1, 0.4) });
    };
  };

  // ---------- Grand dashboard B : tour de contrôle supply chain, camions en direct ----------
  U.bigFleet = function (c) {
    el(c, { left: '48px', top: '38px', fontSize: '15px', fontWeight: '700', letterSpacing: '0.16em', color: UM }, 'SUPPLY CHAIN · LIVE');
    el(c, { left: '48px', top: '64px', fontFamily: 'Plus Jakarta Sans', fontSize: '44px', fontWeight: '800', letterSpacing: '-0.03em', color: INK }, 'Deliveries today');
    el(c, { right: '48px', top: '70px', display: 'flex', gap: '10px' }, pill('38 trucks on the road', INFO, 14) + pill('4 delayed', BAD, 14));
    // Carte stylisée : terre, mer, autoroutes, villes.
    const W = 790, Hh = 470;
    const map = el(c, { left: '48px', top: '140px', width: W + 'px', height: Hh + 'px', borderRadius: '22px', overflow: 'hidden', background: '#ECEFF1', border: `1px solid ${LINE}` });
    const CITY = { Paris: [190, 80], Lyon: [300, 250], Marseille: [330, 400], Milan: [540, 236], Turin: [468, 286], Frankfurt: [500, 60], Munich: [660, 110], Barcelona: [150, 430], Geneva: [380, 220] };
    const ROUTES = [
      { id: 'TR-2041', pts: ['Paris', 'Lyon', 'Turin', 'Milan'], u: [0.38, 0.47], late: 35, from: 'Paris', to: 'Milan' },
      { id: 'TR-1187', pts: ['Frankfurt', 'Munich'], u: [0.3, 0.58], late: 0, from: 'Frankfurt', to: 'Munich' },
      { id: 'TR-3302', pts: ['Barcelona', 'Marseille', 'Lyon'], u: [0.66, 0.78], late: 0, from: 'Barcelona', to: 'Lyon' },
      { id: 'TR-0906', pts: ['Lyon', 'Geneva', 'Frankfurt'], u: [0.55, 0.68], late: 12, from: 'Lyon', to: 'Frankfurt' },
    ].map((r) => ({ ...r, P: r.pts.map((n) => CITY[n]) }));
    map.innerHTML = `<svg width="${W}" height="${Hh}" viewBox="0 0 ${W} ${Hh}">`
      + `<path d="M0 ${Hh} L0 410 Q90 380 170 448 Q240 470 300 430 Q350 400 420 420 Q520 380 600 420 Q690 456 ${W} 400 L${W} ${Hh} Z" fill="#DCE6EE"/>`
      + `<path d="M560 470 Q600 400 660 380 Q700 370 740 330" stroke="#DCE6EE" stroke-width="0" fill="none"/>`
      + `<g stroke="#FFFFFF" stroke-width="10" fill="none" stroke-linecap="round" opacity="0.95"><path d="M0 120 Q200 150 420 110 T${W} 150"/><path d="M120 0 Q170 200 260 470"/><path d="M420 0 Q430 200 560 470"/><path d="M0 300 Q300 330 ${W} 260"/></g>`
      + `<g stroke="#E3E0D8" stroke-width="2" fill="none" stroke-dasharray="3 6"><path d="M240 0 Q300 180 230 470"/><path d="M600 0 Q560 200 640 470"/></g>`
      + ROUTES.map((r) => `<path d="${poly(r.P)}" stroke="${r.late > 20 ? BAD[1] : UM}" stroke-width="5" fill="none" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>`).join('')
      + Object.entries(CITY).map(([n, [x, y]]) => `<circle cx="${x}" cy="${y}" r="7" fill="#fff" stroke="${INK}" stroke-width="3"/><text x="${x + 12}" y="${y - 10}" font-family="DM Sans" font-size="15" font-weight="700" fill="${INK}">${n}</text>`).join('')
      + `</svg>`;
    // Bouchon sur l'itinéraire du camion en retard.
    const jam = el(map, { left: '342px', top: '246px', width: '34px', height: '34px', borderRadius: '17px', background: BAD[1], display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: `0 0 0 6px ${BAD[1]}33` }, icon('circle-alert', 20, 2.4, '#fff'));
    const pins = ROUTES.map((r) => {
      const late = r.late > 20;
      const pin = el(map, { left: '0', top: '0', width: '48px', height: '48px', borderRadius: '24px', background: '#fff', boxShadow: `0 6px 18px rgba(11,11,15,0.25), 0 0 0 ${late ? 5 : 3}px ${late ? BAD[1] : UM}`, display: 'flex', alignItems: 'center', justifyContent: 'center' }, truck(34, late ? BAD[1] : UM));
      const tag = el(map, { left: '0', top: '0', padding: '6px 10px', borderRadius: '10px', background: INK, color: STONE, fontSize: '13px', fontWeight: '700', whiteSpace: 'nowrap' }, `${r.id} · <span class="lt" style="color:${late ? '#FF8A8D' : r.late ? '#FFC46B' : LIME}">${r.late ? '+' + r.late + ' min' : 'On time'}</span>`);
      return { r, pin, tag };
    });
    const alert = el(map, { left: '466px', top: '360px', width: '300px', height: '86px', borderRadius: '16px', background: '#fff', boxShadow: '0 16px 40px rgba(11,11,15,0.18)', borderLeft: `6px solid ${BAD[1]}` });
    el(alert, { left: '18px', top: '14px', fontSize: '15px', fontWeight: '700', color: INK }, 'Traffic on A43 near Lyon');
    el(alert, { left: '18px', top: '40px', fontSize: '13px', color: MUTED, width: '270px', lineHeight: '1.35' }, 'TR-2041 rerouted via Geneva · customer notified');
    // Liste des expéditions.
    const L = el(c, { left: '862px', top: '140px', width: '410px', height: '470px', borderRadius: '22px', background: '#fff', border: `1px solid ${LINE}` });
    el(L, { left: '24px', top: '20px', fontSize: '18px', fontWeight: '700', color: INK }, 'Shipments');
    el(L, { right: '24px', top: '22px', fontSize: '13px', fontWeight: '700', color: MUTED }, 'ETA');
    const ROWS = [['TR-2041', 'Paris → Milan', '15:05', 35], ['TR-1187', 'Frankfurt → Munich', '11:40', 0], ['TR-3302', 'Barcelona → Lyon', '13:15', 0], ['TR-0906', 'Lyon → Frankfurt', '17:52', 12], ['TR-4410', 'Turin → Geneva', '10:20', 0], ['TR-2275', 'Munich → Milan', '18:30', 0]];
    const rows = ROWS.map(([id, rt, eta, late], j) => {
      const r = el(L, { left: '14px', top: 60 + j * 66 + 'px', width: '382px', height: '58px', borderRadius: '14px', background: j === 0 ? '#FDF0F0' : 'transparent' });
      el(r, { left: '10px', top: '13px' }, truck(42, late > 20 ? BAD[1] : late ? '#E08A00' : UM));
      el(r, { left: '64px', top: '9px', fontSize: '15px', fontWeight: '700', color: INK }, id);
      el(r, { left: '64px', top: '31px', fontSize: '12.5px', color: MUTED }, rt);
      el(r, { right: '12px', top: '9px', fontSize: '15px', fontWeight: '700', color: INK, textAlign: 'right' }, eta);
      el(r, { right: '12px', top: '32px' }, late ? pill('+' + late + ' min', late > 20 ? BAD : WARN, 10) : pill('On time', OK, 10));
      return r;
    });
    return (T, t0) => {
      const u = T - t0;
      pins.forEach(({ r, pin, tag }, i) => {
        const [x, y] = along(r.P, lerp(r.u[0], r.u[1], clamp(u / 1.8)));
        S(pin, { x: x - 24, y: y - 24, s: Math.max(0.001, P(T, t0 + 0.1 + i * 0.08, 0.5, Ease.outBack)) });
        S(tag, { x: x + 30, y: y - 46, o: P(T, t0 + 0.3 + i * 0.08, 0.4) });
      });
      const lt = pins[0].tag.querySelector('.lt');
      lt.textContent = '+' + Math.round(lerp(18, 35, P(T, t0 + 0.2, 1.0))) + ' min';
      S(jam, { s: 1 + 0.12 * Math.sin(T * 8), o: P(T, t0 + 0.2, 0.3) });
      const ap = P(T, t0 + 0.55, 0.5, Ease.outBack);
      S(alert, { y: (1 - ap) * -30, o: clamp(ap) });
      rows.forEach((r, j) => { const p = P(T, t0 + 0.15 + j * 0.06, 0.5, Ease.outQuint); S(r, { x: (1 - p) * 40, o: p }); });
    };
  };

  // ---------- Grand dashboard C : maintenance de 42 sites ----------
  U.bigPlants = function (c) {
    el(c, { left: '48px', top: '38px', fontSize: '15px', fontWeight: '700', letterSpacing: '0.16em', color: UM }, 'OPERATIONS · 42 SITES');
    el(c, { left: '48px', top: '64px', fontFamily: 'Plus Jakarta Sans', fontSize: '44px', fontWeight: '800', letterSpacing: '-0.03em', color: INK }, 'Plant maintenance, every site');
    el(c, { right: '48px', top: '72px', display: 'flex', gap: '10px' }, pill('1,860 machines', INFO, 14) + pill('Live sensors', OK, 14));
    const K = [['Overall OEE', 87.4, '%', '+2.1 pts', 'gauge'], ['Unplanned downtime', 3.2, ' h', '−41%', 'timer'], ['Open work orders', 23, '', '6 urgent', 'clipboard-check'], ['Spare parts in stock', 98, '%', 'on target', 'layers']];
    const kp = K.map(([l, v, suf, d, ic], i) => {
      const k = el(c, { left: 48 + i * 330 + 'px', top: '146px', width: '306px', height: '112px', borderRadius: '18px', background: i === 0 ? INK : '#fff', color: i === 0 ? STONE : INK, border: i === 0 ? 'none' : `1px solid ${LINE}` });
      el(k, { left: '20px', top: '16px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '15px', fontWeight: '600', color: i === 0 ? '#B5B0A5' : MUTED }, `${icon(ic, 18, 2.2, i === 0 ? LIME : UM)}${l}`);
      const n = el(k, { left: '20px', top: '50px', fontFamily: 'Plus Jakarta Sans', fontSize: '36px', fontWeight: '800', letterSpacing: '-0.02em' }, '0');
      el(k, { right: '18px', top: '64px', fontSize: '14px', fontWeight: '700', color: d.includes('urgent') ? BAD[1] : i === 0 ? LIME : OK[1] }, d);
      return { n, v, suf };
    });
    // Carte de chaleur : santé machines par site et par ligne.
    const hm = el(c, { left: '48px', top: '282px', width: '880px', height: '416px', borderRadius: '20px', background: '#fff', border: `1px solid ${LINE}` });
    el(hm, { left: '24px', top: '20px', fontSize: '18px', fontWeight: '700', color: INK }, 'Machine health by site');
    el(hm, { right: '24px', top: '22px', display: 'flex', gap: '16px', fontSize: '12.5px', fontWeight: '600', color: MUTED }, ['#2FB36D', 'Healthy', WARN[1], 'Watch', BAD[1], 'Failure risk'].reduce((s, v, i, A) => (i % 2 ? s : s + `<span style="display:flex;align-items:center;gap:6px"><span style="width:11px;height:11px;border-radius:3px;background:${v}"></span>${A[i + 1]}</span>`), ''));
    const SITES = ['Lyon', 'Turin', 'Leipzig', 'Bilbao', 'Łódź', 'Ghent', 'Brno', 'Porto'];
    const r = seeded(77);
    const cells = [];
    SITES.forEach((s, j) => {
      el(hm, { left: '24px', top: 70 + j * 42 + 'px', fontSize: '14px', fontWeight: '600', color: '#3A3833' }, s);
      for (let i = 0; i < 16; i++) {
        const v = r();
        const bad = (j === 0 && i === 3) || (j === 4 && i === 11);
        const col = bad ? BAD[1] : v < 0.12 ? WARN[1] : '#2FB36D';
        const e = el(hm, { left: 112 + i * 46 + 'px', top: 66 + j * 42 + 'px', width: '40px', height: '34px', borderRadius: '8px', background: col, opacity: col === '#2FB36D' ? String(0.5 + v * 0.5) : '1' });
        cells.push({ e, i, j, bad });
      }
    });
    // Alertes et techniciens assignés.
    const al = el(c, { left: '952px', top: '282px', width: '400px', height: '416px', borderRadius: '20px', background: '#fff', border: `1px solid ${LINE}` });
    el(al, { left: '24px', top: '20px', fontSize: '18px', fontWeight: '700', color: INK }, 'Alerts · assigned');
    const AL = [[7, 'Press #4 · Lyon', 'Vibration above threshold', 'On site in 12 min', BAD], [11, 'Robot R2 · Łódź', 'Gearbox temperature rising', 'Visit at 14:00', WARN], [5, 'Conveyor C7 · Turin', 'Belt wear 82%', 'Part ordered', NEU]];
    const arows = AL.map(([k, m, why, act, st], j) => {
      const r2 = el(al, { left: '14px', top: 62 + j * 114 + 'px', width: '372px', height: '102px', borderRadius: '16px', background: j === 0 ? '#FDF0F0' : '#F6F4EE' });
      faceEl(r2, 14, 14, 50, k);
      el(r2, { left: '76px', top: '14px', fontSize: '15px', fontWeight: '700', color: INK }, m);
      el(r2, { left: '76px', top: '38px', fontSize: '13px', color: MUTED }, why);
      el(r2, { left: '76px', top: '64px', display: 'flex', gap: '8px', alignItems: 'center', fontSize: '12.5px', fontWeight: '600', color: '#3A3833' }, `${pill(j === 0 ? 'Urgent' : j === 1 ? 'Watch' : 'Planned', st, 10)}<span>${person(k)[0].split(' ')[0]} · ${act}</span>`);
      return r2;
    });
    return (T, t0) => {
      kp.forEach((k, i) => { const p = P(T, t0 + 0.1 + i * 0.07, 1.0, Ease.outCubic); const v = k.v * p; k.n.textContent = (k.v % 1 ? v.toFixed(1) : Math.round(v).toLocaleString('en-US')) + k.suf; });
      cells.forEach((cl) => { const p = P(T, t0 + 0.15 + cl.i * 0.03 + cl.j * 0.02, 0.35, Ease.outBack); S(cl.e, { s: Math.max(0.001, p * (cl.bad ? 1 + 0.08 * Math.sin(T * 9) : 1)) }); });
      arows.forEach((r2, j) => { const p = P(T, t0 + 0.4 + j * 0.12, 0.5, Ease.outQuint); S(r2, { x: (1 - p) * 40, o: p }); });
    };
  };
})();

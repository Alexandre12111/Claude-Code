// Écrans d'applications complètes générées par YouCraft (taille virtuelle 1600 × 1000) : navigation latérale, barre
// supérieure, filtres, KPI avec tendances, graphiques à axes, tableaux denses, flux d'activité et données en direct.
// Chaque écran renvoie { root, update(T, t0) } : tout est calculé à partir du temps T, comme le reste du film.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon, seeded } = E;
  const el = C.el;
  const UM = '#1E3BFF', INK = '#0B0B0F', LIME = '#D7FF3F';
  const TX = '#111113', TX2 = '#6B6B70', TX3 = '#9A9AA0', BD = '#E7E6E2', BG = '#F7F7F5', CARD = '#FFFFFF', SIDE = '#FBFBFA';
  const ST = { ok: ['#E7F6EC', '#1C8C4E'], warn: ['#FDF3E1', '#A8670A'], bad: ['#FCEBEB', '#CF3B3B'], info: ['#EEF0FF', UM], neu: ['#F1F1EF', '#55555A'] };
  const W = 1600, H = 1000, SB = 224, TB = 56;

  // ---------- Briques ----------
  const photo = (k, d, ring) => `<img src="../assets/img/people/p${String(k).padStart(2, '0')}.jpg" style="width:${d}px;height:${d}px;border-radius:50%;object-fit:cover;display:block;flex:none;${ring ? `box-shadow:0 0 0 2px ${ring}` : ''}">`;
  const badge = (t, k, dot = true) => `<span style="display:inline-flex;align-items:center;gap:6px;height:22px;padding:0 8px;border-radius:5px;background:${ST[k][0]};color:${ST[k][1]};font-size:12px;font-weight:600;white-space:nowrap">${dot ? `<span style="width:6px;height:6px;border-radius:3px;background:${ST[k][1]}"></span>` : ''}${t}</span>`;
  const btn = (t, primary, ic) => `<span style="display:inline-flex;align-items:center;gap:7px;height:34px;padding:0 13px;border-radius:7px;font-size:13px;font-weight:600;white-space:nowrap;${primary ? `background:${UM};color:#fff;` : `background:#fff;color:${TX};border:1px solid ${BD};box-shadow:0 1px 1px rgba(0,0,0,0.03);`}">${ic ? icon(ic, 15, 2, primary ? '#fff' : TX2) : ''}${t}</span>`;
  const bar = (v, c, w, hh = 6) => `<span style="display:block;width:${w}px;height:${hh}px;border-radius:${hh / 2}px;background:#EEEEEB;overflow:hidden;flex:none"><span class="bf" style="display:block;width:${(v * 100).toFixed(1)}%;height:100%;background:${c};border-radius:${hh / 2}px"></span></span>`;
  const card = (p, x, y, w, hh, title, right = '') => {
    const c = el(p, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px', background: CARD, border: `1px solid ${BD}`, borderRadius: '10px', boxShadow: '0 1px 2px rgba(16,16,20,0.04)', overflow: 'hidden' });
    if (title) {
      el(c, { left: '18px', top: '15px', fontSize: '14px', fontWeight: '700', color: TX }, title);
      el(c, { right: '16px', top: '12px', display: 'flex', gap: '8px', alignItems: 'center', fontSize: '12.5px', color: TX2 }, right || icon('ellipsis', 16, 2, TX3));
    }
    return c;
  };
  const spark = (vals, w, hh, c) => {
    const mx = Math.max(...vals), mn = Math.min(...vals);
    const pts = vals.map((v, i) => [(i / (vals.length - 1)) * w, hh - 2 - ((v - mn) / (mx - mn || 1)) * (hh - 4)]);
    const d = 'M' + pts.map((p) => p.map((n) => n.toFixed(1)).join(' ')).join(' L');
    return `<svg width="${w}" height="${hh}" viewBox="0 0 ${w} ${hh}" style="display:block"><path d="${d} L${w} ${hh} L0 ${hh} Z" fill="${c}" opacity="0.10"/><path d="${d}" fill="none" stroke="${c}" stroke-width="1.8" stroke-linejoin="round"/></svg>`;
  };
  // Carte KPI : libellé, valeur (animée par update), variation, tendance.
  function kpi(p, x, y, w, label, fmt, v, delta, good, sp, ic) {
    const c = card(p, x, y, w, 104);
    el(c, { left: '18px', top: '16px', display: 'flex', alignItems: 'center', gap: '7px', fontSize: '12.5px', fontWeight: '500', color: TX2 }, `${icon(ic, 14, 2, TX3)}${label}`);
    const n = el(c, { left: '18px', top: '42px', fontSize: '27px', fontWeight: '700', color: TX, letterSpacing: '-0.02em', fontVariantNumeric: 'tabular-nums' }, fmt(v));
    el(c, { left: '18px', top: '78px', fontSize: '12px', fontWeight: '600', color: good ? ST.ok[1] : ST.bad[1] }, `${delta} <span style="color:${TX3};font-weight:500">vs last quarter</span>`);
    el(c, { right: '16px', top: '44px' }, spark(sp, 96, 34, good ? '#1C8C4E' : '#CF3B3B'));
    return { c, n, fmt, v };
  }
  // Tableau : en-tête et lignes de 50 px, colonnes [libellé, largeur, alignement].
  function table(p, x, y, w, cols, rows, rh = 50) {
    const t = el(p, { left: x + 'px', top: y + 'px', width: w + 'px' });
    const head = el(t, { left: '0', top: '0', width: w + 'px', height: '36px', background: '#FAFAF8', borderTop: `1px solid ${BD}`, borderBottom: `1px solid ${BD}` });
    let cx = 18;
    cols.forEach(([l, cw, al]) => { el(head, { left: cx + 'px', top: '10px', width: cw + 'px', textAlign: al || 'left', fontSize: '11.5px', fontWeight: '600', color: TX2, letterSpacing: '0.02em' }, l); cx += cw; });
    const R = rows.map((r, j) => {
      const row = el(t, { left: '0', top: 36 + j * rh + 'px', width: w + 'px', height: rh + 'px', borderBottom: `1px solid #F0EFEC`, background: '#fff' });
      let x2 = 18;
      cols.forEach(([, cw, al], i) => { el(row, { left: x2 + 'px', top: '0', width: cw + 'px', height: rh + 'px', display: 'flex', alignItems: 'center', justifyContent: al === 'right' ? 'flex-end' : 'flex-start', gap: '10px', fontSize: '13px', color: TX, whiteSpace: 'nowrap', overflow: 'hidden' }, r[i]); x2 += cw; });
      return row;
    });
    return { t, R };
  }
  const who = (k, n, sub) => `${photo(k, 30)}<span style="display:flex;flex-direction:column;line-height:1.25"><b style="font-weight:600">${n}</b><span style="color:${TX2};font-size:12px">${sub}</span></span>`;
  const tabs = (p, x, y, list, act = 0) => el(p, { left: x + 'px', top: y + 'px', display: 'flex', gap: '4px' }, list.map((t, i) => `<span style="height:28px;padding:0 11px;border-radius:6px;display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;${i === act ? `background:${TX};color:#fff` : `color:${TX2};background:#F1F1EF`}">${t}</span>`).join(''));

  // Graphique en courbes : grille, axes, séries ; update(u) trace jusqu'à la fraction u, curseur de survol optionnel.
  function lineChart(p, x, y, w, hh, { series, xl, ymin = 0, ymax, yt = 4, fmt = (v) => v, thr = null, area = true }) {
    const pl = 44, pb = 26, cw = w - pl - 8, ch = hh - pb - 8;
    const g = el(p, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px' });
    const sx = (i, n) => pl + (i / (n - 1)) * cw, sy = (v) => 8 + ch - ((v - ymin) / (ymax - ymin)) * ch;
    let s = `<svg width="${w}" height="${hh}" viewBox="0 0 ${w} ${hh}" style="position:absolute;left:0;top:0;overflow:visible">`;
    for (let k = 0; k <= yt; k++) { const v = ymin + ((ymax - ymin) * k) / yt, yy = sy(v); s += `<line x1="${pl}" x2="${w - 8}" y1="${yy}" y2="${yy}" stroke="#EFEEEA" stroke-width="1"/><text x="${pl - 8}" y="${yy + 4}" text-anchor="end" font-family="DM Sans" font-size="11" fill="${TX3}">${fmt(v)}</text>`; }
    xl.forEach((l, i) => { if (l) s += `<text x="${sx(i, xl.length)}" y="${hh - 6}" text-anchor="middle" font-family="DM Sans" font-size="11" fill="${TX3}">${l}</text>`; });
    if (thr !== null) s += `<line x1="${pl}" x2="${w - 8}" y1="${sy(thr)}" y2="${sy(thr)}" stroke="#CF3B3B" stroke-width="1.5" stroke-dasharray="5 4"/><text x="${w - 10}" y="${sy(thr) - 6}" text-anchor="end" font-family="DM Sans" font-size="11" font-weight="600" fill="#CF3B3B">Threshold ${fmt(thr)}</text>`;
    s += '</svg>';
    g.innerHTML = s;
    const paths = series.map(({ v, c, dash }) => {
      const pts = v.map((val, i) => [sx(i, v.length), sy(val)]);
      const sv = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      sv.setAttribute('width', w); sv.setAttribute('height', hh); sv.style.position = 'absolute'; sv.style.left = '0'; sv.style.top = '0'; sv.style.overflow = 'visible';
      g.appendChild(sv);
      return { pts, sv, c, dash };
    });
    const draw = (u) => paths.forEach(({ pts, sv, c, dash }, si) => {
      const n = Math.max(2, Math.round(lerp(2, pts.length, clamp(u))));
      const q = pts.slice(0, n);
      const d = 'M' + q.map((p2) => p2[0].toFixed(1) + ' ' + p2[1].toFixed(1)).join(' L');
      const last = q[q.length - 1];
      sv.innerHTML = (area && si === 0 ? `<path d="${d} L${last[0]} ${8 + ch} L${pl} ${8 + ch} Z" fill="${c}" opacity="0.08"/>` : '') + `<path d="${d}" fill="none" stroke="${c}" stroke-width="${si ? 1.8 : 2.4}" stroke-linejoin="round" stroke-linecap="round" ${dash ? 'stroke-dasharray="5 4"' : ''}/>` + (si === 0 ? `<circle cx="${last[0]}" cy="${last[1]}" r="4.5" fill="#fff" stroke="${c}" stroke-width="2.4"/>` : '');
    });
    return { g, draw, sx, sy, pl, cw, ch };
  }
  function barChart(p, x, y, w, hh, vals, xl, c, ymax, fmt = (v) => v) {
    const pl = 40, pb = 24, cw = w - pl - 6, ch = hh - pb - 6, bw = cw / vals.length;
    const g = el(p, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px' });
    let s = `<svg width="${w}" height="${hh}" style="position:absolute;left:0;top:0">`;
    for (let k = 0; k <= 4; k++) { const yy = 6 + ch - (ch * k) / 4; s += `<line x1="${pl}" x2="${w - 6}" y1="${yy}" y2="${yy}" stroke="#EFEEEA"/><text x="${pl - 8}" y="${yy + 4}" text-anchor="end" font-family="DM Sans" font-size="11" fill="${TX3}">${fmt((ymax * k) / 4)}</text>`; }
    xl.forEach((l, i) => (s += `<text x="${pl + bw * (i + 0.5)}" y="${hh - 5}" text-anchor="middle" font-family="DM Sans" font-size="11" fill="${TX3}">${l}</text>`));
    g.innerHTML = s + '</svg>';
    const B = vals.map((v, i) => el(g, { left: pl + bw * i + bw * 0.2 + 'px', top: 6 + ch - (v / ymax) * ch + 'px', width: bw * 0.6 + 'px', height: (v / ymax) * ch + 'px', borderRadius: '3px 3px 0 0', background: Array.isArray(c) ? c[i] : c, transformOrigin: '50% 100%' }));
    return (u) => B.forEach((b, i) => S(b, { sy: Math.max(0.001, P(u, i * 0.05, 0.6, Ease.outCubic)) }));
  }

  // ---------- Coque d'application ----------
  function shell(parent, { app, mark, nav, active, crumbs, title, sub, actions, org = 'Northwind Group', user = [19, 'Claire Dubois'] }) {
    const root = el(parent, { left: '0', top: '0', width: W + 'px', height: H + 'px', background: BG, overflow: 'hidden', fontFamily: 'DM Sans', color: TX, transformOrigin: '0 0' });
    const sb = el(root, { left: '0', top: '0', width: SB + 'px', height: H + 'px', background: SIDE, borderRight: `1px solid ${BD}` });
    el(sb, { left: '18px', top: '16px', display: 'flex', alignItems: 'center', gap: '10px' }, `<span style="width:30px;height:30px;border-radius:8px;background:${mark};display:flex;align-items:center;justify-content:center">${icon(app[1], 17, 2.2, mark === LIME ? INK : '#fff')}</span><span style="display:flex;flex-direction:column;line-height:1.2"><b style="font-size:14px">${app[0]}</b><span style="font-size:11.5px;color:${TX2}">${org} ▾</span></span>`);
    el(sb, { left: '14px', top: '66px', width: '196px', height: '32px', borderRadius: '7px', background: '#fff', border: `1px solid ${BD}`, display: 'flex', alignItems: 'center', gap: '8px', padding: '0 10px', fontSize: '12.5px', color: TX3 }, `${icon('search', 14, 2, TX3)}Search<span style="margin-left:auto;font-size:11px;border:1px solid ${BD};border-radius:4px;padding:0 5px">⌘K</span>`);
    nav.forEach(([ic, l, b], i) => {
      const on = i === active;
      el(sb, { left: '10px', top: 112 + i * 34 + 'px', width: '204px', height: '30px', borderRadius: '7px', background: on ? '#EEF0FF' : 'transparent', display: 'flex', alignItems: 'center', gap: '10px', padding: '0 10px', fontSize: '13px', fontWeight: on ? '600' : '500', color: on ? UM : '#3C3C42' },
        `${icon(ic, 16, 2, on ? UM : TX2)}${l}${b ? `<span style="margin-left:auto;min-width:20px;height:18px;padding:0 6px;border-radius:9px;background:${b[0] === '!' ? '#FCEBEB' : '#ECECE9'};color:${b[0] === '!' ? '#CF3B3B' : TX2};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center">${b.replace('!', '')}</span>` : ''}`);
    });
    const ny = 112 + nav.length * 34 + 22;
    el(sb, { left: '20px', top: ny + 'px', fontSize: '11px', fontWeight: '600', color: TX3, letterSpacing: '0.06em' }, 'SAVED VIEWS');
    ['My team', 'This week', 'Needs attention'].forEach((t, i) => el(sb, { left: '20px', top: ny + 26 + i * 30 + 'px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px', color: '#3C3C42' }, `<span style="width:8px;height:8px;border-radius:2px;background:${['#1E3BFF', '#1C8C4E', '#CF3B3B'][i]}"></span>${t}`));
    el(sb, { left: '14px', bottom: '14px', width: '196px', height: '44px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px', fontWeight: '600' }, `${photo(user[0], 30)}<span style="display:flex;flex-direction:column;line-height:1.2">${user[1]}<span style="font-size:11.5px;color:${TX2};font-weight:500">Admin</span></span>`);
    const top = el(root, { left: SB + 'px', top: '0', width: W - SB + 'px', height: TB + 'px', background: '#fff', borderBottom: `1px solid ${BD}` });
    el(top, { left: '28px', top: '18px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '13px', color: TX2 }, crumbs.map((c, i) => (i === crumbs.length - 1 ? `<b style="color:${TX};font-weight:600">${c}</b>` : c)).join(`<span style="color:${TX3}">/</span>`));
    el(top, { right: '24px', top: '12px', display: 'flex', alignItems: 'center', gap: '16px' }, `<span style="position:relative;display:flex">${icon('bell', 18, 2, TX2)}<span style="position:absolute;right:-2px;top:-2px;width:8px;height:8px;border-radius:4px;background:#CF3B3B;border:2px solid #fff"></span></span>${icon('settings', 18, 2, TX2)}${photo(user[0], 30)}`);
    const main = el(root, { left: SB + 'px', top: TB + 'px', width: W - SB + 'px', height: H - TB + 'px' });
    el(main, { left: '28px', top: '22px', fontSize: '23px', fontWeight: '700', letterSpacing: '-0.02em' }, title);
    const subEl = el(main, { left: '28px', top: '56px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '13px', color: TX2 }, sub);
    el(main, { right: '28px', top: '24px', display: 'flex', gap: '8px' }, actions);
    return { root, main, subEl };
  }
  const CW = W - SB - 56; // largeur utile du contenu (1320)
  const live = (txt) => `<span class="lv" style="width:8px;height:8px;border-radius:4px;background:#1C8C4E;box-shadow:0 0 0 4px rgba(28,140,78,0.15)"></span><span class="lvt">${txt}</span>`;

  // ---------- 1. RH : onboarding des nouveaux arrivants ----------
  function people(parent) {
    const s = shell(parent, { app: ['People Hub', 'users'], mark: UM, active: 2, crumbs: ['People', 'Onboarding', 'Q4 2026'], title: 'Onboarding',
      nav: [['house', 'Home'], ['users', 'Employees'], ['user-plus', 'Onboarding', '9'], ['target', 'Recruiting', '12'], ['calendar', 'Time off'], ['layers', 'Org chart'], ['chart-column', 'Reports'], ['settings', 'Settings']],
      sub: live('Live') + `<span style="color:${TX3}">·</span>48 new hires across 6 departments`,
      actions: btn('Oct 1 – Dec 31, 2026', false, 'calendar') + btn('Filters', false, 'list-checks') + btn('Add new hire', true, 'plus') });
    const m = s.main;
    const K = [
      kpi(m, 28, 92, 318, 'New hires this quarter', (v) => Math.round(v), 48, '+12%', true, [22, 25, 24, 30, 33, 31, 38, 41, 48], 'user-plus'),
      kpi(m, 362, 92, 318, 'Ready on day one', (v) => v.toFixed(1) + '%', 94.2, '+3.1 pts', true, [86, 88, 87, 90, 89, 91, 93, 92, 94], 'circle-check'),
      kpi(m, 696, 92, 318, 'Time to productivity', (v) => v.toFixed(1) + ' days', 18.4, '−2.6 days', true, [26, 25, 24, 23, 22, 21, 20, 19, 18], 'timer'),
      kpi(m, 1030, 92, 318, 'Open onboarding tasks', (v) => Math.round(v), 37, '−14', true, [61, 58, 55, 52, 50, 46, 44, 40, 37], 'list-checks'),
    ];
    const tc = card(m, 28, 216, 900, 712, 'Upcoming starts', `<span style="display:flex;align-items:center;gap:6px;height:28px;padding:0 10px;border:1px solid ${BD};border-radius:6px">${icon('search', 13, 2, TX3)}Search people</span>${btn('Export', false, 'download')}`);
    tabs(tc, 18, 50, ['All <b>48</b>', 'This week <b>9</b>', 'Blocked <b>3</b>', 'Completed <b>21</b>']);
    const ROWS = [
      [0, 'Sarah Martin', 'Consultant', 'Consulting', 'Mon, Nov 3', 5, 'David Cohen', 0.92, '11/12', ['On track', 'ok']],
      [3, 'Tom Becker', 'Data analyst', 'Data & AI', 'Mon, Nov 3', 4, 'Elena Rossi', 0.66, '8/12', ['Laptop shipping', 'warn']],
      [2, 'Inès Duval', 'Project lead', 'Operations', 'Tue, Nov 4', 20, 'Paul Martin', 0.84, '10/12', ['On track', 'ok']],
      [7, 'Marc Petit', 'Tax advisor', 'Finance', 'Wed, Nov 5', 5, 'David Cohen', 0.33, '4/12', ['Contract unsigned', 'bad']],
      [9, 'Amara Okafor', 'HR partner', 'People', 'Mon, Nov 10', 19, 'Claire Dubois', 1, '12/12', ['Ready', 'ok']],
      [10, 'Lucas Moreau', 'Engineer', 'Engineering', 'Mon, Nov 10', 4, 'Elena Rossi', 0.58, '7/12', ['Accounts pending', 'warn']],
      [6, 'Yuki Tanaka', 'Product designer', 'Product', 'Mon, Nov 17', 20, 'Paul Martin', 0.75, '9/12', ['On track', 'ok']],
      [16, 'Priya Nair', 'Analyst', 'Data & AI', 'Mon, Nov 17', 4, 'Elena Rossi', 0.5, '6/12', ['On track', 'ok']],
      [15, 'Jonas Weber', 'Ops manager', 'Operations', 'Mon, Dec 1', 20, 'Paul Martin', 0.25, '3/12', ['Not started', 'neu']],
      [13, 'Sofia Alves', 'Buyer', 'Procurement', 'Mon, Dec 1', 5, 'David Cohen', 0.17, '2/12', ['Not started', 'neu']],
      [1, 'Ryan Cole', 'Sales lead', 'Sales', 'Mon, Dec 8', 19, 'Claire Dubois', 0.08, '1/12', ['Not started', 'neu']],
    ];
    const T1 = table(tc, 0, 92, 900, [['EMPLOYEE', 196], ['DEPARTMENT', 118], ['START DATE', 98], ['MANAGER', 152], ['ONBOARDING', 148], ['STATUS', 160]],
      ROWS.map(([k, n, r, d, sd, mk, mn, v, tk, st]) => [who(k, n, r), d, sd, `${photo(mk, 22)}<span>${mn}</span>`, `${bar(v, v === 1 ? '#1C8C4E' : UM, 84)}<span style="color:${TX2};font-size:12px">${tk}</span>`, badge(st[0], st[1])]), 52);
    // Flux d'activité en direct.
    const fc = card(m, 944, 216, 404, 420, 'Live activity', `<span style="display:flex;align-items:center;gap:6px;color:#1C8C4E;font-weight:600">${live('')}Live</span>`);
    const FEED = [[22, '<b>IT</b> shipped a laptop to <b>Tom Becker</b>', 'just now', 'smartphone'], [0, '<b>Sarah Martin</b> completed the security training', '2 min ago', 'graduation-cap'], [5, '<b>David Cohen</b> approved access for <b>Marc Petit</b>', '6 min ago', 'shield-check'], [9, '<b>Amara Okafor</b> is ready for day one', '14 min ago', 'circle-check'], [20, '<b>Paul Martin</b> booked a team lunch for <b>Inès Duval</b>', '22 min ago', 'utensils'], [4, '<b>Elena Rossi</b> assigned a buddy to <b>Lucas Moreau</b>', '31 min ago', 'users']];
    const feed = el(fc, { left: '0', top: '48px', width: '404px', height: '372px', overflow: 'hidden' });
    const F = FEED.map(([k, t, ago, ic], j) => {
      const r = el(feed, { left: '0', top: j * 60 + 'px', width: '404px', height: '60px', borderTop: j ? '1px solid #F0EFEC' : 'none' });
      el(r, { left: '18px', top: '12px' }, photo(k, 34));
      el(r, { left: '62px', top: '11px', width: '320px', fontSize: '13px', lineHeight: '1.35', color: '#2A2A2F' }, t);
      el(r, { left: '62px', top: '33px', fontSize: '11.5px', color: TX3, display: 'flex', alignItems: 'center', gap: '5px' }, `${icon(ic, 12, 2, TX3)}${ago}`);
      return r;
    });
    const dc = card(m, 944, 652, 404, 276, 'Hires by department');
    const bars = barChart(dc, 12, 50, 380, 214, [11, 9, 8, 7, 6, 4, 3], ['Cons.', 'Data', 'Ops', 'Eng.', 'Fin.', 'Sales', 'HR'], [UM, UM, UM, UM, UM, '#B7C2FF', '#B7C2FF'], 12, (v) => Math.round(v));
    const p3 = T1.R[3];
    return {
      root: s.root,
      update(T, t0) {
        const u = T - t0;
        K.forEach((k, i) => { k.n.textContent = k.fmt(k.v * (0.86 + 0.14 * P(T, t0 + i * 0.05, 1.1, Ease.outCubic))); });
        T1.R.forEach((r, j) => { const p = P(T, t0 + 0.05 + j * 0.03, 0.45, Ease.outCubic); S(r, { y: (1 - p) * 12, o: p }); const f = r.querySelector('.bf'); if (f) f.style.transform = `scaleX(${Math.max(0.001, P(T, t0 + 0.2 + j * 0.03, 1.0)).toFixed(3)})`, (f.style.transformOrigin = '0 50%'); });
        // Une nouvelle activité arrive en haut du flux.
        const np = P(T, t0 + 0.7, 0.45, Ease.outCubic);
        F.forEach((r, j) => { r.style.top = (j - 1 + np) * 60 + 'px'; r.style.opacity = j === 0 ? String(np) : '1'; r.style.background = j === 0 ? `rgba(30,59,255,${(0.06 * np * (1 - P(T, t0 + 1.4, 0.6))).toFixed(3)})` : '#fff'; });
        // Contrat signé : la ligne de Marc Petit passe au vert.
        const sg = T > t0 + 1.0;
        p3.children[5].innerHTML = sg ? badge('Contract signed', 'ok') : badge('Contract unsigned', 'bad');
        p3.style.background = sg ? `rgba(28,140,78,${(0.08 * (1 - P(T, t0 + 1.0, 0.8))).toFixed(3)})` : '#fff';
        bars(u);
        s.subEl.querySelector('.lv').style.opacity = String(0.55 + 0.45 * Math.sin(T * 6));
      },
    };
  }

  // ---------- 2. Supply chain : tour de contrôle ----------
  function fleet(parent) {
    const s = shell(parent, { app: ['Control Tower', 'map-pin'], mark: INK, active: 1, crumbs: ['Logistics', 'Live map'], title: 'Live map', user: [21, 'Nadia Haddad'],
      nav: [['layout-dashboard', 'Overview'], ['map-pin', 'Live map'], ['inbox', 'Shipments', '214'], ['circle-alert', 'Exceptions', '!4'], ['building-2', 'Carriers'], ['house', 'Warehouses'], ['chart-column', 'Analytics']],
      sub: live('Live · updated 2 s ago') + `<span style="color:${TX3}">·</span>38 trucks in transit · Europe`,
      actions: btn('Region: Europe', false, 'globe') + btn('Status: In transit', false, 'list-checks') + btn('Share', false, 'external-link') });
    const m = s.main;
    // Carte : terre, mer, frontières, autoroutes, villes, échelle, zoom.
    const MW = 900, MH = 836;
    const mc = el(m, { left: '28px', top: '92px', width: MW + 'px', height: MH + 'px', borderRadius: '10px', overflow: 'hidden', border: `1px solid ${BD}`, background: '#F1F0EB' });
    const CITY = { Paris: [250, 120], Lyon: [390, 420], Marseille: [430, 650], Geneva: [500, 380], Turin: [580, 500], Milan: [680, 450], Frankfurt: [600, 90], Munich: [790, 210], Barcelona: [210, 760], Strasbourg: [580, 210] };
    const R = [
      { id: 'TR-2041', p: ['Paris', 'Lyon', 'Turin', 'Milan'], u: [0.46, 0.5], late: 35 },
      { id: 'TR-1187', p: ['Frankfurt', 'Munich'], u: [0.35, 0.62], late: 0 },
      { id: 'TR-3302', p: ['Barcelona', 'Marseille', 'Lyon'], u: [0.6, 0.72], late: 0 },
      { id: 'TR-0906', p: ['Lyon', 'Geneva', 'Strasbourg', 'Frankfurt'], u: [0.45, 0.55], late: 12 },
      { id: 'TR-4410', p: ['Turin', 'Geneva'], u: [0.3, 0.45], late: 0 },
      { id: 'TR-2275', p: ['Munich', 'Milan'], u: [0.25, 0.4], late: 0 },
      { id: 'TR-5120', p: ['Paris', 'Strasbourg'], u: [0.5, 0.66], late: 0 },
    ].map((r) => ({ ...r, P: r.p.map((n) => CITY[n]) }));
    const poly = (pts) => 'M' + pts.map((q) => q.join(' ')).join(' L');
    mc.innerHTML = `<svg width="${MW}" height="${MH}" viewBox="0 0 ${MW} ${MH}" style="position:absolute;left:0;top:0">`
      + `<path d="M0 ${MH} L0 690 Q60 670 120 700 Q180 740 230 720 Q300 690 360 700 Q420 720 470 690 Q540 650 600 672 Q660 690 720 640 Q780 600 ${MW} 610 L${MW} ${MH} Z" fill="#D6E3EC"/>`
      + `<path d="M0 0 L0 60 Q60 40 110 70 Q150 30 120 0 Z" fill="#D6E3EC"/>`
      + `<g fill="none" stroke="#D9D6CC" stroke-width="1.4" stroke-dasharray="6 5"><path d="M540 0 Q560 160 520 300 Q560 420 640 520 Q700 600 690 680"/><path d="M430 560 Q300 600 160 690"/><path d="M640 260 Q740 280 ${MW} 250"/></g>`
      + `<g fill="none" stroke="#E2DED3" stroke-width="11" stroke-linecap="round"><path d="${poly([CITY.Paris, CITY.Lyon, CITY.Marseille])}"/><path d="${poly([CITY.Lyon, CITY.Turin, CITY.Milan])}"/><path d="${poly([CITY.Frankfurt, CITY.Munich, CITY.Milan])}"/><path d="${poly([CITY.Barcelona, CITY.Marseille])}"/><path d="${poly([CITY.Lyon, CITY.Geneva, CITY.Strasbourg, CITY.Frankfurt])}"/><path d="${poly([CITY.Paris, CITY.Strasbourg])}"/><path d="${poly([CITY.Turin, CITY.Geneva])}"/></g>`
      + `<g fill="none" stroke="#FFFFFF" stroke-width="7" stroke-linecap="round"><path d="${poly([CITY.Paris, CITY.Lyon, CITY.Marseille])}"/><path d="${poly([CITY.Lyon, CITY.Turin, CITY.Milan])}"/><path d="${poly([CITY.Frankfurt, CITY.Munich, CITY.Milan])}"/><path d="${poly([CITY.Barcelona, CITY.Marseille])}"/><path d="${poly([CITY.Lyon, CITY.Geneva, CITY.Strasbourg, CITY.Frankfurt])}"/><path d="${poly([CITY.Paris, CITY.Strasbourg])}"/><path d="${poly([CITY.Turin, CITY.Geneva])}"/></g>`
      + R.map((r) => `<path d="${poly(r.P)}" fill="none" stroke="${r.late > 20 ? '#CF3B3B' : UM}" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" opacity="${r.late > 20 ? 0.9 : 0.55}"/>`).join('')
      + Object.entries(CITY).map(([n, [x, y]]) => `<circle cx="${x}" cy="${y}" r="5" fill="#fff" stroke="#6B6B70" stroke-width="2"/><text x="${x + 10}" y="${y + 4}" font-family="DM Sans" font-size="13" font-weight="600" fill="#3C3C42" stroke="#F1F0EB" stroke-width="3" paint-order="stroke">${n}</text>`).join('')
      + `<g transform="translate(24 ${MH - 30})"><line x1="0" x2="80" y1="0" y2="0" stroke="#55555A" stroke-width="2"/><line x1="0" x2="0" y1="-5" y2="5" stroke="#55555A" stroke-width="2"/><line x1="80" x2="80" y1="-5" y2="5" stroke="#55555A" stroke-width="2"/><text x="90" y="4" font-family="DM Sans" font-size="11" fill="#55555A">100 km</text></g></svg>`;
    el(mc, { right: '16px', top: '16px', width: '34px', height: '70px', borderRadius: '8px', background: '#fff', border: `1px solid ${BD}`, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'space-around', fontSize: '18px', color: TX2, fontWeight: '600' }, '<span>+</span><span style="width:20px;height:1px;background:#E7E6E2"></span><span>−</span>');
    el(mc, { left: '16px', top: '16px', height: '34px', padding: '0 12px', borderRadius: '8px', background: '#fff', border: `1px solid ${BD}`, display: 'flex', alignItems: 'center', gap: '14px', fontSize: '12px', color: TX2, fontWeight: '600' },
      [[UM, 'In transit 34'], ['#CF3B3B', 'Delayed 4'], ['#1C8C4E', 'Delivered today 61']].map(([c, t]) => `<span style="display:flex;align-items:center;gap:6px"><span style="width:9px;height:9px;border-radius:5px;background:${c}"></span>${t}</span>`).join(''));
    // Groupe de camions et incident.
    el(mc, { left: '262px', top: '232px', width: '40px', height: '40px', borderRadius: '20px', background: UM, opacity: '0.92', color: '#fff', fontSize: '14px', fontWeight: '700', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 0 0 6px rgba(30,59,255,0.18)' }, '12');
    const jam = el(mc, { left: '462px', top: '440px', width: '28px', height: '28px', borderRadius: '14px', background: '#CF3B3B', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('circle-alert', 16, 2.4, '#fff'));
    const along = (pts, u) => { let L = 0; const sg = []; for (let i = 1; i < pts.length; i++) { const d = Math.hypot(pts[i][0] - pts[i - 1][0], pts[i][1] - pts[i - 1][1]); sg.push(d); L += d; } let t = clamp(u) * L; for (let i = 0; i < sg.length; i++) { if (t <= sg[i] || i === sg.length - 1) { const k = t / sg[i]; return [lerp(pts[i][0], pts[i + 1][0], k), lerp(pts[i][1], pts[i + 1][1], k), Math.atan2(pts[i + 1][1] - pts[i][1], pts[i + 1][0] - pts[i][0])]; } t -= sg[i]; } return pts[0]; };
    const pins = R.map((r, i) => {
      const late = r.late > 20, col = late ? '#CF3B3B' : r.late ? '#D9890F' : UM;
      const pin = el(mc, { left: '0', top: '0', width: '26px', height: '26px', borderRadius: '13px', background: col, border: '3px solid #fff', boxShadow: '0 2px 6px rgba(0,0,0,0.25)', display: 'flex', alignItems: 'center', justifyContent: 'center' });
      pin.innerHTML = `<svg width="12" height="12" viewBox="0 0 12 12"><path d="M1 6 L11 6 M7 2 L11 6 L7 10" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
      const tag = el(mc, { left: '0', top: '0', padding: '3px 7px', borderRadius: '5px', background: '#fff', border: `1px solid ${BD}`, fontSize: '11px', fontWeight: '700', color: TX, whiteSpace: 'nowrap', boxShadow: '0 1px 3px rgba(0,0,0,0.08)' }, r.id);
      if (i === 0) { pin.style.width = pin.style.height = '34px'; pin.style.borderRadius = '17px'; pin.style.boxShadow = '0 0 0 8px rgba(207,59,59,0.22), 0 2px 6px rgba(0,0,0,0.25)'; }
      return { r, pin, tag, big: i === 0 };
    });
    // Panneau de l'expédition sélectionnée.
    const dp = card(m, 944, 92, 404, 560, null);
    el(dp, { left: '20px', top: '18px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '17px', fontWeight: '700' }, `TR-2041 ${badge('Delayed', 'bad')}`);
    el(dp, { left: '20px', top: '48px', fontSize: '13px', color: TX2 }, 'Paris DC → Milan DC · TransAlp Logistics');
    const etaBox = el(dp, { left: '20px', top: '82px', width: '362px', height: '74px', borderRadius: '8px', background: '#FCF2F2', border: '1px solid #F4D4D4' });
    el(etaBox, { left: '14px', top: '11px', fontSize: '12px', color: '#A33', fontWeight: '600' }, 'Estimated arrival');
    const eta = el(etaBox, { left: '14px', top: '30px', fontSize: '24px', fontWeight: '700', color: TX, fontVariantNumeric: 'tabular-nums' }, '15:05');
    const etaD = el(etaBox, { left: '96px', top: '38px', fontSize: '13px', fontWeight: '700', color: '#CF3B3B' }, '+35 min');
    el(etaBox, { right: '14px', top: '14px', textAlign: 'right', fontSize: '12px', color: TX2, lineHeight: '1.5' }, 'Planned 14:30<br>Customer notified ✓');
    const STOPS = [['Paris DC', 'Departed 06:12', 'ok'], ['Lyon hub', 'Departed 10:48', 'ok'], ['A43 · Chambéry', 'Heavy traffic · 22 min stop', 'bad'], ['Turin cross‑dock', 'ETA 13:40 → 14:15', 'neu'], ['Milan DC', 'ETA 15:05', 'neu']];
    el(dp, { left: '37px', top: '186px', width: '2px', height: '228px', background: '#E7E6E2' });
    STOPS.forEach(([n, d, k], j) => {
      const y = 176 + j * 58;
      el(dp, { left: '29px', top: y + 'px', width: '18px', height: '18px', borderRadius: '9px', background: k === 'ok' ? '#1C8C4E' : k === 'bad' ? '#CF3B3B' : '#fff', border: k === 'neu' ? '2px solid #C9C8C2' : 'none', display: 'flex', alignItems: 'center', justifyContent: 'center' }, k === 'ok' ? icon('check', 11, 3, '#fff') : k === 'bad' ? '<span style="width:6px;height:6px;border-radius:3px;background:#fff"></span>' : '');
      el(dp, { left: '62px', top: y - 2 + 'px', fontSize: '13.5px', fontWeight: '600', color: TX }, n);
      el(dp, { left: '62px', top: y + 18 + 'px', fontSize: '12px', color: k === 'bad' ? '#CF3B3B' : TX2 }, d);
    });
    el(dp, { left: '20px', top: '470px', width: '362px', height: '1px', background: '#F0EFEC' });
    el(dp, { left: '20px', top: '486px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px' }, `${photo(1, 36)}<span style="display:flex;flex-direction:column;line-height:1.3"><b>Mateo García</b><span style="color:${TX2};font-size:12px">Driver · +33 6 12 48 20 77</span></span>`);
    const temp = el(dp, { right: '20px', top: '486px', textAlign: 'right', fontSize: '12px', color: TX2, lineHeight: '1.45' }, '18 pallets · 12.4 t<br><b style="color:#1C8C4E">4.1 °C</b> reefer');
    // Exceptions.
    const ex = card(m, 944, 668, 404, 260, 'Exceptions', `<span style="color:${UM};font-weight:600">View all</span>`);
    const EXC = [['TR-2041', 'Traffic on A43 · +35 min', 'bad', '2 min'], ['TR-0906', 'Loading delay at Lyon hub · +12 min', 'warn', '18 min'], ['TR-3877', 'Temperature drift 6.2 °C', 'warn', '41 min'], ['TR-1530', 'Missing POD signature', 'neu', '1 h']];
    const exr = EXC.map(([id, t, k, ago], j) => {
      const r = el(ex, { left: '0', top: 48 + j * 52 + 'px', width: '404px', height: '52px', borderTop: '1px solid #F0EFEC' });
      el(r, { left: '18px', top: '16px' }, badge(id, k, true));
      el(r, { left: '112px', top: '17px', fontSize: '13px', color: TX }, t);
      el(r, { right: '16px', top: '18px', fontSize: '12px', color: TX3 }, ago);
      return r;
    });
    return {
      root: s.root,
      update(T, t0) {
        const u = T - t0;
        pins.forEach(({ r, pin, tag, big }, i) => {
          const [x, y, a] = along(r.P, lerp(r.u[0], r.u[1], clamp(u / 1.8)));
          const d = big ? 17 : 13;
          pin.style.transform = `translate(${(x - d).toFixed(1)}px,${(y - d).toFixed(1)}px)`;
          pin.firstChild.style.transform = `rotate(${(a * 180 / Math.PI).toFixed(1)}deg)`;
          tag.style.transform = `translate(${(x + d + 4).toFixed(1)}px,${(y - 28).toFixed(1)}px)`;
        });
        const late = Math.round(lerp(31, 35, P(T, t0 + 0.3, 1.0)));
        etaD.textContent = '+' + late + ' min';
        eta.textContent = `15:${String(late - 30).padStart(2, '0')}`;
        temp.innerHTML = `18 pallets · 12.4 t<br><b style="color:#1C8C4E">${(4.1 + 0.1 * Math.sin(T * 3)).toFixed(1)} °C</b> reefer`;
        S(jam, { s: 1 + 0.15 * Math.sin(T * 8) });
        s.subEl.querySelector('.lvt').textContent = `Live · updated ${1 + (Math.floor(u * 2) % 4)} s ago`;
        s.subEl.querySelector('.lv').style.opacity = String(0.55 + 0.45 * Math.sin(T * 6));
        exr.forEach((r, j) => { const p = P(T, t0 + 0.1 + j * 0.05, 0.4); S(r, { o: p, x: (1 - p) * 20 }); });
        S(exr[0], { o: P(T, t0 + 0.1, 0.4) });
        exr[0].style.background = `rgba(207,59,59,${(0.07 * (0.5 + 0.5 * Math.sin(T * 5))).toFixed(3)})`;
      },
    };
  }

  // ---------- 3. Maintenance : surveillance des machines ----------
  function plants(parent) {
    const s = shell(parent, { app: ['Asset Monitor', 'gauge'], mark: UM, active: 3, crumbs: ['Lyon plant', 'Press line', 'Press #4'], title: 'Press #4 · Hydraulic press 800 t', user: [22, 'Karim Benali'],
      nav: [['layout-dashboard', 'Overview'], ['building-2', 'Sites', '42'], ['server', 'Assets'], ['activity', 'Sensors'], ['clipboard-check', 'Work orders', '23'], ['layers', 'Spare parts'], ['calendar', 'Schedules']],
      sub: live('Streaming · 50 Hz') + `<span style="color:${TX3}">·</span>Asset ID PR-LY-004 · 18 sensors`,
      actions: btn('Last 15 minutes', false, 'clock') + btn('Compare', false, 'chart-column') + btn('Create work order', true, 'plus') });
    const m = s.main;
    const K = [
      kpi(m, 28, 92, 318, 'Health score', (v) => Math.round(v) + ' / 100', 62, '−21 pts', false, [88, 87, 86, 84, 83, 80, 76, 70, 62], 'gauge'),
      kpi(m, 362, 92, 318, 'Vibration (RMS)', (v) => v.toFixed(2) + ' mm/s', 7.84, '+38%', false, [4.1, 4.3, 4.2, 4.6, 5.0, 5.6, 6.3, 7.1, 7.8], 'activity'),
      kpi(m, 696, 92, 318, 'Oil temperature', (v) => v.toFixed(1) + ' °C', 71.4, '+6.2 °C', false, [62, 63, 63, 64, 66, 67, 69, 70, 71], 'zap'),
      kpi(m, 1030, 92, 318, 'Plant OEE today', (v) => v.toFixed(1) + '%', 87.4, '+2.1 pts', true, [82, 83, 85, 84, 86, 85, 87, 86, 87], 'trending-up'),
    ];
    const ch = card(m, 28, 216, 900, 400, 'Vibration · live', `<span style="display:flex;gap:14px"><span style="display:flex;align-items:center;gap:6px"><span style="width:14px;height:3px;background:${UM}"></span>Axis X</span><span style="display:flex;align-items:center;gap:6px"><span style="width:14px;height:3px;background:#9AA6FF"></span>Axis Y</span></span>`);
    const r = seeded(9);
    const N = 90;
    const vx = Array.from({ length: N }, (_, i) => 4.2 + 0.5 * Math.sin(i * 0.4) + r() * 0.5 + (i > 62 ? Math.pow((i - 62) / 28, 1.6) * 3.6 : 0));
    const vy = Array.from({ length: N }, (_, i) => 3.1 + 0.4 * Math.sin(i * 0.33 + 1) + r() * 0.4 + (i > 66 ? (i - 66) / 24 * 1.2 : 0));
    const xl = Array.from({ length: N }, (_, i) => (i % 15 === 0 ? `10:${String(30 + Math.floor(i / 6)).padStart(2, '0')}:${String((i % 6) * 10).padStart(2, '0')}` : ''));
    const LC = lineChart(ch, 14, 52, 872, 336, { series: [{ v: vx, c: UM }, { v: vy, c: '#9AA6FF' }], xl, ymin: 0, ymax: 10, yt: 5, fmt: (v) => v.toFixed(0), thr: 7.1 });
    const an = el(ch, { left: '0', top: '0', padding: '6px 10px', borderRadius: '6px', background: TX, color: '#fff', fontSize: '12px', fontWeight: '600', whiteSpace: 'nowrap', display: 'flex', alignItems: 'center', gap: '6px' }, `${icon('circle-alert', 12, 2.4, '#FF8A8A')} Anomaly detected · 10:44:12`);
    const readout = el(ch, { left: '172px', top: '15px', fontSize: '13px', fontWeight: '700', color: '#CF3B3B', fontVariantNumeric: 'tabular-nums' }, '');
    // Autres machines du site.
    const as = card(m, 944, 216, 404, 400, 'Lyon plant · assets', `<span style="color:${UM};font-weight:600">42 sites ▾</span>`);
    const AS = [['Press #4', 62, 'bad', 'Alert'], ['Press #2', 91, 'ok', 'Running'], ['Welding cell W3', 78, 'warn', 'Watch'], ['Paint booth P1', 94, 'ok', 'Running'], ['Conveyor C7', 71, 'warn', 'Belt wear'], ['Robot R12', 96, 'ok', 'Running']];
    AS.forEach(([n, v, k, t], j) => {
      const rr = el(as, { left: '0', top: 48 + j * 57 + 'px', width: '404px', height: '57px', borderTop: '1px solid #F0EFEC', background: j === 0 ? '#FCF5F5' : '#fff' });
      el(rr, { left: '18px', top: '10px', fontSize: '13px', fontWeight: '600' }, n);
      el(rr, { left: '18px', top: '32px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12px', color: TX2 }, `${bar(v / 100, k === 'ok' ? '#1C8C4E' : k === 'warn' ? '#D9890F' : '#CF3B3B', 150)}${v}`);
      el(rr, { right: '16px', top: '17px' }, badge(t, k));
    });
    // Ordres de travail.
    const wo = card(m, 28, 632, 1320, 296, 'Work orders', `<span style="display:flex;align-items:center;gap:8px">${btn('All sites', false, 'building-2')}${btn('Priority', false, 'list-checks')}</span>`);
    const WO = [[22, 'Karim Benali', 'WO-1287', 'Press #4 · vibration above threshold', 'Lyon', ['High', 'bad'], 'SLA', ['Assigned', 'info']], [18, 'Hugo Laurent', 'WO-1286', 'Robot R2 · gearbox temperature', 'Łódź', ['Medium', 'warn'], '1 d 04:20', ['In progress', 'warn']], [17, 'Kwame Mensah', 'WO-1281', 'Conveyor C7 · replace belt', 'Turin', ['Medium', 'warn'], '2 d 11:05', ['Parts ordered', 'neu']], [12, 'Julien Leroy', 'WO-1279', 'Paint booth P1 · filter change', 'Lyon', ['Low', 'neu'], '5 d 02:40', ['Scheduled', 'neu']]];
    const TW = table(wo, 0, 50, 1320, [['ID', 100], ['ASSET & ISSUE', 380], ['SITE', 110], ['PRIORITY', 130], ['ASSIGNEE', 230], ['SLA REMAINING', 170], ['STATUS', 150]],
      WO.map(([k, n, id, t, site, pr, sla, st]) => [`<b style="font-weight:600;color:${UM}">${id}</b>`, t, site, badge(pr[0], pr[1]), `${photo(k, 26)}${n}`, `<span class="sla" style="font-variant-numeric:tabular-nums">${sla}</span>`, badge(st[0], st[1])]), 52);
    return {
      root: s.root,
      update(T, t0) {
        const u = clamp((T - t0 + 0.6) / 1.9);
        LC.draw(lerp(0.62, 1, u));
        K.forEach((k, i) => { k.n.textContent = k.fmt(k.v * (0.9 + 0.1 * P(T, t0 + i * 0.05, 1.0))); });
        const idx = Math.max(1, Math.round(lerp(0.62, 1, u) * (N - 1)));
        readout.textContent = `${vx[idx].toFixed(2)} mm/s`;
        readout.style.color = vx[idx] > 7.1 ? '#CF3B3B' : TX2;
        const ai = 79;
        const show = idx >= ai;
        S(an, { x: LC.sx(ai, N) + 14 - 70, y: 52 + LC.sy(vx[ai]) - 46, o: show ? P(T, t0 + 0.6, 0.3) : 0 });
        // Ordre de travail créé automatiquement à la détection, compte à rebours SLA.
        const r0 = TW.R[0];
        const wp = show ? P(T, t0 + 0.75, 0.4, Ease.outCubic) : 0;
        S(r0, { o: wp, x: (1 - wp) * -20 });
        r0.style.background = `rgba(30,59,255,${(0.07 * wp).toFixed(3)})`;
        const left = 4 * 3600 - Math.max(0, T - t0) * 37;
        const hh = Math.floor(left / 3600), mm = Math.floor((left % 3600) / 60), ss = Math.floor(left % 60);
        r0.querySelector('.sla').textContent = `${hh}:${String(mm).padStart(2, '0')}:${String(ss).padStart(2, '0')}`;
        TW.R.slice(1).forEach((rw, j) => { const p = P(T, t0 + 0.05 + j * 0.04, 0.4); S(rw, { o: p }); });
        s.subEl.querySelector('.lv').style.opacity = String(0.55 + 0.45 * Math.sin(T * 6));
      },
    };
  }

  // ---------- 4. CRM : pipeline commercial ----------
  function crm(parent) {
    const s = shell(parent, { app: ['Revenue Desk', 'target'], mark: INK, active: 1, crumbs: ['Sales', 'Pipeline', 'EMEA'], title: 'Pipeline · EMEA', user: [4, 'Elena Rossi'],
      nav: [['layout-dashboard', 'Overview'], ['target', 'Pipeline'], ['building-2', 'Accounts'], ['users', 'Contacts'], ['calendar', 'Activities', '8'], ['chart-column', 'Forecast'], ['settings', 'Settings']],
      sub: `€12.6M open · 92 deals · Q4 target <b style="color:${TX}">€4.2M</b>`, actions: btn('Owner: All', false, 'users') + btn('Close date: Q4', false, 'calendar') + btn('New deal', true, 'plus') });
    const COL = [['Lead', '€3.1M', 42], ['Qualified', '€2.8M', 27], ['Proposal', '€3.9M', 14], ['Negotiation', '€1.9M', 6], ['Won', '€0.9M', 3]];
    const D = [[['Atlas Retail', '€42k', 8, 'Dec 12'], ['Vertex Industries', '€18k', 3, 'Dec 18'], ['Kappa Chemicals', '€64k', 17, 'Jan 9']], [['Helio Pharma', '€96k', 4, 'Nov 28'], ['Nordic Steel', '€51k', 12, 'Dec 4'], ['Rhine Logistics', '€33k', 8, 'Dec 15']],
      [['Orion Bank', '€120k', 4, 'Nov 21'], ['Bluebay Energy', '€75k', 12, 'Nov 30'], ['Alpha Plastics', '€28k', 3, 'Dec 2']], [['Northwind Foods', '€210k', 8, 'Nov 14'], ['Celta Telecom', '€88k', 17, 'Nov 19']], [['Iberia Motors', '€140k', 12, 'Nov 3'], ['Lumen Health', '€62k', 4, 'Oct 29']]];
    const CWd = 252;
    const cols = COL.map(([n, v, c], i) => {
      const x = 28 + i * (CWd + 15);
      const col = el(s.main, { left: x + 'px', top: '92px', width: CWd + 'px', height: '836px', borderRadius: '10px', background: '#F0F0ED' });
      el(col, { left: '14px', top: '14px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '13px', fontWeight: '700' }, `<span style="width:8px;height:8px;border-radius:4px;background:${i === 4 ? '#1C8C4E' : UM};opacity:${i === 4 ? 1 : 0.4 + i * 0.15}"></span>${n}<span style="color:${TX3};font-weight:600">${c}</span>`);
      el(col, { right: '14px', top: '14px', fontSize: '13px', fontWeight: '700', color: TX2 }, v);
      return col;
    });
    const cards = [];
    D.forEach((list, i) => list.forEach(([n, v, k, d], j) => {
      const c = el(cols[i], { left: '10px', top: 48 + j * 132 + 'px', width: CWd - 20 + 'px', height: '120px', borderRadius: '8px', background: '#fff', border: `1px solid ${BD}`, boxShadow: '0 1px 2px rgba(0,0,0,0.04)' });
      el(c, { left: '14px', top: '14px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13.5px', fontWeight: '700' }, `<span style="width:28px;height:28px;border-radius:7px;background:${['#E8ECFF', '#F1F1EF', '#E7F6EC'][(i + j) % 3]};color:${[UM, '#3C3C42', '#1C8C4E'][(i + j) % 3]};font-size:12px;display:flex;align-items:center;justify-content:center">${n.split(' ').map((w) => w[0]).join('')}</span>${n}`);
      el(c, { left: '14px', top: '54px', fontSize: '19px', fontWeight: '700', letterSpacing: '-0.01em' }, v);
      el(c, { left: '14px', top: '88px', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '12px', color: TX2 }, `${icon('calendar', 12, 2, TX3)}${d}`);
      el(c, { right: '14px', top: '84px' }, photo(k, 24));
      cards.push(c);
    }));
    return { root: s.root, update(T, t0) { cards.forEach((c, i) => { const p = P(T, t0 + i * 0.025, 0.4); S(c, { o: p, y: (1 - p) * 10 }); }); } };
  }

  // ---------- 5. Entrepôt : stocks ----------
  function stock(parent) {
    const s = shell(parent, { app: ['Stock Room', 'layers'], mark: UM, active: 1, crumbs: ['Warehouses', 'Lyon DC', 'Inventory'], title: 'Inventory · Lyon DC', user: [12, 'Fatou Diallo'],
      nav: [['layout-dashboard', 'Overview'], ['layers', 'Inventory'], ['inbox', 'Inbound', '6'], ['send', 'Outbound'], ['circle-alert', 'Reorders', '!3'], ['building-2', 'Suppliers'], ['chart-column', 'Reports']],
      sub: live('Synced with ERP 1 min ago') + `<span style="color:${TX3}">·</span>2,418 SKUs`, actions: btn('All categories', false, 'list-checks') + btn('Create PO', true, 'plus') });
    const m = s.main;
    kpi(m, 28, 92, 318, 'Stock value', () => '€8.42M', 0, '+1.8%', true, [7.9, 8.0, 8.1, 8.0, 8.2, 8.3, 8.2, 8.4, 8.4], 'banknote');
    kpi(m, 362, 92, 318, 'Days of cover', () => '23.6 days', 0, '−1.2 days', false, [27, 26, 26, 25, 25, 24, 24, 24, 23.6], 'clock');
    kpi(m, 696, 92, 318, 'Below reorder point', () => '3 SKUs', 0, '+2', false, [0, 1, 1, 0, 1, 1, 2, 2, 3], 'circle-alert');
    kpi(m, 1030, 92, 318, 'Order accuracy', () => '99.6%', 0, '+0.2 pts', true, [99, 99.2, 99.1, 99.3, 99.4, 99.3, 99.5, 99.5, 99.6], 'circle-check');
    const tc = card(m, 28, 216, 1320, 712, 'Stock levels');
    const RW = [['SKU-10482', 'Hydraulic hose 1/2" · 2 m', 'Spare parts', 0.18, '214', '420', ['Reorder', 'bad'], 'PO-8812 · Nov 6'], ['SKU-20915', 'Bearing 6205-2RS', 'Spare parts', 0.24, '388', '600', ['Reorder', 'bad'], '—'], ['SKU-33107', 'Safety gloves · size 9', 'PPE', 0.31, '1,240', '1,500', ['Reorder', 'bad'], '—'],
      ['SKU-11873', 'Pallet wrap 23 µm', 'Packaging', 0.56, '86 rolls', '60', ['Healthy', 'ok'], '—'], ['SKU-40221', 'Motor oil 5W-30 · 20 L', 'Consumables', 0.72, '142', '80', ['Healthy', 'ok'], 'PO-8790 · Nov 12'], ['SKU-28644', 'Conveyor belt 800 mm', 'Spare parts', 0.44, '12', '10', ['Low', 'warn'], 'PO-8805 · Nov 4'], ['SKU-50317', 'Label roll 100 × 150', 'Packaging', 0.88, '3,600', '1,200', ['Healthy', 'ok'], '—'], ['SKU-36118', 'Air filter F7', 'Spare parts', 0.62, '96', '50', ['Healthy', 'ok'], '—'], ['SKU-19904', 'Forklift battery 48 V', 'Equipment', 0.4, '6', '4', ['Low', 'warn'], '—'], ['SKU-27460', 'Cable ties 300 mm', 'Consumables', 0.93, '9,800', '2,000', ['Healthy', 'ok'], '—'], ['SKU-44012', 'Hi-vis vest · L', 'PPE', 0.52, '310', '200', ['Healthy', 'ok'], '—'], ['SKU-31775', 'Grease cartridge 400 g', 'Consumables', 0.67, '520', '300', ['Healthy', 'ok'], '—']];
    const TB2 = table(tc, 0, 50, 1320, [['SKU', 130], ['ITEM', 300], ['CATEGORY', 150], ['STOCK LEVEL', 250], ['ON HAND', 110, 'right'], ['REORDER AT', 120, 'right'], ['STATUS', 110], ['INBOUND', 130]],
      RW.map(([sk, n, c, v, oh, ra, st, inb]) => [`<b style="font-weight:600;color:${UM}">${sk}</b>`, n, c, bar(v, v < 0.35 ? '#CF3B3B' : v < 0.5 ? '#D9890F' : UM, 200, 7), oh, ra, badge(st[0], st[1]), `<span style="color:${TX2}">${inb}</span>`]), 52);
    return { root: s.root, update(T, t0) { TB2.R.forEach((r, j) => { const p = P(T, t0 + j * 0.03, 0.4); S(r, { o: p }); }); } };
  }

  // ---------- 6. Planning des équipes ----------
  function shifts(parent) {
    const s = shell(parent, { app: ['Shift Planner', 'calendar'], mark: INK, active: 1, crumbs: ['Lyon plant', 'Schedules', 'Week 45'], title: 'Week 45 · Nov 3 – Nov 9', user: [20, 'Paul Martin'],
      nav: [['layout-dashboard', 'Overview'], ['calendar', 'Schedule'], ['users', 'Team', '64'], ['clock', 'Time off', '5'], ['circle-alert', 'Open shifts', '!2'], ['chart-column', 'Reports']],
      sub: `Coverage <b style="color:#1C8C4E">97%</b><span style="color:${TX3}">·</span>2 open shifts`, actions: btn('Line: Press & Weld', false, 'list-checks') + btn('Publish schedule', true, 'send') });
    const m = s.main;
    const c = card(m, 28, 92, 1320, 836, null);
    const DAYS = ['Mon 3', 'Tue 4', 'Wed 5', 'Thu 6', 'Fri 7', 'Sat 8', 'Sun 9'];
    const X0 = 250, DW = 152;
    DAYS.forEach((d, i) => el(c, { left: X0 + i * DW + 'px', top: '0', width: DW + 'px', height: '44px', borderLeft: '1px solid #F0EFEC', borderBottom: `1px solid ${BD}`, background: '#FAFAF8', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '12.5px', fontWeight: '600', color: i > 4 ? TX3 : TX2 }, d));
    el(c, { left: '0', top: '0', width: X0 + 'px', height: '44px', borderBottom: `1px solid ${BD}`, background: '#FAFAF8', display: 'flex', alignItems: 'center', padding: '0 18px', fontSize: '11.5px', fontWeight: '600', color: TX2 }, 'TEAM MEMBER');
    const PPL = [[22, 'Karim Benali', 'Technician'], [1, 'Mateo García', 'Operator'], [17, 'Kwame Mensah', 'Operator'], [2, 'Inès Duval', 'Line lead'], [18, 'Hugo Laurent', 'Welder'], [13, 'Sofia Alves', 'Quality'], [3, 'Tom Becker', 'Operator'], [11, 'Emma Novak', 'Welder'], [23, 'Ryan Cole', 'Maintenance'], [14, 'Léa Bernard', 'Operator']];
    const G = ['MMMEE..', 'EEMMM..', 'NNN..MM', 'MMMMM..', 'EE.NNN.', 'MMEEM..', '..MMMEE', 'NNEE..M', 'MM.MMEE', 'EEEMM..'];
    const SH = { M: ['06:00 – 14:00', '#E8ECFF', UM], E: ['14:00 – 22:00', '#F1F1EF', '#3C3C42'], N: ['22:00 – 06:00', '#16161C', '#fff'] };
    const blocks = [];
    PPL.forEach(([k, n, r], j) => {
      const y = 44 + j * 76;
      el(c, { left: '0', top: y + 'px', width: '1320px', height: '76px', borderBottom: '1px solid #F0EFEC' });
      el(c, { left: '18px', top: y + 20 + 'px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px' }, who(k, n, r));
      G[j].split('').forEach((g, i) => {
        if (g === '.') return;
        const b = el(c, { left: X0 + i * DW + 6 + 'px', top: y + 10 + 'px', width: DW - 12 + 'px', height: '56px', borderRadius: '7px', background: SH[g][1], color: SH[g][2], padding: '9px 11px', fontSize: '12px', fontWeight: '600', lineHeight: '1.45', borderLeft: `3px solid ${g === 'N' ? LIME : SH[g][2]}` }, `${SH[g][0]}<br><span style="font-weight:500;opacity:0.75">${['Press line', 'Weld cell', 'Paint'][(i + j) % 3]}</span>`);
        blocks.push(b);
      });
    });
    const open = el(c, { left: X0 + 5 * DW + 6 + 'px', top: 44 + 3 * 76 + 10 + 'px', width: DW - 12 + 'px', height: '56px', borderRadius: '7px', border: '2px dashed #CF3B3B', background: '#FCF5F5', color: '#CF3B3B', fontSize: '12px', fontWeight: '700', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '6px' }, `${icon('plus', 13, 2.6, '#CF3B3B')}Open shift`);
    return { root: s.root, update(T, t0) { blocks.forEach((b, i) => { const p = P(T, t0 + (i % 30) * 0.015, 0.4); S(b, { o: p, s: 0.96 + 0.04 * p }); }); S(open, { o: 0.6 + 0.4 * Math.sin(T * 5) }); } };
  }

  // ---------- Portail client généré dans la démo (même interface que la scène d'origine : v, dk, kp, grid, cards) ----------
  function portal(parent, dash) {
    const PW = 1180, PH = 800;
    const v = el(parent, { left: '0', top: '0', width: PW + 'px', height: PH + 'px', background: BG, overflow: 'hidden', fontFamily: 'DM Sans', color: TX, transformOrigin: '0 0' });
    const sb = el(v, { left: '0', top: '0', width: '190px', height: PH + 'px', background: SIDE, borderRight: `1px solid ${BD}` });
    el(sb, { left: '16px', top: '16px', display: 'flex', alignItems: 'center', gap: '9px', fontSize: '14px', fontWeight: '700' }, `<span style="width:28px;height:28px;border-radius:7px;background:${UM};display:flex;align-items:center;justify-content:center">${icon('briefcase', 15, 2.2, '#fff')}</span>Client Portal`);
    [['layout-dashboard', 'Dashboard'], ['building-2', 'Clients'], ['folder', 'Documents'], ['clipboard-check', 'Approvals', '5'], ['message-square', 'Messages', '2'], ['settings', 'Settings']].forEach(([ic, l, b], i) => el(sb, { left: '8px', top: 66 + i * 32 + 'px', width: '174px', height: '28px', borderRadius: '6px', background: i === 1 ? '#EEF0FF' : 'transparent', display: 'flex', alignItems: 'center', gap: '9px', padding: '0 9px', fontSize: '12.5px', fontWeight: i === 1 ? '600' : '500', color: i === 1 ? UM : '#3C3C42' }, `${icon(ic, 14, 2, i === 1 ? UM : TX2)}${l}${b ? `<span style="margin-left:auto;font-size:10.5px;font-weight:700;color:${TX2};background:#ECECE9;border-radius:8px;padding:1px 6px">${b}</span>` : ''}`));
    const top = el(v, { left: '190px', top: '0', width: PW - 190 + 'px', height: '50px', background: '#fff', borderBottom: `1px solid ${BD}` });
    el(top, { left: '22px', top: '16px', fontSize: '12.5px', color: TX2 }, `Clients <span style="color:${TX3}">/</span> <b style="color:${TX}">All clients</b>`);
    el(top, { right: '18px', top: '10px', display: 'flex', alignItems: 'center', gap: '12px' }, `<span style="display:flex;align-items:center;gap:6px;height:30px;width:200px;padding:0 10px;border:1px solid ${BD};border-radius:6px;font-size:12px;color:${TX3}">${icon('search', 13, 2, TX3)}Search clients</span>${photo(19, 28)}`);
    const body = el(v, { left: '190px', top: '50px', width: PW - 190 + 'px', height: PH - 50 + 'px' });
    const dk = el(body, { left: '22px', top: '18px', width: '946px', height: '108px' });
    const KP = [['Active clients', '24', '+3', 'users'], ['In onboarding', '9', '+2', 'loader-circle'], ['Avg. time to onboard', '6.5 d', '−1.2 d', 'timer'], ['Pending approvals', '5', '−2', 'clipboard-check']];
    const kp = KP.map(([l, val, d, ic], i) => {
      const c = el(dk, { left: i * 240 + 'px', top: '0', width: '226px', height: '96px', background: '#fff', border: `1px solid ${BD}`, borderRadius: '9px', transformOrigin: '50% 50%' });
      el(c, { left: '14px', top: '13px', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '12px', color: TX2 }, `${icon(ic, 13, 2, TX3)}${l}`);
      el(c, { left: '14px', top: '38px', fontSize: '24px', fontWeight: '700' }, val);
      el(c, { left: '14px', top: '70px', fontSize: '11.5px', fontWeight: '600', color: ST.ok[1] }, `${d} <span style="color:${TX3};font-weight:500">this month</span>`);
      el(c, { right: '12px', top: '40px' }, spark([3, 4, 3, 5, 6, 5, 7, 8], 70, 28, UM));
      return c;
    });
    const grid = el(body, { left: '22px', top: dash ? '142px' : '22px', width: '946px', height: '560px', background: '#fff', border: `1px solid ${BD}`, borderRadius: '10px', overflow: 'hidden' });
    tabs(grid, 14, 14, ['All clients <b>24</b>', 'Onboarding <b>9</b>', 'Awaiting documents <b>4</b>', 'Approved <b>11</b>']);
    el(grid, { right: '14px', top: '12px' }, btn('Add client', true, 'plus'));
    const CL = [['Northwind Logistics', 19, 'Claire Dubois', 'Onboarding', 'info', 0.6, '6/10', 'Nov 14'], ['Bluebay Energy', 20, 'Mark Allen', 'Approved', 'ok', 1, '10/10', 'Nov 2'], ['Atlas Retail', 13, 'Sofia Rossi', 'Awaiting docs', 'warn', 0.35, '3/10', 'Nov 20'],
      ['Helio Pharma', 15, 'Jonas Weber', 'Onboarding', 'info', 0.72, '7/10', 'Nov 12'], ['Vertex Industries', 21, 'Lena Novak', 'Approved', 'ok', 1, '10/10', 'Oct 30'], ['Orion Bank', 12, 'Paul Martin', 'Awaiting docs', 'warn', 0.2, '2/10', 'Nov 26'], ['Celta Telecom', 9, 'Ana Ruiz', 'Onboarding', 'info', 0.5, '5/10', 'Nov 18'], ['Kappa Chemicals', 5, 'Ian Brooks', 'Onboarding', 'info', 0.4, '4/10', 'Nov 22']];
    const TT = table(grid, 0, 56, 946, [['CLIENT', 230], ['CONTACT', 200], ['STATUS', 150], ['DOCUMENTS', 190], ['DUE', 90], ['', 50]],
      CL.map(([n, k, c, st, kk, pv, dc, due]) => [`<span style="width:30px;height:30px;border-radius:7px;background:#F1F1EF;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#3C3C42">${n.split(' ').map((w) => w[0]).join('')}</span><b style="font-weight:600">${n}</b>`, `${photo(k, 24)}${c}`, badge(st, kk), `${bar(pv, pv === 1 ? '#1C8C4E' : UM, 110)}<span style="color:${TX2};font-size:12px">${dc}</span>`, `<span style="color:${TX2}">${due}</span>`, icon('ellipsis', 16, 2, TX3)]), 58);
    if (!dash) dk.style.display = 'none';
    return { v, dk, kp, grid, cards: TT.R };
  }

  U.APP = { people, fleet, plants, crm, stock, shifts, portal, W, H };
})();

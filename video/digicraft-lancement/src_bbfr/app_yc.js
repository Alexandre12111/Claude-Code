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
    el(c, { left: '18px', top: '78px', fontSize: '12px', fontWeight: '600', color: good ? ST.ok[1] : ST.bad[1] }, `${delta} <span style="color:${TX3};font-weight:500">vs trim. préc.</span>`);
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
    if (thr !== null) s += `<line x1="${pl}" x2="${w - 8}" y1="${sy(thr)}" y2="${sy(thr)}" stroke="#CF3B3B" stroke-width="1.5" stroke-dasharray="5 4"/><text x="${w - 10}" y="${sy(thr) - 6}" text-anchor="end" font-family="DM Sans" font-size="11" font-weight="600" fill="#CF3B3B">Seuil ${fmt(thr)}</text>`;
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
    el(sb, { left: '14px', top: '66px', width: '196px', height: '32px', borderRadius: '7px', background: '#fff', border: `1px solid ${BD}`, display: 'flex', alignItems: 'center', gap: '8px', padding: '0 10px', fontSize: '12.5px', color: TX3 }, `${icon('search', 14, 2, TX3)}Rechercher<span style="margin-left:auto;font-size:11px;border:1px solid ${BD};border-radius:4px;padding:0 5px">⌘K</span>`);
    nav.forEach(([ic, l, b], i) => {
      const on = i === active;
      el(sb, { left: '10px', top: 112 + i * 34 + 'px', width: '204px', height: '30px', borderRadius: '7px', background: on ? '#EEF0FF' : 'transparent', display: 'flex', alignItems: 'center', gap: '10px', padding: '0 10px', fontSize: '13px', fontWeight: on ? '600' : '500', color: on ? UM : '#3C3C42' },
        `${icon(ic, 16, 2, on ? UM : TX2)}${l}${b ? `<span style="margin-left:auto;min-width:20px;height:18px;padding:0 6px;border-radius:9px;background:${b[0] === '!' ? '#FCEBEB' : '#ECECE9'};color:${b[0] === '!' ? '#CF3B3B' : TX2};font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center">${b.replace('!', '')}</span>` : ''}`);
    });
    const ny = 112 + nav.length * 34 + 22;
    el(sb, { left: '20px', top: ny + 'px', fontSize: '11px', fontWeight: '600', color: TX3, letterSpacing: '0.06em' }, 'VUES ENREGISTRÉES');
    ['Mon équipe', 'Cette semaine', 'À traiter'].forEach((t, i) => el(sb, { left: '20px', top: ny + 26 + i * 30 + 'px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px', color: '#3C3C42' }, `<span style="width:8px;height:8px;border-radius:2px;background:${['#1E3BFF', '#1C8C4E', '#CF3B3B'][i]}"></span>${t}`));
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
    const s = shell(parent, { app: ['People Hub', 'users'], mark: UM, active: 2, crumbs: ['Ressources humaines', 'Intégration', '4e trimestre 2026'], title: 'Intégration des arrivants',
      nav: [['house', 'Accueil'], ['users', 'Collaborateurs'], ['user-plus', 'Intégration', '9'], ['target', 'Recrutement', '12'], ['calendar', 'Absences'], ['layers', 'Organigramme'], ['chart-column', 'Rapports'], ['settings', 'Paramètres']],
      sub: live('En direct') + `<span style="color:${TX3}">·</span>48 arrivées dans 6 départements`,
      actions: btn('1 oct. – 31 déc. 2026', false, 'calendar') + btn('Filtres', false, 'list-checks') + btn('Nouvelle arrivée', true, 'plus') });
    const m = s.main;
    const K = [
      kpi(m, 28, 92, 318, 'Arrivées ce trimestre', (v) => Math.round(v), 48, '+12%', true, [22, 25, 24, 30, 33, 31, 38, 41, 48], 'user-plus'),
      kpi(m, 362, 92, 318, 'Prêts dès le jour J', (v) => v.toFixed(1).replace('.', ',') + ' %', 94.2, '+3,1 pts', true, [86, 88, 87, 90, 89, 91, 93, 92, 94], 'circle-check'),
      kpi(m, 696, 92, 318, 'Délai de prise de poste', (v) => v.toFixed(1).replace('.', ',') + ' jours', 18.4, '−2,6 jours', true, [26, 25, 24, 23, 22, 21, 20, 19, 18], 'timer'),
      kpi(m, 1030, 92, 318, 'Tâches d’intégration ouvertes', (v) => Math.round(v), 37, '−14', true, [61, 58, 55, 52, 50, 46, 44, 40, 37], 'list-checks'),
    ];
    const tc = card(m, 28, 216, 900, 712, 'Prochaines arrivées', `<span style="display:flex;align-items:center;gap:6px;height:28px;padding:0 10px;border:1px solid ${BD};border-radius:6px">${icon('search', 13, 2, TX3)}Rechercher</span>${btn('Exporter', false, 'download')}`);
    tabs(tc, 18, 50, ['Toutes <b>48</b>', 'Cette semaine <b>9</b>', 'Bloquées <b>3</b>', 'Terminées <b>21</b>']);
    const ROWS = [
      [0, 'Sarah Martin', 'Consultante', 'Conseil', 'lun. 3 nov.', 5, 'David Cohen', 0.92, '11/12', ['Dans les temps', 'ok']],
      [3, 'Tom Becker', 'Analyste de données', 'Données', 'lun. 3 nov.', 4, 'Elena Rossi', 0.66, '8/12', ['Ordinateur en livraison', 'warn']],
      [2, 'Inès Duval', 'Cheffe de projet', 'Opérations', 'mar. 4 nov.', 20, 'Paul Martin', 0.84, '10/12', ['Dans les temps', 'ok']],
      [7, 'Marc Petit', 'Fiscaliste', 'Finance', 'mer. 5 nov.', 5, 'David Cohen', 0.33, '4/12', ['Contrat non signé', 'bad']],
      [9, 'Amara Okafor', 'Chargée de recrutement', 'Ressources humaines', 'lun. 10 nov.', 19, 'Claire Dubois', 1, '12/12', ['Prête', 'ok']],
      [10, 'Lucas Moreau', 'Ingénieur', 'Ingénierie', 'lun. 10 nov.', 4, 'Elena Rossi', 0.58, '7/12', ['Accès en attente', 'warn']],
      [6, 'Yuki Tanaka', 'Designer produit', 'Produit', 'lun. 17 nov.', 20, 'Paul Martin', 0.75, '9/12', ['Dans les temps', 'ok']],
      [16, 'Priya Nair', 'Analyste', 'Données', 'lun. 17 nov.', 4, 'Elena Rossi', 0.5, '6/12', ['Dans les temps', 'ok']],
      [15, 'Jonas Weber', 'Responsable opérations', 'Opérations', 'lun. 1 déc.', 20, 'Paul Martin', 0.25, '3/12', ['Pas commencé', 'neu']],
      [13, 'Sofia Alves', 'Acheteuse', 'Achats', 'lun. 1 déc.', 5, 'David Cohen', 0.17, '2/12', ['Pas commencé', 'neu']],
      [1, 'Ryan Cole', 'Commercial senior', 'Ventes', 'lun. 8 déc.', 19, 'Claire Dubois', 0.08, '1/12', ['Pas commencé', 'neu']],
    ];
    const T1 = table(tc, 0, 92, 900, [['COLLABORATEUR', 196], ['DÉPARTEMENT', 118], ['ARRIVÉE', 98], ['MANAGER', 152], ['INTÉGRATION', 148], ['STATUT', 160]],
      ROWS.map(([k, n, r, d, sd, mk, mn, v, tk, st]) => [who(k, n, r), d, sd, `${photo(mk, 22)}<span>${mn}</span>`, `${bar(v, v === 1 ? '#1C8C4E' : UM, 84)}<span style="color:${TX2};font-size:12px">${tk}</span>`, badge(st[0], st[1])]), 52);
    // Flux d'activité en direct.
    const fc = card(m, 944, 216, 404, 420, 'Activité en direct', `<span style="display:flex;align-items:center;gap:6px;color:#1C8C4E;font-weight:600">${live('')}En direct</span>`);
    const FEED = [[22, '<b>Ordinateur</b> expédié à <b>Tom Becker</b>', 'à l’instant', 'smartphone'], [0, '<b>Sarah Martin</b> a fini la formation sécurité', 'il y a 2 min', 'graduation-cap'], [5, '<b>David Cohen</b> a validé les accès de <b>M. Petit</b>', 'il y a 6 min', 'shield-check'], [9, '<b>Amara Okafor</b> est prête pour le jour J', 'il y a 14 min', 'circle-check'], [20, '<b>Paul Martin</b> a réservé un déjeuner d’équipe', 'il y a 22 min', 'utensils'], [4, '<b>Elena Rossi</b> a choisi un parrain à <b>Lucas</b>', 'il y a 31 min', 'users']];
    const feed = el(fc, { left: '0', top: '48px', width: '404px', height: '372px', overflow: 'hidden' });
    const F = FEED.map(([k, t, ago, ic], j) => {
      const r = el(feed, { left: '0', top: j * 60 + 'px', width: '404px', height: '60px', borderTop: j ? '1px solid #F0EFEC' : 'none' });
      el(r, { left: '18px', top: '12px' }, photo(k, 34));
      el(r, { left: '62px', top: '11px', width: '320px', fontSize: '13px', lineHeight: '1.35', color: '#2A2A2F' }, t);
      el(r, { left: '62px', top: '33px', fontSize: '11.5px', color: TX3, display: 'flex', alignItems: 'center', gap: '5px' }, `${icon(ic, 12, 2, TX3)}${ago}`);
      return r;
    });
    const dc = card(m, 944, 652, 404, 276, 'Arrivées par département');
    const bars = barChart(dc, 12, 50, 380, 214, [11, 9, 8, 7, 6, 4, 3], ['Conseil', 'Données', 'Opér.', 'Ingén.', 'Finance', 'Ventes', 'Pers.'], [UM, UM, UM, UM, UM, '#B7C2FF', '#B7C2FF'], 12, (v) => Math.round(v));
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
        p3.children[5].innerHTML = sg ? badge('Contrat signé', 'ok') : badge('Contrat non signé', 'bad');
        p3.style.background = sg ? `rgba(28,140,78,${(0.08 * (1 - P(T, t0 + 1.0, 0.8))).toFixed(3)})` : '#fff';
        bars(u);
        s.subEl.querySelector('.lv').style.opacity = String(0.55 + 0.45 * Math.sin(T * 6));
      },
    };
  }

  // ---------- 2. Supply chain : tour de contrôle ----------
  function fleet(parent) {
    const s = shell(parent, { app: ['Control Tower', 'map-pin'], mark: INK, active: 1, crumbs: ['Logistique', 'Carte en direct'], title: 'Carte en direct', user: [21, 'Nadia Haddad'],
      nav: [['layout-dashboard', 'Vue d’ensemble'], ['map-pin', 'Carte en direct'], ['inbox', 'Expéditions', '214'], ['circle-alert', 'Incidents', '!4'], ['building-2', 'Transporteurs'], ['house', 'Entrepôts'], ['chart-column', 'Analyses']],
      sub: live('En direct · mis à jour il y a 2 s') + `<span style="color:${TX3}">·</span>38 camions en transit · Europe`,
      actions: btn('Région : Europe', false, 'globe') + btn('Statut : en transit', false, 'list-checks') + btn('Partager', false, 'external-link') });
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
      [[UM, 'En transit 34'], ['#CF3B3B', 'En retard 4'], ['#1C8C4E', 'Livrés aujourd’hui 61']].map(([c, t]) => `<span style="display:flex;align-items:center;gap:6px"><span style="width:9px;height:9px;border-radius:5px;background:${c}"></span>${t}</span>`).join(''));
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
    el(dp, { left: '20px', top: '18px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '17px', fontWeight: '700' }, `TR-2041 ${badge('En retard', 'bad')}`);
    el(dp, { left: '20px', top: '48px', fontSize: '13px', color: TX2 }, 'Paris → Milan · TransAlp Logistics');
    const etaBox = el(dp, { left: '20px', top: '82px', width: '362px', height: '74px', borderRadius: '8px', background: '#FCF2F2', border: '1px solid #F4D4D4' });
    el(etaBox, { left: '14px', top: '11px', fontSize: '12px', color: '#A33', fontWeight: '600' }, 'Arrivée estimée');
    const eta = el(etaBox, { left: '14px', top: '30px', fontSize: '24px', fontWeight: '700', color: TX, fontVariantNumeric: 'tabular-nums' }, '15:05');
    const etaD = el(etaBox, { left: '96px', top: '38px', fontSize: '13px', fontWeight: '700', color: '#CF3B3B' }, '+35 min');
    el(etaBox, { right: '14px', top: '14px', textAlign: 'right', fontSize: '12px', color: TX2, lineHeight: '1.5' }, 'Prévue 14:30<br>Client prévenu ✓');
    const STOPS = [['Entrepôt de Paris', 'Parti à 06:12', 'ok'], ['Hub de Lyon', 'Parti à 10:48', 'ok'], ['A43 · Chambéry', 'Trafic dense · arrêt de 22 min', 'bad'], ['Cross-dock de Turin', 'Arrivée 13:40 → 14:15', 'neu'], ['Entrepôt de Milan', 'Arrivée 15:05', 'neu']];
    el(dp, { left: '37px', top: '186px', width: '2px', height: '228px', background: '#E7E6E2' });
    STOPS.forEach(([n, d, k], j) => {
      const y = 176 + j * 58;
      el(dp, { left: '29px', top: y + 'px', width: '18px', height: '18px', borderRadius: '9px', background: k === 'ok' ? '#1C8C4E' : k === 'bad' ? '#CF3B3B' : '#fff', border: k === 'neu' ? '2px solid #C9C8C2' : 'none', display: 'flex', alignItems: 'center', justifyContent: 'center' }, k === 'ok' ? icon('check', 11, 3, '#fff') : k === 'bad' ? '<span style="width:6px;height:6px;border-radius:3px;background:#fff"></span>' : '');
      el(dp, { left: '62px', top: y - 2 + 'px', fontSize: '13.5px', fontWeight: '600', color: TX }, n);
      el(dp, { left: '62px', top: y + 18 + 'px', fontSize: '12px', color: k === 'bad' ? '#CF3B3B' : TX2 }, d);
    });
    el(dp, { left: '20px', top: '470px', width: '362px', height: '1px', background: '#F0EFEC' });
    el(dp, { left: '20px', top: '486px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px' }, `${photo(1, 36)}<span style="display:flex;flex-direction:column;line-height:1.3"><b>Mateo García</b><span style="color:${TX2};font-size:12px">Chauffeur · +33 6 12 48 20 77</span></span>`);
    const temp = el(dp, { right: '20px', top: '486px', textAlign: 'right', fontSize: '12px', color: TX2, lineHeight: '1.45' }, '18 palettes · 12,4 t<br><b style="color:#1C8C4E">4,1 °C</b> frigo');
    // Exceptions.
    const ex = card(m, 944, 668, 404, 260, 'Incidents', `<span style="color:${UM};font-weight:600">Tout voir</span>`);
    const EXC = [['TR-2041', 'Bouchon sur l’A43 · +35 min', 'bad', '2 min'], ['TR-0906', 'Chargement retardé à Lyon · +12 min', 'warn', '18 min'], ['TR-3877', 'Dérive de température 6,2 °C', 'warn', '41 min'], ['TR-1530', 'Signature de livraison manquante', 'neu', '1 h']];
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
        temp.innerHTML = `18 palettes · 12,4 t<br><b style="color:#1C8C4E">${(4.1 + 0.1 * Math.sin(T * 3)).toFixed(1).replace('.', ',')} °C</b> frigo`;
        S(jam, { s: 1 + 0.15 * Math.sin(T * 8) });
        s.subEl.querySelector('.lvt').textContent = `En direct · mis à jour il y a ${1 + (Math.floor(u * 2) % 4)} s`;
        s.subEl.querySelector('.lv').style.opacity = String(0.55 + 0.45 * Math.sin(T * 6));
        exr.forEach((r, j) => { const p = P(T, t0 + 0.1 + j * 0.05, 0.4); S(r, { o: p, x: (1 - p) * 20 }); });
        S(exr[0], { o: P(T, t0 + 0.1, 0.4) });
        exr[0].style.background = `rgba(207,59,59,${(0.07 * (0.5 + 0.5 * Math.sin(T * 5))).toFixed(3)})`;
      },
    };
  }

  // ---------- 3. Maintenance : surveillance des machines ----------
  function plants(parent) {
    const s = shell(parent, { app: ['Asset Monitor', 'gauge'], mark: UM, active: 3, crumbs: ['Usine de Lyon', 'Ligne de presses', 'Presse n° 4'], title: 'Presse n° 4 · Presse hydraulique 800 t', user: [22, 'Karim Benali'],
      nav: [['layout-dashboard', 'Vue d’ensemble'], ['building-2', 'Sites', '42'], ['server', 'Équipements'], ['activity', 'Capteurs'], ['clipboard-check', 'Ordres de travail', '23'], ['layers', 'Pièces détachées'], ['calendar', 'Plannings']],
      sub: live('Flux en direct · 50 Hz') + `<span style="color:${TX3}">·</span>Machine PR-LY-004 · 18 capteurs`,
      actions: btn('15 dernières minutes', false, 'clock') + btn('Comparer', false, 'chart-column') + btn('Créer un ordre de travail', true, 'plus') });
    const m = s.main;
    const K = [
      kpi(m, 28, 92, 318, 'Score de santé', (v) => Math.round(v) + ' / 100', 62, '−21 pts', false, [88, 87, 86, 84, 83, 80, 76, 70, 62], 'gauge'),
      kpi(m, 362, 92, 318, 'Vibration (RMS)', (v) => v.toFixed(2).replace('.', ',') + ' mm/s', 7.84, '+38 %', false, [4.1, 4.3, 4.2, 4.6, 5.0, 5.6, 6.3, 7.1, 7.8], 'activity'),
      kpi(m, 696, 92, 318, 'Température huile', (v) => v.toFixed(1).replace('.', ',') + ' °C', 71.4, '+6,2 °C', false, [62, 63, 63, 64, 66, 67, 69, 70, 71], 'zap'),
      kpi(m, 1030, 92, 318, 'Rendement de l’usine', (v) => v.toFixed(1).replace('.', ',') + ' %', 87.4, '+2,1 pts', true, [82, 83, 85, 84, 86, 85, 87, 86, 87], 'trending-up'),
    ];
    const ch = card(m, 28, 216, 900, 400, 'Vibration · en direct', `<span style="display:flex;gap:14px"><span style="display:flex;align-items:center;gap:6px"><span style="width:14px;height:3px;background:${UM}"></span>Axe X</span><span style="display:flex;align-items:center;gap:6px"><span style="width:14px;height:3px;background:#9AA6FF"></span>Axe Y</span></span>`);
    const r = seeded(9);
    const N = 90;
    const vx = Array.from({ length: N }, (_, i) => 4.2 + 0.5 * Math.sin(i * 0.4) + r() * 0.5 + (i > 62 ? Math.pow((i - 62) / 28, 1.6) * 3.6 : 0));
    const vy = Array.from({ length: N }, (_, i) => 3.1 + 0.4 * Math.sin(i * 0.33 + 1) + r() * 0.4 + (i > 66 ? (i - 66) / 24 * 1.2 : 0));
    const xl = Array.from({ length: N }, (_, i) => (i % 15 === 0 ? `10:${String(30 + Math.floor(i / 6)).padStart(2, '0')}:${String((i % 6) * 10).padStart(2, '0')}` : ''));
    const LC = lineChart(ch, 14, 52, 872, 336, { series: [{ v: vx, c: UM }, { v: vy, c: '#9AA6FF' }], xl, ymin: 0, ymax: 10, yt: 5, fmt: (v) => v.toFixed(0), thr: 7.1 });
    const an = el(ch, { left: '0', top: '0', padding: '6px 10px', borderRadius: '6px', background: TX, color: '#fff', fontSize: '12px', fontWeight: '600', whiteSpace: 'nowrap', display: 'flex', alignItems: 'center', gap: '6px' }, `${icon('circle-alert', 12, 2.4, '#FF8A8A')} Anomalie détectée · 10:44:12`);
    const readout = el(ch, { left: '172px', top: '15px', fontSize: '13px', fontWeight: '700', color: '#CF3B3B', fontVariantNumeric: 'tabular-nums' }, '');
    // Autres machines du site.
    const as = card(m, 944, 216, 404, 400, 'Usine de Lyon · équipements', `<span style="color:${UM};font-weight:600">42 sites ▾</span>`);
    const AS = [['Presse n° 4', 62, 'bad', 'Alerte'], ['Presse n° 2', 91, 'ok', 'En marche'], ['Cellule de soudage W3', 78, 'warn', 'À surveiller'], ['Cabine de peinture P1', 94, 'ok', 'En marche'], ['Convoyeur C7', 71, 'warn', 'Usure courroie'], ['Robot R12', 96, 'ok', 'En marche']];
    AS.forEach(([n, v, k, t], j) => {
      const rr = el(as, { left: '0', top: 48 + j * 57 + 'px', width: '404px', height: '57px', borderTop: '1px solid #F0EFEC', background: j === 0 ? '#FCF5F5' : '#fff' });
      el(rr, { left: '18px', top: '10px', fontSize: '13px', fontWeight: '600' }, n);
      el(rr, { left: '18px', top: '32px', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '12px', color: TX2 }, `${bar(v / 100, k === 'ok' ? '#1C8C4E' : k === 'warn' ? '#D9890F' : '#CF3B3B', 150)}${v}`);
      el(rr, { right: '16px', top: '17px' }, badge(t, k));
    });
    // Ordres de travail.
    const wo = card(m, 28, 632, 1320, 296, 'Ordres de travail', `<span style="display:flex;align-items:center;gap:8px">${btn('Tous les sites', false, 'building-2')}${btn('Priorité', false, 'list-checks')}</span>`);
    const WO = [[22, 'Karim Benali', 'OT-1287', 'Presse n° 4 · vibration au-dessus du seuil', 'Lyon', ['Haute', 'bad'], 'SLA', ['Assigné', 'info']], [18, 'Hugo Laurent', 'OT-1286', 'Robot R2 · température du réducteur', 'Łódź', ['Moyenne', 'warn'], '1 j 04:20', ['En cours', 'warn']], [17, 'Kwame Mensah', 'OT-1281', 'Convoyeur C7 · changer la courroie', 'Turin', ['Moyenne', 'warn'], '2 j 11:05', ['Pièces commandées', 'neu']], [12, 'Julien Leroy', 'OT-1279', 'Cabine P1 · changement de filtre', 'Lyon', ['Basse', 'neu'], '5 j 02:40', ['Planifié', 'neu']]];
    const TW = table(wo, 0, 50, 1320, [['ID', 100], ['MACHINE ET PROBLÈME', 380], ['SITE', 110], ['PRIORITÉ', 130], ['TECHNICIEN', 230], ['DÉLAI RESTANT', 170], ['STATUT', 150]],
      WO.map(([k, n, id, t, site, pr, sla, st]) => [`<b style="font-weight:600;color:${UM}">${id}</b>`, t, site, badge(pr[0], pr[1]), `${photo(k, 26)}${n}`, `<span class="sla" style="font-variant-numeric:tabular-nums">${sla}</span>`, badge(st[0], st[1])]), 52);
    return {
      root: s.root,
      update(T, t0) {
        const u = clamp((T - t0 + 0.6) / 1.9);
        LC.draw(lerp(0.62, 1, u));
        K.forEach((k, i) => { k.n.textContent = k.fmt(k.v * (0.9 + 0.1 * P(T, t0 + i * 0.05, 1.0))); });
        const idx = Math.max(1, Math.round(lerp(0.62, 1, u) * (N - 1)));
        readout.textContent = `${vx[idx].toFixed(2).replace('.', ',')} mm/s`;
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
    const s = shell(parent, { app: ['Revenue Desk', 'target'], mark: INK, active: 1, crumbs: ['Ventes', 'Pipeline', 'EMEA'], title: 'Pipeline · EMEA', user: [4, 'Elena Rossi'],
      nav: [['layout-dashboard', 'Vue d’ensemble'], ['target', 'Pipeline'], ['building-2', 'Comptes'], ['users', 'Contacts'], ['calendar', 'Activités', '8'], ['chart-column', 'Prévisions'], ['settings', 'Paramètres']],
      sub: `12,6 M€ en cours · 92 affaires · objectif du trimestre <b style="color:${TX}">4,2 M€</b>`, actions: btn('Responsable : tous', false, 'users') + btn('Clôture : ce trimestre', false, 'calendar') + btn('Nouvelle affaire', true, 'plus') });
    const COL = [['Prospect', '3,1 M€', 42], ['Qualifié', '2,8 M€', 27], ['Proposition', '3,9 M€', 14], ['Négociation', '1,9 M€', 6], ['Gagné', '0,9 M€', 3]];
    const D = [[['Atlas Retail', '42 k€', 8, '12 déc.'], ['Vertex Industries', '18 k€', 3, '18 déc.'], ['Kappa Chemicals', '64 k€', 17, '9 janv.']], [['Helio Pharma', '96 k€', 4, '28 nov.'], ['Nordic Steel', '51 k€', 12, '4 déc.'], ['Rhine Logistics', '33 k€', 8, '15 déc.']],
      [['Orion Bank', '120 k€', 4, '21 nov.'], ['Bluebay Energy', '75 k€', 12, '30 nov.'], ['Alpha Plastics', '28 k€', 3, '2 déc.']], [['Northwind Foods', '210 k€', 8, '14 nov.'], ['Celta Telecom', '88 k€', 17, '19 nov.']], [['Iberia Motors', '140 k€', 12, '3 nov.'], ['Lumen Health', '62 k€', 4, '29 oct.']]];
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
    const s = shell(parent, { app: ['Stock Room', 'layers'], mark: UM, active: 1, crumbs: ['Entrepôts', 'Entrepôt de Lyon', 'Inventaire'], title: 'Inventaire · Entrepôt de Lyon', user: [12, 'Fatou Diallo'],
      nav: [['layout-dashboard', 'Vue d’ensemble'], ['layers', 'Inventaire'], ['inbox', 'Réceptions', '6'], ['send', 'Expéditions'], ['circle-alert', 'Réassorts', '!3'], ['building-2', 'Fournisseurs'], ['chart-column', 'Rapports']],
      sub: live('Synchronisé avec la gestion il y a 1 min') + `<span style="color:${TX3}">·</span>2 418 références`, actions: btn('Toutes catégories', false, 'list-checks') + btn('Créer une commande', true, 'plus') });
    const m = s.main;
    kpi(m, 28, 92, 318, 'Valeur du stock', () => '8,42 M€', 0, '+1,8 %', true, [7.9, 8.0, 8.1, 8.0, 8.2, 8.3, 8.2, 8.4, 8.4], 'banknote');
    kpi(m, 362, 92, 318, 'Couverture', () => '23,6 jours', 0, '−1,2 jour', false, [27, 26, 26, 25, 25, 24, 24, 24, 23.6], 'clock');
    kpi(m, 696, 92, 318, 'Sous le seuil de réassort', () => '3 réf.', 0, '+2', false, [0, 1, 1, 0, 1, 1, 2, 2, 3], 'circle-alert');
    kpi(m, 1030, 92, 318, 'Fiabilité des commandes', () => '99,6 %', 0, '+0,2 pt', true, [99, 99.2, 99.1, 99.3, 99.4, 99.3, 99.5, 99.5, 99.6], 'circle-check');
    const tc = card(m, 28, 216, 1320, 712, 'Niveaux de stock');
    const RW = [['SKU-10482', 'Flexible hydraulique 1/2" · 2 m', 'Pièces détachées', 0.18, '214', '420', ['Réassort', 'bad'], 'CMD-8812 · 6 nov.'], ['SKU-20915', 'Roulement 6205-2RS', 'Pièces détachées', 0.24, '388', '600', ['Réassort', 'bad'], '—'], ['SKU-33107', 'Gants de sécurité · T9', 'Protection', 0.31, '1 240', '1 500', ['Réassort', 'bad'], '—'],
      ['SKU-11873', 'Film étirable 23 µm', 'Emballage', 0.56, '86 rouleaux', '60', ['OK', 'ok'], '—'], ['SKU-40221', 'Huile moteur 5W-30 · 20 L', 'Consommables', 0.72, '142', '80', ['OK', 'ok'], 'CMD-8790 · 12 nov.'], ['SKU-28644', 'Bande de convoyeur 800 mm', 'Pièces détachées', 0.44, '12', '10', ['Bas', 'warn'], 'CMD-8805 · 4 nov.'], ['SKU-50317', 'Étiquettes 100 × 150', 'Emballage', 0.88, '3 600', '1 200', ['OK', 'ok'], '—'], ['SKU-36118', 'Filtre à air F7', 'Pièces détachées', 0.62, '96', '50', ['OK', 'ok'], '—'], ['SKU-19904', 'Batterie de chariot 48 V', 'Équipement', 0.4, '6', '4', ['Bas', 'warn'], '—'], ['SKU-27460', 'Colliers de serrage 300 mm', 'Consommables', 0.93, '9 800', '2 000', ['OK', 'ok'], '—'], ['SKU-44012', 'Gilet haute visibilité · L', 'Protection', 0.52, '310', '200', ['OK', 'ok'], '—'], ['SKU-31775', 'Cartouche de graisse 400 g', 'Consommables', 0.67, '520', '300', ['OK', 'ok'], '—']];
    const TB2 = table(tc, 0, 50, 1320, [['RÉF.', 130], ['ARTICLE', 300], ['CATÉGORIE', 150], ['NIVEAU', 250], ['EN STOCK', 110, 'right'], ['SEUIL', 120, 'right'], ['STATUT', 110], ['À RECEVOIR', 130]],
      RW.map(([sk, n, c, v, oh, ra, st, inb]) => [`<b style="font-weight:600;color:${UM}">${sk}</b>`, n, c, bar(v, v < 0.35 ? '#CF3B3B' : v < 0.5 ? '#D9890F' : UM, 200, 7), oh, ra, badge(st[0], st[1]), `<span style="color:${TX2}">${inb}</span>`]), 52);
    return { root: s.root, update(T, t0) { TB2.R.forEach((r, j) => { const p = P(T, t0 + j * 0.03, 0.4); S(r, { o: p }); }); } };
  }

  // ---------- 6. Planning des équipes ----------
  function shifts(parent) {
    const s = shell(parent, { app: ['Shift Planner', 'calendar'], mark: INK, active: 1, crumbs: ['Usine de Lyon', 'Plannings', 'Semaine 45'], title: 'Semaine 45 · 3 – 9 nov.', user: [20, 'Paul Martin'],
      nav: [['layout-dashboard', 'Vue d’ensemble'], ['calendar', 'Planning'], ['users', 'Équipe', '64'], ['clock', 'Absences', '5'], ['circle-alert', 'Postes à pourvoir', '!2'], ['chart-column', 'Rapports']],
      sub: `Couverture <b style="color:#1C8C4E">97 %</b><span style="color:${TX3}">·</span>2 postes à pourvoir`, actions: btn('Ligne : presses et soudure', false, 'list-checks') + btn('Publier le planning', true, 'send') });
    const m = s.main;
    const c = card(m, 28, 92, 1320, 836, null);
    const DAYS = ['Lun. 3', 'Mar. 4', 'Mer. 5', 'Jeu. 6', 'Ven. 7', 'Sam. 8', 'Dim. 9'];
    const X0 = 250, DW = 152;
    DAYS.forEach((d, i) => el(c, { left: X0 + i * DW + 'px', top: '0', width: DW + 'px', height: '44px', borderLeft: '1px solid #F0EFEC', borderBottom: `1px solid ${BD}`, background: '#FAFAF8', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '12.5px', fontWeight: '600', color: i > 4 ? TX3 : TX2 }, d));
    el(c, { left: '0', top: '0', width: X0 + 'px', height: '44px', borderBottom: `1px solid ${BD}`, background: '#FAFAF8', display: 'flex', alignItems: 'center', padding: '0 18px', fontSize: '11.5px', fontWeight: '600', color: TX2 }, 'COLLABORATEUR');
    const PPL = [[22, 'Karim Benali', 'Technicien'], [1, 'Mateo García', 'Opérateur'], [17, 'Kwame Mensah', 'Opérateur'], [2, 'Inès Duval', 'Cheffe de ligne'], [18, 'Hugo Laurent', 'Soudeur'], [13, 'Sofia Alves', 'Qualité'], [3, 'Tom Becker', 'Opérateur'], [11, 'Emma Novak', 'Soudeuse'], [23, 'Ryan Cole', 'Maintenance'], [14, 'Léa Bernard', 'Opératrice']];
    const G = ['MMMEE..', 'EEMMM..', 'NNN..MM', 'MMMMM..', 'EE.NNN.', 'MMEEM..', '..MMMEE', 'NNEE..M', 'MM.MMEE', 'EEEMM..'];
    const SH = { M: ['06:00 – 14:00', '#E8ECFF', UM], E: ['14:00 – 22:00', '#F1F1EF', '#3C3C42'], N: ['22:00 – 06:00', '#16161C', '#fff'] };
    const blocks = [];
    PPL.forEach(([k, n, r], j) => {
      const y = 44 + j * 76;
      el(c, { left: '0', top: y + 'px', width: '1320px', height: '76px', borderBottom: '1px solid #F0EFEC' });
      el(c, { left: '18px', top: y + 20 + 'px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '13px' }, who(k, n, r));
      G[j].split('').forEach((g, i) => {
        if (g === '.') return;
        const b = el(c, { left: X0 + i * DW + 6 + 'px', top: y + 10 + 'px', width: DW - 12 + 'px', height: '56px', borderRadius: '7px', background: SH[g][1], color: SH[g][2], padding: '9px 11px', fontSize: '12px', fontWeight: '600', lineHeight: '1.45', borderLeft: `3px solid ${g === 'N' ? LIME : SH[g][2]}` }, `${SH[g][0]}<br><span style="font-weight:500;opacity:0.75">${['Presses', 'Soudure', 'Peinture'][(i + j) % 3]}</span>`);
        blocks.push(b);
      });
    });
    const open = el(c, { left: X0 + 5 * DW + 6 + 'px', top: 44 + 3 * 76 + 10 + 'px', width: DW - 12 + 'px', height: '56px', borderRadius: '7px', border: '2px dashed #CF3B3B', background: '#FCF5F5', color: '#CF3B3B', fontSize: '12px', fontWeight: '700', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '6px' }, `${icon('plus', 13, 2.6, '#CF3B3B')}Poste à pourvoir`);
    return { root: s.root, update(T, t0) { blocks.forEach((b, i) => { const p = P(T, t0 + (i % 30) * 0.015, 0.4); S(b, { o: p, s: 0.96 + 0.04 * p }); }); S(open, { o: 0.6 + 0.4 * Math.sin(T * 5) }); } };
  }

  // ---------- Portail client généré dans la démo (même interface que la scène d'origine : v, dk, kp, grid, cards) ----------
  function portal(parent, dash) {
    const PW = 1180, PH = 800;
    const v = el(parent, { left: '0', top: '0', width: PW + 'px', height: PH + 'px', background: BG, overflow: 'hidden', fontFamily: 'DM Sans', color: TX, transformOrigin: '0 0' });
    const sb = el(v, { left: '0', top: '0', width: '190px', height: PH + 'px', background: SIDE, borderRight: `1px solid ${BD}` });
    el(sb, { left: '16px', top: '16px', display: 'flex', alignItems: 'center', gap: '9px', fontSize: '14px', fontWeight: '700' }, `<span style="width:28px;height:28px;border-radius:7px;background:${UM};display:flex;align-items:center;justify-content:center">${icon('briefcase', 15, 2.2, '#fff')}</span>Portail client`);
    [['layout-dashboard', 'Tableau de bord'], ['building-2', 'Clients'], ['folder', 'Documents'], ['clipboard-check', 'Validations', '5'], ['message-square', 'Messages', '2'], ['settings', 'Paramètres']].forEach(([ic, l, b], i) => el(sb, { left: '8px', top: 66 + i * 32 + 'px', width: '174px', height: '28px', borderRadius: '6px', background: i === 1 ? '#EEF0FF' : 'transparent', display: 'flex', alignItems: 'center', gap: '9px', padding: '0 9px', fontSize: '12.5px', fontWeight: i === 1 ? '600' : '500', color: i === 1 ? UM : '#3C3C42' }, `${icon(ic, 14, 2, i === 1 ? UM : TX2)}${l}${b ? `<span style="margin-left:auto;font-size:10.5px;font-weight:700;color:${TX2};background:#ECECE9;border-radius:8px;padding:1px 6px">${b}</span>` : ''}`));
    const top = el(v, { left: '190px', top: '0', width: PW - 190 + 'px', height: '50px', background: '#fff', borderBottom: `1px solid ${BD}` });
    el(top, { left: '22px', top: '16px', fontSize: '12.5px', color: TX2 }, `Clients <span style="color:${TX3}">/</span> <b style="color:${TX}">Tous les clients</b>`);
    el(top, { right: '18px', top: '10px', display: 'flex', alignItems: 'center', gap: '12px' }, `<span style="display:flex;align-items:center;gap:6px;height:30px;width:200px;padding:0 10px;border:1px solid ${BD};border-radius:6px;font-size:12px;color:${TX3}">${icon('search', 13, 2, TX3)}Rechercher un client</span>${photo(19, 28)}`);
    const body = el(v, { left: '190px', top: '50px', width: PW - 190 + 'px', height: PH - 50 + 'px' });
    const dk = el(body, { left: '22px', top: '18px', width: '946px', height: '108px' });
    const KP = [['Clients actifs', '24', '+3', 'users'], ['En intégration', '9', '+2', 'loader-circle'], ['Délai moyen d’intégration', '6,5 j', '−1,2 j', 'timer'], ['Validations en attente', '5', '−2', 'clipboard-check']];
    const kp = KP.map(([l, val, d, ic], i) => {
      const c = el(dk, { left: i * 240 + 'px', top: '0', width: '226px', height: '96px', background: '#fff', border: `1px solid ${BD}`, borderRadius: '9px', transformOrigin: '50% 50%' });
      el(c, { left: '14px', top: '13px', display: 'flex', alignItems: 'center', gap: '6px', fontSize: '12px', color: TX2 }, `${icon(ic, 13, 2, TX3)}${l}`);
      el(c, { left: '14px', top: '38px', fontSize: '24px', fontWeight: '700' }, val);
      el(c, { left: '14px', top: '70px', fontSize: '11.5px', fontWeight: '600', color: ST.ok[1] }, `${d} <span style="color:${TX3};font-weight:500">ce mois-ci</span>`);
      el(c, { right: '12px', top: '40px' }, spark([3, 4, 3, 5, 6, 5, 7, 8], 70, 28, UM));
      return c;
    });
    const grid = el(body, { left: '22px', top: dash ? '142px' : '22px', width: '946px', height: '560px', background: '#fff', border: `1px solid ${BD}`, borderRadius: '10px', overflow: 'hidden' });
    tabs(grid, 14, 14, ['Tous <b>24</b>', 'En intégration <b>9</b>', 'Documents manquants <b>4</b>', 'Validés <b>11</b>']);
    el(grid, { right: '14px', top: '12px' }, btn('Ajouter un client', true, 'plus'));
    const CL = [['Northwind Logistics', 19, 'Claire Dubois', 'En cours', 'info', 0.6, '6/10', '14 nov.'], ['Bluebay Energy', 20, 'Mark Allen', 'Validé', 'ok', 1, '10/10', '2 nov.'], ['Atlas Retail', 13, 'Sofia Rossi', 'Docs manquants', 'warn', 0.35, '3/10', '20 nov.'],
      ['Helio Pharma', 15, 'Jonas Weber', 'En cours', 'info', 0.72, '7/10', '12 nov.'], ['Vertex Industries', 21, 'Lena Novak', 'Validé', 'ok', 1, '10/10', '30 oct.'], ['Orion Bank', 12, 'Paul Martin', 'Docs manquants', 'warn', 0.2, '2/10', '26 nov.'], ['Celta Telecom', 9, 'Ana Ruiz', 'En cours', 'info', 0.5, '5/10', '18 nov.'], ['Kappa Chemicals', 5, 'Ian Brooks', 'En cours', 'info', 0.4, '4/10', '22 nov.']];
    const TT = table(grid, 0, 56, 946, [['CLIENT', 230], ['CONTACT', 200], ['STATUT', 150], ['DOCUMENTS', 190], ['ÉCHÉANCE', 90], ['', 50]],
      CL.map(([n, k, c, st, kk, pv, dc, due]) => [`<span style="width:30px;height:30px;border-radius:7px;background:#F1F1EF;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#3C3C42">${n.split(' ').map((w) => w[0]).join('')}</span><b style="font-weight:600">${n}</b>`, `${photo(k, 24)}${c}`, badge(st, kk), `${bar(pv, pv === 1 ? '#1C8C4E' : UM, 110)}<span style="color:${TX2};font-size:12px">${dc}</span>`, `<span style="color:${TX2}">${due}</span>`, icon('ellipsis', 16, 2, TX3)]), 58);
    if (!dash) dk.style.display = 'none';
    return { v, dk, kp, grid, cards: TT.R };
  }

  U.APP = { people, fleet, plants, crm, stock, shifts, portal, W, H };
  // Briques réutilisées par les vidéos qui construisent leurs propres écrans (vidéo KMC).
  U.APPK = { shell, card, kpi, table, badge, btn, bar, spark, lineChart, barChart, tabs, photo, who, live, ST, W, H, SB, TB, CW };
})();

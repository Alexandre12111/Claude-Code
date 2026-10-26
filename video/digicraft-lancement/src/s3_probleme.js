(function () {
  const { h, S, P, Ease, lerp, clamp, words, revealWords, hideWords, icon, spring } = E;

  window.dcIcon = function (size, id) {
    const g = `dcg${id}`;
    const d = (cx, cy, r) => `<path d="M${cx} ${cy - r} L${cx + r} ${cy} L${cx} ${cy + r} L${cx - r} ${cy} Z" fill="url(#${g})"/>`;
    return `<svg width="${size}" height="${size}" viewBox="0 0 100 100"><defs><linearGradient id="${g}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#B7A6FF"/><stop offset="1" stop-color="#6D5DF6"/></linearGradient></defs>${d(50, 50, 17)}${d(50, 14, 9)}${d(50, 86, 9)}${d(14, 50, 9)}${d(86, 50, 9)}</svg>`;
  };

  const CARDS = [
    ['Notes de frais', 'receipt', '#2E8BC0', 400, 320],
    ['Onboarding RH', 'user-check', '#8F8AFF', 960, 290],
    ['Planning des équipes', 'calendar', '#FF6633', 1500, 320],
    ['Reporting mensuel', 'chart-column', '#022446', 600, 470],
    ['Validation des devis', 'badge-check', '#2E8BC0', 1330, 470],
    ['Suivi des congés', 'plane', '#8F8AFF', 370, 625],
    ['Tableau de bord DG', 'layout-dashboard', '#FF6633', 960, 650],
    ['Inventaire matériel', 'layers', '#022446', 1550, 625],
  ];
  const MONTHS = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'];
  const LANE = { x: 180, y: 800, w: 1560, h: 150 };
  const GATE = { x: LANE.x + LANE.w - 80, y: LANE.y + LANE.h / 2 };
  window.G = window.G || {};
  window.G.gate = GATE;
  let els = {};

  SCENES.push({
    id: 'probleme',
    build(root) {
      root.style.background = 'var(--offwhite)';
      const grid = h('div', 'abs bg-grid-light', root);
      Object.assign(grid.style, { inset: '-64px' });
      els.grid = grid;
      els.root = root;

      const tbox = h('div', 'abs', root);
      Object.assign(tbox.style, { left: '0', width: '1920px', top: '96px', textAlign: 'center', color: 'var(--navy)', fontSize: '66px', fontWeight: '800', letterSpacing: '-0.01em' });
      els.tA = words(tbox, [{ t: 'Vos' }, { t: 'équipes' }, { t: 'ne' }, { t: 'manquent' }, { t: 'pas' }, { t: "d'idées.", c: 'hl' }]);
      const tbox2 = h('div', 'abs', root);
      Object.assign(tbox2.style, { left: '0', width: '1920px', top: '96px', textAlign: 'center', color: 'var(--navy)', fontSize: '66px', fontWeight: '800', letterSpacing: '-0.01em' });
      els.tB = words(tbox2, [{ t: 'Elles' }, { t: 'manquent' }, { t: "d'un" }, { t: 'chemin', c: 'hl' }, { t: 'pour' }, { t: 'les' }, { t: 'construire.' }]);

      const lane = h('div', 'abs', root);
      Object.assign(lane.style, { left: LANE.x + 'px', top: LANE.y + 'px', width: LANE.w + 'px', height: LANE.h + 'px', borderRadius: '75px', background: '#E9EEF4', border: '2px dashed #C3CFDB', transformOrigin: '0 50%' });
      els.lane = lane;
      const ll = h('div', 'abs', root, `${icon('hourglass', 34, 2.2, '#4A5568')}<div><div style="font-size:24px;font-weight:700;color:#022446">En attente</div><div style="font-size:20px;font-weight:500;color:#4A5568">d'un créneau IT</div></div>`);
      Object.assign(ll.style, { left: LANE.x + 44 + 'px', top: LANE.y + 40 + 'px', display: 'flex', gap: '16px', alignItems: 'center' });
      els.ll = ll;
      els.hg = ll.querySelector('svg');

      const mc = h('div', 'abs', root);
      Object.assign(mc.style, { left: LANE.x + LANE.w - 430 + 'px', top: LANE.y - 78 + 'px', height: '56px', padding: '0 24px', borderRadius: '28px', background: '#fff', boxShadow: '0 8px 24px rgba(2,36,70,0.10)', display: 'flex', alignItems: 'center', gap: '14px', fontSize: '24px', fontWeight: '700', color: 'var(--navy)' });
      mc.innerHTML = `${icon('calendar', 26, 2.2, '#FF6633')}<span style="color:#4A5568;font-weight:500">Toujours en attente :</span>`;
      const mv = h('span', '', mc);
      Object.assign(mv.style, { display: 'inline-block', minWidth: '120px', color: '#FF6633' });
      els.mc = mc; els.mv = mv;

      const gate = h('div', 'abs', root);
      Object.assign(gate.style, { left: GATE.x - 56 + 'px', top: GATE.y - 56 + 'px', width: '112px', height: '112px', borderRadius: '56px', background: 'var(--navy)', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 12px 30px rgba(2,36,70,0.25)' });
      els.gate = gate;
      els.lock = h('div', 'abs', gate, icon('lock', 48, 2.2, '#fff'));
      Object.assign(els.lock.style, { left: '32px', top: '30px' });
      els.dc = h('div', 'abs', gate, window.dcIcon(84, 'g'));
      Object.assign(els.dc.style, { left: '14px', top: '14px' });

      els.cards = CARDS.map(([label, ic, col, x, y], i) => {
        const c = h('div', 'abs', root);
        c.innerHTML = `<div style="width:68px;height:68px;border-radius:18px;background:${col}1A;display:flex;align-items:center;justify-content:center">${icon(ic, 36, 2.2, col)}</div><div style="font-size:32px;font-weight:700;color:#022446;white-space:nowrap">${label}</div>`;
        Object.assign(c.style, { left: '0', top: '0', display: 'flex', alignItems: 'center', gap: '18px', padding: '18px 30px 18px 18px', borderRadius: '22px', background: '#fff', boxShadow: '0 14px 40px rgba(2,36,70,0.10), 0 2px 6px rgba(2,36,70,0.06)', border: '1px solid #E6ECF2' });
        c._home = { x, y };
        return c;
      });
      els.laneTargets = CARDS.map((_, i) => ({ x: LANE.x + 470 + i * 118, y: LANE.y + LANE.h / 2 }));
    },
    update(T) {
      E.slash(T, 12.95, 0.75, els.root, els.bands || (els.bands = E.slashBands(els.root)));
      S(els.grid, { x: -T * 6, y: -T * 4 });

      revealWords(T, els.tA, 13.55, 0.07, 0.6);
      hideWords(T, els.tA, 15.5, 0.03, 0.4);
      revealWords(T, els.tB, 15.62, 0.07, 0.6);
      hideWords(T, els.tB, 19.25, 0.025, 0.35);

      const laneP = P(T, 15.7, 0.7, Ease.outExpo);
      const laneOut = P(T, 19.4, 0.4, Ease.inCubic);
      S(els.lane, { sx: Math.max(0.0001, laneP), o: (laneP > 0 ? 1 : 0) * (1 - laneOut) });
      const llp = P(T, 15.95, 0.5, Ease.outCubic);
      S(els.ll, { x: (1 - llp) * -30, o: llp * (1 - laneOut) });
      S(els.hg, { r: Math.floor(Math.max(0, T - 16) / 0.9) * 180 + 180 * P((T - 16) % 0.9, 0.55, 0.35, Ease.inOutCubic) });

      const mcp = P(T, 16.3, 0.5, Ease.outBack);
      S(els.mc, { s: Math.max(0.0001, 0.8 + 0.2 * mcp), o: mcp * (1 - laneOut), y: (1 - mcp) * 20 });
      const mi = clamp(Math.floor((T - 16.5) / 0.5), 0, MONTHS.length - 1);
      els.mv.textContent = MONTHS[mi];
      const flip = (T - 16.5) % 0.5;
      S(els.mv, { y: T > 16.5 && T < 19 ? lerp(-12, 0, P(flip, 0, 0.18, Ease.outCubic)) : 0 });

      const gp = spring(T - 15.9, 200, 16);
      const lockOut = P(T, 18.9, 0.3, Ease.inBack);
      const dcIn = spring(T - 19.05, 220, 14);
      const zoom = P(T, 19.75, 0.8, Ease.inExpo);
      S(els.gate, { s: Math.max(0.0001, gp * (1 + zoom * 30)), o: gp > 0.01 ? 1 : 0 });
      els.gate.style.background = T > 19.05 ? '#fff' : 'var(--navy)';
      S(els.lock, { s: Math.max(0.0001, 1 - lockOut), o: 1 - lockOut });
      S(els.dc, { s: Math.max(0.0001, dcIn), r: (1 - dcIn) * -90, o: T > 19.05 ? 1 : 0 });

      els.cards.forEach((c, i) => {
        const t0 = 13.9 + i * 0.13;
        const pop = spring(T - t0, 210, 15);
        const float = Math.sin(T * 1.3 + i * 1.7) * 6;
        const q = P(T, 15.9 + i * 0.1, 0.95, Ease.inOutCubic);
        const tg = els.laneTargets[i];
        const drift = P(T, 16.9, 2.2, Ease.inOutSine) * 14;
        const fly = P(T, 19.2 + (7 - i) * 0.035, 0.5, Ease.inExpo);
        let x = lerp(c._home.x, tg.x + drift, q);
        let y = lerp(c._home.y + float, tg.y, q);
        let s = lerp(1, 0.5, q) * (0.6 + 0.4 * pop);
        x = lerp(x, GATE.x, fly);
        y = lerp(y, GATE.y, fly);
        s *= 1 - fly * 0.95;
        const w = c.offsetWidth, hh = c.offsetHeight;
        const gray = P(T, 16.6 + i * 0.1, 1.6, Ease.inOutSine) * (1 - P(T, 18.9, 0.3));
        S(c, { x: x - w / 2, y: y - hh / 2 + (1 - pop) * 50, s: Math.max(0.0001, s), o: clamp((T - t0) * 8) * (1 - P(T, 19.55, 0.2)), f: gray > 0.01 ? `grayscale(${gray}) opacity(${1 - gray * 0.35})` : '' , r: q * (i % 2 ? 2 : -2) * (1 - fly) });
      });
    },
  });
})();

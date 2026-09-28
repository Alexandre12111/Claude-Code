(function () {
  // Temps internes hérités de la V1 (remappés par la timeline).
  const { h, S, P, Ease, lerp, clamp, words, revealWords, hideWords, icon, spring } = E;
  const { el, warmBg } = C;

  const HUBS = {
    DG: { x: 960, y: 505, col: '#EB6739', ic: 'target' },
    DAF: { x: 430, y: 520, col: '#1E9BD7', ic: 'landmark' },
    DRH: { x: 1490, y: 520, col: '#8B5CF6', ic: 'users' },
  };
  const CARDS = [
    ['Pilotage des KPI', 'gauge', 'DG', 960, 300],
    ['Projets stratégiques', 'folder-kanban', 'DG', 960, 735],
    ['Reporting mensuel', 'chart-column', 'DAF', 330, 305],
    ['Validation des devis', 'badge-check', 'DAF', 260, 720],
    ['Prévisionnel de trésorerie', 'wallet', 'DAF', 560, 870],
    ['Onboarding RH', 'user-plus', 'DRH', 1600, 305],
    ['Plan de formation', 'graduation-cap', 'DRH', 1670, 720],
    ['Entretiens annuels', 'clipboard-check', 'DRH', 1370, 870],
  ];
  const MONTHS = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'];
  const LANE = { x: 180, y: 800, w: 1560, h: 150 };
  const GATE = { x: LANE.x + LANE.w - 80, y: LANE.y + LANE.h / 2 };
  let a = {};

  SCENES.push({
    id: 'idees',
    build(root) {
      a.root = root;
      a.bg = warmBg(root, 1);
      const T1 = el(root, { left: '0', width: '1920px', top: '92px', textAlign: 'center', color: 'var(--navy)', fontSize: '72px' });
      T1.classList.add('nh');
      a.tA = words(T1, [{ t: 'Vos' }, { t: 'équipes' }, { t: 'ne' }, { t: 'manquent' }, { t: 'pas' }, { t: "d'idées.", c: 'o' }]);
      const T2 = el(root, { left: '0', width: '1920px', top: '92px', textAlign: 'center', color: 'var(--navy)', fontSize: '72px' });
      T2.classList.add('nh');
      a.tB = words(T2, [{ t: 'Elles' }, { t: 'manquent' }, { t: "d'un" }, { t: 'outil', c: 'o' }, { t: 'pour' }, { t: 'les' }, { t: 'construire.' }]);

      const ns = 'http://www.w3.org/2000/svg';
      const svg = document.createElementNS(ns, 'svg');
      svg.setAttribute('width', '1920'); svg.setAttribute('height', '1080');
      Object.assign(svg.style, { position: 'absolute', left: '0', top: '0' });
      root.appendChild(svg);
      a.svg = svg;
      a.lines = CARDS.map(([, , hub, x, y]) => {
        const H = HUBS[hub];
        const l = document.createElementNS(ns, 'line');
        l.setAttribute('x1', H.x); l.setAttribute('y1', H.y); l.setAttribute('x2', x); l.setAttribute('y2', y);
        l.setAttribute('stroke', H.col); l.setAttribute('stroke-width', '2.5'); l.setAttribute('stroke-dasharray', '6 8'); l.setAttribute('opacity', '0.55');
        svg.appendChild(l);
        l._len = Math.hypot(x - H.x, y - H.y);
        return l;
      });
      a.hubs = Object.entries(HUBS).map(([k, H]) => {
        const b = el(root, { left: H.x - 78 + 'px', top: H.y - 78 + 'px', width: '156px', height: '156px', borderRadius: '78px', background: H.col, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '4px', color: '#fff', boxShadow: `0 20px 44px ${H.col}55` },
          `${icon(H.ic, 44, 2.2, '#fff')}<div class="nh" style="font-size:38px;line-height:1">${k}</div>`);
        return b;
      });

      const lane = el(root, { left: LANE.x + 'px', top: LANE.y + 'px', width: LANE.w + 'px', height: LANE.h + 'px', borderRadius: '75px', background: '#F3EEEA', border: '2px dashed #E3D3C6', transformOrigin: '0 50%' });
      a.lane = lane;
      a.ll = el(root, { left: LANE.x + 44 + 'px', top: LANE.y + 40 + 'px', display: 'flex', gap: '16px', alignItems: 'center' }, `${icon('hourglass', 34, 2.2, '#4A5568')}<div><div style="font-size:24px;font-weight:700;color:#012D48">En attente</div><div style="font-size:20px;font-weight:500;color:#4A5568">d'un créneau informatique</div></div>`);
      a.hg = a.ll.querySelector('svg');
      a.mc = el(root, { left: LANE.x + LANE.w - 450 + 'px', top: LANE.y - 78 + 'px', height: '56px', padding: '0 24px', borderRadius: '28px', background: '#fff', boxShadow: '0 8px 24px rgba(1,45,72,0.10)', display: 'flex', alignItems: 'center', gap: '14px', fontSize: '24px', fontWeight: '700', color: 'var(--navy)' },
        `${icon('calendar', 26, 2.2, '#EB6739')}<span style="color:#4A5568;font-weight:500">Toujours en attente :</span>`);
      a.mv = h('span', '', a.mc);
      Object.assign(a.mv.style, { display: 'inline-block', minWidth: '120px', color: '#EB6739' });
      const gate = el(root, { left: GATE.x - 56 + 'px', top: GATE.y - 56 + 'px', width: '112px', height: '112px', borderRadius: '56px', background: 'var(--navy)', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 12px 30px rgba(1,45,72,0.25)' });
      a.gate = gate;
      a.lock = el(gate, { left: '32px', top: '30px' }, icon('lock', 48, 2.2, '#fff'));
      a.dc = el(gate, { left: '14px', top: '14px' }, window.dcIcon(84, 'g4', 'brand'));

      a.cards = CARDS.map(([label, ic, hub, x, y]) => {
        const col = HUBS[hub].col;
        const c = el(root, { left: '0', top: '0', display: 'flex', alignItems: 'center', gap: '16px', padding: '16px 28px 16px 16px', borderRadius: '22px', background: '#fff', boxShadow: '0 14px 40px rgba(1,45,72,0.10), 0 2px 6px rgba(1,45,72,0.06)', border: '1px solid #EFE6DF', whiteSpace: 'nowrap' },
          `<div style="width:62px;height:62px;border-radius:17px;background:${col}1F;display:flex;align-items:center;justify-content:center">${icon(ic, 32, 2.2, col)}</div><div style="font-size:30px;font-weight:700;color:#012D48">${label}</div>`);
        c._home = { x, y };
        return c;
      });
      a.laneTargets = CARDS.map((_, i) => ({ x: LANE.x + 470 + i * 118, y: LANE.y + LANE.h / 2 }));
    },
    update(T, realT) {
      E.slash(realT, 13.2, 0.6, a.root, a.bands || (a.bands = E.slashBands(a.root, ['#EB6739', '#012D48'])));
      a.bg(T);
      revealWords(T, a.tA, 13.55, 0.07, 0.6);
      hideWords(T, a.tA, 15.5, 0.03, 0.4);
      revealWords(T, a.tB, 15.62, 0.07, 0.6);
      hideWords(T, a.tB, 19.25, 0.025, 0.35);

      const hubOut = P(T, 15.55, 0.4, Ease.inCubic);
      a.hubs.forEach((b, i) => {
        const p = spring(T - (13.65 + i * 0.1), 230, 15);
        S(b, { s: Math.max(0.0001, p * (1 - hubOut * 0.6)), o: clamp((T - 13.65 - i * 0.1) * 8) * (1 - hubOut) });
      });
      a.lines.forEach((l, i) => {
        const p = P(T, 13.85 + i * 0.07, 0.5, Ease.outCubic);
        l.setAttribute('x2', lerp(+l.getAttribute('x1'), CARDS[i][3], p));
        l.setAttribute('y2', lerp(+l.getAttribute('y1'), CARDS[i][4], p));
        l.style.opacity = String((p > 0 ? 0.55 : 0) * (1 - hubOut));
      });

      const laneP = P(T, 15.7, 0.7, Ease.outExpo);
      const laneOut = P(T, 19.4, 0.4, Ease.inCubic);
      S(a.lane, { sx: Math.max(0.0001, laneP), o: (laneP > 0 ? 1 : 0) * (1 - laneOut) });
      const llp = P(T, 15.95, 0.5, Ease.outCubic);
      S(a.ll, { x: (1 - llp) * -30, o: llp * (1 - laneOut) });
      S(a.hg, { r: Math.max(0, T - 16) * 200 });
      const mcp = P(T, 16.3, 0.5, Ease.outBack);
      S(a.mc, { s: Math.max(0.0001, 0.8 + 0.2 * mcp), o: mcp * (1 - laneOut), y: (1 - mcp) * 20 });
      a.mv.textContent = MONTHS[clamp(Math.floor((T - 16.5) / 0.45), 0, MONTHS.length - 1)];

      const gp = spring(T - 15.9, 200, 16);
      const lockOut = P(T, 18.9, 0.3, Ease.inBack);
      const dcIn = spring(T - 19.05, 220, 14);
      const zoom = P(T, 19.45, 0.5, Ease.inExpo);
      S(a.gate, { s: Math.max(0.0001, gp * (1 + zoom * 30)), o: gp > 0.01 ? 1 : 0 });
      a.gate.style.background = T > 19.05 ? '#fff' : 'var(--navy)';
      S(a.lock, { s: Math.max(0.0001, 1 - lockOut), o: 1 - lockOut });
      S(a.dc, { s: Math.max(0.0001, dcIn), r: (1 - dcIn) * -90, o: T > 19.05 ? 1 : 0 });

      a.cards.forEach((c, i) => {
        const t0 = 13.95 + i * 0.1;
        const pop = spring(T - t0, 210, 15);
        const float = Math.sin(T * 1.3 + i * 1.7) * 6;
        const q = P(T, 15.9 + i * 0.1, 0.95, Ease.inOutCubic);
        const tg = a.laneTargets[i];
        const fly = P(T, 19.2 + (7 - i) * 0.03, 0.45, Ease.inExpo);
        let x = lerp(c._home.x, tg.x + P(T, 16.9, 2.2, Ease.inOutSine) * 14, q);
        let y = lerp(c._home.y + float, tg.y, q);
        let s = lerp(1, 0.5, q) * (0.6 + 0.4 * pop);
        x = lerp(x, GATE.x, fly); y = lerp(y, GATE.y, fly); s *= 1 - fly * 0.95;
        const gray = P(T, 16.6 + i * 0.1, 1.6, Ease.inOutSine) * (1 - P(T, 18.9, 0.3));
        S(c, { x: x - c.offsetWidth / 2, y: y - c.offsetHeight / 2 + (1 - pop) * 50, s: Math.max(0.0001, s), o: clamp((T - t0) * 8) * (1 - P(T, 19.5, 0.2)), f: gray > 0.01 ? `grayscale(${gray}) opacity(${1 - gray * 0.35})` : '', r: q * (i % 2 ? 2 : -2) * (1 - fly) });
      });
    },
  });
})();

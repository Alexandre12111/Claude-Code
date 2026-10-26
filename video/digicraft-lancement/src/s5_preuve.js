(function () {
  const { h, S, P, Ease, lerp, clamp, words, revealWords, icon, spring, fmt, chars } = E;
  const el = (parent, style, html) => { const e = h('div', 'abs', parent, html); Object.assign(e.style, style); return e; };
  let els = {};
  const CARDS = [
    { n: 751, cap: 'collaborateurs Leyton<br>l’utilisent déjà', ic: 'users', t: 37.9, c0: 38.1, c1: 1.8 },
    { n: 129, cap: 'applications<br>en ligne', ic: 'app-window', t: 42.0, c0: 42.2, c1: 1.3 },
    { n: 0, cap: 'développeur<br>mobilisé', ic: 'circle-check', t: 44.55, c0: 44.8, c1: 0.1 },
  ];

  SCENES.push({
    id: 'preuve',
    build(root) {
      els.root = root;
      root.classList.add('bg-navy');
      const grid = el(root, { inset: '-64px' });
      grid.className = 'abs bg-grid-dark';
      els.grid = grid;
      const wm = h('img', 'abs', root);
      wm.src = '../assets/img/logo/x_watermark.png';
      Object.assign(wm.style, { width: '1100px', height: '1100px', left: '-300px', top: '100px', opacity: '0.35' });
      els.wm = wm;
      els.ring = el(root, { borderRadius: '50%', border: '22px solid var(--blue)', boxSizing: 'border-box' });

      const head = el(root, { left: '0', width: '1920px', top: '96px', textAlign: 'center', color: '#fff' });
      const lab = h('div', 'label on-dark', head);
      lab.style.fontSize = '26px';
      lab.style.marginBottom = '22px';
      els.lab = chars(lab, 'Client zéro : Leyton');
      Object.assign(head.style, { fontSize: '74px', fontWeight: '800', letterSpacing: '-0.01em' });
      els.title = words(head, [{ t: 'Pas' }, { t: 'une' }, { t: 'promesse.' }, { t: 'Une', c: 'hl-light' }, { t: 'preuve.', c: 'hl-light' }]);

      els.cards = CARDS.map((c, i) => {
        const card = el(root, { left: 120 + i * 580 + 'px', top: '340px', width: '520px', height: '560px', borderRadius: '30px', background: 'linear-gradient(180deg, rgba(255,255,255,0.075), rgba(255,255,255,0.03))', border: '1.5px solid rgba(255,255,255,0.12)', overflow: 'hidden' });
        el(card, { left: '32px', top: '30px', width: '64px', height: '64px', borderRadius: '18px', background: 'rgba(107,184,230,0.14)', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(c.ic, 34, 2, '#6BB8E6'));
        const vis = el(card, { left: '32px', top: '118px', width: '456px', height: '190px' });
        card._num = el(card, { left: '30px', top: '300px', fontSize: '160px', fontWeight: '800', color: '#fff', letterSpacing: '-0.03em', lineHeight: '1' });
        el(card, { left: '34px', top: '470px', fontSize: '28px', fontWeight: '600', color: '#C9D9E8', lineHeight: '1.3' }, c.cap);
        card._vis = vis;
        return card;
      });

      const r = E.seeded(7);
      const v1 = els.cards[0]._vis;
      els.dots = [];
      for (let i = 0; i < 751; i++) {
        const cx = i % 46, cy = Math.floor(i / 46);
        const d = el(v1, { left: cx * 10 + 'px', top: cy * 11 + 'px', width: '6px', height: '6px', borderRadius: '3px', background: '#fff' });
        d._k = (cx / 46) * 0.55 + r() * 0.45;
        els.dots.push(d);
      }
      const v2 = els.cards[1]._vis;
      const cols = ['#6BB8E6', '#8F8AFF', '#FF6633', '#FFFFFF', '#2E8BC0'];
      els.tiles = [];
      for (let i = 0; i < 129; i++) {
        const cx = i % 16, cy = Math.floor(i / 16);
        const t = el(v2, { left: cx * 28.5 + 'px', top: cy * 21 + 'px', width: '24px', height: '17px', borderRadius: '4px', background: 'rgba(255,255,255,0.10)', overflow: 'hidden', border: '1px solid rgba(255,255,255,0.18)' });
        el(t, { left: '0', top: '0', width: '24px', height: '5px', background: cols[Math.floor(r() * cols.length)] });
        t._d = Math.hypot(cx - 7.5, cy - 4) / 9;
        els.tiles.push(t);
      }
      const v3 = els.cards[2]._vis;
      els.code = el(v3, { left: '153px', top: '20px' }, icon('code-xml', 150, 1.6, '#6BB8E6'));
      els.strike = el(v3, { left: '128px', top: '93px', width: '200px', height: '10px', borderRadius: '5px', background: '#FF6633', transform: 'rotate(-35deg)', transformOrigin: '0 50%' });
      els.strikeWrap = els.strike;
    },
    update(T) {
      const ph = window.G.phone;
      const ip = P(T, 36.7, 0.7, Ease.inOutCubic);
      const R = lerp(0, 2300, ip);
      els.root.style.clipPath = ip < 1 ? `circle(${R}px at ${ph.x}px ${ph.y}px)` : 'none';
      Object.assign(els.ring.style, { left: ph.x - R + 'px', top: ph.y - R + 'px', width: 2 * R + 'px', height: 2 * R + 'px', display: ip > 0 && ip < 1 ? 'block' : 'none' });

      S(els.grid, { x: -T * 8, y: -T * 5 });
      S(els.wm, { r: -T * 1.2, x: T * 4 });
      els.lab._c.forEach((c, i) => { const p = P(T, 37.25 + i * 0.02, 0.5); S(c, { y: (1 - p) * 20, o: p }); });
      revealWords(T, els.title, 37.4, 0.09, 0.65);

      els.cards.forEach((card, i) => {
        const c = CARDS[i];
        const p = spring(T - c.t, 170, 17);
        S(card, { y: (1 - p) * 120, o: clamp((T - c.t) * 4), s: 0.94 + 0.06 * p });
        const cnt = P(T, c.c0, c.c1, Ease.outCubic) * c.n;
        card._num.textContent = fmt(cnt);
        if (i === 2) S(card._num, { s: Math.max(0.0001, spring(T - 44.8, 260, 13)), o: T > 44.8 ? 1 : 0 });
      });
      const lit = P(T, 38.1, 1.8, Ease.outCubic);
      els.dots.forEach((d) => {
        const on = lit > d._k * 0.98 ? 1 : 0;
        d.style.background = on ? '#6BB8E6' : 'rgba(255,255,255,0.14)';
      });
      els.tiles.forEach((t) => {
        const p = spring(T - (42.2 + t._d * 1.1), 260, 16);
        S(t, { s: Math.max(0.0001, p), o: clamp((T - (42.2 + t._d * 1.1)) * 8) });
      });
      const sp = P(T, 45.0, 0.4, Ease.outExpo);
      S(els.strike, { r: -35, sx: Math.max(0.0001, sp), o: sp > 0 ? 1 : 0 });
      S(els.code, { o: 1 - 0.45 * sp });
    },
  });
})();

(function () {
  const { h, S, P, Ease, lerp, clamp, words, revealWords, icon, spring, fmt } = E;
  const { el, navyBg } = C;
  let a = {};
  const CARDS = [
    { pre: '', n: 100, suf: ' %', lab: 'no code', ic: 'code-xml', t: 36.0 },
    { pre: '+', n: 750, suf: '', lab: 'utilisateurs', ic: 'users', t: 37.4 },
    { pre: '+', n: 350, suf: '', lab: 'applications déployées', ic: 'app-window', t: 38.8 },
  ];
  const SLOTS = [440, 960, 1480];

  SCENES.push({
    id: 'adopte',
    build(root) {
      a.root = root;
      a.bg = navyBg(root);
      a.ring = el(root, { borderRadius: '50%', border: '24px solid #EB6739', boxSizing: 'border-box' });
      const t = el(root, { left: '0', width: '1920px', top: '120px', textAlign: 'center', fontSize: '92px', color: '#fff' });
      t.classList.add('nh');
      a.title = words(t, [{ t: 'Déjà' }, { t: 'adopté' }, { t: 'par' }, { t: 'nos', c: 'o-light' }, { t: 'équipes.', c: 'o-light' }]);
      a.cards = CARDS.map((c) => {
        const k = el(root, { left: 960 - 320 + 'px', top: 520 - 190 + 'px', width: '640px', height: '380px', borderRadius: '40px', background: 'linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04))', border: '1.5px solid rgba(255,255,255,0.16)', boxShadow: '0 40px 80px rgba(0,0,0,0.25)', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '6px' });
        const ic = h('div', '', k, icon(c.ic, 52, 2, '#F4A071'));
        Object.assign(ic.style, { position: 'relative', width: '92px', height: '92px', borderRadius: '26px', background: 'rgba(235,103,57,0.18)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '6px' });
        if (c.ic === 'code-xml') {
          const st = h('div', '', ic);
          Object.assign(st.style, { position: 'absolute', left: '14px', top: '42px', width: '64px', height: '7px', borderRadius: '4px', background: '#EB6739', transform: 'rotate(-35deg)' });
        }
        k._num = h('div', 'nh', k);
        Object.assign(k._num.style, { fontSize: '160px', color: '#fff', lineHeight: '1' });
        const lab = h('div', '', k, c.lab);
        Object.assign(lab.style, { fontSize: '40px', fontWeight: '600', color: '#F8D3BC' });
        return k;
      });
    },
    update(T) {
      const ph = window.G.phone || { x: 1390, y: 548 };
      const ip = P(T, 34.8, 0.6, Ease.inOutCubic);
      const R = lerp(0, 2300, ip);
      a.root.style.clipPath = ip < 1 ? `circle(${R}px at ${ph.x}px ${ph.y}px)` : 'none';
      Object.assign(a.ring.style, { left: ph.x - R + 'px', top: ph.y - R + 'px', width: 2 * R + 'px', height: 2 * R + 'px', display: ip > 0 && ip < 1 ? 'block' : 'none' });
      a.bg(T);
      revealWords(T, a.title, 35.3, 0.09, 0.6);
      a.cards.forEach((k, i) => {
        const c = CARDS[i];
        const pop = spring(T - c.t, 200, 15);
        const dock = P(T, (CARDS[i + 1] ? CARDS[i + 1].t : 39.9) - 0.05, 0.55, Ease.inOutQuart);
        const x = lerp(0, SLOTS[i] - 960, dock);
        const y = lerp(0, 300, dock) - 190 * P(T, 39.95, 0.45, Ease.inOutQuart);
        const s = lerp(1, 0.6, dock) * (0.6 + 0.4 * pop);
        S(k, { x, y: y + (1 - pop) * 80, s: Math.max(0.0001, s), o: clamp((T - c.t) * 6) });
        const v = P(T, c.t + 0.05, 0.9, Ease.outCubic) * c.n;
        k._num.textContent = c.pre + fmt(v) + c.suf;
      });
    },
  });
})();

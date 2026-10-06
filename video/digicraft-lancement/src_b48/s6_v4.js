// V4 : messages B2B forts à l'écran et appel à l'action final.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon } = E;
  const el = C.el;
  // [début, fin, texte, x, y, taille]
  const WORDS = [
    [3.45, 4.5, 'No code.', 110, 930, 76], [4.85, 6.05, 'No IT ticket.', 110, 930, 76], [6.4, 7.6, 'No limits.', 110, 930, 76],
    [22.45, 23.5, 'Built in minutes.', 110, 70, 70], [26.45, 27.2, 'Just ask.', 1180, 860, 70],
    [35.9, 37.0, 'Enterprise‑grade.', 110, 930, 76], [38.15, 40.3, 'Secure by design.', 0, 22, 52],
  ];
  const a = {};
  SCENES.push({
    id: 'kinetic',
    build(root) {
      a.root = root;
      root.style.pointerEvents = 'none';
      a.w = WORDS.map(([t0, t1, txt, x, y, fs]) => {
        const c = el(root, { left: x ? x + 'px' : '0', top: y + 'px', width: x ? 'auto' : '1920px', display: 'flex', justifyContent: x ? 'flex-start' : 'center', opacity: '0' });
        const chip = h('div', 'nh', c);
        Object.assign(chip.style, { fontSize: fs + 'px', lineHeight: '1', color: '#fff', padding: `${fs * 0.26}px ${fs * 0.4}px ${fs * 0.2}px`, borderRadius: fs * 0.32 + 'px',
          background: 'linear-gradient(135deg, rgba(2,36,70,0.78), rgba(43,30,120,0.72))', boxShadow: '0 24px 60px rgba(1,20,40,0.35)', border: '1.5px solid rgba(255,255,255,0.18)', whiteSpace: 'nowrap', overflow: 'hidden' });
        const ch = E.chars(chip, txt);
        return { c, chip, ch: ch._c, t0, t1 };
      });
      // Appel à l'action final.
      a.cta = el(root, { left: '0', width: '1920px', top: '838px', display: 'flex', justifyContent: 'center', opacity: '0' });
      const b = h('div', 'ui', a.cta, `Request your demo ${icon('arrow-up-right', 26, 2.6, '#022446')}`);
      Object.assign(b.style, { display: 'flex', alignItems: 'center', gap: '14px', height: '72px', padding: '0 38px', borderRadius: '36px', background: 'linear-gradient(90deg,#FFFFFF,#E2F1FA)', color: '#022446', fontSize: '28px', fontWeight: '700', boxShadow: '0 20px 60px rgba(46,139,192,0.45)' });
      a.btn = b;
    },
    update(T) {
      a.w.forEach((w) => {
        if (T < w.t0 - 0.05 || T > w.t1 + 0.4) { w.c.style.opacity = '0'; return; }
        const pin = P(T, w.t0, 0.4, Ease.outQuint), pout = P(T, w.t1, 0.3, Ease.inCubic);
        S(w.c, { o: pin * (1 - pout), y: (1 - pin) * 30 - pout * 10, blur: pout * 10 });
        w.ch.forEach((c, i) => { const p = P(T, w.t0 + 0.05 + i * 0.02, 0.45, Ease.outQuint); S(c, { y: (1 - p) * 50, o: p }); });
      });
      const cp = P(T, 50.4, 0.6, Ease.outBack), co = P(T, 53.2, 0.8, Ease.inOutSine);
      S(a.cta, { o: clamp(cp) * (1 - co), y: (1 - clamp(cp)) * 30 });
      S(a.btn, { s: 1 + 0.02 * Math.sin(Math.max(0, T - 51) * 3) });
    },
  });
})();

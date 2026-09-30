(function () {
  const { h, S, P, Ease, lerp, clamp, words, revealWords, icon, spring, fmt } = E;
  const { el, navyBg, ringFx, glow } = C;
  let a = {};
  // Les trois cartes arrivent ensemble, comptent en même temps, puis se verrouillent en rafale.
  const CARDS = [
    { pre: '', n: 100, suf: ' %', lab: 'no code', ic: 'code-xml', t: 35.55 },
    { pre: '+', n: 750, suf: '', lab: 'Nutzer', ic: 'users', t: 35.63 },
    { pre: '+', n: 350, suf: '', lab: 'Apps im Einsatz', ic: 'app-window', t: 35.71 },
  ];
  const SLOTS = [400, 960, 1520];
  const SC = 0.8, CY = 580, COUNT_END = 36.85;

  SCENES.push({
    id: 'adopte',
    build(root) {
      a.root = root;
      a.bg = navyBg(root, 2);
      a.ringA = ringFx(root);
      a.ringB = ringFx(root);
      const t = el(root, { left: '0', width: '1920px', top: '130px', textAlign: 'center', fontSize: '80px', color: '#fff' });
      t.classList.add('nh');
      a.title = words(t, [{ t: 'Bereits' }, { t: 'von' }, { t: 'vielen', c: 'o-light' }, { t: 'Nutzern', c: 'o-light' }, { t: 'eingesetzt.' }]);
      a.cards = CARDS.map((c) => {
        const gl = glow(root, 760, 'rgba(46,139,192,0.42)', 0);
        const k = el(root, { left: 960 - 320 + 'px', top: 520 - 190 + 'px', width: '640px', height: '380px', borderRadius: '40px', background: 'linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.04))', border: '1.5px solid rgba(255,255,255,0.16)', boxShadow: '0 40px 80px rgba(0,0,0,0.25)', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '6px' });
        const ic = h('div', '', k, icon(c.ic, 52, 2, '#82BEE1'));
        Object.assign(ic.style, { position: 'relative', width: '92px', height: '92px', borderRadius: '26px', background: 'rgba(46,139,192,0.18)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '6px' });
        if (c.ic === 'code-xml') {
          const st = h('div', '', ic);
          Object.assign(st.style, { position: 'absolute', left: '14px', top: '42px', width: '64px', height: '7px', borderRadius: '4px', background: '#2E8BC0', transform: 'rotate(-35deg)' });
        }
        k._num = h('div', 'nh', k);
        Object.assign(k._num.style, { fontSize: '160px', lineHeight: '1', color: 'transparent', backgroundImage: 'linear-gradient(100deg, #FFFFFF 0%, #FFFFFF 42%, #CBE6F6 50%, #FFFFFF 58%, #FFFFFF 100%)', backgroundSize: '300% 100%', webkitBackgroundClip: 'text', backgroundClip: 'text' });
        k._gl = gl;
        const edge = h('div', '', k);
        Object.assign(edge.style, { position: 'absolute', left: '60px', right: '60px', top: '-1px', height: '3px', borderRadius: '2px', background: 'linear-gradient(90deg, rgba(143,198,231,0), #8FC6E7, rgba(143,198,231,0))' });
        const lab = h('div', '', k, c.lab);
        Object.assign(lab.style, { fontSize: '40px', fontWeight: '600', color: '#C4E0F0' });
        return k;
      });
    },
    update(T) {
      const ph = window.G.phone || { x: 1390, y: 548 };
      const ip = P(T, 34.8, 0.6, Ease.inOutCubic);
      const R = lerp(0, 2300, ip);
      a.root.style.clipPath = ip < 1 ? `circle(${R}px at ${ph.x}px ${ph.y}px)` : 'none';
      const on = ip > 0 && ip < 1;
      a.ringA(ph.x, ph.y, on ? R : 0, 30, T * 120, 1);
      a.ringB(ph.x, ph.y, on ? R * 0.86 : 0, 7, -T * 160, 0.7);
      a.bg(T);
      revealWords(T, a.title, 35.3, 0.09, 0.6);
      a.cards.forEach((k, i) => {
        const c = CARDS[i];
        const pop = spring(T - c.t, 210, 16);
        // verrouillage en rafale une fois les compteurs arrivés : petit coup d'échelle + éclat
        const lockT = COUNT_END + i * 0.11;
        const bump = T > lockT ? Math.exp(-(T - lockT) * 9) * Math.sin(Math.min(Math.PI, (T - lockT) * 14)) : 0;
        const x = SLOTS[i] - 960, y = CY - 520;
        const s = SC * (0.7 + 0.3 * pop) * (1 + 0.07 * bump);
        S(k, { x, y: y + (1 - pop) * 90, s: Math.max(0.0001, s), o: clamp((T - c.t) * 7) });
        const gp = P(T, c.t, 0.5, Ease.outCubic);
        const flash = T > lockT ? Math.exp(-(T - lockT) * 4) : 0;
        S(k._gl, { x: 960 + x, y: 520 + y, s: SC * (0.85 + 0.1 * Math.sin(T * 2 + i) + 0.25 * flash), o: gp * (0.45 + 0.55 * flash) });
        const v = P(T, c.t + 0.05, COUNT_END - c.t - 0.05 + i * 0.11, Ease.outCubic) * c.n;
        k._num.textContent = c.pre + fmt(v) + c.suf;
        k._num.style.backgroundPosition = `${lerp(100, 0, P(T, lockT, 0.8, Ease.inOutSine))}% 0`;
      });
    },
  });
})();

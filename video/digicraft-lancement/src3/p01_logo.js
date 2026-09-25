(function () {
  const { h, S, P, Ease, lerp, clamp, spring, chars } = E;
  const { el, warmBg, riseChars } = C;
  let a = {};
  const CX = 960, CY = 470;
  window.G = window.G || {};
  window.G.logoClip = (T) => lerp(0, 1500, Ease.inExpo(clamp((T - 3.0) / 0.6)));
  window.G.logoCenter = { x: CX, y: CY };

  SCENES.push({
    id: 'logo',
    build(root) {
      a.bg = warmBg(root);
      a.zoom = el(root, { inset: '0', transformOrigin: `${CX}px ${CY}px` });
      a.ring = h('img', 'abs', a.zoom);
      a.ring.src = '../assets/leyton/ring_particles.png';
      Object.assign(a.ring.style, { left: CX - 380 + 'px', top: CY - 407 + 'px', width: '759px', height: '814px', opacity: '0.55' });
      const lock = el(a.zoom, { left: '0', width: '1920px', top: CY - 110 + 'px', height: '220px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '44px' });
      const ic = h('div', '', lock);
      a.ic = ic;
      Object.assign(ic.style, { position: 'relative', width: '180px', height: '180px', flex: 'none' });
      const D = (cx, cy, r) => {
        const side = r * Math.SQRT2 * 1.8;
        return el(ic, { left: cx * 1.8 - side / 2 + 'px', top: cy * 1.8 - side / 2 + 'px', width: side + 'px', height: side + 'px', borderRadius: side * 0.12 + 'px', background: 'linear-gradient(135deg,#F8A87E,#EB6739)' });
      };
      a.dia = [D(50, 50, 17), D(50, 14, 9), D(86, 50, 9), D(50, 86, 9), D(14, 50, 9)];
      const word = h('div', 'nh', lock);
      Object.assign(word.style, { fontSize: '196px', color: 'var(--navy)', lineHeight: '1', paddingTop: '20px' });
      a.digi = chars(word, 'Digi');
      a.craft = chars(word, 'Craft');
      a.craft._c.forEach((c) => c.classList.add('o'));
      const by = el(a.zoom, { left: '0', width: '1920px', top: CY + 150 + 'px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '24px', fontSize: '30px', fontWeight: '600', color: '#6B7B88' });
      by.innerHTML = '<span>par</span>';
      C.leytonLogo(by, 70);
      a.by = by;
    },
    update(T) {
      if (!a.c0) {
        const r = a.ic.getBoundingClientRect();
        if (r.width) {
          a.c0 = { x: r.left + r.width / 2, y: r.top + r.height / 2 };
          window.G.logoCenter = a.c0;
          a.zoom.style.transformOrigin = `${a.c0.x}px ${a.c0.y}px`;
        }
      }
      a.bg(T);
      const rp = P(T, 0.0, 1.4, Ease.outCubic);
      S(a.ring, { s: lerp(0.7, 1, rp), r: lerp(-60, 0, rp) + T * 4, o: rp * 0.55 });
      const dirs = [[0, 0], [0, -1], [1, 0], [0, 1], [-1, 0]];
      a.dia.forEach((d, i) => {
        const t0 = 0.45 + (i ? 0.08 + i * 0.04 : 0);
        const p = spring(T - t0, 220, 15);
        const [dx, dy] = dirs[i];
        S(d, { x: dx * (1 - p) * 260, y: dy * (1 - p) * 260, r: 45 + (1 - p) * 225, s: Math.max(0.0001, i ? 0.3 + 0.7 * p : p), o: clamp((T - t0) * 8) });
      });
      riseChars(T, [...a.digi._c, ...a.craft._c], 0.7, 0.04, 0.7, 90);
      const bp = P(T, 1.55, 0.7, Ease.outQuint);
      S(a.by, { y: (1 - bp) * 30, o: bp });
      const z = Ease.inExpo(clamp((T - 3.0) / 0.6));
      S(a.zoom, { s: 1 + 0.03 * P(T, 0.5, 2.5, Ease.outSine) + z * 6, o: 1 - z * 0.6 });
    },
  });
})();

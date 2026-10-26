(function () {
  const { h, S, P, Ease, lerp, clamp, spring, chars } = E;
  const { el, warmBg, riseChars, shine, glow } = C;
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
      a.halo = glow(a.zoom, 900, 'rgba(251,250,246,0)', 0);
      a.ring = C.ycSketch(a.zoom, 1000, 'rgba(11,11,15,0.09)');
      Object.assign(a.ring.style, { left: CX - 500 + 'px', top: CY - 500 + 'px', opacity: '0' });
      const lock = el(a.zoom, { left: '0', width: '1920px', top: CY - 110 + 'px', height: '220px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '44px' });
      a.L = C.ycLogo(lock, 220);
      a.ic = a.L.sym;
      const by = el(a.zoom, { left: '0', width: '1920px', top: CY + 150 + 'px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '24px', fontSize: '30px', fontWeight: '600', color: '#8F8C86' });
      by.innerHTML = '<span>par</span>';
      C.leytonLogo(by, 100);
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
      S(a.ring, { s: lerp(0.9, 1, rp) + 0.012 * T, o: rp });
      C.ycLogoAnim(a.L, T, 0.45);
      riseChars(T, a.L.glyphs, 0.7, 0.04, 0.7, 90);
      const hp = P(T, 0.35, 1.6, Ease.outCubic);
      S(a.halo, { x: a.c0 ? a.c0.x : CX - 330, y: a.c0 ? a.c0.y : CY, s: 0.5 + 0.6 * hp + 0.04 * Math.sin(T * 2), o: hp * 0.9 });
      const bp = P(T, 1.55, 0.7, Ease.outQuint);
      S(a.by, { y: (1 - bp) * 30, o: bp });
      const z = Ease.inExpo(clamp((T - 3.0) / 0.6));
      S(a.zoom, { s: 1 + 0.03 * P(T, 0.5, 2.5, Ease.outSine) + z * 6, o: 1 - z * 0.6 });
    },
  });
})();

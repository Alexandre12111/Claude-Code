(function () {
  const { S, P, Ease, lerp, clamp, chars } = E;
  const { el, navyBg, slamChars, shine, glow } = C;
  let a = {};
  SCENES.push({
    id: 'cree',
    build(root) {
      a.root = root;
      a.bg = navyBg(root, 3);
      a.bands = E.slashBands(root, C.BANDS.peach);
      a.ringImg = E.h('img', 'abs', root);
      a.ringImg.src = '../assets/leyton/ring_particles_blue.png';
      Object.assign(a.ringImg.style, { left: 960 - 520 + 'px', top: 540 - 558 + 'px', width: '1040px', height: '1116px', opacity: '0' });
      a.gl = glow(root, 1400, 'rgba(46,139,192,0.35)', 0);
      const t = el(root, { left: '0', width: '1920px', top: '330px', textAlign: 'center', fontSize: '170px', color: '#fff', lineHeight: '1.05' });
      t.classList.add('nh');
      a.t = t;
      a.l1 = chars(el(t, { position: 'relative' }), 'Creato da voi,');
      a.l2 = chars(el(t, { position: 'relative' }), 'per voi.');
      a.l2._c.forEach((c) => c.classList.add('grad'));
    },
    update(T) {
      E.slash(T, 44.3, 0.55, a.root, a.bands);
      a.bg(T);
      slamChars(T, a.l1._c, 44.65, 0.03, 1.8);
      slamChars(T, a.l2._c, 45.05, 0.035, 1.8);
      shine(a.l2._c, T, 45.55, 0.9, 170);
      const gi = P(T, 44.6, 1.0, Ease.outCubic);
      S(a.gl, { x: 960, y: 560, s: 0.7 + 0.3 * gi, o: gi * 0.9 });
      S(a.ringImg, { r: T * 12, s: 0.85 + 0.15 * P(T, 44.5, 2, Ease.outCubic), o: 0.22 * P(T, 44.5, 0.8) * (1 - P(T, 46.05, 0.4)) });
      const z = P(T, 46.05, 0.5, Ease.inExpo);
      S(a.t, { s: 1 + 0.04 * P(T, 44.7, 1.4, Ease.outSine) + z * 1.5, o: 1 - z, blur: z * 12 });
    },
  });
})();

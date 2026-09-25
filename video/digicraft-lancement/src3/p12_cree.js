(function () {
  const { S, P, Ease, lerp, clamp, chars } = E;
  const { el, navyBg, slamChars } = C;
  let a = {};
  SCENES.push({
    id: 'cree',
    build(root) {
      a.root = root;
      a.bg = navyBg(root);
      a.bands = E.slashBands(root, ['#2E8BC0', '#6BB8E6']);
      const t = el(root, { left: '0', width: '1920px', top: '330px', textAlign: 'center', fontSize: '170px', color: '#fff', lineHeight: '1.05' });
      t.classList.add('nh');
      a.t = t;
      a.l1 = chars(el(t, { position: 'relative' }), 'Créé par vous,');
      a.l2 = chars(el(t, { position: 'relative' }), 'pour vous.');
      a.l2._c.forEach((c) => c.classList.add('grad'));
    },
    update(T) {
      E.slash(T, 44.3, 0.55, a.root, a.bands);
      a.bg(T);
      slamChars(T, a.l1._c, 44.65, 0.03, 1.8);
      slamChars(T, a.l2._c, 45.05, 0.035, 1.8);
      const z = P(T, 46.05, 0.5, Ease.inExpo);
      S(a.t, { s: 1 + 0.04 * P(T, 44.7, 1.4, Ease.outSine) + z * 1.5, o: 1 - z, blur: z * 12 });
    },
  });
})();

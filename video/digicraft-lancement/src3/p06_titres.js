(function () {
  const { h, S, P, Ease, lerp, clamp, words, chars, revealWords, hideWords, icon, spring } = E;
  const { el, slamChars } = C;
  let t = {};
  const step = (n, label) => `<span style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:23px;background:#2E8BC0;color:#fff;font-size:24px;font-weight:800;margin-right:16px">${n}</span>${label}`;

  SCENES.push({
    id: 'titres',
    z: 40,
    build(root) {
      const how = el(root, { left: '0', width: '1920px', top: '440px', textAlign: 'center', fontSize: '150px', color: 'var(--navy)' });
      how.classList.add('nh');
      t.howW = words(how, [{ t: 'Comment' }, { t: 'ça' }, { t: 'marche', c: 'o' }, { t: '?', c: 'o' }]);
      t.how = how;
      t.tag = el(root, { left: '60px', top: '48px', height: '58px', padding: '0 26px', borderRadius: '29px', background: 'var(--navy)', color: '#fff', display: 'flex', alignItems: 'center', gap: '12px', fontSize: '24px', fontWeight: '700', boxShadow: '0 12px 30px rgba(2,36,70,0.25)' }, `${icon('sparkles', 24, 2.2, '#6BB8E6')}Comment ça marche ?`);

      t.s1 = el(root, { left: '60px', top: '900px', height: '96px', padding: '0 40px 0 26px', borderRadius: '48px', background: '#fff', boxShadow: '0 20px 50px rgba(2,36,70,0.18)', display: 'flex', alignItems: 'center', fontSize: '40px', fontWeight: '700', color: 'var(--navy)' }, step(1, 'Décrivez votre besoin'));

      const b = el(root, { left: '80px', top: '300px', width: '600px' });
      t.b = b;
      t.b0 = el(b, { position: 'relative', fontSize: '30px', fontWeight: '700', color: 'var(--navy)', marginBottom: '26px', display: 'flex', alignItems: 'center' }, step(2, 'En direct'));
      const bt = el(b, { position: 'relative', fontSize: '104px', lineHeight: '1', color: 'var(--navy)' });
      bt.classList.add('nh');
      t.b1 = chars(el(bt, { position: 'relative' }), 'DigiCraft');
      t.b2 = chars(el(bt, { position: 'relative' }), 'construit.');
      t.b2._c.forEach((c) => c.classList.add('o'));
      t.bSub = el(b, { position: 'relative', marginTop: '28px', fontSize: '34px', fontWeight: '600', color: '#4A5568' }, 'Votre application prend forme sous vos yeux.');

      const o = el(root, { left: '110px', top: '300px', width: '760px' });
      t.o = o;
      t.o0 = el(o, { position: 'relative', fontSize: '30px', fontWeight: '700', color: 'var(--navy)', marginBottom: '26px', display: 'flex', alignItems: 'center' }, step(3, 'Publiez'));
      const ot = el(o, { position: 'relative', fontSize: '140px', lineHeight: '1', color: 'var(--navy)' });
      ot.classList.add('nh');
      t.o1 = chars(el(ot, { position: 'relative' }), 'En ligne.');
      t.o2 = chars(el(ot, { position: 'relative', fontSize: '104px', marginTop: '14px' }), 'Le jour même.');
      t.o2._c.forEach((c) => c.classList.add('o'));
    },
    update(T, RT) {
      T = RT;
      revealWords(T, t.howW, 22.2, 0.08, 0.55);
      const shrink = P(T, 23.1, 0.45, Ease.inOutQuart);
      S(t.how, { o: 1 - shrink, s: 1 - shrink * 0.6, y: -shrink * 380, blur: shrink * 6 });
      const tg = P(T, 23.35, 0.4, Ease.outExpo);
      S(t.tag, { x: (1 - tg) * -300, o: tg * (1 - P(T, 34.6, 0.3)) });

      const s1in = P(T, 24.3, 0.5, Ease.outExpo), s1out = P(T, 26.8, 0.3, Ease.inCubic);
      S(t.s1, { x: (1 - s1in) * -600 - s1out * 700, o: s1in > 0 ? 1 - s1out : 0 });

      const b0 = P(T, 27.45, 0.45, Ease.outQuint);
      S(t.b0, { y: (1 - b0) * 20, o: b0 });
      slamChars(T, t.b1._c, 27.6, 0.03, 1.5);
      slamChars(T, t.b2._c, 27.9, 0.03, 1.5);
      const sp = P(T, 28.4, 0.5, Ease.outQuint);
      S(t.bSub, { y: (1 - sp) * 30, o: sp });
      const bo = P(T, 31.3, 0.35, Ease.inCubic);
      S(t.b, { x: -bo * 300, o: 1 - bo, blur: bo * 10 });

      const o0 = P(T, 32.9, 0.45, Ease.outQuint);
      S(t.o0, { y: (1 - o0) * 20, o: o0 });
      slamChars(T, t.o1._c, 33.0, 0.03, 1.6);
      t.o2._c.forEach((c, i) => { const p = P(T, 33.35 + i * 0.025, 0.45, Ease.outExpo); S(c, { y: (1 - p) * 60, o: p }); });
      const oo = P(T, 34.6, 0.3, Ease.inCubic);
      S(t.o, { o: 1 - oo, x: -oo * 200 });
    },
  });
})();

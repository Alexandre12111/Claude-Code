(function () {
  const { h, S, P, Ease, lerp, clamp, words, chars, revealWords, hideWords, icon, spring } = E;
  const { el, slamChars, shine } = C;
  let t = {};
  const step = (n, label) => `<span style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:23px;background:#2E8BC0;color:#fff;font-size:24px;font-weight:800;margin-right:16px">${n}</span>${label}`;

  SCENES.push({
    id: 'titres',
    z: 40,
    build(root) {
      const how = el(root, { left: '0', width: '1920px', top: '440px', textAlign: 'center', fontSize: '150px', color: 'var(--navy)' });
      how.classList.add('nh');
      t.howW = words(how, [{ t: 'Hoe' }, { t: 'werkt', c: 'o' }, { t: 'het?', c: 'o' }]);
      t.how = how;
      // Intuitivité : sous-titre sous « Comment ça marche ? », emporté avec le titre.
      t.easy = el(how, { left: '0', width: '1920px', top: '190px', textAlign: 'center', fontFamily: 'Montserrat', fontSize: '46px', fontWeight: '600', letterSpacing: '0', color: '#4A5568' });
      t.easyW = words(t.easy, [{ t: 'Zo' }, { t: 'eenvoudig' }, { t: 'als' }, { t: 'een' }, { t: 'gesprek.', c: 'o' }]);
      t.tag = el(root, { left: '60px', top: '48px', height: '58px', padding: '0 26px', borderRadius: '29px', background: 'var(--navy)', color: '#fff', display: 'flex', alignItems: 'center', gap: '12px', fontSize: '24px', fontWeight: '700', boxShadow: '0 12px 30px rgba(1,45,72,0.25)' }, `${icon('sparkles', 24, 2.2, '#82BEE1')}Hoe werkt het?`);

      t.s1 = el(root, { left: '60px', top: '900px', height: '96px', padding: '0 40px 0 26px', borderRadius: '48px', background: '#fff', boxShadow: '0 20px 50px rgba(1,45,72,0.18)', display: 'flex', alignItems: 'center', fontSize: '40px', fontWeight: '700', color: 'var(--navy)' }, step(1, 'Beschrijf uw behoefte'));

      const b = el(root, { left: '80px', top: '300px', width: '600px' });
      t.b = b;
      t.b0 = el(b, { position: 'relative', fontSize: '30px', fontWeight: '700', color: 'var(--navy)', marginBottom: '26px', display: 'flex', alignItems: 'center' }, step(2, 'Live'));
      const bt = el(b, { position: 'relative', fontSize: '104px', lineHeight: '1', color: 'var(--navy)' });
      bt.classList.add('nh');
      t.b1 = chars(el(bt, { position: 'relative' }), 'DigiCraft');
      t.b2 = chars(el(bt, { position: 'relative' }), 'bouwt het.');
      t.b2._c.forEach((c) => c.classList.add('o'));
      t.bSub = el(b, { position: 'relative', marginTop: '28px', fontSize: '34px', fontWeight: '600', color: '#4A5568' }, 'Uw app krijgt vorm voor uw ogen.');

      const o = el(root, { left: '110px', top: '300px', width: '760px' });
      t.o = o;
      t.o0 = el(o, { position: 'relative', fontSize: '30px', fontWeight: '700', color: 'var(--navy)', marginBottom: '26px', display: 'flex', alignItems: 'center' }, step(3, 'Publiceer'));
      const ot = el(o, { position: 'relative', fontSize: '140px', lineHeight: '1', color: 'var(--navy)' });
      ot.classList.add('nh');
      t.o1 = chars(el(ot, { position: 'relative' }), 'Online.');
      t.o2 = chars(el(ot, { position: 'relative', fontSize: '104px', marginTop: '14px' }), 'Dezelfde dag.');
      t.o2._c.forEach((c) => c.classList.add('o'));
    },
    update(T, RT) {
      T = RT;
      revealWords(T, t.howW, 22.2, 0.08, 0.55);
      revealWords(T, t.easyW, 22.55, 0.07, 0.55);
      // Le grand titre se réduit et vole jusqu'à l'étiquette en haut à gauche (morphing).
      if (!t.m) {
        const rh = t.how.getBoundingClientRect(), rg = t.tag.getBoundingClientRect();
        t.m = { dx: rg.left + rg.width / 2 - (rh.left + rh.width / 2), dy: rg.top + rg.height / 2 - (rh.top + rh.height / 2), k: (rg.height * 0.62) / rh.height };
      }
      const shrink = P(T, 23.08, 0.34, Ease.inOutQuart);
      S(t.how, { x: t.m.dx * shrink, y: t.m.dy * shrink, s: lerp(1, t.m.k * 2.2, shrink), o: 1 - P(T, 23.26, 0.16), blur: shrink * 3 });
      const tg = P(T, 23.28, 0.22, Ease.outCubic);
      S(t.tag, { s: lerp(1.25, 1, tg), o: tg * (1 - P(T, 34.6, 0.3)) });

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

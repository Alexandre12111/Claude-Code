(function () {
  const { h, S, P, Ease, lerp, clamp, words, chars, revealWords, spring } = E;
  const { el, navyBg, riseChars, shine, glow, ringFx } = C;
  let a = {};

  SCENES.push({
    id: 'valeur',
    build(root) {
      a.root = root;
      a.bg = navyBg(root, 1);
      a.ring = ringFx(root);
      a.gV = glow(root, 1300, 'rgba(46,139,192,0.30)', 0);
      const W = { left: '0', width: '1920px', textAlign: 'center', color: '#fff' };
      const lab = el(root, { ...W, top: '250px', fontSize: '28px', fontWeight: '700', letterSpacing: '0.34em', textTransform: 'uppercase', color: '#82BEE1' });
      a.lab = chars(lab, 'Sedan 1997');
      a.lA = words(el(root, { ...W, top: '320px', fontSize: '92px' }), 'Leyton synliggör och tar tillvara det');
      a.lA.parentElement.classList.add('nh');
      a.vWrap = el(root, { ...W, top: '440px', fontSize: '176px', lineHeight: '1' });
      a.vWrap.classList.add('nh');
      a.v = chars(a.vWrap, 'VÄRDE');
      a.v._c.forEach((c) => c.classList.add('grad'));
      a.lC = words(el(root, { ...W, top: '640px', fontSize: '92px' }), 'som kunderna inte ser.');
      a.lC.parentElement.classList.add('nh');

      a.pWrap = el(root, { ...W, top: '300px', fontSize: '176px', lineHeight: '1' });
      a.pWrap.classList.add('nh');
      a.p = chars(a.pWrap, 'PRODUKTIVITET');
      a.p._c.forEach((c) => c.classList.add('grad'));
      const sub = el(root, { ...W, top: '520px', fontSize: '48px', fontWeight: '600', lineHeight: '1.3', color: '#E6EEF6' });
      a.s1 = words(sub, "Aujourd'hui, on construit celle que vous");
      a.s2 = words(sub, [{ t: "n'avez" }, { t: 'jamais' }, { t: 'eu' }, { t: 'le' }, { t: 'temps' }, { t: 'de' }, { t: 'bâtir.', c: 'o-light' }]);

      if (window.TL.v4) sub.style.display = 'none';
      const kit = el(root, { left: 960 - 280 + 'px', top: window.TL.v4 ? '560px' : '740px', width: '560px', height: '250px' });
      a.kit = kit;
      const blk = (x, y, w, hh, st) => el(kit, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px', borderRadius: '10px', background: 'rgba(255,255,255,0.08)', border: '1.5px solid rgba(255,255,255,0.22)', ...st });
      a.blocks = [blk(0, 0, 560, 34, {}), blk(0, 46, 110, 204, {}), blk(124, 46, 138, 70, { background: 'rgba(46,139,192,0.25)', borderColor: 'rgba(130,190,225,0.7)' }), blk(273, 46, 138, 70, {}), blk(422, 46, 138, 70, { background: 'rgba(182,221,243,0.2)', borderColor: 'rgba(182,221,243,0.6)' }), blk(124, 128, 436, 122, {})];
      a.bars = [40, 62, 50, 84, 70, 96].map((bh, i) => el(a.blocks[5], { left: 26 + i * 66 + 'px', bottom: '14px', width: '40px', height: bh + 'px', borderRadius: '6px 6px 2px 2px', background: i === 5 ? '#2E8BC0' : 'rgba(130,190,225,0.6)', transformOrigin: '50% 100%' }));
    },
    update(T) {
      const r = window.G.logoClip(T);
      const c = window.G.logoCenter;
      a.root.style.clipPath = T < 3.6 ? `circle(${r}px at ${c.x}px ${c.y}px)` : 'none';
      a.ring(c.x, c.y, T < 3.6 ? r : 0, 26, T * 90, 1);
      a.bg(T);
      // halo chaud derrière le mot fort, qui suit VALEUR puis PRODUCTIVITÉ
      const gIn = P(T, 4.3, 1.2, Ease.outCubic), gy = lerp(515, 375, P(T, 8.4, 0.75, Ease.inOutQuart));
      S(a.gV, { x: 960, y: gy, s: (0.55 + 0.35 * gIn) * (1 + 0.05 * Math.sin(T * 1.6)) * (1 + 0.25 * P(T, 9.2, 1.2)), o: gIn * 0.95 });
      riseChars(T, a.lab._c, 3.62, 0.03, 0.5, 24);
      revealWords(T, a.lA, 3.85, 0.09, 0.65);
      riseChars(T, a.v._c, 4.35, 0.05, 0.7, 120);
      shine(a.v._c, T, 5.3, 1.0, 170);
      revealWords(T, a.lC, 4.95, 0.08, 0.65);
      const sw = lerp(-20, 120, P(T, 5.4, 1.1, Ease.inOutCubic));
      const m = `linear-gradient(90deg, #000 ${sw - 12}%, rgba(0,0,0,0.25) ${sw + 8}%)`;
      a.lC.style.webkitMaskImage = m;
      a.lC.style.maskImage = m;

      const out = P(T, 8.3, 0.5, Ease.inCubic);
      [a.lA, a.lC].forEach((l) => S(l, { y: -out * 50, o: 1 - out, blur: out * 8 }));
      a.lab.style.opacity = String(1 - out);
      const mv = P(T, 8.4, 0.75, Ease.inOutQuart);
      S(a.vWrap, { y: mv * -140 });
      a.v._c.forEach((ch, i) => {
        if (T < 9.0) return;
        const p = P(T, 9.0 + i * 0.04, 0.5, Ease.inCubic);
        S(ch, { y: -p * 80, o: 1 - p, blur: p * 14 });
      });
      a.p._c.forEach((ch, i) => {
        const p = P(T, 9.2 + i * 0.04, 0.7, Ease.outQuint);
        S(ch, { y: (1 - p) * 90, o: p, blur: (1 - p) * 14 });
      });
      S(a.pWrap, { s: 1 + 0.07 * P(T, 9.4, 4.2, Ease.inOutSine) });
      shine(a.p._c, T, 10.15, 1.2, 190);
      if (T > 11.4) shine(a.p._c, T, 12.1, 1.1, 190);
      S(a.kit, { y: -12 * P(T, 11.1, 3, Ease.inOutSine), s: 1 + 0.04 * P(T, 11.1, 3, Ease.inOutSine) });
      revealWords(T, a.s1, 10.1, 0.1, 0.6);
      revealWords(T, a.s2, 10.7, 0.1, 0.6);
      a.blocks.forEach((b, i) => {
        const t0 = 11.1 + i * 0.13;
        const p = spring(T - t0, 190, 15);
        S(b, { y: (1 - p) * -70, o: clamp((T - t0) * 6) });
      });
      a.bars.forEach((b, i) => S(b, { sy: Math.max(0.0001, P(T, 11.9 + i * 0.07, 0.6, Ease.outBack) * (1 + 0.08 * Math.sin((T - 12.5) * 3 + i) * P(T, 12.5, 0.5))) }));
    },
  });
})();

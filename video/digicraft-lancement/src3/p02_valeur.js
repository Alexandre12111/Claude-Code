(function () {
  const { h, S, P, Ease, lerp, clamp, words, chars, revealWords, spring } = E;
  const { el, navyBg, riseChars } = C;
  let a = {};

  SCENES.push({
    id: 'valeur',
    build(root) {
      a.root = root;
      a.bg = navyBg(root);
      const W = { left: '0', width: '1920px', textAlign: 'center', color: '#fff' };
      const lab = el(root, { ...W, top: '250px', fontSize: '28px', fontWeight: '700', letterSpacing: '0.34em', textTransform: 'uppercase', color: '#6BB8E6' });
      a.lab = chars(lab, 'Depuis 1997');
      a.lA = words(el(root, { ...W, top: '320px', fontSize: '92px' }), 'Leyton révèle et capte la');
      a.lA.parentElement.classList.add('nh');
      a.vWrap = el(root, { ...W, top: '440px', fontSize: '176px', lineHeight: '1' });
      a.vWrap.classList.add('nh');
      a.v = chars(a.vWrap, 'VALEUR');
      a.v._c.forEach((c) => c.classList.add('grad'));
      a.lC = words(el(root, { ...W, top: '640px', fontSize: '92px' }), 'que ses clients ne voient pas.');
      a.lC.parentElement.classList.add('nh');

      a.pWrap = el(root, { ...W, top: '300px', fontSize: '176px', lineHeight: '1' });
      a.pWrap.classList.add('nh');
      a.p = chars(a.pWrap, 'PRODUCTIVITÉ');
      a.p._c.forEach((c) => c.classList.add('grad'));
      const sub = el(root, { ...W, top: '520px', fontSize: '48px', fontWeight: '600', lineHeight: '1.3', color: '#E6EEF6' });
      a.s1 = words(sub, "Aujourd'hui, on construit celle que vous");
      a.s2 = words(sub, [{ t: "n'avez" }, { t: 'jamais' }, { t: 'eu' }, { t: 'le' }, { t: 'temps' }, { t: 'de' }, { t: 'bâtir.', c: 'o-light' }]);

      const kit = el(root, { left: 960 - 280 + 'px', top: '740px', width: '560px', height: '250px' });
      a.kit = kit;
      const blk = (x, y, w, hh, st) => el(kit, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px', borderRadius: '10px', background: 'rgba(255,255,255,0.08)', border: '1.5px solid rgba(255,255,255,0.22)', ...st });
      a.blocks = [blk(0, 0, 560, 34, {}), blk(0, 46, 110, 204, {}), blk(124, 46, 138, 70, { background: 'rgba(46,139,192,0.25)', borderColor: 'rgba(107,184,230,0.7)' }), blk(273, 46, 138, 70, {}), blk(422, 46, 138, 70, { background: 'rgba(143,138,255,0.22)', borderColor: 'rgba(143,138,255,0.6)' }), blk(124, 128, 436, 122, {})];
      a.bars = [40, 62, 50, 84, 70, 96].map((bh, i) => el(a.blocks[5], { left: 26 + i * 66 + 'px', bottom: '14px', width: '40px', height: bh + 'px', borderRadius: '6px 6px 2px 2px', background: i === 5 ? '#2E8BC0' : 'rgba(107,184,230,0.6)', transformOrigin: '50% 100%' }));
    },
    update(T) {
      const r = window.G.logoClip(T);
      const c = window.G.logoCenter;
      a.root.style.clipPath = T < 3.6 ? `circle(${r}px at ${c.x}px ${c.y}px)` : 'none';
      a.bg(T);
      riseChars(T, a.lab._c, 3.75, 0.03, 0.5, 24);
      revealWords(T, a.lA, 4.1, 0.09, 0.65);
      riseChars(T, a.v._c, 4.6, 0.05, 0.7, 120);
      revealWords(T, a.lC, 5.2, 0.08, 0.65);
      const sw = lerp(-20, 120, P(T, 5.6, 1.1, Ease.inOutCubic));
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
      S(a.pWrap, { s: 1 + 0.02 * P(T, 9.9, 3.5, Ease.inOutSine) });
      revealWords(T, a.s1, 10.1, 0.1, 0.6);
      revealWords(T, a.s2, 10.7, 0.1, 0.6);
      a.blocks.forEach((b, i) => {
        const t0 = 11.1 + i * 0.13;
        const p = spring(T - t0, 190, 15);
        S(b, { y: (1 - p) * -70, o: clamp((T - t0) * 6) });
      });
      a.bars.forEach((b, i) => S(b, { sy: Math.max(0.0001, P(T, 11.9 + i * 0.07, 0.6, Ease.outBack)) }));
    },
  });
})();

(function () {
  const { h, S, P, Ease, lerp, clamp, words, chars, revealWords } = E;
  let els = {};
  let bigPos = null;

  SCENES.push({
    id: 'valeur',
    build(root) {
      root.classList.add('bg-navy');
      const grid = h('div', 'abs bg-grid-dark', root);
      Object.assign(grid.style, { inset: '-64px' });
      els.grid = grid;
      const wm = h('img', 'abs', root);
      wm.src = '../assets/img/logo/x_watermark.png';
      Object.assign(wm.style, { width: '1250px', height: '1250px', left: '1050px', top: '-120px', opacity: '0.55' });
      els.wm = wm;

      const box = h('div', 'abs', root);
      Object.assign(box.style, { left: '0', width: '1920px', top: '300px', textAlign: 'center', color: '#fff' });
      const lab = h('div', 'label on-dark', box);
      els.lab = chars(lab, 'Depuis 1997');
      lab.style.marginBottom = '34px'; lab.style.fontSize = '30px';
      const big = { fontSize: '100px', fontWeight: '800', lineHeight: '1.12', letterSpacing: '-0.01em' };
      els.lA = words(box, [{ t: 'Leyton' }, { t: 'trouve' }, { t: 'la' }, { t: 'valeur', c: 'hl-light vword' }]);
      Object.assign(els.lA.style, big);
      els.lB = words(box, 'que vous ne voyiez pas.');
      Object.assign(els.lB.style, big);
      els.vword = els.lA.querySelector('.vword');

      els.ul = h('div', 'abs', root);
      Object.assign(els.ul.style, { height: '8px', borderRadius: '4px', background: 'var(--blue-light)', transformOrigin: '0 50%' });

      els.bigWrap = h('div', 'abs', root);
      Object.assign(els.bigWrap.style, { left: '0', width: '1920px', top: '0', textAlign: 'center', fontWeight: '800', color: 'var(--blue-light)' });
      els.bigV = chars(els.bigWrap, 'valeur');
      els.bigP = h('div', 'abs', root);
      Object.assign(els.bigP.style, { left: '0', width: '1920px', top: '300px', textAlign: 'center', fontWeight: '800', fontSize: '156px', color: 'var(--blue-light)', letterSpacing: '-0.02em' });
      els.prod = chars(els.bigP, 'productivité');

      const sub = h('div', 'abs', root);
      Object.assign(sub.style, { left: '0', width: '1920px', top: '520px', textAlign: 'center', color: '#E6EEF6', fontSize: '50px', fontWeight: '600', lineHeight: '1.3' });
      els.s1 = words(sub, [{ t: "Aujourd'hui," }, { t: 'on' }, { t: 'construit' }, { t: 'celle' }, { t: 'que' }, { t: 'vous' }]);
      els.s2 = words(sub, [{ t: "n'avez" }, { t: 'jamais' }, { t: 'eu' }, { t: 'le' }, { t: 'temps' }, { t: 'de' }, { t: 'bâtir.', c: 'hl-light' }]);

      // Mini tableau de bord qui s'assemble : l'idée de « construire ».
      const kit = h('div', 'abs', root);
      Object.assign(kit.style, { left: 960 - 280 + 'px', top: '740px', width: '560px', height: '250px' });
      els.kit = kit;
      const blk = (x, y, w, hh, style) => {
        const b = h('div', 'abs', kit);
        Object.assign(b.style, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px', borderRadius: '10px', background: 'rgba(255,255,255,0.08)', border: '1.5px solid rgba(255,255,255,0.22)', ...style });
        return b;
      };
      els.blocks = [
        blk(0, 0, 560, 34, {}),
        blk(0, 46, 110, 204, {}),
        blk(124, 46, 138, 70, { background: 'rgba(107,184,230,0.22)', borderColor: 'rgba(107,184,230,0.6)' }),
        blk(273, 46, 138, 70, {}),
        blk(422, 46, 138, 70, { background: 'rgba(143,138,255,0.22)', borderColor: 'rgba(143,138,255,0.6)' }),
        blk(124, 128, 436, 122, {}),
      ];
      els.bars = [];
      const heights = [40, 62, 50, 84, 70, 96];
      heights.forEach((bh, i) => {
        const b = h('div', 'abs', els.blocks[5]);
        Object.assign(b.style, { left: 26 + i * 66 + 'px', bottom: '14px', width: '40px', height: bh + 'px', borderRadius: '6px 6px 2px 2px', background: i === 5 ? 'var(--blue-light)' : 'rgba(107,184,230,0.55)', transformOrigin: '50% 100%' });
        els.bars.push(b);
      });
    },
    update(T) {
      const root = els.grid.parentElement;
      const r = window.G.logoZoomClip(T);
      if (T < 3.9) root.style.clipPath = `circle(${Math.max(0, r)}px at ${window.G.oCenter.x}px ${window.G.oCenter.y}px)`;
      else root.style.clipPath = 'none';

      S(els.grid, { x: -T * 8, y: -T * 5 });
      S(els.wm, { r: T * 1.5, x: -T * 6, s: 1 + T * 0.004 });

      els.lab._c.forEach((c, i) => {
        const p = P(T, 3.95 + i * 0.03, 0.5, Ease.outCubic);
        S(c, { y: (1 - p) * 24, o: p });
      });
      revealWords(T, els.lA, 4.95, 0.1, 0.7);
      revealWords(T, els.lB, 6.25, 0.08, 0.7);

      const vr = els.vword.getBoundingClientRect();
      if (!bigPos && vr.width > 0 && T > 5.8) bigPos = { x: vr.left, y: vr.top, w: vr.width, h: vr.height };
      const pu = P(T, 5.75, 0.55, Ease.outExpo);
      Object.assign(els.ul.style, { left: vr.left + 'px', top: vr.bottom + 2 + 'px', width: vr.width + 'px' });
      S(els.ul, { sx: Math.max(0.0001, pu), o: pu > 0 && T < 7.75 ? 1 : 0 });

      // Balayage lumineux : la valeur cachée apparaît.
      const sw = lerp(-20, 120, P(T, 6.55, 1.0, Ease.inOutCubic));
      const m = `linear-gradient(90deg, #000 ${sw - 12}%, rgba(0,0,0,0.22) ${sw + 8}%)`;
      els.lB.style.webkitMaskImage = m;
      els.lB.style.maskImage = m;

      // Sortie : tout disparaît sauf « valeur », qui prend le centre.
      const out = P(T, 7.7, 0.5, Ease.inCubic);
      if (out > 0) {
        S(els.lB, { y: -out * 40, o: 1 - out, blur: out * 8 });
        els.lab.style.opacity = String(1 - out);
        els.lA._w.forEach((w, i) => {
          if (i < 3) S(w, { y: -out * 60, o: 1 - out });
          else S(w, { o: 0 });
        });
      } else {
        S(els.lB, { y: 0, o: 1, blur: 0 });
        els.lab.style.opacity = '1';
        if (els.lA._w[3]) els.lA._w[3].style.visibility = 'visible';
      }

      const showBig = T >= 7.7 && bigPos;
      els.bigWrap.style.display = showBig ? 'block' : 'none';
      if (showBig) {
        const mv = P(T, 7.75, 0.8, Ease.inOutQuart);
        const fs0 = 100, fs1 = 156;
        const fs = lerp(fs0, fs1, mv);
        els.bigWrap.style.fontSize = fs + 'px';
        const cx0 = bigPos.x + bigPos.w / 2;
        const top = lerp(bigPos.y, 300, mv);
        els.bigWrap.style.top = top + 'px';
        S(els.bigWrap, { x: lerp(cx0 - 960, 0, mv) });
        els.bigV._c.forEach((c, i) => {
          const p = P(T, 8.8 + i * 0.035, 0.5, Ease.inCubic);
          S(c, { y: -p * 70, o: 1 - p, blur: p * 14 });
        });
      }
      els.prod._c.forEach((c, i) => {
        const p = P(T, 8.95 + i * 0.04, 0.7, Ease.outQuint);
        S(c, { y: (1 - p) * 80, o: p, blur: (1 - p) * 14 });
      });
      const prodBreath = 1 + 0.02 * P(T, 9.6, 3.4, Ease.inOutSine);
      S(els.bigP, { s: prodBreath });

      revealWords(T, els.s1, 9.3, 0.13, 0.6);
      els.s1._w.forEach((w, i) => { if (i > 0) S(w, { y: (1 - P(T, 9.55 + i * 0.16, 0.6, Ease.outQuint)) * 110 }); });
      revealWords(T, els.s2, 10.6, 0.16, 0.6);

      els.blocks.forEach((b, i) => {
        const p = E.spring(T - (10.3 + i * 0.16), 190, 15);
        S(b, { y: (1 - p) * -70, o: clamp((T - (10.3 + i * 0.16)) * 6) });
      });
      els.bars.forEach((b, i) => {
        const p = P(T, 11.3 + i * 0.08, 0.6, Ease.outBack);
        S(b, { sy: Math.max(0.0001, p) });
      });
      S(els.kit, { s: 1 + 0.03 * P(T, 10.3, 3, Ease.inOutSine) });
    },
  });
})();

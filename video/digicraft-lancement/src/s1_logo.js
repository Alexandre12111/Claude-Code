(function () {
  const { h, S, P, Ease, kf, clamp, lerp } = E;
  const K = 0.9;
  const LW = 1034 * K, LH = 420 * K;
  const LX = (1920 - LW) / 2, LY = (1080 - LH) / 2 - 20;
  const pc = window.LOGO_PIECES;
  const O = pc.O;
  const OC = { x: LX + (O.x + O.w / 2) * K, y: LY + (O.y + O.h / 2) * K };
  const O_INNER = 31 * K;
  window.G = window.G || {};
  window.G.oCenter = OC;

  const ZOOM_T0 = 3.05, ZOOM_T1 = 3.9;
  const zoomScale = (T) => lerp(1, 140, Ease.inExpo(clamp((T - ZOOM_T0) / (ZOOM_T1 - ZOOM_T0))));
  window.G.logoZoomClip = (T) => {
    if (T < ZOOM_T0) return 0;
    return O_INNER * zoomScale(T) * breath(T);
  };
  const breath = (T) => 1 + 0.025 * P(T, 2.3, 1.2, Ease.inOutSine);

  let els = {};

  SCENES.push({
    id: 'logo',
    build(root) {
      root.style.background = 'var(--offwhite)';
      const bg = h('div', 'abs bg-grid-light', root);
      Object.assign(bg.style, { inset: '-64px' });
      els.bg = bg;
      els.b1 = h('div', 'blob', root);
      Object.assign(els.b1.style, { width: '900px', height: '900px', left: '-200px', top: '-300px', background: 'rgba(46,139,192,0.13)' });
      els.b2 = h('div', 'blob', root);
      Object.assign(els.b2.style, { width: '800px', height: '800px', right: '-250px', bottom: '-350px', background: 'rgba(143,138,255,0.12)' });

      const zoomer = h('div', 'abs', root);
      Object.assign(zoomer.style, { inset: '0', transformOrigin: `${OC.x}px ${OC.y}px` });
      els.zoomer = zoomer;

      const logo = h('div', 'abs', zoomer);
      Object.assign(logo.style, { left: LX + 'px', top: LY + 'px', width: LW + 'px', height: LH + 'px' });

      const mkRow = (y0, y1) => {
        const r = h('div', 'abs', logo);
        Object.assign(r.style, { left: '0', top: y0 * K + 'px', width: LW + 'px', height: (y1 - y0) * K + 'px', overflow: 'hidden' });
        r._y0 = y0;
        return r;
      };
      const rowTop = mkRow(38, 156);
      const rowBot = mkRow(158, 392);
      const place = (key, parent, variant) => {
        const p = pc[key];
        const img = h('img', 'abs', parent);
        img.src = `../assets/img/logo/${key}_${p.blue ? 'w' : variant}.png`;
        Object.assign(img.style, { left: p.x * K + 'px', top: (p.y - parent._y0) * K + 'px', width: p.w * K + 'px', height: p.h * K + 'px' });
        return img;
      };
      els.top = ['L', 'E', 'Y', 'T', 'N'].map((k) => place(k, rowTop, 'n'));
      els.bot = ['C', 'o', 'g', 'n', 'i', 't'].map((k) => place(k, rowBot, 'n'));

      const xClip = h('div', 'abs', logo);
      const X = pc.Xs;
      Object.assign(xClip.style, { left: X.x * K - 4 + 'px', top: X.y * K - 4 + 'px', width: X.w * K + 8 + 'px', height: X.h * K + 8 + 'px', overflow: 'hidden' });
      xClip._y0 = X.y - 4 / K;
      const xs = h('img', 'abs', xClip);
      xs.src = '../assets/img/logo/Xs_w.png';
      Object.assign(xs.style, { left: '4px', top: '4px', width: X.w * K + 'px', height: X.h * K + 'px' });
      els.xs = xs;
      const xt = h('img', 'abs', logo);
      const XT = pc.Xt;
      xt.src = '../assets/img/logo/Xt_n.png';
      Object.assign(xt.style, { left: XT.x * K + 'px', top: XT.y * K + 'px', width: XT.w * K + 'px', height: XT.h * K + 'px', transformOrigin: '50% 100%' });
      els.xt = xt;

      const ring = h('img', 'abs', zoomer);
      ring.src = '../assets/img/logo/O_w.png';
      Object.assign(ring.style, { left: OC.x - (O.w * K) / 2 + 'px', top: OC.y - (O.h * K) / 2 + 'px', width: O.w * K + 'px', height: O.h * K + 'px' });
      els.ring = ring;

      const ul = h('div', 'abs', zoomer);
      Object.assign(ul.style, { left: 960 - 110 + 'px', top: LY + LH + 34 + 'px', width: '220px', height: '6px', borderRadius: '3px', background: 'var(--blue)', transformOrigin: '0 50%' });
      els.ul = ul;
    },
    update(T) {
      S(els.bg, { x: -T * 6, y: -T * 4 });
      S(els.b1, { x: Math.sin(T * 0.5) * 40, y: T * 10 });
      S(els.b2, { x: -T * 12, y: Math.cos(T * 0.4) * 30 });

      const zs = zoomScale(T) * breath(T);
      S(els.zoomer, { s: zs });

      // Anneau : tracé conique puis placement dans le logo.
      const draw = P(T, 0.12, 0.85, Ease.inOutCubic) * 360;
      const m = `conic-gradient(from 42deg, #000 0deg, #000 ${draw}deg, transparent ${draw + 0.5}deg)`;
      els.ring.style.webkitMaskImage = m;
      els.ring.style.maskImage = m;
      const mv = P(T, 0.85, 0.7, Ease.outExpo);
      const sc = lerp(2.6, 1, mv);
      const dx = lerp(960 - OC.x, 0, mv), dy = lerp(540 - OC.y, 0, mv);
      const rot = lerp(-140, 0, P(T, 0.12, 1.3, Ease.outCubic));
      S(els.ring, { x: dx, y: dy, s: sc, r: rot, o: T < 0.12 ? 0 : 1 });

      const topStart = [1.26, 1.19, 1.12, 1.05, 1.08];
      els.top.forEach((el, i) => {
        const p = P(T, topStart[i], 0.62, Ease.outQuint);
        const side = i === 4 ? -1 : 1;
        S(el, { y: (1 - p) * 130, x: (1 - p) * 50 * side, o: p > 0 ? 1 : 0 });
      });
      els.bot.forEach((el, i) => {
        const p = P(T, 1.42 + i * 0.05, 0.62, Ease.outQuint);
        S(el, { y: (1 - p) * 230, o: p > 0 ? 1 : 0 });
      });
      const px = P(T, 1.72, 0.55, Ease.outExpo);
      S(els.xs, { x: (1 - px) * 190, y: -(1 - px) * 170, o: px > 0 ? 1 : 0 });
      const pt = P(T, 1.98, 0.5, Ease.outBack);
      S(els.xt, { s: Math.max(0.0001, pt), o: pt > 0 ? 1 : 0 });
      const pu = P(T, 2.15, 0.6, Ease.outExpo);
      S(els.ul, { sx: Math.max(0.0001, pu), o: pu > 0 ? 1 : 0 });
    },
  });
})();

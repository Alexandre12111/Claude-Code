(function () {
  const { h, S, P, Ease, lerp, clamp } = E;
  const el = (parent, style, html) => { const e = h('div', 'abs', parent, html); Object.assign(e.style, style); return e; };

  const GRADS = { brand: ['#8FC6E7', '#2E8BC0'], product: ['#B7A6FF', '#6D5DF6'] };
  window.dcIcon = function (size, id, kind = 'product') {
    const g = `dcg${id}`;
    const [a, b] = GRADS[kind];
    const d = (cx, cy, r) => `<path d="M${cx} ${cy - r} L${cx + r} ${cy} L${cx} ${cy + r} L${cx - r} ${cy} Z" fill="url(#${g})"/>`;
    return `<svg width="${size}" height="${size}" viewBox="0 0 100 100"><defs><linearGradient id="${g}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${a}"/><stop offset="1" stop-color="${b}"/></linearGradient></defs>${d(50, 50, 17)}${d(50, 14, 9)}${d(50, 86, 9)}${d(14, 50, 9)}${d(86, 50, 9)}</svg>`;
  };

  const glow = (root, d, col, a0 = 0.7) => el(root, { left: -d / 2 + 'px', top: -d / 2 + 'px', width: d + 'px', height: d + 'px', borderRadius: '50%', background: `radial-gradient(closest-side, ${col}, rgba(0,0,0,0) 100%)`, opacity: String(a0) });
  const drift = (T, k, ax, ay, sp = 0.18) => [Math.sin(T * sp + k * 1.7) * ax, Math.cos(T * sp * 0.8 + k * 2.3) * ay];

  // Fond clair de la charte leyton.com : blanc chaud, halos pêche mouvants, trame de points, cercles fins.
  function warmBg(root, seed = 0) {
    root.classList.add('bg-warm');
    const G = [
      [glow(root, 1500, 'rgba(177,218,242,0.95)'), 1540, 90, 160, 110],
      [glow(root, 1200, 'rgba(165,213,240,0.55)'), 250, 1010, 140, 90],
      [glow(root, 900, 'rgba(46,139,192,0.16)'), 1720, 900, 120, 80],
      [glow(root, 1000, 'rgba(232,244,251,0.9)'), 900, 480, 200, 120],
    ];
    const dots = el(root, {});
    dots.className = 'dots-light';
    const c = [];
    [[1500, 170, 1100], [1500, 170, 760], [260, 980, 900]].forEach(([x, y, d], i) => {
      const e = el(root, { left: x - d / 2 + 'px', top: y - d / 2 + 'px', width: d + 'px', height: d + 'px' });
      e.className = 'circles';
      e._k = i + seed;
      c.push(e);
    });
    return (T) => {
      G.forEach(([g, x, y, ax, ay], i) => { const [dx, dy] = drift(T, i + seed * 3, ax, ay); S(g, { x: x + dx, y: y + dy, s: 1 + 0.06 * Math.sin(T * 0.3 + i) }); });
      S(dots, { x: (T * 5) % 36, y: (T * 3) % 36 });
      c.forEach((e) => S(e, { r: T * (e._k % 2 ? 3 : -2), s: 1 + 0.01 * Math.sin(T * 0.5 + e._k) }));
    };
  }
  // Fond marine : aurore orange et bleue, rayons lumineux lents, particules de lumière.
  function navyBg(root, seed = 0) {
    root.classList.add('bg-site-navy');
    const G = [
      [glow(root, 1700, 'rgba(24,110,168,0.75)'), 1500, 60, 180, 120],
      [glow(root, 1500, 'rgba(46,139,192,0.36)'), 180, 1080, 200, 110],
      [glow(root, 1100, 'rgba(143,198,231,0.16)'), 1700, 1000, 160, 90],
      [glow(root, 1300, 'rgba(15,88,133,0.6)'), 820, 420, 220, 140],
    ];
    const rays = el(root, {});
    rays.className = 'rays';
    const c = [];
    [[1560, 120, 1200], [1560, 120, 820], [300, 1000, 1000]].forEach(([x, y, d]) => {
      const e = el(root, { left: x - d / 2 + 'px', top: y - d / 2 + 'px', width: d + 'px', height: d + 'px' });
      e.className = 'circles dark';
      c.push(e);
    });
    const rnd = E.seeded(31 + seed);
    const bk = Array.from({ length: 26 }, () => {
      const d = 4 + rnd() * 16;
      const e = el(root, { width: d + 'px', height: d + 'px', borderRadius: '50%', background: `radial-gradient(closest-side, rgba(255,${190 + Math.floor(rnd() * 50)},${150 + Math.floor(rnd() * 60)},0.9), rgba(173,217,242,0))` });
      e._x = rnd() * 1920; e._y = rnd() * 1080; e._v = 14 + rnd() * 30; e._a = 0.25 + rnd() * 0.55; e._p = rnd() * 6.28;
      return e;
    });
    return (T) => {
      G.forEach(([g, x, y, ax, ay], i) => { const [dx, dy] = drift(T, i + seed * 5, ax, ay, 0.22); S(g, { x: x + dx, y: y + dy, s: 1 + 0.08 * Math.sin(T * 0.35 + i) }); });
      S(rays, { x: 560, y: -620, r: T * 2.2 });
      c.forEach((e, i) => S(e, { s: 1 + 0.015 * Math.sin(T * 0.6 + i) }));
      bk.forEach((e) => {
        const y = ((e._y - T * e._v) % 1180 + 1180) % 1180 - 50;
        S(e, { x: e._x + Math.sin(T * 0.7 + e._p) * 30, y, o: e._a * (0.6 + 0.4 * Math.sin(T * 1.3 + e._p)) });
      });
    };
  }

  // Dégradé continu sur un mot découpé en lettres, avec reflet lumineux qui balaie le mot.
  const BASE = 'linear-gradient(90deg, #4AA6DC 0%, #5FB4E6 30%, #86C8EE 60%, #A9D9F4 85%, #C4E6F8 100%)';
  function shine(list, T, t0 = -99, dur = 1.0, w = 150) {
    if (!list.length) return;
    if (!list._m) {
      const x0 = list[0].offsetLeft, last = list[list.length - 1];
      list._m = { x0, W: last.offsetLeft + last.offsetWidth - x0 };
    }
    const { x0, W } = list._m;
    const u = (T - t0) / dur;
    const X = lerp(-w * 1.5, W + w * 1.5, Ease.inOutSine(clamp(u)));
    const hl = u > 0 && u < 1 ? `linear-gradient(105deg, rgba(255,255,255,0) ${X - w}px, rgba(242,249,253,0.95) ${X}px, rgba(255,255,255,0) ${X + w}px), ` : '';
    list.forEach((c) => {
      c.style.backgroundImage = hl + BASE;
      c.style.backgroundSize = hl ? `${W}px 100%, ${W}px 100%` : `${W}px 100%`;
      c.style.backgroundPosition = `${x0 - c.offsetLeft}px 0`;
    });
  }

  // Anneau en dégradé conique (transitions circulaires).
  function ringFx(root) {
    const e = el(root, { display: 'none' });
    e.className = 'ringfx';
    let th0 = -1;
    return (cx, cy, R, th, rot = 0, o = 1) => {
      if (R <= 0 || o <= 0.001) { e.style.display = 'none'; return; }
      e.style.display = 'block';
      Object.assign(e.style, { left: cx - R + 'px', top: cy - R + 'px', width: 2 * R + 'px', height: 2 * R + 'px', opacity: String(o), transform: `rotate(${rot}deg)` });
      if (Math.abs(th - th0) > 0.5) {
        const m = `radial-gradient(farthest-side, rgba(0,0,0,0) calc(100% - ${th}px), #000 calc(100% - ${th - 1}px))`;
        e.style.webkitMask = m; e.style.mask = m; th0 = th;
      }
    };
  }

  const slamChars = (T, arr, t0, st = 0.03, from = 1.9) => arr.forEach((c, i) => {
    const p = P(T, t0 + i * st, 0.5, Ease.outExpo);
    S(c, { s: lerp(from, 1, p), o: p > 0 ? clamp(p * 3) : 0, blur: (1 - p) * 16, y: (1 - p) * -20 });
  });
  const riseChars = (T, arr, t0, st = 0.025, d = 0.55, dist = 70) => arr.forEach((c, i) => {
    const p = P(T, t0 + i * st, d, Ease.outQuint);
    S(c, { y: (1 - p) * dist, o: p, blur: (1 - p) * 8 });
  });

  // Logo Leyton CognitX (version blanche disponible pour les fonds sombres : O et X restent bleus).
  function leytonLogo(parent, height, white = false) {
    const img = h('img', '', parent);
    img.src = `../assets/cognitx/${white ? 'leyton_cognitx_white' : 'leyton_cognitx'}.png`;
    img.style.height = height + 'px';
    img.style.display = 'block';
    return img;
  }

  // Bandes du volet diagonal : liseré lumineux, bande orange en dégradé, bande de fond.
  const BANDS = {
    navy: ['#DDEFFA', 'linear-gradient(180deg, #9ACCE9 0%, #2E8BC0 45%, #3880A9 100%)', 'linear-gradient(180deg, #0F5885 0%, #012D48 60%, #011E31 100%)'],
    warm: ['#EDF7FC', 'linear-gradient(180deg, #9ACCE9 0%, #2E8BC0 45%, #3880A9 100%)', 'linear-gradient(180deg, #D3EAF8 0%, #F9FAFB 100%)'],
    peach: ['#EDF7FC', 'linear-gradient(180deg, #9ACCE9 0%, #2E8BC0 45%, #3880A9 100%)', 'linear-gradient(180deg, #B6DDF3 0%, #82BEE1 100%)'],
  };
  window.C = { el, warmBg, navyBg, slamChars, riseChars, leytonLogo, shine, ringFx, glow, BANDS };
})();

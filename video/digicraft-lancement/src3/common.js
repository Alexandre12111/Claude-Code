(function () {
  const { h, S, P, Ease, lerp, clamp } = E;
  const el = (parent, style, html) => { const e = h('div', 'abs', parent, html); Object.assign(e.style, style); return e; };

  const GRADS = { brand: ['#F8A87E', '#EB6739'], product: ['#B7A6FF', '#6D5DF6'] };
  window.dcIcon = function (size, id, kind = 'product') {
    const g = `dcg${id}`;
    const [a, b] = GRADS[kind];
    const d = (cx, cy, r) => `<path d="M${cx} ${cy - r} L${cx + r} ${cy} L${cx} ${cy + r} L${cx - r} ${cy} Z" fill="url(#${g})"/>`;
    return `<svg width="${size}" height="${size}" viewBox="0 0 100 100"><defs><linearGradient id="${g}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${a}"/><stop offset="1" stop-color="${b}"/></linearGradient></defs>${d(50, 50, 17)}${d(50, 14, 9)}${d(50, 86, 9)}${d(14, 50, 9)}${d(86, 50, 9)}</svg>`;
  };

  // Fond clair de la charte leyton.com : blanc chaud, halos pêche, cercles fins.
  function warmBg(root, seed = 0) {
    root.classList.add('bg-warm');
    const c = [];
    [[1500, 170, 1100], [1500, 170, 760], [260, 980, 900]].forEach(([x, y, d], i) => {
      const e = el(root, { left: x - d / 2 + 'px', top: y - d / 2 + 'px', width: d + 'px', height: d + 'px' });
      e.className = 'circles';
      e._k = i + seed;
      c.push(e);
    });
    return (T) => c.forEach((e) => S(e, { r: T * (e._k % 2 ? 3 : -2), s: 1 + 0.01 * Math.sin(T * 0.5 + e._k) }));
  }
  function navyBg(root) {
    root.classList.add('bg-site-navy');
    const c = [];
    [[1560, 120, 1200], [1560, 120, 820], [300, 1000, 1000]].forEach(([x, y, d], i) => {
      const e = el(root, { left: x - d / 2 + 'px', top: y - d / 2 + 'px', width: d + 'px', height: d + 'px' });
      e.className = 'circles dark';
      c.push(e);
    });
    return (T) => c.forEach((e, i) => S(e, { s: 1 + 0.015 * Math.sin(T * 0.6 + i) }));
  }

  const slamChars = (T, arr, t0, st = 0.03, from = 1.9) => arr.forEach((c, i) => {
    const p = P(T, t0 + i * st, 0.5, Ease.outExpo);
    S(c, { s: lerp(from, 1, p), o: p > 0 ? clamp(p * 3) : 0, blur: (1 - p) * 16, y: (1 - p) * -20 });
  });
  const riseChars = (T, arr, t0, st = 0.025, d = 0.55, dist = 70) => arr.forEach((c, i) => {
    const p = P(T, t0 + i * st, d, Ease.outQuint);
    S(c, { y: (1 - p) * dist, o: p, blur: (1 - p) * 8 });
  });

  // Logo Leyton officiel (SVG du site).
  function leytonLogo(parent, height, white = false) {
    const img = h('img', '', parent);
    img.src = `../assets/leyton/${white ? 'logo_white' : 'logo'}.svg`;
    img.style.height = height + 'px';
    img.style.display = 'block';
    return img;
  }

  window.C = { el, warmBg, navyBg, slamChars, riseChars, leytonLogo };
})();

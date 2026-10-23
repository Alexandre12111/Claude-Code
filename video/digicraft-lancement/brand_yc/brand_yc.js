// Charte YouCraft : remplace le symbole, le logo, les fonds et les accents de la charte précédente.
// Chargé juste après common.js : il redéfinit les mêmes fonctions, les scènes n'ont rien d'autre à connaître.
(function () {
  const { h, S, P, Ease, lerp, clamp } = E;
  const { el, glow } = C;
  const L0 = window.YC_LOGO;
  const INK = '#0B0B0F', STONE = '#F5F2EA';
  let uid = 0;

  const grad = (id) => `<linearGradient id="${id}" x1="0" y1="0" x2="1" y2="1">${L0.grad.map(([o, c]) => `<stop offset="${o}" stop-color="${c}"/>`).join('')}</linearGradient>`;
  const [bx, by, bw, bh] = L0.sym.block;
  const symParts = (gid, dark) => [
    `<path d="${L0.sym.dome}" fill="url(#${gid})"/>`,
    `<path d="${L0.sym.quarter}" fill="${dark ? STONE : INK}"/>`,
    `<rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="#E4F53C"/>`,
  ];

  // Symbole seul (interface produit, pastilles) : quart inférieur Ink sur fond clair, Stone sur fond sombre.
  window.dcIcon = function (size, id, kind = 'light') {
    const gid = `ycs${id}${++uid}`;
    return `<svg width="${size}" height="${size}" viewBox="0 0 248 247"><defs>${grad(gid)}</defs>${symParts(gid, kind === 'dark').join('')}</svg>`;
  };

  // Logo horizontal complet en une seule image vectorielle (barre d'application, petits formats).
  window.ycLogoSvg = function (height, dark = false) {
    const gid = `ycl${++uid}`;
    const col = dark ? STONE : INK;
    const glyphs = L0.glyphs.map((d) => `<path d="${d}" fill="${col}" transform="translate(${L0.tx} ${L0.ty})"/>`).join('');
    return `<svg height="${height}" width="${(height * L0.w / L0.h).toFixed(1)}" viewBox="0 0 ${L0.w} ${L0.h}" style="display:block"><defs>${grad(gid)}</defs>${symParts(gid, dark).join('')}${glyphs}</svg>`;
  };

  // Logo horizontal en calques : trois pièces du symbole et huit lettres du wordmark, animables séparément.
  C.ycLogo = function (parent, height, dark = false) {
    const k = height / L0.h, W = L0.w * k;
    const box = h('div', 'yc-logo', parent);
    Object.assign(box.style, { width: W + 'px', height: height + 'px' });
    const layer = (inner) => {
      const s = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      s.setAttribute('viewBox', `0 0 ${L0.w} ${L0.h}`);
      s.setAttribute('width', W); s.setAttribute('height', height);
      s.innerHTML = inner;
      box.appendChild(s);
      return s;
    };
    const gid = `ycg${++uid}`;
    const parts = symParts(gid, dark);
    const dome = layer(`<defs>${grad(gid)}</defs>${parts[0]}`);
    const quarter = layer(parts[1]);
    const block = layer(parts[2]);
    block.style.transformOrigin = `${(bx + bw / 2) * k}px ${(by + bh / 2) * k}px`;
    const col = dark ? STONE : INK;
    const glyphs = L0.glyphs.map((d) => layer(`<path d="${d}" fill="${col}" transform="translate(${L0.tx} ${L0.ty})"/>`));
    // Repère invisible sur le symbole : centre des transitions de zoom.
    const sym = h('div', '', box);
    Object.assign(sym.style, { position: 'absolute', left: '0', top: '0', width: 248 * k + 'px', height: height + 'px' });
    return { box, sym, dome, quarter, block, glyphs, k };
  };

  // Arrivée du symbole : les trois pièces se posent en douceur (environ 0,8 s), puis le bloc lime pulse une fois.
  C.ycLogoAnim = function (Lg, T, t0) {
    const d = 150 * Lg.k;
    [[Lg.dome, -1, -1, 0], [Lg.quarter, -0.4, 1, 0.08], [Lg.block, 1, -0.4, 0.16]].forEach(([e, dx, dy, st]) => {
      const p = P(T, t0 + st, 0.8, Ease.inOutCubic);
      S(e, { x: dx * d * (1 - p), y: dy * d * (1 - p), o: clamp((T - t0 - st) * 5) });
    });
    const u = clamp((T - (t0 + 1.0)) / 0.55);
    if (u > 0 && u < 1) S(Lg.block, { s: 1 + 0.14 * Math.sin(Math.PI * u), o: 1 });
  };

  // Symbole en traits fins, très grand et recadré : élément graphique de fond (remplace l'anneau de l'ancienne charte).
  C.ycSketch = function (parent, size, color) {
    const s = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    s.setAttribute('viewBox', '-8 -8 264 263');
    s.setAttribute('width', size); s.setAttribute('height', size);
    Object.assign(s.style, { position: 'absolute', overflow: 'visible' });
    const st = `fill="none" stroke="${color}" stroke-width="1.6" vector-effect="non-scaling-stroke"`;
    s.innerHTML = `<path d="${L0.sym.dome}" ${st}/><path d="${L0.sym.quarter}" ${st}/><rect x="${bx}" y="${by}" width="${bw}" height="${bh}" ${st}/>`;
    parent.appendChild(s);
    return s;
  };

  const drift = (T, k, ax, ay, sp = 0.18) => [Math.sin(T * sp + k * 1.7) * ax, Math.cos(T * sp * 0.8 + k * 2.3) * ay];

  // Fond clair : Stone, lumière Paper, brume bleutée très légère, trame de points et cercles fins en Ink.
  C.warmBg = function (root, seed = 0) {
    root.classList.add('bg-warm');
    const G = [
      [glow(root, 1500, 'rgba(251,250,246,0.95)'), 1540, 90, 160, 110],
      [glow(root, 1200, 'rgba(223,232,240,0.55)'), 250, 1010, 140, 90],
      [glow(root, 900, 'rgba(30,59,255,0.05)'), 1720, 900, 120, 80],
      [glow(root, 1000, 'rgba(251,250,246,0.8)'), 900, 480, 200, 120],
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
  };

  // Fond sombre : Ink, lueur Ultramarine en bas à droite et touche olive en haut, comme les modèles du kit.
  C.navyBg = function (root, seed = 0) {
    root.classList.add('bg-site-navy');
    const G = [
      [glow(root, 1700, 'rgba(30,59,255,0.20)'), 1600, 1020, 180, 110],
      [glow(root, 1400, 'rgba(44,76,240,0.08)'), 160, 1060, 200, 110],
      [glow(root, 1200, 'rgba(215,255,63,0.06)'), 1760, 40, 160, 90],
      [glow(root, 1300, 'rgba(22,22,28,0.85)'), 820, 420, 220, 140],
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
      const d = 4 + rnd() * 14;
      const e = el(root, { width: d + 'px', height: d + 'px', borderRadius: '50%', background: 'radial-gradient(closest-side, rgba(245,242,234,0.85), rgba(245,242,234,0))' });
      e._x = rnd() * 1920; e._y = rnd() * 1080; e._v = 14 + rnd() * 30; e._a = 0.2 + rnd() * 0.45; e._p = rnd() * 6.28;
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
  };

  // Mot fort d'un plan sombre : Lime plein, avec un reflet Paper qui le balaie une fois.
  const BASE = 'linear-gradient(90deg, #D7FF3F 0%, #D7FF3F 100%)';
  C.shine = function (list, T, t0 = -99, dur = 1.0, w = 150) {
    if (!list.length) return;
    if (!list._m) {
      const x0 = list[0].offsetLeft, last = list[list.length - 1];
      list._m = { x0, W: last.offsetLeft + last.offsetWidth - x0 };
    }
    const { x0, W } = list._m;
    const u = (T - t0) / dur;
    const X = lerp(-w * 1.5, W + w * 1.5, Ease.inOutSine(clamp(u)));
    const hl = u > 0 && u < 1 ? `linear-gradient(105deg, rgba(251,250,246,0) ${X - w}px, rgba(251,250,246,0.85) ${X}px, rgba(251,250,246,0) ${X + w}px), ` : '';
    list.forEach((c) => {
      c.style.backgroundImage = hl + BASE;
      c.style.backgroundSize = hl ? `${W}px 100%, ${W}px 100%` : `${W}px 100%`;
      c.style.backgroundPosition = `${x0 - c.offsetLeft}px 0`;
    });
  };

  // Volets diagonaux : liseré clair, bande Ultramarine, bande de fond Ink ou Stone.
  const UM = 'linear-gradient(180deg, #4F6BFF 0%, #1E3BFF 45%, #1F3FE6 100%)';
  C.BANDS = {
    navy: ['#F5F2EA', UM, 'linear-gradient(180deg, #16161C 0%, #0B0B0F 60%, #0B0B0F 100%)'],
    warm: ['#FBFAF6', UM, 'linear-gradient(180deg, #FBFAF6 0%, #F5F2EA 100%)'],
    peach: ['#FBFAF6', UM, 'linear-gradient(180deg, #DFE8F0 0%, #F5F2EA 100%)'],
  };
})();

// Moteur d'animation déterministe : tout est calculé à partir du temps T (secondes).
(function () {
  const clamp = (x, a = 0, b = 1) => (x < a ? a : x > b ? b : x);
  const lerp = (a, b, t) => a + (b - a) * t;
  const Ease = {
    lin: (t) => t,
    inQuad: (t) => t * t,
    outQuad: (t) => 1 - (1 - t) * (1 - t),
    inOutQuad: (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2),
    inCubic: (t) => t * t * t,
    outCubic: (t) => 1 - Math.pow(1 - t, 3),
    inOutCubic: (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2),
    outQuart: (t) => 1 - Math.pow(1 - t, 4),
    inOutQuart: (t) => (t < 0.5 ? 8 * t * t * t * t : 1 - Math.pow(-2 * t + 2, 4) / 2),
    outQuint: (t) => 1 - Math.pow(1 - t, 5),
    inOutQuint: (t) => (t < 0.5 ? 16 * Math.pow(t, 5) : 1 - Math.pow(-2 * t + 2, 5) / 2),
    outExpo: (t) => (t >= 1 ? 1 : 1 - Math.pow(2, -10 * t)),
    inExpo: (t) => (t <= 0 ? 0 : Math.pow(2, 10 * t - 10)),
    inOutExpo: (t) =>
      t <= 0 ? 0 : t >= 1 ? 1 : t < 0.5 ? Math.pow(2, 20 * t - 10) / 2 : (2 - Math.pow(2, -20 * t + 10)) / 2,
    inOutSine: (t) => -(Math.cos(Math.PI * t) - 1) / 2,
    outSine: (t) => Math.sin((t * Math.PI) / 2),
    outBack: (t) => {
      const c1 = 1.70158, c3 = c1 + 1;
      return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
    },
    outBackSoft: (t) => {
      const c1 = 1.1, c3 = c1 + 1;
      return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
    },
    inBack: (t) => 2.70158 * t * t * t - 1.70158 * t * t,
  };

  // Ressort amorti analytique (0 -> 1), t en secondes.
  function spring(t, stiffness = 170, damping = 16, mass = 1) {
    if (t <= 0) return 0;
    const w0 = Math.sqrt(stiffness / mass);
    const z = damping / (2 * Math.sqrt(stiffness * mass));
    if (z < 1) {
      const wd = w0 * Math.sqrt(1 - z * z);
      return 1 - Math.exp(-z * w0 * t) * (Math.cos(wd * t) + ((z * w0) / wd) * Math.sin(wd * t));
    }
    return 1 - Math.exp(-w0 * t) * (1 + w0 * t);
  }

  // Progression entre t0 et t0+d avec easing.
  const P = (T, t0, d, e = Ease.outCubic) => e(clamp((T - t0) / d));

  // Keyframes : [[t, valeur, easingVersCeKeyframe], ...]
  function kf(T, frames) {
    if (T <= frames[0][0]) return frames[0][1];
    for (let i = 1; i < frames.length; i++) {
      const [t1, v1, e] = frames[i];
      const [t0, v0] = frames[i - 1];
      if (T <= t1) {
        const p = (e || Ease.inOutCubic)(clamp((T - t0) / (t1 - t0)));
        return lerp(v0, v1, p);
      }
    }
    return frames[frames.length - 1][1];
  }

  // Applique transform / opacité / flou.
  function S(el, o) {
    if (!el) return;
    let tr = '';
    if (o.x || o.y || o.z) tr += `translate3d(${(o.x || 0).toFixed(2)}px,${(o.y || 0).toFixed(2)}px,${(o.z || 0).toFixed(2)}px) `;
    if (o.rx) tr += `rotateX(${o.rx.toFixed(3)}deg) `;
    if (o.ry) tr += `rotateY(${o.ry.toFixed(3)}deg) `;
    if (o.r) tr += `rotate(${o.r.toFixed(3)}deg) `;
    if (o.skx) tr += `skewX(${o.skx.toFixed(3)}deg) `;
    if (o.s !== undefined) tr += `scale(${o.s.toFixed(4)}) `;
    if (o.sx !== undefined || o.sy !== undefined) tr += `scale(${(o.sx ?? 1).toFixed(4)},${(o.sy ?? 1).toFixed(4)}) `;
    el.style.transform = tr || 'none';
    if (o.o !== undefined) el.style.opacity = clamp(o.o).toFixed(3);
    if (o.blur !== undefined || o.f !== undefined) {
      const parts = [];
      if (o.blur > 0.05) parts.push(`blur(${o.blur.toFixed(2)}px)`);
      if (o.f) parts.push(o.f);
      el.style.filter = parts.length ? parts.join(' ') : 'none';
    }
  }

  // Création d'éléments.
  function h(tag, cls, parent, html) {
    const el = document.createElement(tag);
    if (cls) el.className = cls;
    if (html !== undefined) el.innerHTML = html;
    if (parent) parent.appendChild(el);
    return el;
  }

  function icon(name, size = 24, stroke = 2, color = 'currentColor', cls = '') {
    const inner = (window.ICONS && window.ICONS[name]) || '';
    return `<svg class="ic ${cls}" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="${stroke}" stroke-linecap="round" stroke-linejoin="round">${inner}</svg>`;
  }

  // Texte découpé en mots masqués. spec: tableau de {t, c} ou chaîne.
  function words(parent, spec, cls = '') {
    const items = typeof spec === 'string' ? spec.split(' ').map((t) => ({ t })) : spec;
    const line = h('div', 'wline ' + cls, parent);
    const inners = [];
    items.forEach((it, i) => {
      const m = h('span', 'wm', line);
      const w = h('span', 'wi ' + (it.c || ''), m, it.t);
      inners.push(w);
      if (i < items.length - 1) line.appendChild(document.createTextNode(' '));
    });
    line._w = inners;
    return line;
  }

  // Caractères individuels (pour morph / stagger).
  function chars(parent, text, cls = '') {
    const wrap = h('span', 'chars ' + cls, parent);
    const out = [];
    for (const ch of text) {
      const c = h('span', 'ch', wrap, ch === ' ' ? '&nbsp;' : ch);
      out.push(c);
    }
    wrap._c = out;
    return wrap;
  }

  // Révélation de mots : montée depuis le masque avec décalage.
  function revealWords(T, line, t0, stagger = 0.06, dur = 0.6, dist = 110) {
    line._w.forEach((w, i) => {
      const p = P(T, t0 + i * stagger, dur, Ease.outQuint);
      S(w, { y: (1 - p) * dist, o: p < 0.001 ? 0 : 1, r: (1 - p) * 4 });
    });
  }
  function hideWords(T, line, t0, stagger = 0.03, dur = 0.45, dist = -110) {
    line._w.forEach((w, i) => {
      const p = P(T, t0 + i * stagger, dur, Ease.inCubic);
      if (p > 0) S(w, { y: p * dist, o: 1 - p * 0.3 });
    });
  }

  function seeded(seed) {
    let s = seed >>> 0;
    return () => {
      s = (s * 1664525 + 1013904223) >>> 0;
      return s / 4294967296;
    };
  }

  // Compteur numérique formaté à la française.
  const fmt = (n) => Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

  // Volet diagonal « / » (inspiré du X CognitX) : révèle root de gauche à droite.
  const SLANT = 620;
  function slash(T, t0, dur, root, bands, widths = [240, 70]) {
    const total = widths.reduce((a, b) => a + b, 0);
    const p = Ease.inOutQuart(clamp((T - t0) / dur));
    const xt = lerp(0, 1920 + SLANT + total + 40, p);
    const xb = xt - SLANT;
    if (p >= 1) root.style.clipPath = 'none';
    else root.style.clipPath = `polygon(0 0, ${xt}px 0, ${xb}px 1080px, 0 1080px)`;
    let off = 0;
    bands.forEach((b, i) => {
      const w = widths[i];
      const a = xt - off, c = xt - off - w;
      b.style.clipPath = `polygon(${c}px 0, ${a}px 0, ${a - SLANT}px 1080px, ${c - SLANT}px 1080px)`;
      b.style.display = p > 0 && p < 1 ? 'block' : 'none';
      off += w;
    });
    return p;
  }
  function slashBands(root, colors = ['var(--blue)', 'var(--navy)']) {
    return colors.map((c) => {
      const b = h('div', 'abs', root);
      Object.assign(b.style, { inset: '0', background: c, zIndex: '50' });
      return b;
    });
  }

  window.E = { clamp, lerp, Ease, spring, P, kf, S, h, icon, words, chars, revealWords, hideWords, seeded, fmt, slash, slashBands };
})();

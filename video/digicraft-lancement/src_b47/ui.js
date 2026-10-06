// Briques visuelles du film : fonds dégradés vivants, fenêtres d'app, maquettes d'appareils, saisie.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon } = E;
  const el = C.el;

  // Champ de couleur : grands halos qui dérivent lentement (palette de la charte DigiCraft).
  const PAL = {
    sky: ['#0B4471', '#2E8BC0', '#8FC6E7', '#E2F1FA'],
    violet: ['#2A1F6B', '#6D5DF6', '#B7A6FF', '#EDE9FF'],
    dusk: ['#011A33', '#0F5885', '#7C6CF0', '#C9B8FF'],
    ice: ['#CFE6F5', '#EAF4FB', '#B7A6FF', '#F7FAFC'],
    dawn: ['#022446', '#2E8BC0', '#A855F7', '#FDE7F3'],
    mint: ['#D6F0EE', '#EAF7F6', '#BFE3F7', '#F4FBFB'],
  };
  function field(root, pal, seed = 0) {
    const c = PAL[pal];
    const base = el(root, { inset: '0', background: c[0] });
    const blobs = [
      [1500, c[1], 0.95, 0.25, 0.85], [1200, c[2], 0.85, 0.8, 0.3], [1000, c[3], 0.7, 0.55, 0.95], [900, c[2], 0.6, 0.1, 0.2],
    ].map(([d, col, o, fx, fy], i) => ({ e: C.glow(root, d, col, o), fx, fy, k: i + seed * 3 }));
    return (T, amp = 1) => {
      blobs.forEach((b) => {
        const x = b.fx * 1920 + Math.sin(T * 0.35 + b.k * 1.9) * 220 * amp;
        const y = b.fy * 1080 + Math.cos(T * 0.28 + b.k * 2.4) * 160 * amp;
        S(b.e, { x, y, s: 1 + 0.08 * Math.sin(T * 0.5 + b.k) });
      });
      return base;
    };
  }

  // Fenêtre d'application DigiCraft (navigateur épuré).
  function win(parent, x, y, w, hh, url = 'digicraft.leyton-cognitx.com') {
    const r = el(parent, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px' });
    r.className = 'win ui';
    const bar = h('div', 'win-bar', r);
    ['#FF6B6B', '#FFC94D', '#3DD68C'].forEach((c) => { const d = h('i', '', bar); d.style.background = c; });
    h('div', 'url', bar, `${icon('lock', 14, 2.2, '#94A3B8')}${url}`);
    const body = el(r, { left: '0', right: '0', top: '52px', bottom: '0' });
    return { r, body, bar };
  }

  // Ordinateur portable vu de face (écran + base), contenu dans .screen.
  function laptop(parent, cx, cy, w) {
    const hh = w * 0.62;
    const g = el(parent, { left: cx - w / 2 + 'px', top: cy - hh / 2 + 'px', width: w + 'px', height: hh + 'px' });
    el(g, { inset: '0', borderRadius: w * 0.025 + 'px', background: 'linear-gradient(180deg,#1C2430,#0B1118)', boxShadow: '0 50px 120px rgba(0,0,0,0.45)' });
    const screen = el(g, { left: w * 0.022 + 'px', top: w * 0.022 + 'px', right: w * 0.022 + 'px', bottom: w * 0.03 + 'px', borderRadius: w * 0.008 + 'px', overflow: 'hidden', background: '#F7FAFC' });
    el(g, { left: -w * 0.07 + 'px', width: w * 1.14 + 'px', top: hh - 4 + 'px', height: w * 0.035 + 'px', borderRadius: `0 0 ${w * 0.04}px ${w * 0.04}px`, background: 'linear-gradient(180deg,#C7CED6,#7D8792)', boxShadow: '0 30px 60px rgba(0,0,0,0.35)' });
    el(g, { left: w * 0.42 + 'px', width: w * 0.16 + 'px', top: hh - 4 + 'px', height: w * 0.008 + 'px', borderRadius: `0 0 ${w * 0.01}px ${w * 0.01}px`, background: '#5D6670' });
    return { g, screen, w, hh };
  }

  function tablet(parent, cx, cy, w) {
    const hh = w * 0.72;
    const g = el(parent, { left: cx - w / 2 + 'px', top: cy - hh / 2 + 'px', width: w + 'px', height: hh + 'px', borderRadius: w * 0.05 + 'px', background: '#10161D', boxShadow: '0 60px 140px rgba(1,40,60,0.40)' });
    const screen = el(g, { left: w * 0.03 + 'px', top: w * 0.03 + 'px', right: w * 0.03 + 'px', bottom: w * 0.03 + 'px', borderRadius: w * 0.025 + 'px', overflow: 'hidden', background: '#F7FAFC' });
    return { g, screen };
  }

  // Saisie au clavier : renvoie le nombre de caractères visibles.
  function typeText(node, text, T, t0, cps = 26, caret = true) {
    const n = Math.max(0, Math.min(text.length, Math.floor((T - t0) * cps)));
    const on = caret && (T < t0 + text.length / cps + 0.6 ? true : Math.floor(T * 2.2) % 2 === 0);
    node.innerHTML = text.slice(0, n).replace(/</g, '&lt;') + (caret && T > t0 - 0.4 && on ? '<span class="caret"></span>' : '');
    return n;
  }

  // Curseur de souris.
  function cursor(parent) {
    const c = el(parent, { left: '0', top: '0', width: '34px', height: '34px', zIndex: '50' },
      '<svg width="34" height="34" viewBox="0 0 24 24"><path d="M4 2 L4 19 L8.6 14.8 L11.6 21.6 L14.4 20.4 L11.4 13.8 L17.6 13.8 Z" fill="#0B2545" stroke="#fff" stroke-width="1.4" stroke-linejoin="round"/></svg>');
    return c;
  }

  // Symbole DigiCraft (5 losanges).
  function symbol(parent, size, grad = ['#8FC6E7', '#2E8BC0']) {
    const g = el(parent, { width: size + 'px', height: size + 'px' });
    const k = size / 100;
    const D = (cx, cy, r) => {
      const side = r * Math.SQRT2 * k;
      const d = el(g, { left: cx * k - side / 2 + 'px', top: cy * k - side / 2 + 'px', width: side + 'px', height: side + 'px', borderRadius: side * 0.14 + 'px', background: `linear-gradient(135deg,${grad[0]},${grad[1]})`, transform: 'rotate(45deg)' });
      return d;
    };
    g._d = [D(50, 50, 17), D(50, 14, 9), D(86, 50, 9), D(50, 86, 9), D(14, 50, 9)];
    return g;
  }

  // Logo complet DigiCraft (symbole + mot), en version claire ou sombre.
  function lockup(parent, size, dark = false) {
    const g = el(parent, { display: 'flex', alignItems: 'center', gap: size * 0.24 + 'px' });
    g.style.position = 'relative';
    const sy = symbol(g, size);
    sy.style.position = 'relative';
    sy.style.flex = 'none';
    const w = h('div', 'nh', g);
    Object.assign(w.style, { fontSize: size * 1.06 + 'px', lineHeight: '1', paddingTop: size * 0.1 + 'px', color: dark ? '#fff' : 'var(--navy)' });
    const digi = E.chars(w, 'Digi');
    const craft = E.chars(w, 'Craft');
    craft._c.forEach((c) => (c.style.color = dark ? '#8FC6E7' : '#2E8BC0'));
    g._sym = sy; g._ch = [...digi._c, ...craft._c];
    return g;
  }

  // Petites briques d'interface.
  const kpi = (p, x, y, w, lab, val, delta, col, ic) => {
    const c = el(p, { left: x + 'px', top: y + 'px', width: w + 'px', height: '118px' });
    c.className = 'card ui';
    c.innerHTML = `<div style="position:absolute;left:20px;top:18px;display:flex;align-items:center;gap:10px;font-size:15px;color:#64748B;font-weight:500"><span style="width:30px;height:30px;border-radius:9px;background:${col}1F;display:flex;align-items:center;justify-content:center">${icon(ic, 17, 2.2, col)}</span>${lab}</div>
      <div class="v" style="position:absolute;left:20px;top:58px;font-size:34px;font-weight:700;color:#0B2545">${val}</div>
      <div style="position:absolute;right:18px;top:68px;font-size:14px;font-weight:700;color:${delta[0] === '−' ? '#D97706' : '#16A34A'}">${delta}</div>`;
    return c;
  };
  function bars(p, x, y, w, hh, vals, col = '#2E8BC0') {
    const g = el(p, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px' });
    const bw = w / vals.length;
    g._b = vals.map((v, i) => el(g, { left: i * bw + bw * 0.18 + 'px', bottom: '0', width: bw * 0.64 + 'px', height: v * hh + 'px', borderRadius: '8px 8px 3px 3px', background: i === vals.length - 1 ? col : col + '55', transformOrigin: '50% 100%' }));
    return g;
  }
  const growBars = (g, T, t0, st = 0.05) => g._b.forEach((b, i) => S(b, { sy: Math.max(0.001, P(T, t0 + i * st, 0.6, Ease.outBack)) }));
  function badge(p, txt, kind) {
    const K = { ok: ['#DCFCE7', '#15803D'], warn: ['#FEF3C7', '#B45309'], info: ['#E0EEFA', '#1D6FA3'], vio: ['#EDE9FF', '#6D5DF6'] }[kind];
    const b = h('span', 'pill', p, txt);
    Object.assign(b.style, { background: K[0], color: K[1], height: '28px', fontSize: '13px', padding: '0 12px' });
    return b;
  }
  const avatar = (p, x, y, d, ini, col) => el(p, { left: x + 'px', top: y + 'px', width: d + 'px', height: d + 'px', borderRadius: '50%', background: col, color: '#fff', fontFamily: 'DM Sans', fontWeight: '700', fontSize: d * 0.38 + 'px', display: 'flex', alignItems: 'center', justifyContent: 'center' }, ini);

  // Ligne de progression : barre et pourcentage.
  function progress(p, x, y, w, col) {
    const g = el(p, { left: x + 'px', top: y + 'px', width: w + 'px', height: '10px', borderRadius: '5px', background: '#E8EEF4', overflow: 'hidden' });
    const f = el(g, { left: '0', top: '0', bottom: '0', width: w + 'px', borderRadius: '5px', background: col, transformOrigin: '0 50%' });
    return (v) => S(f, { sx: Math.max(0.001, v) });
  }

  // Volet de passage : la scène entre en glissant avec flou de mouvement.
  function whip(node, T, tIn, tOut, dir = 1, dist = 1920) {
    const pi = P(T, tIn, 0.45, Ease.outExpo);
    const po = tOut ? P(T, tOut, 0.4, Ease.inExpo) : 0;
    const x = (1 - pi) * dist * dir - po * dist * dir;
    const v = Math.abs((1 - pi) - po);
    S(node, { x, blur: Math.min(40, v * 60) });
  }

  window.U = { PAL, field, win, laptop, tablet, typeText, cursor, symbol, lockup, kpi, bars, growBars, badge, avatar, progress, whip };
})();

(function () {
  const { h, S, P, Ease, lerp, clamp, chars, icon, spring } = E;
  const el = (parent, style, html) => { const e = h('div', 'abs', parent, html); Object.assign(e.style, style); return e; };
  let els = {};
  const TAG = 'Build, sans coder.';
  const TY0 = 55.95, TY1 = 57.1;

  SCENES.push({
    id: 'cloture',
    build(root) {
      els.root = root;
      root.style.background = 'var(--offwhite)';
      const grid = el(root, { inset: '-64px' });
      grid.className = 'abs bg-grid-light';
      els.grid = grid;
      els.b1 = el(root, { width: '900px', height: '900px', left: '-150px', top: '-350px', background: 'rgba(143,138,255,0.16)' });
      els.b1.className = 'blob';
      els.b2 = el(root, { width: '900px', height: '900px', right: '-200px', bottom: '-400px', background: 'rgba(46,139,192,0.14)' });
      els.b2.className = 'blob';

      const lock = el(root, { left: '0', width: '1920px', top: '300px', height: '200px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '44px' });
      els.lock = lock;
      const ic = h('div', '', lock);
      Object.assign(ic.style, { position: 'relative', width: '170px', height: '170px', flex: 'none' });
      const D = (cx, cy, r, i) => {
        const side = r * Math.SQRT2 * 1.7;
        const d = el(ic, { left: cx * 1.7 - side / 2 + 'px', top: cy * 1.7 - side / 2 + 'px', width: side + 'px', height: side + 'px', borderRadius: side * 0.12 + 'px', background: 'linear-gradient(135deg,#B7A6FF,#6D5DF6)' });
        d._i = i;
        return d;
      };
      els.dia = [D(50, 50, 17, 0), D(50, 14, 9, 1), D(86, 50, 9, 2), D(50, 86, 9, 3), D(14, 50, 9, 4)];
      const word = h('div', '', lock);
      Object.assign(word.style, { fontSize: '176px', fontWeight: '800', letterSpacing: '-0.03em', color: 'var(--navy)', lineHeight: '1', paddingBottom: '18px' });
      els.digi = chars(word, 'Digi');
      const craft = chars(word, 'Craft');
      craft.style.color = 'var(--violet)';
      els.craft = craft;
      els.wordEl = word;

      const tag = el(root, { left: '0', width: '1920px', top: '560px', textAlign: 'center', fontSize: '84px', fontWeight: '700', color: 'var(--navy)', letterSpacing: '-0.01em' });
      els.tag = tag;
      els.tagU = el(root, { top: '672px', height: '8px', borderRadius: '4px', background: 'var(--blue)', transformOrigin: '0 50%' });

      const by = el(root, { left: '0', width: '1920px', top: '740px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '22px', fontSize: '30px', fontWeight: '600', color: 'var(--gray-2)' });
      by.innerHTML = `<span>par</span><img src="../assets/img/logo/leyton_cognitx_navy.png" style="height:112px">`;
      els.by = by;
      const cta = el(root, { left: '0', width: '1920px', top: '925px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '30px' });
      cta.innerHTML = `<div style="height:64px;padding:0 30px;border-radius:32px;background:#022446;color:#fff;display:flex;align-items:center;gap:14px;font-size:25px;font-weight:700">${icon('message-square', 26, 2.2, '#6BB8E6')}Parlez-en à votre interlocuteur Leyton</div><div style="font-size:26px;font-weight:600;color:#899AA8;display:flex;align-items:center;gap:10px">${icon('globe', 24, 2, '#899AA8')}digicraft.leyton-cognitx.com</div>`;
      els.cta = cta;
    },
    update(T) {
      S(els.root, { o: P(T, 53.6, 0.5, Ease.outCubic) });
      S(els.grid, { x: -T * 6, y: -T * 4 });
      S(els.b1, { x: Math.sin(T * 0.4) * 60, y: T * 3 - 170 });
      S(els.b2, { x: Math.cos(T * 0.3) * 50 });

      const dirs = [[0, 0], [0, -1], [1, 0], [0, 1], [-1, 0]];
      els.dia.forEach((d, i) => {
        const t0 = 54.05 + (i === 0 ? 0 : 0.12 + i * 0.05);
        const p = spring(T - t0, 200, 15);
        const [dx, dy] = dirs[i];
        S(d, { x: dx * (1 - p) * 220, y: dy * (1 - p) * 220, r: 45 + (1 - p) * 180, s: Math.max(0.0001, i === 0 ? p : 0.4 + 0.6 * p), o: clamp((T - t0) * 6) });
      });
      [...els.digi._c, ...els.craft._c].forEach((c, i) => {
        const p = P(T, 54.45 + i * 0.045, 0.7, Ease.outQuint);
        S(c, { y: (1 - p) * 90, o: p, blur: (1 - p) * 10 });
      });
      const settle = P(T, 54.0, 7, Ease.outSine);
      S(els.lock, { s: 0.97 + 0.04 * settle });

      const n = Math.round(clamp((T - TY0) / (TY1 - TY0)) * TAG.length);
      const typed = TAG.slice(0, n);
      const caret = T > 55.7 && (T < TY1 + 0.1 || Math.floor(T * 2.2) % 2 === 0) && T < 59.5;
      const b = typed.slice(0, 5), rest = typed.slice(5);
      els.tag.innerHTML = `<span style="color:var(--blue)">${b}</span>${rest}<span style="display:inline-block;width:6px;height:78px;background:var(--blue);vertical-align:-10px;margin-left:6px;opacity:${caret ? 1 : 0}"></span>`;
      const tw = 700;
      const u = P(T, 57.15, 0.6, Ease.outExpo);
      Object.assign(els.tagU.style, { left: 960 - tw / 2 + 'px', width: tw + 'px' });
      S(els.tagU, { sx: Math.max(0.0001, u), o: u > 0 ? 1 : 0 });

      const bp = P(T, 57.55, 0.7, Ease.outQuint);
      S(els.by, { y: (1 - bp) * 30, o: bp });
      const cp = P(T, 58.15, 0.7, Ease.outQuint);
      S(els.cta, { y: (1 - cp) * 30, o: cp });
    },
  });
})();

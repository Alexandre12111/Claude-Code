(function () {
  const { h, S, P, Ease, lerp, clamp, chars, icon, spring } = E;
  const { el, warmBg, riseChars } = C;
  let a = {};
  const TAG = 'Build, sans coder.';
  const TY0 = 47.25, TY1 = 48.15;

  SCENES.push({
    id: 'fin',
    build(root) {
      a.root = root;
      a.bg = warmBg(root, 4);
      a.ring = h('img', 'abs', root);
      a.ring.src = '../assets/leyton/ring_particles.png';
      Object.assign(a.ring.style, { left: 960 - 380 + 'px', top: 400 - 407 + 'px', width: '759px', height: '814px', opacity: '0.35' });
      const lock = el(root, { left: '0', width: '1920px', top: '250px', height: '200px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '40px' });
      a.lock = lock;
      const ic = h('div', '', lock);
      Object.assign(ic.style, { position: 'relative', width: '160px', height: '160px', flex: 'none' });
      const D = (cx, cy, r) => {
        const side = r * Math.SQRT2 * 1.6;
        return el(ic, { left: cx * 1.6 - side / 2 + 'px', top: cy * 1.6 - side / 2 + 'px', width: side + 'px', height: side + 'px', borderRadius: side * 0.12 + 'px', background: 'linear-gradient(135deg,#F8A87E,#EB6739)' });
      };
      a.dia = [D(50, 50, 17), D(50, 14, 9), D(86, 50, 9), D(50, 86, 9), D(14, 50, 9)];
      const word = h('div', 'nh', lock);
      Object.assign(word.style, { fontSize: '176px', color: 'var(--navy)', lineHeight: '1', paddingTop: '18px' });
      a.digi = chars(word, 'Digi');
      a.craft = chars(word, 'Craft');
      a.craft._c.forEach((c) => c.classList.add('o'));
      a.tag = el(root, { left: '0', width: '1920px', top: '500px', textAlign: 'center', fontSize: '84px', color: 'var(--navy)' });
      a.tag.classList.add('nh');
      a.tagU = el(root, { left: 960 - 330 + 'px', width: '660px', top: '612px', height: '8px', borderRadius: '4px', background: 'var(--orange)', transformOrigin: '0 50%' });
      const by = el(root, { left: '0', width: '1920px', top: '680px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '22px', fontSize: '30px', fontWeight: '600', color: '#6B7B88' });
      by.innerHTML = '<span>par</span>';
      C.leytonLogo(by, 66);
      a.by = by;
      a.cta = el(root, { left: '0', width: '1920px', top: '850px', display: 'flex', justifyContent: 'center' },
        `<div style="height:92px;padding:0 16px 0 44px;border-radius:46px;background:#012D48;color:#fff;display:flex;align-items:center;gap:26px;font-size:38px;font-weight:700;box-shadow:0 24px 50px rgba(1,45,72,0.28)">Demander une démo<div style="width:64px;height:64px;border-radius:32px;background:#fff;display:flex;align-items:center;justify-content:center">${icon('arrow-up-right', 32, 2.6, '#012D48')}</div></div>`);
      a.btn = a.cta.firstChild;
    },
    update(T) {
      const ip = P(T, 46.1, 0.45, Ease.inOutCubic);
      a.root.style.clipPath = ip < 1 ? `circle(${lerp(0, 1300, ip)}px at 960px 540px)` : 'none';
      a.bg(T);
      S(a.ring, { r: T * 5, s: 0.9 + 0.1 * P(T, 46.2, 3), o: 0.35 * P(T, 46.2, 1) });
      const dirs = [[0, 0], [0, -1], [1, 0], [0, 1], [-1, 0]];
      a.dia.forEach((d, i) => {
        const t0 = 46.35 + (i ? 0.06 + i * 0.04 : 0);
        const p = spring(T - t0, 220, 15);
        const [dx, dy] = dirs[i];
        S(d, { x: dx * (1 - p) * 240, y: dy * (1 - p) * 240, r: 45 + (1 - p) * 225, s: Math.max(0.0001, i ? 0.3 + 0.7 * p : p), o: clamp((T - t0) * 8) });
      });
      riseChars(T, [...a.digi._c, ...a.craft._c], 46.6, 0.035, 0.6, 90);
      const n = Math.round(clamp((T - TY0) / (TY1 - TY0)) * TAG.length);
      const typed = TAG.slice(0, n);
      const caret = T > 47.0 && (T < TY1 + 0.1 || Math.floor(T * 2.2) % 2 === 0);
      a.tag.innerHTML = `<span class="o">${typed.slice(0, 5)}</span>${typed.slice(5)}<span style="display:inline-block;width:6px;height:76px;background:#EB6739;vertical-align:-8px;margin-left:6px;opacity:${caret ? 1 : 0}"></span>`;
      const u = P(T, 48.2, 0.5, Ease.outExpo);
      S(a.tagU, { sx: Math.max(0.0001, u), o: u > 0 ? 1 : 0 });
      const bp = P(T, 48.45, 0.6, Ease.outQuint);
      S(a.by, { y: (1 - bp) * 30, o: bp });
      const cp = spring(T - 48.85, 190, 15);
      S(a.cta, { y: (1 - cp) * 60, o: clamp((T - 48.85) * 5) });
      S(a.btn, { s: 1 + 0.03 * Math.max(0, Math.sin((T - 49.4) * 4)) * (T > 49.4 ? 1 : 0) });
      S(a.lock, { s: 0.98 + 0.03 * P(T, 46.5, 4, Ease.outSine) });
    },
  });
})();

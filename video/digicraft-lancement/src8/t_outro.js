// Fin : récapitulatif des 4 étapes, logo DigiCraft by Leyton CognitX, appel à l'action.
(function () {
  const { h, S, P, Ease, clamp, lerp, chars, spring, icon } = E;
  const { el, warmBg, riseChars, ringFx } = C;
  let a = {};
  const RECAP = [['square-pen', 'Describe'], ['wand-sparkles', 'Build'], ['message-square', 'Refine'], ['rocket', 'Publish']];

  SCENES.push({
    id: 'outro',
    build(root) {
      a.root = root;
      a.bg = warmBg(root, 5);
      a.ring = ringFx(root);
      const lock = el(root, { left: '0', width: '1920px', top: '215px', height: '170px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '34px' });
      a.lock = lock;
      a.ic = h('div', '', lock, window.dcIcon(140, 'to', 'brand'));
      const word = h('div', 'nh', lock);
      Object.assign(word.style, { fontSize: '150px', color: 'var(--navy)', lineHeight: '1', paddingTop: '16px' });
      a.digi = chars(word, 'Digi');
      a.craft = chars(word, 'Craft');
      a.craft._c.forEach((c) => c.classList.add('o'));
      a.recap = RECAP.map(([ic, lab], i) => {
        const g = el(root, { left: 960 + (i - 1.5) * 290 - 125 + 'px', top: '470px', width: '250px', height: '84px', borderRadius: '42px', background: '#fff', boxShadow: '0 14px 34px rgba(1,45,72,0.10)', display: 'flex', alignItems: 'center', gap: '14px', padding: '0 26px 0 12px', fontSize: '30px', fontWeight: '700', color: '#012D48' },
          `<div style="width:60px;height:60px;border-radius:30px;background:#2E8BC0;display:flex;align-items:center;justify-content:center">${icon(ic, 28, 2.2, '#fff')}</div>${lab}`);
        return g;
      });
      a.arrows = [0, 1, 2].map((i) => el(root, { left: 960 + (i - 1) * 290 - 14 + 'px', top: '498px', width: '28px', height: '28px' }, icon('chevron-right', 28, 2.6, '#9FD0EE')));
      const by = el(root, { left: '0', width: '1920px', top: '640px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '20px', fontSize: '28px', fontWeight: '600', color: '#6B7B88' });
      by.innerHTML = '<span>by</span>';
      C.leytonLogo(by, 88);
      a.by = by;
      a.cta = el(root, { left: '0', width: '1920px', top: '830px', display: 'flex', justifyContent: 'center' },
        `<div style="position:relative;overflow:hidden;height:92px;padding:0 16px 0 44px;border-radius:46px;background:#012D48;color:#fff;display:flex;align-items:center;gap:26px;font-size:38px;font-weight:700;box-shadow:0 24px 50px rgba(1,45,72,0.28)">Book a demo<div style="width:64px;height:64px;border-radius:32px;background:#fff;display:flex;align-items:center;justify-content:center">${icon('arrow-up-right', 32, 2.6, '#012D48')}</div></div>`);
      a.btn = a.cta.firstChild;
      a.sheen = h('div', '', a.btn);
      Object.assign(a.sheen.style, { position: 'absolute', top: '-20px', left: '0', width: '120px', height: '140px', background: 'linear-gradient(100deg, rgba(255,255,255,0), rgba(255,255,255,0.32), rgba(255,255,255,0))', transform: 'translateX(-200px) skewX(-20deg)' });
    },
    update(T) {
      const ip = P(T, 52.7, 0.5, Ease.inOutCubic);
      const R = lerp(0, 1300, ip);
      a.root.style.clipPath = ip < 1 ? `circle(${R}px at 960px 540px)` : 'none';
      a.ring(960, 540, ip < 1 ? R : 0, 22, T * 100, 1);
      a.bg(T);
      const ic = spring(T - 53.0, 200, 15);
      S(a.ic, { s: Math.max(0.0001, ic), r: (1 - ic) * -120, o: clamp((T - 53.0) * 6) });
      riseChars(T, [...a.digi._c, ...a.craft._c], 53.15, 0.035, 0.6, 80);
      a.recap.forEach((g, i) => {
        const p = spring(T - (53.9 + i * 0.22), 220, 15);
        S(g, { y: (1 - p) * 50, s: Math.max(0.0001, 0.8 + 0.2 * p), o: clamp((T - 53.9 - i * 0.22) * 6) });
      });
      a.arrows.forEach((e, i) => S(e, { x: (1 - P(T, 54.1 + i * 0.22, 0.4, Ease.outCubic)) * -16, o: P(T, 54.1 + i * 0.22, 0.4) }));
      const bp = P(T, 55.2, 0.6, Ease.outQuint);
      S(a.by, { y: (1 - bp) * 30, o: bp });
      const cp = spring(T - 55.7, 190, 15);
      S(a.cta, { y: (1 - cp) * 60, o: clamp((T - 55.7) * 5) });
      const sh = ((T - 56.4) % 1.6) / 0.9;
      a.sheen.style.transform = `translateX(${T > 56.4 && sh < 1 ? lerp(-200, 560, Ease.inOutSine(sh)) : -200}px) skewX(-20deg)`;
      S(a.lock, { s: 0.98 + 0.03 * P(T, 53.2, 6, Ease.outSine) });
    },
  });
})();

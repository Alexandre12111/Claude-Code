(function () {
  const { h, S, P, Ease, lerp, clamp, chars, icon, spring } = E;
  const { el, warmBg, riseChars, shine, ringFx } = C;
  let a = {};
  const TAG = 'Build, ohne Code.';
  const TY0 = 47.25, TY1 = 48.15;

  SCENES.push({
    id: 'fin',
    build(root) {
      a.root = root;
      a.bg = warmBg(root, 4);
      a.ring = C.ycSketch(root, 1000, 'rgba(11,11,15,0.08)');
      Object.assign(a.ring.style, { left: 960 - 500 + 'px', top: 400 - 500 + 'px', opacity: '0' });
      const lock = el(root, { left: '0', width: '1920px', top: '250px', height: '200px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '40px' });
      a.lock = lock;
      a.L = C.ycLogo(lock, 190);
      a.tag = el(root, { left: '0', width: '1920px', top: '500px', textAlign: 'center', fontSize: '84px', color: 'var(--navy)' });
      a.tag.classList.add('nh');
      a.tagC = chars(a.tag, TAG);
      a.tagC._c.forEach((c, i) => { if (i < 5) c.classList.add('o'); c.style.opacity = '0'; });
      a.caret = el(root, { top: '512px', width: '6px', height: '76px', background: '#1E3BFF', borderRadius: '3px' });
      a.tagU = el(root, { left: 960 - 330 + 'px', width: '660px', top: '612px', height: '8px', borderRadius: '4px', background: 'var(--orange)', transformOrigin: '0 50%' });
      const by = el(root, { left: '0', width: '1920px', top: '680px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '22px', fontSize: '30px', fontWeight: '600', color: '#8F8C86' });
      by.innerHTML = '<span>von</span>';
      C.leytonLogo(by, 92);
      a.by = by;
      a.cta = el(root, { left: '0', width: '1920px', top: '850px', display: 'flex', justifyContent: 'center' },
        `<div style="height:92px;padding:0 16px 0 44px;border-radius:46px;background:#0B0B0F;color:#F5F2EA;display:flex;align-items:center;gap:26px;font-size:38px;font-weight:700;box-shadow:0 24px 50px rgba(11,11,15,0.28)">Demo anfordern<div style="width:64px;height:64px;border-radius:32px;background:#D7FF3F;display:flex;align-items:center;justify-content:center">${icon('arrow-up-right', 32, 2.6, '#0B0B0F')}</div></div>`);
      a.btn = a.cta.firstChild;
      a.btn.style.position = 'relative'; a.btn.style.overflow = 'hidden';
      a.sheen = E.h('div', '', a.btn);
      Object.assign(a.sheen.style, { position: 'absolute', top: '-20px', left: '0', width: '120px', height: '140px', background: 'linear-gradient(100deg, rgba(255,255,255,0), rgba(255,255,255,0.32), rgba(255,255,255,0))', transform: 'translateX(-200px) skewX(-20deg)' });
      a.ringR = ringFx(root);
    },
    update(T) {
      const ip = P(T, 46.1, 0.45, Ease.inOutCubic);
      a.root.style.clipPath = ip < 1 ? `circle(${lerp(0, 1300, ip)}px at 960px 540px)` : 'none';
      a.ringR(960, 540, ip < 1 ? lerp(0, 1300, ip) : 0, 22, T * 100, 1);
      a.bg(T);
      S(a.ring, { s: 0.92 + 0.08 * P(T, 46.2, 3, Ease.outSine), o: P(T, 46.2, 1) });
      C.ycLogoAnim(a.L, T, 46.35);
      riseChars(T, a.L.glyphs, 46.6, 0.035, 0.6, 90);
      const cs = a.tagC._c, step = (TY1 - TY0) / cs.length;
      let lastX = null;
      cs.forEach((c, i) => {
        const p = clamp((T - (TY0 + i * step)) / 0.1);
        S(c, { o: p, y: (1 - p) * 10 });
        if (p > 0) { const r = c.getBoundingClientRect(); lastX = r.right; }
      });
      if (lastX === null) lastX = cs[0].getBoundingClientRect().left;
      const caretOn = T > 47.0 && T < 49.6 && (T < TY1 + 0.1 || Math.floor(T * 2.2) % 2 === 0);
      a.caret.style.left = lastX + 6 + 'px';
      a.caret.style.opacity = caretOn ? '1' : '0';
      const u = P(T, 48.2, 0.5, Ease.outExpo);
      S(a.tagU, { sx: Math.max(0.0001, u), o: u > 0 ? 1 : 0 });
      const bp = P(T, 48.45, 0.6, Ease.outQuint);
      S(a.by, { y: (1 - bp) * 30, o: bp });
      const cp = spring(T - 48.85, 190, 15);
      S(a.cta, { y: (1 - cp) * 60, o: clamp((T - 48.85) * 5) });
      S(a.btn, { s: 1 + 0.03 * Math.max(0, Math.sin((T - 49.4) * 4)) * (T > 49.4 ? 1 : 0) });
      const sh = ((T - 49.3) % 1.6) / 0.9;
      a.sheen.style.transform = `translateX(${T > 49.3 && sh < 1 ? lerp(-200, 560, Ease.inOutSine(sh)) : -200}px) skewX(-20deg)`;
      S(a.lock, { s: 0.98 + 0.03 * P(T, 46.5, 4, Ease.outSine) });
    },
  });
})();

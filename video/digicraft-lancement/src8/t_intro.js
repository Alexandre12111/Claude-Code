// Intro : « How DigiCraft works » et annonce des 4 étapes.
(function () {
  const { h, S, P, Ease, clamp, lerp, words, revealWords, chars, spring, icon } = E;
  const { el, navyBg, riseChars, shine, glow } = C;
  let a = {};
  const STEPS = [['square-pen', 'Describe'], ['wand-sparkles', 'Build'], ['message-square', 'Refine'], ['rocket', 'Publish']];

  SCENES.push({
    id: 'intro',
    build(root) {
      a.root = root;
      a.bg = navyBg(root, 4);
      a.gl = glow(root, 1300, 'rgba(46,139,192,0.45)', 0);
      const lock = el(root, { left: '0', width: '1920px', top: '200px', display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '18px' });
      a.lock = lock;
      a.ic = h('div', '', lock, window.dcIcon(64, 'ti', 'brand'));
      const wm = h('div', 'nh', lock);
      Object.assign(wm.style, { fontSize: '58px', color: '#fff', lineHeight: '1', paddingTop: '8px' });
      wm.innerHTML = 'Digi<span style="color:#6DBCE9">Craft</span>';
      a.wm = wm;
      const t = el(root, { left: '0', width: '1920px', top: '330px', textAlign: 'center', fontSize: '128px', color: '#fff', lineHeight: '1.05' });
      t.classList.add('nh');
      a.l1 = chars(el(t, { position: 'relative' }), 'How DigiCraft');
      a.l2 = chars(el(t, { position: 'relative' }), 'works');
      a.l2._c.forEach((c) => c.classList.add('grad'));
      a.t = t;
      const sub = el(root, { left: '0', width: '1920px', top: '630px', textAlign: 'center', fontSize: '40px', fontWeight: '600', color: '#CFE3F1' });
      a.sub = words(sub, 'From idea to live app, in 4 steps.');
      a.steps = STEPS.map(([ic, lab], i) => {
        const x = 960 + (i - 1.5) * 300;
        const g = el(root, { left: x - 130 + 'px', top: '760px', width: '260px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '14px' });
        g.innerHTML = `<div style="width:92px;height:92px;border-radius:28px;background:rgba(46,139,192,0.22);border:1.5px solid rgba(109,188,233,0.55);display:flex;align-items:center;justify-content:center;position:relative">${icon(ic, 44, 2, '#9FD2F2')}<div style="position:absolute;right:-10px;top:-10px;width:34px;height:34px;border-radius:17px;background:#2E8BC0;color:#fff;font:800 18px Montserrat;display:flex;align-items:center;justify-content:center">${i + 1}</div></div><div style="font:700 28px Montserrat;color:#fff">${lab}</div>`;
        return g;
      });
      a.line = el(root, { left: 960 - 450 + 'px', top: '806px', width: '900px', height: '3px', background: 'linear-gradient(90deg, rgba(109,188,233,0), rgba(109,188,233,0.6), rgba(109,188,233,0))', transformOrigin: '0 50%' });
      root.insertBefore(a.line, a.steps[0]);
    },
    update(T) {
      a.bg(T);
      const gi = P(T, 0.2, 1.4, Ease.outCubic);
      S(a.gl, { x: 960, y: 470, s: 0.6 + 0.4 * gi, o: gi });
      const lp = spring(T - 0.25, 200, 16);
      S(a.lock, { y: (1 - lp) * 40, o: clamp((T - 0.25) * 5) });
      S(a.ic, { r: (1 - lp) * -90 });
      riseChars(T, a.l1._c, 0.55, 0.03, 0.6, 90);
      riseChars(T, a.l2._c, 0.85, 0.04, 0.6, 90);
      shine(a.l2._c, T, 1.5, 0.9, 150);
      revealWords(T, a.sub, 1.35, 0.06, 0.6);
      S(a.line, { sx: Math.max(0.0001, P(T, 2.0, 0.9, Ease.outCubic)) });
      a.steps.forEach((g, i) => {
        const p = spring(T - (2.15 + i * 0.16), 220, 15);
        S(g, { y: (1 - p) * 60, s: Math.max(0.0001, 0.7 + 0.3 * p), o: clamp((T - 2.15 - i * 0.16) * 6) });
      });
      // légère poussée avant l'ouverture circulaire vers l'application
      const push = P(T, 3.6, 1.2, Ease.inCubic);
      S(a.t, { s: 1 + 0.05 * push });
    },
  });
})();

(function () {
  const { S, P, Ease, words, revealWords, hideWords } = E;
  const { el, warmBg } = C;
  let a = {};
  SCENES.push({
    id: 'decouvrez',
    build(root) {
      a.root = root;
      a.bg = warmBg(root, 2);
      const t = el(root, { left: '0', width: '1920px', top: '360px', textAlign: 'center', fontSize: '104px', color: 'var(--navy)', lineHeight: '1.12' });
      t.classList.add('nh');
      a.l1 = words(t, 'Découvrez la solution');
      a.l2 = words(t, [{ t: 'qui' }, { t: 'répond' }, { t: 'à' }, { t: 'votre', c: 'o' }, { t: 'besoin.', c: 'o' }]);
      a.t = t;
      a.u = el(t, { left: '1030px', top: '238px', width: '520px', height: '12px', borderRadius: '6px', background: 'linear-gradient(90deg, #EB6739, #F6AF84)', transformOrigin: '0 50%' });
    },
    update(T) {
      S(a.root, { o: P(T, 20.0, 0.3, Ease.outCubic) });
      a.bg(T);
      revealWords(T, a.l1, 20.4, 0.08, 0.6);
      revealWords(T, a.l2, 20.75, 0.08, 0.6);
      if (!a.uPos) {
        const w = a.l2._w, r0 = w[3].parentElement.getBoundingClientRect(), r1 = w[4].parentElement.getBoundingClientRect();
        a.uPos = true;
        const rt = a.t.getBoundingClientRect();
        Object.assign(a.u.style, { left: r0.left - rt.left + 'px', width: r1.right - r0.left + 'px' });
      }
      const out = P(T, 21.9, 0.45, Ease.inCubic);
      const push = P(T, 20.3, 1.8, Ease.outSine);
      S(a.t, { s: 1 + 0.04 * push + 0.35 * out, o: 1 - out, blur: out * 14 });
      const up = P(T, 21.2, 0.5, Ease.outExpo);
      S(a.u, { sx: Math.max(0.0001, up), o: (up > 0 ? 1 : 0) * (1 - out) });
    },
  });
})();

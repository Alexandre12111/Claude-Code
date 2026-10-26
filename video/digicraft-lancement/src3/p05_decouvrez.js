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
    },
    update(T) {
      S(a.root, { o: P(T, 20.0, 0.3, Ease.outCubic) });
      a.bg(T);
      revealWords(T, a.l1, 20.4, 0.08, 0.6);
      revealWords(T, a.l2, 20.75, 0.08, 0.6);
      hideWords(T, a.l1, 21.95, 0.03, 0.35);
      hideWords(T, a.l2, 22.0, 0.03, 0.35);
    },
  });
})();

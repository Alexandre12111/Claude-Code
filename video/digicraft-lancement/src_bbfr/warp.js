// Version française allongée : certaines mesures de 2,5 s du film d'origine sont jouées deux fois plus lentement
// (plans métier, plateforme, démo de création, tablette, rafale de demandes, mosaïque). Même table que audio/warp_bbfr.py.
(function () {
  const F = [1, 2, 2, 1, 1, 1, 2, 1, 1, 2, 2, 2, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1];
  const OFF = 0.7, BAR = 2.5;
  const B = [OFF];
  F.forEach((f, j) => B.push(B[j] + BAR * f));
  // Temps de sortie -> temps du film d'origine.
  window.WARP = (t) => {
    if (t <= OFF) return t;
    for (let j = 0; j < F.length; j++) if (t < B[j + 1]) return OFF + BAR * j + (t - B[j]) / F[j];
    return OFF + BAR * F.length + (t - B[F.length]);
  };
  const r = window.render;
  window.render = (t) => r(window.WARP(t));
})();

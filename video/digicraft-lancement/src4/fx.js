// Couche de finition globale (temps réel de la vidéo) : grain, vignette, fuites de lumière, flashs.
(function () {
  const { h, clamp, lerp, Ease, seeded } = E;
  const TL = window.TL;
  const root = h('div', '', document.body);
  root.id = 'fx';

  // Grain argentique : 8 tuiles de bruit pré-calculées, changées 24 fois par seconde.
  const tiles = [];
  const rnd = seeded(97);
  for (let k = 0; k < 8; k++) {
    const c = document.createElement('canvas');
    c.width = c.height = 256;
    const ctx = c.getContext('2d');
    const img = ctx.createImageData(256, 256);
    for (let i = 0; i < img.data.length; i += 4) {
      const v = 128 + (rnd() + rnd() + rnd() - 1.5) * 150;
      img.data[i] = img.data[i + 1] = img.data[i + 2] = v;
      img.data[i + 3] = 255;
    }
    ctx.putImageData(img, 0, 0);
    tiles.push(`url(${c.toDataURL()})`);
  }
  const grain = h('div', 'fx-grain', root);
  const vign = h('div', 'fx-vignette', root);

  // Fuites de lumière sur les transitions : [temps, direction, teinte, intensité].
  const LEAKS = TL.leaks || [];
  const leaks = LEAKS.map(([t, dir, tint, g]) => {
    const e = h('div', 'fx-leak', root);
    e.style.background = tint === 'warm'
      ? 'radial-gradient(closest-side, rgba(255,214,176,0.95), rgba(248,168,126,0.55) 38%, rgba(235,103,57,0.18) 70%, rgba(235,103,57,0) 100%)'
      : 'radial-gradient(closest-side, rgba(255,236,220,0.9), rgba(244,160,113,0.45) 40%, rgba(235,103,57,0) 100%)';
    return { e, t, dir, g };
  });
  const FLASH = TL.flashes || [];
  const flash = h('div', 'fx-flash', root);

  window.FX = {
    update(T) {
      // Grain : tuile et décalage déterministes par image de 1/24 s.
      const f = Math.floor(T * 24);
      const r = seeded(f * 7919 + 13);
      grain.style.backgroundImage = tiles[f % tiles.length];
      grain.style.backgroundPosition = `${Math.floor(r() * 256)}px ${Math.floor(r() * 256)}px`;
      // Passage clair/sombre en fondu de 0,5 s centré sur la transition (pas de saut de vignette).
      const sm = (x) => { x = clamp(x); return x * x * (3 - 2 * x); };
      const dk = Math.max(0, ...(TL.darkZones || []).map(([a, b]) => sm((T - a + 0.25) / 0.5) * sm((b + 0.25 - T) / 0.5)));
      grain.style.opacity = lerp(0.06, 0.085, dk).toFixed(4);
      vign.style.opacity = lerp(0.45, 0.9, dk).toFixed(4);

      for (const L of leaks) {
        const u = (T - (L.t - 0.55)) / 1.25;
        if (u < 0 || u > 1) { L.e.style.display = 'none'; continue; }
        L.e.style.display = 'block';
        const env = Math.sin(Math.PI * u) ** 1.5;
        const x = lerp(-700, 2620, Ease.inOutSine(u)) * (L.dir > 0 ? 1 : -1) + (L.dir > 0 ? 0 : 1920);
        const y = lerp(760, 320, u);
        L.e.style.transform = `translate(${(x - 900).toFixed(1)}px, ${(y - 700).toFixed(1)}px) rotate(${(-18 * L.dir).toFixed(1)}deg) scale(${(1 + 0.25 * u).toFixed(3)}, 0.62)`;
        L.e.style.opacity = (env * L.g).toFixed(3);
      }

      let fl = 0;
      for (const [t, g] of FLASH) {
        if (T < t - 0.12 || T > t + 0.6) continue;
        const v = T < t ? Ease.inCubic(clamp((T - (t - 0.12)) / 0.12)) : Math.exp(-(T - t) * 7);
        fl = Math.max(fl, v * g);
      }
      flash.style.opacity = fl.toFixed(3);
      flash.style.display = fl > 0.002 ? 'block' : 'none';
    },
  };
})();

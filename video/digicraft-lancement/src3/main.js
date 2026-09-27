(function () {
  const stage = document.getElementById('stage');
  const TL = window.TL;
  const mkRemap = (pts) => (T) => {
    if (!pts) return T;
    if (T <= pts[0][0]) return pts[0][1] + (T - pts[0][0]);
    for (let i = 1; i < pts.length; i++) {
      if (T <= pts[i][0]) {
        const [a0, b0] = pts[i - 1], [a1, b1] = pts[i];
        return b0 + ((T - a0) / (a1 - a0)) * (b1 - b0);
      }
    }
    const l = pts[pts.length - 1];
    return l[1] + (T - l[0]);
  };
  const scenes = window.SCENES.filter((s) => TL.scenes[s.id]).map((s) => {
    const root = E.h('div', 'scene ' + (s.cls || ''), stage);
    root.id = 'sc-' + s.id;
    const [t0, t1] = TL.scenes[s.id];
    s.build(root);
    return { ...s, root, t0, t1, visible: false, map: mkRemap(TL.remap && TL.remap[s.id]) };
  });
  const order = Object.keys(TL.scenes);
  scenes.sort((a, b) => order.indexOf(a.id) - order.indexOf(b.id));
  scenes.forEach((s, i) => (s.root.style.zIndex = String(10 + (s.z || i))));

  // Petit « punch » de caméra sur les temps forts de la musique.
  const beat = 60 / TL.bpm;
  const punchZones = TL.punchZonesNew || [[20.4, 34.8], [34.8, 40.2]];
  // Courbe de temps lissée (Hermite monotone) : pas de changement de vitesse brutal.
  function pchip(pts) {
    const x = pts.map((p) => p[0]), y = pts.map((p) => p[1]), n = pts.length;
    const d = [], m = [];
    for (let i = 0; i < n - 1; i++) d.push((y[i + 1] - y[i]) / (x[i + 1] - x[i]));
    m[0] = d[0]; m[n - 1] = d[n - 2];
    for (let i = 1; i < n - 1; i++) {
      const h0 = x[i] - x[i - 1], h1 = x[i + 1] - x[i];
      m[i] = d[i - 1] * d[i] <= 0 ? 0 : (3 * (h0 + h1)) / ((2 * h1 + h0) / d[i - 1] + (h1 + 2 * h0) / d[i]);
    }
    return (t) => {
      if (t <= x[0]) return y[0] + (t - x[0]) * m[0];
      if (t >= x[n - 1]) return y[n - 1] + (t - x[n - 1]) * m[n - 1];
      let i = 0;
      while (t > x[i + 1]) i++;
      const h = x[i + 1] - x[i], s = (t - x[i]) / h;
      const h00 = 2 * s * s * s - 3 * s * s + 1, h10 = s * s * s - 2 * s * s + s, h01 = -2 * s * s * s + 3 * s * s, h11 = s * s * s - s * s;
      return h00 * y[i] + h10 * h * m[i] + h01 * y[i + 1] + h11 * h * m[i + 1];
    };
  }
  const retime = TL.retime ? pchip(TL.retime) : (T) => T;
  function punch(T) {
    let v = 0;
    for (const [a, b] of punchZones) {
      if (T < a || T > b + 0.4) continue;
      const k = Math.floor((T - a) / (beat * 2));
      const tb = a + k * beat * 2;
      if (tb <= b) v = Math.max(v, Math.exp(-(T - tb) * 9));
    }
    for (const t of TL.hitsNew || TL.hits || []) if (T >= t) v = Math.max(v, 1.6 * (1 - Math.exp(-(T - t) * 30)) * Math.exp(-(T - t) * 5));
    return v;
  }

  window.render = function (T) {
    const pv = punch(T);
    stage.style.transform = pv > 0.001 ? `scale(${(1 + 0.008 * pv).toFixed(5)})` : 'none';
    const T3 = retime(T);
    for (const s of scenes) {
      const vis = T3 >= s.t0 && T3 < s.t1;
      if (vis !== s.visible) {
        s.root.style.display = vis ? 'block' : 'none';
        s.visible = vis;
      }
      if (vis) s.update(s.map(T3), T3);
    }
  };

  const imgs = [...document.images];
  Promise.all([
    document.fonts.ready,
    ...imgs.map((im) => (im.complete ? Promise.resolve() : new Promise((r) => { im.onload = r; im.onerror = r; }))),
  ]).then(() => {
    const q = new URLSearchParams(location.search);
    window.render(parseFloat(q.get('t') || '0'));
    window.READY = true;
  });
})();

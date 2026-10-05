(function () {
  const stage = document.getElementById('stage');
  const TL = window.TL;
  const order = Object.keys(TL.scenes);
  const scenes = window.SCENES.filter((s) => TL.scenes[s.id]).map((s) => {
    const root = E.h('div', 'scene', stage);
    root.id = 'sc-' + s.id;
    s.build(root);
    const [t0, t1] = TL.scenes[s.id];
    return { ...s, root, t0, t1, visible: false };
  });
  scenes.sort((a, b) => order.indexOf(a.id) - order.indexOf(b.id));
  scenes.forEach((s, i) => (s.root.style.zIndex = String(10 + i)));
  window.render = function (T) {
    for (const s of scenes) {
      const vis = T >= s.t0 && T < s.t1;
      if (vis !== s.visible) { s.root.style.display = vis ? 'block' : 'none'; s.visible = vis; }
      if (vis) s.update(T);
    }
    if (window.FX) window.FX.update(T);
  };
  Promise.all([document.fonts.ready, ...[...document.images].map((im) => (im.complete ? 1 : new Promise((r) => { im.onload = r; im.onerror = r; })))]).then(() => {
    window.render(parseFloat(new URLSearchParams(location.search).get('t') || '0'));
    window.READY = true;
  });
})();

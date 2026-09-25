(function () {
  const stage = document.getElementById('stage');
  const scenes = window.SCENES.map((s) => {
    const root = E.h('div', 'scene ' + (s.cls || ''), stage);
    root.id = 'sc-' + s.id;
    const [t0, t1] = window.TL.scenes[s.id];
    s.build(root);
    return { ...s, root, t0, t1, visible: false };
  });
  scenes.sort((a, b) => (a.z || 0) - (b.z || 0));
  scenes.forEach((s, i) => (s.root.style.zIndex = String(10 + (s.z || i))));

  window.render = function (T) {
    for (const s of scenes) {
      const vis = T >= s.t0 && T < s.t1;
      if (vis !== s.visible) {
        s.root.style.display = vis ? 'block' : 'none';
        s.visible = vis;
      }
      if (vis) s.update(T);
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

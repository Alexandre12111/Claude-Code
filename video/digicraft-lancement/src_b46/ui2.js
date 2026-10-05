// V2 : fonds plus riches (aurores, sol en perspective, particules), photos, mini dashboards, volets.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon, seeded } = E;
  const el = C.el;

  // Aurore : base en dégradé, halos vifs à grande amplitude, rayons et trame de points.
  const AUR = {
    light: { base: 'linear-gradient(160deg,#F4F8FC 0%,#E9F1FA 50%,#F3EEFF 100%)', blobs: ['#7CC3EC', '#A99BFF', '#5FD3E0', '#C9B8FF', '#8FC6E7'], o: 0.55, dots: 'rgba(2,36,70,0.09)' },
    dark: { base: 'radial-gradient(1400px 900px at 70% 20%, #0F4F80 0%, #062A4C 45%, #010F1F 100%)', blobs: ['#2E8BC0', '#6D5DF6', '#0EA5A4', '#A855F7', '#3B6FD8'], o: 0.55, dots: 'rgba(255,255,255,0.07)', rays: true },
    violet: { base: 'radial-gradient(1400px 900px at 30% 20%, #3B2A8C 0%, #1C1550 50%, #0A0826 100%)', blobs: ['#6D5DF6', '#A855F7', '#2E8BC0', '#E2D4FF', '#8F7CF5'], o: 0.5, dots: 'rgba(255,255,255,0.06)', rays: true },
    teal: { base: 'radial-gradient(1400px 900px at 60% 30%, #0B5F6E 0%, #063B4A 50%, #021A24 100%)', blobs: ['#0EA5A4', '#2E8BC0', '#5FD3E0', '#8FC6E7', '#6D5DF6'], o: 0.5, dots: 'rgba(255,255,255,0.06)', rays: true },
    sky: { base: 'linear-gradient(170deg,#0A3358 0%,#2E8BC0 55%,#BFE3F7 100%)', blobs: ['#8FC6E7', '#B7A6FF', '#E2F1FA', '#2E8BC0', '#6D5DF6'], o: 0.5, dots: 'rgba(255,255,255,0.06)' },
  };
  function aurora(root, kind, seed = 0) {
    const c = AUR[kind];
    el(root, { inset: '0', background: c.base });
    const r = seeded(11 + seed * 13);
    const blobs = c.blobs.map((col, i) => ({ e: C.glow(root, 900 + r() * 700, col, c.o), x: r() * 1920, y: r() * 1080, ax: 260 + r() * 260, ay: 160 + r() * 160, k: r() * 6, sp: 0.25 + r() * 0.25 }));
    let rays = null;
    if (c.rays) { rays = h('div', 'rays', root); rays.style.opacity = '0.8'; }
    const dots = el(root, { inset: '-60px', backgroundImage: `radial-gradient(${c.dots} 1.4px, transparent 1.7px)`, backgroundSize: '38px 38px',
      webkitMask: 'radial-gradient(ellipse 70% 60% at 50% 50%, #000, rgba(0,0,0,0) 80%)', mask: 'radial-gradient(ellipse 70% 60% at 50% 50%, #000, rgba(0,0,0,0) 80%)' });
    const parts = particles(root, kind === 'light' ? 'rgba(46,139,192,0.55)' : 'rgba(255,255,255,0.75)', 34, seed);
    return (T) => {
      blobs.forEach((b) => S(b.e, { x: b.x + Math.sin(T * b.sp + b.k) * b.ax, y: b.y + Math.cos(T * b.sp * 0.8 + b.k * 1.3) * b.ay, s: 1 + 0.12 * Math.sin(T * 0.6 + b.k) }));
      if (rays) S(rays, { r: T * 3 });
      S(dots, { x: (T * 6) % 38, y: (T * 3) % 38 });
      parts(T);
    };
  }

  // Particules lumineuses qui dérivent en profondeur.
  function particles(root, col, n = 30, seed = 0) {
    const r = seeded(77 + seed);
    const ps = Array.from({ length: n }, () => {
      const d = 2 + r() * 6;
      return { e: el(root, { left: '0', top: '0', width: d + 'px', height: d + 'px', borderRadius: '50%', background: col, boxShadow: `0 0 ${d * 3}px ${col}` }), x: r() * 1920, y: r() * 1080, v: 10 + r() * 40, k: r() * 6, o: 0.3 + r() * 0.6 };
    });
    return (T) => ps.forEach((p) => S(p.e, { x: (p.x + Math.sin(T * 0.4 + p.k) * 40) % 1920, y: ((p.y - T * p.v) % 1080 + 1080) % 1080, o: p.o * (0.6 + 0.4 * Math.sin(T * 2 + p.k)) }));
  }

  // Sol en perspective (grille lumineuse) avec horizon.
  function floor(root, col = 'rgba(143,198,231,0.35)') {
    const wrap = el(root, { left: '-1200px', width: '4320px', top: '560px', height: '1400px', perspective: '700px', perspectiveOrigin: '50% 0%' });
    const g = el(wrap, { left: '0', top: '0', width: '4320px', height: '2600px', transformOrigin: '50% 0%',
      backgroundImage: `linear-gradient(${col} 2px, transparent 2px), linear-gradient(90deg, ${col} 2px, transparent 2px)`, backgroundSize: '120px 120px',
      webkitMask: 'linear-gradient(180deg, rgba(0,0,0,0) 0%, #000 25%, #000 100%)', mask: 'linear-gradient(180deg, rgba(0,0,0,0) 0%, #000 25%, #000 100%)' });
    const hz = el(root, { left: '0', right: '0', top: '440px', height: '260px', background: 'radial-gradient(60% 50% at 50% 50%, rgba(143,198,231,0.55), rgba(143,198,231,0) 100%)' });
    return (T) => { g.style.transform = `rotateX(72deg) translateY(${(T * 140) % 120}px)`; S(hz, { o: 0.8 + 0.2 * Math.sin(T * 2) }); };
  }

  // Photo détourée (charte Leyton recolorée en bleu).
  function photo(root, name, x, y, hh) {
    const im = h('img', 'abs', root);
    im.src = `../assets/img/b44/${name}.png`;
    Object.assign(im.style, { left: x + 'px', top: y + 'px', height: hh + 'px' });
    return im;
  }

  // Mini dashboards (280 × 190) variés pour la galaxie et l'intro.
  const DASH = [
    ['Revenue', '€48.2M', '+6.2%', 'line'], ['Pipeline', '€12.6M', '+18%', 'bars'], ['Onboarding', '86%', 'on track', 'donut'], ['Audits', '312', 'this quarter', 'table'],
    ['Cash flow', '€3.1M', '+€1.4M', 'line'], ['Headcount', '12,400', '+4.1%', 'bars'], ['Approvals', '94%', 'within SLA', 'donut'], ['Projects', '57', '8 at risk', 'table'],
    ['NPS', '62', '+5 pts', 'line'], ['Tickets', '1,284', '−12%', 'bars'], ['Training', '78%', 'completed', 'donut'], ['Suppliers', '146', '12 to review', 'table'],
  ];
  const ACC = ['#2E8BC0', '#6D5DF6', '#0EA5A4', '#A855F7', '#3B6FD8', '#16A34A'];
  function dash(parent, i, dark = false) {
    const [ti, v, d, kind] = DASH[i % DASH.length];
    const ac = ACC[i % ACC.length];
    const t = el(parent, { left: '0', top: '0', width: '280px', height: '190px', borderRadius: '16px', overflow: 'hidden', background: dark ? 'linear-gradient(160deg,#0B3A63,#04203C)' : '#fff', boxShadow: '0 18px 48px rgba(0,0,0,0.35)', transformOrigin: '0 0', fontFamily: 'DM Sans' });
    const fg = dark ? '#fff' : '#0B2545', mu = dark ? '#8FC6E7' : '#64748B';
    el(t, { left: '16px', top: '14px', fontSize: '13px', fontWeight: '700', color: mu, display: 'flex', alignItems: 'center', gap: '7px' }, `<span style="width:8px;height:8px;border-radius:3px;background:${ac}"></span>${ti}`);
    el(t, { left: '16px', top: '34px', fontSize: '28px', fontWeight: '700', color: fg }, v);
    el(t, { right: '14px', top: '44px', fontSize: '12px', fontWeight: '700', color: d[0] === '−' || d.includes('risk') || d.includes('review') ? '#F59E0B' : '#16A34A' }, d);
    const r = seeded(500 + i);
    if (kind === 'line') {
      let p = 'M0 70'; for (let k = 1; k <= 8; k++) p += ` L${k * 31} ${70 - k * 6 - r() * 18}`;
      el(t, { left: '16px', top: '86px', width: '248px', height: '90px' }, `<svg width="248" height="90" viewBox="0 0 248 90"><path d="${p} L248 90 L0 90 Z" fill="${ac}" opacity="0.15"/><path d="${p}" fill="none" stroke="${ac}" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`);
    } else if (kind === 'bars') {
      let s = ''; for (let k = 0; k < 9; k++) { const bh = 20 + r() * 60; s += `<rect x="${k * 27.5}" y="${86 - bh}" width="18" height="${bh}" rx="4" fill="${ac}" opacity="${k === 8 ? 1 : 0.45}"/>`; }
      el(t, { left: '16px', top: '88px', width: '248px', height: '86px' }, `<svg width="248" height="86" viewBox="0 0 248 86">${s}</svg>`);
    } else if (kind === 'donut') {
      const f = parseFloat(v) / 100 || 0.8;
      el(t, { left: '16px', top: '80px', width: '100px', height: '100px' }, `<svg width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="36" fill="none" stroke="${dark ? 'rgba(255,255,255,0.15)' : '#E8EEF4'}" stroke-width="12"/><circle cx="50" cy="50" r="36" fill="none" stroke="${ac}" stroke-width="12" stroke-linecap="round" stroke-dasharray="226" stroke-dashoffset="${226 * (1 - f)}" transform="rotate(-90 50 50)"/></svg>`);
      ['Done', 'In progress', 'Planned'].forEach((l, k) => el(t, { left: '132px', top: 96 + k * 26 + 'px', fontSize: '12px', fontWeight: '600', color: mu, display: 'flex', gap: '7px', alignItems: 'center' }, `<span style="width:10px;height:10px;border-radius:3px;background:${ac};opacity:${1 - k * 0.3}"></span>${l}`));
    } else {
      for (let k = 0; k < 4; k++) {
        const row = el(t, { left: '16px', top: 84 + k * 25 + 'px', width: '248px', height: '20px', display: 'flex', alignItems: 'center', gap: '8px' });
        h('span', '', row).style.cssText = `width:${60 + r() * 60}px;height:8px;border-radius:4px;background:${dark ? 'rgba(255,255,255,0.25)' : '#CBD5E1'}`;
        h('span', '', row).style.cssText = 'flex:1';
        const st = ['#16A34A', '#F59E0B', ac][k % 3];
        h('span', '', row).style.cssText = `width:44px;height:14px;border-radius:7px;background:${st}33;border:1.5px solid ${st}`;
      }
    }
    return t;
  }

  // Volet « éclair » : bande lumineuse diagonale qui traverse l'écran.
  function sweep(root) {
    const b = el(root, { left: '0', top: '-300px', width: '700px', height: '1700px', background: 'linear-gradient(90deg, rgba(255,255,255,0), rgba(226,241,250,0.95) 45%, rgba(183,166,255,0.9) 55%, rgba(255,255,255,0))', transform: 'rotate(18deg)', zIndex: '90', display: 'none' });
    return (T, t0, dur = 0.5) => {
      const p = (T - t0) / dur;
      if (p < 0 || p > 1) { b.style.display = 'none'; return; }
      b.style.display = 'block';
      S(b, { x: lerp(-900, 2400, Ease.inOutCubic(p)), r: 18, sx: 1 + p });
    };
  }

  // Remplacement d'une scène existante par sa version V2.
  window.replaceScene = (sc) => { const i = SCENES.findIndex((s) => s.id === sc.id); if (i >= 0) SCENES.splice(i, 1); SCENES.push(sc); };
  Object.assign(window.U, { aurora, particles, floor, photo, dash, sweep });
})();

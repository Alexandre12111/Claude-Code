(function () {
  const { h, S, P, Ease, lerp, clamp, words, revealWords, icon, spring } = E;
  const el = (parent, style, html) => { const e = h('div', 'abs', parent, html); Object.assign(e.style, style); return e; };
  let els = {};

  const euStars = () => {
    let s = '';
    for (let i = 0; i < 12; i++) {
      const a = (i / 12) * Math.PI * 2 - Math.PI / 2;
      const x = 24 + Math.cos(a) * 14, y = 24 + Math.sin(a) * 14;
      s += `<path class="st" d="M${x} ${y - 3.2} L${x + 0.95} ${y - 0.95} L${x + 3.2} ${y - 0.95} L${x + 1.4} ${y + 0.6} L${x + 2.1} ${y + 2.9} L${x} ${y + 1.5} L${x - 2.1} ${y + 2.9} L${x - 1.4} ${y + 0.6} L${x - 3.2} ${y - 0.95} L${x - 0.95} ${y - 0.95} Z" fill="#FFCC00"/>`;
    }
    return `<svg width="66" height="66" viewBox="0 0 48 48">${s}</svg>`;
  };
  const ROWS = [
    [47.4, 'eu', 'Hébergé en Europe', 'Vos données restent dans l’Union européenne.'],
    [48.05, 'shield-check', 'Gouvernance intégrée', 'Sécurité et réversibilité dès le socle.'],
    [48.7, 'handshake', 'Accompagné par les équipes Leyton', 'Du cadrage au déploiement.'],
  ];

  SCENES.push({
    id: 'confiance',
    build(root) {
      els.root = root;
      root.style.background = 'var(--offwhite)';
      const grid = el(root, { inset: '-64px' });
      grid.className = 'abs bg-grid-light';
      els.grid = grid;
      els.blob = el(root, { width: '1000px', height: '1000px', left: '-200px', top: '200px', background: 'rgba(46,139,192,0.12)' });
      els.blob.className = 'blob';
      const ph = h('img', 'abs', root);
      ph.src = '../assets/img/photo_femmes_ordi.png';
      Object.assign(ph.style, { left: '30px', top: '150px', width: '921px', height: '928px' });
      els.photo = ph;

      els.rows = ROWS.map(([t0, ic, t, sub], i) => {
        const r = el(root, { left: '1000px', top: 190 + i * 170 + 'px', width: '860px', height: '140px', display: 'flex', alignItems: 'center', gap: '30px' });
        const c = el(r, { position: 'relative', width: '96px', height: '96px', borderRadius: '28px', background: 'var(--navy)', display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none', boxShadow: '0 14px 30px rgba(2,36,70,0.22)' }, ic === 'eu' ? euStars() : icon(ic, 46, 2, '#fff'));
        c.style.position = 'relative';
        const tx = h('div', '', r, `<div style="font-size:40px;font-weight:800;color:#022446;letter-spacing:-0.01em;white-space:nowrap">${t}</div><div style="font-size:26px;font-weight:500;color:#4A5568;margin-top:6px">${sub}</div>`);
        r._c = c; r._tx = tx; r._t0 = t0;
        r._stars = [...c.querySelectorAll('.st')];
        return r;
      });
      const big = el(root, { left: '1000px', top: '735px', fontSize: '112px', fontWeight: '800', color: 'var(--blue)', letterSpacing: '-0.02em', fontStyle: 'italic' });
      els.big = words(big, 'À vos côtés.');
      els.ul = el(root, { left: '1004px', top: '880px', width: '700px', height: '10px', borderRadius: '5px', background: 'var(--navy)', transformOrigin: '0 50%' });
    },
    update(T) {
      E.slash(T, 46.45, 0.75, els.root, els.bands || (els.bands = E.slashBands(els.root, ['var(--blue)', '#FFFFFF'])));
      S(els.grid, { x: -T * 6, y: -T * 4 });
      S(els.blob, { x: Math.sin(T * 0.5) * 50 });
      const pp = P(T, 46.9, 1.1, Ease.outExpo);
      const out = P(T, 53.35, 0.6, Ease.inCubic);
      S(els.photo, { x: (1 - pp) * -120 - out * 300 + (T - 47) * 6, o: pp * (1 - out) });
      els.rows.forEach((r, i) => {
        const p = spring(T - r._t0, 190, 17);
        S(r._c, { s: Math.max(0.0001, p), r: (1 - p) * -30 });
        const tp = P(T, r._t0 + 0.08, 0.6, Ease.outQuint);
        S(r._tx, { x: (1 - tp) * 60, o: tp });
        S(r, { x: -out * (200 + i * 60), o: 1 - out });
        r._stars.forEach((s, k) => { s.style.opacity = T > r._t0 + 0.1 + k * 0.04 ? '1' : '0'; });
      });
      revealWords(T, els.big, 50.2, 0.12, 0.7);
      const u = P(T, 50.75, 0.6, Ease.outExpo);
      S(els.ul, { sx: Math.max(0.0001, u * 0.62), o: u > 0 ? 1 - out : 0 });
      const bw = els.big.parentElement;
      S(bw, { x: -out * 320, o: 1 - out });
    },
  });
})();

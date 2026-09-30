(function () {
  const { h, S, P, Ease, lerp, clamp, words, revealWords, icon, spring } = E;
  const { el, warmBg } = C;
  let a = {};

  const euStars = () => {
    let s = '';
    for (let i = 0; i < 12; i++) {
      const g = (i / 12) * Math.PI * 2 - Math.PI / 2;
      const x = 24 + Math.cos(g) * 14, y = 24 + Math.sin(g) * 14;
      s += `<path class="st" d="M${x} ${y - 3.2} L${x + 0.95} ${y - 0.95} L${x + 3.2} ${y - 0.95} L${x + 1.4} ${y + 0.6} L${x + 2.1} ${y + 2.9} L${x} ${y + 1.5} L${x - 2.1} ${y + 2.9} L${x - 1.4} ${y + 0.6} L${x - 3.2} ${y - 0.95} L${x - 0.95} ${y - 0.95} Z" fill="#FFCC00"/>`;
    }
    return `<svg width="62" height="62" viewBox="0 0 48 48">${s}</svg>`;
  };
  const ROWS = [
    [41.1, 'eu', 'Ospitato in Europa', 'I vostri dati restano nell’Unione europea.'],
    [42.2, 'shield-check', 'Governance integrata', 'Accessi, permessi e tracciabilità sotto controllo.'],
    [43.05, 'mouse-pointer-click', 'Intuitivo fin dal primo minuto', 'Nessuna formazione né riga di codice necessaria.'],
  ];

  function dashboard(parent) {
    const d = el(parent, { left: '110px', top: '330px', width: '740px', height: '520px', borderRadius: '28px', background: '#fff', boxShadow: '0 40px 90px rgba(1,45,72,0.18)', border: '1px solid #E1E9ED', overflow: 'hidden', fontFamily: 'DM Sans' });
    el(d, { left: '0', top: '0', width: '170px', height: '520px', background: '#0B2545' });
    el(d, { left: '18px', top: '22px', display: 'flex', alignItems: 'center', gap: '8px', color: '#fff', fontWeight: '700', fontSize: '17px' }, `<div style="width:30px;height:30px;border-radius:8px;background:#2E8BC0;display:flex;align-items:center;justify-content:center">${icon('gauge', 17, 2.2, '#fff')}</div>Cruscotto DG`);
    ['Panoramica', 'Finanza', 'Commerciale', 'Risorse umane', 'Avvisi'].forEach((t, i) => el(d, { left: '12px', top: 84 + i * 42 + 'px', width: '146px', height: '32px', borderRadius: '8px', background: i ? 'transparent' : 'rgba(46,139,192,0.45)', color: i ? '#A9BCD0' : '#fff', fontSize: '13px', display: 'flex', alignItems: 'center', padding: '0 10px' }, t));
    el(d, { left: '196px', top: '22px', fontWeight: '700', fontSize: '24px', color: '#0B2545' }, 'Panoramica di gruppo');
    [['Fatturato', '48,2 M€'], ['Margine lordo', '31,4%'], ['Organico', '1.240'], ['Cassa', '12,8 M€']].forEach(([l, v], i) => {
      const k = el(d, { left: 196 + i * 132 + 'px', top: '76px', width: '122px', height: '84px', borderRadius: '12px', border: '1px solid #E5EAF0' });
      el(k, { left: '12px', top: '10px', fontSize: '12px', color: '#6B7280' }, l);
      el(k, { left: '12px', top: '34px', fontSize: '22px', fontWeight: '700', color: '#0B2545' }, v);
    });
    const ch = el(d, { left: '196px', top: '180px', width: '520px', height: '310px', borderRadius: '14px', border: '1px solid #E5EAF0' });
    el(ch, { left: '16px', top: '14px', fontWeight: '700', fontSize: '15px', color: '#0B2545' }, 'Fatturato mensile');
    [0.45, 0.55, 0.5, 0.62, 0.58, 0.7, 0.66, 0.82].forEach((v, i) => el(ch, { left: 26 + i * 60 + 'px', bottom: '22px', width: '38px', height: v * 220 + 'px', borderRadius: '7px 7px 2px 2px', background: i === 7 ? '#2E8BC0' : '#B3D8ED' }));
    return d;
  }

  SCENES.push({
    id: 'serenite',
    build(root) {
      a.root = root;
      a.bg = warmBg(root, 3);
      const t = el(root, { left: '0', width: '1920px', top: '110px', textAlign: 'center', fontSize: '88px', color: 'var(--navy)' });
      t.classList.add('nh');
      a.title = words(t, [{ t: 'Serenità' }, { t: 'e' }, { t: 'sicurezza' }, { t: 'garantite.', c: 'o' }]);
      a.dash = dashboard(root);
      a.scan = el(a.dash, { left: '0', top: '0', width: '740px', height: '120px', background: 'linear-gradient(180deg, rgba(46,139,192,0), rgba(46,139,192,0.16) 80%, rgba(46,139,192,0.9) 98%, rgba(46,139,192,0))' });
      a.veil = el(a.dash, { inset: '0', background: 'rgba(1,45,72,0.35)' });
      a.pulses = [0, 1, 2].map(() => el(root, { left: 480 - 170 + 'px', top: 590 - 170 + 'px', width: '340px', height: '340px', borderRadius: '50%', border: '3px solid rgba(46,139,192,0.9)' }));
      a.halo = el(root, { left: 480 - 190 + 'px', top: 590 - 190 + 'px', width: '380px', height: '380px', borderRadius: '50%', background: 'radial-gradient(circle, rgba(46,139,192,0.35), rgba(46,139,192,0) 70%)' });
      a.shield = el(root, { left: 480 - 130 + 'px', top: 590 - 140 + 'px', width: '260px', height: '280px' },
        `<svg width="260" height="280" viewBox="0 0 24 26"><path d="M12 1.5 L21 5 V12 C21 18 17 22.5 12 24.5 C7 22.5 3 18 3 12 V5 Z" fill="#012D48" stroke="#2E8BC0" stroke-width="1.2" stroke-linejoin="round"/></svg>`);
      a.lock = el(a.shield, { left: '80px', top: '80px' }, icon('lock-keyhole', 100, 1.8, '#fff'));
      a.rows = ROWS.map(([t0, ic, tt, sub], i) => {
        const r = el(root, { left: '960px', top: 360 + i * 175 + 'px', width: '900px', height: '130px', display: 'flex', alignItems: 'center', gap: '30px' });
        const c = h('div', '', r, ic === 'eu' ? euStars() : icon(ic, 50, 2, '#fff'));
        Object.assign(c.style, { width: '104px', height: '104px', borderRadius: '30px', background: 'var(--navy)', display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none', boxShadow: '0 14px 30px rgba(1,45,72,0.22)' });
        const tx = h('div', '', r, `<div class="nh" style="font-size:44px;color:#012D48;white-space:nowrap">${tt}</div><div style="font-size:28px;font-weight:500;color:#4A5568;margin-top:6px">${sub}</div>`);
        r._c = c; r._tx = tx; r._t0 = t0; r._stars = [...c.querySelectorAll('.st')];
        return r;
      });
    },
    update(T) {
      E.slash(T, 40.3, 0.6, a.root, a.bands || (a.bands = E.slashBands(a.root, C.BANDS.warm)));
      a.bg(T);
      revealWords(T, a.title, 40.65, 0.09, 0.6);
      const dp = P(T, 40.75, 0.8, Ease.outExpo);
      S(a.dash, { x: (1 - dp) * -200, o: dp, r: (1 - dp) * -4 });
      const lockT = 41.45;
      const sp = spring(T - lockT, 200, 13);
      S(a.shield, { s: Math.max(0.0001, sp), o: clamp((T - lockT) * 6), y: Math.sin(T * 2) * 4 });
      S(a.lock, { y: (1 - P(T, lockT + 0.35, 0.3, Ease.outBack)) * -30 });
      const vp = P(T, lockT, 0.4);
      S(a.veil, { o: vp });
      S(a.dash, { x: (1 - dp) * -200, o: dp, r: (1 - dp) * -4, blur: vp * 3 });
      const sc = P(T, 40.95, 0.55, Ease.inOutSine);
      S(a.scan, { y: lerp(-120, 520, sc), o: sc > 0 && sc < 1 ? 1 : 0 });
      a.pulses.forEach((q, j) => {
        const t0 = lockT + 0.25 + j * 0.45;
        const u = T > t0 ? ((T - t0) / 1.35) % 1 : 0;
        S(q, { s: 0.75 + Ease.outCubic(u) * 1.1, o: T > t0 ? (1 - u) * 0.55 : 0 });
      });
      const hp = P(T, lockT + 0.1, 1.2, Ease.outCubic);
      S(a.halo, { s: 0.6 + hp * 0.8 + 0.05 * Math.sin(T * 3), o: hp * 0.9 });
      a.rows.forEach((r) => {
        const p = spring(T - r._t0, 190, 17);
        S(r._c, { s: Math.max(0.0001, p), r: (1 - p) * -30 });
        const tp = P(T, r._t0 + 0.08, 0.6, Ease.outQuint);
        S(r._tx, { x: (1 - tp) * 60, o: tp });
        r._stars.forEach((s, k) => { s.style.opacity = T > r._t0 + 0.1 + k * 0.04 ? '1' : '0'; });
      });
    },
  });
})();

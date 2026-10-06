// V3 : fonds « paysage » façon film de référence, séquence plateforme en plans courts (couches éclatées, modèles en carrousel).
(function () {
  const { h, S, P, Ease, lerp, clamp, spring, icon, seeded } = E;
  const el = C.el;

  // Fond paysage avec mouvement lent de caméra (Ken Burns) et particules.
  U.scenery = function (root, name, { drift = 1, parts = true, from = 1.0, to = 1.08 } = {}) {
    const im = h('img', 'abs', root);
    im.src = `../assets/img/${name.includes('/') ? name : 'b46/' + name}.jpg`;
    Object.assign(im.style, { left: '-192px', top: '-108px', width: '2304px', height: '1296px', transformOrigin: '50% 50%' });
    const pa = parts ? U.particles(root, 'rgba(255,255,255,0.7)', 26, name.length) : null;
    let t0 = null;
    return (T) => {
      if (t0 === null) t0 = T;
      const u = T - t0;
      S(im, { s: lerp(from, to, clamp(u / 4)), x: Math.sin(u * 0.25) * 40 * drift - u * 14 * drift, y: Math.cos(u * 0.2) * 14 });
      if (pa) pa(T);
    };
  };

  // ---------- Plateforme complète : couches éclatées ----------
  {
    const a = {};
    SCENES.push({
      id: 'platform',
      build(root) {
        a.root = root;
        a.bg = U.scenery(root, 'b47/earth_night', { drift: 0.4, from: 1.05, to: 1.15 });
        a.cam = el(root, { inset: '0', perspective: '2400px', perspectiveOrigin: '50% 40%' });
        a.rig = el(a.cam, { left: '560px', top: '250px', width: '800px', height: '520px', transformStyle: 'preserve-3d' });
        const L = [
          ['users', 'Access & roles', '#6D5DF6', (c) => { ['Admin', 'Manager', 'Consultant', 'Client'].forEach((r, i) => el(c, { left: 40 + (i % 2) * 360 + 'px', top: 100 + Math.floor(i / 2) * 150 + 'px', width: '330px', height: '120px', borderRadius: '18px', background: '#F4F2FF', display: 'flex', alignItems: 'center', gap: '16px', padding: '0 22px', fontSize: '24px', fontWeight: '700', color: '#3B2F9E' }, `${icon(['shield-check', 'user-check', 'briefcase', 'user'][i], 30, 2.2, '#6D5DF6')}${r}`)); }],
          ['layout-dashboard', 'Screens', '#2E8BC0', (c) => { [0, 1, 2].forEach((i) => U.kpi(c, 40 + i * 245, 96, 225, ['Clients', 'Approvals', 'Avg. time'][i], ['24', '5', '6.5 d'][i], ['+3', '−2', '−1.2 d'][i], ['#2E8BC0', '#F59E0B', '#0EA5A4'][i], ['users', 'clipboard-check', 'timer'][i])); const b = U.bars(c, 40, 250, 720, 220, [0.4, 0.55, 0.5, 0.7, 0.62, 0.85, 0.78, 0.95, 0.88, 1.0]); c._b = b; }],
          ['workflow', 'Workflows', '#0EA5A4', (c) => { ['Request', 'Review', 'Approve', 'Notify'].forEach((t, i) => { el(c, { left: 40 + i * 190 + 'px', top: '200px', width: '150px', height: '120px', borderRadius: '20px', background: i === 2 ? 'linear-gradient(135deg,#0EA5A4,#2E8BC0)' : '#E6F6F5', color: i === 2 ? '#fff' : '#0B5F5C', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '10px', fontSize: '20px', fontWeight: '700' }, `${icon(['file-plus', 'eye', 'check-check', 'bell'][i], 32, 2.2, i === 2 ? '#fff' : '#0EA5A4')}${t}`); if (i < 3) el(c, { left: 190 + i * 190 + 'px', top: '256px', width: '40px', height: '6px', borderRadius: '3px', background: '#0EA5A4' }); }); }],
          ['server', 'Data', '#3B6FD8', (c) => { ['client_id', 'company', 'status', 'documents', 'owner'].forEach((f, i) => { el(c, { left: '40px', top: 96 + i * 76 + 'px', width: '720px', height: '60px', borderRadius: '14px', background: i % 2 ? '#F1F5FB' : '#E8F0FB', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 24px', fontFamily: 'JetBrains Mono', fontSize: '22px', color: '#1E3A8A' }, `<span>${f}</span><span style="color:#64748B">${['uuid', 'text', 'enum', 'file[]', 'user'][i]}</span>`); }); }],
        ];
        a.layers = L.map(([ic, name, col, fill], i) => {
          const c = el(a.rig, { left: '0', top: '0', width: '800px', height: '520px', borderRadius: '30px', background: 'rgba(255,255,255,0.94)', boxShadow: '0 40px 90px rgba(8,6,40,0.45)', border: `2px solid ${col}55` });
          c.className = 'abs ui';
          el(c, { left: '40px', top: '32px', display: 'flex', alignItems: 'center', gap: '14px', fontSize: '28px', fontWeight: '700', color: '#0B2545' }, `<span style="width:46px;height:46px;border-radius:14px;background:${col};display:flex;align-items:center;justify-content:center">${icon(ic === 'workflow' ? 'layers' : ic, 26, 2.2, '#fff')}</span>${name}`);
          fill(c);
          const lab = el(root, { left: '0', top: '0', fontFamily: 'Outfit', fontSize: '40px', fontWeight: '600', color: '#fff', whiteSpace: 'nowrap', display: 'flex', alignItems: 'center', gap: '14px' }, `<span style="width:14px;height:14px;border-radius:4px;background:${col};transform:rotate(45deg)"></span>${name}`);
          return { c, lab, i };
        });
      },
      update(T) {
        a.root.style.opacity = String(P(T, 14.5, 0.2, Ease.inOutSine));
        a.bg(T);
        const u = T - 14.5;
        const ex = Ease.outQuint(clamp((u - 0.15) / 1.2));
        const orbit = lerp(-28, -14, Ease.inOutSine(clamp(u / 2.4)));
        a.rig.style.transform = `translateY(${lerp(80, 30, ex).toFixed(1)}px) rotateX(${lerp(20, 56, Ease.inOutCubic(clamp(u / 1.2))).toFixed(2)}deg) rotateZ(${orbit.toFixed(2)}deg) scale(${lerp(1.25, 0.95, Ease.outCubic(clamp(u / 1.4))).toFixed(4)})`;
        a.layers.forEach((L) => {
          const z = (L.i - 1.5) * lerp(6, 170, ex);
          L.c.style.transform = `translateZ(${z.toFixed(1)}px)`;
          const lp = P(T, 15.0 + (3 - L.i) * 0.28, 0.45, Ease.outQuint);
          // Étiquette à droite, alignée sur la couche.
          const ly = 540 - (L.i - 1.5) * 150 * ex;
          S(L.lab, { x: 1450 + (1 - lp) * 60, y: ly - 26, o: lp });
        });
        const out = P(T, 16.7, 0.25, Ease.inCubic);
        a.cam.style.filter = out > 0 ? `blur(${(out * 20).toFixed(1)}px)` : 'none';
      },
    });
  }

  // ---------- Modèles en carrousel ----------
  {
    const a = {};
    const TPL = [['wallet', 'Expense approvals', 'Finance', '#2E8BC0'], ['chart-column', 'Sales pipeline', 'Sales', '#6D5DF6'], ['clipboard-check', 'Supplier audits', 'Quality', '#0EA5A4'], ['users', 'HR onboarding', 'People', '#A855F7'], ['folder-kanban', 'Project tracker', 'Operations', '#3B6FD8'], ['briefcase', 'Client portal', 'Consulting', '#16A34A'], ['landmark', 'Budget planning', 'Finance', '#2E8BC0'], ['file-pen-line', 'Contract review', 'Legal', '#6D5DF6']];
    SCENES.push({
      id: 'templates',
      build(root) {
        a.root = root;
        a.bg = U.scenery(root, 'b47/star_peaks', { drift: 0.6 });
        a.cam = el(root, { inset: '0', perspective: '1800px' });
        a.cards = TPL.map(([ic, t, d, col], i) => {
          const c = el(a.cam, { left: '760px', top: '300px', width: '400px', height: '480px', borderRadius: '30px', background: 'rgba(255,255,255,0.92)', boxShadow: '0 40px 100px rgba(20,16,70,0.35)', overflow: 'hidden' });
          c.className = 'abs ui';
          el(c, { left: '0', right: '0', top: '0', height: '210px', background: `linear-gradient(135deg, ${col}22, ${col}55)` });
          const b = U.bars(c, 40, 60, 320, 120, [0.4, 0.6, 0.5, 0.75, 0.65, 0.9], col);
          b._b.forEach((x) => (x.style.transform = 'none'));
          el(c, { left: '32px', top: '236px', width: '56px', height: '56px', borderRadius: '16px', background: col, display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 30, 2.2, '#fff'));
          el(c, { left: '32px', top: '312px', fontSize: '30px', fontWeight: '700', color: '#0B2545' }, t);
          el(c, { left: '32px', top: '356px', fontSize: '20px', color: '#64748B' }, `${d} · template`);
          el(c, { left: '32px', top: '406px', display: 'flex', gap: '8px' }).append(U.badge(h('div'), 'Ready to use', 'ok'));
          return c;
        });
        a.title = el(root, { left: '0', width: '1920px', top: '130px', textAlign: 'center', fontFamily: 'Outfit', fontSize: '56px', fontWeight: '600', color: '#fff', textShadow: '0 6px 30px rgba(20,16,70,0.35)' }, 'Start from an idea, or a template.');
      },
      update(T) {
        a.root.style.opacity = String(P(T, 16.8, 0.2, Ease.inOutSine));
        a.bg(T);
        // Défilement rapide qui ralentit.
        const pos = lerp(-2.5, 3.2, Ease.outCubic(clamp((T - 16.8) / 1.6)));
        a.cards.forEach((c, i) => {
          const d = i - pos;
          const x = d * 440, ry = -d * 22, z = -Math.abs(d) * 160;
          S(c, { x, z, ry, o: clamp(1.6 - Math.abs(d) * 0.35) });
          c.style.zIndex = String(100 - Math.round(Math.abs(d) * 10));
        });
        const tp = P(T, 16.95, 0.5, Ease.outQuint);
        S(a.title, { o: tp, y: (1 - tp) * 20 });
        const out = P(T, 18.2, 0.25, Ease.inCubic);
        a.cam.style.filter = out > 0 ? `blur(${(out * 20).toFixed(1)}px)` : 'none';
        a.title.style.opacity = String(tp * (1 - out));
      },
    });
  }
})();

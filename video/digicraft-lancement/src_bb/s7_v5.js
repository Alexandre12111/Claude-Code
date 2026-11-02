// V5 : modèles du carrousel avec de vrais dashboards métier, défilement plus posé.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon } = E;
  const el = C.el;
  const row = (p, y, html, st = {}) => el(p, { left: '20px', right: '20px', top: y + 'px', height: '40px', borderRadius: '10px', background: '#F6F4EE', display: 'flex', alignItems: 'center', gap: '10px', padding: '0 12px', fontSize: '14px', color: '#16161C', fontWeight: '600', ...st }, html);
  const pill = (t, bg, fg) => `<span style="margin-left:auto;height:22px;padding:0 9px;border-radius:11px;background:${bg};color:${fg};font-size:11px;font-weight:700;display:flex;align-items:center">${t}</span>`;
  const av = (ini, c) => `<span style="width:26px;height:26px;border-radius:13px;background:${c};color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex:none">${ini}</span>`;
  const bar = (v, c, w = 90) => `<span style="width:${w}px;height:8px;border-radius:4px;background:#E6E1D6;overflow:hidden;flex:none"><span style="display:block;width:${v * 100}%;height:100%;background:${c};border-radius:4px"></span></span>`;
  const kpi = (p, x, lab, val, col) => el(p, { left: x + 'px', top: '14px', fontSize: '12px', color: '#77746E', fontWeight: '600' }, `${lab}<div style="font-size:24px;font-weight:700;color:${col};margin-top:2px">${val}</div>`);
  const OK = ['#DCFCE7', '#15803D'], WARN = ['#FEF3C7', '#B45309'], INFO = ['#E8ECFF', '#1E3BFF'], VIO = ['#E8ECFF', '#1E3BFF'];

  const DASH = {
    expense(p) {
      kpi(p, 20, 'Pending approvals', '12', '#0B0B0F'); kpi(p, 210, 'Approved this month', '€48,320', '#16A34A');
      [['JL', '#1E3BFF', 'Client dinner', '€420', OK, 'Approved'], ['SM', '#1E3BFF', 'Train Paris–Lyon', '€186', WARN, 'Review'], ['TB', '#1E3BFF', 'Software licence', '€1,240', INFO, 'Finance']]
        .forEach(([i, c, t, v, k, s], j) => row(p, 92 + j * 50, `${av(i, c)}<span>${t}</span><span style="margin-left:auto;font-weight:700">${v}</span>${pill(s, k[0], k[1]).replace('margin-left:auto;', '')}`));
    },
    sales(p) {
      kpi(p, 20, 'Open pipeline', '€2.4M', '#0B0B0F'); kpi(p, 230, 'Win rate', '38%', '#1E3BFF');
      [['Lead', [['Atlas Retail', '€42k'], ['Vertex', '€18k']]], ['Proposal', [['Helio Pharma', '€96k'], ['Orion Bank', '€120k']]], ['Won', [['Bluebay', '€75k']]]].forEach(([c, deals], j) => {
        const col = el(p, { left: 20 + j * 140 + 'px', top: '86px', width: '130px', height: '196px', borderRadius: '12px', background: '#F1F3FF' });
        el(col, { left: '10px', top: '8px', fontSize: '12px', fontWeight: '700', color: '#1E3BFF' }, c);
        deals.forEach(([n, v], q) => el(col, { left: '8px', right: '8px', top: 32 + q * 62 + 'px', height: '54px', borderRadius: '9px', background: '#fff', padding: '7px 9px', fontSize: '12px', fontWeight: '600', color: '#16161C' }, `${n}<div style="font-size:15px;font-weight:700;color:#0B0B0F;margin-top:4px">${v}</div>`));
      });
    },
    suppliers(p) {
      kpi(p, 20, 'Suppliers audited', '146', '#0B0B0F'); kpi(p, 220, 'Avg. score', '8.4 / 10', '#1E3BFF');
      [['Nordic Steel', 0.92, OK, 'Compliant'], ['Alpha Plastics', 0.71, WARN, 'Action plan'], ['Rhine Logistics', 0.86, OK, 'Compliant'], ['Kappa Chemicals', 0.54, WARN, 'Re-audit']]
        .forEach(([n, v, k, s], j) => row(p, 88 + j * 46, `<span style="width:120px">${n}</span>${bar(v, v > 0.8 ? '#1E3BFF' : '#F59E0B', 100)}${pill(s, k[0], k[1])}`, { height: '38px' }));
    },
    hr(p) {
      kpi(p, 20, 'New joiners', '18', '#0B0B0F'); kpi(p, 170, 'Ready day one', '94%', '#4F6BFF'); kpi(p, 330, 'Open roles', '7', '#1E3BFF');
      [['SM', '#1E3BFF', 'Sarah Martin', 0.92], ['TB', '#1E3BFF', 'Tom Becker', 0.66], ['ID', '#1E3BFF', 'Inès Duval', 0.48], ['MP', '#F59E0B', 'Marc Petit', 0.3]]
        .forEach(([i, c, n, v], j) => row(p, 88 + j * 46, `${av(i, c)}<span style="width:110px">${n}</span>${bar(v, c, 120)}<span style="margin-left:auto;font-size:12px;color:#77746E">${Math.round(v * 100)}%</span>`, { height: '38px' }));
    },
    projects(p) {
      kpi(p, 20, 'Active projects', '57', '#0B0B0F'); kpi(p, 220, 'On schedule', '86%', '#4F6BFF');
      [['ERP rollout', 0.05, 0.55, '#4F6BFF'], ['New plant', 0.25, 0.65, '#1E3BFF'], ['CRM migration', 0.45, 0.5, '#1E3BFF'], ['ESG report', 0.6, 0.35, '#F59E0B']].forEach(([n, x, w, c], j) => {
        el(p, { left: '20px', top: 96 + j * 44 + 'px', fontSize: '13px', fontWeight: '600', color: '#3A3833' }, n);
        el(p, { left: 140 + x * 280 + 'px', top: 94 + j * 44 + 'px', width: w * 280 + 'px', height: '22px', borderRadius: '6px', background: c });
      });
      el(p, { left: 140 + 0.52 * 280 + 'px', top: '86px', width: '2px', height: '190px', background: '#EF4444' });
    },
    clients(p) {
      kpi(p, 20, 'Active clients', '24', '#0B0B0F'); kpi(p, 200, 'In onboarding', '9', '#16A34A');
      [['Northwind', INFO, 'Onboarding'], ['Bluebay', OK, 'Approved'], ['Atlas Retail', WARN, 'Docs'], ['Helio Pharma', INFO, 'Onboarding']].forEach(([n, k, s], j) => {
        const c = el(p, { left: 20 + (j % 2) * 212 + 'px', top: 88 + Math.floor(j / 2) * 98 + 'px', width: '200px', height: '86px', borderRadius: '12px', background: '#F6F4EE', padding: '12px', fontSize: '14px', fontWeight: '700', color: '#0B0B0F' }, `${n}<div style="margin-top:10px;display:flex">${pill(s, k[0], k[1]).replace('margin-left:auto;', '')}</div>`);
      });
    },
    budget(p) {
      kpi(p, 20, 'Annual budget', '€4.8M', '#0B0B0F'); kpi(p, 230, 'Spent', '62%', '#1E3BFF');
      el(p, { left: '24px', top: '92px', width: '170px', height: '170px' }, `<svg width="170" height="170" viewBox="0 0 170 170"><circle cx="85" cy="85" r="64" fill="none" stroke="#E6E1D6" stroke-width="22"/><circle cx="85" cy="85" r="64" fill="none" stroke="#1E3BFF" stroke-width="22" stroke-dasharray="402" stroke-dashoffset="153" transform="rotate(-90 85 85)"/><circle cx="85" cy="85" r="64" fill="none" stroke="#1E3BFF" stroke-width="22" stroke-dasharray="402" stroke-dashoffset="330" transform="rotate(134 85 85)"/></svg>`);
      [['People', '€2.1M', '#1E3BFF'], ['Tools', '€0.7M', '#1E3BFF'], ['Travel', '€0.2M', '#1E3BFF']].forEach(([l, v, c], j) => el(p, { left: '220px', top: 110 + j * 46 + 'px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '14px', fontWeight: '600', color: '#3A3833' }, `<span style="width:12px;height:12px;border-radius:4px;background:${c}"></span>${l}<b style="margin-left:14px;color:#0B0B0F">${v}</b>`));
    },
    contracts(p) {
      kpi(p, 20, 'Contracts in review', '23', '#0B0B0F'); kpi(p, 230, 'Avg. cycle', '4.2 days', '#1E3BFF');
      [['Framework · Orion Bank', OK, 'Signed'], ['NDA · Helio Pharma', INFO, 'In review'], ['SLA · Vertex', WARN, 'Redlines'], ['MSA · Atlas Retail', VIO, 'Draft']]
        .forEach(([n, k, s], j) => row(p, 88 + j * 46, `${icon('file-pen-line', 16, 2, '#1E3BFF')}<span>${n}</span>${pill(s, k[0], k[1])}`, { height: '38px' }));
    },
  };
  const TPL = [['wallet', 'Expense approvals', 'Finance', '#1E3BFF', 'expense'], ['chart-column', 'Sales pipeline', 'Sales', '#1E3BFF', 'sales'], ['clipboard-check', 'Supplier audits', 'Quality', '#1E3BFF', 'suppliers'], ['users', 'HR onboarding', 'People', '#4F6BFF', 'hr'],
    ['folder-kanban', 'Project tracker', 'Operations', '#4F6BFF', 'projects'], ['briefcase', 'Client portal', 'Consulting', '#16A34A', 'clients'], ['landmark', 'Budget planning', 'Finance', '#1E3BFF', 'budget'], ['file-pen-line', 'Contract review', 'Legal', '#1E3BFF', 'contracts']];

  const a = {};
  replaceScene({
    id: 'templates',
    build(root) {
      a.root = root;
      a.bg = U.scenery(root, 'b49/gradient_deep', { drift: 0.4, parts: false, tint: 'rgba(11,11,15,0.22)' });
      a.cam = el(root, { inset: '0', perspective: '2000px' });
      a.cards = TPL.map(([ic, t, d, col, k]) => {
        const c = el(a.cam, { left: '720px', top: '250px', width: '480px', height: '560px', borderRadius: '30px', background: '#fff', boxShadow: '0 40px 100px rgba(11,11,15,0.45)', overflow: 'hidden' });
        c.className = 'abs ui';
        const pv = el(c, { left: '0', right: '0', top: '0', height: '300px', background: '#fff', borderBottom: '1px solid #EEEBE4' });
        DASH[k](pv);
        el(c, { left: '0', right: '0', top: '300px', bottom: '0', background: `linear-gradient(160deg, ${col}14, ${col}2E)` });
        el(c, { left: '30px', top: '330px', width: '56px', height: '56px', borderRadius: '16px', background: col, display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 30, 2.2, '#fff'));
        el(c, { left: '30px', top: '404px', fontSize: '30px', fontWeight: '700', color: '#0B0B0F' }, t);
        el(c, { left: '30px', top: '448px', fontSize: '19px', color: '#77746E' }, `${d} · template`);
        el(c, { left: '30px', top: '494px', display: 'flex', gap: '8px' }).append(U.badge(h('div'), 'Ready to use', 'ok'));
        return c;
      });
      a.title = el(root, { left: '0', width: '1920px', top: '120px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontSize: '56px', fontWeight: '600', color: '#fff', textShadow: '0 6px 30px rgba(11,11,15,0.5)' }, 'Start from an idea, or a template.');
    },
    update(T) {
      a.root.style.opacity = String(P(T, 16.8, 0.3, Ease.inOutSine));
      a.bg(T);
      // Défilement posé et régulier.
      const pos = lerp(-0.6, 2.4, Ease.inOutSine(clamp((T - 16.8) / 1.65)));
      a.cards.forEach((c, i) => {
        const d = i - pos;
        S(c, { x: d * 520, z: -Math.abs(d) * 140, ry: -d * 16, o: clamp(1.7 - Math.abs(d) * 0.4) });
        c.style.zIndex = String(100 - Math.round(Math.abs(d) * 10));
      });
      const tp = P(T, 16.95, 0.5, Ease.outQuint), out = P(T, 18.15, 0.3, Ease.inOutSine);
      S(a.title, { o: tp * (1 - out), y: (1 - tp) * 20 });
      a.cam.style.opacity = String(1 - out * 0.6);
    },
  });
})();

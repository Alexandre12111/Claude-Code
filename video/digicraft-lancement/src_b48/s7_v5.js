// V5 : modèles du carrousel avec de vrais dashboards métier, défilement plus posé.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon } = E;
  const el = C.el;
  const row = (p, y, html, st = {}) => el(p, { left: '20px', right: '20px', top: y + 'px', height: '40px', borderRadius: '10px', background: '#F6F8FB', display: 'flex', alignItems: 'center', gap: '10px', padding: '0 12px', fontSize: '14px', color: '#1E293B', fontWeight: '600', ...st }, html);
  const pill = (t, bg, fg) => `<span style="margin-left:auto;height:22px;padding:0 9px;border-radius:11px;background:${bg};color:${fg};font-size:11px;font-weight:700;display:flex;align-items:center">${t}</span>`;
  const av = (ini, c) => `<span style="width:26px;height:26px;border-radius:13px;background:${c};color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex:none">${ini}</span>`;
  const bar = (v, c, w = 90) => `<span style="width:${w}px;height:8px;border-radius:4px;background:#E2E8F0;overflow:hidden;flex:none"><span style="display:block;width:${v * 100}%;height:100%;background:${c};border-radius:4px"></span></span>`;
  const kpi = (p, x, lab, val, col) => el(p, { left: x + 'px', top: '14px', fontSize: '12px', color: '#64748B', fontWeight: '600' }, `${lab}<div style="font-size:24px;font-weight:700;color:${col};margin-top:2px">${val}</div>`);
  const OK = ['#DCFCE7', '#15803D'], WARN = ['#FEF3C7', '#B45309'], INFO = ['#E0EEFA', '#1D6FA3'], VIO = ['#EDE9FF', '#6D5DF6'];

  const DASH = {
    expense(p) {
      kpi(p, 20, 'Pending approvals', '12', '#0B2545'); kpi(p, 210, 'Approved this month', '€48,320', '#16A34A');
      [['JL', '#6D5DF6', 'Client dinner', '€420', OK, 'Approved'], ['SM', '#2E8BC0', 'Train Paris–Lyon', '€186', WARN, 'Review'], ['TB', '#0EA5A4', 'Software licence', '€1,240', INFO, 'Finance']]
        .forEach(([i, c, t, v, k, s], j) => row(p, 92 + j * 50, `${av(i, c)}<span>${t}</span><span style="margin-left:auto;font-weight:700">${v}</span>${pill(s, k[0], k[1]).replace('margin-left:auto;', '')}`));
    },
    sales(p) {
      kpi(p, 20, 'Open pipeline', '€2.4M', '#0B2545'); kpi(p, 230, 'Win rate', '38%', '#6D5DF6');
      [['Lead', [['Atlas Retail', '€42k'], ['Vertex', '€18k']]], ['Proposal', [['Helio Pharma', '€96k'], ['Orion Bank', '€120k']]], ['Won', [['Bluebay', '€75k']]]].forEach(([c, deals], j) => {
        const col = el(p, { left: 20 + j * 140 + 'px', top: '86px', width: '130px', height: '196px', borderRadius: '12px', background: '#F4F2FF' });
        el(col, { left: '10px', top: '8px', fontSize: '12px', fontWeight: '700', color: '#6D5DF6' }, c);
        deals.forEach(([n, v], q) => el(col, { left: '8px', right: '8px', top: 32 + q * 62 + 'px', height: '54px', borderRadius: '9px', background: '#fff', padding: '7px 9px', fontSize: '12px', fontWeight: '600', color: '#1E293B' }, `${n}<div style="font-size:15px;font-weight:700;color:#0B2545;margin-top:4px">${v}</div>`));
      });
    },
    suppliers(p) {
      kpi(p, 20, 'Suppliers audited', '146', '#0B2545'); kpi(p, 220, 'Avg. score', '8.4 / 10', '#0EA5A4');
      [['Nordic Steel', 0.92, OK, 'Compliant'], ['Alpha Plastics', 0.71, WARN, 'Action plan'], ['Rhine Logistics', 0.86, OK, 'Compliant'], ['Kappa Chemicals', 0.54, WARN, 'Re-audit']]
        .forEach(([n, v, k, s], j) => row(p, 88 + j * 46, `<span style="width:120px">${n}</span>${bar(v, v > 0.8 ? '#0EA5A4' : '#F59E0B', 100)}${pill(s, k[0], k[1])}`, { height: '38px' }));
    },
    hr(p) {
      kpi(p, 20, 'New joiners', '18', '#0B2545'); kpi(p, 170, 'Ready day one', '94%', '#A855F7'); kpi(p, 330, 'Open roles', '7', '#2E8BC0');
      [['SM', '#2E8BC0', 'Sarah Martin', 0.92], ['TB', '#6D5DF6', 'Tom Becker', 0.66], ['ID', '#0EA5A4', 'Inès Duval', 0.48], ['MP', '#F59E0B', 'Marc Petit', 0.3]]
        .forEach(([i, c, n, v], j) => row(p, 88 + j * 46, `${av(i, c)}<span style="width:110px">${n}</span>${bar(v, c, 120)}<span style="margin-left:auto;font-size:12px;color:#64748B">${Math.round(v * 100)}%</span>`, { height: '38px' }));
    },
    projects(p) {
      kpi(p, 20, 'Active projects', '57', '#0B2545'); kpi(p, 220, 'On schedule', '86%', '#3B6FD8');
      [['ERP rollout', 0.05, 0.55, '#3B6FD8'], ['New plant', 0.25, 0.65, '#0EA5A4'], ['CRM migration', 0.45, 0.5, '#6D5DF6'], ['ESG report', 0.6, 0.35, '#F59E0B']].forEach(([n, x, w, c], j) => {
        el(p, { left: '20px', top: 96 + j * 44 + 'px', fontSize: '13px', fontWeight: '600', color: '#334155' }, n);
        el(p, { left: 140 + x * 280 + 'px', top: 94 + j * 44 + 'px', width: w * 280 + 'px', height: '22px', borderRadius: '6px', background: c });
      });
      el(p, { left: 140 + 0.52 * 280 + 'px', top: '86px', width: '2px', height: '190px', background: '#EF4444' });
    },
    clients(p) {
      kpi(p, 20, 'Active clients', '24', '#0B2545'); kpi(p, 200, 'In onboarding', '9', '#16A34A');
      [['Northwind', INFO, 'Onboarding'], ['Bluebay', OK, 'Approved'], ['Atlas Retail', WARN, 'Docs'], ['Helio Pharma', INFO, 'Onboarding']].forEach(([n, k, s], j) => {
        const c = el(p, { left: 20 + (j % 2) * 212 + 'px', top: 88 + Math.floor(j / 2) * 98 + 'px', width: '200px', height: '86px', borderRadius: '12px', background: '#F6F8FB', padding: '12px', fontSize: '14px', fontWeight: '700', color: '#0B2545' }, `${n}<div style="margin-top:10px;display:flex">${pill(s, k[0], k[1]).replace('margin-left:auto;', '')}</div>`);
      });
    },
    budget(p) {
      kpi(p, 20, 'Annual budget', '€4.8M', '#0B2545'); kpi(p, 230, 'Spent', '62%', '#2E8BC0');
      el(p, { left: '24px', top: '92px', width: '170px', height: '170px' }, `<svg width="170" height="170" viewBox="0 0 170 170"><circle cx="85" cy="85" r="64" fill="none" stroke="#E2E8F0" stroke-width="22"/><circle cx="85" cy="85" r="64" fill="none" stroke="#2E8BC0" stroke-width="22" stroke-dasharray="402" stroke-dashoffset="153" transform="rotate(-90 85 85)"/><circle cx="85" cy="85" r="64" fill="none" stroke="#6D5DF6" stroke-width="22" stroke-dasharray="402" stroke-dashoffset="330" transform="rotate(134 85 85)"/></svg>`);
      [['People', '€2.1M', '#2E8BC0'], ['Tools', '€0.7M', '#6D5DF6'], ['Travel', '€0.2M', '#0EA5A4']].forEach(([l, v, c], j) => el(p, { left: '220px', top: 110 + j * 46 + 'px', display: 'flex', alignItems: 'center', gap: '10px', fontSize: '14px', fontWeight: '600', color: '#334155' }, `<span style="width:12px;height:12px;border-radius:4px;background:${c}"></span>${l}<b style="margin-left:14px;color:#0B2545">${v}</b>`));
    },
    contracts(p) {
      kpi(p, 20, 'Contracts in review', '23', '#0B2545'); kpi(p, 230, 'Avg. cycle', '4.2 days', '#6D5DF6');
      [['Framework · Orion Bank', OK, 'Signed'], ['NDA · Helio Pharma', INFO, 'In review'], ['SLA · Vertex', WARN, 'Redlines'], ['MSA · Atlas Retail', VIO, 'Draft']]
        .forEach(([n, k, s], j) => row(p, 88 + j * 46, `${icon('file-pen-line', 16, 2, '#6D5DF6')}<span>${n}</span>${pill(s, k[0], k[1])}`, { height: '38px' }));
    },
  };
  const TPL = [['wallet', 'Expense approvals', 'Finance', '#2E8BC0', 'expense'], ['chart-column', 'Sales pipeline', 'Sales', '#6D5DF6', 'sales'], ['clipboard-check', 'Supplier audits', 'Quality', '#0EA5A4', 'suppliers'], ['users', 'HR onboarding', 'People', '#A855F7', 'hr'],
    ['folder-kanban', 'Project tracker', 'Operations', '#3B6FD8', 'projects'], ['briefcase', 'Client portal', 'Consulting', '#16A34A', 'clients'], ['landmark', 'Budget planning', 'Finance', '#2E8BC0', 'budget'], ['file-pen-line', 'Contract review', 'Legal', '#6D5DF6', 'contracts']];

  const a = {};
  replaceScene({
    id: 'templates',
    build(root) {
      a.root = root;
      a.bg = U.scenery(root, 'b48/aurora', { drift: 0.4 });
      a.cam = el(root, { inset: '0', perspective: '2000px' });
      a.cards = TPL.map(([ic, t, d, col, k]) => {
        const c = el(a.cam, { left: '720px', top: '250px', width: '480px', height: '560px', borderRadius: '30px', background: '#fff', boxShadow: '0 40px 100px rgba(10,8,50,0.45)', overflow: 'hidden' });
        c.className = 'abs ui';
        const pv = el(c, { left: '0', right: '0', top: '0', height: '300px', background: '#fff', borderBottom: '1px solid #EEF2F6' });
        DASH[k](pv);
        el(c, { left: '0', right: '0', top: '300px', bottom: '0', background: `linear-gradient(160deg, ${col}14, ${col}2E)` });
        el(c, { left: '30px', top: '330px', width: '56px', height: '56px', borderRadius: '16px', background: col, display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 30, 2.2, '#fff'));
        el(c, { left: '30px', top: '404px', fontSize: '30px', fontWeight: '700', color: '#0B2545' }, t);
        el(c, { left: '30px', top: '448px', fontSize: '19px', color: '#64748B' }, `${d} · template`);
        el(c, { left: '30px', top: '494px', display: 'flex', gap: '8px' }).append(U.badge(h('div'), 'Ready to use', 'ok'));
        return c;
      });
      a.title = el(root, { left: '0', width: '1920px', top: '120px', textAlign: 'center', fontFamily: 'Outfit', fontSize: '56px', fontWeight: '600', color: '#fff', textShadow: '0 6px 30px rgba(10,8,50,0.5)' }, 'Start from an idea, or a template.');
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

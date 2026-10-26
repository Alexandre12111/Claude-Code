(function () {
  const { h, S, P, Ease, lerp, clamp, kf, words, revealWords, hideWords, icon, spring, fmt } = E;
  const el = (parent, style, html, tag = 'div') => {
    const e = h(tag, 'abs', parent, html);
    Object.assign(e.style, style);
    return e;
  };
  const px = (o) => { const r = {}; for (const k in o) r[k] = typeof o[k] === 'number' && !['opacity', 'zIndex', 'fontWeight', 'lineHeight', 'flex'].includes(k) ? o[k] + 'px' : o[k]; return r; };

  const BW = 1600, BH = 940, CH = 56;
  const PROMPT = "Build a KPI dashboard across all departments: revenue, margin, headcount and cash, with alerts and mobile access.";
  const TYPE0 = 21.95, TYPE1 = 24.75;
  const PHONE = { x: 1390, y: 548 };
  window.G = window.G || {};
  window.G.phone = PHONE;
  let els = {};

  const STEPS = [
    [14.5, 'sparkles', 'Analyzing your request', ''],
    [15.1, 'file-code', 'Created', 'prisma/schema.prisma'],
    [15.7, 'file-code', 'Created', 'app/kpi/finance.ts'],
    [16.3, 'file-code', 'Created', 'app/kpi/hr.ts'],
    [16.9, 'file-code', 'Created', 'app/alerts/route.ts'],
    [17.5, 'file-code', 'Created', 'app/dashboard/page.tsx'],
    [18.2, 'square-terminal', 'Ran', 'tests: 14 passed'],
    [19.0, 'circle-check', 'Preview updated', ''],
  ];
  const ROWS = [
    ['FIN', 'Operating cash flow', 'Finance', '+€1.4M', 'ok'],
    ['SAL', 'Southern Europe margin', 'Sales', '−2.1 pts', 'warn'],
    ['HR', 'Q4 hiring', 'HR', '18 / 25 roles', 'warn'],
    ['CEO', 'Customer satisfaction', 'Group', 'NPS 62', 'ok'],
    ['FIN', 'Days to pay', 'Finance', '47 days', 'warn'],
  ];
  const STATUS = { ok: ['On track', 'var(--ok)', 'var(--ok-bg)'], warn: ['Watch', 'var(--warn)', 'var(--warn-bg)'] };
  const KPIS = [
    ['Revenue', 48.2, 'M', 'trending-up', '#16A34A'],
    ['Gross margin', 31.4, '%', 'chart-pie', '#2E8BC0'],
    ['Headcount', 1240, '', 'users', '#1E9BD7'],
    ['Cash', 12.8, 'M', 'wallet', '#7C3AED'],
  ];
  const BARS = [['Apr', 0.52], ['May', 0.6], ['Jun', 0.57], ['Jul', 0.68], ['Aug', 0.63], ['Sep', 0.82]];

  function buildHome(c) {
    const v = el(c, { inset: '0' });
    el(v, px({ left: 0, top: 0, width: BW, height: 76, borderBottom: '1px solid #EEF0F4' }));
    el(v, px({ left: 36, top: 24 }), icon('panels-top-left', 26, 2, '#6B7280'));
    const lg = el(v, px({ left: 84, top: 18, display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'Outfit', fontWeight: 700, fontSize: 30 }), `${window.dcIcon(36, 'h')}<span><span style="color:#012D48">Digi</span><span style="color:#2E8BC0">Craft</span></span>`);
    void lg;
    el(v, px({ right: 110, top: 25 }), icon('sparkles', 26, 2, '#9CA3AF'));
    el(v, px({ right: 40, top: 16, width: 44, height: 44, borderRadius: 22, background: 'linear-gradient(135deg,#6D5DF6,#2E8BC0)', color: '#fff', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 20, display: 'flex', alignItems: 'center', justifyContent: 'center' }), 'L');
    el(v, { position: 'absolute', left: '0', right: '0', top: '-120px', height: '520px', background: 'radial-gradient(600px 260px at 50% 40%, rgba(168,85,247,0.10), rgba(168,85,247,0))' });
    el(v, px({ left: 0, width: BW, top: 150, textAlign: 'center', fontFamily: 'Outfit', fontWeight: 800, fontSize: 76, lineHeight: 1.08, color: '#111827' }),
      `<span style="background:linear-gradient(90deg,#A855F7,#C084FC 60%,#D8B4FE);-webkit-background-clip:text;background-clip:text;color:transparent">What would you like</span><br>to build today?`);
    el(v, px({ left: 0, width: BW, top: 338, textAlign: 'center', fontFamily: 'DM Sans', fontSize: 26, color: '#6B7280' }), 'Describe your idea, DigiCraft brings it to life.');
    const card = el(v, px({ left: 330, top: 408, width: 940, height: 226, borderRadius: 26, background: '#fff', border: '1px solid #ECEAF5', boxShadow: '0 22px 60px rgba(76,29,149,0.10), 0 2px 8px rgba(17,24,39,0.05)' }));
    els.ta = el(card, px({ left: 32, top: 28, width: 870, fontFamily: 'DM Sans', fontSize: 28, lineHeight: 1.42, color: '#1F2937' }));
    els.ph = el(card, px({ left: 32, top: 28, fontFamily: 'DM Sans', fontSize: 28, color: '#9CA3AF' }), 'Describe your project…');
    el(card, px({ left: 30, bottom: 26 }), icon('image', 30, 1.8, '#9CA3AF'));
    els.create = el(card, px({ right: 22, bottom: 20, width: 176, height: 62, borderRadius: 31, background: 'linear-gradient(90deg,#7C3AED,#9333EA)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 24, boxShadow: '0 10px 24px rgba(124,58,237,0.35)' }), `${icon('send', 24, 2.2, '#fff')}Create`);
    els.ripple = el(els.create, px({ left: 88, top: 31, width: 10, height: 10, borderRadius: 5, marginLeft: -5, marginTop: -5, background: 'rgba(255,255,255,0.55)' }));
    const chips = el(v, px({ left: 0, width: BW, top: 676, display: 'flex', justifyContent: 'center', gap: 18 }));
    chips.style.position = 'absolute';
    [['chart-column', 'Dashboard'], ['file-pen-line', 'Form'], ['users', 'HR portal']].forEach(([ic, t]) => {
      const ch = h('div', '', chips, `${icon(ic, 22, 2, '#7C3AED')}${t}`);
      (els.chips = els.chips || []).push(ch);
      Object.assign(ch.style, px({ display: 'flex', alignItems: 'center', gap: 10, padding: '14px 24px', borderRadius: 28, border: '1px solid #E9E5F5', background: '#fff', fontFamily: 'DM Sans', fontSize: 22, color: '#374151' }));
    });
    return v;
  }

  function buildApp(c) {
    const app = el(c, px({ left: 26, top: 78, width: 1078, height: 780, borderRadius: 18, background: '#fff', overflow: 'hidden', boxShadow: '0 10px 40px rgba(2,36,70,0.10)', border: '1px solid #E5EAF0' }));
    const sk = el(app, { inset: '0', background: '#fff' });
    const skel = (x, y, w, hh) => el(sk, px({ left: x, top: y, width: w, height: hh, borderRadius: 10, background: 'linear-gradient(90deg,#EEF2F6,#F6F8FB,#EEF2F6)' }));
    [[0, 0, 210, 780], [238, 28, 300, 34], [238, 90, 190, 110], [441, 90, 190, 110], [644, 90, 190, 110], [847, 90, 190, 110], [238, 226, 390, 300], [644, 226, 406, 520]].forEach((a) => skel(...a));
    els.skel = sk;

    const side = el(app, px({ left: 0, top: 0, width: 210, height: 780, background: '#0B2545' }));
    el(side, px({ left: 22, top: 26, display: 'flex', alignItems: 'center', gap: 10, color: '#fff', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 21 }), `<div style="width:36px;height:36px;border-radius:10px;background:#2E8BC0;display:flex;align-items:center;justify-content:center">${icon('gauge', 20, 2.2, '#fff')}</div>CEO KPIs`);
    const nav = [['layout-dashboard', 'Overview', 1], ['landmark', 'Finance'], ['briefcase', 'Sales'], ['users', 'Human resources'], ['bell', 'Alerts', 0, '3']];
    els.nav = nav.map(([ic, t, act, badge], i) => el(side, px({ left: 12, top: 100 + i * 54, width: 186, height: 44, borderRadius: 10, background: act ? 'rgba(46,139,192,0.45)' : 'transparent', display: 'flex', alignItems: 'center', gap: 12, padding: '0 12px', color: act ? '#fff' : '#A9BCD0', fontFamily: 'DM Sans', fontWeight: 500, fontSize: 17 }),
      `${icon(ic, 19, 2, act ? '#fff' : '#A9BCD0')}<span style="flex:1">${t}</span>${badge ? `<span style="background:#4AAAE2;color:#fff;border-radius:10px;padding:1px 8px;font-size:14px;font-weight:700">${badge}</span>` : ''}`));
    els.side = side;

    const head = el(app, px({ left: 238, top: 24, width: 812, height: 50 }));
    el(head, px({ left: 0, top: 0, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 30, color: '#0B2545' }), 'Group overview');
    el(head, px({ left: 0, top: 40, fontFamily: 'DM Sans', fontSize: 16, color: '#6B7280' }), 'September 2026 · all departments');
    els.exportBtn = el(head, px({ right: 170, top: 4, height: 42, padding: '0 16px', borderRadius: 10, border: '1px solid #D5DEE8', display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'DM Sans', fontWeight: 600, fontSize: 16, color: '#0B2545', background: '#fff' }), `${icon('download', 18, 2, '#0B2545')}Export PDF`);
    els.newBtn = el(head, px({ right: 0, top: 4, height: 42, padding: '0 16px', borderRadius: 10, background: '#012D48', display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'DM Sans', fontWeight: 600, fontSize: 16, color: '#fff' }), `${icon('link', 18, 2.4, '#fff')}Share with the board`);
    els.head = head;

    els.kpis = KPIS.map(([t, v, suf, ic, col], i) => {
      const k = el(app, px({ left: 238 + i * 203, top: 100, width: 190, height: 106, borderRadius: 14, border: '1px solid #E5EAF0', background: '#fff' }));
      el(k, px({ left: 16, top: 14, fontFamily: 'DM Sans', fontSize: 15, color: '#6B7280' }), t);
      const val = el(k, px({ left: 16, top: 42, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 32, color: '#0B2545' }), '0');
      el(k, px({ right: 14, top: 14, width: 38, height: 38, borderRadius: 10, background: col + '1A', display: 'flex', alignItems: 'center', justifyContent: 'center' }), icon(ic, 20, 2.2, col));
      k._val = val; k._v = v; k._suf = suf; k._pre = suf === 'M' ? '€' : '';
      return k;
    });

    const chart = el(app, px({ left: 238, top: 226, width: 390, height: 300, borderRadius: 14, border: '1px solid #E5EAF0', background: '#fff' }));
    el(chart, px({ left: 18, top: 16, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 18, color: '#0B2545' }), 'Monthly revenue');
    els.bars = BARS.map(([lab, v], i) => {
      const b = el(chart, px({ left: 26 + i * 59, bottom: 44, width: 36, height: v * 190, borderRadius: '8px 8px 3px 3px', background: i === 5 ? '#2E8BC0' : '#B3D8ED', transformOrigin: '50% 100%' }));
      el(chart, px({ left: 14 + i * 59, bottom: 16, width: 60, textAlign: 'center', fontFamily: 'DM Sans', fontSize: 12, color: '#6B7280' }), lab);
      return b;
    });
    els.chart = chart;

    const table = el(app, px({ left: 644, top: 226, width: 406, height: 520, borderRadius: 14, border: '1px solid #E5EAF0', background: '#fff' }));
    el(table, px({ left: 18, top: 16, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 18, color: '#0B2545' }), 'Alerts and targets');
    els.rows = ROWS.map(([ini, t, team, amt, st], i) => {
      const r = el(table, px({ left: 12, top: 60 + i * 88, width: 382, height: 76, borderRadius: 12, background: i % 2 ? '#fff' : '#F7F9FC' }));
      el(r, px({ left: 12, top: 18, width: 40, height: 40, borderRadius: 12, background: ['#DCFCE7', '#DBEEF9', '#EDE9FE', '#E0F2FE', '#DCFCE7'][i], color: '#0B2545', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 12, display: 'flex', alignItems: 'center', justifyContent: 'center' }), ini);
      el(r, px({ left: 64, top: 14, fontFamily: 'DM Sans', fontWeight: 600, fontSize: 17, color: '#0B2545' }), t);
      el(r, px({ left: 64, top: 40, fontFamily: 'DM Sans', fontSize: 14, color: '#6B7280' }), `${team} · ${amt}`);
      const [lab, col, bg] = STATUS[st];
      r._badge = el(r, px({ right: 12, top: 24, padding: '4px 12px', borderRadius: 14, background: bg, color: col, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 14 }), lab);
      return r;
    });
    els.table = table;
    return app;
  }

  function buildBuild(c) {
    const v = el(c, { inset: '0', background: '#fff' });
    const top = el(v, px({ left: 0, top: 0, width: BW, height: 70, borderBottom: '1px solid #EEF0F4', background: '#fff' }));
    el(top, px({ left: 26, top: 22 }), icon('arrow-left', 26, 2, '#374151'));
    el(top, px({ left: 70, top: 20, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 24, color: '#111827' }), 'CEO Dashboard');
    el(top, px({ left: 262, top: 23, display: 'flex', gap: 6, alignItems: 'center', fontFamily: 'DM Sans', fontSize: 18, color: '#6B7280' }), `${icon('users', 20, 2, '#6B7280')}1`);
    const tg = el(top, px({ left: 510, top: 13, height: 44, borderRadius: 12, background: '#F3F4F6', display: 'flex', alignItems: 'center', gap: 4, padding: '0 6px' }));
    ['monitor', 'code-xml', 'smartphone'].forEach((ic, i) => {
      const b = h('div', '', tg, icon(ic, 20, 2, i === 0 ? '#111827' : '#9CA3AF'));
      Object.assign(b.style, px({ width: 44, height: 34, borderRadius: 9, background: i === 0 ? '#fff' : 'transparent', display: 'flex', alignItems: 'center', justifyContent: 'center' }));
    });
    el(top, px({ left: 690, top: 13, width: 340, height: 44, borderRadius: 12, border: '1px solid #E5E7EB', display: 'flex', alignItems: 'center', gap: 10, padding: '0 14px', fontFamily: 'DM Sans', fontSize: 18, color: '#6B7280' }), `${icon('house', 18, 2, '#9CA3AF')}/pilotage`);
    els.publish = el(top, px({ right: 22, top: 11, width: 196, height: 48, borderRadius: 12, background: 'linear-gradient(90deg,#6D5DF6,#3B82F6)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 20, overflow: 'hidden', boxShadow: '0 8px 20px rgba(79,70,229,0.30)' }));
    els.pubA = el(els.publish, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }), `${icon('rocket', 22, 2.2, '#fff')}Publish`);
    els.pubB = el(els.publish, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, background: '#16A34A' }), `${icon('circle-check', 22, 2.4, '#fff')}Live`);

    const left = el(v, px({ left: 0, top: 70, width: 470, height: BH - CH - 70, borderRight: '1px solid #EEF0F4', background: '#fff', overflow: 'hidden' }));
    els.bubble = el(left, px({ left: 22, top: 22, width: 426, padding: '16px 18px', borderRadius: 16, background: '#F4F1FF', fontFamily: 'DM Sans', fontSize: 17, lineHeight: 1.45, color: '#312E81' }), PROMPT);
    els.steps = STEPS.map(([t0, ic, verb, code], i) => {
      const r = el(left, px({ left: 22, top: 176 + i * 50, width: 430, height: 40, display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'DM Sans', fontSize: 18, color: '#374151' }),
        `${icon(ic, 20, 2, ic === 'circle-check' ? '#16A34A' : '#6B7280')}<span style="font-weight:600">${verb}</span>${code ? `<span style="font-family:'JetBrains Mono';font-size:14px;background:#F3F4F6;border-radius:6px;padding:3px 8px;color:#4B5563;white-space:nowrap">${code}</span>` : ''}`);
      r._t0 = t0;
      return r;
    });
    els.status = el(left, px({ left: 22, top: 590, width: 426, height: 70, borderRadius: 16, overflow: 'hidden' }));
    els.stA = el(els.status, px({ inset: 0, background: 'linear-gradient(90deg,#EFF7FC,#F6FBFD)', border: '1px solid #D5EAF7', borderRadius: 16, display: 'flex', alignItems: 'center', gap: 14, padding: '0 18px', fontFamily: 'DM Sans', fontSize: 18, color: '#21597A' }),
      `<div style="width:14px;height:14px;border-radius:7px;background:#2E9AD8"></div><div style="flex:1"><div style="font-weight:700">DigiCraft</div><div style="font-size:15px;color:#23709D">Building…</div></div>`);
    els.eq = [0, 1, 2, 3].map((i) => el(els.stA, px({ right: 20 + (3 - i) * 9, bottom: 22, width: 5, height: 24, borderRadius: 3, background: '#2E9AD8', transformOrigin: '50% 100%' })));
    els.stB = el(els.status, px({ inset: 0, background: '#ECFDF3', border: '1px solid #BBF7D0', borderRadius: 16, display: 'flex', alignItems: 'center', gap: 14, padding: '0 18px', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 19, color: '#15803D' }), `${icon('circle-check', 26, 2.4, '#16A34A')}Your app is ready`);
    el(left, px({ left: 22, bottom: 22, width: 426, height: 64, borderRadius: 16, border: '1px solid #E5E7EB', display: 'flex', alignItems: 'center', padding: '0 18px', fontFamily: 'DM Sans', fontSize: 18, color: '#9CA3AF' }), 'Ask DigiCraft…');

    const pv = el(v, px({ left: 470, top: 70, width: BW - 470, height: BH - CH - 70, background: '#F4F6FA', overflow: 'hidden' }));
    els.prog = el(pv, px({ left: 0, top: 0, height: 4, width: BW - 470, background: 'linear-gradient(90deg,#8F8AFF,#2E8BC0)', transformOrigin: '0 50%' }));
    els.pvLabel = el(pv, px({ left: 26, top: 24, display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 600, fontSize: 18, color: '#4B5563' }), `${icon('loader-circle', 20, 2.2, '#7C3AED')}<span>Live preview</span>`);
    els.spin = els.pvLabel.querySelector('svg');
    els.fast = el(pv, px({ right: 26, top: 20, display: 'flex', alignItems: 'center', gap: 8, padding: '6px 14px', borderRadius: 16, background: '#EDE9FE', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 15, color: '#6D28D9' }), `${icon('history', 17, 2.2, '#6D28D9')}Sped up`);
    buildApp(pv);
    els.toast = el(v, px({ left: 470 + (BW - 470) / 2 - 330, top: 96, width: 660, height: 84, borderRadius: 18, background: '#0B2545', color: '#fff', display: 'flex', alignItems: 'center', gap: 16, padding: '0 24px', boxShadow: '0 20px 50px rgba(2,36,70,0.35)', fontFamily: 'DM Sans' }),
      `<div style="width:44px;height:44px;border-radius:22px;background:#16A34A;display:flex;align-items:center;justify-content:center">${icon('check', 26, 3, '#fff')}</div><div><div style="font-weight:700;font-size:21px">Your app is live</div><div style="font-size:16px;color:#A9BCD0">Link shared with your team</div></div>`);
    return v;
  }

  function cursorSvg() {
    return `<svg width="46" height="46" viewBox="0 0 24 24"><path d="M4.5 3.2 L19.5 11.3 L12.6 12.9 L9.6 19.6 Z" fill="#111827" stroke="#fff" stroke-width="1.4" stroke-linejoin="round"/></svg>`;
  }

  function buildPhone(root) {
    const ph = el(root, px({ left: PHONE.x - 205, top: PHONE.y - 420, width: 410, height: 840, borderRadius: 68, background: '#0B1B2E', boxShadow: '0 50px 120px rgba(2,36,70,0.35), inset 0 0 0 2px #2A3B52' }));
    const sc = el(ph, px({ left: 14, top: 14, width: 382, height: 812, borderRadius: 56, background: '#F4F6FA', overflow: 'hidden' }));
    el(sc, px({ left: 141, top: 12, width: 100, height: 30, borderRadius: 15, background: '#0B1B2E' }));
    el(sc, px({ left: 30, top: 16, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 17, color: '#111827' }), '9:41');
    el(sc, px({ left: 0, top: 58, width: 382, height: 86, background: '#0B2545' }));
    el(sc, px({ left: 24, top: 78, display: 'flex', alignItems: 'center', gap: 12, color: '#fff', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 22 }), `<div style="width:40px;height:40px;border-radius:11px;background:#2E8BC0;display:flex;align-items:center;justify-content:center">${icon('gauge', 22, 2.2, '#fff')}</div>CEO Dashboard`);
    el(sc, px({ left: 24, top: 166, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 24, color: '#0B2545' }), 'September report');
    const card = el(sc, px({ left: 18, top: 206, width: 346, height: 390, borderRadius: 22, background: '#fff', boxShadow: '0 8px 24px rgba(2,36,70,0.08)' }));
    const rc = el(card, px({ left: 18, top: 18, width: 310, height: 150, borderRadius: 14, background: '#F0F8FD', overflow: 'hidden' }));
    [0.45, 0.55, 0.5, 0.66, 0.6, 0.8, 0.74, 0.92].forEach((v, i) => el(rc, px({ left: 20 + i * 36, bottom: 16, width: 22, height: v * 110, borderRadius: '6px 6px 2px 2px', background: i === 7 ? '#2E8BC0' : '#B3D8ED' })));
    el(card, px({ left: 20, top: 186, fontFamily: 'DM Sans', fontSize: 15, color: '#6B7280' }), 'Revenue');
    el(card, px({ left: 20, top: 206, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 40, color: '#0B2545' }), '€48.2M');
    el(card, px({ left: 20, top: 272, padding: '6px 14px', borderRadius: 14, background: '#DCFCE7', color: '#15803D', fontFamily: 'DM Sans', fontWeight: 600, fontSize: 15 }), '+6% YoY');
    el(card, px({ left: 150, top: 272, padding: '6px 14px', borderRadius: 14, background: '#F3F4F6', color: '#4B5563', fontFamily: 'DM Sans', fontWeight: 600, fontSize: 15 }), 'Margin 31.4%');
    els.stChip = el(card, px({ left: 20, top: 330, height: 36, padding: '0 14px', borderRadius: 18, display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 15 }));
    els.send = el(sc, px({ left: 18, top: 620, width: 346, height: 66, borderRadius: 18, background: '#012D48', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 20, overflow: 'hidden' }));
    els.sendTxt = el(els.send, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }));
    els.tap = el(els.send, px({ left: 173, top: 33, width: 20, height: 20, marginLeft: -10, marginTop: -10, borderRadius: 10, background: 'rgba(255,255,255,0.45)' }));
    els.notif = el(sc, px({ left: 12, top: 12, width: 358, height: 96, borderRadius: 24, background: 'rgba(17,24,39,0.94)', display: 'flex', alignItems: 'center', gap: 14, padding: '0 16px', fontFamily: 'DM Sans', color: '#fff' }),
      `<div style="width:48px;height:48px;border-radius:13px;background:#fff;display:flex;align-items:center;justify-content:center">${window.dcIcon(34, 'n', 'brand')}</div><div><div style="font-weight:700;font-size:17px">Target met</div><div style="font-size:14px;color:#D1D5DB;line-height:1.35">Operating cash flow: +€1.4M this month.</div></div>`);
    return ph;
  }

  window.UI = { el, px, els, BW, BH, CH, PROMPT, PHONE, buildHome, buildBuild, buildPhone, cursorSvg };
})();

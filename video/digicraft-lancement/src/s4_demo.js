(function () {
  const { h, S, P, Ease, lerp, clamp, kf, words, revealWords, hideWords, icon, spring, fmt } = E;
  const el = (parent, style, html, tag = 'div') => {
    const e = h(tag, 'abs', parent, html);
    Object.assign(e.style, style);
    return e;
  };
  const px = (o) => { const r = {}; for (const k in o) r[k] = typeof o[k] === 'number' && !['opacity', 'zIndex', 'fontWeight', 'lineHeight', 'flex'].includes(k) ? o[k] + 'px' : o[k]; return r; };

  const BW = 1600, BH = 940, CH = 56;
  const PROMPT = "Crée une app de suivi des notes de frais avec validation par le manager, export comptable et tableau de bord.";
  const TYPE0 = 21.95, TYPE1 = 24.75;
  const PHONE = { x: 1390, y: 548 };
  window.G = window.G || {};
  window.G.phone = PHONE;
  let els = {};

  const STEPS = [
    [26.2, 'sparkles', 'Analyse de votre demande', ''],
    [26.75, 'file-code', 'Créé', 'prisma/schema.prisma'],
    [27.3, 'file-code', 'Créé', 'app/notes/nouvelle/page.tsx'],
    [27.85, 'file-code', 'Créé', 'app/validation/route.ts'],
    [28.4, 'file-code', 'Créé', 'app/export/comptable.ts'],
    [28.95, 'file-code', 'Créé', 'app/tableau-de-bord/page.tsx'],
    [29.5, 'square-terminal', 'Exécuté', 'tests : 12 réussis'],
    [30.1, 'circle-check', 'Aperçu mis à jour', ''],
  ];
  const ROWS = [
    ['CM', 'Déjeuner client', 'Conseil', '86,40 €', 'ok'],
    ['JB', 'Billet de train Lyon', 'Commercial', '112,00 €', 'warn'],
    ['SL', 'Salon professionnel', 'Marketing', '320,00 €', 'ok'],
    ['AD', 'Taxi aéroport', 'Finance', '54,90 €', 'warn'],
    ['MR', 'Fournitures bureau', 'RH', '38,20 €', 'ok'],
  ];
  const STATUS = { ok: ['Validée', 'var(--ok)', 'var(--ok-bg)'], warn: ['À valider', 'var(--warn)', 'var(--warn-bg)'] };
  const KPIS = [
    ['À valider', 12, '', 'hourglass', '#D97706'],
    ['Validées ce mois', 148, '', 'circle-check', '#16A34A'],
    ['Montant du mois', 18420, ' €', 'euro', '#2E8BC0'],
    ['Délai moyen', 1.4, ' j', 'timer', '#7C3AED'],
  ];
  const BARS = [['Conseil', 0.82], ['Commercial', 0.64], ['Marketing', 0.5], ['Finance', 0.36], ['RH', 0.28], ['IT', 0.44]];

  function buildHome(c) {
    const v = el(c, { inset: '0' });
    el(v, px({ left: 0, top: 0, width: BW, height: 76, borderBottom: '1px solid #EEF0F4' }));
    el(v, px({ left: 36, top: 24 }), icon('panels-top-left', 26, 2, '#6B7280'));
    const lg = el(v, px({ left: 84, top: 18, display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'Outfit', fontWeight: 700, fontSize: 30 }), `${window.dcIcon(36, 'h')}<span><span style="color:#022446">Digi</span><span style="color:#8F8AFF">Craft</span></span>`);
    void lg;
    el(v, px({ right: 110, top: 25 }), icon('sparkles', 26, 2, '#9CA3AF'));
    el(v, px({ right: 40, top: 16, width: 44, height: 44, borderRadius: 22, background: 'linear-gradient(135deg,#6D5DF6,#2E8BC0)', color: '#fff', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 20, display: 'flex', alignItems: 'center', justifyContent: 'center' }), 'L');
    el(v, { position: 'absolute', left: '0', right: '0', top: '-120px', height: '520px', background: 'radial-gradient(600px 260px at 50% 40%, rgba(168,85,247,0.10), rgba(168,85,247,0))' });
    el(v, px({ left: 0, width: BW, top: 150, textAlign: 'center', fontFamily: 'Outfit', fontWeight: 800, fontSize: 76, lineHeight: 1.08, color: '#111827' }),
      `<span style="background:linear-gradient(90deg,#A855F7,#C084FC 60%,#D8B4FE);-webkit-background-clip:text;background-clip:text;color:transparent">Qu'aimeriez-vous</span><br>construire aujourd'hui ?`);
    el(v, px({ left: 0, width: BW, top: 338, textAlign: 'center', fontFamily: 'DM Sans', fontSize: 26, color: '#6B7280' }), 'Décrivez votre idée, DigiCraft lui donne vie.');
    const card = el(v, px({ left: 330, top: 408, width: 940, height: 226, borderRadius: 26, background: '#fff', border: '1px solid #ECEAF5', boxShadow: '0 22px 60px rgba(76,29,149,0.10), 0 2px 8px rgba(17,24,39,0.05)' }));
    els.ta = el(card, px({ left: 32, top: 28, width: 870, fontFamily: 'DM Sans', fontSize: 28, lineHeight: 1.42, color: '#1F2937' }));
    els.ph = el(card, px({ left: 32, top: 28, fontFamily: 'DM Sans', fontSize: 28, color: '#9CA3AF' }), 'Décrivez votre projet…');
    el(card, px({ left: 30, bottom: 26 }), icon('image', 30, 1.8, '#9CA3AF'));
    els.create = el(card, px({ right: 22, bottom: 20, width: 176, height: 62, borderRadius: 31, background: 'linear-gradient(90deg,#7C3AED,#9333EA)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 24, boxShadow: '0 10px 24px rgba(124,58,237,0.35)' }), `${icon('send', 24, 2.2, '#fff')}Créer`);
    els.ripple = el(els.create, px({ left: 88, top: 31, width: 10, height: 10, borderRadius: 5, marginLeft: -5, marginTop: -5, background: 'rgba(255,255,255,0.55)' }));
    const chips = el(v, px({ left: 0, width: BW, top: 676, display: 'flex', justifyContent: 'center', gap: 18 }));
    chips.style.position = 'absolute';
    [['chart-column', 'Tableau de bord'], ['file-pen-line', 'Formulaire'], ['users', 'Portail RH']].forEach(([ic, t]) => {
      const ch = h('div', '', chips, `${icon(ic, 22, 2, '#7C3AED')}${t}`);
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
    el(side, px({ left: 22, top: 26, display: 'flex', alignItems: 'center', gap: 10, color: '#fff', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 21 }), `<div style="width:36px;height:36px;border-radius:10px;background:#2E8BC0;display:flex;align-items:center;justify-content:center">${icon('receipt', 20, 2.2, '#fff')}</div>Notes de frais`);
    const nav = [['layout-dashboard', 'Tableau de bord', 1], ['receipt', 'Mes notes'], ['badge-check', 'À valider', 0, '12'], ['download', 'Export comptable'], ['users', 'Équipes']];
    els.nav = nav.map(([ic, t, act, badge], i) => el(side, px({ left: 12, top: 100 + i * 54, width: 186, height: 44, borderRadius: 10, background: act ? 'rgba(46,139,192,0.35)' : 'transparent', display: 'flex', alignItems: 'center', gap: 12, padding: '0 12px', color: act ? '#fff' : '#A9BCD0', fontFamily: 'DM Sans', fontWeight: 500, fontSize: 17 }),
      `${icon(ic, 19, 2, act ? '#fff' : '#A9BCD0')}<span style="flex:1">${t}</span>${badge ? `<span style="background:#FF6633;color:#fff;border-radius:10px;padding:1px 8px;font-size:14px;font-weight:700">${badge}</span>` : ''}`));
    els.side = side;

    const head = el(app, px({ left: 238, top: 24, width: 812, height: 50 }));
    el(head, px({ left: 0, top: 0, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 30, color: '#0B2545' }), 'Tableau de bord');
    el(head, px({ left: 0, top: 40, fontFamily: 'DM Sans', fontSize: 16, color: '#6B7280' }), 'Septembre 2026 · équipe France');
    els.exportBtn = el(head, px({ right: 170, top: 4, height: 42, padding: '0 16px', borderRadius: 10, border: '1px solid #D5DEE8', display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'DM Sans', fontWeight: 600, fontSize: 16, color: '#0B2545', background: '#fff' }), `${icon('download', 18, 2, '#0B2545')}Export CSV`);
    els.newBtn = el(head, px({ right: 0, top: 4, height: 42, padding: '0 16px', borderRadius: 10, background: '#2E8BC0', display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'DM Sans', fontWeight: 600, fontSize: 16, color: '#fff' }), `${icon('plus', 18, 2.4, '#fff')}Nouvelle note`);
    els.head = head;

    els.kpis = KPIS.map(([t, v, suf, ic, col], i) => {
      const k = el(app, px({ left: 238 + i * 203, top: 100, width: 190, height: 106, borderRadius: 14, border: '1px solid #E5EAF0', background: '#fff' }));
      el(k, px({ left: 16, top: 14, fontFamily: 'DM Sans', fontSize: 15, color: '#6B7280' }), t);
      const val = el(k, px({ left: 16, top: 42, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 32, color: '#0B2545' }), '0');
      el(k, px({ right: 14, top: 14, width: 38, height: 38, borderRadius: 10, background: col + '1A', display: 'flex', alignItems: 'center', justifyContent: 'center' }), icon(ic, 20, 2.2, col));
      k._val = val; k._v = v; k._suf = suf;
      return k;
    });

    const chart = el(app, px({ left: 238, top: 226, width: 390, height: 300, borderRadius: 14, border: '1px solid #E5EAF0', background: '#fff' }));
    el(chart, px({ left: 18, top: 16, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 18, color: '#0B2545' }), 'Dépenses par équipe');
    els.bars = BARS.map(([lab, v], i) => {
      const b = el(chart, px({ left: 26 + i * 59, bottom: 44, width: 36, height: v * 190, borderRadius: '8px 8px 3px 3px', background: i === 0 ? '#2E8BC0' : '#9CCBE8', transformOrigin: '50% 100%' }));
      el(chart, px({ left: 14 + i * 59, bottom: 16, width: 60, textAlign: 'center', fontFamily: 'DM Sans', fontSize: 12, color: '#6B7280' }), lab);
      return b;
    });
    els.chart = chart;

    const table = el(app, px({ left: 644, top: 226, width: 406, height: 520, borderRadius: 14, border: '1px solid #E5EAF0', background: '#fff' }));
    el(table, px({ left: 18, top: 16, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 18, color: '#0B2545' }), 'Dernières notes');
    els.rows = ROWS.map(([ini, t, team, amt, st], i) => {
      const r = el(table, px({ left: 12, top: 60 + i * 88, width: 382, height: 76, borderRadius: 12, background: i % 2 ? '#fff' : '#F7F9FC' }));
      el(r, px({ left: 12, top: 18, width: 40, height: 40, borderRadius: 20, background: ['#DBEAFE', '#EDE9FE', '#FFEDD5', '#DCFCE7', '#E0F2FE'][i], color: '#0B2545', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 15, display: 'flex', alignItems: 'center', justifyContent: 'center' }), ini);
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
    el(top, px({ left: 70, top: 20, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 24, color: '#111827' }), 'Notes de frais');
    el(top, px({ left: 262, top: 23, display: 'flex', gap: 6, alignItems: 'center', fontFamily: 'DM Sans', fontSize: 18, color: '#6B7280' }), `${icon('users', 20, 2, '#6B7280')}1`);
    const tg = el(top, px({ left: 510, top: 13, height: 44, borderRadius: 12, background: '#F3F4F6', display: 'flex', alignItems: 'center', gap: 4, padding: '0 6px' }));
    ['monitor', 'code-xml', 'smartphone'].forEach((ic, i) => {
      const b = h('div', '', tg, icon(ic, 20, 2, i === 0 ? '#111827' : '#9CA3AF'));
      Object.assign(b.style, px({ width: 44, height: 34, borderRadius: 9, background: i === 0 ? '#fff' : 'transparent', display: 'flex', alignItems: 'center', justifyContent: 'center' }));
    });
    el(top, px({ left: 690, top: 13, width: 340, height: 44, borderRadius: 12, border: '1px solid #E5E7EB', display: 'flex', alignItems: 'center', gap: 10, padding: '0 14px', fontFamily: 'DM Sans', fontSize: 18, color: '#6B7280' }), `${icon('house', 18, 2, '#9CA3AF')}/tableau-de-bord`);
    els.publish = el(top, px({ right: 22, top: 11, width: 196, height: 48, borderRadius: 12, background: 'linear-gradient(90deg,#6D5DF6,#3B82F6)', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 20, overflow: 'hidden', boxShadow: '0 8px 20px rgba(79,70,229,0.30)' }));
    els.pubA = el(els.publish, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }), `${icon('rocket', 22, 2.2, '#fff')}Publier`);
    els.pubB = el(els.publish, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, background: '#16A34A' }), `${icon('circle-check', 22, 2.4, '#fff')}En ligne`);

    const left = el(v, px({ left: 0, top: 70, width: 470, height: BH - CH - 70, borderRight: '1px solid #EEF0F4', background: '#fff', overflow: 'hidden' }));
    els.bubble = el(left, px({ left: 22, top: 22, width: 426, padding: '16px 18px', borderRadius: 16, background: '#F4F1FF', fontFamily: 'DM Sans', fontSize: 17, lineHeight: 1.45, color: '#312E81' }), PROMPT);
    els.steps = STEPS.map(([t0, ic, verb, code], i) => {
      const r = el(left, px({ left: 22, top: 176 + i * 50, width: 430, height: 40, display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'DM Sans', fontSize: 18, color: '#374151' }),
        `${icon(ic, 20, 2, ic === 'circle-check' ? '#16A34A' : '#6B7280')}<span style="font-weight:600">${verb}</span>${code ? `<span style="font-family:'JetBrains Mono';font-size:14px;background:#F3F4F6;border-radius:6px;padding:3px 8px;color:#4B5563;white-space:nowrap">${code}</span>` : ''}`);
      r._t0 = t0;
      return r;
    });
    els.status = el(left, px({ left: 22, top: 590, width: 426, height: 70, borderRadius: 16, overflow: 'hidden' }));
    els.stA = el(els.status, px({ inset: 0, background: 'linear-gradient(90deg,#FFF4EC,#FFF9F4)', border: '1px solid #FDE2CF', borderRadius: 16, display: 'flex', alignItems: 'center', gap: 14, padding: '0 18px', fontFamily: 'DM Sans', fontSize: 18, color: '#9A3412' }),
      `<div style="width:14px;height:14px;border-radius:7px;background:#F97316"></div><div style="flex:1"><div style="font-weight:700">DigiCraft</div><div style="font-size:15px;color:#C2410C">Construction en cours…</div></div>`);
    els.eq = [0, 1, 2, 3].map((i) => el(els.stA, px({ right: 20 + (3 - i) * 9, bottom: 22, width: 5, height: 24, borderRadius: 3, background: '#F97316', transformOrigin: '50% 100%' })));
    els.stB = el(els.status, px({ inset: 0, background: '#ECFDF3', border: '1px solid #BBF7D0', borderRadius: 16, display: 'flex', alignItems: 'center', gap: 14, padding: '0 18px', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 19, color: '#15803D' }), `${icon('circle-check', 26, 2.4, '#16A34A')}Votre application est prête`);
    el(left, px({ left: 22, bottom: 22, width: 426, height: 64, borderRadius: 16, border: '1px solid #E5E7EB', display: 'flex', alignItems: 'center', padding: '0 18px', fontFamily: 'DM Sans', fontSize: 18, color: '#9CA3AF' }), 'Demandez à DigiCraft…');

    const pv = el(v, px({ left: 470, top: 70, width: BW - 470, height: BH - CH - 70, background: '#F4F6FA', overflow: 'hidden' }));
    els.prog = el(pv, px({ left: 0, top: 0, height: 4, width: BW - 470, background: 'linear-gradient(90deg,#8F8AFF,#2E8BC0)', transformOrigin: '0 50%' }));
    els.pvLabel = el(pv, px({ left: 26, top: 24, display: 'flex', alignItems: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 600, fontSize: 18, color: '#4B5563' }), `${icon('loader-circle', 20, 2.2, '#7C3AED')}<span>Aperçu en direct</span>`);
    els.spin = els.pvLabel.querySelector('svg');
    els.fast = el(pv, px({ right: 26, top: 20, display: 'flex', alignItems: 'center', gap: 8, padding: '6px 14px', borderRadius: 16, background: '#EDE9FE', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 15, color: '#6D28D9' }), `${icon('history', 17, 2.2, '#6D28D9')}Accéléré`);
    buildApp(pv);
    els.toast = el(v, px({ left: 470 + (BW - 470) / 2 - 330, top: 96, width: 660, height: 84, borderRadius: 18, background: '#0B2545', color: '#fff', display: 'flex', alignItems: 'center', gap: 16, padding: '0 24px', boxShadow: '0 20px 50px rgba(2,36,70,0.35)', fontFamily: 'DM Sans' }),
      `<div style="width:44px;height:44px;border-radius:22px;background:#16A34A;display:flex;align-items:center;justify-content:center">${icon('check', 26, 3, '#fff')}</div><div><div style="font-weight:700;font-size:21px">Votre application est en ligne</div><div style="font-size:16px;color:#A9BCD0">Lien partagé avec votre équipe</div></div>`);
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
    el(sc, px({ left: 24, top: 78, display: 'flex', alignItems: 'center', gap: 12, color: '#fff', fontFamily: 'DM Sans', fontWeight: 700, fontSize: 22 }), `<div style="width:40px;height:40px;border-radius:11px;background:#2E8BC0;display:flex;align-items:center;justify-content:center">${icon('receipt', 22, 2.2, '#fff')}</div>Notes de frais`);
    el(sc, px({ left: 24, top: 166, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 24, color: '#0B2545' }), 'Nouvelle note');
    const card = el(sc, px({ left: 18, top: 206, width: 346, height: 390, borderRadius: 22, background: '#fff', boxShadow: '0 8px 24px rgba(2,36,70,0.08)' }));
    const rc = el(card, px({ left: 18, top: 18, width: 310, height: 150, borderRadius: 14, background: '#EEF3F8', overflow: 'hidden' }));
    const paper = el(rc, px({ left: 105, top: 14, width: 100, height: 128, background: '#fff', borderRadius: 4, boxShadow: '0 4px 12px rgba(0,0,0,0.10)', transform: 'rotate(-6deg)' }));
    [16, 32, 48, 64, 90].forEach((y, i) => el(paper, px({ left: 12, top: y, width: i === 4 ? 44 : 76, height: i === 4 ? 9 : 6, borderRadius: 3, background: i === 4 ? '#0B2545' : '#D5DEE8' })));
    el(rc, px({ right: 10, bottom: 10, width: 34, height: 34, borderRadius: 17, background: '#2E8BC0', display: 'flex', alignItems: 'center', justifyContent: 'center' }), icon('camera', 18, 2.2, '#fff'));
    el(card, px({ left: 20, top: 186, fontFamily: 'DM Sans', fontSize: 15, color: '#6B7280' }), 'Montant');
    el(card, px({ left: 20, top: 206, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 40, color: '#0B2545' }), '86,40 €');
    el(card, px({ left: 20, top: 272, padding: '6px 14px', borderRadius: 14, background: '#E0F2FE', color: '#0369A1', fontFamily: 'DM Sans', fontWeight: 600, fontSize: 15 }), 'Repas client');
    el(card, px({ left: 150, top: 272, padding: '6px 14px', borderRadius: 14, background: '#F3F4F6', color: '#4B5563', fontFamily: 'DM Sans', fontWeight: 600, fontSize: 15 }), '25 sept.');
    els.stChip = el(card, px({ left: 20, top: 330, height: 36, padding: '0 14px', borderRadius: 18, display: 'flex', alignItems: 'center', gap: 8, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 15 }));
    els.send = el(sc, px({ left: 18, top: 620, width: 346, height: 66, borderRadius: 18, background: '#2E8BC0', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontWeight: 700, fontSize: 20, overflow: 'hidden' }));
    els.sendTxt = el(els.send, px({ inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }));
    els.tap = el(els.send, px({ left: 173, top: 33, width: 20, height: 20, marginLeft: -10, marginTop: -10, borderRadius: 10, background: 'rgba(255,255,255,0.45)' }));
    els.notif = el(sc, px({ left: 12, top: 12, width: 358, height: 96, borderRadius: 24, background: 'rgba(17,24,39,0.94)', display: 'flex', alignItems: 'center', gap: 14, padding: '0 16px', fontFamily: 'DM Sans', color: '#fff' }),
      `<div style="width:48px;height:48px;border-radius:13px;background:#fff;display:flex;align-items:center;justify-content:center">${window.dcIcon(34, 'n')}</div><div><div style="font-weight:700;font-size:17px">Note validée</div><div style="font-size:14px;color:#D1D5DB;line-height:1.35">Votre note de 86,40 € a été validée par votre manager.</div></div>`);
    return ph;
  }

  SCENES.push({
    id: 'demo',
    build(root) {
      els.root = root;
      root.style.background = 'linear-gradient(160deg,#F7FAFC 0%,#F1F3FF 55%,#EAF3FA 100%)';
      const grid = el(root, { inset: '-64px' });
      grid.className = 'abs bg-grid-light';
      els.grid = grid;
      els.b1 = el(root, px({ width: 900, height: 900, left: -250, top: 250, background: 'rgba(143,138,255,0.16)' }));
      els.b1.className = 'blob';
      els.b2 = el(root, px({ width: 800, height: 800, right: -200, top: -300, background: 'rgba(46,139,192,0.14)' }));
      els.b2.className = 'blob';

      els.capTop = el(root, px({ left: 0, width: 1920, top: 70, textAlign: 'center', fontSize: 62, fontWeight: 800, color: 'var(--navy)', letterSpacing: '-0.01em', zIndex: 5 }));
      els.cT = words(els.capTop, [{ t: 'Décrivez' }, { t: "l'outil" }, { t: 'dont' }, { t: 'vous' }, { t: 'avez' }, { t: 'besoin.', c: 'hl' }]);

      const persp = el(root, { inset: '0', perspective: '2600px' });
      const br = el(persp, px({ left: 960 - BW / 2, top: 540 - BH / 2, width: BW, height: BH, borderRadius: 22, overflow: 'hidden', background: '#fff', boxShadow: '0 60px 140px rgba(2,36,70,0.22), 0 10px 30px rgba(2,36,70,0.10)' }));
      els.browser = br;
      const chrome = el(br, px({ left: 0, top: 0, width: BW, height: CH, background: '#022446' }));
      ['#FF6633', '#F7C948', '#2E8BC0'].forEach((c, i) => el(chrome, px({ left: 26 + i * 26, top: 21, width: 14, height: 14, borderRadius: 7, background: c, opacity: 0.9 })));
      el(chrome, px({ left: BW / 2 - 260, top: 11, width: 520, height: 34, borderRadius: 17, background: 'rgba(255,255,255,0.10)', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10, fontFamily: 'DM Sans', fontSize: 17, color: '#C9D9E8' }), `${icon('lock', 15, 2.2, '#6BB8E6')}digicraft.leyton-cognitx.com`);
      const content = el(br, px({ left: 0, top: CH, width: BW, height: BH - CH, overflow: 'hidden' }));
      els.home = buildHome(content);
      els.build = buildBuild(content);
      els.cursor = el(br, px({ left: 0, top: 0, zIndex: 20 }), cursorSvg());

      const cl = el(root, px({ left: 90, top: 250, width: 640, fontSize: 80, fontWeight: 800, lineHeight: 1.06, color: 'var(--navy)', letterSpacing: '-0.015em' }));
      els.cl1 = words(cl, 'DigiCraft');
      els.cl2 = words(cl, 'le construit');
      els.cl3 = words(cl, [{ t: 'sous' }, { t: 'vos' }, { t: 'yeux.' }], 'hl');
      els.cl3.querySelectorAll('.wi').forEach((w) => w.classList.add('hl'));
      els.capLeft = cl;
      const chip = (y, t) => el(root, px({ left: 94, top: y, height: 76, padding: '0 30px 0 20px', borderRadius: 38, background: '#fff', boxShadow: '0 14px 36px rgba(2,36,70,0.12)', display: 'flex', alignItems: 'center', gap: 16, fontSize: 32, fontWeight: 700, color: 'var(--navy)' }),
        `<div style="width:44px;height:44px;border-radius:22px;background:#FFE8E0;display:flex;align-items:center;justify-content:center">${icon('x', 26, 3, '#FF6633')}</div>${t}`);
      els.chipA = chip(600, 'Sans développeur');
      els.chipB = chip(700, 'Sans ticket IT');

      els.phone = buildPhone(root);
      const pc = el(root, px({ left: 130, top: 330, width: 820, fontSize: 104, fontWeight: 800, lineHeight: 1.04, color: 'var(--navy)', letterSpacing: '-0.02em' }));
      els.pc1 = words(pc, 'En ligne');
      els.pc2 = words(pc, [{ t: 'le', c: 'hl' }, { t: 'jour', c: 'hl' }, { t: 'même.', c: 'hl' }]);
      els.pcChip = el(root, px({ left: 136, top: 600, height: 64, padding: '0 26px 0 18px', borderRadius: 32, background: '#fff', boxShadow: '0 12px 30px rgba(2,36,70,0.10)', display: 'flex', alignItems: 'center', gap: 14, fontSize: 26, fontWeight: 600, color: 'var(--navy)' }),
        `${icon('circle-check', 30, 2.4, '#16A34A')}Web et mobile, prête à partager`);
    },
    update(T) {
      S(els.root, { o: P(T, 20.1, 0.35, Ease.outCubic) });
      S(els.grid, { x: -T * 6, y: -T * 4 });
      S(els.b1, { x: Math.sin(T * 0.4) * 60, y: Math.cos(T * 0.3) * 40 });
      S(els.b2, { x: Math.cos(T * 0.35) * 50, y: Math.sin(T * 0.45) * 40 });

      revealWords(T, els.cT, 20.95, 0.07, 0.6);
      hideWords(T, els.cT, 21.7, 0.02, 0.35);

      // Caméra sur le navigateur.
      const ent = P(T, 19.95, 1.15, Ease.outExpo);
      let x = kf(T, [[21.7, 0], [22.35, 0, Ease.inOutCubic], [25.15, 0], [25.95, 318, Ease.inOutQuart], [32.6, 318], [33.4, -290, Ease.inOutQuart]]);
      let y = kf(T, [[21.0, 95], [21.7, 80], [22.35, -150, Ease.inOutQuart], [25.15, -160], [25.95, 40, Ease.inOutQuart], [32.6, 40], [33.4, 30, Ease.inOutQuart]]);
      let s = kf(T, [[21.0, 0.76], [21.7, 0.78], [22.35, 1.42, Ease.inOutQuart], [25.15, 1.48, Ease.lin], [25.95, 0.735, Ease.inOutQuart], [31.6, 0.76, Ease.lin], [32.6, 0.76], [33.4, 0.64, Ease.inOutQuart]]);
      let rx = kf(T, [[21.0, 14], [21.7, 0]]);
      let ry = kf(T, [[25.15, 0], [25.95, -4, Ease.inOutQuart], [31.6, -2, Ease.lin], [32.6, -2], [33.4, 8, Ease.inOutQuart]]);
      y = lerp(900, y, ent); rx = lerp(40, rx, ent); s = lerp(0.6, s, ent);
      const dim = P(T, 32.7, 0.8, Ease.inOutCubic);
      S(els.browser, { x, y, s, rx, ry, o: ent > 0 ? 1 - dim * 0.72 : 0, blur: dim * 9 });

      // Saisie du prompt.
      const n = Math.round(clamp((T - TYPE0) / (TYPE1 - TYPE0)) * PROMPT.length);
      const caretOn = T < TYPE0 ? (Math.floor(T * 2.4) % 2 === 0) : T < 25.0 ? (T < TYPE1 || Math.floor(T * 2.4) % 2 === 0) : false;
      els.ta.innerHTML = PROMPT.slice(0, n) + `<span style="display:inline-block;width:2px;height:32px;background:#7C3AED;vertical-align:-6px;margin-left:1px;opacity:${caretOn ? 1 : 0}"></span>`;
      els.ph.style.opacity = n > 0 ? '0' : '1';

      // Curseur.
      const curVis = (T > 24.35 && T < 25.5) || (T > 31.35 && T < 33.0);
      let cx, cy, press = 0;
      if (T < 26) {
        const m = P(T, 24.35, 0.6, Ease.inOutCubic);
        cx = lerp(1180, 1160, m); cy = lerp(900, 612 + CH, m);
        cx = lerp(1480, 1178, m); cy = lerp(880, 408 + 226 - 20 - 31 + CH, m);
        press = P(T, 24.98, 0.08) * (1 - P(T, 25.1, 0.12));
      } else {
        const m = P(T, 31.35, 0.75, Ease.inOutCubic);
        cx = lerp(1150, BW - 22 - 98, m); cy = lerp(620, CH + 11 + 24, m);
        press = P(T, 32.3, 0.08) * (1 - P(T, 32.42, 0.12));
      }
      els.cursor.style.display = curVis ? 'block' : 'none';
      S(els.cursor, { x: cx - 12, y: cy - 8, s: 1 - press * 0.18, o: P(T, T < 26 ? 24.35 : 31.35, 0.2) });

      const cp = P(T, 24.98, 0.08) * (1 - P(T, 25.1, 0.2, Ease.outBack));
      S(els.create, { s: 1 - cp * 0.07 });
      const rp = P(T, 25.0, 0.5, Ease.outCubic);
      S(els.ripple, { s: 1 + rp * 24, o: T > 25 ? 1 - rp : 0 });

      // Passage à la vue construction.
      const sw = P(T, 25.2, 0.55, Ease.inOutCubic);
      S(els.home, { o: 1 - sw, y: -sw * 60, s: 1 - sw * 0.04 });
      els.home.style.display = sw >= 1 ? 'none' : 'block';
      els.build.style.display = sw > 0 ? 'block' : 'none';
      S(els.build, { o: sw, y: (1 - sw) * 60 });
      const bb = P(T, 25.45, 0.6, Ease.outQuint);
      S(els.bubble, { y: (1 - bb) * 260, s: lerp(1.3, 1, bb), o: bb });
      els.bubble.style.transformOrigin = '0 0';

      els.steps.forEach((r) => {
        const p = P(T, r._t0, 0.45, Ease.outQuint);
        S(r, { x: (1 - p) * -40, o: p });
      });
      const ready = P(T, 30.75, 0.35, Ease.outCubic);
      const stIn = P(T, 26.05, 0.4, Ease.outQuint);
      S(els.status, { y: (1 - stIn) * 30, o: stIn });
      S(els.stA, { o: 1 - ready });
      S(els.stB, { o: ready, s: 0.96 + 0.04 * ready });
      els.eq.forEach((b, i) => S(b, { sy: 0.3 + 0.7 * Math.abs(Math.sin(T * 7 + i * 1.3)) }));

      const prog = P(T, 26.1, 4.7, Ease.inOutSine);
      S(els.prog, { sx: Math.max(0.0001, prog), o: 1 - P(T, 31.0, 0.4) });
      S(els.spin, { r: T * 360 });
      S(els.fast, { o: P(T, 26.3, 0.4) * (1 - P(T, 31.0, 0.4)) });

      const skOut = P(T, 29.8, 0.6, Ease.inOutCubic);
      S(els.skel, { o: P(T, 26.4, 0.4) * (1 - skOut) });
      const pop = (e, t0, dy = 24) => { const p = P(T, t0, 0.55, Ease.outQuint); S(e, { y: (1 - p) * dy, o: p }); return p; };
      const sp = P(T, 27.0, 0.6, Ease.outExpo);
      S(els.side, { x: (1 - sp) * -210, o: sp > 0 ? 1 : 0 });
      pop(els.head, 27.35);
      S(els.exportBtn, { s: Math.max(0.0001, spring(T - 28.5, 240, 15)), o: T > 28.5 ? 1 : 0 });
      pop(els.table, 27.85);
      els.rows.forEach((r, i) => {
        pop(r, 28.0 + i * 0.1, 30);
        S(r._badge, { s: Math.max(0.0001, spring(T - (28.35 + i * 0.1), 260, 14)) });
      });
      els.kpis.forEach((k, i) => {
        pop(k, 29.05 + i * 0.1, 30);
        const cnt = P(T, 29.2 + i * 0.1, 1.1, Ease.outCubic) * k._v;
        k._val.textContent = (k._v % 1 ? cnt.toFixed(1).replace('.', ',') : fmt(cnt)) + k._suf;
      });
      pop(els.chart, 29.45);
      els.bars.forEach((b, i) => S(b, { sy: Math.max(0.0001, P(T, 29.6 + i * 0.07, 0.6, Ease.outBack)) }));

      const pubP = P(T, 32.4, 0.3, Ease.inOutCubic);
      S(els.pubB, { o: pubP, y: (1 - pubP) * 20 });
      S(els.pubA, { o: 1 - pubP, y: -pubP * 20 });
      S(els.publish, { s: 1 - (P(T, 32.3, 0.08) * (1 - P(T, 32.42, 0.2, Ease.outBack))) * 0.08 });
      const ts = spring(T - 32.5, 200, 17);
      S(els.toast, { y: (1 - ts) * -60, s: 0.9 + 0.1 * ts, o: T > 32.5 ? clamp((T - 32.5) * 6) : 0 });

      // Légendes de gauche pendant la construction.
      revealWords(T, els.cl1, 26.95, 0.08, 0.6);
      revealWords(T, els.cl2, 27.25, 0.1, 0.6);
      revealWords(T, els.cl3, 27.6, 0.1, 0.6);
      const clOut = P(T, 31.25, 0.45, Ease.inCubic);
      S(els.capLeft, { x: -clOut * 80, o: 1 - clOut });
      [[els.chipA, 29.25], [els.chipB, 30.2]].forEach(([c, t0]) => {
        const p = spring(T - t0, 230, 16);
        S(c, { x: (1 - p) * -60 - clOut * 80, s: Math.max(0.0001, 0.85 + 0.15 * p), o: clamp((T - t0) * 6) * (1 - clOut) });
      });

      // Téléphone.
      const pe = P(T, 32.85, 1.0, Ease.outExpo);
      S(els.phone, { y: (1 - pe) * 820, r: lerp(12, -4, pe) + Math.sin(T * 0.8) * 0.6, o: pe > 0 ? 1 : 0 });
      const tapT = 34.75;
      const tp = P(T, tapT, 0.55, Ease.outCubic);
      S(els.tap, { s: 1 + tp * 22, o: T > tapT ? 1 - tp : 0 });
      const sent = T > tapT + 0.15;
      const valid = T > 35.75;
      els.sendTxt.innerHTML = sent ? `${icon('check', 24, 3, '#fff')}Envoyée` : `${icon('send', 22, 2.2, '#fff')}Envoyer pour validation`;
      els.send.style.background = sent ? '#16A34A' : '#2E8BC0';
      S(els.send, { s: 1 - (P(T, tapT - 0.05, 0.06) * (1 - P(T, tapT + 0.05, 0.2))) * 0.05 });
      const [lab, col, bg] = valid ? ['Validée', '#15803D', '#DCFCE7'] : sent ? ['En attente de validation', '#B45309', '#FEF3C7'] : ['Brouillon', '#4B5563', '#F3F4F6'];
      els.stChip.innerHTML = `${icon(valid ? 'circle-check' : sent ? 'hourglass' : 'file-pen-line', 17, 2.4, col)}${lab}`;
      Object.assign(els.stChip.style, { background: bg, color: col });
      S(els.stChip, { s: valid ? 0.9 + 0.1 * spring(T - 35.75, 300, 14) : 1 });
      const nt = spring(T - 35.7, 190, 17);
      S(els.notif, { y: (1 - nt) * -130, o: T > 35.7 ? 1 : 0 });

      revealWords(T, els.pc1, 33.45, 0.1, 0.65);
      revealWords(T, els.pc2, 33.75, 0.1, 0.65);
      const pcc = spring(T - 34.3, 220, 17);
      S(els.pcChip, { y: (1 - pcc) * 30, o: clamp((T - 34.3) * 5) });
      if (window.TL.v2) {
        [els.capTop, els.capLeft, els.chipA, els.chipB, els.pc1.parentElement, els.pcChip].forEach((e) => (e.style.display = 'none'));
      }
    },
  });
})();

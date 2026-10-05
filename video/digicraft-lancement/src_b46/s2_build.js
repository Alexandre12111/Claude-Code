// Création : ordinateur portable, saisie du besoin, construction de l'app, itération, publication, tablette.
(function () {
  const { h, S, P, Ease, lerp, clamp, spring, icon } = E;
  const el = C.el;
  const PROMPT = 'Build a client onboarding portal for our consulting team, with document upload and manager approval.';
  const PROMPT2 = 'Add a manager dashboard with onboarding status by client.';
  const TYPED_ON_LAPTOP = 34;

  // Accueil DigiCraft (dans l'écran du portable), taille virtuelle 1600 × 950.
  function home(parent) {
    const v = el(parent, { left: '0', top: '0', width: '1600px', height: '950px', background: 'radial-gradient(900px 500px at 80% 0%, rgba(183,166,255,0.35), rgba(0,0,0,0) 70%), radial-gradient(900px 600px at 10% 100%, rgba(143,198,231,0.4), rgba(0,0,0,0) 70%), #F7FAFC', transformOrigin: '0 0' });
    v.className = 'ui';
    const nav = el(v, { left: '48px', top: '34px', display: 'flex', alignItems: 'center', gap: '14px' });
    nav.innerHTML = window.dcIcon(40, 'hm', 'brand') + '<span class="nh" style="font-size:30px;color:#022446">Digi<span style="color:#2E8BC0">Craft</span></span>';
    el(v, { right: '48px', top: '38px', display: 'flex', gap: '28px', alignItems: 'center', fontSize: '18px', color: '#475569', fontWeight: '500' }, 'Templates<span>Docs</span><span class="pill" style="background:#022446;color:#fff">Sign in</span>');
    el(v, { left: '0', width: '1600px', top: '230px', textAlign: 'center', fontFamily: 'Outfit', fontWeight: '600', fontSize: '68px', color: '#0B2545', lineHeight: '1.1' }, 'What would you like to <span class="dcgrad">build today?</span>');
    el(v, { left: '0', width: '1600px', top: '330px', textAlign: 'center', fontSize: '22px', color: '#64748B' }, 'Describe your idea. DigiCraft builds the app, ready for your team.');
    const box = el(v, { left: '300px', top: '420px', width: '1000px', height: '190px', borderRadius: '28px', background: '#fff', boxShadow: '0 20px 60px rgba(1,36,70,0.14)', border: '1.5px solid #E2E8F0' });
    const txt = el(box, { left: '34px', top: '30px', right: '120px', fontSize: '26px', color: '#0B2545', lineHeight: '1.4' });
    el(box, { right: '24px', bottom: '22px', width: '60px', height: '60px', borderRadius: '30px', background: 'linear-gradient(135deg,#2E8BC0,#6D5DF6)', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('arrow-up-right', 28, 2.6, '#fff'));
    const chips = el(v, { left: '0', width: '1600px', top: '650px', display: 'flex', justifyContent: 'center', gap: '14px' });
    ['Expense approvals', 'Sales pipeline', 'Supplier audits', 'HR onboarding'].forEach((t) => h('span', 'chip', chips, t));
    return { v, txt };
  }

  // Portail client généré (taille virtuelle 1180 × 800).
  function portal(parent, dash) {
    const v = el(parent, { left: '0', top: '0', width: '1180px', height: '800px', background: '#F7FAFC', transformOrigin: '0 0' });
    v.className = 'ui';
    const top = el(v, { left: '0', right: '0', top: '0', height: '72px', background: '#fff', borderBottom: '1px solid #E5ECF2' });
    el(top, { left: '28px', top: '20px', display: 'flex', alignItems: 'center', gap: '12px', fontSize: '21px', fontWeight: '700' }, `<span style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#0EA5A4,#2E8BC0);display:flex;align-items:center;justify-content:center">${icon('briefcase', 18, 2.2, '#fff')}</span>Client Portal`);
    el(top, { left: '300px', top: '25px', display: 'flex', gap: '30px', fontSize: '17px', fontWeight: '600', color: '#64748B' }, '<span style="color:#0B2545">Clients</span><span>Documents</span><span>Approvals</span><span>Messages</span>');
    U.avatar(top, 1110, 18, 38, 'AC', '#6D5DF6');
    el(top, { left: '860px', top: '18px', width: '220px', height: '38px', borderRadius: '19px', background: '#F1F5F9', display: 'flex', alignItems: 'center', gap: '8px', padding: '0 14px', fontSize: '15px', color: '#94A3B8' }, `${icon('search', 16, 2, '#94A3B8')}Search clients`);
    const body = el(v, { left: '0', right: '0', top: '72px', bottom: '0' });
    // Bandeau de pilotage (ajouté à la 2e demande).
    const dk = el(body, { left: '28px', top: '22px', width: '1124px', height: '118px' });
    const kp = [['Active clients', '24', '+3', '#2E8BC0', 'users'], ['In onboarding', '9', '+2', '#6D5DF6', 'loader-circle'], ['Avg. time to onboard', '6.5 d', '−1.2 d', '#0EA5A4', 'timer'], ['Pending approvals', '5', '−2', '#F59E0B', 'clipboard-check']]
      .map(([l, val, d, col, ic], i) => U.kpi(dk, i * 285, 0, 270, l, val, d, col, ic));
    const grid = el(body, { left: '28px', top: dash ? '162px' : '22px', width: '1124px', height: '600px' });
    const fl = el(grid, { left: '0', top: '0', display: 'flex', gap: '10px' });
    ['All clients', 'Onboarding', 'Awaiting documents', 'Approved'].forEach((t, i) => { const c = h('span', 'chip', fl, t); if (!i) Object.assign(c.style, { background: '#022446', color: '#fff', borderColor: '#022446' }); });
    const CL = [['Northwind Logistics', 'Claire Dubois', 'Onboarding', 'info', 0.6, '#2E8BC0'], ['Bluebay Energy', 'Mark Allen', 'Approved', 'ok', 1, '#16A34A'], ['Atlas Retail', 'Sofia Rossi', 'Awaiting docs', 'warn', 0.35, '#F59E0B'],
      ['Helio Pharma', 'Jonas Weber', 'Onboarding', 'info', 0.72, '#6D5DF6'], ['Vertex Industries', 'Lena Novak', 'Approved', 'ok', 1, '#0EA5A4'], ['Orion Bank', 'Paul Martin', 'Awaiting docs', 'warn', 0.2, '#EF4444']];
    const cards = CL.map(([n, c, st, k, pv, col], i) => {
      const x = (i % 3) * 382, y = 66 + Math.floor(i / 3) * 196;
      const cd = el(grid, { left: x + 'px', top: y + 'px', width: '360px', height: '178px' }); cd.className = 'card';
      el(cd, { left: '22px', top: '22px', width: '46px', height: '46px', borderRadius: '13px', background: col + '22', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('landmark', 22, 2, col));
      el(cd, { left: '82px', top: '22px', fontSize: '19px', fontWeight: '700' }, n);
      el(cd, { left: '82px', top: '50px', fontSize: '15px', color: '#64748B' }, c);
      el(cd, { left: '22px', top: '96px' }).appendChild(U.badge(h('div'), st, k));
      el(cd, { left: '22px', top: '140px', fontSize: '13px', color: '#64748B', fontWeight: '600' }, 'Documents');
      const pr = U.progress(cd, 110, 144, 220, col); pr(pv);
      return cd;
    });
    if (!dash) dk.style.display = 'none';
    return { v, dk, kp, grid, cards };
  }

  // ---------- Portable ----------
  {
    const a = {};
    SCENES.push({
      id: 'laptop',
      build(root) {
        a.root = root;
        root.style.perspective = '2000px';
        a.cam = el(root, { inset: '0', transformOrigin: '50% 50%', transformStyle: 'preserve-3d' });
        el(a.cam, { left: '-200px', top: '-200px', width: '2320px', height: '1480px', background: 'linear-gradient(180deg,#E6EFF6 0%,#D5E4EF 55%,#B9CCDC 64%,#A5BBCD 100%)' });
        a.g = C.glow(a.cam, 1600, 'rgba(255,255,255,0.85)', 1);
        // Lumières de bureau floues à l'arrière-plan.
        const rb = E.seeded(8);
        a.bok = Array.from({ length: 14 }, (_, i) => ({ e: C.glow(a.cam, 90 + rb() * 220, ['rgba(255,214,170,0.7)', 'rgba(143,198,231,0.75)', 'rgba(183,166,255,0.65)'][i % 3], 1), x: rb() * 1920, y: 60 + rb() * 420, k: rb() * 6 }));
        a.shafts = [0, 1, 2].map((i) => el(a.cam, { left: '0', top: '-400px', width: 180 + i * 90 + 'px', height: '2000px', background: 'linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.55), rgba(255,255,255,0))', transform: 'rotate(28deg)', filter: 'blur(26px)' }));
        a.shadow = el(a.cam, { left: '360px', top: '800px', width: '1200px', height: '90px', borderRadius: '50%', background: 'radial-gradient(closest-side, rgba(20,40,60,0.45), rgba(20,40,60,0))' });
        a.lap = U.laptop(a.cam, 960, 500, 1060);
        const sw = a.lap.w * (1 - 0.044);
        a.home = home(a.lap.screen);
        a.k = sw / 1600;
        a.home.v.style.transform = `scale(${a.k})`;
        a.cup = el(a.cam, { left: '1560px', top: '640px', width: '120px', height: '180px', borderRadius: '14px 14px 22px 22px', background: 'linear-gradient(90deg, rgba(255,255,255,0.35), rgba(255,255,255,0.75) 40%, rgba(255,255,255,0.3))', border: '2px solid rgba(255,255,255,0.8)', boxShadow: '20px 30px 50px rgba(30,60,90,0.18)' });
      },
      update(T) {
        a.root.style.opacity = String(P(T, 12.5, 0.35, Ease.inOutSine));
        // Travelling avant avec rotation, puis plongée dans l'écran.
        const u = clamp((T - 12.5) / 1.5);
        const push = Ease.inExpo(clamp((T - 13.95) / 0.65));
        const k = lerp(0.94, 1.1, Ease.outCubic(u)) * lerp(1, 3.3, push);
        S(a.cam, { s: k, y: 60 * k * push, x: lerp(40, 0, u) });
        S(a.lap.g, { ry: lerp(24, 0, Ease.outCubic(u)), rx: lerp(8, 0, Ease.outCubic(u)) });
        a.cam.style.filter = push > 0.3 ? `blur(${((push - 0.3) * 18).toFixed(1)}px)` : 'none';
        a.shafts.forEach((s, i) => S(s, { x: 300 + i * 520 + Math.sin(T * 0.3 + i) * 60 + (T - 12.5) * 18, r: 28 }));
        S(a.g, { x: 1400, y: 200 });
        a.bok.forEach((b) => S(b.e, { x: b.x + Math.sin(T * 0.3 + b.k) * 30, y: b.y, o: 0.7 + 0.3 * Math.sin(T * 1.3 + b.k) }));
        a.home.txt.innerHTML = '<span style="color:#94A3B8">Describe the app your team needs…</span>';
        const out = P(T, 19.45, 0.3, Ease.inCubic);
        S(a.lap.g, { o: 1 - out * 0.2 });
      },
    });
  }

  // ---------- Saisie du besoin ----------
  {
    const a = {};
    SCENES.push({
      id: 'prompt',
      build(root) {
        a.root = root;
        a.f = U.aurora(root, 'light', 5);
        a.cam = el(root, { inset: '0', transformOrigin: '50% 45%' });
        a.card = el(a.cam, { left: '260px', top: '330px', width: '1400px', height: '300px', borderRadius: '36px', background: '#fff', boxShadow: '0 40px 120px rgba(1,36,70,0.22)', border: '1.5px solid #E2E8F0' });
        a.card.className = 'abs ui';
        a.txt = el(a.card, { left: '48px', top: '44px', right: '150px', fontFamily: 'Outfit', fontSize: '44px', lineHeight: '1.32', color: '#0B2545' });
        a.send = el(a.card, { right: '36px', bottom: '34px', width: '84px', height: '84px', borderRadius: '42px', background: 'linear-gradient(135deg,#2E8BC0,#6D5DF6)', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('arrow-up-right', 38, 2.6, '#fff'));
        el(a.card, { left: '48px', bottom: '40px', display: 'flex', gap: '14px', fontSize: '18px', color: '#64748B', alignItems: 'center' }, `${icon('image', 22, 2, '#94A3B8')}${icon('file-plus', 22, 2, '#94A3B8')}<span>Attach a process doc</span>`);
        a.tip = el(a.cam, { left: '0', width: '1920px', top: '676px', display: 'flex', justifyContent: 'center', gap: '14px', fontFamily: 'DM Sans' });
        h('span', '', a.tip, 'Not sure where to start?').style.cssText = 'font-size:18px;color:#475569;align-self:center;margin-right:6px';
        a.chips = ['Expense approvals', 'Sales pipeline', 'Supplier audits', 'HR onboarding'].map((t) => h('span', 'chip', a.tip, t));
      },
      update(T) {
        a.root.style.opacity = String(P(T, 18.3, 0.2, Ease.inOutSine));
        a.f(T, 0.8);
        const u = T - 18.3;
        S(a.cam, { s: lerp(1.25, 1.06, Ease.outCubic(clamp(u / 0.6))) + u * 0.025 });
        U.typeText(a.txt, PROMPT, T, 18.55, 29, T < 22.2);
        a.chips.forEach((c, i) => { const p = P(T, 18.6 + i * 0.07, 0.6, Ease.outQuint); S(c, { y: (1 - p) * 20, o: p }); });
        const press = T - 22.15;
        S(a.send, { s: press > 0 ? 1 - 0.14 * Math.exp(-press * 9) * Math.sin(Math.min(press * 18, Math.PI)) : 1 });
        const out = P(T, 22.3, 0.4, Ease.inCubic);
        S(a.card, { s: 1 - out * 0.35, o: 1 - out, y: -out * 80 });
        a.tip.style.opacity = String(1 - out);
      },
    });
  }

  // ---------- Construction, itération, publication ----------
  {
    const a = {};
    SCENES.push({
      id: 'build',
      build(root) {
        a.f = U.aurora(root, 'dark', 6);
        a.cam = el(root, { inset: '0', transformOrigin: '0 0' });
        const W = U.win(a.cam, 110, 70, 1700, 940, 'digicraft.leyton-cognitx.com/apps/client-portal');
        a.W = W;
        // Barre d'outils de l'éditeur.
        a.pub = el(W.bar, { right: '20px', top: '9px', height: '34px', padding: '0 18px', borderRadius: '11px', background: 'linear-gradient(90deg,#2E8BC0,#6D5DF6)', color: '#fff', fontSize: '15px', fontWeight: '700', display: 'flex', alignItems: 'center', gap: '8px' }, `${icon('rocket', 16, 2.2, '#fff')}Publish`);
        el(W.bar, { right: '170px', top: '13px', display: 'flex', gap: '18px' }, `${icon('monitor', 22, 2, '#64748B')}${icon('smartphone', 22, 2, '#94A3B8')}${icon('code-xml', 22, 2, '#94A3B8')}`);
        // Panneau de discussion.
        const chat = el(W.body, { left: '0', top: '0', bottom: '0', width: '480px', background: '#fff', borderRight: '1px solid #E5ECF2' });
        a.chat = chat;
        a.u1 = el(chat, { right: '24px', top: '26px', width: '380px', padding: '16px 18px', borderRadius: '18px 18px 4px 18px', background: '#EEF2FF', fontSize: '16px', lineHeight: '1.45', color: '#1E293B' }, PROMPT);
        a.u1.style.position = 'absolute';
        a.as1 = el(chat, { left: '24px', top: '172px', width: '420px' });
        a.as1.innerHTML = `<div style="display:flex;gap:10px;align-items:center;font-size:16px;font-weight:700;color:#0B2545">${window.dcIcon(26, 'ch', 'brand')}DigiCraft</div><div style="margin-top:10px;font-size:16px;line-height:1.5;color:#334155">Great idea. I'm building your client onboarding portal now.</div>`;
        a.steps = ['Creating the data model', 'Building client pages', 'Adding document upload', 'Setting up manager approval', 'Applying your company branding'].map((t, i) => {
          const r = el(chat, { left: '24px', top: 290 + i * 46 + 'px', display: 'flex', alignItems: 'center', gap: '12px', fontSize: '16px', color: '#475569' });
          const ic = h('span', '', r); Object.assign(ic.style, { width: '24px', height: '24px', borderRadius: '12px', display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none' });
          h('span', '', r, t);
          return { r, ic };
        });
        a.u2 = el(chat, { right: '24px', top: '548px', width: '380px', padding: '16px 18px', borderRadius: '18px 18px 4px 18px', background: '#EEF2FF', fontSize: '16px', lineHeight: '1.45', color: '#1E293B' }, PROMPT2);
        a.as2 = el(chat, { left: '24px', top: '650px', width: '420px', fontSize: '16px', lineHeight: '1.5', color: '#334155' }, `<div style="display:flex;gap:10px;align-items:center;font-size:16px;font-weight:700;color:#0B2545">${window.dcIcon(26, 'ch2', 'brand')}DigiCraft</div><div style="margin-top:10px">Done. Managers now see onboarding status for every client.</div>`);
        a.inp = el(chat, { left: '20px', right: '20px', bottom: '20px', height: '108px', borderRadius: '18px', border: '1.5px solid #DCE5EE', background: '#fff' });
        a.inT = el(a.inp, { left: '18px', top: '16px', right: '70px', fontSize: '17px', lineHeight: '1.45', color: '#0B2545' });
        a.inS = el(a.inp, { right: '14px', bottom: '14px', width: '44px', height: '44px', borderRadius: '22px', background: 'linear-gradient(135deg,#2E8BC0,#6D5DF6)', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('arrow-up-right', 22, 2.6, '#fff'));
        // Aperçu.
        a.pv = el(W.body, { left: '480px', top: '0', right: '0', bottom: '0', background: '#F1F5F9', overflow: 'hidden' });
        a.load = el(a.pv, { inset: '0', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '22px' });
        a.orb = el(a.load, { width: '120px', height: '120px', borderRadius: '50%', background: 'radial-gradient(circle at 35% 30%, #E2D4FF, #6D5DF6 45%, #2E8BC0 100%)', boxShadow: '0 0 80px rgba(109,93,246,0.55)', position: 'relative' });
        h('div', 'ui', a.load, 'Creating your app').style.cssText = 'font-size:26px;font-weight:700;color:#0B2545';
        a.loadSub = h('div', 'ui', a.load, ''); a.loadSub.style.cssText = 'font-size:17px;color:#64748B';
        a.portal = portal(a.pv, false);
        a.pk = (1700 - 480) / 1180;
        a.portal.v.style.transform = `scale(${a.pk})`;
        a.toast = el(a.cam, { left: '660px', top: '880px', height: '64px', padding: '0 26px', borderRadius: '20px', background: '#022446', color: '#fff', display: 'flex', alignItems: 'center', gap: '12px', fontFamily: 'DM Sans', fontSize: '19px', fontWeight: '600', boxShadow: '0 20px 50px rgba(1,36,70,0.35)' }, `${icon('circle-check', 24, 2.4, '#3DD68C')}Live for your team · client-portal.digicraft.app`);
        a.cur = U.cursor(a.cam);
      },
      update(T) {
        a.f(T, 0.7);
        // Apparition de la fenêtre.
        const wp = P(T, 22.32, 0.6, Ease.outQuint);
        S(a.W.r, { s: lerp(0.82, 1, wp), o: wp, y: (1 - wp) * 60 });
        // Caméra : plan large, zoom sur la saisie, retour, petit zoom sur Publier.
        const zin = P(T, 24.55, 0.55, Ease.inOutCubic) * (1 - P(T, 26.35, 0.6, Ease.inOutCubic));
        const zp = P(T, 27.3, 0.6, Ease.inOutCubic) * (1 - P(T, 28.3, 0.35, Ease.inCubic));
        const Z = lerp(1, 2.1, zin) * lerp(1, 1.35, zp);
        const fx = lerp(lerp(960, 360, zin), 1725, zp), fy = lerp(lerp(540, 930, zin), 96, zp);
        const dx = lerp(lerp(960, 820, zin), 1250, zp), dy = lerp(lerp(540, 600, zin), 330, zp);
        const Zb = Z * (1 + (T - 22.3) * 0.006);
        S(a.cam, { x: dx - Zb * fx, y: dy - Zb * fy, s: Zb });
        // Discussion.
        S(a.u1, { o: P(T, 22.5, 0.3), y: (1 - P(T, 22.5, 0.5, Ease.outQuint)) * 20 });
        S(a.as1, { o: P(T, 22.8, 0.3), y: (1 - P(T, 22.8, 0.5, Ease.outQuint)) * 20 });
        a.steps.forEach((s, i) => {
          const t = 23.0 + i * 0.32;
          const on = T > t + 0.25;
          s.r.style.opacity = String(P(T, t, 0.25));
          s.ic.style.background = on ? '#16A34A' : 'transparent';
          s.ic.innerHTML = on ? icon('check', 15, 3, '#fff') : `<span style="display:block;width:18px;height:18px;border-radius:9px;border:2.5px solid #CBD5E1;border-top-color:#6D5DF6;transform:rotate(${(T * 720) % 360}deg)"></span>`;
        });
        const pr2 = PROMPT2;
        if (T < 26.35) U.typeText(a.inT, pr2, T, 24.95, 40, T > 24.6);
        else a.inT.innerHTML = '<span style="color:#94A3B8">Ask DigiCraft to change anything…</span>';
        if (T < 24.6) a.inT.innerHTML = '<span style="color:#94A3B8">Ask DigiCraft to change anything…</span>';
        const sp = T - 26.3;
        S(a.inS, { s: sp > 0 && sp < 0.5 ? 1 - 0.15 * Math.sin(sp * 2 * Math.PI) : 1 });
        S(a.u2, { o: P(T, 26.4, 0.3), y: (1 - P(T, 26.4, 0.5, Ease.outQuint)) * 20 });
        S(a.as2, { o: P(T, 26.9, 0.3), y: (1 - P(T, 26.9, 0.5, Ease.outQuint)) * 20 });
        // Aperçu : chargement puis portail, puis tableau de bord ajouté.
        const lp = P(T, 23.75, 0.35);
        a.load.style.opacity = String(P(T, 22.6, 0.3) * (1 - lp));
        S(a.orb, { s: 1 + 0.08 * Math.sin(T * 6), r: T * 90 });
        a.loadSub.textContent = ['Designing screens…', 'Wiring the data…', 'Almost there…'][Math.min(2, Math.max(0, Math.floor((T - 22.6) / 0.4)))];
        a.portal.v.style.opacity = String(lp);
        a.portal.cards.forEach((c, i) => { const p = P(T, 23.8 + i * 0.06, 0.6, Ease.outQuint); S(c, { y: (1 - p) * 40, o: p }); });
        const dp = P(T, 26.75, 0.6, Ease.outQuint);
        a.portal.dk.style.display = dp > 0 ? 'block' : 'none';
        a.portal.grid.style.top = lerp(22, 162, dp) + 'px';
        a.portal.kp.forEach((k, i) => { const p = P(T, 26.8 + i * 0.07, 0.55, Ease.outBack); S(k, { s: Math.max(0.001, p), o: clamp(p) }); });
        // Curseur et publication.
        const cp = P(T, 27.25, 0.65, Ease.inOutCubic);
        const cx = lerp(1180, 1722, cp), cy = lerp(700, 90, cp);
        const click = T - 27.95;
        S(a.cur, { x: cx, y: cy, o: P(T, 27.2, 0.2) * (1 - P(T, 28.45, 0.15)), s: click > 0 && click < 0.25 ? 0.85 : 1 });
        const done = T > 28.0;
        a.pub.innerHTML = done ? `${icon('check', 16, 2.6, '#fff')}Published` : `${icon('rocket', 16, 2.2, '#fff')}Publish`;
        a.pub.style.background = done ? '#16A34A' : 'linear-gradient(90deg,#2E8BC0,#6D5DF6)';
        S(a.pub, { s: click > 0 ? 1 + 0.12 * Math.exp(-click * 8) : 1 });
        const tp = P(T, 28.05, 0.45, Ease.outBack);
        S(a.toast, { y: (1 - tp) * 120, o: clamp(tp) });
      },
    });
  }

  // ---------- Tablette ----------
  {
    const a = {};
    SCENES.push({
      id: 'tablet',
      build(root) {
        a.root = root;
        a.f = U.scenery(root, 'blur_sky', { drift: 0.5 });
        a.tb = U.tablet(root, 960, 560, 1240);
        const p = portal(a.tb.screen, true);
        p.v.style.transform = `scale(${(1240 * 0.94) / 1180})`;
        a.p = p;
      },
      update(T) {
        E.slash(T, 28.42, 0.5, a.root, a.bands || (a.bands = E.slashBands(a.root, C.BANDS.peach)));
        a.f(T, 0.6);
        const u = T - 28.5;
        S(a.tb.g, { r: lerp(-5, 1, Ease.outCubic(clamp(u / 1.6))), s: lerp(0.9, 1.0, Ease.outCubic(clamp(u / 1.6))), y: -u * 14 });
        a.root.style.filter = T > 29.85 ? `blur(${((T - 29.85) * 40).toFixed(1)}px)` : 'none';
      },
    });
  }
})();

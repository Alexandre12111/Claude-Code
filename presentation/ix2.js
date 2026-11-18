/* ===== v1.6 : couverture animée, cas d'usage cliquables, simulateur de budget, vue d'ensemble, assistant ===== */
(function () {
  'use strict';
  var tr = window.__ycTr, nf = window.__ycNf, eur = window.__ycEur;
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function slides() { return $$('.slide'); }
  function goId(id) { var i = slides().indexOf(document.getElementById(id)); if (i >= 0 && window.__ycGo) window.__ycGo(i); }
  function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;'); }
  function fill(t, o) { return t.replace(/\{(\w+)\}/g, function (_, k) { return o[k]; }); }
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var OPEN = null;
  function openPop(p, focusSel) { closePop(); p.classList.add('on'); OPEN = p; var f = $(focusSel || '.x', p); if (f) f.focus(); }
  function closePop() { if (OPEN) { OPEN.classList.remove('on'); OPEN = null; } }
  window.__ycPopOpen = function () { return !!OPEN || ($('.db-dr.on') !== null); };
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { if (OPEN) { closePop(); e.stopImmediatePropagation(); } var dr = $('.db-dr.on'); if (dr) dr.classList.remove('on'); return; }
    var tg = e.target; if (tg && (tg.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(tg.tagName))) return;
    if ((e.key === 'o' || e.key === 'O' || e.key === '?') && !e.ctrlKey && !e.metaKey) { e.preventDefault(); toggleOv(); }
  }, true);
  $$('.pop').forEach(function (p) { p.addEventListener('click', function (e) { if (e.target === p || e.target.classList.contains('x')) closePop(); }); });

  /* ------------------------------------------------ couverture : l'aperçu produit se construit en boucle */
  var hv = $('#s1 .hero-v');
  if (hv) {
    var APPS = [
      ['Build a CEO dashboard with revenue, margin and cash by region', 'CEO dashboard', [['Revenue', function () { return eur(118.8, 1, 'M'); }], ['Gross margin', function () { return nf(32.4, 1) + ' %'; }], ['Cash', function () { return eur(12.8, 1, 'M'); }]], [48, 62, 55, 74, 68, 90, 82], 'ceo-dashboard'],
      ['Build an expense approval app with a manager review step.', 'Expense approvals', [['Pending', function () { return nf(12); }], ['Approved', function () { return nf(148); }], ['Spent', function () { return eur(18.4, 1, 'k'); }]], [45, 62, 50, 78, 66, 92, 70], 'expenses'],
      ['Build a client onboarding tracker with reminders and a dashboard', 'Client onboarding tracker', [['Active clients', function () { return nf(86); }], ['Due this week', function () { return nf(9); }], ['Completed', function () { return nf(94) + ' %'; }]], [30, 44, 52, 61, 70, 76, 88], 'onboarding']];
    var tx = $('.tx', hv), aw = $('.aw', hv), k = 0, tm = [];
    function later(f, ms) { tm.push(setTimeout(f, ms)); }
    function show(a) {
      $('.u span', aw).textContent = a[4] + '.acme.youcraft.build';
      $('h6', aw).textContent = tr(a[1]);
      $$('.k3 div', aw).forEach(function (d, i) { d.querySelector('small').textContent = tr(a[2][i][0]); d.querySelector('b').textContent = a[2][i][1](); });
      $$('.cb i', aw).forEach(function (b, i) { b.style.height = a[3][i] + '%'; });
    }
    function cycle() {
      tm.forEach(clearTimeout); tm = [];
      if (!$('#s1').classList.contains('active')) return;
      var a = APPS[k % APPS.length], full = tr(a[0]), i = 0;
      if (reduce) { tx.textContent = full; show(a); return; }
      tx.textContent = ''; tx.classList.add('typing'); aw.classList.add('gen');
      $$('.cb i', aw).forEach(function (b) { b.style.height = '8%'; });
      (function step() { i++; tx.textContent = full.slice(0, i); if (i < full.length) later(step, 30); else later(function () { tx.classList.remove('typing'); show(a); aw.classList.remove('gen'); later(function () { k++; cycle(); }, 4200); }, 500); })();
    }
    window.__ycCoverEnter = function () { later(cycle, 900); };
    window.__ycCoverLeave = function () { tm.forEach(clearTimeout); tm = []; };
    hv.addEventListener('click', function () { goId('s3'); });
    hv.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); goId('s3'); } });
    document.addEventListener('yc-lang', function () { if ($('#s1').classList.contains('active')) { k = k % APPS.length; cycle(); } });
    show(APPS[0]); tx.textContent = tr(APPS[0][0]);
  }

  /* ------------------------------------------------ cas d'usage : un exemple d'application par équipe */
  var UC = [
    ['Operations', 'Operations control room', 'Track every open work order and alert me when one is late', [['Open orders', '128'], ['Late', '6'], ['On time', '95 %']], [['Line 3 · conveyor belt', 'Late', 'r'], ['Warehouse B · forklift', 'In progress', 'p'], ['Site Lyon · HVAC check', 'Done', 'a'], ['Line 1 · sensor', 'In progress', 'p']], ['Alerts by email and Teams', 'Weekly report generated automatically']],
    ['Sales & Marketing', 'Lead management', 'Score inbound leads and assign them to the right sales rep', [['New leads', '342'], ['Qualified', '118'], ['Conversion', '18 %']], [['Northwind Group · score 92', 'Hot', 'a'], ['Atlas Industries · score 74', 'Warm', 'p'], ['Orion Retail · score 61', 'Warm', 'p'], ['Vega Logistics · score 38', 'Cold', 'r']], ['Synced with your CRM', 'Automatic assignment rules']],
    ['Finance', 'Approval workflows', 'Route purchase requests to the right approver by amount', [['Pending', '24'], ['Approved', '312'], ['Average delay', '1.8 d']], [['IT equipment · €4,200', 'Manager', 'p'], ['Marketing event · €12,500', 'CFO', 'r'], ['Office supplies · €380', 'Approved', 'a'], ['Software licence · €1,150', 'Approved', 'a']], ['Approval thresholds by amount', 'Monthly export for accounting']],
    ['HR', 'Onboarding apps', 'Give each new hire a checklist, a buddy and a first-week plan', [['New hires', '18'], ['Ready for day one', '15'], ['Tasks done', '86 %']], [['Léa · Data analyst', 'Ready', 'a'], ['Hugo · Sales', 'Missing laptop', 'r'], ['Emma · Finance', 'Ready', 'a'], ['Noah · R&D', 'Contract to sign', 'p']], ['Reminders for managers', 'Buddy and checklist per new hire']],
    ['Legal & Compliance', 'Contract workflows', 'Track contracts, renewal dates and risky clauses', [['Active contracts', '412'], ['Renewals in 90 days', '27'], ['High risk', '5']], [['Supplier A · renewal in 21 days', 'To review', 'p'], ['Client B · unlimited liability', 'High risk', 'r'], ['Partner C · NDA', 'Compliant', 'a'], ['Lease D · renewal in 60 days', 'To review', 'p']], ['AI clause analysis', 'Renewal alerts']],
    ['Innovation & R&D', 'R&D project time tracking', 'Log time per R&D project and prepare the files for the research tax credit', [['Projects', '14'], ['Hours this month', '2,340'], ['Eligible share', '72 %']], [['Project Atlas · 410 h', 'Eligible', 'a'], ['Project Nova · 265 h', 'To qualify', 'p'], ['Project Helios · 188 h', 'Eligible', 'a'], ['Project Vega · 96 h', 'Missing proof', 'r']], ['Timesheets per researcher', 'Export ready for tax credit files']],
  ];
  var ucp = $('#ucp'), cur = 0;
  function num(s) { var m = /^([\d.,]+)(.*)$/.exec(s); if (!m) return tr(s); var v = parseFloat(m[1].replace(',', '')); var dec = /\./.test(m[1]) ? 1 : 0; return nf(v, dec) + tr(m[2]); }
  function rowTxt(s) { s = s.replace(/€([\d,]+)/, function (_, v) { return eur(parseFloat(v.replace(/,/g, '')), 0); }); return s.split(' · ').map(function (x) { return tr(x); }).join(' · '); }
  function renderUc() {
    var u = UC[cur], st = { a: 'background:#EFF7CF;color:#4E5A06', p: 'background:#F4E9CE;color:#8A5F00', r: 'background:#FBE3DF;color:#A3361F' };
    $('h3', ucp).textContent = tr(u[0]);
    $('.sub3', ucp).textContent = tr('Example application, built from one prompt');
    $('.ucp-l .q', ucp).innerHTML = '<small>' + esc(tr('The prompt')) + '</small>“' + esc(tr(u[2])) + '”';
    $('.ucp-l .feat', ucp).innerHTML = u[5].map(function (f) { return '<span>' + esc(tr(f)) + '</span>'; }).join('');
    $('.ucp-a .u span', ucp).textContent = u[1].toLowerCase().replace(/[^a-z]+/g, '-').replace(/-$/, '') + '.acme.youcraft.build';
    $('.ucp-a h6', ucp).textContent = tr(u[1]);
    $('.ucp-a .k3', ucp).innerHTML = u[3].map(function (k) { return '<div><small>' + esc(tr(k[0])) + '</small><b>' + esc(num(k[1])) + '</b></div>'; }).join('');
    $('.ucp-a .rows', ucp).innerHTML = u[4].map(function (r) { return '<div><span>' + esc(rowTxt(r[0])) + '</span><span class="st" style="' + st[r[2]] + '">' + esc(tr(r[1])) + '</span></div>'; }).join('');
    $('.ucp-nav', ucp).innerHTML = UC.map(function (x, i) { return '<button data-i="' + i + '" class="' + (i === cur ? 'on' : '') + '">' + esc(tr(x[0])) + '</button>'; }).join('');
    $$('.ucp-nav button', ucp).forEach(function (b) { b.onclick = function () { cur = +b.dataset.i; renderUc(); }; });
  }
  $$('#s7 .uc').forEach(function (c, i) {
    c.setAttribute('tabindex', '0'); c.setAttribute('role', 'button');
    var m = document.createElement('div'); m.className = 'more'; m.innerHTML = esc(tr('See an example app')) + ' →'; c.appendChild(m);
    function op() { cur = i; renderUc(); openPop(ucp); }
    c.addEventListener('click', op); c.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); e.stopPropagation(); op(); } });
  });
  if (ucp) ucp.addEventListener('keydown', function (e) { if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); e.stopPropagation(); cur = (cur + (e.key === 'ArrowRight' ? 1 : UC.length - 1)) % UC.length; renderUc(); } });

  /* ------------------------------------------------ simulateur de budget (tarifs de la diapositive) */
  var sim = $('#sim'), PL = [['Starter', 25, 100], ['Business', 50, 300], ['Sovereign', 100, 500]], P = 1, N = 10;
  function renderSim() {
    var p = PL[P], m = p[1] * N, y = m * 12, r = $('.sim-r', sim);
    $$('.sim-seg button', sim).forEach(function (b, i) { b.classList.toggle('on', i === P); });
    $('.sim-n b', sim).textContent = nf(N);
    r.value = N; r.style.setProperty('--p', ((N - 1) / 59 * 100) + '%');
    $('.big2', sim).textContent = eur(m, 0);
    $('.v1', sim).textContent = eur(y, 0); $('.v2', sim).textContent = nf(p[2] * N);
    var tip = $('.sim-tip', sim), lim = Math.ceil(12000 / (p[1] * 12));
    tip.classList.toggle('on', y >= 12000);
    tip.textContent = fill(tr('From {n} users on {plan}, Enterprise (from €12K per year) becomes worth comparing: any LLM, audit logs and a dedicated CSM.'), { n: nf(lim), plan: tr(p[0]) });
  }
  if (sim) {
    $$('.sim-seg button', sim).forEach(function (b, i) { b.onclick = function () { P = i; renderSim(); }; });
    $('.sim-r', sim).addEventListener('input', function (e) { N = +e.target.value; renderSim(); });
    $('.sim-open').addEventListener('click', function () { renderSim(); openPop(sim, '.sim-r'); });
    $$('#s12 .plan').forEach(function (c, i) { c.setAttribute('tabindex', '0'); c.addEventListener('click', function () { if (i < 3) { P = i; } renderSim(); openPop(sim, '.sim-r'); }); });
    document.addEventListener('yc-lang', function () { if (OPEN === sim) renderSim(); if (OPEN === ucp) renderUc(); });
  }

  /* ------------------------------------------------ vue d'ensemble des diapositives (touche O) */
  var ov = $('#ov');
  function toggleOv() {
    if (OPEN === ov) { closePop(); return; }
    var act = slides().indexOf($('.slide.active'));
    $('.ovg', ov).innerHTML = slides().map(function (s, i) { var h = s.querySelector('h2, .big, .tag'); var t = s.id === 's1' ? 'YouCraft' : (h ? h.innerHTML.replace(/<br\s*\/?>/gi, ' ').replace(/<[^>]+>/g, '').replace(/&amp;/g, '&').replace(/\s+/g, ' ').trim() : ''); return '<button data-i="' + i + '" class="' + (i === act ? 'cur' : '') + '"><small>' + (i + 1 < 10 ? '0' : '') + (i + 1) + '</small>' + esc(t) + '</button>'; }).join('');
    $$('.ovg button', ov).forEach(function (b) { b.onclick = function () { closePop(); window.__ycGo(+b.dataset.i); }; });
    openPop(ov, '.ovg button.cur');
  }
  var ob = $('#ovbtn'); if (ob) ob.addEventListener('click', toggleOv);

  /* ------------------------------------------------ assistant « Interroger les données » */
  var dr = $('.db-dr');
  if (dr && window.__ycDash) {
    var Dh = window.__ycDash, th = $('.th', dr);
    var Q = ['Which months were below target?', 'What is the revenue trend?', 'Which departments are over budget?', 'What are the top 3 deals?'];
    function answer(i) {
      var d = Dh.series(), S = Dh.S, reg = tr(Dh.REG[S.reg][0]), per = fill(tr('{n} months'), { n: S.per });
      if (i === 0) { var low = d.m.filter(function (m, k) { return d.rev[k] < d.tgt[k]; }); return low.length ? fill(tr('{reg}, last {per}: revenue was below target in <b>{n}</b> months ({list}).'), { reg: reg, per: per, n: low.length, list: low.join(', ') }) : fill(tr('{reg}, last {per}: revenue met the target every month.'), { reg: reg, per: per }); }
      if (i === 1) { var a = d.rev[0], b = d.rev[d.rev.length - 1], g = (b / a - 1) * 100, tot = d.rev.reduce(function (x, y) { return x + y; }, 0); return fill(tr('{reg}, last {per}: revenue totals <b>{tot}</b>, and monthly revenue moved by <b>{g}</b> between the first and last month.'), { reg: reg, per: per, tot: eur(tot, 1, 'M'), g: (g >= 0 ? '+' : '') + nf(g, 1) + ' %' }); }
      if (i === 2) { var k = Dh.REG[S.reg][1] * 2.2 * S.per / 12; var over = Dh.DEP.filter(function (x) { return x[2] > x[1]; }).map(function (x) { return tr(x[0]) + ' (+' + eur((x[2] - x[1]) * k, 2, 'M') + ')'; }); return fill(tr('<b>{n}</b> departments are over budget: {list}. The others are within budget.'), { n: over.length, list: over.join(', ') }); }
      var kk = Dh.REG[S.reg][1] * 2.2, top = Dh.DEALS.slice().sort(function (x, y) { return y[2] - x[2]; }).slice(0, 3).map(function (x) { return x[0] + ' · ' + eur(Math.round(x[2] * kk), 0, 'k') + ' (' + tr(x[1]) + ')'; });
      return fill(tr('Top 3 deals: {list}.'), { list: top.join(' ; ') });
    }
    function ask(i) {
      var me = document.createElement('div'); me.className = 'me'; me.textContent = tr(Q[i]); th.appendChild(me);
      var ai = document.createElement('div'); ai.className = 'ai typing'; th.appendChild(ai);
      while (th.children.length > 6) th.removeChild(th.firstChild);
      var html = answer(i), plain = html.replace(/<[^>]+>/g, ''), n = 0;
      if (reduce) { ai.innerHTML = html; ai.classList.remove('typing'); return; }
      (function step() { n += 3; ai.textContent = plain.slice(0, n); if (n < plain.length) setTimeout(step, 16); else { ai.innerHTML = html; ai.classList.remove('typing'); } })();
    }
    function sugg() { $('.sg', dr).innerHTML = Q.map(function (q, i) { return '<button data-q="' + i + '">' + esc(tr(q)) + '</button>'; }).join(''); $$('.sg button', dr).forEach(function (b) { b.onclick = function () { ask(+b.dataset.q); }; }); }
    window.__ycAskOpen = function () { sugg(); dr.classList.add('on'); var f = $('.sg button', dr); if (f) f.focus(); };
    $('.cl', dr).onclick = function () { dr.classList.remove('on'); };
    document.addEventListener('yc-lang', sugg);
    window.__ycDashLeave = function () { dr.classList.remove('on'); th.innerHTML = ''; };
  }
})();

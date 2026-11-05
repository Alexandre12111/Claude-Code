/* ===== v1.7 : builder (slide 4), espace applications (slide 8), mosaïque (slide 5), mode lecture mobile ===== */
(function () {
  'use strict';
  function tr(s) { return window.__ycTr ? window.__ycTr(s) : s; }
  function $(s, r) { return (r || document).querySelector(s); }
  function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
  function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;'); }
  var ic = YA.ic;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function goId(id) { var i = $$('.slide').indexOf(document.getElementById(id)); if (i >= 0 && window.__ycGo) window.__ycGo(i); }
  var INFO = {
    people: { p: 'Build an onboarding hub for new hires with a checklist, a buddy and a manager view', t: 'Onboarding hub', d: 'HR · new hires', c: '#1E3BFF', i: 'users',
      steps: [['Data model', 'Employees, tasks, buddies'], ['Screens and navigation', '4 screens'], ['Business logic and automations', 'Reminders, alerts'], ['Access and roles', 'HR, managers, new hires']] },
    tower: { p: 'Create a control tower to track our trucks live and flag late deliveries', t: 'Control tower', d: 'Logistics · live map', c: '#0B0B0F', i: 'map-pin',
      steps: [['Data model', 'Trucks, routes, stops'], ['Screens and navigation', 'Live map, incidents'], ['Business logic and automations', 'Delay detection'], ['Access and roles', 'Dispatch, carriers']] },
    assets: { p: 'Monitor our machine sensors and open a work order when vibration is too high', t: 'Machine monitoring', d: 'Industry · sensors', c: '#1E3BFF', i: 'gauge',
      steps: [['Data model', 'Machines, sensors, work orders'], ['Screens and navigation', 'Live charts'], ['Business logic and automations', 'Thresholds, work orders'], ['Access and roles', 'Technicians, managers']] },
    portal: { p: 'Build a client portal to collect onboarding documents and validate each client', t: 'Client portal', d: 'Consulting · documents', c: '#1E3BFF', i: 'building-2',
      steps: [['Data model', 'Clients, contacts, documents'], ['Screens and navigation', 'Portal, checklist'], ['Business logic and automations', 'Reminders, validation'], ['Access and roles', 'Advisers, clients']] },
  };
  var ORDER = ['people', 'tower', 'assets', 'portal'];
  function route(text) { var t = text.toLowerCase(); if (/truck|fleet|deliver|logist|camion|livrais|transport|flotte/.test(t)) return 'tower'; if (/machine|sensor|maintenance|vibration|factory|usine|capteur|plant|équipement|equipment/.test(t)) return 'assets'; if (/client|portal|customer|portail|document|kyc/.test(t)) return 'portal'; return 'people'; }

  /* ------------------------------------------------ builder (slide 4) */
  var B = $('#s3 .bld');
  if (B) {
    var st = { app: null, kind: null, busy: false, pub: false, dev: 'desk', auto: false, alert: false, msgs: [] };
    var msgs = $('.bld-msgs', B), sg = $('.bld-sg', B), ta = $('.bld-cmp textarea', B), snd = $('.bld-cmp .snd', B), pv = $('.bld-pv', B), fr = $('.bld-frame', B), host = $('.bld-host', B), emp = $('.bld-empty', B), ph = $('.bld-ph', B), tst = $('.bld-toast', B);
    var tm = [];
    function later(f, ms) { tm.push(setTimeout(f, ms)); }
    function layout() {
      var W = pv.clientWidth, H = pv.clientHeight, m = 28;
      function box(l, t, w, h) { fr.style.left = l + 'px'; fr.style.top = t + 'px'; fr.style.width = w + 'px'; fr.style.height = (h + 36) + 'px'; host.style.width = w + 'px'; host.style.height = h + 'px'; }
      if (window.__ycFlow) { box(10, 10, W - 20, H - 20 - 36); }
      else { box(18, 18, W - 36, H - 36 - 36); }
      if (st.app) st.app.fit();
      var pk = Math.min(1, (H - 40) / 800); ph.style.transform = 'scale(' + pk + ')'; ph.style.left = Math.round((W - 390 * pk) / 2) + 'px'; ph.style.top = Math.round((H - 800 * pk) / 2) + 'px';
    }
    function toast(t) { tst.innerHTML = '<i>' + ic('check', 12, '#fff', 3) + '</i>' + t; tst.classList.add('on'); clearTimeout(tst._t); tst._t = setTimeout(function () { tst.classList.remove('on'); }, 2600); }
    function add(cls, html) { var d = document.createElement('div'); d.className = 'bm ' + cls; d.innerHTML = html; msgs.appendChild(d); while (msgs.children.length > 6) msgs.removeChild(msgs.firstChild); return d; }
    function aiHead() { return '<div class="who"><i>' + ic('sparkles', 13, '#D7FF3F', 2.2) + '</i>YouCraft</div>'; }
    function chips() {
      var L = [];
      if (st.kind) {
        L.push(['mob', st.dev === 'mob' ? 'Back to desktop view' : 'Show it on mobile']);
        if (st.kind === 'people' && !st.alert) L.push(['alert', 'Add an alert when a contract is not signed']);
        if (!st.pub) L.push(['pub', 'Publish to my team']);
      }
      ORDER.forEach(function (k) { if (k !== st.kind) L.push(['b:' + k, INFO[k].p]); });
      sg.innerHTML = L.slice(0, st.kind ? 4 : 3).map(function (c) { return '<button data-c="' + c[0] + '" class="' + (c[0].indexOf('b:') ? 'next' : '') + '">' + esc(tr(c[1])) + '</button>'; }).join('');
      $$('button', sg).forEach(function (b) { b.onclick = function () { var c = b.dataset.c; if (c.indexOf('b:') === 0) send(tr(INFO[c.slice(2)].p), c.slice(2)); else action(c); }; });
    }
    function setDev(d) { st.dev = d; $$('.bld-dev button', B).forEach(function (b) { b.classList.toggle('on', b.dataset.d === d); }); var mob = d === 'mob' && st.app; fr.style.opacity = mob ? '0' : '1'; fr.style.visibility = (mob || !st.app && st.dev === 'desk' && !st.busy && !st.kind) ? (mob ? 'hidden' : fr.style.visibility) : 'visible'; ph.style.display = mob ? 'block' : 'none'; if (mob) { YA.mobile(ph, st.app); layout(); } chips(); }
    function action(c) {
      if (st.busy) return;
      if (c === 'mob') { var to = st.dev === 'mob' ? 'desk' : 'mob'; add('me', esc(tr(to === 'mob' ? 'Show it on mobile' : 'Back to desktop view'))); later(function () { add('ai', aiHead() + esc(tr(to === 'mob' ? 'Here is the same app on a phone. Nothing to rebuild: every screen adapts automatically.' : 'Back to the desktop view.'))); setDev(to); }, 500); return; }
      if (c === 'alert') { add('me', esc(tr('Add an alert when a contract is not signed'))); st.busy = true; var d = add('ai', aiHead() + esc(tr('Adding an alert rule…'))); later(function () { d.innerHTML = aiHead() + esc(tr('Done. If a contract is still unsigned 3 days before the start date, the manager gets an email and a Teams message.')) + '<div class="res">' + ic('bell', 14, '#1E3BFF') + ' <b>' + esc(tr('New alert rule')) + '</b> · ' + esc(tr('visible in the app')) + '</div>'; st.alert = true; if (st.app && st.app.addAlert) st.app.addAlert(); if (st.dev === 'mob') setDev('desk'); st.busy = false; chips(); }, 1300); return; }
      if (c === 'pub') publish();
    }
    function publish() {
      if (!st.app || st.pub) return; st.pub = true;
      var b = $('.bld-pub', B); b.classList.add('done'); b.innerHTML = ic('check', 16, '#fff', 3) + esc(tr('Published'));
      fr.classList.add('live'); add('me', esc(tr('Publish to my team')));
      later(function () { add('ai', aiHead() + esc(tr('Your app is live. Your team can open it now, on desktop or mobile, with their usual login.'))); toast(esc(tr('Published')) + ' · ' + st.app.url.split('/')[0]); chips(); }, 600);
    }
    function send(text, kind) {
      if (st.busy) return; text = text.trim(); if (!text) return;
      tm.forEach(clearTimeout); tm = []; st.busy = true; st.pub = false; st.alert = false;
      kind = kind || route(text); var I = INFO[kind];
      ta.value = ''; snd.disabled = true;
      var wl = $('.welcome', msgs); if (wl) wl.remove();
      add('me', esc(text));
      var d = add('ai', aiHead() + esc(tr('On it. Here is what I am building:')) + I.steps.map(function (s) { return '<div class="stp"><i></i>' + esc(tr(s[0])) + '<small>' + esc(tr(s[1])) + '</small></div>'; }).join(''));
      var sp = $$('.stp', d);
      emp.style.opacity = '0'; emp.style.visibility = 'hidden'; fr.style.opacity = '1'; fr.style.visibility = 'visible'; fr.classList.add('gen'); fr.classList.remove('live'); setDev('desk');
      var b = $('.bld-pub', B); b.classList.remove('done'); b.innerHTML = ic('rocket', 16, '#fff') + esc(tr('Publish'));
      $('.bld-top .prj b', B).textContent = YA.DEF[kind][1]; $('.bld-top .prj small', B).textContent = tr('Draft · just now');
      $('.url span', fr).textContent = YA.DEF[kind][4];
      if (st.app) { st.app.destroy(); st.app = null; }
      var step = reduce ? 80 : 700;
      sp.forEach(function (s, i) { later(function () { s.classList.add('run'); }, 200 + i * step); later(function () { s.classList.remove('run'); s.classList.add('ok'); s.querySelector('i').innerHTML = ic('check', 11, '#fff', 3); }, 200 + (i + 1) * step - 80); });
      later(function () {
        st.app = YA.create(kind, host); st.kind = kind; layout(); st.app.root.style.opacity = '0'; st.app.root.style.transition = 'opacity .5s';
        requestAnimationFrame(function () { st.app.root.style.opacity = '1'; }); fr.classList.remove('gen');
        if ($('#s3').classList.contains('active') || window.__ycFlow) st.app.start();
        d.insertAdjacentHTML('beforeend', '<div class="res">' + ic('circle-check', 14, '#1C8C4E') + ' <b>' + esc(YA.DEF[kind][1]) + '</b> ' + esc(tr('is ready. It is live on the right: click around, everything works.')) + '</div>');
        st.busy = false; snd.disabled = !ta.value.trim(); chips();
      }, 200 + sp.length * step + 200);
    }
    ta.addEventListener('input', function () { snd.disabled = !ta.value.trim() || st.busy; });
    ta.addEventListener('keydown', function (e) { e.stopPropagation(); if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(ta.value); } });
    snd.onclick = function () { send(ta.value); };
    $$('.bld-tpl button', B).forEach(function (b) { b.onclick = function () { st.auto = true; send(tr(INFO[b.dataset.k].p), b.dataset.k); }; });
    $$('.bld-dev button', B).forEach(function (b) { b.onclick = function () { if (!st.app) return; if (b.dataset.d !== st.dev) action('mob'); }; });
    $('.bld-pub', B).onclick = function () { if (st.app) publish(); };
    $('.bld-share', B).onclick = function () { toast(esc(tr('Link copied · read-only access'))); };
    function typeInto(text, done) { if (reduce) { ta.value = text; done(); return; } var i = 0; (function f() { if (st.auto) return; ta.value = text.slice(0, ++i); snd.disabled = false; if (i < text.length) later(f, 22); else later(done, 450); })(); }
    window.__ycBuilderEnter = function () { layout(); if (st.app) st.app.start(); if (!st.auto && !st.kind) { later(function () { if (st.auto || st.kind) return; typeInto(tr(INFO.people.p), function () { if (!st.auto && !st.kind) send(ta.value, 'people'); }); }, 900); } };
    window.__ycBuilderLeave = function () { if (st.app) st.app.stop(); };
    ta.addEventListener('focus', function () { st.auto = true; });
    window.addEventListener('resize', layout);
    document.addEventListener('yc-lang', function () { chips(); if (!st.kind) ta.placeholder = tr('Describe the app you need…'); });
    ta.placeholder = tr('Describe the app you need…');
    chips(); layout(); ph.style.display = 'none';
  }

  /* ------------------------------------------------ espace applications (slide 8) */
  var WS = $('#s6b .ws');
  if (WS) {
    var cur = null, appW = null, wh = $('.ws-host', WS);
    function show(k) {
      if (cur === k) return; cur = k;
      $$('.ws-dock button', WS).forEach(function (b) { b.classList.toggle('on', b.dataset.k === k); });
      if (appW) appW.destroy();
      appW = YA.create(k, wh); $('.ws-fr .url span', WS).textContent = YA.DEF[k][4];
      if ($('#s6b').classList.contains('active') || window.__ycFlow) appW.start();
    }
    $$('.ws-dock button', WS).forEach(function (b) { b.onclick = function () { show(b.dataset.k); }; });
    window.__ycWsShow = function (k) { show(k); };
    window.__ycWsEnter = function () { if (!appW) show('people'); appW.fit(); appW.start(); };
    window.__ycWsLeave = function () { if (appW) appW.stop(); };
    show('people'); appW.stop();
  }

  /* ------------------------------------------------ mosaïque (slide 5) */
  $$('#s4 .bt').forEach(function (t) {
    function go() { var k = t.dataset.go; if (k === 'builder') goId('s3'); else if (k === 'expenses') goId('s6'); else { goId('s6b'); if (window.__ycWsShow) window.__ycWsShow(k); } }
    t.addEventListener('click', go); t.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); e.stopPropagation(); go(); } });
  });

  /* ------------------------------------------------ mode lecture (téléphones et écrans étroits) */
  function isFlow() { return window.innerWidth < 900 || (window.innerWidth < 1200 && window.innerHeight > window.innerWidth * 1.05); }
  var io = null;
  function setFlow() {
    var f = isFlow(), was = !!window.__ycFlow; window.__ycFlow = f;
    document.documentElement.classList.toggle('ycflow', f);
    if (f) {
      var dm = document.getElementById('demo'); if (dm) dm.classList.add('built');
      if (!io && 'IntersectionObserver' in window) {
        io = new IntersectionObserver(function (es) { es.forEach(function (e) { var id = e.target.id, on = e.isIntersecting;
          if (on) e.target.classList.add('active');
          if (id === 's3') { if (on) window.__ycBuilderEnter && window.__ycBuilderEnter(); else window.__ycBuilderLeave && window.__ycBuilderLeave(); }
          if (id === 's6b') { if (on) window.__ycWsEnter && window.__ycWsEnter(); else window.__ycWsLeave && window.__ycWsLeave(); }
          if (id === 's1' && on && window.__ycCoverEnter) window.__ycCoverEnter(); }); }, { threshold: 0.25 });
        $$('.slide').forEach(function (s) { io.observe(s); });
      }
    }
    if (f !== was) window.dispatchEvent(new Event('resize'));
  }
  window.__ycSetFlow = setFlow;
  setFlow();
  window.addEventListener('resize', function () { var f = isFlow(); if (f !== !!window.__ycFlow) setFlow(); });
})();

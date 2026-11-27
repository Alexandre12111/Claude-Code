/* YouCraft · présentation v2 : navigation, démonstration, applications interactives */
(function () {
  'use strict';
  var deck = document.getElementById('deck'), chrome = document.getElementById('chrome');
  var slides = Array.prototype.slice.call(document.querySelectorAll('section.yd')), N = slides.length, cur = -1;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var H = {};

  function fit() { var k = Math.min(window.innerWidth / 1920, window.innerHeight / 1080); deck.style.transform = 'translate(-50%,-50%) scale(' + k + ')'; }
  window.addEventListener('resize', fit); fit();

  /* ---------------------------------------------------------------- navigation */
  function go(i) {
    i = Math.max(0, Math.min(N - 1, i)); if (i === cur) return;
    var prev = slides[cur];
    if (prev) { prev.classList.remove('on'); var lk = prev.dataset.enter; if (lk && H[lk] && H[lk].leave) H[lk].leave(); }
    cur = i; var s = slides[i]; s.classList.add('on');
    var dark = s.classList.contains('dk');
    chrome.classList.toggle('dk', dark); document.body.classList.toggle('dk', dark);
    chrome.classList.toggle('cover', i === 0);
    $('#chrome .ch').innerHTML = (s.dataset.ch ? '<span>' + s.dataset.ch + '</span><i></i>' : '') + '<b>' + String(i + 1).padStart(2, '0') + '</b> / ' + String(N).padStart(2, '0');
    $('#prog').style.width = ((i + 1) / N * 100) + '%';
    $('#nav span').textContent = (i + 1) + ' / ' + N;
    $$('#menu li button').forEach(function (b, k) { b.classList.toggle('on', k === i); });
    if (history.replaceState) history.replaceState(null, '', '#' + (i + 1));
    var ek = s.dataset.enter; if (ek && H[ek] && H[ek].enter) H[ek].enter();
    count(s);
  }
  function goId(id) { var k = slides.indexOf(document.getElementById(id)); if (k >= 0) go(k); }
  window.__deckGo = go;

  document.addEventListener('keydown', function (e) {
    var t = e.target, tag = t && t.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || (t && t.isContentEditable)) return;
    if (e.metaKey || e.ctrlKey || e.altKey) return;
    if (FS.on) { if (e.key === 'Escape') closeFull(); return; }
    var m = $('#menu');
    if (e.key === 'Escape' && m.classList.contains('on')) { m.classList.remove('on'); return; }
    var ek0 = slides[cur] && slides[cur].dataset.enter, hs = ek0 && H[ek0] && H[ek0].step;
    if (hs && (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') && hs(1)) { e.preventDefault(); return; }
    if (hs && (e.key === 'ArrowLeft' || e.key === 'PageUp') && hs(-1)) { e.preventDefault(); return; }
    if (['ArrowRight', 'PageDown', ' ', 'Enter'].indexOf(e.key) >= 0 && !(e.key === 'Enter' && tag === 'BUTTON')) { e.preventDefault(); go(cur + 1); }
    else if (['ArrowLeft', 'PageUp', 'Backspace'].indexOf(e.key) >= 0) { e.preventDefault(); go(cur - 1); }
    else if (e.key === 'Home') go(0); else if (e.key === 'End') go(N - 1);
    else if (e.key === 'm' || e.key === 'M' || e.key === 'o' || e.key === 'O') m.classList.toggle('on');
    else if (e.key === 'f' || e.key === 'F') full();
  });
  function full() { if (!document.fullscreenElement) document.documentElement.requestFullscreen && document.documentElement.requestFullscreen(); else document.exitFullscreen && document.exitFullscreen(); }
  $('#nav [data-prev]').onclick = function () { go(cur - 1); };
  $('#nav [data-next]').onclick = function () { go(cur + 1); };
  $('#nav [data-menu]').onclick = function () { $('#menu').classList.toggle('on'); };
  $('#nav [data-full]').onclick = full;
  $('#menu').addEventListener('click', function (e) { if (e.target.id === 'menu') this.classList.remove('on'); });
  $('#menu ol').innerHTML = slides.map(function (s, k) { return '<li><button data-k="' + k + '"><em>' + String(k + 1).padStart(2, '0') + '</em><span>' + s.dataset.t + '<small>' + (s.dataset.ch || 'Ouverture') + '</small></span></button></li>'; }).join('');
  $$('#menu li button').forEach(function (b) { b.onclick = function () { go(+b.dataset.k); $('#menu').classList.remove('on'); }; });
  $$('[data-go]').forEach(function (b) { b.addEventListener('click', function () { goId(b.dataset.go); }); });

  /* barre de navigation : visible quelques secondes après un mouvement de souris */
  var navT = null, navEl = $('#nav');
  document.addEventListener('mousemove', function (e) { if (e.clientY > window.innerHeight - 140) { navEl.classList.add('show'); clearTimeout(navT); navT = setTimeout(function () { navEl.classList.remove('show'); }, 2200); } });

  /* compteurs */
  function count(s) {
    $$('[data-count]', s).forEach(function (el) {
      var to = +el.dataset.count, sfx = el.dataset.sfx || '', t0 = null, d = reduce ? 1 : 1600;
      function f(v) { return Math.round(v).toLocaleString('fr-FR').replace(/ /g, ' ') + sfx; }
      function step(ts) { if (!t0) t0 = ts; var p = Math.min(1, (ts - t0) / d); el.textContent = f(to * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); }
      el.textContent = f(0); requestAnimationFrame(step);
    });
  }

  /* ---------------------------------------------------------------- applications réelles (moteur YouCraft) */
  var Y = window.YA;
  /* Chaque application a sa propre couleur : les bleus du moteur sont remplacés par sa palette. */
  var ACC = { people: '#7C3AED', sales: '#1E3BFF', expenses: '#0F766E', tower: '#E8590C', assets: '#0284C7', legal: '#DB2777', rnd: '#A21CAF', portal: '#18181B' };
  var YB = window.__YB || [];
  function hx(h) { return [parseInt(h.slice(1, 3), 16), parseInt(h.slice(3, 5), 16), parseInt(h.slice(5, 7), 16)]; }
  function mixc(h, t) { var c = hx(h), e = t >= 0 ? 255 : 0, a = Math.abs(t); return '#' + c.map(function (v) { return Math.round(v + (e - v) * a).toString(16).padStart(2, '0'); }).join(''); }
  var YB_RGB = YB.map(function (p) { return hx(p[0]).join(','); });
  var HEXRE = new RegExp(YB.map(function (p) { return p[0]; }).join('|'), 'gi');
  var RGBRE = /rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*[\d.]+\s*)?\)/g;
  function palette(k) { var a = ACC[k] || '#1E3BFF'; return { acc: a, c: YB.map(function (p) { return mixc(a, p[1]); }), rgb: hx(a).join(',') }; }
  function swap(v, pal) {
    if (!v) return v;
    var o = v.replace(HEXRE, function (m) { for (var i = 0; i < YB.length; i++) if (YB[i][0].toLowerCase() === m.toLowerCase()) return pal.c[i]; return m; });
    return o.replace(RGBRE, function (m, r, g, b2, al) { var i = YB_RGB.indexOf(r + ',' + g + ',' + b2); if (i < 0) return m; var n = hx(pal.c[i]).join(', '); return al ? 'rgba(' + n + al + ')' : 'rgb(' + n + ')'; });
  }
  var ATTRS = ['style', 'fill', 'stroke', 'stop-color'];
  function recolor(root, pal) {
    if (!pal || pal.acc.toLowerCase() === '#1e3bff') return;
    var els = root.nodeType === 1 ? [root].concat(Array.prototype.slice.call(root.querySelectorAll('*'))) : [];
    els.forEach(function (el) { ATTRS.forEach(function (a) { var v = el.getAttribute(a); if (v) { var n = swap(v, pal); if (n !== v) el.setAttribute(a, n); } }); });
  }
  function themeHost(host, k) {
    var pal = palette(k); host._pal = pal; host._k = k;
    host.className = host.className.replace(/\s*\bth-\w+/g, '') + ' thm th-' + k;
    pal.c.forEach(function (c, i) { host.style.setProperty('--yb' + i, c); });
    host.style.setProperty('--yrgb', pal.rgb);
    if (!host._ob) {
      host._ob = new MutationObserver(function (ms) {
        var p = host._pal; if (!p) return;
        ms.forEach(function (m) {
          if (m.type === 'attributes') recolor(m.target, p);
          else m.addedNodes.forEach(function (n) { if (n.nodeType === 1) recolor(n, p); });
        });
        brandify(host);
      });
      host._ob.observe(host, { subtree: true, childList: true, attributes: true, attributeFilter: ATTRS });
    }
  }
  /* Marques fictives : logo, nom et signature de chaque application */
  var BR = {
    people: ['Kaya', 'Northwind · Espace RH', '<svg viewBox="0 0 34 34"><circle cx="17" cy="17" r="17" fill="#7C3AED"/><circle cx="12.5" cy="14" r="2.6" fill="#fff"/><circle cx="21.5" cy="14" r="2.6" fill="#fff"/><path d="M10.5 20.5c3.6 4 9.4 4 13 0" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg>'],
    sales: ['Vela', 'Northwind · Revenus', '<svg viewBox="0 0 34 34"><rect width="34" height="34" rx="9" fill="#1E3BFF"/><path d="M17 7 L17 24 L8 24 Z" fill="#fff"/><path d="M19 11 L19 24 L26 24 Z" fill="#9DB0FF"/><rect x="7" y="26" width="20" height="2" rx="1" fill="#fff"/></svg>'],
    expenses: ['Ledgr', 'Northwind · Finance', '<svg viewBox="0 0 34 34"><rect width="34" height="34" rx="4" fill="#0F766E"/><rect x="8" y="9" width="18" height="3.4" fill="#fff"/><rect x="8" y="15.3" width="13" height="3.4" fill="#99E0D4"/><rect x="8" y="21.6" width="8" height="3.4" fill="#fff"/></svg>'],
    tower: ['Routa', 'Northwind · Logistique', '<svg viewBox="0 0 34 34"><circle cx="17" cy="17" r="17" fill="#E8590C"/><path d="M10 23 C 14 23, 13 12, 17 12 S 21 19, 24 11" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/><circle cx="10" cy="23" r="2.6" fill="#fff"/><circle cx="24" cy="11" r="2.6" fill="#FFD8C2"/></svg>'],
    assets: ['Forgex', 'Northwind · Usines', '<svg viewBox="0 0 34 34"><path d="M17 1.5 30.4 9.25v15.5L17 32.5 3.6 24.75V9.25Z" fill="#0284C7"/><path d="M17 9.5 23.5 13.25v7.5L17 24.5 10.5 20.75v-7.5Z" fill="none" stroke="#fff" stroke-width="2.4"/><circle cx="17" cy="17" r="2.4" fill="#BAE6FD"/></svg>'],
    legal: ['Socia', 'Northwind · DAF', '<svg viewBox="0 0 34 34"><rect width="34" height="34" rx="11" fill="#fff"/><circle cx="13.5" cy="17" r="8" fill="#DB2777" opacity=".9"/><circle cx="20.5" cy="17" r="8" fill="#4F46E5" opacity=".85" style="mix-blend-mode:multiply"/></svg>'],
    rnd: ['Labora', 'Northwind · Innovation', '<svg viewBox="0 0 34 34"><defs><linearGradient id="lbg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#C026D3"/><stop offset="1" stop-color="#7C3AED"/></linearGradient></defs><rect width="34" height="34" rx="11" fill="url(#lbg)"/><ellipse cx="17" cy="17" rx="10" ry="4" fill="none" stroke="#fff" stroke-width="1.8"/><ellipse cx="17" cy="17" rx="10" ry="4" fill="none" stroke="#fff" stroke-width="1.8" transform="rotate(60 17 17)"/><ellipse cx="17" cy="17" rx="10" ry="4" fill="none" stroke="#fff" stroke-width="1.8" transform="rotate(120 17 17)"/><circle cx="17" cy="17" r="2.2" fill="#fff"/></svg>'],
    portal: ['Atrium', 'Northwind · Espace clients', '<svg viewBox="0 0 34 34"><rect width="34" height="34" rx="10" fill="#fff"/><path d="M9 27V16a8 8 0 0 1 16 0v11" fill="none" stroke="#0B0B0F" stroke-width="3"/><path d="M14 27v-9a3 3 0 0 1 6 0v9" fill="#D7FF3F"/></svg>']
  };
  function brandify(host) {
    var k = host._k, br = BR[k]; if (!br) return;
    host.querySelectorAll('.ya-logo').forEach(function (lg) {
      if (lg.dataset.br === k) return; lg.dataset.br = k;
      var mk = lg.querySelector('.mk'); if (mk) mk.innerHTML = br[2];
      var b = lg.querySelector('b'); if (b) b.textContent = br[0];
      var sub = lg.querySelector('span span'); if (sub) sub.textContent = br[1];
    });
    layout(host);
  }

  /* Mise en page propre à chaque application : position des blocs et forme des indicateurs.
     Les blocs gardent leur taille (leur contenu reste intact), seule leur disposition change. */
  var LAY = {
    people: { kpi: 'band' },
    sales: { mirror: true, kpi: 'hero' },
    expenses: { kpiBottom: true, kpi: 'band' },
    tower: {},
    assets: { kpi: 'gap' },
    legal: { kpi: 'gap' },
    rnd: { mirror: true, kpiBottom: true, kpi: 'gap' },
    portal: { kpi: 'band' }
  };
  function px(e, p, v) { var s = Math.round(v) + 'px'; if (e.style[p] !== s) e.style[p] = s; }
  function layout(host) {
    var L = LAY[host._k]; if (!L) return;
    var m = host.querySelector('.ya-main'); if (!m) return;
    var els = Array.prototype.slice.call(m.querySelectorAll(':scope > .ya-card, :scope > .ya-map'));
    els.forEach(function (e) {
      if (e.dataset.ox != null || !e.style.left || !e.style.top) return;
      e.dataset.ox = parseFloat(e.style.left); e.dataset.oy = parseFloat(e.style.top);
      e.dataset.ow = parseFloat(e.style.width); e.dataset.oh = parseFloat(e.style.height);
    });
    els = els.filter(function (e) { return e.dataset.ox != null; });
    var kp = els.filter(function (e) { return e.classList.contains('ya-kpi'); });
    var kTop = Infinity, kBot = -Infinity, cTop = Infinity;
    kp.forEach(function (e) { kTop = Math.min(kTop, +e.dataset.oy); kBot = Math.max(kBot, +e.dataset.oy + +e.dataset.oh); });
    els.forEach(function (e) { if (!e.classList.contains('ya-kpi') && +e.dataset.oy >= kBot) cTop = Math.min(cTop, +e.dataset.oy); });
    var row = kp.length >= 3 && kp.every(function (e) { return +e.dataset.oy === kTop; });
    els.forEach(function (e) {
      var x = +e.dataset.ox, y = +e.dataset.oy, w = +e.dataset.ow, isK = e.classList.contains('ya-kpi');
      if (L.mirror && !isK) x = 1376 - x - w;
      if (L.kpiBottom && row && cTop < Infinity) { if (isK) y = 928 - (kBot - y); else if (y >= kBot) y -= cTop - kTop; }
      if (isK && row) {
        var i = kp.indexOf(e), n = kp.length;
        e.classList.toggle('k0', i === 0); e.classList.toggle('kl', i === n - 1);
        if (L.kpi === 'band') { w = 1320 / n; x = 28 + i * w; }
        else if (L.kpi === 'hero' && n === 4) { var W = [652, 206, 206, 206]; w = W[i]; x = 28; for (var j = 0; j < i; j++) x += W[j] + 16; }
        else if (L.kpi === 'gap') { w = (1320 - (n - 1) * 24) / n; x = 28 + i * (w + 24); }
      }
      px(e, 'left', x); px(e, 'top', y); px(e, 'width', w);
    });
  }

  function tileBg(k) { var a = ACC[k] || '#1E3BFF'; return 'linear-gradient(150deg,' + mixc(a, .38) + ' 0%,' + a + ' 50%,' + mixc(a, -.2) + ' 100%)'; }

  function mount(host, k, view) {
    host.innerHTML = '';
    themeHost(host, k);
    var app = Y.create(k, host);
    recolor(host, host._pal);
    brandify(host);
    if (view != null && window.YAX && window.YAX.nav) { try { window.YAX.nav(app, view); } catch (e) {} }
    requestAnimationFrame(function () { app.fit(); });
    return app;
  }
  function urlOf(k) { return Y.DEF[k][4]; }

  /* couverture : le pipeline commercial, en direct */
  var cover = { app: null };
  H.cover = {
    enter: function () { if (!cover.app) cover.app = mount($('#h-cover'), 'sales'); cover.app.fit(); cover.app.start(); },
    leave: function () { if (cover.app) cover.app.stop(); }
  };
  slides[0].dataset.enter = 'cover';

  /* explorateur */
  var APPS = [
    { k: 'people', team: 'RH', name: 'Espace d’intégration', icon: 'users', p: 'Crée un espace d’intégration pour les nouveaux arrivants, avec une checklist, un parrain et une vue manager.', f: ['Une checklist pour chaque arrivée', 'Relances automatiques aux managers', 'Absences et organigramme'] },
    { k: 'sales', team: 'Commercial', name: 'Pipeline commercial', icon: 'target', p: 'Construis un tableau de bord de notre pipeline sur nos données Salesforce en direct, avec un générateur de devis.', f: ['Synchronisé avec Salesforce', 'Devis générés en un clic', 'Prévisions par commercial'] },
    { k: 'expenses', team: 'Finance', name: 'Notes de frais', icon: 'receipt', p: 'Crée une application de notes de frais avec une validation par le manager.', f: ['Circuit de validation', 'Justificatifs lus automatiquement', 'Export mensuel vers la comptabilité'] },
    { k: 'tower', team: 'Logistique', name: 'Tour de contrôle', icon: 'truck', p: 'Crée une tour de contrôle pour suivre nos camions en direct et signaler les livraisons en retard.', f: ['Carte des camions en direct', 'Retards signalés en temps réel', 'Incidents suivis jusqu’à leur résolution'] },
    { k: 'assets', team: 'Industrie', name: 'Supervision machines', icon: 'gauge', view: 0, p: 'Surveille nos machines sur chaque site et ouvre un ordre de travail quand un capteur dérive.', f: ['Capteurs et alertes en direct', 'Ordres de travail attribués automatiquement', 'Stock de pièces détachées'] },
    { k: 'legal', team: 'Direction financière', name: 'Bilan social', icon: 'chart-pie', p: 'Je suis DAF, je souhaite une application de bilan social à partir du fichier Excel de nos salariés, avec des tableaux de bord sur l’absentéisme, la parité et les catégories socio‑professionnelles.', f: ['Import du fichier Excel des salariés', 'Absentéisme, parité et CSP en un coup d’œil', 'Fiches salariés, alertes et index égalité cliquables'] },
    { k: 'rnd', team: 'R&D', name: 'Temps R&D', icon: 'timer', p: 'Saisis les temps par projet R&D et prépare le dossier de crédit d’impôt recherche.', f: ['Feuilles de temps hebdomadaires', 'Projets par phase', 'Dossier CIR prêt à envoyer'] },
    { k: 'portal', team: 'Conseil', name: 'Portail client', icon: 'building-2', p: 'Crée un portail où nos clients partagent leurs documents, valident les étapes et suivent l’avancement.', f: ['Documents partagés', 'Validations en ligne', 'Suivi de l’avancement'] }
  ];
  var ex = { app: null, i: 0, pending: null };
  var esc = function (s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;'); };
  var tabs = $('#s-explore [data-tabs]');
  tabs.innerHTML = APPS.map(function (a, i) { return '<button data-i="' + i + '"><span class="dtile" style="background:' + tileBg(a.k) + '">' + Y.ic(a.icon, 17, '#fff', 2) + '</span><span>' + esc(a.name) + '<small>' + esc(a.team) + '</small></span></button>'; }).join('');
  $$('button', tabs).forEach(function (b) { b.onclick = function () { show(+b.dataset.i); }; });
  function show(i) {
    ex.i = i; var a = APPS[i];
    $$('button', tabs).forEach(function (b, k) { b.classList.toggle('on', k === i); });
    $('#s-explore [data-prompt]').textContent = '« ' + a.p + ' »';
    $('#s-explore [data-feats]').innerHTML = a.f.map(function (f) { return '<span>' + esc(f) + '</span>'; }).join('');
    $('#s-explore [data-url]').textContent = urlOf(a.k);
    if (ex.app) ex.app.destroy();
    ex.app = mount($('#h-explore'), a.k, a.view);
    if (slides[cur] && slides[cur].id === 's-explore') ex.app.start(); else ex.app.stop();
  }
  H.explore = {
    enter: function () { var i = ex.pending != null ? ex.pending : ex.i; ex.pending = null; if (!ex.app || i !== ex.i) show(i); else { ex.app.fit(); ex.app.start(); } },
    leave: function () { if (ex.app) ex.app.stop(); }
  };
  /* cartes des équipes : la couleur de chaque application */
  $$('#s-teams [data-app]').forEach(function (card) { var t = card.querySelector('.dtile'); if (t) t.style.background = tileBg(card.dataset.app); });

  /* application en plein écran */
  var FS = { on: false, app: null }, fsv = $('#fsv');
  function openFull(i) {
    var a = APPS[i]; FS.on = true; fsv.classList.add('on');
    $('[data-url]', fsv).textContent = urlOf(a.k);
    if (ex.app) ex.app.stop(); if (cover.app) cover.app.stop();
    if (FS.app) FS.app.destroy();
    FS.app = mount($('#h-full'), a.k, a.view); FS.app.start();
    setTimeout(function () { if (FS.app) FS.app.fit(); }, 60);
    $('[data-close]', fsv).focus();
  }
  function closeFull() {
    FS.on = false; fsv.classList.remove('on');
    if (FS.app) { FS.app.destroy(); FS.app = null; }
    var id = slides[cur] && slides[cur].id;
    if (id === 's-explore' && ex.app) { ex.app.fit(); ex.app.start(); }
    if (id === 's-cover' && cover.app) cover.app.start();
  }
  $('[data-close]', fsv).onclick = closeFull;
  fsv.addEventListener('click', function (e) { if (e.target === fsv) closeFull(); });
  window.addEventListener('resize', function () { if (FS.app) FS.app.fit(); });
  $('#s-explore [data-fs]').onclick = function () { openFull(ex.i); };
  function appIndex(k) { return APPS.findIndex(function (a) { return a.k === k; }); }
  $$('#s-teams [data-app]').forEach(function (card) {
    var k = appIndex(card.dataset.app);
    function explore() { ex.pending = k; goId('s-explore'); }
    card.addEventListener('click', function (e) { if (e.target.closest('[data-full-app]')) { e.stopPropagation(); openFull(k); } else explore(); });
    card.addEventListener('keydown', function (e) { if ((e.key === 'Enter' || e.key === ' ') && e.target === card) { e.preventDefault(); e.stopPropagation(); explore(); } });
  });

  /* ---------------------------------------------------------------- démonstration « Décrivez, YouCraft construit » */
  var B = { tm: [], app: null };
  var bs = $('#s-build'), typer = $('[data-typer]', bs), thread = $('[data-thread]', bs), prev = $('.bprev', bs), toast = $('[data-toast]', bs), pub = $('[data-pub]', bs);
  var P1 = 'Crée un espace d’intégration pour les nouveaux arrivants, avec une checklist, un parrain et une vue manager.';
  var P2 = 'Ajoute des relances automatiques aux managers avant chaque arrivée.';
  var STEPS = [['Modèle de données', 'Collaborateurs, tâches, parrains'], ['Écrans et navigation', '4 écrans'], ['Logique et automatisations', 'Relances, alertes'], ['Accès et rôles', 'RH, managers, nouveaux arrivants']];
  function later(f, ms) { B.tm.push(setTimeout(f, reduce ? Math.min(ms, 60) : ms)); }
  function stage(n) { $$('.bstp', bs).forEach(function (s, k) { s.classList.toggle('on', k === n); s.classList.toggle('bok', k < n); }); }
  function type(txt, done) {
    typer.textContent = ''; typer.classList.add('btyping'); var i = 0;
    (function tick() { if (i <= txt.length) { typer.textContent = txt.slice(0, i); i += 2; later(tick, 26); } else { typer.classList.remove('btyping'); later(done, 380); } })();
  }
  function say(html, me) { var d = document.createElement('div'); d.className = 'bmsg' + (me ? ' bme' : ''); d.innerHTML = html; thread.appendChild(d); return d; }
  function reset() {
    B.tm.forEach(clearTimeout); B.tm = []; while (thread.children.length > 1) thread.removeChild(thread.lastChild); typer.textContent = ''; typer.classList.remove('btyping');
    prev.classList.remove('bready'); $('.bph', prev).classList.remove('bidle'); toast.classList.remove('on'); pub.classList.remove('bpulse'); stage(-1);
    if (B.app) B.app.stop();
  }
  /* Démonstration pas à pas : chaque étape se lance au clic (ou avec les flèches du clavier),
     et l'on peut revenir à une étape précédente à tout moment. */
  var CUR = -1;
  function plan(done) {
    var m = say('<small>' + Y.ic('sparkles', 12, '#1E3BFF', 2.4) + 'YouCraft</small>C’est parti. Voici ce que je construis :<ul>' + STEPS.map(function (s) { return '<li' + (done ? ' class="bdone"' : '') + '><i></i><span>' + s[0] + '</span></li>'; }).join('') + '</ul>');
    return $$('li', m);
  }
  function showApp() { if (!B.app) B.app = mount($('#h-build'), 'people'); B.app.fit(); B.app.start(); prev.classList.add('bready'); }
  var REPLY = 'C’est fait : chaque manager reçoit une relance 3 jours avant l’arrivée.';
  function instant(n) {
    if (n === 0) { typer.textContent = P1; }
    if (n === 1) { typer.textContent = ''; say(esc(P1), true); plan(true); showApp(); }
    if (n === 2) { say(esc(P2), true); say('<small>' + Y.ic('sparkles', 12, '#1E3BFF', 2.4) + 'YouCraft</small>' + REPLY); }
    if (n === 3) { pub.classList.add('bpulse'); toast.classList.add('on'); }
  }
  function animate(n) {
    if (n === 0) later(function () { type(P1, function () {}); }, 400);
    if (n === 1) {
      typer.textContent = ''; say(esc(P1), true);
      var lis = plan(false);
      lis.forEach(function (li, k) { later(function () { li.classList.add('brun'); }, 300 + k * 700); later(function () { li.classList.remove('brun'); li.classList.add('bdone'); }, 900 + k * 700); });
      later(showApp, 300 + STEPS.length * 700 + 200);
    }
    if (n === 2) type(P2, function () { typer.textContent = ''; say(esc(P2), true); later(function () { say('<small>' + Y.ic('sparkles', 12, '#1E3BFF', 2.4) + 'YouCraft</small>' + REPLY); }, 500); });
    if (n === 3) { pub.classList.add('bpulse'); later(function () { toast.classList.add('on'); }, 700); }
  }
  function goStage(n) {
    n = Math.max(0, Math.min(3, n));
    reset(); for (var k = 0; k < n; k++) instant(k);
    CUR = n; stage(n); animate(n);
    $('[data-sprev]', bs).disabled = n === 0; $('[data-snext]', bs).disabled = n === 3;
  }
  $$('.bstp', bs).forEach(function (b) { b.onclick = function () { goStage(+b.dataset.st); }; });
  $('[data-sprev]', bs).onclick = function () { goStage(CUR - 1); };
  $('[data-snext]', bs).onclick = function () { goStage(CUR + 1); };
  $('[data-replay]', bs).onclick = function () { goStage(0); };
  $('[data-url]', toast).textContent = Y.DEF.people[4].split('/')[0];
  H.build = { enter: function () { goStage(0); }, leave: function () { reset(); CUR = -1; },
    step: function (d) { var n = CUR + d; if (n < 0 || n > 3) return false; goStage(n); return true; } };

  /* ---------------------------------------------------------------- sécurité : anneaux et cartes liés */
  $$('#s-sec [data-r]').forEach(function (el) {
    var r = el.dataset.r;
    el.addEventListener('mouseenter', function () { $$('#s-sec .row[data-r="' + r + '"]').forEach(function (x) { x.classList.add('hv'); }); var g = $('#s-sec .rg[data-r="' + r + '"]'); if (g) g.style.boxShadow = '0 0 0 3px rgba(30,59,255,.45)'; });
    el.addEventListener('mouseleave', function () { $$('#s-sec .row.hv').forEach(function (x) { x.classList.remove('hv'); }); var g = $('#s-sec .rg[data-r="' + r + '"]'); if (g) g.style.boxShadow = ''; });
  });

  /* démarrage */
  var h = parseInt((location.hash || '').slice(1), 10);
  go(h >= 1 && h <= N ? h - 1 : 0);
})();

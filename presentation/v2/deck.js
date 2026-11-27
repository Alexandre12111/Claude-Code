/* YouCraft · présentation v2 : navigation, démonstration, applications interactives */
(function () {
  'use strict';
  var deck = document.getElementById('deck'), chrome = document.getElementById('chrome');
  var slides = Array.prototype.slice.call(document.querySelectorAll('section.yd:not(.annex)')), N = slides.length, cur = -1;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var H = {};

  function fit() { var k = Math.min(window.innerWidth / 1920, window.innerHeight / 1080); deck.style.transform = 'translate(-50%,-50%) scale(' + k + ')'; }
  window.addEventListener('resize', fit); fit();

  /* ---------------------------------------------------------------- navigation */
  var annex = document.getElementById('s-live'), inAnnex = false;
  function go(i) {
    i = Math.max(0, Math.min(N - 1, i));
    if (inAnnex) { inAnnex = false; annex.classList.toggle('on', false); var keep = cur; cur = -1; if (i === keep) slides[i].classList.toggle('on', false); }
    if (i === cur) return;
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
    if (inAnnex) { if (['Escape', 'ArrowLeft', 'ArrowRight', 'PageUp', 'PageDown'].indexOf(e.key) >= 0) { e.preventDefault(); go(cur); return; } if (e.key !== 'm' && e.key !== 'M') return; }
    var m = $('#menu');
    if (e.key === 'Escape' && m.classList.contains('on')) { m.classList.remove('on'); return; }
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

  /* ---------------------------------------------------------------- annexe : YouCraft en direct (sommaire uniquement) */
  $('#menu ol').insertAdjacentHTML('afterend', '<div class="mx"><span>Annexe</span><button data-annex><em>A</em><span>YouCraft en direct<small>Construire en direct pendant le rendez‑vous</small></span></button></div>');
  $('#menu [data-annex]').onclick = function () { showAnnex(); $('#menu').classList.toggle('on', false); };
  function showAnnex() {
    if (inAnnex) return;
    var s = slides[cur];
    if (s) { s.classList.toggle('on', false); var lk = s.dataset.enter; if (lk && H[lk] && H[lk].leave) H[lk].leave(); }
    inAnnex = true; annex.classList.add('on');
    chrome.classList.toggle('dk', false); chrome.classList.toggle('cover', false); document.body.classList.toggle('dk', false);
    $('#chrome .ch').innerHTML = '<span>Annexe</span><i></i><b>A</b>';
    $('#nav span').textContent = 'Annexe';
    $$('#menu li button').forEach(function (b) { b.classList.toggle('on', false); });
  }
  /* L'iframe n'est jamais retirée : YouCraft continue de tourner quand on change de diapositive. */
  var LV = { frame: null, win: null }, KEYU = 'yc-live-url', DEFU = 'https://app.youcraft.tech/';
  var lurl = $('[data-lurl]', annex), lhost = $('[data-lhost]', annex), lstate = $('[data-lstate]', annex);
  try { lurl.value = localStorage.getItem(KEYU) || DEFU; } catch (e) { lurl.value = DEFU; }
  function lu() { var u = (lurl.value || '').trim() || DEFU; if (!/^https?:\/\//.test(u)) u = 'https://' + u; try { localStorage.setItem(KEYU, u); } catch (e) {} return u; }
  function connect() {
    if (!LV.frame) {
      LV.frame = document.createElement('iframe');
      LV.frame.setAttribute('allow', 'clipboard-read; clipboard-write; fullscreen');
      LV.frame.setAttribute('title', 'YouCraft');
      lhost.appendChild(LV.frame);
    }
    LV.frame.src = lu(); annex.classList.add('live'); lstate.textContent = 'Connecté'; lstate.classList.add('on');
  }
  function openWin() {
    if (LV.win && !LV.win.closed) { LV.win.focus(); return; }
    var w = Math.round(screen.availWidth * .92), h = Math.round(screen.availHeight * .92);
    LV.win = window.open(lu(), 'youcraft-live', 'popup=yes,width=' + w + ',height=' + h + ',left=' + Math.round((screen.availWidth - w) / 2) + ',top=0');
    $$('[data-lwin] span', annex).forEach(function (s) { s.textContent = 'Afficher la fenêtre YouCraft'; });
  }
  $('[data-lconnect]', annex).onclick = connect;
  $$('[data-lwin]', annex).forEach(function (b) { b.onclick = openWin; });
  $('[data-lreload]', annex).onclick = function () { if (LV.frame) LV.frame.src = lu(); };
  $('[data-back]', annex).onclick = function () { go(cur); };
  $('[data-lfull]', annex).onclick = function () { var w = $('.lwin', annex); if (document.fullscreenElement) document.exitFullscreen(); else if (w.requestFullscreen) w.requestFullscreen(); };
  lurl.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); if (LV.frame) LV.frame.src = lu(); else connect(); } });

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
  var ACC = { people: '#7C3AED', sales: '#1E3BFF', expenses: '#0F766E', tower: '#E8590C', assets: '#0284C7', legal: '#BE185D', rnd: '#A21CAF', portal: '#18181B' };
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
    legal: ['Clausia', 'Northwind · Juridique', '<svg viewBox="0 0 34 34"><circle cx="17" cy="17" r="16" fill="#BE185D"/><circle cx="17" cy="17" r="12.5" fill="none" stroke="#F9A8D4" stroke-width="1"/><text x="17" y="23.5" text-anchor="middle" font-family="Georgia,serif" font-size="18" font-style="italic" font-weight="700" fill="#fff">C</text></svg>'],
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
    { k: 'legal', team: 'Juridique', name: 'Revue de contrats', icon: 'file-text', p: 'Crée un agent de revue de contrats qui signale les clauses à risque et suit les dates de renouvellement.', f: ['Revue IA de chaque contrat', 'Alertes de renouvellement', 'Suivi des preuves d’audit'] },
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
  function run() {
    reset(); stage(0);
    later(function () {
      type(P1, function () {
        typer.textContent = ''; say(esc(P1), true); stage(1);
        var m = say('<small>' + Y.ic('sparkles', 12, '#1E3BFF', 2.4) + 'YouCraft</small>C’est parti. Voici ce que je construis :<ul>' + STEPS.map(function (s) { return '<li><i></i><span>' + s[0] + '</span></li>'; }).join('') + '</ul>');
        var lis = $$('li', m);
        lis.forEach(function (li, k) { later(function () { li.classList.add('brun'); }, 300 + k * 900); later(function () { li.classList.remove('brun'); li.classList.add('bdone'); }, 1100 + k * 900); });
        later(function () {
          if (!B.app) B.app = mount($('#h-build'), 'people');
          B.app.fit(); B.app.start(); prev.classList.add('bready');
          later(function () { stage(2); type(P2, function () { typer.textContent = ''; say(esc(P2), true); var r = say('<small>' + Y.ic('sparkles', 12, '#1E3BFF', 2.4) + 'YouCraft</small>C’est fait : chaque manager reçoit une relance 3 jours avant l’arrivée.'); later(function () { stage(3); pub.classList.add('bpulse'); later(function () { toast.classList.add('on'); }, 900); }, 1500); }); }, 2200);
        }, 300 + STEPS.length * 900 + 400);
      });
    }, 700);
  }
  $('[data-url]', toast).textContent = Y.DEF.people[4].split('/')[0];
  H.build = { enter: run, leave: reset };
  $('[data-replay]', bs).onclick = run;

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

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
  $$('[data-go]').forEach(function (b) { b.addEventListener('click', function () { goId(b.dataset.go); }); });

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
  function mount(host, k, view) {
    host.innerHTML = '';
    var app = Y.create(k, host);
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
  tabs.innerHTML = APPS.map(function (a, i) { return '<button data-i="' + i + '"><span class="tile">' + Y.ic(a.icon, 17, '#fff', 2) + '</span><span>' + esc(a.name) + '<small>' + esc(a.team) + '</small></span></button>'; }).join('');
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
  $$('#s-teams [data-app]').forEach(function (b) {
    b.addEventListener('click', function () { var k = APPS.findIndex(function (a) { return a.k === b.dataset.app; }); ex.pending = k; goId('s-explore'); });
  });

  /* ---------------------------------------------------------------- démonstration « Décrivez, YouCraft construit » */
  var B = { tm: [], app: null };
  var bs = $('#s-build'), typer = $('[data-typer]', bs), thread = $('[data-thread]', bs), prev = $('.prev', bs), toast = $('[data-toast]', bs), pub = $('[data-pub]', bs);
  var P1 = 'Crée un espace d’intégration pour les nouveaux arrivants, avec une checklist, un parrain et une vue manager.';
  var P2 = 'Ajoute des relances automatiques aux managers avant chaque arrivée.';
  var STEPS = [['Modèle de données', 'Collaborateurs, tâches, parrains'], ['Écrans et navigation', '4 écrans'], ['Logique et automatisations', 'Relances, alertes'], ['Accès et rôles', 'RH, managers, nouveaux arrivants']];
  function later(f, ms) { B.tm.push(setTimeout(f, reduce ? Math.min(ms, 60) : ms)); }
  function stage(n) { $$('.stp', bs).forEach(function (s, k) { s.classList.toggle('on', k === n); s.classList.toggle('ok', k < n); }); }
  function type(txt, done) {
    typer.textContent = ''; typer.classList.add('typing'); var i = 0;
    (function tick() { if (i <= txt.length) { typer.textContent = txt.slice(0, i); i += 2; later(tick, 26); } else { typer.classList.remove('typing'); later(done, 380); } })();
  }
  function say(html, me) { var d = document.createElement('div'); d.className = 'msg' + (me ? ' me' : ''); d.innerHTML = html; thread.appendChild(d); return d; }
  function reset() {
    B.tm.forEach(clearTimeout); B.tm = []; while (thread.children.length > 1) thread.removeChild(thread.lastChild); typer.textContent = ''; typer.classList.remove('typing');
    prev.classList.remove('ready'); $('.ph', prev).classList.remove('idle'); toast.classList.remove('on'); pub.classList.remove('pulse'); stage(-1);
    if (B.app) B.app.stop();
  }
  function run() {
    reset(); stage(0);
    later(function () {
      type(P1, function () {
        typer.textContent = ''; say(esc(P1), true); stage(1);
        var m = say('<small>' + Y.ic('sparkles', 12, '#1E3BFF', 2.4) + 'YouCraft</small>C’est parti. Voici ce que je construis :<ul>' + STEPS.map(function (s) { return '<li><i></i><span>' + s[0] + '</span></li>'; }).join('') + '</ul>');
        var lis = $$('li', m);
        lis.forEach(function (li, k) { later(function () { li.classList.add('run'); }, 300 + k * 900); later(function () { li.classList.remove('run'); li.classList.add('done'); }, 1100 + k * 900); });
        later(function () {
          if (!B.app) B.app = mount($('#h-build'), 'people');
          B.app.fit(); B.app.start(); prev.classList.add('ready');
          later(function () { stage(2); type(P2, function () { typer.textContent = ''; say(esc(P2), true); var r = say('<small>' + Y.ic('sparkles', 12, '#1E3BFF', 2.4) + 'YouCraft</small>C’est fait : chaque manager reçoit une relance 3 jours avant l’arrivée.'); later(function () { stage(3); pub.classList.add('pulse'); later(function () { toast.classList.add('on'); }, 900); }, 1500); }); }, 2200);
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

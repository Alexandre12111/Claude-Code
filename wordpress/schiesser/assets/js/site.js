/* Comportements communs à toutes les pages. */
(function () {
  var S = window.SCHIESSER || {};

  /* menu mobile */
  document.querySelectorAll('header .burger').forEach(function (b) {
    var nav = b.closest('header').querySelector('nav.main');
    if (!nav) return;
    b.addEventListener('click', function () {
      var ouvert = nav.classList.toggle('open');
      b.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { nav.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.classList.contains('open')) { nav.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); b.focus(); }
    });
  });

  /* ombre de l'en-tête au défilement */
  var tete = document.querySelector('header.site-header');
  function ombre() { if (tete) tete.classList.toggle('scrolled', window.scrollY > 8); }
  window.addEventListener('scroll', ombre, { passive: true }); ombre();

  /* apparition au défilement */
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.1, rootMargin: '0px 0px -6% 0px' });
    document.querySelectorAll('.rv').forEach(function (el) { io.observe(el); });
  } else {
    document.querySelectorAll('.rv').forEach(function (el) { el.classList.add('in'); });
  }

  /* page produit sur mobile : la barre de commande s'efface quand les boutons de la fiche sont visibles */
  var barre = document.querySelector('.js-produit-barre');
  var actions = document.querySelector('.produit-actions');
  if (barre && actions && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (es) {
      barre.classList.toggle('is-cachee', es[0].isIntersecting);
    }).observe(actions);
  }

  /* ouvert / fermé, d'après les horaires des Réglages de la maison (heure de la boutique) */
  var H = S.horaires || {};
  var P = S.particuliers || {}; // jours fériés et dates exceptionnelles : { "2026-04-06": { h: null, m: "Lundi de Pâques" } }
  var fuseau = (S.fuseau && S.fuseau.indexOf('/') > 0) ? S.fuseau : null;
  function hh(h) { var m = Math.round((h % 1) * 60); return String(Math.floor(h)).padStart(2, '0') + ':' + String(m).padStart(2, '0'); }
  function maintenant() {
    var d = new Date();
    if (fuseau && window.Intl) {
      try {
        var p = {};
        new Intl.DateTimeFormat('en-US', { timeZone: fuseau, weekday: 'short', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' })
          .formatToParts(d).forEach(function (x) { p[x.type] = x.value; });
        var jours = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };
        if (p.weekday in jours) return { jour: jours[p.weekday], h: (parseInt(p.hour, 10) % 24) + parseInt(p.minute, 10) / 60, d: d, y: +p.year, m: +p.month, dj: +p.day };
      } catch (e) { /* fuseau inconnu : heure de l'appareil */ }
    }
    return { jour: d.getDay(), h: d.getHours() + d.getMinutes() / 60, d: d, y: d.getFullYear(), m: d.getMonth() + 1, dj: d.getDate() };
  }
  // Date (AAAA-MM-JJ) dans k jours, et horaires de ce jour-là (jours particuliers compris).
  function dateDans(t, k) { var x = new Date(Date.UTC(t.y, t.m - 1, t.dj + k)); return x.getUTCFullYear() + '-' + ('0' + (x.getUTCMonth() + 1)).slice(-2) + '-' + ('0' + x.getUTCDate()).slice(-2); }
  function horairesDans(t, k) { var c = dateDans(t, k); return Object.prototype.hasOwnProperty.call(P, c) ? P[c].h : H[(t.jour + k) % 7]; }
  S.dateDans = dateDans; S.horairesDans = horairesDans; S.maintenant = maintenant; // partagés avec maquette.js
  function etat() {
    var t = maintenant();
    var j = horairesDans(t, 0), ouvert = !!j && t.h >= j[0] && t.h < j[1];
    document.querySelectorAll('.js-led').forEach(function (e) { e.classList.toggle('shut', !ouvert); });
    document.querySelectorAll('.js-statut').forEach(function (e) { e.textContent = ouvert ? 'Ouvert' : 'Fermé'; });
    document.querySelectorAll('.js-heures').forEach(function (e) { e.textContent = j ? hh(j[0]) + '–' + hh(j[1]) : 'Fermé aujourd’hui'; });
    var options = { weekday: 'long', day: 'numeric', month: 'long' };
    if (fuseau) options.timeZone = fuseau;
    document.querySelectorAll('.js-date').forEach(function (e) {
      try { e.textContent = t.d.toLocaleDateString(S.langue || 'fr', options); }
      catch (err) { e.textContent = t.d.toLocaleDateString(); }
    });
  }
  etat(); setInterval(etat, 30000);

  /* filtres allergènes et régimes (boutique et carte du salon, inc/allergenes.php) */
  document.querySelectorAll('.js-al-filtres').forEach(function (bar) {
    var scope = bar.closest('.js-boutique') || bar.closest('.mn') || bar.parentNode;
    var items = scope.querySelectorAll('.card, .mi');
    var vide = bar.querySelector('.js-al-vide');
    function liste(attr) { var o = []; bar.querySelectorAll('[' + attr + '][aria-pressed="true"]').forEach(function (b) { o.push(b.getAttribute(attr)); }); return o; }
    function appliquer() {
      var sans = liste('data-sans'), reg = liste('data-regime'), actif = sans.length || reg.length, n = 0;
      items.forEach(function (it) {
        var ok = true;
        if (sans.length) {
          var al = (it.getAttribute('data-al') || '').split(' '), re0 = (it.getAttribute('data-re') || '').split(' ');
          sans.forEach(function (s) {
            // allergènes non renseignés : jamais « sans » par défaut, sauf « sans gluten » coché comme régime
            if (s === 'gluten' && re0.indexOf('sans-gluten') >= 0 && al.indexOf('gluten') < 0) return;
            if (!it.hasAttribute('data-al-ok') || al.indexOf(s) >= 0) ok = false;
          });
        }
        if (reg.length) { var re = (it.getAttribute('data-re') || '').split(' '); reg.forEach(function (r) { if (re.indexOf(r) < 0) ok = false; }); }
        it.classList.toggle('al-masque', !ok);
        if (ok && !it.hidden) n++;
      });
      scope.classList.toggle('al-actif', !!actif);
      if (vide) vide.hidden = !(actif && n === 0);
      scope.dispatchEvent(new CustomEvent('schiesser:filtre'));
    }
    bar.querySelectorAll('.fchip--al').forEach(function (b) {
      b.addEventListener('click', function () {
        var on = b.getAttribute('aria-pressed') !== 'true';
        b.setAttribute('aria-pressed', on ? 'true' : 'false'); b.classList.toggle('on', on);
        appliquer();
      });
    });
    scope.addEventListener('schiesser:categorie', appliquer);
  });

  /* barre du téléphone : « Ouvert jusqu'à 18 h 30 » / « Fermé · ouvre demain à 8 h » */
  var bm = document.querySelector('.js-bm-texte');
  function hfr(x) { var h = Math.floor(x), m = Math.round((x % 1) * 60); return h + '\u00a0h' + (m ? '\u00a0' + ('0' + m).slice(-2) : ''); }
  function barreMobile() {
    if (!bm) return;
    var t = maintenant(), j = horairesDans(t, 0), ouvert = !!j && t.h >= j[0] && t.h < j[1], txt = 'Fermé';
    if (ouvert) txt = 'Ouvert jusqu’à ' + hfr(j[1]);
    else for (var k = 0; k <= 14; k++) {
      var x = horairesDans(t, k);
      if (x && (k > 0 || t.h < x[0])) {
        var quand = k === 0 ? '' : (k === 1 ? 'demain ' : ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][(t.jour + k) % 7] + ' ');
        txt = 'Fermé · ouvre ' + quand + 'à ' + hfr(x[0]); break;
      }
    }
    bm.textContent = txt;
    var led = document.querySelector('.js-bm-led'); if (led) led.classList.toggle('shut', !ouvert);
  }
  barreMobile(); setInterval(barreMobile, 30000);
  // menu mobile ouvert : la barre s'efface
  var barreEl = document.querySelector('.barre-mobile');
  document.querySelectorAll('header .burger').forEach(function (b) {
    b.addEventListener('click', function () { if (barreEl) barreEl.classList.toggle('is-cachee', b.getAttribute('aria-expanded') === 'true'); });
  });
})();

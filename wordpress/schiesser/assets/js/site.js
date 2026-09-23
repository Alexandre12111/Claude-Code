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
  var fuseau = (S.fuseau && S.fuseau.indexOf('/') > 0) ? S.fuseau : null;
  function hh(h) { var m = Math.round((h % 1) * 60); return String(Math.floor(h)).padStart(2, '0') + ':' + String(m).padStart(2, '0'); }
  function maintenant() {
    var d = new Date();
    if (fuseau && window.Intl) {
      try {
        var p = {};
        new Intl.DateTimeFormat('en-US', { timeZone: fuseau, weekday: 'short', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' })
          .formatToParts(d).forEach(function (x) { p[x.type] = x.value; });
        var jours = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };
        if (p.weekday in jours) return { jour: jours[p.weekday], h: (parseInt(p.hour, 10) % 24) + parseInt(p.minute, 10) / 60, d: d };
      } catch (e) { /* fuseau inconnu : heure de l'appareil */ }
    }
    return { jour: d.getDay(), h: d.getHours() + d.getMinutes() / 60, d: d };
  }
  function etat() {
    var t = maintenant();
    var j = H[t.jour], ouvert = !!j && t.h >= j[0] && t.h < j[1];
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
})();

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

  /* ouvert / fermé, d'après les horaires des Réglages de la maison */
  var H = S.horaires || {};
  function hh(h) { var m = Math.round((h % 1) * 60); return String(Math.floor(h)).padStart(2, '0') + ':' + String(m).padStart(2, '0'); }
  function etat() {
    var now = new Date(), d = now.getDay(), h = now.getHours() + now.getMinutes() / 60;
    var j = H[d], ouvert = !!j && h >= j[0] && h < j[1];
    document.querySelectorAll('.js-led').forEach(function (e) { e.classList.toggle('shut', !ouvert); });
    document.querySelectorAll('.js-statut').forEach(function (e) { e.textContent = ouvert ? 'Ouvert' : 'Fermé'; });
    document.querySelectorAll('.js-heures').forEach(function (e) { e.textContent = j ? hh(j[0]) + '–' + hh(j[1]) : 'Fermé aujourd’hui'; });
    document.querySelectorAll('.js-date').forEach(function (e) {
      e.textContent = now.toLocaleDateString(S.langue || 'fr', { weekday: 'long', day: 'numeric', month: 'long' });
    });
  }
  etat(); setInterval(etat, 30000);
})();

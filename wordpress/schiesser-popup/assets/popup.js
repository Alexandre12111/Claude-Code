/* Schiesser · Fenêtre des horaires : affichage selon la fréquence choisie, accessible au clavier. */
(function () {
  var el = document.getElementById('spop');
  if (!el) return;
  var r = {};
  try { r = JSON.parse(el.getAttribute('data-reglages') || '{}'); } catch (e) {}
  var CLE = 'schiesser_popup';
  var apercu = /[?&]apercu_horaires=1/.test(location.search);

  // Date et jour de la semaine à Bâle (et non selon l'horloge du visiteur).
  function bale() {
    var o = { timeZone: 'Europe/Zurich' };
    try {
      return {
        date: new Intl.DateTimeFormat('en-CA', Object.assign({ year: 'numeric', month: '2-digit', day: '2-digit' }, o)).format(new Date()),
        jour: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(new Intl.DateTimeFormat('en-US', Object.assign({ weekday: 'short' }, o)).format(new Date()))
      };
    } catch (e) {
      var d = new Date();
      return { date: d.toISOString().slice(0, 10), jour: d.getDay() };
    }
  }
  var auj = bale();

  function lire(stock) { try { return JSON.parse(window[stock].getItem(CLE) || 'null'); } catch (e) { return null; } }
  function ecrire(stock) { try { window[stock].setItem(CLE, JSON.stringify({ v: r.v, t: Date.now(), d: auj.date })); } catch (e) {} }

  function aMontrer() {
    if (apercu) return true;
    if (r.du && auj.date < r.du) return false;
    if (r.au && auj.date > r.au) return false;
    if (r.freq === 'toujours') return true;
    if (r.freq === 'session') { var s = lire('sessionStorage'); return !(s && s.v === r.v); }
    var m = lire('localStorage');
    if (!m || m.v !== r.v) return true;
    if (r.freq === 'une_fois') return false;
    if (r.freq === 'jours') return Date.now() - m.t >= (r.jours || 1) * 864e5;
    return m.d !== auj.date; // une fois par jour
  }

  if (!aMontrer()) return;
  // La fenêtre passe avant le bandeau cookies : il attend qu'elle soit fermée.
  var html = document.documentElement;
  html.classList.add('horaires-attente');

  var boite = el.querySelector('.spop-boite');
  var avant = null;
  var ligne = el.querySelector('.spop-j[data-j="' + auj.jour + '"]');
  if (ligne) ligne.classList.add('is-auj');

  function focusables() {
    return Array.prototype.filter.call(boite.querySelectorAll('a[href],button,[tabindex]:not([tabindex="-1"])'), function (x) { return x.offsetParent !== null; });
  }
  function clavier(e) {
    if (e.key === 'Escape') { fermer(); return; }
    if (e.key !== 'Tab') return;
    var f = focusables(); if (!f.length) return;
    var premier = f[0], dernier = f[f.length - 1];
    if (e.shiftKey && (document.activeElement === premier || document.activeElement === boite)) { e.preventDefault(); dernier.focus(); }
    else if (!e.shiftKey && document.activeElement === dernier) { e.preventDefault(); premier.focus(); }
  }
  function ouvrir() {
    avant = document.activeElement;
    el.hidden = false;
    document.documentElement.classList.add('spop-bloque');
    requestAnimationFrame(function () { requestAnimationFrame(function () { el.classList.add('is-ouvert'); }); });
    boite.focus();
    document.addEventListener('keydown', clavier);
    if (!apercu) ecrire(r.freq === 'session' ? 'sessionStorage' : 'localStorage');
  }
  function fermer() {
    if (html.classList.contains('horaires-attente')) {
      html.classList.remove('horaires-attente');
      document.dispatchEvent(new CustomEvent('schiesser:horaires'));
    }
    el.classList.remove('is-ouvert');
    document.removeEventListener('keydown', clavier);
    document.documentElement.classList.remove('spop-bloque');
    var lent = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    setTimeout(function () { el.hidden = true; }, lent ? 350 : 0);
    if (avant && avant.focus) avant.focus();
  }
  Array.prototype.forEach.call(el.querySelectorAll('[data-fermer]'), function (b) { b.addEventListener('click', fermer); });

  setTimeout(ouvrir, Math.max(0, (r.delai || 0) * 1000));
})();

/*
 * Finitions « prestige » (version 0.10) : titres qui montent mot par mot, photos qui se dévoilent,
 * léger effet de profondeur sur la grande photo, boutons aimantés, barre de lecture dorée.
 * Rien n'est indispensable : sans JavaScript ou avec « mouvement réduit », tout reste affiché normalement.
 */
(function () {
  'use strict';
  var calme = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (calme || !('IntersectionObserver' in window)) return;

  function tous(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
  var vu = new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); vu.unobserve(e.target); } });
  }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

  /* ---------- titres : chaque mot monte depuis un masque ---------- */
  function decouper(el) {
    var i = 0;
    (function parcourir(noeud) {
      Array.prototype.slice.call(noeud.childNodes).forEach(function (n) {
        if (n.nodeType === 3) {
          var morceaux = n.textContent.split(/(\s+)/), frag = document.createDocumentFragment();
          morceaux.forEach(function (m) {
            if (!m) return;
            if (/^\s+$/.test(m)) { frag.appendChild(document.createTextNode(m)); return; }
            var w = document.createElement('span'), s = document.createElement('span');
            w.className = 'w'; s.textContent = m; s.style.setProperty('--i', i++);
            w.appendChild(s); frag.appendChild(w);
          });
          n.parentNode.replaceChild(frag, n);
        } else if (n.nodeType === 1 && n.tagName !== 'BR') {
          parcourir(n);
        }
      });
    })(el);
    el.classList.add('ts');
  }
  tous('.hero h1, .sec-head h2, .cta h2, .mn-titre, .ml-titre').forEach(function (h) {
    if (h.closest('[contenteditable]') || h.querySelector('.w')) return;
    decouper(h);
    if (h.closest('.hero')) { requestAnimationFrame(function () { requestAnimationFrame(function () { h.classList.add('in'); }); }); }
    else vu.observe(h);
  });
  /* les titres de la carte se rejouent à chaque changement d'onglet */
  tous('.js-carte-salon .mtab').forEach(function (t) {
    t.addEventListener('click', function () {
      var bloc = t.closest('.js-carte-salon');
      tous('.mn-titre', bloc).forEach(function (h) { h.classList.remove('in'); void h.offsetWidth; h.classList.add('in'); });
    });
  });

  /* ---------- photos : dévoilement en rideau ----------
     Une photo masquée n'est jamais « visible » pour le navigateur : on surveille donc son conteneur. */
  var rideaux = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      tous('.rev', e.target).forEach(function (r) { r.classList.add('in'); });
      rideaux.unobserve(e.target);
    });
  }, { threshold: 0, rootMargin: '0px 0px -12% 0px' });
  tous('.x-floor, .cat-preview .pv, .dy-im, .vn-im, .origin-fig .im, .x-front, .carte .x-menupic, .cthumb, .cm-stack').forEach(function (b) {
    if (b.closest('.hero') || !b.parentNode) return;
    b.classList.add('rev');
    rideaux.observe(b.parentNode);
  });

  /* ---------- profondeur de la grande photo et barre de lecture ---------- */
  var heros = tous('.hero-media img'), barre = document.createElement('div'), attente = false;
  barre.className = 'x-lecture'; barre.setAttribute('aria-hidden', 'true'); document.body.appendChild(barre);
  function defiler() {
    attente = false;
    var y = window.scrollY, h = document.documentElement.scrollHeight - window.innerHeight;
    barre.style.setProperty('--p', h > 0 ? Math.min(1, y / h).toFixed(4) : 0);
    heros.forEach(function (img) {
      var r = img.parentNode.getBoundingClientRect();
      if (r.bottom > 0) img.style.setProperty('--py', Math.round(Math.max(0, -r.top) * 0.28) + 'px');
    });
  }
  window.addEventListener('scroll', function () { if (!attente) { attente = true; requestAnimationFrame(defiler); } }, { passive: true });
  defiler();

  /* ---------- boutons aimantés (souris uniquement) ---------- */
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    tous('.btn, .carte-pdf').forEach(function (b) {
      b.addEventListener('mousemove', function (e) {
        var r = b.getBoundingClientRect();
        var dx = (e.clientX - r.left - r.width / 2) / r.width, dy = (e.clientY - r.top - r.height / 2) / r.height;
        b.style.translate = (dx * 8).toFixed(1) + 'px ' + (dy * 6).toFixed(1) + 'px';
      });
      b.addEventListener('mouseleave', function () { b.style.translate = ''; });
    });
  }

  /* ---------- carte du Tea Room : l'onglet choisi reste visible sur téléphone ---------- */
  tous('.carte .mtab').forEach(function (t) {
    t.addEventListener('click', function () {
      var rang = t.parentNode; // l'onglet choisi se centre dans la barre, sans faire bouger la page
      if (rang && rang.scrollWidth > rang.clientWidth) rang.scrollTo({ left: t.offsetLeft - (rang.clientWidth - t.offsetWidth) / 2, behavior: 'smooth' });
    });
  });
})();

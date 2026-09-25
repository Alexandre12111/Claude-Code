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
  // État de la maison maintenant : { ouvert, texte } (« Ouvert jusqu'à 18 h 30 », « Fermé · ouvre demain à 8 h »).
  function etatMaison() {
    var t = maintenant(), j = horairesDans(t, 0), ouvert = !!j && t.h >= j[0] && t.h < j[1], txt = 'Fermé';
    if (ouvert) txt = 'Ouvert jusqu’à ' + hfr(j[1]);
    else for (var k = 0; k <= 14; k++) {
      var x = horairesDans(t, k);
      if (x && (k > 0 || t.h < x[0])) {
        var quand = k === 0 ? '' : (k === 1 ? 'demain ' : ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'][(t.jour + k) % 7] + ' ');
        txt = 'Fermé · ouvre ' + quand + 'à ' + hfr(x[0]); break;
      }
    }
    return { ouvert: ouvert, texte: txt };
  }
  S.etatMaison = etatMaison;
  function barreMobile() {
    var e = etatMaison();
    // boutons « Appeler pour commander » : appeler quand c'est ouvert, laisser un message sinon
    document.querySelectorAll('.js-cmd').forEach(function (c) {
      var o = c.querySelector('.js-cmd-ouvert'), f = c.querySelector('.js-cmd-ferme'), p = c.querySelector('.js-cmd-prochain');
      if (o) { o.hidden = !e.ouvert; if (f) f.hidden = e.ouvert; }
      if (p) p.textContent = e.texte;
    });
    if (!bm) return;
    bm.textContent = e.texte;
    var led = document.querySelector('.js-bm-led'); if (led) led.classList.toggle('shut', !e.ouvert);
  }
  barreMobile(); setInterval(barreMobile, 30000);
  // menu mobile ouvert : la barre s'efface
  var barreEl = document.querySelector('.barre-mobile');
  document.querySelectorAll('header .burger').forEach(function (b) {
    b.addEventListener('click', function () { if (barreEl) barreEl.classList.toggle('is-cachee', b.getAttribute('aria-expanded') === 'true'); });
  });

  /* ---------- « Ma sélection » : produits mis de côté pour préparer une commande par téléphone ---------- */
  var CLE = 'schiesser-selection';
  function lire() { try { return JSON.parse(localStorage.getItem(CLE) || '[]') || []; } catch (e) { return []; } }
  function ecrire(l) { try { localStorage.setItem(CLE, JSON.stringify(l)); } catch (e) {} majSelection(); }
  function cle(x) { return String(x.id || x.nom); }
  function dansSelection(b) { var k = String(b.getAttribute('data-id') || b.getAttribute('data-nom')); return lire().some(function (x) { return cle(x) === k; }); }
  function echap(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }

  var panneau = null, flottant = null;
  function construire() {
    if (flottant) return;
    flottant = document.createElement('button');
    flottant.type = 'button'; flottant.className = 'sel-flottant'; flottant.hidden = true;
    flottant.setAttribute('aria-haspopup', 'dialog');
    flottant.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" fill="currentColor"/></svg><span>Ma sélection</span><b class="sel-nb">0</b>';
    document.body.appendChild(flottant);
    panneau = document.createElement('div');
    panneau.className = 'sel-panneau'; panneau.hidden = true;
    panneau.setAttribute('role', 'dialog'); panneau.setAttribute('aria-modal', 'true'); panneau.setAttribute('aria-labelledby', 'sel-titre');
    panneau.innerHTML = '<div class="sel-fond js-sel-fermer"></div><div class="sel-boite"><div class="sel-tete"><p class="sel-titre" id="sel-titre">Ma sélection</p><button type="button" class="sel-x js-sel-fermer" aria-label="Fermer">✕</button></div>'
      + '<p class="sel-intro">Votre liste pour commander par téléphone ou par message. Elle reste dans ce navigateur.</p><ul class="sel-liste js-sel-liste"></ul>'
      + '<div class="sel-actions"><a class="btn btn-kir js-sel-appeler" href="#"><span>Appeler pour commander</span></a><a class="btn btn-line js-sel-message" href="#"><span>Envoyer par message</span></a></div>'
      + '<div class="sel-pied"><button type="button" class="sel-lien js-sel-copier">Copier la liste</button><button type="button" class="sel-lien js-sel-vider">Vider la sélection</button></div></div>';
    document.body.appendChild(panneau);
    var dernier = null;
    flottant.addEventListener('click', function () { dernier = document.activeElement; panneau.hidden = false; document.documentElement.classList.add('sel-ouvert'); var x = panneau.querySelector('.sel-x'); if (x) x.focus(); });
    function fermer() { panneau.hidden = true; document.documentElement.classList.remove('sel-ouvert'); if (dernier && dernier.focus) dernier.focus(); }
    panneau.addEventListener('click', function (e) {
      var t = e.target.closest('button, a'); if (!t) return;
      if (t.classList.contains('js-sel-fermer')) return fermer();
      var li = t.closest('[data-k]'), l = lire();
      if (li) {
        var k = li.getAttribute('data-k'), x = l.filter(function (y) { return cle(y) === k; })[0];
        if (!x) return;
        if (t.classList.contains('js-sel-plus')) x.qte = (x.qte || 1) + 1;
        if (t.classList.contains('js-sel-moins')) x.qte = Math.max(1, (x.qte || 1) - 1);
        if (t.classList.contains('js-sel-retirer')) l = l.filter(function (y) { return cle(y) !== k; });
        ecrire(l); return;
      }
      if (t.classList.contains('js-sel-vider')) { if (window.confirm('Vider votre sélection ?')) { ecrire([]); fermer(); } }
      if (t.classList.contains('js-sel-copier')) {
        var txt = texteListe();
        (navigator.clipboard ? navigator.clipboard.writeText(txt) : Promise.reject()).then(function () { t.textContent = 'Liste copiée'; setTimeout(function () { t.textContent = 'Copier la liste'; }, 2000); }, function () { window.prompt('Copiez la liste :', txt); });
      }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panneau.hidden) fermer(); });
  }
  function texteListe() {
    return lire().map(function (x) { return (x.qte || 1) + ' × ' + x.nom + (x.unite ? ' (' + x.unite + ')' : ''); }).join('\n');
  }
  function majSelection() {
    var l = lire();
    document.querySelectorAll('.js-sel').forEach(function (b) {
      var on = dansSelection(b);
      b.setAttribute('aria-pressed', on ? 'true' : 'false');
      var t = b.querySelector('.js-sel-texte');
      if (t) t.textContent = on ? 'Dans ma sélection' : (b.closest('.cmd--barre') ? 'Garder' : 'Ajouter à ma sélection');
    });
    if (!l.length && !flottant) return;
    construire();
    flottant.hidden = !l.length;
    flottant.querySelector('.sel-nb').textContent = l.reduce(function (n, x) { return n + (x.qte || 1); }, 0);
    panneau.querySelector('.js-sel-liste').innerHTML = l.map(function (x) {
      return '<li data-k="' + echap(cle(x)) + '"><span class="sel-nom">' + (x.url ? '<a href="' + echap(x.url) + '">' + echap(x.nom) + '</a>' : echap(x.nom)) + (x.prix ? '<small>' + echap(x.prix) + (x.unite ? ' · ' + echap(x.unite) : '') + '</small>' : '') + '</span>'
        + '<span class="sel-qte"><button type="button" class="js-sel-moins" aria-label="Un de moins : ' + echap(x.nom) + '">−</button><b aria-live="polite">' + (x.qte || 1) + '</b><button type="button" class="js-sel-plus" aria-label="Un de plus : ' + echap(x.nom) + '">+</button></span>'
        + '<button type="button" class="sel-retirer js-sel-retirer" aria-label="Retirer ' + echap(x.nom) + '">✕</button></li>';
    }).join('') || '<li class="sel-vide">Votre sélection est vide.</li>';
    var app = panneau.querySelector('.js-sel-appeler'), msg = panneau.querySelector('.js-sel-message');
    var e = etatMaison();
    app.hidden = !S.tel;
    app.href = S.tel || '#';
    app.querySelector('span').textContent = e.ouvert ? 'Appeler pour commander' : 'Appeler (' + e.texte.replace('Fermé · ', '') + ')';
    msg.href = (S.contact || '/') + ((S.contact || '').indexOf('?') < 0 ? '?' : '&') + 'selection=1#ecrire';
  }
  S.majSelection = majSelection;
  S.texteSelection = texteListe;
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.js-sel'); if (!b) return;
    var l = lire(), k = String(b.getAttribute('data-id') || b.getAttribute('data-nom'));
    if (!b.getAttribute('data-nom')) return;
    if (l.some(function (x) { return cle(x) === k; })) l = l.filter(function (x) { return cle(x) !== k; });
    else l.push({ id: b.getAttribute('data-id') || '', nom: b.getAttribute('data-nom'), prix: b.getAttribute('data-prix') || '', unite: b.getAttribute('data-unite') || '', url: b.getAttribute('data-url') || '', qte: 1 });
    ecrire(l);
  });
  majSelection();
  window.addEventListener('storage', function (e) { if (e.key === CLE) majSelection(); });

  /* ---------- partage : WhatsApp, e-mail, copier le lien, partage du téléphone ---------- */
  function majPartage(bloc, url, titre) {
    bloc.setAttribute('data-url', url); bloc.setAttribute('data-titre', titre);
    var texte = titre + ' · ' + url;
    var wa = bloc.querySelector('.js-partage-wa'), ml = bloc.querySelector('.js-partage-mail');
    if (wa) wa.href = 'https://wa.me/?text=' + encodeURIComponent(texte);
    if (ml) ml.href = 'mailto:?subject=' + encodeURIComponent(titre) + '&body=' + encodeURIComponent(texte);
  }
  S.majPartage = majPartage;
  document.querySelectorAll('.js-partage-natif').forEach(function (b) { if (navigator.share) b.hidden = false; });
  document.addEventListener('click', function (e) {
    var b = e.target.closest('.js-partage-natif, .js-partage-copier'); if (!b) return;
    var bloc = b.closest('.js-partage'), url = bloc.getAttribute('data-url'), titre = bloc.getAttribute('data-titre');
    if (b.classList.contains('js-partage-natif')) { navigator.share({ title: titre, url: url }).catch(function () {}); return; }
    var t = b.querySelector('.js-partage-copier-texte');
    (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () {
      if (t) { t.textContent = 'Lien copié'; setTimeout(function () { t.textContent = 'Copier le lien'; }, 2000); }
    }, function () { window.prompt('Copiez le lien :', url); });
  });
})();

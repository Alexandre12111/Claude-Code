/**
 * Interactions des sections de la maquette (repris de la maquette v3).
 * Chaque section fonctionne seule : plusieurs sections du même type peuvent cohabiter.
 * Sans JavaScript, tout le contenu reste lisible (premier élément affiché, les autres à la suite).
 */
(function () {
  'use strict';
  var S = window.SCHIESSER || {};

  function tous(sel, racine) { return Array.prototype.slice.call((racine || document).querySelectorAll(sel)); }
  function un(sel, racine) { return (racine || document).querySelector(sel); }
  function dd(n) { return ('0' + n).slice(-2); }
  function relancer(el, classe) { el.classList.remove(classe); void el.offsetWidth; el.classList.add(classe); }
  // Classe « on » (et aria-pressed) sur l'élément choisi.
  function activer(liste, i, attr) {
    liste.forEach(function (x) {
      var on = +x.getAttribute(attr) === i;
      x.classList.toggle('on', on);
      if (x.hasAttribute('aria-pressed')) x.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
  }
  // Affiche le panneau choisi, masque les autres.
  function montrer(liste, i, attr) {
    liste.forEach(function (x) {
      var on = +x.getAttribute(attr) === i;
      x.classList.toggle('is-off', !on);
      if (on) x.classList.add('in');
    });
  }
  function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }

  /* ---------- heure de la boutique (fuseau des Réglages) ---------- */
  var H = S.horaires || {};
  var fuseau = (S.fuseau && S.fuseau.indexOf('/') > 0) ? S.fuseau : null;
  function maintenant() {
    var d = new Date();
    if (fuseau && window.Intl) {
      try {
        var p = {};
        new Intl.DateTimeFormat('en-US', { timeZone: fuseau, weekday: 'short', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' })
          .formatToParts(d).forEach(function (x) { p[x.type] = x.value; });
        var jours = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };
        if (p.weekday in jours) return { jour: jours[p.weekday], h: (parseInt(p.hour, 10) % 24) + parseInt(p.minute, 10) / 60, y: +p.year, m: +p.month, dj: +p.day };
      } catch (e) { /* fuseau inconnu : heure de l'appareil */ }
    }
    return { jour: d.getDay(), h: d.getHours() + d.getMinutes() / 60, y: d.getFullYear(), m: d.getMonth() + 1, dj: d.getDate() };
  }
  function hh(h) { var m = Math.round((h % 1) * 60); return dd(Math.floor(h)) + ':' + dd(m); }
  var P = S.particuliers || {};
  // Horaires dans k jours, jours fériés et dates exceptionnelles compris (même calcul que site.js).
  function dateDans(t, k) { var x = new Date(Date.UTC(t.y, t.m - 1, t.dj + k)); return x.getUTCFullYear() + '-' + dd(x.getUTCMonth() + 1) + '-' + dd(x.getUTCDate()); }
  function horairesDans(t, k) { var c = dateDans(t, k); return Object.prototype.hasOwnProperty.call(P, c) ? P[c].h : H[(t.jour + k) % 7]; }
  function motif(t, k) { var c = dateDans(t, k); return Object.prototype.hasOwnProperty.call(P, c) ? P[c].m : ''; }
  // Prochaine ouverture : { k: jours d'écart, h: heure } (pour « Ouvre demain à 8 h »).
  function prochaineOuverture(t) {
    for (var k = 0; k <= 14; k++) { var x = horairesDans(t, k); if (x && (k > 0 || t.h < x[0])) return { k: k, h: x[0] }; }
    return null;
  }
  function plage(t, k) { var x = horairesDans(t, k); return x ? hh(x[0]) + ' – ' + hh(x[1]) : null; }

  /* ---------- états en direct : bandeau, fiches, tableau des horaires ---------- */
  var JOURS = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
  function direct() {
    var t = maintenant(), j = horairesDans(t, 0), ouvert = !!j && t.h >= j[0] && t.h < j[1];
    var minutes = null, libelle;
    if (ouvert) { minutes = Math.round((j[1] - t.h) * 60); libelle = 'Fermeture dans'; }
    else {
      libelle = 'Ouverture dans';
      if (j && t.h < j[0]) minutes = Math.round((j[0] - t.h) * 60);
      else for (var k = 1; k <= 7; k++) { var s = horairesDans(t, k); if (s) { minutes = Math.round((24 - t.h + 24 * (k - 1) + s[0]) * 60); break; } }
    }
    var duree = minutes === null ? '' : (Math.floor(minutes / 60) ? Math.floor(minutes / 60) + 'h' + dd(minutes % 60) : (minutes % 60) + ' min');
    tous('.js-lv-dot').forEach(function (e) { e.classList.toggle('shut', !ouvert); });
    tous('.js-lv-etat').forEach(function (e) { e.textContent = ouvert ? 'Ouvert' : 'Fermé'; });
    tous('.js-lv-compte').forEach(function (e) { e.innerHTML = libelle + ' <b>' + duree + '</b>'; });
    var m0 = motif(t, 0), m1 = motif(t, 1);
    tous('.js-aujourdhui').forEach(function (e) { e.textContent = (plage(t, 0) || 'Fermé aujourd’hui') + (m0 ? ' · ' + m0 : ''); });
    tous('.js-demain').forEach(function (e) { e.textContent = 'Demain · ' + (plage(t, 1) || 'fermé') + (m1 ? ' (' + m1 + ')' : ''); });
    S.etatDirect = { ouvert: ouvert, j: j, t: t, prochain: ouvert ? null : prochaineOuverture(t) };
    tous('.js-live-court').forEach(function (e) { e.textContent = ouvert ? 'Ouvert · ' + hh(j[1]) : 'Fermé'; e.classList.toggle('shut', !ouvert); });
    tous('.js-etat-plage').forEach(function (e) {
      var txt = (ouvert ? 'Ouvert' : 'Fermé') + (j ? ' · ' + hh(j[0]) + '–' + hh(j[1]) : '');
      var span = un('span', e); if (span) span.textContent = txt; else e.textContent = txt;
      e.classList.toggle('shut', !ouvert);
    });
    tous('.js-htable .hrow').forEach(function (r) { r.classList.toggle('today', +r.getAttribute('data-j') === t.jour); });
    tous('.js-jour').forEach(function (e) { e.textContent = JOURS[t.jour]; });
  }
  direct();
  setInterval(direct, 30000);

  /* ---------- catalogue : aperçu au survol ---------- */
  tous('.cat').forEach(function (cat) {
    var imgs = tous('.cat-preview .pv img', cat), nom = un('.js-cap-nom', cat), no = un('.js-cap-no', cat);
    tous('.cat-row', cat).forEach(function (row) {
      function voir() {
        var i = row.getAttribute('data-img');
        imgs.forEach(function (im) { im.classList.toggle('on', im.getAttribute('data-i') === i); });
        if (nom) nom.textContent = row.getAttribute('data-nom');
        if (no) no.textContent = row.getAttribute('data-no');
      }
      row.addEventListener('mouseenter', voir);
      row.addEventListener('focus', voir);
    });
  });

  /* ---------- savoir-faire : la photo suit l'étape lue ---------- */
  if ('IntersectionObserver' in window) {
    tous('.craft-grid').forEach(function (g) {
      var etapes = tous('.cstep', g), media = tous('.cm-stack img', g), num = un('.js-cm-num', g);
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) {
          if (!e.isIntersecting) return;
          var s = e.target.getAttribute('data-s');
          etapes.forEach(function (x) { x.classList.toggle('active', x === e.target); });
          media.forEach(function (m) { m.classList.toggle('on', m.getAttribute('data-s') === s); });
          if (num) num.textContent = dd(+s + 1);
        });
      }, { threshold: 0.55 });
      etapes.forEach(function (x) { io.observe(x); });
    });
  }

  /* ---------- ligne du temps : cartes à faire glisser ---------- */
  tous('.js-tl-scroll').forEach(function (sc) {
    var sec = sc.closest('.tl') || document, bas = false, sx = 0, sl = 0;
    sc.addEventListener('mousedown', function (e) { bas = true; sc.classList.add('drag'); sx = e.pageX; sl = sc.scrollLeft; });
    window.addEventListener('mouseup', function () { bas = false; sc.classList.remove('drag'); });
    sc.addEventListener('mousemove', function (e) { if (!bas) return; e.preventDefault(); sc.scrollLeft = sl - (e.pageX - sx) * 1.4; });
    function pas() { return Math.min(sc.clientWidth * 0.8, 460); }
    var suiv = un('.js-tl-suiv', sec), prec = un('.js-tl-prec', sec);
    if (suiv) suiv.addEventListener('click', function () { sc.scrollBy({ left: pas(), behavior: 'smooth' }); });
    if (prec) prec.addEventListener('click', function () { sc.scrollBy({ left: -pas(), behavior: 'smooth' }); });
  });

  /* ---------- chronologie à onglets ---------- */
  tous('.tl-stage').forEach(function (stage) {
    var sec = stage.closest('.tl') || document;
    var imgs = tous('.tl-media img', stage), textes = tous('.tl-txt', stage), noeuds = tous('.tl-node', sec), tampon = un('.js-tl-stamp', stage), cur = 0;
    if (!noeuds.length) return;
    function aller(i, clic) {
      cur = (i + noeuds.length) % noeuds.length;
      activer(imgs, cur, 'data-e');
      activer(noeuds, cur, 'data-e');
      montrer(textes, cur, 'data-e');
      if (textes[cur]) relancer(textes[cur], 'tl-anim');
      if (tampon) tampon.textContent = noeuds[cur].getAttribute('data-annee');
      var piste = noeuds[cur].parentNode;
      if (clic && piste && piste.scrollWidth > piste.clientWidth) piste.scrollTo({ left: noeuds[cur].offsetLeft - piste.clientWidth / 2 + noeuds[cur].clientWidth / 2, behavior: 'smooth' });
    }
    noeuds.forEach(function (n) { n.addEventListener('click', function () { aller(+n.getAttribute('data-e'), true); }); });
    var prec = un('.js-tl-prec', sec), suiv = un('.js-tl-suiv', sec);
    if (prec) prec.addEventListener('click', function () { aller(cur - 1, true); });
    if (suiv) suiv.addEventListener('click', function () { aller(cur + 1, true); });
  });

  /* ---------- galerie (visionneuse) ---------- */
  tous('.js-gv').forEach(function (gv) {
    var imgs = tous('.gv-main img', gv), caps = tous('.gv-cap', gv), vign = tous('.gv-th', gv), n = Math.max(vign.length, imgs.length), cur = 0;
    function aller(i) {
      cur = (i + n) % n;
      activer(imgs, cur, 'data-g');
      activer(vign, cur, 'data-g');
      montrer(caps, cur, 'data-g');
      if (caps[cur]) relancer(caps[cur], 'gv-anim');
    }
    vign.forEach(function (b) { b.addEventListener('click', function () { aller(+b.getAttribute('data-g')); }); });
    var prec = un('.js-gv-prec', gv), suiv = un('.js-gv-suiv', gv);
    if (prec) prec.addEventListener('click', function () { aller(cur - 1); });
    if (suiv) suiv.addEventListener('click', function () { aller(cur + 1); });
  });

  /* ---------- montée à l'étage (défilement) ---------- */
  tous('.js-ascend').forEach(function (sec) {
    var imgs = tous('.asc-layers img', sec), noeuds = tous('.asc-node', sec), textes = tous('.asc-copy', sec);
    var fill = un('.js-asc-fill', sec), cab = un('.js-asc-cab', sec), barre = un('.js-asc-bar', sec), indic = un('.js-asc-hint', sec);
    var n = textes.length, idx = -1;
    function poser(i) {
      if (i === idx) return;
      idx = i;
      activer(imgs, i, 'data-a');
      activer(noeuds, i, 'data-a');
      montrer(textes, i, 'data-a');
      if (textes[i]) relancer(textes[i], 'asc-fade');
    }
    function defiler() {
      var r = sec.getBoundingClientRect(), h = sec.offsetHeight - window.innerHeight;
      var p = h > 0 ? Math.max(0, Math.min(1, -r.top / h)) : 0;
      if (fill) fill.style.height = (p * 100) + '%';
      if (cab) cab.style.top = (p * 100) + '%';
      if (barre) barre.style.width = (p * 100) + '%';
      if (indic) indic.style.opacity = p > 0.06 ? '0' : '1';
      poser(Math.min(n - 1, Math.floor(p * n * 0.999)));
    }
    noeuds.forEach(function (b, i) {
      b.addEventListener('click', function () {
        var r = sec.getBoundingClientRect(), h = sec.offsetHeight - window.innerHeight;
        window.scrollTo({ top: window.scrollY + r.top + (i / Math.max(1, n - 1)) * h * 0.94 + 2, behavior: 'smooth' });
      });
    });
    window.addEventListener('scroll', defiler, { passive: true });
    window.addEventListener('resize', defiler);
    poser(0);
    defiler();
  });

  /* ---------- carte du salon : onglets ---------- */
  tous('.js-carte-salon').forEach(function (bloc) {
    var onglets = tous('.mtab', bloc), listes = tous('.mn-list', bloc), photos = tous('.x-menupic img', bloc), legende = un('.js-mn-legende', bloc);
    onglets.forEach(function (t) {
      t.addEventListener('click', function () {
        var i = +t.getAttribute('data-c');
        activer(onglets, i, 'data-c');
        activer(photos, i, 'data-c');
        montrer(listes, i, 'data-c');
        if (legende) legende.textContent = t.textContent;
      });
    });
  });

  /* ---------- panneaux photo ---------- */
  tous('.js-gal').forEach(function (gal) {
    var panneaux = tous('.gp', gal);
    panneaux.forEach(function (p) {
      function ouvrir() { panneaux.forEach(function (x) { x.classList.toggle('on', x === p); }); }
      p.addEventListener('mouseenter', ouvrir);
      p.addEventListener('click', ouvrir);
      p.addEventListener('focus', ouvrir);
    });
  });

  /* ---------- moments de la journée ---------- */
  tous('.js-moments').forEach(function (m) {
    var noeuds = tous('.mo-node', m), imgs = tous('.mo-media img', m), textes = tous('.mo-txt', m), heure = un('.js-mo-heure', m);
    noeuds.forEach(function (b) {
      b.addEventListener('click', function () {
        var i = +b.getAttribute('data-m');
        activer(noeuds, i, 'data-m');
        activer(imgs, i, 'data-m');
        montrer(textes, i, 'data-m');
        if (heure) heure.textContent = b.getAttribute('data-heure');
        if (textes[i]) relancer(textes[i], 'mo-anim');
      });
    });
  });

  /* ---------- archives : agrandissement ---------- */
  tous('.js-archives').forEach(function (grille) {
    var sec = grille.closest('section') || document, lb = un('.js-lb', sec), items = tous('.arch', grille);
    if (!lb || !items.length) return;
    var img = un('.js-lb-img', lb), titre = un('.js-lb-titre', lb), texte = un('.js-lb-texte', lb), num = un('.js-lb-num', lb), fermerBtn = un('.js-lb-fermer', lb);
    var cur = 0, retour = null;
    function ouvrir(i) {
      cur = (i + items.length) % items.length;
      var a = items[cur];
      img.src = a.getAttribute('data-image') || '';
      img.alt = a.getAttribute('data-texte') || '';
      titre.textContent = a.getAttribute('data-titre') || '';
      texte.textContent = a.getAttribute('data-texte') || '';
      num.textContent = 'Archive ' + dd(cur + 1) + ' / ' + dd(items.length);
      if (!lb.classList.contains('open')) {
        retour = document.activeElement;
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        fermerBtn.focus();
      }
    }
    function fermer() {
      lb.classList.remove('open');
      document.body.style.overflow = '';
      if (retour) retour.focus();
    }
    items.forEach(function (b) { b.addEventListener('click', function () { ouvrir(+b.getAttribute('data-a')); }); });
    fermerBtn.addEventListener('click', fermer);
    un('.js-lb-prec', lb).addEventListener('click', function () { ouvrir(cur - 1); });
    un('.js-lb-suiv', lb).addEventListener('click', function () { ouvrir(cur + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) fermer(); });
    document.addEventListener('keydown', function (e) {
      if (!lb.classList.contains('open')) return;
      if (e.key === 'Escape') fermer();
      if (e.key === 'ArrowLeft') ouvrir(cur - 1);
      if (e.key === 'ArrowRight') ouvrir(cur + 1);
      if (e.key === 'Tab') {
        var f = tous('button', lb), premier = f[0], dernier = f[f.length - 1];
        if (e.shiftKey && document.activeElement === premier) { e.preventDefault(); dernier.focus(); }
        else if (!e.shiftKey && document.activeElement === dernier) { e.preventDefault(); premier.focus(); }
      }
    });
  });

  /* ---------- hier et aujourd'hui : comparateur ---------- */
  tous('.js-compare').forEach(function (cmp) {
    var sec = cmp.closest('.tn') || cmp.parentNode, clip = un('.clip', cmp), poignee = un('.cmp-handle', cmp);
    var apres = un('img.after', cmp), avant = un('.clip img', cmp), tagL = un('.cmp-tag.l', cmp), tagR = un('.cmp-tag.r', cmp), p = 50, glisse = false;
    if (!clip || !poignee) return;
    function poser(x) { p = Math.max(2, Math.min(98, x)); clip.style.width = p + '%'; poignee.style.left = p + '%'; poignee.setAttribute('aria-valuenow', String(Math.round(p))); }
    function depuis(x) { var r = cmp.getBoundingClientRect(); poser((x - r.left) / r.width * 100); }
    cmp.addEventListener('mousedown', function (e) { glisse = true; depuis(e.clientX); e.preventDefault(); });
    window.addEventListener('mouseup', function () { glisse = false; });
    window.addEventListener('mousemove', function (e) { if (glisse) depuis(e.clientX); });
    cmp.addEventListener('touchstart', function (e) { depuis(e.touches[0].clientX); }, { passive: true });
    cmp.addEventListener('touchmove', function (e) { depuis(e.touches[0].clientX); }, { passive: true });
    poignee.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') { poser(p - 5); e.preventDefault(); }
      if (e.key === 'ArrowRight') { poser(p + 5); e.preventDefault(); }
    });
    // L'image d'hier garde toute la largeur, seule sa fenêtre change.
    function ajuster() { if (avant && cmp.offsetWidth) avant.style.width = cmp.offsetWidth + 'px'; }
    if (window.ResizeObserver) new ResizeObserver(ajuster).observe(cmp); else window.addEventListener('resize', ajuster);
    ajuster();
    function changer(img, url, alt) { if (!img || !url) return; img.removeAttribute('srcset'); img.removeAttribute('sizes'); img.src = url; img.alt = alt || ''; }
    var onglets = tous('.x-cmptab', sec);
    onglets.forEach(function (t) {
      t.addEventListener('click', function () {
        activer(onglets, +t.getAttribute('data-k'), 'data-k');
        changer(avant, t.getAttribute('data-avant'), t.getAttribute('data-avant-alt'));
        changer(apres, t.getAttribute('data-apres'), t.getAttribute('data-apres-alt'));
        if (avant) avant.classList.toggle('is-vieilli', t.getAttribute('data-vieillir') === '1');
        if (tagL) tagL.textContent = t.getAttribute('data-l') || '';
        if (tagR) tagR.textContent = t.getAttribute('data-r') || '';
        ajuster();
      });
    });
  });

  /* ---------- affluence ---------- */
  tous('.js-affluence').forEach(function (bloc) {
    var donnees = {};
    try { donnees = JSON.parse(bloc.getAttribute('data-donnees') || '{}'); } catch (e) { return; }
    var debut = +bloc.getAttribute('data-debut') || 8, jours = tous('.dchip', bloc), chart = un('.js-aff-chart', bloc), calme = un('.js-aff-calme', bloc), anime = un('.js-aff-anime', bloc);
    function niveau(v) { return v === 0 ? 'Fermé' : v > 75 ? 'Très animé' : v > 50 ? 'Animé' : 'Calme'; }
    function dessiner(j) {
      var t = maintenant(), data = donnees[j] || [], html = '', ouverts = [];
      data.forEach(function (v, k) {
        var h = debut + k, now = (+j === t.jour && h === Math.floor(t.h) && v > 0);
        html += '<div class="bar' + (now ? ' now' : '') + '" style="height:' + Math.max(v, 4) + '%"><span class="bv">' + niveau(v) + '</span><span class="bl">' + h + 'h</span></div>';
        if (v > 0) ouverts.push({ v: v, h: h });
      });
      chart.innerHTML = html;
      if (ouverts.length) {
        var q = ouverts.reduce(function (a, b) { return b.v < a.v ? b : a; });
        var z = ouverts.reduce(function (a, b) { return b.v > a.v ? b : a; });
        calme.textContent = q.h + 'h – ' + (q.h + 1) + 'h';
        anime.textContent = z.h + 'h – ' + (z.h + 1) + 'h';
      } else { calme.textContent = 'Fermé'; anime.textContent = 'Fermé'; }
    }
    jours.forEach(function (b) {
      b.addEventListener('click', function () { var j = b.getAttribute('data-j'); activer(jours, +j, 'data-j'); dessiner(j); });
    });
    var auj = String(maintenant().jour);
    activer(jours, +auj, 'data-j');
    dessiner(auj);
  });

  /* ---------- trajets ---------- */
  tous('.js-trajets').forEach(function (jp) {
    var departs = tous('.ochip', jp), details = tous('.jp-main', jp);
    departs.forEach(function (b) {
      b.addEventListener('click', function () {
        var i = +b.getAttribute('data-t');
        activer(departs, i, 'data-t');
        montrer(details, i, 'data-t');
        var r = details[i] && un('.route', details[i]);
        if (r) relancer(r, 'route-anim');
      });
    });
  });

  /* ---------- questions fréquentes : une seule ouverte à la fois ---------- */
  tous('.js-faq').forEach(function (faq) {
    var items = tous('details.fq', faq);
    items.forEach(function (d) {
      d.addEventListener('toggle', function () { if (d.open) items.forEach(function (x) { if (x !== d) x.open = false; }); });
    });
  });

  /* ---------- formulaire de contact ---------- */
  tous('.js-contact').forEach(function (f) {
    tous('.xg input, .xg textarea', f).forEach(function (ch) {
      function flotter() { ch.closest('.xg').classList.toggle('float', !!ch.value); }
      ch.addEventListener('input', flotter);
      ch.addEventListener('change', flotter);
      flotter();
    });
    // Anti-spam discret : un vrai visiteur tape, clique ou touche l'écran avant d'envoyer.
    var humain = un('.js-humain', f);
    function geste() { if (humain && !humain.value) humain.value = '1'; }
    ['keydown', 'pointerdown', 'touchstart', 'focusin'].forEach(function (ev) { f.addEventListener(ev, geste, { passive: true, once: true }); });
    f.addEventListener('submit', function (e) {
      if (!f.checkValidity()) { e.preventDefault(); f.reportValidity(); }
    });
  });

  /* ---------- carte interactive (OpenStreetMap) ---------- */
  function carte(el) {
    var lat = parseFloat(el.getAttribute('data-lat')), lng = parseFloat(el.getAttribute('data-lng'));
    if (!window.L || isNaN(lat) || isNaN(lng) || el.getAttribute('data-prete')) return;
    el.setAttribute('data-prete', '1');
    var pos = [lat, lng], zoom = +el.getAttribute('data-zoom') || 16, style = el.getAttribute('data-style'), nom = el.getAttribute('data-nom') || '';
    var perso = el.hasAttribute('data-zoom-perso');
    var map = window.L.map(el, { scrollWheelZoom: false, zoomControl: !perso }).setView(pos, zoom);
    var couche = style === 'carto'
      ? window.L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20, subdomains: 'abcd', attribution: '© OpenStreetMap · © CARTO' })
      : window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' });
    couche.addTo(map);
    // Le lien de secours reste visible tant que la carte n'est pas chargée.
    couche.once('load', function () { var f = un('.mapfallback', el); if (f) f.remove(); });
    var icone = style === 'carto'
      ? window.L.divIcon({ className: '', html: '<div class="shop-pin"><div class="pin-b"><span>S</span></div><div class="pin-l">' + esc(nom) + '</div></div>', iconSize: [38, 64], iconAnchor: [19, 38] })
      : window.L.divIcon({ className: 'kir-pin', html: '<span></span>', iconSize: [22, 22], iconAnchor: [11, 11] });
    var repere = window.L.marker(pos, { icon: icone, alt: nom }).addTo(map);
    if (style !== 'carto' && nom) repere.bindPopup(esc(nom)).openPopup();
    map.on('click', function () { map.scrollWheelZoom.enable(); });
    var boite = el.closest('.mapbox');
    if (boite) {
      var plus = un('.js-zoom-plus', boite), moins = un('.js-zoom-moins', boite), centre = un('.js-zoom-centre', boite);
      if (plus) plus.addEventListener('click', function () { map.zoomIn(); });
      if (moins) moins.addEventListener('click', function () { map.zoomOut(); });
      if (centre) centre.addEventListener('click', function () { map.flyTo(pos, zoom, { duration: 0.7 }); });
    }
    setTimeout(function () { map.invalidateSize(); }, 300);
  }
  function preparer(el) {
    if (window.L) carte(el);
    else window.addEventListener('load', function () { carte(el); });
  }
  /* ---------- carte Google Maps affichée après un clic (Réglages maison) ---------- */
  tous('.js-gmap').forEach(function (bloc) {
    var bouton = un('.js-gmap-charger', bloc);
    if (!bouton) return;
    bouton.addEventListener('click', function () {
      var cadre = document.createElement('iframe');
      cadre.className = 'gmap';
      cadre.src = bloc.getAttribute('data-src');
      cadre.title = bloc.getAttribute('data-titre') || 'Carte Google Maps';
      cadre.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
      cadre.setAttribute('allowfullscreen', '');
      bloc.parentNode.replaceChild(cadre, bloc);
      cadre.focus();
    });
  });

  var cartes = tous('.js-carte');
  if (cartes.length) {
    if ('IntersectionObserver' in window) {
      var ioc = new IntersectionObserver(function (es) {
        es.forEach(function (e) { if (e.isIntersecting) { ioc.unobserve(e.target); preparer(e.target); } });
      }, { rootMargin: '300px' });
      cartes.forEach(function (c) { ioc.observe(c); });
    } else cartes.forEach(preparer);
  }

  /* ---------- barre de progression de lecture ---------- */
  var prog = un('.prog');
  if (prog) {
    var avancer = function () {
      var h = document.documentElement.scrollHeight - window.innerHeight;
      prog.style.width = (h > 0 ? window.scrollY / h * 100 : 0) + '%';
    };
    window.addEventListener('scroll', avancer, { passive: true });
    avancer();
  }

  /* ---------- curseur dessiné (ordinateur) ---------- */
  var curseur = un('.x-cursor');
  if (curseur && window.matchMedia && window.matchMedia('(hover: hover) and (min-width: 821px)').matches) {
    var ZONES = [['.card', 'Voir'], ['.gv-th', 'Voir'], ['.gv-main', 'Voir', 1], ['.compare', 'Glisser', 1], ['.arch', 'Ouvrir'],
      ['.gp', 'Voir', 1], ['.x-menupic', 'Voir'], ['.x-floor', 'Ouvrir', 1], ['.mapel', 'Explorer'], ['.vmap', 'Explorer']];
    document.addEventListener('mousemove', function (e) { curseur.style.transform = 'translate(' + e.clientX + 'px,' + e.clientY + 'px)'; });
    ZONES.forEach(function (z) {
      tous(z[0]).forEach(function (zone) {
        zone.addEventListener('mouseenter', function () { curseur.textContent = z[1]; curseur.classList.add('on'); curseur.classList.toggle('dark', !!z[2]); });
        zone.addEventListener('mouseleave', function () { curseur.classList.remove('on'); });
      });
    });
  }
})();

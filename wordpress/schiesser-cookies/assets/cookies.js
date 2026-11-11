/* Schiesser · Bandeau cookies : choix du visiteur, Consent Mode v2, Google Tag Manager, clics utiles. */
(function () {
  var S = window.schiesserCookies, el = document.getElementById('sck');
  if (!S || !el) return;
  var C = S.C, choix = el.querySelector('#sck-choix'), html = document.documentElement;
  var b = function (n) { return el.querySelector('[data-sck="' + n + '"]'); };

  function enregistrer(o) {
    o.v = C.v; o.t = Date.now();
    var secure = location.protocol === 'https:' ? ';Secure' : '';
    document.cookie = 'schiesser_consentement=' + encodeURIComponent(JSON.stringify(o)) + ';path=/;max-age=' + (C.duree * 86400) + ';SameSite=Lax' + secure;
    var avant = S.choix; S.choix = o;
    window.gtag('consent', 'update', S.signaux(o));
    window.dataLayer.push({ event: 'consentement_mis_a_jour', consentement_marketing: o.m ? 'oui' : 'non', consentement_statistiques: o.s ? 'oui' : 'non' });
    if ((o.m && C.mark) || (o.s && C.stats)) S.charger();
    // Accord retiré : on efface les cookies Google déjà posés sur ce site.
    if (avant && ((avant.m && !o.m) || (avant.s && !o.s))) effacer(!o.m, !o.s);
    fermer();
  }
  function effacer(pub, stats) {
    document.cookie.split(';').forEach(function (c) {
      var n = c.split('=')[0].trim();
      if ((pub && /^(_gcl_|_gac_|IDE|test_cookie)/.test(n)) || (stats && /^(_ga|_gid|_gat)/.test(n))) {
        var d = location.hostname.replace(/^www\./, '');
        ['', ';domain=' + d, ';domain=.' + d].forEach(function (dom) { document.cookie = n + '=;path=/;max-age=0' + dom; });
      }
    });
  }
  function ouvrir(details) {
    var o = S.choix || {};
    el.querySelectorAll('.sck-bascule').forEach(function (x) { x.checked = !!o[x.getAttribute('data-cat')]; });
    montrerChoix(!!details);
    el.hidden = false;
    if (details) { var f = el.querySelector('.sck-bascule') || b('enregistrer'); if (f) f.focus(); }
  }
  function fermer() {
    el.hidden = true;
    if (html.classList.contains('cookies-attente')) {
      html.classList.remove('cookies-attente');
      document.dispatchEvent(new CustomEvent('schiesser:cookies'));
    }
  }
  function montrerChoix(oui) {
    choix.hidden = !oui; b('enregistrer').hidden = !oui; b('choisir').hidden = oui;
    b('choisir').setAttribute('aria-expanded', oui ? 'true' : 'false');
  }

  b('tout').addEventListener('click', function () { enregistrer({ m: 1, s: C.stats ? 1 : 0 }); });
  b('rien').addEventListener('click', function () { enregistrer({ m: 0, s: 0 }); });
  b('choisir').addEventListener('click', function () { montrerChoix(true); var f = el.querySelector('.sck-bascule'); if (f) f.focus(); });
  b('enregistrer').addEventListener('click', function () {
    var o = { m: 0, s: 0 };
    el.querySelectorAll('.sck-bascule').forEach(function (x) { o[x.getAttribute('data-cat')] = x.checked ? 1 : 0; });
    enregistrer(o);
  });
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('.js-cookies-reglages, a[href="#cookie-einstellungen"]');
    if (a) { e.preventDefault(); ouvrir(true); }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !el.hidden && S.choix) fermer(); });

  if (!S.choix) ouvrir(false);

  // Clics utiles pour Google Ads : envoyés au dataLayer, lus par Google Tag Manager seulement après accord.
  if (C.evt) {
    document.addEventListener('click', function (e) {
      var a = e.target.closest && e.target.closest('a[href]');
      if (!a) return;
      var h = a.getAttribute('href') || '', ev = '';
      if (/^tel:/i.test(h)) ev = 'clic_telephone';
      else if (/^mailto:/i.test(h)) ev = 'clic_email';
      else if (/^https?:\/\/(wa\.me|api\.whatsapp\.com)/i.test(h)) ev = 'clic_whatsapp';
      else if (/^https?:\/\/(www\.)?(google\.[a-z.]+\/maps|maps\.google\.|maps\.app\.goo\.gl|goo\.gl\/maps)/i.test(h)) ev = 'clic_itineraire';
      if (ev) window.dataLayer.push({ event: ev, lien_url: h, lien_texte: (a.textContent || '').trim().slice(0, 80) });
    }, true);
  }
})();

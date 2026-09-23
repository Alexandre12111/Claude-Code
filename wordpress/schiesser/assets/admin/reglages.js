/* Écran « Réglages de la maison » : onglets, couleurs, aperçu des polices, horaires. */
(function ($) {
  var form = document.querySelector('.s-formulaire');
  if (!form) return;

  /* ---------- onglets ---------- */
  var onglets = document.querySelectorAll('.s-onglet');
  var panneaux = document.querySelectorAll('.s-panneau');
  function ouvrir(cle) {
    var existe = false;
    panneaux.forEach(function (p) { if (p.dataset.panneau === cle) existe = true; });
    if (!existe) cle = onglets.length ? onglets[0].dataset.onglet : '';
    onglets.forEach(function (o) {
      var actif = o.dataset.onglet === cle;
      o.classList.toggle('is-actif', actif);
      o.setAttribute('aria-current', actif ? 'page' : 'false');
    });
    panneaux.forEach(function (p) { p.hidden = p.dataset.panneau !== cle; });
    // après l'enregistrement, WordPress revient sur le même onglet
    var ref = form.querySelector('input[name="_wp_http_referer"]');
    if (ref) ref.value = ref.value.split('#')[0] + '#' + cle;
  }
  onglets.forEach(function (o) {
    o.addEventListener('click', function (e) {
      e.preventDefault();
      history.replaceState(null, '', '#' + o.dataset.onglet);
      ouvrir(o.dataset.onglet);
    });
  });
  ouvrir((location.hash || '').replace('#', ''));
  // lien vers un autre onglet de la même page (ex. #charte) : on l'ouvre sans recharger
  window.addEventListener('hashchange', function () { ouvrir((location.hash || '').replace('#', '')); });

  /* ---------- modifications non enregistrées ---------- */
  // On compare le formulaire à son état de départ : l'avertissement n'apparaît
  // que si quelque chose a vraiment changé (et disparaît si on revient en arrière).
  var modifie = false;
  var initial = null;
  var etat = document.querySelector('.js-etat-modif');
  function serialiser() { return new URLSearchParams(new FormData(form)).toString(); }
  setTimeout(function () { initial = serialiser(); }, 1500);
  function signaler() {
    setTimeout(function () {
      if (initial === null) return;
      modifie = serialiser() !== initial;
      if (etat) etat.hidden = !modifie;
    }, 0);
  }
  form.addEventListener('input', signaler);
  form.addEventListener('change', signaler);
  form.addEventListener('submit', function () { modifie = false; initial = null; });
  window.addEventListener('beforeunload', function (e) {
    if (modifie) { e.preventDefault(); e.returnValue = ''; }
  });

  /* ---------- couleurs (l'aperçu suit les choix en direct) ---------- */
  var apercuCouleurs = document.querySelector('.js-apercu-polices');
  function majCouleur(input, valeur) {
    if (!apercuCouleurs || !input) return;
    var cle = input.id.replace('s-couleur-', '');
    apercuCouleurs.style.setProperty('--ap-' + cle, valeur || input.value);
  }
  document.querySelectorAll('.js-couleur').forEach(function (i) { majCouleur(i, i.value); });
  if ($.fn.wpColorPicker) {
    $('.js-couleur').wpColorPicker({
      change: function (e, ui) { signaler(); majCouleur(this, ui.color.toString()); },
      clear: function () { signaler(); majCouleur(this, this.dataset ? this.dataset.defaultColor : ''); }
    });
  }

  /* ---------- aperçu des polices ---------- */
  var apercu = document.querySelector('.js-apercu-polices');
  if (apercu) {
    var polices = JSON.parse(apercu.dataset.polices || '{}');
    var titres = apercu.querySelectorAll('.s-apercu-titre');
    function majPolices() {
      form.querySelectorAll('.js-police').forEach(function (s) {
        var famille = (polices[s.dataset.cible] || {})[s.value];
        if (!famille) return;
        if (s.dataset.cible === 'titres') titres.forEach(function (t) { t.style.fontFamily = famille; });
        else apercu.style.fontFamily = famille;
      });
    }
    form.querySelectorAll('.js-police').forEach(function (s) { s.addEventListener('change', majPolices); });
    majPolices();
  }

  /* ---------- horaires ---------- */
  var lignes = document.querySelectorAll('.s-horaires tbody tr');
  var courts = { 1: 'Lun', 2: 'Mar', 3: 'Mer', 4: 'Jeu', 5: 'Ven', 6: 'Sam', 0: 'Dim' };
  function valeurs(tr) {
    var t = tr.querySelectorAll('input[type="time"]');
    return { o: t[0].value, f: t[1].value, ferme: tr.querySelector('input[type="checkbox"]').checked };
  }
  function resume() {
    var groupes = [];
    lignes.forEach(function (tr) {
      var v = valeurs(tr);
      var texte = v.ferme ? 'fermé' : v.o + '–' + v.f;
      var der = groupes[groupes.length - 1];
      if (der && der.texte === texte) der.fin = courts[tr.dataset.jour];
      else groupes.push({ debut: courts[tr.dataset.jour], fin: null, texte: texte });
    });
    var sortie = groupes.map(function (g) { return (g.fin ? g.debut + '–' + g.fin : g.debut) + ' ' + g.texte; }).join(' · ');
    var cible = document.querySelector('.js-resume-horaires');
    if (cible) cible.textContent = sortie;
  }
  lignes.forEach(function (tr) {
    tr.addEventListener('input', resume);
    tr.addEventListener('change', function () {
      tr.classList.toggle('is-ferme', valeurs(tr).ferme);
      resume();
    });
  });
  var copier = document.querySelector('.js-copier-lundi');
  if (copier) {
    copier.addEventListener('click', function () {
      var lundi = document.querySelector('.s-horaires tr[data-jour="1"]');
      if (!lundi) return;
      var v = valeurs(lundi);
      ['2', '3', '4', '5'].forEach(function (j) {
        var tr = document.querySelector('.s-horaires tr[data-jour="' + j + '"]');
        var t = tr.querySelectorAll('input[type="time"]');
        t[0].value = v.o; t[1].value = v.f;
        tr.querySelector('input[type="checkbox"]').checked = v.ferme;
        tr.classList.toggle('is-ferme', v.ferme);
      });
      resume();
      signaler();
    });
  }
})(jQuery);

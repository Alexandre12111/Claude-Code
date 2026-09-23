/* Boutique : filtres par catégorie et fiche produit. */
(function () {
  document.querySelectorAll('.js-boutique').forEach(function (root) {
    var dataEl = root.querySelector('.js-boutique-data');
    var sheet = root.querySelector('.js-sheet');
    if (!dataEl || !sheet) return;
    var fiches = JSON.parse(dataEl.textContent || '[]');
    var cartes = Array.prototype.slice.call(root.querySelectorAll('.card'));
    var nombre = root.querySelector('.js-nombre');
    var libelle = root.querySelector('.js-libelle');
    var courant = 0;
    var dernierFocus = null;

    /* filtres */
    root.querySelectorAll('.fchip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        var filtre = chip.getAttribute('data-filtre');
        root.querySelectorAll('.fchip').forEach(function (c) { c.classList.toggle('on', c === chip); });
        var n = 0;
        cartes.forEach(function (carte, i) {
          var cats = (carte.getAttribute('data-categories') || '').split(' ');
          var visible = !filtre || cats.indexOf(filtre) >= 0;
          carte.hidden = !visible;
          if (visible) {
            carte.style.animation = 'none'; void carte.offsetWidth;
            carte.style.animation = ''; carte.style.animationDelay = (n * 0.04) + 's';
            n++;
          }
        });
        nombre.textContent = n;
        libelle.textContent = n > 1 ? 'produits' : 'produit';
      });
    });

    /* fiche */
    function q(s) { return sheet.querySelector(s); }
    function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
    function ouvrir(i) {
      courant = (i + fiches.length) % fiches.length;
      var f = fiches[courant];
      var img = q('.js-sh-img');
      img.src = f.image || ''; img.alt = f.alt || ''; img.style.display = f.image ? '' : 'none';
      q('.js-sh-cat').textContent = f.categorie || '';
      q('.js-sh-nom').textContent = f.nom;
      q('.js-sh-desc').textContent = f.description || '';
      q('.js-sh-lignes').innerHTML = (f.lignes || []).map(function (l) {
        return '<div class="sh-row' + (l[2] ? ' x-extra' : '') + '"><span class="k">' + esc(l[0]) + '</span><span>' + esc(l[1]) + '</span></div>';
      }).join('');
      q('.js-sh-unite').textContent = 'Prix' + (f.unite ? ' · ' + f.unite : '');
      q('.js-sh-prix').textContent = f.prix || '';
      if (!sheet.classList.contains('open')) {
        dernierFocus = document.activeElement;
        sheet.hidden = false;
        sheet.classList.add('open');
        document.body.style.overflow = 'hidden';
        q('.js-sh-fermer').focus();
      }
      sheet.scrollTop = 0;
    }
    function fermer() {
      sheet.classList.remove('open');
      sheet.hidden = true;
      document.body.style.overflow = '';
      if (dernierFocus) dernierFocus.focus();
    }

    cartes.forEach(function (carte) {
      carte.addEventListener('click', function () { ouvrir(+carte.getAttribute('data-i')); });
    });
    q('.js-sh-fermer').addEventListener('click', fermer);
    q('.js-sh-prec').addEventListener('click', function () { ouvrir(courant - 1); });
    q('.js-sh-suiv').addEventListener('click', function () { ouvrir(courant + 1); });
    sheet.addEventListener('click', function (e) {
      if (e.target === sheet || e.target.classList.contains('sh-wrap')) fermer();
    });
    document.addEventListener('keydown', function (e) {
      if (!sheet.classList.contains('open')) return;
      if (e.key === 'Escape') fermer();
      if (e.key === 'ArrowLeft') ouvrir(courant - 1);
      if (e.key === 'ArrowRight') ouvrir(courant + 1);
    });
  });
})();

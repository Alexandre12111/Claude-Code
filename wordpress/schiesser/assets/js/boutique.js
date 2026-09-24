/* Boutique : filtres par catégorie et fiche rapide des produits. */
(function () {
  document.querySelectorAll('.js-boutique').forEach(function (root) {
    var dataEl = root.querySelector('.js-boutique-data');
    var sheet = root.querySelector('.js-sheet');
    if (!dataEl || !sheet) return;
    var fiches = JSON.parse(dataEl.textContent || '[]');
    var cartes = Array.prototype.slice.call(root.querySelectorAll('.card'));
    var nombre = root.querySelector('.js-nombre');
    var libelle = root.querySelector('.js-libelle');
    var email = root.getAttribute('data-email') || '';
    var courant = 0;
    var dernierFocus = null;

    /* ---------- filtres ---------- */
    root.querySelectorAll('.fchip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        var filtre = chip.getAttribute('data-filtre');
        root.querySelectorAll('.fchip').forEach(function (c) {
          c.classList.toggle('on', c === chip);
          c.setAttribute('aria-pressed', c === chip ? 'true' : 'false');
        });
        var n = 0;
        cartes.forEach(function (carte) {
          var cats = (carte.getAttribute('data-categories') || '').split(' ');
          var visible = !filtre || cats.indexOf(filtre) >= 0;
          carte.hidden = !visible;
          if (visible) {
            carte.style.animation = 'none'; void carte.offsetWidth;
            carte.style.animation = ''; carte.style.animationDelay = (n * 0.04) + 's';
            n++;
          }
        });
        compter();
        root.dispatchEvent(new CustomEvent('schiesser:categorie'));
      });
    });
    // Recherche : nom, description, composition… (accents ignorés).
    var recherche = root.querySelector('.js-recherche');
    var rechercheVide = root.querySelector('.js-recherche-vide');
    function sansAccents(t) { return String(t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); }
    if (recherche) {
      recherche.addEventListener('input', function () {
        var mots = sansAccents(recherche.value).split(/\s+/).filter(Boolean);
        cartes.forEach(function (c) {
          var t = c.getAttribute('data-texte') || '';
          c.classList.toggle('rech-masque', mots.some(function (m) { return t.indexOf(m) < 0; }));
        });
        compter();
      });
    }
    // Nombre de produits affichés (catégorie, recherche et filtres allergènes).
    function compter() {
      var n = cartes.filter(function (c) { return !c.hidden && !c.classList.contains('al-masque') && !c.classList.contains('rech-masque'); }).length;
      if (rechercheVide) rechercheVide.hidden = !(recherche && recherche.value.trim() && n === 0);
      if (nombre) nombre.textContent = n;
      if (libelle) libelle.textContent = n > 1 ? 'produits' : 'produit';
    }
    root.addEventListener('schiesser:filtre', compter);

    /* ---------- fiche rapide ---------- */
    function q(s) { return sheet.querySelector(s); }
    function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
    function visibles() { return cartes.filter(function (c) { return !c.hidden && !c.classList.contains('al-masque') && !c.classList.contains('rech-masque'); }).map(function (c) { return +c.getAttribute('data-i'); }); }

    function remplir(i) {
      courant = i;
      var f = fiches[i];
      var img = q('.js-sh-img');
      if (f.image) { img.src = f.image; img.alt = f.alt || f.nom; img.style.display = ''; }
      else { img.removeAttribute('src'); img.alt = ''; img.style.display = 'none'; }
      q('.js-sh-cat').textContent = f.categorie || '';
      q('.js-sh-nom').textContent = f.nom;
      q('.js-sh-desc').textContent = f.description || '';
      q('.js-sh-lignes').innerHTML = (f.lignes || []).filter(function (l) { return l[0] || l[1]; }).map(function (l) {
        return '<div class="sh-row' + (l[2] ? ' x-extra' : '') + '"><span class="k">' + esc(l[0]) + '</span><span>' + esc(l[1]) + '</span></div>';
      }).join('');
      q('.js-sh-unite').textContent = 'Prix' + (f.unite ? ' · ' + f.unite : '');
      q('.js-sh-prix').textContent = f.prix || '';
      q('.sh-price').hidden = !f.prix;

      // Vente en ligne (WooCommerce) : panier ; sinon commande par téléphone ou message (inc/client.php).
      var action = q('.js-sh-action');
      action.hidden = !f.panier;
      if (f.panier) action.href = f.panier;
      var cmd = q('.js-cmd');
      if (cmd) {
        cmd.hidden = !!f.panier;
        cmd.setAttribute('data-produit', f.nom);
        var msg = q('.js-cmd-message');
        if (msg && window.SCHIESSER && SCHIESSER.contact) msg.href = SCHIESSER.contact + (SCHIESSER.contact.indexOf('?') < 0 ? '?' : '&') + 'produit=' + encodeURIComponent(f.nom) + '#ecrire';
        var sel = q('.js-sel');
        if (sel) {
          sel.setAttribute('data-id', f.id || ''); sel.setAttribute('data-nom', f.nom); sel.setAttribute('data-prix', f.prix || '');
          sel.setAttribute('data-unite', f.unite || ''); sel.setAttribute('data-url', f.url || '');
          if (window.SCHIESSER && SCHIESSER.majSelection) SCHIESSER.majSelection();
        }
      }
      q('.js-sh-epuise').hidden = !f.epuise;
      var part = q('.js-partage');
      if (part && window.SCHIESSER && SCHIESSER.majPartage) SCHIESSER.majPartage(part, f.url || location.href, f.nom);
      var page = q('.js-sh-page');
      page.href = f.url || '#';
      page.hidden = !f.url;
      sheet.scrollTop = 0;
      var tx = q('.sh-tx');
      if (tx) tx.scrollTop = 0;
    }

    function ouvrir(i) {
      remplir(i);
      if (!sheet.classList.contains('open')) {
        dernierFocus = document.activeElement;
        sheet.hidden = false;
        sheet.classList.add('open');
        document.body.style.overflow = 'hidden';
        q('.js-sh-fermer').focus();
      }
    }

    function voisin(pas) {
      var liste = visibles();
      if (!liste.length) return;
      var pos = liste.indexOf(courant);
      remplir(liste[(pos + pas + liste.length) % liste.length]);
    }

    function fermer() {
      sheet.classList.remove('open');
      sheet.hidden = true;
      document.body.style.overflow = '';
      if (dernierFocus) dernierFocus.focus();
    }

    cartes.forEach(function (carte) {
      carte.addEventListener('click', function (e) {
        // Ctrl/Cmd/Maj + clic ou clic molette : on laisse le navigateur ouvrir la page du produit.
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;
        e.preventDefault();
        ouvrir(+carte.getAttribute('data-i'));
      });
    });
    q('.js-sh-fermer').addEventListener('click', fermer);
    q('.js-sh-prec').addEventListener('click', function () { voisin(-1); });
    q('.js-sh-suiv').addEventListener('click', function () { voisin(1); });
    sheet.addEventListener('click', function (e) {
      if (e.target === sheet || e.target.classList.contains('sh-wrap')) fermer();
    });
    document.addEventListener('keydown', function (e) {
      if (!sheet.classList.contains('open')) return;
      if (e.key === 'Escape') { fermer(); return; }
      if (e.key === 'ArrowLeft') voisin(-1);
      if (e.key === 'ArrowRight') voisin(1);
      if (e.key === 'Tab') {
        // le focus reste dans la fiche tant qu'elle est ouverte
        var focusables = Array.prototype.slice.call(sheet.querySelectorAll('a[href]:not([hidden]),button:not([hidden])'))
          .filter(function (x) { return x.offsetParent !== null; });
        if (!focusables.length) return;
        var premier = focusables[0], dernier = focusables[focusables.length - 1];
        if (e.shiftKey && document.activeElement === premier) { e.preventDefault(); dernier.focus(); }
        else if (!e.shiftKey && document.activeElement === dernier) { e.preventDefault(); premier.focus(); }
      }
    });
  });
})();

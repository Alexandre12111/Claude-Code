/**
 * Photos communes aux langues : après l'enregistrement d'une page, si une photo a changé,
 * propose de mettre la même photo sur les autres versions (allemand, français, anglais).
 */
(function (wp, D) {
  if (!wp || !D || !D.trads || !D.trads.length) { return; }
  var data = wp.data, notices = data.dispatch('core/notices');
  var ID_AVIS = 'schiesser-photos-langues';

  /* Photos des blocs : « chemin|bloc|préfixe » => identifiant (attributs <préfixe>Id + <préfixe>Url). */
  function photos(blocs, base, out) {
    blocs.forEach(function (b, i) {
      var chemin = base.concat(i), a = b.attributes || {};
      Object.keys(a).forEach(function (k) {
        var m = k.match(/^(.+)Id$/);
        if (m && Object.prototype.hasOwnProperty.call(a, m[1] + 'Url')) {
          out[chemin.join('.') + '|' + b.name + '|' + m[1]] = parseInt(a[k], 10) || 0;
        }
      });
      if (b.innerBlocks && b.innerBlocks.length) { photos(b.innerBlocks, chemin, out); }
    });
    return out;
  }
  function etat() {
    return {
      blocs: photos(data.select('core/block-editor').getBlocks(), [], {}),
      une: parseInt(data.select('core/editor').getEditedPostAttribute('featured_media'), 10) || 0
    };
  }
  function changements(avant, apres) {
    var liste = [];
    Object.keys(apres.blocs).forEach(function (cle) {
      if (!Object.prototype.hasOwnProperty.call(avant.blocs, cle) || avant.blocs[cle] === apres.blocs[cle]) { return; }
      var p = cle.split('|');
      liste.push({ chemin: p[0], bloc: p[1], prefixe: p[2], ancien: avant.blocs[cle], nouveau: apres.blocs[cle] });
    });
    return liste;
  }

  function appliquer(cibles, liste, une) {
    notices.removeNotice(ID_AVIS);
    wp.apiFetch({ path: '/schiesser/v1/photos-langues', method: 'POST', data: { source: D.post, cibles: cibles, changements: liste, une: une } })
      .then(function (r) {
        var faits = (r || []).filter(function (x) { return x.photos > 0; });
        if (!faits.length) {
          notices.createNotice('warning', 'Aucune photo correspondante trouvée sur les autres versions : la page traduite a peut-être une mise en page différente. Changez la photo directement sur cette version.', { id: ID_AVIS, isDismissible: true });
          return;
        }
        notices.createNotice('success', 'Photo mise à jour aussi sur : ' + faits.map(function (x) { return x.langue; }).join(', ') + '.', {
          id: ID_AVIS, isDismissible: true, type: 'snackbar'
        });
      })
      .catch(function () {
        notices.createNotice('error', 'La photo n’a pas pu être recopiée sur les autres langues. Réessayez ou changez la photo directement sur chaque version.', { id: ID_AVIS, isDismissible: true });
      });
  }

  function proposer(liste, une) {
    var n = liste.length + (une >= 0 ? 1 : 0);
    var noms = D.trads.map(function (t) { return t.langue; });
    var actions = [{
      label: 'Oui, sur ' + (noms.length > 1 ? 'toutes les langues (' + noms.join(', ') + ')' : 'la version ' + noms[0]),
      onClick: function () { appliquer(D.trads.map(function (t) { return t.id; }), liste, une); }
    }];
    if (D.trads.length > 1) {
      D.trads.forEach(function (t) {
        actions.push({ label: 'Seulement ' + t.langue, onClick: function () { appliquer([t.id], liste, une); } });
      });
    }
    actions.push({ label: 'Non, seulement cette page', onClick: function () { notices.removeNotice(ID_AVIS); } });
    notices.createNotice('info', (n > 1 ? n + ' photos ont changé' : 'Une photo a changé') + ' sur cette page. Mettre la même photo sur les autres langues ? Les textes de chaque langue ne changent pas.', {
      id: ID_AVIS, isDismissible: true, actions: actions
    });
  }

  var reference = null, enregistre = false;
  data.subscribe(function () {
    var ed = data.select('core/editor');
    if (reference === null) {
      if (data.select('core/block-editor').getBlocks().length) { reference = etat(); }
      return;
    }
    var enCours = ed.isSavingPost() && !ed.isAutosavingPost();
    if (enCours && !enregistre) { enregistre = true; return; }
    if (!enCours && enregistre) {
      enregistre = false;
      if (!ed.didPostSaveRequestSucceed()) { return; }
      var apres = etat(), liste = changements(reference, apres);
      var une = apres.une !== reference.une ? apres.une : -1;
      reference = apres;
      if (liste.length || une >= 0) { proposer(liste, une); }
    }
  });
})(window.wp, window.SCHIESSER_PHOTOS);

/**
 * Fenêtre « Dans les autres langues » de l'éditeur : pour l'élément sélectionné, montre le même
 * texte sur les autres versions de la page et permet de le corriger sur place.
 */
(function (wp, D) {
  if (!wp || !wp.plugins || !D || !D.trads || !D.trads.length) { return; }
  var el = wp.element.createElement, useState = wp.element.useState, useEffect = wp.element.useEffect, useRef = wp.element.useRef;
  var C = wp.components, useSelect = wp.data.useSelect;

  var NOMS = {
    surtitre: 'Surtitre', titre: 'Titre', texte: 'Texte', note: 'Note', lead: 'Introduction', legende: 'Légende',
    bouton1Texte: 'Bouton 1', bouton2Texte: 'Bouton 2', b1Texte: 'Bouton 1', b2Texte: 'Bouton 2', boutonTexte: 'Bouton', lienTexte: 'Lien',
    imageAlt: 'Description de la photo', question: 'Question', reponse: 'Réponse', libelle: 'Libellé', detail: 'Détail',
    encartTitre: 'Titre de l’encart', sousTitre: 'Sous-titre', mention: 'Mention', conseil: 'Conseil', indication: 'Indication',
    description: 'Description', meta: 'Précision', valeur: 'Valeur', numero: 'Numéro', retenir: 'À retenir', retenirLibelle: 'Libellé « à retenir »'
  };
  function nom(cle) { return NOMS[cle] || cle.replace(/([A-Z])/g, ' $1').replace(/^./, function (c) { return c.toUpperCase(); }); }
  function estTexte(cle, v) {
    if (typeof v !== 'string' || /(Url|Lien|Id|Auto|Style)$/.test(cle)) { return false; }
    if (['hauteur', 'filtre', 'modele', 'fond', 'fondCarte', 'ancre', 'affichage', 'icone', 'auto', 'produits', 'niveau', 'align', 'className', 'accent', 'source', 'largeur', 'styleAccroche', 'numero', 'nombre', 'annee', 'afficherAu', 'afficherDu', 'schema', 'lien'].indexOf(cle) > -1) { return false; }
    return !/^(https?:\/\/|\/|#[0-9a-f]{3,8}$|mailto:|tel:)/i.test(v.trim());
  }
  /* Texte lisible dans la fenêtre : *italique*, retours à la ligne, espaces normales (sans balises). */
  function versLisible(v) {
    v = v || '';
    if (/<(?!\/?(em|br)\b)[a-z]/i.test(v)) { return v; } // autres balises (liens…) : texte tel quel
    var d = document.createElement('div');
    d.innerHTML = v.replace(/<br\s*\/?>\s*/gi, '\n').replace(/<em>/gi, '\u2063*').replace(/<\/em>/gi, '*\u2063');
    return d.textContent.replace(/\u2063/g, '').replace(/\u00a0/g, ' ');
  }
  function versHtml(t, original) {
    if (/<(?!\/?(em|br)\b)[a-z]/i.test(original || '')) { return t; }
    var echap = t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return echap.replace(/\*([^*\n]+)\*/g, '<em>$1</em>').replace(/\n/g, '<br>');
  }

  function Fenetre() {
    var sel = useSelect(function (select) {
      var be = select('core/block-editor'), id = be.getSelectedBlockClientId();
      if (!id) { return null; }
      var b = be.getBlock(id), chemin = [], x = id;
      while (x) { chemin.unshift(be.getBlockIndex(x)); x = be.getBlockRootClientId(x); }
      return { id: id, nom: b.name, attrs: b.attributes, chemin: chemin.join('.') };
    }, []);
    var s1 = useState(true), ouvert = s1[0], setOuvert = s1[1];
    var s2 = useState(null), versions = s2[0], setVersions = s2[1];
    var s3 = useState(''), cle = s3[0], setCle = s3[1];
    var s4 = useState({}), brouillons = s4[0], setBrouillons = s4[1];
    var s5 = useState({}), etats = s5[0], setEtats = s5[1];
    var prec = useRef({ id: null, attrs: null });

    var bloc = sel && sel.nom.indexOf('schiesser/') === 0 ? sel : null;
    var cleBloc = bloc ? bloc.chemin + '|' + bloc.nom : '';

    /* Textes des autres versions, chargés à chaque changement d'élément. */
    useEffect(function () {
      setVersions(null); setBrouillons({}); setEtats({});
      if (!bloc) { return; }
      var t = setTimeout(function () {
        wp.apiFetch({ path: '/schiesser/v1/textes-langues?source=' + D.post + '&chemin=' + encodeURIComponent(bloc.chemin) + '&bloc=' + encodeURIComponent(bloc.nom) })
          .then(setVersions).catch(function () { setVersions([]); });
      }, 250);
      return function () { clearTimeout(t); };
    }, [cleBloc]);

    /* Le texte qu'on vient de modifier devient le texte affiché. */
    useEffect(function () {
      if (!bloc) { return; }
      var p = prec.current;
      if (p.id === bloc.id && p.attrs) {
        Object.keys(bloc.attrs).some(function (k) {
          if (p.attrs[k] !== bloc.attrs[k] && estTexte(k, bloc.attrs[k])) { setCle(k); return true; }
          return false;
        });
      }
      prec.current = { id: bloc.id, attrs: bloc.attrs };
    });

    var cles = bloc ? Object.keys(bloc.attrs).filter(function (k) { return estTexte(k, bloc.attrs[k]) && bloc.attrs[k] !== ''; }) : [];
    var active = cles.indexOf(cle) > -1 ? cle : (cles[0] || '');

    function enregistrer(v) {
      var k = v.id + '|' + active, valeur = versHtml(brouillons[k], v.textes[active]);
      setEtats(Object.assign({}, etats, (function () { var o = {}; o[k] = 'enCours'; return o; })()));
      wp.apiFetch({ path: '/schiesser/v1/textes-langues', method: 'POST', data: { source: D.post, cible: v.id, chemin: bloc.chemin, bloc: bloc.nom, cle: active, valeur: valeur } })
        .then(function (r) {
          v.textes[active] = r.valeur;
          var o = {}; o[k] = 'ok'; setEtats(function (e) { return Object.assign({}, e, o); });
          var b = {}; b[k] = undefined; setBrouillons(function (x) { return Object.assign({}, x, b); });
          wp.data.dispatch('core/notices').createNotice('success', 'Texte enregistré sur la version ' + v.langue + '.', { type: 'snackbar', isDismissible: true });
        })
        .catch(function (e) {
          var o = {}; o[k] = (e && e.message) || 'Erreur'; setEtats(function (x) { return Object.assign({}, x, o); });
        });
    }

    var corps;
    if (!bloc) {
      corps = el('p', { className: 'stl-vide' }, 'Cliquez sur une section ou un élément de la page : ses textes dans les autres langues s’affichent ici.');
    } else if (!cles.length) {
      corps = el('p', { className: 'stl-vide' }, 'Cet élément n’a pas de texte.');
    } else {
      corps = [
        cles.length > 1 ? el(C.SelectControl, {
          key: 'champ', label: 'Texte', value: active, __nextHasNoMarginBottom: true,
          options: cles.map(function (k) { return { value: k, label: nom(k) }; }),
          onChange: setCle
        }) : el('div', { key: 'champ', className: 'stl-champ' }, nom(active)),
        el('div', { key: 'ici', className: 'stl-ici' }, el('span', null, 'Ici (' + D.langue + ')'), el('q', null, versLisible(bloc.attrs[active]))),
        /\*/.test(versLisible(bloc.attrs[active])) || /<em>/i.test(bloc.attrs[active] || '') ? el('p', { key: 'aide', className: 'stl-aide' }, 'Les mots entre *étoiles* s’affichent en italique, comme sur le site.') : null
      ];
      if (!versions) {
        corps.push(el('div', { key: 'att', className: 'stl-vide' }, el(C.Spinner), ' Lecture des autres langues…'));
      } else {
        versions.forEach(function (v) {
          var k = v.id + '|' + active;
          if (!v.textes) {
            corps.push(el('div', { key: k, className: 'stl-langue' }, el('strong', null, v.langue),
              el('p', { className: 'stl-vide' }, 'Section introuvable à cet endroit sur la version ' + v.langue + ' (mise en page différente). ',
                el('a', { href: v.lien, target: '_blank', rel: 'noopener' }, 'Ouvrir la page'))));
            return;
          }
          var html = v.textes[active] !== undefined ? v.textes[active] : '';
          var actuel = versLisible(html);
          var valeur = brouillons[k] !== undefined ? brouillons[k] : actuel;
          var etat = etats[k];
          corps.push(el('div', { key: k, className: 'stl-langue' },
            el('strong', null, v.langue),
            el(C.TextareaControl, {
              value: valeur, rows: Math.min(6, Math.max(2, Math.ceil(valeur.length / 38))), __nextHasNoMarginBottom: true,
              onChange: function (x) { var o = {}; o[k] = x; setBrouillons(Object.assign({}, brouillons, o)); var e = {}; e[k] = undefined; setEtats(Object.assign({}, etats, e)); },
              onKeyDown: function (e) { if ((e.metaKey || e.ctrlKey) && e.key === 'Enter' && valeur !== actuel) { enregistrer(v); } }
            }),
            el('div', { className: 'stl-actions' },
              el(C.Button, { variant: 'primary', size: 'small', disabled: valeur === actuel || etat === 'enCours', isBusy: etat === 'enCours', onClick: function () { enregistrer(v); } }, 'Enregistrer en ' + v.langue),
              etat === 'ok' ? el('span', { className: 'stl-ok' }, '✓ Enregistré') : null,
              etat && etat !== 'ok' && etat !== 'enCours' ? el('span', { className: 'stl-err' }, etat) : null)
          ));
        });
      }
    }

    // La zone des extensions de l'éditeur est masquée : la fenêtre est placée directement dans la page.
    return wp.element.createPortal(el('div', { className: 'schiesser-tl' + (ouvert ? '' : ' is-ferme'), role: 'region', 'aria-label': 'Textes dans les autres langues' },
      el('button', { type: 'button', className: 'stl-tete', 'aria-expanded': ouvert, onClick: function () { setOuvert(!ouvert); } },
        el('span', null, 'Dans les autres langues'), el('span', { className: 'stl-langues' }, D.trads.map(function (t) { return t.langue.slice(0, 2).toUpperCase(); }).join(' · ')), el('span', { 'aria-hidden': true }, ouvert ? '–' : '+')),
      ouvert ? el('div', { className: 'stl-corps' }, corps) : null), document.body);
  }

  wp.plugins.registerPlugin('schiesser-textes-langues', { render: Fenetre });
})(window.wp, window.SCHIESSER_PHOTOS);

/**
 * Éditeur : modifier le site plus facilement (voir inc/edition.php).
 *
 * - Panneau « Affichage programmé » sur chaque bloc : afficher du … au ….
 * - Ouverture de l'éditeur sur la section choisie depuis le site (« Modifier cette section »).
 * - Point d'intérêt des photos : composant réutilisé par les blocs (window.SchiesserPointPhoto).
 *
 * JavaScript simple, sans compilation.
 */
(function (wp) {
  'use strict';
  if (!wp || !wp.hooks || !wp.blockEditor) return;
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var useState = wp.element.useState;
  var useEffect = wp.element.useEffect;
  var be = wp.blockEditor;
  var c = wp.components;

  /* ------------------------------------------------------------------ */
  /* Affichage programmé                                                 */
  /* ------------------------------------------------------------------ */

  wp.hooks.addFilter('blocks.registerBlockType', 'schiesser/programmation-attributs', function (reglages) {
    return Object.assign({}, reglages, {
      attributes: Object.assign({}, reglages.attributes || {}, { afficherDu: { type: 'string' }, afficherAu: { type: 'string' } })
    });
  });

  function maintenant() {
    var d = new Date(), z = function (n) { return ('0' + n).slice(-2); };
    return d.getFullYear() + '-' + z(d.getMonth() + 1) + '-' + z(d.getDate()) + 'T' + z(d.getHours()) + ':' + z(d.getMinutes());
  }
  function lisible(v) {
    if (!v) return '';
    var d = new Date(v.length === 10 ? v + 'T00:00' : v);
    if (isNaN(d)) return v;
    return d.toLocaleDateString('fr-CH', { day: 'numeric', month: 'long', year: 'numeric' }) + (v.length > 10 ? ' à ' + d.toLocaleTimeString('fr-CH', { hour: '2-digit', minute: '2-digit' }) : '');
  }
  // 'avant' (pas encore affiché), 'pendant', 'apres' (plus affiché) ou '' (pas de programmation)
  function periode(a) {
    if (!a.afficherDu && !a.afficherAu) return '';
    var m = maintenant();
    if (a.afficherDu && m < a.afficherDu.slice(0, 16)) return 'avant';
    if (a.afficherAu && m > (a.afficherAu.length === 10 ? a.afficherAu + 'T23:59' : a.afficherAu.slice(0, 16))) return 'apres';
    return 'pendant';
  }
  function resume(a) {
    return (a.afficherDu ? 'du ' + lisible(a.afficherDu) : '') + (a.afficherDu && a.afficherAu ? ' ' : '') + (a.afficherAu ? 'jusqu’au ' + lisible(a.afficherAu) : '');
  }

  function PanneauProgramme(props) {
    var a = props.attributes, set = props.setAttributes;
    var etat = periode(a);
    var textes = {
      avant: 'Pas encore visible sur le site : il apparaîtra ' + resume(a) + '.',
      pendant: 'Visible sur le site en ce moment (' + resume(a) + ').',
      apres: 'Plus visible sur le site : la période est terminée.'
    };
    return el(c.PanelBody, { title: 'Affichage programmé', initialOpen: !!etat, icon: 'calendar-alt' },
      el('p', { style: { marginTop: 0, fontSize: '12px', color: '#757575' } },
        'Préparez à l’avance une section de Pâques, une photo de Noël… Elle apparaît et disparaît toute seule. Laissez vide pour l’afficher toujours.'),
      el(c.TextControl, { type: 'datetime-local', label: 'Afficher à partir du', value: a.afficherDu || '', onChange: function (v) { set({ afficherDu: v || undefined }); }, __nextHasNoMarginBottom: true }),
      el('div', { style: { height: 12 } }),
      el(c.TextControl, { type: 'datetime-local', label: 'Jusqu’au (compris)', value: a.afficherAu || '', onChange: function (v) { set({ afficherAu: v || undefined }); }, __nextHasNoMarginBottom: true }),
      etat ? el(c.Notice, { status: etat === 'pendant' ? 'success' : 'warning', isDismissible: false, className: 'sch-programme-notice' }, textes[etat]) : null,
      etat ? el(c.Button, { variant: 'link', onClick: function () { set({ afficherDu: undefined, afficherAu: undefined }); } }, 'Toujours afficher') : null);
  }

  wp.hooks.addFilter('editor.BlockEdit', 'schiesser/programmation-panneau', wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      return el(Fragment, null, el(BlockEdit, props),
        props.isSelected ? el(be.InspectorControls, null, el(PanneauProgramme, props)) : null);
    };
  }, 'avecProgrammation'));

  // Dans l'éditeur : les blocs programmés sont signalés (pointillés et étiquette).
  wp.hooks.addFilter('editor.BlockListBlock', 'schiesser/programmation-apercu', wp.compose.createHigherOrderComponent(function (BlockListBlock) {
    return function (props) {
      var etat = periode(props.attributes || {});
      if (!etat) return el(BlockListBlock, props);
      var w = props.wrapperProps || {};
      var etiquette = { avant: 'Programmé : ' + resume(props.attributes), pendant: 'Affiché ' + resume(props.attributes), apres: 'Période terminée : masqué sur le site' }[etat];
      return el(BlockListBlock, Object.assign({}, props, {
        className: [props.className, 'sch-programme', 'sch-programme--' + etat].filter(Boolean).join(' '),
        wrapperProps: Object.assign({}, w, { 'data-sch-programme': etiquette })
      }));
    };
  }, 'avecApercuProgrammation'));

  /* ------------------------------------------------------------------ */
  /* « Modifier cette section » : l'éditeur s'ouvre sur le bon bloc       */
  /* ------------------------------------------------------------------ */

  var cible = new URLSearchParams(location.search).get('schiesser_bloc');
  if (cible !== null && wp.domReady) {
    wp.domReady(function () {
      var essais = 0;
      var t = setInterval(function () {
        essais++;
        var ed = wp.data.select('core/block-editor');
        var ordre = ed && ed.getBlockOrder ? ed.getBlockOrder() : [];
        if (!ordre.length && essais < 60) return;
        clearInterval(t);
        var id = ordre[parseInt(cible, 10)];
        if (!id) return;
        wp.data.dispatch('core/block-editor').selectBlock(id);
        setTimeout(function () {
          var f = document.querySelector('iframe[name="editor-canvas"]');
          var doc = f && f.contentDocument ? f.contentDocument : document;
          var n = doc.getElementById('block-' + id);
          if (n) n.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 400);
      }, 250);
    });
  }

  /* ------------------------------------------------------------------ */
  /* Point d'intérêt des photos                                          */
  /* ------------------------------------------------------------------ */

  // Point d'intérêt enregistré sur la photo (médiathèque) : « 50% 30% ».
  function usePoint(id) {
    return wp.data.useSelect(function (s) {
      var m = id ? s('core').getMedia(id, { context: 'view' }) : null;
      return (m && m.schiesser_point) || '';
    }, [id]);
  }

  var minuterie = null;
  function PointPhoto(props) {
    var enregistre = usePoint(props.id);
    var local = useState(null);
    var enCours = useState(false);
    useEffect(function () { local[1](null); }, [props.id, enregistre]);
    if (!props.id || !props.url) return null;
    var valeur = local[0] || enregistre || '50% 50%';
    var p = valeur.replace(/%/g, '').split(' ');
    function sauver(x, y) {
      var txt = Math.round(x * 100) + '% ' + Math.round(y * 100) + '%';
      local[1](txt);
      clearTimeout(minuterie);
      minuterie = setTimeout(function () {
        enCours[1](true);
        wp.apiFetch({ path: '/wp/v2/media/' + props.id, method: 'POST', data: { schiesser_point: txt } }).then(function (m) {
          wp.data.dispatch('core').receiveEntityRecords('root', 'media', [m], undefined, false, { context: 'view' });
          enCours[1](false);
        }).catch(function () { enCours[1](false); });
      }, 500);
    }
    return el('div', { className: 'sch-point-bloc' },
      el(c.FocalPointPicker, {
        label: 'Point d’intérêt',
        url: props.url,
        value: { x: parseFloat(p[0]) / 100, y: parseFloat(p[1]) / 100 },
        onChange: function (v) { sauver(v.x, v.y); },
        help: 'Cliquez sur l’endroit important de la photo : il reste visible quand la photo est recadrée (téléphone, cartes). Réglage gardé avec la photo, partout sur le site.'
      }),
      enCours[0] ? el('p', { style: { fontSize: '12px', color: '#757575', margin: '4px 0 0' } }, 'Enregistrement…') : null);
  }
  window.SchiesserPointPhoto = PointPhoto;
  window.SchiesserUsePoint = usePoint;

  // Aperçu dans l'éditeur : photo recadrée selon son point d'intérêt.
  function PhotoPoint(props) {
    var point = usePoint(props.id);
    return el('img', Object.assign({}, props.imgProps, { style: Object.assign({}, props.imgProps.style || {}, point ? { objectPosition: point } : {}) }));
  }
  window.SchiesserPhotoPoint = PhotoPoint;

  /* Styles de l'éditeur (dans le cadre de la page) */
  function injecter() {
    var css = '.sch-programme{outline:2px dashed #b26200;outline-offset:-2px;position:relative}'
      + '.sch-programme--pendant{outline-color:#23503B}.sch-programme--apres{opacity:.55}'
      + '.sch-programme::before{content:attr(data-sch-programme);position:absolute;top:6px;left:6px;z-index:30;padding:4px 9px;border-radius:3px;background:#b26200;color:#fff;font:600 11px/1.3 system-ui,sans-serif;letter-spacing:0;text-transform:none;pointer-events:none}'
      + '.sch-programme--pendant::before{background:#23503B}';
    [document].concat(Array.prototype.map.call(document.querySelectorAll('iframe[name="editor-canvas"]'), function (f) { return f.contentDocument; })).forEach(function (d) {
      if (!d || d.getElementById('sch-programme-css')) return;
      var s = d.createElement('style'); s.id = 'sch-programme-css'; s.textContent = css; (d.head || d.body).appendChild(s);
    });
  }
  if (wp.domReady) wp.domReady(function () { injecter(); setInterval(injecter, 2000); });
})(window.wp);

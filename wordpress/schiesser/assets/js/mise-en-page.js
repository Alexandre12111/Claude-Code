/**
 * Mise en page sans code, dans l'esprit d'Elementor (voir inc/mise-en-page.php).
 *
 * - Panneau « Espacements » : espace au-dessus et en dessous d'une section, au curseur.
 * - Panneau « Boutons » : couleur, couleur du texte, taille et arrondi des boutons d'une section.
 * - Menu ⋮ d'un bloc → « Copier vers une autre page… » : la section est ajoutée au début
 *   ou à la fin de la page choisie, sans quitter la page en cours.
 *
 * JavaScript simple, sans compilation.
 */
(function (wp) {
  'use strict';
  if (!wp || !wp.hooks || !wp.blockEditor) return;
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var useState = wp.element.useState;
  var be = wp.blockEditor;
  var c = wp.components;
  var MEP = window.SCHIESSER_MEP || {};
  var SECTIONS = MEP.sections || [];
  var BOUTONS = MEP.boutons || [];

  function estSection(nom) { return SECTIONS.indexOf(nom) !== -1; }
  function aBoutons(nom) { return BOUTONS.indexOf(nom) !== -1; }

  /* Attributs (déjà déclarés côté serveur ; ici pour les blocs enregistrés sans eux). */
  wp.hooks.addFilter('blocks.registerBlockType', 'schiesser/mise-en-page-attributs', function (reglages, nom) {
    var ajout = {};
    if (estSection(nom)) { ajout.espHaut = { type: 'integer' }; ajout.espBas = { type: 'integer' }; }
    if (aBoutons(nom)) {
      ajout.btnFond = { type: 'string', 'default': '' };
      ajout.btnTexte = { type: 'string', 'default': '' };
      ajout.btnTaille = { type: 'string', 'default': '' };
      ajout.btnRayon = { type: 'integer' };
    }
    if (!Object.keys(ajout).length) return reglages;
    return Object.assign({}, reglages, { attributes: Object.assign({}, reglages.attributes || {}, ajout) });
  });

  // Même calcul que schiesser_espacement_css() : pleine taille sur grand écran, un peu plus de la moitié sur téléphone.
  function espacement(px) {
    px = Math.max(0, Math.min(320, parseInt(px, 10) || 0));
    if (!px) return '0px';
    return 'clamp(' + Math.round(px * 0.55) + 'px,' + (Math.round(px / 14.4 * 100) / 100) + 'vw,' + px + 'px)';
  }
  function num(v) { return typeof v === 'number' && !isNaN(v); }

  // Classes et variables posées sur le bloc (identiques au site : schiesser_mep_style()).
  function styleBloc(a) {
    var classes = [], style = {};
    if (num(a.espHaut)) style['--esp-h'] = espacement(a.espHaut);
    if (num(a.espBas)) style['--esp-b'] = espacement(a.espBas);
    if (a.btnFond) { classes.push('a-btn-fond'); style['--btn-fond'] = a.btnFond; }
    if (a.btnTexte) { classes.push('a-btn-texte'); style['--btn-texte'] = a.btnTexte; }
    if (a.btnTaille === 'petit' || a.btnTaille === 'grand') classes.push('a-btn-' + a.btnTaille);
    if (num(a.btnRayon)) { classes.push('a-btn-rayon'); style['--btn-rayon'] = a.btnRayon + 'px'; }
    return { classes: classes, style: style };
  }

  wp.hooks.addFilter('editor.BlockListBlock', 'schiesser/mise-en-page-apercu', wp.compose.createHigherOrderComponent(function (BlockListBlock) {
    return function (props) {
      if (!estSection(props.name) && !aBoutons(props.name)) return el(BlockListBlock, props);
      var s = styleBloc(props.attributes || {});
      if (!s.classes.length && !Object.keys(s.style).length) return el(BlockListBlock, props);
      var wp0 = props.wrapperProps || {};
      return el(BlockListBlock, Object.assign({}, props, {
        className: [props.className, s.classes.join(' ')].filter(Boolean).join(' '),
        wrapperProps: Object.assign({}, wp0, { style: Object.assign({}, wp0.style || {}, s.style) })
      }));
    };
  }, 'avecMiseEnPage'));

  function palette() {
    var r = wp.data.select('core/block-editor').getSettings().colors || [];
    return r.filter(function (x) { return x && x.color; }).map(function (x) { return { name: x.name, color: x.color }; });
  }

  function Choix(options, valeur, choisir) {
    return el('div', { className: 'sch-choix', style: { display: 'flex', gap: '4px', marginBottom: '16px' } },
      options.map(function (o) {
        return el(c.Button, {
          key: o.value, variant: valeur === o.value ? 'primary' : 'secondary',
          style: { flex: '1 1 0', justifyContent: 'center' },
          onClick: function () { choisir(o.value); }
        }, o.label);
      }));
  }

  function Etiquette(texte, info) {
    return el('p', { style: { display: 'flex', justifyContent: 'space-between', fontSize: '11px', fontWeight: 600, textTransform: 'uppercase', margin: '0 0 8px' } },
      texte, info ? el('span', { style: { fontWeight: 400, textTransform: 'none', color: '#757575' } }, info) : null);
  }

  function PanneauEspacements(props) {
    var a = props.attributes, set = props.setAttributes;
    function curseur(cle, titre) {
      var v = a[cle];
      return el(Fragment, null,
        Etiquette(titre, num(v) ? v + ' px' : 'automatique'),
        el(c.RangeControl, {
          label: titre, hideLabelFromVision: true,
          value: num(v) ? v : undefined,
          initialPosition: 144,
          min: 0, max: 320, step: 4,
          allowReset: true, resetFallbackValue: undefined,
          onChange: function (n) { var o = {}; o[cle] = (n === undefined || n === null || isNaN(n)) ? undefined : n; set(o); },
          __nextHasNoMarginBottom: true
        }),
        el('div', { style: { height: 16 } }));
    }
    return el(c.PanelBody, { title: 'Espacements', initialOpen: false },
      el('p', { style: { fontSize: '12px', color: '#757575', marginTop: 0 } },
        'Espace avant et après la section. « Automatique » garde l’espace prévu par le thème. Sur téléphone, l’espace est réduit automatiquement.'),
      curseur('espHaut', 'Au-dessus'),
      curseur('espBas', 'En dessous'),
      (num(a.espHaut) || num(a.espBas)) ? el(c.Button, { variant: 'tertiary', onClick: function () { set({ espHaut: undefined, espBas: undefined }); } }, 'Revenir aux espacements automatiques') : null);
  }

  function PanneauBoutons(props) {
    var a = props.attributes, set = props.setAttributes;
    var modifie = a.btnFond || a.btnTexte || a.btnTaille || num(a.btnRayon);
    return el(c.PanelBody, { title: 'Boutons', initialOpen: false },
      el('p', { style: { fontSize: '12px', color: '#757575', marginTop: 0 } },
        'S’applique à tous les boutons de ce bloc. Les boutons pleins prennent la couleur en fond ; les boutons à contour, en bordure (et en fond au survol).'),
      Etiquette('Couleur du bouton', a.btnFond ? null : 'celle du thème'),
      el(c.ColorPalette, { colors: palette(), value: a.btnFond || undefined, clearable: true, enableAlpha: false, onChange: function (v) { set({ btnFond: v || '' }); } }),
      el('div', { style: { height: 12 } }),
      Etiquette('Couleur du texte', a.btnTexte ? null : 'automatique'),
      el(c.ColorPalette, { colors: palette(), value: a.btnTexte || undefined, clearable: true, enableAlpha: false, onChange: function (v) { set({ btnTexte: v || '' }); } }),
      el('div', { style: { height: 12 } }),
      Etiquette('Taille'),
      Choix([{ label: 'Petit', value: 'petit' }, { label: 'Normal', value: '' }, { label: 'Grand', value: 'grand' }], a.btnTaille || '', function (v) { set({ btnTaille: v }); }),
      Etiquette('Arrondi des coins', num(a.btnRayon) ? a.btnRayon + ' px' : 'carré (thème)'),
      el(c.RangeControl, {
        label: 'Arrondi des coins', hideLabelFromVision: true,
        value: num(a.btnRayon) ? a.btnRayon : undefined, initialPosition: 0,
        min: 0, max: 40, step: 1, allowReset: true,
        onChange: function (n) { set({ btnRayon: (n === undefined || n === null || isNaN(n)) ? undefined : n }); },
        __nextHasNoMarginBottom: true
      }),
      el('div', { style: { height: 12 } }),
      modifie ? el(c.Button, { variant: 'tertiary', onClick: function () { set({ btnFond: '', btnTexte: '', btnTaille: '', btnRayon: undefined }); } }, 'Revenir aux boutons du thème') : null);
  }

  wp.hooks.addFilter('editor.BlockEdit', 'schiesser/mise-en-page-panneaux', wp.compose.createHigherOrderComponent(function (BlockEdit) {
    return function (props) {
      var sec = estSection(props.name), btn = aBoutons(props.name);
      if (!sec && !btn) return el(BlockEdit, props);
      return el(Fragment, null,
        el(BlockEdit, props),
        props.isSelected ? el(be.InspectorControls, null,
          btn ? el(PanneauBoutons, props) : null,
          sec ? el(PanneauEspacements, props) : null) : null);
    };
  }, 'avecPanneauxMiseEnPage'));

  /* ------------------------------------------------------------------ */
  /* Copier une section vers une autre page                              */
  /* ------------------------------------------------------------------ */

  function FenetreCopie(props) {
    var cible = useState(''), position = useState('fin'), etat = useState('');
    var pages = wp.data.useSelect(function (s) {
      return s('core').getEntityRecords('postType', 'page', { per_page: 100, status: ['publish', 'draft', 'private', 'future', 'pending'], orderby: 'title', order: 'asc', context: 'view', _fields: 'id,title,status' });
    }, []);
    var actuelle = wp.data.select('core/editor') ? wp.data.select('core/editor').getCurrentPostId() : 0;
    var options = [{ label: pages ? 'Choisir une page…' : 'Chargement des pages…', value: '' }].concat((pages || []).filter(function (p) { return p.id !== actuelle; }).map(function (p) {
      var t = (p.title && (p.title.rendered || p.title.raw)) || '(sans titre)';
      var d = document.createElement('textarea'); d.innerHTML = t; // entités HTML du titre
      return { label: d.value + (p.status === 'publish' ? '' : ' (brouillon)'), value: String(p.id) };
    }));
    var n = props.ids.length;

    function copier() {
      if (!cible[0]) return;
      etat[1]('encours');
      var blocs = wp.data.select('core/block-editor').getBlocksByClientId(props.ids).filter(Boolean);
      var html = wp.blocks.serialize(blocs);
      var chemin = '/wp/v2/pages/' + cible[0];
      wp.apiFetch({ path: chemin + '?context=edit&_fields=id,content,title,link' }).then(function (page) {
        var brut = (page.content && page.content.raw) || '';
        var nouveau = position[0] === 'debut' ? html + '\n\n' + brut : brut + (brut ? '\n\n' : '') + html;
        return wp.apiFetch({ path: chemin, method: 'POST', data: { content: nouveau } });
      }).then(function (page) {
        var titre = page.title && (page.title.raw || page.title.rendered);
        wp.data.dispatch('core/notices').createSuccessNotice(
          (n > 1 ? n + ' blocs copiés' : 'Section copiée') + ' dans « ' + titre + ' » (' + (position[0] === 'debut' ? 'au début' : 'à la fin') + ').',
          { type: 'snackbar', actions: [{ label: 'Ouvrir la page', url: 'post.php?post=' + page.id + '&action=edit' }] });
        props.fermer();
      }).catch(function (e) {
        etat[1]('');
        wp.data.dispatch('core/notices').createErrorNotice('La copie n’a pas pu être faite : ' + ((e && e.message) || 'erreur inconnue'), { type: 'snackbar' });
      });
    }

    return el(c.Modal, { title: n > 1 ? 'Copier ces ' + n + ' blocs vers une autre page' : 'Copier cette section vers une autre page', onRequestClose: props.fermer, className: 'sch-copie' },
      el('p', { style: { marginTop: 0, maxWidth: '420px' } }, 'Une copie est ajoutée à la page choisie, avec ses textes, photos et réglages. Cette page-ci ne change pas. Pensez à relire la page de destination.'),
      el(c.SelectControl, { label: 'Page de destination', value: cible[0], options: options, onChange: cible[1], __nextHasNoMarginBottom: true }),
      el('div', { style: { height: 16 } }),
      el(c.RadioControl, {
        label: 'Emplacement', selected: position[0], onChange: position[1],
        options: [{ label: 'À la fin de la page (avant le pied de page)', value: 'fin' }, { label: 'Au début de la page', value: 'debut' }]
      }),
      el('div', { style: { display: 'flex', gap: '8px', justifyContent: 'flex-end', marginTop: '24px' } },
        el(c.Button, { variant: 'tertiary', onClick: props.fermer }, 'Annuler'),
        el(c.Button, { variant: 'primary', onClick: copier, disabled: !cible[0] || etat[0] === 'encours', isBusy: etat[0] === 'encours' }, 'Copier')));
  }

  function MenuCopie() {
    var ids = useState(null);
    return el(Fragment, null,
      el(be.BlockSettingsMenuControls, null, function (f) {
        var sel = (f && f.selectedClientIds) || [];
        return el(c.MenuItem, {
          icon: 'admin-page',
          onClick: function () { ids[1](sel.slice()); if (f.onClose) f.onClose(); }
        }, 'Copier vers une autre page…');
      }),
      ids[0] ? el(FenetreCopie, { ids: ids[0], fermer: function () { ids[1](null); } }) : null);
  }

  if (wp.plugins && be.BlockSettingsMenuControls) {
    wp.plugins.registerPlugin('schiesser-copie-section', { render: MenuCopie });
  }
})(window.wp);

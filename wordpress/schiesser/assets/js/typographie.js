/**
 * Typographie et couleur au clic sur le texte, dans tous les textes de l'éditeur.
 *
 * On sélectionne des mots, puis bouton « Aa » de la barre d'outils :
 * police des titres ou du texte, taille, majuscules espacées, couleur de la charte.
 * Le résultat est une simple classe (<span class="t-grande">…) : le design reste
 * cohérent, et si la charte change (Réglages maison), les textes suivent.
 *
 * JavaScript simple, sans compilation.
 */
(function (wp) {
  'use strict';
  if (!wp || !wp.richText || !wp.blockEditor) return;
  var el = wp.element.createElement;
  var RT = wp.richText;
  var be = wp.blockEditor;
  var c = wp.components;

  // Mises en forme proposées : une classe chacune. Dans un même groupe, une seule à la fois.
  var FORMATS = [
    { nom: 'schiesser/police-titres', classe: 'f-titres', titre: 'Police des titres', groupe: 'police' },
    { nom: 'schiesser/police-texte', classe: 'f-texte', titre: 'Police du texte', groupe: 'police' },
    { nom: 'schiesser/taille-petite', classe: 't-petite', titre: 'Plus petit', groupe: 'taille' },
    { nom: 'schiesser/taille-grande', classe: 't-grande', titre: 'Plus grand', groupe: 'taille' },
    { nom: 'schiesser/taille-tres-grande', classe: 't-tres-grande', titre: 'Beaucoup plus grand', groupe: 'taille' },
    { nom: 'schiesser/majuscules', classe: 'f-maj', titre: 'Majuscules espacées', groupe: 'maj' }
  ];

  FORMATS.forEach(function (f) {
    if (!RT.getFormatType || !RT.getFormatType(f.nom)) {
      RT.registerFormatType(f.nom, { title: f.titre, tagName: 'span', className: f.classe, edit: function () { return null; } });
    }
  });

  var ICONE = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24', 'aria-hidden': true },
    el('text', { x: 2, y: 17, fontFamily: 'Georgia, serif', fontSize: 15, fill: 'currentColor' }, 'A'),
    el('text', { x: 12.5, y: 17, fontFamily: 'Arial, sans-serif', fontSize: 11, fill: 'currentColor' }, 'a'));

  function palette() {
    var r = wp.data.select('core/block-editor').getSettings().colors || [];
    return r.filter(function (x) { return x && x.color; });
  }

  function pastille(couleur) {
    return el('span', { style: { display: 'inline-block', width: 16, height: 16, borderRadius: '50%', background: couleur, boxShadow: 'inset 0 0 0 1px rgba(0,0,0,.2)' } });
  }

  function Menu(props) {
    var value = props.value;
    var onChange = props.onChange;
    var actifs = (RT.getActiveFormats(value) || []).map(function (f) { return f.type; });

    function basculer(f) {
      var v = value;
      FORMATS.forEach(function (g) {
        if (g.groupe === f.groupe && g.nom !== f.nom) v = RT.removeFormat(v, g.nom);
      });
      onChange(RT.toggleFormat(v, { type: f.nom }));
    }
    function colorer(p) {
      onChange(RT.applyFormat(value, {
        type: 'core/text-color',
        attributes: { style: 'color:' + p.color, class: 'has-' + p.slug + '-color' }
      }));
    }
    function effacer() {
      var v = value;
      FORMATS.forEach(function (g) { v = RT.removeFormat(v, g.nom); });
      onChange(RT.removeFormat(v, 'core/text-color'));
    }

    var controles = [
      FORMATS.filter(function (f) { return f.groupe === 'police'; }).map(function (f) {
        return { title: f.titre, isActive: actifs.indexOf(f.nom) !== -1, onClick: function () { basculer(f); } };
      }),
      FORMATS.filter(function (f) { return f.groupe !== 'police'; }).map(function (f) {
        return { title: f.titre, isActive: actifs.indexOf(f.nom) !== -1, onClick: function () { basculer(f); } };
      }),
      palette().map(function (p) {
        return { title: 'Couleur : ' + p.name, icon: pastille(p.color), onClick: function () { colorer(p); } };
      }),
      [{ title: 'Retirer police, taille et couleur', icon: 'editor-removeformatting', onClick: effacer }]
    ];

    return el(be.BlockControls, { group: 'inline' },
      el(c.ToolbarGroup, null,
        el(c.ToolbarDropdownMenu, {
          icon: ICONE,
          label: 'Typographie et couleur (sélectionnez d’abord le texte)',
          controls: controles
        })));
  }

  // Le menu « Aa » est porté par un format à part (jamais appliqué au texte).
  if (!RT.getFormatType || !RT.getFormatType('schiesser/typographie')) {
    RT.registerFormatType('schiesser/typographie', {
      title: 'Typographie et couleur',
      tagName: 'span',
      className: 'sch-typographie',
      edit: Menu
    });
  }

  // Paragraphes, titres, listes : police, taille et graisse visibles d'emblée dans le panneau « Typographie ».
  if (wp.hooks) {
    wp.hooks.addFilter('blocks.registerBlockType', 'schiesser/typographie-visible', function (reglages, nom) {
      if (['core/paragraph', 'core/heading', 'core/list', 'core/quote'].indexOf(nom) === -1) return reglages;
      var t = reglages.supports && reglages.supports.typography;
      if (!t) return reglages;
      var defaut = Object.assign({}, t.__experimentalDefaultControls || {}, { fontSize: true, fontFamily: true, fontAppearance: true });
      return Object.assign({}, reglages, {
        supports: Object.assign({}, reglages.supports, { typography: Object.assign({}, t, { __experimentalDefaultControls: defaut }) })
      });
    });
  }

  // Liste utilisée par les blocs maison pour autoriser ces mises en forme.
  window.SCHIESSER_TYPO = ['schiesser/typographie', 'core/text-color'].concat(FORMATS.map(function (f) { return f.nom; }));
})(window.wp);

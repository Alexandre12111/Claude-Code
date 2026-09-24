/**
 * Panneau « Aa » : police, taille, graisse, style et couleur des textes, façon Elementor.
 *
 * On clique dans un texte (ou on sélectionne quelques mots), puis sur « Aa » dans la barre
 * d'outils : un panneau s'ouvre avec des curseurs et des boutons, et le texte change en direct.
 * Sans sélection, le réglage s'applique à tout le texte du champ.
 *
 * Tout est enregistré dans une seule balise <span class="sch-style" style="…"> ; la couleur
 * reste le format natif de WordPress. Les tailles sont relatives (100 % = taille normale),
 * donc elles s'adaptent au mobile. Les polices proposées sont celles de la charte : si elle
 * change (Réglages maison), les textes suivent.
 *
 * JavaScript simple, sans compilation.
 */
(function (wp) {
  'use strict';
  if (!wp || !wp.richText || !wp.blockEditor) return;
  var el = wp.element.createElement;
  var useState = wp.element.useState;
  var Fragment = wp.element.Fragment;
  var RT = wp.richText;
  var be = wp.blockEditor;
  var c = wp.components;

  var STYLE = 'schiesser/style';
  var COULEUR = 'core/text-color';

  // Anciennes mises en forme (versions 0.4) : toujours reconnues, remplacées dès qu'on retouche le texte.
  var ANCIENS = [
    { nom: 'schiesser/police-titres', classe: 'f-titres', titre: 'Police des titres', prop: 'font-family' },
    { nom: 'schiesser/police-texte', classe: 'f-texte', titre: 'Police du texte', prop: 'font-family' },
    { nom: 'schiesser/taille-petite', classe: 't-petite', titre: 'Plus petit', prop: 'font-size' },
    { nom: 'schiesser/taille-grande', classe: 't-grande', titre: 'Plus grand', prop: 'font-size' },
    { nom: 'schiesser/taille-tres-grande', classe: 't-tres-grande', titre: 'Beaucoup plus grand', prop: 'font-size' },
    { nom: 'schiesser/majuscules', classe: 'f-maj', titre: 'Majuscules espacées', prop: 'text-transform' }
  ];
  ANCIENS.forEach(function (f) {
    if (!RT.getFormatType || !RT.getFormatType(f.nom)) {
      RT.registerFormatType(f.nom, { title: f.titre, tagName: 'span', className: f.classe, edit: function () { return null; } });
    }
  });
  if (!RT.getFormatType || !RT.getFormatType(STYLE)) {
    RT.registerFormatType(STYLE, {
      title: 'Style du texte',
      tagName: 'span',
      className: 'sch-style',
      attributes: { style: 'style' },
      edit: function () { return null; }
    });
  }

  // Ordre d'écriture des propriétés dans l'attribut style.
  var ORDRE = ['font-family', 'font-size', 'font-weight', 'font-style', 'text-transform', 'letter-spacing', 'text-decoration'];

  var POLICES = [
    { titre: 'Par défaut', valeur: null },
    { titre: 'Titres', valeur: 'var(--serif)', apercu: 'var(--serif, Georgia), serif' },
    { titre: 'Texte', valeur: 'var(--sans)', apercu: 'var(--sans, Arial), sans-serif' }
  ];
  var TAILLES = [
    { titre: 'S', aide: 'Petit (80 %)', valeur: 80 },
    { titre: 'M', aide: 'Normal (100 %)', valeur: 100 },
    { titre: 'L', aide: 'Grand (130 %)', valeur: 130 },
    { titre: 'XL', aide: 'Très grand (170 %)', valeur: 170 },
    { titre: 'XXL', aide: 'Énorme (230 %)', valeur: 230 }
  ];
  var GRAISSES = [
    { titre: 'Fin', valeur: '300' },
    { titre: 'Normal', valeur: '400' },
    { titre: 'Moyen', valeur: '500' },
    { titre: 'Gras', valeur: '700' }
  ];

  // Style copié (bouton « Copier le style »), partagé entre tous les champs.
  var presse = null;

  function lireStyle(txt) {
    var o = {};
    String(txt || '').split(';').forEach(function (d) {
      var i = d.indexOf(':');
      if (i > 0) o[d.slice(0, i).trim().toLowerCase()] = d.slice(i + 1).trim();
    });
    return o;
  }
  function ecrireStyle(o) {
    return ORDRE.filter(function (k) { return o[k]; }).map(function (k) { return k + ':' + o[k]; }).join(';');
  }

  function formatA(value, i, type) {
    var liste = (value.formats && value.formats[i]) || [];
    for (var n = 0; n < liste.length; n++) if (liste[n] && liste[n].type === type) return liste[n];
    return null;
  }

  // Plage concernée : la sélection, ou tout le champ si rien n'est sélectionné.
  function plage(value) {
    var s = value.start, e = value.end;
    if (typeof s !== 'number' || typeof e !== 'number' || s === e) return { s: 0, e: value.text.length, tout: true };
    return { s: Math.min(s, e), e: Math.max(s, e), tout: false };
  }

  // Ce qu'affiche le panneau : le style du premier caractère de la plage (anciens formats compris).
  function styleActuel(value, p) {
    var f = formatA(value, p.s, STYLE);
    var o = lireStyle(f && f.attributes && f.attributes.style);
    if (!o['font-family'] && formatA(value, p.s, 'schiesser/police-titres')) o['font-family'] = 'var(--serif)';
    if (!o['font-family'] && formatA(value, p.s, 'schiesser/police-texte')) o['font-family'] = 'var(--sans)';
    if (!o['font-size'] && formatA(value, p.s, 'schiesser/taille-petite')) o['font-size'] = '0.82em';
    if (!o['font-size'] && formatA(value, p.s, 'schiesser/taille-grande')) o['font-size'] = '1.25em';
    if (!o['font-size'] && formatA(value, p.s, 'schiesser/taille-tres-grande')) o['font-size'] = '1.6em';
    if (!o['text-transform'] && formatA(value, p.s, 'schiesser/majuscules')) { o['text-transform'] = 'uppercase'; o['letter-spacing'] = o['letter-spacing'] || '0.14em'; }
    var col = formatA(value, p.s, COULEUR);
    o.couleur = col && col.attributes ? (lireStyle(col.attributes.style).color || null) : null;
    return o;
  }

  // Applique des changements de style morceau par morceau : ce qui n'est pas modifié est conservé.
  function appliquerStyle(value, p, changes) {
    var v = value;
    var i = p.s;
    while (i < p.e) {
      var f = formatA(v, i, STYLE);
      var cle = f && f.attributes ? f.attributes.style || '' : '';
      var j = i + 1;
      while (j < p.e) {
        var g = formatA(v, j, STYLE);
        if ((g && g.attributes ? g.attributes.style || '' : '') !== cle) break;
        j++;
      }
      var o = lireStyle(cle);
      // Les anciennes classes sont reprises dans le style pour ne rien perdre.
      var ancien = styleActuel(v, { s: i, e: j });
      ['font-family', 'font-size', 'text-transform', 'letter-spacing'].forEach(function (k) { if (!o[k] && ancien[k]) o[k] = ancien[k]; });
      Object.keys(changes).forEach(function (k) { if (changes[k] === null || changes[k] === '') delete o[k]; else o[k] = changes[k]; });
      ANCIENS.forEach(function (a) { v = RT.removeFormat(v, a.nom, i, j); });
      v = RT.removeFormat(v, STYLE, i, j);
      var txt = ecrireStyle(o);
      if (txt) v = RT.applyFormat(v, { type: STYLE, attributes: { style: txt } }, i, j);
      i = j;
    }
    return v;
  }

  function appliquerCouleur(value, p, hex) {
    var v = RT.removeFormat(value, COULEUR, p.s, p.e);
    if (!hex) return v;
    var attrs = { style: 'color:' + hex };
    var trouve = palette().filter(function (x) { return String(x.color).toLowerCase() === String(hex).toLowerCase(); })[0];
    if (trouve) attrs['class'] = 'has-' + trouve.slug + '-color';
    return RT.applyFormat(v, { type: COULEUR, attributes: attrs }, p.s, p.e);
  }

  function palette() {
    var r = wp.data.select('core/block-editor').getSettings().colors || [];
    return r.filter(function (x) { return x && x.color; });
  }

  var ICONE = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24', 'aria-hidden': true },
    el('text', { x: 2, y: 17, fontFamily: 'Georgia, serif', fontSize: 15, fill: 'currentColor' }, 'A'),
    el('text', { x: 12.5, y: 17, fontFamily: 'Arial, sans-serif', fontSize: 11, fill: 'currentColor' }, 'a'));

  // Mise en page du panneau (il s'affiche dans la page d'administration, hors du cadre d'édition).
  function injecterCss() {
    if (document.getElementById('sch-panneau-css')) return;
    var s = document.createElement('style');
    s.id = 'sch-panneau-css';
    s.textContent = [
      '.sch-panneau .components-popover__content{width:320px;max-width:calc(100vw - 24px);padding:0;max-height:min(80vh,640px);overflow:auto}',
      '.sch-panneau__tete{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:12px 16px;border-bottom:1px solid #e0e0e0;position:sticky;top:0;background:#fff;z-index:1}',
      '.sch-panneau__tete strong{font-size:13px}',
      '.sch-panneau__cible{font-size:12px;color:#555;padding:8px 16px 0;margin:0}',
      '.sch-panneau__zone{padding:12px 16px;border-bottom:1px solid #f0f0f0}',
      '.sch-panneau__zone:last-child{border-bottom:0}',
      '.sch-panneau__titre{display:flex;justify-content:space-between;align-items:center;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#1e1e1e;margin:0 0 8px}',
      '.sch-panneau__titre span{font-weight:400;text-transform:none;letter-spacing:0;color:#757575}',
      '.sch-choix{display:flex;gap:4px;flex-wrap:wrap}',
      '.sch-choix .components-button{flex:1 1 0;justify-content:center;min-width:0;height:34px;padding:0 6px;border:1px solid #ddd;border-radius:4px;background:#fff;color:#1e1e1e}',
      '.sch-choix .components-button.is-pressed{background:#1e1e1e;color:#fff;border-color:#1e1e1e}',
      '.sch-taille{display:flex;align-items:center;gap:6px}',
      '.sch-taille .components-range-control{flex:1}',
      '.sch-taille .components-base-control__field{margin-bottom:0}',
      '.sch-taille .components-button{width:32px;height:32px;min-width:32px;justify-content:center;border:1px solid #ddd;border-radius:4px;font-size:18px;padding:0}',
      '.sch-panneau .components-range-control{margin-bottom:0}',
      '.sch-panneau__pied{display:flex;gap:6px;flex-wrap:wrap;padding:12px 16px}',
      '.sch-panneau__pied .components-button{flex:1 1 auto;justify-content:center}'
    ].join('\n');
    document.head.appendChild(s);
  }

  function Choix(props) {
    return el('div', { className: 'sch-choix', role: 'group', 'aria-label': props.label },
      props.options.map(function (o) {
        return el(c.Button, {
          key: o.titre,
          isPressed: props.actif(o),
          label: o.aide,
          showTooltip: !!o.aide,
          style: o.style,
          onClick: function () { props.choisir(o); }
        }, o.titre);
      }));
  }

  function Zone(titre, info, contenu) {
    return el('div', { className: 'sch-panneau__zone' },
      el('p', { className: 'sch-panneau__titre' }, titre, info ? el('span', null, info) : null),
      contenu);
  }

  function Panneau(props) {
    var value = props.value;
    var onChange = props.onChange;
    var p = plage(value);
    var st = styleActuel(value, p);
    var taille = st['font-size'] && /em$/.test(st['font-size']) ? Math.round(parseFloat(st['font-size']) * 100) : null;
    var espace = st['letter-spacing'] && /em$/.test(st['letter-spacing']) ? Math.round(parseFloat(st['letter-spacing']) * 100) : 0;
    var vide = !value.text.length;

    function maj(changes) { onChange(appliquerStyle(value, p, changes)); }
    function majTaille(pct) {
      if (pct === null || pct === undefined || isNaN(pct)) return maj({ 'font-size': null });
      pct = Math.max(30, Math.min(500, Math.round(pct)));
      maj({ 'font-size': pct === 100 ? null : (pct / 100) + 'em' });
    }
    function bascule(prop, oui) { var o = {}; o[prop] = st[prop] === oui ? null : oui; maj(o); }

    function toutRetirer() {
      var v = appliquerStyle(value, p, { 'font-family': null, 'font-size': null, 'font-weight': null, 'font-style': null, 'text-transform': null, 'letter-spacing': null, 'text-decoration': null });
      onChange(appliquerCouleur(v, p, null));
    }
    function copier() {
      presse = Object.assign({}, st);
      props.rafraichir();
    }
    function coller() {
      if (!presse) return;
      var changes = {};
      ORDRE.forEach(function (k) { changes[k] = presse[k] || null; });
      onChange(appliquerCouleur(appliquerStyle(value, p, changes), p, presse.couleur));
    }

    var contenu = vide ? el('p', { className: 'sch-panneau__cible' }, 'Écrivez d’abord un texte dans ce champ.') : el(Fragment, null,
      el('p', { className: 'sch-panneau__cible' }, p.tout
        ? 'S’applique à tout le texte de ce champ. Pour ne modifier que quelques mots, sélectionnez-les d’abord.'
        : 'S’applique aux mots sélectionnés (' + (p.e - p.s) + ' caractères).'),

      Zone('Police', null, el(Choix, {
        label: 'Police',
        options: POLICES.map(function (o) { return Object.assign({ style: o.apercu ? { fontFamily: o.apercu, fontSize: '15px' } : null }, o); }),
        actif: function (o) { return (st['font-family'] || null) === o.valeur; },
        choisir: function (o) { maj({ 'font-family': o.valeur }); }
      })),

      Zone('Taille', taille ? taille + ' %' : '100 % (normale)', el(Fragment, null,
        el('div', { className: 'sch-taille' },
          el(c.Button, { label: 'Plus petit', showTooltip: true, onClick: function () { majTaille((taille || 100) - 10); } }, '−'),
          el(c.RangeControl, {
            label: 'Taille en pourcentage de la taille normale',
            hideLabelFromVision: true,
            value: taille || 100,
            min: 30, max: 400, step: 5,
            withInputField: true,
            onChange: majTaille,
            __nextHasNoMarginBottom: true
          }),
          el(c.Button, { label: 'Plus grand', showTooltip: true, onClick: function () { majTaille((taille || 100) + 10); } }, '+')),
        el('div', { style: { height: 8 } }),
        el(Choix, {
          label: 'Tailles rapides',
          options: TAILLES,
          actif: function (o) { return (taille || 100) === o.valeur; },
          choisir: function (o) { majTaille(o.valeur); }
        }))),

      Zone('Graisse', null, el(Choix, {
        label: 'Graisse',
        options: GRAISSES.map(function (o) { return Object.assign({ style: { fontWeight: o.valeur } }, o); }),
        actif: function (o) { return (st['font-weight'] || null) === o.valeur; },
        choisir: function (o) { maj({ 'font-weight': st['font-weight'] === o.valeur ? null : o.valeur }); }
      })),

      Zone('Style', null, el(Choix, {
        label: 'Style',
        options: [
          { titre: 'Italique', prop: 'font-style', oui: 'italic', style: { fontStyle: 'italic' } },
          { titre: 'MAJUSCULES', prop: 'text-transform', oui: 'uppercase', style: { fontSize: '11px', letterSpacing: '.08em' } },
          { titre: 'Souligné', prop: 'text-decoration', oui: 'underline', style: { textDecoration: 'underline' } }
        ],
        actif: function (o) { return st[o.prop] === o.oui; },
        choisir: function (o) {
          if (o.prop === 'text-transform' && st[o.prop] !== o.oui && !st['letter-spacing']) maj({ 'text-transform': 'uppercase', 'letter-spacing': '0.1em' });
          else bascule(o.prop, o.oui);
        }
      })),

      Zone('Espacement des lettres', espace ? espace + '' : 'normal', el(c.RangeControl, {
        label: 'Espacement des lettres',
        hideLabelFromVision: true,
        value: espace,
        min: -5, max: 40, step: 1,
        withInputField: true,
        allowReset: false,
        onChange: function (n) { maj({ 'letter-spacing': n ? (n / 100) + 'em' : null }); },
        __nextHasNoMarginBottom: true
      })),

      Zone('Couleur', st.couleur ? null : 'couleur normale', el(c.ColorPalette, {
        colors: palette().map(function (x) { return { name: x.name, color: x.color }; }),
        value: st.couleur || undefined,
        onChange: function (hex) { onChange(appliquerCouleur(value, p, hex || null)); },
        enableAlpha: false,
        clearable: true,
        __experimentalIsRenderedInSidebar: true
      })),

      el('div', { className: 'sch-panneau__pied' },
        el(c.Button, { variant: 'secondary', onClick: copier }, 'Copier le style'),
        el(c.Button, { variant: 'secondary', onClick: coller, disabled: !presse }, 'Coller le style'),
        el(c.Button, { variant: 'tertiary', isDestructive: true, onClick: toutRetirer }, 'Tout remettre par défaut'))
    );

    return el(c.Popover, {
      className: 'sch-panneau',
      anchor: props.ancre || undefined,
      placement: 'bottom-start',
      offset: 8,
      focusOnMount: false,
      shift: true,
      onClose: props.fermer
    },
      el('div', { className: 'sch-panneau__tete' },
        el('strong', null, 'Texte : police, taille, couleur'),
        el(c.Button, { icon: 'no-alt', label: 'Fermer', size: 'small', onClick: props.fermer })),
      contenu);
  }

  var fermeLe = 0;

  function Barre(props) {
    var etat = useState(false);
    var ouvert = etat[0], setOuvert = etat[1];
    var tic = useState(0);
    var ancre = props.contentRef && props.contentRef.current;

    function fermer() { fermeLe = Date.now(); setOuvert(false); }
    function basculer() {
      if (!ouvert && Date.now() - fermeLe < 250) return; // le clic qui vient de fermer le panneau
      injecterCss();
      setOuvert(!ouvert);
    }

    return el(Fragment, null,
      el(be.BlockControls, { group: 'inline' },
        el(c.ToolbarGroup, null,
          el(c.ToolbarButton, {
            icon: ICONE,
            label: 'Texte : police, taille, couleur',
            showTooltip: true,
            isPressed: ouvert,
            onClick: basculer
          }))),
      ouvert ? el(Panneau, {
        value: props.value,
        onChange: props.onChange,
        ancre: ancre,
        fermer: fermer,
        rafraichir: function () { tic[1](tic[0] + 1); }
      }) : null);
  }

  // Le bouton « Aa » est porté par un format à part (jamais appliqué au texte).
  if (!RT.getFormatType || !RT.getFormatType('schiesser/typographie')) {
    RT.registerFormatType('schiesser/typographie', {
      title: 'Texte : police, taille, couleur',
      tagName: 'span',
      className: 'sch-typographie',
      edit: Barre
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
  window.SCHIESSER_TYPO = ['schiesser/typographie', STYLE, COULEUR].concat(ANCIENS.map(function (f) { return f.nom; }));
})(window.wp);

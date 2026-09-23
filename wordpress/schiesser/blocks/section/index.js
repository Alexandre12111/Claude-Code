/**
 * Bloc Section : édition dans l'éditeur.
 * En-tête modifiable directement (titre, note), réglages à droite (fond, largeur…),
 * et un contenu libre : on y ajoute n'importe quel bloc avec le bouton +.
 */
(function (wp) {
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;

  var FONDS = { papier: '', clair: 'bg-soft', alterne: 'bg-alt', sable: 'bg-deep', sombre: 'bg-ink' };
  var AUTORISES = [
    'core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/image', 'core/gallery',
    'core/columns', 'core/group', 'core/buttons', 'core/separator', 'core/spacer', 'core/table',
    'core/details', 'core/html', 'core/shortcode', 'core/embed', 'core/video', 'core/file',
    'core/media-text', 'core/pullquote'
  ];

  wp.blocks.registerBlockType('schiesser/section', {
    edit: function (props) {
      var a = props.attributes;
      var set = props.setAttributes;
      var fond = FONDS.hasOwnProperty(a.fond) ? a.fond : 'papier';
      var blockProps = be.useBlockProps({ className: ('sec sec-libre sec--' + fond + ' ' + FONDS[fond]).trim() });
      var innerProps = be.useInnerBlocksProps(
        { className: 'sec-body sec-body--' + (a.largeur === 'lecture' ? 'lecture' : 'large') + (a.centre ? ' sec-body--centre' : '') },
        {
          allowedBlocks: AUTORISES,
          template: [['core/paragraph', { placeholder: 'Écrivez ici, ou cliquez sur + pour ajouter une image, des colonnes, un bouton…' }]],
          templateLock: false
        }
      );

      return el(Fragment, null,
        el(be.InspectorControls, null,
          el(c.PanelBody, { title: 'Apparence de la section', initialOpen: true },
            el(c.SelectControl, {
              label: 'Fond', value: fond,
              options: [
                { label: 'Papier (fond de la page)', value: 'papier' },
                { label: 'Clair', value: 'clair' },
                { label: 'Crème soutenu', value: 'alterne' },
                { label: 'Sable', value: 'sable' },
                { label: 'Sombre (chocolat)', value: 'sombre' }
              ],
              help: 'Alternez les fonds d’une section à l’autre pour rythmer la page.',
              onChange: function (v) { set({ fond: v }); }
            }),
            el(c.SelectControl, {
              label: 'Largeur du contenu', value: a.largeur || 'large',
              options: [
                { label: 'Large (colonnes, grilles)', value: 'large' },
                { label: 'Lecture (texte long)', value: 'lecture' }
              ],
              onChange: function (v) { set({ largeur: v }); }
            }),
            el(c.ToggleControl, {
              label: 'Centrer le contenu', checked: !!a.centre,
              onChange: function (v) { set({ centre: v }); }
            })
          ),
          el(c.PanelBody, { title: 'En-tête', initialOpen: true },
            el(c.ToggleControl, {
              label: 'Afficher l’en-tête (N°, titre, note)', checked: a.entete !== false,
              onChange: function (v) { set({ entete: v }); }
            }),
            el(c.TextControl, {
              label: 'Numéro (ex. 01)', value: a.numero,
              help: 'Laissez vide pour n’afficher que le losange et le filet.',
              onChange: function (v) { set({ numero: v }); }
            }),
            el(c.TextControl, {
              label: 'Ancre (lien direct #…)', value: a.ancre,
              help: 'Ex. « horaires » : un bouton peut alors pointer vers #horaires.',
              onChange: function (v) { set({ ancre: v }); }
            })
          )
        ),
        el('section', blockProps,
          el('div', { className: 'wrap' },
            a.entete !== false ? el('div', { className: 'sec-head' + (a.centre ? ' sec-head--centre' : '') },
              el('span', { className: 'idx' + (a.numero ? '' : ' idx--vide') }, el('i', { className: 'x-diamond' }), a.numero),
              el(be.RichText, {
                tagName: 'h2', value: a.titre, allowedFormats: ['core/italic'], placeholder: 'Titre de la section',
                onChange: function (v) { set({ titre: v }); }
              }),
              el(be.RichText, {
                tagName: 'p', className: 'note', value: a.note, allowedFormats: [], placeholder: 'Petite note (facultatif)',
                onChange: function (v) { set({ note: v }); }
              })
            ) : null,
            el('div', innerProps)
          )
        )
      );
    },
    save: function () { return el(be.InnerBlocks.Content); }
  });
})(window.wp);

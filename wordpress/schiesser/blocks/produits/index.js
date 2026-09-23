/**
 * Bloc « Grille des produits » : édition dans l'éditeur.
 * Le titre et la note se modifient directement ; la grille est un aperçu
 * réel des produits saisis dans le menu « Produits ».
 */
(function (wp) {
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;
  var ServerSideRender = wp.serverSideRender;

  wp.blocks.registerBlockType('schiesser/produits', {
    edit: function (props) {
      var a = props.attributes;
      var set = props.setAttributes;
      var blockProps = be.useBlockProps({ className: 'sec' });

      return el(Fragment, null,
        el(be.InspectorControls, null,
          el(c.PanelBody, { title: 'Section' },
            el(c.TextControl, {
              label: 'Numéro de section', value: a.numero,
              onChange: function (v) { set({ numero: v }); }
            }),
            el(c.TextControl, {
              label: 'Ancre (pour les liens #…)', help: 'Ex. « catalogue » : un bouton peut pointer vers #catalogue.',
              value: a.ancre, onChange: function (v) { set({ ancre: v }); }
            })
          ),
          el(c.PanelBody, { title: 'Produits' },
            el('p', null, 'Les produits se gèrent dans le menu « Produits » de l’administration. Toute modification apparaît ici automatiquement.'),
            el(c.Button, { variant: 'secondary', href: 'edit.php?post_type=schiesser_produit', target: '_blank' }, 'Gérer les produits')
          )
        ),
        el('section', blockProps,
          el('div', { className: 'wrap' },
            el('div', { className: 'sec-head' },
              el('span', { className: 'idx' }, el('i', { className: 'x-diamond' }), a.numero),
              el(be.RichText, {
                tagName: 'h2', value: a.titre, allowedFormats: ['core/italic'], placeholder: 'Titre de la section',
                onChange: function (v) { set({ titre: v }); }
              }),
              el(be.RichText, {
                tagName: 'p', className: 'note', value: a.note, allowedFormats: [], placeholder: 'Petite note (facultatif)',
                onChange: function (v) { set({ note: v }); }
              })
            ),
            el('div', { style: { pointerEvents: 'none' } },
              el(ServerSideRender, { block: 'schiesser/produits', attributes: { apercu: true } })
            )
          )
        )
      );
    },
    save: function () { return null; }
  });
})(window.wp);

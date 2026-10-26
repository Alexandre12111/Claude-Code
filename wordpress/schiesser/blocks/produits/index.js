/**
 * Bloc « Grille des produits » : édition dans l'éditeur.
 * Le titre et la note se modifient directement ; la grille est un aperçu
 * réel des produits saisis dans le menu « Produits » (ou dans WooCommerce).
 */
(function (wp) {
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;
  var ServerSideRender = wp.serverSideRender;

  var FONDS = { papier: '', clair: 'bg-soft', alterne: 'bg-alt', sable: 'bg-deep' };

  wp.blocks.registerBlockType('schiesser/produits', {
    edit: function (props) {
      var a = props.attributes;
      var set = props.setAttributes;
      var fond = FONDS.hasOwnProperty(a.fond) ? a.fond : 'papier';
      var blockProps = be.useBlockProps({ className: ('sec sec-produits ' + FONDS[fond]).trim() });

      return el(Fragment, null,
        el(be.InspectorControls, null,
          el(c.PanelBody, { title: 'Produits affichés', initialOpen: true },
            el(c.RangeControl, {
              label: 'Nombre de produits', value: a.limite, min: 0, max: 24,
              help: a.limite ? 'Les ' + a.limite + ' premiers produits (dans l’ordre du menu « Produits »).' : '0 = tous les produits.',
              onChange: function (v) { set({ limite: v || 0 }); }
            }),
            el(c.ToggleControl, {
              label: 'Afficher les filtres par catégorie', checked: !!a.filtres,
              help: 'Conseillé sur la page Boutique ; à désactiver pour une petite sélection.',
              onChange: function (v) { set({ filtres: v }); }
            }),
            el(c.SelectControl, {
              label: 'Source des produits', value: a.source,
              options: [
                { label: 'Automatique (WooCommerce s’il est installé et rempli)', value: 'auto' },
                { label: 'Menu « Produits » du site', value: 'maison' },
                { label: 'WooCommerce', value: 'woocommerce' }
              ],
              onChange: function (v) { set({ source: v }); }
            }),
            el('p', { className: 'components-base-control__help' }, 'Les produits se gèrent dans le menu « Produits » de l’administration. Toute modification apparaît ici automatiquement.'),
            el(c.Button, { variant: 'secondary', href: 'edit.php?post_type=schiesser_produit', target: '_blank' }, 'Gérer les produits ↗')
          ),
          el(c.PanelBody, { title: 'Section', initialOpen: false },
            el(c.SelectControl, {
              label: 'Fond', value: fond,
              options: [
                { label: 'Papier (fond de la page)', value: 'papier' },
                { label: 'Clair', value: 'clair' },
                { label: 'Crème soutenu', value: 'alterne' },
                { label: 'Sable', value: 'sable' }
              ],
              onChange: function (v) { set({ fond: v }); }
            }),
            el(c.TextControl, {
              label: 'Numéro de section', value: a.numero,
              onChange: function (v) { set({ numero: v }); }
            }),
            el(c.TextControl, {
              label: 'Ancre (pour les liens #…)', help: 'Ex. « catalogue » : un bouton peut pointer vers #catalogue.',
              value: a.ancre, onChange: function (v) { set({ ancre: v }); }
            }),
            el(c.TextControl, {
              label: 'Bouton sous la grille (texte)', value: a.lienTexte,
              help: 'Ex. « Voir toute la boutique ». Laisser vide pour ne pas afficher de bouton.',
              onChange: function (v) { set({ lienTexte: v }); }
            }),
            el(c.TextControl, {
              label: 'Bouton sous la grille (lien)', value: a.lienUrl,
              help: 'Vide = page Boutique.',
              onChange: function (v) { set({ lienUrl: v }); }
            })
          )
        ),
        el('section', blockProps,
          el('div', { className: 'wrap' },
            el('div', { className: 'sec-head' },
              el('span', { className: 'idx' + (a.numero ? '' : ' idx--vide') }, el('i', { className: 'x-diamond' }), a.numero),
              el(be.RichText, {
                identifier: 'titre', tagName: 'h2', value: a.titre, allowedFormats: ['core/italic', 'core/bold'].concat(window.SCHIESSER_TYPO || []), placeholder: 'Titre de la section',
                onChange: function (v) { set({ titre: v }); }
              }),
              el(be.RichText, {
                identifier: 'note', tagName: 'p', className: 'note', value: a.note, allowedFormats: ['core/italic', 'core/bold'].concat(window.SCHIESSER_TYPO || []), placeholder: 'Petite note (facultatif)',
                onChange: function (v) { set({ note: v }); }
              })
            ),
            el('div', { style: { pointerEvents: 'none' } },
              el(ServerSideRender, {
                block: 'schiesser/produits',
                attributes: { apercu: true, limite: a.limite, filtres: a.filtres, source: a.source, ancre: a.ancre }
              })
            ),
            a.lienTexte ? el('div', { className: 'shop-tout' },
              el('span', { className: 'btn btn-line' }, el('span', null, a.lienTexte), ' ', el('span', { className: 'a' }, '→'))
            ) : null
          )
        )
      );
    },
    save: function () { return null; }
  });
})(window.wp);

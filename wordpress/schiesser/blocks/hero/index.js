/**
 * Bloc Hero : édition dans l'éditeur.
 * Le client clique directement sur les textes pour les modifier,
 * et change la photo avec le bouton « Image » de la barre d'outils.
 * JavaScript simple, sans étape de compilation.
 */
(function (wp) {
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;
  var useSelect = wp.data.useSelect;

  var SCEAU_TEXTE = 'CONFISERIE · TEA-ROOM · MARKTPLATZ BASEL · SEIT 1870 ·';

  function Sceau() {
    return el('div', { className: 'hero-seal', 'aria-hidden': true },
      el('svg', { viewBox: '0 0 200 200' },
        el('defs', null, el('path', { id: 'sealRingEd', d: 'M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0' })),
        el('g', { className: 'ring' },
          el('text', { fontFamily: 'Inter, sans-serif', fontSize: 11, fontWeight: 600, letterSpacing: 3, fill: 'currentColor' },
            el('textPath', { href: '#sealRingEd', textLength: 496, lengthAdjust: 'spacing' }, SCEAU_TEXTE))),
        el('circle', { cx: 100, cy: 100, r: 64, fill: 'none', stroke: 'currentColor', strokeOpacity: 0.55 }),
        el('circle', { cx: 100, cy: 100, r: 58, fill: 'none', stroke: 'currentColor', strokeOpacity: 0.4, strokeDasharray: '1 4' }),
        el('text', { x: 100, y: 121, textAnchor: 'middle', fontFamily: 'Bodoni Moda, serif', fontSize: 58, fontWeight: 500, fill: 'currentColor' }, 'S')
      )
    );
  }

  wp.blocks.registerBlockType('schiesser/hero', {
    edit: function (props) {
      var a = props.attributes;
      var set = props.setAttributes;
      var titrePage = useSelect(function (select) {
        var ed = select('core/editor');
        return ed ? ed.getEditedPostAttribute('title') : '';
      }, []);
      var blockProps = be.useBlockProps({ className: 'hero hero--' + a.hauteur });

      function choisirImage(m) {
        var url = (m.sizes && m.sizes.full) ? m.sizes.full.url : m.url;
        set({ imageId: m.id, imageUrl: url, imageAlt: m.alt || '' });
      }

      var boutonImage = el(be.MediaUploadCheck, null,
        el(be.MediaUpload, {
          onSelect: choisirImage,
          allowedTypes: ['image'],
          value: a.imageId,
          render: function (o) {
            return el(c.ToolbarButton, { icon: 'format-image', label: 'Changer la photo', onClick: o.open }, 'Photo');
          }
        })
      );

      var reglages = el(be.InspectorControls, null,
        el(c.PanelBody, { title: 'Mise en page', initialOpen: true },
          el(c.SelectControl, {
            label: 'Hauteur',
            value: a.hauteur,
            options: [
              { label: 'Accueil (très grande)', value: 'accueil' },
              { label: 'Page (grande)', value: 'page' },
              { label: 'Compacte', value: 'compacte' }
            ],
            onChange: function (v) { set({ hauteur: v }); }
          }),
          el(c.ToggleControl, {
            label: 'Afficher le fil d’Ariane (Accueil · Page)',
            checked: a.ariane,
            onChange: function (v) { set({ ariane: v }); }
          }),
          el(c.ToggleControl, {
            label: 'Afficher le sceau tournant',
            checked: a.sceau,
            onChange: function (v) { set({ sceau: v }); }
          })
        ),
        el(c.PanelBody, { title: 'Liens des boutons', initialOpen: true },
          el(c.TextControl, {
            label: 'Lien du bouton 1',
            help: 'Adresse d’une page (ex. /boutique/) ou d’une section (ex. #catalogue).',
            value: a.bouton1Lien,
            onChange: function (v) { set({ bouton1Lien: v }); }
          }),
          el(c.TextControl, {
            label: 'Lien du bouton 2',
            value: a.bouton2Lien,
            onChange: function (v) { set({ bouton2Lien: v }); }
          })
        )
      );

      var media = a.imageUrl
        ? el('img', { src: a.imageUrl, alt: '' })
        : el('div', { className: 'hero-vide' },
            el(be.MediaUploadCheck, null,
              el(be.MediaUpload, {
                onSelect: choisirImage,
                allowedTypes: ['image'],
                render: function (o) { return el(c.Button, { variant: 'secondary', onClick: o.open }, 'Choisir une photo'); }
              })
            )
          );

      return el(Fragment, null,
        el(be.BlockControls, null, el(c.ToolbarGroup, null, boutonImage)),
        reglages,
        el('section', blockProps,
          el('div', { className: 'hero-media' }, media),
          el('div', { className: 'hero-copy' },
            el('div', { className: 'hero-inner' },
              a.ariane ? el('div', { className: 'crumb' }, el('span', null, 'Accueil'), ' · ', el('span', null, titrePage || 'Page')) : null,
              el(be.RichText, {
                tagName: 'p', className: 'eyebrow', value: a.surtitre, allowedFormats: [],
                placeholder: 'Surtitre (ex. Rez-de-chaussée · Marktplatz)',
                onChange: function (v) { set({ surtitre: v }); }
              }),
              el(be.RichText, {
                tagName: 'h1', value: a.titre, allowedFormats: ['core/italic'],
                placeholder: 'Titre de la page (Maj + Entrée pour aller à la ligne)',
                onChange: function (v) { set({ titre: v }); }
              }),
              el(be.RichText, {
                tagName: 'p', className: 'hero-sub', value: a.texte, allowedFormats: ['core/italic'],
                placeholder: 'Texte d’introduction',
                onChange: function (v) { set({ texte: v }); }
              }),
              el('div', { className: 'hero-actions' },
                el('span', { className: 'btn btn-solid' },
                  el(be.RichText, {
                    tagName: 'span', value: a.bouton1Texte, allowedFormats: [], placeholder: 'Bouton 1',
                    onChange: function (v) { set({ bouton1Texte: v }); }
                  }), ' ', el('span', { className: 'a' }, '→')),
                el(be.RichText, {
                  tagName: 'span', className: 'btn btn-ghost', value: a.bouton2Texte, allowedFormats: [], placeholder: 'Bouton 2 (facultatif)',
                  onChange: function (v) { set({ bouton2Texte: v }); }
                })
              ),
              a.sceau ? el(Sceau) : null
            )
          )
        )
      );
    },
    save: function () { return null; }
  });
})(window.wp);

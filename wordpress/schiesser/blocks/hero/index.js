/**
 * Bloc Hero : édition dans l'éditeur.
 * Le client clique directement sur les textes pour les modifier,
 * change la photo avec le bouton « Photo » de la barre d'outils,
 * et règle le reste dans le panneau de droite (styles de boutons, accent, hauteur).
 * JavaScript simple, sans étape de compilation.
 */
(function (wp) {
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var be = wp.blockEditor;
  var c = wp.components;
  var useSelect = wp.data.useSelect;

  var SCEAU_TEXTE = 'CONFISERIE · TEA-ROOM · MARKTPLATZ BASEL · SEIT 1870 ·';
  var STYLES_BOUTON = [
    { label: 'Crème (plein)', value: 'creme' },
    { label: 'Vert maison (plein)', value: 'vert' },
    { label: 'Contour clair', value: 'contour' }
  ];
  var CLASSES_BOUTON = { creme: 'btn btn-solid', vert: 'btn btn-kir', contour: 'btn btn-ghost' };

  function Sceau() {
    return el('div', { className: 'hero-seal', 'aria-hidden': true },
      el('svg', { viewBox: '0 0 200 200' },
        el('defs', null, el('path', { id: 'sealRingEd', d: 'M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0' })),
        el('g', { className: 'ring' },
          el('text', { className: 'seal-txt', fontSize: 11, fontWeight: 600, letterSpacing: 3, fill: 'currentColor' },
            el('textPath', { href: '#sealRingEd', textLength: 496, lengthAdjust: 'spacing' }, SCEAU_TEXTE))),
        el('circle', { cx: 100, cy: 100, r: 64, fill: 'none', stroke: 'currentColor', strokeOpacity: 0.55 }),
        el('circle', { cx: 100, cy: 100, r: 58, fill: 'none', stroke: 'currentColor', strokeOpacity: 0.4, strokeDasharray: '1 4' }),
        el('text', { className: 'seal-s', x: 100, y: 121, textAnchor: 'middle', fontSize: 58, fontWeight: 500, fill: 'currentColor' }, 'S')
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
      var blockProps = be.useBlockProps({ className: 'hero hero--' + a.hauteur + ' hero--accent-' + (a.accent || 'creme') + (a.filtre ? ' hero--' + a.filtre : '') });

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
          el(c.SelectControl, {
            label: 'Couleur des mots en italique du titre',
            value: a.accent || 'creme',
            options: [
              { label: 'Crème (d’origine)', value: 'creme' },
              { label: 'Menthe', value: 'menthe' },
              { label: 'Blanc', value: 'blanc' }
            ],
            help: 'Pour mettre un mot en italique : sélectionnez-le, puis Ctrl+I (Cmd+I sur Mac).',
            onChange: function (v) { set({ accent: v }); }
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
          }),
          el(c.SelectControl, {
            label: 'Teinte de la photo',
            value: a.filtre || '',
            options: [
              { label: 'Naturelle', value: '' },
              { label: 'Légèrement ancienne', value: 'sepia-leger' },
              { label: 'Ancienne (archives)', value: 'sepia' }
            ],
            onChange: function (v) { set({ filtre: v }); }
          }),
          el(c.ToggleControl, {
            label: 'Barre de progression de lecture (en haut de l’écran)',
            checked: !!a.progression,
            help: 'Utile sur les pages longues comme « Notre histoire ».',
            onChange: function (v) { set({ progression: v }); }
          })
        ),
        el(c.PanelBody, { title: 'Boutons', initialOpen: true },
          el(c.SelectControl, {
            label: 'Style du bouton 1', value: a.bouton1Style || 'creme', options: STYLES_BOUTON,
            onChange: function (v) { set({ bouton1Style: v }); }
          }),
          el(c.TextControl, {
            label: 'Lien du bouton 1',
            help: 'Adresse d’une page (ex. /boutique/) ou d’une section de la page (ex. #catalogue).',
            value: a.bouton1Lien,
            onChange: function (v) { set({ bouton1Lien: v }); }
          }),
          el(c.SelectControl, {
            label: 'Style du bouton 2', value: a.bouton2Style || 'contour', options: STYLES_BOUTON,
            onChange: function (v) { set({ bouton2Style: v }); }
          }),
          el(c.TextControl, {
            label: 'Lien du bouton 2',
            value: a.bouton2Lien,
            onChange: function (v) { set({ bouton2Lien: v }); }
          })
        ),
        el(c.PanelBody, { title: 'Photo', initialOpen: true },
          el(c.RangeControl, {
            label: 'Assombrir la photo',
            help: 'Pour que le texte ressorte mieux sur une photo claire. 0 = photo d’origine.',
            value: a.sombre || 0, min: 0, max: 80, step: 5,
            onChange: function (v) { set({ sombre: v || 0 }); }
          }),
          a.imageId && a.imageUrl && window.SchiesserPointPhoto ? el(window.SchiesserPointPhoto, { id: a.imageId, url: a.imageUrl }) : null,
          el(c.TextareaControl, {
            label: 'Texte alternatif (description de la photo)',
            help: 'Décrit l’image pour Google et les personnes malvoyantes. Ex. « Vitrine de pralinés de la Confiserie Schiesser à Bâle ».',
            value: a.imageAlt,
            onChange: function (v) { set({ imageAlt: v }); }
          })
        )
      );

      var media = a.imageUrl
        ? (window.SchiesserPhotoPoint
          ? el(window.SchiesserPhotoPoint, { id: a.imageId, imgProps: { src: a.imageUrl, alt: '', style: a.sombre ? { '--lum': String(1 - a.sombre / 100) } : undefined } })
          : el('img', { src: a.imageUrl, alt: '', style: a.sombre ? { '--lum': String(1 - a.sombre / 100) } : undefined }))
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
                identifier: 'surtitre', tagName: 'p', className: 'eyebrow', value: a.surtitre, allowedFormats: ['core/italic', 'core/bold'].concat(window.SCHIESSER_TYPO || []),
                placeholder: 'Surtitre (ex. Confiserie et tea room à Bâle)',
                onChange: function (v) { set({ surtitre: v }); }
              }),
              el(be.RichText, {
                identifier: 'titre', tagName: 'h1', value: a.titre, allowedFormats: ['core/italic', 'core/bold'].concat(window.SCHIESSER_TYPO || []),
                placeholder: 'Titre de la page (Maj + Entrée pour aller à la ligne)',
                onChange: function (v) { set({ titre: v }); }
              }),
              el(be.RichText, {
                identifier: 'texte', tagName: 'p', className: 'hero-sub', value: a.texte, allowedFormats: ['core/italic', 'core/bold'].concat(window.SCHIESSER_TYPO || []),
                placeholder: 'Texte d’introduction',
                onChange: function (v) { set({ texte: v }); }
              }),
              el('div', { className: 'hero-actions' },
                el('span', { className: CLASSES_BOUTON[a.bouton1Style || 'creme'] },
                  el(be.RichText, {
                    identifier: 'bouton1Texte', tagName: 'span', value: a.bouton1Texte, allowedFormats: [], placeholder: 'Bouton 1',
                    onChange: function (v) { set({ bouton1Texte: v }); }
                  }), ' ', el('span', { className: 'a' }, '→')),
                el('span', { className: CLASSES_BOUTON[a.bouton2Style || 'contour'] },
                  el(be.RichText, {
                    identifier: 'bouton2Texte', tagName: 'span', value: a.bouton2Texte, allowedFormats: [], placeholder: 'Bouton 2 (facultatif)',
                    onChange: function (v) { set({ bouton2Texte: v }); }
                  }))
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

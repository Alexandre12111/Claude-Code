/**
 * Blocs de la maquette : édition dans l'éditeur de pages.
 *
 * Le rendu du site est fait en PHP (inc/maquette.php), à l'identique de la maquette.
 * Ici, chaque bloc s'affiche comme sur le site (ou sous forme de fiches pour les
 * éléments animés) et tout se modifie directement :
 * - cliquer sur un texte pour l'écrire ;
 * - cliquer sur une photo (ou bouton « Photo » de la barre d'outils) pour la changer ;
 * - bouton + pour ajouter un élément (un étage, une date, une question…) ;
 * - panneau de droite pour les réglages (fond, numéro, liens…).
 *
 * JavaScript simple, sans compilation.
 */
(function (wp) {
  'use strict';
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var useState = wp.element.useState;
  var be = wp.blockEditor;
  var c = wp.components;
  var RichText = be.RichText;
  var useSelect = wp.data.useSelect;
  var SSR = wp.serverSideRender;
  var ED = window.SCHIESSER_ED || {};

  var FONDS = { papier: '', clair: 'bg-soft', alterne: 'bg-alt', sable: 'bg-deep', sombre: 'bg-ink' };
  var OPTIONS_FONDS = [
    { label: 'Papier (fond de la page)', value: 'papier' },
    { label: 'Clair', value: 'clair' },
    { label: 'Crème soutenu', value: 'alterne' },
    { label: 'Sable', value: 'sable' },
    { label: 'Sombre (chocolat)', value: 'sombre' }
  ];
  var PICTOS = ED.pictos || {};

  function nul() { return null; }
  function contenuEnfants() { return el(be.InnerBlocks.Content); }

  function maj(set, cle) {
    return function (v) { var o = {}; o[cle] = v; set(o); };
  }

  /* ------------------------------------------------------------------ */
  /* Champs                                                              */
  /* ------------------------------------------------------------------ */

  // Texte simple (sans mise en forme).
  function T(props, cle, tag, className, placeholder) {
    return el(RichText, {
      tagName: tag || 'span', className: className, value: props.attributes[cle] || '',
      placeholder: placeholder || '', allowedFormats: [], withoutInteractiveFormatting: true,
      onChange: maj(props.setAttributes, cle)
    });
  }
  // Titre : italique autorisé (les mots en italique prennent la couleur d'accent).
  function I(props, cle, tag, className, placeholder) {
    return el(RichText, {
      tagName: tag || 'span', className: className, value: props.attributes[cle] || '',
      placeholder: placeholder || '', allowedFormats: ['core/italic'],
      onChange: maj(props.setAttributes, cle)
    });
  }
  // Texte : gras, italique et liens autorisés.
  function R(props, cle, tag, className, placeholder) {
    return el(RichText, {
      tagName: tag || 'p', className: className, value: props.attributes[cle] || '',
      placeholder: placeholder || '', allowedFormats: ['core/bold', 'core/italic', 'core/link'],
      onChange: maj(props.setAttributes, cle)
    });
  }

  function picto(nom) {
    var p = PICTOS[nom] || PICTOS.horloge || ['', ''];
    return el('svg', { viewBox: '0 0 24 24', 'aria-hidden': true, dangerouslySetInnerHTML: { __html: p[1] } });
  }
  function optionsPictos() {
    return Object.keys(PICTOS).map(function (k) { return { label: PICTOS[k][0], value: k }; });
  }

  /* Photos ----------------------------------------------------------- */

  function urlMedia(m) {
    return (m.sizes && m.sizes.large && m.sizes.large.url) || m.url;
  }
  function choisir(props, p) {
    return function (m) {
      var o = {};
      o[p + 'Id'] = m.id;
      o[p + 'Url'] = urlMedia(m);
      if (m.alt) o[p + 'Alt'] = m.alt;
      props.setAttributes(o);
    };
  }
  // Photo dans la page : un clic ouvre la médiathèque.
  function photo(props, p, className) {
    var a = props.attributes;
    return el(be.MediaUploadCheck, { fallback: a[p + 'Url'] ? el('img', { src: a[p + 'Url'], alt: '', className: className }) : null },
      el(be.MediaUpload, {
        onSelect: choisir(props, p), allowedTypes: ['image'], value: a[p + 'Id'],
        render: function (o) {
          return a[p + 'Url']
            ? el('img', { src: a[p + 'Url'], alt: '', className: (className || '') + ' sch-ed-img', onClick: o.open, title: 'Cliquez pour changer la photo' })
            : el('button', { type: 'button', className: 'sch-ed-photo-vide ' + (className || ''), onClick: o.open }, 'Choisir une photo');
        }
      }));
  }
  // Bouton « Photo » dans la barre d'outils du bloc.
  function barrePhoto(props, p, libelle) {
    var a = props.attributes;
    return el(be.BlockControls, { group: 'other' },
      el(be.MediaUploadCheck, null,
        el(be.MediaUpload, {
          onSelect: choisir(props, p), allowedTypes: ['image'], value: a[p + 'Id'],
          render: function (o) { return el(c.ToolbarButton, { icon: 'format-image', label: libelle || 'Changer la photo', onClick: o.open }, libelle || 'Photo'); }
        })));
  }
  // Panneau « Photo » à droite : aperçu, remplacer, retirer, texte alternatif.
  function panneauPhoto(props, p, titre) {
    var a = props.attributes;
    var set = props.setAttributes;
    return el(c.PanelBody, { title: titre || 'Photo', initialOpen: true },
      a[p + 'Url'] ? el('img', { src: a[p + 'Url'], alt: '', className: 'sch-ed-apercu' }) : el('p', { className: 'sch-ed-aide' }, 'Aucune photo pour le moment.'),
      el(be.MediaUploadCheck, null,
        el(be.MediaUpload, {
          onSelect: choisir(props, p), allowedTypes: ['image'], value: a[p + 'Id'],
          render: function (o) { return el(c.Button, { variant: 'secondary', onClick: o.open }, a[p + 'Url'] ? 'Remplacer la photo' : 'Choisir une photo'); }
        })),
      a[p + 'Url'] ? el(c.Button, {
        variant: 'link', isDestructive: true,
        onClick: function () { var o = {}; o[p + 'Id'] = 0; o[p + 'Url'] = ''; set(o); }
      }, 'Retirer la photo') : null,
      el(c.TextareaControl, {
        label: 'Texte alternatif', value: a[p + 'Alt'] || '',
        help: 'Décrivez la photo en une phrase : il est lu par Google et par les lecteurs d’écran.',
        onChange: maj(set, p + 'Alt')
      })
    );
  }

  /* Liens et boutons ---------------------------------------------------- */

  function champLien(props, cle, label, help) {
    return el('div', { className: 'sch-ed-lien' },
      el('p', { className: 'sch-ed-label' }, label),
      el(be.URLInput, { value: props.attributes[cle] || '', onChange: maj(props.setAttributes, cle) }),
      help ? el('p', { className: 'sch-ed-aide' }, help) : null);
  }
  // Bouton dont le texte s'écrit directement dans la page.
  function bouton(props, cle, classe, placeholder, fleche) {
    return el('span', { className: 'btn ' + classe },
      T(props, cle, 'span', '', placeholder || 'Texte du bouton'),
      fleche ? el('span', { className: 'a', 'aria-hidden': true }, ' →') : null);
  }

  /* Sections -------------------------------------------------------------- */

  function marque() {
    return el('span', { className: 'x-mark', 'aria-hidden': true, dangerouslySetInnerHTML: { __html: '<svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="30" fill="none" stroke="currentColor" stroke-width="1"/><text x="32" y="43" text-anchor="middle" font-family="Bodoni Moda, serif" font-weight="600" font-size="31" fill="currentColor">S</text></svg>' } });
  }

  function entete(props) {
    var a = props.attributes;
    return el('div', { className: 'sec-head' },
      el('span', { className: 'idx' + (a.numero ? '' : ' idx--vide') }, el('i', { className: 'x-diamond' }), a.numero),
      I(props, 'titre', 'h2', '', 'Titre de la section'),
      T(props, 'note', 'p', 'note', 'Petite note (facultatif)'));
  }

  function panneauSection(props, options) {
    var a = props.attributes;
    var set = props.setAttributes;
    return el(c.PanelBody, { title: 'Section', initialOpen: true },
      options.fond === false ? null : el(c.SelectControl, {
        label: 'Fond', value: FONDS.hasOwnProperty(a.fond) ? a.fond : 'papier', options: OPTIONS_FONDS,
        help: 'Alternez les fonds d’une section à l’autre pour rythmer la page.',
        onChange: maj(set, 'fond')
      }),
      options.fond !== false && a.fond === 'sombre' ? el(c.ToggleControl, {
        label: 'Filigrane « S » en haut à droite', checked: !!a.marque, onChange: maj(set, 'marque')
      }) : null,
      el(c.TextControl, {
        label: 'Numéro (ex. 01)', value: a.numero || '',
        help: 'Numérotez les sections dans l’ordre de la page.', onChange: maj(set, 'numero')
      }),
      el(c.TextControl, {
        label: 'Ancre (lien direct #…)', value: a.ancre || '',
        help: 'Ex. « carte » : un bouton peut alors pointer vers #carte.', onChange: maj(set, 'ancre')
      })
    );
  }

  /**
   * Section complète dans l'éditeur.
   * options : fond (false si le fond est imposé), classe (balise section), wrap (classe du conteneur),
   * panneaux (réglages supplémentaires), barre (barre d'outils).
   */
  function Section(props, options, contenu) {
    var a = props.attributes;
    var fond = FONDS.hasOwnProperty(a.fond) ? a.fond : 'papier';
    var classe = options.classe || ('sec ' + (options.fond === false ? '' : FONDS[fond]));
    var bp = be.useBlockProps({ className: (classe + ' sch-ed').trim() });
    return el(Fragment, null,
      options.barre || null,
      el(be.InspectorControls, null, panneauSection(props, options), options.panneaux || null),
      el('section', bp,
        options.fond !== false && fond === 'sombre' && a.marque ? marque() : null,
        el('div', { className: options.wrap || 'wrap' }, entete(props), contenu)));
  }

  // Liste d'éléments (blocs enfants) : on les ajoute avec +, on les déplace, on les supprime.
  function Liste(className, enfant, modele, orientation) {
    return el('div', be.useInnerBlocksProps({ className: className }, {
      allowedBlocks: [enfant],
      template: modele || [[enfant]],
      orientation: orientation || 'horizontal'
    }));
  }

  // Nom du bloc parent (les éléments s'affichent selon la section qui les contient).
  function useParent(clientId) {
    return useSelect(function (s) {
      var ed = s('core/block-editor');
      var ids = ed.getBlockParents(clientId, true);
      return ids.length ? { nom: ed.getBlockName(ids[0]), attrs: ed.getBlockAttributes(ids[0]) || {} } : { nom: '', attrs: {} };
    }, [clientId]);
  }

  function aide(texte) {
    return el('p', { className: 'sch-ed-note' }, texte);
  }

  function enregistrer(nom, edit, avecEnfants) {
    wp.blocks.registerBlockType('schiesser/' + nom, { edit: edit, save: avecEnfants ? contenuEnfants : nul });
  }

  // Aperçu calculé par le site (blocs sans éléments).
  function apercu(nom, props) {
    return el('div', { className: 'sch-ed-ssr' },
      el(SSR, { block: 'schiesser/' + nom, attributes: Object.assign({}, props.attributes, { apercu: true }), EmptyResponsePlaceholder: function () { return aide('Rien à afficher pour le moment.'); } }));
  }

  /* ------------------------------------------------------------------ */
  /* Bandeaux                                                            */
  /* ------------------------------------------------------------------ */

  var OPTIONS_AUTO = [
    { label: 'Texte saisi', value: '' },
    { label: 'Téléphone (Réglages maison)', value: 'telephone' },
    { label: 'E-mail (Réglages maison)', value: 'email' },
    { label: 'Téléphone et e-mail (Réglages maison)', value: 'contact' },
    { label: 'Adresse (Réglages maison)', value: 'adresse' },
    { label: 'Horaires du jour et de demain', value: 'aujourdhui' }
  ];
  var APERCUS_AUTO = {
    telephone: ED.telephone || 'Téléphone',
    email: ED.email || 'E-mail',
    adresse: ED.rue || 'Adresse',
    'adresse-ligne': ED.adresseLigne || 'Adresse',
    aujourdhui: ED.aujourdhui || '07:30 – 18:30',
    horaires: ED.aujourdhui || 'Ouvert',
    contact: ED.telephone || 'Téléphone'
  };

  enregistrer('bandeau-live', function (props) {
    var a = props.attributes;
    var set = props.setAttributes;
    var bp = be.useBlockProps({ className: 'live' });
    function cellule(n) {
      var auto = a['c' + n + 'Auto'];
      return el('div', { className: 'lv-cell' },
        T(props, 'c' + n + 'Libelle', 'div', 'k', 'Libellé'),
        el('div', { className: 'v' },
          auto ? el('span', { className: 'sch-ed-auto', title: 'Rempli automatiquement depuis les Réglages maison' }, APERCUS_AUTO[auto] || '') : T(props, 'c' + n + 'Valeur', 'span', '', 'Information'),
          el('small', null, auto === 'aujourdhui' ? ED.demain || 'Demain' : T(props, 'c' + n + 'Detail', 'span', '', 'Précision (facultatif)'))));
    }
    return el(Fragment, null,
      el(be.InspectorControls, null, [1, 2].map(function (n) {
        return el(c.PanelBody, { key: n, title: 'Information ' + n, initialOpen: true },
          el(c.SelectControl, { label: 'Contenu', value: a['c' + n + 'Auto'], options: OPTIONS_AUTO, onChange: maj(set, 'c' + n + 'Auto') }),
          a['c' + n + 'Auto'] ? null : champLien(props, 'c' + n + 'Lien', 'Lien (facultatif)'));
      })),
      el('section', bp, el('div', { className: 'wrap' },
        el('div', { className: 'lv-main' }, el('span', { className: 'lv-dot' }), el('span', { className: 'lv-state' }, 'Ouvert'), el('span', { className: 'lv-count' }, 'Fermeture dans ', el('b', null, '4h42'))),
        cellule(1), cellule(2))));
  });

  enregistrer('vitrine-jour', function (props) {
    var bp = be.useBlockProps({ className: 'vband' });
    var produits = ED.vitrine || [];
    return el('div', bp, el('div', { className: 'wrap' },
      el('div', { className: 'vt' }, el('span', { className: 'dot' }), T(props, 'titre', 'b', '', 'En vitrine aujourd’hui')),
      el('ul', null, produits.length ? produits.map(function (p, i) { return el('li', { key: i }, el('b', null, ('0' + (i + 1)).slice(-2)), p); }) : el('li', null, 'Ajoutez les produits du jour dans Réglages maison.')),
      el('div', { className: 'vday' }, 'aujourd’hui')));
  });

  enregistrer('chiffres', function (props) {
    var bp = be.useBlockProps({ className: 'sband x-dia' });
    return el('div', bp, Liste('wrap', 'schiesser/chiffre', [['schiesser/chiffre'], ['schiesser/chiffre'], ['schiesser/chiffre'], ['schiesser/chiffre']]));
  }, true);

  enregistrer('chiffre', function (props) {
    var parent = useParent(props.clientId);
    var encart = parent.nom === 'schiesser/encart';
    var bp = be.useBlockProps({ className: encart ? 'dy-d' : 'sb' });
    return el('div', bp,
      T(props, 'valeur', 'div', encart ? 'y' : 'n', encart ? '1870' : 'Chiffre'),
      T(props, 'libelle', 'div', encart ? 't' : 'l', 'Libellé'));
  });

  enregistrer('plaque', function (props) {
    var bp = be.useBlockProps({ className: 'sec-plate' });
    return el('section', bp, el('div', { className: 'wrap' }, el('div', { className: 'x-plate' },
      el('div', { className: 'rule' }, el('span', { className: 'x-seal', 'aria-hidden': true, dangerouslySetInnerHTML: { __html: '<svg viewBox="0 0 64 64"><circle cx="32" cy="32" r="30" fill="none" stroke="currentColor" stroke-width="1"/><circle cx="32" cy="32" r="25.5" fill="none" stroke="currentColor" stroke-width="0.6" stroke-dasharray="1 3"/><text x="32" y="42.5" text-anchor="middle" font-family="Bodoni Moda, serif" font-weight="600" font-size="30" fill="currentColor">S</text></svg>' } })),
      T(props, 'nombre', 'div', 'n', '150'),
      T(props, 'libelle', 'div', 'l', 'ans de maison'),
      T(props, 'dates', 'div', 'd', '1870 · 2020'),
      R(props, 'texte', 'p', '', 'Texte de la plaque'))));
  });

  enregistrer('separateur', function () {
    var bp = be.useBlockProps({ className: 'wrap' });
    return el('div', bp, el('div', { className: 'x-sep' }, el('i', { className: 'x-diamond' }), el('i', { className: 'x-diamond' }), el('i', { className: 'x-diamond' })));
  });

  enregistrer('appel', function (props) {
    var a = props.attributes;
    var bp = be.useBlockProps({ className: 'cta' });
    return el(Fragment, null,
      el(be.InspectorControls, null, el(c.PanelBody, { title: 'Boutons et ancre', initialOpen: true },
        champLien(props, 'b1Lien', 'Lien du bouton 1'),
        champLien(props, 'b2Lien', 'Lien du bouton 2', 'Laissez le texte vide pour ne pas afficher le bouton.'),
        el(c.TextControl, { label: 'Ancre (lien direct #…)', value: a.ancre || '', onChange: maj(props.setAttributes, 'ancre') }))),
      el('section', bp, el('div', { className: 'wrap' },
        T(props, 'surtitre', 'p', 'eyebrow', 'Surtitre'),
        I(props, 'titre', 'h2', '', 'Grand titre (mots en italique possibles)'),
        R(props, 'texte', 'p', '', 'Texte'),
        el('div', { className: 'cta-actions' }, bouton(props, 'b1Texte', 'btn-solid', 'Bouton principal', true), bouton(props, 'b2Texte', 'btn-ghost', 'Second bouton (facultatif)')))));
  });

  /* ------------------------------------------------------------------ */
  /* Accueil                                                             */
  /* ------------------------------------------------------------------ */

  // Sélection des produits du catalogue : liste ordonnée d'identifiants (« 12,15,9 »).
  function choixProduits(a, set) {
    var tous = ED.produits || [];
    var ids = String(a.produits || '').split(',').map(function (x) { return parseInt(x, 10); }).filter(function (x) { return x > 0; });
    var nom = function (id) {
      for (var i = 0; i < tous.length; i++) { if (tous[i].id === id) return tous[i].nom; }
      return 'Produit introuvable (supprimé ou non publié)';
    };
    var ecrire = function (liste) { set({ produits: liste.join(',') }); };
    var deplacer = function (i, d) {
      var liste = ids.slice();
      var x = liste[i]; liste[i] = liste[i + d]; liste[i + d] = x;
      ecrire(liste);
    };
    var restants = tous.filter(function (p) { return ids.indexOf(p.id) === -1; });
    return el(Fragment, null,
      ids.map(function (id, i) {
        return el('div', { key: id, style: { display: 'flex', alignItems: 'center', gap: '4px', padding: '4px 0', borderBottom: '1px solid #e0e0e0' } },
          el('span', { style: { flex: '1', fontSize: '13px' } }, (i + 1) + '. ' + nom(id)),
          el(c.Button, { icon: 'arrow-up-alt2', label: 'Monter', size: 'small', disabled: i === 0, onClick: function () { deplacer(i, -1); } }),
          el(c.Button, { icon: 'arrow-down-alt2', label: 'Descendre', size: 'small', disabled: i === ids.length - 1, onClick: function () { deplacer(i, 1); } }),
          el(c.Button, { icon: 'no-alt', label: 'Retirer', size: 'small', isDestructive: true, onClick: function () { ecrire(ids.filter(function (x) { return x !== id; })); } }));
      }),
      restants.length ? el('div', { style: { marginTop: '12px' } },
        el(c.SelectControl, {
          label: 'Ajouter un produit', value: '',
          options: [{ label: 'Choisir…', value: '' }].concat(restants.map(function (p) { return { label: p.nom, value: String(p.id) }; })),
          onChange: function (v) { if (v) ecrire(ids.concat([parseInt(v, 10)])); }
        })) : null);
  }

  enregistrer('catalogue', function (props) {
    var a = props.attributes;
    var set = props.setAttributes;
    var manuel = !!a.produits;
    var premiers = (ED.produits || []).slice(0, 6).map(function (p) { return p.id; }).join(',');
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Produits affichés', initialOpen: true },
        el(c.ToggleControl, {
          label: 'Choisir les produits un par un', checked: manuel,
          help: manuel ? 'Seuls ces produits s’affichent, dans cet ordre.' : 'Les premiers produits du menu Produits, dans l’ordre de ce menu.',
          onChange: function (v) { set({ produits: v ? (premiers || '0') : '' }); }
        }),
        manuel ? choixProduits(a, set) : el(Fragment, null,
          el(c.RangeControl, { label: 'Nombre de produits (0 = tous)', value: a.limite, min: 0, max: 24, onChange: maj(set, 'limite') }),
          el(c.SelectControl, { label: 'Source', value: a.source, options: [{ label: 'Automatique', value: 'auto' }, { label: 'Produits du site', value: 'maison' }, { label: 'WooCommerce', value: 'woocommerce' }], onChange: maj(set, 'source') })),
        aide('La photo, le nom et l’accroche de chaque produit se modifient dans le menu Produits.'))
    }, apercu('catalogue', props));
  });

  enregistrer('etages', function (props) {
    return Section(props, {}, Liste('x-floors', 'schiesser/etage', [['schiesser/etage', { numero: '0' }], ['schiesser/etage', { numero: '1' }]]));
  }, true);

  enregistrer('etage', function (props) {
    var bp = be.useBlockProps({ className: 'x-floor' });
    return el(Fragment, null,
      barrePhoto(props, 'image'),
      el(be.InspectorControls, null, panneauPhoto(props, 'image'), el(c.PanelBody, { title: 'Lien', initialOpen: true }, champLien(props, 'lienUrl', 'Page ouverte au clic'))),
      el('div', bp,
        photo(props, 'image', ''),
        T(props, 'numero', 'span', 'x-num', '0'),
        el('span', { className: 'x-fc' },
          T(props, 'surtitre', 'span', 'x-lv', 'Rez-de-chaussée'),
          T(props, 'titre', 'span', 'x-ft', 'Titre'),
          T(props, 'texte', 'span', 'x-fd', 'Texte'),
          el('span', { className: 'x-fb' }, T(props, 'lienTexte', 'span', '', 'Texte du lien'), el('span', null, ' →')))));
  });

  enregistrer('savoir-faire', function (props) {
    return Section(props, {}, el(Fragment, null,
      aide('Sur le site : la photo change à mesure que l’on fait défiler les étapes.'),
      Liste('sch-ed-grille sch-ed-numeros', 'schiesser/geste', [['schiesser/geste'], ['schiesser/geste'], ['schiesser/geste'], ['schiesser/geste']])));
  }, true);

  enregistrer('geste', function (props) {
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null, barrePhoto(props, 'image'), el(be.InspectorControls, null, panneauPhoto(props, 'image')),
      el('div', bp, photo(props, 'image', 'sch-ed-vignette'), T(props, 'titre', 'h3', '', 'Titre de l’étape'), R(props, 'texte', 'p', '', 'Texte')));
  });

  enregistrer('frise', function (props) {
    var a = props.attributes;
    var set = props.setAttributes;
    return Section(props, {
      fond: false, classe: 'tl', wrap: 'sec wrap',
      panneaux: el(c.PanelBody, { title: 'Affichage', initialOpen: true },
        el(c.SelectControl, { label: 'Présentation', value: a.affichage, options: [{ label: 'Cartes à faire glisser', value: 'cartes' }, { label: 'Chronologie à onglets (photo, grande date, texte)', value: 'onglets' }], onChange: maj(set, 'affichage') }),
        el(c.TextControl, { label: 'Indication sous la frise', value: a.indication, onChange: maj(set, 'indication') }),
        a.affichage === 'onglets' ? el(c.TextControl, { label: 'Libellé « À retenir »', value: a.retenirLibelle, onChange: maj(set, 'retenirLibelle') }) : null)
    }, Liste('sch-ed-grille sch-ed-sombre', 'schiesser/date', [['schiesser/date'], ['schiesser/date'], ['schiesser/date']]));
  }, true);

  enregistrer('date', function (props) {
    var parent = useParent(props.clientId);
    var onglets = parent.attrs.affichage === 'onglets';
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null, barrePhoto(props, 'image'), el(be.InspectorControls, null, panneauPhoto(props, 'image')),
      el('div', bp,
        photo(props, 'image', 'sch-ed-vignette'),
        T(props, 'annee', 'div', 'sch-ed-annee', 'Année (ex. 1870)'),
        onglets ? T(props, 'libelle', 'div', 'sch-ed-petit', 'Libellé court (onglet)') : null,
        T(props, 'titre', 'h3', '', 'Titre'),
        R(props, 'texte', 'p', '', 'Texte'),
        onglets ? R(props, 'retenir', 'p', 'sch-ed-petit', 'À retenir (facultatif)') : null));
  });

  enregistrer('adresse', function (props) {
    var a = props.attributes;
    var set = props.setAttributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Carte et boutons', initialOpen: true },
        el(c.ToggleControl, { label: 'Carte interactive (OpenStreetMap)', checked: !!a.carte, help: 'Position : Réglages maison, latitude et longitude.', onChange: maj(set, 'carte') }),
        champLien(props, 'b1Lien', 'Lien du bouton 1', 'Vide : e-mail de la maison.'),
        champLien(props, 'b2Lien', 'Lien du bouton 2', 'Vide : itinéraire Google Maps.'))
    }, el('div', { className: 'visit' },
      el('div', { className: 'vmap sch-ed-carte-plan' }, el('span', null, 'Carte interactive'), el('small', null, ED.rue ? ED.rue + ', ' + ED.ville : '')),
      el('div', { className: 'vinfo' },
        T(props, 'lieu', 'h3', '', ED.rue ? ED.rue + ', ' + ED.ville : 'Lieu'),
        Liste('sch-ed-lignes', 'schiesser/ligne', [['schiesser/ligne', { libelle: 'Adresse', auto: 'adresse' }], ['schiesser/ligne', { libelle: 'Horaires', auto: 'horaires' }], ['schiesser/ligne', { libelle: 'Contact', auto: 'contact' }]], 'vertical'),
        el('div', { className: 'vcta' }, bouton(props, 'b1Texte', 'btn-kir', 'Nous écrire', true), bouton(props, 'b2Texte', 'btn-line', 'Itinéraire')))));
  }, true);

  /* Ligne d'information : son apparence suit la section qui la contient. */
  var OPTIONS_LIGNE = [
    { label: 'Texte saisi', value: '' },
    { label: 'Adresse sur deux lignes', value: 'adresse' },
    { label: 'Adresse sur une ligne', value: 'adresse-ligne' },
    { label: 'Horaires (état du jour et résumé)', value: 'horaires' },
    { label: 'Téléphone et e-mail', value: 'contact' },
    { label: 'Téléphone', value: 'telephone' },
    { label: 'E-mail', value: 'email' }
  ];
  enregistrer('ligne', function (props) {
    var a = props.attributes;
    var parent = useParent(props.clientId).nom;
    var auto = a.auto;
    var valeur = auto ? el('span', { className: 'sch-ed-auto', title: 'Rempli automatiquement depuis les Réglages maison' }, APERCUS_AUTO[auto] || '') : R(props, 'valeur', 'span', '', 'Valeur');
    var detail = T(props, 'detail', 'span', '', auto ? 'Précision (vide : automatique)' : 'Précision (facultatif)');
    var panneau = el(be.InspectorControls, null, el(c.PanelBody, { title: 'Ligne', initialOpen: true },
      el(c.SelectControl, { label: 'Contenu', value: auto, options: OPTIONS_LIGNE, help: 'Les contenus automatiques suivent les Réglages maison.', onChange: maj(props.setAttributes, 'auto') }),
      auto ? null : champLien(props, 'lien', 'Lien (facultatif)')));
    var bp;
    if (parent === 'schiesser/formulaire') {
      bp = be.useBlockProps({ className: 'xs' });
      return el(Fragment, null, panneau, el('div', bp, T(props, 'libelle', 'div', 'k', 'Libellé'), el('div', { className: 'v' }, valeur), el('div', { className: 's' }, detail)));
    }
    if (parent === 'schiesser/fiche-contact') {
      bp = be.useBlockProps({ className: 'dl' });
      return el(Fragment, null, panneau, el('div', bp, T(props, 'libelle', 'span', 'k', 'Libellé'), el('span', null, valeur)));
    }
    bp = be.useBlockProps({ className: parent === 'schiesser/adresse' ? 'vrow' : 'vn-row' });
    return el(Fragment, null, panneau, el('div', bp, T(props, 'libelle', 'span', 'k', 'Libellé'), el('span', { className: 'v' }, valeur, el('small', null, detail))));
  });

  /* ------------------------------------------------------------------ */
  /* Boutique                                                            */
  /* ------------------------------------------------------------------ */

  enregistrer('galerie', function (props) {
    return Section(props, {}, el(Fragment, null,
      aide('Sur le site : grande photo avec légende, flèches et vignettes.'),
      Liste('sch-ed-grille sch-ed-sombre', 'schiesser/vue', [['schiesser/vue'], ['schiesser/vue'], ['schiesser/vue']])));
  }, true);

  enregistrer('vue', function (props) {
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null, barrePhoto(props, 'image'), el(be.InspectorControls, null, panneauPhoto(props, 'image')),
      el('div', bp, photo(props, 'image', 'sch-ed-vignette'), T(props, 'titre', 'h3', '', 'Titre de la photo'), R(props, 'texte', 'p', '', 'Légende')));
  });

  enregistrer('infos', function (props) {
    return Section(props, {}, Liste('prac', 'schiesser/info', [['schiesser/info'], ['schiesser/info'], ['schiesser/info']]));
  }, true);

  enregistrer('info', function (props) {
    var a = props.attributes;
    var bp = be.useBlockProps({ className: 'pcell' });
    return el(Fragment, null,
      el(be.InspectorControls, null, el(c.PanelBody, { title: 'Pictogramme', initialOpen: true },
        el(c.SelectControl, { label: 'Pictogramme', value: a.icone, options: optionsPictos(), onChange: maj(props.setAttributes, 'icone') }))),
      el('div', bp, el('span', { className: 'pi' }, picto(a.icone)),
        el('div', null, T(props, 'titre', 'h3', '', 'Titre'), R(props, 'texte', 'p', '', 'Texte'))));
  });

  enregistrer('cartes', function (props) {
    var a = props.attributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Modèle', initialOpen: true },
        el(c.SelectControl, { label: 'Modèle de cartes', value: a.modele, options: [{ label: 'Passer commande (avec bouton)', value: 'commande' }, { label: 'Principes (sans bouton)', value: 'principes' }], onChange: maj(props.setAttributes, 'modele') }))
    }, Liste(a.modele === 'principes' ? 'vals sch-ed-numeros' : 'ord sch-ed-numeros', 'schiesser/carte', [['schiesser/carte'], ['schiesser/carte'], ['schiesser/carte', { sombre: a.modele !== 'principes' }]]));
  }, true);

  enregistrer('carte', function (props) {
    var a = props.attributes;
    var modele = useParent(props.clientId).attrs.modele;
    if (modele === 'principes') {
      return el('div', be.useBlockProps({ className: 'val' }), el('div', { className: 'vn' }), T(props, 'titre', 'h3', '', 'Titre'), R(props, 'texte', 'p', '', 'Texte'));
    }
    return el(Fragment, null,
      el(be.InspectorControls, null, el(c.PanelBody, { title: 'Carte', initialOpen: true },
        el(c.ToggleControl, { label: 'Carte sombre (mise en avant)', checked: !!a.sombre, onChange: maj(props.setAttributes, 'sombre') }),
        champLien(props, 'boutonLien', 'Lien du bouton', 'Ex. /contact/, tel:+41…, mailto:…'))),
      el('div', be.useBlockProps({ className: 'oc' + (a.sombre ? ' oc-dark' : '') }),
        el('span', { className: 'on' }),
        T(props, 'titre', 'h3', '', 'Titre'),
        R(props, 'texte', 'p', '', 'Texte'),
        el('div', { className: 'ob' }, bouton(props, 'boutonTexte', a.sombre ? 'btn-solid' : 'btn-line', 'Bouton (facultatif)', a.sombre))));
  });

  /* ------------------------------------------------------------------ */
  /* Salon de thé                                                        */
  /* ------------------------------------------------------------------ */

  enregistrer('intro', function (props) {
    var a = props.attributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Enchaînement', initialOpen: false },
        el(c.ToggleControl, { label: 'Collée à la section suivante', checked: !!a.suite, help: 'Pour enchaîner directement avec la montée à l’étage.', onChange: maj(props.setAttributes, 'suite') }))
    }, el('div', { className: 'asc-intro' },
      R(props, 'lead', 'p', 'lead', 'Grande phrase d’accroche'),
      el('div', be.useInnerBlocksProps({ className: 'body' }, {
        allowedBlocks: ['core/paragraph', 'core/list'],
        template: [['core/paragraph', { placeholder: 'Premier paragraphe' }], ['core/paragraph', { placeholder: 'Second paragraphe' }]]
      }))));
  }, true);

  enregistrer('ascension', function (props) {
    var a = props.attributes;
    var bp = be.useBlockProps({ className: 'sec sch-ed' });
    return el(Fragment, null,
      el(be.InspectorControls, null, el(c.PanelBody, { title: 'Indication', initialOpen: true },
        el(c.TextControl, { label: 'Texte en bas de la scène', value: a.indication, onChange: maj(props.setAttributes, 'indication') }))),
      el('section', bp, el('div', { className: 'wrap' },
        aide('Sur le site : grande scène sombre, les paliers défilent à mesure que l’on descend dans la page.'),
        Liste('sch-ed-grille sch-ed-sombre', 'schiesser/palier', [['schiesser/palier', { niveau: '0' }], ['schiesser/palier', { niveau: '½' }], ['schiesser/palier', { niveau: '1' }]]))));
  }, true);

  enregistrer('palier', function (props) {
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null, barrePhoto(props, 'image'), el(be.InspectorControls, null, panneauPhoto(props, 'image')),
      el('div', bp, photo(props, 'image', 'sch-ed-vignette'),
        el('div', { className: 'sch-ed-ligne' }, T(props, 'niveau', 'span', 'sch-ed-annee', '0'), T(props, 'libelle', 'span', 'sch-ed-petit', 'Libellé court (Boutique)')),
        T(props, 'surtitre', 'div', 'sch-ed-petit', 'Surtitre (Rez-de-chaussée)'),
        T(props, 'titre', 'h3', '', 'Titre'),
        R(props, 'texte', 'p', '', 'Texte')));
  });

  enregistrer('encart', function (props) {
    return Section(props, { barre: barrePhoto(props, 'image'), panneaux: panneauPhoto(props, 'image') },
      el('div', { className: 'dy' },
        el('div', { className: 'dy-top' },
          el('div', { className: 'dy-im' }, photo(props, 'image', '')),
          el('div', { className: 'dy-tx' },
            T(props, 'surtitre', 'span', 'dy-k', 'Surtitre'),
            T(props, 'encartTitre', 'h3', '', 'Titre'),
            T(props, 'sousTitre', 'div', 'dy-sub', 'Sous-titre'),
            R(props, 'texte', 'p', '', 'Texte'))),
        Liste('dy-dates', 'schiesser/chiffre', [['schiesser/chiffre'], ['schiesser/chiffre'], ['schiesser/chiffre']]),
        T(props, 'mention', 'div', 'dy-note', 'Mention (facultatif)')));
  }, true);

  enregistrer('carte-salon', function (props) {
    return Section(props, { panneaux: panneauPhoto(props, 's', 'Photo de la suggestion') },
      el('div', { className: 'mn' },
        el('div', null,
          aide('Sur le site : une photo et un onglet par rubrique.'),
          Liste('sch-ed-rubriques', 'schiesser/rubrique', [['schiesser/rubrique'], ['schiesser/rubrique']], 'vertical')),
        el('aside', { className: 'mn-side' },
          el('div', { className: 'sugg' },
            el('div', { className: 'si' }, photo(props, 's', '')),
            el('div', { className: 'sb' },
              T(props, 'sSurtitre', 'div', 'sk', 'La suggestion du jour'),
              T(props, 'sTitre', 'div', 'sh', 'Titre de la suggestion (vide : pas de suggestion)'),
              R(props, 'sTexte', 'p', 'sp', 'Texte'),
              el('div', { className: 'sf' }, T(props, 'sPied', 'span', '', 'Servi toute la journée'), T(props, 'sPrix', 'b', '', 'CHF 0.00')))),
          T(props, 'mention', 'p', 'mn-note', 'Mention sous la carte (facultatif)'))));
  }, true);

  enregistrer('rubrique', function (props) {
    var bp = be.useBlockProps({ className: 'sch-ed-rubrique' });
    return el(Fragment, null, barrePhoto(props, 'image'), el(be.InspectorControls, null, panneauPhoto(props, 'image', 'Photo de la rubrique')),
      el('div', bp,
        el('div', { className: 'sch-ed-rubrique-tete' }, photo(props, 'image', 'sch-ed-vignette-petite'), T(props, 'nom', 'h3', 'mtab on', 'Nom de la rubrique (onglet)')),
        el('div', be.useInnerBlocksProps({ className: 'sch-ed-plats' }, { allowedBlocks: ['schiesser/plat'], template: [['schiesser/plat'], ['schiesser/plat']], orientation: 'vertical' }))));
  }, true);

  enregistrer('plat', function (props) {
    var bp = be.useBlockProps({ className: 'mi' });
    return el('div', bp,
      el('div', { className: 'mi-top' }, T(props, 'nom', 'span', 'mi-nm', 'Nom du plat'), el('span', { className: 'mi-dots' }), T(props, 'prix', 'span', 'mi-pr', 'CHF 0.00')),
      R(props, 'description', 'div', 'mi-d', 'Description'),
      T(props, 'mention', 'span', 'mi-tag sch-ed-tag', 'Mention (Signature…)'));
  });

  enregistrer('panneaux', function (props) {
    var a = props.attributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Indication', initialOpen: false },
        el(c.TextControl, { label: 'Texte sous les panneaux', value: a.indication, onChange: maj(props.setAttributes, 'indication') }))
    }, el(Fragment, null,
      aide('Sur le site : les panneaux photo s’ouvrent au survol.'),
      Liste('sch-ed-grille', 'schiesser/panneau', [['schiesser/panneau'], ['schiesser/panneau'], ['schiesser/panneau'], ['schiesser/panneau']])));
  }, true);

  enregistrer('panneau', function (props) {
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null, barrePhoto(props, 'image'), el(be.InspectorControls, null, panneauPhoto(props, 'image')),
      el('div', bp, photo(props, 'image', 'sch-ed-vignette'), T(props, 'titre', 'h3', '', 'Titre'), R(props, 'texte', 'p', '', 'Texte')));
  });

  enregistrer('moments', function (props) {
    var a = props.attributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Libellé', initialOpen: false },
        el(c.TextControl, { label: 'Libellé du conseil', value: a.conseilLibelle, onChange: maj(props.setAttributes, 'conseilLibelle') }))
    }, el(Fragment, null,
      aide('Sur le site : on choisit un moment de la journée, la photo et le texte changent.'),
      Liste('sch-ed-grille sch-ed-sombre', 'schiesser/moment', [['schiesser/moment'], ['schiesser/moment'], ['schiesser/moment'], ['schiesser/moment']])));
  }, true);

  enregistrer('moment', function (props) {
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null, barrePhoto(props, 'image'), el(be.InspectorControls, null, panneauPhoto(props, 'image')),
      el('div', bp, photo(props, 'image', 'sch-ed-vignette'),
        el('div', { className: 'sch-ed-ligne' }, T(props, 'heure', 'span', 'sch-ed-annee', '07:30'), T(props, 'libelle', 'span', 'sch-ed-petit', 'Le matin')),
        T(props, 'titre', 'h3', '', 'Titre'),
        T(props, 'sousTitre', 'div', 'sch-ed-italique', 'Sous-titre'),
        R(props, 'texte', 'p', '', 'Texte'),
        T(props, 'conseil', 'div', 'sch-ed-petit', 'Ce que nous conseillons')));
  });

  enregistrer('venir', function (props) {
    return Section(props, {
      barre: barrePhoto(props, 'image'),
      panneaux: el(Fragment, null, panneauPhoto(props, 'image'),
        el(c.PanelBody, { title: 'Liens des boutons', initialOpen: false }, champLien(props, 'b1Lien', 'Bouton 1'), champLien(props, 'b2Lien', 'Bouton 2')))
    }, el('div', { className: 'vn' },
      el('div', { className: 'vn-im' }, photo(props, 'image', '')),
      el('div', { className: 'vn-tx' },
        T(props, 'encartTitre', 'h3', '', 'Titre'),
        R(props, 'texte', 'p', '', 'Texte'),
        Liste('vn-rows', 'schiesser/ligne', [['schiesser/ligne', { libelle: 'Adresse', auto: 'adresse' }], ['schiesser/ligne', { libelle: 'Horaires', auto: 'horaires' }]], 'vertical'),
        el('div', { className: 'vn-cta' }, bouton(props, 'b1Texte', 'btn-kir', 'Bouton 1', true), bouton(props, 'b2Texte', 'btn-line', 'Bouton 2')))));
  }, true);

  /* ------------------------------------------------------------------ */
  /* Histoire                                                            */
  /* ------------------------------------------------------------------ */

  enregistrer('origine', function (props) {
    return Section(props, { barre: barrePhoto(props, 'image'), panneaux: panneauPhoto(props, 'image') },
      el('div', { className: 'origin' },
        el('div', be.useInnerBlocksProps({ className: 'origin-txt' }, {
          allowedBlocks: ['core/paragraph', 'schiesser/exergue'],
          template: [['core/paragraph', { placeholder: 'Premier paragraphe (avec lettrine)' }], ['core/paragraph'], ['schiesser/exergue'], ['core/paragraph']]
        })),
        el('figure', { className: 'origin-fig' },
          el('div', { className: 'im' }, photo(props, 'image', '')),
          el('figcaption', null, T(props, 'legende', 'span', '', 'Légende'), T(props, 'figure', 'span', '', 'Fig. 01')))));
  }, true);

  enregistrer('exergue', function (props) {
    var bp = be.useBlockProps({ className: 'pull' });
    return el('div', bp, R(props, 'texte', 'q', 'serif', 'Citation'), T(props, 'auteur', 'div', 'who', 'Auteur'));
  });

  enregistrer('archives', function (props) {
    var a = props.attributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Crédit', initialOpen: false },
        el(c.TextareaControl, { label: 'Mention sous la grille', value: a.credit, onChange: maj(props.setAttributes, 'credit') }))
    }, el(Fragment, null,
      Liste('arch-grid', 'schiesser/archive', [['schiesser/archive'], ['schiesser/archive'], ['schiesser/archive'], ['schiesser/archive']]),
      a.credit ? el('p', { className: 'x-credit' }, a.credit) : null));
  }, true);

  enregistrer('archive', function (props) {
    var a = props.attributes;
    var bp = be.useBlockProps({ className: 'arch' });
    return el(Fragment, null, barrePhoto(props, 'image'),
      el(be.InspectorControls, null, panneauPhoto(props, 'image'), el(c.PanelBody, { title: 'Agrandissement', initialOpen: true },
        el(c.TextareaControl, { label: 'Description (affichée en grand)', value: a.description, onChange: maj(props.setAttributes, 'description') }))),
      el('div', bp, el('span', { className: 'aim' }, photo(props, 'image', '')),
        el('span', { className: 'acap' }, T(props, 'titre', 'b', '', 'Titre'), T(props, 'meta', 'span', '', '1870 · Photographie'))));
  });

  enregistrer('avant-apres', function (props) {
    var a = props.attributes;
    return Section(props, {
      fond: false, classe: 'tn', wrap: 'sec wrap',
      panneaux: el(c.PanelBody, { title: 'Indication', initialOpen: false },
        el(c.TextControl, { label: 'Texte sous le comparateur', value: a.indication, onChange: maj(props.setAttributes, 'indication') }))
    }, el(Fragment, null,
      aide('Sur le site : les deux photos se superposent, un curseur permet de passer de l’une à l’autre. Un onglet par comparaison.'),
      Liste('sch-ed-grille sch-ed-grille--2', 'schiesser/comparaison', [['schiesser/comparaison']])));
  }, true);

  enregistrer('comparaison', function (props) {
    var a = props.attributes;
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null,
      el(be.InspectorControls, null, panneauPhoto(props, 'avant', 'Photo d’hier'), panneauPhoto(props, 'apres', 'Photo d’aujourd’hui'),
        el(c.PanelBody, { title: 'Effet', initialOpen: false }, el(c.ToggleControl, { label: 'Vieillir la photo d’hier (sépia)', checked: !!a.vieillir, onChange: maj(props.setAttributes, 'vieillir') }))),
      el('div', bp,
        T(props, 'onglet', 'div', 'sch-ed-petit', 'Nom de l’onglet (La façade…)'),
        el('div', { className: 'sch-ed-deux' },
          el('div', null, photo(props, 'avant', 'sch-ed-vignette' + (a.vieillir ? ' is-vieilli' : '')), T(props, 'avantLibelle', 'div', 'sch-ed-petit', '1870')),
          el('div', null, photo(props, 'apres', 'sch-ed-vignette'), T(props, 'apresLibelle', 'div', 'sch-ed-petit', 'Aujourd’hui')))));
  });

  /* ------------------------------------------------------------------ */
  /* Visite                                                              */
  /* ------------------------------------------------------------------ */

  enregistrer('plan-horaires', function (props) {
    var a = props.attributes;
    var set = props.setAttributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Carte et horaires', initialOpen: true },
        el(c.ToggleControl, { label: 'Carte interactive (OpenStreetMap)', checked: !!a.carte, onChange: maj(set, 'carte') }),
        el(c.TextControl, { label: 'Titre des horaires', value: a.titreHoraires, onChange: maj(set, 'titreHoraires') }),
        el(c.TextControl, { label: 'Bouton 1 (itinéraire)', value: a.b1Texte, onChange: maj(set, 'b1Texte') }),
        el(c.TextControl, { label: 'Bouton 2 (carte agrandie)', value: a.b2Texte, onChange: maj(set, 'b2Texte') }),
        aide('Adresse, horaires et position se modifient dans Réglages maison.'))
    }, apercu('plan-horaires', props));
  });

  enregistrer('affluence', function (props) {
    var a = props.attributes;
    var set = props.setAttributes;
    var etat = useState('1');
    var jour = etat[0];
    var noms = { '1': 'Lundi', '2': 'Mardi', '3': 'Mercredi', '4': 'Jeudi', '5': 'Vendredi', '6': 'Samedi', '0': 'Dimanche' };
    var donnees = a.donnees || {};
    var valeurs = (donnees[jour] || []).slice();
    while (valeurs.length < 11) valeurs.push(0);
    function regler(k, v) {
      var copie = JSON.parse(JSON.stringify(donnees));
      var liste = (copie[jour] || []).slice();
      while (liste.length < 11) liste.push(0);
      liste[k] = v;
      copie[jour] = liste;
      set({ donnees: copie });
    }
    return Section(props, {
      panneaux: el(Fragment, null,
        el(c.PanelBody, { title: 'Affluence par heure', initialOpen: true },
          el(c.SelectControl, { label: 'Jour à modifier', value: jour, options: Object.keys(noms).map(function (k) { return { label: noms[k], value: k }; }), onChange: etat[1] }),
          valeurs.map(function (v, k) {
            return el(c.RangeControl, { key: jour + '-' + k, label: (a.debut + k) + ' h', value: v, min: 0, max: 100, onChange: function (x) { regler(k, x || 0); } });
          }),
          aide('0 = fermé. Les heures les plus calmes et les plus animées sont calculées automatiquement.')),
        el(c.PanelBody, { title: 'Réglages', initialOpen: false },
          el(c.RangeControl, { label: 'Première heure du graphique', value: a.debut, min: 5, max: 12, onChange: maj(set, 'debut') }),
          el(c.TextControl, { label: 'Notre conseil', value: a.conseil, onChange: maj(set, 'conseil') })))
    }, apercu('affluence', props));
  });

  enregistrer('trajets', function (props) {
    var a = props.attributes;
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Textes', initialOpen: false },
        el(c.TextControl, { label: 'Libellé des départs', value: a.departLibelle, onChange: maj(props.setAttributes, 'departLibelle') }),
        el(c.TextControl, { label: 'Bouton (itinéraire Google Maps)', value: a.boutonTexte, onChange: maj(props.setAttributes, 'boutonTexte') }))
    }, el(Fragment, null,
      aide('Sur le site : on choisit un point de départ, l’itinéraire s’affiche étape par étape.'),
      Liste('sch-ed-grille sch-ed-grille--2 sch-ed-sombre', 'schiesser/trajet', [['schiesser/trajet'], ['schiesser/trajet']])));
  }, true);

  enregistrer('trajet', function (props) {
    var a = props.attributes;
    var bp = be.useBlockProps({ className: 'sch-ed-carte' });
    return el(Fragment, null,
      el(be.InspectorControls, null, el(c.PanelBody, { title: 'Pictogramme', initialOpen: true },
        el(c.SelectControl, { label: 'Pictogramme du départ', value: a.icone, options: optionsPictos(), onChange: maj(props.setAttributes, 'icone') }))),
      el('div', bp,
        el('div', { className: 'sch-ed-ligne' }, el('span', { className: 'sch-ed-picto' }, picto(a.icone)),
          el('div', null, T(props, 'depart', 'div', 'sch-ed-titre', 'Point de départ'), T(props, 'detail', 'div', 'sch-ed-petit', 'Précision'))),
        T(props, 'titre', 'h3', '', 'Titre de l’itinéraire'),
        T(props, 'sousTitre', 'div', 'sch-ed-italique', 'Sous-titre'),
        el('div', { className: 'sch-ed-kpis' },
          el('span', null, 'Durée ', T(props, 'duree', 'b', '', '12'), ' min'),
          el('span', null, 'Changements ', T(props, 'changements', 'b', '', '0')),
          el('span', null, 'Marche ', T(props, 'marche', 'b', '', '4'), ' min')),
        el('div', be.useInnerBlocksProps({ className: 'sch-ed-etapes' }, { allowedBlocks: ['schiesser/etape'], template: [['schiesser/etape'], ['schiesser/etape'], ['schiesser/etape']], orientation: 'vertical' })),
        R(props, 'astuce', 'p', 'sch-ed-petit', 'Astuce (facultatif)')));
  }, true);

  enregistrer('etape', function (props) {
    var a = props.attributes;
    var bp = be.useBlockProps({ className: 'sch-ed-etape' });
    return el(Fragment, null,
      el(be.InspectorControls, null, el(c.PanelBody, { title: 'Pictogramme', initialOpen: true },
        el(c.SelectControl, { label: 'Pictogramme', value: a.icone, options: optionsPictos(), help: '« Marche » trace un chemin en pointillés.', onChange: maj(props.setAttributes, 'icone') }))),
      el('div', bp, el('span', { className: 'sch-ed-picto' }, picto(a.icone)),
        el('div', null, T(props, 'titre', 'div', 'sch-ed-titre', 'Étape'), R(props, 'texte', 'div', '', 'Texte'), T(props, 'duree', 'div', 'sch-ed-petit', 'Durée (≈ 8 min)'))));
  });

  enregistrer('devanture', function (props) {
    return Section(props, { barre: barrePhoto(props, 'image'), panneaux: panneauPhoto(props, 'image') },
      el('div', { className: 'x-front' }, photo(props, 'image', ''),
        el('div', { className: 'x-frc' }, T(props, 'surtitre', 'div', 'x-frk', 'Surtitre'), T(props, 'encartTitre', 'div', 'x-frt', 'Titre'), R(props, 'texte', 'div', 'x-frd', 'Texte'))));
  }, true);

  enregistrer('faq', function (props) {
    return Section(props, {}, Liste('faq', 'schiesser/question', [['schiesser/question'], ['schiesser/question'], ['schiesser/question']], 'vertical'));
  }, true);

  enregistrer('question', function (props) {
    var bp = be.useBlockProps({ className: 'fq open' });
    return el('div', bp,
      el('div', { className: 'sch-ed-q' }, T(props, 'question', 'span', 'q', 'Question'), el('span', { className: 'pm' }, '+')),
      el('div', { className: 'ans' }, R(props, 'reponse', 'p', '', 'Réponse')));
  });

  /* ------------------------------------------------------------------ */
  /* Contact                                                             */
  /* ------------------------------------------------------------------ */

  enregistrer('formulaire', function (props) {
    var a = props.attributes;
    var set = props.setAttributes;
    function champ(cle, requis, long) {
      return el('div', { className: 'xg float' },
        el(long ? 'textarea' : 'input', { disabled: true, rows: long ? 3 : undefined }),
        el('label', null, T(props, cle, 'span', '', 'Libellé'), requis ? el('em', null, ' *') : null),
        el('span', { className: 'bar' }));
    }
    return Section(props, {
      panneaux: el(c.PanelBody, { title: 'Message de confirmation', initialOpen: false },
        el(c.TextControl, { label: 'Titre', value: a.okTitre, onChange: maj(set, 'okTitre') }),
        el(c.TextareaControl, { label: 'Texte', value: a.okTexte, onChange: maj(set, 'okTexte') }),
        aide('Les messages arrivent à l’adresse e-mail des Réglages maison.'))
    }, el('div', { className: 'xf' },
      el('div', { className: 'xf-form' },
        el('div', { className: 'xrow two' }, champ('lNom', true), champ('lEmail', true)),
        champ('lTel', false),
        champ('lMessage', true, true),
        el('div', { className: 'xf-send' }, bouton(props, 'boutonTexte', 'btn-kir', 'Envoyer', true), T(props, 'mentionLegale', 'p', 'xf-note', 'Mention'))),
      el('aside', { className: 'xf-side' }, Liste('sch-ed-lignes', 'schiesser/ligne', [['schiesser/ligne', { libelle: 'Par téléphone', auto: 'telephone' }], ['schiesser/ligne', { libelle: 'Par e-mail', auto: 'email' }]], 'vertical'))));
  }, true);

  enregistrer('fiche-contact', function (props) {
    return Section(props, {}, el('div', { className: 'deps' }, el('div', { className: 'dep' },
      T(props, 'surtitre', 'span', 'dfloor', 'Surtitre'),
      T(props, 'encartTitre', 'h3', '', 'Titre'),
      R(props, 'texte', 'p', '', 'Texte'),
      Liste('dlines', 'schiesser/ligne', [['schiesser/ligne', { libelle: 'E-mail', auto: 'email' }], ['schiesser/ligne', { libelle: 'Téléphone', auto: 'telephone' }]], 'vertical'))));
  }, true);
})(window.wp);

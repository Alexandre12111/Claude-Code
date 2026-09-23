/**
 * Éditeur de blocs : réglages propres au site Schiesser.
 *
 * 1. Retire les styles WordPress qui ne suivent pas la charte (boutons
 *    arrondis, images rondes…) : seuls les styles maison restent proposés.
 * 2. Masque les blocs « de thème » inutiles ici (navigation, requêtes,
 *    commentaires…) pour garder un outil d'ajout simple.
 * 3. Rank Math : les textes des blocs maison (titre du Hero, titres des
 *    sections) sont transmis à l'analyse SEO, sinon Rank Math ne les verrait pas.
 */
(function (wp) {
  var blocks = wp.blocks;
  var data = wp.data;

  var STYLES_RETIRES = {
    'core/button': ['fill', 'outline'],
    'core/image': ['rounded'],
    'core/quote': ['plain'],
    'core/separator': ['wide', 'dots'],
    'core/table': ['stripes']
  };

  var BLOCS_MASQUES = [
    'core/navigation', 'core/navigation-link', 'core/navigation-submenu', 'core/home-link', 'core/page-list',
    'core/site-logo', 'core/site-title', 'core/site-tagline', 'core/loginout',
    'core/query', 'core/query-title', 'core/query-pagination', 'core/query-no-results', 'core/post-template',
    'core/post-title', 'core/post-content', 'core/post-date', 'core/post-excerpt', 'core/post-featured-image',
    'core/post-author', 'core/post-author-name', 'core/post-author-biography', 'core/post-terms',
    'core/post-navigation-link', 'core/post-comments-form', 'core/read-more', 'core/avatar',
    'core/comments', 'core/comment-template', 'core/latest-comments', 'core/term-description',
    'core/archives', 'core/calendar', 'core/categories', 'core/tag-cloud', 'core/rss', 'core/latest-posts',
    'core/template-part'
  ];

  wp.domReady(function () {
    Object.keys(STYLES_RETIRES).forEach(function (bloc) {
      STYLES_RETIRES[bloc].forEach(function (style) {
        try { blocks.unregisterBlockStyle(bloc, style); } catch (e) { /* style absent */ }
      });
    });
    BLOCS_MASQUES.forEach(function (nom) {
      // un bloc déjà utilisé dans la page reste disponible (sinon il serait signalé comme manquant)
      if (blocks.getBlockType(nom) && !estUtilise(nom)) {
        try { blocks.unregisterBlockType(nom); } catch (e) { /* bloc absent */ }
      }
    });
  });

  function estUtilise(nom) {
    var ed = data.select('core/block-editor');
    if (!ed) return false;
    var trouve = false;
    (function parcourir(liste) {
      liste.forEach(function (b) {
        if (b.name === nom) trouve = true;
        if (b.innerBlocks && b.innerBlocks.length) parcourir(b.innerBlocks);
      });
    })(ed.getBlocks());
    return trouve;
  }

  /* ---------- Rank Math ---------- */
  function attr(t) {
    return String(t || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
  }

  // un retour à la ligne dans un titre compte comme une espace (sinon « à<br>Bâle » devient « àBâle »)
  function txt(html) { return String(html || '').replace(/<br\s*\/?>/gi, ' ').replace(/&nbsp;|\u00a0/g, ' '); }

  function contenu(liste) {
    return liste.map(function (b) {
      var a = b.attributes || {};
      if (b.name === 'schiesser/hero') {
        return '<h1>' + txt(a.titre) + '</h1>'
          + (a.surtitre ? '<p>' + txt(a.surtitre) + '</p>' : '')
          + (a.texte ? '<p>' + txt(a.texte) + '</p>' : '')
          + (a.imageUrl ? '<img src="' + attr(a.imageUrl) + '" alt="' + attr(a.imageAlt) + '">' : '');
      }
      if (b.name === 'schiesser/section') {
        return (a.titre ? '<h2>' + txt(a.titre) + '</h2>' : '')
          + (a.note ? '<p>' + txt(a.note) + '</p>' : '')
          + contenu(b.innerBlocks || []);
      }
      if (b.name === 'schiesser/produits') {
        return (a.titre ? '<h2>' + txt(a.titre) + '</h2>' : '') + (a.note ? '<p>' + txt(a.note) + '</p>' : '');
      }
      if (b.name.indexOf('schiesser/') === 0) {
        // Blocs de la maquette : titre de section (H2), titres d'éléments (H3), textes et photos.
        var section = !!(b.attributes && Object.prototype.hasOwnProperty.call(b.attributes, 'fond')) || b.name === 'schiesser/frise' || b.name === 'schiesser/avant-apres' || b.name === 'schiesser/appel';
        var h = '';
        if (a.titre) h += section ? '<h2>' + txt(a.titre) + '</h2>' : '<h3>' + txt(a.titre) + '</h3>';
        if (a.encartTitre) h += '<h3>' + txt(a.encartTitre) + '</h3>';
        if (a.question) h += '<h3>' + txt(a.question) + '</h3>';
        ['note', 'lead', 'surtitre', 'sousTitre', 'texte', 'reponse', 'description', 'retenir', 'conseil', 'valeur', 'libelle', 'nom', 'sTitre', 'sTexte'].forEach(function (k) {
          if (a[k] && typeof a[k] === 'string') h += '<p>' + txt(a[k]) + '</p>';
        });
        ['image', 'avant', 'apres', 's'].forEach(function (p) {
          if (a[p + 'Url']) h += '<img src="' + attr(a[p + 'Url']) + '" alt="' + attr(a[p + 'Alt'] || '') + '">';
        });
        return h + contenu(b.innerBlocks || []);
      }
      try { return blocks.getBlockContent(b); } catch (e) { return ''; }
    }).join('\n');
  }

  function contenuPage() {
    var ed = data.select('core/block-editor');
    return ed ? contenu(ed.getBlocks()) : '';
  }

  if (wp.hooks) {
    wp.hooks.addFilter('rank_math_content', 'schiesser/blocs-maison', function (html) {
      var reconstruit = contenuPage();
      return reconstruit || html;
    });
  }

  // Relance l'analyse quand un texte de bloc maison change.
  var dernier = '';
  var minuterie = null;
  data.subscribe(function () {
    if (!window.rankMathEditor || typeof window.rankMathEditor.refresh !== 'function') return;
    clearTimeout(minuterie);
    minuterie = setTimeout(function () {
      var actuel = contenuPage();
      if (actuel !== dernier) {
        dernier = actuel;
        window.rankMathEditor.refresh('content');
      }
    }, 900);
  });
})(window.wp);

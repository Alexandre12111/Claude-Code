/*!
 * Aurea Media : interactions du site
 * GSAP 3 (ScrollTrigger, SplitText, Flip) + Lenis.
 * Tout le contenu reste lisible sans JavaScript : les états masqués ne
 * s'appliquent que sous html.js-anim, posé dans le <head> et retiré si les
 * bibliothèques ne se chargent pas.
 */
(function () {
  'use strict';

  var doc = document.documentElement;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var hasGsap = typeof window.gsap !== 'undefined';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  window.__aureaReady = true;
  if (!hasGsap) { doc.classList.remove('js-anim'); }

  var gsap = window.gsap;
  if (hasGsap) {
    gsap.registerPlugin.apply(gsap, [window.ScrollTrigger, window.SplitText, window.Flip].filter(Boolean));
    gsap.defaults({ ease: 'expo.out', duration: 1 });
  }
  var ST = window.ScrollTrigger;

  /* ------------------------------------------------------------------
     Défilement fluide (Lenis) synchronisé avec ScrollTrigger
     ------------------------------------------------------------------ */
  var lenis = null;
  if (hasGsap && window.Lenis && !reduce && fine) {
    lenis = new window.Lenis({ duration: 1.15, easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); }, smoothWheel: true });
    lenis.on('scroll', ST.update);
    gsap.ticker.add(function (time) { lenis.raf(time * 1000); });
    gsap.ticker.lagSmoothing(0);
    // Ancres internes
    $$('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = a.getAttribute('href');
        if (id.length < 2) return;
        var el = document.getElementById(id.slice(1));
        if (!el) return;
        e.preventDefault();
        lenis.scrollTo(el, { offset: -90 });
        history.replaceState(null, '', id);
      });
    });
  }
  window.aureaLenis = lenis;

  /* ------------------------------------------------------------------
     En-tête : fond au défilement, masqué en descente, visible en montée
     ------------------------------------------------------------------ */
  var hd = $('[data-hd]');
  var bar = $('.progress span');
  var lastY = window.scrollY, ticking = false;
  function onScroll() {
    var y = window.scrollY;
    if (hd) {
      hd.classList.toggle('is-solid', y > 40);
      var menuOpen = document.body.classList.contains('menu-open') || $('.mega.is-open');
      if (!menuOpen) hd.classList.toggle('is-hidden', y > 420 && y > lastY + 2);
      if (y < lastY - 2) hd.classList.remove('is-hidden');
    }
    if (bar) {
      var max = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.transform = 'scaleX(' + (max > 0 ? y / max : 0) + ')';
    }
    lastY = y;
    ticking = false;
  }
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();

  /* Méga-menu Services */
  $$('.has-mega').forEach(function (item) {
    var btn = $('.nav__link', item), panel = $('.mega', item), timer;
    if (!btn || !panel) return;
    function open() { clearTimeout(timer); btn.setAttribute('aria-expanded', 'true'); panel.classList.add('is-open'); hd && hd.classList.remove('is-hidden'); }
    function close() { btn.setAttribute('aria-expanded', 'false'); panel.classList.remove('is-open'); }
    btn.addEventListener('click', function () { btn.getAttribute('aria-expanded') === 'true' ? close() : open(); });
    if (fine) {
      item.addEventListener('mouseenter', open);
      item.addEventListener('mouseleave', function () { timer = setTimeout(close, 180); });
    }
    item.addEventListener('focusout', function (e) { if (!item.contains(e.relatedTarget)) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && panel.classList.contains('is-open')) { close(); btn.focus(); } });
    document.addEventListener('click', function (e) { if (!item.contains(e.target)) close(); });
  });

  /* Menu mobile */
  var burger = $('.burger'), mnav = $('#menu-mobile');
  if (burger && mnav) {
    var setMenu = function (open) {
      burger.setAttribute('aria-expanded', String(open));
      burger.querySelector('.sr-only').textContent = open ? 'Fermer le menu' : 'Ouvrir le menu';
      document.body.classList.toggle('menu-open', open);
      if (open) {
        mnav.hidden = false;
        requestAnimationFrame(function () { mnav.classList.add('is-open'); });
        lenis && lenis.stop();
        document.body.style.overflow = 'hidden';
        if (hasGsap && !reduce) gsap.fromTo($$('.mnav__list > li', mnav), { y: 40, opacity: 0 }, { y: 0, opacity: 1, stagger: 0.05, duration: 0.9, delay: 0.25 });
        setTimeout(function () { var f = $('a', mnav); f && f.focus(); }, 350);
      } else {
        mnav.classList.remove('is-open');
        lenis && lenis.start();
        document.body.style.overflow = '';
        setTimeout(function () { if (burger.getAttribute('aria-expanded') === 'false') mnav.hidden = true; }, 800);
      }
    };
    burger.addEventListener('click', function () { setMenu(burger.getAttribute('aria-expanded') !== 'true'); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && burger.getAttribute('aria-expanded') === 'true') { setMenu(false); burger.focus(); } });
    $$('a', mnav).forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
    // Piège de focus simple
    mnav.addEventListener('keydown', function (e) {
      if (e.key !== 'Tab') return;
      var f = $$('a, button', mnav).concat([burger]);
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });
  }

  /* ------------------------------------------------------------------
     Formulaires, simulateur, filtres, avis : fonctionnent aussi sans GSAP
     ------------------------------------------------------------------ */
  initEstimator();
  initForms();
  initReviews();
  initFilters();
  initToc();
  initBeforeAfter();
  initTabs();
  $$('.swatch[data-hex]').forEach(function (b) {
    b.addEventListener('click', function () {
      var hex = b.getAttribute('data-hex');
      var done = function () { b.classList.add('is-copied'); setTimeout(function () { b.classList.remove('is-copied'); }, 1600); };
      if (navigator.clipboard) navigator.clipboard.writeText(hex).then(done, done); else done();
    });
  });
  $$('[data-year]').forEach(function (el) { el.textContent = new Date().getFullYear(); });

  if (!hasGsap) return;

  /* ------------------------------------------------------------------
     Titres découpés en lignes (masque) : révélation élégante
     ------------------------------------------------------------------ */
  function splitReveal(el, delay) {
    if (!window.SplitText) { el.style.visibility = 'visible'; return; }
    window.SplitText.create(el, {
      type: 'lines', mask: 'lines', linesClass: 'split-line', autoSplit: true, aria: 'none',
      onSplit: function (self) {
        var tw = gsap.from(self.lines, { yPercent: 115, rotate: 2.5, duration: 1.25, stagger: 0.09, delay: delay || 0, ease: 'expo.out', paused: true });
        el.__tw = tw;
        if (el.__played) tw.progress(1);
        else if (el.__go) { el.__played = true; tw.play(); }
        return tw;
      }
    });
    el.style.visibility = 'visible';
  }
  function playSplit(el) {
    el.__go = true;
    if (el.__tw && !el.__played) { el.__played = true; el.__tw.play(); }
  }

  if (reduce) {
    doc.classList.remove('js-anim');
  } else {
    // Titres découpés en lignes : découpage à l'approche de l'écran (moins de travail au chargement).
    // Les titres H1 des héros ne sont pas découpés : ils s'animent en CSS, sans attendre le JavaScript.
    $$('[data-split]').forEach(function (el) {
      ST.create({ trigger: el, start: 'top bottom+=120', once: true, onEnter: function () { splitReveal(el); } });
      ST.create({ trigger: el, start: 'top 90%', once: true, onEnter: function () { playSplit(el); } });
    });

    // Apparitions simples
    ST.batch('[data-reveal]', {
      start: 'top 90%', once: true,
      onEnter: function (els) {
        els.forEach(function (el, i) {
          if (el.getAttribute('data-reveal') === 'clip') {
            gsap.to(el, { clipPath: 'inset(0% 0 0 0)', duration: 1.4, delay: i * 0.08, ease: 'expo.inOut' });
          } else {
            gsap.to(el, { opacity: 1, y: 0, duration: 1.1, delay: i * 0.08 });
          }
        });
      }
    });

    // Groupes échelonnés
    $$('[data-stagger]').forEach(function (group) {
      gsap.to(group.children, { opacity: 1, y: 0, duration: 1, stagger: 0.07, scrollTrigger: { trigger: group, start: 'top 88%', once: true } });
    });

    // Manifeste : les mots s'allument au fil du défilement
    $$('[data-words]').forEach(function (el) {
      wrapWords(el);
      var words = $$('.w', el);
      ST.create({
        trigger: el, start: 'top 80%', end: 'bottom 45%', scrub: true,
        onUpdate: function (self) {
          var p = self.progress * words.length;
          for (var i = 0; i < words.length; i++) {
            var o = Math.min(1, Math.max(0.55, p - i + 0.55));
            words[i].style.setProperty('--o', o);
          }
        }
      });
    });

    // Compteurs
    $$('[data-count]').forEach(function (el) {
      var to = parseFloat(el.getAttribute('data-count'));
      var dec = (el.getAttribute('data-count').split('.')[1] || '').length;
      var obj = { v: 0 };
      el.textContent = (0).toFixed(dec).replace('.', ',');
      gsap.to(obj, { v: to, duration: 2.2, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 90%', once: true },
        onUpdate: function () { el.textContent = obj.v.toFixed(dec).replace('.', ','); } });
    });

    // Parallaxe dans les captures de sites
    $$('.shot__img img').forEach(function (img) {
      gsap.fromTo(img, { '--py': '0%' }, { '--py': '-15%', ease: 'none', scrollTrigger: { trigger: img.closest('.shot'), start: 'top bottom', end: 'bottom top', scrub: true } });
    });

    // Tracé des spirales d'or
    $$('[data-draw]').forEach(function (svg) {
      var paths = $$('path, line, rect', svg);
      paths.forEach(function (p) {
        var len = p.getTotalLength ? p.getTotalLength() : 1000;
        p.style.strokeDasharray = len;
        p.style.strokeDashoffset = len;
      });
      gsap.to(paths, { strokeDashoffset: 0, duration: 2.6, stagger: 0.12, ease: 'power2.inOut', delay: 0.3,
        scrollTrigger: { trigger: svg, start: 'top 95%', once: true } });
      gsap.to(svg, { rotate: 8, ease: 'none', scrollTrigger: { trigger: svg.closest('section') || svg, start: 'top top', end: 'bottom top', scrub: true } });
    });

    // Méthode : ligne dorée et étapes actives
    $$('.steps').forEach(function (steps) {
      var line = $('.steps__line span', steps);
      var items = $$('.step', steps);
      ST.create({
        trigger: steps, start: 'top 70%', end: 'bottom 60%', scrub: true,
        onUpdate: function (self) {
          if (line) line.style.setProperty('--p', self.progress);
          items.forEach(function (it, i) { it.classList.toggle('is-active', self.progress >= i / items.length); });
        }
      });
      if (line) line.style.setProperty('--p', 0);
    });

    // Bandeau défilant : accélère avec la vitesse de défilement
    var marquees = $$('.marquee__track');
    if (marquees.length) {
      var anims = marquees.map(function (m) { return m.getAnimations ? m.getAnimations()[0] : null; }).filter(Boolean);
      ST.create({ onUpdate: function (self) {
        var v = Math.min(5, 1 + Math.abs(self.getVelocity()) / 600);
        anims.forEach(function (a) { a.playbackRate += (v - a.playbackRate) * 0.2; });
      } });
      gsap.ticker.add(function () { anims.forEach(function (a) { if (a.playbackRate > 1) a.playbackRate += (1 - a.playbackRate) * 0.04; }); });
    }

    // Galerie horizontale épinglée (bureau)
    var mm = gsap.matchMedia();
    mm.add('(min-width: 1024px)', function () {
      $$('.hscroll').forEach(function (wrap) {
        var track = $('.hscroll__track', wrap);
        if (!track) return;
        wrap.classList.add('is-pinnable');
        var dist = function () { return Math.max(0, track.scrollWidth - window.innerWidth + 40); };
        var tween = gsap.to(track, { x: function () { return -dist(); }, ease: 'none',
          scrollTrigger: { trigger: wrap, start: 'top top', end: function () { return '+=' + dist(); }, pin: true, scrub: 0.8, invalidateOnRefresh: true, anticipatePin: 1 } });
        $$('.proj', track).forEach(function (card) {
          var img = $('.shot__img img', card);
          if (img) gsap.fromTo(img, { scale: 1.12 }, { scale: 1, ease: 'none', scrollTrigger: { trigger: card, containerAnimation: tween, start: 'left right', end: 'center center', scrub: true } });
        });
        return function () { wrap.classList.remove('is-pinnable'); gsap.set(track, { clearProps: 'x' }); };
      });
    });

    // Mot géant du pied de page
    $$('.ft__word').forEach(function (w) {
      if (window.SplitText) {
        var s = window.SplitText.create(w, { type: 'chars', mask: 'chars' });
        gsap.from(s.chars, { yPercent: 100, duration: 1.4, stagger: 0.035, ease: 'expo.out', scrollTrigger: { trigger: w, start: 'top 95%', once: true } });
      }
    });
  }

  /* ------------------------------------------------------------------
     Micro-interactions au pointeur précis
     ------------------------------------------------------------------ */
  if (fine && !reduce) {
    // Boutons magnétiques
    $$('[data-magnetic]').forEach(function (el) {
      var xTo = gsap.quickTo(el, 'x', { duration: 0.6, ease: 'elastic.out(1, 0.4)' });
      var yTo = gsap.quickTo(el, 'y', { duration: 0.6, ease: 'elastic.out(1, 0.4)' });
      el.addEventListener('pointermove', function (e) {
        var r = el.getBoundingClientRect();
        xTo((e.clientX - r.left - r.width / 2) * 0.28);
        yTo((e.clientY - r.top - r.height / 2) * 0.35);
      });
      el.addEventListener('pointerleave', function () { xTo(0); yTo(0); });
    });

    // Curseur doré
    var cursor = $('.cursor');
    if (cursor) {
      doc.classList.add('has-cursor');
      cursor.classList.add('is-hidden');
      window.addEventListener('pointermove', function first() { cursor.classList.remove('is-hidden'); window.removeEventListener('pointermove', first); }, { passive: true });
      var cx = gsap.quickTo(cursor, 'x', { duration: 0.45, ease: 'power3.out' });
      var cy = gsap.quickTo(cursor, 'y', { duration: 0.45, ease: 'power3.out' });
      var label = $('.cursor__label', cursor);
      window.addEventListener('pointermove', function (e) { cx(e.clientX); cy(e.clientY); }, { passive: true });
      document.addEventListener('pointerover', function (e) {
        var view = e.target.closest('[data-cursor]');
        var link = e.target.closest('a, button, label, summary, input, select, textarea');
        cursor.classList.toggle('is-view', !!view);
        cursor.classList.toggle('is-link', !view && !!link);
        if (view && label) label.textContent = view.getAttribute('data-cursor');
      });
      document.addEventListener('pointerleave', function () { cursor.classList.add('is-hidden'); });
      document.addEventListener('pointerenter', function () { cursor.classList.remove('is-hidden'); });
    }

    // Aperçu d'image sur les lignes de services
    var preview = $('.svc-preview');
    if (preview) {
      var px = gsap.quickTo(preview, 'x', { duration: 0.7, ease: 'power3.out' });
      var py = gsap.quickTo(preview, 'y', { duration: 0.7, ease: 'power3.out' });
      var imgs = $$('img', preview), loaded = false;
      $$('.svc__row').forEach(function (row, i) {
        row.addEventListener('pointerenter', function () {
          if (!loaded) { loaded = true; imgs.forEach(function (im) { im.src = im.getAttribute('data-src'); }); }
          imgs.forEach(function (im, j) { im.classList.toggle('is-on', j === i); });
          gsap.to(preview, { opacity: 1, scale: 1, duration: 0.5 });
        });
        row.addEventListener('pointerleave', function () { gsap.to(preview, { opacity: 0, scale: 0.6, duration: 0.4 }); });
        row.addEventListener('pointermove', function (e) { px(e.clientX + 40); py(e.clientY); });
      });
    }

    // Inclinaison légère des cartes tarifaires
    $$('[data-tilt]').forEach(function (card) {
      var rx = gsap.quickTo(card, 'rotationX', { duration: 0.6 }), ry = gsap.quickTo(card, 'rotationY', { duration: 0.6 });
      gsap.set(card, { transformPerspective: 900 });
      card.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect();
        ry(((e.clientX - r.left) / r.width - 0.5) * 6);
        rx(-((e.clientY - r.top) / r.height - 0.5) * 6);
      });
      card.addEventListener('pointerleave', function () { rx(0); ry(0); });
    });
  }

  // Recalcule les positions après le chargement des polices et images
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { ST.refresh(); });
  window.addEventListener('load', function () { ST.refresh(); });

  /* ================================================================== */

  function wrapWords(el) {
    var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
    var nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach(function (node) {
      var parts = node.textContent.split(/(\s+)/);
      var frag = document.createDocumentFragment();
      parts.forEach(function (p) {
        if (!p) return;
        if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(p)); return; }
        var s = document.createElement('span');
        s.className = 'w';
        s.textContent = p;
        frag.appendChild(s);
      });
      node.parentNode.replaceChild(frag, node);
    });
  }

  /* Simulateur de budget : formules réelles de l'agence */
  function initEstimator() {
    var form = $('[data-estimator]');
    if (!form) return;
    var out = {
      formula: $('[data-est="formula"]'), price: $('[data-est="price"]'), list: $('[data-est="list"]'),
      delay: $('[data-est="delay"]'), cta: $('[data-est="cta"]'), pages: $('[data-est="pages"]')
    };
    var range = $('input[type="range"]', form);
    var check = '<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8.5l3 3 7-7" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>';
    var PLANS = {
      vitrine: [
        { max: 3, name: 'Site vitrine Essentiel', price: '1 000', unit: '€ HT', delay: '3 semaines', items: ['Site 3 pages sur mesure', 'SEO technique de base', 'Formulaire de contact RGPD', 'Responsive et Core Web Vitals'] },
        { max: 7, name: 'Site vitrine Professionnel', price: '1 800', unit: '€ HT', delay: '4 semaines', items: ['Site 5 à 7 pages sur mesure', 'SEO complet et Search Console', 'Blog intégré prêt à publier', 'Rédaction assistée et formation'] },
        { max: 99, name: 'Site vitrine Premium', price: 'Sur devis', unit: '', delay: 'selon le périmètre', items: ['Site de 8 pages et plus', 'Fonctionnalités sur mesure', 'Stratégie SEO approfondie', 'Accompagnement dans la durée'] }
      ],
      ecommerce: [
        { max: 30, name: 'Boutique Lancement', price: '1 650', unit: '€ HT', delay: '6 semaines', items: ["Jusqu'à 30 produits", 'Design WooCommerce sur mesure', 'Paiement Stripe et PayPal', 'Livraison et CGV configurées'] },
        { max: 500, name: 'Boutique Croissance', price: '3 200', unit: '€ HT', delay: '8 semaines', items: ['Catalogue illimité et variations', 'SEO complet et Google Shopping', 'Relance des paniers abandonnés', 'Codes promo et fidélité'] },
        { max: 1e9, name: 'Boutique Sur mesure', price: 'Sur devis', unit: '', delay: 'selon le périmètre', items: ['Boutique bilingue et multidevise', 'Tarifs B2B et comptes clients', 'Connexion ERP, caisse ou comptabilité', 'Abonnements et ventes récurrentes'] }
      ]
    };
    function val(name) { var el = $('input[name="' + name + '"]:checked', form); return el ? el.value : null; }
    function update() {
      var type = val('type') || 'vitrine';
      var n = parseInt(range.value, 10);
      var isShop = type === 'ecommerce';
      var steps = isShop ? [10, 30, 100, 300, 1000] : [1, 3, 5, 7, 10, 15];
      // Échelle du curseur adaptée au type de site
      range.min = 0; range.max = steps.length - 1;
      if (n > steps.length - 1) { n = steps.length - 1; range.value = n; }
      var qty = steps[n];
      range.style.setProperty('--fill', (n / (steps.length - 1) * 100) + '%');
      var bi = $('input[name="bilingue"]', form).checked;
      var sur = $('input[name="surmesure"]', form).checked;
      var plan = PLANS[type].filter(function (p) { return qty <= p.max; })[0];
      if (bi || sur) plan = PLANS[type][2];
      var brand = $('input[name="branding"]', form).checked;
      out.pages.textContent = isShop ? (qty >= 1000 ? '1 000+' : qty) + ' produits' : (qty >= 15 ? '15+' : qty) + (qty > 1 ? ' pages' : ' page');
      $('[data-est="unit-label"]', form).textContent = isShop ? 'Taille du catalogue' : 'Nombre de pages';
      $('[data-est="scale-min"]', form).textContent = isShop ? '10' : '1';
      $('[data-est="scale-max"]', form).textContent = isShop ? '1 000+' : '15+';
      out.formula.textContent = plan.name;
      out.price.innerHTML = (plan.unit ? '<span class="est__from">à partir de </span>' : '') + plan.price + (plan.unit ? '<small>' + plan.unit + '</small>' : '');
      var items = plan.items.slice();
      if (brand) items.push('Identité visuelle : devis séparé, tarif global avantageux');
      if (bi) items.push('Version bilingue français et anglais');
      out.list.innerHTML = items.map(function (t) { return '<li>' + check + '<span>' + t + '</span></li>'; }).join('');
      out.delay.textContent = plan.delay;
      if (out.cta) {
        var q = new URLSearchParams({ besoin: isShop ? 'Site e-commerce' : 'Site vitrine', formule: plan.name, volume: out.pages.textContent });
        if (brand) q.set('branding', 'oui');
        out.cta.setAttribute('href', out.cta.getAttribute('data-base') + '?' + q.toString());
      }
      if (hasGsap && !reduce) gsap.fromTo(out.price, { y: 10, opacity: 0.3 }, { y: 0, opacity: 1, duration: 0.6 });
    }
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
  }

  /* Formulaires : validation en ligne, préremplissage depuis le simulateur */
  function initForms() {
    $$('form[data-form]').forEach(function (form) {
      var params = new URLSearchParams(location.search);
      if (params.get('besoin')) {
        var r = $('input[name="besoin"][value="' + params.get('besoin') + '"]', form);
        if (r) r.checked = true;
      }
      var msg = $('textarea[name="message"]', form);
      if (msg && params.get('formule')) {
        msg.value = 'Bonjour, le simulateur me propose la formule « ' + params.get('formule') + ' » (' + (params.get('volume') || '') + ')' + (params.get('branding') ? ', avec une identité visuelle' : '') + '. Mon projet : ';
      }
      function check(field) {
        var input = $('input, textarea, select', field);
        if (!input || !input.hasAttribute('required') && input.type !== 'email') return true;
        var ok = input.checkValidity() && (input.value.trim() !== '' || !input.required);
        field.classList.toggle('is-invalid', !ok);
        input.setAttribute('aria-invalid', String(!ok));
        return ok;
      }
      $$('.field', form).forEach(function (f) {
        var input = $('input, textarea', f);
        input && input.addEventListener('blur', function () { if (input.value) check(f); });
        input && input.addEventListener('input', function () { if (f.classList.contains('is-invalid')) check(f); });
      });
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var fields = $$('.field', form), firstBad = null;
        fields.forEach(function (f) { if (!check(f) && !firstBad) firstBad = f; });
        if (firstBad) { $('input, textarea', firstBad).focus(); return; }
        var btn = $('button[type="submit"]', form);
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Envoi en cours…';
        // Maquette : l'envoi réel sera branché sur WordPress (admin-ajax ou REST)
        setTimeout(function () {
          form.classList.add('is-sent');
          var ok = $('.form__ok', form);
          ok.setAttribute('tabindex', '-1');
          ok.focus();
        }, 900);
      });
    });
  }

  /* Avis : boutons et glisser */
  function initReviews() {
    $$('[data-reviews]').forEach(function (wrap) {
      var track = $('.rv-track', wrap);
      if (!track) return;
      var step = function () { var c = $('.rv', track); return c ? c.getBoundingClientRect().width + 16 : 300; };
      var prev = $('[data-rv="prev"]', wrap), next = $('[data-rv="next"]', wrap);
      prev && prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: reduce ? 'auto' : 'smooth' }); });
      next && next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: reduce ? 'auto' : 'smooth' }); });
      var down = false, sx = 0, sl = 0, moved = 0;
      track.addEventListener('pointerdown', function (e) {
        if (e.pointerType !== 'mouse') return;
        down = true; moved = 0; sx = e.clientX; sl = track.scrollLeft; track.classList.add('is-drag');
      });
      window.addEventListener('pointermove', function (e) { if (!down) return; moved = Math.abs(e.clientX - sx); track.scrollLeft = sl - (e.clientX - sx); });
      window.addEventListener('pointerup', function () { if (!down) return; down = false; track.classList.remove('is-drag'); });
    });
  }

  /* Filtres animés (réalisations, articles) avec Flip */
  function initFilters() {
    $$('[data-filters]').forEach(function (bar) {
      var grid = document.getElementById(bar.getAttribute('data-filters'));
      if (!grid) return;
      var items = $$('[data-cat]', grid);
      var live = $('[data-filter-live]', bar.parentNode);
      $$('button[data-filter]', bar).forEach(function (btn) {
        btn.addEventListener('click', function () {
          var f = btn.getAttribute('data-filter');
          $$('button[data-filter]', bar).forEach(function (b) { b.setAttribute('aria-pressed', String(b === btn)); });
          var state = hasGsap && window.Flip && !reduce ? window.Flip.getState(items) : null;
          var shown = 0;
          items.forEach(function (it) {
            var on = f === 'tout' || (' ' + it.getAttribute('data-cat') + ' ').indexOf(' ' + f + ' ') > -1;
            it.classList.toggle('is-hidden', !on);
            if (on) shown++;
          });
          if (live) live.textContent = shown + (shown > 1 ? ' résultats' : ' résultat');
          if (state) {
            window.Flip.from(state, { duration: 0.7, ease: 'expo.out', scale: true, absolute: true,
              onEnter: function (els) { return gsap.fromTo(els, { opacity: 0, scale: 0.94 }, { opacity: 1, scale: 1, duration: 0.6 }); },
              onLeave: function (els) { return gsap.to(els, { opacity: 0, scale: 0.94, duration: 0.3 }); },
              onComplete: function () { window.ScrollTrigger && window.ScrollTrigger.refresh(); } });
          }
        });
      });
    });
  }

  /* Onglets accessibles (tous les contenus restent dans le HTML) */
  function initTabs() {
    $$('[data-tabs]').forEach(function (box) {
      var tabs = $$('[role="tab"]', box);
      function select(tab, focus) {
        tabs.forEach(function (t) {
          var on = t === tab;
          t.setAttribute('aria-selected', String(on));
          t.tabIndex = on ? 0 : -1;
          var panel = document.getElementById(t.getAttribute('aria-controls'));
          if (panel) panel.hidden = !on;
        });
        if (focus) tab.focus();
        if (window.ScrollTrigger) window.ScrollTrigger.refresh();
      }
      tabs.forEach(function (t, i) {
        t.addEventListener('click', function () { select(t); });
        t.addEventListener('keydown', function (e) {
          var k = e.key, n = null;
          if (k === 'ArrowRight') n = tabs[(i + 1) % tabs.length];
          if (k === 'ArrowLeft') n = tabs[(i - 1 + tabs.length) % tabs.length];
          if (k === 'Home') n = tabs[0];
          if (k === 'End') n = tabs[tabs.length - 1];
          if (n) { e.preventDefault(); select(n, true); }
        });
      });
      box.classList.add('is-ready');
      select(tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0]);
    });
  }

  /* Comparateur avant / après */
  function initBeforeAfter() {
    $$('[data-ba]').forEach(function (fig) {
      var r = $('.ba__range', fig);
      var set = function (v) { fig.style.setProperty('--pos', v + '%'); };
      r.addEventListener('input', function () { set(r.value); });
      if (hasGsap && !reduce && window.ScrollTrigger) {
        var o = { v: 88 };
        set(88);
        gsap.to(o, { v: 50, duration: 1.8, ease: 'expo.inOut', scrollTrigger: { trigger: fig, start: 'top 75%', once: true },
          onUpdate: function () { set(o.v); r.value = Math.round(o.v); } });
      }
    });
  }

  /* Sommaire d'article : section active */
  function initToc() {
    var toc = $('.toc');
    if (!toc || !('IntersectionObserver' in window)) return;
    var links = $$('a', toc);
    var map = {};
    links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          links.forEach(function (l) { l.classList.remove('is-active'); });
          var a = map[en.target.id];
          a && a.classList.add('is-active');
        }
      });
    }, { rootMargin: '-20% 0px -70% 0px' });
    Object.keys(map).forEach(function (id) { var h = document.getElementById(id); h && io.observe(h); });
  }
})();

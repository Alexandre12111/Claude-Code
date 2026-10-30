(function () {
  'use strict';

  const root = document.documentElement;
  const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const FINE = window.matchMedia('(pointer: fine)').matches;
  const HAS_GSAP = !!(window.gsap && window.ScrollTrigger);
  const MOTION = HAS_GSAP && !REDUCED;
  const $ = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));
  const fmt = (n) => Math.round(n).toLocaleString('fr-FR');
  const clamp = (v, a, b) => Math.min(b, Math.max(a, v));

  if (REDUCED) root.classList.add('reduced');
  if (HAS_GSAP) gsap.registerPlugin(ScrollTrigger);

  // Les photos répétées partagent la source de leur première occurrence
  $$('img[data-same]').forEach((img) => {
    const src = $('img[data-img="' + img.dataset.same + '"]');
    if (src) img.src = src.src;
  });

  /* ------------------------------------------------------------------ */
  /* Outils                                                              */
  /* ------------------------------------------------------------------ */
  function splitWords(el, cls) {
    const c = cls || 'w';
    if (el.dataset.splitDone) return $$('.' + c + ' > span', el);
    el.dataset.splitDone = '1';
    const walk = (node) => {
      Array.from(node.childNodes).forEach((ch) => {
        if (ch.nodeType === 3) {
          const frag = document.createDocumentFragment();
          ch.textContent.split(/(\s+)/).forEach((p) => {
            if (!p) return;
            if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(p)); return; }
            const w = document.createElement('span');
            w.className = c;
            const i = document.createElement('span');
            i.textContent = p;
            w.appendChild(i);
            frag.appendChild(w);
          });
          node.replaceChild(frag, ch);
        } else if (ch.nodeType === 1) {
          walk(ch);
        }
      });
    };
    walk(el);
    return $$('.' + c + ' > span', el);
  }
  function splitChars(el) {
    const walk = (node) => {
      Array.from(node.childNodes).forEach((ch) => {
        if (ch.nodeType === 3) {
          const frag = document.createDocumentFragment();
          Array.from(ch.textContent).forEach((c) => {
            if (c === ' ') { frag.appendChild(document.createTextNode(' ')); return; }
            const s = document.createElement('span');
            s.className = 'ch';
            s.textContent = c;
            frag.appendChild(s);
          });
          node.replaceChild(frag, ch);
        } else if (ch.nodeType === 1) {
          walk(ch);
        }
      });
    };
    walk(el);
    return $$('.ch', el);
  }

  /* ------------------------------------------------------------------ */
  /* Défilement doux                                                     */
  /* ------------------------------------------------------------------ */
  let lenis = null;
  if (MOTION && window.Lenis) {
    lenis = new Lenis({ lerp: 0.08, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);
  }
  const scrollTop = () => { if (lenis) lenis.scrollTo(0, { immediate: true, force: true }); else window.scrollTo(0, 0); };

  /* ------------------------------------------------------------------ */
  /* Navigation fixe                                                     */
  /* ------------------------------------------------------------------ */
  const nav = $('#nav');
  let lastY = 0;
  function onScroll(y) {
    nav.classList.toggle('is-scrolled', y > 30);
    if (y > 700 && y > lastY + 3 && !root.classList.contains('menu-open')) nav.classList.add('is-hidden');
    else if (y < lastY - 3 || y < 700) nav.classList.remove('is-hidden');
    lastY = y;
  }
  if (lenis) lenis.on('scroll', (l) => onScroll(l.scroll));
  else window.addEventListener('scroll', () => onScroll(window.scrollY), { passive: true });

  // Pastille qui glisse sous le lien actif ou survolé
  const pill = $('.nav__pill');
  const navLinks = $$('.nav__links a');
  function movePill(a) {
    if (!pill) return;
    if (!a) { pill.style.opacity = '0'; navLinks.forEach((l) => l.classList.remove('is-current')); return; }
    pill.style.opacity = '1';
    pill.style.width = a.offsetWidth + 'px';
    pill.style.transform = 'translateX(' + a.offsetLeft + 'px)';
    navLinks.forEach((l) => l.classList.toggle('is-current', l === a));
  }
  const currentLink = () => navLinks.find((l) => l.getAttribute('href') === '#' + current) || null;
  navLinks.forEach((a) => a.addEventListener('pointerenter', () => movePill(a)));
  const linksBox = $('.nav__links');
  if (linksBox) linksBox.addEventListener('pointerleave', () => movePill(currentLink()));

  const menuBtn = $('.nav__menu');
  function closeMenu() {
    root.classList.remove('menu-open');
    if (menuBtn) { menuBtn.setAttribute('aria-expanded', 'false'); menuBtn.textContent = 'Menu'; }
  }
  if (menuBtn) menuBtn.addEventListener('click', () => {
    const open = !root.classList.contains('menu-open');
    root.classList.toggle('menu-open', open);
    menuBtn.setAttribute('aria-expanded', String(open));
    menuBtn.textContent = open ? 'Fermer' : 'Menu';
    if (open && MOTION) gsap.fromTo('.mmenu li', { yPercent: 100, opacity: 0 }, { yPercent: 0, opacity: 1, stagger: 0.05, duration: 1, ease: 'expo.out', delay: 0.3 });
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });

  /* ------------------------------------------------------------------ */
  /* Curseur, boutons magnétiques, survol « eau savonneuse »             */
  /* ------------------------------------------------------------------ */
  if (FINE && MOTION) {
    root.classList.add('has-cursor');
    const cur = $('.cursor'), label = $('.cursor__ring span');
    const ringX = gsap.quickTo('.cursor__ring', 'x', { duration: 0.55, ease: 'power3' });
    const ringY = gsap.quickTo('.cursor__ring', 'y', { duration: 0.55, ease: 'power3' });
    const dotX = gsap.quickTo('.cursor__dot', 'x', { duration: 0.12 });
    const dotY = gsap.quickTo('.cursor__dot', 'y', { duration: 0.12 });
    window.addEventListener('pointermove', (e) => { cur.classList.add('is-on'); ringX(e.clientX); ringY(e.clientY); dotX(e.clientX); dotY(e.clientY); }, { passive: true });
    document.addEventListener('pointerleave', () => cur.classList.remove('is-on'));
    document.addEventListener('pointerover', (e) => {
      const t = e.target.closest('[data-cursor]');
      const l = e.target.closest('a, button, input, textarea, label');
      cur.classList.toggle('is-label', !!t);
      cur.classList.toggle('is-link', !t && !!l);
      if (t) label.textContent = t.dataset.cursor;
    });

    $$('.magnetic').forEach((el) => {
      el.addEventListener('pointermove', (e) => {
        const r = el.getBoundingClientRect();
        const x = e.clientX - r.left, y = e.clientY - r.top;
        el.style.setProperty('--bx', x + 'px');
        el.style.setProperty('--by', y + 'px');
        gsap.to(el, { x: (x - r.width / 2) * 0.2, y: (y - r.height / 2) * 0.3, duration: 0.6, ease: 'power3.out' });
      });
      el.addEventListener('pointerleave', () => gsap.to(el, { x: 0, y: 0, duration: 1, ease: 'elastic.out(1,.4)' }));
    });

    const map = $('#rippleMap'), noise = $('#rippleNoise');
    let rippleTween = null;
    document.addEventListener('pointerover', (e) => {
      const r = e.target.closest('.ripple');
      if (!r || r.classList.contains('is-rippling')) return;
      $$('.ripple.is-rippling').forEach((x) => x.classList.remove('is-rippling'));
      r.classList.add('is-rippling');
      if (rippleTween) rippleTween.kill();
      const st = { s: 0, f: 0 };
      rippleTween = gsap.timeline()
        .to(st, { s: 26, duration: 0.5, ease: 'power2.out', onUpdate: () => map.setAttribute('scale', st.s) })
        .to(st, { s: 0, duration: 1.6, ease: 'power3.out', onUpdate: () => map.setAttribute('scale', st.s) })
        .add(() => r.classList.remove('is-rippling'));
      gsap.fromTo(st, { f: 0 }, { f: 1, duration: 2.1, ease: 'none', onUpdate: () => noise.setAttribute('baseFrequency', (0.008 + st.f * 0.006).toFixed(4) + ' ' + (0.022 - st.f * 0.008).toFixed(4)) });
    });
  }

  /* ------------------------------------------------------------------ */
  /* Titre d'accueil : chaque lettre réagit au curseur                   */
  /* ------------------------------------------------------------------ */
  const morph = $('[data-morph]');
  let chars = [], centers = [], morphRaf = null;
  const pointer = { x: -9999, y: -9999 };
  function measureChars() { centers = chars.map((c) => { const r = c.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; }); }
  function morphFrame() {
    chars.forEach((c, i) => {
      const p = centers[i];
      if (!p) return;
      const d = Math.hypot(pointer.x - p.x, pointer.y - p.y);
      const t = clamp(1 - d / 280, 0, 1);
      const k = t * t * (3 - 2 * t);
      c._w = (c._w == null ? 300 : c._w) + ((300 + k * 420) - (c._w == null ? 300 : c._w)) * 0.18;
      c._s = (c._s == null ? 118 : c._s) + ((118 - k * 46) - (c._s == null ? 118 : c._s)) * 0.18;
      c.style.fontVariationSettings = '"wght" ' + c._w.toFixed(1) + ', "wdth" ' + c._s.toFixed(1);
    });
    morphRaf = requestAnimationFrame(morphFrame);
  }
  if (morph && MOTION) {
    $$('.line', morph).forEach((l) => splitChars(l));
    chars = $$('.ch', morph);
    const hero = morph.closest('.hero');
    hero.addEventListener('pointermove', (e) => { pointer.x = e.clientX; pointer.y = e.clientY; }, { passive: true });
    hero.addEventListener('pointerleave', () => { pointer.x = -9999; pointer.y = -9999; });
    window.addEventListener('resize', measureChars);
  }
  function morphStart() {
    if (!chars.length || morphRaf) return;
    measureChars();
    morphFrame();
    // Une vague traverse le titre à l'ouverture
    const r = morph.getBoundingClientRect();
    const o = { x: r.left - 200 };
    gsap.to(o, { x: r.right + 200, duration: 2.4, ease: 'power2.inOut', delay: 0.2, onUpdate: () => { if (!pointer.real) { pointer.x = o.x; pointer.y = r.top + r.height * 0.55; } }, onComplete: () => { if (!pointer.real) { pointer.x = -9999; } } });
  }
  function morphStop() { if (morphRaf) cancelAnimationFrame(morphRaf); morphRaf = null; }
  window.addEventListener('pointermove', () => { pointer.real = true; }, { once: true });

  /* ------------------------------------------------------------------ */
  /* Bandeau qui suit la vitesse du défilement                           */
  /* ------------------------------------------------------------------ */
  const marquee = $('[data-marquee] .marquee__track');
  let mx = 0, mSkew = 0, mOn = false;
  function marqueeTick() {
    if (!mOn || !marquee) return;
    const v = lenis ? lenis.velocity : 0;
    const half = marquee.scrollWidth / 2;
    mx -= 0.6 + Math.abs(v) * 0.35;
    if (mx <= -half) mx += half;
    mSkew += (clamp(-v * 0.5, -10, 10) - mSkew) * 0.12;
    marquee.style.transform = 'translate3d(' + mx + 'px,0,0) skewX(' + mSkew.toFixed(2) + 'deg)';
  }
  if (HAS_GSAP) gsap.ticker.add(marqueeTick);

  /* ------------------------------------------------------------------ */
  /* Animations propres à chaque page                                    */
  /* ------------------------------------------------------------------ */
  let ctx = null;
  function initView(view, name) {
    if (!MOTION) return;
    ctx = gsap.context(() => {
      $$('.split', view).forEach((h) => {
        const words = splitWords(h);
        const inPhead = h.closest('.phead, .bleed');
        gsap.from(words, { yPercent: 110, duration: 1.5, ease: 'expo.out', stagger: 0.05, delay: inPhead ? 0.35 : 0, scrollTrigger: inPhead ? null : { trigger: h, start: 'top 88%' } });
      });

      $$('.phead .lead, .phead .btn, .phead .idx, .phead .label', view).forEach((el, i) => {
        gsap.from(el, { y: 24, opacity: 0, duration: 1.4, ease: 'expo.out', delay: 0.55 + i * 0.06 });
      });

      $$('.reveal', view).forEach((f) => {
        const img = $('img', f);
        gsap.timeline({ scrollTrigger: { trigger: f, start: 'top 90%' } })
          .from(f, { clipPath: 'inset(100% 0% 0% 0%)', duration: 1.6, ease: 'expo.inOut' })
          .from(img, { scale: 1.3, duration: 2.2, ease: 'expo.out' }, 0.2);
      });

      $$('[data-depth]', view).forEach((img) => {
        gsap.fromTo(img, { yPercent: -6, scale: 1.12 }, { yPercent: 6, scale: 1.12, ease: 'none', scrollTrigger: { trigger: img.closest('.frame'), start: 'top bottom', end: 'bottom top', scrub: true } });
      });

      const fade = $$('.head .lead, .split2__copy > *, .pillar > :not(.frame), .person figcaption, .fig, .door, .tl, .acc__item, .jrow, .press-row li, .coords, .form, .quote-block .who, .quote-block .link, .sim, .film, .tags', view)
        .filter((el) => el.getBoundingClientRect().top > window.innerHeight * 0.9);
      if (fade.length) {
        gsap.set(fade, { y: 40, opacity: 0 });
        ScrollTrigger.batch(fade, { start: 'top 92%', onEnter: (b) => gsap.to(b, { y: 0, opacity: 1, duration: 1.3, ease: 'expo.out', stagger: 0.08, overwrite: true }) });
      }

      $$('[data-words]', view).forEach((p) => {
        const w = splitWords(p, 'mw');
        gsap.fromTo(w, { opacity: 0.14 }, { opacity: 1, ease: 'none', stagger: 0.05, scrollTrigger: { trigger: p, start: 'top 82%', end: 'bottom 50%', scrub: 0.8 } });
      });

      $$('[data-count]', view).forEach((el) => {
        const end = Number(el.dataset.count);
        ScrollTrigger.create({ trigger: el, start: 'top 92%', once: true, onEnter: () => { const o = { v: 0 }; gsap.to(o, { v: end, duration: 2.4, ease: 'power3.out', onUpdate: () => { el.textContent = fmt(o.v); } }); } });
      });

      // Accueil
      if (name === 'accueil') {
        gsap.to('[data-hero-media] img', { yPercent: 10, ease: 'none', scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true } });
        gsap.to('.hero__title', { yPercent: -18, opacity: 0.2, ease: 'none', scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true } });
        gsap.to('.cta .bub', { yPercent: -30, ease: 'none', scrollTrigger: { trigger: '.cta', start: 'top bottom', end: 'bottom top', scrub: true } });
      }

      // Savoir-faire : parcours horizontal épinglé
      if (name === 'savoir-faire') {
        const track = $('[data-htrack]', view), pin = $('[data-hpin]', view), fill = $('[data-hfill]', view);
        const mm = gsap.matchMedia();
        mm.add('(min-width: 1024px)', () => {
          const dist = () => track.scrollWidth - window.innerWidth;
          const tween = gsap.to(track, { x: () => -dist(), ease: 'none', scrollTrigger: { trigger: pin, start: 'top top', end: () => '+=' + dist(), pin: true, scrub: 1, invalidateOnRefresh: true, onUpdate: (s) => { fill.style.transform = 'scaleX(' + s.progress + ')'; } } });
          $$('.hstep', track).forEach((st) => {
            gsap.from($$('.hstep__copy > *', st), { y: 50, opacity: 0, stagger: 0.08, duration: 1.2, ease: 'expo.out', scrollTrigger: { trigger: st, containerAnimation: tween, start: 'left 70%' } });
            gsap.fromTo($('img', st), { scale: 1.3 }, { scale: 1, ease: 'none', scrollTrigger: { trigger: st, containerAnimation: tween, start: 'left right', end: 'right right', scrub: true } });
          });
        });
        if (filmTrigger) filmTrigger(view);
      }

      // Hôtels : les cartes s'empilent
      if (name === 'hotels') {
        const cards = $$('.scard', view);
        if (window.innerWidth > 760) cards.forEach((c, i) => {
          const next = cards[i + 1];
          if (!next) return;
          gsap.to(c, { scale: 0.93 - (cards.length - i) * 0.005, opacity: 0.55, ease: 'none', scrollTrigger: { trigger: next, start: 'top 85%', end: 'top 25%', scrub: true } });
        });
      }

      // Impact : image plein cadre
      if (name === 'impact') {
        gsap.fromTo('[data-bleed]', { yPercent: -6 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: '.bleed', start: 'top top', end: 'bottom top', scrub: true } });
      }
    }, view);
    if (name === 'accueil') { mOn = true; morphStart(); } else { mOn = false; morphStop(); }
  }

  // Le grand logotype du pied de page s'étire en arrivant
  if (MOTION) {
    gsap.fromTo('[data-mark]', { fontVariationSettings: '"wdth" 62, "wght" 200', letterSpacing: '0em' }, { fontVariationSettings: '"wdth" 125, "wght" 200', letterSpacing: '-0.06em', ease: 'none', scrollTrigger: { trigger: '.footer', start: 'top bottom', end: 'bottom bottom', scrub: true } });
  }

  /* ------------------------------------------------------------------ */
  /* Routeur : une page par onglet, transition plein écran               */
  /* ------------------------------------------------------------------ */
  const views = {};
  $$('[data-view]').forEach((v) => { views[v.dataset.view] = v; });
  const order = Object.keys(views);
  let current = null, busy = false;
  const pt = $('.pt');

  function show(name) {
    Object.values(views).forEach((v) => v.classList.toggle('is-active', v.dataset.view === name));
    current = name;
    document.title = (name === 'accueil' ? 'SapoCycle' : views[name].dataset.title + ' · SapoCycle');
    movePill(currentLink());
    if (ctx) { ctx.revert(); ctx = null; }
    scrollTop();
    initView(views[name], name);
    if (HAS_GSAP) ScrollTrigger.refresh();
  }

  function navigate(name) {
    if (!views[name] || name === current || busy) return;
    closeMenu();
    if (!MOTION) { show(name); return; }
    busy = true;
    const panels = $$('.pt__panel', pt);
    $('.pt__title', pt).textContent = views[name].dataset.title;
    $('.pt__idx', pt).textContent = String(order.indexOf(name) + 1).padStart(2, '0') + ' / ' + String(order.length).padStart(2, '0');
    gsap.timeline({ onComplete: () => { busy = false; } })
      .set(pt, { visibility: 'visible', pointerEvents: 'auto' })
      .fromTo(panels, { yPercent: 100 }, { yPercent: 0, duration: 0.8, ease: 'expo.inOut', stagger: 0.09 })
      .fromTo('.pt__txt', { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.6, ease: 'expo.out' }, '-=0.3')
      .add(() => show(name))
      .to('.pt__txt', { opacity: 0, y: -30, duration: 0.45, ease: 'power2.in' }, '+=0.25')
      .to(panels.slice().reverse(), { yPercent: -100, duration: 0.9, ease: 'expo.inOut', stagger: 0.08 }, '-=0.15')
      .set(pt, { visibility: 'hidden', pointerEvents: 'none' });
  }

  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const id = a.getAttribute('href').slice(1);
    if (views[id]) {
      e.preventDefault();
      if (location.hash.slice(1) !== id) location.hash = id;
      else navigate(id);
      return;
    }
    const target = id && document.getElementById(id);
    if (target) {
      e.preventDefault();
      if (lenis) lenis.scrollTo(target, { offset: -nav.offsetHeight, duration: 1.6 });
      else target.scrollIntoView();
    }
  });
  window.addEventListener('hashchange', () => navigate(location.hash.slice(1) || 'accueil'));

  /* ------------------------------------------------------------------ */
  /* Ouverture                                                           */
  /* ------------------------------------------------------------------ */
  const first = views[location.hash.slice(1)] ? location.hash.slice(1) : 'accueil';
  const loader = $('.loader');
  function heroIntro() {
    if (!MOTION || current !== 'accueil') return;
    gsap.timeline({ defaults: { ease: 'expo.out' } })
      .fromTo('[data-hero-media] img', { scale: 1.15 }, { scale: 1, duration: 3, ease: 'power3.out' }, 0)
      .from('.hero__title .line', { yPercent: 60, opacity: 0, duration: 1.6, stagger: 0.12 }, 0.15)
      .from('[data-hero]', { y: 24, opacity: 0, duration: 1.4, stagger: 0.1 }, 0.5)
      .from('.hero__badge', { scale: 0.4, opacity: 0, rotate: -90, duration: 1.6 }, 0.6);
  }
  show(first);
  if (loader) {
    if (MOTION) {
      loader.style.animation = 'none';
      const n = { v: 0 }, out = $('.loader__count', loader);
      gsap.timeline()
        .to(n, { v: 100, duration: 1.3, ease: 'power2.inOut', onUpdate: () => { out.textContent = Math.round(n.v); } })
        .to(loader, { yPercent: -100, duration: 1.1, ease: 'expo.inOut' }, '+=0.1')
        .set(loader, { display: 'none' })
        .add(heroIntro, '-=0.8');
    } else {
      loader.style.display = 'none';
    }
  }

  /* ------------------------------------------------------------------ */
  /* Composants                                                          */
  /* ------------------------------------------------------------------ */
  // Accordéons
  $$('[data-acc] .acc__btn').forEach((b) => {
    const panel = b.nextElementSibling;
    b.addEventListener('click', () => {
      const open = b.getAttribute('aria-expanded') !== 'true';
      b.setAttribute('aria-expanded', String(open));
      if (MOTION) gsap.to(panel, { height: open ? 'auto' : 0, duration: 0.9, ease: 'expo.inOut', onComplete: () => ScrollTrigger.refresh() });
      else panel.style.height = open ? 'auto' : '0';
    });
  });

  // Frise à faire glisser
  $$('[data-drag]').forEach((t) => {
    let down = false, sx = 0, sl = 0, moved = false;
    t.addEventListener('pointerdown', (e) => { if (e.pointerType !== 'mouse') return; down = true; moved = false; sx = e.clientX; sl = t.scrollLeft; t.classList.add('is-drag'); });
    window.addEventListener('pointermove', (e) => { if (!down) return; const dx = e.clientX - sx; if (Math.abs(dx) > 3) moved = true; t.scrollLeft = sl - dx; });
    window.addEventListener('pointerup', () => { down = false; t.classList.remove('is-drag'); });
    t.addEventListener('click', (e) => { if (moved) e.preventDefault(); }, true);
    if (lenis) t.setAttribute('data-lenis-prevent-wheel', '');
  });

  // Journal : l'aperçu suit le curseur
  const prev = $('.jpreview');
  if (prev && FINE && MOTION) {
    const imgs = $$('img', prev);
    const px = gsap.quickTo(prev, 'x', { duration: 0.7, ease: 'power3' });
    const py = gsap.quickTo(prev, 'y', { duration: 0.7, ease: 'power3' });
    window.addEventListener('pointermove', (e) => { px(e.clientX - 150); py(e.clientY - 112); }, { passive: true });
    $$('.jrow').forEach((r) => {
      r.addEventListener('pointerenter', () => {
        imgs.forEach((im, i) => im.classList.toggle('is-on', String(i) === r.dataset.preview));
        gsap.to(prev, { opacity: 1, scale: 1, rotate: -3, duration: 0.6, ease: 'expo.out' });
      });
      r.addEventListener('pointerleave', () => gsap.to(prev, { opacity: 0, scale: 0.6, rotate: 0, duration: 0.5, ease: 'expo.out' }));
    });
  }

  // Simulateur
  const rooms = $('#rooms'), occ = $('#occ');
  const last = { kg: 221, s: 1450, co2: 347 };
  function updateSim() {
    const r = Number(rooms.value), o = Number(occ.value) / 100;
    const kg = r * o * 365 * 0.007, soaps = Math.round(kg / 0.152 / 10) * 10, co2 = kg * 1.57;
    $('#roomsOut').textContent = fmt(r);
    $('#occOut').textContent = Math.round(o * 100) + ' %';
    [rooms, occ].forEach((x) => x.style.setProperty('--p', ((x.value - x.min) / (x.max - x.min)) * 100 + '%'));
    const put = (el, from, to, suf) => {
      if (MOTION) { const t = { v: from }; gsap.to(t, { v: to, duration: 0.6, ease: 'power2.out', overwrite: true, onUpdate: () => { el.textContent = fmt(t.v) + suf; } }); }
      else el.textContent = fmt(to) + suf;
    };
    put($('#resKg'), last.kg, kg, ' kg'); put($('#resSoaps'), last.s, soaps, ''); put($('#resCo2'), last.co2, co2, ' kg');
    Object.assign(last, { kg, s: soaps, co2 });
  }
  if (rooms && occ) { rooms.addEventListener('input', updateSim); occ.addEventListener('input', updateSim); updateSim(); }

  // Formulaires
  const form = $('#joinForm');
  if (form) {
    const validate = (input) => {
      const f = input.closest('.field');
      if (!f) return true;
      const v = input.value.trim();
      const ok = v !== '' && (input.type !== 'email' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v));
      f.classList.toggle('is-invalid', !ok);
      input.setAttribute('aria-invalid', String(!ok));
      return ok;
    };
    $$('[required]', form).forEach((i) => i.addEventListener('blur', () => { if (i.value) validate(i); }));
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const bad = $$('[required]', form).filter((i) => !validate(i));
      if (bad.length) { bad[0].focus(); return; }
      form.classList.add('is-done');
      if (MOTION) gsap.from('.form__done', { y: 20, opacity: 0, duration: 1, ease: 'expo.out' });
    });
  }
  const nf = $('#newsForm');
  if (nf) nf.addEventListener('submit', (e) => { e.preventDefault(); $('button', nf).textContent = 'Merci'; });
  const copyBtn = $('#copyMail');
  if (copyBtn) copyBtn.addEventListener('click', () => {
    const mail = $('#mail');
    const done = () => { copyBtn.textContent = 'Copié'; setTimeout(() => { copyBtn.textContent = 'Copier'; }, 1800); };
    const fallback = () => { const r = document.createRange(); r.selectNodeContents(mail); const s = window.getSelection(); s.removeAllRanges(); s.addRange(r); copyBtn.textContent = 'Sélectionné'; };
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(mail.textContent).then(done, fallback);
    else fallback();
  });

  /* ------------------------------------------------------------------ */
  /* Film « solide ou liquide »                                          */
  /* ------------------------------------------------------------------ */
  var filmTrigger = null;
  const film = $('#film');
  const chapters = [
    { t: 0, label: 'Introduction' }, { t: 6, label: 'Composition' }, { t: 14, label: 'Transport' },
    { t: 22, label: 'Emballage' }, { t: 30, label: 'Usage' }, { t: 38, label: 'Bilan carbone' }, { t: 45, label: 'Seconde vie' }
  ];
  const FILM_END = 52;
  const rand = (a, b) => a + Math.random() * (b - a);
  if (film && HAS_GSAP) {
    const playBtn = $('#filmPlay'), toggle = $('#filmToggle'), seek = $('#filmSeek'), time = $('#filmTime'), restart = $('#filmRestart');
    const chapList = $('#filmChapters'), marks = $('#filmMarks');
    const txt = (n) => $('.film__txt[data-scene="' + n + '"]', film);
    const ends = chapters.map((c, i) => (chapters[i + 1] ? chapters[i + 1].t : FILM_END));
    gsap.set($$('.scene', film), { autoAlpha: 0 });
    gsap.set('#sc1', { autoAlpha: 1 });
    gsap.set($$('.film__txt', film), { autoAlpha: 0 });
    gsap.set(txt(1), { autoAlpha: 1 });
    const tl = gsap.timeline({ paused: true, onUpdate: sync, onComplete: () => setPlaying(false) });
    const T = (t, from, to, pos) => tl.fromTo(t, from, Object.assign({ immediateRender: false }, to), pos);
    chapters.forEach((c, i) => {
      const n = i + 1, s = c.t, e = ends[i];
      if (n > 1) {
        T('#sc' + n, { autoAlpha: 0 }, { autoAlpha: 1, duration: 0.6 }, s);
        T(txt(n), { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: 0.8, ease: 'power3.out' }, s + 0.2);
      }
      if (n < chapters.length) {
        tl.to('#sc' + n, { autoAlpha: 0, duration: 0.5 }, e - 0.5);
        tl.to(txt(n), { autoAlpha: 0, y: -16, duration: 0.45 }, e - 0.55);
      }
    });
    tl.fromTo('.s1-bar', { x: -90, opacity: 0 }, { x: 0, opacity: 1, duration: 1.2, ease: 'power3.out' }, 0.3);
    tl.fromTo('.s1-bottle', { x: 90, opacity: 0 }, { x: 0, opacity: 1, duration: 1.2, ease: 'power3.out' }, 0.5);
    tl.fromTo('.s1-or', { scale: 0, svgOrigin: '840 650' }, { scale: 1, svgOrigin: '840 650', duration: 0.8, ease: 'back.out(2)' }, 1.2);
    T('.s2-water', { y: 260 }, { y: 0, duration: 3.2, ease: 'power2.out' }, 6.6);
    T('.s2-wave', { x: 0 }, { x: -160, duration: 1.3, ease: 'none', repeat: 5 }, 6.4);
    T('.s2-label', { opacity: 0 }, { opacity: 1, duration: 0.8 }, 9.4);
    T('.s3-solid', { x: -260 }, { x: 1750, duration: 5.6, ease: 'none' }, 14.4);
    T('.s3-liquid', { x: -260 }, { x: 2350, duration: 7.4, ease: 'none' }, 14.4);
    T('.s4-bar', { opacity: 0, y: 40 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }, 22.5);
    T('.s4-bottles use', { y: -720, rotation: () => rand(-30, 30), transformOrigin: '50% 50%' }, { y: 0, rotation: () => rand(-7, 7), transformOrigin: '50% 50%', duration: 0.9, ease: 'bounce.out', stagger: 0.24 }, 23);
    T('.s5-flake', { scale: 0, transformOrigin: '50% 50%' }, { scale: 1, transformOrigin: '50% 50%', duration: 0.8, ease: 'back.out(2)' }, 30.8);
    T('.s5-drops use', { y: -140, opacity: 0 }, { y: 0, opacity: 1, duration: 0.6, ease: 'power2.in', stagger: 0.28 }, 31.4);
    T('.s6-a', { attr: { width: 0 } }, { attr: { width: 600 }, duration: 1.4, ease: 'power3.out' }, 38.6);
    T('.s6-ta', { opacity: 0 }, { opacity: 1, duration: 0.6 }, 39.6);
    T('.s6-b', { attr: { width: 0 } }, { attr: { width: 750 }, duration: 1.8, ease: 'power3.out' }, 39.3);
    T('.s6-tb', { opacity: 0 }, { opacity: 1, duration: 0.6 }, 40.8);
    T('.s7-ring', { rotation: 0, svgOrigin: '560 650' }, { rotation: 140, svgOrigin: '560 650', duration: 7, ease: 'none' }, 45);
    T('.s7-bar', { scale: 0.4, opacity: 0, svgOrigin: '560 648' }, { scale: 1, opacity: 1, svgOrigin: '560 648', duration: 1.2, ease: 'elastic.out(1,.5)' }, 45.4);
    T('.s7-bubbles circle', { y: 30, opacity: 0 }, { y: -110, opacity: 1, duration: 2.6, ease: 'power1.out', stagger: 0.3 }, 46);
    T('.s7-big', { opacity: 0, x: 50 }, { opacity: 1, x: 0, duration: 1.1, ease: 'power3.out' }, 46.4);

    chapters.forEach((c, i) => {
      const li = document.createElement('li');
      const b = document.createElement('button');
      b.type = 'button';
      b.textContent = c.label;
      b.addEventListener('click', () => { tl.time(c.t + 0.01); start(); });
      li.appendChild(b);
      chapList.appendChild(li);
      if (i > 0) { const m = document.createElement('i'); m.style.left = (c.t / FILM_END) * 100 + '%'; marks.appendChild(m); }
    });
    const chapBtns = $$('button', chapList);
    let playing = false, userPaused = false;
    const mmss = (s) => Math.floor(s / 60) + ':' + String(Math.floor(s % 60)).padStart(2, '0');
    function sync() {
      const t = tl.time(), p = (t / FILM_END) * 100;
      seek.value = Math.round(p * 10);
      seek.style.setProperty('--p', p + '%');
      time.textContent = mmss(t) + ' / ' + mmss(FILM_END);
      let idx = 0;
      chapters.forEach((c, i) => { if (t >= c.t) idx = i; });
      chapBtns.forEach((b, i) => b.setAttribute('aria-current', String(i === idx)));
    }
    function setPlaying(v) { playing = v; $('use', toggle).setAttribute('href', v ? '#i-pause' : '#i-play'); toggle.setAttribute('aria-label', v ? 'Pause' : 'Lecture'); }
    function start() { film.classList.add('is-started'); if (tl.time() >= FILM_END - 0.05) tl.time(0); tl.play(); setPlaying(true); }
    function pause(byUser) { tl.pause(); setPlaying(false); if (byUser) userPaused = true; }
    tl.time(2.6);
    sync();
    playBtn.addEventListener('click', () => { tl.time(0); start(); });
    toggle.addEventListener('click', () => { if (playing) pause(true); else { userPaused = false; start(); } });
    restart.addEventListener('click', () => { tl.time(0); userPaused = false; start(); });
    seek.addEventListener('input', () => { film.classList.add('is-started'); tl.time((seek.value / 1000) * FILM_END); sync(); });
    // Lecture automatique quand le film entre dans l'écran (déclencheur recréé à chaque visite de la page)
    filmTrigger = () => {
      if (REDUCED) return;
      ScrollTrigger.create({
        trigger: film, start: 'top 60%', end: 'bottom 20%',
        onEnter: () => { if (userPaused) return; if (!film.classList.contains('is-started')) tl.time(0); if (tl.time() < FILM_END - 0.05) start(); },
        onEnterBack: () => { if (!userPaused && film.classList.contains('is-started') && tl.time() < FILM_END - 0.05) start(); },
        onLeave: () => { if (playing) pause(false); },
        onLeaveBack: () => { if (playing) pause(false); }
      });
    };
    window.addEventListener('hashchange', () => { if (playing) pause(false); });
    if (current === 'savoir-faire' && ctx) ctx.add(() => filmTrigger());
  }

  if (HAS_GSAP) {
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => { ScrollTrigger.refresh(); measureChars(); movePill(currentLink()); });
    window.addEventListener('load', () => { ScrollTrigger.refresh(); movePill(currentLink()); });
  }
})();

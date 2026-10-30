(function () {
  'use strict';

  const root = document.documentElement;
  const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const HAS_GSAP = !!(window.gsap && window.ScrollTrigger);
  const MOTION = HAS_GSAP && !REDUCED;
  const $ = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));
  const fmt = (n) => Math.round(n).toLocaleString('fr-FR');

  if (REDUCED) root.classList.add('reduced');
  if (HAS_GSAP) gsap.registerPlugin(ScrollTrigger);

  /* ------------------------------------------------------------------ */
  /* Découpage des titres en mots                                        */
  /* ------------------------------------------------------------------ */
  function splitWords(el, cls) {
    const c = cls || 'w';
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

  /* ------------------------------------------------------------------ */
  /* Défilement doux                                                     */
  /* ------------------------------------------------------------------ */
  let lenis = null;
  if (MOTION && window.Lenis) {
    lenis = new Lenis({ lerp: 0.075, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);
  }
  const nav = $('#nav');
  const navH = () => (nav ? nav.offsetHeight : 0);
  let sweep = () => {};
  function jumpTo(target) {
    const off = target.id === 'top' ? 0 : -navH() + 1;
    if (lenis) lenis.scrollTo(target, { immediate: true, offset: off, force: true });
    else window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY + off, behavior: 'auto' });
    requestAnimationFrame(() => requestAnimationFrame(() => { if (HAS_GSAP) ScrollTrigger.update(); sweep(); }));
  }

  /* ------------------------------------------------------------------ */
  /* Rideau ivoire entre les sections                                    */
  /* ------------------------------------------------------------------ */
  const curtain = $('.curtain');
  let moving = false;
  function goTo(target) {
    if (!MOTION || moving) { jumpTo(target); return; }
    moving = true;
    gsap.timeline({ onComplete: () => { moving = false; } })
      .set(curtain, { pointerEvents: 'auto' })
      .fromTo(curtain, { yPercent: 100 }, { yPercent: 0, duration: 0.75, ease: 'expo.inOut' })
      .fromTo($('svg', curtain), { opacity: 0, y: 10 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }, '-=0.25')
      .add(() => { jumpTo(target); ScrollTrigger.update(); })
      .add(() => { ScrollTrigger.update(); sweep(); }, '+=0.1')
      .to($('svg', curtain), { opacity: 0, duration: 0.3 }, '+=0.05')
      .to(curtain, { yPercent: -100, duration: 0.85, ease: 'expo.inOut' }, '-=0.1')
      .set(curtain, { pointerEvents: 'none', yPercent: 100 });
  }
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const id = a.getAttribute('href').slice(1);
    const target = id ? document.getElementById(id) : null;
    if (!target) return;
    e.preventDefault();
    closeMenu();
    goTo(target);
  });

  /* ------------------------------------------------------------------ */
  /* Navigation                                                          */
  /* ------------------------------------------------------------------ */
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
    if (open && MOTION) gsap.fromTo('.mobile-menu li', { y: 30, opacity: 0 }, { y: 0, opacity: 1, stagger: 0.06, duration: 1, ease: 'expo.out', delay: 0.1 });
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });

  let lastY = 0;
  function onScrollNav(y) {
    if (!nav) return;
    nav.classList.toggle('is-scrolled', y > 30);
    if (y > 800 && y > lastY + 3 && !root.classList.contains('menu-open')) nav.classList.add('is-hidden');
    else if (y < lastY - 3 || y < 800) nav.classList.remove('is-hidden');
    lastY = y;
  }
  if (lenis) lenis.on('scroll', (l) => onScrollNav(l.scroll));
  else window.addEventListener('scroll', () => onScrollNav(window.scrollY), { passive: true });

  const navLinks = $$('.nav__links a');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) navLinks.forEach((a) => a.classList.toggle('is-current', a.getAttribute('href') === '#' + en.target.id));
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    ['maison', 'geste', 'solide-liquide', 'visages'].forEach((id) => { const s = document.getElementById(id); if (s) io.observe(s); });
  }

  /* ------------------------------------------------------------------ */
  /* Ouverture : préchargement puis entrée du hero                       */
  /* ------------------------------------------------------------------ */
  const loader = $('.loader');
  const heroTitle = $('[data-hero-title]');
  function heroIntro() {
    if (!MOTION) return;
    const words = splitWords(heroTitle);
    gsap.timeline({ defaults: { ease: 'expo.out' } })
      .fromTo('[data-hero-media] img', { scale: 1.12 }, { scale: 1, duration: 3.2, ease: 'power3.out' }, 0)
      .from(words, { yPercent: 115, duration: 1.6, stagger: 0.055 }, 0.2)
      .from('[data-hero]', { y: 22, opacity: 0, duration: 1.4, stagger: 0.12 }, 0.55)
      .from('.hero__foot', { opacity: 0, duration: 1.4 }, 1.2);
  }
  if (loader) {
    if (MOTION) {
      loader.style.animation = 'none';
      gsap.timeline()
        .to('.loader__line span', { scaleX: 1, duration: 1.1, ease: 'power2.inOut' })
        .to(loader, { opacity: 0, duration: 0.8, ease: 'power2.inOut' }, '+=0.1')
        .set(loader, { display: 'none' })
        .add(heroIntro, '-=0.7');
    } else {
      loader.style.display = 'none';
    }
  }

  /* ------------------------------------------------------------------ */
  /* Animations au défilement : lentes et feutrées                       */
  /* ------------------------------------------------------------------ */
  if (MOTION) {
    $$('.split').forEach((h) => {
      if (h === heroTitle) return;
      const words = splitWords(h);
      gsap.from(words, { yPercent: 115, duration: 1.5, ease: 'expo.out', stagger: 0.05, scrollTrigger: { trigger: h, start: 'top 86%' } });
    });

    // Photographies : révélation par le bas, puis léger zoom arrière
    $$('.reveal').forEach((f) => {
      if (f.getBoundingClientRect().top < window.innerHeight) return;
      const img = $('img', f);
      gsap.timeline({ scrollTrigger: { trigger: f, start: 'top 88%' } })
        .from(f, { clipPath: 'inset(100% 0% 0% 0%)', duration: 1.6, ease: 'expo.inOut' })
        .from(img, { scale: 1.25, duration: 2.2, ease: 'expo.out' }, 0.2);
    });
    // Profondeur discrète sur les grandes images
    $$('[data-depth]').forEach((img) => {
      gsap.fromTo(img, { yPercent: -5 }, { yPercent: 5, ease: 'none', scrollTrigger: { trigger: img.closest('.frame'), start: 'top bottom', end: 'bottom top', scrub: true } });
    });
    gsap.fromTo('[data-planet]', { yPercent: -6 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: '.planet', start: 'top bottom', end: 'bottom top', scrub: true } });
    gsap.fromTo('[data-lives]', { yPercent: -5 }, { yPercent: 5, ease: 'none', scrollTrigger: { trigger: '.lives', start: 'top bottom', end: 'bottom top', scrub: true } });

    const fade = $$('.lead, .chapter__copy > *, .press__in > *, .founder blockquote, .founder__who, .founder__bio, .person figcaption, .artisans__copy > p, .benefits li, .steps li, .labels, .hotels__copy > div, .lives__copy > *, .coords, .form, .figures .figure, .planet__src, .film')
      .filter((el) => !el.closest('.hero') && el.getBoundingClientRect().top > window.innerHeight);
    gsap.set(fade, { y: 30, opacity: 0 });
    // Balayage à chaque défilement : fiable aussi après un saut par le menu
    let pending = fade.slice();
    sweep = () => {
      const limit = window.innerHeight * 0.92;
      const passed = pending.filter((el) => el.getBoundingClientRect().bottom < 0);
      const now = pending.filter((el) => { const r = el.getBoundingClientRect(); return r.top < limit && r.bottom >= 0; });
      if (!now.length && !passed.length) return;
      pending = pending.filter((el) => !now.includes(el) && !passed.includes(el));
      if (passed.length) gsap.set(passed, { y: 0, opacity: 1 });
      if (now.length) gsap.to(now, { y: 0, opacity: 1, duration: 1.4, ease: 'expo.out', stagger: 0.09, overwrite: true });
    };
    if (lenis) lenis.on('scroll', sweep);
    else window.addEventListener('scroll', sweep, { passive: true });

    // Manifeste : les mots s'éclairent au fil de la lecture
    const man = $('#manifesto');
    if (man) {
      const mw = splitWords(man, 'mw');
      gsap.fromTo(mw, { opacity: 0.16 }, { opacity: 1, ease: 'none', stagger: 0.05, scrollTrigger: { trigger: man, start: 'top 80%', end: 'bottom 45%', scrub: 0.8 } });
    }

    // Chiffres
    $$('[data-count]').forEach((el) => {
      const end = Number(el.dataset.count);
      ScrollTrigger.create({
        trigger: el, start: 'top 90%', once: true,
        onEnter: () => { const o = { v: 0 }; gsap.to(o, { v: end, duration: 2.6, ease: 'power3.out', onUpdate: () => { el.textContent = fmt(o.v); } }); }
      });
    });
  }

  /* ------------------------------------------------------------------ */
  /* Film « solide ou liquide »                                          */
  /* ------------------------------------------------------------------ */
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
      const t = tl.time();
      const p = (t / FILM_END) * 100;
      seek.value = Math.round(p * 10);
      seek.style.setProperty('--p', p + '%');
      time.textContent = mmss(t) + ' / ' + mmss(FILM_END);
      let idx = 0;
      chapters.forEach((c, i) => { if (t >= c.t) idx = i; });
      chapBtns.forEach((b, i) => b.setAttribute('aria-current', String(i === idx)));
    }
    function setPlaying(v) {
      playing = v;
      $('use', toggle).setAttribute('href', v ? '#i-pause' : '#i-play');
      toggle.setAttribute('aria-label', v ? 'Pause' : 'Lecture');
    }
    function start() {
      film.classList.add('is-started');
      if (tl.time() >= FILM_END - 0.05) tl.time(0);
      tl.play();
      setPlaying(true);
    }
    function pause(byUser) { tl.pause(); setPlaying(false); if (byUser) userPaused = true; }

    tl.time(2.6);
    sync();

    playBtn.addEventListener('click', () => { tl.time(0); start(); });
    toggle.addEventListener('click', () => { if (playing) pause(true); else { userPaused = false; start(); } });
    restart.addEventListener('click', () => { tl.time(0); userPaused = false; start(); });
    seek.addEventListener('input', () => { film.classList.add('is-started'); tl.time((seek.value / 1000) * FILM_END); sync(); });

    if (!REDUCED) {
      ScrollTrigger.create({
        trigger: film, start: 'top 60%', end: 'bottom 20%',
        onEnter: () => { if (userPaused) return; if (!film.classList.contains('is-started')) tl.time(0); if (tl.time() < FILM_END - 0.05) start(); },
        onEnterBack: () => { if (!userPaused && film.classList.contains('is-started') && tl.time() < FILM_END - 0.05) start(); },
        onLeave: () => { if (playing) pause(false); },
        onLeaveBack: () => { if (playing) pause(false); }
      });
    }
  }

  /* ------------------------------------------------------------------ */
  /* Formulaires                                                         */
  /* ------------------------------------------------------------------ */
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

  if (HAS_GSAP) {
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => ScrollTrigger.refresh());
    window.addEventListener('load', () => ScrollTrigger.refresh());
  }
})();

(function () {
  'use strict';

  const root = document.documentElement;
  const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const HAS_GSAP = !!(window.gsap && window.ScrollTrigger);
  const MOTION = HAS_GSAP && !REDUCED;
  const $ = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));
  const fmt = (n) => Math.round(n).toLocaleString('fr-FR');

  if (HAS_GSAP) gsap.registerPlugin(ScrollTrigger);

  /* ---------- Défilement doux ---------- */
  let lenis = null;
  if (MOTION && window.Lenis) {
    lenis = new Lenis({ lerp: 0.1, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);
  }

  /* ---------- En-tête ---------- */
  const header = $('#header');
  let lastY = 0;
  function onScroll(y) {
    header.classList.toggle('is-scrolled', y > 40);
    if (y > 500 && y > lastY + 4 && !root.classList.contains('menu-open')) header.classList.add('is-hidden');
    else if (y < lastY - 4 || y < 500) header.classList.remove('is-hidden');
    lastY = y;
  }
  if (lenis) lenis.on('scroll', (l) => onScroll(l.scroll));
  else window.addEventListener('scroll', () => onScroll(window.scrollY), { passive: true });
  onScroll(window.scrollY);

  const burger = $('.burger');
  function setMenu(open) {
    root.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', String(open));
    if (lenis) { if (open) lenis.stop(); else lenis.start(); }
    if (open && MOTION) gsap.fromTo('.drawer li', { y: 24, opacity: 0 }, { y: 0, opacity: 1, duration: 0.7, stagger: 0.04, ease: 'power3.out' });
  }
  if (burger) burger.addEventListener('click', () => setMenu(!root.classList.contains('menu-open')));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && root.classList.contains('menu-open')) { setMenu(false); burger.focus(); } });

  // Ancres internes en défilement doux
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a || a.getAttribute('href').length < 2) return;
    const t = document.getElementById(a.getAttribute('href').slice(1));
    if (!t) return;
    e.preventDefault();
    if (lenis) lenis.scrollTo(t, { offset: -90 }); else t.scrollIntoView();
  });

  /* ---------- Animations ---------- */
  function splitWords(el) {
    const walk = (node) => {
      Array.from(node.childNodes).forEach((ch) => {
        if (ch.nodeType === 3) {
          const frag = document.createDocumentFragment();
          ch.textContent.split(/(\s+)/).forEach((p) => {
            if (!p) return;
            if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(p)); return; }
            const w = document.createElement('span'); w.className = 'w';
            const i = document.createElement('span'); i.textContent = p;
            w.appendChild(i); frag.appendChild(w);
          });
          node.replaceChild(frag, ch);
        } else if (ch.nodeType === 1) walk(ch);
      });
    };
    walk(el);
    return $$('.w > span', el);
  }

  if (MOTION) {
    root.classList.add('js-anim');
    const vh = window.innerHeight;

    // Entrée de page
    const ht = $('[data-hero-title]');
    const intro = gsap.timeline({ defaults: { ease: 'expo.out' } });
    if (ht) intro.from(splitWords(ht), { yPercent: 110, duration: 1.3, stagger: 0.05 }, 0.1);
    intro.from('[data-hero]', { y: 22, opacity: 0, duration: 1.1, stagger: 0.09 }, 0.3);
    intro.from('[data-hero-img]', { clipPath: 'inset(18% 14% 18% 14% round 64px)', scale: 1.06, duration: 1.6, ease: 'expo.inOut', stagger: 0.15 }, 0.1);
    intro.from('[data-hero-card]', { y: 30, opacity: 0, scale: 0.94, duration: 1.1, ease: 'back.out(1.4)' }, 0.9);
    $$('[data-float]').forEach((b, i) => gsap.to(b, { y: -18 - i * 6, duration: 3 + i, ease: 'sine.inOut', yoyo: true, repeat: -1 }));

    // Titres de section : les mots montent
    $$('.split').forEach((h) => {
      if (h === ht) return;
      const words = splitWords(h);
      if (h.getBoundingClientRect().top < vh) { gsap.from(words, { yPercent: 110, duration: 1.2, stagger: 0.04, ease: 'expo.out', delay: 0.15 }); return; }
      gsap.from(words, { yPercent: 110, duration: 1.2, stagger: 0.04, ease: 'expo.out', scrollTrigger: { trigger: h, start: 'top 88%' } });
    });

    // Apparitions en fondu
    const rev = $$('[data-reveal]');
    rev.filter((el) => el.getBoundingClientRect().top < vh).forEach((el, i) => gsap.to(el, { opacity: 1, y: 0, duration: 1.1, ease: 'expo.out', delay: 0.2 + i * 0.06 }));
    ScrollTrigger.batch(rev.filter((el) => el.getBoundingClientRect().top >= vh), {
      start: 'top 90%',
      onEnter: (b) => gsap.to(b, { opacity: 1, y: 0, duration: 1.1, ease: 'expo.out', stagger: 0.09, overwrite: true })
    });

    // Photos qui s'ouvrent dans leur forme de savon
    $$('[data-clip]').forEach((f) => {
      gsap.to(f, { clipPath: 'inset(0% 0% 0% 0% round 0px)', duration: 1.6, ease: 'expo.inOut', scrollTrigger: { trigger: f, start: 'top 85%' }, onComplete: () => { f.style.clipPath = 'none'; } });
    });
    $$('[data-parallax]').forEach((img) => {
      gsap.fromTo(img, { yPercent: -6, scale: 1.12 }, { yPercent: 6, scale: 1.12, ease: 'none', scrollTrigger: { trigger: img.parentElement, start: 'top bottom', end: 'bottom top', scrub: true } });
    });

    // Compteurs
    $$('[data-count]').forEach((el) => {
      const end = Number(el.dataset.count);
      ScrollTrigger.create({ trigger: el, start: 'top 92%', once: true, onEnter: () => { const o = { v: 0 }; gsap.to(o, { v: end, duration: 2.2, ease: 'power3.out', onUpdate: () => { el.textContent = fmt(o.v); } }); } });
    });

    // Ligne qui relie les étapes
    $$('[data-draw]').forEach((line) => {
      gsap.fromTo(line, { attr: { x2: 0 } }, { attr: { x2: 100 }, ease: 'none', scrollTrigger: { trigger: line.closest('[data-steps]'), start: 'top 75%', end: 'bottom 55%', scrub: 0.6 } });
    });

    // Frise de l'histoire
    const tlFill = $('.timeline__fill');
    if (tlFill) gsap.to(tlFill, { height: () => tlFill.parentElement.offsetHeight - 16, ease: 'none', scrollTrigger: { trigger: tlFill.parentElement, start: 'top 70%', end: 'bottom 60%', scrub: 0.5, invalidateOnRefresh: true } });
  }

  /* ---------- Récit avec image fixe (notre approche) ---------- */
  const story = $('[data-story]');
  if (story) {
    const imgs = $$('.story__media img', story), badge = $('.story__badge', story), steps = $$('.story__step', story);
    const activate = (i) => {
      steps.forEach((s, k) => s.classList.toggle('is-on', k === i));
      imgs.forEach((im, k) => im.classList.toggle('is-on', k === i));
      if (badge) badge.textContent = steps[i].dataset.badge || '';
    };
    activate(0);
    if (HAS_GSAP) steps.forEach((s, i) => ScrollTrigger.create({ trigger: s, start: 'top 55%', end: 'bottom 55%', onToggle: (st) => { if (st.isActive) activate(i); } }));
  }

  /* ---------- Témoignages ---------- */
  const qs = $('[data-quotes]');
  if (qs) {
    const items = $$('.quote', qs), count = $('[data-q="count"]'), pauseBtn = $('[data-q="pause"]');
    let i = 0, timer = null, paused = REDUCED;
    const show = (n) => { i = (n + items.length) % items.length; items.forEach((q, k) => q.classList.toggle('is-active', k === i)); count.textContent = (i + 1) + ' / ' + items.length; };
    const loop = () => { clearInterval(timer); if (!paused) timer = setInterval(() => show(i + 1), 7000); };
    $('[data-q="prev"]').addEventListener('click', () => { show(i - 1); loop(); });
    $('[data-q="next"]').addEventListener('click', () => { show(i + 1); loop(); });
    const setPauseIcon = () => { $('use', pauseBtn).setAttribute('href', paused ? '#i-play' : '#i-pause'); pauseBtn.setAttribute('aria-label', paused ? 'Reprendre le défilement' : 'Mettre en pause le défilement'); };
    pauseBtn.addEventListener('click', () => { paused = !paused; setPauseIcon(); loop(); });
    qs.addEventListener('pointerenter', () => clearInterval(timer));
    qs.addEventListener('pointerleave', loop);
    qs.addEventListener('focusin', () => clearInterval(timer));
    setPauseIcon(); show(0); loop();
  }

  /* ---------- Filtres (partenaires, actualités) ---------- */
  $$('[data-filters]').forEach((bar) => {
    const target = $(bar.dataset.filters);
    const btns = $$('button', bar);
    btns.forEach((b) => b.addEventListener('click', () => {
      btns.forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
      const f = b.dataset.f;
      const items = $$('[data-cat]', target);
      items.forEach((it) => { it.hidden = f !== 'all' && it.dataset.cat !== f; });
      if (MOTION) gsap.fromTo(items.filter((it) => !it.hidden), { y: 16, opacity: 0 }, { y: 0, opacity: 1, duration: 0.6, stagger: 0.03, ease: 'power3.out' });
      if (HAS_GSAP) ScrollTrigger.refresh();
    }));
  });

  /* ---------- Simulateur (hôtels) ---------- */
  const rooms = $('#rooms'), occ = $('#occ');
  if (rooms && occ) {
    const last = { kg: 0, s: 0, c: 0 };
    const put = (el, from, to, suf) => {
      if (MOTION) { const o = { v: from }; gsap.to(o, { v: to, duration: 0.6, ease: 'power2.out', overwrite: true, onUpdate: () => { el.textContent = fmt(o.v) + suf; } }); }
      else el.textContent = fmt(to) + suf;
    };
    const update = () => {
      const r = Number(rooms.value), o = Number(occ.value) / 100;
      const kg = r * o * 365 * 0.007, s = Math.round(kg / 0.152 / 10) * 10, c = kg * 1.57;
      $('#roomsOut').textContent = fmt(r);
      $('#occOut').textContent = Math.round(o * 100) + ' %';
      [rooms, occ].forEach((x) => x.style.setProperty('--p', ((x.value - x.min) / (x.max - x.min)) * 100 + '%'));
      put($('#resKg'), last.kg, kg, ' kg'); put($('#resSoaps'), last.s, s, ''); put($('#resCo2'), last.c, c, ' kg');
      Object.assign(last, { kg, s, c });
    };
    rooms.addEventListener('input', update); occ.addEventListener('input', update);
    update();
  }

  /* ---------- Formulaires ---------- */
  $$('form[data-validate]').forEach((form) => {
    const check = (input) => {
      const f = input.closest('.field');
      if (!f) return true;
      const v = input.value.trim();
      const ok = v !== '' && (input.type !== 'email' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v));
      f.classList.toggle('is-invalid', !ok);
      input.setAttribute('aria-invalid', String(!ok));
      return ok;
    };
    $$('[required]', form).forEach((i) => i.addEventListener('blur', () => { if (i.value) check(i); }));
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const bad = $$('[required]', form).filter((i) => !check(i));
      if (bad.length) { bad[0].focus(); return; }
      form.classList.add('is-done');
      const done = $('.form__done', form);
      if (done) done.focus();
    });
  });
  $$('[data-news]').forEach((f) => f.addEventListener('submit', (e) => { e.preventDefault(); $('button', f).textContent = 'Merci'; }));
  const copyBtn = $('#copyMail');
  if (copyBtn) copyBtn.addEventListener('click', () => {
    const mail = $('#mail');
    const fallback = () => { const r = document.createRange(); r.selectNodeContents(mail); const s = window.getSelection(); s.removeAllRanges(); s.addRange(r); copyBtn.textContent = 'Sélectionné'; };
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(mail.textContent).then(() => { copyBtn.textContent = 'Copié'; setTimeout(() => { copyBtn.textContent = 'Copier'; }, 1800); }, fallback);
    else fallback();
  });

  /* ---------- Film « solide ou liquide » ---------- */
  const film = $('#film');
  if (film && HAS_GSAP) {
    const chapters = [
      { t: 0, label: 'Introduction' }, { t: 6, label: 'Composition' }, { t: 14, label: 'Transport' },
      { t: 22, label: 'Emballage' }, { t: 30, label: 'Usage' }, { t: 38, label: 'Bilan carbone' }, { t: 45, label: 'Seconde vie' }
    ];
    const END = 52;
    const rand = (a, b) => a + Math.random() * (b - a);
    const playBtn = $('#filmPlay'), toggle = $('#filmToggle'), seek = $('#filmSeek'), time = $('#filmTime'), restart = $('#filmRestart');
    const chapList = $('#filmChapters'), marks = $('#filmMarks');
    const txt = (n) => $('.film__txt[data-scene="' + n + '"]', film);
    const ends = chapters.map((c, i) => (chapters[i + 1] ? chapters[i + 1].t : END));
    gsap.set($$('.scene', film), { autoAlpha: 0 });
    gsap.set('#sc1', { autoAlpha: 1 });
    gsap.set($$('.film__txt', film), { autoAlpha: 0 });
    gsap.set(txt(1), { autoAlpha: 1 });
    const tl = gsap.timeline({ paused: true, onUpdate: sync, onComplete: () => setPlaying(false) });
    const T = (t, from, to, pos) => tl.fromTo(t, from, Object.assign({ immediateRender: false }, to), pos);
    chapters.forEach((c, i) => {
      const n = i + 1, s = c.t, e = ends[i];
      if (n > 1) { T('#sc' + n, { autoAlpha: 0 }, { autoAlpha: 1, duration: 0.6 }, s); T(txt(n), { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: 0.8, ease: 'power3.out' }, s + 0.2); }
      if (n < chapters.length) { tl.to('#sc' + n, { autoAlpha: 0, duration: 0.5 }, e - 0.5); tl.to(txt(n), { autoAlpha: 0, y: -16, duration: 0.45 }, e - 0.55); }
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
      const li = document.createElement('li'), b = document.createElement('button');
      b.type = 'button'; b.textContent = c.label;
      b.addEventListener('click', () => { tl.time(c.t + 0.01); start(); });
      li.appendChild(b); chapList.appendChild(li);
      if (i > 0) { const m = document.createElement('i'); m.style.left = (c.t / END) * 100 + '%'; marks.appendChild(m); }
    });
    const chapBtns = $$('button', chapList);
    let playing = false, userPaused = false;
    const mmss = (s) => Math.floor(s / 60) + ':' + String(Math.floor(s % 60)).padStart(2, '0');
    function sync() {
      const t = tl.time(), p = (t / END) * 100;
      seek.value = Math.round(p * 10); seek.style.setProperty('--p', p + '%');
      time.textContent = mmss(t) + ' / ' + mmss(END);
      let idx = 0; chapters.forEach((c, i) => { if (t >= c.t) idx = i; });
      chapBtns.forEach((b, i) => b.setAttribute('aria-current', String(i === idx)));
    }
    function setPlaying(v) { playing = v; $('use', toggle).setAttribute('href', v ? '#i-pause' : '#i-play'); toggle.setAttribute('aria-label', v ? 'Pause' : 'Lecture'); }
    function start() { film.classList.add('is-started'); if (tl.time() >= END - 0.05) tl.time(0); tl.play(); setPlaying(true); }
    function pause(byUser) { tl.pause(); setPlaying(false); if (byUser) userPaused = true; }
    tl.time(2.6); sync();
    playBtn.addEventListener('click', () => { tl.time(0); start(); });
    toggle.addEventListener('click', () => { if (playing) pause(true); else { userPaused = false; start(); } });
    restart.addEventListener('click', () => { tl.time(0); userPaused = false; start(); });
    seek.addEventListener('input', () => { film.classList.add('is-started'); tl.time((seek.value / 1000) * END); sync(); });
    if (!REDUCED) ScrollTrigger.create({
      trigger: film, start: 'top 60%', end: 'bottom 20%',
      onEnter: () => { if (userPaused) return; if (!film.classList.contains('is-started')) tl.time(0); if (tl.time() < END - 0.05) start(); },
      onEnterBack: () => { if (!userPaused && film.classList.contains('is-started') && tl.time() < END - 0.05) start(); },
      onLeave: () => { if (playing) pause(false); },
      onLeaveBack: () => { if (playing) pause(false); }
    });
  }

  if (HAS_GSAP) {
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => ScrollTrigger.refresh());
    window.addEventListener('load', () => ScrollTrigger.refresh());
  }
})();

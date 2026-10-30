(function () {
  'use strict';

  const root = document.documentElement;
  const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const FINE = window.matchMedia('(pointer: fine)').matches;
  const HAS_GSAP = !!(window.gsap && window.ScrollTrigger);
  const TAU = Math.PI * 2;
  const $ = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));
  const rand = (a, b) => a + Math.random() * (b - a);
  const fmt = (n) => Math.round(n).toLocaleString('fr-FR');

  root.classList.remove('no-js');
  if (REDUCED) root.classList.add('reduced');
  if (!HAS_GSAP) root.classList.add('no-gsap');
  if (HAS_GSAP) gsap.registerPlugin(ScrollTrigger);

  /* ------------------------------------------------------------------ */
  /* Champ de bulles (canvas)                                            */
  /* ------------------------------------------------------------------ */
  class BubbleField {
    constructor(canvas, opt) {
      this.c = canvas;
      this.ctx = canvas.getContext('2d');
      this.o = Object.assign({ count: 30, min: 5, max: 44, speed: [16, 46], dark: false, interactive: false }, opt);
      this.host = canvas.parentElement;
      this.bs = [];
      this.pops = [];
      this.mouse = { x: -999, y: -999, on: false };
      this.sprite = BubbleField.sprite(this.o.dark);
      this.resize();
      for (let i = 0; i < this.o.count; i++) this.bs.push(this.make(true));
      if ('ResizeObserver' in window) new ResizeObserver(() => this.resize()).observe(this.host);
      this.visible = false;
      new IntersectionObserver((e) => {
        this.visible = e[0].isIntersecting;
        if (this.visible && !this.raf && !REDUCED) this.start();
      }, { rootMargin: '120px' }).observe(this.host);
      if (this.o.interactive && FINE && !REDUCED) {
        this.host.addEventListener('pointermove', (e) => {
          const r = this.c.getBoundingClientRect();
          this.mouse.x = e.clientX - r.left;
          this.mouse.y = e.clientY - r.top;
          this.mouse.on = true;
        });
        this.host.addEventListener('pointerleave', () => { this.mouse.on = false; });
      }
      if (REDUCED) this.draw();
    }
    static sprite(dark) {
      const S = 256, C = S / 2, R = S / 2 - 6;
      const c = document.createElement('canvas');
      c.width = c.height = S;
      const g = c.getContext('2d');
      const gr = g.createRadialGradient(C, C, R * 0.4, C, C, R);
      gr.addColorStop(0, 'rgba(255,255,255,0)');
      gr.addColorStop(0.72, dark ? 'rgba(82,171,255,.07)' : 'rgba(82,171,255,.08)');
      gr.addColorStop(0.9, dark ? 'rgba(150,200,255,.24)' : 'rgba(1,117,214,.16)');
      gr.addColorStop(1, dark ? 'rgba(255,255,255,.55)' : 'rgba(255,255,255,.95)');
      g.fillStyle = gr;
      g.beginPath(); g.arc(C, C, R, 0, TAU); g.fill();
      if (g.createConicGradient) {
        const cg = g.createConicGradient(0.6, C, C);
        cg.addColorStop(0, 'rgba(233,72,143,.34)');
        cg.addColorStop(0.25, 'rgba(82,171,255,.38)');
        cg.addColorStop(0.5, 'rgba(120,220,200,.30)');
        cg.addColorStop(0.75, 'rgba(255,214,120,.26)');
        cg.addColorStop(1, 'rgba(233,72,143,.34)');
        g.strokeStyle = cg;
      } else {
        g.strokeStyle = 'rgba(82,171,255,.35)';
      }
      g.lineWidth = 6;
      g.beginPath(); g.arc(C, C, R - 5, 0, TAU); g.stroke();
      g.strokeStyle = dark ? 'rgba(255,255,255,.38)' : 'rgba(1,105,180,.30)';
      g.lineWidth = 2;
      g.beginPath(); g.arc(C, C, R - 1, 0, TAU); g.stroke();
      g.save();
      g.translate(C - R * 0.42, C - R * 0.46);
      g.rotate(-0.7);
      g.fillStyle = 'rgba(255,255,255,.92)';
      g.beginPath(); g.ellipse(0, 0, R * 0.22, R * 0.1, 0, 0, TAU); g.fill();
      g.restore();
      g.fillStyle = 'rgba(255,255,255,.75)';
      g.beginPath(); g.arc(C + R * 0.45, C + R * 0.5, R * 0.06, 0, TAU); g.fill();
      return c;
    }
    resize() {
      const r = this.host.getBoundingClientRect();
      this.w = r.width; this.h = r.height;
      const d = Math.min(window.devicePixelRatio || 1, 2);
      this.c.width = Math.max(1, Math.round(this.w * d));
      this.c.height = Math.max(1, Math.round(this.h * d));
      this.ctx.setTransform(d, 0, 0, d, 0, 0);
      if (REDUCED) this.draw();
    }
    make(initial) {
      const { min, max, speed } = this.o;
      const r = min + Math.pow(Math.random(), 2.2) * (max - min);
      return {
        x: rand(0, this.w), y: initial ? rand(0, this.h) : this.h + r + rand(0, 120), r,
        vy: rand(speed[0], speed[1]) * (1.25 - (r / max) * 0.55), vx: 0,
        wob: rand(0.3, 1.1), ph: rand(0, TAU), f: rand(0.5, 1.4), a: rand(0.55, 1)
      };
    }
    burst(x, y, n) {
      for (let i = 0; i < (n || 18); i++) {
        this.bs.push({ x: x + rand(-30, 30), y: y + rand(-14, 14), r: rand(4, 26), vy: rand(60, 170), vx: rand(-130, 130), wob: rand(0.2, 0.8), ph: rand(0, TAU), f: rand(0.6, 1.4), a: rand(0.7, 1), burst: true });
      }
      if (!this.raf && !REDUCED) this.start();
    }
    pop(b) { this.pops.push({ x: b.x, y: b.y, r: b.r, t: 0 }); }
    start() {
      let last = performance.now();
      const loop = (now) => {
        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;
        this.step(dt, now / 1000);
        this.draw();
        if (this.visible && !document.hidden) this.raf = requestAnimationFrame(loop);
        else this.raf = null;
      };
      this.raf = requestAnimationFrame(loop);
    }
    step(dt, t) {
      const m = this.mouse;
      for (let i = this.bs.length - 1; i >= 0; i--) {
        const b = this.bs[i];
        b.y -= b.vy * dt;
        if (b.vx) { b.x += b.vx * dt; b.vx *= 0.95; }
        b.x += Math.sin(t * b.f + b.ph) * b.wob * 0.6;
        if (m.on) {
          const dx = b.x - m.x, dy = b.y - m.y, d = Math.hypot(dx, dy) || 1;
          if (d < b.r * 0.9 && b.r < 30) {
            this.pop(b);
            if (b.burst) this.bs.splice(i, 1); else this.bs[i] = this.make(false);
            continue;
          } else if (d < 130) {
            const k = (130 - d) * 0.035;
            b.x += (dx / d) * k; b.y += (dy / d) * k;
          }
        }
        if (b.y < -b.r - 20 || b.x < -80 || b.x > this.w + 80) {
          if (b.burst) this.bs.splice(i, 1); else this.bs[i] = this.make(false);
        }
      }
      for (let i = this.pops.length - 1; i >= 0; i--) {
        this.pops[i].t += dt;
        if (this.pops[i].t > 0.42) this.pops.splice(i, 1);
      }
    }
    draw() {
      const g = this.ctx;
      g.clearRect(0, 0, this.w, this.h);
      for (const b of this.bs) {
        g.globalAlpha = b.a;
        g.drawImage(this.sprite, b.x - b.r, b.y - b.r, b.r * 2, b.r * 2);
      }
      g.lineWidth = 1.2;
      for (const p of this.pops) {
        const k = p.t / 0.42;
        g.globalAlpha = 1 - k;
        g.strokeStyle = this.o.dark ? 'rgba(255,255,255,.8)' : 'rgba(1,117,214,.55)';
        g.beginPath(); g.arc(p.x, p.y, p.r * (1 + k * 1.6), 0, TAU); g.stroke();
        g.fillStyle = g.strokeStyle;
        for (let j = 0; j < 7; j++) {
          const a = (j / 7) * TAU, rr = p.r * (1.1 + k * 2.4);
          g.beginPath(); g.arc(p.x + Math.cos(a) * rr, p.y + Math.sin(a) * rr, 1.6 * (1 - k), 0, TAU); g.fill();
        }
      }
      g.globalAlpha = 1;
    }
  }

  const heroField = $('#heroBubbles') ? new BubbleField($('#heroBubbles'), { count: 26, interactive: true }) : null;
  if ($('#impactBubbles')) new BubbleField($('#impactBubbles'), { count: 26, max: 52, dark: true, speed: [10, 30] });
  if ($('#contactBubbles')) new BubbleField($('#contactBubbles'), { count: 16, max: 60, dark: true, speed: [8, 22] });

  /* ------------------------------------------------------------------ */
  /* Découpage des titres en mots                                        */
  /* ------------------------------------------------------------------ */
  function splitWords(el, cls) {
    const walk = (node) => {
      Array.from(node.childNodes).forEach((ch) => {
        if (ch.nodeType === 3) {
          const frag = document.createDocumentFragment();
          ch.textContent.split(/(\s+)/).forEach((p) => {
            if (!p) return;
            if (/^\s+$/.test(p)) { frag.appendChild(document.createTextNode(p)); return; }
            const w = document.createElement('span');
            w.className = cls || 'w';
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
    return $$('.' + (cls || 'w') + ' > span', el);
  }

  /* ------------------------------------------------------------------ */
  /* Défilement doux                                                     */
  /* ------------------------------------------------------------------ */
  let lenis = null;
  if (HAS_GSAP && window.Lenis && !REDUCED) {
    lenis = new Lenis({ lerp: 0.09, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);
  }
  const navH = () => ($('#nav') ? $('#nav').offsetHeight : 0);
  function jumpTo(target) {
    if (lenis) lenis.scrollTo(target, { immediate: true, offset: target.id === 'top' ? 0 : -navH() + 1, force: true });
    else window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - (target.id === 'top' ? 0 : navH()), behavior: 'auto' });
  }

  /* ------------------------------------------------------------------ */
  /* Transition « mousse » sur les liens d'ancre                         */
  /* ------------------------------------------------------------------ */
  const foam = $('.foam');
  const foamDots = [];
  if (foam) for (let i = 0; i < 20; i++) { const d = document.createElement('i'); foam.appendChild(d); foamDots.push(d); }
  let foaming = false;
  function foamTo(target) {
    if (!HAS_GSAP || REDUCED || foaming) { jumpTo(target); return; }
    foaming = true;
    const W = window.innerWidth, H = window.innerHeight, cols = 5, rows = 4;
    const D = Math.max(W / cols, H / rows) * 2.1;
    foamDots.forEach((d, i) => {
      const cx = ((i % cols) + 0.5) * (W / cols) + rand(-30, 30);
      const cy = (Math.floor(i / cols) + 0.5) * (H / rows) + rand(-30, 30);
      const s = D * rand(0.85, 1.15);
      Object.assign(d.style, { width: s + 'px', height: s + 'px', left: cx - s / 2 + 'px', top: cy - s / 2 + 'px' });
    });
    gsap.timeline({ onComplete: () => { foaming = false; } })
      .set(foam, { pointerEvents: 'auto' })
      .fromTo(foamDots, { scale: 0 }, { scale: 1, duration: 0.55, ease: 'power3.in', stagger: { each: 0.025, from: 'random' } })
      .add(() => { jumpTo(target); ScrollTrigger.update(); })
      .to(foamDots, { scale: 0, duration: 0.6, ease: 'power3.out', stagger: { each: 0.025, from: 'random' } }, '+=0.08')
      .set(foam, { pointerEvents: 'none' });
  }
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if (!a) return;
    const id = a.getAttribute('href').slice(1);
    const target = id ? document.getElementById(id) : null;
    if (!target) return;
    e.preventDefault();
    closeMenu();
    foamTo(target);
  });

  /* ------------------------------------------------------------------ */
  /* Navigation                                                          */
  /* ------------------------------------------------------------------ */
  const nav = $('#nav');
  const burger = $('.burger');
  function closeMenu() {
    root.classList.remove('menu-open');
    if (burger) { burger.setAttribute('aria-expanded', 'false'); burger.setAttribute('aria-label', 'Ouvrir le menu'); }
  }
  if (burger) burger.addEventListener('click', () => {
    const open = !root.classList.contains('menu-open');
    root.classList.toggle('menu-open', open);
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    if (open && HAS_GSAP && !REDUCED) gsap.fromTo('.mobile-menu li', { y: 40, opacity: 0 }, { y: 0, opacity: 1, stagger: 0.05, duration: 0.7, ease: 'power3.out', delay: 0.15 });
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });

  $$('.lang button').forEach((b) => b.addEventListener('click', () => {
    $$('.lang button').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
  }));

  const progressBar = $('.nav__progress span');
  let lastY = 0;
  function onScrollNav(y, max) {
    if (!nav) return;
    nav.classList.toggle('is-scrolled', y > 24);
    const hide = y > 700 && y > lastY + 2 && !root.classList.contains('menu-open');
    const show = y < lastY - 2 || y < 700;
    if (hide) nav.classList.add('is-hidden'); else if (show) nav.classList.remove('is-hidden');
    lastY = y;
    if (progressBar) progressBar.style.transform = 'scaleX(' + (max > 0 ? y / max : 0) + ')';
  }
  if (lenis) lenis.on('scroll', (l) => onScrollNav(l.scroll, l.limit));
  else window.addEventListener('scroll', () => onScrollNav(window.scrollY, document.documentElement.scrollHeight - window.innerHeight), { passive: true });

  const navLinks = $$('.nav__links a');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        navLinks.forEach((a) => a.classList.toggle('is-current', a.getAttribute('href') === '#' + en.target.id));
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    ['parcours', 'solide-liquide', 'impact', 'hotels', 'partenaires', 'actualites'].forEach((id) => { const s = document.getElementById(id); if (s) io.observe(s); });
  }

  /* ------------------------------------------------------------------ */
  /* Curseur bulle et boutons magnétiques                                */
  /* ------------------------------------------------------------------ */
  if (FINE && !REDUCED && HAS_GSAP) {
    root.classList.add('has-cursor');
    const cur = $('.cursor'), label = $('.cursor__label');
    const xTo = gsap.quickTo(cur, 'x', { duration: 0.45, ease: 'power3' });
    const yTo = gsap.quickTo(cur, 'y', { duration: 0.45, ease: 'power3' });
    window.addEventListener('pointermove', (e) => { xTo(e.clientX); yTo(e.clientY); }, { passive: true });
    document.addEventListener('pointerover', (e) => {
      const t = e.target.closest('[data-cursor]');
      if (t) { label.textContent = t.dataset.cursor; cur.classList.add('is-active'); }
    });
    document.addEventListener('pointerout', (e) => {
      const t = e.target.closest('[data-cursor]');
      if (t && !t.contains(e.relatedTarget)) cur.classList.remove('is-active');
    });

    $$('.magnetic').forEach((el) => {
      el.addEventListener('pointermove', (e) => {
        const r = el.getBoundingClientRect();
        gsap.to(el, { x: (e.clientX - r.left - r.width / 2) * 0.22, y: (e.clientY - r.top - r.height / 2) * 0.32, duration: 0.5, ease: 'power3.out' });
      });
      el.addEventListener('pointerleave', () => gsap.to(el, { x: 0, y: 0, duration: 0.9, ease: 'elastic.out(1, .4)' }));
    });
  }

  /* ------------------------------------------------------------------ */
  /* Savon 3D                                                            */
  /* ------------------------------------------------------------------ */
  const hero = $('.hero');
  const stage = $('#soapStage');
  const soap = $('#soap');
  const soapFloat = $('#soapFloat');
  let foamed = false;
  function lather() {
    if (!stage) return;
    const hr = hero.getBoundingClientRect(), sr = stage.getBoundingClientRect();
    if (heroField) heroField.burst(sr.left - hr.left + sr.width / 2, sr.top - hr.top + sr.height * 0.45, 26);
    if (HAS_GSAP && !REDUCED) {
      gsap.timeline()
        .to(soapFloat, { scaleX: 1.07, scaleY: 0.92, duration: 0.12, ease: 'power2.out' })
        .to(soapFloat, { scaleX: 1, scaleY: 1, duration: 0.9, ease: 'elastic.out(1.1, .35)' });
    }
    if (!foamed) { foamed = true; const h = $('.soap-hint'); if (h) h.textContent = 'Encore ? Cliquez à nouveau'; }
  }
  if (stage) {
    stage.addEventListener('click', lather);
    stage.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); lather(); } });
  }
  if (HAS_GSAP && !REDUCED && soap) {
    gsap.to(soapFloat, { y: -18, duration: 3.2, ease: 'sine.inOut', yoyo: true, repeat: -1 });
    gsap.to('.soap-shadow', { scaleX: 0.86, opacity: 0.7, duration: 3.2, ease: 'sine.inOut', yoyo: true, repeat: -1 });
    $$('[data-float]').forEach((b, i) => {
      gsap.to(b, { y: rand(-26, -12), x: rand(-8, 8), duration: rand(2.6, 4.2), ease: 'sine.inOut', yoyo: true, repeat: -1, delay: i * 0.3 });
    });
    if (FINE) {
      hero.addEventListener('pointermove', (e) => {
        const r = hero.getBoundingClientRect();
        const nx = (e.clientX - r.left) / r.width - 0.5, ny = (e.clientY - r.top) / r.height - 0.5;
        gsap.to(soap, { '--rx': 16 - ny * 22 + 'deg', '--ry': -16 + nx * 34 + 'deg', '--mx': 32 + nx * 50 + '%', '--my': 24 + ny * 50 + '%', duration: 0.9, ease: 'power3.out' });
      });
      hero.addEventListener('pointerleave', () => gsap.to(soap, { '--rx': '16deg', '--ry': '-16deg', '--mx': '32%', '--my': '24%', duration: 1.4, ease: 'elastic.out(1,.5)' }));
    }
    gsap.to('.hero__visual', { yPercent: 14, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });
  }

  /* ------------------------------------------------------------------ */
  /* Préchargement puis entrée du hero                                   */
  /* ------------------------------------------------------------------ */
  const loader = $('.loader');
  const heroTitle = $('[data-hero-title]');
  function heroIntro() {
    if (!HAS_GSAP || REDUCED) return;
    const words = splitWords(heroTitle);
    gsap.timeline({ defaults: { ease: 'expo.out' } })
      .from(words, { yPercent: 115, duration: 1.3, stagger: 0.06 })
      .from('[data-hero]', { y: 26, opacity: 0, duration: 1.1, stagger: 0.1 }, 0.25)
      .from('.soap-stage', { scale: 0.7, opacity: 0, rotate: -12, duration: 1.6, ease: 'elastic.out(1, .6)' }, 0.1)
      .from('.badge10', { scale: 0, rotate: -90, duration: 1.2, ease: 'back.out(1.8)' }, 0.7)
      .add(() => { if (heroField) { const sr = stage.getBoundingClientRect(), hr = hero.getBoundingClientRect(); heroField.burst(sr.left - hr.left + sr.width / 2, sr.top - hr.top + sr.height / 2, 16); } }, 0.5);
  }
  if (loader) {
    if (HAS_GSAP && !REDUCED) {
      loader.style.animation = 'none';
      gsap.timeline()
        .to('.loader__bar span', { scaleX: 1, duration: 1.0, ease: 'power2.inOut' })
        .to('.loader__inner', { y: -20, opacity: 0, duration: 0.4, ease: 'power2.in' })
        .to(loader, { clipPath: 'circle(0% at 50% 50%)', duration: 0.9, ease: 'expo.inOut' }, '-=0.1')
        .set(loader, { display: 'none' })
        .add(heroIntro, '-=0.75');
    } else {
      loader.style.display = 'none';
    }
  }

  /* ------------------------------------------------------------------ */
  /* Animations au défilement                                            */
  /* ------------------------------------------------------------------ */
  if (HAS_GSAP && !REDUCED) {
    $$('.split').forEach((h) => {
      if (h === heroTitle) return;
      const words = splitWords(h);
      gsap.from(words, { yPercent: 115, duration: 1.2, ease: 'expo.out', stagger: 0.045, scrollTrigger: { trigger: h, start: 'top 86%' } });
    });

    const reveal = $$('.section-head .lead, .section-head .link-arrow, .card, .pillar, .post, .stat, .chips li, .lives__fact, .pull, .calc, .compare__panel, .compare > div:first-child, .film, .big-claim, .contact__info, .form, .manifesto__foot')
      .filter((el) => el.getBoundingClientRect().top > window.innerHeight);
    gsap.set(reveal, { y: 48, opacity: 0 });
    ScrollTrigger.batch(reveal, {
      start: 'top 90%',
      onEnter: (b) => gsap.to(b, { y: 0, opacity: 1, duration: 1.1, ease: 'expo.out', stagger: 0.08, overwrite: true })
    });

    const tls = $$('.tl');
    gsap.from(tls, { x: 80, opacity: 0, duration: 1.1, ease: 'expo.out', stagger: 0.07, scrollTrigger: { trigger: '#timeline', start: 'top 85%' } });

    $$('[data-parallax]').forEach((el) => {
      gsap.to(el, { y: Number(el.dataset.parallax) * 3, ease: 'none', scrollTrigger: { trigger: el.closest('section'), start: 'top bottom', end: 'bottom top', scrub: true } });
    });

    // Manifeste : les mots s'allument au fil du défilement
    const man = $('#manifesto');
    if (man) {
      const mw = splitWords(man, 'mw');
      gsap.fromTo(mw, { opacity: 0.14 }, { opacity: 1, ease: 'none', stagger: 0.05, scrollTrigger: { trigger: man, start: 'top 78%', end: 'bottom 42%', scrub: 0.6 } });
    }

    // Photo humanitaire
    const lp = $('.lives__photo');
    if (lp) {
      gsap.from(lp, { clipPath: 'inset(14% 12% 14% 12% round 36px)', duration: 1.6, ease: 'expo.out', scrollTrigger: { trigger: lp, start: 'top 80%' } });
      gsap.fromTo('[data-parallax-img]', { yPercent: -12 }, { yPercent: 0, ease: 'none', scrollTrigger: { trigger: lp, start: 'top bottom', end: 'bottom top', scrub: true } });
    }

    // Piliers : léger décalage des images
    $$('.pillar__img img').forEach((img) => gsap.fromTo(img, { yPercent: -6 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: img.closest('.pillar'), start: 'top bottom', end: 'bottom top', scrub: true } }));

    // Compteurs
    $$('[data-count]').forEach((el) => {
      const end = Number(el.dataset.count);
      ScrollTrigger.create({
        trigger: el, start: 'top 88%', once: true,
        onEnter: () => { const o = { v: 0 }; gsap.to(o, { v: end, duration: 2.4, ease: 'power3.out', onUpdate: () => { el.textContent = fmt(o.v); } }); }
      });
    });
    const bc = $('.big-claim strong');
    if (bc) ScrollTrigger.create({ trigger: bc, start: 'top 85%', once: true, onEnter: () => { const o = { v: 0 }; gsap.to(o, { v: 153318, duration: 2.6, ease: 'power3.out', onUpdate: () => { bc.textContent = fmt(o.v); } }); } });

    // Badge 10 ans : accélère avec le défilement
    const bsvg = $('.badge10 svg');
    if (bsvg) gsap.to(bsvg, { rotate: 360, ease: 'none', scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } });

    gsap.to('.road-dashes', { x: -80, duration: 0.7, ease: 'none', repeat: -1 });
    gsap.to('.van', { y: -3, duration: 0.25, ease: 'sine.inOut', yoyo: true, repeat: -1 });
    gsap.to('.worn-soap', { rotate: 3, transformOrigin: '50% 80%', duration: 2.2, ease: 'sine.inOut', yoyo: true, repeat: -1 });
    gsap.to('.mini-bubbles circle', { y: -14, opacity: 0.4, duration: 2, ease: 'sine.inOut', yoyo: true, repeat: -1, stagger: 0.3 });
  }

  /* ------------------------------------------------------------------ */
  /* Parcours horizontal épinglé                                         */
  /* ------------------------------------------------------------------ */
  const track = $('#journeyTrack');
  const pin = $('#journeyPin');
  const binFillA = $('.bin-fill');
  if (HAS_GSAP && !REDUCED && track) {
    const mm = gsap.matchMedia();
    mm.add('(min-width: 1024px)', () => {
      pin.style.paddingTop = navH() + 18 + 'px';
      track.style.paddingBottom = '56px';
      const dist = () => track.scrollWidth - window.innerWidth;
      const fill = $('#journeyFill'), dot = $('#journeyDot');
      const tween = gsap.to(track, {
        x: () => -dist(), ease: 'none',
        scrollTrigger: {
          trigger: pin, start: 'top top', end: () => '+=' + dist(), pin: true, scrub: 1, invalidateOnRefresh: true, anticipatePin: 1,
          onUpdate: (s) => { fill.style.transform = 'scaleX(' + s.progress + ')'; dot.style.left = s.progress * 100 + '%'; }
        }
      });
      const steps = $$('.step', track);
      steps.forEach((st) => {
        const art = $('.step__art img', st);
        if (art) gsap.fromTo(art, { scale: 1.25 }, { scale: 1, ease: 'none', scrollTrigger: { trigger: st, containerAnimation: tween, start: 'left right', end: 'right right', scrub: true } });
        gsap.from($$('.step__copy > *', st), { y: 40, opacity: 0, stagger: 0.08, duration: 1, ease: 'expo.out', scrollTrigger: { trigger: st, containerAnimation: tween, start: 'left 75%' } });
      });
      if (binFillA) gsap.fromTo(binFillA, { attr: { y: 320 } }, { attr: { y: 150 }, ease: 'none', scrollTrigger: { trigger: steps[1], containerAnimation: tween, start: 'left 80%', end: 'center 45%', scrub: true } });
      gsap.fromTo('.van', { x: -260 }, { x: 0, ease: 'power2.out', scrollTrigger: { trigger: steps[2], containerAnimation: tween, start: 'left 85%', end: 'center 50%', scrub: true } });
      return () => { pin.style.paddingTop = ''; track.style.paddingBottom = ''; };
    });
    mm.add('(max-width: 1023px)', () => {
      if (binFillA) gsap.fromTo(binFillA, { attr: { y: 320 } }, { attr: { y: 150 }, ease: 'none', scrollTrigger: { trigger: binFillA.closest('.step'), start: 'top 75%', end: 'center 40%', scrub: true } });
      $$('.step', track).forEach((st) => gsap.from(st, { y: 60, opacity: 0, duration: 1.1, ease: 'expo.out', scrollTrigger: { trigger: st, start: 'top 88%' } }));
    });
  } else if (binFillA) {
    binFillA.setAttribute('y', '150');
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
      tl.addLabel('c' + n, s);
    });

    // 1 · Introduction
    tl.fromTo('.s1-bar', { x: -90, opacity: 0 }, { x: 0, opacity: 1, duration: 1.2, ease: 'power3.out' }, 0.3);
    tl.fromTo('.s1-bottle', { x: 90, opacity: 0 }, { x: 0, opacity: 1, duration: 1.2, ease: 'power3.out' }, 0.5);
    tl.fromTo('.s1-or', { scale: 0, svgOrigin: '840 650' }, { scale: 1, svgOrigin: '840 650', duration: 0.8, ease: 'back.out(2)' }, 1.2);
    // 2 · Composition
    T('.s2-water', { y: 260 }, { y: 0, duration: 3.2, ease: 'power2.out' }, 6.6);
    T('.s2-wave', { x: 0 }, { x: -160, duration: 1.3, ease: 'none', repeat: 5 }, 6.4);
    T('.s2-label', { opacity: 0 }, { opacity: 1, duration: 0.8 }, 9.4);
    // 3 · Transport
    T('.s3-solid', { x: -260 }, { x: 1750, duration: 5.6, ease: 'none' }, 14.4);
    T('.s3-liquid', { x: -260 }, { x: 2350, duration: 7.4, ease: 'none' }, 14.4);
    // 4 · Emballage
    T('.s4-bar', { opacity: 0, y: 40 }, { opacity: 1, y: 0, duration: 1, ease: 'power3.out' }, 22.5);
    T('.s4-bottles use', { y: -720, rotation: () => rand(-30, 30), transformOrigin: '50% 50%' }, { y: 0, rotation: () => rand(-7, 7), transformOrigin: '50% 50%', duration: 0.9, ease: 'bounce.out', stagger: 0.24 }, 23);
    // 5 · Usage
    T('.s5-flake', { scale: 0, transformOrigin: '50% 50%' }, { scale: 1, transformOrigin: '50% 50%', duration: 0.8, ease: 'back.out(2)' }, 30.8);
    T('.s5-drops use', { y: -140, opacity: 0 }, { y: 0, opacity: 1, duration: 0.6, ease: 'power2.in', stagger: 0.28 }, 31.4);
    // 6 · Bilan carbone
    T('.s6-a', { attr: { width: 0 } }, { attr: { width: 600 }, duration: 1.4, ease: 'power3.out' }, 38.6);
    T('.s6-ta', { opacity: 0 }, { opacity: 1, duration: 0.6 }, 39.6);
    T('.s6-b', { attr: { width: 0 } }, { attr: { width: 750 }, duration: 1.8, ease: 'power3.out' }, 39.3);
    T('.s6-tb', { opacity: 0 }, { opacity: 1, duration: 0.6 }, 40.8);
    // 7 · Seconde vie
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
        onEnter: () => { if (!userPaused && !film.classList.contains('is-started')) { tl.time(0); start(); } else if (!userPaused && tl.time() < FILM_END - 0.05) start(); },
        onEnterBack: () => { if (!userPaused && film.classList.contains('is-started') && tl.time() < FILM_END - 0.05) start(); },
        onLeave: () => { if (playing) pause(false); },
        onLeaveBack: () => { if (playing) pause(false); }
      });
    }
  }

  /* ------------------------------------------------------------------ */
  /* Comparateur solide / liquide                                        */
  /* ------------------------------------------------------------------ */
  const washes = $('#washes');
  function grams(g) { return g < 1000 ? fmt(g) + ' g' : (g / 1000).toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' kg'; }
  function updateCompare() {
    const n = Number(washes.value);
    const max = Number(washes.max) * 2.3;
    $('#washesOut').textContent = fmt(n);
    $('#solidOut').textContent = grams(n * 0.35);
    $('#liquidOut').textContent = grams(n * 2.3);
    $('#solidBar').style.width = Math.max(1.5, (n * 0.35 / max) * 100) + '%';
    $('#liquidBar').style.width = Math.max(1.5, (n * 2.3 / max) * 100) + '%';
    washes.style.setProperty('--p', ((n - washes.min) / (washes.max - washes.min)) * 100 + '%');
  }
  if (washes) { washes.addEventListener('input', updateCompare); updateCompare(); }

  /* ------------------------------------------------------------------ */
  /* Rejoindre : le bac se remplit                                       */
  /* ------------------------------------------------------------------ */
  const binFill = $('#binFillB'), binPct = $('#binPct');
  const levelY = (pct) => 346 - (pct / 100) * 242;
  let binState = { p: 0 };
  function setBin(pct, animate) {
    if (!binFill) return;
    if (HAS_GSAP && animate && !REDUCED) {
      gsap.to(binState, { p: pct, duration: 1.2, ease: 'power3.out', onUpdate: () => { binFill.setAttribute('y', levelY(binState.p)); binPct.textContent = Math.round(binState.p) + ' %'; } });
      if (pct >= 75) gsap.fromTo('#binLid', { y: 0 }, { y: -16, duration: 0.35, yoyo: true, repeat: 1, ease: 'power2.out', delay: 0.9 });
    } else {
      binState.p = pct;
      binFill.setAttribute('y', levelY(pct));
      binPct.textContent = pct + ' %';
    }
  }
  const joinSteps = $$('.join__step');
  if (HAS_GSAP && !REDUCED && joinSteps.length) {
    joinSteps.forEach((st) => {
      ScrollTrigger.create({
        trigger: st, start: 'top 62%', end: 'bottom 62%',
        onToggle: (s) => { if (s.isActive) { joinSteps.forEach((x) => x.classList.toggle('is-active', x === st)); setBin(Number(st.dataset.fill), true); } }
      });
    });
  } else {
    setBin(75, false);
  }

  /* ------------------------------------------------------------------ */
  /* Simulateur d'impact                                                 */
  /* ------------------------------------------------------------------ */
  const rooms = $('#rooms'), occ = $('#occ');
  const prev = { kg: 0, s: 0, co2: 0 };
  function updateCalc() {
    const r = Number(rooms.value), o = Number(occ.value) / 100;
    const kg = r * o * 365 * 0.007;
    const soaps = Math.round(kg / 0.152 / 10) * 10;
    const co2 = kg * 1.57;
    $('#roomsOut').textContent = fmt(r);
    $('#occOut').textContent = Math.round(o * 100) + ' %';
    [rooms, occ].forEach((x) => x.style.setProperty('--p', ((x.value - x.min) / (x.max - x.min)) * 100 + '%'));
    const out = (el, from, to, suffix) => {
      if (HAS_GSAP && !REDUCED) { const o2 = { v: from }; gsap.to(o2, { v: to, duration: 0.6, ease: 'power2.out', overwrite: true, onUpdate: () => { el.textContent = fmt(o2.v) + suffix; } }); }
      else el.textContent = fmt(to) + suffix;
    };
    out($('#resKg'), prev.kg, kg, ' kg');
    out($('#resSoaps'), prev.s, soaps, '');
    out($('#resCo2'), prev.co2, co2, ' kg');
    prev.kg = kg; prev.s = soaps; prev.co2 = co2;
  }
  if (rooms && occ) {
    rooms.addEventListener('input', updateCalc);
    occ.addEventListener('input', updateCalc);
    prev.kg = 221; prev.s = 1450; prev.co2 = 347;
    updateCalc();
  }

  /* ------------------------------------------------------------------ */
  /* Témoignages                                                         */
  /* ------------------------------------------------------------------ */
  const quotes = $$('.quote');
  let qi = 0, qTimer = null, qPaused = REDUCED;
  const qPauseBtn = $('#qPause');
  function showQuote(n) {
    const prevQ = quotes[qi];
    qi = (n + quotes.length) % quotes.length;
    const next = quotes[qi];
    if (prevQ === next) return;
    if (HAS_GSAP && !REDUCED) {
      gsap.to(prevQ, { autoAlpha: 0, y: -20, duration: 0.45, ease: 'power2.in', onComplete: () => prevQ.classList.remove('is-active') });
      next.classList.add('is-active');
      gsap.fromTo(next, { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: 0.9, ease: 'expo.out', delay: 0.3 });
    } else {
      prevQ.classList.remove('is-active');
      next.classList.add('is-active');
    }
    $('#qCount').textContent = qi + 1 + ' / ' + quotes.length;
  }
  function qLoop() { clearInterval(qTimer); if (!qPaused) qTimer = setInterval(() => showQuote(qi + 1), 7000); }
  if (quotes.length) {
    $('#qPrev').addEventListener('click', () => { showQuote(qi - 1); qLoop(); });
    $('#qNext').addEventListener('click', () => { showQuote(qi + 1); qLoop(); });
    qPauseBtn.addEventListener('click', () => {
      qPaused = !qPaused;
      $('use', qPauseBtn).setAttribute('href', qPaused ? '#i-play' : '#i-pause');
      qPauseBtn.setAttribute('aria-label', qPaused ? 'Reprendre le défilement' : 'Mettre en pause le défilement');
      qLoop();
    });
    if (REDUCED) { $('use', qPauseBtn).setAttribute('href', '#i-play'); qPauseBtn.setAttribute('aria-label', 'Reprendre le défilement'); }
    const qs = $('#quotes');
    qs.addEventListener('pointerenter', () => clearInterval(qTimer));
    qs.addEventListener('pointerleave', qLoop);
    qs.addEventListener('focusin', () => clearInterval(qTimer));
    qLoop();
  }

  /* ------------------------------------------------------------------ */
  /* Frise : glisser à la souris                                         */
  /* ------------------------------------------------------------------ */
  const tline = $('#timeline');
  if (tline) {
    let down = false, sx = 0, sl = 0, moved = false;
    tline.addEventListener('pointerdown', (e) => { if (e.pointerType !== 'mouse') return; down = true; moved = false; sx = e.clientX; sl = tline.scrollLeft; tline.classList.add('is-drag'); });
    window.addEventListener('pointermove', (e) => { if (!down) return; const dx = e.clientX - sx; if (Math.abs(dx) > 3) moved = true; tline.scrollLeft = sl - dx; });
    window.addEventListener('pointerup', () => { if (!down) return; down = false; tline.classList.remove('is-drag'); });
    tline.addEventListener('click', (e) => { if (moved) e.preventDefault(); }, true);
    if (lenis) tline.setAttribute('data-lenis-prevent-wheel', '');
  }

  /* ------------------------------------------------------------------ */
  /* Onglets partenaires                                                 */
  /* ------------------------------------------------------------------ */
  const tabs = $$('[role="tab"]');
  function selectTab(tab) {
    tabs.forEach((t) => {
      const on = t === tab;
      t.setAttribute('aria-selected', String(on));
      t.tabIndex = on ? 0 : -1;
      const p = document.getElementById(t.getAttribute('aria-controls'));
      p.hidden = !on;
      if (on && HAS_GSAP && !REDUCED) gsap.from($$('.chips li', p), { y: 20, opacity: 0, stagger: 0.03, duration: 0.6, ease: 'expo.out' });
    });
  }
  tabs.forEach((t, i) => {
    t.addEventListener('click', () => selectTab(t));
    t.addEventListener('keydown', (e) => {
      const k = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
      if (!k) return;
      const n = tabs[(i + k + tabs.length) % tabs.length];
      n.focus();
      selectTab(n);
    });
  });

  /* ------------------------------------------------------------------ */
  /* Formulaires                                                         */
  /* ------------------------------------------------------------------ */
  const form = $('#joinForm');
  if (form) {
    const validate = (input) => {
      const f = input.closest('.field');
      if (!f) return true;
      const ok = input.value.trim() !== '' && (input.type !== 'email' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim()));
      f.classList.toggle('is-invalid', !ok);
      input.setAttribute('aria-invalid', String(!ok));
      return ok;
    };
    $$('[required]', form).forEach((i) => i.addEventListener('blur', () => { if (i.value) validate(i); }));
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const req = $$('[required]', form);
      const bad = req.filter((i) => !validate(i));
      if (bad.length) { bad[0].focus(); return; }
      form.classList.add('is-done');
      if (HAS_GSAP && !REDUCED) {
        gsap.from('.form__done', { y: 20, opacity: 0, duration: 0.8, ease: 'expo.out' });
        gsap.fromTo($('button[type="submit"]', form), { scale: 0.94 }, { scale: 1, duration: 0.8, ease: 'elastic.out(1,.4)' });
      }
    });
  }
  const nf = $('#newsForm');
  if (nf) nf.addEventListener('submit', (e) => { e.preventDefault(); const b = $('button', nf); b.textContent = 'Merci !'; });

  const copyBtn = $('#copyMail');
  if (copyBtn) copyBtn.addEventListener('click', () => {
    const txt = $('#mail').textContent;
    const done = () => { copyBtn.textContent = 'Copié'; setTimeout(() => { copyBtn.textContent = 'Copier'; }, 1800); };
    const fallback = () => { const r = document.createRange(); r.selectNodeContents($('#mail')); const s = window.getSelection(); s.removeAllRanges(); s.addRange(r); copyBtn.textContent = 'Sélectionné'; };
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done, fallback);
    else fallback();
  });

  /* ------------------------------------------------------------------ */
  if (HAS_GSAP) {
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => ScrollTrigger.refresh());
    window.addEventListener('load', () => ScrollTrigger.refresh());
  }
})();

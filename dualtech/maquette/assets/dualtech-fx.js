/* DualTech 2026 : effets dynamiques (GSAP 3.12.5 + ScrollTrigger, hébergés avec le site)
   Tout est désactivé si le visiteur a demandé moins d'animations : le contenu reste dans son état final. */
(() => {
  const calme = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fin = matchMedia('(hover: hover) and (pointer: fine)').matches;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const G = window.gsap;
  if (G && window.ScrollTrigger) G.registerPlugin(ScrollTrigger);

  /* ---------- Apparitions : tracé des icônes, volets d'images, anneaux ---------- */
  const io = new IntersectionObserver((es) => es.forEach((e) => {
    if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
  }), { threshold: .2 });
  $$('.fx-trace, .anneau').forEach((el) => io.observe(el));
  /* un bloc masqué par son propre clip-path n'est jamais « visible » : on observe son parent */
  const ioVolet = new IntersectionObserver((es) => es.forEach((e) => {
    if (!e.isIntersecting) return; ioVolet.unobserve(e.target);
    $$(':scope > [data-volet]', e.target).forEach((v) => v.classList.add('is-in'));
  }), { threshold: .15 });
  $$('[data-volet]').forEach((el) => ioVolet.observe(el.parentElement));

  /* ---------- Surtitres déchiffrés (effet de décodage) ---------- */
  const signes = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/·';
  const dechiffrer = (el) => {
    const cible = el.dataset.texte, n = cible.length; let t = 0;
    const pas = () => {
      t += 1;
      el.textContent = [...cible].map((c, i) => (c === ' ' || i < t / 2 ? c : signes[(Math.random() * signes.length) | 0])).join('');
      if (t / 2 < n) requestAnimationFrame(pas); else el.textContent = cible;
    };
    pas();
  };
  if (!calme) {
    const ioS = new IntersectionObserver((es) => es.forEach((e) => {
      if (!e.isIntersecting) return; ioS.unobserve(e.target);
      const span = $('.code', e.target); if (span) setTimeout(() => dechiffrer(span), 150);
    }), { threshold: .6 });
    $$('.surtitre').forEach((s) => {
      if (s.querySelector('*')) return;
      const t = s.textContent.trim();
      s.setAttribute('aria-label', t);
      s.innerHTML = `<span class="code" aria-hidden="true" data-texte="${t.replace(/"/g, '&quot;')}">${t}</span>`;
      ioS.observe(s);
    });
  }


  /* ---------- Fiches partenaires (logo cliqué : présentation) ---------- */
  const fiche = $('#fiche');
  if (fiche) {
    let liste = [], pos = 0;
    const remplir = (b) => {
      $('.fiche__logo img', fiche).src = b.querySelector('img').src;
      $('.fiche__logo img', fiche).alt = 'Logo ' + b.dataset.nom;
      $('.fiche__cat', fiche).textContent = b.dataset.cat;
      $('#fiche-nom', fiche).textContent = b.dataset.nom;
      $('.fiche__desc', fiche).textContent = b.dataset.desc || 'Partenaire de DualTech 2026.';
    };
    document.addEventListener('click', (e) => {
      const b = e.target.closest('[data-fiche]'); if (!b) return;
      const portee = b.closest('.vue') || document;
      const vus = new Set();
      liste = $$('[data-fiche]', portee).filter((x) => !vus.has(x.dataset.nom) && vus.add(x.dataset.nom));
      pos = liste.findIndex((x) => x.dataset.nom === b.dataset.nom);
      remplir(b); fiche.showModal();
    });
    $$('[data-fiche-pas]', fiche).forEach((n) => n.addEventListener('click', () => { pos = (pos + +n.dataset.fichePas + liste.length) % liste.length; remplir(liste[pos]); }));
    $('.fiche__fermer', fiche).addEventListener('click', () => fiche.close());
    fiche.addEventListener('click', (e) => { if (e.target === fiche) fiche.close(); });
  }

  /* ---------- Page Intervenants : univers, recherche, vue, fiche ---------- */
  const grille = $('#iv-grille');
  if (grille) {
    const cartes = $$('.iv-carte', grille), champ = $('#iv-recherche');
    let univers = 'tous';
    const norm = (t) => t.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const appliquer = () => {
      const q = norm(champ.value.trim()); let n = 0;
      cartes.forEach((c) => { const ok = (univers === 'tous' || c.dataset.univers === univers) && (!q || c.dataset.cherche.includes(q)); c.hidden = !ok; n += ok; });
      $('#iv-vide').hidden = n > 0;
      $('#iv-statut').textContent = `${n} intervenant${n > 1 ? 's' : ''}`;
      if (window.gsap && !calme) gsap.fromTo(cartes.filter((c) => !c.hidden), { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: .5, ease: 'expo.out', stagger: .025 });
    };
    $$('.iv-barre .filtres button').forEach((b) => b.addEventListener('click', () => {
      univers = b.dataset.univers;
      $$('.iv-barre .filtres button').forEach((x) => x.setAttribute('aria-pressed', x === b));
      appliquer();
    }));
    champ.addEventListener('input', appliquer);
    $('#iv-effacer').addEventListener('click', () => { champ.value = ''; appliquer(); champ.focus(); });
    $$('.vues button').forEach((b) => b.addEventListener('click', () => {
      $$('.vues button').forEach((x) => x.setAttribute('aria-pressed', x === b));
      grille.classList.toggle('iv-grille--liste', b.dataset.vue === 'liste');
      grille.parentElement.classList.toggle('iv-liste-active', b.dataset.vue === 'liste');
      if (window.gsap && !calme) gsap.fromTo(cartes.filter((c) => !c.hidden), { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: .45, ease: 'expo.out', stagger: .02 });
    }));
    const fi = $('#fiche-iv'); let pos = 0;
    const visibles = () => cartes.filter((c) => !c.hidden);
    const ouvrir = (c) => {
      const d = c.dataset;
      $('.fiche__photo img', fi).src = d.photo; $('.fiche__photo img', fi).alt = d.nom;
      $('.fiche__cat', fi).textContent = c.querySelector('.iv-carte__univers').textContent;
      $('#fiche-iv-nom', fi).textContent = d.nom;
      $('.fiche__fonction', fi).textContent = `${d.fonction}, ${d.orga}`;
      const s = JSON.parse(d.sessions);
      $('.fiche__sessions', fi).innerHTML = s.length ? s.map((x) => `<li><b>${x.h}</b><span>${x.t}<small>${x.r === 'modération' ? 'Modération' : 'Intervention'}</small></span></li>`).join('') : '<li><b>2026</b><span>Horaire de passage communiqué prochainement.</span></li>';
    };
    cartes.forEach((c) => c.addEventListener('click', () => { pos = visibles().indexOf(c); ouvrir(c); fi.showModal(); }));
    fi.addEventListener('close', () => { const c = visibles()[pos]; if (c) c.querySelector('.iv-carte__ouvrir').focus(); });
    $$('[data-iv-pas]', fi).forEach((n) => n.addEventListener('click', () => { const v = visibles(); pos = (pos + +n.dataset.ivPas + v.length) % v.length; ouvrir(v[pos]); }));
    $('.fiche__fermer', fi).addEventListener('click', () => fi.close());
    fi.addEventListener('click', (e) => { if (e.target === fi) fi.close(); });
  }

  /* ---------- Vidéos MP4 et YouTube chargées à l'approche (jamais en lecture auto si moins d'animations) ---------- */
  const ioV = new IntersectionObserver((es) => es.forEach((e) => {
    const v = e.target;
    if (e.isIntersecting) {
      if (!v.src && v.dataset.src) { v.src = v.dataset.src; }
      if (!calme) v.play().catch(() => {});
    } else if (!v.paused) v.pause();
  }), { rootMargin: '200px 0px' });
  $$('video[data-src]').forEach((v) => ioV.observe(v));
  const ioY = new IntersectionObserver((es) => es.forEach((e) => {
    if (!e.isIntersecting || calme) return; ioY.unobserve(e.target);
    const cible = e.target.querySelector(':scope > [data-yt]') || e.target;
    const id = cible.dataset.yt, f = document.createElement('iframe');
    f.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&mute=1&controls=0&loop=1&playlist=${id}&start=10&playsinline=1&rel=0&modestbranding=1`;
    f.title = 'Vidéo du Campus Cyber'; f.setAttribute('aria-hidden', 'true'); f.setAttribute('tabindex', '-1'); f.allow = 'autoplay; encrypted-media';
    f.addEventListener('load', () => setTimeout(() => f.classList.add('is-pret'), 1200));
    cible.insertBefore(f, cible.querySelector('.lieu__etiquette'));
  }), { threshold: .3 });
  $$('[data-yt]').forEach((el) => ioY.observe(el.parentElement));

  if (calme) { document.documentElement.classList.add('fx-calme'); return; }

  /* ---------- Projecteur qui suit la souris (cartes, secteurs) ---------- */
  document.addEventListener('pointermove', (e) => {
    const c = e.target.closest && e.target.closest('.secteur, .temps li, .projecteur');
    if (!c) return;
    const r = c.getBoundingClientRect();
    c.style.setProperty('--mx', `${e.clientX - r.left}px`);
    c.style.setProperty('--my', `${e.clientY - r.top}px`);
  }, { passive: true });

  /* ---------- Curseur réticule ---------- */
  if (fin) {
    const cur = document.createElement('div');
    cur.className = 'curseur'; cur.setAttribute('aria-hidden', 'true');
    cur.innerHTML = '<span class="curseur__cercle"><span class="curseur__lib">Voir</span></span><span class="curseur__point"></span>';
    document.body.appendChild(cur);
    const cercle = $('.curseur__cercle', cur), point = $('.curseur__point', cur);
    let x = innerWidth / 2, y = innerHeight / 2, cx = x, cy = y;
    addEventListener('pointermove', (e) => { x = e.clientX; y = e.clientY; }, { passive: true });
    const boucle = () => {
      cx += (x - cx) * .18; cy += (y - cy) * .18;
      point.style.transform = `translate(${x}px, ${y}px)`;
      cercle.style.transform = `translate(${cx}px, ${cy}px)`;
      requestAnimationFrame(boucle);
    };
    boucle();
    document.addEventListener('pointerover', (e) => {
      const t = e.target;
      cur.classList.toggle('is-voir', !!t.closest('.orateur, .portrait, [data-film], .lieu__visuel'));
      cur.classList.toggle('is-lien', !cur.classList.contains('is-voir') && !!t.closest('a, button, summary, [role="tab"], .secteur, label.case'));
      cur.classList.toggle('is-cache', !!t.closest('input, textarea, select, iframe'));
    });
    document.addEventListener('pointerleave', () => cur.classList.add('is-cache'));
    document.addEventListener('pointerenter', () => cur.classList.remove('is-cache'));
  }

  /* ---------- Boutons magnétiques ---------- */
  if (fin && G) {
    $$('.btn, .video-ctrl button, .rond, .haut').forEach((b) => {
      const xTo = G.quickTo(b, 'x', { duration: .5, ease: 'power3' }), yTo = G.quickTo(b, 'y', { duration: .5, ease: 'power3' });
      b.addEventListener('pointermove', (e) => {
        const r = b.getBoundingClientRect();
        xTo((e.clientX - r.left - r.width / 2) * .3); yTo((e.clientY - r.top - r.height / 2) * .35);
      });
      b.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
    });
  }

  /* ---------- Inclinaison 3D des cartes ---------- */
  if (fin && G) {
    $$('[data-incline]').forEach((c) => {
      G.set(c, { transformPerspective: 900 });
      const rx = G.quickTo(c, 'rotationX', { duration: .6, ease: 'power3' }), ry = G.quickTo(c, 'rotationY', { duration: .6, ease: 'power3' });
      c.addEventListener('pointermove', (e) => {
        const r = c.getBoundingClientRect();
        ry(((e.clientX - r.left) / r.width - .5) * 10); rx(-((e.clientY - r.top) / r.height - .5) * 10);
      });
      c.addEventListener('pointerleave', () => { rx(0); ry(0); });
    });
  }

  /* ---------- Bouton haut de page avec progression ---------- */
  const haut = document.createElement('button');
  haut.className = 'haut'; haut.type = 'button'; haut.setAttribute('aria-label', 'Revenir en haut de la page');
  haut.innerHTML = '<svg class="anneau-haut" viewBox="0 0 52 52" aria-hidden="true"><circle class="fond" cx="26" cy="26" r="24"/><circle class="val" cx="26" cy="26" r="24" pathLength="1"/></svg><svg class="fleche" viewBox="0 0 256 256" fill="currentColor" aria-hidden="true"><path d="M221.66 133.66l-72 72a8 8 0 0 1-11.32-11.32L196.69 136H40a8 8 0 0 1 0-16h156.69l-58.35-58.34a8 8 0 0 1 11.32-11.32l72 72a8 8 0 0 1 0 11.32Z"/></svg>';
  document.body.appendChild(haut);
  haut.addEventListener('click', () => scrollTo({ top: 0, behavior: 'smooth' }));
  const valHaut = $('.val', haut);
  addEventListener('scroll', () => {
    const h = document.documentElement.scrollHeight - innerHeight, p = h > 0 ? scrollY / h : 0;
    haut.classList.toggle('is-visible', scrollY > innerHeight);
    valHaut.style.strokeDashoffset = 1 - p;
  }, { passive: true });

  /* ---------- Constellation : réseau de points reliés, sensible à la souris ---------- */
  $$('[data-reseau]').forEach((sec) => {
    const cv = document.createElement('canvas'); cv.className = 'reseau'; cv.setAttribute('aria-hidden', 'true');
    sec.prepend(cv); sec.classList.add('avec-reseau');
    const ctx = cv.getContext('2d'); let w, h, pts = [], actif = false, souris = { x: -999, y: -999 };
    const clair = !!sec.closest('.sec--blanc, .sec--papier') || sec.matches('.sec--blanc, .sec--papier');
    const cLigne = clair ? '27,95,193' : '73,167,255', cPoint = clair ? 'rgba(91,103,121,.7)' : 'rgba(157,182,220,.8)';
    const dpr = Math.min(devicePixelRatio || 1, 2);
    const taille = () => {
      w = sec.offsetWidth; h = sec.offsetHeight; if (!w || !h) return;
      cv.width = w * dpr; cv.height = h * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      const n = Math.min(90, Math.round(w * h / 16000));
      pts = Array.from({ length: n }, () => ({ x: Math.random() * w, y: Math.random() * h, vx: (Math.random() - .5) * .25, vy: (Math.random() - .5) * .25, r: Math.random() * 1.4 + .6, rouge: Math.random() < .06 }));
    };
    new ResizeObserver(taille).observe(sec);
    sec.addEventListener('pointermove', (e) => { const r = sec.getBoundingClientRect(); souris = { x: e.clientX - r.left, y: e.clientY - r.top }; });
    sec.addEventListener('pointerleave', () => { souris = { x: -999, y: -999 }; });
    new IntersectionObserver(([e]) => { actif = e.isIntersecting; if (actif) dessine(); }).observe(sec);
    function dessine() {
      if (!actif) return;
      ctx.clearRect(0, 0, w, h);
      for (const p of pts) {
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > w) p.vx *= -1; if (p.y < 0 || p.y > h) p.vy *= -1;
        const dx = p.x - souris.x, dy = p.y - souris.y, d = Math.hypot(dx, dy);
        if (d < 140) { p.x += dx / d * .8; p.y += dy / d * .8; }
      }
      for (let i = 0; i < pts.length; i++) for (let j = i + 1; j < pts.length; j++) {
        const a = pts[i], b = pts[j], d = Math.hypot(a.x - b.x, a.y - b.y);
        if (d < 130) { ctx.strokeStyle = `rgba(${cLigne},${(1 - d / 130) * (clair ? .22 : .28)})`; ctx.lineWidth = .6; ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke(); }
      }
      for (const p of pts) { ctx.fillStyle = p.rouge ? '#DF2229' : cPoint; ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 6.283); ctx.fill(); }
      requestAnimationFrame(dessine);
    }
  });

  if (!G || !window.ScrollTrigger) return;
  /* un élément animé par GSAP quitte l'apparition CSS pour éviter deux animations concurrentes */
  const libre = (els) => { els.forEach((e) => { e.removeAttribute('data-reveal'); e.classList.add('is-in'); }); return els; };

  /* ---------- Intro de l'accueil (une fois par session) ---------- */
  const intro = $('.intro');
  let dejaVu = false;
  try { dejaVu = sessionStorage.getItem('dt-intro') === '1'; sessionStorage.setItem('dt-intro', '1'); } catch (e) { /* stockage indisponible */ }
  const lancerHero = () => {
    const h1 = $('.hero h1'); if (!h1) return;
    G.from($$('.hero .car'), { yPercent: 110, opacity: 0, rotateX: -60, duration: 1, ease: 'expo.out', stagger: .018 });
    G.from(libre($$('.hero__meta, .hero__texte, .hero .actions, .hero__bas')), { y: 24, opacity: 0, duration: 1, ease: 'expo.out', stagger: .1, delay: .35 });
    G.from($$('.hud i'), { scale: 0, opacity: 0, duration: .8, ease: 'expo.out', stagger: .08, delay: .2 });
  };
  /* découpe du H1 du hero en lettres (lecteurs d'écran : texte d'origine conservé) */
  const h1 = $('.hero h1');
  if (h1) {
    h1.setAttribute('aria-label', h1.textContent.replace(/\s+/g, ' ').trim());
    $$('.l > span', h1).forEach((ligne) => {
      const em = ligne.querySelector('em'), cible = em || ligne;
      cible.innerHTML = cible.textContent.split(' ').map((m) => `<span class="mot-split" aria-hidden="true">${[...m].map((c) => `<span class="car">${c}</span>`).join('')}</span>`).join(' ');
    });
    h1.classList.remove('lignes'); h1.classList.add('is-in');
  }
  if (intro && !dejaVu) {
    const tl = G.timeline({ onComplete: () => { intro.remove(); lancerHero(); } });
    const pc = $('.intro__pc', intro), o = { v: 0 };
    tl.to($('.intro__logo', intro), { clipPath: 'inset(0 0% 0 0)', duration: 1.1, ease: 'expo.inOut' })
      .to($('.intro__barre i', intro), { scaleX: 1, duration: 1.3, ease: 'power2.inOut' }, 0)
      .to(o, { v: 100, duration: 1.3, ease: 'power2.inOut', onUpdate: () => { pc.textContent = String(Math.round(o.v)).padStart(3, '0'); } }, 0)
      .to($('.intro__in', intro), { y: -30, opacity: 0, duration: .5, ease: 'power2.in' }, '+=.15')
      .to(intro, { clipPath: 'inset(0 0 100% 0)', duration: .9, ease: 'expo.inOut' }, '-=.15');
  } else {
    if (intro) intro.remove();
    lancerHero();
  }

  /* ---------- Hero : profondeur au défilement ---------- */
  if ($('.hero')) {
    G.to('.hero__media', { scale: 1.18, ease: 'none', scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true } });
    G.to('.hero__contenu', { y: -140, opacity: 0, ease: 'none', scrollTrigger: { trigger: '.hero', start: '25% top', end: 'bottom top', scrub: true } });
    G.to('.hud', { opacity: 0, ease: 'none', scrollTrigger: { trigger: '.hero', start: 'top top', end: '40% top', scrub: true } });
  }

  /* ---------- En-têtes des pages : fond qui suit la souris ---------- */
  $$('.phero').forEach((ph) => {
    const fond = $('.phero__fond', ph); if (!fond || !fin) return;
    G.set(fond, { scale: 1.08 });
    const xTo = G.quickTo(fond, 'x', { duration: 1.2, ease: 'power3' }), yTo = G.quickTo(fond, 'y', { duration: 1.2, ease: 'power3' });
    ph.addEventListener('pointermove', (e) => { xTo((e.clientX / innerWidth - .5) * -30); yTo((e.clientY / innerHeight - .5) * -20); });
  });

  /* ---------- Bandeaux : vitesse et inclinaison liées au défilement ---------- */
  $$('.bandeau').forEach((b, i) => {
    const piste = $('.bandeau__piste', b); b.classList.add('bandeau--js');
    const sens = b.classList.contains('bandeau--inverse') ? 1 : -1;
    let x = 0, vitesse = 0;
    ScrollTrigger.create({ trigger: b, start: 'top bottom', end: 'bottom top', onUpdate: (s) => { vitesse = s.getVelocity() / 120; } });
    G.ticker.add(() => {
      const larg = piste.scrollWidth / 2; if (!larg) return;
      x += sens * (0.6 + Math.abs(vitesse)); vitesse *= .92;
      if (x <= -larg) x += larg; if (x > 0) x -= larg;
      G.set(piste, { x, skewX: Math.max(-12, Math.min(12, -vitesse * 1.5)) });
    });
  });

  /* ---------- Secteurs : apparition en vague ---------- */
  if ($('.secteurs')) {
    G.from('.secteur', { opacity: 0, y: 40, scale: .96, duration: .8, ease: 'expo.out', stagger: { each: .05, grid: 'auto', from: 'start' },
      scrollTrigger: { trigger: '.secteurs', start: 'top 80%' } });
  }

  /* ---------- Section cinéma : la vidéo s'ouvre en plein écran au défilement ---------- */
  $$('.cinema').forEach((c) => {
    const tl = G.timeline({ scrollTrigger: { trigger: c, start: 'top top', end: '+=90%', pin: true, scrub: 1, refreshPriority: 2 } });
    tl.fromTo($('.cinema__cadre', c), { clipPath: 'inset(14% 12% 14% 12% round 6px)' }, { clipPath: 'inset(0% 0% 0% 0% round 0px)', ease: 'none' })
      .from($('.cinema__texte h2', c), { y: 80, opacity: 0, ease: 'none' }, .25)
      .from($('.cinema__texte p', c), { y: 60, opacity: 0, ease: 'none' }, .4)
      .from($('.cinema__badge', c), { opacity: 0, ease: 'none' }, .5);
  });

  /* ---------- Programme de l'accueil : défilement horizontal épinglé ---------- */
  const horizon = $('.horizon-sec');
  const mm = G.matchMedia();
  if (horizon) {
    mm.add('(min-width: 1024px)', () => {
      const piste = $('.horizon .temps', horizon), cartes = $$('li', piste);
      const horloge = $('.horizon__horloge', horizon), progres = $('.horizon__progres', horizon);
      const enveloppe = $('.horizon .wrap', horizon);
      const distance = () => {
        const droite = enveloppe.offsetLeft + enveloppe.clientWidth - parseFloat(getComputedStyle(enveloppe).paddingRight);
        return Math.max(0, piste.offsetLeft + piste.scrollWidth - droite);
      };
      const tw = G.to(piste, { x: () => -distance(), ease: 'none',
        scrollTrigger: { trigger: horizon, start: 'top top', end: () => '+=' + distance(), pin: true, scrub: 1, invalidateOnRefresh: true, refreshPriority: 1,
          onUpdate: (s) => {
            G.set(progres, { scaleX: s.progress });
            const i = Math.min(cartes.length - 1, Math.round(s.progress * (cartes.length - 1)));
            horloge.textContent = cartes[i].dataset.heure;
            cartes.forEach((c, k) => c.classList.toggle('is-actif', k === i));
          } } });
      return () => tw.scrollTrigger && tw.scrollTrigger.kill();
    });
  }

  /* ---------- Chiffres : léger zoom à l'entrée ---------- */
  $$('.chiffres').forEach((l) => G.from(libre($$('li', l)), { y: 50, opacity: 0, duration: 1, ease: 'expo.out', stagger: .12, scrollTrigger: { trigger: l, start: 'top 85%' } }));

  /* ---------- Cartes : apparition échelonnée ---------- */
  $$('.profils, .cartes, .orateurs, .etapes, .avantages, .grille-3').forEach((g) => {
    G.from(libre([...g.children]), { y: 60, opacity: 0, duration: 1, ease: 'expo.out', stagger: .08, scrollTrigger: { trigger: g, start: 'top 85%' }, clearProps: 'opacity' });
  });

  /* ---------- Lieu et visuels : parallaxe douce ---------- */
  $$('.lieu__visuel img').forEach((img) => G.fromTo(img, { yPercent: -8 }, { yPercent: 8, ease: 'none', scrollTrigger: { trigger: img.parentElement, scrub: true } }));

  /* ---------- Appel final : la date se remplit au défilement ---------- */
  $$('.final').forEach((f) => {
    const d = $('.final__date', f);
    G.fromTo(d, { '--rempli': '0%' }, { '--rempli': '100%', ease: 'none', scrollTrigger: { trigger: f, start: 'top 85%', end: 'center 55%', scrub: true } });
    G.from(d, { xPercent: 8, ease: 'none', scrollTrigger: { trigger: f, start: 'top bottom', end: 'bottom top', scrub: true } });
  });

  /* ---------- Programme : fondu des créneaux filtrés ---------- */
  const filtres = $('#programme-vide') && $('.filtres');
  if (filtres) filtres.addEventListener('click', () => requestAnimationFrame(() => {
    G.fromTo($$('.creneau:not([hidden])'), { opacity: 0, x: 24 }, { opacity: 1, x: 0, duration: .5, ease: 'expo.out', stagger: .03 });
    ScrollTrigger.refresh();
  }));

  /* ---------- Galerie : parallaxe des photos et entrée en cascade ---------- */
  $$('[data-parallaxe-img]').forEach((img) => G.fromTo(img, { yPercent: -10 }, { yPercent: 2, ease: 'none', scrollTrigger: { trigger: img.parentElement, scrub: true } }));
  $$('.experience').forEach((g) => G.from(g.children, { clipPath: 'inset(100% 0 0 0)', duration: 1.2, ease: 'expo.inOut', stagger: .1, scrollTrigger: { trigger: g, start: 'top 80%' } }));

  /* ---------- Icônes des secteurs : attirées par la souris ---------- */
  if (fin) $$('.secteur').forEach((c) => {
    const ico = $('.secteur__ico-cadre', c);
    const xTo = G.quickTo(ico, 'x', { duration: .6, ease: 'power3' }), yTo = G.quickTo(ico, 'y', { duration: .6, ease: 'power3' });
    c.addEventListener('pointermove', (e) => { const r = c.getBoundingClientRect(); xTo((e.clientX - r.left - r.width / 2) * .12); yTo((e.clientY - r.top - r.height / 2) * .12); });
    c.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
  });

  /* ---------- Titres de section : mots qui montent avec un léger flou ---------- */
  $$('.sec h2.lignes .l > span').forEach((l) => {
    l.innerHTML = l.innerHTML.split(' ').map((m) => `<span class="mot-titre">${m}</span>`).join(' ');
  });
  $$('.sec h2.lignes').forEach((h) => G.from($$('.mot-titre', h), { filter: 'blur(8px)', opacity: .2, duration: 1, ease: 'expo.out', stagger: .05, scrollTrigger: { trigger: h, start: 'top 85%' } }));

  /* ---------- Mosaïque de la page Intervenants : portraits qui s'allument ---------- */
  $$('.iv-mosaique').forEach((m) => {
    const imgs = $$('img', m);
    G.from(imgs, { opacity: 0, scale: .6, duration: .8, ease: 'expo.out', stagger: { each: .025, from: 'random' } });
    setInterval(() => { imgs.forEach((i) => i.classList.remove('is-on')); for (let k = 0; k < 4; k++) imgs[(Math.random() * imgs.length) | 0].classList.add('is-on'); }, 1400);
    G.to(m, { yPercent: -8, ease: 'none', scrollTrigger: { trigger: m.closest('section'), start: 'top top', end: 'bottom top', scrub: true } });
  });

  /* ---------- Vedettes et cartes d'intervenants : entrée échelonnée ---------- */
  $$('.iv-vedettes, .iv-grille').forEach((g) => G.from(libre([...g.children]).slice(0, 12), { y: 50, opacity: 0, duration: .9, ease: 'expo.out', stagger: .06, scrollTrigger: { trigger: g, start: 'top 85%' }, clearProps: 'opacity,transform' }));

  /* recalcul après chargement des images, polices et changements de vue (maquette autonome) */
  ScrollTrigger.sort();
  addEventListener('load', () => ScrollTrigger.refresh());
  window.DT_refresh = () => { ScrollTrigger.refresh(); };
})();

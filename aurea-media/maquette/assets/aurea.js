/* Aurea Media : interactions de la maquette (sans dépendance) */
(() => {
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fine = matchMedia('(pointer: fine)').matches;

  // En-tête qui se resserre au scroll
  const hd = document.querySelector('.hd');
  const onScroll = () => hd && hd.classList.toggle('is-scrolled', scrollY > 30);
  addEventListener('scroll', onScroll, { passive: true }); onScroll();

  // Menu mobile
  const burger = document.querySelector('.burger');
  burger && burger.addEventListener('click', () => {
    const open = document.body.classList.toggle('menu-open');
    burger.setAttribute('aria-expanded', open);
  });
  document.querySelectorAll('.mnav a').forEach(a => a.addEventListener('click', () => {
    document.body.classList.remove('menu-open'); burger.setAttribute('aria-expanded', false);
  }));

  // Titres découpés en mots
  document.querySelectorAll('.split-words').forEach(el => {
    let i = 0;
    const walk = node => [...node.childNodes].forEach(n => {
      if (n.nodeType === 3) {
        const frag = document.createDocumentFragment();
        n.textContent.split(/([ \t\n\r]+)/).forEach(part => {
          if (!part) return;
          if (/^[ \t\n\r]+$/.test(part)) { frag.append(part); return; }
          const w = document.createElement('span'); w.className = 'w';
          const s = document.createElement('span'); s.style.setProperty('--i', i++); s.textContent = part;
          w.append(s); frag.append(w);
        });
        n.replaceWith(frag);
      } else if (n.nodeType === 1 && n.tagName !== 'BR') walk(n);
    });
    walk(el);
    // La ponctuation (« , . ? : ») reste collée au mot précédent, même après un mot en or
    el.querySelectorAll('.w').forEach(w => {
      if (!/^[,.;:!?»)]/.test(w.textContent)) return;
      let prev = w.previousSibling;
      const espace = prev && prev.nodeType === 3 && /^[ \t\n\r]+$/.test(prev.textContent) ? prev : null;
      if (espace) prev = espace.previousSibling;
      if (!prev || prev.nodeType !== 1) return;
      const groupe = document.createElement('span'); groupe.className = 'nw';
      prev.before(groupe); groupe.append(prev);
      if (espace) groupe.append('\u00a0');
      groupe.append(w);
    });
  });

  // Apparitions au scroll
  const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
  }), { rootMargin: '0px 0px -10% 0px' });
  document.querySelectorAll('.rv, .split-words').forEach(el => reduce ? el.classList.add('is-in') : io.observe(el));

  // Compteurs
  const cio = new IntersectionObserver(es => es.forEach(e => {
    if (!e.isIntersecting) return;
    const el = e.target, to = parseFloat(el.dataset.count), dec = (el.dataset.count.split('.')[1] || '').length;
    const t0 = performance.now(), dur = 1600;
    const tick = t => {
      const p = Math.min(1, (t - t0) / dur), v = to * (1 - Math.pow(1 - p, 3));
      el.firstChild.textContent = v.toFixed(dec).replace('.', ',');
      p < 1 && requestAnimationFrame(tick);
    };
    reduce ? (el.firstChild.textContent = String(to).replace('.', ',')) : requestAnimationFrame(tick);
    cio.unobserve(el);
  }), { threshold: .6 });
  document.querySelectorAll('[data-count]').forEach(el => cio.observe(el));

  // Halo de curseur et cartes éclairées
  if (fine && !reduce) {
    const glow = document.querySelector('.glow');
    addEventListener('pointermove', e => {
      glow && glow.style.setProperty('--x', e.clientX + 'px');
      glow && glow.style.setProperty('--y', e.clientY + 'px');
    }, { passive: true });
    document.querySelectorAll('.card').forEach(c => c.addEventListener('pointermove', e => {
      const r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    }));
    // Boutons magnétiques
    document.querySelectorAll('.btn').forEach(b => {
      b.addEventListener('pointermove', e => {
        const r = b.getBoundingClientRect();
        b.style.transform = `translate(${(e.clientX - r.left - r.width / 2) * .06}px,${(e.clientY - r.top - r.height / 2) * .1}px)`;
      });
      b.addEventListener('pointerleave', () => { b.style.transform = ''; });
    });
  }

  // Réalisations : défilement horizontal piloté par le scroll
  const work = document.querySelector('.work'), track = document.querySelector('.work-track'), bar = document.querySelector('.work-progress i');
  const desk = matchMedia('(min-width: 681px)');
  const sizeWork = () => {
    if (!work || !track) return;
    if (!desk.matches || reduce) { work.style.height = ''; track.style.transform = ''; return; }
    work.style.height = (track.scrollWidth - innerWidth + innerHeight * 1.2) + 'px';
  };
  const moveWork = () => {
    if (!work || !track || !desk.matches || reduce) return;
    const r = work.getBoundingClientRect(), max = work.offsetHeight - innerHeight;
    const p = Math.min(1, Math.max(0, -r.top / max));
    track.style.transform = `translate3d(${-p * (track.scrollWidth - innerWidth)}px,0,0)`;
    bar && (bar.style.width = p * 100 + '%');
  };
  addEventListener('resize', () => { sizeWork(); moveWork(); });
  addEventListener('scroll', moveWork, { passive: true });
  addEventListener('load', () => { sizeWork(); moveWork(); });
  sizeWork();

  // Méthode : ligne qui se remplit
  const steps = document.querySelector('.steps');
  if (steps) {
    const items = steps.querySelectorAll('.step');
    const fill = () => {
      const r = steps.getBoundingClientRect();
      const p = Math.min(1, Math.max(0, (innerHeight * .75 - r.top) / (r.height + innerHeight * .25)));
      steps.style.setProperty('--p', reduce ? 1 : p);
      items.forEach((s, i) => s.classList.toggle('is-on', reduce || p >= i / items.length));
    };
    addEventListener('scroll', fill, { passive: true }); fill();
  }

  // Écrans d'ordinateur : diaporama des réalisations
  document.querySelectorAll('.laptop-view').forEach(v => {
    const imgs = v.querySelectorAll('img');
    if (imgs.length < 2) { imgs[0] && imgs[0].classList.add('is-on'); return; }
    let i = 0; imgs[0].classList.add('is-on');
    if (reduce) return;
    setInterval(() => {
      imgs[i].classList.remove('is-on'); i = (i + 1) % imgs.length; imgs[i].classList.add('is-on');
    }, 3600);
  });

  // Vidéos : chargées et lancées seulement quand elles sont à l'écran
  const vio = new IntersectionObserver(es => es.forEach(e => {
    const v = e.target;
    if (e.isIntersecting) {
      if (!v.dataset.charge) { v.querySelectorAll('source[data-src]').forEach(s => { s.src = s.dataset.src; }); v.load(); v.dataset.charge = '1'; }
      if (!reduce) v.play().catch(() => {});
    } else v.pause();
  }), { rootMargin: '200px' });
  document.querySelectorAll('video[data-lazy-video]').forEach(v => vio.observe(v));

  // Avant / après : curseur
  document.querySelectorAll('[data-ba]').forEach(ba => {
    const r = ba.querySelector('.ba-range');
    const maj = () => ba.style.setProperty('--pos', r.value + '%');
    r.addEventListener('input', maj); maj();
  });

  // Filtres des réalisations
  const gal = document.querySelector('.gal--filtre');
  if (gal) {
    const n = document.querySelector('.filtres-n');
    const compter = () => { const v = gal.querySelectorAll('.proj:not([hidden])').length; n && (n.textContent = v + ' projet' + (v > 1 ? 's' : '')); };
    document.querySelectorAll('.filtre[data-filtre]').forEach(b => b.addEventListener('click', () => {
      document.querySelectorAll('.filtre[data-filtre]').forEach(x => { x.classList.toggle('is-on', x === b); x.setAttribute('aria-pressed', x === b); });
      gal.querySelectorAll('.proj').forEach(p => { p.hidden = !!b.dataset.filtre && p.dataset.type !== b.dataset.filtre; p.classList.add('is-in'); });
      compter();
    }));
    compter();
  }

  // Formulaire de contact (maquette : rien n'est envoyé)
  const form = document.getElementById('form-contact');
  if (form) form.addEventListener('submit', e => {
    e.preventDefault();
    const mail = form.querySelector('#f-mail'), err = form.querySelector('#f-mail-err');
    const okMail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail.value.trim());
    mail.setAttribute('aria-invalid', !okMail); err.hidden = okMail;
    const requis = [...form.querySelectorAll('[required]')];
    const vide = requis.find(c => !c.value.trim());
    if (!okMail) { mail.focus(); return; }
    if (vide) { vide.setAttribute('aria-invalid', true); vide.focus(); return; }
    form.querySelector('.f-ok').hidden = false;
    form.querySelector('button[type=submit]').disabled = true;
  });

  // FAQ : une seule question ouverte à la fois
  document.querySelectorAll('.faq details').forEach(d => d.addEventListener('toggle', () => {
    if (d.open) document.querySelectorAll('.faq details[open]').forEach(o => o !== d && (o.open = false));
  }));
})();

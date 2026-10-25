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
        n.textContent.split(/(\s+)/).forEach(part => {
          if (!part) return;
          if (/^\s+$/.test(part)) { frag.append(part); return; }
          const w = document.createElement('span'); w.className = 'w';
          const s = document.createElement('span'); s.style.setProperty('--i', i++); s.textContent = part;
          w.append(s); frag.append(w);
        });
        n.replaceWith(frag);
      } else if (n.nodeType === 1 && n.tagName !== 'BR') walk(n);
    });
    walk(el);
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
        b.style.transform = `translate(${(e.clientX - r.left - r.width / 2) * .18}px,${(e.clientY - r.top - r.height / 2) * .3}px)`;
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

  // FAQ : une seule question ouverte à la fois
  document.querySelectorAll('.faq details').forEach(d => d.addEventListener('toggle', () => {
    if (d.open) document.querySelectorAll('.faq details[open]').forEach(o => o !== d && (o.open = false));
  }));
})();

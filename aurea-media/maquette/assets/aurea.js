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
  const desk = matchMedia('(min-width: 1081px)');
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
  document.querySelectorAll('.laptop-view:not([data-sync])').forEach(v => {
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
  // Sur le site WordPress (data-envoi="serveur"), la demande part au serveur une fois les champs vérifiés.
  if (form) form.addEventListener('submit', e => {
    const mail = form.querySelector('#f-mail'), err = form.querySelector('#f-mail-err');
    const okMail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail.value.trim());
    mail.setAttribute('aria-invalid', !okMail); err.hidden = okMail;
    const requis = [...form.querySelectorAll('[required]')];
    const vide = requis.find(c => !c.value.trim());
    if (!okMail) { e.preventDefault(); mail.focus(); return; }
    if (vide) { e.preventDefault(); vide.setAttribute('aria-invalid', true); vide.focus(); return; }
    if (form.dataset.envoi === 'serveur') { form.querySelector('button[type=submit]').disabled = true; return; }
    e.preventDefault();
    form.querySelector('.f-ok').hidden = false;
    form.querySelector('button[type=submit]').disabled = true;
  });

  // Galeries : afficher tous les projets
  document.querySelectorAll('[data-plus]').forEach(b => b.addEventListener('click', () => {
    const g = b.closest('.wrap').querySelector('.gal--plus');
    g.classList.add('is-ouvert'); g.querySelectorAll('.is-plus').forEach(p => p.classList.add('is-in'));
    b.parentElement.hidden = true;
  }));

  // Carte Refonte : le curseur suit la souris (ou le doigt)
  document.querySelectorAll('[data-ba-survol]').forEach(v => {
    const echelle = v.querySelector('.ba2-echelle');
    const ajuster = () => echelle && echelle.style.setProperty('--k', v.offsetWidth / 540);
    ajuster(); addEventListener('resize', ajuster);
    const suivre = x => { const r = v.getBoundingClientRect(); v.style.setProperty('--pos', Math.min(96, Math.max(4, (x - r.left) / r.width * 100)) + '%'); v.classList.add('is-actif'); };
    v.addEventListener('pointermove', e => suivre(e.clientX));
    v.addEventListener('pointerleave', () => { v.style.setProperty('--pos', '50%'); });
  });

  // FAQ : une seule question ouverte à la fois
  /* FAQ : accordéon animé plus bas (une seule réponse ouverte à la fois) */
})();

/* Visuels synchronisés (hero) : ordinateur, téléphone et légende changent ensemble */
(() => {
  const groupes = {};
  document.querySelectorAll('[data-sync]').forEach(v => { (groupes[v.dataset.sync] = groupes[v.dataset.sync] || []).push(v); });
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  Object.entries(groupes).forEach(([nom, vues]) => {
    const etiquettes = document.querySelectorAll(`[data-sync-label="${nom}"] span`);
    const total = vues[0].querySelectorAll('img').length;
    let i = 0;
    const montrer = n => {
      vues.forEach(v => v.querySelectorAll('img').forEach((img, k) => img.classList.toggle('is-on', k === n)));
      etiquettes.forEach((e, k) => e.classList.toggle('is-on', k === n));
    };
    montrer(0);
    if (reduce || total < 2) return;
    setInterval(() => { if (document.hidden) return; i = (i + 1) % total; montrer(i); }, 3800);
  });
})();

/* FAQ : ouverture et fermeture animées (pages et articles), une seule réponse ouverte par bloc */
(() => {
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const items = document.querySelectorAll('.faq details, details.faq-bloc');
  items.forEach(d => {
    const summary = d.querySelector('summary');
    if (!summary || d.dataset.anime) return;
    d.dataset.anime = '1';
    // le contenu (tout sauf le résumé) est regroupé pour pouvoir animer sa hauteur
    const corps = document.createElement('div');
    corps.className = 'faq-corps';
    [...d.children].filter(c => c !== summary).forEach(c => corps.appendChild(c));
    d.appendChild(corps);
    let anim = null;
    const fermer = (instant) => {
      if (!d.open) return;
      if (reduce || instant) { d.open = false; return; }
      d.classList.add('is-fermeture');
      anim && anim.cancel();
      anim = corps.animate([{ height: corps.offsetHeight + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 320, easing: 'cubic-bezier(.4,0,.2,1)' });
      anim.onfinish = () => { d.open = false; d.classList.remove('is-fermeture'); anim = null; };
    };
    const ouvrir = () => {
      const groupe = d.closest('.faq, .entry-content, .prose, .cas-corps') || document;
      groupe.querySelectorAll('details[open]').forEach(o => o !== d && o.matches('.faq details, details.faq-bloc') && o._fermer && o._fermer());
      d.open = true;
      if (reduce) return;
      const h = corps.offsetHeight;
      anim && anim.cancel();
      anim = corps.animate([{ height: '0px', opacity: 0, transform: 'translateY(-6px)' }, { height: h + 'px', opacity: 1, transform: 'none' }], { duration: 420, easing: 'cubic-bezier(.22,1,.36,1)' });
      anim.onfinish = () => { anim = null; };
    };
    d._fermer = fermer;
    summary.addEventListener('click', e => { e.preventDefault(); d.open && !d.classList.contains('is-fermeture') ? fermer() : ouvrir(); });
  });
})();

/* Avis de l'accueil : carrousel (flèches, points, glisser au doigt) */
document.querySelectorAll('[data-defile]').forEach(bloc => {
  const piste = bloc.querySelector('.reviews--defile'), nav = bloc.querySelector('.defile-nav');
  if (!piste || !nav) return;
  const cartes = [...piste.children], prec = nav.querySelector('[data-prec]'), suiv = nav.querySelector('[data-suiv]'), points = nav.querySelector('.defile-points');
  const parPage = () => Math.max(1, Math.round(piste.clientWidth / (cartes[0].getBoundingClientRect().width + 16)));
  const pages = () => Math.max(1, Math.ceil(cartes.length / parPage()));
  const page = () => Math.round(piste.scrollLeft / Math.max(1, piste.scrollWidth - piste.clientWidth) * (pages() - 1)) || 0;
  const aller = n => { const c = cartes[Math.min(cartes.length - 1, n * parPage())]; piste.scrollTo({ left: c.offsetLeft - cartes[0].offsetLeft, behavior: 'smooth' }); };
  const dessiner = () => {
    const n = pages();
    nav.hidden = n < 2;
    if (points.children.length !== n) {
      points.innerHTML = '';
      for (let i = 0; i < n; i++) { const b = document.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', (document.documentElement.lang || '').startsWith('en') ? 'Go to reviews ' + (i + 1) : 'Aller aux avis ' + (i + 1)); b.addEventListener('click', () => aller(i)); points.appendChild(b); }
    }
    const p = page();
    [...points.children].forEach((b, i) => b.toggleAttribute('aria-current', i === p));
    prec.disabled = piste.scrollLeft < 4;
    suiv.disabled = piste.scrollLeft > piste.scrollWidth - piste.clientWidth - 4;
  };
  prec.addEventListener('click', () => aller(Math.max(0, page() - 1)));
  suiv.addEventListener('click', () => aller(Math.min(pages() - 1, page() + 1)));
  let t; piste.addEventListener('scroll', () => { clearTimeout(t); t = setTimeout(dessiner, 60); }, { passive: true });
  window.addEventListener('resize', dessiner);
  dessiner();
});

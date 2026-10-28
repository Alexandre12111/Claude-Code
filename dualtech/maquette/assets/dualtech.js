/* DualTech 2026 : interactions de la maquette (JavaScript natif, sans bibliothèque) */
(() => {
  const doc = document.documentElement;
  const calme = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];

  /* En-tête : fond au défilement, masqué en descente, barre de progression */
  const nav = $('.nav');
  const barre = $('.nav__progression');
  let dernierY = 0, tic = false;
  const surDefilement = () => {
    const y = scrollY;
    nav.classList.toggle('is-solid', y > 40 || !$('.hero'));
    nav.classList.toggle('is-cachee', y > 640 && y > dernierY && !document.body.classList.contains('menu-ouvert'));
    dernierY = y;
    const h = doc.scrollHeight - innerHeight;
    if (barre) barre.style.transform = `scaleX(${h > 0 ? y / h : 0})`;
    parallaxe();
    manifeste();
    filProgramme();
    tic = false;
  };
  addEventListener('scroll', () => { if (!tic) { tic = true; requestAnimationFrame(surDefilement); } }, { passive: true });

  /* Menu mobile */
  const burger = $('.nav__burger'), tiroir = $('#tiroir');
  const basculer = (ouvrir) => {
    document.body.classList.toggle('menu-ouvert', ouvrir);
    tiroir.classList.toggle('is-ouvert', ouvrir);
    burger.setAttribute('aria-expanded', ouvrir);
    burger.setAttribute('aria-label', ouvrir ? 'Fermer le menu' : 'Ouvrir le menu');
    tiroir.inert = !ouvrir;
    if (ouvrir) $('a', tiroir).focus();
  };
  if (burger) {
    tiroir.inert = true;
    burger.addEventListener('click', () => basculer(!tiroir.classList.contains('is-ouvert')));
    addEventListener('keydown', (e) => { if (e.key === 'Escape' && tiroir.classList.contains('is-ouvert')) { basculer(false); burger.focus(); } });
    $$('a', tiroir).forEach((a) => a.addEventListener('click', () => basculer(false)));
  }

  /* Apparitions */
  const io = new IntersectionObserver((entrees) => entrees.forEach((e) => {
    if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
  }), { rootMargin: '0px 0px -8% 0px', threshold: .12 });
  $$('[data-reveal], .lignes').forEach((el) => io.observe(el));

  /* Compteurs */
  const formater = (n) => n.toLocaleString('fr-FR').replace(/ |\s/g, ' ');
  const ioC = new IntersectionObserver((entrees) => entrees.forEach((e) => {
    if (!e.isIntersecting) return;
    ioC.unobserve(e.target);
    const el = e.target, fin = +el.dataset.compte, t0 = performance.now(), duree = 1600;
    if (calme) { el.textContent = formater(fin); return; }
    const pas = (t) => {
      const p = Math.min((t - t0) / duree, 1), v = Math.round(fin * (1 - Math.pow(1 - p, 4)));
      el.textContent = formater(v);
      if (p < 1) requestAnimationFrame(pas);
    };
    requestAnimationFrame(pas);
  }), { threshold: .5 });
  $$('[data-compte]').forEach((el) => ioC.observe(el));

  /* Compte à rebours jusqu'au jeudi 26 novembre 2026, 8 h 30 (Paris) */
  const cible = new Date('2026-11-26T08:30:00+01:00').getTime();
  const decomptes = $$('[data-decompte]');
  const majDecompte = () => {
    let d = Math.max(0, cible - Date.now());
    const v = { j: Math.floor(d / 864e5), h: Math.floor(d / 36e5) % 24, m: Math.floor(d / 6e4) % 60, s: Math.floor(d / 1e3) % 60 };
    decomptes.forEach((bloc) => Object.entries(v).forEach(([k, n]) => {
      const el = $(`[data-u="${k}"]`, bloc);
      if (el) el.textContent = String(n).padStart(2, '0');
    }));
  };
  if (decomptes.length) { majDecompte(); setInterval(majDecompte, 1000); }

  /* Hero vidéo : image de couverture d'abord, vidéo YouTube ensuite */
  const hero = $('.hero[data-video]');
  let lecteur = null;
  const commande = (func, args = []) => lecteur && lecteur.contentWindow && lecteur.contentWindow.postMessage(JSON.stringify({ event: 'command', func, args }), '*');
  if (hero) {
    const id = hero.dataset.video;
    const ctrlSon = $('[data-action="son"]', hero), ctrlLecture = $('[data-action="lecture"]', hero);
    const charger = () => {
      if (lecteur) return;
      lecteur = document.createElement('iframe');
      lecteur.className = 'hero__video';
      lecteur.title = 'Film DualTech, lecture en arrière-plan';
      lecteur.setAttribute('tabindex', '-1');
      lecteur.setAttribute('aria-hidden', 'true');
      lecteur.allow = 'autoplay; encrypted-media; picture-in-picture';
      lecteur.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&mute=1&controls=0&loop=1&playlist=${id}&playsinline=1&rel=0&modestbranding=1&iv_load_policy=3&disablekb=1&enablejsapi=1&origin=${encodeURIComponent(location.origin)}`;
      /* la vidéo ne remplace l'image qu'une fois réellement en lecture (sinon l'image de couverture reste) */
      lecteur.addEventListener('load', () => lecteur.contentWindow.postMessage(JSON.stringify({ event: 'listening', id: 'hero' }), '*'));
      addEventListener('message', (e) => {
        if (!/youtube/.test(e.origin)) return;
        let d; try { d = JSON.parse(e.data); } catch { return; }
        const etat = d.info && (d.info.playerState ?? (d.event === 'onStateChange' ? d.info : undefined));
        if (d.event === 'onStateChange' ? d.info === 1 : etat === 1) hero.classList.add('is-video');
      });
      $('.hero__media', hero).appendChild(lecteur);
    };
    if (!calme) {
      addEventListener('load', () => ('requestIdleCallback' in window ? requestIdleCallback(charger, { timeout: 1500 }) : setTimeout(charger, 600)));
    }
    ctrlSon.addEventListener('click', () => {
      charger();
      const actif = ctrlSon.dataset.etat === 'son-on';
      commande(actif ? 'mute' : 'unMute');
      if (!actif) commande('setVolume', [70]);
      ctrlSon.dataset.etat = actif ? 'son-off' : 'son-on';
      ctrlSon.setAttribute('aria-pressed', !actif);
      ctrlSon.setAttribute('aria-label', actif ? 'Activer le son de la vidéo' : 'Couper le son de la vidéo');
    });
    ctrlLecture.addEventListener('click', () => {
      const enPause = ctrlLecture.dataset.etat === 'pause';
      if (enPause) { charger(); commande('playVideo'); hero.classList.add('is-video'); } else commande('pauseVideo');
      ctrlLecture.dataset.etat = enPause ? 'lecture' : 'pause';
      ctrlLecture.setAttribute('aria-label', enPause ? 'Mettre la vidéo en pause' : 'Lire la vidéo');
    });
    if (calme) { ctrlLecture.dataset.etat = 'pause'; ctrlLecture.setAttribute('aria-label', 'Lire la vidéo'); }
    /* pause automatique quand le hero sort de l'écran */
    new IntersectionObserver(([e]) => {
      if (ctrlLecture.dataset.etat === 'pause') return;
      commande(e.isIntersecting ? 'playVideo' : 'pauseVideo');
    }, { threshold: .05 }).observe(hero);
  }

  /* Vidéo d'arrière-plan des pages intérieures */
  $$('[data-video-fond]').forEach((cadre) => {
    if (calme) return;
    addEventListener('load', () => setTimeout(() => {
      const f = document.createElement('iframe');
      const id = cadre.dataset.videoFond, fin = cadre.dataset.fin ? `&end=${cadre.dataset.fin}` : '';
      f.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&mute=1&controls=0&loop=1&playlist=${id}&playsinline=1&rel=0&modestbranding=1&disablekb=1&fs=0${fin}`;
      f.title = 'Vidéo d’ambiance'; f.setAttribute('aria-hidden', 'true'); f.setAttribute('tabindex', '-1'); f.allow = 'autoplay; encrypted-media';
      f.style.opacity = 0; f.style.transition = 'opacity 1.2s';
      f.addEventListener('load', () => setTimeout(() => { f.style.opacity = ''; const img = $('img', cadre); if (img) img.style.opacity = 0; }, 900));
      cadre.appendChild(f);
    }, 400));
  });

  /* Fenêtre « Voir le film » */
  const modale = $('#film');
  if (modale) {
    const cadre = $('.modale__cadre', modale);
    $$('[data-film]').forEach((b) => b.addEventListener('click', () => {
      cadre.innerHTML = `<iframe src="https://www.youtube-nocookie.com/embed/${b.dataset.film}?autoplay=1&rel=0&modestbranding=1" title="Film de présentation DualTech" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>`;
      commande('pauseVideo');
      modale.showModal();
    }));
    const fermer = () => { modale.close(); };
    $('.modale__fermer', modale).addEventListener('click', fermer);
    modale.addEventListener('click', (e) => { if (e.target === modale) fermer(); });
    modale.addEventListener('close', () => {
      cadre.innerHTML = '';
      const ctrl = $('[data-action="lecture"]');
      if (ctrl && ctrl.dataset.etat !== 'pause') commande('playVideo');
    });
  }

  /* Parallaxe douce */
  const plx = calme ? [] : $$('[data-parallaxe]');
  function parallaxe() {
    plx.forEach((el) => {
      const r = el.parentElement.getBoundingClientRect();
      if (r.bottom < 0 || r.top > innerHeight) return;
      const p = (r.top + r.height / 2 - innerHeight / 2) / innerHeight;
      el.style.transform = `translate3d(0, ${(-p * (+el.dataset.parallaxe || .12) * 100).toFixed(2)}px, 0)`;
    });
  }

  /* Manifeste : les mots s'éclairent au défilement */
  const mani = $('.manifeste__titre');
  let mots = [];
  if (mani) {
    const forts = (mani.dataset.forts || '').split('|');
    const texte = mani.textContent.trim();
    mani.setAttribute('aria-label', texte);
    mani.innerHTML = texte.split(/\s+/).map((m) => `<span class="mot${forts.some((f) => f && m.toLowerCase().startsWith(f)) ? ' fort' : ''}" aria-hidden="true">${m}</span>`).join(' ');
    mots = $$('.mot', mani);
  }
  function manifeste() {
    if (!mots.length) return;
    const r = mani.getBoundingClientRect();
    const p = Math.min(Math.max((innerHeight * .85 - r.top) / (r.height + innerHeight * .35), 0), 1);
    const n = calme ? mots.length : Math.round(p * mots.length);
    mots.forEach((m, i) => m.classList.toggle('is-on', i < n));
  }

  /* Carte Google chargée au clic seulement */
  $$('[data-carte]').forEach((b) => b.addEventListener('click', () => {
    const f = document.createElement('iframe');
    f.src = b.dataset.carte; f.title = 'Plan d’accès au Campus Cyber'; f.loading = 'lazy'; f.referrerPolicy = 'no-referrer-when-downgrade';
    b.replaceWith(f);
  }));

  /* Programme : filtres par format et fil de temps */
  const filtres = $('.filtres');
  if (filtres) {
    const creneaux = $$('.creneau[data-type]');
    filtres.addEventListener('click', (e) => {
      const b = e.target.closest('button'); if (!b) return;
      $$('button', filtres).forEach((x) => x.setAttribute('aria-pressed', x === b));
      const t = b.dataset.filtre;
      let n = 0;
      creneaux.forEach((c) => { const ok = t === 'tous' || c.dataset.type === t; c.hidden = !ok; n += ok; });
      $$('.partie').forEach((p) => { p.hidden = !$$('.creneau[data-type]:not([hidden])', p).length; });
      $('#programme-vide').hidden = n > 0;
      $('#programme-statut').textContent = `${n} séquence${n > 1 ? 's' : ''} affichée${n > 1 ? 's' : ''}`;
    });
  }
  const fils = calme ? [] : $$('.creneaux__fil');
  function filProgramme() {
    fils.forEach((f) => {
      const r = f.parentElement.getBoundingClientRect();
      const p = Math.min(Math.max((innerHeight * .6 - r.top) / r.height, 0), 1);
      f.style.transform = `scaleY(${p})`;
    });
  }

  /* Intervenants : recherche */
  const champ = $('#recherche-intervenant');
  if (champ) {
    const cartes = $$('.orateur[data-cherche]');
    const vide = $('#intervenants-vide'), statut = $('#intervenants-statut');
    const norm = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const filtrer = () => {
      const q = norm(champ.value.trim());
      let n = 0;
      cartes.forEach((c) => { const ok = !q || c.dataset.cherche.includes(q); c.hidden = !ok; n += ok; });
      vide.hidden = n > 0;
      statut.textContent = `${n} intervenant${n > 1 ? 's' : ''}`;
    };
    champ.addEventListener('input', filtrer);
    $('#recherche-effacer').addEventListener('click', () => { champ.value = ''; filtrer(); champ.focus(); });
  }

  /* Prix : calendrier en onglets */
  const cal = $('.calendrier');
  if (cal) {
    const onglets = $$('[role="tab"]', cal);
    const choisir = (o) => {
      onglets.forEach((x) => { const sel = x === o; x.setAttribute('aria-selected', sel); x.tabIndex = sel ? 0 : -1; $('#' + x.getAttribute('aria-controls')).hidden = !sel; });
    };
    onglets.forEach((o, i) => {
      o.addEventListener('click', () => choisir(o));
      o.addEventListener('keydown', (e) => {
        const d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
        if (d) { const c = onglets[(i + d + onglets.length) % onglets.length]; choisir(c); c.focus(); }
      });
    });
  }

  /* Ancres de page : section active */
  const ancres = $$('.ancres a');
  if (ancres.length) {
    const ioA = new IntersectionObserver((entrees) => entrees.forEach((e) => {
      if (e.isIntersecting) ancres.forEach((a) => a.classList.toggle('is-actif', a.hash === '#' + e.target.id));
    }), { rootMargin: '-45% 0px -50% 0px' });
    ancres.forEach((a) => { const s = $(a.hash); if (s) ioA.observe(s); });
  }

  surDefilement();
})();

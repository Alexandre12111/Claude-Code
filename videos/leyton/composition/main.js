/*
 * Leyton : vidéo de présentation du site leyton.com/fr
 * Composition 1920 × 1080 pilotée par une timeline GSAP en pause.
 * Le moteur de rendu (scripts/render.js) appelle window.__seek(t) pour chaque image, puis capture l'écran.
 * Aperçu en temps réel : npm run preview (ouvre index.html?play via un serveur local).
 */
(() => {
  const $ = (s) => document.querySelector(s);

  /* ------------------------------------------------------------------ */
  /* Contenus                                                            */
  /* ------------------------------------------------------------------ */
  // [[texte]] = dégradé orange vers pêche, {{texte}} = orange
  const CAPTIONS = {
    A: { pill: 'Accueil', lines: ['/ Une promesse claire', 'dès l’accueil'], p: 'Leyton accompagne les entreprises dans le financement de l’innovation et l’amélioration de leur performance.' },
    B: { pill: 'Expertises', lines: ['/ Vos enjeux', 'transformés en {{leviers}}', '{{de performance}}'], p: 'Nos experts identifient, sécurisent et activent les bons leviers pour vous.' },
    D: { pill: 'Notre méthode', lines: ['/ De l’analyse', 'aux résultats'], p: 'Gagnez du temps, maximisez vos financements et sécurisez vos décisions.' },
    E: { pill: 'Résultats clients', lines: ['/ La preuve', 'par nos clients'], p: 'Témoignages et cas concrets&nbsp;: Stations-E, ComptaSecure, Groupe Carso…' },
    F: { pill: 'Références', lines: ['/ La confiance', 'de tous les secteurs'], p: 'Sephora, Ubisoft, bioMérieux, Fayat, emlyon business school…' },
    G: { pill: 'Ressources', lines: ['/ Des analyses pour', 'anticiper vos enjeux'], p: 'Articles, cas clients, livres blancs et webinaires signés par nos experts.' },
    H: { pill: 'À propos', lines: ['/ Un leader du conseil', 'depuis {{1997}}'], p: 'Près de 30 ans d’expertise auprès de plus de 50&nbsp;000 organisations.' },
    J: { pill: 'Intelligence artificielle', lines: ['/ L’IA au service', 'de la performance'], p: 'Leyton CognitX réunit plus de 200 experts tech et data scientists.' },
    K: { pill: 'Partenariat', lines: ['/ Partenaire officiel', 'du France SailGP Team'], p: 'Là où la technologie rencontre la haute performance.' },
    M: { pill: 'Mobile', lines: ['/ Une expérience fluide', 'sur tous les écrans'], p: 'Contenus, navigation et prise de contact pensés pour le mobile.' },
  };

  const FIGURES = [
    { icon: 'Group-27154.svg', pre: '+', to: 50000, suf: '', lbl: 'Entreprises et organisations publiques accompagnées dans le monde' },
    { icon: 'Group-27156.svg', pre: '+', to: 29, suf: ' ans', lbl: 'D’expertise en optimisation de la performance' },
    { icon: 'Group-27155.svg', pre: '', to: 20, suf: '', lbl: 'Pays dans lesquels Leyton est implanté' },
    { icon: 'boxicons_pie-chart.svg', pre: '+', to: 3000, suf: '', lbl: 'Experts et consultants à travers le monde' },
    { icon: 'Frame-2071858276.svg', pre: '', to: 2.4, dec: 1, suf: '&nbsp;Md€', lbl: 'De flux financiers traités pour nos clients en France' },
  ];

  const EXPERTISES = [
    { slug: 'financement-innovation', name: 'Financement de l’innovation', desc: 'CIR, CII, JEI, subventions&nbsp;: 7 solutions pour financer votre innovation.' },
    { slug: 'fiscalite-et-performance', name: 'Fiscalité et performance financière', desc: 'Une offre complète pour maîtriser votre performance fiscale.' },
    { slug: 'performance-achats', name: 'Performance achats', desc: 'Un accompagnement 360° au service de vos marges.' },
    { slug: 'performance-rh', name: 'Performance RH', desc: 'Six solutions clés pour optimiser votre performance RH.' },
    { slug: 'performance-environnementale', name: 'Performance environnementale', desc: 'Alignez transition environnementale et rentabilité.' },
    { slug: 'formations', name: 'Formations', desc: 'Leyton Academy&nbsp;: renforcez les compétences de vos équipes.' },
  ];

  const PHONES = [
    { img: 'm-fin', cx: 880, cy: 578, s: 0.8, scroll: 2350 },
    { img: 'm-home', cx: 1280, cy: 548, s: 0.9, scroll: 2500 },
    { img: 'm-apropos', cx: 1680, cy: 578, s: 0.8, scroll: 2650 },
  ];

  // Positions de défilement (px CSS) des sections capturées sur leyton.com/fr
  const HOME = { expertises: 939, impact: 1714, methode: 2500, clients: 3505, logos: 4480, analyses: 5407 };
  const ABOUT = { histoire: 330, ia: 3650, sailgp: 4990 };

  /* ------------------------------------------------------------------ */
  /* Construction du DOM                                                  */
  /* ------------------------------------------------------------------ */
  const markup = (s) => s.replace(/\[\[(.+?)\]\]/g, '<span class="grad">$1</span>').replace(/\{\{(.+?)\}\}/g, '<span class="or">$1</span>');
  const fillHeading = (el, lines) => { el.innerHTML = lines.map((l) => `<span class="ln"><span class="li">${markup(l)}</span></span>`).join(''); return el; };
  const LOCK = '<svg viewBox="0 0 12 14"><rect x="1" y="6" width="10" height="7.5" rx="2" fill="#012D48" opacity=".75"/><path d="M3.2 6V4.3a2.8 2.8 0 0 1 5.6 0V6" fill="none" stroke="#012D48" stroke-opacity=".75" stroke-width="1.6"/></svg>';

  const caps = {};
  const makeCap = (key, left) => {
    const c = CAPTIONS[key];
    const d = document.createElement('div');
    d.className = 'cap'; d.style.left = left + 'px';
    d.innerHTML = `<span class="pill">${c.pill}</span><h2 class="h"></h2><p class="p">${c.p}</p>`;
    fillHeading(d.querySelector('.h'), c.lines);
    $('#caps').appendChild(d);
    caps[key] = d;
  };
  ['A', 'B', 'D', 'E', 'F', 'G', 'M'].forEach((k) => makeCap(k, 110));
  ['H', 'J', 'K'].forEach((k) => makeCap(k, 1284));

  fillHeading($('#tagline'), ['Votre partenaire pour {{l’innovation}}', '[[et la performance financière]]']);
  fillHeading($('#s4h'), ['/ Une expertise reconnue', 'au service de votre performance']);
  fillHeading($('#s5h'), ['/ Six expertises, un objectif&nbsp;: votre performance']);
  fillHeading($('#s8h'), ['/ Activez [[de nouveaux leviers]]', '[[de performance]]']);

  const fmtNum = (v, dec) => (dec ? v.toFixed(dec).replace('.', ',') : String(Math.round(v))).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
  FIGURES.forEach((f) => {
    const c = document.createElement('div');
    c.className = 'card';
    c.innerHTML = `<img src="assets/${f.icon}" alt=""><div class="num"></div><div class="lbl">${f.lbl}</div>`;
    $('#cards').appendChild(c);
    f.card = c; f.num = c.querySelector('.num'); f.v = 0;
    f.paint = () => { f.num.innerHTML = f.pre + fmtNum(f.v, f.dec) + f.suf; };
    f.paint();
  });

  EXPERTISES.forEach((e, i) => {
    const c = document.createElement('div');
    c.className = 'browser fcard';
    c.innerHTML = `<div class="toolbar"><span class="dots"><i></i><i></i><i></i></span><span class="url">${LOCK}<span>leyton.com/fr/${e.slug}</span></span></div><div class="vp"><img src="assets/x-${e.slug}.jpg" alt=""></div>`;
    $('#flow').appendChild(c);
    const l = document.createElement('div');
    l.className = 'flabel';
    l.innerHTML = `<div class="n">${String(i + 1).padStart(2, '0')} / 06</div><div class="t">${e.name}</div><div class="d">${e.desc}</div>`;
    $('#flabels').appendChild(l);
    e.card = c; e.label = l;
  });

  const STATUS = '<span>9:41</span><svg viewBox="0 0 78 13"><rect x="0" y="8" width="3.2" height="5" rx="1"/><rect x="5" y="5.5" width="3.2" height="7.5" rx="1"/><rect x="10" y="3" width="3.2" height="10" rx="1"/><rect x="15" y="0" width="3.2" height="13" rx="1"/><path d="M31.5 3.2a10.6 10.6 0 0 1 14 0l-1.5 1.6a8.4 8.4 0 0 0-11 0zM34.4 6.3a6.4 6.4 0 0 1 8.2 0L41 7.9a4.2 4.2 0 0 0-5 0zM38.5 12.4l-2-2.2a2.9 2.9 0 0 1 4 0z"/><rect x="53" y="0.5" width="22" height="12" rx="3.6" fill="none" stroke="#000" stroke-opacity=".4"/><rect x="55" y="2.5" width="16.5" height="8" rx="2"/><rect x="76.2" y="4.5" width="1.6" height="4" rx=".8" opacity=".45"/></svg>';
  PHONES.forEach((p) => {
    const d = document.createElement('div');
    d.className = 'phone';
    d.style.left = (p.cx - 209) + 'px'; d.style.top = (p.cy - 436) + 'px';
    d.innerHTML = `<div class="scr"><div class="sb">${STATUS}</div><div class="island"></div><div class="pvp"><img src="assets/${p.img}.jpg" alt=""></div></div>`;
    $('#phones').appendChild(d);
    p.el = d; p.page = d.querySelector('.pvp img');
  });

  /* ------------------------------------------------------------------ */
  /* Mise en place dépendante des polices et des images                   */
  /* ------------------------------------------------------------------ */
  const RING = { x: 955, y: 525, r: 405 };   // anneau de particules dans la vidéo hero (centre, rayon extérieur)
  const LOGO_W = 620, LOGO_CY = 452;           // logo final de l'intro
  const LOGO_UNIT_CY = 21.9;                   // centre vertical des lettres dans le viewBox du logo

  function placeLogo() {
    const Ls = LOGO_W / 108;
    const logo = $('#logo');
    Object.assign(logo.style, { width: LOGO_W + 'px', height: 44 * Ls + 'px', left: (960 - LOGO_W / 2) + 'px', top: (LOGO_CY - LOGO_UNIT_CY * Ls) + 'px' });
    const bb = $('#lO').getBBox();
    const ocx = bb.x + bb.width / 2, ocy = bb.y + bb.height / 2, orad = (bb.width + bb.height) / 4;
    const S0 = RING.r / orad;
    const big = $('#bigO');
    Object.assign(big.style, { width: 108 * S0 + 'px', height: 44 * S0 + 'px', left: (RING.x - ocx * S0) + 'px', top: (RING.y - ocy * S0) + 'px' });
    const target = { x: 960 - LOGO_W / 2 + ocx * Ls, y: LOGO_CY - LOGO_UNIT_CY * Ls + ocy * Ls };
    $('#tagline').style.top = (LOGO_CY + 118) + 'px';
    $('#endUrl').style.top = (LOGO_CY + 150) + 'px';
    return { origin: `${ocx * S0}px ${ocy * S0}px`, x: target.x - RING.x, y: target.y - RING.y, scale: Ls / S0, ocx };
  }

  function fitCaptions() {
    for (const cap of Object.values(caps)) {
      const h = cap.querySelector('.h');
      const w = Math.max(...[...h.querySelectorAll('.li')].map((li) => li.getBoundingClientRect().width));
      if (w > 540) h.style.fontSize = (48 * 540 / w).toFixed(2) + 'px';
    }
  }

  /* ------------------------------------------------------------------ */
  /* Intro vidéo (images extraites de la vidéo hero du site, 60 i/s)      */
  /* ------------------------------------------------------------------ */
  const INTRO_N = 299;
  const introCtx = $('#intro').getContext('2d');
  const vid = { f: 1 };
  let introWant = 1, introHave = 0;
  async function syncIntro() {
    if (introWant === introHave) return;
    const img = new Image();
    img.src = `assets/intro/f_${String(introWant).padStart(4, '0')}.jpg`;
    await img.decode();
    introCtx.drawImage(img, 0, 0, 1920, 1080);
    introHave = introWant;
  }

  /* ------------------------------------------------------------------ */
  /* Timeline                                                            */
  /* ------------------------------------------------------------------ */
  const tl = gsap.timeline({ paused: true, defaults: { ease: 'power3.out' } });
  const browser = $('#browser'), pgHome = $('#pgHome'), pgAbout = $('#pgAbout');
  const LAYOUT = {
    hero: { x: 240, y: 67, scale: 1 },
    right: { x: 548.4, y: 67, scale: 0.78 },
    left: { x: -68.4, y: 67, scale: 0.78 },
    dive: { x: 240, y: 16.5, scale: 2.15 },
  };

  function capIn(key, t) {
    const c = caps[key];
    tl.set(c, { visibility: 'visible' }, t);
    tl.fromTo(c.querySelector('.pill'), { autoAlpha: 0, y: 16, scale: 0.94 }, { autoAlpha: 1, y: 0, scale: 1, duration: 0.6 }, t);
    tl.fromTo(c.querySelectorAll('.li'), { yPercent: 118 }, { yPercent: 0, duration: 0.9, stagger: 0.09, ease: 'power4.out' }, t + 0.08);
    tl.fromTo(c.querySelector('.p'), { autoAlpha: 0, y: 20 }, { autoAlpha: 1, y: 0, duration: 0.75 }, t + 0.32);
  }
  function capOut(key, t) {
    const c = caps[key];
    tl.to(c.querySelectorAll('.li'), { yPercent: -118, duration: 0.45, stagger: 0.04, ease: 'power2.in' }, t);
    tl.to([c.querySelector('.pill'), c.querySelector('.p')], { autoAlpha: 0, y: -14, duration: 0.4, ease: 'power2.in' }, t);
    tl.set(c, { visibility: 'hidden' }, t + 0.55);
  }
  // Défile jusqu'à une section puis affiche sa légende ; renvoie l'instant de fin du maintien.
  function stop(img, y, key, t, { dur = 1.25, hold = 2.0 } = {}) {
    tl.to(img, { y: -y, duration: dur, ease: 'sine.inOut' }, t);
    capIn(key, t + dur - 0.45);
    return t + dur + hold;
  }

  function build() {
    const O = placeLogo();
    fitCaptions();

    // État initial
    tl.set(browser, { autoAlpha: 0, transformPerspective: 2400, transformOrigin: '50% 50%' }, 0);
    tl.set(pgAbout, { autoAlpha: 0 }, 0);
    tl.set(['#s4', '#s5', '#s8'], { visibility: 'hidden' }, 0);
    tl.set('#phones', { autoAlpha: 0 }, 0);
    tl.set('#endUrl', { autoAlpha: 0 }, 0);
    tl.set('#bigO', { autoAlpha: 0, transformOrigin: O.origin }, 0);
    tl.set('#lO', { autoAlpha: 0 }, 0);

    /* 1. Intro : les particules Leyton forment le symbole, puis le logo */
    const INTRO_DUR = (INTRO_N - 1) / 60;
    tl.to(vid, { f: INTRO_N, duration: INTRO_DUR, ease: 'none', onUpdate: () => { introWant = Math.round(vid.f); } }, 0);
    tl.fromTo('#intro', { autoAlpha: 0 }, { autoAlpha: 1, duration: 0.6, ease: 'power1.out' }, 0);
    tl.fromTo('#bigO', { autoAlpha: 0, scale: 1.035 }, { autoAlpha: 1, scale: 1, duration: 0.6, ease: 'power2.out', immediateRender: false }, 4.35);
    tl.to('#intro', { autoAlpha: 0, duration: 0.55, ease: 'power1.inOut' }, 4.6);
    tl.to('#bigO', { x: O.x, y: O.y, scale: O.scale, duration: 1.05, ease: 'expo.inOut' }, 5.0);
    tl.set('#lO', { autoAlpha: 1 }, 6.05);
    tl.set('#bigO', { autoAlpha: 0 }, 6.05);
    const letters = [['#lT', 56.7, 0], ['#lN', 98.2, 0], ['#lY', 39.7, 0.07], ['#lE', 21.8, 0.14], ['#lL', 6.5, 0.21]];
    letters.forEach(([id, cx, delay]) => {
      tl.fromTo(id, { x: (O.ocx - cx) * 0.45, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 0.85, ease: 'power3.out' }, 5.62 + delay);
    });
    tl.fromTo('#tagline .li', { yPercent: 118 }, { yPercent: 0, duration: 0.95, stagger: 0.1, ease: 'power4.out' }, 6.15);

    /* 2. Le site apparaît dans un navigateur */
    tl.to(['#logo', '#tagline'], { y: -90, autoAlpha: 0, duration: 0.7, ease: 'power2.in' }, 7.9);
    tl.fromTo(browser, { autoAlpha: 1, x: 240, y: 1160, scale: 0.94, rotationX: 28 },
      { y: LAYOUT.hero.y, scale: 1, rotationX: 0, duration: 1.45, ease: 'power3.out', immediateRender: false }, 8.05);
    tl.to(browser, { ...LAYOUT.right, duration: 1.15, ease: 'power3.inOut' }, 10.35);
    capIn('A', 10.95);

    /* 3. Accueil : défilement commenté */
    let t = 13.9;
    capOut('A', t - 0.15);
    t = stop(pgHome, HOME.expertises, 'B', t);
    capOut('B', t - 0.15);
    tl.to(pgHome, { y: -HOME.impact, duration: 1.2, ease: 'sine.inOut' }, t);
    t += 1.2;

    /* 4. Plongée dans les chiffres clés */
    tl.to(browser, { ...LAYOUT.dive, duration: 0.95, ease: 'power2.in' }, t);
    tl.set('#s4', { visibility: 'visible' }, t + 0.5);
    tl.fromTo('#s4', { opacity: 0 }, { opacity: 1, duration: 0.45, ease: 'none', immediateRender: false }, t + 0.5);
    const tf = t + 0.95;
    tl.fromTo('#s4 .pill', { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: 0.6 }, tf + 0.05);
    tl.fromTo('#s4h .li', { yPercent: 118 }, { yPercent: 0, duration: 0.95, stagger: 0.09, ease: 'power4.out' }, tf + 0.12);
    tl.fromTo('.card', { autoAlpha: 0, y: 70 }, { autoAlpha: 1, y: 0, duration: 0.9, stagger: 0.1 }, tf + 0.4);
    FIGURES.forEach((f, i) => {
      tl.fromTo(f, { v: 0 }, { v: f.to, duration: 1.9, ease: 'power2.out', onUpdate: f.paint }, tf + 0.55 + i * 0.1);
    });
    t = tf + 5.0;
    tl.to(['#s4 .head', '#cards'], { autoAlpha: 0, y: -30, duration: 0.5, ease: 'power2.in' }, t);
    tl.to('#s4', { opacity: 0, duration: 0.5, ease: 'none' }, t + 0.35);
    tl.set('#s4', { visibility: 'hidden' }, t + 0.9);
    tl.to(browser, { ...LAYOUT.right, duration: 1.0, ease: 'power3.out' }, t + 0.35);
    t += 1.4;

    /* 5. Suite de l'accueil */
    t = stop(pgHome, HOME.methode, 'D', t);
    capOut('D', t - 0.15);
    t = stop(pgHome, HOME.clients, 'E', t);
    capOut('E', t - 0.15);
    t = stop(pgHome, HOME.logos, 'F', t);
    capOut('F', t - 0.15);
    t = stop(pgHome, HOME.analyses, 'G', t);
    capOut('G', t - 0.15);

    /* 6. Les six expertises */
    tl.to(browser, { x: -1650, rotationY: 14, duration: 1.0, ease: 'power3.in' }, t);
    tl.set('#s5', { visibility: 'visible' }, t + 0.3);
    tl.fromTo('#s5 .pill', { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: 0.6 }, t + 0.55);
    tl.fromTo('#s5h .li', { yPercent: 118 }, { yPercent: 0, duration: 0.95, ease: 'power4.out' }, t + 0.62);
    const flow = { i: -2.6 };
    const layoutFlow = () => {
      EXPERTISES.forEach((e, i) => {
        const d = i - flow.i, a = Math.abs(d), s = Math.sign(d);
        const a1 = Math.min(a, 1), a2 = Math.max(0, Math.min(a - 1, 1.6));
        gsap.set(e.card, {
          x: s * (650 * a1 + 430 * a2),
          scale: 0.56 - 0.17 * a1 - 0.07 * a2,
          rotationY: -s * 24 * a1,
          opacity: Math.max(0, 1 - 0.38 * a1 - 0.62 * a2),
          zIndex: 100 - Math.round(a * 10),
          transformOrigin: '50% 50%',
        });
        gsap.set(e.label, { opacity: Math.max(0, 1 - a * 2.4), y: d * 28 });
      });
    };
    layoutFlow();
    tl.to(flow, { i: 0, duration: 1.3, ease: 'power3.out', onUpdate: layoutFlow }, t + 0.45);
    let tk = t + 0.45 + 1.3 + 1.15;
    for (let k = 1; k < EXPERTISES.length; k++) {
      tl.to(flow, { i: k, duration: 0.85, ease: 'power3.inOut', onUpdate: layoutFlow }, tk);
      tk += 0.85 + 1.15;
    }
    t = tk - 0.2;
    tl.to(flow, { i: EXPERTISES.length + 1.8, duration: 1.0, ease: 'power2.in', onUpdate: layoutFlow }, t);
    tl.to('#s5 .head', { autoAlpha: 0, y: -30, duration: 0.5, ease: 'power2.in' }, t + 0.2);
    tl.set('#s5', { visibility: 'hidden' }, t + 1.05);

    /* 7. À propos */
    t += 0.7;
    tl.set(pgHome, { autoAlpha: 0 }, t);
    tl.set(pgAbout, { autoAlpha: 1, y: -ABOUT.histoire }, t);
    tl.set('#urlHome', { display: 'none' }, t);
    tl.set('#urlAbout', { display: 'inline' }, t);
    tl.fromTo(browser, { x: LAYOUT.left.x, y: 1160, scale: 0.74, rotationX: 28, rotationY: 0 },
      { y: LAYOUT.left.y, scale: LAYOUT.left.scale, rotationX: 0, duration: 1.4, ease: 'power3.out', immediateRender: false }, t);
    capIn('H', t + 0.75);
    t += 0.75 + 2.7;
    capOut('H', t - 0.15);
    t = stop(pgAbout, ABOUT.ia, 'J', t, { dur: 1.6 });
    capOut('J', t - 0.15);
    t = stop(pgAbout, ABOUT.sailgp, 'K', t);
    capOut('K', t - 0.15);

    /* 8. Mobile */
    tl.to(browser, { y: -1200, rotationX: -18, duration: 1.0, ease: 'power3.in' }, t);
    tl.set('#phones', { autoAlpha: 1 }, t + 0.45);
    PHONES.forEach((p, i) => {
      tl.fromTo(p.el, { y: 900, scale: p.s * 0.96, rotation: (i - 1) * 3 }, { y: 0, scale: p.s, rotation: 0, duration: 1.3, ease: 'power3.out' }, t + 0.45 + [0.12, 0, 0.24][i]);
      tl.to(p.page, { y: -p.scroll, duration: 5.1, ease: 'sine.inOut' }, t + 1.4);
    });
    capIn('M', t + 0.8);
    t += 6.8;
    capOut('M', t - 0.15);
    tl.to(PHONES.map((p) => p.el), { y: -60, autoAlpha: 0, duration: 0.6, stagger: 0.06, ease: 'power2.in' }, t);

    /* 9. Appel à l'action et logo */
    t += 0.55;
    tl.set('#s8', { visibility: 'visible' }, t);
    tl.fromTo('#ring', { autoAlpha: 0, scale: 1.1 }, { autoAlpha: 0.55, scale: 1, duration: 2.2, ease: 'power2.out' }, t);
    tl.fromTo('#s8 .pill', { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: 0.6 }, t + 0.1);
    tl.fromTo('#s8h .li', { yPercent: 118 }, { yPercent: 0, duration: 1.0, stagger: 0.1, ease: 'power4.out' }, t + 0.2);
    tl.fromTo('#s8 .p', { autoAlpha: 0, y: 20 }, { autoAlpha: 1, y: 0, duration: 0.8 }, t + 0.55);
    tl.fromTo('#s8 .cta', { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: 0.8 }, t + 0.8);
    t += 3.4;
    tl.to('#s8 .body', { autoAlpha: 0, y: -40, duration: 0.6, ease: 'power2.in' }, t);
    tl.to('#ring', { autoAlpha: 0.3, scale: 1.3, duration: 1.4, ease: 'power2.inOut' }, t);
    tl.fromTo('#logo', { autoAlpha: 0, y: 70, scale: 0.94 }, { autoAlpha: 1, y: 40, scale: 1, duration: 1.0, immediateRender: false }, t + 0.45);
    tl.fromTo('#endUrl', { autoAlpha: 0, y: 70 }, { autoAlpha: 1, y: 40, duration: 0.9, immediateRender: false }, t + 0.7);
    tl.to({}, { duration: 2.4 }, t + 1.6);
  }

  /* ------------------------------------------------------------------ */
  /* API de rendu                                                        */
  /* ------------------------------------------------------------------ */
  window.__ready = false;
  window.__seek = async (time) => {
    tl.seek(time, false);
    if (time < 5.3) await syncIntro();
  };
  (async () => {
    await document.fonts.ready;
    await Promise.all(['700 48px Nohemi', '600 48px Nohemi', '400 20px Montserrat', '500 20px Montserrat', '600 20px Montserrat'].map((f) => document.fonts.load(f)));
    await Promise.all([...document.images].map((img) => img.decode().catch(() => {})));
    build();
    await syncIntro();
    window.__duration = tl.duration();
    window.__ready = true;
    if (location.search.includes('play')) {
      const t0 = performance.now();
      const loop = async () => { await window.__seek((performance.now() - t0) / 1000); if (tl.time() < tl.duration()) requestAnimationFrame(loop); };
      loop();
    }
  })();
})();

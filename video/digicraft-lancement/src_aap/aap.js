// Vidéo explicative de l'appel à projets France 2030 « Métaux critiques 3 ».
// Toutes les informations à l'écran viennent du cahier des charges de l'appel à projets.
// Montage : chaque scène occupe le créneau calculé à partir de la voix off (window.CT, généré par audio/build_aap.py) ;
// les apparitions se calent sur les mots prononcés.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon, spring, seeded } = E;
  const el = C.el;
  const INK = '#0B0E13', INK2 = '#131821', PAPER = '#F4F1EA', STONE = '#E9E4D8', CU = '#E08A4E', CU2 = '#F3B37E', ICE = '#86C8FF', STEEL = '#9AA6B2';
  const TX = '#12151B', TX2 = '#5E636B', BD = '#DCD6C9', OK = '#3FBF7F', BAD = '#E0574F';
  const CT = window.CT, SC = CT.scenes;
  const lines = (name) => CT.lines.filter((l) => l.scene === name);
  const L = (name, i) => lines(name)[i] || { start: SC[name][0] + 1 + i * 4, dur: 3.5 };
  const wordAt = (name, w, fb) => { for (const l of lines(name)) { const x = l.words.find((v) => v[0].includes(w)); if (x) return l.start + x[1]; } return fb; };
  const rise = (e, T, t0, d = 0.6, dy = 30) => { const p = P(T, t0, d, Ease.outQuint); S(e, { y: (1 - p) * dy, o: p, blur: (1 - p) * 6 }); return p; };
  const pop = (e, T, t0, k = 210, z = 17, extra = {}) => { const p = spring(T - t0, k, z); S(e, { s: Math.max(0.001, 0.86 + 0.14 * p), o: clamp(p * 1.4), ...extra }); return p; };
  const grad = (a, b) => `background:linear-gradient(100deg, ${a}, ${b});-webkit-background-clip:text;background-clip:text;color:transparent`;
  const cu = (t) => `<span style="${grad(CU, CU2)}">${t}</span>`;
  const ice = (t) => `<span style="color:${ICE}">${t}</span>`;
  const T1 = (p, html, top, fs = 72, col = PAPER, align = 'center', left = 0, width = 1920) => el(p, { left: left + 'px', width: width + 'px', top: top + 'px', textAlign: align, fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: fs + 'px', letterSpacing: '-0.035em', lineHeight: '1.06', color: col }, html);
  const K1 = (p, txt, top, col = CU, align = 'center', left = 0, width = 1920) => el(p, { left: left + 'px', width: width + 'px', top: top + 'px', textAlign: align, fontFamily: 'DM Sans', fontWeight: '700', fontSize: '22px', letterSpacing: '0.26em', color: col }, txt);
  const B1 = (p, html, top, fs = 28, col = '#B9C0C9', align = 'center', left = 0, width = 1920) => el(p, { left: left + 'px', width: width + 'px', top: top + 'px', textAlign: align, fontFamily: 'DM Sans', fontWeight: '500', fontSize: fs + 'px', lineHeight: '1.4', color: col }, html);
  const tile = (p, ic, size, bg, col) => el(p, { width: size + 'px', height: size + 'px', borderRadius: size * 0.28 + 'px', background: bg, display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, size * 0.5, 2.1, col));

  // ---------- Fonds ----------
  function glow(root, d, col) { return el(root, { left: -d / 2 + 'px', top: -d / 2 + 'px', width: d + 'px', height: d + 'px', borderRadius: '50%', background: `radial-gradient(closest-side, ${col}, rgba(0,0,0,0))` }); }
  function bgDark(root, seed = 0) {
    el(root, { inset: '0', background: `radial-gradient(120% 90% at 70% 10%, ${INK2} 0%, ${INK} 60%)` });
    const G = [[glow(root, 1500, 'rgba(224,138,78,0.16)'), 1650, 980], [glow(root, 1300, 'rgba(134,200,255,0.10)'), 180, 120], [glow(root, 1100, 'rgba(224,138,78,0.07)'), 300, 1050]];
    const grid = el(root, { inset: '-80px', backgroundImage: 'linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px)', backgroundSize: '80px 80px',
      maskImage: 'radial-gradient(70% 70% at 50% 45%, #000 20%, transparent 80%)', webkitMaskImage: 'radial-gradient(70% 70% at 50% 45%, #000 20%, transparent 80%)' });
    const rnd = seeded(11 + seed);
    const dust = Array.from({ length: 34 }, () => { const d = 2 + rnd() * 5; const e = el(root, { width: d + 'px', height: d + 'px', borderRadius: '50%', background: rnd() > 0.5 ? 'rgba(243,179,126,0.8)' : 'rgba(220,230,240,0.7)' }); e._x = rnd() * 1920; e._y = rnd() * 1080; e._v = 8 + rnd() * 22; e._a = 0.15 + rnd() * 0.5; e._p = rnd() * 6.28; return e; });
    return (T) => {
      G.forEach(([g, x, y], i) => S(g, { x: x + Math.sin(T * 0.21 + i * 2 + seed) * 120, y: y + Math.cos(T * 0.17 + i + seed) * 70 }));
      S(grid, { x: (T * 6) % 80, y: (T * 4) % 80 });
      dust.forEach((d) => S(d, { x: d._x + Math.sin(T * 0.3 + d._p) * 30, y: ((d._y - T * d._v) % 1080 + 1080) % 1080, o: d._a * (0.6 + 0.4 * Math.sin(T * 1.3 + d._p)) }));
    };
  }
  function bgLight(root, seed = 0) {
    el(root, { inset: '0', background: `radial-gradient(110% 90% at 30% 0%, #FBF9F4 0%, ${PAPER} 55%, #EAE5DA 100%)` });
    const G = [[glow(root, 1400, 'rgba(224,138,78,0.12)'), 1700, 120], [glow(root, 1200, 'rgba(134,200,255,0.16)'), 150, 1000]];
    const grid = el(root, { inset: '-80px', backgroundImage: 'radial-gradient(rgba(18,21,27,0.10) 1.4px, transparent 1.6px)', backgroundSize: '36px 36px',
      maskImage: 'radial-gradient(70% 70% at 50% 50%, #000 10%, transparent 85%)', webkitMaskImage: 'radial-gradient(70% 70% at 50% 50%, #000 10%, transparent 85%)' });
    return (T) => { G.forEach(([g, x, y], i) => S(g, { x: x + Math.sin(T * 0.2 + i * 2 + seed) * 110, y: y + Math.cos(T * 0.16 + i) * 70 })); S(grid, { x: (T * 4) % 36, y: (T * 3) % 36 }); };
  }
  // En-tête de scène : rubrique numérotée et titre.
  function head(root, n, k, t, dark = true, top = 96) {
    const g = el(root, { left: '0', top: '0', width: '1920px' });
    g._k = K1(g, `<span style="opacity:.6">${n}</span>&nbsp;&nbsp;·&nbsp;&nbsp;${k}`, top, dark ? CU : '#B4652F');
    g._t = T1(g, t, top + 46, 64, dark ? PAPER : TX);
    return g;
  }
  const headUp = (g, T, s) => { rise(g._k, T, s + 0.05, 0.6, 14); rise(g._t, T, s + 0.15, 0.7); };
  const card = (p, x, y, w, hh, dark = true) => el(p, { left: x + 'px', top: y + 'px', width: w + 'px', height: hh + 'px', borderRadius: '28px', background: dark ? 'rgba(255,255,255,0.045)' : '#fff',
    border: dark ? '1px solid rgba(255,255,255,0.10)' : `1px solid ${BD}`, boxShadow: dark ? '0 30px 80px rgba(0,0,0,0.35)' : '0 30px 70px rgba(18,21,27,0.10)' });

  // Tuile d'élément, façon tableau périodique.
  function elem(p, sym, name, w = 100, hi = false) {
    const e = el(p, { width: w + 'px', height: w + 'px', borderRadius: '16px', background: hi ? `linear-gradient(150deg, ${CU}, #B45F2A)` : 'rgba(255,255,255,0.06)', border: hi ? 'none' : '1px solid rgba(255,255,255,0.14)' });
    el(e, { left: '0', width: w + 'px', top: w * 0.16 + 'px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: (sym.length > 2 ? w * 0.3 : w * 0.4) + 'px', color: hi ? '#fff' : PAPER, letterSpacing: '-0.02em' }, sym);
    el(e, { left: '4px', width: w - 8 + 'px', top: w * 0.68 + 'px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '500', fontSize: Math.max(11, w * 0.13) + 'px', color: hi ? '#FFE6D2' : '#9EA7B1', whiteSpace: 'nowrap', overflow: 'hidden' }, name);
    return e;
  }

  const OWN = {};

  // 1. Titre
  OWN.intro = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 0);
      const SYM = [['Li', 'Lithium'], ['Co', 'Cobalt'], ['Ni', 'Nickel'], ['Cu', 'Cuivre'], ['Ga', 'Gallium'], ['Ge', 'Germanium'], ['Pt', 'Platine'], ['W', 'Tungstène'], ['Mg', 'Magnésium'], ['Ti', 'Titane'], ['Ta', 'Tantale'], ['Mn', 'Manganèse']];
      const rnd = seeded(5);
      a.tiles = SYM.map(([s, n], i) => { const e = elem(root, s, n, 112, i === 3); const ang = (i / SYM.length) * Math.PI * 2; e._x = 960 + Math.cos(ang) * (700 + rnd() * 120) - 56; e._y = 470 + Math.sin(ang) * (360 + rnd() * 60) - 56; e._r = (rnd() - 0.5) * 30; e._d = i * 0.06; return e; });
      a.k = K1(root, 'FRANCE 2030&nbsp;&nbsp;·&nbsp;&nbsp;APPEL À PROJETS', 330);
      a.t = T1(root, `Métaux ${cu('critiques 3')}`, 390, 132);
      a.s = B1(root, 'Réduire la dépendance de l’industrie française aux métaux critiques', 580, 38, '#C9CFD6');
    },
    update(T, s) {
      const a = this.a; a.bg(T);
      a.tiles.forEach((e) => { const p = spring(T - s - 0.2 - e._d, 120, 15); const fl = Math.sin(T * 0.6 + e._d * 10) * 10; S(e, { x: lerp(960 - 56, e._x, clamp(p)), y: lerp(470 - 56, e._y, clamp(p)) + fl, r: e._r * clamp(p), s: 0.6 + 0.4 * clamp(p), o: clamp(p * 1.5) * 0.9 }); });
      rise(a.k, T, s + 0.5, 0.7, 14); rise(a.t, T, s + 0.7, 0.9, 40); rise(a.s, T, L('intro', 1).start, 0.8, 20);
    },
  };

  // 2. Contexte : les crises
  OWN.contexte = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 1);
      a.hd = head(root, '01', 'CONTEXTE', 'Des crises en série');
      const C4 = [['2018', 'Crise de l’aluminium', 'aluminium'], ['Chine', 'Restrictions à l’export sur les terres rares', 'terres'], ['Covid 19', 'Crise sanitaire mondiale', 'covid'], ['Chine', 'Pénurie de magnésium', 'magnesium']];
      a.line = el(root, { left: '200px', top: '520px', width: '1520px', height: '2px', background: 'linear-gradient(90deg, rgba(224,138,78,0), rgba(224,138,78,0.8), rgba(224,138,78,0))', transformOrigin: 'left center' });
      a.cards = C4.map(([y, t, w], i) => {
        const c = card(root, 170 + i * 400, 330, 360, 380);
        el(c, { left: '50%', top: '172px', width: '22px', height: '22px', marginLeft: '-11px', borderRadius: '50%', background: CU, boxShadow: `0 0 0 8px rgba(224,138,78,0.18)` });
        el(c, { left: '0', width: '360px', top: '48px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '54px', letterSpacing: '-0.03em', color: PAPER }, y);
        el(c, { left: '30px', width: '300px', top: '232px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '28px', lineHeight: '1.3', color: '#C9CFD6' }, t);
        c._w = w; return c;
      });
      a.big = el(root, { left: '0', top: '0', width: '1920px' });
      T1(a.big, `Des risques d’approvisionnement<br>${cu('permanents')}, notamment en métaux.`, 760, 54);
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      S(a.line, { sx: P(T, s + 0.4, 2.5, Ease.inOutCubic), o: 1 });
      a.cards.forEach((c, i) => { const t = wordAt('contexte', c._w, s + 1 + i * 1.4) - 0.25; const p = spring(T - t, 190, 17); S(c, { y: (1 - clamp(p)) * 60, o: clamp(p * 1.4), s: 0.94 + 0.06 * p }); });
      const l1 = L('contexte', 1).start;
      rise(a.big, T, l1 + 0.2, 0.8, 24);
      a.cards.forEach((c) => { const q = P(T, l1, 0.8); c.style.filter = `saturate(${1 - 0.5 * q})`; S(c, { y: -60 * Ease.inOutCubic(q) }); });
      S(a.line, { y: -60 * P(T, l1, 0.8, Ease.inOutCubic) });
    },
  };

  // 3. Objectif
  OWN.objectif = {
    build(root) {
      const a = (this.a = {}); a.bg = bgLight(root, 0);
      a.hd = head(root, '02', 'OBJECTIF', 'Soutenir les meilleurs projets', false);
      const PIL = [['factory', 'Investissement', 'investissement'], ['lightbulb', 'Innovation', 'innovation'], ['rocket', 'Industrialisation rapide sur le territoire', 'industrialisables']];
      a.pil = PIL.map(([ic, t, w], i) => {
        const c = card(root, 260 + i * 480, 300, 440, 230, false);
        const tl = tile(c, ic, 70, '#FFF1E6', '#B4652F'); Object.assign(tl.style, { left: '40px', top: '40px' });
        el(c, { left: '40px', top: '130px', width: '370px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '32px', lineHeight: '1.15', letterSpacing: '-0.02em', color: TX }, t);
        c._w = w; return c;
      });
      // Flux : fournisseurs extra européens -> valeur en France et en Europe.
      a.f = el(root, { left: '0', top: '0', width: '1920px', height: '1080px' });
      const L1 = card(a.f, 260, 600, 560, 190, false), R1 = card(a.f, 1100, 600, 560, 190, false);
      el(L1, { left: '36px', top: '34px', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '20px', letterSpacing: '0.18em', color: TX2 }, 'MOINS DE DÉPENDANCE');
      el(L1, { left: '36px', top: '74px', width: '500px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '34px', lineHeight: '1.15', color: TX }, 'aux fournisseurs extra‑européens');
      el(R1, { left: '36px', top: '34px', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '20px', letterSpacing: '0.18em', color: '#B4652F' }, 'PLUS DE VALEUR');
      el(R1, { left: '36px', top: '74px', width: '500px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '34px', lineHeight: '1.15', color: TX }, 'créée en France et en Europe');
      a.arr = el(a.f, { left: '860px', top: '660px', width: '200px', height: '70px' });
      a.arr.innerHTML = `<svg width="200" height="70" viewBox="0 0 200 70"><path d="M10 35 H170" stroke="${CU}" stroke-width="6" stroke-linecap="round" stroke-dasharray="14 12"/><path d="M160 15 L188 35 L160 55" fill="none" stroke="${CU}" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
      a.down = el(L1, { right: '36px', top: '30px', width: '54px', height: '54px', borderRadius: '27px', background: '#FDECEA', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('trending-down', 28, 2.4, BAD));
      a.up = el(R1, { right: '36px', top: '30px', width: '54px', height: '54px', borderRadius: '27px', background: '#E5F6EC', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon('trending-up', 28, 2.4, OK));
      a.L1 = L1; a.R1 = R1;
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      a.pil.forEach((c, i) => { const t = wordAt('objectif', c._w, s + 1 + i) - 0.3; pop(c, T, t, 200, 17, { y: (1 - P(T, t, 0.6, Ease.outQuint)) * 40 }); });
      const l1 = L('objectif', 1).start;
      const t1 = wordAt('objectif', 'dependance', l1 + 0.8) - 0.3, t2 = wordAt('objectif', 'valeur', l1 + 3) - 0.4;
      rise(a.L1, T, t1, 0.7, 40); rise(a.R1, T, t2, 0.7, 40);
      S(a.arr, { o: P(T, t2 - 0.3, 0.5), x: (1 - P(T, t2 - 0.3, 0.6, Ease.outQuint)) * -40 });
      a.arr.firstChild.firstChild.style.strokeDashoffset = String(-T * 40);
      pop(a.down, T, t1 + 0.5); pop(a.up, T, t2 + 0.5);
    },
  };

  // 4. Thématiques
  OWN.themes = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 2);
      a.hd = head(root, '03', 'THÉMATIQUES', `${cu('Trois')} thématiques visées`);
      const TH = [['recycle', 'Production', 'Métaux primaires et issus du recyclage'], ['cpu', 'Numérisation et automatisation', 'De la production de matières premières'], ['gauge', 'Utilisation des métaux', 'Consommation, volumes, qualités suffisantes et substitutions, pour une même fonction fournie au client']];
      a.c = TH.map(([ic, t, d], i) => {
        const c = card(root, 140 + i * 560, 320, 520, 470);
        el(c, { left: '44px', top: '40px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '120px', lineHeight: '1', letterSpacing: '-0.05em', ...{ background: `linear-gradient(160deg, ${CU2}, ${CU})`, webkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent' } }, String(i + 1));
        const tl = tile(c, ICONS[ic] ? ic : 'layers', 76, 'rgba(224,138,78,0.14)', CU2); Object.assign(tl.style, { right: '44px', left: 'auto', top: '52px' });
        el(c, { left: '44px', top: '210px', width: '430px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '38px', lineHeight: '1.12', letterSpacing: '-0.02em', color: PAPER }, t);
        el(c, { left: '44px', top: '316px', width: '430px', fontFamily: 'DM Sans', fontWeight: '500', fontSize: '25px', lineHeight: '1.4', color: '#AEB6BF' }, d);
        return c;
      });
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      a.c.forEach((c, i) => { const t = L('themes', i + 1).start - 0.2; const p = spring(T - t, 180, 17); S(c, { y: (1 - clamp(p)) * 80, o: clamp(p * 1.4), s: 0.94 + 0.06 * p });
        const cur = T >= t && (i === 2 || T < L('themes', i + 2).start - 0.2); c.style.borderColor = cur ? 'rgba(224,138,78,0.7)' : 'rgba(255,255,255,0.10)'; });
    },
  };

  // 5. Métaux ciblés (annexe 1)
  OWN.metaux = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 3);
      a.hd = head(root, '04', 'MÉTAUX CIBLÉS', 'Bien plus que des métaux', true, 70);
      a.chips = ['Métaux', 'Métalloïdes', 'Minerais', 'Oxydes, sulfates…'].map((t, i) => el(root, { left: [410, 670, 990, 1260][i] + 'px', top: '250px', height: '62px', padding: '0 28px', borderRadius: '31px', background: i ? 'rgba(255,255,255,0.06)' : 'rgba(224,138,78,0.18)', border: `1px solid ${i ? 'rgba(255,255,255,0.16)' : 'rgba(224,138,78,0.6)'}`,
        display: 'flex', alignItems: 'center', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '26px', color: PAPER, whiteSpace: 'nowrap' }, t));
      const G = [
        ['Métaux d’alliage', 'alliage', [['W', 'Tungstène'], ['Re', 'Rhénium'], ['Ta', 'Tantale'], ['V', 'Vanadium'], ['Mo', 'Molybdène'], ['Nb', 'Niobium'], ['Cr', 'Chrome'], ['Mg', 'Magnésium']]],
        ['Hydrogène', 'hydrogene', [['Pt', 'Platine'], ['Ir', 'Iridium'], ['Pd', 'Palladium'], ['Ru', 'Ruthénium'], ['Rh', 'Rhodium'], ['Ti·Nb', 'Alliages']]],
        ['Électronique, réseaux', 'electronique', [['Cu', 'Cuivre'], ['Sn', 'Étain'], ['Be', 'Béryllium'], ['Ga', 'Gallium'], ['Ge', 'Germanium'], ['In', 'Indium'], ['Si', 'Silicium'], ['Ag', 'Argent']]],
        ['Batteries', 'batteries', [['Li', 'Lithium'], ['Co', 'Cobalt'], ['Ni', 'Nickel'], ['C', 'Graphite'], ['Mn', 'Manganèse']]],
        ['Divers', 'terres', [['Sb', 'Antimoine'], ['Hf', 'Hafnium'], ['Zr', 'Zirconium'], ['Zn', 'Zinc'], ['Ti', 'Titane'], ['Al', 'Aluminium'], ['TR', 'Terres rares']]],
      ];
      a.g = G.map(([t, w, items], gi) => {
        const g = el(root, { left: 110 + gi * 346 + 'px', top: '400px', width: '330px' });
        el(g, { left: '0', top: '0', width: '330px', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '20px', letterSpacing: '0.14em', color: CU2, textTransform: 'uppercase', whiteSpace: 'nowrap' }, t);
        g._t = items.map(([sy, n], k) => { const e = elem(g, sy, n, 100, sy === 'TR'); e._x = (k % 3) * 110; e._y = 44 + Math.floor(k / 3) * 110; S(e, { x: e._x, y: e._y }); return e; });
        g._w = w; return g;
      });
      a.note = B1(root, 'Liste indicative, susceptible d’évoluer au cours de l’appel à projets', 830, 22, '#7F8893');
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      a.chips.forEach((c, i) => pop(c, T, s + 0.8 + i * 0.5 + (i ? wordAt('metaux', ['', 'metallo', 'minerais', 'oxydes'][i], 0) * 0 : 0)));
      const ws = ['metallo', 'minerais', 'oxydes'];
      a.chips.forEach((c, i) => { const t = i ? wordAt('metaux', ws[i - 1], s + 1 + i) - 0.2 : s + 0.6; pop(c, T, t); });
      const l1 = L('metaux', 1).start;
      a.g.forEach((g, gi) => { const t = wordAt('metaux', g._w, l1 + 1 + gi * 0.9) - 0.4; g._t.forEach((e, k) => { const p = spring(T - t - k * 0.05, 230, 18); S(e, { x: e._x, y: e._y + (1 - clamp(p)) * 30, s: Math.max(0.001, 0.7 + 0.3 * p), o: clamp(p * 1.5) }); }); g.firstChild.style.opacity = String(P(T, t - 0.1, 0.4)); });
      rise(a.note, T, l1 + 2, 0.7, 10);
    },
  };

  // 6. Chaîne de valeur duale
  OWN.dual = {
    build(root) {
      const a = (this.a = {}); a.bg = bgLight(root, 1);
      a.hd = head(root, '05', 'PRIORITÉ', 'Une chaîne de valeur duale stratégique', false, 80);
      a.hub = el(root, { left: '810px', top: '420px', width: '300px', height: '300px', borderRadius: '150px', background: INK, boxShadow: '0 40px 90px rgba(18,21,27,0.3)', display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', textAlign: 'center' },
        `${icon('shield-check', 56, 2, CU2)}<div style="font-family:'Plus Jakarta Sans';font-weight:800;font-size:30px;line-height:1.1;color:${PAPER};margin-top:12px">Chaîne de valeur<br>duale</div><div style="font-family:'DM Sans';font-weight:600;font-size:19px;color:${CU2};margin-top:8px;letter-spacing:.12em">CIVIL · DÉFENSE</div>`);
      const N = [['zap', 'Énergie', 'energie'], ['train-front', 'Transports et mobilité', 'transports'], ['heart-handshake', 'Santé', 'sante'], ['plane', 'Aéronautique', 'aeronautique'], ['sparkle', 'Spatial', 'spatial'], ['cpu', 'Électronique', 'electronique'], ['shield', 'Défense', 'defense']];
      a.svg = el(root, { inset: '0' });
      a.nodes = N.map(([ic, t, w], i) => {
        const ang = -Math.PI / 2 + (i / N.length) * Math.PI * 2; const cx = 960 + Math.cos(ang) * 470, cy = 570 + Math.sin(ang) * 270;
        const n = el(root, { left: cx - 150 + 'px', top: cy - 40 + 'px', width: '300px', height: '80px', display: 'flex', justifyContent: 'center' });
        el(n, { position: 'relative', left: 'auto', top: 'auto', height: '80px', padding: '0 26px 0 14px', borderRadius: '40px', background: '#fff', border: `1px solid ${BD}`, boxShadow: '0 20px 50px rgba(18,21,27,0.12)', display: 'flex', alignItems: 'center', gap: '14px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '26px', color: TX, whiteSpace: 'nowrap' },
          `<span style="width:52px;height:52px;border-radius:26px;background:#FFF1E6;display:flex;align-items:center;justify-content:center">${icon(ICONS[ic] ? ic : 'circle-check', 26, 2.2, '#B4652F')}</span>${t}`);
        n._w = w; n._cx = cx; n._cy = cy; return n;
      });
      a.svg.innerHTML = `<svg width="1920" height="1080">${a.nodes.map((n) => `<line x1="960" y1="570" x2="${n._cx}" y2="${n._cy}" stroke="${CU}" stroke-width="2.5" stroke-dasharray="8 8" pathLength="1"/>`).join('')}</svg>`;
      a.ln = [...a.svg.querySelectorAll('line')];
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      pop(a.hub, T, wordAt('dual', 'chaine', s + 1) - 0.2, 160, 15);
      const l1 = L('dual', 1).start;
      a.nodes.forEach((n, i) => { const t = wordAt('dual', n._w, l1 + 0.6 + i * 0.6) - 0.25; pop(n, T, t, 220, 17); const ln = a.ln[i]; const q = P(T, t - 0.2, 0.5); ln.style.opacity = String(q * 0.8); ln.style.strokeDashoffset = String(-T * 0.2); });
    },
  };

  // 7. Réduction de la dépendance
  OWN.dependance = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 4);
      a.hd = head(root, '06', 'EXIGENCE CLÉ', `Au moins ${cu('5 points')} de dépendance en moins`, true, 80);
      // Jauge : taux de dépendance pour le métal considéré.
      a.g = card(root, 200, 300, 700, 520);
      el(a.g, { left: '50px', top: '44px', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '20px', letterSpacing: '0.18em', color: STEEL }, 'TAUX DE DÉPENDANCE · MÉTAL CONSIDÉRÉ');
      a.bars = [0, 1].map((k) => { const b = el(a.g, { left: 80 + k * 300 + 'px', bottom: '90px', top: 'auto', width: '190px', height: '300px', borderRadius: '18px 18px 6px 6px', background: k ? `linear-gradient(180deg, ${CU2}, ${CU})` : 'rgba(255,255,255,0.16)', transformOrigin: 'bottom center' });
        el(a.g, { left: 80 + k * 300 + 'px', bottom: '40px', top: 'auto', width: '190px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '22px', color: '#AEB6BF' }, k ? 'Après le projet' : 'Aujourd’hui'); return b; });
      a.badge = el(a.g, { left: '470px', top: '110px', height: '64px', padding: '0 24px', borderRadius: '32px', background: OK, color: '#fff', display: 'flex', alignItems: 'center', gap: '10px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', whiteSpace: 'nowrap' }, `${icon('trending-down', 26, 2.6, '#fff')}≥ 5 points`);
      // Calendrier.
      a.tl = card(root, 960, 300, 760, 520);
      el(a.tl, { left: '50px', top: '44px', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '20px', letterSpacing: '0.18em', color: STEEL }, 'CALENDRIER DE L’IMPACT');
      a.track = el(a.tl, { left: '70px', top: '250px', width: '620px', height: '6px', borderRadius: '3px', background: 'rgba(255,255,255,0.12)' });
      a.fill = el(a.tl, { left: '70px', top: '250px', width: '620px', height: '6px', borderRadius: '3px', background: `linear-gradient(90deg, ${ICE}, ${CU})`, transformOrigin: 'left center' });
      const M = [[0, 'Investissements', 'Fin des investissements', 'investissements'], [0.42, 'Sous 2 ans', 'Premier effet significatif', 'deux'], [1, '5 ans maximum', 'Objectif complet atteint', 'cinq']];
      a.m = M.map(([u, t, d, w]) => { const g = el(a.tl, { left: 70 + u * 620 - 110 + 'px', top: '170px', width: '220px', textAlign: 'center' });
        el(g, { left: '0', top: '0', width: '220px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', color: PAPER, whiteSpace: 'nowrap' }, t);
        el(g, { left: '99px', top: '70px', width: '22px', height: '22px', borderRadius: '11px', background: CU, boxShadow: '0 0 0 8px rgba(224,138,78,0.2)' });
        el(g, { left: '0', top: '120px', width: '220px', fontFamily: 'DM Sans', fontWeight: '500', fontSize: '22px', lineHeight: '1.3', color: '#AEB6BF' }, d);
        g._u = u; g._w = w; return g; });
      a.note = el(a.tl, { left: '50px', top: '420px', width: '660px', fontFamily: 'DM Sans', fontWeight: '500', fontSize: '20px', lineHeight: '1.4', color: '#7F8893' }, 'Délais adaptables pour les projets particulièrement risqués ou à forte part de R&D.');
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      rise(a.g, T, s + 0.6, 0.7, 40);
      const tc = wordAt('dependance', 'cinq', s + 2.5) - 0.4;
      S(a.bars[0], { sy: P(T, s + 0.9, 0.9, Ease.outCubic) });
      S(a.bars[1], { sy: P(T, s + 1.1, 0.9, Ease.outCubic) * lerp(1, 0.78, P(T, tc, 1.2, Ease.inOutCubic)) });
      pop(a.badge, T, tc + 0.7);
      const l1 = L('dependance', 1).start;
      rise(a.tl, T, l1 - 0.2, 0.7, 40);
      const t2 = wordAt('dependance', 'deux', l1 + 1.5), t5 = (() => { for (const l of lines('dependance').slice(1)) { const x = l.words.filter((v) => v[0].includes('cinq')); if (x.length) return l.start + x[x.length - 1][1]; } return l1 + 4; })();
      const u = T < t2 ? lerp(0, 0.42, P(T, l1 + 0.3, t2 - l1 - 0.3, Ease.inOutSine)) : lerp(0.42, 1, P(T, t2 + 0.3, Math.max(0.5, t5 - t2), Ease.inOutSine));
      S(a.fill, { sx: Math.max(0.001, u) });
      a.m.forEach((g, i) => { const t = [l1 + 0.2, t2 - 0.3, t5 - 0.3][i]; rise(g, T, t, 0.6, 20); });
      rise(a.note, T, t5 + 0.6, 0.6, 10);
    },
  };

  // 8. Environnement
  OWN.environnement = {
    build(root) {
      const a = (this.a = {}); a.bg = bgLight(root, 2);
      a.hd = head(root, '07', 'ENVIRONNEMENT', 'Aucun préjudice important', false, 90);
      a.sh = el(root, { left: '760px', top: '300px', width: '400px', height: '200px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '22px' },
        `<div style="width:120px;height:120px;border-radius:36px;background:${INK};display:flex;align-items:center;justify-content:center">${icon('shield-check', 62, 2, CU2)}</div><div style="font-family:'Plus Jakarta Sans';font-weight:800;font-size:64px;color:${TX};letter-spacing:-.03em">DNSH</div>`);
      a.sub = B1(root, 'Principe « Do No Significant Harm » : les projets causant un préjudice important à l’environnement sont exclus', 500, 26, TX2, 'center', 360, 1200);
      const AX = [['thermometer', 'Atténuation du changement climatique'], ['umbrella', 'Adaptation au changement climatique'], ['droplet', 'Ressources aquatiques et marines'], ['recycle', 'Économie circulaire'], ['wind', 'Prévention de la pollution'], ['leaf', 'Biodiversité et écosystèmes']];
      a.k = K1(root, 'LES 6 OBJECTIFS DE LA TAXONOMIE EUROPÉENNE', 630, '#B4652F');
      a.ax = AX.map(([ic, t], i) => { const c = card(root, 140 + i * 280, 690, 260, 170, false); const tl = tile(c, ICONS[ic] ? ic : 'circle-check', 52, '#EEF6FF', '#2F6FB0'); Object.assign(tl.style, { left: '24px', top: '22px' });
        el(c, { left: '24px', top: '88px', width: '220px', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '21px', lineHeight: '1.25', color: TX }, t); return c; });
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      pop(a.sh, T, s + 0.6); rise(a.sub, T, wordAt('environnement', 'prejudice', s + 2) - 0.4, 0.7, 14);
      rise(a.k, T, s + 2.4, 0.6, 10);
      a.ax.forEach((c, i) => pop(c, T, s + 2.6 + i * 0.18, 220, 17));
    },
  };

  // 9. Maturité
  OWN.maturite = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 5);
      a.hd = head(root, '08', 'MATURITÉ', 'Des projets prêts à industrialiser');
      const ST = [['flask-conical', 'R&D amont', 'Stade dépassé'], ['settings', 'Pré‑industrialisation', 'Ingénierie industrielle'], ['factory', 'Production industrielle', 'Commercialisation, industrialisation']];
      a.track = el(root, { left: '260px', top: '505px', width: '1400px', height: '4px', background: 'rgba(255,255,255,0.12)', borderRadius: '2px' });
      a.fill = el(root, { left: '260px', top: '505px', width: '1400px', height: '4px', background: `linear-gradient(90deg, ${STEEL}, ${CU})`, borderRadius: '2px', transformOrigin: 'left center' });
      a.st = ST.map(([ic, t, d], i) => { const g = el(root, { left: 260 + i * 700 - 170 + 'px', top: '340px', width: '340px', textAlign: 'center' });
        g._c = el(g, { left: '110px', top: '0', width: '120px', height: '120px', borderRadius: '60px', background: i ? `linear-gradient(150deg, ${CU}, #B45F2A)` : 'rgba(255,255,255,0.08)', border: i ? 'none' : '2px solid rgba(255,255,255,0.2)', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ICONS[ic] ? ic : 'layers', 54, 2, i ? '#fff' : STEEL));
        el(g, { left: '0', top: '200px', width: '340px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '34px', color: i ? PAPER : '#8A939D', textDecoration: i ? 'none' : 'line-through', textDecorationThickness: '3px' }, t);
        el(g, { left: '0', top: '250px', width: '340px', fontFamily: 'DM Sans', fontWeight: '500', fontSize: '24px', color: '#AEB6BF' }, d); return g; });
      a.chips = [['check-check', 'Ingénierie industrielle éligible'], ['flask-conical', 'R&D éligible dans un projet complet']].map(([ic, t], i) => el(root, { left: [420, 1000][i] + 'px', top: '720px', height: '70px', padding: '0 30px 0 16px', borderRadius: '35px', background: 'rgba(63,191,127,0.12)', border: '1px solid rgba(63,191,127,0.5)', display: 'flex', alignItems: 'center', gap: '14px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '26px', color: PAPER, whiteSpace: 'nowrap' },
        `<span style="width:42px;height:42px;border-radius:21px;background:${OK};display:flex;align-items:center;justify-content:center">${icon('check', 22, 3, '#fff')}</span>${t}`));
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      a.st.forEach((g, i) => { const t = [wordAt('maturite', 'recherche', s + 1), wordAt('maturite', 'preindustrielle', s + 3), wordAt('maturite', 'industrielle', s + 4)][i] - 0.3; rise(g, T, i === 2 ? Math.max(t, wordAt('maturite', 'preindustrielle', s + 3) + 0.4) : t, 0.6, 30); });
      S(a.fill, { sx: Math.max(0.001, P(T, s + 1, 3.5, Ease.inOutSine)) });
      const l1 = L('maturite', 1).start;
      a.chips.forEach((c, i) => pop(c, T, [wordAt('maturite', 'ingenierie', l1 + 0.3), wordAt('maturite', 'developpement', l1 + 2.5)][i] - 0.2));
    },
  };

  // 10. Qui peut candidater
  OWN.qui = {
    build(root) {
      const a = (this.a = {}); a.bg = bgLight(root, 3);
      a.hd = head(root, '09', 'BÉNÉFICIAIRES', 'Qui peut candidater ?', false);
      const C2 = [['building-2', 'Une entreprise seule', 'Immatriculée en France au RCS, quelle que soit sa taille. Entreprises en création éligibles.', 'seule'], ['users', 'Un consortium', 'Jusqu’à 5 partenaires, avec un accord de consortium et un chef de file clairement identifié.', 'consortium']];
      a.c = C2.map(([ic, t, d, w], i) => { const c = card(root, 250 + i * 730, 320, 690, 440, false);
        const tl = tile(c, ic, 96, i ? '#EEF6FF' : '#FFF1E6', i ? '#2F6FB0' : '#B4652F'); Object.assign(tl.style, { left: '52px', top: '52px' });
        el(c, { left: '52px', top: '190px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '46px', letterSpacing: '-0.03em', color: TX }, t);
        el(c, { left: '52px', top: '262px', width: '590px', fontFamily: 'DM Sans', fontWeight: '500', fontSize: '28px', lineHeight: '1.42', color: TX2 }, d);
        c._w = w; return c; });
      a.or = el(root, { left: '920px', top: '500px', width: '80px', height: '80px', borderRadius: '40px', background: INK, color: PAPER, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '26px', zIndex: '3' }, 'ou');
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      a.c.forEach((c, i) => { const t = wordAt('qui', c._w, s + 1 + i * 3) - 0.6; rise(c, T, t, 0.7, 50); });
      pop(a.or, T, wordAt('qui', 'consortium', s + 4) - 0.9);
    },
  };

  // 11. Assiette minimale
  OWN.assiette = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 6);
      a.hd = head(root, '10', 'ÉLIGIBILITÉ', 'Assiette minimale de dépenses', true, 80);
      const R = [['Mono‑partenaire', 'Startup, PME, ETI', '2 M€', 'deux'], ['Mono‑partenaire', 'Grande entreprise', '5 M€', 'cinq'], ['Collaboratif', 'Chef de file startup, PME, ETI', '3 M€', 'trois'], ['Collaboratif', 'Chef de file grande entreprise', '5 M€', 'cinq']];
      a.rows = R.map(([ty, cat, v, w], i) => { const r = el(root, { left: '300px', top: 290 + i * 120 + (i > 1 ? 30 : 0) + 'px', width: '1320px', height: '104px', borderRadius: '22px', background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.10)' });
        el(r, { left: '40px', top: '0', height: '104px', display: 'flex', alignItems: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '20px', letterSpacing: '0.14em', color: i > 1 ? ICE : CU2, textTransform: 'uppercase' }, ty);
        el(r, { left: '380px', top: '0', height: '104px', display: 'flex', alignItems: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '700', fontSize: '32px', color: PAPER }, cat);
        r._v = el(r, { right: '44px', left: 'auto', top: '0', height: '104px', display: 'flex', alignItems: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '52px', letterSpacing: '-0.03em', color: PAPER }, v);
        r._w = w; return r; });
      a.note = el(root, { left: '300px', top: '820px', width: '1320px', display: 'flex', alignItems: 'center', gap: '14px', fontFamily: 'DM Sans', fontWeight: '500', fontSize: '24px', color: '#AEB6BF' }, `${icon('circle-alert', 24, 2.2, CU2)}Aucune aide de moins de 200 000 € ne sera attribuée à une grande entreprise.`);
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      const l1 = L('assiette', 1).start;
      const ts = [wordAt('assiette', 'deux', s + 1.5), (() => { const l = lines('assiette')[0]; const x = l && l.words.find((v) => v[0].includes('cinq')); return x ? l.start + x[1] : s + 3; })(), wordAt('assiette', 'trois', l1 + 1.5), (() => { const l = lines('assiette')[1]; const x = l && l.words.find((v) => v[0].includes('cinq')); return x ? l.start + x[1] : l1 + 3; })()];
      a.rows.forEach((r, i) => { const t = ts[i] - 0.6; rise(r, T, t, 0.6, 30); const cur = T >= t + 0.3 && T < (ts[i + 1] || 1e9) - 0.6 + 0.3; r.style.borderColor = cur ? 'rgba(224,138,78,0.8)' : 'rgba(255,255,255,0.10)'; r.style.background = cur ? 'rgba(224,138,78,0.10)' : 'rgba(255,255,255,0.05)'; pop(r._v, T, ts[i] - 0.1, 240, 16); });
      rise(a.note, T, l1 + L('assiette', 1).dur - 1.2, 0.6, 10);
    },
  };

  // 12. Nature de l'aide
  OWN.aide = {
    build(root) {
      const a = (this.a = {}); a.bg = bgLight(root, 4);
      a.hd = head(root, '11', 'FINANCEMENT', 'Avances remboursables et subvention', false, 90);
      a.bar = el(root, { left: '260px', top: '380px', width: '1400px', height: '150px', borderRadius: '30px', overflow: 'hidden', background: '#E3DED2' });
      a.av = el(a.bar, { left: '0', top: '0', width: '560px', height: '150px', background: INK, transformOrigin: 'left center' });
      a.su = el(a.bar, { left: '560px', top: '0', width: '840px', height: '150px', background: `linear-gradient(100deg, ${CU}, ${CU2})`, transformOrigin: 'left center' });
      a.l1 = el(root, { left: '260px', top: '570px', width: '560px' }); a.l2 = el(root, { left: '820px', top: '570px', width: '840px' });
      el(a.l1, { left: '0', top: '0', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '72px', letterSpacing: '-0.04em', color: TX }, '≥ 40 %');
      el(a.l1, { left: '0', top: '92px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '28px', color: TX2 }, 'd’avances remboursables');
      el(a.l2, { left: '40px', top: '0', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '72px', letterSpacing: '-0.04em', color: '#B4652F' }, '≤ 60 %');
      el(a.l2, { left: '40px', top: '92px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '28px', color: TX2 }, 'de subvention');
      a.note = B1(root, 'Modalités définies pour chaque projet selon sa nature, la taille de l’entreprise et le niveau de risque', 790, 26, TX2);
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      const t1 = wordAt('aide', 'avances', s + 1) - 0.3, t2 = wordAt('aide', 'subvention', s + 3) - 0.3;
      rise(a.bar, T, s + 0.3, 0.6, 20);
      S(a.av, { sx: Math.max(0.001, P(T, t1, 0.9, Ease.outCubic)) }); S(a.su, { sx: Math.max(0.001, P(T, t2, 0.9, Ease.outCubic)) });
      rise(a.l1, T, t1 + 0.3, 0.6, 20); rise(a.l2, T, t2 + 0.3, 0.6, 20); rise(a.note, T, t2 + 1.8, 0.7, 10);
    },
  };

  // 13. Taux d'aide maximaux
  OWN.taux = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 7);
      a.hd = head(root, '12', 'INTENSITÉ DE L’AIDE', 'Des taux selon les travaux et la taille', true, 70);
      const COL = ['Petite entreprise', 'Moyenne entreprise', 'ETI et grande entreprise'];
      const ROW = [['Recherche industrielle', [70, 60, 50], ['70 %', '60 %', '50 %']], ['Développement expérimental', [45, 35, 25], ['45 %', '35 %', '25 %']], ['Investissements en zone AFR', [35, 25, 15], ['30 ou 35 %', '20 ou 25 %', '10 ou 15 %']], ['Investissements hors zone AFR', [20, 10, 0], ['20 %', '10 %', '–']]];
      a.hdr = COL.map((t, j) => el(root, { left: 760 + j * 330 + 'px', top: '260px', width: '300px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '20px', letterSpacing: '0.1em', color: STEEL, textTransform: 'uppercase' }, t));
      a.rows = ROW.map(([t, v, lab], i) => { const r = el(root, { left: '180px', top: 320 + i * 132 + 'px', width: '1560px', height: '116px', borderRadius: '22px', background: 'rgba(255,255,255,0.045)', border: '1px solid rgba(255,255,255,0.09)' });
        el(r, { left: '36px', top: '0', height: '116px', width: '520px', display: 'flex', alignItems: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', color: PAPER }, t);
        r._b = v.map((x, j) => { const c = el(r, { left: 580 + j * 330 + 'px', top: '22px', width: '300px', height: '72px' });
          el(c, { left: '0', top: '50px', width: '300px', height: '10px', borderRadius: '5px', background: 'rgba(255,255,255,0.08)' });
          c._f = el(c, { left: '0', top: '50px', width: 300 * x / 70 + 'px', height: '10px', borderRadius: '5px', background: i === 0 && j === 0 ? `linear-gradient(90deg, ${CU}, ${CU2})` : `linear-gradient(90deg, ${STEEL}, #D6DDE4)`, transformOrigin: 'left center' });
          el(c, { left: '0', top: '0', width: '300px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '34px', color: i === 0 && j === 0 ? CU2 : PAPER, letterSpacing: '-0.02em' }, lab[j]); return c; });
        return r; });
      a.note = B1(root, 'Taux maximaux donnés à titre d’exemple, examinés au cas par cas selon le régime d’aide mobilisé', 870, 22, '#7F8893');
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      a.hdr.forEach((e, j) => rise(e, T, s + 0.5 + j * 0.1, 0.5, 10));
      a.rows.forEach((r, i) => { const t = s + 0.8 + i * 0.35; rise(r, T, t, 0.6, 30); r._b.forEach((c, j) => S(c._f, { sx: Math.max(0.001, P(T, t + 0.3 + j * 0.12, 0.9, Ease.outCubic)) })); });
      const t70 = wordAt('taux', 'soixante', s + 5) - 0.2; const r0 = a.rows[0]; const q = P(T, t70, 0.5);
      r0.style.borderColor = `rgba(224,138,78,${0.09 + 0.7 * q})`; r0.style.background = `rgba(224,138,78,${0.045 + 0.08 * q})`; S(r0._b[0], { s: 1 + 0.08 * Math.sin(Math.PI * P(T, t70, 0.6)) });
      rise(a.note, T, s + 3, 0.6, 10);
    },
  };

  // 14. Critères de sélection
  OWN.criteres = {
    build(root) {
      const a = (this.a = {}); a.bg = bgLight(root, 5);
      a.hd = head(root, '13', 'SÉLECTION', 'Trois axes d’évaluation', false, 80);
      const AX = [['lightbulb', 'Innovation et maturité', ['Niveau d’innovation', 'Crédibilité technique', 'Lancement sous 1 an', 'Moyens financiers suffisants'], 'innovation'],
        ['factory', 'Impact dual, économique et industriel', ['Contribution duale', 'Investissements en France', 'Emplois maintenus et créés', 'Modèle économique solide'], 'dual'],
        ['leaf', 'Impact environnemental', ['Les 6 axes de la taxonomie européenne', 'Performance des procédés', 'Usage des produits en aval'], 'environnemental']];
      a.c = AX.map(([ic, t, items, w], i) => { const c = card(root, 140 + i * 560, 280, 520, 580, false);
        const tl = tile(c, ICONS[ic] ? ic : 'layers', 80, ['#FFF1E6', '#EEF6FF', '#E8F6EE'][i], ['#B4652F', '#2F6FB0', '#1F8A55'][i]); Object.assign(tl.style, { left: '44px', top: '44px' });
        el(c, { left: '44px', top: '150px', width: '440px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '36px', lineHeight: '1.12', letterSpacing: '-0.02em', color: TX }, t);
        c._it = items.map((x, k) => el(c, { left: '44px', top: 270 + k * 66 + 'px', width: '440px', display: 'flex', alignItems: 'center', gap: '14px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '24px', color: TX2 }, `${icon('check', 22, 2.8, ['#B4652F', '#2F6FB0', '#1F8A55'][i])}${x}`));
        c._w = w; return c; });
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      a.c.forEach((c, i) => { const t = wordAt('criteres', c._w, s + 1.5 + i * 2.5) - 0.5; rise(c, T, t, 0.7, 50); c._it.forEach((x, k) => rise(x, T, t + 0.5 + k * 0.2, 0.5, 12)); });
    },
  };

  // 15. Processus
  OWN.process = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 8);
      a.hd = head(root, '14', 'PROCESSUS', 'Du dépôt au versement', true, 80);
      const ST = [['upload', 'Dépôt en ligne', 'Plateforme Bpifrance', 'depose'], ['search', 'Instruction', 'Par Bpifrance, avec des experts', 'instruction'], ['users', 'Comité interministériel', 'Recommandations', 'instruction'], ['badge-check', 'Décision', 'Par le Premier ministre', 'ministre'],
        ['file-pen-line', 'Convention', 'Avec Bpifrance', 'convention'], ['coins', 'Versements', 'En plusieurs tranches', 'tranches']];
      a.st = ST.map(([ic, t, d, w], i) => { const row = i < 4 ? 0 : 1; const x = row ? 560 + (i - 4) * 460 : 170 + i * 410; const y = row ? 600 : 300;
        const c = card(root, x, y, 380, 220);
        const tl = tile(c, ICONS[ic] ? ic : 'circle-check', 64, row ? 'rgba(134,200,255,0.14)' : 'rgba(224,138,78,0.14)', row ? ICE : CU2); Object.assign(tl.style, { left: '34px', top: '34px' });
        el(c, { right: '34px', left: 'auto', top: '40px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '26px', color: 'rgba(255,255,255,0.25)' }, String(i + 1).padStart(2, '0'));
        el(c, { left: '34px', top: '118px', width: '320px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', color: PAPER, whiteSpace: 'nowrap' }, t);
        el(c, { left: '34px', top: '162px', width: '320px', fontFamily: 'DM Sans', fontWeight: '500', fontSize: '22px', color: '#AEB6BF' }, d);
        c._w = w; c._k = i; return c; });
      a.url = el(root, { left: '0', top: '880px', width: '1920px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '26px', color: ICE }, `${icon('globe', 24, 2.2, ICE)}&nbsp;&nbsp;www.picxel.bpifrance.fr`);
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      const l1 = L('process', 1).start;
      const tt = [wordAt('process', 'depose', s + 0.8), wordAt('process', 'instruction', s + 3), wordAt('process', 'instruction', s + 3) + 0.6, wordAt('process', 'ministre', s + 5), wordAt('process', 'convention', l1 + 2.5), wordAt('process', 'tranches', l1 + 1.5)];
      tt[5] = Math.min(tt[5], tt[4]) ; tt[4] = tt[5] + 0.5;
      a.st.forEach((c, i) => pop(c, T, tt[i] - 0.4, 200, 17, { y: (1 - P(T, tt[i] - 0.4, 0.6, Ease.outQuint)) * 40 }));
      rise(a.url, T, tt[0] + 0.4, 0.6, 10);
    },
  };

  // 16. Date de relève
  OWN.date = {
    build(root) {
      const a = (this.a = {}); a.bg = bgLight(root, 6);
      a.hd = head(root, '15', 'CALENDRIER', 'Date de relève', false, 90);
      a.cal = el(root, { left: '660px', top: '290px', width: '600px', height: '440px', borderRadius: '40px', background: '#fff', boxShadow: '0 50px 110px rgba(18,21,27,0.18)', border: `1px solid ${BD}`, overflow: 'hidden' });
      el(a.cal, { left: '0', top: '0', width: '600px', height: '110px', background: `linear-gradient(100deg, ${CU}, ${CU2})`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '34px', letterSpacing: '0.3em', color: '#fff' }, 'FÉVRIER 2027');
      el(a.cal, { left: '0', top: '120px', width: '600px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '220px', letterSpacing: '-0.06em', lineHeight: '1', color: TX }, '23');
      el(a.cal, { left: '0', top: '360px', width: '600px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '34px', color: TX2 }, `${icon('clock', 28, 2.4, '#B4652F')}&nbsp; 12 h 00 (GMT+1)`);
      a.note = el(root, { left: '0', top: '800px', width: '1920px', display: 'flex', justifyContent: 'center' });
      el(a.note, { position: 'relative', left: 'auto', top: 'auto', height: '70px', padding: '0 30px 0 18px', borderRadius: '35px', background: INK, color: PAPER, display: 'flex', alignItems: 'center', gap: '14px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '26px', whiteSpace: 'nowrap' },
        `<span style="width:42px;height:42px;border-radius:21px;background:${CU};display:flex;align-items:center;justify-content:center">${icon('hourglass', 22, 2.4, '#fff')}</span>Instruction jusqu’à épuisement des moyens financiers disponibles`);
    },
    update(T, s) {
      const a = this.a; a.bg(T); headUp(a.hd, T, s);
      const p = spring(T - s - 0.5, 150, 15); S(a.cal, { y: (1 - clamp(p)) * 80, r: (1 - clamp(p)) * -6, o: clamp(p * 1.4), s: 0.9 + 0.1 * p });
      pop(a.note, T, L('date', 1).start - 0.2);
    },
  };

  // 17. Fin
  OWN.fin = {
    build(root) {
      const a = (this.a = {}); a.bg = bgDark(root, 9);
      a.k = K1(root, 'FRANCE 2030&nbsp;&nbsp;·&nbsp;&nbsp;APPEL À PROJETS', 250);
      a.t = T1(root, `Métaux ${cu('critiques 3')}`, 300, 120);
      a.s = B1(root, 'Réduire nos dépendances, développer les filières industrielles d’avenir', 470, 36, '#C9CFD6');
      a.box = el(root, { left: '0', top: '620px', width: '1920px', display: 'flex', justifyContent: 'center', gap: '24px' });
      a.items = [['calendar', 'Relève le 23 février 2027'], ['globe', 'picxel.bpifrance.fr'], ['send', 'aap-france2030@bpifrance.fr']].map(([ic, t]) => el(a.box, { position: 'relative', left: 'auto', top: 'auto', height: '74px', padding: '0 30px 0 16px', borderRadius: '37px', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.14)', display: 'flex', alignItems: 'center', gap: '14px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '26px', color: PAPER, whiteSpace: 'nowrap' },
        `<span style="width:44px;height:44px;border-radius:22px;background:rgba(224,138,78,0.2);display:flex;align-items:center;justify-content:center">${icon(ICONS[ic] ? ic : 'circle-check', 22, 2.2, CU2)}</span>${t}`));
      a.black = el(root, { inset: '0', background: '#000', opacity: '0' });
    },
    update(T, s, e) {
      const a = this.a; a.bg(T);
      rise(a.k, T, s + 0.2, 0.7, 14); rise(a.t, T, s + 0.35, 0.9, 40); rise(a.s, T, wordAt('fin', 'reduire', s + 2) - 0.2, 0.8, 20);
      a.items.forEach((c, i) => pop(c, T, s + 3 + i * 0.3));
      a.black.style.opacity = String(P(T, e - 0.9, 0.9, Ease.inOutSine));
    },
  };

  // ---------- Montage ----------
  const stage = document.getElementById('aap');
  const NAMES = Object.keys(SC);
  const R = {};
  NAMES.forEach((n) => { const r = el(stage, { inset: '0', display: 'none', overflow: 'hidden' }); OWN[n].build(r); R[n] = r; });
  const subs = document.getElementById('subs');
  const sb = el(subs, { left: '0', width: '1920px', bottom: '40px', top: 'auto', display: 'flex', justifyContent: 'center' });
  const sbox = h('div', '', sb);
  Object.assign(sbox.style, { maxWidth: '1500px', padding: '12px 28px 14px', borderRadius: '18px', background: 'rgba(11,14,19,0.86)', color: PAPER, fontFamily: 'DM Sans', fontWeight: '500', fontSize: '36px', lineHeight: '1.3', textAlign: 'center', opacity: '0' });
  window.render = function (t) {
    const i = Math.max(0, NAMES.findIndex((n) => t >= SC[n][0] && t < SC[n][1]));
    const name = t >= CT.END ? NAMES[NAMES.length - 1] : NAMES[i];
    const prev = NAMES[NAMES.indexOf(name) - 1];
    const [s, e] = SC[name];
    const fade = prev ? clamp((t - s) / 0.4) : 1;
    const show = fade < 1 ? [prev, name] : [name];
    NAMES.forEach((n) => { const on = show.includes(n); if ((R[n].style.display !== 'none') !== on) R[n].style.display = on ? 'block' : 'none'; });
    if (fade < 1) { OWN[prev].update(t, SC[prev][0], SC[prev][1]); R[prev].style.zIndex = '1'; }
    OWN[name].update(t, s, e); R[name].style.zIndex = '2'; R[name].style.opacity = String(Ease.inOutSine(fade));
    const cue = CT.subs.find(([a, b]) => t >= a && t < b);
    if (cue) { if (sbox._t !== cue[2]) { sbox.innerHTML = cue[2]; sbox._t = cue[2]; } const f = Math.min(P(t, cue[0], 0.12), 1 - P(t, cue[1] - 0.12, 0.12)); sbox.style.opacity = String(f); }
    else sbox.style.opacity = '0';
  };
  Promise.all([document.fonts.ready]).then(() => {
    window.render(parseFloat(new URLSearchParams(location.search).get('t') || '0'));
    window.READY = true;
  });
})();

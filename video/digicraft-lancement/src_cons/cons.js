// Vidéo explicative YouCraft pour les consultants (environ 3 min).
// Montage : chaque scène occupe le créneau calculé à partir de la voix off (window.CT, généré par audio/build_cons.py).
// - plans « film » : extraits du film français, dont le temps d'origine est étiré pour remplir le créneau ;
// - scènes propres : constat, cas d'usage en applications complètes, sécurité, méthode de vente, signature ;
// - sous-titres incrustés (bandeau Ink, texte Stone), calés mot à mot sur la voix.
(function () {
  const { h, S, P, Ease, lerp, clamp, icon, spring } = E;
  const el = C.el;
  const INK = '#0B0B0F', INK2 = '#16161C', STONE = '#F5F2EA', PAPER = '#FBFAF6', UM = '#1E3BFF', UMD = '#4F6BFF', LIME = '#D7FF3F', MUTED = '#8F8C86';
  const CT = window.CT;
  const SC = CT.scenes;
  const lines = (name) => CT.lines.filter((l) => l.scene === name);
  const wordAt = (name, w, fb) => { for (const l of lines(name)) { const x = l.words.find((v) => v[0].includes(w)); if (x) return l.start + x[1]; } return fb; };
  const title = (p, txt, top = 120, fs = 64, col = INK, al = 'center') => el(p, { left: al === 'center' ? '0' : '120px', width: al === 'center' ? '1920px' : '1680px', top: top + 'px', textAlign: al,
    fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: fs + 'px', letterSpacing: '-0.03em', lineHeight: '1.08', color: col }, txt);
  const rise = (e, T, t0, d = 0.6, dy = 30) => { const p = P(T, t0, d, Ease.outQuint); S(e, { y: (1 - p) * dy, o: p, blur: (1 - p) * 6 }); return p; };

  // ---------- Film : construction de toutes les scènes d'origine dans leur couche ----------
  const film = document.getElementById('film');
  const TL = window.TL, order = Object.keys(TL.scenes);
  const FS = window.SCENES.filter((s) => TL.scenes[s.id] && s.id !== 'kinetic').map((s) => {
    const root = E.h('div', 'scene', film); root.id = 'sc-' + s.id; s.build(root);
    const [t0, t1] = TL.scenes[s.id]; return { ...s, root, t0, t1, visible: false };
  });
  FS.sort((a, b) => order.indexOf(a.id) - order.indexOf(b.id));
  FS.forEach((s, i) => (s.root.style.zIndex = String(10 + i)));
  // Le titre du carrousel de modèles est remplacé par le titre de rubrique de la vidéo.
  [...document.querySelectorAll('#sc-templates div')].filter((d) => d.textContent.startsWith('Partez d’une idée') && d.children.length === 0).forEach((d) => (d.style.visibility = 'hidden'));
  function filmRender(T) {
    for (const s of FS) {
      const vis = T !== null && T >= s.t0 && T < s.t1;
      if (vis !== s.visible) { s.root.style.display = vis ? 'block' : 'none'; s.visible = vis; }
      if (vis) s.update(T);
    }
  }
  // Extraits du film par scène : segments [début, fin] en temps d'origine, étirés pour remplir le créneau.
  const CLIPS = {
    logo: [[9.62, 12.3]], produit: [[12.5, 13.92]], etape1: [[18.35, 22.25]], etape2: [[14.55, 16.65], [22.35, 24.4]], etape2b: [[24.5, 27.15]],
    etape3: [[27.2, 28.45], [28.55, 29.9]], cas_intro: [[16.85, 18.2]], cas_autres: [[37.05, 40.4]], preuves: [[40.65, 45.3]],
  };

  // ---------- Briques des scènes propres ----------
  function browser(parent, x, y, k, url) {
    const A = U.APP, bar = 44, w = A.W * k;
    const r = el(parent, { left: x + 'px', top: y + 'px', width: w + 'px', height: A.H * k + bar + 'px', borderRadius: '14px', overflow: 'hidden', background: '#fff', boxShadow: '0 50px 140px rgba(11,11,15,0.45), 0 8px 24px rgba(11,11,15,0.15)' });
    const b = el(r, { left: '0', top: '0', width: w + 'px', height: bar + 'px', background: '#F3F3F1', borderBottom: '1px solid #E7E6E2' });
    ['#FF5F57', '#FEBC2E', '#28C840'].forEach((c, i) => el(b, { left: 20 + i * 21 + 'px', top: '16px', width: '12px', height: '12px', borderRadius: '6px', background: c }));
    el(b, { left: '50%', top: '8px', transform: 'translateX(-50%)', height: '28px', minWidth: '440px', padding: '0 16px', borderRadius: '8px', background: '#fff', border: '1px solid #E7E6E2', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', fontFamily: 'DM Sans', fontSize: '14px', color: '#6B6B70' }, `${icon('lock', 13, 2.2, '#9A9AA0')}${url}`);
    const body = el(r, { left: '0', top: bar + 'px', width: w + 'px', height: A.H * k + 'px', overflow: 'hidden' });
    return { r, body };
  }
  const chip = (p, txt, ic, dark = true) => el(p, { left: '0', top: '0', height: '58px', padding: '0 24px', borderRadius: '29px', background: dark ? 'rgba(11,11,15,0.9)' : PAPER, color: dark ? STONE : INK,
    display: 'flex', alignItems: 'center', gap: '12px', fontFamily: 'DM Sans', fontWeight: '600', fontSize: '22px', whiteSpace: 'nowrap', boxShadow: '0 18px 50px rgba(11,11,15,0.35)', border: dark ? '1px solid rgba(245,242,234,0.14)' : '1px solid #E6E1D6' },
    `<span style="width:30px;height:30px;border-radius:15px;background:${LIME};display:flex;align-items:center;justify-content:center">${icon(ic, 17, 2.4, INK)}</span>${txt}`);
  const tag = (p, k, t, sub) => {
    const g = el(p, { left: '110px', top: '64px' });
    el(g, { left: '0', top: '0', height: '40px', padding: '0 18px', borderRadius: '20px', background: UM, color: '#fff', display: 'flex', alignItems: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '18px', letterSpacing: '0.04em', whiteSpace: 'nowrap' }, k);
    el(g, { left: '0', top: '52px', padding: '10px 24px 12px', borderRadius: '18px', background: 'rgba(11,11,15,0.9)', boxShadow: '0 20px 60px rgba(11,11,15,0.35)', whiteSpace: 'nowrap', color: t[1] },
      `<div style="font-family:'Plus Jakarta Sans';font-weight:800;font-size:40px;letter-spacing:-0.03em">${t[0]}</div>` + (sub ? `<div style="font-family:'DM Sans';font-size:20px;color:#B5B0A5;margin-top:2px">${sub}</div>` : ''));
    return g;
  };
  let gid = 0;
  function symbol3(parent, size, dark) {
    const L0 = window.YC_LOGO, [bx, by, bw, bh] = L0.sym.block, id = `cs${++gid}`;
    const box = el(parent, { width: size + 'px', height: size + 'px' });
    const grad = `<linearGradient id="${id}" x1="0" y1="0" x2="1" y2="1">${L0.grad.map(([o, c]) => `<stop offset="${o}" stop-color="${c}"/>`).join('')}</linearGradient>`;
    const layer = (inner) => { const s = document.createElementNS('http://www.w3.org/2000/svg', 'svg'); s.setAttribute('viewBox', '0 0 248 247'); s.setAttribute('width', size); s.setAttribute('height', size); s.style.position = 'absolute'; s.style.left = '0'; s.style.top = '0'; s.style.overflow = 'visible'; s.innerHTML = inner; box.appendChild(s); return s; };
    return { box, parts: [layer(`<defs>${grad}</defs><path d="${L0.sym.dome}" fill="url(#${id})"/>`), layer(`<path d="${L0.sym.quarter}" fill="${dark ? STONE : INK}"/>`), layer(`<rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="#E4F53C"/>`)] };
  }

  // ---------- Scènes propres ----------
  const OWN = {};

  // 1. Le constat : des idées, trois besoins, puis le tableur où elles restent bloquées.
  OWN.constat = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 2);
      a.t1 = title(root, 'Vos clients ont tous des <span style="color:#1E3BFF">idées</span>.', 160, 78);
      a.needs = [['users', 'Suivre leurs équipes'], ['map-pin', 'Piloter leurs livraisons'], ['gauge', 'Anticiper leurs pannes']].map(([ic, t], i) => {
        const c = chip(root, t, ic, false); Object.assign(c.style, { left: 190 + i * 540 + 'px', top: '330px', height: '78px', fontSize: '28px', borderRadius: '39px' }); return c;
      });
      // Tableur : en-têtes, cellules remplies à la main, onglets.
      a.sheet = el(root, { left: '360px', top: '470px', width: '1200px', height: '470px', borderRadius: '16px', background: '#fff', boxShadow: '0 40px 110px rgba(11,11,15,0.25)', overflow: 'hidden', fontFamily: 'DM Sans' });
      el(a.sheet, { left: '0', top: '0', right: '0', height: '46px', background: '#1E7145', color: '#fff', display: 'flex', alignItems: 'center', gap: '12px', padding: '0 18px', fontSize: '17px', fontWeight: '600' }, `${icon('sheet', 20, 2, '#fff')}suivi_projets_V12_FINAL_vraiment-final.xlsx`);
      const cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'], r = E.seeded(5);
      for (let i = 0; i < 7; i++) el(a.sheet, { left: 46 + i * 164 + 'px', top: '46px', width: '164px', height: '30px', background: '#F3F3F1', borderRight: '1px solid #E2E2DE', borderBottom: '1px solid #E2E2DE', fontSize: '13px', color: '#6B6B70', display: 'flex', alignItems: 'center', justifyContent: 'center' }, cols[i]);
      const TXT = ['Dossier', 'Responsable', 'Statut', 'Relance', 'Échéance', 'Montant', 'Commentaire'];
      for (let j = 0; j < 11; j++) {
        el(a.sheet, { left: '0', top: 76 + j * 34 + 'px', width: '46px', height: '34px', background: '#F3F3F1', borderBottom: '1px solid #E2E2DE', fontSize: '13px', color: '#6B6B70', display: 'flex', alignItems: 'center', justifyContent: 'center' }, String(j + 1));
        for (let i = 0; i < 7; i++) {
          const v = j === 0 ? TXT[i] : r() < 0.15 ? '' : r() < 0.12 ? '#REF!' : ['à relancer', 'en attente', 'OK ?', 'voir mail', '12/03', '1 240 €', 'Martin'][Math.floor(r() * 7)];
          el(a.sheet, { left: 46 + i * 164 + 'px', top: 76 + j * 34 + 'px', width: '164px', height: '34px', borderRight: '1px solid #EEEEEA', borderBottom: '1px solid #EEEEEA', fontSize: '14px', fontWeight: j === 0 ? '700' : '400',
            color: v === '#REF!' ? '#CF3B3B' : '#2A2A2F', background: j > 0 && (i + j) % 9 === 0 ? '#FFF6C7' : 'transparent', display: 'flex', alignItems: 'center', padding: '0 10px', whiteSpace: 'nowrap', overflow: 'hidden' }, v);
        }
      }
      a.pains = [['timer', 'Manque de temps'], ['banknote', 'Budgets informatiques'], ['hourglass', 'Des mois de développement']].map(([ic, t], i) => {
        const c = chip(root, t, ic, true); Object.assign(c.style, { left: [170, 760, 1270][i] + 'px', top: [520, 420, 610][i] + 'px' }); return c;
      });
    },
    update(T, s, e) {
      const a = this.a, L = lines('constat');
      a.bg(T);
      rise(a.t1, T, s + 0.2, 0.7);
      a.needs.forEach((c, i) => rise(c, T, L[1].start + 0.2 + i * 0.6, 0.6, 24));
      const sp = P(T, L[2].start - 0.2, 0.8, Ease.outCubic);
      S(a.sheet, { y: (1 - sp) * 120, o: sp, r: lerp(4, -1.5, sp) });
      a.needs.forEach((c) => (c.style.opacity = String(c.style.opacity * (1 - 0.65 * sp))));
      a.pains.forEach((c, i) => { const t = wordAt('constat', ['temps', 'budgets', 'mois'][i], L[2].start + 0.5 + i); const p = spring(T - t + 0.1, 220, 16); S(c, { s: Math.max(0.001, p), o: clamp(p), r: [-4, 3, -2][i] }); });
      S(a.t1, { o: P(T, s + 0.2, 0.7) * (1 - 0.6 * sp), y: -sp * 40 });
    },
  };

  // 2. Cas d'usage en applications complètes : travelling vers les zones clés et légendes synchronisées.
  function caseScene(name, make, url, kTag, ttl, sub, cam, callouts) {
    return {
      build(root) {
        const a = (this.a = {});
        a.bg = C.navyBg(root, name.length);
        a.cam = el(root, { inset: '0', transformOrigin: '0 0' });
        const K = 0.72;
        a.B = browser(a.cam, 960 - (U.APP.W * K) / 2, 250, K, url);
        a.app = make(a.B.body); a.app.root.style.transform = `scale(${K})`;
        a.K = K; a.ox = 960 - (U.APP.W * K) / 2; a.oy = 250 + 44;
        a.tag = tag(root, kTag, [ttl, STONE], sub);
        a.co = callouts.map(([t, ic]) => chip(root, t, ic, true));
      },
      update(T, s, e) {
        const a = this.a, L = lines(name);
        a.bg(T);
        rise(a.tag, T, s + 0.1, 0.7, 20);
        const wp = P(T, s + 0.15, 0.9, Ease.outCubic);
        // Caméra : plan large puis zoom sur la zone citée par la deuxième phrase ([x, y, zoom] en coordonnées de l'application).
        const z1 = Ease.inOutSine(clamp((T - (L[1] ? L[1].start - 0.4 : s + 3)) / 1.4));
        const [cx, cy, zm] = cam;
        // Départ : vue d'ensemble centrée ; arrivée : le point (cx, cy) de l'application ramené au centre, zoomé.
        const Z0 = lerp(0.94, 1, wp), Z = lerp(Z0, zm, z1);
        const fx = a.ox + cx * a.K, fy = a.oy + cy * a.K;
        const tx = lerp(960 * (1 - Z0), 960 - fx * zm, z1), ty = lerp(650 * (1 - Z0), 620 - fy * zm, z1) + (1 - wp) * 80;
        a.cam.style.transform = `translate(${tx.toFixed(1)}px,${ty.toFixed(1)}px) scale(${Z.toFixed(4)})`;
        a.cam.style.opacity = String(wp);
        a.app.update(T, s + 0.6);
        // Légendes : apparaissent sur le mot cité.
        a.co.forEach((c, i) => {
          const [, , w, x, y] = callouts[i];
          const t = wordAt(name, w, (L[1] ? L[1].start : s) + 0.6 + i * 0.8);
          const p = spring(T - t, 220, 17);
          S(c, { x, y: y + (1 - clamp(p)) * 20, s: Math.max(0.001, 0.8 + 0.2 * p), o: clamp(p * 1.4) });
        });
      },
    };
  }
  OWN.cas_rh = caseScene('cas_rh', (b) => U.APP.people(b), 'rh.northwind.youcraft.fr/integration', 'RESSOURCES HUMAINES', 'Accueil des nouveaux arrivants', 'Parcours, tâches et parrainage, suivis en direct',
    [1000, 520, 1.45], [['Un parcours par recrue', 'user-check', 'parcours', 1180, 300], ['Tâches et parrain', 'list-checks', 'parrain', 1240, 400], ['Suivi en direct', 'activity', 'direct', 1300, 500]]);
  OWN.cas_log = caseScene('cas_log', (b) => U.APP.fleet(b), 'tour-de-controle.northwind.youcraft.fr/carte', 'LOGISTIQUE', 'Tour de contrôle des livraisons', 'Camions en temps réel, retards détectés, clients prévenus',
    [700, 480, 1.4], [['Suivi en temps réel', 'map-pin', 'reel', 1200, 300], ['Retards détectés', 'circle-alert', 'retards', 1260, 400], ['Client prévenu automatiquement', 'bell-ring', 'prevenu', 1150, 500]]);
  OWN.cas_ind = caseScene('cas_ind', (b) => U.APP.plants(b), 'supervision.northwind.youcraft.fr/capteurs/PR-LY-004', 'INDUSTRIE', 'Surveillance des machines', 'Alertes avant la panne, ordres de travail assignés',
    [700, 620, 1.35], [['Alerte avant la panne', 'activity', 'panne', 1220, 300], ['Ordre de travail créé', 'clipboard-check', 'cree', 1240, 400], ['Assigné au bon technicien', 'user-check', 'technicien', 1180, 500]]);

  // 3. Sécurité : trois garanties qui apparaissent quand elles sont citées.
  OWN.securite = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 7);
      a.t = title(root, 'Sécurité <span style="color:#D7FF3F">intégrée</span>.', 170, 72, STONE);
      a.cards = [['lock', 'Connexion sécurisée', 'Authentification des utilisateurs'], ['shield-check', 'Rôles et droits d’accès', 'Chacun voit ce qui le concerne'], ['server', 'Hébergé en Europe', 'Données hébergées en Europe']].map(([ic, t, d], i) => {
        const c = el(root, { left: 180 + i * 540 + 'px', top: '380px', width: '480px', height: '330px', borderRadius: '28px', background: 'rgba(245,242,234,0.05)', border: '1px solid rgba(245,242,234,0.14)', fontFamily: 'DM Sans', color: STONE });
        el(c, { left: '40px', top: '44px', width: '84px', height: '84px', borderRadius: '24px', background: i === 1 ? LIME : UM, display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 44, 2.2, i === 1 ? INK : '#fff'));
        el(c, { left: '40px', top: '164px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '34px', letterSpacing: '-0.02em', width: '420px' }, t);
        el(c, { left: '40px', top: '222px', fontSize: '22px', color: '#B5B0A5', width: '400px', lineHeight: '1.35' }, d);
        return c;
      });
    },
    update(T, s) {
      const a = this.a; a.bg(T);
      rise(a.t, T, s + 0.1, 0.7);
      a.cards.forEach((c, i) => { const t = wordAt('securite', ['connexion', 'roles', 'hebergees'][i], s + 1 + i); const p = spring(T - t + 0.15, 200, 17); S(c, { y: (1 - clamp(p)) * 60, s: 0.92 + 0.08 * p, o: clamp(p * 1.3) }); });
    },
  };

  // 4. Comment le vendre : titre, puis parcours en trois étapes mis en avant tour à tour.
  OWN.vendre = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 4);
      a.k = el(root, { left: '0', width: '1920px', top: '330px', textAlign: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '26px', letterSpacing: '0.3em', color: UM }, 'POUR VOUS, CONSULTANTS');
      a.t = title(root, 'Une vraie <span style="color:#1E3BFF">opportunité</span>.', 400, 110);
      a.sym = symbol3(root, 180, false); Object.assign(a.sym.box.style, { left: '870px', top: '640px' });
    },
    update(T, s) {
      const a = this.a; a.bg(T);
      rise(a.k, T, s + 0.1, 0.6, 16); rise(a.t, T, s + 0.25, 0.8, 40);
      a.sym.parts.forEach((p, i) => { const k = spring(T - s - 0.6 - i * 0.1, 200, 17); S(p, { x: [[-1, -1], [-0.4, 1], [1, -0.4]][i][0] * (1 - k) * 160, y: [[-1, -1], [-0.4, 1], [1, -0.4]][i][1] * (1 - k) * 160, o: clamp((T - s - 0.6 - i * 0.1) * 5) }); });
    },
  };
  OWN.methode = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.warmBg(root, 6);
      a.t = title(root, 'Comment le proposer à vos clients', 110, 58);
      const ST = [['1', 'Repérer', 'Les processus qui reposent sur des tableurs, des courriels ou des ressaisies.', ['sheet', 'message-square', 'history']],
        ['2', 'Atelier', 'En une séance, une première application construite avec le client, sur son propre cas.', ['presentation', 'wand-sparkles', 'users']],
        ['3', 'Pilote, puis déploiement', 'Mesurer le temps gagné, puis étendre à d’autres équipes.', ['target', 'trending-up', 'rocket']]];
      a.cards = ST.map(([n, t, d, ics], i) => {
        const c = el(root, { left: 140 + i * 560 + 'px', top: '300px', width: '520px', height: '560px', borderRadius: '32px', background: '#fff', border: '1px solid #E6E1D6', boxShadow: '0 30px 80px rgba(11,11,15,0.12)', fontFamily: 'DM Sans', color: INK, overflow: 'hidden' });
        const hd = el(c, { left: '0', top: '0', right: '0', height: '190px', background: '#F1EEE7' });
        ics.forEach((ic, j) => el(hd, { left: 60 + j * 140 + 'px', top: '50px', width: '96px', height: '96px', borderRadius: '28px', background: j === 1 ? UM : '#fff', boxShadow: '0 12px 30px rgba(11,11,15,0.10)', display: 'flex', alignItems: 'center', justifyContent: 'center' }, icon(ic, 44, 2, j === 1 ? '#fff' : UM)));
        el(c, { left: '44px', top: '232px', width: '64px', height: '64px', borderRadius: '32px', background: INK, color: LIME, fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '30px', display: 'flex', alignItems: 'center', justifyContent: 'center' }, n);
        el(c, { left: '44px', top: '322px', width: '440px', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '40px', letterSpacing: '-0.02em', lineHeight: '1.1' }, t);
        el(c, { left: '44px', top: '420px', width: '432px', fontSize: '24px', lineHeight: '1.4', color: '#55534E' }, d);
        return c;
      });
    },
    update(T, s) {
      const a = this.a; a.bg(T);
      rise(a.t, T, s + 0.1, 0.6, 20);
      const starts = ['vendre1', 'vendre2', 'vendre3'].map((n) => (lines(n)[0] || { start: s }).start);
      a.cards.forEach((c, i) => {
        const p = spring(T - starts[i] + 0.25, 200, 18);
        const cur = T >= starts[i] - 0.25 && (i === 2 || T < starts[i + 1] - 0.25);
        const act = P(T, starts[i] - 0.25, 0.4) * (i < 2 ? 1 - P(T, starts[i + 1] - 0.25, 0.4) : 1);
        S(c, { y: (1 - clamp(p)) * 80 - act * 18, s: 0.97 + 0.03 * act, o: clamp(p * 1.4) * (cur || T > starts[i] ? 1 : 0) * (0.55 + 0.45 * (cur ? 1 : 0.6)) });
        c.style.borderColor = act > 0.5 ? UM : '#E6E1D6';
        c.style.boxShadow = act > 0.5 ? '0 40px 100px rgba(30,59,255,0.22)' : '0 30px 80px rgba(11,11,15,0.12)';
      });
    },
  };

  // 5. Signature.
  OWN.fin = {
    build(root) {
      const a = (this.a = {});
      a.bg = C.navyBg(root, 9);
      a.line = el(root, { left: '0', width: '1920px', top: '330px', display: 'flex', justifyContent: 'center' });
      a.L = C.ycLogo(a.line, 190, true);
      a.t1 = el(root, { left: '0', width: '1920px', top: '600px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '700', fontSize: '48px', letterSpacing: '-0.02em', color: STONE }, 'Les idées de vos clients deviennent des <span style="color:#4F6BFF">applications</span>.');
      a.t2 = el(root, { left: '0', width: '1920px', top: '690px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '700', fontSize: '48px', letterSpacing: '-0.02em', color: STONE }, 'Donnons-leur <span style="color:#D7FF3F">vie</span>, ensemble.');
      a.black = el(root, { inset: '0', background: '#000', opacity: '0' });
    },
    update(T, s, e) {
      const a = this.a, L = lines('fin'); a.bg(T);
      C.ycLogoAnim(a.L, T, s + 0.1);
      a.L.glyphs.forEach((c, i) => { const p = P(T, s + 0.5 + i * 0.035, 0.7, Ease.outQuint); S(c, { y: (1 - p) * 50, o: p }); });
      rise(a.t1, T, (L[0] || { start: s }).start + 0.6, 0.8);
      rise(a.t2, T, (L[1] || { start: s }).start, 0.8);
      a.black.style.opacity = String(P(T, e - 0.9, 0.9, Ease.inOutSine));
    },
  };
  // Titres sur les plans film (étape, rubrique).
  const OVER = {
    etape1: ['ÉTAPE 1', 'Décrire'], etape2: ['ÉTAPE 2', 'YouCraft construit tout'], etape2b: ['ÉTAPE 2', 'Modifier en langage naturel'], etape3: ['ÉTAPE 3', 'Publier'],
    cas_intro: ['CAS D’USAGE', 'Des exemples concrets'], cas_autres: ['ET AUSSI', 'Un outil pour chaque métier'], preuves: ['DÉJÀ ADOPTÉ', '750 utilisateurs · 350 applications'], produit: ['YOUCRAFT', 'Créer des applications par l’intelligence artificielle'],
  };

  // ---------- Montage ----------
  const cons = document.getElementById('cons');
  const NAMES = Object.keys(SC);
  const OWNR = {};
  NAMES.forEach((n) => {
    if (OWN[n]) { const r = el(cons, { inset: '0', display: 'none' }); OWN[n].build(r); OWNR[n] = r; }
  });
  // « vendre1 » à « vendre3 » partagent la scène « méthode ».
  const methodeWin = () => [SC.vendre1[0], SC.vendre3[1]];
  { const r = el(cons, { inset: '0', display: 'none' }); OWN.methode.build(r); OWNR.methode = r; }
  const over = el(cons, { left: '0', top: '0', zIndex: '5000' });
  const ov = { k: el(over, { left: '96px', top: '60px', height: '40px', padding: '0 18px', borderRadius: '20px', background: UM, color: '#fff', display: 'flex', alignItems: 'center', fontFamily: 'DM Sans', fontWeight: '700', fontSize: '18px', letterSpacing: '0.06em', whiteSpace: 'nowrap' }),
    t: el(over, { left: '96px', top: '112px', height: '64px', padding: '0 26px', borderRadius: '18px', background: 'rgba(11,11,15,0.88)', color: STONE, display: 'flex', alignItems: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '34px', letterSpacing: '-0.02em', whiteSpace: 'nowrap', boxShadow: '0 20px 60px rgba(11,11,15,0.35)' }) };

  // Sous-titres : deux lignes au plus, bandeau Ink arrondi ajusté au texte.
  const subs = document.getElementById('subs');
  const sb = el(subs, { left: '0', width: '1920px', bottom: '58px', top: 'auto', display: 'flex', justifyContent: 'center' });
  const sbox = h('div', '', sb);
  Object.assign(sbox.style, { maxWidth: '1500px', padding: '14px 30px 16px', borderRadius: '18px', background: 'rgba(11,11,15,0.86)', color: STONE, fontFamily: 'DM Sans', fontWeight: '500', fontSize: '40px', lineHeight: '1.3', textAlign: 'center', opacity: '0' });

  let curOwn = null;
  window.render = function (t) {
    // Scène active.
    let name = NAMES.find((n) => t >= SC[n][0] && t < SC[n][1]) || NAMES[NAMES.length - 1];
    const [s, e] = SC[name];
    let own = OWN[name] ? name : null;
    if (/^vendre[123]$/.test(name)) own = 'methode';
    if (own !== curOwn) { if (curOwn) OWNR[curOwn].style.display = 'none'; if (own) OWNR[own].style.display = 'block'; curOwn = own; }
    if (own === 'methode') { const [ms, me] = methodeWin(); OWN.methode.update(t, ms, me); }
    else if (own) OWN[own].update(t, s, e);
    // Plan film : temps d'origine étiré sur le créneau, segments enchaînés au prorata de leur durée.
    const clip = CLIPS[name];
    if (clip) {
      const tot = clip.reduce((x, [a, b]) => x + b - a, 0);
      let u = clamp((t - s) / (e - s)) * tot, T = clip[clip.length - 1][1];
      for (const [a, b] of clip) { if (u <= b - a) { T = a + u; break; } u -= b - a; }
      film.style.display = 'block'; filmRender(T); if (window.FX) window.FX.update(T);
    } else { film.style.display = 'none'; filmRender(null); if (window.FX) window.FX.update(-100); }
    // Titre de rubrique sur les plans film.
    const o = OVER[name];
    if (o && clip) { ov.k.textContent = o[0]; ov.t.textContent = o[1]; const p = P(t, s + 0.25, 0.5, Ease.outQuint) * (1 - P(t, e - 0.35, 0.3)); S(ov.k, { x: (1 - p) * -30, o: p }); S(ov.t, { x: (1 - p) * -40, o: p }); }
    else { ov.k.style.opacity = '0'; ov.t.style.opacity = '0'; }
    // Sous-titre courant.
    const cue = CT.subs.find(([a, b]) => t >= a && t < b);
    if (cue) { if (sbox._t !== cue[2]) { sbox.innerHTML = cue[2]; sbox._t = cue[2]; } const f = Math.min(P(t, cue[0], 0.12), 1 - P(t, cue[1] - 0.12, 0.12)); sbox.style.opacity = String(f); }
    else sbox.style.opacity = '0';
  };
  Promise.all([document.fonts.ready, ...[...document.images].map((im) => (im.complete ? 1 : new Promise((r) => { im.onload = r; im.onerror = r; })))]).then(() => {
    window.render(parseFloat(new URLSearchParams(location.search).get('t') || '0'));
    window.READY = true;
  });
})();

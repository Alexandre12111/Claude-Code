// Montage V3 : Gaspard le castor. Repères calés sur la voix d'Alexandre : P(id) début de réplique, F(id) fin, M(id, k) début du mot k.
function jetons(st) {
  const out = [];
  for (const b of st.replace(/\*/g, '').split(/\s+/)) { if (/^[?!:;.,…]+$/.test(b) && out.length) out[out.length - 1] += ' ' + b; else if (b) out.push(b); }
  return out;
}
function construireCalage(script, cal) {
  const ph = {};
  script.phrases.forEach((p, i) => {
    let debut, fin, mots;
    if (cal && cal.phrases[p.id]) ({ debut, fin, mots } = cal.phrases[p.id]);
    else { debut = p.debut_prevu; fin = (script.phrases[i + 1] ? script.phrases[i + 1].debut_prevu : debut + 3) - .45; }
    const toks = jetons(p.sous_titre);
    if (!mots || mots.length !== toks.length) {
      const poids = toks.map(m => m.length + 2), tot = poids.reduce((a, b) => a + b, 0);
      let c = debut; mots = toks.map((m, k) => { const d = (fin - debut) * poids[k] / tot; const o = { debut: c, fin: c + d }; c += d; return o; });
    }
    ph[p.id] = { debut, fin, mots };
  });
  const der = ph[script.phrases[script.phrases.length - 1].id];
  return { phrases: ph, duree: (cal && cal.duree) || der.fin + .5 + script.fin.duree, finTexte: der.fin + .4 };
}
const P = id => CAL.phrases[id].debut, F = id => CAL.phrases[id].fin;
const M = (id, k) => CAL.phrases[id].mots[Math.min(k, CAL.phrases[id].mots.length - 1)].debut;
const L = {};
function calque(id, html) { const g = el($('svg'), html); g.id = id; L[id] = g; return g; }
function camera(id, cx, cy, z, r = 0, sh = { x: 0, y: 0, r: 0 }) { tr(id, `translate(${540 + sh.x},${960 + sh.y}) rotate(${r + sh.r}) scale(${z}) translate(${-cx},${-cy})`); }
const PERS = {};
// Lumière de scène : dégradé chaud (clé) + vignette froide, en espace écran
function lumiere(cle = '#ffd59a', froid = '#1b2a5a', force = .35) {
  const id = 'lu' + Math.round(Math.random() * 1e6);
  return `<defs><radialGradient id="${id}a" cx=".85" cy=".1" r="1.1"><stop offset="0" stop-color="${cle}" stop-opacity="${force}"/><stop offset=".6" stop-color="${cle}" stop-opacity="0"/></radialGradient>
    <radialGradient id="${id}b" cx=".5" cy=".45" r=".75"><stop offset=".6" stop-color="${froid}" stop-opacity="0"/><stop offset="1" stop-color="${froid}" stop-opacity=".38"/></radialGradient></defs>
    <rect width="1080" height="1920" fill="url(#${id}a)" style="mix-blend-mode:soft-light;pointer-events:none"/><rect width="1080" height="1920" fill="url(#${id}b)" style="pointer-events:none"/>`;
}

// ======================================================================================
function construireScenes() {
  $('defs').innerHTML = `
    <clipPath id="clipIris"><circle id="irisC" cx="540" cy="760" r="0"/></clipPath>
    <filter id="desature"><feColorMatrix id="desatM" type="saturate" values="1"/></filter>
    <filter id="flouAvant" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="14"/></filter>
    <filter id="flouFond" x="-5%" y="-5%" width="110%" height="110%"><feGaussianBlur stdDeviation="5"/></filter>
    <linearGradient id="gCiel" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#6cc0f2"/><stop offset=".7" stop-color="#bfe3f7"/><stop offset="1" stop-color="#ffe2b8"/></linearGradient>
    <linearGradient id="gCielSoir" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff9f6b"/><stop offset=".6" stop-color="#ffd29a"/><stop offset="1" stop-color="#fff0d2"/></linearGradient>
    <radialGradient id="gLampe" cx=".5" cy="0" r="1"><stop offset="0" stop-color="#ffd98a" stop-opacity=".9"/><stop offset=".6" stop-color="#ffc35c" stop-opacity=".18"/><stop offset="1" stop-color="#ffc35c" stop-opacity="0"/></radialGradient>
    <radialGradient id="gHalo" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#ffe7a3" stop-opacity=".95"/><stop offset="1" stop-color="#ffe7a3" stop-opacity="0"/></radialGradient>
    <linearGradient id="gEau" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5fc3e8"/><stop offset="1" stop-color="#2f86c8"/></linearGradient>
    <linearGradient id="gCarte" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffe9a8"/><stop offset=".5" stop-color="#f2c457"/><stop offset="1" stop-color="#d49a2a"/></linearGradient>`;

  // ---------------- 1. ACCROCHE ----------------
  let planche = '';
  for (let i = 0; i < 4; i++) planche += `<rect x="-300" y="${-400 + i * 700}" width="1700" height="680" rx="20" fill="${['#e9a75e', '#e19c52', '#eaab62', '#df9a4f'][i]}"/>
    ${[0, 1, 2, 3, 4].map(k => `<path d="M-300,${-340 + i * 700 + k * 130} C200,${-330 + i * 700 + k * 130} 700,${-360 + i * 700 + k * 130} 1400,${-338 + i * 700 + k * 130}" stroke="#c9843f" stroke-width="7" fill="none" stroke-linecap="round" opacity=".45"/>`).join('')}
    <circle cx="${200 + i * 230}" cy="${-150 + i * 700}" r="22" fill="none" stroke="#c9843f" stroke-width="7" opacity=".45"/><rect x="-300" y="${276 + i * 700}" width="1700" height="22" fill="#9a5a26" opacity=".6"/>`;
  const splats = [[150, 280, 70], [900, 420, 50], [140, 1560, 90], [930, 1690, 60], [800, 1230, 40], [240, 850, 34]].map(([x, y, r]) => `<g transform="translate(${x},${y})"><circle r="${r}" fill="#f4efe6"/><circle cx="${r * .9}" cy="${-r * .4}" r="${r * .32}" fill="#f4efe6"/><circle cx="${-r * .7}" cy="${r * .8}" r="${r * .25}" fill="#f4efe6"/><circle cx="${r * .3}" cy="${r * 1.2}" r="${r * .15}" fill="#f4efe6"/></g>`).join('');
  const rouleau = Array.from({ length: 15 }, (_, i) => `<text x="0" y="${40 + i * 150}" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="130" fill="#fff">${i}</text>`).join('');
  calque('hook', `<g id="hk-cam">${planche}${splats}
    <g transform="translate(870,1500) rotate(-20)"><rect x="-26" y="-190" width="52" height="150" rx="22" fill="#8a5432"/><path d="M-160,-50 L160,-50 L150,40 L-150,40 Z" fill="#cfd6de"/><path d="M-160,-50 L160,-50 L158,-38 L-158,-38 Z" fill="#fff"/><path d="M-120,26 C-40,46 60,16 140,34 L150,40 L-150,40 Z" fill="#f4efe6"/></g>
    <g transform="translate(170,1180) rotate(14)"><path d="M-60,-80 L60,-80 L52,60 C50,72 -50,72 -52,60 Z" fill="#fff" stroke="#3a2a22" stroke-width="8"/><path d="M60,-50 C96,-50 96,10 56,10" stroke="#3a2a22" stroke-width="8" fill="none"/><ellipse cx="0" cy="-78" rx="58" ry="14" fill="#6b3b20"/></g>
    <ellipse id="hk-ombre" cx="540" cy="1340" rx="250" ry="60" fill="#5a2e10" opacity=".28"/>
    <g id="hk-tel"><g id="hk-telIn">
      <rect x="-210" y="-420" width="420" height="840" rx="74" fill="#0f1116"/><rect x="-200" y="-410" width="400" height="820" rx="66" fill="#2a2e38"/><rect x="-190" y="-400" width="380" height="800" rx="58" fill="#1c2130"/>
      <rect x="-64" y="-386" width="128" height="36" rx="18" fill="#000"/>
      <text id="hk-heure" x="0" y="-210" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="124" fill="#fff">8:59</text>
      <text x="0" y="-158" text-anchor="middle" font-family="Geist" font-size="30" fill="#aab3c5">mardi 18 novembre</text>
      <g transform="translate(0,30)"><rect x="-150" y="-80" width="300" height="160" rx="40" fill="#ff4d4f"/><clipPath id="hk-clipR"><rect x="-150" y="-80" width="300" height="160" rx="40"/></clipPath>
        <g clip-path="url(#hk-clipR)"><g id="hk-rouleau">${rouleau}</g></g><rect x="-150" y="-80" width="300" height="160" rx="40" fill="none" stroke="#fff" stroke-width="8"/></g>
      <text x="0" y="170" text-anchor="middle" font-family="Geist" font-weight="700" font-size="40" fill="#fff">appels manqués</text>
      <g transform="translate(-110,300)"><circle r="56" fill="#ff4d4f"/><path d="M-24,4 c16,-16 32,-16 48,0" stroke="#fff" stroke-width="10" fill="none" stroke-linecap="round"/></g>
      <g transform="translate(110,300)"><circle r="56" fill="#22c55e"/><path d="M-24,-4 c16,16 32,16 48,0" stroke="#fff" stroke-width="10" fill="none" stroke-linecap="round"/></g>
      <path d="M-180,-390 L-60,-390 L-180,-100 Z" fill="#fff" opacity=".06"/>
    </g></g>
    <g id="hk-ga"></g>
    <g id="hk-fx"></g></g>${lumiere('#ffd59a', '#3a1a06', .4)}`);
  PERS.gh = new Gaspard('gh'); $('hk-ga').innerHTML = PERS.gh.svg();

  // ---------------- 2. EXTÉRIEUR (échafaudage) ----------------
  const fenetres = [];
  for (let r = 0; r < 9; r++) for (let c = 0; c < 5; c++) {
    const x = -220 + c * 320, y = 260 + r * 330;
    fenetres.push(`<g transform="translate(${x},${y})"><rect x="-14" y="-14" width="148" height="238" rx="10" fill="#ead2a6"/><path d="M0,60 C0,10 120,10 120,60 V210 H0 Z" fill="#4c6fa8"/><path d="M14,64 C16,34 60,26 70,30 L20,150 Z" fill="#fff" opacity=".22"/><path d="M60,22 V210 M0,120 H120" stroke="#ead2a6" stroke-width="9"/>
      <rect x="-30" y="196" width="180" height="16" rx="6" fill="#2a2f3a"/>${[0, 1, 2, 3, 4, 5, 6].map(k => `<rect x="${-22 + k * 26}" y="212" width="6" height="44" fill="#2a2f3a"/>`).join('')}<rect x="-30" y="252" width="180" height="10" rx="5" fill="#2a2f3a"/><path d="M-14,224 h148" stroke="#000" stroke-width="10" opacity=".07"/></g>`);
  }
  const facade = `<rect x="-400" y="100" width="1880" height="3200" fill="#f6e2bc"/>${Array.from({ length: 10 }, (_, i) => `<rect x="-400" y="${150 + i * 330}" width="1880" height="10" fill="#ead2a6"/>`).join('')}${fenetres.join('')}
    <path d="M-420,110 L1500,110 L1440,-80 L-360,-80 Z" fill="#6f7f95"/>${[-200, 140, 480, 820, 1160].map(x => `<g transform="translate(${x},-150)"><rect width="120" height="170" rx="12" fill="#f6e2bc"/><path d="M-14,0 L60,-60 L134,0 Z" fill="#5a6a80"/><path d="M20,50 C20,26 100,26 100,50 V150 H20 Z" fill="#4c6fa8"/></g>`).join('')}
    <rect x="-420" y="96" width="1920" height="28" rx="8" fill="#ead2a6"/>
    <rect x="-400" y="100" width="1880" height="3200" fill="#ff9a4a" opacity=".08"/>`;
  const echaf = y => `<rect x="-60" y="${y - 250}" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/><rect x="-60" y="${y - 140}" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/>
    <rect x="-80" y="${y - 6}" width="1240" height="40" rx="8" fill="#e8a65c"/><rect x="-80" y="${y - 6}" width="1240" height="10" rx="5" fill="#f7c27f"/><rect x="-80" y="${y + 34}" width="1240" height="44" fill="#ff7b2c"/><rect x="-80" y="${y + 34}" width="1240" height="10" fill="#ff9d5c"/>`;
  calque('ext', `<g id="ex-cam">
    <rect x="-1500" y="-2600" width="4000" height="2800" fill="url(#gCiel)"/>
    <circle cx="880" cy="-520" r="190" fill="#fff1c2"/><circle cx="880" cy="-520" r="300" fill="#fff1c2" opacity=".25"/>
    <g id="ex-nuages">${[[-200, -700, 1], [500, -1000, 1.3], [1200, -620, .9], [-700, -400, 1.1]].map(([x, y, s]) => `<g transform="translate(${x},${y}) scale(${s})"><rect x="-170" y="-50" width="340" height="100" rx="50" fill="#fff"/><circle cx="-40" cy="-60" r="70" fill="#fff"/><circle cx="60" cy="-40" r="56" fill="#fff"/></g>`).join('')}</g>
    <path d="M-1500,140 L-900,140 L-900,40 L-780,40 L-780,-20 L-640,-20 L-640,60 L-500,60 L-500,140 L2500,140 L2500,300 L-1500,300 Z" fill="#a9c7de"/>
    <g transform="translate(-1150,-260)" fill="#a9c7de"><path d="M0,400 L60,0 L120,400 Z"/><rect x="40" y="-40" width="40" height="60"/></g>
    <g id="ex-fond">${facade}
      <rect x="-1500" y="3300" width="4000" height="900" fill="#7b8494"/><rect x="-1500" y="3300" width="4000" height="40" fill="#b9bfc8"/>
      <g transform="translate(660,3360)"><rect x="0" y="-190" width="440" height="190" rx="30" fill="#fff"/><path d="M440,-140 L530,-140 L566,-60 L566,0 L440,0 Z" fill="#f1f1ee"/><path d="M452,-128 L520,-128 L548,-66 L452,-66 Z" fill="#7aa3d1"/><rect x="40" y="-140" width="300" height="60" rx="14" fill="#ffc21a"/><text x="190" y="-98" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="34" fill="#15181e">GASPARD BTP</text><circle cx="110" cy="6" r="44" fill="#1b1f2a"/><circle cx="110" cy="6" r="18" fill="#c9d2dc"/><circle cx="470" cy="6" r="44" fill="#1b1f2a"/><circle cx="470" cy="6" r="18" fill="#c9d2dc"/></g>
      <rect x="16" y="-200" width="34" height="4200" rx="14" fill="#c9d2dc" stroke="#8a96a5" stroke-width="5"/><rect x="1030" y="-200" width="34" height="4200" rx="14" fill="#c9d2dc" stroke="#8a96a5" stroke-width="5"/>
      ${echaf(2100)}${echaf(2900)}
      <rect x="-60" y="1050" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/><rect x="-60" y="1160" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/>
      <g id="ex-mur"><path d="M120,1080 C220,1030 380,1040 440,1100 L440,1300 L120,1300 Z" fill="#fffaf2"/></g>
    </g>
    <g id="ex-traces"></g>
    <g id="ex-seau" transform="translate(900,1300)"><path d="M-62,-110 L62,-110 L52,0 L-52,0 Z" fill="#ff7b2c"/><rect x="-66" y="-122" width="132" height="20" rx="8" fill="#e0611a"/><ellipse cx="0" cy="-118" rx="54" ry="10" fill="#f4efe6"/><path d="M-52,-118 C-50,-190 50,-190 52,-118" stroke="#8a96a5" stroke-width="6" fill="none"/></g>
    <g id="ex-pigeon"></g>
    <g id="ex-ga"></g>
    <rect x="-80" y="1294" width="1240" height="40" rx="8" fill="#e8a65c"/><rect x="-80" y="1294" width="1240" height="10" rx="5" fill="#f7c27f"/><rect x="-80" y="1334" width="1240" height="50" fill="#ff7b2c"/><rect x="-80" y="1334" width="1240" height="10" fill="#ff9d5c"/>
    <g id="ex-collegue"></g>
    <g id="ex-fx"></g>
  </g>
  <g id="ex-avant" filter="url(#flouAvant)" opacity=".9"><rect id="ex-tubeAvant" x="0" y="-200" width="70" height="2400" rx="30" fill="#b6c0cb"/></g>
  ${lumiere('#ffcf8a', '#16264d', .45)}`);
  PERS.ga = new Gaspard('ga'); $('ex-ga').innerHTML = PERS.ga.svg();

  // ---------------- 4. CHANTIER (salle de bain) ----------------
  const carreaux = Array.from({ length: 14 }, (_, r) => Array.from({ length: 6 }, (_, c) => `<rect x="${-40 + c * 96}" y="${180 + r * 76}" width="92" height="72" rx="6" fill="${(r + c) % 2 ? '#d9eef0' : '#cbe5e8'}"/>`).join('')).join('');
  calque('chantier', `<g id="ch-cam">
    <rect x="-400" y="-200" width="1900" height="1600" fill="#e9dcc6"/>
    <g clip-path="url(#ch-clipMur)">${carreaux}</g><clipPath id="ch-clipMur"><path d="M-400,170 L470,170 L520,420 L470,700 L540,980 L480,1260 L-400,1260 Z"/></clipPath>
    ${[[640, 330], [800, 520], [700, 820], [900, 1000]].map(([x, y]) => `<path d="M${x},${y} q60,-30 120,0 q60,30 120,0" stroke="#d6c4a6" stroke-width="10" fill="none" stroke-linecap="round"/>`).join('')}
    <g transform="translate(700,150)"><rect width="320" height="420" rx="16" fill="#fff"/><rect x="20" y="20" width="280" height="380" rx="10" fill="#9fd4f2"/><path d="M160,20 V400 M20,210 H300" stroke="#fff" stroke-width="14"/></g>
    <path d="M700,570 L1020,570 L1300,1260 L520,1260 Z" fill="#fff4d6" opacity=".35"/>
    <g id="ch-horloge" transform="translate(260,330)"><circle r="120" fill="#1b1f2a"/><circle r="104" fill="#fff"/>${Array.from({ length: 12 }, (_, i) => `<rect x="-4" y="-96" width="8" height="${i % 3 ? 12 : 22}" rx="4" fill="#1b1f2a" transform="rotate(${i * 30})"/>`).join('')}
      <g id="ch-h"><rect x="-7" y="-56" width="14" height="62" rx="7" fill="#1b1f2a"/></g><g id="ch-m"><rect x="-5" y="-86" width="10" height="92" rx="5" fill="#ff4d4f"/></g><circle r="12" fill="#ffc21a"/></g>
    <g transform="translate(560,240)"><rect x="-80" y="-20" width="160" height="190" rx="12" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><rect x="-80" y="-20" width="160" height="50" rx="12" fill="#ff4d4f"/><text id="ch-jour" x="0" y="130" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="90" fill="#1b1f2a">12</text><g id="ch-pages"></g></g>
    <rect x="-400" y="1260" width="1900" height="900" fill="#c99c6b"/>${Array.from({ length: 6 }, (_, i) => `<rect x="-400" y="${1300 + i * 110}" width="1900" height="8" fill="#b4875a"/>`).join('')}
    <g transform="translate(150,1360)"><rect x="-90" y="-120" width="180" height="120" rx="10" fill="#d8a96c"/><rect x="-90" y="-120" width="180" height="20" fill="#c08f52"/><rect x="-110" y="-250" width="150" height="130" rx="10" fill="#e2b57a"/></g>
    <g id="ch-ga"></g>
    <g id="ch-cl"></g>
    <g id="ch-fx"></g>
  </g>
  <g id="ch-carte" transform="translate(540,190)"><rect x="-300" y="-80" width="600" height="160" rx="36" fill="#fff" stroke="#1b1f2a" stroke-width="6"/>
    <text x="-260" y="-18" font-family="Bricolage" font-weight="800" font-size="40" fill="#1b1f2a">Avancement</text><text x="260" y="-18" text-anchor="end" font-family="Bricolage" font-weight="800" font-size="40" fill="#ff4d4f">40 %</text>
    <rect x="-260" y="20" width="520" height="34" rx="17" fill="#eee6d8"/><rect x="-260" y="20" width="208" height="34" rx="17" fill="#ff7b2c"/></g>
  ${lumiere('#fff1c9', '#203050', .35)}`);
  PERS.gc = new Gaspard('gc'); $('ch-ga').innerHTML = PERS.gc.svg();
  PERS.cl = new Pantin('cl', 'cliente'); $('ch-cl').innerHTML = PERS.cl.svg();

  // ---------------- 5. LE CLIENT RATÉ appelle le suivant ----------------
  const carteRes = (y, nom, note, avis, site, id) => `<g transform="translate(-260,${y})" id="${id}"><rect width="520" height="250" rx="32" fill="#fff"/>
    <rect x="24" y="24" width="96" height="96" rx="24" fill="${site ? '#15181e' : '#d8d1c4'}"/><text x="72" y="88" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="46" fill="${site ? '#e8b84a' : '#fff'}">${nom[0]}</text>
    <text x="140" y="62" font-family="Geist" font-weight="700" font-size="32" fill="#15181e">${nom}</text>
    <text x="140" y="100" font-family="Geist" font-size="24" fill="#7a7166"><tspan fill="#f2a516">★★★★★</tspan> ${note} (${avis})</text>
    <text x="140" y="134" font-family="Geist" font-weight="600" font-size="23" fill="${site ? '#1f8f5a' : '#b0a698'}">${site ? '✓ Site web · Devis en ligne' : 'Pas de site web'}</text>
    ${site ? `<g id="pr-btnSite"><rect x="24" y="168" width="230" height="58" rx="29" fill="#15181e"/><text x="139" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#fff">Voir le site</text></g><rect x="266" y="168" width="230" height="58" rx="29" fill="#e8b84a"/><text x="381" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#15181e">Devis en ligne</text>`
      : `<rect x="24" y="168" width="230" height="58" rx="29" fill="#eef2ee"/><text x="139" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#1f8f5a">Appeler</text><g id="pr-sansRep"><rect x="266" y="168" width="230" height="58" rx="29" fill="#ffe4e4"/><text x="381" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#e03b3d">Pas de réponse</text></g>`}</g>`;
  calque('prospect', `<g id="pr-cam">
    <g filter="url(#flouFond)"><rect x="-300" y="-300" width="1700" height="2600" fill="#f3d7b4"/><rect x="-300" y="1300" width="1700" height="1000" fill="#d9a774"/>
    <g transform="translate(-60,1050)"><rect x="0" y="0" width="760" height="300" rx="70" fill="#5f8f86"/><rect x="40" y="-120" width="680" height="200" rx="70" fill="#6fa197"/></g>
    <g transform="translate(980,700)"><rect x="-14" y="0" width="28" height="640" fill="#3a3a3a"/><path d="M-130,0 L130,0 L80,-180 L-80,-180 Z" fill="#ffd98a"/></g>
    <g transform="translate(160,220)"><rect width="380" height="460" rx="20" fill="#fff"/><rect x="24" y="24" width="332" height="412" rx="12" fill="#8fc6e8"/><circle cx="260" cy="130" r="50" fill="#ffe7a3"/></g></g>
    <g id="pr-tel" transform="translate(540,880)">
      <rect x="-312" y="-632" width="624" height="1264" rx="96" fill="#2a2e38"/><rect x="-300" y="-620" width="600" height="1240" rx="90" fill="#0f1116"/><rect x="-282" y="-602" width="564" height="1204" rx="76" fill="#f4efe6"/>
      <g clip-path="url(#pr-clip)"><clipPath id="pr-clip"><rect x="-282" y="-602" width="564" height="1204" rx="76"/></clipPath>
        <g id="pr-liste"><rect x="-282" y="-602" width="564" height="1204" fill="#f4efe6"/>
          <rect x="-250" y="-500" width="500" height="80" rx="40" fill="#fff"/><circle cx="-200" cy="-460" r="15" fill="none" stroke="#7a7166" stroke-width="5"/><path d="M-189,-449 l13,13" stroke="#7a7166" stroke-width="5"/><text x="-166" y="-450" font-family="Geist" font-size="26" fill="#15181e">rénovation salle de bain 92</text>
          ${carteRes(-380, 'Gaspard BTP', '4,9', 12, false, 'pr-c1')}${carteRes(-110, 'Rénov Express', '4,7', 58, true, 'pr-c2')}<g opacity=".55">${carteRes(160, 'Maison & Bain', '4,6', 31, true, 'pr-c3')}</g></g>
        <g id="pr-appel"><rect x="-282" y="-602" width="564" height="1204" fill="#1c2130"/><circle cx="0" cy="-260" r="100" fill="#3b465c"/><g transform="translate(0,-170) scale(.32)" id="pr-avatar"></g>
          <text x="0" y="-80" text-anchor="middle" font-family="Geist" font-weight="700" font-size="46" fill="#fff">Gaspard BTP</text><text id="pr-etat" x="0" y="-26" text-anchor="middle" font-family="Geist" font-size="32" fill="#aab3c5">Appel en cours…</text>
          <g>${[0, 1, 2].map(i => `<circle id="pr-pt${i}" cx="${-40 + i * 40}" cy="60" r="12" fill="#aab3c5"/>`).join('')}</g>
          <g transform="translate(0,400)"><circle r="64" fill="#ff4d4f"/><path d="M-30,4 c20,-20 40,-20 60,0" stroke="#fff" stroke-width="12" fill="none" stroke-linecap="round"/></g></g>
      </g>
      <rect x="-90" y="-590" width="180" height="40" rx="20" fill="#000"/><path d="M-260,-560 L-120,-560 L-260,-200 Z" fill="#fff" opacity=".07"/>
    </g>
    <g id="pr-pouce"><path d="M0,0 C-20,-30 -10,-80 30,-90 C70,-98 90,-60 80,-20 L60,140 L200,320 L320,240 L200,40 Z" fill="#c98d66"/><ellipse cx="34" cy="-58" rx="26" ry="20" fill="#e6b896"/></g>
    <g id="pr-fx"></g>
  </g>${lumiere('#fff1c9', '#203050', .3)}`);

  // ---------------- 6. SOIR et 7. DÉCLIC ----------------
  calque('soir', `<g id="so-cam">
    <rect x="-500" y="-400" width="2100" height="2000" fill="#2b3666"/>
    <g transform="translate(640,180)"><rect width="400" height="560" rx="24" fill="#1d2550"/><rect x="20" y="20" width="360" height="520" rx="14" fill="#141a3d"/><circle cx="270" cy="140" r="56" fill="#ffeeb8"/><circle cx="250" cy="126" r="56" fill="#141a3d"/>
      ${Array.from({ length: 14 }, (_, i) => etoile4(40 + (i * 53) % 320, 50 + (i * 37) % 220, 6 + (i % 3) * 3, '#fff8d6')).join('')}
      <path d="M20,420 L80,380 L80,330 L140,330 L140,380 L200,350 L260,390 L260,320 L330,320 L330,370 L380,350 L380,540 L20,540 Z" fill="#232c5a"/>
      ${[[60, 430], [110, 470], [180, 440], [290, 420], [320, 480]].map(([x, y]) => `<rect x="${x}" y="${y}" width="22" height="26" rx="4" fill="#ffd36b"/>`).join('')}<path d="M200,20 V540 M20,280 H380" stroke="#1d2550" stroke-width="16"/></g>
    <g transform="translate(60,520)"><rect width="300" height="150" rx="24" fill="#141a3d"/><text id="so-heure" x="150" y="104" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="76" fill="#ff9d3c">22:47</text></g>
    <g id="so-lampe"><path d="M440,-400 L440,280" stroke="#141a3d" stroke-width="7"/><path d="M340,340 C350,260 530,260 540,340 Z" fill="#e8b84a"/><path d="M340,340 L540,340 L960,1290 L-80,1290 Z" fill="url(#gLampe)"/><ellipse cx="440" cy="340" rx="100" ry="14" fill="#fff4cf"/></g>
    <g id="so-ga"></g>
    <rect x="-500" y="1250" width="2100" height="70" fill="#a8693c"/><rect x="-500" y="1250" width="2100" height="16" fill="#c9844f"/><rect x="-500" y="1320" width="2100" height="800" fill="#1d2550"/>
    <ellipse cx="440" cy="1258" rx="380" ry="40" fill="#ffd98a" opacity=".25"/>
    <g id="so-tasses"></g>
    <g transform="translate(600,1250)"><path d="M-130,0 L120,0 L130,-16 L-120,-16 Z" fill="#fff"/><path d="M-120,-16 L130,-16 L136,-30 L-114,-30 Z" fill="#f4efe6"/><text x="0" y="-38" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="24" fill="#1b1f2a">DEVIS</text></g>
    <rect id="so-halo" x="-500" y="-400" width="2100" height="2000" fill="url(#gHalo)" opacity="0"/>
    <g id="so-fx"></g>
  </g>${lumiere('#ffd08a', '#05081c', .3)}`);
  PERS.gs = new Gaspard('gs'); $('so-ga').innerHTML = PERS.gs.svg();

  // ---------------- 8. LE BARRAGE (métaphore) ----------------
  calque('barrage', `<g id="ba-cam">
    <rect x="-600" y="-600" width="2300" height="1500" fill="url(#gCielSoir)"/>
    <path d="M-600,640 L-300,640 L-300,560 L-200,560 L-200,600 L0,600 L0,520 L80,520 L80,590 L300,590 L300,540 L420,540 L420,600 L700,600 L700,500 L760,440 L820,500 L820,600 L1100,600 L1100,560 L1700,560 L1700,900 L-600,900 Z" fill="#d79a6a" opacity=".7"/>
    <rect x="-600" y="860" width="2300" height="1400" fill="#7fb36a"/><path d="M-600,880 C0,840 600,900 1700,860 L1700,940 L-600,940 Z" fill="#6aa357"/>
    <path id="ba-riviere" d="M-600,1100 C-200,1040 200,1180 540,1130 C800,1090 1100,1160 1700,1120 L1700,1500 C1100,1540 800,1470 540,1510 C200,1560 -200,1440 -600,1500 Z" fill="url(#gEau)"/>
    <g id="ba-flots"></g>
    <g id="ba-buches"></g>
    <g id="ba-ga"></g>
    <g id="ba-bassin"><path d="M680,1180 C820,1150 1000,1190 1300,1170 L1300,1460 C1000,1480 820,1440 680,1460 Z" fill="#8fd6f2" opacity=".9"/></g>
    <g id="ba-fx"></g>
  </g>${lumiere('#ffd08a', '#3a1a06', .4)}`);
  PERS.gb = new Gaspard('gb'); $('ba-ga').innerHTML = PERS.gb.svg();

  // ---------------- 9. FOND DU SITE et 10. CANAPÉ (le prospect) ----------------
  calque('siteFond', `<rect width="1080" height="1920" fill="#f3ead9"/><circle cx="540" cy="760" r="640" fill="url(#gHalo)" opacity=".7"/>
    <g id="sf-formes">${[[120, 260, 90, '#e8b84a'], [960, 400, 60, '#ff7b2c'], [140, 1300, 70, '#4c7bd9'], [950, 1200, 100, '#3fb27f'], [880, 1700, 50, '#e8b84a'], [200, 1750, 40, '#ff7b2c']].map(([x, y, r, c], i) => `<g id="sf-f${i}" transform="translate(${x},${y})"><rect x="${-r / 2}" y="${-r / 2}" width="${r}" height="${r}" rx="${r * .28}" fill="${c}" opacity=".85"/></g>`).join('')}</g>`);
  calque('canape', `<g id="cn-cam">
    <rect x="-400" y="-300" width="1900" height="2400" fill="#f1dcc2"/>
    <g transform="translate(620,240)"><rect width="380" height="470" rx="20" fill="#fff"/><rect x="22" y="22" width="336" height="426" rx="12" fill="url(#gCielSoir)"/><path d="M22,448 L22,330 L90,330 L90,280 L150,280 L150,340 L230,300 L300,340 L358,320 L358,448 Z" fill="#c7845a"/><path d="M190,22 V448 M22,235 H358" stroke="#fff" stroke-width="14"/></g>
    <g transform="translate(140,520)"><rect x="-10" y="0" width="20" height="560" fill="#3a3a3a"/><path d="M-110,0 L110,0 L70,-150 L-70,-150 Z" fill="#ffd98a"/></g>
    <rect x="-400" y="1500" width="1900" height="800" fill="#c9905d"/>
    <g transform="translate(540,1520)"><rect x="-520" y="-420" width="1040" height="260" rx="90" fill="#4f7d74"/><rect x="-560" y="-200" width="1120" height="260" rx="70" fill="#5f8f86"/><rect x="-600" y="-300" width="140" height="360" rx="60" fill="#4f7d74"/><rect x="460" y="-300" width="140" height="360" rx="60" fill="#4f7d74"/></g>
    <g id="cn-pr"></g>
    <g transform="translate(540,1560)"><rect x="-480" y="-30" width="960" height="60" rx="20" fill="#8a5a36"/><rect x="-480" y="-30" width="960" height="16" rx="8" fill="#a8724a"/><rect x="-420" y="30" width="40" height="300" fill="#6e4628"/><rect x="380" y="30" width="40" height="300" fill="#6e4628"/><g transform="translate(260,-30)"><path d="M-40,-70 L40,-70 L34,0 L-34,0 Z" fill="#fff" stroke="#3a2a22" stroke-width="6"/></g><g transform="translate(-250,-30)"><rect x="-60" y="-80" width="120" height="80" rx="10" fill="#3fb27f"/><path d="M-30,-80 C-60,-160 -10,-170 0,-90 C10,-170 70,-150 30,-80 Z" fill="#2f8f5b"/></g></g>
    <g id="cn-fx"></g>
  </g>${lumiere('#ffd08a', '#203050', .35)}`);
  PERS.pr = new Pantin('pr', 'prospecte'); $('cn-pr').innerHTML = PERS.pr.svg();
  // le vol de la carte de devis au dessus des toits
  calque('vol', `<rect width="1080" height="1920" fill="url(#gCielSoir)"/>
    <g id="vo-toits">${Array.from({ length: 4 }, (_, k) => `<g transform="translate(0,${1150 + k * 170})" opacity="${1 - k * .12}">${Array.from({ length: 9 }, (_, i) => `<g transform="translate(${-200 + i * 190 + (k % 2) * 90},0)"><rect x="0" y="0" width="170" height="400" fill="${['#c7845a', '#b97750', '#a8694a', '#94603f'][k]}"/><path d="M-10,0 L85,-90 L180,0 Z" fill="${['#6f7f95', '#62728a', '#56657c', '#4a586e'][k]}"/><rect x="30" y="-120" width="22" height="60" fill="#c9765a"/>${[0, 1].map(j => `<rect x="${30 + j * 70}" y="60" width="40" height="56" rx="6" fill="#ffd36b" opacity=".8"/>`).join('')}</g>`).join('')}</g>`).join('')}</g>
    <g id="vo-carte"></g><g id="vo-fx"></g>`);

  // ---------------- FIN ----------------
  calque('fin', `<rect width="1080" height="1920" fill="#15181e"/><circle cx="540" cy="900" r="760" fill="url(#gHalo)" opacity=".12"/>
    <g id="fi-ga"></g>
    <g id="fi-a"><text x="540" y="380" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="100" fill="#fff">Tu te reconnais ?</text></g>
    <g id="fi-b"><text x="290" y="520" text-anchor="end" font-family="Bricolage" font-weight="700" font-size="60" fill="#cfc6b8">Écris</text><text x="502" y="520" font-family="Bricolage" font-weight="700" font-size="60" fill="#cfc6b8">en commentaire</text></g>
    <g id="fi-pill"><g id="fi-pillIn"><rect x="-96" y="-50" width="192" height="96" rx="26" fill="#e8b84a"/><text x="0" y="22" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="64" fill="#15181e">SITE</text></g></g>
    <g id="fi-logo"><image href="logo-aurea-media-clair.png" x="380" y="1640" width="320" height="112"/><text x="540" y="1800" text-anchor="middle" font-family="Geist" font-size="32" fill="#8a8478">aurea-media.fr</text></g>
    <g id="fi-fx"></g>`);
  PERS.gf = new Gaspard('gf'); $('fi-ga').innerHTML = PERS.gf.svg();

  // ---------------- Interface plein écran ----------------
  calque('ui', `<g id="ui-bulles"></g>
    <g id="notif"><rect x="40" y="0" width="1000" height="190" rx="44" fill="#fff" stroke="#1b1f2a" stroke-width="6"/><rect x="74" y="34" width="122" height="122" rx="30" fill="#15181e"/><text x="135" y="118" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="62" fill="#e8b84a">G</text>
      <text x="224" y="70" font-family="Geist" font-weight="600" font-size="26" fill="#8a8478">gaspard-btp.fr · maintenant</text><text id="notifTitre" x="224" y="116" font-family="Bricolage" font-weight="800" font-size="42" fill="#15181e">Nouvelle demande de devis</text><text id="notifTxt" x="224" y="160" font-family="Geist" font-size="30" fill="#4b463e">Salle de bain · Boulogne · 4 photos</text></g>
    <g id="ui-fx"></g>`);
  for (const id of ['hook', 'ext', 'chantier', 'prospect', 'soir', 'barrage', 'siteFond', 'canape', 'vol', 'fin', 'ui']) $('svg').appendChild(L[id]);
  // l'avatar de Gaspard dans l'écran d'appel
  const av = new Gaspard('gav'); $('pr-avatar').innerHTML = av.svg(); av.poser({ y: 260, bouche: 'sourire', ombre: 0 });
}

// Téléphone « collègue » : un smartphone avec casque, bras et jambes en tubes
function collegue(p) {
  const T = Gaspard.prototype.tube;
  const bras = (sx, m) => { const S = { x: sx * 120, y: -230 }; const Mi = { x: (S.x + m.x) / 2 + sx * 30, y: (S.y + m.y) / 2 + 20 }; return T(S, Mi, m, 26, '#15181e', '#000') + `<circle cx="${m.x}" cy="${m.y}" r="24" fill="#fff" stroke="#15181e" stroke-width="6"/>`; };
  const jambe = sx => T({ x: sx * 50, y: -40 }, { x: sx * 56, y: -10 }, { x: sx * 60, y: 0 }, 26, '#15181e', '#000') + `<ellipse cx="${sx * 74}" cy="4" rx="40" ry="18" fill="#ffc21a"/>`;
  return `<g transform="translate(0,${p.y || 0}) rotate(${p.rot || 0}) scale(${p.sx || 1},${p.sy || 1})">${jambe(-1)}${jambe(1)}
    <g transform="translate(0,-40)"><rect x="-130" y="-470" width="260" height="470" rx="50" fill="#15181e"/><rect x="-114" y="-454" width="228" height="438" rx="38" fill="#f5f0e8"/>
      <rect x="-114" y="-454" width="228" height="72" rx="38" fill="#15181e"/><rect x="-114" y="-414" width="228" height="32" fill="#15181e"/><circle cx="-82" cy="-420" r="16" fill="#e8b84a"/>
      <g opacity="${p.ecran || 0}"><image href="photos/hero.webp" x="-104" y="-372" width="208" height="150" preserveAspectRatio="xMidYMid slice"/><rect x="-100" y="-206" width="160" height="16" rx="8" fill="#15181e"/><rect x="-100" y="-150" width="200" height="50" rx="25" fill="#e8b84a"/></g>
      <g opacity="${1 - (p.ecran || 0)}" transform="translate(0,-240)"><ellipse cx="-50" cy="-30" rx="30" ry="${36 * (1 - (p.cligne || 0) * .9)}" fill="#15181e"/><ellipse cx="50" cy="-30" rx="30" ry="${36 * (1 - (p.cligne || 0) * .9)}" fill="#15181e"/><circle cx="-40" cy="-44" r="10" fill="#fff"/><circle cx="60" cy="-44" r="10" fill="#fff"/>
        ${p.bouche === 'plat' ? '<path d="M-40,70 h80" stroke="#15181e" stroke-width="12" stroke-linecap="round"/>' : '<path d="M-50,56 C-20,96 20,96 50,56" stroke="#15181e" stroke-width="12" stroke-linecap="round" fill="none"/>'}</g>
      <g transform="translate(0,-470)"><path d="M-130,10 C-126,-110 126,-110 130,10 Z" fill="#ffc21a"/><rect x="-160" y="0" width="320" height="30" rx="15" fill="#e89b00"/><rect x="-18" y="-96" width="36" height="96" rx="12" fill="#ffe27a"/></g></g>
    ${bras(-1, p.mainG || { x: -170, y: -110 })}${bras(1, p.mainD || { x: 170, y: -110 })}</g>`;
}

// ======================================================================================
const T = {};
function reperes() {
  T.whip = P('l01') - .12; T.ch = P('l04') - .14; T.pr = P('l05') - .14; T.so = P('l06') - .2; T.dec = P('l07');
  T.ba = M('l07', 5) + .35; T.si = P('l08') + .2; T.cn = P('l09') - .2; T.vol = M('l09', 9) + .1; T.ret = T.vol + .75;
  T.fil = P('l10') - .1; T.co = P('l11') - .2; T.fin = CAL.finTexte;
}
function rendre(t) {
  if (!T.whip) reperes();
  for (const id in L) vis(L[id], false);
  vis(L.ui, true);
  $('flash').style.opacity = 0; $('fondu').style.opacity = 0;
  for (const id in L) { L[id].removeAttribute('filter'); L[id].removeAttribute('clip-path'); tr(L[id], ''); op(L[id], 1); }
  $('tel').style.display = 'none'; $('svgHaut').innerHTML = '';
  let fx = '';
  const flash = v => { $('flash').style.opacity = Math.max(+$('flash').style.opacity, v); };
  const fondu = v => { $('fondu').style.opacity = Math.max(+$('fondu').style.opacity, v); };

  if (t < T.whip + .25) { vis(L.hook, true); rHook(t); }
  const extA = t >= T.whip && t < T.ch + .4, extB = t >= T.ret && t < T.ret + 1.0, extC = t >= T.co;
  if (extA || extB || extC) { vis(L.ext, true); rExt(t, extA ? 'a' : extB ? 'b' : 'c'); }
  if (t >= T.whip && t < T.whip + .25) tr(L.hook, `translate(0,${E.in((t - T.whip) / .25) * 1920})`);
  if (t >= T.whip && t < T.whip + .3) tr(L.ext, `translate(0,${(E.out((t - T.whip) / .3) - 1) * 1920})`);
  fx += lignesVitesse(t, T.whip - .05, .45, 540, 960, '#fff', .8);
  if (t >= T.ch && t < T.pr + .45) { vis(L.chantier, true); rChantier(t); const r = E.in(clamp((t - T.ch) / .38)) * 1500; if (r < 1499) { attr('irisC', 'r', r); L.chantier.setAttribute('clip-path', 'url(#clipIris)'); } }
  if (t >= T.pr && t < T.so + .1) { vis(L.prospect, true); rProspect(t); const k = E.expoIo(clamp((t - T.pr) / .4)); tr(L.prospect, `translate(${(1 - k) * 1080},0) rotate(${(1 - k) * 6})`); if (t < T.pr + .4) tr(L.chantier, `translate(${-k * 1080},0) rotate(${-k * 6})`); }
  if (t >= T.so && t < T.ba + .3) { vis(L.soir, true); rSoir(t); }
  fondu(1 - Math.abs(t - (T.so + .03)) / .18);
  if (t >= T.ba && t < T.si + .4) { vis(L.barrage, true); rBarrage(t, 'a'); }
  if (t >= T.fil && t < T.co + .35) { vis(L.barrage, true); rBarrage(t, 'b'); }
  flash(.8 * (1 - Math.abs(t - T.ba) / .1));
  if (t >= T.si && t < T.cn + .3) { vis(L.siteFond, true); rSite(t); }
  if (t >= T.cn && t < T.vol + .15) { vis(L.canape, true); rCanape(t); }
  if (t >= T.vol && t < T.ret + .1) { vis(L.vol, true); rVol(t); }
  if (t >= T.fin) { vis(L.fin, true); rFin(t); }
  fondu(1 - Math.abs(t - T.fin) / .2); fondu(clamp((t - CAL.duree + .3) / .3));
  fx += rUI(t);
  $('ui-fx').innerHTML = fx;
  rendreSousTitres(t);
  $('st').style.opacity = t >= T.fin - .05 ? 0 : 1;
}

// ---------------------------------- 1. ACCROCHE ----------------------------------
function rHook(t) {
  const salve = .58, k = t % salve, n = Math.floor(t / salve);
  const h = k < .28 ? Math.sin(Math.PI * k / .28) : 0, imp = squashImpact(k, .28, .24, .3);
  const vib = Math.sin(t * 110) * 5, t14 = M('l00', 0), t9 = M('l00', 6);
  tr('hk-tel', `translate(${540 + vib + Math.sin(n * 1.7) * 30},${880 - h * 110}) rotate(${-6 + Math.sin(n * 2.1) * 7 + vib * .3})`);
  tr('hk-telIn', `scale(${k < .28 ? 1 - h * .05 : imp.sx},${k < .28 ? 1 + h * .08 : imp.sy})`);
  tr('hk-ombre', `translate(${Math.sin(n * 1.7) * 30},0) scale(${1 - h * .35},1) translate(${540 * (h * .35)},${1340 * 0})`);
  // compteur en rouleau : défile de 1 à 14 et se cale sur le mot « Quatorze »
  const v = Math.min(14, 14 * E.out(clamp(t / Math.max(.4, t14 + .15))));
  tr('hk-rouleau', `translate(0,${-v * 150})`);
  $('hk-heure').textContent = t < t9 ? '8:59' : '9:00';
  const sh = tremble(t, t14 + .12, 26, .35);
  const z = 1.02 + .06 * E.out(clamp(t / 2.4)) + .07 * apparition(t, t14 + .12, .3);
  camera('hk-cam', 540, 960, z, 0, sh);
  // Gaspard surgit du bas du cadre, les yeux exorbités, les dents qui claquent
  const ga = apparition(t, t9 - .25, .35);
  PERS.gh.poser({ x: 540, y: 2150 - ga * 300, s: 1.05, oeil: 1.35, paup: 0, sourcils: 1.3, froncer: 1, bouche: 'grimace', dents: 1, sueur: 1, regardY: -1, casque: -Math.abs(Math.sin(t * 30)) * 10 * ga,
    mainG: { x: -190, y: -560 }, mainD: { x: 190, y: -560 }, coudeG: 1, coudeD: -1, formeG: 'salut', formeD: 'salut', cligne: 0, telCeinture: false });
  let fx = vibrations(540, 880, t, true, 260, '#3a1a06');
  fx += poussiere(540, 1300, t, n * salve + .28, 6, .9, '#f4efe6', .4);
  fx += impact(540, 910, t, t14 + .1, 1.7, '#ffd23f', .3) + etincelles(540, 910, t, t14 + .12, 9, 260, '#ffd23f', .6);
  fx += onomatopee(250, 420, 'DRING !', t, .02, .45, '#fff', 116, -10) + onomatopee(840, 1480, 'BZZ', t, .7, .35, '#ffd23f', 88, 8) + onomatopee(250, 1500, 'BZZ', t, 1.3, .35, '#ffd23f', 80, -6);
  if (t > t9 - .05) fx += onomatopee(540, 300, '9:00 !', t, t9 - .05, .6, '#ffd23f', 130, 0) + gouttesSueur(540, 1700, t, t9 + .1, .5);
  $('hk-fx').innerHTML = fx;
}

// ---------------------------------- 2-3-4, 10b, 11-12. EXTÉRIEUR ----------------------------------
function rExt(t, phase) {
  const ga = PERS.ga; let fx = '';
  const p = { x: 560, y: 1300, s: 1.22, cligne: clignementG(t, 2), queue: 0, casque: 0 };
  let sh = { x: 0, y: 0, r: 0 }, cam;
  if (phase === 'a') {
    const tToi = M('l01', 1), tRing = tToi + .55, tDec = P('l02'), tOr = M('l02', 3), tQ = P('l03');
    cam = piste(t, [[T.whip, { x: 560, y: 760, z: 2.0 }], [tToi, { x: 560, y: 780, z: 1.95 }], [tToi + .6, { x: 560, y: 1700, z: .5 }, E.expoIo], [tDec - .35, { x: 560, y: 1500, z: .6 }, E.sine],
      [tDec - .05, { x: 560, y: 880, z: 1.7 }, E.expoIo], [tQ - .05, { x: 560, y: 860, z: 1.8 }, E.sine], [P('l04'), { x: 560, y: 820, z: 2.2 }, E.sine]]);
    // il lisse l'enduit avec sa queue en sifflotant
    const lisse = t < tRing;
    p.queue = lisse ? -40 + Math.sin(t * 7) * 28 : 0; p.bouche = lisse ? 'o' : 'sourire'; p.paup = .25; p.regardX = -.6; p.mainG = { x: -200, y: -150 }; p.mainD = { x: 210, y: -160 };
    if (lisse) fx += notes(640, 560, t, T.whip, tRing);
    // sonnerie : sursaut cartoon, casque qui décolle (follow-through), pigeon
    const s = saut(t, tRing, .17, 90);
    if (t > tRing - .2) { p.y = 1300 + s.y; p.sx = s.sx; p.sy = s.sy; const air = clamp((t - tRing) / .34); p.casque = -60 * Math.sin(Math.PI * air) - (t > tRing + .34 ? squashImpact(t, tRing + .34, 1, .4).sx * 0 : 0);
      p.oeil = 1 + .45 * apparition(t, tRing, .2) * (1 - clamp((t - tRing - .7) / .3)); p.paup = 0; p.sourcils = 1.3; p.bouche = 'cri'; p.regardX = 0; p.regardY = .7; p.queue = -60 * Math.sin(Math.PI * air);
      p.mainG = { x: -190, y: -520 }; p.mainD = { x: 190, y: -520 }; p.coudeG = 1; p.coudeD = -1; p.formeG = 'salut'; p.formeD = 'salut'; }
    if (t > tRing - .05 && t < tRing + .5) fx += onomatopee(560, 330, '!', t, tRing, .5, '#ff4d4f', 190, 0);
    fx += vibrations(560 + 122, 1180, t, t > tToi && t < tDec + .2, 70, '#3a1a06');
    // il décroche : jonglage avec le téléphone (mains pleines d'enduit), SPLAT, puis à l'oreille
    if (t > tDec - .1) {
      p.telCeinture = false;
      if (t < tOr) {
        const j = clamp((t - tDec) / (tOr - tDec)), ph = Math.floor(j * 4), u = (j * 4) % 1;
        const tel = { x: ph % 2 ? 90 : -90, y: -560 - Math.sin(u * Math.PI) * 170 };
        p.mainG = { x: -110, y: -430 }; p.mainD = { x: 110, y: -430 }; p.coudeG = 1; p.coudeD = -1; p.formeG = 'salut'; p.formeD = 'salut';
        p.bouche = 'grimace'; p.oeil = 1.2; p.regardY = -1; p.regardX = tel.x / 120; p.sourcils = 1.1; p.queue = Math.sin(t * 20) * 12;
        fx += `<g transform="translate(${560 + tel.x * 1.22},${1300 + tel.y * 1.22}) rotate(${j * 900})"><rect x="-36" y="-66" width="72" height="132" rx="18" fill="#15181e"/><rect x="-30" y="-60" width="60" height="120" rx="12" fill="#3b5c8f"/><circle cx="8" cy="-10" r="18" fill="#f4efe6"/><circle cx="-12" cy="22" r="10" fill="#f4efe6"/></g>`;
        if (u < .1 && t > tDec) fx += poussiere(560 + tel.x * 1.22, 1300 + tel.y * 1.22 + 70, t, t - .02, 4, .4, '#f4efe6', .25);
      } else {
        Object.assign(p, { mainD: { x: 165, y: -405 }, coudeD: 1, objetD: 'tel', formeD: 'tient', mainG: { x: -200, y: -160 }, regardX: .6, regardY: -.2, sourcils: .7, froncer: .7, paup: .05,
          bouche: t < tQ ? (t < tOr + .45 ? 'rire' : 'sourire') : ['ouvert', 'o', 'plat', 'ouvert'][Math.floor(t * 9) % 4] });
      }
      if (t > tOr - .05 && t < tOr + .5) fx += onomatopee(380, 520, 'SPLAT', t, tOr - .05, .5, '#fff', 96, -12) + poussiere(760, 780, t, tOr - .05, 5, .7, '#f4efe6', .4);
    }
    // les questions déferlent comme une rivière : le niveau monte jusqu'au menton
    if (t > tQ - .1) {
      p.sueur = 1; p.oeil = 1.15; p.sourcils = 1.2; p.froncer = 1; p.regardX = Math.sin(t * 9) * .8;
      p.dents = 1; p.casque = Math.sin(t * 40) * 3;
      const niveau = E.io(clamp((t - tQ) / (P('l04') - tQ - .1)));
      const yEau = 1300 - niveau * 420;
      fx += `<path d="M-200,${yEau} ${Array.from({ length: 14 }, (_, i) => `Q${-150 + i * 100},${yEau - 26 * Math.sin(t * 6 + i)} ${-100 + i * 100},${yEau}`).join(' ')} L1300,1500 L-200,1500 Z" fill="#5fc3e8" opacity=".9"/>`;
      const qs = ['Boulogne ?', 'Combien ?', 'Des photos ?', 'Dispo quand ?', 'Salle de bain ?', 'Un devis ?', 'Le prix ?', 'Vous venez ?'];
      for (let i = 0; i < 8; i++) {
        const t0 = tQ + i * .2, u = clamp((t - t0) / 1.4);
        if (u <= 0) continue;
        const x = -100 + ((i * 197) % 1100) + Math.sin(t * 3 + i) * 20, y = yEau - 30 - Math.sin(u * Math.PI) * 60;
        fx += `<g transform="translate(${mix(x - 500, x, E.out(clamp(u * 2.5)))},${y}) rotate(${Math.sin(t * 2 + i) * 8})"><rect x="-120" y="-44" width="240" height="88" rx="40" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><text y="12" text-anchor="middle" font-family="Geist" font-weight="700" font-size="32" fill="#1b1f2a">${qs[i]}</text></g>`;
      }
      sh = tremble(t, tQ + Math.floor((t - tQ) / .4) * .4, 8, .2);
    }
    for (let i = 0; i < 7; i++) { const u = ((t * .25 + i / 7) % 1); fx += `<circle cx="${200 + (i * 151) % 700 + Math.sin(u * 6 + i) * 30}" cy="${700 + u * 700}" r="${4 + i % 3 * 2}" fill="#fff" opacity="${Math.sin(Math.PI * u) * .6}"/>`; }
  } else if (phase === 'b') {
    // la carte de devis atterrit : Gaspard la rattrape d'un coup de queue
    cam = piste(t, [[T.ret, { x: 600, y: 860, z: 1.7 }], [T.ret + 1, { x: 600, y: 880, z: 1.6 }, E.sine]]);
    const att = T.ret + .25, k = clamp((t - T.ret) / .25);
    if (k < 1) fx += `<g transform="translate(${mix(1200, 760, E.out(k))},${mix(300, 1180, E.in(k))}) rotate(${mix(-30, 10, k)})">${carteDevis(1)}</g>`;
    else fx += `<g transform="translate(${680 + 0},${1170}) rotate(-6) scale(.8)">${carteDevis(1)}</g>`;
    const s = squashImpact(t, att, .25, .4);
    Object.assign(p, { queue: -50 * Math.sin(Math.PI * clamp((t - att + .15) / .35)), bouche: t > att ? 'rire' : 'o', oeil: t > att ? 1.1 : 1.3, paup: 0, sourcils: 1, regardX: .6, regardY: .6,
      sx: s.sx, sy: s.sy, mainD: { x: 190, y: -150 }, formeD: 'pouce', mainG: { x: -200, y: -160 } });
    fx += impact(760, 1180, t, att, .9, '#ffd23f') + etincelles(760, 1180, t, att, 8, 200);
  } else {
    // 11-12 : le collègue arrive, tope là, puis la pause café
    cam = piste(t, [[T.co, { x: 640, y: 900, z: 1.5 }], [M('l11', 4) - .1, { x: 700, y: 960, z: 1.2 }, E.expoIo], [P('l12'), { x: 700, y: 950, z: 1.25 }, E.sine], [T.fin + .4, { x: 690, y: 900, z: 1.5 }, E.sine]]);
    const tc = M('l11', 4) - .2, tTope = M('l11', 7), tCafe = P('l12'), tNon = M('l12', 4);
    Object.assign(p, { bouche: 'sourire', paup: .15, regardX: .7, queue: Math.sin(t * 4) * 8, mainG: { x: -200, y: -160 }, mainD: { x: 210, y: -170 } });
    if (t > tc) {
      const chute = clamp((t - tc) / .3), yM = t < tc + .3 ? -900 * (1 - E.in(chute)) : 0, sq = squashImpact(t, tc + .3, .3, .5);
      let cp = { y: yM, sx: sq.sx, sy: sq.sy, cligne: clignementG(t, 9), bouche: 'sourire', ecran: 0, mainG: { x: -170, y: -110 }, mainD: { x: 170, y: -110 } };
      if (t > tTope - .25 && t < tTope + .5) { cp.mainG = { x: -230, y: -560 }; Object.assign(p, { mainD: { x: 300, y: -560 }, coudeD: 1, formeD: 'salut', bouche: 'rire', oeil: 1.1, paup: 0 }); }
      if (t > tCafe) {
        Object.assign(p, { mainD: { x: 290, y: -330 }, coudeD: 1, objetD: 'tasse', formeD: 'tient', bouche: 'sourire', regardX: .9 });
        const non = t > tNon - .1 && t < tNon + .9;
        cp.rot = non ? Math.sin((t - tNon) * 22) * 9 : 0; cp.bouche = 'plat'; cp.ecran = clamp((t - tNon - .3) / .2);
        if (t > tNon + .9) Object.assign(p, { mainD: { x: 120, y: -420 }, bouche: 'o', paup: .35, regardX: .5, queue: Math.sin(t * 6) * 15 });
        if (t > tNon + .2) fx += `<g transform="translate(740,520) scale(${apparition(t, tNon + .2, .3)})"><rect x="-250" y="-70" width="500" height="132" rx="34" fill="#fff" stroke="#1b1f2a" stroke-width="6"/><text x="0" y="-16" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="34" fill="#15181e">Pas le temps !</text><text x="0" y="30" text-anchor="middle" font-family="Geist" font-size="28" fill="#4b463e">Je réponds à 3 clients</text></g>`;
      }
      $('ex-collegue').innerHTML = `<g transform="translate(900,1300) scale(.62)">${collegue(cp)}</g>`;
      fx += impact(900, 1300, t, tc + .3, .9, '#fff') + poussiere(900, 1300, t, tc + .3, 6, .9);
      fx += impact(800, 640, t, tTope, 1.2, '#ffd23f') + onomatopee(800, 500, 'TOPE !', t, tTope, .6, '#ffd23f', 100, 6) + etincelles(800, 640, t, tTope, 8, 220);
    }
  }
  if (phase !== 'c') $('ex-collegue').innerHTML = '';
  ga.poser(p);
  // pigeon qui s'envole à la sonnerie
  const tEnvol = M('l01', 1) + .6, envol = clamp((t - tEnvol) / 1.2), ailes = Math.sin(t * 40);
  $('ex-pigeon').innerHTML = phase !== 'a' ? '' : `<g transform="translate(${880 + envol * 500},${1166 - envol * 700 - Math.sin(envol * 3) * 40}) scale(${envol > 0 ? -1 : 1},1)"><ellipse cx="0" cy="0" rx="46" ry="34" fill="#8e98a8"/><circle cx="-38" cy="-28" r="22" fill="#a7b0be"/><circle cx="-44" cy="-32" r="5" fill="#1b1f2a"/><path d="M-60,-26 l-16,6 l16,4 z" fill="#ff9d3c"/><path d="M10,-10 C40,${-40 - (envol > 0 ? ailes * 40 : 0)} 70,-30 60,0 Z" fill="#6f7a8c"/><path d="M-6,30 v18 M10,30 v18" stroke="#ff9d3c" stroke-width="5"/></g>`;
  // traces d'enduit laissées par la queue sur le mur
  $('ex-traces').innerHTML = phase === 'a' && t > T.whip ? Array.from({ length: Math.min(5, Math.floor((t - T.whip) * 3)) }, (_, i) => `<path d="M${420 + i * 18},${1010 - i * 30} q50,-24 100,0" stroke="#fffaf2" stroke-width="22" fill="none" stroke-linecap="round"/>`).join('') : '';
  $('ex-fx').innerHTML = fx;
  camera('ex-cam', cam.x + bruit(t, 4) * 3, cam.y + bruit(t, 5) * 3, cam.z, bruit(t, 6) * .4, sh);
  // tube d'échafaudage au premier plan (profondeur, parallaxe)
  tr('ex-tubeAvant', `translate(${-200 + (cam.x - 560) * -1.6 + (cam.z - 1) * 120},0)`);
  op('ex-avant', cam.z > 1.2 ? .85 : 0);
  tr('ex-nuages', `translate(${t * 20},0)`);
}
function carteDevis(s = 1) {
  return `<g transform="scale(${s})"><rect x="-150" y="-95" width="300" height="190" rx="24" fill="url(#gCarte)" stroke="#8a6214" stroke-width="5"/><rect x="-126" y="-70" width="70" height="70" rx="16" fill="#15181e"/><text x="-91" y="-22" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="40" fill="#e8b84a">€</text>
    <text x="-40" y="-40" font-family="Bricolage" font-weight="800" font-size="34" fill="#15181e">Devis</text><text x="-40" y="-8" font-family="Geist" font-size="22" fill="#4b3a14">Salle de bain</text>
    <rect x="-126" y="24" width="252" height="14" rx="7" fill="#15181e" opacity=".25"/><rect x="-126" y="50" width="180" height="14" rx="7" fill="#15181e" opacity=".25"/><g transform="translate(108,40)"><circle r="26" fill="#1f8f5a"/><path d="M-12,1 l8,9 l16,-18" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g></g>`;
}

// ---------------------------------- 4. CHANTIER ----------------------------------
function rChantier(t) {
  const u = t - T.ch;
  const cam = piste(u, [[0, { x: 300, y: 380, z: 2.3 }], [.45, { x: 330, y: 390, z: 2.1 }], [1.1, { x: 560, y: 980, z: 1.0 }, E.expoIo], [2.8, { x: 590, y: 1000, z: 1.08 }, E.sine]]);
  tr('ch-m', `rotate(${u * 1600})`); tr('ch-h', `rotate(${u * 140 + 60})`);
  $('ch-jour').textContent = 12 + Math.floor(clamp((u - .9) / 1.4) * 9);
  let pages = '';
  for (let i = 0; i < 9; i++) { const t0 = .9 + i * 1.4 / 9, k = clamp((u - t0) / .7); if (k > 0 && k < 1) pages += `<g transform="translate(${k * 260},${k * 300 + k * k * 200}) rotate(${k * 260})" opacity="${1 - k}"><rect x="-80" y="30" width="160" height="140" rx="10" fill="#fff" stroke="#1b1f2a" stroke-width="4"/><text x="0" y="130" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="80" fill="#1b1f2a">${12 + i}</text></g>`; }
  $('ch-pages').innerHTML = pages;
  tr('ch-carte', `translate(${540 + ((u % .7) < .12 ? Math.sin(u * 120) * 6 : 0)},${mix(-200, 190, apparition(u, .9, .35))})`);
  PERS.gc.poser({ x: 300, y: 1360, s: .62, ombre: .15, cligne: clignementG(t, 4), mainD: { x: 165, y: -405 }, coudeD: 1, objetD: 'tel', formeD: 'tient', mainG: { x: -200, y: -330 }, coudeG: -1, formeG: 'pointe',
    bouche: ['ouvert', 'o', 'plat', 'ouvert'][Math.floor(t * 10) % 4], sueur: 1, sourcils: 1, froncer: 1, regardX: .7, queue: Math.sin(t * 9) * 14, telCeinture: false });
  const tape = Math.max(0, Math.sin(u * 22)), souffle = clamp((u - 1.4) / .3) * (1 - clamp((u - 2.2) / .3));
  PERS.cl.poser({ x: 790, y: 1780, s: 1.08, cligne: clignementG(t, 7), tourne: -.55, regardX: -1, regardY: -.2, sourcils: -.4, froncer: -1, bouche: souffle > .3 ? 'o' : 'moue',
    mainG: { x: 70, y: -405 }, coudeG: 1, mainD: { x: -70, y: -425 }, coudeD: -1, piedD: { x: 60 + tape * 10, y: -tape * 26 }, bob: Math.sin(u * 22) * 3, tete: Math.sin(u * 3) * 3 });
  let fx = '';
  for (let i = 0; i < 4; i++) { const k = ((u * 1.2 + i / 4) % 1); if (u > .9) fx += `<circle cx="${790 + (i % 2 ? 130 : -130) + (i % 2 ? 1 : -1) * k * 60}" cy="${930 - k * 160}" r="${18 + k * 34}" fill="#fff" opacity="${(1 - k) * .85}"/>`; }
  fx += onomatopee(830, 1700, 'TAP TAP', t, T.ch + 1.0, 1.0, '#fff', 56, -4);
  $('ch-fx').innerHTML = fx;
  camera('ch-cam', cam.x + bruit(t, 7) * 3, cam.y + bruit(t, 8) * 3, cam.z);
}

// ---------------------------------- 5. LE CLIENT RATÉ ----------------------------------
function rProspect(t) {
  const tRate = M('l05', 5), tSuiv = M('l05', 9) - .05, u = t - T.pr;
  camera('pr-cam', 540, 960, mix(1.02, 1.08, clamp(u / 2.5)));
  for (let i = 0; i < 3; i++) op('pr-pt' + i, ((t * 3 - i * .33) % 1) < .5 ? 1 : .25);
  $('pr-etat').textContent = t < tRate ? 'Appel en cours…' : 'Pas de réponse';
  attr('pr-etat', 'fill', t < tRate ? '#aab3c5' : '#ff6b6d');
  const rac = tRate + .15, k = E.io(clamp((t - rac) / .3));
  op('pr-appel', 1 - k); tr('pr-liste', `translate(0,${(1 - k) * 90})`);
  op('pr-c1', t > rac + .2 ? .5 : 1); op('pr-sansRep', clamp((t - rac - .2) / .2));
  const pt = piste(t, [[T.pr, { x: 700, y: 1500 }], [rac + .2, { x: 700, y: 1450 }], [tSuiv - .15, { x: 420, y: 1060 }, E.io], [tSuiv, { x: 410, y: 1080 }, E.out], [tSuiv + .5, { x: 420, y: 1070 }]]);
  tr('pr-pouce', `translate(${pt.x},${pt.y}) rotate(-28)`);
  tr('pr-btnSite', t > tSuiv && t < tSuiv + .2 ? 'translate(139,197) scale(.9) translate(-139,-197)' : '');
  const sq = squashImpact(t, tSuiv, .06, .3);
  tr('pr-tel', `translate(540,880) rotate(${-3 + Math.sin(t * 1.4)}) scale(${sq.sx},${sq.sy})`);
  let fx = '';
  const ta = tSuiv + .15, ka = clamp((t - ta) / .9);
  if (ka > 0 && ka < 1) {
    const x = 420 + ka * 800, y = 1060 - ka * 900 + Math.sin(ka * 9) * 50;
    fx += `<path d="M420,1060 Q${420 + ka * 400},${1060 - ka * 700} ${x},${y}" stroke="#15181e" stroke-width="5" stroke-dasharray="14 12" fill="none" opacity=".5"/><g transform="translate(${x},${y}) rotate(${-40 + Math.sin(ka * 9) * 15}) scale(1.3)"><path d="M-60,0 L60,-30 L-10,30 Z" fill="#fff" stroke="#15181e" stroke-width="5" stroke-linejoin="round"/><path d="M-10,30 L0,6 L60,-30" fill="none" stroke="#15181e" stroke-width="5" stroke-linejoin="round"/><text x="0" y="-36" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="30" fill="#15181e">DEVIS</text></g>`;
  }
  fx += impact(410, 1080, t, tSuiv, .5, '#e8b84a', .25) + onomatopee(780, 340, 'BIP… BIP…', t, T.pr + .25, Math.max(.3, tRate - T.pr - .3), '#fff', 60, 0) + onomatopee(800, 1300, 'BYE !', t, ta + .3, .6, '#ffd23f', 80, -8);
  $('pr-fx').innerHTML = fx;
}

// ---------------------------------- 6-7. SOIR, DÉCLIC ----------------------------------
function rSoir(t) {
  const u = t - T.so, dec = T.dec, declic = apparition(t, dec + .05, .3);
  let cam = piste(t, [[T.so, { x: 470, y: 980, z: 1.15 }], [dec, { x: 450, y: 1000, z: 1.35 }, E.sine], [dec + .3, { x: 440, y: 1110, z: 1.9 }, E.expoOut], [T.ba, { x: 440, y: 1110, z: 2.0 }, E.sine]]);
  camera('so-cam', cam.x + bruit(t, 11) * 2, cam.y + bruit(t, 12) * 2, cam.z, 0, tremble(t, T.ba - .25, 20, .25));
  if (t > dec - .02 && t < dec + .3) { L.soir.setAttribute('filter', 'url(#desature)'); attr('desatM', 'values', mix(.05, 1, clamp((t - dec - .1) / .2))); }
  op('so-halo', clamp((t - dec - .15) / .5) * .5);
  tr('so-lampe', `rotate(${Math.sin(t * 2.2) * 2} 440 -400)`);
  $('so-heure').textContent = t < T.so + 1.0 ? '22:47' : '22:48';
  let tasses = ''; const n = Math.min(4, 1 + Math.floor(u / .45));
  for (let i = 0; i < n; i++) { const a = apparition(t, T.so + i * .45, .25); tasses += `<g transform="translate(${840 - i * 8},${1236 - i * 64}) scale(${a})"><path d="M-40,-60 L40,-60 L34,0 C32,8 -32,8 -34,0 Z" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><path d="M40,-46 C64,-46 64,-14 36,-14" stroke="#1b1f2a" stroke-width="5" fill="none"/></g>`; }
  $('so-tasses').innerHTML = tasses;
  const dodo = Math.max(0, Math.sin(u * 2.4));
  const p = { x: 440, y: 1520 + dodo * 16, s: 1.15, cligne: clignementG(t, 5), paup: .6, sourcils: -.4, froncer: .6, bouche: dodo > .7 ? 'baille' : ['ouvert', 'plat', 'o', 'plat'][Math.floor(t * 7) % 4],
    mainD: { x: 165, y: -405 }, coudeD: 1, objetD: 'tel', formeD: 'tient', mainG: { x: -190, y: -200 }, telCeinture: false, ombre: 0, pench: -dodo * 6, regardX: .4, regardY: .4, queue: 10 };
  let fx = '';
  if (t < dec) fx += [0, 1, 2].map(i => { const k = ((u * .6 + i / 3) % 1); return `<text x="${600 + k * 120}" y="${640 - k * 220}" font-family="Bricolage" font-weight="800" font-size="${44 + i * 14}" fill="#fff" opacity="${Math.sin(Math.PI * k) * .8}">z</text>`; }).join('');
  if (t > dec) {
    const leve = E.backS(clamp((t - dec - .15) / .35));
    Object.assign(p, { y: 1520 - leve * 30, paup: 0, oeil: 1 + .35 * declic * (1 - clamp((t - dec - .9) / .4)), sourcils: 1.3 * declic, froncer: 0, regardX: 0, regardY: 0, pench: 0, casque: -30 * Math.sin(Math.PI * clamp((t - dec) / .3)),
      bouche: t > dec + .9 ? 'sourire' : 'o', mainD: { x: 210, y: -480 }, objetD: null, formeD: 'pointe', coudeD: 1 });
    fx += onomatopee(440, 380, '?!', t, dec + .1, .9, '#ffd23f', 150, 6) + etincelles(440, 560, t, dec + .15, 8, 300, '#ffd23f', .8);
    // coup de queue sur la table : il se lance
    if (t > T.ba - .35) { p.queue = -70 * Math.sin(Math.PI * clamp((t - T.ba + .35) / .3)); p.bouche = 'rire'; fx += impact(640, 1250, t, T.ba - .2, 1.2, '#ffd23f') + onomatopee(700, 1150, 'PAF !', t, T.ba - .2, .3, '#fff', 100, 8); }
  }
  PERS.gs.poser(p);
  $('so-fx').innerHTML = fx;
}

// ---------------------------------- 8 et 10. LE BARRAGE ----------------------------------
function rBarrage(t, phase) {
  let fx = '', buches = '';
  const nb = 12;
  if (phase === 'a') {
    // les bûches volent et s'empilent en barrage, Gaspard les attrape et les pose
    const u = t - T.ba;
    camera('ba-cam', 540, 1050, mix(1.25, 1.0, E.out(clamp(u / 1))));
    for (let i = 0; i < nb; i++) {
      const t0 = i * .08, k = clamp((u - t0) / .28), pose = posBuche(i);
      if (k <= 0) continue;
      const x = mix(-300, pose.x, E.out(k)), y = mix(400, pose.y, E.in(k)) - Math.sin(Math.PI * k) * 300;
      buches += `<g transform="translate(${x},${y}) rotate(${(1 - k) * 360})"><rect x="-90" y="-34" width="180" height="68" rx="34" fill="#9a5a2e"/><rect x="-80" y="-30" width="150" height="10" rx="5" fill="#b8733f"/><ellipse cx="90" cy="0" rx="20" ry="34" fill="#e2b07a"/><circle cx="90" cy="0" r="12" fill="none" stroke="#9a5a2e" stroke-width="4"/></g>`;
      if (k >= 1) fx += poussiere(pose.x, pose.y + 20, t, T.ba + t0 + .3, 4, .5, '#e9d6b8', .35);
    }
    const s = saut(t, T.ba + .3, .2, 100);
    PERS.gb.poser({ x: 700, y: 1200 + s.y, s: .95, sx: s.sx, sy: s.sy, bouche: 'rire', oeil: 1.1, paup: 0, sourcils: 1, queue: Math.sin(t * 14) * 30, mainG: { x: -220, y: -470 }, mainD: { x: 220, y: -470 }, coudeG: 1, coudeD: -1, formeG: 'salut', formeD: 'salut', regardX: -.8 });
    fx += onomatopee(540, 600, 'BARRAGE !', t, T.ba + .5, .7, '#ffd23f', 104, -4);
    // le flot de questions arrive de la gauche
    fx += flots(t, false);
  } else {
    // 10 : les questions heurtent le barrage et se changent en coches ; seuls les devis dorés passent jusqu'à Gaspard
    const u = t - T.fil;
    camera('ba-cam', 600, 1050, mix(1.1, 1.18, clamp(u / 3)));
    for (let i = 0; i < nb; i++) { const pose = posBuche(i); buches += `<g transform="translate(${pose.x},${pose.y})"><rect x="-90" y="-34" width="180" height="68" rx="34" fill="#9a5a2e"/><rect x="-80" y="-30" width="150" height="10" rx="5" fill="#b8733f"/><ellipse cx="90" cy="0" rx="20" ry="34" fill="#e2b07a"/></g>`; }
    buches += `<g transform="translate(470,990)"><rect x="-90" y="-150" width="180" height="300" rx="30" fill="#15181e"/><rect x="-78" y="-138" width="156" height="276" rx="22" fill="#f5f0e8"/><image href="photos/hero.webp" x="-74" y="-110" width="148" height="100" preserveAspectRatio="xMidYMid slice"/><rect x="-66" y="10" width="110" height="12" rx="6" fill="#15181e"/><rect x="-66" y="34" width="132" height="34" rx="17" fill="#e8b84a"/></g>`;
    fx += flots(t, true);
    // devis dorés qui passent par le haut
    const tVrais = M('l10', 7);
    for (let i = 0; i < 3; i++) {
      const t0 = T.fil + .4 + i * .65, k = clamp((t - t0) / .8);
      if (k <= 0 || k >= 1) continue;
      fx += `<g transform="translate(${mix(380, 800, k)},${1100 - Math.sin(Math.PI * k) * 260}) rotate(${mix(-15, 10, k)}) scale(.55)">${carteDevis(1)}</g>`;
    }
    const tient = t > tVrais;
    PERS.gb.poser({ x: 860, y: 1230, s: .95, bouche: tient ? 'ouvert' : 'sourire', paup: .1, sourcils: .5, regardX: -.6, queue: Math.sin(t * 5) * 12,
      mainD: tient ? { x: 160, y: -400 } : { x: 210, y: -170 }, coudeD: 1, objetD: tient ? 'tel' : null, formeD: tient ? 'tient' : 'ouverte', mainG: { x: -200, y: -300 }, formeG: 'pouce', telCeinture: !tient });
    if (tient) fx += `<g transform="translate(990,780) scale(${apparition(t, tVrais, .3)})"><rect x="-150" y="-50" width="300" height="100" rx="30" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><text y="12" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="36" fill="#1f8f5a">Vrai chantier ✓</text></g>`;
  }
  $('ba-buches').innerHTML = buches;
  $('ba-fx').innerHTML = fx;
}
function posBuche(i) { const rang = i < 5 ? 0 : i < 9 ? 1 : i < 11 ? 2 : 3, k = i - [0, 5, 9, 11][rang]; return { x: 250 + rang * 45 + k * 95, y: 1290 - rang * 66 }; }
function flots(t, filtre) {
  let s = '';
  const qs = ['Boulogne ?', 'Prix ?', 'Photos ?', 'Délais ?', 'Dispo ?', 'Zone ?'];
  for (let i = 0; i < 9; i++) {
    const per = 2.0, u = ((t * .55 + i / 9) % 1);
    const x = -260 + u * 700, y = 1180 + Math.sin(u * 8 + i) * 30 + (i % 3) * 40;
    if (filtre && x > 300) {
      // touché : la bulle devient une coche verte qui tombe dans le bassin
      const k = clamp((x - 300) / 140);
      s += `<g transform="translate(${300 + k * 120},${y + k * 120}) scale(${1 - k * .3})" opacity="${1 - k}"><circle r="30" fill="#1f8f5a"/><path d="M-13,1 l9,9 l17,-19" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g>`;
      continue;
    }
    s += `<g transform="translate(${x},${y}) rotate(${Math.sin(t * 2 + i) * 8})"><rect x="-90" y="-34" width="180" height="68" rx="30" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><text y="10" text-anchor="middle" font-family="Geist" font-weight="700" font-size="28" fill="#1b1f2a">${qs[i % 6]}</text></g>`;
  }
  return s;
}

// ---------------------------------- 8 bis. LE SITE ----------------------------------
function rSite(t) {
  $('tel').style.display = 'block';
  const mots = [M('l08', 4), M('l08', 6), M('l08', 10)];
  const entree = E.backS(clamp((t - T.si) / .55));
  const defile = piste(t, [[T.si, 0], [mots[0] - .15, 30], [mots[0] + .35, SITE_Y.sZone - 70, E.expoIo], [mots[1] + .35, SITE_Y.sReal - 70, E.expoIo], [mots[2] + .35, SITE_Y.sDelais - 70, E.expoIo]]);
  const poignee = piste(t, [[mots[1] + .3, .82], [mots[1] + .9, .18, E.io], [mots[2], .5, E.io]]);
  siteEtat({ defile, poignee, photos: 4, envoi: 0, ok: 0, rayon: clamp((t - mots[0] - .1) / .5), collant: 0, frappe: 1, chip: 1, morph: 0, t });
  const flotte = Math.sin(t * 1.6) * 8;
  $('tel').style.transform = `translate(-50%,-50%) translate(540px,${800 + (1 - entree) * 600 + flotte}px) perspective(2200px) rotateY(${(1 - entree) * 50 + Math.sin(t * .9) * 4}deg) rotateX(${Math.sin(t * .7) * 3}deg) rotateZ(${(1 - entree) * -10}deg) scale(${.6 + .92 * entree})`;
  $('telReflet').style.transform = `translateX(${-500 + (((t - T.si) % 3.2) / 3.2) * 1400}px) rotate(20deg)`;
  const lab = [['Ta zone', -1], ['Tes chantiers en photos', 1], ['Tes délais', -1]], ys = [330, 560, 790];
  let s = '';
  lab.forEach(([txt, sx], i) => {
    const a = apparition(t, mots[i] - .05, .35); if (a <= 0) return;
    const w = txt.length * 20 + 110, x = sx > 0 ? 1050 - w : 30, y = ys[i];
    s += `<g transform="translate(${x + w / 2},${y}) rotate(${(1 - a) * sx * 14 + sx * -3}) scale(${a}) translate(${-w / 2},0)"><rect x="0" y="-42" width="${w}" height="84" rx="42" fill="#15181e" stroke="#fff" stroke-width="5"/><circle cx="42" cy="0" r="26" fill="#e8b84a"/><path d="M30,0 l9,9 l16,-18" stroke="#15181e" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/><text x="84" y="12" font-family="Bricolage" font-weight="800" font-size="34" fill="#fff">${txt}</text></g>` + etincelles(x + w / 2, y, t, mots[i], 6, 150, '#e8b84a', .6);
  });
  $('svgHaut').innerHTML = s;
  for (let i = 0; i < 6; i++) tr('sf-f' + i, `translate(${[120, 960, 140, 950, 880, 200][i]},${[260, 400, 1300, 1200, 1700, 1750][i] + Math.sin(t * 1.3 + i) * 20}) rotate(${t * 30 * (i % 2 ? 1 : -1)})`);
}

// ---------------------------------- 9. LE PROSPECT SUR SON CANAPÉ ----------------------------------
function rCanape(t) {
  const u = t - T.cn, conv = M('l09', 4), dem = M('l09', 6), tEnv = T.vol - .55;
  // plan large sur la prospecte, puis plongée sur son téléphone (le site s'y affiche en grand)
  const gros = E.expoIo(clamp((t - (conv - .3)) / .45));
  camera('cn-cam', mix(540, 600, gros), mix(1050, 700, gros), mix(1.05, 2.4, gros));
  const scroll = clamp(u / 1.2);
  PERS.pr.poser({ x: 540, y: 1640, s: 1.0, cligne: clignementG(t, 3), mainG: { x: -40, y: -420 }, mainD: { x: 60, y: -430 }, coudeG: -1, coudeD: 1, formeD: 'tel', angleMainD: 180, tete: 6, regardY: 1, regardX: .2,
    bouche: t > conv - .2 ? 'sourire' : 'ferme', sourcils: t > conv - .2 ? .8 : 0, oeil: t > conv - .2 ? 1.1 : 1, piedG: { x: -70, y: -70 }, piedD: { x: 70, y: -70 }, hanche: -340 });
  let fx = '';
  if (t > conv - .3 && t < conv + .4) fx += etincelles(600, 640, t, conv - .1, 7, 160, '#ffd23f', .6);
  $('cn-fx').innerHTML = fx;
  // le téléphone HTML prend le relais : formulaire rempli, envoyé, confirmé
  op(L.canape, 1 - clamp((gros - .55) / .3));
  if (gros > .55) vis(L.siteFond, true);
  if (gros > .6) {
    $('tel').style.display = 'block';
    const frappe = clamp((t - dem + .1) / .5), chip = t > dem - .3 ? 1 : 0, morph = clamp((t - tEnv) / .25), ok = clamp((t - tEnv - .25) / .35);
    siteEtat({ defile: SITE_Y.sForm - 40, poignee: .5, photos: clamp((t - dem - .1) / .4) * 4, envoi: 0, ok, rayon: 1, collant: 1, frappe, chip, morph, t });
    $('tel').style.transform = `translate(-50%,-50%) translate(540px,${800 + Math.sin(t * 1.4) * 6}px) perspective(2200px) rotateY(${-6 + Math.sin(t) * 3}deg) rotateX(4deg) scale(1.42)`;
    let s = confettis(540, 820, t, tEnv + .3, 30, 1.2);
    // la carte de devis jaillit de l'écran
    const kc = clamp((t - (T.vol - .2)) / .3);
    if (kc > 0) s += `<g transform="translate(540,${820 - kc * 700}) scale(${.4 + kc * .8}) rotate(${kc * -10})">${carteDevis(1)}</g>`;
    $('svgHaut').innerHTML = s;
  }
}
function rVol(t) {
  const u = clamp((t - T.vol) / (T.ret - T.vol));
  tr('vo-toits', `translate(${-u * 500},${u * 120})`);
  const x = mix(-100, 1200, E.io(u)), y = mix(900, 500, u) - Math.sin(u * Math.PI) * 300;
  $('vo-carte').innerHTML = `<path d="M-100,900 Q540,${400} ${x},${y}" stroke="#fff" stroke-width="8" stroke-dasharray="20 16" fill="none" opacity=".7"/><g transform="translate(${x},${y}) rotate(${-20 + Math.sin(u * 12) * 10})">${carteDevis(1.1)}</g>`;
  $('vo-fx').innerHTML = lignesVitesse(t, T.vol, T.ret - T.vol, x, y, '#fff', .5);
}

// ---------------------------------- FIN ----------------------------------
function rFin(t) {
  const u = t - T.fin;
  const a = apparition(u, .1, .4), b = apparition(u, .4, .4), d = clamp((u - 1.1) / .4);
  tr('fi-a', `translate(540,360) scale(${a}) translate(-540,-360)`);
  tr('fi-b', `translate(0,${(1 - b) * 40})`); op('fi-b', b);
  tr('fi-pill', `translate(398,${500 - Math.abs(Math.sin(u * 4)) * 14 * clamp(u - 1)})`); tr('fi-pillIn', `scale(${b}) rotate(${(1 - b) * -12 + Math.sin(u * 3) * 3})`);
  const s = saut(u, .55, .2, 80), arrive = apparition(u, .3, .45);
  PERS.gf.poser({ x: 540, y: 1460 + s.y + (1 - arrive) * 700, s: 1.25, sx: s.sx, sy: s.sy, bouche: 'rire', paup: 0, oeil: 1.05, sourcils: .8, queue: Math.sin(u * 8) * 18,
    mainD: { x: 230 + Math.sin(u * 12) * 30, y: -560 }, coudeD: 1, formeD: 'salut', mainG: { x: -220, y: -170 }, cligne: clignementG(t, 1), ombre: .3 });
  op('fi-logo', d);
  $('fi-fx').innerHTML = etincelles(398, 500, t, T.fin + .45, 8, 200, '#e8b84a', .7);
}

// ---------------------------------- INTERFACE ----------------------------------
function rUI(t) {
  const n0 = T.ret + .05;
  const a = apparition(t, n0, .4), b = clamp((t - (T.ret + 1.0)) / .3);
  vis('notif', t > n0 && b < 1);
  tr('notif', `translate(0,${mix(-260, 110, a) - E.in(b) * 400})`);
  return '';
}

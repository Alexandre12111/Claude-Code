// Montage V2 de l'épisode 1. Repères temporels calés sur la voix : P(id) début de phrase, F(id) fin, M(id, k) début du mot k.
function construireCalage(script, cal) {
  const ph = {};
  script.phrases.forEach((p, i) => {
    let debut, fin, mots;
    if (cal && cal.phrases[p.id]) ({ debut, fin, mots } = cal.phrases[p.id]);
    else { debut = p.debut_prevu; fin = (script.phrases[i + 1] ? script.phrases[i + 1].debut_prevu : debut + 3) - .3; }
    const toks = jetons(p.sous_titre);
    if (!mots || mots.length !== toks.length) {
      const poids = toks.map(m => m.length + 2), tot = poids.reduce((a, b) => a + b, 0);
      let c = debut; mots = toks.map((m, k) => { const d = (fin - debut) * poids[k] / tot; const o = { debut: c, fin: c + d }; c += d; return o; });
    }
    ph[p.id] = { debut, fin, mots };
  });
  const der = ph[script.phrases[script.phrases.length - 1].id];
  return { phrases: ph, duree: (cal && cal.duree) || der.fin + .6 + script.fin.duree, finTexte: der.fin + .45 };
}
// Mots du sous-titre (la ponctuation isolée est rattachée au mot précédent)
function jetons(st) {
  const out = [];
  for (const b of st.replace(/\*/g, '').split(/\s+/)) { if (/^[?!:;.,…]+$/.test(b) && out.length) out[out.length - 1] += ' ' + b; else if (b) out.push(b); }
  return out;
}
const P = id => CAL.phrases[id].debut, F = id => CAL.phrases[id].fin;
const M = (id, k) => CAL.phrases[id].mots[Math.min(k, CAL.phrases[id].mots.length - 1)].debut;
const L = {};
function calque(id, html, parent = 'svg') { const g = el($(parent), html); g.id = id; L[id] = g; return g; }
function camera(id, cx, cy, z, r = 0, sh = { x: 0, y: 0, r: 0 }) { tr(id, `translate(${540 + sh.x},${960 + sh.y}) rotate(${r + sh.r}) scale(${z}) translate(${-cx},${-cy})`); }
const PERSOS = {};

// ======================================================================================
//                                   CONSTRUCTION
// ======================================================================================
function construireScenes() {
  $('defs').innerHTML = `
    <clipPath id="clipIris"><circle id="irisC" cx="540" cy="760" r="0"/></clipPath>
    <filter id="desature"><feColorMatrix id="desatM" type="saturate" values="1"/></filter>
    <linearGradient id="gCiel" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5fb8f0"/><stop offset="1" stop-color="#bfe6fb"/></linearGradient>
    <linearGradient id="gNuit" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1b2347"/><stop offset="1" stop-color="#2e3a6b"/></linearGradient>
    <radialGradient id="gLampe" cx=".5" cy="0" r="1"><stop offset="0" stop-color="#ffd98a" stop-opacity=".9"/><stop offset=".6" stop-color="#ffc35c" stop-opacity=".18"/><stop offset="1" stop-color="#ffc35c" stop-opacity="0"/></radialGradient>
    <radialGradient id="gHalo" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#ffe7a3" stop-opacity=".95"/><stop offset="1" stop-color="#ffe7a3" stop-opacity="0"/></radialGradient>`;

  // ---------------- HOOK : le téléphone qui saute sur la planche ----------------
  let planche = '';
  for (let i = 0; i < 4; i++) planche += `<rect x="-300" y="${-400 + i * 700}" width="1700" height="680" rx="20" fill="${['#e9a75e', '#e19c52', '#eaab62', '#df9a4f'][i]}"/>
    ${[0, 1, 2, 3, 4].map(k => `<path d="M-300,${-340 + i * 700 + k * 130} C200,${-330 + i * 700 + k * 130} 700,${-360 + i * 700 + k * 130} 1400,${-338 + i * 700 + k * 130}" stroke="#c9843f" stroke-width="7" fill="none" stroke-linecap="round" opacity=".45"/>`).join('')}
    <circle cx="${200 + i * 230}" cy="${-150 + i * 700}" r="22" fill="none" stroke="#c9843f" stroke-width="7" opacity=".45"/>`;
  const splats = [[160, 260, 70], [880, 420, 50], [140, 1500, 90], [930, 1650, 60], [770, 1180, 40], [260, 820, 34]].map(([x, y, r]) => `<g transform="translate(${x},${y})"><circle r="${r}" fill="#f4efe6"/><circle cx="${r * .9}" cy="${-r * .4}" r="${r * .32}" fill="#f4efe6"/><circle cx="${-r * .7}" cy="${r * .8}" r="${r * .25}" fill="#f4efe6"/><circle cx="${r * .3}" cy="${r * 1.2}" r="${r * .15}" fill="#f4efe6"/></g>`).join('');
  const notifs = Array.from({ length: 6 }, (_, i) => `<g id="hk-n${i}"><rect x="-300" y="-46" width="600" height="92" rx="28" fill="#fff"/><circle cx="-246" cy="0" r="28" fill="#ff4d4f"/><path d="M-258,-6 c8,10 16,10 24,0" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" transform="rotate(180 -246 0)"/>
    <text x="-200" y="-6" font-family="Geist" font-weight="700" font-size="30" fill="#15181e">Appel manqué</text><text x="-200" y="28" font-family="Geist" font-size="24" fill="#7a7166">06 ${['12 48', '77 03', '45 19', '90 22', '31 64', '58 07'][i]} •• ••</text><text x="270" y="-6" text-anchor="end" font-family="Geist" font-size="22" fill="#9a9184">${['8:41', '8:47', '8:52', '8:55', '8:58', '9:00'][i]}</text></g>`).join('');
  calque('hook', `<g id="hk-cam">${planche}${splats}
    <g transform="translate(860,1520) rotate(-20)"><rect x="-26" y="-190" width="52" height="150" rx="22" fill="#8a5432"/><path d="M-160,-50 L160,-50 L150,40 L-150,40 Z" fill="#cfd6de"/><path d="M-160,-50 L160,-50 L158,-38 L-158,-38 Z" fill="#fff"/><path d="M-120,26 C-40,46 60,16 140,34 L150,40 L-150,40 Z" fill="#f4efe6"/></g>
    <g id="hk-ombreTel"><ellipse cx="0" cy="0" rx="230" ry="60" fill="#000" opacity=".2"/></g>
    <g id="hk-tel"><g id="hk-telIn">
      <rect x="-200" y="-400" width="400" height="800" rx="70" fill="#15181e"/><rect x="-186" y="-386" width="372" height="772" rx="58" fill="#202634"/>
      <rect x="-60" y="-372" width="120" height="34" rx="17" fill="#000"/>
      <text id="hk-heure" x="0" y="-200" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="120" fill="#fff">9:00</text>
      <text x="0" y="-150" text-anchor="middle" font-family="Geist" font-size="30" fill="#aab3c5">mardi 18 novembre</text>
      <circle cx="0" cy="40" r="78" fill="#3b465c"/><circle cx="0" cy="22" r="28" fill="#aab3c5"/><path d="M-44,90 c0,-50 88,-50 88,0" fill="#aab3c5"/>
      <text x="0" y="180" text-anchor="middle" font-family="Geist" font-weight="700" font-size="38" fill="#fff">Numéro inconnu</text>
      <g transform="translate(-110,300)"><circle r="54" fill="#ff4d4f"/><path d="M-24,4 c16,-16 32,-16 48,0" stroke="#fff" stroke-width="10" fill="none" stroke-linecap="round"/></g>
      <g transform="translate(110,300)"><circle r="54" fill="#22c55e"/><path d="M-24,-4 c16,16 32,16 48,0" stroke="#fff" stroke-width="10" fill="none" stroke-linecap="round"/></g>
    </g></g>
    <g id="hk-notifs">${notifs}</g>
    <g id="hk-badge"><circle r="110" fill="#ff4d4f"/><circle r="110" fill="none" stroke="#fff" stroke-width="10"/><text id="hk-nb" y="40" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="118" fill="#fff">14</text></g>
    <g id="hk-fx"></g></g>`);

  // ---------------- EXTÉRIEUR : façade, échafaudage, artisan ----------------
  const fenetres = [];
  for (let r = 0; r < 9; r++) for (let c = 0; c < 5; c++) {
    const x = -220 + c * 320, y = 260 + r * 330;
    fenetres.push(`<g transform="translate(${x},${y})"><rect x="-14" y="-14" width="148" height="238" rx="10" fill="#ead2a6"/><path d="M0,60 C0,10 120,10 120,60 V210 H0 Z" fill="#4c6fa8"/><path d="M14,64 C16,34 60,26 70,30 L20,150 Z" fill="#fff" opacity=".22"/><path d="M60,22 V210 M0,120 H120" stroke="#ead2a6" stroke-width="9"/>
      <rect x="-30" y="196" width="180" height="16" rx="6" fill="#2a2f3a"/>${[0, 1, 2, 3, 4, 5, 6].map(k => `<rect x="${-22 + k * 26}" y="212" width="6" height="44" fill="#2a2f3a"/>`).join('')}<rect x="-30" y="252" width="180" height="10" rx="5" fill="#2a2f3a"/></g>`);
  }
  const facadeHTML = `<rect x="-400" y="100" width="1880" height="3200" fill="#f6e2bc"/>${Array.from({ length: 10 }, (_, i) => `<rect x="-400" y="${150 + i * 330}" width="1880" height="10" fill="#ead2a6"/>`).join('')}${fenetres.join('')}
    <path d="M-420,110 L1500,110 L1440,-80 L-360,-80 Z" fill="#6f7f95"/>${[-200, 140, 480, 820, 1160].map(x => `<g transform="translate(${x},-150)"><rect width="120" height="170" rx="12" fill="#f6e2bc"/><path d="M-14,0 L60,-60 L134,0 Z" fill="#5a6a80"/><path d="M20,50 C20,26 100,26 100,50 V150 H20 Z" fill="#4c6fa8"/></g>`).join('')}
    <rect x="-420" y="96" width="1920" height="28" rx="8" fill="#ead2a6"/>`;
  const echaf = (y) => `<rect x="-60" y="${y - 250}" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/><rect x="-60" y="${y - 140}" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/>
    <rect x="-80" y="${y - 6}" width="1240" height="40" rx="8" fill="#e8a65c"/><rect x="-80" y="${y - 6}" width="1240" height="10" rx="5" fill="#f7c27f"/><rect x="-80" y="${y + 34}" width="1240" height="44" fill="#ff7b2c"/><rect x="-80" y="${y + 34}" width="1240" height="10" fill="#ff9d5c"/>`;
  calque('ext', `<g id="ex-cam">
    <rect x="-1500" y="-2600" width="4000" height="2800" fill="url(#gCiel)"/>
    <circle cx="880" cy="-520" r="190" fill="#ffe7a3"/><circle cx="880" cy="-520" r="260" fill="#ffe7a3" opacity=".25"/>
    <g id="ex-nuages">${[[-200, -700, 1], [500, -1000, 1.3], [1200, -620, .9], [-700, -400, 1.1]].map(([x, y, s]) => `<g transform="translate(${x},${y}) scale(${s})"><rect x="-170" y="-50" width="340" height="100" rx="50" fill="#fff"/><circle cx="-40" cy="-60" r="70" fill="#fff"/><circle cx="60" cy="-40" r="56" fill="#fff"/></g>`).join('')}</g>
    <path d="M-1500,140 L-900,140 L-900,40 L-780,40 L-780,-20 L-640,-20 L-640,60 L-500,60 L-500,140 L2500,140 L2500,300 L-1500,300 Z" fill="#a9c7de"/>
    <g transform="translate(-1150,-260)" fill="#a9c7de"><path d="M0,400 L60,0 L120,400 Z"/><rect x="40" y="-40" width="40" height="60"/></g>
    ${facadeHTML}
    <rect x="-1500" y="3300" width="4000" height="900" fill="#7b8494"/><rect x="-1500" y="3300" width="4000" height="40" fill="#b9bfc8"/>
    <g transform="translate(660,3360)"><rect x="0" y="-190" width="440" height="190" rx="30" fill="#fff"/><path d="M440,-140 L530,-140 L566,-60 L566,0 L440,0 Z" fill="#f1f1ee"/><path d="M452,-128 L520,-128 L548,-66 L452,-66 Z" fill="#7aa3d1"/><rect x="40" y="-140" width="300" height="60" rx="14" fill="#ff7b2c"/><text x="190" y="-98" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="34" fill="#fff">RÉNOVATION</text><circle cx="110" cy="6" r="44" fill="#1b1f2a"/><circle cx="110" cy="6" r="18" fill="#c9d2dc"/><circle cx="470" cy="6" r="44" fill="#1b1f2a"/><circle cx="470" cy="6" r="18" fill="#c9d2dc"/></g>
    <rect x="16" y="-200" width="34" height="4200" rx="14" fill="#c9d2dc" stroke="#8a96a5" stroke-width="5"/><rect x="1030" y="-200" width="34" height="4200" rx="14" fill="#c9d2dc" stroke="#8a96a5" stroke-width="5"/>
    ${echaf(2100)}${echaf(2900)}
    <rect x="-60" y="1050" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/><rect x="-60" y="1160" width="1200" height="16" rx="8" fill="#c9d2dc" stroke="#8a96a5" stroke-width="4"/>
    <path d="M150,1080 C240,1030 380,1040 430,1100 L430,1300 L150,1300 Z" fill="#fffaf2"/>
    <g id="ex-seau" transform="translate(880,1300)"><path d="M-62,-110 L62,-110 L52,0 L-52,0 Z" fill="#ff7b2c"/><rect x="-66" y="-122" width="132" height="20" rx="8" fill="#e0611a"/><ellipse cx="0" cy="-118" rx="54" ry="10" fill="#f4efe6"/><path d="M-52,-118 C-50,-190 50,-190 52,-118" stroke="#8a96a5" stroke-width="6" fill="none"/></g>
    <g id="ex-pigeon"></g>
    <g id="ex-ar"></g>
    <rect x="-80" y="1294" width="1240" height="40" rx="8" fill="#e8a65c"/><rect x="-80" y="1294" width="1240" height="10" rx="5" fill="#f7c27f"/><rect x="-80" y="1334" width="1240" height="50" fill="#ff7b2c"/><rect x="-80" y="1334" width="1240" height="10" fill="#ff9d5c"/>
    <g id="ex-mascotte"></g>
    <g id="ex-fx"></g>
  </g>`);
  const ar = new Pantin('ar', 'artisan'); PERSOS.ar = ar; $('ex-ar').innerHTML = ar.svg();

  // ---------------- CHANTIER ----------------
  const carreaux = Array.from({ length: 14 }, (_, r) => Array.from({ length: 6 }, (_, c) => `<rect x="${-40 + c * 96}" y="${180 + r * 76}" width="92" height="72" rx="6" fill="${(r + c) % 2 ? '#d9eef0' : '#cbe5e8'}"/>`).join('')).join('');
  calque('chantier', `<g id="ch-cam">
    <rect x="-400" y="-200" width="1900" height="1600" fill="#e9dcc6"/>
    <g clip-path="url(#ch-clipMur)">${carreaux}</g><clipPath id="ch-clipMur"><path d="M-400,170 L470,170 L520,420 L470,700 L540,980 L480,1260 L-400,1260 Z"/></clipPath>
    ${[[640, 330], [800, 520], [700, 820], [900, 1000]].map(([x, y]) => `<path d="M${x},${y} q60,-30 120,0 q60,30 120,0" stroke="#d6c4a6" stroke-width="10" fill="none" stroke-linecap="round"/>`).join('')}
    <g transform="translate(700,150)"><rect width="320" height="420" rx="16" fill="#fff"/><rect x="20" y="20" width="280" height="380" rx="10" fill="#9fd4f2"/><path d="M160,20 V400 M20,210 H300" stroke="#fff" stroke-width="14"/></g>
    <g id="ch-horloge" transform="translate(260,330)"><circle r="120" fill="#1b1f2a"/><circle r="104" fill="#fff"/>${Array.from({ length: 12 }, (_, i) => `<rect x="-4" y="-96" width="8" height="${i % 3 ? 12 : 22}" rx="4" fill="#1b1f2a" transform="rotate(${i * 30})"/>`).join('')}
      <g id="ch-h"><rect x="-7" y="-56" width="14" height="62" rx="7" fill="#1b1f2a"/></g><g id="ch-m"><rect x="-5" y="-86" width="10" height="92" rx="5" fill="#ff4d4f"/></g><circle r="12" fill="#ff7b2c"/><g id="ch-hFlou"></g></g>
    <g id="ch-calendrier" transform="translate(560,240)"><rect x="-80" y="-20" width="160" height="190" rx="12" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><rect x="-80" y="-20" width="160" height="50" rx="12" fill="#ff4d4f"/><text id="ch-jour" x="0" y="130" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="90" fill="#1b1f2a">12</text><g id="ch-pages"></g></g>
    <rect x="-400" y="1260" width="1900" height="900" fill="#c99c6b"/>${Array.from({ length: 6 }, (_, i) => `<rect x="-400" y="${1300 + i * 110}" width="1900" height="8" fill="#b4875a"/>`).join('')}
    <g transform="translate(150,1360)"><rect x="-90" y="-120" width="180" height="120" rx="10" fill="#d8a96c"/><rect x="-90" y="-120" width="180" height="20" fill="#c08f52"/><rect x="-110" y="-250" width="150" height="130" rx="10" fill="#e2b57a"/></g>
    <g transform="translate(480,1440)"><path d="M-62,-110 L62,-110 L52,0 L-52,0 Z" fill="#4c7bd9"/><rect x="-66" y="-122" width="132" height="20" rx="8" fill="#3a63b5"/><ellipse cx="0" cy="-118" rx="54" ry="10" fill="#e7e2d6"/></g>
    <g id="ch-ar2"></g>
    <g id="ch-cl"></g>
    <g id="ch-fx"></g>
  </g>
  <g id="ch-carte" transform="translate(540,190)"><g id="ch-carteIn"><rect x="-300" y="-80" width="600" height="160" rx="36" fill="#fff" stroke="#1b1f2a" stroke-width="6"/>
    <text x="-260" y="-18" font-family="Bricolage" font-weight="800" font-size="40" fill="#1b1f2a">Avancement</text><text id="ch-pct" x="260" y="-18" text-anchor="end" font-family="Bricolage" font-weight="800" font-size="40" fill="#ff4d4f">40 %</text>
    <rect x="-260" y="20" width="520" height="34" rx="17" fill="#eee6d8"/><rect id="ch-barre" x="-260" y="20" width="208" height="34" rx="17" fill="#ff7b2c"/></g></g>`);
  const ar2 = new Pantin('ar2', 'artisan'); PERSOS.ar2 = ar2; $('ch-ar2').innerHTML = ar2.svg();
  const cl = new Pantin('cl', 'cliente'); PERSOS.cl = cl; $('ch-cl').innerHTML = cl.svg();

  // ---------------- PROSPECT : l'appel sans réponse, puis le concurrent ----------------
  const carteRes = (y, nom, note, avis, site, id) => `<g transform="translate(-260,${y})" id="${id}"><rect width="520" height="250" rx="32" fill="#fff"/>
    <rect x="24" y="24" width="96" height="96" rx="24" fill="${site ? '#15181e' : '#d8d1c4'}"/><text x="72" y="88" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="46" fill="${site ? '#e8b84a' : '#fff'}">${nom[0]}</text>
    <text x="140" y="62" font-family="Geist" font-weight="700" font-size="32" fill="#15181e">${nom}</text>
    <text x="140" y="100" font-family="Geist" font-size="24" fill="#7a7166"><tspan fill="#f2a516">★★★★★</tspan> ${note} (${avis})</text>
    <text x="140" y="134" font-family="Geist" font-weight="600" font-size="23" fill="${site ? '#1f8f5a' : '#b0a698'}">${site ? '✓ Site web · Devis en ligne' : 'Pas de site web'}</text>
    ${site ? `<g id="pr-btnSite"><rect x="24" y="168" width="230" height="58" rx="29" fill="#15181e"/><text x="139" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#fff">Voir le site</text></g><rect x="266" y="168" width="230" height="58" rx="29" fill="#e8b84a"/><text x="381" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#15181e">Devis en ligne</text>`
      : `<rect x="24" y="168" width="230" height="58" rx="29" fill="#eef2ee"/><text x="139" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#1f8f5a">Appeler</text><g id="pr-sansRep"><rect x="266" y="168" width="230" height="58" rx="29" fill="#ffe4e4"/><text x="381" y="206" text-anchor="middle" font-family="Geist" font-weight="700" font-size="24" fill="#e03b3d">Pas de réponse</text></g>`}</g>`;
  calque('prospect', `<g id="pr-cam">
    <rect x="-300" y="-300" width="1700" height="2600" fill="#f3d7b4"/>
    <rect x="-300" y="1300" width="1700" height="1000" fill="#d9a774"/>
    <g transform="translate(-60,1050)"><rect x="0" y="0" width="760" height="300" rx="70" fill="#5f8f86"/><rect x="40" y="-120" width="680" height="200" rx="70" fill="#6fa197"/></g>
    <g transform="translate(980,700)"><rect x="-14" y="0" width="28" height="640" fill="#3a3a3a"/><path d="M-130,0 L130,0 L80,-180 L-80,-180 Z" fill="#ffd98a"/></g>
    <g transform="translate(160,220)"><rect width="380" height="460" rx="20" fill="#fff"/><rect x="24" y="24" width="332" height="412" rx="12" fill="#8fc6e8"/><circle cx="260" cy="130" r="50" fill="#ffe7a3"/><path d="M24,436 L140,280 L230,380 L300,300 L356,360 L356,436 Z" fill="#6fa197"/></g>
    <g id="pr-tel" transform="translate(540,880)">
      <rect x="-300" y="-620" width="600" height="1240" rx="90" fill="#15181e"/><rect x="-282" y="-602" width="564" height="1204" rx="76" fill="#f4efe6"/>
      <g clip-path="url(#pr-clip)"><clipPath id="pr-clip"><rect x="-282" y="-602" width="564" height="1204" rx="76"/></clipPath>
        <g id="pr-liste"><rect x="-282" y="-602" width="564" height="1204" fill="#f4efe6"/>
          <rect x="-250" y="-500" width="500" height="80" rx="40" fill="#fff"/><circle cx="-200" cy="-460" r="15" fill="none" stroke="#7a7166" stroke-width="5"/><path d="M-189,-449 l13,13" stroke="#7a7166" stroke-width="5"/><text x="-166" y="-450" font-family="Geist" font-size="26" fill="#15181e">rénovation salle de bain 92</text>
          ${carteRes(-380, 'Atelier R.', '4,9', 12, false, 'pr-c1')}${carteRes(-110, 'Rénov Express', '4,7', 58, true, 'pr-c2')}<g opacity=".55">${carteRes(160, 'Maison & Bain', '4,6', 31, true, 'pr-c3')}</g></g>
        <g id="pr-appel"><rect x="-282" y="-602" width="564" height="1204" fill="#202634"/><circle cx="0" cy="-260" r="100" fill="#3b465c"/><text x="0" y="-226" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="96" fill="#fff">A</text>
          <text x="0" y="-80" text-anchor="middle" font-family="Geist" font-weight="700" font-size="46" fill="#fff">Atelier R.</text><text id="pr-etat" x="0" y="-26" text-anchor="middle" font-family="Geist" font-size="32" fill="#aab3c5">Appel en cours…</text>
          <g id="pr-points">${[0, 1, 2].map(i => `<circle id="pr-pt${i}" cx="${-40 + i * 40}" cy="60" r="12" fill="#aab3c5"/>`).join('')}</g>
          <g transform="translate(0,400)"><circle r="64" fill="#ff4d4f"/><path d="M-30,4 c20,-20 40,-20 60,0" stroke="#fff" stroke-width="12" fill="none" stroke-linecap="round"/></g></g>
      </g>
      <rect x="-90" y="-590" width="180" height="40" rx="20" fill="#000"/>
    </g>
    <g id="pr-mainG"><rect x="-420" y="0" width="140" height="70" rx="35" fill="#e3a07a" transform="translate(${540 - 10},${880 - 120}) "/><rect x="-420" y="90" width="140" height="70" rx="35" fill="#e3a07a" transform="translate(${540 - 10},${880 - 120})"/><rect x="-420" y="180" width="140" height="70" rx="35" fill="#e3a07a" transform="translate(${540 - 10},${880 - 120})"/></g>
    <g id="pr-pouce"><path d="M0,0 C-20,-30 -10,-80 30,-90 C70,-98 90,-60 80,-20 L60,140 L200,320 L320,240 L200,40 Z" fill="#e3a07a"/><ellipse cx="34" cy="-58" rx="26" ry="20" fill="#f7d3bd"/></g>
    <g id="pr-avion"></g>
    <g id="pr-fx"></g>
  </g>`);

  // ---------------- SOIR ----------------
  calque('soir', `<g id="so-cam">
    <rect x="-500" y="-400" width="2100" height="2000" fill="#2b3666"/>
    <g transform="translate(640,180)"><rect width="400" height="560" rx="24" fill="#1d2550"/><rect x="20" y="20" width="360" height="520" rx="14" fill="#141a3d"/><circle cx="270" cy="140" r="56" fill="#ffeeb8"/><circle cx="250" cy="126" r="56" fill="#141a3d"/>
      ${Array.from({ length: 14 }, (_, i) => etoile4(40 + (i * 53) % 320, 50 + (i * 37) % 220, 6 + (i % 3) * 3, '#fff8d6')).join('')}
      <path d="M20,420 L80,380 L80,330 L140,330 L140,380 L200,350 L260,390 L260,320 L330,320 L330,370 L380,350 L380,540 L20,540 Z" fill="#232c5a"/>
      ${[[60, 430], [110, 470], [180, 440], [290, 420], [320, 480]].map(([x, y]) => `<rect x="${x}" y="${y}" width="22" height="26" rx="4" fill="#ffd36b"/>`).join('')}<path d="M200,20 V540 M20,280 H380" stroke="#1d2550" stroke-width="16"/></g>
    <g transform="translate(60,520)"><rect width="300" height="150" rx="24" fill="#141a3d"/><text id="so-heure" x="150" y="104" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="76" fill="#ff9d3c">22:47</text></g>
    <g id="so-lampe"><path d="M440,-400 L440,280" stroke="#141a3d" stroke-width="7"/><path d="M340,340 C350,260 530,260 540,340 Z" fill="#e8b84a"/><path id="so-cone" d="M340,340 L540,340 L960,1290 L-80,1290 Z" fill="url(#gLampe)"/><ellipse cx="440" cy="340" rx="100" ry="14" fill="#fff4cf"/></g>
    <g id="so-ar"></g>
    <rect x="-500" y="1250" width="2100" height="70" fill="#a8693c"/><rect x="-500" y="1250" width="2100" height="16" fill="#c9844f"/><rect x="-500" y="1320" width="2100" height="800" fill="#1d2550"/>
    <g id="so-tasses"></g>
    <g transform="translate(560,1250)"><path d="M-130,0 L120,0 L130,-16 L-120,-16 Z" fill="#fff"/><path d="M-120,-16 L130,-16 L136,-30 L-114,-30 Z" fill="#f4efe6"/><text x="0" y="-38" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="24" fill="#1b1f2a">DEVIS</text></g>
    <rect id="so-halo" x="-500" y="-400" width="2100" height="2000" fill="url(#gHalo)" opacity="0"/>
    <g id="so-fx"></g>
  </g>`);
  const ar3 = new Pantin('ar3', 'artisan'); PERSOS.ar3 = ar3; $('so-ar').innerHTML = ar3.svg();

  // ---------------- FOND DU SITE (le téléphone HTML est au dessus) ----------------
  calque('siteFond', `<rect width="1080" height="1920" fill="#f3ead9"/>
    <circle cx="540" cy="760" r="620" fill="url(#gHalo)" opacity=".7"/>
    <g id="sf-formes">${[[120, 260, 90, '#e8b84a'], [960, 400, 60, '#ff7b2c'], [140, 1300, 70, '#4c7bd9'], [950, 1200, 100, '#3fb27f'], [880, 1700, 50, '#e8b84a'], [200, 1750, 40, '#ff7b2c']].map(([x, y, r, c], i) => `<g id="sf-f${i}" transform="translate(${x},${y})"><rect x="${-r / 2}" y="${-r / 2}" width="${r}" height="${r}" rx="${r * .28}" fill="${c}" opacity=".85"/></g>`).join('')}</g>
    <g id="sf-labels"></g>`);

  // ---------------- FIN ----------------
  calque('fin', `<rect width="1080" height="1920" fill="#15181e"/>
    <circle cx="540" cy="860" r="760" fill="url(#gHalo)" opacity=".1"/>
    <g id="fi-a"><text x="540" y="730" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="104" fill="#fff">Tu te reconnais ?</text></g>
    <g id="fi-b"><text x="300" y="900" text-anchor="end" font-family="Bricolage" font-weight="700" font-size="62" fill="#cfc6b8">Écris</text><text x="512" y="900" font-family="Bricolage" font-weight="700" font-size="62" fill="#cfc6b8">en commentaire</text></g>
    <g id="fi-pill"><g id="fi-pillIn"><rect x="-96" y="-50" width="192" height="96" rx="26" fill="#e8b84a"/><text x="0" y="22" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="64" fill="#15181e">SITE</text></g></g>
    <g id="fi-bulle" transform="translate(540,1110)"><path d="M-80,-56 h160 a34,34 0 0 1 34,34 v68 a34,34 0 0 1 -34,34 h-90 l-46,40 v-40 h-24 a34,34 0 0 1 -34,-34 v-68 a34,34 0 0 1 34,-34 z" fill="none" stroke="#e8b84a" stroke-width="10" stroke-linejoin="round"/>${[-44, 0, 44].map((x, i) => `<circle id="fi-p${i}" cx="${x}" cy="12" r="11" fill="#e8b84a"/>`).join('')}</g>
    <g id="fi-logo"><image href="logo-aurea-media-clair.png" x="380" y="1480" width="320" height="112"/><text x="540" y="1650" text-anchor="middle" font-family="Geist" font-size="32" fill="#8a8478">aurea-media.fr</text></g>
    <g id="fi-fx"></g>`);

  // ---------------- Interface plein écran : bulles, notification, effets ----------------
  const qs = ['Vous allez jusqu’à Boulogne ?', 'C’est combien, à peu près ?', 'Vous faites les salles de bain ?', 'Vous avez des photos ?', 'Vous êtes dispo quand ?'];
  calque('ui', qs.map((q, i) => { const w = q.length * 22 + 90; return `<g id="bq${i}"><g id="bq${i}-in"><path d="${i % 2 ? `M${w - 60},96 l26,36 l-60,-28 z` : 'M60,96 l-26,36 l60,-28 z'}" fill="#fff" stroke="#1b1f2a" stroke-width="6" stroke-linejoin="round"/><rect width="${w}" height="110" rx="40" fill="#fff" stroke="#1b1f2a" stroke-width="6"/><path d="${i % 2 ? `M${w - 58},92 l20,30 l-50,-24 z` : 'M58,92 l-20,30 l50,-24 z'}" fill="#fff"/>
      <text x="${w / 2}" y="70" text-anchor="middle" font-family="Geist" font-weight="700" font-size="40" fill="#1b1f2a">${q}</text></g></g>`; }).join('') + `
    <g id="notif"><rect x="40" y="0" width="1000" height="190" rx="44" fill="#fff" stroke="#1b1f2a" stroke-width="6"/><rect x="74" y="34" width="122" height="122" rx="30" fill="#15181e"/><text x="135" y="118" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="62" fill="#e8b84a">M</text>
      <text x="224" y="70" font-family="Geist" font-weight="600" font-size="26" fill="#8a8478">ateliermorel.fr · maintenant</text><text x="224" y="116" font-family="Bricolage" font-weight="800" font-size="42" fill="#15181e">Nouvelle demande de devis</text><text x="224" y="160" font-family="Geist" font-size="30" fill="#4b463e">Salle de bain · Boulogne · 4 photos</text></g>
    <g id="ui-fx"></g>`);
  for (const id of ['hook', 'ext', 'chantier', 'prospect', 'soir', 'siteFond', 'fin', 'ui']) $('svg').appendChild(L[id]);
}

// Mascotte « le collègue » : un téléphone avec un casque de chantier, petits bras et jambes en tubes
function mascotte(t, p) {
  const T = Pantin.prototype.tube, tube = (A, Mi, B, l, c, tr) => T.call(null, A, Mi, B, l, c, tr);
  const bras = (sx, main) => { const S = { x: sx * 120, y: -200 }; const Mi = { x: (S.x + main.x) / 2 + sx * 30, y: (S.y + main.y) / 2 + 20 }; return tube(S, Mi, main, 26, '#15181e', '#000') + `<circle cx="${main.x}" cy="${main.y}" r="24" fill="#fff" stroke="#15181e" stroke-width="6"/>`; };
  const jambe = sx => tube({ x: sx * 50, y: -40 }, { x: sx * 56, y: -10 }, { x: sx * 60, y: 0 }, 26, '#15181e', '#000') + `<ellipse cx="${sx * 74}" cy="4" rx="40" ry="18" fill="#ff7b2c"/>`;
  const visage = p.visage ?? 1;
  return `<g transform="translate(0,${p.y || 0}) rotate(${p.rot || 0}) scale(${p.sx || 1},${p.sy || 1})">
    ${jambe(-1)}${jambe(1)}
    <g transform="translate(0,-40)">
      <rect x="-130" y="-470" width="260" height="470" rx="50" fill="#15181e"/><rect x="-114" y="-454" width="228" height="438" rx="38" fill="#f5f0e8"/>
      <rect x="-114" y="-454" width="228" height="70" rx="38" fill="#15181e"/><rect x="-114" y="-414" width="228" height="30" fill="#15181e"/><circle cx="-82" cy="-420" r="16" fill="#e8b84a"/><rect x="-56" y="-428" width="100" height="14" rx="7" fill="#fff" opacity=".8"/>
      <g opacity="${1 - visage}"><image href="photos/hero.webp" x="-104" y="-372" width="208" height="150" preserveAspectRatio="xMidYMid slice"/><rect x="-100" y="-206" width="160" height="16" rx="8" fill="#15181e"/><rect x="-100" y="-180" width="120" height="12" rx="6" fill="#b5ab9c"/><rect x="-100" y="-150" width="200" height="50" rx="25" fill="#e8b84a"/><text x="0" y="-117" text-anchor="middle" font-family="Geist" font-weight="700" font-size="20" fill="#15181e">Demander un devis</text></g>
      <g opacity="${visage}" transform="translate(0,-240)"><ellipse cx="-50" cy="-30" rx="30" ry="${36 * (1 - (p.cligne || 0) * .9)}" fill="#15181e"/><ellipse cx="50" cy="-30" rx="30" ry="${36 * (1 - (p.cligne || 0) * .9)}" fill="#15181e"/><circle cx="-40" cy="-44" r="10" fill="#fff"/><circle cx="60" cy="-44" r="10" fill="#fff"/>
        ${p.bouche === 'o' ? '<ellipse cx="0" cy="70" rx="22" ry="26" fill="#15181e"/>' : p.bouche === 'plat' ? '<path d="M-40,70 h80" stroke="#15181e" stroke-width="12" stroke-linecap="round"/>' : '<path d="M-50,56 C-20,96 20,96 50,56" stroke="#15181e" stroke-width="12" stroke-linecap="round" fill="none"/>'}
        <ellipse cx="-90" cy="30" rx="18" ry="11" fill="#ff8a7a" opacity=".5"/><ellipse cx="90" cy="30" rx="18" ry="11" fill="#ff8a7a" opacity=".5"/></g>
      <g transform="translate(0,-470)"><path d="M-130,10 C-126,-110 126,-110 130,10 Z" fill="#ffd23f"/><rect x="-160" y="0" width="320" height="30" rx="15" fill="#f2b705"/><rect x="-18" y="-96" width="36" height="96" rx="12" fill="#ffe27a"/></g>
    </g>
    ${bras(-1, p.mainG || { x: -170, y: -110 })}${bras(1, p.mainD || { x: 170, y: -110 })}
  </g>`;
}

// ======================================================================================
//                                      RENDU
// ======================================================================================
const T = {};  // repères calculés une fois
function reperes() {
  T.whip = P('v2') - .12; T.ch = P('v5') - .14; T.pr = P('v6') - .14; T.so = P('v7') - .2; T.dec = P('v8');
  T.si = P('v9') - .3; T.ap = P('v10') - .32; T.fin = CAL.finTexte;
}

function rendre(t) {
  if (!T.whip) reperes();
  for (const id in L) vis(L[id], false);
  vis(L.ui, true);
  $('flash').style.opacity = 0; $('fondu').style.opacity = 0;
  for (const id of ['hook', 'ext', 'chantier', 'prospect', 'soir', 'siteFond', 'fin']) { L[id].removeAttribute('filter'); L[id].removeAttribute('clip-path'); tr(L[id], ''); op(L[id], 1); }
  let fx = '';
  const flash = v => { $('flash').style.opacity = Math.max(+$('flash').style.opacity, v); };

  if (t < T.whip + .25) { vis(L.hook, true); rHook(t); }
  if ((t >= T.whip && t < T.ch + .4) || t >= T.ap) { vis(L.ext, true); rExt(t); }
  if (t >= T.whip && t < T.whip + .25) { const k = E.in((t - T.whip) / .25); tr(L.hook, `translate(0,${k * 1920})`); }
  if (t >= T.whip && t < T.whip + .3) { const k = E.out((t - T.whip) / .3); tr(L.ext, `translate(0,${(k - 1) * 1920})`); }
  fx += lignesVitesse(t, T.whip - .05, .45, 540, 960, '#fff', .8);
  if (t >= T.ch && t < T.pr + .45) {
    vis(L.chantier, true); rChantier(t);
    const r = E.in(clamp((t - T.ch) / .38)) * 1500;
    if (r < 1499) { attr('irisC', 'r', r); L.chantier.setAttribute('clip-path', 'url(#clipIris)'); }
  }
  if (t >= T.pr && t < T.so + .1) {
    vis(L.prospect, true); rProspect(t);
    const k = E.expoIo(clamp((t - T.pr) / .4));
    tr(L.prospect, `translate(${(1 - k) * 1080},0) rotate(${(1 - k) * 6})`);
    if (t < T.pr + .4) tr(L.chantier, `translate(${-k * 1080},0) rotate(${-k * 6})`);
    fx += lignesVitesse(t, T.pr - .05, .45, 540, 960, '#1b1f2a', .25);
  }
  if (t >= T.so && t < T.si + .35) { vis(L.soir, true); rSoir(t); }
  $('fondu').style.opacity = Math.max(0, 1 - Math.abs(t - (T.so + .03)) / .18);
  if (t >= T.si && t < T.ap + .35) { vis(L.siteFond, true); rSite(t); } else { $('tel').style.display = 'none'; $('svgHaut').innerHTML = ''; }
  flash(.85 * (1 - Math.abs(t - (T.si + .05)) / .12));
  if (t >= T.ap && t < T.ap + .35) { const k = E.io((t - T.ap) / .35); op(L.siteFond, 1 - k); $('tel').style.opacity = 1 - k; $('svgHaut').style.opacity = 1 - k; } else { $('tel').style.opacity = 1; $('svgHaut').style.opacity = 1; }
  if (t >= T.fin) { vis(L.fin, true); rFin(t); }
  $('fondu').style.opacity = Math.max(+$('fondu').style.opacity, 1 - Math.abs(t - T.fin) / .2, clamp((t - CAL.duree + .3) / .3));

  fx += rUI(t);
  $('ui-fx').innerHTML = fx;
  rendreSousTitres(t);
  $('st').style.opacity = t >= T.fin - .05 ? 0 : 1;
}

// ---------------------------------- HOOK ----------------------------------
function rHook(t) {
  // le téléphone saute à chaque salve de vibration (squash and stretch), les appels manqués s'empilent
  const salve = .62, k = t % salve, n = Math.floor(t / salve);
  const h = k < .3 ? Math.sin(Math.PI * k / .3) : 0;
  const imp = squashImpact(k, .3, .22, .3);
  const vib = Math.sin(t * 110) * 5;
  const t14 = M('v1', 0), t9 = M('v1', 3);
  const fin = E.in(clamp((t - (T.whip - .3)) / .4));
  tr('hk-tel', `translate(${540 + vib + fin * 0},${900 - h * 90 - fin * 400}) rotate(${-6 + Math.sin(n * 2.1) * 6 + vib * .3})`);
  tr('hk-telIn', `scale(${(k < .3 ? 1 - h * .05 : imp.sx) * (1 + fin * .5)},${(k < .3 ? 1 + h * .08 : imp.sy) * (1 + fin * .5)})`);
  tr('hk-ombreTel', `translate(540,1330) scale(${1 - h * .35},1)`);
  // appels manqués qui tombent du haut et s'empilent
  for (let i = 0; i < 6; i++) {
    const t0 = .05 + i * .2, a = apparition(t, t0, .3);
    const nb = Math.max(0, Math.min(5, Math.floor((t - .05) / .2)));
    const rang = nb - i;            // 0 = la plus récente, devant
    const y = 400 + Math.max(0, 3 - rang) * 30;
    tr('hk-n' + i, `translate(540,${mix(-200, y, a)}) rotate(${(1 - a) * (i % 2 ? 8 : -8)}) scale(${a > 0 ? 1 - Math.max(0, rang) * .05 : 0})`);
    op('hk-n' + i, Math.min(1, a * 2) * (rang > 3 ? 0 : 1));
  }
  // badge « 14 » qui claque sur le mot « Quatorze »
  const b = apparition(t, t14 - .05, .3);
  const sb = squashImpact(t, t14 + .2, .3, .5);
  tr('hk-badge', `translate(760,500) rotate(${(1 - b) * -30 + 12}) scale(${b * sb.sx},${b * sb.sy})`);
  $('hk-heure').textContent = t < t9 ? '8:59' : '9:00';
  const sh = tremble(t, t14 - .02, 26, .35);
  const z = 1 + .06 * E.out(clamp(t / 2.4)) + .08 * apparition(t, t14, .3);
  camera('hk-cam', 540, 960, z, 0, sh);
  let fx = impact(760, 500, t, t14 - .02, 1.6, '#ffd23f') + onomatopee(300, 560, 'DRING !', t, .05, .5, '#fff', 120, -10) + onomatopee(800, 1500, 'BZZ', t, .7, .4, '#ffd23f', 90, 8) + onomatopee(260, 1520, 'BZZ', t, 1.35, .4, '#ffd23f', 80, -6);
  fx += vibrations(540, 900, t, true, 250, '#1b1f2a');
  fx += etincelles(760, 500, t, t14, 8, 230, '#ffd23f', .6);
  if (t > t9 - .05 && t < t9 + .5) fx += onomatopee(540, 300, '9:00 !', t, t9 - .05, .55, '#ffd23f', 130, 0);
  $('hk-fx').innerHTML = fx;
}

// ---------------------------------- EXTÉRIEUR ----------------------------------
function rExt(t) {
  const deux = t >= T.ap;
  const v2 = P('v2'), v3 = P('v3'), v4 = P('v4'), tToi = M('v2', 1);
  const ar = PERSOS.ar;
  let fx = '';
  let cam;
  if (!deux) cam = piste(t, [[T.whip, { x: 560, y: 760, z: 2.1 }], [tToi, { x: 560, y: 780, z: 2.0 }], [tToi + .55, { x: 560, y: 1700, z: .5 }, E.expoIo], [v3 - .35, { x: 560, y: 1500, z: .62 }, E.sine],
    [v3 - .05, { x: 560, y: 880, z: 1.75 }, E.expoIo], [v4 - .05, { x: 560, y: 860, z: 1.85 }, E.sine], [P('v5'), { x: 560, y: 760, z: 2.25 }, E.sine]]);
  else cam = piste(t, [[T.ap, { x: 600, y: 860, z: 1.7 }], [P('v11') - .3, { x: 600, y: 900, z: 1.55 }, E.sine], [M('v11', 5) - .1, { x: 700, y: 980, z: 1.22 }, E.expoIo], [P('v12'), { x: 700, y: 960, z: 1.3 }, E.sine], [T.fin + .4, { x: 690, y: 900, z: 1.6 }, E.sine]]);
  // ---- jeu de l'artisan ----
  const p = { x: 560, y: 1300, s: 1, cligne: clignement(t, 2), regardX: 0, regardY: 0 };
  let sh = { x: 0, y: 0, r: 0 };
  const respire = Math.sin(t * 2.6) * 6;
  p.bob = respire;
  if (!deux) {
    const tRing = tToi + .7, tSaut = tRing + .05;
    const sifflote = t < tRing;
    // lisse l'enduit (bras droit), sifflote
    const k = Math.sin(t * 7);
    p.mainD = { x: 250 + k * 30, y: -520 + Math.cos(t * 7) * 60 }; p.coudeD = 1; p.angleMainD = 100;
    p.mainG = { x: -120, y: -330 };
    p.tourne = .45; p.regardX = 1; p.bouche = sifflote ? 'o' : 'ferme'; p.pench = 4; p.paup = .2;
    if (sifflote) fx += notes(640, 560, t, T.whip, tRing);
    // le téléphone vibre : sursaut (saut cartoon) puis yeux exorbités
    const s = saut(t, tSaut, .16, 70);
    if (t > tSaut - .2) { p.y = 1300 + s.y; p.sx = s.sx; p.sy = s.sy; p.oeil = 1 + .4 * apparition(t, tSaut, .2) * (1 - clamp((t - tSaut - .6) / .3)); p.paup = 0; p.sourcils = 1.2; p.bouche = 'o'; p.tourne = mix(.45, 0, clamp((t - tSaut) / .2)); p.regardX = 0; p.regardY = .6;
      p.mainD = { x: 200, y: -300 }; p.angleMainD = null; }
    if (t > tSaut - .05 && t < tSaut + .5) fx += onomatopee(560, 380, '!', t, tSaut, .5, '#ff4d4f', 180, 0);
    // il attrape le téléphone : jonglage, puis à l'oreille
    const tj = v3 + .1, tOr = M('v3', 3);
    if (t > tj - .2) {
      const jong = clamp((t - tj) / (tOr - tj));
      const ph = Math.floor(jong * 4);
      const tel = { x: mix(-40, 60, (ph % 2)), y: -560 - Math.sin((jong * 4 % 1) * Math.PI) * 140 };
      if (t < tOr) {
        p.mainG = { x: -70 + (ph % 2 ? 40 : -20), y: -470 }; p.mainD = { x: 70 + (ph % 2 ? 20 : -40), y: -470 }; p.coudeG = -1; p.coudeD = 1;
        p.bouche = 'grimace'; p.oeil = 1.15; p.regardY = -1; p.regardX = tel.x / 120; p.sourcils = 1;
        fx += `<g transform="translate(${560 + tel.x},${1300 + tel.y}) rotate(${jong * 720})"><rect x="-34" y="-62" width="68" height="124" rx="16" fill="#15181e"/><rect x="-28" y="-56" width="56" height="112" rx="12" fill="#3b5c8f"/><circle cx="8" cy="-10" r="16" fill="#f4efe6"/></g>`;
        if ((jong * 4 % 1) < .08 && t > tj) fx += impact(560 + tel.x, 1300 + tel.y + 60, t, t - .02, .35, '#fff', .2);
      } else {
        // téléphone à l'oreille, sourire forcé, enduit sur l'écran
        p.mainD = { x: 135, y: -690 }; p.coudeD = 1; p.angleMainD = 200; p.formeD = 'tel'; p.mainG = { x: -110, y: -340 };
        p.bouche = t < v4 ? (t < tOr + .5 ? 'rire' : parler(t)) : parler(t); p.oeil = 1; p.sourcils = .6; p.paup = .05;
      }
      if (t > tOr - .05 && t < tOr + .5) fx += onomatopee(420, 520, 'SPLAT', t, tOr - .05, .5, '#fff', 90, -12) + poussiere(700, 600, t, tOr - .05, 5, .6, '#f4efe6', .4);
    }
    // les questions frappent : la tête encaisse (follow-through), sueur, tremblements
    for (let i = 0; i < 5; i++) {
      const ti = P('v4') - .1 + i * .4;
      if (t > ti && t < ti + .5) { const u = (t - ti) / .5; p.tete = (i % 2 ? -1 : 1) * 16 * Math.exp(-u * 4) * Math.cos(u * 12); p.oeil = 1.2; p.bouche = 'grimace'; sh = tremble(t, ti, 14, .25); fx += gouttesSueur(560, 560, t, ti, .5); }
    }
    if (t > P('v4')) { p.sueur = 1; p.froncer = 1; p.sourcils = 1; }
    if (t > P('v4') + 1.7) { p.oeil = .9; p.paup = .4; p.bouche = 'grimace'; }
    // poussière d'enduit ambiante
    for (let i = 0; i < 6; i++) { const u = ((t * .25 + i / 6) % 1); fx += `<circle cx="${200 + (i * 151) % 700 + Math.sin(u * 6 + i) * 30}" cy="${700 + u * 700}" r="${4 + i % 3 * 2}" fill="#fff" opacity="${Math.sin(Math.PI * u) * .6}"/>`; }
  } else {
    // APRÈS : il travaille tranquille, la notification arrive, il saute de joie
    const tn = P('v10') + .2, tJoie = tn + .9;
    const k = Math.sin(t * 6);
    p.mainD = { x: 250 + k * 26, y: -520 + Math.cos(t * 6) * 50 }; p.coudeD = 1; p.angleMainD = 100; p.mainG = { x: -120, y: -330 };
    p.tourne = .45; p.regardX = 1; p.bouche = 'malin'; p.pench = 4; p.paup = .15;
    if (t > tn && t < tJoie) { p.tourne = .1; p.regardX = 0; p.regardY = -1; p.oeil = 1.1; p.bouche = 'o'; p.sourcils = .8; }
    const s = saut(t, tJoie, .22, 130);
    if (t > tJoie - .2 && t < tJoie + .9) {
      p.y = 1300 + s.y; p.sx = s.sx; p.sy = s.sy; p.mainG = { x: -150, y: -760 }; p.formeG = 'poing'; p.coudeG = 1; p.mainD = { x: 150, y: -330 };
      p.bouche = 'rire'; p.oeil = 1.15; p.paup = 0; p.tourne = 0; p.regardX = 0; p.sourcils = 1;
      fx += etincelles(560, 600, t, tJoie + .1, 9, 260, '#ffd23f', .7) + poussiere(560, 1300, t, tJoie + .44, 6, .8);
    }
    fx += vibrations(560, 1050, t, t > tn - .1 && t < tn + .5, 80);
    // le collègue : il tombe du ciel sur la planche, tope là, puis la pause café
    const tc = M('v11', 5) - .25, tTope = M('v11', 8), tCafe = P('v12');
    const chute = clamp((t - tc) / .3);
    if (t > tc) {
      const yM = t < tc + .3 ? -900 * (1 - E.in(chute)) : 0;
      const sq = squashImpact(t, tc + .3, .3, .5);
      let mp = { y: yM, sx: sq.sx, sy: sq.sy, visage: 1, bouche: 'sourire', cligne: clignement(t, 9), mainG: { x: -170, y: -110 }, mainD: { x: 170, y: -110 } };
      // tope là
      if (t > tTope - .25 && t < tTope + .5) { mp.mainG = { x: -200, y: -520 }; p.mainD = { x: 330, y: -700 }; p.coudeD = 1; p.angleMainD = 150; p.tourne = .6; p.regardX = 1; p.bouche = 'rire'; }
      // café : il tend une tasse, le collègue refuse (secoue l'écran), visage blasé
      if (t > tCafe) {
        const off = clamp((t - tCafe) / .3);
        p.mainD = { x: mix(250, 300, off), y: -470 }; p.coudeD = 1; p.angleMainD = 90; p.tourne = .5; p.regardX = 1; p.bouche = 'sourire';
        fx += `<g transform="translate(${560 + mix(250, 300, off)},${1300 - 500})"><path d="M-30,-10 L30,-10 L26,50 C24,58 -24,58 -26,50 Z" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><path d="M30,4 C52,4 52,32 28,32" stroke="#1b1f2a" stroke-width="5" fill="none"/><ellipse cx="0" cy="-8" rx="30" ry="7" fill="#7a4a2a"/>
          ${[0, 1, 2].map(i => { const u = ((t * .7 + i / 3) % 1); return `<path d="M${-12 + i * 12},${-20 - u * 70} q8,-12 0,-24" stroke="#fff" stroke-width="5" fill="none" opacity="${Math.sin(Math.PI * u) * .9}"/>`; }).join('')}</g>`;
        const non = t > M('v12', 4) - .1 && t < M('v12', 4) + .9;
        mp.rot = non ? Math.sin((t - M('v12', 4)) * 22) * 9 : 0; mp.bouche = non || t > M('v12', 4) ? 'plat' : 'sourire'; mp.mainG = { x: -150, y: -90 }; mp.mainD = { x: 150, y: -90 };
        if (t > M('v12', 7)) { p.bouche = 'moue'; p.sourcils = -.2; p.tourne = .2; p.regardX = .6; }
      }
      $('ex-mascotte').innerHTML = `<g transform="translate(880,1300) scale(.62)">${mascotte(t, mp)}</g>`;
      fx += impact(880, 1300, t, tc + .3, .9, '#fff') + poussiere(880, 1300, t, tc + .3, 6, .9);
      fx += impact(780, 640, t, tTope, 1.2, '#ffd23f') + onomatopee(780, 520, 'TOPE !', t, tTope, .6, '#ffd23f', 100, 6) + etincelles(780, 640, t, tTope, 8, 220);
      if (t > M('v11', 9) && t < P('v12') - .1) fx += `<g transform="translate(880,560)"><g transform="scale(${apparition(t, M('v11', 9), .3)})"><rect x="-190" y="-60" width="380" height="96" rx="36" fill="#fff" stroke="#1b1f2a" stroke-width="6"/><text x="0" y="0" text-anchor="middle" font-family="Geist" font-weight="700" font-size="36" fill="#1f8f5a">Devis envoyé ✓</text></g></g>`;
    } else $('ex-mascotte').innerHTML = '';
  }
  ar.poser(p);
  // pigeon : sursaute et s'envole quand ça sonne
  const tEnvol = M('v2', 1) + .75;
  const envol = clamp((t - tEnvol) / 1.2);
  const ailes = Math.sin(t * 40);
  $('ex-pigeon').innerHTML = deux ? '' : `<g transform="translate(${900 + envol * 500},${1166 - envol * 700 - Math.sin(envol * 3) * 40}) scale(${envol > 0 ? -1 : 1},1)">
    <ellipse cx="0" cy="0" rx="46" ry="34" fill="#8e98a8"/><circle cx="-38" cy="-28" r="22" fill="#a7b0be"/><circle cx="-44" cy="-32" r="5" fill="#1b1f2a"/><path d="M-60,-26 l-16,6 l16,4 z" fill="#ff9d3c"/>
    <path d="M10,-10 C40,${-40 - (envol > 0 ? ailes * 40 : 0)} 70,-30 60,0 Z" fill="#6f7a8c"/><path d="M-6,30 v18 M10,30 v18" stroke="#ff9d3c" stroke-width="5"/></g>`;
  if (!deux && t > tEnvol && t < tEnvol + .4) fx += onomatopee(960, 1040, 'ROU !', t, tEnvol, .4, '#fff', 60, 10);
  $('ex-fx').innerHTML = fx;
  const tPan = t < T.whip + .3 ? (1 - E.out(clamp((t - T.whip) / .3))) : 0;
  camera('ex-cam', cam.x + bruit(t, 4) * 3, cam.y + bruit(t, 5) * 3, cam.z, bruit(t, 6) * .4, sh);
  tr('ex-nuages', `translate(${t * 20},0)`);
}

// ---------------------------------- CHANTIER ----------------------------------
function rChantier(t) {
  const u = t - T.ch;
  const cam = piste(u, [[0, { x: 300, y: 380, z: 2.3 }], [.45, { x: 330, y: 390, z: 2.1 }], [1.1, { x: 560, y: 980, z: 1.0 }, E.expoIo], [2.6, { x: 590, y: 1000, z: 1.08 }, E.sine]]);
  // horloge qui s'emballe, calendrier qui s'effeuille
  tr('ch-m', `rotate(${u * 1600})`); tr('ch-h', `rotate(${u * 140 + 60})`);
  const jour = 12 + Math.floor(clamp((u - .9) / 1.4) * 9);
  $('ch-jour').textContent = jour;
  let pages = '';
  for (let i = 0; i < 9; i++) { const t0 = .9 + i * 1.4 / 9, k = clamp((u - t0) / .7); if (k > 0 && k < 1) pages += `<g transform="translate(${k * 260},${k * 300 + k * k * 200}) rotate(${k * 260})" opacity="${1 - k}"><rect x="-80" y="30" width="160" height="140" rx="10" fill="#fff" stroke="#1b1f2a" stroke-width="4"/><text x="0" y="130" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="80" fill="#1b1f2a">${12 + i}</text></g>`; }
  $('ch-pages').innerHTML = pages;
  // carte d'avancement bloquée, qui tremble
  const a = apparition(u, .9, .35);
  const sh = (u % .7) < .12 ? Math.sin(u * 120) * 6 : 0;
  tr('ch-carte', `translate(${540 + sh},${mix(-200, 190, a)})`);
  // l'artisan au fond, toujours au téléphone
  PERSOS.ar2.poser({ x: 290, y: 1360, s: .62, ombre: .12, cligne: clignement(t, 4), mainD: { x: 135, y: -690 }, coudeD: 1, angleMainD: 200, formeD: 'tel', mainG: { x: -140, y: -470 }, coudeG: 1, formeG: 'pointe',
    bouche: parler(t, 12), sueur: 1, sourcils: 1, froncer: 1, tourne: .3, regardX: .5, tete: Math.sin(t * 6) * 4 });
  // la cliente : bras croisés, tapote du pied à toute vitesse, fumée qui sort des oreilles
  const tape = Math.max(0, Math.sin(u * 22)) ;
  const souffle = clamp((u - 1.4) / .3) * (1 - clamp((u - 2.2) / .3));
  PERSOS.cl.poser({ x: 790, y: 1780, s: 1.08, cligne: clignement(t, 7), tourne: -.55, regardX: -1, regardY: -.2, sourcils: -.4, froncer: -1, bouche: souffle > .3 ? 'o' : 'moue',
    mainG: { x: 70, y: -405 }, coudeG: 1, mainD: { x: -70, y: -425 }, coudeD: -1, piedD: { x: 60 + tape * 10, y: -tape * 26 }, bob: Math.sin(u * 22) * 3, tete: Math.sin(u * 3) * 3 });
  let fx = '';
  for (let i = 0; i < 4; i++) { const k = ((u * 1.2 + i / 4) % 1); if (u > .9) fx += `<circle cx="${790 + (i % 2 ? 130 : -130) + (i % 2 ? 1 : -1) * k * 60}" cy="${1780 - 850 - k * 160}" r="${18 + k * 34}" fill="#fff" opacity="${(1 - k) * .85}"/>`; }
  fx += onomatopee(830, 1700, 'TAP TAP', t, T.ch + 1.0, .9, '#fff', 56, -4);
  $('ch-fx').innerHTML = fx;
  camera('ch-cam', cam.x + bruit(t, 7) * 3, cam.y + bruit(t, 8) * 3, cam.z);
}

// ---------------------------------- PROSPECT ----------------------------------
function rProspect(t) {
  const u = t - T.pr, tRate = M('v6', 4), tSuiv = M('v6', 8);
  camera('pr-cam', 540, 960, mix(1.02, 1.08, clamp(u / 2.5)));
  // appel qui sonne dans le vide
  for (let i = 0; i < 3; i++) op('pr-pt' + i, ((t * 3 - i * .33) % 1) < .5 ? 1 : .25);
  $('pr-etat').textContent = t < tRate - .1 ? 'Appel en cours…' : 'Pas de réponse';
  attr('pr-etat', 'fill', t < tRate - .1 ? '#aab3c5' : '#ff6b6d');
  const rac = tRate + .2, k = E.io(clamp((t - rac) / .3));
  op('pr-appel', 1 - k); tr('pr-liste', `translate(0,${(1 - k) * 90})`);
  op('pr-c1', t > rac + .2 ? .5 : 1); op('pr-sansRep', clamp((t - rac - .2) / .2));
  // le pouce glisse vers le concurrent et appuie sur « Voir le site »
  const pt = piste(t, [[T.pr, { x: 700, y: 1500 }], [rac + .25, { x: 700, y: 1450 }], [tSuiv - .15, { x: 420, y: 1060 }, E.io], [tSuiv, { x: 410, y: 1080 }, E.out], [tSuiv + .5, { x: 420, y: 1070 }]]);
  tr('pr-pouce', `translate(${pt.x},${pt.y}) rotate(-28)`);
  tr('pr-btnSite', t > tSuiv && t < tSuiv + .2 ? 'translate(139,197) scale(.9) translate(-139,-197)' : '');
  const sq = squashImpact(t, tSuiv, .06, .3);
  tr('pr-tel', `translate(540,880) rotate(${-3 + Math.sin(t * 1.4)}) scale(${sq.sx},${sq.sy})`);
  // le devis s'envole chez le concurrent (avion en papier)
  let fx = '';
  const ta = tSuiv + .15, ka = clamp((t - ta) / 1.0);
  if (ka > 0 && ka < 1) {
    const x = 420 + ka * 800, y = 1060 - ka * 900 + Math.sin(ka * 9) * 50;
    fx += `<path d="M420,1060 Q${420 + ka * 400},${1060 - ka * 700} ${x},${y}" stroke="#15181e" stroke-width="5" stroke-dasharray="14 12" fill="none" opacity=".5"/>`;
    fx += `<g transform="translate(${x},${y}) rotate(${-40 + Math.sin(ka * 9) * 15}) scale(1.3)"><path d="M-60,0 L60,-30 L-10,30 Z" fill="#fff" stroke="#15181e" stroke-width="5" stroke-linejoin="round"/><path d="M-10,30 L0,6 L60,-30" fill="none" stroke="#15181e" stroke-width="5" stroke-linejoin="round"/><text x="0" y="-36" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="30" fill="#15181e">DEVIS</text></g>`;
  }
  fx += impact(410, 1080, t, tSuiv, .5, '#e8b84a', .25) + onomatopee(780, 340, 'BIP… BIP…', t, T.pr + .25, Math.max(.3, tRate - T.pr - .3), '#fff', 60, 0) + onomatopee(800, 1300, 'BYE !', t, ta + .3, .6, '#ffd23f', 80, -8);
  $('pr-fx').innerHTML = fx;
}

// ---------------------------------- SOIR + DÉCLIC ----------------------------------
function rSoir(t) {
  const u = t - T.so, dec = T.dec;
  const declic = apparition(t, dec + .05, .3);
  let cam = piste(t, [[T.so, { x: 470, y: 960, z: 1.15 }], [dec, { x: 450, y: 900, z: 1.35 }, E.sine], [dec + .3, { x: 440, y: 760, z: 2.0 }, E.expoOut], [T.si - .4, { x: 440, y: 770, z: 2.1 }, E.sine]]);
  const plonge = E.in(clamp((t - (T.si - .4)) / .5));
  if (plonge > 0) cam = { x: mix(cam.x, 600, plonge), y: mix(cam.y, 760, plonge), z: mix(cam.z, 10, plonge) };
  camera('so-cam', cam.x + bruit(t, 11) * 2, cam.y + bruit(t, 12) * 2, cam.z);
  // gel sur le déclic : désaturation, halo doré
  if (t > dec - .02 && t < dec + .3) { L.soir.setAttribute('filter', 'url(#desature)'); attr('desatM', 'values', mix(.05, 1, clamp((t - dec - .1) / .2))); }
  op('so-halo', clamp((t - dec - .15) / .5) * .5);
  tr('so-lampe', `rotate(${Math.sin(t * 2.2) * 2} 440 -400)`);
  $('so-heure').textContent = t < T.so + 1.2 ? '22:47' : '22:48';
  // tasses de café qui s'empilent
  let tasses = '';
  const n = Math.min(4, 1 + Math.floor(u / .5));
  for (let i = 0; i < n; i++) { const a = apparition(t, T.so + i * .5, .25); tasses += `<g transform="translate(${830 - i * 8},${1236 - i * 64}) scale(${a})"><path d="M-40,-60 L40,-60 L34,0 C32,8 -32,8 -34,0 Z" fill="#fff" stroke="#1b1f2a" stroke-width="5"/><path d="M40,-46 C64,-46 64,-14 36,-14" stroke="#1b1f2a" stroke-width="5" fill="none"/></g>`; }
  $('so-tasses').innerHTML = tasses;
  // l'artisan s'endort au téléphone, puis le déclic : il se redresse et nous regarde
  const dodo = Math.max(0, Math.sin(u * 2.4));
  const p = { x: 440, y: 1640, s: 1, cligne: clignement(t, 5), bob: dodo * 18, tete: -dodo * 14, paup: mix(.55, 0, declic), oeil: 1, mainD: { x: 128, y: -672 + dodo * 18 }, coudeD: 1, angleMainD: 200, formeD: 'tel',
    mainG: { x: -150, y: -350 }, coudeG: 1, formeG: 'poing', bouche: dodo > .7 ? 'baille' : parler(t, 7), tourne: .15, regardX: .3, regardY: .5, sueur: 0 };
  let fx = '';
  if (t < dec) { fx += [0, 1, 2].map(i => { const k = ((u * .6 + i / 3) % 1); return `<text x="${560 + k * 120}" y="${560 - k * 220}" font-family="Bricolage" font-weight="800" font-size="${40 + i * 14}" fill="#fff" opacity="${Math.sin(Math.PI * k) * .8}">z</text>`; }).join(''); }
  if (t > dec) {
    const leve = E.backS(clamp((t - dec - .2) / .35));
    Object.assign(p, { bob: mix(dodo * 18, -20, leve), tete: mix(-dodo * 14, 0, leve), paup: 0, oeil: 1 + .3 * declic * (1 - clamp((t - dec - .8) / .4)), sourcils: 1.2 * declic, tourne: 0, regardX: 0, regardY: 0,
      bouche: t > dec + .9 ? 'malin' : 'o', mainD: { x: mix(135, 70, leve), y: mix(-690, -560, leve) }, coudeD: 1, angleMainD: mix(200, 175, leve) });
    fx += onomatopee(440, 380, '?!', t, dec + .1, .9, '#ffd23f', 150, 6) + etincelles(440, 520, t, dec + .15, 8, 300, '#ffd23f', .8);
  }
  PERSOS.ar3.poser(p);
  $('so-fx').innerHTML = fx;
}

// ---------------------------------- SITE ----------------------------------
let siteEtatPrec = '';
function rSite(t) {
  $('tel').style.display = 'block';
  const mots = [M('v9', 1), M('v9', 3), M('v9', 6), M('v9', 10), F('v9')];
  const entree = E.backS(clamp((t - T.si - .05) / .55));
  const defile = piste(t, [[T.si, 0], [mots[0] - .15, 30], [mots[0] + .35, SITE_Y.sZone - 70, E.expoIo], [mots[1] + .35, SITE_Y.sReal - 70, E.expoIo], [mots[2] + .35, SITE_Y.sDelais - 70, E.expoIo], [mots[3] + .35, SITE_Y.sForm - 40, E.expoIo]]);
  const poignee = piste(t, [[mots[1] + .3, .82], [mots[1] + .9, .18, E.io], [mots[2], .5, E.io]]);
  const photos = clamp((t - mots[3] - .3) / .5) * 4;
  const tEnvoi = Math.min(mots[3] + .6, T.ap - .55);
  siteEtat({ defile, poignee, photos, envoi: clamp((t - tEnvoi) / .2), ok: clamp((t - tEnvoi - .15) / .45), rayon: clamp((t - mots[0] - .1) / .5), collant: clamp((t - mots[3]) / .3) });
  const tilt = (1 - entree) * 50;
  const flotte = Math.sin(t * 1.6) * 8;
  $('tel').style.transform = `translate(-50%,-50%) translate(540px,${800 + (1 - entree) * 500 + flotte}px) perspective(2200px) rotateY(${tilt}deg) rotateZ(${(1 - entree) * -10 + Math.sin(t * 1.1) * 1.2}deg) scale(${.6 + .92 * entree})`;
  // reflet qui balaie la vitre
  const rk = ((t - T.si) % 3.2) / 3.2;
  $('telReflet').style.transform = `translateX(${-500 + rk * 1400}px) rotate(20deg)`;
  // étiquettes dorées qui jaillissent à chaque mot clé
  const lab = [['Ta zone', 40, 330, -1, 'zone'], ['Tes chantiers en photos', 0, 560, 1, 'photo'], ['Tes délais', 40, 790, -1, 'delai'], ['Ton devis en ligne', 0, 1040, 1, 'devis']];
  let s = '';
  lab.forEach(([txt, x0, y, sx, ic], i) => {
    let x = x0;
    const a = apparition(t, mots[i] - .05, .35);
    if (a <= 0) return;
    const w = txt.length * 20 + 110;
    if (sx > 0) x = 1050 - w; else x = 30;
    s += `<g transform="translate(${x + w / 2},${y}) rotate(${(1 - a) * sx * 14 + sx * -3}) scale(${a}) translate(${-w / 2},0)"><rect x="0" y="-42" width="${w}" height="84" rx="42" fill="#15181e" stroke="#fff" stroke-width="5"/><circle cx="42" cy="0" r="26" fill="#e8b84a"/><path d="M30,0 l9,9 l16,-18" stroke="#15181e" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
      <text x="84" y="12" font-family="Bricolage" font-weight="800" font-size="34" fill="#fff">${txt}</text></g>`;
    s += etincelles(x + w / 2, y, t, mots[i], 6, 150, '#e8b84a', .6);
  });
  s += confettis(540, 900, t, tEnvoi + .25, 30, 1.4);
  $('svgHaut').innerHTML = s;
  for (let i = 0; i < 6; i++) tr('sf-f' + i, `translate(${[120, 960, 140, 950, 880, 200][i]},${[260, 400, 1300, 1200, 1700, 1750][i] + Math.sin(t * 1.3 + i) * 20}) rotate(${t * 30 * (i % 2 ? 1 : -1)})`);
}

// ---------------------------------- FIN ----------------------------------
function rFin(t) {
  const u = t - T.fin;
  const a = apparition(u, .1, .4), b = apparition(u, .45, .4), c = apparition(u, .8, .4), d = clamp((u - 1.1) / .4);
  tr('fi-a', `translate(540,700) scale(${a}) translate(-540,-700)`);
  tr('fi-b', `translate(0,${(1 - b) * 40})`); op('fi-b', b);
  const rebond = Math.abs(Math.sin(u * 4)) * 14 * clamp(u - 1);
  tr('fi-pill', `translate(406,${880 - rebond})`); tr('fi-pillIn', `scale(${b}) rotate(${(1 - b) * -12 + Math.sin(u * 3) * 3})`);
  tr('fi-bulle', `translate(540,1110) scale(${c})`);
  for (let i = 0; i < 3; i++) tr('fi-p' + i, `translate(0,${-Math.max(0, Math.sin(u * 6 - i * .8)) * 12})`);
  op('fi-logo', d);
  $('fi-fx').innerHTML = etincelles(406, 880, t, T.fin + .5, 8, 200, '#e8b84a', .7);
}

// ---------------------------------- INTERFACE ----------------------------------
function rUI(t) {
  let fx = '';
  // bulles de questions lancées comme des projectiles
  const pos = [[40, 250, -1], [400, 420, 1], [40, 590, -1], [430, 1040, 1], [60, 1180, -1]];
  for (let i = 0; i < 5; i++) {
    const t0 = P('v4') - .15 + i * .4, t1 = P('v5') - .2;
    const a = clamp((t - t0) / .22), b = clamp((t - t1) / .15);
    const vivant = t >= t0 && t < t1 + .15;
    vis('bq' + i, vivant);
    if (!vivant) continue;
    const [x, y, sx] = pos[i];
    const depart = sx < 0 ? -900 : 900;
    const sq = squashImpact(t, t0 + .22, .25, .4);
    tr('bq' + i, `translate(${x + (1 - E.out(a)) * depart},${y + Math.sin((t - t0) * 3 + i) * 6})`);
    tr('bq' + i + '-in', `rotate(${(1 - a) * sx * -20 + sx * 2}) scale(${sq.sx * (1 + b * .4)},${sq.sy * (1 + b * .4)})`);
    op('bq' + i + '-in', 1 - b);
    if (b > 0) fx += poussiere(x + 300, y + 55, t, t1, 6, 1.1, '#fff', .4);
  }
  // notification de devis
  const n0 = P('v10') + .15;
  const a = apparition(t, n0, .4), b = clamp((t - F('v10') - .2) / .3);
  vis('notif', t > n0 && b < 1);
  tr('notif', `translate(0,${mix(-260, 110, a) - E.in(b) * 400})`);
  return fx;
}

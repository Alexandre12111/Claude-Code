// Épisode 2 : « La recommandation perdue ». Repères calés sur la voix d'Alexandre : P(id) début de réplique, F(id) fin, M(id, k) début du mot k.
// Effets de rétention : arrêt sur image + scratch, retour en arrière façon cassette, zooms sur les mots-clés, tampons, compteur d'avis,
// criquets, mouche, fantôme, construction du site, barre de progression. Une relance visuelle toutes les 2 à 3 secondes.
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
function lumiere(cle = '#ffd59a', froid = '#1b2a5a', force = .35) {
  const id = 'lu' + Math.round(Math.random() * 1e6);
  return `<defs><radialGradient id="${id}a" cx=".85" cy=".1" r="1.1"><stop offset="0" stop-color="${cle}" stop-opacity="${force}"/><stop offset=".6" stop-color="${cle}" stop-opacity="0"/></radialGradient>
    <radialGradient id="${id}b" cx=".5" cy=".45" r=".75"><stop offset=".6" stop-color="${froid}" stop-opacity="0"/><stop offset="1" stop-color="${froid}" stop-opacity=".38"/></radialGradient></defs>
    <rect width="1080" height="1920" fill="url(#${id}a)" style="mix-blend-mode:soft-light;pointer-events:none"/><rect width="1080" height="1920" fill="url(#${id}b)" style="pointer-events:none"/>`;
}
function clignementG(t, graine = 0) { const periode = 2.6 + (graine % 3) * .7, u = ((t + graine * 1.37) % periode) / periode, d = .055; return u < d ? Math.sin((u / d) * Math.PI) : 0; }
const _ctxMesure = document.createElement('canvas').getContext('2d');
function mesureTexte(t, taille, police = 'Bricolage', graisse = 800) { _ctxMesure.font = `${graisse} ${taille}px ${police}`; return _ctxMesure.measureText(t).width; }

// ---------- éléments réutilisables ----------
// iPhone récent en SVG : cadre titane fin, Dynamic Island, barre d'état. Écran 564 x 1204 centré sur (0,0). contenu : id du groupe d'écran.
function iphone(id, fond = '#f4efe6', statutClair = false) {
  const c = statutClair ? '#fff' : '#15181e';
  return `<g id="${id}">
    <rect x="-292" y="-614" width="612" height="1252" rx="96" fill="#2a1a10" opacity=".22" transform="translate(22,34)"/>
    <rect x="-318" y="-360" width="8" height="64" rx="3" fill="url(#gTitane)"/><rect x="-318" y="-270" width="8" height="110" rx="3" fill="url(#gTitane)"/><rect x="-318" y="-140" width="8" height="110" rx="3" fill="url(#gTitane)"/><rect x="310" y="-300" width="8" height="160" rx="3" fill="url(#gTitane)"/>
    <rect x="-312" y="-634" width="624" height="1268" rx="100" fill="url(#gTitane)"/><rect x="-302" y="-624" width="604" height="1248" rx="92" fill="#050506"/>
    <g clip-path="url(#clipEcran)"><rect x="-288" y="-610" width="576" height="1220" fill="${fond}"/><g id="${id}-ecran"></g>
      <text x="-212" y="-552" font-family="Geist" font-weight="600" font-size="26" fill="${c}">9:41</text>
      <g transform="translate(176,-561)" fill="${c}"><rect x="0" y="0" width="22" height="14" rx="2"/><rect x="28" y="-1" width="36" height="16" rx="4" fill="none" stroke="${c}" stroke-width="2.5"/><rect x="31" y="2" width="27" height="10" rx="2"/></g>
      <rect x="-288" y="-610" width="576" height="1220" fill="url(#gVerre)"/></g>
    <rect x="-66" y="-590" width="132" height="38" rx="19" fill="#000"/><circle cx="38" cy="-571" r="7" fill="#1d2a44"/>
  </g>`;
}
const etoiles = (n, x, y, taille = 26, coul = '#f2a516', vide = '#d8d1c4') => [0, 1, 2, 3, 4].map(i => `<text x="${x + i * taille * 1.05}" y="${y}" font-size="${taille}" fill="${i < n ? coul : vide}">★</text>`).join('');
// tampon (CHANTIER PERDU / GAGNÉ) qui s'abat : grand, de travers, avec écrasement
function tampon(x, y, txt, coul, t, t0, rot = -9, taille = 96) {
  if (t < t0) return '';
  const k = clamp((t - t0) / .18), s = k < 1 ? mix(2.8, 1, E.in(k)) : 1 + .08 * Math.exp(-(t - t0 - .18) * 14) * Math.sin((t - t0) * 40);
  const w = mesureTexte(txt, taille) + 90;
  return `<g transform="translate(${x},${y}) rotate(${rot}) scale(${s})" opacity="${clamp(k * 3)}">
    <rect x="${-w / 2}" y="${-taille * .85}" width="${w}" height="${taille * 1.5}" rx="18" fill="${coul}" fill-opacity=".1" stroke="${coul}" stroke-width="12"/>
    <rect x="${-w / 2 + 16}" y="${-taille * .85 + 16}" width="${w - 32}" height="${taille * 1.5 - 32}" rx="10" fill="none" stroke="${coul}" stroke-width="4"/>
    <text y="${taille * .32}" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="${taille}" fill="${coul}" letter-spacing="2">${txt}</text></g>`;
}
// mot-clé géant qui jaillit (rétention : renforce le mot fort de la réplique)
function motCle(x, y, txt, t, t0, d = .9, coul = '#ffd23f', taille = 110, rot = -4) {
  if (t < t0 || t > t0 + d) return '';
  const a = E.backS(clamp((t - t0) / .22)), b = clamp((t - t0 - d + .2) / .2);
  return `<g transform="translate(${x},${y - b * 30}) rotate(${rot}) scale(${a * (1 - b * .2)})" opacity="${1 - b}"><text text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="${taille}" fill="${coul}" stroke="#15181e" stroke-width="16" paint-order="stroke" stroke-linejoin="round">${txt}</text></g>`;
}
function coeur(x, y, s, coul = '#ff5d73', o = 1) { return `<path transform="translate(${x},${y}) scale(${s})" d="M0,18 C-30,-6 -24,-34 -6,-30 C2,-28 0,-20 0,-18 C0,-20 -2,-28 6,-30 C24,-34 30,-6 0,18 Z" fill="${coul}" opacity="${o}"/>`; }
function mouche(x, y, t) { const a = Math.sin(t * 60) * .5 + .5; return `<g transform="translate(${x},${y})"><ellipse rx="10" ry="7" fill="#1b1f2a"/><ellipse cx="-6" cy="-8" rx="9" ry="${4 + a * 4}" fill="#cfe3ff" opacity=".8"/><ellipse cx="6" cy="-8" rx="9" ry="${4 + (1 - a) * 4}" fill="#cfe3ff" opacity=".8"/></g>`; }

// ======================================================================================
function construireScenes() {
  $('defs').innerHTML = `
    <filter id="nb"><feColorMatrix id="nbM" type="saturate" values="1"/></filter>
    <filter id="flouFond" x="-5%" y="-5%" width="110%" height="110%"><feGaussianBlur stdDeviation="5"/></filter>
    <filter id="flouPhoto" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="7"/></filter>
    <filter id="fb" x="-8%" y="-8%" width="116%" height="116%"><feTurbulence id="fbTurb" type="fractalNoise" baseFrequency="0.005 0.02" numOctaves="2" seed="7" result="n"/><feDisplacementMap id="fbDisp" in="SourceGraphic" in2="n" scale="0" xChannelSelector="R" yChannelSelector="G" result="d"/><feColorMatrix id="fbSep" in="d" type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 1 0"/></filter>
    <radialGradient id="fbVig" cx=".5" cy=".46" r=".72"><stop offset=".55" stop-color="#2a1606" stop-opacity="0"/><stop offset="1" stop-color="#2a1606" stop-opacity=".85"/></radialGradient>
    <radialGradient id="fbFuite" cx="1" cy="0" r="1"><stop offset="0" stop-color="#ffb347" stop-opacity=".75"/><stop offset=".5" stop-color="#ff7a2c" stop-opacity=".2"/><stop offset="1" stop-color="#ff7a2c" stop-opacity="0"/></radialGradient>
    <filter id="vhs"><feOffset dx="7" in="SourceGraphic" result="r"/><feColorMatrix in="r" type="matrix" values="1 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 1 0" result="rr"/><feOffset dx="-7" in="SourceGraphic" result="b"/><feColorMatrix in="b" type="matrix" values="0 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 1 0" result="bb"/><feBlend in="rr" in2="bb" mode="screen"/></filter>
    <linearGradient id="gCiel" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#6cc0f2"/><stop offset=".7" stop-color="#bfe3f7"/><stop offset="1" stop-color="#ffe2b8"/></linearGradient>
    <linearGradient id="gTitane" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#e2ded6"/><stop offset=".15" stop-color="#8e8a82"/><stop offset=".45" stop-color="#3a3835"/><stop offset=".7" stop-color="#55524d"/><stop offset=".9" stop-color="#a19c93"/><stop offset="1" stop-color="#e2ded6"/></linearGradient>
    <linearGradient id="gVerre" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".14"/><stop offset=".3" stop-color="#fff" stop-opacity="0"/><stop offset=".8" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#fff" stop-opacity=".06"/></linearGradient>
    <linearGradient id="gOr" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffe9a8"/><stop offset=".5" stop-color="#f2c457"/><stop offset="1" stop-color="#d49a2a"/></linearGradient>
    <radialGradient id="gHalo" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#ffe7a3" stop-opacity=".95"/><stop offset="1" stop-color="#ffe7a3" stop-opacity="0"/></radialGradient>
    <radialGradient id="gHaloBleu" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#8fd3ff" stop-opacity=".9"/><stop offset="1" stop-color="#8fd3ff" stop-opacity="0"/></radialGradient>
    <clipPath id="clipEcran"><rect x="-288" y="-610" width="576" height="1220" rx="80"/></clipPath>`;

  // ---------------- SALLE DE BAIN (hook, retour en arrière, fin) ----------------
  const carreaux = Array.from({ length: 12 }, (_, i) => Array.from({ length: 9 }, (_, j) => `<rect x="${-200 + j * 170}" y="${-300 + i * 170}" width="164" height="164" rx="6" fill="${(i + j) % 2 ? '#eef3f1' : '#e4ece9'}"/>`).join('')).join('');
  calque('sdb', `<g id="sb-cam">
    <rect x="-400" y="-400" width="1900" height="2700" fill="#dfe8e5"/>${carreaux}
    <rect x="-400" y="1430" width="1900" height="900" fill="#c9a57c"/>${[0, 1, 2, 3, 4, 5].map(i => `<rect x="-400" y="${1430 + i * 70}" width="1900" height="4" fill="#b38c62" opacity=".5"/>`).join('')}
    <g transform="translate(690,200)"><rect x="0" y="0" width="360" height="1240" fill="#bfe3f7" opacity=".35" stroke="#a9b8bf" stroke-width="10"/><path d="M40,60 L140,60" stroke="#fff" stroke-width="18" stroke-linecap="round" opacity=".8"/><path d="M40,110 L100,110" stroke="#fff" stroke-width="12" stroke-linecap="round" opacity=".6"/>
      <rect x="150" y="-40" width="16" height="200" fill="#9aa4ab"/><ellipse cx="158" cy="170" rx="90" ry="18" fill="#6d777e"/>
      ${Array.from({ length: 14 }, (_, i) => `<path id="sb-goutte${i}" d="M0,0 v26" stroke="#7cc4f0" stroke-width="5" stroke-linecap="round" opacity=".7"/>`).join('')}</g>
    <g transform="translate(-40,820)"><rect x="0" y="0" width="420" height="200" rx="20" fill="#7a5233"/><rect x="20" y="20" width="380" height="160" rx="14" fill="#8e6340"/><rect x="60" y="-26" width="300" height="34" rx="17" fill="#fff"/>
      <circle cx="210" cy="-260" r="170" fill="#d9e4e2" stroke="#c7a76c" stroke-width="14"/><circle cx="210" cy="-260" r="150" fill="#eef7f6"/><path d="M120,-340 L190,-390" stroke="#fff" stroke-width="16" stroke-linecap="round" opacity=".9"/></g>
    <g transform="translate(90,1240)"><path d="M-40,190 L40,190 L56,40 L-56,40 Z" fill="#e9e2d4"/><path d="M0,40 C-80,-60 -120,-140 -60,-200 M0,40 C20,-80 0,-170 50,-230 M0,40 C70,-40 120,-90 110,-170" stroke="#3f8f5c" stroke-width="22" fill="none" stroke-linecap="round"/></g>
    <g id="sb-scintilles"></g>
    <g id="sb-gaspard"></g><g id="sb-cliente"></g>
    <g id="sb-fx"></g></g>${lumiere('#fff1c9', '#1b3a5a', .35)}`);
  PERS.gs = new Gaspard3D('gs'); $('sb-gaspard').innerHTML = PERS.gs.svg();
  PERS.cs = new Pantin('cs', 'cliente'); $('sb-cliente').innerHTML = PERS.cs.svg();

  // ---------------- JARDIN (haie, fantôme, final) ----------------
  const maison = (x, coul, toit) => `<g transform="translate(${x},420)"><path d="M0,240 L230,40 L460,240 Z" fill="${toit}"/><rect x="30" y="230" width="400" height="820" fill="${coul}"/>
    ${[0, 1].map(i => [0, 1].map(j => `<g transform="translate(${80 + j * 190},${330 + i * 300})"><rect width="120" height="170" rx="10" fill="#9fd0ee" stroke="#fff" stroke-width="12"/><path d="M60,0 v170 M0,85 h120" stroke="#fff" stroke-width="8"/></g>`).join('')).join('')}</g>`;
  calque('jardin', `<g id="ja-cam">
    <rect x="-500" y="-500" width="2100" height="1900" fill="url(#gCiel)"/>
    <circle cx="880" cy="230" r="90" fill="#ffe7a3" opacity=".9"/>
    ${maison(-220, '#f1e2c9', '#c8643a')}${maison(760, '#e3ecf2', '#5d6f8a')}
    <rect x="-500" y="1380" width="2100" height="1000" fill="#8fcf6b"/><rect x="-500" y="1380" width="2100" height="30" fill="#7dbb5c"/>
    <g id="ja-haie">${Array.from({ length: 9 }, (_, i) => `<circle cx="${300 + i * 60}" cy="${1250 + (i % 2) * 20}" r="${90 + (i % 3) * 14}" fill="${i % 2 ? '#4f9a4a' : '#5aa953'}"/>`).join('')}<rect x="260" y="1250" width="560" height="200" fill="#4f9a4a"/></g>
    <g id="ja-cliente"></g><g id="ja-voisine"></g><g id="ja-gaspard"></g>
    <g id="ja-fx"></g></g>${lumiere('#ffe2a8', '#1b2a5a', .3)}`);
  PERS.cj = new Pantin('cj', 'cliente'); $('ja-cliente').innerHTML = PERS.cj.svg();
  PERS.vj = new Pantin('vj', 'prospecte'); $('ja-voisine').innerHTML = PERS.vj.svg();
  PERS.gj = new Gaspard3D('gj'); $('ja-gaspard').innerHTML = PERS.gj.svg();

  // ---------------- GOOGLE sur l'iPhone de la voisine ----------------
  calque('google', `<g id="go-cam">
    <g filter="url(#flouFond)"><rect x="-300" y="-300" width="1700" height="2600" fill="#f3d7b4"/><rect x="-300" y="1300" width="1700" height="1000" fill="#d9a774"/>
      <g transform="translate(-60,1050)"><rect width="760" height="300" rx="70" fill="#5f8f86"/><rect x="40" y="-120" width="680" height="200" rx="70" fill="#6fa197"/></g>
      <g transform="translate(160,220)"><rect width="380" height="460" rx="20" fill="#fff"/><rect x="24" y="24" width="332" height="412" rx="12" fill="#8fc6e8"/></g></g>
    <g id="go-telPos" transform="translate(540,880) scale(.94)">${iphone('go-tel', '#fff')}</g>
    <g id="go-fx"></g></g><g id="go-gaspard"></g><g id="go-fxE"></g>${lumiere('#fff1c9', '#203050', .3)}`);
  PERS.gg = new Gaspard3D('gg'); $('go-gaspard').innerHTML = PERS.gg.svg();

  // ---------------- APPEL : écran partagé (le concurrent sonne, Gaspard attend) ----------------
  calque('appel', `<g id="ap-haut"><rect width="1080" height="960" fill="#1a2234"/><circle cx="540" cy="470" r="520" fill="url(#gHaloBleu)" opacity=".25"/>
      <g id="ap-telC" transform="translate(540,500) scale(.62)">${iphone('ap-tc', '#1a2234', true)}</g><g id="ap-fxH"></g></g>
    <g id="ap-bas"><rect y="960" width="1080" height="960" fill="#efe3cf"/>${Array.from({ length: 6 }, (_, i) => `<rect x="${i * 190 - 40}" y="960" width="170" height="960" fill="#e8d9c0" opacity=".6"/>`).join('')}
      <g id="ap-gaspard"></g><g id="ap-fxB"></g></g>
    <rect y="952" width="1080" height="16" fill="#15181e"/>`);
  PERS.ga = new Gaspard3D('ga'); $('ap-gaspard').innerHTML = PERS.ga.svg();

  // ---------------- VÉRIFICATION (typographie animée) ----------------
  calque('verif', `<rect width="1080" height="1920" fill="#15181e"/><circle cx="540" cy="820" r="700" fill="url(#gHalo)" opacity=".1"/><g id="ve-cont"></g>`);

  // ---------------- VITRINE : le site se construit ----------------
  calque('vitrine', `<g id="vi-cam"><rect x="-300" y="-300" width="1700" height="2600" fill="#1d4b84"/>
      ${Array.from({ length: 30 }, (_, i) => `<path d="M${-300 + i * 60},-300 V2300 M-300,${-300 + i * 90} H1400" stroke="#fff" stroke-opacity=".06" stroke-width="2"/>`).join('')}
      <circle cx="540" cy="760" r="760" fill="#3f7fc4" opacity=".3"/>
      <g transform="translate(470,820) scale(.96)">${iphone('vi-tel', '#f5f0e8')}</g>
      <g id="vi-gaspard"></g><g id="vi-fx"></g></g>${lumiere('#d8ecff', '#081a38', .25)}`);
  PERS.gv = new Gaspard3D('gv'); $('vi-gaspard').innerHTML = PERS.gv.svg();

  // ---------------- FIN ----------------
  calque('fin', `<rect width="1080" height="1920" fill="#15181e"/><circle cx="540" cy="900" r="760" fill="url(#gHalo)" opacity=".12"/>
    <g id="fi-ga"></g>
    <g id="fi-a"><text x="540" y="380" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="100" fill="#fff">Tu te reconnais ?</text></g>
    <g id="fi-b"><text x="290" y="520" text-anchor="end" font-family="Bricolage" font-weight="700" font-size="60" fill="#cfc6b8">Écris</text><text x="502" y="520" font-family="Bricolage" font-weight="700" font-size="60" fill="#cfc6b8">en commentaire</text></g>
    <g id="fi-pill"><g id="fi-pillIn"><rect x="-96" y="-50" width="192" height="96" rx="26" fill="#e8b84a"/><text x="0" y="22" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="64" fill="#15181e">SITE</text></g></g>
    <g id="fi-logo"><image href="logo-aurea-media-clair.png" x="380" y="1640" width="320" height="112"/><text x="540" y="1800" text-anchor="middle" font-family="Geist" font-size="32" fill="#8a8478">aurea-media.fr</text></g>
    <g id="fi-fx"></g>`);
  PERS.gf = new Gaspard3D('gf'); $('fi-ga').innerHTML = PERS.gf.svg();

  calque('ui', `<g id="ui-fx"></g>`);
}

// ======================================================================================
const T = {};
function reperes() {
  T.gel = M('l00', 7) - .04; T.tamp = M('l00', 11) - .02;
  T.rw = P('l01') - .8; T.j = P('l02') - .12; T.g = P('l03') - .12; T.ap = P('l06') - .1; T.v = P('l07') - .1;
  T.fa = P('l08') - .1; T.vi = P('l09') - .1; T.w = P('l10') - .1; T.wJ = M('l10', 10) + .1; T.fi = P('l11') - .1; T.sd = P('l12') - .1; T.fin = CAL.finTexte;
}
function rendre(t) {
  if (!T.gel) reperes();
  for (const id in L) vis(L[id], false);
  vis(L.ui, true);
  $('flash').style.opacity = 0; $('fondu').style.opacity = 0;
  for (const id in L) { L[id].removeAttribute('filter'); tr(L[id], ''); op(L[id], 1); }
  const flash = v => { $('flash').style.opacity = Math.max(+$('flash').style.opacity, v); };
  let fx = ''; window._uiSdb = ''; window._uiTampon = '';
  if (t < T.j) { vis(L.sdb, true); rSdb(t); }
  if (t >= T.j && t < T.g + .3) { vis(L.jardin, true); rJardin(t, 'haie'); }
  if (t >= T.g && t < T.ap) { vis(L.google, true); rGoogle(t, 'perdu'); if (t < T.g + .3) tr(L.google, `translate(${(1 - E.out((t - T.g) / .3)) * 1080},0)`); }
  if (t >= T.ap && t < T.v) { vis(L.appel, true); rAppel(t); }
  if (t >= T.v && t < T.fa) { vis(L.verif, true); rVerif(t); }
  if (t >= T.fa && t < T.vi) { vis(L.jardin, true); rJardin(t, 'fantome'); }
  if (t >= T.vi && t < T.w + .3) { vis(L.vitrine, true); rVitrine(t); }
  if (t >= T.w && t < T.wJ) { vis(L.google, true); rGoogle(t, 'gagne'); if (t < T.w + .3) tr(L.google, `translate(${(1 - E.out((t - T.w) / .3)) * 1080},0)`); }
  if (t >= T.wJ && t < T.sd) { vis(L.jardin, true); rJardin(t, t < T.fi ? 'appel' : 'final'); }
  if (t >= T.sd && t < T.fin) { vis(L.sdb, true); rSdb(t); }
  if (t >= T.fin) { vis(L.fin, true); rFin(t); }
  // transitions : flash bref sur chaque changement de plan (relance l'attention)
  for (const c of [T.j, T.ap, T.v, T.fa, T.vi, T.wJ, T.fi, T.sd]) flash(.55 * (1 - Math.abs(t - c) / .08));
  $('fondu').style.opacity = Math.max(clamp(1 - Math.abs(t - T.fin) / .2), clamp((t - CAL.duree + .3) / .3));
  fx += lignesVitesse(t, T.g - .05, .4, 540, 960, '#fff', .6) + lignesVitesse(t, T.w - .05, .4, 540, 960, '#fff', .6);
  $('ui-fx').innerHTML = fx + window._uiSdb + window._uiTampon;
  rendreSousTitres(t);
  if (t >= T.fin - .05) $('st').style.opacity = 0;
  $('progression').firstElementChild.style.width = (clamp(t / CAL.duree) * 100) + '%';
}

// ---------------------------------- SALLE DE BAIN ----------------------------------
function rSdb(t) {
  let fx = '';
  const fin = t >= T.sd;
  const gele = !fin && t >= T.gel && t < T.rw, tt = gele ? T.gel : t;       // arrêt sur image
  const flashback = !fin && t >= T.rw;
  let cam;
  if (!fin) cam = piste(tt, [[0, { x: 540, y: 980, z: 1.08 }], [M('l00', 3), { x: 540, y: 930, z: 1.18 }, E.sine], [T.gel, { x: 560, y: 860, z: 1.28 }, E.sine], [P('l01'), { x: 600, y: 900, z: 1.2 }], [F('l01') + .3, { x: 640, y: 880, z: 1.32 }, E.sine]]);
  else cam = piste(t, [[T.sd, { x: 540, y: 820, z: 1.45 }], [T.fin, { x: 540, y: 960, z: 1.06 }, E.io]]);
  let sh = { x: 0, y: 0, r: 0 };
  if (gele) { const k = E.out(clamp((t - T.gel) / .12)); cam.z += .12 * k; sh = tremble(t, T.gel, 22, .25); if (t >= T.tamp) { const s2 = tremble(t, T.tamp, 30, .3); sh.x += s2.x; sh.y += s2.y; sh.r += s2.r; } }
  camera('sb-cam', cam.x + bruit(t, 2) * 3, cam.y + bruit(t, 3) * 3, cam.z, 0, sh);
  // pluie de douche
  for (let i = 0; i < 14; i++) { const u = ((tt * 1.6 + i * .37) % 1); tr('sb-goutte' + i, `translate(${80 + (i * 53) % 160},${200 + u * 900})`); op('sb-goutte' + i, fin || t < T.rw ? .7 : .4); }
  // scintillements sur la salle de bain neuve
  $('sb-scintilles').innerHTML = [[760, 380], [990, 600], [240, 640], [860, 1100], [120, 1000]].map(([x, y], i) => { const a = Math.max(0, Math.sin(tt * 3 + i * 1.7)); return etoile4(x, y, 22 * a, '#fff', tt * 40); }).join('');
  // Gaspard et la cliente
  const g = { x: 380, y: 1500, s: 1.0, cligne: clignementG(tt, 2), queue: Math.sin(tt * 4) * 8, tourne: .15 }, c = { x: 770, y: 1560, s: 1.0, cligne: clignementG(tt, 5), tourne: -.5, regardX: -1 };
  if (!fin && t < T.rw) {
    const tope = M('l00', 4);
    const lev = E.backS(clamp((tt - tope + .5) / .45));
    Object.assign(g, { mainD: { x: mix(220, 260, lev), y: mix(-170, -560, lev) }, coudeD: 1, formeD: 'salut', bouche: 'rire', paup: 0, sourcils: .8, oeil: 1.05 });
    Object.assign(c, { mainG: { x: mix(-120, -220, lev), y: mix(-322, -640, lev) }, coudeG: 1, bouche: 'rire', sourcils: .8 });
    if (tt > tope) { fx += impact(560, 680, tt, tope, 1.2, '#ffd23f') + etincelles(560, 680, tt, tope, 9, 260, '#ffd23f', .8) + confettis(560, 700, tt, tope + .05, 30, 1.6); }
    if (tt < tope + .9) fx += onomatopee(560, 520, 'TOPE !', tt, tope, .7, '#ffd23f', 110, -6);
    if (gele) {
      fx += `<rect x="-400" y="-400" width="1900" height="2700" fill="#000" opacity=".12"/>`;
      fx += motCle(540, 420, 'Attends…', t, T.gel + .05, Math.max(.5, T.tamp - T.gel - .05), '#fff', 96, 4);
      window._uiTampon = tampon(540, 760, 'CHANTIER PERDU', '#e5484d', t, T.tamp, -9, 78);
    }
  } else if (!fin) {
    // retour en arrière puis la cliente admire sa douche
    Object.assign(g, { mainD: { x: 230, y: -330 }, formeD: 'pouce', coudeD: 1, bouche: 'sourire', regardX: .7, sourcils: .5 });
    Object.assign(c, { tourne: .6, regardX: .9, regardY: -.2, bouche: t > P('l01') ? 'rire' : 'o', mainG: { x: -60, y: -560 }, mainD: { x: 40, y: -560 }, coudeG: -1, coudeD: 1, sourcils: 1, oeil: 1.15 });
    for (let i = 0; i < 6; i++) { const u = ((t - P('l01')) * .7 + i / 6) % 1; if (t > P('l01')) fx += coeur(760 + Math.sin(u * 6 + i) * 70 + (i % 3) * 50, 620 - u * 380, .9 + (i % 2) * .5, '#ff5d73', Math.sin(Math.PI * u)); }
    fx += motCle(760, 360, 'ADORÉ', t, M('l01', 2) - .05, .9, '#ff5d73', 120, -6);
  } else {
    // fin douce : Gaspard fier devant son chantier, lumière chaude
    Object.assign(g, { x: 540, y: 1520, s: 1.12, tourne: .25, mainD: { x: 230, y: -330 }, formeD: 'pouce', coudeD: 1, mainG: { x: -210, y: -170 }, bouche: 'sourire', paup: .12, sourcils: .4, regardX: 0 });
    c.x = 2000;
    fx += motCle(540, 380, 'Exceptionnel.', t, M('l12', 5) - .1, 1.3, '#ffd23f', 104, -3);
    fx += etincelles(540, 600, t, M('l12', 5), 10, 300, '#ffd23f', 1);
  }
  PERS.gs.poser(g); PERS.cs.poser(c);
  $('sb-fx').innerHTML = fx;
  // arrêt sur image : noir et blanc ; retour en arrière : effet cassette
  if (gele) { L.sdb.setAttribute('filter', 'url(#nb)'); attr('nbM', 'values', mix(1, 0, clamp((t - T.gel) / .1))); }
  let ui = '';
  if (gele) ui += `<rect width="1080" height="1920" fill="none" stroke="#fff" stroke-width="18" opacity=".9"/><g transform="translate(70,150)"><rect width="34" height="90" fill="#fff"/><rect x="54" width="34" height="90" fill="#fff"/></g>`;
  if (flashback) {
    // flash-back « 48 H AVANT » : éclair blanc, l'image ondule comme un souvenir, puis sépia, vignette, grain et fuite de lumière
    const fbA = P('l01') - .05, k = clamp((t - T.rw) / (fbA - T.rw)), onde = Math.sin(Math.PI * clamp(k * 1.1));
    const sep = .8 * E.out(clamp(k * 1.4)), I = 1 - sep;
    const S3 = [[.393, .769, .189], [.349, .686, .168], [.272, .534, .131]];
    const m = S3.map((r, a) => r.map((v, b) => (I * (a === b ? 1 : 0) + sep * v * 1.05).toFixed(3)).join(' ') + ' 0 ' + (sep * .03).toFixed(3)).join('  ') + '  0 0 0 1 0';
    L.sdb.setAttribute('filter', 'url(#fb)');
    attr('fbSep', 'values', m);
    attr('fbDisp', 'scale', (onde * 60).toFixed(1));
    attr('fbTurb', 'baseFrequency', `${(.004 + .003 * k).toFixed(4)} ${(.02 + .008 * Math.sin(t * 11)).toFixed(4)}`);
    tr(L.sdb, `translate(540,900) scale(${(1 + .22 * onde).toFixed(3)}) rotate(${(-5 * onde).toFixed(2)}) translate(-540,-900)`);
    // éclair blanc à l'entrée
    ui += `<rect width="1080" height="1920" fill="#fff" opacity="${(clamp(1 - Math.abs(t - T.rw - .06) / .22) * .95).toFixed(3)}"/>`;
    // ambiance souvenir
    ui += `<rect width="1080" height="1920" fill="url(#fbVig)" opacity="${sep.toFixed(3)}"/><rect width="1080" height="1920" fill="url(#fbFuite)" opacity="${(sep * (.55 + .25 * Math.sin(t * 2.3))).toFixed(3)}"/>`;
    const img = Math.floor(t * 12);
    for (let i = 0; i < 22; i++) { const h = Math.sin((img * 31 + i) * 12.9898) * 43758.5453, u = h - Math.floor(h), v = (h * 7.3) - Math.floor(h * 7.3); ui += `<circle cx="${(u * 1080).toFixed(0)}" cy="${(v * 1920).toFixed(0)}" r="${(1.5 + (i % 3) * 1.5).toFixed(1)}" fill="${i % 4 ? '#fff6e0' : '#2a1606'}" opacity="${(.5 * sep).toFixed(2)}"/>`; }
    if (Math.sin(img * 3.7) > .6) ui += `<rect x="${(200 + (img * 97) % 700)}" y="0" width="2" height="1920" fill="#fff6e0" opacity="${(.35 * sep).toFixed(2)}"/>`;
    // carton « 48 H AVANT » avec horloge qui tourne à l'envers, puis étiquette dans le coin
    const ent = E.backS(clamp((t - T.rw - .08) / .35)), red = E.io(clamp((t - P('l01') - .45) / .4));
    const cx = mix(540, 270, red), cy = mix(560, 190, red), sc = mix(1, .42, red) * ent;
    const aig = -(t - T.rw) * 900;
    ui += `<g transform="translate(${cx.toFixed(1)},${cy.toFixed(1)}) scale(${sc.toFixed(3)})" opacity="${clamp(ent * 1.5).toFixed(3)}">
      <rect x="-460" y="-130" width="920" height="260" rx="34" fill="#1d1208" opacity=".82"/>
      <rect x="-444" y="-114" width="888" height="228" rx="24" fill="none" stroke="#ffd23f" stroke-width="5" opacity=".9"/>
      <g transform="translate(-320,0)"><circle r="84" fill="#fff6e0" stroke="#ffd23f" stroke-width="10"/>
        ${Array.from({ length: 12 }, (_, i) => `<rect x="-3" y="-74" width="6" height="${i % 3 ? 10 : 18}" fill="#1d1208" transform="rotate(${i * 30})"/>`).join('')}
        <rect x="-5" y="-62" width="10" height="62" rx="5" fill="#1d1208" transform="rotate(${(aig / 12).toFixed(1)})"/><rect x="-4" y="-74" width="8" height="74" rx="4" fill="#e5484d" transform="rotate(${aig.toFixed(1)})"/><circle r="9" fill="#1d1208"/>
        <path d="M-118,-30 A120,120 0 0 1 -60,-104" fill="none" stroke="#ffd23f" stroke-width="10" stroke-linecap="round"/><path d="M-138,-44 L-116,-6 L-92,-42 Z" fill="#ffd23f"/></g>
      <text x="-196" y="40" text-anchor="start" font-family="Bricolage" font-weight="800" font-size="118" fill="#ffd23f" letter-spacing="-2">48 H AVANT</text>
    </g>`;
  }
  window._uiSdb = ui;
}

// ---------------------------------- JARDIN ----------------------------------
function rJardin(t, mode) {
  let fx = '', cam, sh = { x: 0, y: 0, r: 0 };
  const cl = { x: 300, y: 1600, s: .92, cligne: clignementG(t, 3), tourne: .5, regardX: 1, bouche: 'sourire' };
  const vo = { x: 790, y: 1600, s: .92, cligne: clignementG(t, 8), tourne: -.5, regardX: -1, bouche: 'ferme' };
  const g = { x: 2000, y: 1580, s: .82, cligne: clignementG(t, 6), queue: Math.sin(t * 4) * 8 };
  let fantome = false;
  if (mode === 'haie') {
    cam = piste(t, [[T.j, { x: 540, y: 1100, z: 1.25 }], [T.g, { x: 540, y: 1060, z: 1.4 }, E.sine]]);
    const don = M('l02', 3) - .1, k = E.io(clamp((t - don) / .45));
    cl.mainD = { x: mix(110, 300, k), y: mix(-330, -560, k) }; cl.coudeD = -1; cl.bouche = 'rire';
    vo.mainG = { x: mix(-110, -230, clamp((t - don - .3) / .3)), y: mix(-330, -560, clamp((t - don - .3) / .3)) }; vo.coudeG = 1; vo.bouche = t > don + .5 ? 'sourire' : 'o';
    const px = mix(300 + 300 * .92, 790 - 230 * .92, E.io(clamp((t - don - .2) / .5))), py = 1600 - 560 * .92 - 30;
    fx += `<g transform="translate(${px},${py}) rotate(${-6 + Math.sin(t * 5) * 3}) scale(${apparition(t, don - .1, .3)})"><rect x="-90" y="-80" width="180" height="160" fill="#ffe36b" stroke="#d9b62e" stroke-width="4"/><text y="-10" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="34" fill="#15181e">Gaspard</text><text y="34" text-anchor="middle" font-size="26" fill="#e58a00">★★★★★</text></g>`;
    fx += motCle(540, 560, 'TON NOM', t, M('l02', 4) - .05, .9, '#ffd23f', 104, -5);
    if (t > don + .6) fx += coeur(300, 760 - (t - don) * 60, 1, '#ff5d73', clamp(1.6 - (t - don)));
  } else if (mode === 'fantome') {
    cam = piste(t, [[T.fa, { x: 540, y: 1040, z: 1.3 }], [T.vi, { x: 540, y: 1000, z: 1.42 }, E.sine]]);
    Object.assign(g, { x: 540, y: 1600, s: 1.0, bouche: 'o', oeil: 1.2, sourcils: 1.1, regardX: -.5, mainG: { x: -220, y: -420 }, mainD: { x: 220, y: -420 }, coudeG: 1, coudeD: -1, formeG: 'salut', formeD: 'salut' });
    fantome = true;
    // la cliente passe la main à travers lui
    const k = E.io(clamp((t - M('l08', 8)) / .5));
    cl.x = 200; cl.mainD = { x: mix(150, 560, k), y: -500 }; cl.coudeD = -1; cl.bouche = 'o'; cl.regardX = 1;
    vo.x = 900; vo.bouche = 'o';
    window._uiTampon = tampon(540, 600, 'INTROUVABLE', '#9fb4c8', t, M('l08', 7) - .1, 6, 78);
    for (let i = 0; i < 5; i++) { const u = ((t * .6 + i / 5) % 1); fx += `<circle cx="${420 + i * 60 + Math.sin(u * 6) * 20}" cy="${1250 - u * 600}" r="${6 + i % 3 * 3}" fill="#cfe9ff" opacity="${Math.sin(Math.PI * u) * .7}"/>`; }
  } else if (mode === 'appel') {
    // le téléphone de Gaspard vibre : salto de joie sur « Toi »
    cam = piste(t, [[T.wJ, { x: 540, y: 1000, z: 1.5 }], [T.fi, { x: 540, y: 1040, z: 1.35 }, E.sine]]);
    const toi = T.wJ + .4,   // salto quand le téléphone sonne (le « Toi » a été retiré)
      s = saut(t, toi, .26, 260), rot = t > toi ? 360 * E.io(clamp((t - toi) / .55)) : 0;
    const h = 320, th = rot * Math.PI / 180;   // salto autour du centre du corps (et non des pieds)
    Object.assign(g, { x: 540 - h * Math.sin(th), y: 1580 + s.y - h + h * Math.cos(th), s: 1.0, sx: s.sx, sy: s.sy, rot, bouche: t > toi ? 'rire' : 'o', oeil: 1.15, paup: 0, sourcils: 1, mainD: { x: 165, y: -405 }, objetD: 'tel', formeD: 'tient', telCeinture: false, regardX: .5, regardY: -.2 });
    cl.x = 1800; vo.x = 2000;
    fx += vibrations(540 + 200, 1580 - 420, t, t < toi, 80, '#3a1a06') + onomatopee(640, 600, 'DRIIING !', t, T.wJ, .6, '#ffd23f', 100, 8);
    fx += etincelles(540, 1100, t, toi + .5, 10, 300, '#ffd23f', .8);
  } else {
    // final : tope là à trois, tampon CHANTIER GAGNÉ
    cam = piste(t, [[T.fi, { x: 540, y: 1060, z: 1.22 }], [T.sd, { x: 540, y: 1020, z: 1.34 }, E.sine]]);
    const tope = M('l11', 4) - .1, lev = E.backS(clamp((t - tope + .45) / .4));
    Object.assign(g, { x: 540, y: 1620, s: .9, bouche: 'rire', paup: 0, sourcils: .8, mainD: { x: 100, y: mix(-300, -580, lev) }, mainG: { x: -100, y: mix(-300, -580, lev) }, coudeD: 1, coudeG: -1, formeD: 'salut', formeG: 'salut' });
    cl.x = 230; cl.mainD = { x: mix(110, 300, lev), y: mix(-330, -580, lev) }; cl.coudeD = -1; cl.bouche = 'rire';
    vo.x = 850; vo.mainG = { x: mix(-110, -300, lev), y: mix(-330, -580, lev) }; vo.coudeG = 1; vo.bouche = 'rire';
    fx += impact(540, 1090, t, tope, 1.3, '#ffd23f') + etincelles(540, 1090, t, tope, 10, 280, '#ffd23f', .8) + confettis(540, 1000, t, tope + .05, 32, 1.8);
    window._uiTampon = tampon(540, 560, 'CHANTIER GAGNÉ', '#22a05a', t, M('l11', 9) - .1, -7, 78);
    if (t > tope + .1) sh = tremble(t, tope, 14, .25);
  }
  camera('ja-cam', cam.x + bruit(t, 11) * 3, cam.y + bruit(t, 12) * 3, cam.z, 0, sh);
  PERS.cj.poser(cl); PERS.vj.poser(vo); PERS.gj.poser(g);
  // fantôme : transparent, teinté de bleu, contour pointillé
  const gj = $('gj-root');
  if (gj) { gj.style.opacity = fantome ? (.32 + .08 * Math.sin(t * 6)) : 1; gj.style.filter = fantome ? 'grayscale(1) brightness(1.7) contrast(.75) drop-shadow(0 0 18px #9fd3ff)' : ''; }
  if (fantome) fx += `<ellipse cx="540" cy="1260" rx="230" ry="370" fill="none" stroke="#cfe9ff" stroke-width="6" stroke-dasharray="18 16" opacity=".7"/>`;
  $('ja-fx').innerHTML = fx;
}

// ---------------------------------- GOOGLE ----------------------------------
function carteResultat(y, o) {
  // o : { titre, url, note, avis, info, photo (svg), site (bool), dore (bool), floue (bool) }
  return `<g transform="translate(-252,${y})">
    ${o.dore ? `<rect x="-14" y="-14" width="532" height="${o.h + 28}" rx="36" fill="none" stroke="url(#gOr)" stroke-width="8"/>` : ''}
    <rect width="504" height="${o.h}" rx="28" fill="#fff" stroke="#ece6da" stroke-width="2"/>
    <text x="24" y="44" font-family="Geist" font-size="20" fill="#5f6368">${o.url}</text>
    <text x="24" y="86" font-family="Geist" font-weight="700" font-size="30" fill="${o.site ? '#1a0dab' : '#15181e'}">${o.titre}</text>
    ${etoiles(o.note, 24, 124, 24, '#f2a516')}<text x="160" y="124" font-family="Geist" font-size="21" fill="#5f6368">${o.avis}</text>
    <text x="24" y="160" font-family="Geist" font-size="20" fill="${o.site ? '#1f8f5a' : '#9a9184'}">${o.info}</text>
    ${o.photo || ''}</g>`;
}
function rGoogle(t, mode) {
  const perdu = mode === 'perdu';
  let ec = '', fx = '';
  const t0 = perdu ? T.g : T.w;
  const cam = perdu ? piste(t, [[T.g, { x: 540, y: 900, z: 1.0 }], [M('l03', 9) - .25, { x: 540, y: 520, z: 1.6 }, E.expoIo], [P('l04') - .1, { x: 540, y: 540, z: 1.55 }, E.sine], [P('l05') - .05, { x: 540, y: 700, z: 1.5 }, E.expoIo], [T.ap, { x: 540, y: 730, z: 1.58 }, E.sine]])
    : piste(t, [[T.w, { x: 540, y: 760, z: 1.3 }], [M('l10', 9) - .3, { x: 540, y: 720, z: 1.42 }, E.sine], [T.wJ, { x: 540, y: 720, z: 1.5 }, E.sine]]);
  camera('go-cam', cam.x, cam.y, cam.z);
  // barre de recherche et frappe
  const req = 'Gaspard rénovation', n = perdu ? Math.floor(clamp((t - M('l03', 9)) / .7) * req.length) : req.length;
  ec += `<g transform="translate(0,-470)"><text x="0" y="0" text-anchor="middle" font-family="Geist" font-weight="700" font-size="58" fill="#4285f4">G<tspan fill="#ea4335">o</tspan><tspan fill="#fbbc05">o</tspan><tspan fill="#4285f4">g</tspan><tspan fill="#34a853">l</tspan><tspan fill="#ea4335">e</tspan></text></g>
    <rect x="-252" y="-430" width="504" height="76" rx="38" fill="#fff" stroke="#dfe1e5" stroke-width="3"/>
    <circle cx="-210" cy="-392" r="13" fill="none" stroke="#9aa0a6" stroke-width="4"/><path d="M-200,-382 l10,10" stroke="#9aa0a6" stroke-width="4" stroke-linecap="round"/>
    <text x="-180" y="-382" font-family="Geist" font-size="27" fill="#15181e">${req.slice(0, n)}${perdu && n < req.length && Math.sin(t * 18) > 0 ? '|' : ''}</text>`;
  if (perdu) {
    const r1 = E.out(clamp((t - P('l04') + .1) / .35)), r2 = E.out(clamp((t - P('l05') + .05) / .4));
    // résultat 1 : la vieille page Facebook de Gaspard
    const photoFloue = `<g transform="translate(380,30)"><rect width="100" height="100" rx="16" fill="#8aa1b8"/><g filter="url(#flouPhoto)"><circle cx="50" cy="56" r="30" fill="#a8643a"/><path d="M20,30 L80,30 L74,18 L26,18 Z" fill="#ffc21a"/><path d="M60,70 q30,-10 34,10 q-20,10 -34,-2 z" fill="#7fb3d5"/></g></g>`;
    if (r1 > 0) ec += `<g opacity="${r1}" transform="translate(0,${(1 - r1) * 40})">${carteResultat(-320, { h: 190, url: 'facebook.com › gaspard.btp', titre: 'Gaspard BTP', note: 2, avis: '(2 avis)', info: 'Dernière publication : 2019', photo: photoFloue })}</g>`;
    if (t > M('l04', 6) - .1) fx += `<g transform="translate(${540 + 250},${880 - 320 * .94 + 60})">${motCle(0, 0, '2019', t, M('l04', 6) - .1, 1.1, '#ff6b62', 64, 8)}</g>`;
    // criquets : le silence gêné
    let fxE = '';
    if (t > M('l04', 9) - .1 && t < P('l05')) fxE += onomatopee(760, 260, 'cri… cri…', t, M('l04', 9) - .1, Math.max(.4, P('l05') - M('l04', 9)), '#9be36b', 72, -4);
    // résultat 2 : le concurrent, avec halo doré et compteur d'avis
    const avis = Math.round(48 * E.out(clamp((t - M('l05', 12)) / .5)));
    const galerie = `<g transform="translate(24,176)">${[0, 1, 2].map(i => `<image href="photos/${['real1', 'real2', 'real3'][i]}.webp" x="${i * 156}" y="0" width="148" height="104" preserveAspectRatio="xMidYMid slice"/><rect x="${i * 156}" y="0" width="148" height="104" rx="12" fill="none" stroke="#ece6da" stroke-width="2"/>`).join('')}</g>`;
    if (r2 > 0) ec += `<g opacity="${r2}" transform="translate(0,${(1 - r2) * 120})">${carteResultat(-100, { h: 300, url: 'renov-express.fr', titre: 'Rénov Express · Salles de bain', note: 5, avis: `4,8 (${avis} avis)`, info: '✓ Site web · Photos · Devis en ligne', site: true, dore: true, photo: galerie })}</g>`;
    if (r2 > 0) fxE += onomatopee(820, 380, 'DING !', t, P('l05') - .05, .55, '#ffd23f', 90, 6);
    $('go-fxE').innerHTML = fxE;
    if (t > M('l05', 12) - .1) fx += `<g transform="translate(${540 + 150},${880 + 120})">${motCle(0, 0, '48 avis', t, M('l05', 12) - .1, 1.2, '#ffd23f', 84, -6)}</g>`;
    // Gaspard en incrustation, gêné, qui se cache derrière son casque
    const gene = t > P('l04') + .3;
    PERS.gg.poser({ x: 880, y: 1880 + (1 - apparition(t, P('l04') + .2, .35)) * 500, s: .62, tourne: -.3, cligne: clignementG(t, 4), bouche: gene ? 'grimace' : 'o', sourcils: 1.1, froncer: .8, sueur: gene ? 1 : 0,
      mainD: gene ? { x: 70, y: -420 } : { x: 210, y: -170 }, mainG: gene ? { x: -70, y: -420 } : { x: -210, y: -170 }, coudeD: -1, coudeG: 1, casque: gene ? 30 : 0, regardY: .4, ombre: 0 });
  } else {
    // gagné : le site de Gaspard en tête, la voisine appuie sur Appeler
    const r1 = E.out(clamp((t - T.w - .1) / .35));
    const vignette = `<g transform="translate(24,176)"><rect width="456" height="110" rx="14" fill="#15181e"/><text x="24" y="48" font-family="Bricolage" font-weight="800" font-size="28" fill="#fff">Votre salle de bain</text><text x="24" y="84" font-family="Bricolage" font-weight="800" font-size="28" fill="#e8b84a">refaite en 3 semaines</text><circle cx="400" cy="55" r="34" fill="#ffc21a"/><text x="400" y="66" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="30" fill="#15181e">G</text></g>`;
    ec += `<g opacity="${r1}" transform="translate(0,${(1 - r1) * 60})">${carteResultat(-320, { h: 400, url: 'gaspard-renovation.fr', titre: 'Gaspard Rénovation · Salles de bain', note: 5, avis: '4,9 (39 avis)', info: '✓ Réalisations · Zone · Délais · Tarifs', site: true, dore: true, photo: vignette + `<g id="go-btn" transform="translate(252,340)"><rect x="-228" y="-34" width="456" height="68" rx="34" fill="#22a05a"/><text y="12" text-anchor="middle" font-family="Geist" font-weight="700" font-size="28" fill="#fff">Appeler</text></g>` })}</g>`;
    ec += `<g opacity=".45">${carteResultat(110, { h: 190, url: 'renov-express.fr', titre: 'Rénov Express', note: 4, avis: '4,8 (48 avis)', info: '✓ Site web' })}</g>`;
    fx += motCle(540, 300, 'Trouvé !', t, P('l10') + .5, 1, '#ffd23f', 96, -4);
    // pouce de la voisine qui appuie sur Appeler
    const tap = M('l10', 10) - .15, k = E.io(clamp((t - tap + .5) / .5)), appui = clamp(1 - Math.abs(t - tap) / .1);
    const bx = 540, by = 880 + (-320 + 340) * .94;
    const px = mix(1000, bx + 40, k), py = mix(1700, by + 30, k);
    fx += `<g transform="translate(${px},${py}) rotate(-30)"><path d="M-46,10 C-50,-60 -30,-104 4,-106 C40,-108 52,-64 50,4 L60,420 L-70,420 Z" fill="#c98d66" stroke="#8f5a3a" stroke-width="5"/><path d="M-26,-60 C-24,-90 -6,-98 10,-97 C28,-96 34,-80 32,-58 C30,-42 -24,-40 -26,-60 Z" fill="#e8b496"/></g>`;
    if (appui > 0) fx += `<circle cx="${bx}" cy="${by}" r="${40 + (1 - appui) * 70}" fill="none" stroke="#22a05a" stroke-width="7" opacity="${appui}"/>`;
    PERS.gg.poser({ x: 2000, y: 0 }); $('go-fxE').innerHTML = '';
  }
  $('go-tel-ecran').innerHTML = ec;
  $('go-fx').innerHTML = fx;
}

// ---------------------------------- APPEL (écran partagé) ----------------------------------
function rAppel(t) {
  const u = t - T.ap, k = E.out(clamp(u / .3));
  tr('ap-haut', `translate(0,${(1 - k) * -960})`); tr('ap-bas', `translate(0,${(1 - k) * 960})`);
  // haut : le téléphone de Rénov Express sonne
  const vib = Math.sin(t * 70) * 6;
  tr('ap-telC', `translate(${540 + vib},500) rotate(${vib * .4}) scale(.62)`);
  $('ap-tc-ecran').innerHTML = `<rect x="-288" y="-610" width="576" height="1220" fill="#1a2234"/>
    ${[0, 1].map(i => { const q = ((t * .9 + i * .5) % 1); return `<circle cx="0" cy="-230" r="${110 + q * 120}" fill="none" stroke="#7ee0a0" stroke-width="5" opacity="${(1 - q) * .7}"/>`; }).join('')}
    <circle cx="0" cy="-230" r="110" fill="#c8875a"/><text x="0" y="-200" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="84" fill="#fff">RE</text>
    <text x="0" y="-50" text-anchor="middle" font-family="Geist" font-weight="700" font-size="54" fill="#fff">Appel entrant</text>
    <text x="0" y="10" text-anchor="middle" font-family="Geist" font-size="34" fill="#aab3c5">Voisine · 92</text>
    <g transform="translate(-140,380)"><circle r="70" fill="#ff3b30"/></g><g transform="translate(140,380)"><circle r="70" fill="#22c55e"/></g>`;
  $('ap-fxH').innerHTML = vibrations(540, 500, t, true, 230, '#7ee0a0') + onomatopee(220, 170, 'DRING !', t, T.ap + .1, 1.2, '#ffd23f', 92, -10) + onomatopee(860, 820, 'DRING !', t, T.ap + .6, 1.2, '#ffd23f', 76, 8);
  // bas : Gaspard fixe son téléphone muet, une mouche passe
  const z = 1 + .25 * E.sine(clamp(u / 2));
  PERS.ga.poser({ x: 540, y: 2080 + 30 * z, s: .78 * z, tourne: .1, cligne: clignementG(t, 7), bouche: 'plat', paup: .35, sourcils: -.2, froncer: .3, regardX: .4, regardY: .5,
    mainD: { x: 150, y: -330 }, objetD: 'tel', formeD: 'tient', telCeinture: false, ombre: 0 });
  const mx = 540 + Math.sin(t * 2.3) * 300, my = 1240 + Math.sin(t * 4.1) * 120;
  $('ap-fxB').innerHTML = mouche(mx, my, t) + `<path d="M${mx - 20},${my} q-30,-20 -60,0 q-30,20 -60,0" stroke="#15181e" stroke-width="3" fill="none" stroke-dasharray="6 8" opacity=".4"/>` + onomatopee(mx + 90, my - 60, 'bzzz…', t, T.ap + .4, 2, '#15181e', 44, 0)
    + motCle(540, 1040, '…', t, T.ap + .4, 1.4, '#fff', 120, 0);
}

// ---------------------------------- VÉRIFICATION ----------------------------------
function rVerif(t) {
  const u = t - T.v;
  let s = '';
  // post-it « recommandé » qui arrive, puis la loupe qui vérifie
  const a = E.backS(clamp(u / .3));
  s += `<g transform="translate(540,560) rotate(${-6 + (1 - a) * 30}) scale(${a})"><rect x="-200" y="-160" width="400" height="320" fill="#ffe36b" stroke="#d9b62e" stroke-width="6"/><text y="-30" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="56" fill="#15181e">Gaspard</text><text y="50" text-anchor="middle" font-size="52" fill="#e58a00">★★★★★</text><text y="120" text-anchor="middle" font-family="Geist" font-size="30" fill="#6b6357">recommandé par Sophie</text></g>`;
  const l = E.io(clamp((u - .45) / .6)), lx = mix(860, 600, l) + Math.sin(u * 5) * 40 * l, ly = mix(1300, 600, l);
  s += `<g transform="translate(${lx},${ly}) rotate(-35)"><circle r="120" fill="#bfe3f7" fill-opacity=".25" stroke="#fff" stroke-width="22"/><rect x="-16" y="120" width="32" height="170" rx="16" fill="#e8b84a"/></g>`;
  // check-list qui se remplit de croix rouges (rétention : petit suspense par ligne)
  const items = [['Avis Google ?', .2], ['Photos de chantiers ?', .55], ['Site internet ?', .9]];
  const base = M('l07', 5) - .1;
  items.forEach(([txt, d], i) => {
    const k = E.backS(clamp((t - base - d) / .25)), x = clamp((t - base - d - .2) / .2);
    if (k <= 0) return;
    s += `<g transform="translate(170,${1000 + i * 110}) scale(${k})"><rect x="0" y="-50" width="740" height="90" rx="24" fill="#22262f"/><text x="40" y="12" font-family="Bricolage" font-weight="700" font-size="44" fill="#fff">${txt}</text>
      <g transform="translate(660,-4) scale(${E.backS(x)})"><circle r="30" fill="#e5484d"/><path d="M-12,-12 L12,12 M12,-12 L-12,12" stroke="#fff" stroke-width="7" stroke-linecap="round"/></g></g>`;
  });
  s += motCle(540, 300, 'VÉRIFIÉ ?', t, M('l07', 4) - .1, 1.6, '#ffd23f', 110, -3);
  $('ve-cont').innerHTML = s;
}

// ---------------------------------- VITRINE : le site se construit ----------------------------------
function rVitrine(t) {
  const u = t - T.vi;
  camera('vi-cam', 560 + bruit(t, 21) * 2, 880 + bruit(t, 22) * 2, mix(1.0, 1.08, clamp(u / 4)));
  const mots = [M('l09', 6), M('l09', 8), M('l09', 10), M('l09', 12)];   // réalisations, avis, zone, numéro
  const blocs = [
    (k) => `<g transform="translate(0,-330) scale(${k})"><text x="-240" y="-40" font-family="Bricolage" font-weight="800" font-size="30" fill="#15181e">Nos réalisations</text>${[0, 1, 2, 3].map(i => `<image href="photos/${['real1', 'real2', 'real3', 'real4'][i]}.webp" x="${-240 + (i % 2) * 244}" y="${-10 + Math.floor(i / 2) * 150}" width="236" height="140" preserveAspectRatio="xMidYMid slice"/>`).join('')}</g>`,
    (k) => `<g transform="translate(0,40) scale(${k})"><rect x="-240" y="-60" width="480" height="120" rx="24" fill="#fff" stroke="#ece6da" stroke-width="3"/><text x="-210" y="12" font-family="Bricolage" font-weight="800" font-size="56" fill="#15181e">4,9</text>${etoiles(5, -110, 0, 30)}<text x="-110" y="40" font-family="Geist" font-size="22" fill="#6b6357">39 avis Google</text></g>`,
    (k) => `<g transform="translate(0,210) scale(${k})"><rect x="-240" y="-70" width="480" height="140" rx="24" fill="#e4ecf2"/><circle cx="-20" cy="0" r="${48 + Math.sin(t * 4) * 6}" fill="#4285f4" opacity=".2"/><path d="M-20,-34 c-18,0 -26,12 -26,24 c0,18 26,40 26,40 c0,0 26,-22 26,-40 c0,-12 -8,-24 -26,-24 z" fill="#ea4335"/><text x="40" y="-8" font-family="Geist" font-weight="700" font-size="24" fill="#15181e">Boulogne · Issy</text><text x="40" y="26" font-family="Geist" font-weight="700" font-size="24" fill="#15181e">Clamart · Meudon</text></g>`,
    (k) => `<g transform="translate(0,370) scale(${k})"><rect x="-240" y="-44" width="480" height="88" rx="44" fill="#22a05a"/><text y="14" text-anchor="middle" font-family="Geist" font-weight="700" font-size="34" fill="#fff">Appeler · 06 12 34 56 78</text></g>`,
  ];
  let ec = `<g transform="translate(0,-490)"><circle cx="-210" r="26" fill="#15181e"/><text x="-210" y="10" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="28" fill="#ffc21a">G</text><text x="-170" y="-4" font-family="Bricolage" font-weight="800" font-size="30" fill="#15181e">Gaspard Rénovation</text><text x="-170" y="26" font-family="Geist" font-size="20" fill="#6b6357">Salles de bain · Hauts-de-Seine</text></g>`;
  let fx = '';
  blocs.forEach((b, i) => {
    const k = E.backS(clamp((t - mots[i] + .15) / .3));
    if (k <= 0) { ec += `<rect x="-240" y="${[-340, -20, 140, 326][i]}" width="480" height="${[300, 120, 140, 88][i]}" rx="24" fill="none" stroke="#c9bfae" stroke-width="3" stroke-dasharray="12 10"/>`; return; }
    ec += b(k);
    fx += etincelles(470, 820 + [-330, 40, 210, 370][i] * .96, t, mots[i], 7, 170, '#ffd23f', .6);
  });
  const labels = ['Réalisations', 'Avis', 'Zone', 'Numéro'];
  labels.forEach((lab, i) => { fx += motCle(470 + (i % 2 ? 220 : -220), 220 + (i % 2) * 40, lab, t, mots[i] - .1, .75, '#ffd23f', 76, i % 2 ? 6 : -6); });
  $('vi-tel-ecran').innerHTML = ec;
  $('vi-fx').innerHTML = fx;
  // Gaspard pose les blocs un par un (geste vers le téléphone), puis pouce levé
  let dernier = -1; mots.forEach((m, i) => { if (t > m - .3) dernier = i; });
  const w = dernier >= 0 ? t - mots[dernier] : -1, geste = w > -.3 && w < .25;
  PERS.gv.poser({ x: 910, y: 1700, s: .72, tourne: -.4, cligne: clignementG(t, 9), bouche: geste ? 'ouvert' : 'sourire', sourcils: .5, regardX: -.9,
    mainG: geste ? { x: -250, y: -340 } : { x: -210, y: -170 }, coudeG: geste ? 1 : -1, formeG: geste ? 'ouverte' : 'ouverte', mainD: t > mots[3] + .4 ? { x: 230, y: -330 } : { x: 210, y: -170 }, formeD: t > mots[3] + .4 ? 'pouce' : 'ouverte', ombre: 0 });
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

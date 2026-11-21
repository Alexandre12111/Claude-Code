// Montage de l'épisode 1 : construction des scènes et rendu d'une image au temps t.
// Les repères temporels viennent de la voix (calage.json) : P(id) = début de la phrase, F(id) = fin, M(id, k) = début du mot k.

function construireCalage(script, cal) {
  const ph = {};
  const liste = script.phrases;
  liste.forEach((p, i) => {
    let debut, fin, mots;
    if (cal && cal.phrases && cal.phrases[p.id]) ({ debut, fin, mots } = cal.phrases[p.id]);
    else {
      debut = p.debut_prevu;
      const suiv = liste[i + 1] ? liste[i + 1].debut_prevu : debut + 3.6;
      fin = suiv - .3;
    }
    const nb = p.sous_titre.replace(/\*/g, '').split(/\s+/).length;
    if (!mots || mots.length !== nb) {
      const brut = p.sous_titre.replace(/\*/g, '').split(/\s+/);
      const poids = brut.map(m => m.length + 2), tot = poids.reduce((a, b) => a + b, 0);
      let c = debut; mots = brut.map((m, k) => { const d = (fin - debut) * poids[k] / tot; const o = { debut: c, fin: c + d }; c += d; return o; });
    }
    ph[p.id] = { debut, fin, mots };
  });
  const derniere = ph[liste[liste.length - 1].id];
  return { phrases: ph, duree: (cal && cal.duree) || derniere.fin + .6 + script.fin.duree, finTexte: derniere.fin + .5 };
}
const P = id => CAL.phrases[id].debut;
const F = id => CAL.phrases[id].fin;
const M = (id, k) => CAL.phrases[id].mots[Math.min(k, CAL.phrases[id].mots.length - 1)].debut;

const L = {};  // calques
function calque(id, html) { const g = el($('svg'), html); g.id = id; L[id] = g; return g; }
function camera(id, cx, cy, z, r = 0) { tr(id, `translate(540,960) rotate(${r}) scale(${z}) translate(${-cx},${-cy})`); }

// Illustration de salle de bain (pour le site de l'artisan) : fini = true (après) ou false (avant travaux)
function illusSdb(w, h, fini, v = 0) {
  if (!fini) return `<rect width="${w}" height="${h}" fill="#b9b2a6"/><rect y="${h * .62}" width="${w}" height="${h * .38}" fill="#8f877b"/>
    <path d="M${w * .1},${h * .2} l${w * .15},${h * .1} M${w * .5},${h * .1} l${w * .2},${h * .2} M${w * .7},${h * .45} l${w * .2},-${h * .05}" stroke="#7f776c" stroke-width="3"/>
    <rect x="${w * .62}" y="${h * .44}" width="${w * .22}" height="${h * .3}" fill="#d9d2c6"/><rect x="${w * .1}" y="${h * .5}" width="${w * .3}" height="${h * .22}" fill="#6f675c"/>`;
  const carreaux = Array.from({ length: 8 }, (_, i) => `<path d="M0,${i * h * .08} H${w}" stroke="#d5dde0" stroke-width="2"/>`).join('') + Array.from({ length: 10 }, (_, i) => `<path d="M${i * w * .11},0 V${h * .62}" stroke="#d5dde0" stroke-width="2"/>`).join('');
  const teinte = ['#e8edef', '#efe7dc', '#e3ebe6'][v % 3];
  return `<rect width="${w}" height="${h}" fill="${teinte}"/>${carreaux}<rect y="${h * .62}" width="${w}" height="${h * .38}" fill="#c7b59a"/>
    <rect x="${w * .08}" y="${h * .46}" width="${w * .42}" height="${h * .26}" rx="${h * .05}" fill="#fff"/><rect x="${w * .08}" y="${h * .46}" width="${w * .42}" height="${h * .05}" rx="${h * .025}" fill="#e6ecef"/>
    <rect x="${w * .6}" y="${h * .14}" width="${w * .26}" height="${h * .3}" rx="${h * .04}" fill="#cfe0ea" stroke="#d9b062" stroke-width="3"/>
    <rect x="${w * .58}" y="${h * .5}" width="${w * .3}" height="${h * .2}" fill="#8a6a49"/><rect x="${w * .58}" y="${h * .48}" width="${w * .3}" height="${h * .03}" fill="#f4f1ea"/>
    <path d="M${w * .92},${h * .66} c-${w * .02},-${h * .1} -${w * .06},-${h * .14} -${w * .02},-${h * .24} c${w * .02},${h * .08} ${w * .05},${h * .12} ${w * .02},${h * .24} z" fill="#5e8a5a"/>
    <rect x="0" y="0" width="${w}" height="${h}" fill="url(#soleil)" opacity=".5"/>`;
}

// ---------------------------------------------------------------------------------------------
function construireScenes() {
  $('defs').innerHTML = DEFS + `<clipPath id="clipIris"><circle id="irisC" cx="540" cy="760" r="0"/></clipPath>`;

  // ================= Scène 1 : le téléphone qui sonne sur la planche =================
  let planches = '';
  for (let i = 0; i < 5; i++) {
    const y = -300 + i * 560;
    planches += `<rect x="-400" y="${y}" width="1900" height="540" fill="url(#boisDessus)"/>
      <rect x="-400" y="${y}" width="1900" height="540" fill="${['#000', '#fff', '#000', '#fff', '#000'][i]}" opacity="${[.04, .03, .07, .02, .05][i]}"/>
      ${Array.from({ length: 14 }, (_, k) => { const yy = y + 20 + k * 37 + rnd() * 14; return `<path d="M-400,${yy} C0,${yy + rnd() * 30 - 15} 600,${yy + rnd() * 30 - 15} 1500,${yy}" stroke="#a8743d" stroke-width="${1 + rnd() * 2.5}" fill="none" opacity="${.25 + rnd() * .3}"/>`; }).join('')}
      <ellipse cx="${100 + rnd() * 800}" cy="${y + 120 + rnd() * 300}" rx="${30 + rnd() * 20}" ry="${12 + rnd() * 8}" fill="none" stroke="#9a6532" stroke-width="3" opacity=".35"/>
      <rect x="-400" y="${y + 540}" width="1900" height="20" fill="#2a1d12"/>`;
  }
  let platre = '';
  for (let i = 0; i < 22; i++) {
    const x = rnd() * 1080, y = rnd() * 1920, r = 8 + rnd() * 46;
    if (Math.abs(x - 540) < 230 && Math.abs(y - 820) < 420) continue;
    platre += `<path d="M${x - r},${y} C${x - r},${y - r * .8} ${x + r * .6},${y - r} ${x + r},${y - r * .2} C${x + r * 1.2},${y + r * .6} ${x},${y + r} ${x - r * .6},${y + r * .7} Z" fill="#f1ede4" opacity="${.75 + rnd() * .25}"/>`;
  }
  calque('hook', `<g id="hook-cam">
    ${planches}${platre}
    <g transform="translate(150,330) rotate(-28)" filter="url(#ombreDouce)"><rect x="-26" y="-210" width="52" height="150" rx="20" fill="#7a4b2a"/><rect x="-20" y="-200" width="10" height="130" rx="5" fill="#9b6a42" opacity=".6"/><path d="M-170,-60 L170,-60 L160,40 L-160,40 Z" fill="#b9c0c8"/><path d="M-170,-60 L170,-60 L168,-50 L-168,-50 Z" fill="#eef2f5"/><path d="M-120,20 C-40,40 60,10 140,30 L150,40 L-160,40 Z" fill="#ece8df"/></g>
    <g transform="translate(900,1560) rotate(12)" filter="url(#ombreDouce)"><path d="M-70,-90 L70,-90 L60,90 C58,104 -58,104 -60,90 Z" fill="#e9772e"/><ellipse cx="0" cy="-90" rx="70" ry="18" fill="#c95f1f"/><ellipse cx="0" cy="-90" rx="60" ry="13" fill="#efe9de"/></g>
    <g transform="translate(170,1500) rotate(-12)" filter="url(#ombreDouce)"><rect x="-90" y="-16" width="180" height="32" rx="8" fill="#e8b93a"/><rect x="-40" y="-9" width="80" height="18" rx="9" fill="#9bd36d"/><circle cx="0" cy="0" r="5" fill="#fff" opacity=".8"/></g>
    <g id="hook-ondes" fill="none" stroke="#fff">${[0, 1, 2].map(i => `<rect id="onde${i}" x="-200" y="-400" width="400" height="800" rx="64" stroke-width="6"/>`).join('')}</g>
    <g id="hook-tel" filter="url(#ombreDouce)">
      ${telephone('tel1', 380, 780, ecranAppel(346, 746, 'Numéro inconnu', 'Appel entrant…'))}
      <g id="hook-badge" transform="translate(178,-378)"><circle r="82" fill="url(#or)"/><circle r="82" fill="none" stroke="#fff" stroke-opacity=".4" stroke-width="4"/>
        <text id="hook-nb" x="0" y="28" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="84" fill="#1a1206">9</text></g>
    </g>
  </g>`);

  // ================= Extérieur : façade, échafaudage, artisan (scènes 2, 3, 4, 10, 11) =================
  const fac = facade(1900, 2400, { cols: 5, rows: 7 });
  const toit = `<path d="M-500,110 L1600,110 L1560,330 L-460,330 Z" fill="#5f6b78"/>
    ${Array.from({ length: 48 }, (_, i) => `<path d="M${-500 + i * 46},110 L${-462 + i * 44},330" stroke="#6f7b88" stroke-width="5" opacity=".7"/>`).join('')}
    ${[-160, 220, 600, 980].map(x => `<g transform="translate(${x},40)"><rect x="0" y="0" width="160" height="210" fill="#e7dcc5"/><path d="M-20,0 L80,-60 L180,0 Z" fill="#4f5a66"/><rect x="28" y="40" width="104" height="140" fill="url(#vitre)"/><path d="M80,40 v140" stroke="#efe5d2" stroke-width="6"/></g>`).join('')}
    <rect x="-500" y="320" width="2100" height="34" fill="#e3d3b2"/><rect x="-500" y="350" width="2100" height="10" fill="#000" opacity=".1"/>
    ${Array.from({ length: 10 }, (_, i) => `<g transform="translate(${-380 + i * 210},-120)"><rect width="70" height="150" fill="#c9765a"/><rect x="-6" y="-8" width="82" height="16" fill="#b2654b"/><rect x="10" y="-26" width="16" height="20" fill="#8a8f96"/><rect x="40" y="-30" width="16" height="24" fill="#8a8f96"/></g>`).join('')}`;
  const rue = `<rect x="-600" y="2680" width="2300" height="700" fill="#6b6f74"/><rect x="-600" y="2680" width="2300" height="40" fill="#a7a49c"/>
    <g transform="translate(650,2740)"><rect x="0" y="-150" width="360" height="150" rx="18" fill="#f4f4f2"/><path d="M360,-110 L440,-110 L470,-40 L470,0 L360,0 Z" fill="#eaeae7"/><path d="M372,-100 L432,-100 L456,-46 L372,-46 Z" fill="#6f8aa3"/>
      <rect x="40" y="-112" width="220" height="44" rx="6" fill="#d9b062"/><text x="150" y="-82" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="26" fill="#1a1206">RÉNOVATION</text>
      <circle cx="90" cy="2" r="34" fill="#1d1e21"/><circle cx="90" cy="2" r="14" fill="#9aa1a8"/><circle cx="390" cy="2" r="34" fill="#1d1e21"/><circle cx="390" cy="2" r="14" fill="#9aa1a8"/></g>
    <g transform="translate(-120,2700)"><rect x="-40" y="-260" width="18" height="260" fill="#3f4a3a"/><circle cx="-31" cy="-280" r="90" fill="#6c8f5c"/><circle cx="20" cy="-240" r="70" fill="#5f8251"/><circle cx="-80" cy="-230" r="64" fill="#7aa067"/></g>`;
  const collegue = `<g id="ext-collegue">
      <g transform="translate(0,-130)">
        <rect x="-70" y="-130" width="140" height="262" rx="22" fill="#0a0b0e"/><rect x="-62" y="-122" width="124" height="246" rx="17" fill="#f4efe6"/>
        <rect x="-62" y="-122" width="124" height="36" rx="12" fill="#1b2433"/><rect x="-52" y="-110" width="16" height="12" rx="3" fill="#d9b062"/><rect x="-30" y="-108" width="50" height="7" rx="3" fill="#fff" opacity=".8"/>
        <g transform="translate(-56,-80)">${illusSdb(112, 70, true, 1)}</g>
        <rect x="-52" y="0" width="80" height="9" rx="4" fill="#1b2433"/><rect x="-52" y="16" width="100" height="7" rx="3" fill="#9a9488"/><rect x="-52" y="30" width="90" height="7" rx="3" fill="#9a9488"/>
        <rect x="-52" y="54" width="104" height="30" rx="15" fill="url(#or)"/><text x="0" y="74" text-anchor="middle" font-family="Geist" font-weight="700" font-size="13" fill="#1a1206">Demander un devis</text>
        <g transform="translate(0,-138)"><path d="M-62,6 C-60,-40 60,-40 62,6 Z" fill="#f2c230"/><rect x="-76" y="2" width="152" height="14" rx="7" fill="#e3a91e"/><path d="M-8,-36 h16 v34 h-16 z" fill="#f7d35c"/><path d="M-40,-14 C-30,-30 -10,-34 0,-34" stroke="#fff" stroke-width="5" opacity=".5" fill="none" stroke-linecap="round"/></g>
      </g>
      <g id="ext-collegue-bulle" transform="translate(-40,-450)">${bulle('ext-cb', 'Demande reçue ✓', 330, 'd')}</g>
    </g>`;
  calque('ext', `<g id="ext-cam">
    <rect x="-1200" y="-1600" width="3500" height="1800" fill="url(#ciel)"/>
    <rect x="-1200" y="-1600" width="3500" height="1800" fill="url(#soleil)"/>
    <g id="ext-nuages" opacity=".9">${[[-300, -300, 1], [600, -520, 1.4], [1300, -200, .9]].map(([x, y, s]) => `<g transform="translate(${x},${y}) scale(${s})"><ellipse cx="0" cy="0" rx="160" ry="56" fill="#fff" opacity=".85"/><ellipse cx="90" cy="-30" rx="110" ry="60" fill="#fff" opacity=".9"/><ellipse cx="-80" cy="-14" rx="90" ry="44" fill="#fff" opacity=".8"/></g>`).join('')}</g>
    <g id="ext-fond">
      <g transform="translate(-410,360)">${fac}</g>
      ${toit}
      <path d="M150,980 C260,940 420,960 470,1010 L470,1078 L150,1078 Z" fill="#f6f2ea" opacity=".9"/>
      ${rue}
      <g transform="translate(0,1100)">${echafaudage(1080, 3, 720, { montants: [70, 1010] })}</g>
    </g>
    <g id="ext-fond-flou" filter="url(#flou6)" opacity="0">
      <g transform="translate(-410,360)">${fac}</g>${toit}
      <path d="M150,980 C260,940 420,960 470,1010 L470,1078 L150,1078 Z" fill="#f6f2ea" opacity=".9"/>
      <g transform="translate(0,1100)">${echafaudage(1080, 1, 720, { montants: [70, 1010] })}</g>
    </g>
    <g id="ext-perso">${personnage('ar', 'artisan')}</g>
    <g id="ext-gouttes">${Array.from({ length: 6 }, (_, i) => `<circle id="goutte${i}" r="${5 + (i % 3) * 2}" fill="#efebe2"/>`).join('')}</g>
    <g id="ext-plancheAvant"><rect x="-500" y="1078" width="2100" height="30" fill="url(#bois)"/><rect x="-500" y="1078" width="2100" height="7" fill="#e9c08a"/><rect x="-500" y="1108" width="2100" height="14" fill="#000" opacity=".2"/></g>
    <g id="ext-coll-pos" transform="translate(890,1078)">${collegue}</g>
    <rect x="-1200" y="-1600" width="3500" height="4500" fill="#ffc87a" opacity="0" id="ext-chaud" style="mix-blend-mode:soft-light"/>
  </g>`);
  $('ar-poche-tel').innerHTML = `<g id="ar-telPoche"><rect x="-22" y="-182" width="36" height="58" rx="7" fill="#15171c"/><rect x="-18" y="-178" width="28" height="30" rx="4" fill="#3b5c8f"/></g>
    <g id="ar-vib" stroke="#fff" stroke-width="5" stroke-linecap="round" fill="none"><path d="M-36,-182 c-10,8 -10,20 0,28"/><path d="M-48,-190 c-16,14 -16,34 0,46"/><path d="M28,-182 c10,8 10,20 0,28"/><path d="M40,-190 c16,14 16,34 0,46"/></g>`;
  $('ar-propsD').innerHTML = PROPS.tel('ar-telMain');
  $('ar-propsG').innerHTML = PROPS.taloche('ar-taloche');

  // ================= Scène 5 : le chantier qui n'avance pas =================
  const carreauxMur = Array.from({ length: 22 }, (_, r) => Array.from({ length: 9 }, (_, c) => `<rect x="${c * 70 + (r % 2) * 35 - 35}" y="${200 + r * 50}" width="66" height="46" rx="3" fill="#f3f6f7" stroke="#d6dfe2" stroke-width="2"/>`).join('')).join('');
  calque('chantier', `<g id="ch-cam">
    <rect x="-300" y="-200" width="1700" height="1540" fill="#c9bfae"/>
    <g clip-path="url(#clipMur)">${carreauxMur}</g>
    <clipPath id="clipMur"><path d="M-300,200 L600,200 L640,520 L560,800 L620,1100 L580,1340 L-300,1340 Z"/></clipPath>
    ${Array.from({ length: 8 }, (_, i) => `<path d="M${680 + rnd() * 500},${260 + i * 130} q60,-30 120,0" stroke="#b5aa97" stroke-width="5" fill="none" opacity=".7"/>`).join('')}
    <g transform="translate(720,190)"><rect width="330" height="430" fill="#f0e6d2"/><rect x="18" y="18" width="294" height="394" fill="#cfe4f2"/><rect x="18" y="18" width="294" height="394" fill="url(#soleil)"/><path d="M165,18 v394 M18,215 h294" stroke="#f0e6d2" stroke-width="12"/></g>
    <path d="M720,620 L1050,620 L1400,1340 L560,1340 Z" fill="#fff6dd" opacity=".35"/>
    <rect x="-300" y="1330" width="1700" height="700" fill="#8a6a49"/>
    ${Array.from({ length: 9 }, (_, i) => `<path d="M-300,${1340 + i * 70} H1400" stroke="#7a5b3d" stroke-width="3"/>`).join('')}
    <rect x="-300" y="1330" width="1700" height="18" fill="#000" opacity=".2"/>
    <g id="ch-horloge" transform="translate(230,360)"><circle r="104" fill="#2b2f36"/><circle r="92" fill="#f8f4ec"/>
      ${Array.from({ length: 12 }, (_, i) => `<rect x="-3" y="-84" width="6" height="${i % 3 ? 10 : 18}" fill="#2b2f36" transform="rotate(${i * 30})"/>`).join('')}
      <rect id="ch-h" x="-5" y="-50" width="10" height="56" rx="5" fill="#2b2f36"/><rect id="ch-m" x="-3.5" y="-76" width="7" height="82" rx="3.5" fill="#2b2f36"/><circle r="9" fill="#c9a24a"/></g>
    <g transform="translate(140,1300)"><rect width="190" height="120" fill="#c69c62"/><rect y="0" width="190" height="16" fill="#b1884f"/><text x="95" y="80" text-anchor="middle" font-family="Geist" font-weight="700" font-size="26" fill="#6b4c25">CARRELAGE</text></g>
    <g transform="translate(160,1170)"><rect width="150" height="130" fill="#d4ac70"/><rect y="0" width="150" height="14" fill="#b88f55"/></g>
    <g transform="translate(470,1420)"><path d="M-60,-80 L60,-80 L52,70 C50,82 -50,82 -52,70 Z" fill="#e9772e"/><ellipse cx="0" cy="-80" rx="60" ry="15" fill="#c95f1f"/></g>
    <g id="ch-artisan" transform="translate(0,0)">${personnage('ar2', 'artisan')}</g>
    <g id="ch-cliente">${personnage('cl', 'cliente')}</g>
    <rect x="-300" y="-200" width="1700" height="2300" fill="#3a5a8a" opacity=".08"/>
  </g>
  <g id="ch-carte" transform="translate(560,150)" filter="url(#ombreCarte)"><rect width="460" height="150" rx="26" fill="#fff"/>
    <text x="30" y="52" font-family="Geist" font-weight="700" font-size="28" fill="#1a1f29">Chantier salle de bain</text>
    <rect x="30" y="80" width="300" height="22" rx="11" fill="#e8e3da"/><rect id="ch-barre" x="30" y="80" width="120" height="22" rx="11" fill="#e9772e"/>
    <text x="350" y="99" font-family="Geist" font-weight="700" font-size="26" fill="#1a1f29">40 %</text>
    <g id="ch-pause"><rect x="300" y="18" width="134" height="40" rx="20" fill="#fde8e6"/><circle cx="324" cy="38" r="8" fill="#e5484d"/><text x="340" y="47" font-family="Geist" font-weight="700" font-size="22" fill="#c4363b">En pause</text></g>
  </g>`);
  $('ar2-propsD').innerHTML = PROPS.tel('ar2-tel');

  // ================= Scène 6 : le prospect appelle le suivant =================
  const carte = (y, nom, note, avis, site, mis) => `<g transform="translate(-255,${y})" id="${mis}">
    <rect width="510" height="236" rx="26" fill="#fff" stroke="#e7e2d8" stroke-width="2"/>
    <rect x="22" y="22" width="92" height="92" rx="18" fill="${site ? '#1b2433' : '#c9c3b8'}"/><text x="68" y="82" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="40" fill="${site ? '#d9b062' : '#fff'}">${nom[0]}</text>
    <text x="132" y="58" font-family="Geist" font-weight="700" font-size="30" fill="#1a1f29">${nom}</text>
    <text x="132" y="96" font-family="Geist" font-size="24" fill="#6b6457"><tspan fill="#e8a317">★★★★★</tspan> ${note} (${avis} avis)</text>
    <text x="132" y="128" font-family="Geist" font-size="22" fill="${site ? '#2f8f5b' : '#a39b8e'}">${site ? 'Site web · Devis en ligne' : 'Pas de site web'}</text>
    ${site ? `<g id="cc-btnSite"><rect x="22" y="160" width="146" height="54" rx="27" fill="#1b2433"/><text x="95" y="195" text-anchor="middle" font-family="Geist" font-weight="700" font-size="22" fill="#fff">Site web</text></g>
      <rect x="180" y="160" width="146" height="54" rx="27" fill="#eef2ee"/><text x="253" y="195" text-anchor="middle" font-family="Geist" font-weight="700" font-size="22" fill="#2f8f5b">Appeler</text>
      <rect x="338" y="160" width="152" height="54" rx="27" fill="url(#or)"/><text x="414" y="195" text-anchor="middle" font-family="Geist" font-weight="700" font-size="22" fill="#1a1206">Devis</text>`
    : `<rect x="22" y="160" width="146" height="54" rx="27" fill="#eef2ee"/><text x="95" y="195" text-anchor="middle" font-family="Geist" font-weight="700" font-size="22" fill="#2f8f5b">Appeler</text>
      <g id="cc-sansReponse"><rect x="180" y="160" width="230" height="54" rx="27" fill="#fde8e6"/><text x="295" y="195" text-anchor="middle" font-family="Geist" font-weight="700" font-size="22" fill="#c4363b">Pas de réponse</text></g>`}
  </g>`;
  const ecranListe = `<rect x="-280" y="-600" width="560" height="1200" fill="#f6f3ed"/>
    <text x="-240" y="-470" font-family="Geist" font-weight="700" font-size="22" fill="#1a1f29">9:41</text>
    <rect x="-255" y="-430" width="510" height="76" rx="38" fill="#fff" stroke="#e1dbd0" stroke-width="2"/>
    <circle cx="-210" cy="-392" r="14" fill="none" stroke="#6b6457" stroke-width="4"/><path d="M-200,-382 l12,12" stroke="#6b6457" stroke-width="4"/>
    <text x="-175" y="-383" font-family="Geist" font-size="25" fill="#1a1f29">rénovation salle de bain boulogne</text>
    <text x="-255" y="-300" font-family="Geist" font-weight="600" font-size="22" fill="#6b6457">Entreprises à proximité</text>
    ${carte(-270, 'Ton entreprise', '4,9', 12, false, 'cc-c1')}
    ${carte(-10, 'Habitat Rénov 92', '4,7', 58, true, 'cc-c2')}
    <g opacity=".5">${carte(250, 'Atelier Martin', '4,6', 31, true, 'cc-c3')}</g>`;
  const ecranAppelSortant = `<g id="cc-appel"><rect x="-280" y="-600" width="560" height="1200" fill="url(#ecranAppel)"/>
    <circle cx="0" cy="-260" r="86" fill="#55657e"/><text x="0" y="-232" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="80" fill="#fff">T</text>
    <text x="0" y="-110" text-anchor="middle" font-family="Geist" font-weight="600" font-size="46" fill="#fff">Ton entreprise</text>
    <text id="cc-etat" x="0" y="-55" text-anchor="middle" font-family="Geist" font-size="30" fill="#b9c3d3">Appel en cours… 0:12</text>
    <g transform="translate(0,380)"><circle r="56" fill="#e5484d"/><path d="M-26,2 c18,-18 34,-18 52,0" stroke="#fff" stroke-width="10" fill="none" stroke-linecap="round"/></g></g>`;
  calque('concurrent', `<g id="cc-cam">
    <g filter="url(#flou12)"><rect x="-200" y="-200" width="1500" height="2400" fill="#e9dccb"/><rect x="-200" y="1250" width="1500" height="900" fill="#b48a62"/>
      <rect x="-100" y="900" width="900" height="420" rx="60" fill="#4e6b6b"/><rect x="-60" y="820" width="820" height="200" rx="60" fill="#5d7c7c"/>
      <circle cx="950" cy="600" r="160" fill="#ffd9a0" opacity=".6"/><rect x="900" y="640" width="20" height="700" fill="#3a3a3a"/>
      <rect x="120" y="180" width="380" height="460" fill="#cfb994"/><rect x="140" y="200" width="340" height="420" fill="#8fa8b8"/></g>
    <g id="cc-tel" transform="translate(540,820)">
      <g filter="url(#ombreDouce)">${telephone('tel2', 560, 1200, `<g id="cc-liste">${ecranListe}</g>${ecranAppelSortant}`)}</g>
      <g id="cc-main">
        ${[0, 1, 2].map(i => `<path d="M${-282 - i * 4},${300 + i * 120} c-34,0 -44,70 -10,80 l14,0 c6,-30 6,-60 -4,-80 z" fill="#d3976f"/><path d="M${-286 - i * 4},${312 + i * 120} c-14,6 -16,40 -2,52" stroke="#b57c57" stroke-width="3" fill="none"/>`).join('')}
        <path d="M282,640 C300,560 330,470 360,430 C392,390 450,420 440,480 C430,540 400,640 390,760 L300,820 Z" fill="#d3976f"/>
        <g id="cc-pouce"><path d="M0,0 C26,-26 70,-34 86,-12 C98,6 80,30 46,42 C10,56 -60,100 -130,170 C-160,150 -190,110 -180,80 C-110,40 -40,30 0,0 Z" fill="#d79c77"/>
          <path d="M44,-18 C62,-24 80,-18 82,-6 C84,6 70,14 54,12 C40,10 32,-8 44,-18 Z" fill="#f6d9c6"/><path d="M-30,40 C-10,30 10,26 20,24" stroke="#b57c57" stroke-width="3" fill="none" opacity=".7"/></g>
        <circle id="cc-tap" r="20" fill="#1b2433" opacity="0"/></g>
    </g>
  </g>`);

  // ================= Scènes 7 et 8 : le soir, puis le déclic =================
  calque('soir', `<g id="so-cam">
    <rect x="-400" y="-300" width="1900" height="1700" fill="url(#nuit)"/>
    <g transform="translate(600,200)"><rect width="420" height="600" fill="#1a2440"/><rect x="18" y="18" width="384" height="564" fill="#0c1330"/>
      <circle cx="300" cy="120" r="46" fill="#f4ecd2"/><circle cx="286" cy="110" r="46" fill="#0c1330"/>
      ${Array.from({ length: 24 }, () => `<circle cx="${20 + rnd() * 380}" cy="${20 + rnd() * 220}" r="${1 + rnd() * 1.6}" fill="#fff" opacity="${.4 + rnd() * .5}"/>`).join('')}
      <path d="M18,420 L70,380 L70,340 L120,340 L120,390 L170,360 L230,400 L230,330 L290,330 L290,380 L350,350 L402,390 L402,582 L18,582 Z" fill="#232c47"/>
      ${Array.from({ length: 10 }, () => `<rect x="${40 + rnd() * 320}" y="${430 + rnd() * 120}" width="14" height="18" fill="#ffcf7a" opacity="${.5 + rnd() * .5}"/>`).join('')}
      <path d="M210,18 v564 M18,300 h384" stroke="#1a2440" stroke-width="14"/></g>
    <g transform="translate(70,470)"><rect width="300" height="150" rx="10" fill="#141a2a"/><rect x="40" y="40" width="220" height="70" rx="8" fill="#060912"/>
      <text id="so-heure" x="150" y="98" text-anchor="middle" font-family="Geist" font-weight="700" font-size="54" fill="#ff9a3c">22:47</text></g>
    <path d="M420,-300 L420,300" stroke="#0a0e18" stroke-width="5"/>
    <path id="so-cone" d="M330,330 L510,330 L900,1260 L-60,1260 Z" fill="url(#lampe)"/>
    <path d="M330,330 C340,270 500,270 510,330 Z" fill="#c9a24a"/><ellipse cx="420" cy="330" rx="90" ry="12" fill="#fff1c9"/>
    <g id="so-perso">${personnage('ar3', 'artisan')}</g>
    <g id="so-fantomes" opacity="0">${[['Boulogne ?', 120, 520], ['Prix ?', 640, 640], ['Photos ?', 150, 820]].map(([q, x, y], i) => `<g transform="translate(${x},${y}) scale(.8)" id="sf${i}">${bulle('sfb' + i, q, q.length * 19 + 70, i % 2 ? 'd' : 'g')}</g>`).join('')}</g>
    <rect x="-400" y="1180" width="1900" height="60" fill="#5a3f2a"/><rect x="-400" y="1180" width="1900" height="14" fill="#8a6443"/>
    <rect x="-400" y="1240" width="1900" height="900" fill="#20283b"/>
    <ellipse cx="420" cy="1186" rx="420" ry="50" fill="#ffcf8a" opacity=".28"/>
    <g transform="translate(560,1176)"><path d="M-110,0 L110,0 L120,-14 L-100,-14 Z" fill="#f4f1ea"/><path d="M-100,-14 L120,-14 L126,-26 L-94,-26 Z" fill="#fbf9f4"/><text x="0" y="-32" text-anchor="middle" font-family="Geist" font-weight="700" font-size="18" fill="#1b2433">DEVIS</text></g>
    <g transform="translate(160,1178)"><rect x="-60" y="-16" width="120" height="16" rx="3" fill="#3a3f48"/><rect x="-48" y="-46" width="96" height="34" rx="4" fill="#2a2f38" transform="skewX(-10)"/></g>
    <g transform="translate(800,1180)">${PROPS.tasse('so-tasse')}</g>
    <rect id="so-halo" x="-400" y="-300" width="1900" height="1700" fill="url(#halo)" opacity="0"/>
  </g>`);
  $('ar3-propsD').innerHTML = PROPS.tel('ar3-tel');
  $('ar3-propsG').innerHTML = PROPS.stylo('ar3-stylo');
  tr('ar3-tasse', 'translate(0,-40)');

  // ================= Scène 9 : le site internet =================
  const contenuSite = `
    <rect x="-280" y="-620" width="560" height="2600" fill="#fbf9f5"/>
    <text x="-238" y="-528" font-family="Geist" font-weight="700" font-size="22" fill="#1a1f29">9:41</text>
    <rect x="-240" y="-500" width="480" height="58" rx="29" fill="#efebe3"/><text x="0" y="-462" text-anchor="middle" font-family="Geist" font-size="23" fill="#4b463e">ton-entreprise.fr</text><g transform="translate(-118,-478)"><rect x="-9" y="-2" width="18" height="14" rx="3" fill="#4b463e"/><path d="M-5,-2 v-5 a5,5 0 0 1 10,0 v5" stroke="#4b463e" stroke-width="3" fill="none"/></g>
    <rect x="-280" y="-430" width="560" height="84" fill="#1b2433"/>
    <rect x="-246" y="-412" width="48" height="48" rx="12" fill="url(#or)"/><text x="-222" y="-378" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="24" fill="#1b2433">TE</text>
    <text x="-186" y="-394" font-family="Bricolage" font-weight="800" font-size="25" fill="#fff">Ton Entreprise</text><text x="-186" y="-368" font-family="Geist" font-size="18" fill="#d9b062">Rénovation · Boulogne</text>
    <path d="M200,-398 h34 M200,-388 h34 M200,-378 h34" stroke="#fff" stroke-width="4"/>
    <g transform="translate(-280,-346)">${illusSdb(560, 330, true, 0)}</g>
    <text x="-240" y="30" font-family="Bricolage" font-weight="800" font-size="38" fill="#1b2433">Rénovation de salles</text>
    <text x="-240" y="74" font-family="Bricolage" font-weight="800" font-size="38" fill="#1b2433">de bain à Boulogne</text>
    <text x="-240" y="118" font-family="Bricolage" font-weight="800" font-size="38" fill="#b0812e">et dans le 92</text>
    <rect x="-240" y="146" width="290" height="66" rx="33" fill="url(#or)"/><text x="-95" y="188" text-anchor="middle" font-family="Geist" font-weight="700" font-size="25" fill="#1a1206">Demander un devis</text>
    <g id="si-zone" transform="translate(0,270)">
      <text x="-240" y="0" font-family="Bricolage" font-weight="800" font-size="32" fill="#1b2433">Zone d’intervention</text>
      <rect x="-240" y="24" width="480" height="300" rx="24" fill="#e7efe9"/>
      <path d="M-170,90 C-120,50 -40,60 10,40 C80,20 150,60 190,120 C220,180 190,260 120,290 C40,320 -80,300 -150,250 C-210,200 -220,130 -170,90 Z" fill="#cfe0d4" stroke="#9cc0a6" stroke-width="3"/>
      <circle cx="0" cy="170" r="110" fill="#d9b062" opacity=".18" stroke="#d9b062" stroke-width="4" stroke-dasharray="10 8"/>
      ${[['Boulogne', 0, 170], ['Issy', 80, 140], ['Clamart', 60, 250], ['Sèvres', -90, 200], ['Paris 16e', -40, 80]].map(([n, x, y]) => `<g transform="translate(${x},${y})"><path d="M0,0 c-14,-20 -14,-38 0,-38 c14,0 14,18 0,38 z" fill="#1b2433"/><circle cy="-26" r="6" fill="#d9b062"/><text x="0" y="22" text-anchor="middle" font-family="Geist" font-weight="700" font-size="18" fill="#1b2433">${n}</text></g>`).join('')}
    </g>
    <g id="si-galerie" transform="translate(0,660)">
      <text x="-240" y="0" font-family="Bricolage" font-weight="800" font-size="32" fill="#1b2433">Nos chantiers</text>
      ${[[0, 0, false], [1, 0, true], [0, 1, true], [1, 1, true]].map(([c, r, fini], i) => `<g transform="translate(${-240 + c * 246},${26 + r * 196})"><clipPath id="cg${i}"><rect width="234" height="184" rx="18"/></clipPath><g clip-path="url(#cg${i})">${illusSdb(234, 184, fini, i)}</g>
        <rect x="10" y="10" width="${fini ? 86 : 80}" height="32" rx="16" fill="${fini ? '#1b2433' : '#fff'}"/><text x="${fini ? 53 : 50}" y="32" text-anchor="middle" font-family="Geist" font-weight="700" font-size="17" fill="${fini ? '#d9b062' : '#1b2433'}">${fini ? 'Après' : 'Avant'}</text></g>`).join('')}
    </g>
    <g id="si-delais" transform="translate(0,1110)">
      <text x="-240" y="0" font-family="Bricolage" font-weight="800" font-size="32" fill="#1b2433">Délais</text>
      <g transform="translate(-240,26)"><rect width="232" height="150" rx="22" fill="#1b2433"/><text x="20" y="60" font-family="Bricolage" font-weight="800" font-size="40" fill="#d9b062">48 h</text><text x="20" y="100" font-family="Geist" font-size="21" fill="#fff">pour recevoir</text><text x="20" y="128" font-family="Geist" font-size="21" fill="#fff">votre devis</text></g>
      <g transform="translate(8,26)"><rect width="232" height="150" rx="22" fill="#efe7d6"/><text x="20" y="60" font-family="Bricolage" font-weight="800" font-size="40" fill="#1b2433">2 à 3 sem.</text><text x="20" y="100" font-family="Geist" font-size="21" fill="#4b463e">de travaux pour</text><text x="20" y="128" font-family="Geist" font-size="21" fill="#4b463e">une salle de bain</text></g>
    </g>
    <g id="si-form" transform="translate(0,1340)">
      <text x="-240" y="0" font-family="Bricolage" font-weight="800" font-size="32" fill="#1b2433">Demande de devis</text>
      ${[['Type de travaux', 'Salle de bain'], ['Ville', 'Boulogne-Billancourt'], ['Délai souhaité', 'Janvier']].map(([l, v], i) => `<g transform="translate(-240,${30 + i * 104})"><text y="22" font-family="Geist" font-weight="600" font-size="19" fill="#6b6457">${l}</text><rect y="32" width="480" height="58" rx="14" fill="#fff" stroke="#e1dbd0" stroke-width="2"/><text id="si-v${i}" x="18" y="70" font-family="Geist" font-size="23" fill="#1a1f29">${v}</text></g>`).join('')}
      <g transform="translate(-240,346)"><text y="22" font-family="Geist" font-weight="600" font-size="19" fill="#6b6457">Photos du chantier</text>
        ${[0, 1, 2, 3].map(i => `<g id="si-ph${i}" transform="translate(${i * 122},34)"><clipPath id="cp${i}"><rect width="110" height="84" rx="12"/></clipPath><g clip-path="url(#cp${i})">${illusSdb(110, 84, false, i)}</g></g>`).join('')}</g>
      <g id="si-envoyer" transform="translate(-240,480)"><rect width="480" height="72" rx="36" fill="#1b2433"/><text x="240" y="46" text-anchor="middle" font-family="Geist" font-weight="700" font-size="26" fill="#fff">Envoyer ma demande</text></g>
    </g>`;
  const pastille = (id, icone, texte, larg) => `<g id="${id}"><g id="${id}-in" filter="url(#ombreCarte)"><rect x="0" y="-38" width="${larg}" height="76" rx="38" fill="#1b2433"/><circle cx="38" cy="0" r="26" fill="url(#or)"/>${icone}<text x="78" y="10" font-family="Geist" font-weight="700" font-size="29" fill="#fff">${texte}</text></g></g>`;
  calque('site', `<rect width="1080" height="1920" fill="#f1ebe0"/><rect width="1080" height="1920" fill="url(#soleil)" opacity=".6"/>
    <g opacity=".5">${Array.from({ length: 14 }, (_, i) => `<path d="M${i * 90 - 200},0 L${i * 90 + 300},1920" stroke="#e4dccd" stroke-width="2"/>`).join('')}</g>
    <g id="si-cam"><g id="si-tel" transform="translate(540,700)"><g filter="url(#ombreDouce)">${telephone('tel3', 560, 1140, `<g id="si-contenu">${contenuSite}</g>`)}</g></g></g>
    ${pastille('pa0', '<path d="M38,-14 c-11,0 -16,8 -16,14 c0,10 16,24 16,24 c0,0 16,-14 16,-24 c0,-6 -5,-14 -16,-14 z" fill="#1b2433"/><circle cx="38" cy="-1" r="5" fill="#d9b062"/>', 'Ta zone', 230)}
    ${pastille('pa1', '<rect x="24" y="-12" width="28" height="22" rx="4" fill="#1b2433"/><circle cx="38" cy="-1" r="6" fill="#d9b062"/>', 'Tes chantiers', 300)}
    ${pastille('pa2', '<circle cx="38" cy="0" r="13" fill="none" stroke="#1b2433" stroke-width="4"/><path d="M38,-7 v8 l6,4" stroke="#1b2433" stroke-width="4" fill="none"/>', 'Tes délais', 250)}
    ${pastille('pa3', '<rect x="27" y="-14" width="22" height="28" rx="3" fill="#1b2433"/><path d="M31,-6 h14 M31,1 h14 M31,8 h9" stroke="#d9b062" stroke-width="3"/>', 'Ton devis en ligne', 380)}
    <g id="si-check" transform="translate(800,170)"><circle r="70" fill="#2f8f5b"/><path d="M-30,2 l20,22 l40,-46" stroke="#fff" stroke-width="14" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g>`);

  // ================= Fin : carton final =================
  calque('finale', `<rect width="1080" height="1920" fill="#0b0b0d"/>
    <circle cx="540" cy="820" r="700" fill="url(#halo)" opacity=".12"/>
    <g id="fi-l1"><text x="540" y="760" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="96" fill="#fff">Tu te reconnais ?</text></g>
    <g id="fi-l2"><text x="292" y="902" text-anchor="end" font-family="Bricolage" font-weight="700" font-size="58" fill="#cfc8bb">Écris</text><text x="508" y="902" font-family="Bricolage" font-weight="700" font-size="58" fill="#cfc8bb">en commentaire</text></g>
    <g id="fi-pill"><rect x="314" y="844" width="172" height="80" rx="22" fill="url(#or)"/><text x="400" y="902" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="56" fill="#1a1206">SITE</text></g>
    <g id="fi-bulle" transform="translate(540,1080)"><path d="M-70,-50 h140 a30,30 0 0 1 30,30 v60 a30,30 0 0 1 -30,30 h-80 l-40,34 v-34 h-20 a30,30 0 0 1 -30,-30 v-60 a30,30 0 0 1 30,-30 z" fill="none" stroke="#d9b062" stroke-width="8" stroke-linejoin="round"/><circle cx="-40" cy="10" r="9" fill="#d9b062"/><circle cx="0" cy="10" r="9" fill="#d9b062"/><circle cx="40" cy="10" r="9" fill="#d9b062"/></g>
    <g id="fi-logo"><image href="logo-aurea-media-clair.png" x="390" y="1500" width="300" height="105"/><text x="540" y="1660" text-anchor="middle" font-family="Geist" font-size="30" fill="#8a8478">aurea-media.fr</text></g>`);

  // ================= Calque d'interface (bulles de questions, notification) =================
  const qs = ['Vous allez jusqu’à Boulogne ?', 'Vous faites les salles de bain ?', 'C’est combien, à peu près ?', 'Vous avez des photos ?', 'Vous êtes dispo quand ?'];
  const pos = [[40, 120, 'g'], [420, 262, 'd'], [40, 404, 'g'], [440, 960, 'd'], [50, 1090, 'g']];
  calque('ui', qs.map((q, i) => { const w = q.length * 21 + 80; const [x, y, c] = pos[i]; return `<g id="bq${i}" transform="translate(${Math.min(x, 1050 - w)},${y})"><g id="bq${i}-in">${bulleGrande('bq' + i + 'b', q, w, c)}</g></g>`; }).join('') + `
    <g id="notif" transform="translate(50,90)"><g filter="url(#ombreCarte)"><rect width="980" height="180" rx="40" fill="#fbfaf7" opacity=".97"/>
      <rect x="30" y="30" width="78" height="78" rx="20" fill="#1b2433"/><text x="69" y="83" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="34" fill="#d9b062">TE</text>
      <text x="130" y="58" font-family="Geist" font-weight="600" font-size="24" fill="#8a8478">ton-entreprise.fr · maintenant</text>
      <text x="130" y="100" font-family="Geist" font-weight="700" font-size="34" fill="#1a1f29">Nouvelle demande de devis</text>
      <text x="130" y="146" font-family="Geist" font-size="28" fill="#4b463e">Salle de bain · Boulogne · 4 photos jointes</text></g></g>`);
  for (const id of ['hook', 'ext', 'chantier', 'concurrent', 'soir', 'site', 'finale', 'ui']) $('svg').appendChild(L[id]);
}

// Bulle plus grande (lisible en plein écran)
function bulleGrande(id, texte, larg, cote = 'g') {
  const h = 104;
  const queue = cote === 'g' ? `M34,${h - 8} l-22,30 l50,-24 z` : `M${larg - 34},${h - 8} l22,30 l-50,-24 z`;
  return `<g id="${id}" filter="url(#ombreCarte)"><path d="${queue}" fill="#fff"/><rect width="${larg}" height="${h}" rx="36" fill="#fff"/>
    <text x="${larg / 2}" y="${h / 2 + 14}" text-anchor="middle" font-family="Geist" font-weight="600" font-size="40" fill="#1a1f29">${texte}</text></g>`;
}

// ---------------------------------------------------------------------------------------------
function fenetre(t, a, b) { return t >= a && t < b; }

function rendre(t) {
  for (const id in L) vis(L[id], false);
  vis('ui', true);
  $('flash').style.opacity = 0; $('fondu').style.opacity = 0;
  for (const id of ['hook', 'ext', 'chantier', 'concurrent', 'soir', 'site']) { L[id].removeAttribute('filter'); L[id].removeAttribute('clip-path'); tr(L[id], ''); }
  const tWhip = P('v2') - .14;
  const tCh = P('v5') - .12, tCc = P('v6') - .12, tSo = P('v7') - .18, tSi = P('v9') - .3, tAp = P('v10') - .35, tFin = CAL.finTexte;

  // ----- Scène 1 -----
  if (t < tWhip + .3) { vis(L.hook, true); renduHook(t, tWhip); }
  // ----- Extérieur -----
  if (fenetre(t, tWhip, tCh + .36) || fenetre(t, tAp, tFin + .4)) { vis(L.ext, true); renduExt(t, tWhip); }
  const wf = Math.sin(Math.PI * clamp((t - tWhip) / .42));
  $('flouVdev').setAttribute('stdDeviation', `0 ${wf * 40}`);
  if (fenetre(t, tWhip, tWhip + .42)) L.ext.setAttribute('filter', 'url(#flouV)');
  $('flash').style.opacity = Math.max(0, .45 * (1 - Math.abs(t - (tWhip + .2)) / .12));

  // ----- Chantier (ouverture en iris depuis la bulle) -----
  if (fenetre(t, tCh, tCc + .45)) {
    vis(L.chantier, true); renduChantier(t, tCh);
    const r = prog(t, tCh, tCh + .4, E.in) * 1500;
    if (r < 1499) { attr('irisC', 'r', r); L.chantier.setAttribute('clip-path', 'url(#clipIris)'); }
  }
  // ----- Concurrent (poussé depuis le bas) -----
  if (fenetre(t, tCc, tSo + .1)) {
    vis(L.concurrent, true); renduConcurrent(t, tCc);
    const k = prog(t, tCc, tCc + .42, E.expoIo);
    tr(L.concurrent, `translate(0,${(1 - k) * 1920})`);
    if (t < tCc + .42) tr(L.chantier, `translate(0,${-k * 1920})`);
  }
  // ----- Soir et déclic -----
  if (fenetre(t, tSo, tSi + .32)) { vis(L.soir, true); renduSoir(t, tSo, tSi); }
  $('fondu').style.opacity = Math.max(0, 1 - Math.abs(t - (tSo + .02)) / .2);
  // ----- Site -----
  if (fenetre(t, tSi + .15, tAp + .4)) { vis(L.site, true); renduSite(t, tSi + .15, tAp); }
  $('flash').style.opacity = Math.max(+$('flash').style.opacity, .9 * (1 - Math.abs(t - (tSi + .2)) / .14));
  if (t >= tAp && t < tAp + .4) { const k = prog(t, tAp, tAp + .4, E.io); tr(L.site, `translate(540,960) scale(${1 + k * .25}) translate(-540,-960)`); op(L.site, 1 - k); } else op(L.site, 1);
  // ----- Fin -----
  if (t >= tFin) { vis(L.finale, true); renduFin(t, tFin); }
  $('fondu').style.opacity = Math.max(+$('fondu').style.opacity, 1 - Math.abs(t - tFin) / .25, prog(t, CAL.duree - .35, CAL.duree, E.lin));

  renduUI(t);
  rendreSousTitres(t);
  $('st').style.opacity = t >= tFin - .05 ? 0 : 1;
  grain(t);
}

// ---------------- Scène 1 ----------------
function renduHook(t, tWhip) {
  const u = t;
  const sonne = 1;
  const vibre = Math.sin(u * 95) * 2.4 + Math.sin(u * 61) * 1.6;
  const salve = (u % .9) < .55 ? 1 : .15;
  const tension = 1 + prog(u, 1.6, tWhip, E.in) * 1.6;
  tr('hook-tel', `translate(${540 + vibre * salve * 2 * tension},${820 + vibre * salve * tension}) rotate(${-7 + vibre * salve * .6 * tension})`);
  for (let i = 0; i < 3; i++) {
    const k = ((u * .9 + i / 3) % 1);
    tr('onde' + i, `translate(540,820) rotate(-7) scale(${1 + k * .55})`);
    op('onde' + i, sonne * (1 - k) * .5);
  }
  // compteur : de 9 à 14, calé sur le mot « 14 »
  const t14 = M('v1', 4);
  const pas = (t14 - .35) / 5;
  const n = Math.min(14, 9 + Math.max(0, Math.floor((u - .35) / pas) + 1));
  $('hook-nb').textContent = u < .35 ? 9 : n;
  const dernier = .35 + (n - 10) * pas;
  const pop = u >= .35 ? 1 + (n === 14 ? .55 : .3) * Math.exp(-(u - dernier) * 8) * Math.cos((u - dernier) * 18) : 1;
  tr('hook-badge', `translate(178,-378) scale(${piste(u, [[0, 0], [.22, 1.15, E.backS], [.4, 1]]) * pop})`);
  const z = piste(u, [[0, 1.12], [t14, 1.22, E.sine], [t14 + .25, 1.34, E.backS], [tWhip, 1.42, E.sine]]);
  const w = prog(t, tWhip, tWhip + .3, E.in);
  camera('hook-cam', 540 + bruit(u, 1) * 4, 820 + bruit(u, 2) * 4 - w * 1500, z, bruit(u, 3) * .5);
  if (w > 0) L.hook.setAttribute('filter', 'url(#flouV)');
}

// ---------------- Extérieur ----------------
function renduExt(t, tWhip) {
  const v2 = P('v2'), v3 = P('v3'), v4 = P('v4'), v10 = P('v10'), v11 = P('v11');
  const deuxieme = t >= P('v10') - .4;
  let cam;
  if (!deuxieme) cam = piste(t, [
    [tWhip, { x: 600, y: 520, z: 2.3 }],
    [v2 + .3, { x: 600, y: 540, z: 2.2 }],
    [M('v2', 4) + .1, { x: 560, y: 1200, z: .6 }, E.expoIo],
    [v3 - .2, { x: 570, y: 1040, z: .74 }, E.sine],
    [v3 + .35, { x: 610, y: 600, z: 1.75 }, E.expoIo],
    [v4, { x: 600, y: 560, z: 1.95 }, E.sine],
    [P('v5'), { x: 585, y: 510, z: 2.5 }, E.sine],
  ]);
  else cam = piste(t, [
    [v10 - .4, { x: 620, y: 640, z: 1.9 }],
    [v10 + .6, { x: 620, y: 660, z: 1.6 }, E.sine],
    [v11 - .1, { x: 640, y: 760, z: 1.35 }, E.sine],
    [M('v11', 5) - .1, { x: 720, y: 820, z: 1.05 }, E.expoIo],
    [CAL.finTexte + .4, { x: 720, y: 840, z: .98 }, E.sine],
  ]);
  const entree = deuxieme ? 0 : 1 - prog(t, tWhip, tWhip + .3, E.out);
  camera('ext-cam', cam.x + bruit(t, 4) * 3, cam.y - entree * 900 + bruit(t, 5) * 3, cam.z, bruit(t, 6) * .3);
  op('ext-fond-flou', clamp((cam.z - 1.1) / .6));
  tr('ext-nuages', `translate(${t * 12},0)`);
  op('ext-chaud', deuxieme ? .35 : 0);

  // ---- Jeu de l'artisan ----
  const pose = { x: 600, y: 1080, s: 1, resp: Math.sin(t * 2.1), cligne: clignement(t, 2), enduit: 1, tourne: -.35 };
  if (!deuxieme) {
    const lisse = 1 - prog(t, M('v2', 4), M('v2', 4) + .35);
    const sortTel = prog(t, v3 - .05, v3 + .45, E.io);
    const versOreille = prog(t, v3 + .5, v3 + 1.1, E.io);
    const k = Math.sin(t * 5.2);
    Object.assign(pose, ikBras('G', mix(-150, -215 + k * 22, lisse), mix(-40, -260 + Math.sin(t * 2.6) * 70, lisse), -1, mix(10, 115, lisse)));
    const mainD = versOreille > 0 ? { x: mix(-6, 50, versOreille), y: mix(-150, -292, versOreille), a: mix(170, 200, versOreille) } : { x: mix(130, -6, sortTel), y: mix(-30, -150, sortTel), a: mix(0, 170, sortTel) };
    Object.assign(pose, ikBras('D', mainD.x, mainD.y, 1, mainD.a));
    const enMain = versOreille > 0 || sortTel > .95;
    op('ar-telMain', enMain ? 1 : 0); vis('ar-telPoche', !enMain);
    op('ar-vib', !enMain && (t % .5) < .32 ? 1 : 0);
    tr('ar-telPoche', `translate(${!enMain ? Math.sin(t * 90) * 2 : 0},0)`);
    const regarde = prog(t, M('v2', 4) + .15, M('v2', 4) + .45) * (1 - versOreille);
    pose.tete = mix(-4, 10, regarde) + mix(0, 8, versOreille);
    pose.regardX = mix(-1, .2, regarde); pose.regardY = mix(0, 1, regarde);
    pose.tourne = mix(-.35, 0, Math.max(regarde, versOreille));
    pose.sourcils = regarde * .6; pose.pench = mix(-3, 2, regarde);
    pose.bouche = regarde > .5 && t < v3 ? 'o' : 'neutre';
    const stress = prog(t, v4 + .2, P('v5') - .3, E.lin);
    if (versOreille > .6) {
      pose.bouche = t < v4 ? (t < v3 + 1.7 ? (parole(t, true) || 'parle') : 'sourire') : (parole(t, Math.sin(t * 3.1) > 0) || 'grimace');
      pose.sourcils = mix(.4, 1, stress); pose.froncer = mix(.2, 1, stress); pose.sueur = stress;
      pose.regardX = t > v4 ? Math.sin(t * 4.3) * (.6 + stress * .4) : .3; pose.regardY = t > v4 ? -.4 : 0;
      pose.tete = 8 + Math.sin(t * 2.2) * 3 * stress;
    }
    // gouttes d'enduit qui tombent de la main pendant qu'il décroche
    for (let i = 0; i < 6; i++) {
      const t0 = v3 + .2 + i * .22, k2 = clamp((t - t0) / .6);
      op('goutte' + i, k2 > 0 && k2 < 1 ? 1 - k2 * .3 : 0);
      tr('goutte' + i, `translate(${680 + i * 9 - 40},${780 + i * 10 + k2 * k2 * 320})`);
    }
    vis('ext-coll-pos', false); vis('ext-collegue-bulle', false);
  } else {
    // Après : il travaille tranquillement, le téléphone vibre une fois, il sourit
    const k = Math.sin(t * 3.6);
    Object.assign(pose, ikBras('G', -215 + k * 18, -250 + Math.sin(t * 1.8) * 60, -1, 115));
    const glance = prog(t, v10 + .45, v10 + .75) * (1 - prog(t, v10 + 1.9, v10 + 2.3));
    Object.assign(pose, ikBras('D', 120, -30, 1, 0));
    op('ar-telMain', 0); vis('ar-telPoche', true);
    const buzz = fenetre(t, v10 + .25, v10 + .85);
    op('ar-vib', buzz && (t % .3) < .18 ? 1 : 0);
    tr('ar-telPoche', `translate(${buzz ? Math.sin(t * 90) * 2 : 0},0)`);
    pose.tete = mix(-4, 9, glance); pose.regardY = mix(0, 1, glance); pose.regardX = mix(-1, .1, glance);
    pose.tourne = mix(-.35, 0, glance);
    pose.bouche = t > v10 + .9 ? 'sourire' : 'neutre';
    pose.sourcils = t > v10 + .9 ? .5 : 0;
    // chute : il se tourne vers son « collègue »
    const vers = prog(t, M('v11', 5) - .1, M('v11', 5) + .4);
    if (vers > 0) { pose.tourne = mix(pose.tourne, .6, vers); pose.regardX = mix(pose.regardX, 1, vers); pose.regardY = 0; pose.tete = mix(pose.tete, 4, vers); pose.bouche = 'sourire'; }
    for (let i = 0; i < 6; i++) op('goutte' + i, 0);
    const app = prog(t, M('v11', 5) - .25, M('v11', 5) + .25, E.backS);
    vis('ext-coll-pos', app > 0);
    tr('ext-collegue', `translate(0,${(1 - app) * 60}) scale(${app})`);
    const bl = prog(t, M('v11', 7), M('v11', 7) + .3, E.backS);
    vis('ext-collegue-bulle', bl > 0);
    tr('ext-collegue-bulle', `translate(-40,-450) scale(${bl})`);
  }
  poser('ar', pose);
}

// ---------------- Scène 5 : le chantier ----------------
function renduChantier(t, t0) {
  const u = t - t0;
  const cam = piste(u, [[0, { x: 300, y: 370, z: 2.4 }], [.35, { x: 300, y: 380, z: 2.25 }], [1.25, { x: 560, y: 980, z: 1.02 }, E.expoIo], [3.2, { x: 590, y: 1000, z: 1.1 }, E.sine]]);
  camera('ch-cam', cam.x + bruit(t, 7) * 3, cam.y + bruit(t, 8) * 3, cam.z);
  const vit = u * 4.5;  // les aiguilles tournent vite : le temps passe
  tr('ch-m', `rotate(${vit * 360})`); tr('ch-h', `rotate(${vit * 30 + 40})`);
  op('ch-pause', (u % .8) < .55 ? 1 : .35);
  tr('ch-carte', `translate(560,${mix(-200, 150, prog(u, .9, 1.3, E.backS))})`);
  // l'artisan, au fond, toujours au téléphone
  poser('ar2', Object.assign({ x: 310, y: 1380, s: .66, ombre: .15, cligne: clignement(t, 4), tete: 6, tourne: .3, regardX: .4, bouche: parole(t, Math.sin(t * 2.7) > -.3) || 'neutre', enduit: 1, sueur: .6, sourcils: .6, froncer: .8 },
    ikBras('D', 50, -292, 1, 200), ikBras('G', -60, -150, -1, 40)));
  // la cliente, au premier plan : bras croisés, tapote du pied, regarde l'heure
  const tape = Math.max(0, Math.sin(u * 13)) * 9;
  const soupir = prog(u, 1.6, 2.0) * (1 - prog(u, 2.4, 2.8));
  poser('cl', Object.assign({ x: 800, y: 1790, s: 1.08, cligne: clignement(t, 7), tourne: -.5, regardX: mix(-1, -.6, soupir), regardY: mix(-.6, .3, soupir), tete: mix(-6, 4, soupir), sourcils: -.4, froncer: -1, bouche: soupir > .4 ? 'o' : 'neutre', pD: -tape, cD: -3 - tape * .2, resp: Math.sin(t * 2.5) + soupir * 2 },
    ikBras('G', 30, -120, 1, -90), ikBras('D', -30, -128, -1, 90)));
}

// ---------------- Scène 6 : le prospect passe au suivant ----------------
function renduConcurrent(t, t0) {
  const u = t - t0;
  const tRate = M('v6', 4), tSuiv = M('v6', 8);
  camera('cc-cam', 540 + bruit(t, 9) * 4, 960 + bruit(t, 10) * 4, piste(u, [[0, 1.05], [3.4, 1.12, E.sine]]));
  tr('cc-tel', `translate(540,${820 + Math.sin(t * 1.3) * 6}) rotate(${-4 + Math.sin(t * .9)})`);
  // appel sans réponse puis liste des entreprises
  const raccroche = tRate + .25;
  $('cc-etat').textContent = t < tRate - .2 ? `Appel en cours… 0:${String(12 + Math.floor((t - t0) * 3)).padStart(2, '0')}` : 'Pas de réponse';
  attr('cc-etat', 'fill', t < tRate - .2 ? '#b9c3d3' : '#ff8a8e');
  const k = prog(t, raccroche, raccroche + .3, E.io);
  op('cc-appel', 1 - k);
  tr('cc-liste', `translate(0,${(1 - k) * 80})`);
  op('cc-c1', t > raccroche + .2 ? .55 : 1);
  op('cc-sansReponse', prog(t, raccroche + .2, raccroche + .45));
  // le pouce glisse vers le concurrent puis appuie sur « Site web »
  const p = piste(t, [[t0, { x: 380, y: 520 }], [raccroche + .3, { x: 360, y: 520 }], [tSuiv - .2, { x: -130, y: 168 }, E.io], [tSuiv, { x: -150, y: 182 }, E.out], [tSuiv + .5, { x: -140, y: 176 }]]);
  tr('cc-pouce', `translate(${p.x + 180},${p.y + 30})`);
  const tap = prog(t, tSuiv, tSuiv + .4, E.out);
  op('cc-tap', t > tSuiv && t < tSuiv + .4 ? .35 * (1 - tap) : 0);
  attr('cc-tap', 'r', 20 + tap * 70);
  tr('cc-tap', `translate(${p.x},${p.y})`);
  tr('cc-btnSite', t > tSuiv && t < tSuiv + .25 ? 'translate(95,187) scale(.93) translate(-95,-187)' : '');
}

// ---------------- Scènes 7 et 8 : le soir, puis le déclic ----------------
function renduSoir(t, t0, tFin) {
  const u = t - t0, v8 = P('v8');
  const declic = prog(t, v8, v8 + .25, E.out);
  const zoomTel = prog(t, tFin - .45, tFin + .3, E.in);
  let cam = piste(t, [[t0, { x: 470, y: 840, z: 1.22 }], [v8, { x: 440, y: 860, z: 1.45 }, E.sine], [v8 + .3, { x: 425, y: 880, z: 2.1 }, E.expoOut], [tFin - .45, { x: 425, y: 890, z: 2.2 }, E.sine]]);
  if (zoomTel > 0) cam = { x: mix(cam.x, 455, zoomTel), y: mix(cam.y, 1000, zoomTel), z: mix(cam.z, 9, zoomTel) };
  camera('so-cam', cam.x + bruit(t, 11) * 2, cam.y + bruit(t, 12) * 2, cam.z);
  // gel sur le déclic : désaturation et halo doré
  const gel = fenetre(t, v8 - .02, v8 + .35);
  if (gel) { L.soir.setAttribute('filter', 'url(#desature)'); attr('desatM', 'values', mix(.15, 1, prog(t, v8 + .1, v8 + .35))); }
  $('flash').style.opacity = Math.max(+$('flash').style.opacity, .35 * (1 - Math.abs(t - v8) / .08));
  op('so-halo', prog(t, v8 + .2, v8 + .8) * .55);
  op('so-cone', 1 + Math.sin(t * 30) * .02);
  const sec = Math.floor((t - t0) * 2);
  $('so-heure').textContent = `22:${String(47 + Math.floor(sec / 6)).padStart(2, '0')}`;
  // bulles fantômes : les mêmes questions reviennent
  const fant = prog(t, M('v7', 9), M('v7', 9) + .4) * (1 - prog(t, v8 - .2, v8));
  op('so-fantomes', fant * .85);
  for (let i = 0; i < 3; i++) tr('sfb' + i, `translate(0,${Math.sin(t * 2 + i) * 6})`);
  // l'artisan, fatigué, au téléphone, puis il baisse le téléphone et nous regarde
  const baisse = prog(t, v8 + .25, v8 + .75, E.io);
  const baille = fenetre(t, P('v7') + 1.1, P('v7') + 1.9);
  const pose = { x: 420, y: 1560, s: 1, resp: Math.sin(t * 1.6), cligne: clignement(t, 5), paupieres: mix(.45, 0, declic), enduit: .5, tete: mix(-6, 0, declic), tourne: .15, regardX: .4, regardY: .5 };
  Object.assign(pose, ikBras('G', -95 + Math.sin(t * 6) * 6 * (1 - declic), 8, -1, 70));
  const haut = ikBras('D', 50, -292, 1, 200), bas = ikBras('D', 30, -190, -1, 175);
  pose.bD = mix(haut.bD, bas.bD, baisse); pose.abD = mix(haut.abD, bas.abD, baisse); pose.mD = mix(haut.mD, bas.mD, baisse);
  if (t < v8) {
    pose.bouche = baille ? 'baille' : (parole(t, Math.sin(t * 2.3) > 0) || 'neutre');
    if (baille) { pose.paupieres = .8; pose.tete = -10; }
    pose.sourcils = .3; pose.froncer = .5;
  } else {
    pose.regardX = mix(.4, 0, declic); pose.regardY = mix(.5, 0, declic);
    pose.sourcils = 1.1 * declic; pose.froncer = .3; pose.tourne = mix(.15, 0, declic);
    pose.bouche = t > v8 + .9 ? 'sourire' : 'o';
  }
  poser('ar3', pose);
}

// ---------------- Scène 9 : le site ----------------
function renduSite(t, t0, tFin) {
  const u = t - t0;
  const mots = [M('v9', 1), M('v9', 3), M('v9', 7), M('v9', 9), M('v9', 12)];
  const defile = piste(t, [[t0, 0], [mots[0] - .1, 120, E.io], [mots[0] + .3, 480, E.expoIo], [mots[1] + .3, 880, E.expoIo], [mots[2] + .3, 1310, E.expoIo], [mots[3] + .3, 1620, E.expoIo]]);
  tr('si-contenu', `translate(0,${-defile})`);
  const entree = prog(t, t0, t0 + .45, E.expoOut);
  const fin = prog(t, mots[4] - .1, mots[4] + .5, E.expoIo);
  tr('si-tel', `translate(540,${700 + (1 - entree) * 160}) scale(${mix(1.35, 1, entree) * mix(1, .9, fin)}) rotate(${mix(0, -3, fin)})`);
  camera('si-cam', 540 + bruit(t, 13) * 2, 960 + bruit(t, 14) * 2, 1);
  const cotes = [[60, 360, -1], [700, 600, 1], [60, 840, -1], [590, 1080, 1]];
  for (let i = 0; i < 4; i++) {
    const a = prog(t, mots[i] - .05, mots[i] + .3, E.backS);
    vis('pa' + i, a > 0);
    const [x, y, s] = cotes[i];
    const yf = mix(y, 170 + i * 0, 0);
    tr('pa' + i, `translate(${x + (1 - a) * 80 * s},${y - fin * 40}) scale(${a})`);
  }
  const c = prog(t, M('v9', 17) - .05, M('v9', 17) + .3, E.backS);
  vis('si-check', c > 0);
  tr('si-check', `translate(830,190) scale(${c}) rotate(${(1 - c) * -30})`);
  // le formulaire se remplit : photos qui arrivent une à une
  for (let i = 0; i < 4; i++) { const a = prog(t, mots[3] + .2 + i * .12, mots[3] + .45 + i * .12, E.backS); tr('si-ph' + i, `translate(${i * 122 + 55},${34 + 42}) scale(${a}) translate(-55,-42)`); }
  tr('si-envoyer', `translate(-240,480) ${t > mots[4] && t < mots[4] + .2 ? 'translate(240,36) scale(.95) translate(-240,-36)' : ''}`);
}

// ---------------- Interface : bulles et notification ----------------
function renduUI(t) {
  for (let i = 0; i < 5; i++) {
    const t0 = P('v4') - .1 + i * .5, t1 = P('v5') - .22;
    const a = prog(t, t0, t0 + .3, E.backS), b = prog(t, t1, t1 + .16, E.in);
    const vivant = t >= t0 && t < t1 + .16;
    vis('bq' + i, vivant);
    if (vivant) {
      tr('bq' + i + '-in', `translate(${(1 - a) * (i % 2 ? 50 : -50)},${(1 - a) * 30 + Math.sin((t - t0) * 2.4 + i) * 5}) scale(${a * (1 + b * .3)})`);
      op('bq' + i + '-in', 1 - b);
    }
  }
  const n0 = P('v10') + .25;
  const a = prog(t, n0, n0 + .45, E.backS), b = prog(t, F('v10') + .1, F('v10') + .45, E.in);
  vis('notif', t > n0 && b < 1);
  tr('notif', `translate(50,${mix(-260, 90, a) - b * 350})`);
}

// ---------------- Carton final ----------------
function renduFin(t, t0) {
  const u = t - t0;
  const a = prog(u, .15, .55, E.backS), b = prog(u, .55, .95, E.backS), c = prog(u, .9, 1.3, E.backS), d = prog(u, 1.2, 1.7, E.out);
  tr('fi-l1', `translate(540,740) scale(${.8 + .2 * a}) translate(-540,-740)`); op('fi-l1', a);
  tr('fi-l2', `translate(0,${(1 - b) * 30})`); op('fi-l2', b);
  tr('fi-pill', `translate(400,884) scale(${b}) rotate(${(1 - b) * -8}) translate(-400,-884)`);
  tr('fi-bulle', `translate(540,1080) scale(${c})`); op('fi-bulle', c);
  op('fi-logo', d);
}

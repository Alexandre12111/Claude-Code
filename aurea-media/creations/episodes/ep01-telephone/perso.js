// Personnages articulés (marionnettes vectorielles). Repère : pieds au sol en (0, 0), y vers le bas, taille ≈ 750.
// Côtés nommés par rapport à l'écran : G (x < 0), D (x > 0).
const PALETTES = {
  artisan: {
    peau: '#e3a982', peauOmbre: '#c4835e', peauClair: '#f0c3a0', cheveux: '#3a271c', cheveuxClair: '#5a3d2b', barbe: '#4a3122',
    haut: '#e7e2d8', hautOmbre: '#c9c1b2', bas: '#27364f', basOmbre: '#1b263a', basClair: '#34476a', piqure: '#c9a24a',
    chaussure: '#4a3424', semelle: '#1d1712', yeux: '#4a2f1d', barbeOn: true, salopette: true, cheveuxLongs: false,
  },
  cliente: {
    peau: '#f1c6a5', peauOmbre: '#d39c7a', peauClair: '#fbdcc4', cheveux: '#a46a3f', cheveuxClair: '#c48a5a', barbe: '#000',
    haut: '#7d8f7a', hautOmbre: '#62735f', bas: '#2f2f38', basOmbre: '#212128', basClair: '#3d3d48', piqure: '#2f2f38',
    chaussure: '#d9d2c6', semelle: '#8f877b', yeux: '#3b5a3a', barbeOn: false, salopette: false, cheveuxLongs: true,
  },
};

function personnage(id, nom) {
  const c = PALETTES[nom];
  const g = (n) => `${id}-${n}`;
  const defs = `
  <linearGradient id="${g('gPeau')}" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="${c.peauClair}"/><stop offset=".55" stop-color="${c.peau}"/><stop offset="1" stop-color="${c.peauOmbre}"/></linearGradient>
  <linearGradient id="${g('gBas')}" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="${c.basClair}"/><stop offset=".5" stop-color="${c.bas}"/><stop offset="1" stop-color="${c.basOmbre}"/></linearGradient>
  <linearGradient id="${g('gHaut')}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${c.haut}"/><stop offset="1" stop-color="${c.hautOmbre}"/></linearGradient>
  <radialGradient id="${g('gJoue')}" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#e07a5f" stop-opacity=".35"/><stop offset="1" stop-color="#e07a5f" stop-opacity="0"/></radialGradient>
  <clipPath id="${g('clipOeilG')}"><ellipse cx="-17" cy="-76" rx="8.6" ry="5.8"/></clipPath>
  <clipPath id="${g('clipOeilD')}"><ellipse cx="17" cy="-76" rx="8.6" ry="5.8"/></clipPath>`;

  // ---- Jambes (hanche à l'origine, pendantes vers +y) ----
  const jambe = (s) => `
  <g id="${g('cuisse' + s)}">
    <path d="M-33,-40 C-32,50 -26,130 -21,192 L21,192 C26,130 31,50 31,-40 Z" fill="url(#${g('gBas')})"/>
    <path d="M10,10 C14,70 16,130 16,190" stroke="${c.basOmbre}" stroke-width="3" fill="none" opacity=".6"/>
    ${c.salopette ? `<path d="M-14,40 h22 v26 h-22 z" fill="${c.basOmbre}" opacity=".55"/><path d="M-14,40 h22" stroke="${c.piqure}" stroke-width="1.6" stroke-dasharray="3 3"/>` : ''}
    <g id="${g('tibia' + s)}" transform="translate(0,190)">
      ${c.salopette ? `<ellipse cx="0" cy="4" rx="24" ry="20" fill="#5d6470"/><ellipse cx="-4" cy="0" rx="14" ry="9" fill="#7a828f" opacity=".7"/>` : ''}
      <path d="M-20,0 C-21,60 -19,120 -17,168 L17,168 C19,120 21,60 20,0 Z" fill="url(#${g('gBas')})"/>
      ${c.salopette ? `<ellipse cx="0" cy="6" rx="23" ry="19" fill="#5d6470"/><ellipse cx="-5" cy="2" rx="13" ry="8" fill="#8a929e" opacity=".6"/>` : ''}
      <g id="${g('pied' + s)}" transform="translate(0,166)">
        <g transform="translate(${s === 'G' ? -3 : 3},0)">
        <path d="M-20,-10 C-22,6 -25,14 -26,21 C-26,28 -20,30 -12,30 L12,30 C20,30 26,28 26,21 C25,14 22,6 20,-10 Z" fill="${c.chaussure}"/>
        <path d="M-24,18 C-18,12 18,12 24,18 C24,24 20,27 12,27 L-12,27 C-20,27 -24,24 -24,18 Z" fill="#000" opacity=".18"/>
        <path d="M-14,4 C-8,0 8,0 14,4" stroke="#000" stroke-opacity=".3" stroke-width="2" fill="none"/>
        <ellipse cx="-6" cy="16" rx="8" ry="4" fill="#fff" opacity=".12"/>
        <rect x="-27" y="28" width="54" height="7" rx="3" fill="${c.semelle}"/>
        </g>
      </g>
    </g>
  </g>`;

  // ---- Bras (épaule à l'origine) ----
  const bras = (s) => `
  <g id="${g('bras' + s)}">
    <path d="M-17,-8 C-19,40 -16,90 -14,132 L14,132 C16,90 19,40 17,-8 Z" fill="url(#${g('gPeau')})"/>
    <path d="M-23,-14 C-26,20 -24,48 -22,68 C-8,74 8,74 22,68 C24,48 26,20 23,-14 C8,-22 -8,-22 -23,-14 Z" fill="url(#${g('gHaut')})"/>
    <path d="M-22,68 C-8,74 8,74 22,68" stroke="${c.hautOmbre}" stroke-width="3" fill="none"/>
    <g id="${g('avb' + s)}" transform="translate(0,130)">
      <ellipse cx="0" cy="0" rx="15" ry="13" fill="${c.peau}"/>
      <path d="M-15,0 C-16,40 -13,85 -11,118 L11,118 C13,85 16,40 15,0 Z" fill="url(#${g('gPeau')})"/>
      <path d="M6,20 C8,50 8,80 7,110" stroke="${c.peauOmbre}" stroke-width="3" fill="none" opacity=".5"/>
      <g id="${g('enduitBras' + s)}" opacity="0"><path d="M-12,70 C-4,64 6,76 12,72 L11,118 L-11,118 Z" fill="#e9e5dc"/><circle cx="-6" cy="58" r="3" fill="#e9e5dc"/></g>
      <g id="${g('main' + s)}" transform="translate(0,116)">
        <g id="${g('props' + s)}"></g>
        <path d="M-13,-4 C-16,10 -15,26 -10,36 C-6,42 6,42 10,36 C15,26 16,10 13,-4 Z" fill="${c.peau}"/>
        <path d="M${s === 'G' ? '10' : '-10'},4 C${s === 'G' ? '22,8 22,22 14,26' : '-22,8 -22,22 -14,26'}" stroke="${c.peau}" stroke-width="9" stroke-linecap="round" fill="none"/>
        <path d="M-8,30 C-3,33 3,33 8,30" stroke="${c.peauOmbre}" stroke-width="1.6" fill="none" opacity=".8"/>
        <g id="${g('enduitMain' + s)}" opacity="0"><path d="M-12,8 C-6,2 8,14 13,6 L12,30 C4,36 -6,34 -11,26 Z" fill="#ece8df"/><circle cx="7" cy="34" r="3.5" fill="#ece8df"/></g>
      </g>
    </g>
  </g>`;

  // ---- Tête (base du cou à l'origine) ----
  const bouches = `
    <g id="${g('b-neutre')}"><path d="M-10,-36 C-4,-34 4,-34 10,-36" stroke="#6b3a2c" stroke-width="3" stroke-linecap="round" fill="none"/></g>
    <g id="${g('b-sourire')}"><path d="M-13,-39 C-6,-31 6,-31 13,-39 C6,-35 -6,-35 -13,-39 Z" fill="#6b2f26"/><path d="M-11,-38.5 C-5,-36 5,-36 11,-38.5 L10,-37 C4,-35.5 -4,-35.5 -10,-37 Z" fill="#fff"/></g>
    <g id="${g('b-grimace')}"><path d="M-14,-35 C-8,-40 8,-40 14,-35 C8,-31 -8,-31 -14,-35 Z" fill="#5a2620"/><path d="M-12,-35.5 C-6,-38.5 6,-38.5 12,-35.5 L12,-33.6 C6,-34.8 -6,-34.8 -12,-33.6 Z" fill="#fff"/></g>
    <g id="${g('b-o')}"><ellipse cx="0" cy="-35" rx="6.5" ry="8" fill="#4a1d18"/><ellipse cx="0" cy="-31" rx="4" ry="2.6" fill="#b5554a"/></g>
    <g id="${g('b-parle')}"><path d="M-11,-38 C-5,-40 5,-40 11,-38 C7,-28 -7,-28 -11,-38 Z" fill="#4a1d18"/><path d="M-9,-38.5 C-4,-39.6 4,-39.6 9,-38.5 L8.5,-37 C4,-37.8 -4,-37.8 -8.5,-37 Z" fill="#fff"/><ellipse cx="0" cy="-31.5" rx="5" ry="2" fill="#b5554a"/></g>
    <g id="${g('b-baille')}"><ellipse cx="0" cy="-33" rx="9" ry="13" fill="#3d1713"/><ellipse cx="0" cy="-25" rx="6" ry="4" fill="#b5554a"/><path d="M-7,-44 C-3,-45.5 3,-45.5 7,-44 L6,-42.5 C3,-43.4 -3,-43.4 -6,-42.5 Z" fill="#fff"/></g>
    <g id="${g('b-dents')}"><path d="M-13,-36 C-6,-41 6,-41 13,-36 C6,-30 -6,-30 -13,-36 Z" fill="#fff"/><path d="M-13,-36 C-6,-34 6,-34 13,-36" stroke="#c9b9ad" stroke-width="1" fill="none"/><path d="M-13,-36 C-6,-41 6,-41 13,-36 C6,-30 -6,-30 -13,-36 Z" stroke="#6b2f26" stroke-width="2" fill="none"/></g>`;
  const oeil = (s, x) => `
    <g>
      <ellipse cx="${x}" cy="-76" rx="8.6" ry="5.8" fill="#fbf7f2"/>
      <g clip-path="url(#${g('clipOeil' + s)})">
        <g id="${g('iris' + s)}"><circle cx="${x}" cy="-76" r="4.6" fill="${c.yeux}"/><circle cx="${x}" cy="-76" r="2.2" fill="#140c08"/><circle cx="${x + 1.6}" cy="-77.8" r="1.3" fill="#fff"/></g>
        <g id="${g('paup' + s)}"><rect x="${x - 10}" y="-94" width="20" height="13" fill="${c.peau}"/><path d="M${x - 10},-81 C${x - 4},-80 ${x + 4},-80 ${x + 10},-81" stroke="#2a1a12" stroke-width="2.2" fill="none"/></g>
      </g>
      <path d="M${x - 9},-77 C${x - 6},-83 ${x + 6},-83 ${x + 9},-77" stroke="#2a1a12" stroke-width="2" fill="none" stroke-linecap="round"/>
    </g>`;
  const cheveux = c.cheveuxLongs
    ? `<path d="M-44,-80 C-50,-130 -20,-142 2,-140 C30,-140 52,-122 46,-78 C50,-40 54,-4 46,20 C40,8 38,-20 40,-60 C34,-96 24,-110 0,-112 C-22,-110 -34,-96 -40,-60 C-38,-20 -42,8 -50,20 C-56,-4 -50,-40 -44,-80 Z" fill="${c.cheveux}"/>`
    : '';
  const meche = c.cheveuxLongs
    ? `<path d="M-42,-84 C-44,-124 -16,-138 4,-136 C30,-134 46,-118 43,-86 C30,-108 4,-118 -18,-110 C-30,-104 -38,-96 -42,-84 Z" fill="${c.cheveux}"/><path d="M-10,-130 C10,-126 28,-118 36,-102" stroke="${c.cheveuxClair}" stroke-width="4" fill="none" opacity=".6"/>`
    : `<path d="M-43,-78 C-47,-116 -28,-138 0,-138 C30,-138 48,-118 43,-78 C40,-94 34,-104 22,-108 C8,-104 -12,-106 -26,-108 C-36,-102 -40,-92 -43,-78 Z" fill="${c.cheveux}"/>
       <path d="M-30,-118 C-14,-128 12,-130 30,-120" stroke="${c.cheveuxClair}" stroke-width="5" fill="none" opacity=".55" stroke-linecap="round"/>`;
  const barbe = c.barbeOn ? `
    <path d="M-40,-66 C-40,-41 -32,-24 -18,-14 C-10,-7 10,-7 18,-14 C32,-24 40,-41 40,-66 C34,-48 26,-40 16,-38 C8,-42 -8,-42 -16,-38 C-26,-40 -34,-48 -40,-66 Z" fill="${c.barbe}" opacity=".88"/>
    <path d="M-15,-47 C-8,-51 -2,-49 0,-47 C2,-49 8,-51 15,-47 C10,-43 4,-44 0,-45 C-4,-44 -10,-43 -15,-47 Z" fill="${c.barbe}"/>` : '';
  const tete = `
  <g id="${g('tete')}" transform="translate(0,-228)">
    ${cheveux}
    <path d="M-15,-20 L-14,8 C-6,14 6,14 14,8 L15,-20 Z" fill="${c.peauOmbre}"/>
    <ellipse id="${g('oreilleG')}" cx="-41" cy="-70" rx="7" ry="13" fill="${c.peauOmbre}"/>
    <ellipse id="${g('oreilleD')}" cx="41" cy="-70" rx="7" ry="13" fill="${c.peau}"/>
    <path d="M-40,-80 C-42,-120 -20,-127 0,-127 C20,-127 42,-120 40,-80 C40,-50 34,-27 18,-15 C10,-9 -10,-9 -18,-15 C-34,-27 -40,-50 -40,-80 Z" fill="url(#${g('gPeau')})"/>
    <path d="M24,-110 C38,-96 40,-70 36,-48 C32,-34 26,-24 18,-16 C28,-36 32,-70 24,-110 Z" fill="${c.peauOmbre}" opacity=".45"/>
    <g id="${g('traits')}">
      <ellipse cx="-24" cy="-58" rx="10" ry="6" fill="url(#${g('gJoue')})"/><ellipse cx="24" cy="-58" rx="10" ry="6" fill="url(#${g('gJoue')})"/>
      ${barbe}
      ${oeil('G', -17)}${oeil('D', 17)}
      <path id="${g('sourcilG')}" d="M-28,-88 C-22,-94 -12,-95 -6,-91 L-7,-87 C-13,-89 -21,-89 -27,-85 Z" fill="${c.cheveux}"/>
      <path id="${g('sourcilD')}" d="M28,-88 C22,-94 12,-95 6,-91 L7,-87 C13,-89 21,-89 27,-85 Z" fill="${c.cheveux}"/>
      <path d="M1,-80 C2,-70 5,-61 6,-56 C3,-52 -3,-52 -6,-55" stroke="${c.peauOmbre}" stroke-width="2.6" fill="none" stroke-linecap="round"/>
      <ellipse cx="2" cy="-58" rx="4" ry="2.2" fill="${c.peauClair}" opacity=".7"/>
      <g id="${g('bouches')}">${bouches}</g>
      <g id="${g('sueur')}" opacity="0"><path d="M36,-104 C40,-96 42,-90 38,-87 C34,-89 33,-96 36,-104 Z" fill="#cfe7f5"/><path d="M37,-99 C38,-96 38.5,-93 37.5,-91" stroke="#fff" stroke-width="1" fill="none"/></g>
    </g>
    ${meche}
    ${c.salopette ? `<g id="${g('crayon')}"><rect x="38" y="-96" width="5" height="34" rx="1" fill="#e8b93a" transform="rotate(-24 40 -80)"/><path d="M38,-62 l2.5,7 l2.5,-7 z" fill="#3a2a1a" transform="rotate(-24 40 -80)"/></g>` : ''}
  </g>`;

  // ---- Torse (hanches à l'origine) ----
  const torse = c.salopette ? `
    <path d="M-58,-228 C-80,-222 -94,-212 -98,-196 C-96,-160 -90,-120 -82,-80 C-76,-50 -72,-24 -72,0 L72,0 C72,-24 76,-50 82,-80 C90,-120 96,-160 98,-196 C94,-212 80,-222 58,-228 C40,-216 -40,-216 -58,-228 Z" fill="url(#${g('gHaut')})"/>
    <path d="M60,-200 C70,-150 72,-100 66,-60" stroke="${c.hautOmbre}" stroke-width="10" fill="none" opacity=".5" stroke-linecap="round"/>
    <path d="M-30,-226 C-14,-214 14,-214 30,-226" stroke="${c.hautOmbre}" stroke-width="5" fill="none"/>
    <path d="M-46,-170 L46,-170 L52,-48 L-52,-48 Z" fill="url(#${g('gBas')})"/>
    <path d="M-46,-172 L46,-172 L46,-160 L-46,-160 Z" fill="#000" opacity=".12"/>
    <path d="M-46,-166 L46,-166" stroke="${c.piqure}" stroke-width="1.6" stroke-dasharray="4 3"/>
    <rect x="-24" y="-150" width="48" height="40" rx="4" fill="${c.basOmbre}"/><path d="M-24,-132 h48" stroke="${c.piqure}" stroke-width="1.4" stroke-dasharray="3 3"/>
    <rect x="10" y="-162" width="5" height="30" rx="1" fill="#e8b93a"/><rect x="2" y="-160" width="4" height="26" rx="1" fill="#2c2c2c"/>
    <path d="M-44,-168 C-50,-196 -60,-216 -74,-224" stroke="${c.bas}" stroke-width="16" fill="none" stroke-linecap="round"/>
    <path d="M44,-168 C50,-196 60,-216 74,-224" stroke="${c.basOmbre}" stroke-width="16" fill="none" stroke-linecap="round"/>
    <circle cx="-44" cy="-164" r="6" fill="#c9a24a"/><circle cx="44" cy="-164" r="6" fill="#c9a24a"/><circle cx="-45" cy="-165" r="2.2" fill="#f1d58a"/><circle cx="43" cy="-165" r="2.2" fill="#f1d58a"/>
    <path d="M-74,-52 C-30,-44 30,-44 74,-52 C76,-20 74,10 70,24 L-70,24 C-74,10 -76,-20 -74,-52 Z" fill="url(#${g('gBas')})"/><path d="M-6,22 L6,22 L0,34 Z" fill="${c.basOmbre}"/>
    <path d="M-74,-46 C-30,-38 30,-38 74,-46" stroke="${c.basOmbre}" stroke-width="5" fill="none"/>
    <path d="M0,-40 L0,30" stroke="${c.basOmbre}" stroke-width="3"/>
    <path d="M-70,-32 C-58,-22 -52,-8 -54,8" stroke="${c.piqure}" stroke-width="1.4" stroke-dasharray="3 3" fill="none"/>
    <path d="M70,-32 C58,-22 52,-8 54,8" stroke="${c.piqure}" stroke-width="1.4" stroke-dasharray="3 3" fill="none"/>
    <g id="${g('poche-tel')}"></g>`
    : `
    <path d="M-54,-228 C-74,-222 -88,-212 -92,-196 C-90,-160 -80,-120 -74,-80 C-70,-50 -70,-24 -72,0 L72,0 C70,-24 70,-50 74,-80 C80,-120 90,-160 92,-196 C88,-212 74,-222 54,-228 C40,-216 -40,-216 -54,-228 Z" fill="url(#${g('gHaut')})"/>
    <path d="M-26,-226 L0,-186 L26,-226" stroke="#f4efe6" stroke-width="7" fill="none"/>
    <path d="M-22,-222 L0,-190 L22,-222 Z" fill="#f4efe6"/>
    <path d="M0,-190 L0,-10" stroke="${c.hautOmbre}" stroke-width="3"/>
    <circle cx="6" cy="-150" r="3" fill="${c.hautOmbre}"/><circle cx="6" cy="-110" r="3" fill="${c.hautOmbre}"/><circle cx="6" cy="-70" r="3" fill="${c.hautOmbre}"/>
    <path d="M-74,-8 C-30,0 30,0 74,-8 C76,8 74,18 70,26 L-70,26 C-74,18 -76,8 -74,-8 Z" fill="url(#${g('gBas')})"/>`;

  return `<defs>${defs}</defs>
  <g id="${g('root')}">
    <ellipse id="${g('ombre')}" cx="0" cy="6" rx="110" ry="16" fill="#000" opacity=".22"/>
    <g id="${g('jambeG')}" transform="translate(-36,-385)">${jambe('G')}</g>
    <g id="${g('jambeD')}" transform="translate(36,-385)">${jambe('D')}</g>
    <g id="${g('torse')}" transform="translate(0,-395)">
      <g id="${g('brasArriere')}"></g>
      ${torse}
      ${tete}
      <g id="${g('epauleG')}" transform="translate(-90,-196)">${bras('G')}</g>
      <g id="${g('epauleD')}" transform="translate(90,-196)">${bras('D')}</g>
    </g>
  </g>`;
}

// Accessoires tenus en main (dessinés dans le repère de la main : paume en (0, 15))
const PROPS = {
  tel: (id) => `<g id="${id}"><rect x="-17" y="-8" width="34" height="62" rx="7" fill="#15171c" transform="rotate(-8)"/><rect x="-14" y="-4" width="28" height="54" rx="5" fill="#2a3a55" transform="rotate(-8)"/><rect x="-14" y="-4" width="28" height="54" rx="5" fill="url(#ecranLueur)" opacity=".6" transform="rotate(-8)"/></g>`,
  taloche: (id) => `<g id="${id}"><rect x="-6" y="6" width="12" height="30" rx="5" fill="#7a4b2a"/><path d="M-58,36 L58,36 L54,46 L-54,46 Z" fill="#b9c0c8"/><path d="M-58,36 L58,36 L57,39 L-57,39 Z" fill="#e3e8ec"/><path d="M-40,44 C-20,50 20,50 40,44 L40,47 C20,54 -20,54 -40,47 Z" fill="#ece8df"/></g>`,
  stylo: (id) => `<g id="${id}"><rect x="-3" y="-30" width="6" height="56" rx="2" fill="#1f2a44" transform="rotate(30)"/><path d="M-3,26 l3,8 l3,-8 z" fill="#c9a24a" transform="rotate(30)"/></g>`,
  tasse: (id) => `<g id="${id}"><path d="M-18,0 L18,0 L15,36 C14,40 -14,40 -15,36 Z" fill="#f2efe8"/><path d="M18,8 C30,8 30,26 16,26" stroke="#f2efe8" stroke-width="5" fill="none"/><ellipse cx="0" cy="1" rx="18" ry="4" fill="#5a3a24"/></g>`,
};

// Pose : angles en degrés. Valeurs par défaut = debout, bras le long du corps.
const POSE0 = {
  x: 0, y: 0, s: 1, sx: 1, pench: 0, resp: 0, tete: 0, teteY: 0, regardX: 0, regardY: 0, tourne: 0,
  bG: 8, abG: -6, mG: 0, bD: -8, abD: 6, mD: 0,
  cG: 3, tG: 0, pG: 0, cD: -3, tD: 0, pD: 0, hanche: 0,
  sourcils: 0, froncer: 0, cligne: 0, paupieres: 0, sueur: 0, enduit: 0, bouche: 'neutre',
};
function poser(id, p) {
  p = Object.assign({}, POSE0, p);
  const g = (n) => `${id}-${n}`;
  tr(g('root'), `translate(${p.x},${p.y}) scale(${p.s * p.sx},${p.s})`);
  op(g('ombre'), p.ombre ?? .22);
  const respire = 1 + p.resp * .012;
  tr(g('torse'), `translate(0,${-395 + p.hanche}) rotate(${p.pench}) scale(1,${respire})`);
  tr(g('jambeG'), `translate(-36,${-385 + p.hanche}) rotate(${p.cG})`); tr(g('tibiaG'), `translate(0,190) rotate(${p.tG})`); tr(g('piedG'), `translate(0,166) rotate(${p.pG})`);
  tr(g('jambeD'), `translate(36,${-385 + p.hanche}) rotate(${p.cD})`); tr(g('tibiaD'), `translate(0,190) rotate(${p.tD})`); tr(g('piedD'), `translate(0,166) rotate(${p.pD})`);
  tr(g('epauleG'), `translate(-90,-196) rotate(${p.bG})`); tr(g('avbG'), `translate(0,130) rotate(${p.abG})`); tr(g('mainG'), `translate(0,116) rotate(${p.mG})`);
  tr(g('epauleD'), `translate(90,-196) rotate(${p.bD})`); tr(g('avbD'), `translate(0,130) rotate(${p.abD})`); tr(g('mainD'), `translate(0,116) rotate(${p.mD})`);
  tr(g('tete'), `translate(0,${-228 + p.teteY}) rotate(${p.tete})`);
  const t = p.tourne;  // -1 … 1 : tête tournée vers la gauche ou la droite de l'écran
  tr(g('traits'), `translate(${t * 9},0)`);
  tr(g('oreilleG'), `translate(${t > 0 ? t * 4 : t * 2},0) scale(${1 - Math.max(0, -t) * .6},1)`);
  tr(g('oreilleD'), `translate(${t < 0 ? t * 4 : t * 2},0) scale(${1 - Math.max(0, t) * .6},1)`);
  for (const s of ['G', 'D']) {
    tr(g('iris' + s), `translate(${p.regardX * 3.6},${p.regardY * 2.4})`);
    const ferme = clamp(Math.max(p.cligne, p.paupieres));
    tr(g('paup' + s), `translate(0,${-8 + ferme * 15.5})`);
  }
  const sg = p.sourcils, fr = p.froncer;  // sourcils levés (+) ou baissés (-) ; froncer : inclinaison inquiète (+) ou colère (-)
  tr(g('sourcilG'), `translate(0,${-sg * 4}) rotate(${-fr * 12} -17 -89)`);
  tr(g('sourcilD'), `translate(0,${-sg * 4}) rotate(${fr * 12} 17 -89)`);
  op(g('sueur'), p.sueur);
  for (const b of ['neutre', 'sourire', 'grimace', 'o', 'parle', 'baille', 'dents']) vis(g('b-' + b), p.bouche === b);
  for (const s of ['G', 'D']) { op(g('enduitMain' + s), p.enduit); op(g('enduitBras' + s), p.enduit); }
}

// Clignement naturel : renvoie 0 … 1 à partir du temps et d'une graine
function clignement(t, graine = 0) {
  const periode = 3.1 + (graine % 3) * .7;
  const u = ((t + graine * 1.37) % periode) / periode;
  const d = .045;
  return u < d ? Math.sin((u / d) * Math.PI) : 0;
}
// Lèvres pendant la parole (alternance de formes)
function parole(t, actif) {
  if (!actif) return null;
  const k = Math.floor(t * 9) % 5;
  return ['parle', 'o', 'parle', 'neutre', 'dents'][k];
}

// Cinématique inverse du bras : la main (poignet) va en (x, y) dans le repère du torse.
// coude = +1 ou -1 choisit le sens de pliage. Renvoie { b, ab } à fusionner dans la pose.
function ikBras(cote, x, y, coude = 1, angleMain = null) {
  const S = { x: cote === 'G' ? -90 : 90, y: -196 }, L1 = 130, L2 = 116;
  const dx = x - S.x, dy = y - S.y;
  const D = Math.min(Math.hypot(dx, dy), L1 + L2 - .5);
  const phi = Math.atan2(-dx, dy);
  const alpha = Math.acos(clamp((L1 * L1 + D * D - L2 * L2) / (2 * L1 * D), -1, 1));
  const t1 = phi + coude * alpha;
  const E = { x: S.x - L1 * Math.sin(t1), y: S.y + L1 * Math.cos(t1) };
  const t2 = Math.atan2(-(x - E.x), y - E.y);
  const deg = 180 / Math.PI;
  const o = {};
  o['b' + cote] = t1 * deg; o['ab' + cote] = (t2 - t1) * deg;
  if (angleMain !== null) o['m' + cote] = angleMain - t2 * deg;
  return o;
}

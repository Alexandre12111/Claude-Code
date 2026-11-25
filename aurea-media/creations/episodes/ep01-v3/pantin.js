// Personnages V2 : style cartoon géométrique, membres en « tubes » continus recalculés à chaque image
// (épaule → coude → main, hanche → genou → pied). Aucune articulation rigide : pas de cassure possible.
// Repère : pieds au sol en (0, 0), y vers le bas. Taille ≈ 780.

const STYLES = {
  artisan: {
    peau: '#f2b68c', peauOmbre: '#dc9a70', peauTrait: '#c27e57', barbe: '#5b3a27', cheveux: '#5b3a27',
    haut: '#f4f0e8', hautOmbre: '#dcd5c8', bas: '#2d4373', basOmbre: '#223459', basTrait: '#1a2849',
    bonnet: '#ff7b2c', bonnetOmbre: '#e0611a', chaussure: '#7a4a2a', semelle: '#2b1c12', bouton: '#e7b955',
    largeurTete: 230, hauteurTete: 240, barbeOn: true, bonnetOn: true, salopette: true,
  },
  prospecte: {
    peau: '#c98d66', peauOmbre: '#b0744f', peauTrait: '#8f5a3a', barbe: '#000', cheveux: '#2a1c16',
    haut: '#3fa39a', hautOmbre: '#2f857d', bas: '#e9e2d4', basOmbre: '#d6cdbd', basTrait: '#b8ad9a',
    bonnet: '#000', bonnetOmbre: '#000', chaussure: '#ffffff', semelle: '#c9c1b2', bouton: '#fff',
    largeurTete: 200, hauteurTete: 226, barbeOn: false, bonnetOn: false, salopette: false, chignon: true, lunettes: false,
  },
  cliente: {
    peau: '#f6c7a6', peauOmbre: '#e3a985', peauTrait: '#c98b66', barbe: '#000', cheveux: '#7a3f22',
    haut: '#e9a93a', hautOmbre: '#d08e25', bas: '#3c4f78', basOmbre: '#2e3e60', basTrait: '#24324e',
    bonnet: '#000', bonnetOmbre: '#000', chaussure: '#f1ece3', semelle: '#9a8f80', bouton: '#fff',
    largeurTete: 200, hauteurTete: 226, barbeOn: false, bonnetOn: false, salopette: false, chignon: true, lunettes: true,
  },
};

class Pantin {
  constructor(id, nom) { this.id = id; this.c = STYLES[nom]; this.nom = nom; }
  g(n) { return `${this.id}-${n}`; }

  svg() {
    const c = this.c, g = n => this.g(n), W = c.largeurTete, H = c.hauteurTete;
    // ----- Tête (centre du crâne en 0,0 ; cou en 0, H/2) -----
    const face = `M${-W / 2},${-H * .15} C${-W / 2},${-H * .55} ${-W * .35},${-H * .62} 0,${-H * .62} C${W * .35},${-H * .62} ${W / 2},${-H * .55} ${W / 2},${-H * .15}
      C${W / 2},${H * .25} ${W * .38},${H * .46} 0,${H * .48} C${-W * .38},${H * .46} ${-W / 2},${H * .25} ${-W / 2},${-H * .15} Z`;
    const yeux = (s, x) => `
      <g id="${g('oeil' + s)}" transform="translate(${x},${-H * .08})">
        <ellipse rx="25" ry="29" fill="#fff"/>
        <g clip-path="url(#${g('clip' + s)})">
          <g id="${g('pup' + s)}"><circle r="11.5" fill="#1c1410"/><circle cx="4" cy="-5" r="4" fill="#fff"/></g>
          <g id="${g('paup' + s)}"><rect x="-30" y="-64" width="60" height="34" fill="${c.peau}"/><path d="M-27,-30 C-12,-26 12,-26 27,-30" stroke="${c.peauTrait}" stroke-width="5" stroke-linecap="round" fill="none"/></g>
          <g id="${g('paupB' + s)}"><rect x="-30" y="22" width="60" height="30" fill="${c.peau}"/></g>
        </g>
        <ellipse rx="25" ry="29" fill="none" stroke="${c.peauTrait}" stroke-width="2" opacity=".35"/>
      </g>`;
    const bouches = {
      ferme: `<path d="M-22,0 C-8,6 8,6 22,0" stroke="#3b1a14" stroke-width="6" stroke-linecap="round" fill="none"/>`,
      sourire: `<path d="M-30,-6 C-14,16 14,16 30,-6 C14,4 -14,4 -30,-6 Z" fill="#3b1a14"/><path d="M-24,-3 C-10,4 10,4 24,-3 L22,2 C10,7 -10,7 -22,2 Z" fill="#fff"/>`,
      rire: `<path d="M-34,-8 C-30,30 30,30 34,-8 C14,-2 -14,-2 -34,-8 Z" fill="#3b1a14"/><path d="M-30,-6 C-14,-1 14,-1 30,-6 L28,3 C14,7 -14,7 -28,3 Z" fill="#fff"/><ellipse cx="0" cy="17" rx="14" ry="7" fill="#e0584f"/>`,
      o: `<ellipse cx="0" cy="4" rx="13" ry="17" fill="#3b1a14"/><ellipse cx="0" cy="12" rx="8" ry="5" fill="#e0584f"/>`,
      a: `<path d="M-22,-4 C-20,24 20,24 22,-4 C10,-8 -10,-8 -22,-4 Z" fill="#3b1a14"/><path d="M-18,-4 C-8,-6 8,-6 18,-4 L17,1 C8,0 -8,0 -17,1 Z" fill="#fff"/><ellipse cx="0" cy="13" rx="10" ry="5" fill="#e0584f"/>`,
      e: `<path d="M-26,-2 C-12,10 12,10 26,-2 C12,-6 -12,-6 -26,-2 Z" fill="#3b1a14"/><path d="M-22,-2 C-10,-3 10,-3 22,-2 L21,3 C10,3 -10,3 -21,3 Z" fill="#fff"/>`,
      grimace: `<path d="M-30,2 C-16,-12 16,-12 30,2 C16,10 -16,10 -30,2 Z" fill="#fff" stroke="#3b1a14" stroke-width="5"/><path d="M-30,2 C-16,0 16,0 30,2" stroke="#c9b9ad" stroke-width="2"/><path d="M-15,-6 v13 M0,-8 v15 M15,-6 v13" stroke="#c9b9ad" stroke-width="2"/>`,
      cri: `<path d="M-26,-6 C-30,40 30,40 26,-6 C12,-12 -12,-12 -26,-6 Z" fill="#2c120e"/><path d="M-20,-6 C-8,-9 8,-9 20,-6 L19,0 C8,-2 -8,-2 -19,0 Z" fill="#fff"/><ellipse cx="0" cy="24" rx="13" ry="7" fill="#e0584f"/>`,
      baille: `<ellipse cx="0" cy="10" rx="20" ry="27" fill="#2c120e"/><ellipse cx="0" cy="26" rx="12" ry="7" fill="#e0584f"/>`,
      moue: `<path d="M-20,6 C-8,-4 8,-4 20,6" stroke="#3b1a14" stroke-width="6" stroke-linecap="round" fill="none"/>`,
      malin: `<path d="M-22,2 C-6,8 10,6 24,-8" stroke="#3b1a14" stroke-width="6" stroke-linecap="round" fill="none"/>`,
    };
    const barbe = c.barbeOn ? `<path d="M${-W * .48},${-H * .02} C${-W * .48},${H * .3} ${-W * .32},${H * .5} 0,${H * .52} C${W * .32},${H * .5} ${W * .48},${H * .3} ${W * .48},${-H * .02}
        C${W * .38},${H * .14} ${W * .2},${H * .1} ${W * .1},${H * .1} C${W * .04},${H * .06} ${-W * .04},${H * .06} ${-W * .1},${H * .1} C${-W * .2},${H * .1} ${-W * .38},${H * .14} ${-W * .48},${-H * .02} Z" fill="${c.barbe}"/>
      <path d="M${-W * .2},${H * .14} C${-W * .1},${H * .06} ${-W * .03},${H * .07} 0,${H * .1} C${W * .03},${H * .07} ${W * .1},${H * .06} ${W * .2},${H * .14} C${W * .1},${H * .18} ${W * .04},${H * .15} 0,${H * .16} C${-W * .04},${H * .15} ${-W * .1},${H * .18} ${-W * .2},${H * .14} Z" fill="${c.barbe}"/>` : '';
    const bonnet = c.bonnetOn ? `<g id="${g('bonnet')}">
        <path d="M${-W * .53},${-H * .3} C${-W * .55},${-H * .78} ${W * .55},${-H * .78} ${W * .53},${-H * .3} Z" fill="${c.bonnet}"/>
        <path d="M${W * .1},${-H * .74} C${W * .38},${-H * .7} ${W * .52},${-H * .52} ${W * .53},${-H * .3} L${W * .3},${-H * .3} C${W * .3},${-H * .5} ${W * .22},${-H * .64} ${W * .1},${-H * .74} Z" fill="${c.bonnetOmbre}"/>
        <rect x="${-W * .56}" y="${-H * .38}" width="${W * 1.12}" height="${H * .16}" rx="${H * .07}" fill="${c.bonnetOmbre}"/>
        ${Array.from({ length: 9 }, (_, i) => `<path d="M${-W * .5 + i * W * .125},${-H * .36} v${H * .12}" stroke="${c.bonnet}" stroke-width="5" stroke-linecap="round" opacity=".7"/>`).join('')}
        <circle cx="0" cy="${-H * .78}" r="${H * .09}" fill="${c.bonnet}"/><circle cx="${H * .03}" cy="${-H * .8}" r="${H * .05}" fill="#ffa266"/></g>` : '';
    const chignon = c.chignon ? `<circle cx="0" cy="${-H * .7}" r="${H * .2}" fill="${c.cheveux}"/>
        <path d="M${-W * .52},${-H * .05} C${-W * .6},${-H * .7} ${W * .6},${-H * .7} ${W * .52},${-H * .05} C${W * .45},${-H * .4} ${W * .1},${-H * .5} 0,${-H * .48} C${-W * .1},${-H * .5} ${-W * .45},${-H * .4} ${-W * .52},${-H * .05} Z" fill="${c.cheveux}"/>` : '';
    const lunettes = c.lunettes ? `<g fill="none" stroke="#2b2b30" stroke-width="5"><rect x="${-W * .2 - 32}" y="${-H * .08 - 26}" width="64" height="52" rx="18"/><rect x="${W * .2 - 32}" y="${-H * .08 - 26}" width="64" height="52" rx="18"/><path d="M-12,${-H * .08} h24"/></g>` : '';
    const tete = `
      <g id="${g('tete')}">
        <path d="M-34,${H * .32} L-30,${H * .62} L30,${H * .62} L34,${H * .32} Z" fill="${c.peauOmbre}"/>
        <circle id="${g('oreG')}" cx="${-W / 2}" cy="${H * .02}" r="${H * .1}" fill="${c.peauOmbre}"/>
        <circle id="${g('oreD')}" cx="${W / 2}" cy="${H * .02}" r="${H * .1}" fill="${c.peau}"/>
        ${chignon}
        <path d="${face}" fill="${c.peau}"/>
        <path d="M${W * .3},${-H * .55} C${W * .5},${-H * .4} ${W * .52},${H * .1} ${W * .32},${H * .38} C${W * .44},${H * .05} ${W * .42},${-H * .3} ${W * .3},${-H * .55} Z" fill="${c.peauOmbre}" opacity=".55"/>
        <g id="${g('traits')}">
          <ellipse cx="${-W * .3}" cy="${H * .1}" rx="20" ry="11" fill="#ff8a7a" opacity=".35"/><ellipse cx="${W * .3}" cy="${H * .1}" rx="20" ry="11" fill="#ff8a7a" opacity=".35"/>
          ${barbe}
          ${yeux('G', -W * .2)}${yeux('D', W * .2)}
          ${lunettes}
          <rect id="${g('srcG')}" x="-32" y="-9" width="64" height="18" rx="9" fill="${c.cheveux}" transform="translate(${-W * .2},${-H * .26})"/>
          <rect id="${g('srcD')}" x="-32" y="-9" width="64" height="18" rx="9" fill="${c.cheveux}" transform="translate(${W * .2},${-H * .26})"/>
          <g id="${g('nez')}" transform="translate(0,${H * .05})"><ellipse cx="0" cy="0" rx="${c.barbeOn ? 24 : 15}" ry="${c.barbeOn ? 20 : 14}" fill="${c.peauOmbre}"/><ellipse cx="-6" cy="-5" rx="8" ry="6" fill="#fff" opacity=".3"/></g>
          <g id="${g('bouche')}" transform="translate(0,${H * .24})">${Object.entries(bouches).map(([k, v]) => `<g id="${g('b-' + k)}" style="display:none">${v}</g>`).join('')}</g>
          <g id="${g('sueur')}" opacity="0" transform="translate(${W * .44},${-H * .3})"><path d="M0,-22 C10,-6 13,6 0,10 C-13,6 -10,-6 0,-22 Z" fill="#8fd3ff"/><path d="M-4,-6 C-5,0 -4,4 0,6" stroke="#fff" stroke-width="3" fill="none"/></g>
        </g>
        ${c.chignon ? `<path d="M${-W * .54},${H * .1} C${-W * .66},${-H * .9} ${W * .66},${-H * .9} ${W * .54},${H * .1} C${W * .5},${-H * .2} ${W * .38},${-H * .34} ${W * .12},${-H * .36} C${W * .02},${-H * .28} ${-W * .2},${-H * .3} ${-W * .3},${-H * .34} C${-W * .44},${-H * .26} ${-W * .5},${-H * .12} ${-W * .54},${H * .1} Z" fill="${c.cheveux}"/>
          <path d="M${-W * .2},${-H * .5} C${-W * .02},${-H * .56} ${W * .2},${-H * .52} ${W * .34},${-H * .42}" stroke="#a65a33" stroke-width="7" fill="none" stroke-linecap="round" opacity=".7"/>
          <circle cx="${-W * .53}" cy="${H * .16}" r="7" fill="#e7b955"/><circle cx="${W * .53}" cy="${H * .16}" r="7" fill="#e7b955"/>` : ''}
        ${bonnet}
        ${c.salopette ? `<g id="${g('crayon')}" transform="translate(${W * .5},${-H * .12}) rotate(-28)"><rect x="-6" y="-46" width="12" height="76" rx="3" fill="#ffd23f"/><path d="M-6,30 l6,16 l6,-16 z" fill="#f3d9b0"/><path d="M-2.5,40 l2.5,6 l2.5,-6 z" fill="#333"/><rect x="-6" y="-46" width="12" height="10" fill="#ff8fa3"/></g>` : ''}
      </g>`;
    // ----- Torse (repère : hanches en 0,0 ; épaules à y = -230) -----
    const torse = c.salopette ? `
      <path d="M-98,-226 C-112,-200 -114,-120 -104,-40 C-100,-8 -96,8 -92,22 L92,22 C96,8 100,-8 104,-40 C114,-120 112,-200 98,-226 C60,-246 -60,-246 -98,-226 Z" fill="${c.haut}"/>
      <path d="M60,-236 C96,-222 108,-180 108,-120 C108,-80 100,-40 96,-10 C88,-60 84,-150 60,-236 Z" fill="${c.hautOmbre}"/>
      <path d="M-74,-170 L74,-170 C84,-110 94,-40 100,26 L-100,26 C-94,-40 -84,-110 -74,-170 Z" fill="${c.bas}"/>
      <path d="M50,-170 L74,-170 C84,-110 94,-40 100,26 L70,26 C66,-40 60,-110 50,-170 Z" fill="${c.basOmbre}"/>
      <rect x="-38" y="-150" width="76" height="60" rx="12" fill="${c.basOmbre}"/><path d="M-38,-128 h76" stroke="${c.bouton}" stroke-width="3" stroke-dasharray="6 5" opacity=".7"/>
      <g id="${g('poche')}"></g>
      <path d="M-62,-168 C-70,-200 -76,-222 -84,-236" stroke="${c.bas}" stroke-width="26" stroke-linecap="round" fill="none"/>
      <path d="M62,-168 C70,-200 76,-222 84,-236" stroke="${c.basOmbre}" stroke-width="26" stroke-linecap="round" fill="none"/>
      <circle cx="-60" cy="-160" r="11" fill="${c.bouton}"/><circle cx="60" cy="-160" r="11" fill="${c.bouton}"/><circle cx="-63" cy="-163" r="4" fill="#fff6d6"/><circle cx="57" cy="-163" r="4" fill="#fff6d6"/>
      <path d="M-100,-40 C-40,-28 40,-28 100,-40" stroke="${c.basTrait}" stroke-width="7" fill="none"/>`
      : `
      <path d="M-86,-226 C-104,-200 -108,-120 -98,-40 C-94,-8 -90,8 -86,22 L86,22 C90,8 94,-8 98,-40 C108,-120 104,-200 86,-226 C50,-244 -50,-244 -86,-226 Z" fill="${c.haut}"/>
      <path d="M50,-236 C88,-222 100,-180 100,-120 C100,-80 94,-40 90,-10 C82,-60 76,-150 50,-236 Z" fill="${c.hautOmbre}"/>
      <path d="M-30,-238 L0,-190 L30,-238 Z" fill="#fff8ee"/>
      ${[-160, -110, -60].map(y => `<circle cx="0" cy="${y}" r="6" fill="${c.hautOmbre}"/>`).join('')}
      <path d="M-98,-30 C-40,-20 40,-20 98,-30 L100,26 L-100,26 Z" fill="${c.bas}"/>`;
    return `<defs>
        <clipPath id="${g('clipG')}"><ellipse rx="25" ry="29"/></clipPath><clipPath id="${g('clipD')}"><ellipse rx="25" ry="29"/></clipPath>
      </defs>
      <g id="${g('root')}">
        <ellipse id="${g('ombre')}" cx="0" cy="4" rx="150" ry="22" fill="#000" opacity=".18"/>
        <g id="${g('corps')}">
          <g id="${g('jambes')}"></g>
          <g id="${g('torse')}">${torse}</g>
          <g id="${g('teteP')}">${tete}</g>
          <g id="${g('brasG')}"></g><g id="${g('brasD')}"></g>
        </g>
      </g>`;
  }

  // Tube : contour foncé puis remplissage, passant par le point milieu M (courbe quadratique)
  tube(A, Mi, B, larg, coul, trait) {
    const C = { x: 2 * Mi.x - (A.x + B.x) / 2, y: 2 * Mi.y - (A.y + B.y) / 2 };
    const d = `M${A.x.toFixed(1)},${A.y.toFixed(1)} Q${C.x.toFixed(1)},${C.y.toFixed(1)} ${B.x.toFixed(1)},${B.y.toFixed(1)}`;
    return `<path d="${d}" stroke="${trait}" stroke-width="${larg + 6}" stroke-linecap="round" fill="none"/><path d="${d}" stroke="${coul}" stroke-width="${larg}" stroke-linecap="round" fill="none"/>`;
  }
  // Point sur la courbe quadratique A → B passant par Mi, à la fraction u
  surTube(A, Mi, B, u) {
    const C = { x: 2 * Mi.x - (A.x + B.x) / 2, y: 2 * Mi.y - (A.y + B.y) / 2 };
    const v = 1 - u;
    return { x: v * v * A.x + 2 * v * u * C.x + u * u * B.x, y: v * v * A.y + 2 * v * u * C.y + u * u * B.y };
  }
  // Coude ou genou : position du milieu articulé pour longueurs L1, L2 et sens de pliage
  articulation(A, B, L1, L2, sens) {
    const dx = B.x - A.x, dy = B.y - A.y, D = Math.max(1, Math.min(Math.hypot(dx, dy), L1 + L2 - 1));
    const a = (L1 * L1 - L2 * L2 + D * D) / (2 * D), h = Math.sqrt(Math.max(0, L1 * L1 - a * a));
    const ux = dx / Math.hypot(dx, dy), uy = dy / Math.hypot(dx, dy);
    return { x: A.x + ux * a - uy * h * sens, y: A.y + uy * a + ux * h * sens };
  }

  main(P, angle, forme, cote) {
    const c = this.c;
    const pouce = cote === 'G' ? -1 : 1;
    let s = `<g transform="translate(${P.x.toFixed(1)},${P.y.toFixed(1)}) rotate(${angle.toFixed(1)})">`;
    if (forme === 'pointe') s += `<rect x="-8" y="10" width="16" height="44" rx="8" fill="${c.peau}" stroke="${c.peauTrait}" stroke-width="3"/>`;
    if (forme === 'tel') s += `<g transform="translate(${-4 * pouce},34) rotate(${8 * pouce})"><rect x="-26" y="-52" width="52" height="104" rx="13" fill="#15181e"/><rect x="-21" y="-47" width="42" height="94" rx="9" fill="#3b5c8f"/><circle cx="6" cy="-14" r="13" fill="#f4efe6" opacity="${c.salopette ? .95 : 0}"/></g>`;
    s += `<circle r="27" fill="${c.peauTrait}"/><circle r="24" fill="${c.peau}"/>`;
    if (forme !== 'poing') s += `<ellipse cx="${18 * pouce}" cy="-6" rx="11" ry="14" fill="${c.peau}" stroke="${c.peauTrait}" stroke-width="3"/>`;
    else s += `<path d="M-14,10 h28" stroke="${c.peauTrait}" stroke-width="3" stroke-linecap="round"/>`;
    s += `<g id="${this.g('accessoire' + cote)}"></g></g>`;
    return s;
  }

  poser(p) {
    const c = this.c, g = n => this.g(n);
    p = Object.assign({ x: 0, y: 0, s: 1, sx: 1, sy: 1, rot: 0, bob: 0, pench: 0, tete: 0, teteY: 0, tourne: 0, regardX: 0, regardY: 0, oeil: 1, paup: .12, cligne: 0,
      sourcils: 0, froncer: 0, bouche: 'ferme', sueur: 0, mainG: { x: -118, y: -322 }, mainD: { x: 118, y: -322 }, coudeG: 1, coudeD: -1, formeG: 'ouverte', formeD: 'ouverte',
      angleMainG: null, angleMainD: null, piedG: { x: -60, y: 0 }, piedD: { x: 60, y: 0 }, hanche: -300, assis: false, ombre: .18 }, p);
    tr(g('root'), `translate(${p.x},${p.y}) rotate(${p.rot}) scale(${p.s * p.sx},${p.s * p.sy})`);
    op(g('ombre'), p.ombre);
    // hanches et torse
    const H0 = { x: 0, y: p.hanche + p.bob };
    const rad = p.pench * Math.PI / 180, cs = Math.cos(rad), sn = Math.sin(rad);
    const rotP = (x, y) => ({ x: H0.x + x * cs - y * sn, y: H0.y + x * sn + y * cs });
    tr(g('torse'), `translate(${H0.x},${H0.y}) rotate(${p.pench})`);
    const cou = rotP(0, -236);
    tr(g('teteP'), `translate(${cou.x},${cou.y - c.hauteurTete * .55 + p.teteY}) rotate(${p.tete + p.pench * .5}) translate(0,0)`);
    // jambes
    let jambes = '';
    for (const [cote, sx] of [['G', -1], ['D', 1]]) {
      const hip = rotP(sx * 46, 0);
      const pied = p['pied' + cote];
      const L = 148;
      const genou = this.articulation(hip, pied, L, L, (p.genoux ?? 1) * (sx > 0 ? 1 : -1));
      jambes += this.tube(hip, genou, pied, 58, c.bas, c.basTrait);
      const dir = sx;
      jambes += `<g transform="translate(${pied.x},${pied.y})"><path d="M-30,-22 C-32,-4 -30,4 -26,8 L${dir * 54},8 C${dir * 60},-4 ${dir * 44},-24 ${dir * 20},-26 Z" fill="${c.chaussure}"/><rect x="${dir > 0 ? -30 : -58}" y="4" width="88" height="11" rx="5" fill="${c.semelle}"/><ellipse cx="${dir * 24}" cy="-12" rx="12" ry="6" fill="#fff" opacity=".18"/></g>`;
    }
    $(g('jambes')).innerHTML = jambes;
    // bras
    for (const [cote, sx] of [['G', -1], ['D', 1]]) {
      const S = rotP(sx * 86, -206);
      const M = p['main' + cote];
      const E = this.articulation(S, M, 128, 122, p['coude' + cote]);
      const fin = this.surTube(S, E, M, .27);
      const milieu = this.surTube(S, E, M, .14);
      let s = this.tube(S, E, M, 40, c.peau, c.peauTrait);
      s += this.tube(S, milieu, fin, 50, c.haut, c.hautOmbre);
      const av = this.surTube(S, E, M, .9);
      const angle = p['angleMain' + cote] ?? (Math.atan2(M.y - av.y, M.x - av.x) * 180 / Math.PI - 90);
      s += this.main(M, angle, p['forme' + cote], cote);
      $(g('bras' + cote)).innerHTML = s;
    }
    // visage
    const W = c.largeurTete, H = c.hauteurTete, t = p.tourne;
    tr(g('traits'), `translate(${t * 22},0)`);
    tr(g('oreG'), `translate(${t > 0 ? t * 6 : t * 2},0) scale(${1 - Math.max(0, -t) * .7},1)`);
    tr(g('oreD'), `translate(${t < 0 ? t * 6 : t * 2},0) scale(${1 - Math.max(0, t) * .7},1)`);
    const ferme = clamp(Math.max(p.cligne, p.paup));
    for (const [cote, sx] of [['G', -1], ['D', 1]]) {
      tr(g('oeil' + cote), `translate(${sx * W * .2},${-H * .08}) scale(${p.oeil})`);
      tr(g('pup' + cote), `translate(${p.regardX * 11},${p.regardY * 13})`);
      tr(g('paup' + cote), `translate(0,${ferme * 58})`);
      tr(g('paupB' + cote), `translate(0,${-Math.max(0, p.cligne - .5) * 20})`);
      const ang = -sx * p.froncer * 16;
      tr(g('src' + cote), `translate(${sx * W * .2},${-H * .26 - p.sourcils * 16 - (p.oeil - 1) * 30}) rotate(${ang})`);
    }
    for (const b of ['ferme', 'sourire', 'rire', 'o', 'a', 'e', 'grimace', 'cri', 'baille', 'moue', 'malin']) vis(g('b-' + b), p.bouche === b);
    op(g('sueur'), p.sueur);
  }
}

// Clignement naturel
function clignement(t, graine = 0) {
  const periode = 2.9 + (graine % 3) * .8;
  const u = ((t + graine * 1.37) % periode) / periode, d = .05;
  return u < d ? Math.sin((u / d) * Math.PI) : 0;
}
// Formes de bouche pendant la parole (au téléphone)
function parler(t, vitesse = 11) {
  return ['a', 'e', 'o', 'ferme', 'a', 'e', 'ferme', 'o'][Math.floor(t * vitesse) % 8];
}

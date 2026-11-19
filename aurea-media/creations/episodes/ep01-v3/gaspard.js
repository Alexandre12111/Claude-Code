// Gaspard, le castor bâtisseur : mascotte de la série. Repère : pieds au sol en (0, 0), y vers le bas, hauteur ≈ 640.
// Construction : une seule masse tête et corps (haricot), museau large, deux grandes incisives, casque de chantier,
// queue plate en pagaie, ceinture à outils. Bras et jambes en tubes souples. Ombrage « cel » clippé sur le corps,
// liseré de lumière, ombre portée. Paramètres d'animation : voir poser().
const G_COUL = {
  poil: '#a8643a', poilOmbre: '#7f4524', poilClair: '#c98552', ventre: '#f6d9ae', ventreOmbre: '#e7bf8c',
  museau: '#fbe6c6', nez: '#3a2016', dents: '#fffaf0', dentsTrait: '#d9cbb2', oeil: '#ffffff', pupille: '#1a120d',
  casque: '#ffc21a', casqueOmbre: '#e89b00', casqueClair: '#ffe27a', queue: '#5e3420', queueTrait: '#7d4a2c',
  pied: '#6b3b20', ceinture: '#3a2a22', boucle: '#e8b84a', trait: '#4a2615', joue: '#ff8a6b',
};

class Gaspard {
  constructor(id) { this.id = id; }
  g(n) { return `${this.id}-${n}`; }

  // Silhouette du corps (haricot) : largeur w, hauteur h, base au sol. Renvoie un chemin.
  corpsChemin(w = 330, h = 470) {
    const W = w / 2;
    return `M0,${-h} C${W * .92},${-h} ${W * 1.02},${-h * .72} ${W * 1.02},${-h * .52} C${W * 1.04},${-h * .3} ${W * 1.12},${-h * .08} ${W * .96},${-h * .02}
      C${W * .7},${h * .02} ${-W * .7},${h * .02} ${-W * .96},${-h * .02} C${-W * 1.12},${-h * .08} ${-W * 1.04},${-h * .3} ${-W * 1.02},${-h * .52} C${-W * 1.02},${-h * .72} ${-W * .92},${-h} 0,${-h} Z`;
  }

  svg() {
    const c = G_COUL, g = n => this.g(n);
    const corps = this.corpsChemin();
    const oeil = s => `<g id="${g('oeil' + s)}">
        <ellipse rx="40" ry="48" fill="${c.oeil}"/>
        <g clip-path="url(#${g('clipOeil')})">
          <g id="${g('pup' + s)}"><ellipse rx="19" ry="22" fill="${c.pupille}"/><circle cx="7" cy="-9" r="7.5" fill="#fff"/><circle cx="-6" cy="8" r="3" fill="#fff" opacity=".7"/></g>
          <g id="${g('paupH' + s)}"><rect x="-50" y="-110" width="100" height="62" fill="${c.poil}"/><path d="M-44,-48 C-20,-40 20,-40 44,-48" stroke="${c.trait}" stroke-width="6" stroke-linecap="round" fill="none"/></g>
          <g id="${g('paupB' + s)}"><rect x="-50" y="44" width="100" height="60" fill="${c.poil}"/></g>
        </g>
        <ellipse rx="40" ry="48" fill="none" stroke="${c.trait}" stroke-width="4" opacity=".25"/>
      </g>`;
    const bouches = {
      sourire: `<path d="M-46,0 C-30,30 30,30 46,0" stroke="${c.trait}" stroke-width="7" stroke-linecap="round" fill="none"/>`,
      ouvert: `<path d="M-44,-4 C-40,52 40,52 44,-4 Z" fill="#3a1610"/><ellipse cx="0" cy="34" rx="22" ry="10" fill="#ff7a6b"/>`,
      rire: `<path d="M-54,-6 C-48,66 48,66 54,-6 Z" fill="#3a1610"/><ellipse cx="0" cy="44" rx="28" ry="13" fill="#ff7a6b"/>`,
      o: `<ellipse cx="0" cy="18" rx="20" ry="26" fill="#3a1610"/>`,
      cri: `<path d="M-40,-4 C-56,90 56,90 40,-4 Z" fill="#3a1610"/><ellipse cx="0" cy="62" rx="22" ry="12" fill="#ff7a6b"/>`,
      moue: `<path d="M-34,22 C-14,6 14,6 34,22" stroke="${c.trait}" stroke-width="7" stroke-linecap="round" fill="none"/>`,
      plat: `<path d="M-30,14 H30" stroke="${c.trait}" stroke-width="7" stroke-linecap="round"/>`,
      grimace: `<path d="M-50,8 C-30,-6 30,-6 50,8 C30,22 -30,22 -50,8 Z" fill="#3a1610"/>`,
      baille: `<ellipse cx="0" cy="30" rx="34" ry="44" fill="#3a1610"/><ellipse cx="0" cy="56" rx="20" ry="11" fill="#ff7a6b"/>`,
    };
    return `<defs>
        <clipPath id="${g('clipCorps')}"><path d="${corps}"/></clipPath>
        <clipPath id="${g('clipOeil')}"><ellipse rx="40" ry="48"/></clipPath>
      </defs>
      <g id="${g('root')}">
        <ellipse id="${g('ombre')}" cx="0" cy="4" rx="210" ry="30" fill="#2a1a10" opacity=".22"/>
        <g id="${g('queue')}"><g id="${g('queueIn')}">
          <path d="M0,0 C60,-30 230,-60 300,-10 C340,20 320,90 250,96 C160,104 40,60 0,30 Z" fill="${c.queue}"/>
          ${[0, 1, 2, 3].map(i => `<path d="M${90 + i * 50},${-24 + i * 4} C${100 + i * 50},${20} ${96 + i * 50},${60} ${80 + i * 50},${88 - i * 4}" stroke="${c.queueTrait}" stroke-width="7" fill="none" stroke-linecap="round"/>`).join('')}
          <path d="M120,0 C180,-6 250,10 300,30" stroke="${c.queueTrait}" stroke-width="7" fill="none" stroke-linecap="round"/>
          <path d="M70,-14 C140,-40 230,-44 290,-8" stroke="#8a5536" stroke-width="6" fill="none" opacity=".7" stroke-linecap="round"/>
        </g></g>
        <g id="${g('jambes')}"></g>
        <g id="${g('brasArr')}"></g>
        <g id="${g('corps')}">
          <path d="${corps}" fill="${c.poil}"/>
          <g clip-path="url(#${g('clipCorps')})">
            <ellipse id="${g('ventre')}" cx="0" cy="-150" rx="122" ry="150" fill="${c.ventre}"/>
            <path d="M120,-470 C230,-380 220,-80 120,40 L260,40 L260,-470 Z" fill="${c.poilOmbre}" opacity=".55"/>
            <ellipse cx="96" cy="-150" rx="60" ry="150" fill="${c.ventreOmbre}" opacity=".55"/>
            <path d="M-150,-420 C-170,-330 -168,-250 -150,-190" stroke="${c.poilClair}" stroke-width="16" fill="none" stroke-linecap="round" opacity=".55"/>
            <g id="${g('ceinture')}"><rect x="-200" y="-112" width="400" height="34" fill="${c.ceinture}"/><rect x="-26" y="-118" width="52" height="46" rx="10" fill="${c.boucle}"/><rect x="-14" y="-106" width="28" height="22" rx="5" fill="${c.ceinture}"/>
              <g transform="translate(100,-100)"><rect x="-24" y="0" width="48" height="66" rx="8" fill="#5a4436"/><g id="${g('telCeinture')}"><rect x="-17" y="-26" width="34" height="58" rx="8" fill="#15181e"/><rect x="-12" y="-21" width="24" height="40" rx="5" fill="#3b5c8f"/></g></g>
              <g transform="translate(-110,-98) rotate(-12)"><rect x="-8" y="-40" width="16" height="70" rx="4" fill="#ffd23f"/><path d="M-8,30 l8,16 l8,-16 z" fill="#f3d9b0"/></g></g>
          </g>
          <g id="${g('visage')}" transform="translate(0,-330)">
            <ellipse cx="-92" cy="34" rx="30" ry="18" fill="${c.joue}" opacity=".35"/><ellipse cx="92" cy="34" rx="30" ry="18" fill="${c.joue}" opacity=".35"/>
            <g id="${g('oeilPosG')}" transform="translate(-50,-40)">${oeil('G')}</g>
            <g id="${g('oeilPosD')}" transform="translate(50,-40)">${oeil('D')}</g>
            <path id="${g('srcG')}" d="M-34,-10 C-18,-22 18,-22 34,-12 L30,4 C14,-4 -14,-4 -30,6 Z" fill="${c.trait}"/>
            <path id="${g('srcD')}" d="M-34,-12 C-18,-22 18,-22 34,-10 L30,6 C14,-4 -14,-4 -30,4 Z" fill="${c.trait}"/>
            <g id="${g('museau')}">
              <ellipse cx="0" cy="56" rx="96" ry="62" fill="${c.museau}"/>
              <ellipse cx="18" cy="70" rx="60" ry="40" fill="${c.ventreOmbre}" opacity=".35"/>
              <g id="${g('bouche')}" transform="translate(0,62)">${Object.entries(bouches).map(([k, v]) => `<g id="${g('b-' + k)}" style="display:none">${v}</g>`).join('')}</g>
              <g id="${g('dents')}"><rect x="-30" y="58" width="29" height="52" rx="9" fill="${c.dents}" stroke="${c.dentsTrait}" stroke-width="4"/><rect x="1" y="58" width="29" height="52" rx="9" fill="${c.dents}" stroke="${c.dentsTrait}" stroke-width="4"/></g>
              <path d="M-36,20 C-36,-4 36,-4 36,20 C36,38 14,46 0,46 C-14,46 -36,38 -36,20 Z" fill="${c.nez}"/><ellipse cx="-12" cy="12" rx="12" ry="7" fill="#fff" opacity=".35"/>
              ${[-1, 1].map(s => [0, 1, 2].map(k => `<path d="M${s * 70},${44 + k * 14} l${s * 46},${-10 + k * 10}" stroke="${c.trait}" stroke-width="3.5" stroke-linecap="round" opacity=".5"/>`).join('')).join('')}
            </g>
            <g id="${g('sueur')}" opacity="0" transform="translate(150,-90)"><path d="M0,-30 C14,-8 18,8 0,14 C-18,8 -14,-8 0,-30 Z" fill="#8fd3ff"/><path d="M-5,-8 C-6,0 -5,6 0,8" stroke="#fff" stroke-width="4" fill="none"/></g>
          </g>
          <g id="${g('oreilles')}"><circle cx="-128" cy="-440" r="34" fill="${c.poil}"/><circle cx="-128" cy="-440" r="17" fill="${c.poilOmbre}"/><circle cx="128" cy="-440" r="34" fill="${c.poil}"/><circle cx="128" cy="-440" r="17" fill="${c.poilOmbre}"/></g>
          <g id="${g('casque')}"><g id="${g('casqueIn')}">
            <path d="M-150,-470 C-150,-610 150,-610 150,-470 Z" fill="${c.casque}"/>
            <path d="M40,-598 C110,-580 150,-530 150,-470 L96,-470 C96,-520 76,-566 40,-598 Z" fill="${c.casqueOmbre}"/>
            <path d="M-110,-520 C-96,-562 -60,-586 -20,-592" stroke="${c.casqueClair}" stroke-width="14" fill="none" stroke-linecap="round"/>
            <rect x="-22" y="-610" width="44" height="140" rx="16" fill="${c.casqueClair}"/>
            <path d="M-196,-476 C-120,-500 120,-500 196,-476 C200,-456 186,-446 170,-448 C90,-462 -90,-462 -170,-448 C-186,-446 -200,-456 -196,-476 Z" fill="${c.casqueOmbre}"/>
            <g transform="translate(-74,-520)"><circle r="22" fill="#15181e"/><text y="9" text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="26" fill="${c.casque}">A</text></g>
          </g></g>
        </g>
        <g id="${g('brasAv')}"></g>
        <g id="${g('fx')}"></g>
      </g>`;
  }

  tube(A, Mi, B, larg, coul, trait) {
    const C = { x: 2 * Mi.x - (A.x + B.x) / 2, y: 2 * Mi.y - (A.y + B.y) / 2 };
    const d = `M${A.x.toFixed(1)},${A.y.toFixed(1)} Q${C.x.toFixed(1)},${C.y.toFixed(1)} ${B.x.toFixed(1)},${B.y.toFixed(1)}`;
    return `<path d="${d}" stroke="${trait}" stroke-width="${larg + 8}" stroke-linecap="round" fill="none"/><path d="${d}" stroke="${coul}" stroke-width="${larg}" stroke-linecap="round" fill="none"/>`;
  }
  surTube(A, Mi, B, u) { const C = { x: 2 * Mi.x - (A.x + B.x) / 2, y: 2 * Mi.y - (A.y + B.y) / 2 }, v = 1 - u; return { x: v * v * A.x + 2 * v * u * C.x + u * u * B.x, y: v * v * A.y + 2 * v * u * C.y + u * u * B.y }; }
  articulation(A, B, L1, L2, sens) {
    const dx = B.x - A.x, dy = B.y - A.y, n = Math.hypot(dx, dy) || 1, D = Math.max(1, Math.min(n, L1 + L2 - 1));
    const a = (L1 * L1 - L2 * L2 + D * D) / (2 * D), h = Math.sqrt(Math.max(0, L1 * L1 - a * a));
    return { x: A.x + dx / n * a - dy / n * h * sens, y: A.y + dy / n * a + dx / n * h * sens };
  }
  // Main de cartoon à quatre doigts : formes 'ouverte', 'poing', 'pouce', 'tient', 'pointe', 'salut'
  main(P, ang, forme, cote, objet) {
    const c = G_COUL, s = cote === 'G' ? -1 : 1;
    let h = `<g transform="translate(${P.x.toFixed(1)},${P.y.toFixed(1)}) rotate(${ang.toFixed(1)})">`;
    if (objet === 'tel') h += `<g transform="translate(${-6 * s},30) rotate(${10 * s})"><rect x="-30" y="-58" width="60" height="116" rx="15" fill="#15181e"/><rect x="-24" y="-52" width="48" height="104" rx="10" fill="#3b5c8f"/><rect x="-24" y="-52" width="48" height="40" rx="10" fill="#6d8fc4" opacity=".6"/></g>`;
    if (objet === 'tasse') h += `<g transform="translate(0,40)"><path d="M-34,-30 L34,-30 L30,40 C28,50 -28,50 -30,40 Z" fill="#fff" stroke="#3a2a22" stroke-width="6"/><path d="M34,-14 C60,-14 60,22 30,22" stroke="#3a2a22" stroke-width="6" fill="none"/><ellipse cx="0" cy="-28" rx="34" ry="8" fill="#6b3b20"/></g>`;
    if (objet === 'truelle') h += `<g transform="translate(0,18)"><rect x="-8" y="0" width="16" height="44" rx="6" fill="#8a5432"/><path d="M-50,44 L50,44 L0,130 Z" fill="#cfd6de" stroke="#8a96a5" stroke-width="4" stroke-linejoin="round"/></g>`;
    const doigts = forme === 'poing' ? '' : forme === 'pointe' ? `<rect x="-9" y="12" width="18" height="50" rx="9" fill="${c.poil}" stroke="${c.trait}" stroke-width="4"/>`
      : [-18, -6, 6, 18].map((x, i) => `<ellipse cx="${x}" cy="${26 + (i === 0 || i === 3 ? -4 : 0)}" rx="10" ry="${forme === 'salut' ? 22 : 16}" fill="${c.poil}" stroke="${c.trait}" stroke-width="4"/>`).join('');
    h += doigts + `<circle r="30" fill="${c.trait}"/><circle r="26" fill="${c.poil}"/>`;
    if (forme === 'pouce') h += `<rect x="-9" y="-62" width="18" height="44" rx="9" fill="${c.poil}" stroke="${c.trait}" stroke-width="4" transform="rotate(${-10 * s})"/>`;
    else if (forme !== 'poing') h += `<ellipse cx="${24 * s}" cy="-8" rx="11" ry="15" fill="${c.poil}" stroke="${c.trait}" stroke-width="4"/>`;
    else h += `<path d="M-16,8 h32 M-14,-6 h28" stroke="${c.trait}" stroke-width="4" stroke-linecap="round"/>`;
    return h + `</g>`;
  }

  // p : x, y, s, sx, sy (écrasement), rot, pench (inclinaison du haut du corps), regardX/Y, oeil (taille), paup (0..1), cligne,
  //     sourcils (-1..1.5), froncer (-1 colère … 1 inquiet), bouche, dents (0..1 visibles), sueur, queue (angle), oreilles (décalage),
  //     casque (décalage vertical, follow-through), mainG/mainD {x,y}, coudeG/coudeD (±1), formeG/formeD, objetG/objetD,
  //     piedG/piedD {x,y}, lumiere (0..1 liseré), ombre
  poser(p) {
    const c = G_COUL, g = n => this.g(n);
    p = Object.assign({ x: 0, y: 0, s: 1, sx: 1, sy: 1, rot: 0, pench: 0, regardX: 0, regardY: 0, oeil: 1, paup: .1, cligne: 0, sourcils: 0, froncer: 0,
      bouche: 'sourire', dents: 1, sueur: 0, queue: 0, casque: 0, mainG: { x: -230, y: -170 }, mainD: { x: 230, y: -170 }, coudeG: -1, coudeD: 1,
      formeG: 'ouverte', formeD: 'ouverte', objetG: null, objetD: null, piedG: { x: -80, y: 0 }, piedD: { x: 80, y: 0 }, ombre: .22, telCeinture: true, visageX: 0 }, p);
    tr(g('root'), `translate(${p.x},${p.y}) rotate(${p.rot}) scale(${p.s * p.sx},${p.s * p.sy})`);
    op(g('ombre'), p.ombre); tr(g('ombre'), `scale(${1 / p.sx * (p.y < -1 ? .8 : 1)},1)`);
    // le haut du corps s'incline (déformation douce par cisaillement)
    tr(g('corps'), `skewX(${-p.pench * .6})`);
    tr(g('visage'), `translate(${p.visageX + p.pench * 2.2},-330)`);
    tr(g('casque'), `translate(${p.pench * 3.6},${p.casque})`);
    tr(g('oreilles'), `translate(${p.pench * 3.4},${p.casque * .5})`);
    tr(g('queueIn'), `rotate(${p.queue})`);
    tr(g('queue'), `translate(90,-40)`);
    vis(g('telCeinture'), p.telCeinture);
    // yeux
    const ferme = clamp(Math.max(p.cligne, p.paup));
    for (const s of ['G', 'D']) {
      tr(g('oeil' + s), `scale(${p.oeil})`);
      tr(g('pup' + s), `translate(${p.regardX * 16},${p.regardY * 18})`);
      tr(g('paupH' + s), `translate(0,${ferme * 98})`);
      tr(g('paupB' + s), `translate(0,${-Math.max(0, p.cligne - .5) * 40})`);
    }
    const sx = 50, sy = -40 - 60 - p.sourcils * 22 - (p.oeil - 1) * 50;
    tr(g('srcG'), `translate(${-sx},${sy}) rotate(${p.froncer * 14})`);
    tr(g('srcD'), `translate(${sx},${sy}) rotate(${-p.froncer * 14})`);
    for (const b of ['sourire', 'ouvert', 'rire', 'o', 'cri', 'moue', 'plat', 'grimace', 'baille']) vis(g('b-' + b), p.bouche === b);
    const dentY = { ouvert: 0, rire: -4, cri: -6, o: 0, baille: -6 }[p.bouche] ?? 0;
    tr(g('dents'), `translate(0,${dentY}) scale(1,${p.dents})`);
    op(g('sueur'), p.sueur);
    // jambes (courtes, gros pieds plats)
    let j = '';
    for (const [cote, s] of [['G', -1], ['D', 1]]) {
      const H = { x: s * 80 + p.pench * -.6, y: -60 }, F = p['pied' + cote];
      const K = this.articulation(H, F, 40, 40, s);
      j += this.tube(H, K, F, 54, c.poil, c.trait);
      j += `<g transform="translate(${F.x},${F.y})"><path d="M${-s * 20},-16 C${s * 10},-26 ${s * 70},-20 ${s * 84},-2 C${s * 88},10 ${s * 70},14 ${s * 40},14 L${-s * 26},14 C${-s * 40},14 ${-s * 40},-8 ${-s * 20},-16 Z" fill="${c.pied}"/>${[0, 1, 2].map(k => `<path d="M${s * (30 + k * 18)},2 v12" stroke="${c.trait}" stroke-width="4" stroke-linecap="round" opacity=".5"/>`).join('')}</g>`;
    }
    $(g('jambes')).innerHTML = j;
    // bras (tubes) : épaules sur les flancs du haricot
    let av = '', arr = '';
    for (const [cote, s] of [['G', -1], ['D', 1]]) {
      const S = { x: s * 150 + p.pench * 2.2, y: -250 };
      const Mn = p['main' + cote];
      const E = this.articulation(S, Mn, 100, 96, p['coude' + cote] * s);
      let b = this.tube(S, E, Mn, 46, c.poil, c.trait);
      const pr = this.surTube(S, E, Mn, .88);
      const ang = Math.atan2(Mn.y - pr.y, Mn.x - pr.x) * 180 / Math.PI - 90;
      b += this.main(Mn, ang, p['forme' + cote], cote, p['objet' + cote]);
      if (p['derriere' + cote]) arr += b; else av += b;
    }
    $(g('brasAv')).innerHTML = av; $(g('brasArr')).innerHTML = arr;
  }
}

function clignementG(t, graine = 0) {
  const periode = 2.6 + (graine % 3) * .7, u = ((t + graine * 1.37) % periode) / periode, d = .055;
  return u < d ? Math.sin((u / d) * Math.PI) : 0;
}

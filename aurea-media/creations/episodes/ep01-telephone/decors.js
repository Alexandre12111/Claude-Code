// Décors, objets et interfaces de l'épisode (SVG), dessinés pour un cadre vertical 1080 x 1920.
const SVGNS = 'http://www.w3.org/2000/svg';
function el(parent, html) { const g = document.createElementNS(SVGNS, 'g'); g.innerHTML = html; parent.appendChild(g); return g; }
const rnd = (() => { let s = 7; return () => (s = (s * 16807) % 2147483647) / 2147483647; })();

const DEFS = `
<linearGradient id="ecranLueur" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#9cc0ff"/><stop offset="1" stop-color="#2a3a55"/></linearGradient>
<linearGradient id="ciel" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7fb2e0"/><stop offset=".55" stop-color="#bcd7ec"/><stop offset="1" stop-color="#f3e2c4"/></linearGradient>
<linearGradient id="pierre" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#efe3cc"/><stop offset="1" stop-color="#d8c7a7"/></linearGradient>
<linearGradient id="pierreOmbre" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#000" stop-opacity=".0"/><stop offset="1" stop-color="#000" stop-opacity=".18"/></linearGradient>
<linearGradient id="vitre" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#9fb8cc"/><stop offset=".5" stop-color="#5f7890"/><stop offset="1" stop-color="#43566a"/></linearGradient>
<linearGradient id="tube" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#9aa3ad"/><stop offset=".35" stop-color="#e6ebef"/><stop offset="1" stop-color="#717b86"/></linearGradient>
<linearGradient id="tubeH" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#9aa3ad"/><stop offset=".35" stop-color="#e6ebef"/><stop offset="1" stop-color="#717b86"/></linearGradient>
<linearGradient id="bois" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#d9a868"/><stop offset="1" stop-color="#b07a3e"/></linearGradient>
<linearGradient id="boisDessus" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#c89255"/><stop offset=".5" stop-color="#ddb07a"/><stop offset="1" stop-color="#c08a4c"/></linearGradient>
<linearGradient id="tel" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2b2f38"/><stop offset="1" stop-color="#0d0f13"/></linearGradient>
<linearGradient id="ecranAppel" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2d3a4f"/><stop offset="1" stop-color="#11161f"/></linearGradient>
<linearGradient id="or" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f3d68c"/><stop offset=".5" stop-color="#d9b062"/><stop offset="1" stop-color="#a87c32"/></linearGradient>
<linearGradient id="nuit" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0d1426"/><stop offset="1" stop-color="#1f2b46"/></linearGradient>
<linearGradient id="carrelage" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e9eef0"/><stop offset="1" stop-color="#cfd8dc"/></linearGradient>
<radialGradient id="lampe" cx=".5" cy="0" r="1"><stop offset="0" stop-color="#ffd690" stop-opacity=".85"/><stop offset=".45" stop-color="#ffb760" stop-opacity=".25"/><stop offset="1" stop-color="#ffb760" stop-opacity="0"/></radialGradient>
<radialGradient id="halo" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#fff3d1" stop-opacity=".9"/><stop offset="1" stop-color="#fff3d1" stop-opacity="0"/></radialGradient>
<radialGradient id="soleil" cx=".8" cy=".1" r=".9"><stop offset="0" stop-color="#fff2cf" stop-opacity=".75"/><stop offset=".5" stop-color="#ffe2a6" stop-opacity=".15"/><stop offset="1" stop-color="#ffe2a6" stop-opacity="0"/></radialGradient>
<filter id="flou2" x="-10%" y="-10%" width="120%" height="120%"><feGaussianBlur stdDeviation="2"/></filter>
<filter id="flou6" x="-10%" y="-10%" width="120%" height="120%"><feGaussianBlur stdDeviation="6"/></filter>
<filter id="flou12" x="-10%" y="-10%" width="120%" height="120%"><feGaussianBlur stdDeviation="12"/></filter>
<filter id="ombreDouce" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur in="SourceAlpha" stdDeviation="14"/><feOffset dy="18"/><feComponentTransfer><feFuncA type="linear" slope=".35"/></feComponentTransfer><feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge></filter>
<filter id="ombreCarte" x="-30%" y="-30%" width="160%" height="160%"><feGaussianBlur in="SourceAlpha" stdDeviation="10"/><feOffset dy="10"/><feComponentTransfer><feFuncA type="linear" slope=".28"/></feComponentTransfer><feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge></filter>
<filter id="flouV" x="-5%" y="-30%" width="110%" height="160%"><feGaussianBlur id="flouVdev" stdDeviation="0 0"/></filter>
<filter id="desature"><feColorMatrix id="desatM" type="saturate" values="1"/></filter>
`;

// ---------- Façade haussmannienne ----------
function facade(w, h, opts = {}) {
  const fen = [];
  const cols = opts.cols || 4, rows = opts.rows || 5, mx = 90, my = 120;
  const fw = (w - mx * 2) / cols;
  for (let r = 0; r < rows; r++) for (let c = 0; c < cols; c++) {
    const x = mx + c * fw + fw * .2, y = my + r * 360, ww = fw * .6, hh = 230;
    fen.push(`<g transform="translate(${x},${y})">
      <rect x="-14" y="-18" width="${ww + 28}" height="${hh + 34}" fill="#e4d4b6"/>
      <path d="M-14,-18 h${ww + 28} l-8,-14 h${-(ww + 12)} z" fill="#d6c3a0"/>
      <rect width="${ww}" height="${hh}" fill="url(#vitre)"/>
      <path d="M${ww / 2},0 v${hh} M0,${hh * .42} h${ww}" stroke="#efe5d2" stroke-width="7"/>
      <path d="M8,10 L${ww * .3},10 L8,${hh * .35} Z" fill="#fff" opacity=".18"/>
      <rect x="-24" y="${hh - 6}" width="${ww + 48}" height="14" fill="#cdb890"/>
      <g stroke="#2b2b2e" stroke-width="3" fill="none">
        <path d="M-20,${hh + 8} h${ww + 40} M-20,${hh + 66} h${ww + 40}"/>
        ${Array.from({ length: 9 }, (_, i) => `<path d="M${-14 + i * (ww + 28) / 8},${hh + 8} v58"/>`).join('')}
        <path d="M${ww / 2 - 26},${hh + 37} c10,-22 42,-22 52,0 c-10,22 -42,22 -52,0" />
      </g>
    </g>`);
  }
  const joints = Array.from({ length: Math.ceil(h / 60) }, (_, i) => `<path d="M0,${i * 60} H${w}" stroke="#cbb895" stroke-width="2" opacity=".55"/>`).join('');
  return `<rect width="${w}" height="${h}" fill="url(#pierre)"/>${joints}
    ${Array.from({ length: rows }, (_, r) => `<rect x="0" y="${my + r * 360 + 300}" width="${w}" height="22" fill="#d9c7a3"/><rect x="0" y="${my + r * 360 + 318}" width="${w}" height="8" fill="#000" opacity=".08"/>`).join('')}
    ${fen.join('')}<rect width="${w}" height="${h}" fill="url(#pierreOmbre)"/>`;
}

// ---------- Échafaudage (vu de face) ----------
function echafaudage(w, niveaux, pas, opts = {}) {
  const montants = opts.montants || [60, w - 60];
  let s = '';
  for (let n = 0; n < niveaux; n++) {
    const y = n * pas;
    s += `<rect x="${montants[0] - 30}" y="${y - 22}" width="${montants[1] - montants[0] + 60}" height="26" fill="url(#bois)"/>
          <rect x="${montants[0] - 30}" y="${y - 22}" width="${montants[1] - montants[0] + 60}" height="6" fill="#e9c08a"/>
          <rect x="${montants[0] - 30}" y="${y + 4}" width="${montants[1] - montants[0] + 60}" height="8" fill="#000" opacity=".18"/>
          ${Array.from({ length: 6 }, (_, i) => `<path d="M${montants[0] - 30 + i * (montants[1] - montants[0] + 60) / 6},${y - 22} v26" stroke="#9c6a35" stroke-width="2" opacity=".5"/>`).join('')}
          <rect x="${montants[0]}" y="${y - 140}" width="${montants[1] - montants[0]}" height="10" rx="5" fill="url(#tubeH)"/>
          <rect x="${montants[0]}" y="${y - 72}" width="${montants[1] - montants[0]}" height="10" rx="5" fill="url(#tubeH)"/>
          <rect x="${montants[0] - 30}" y="${y - 50}" width="${montants[1] - montants[0] + 60}" height="28" fill="#e9772e" opacity=".9"/>
          <rect x="${montants[0] - 30}" y="${y - 50}" width="${montants[1] - montants[0] + 60}" height="6" fill="#ffa25e" opacity=".8"/>`;
  }
  for (const x of montants) s += `<rect x="${x - 8}" y="${-pas * .3}" width="16" height="${niveaux * pas + 400}" rx="6" fill="url(#tube)"/>`;
  for (let n = 0; n < niveaux; n++) for (const x of montants) s += `<rect x="${x - 13}" y="${n * pas - 152}" width="26" height="22" rx="4" fill="#59616b"/><rect x="${x - 13}" y="${n * pas - 84}" width="26" height="22" rx="4" fill="#59616b"/>`;
  return s;
}

// ---------- Smartphone à plat ou tenu ----------
function telephone(id, w, h, ecran) {
  return `<g id="${id}">
    <rect x="${-w / 2 - 6}" y="${-h / 2 - 6}" width="${w + 12}" height="${h + 12}" rx="${w * .17}" fill="#0a0b0e"/>
    <rect x="${-w / 2}" y="${-h / 2}" width="${w}" height="${h}" rx="${w * .15}" fill="url(#tel)"/>
    <rect x="${-w / 2 + w * .045}" y="${-h / 2 + w * .045}" width="${w * .91}" height="${h - w * .09}" rx="${w * .12}" fill="#000"/>
    <g id="${id}-ecran" clip-path="url(#${id}-clip)">${ecran}</g>
    <clipPath id="${id}-clip"><rect x="${-w / 2 + w * .045}" y="${-h / 2 + w * .045}" width="${w * .91}" height="${h - w * .09}" rx="${w * .12}"/></clipPath>
    <rect x="${-w * .12}" y="${-h / 2 + w * .08}" width="${w * .24}" height="${w * .07}" rx="${w * .035}" fill="#000"/>
    <path d="M${-w / 2 + w * .08},${-h / 2 + w * .1} L${-w / 2 + w * .4},${-h / 2 + w * .1} L${-w / 2 + w * .08},${-h / 2 + h * .35} Z" fill="#fff" opacity=".05"/>
  </g>`;
}

// Écran « appel entrant »
function ecranAppel(w, h, nom, sous) {
  return `<rect x="${-w / 2}" y="${-h / 2}" width="${w}" height="${h}" fill="url(#ecranAppel)"/>
    <circle cx="0" cy="${-h * .25}" r="${w * .17}" fill="#55657e"/><circle cx="0" cy="${-h * .28}" r="${w * .06}" fill="#9aa8bd"/><path d="M${-w * .1},${-h * .19} c0,-${w * .09} ${w * .2},-${w * .09} ${w * .2},0" fill="#9aa8bd"/>
    <text x="0" y="${-h * .06}" text-anchor="middle" font-family="Geist" font-size="${w * .085}" fill="#fff" font-weight="600">${nom}</text>
    <text x="0" y="${-h * .015}" text-anchor="middle" font-family="Geist" font-size="${w * .05}" fill="#b9c3d3">${sous}</text>
    <g transform="translate(${-w * .24},${h * .3})"><circle r="${w * .1}" fill="#e5484d"/><path d="M-${w * .045},2 c${w * .03},-${w * .03} ${w * .06},-${w * .03} ${w * .09},0" stroke="#fff" stroke-width="${w * .018}" fill="none" stroke-linecap="round"/></g>
    <g transform="translate(${w * .24},${h * .3})"><circle r="${w * .1}" fill="#30c46b"/><path d="M-${w * .045},-2 c${w * .03},${w * .03} ${w * .06},${w * .03} ${w * .09},0" stroke="#fff" stroke-width="${w * .018}" fill="none" stroke-linecap="round"/></g>`;
}

// Bulle de question (style message)
function bulle(id, texte, larg, cote = 'g') {
  const h = 92;
  const queue = cote === 'g' ? `M28,${h - 6} l-18,26 l40,-20 z` : `M${larg - 28},${h - 6} l18,26 l-40,-20 z`;
  return `<g id="${id}" filter="url(#ombreCarte)"><path d="${queue}" fill="#fff"/><rect width="${larg}" height="${h}" rx="30" fill="#fff"/>
    <text x="${larg / 2}" y="${h / 2 + 13}" text-anchor="middle" font-family="Geist" font-weight="600" font-size="36" fill="#1a1f29">${texte}</text></g>`;
}

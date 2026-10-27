// Fully vector modelled hand holding a Leyton marker. Pen tip is at (0,0).
// Built in a "pen frame" (pen vertical, tip up) then rotated so the arm comes from bottom right.
function buildHandSVG(opts = {}) {
  const ROT = opts.rot ?? -34;
  const OL = '#9A5B3C';                       // warm skin outline
  // ---- geometry helpers
  const add = (a, b) => [a[0] + b[0], a[1] + b[1]], sub = (a, b) => [a[0] - b[0], a[1] - b[1]];
  const mul = (a, k) => [a[0] * k, a[1] * k], len = a => Math.hypot(a[0], a[1]);
  const nrm = a => { const l = len(a) || 1; return [a[0] / l, a[1] / l]; };
  const perp = a => [-a[1], a[0]];
  const f = n => n.toFixed(1);
  // smooth closed path through points (Catmull-Rom -> cubic Bezier)
  function smooth(pts, closed = true) {
    const n = pts.length; let d = `M ${f(pts[0][0])} ${f(pts[0][1])}`;
    const P = i => closed ? pts[(i + n) % n] : pts[Math.max(0, Math.min(n - 1, i))];
    const last = closed ? n : n - 1;
    for (let i = 0; i < last; i++) {
      const p0 = P(i - 1), p1 = P(i), p2 = P(i + 1), p3 = P(i + 2);
      const c1 = add(p1, mul(sub(p2, p0), 1 / 6)), c2 = sub(p2, mul(sub(p3, p1), 1 / 6));
      d += ` C ${f(c1[0])} ${f(c1[1])} ${f(c2[0])} ${f(c2[1])} ${f(p2[0])} ${f(p2[1])}`;
    }
    return d + (closed ? ' Z' : '');
  }
  // finger: centreline from base to tip with widths; returns outline path and useful frames
  function fingerPath(cl, w) {
    const R = [], L = [], fr = [];
    for (let i = 0; i < cl.length; i++) {
      const dir = nrm(sub(cl[Math.min(i + 1, cl.length - 1)], cl[Math.max(i - 1, 0)]));
      const nn = perp(dir); fr.push({ p: cl[i], dir, nn, w: w[i] });
      R.push(sub(cl[i], mul(nn, w[i] / 2))); L.push(add(cl[i], mul(nn, w[i] / 2)));
    }
    const tip = fr[fr.length - 1], cap = [];
    for (let k = 1; k < 6; k++) {
      const a = -Math.PI / 2 + Math.PI * k / 6;
      cap.push(add(tip.p, add(mul(tip.nn, Math.sin(a) * tip.w / 2), mul(tip.dir, Math.cos(a) * tip.w * .6))));
    }
    return { d: smooth([...R, ...cap, ...L.slice().reverse()]), fr };
  }
  const nail = (fr, side = 1, scale = 1) => {
    const t = fr[fr.length - 1], c = add(add(t.p, mul(t.dir, -t.w * .12)), mul(t.nn, side * t.w * .14));
    const ang = Math.atan2(t.dir[1], t.dir[0]) * 180 / Math.PI;
    return `<g transform="translate(${f(c[0])} ${f(c[1])}) rotate(${f(ang)})">
      <ellipse rx="${f(t.w * .42 * scale)}" ry="${f(t.w * .29 * scale)}" fill="url(#nail)" stroke="#C98C70" stroke-width="1.6"/>
      <ellipse cx="${f(t.w * .1)}" cy="${f(-t.w * .1 * side)}" rx="${f(t.w * .16)}" ry="${f(t.w * .06)}" fill="#fff" opacity=".7"/></g>`;
  };
  const crease = (fr, i, side = 1, k = .55) => {
    const q = fr[i]; const a = add(q.p, mul(q.nn, side * q.w * .1)), b = add(a, mul(q.nn, side * q.w * k * .5));
    const m = add(mul(add(a, b), .5), mul(q.dir, 5));
    return `<path d="M ${f(a[0])} ${f(a[1])} Q ${f(m[0])} ${f(m[1])} ${f(b[0])} ${f(b[1])}" fill="none" stroke="#B9765A" stroke-width="2.4" stroke-linecap="round" opacity=".55"/>`;
  };
  const sk = (d, fill = 'url(#skin)', extra = '') => `<path d="${d}" fill="${fill}" stroke="${OL}" stroke-width="2.6" stroke-linejoin="round" ${extra}/>`;

  // ---- parts (pen frame: pen along +y, tip at origin; +x = back of hand side)
  const index = fingerPath([[74, 262], [52, 222], [32, 184], [15, 148], [4, 116]], [50, 46, 41, 37, 34]);
  const thumb = fingerPath([[-6, 352], [-46, 306], [-54, 262], [-42, 224], [-22, 196]], [64, 54, 47, 42, 37]);
  const middle = fingerPath([[70, 300], [32, 286], [0, 262], [-18, 236]], [46, 42, 38, 34]);
  const ring = fingerPath([[78, 338], [40, 334], [10, 318], [-6, 296]], [44, 40, 36, 32]);
  const pinky = fingerPath([[84, 372], [52, 372], [28, 360], [16, 344]], [38, 34, 30, 27]);
  const back = smooth([[52, 236], [104, 230], [148, 262], [168, 318], [166, 380], [150, 440], [104, 470], [30, 468], [-24, 440], [-40, 390], [-20, 330], [10, 280]]);
  const wrist = smooth([[22, 440], [150, 426], [196, 560], [214, 640], [40, 690], [16, 600]]);
  const tendons = [[[72, 270], [96, 330], [110, 410]], [[110, 262], [126, 330], [134, 410]], [[138, 286], [148, 340], [150, 400]]]
    .map(p => `<path d="${smooth(p, false)}" fill="none" stroke="#E0A483" stroke-width="5" stroke-linecap="round" opacity=".22"/>`).join('');
  const knuckles = [[92, 262], [118, 270], [140, 290]].map(([x, y]) => `<ellipse cx="${x + 6}" cy="${y - 4}" rx="16" ry="10" fill="#FFE3CF" opacity=".55"/>`).join('');

  const pen = `
    <path d="M 0 0 L -9 26 Q 0 31 9 26 Z" fill="#06263F"/>
    <path d="M -2 4 L -5 20" stroke="#5E86A8" stroke-width="2" stroke-linecap="round"/>
    <path d="M -12 26 L 12 26 L 15 50 L -15 50 Z" fill="url(#collar)"/>
    <rect x="-19" y="48" width="38" height="380" rx="9" fill="url(#barrel)" stroke="#8FA3B5" stroke-width="1.6"/>
    <rect x="-19" y="86" width="38" height="18" fill="url(#grip)" opacity=".9"/>
    <rect x="-19" y="340" width="38" height="88" rx="9" fill="url(#capg)"/>
    <circle cx="0" cy="372" r="11" fill="none" stroke="#F0682F" stroke-width="5" stroke-dasharray="52 18" transform="rotate(-50 0 372)"/>
    <rect x="-11" y="56" width="5" height="270" rx="2.5" fill="#fff" opacity=".85"/>`;

  const sleeve = smooth([[10, 640], [228, 600], [330, 900], [420, 1300], [120, 1400], [-40, 760]]);
  const cuff = smooth([[8, 612], [210, 570], [236, 640], [24, 684]]);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" style="overflow:visible">
  <defs>
    <linearGradient id="skin" gradientUnits="userSpaceOnUse" x1="150" y1="200" x2="-40" y2="420"><stop offset="0" stop-color="#FBD9C0"/><stop offset=".55" stop-color="#F1BF9C"/><stop offset="1" stop-color="#DB9C78"/></linearGradient>
    <linearGradient id="skinD" gradientUnits="userSpaceOnUse" x1="100" y1="260" x2="-20" y2="380"><stop offset="0" stop-color="#EDB693"/><stop offset="1" stop-color="#CF8C67"/></linearGradient>
    <linearGradient id="nail" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FCE9DF"/><stop offset="1" stop-color="#EFC3AE"/></linearGradient>
    <linearGradient id="barrel" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#C9D4DE"/><stop offset=".3" stop-color="#FFFFFF"/><stop offset=".7" stop-color="#EEF2F6"/><stop offset="1" stop-color="#AEBCCA"/></linearGradient>
    <linearGradient id="capg" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#001A2C"/><stop offset=".35" stop-color="#1D4C72"/><stop offset="1" stop-color="#001A2C"/></linearGradient>
    <linearGradient id="collar" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#C24A18"/><stop offset=".35" stop-color="#FF8A55"/><stop offset="1" stop-color="#C9521F"/></linearGradient>
    <linearGradient id="grip" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#C24A18"/><stop offset=".4" stop-color="#F48A5A"/><stop offset="1" stop-color="#C9521F"/></linearGradient>
    <linearGradient id="slv" gradientUnits="userSpaceOnUse" x1="0" y1="700" x2="360" y2="900"><stop offset="0" stop-color="#14466E"/><stop offset=".5" stop-color="#0A3355"/><stop offset="1" stop-color="#001C30"/></linearGradient>
    <linearGradient id="cuffg" gradientUnits="userSpaceOnUse" x1="0" y1="640" x2="240" y2="600"><stop offset="0" stop-color="#DCE4EC"/><stop offset=".45" stop-color="#FFFFFF"/><stop offset="1" stop-color="#C5D1DC"/></linearGradient>
    <radialGradient id="ao" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#7A3E22" stop-opacity=".35"/><stop offset="1" stop-color="#7A3E22" stop-opacity="0"/></radialGradient>
  </defs>
  <g transform="rotate(${ROT})">
    <path d="${sleeve}" fill="url(#slv)"/>
    <path d="M 120 760 Q 190 900 220 1100 M 250 720 Q 300 820 330 960" fill="none" stroke="#2A5F8A" stroke-width="5" stroke-linecap="round" opacity=".6"/>
    <path d="M 60 820 Q 120 980 130 1200" fill="none" stroke="#00121F" stroke-width="7" stroke-linecap="round" opacity=".5"/>
    ${sk(wrist)}
    <path d="${cuff}" fill="url(#cuffg)" stroke="#9FB0C0" stroke-width="2"/>
    <circle cx="200" cy="610" r="9" fill="#F0682F" stroke="#B5461A" stroke-width="2"/>
    ${pen}
    ${sk(back)}${tendons}${knuckles}
    <ellipse cx="40" cy="330" rx="60" ry="40" fill="url(#ao)"/>
    ${sk(pinky.d, 'url(#skinD)')}${crease(pinky.fr, 2, -1)}
    ${sk(ring.d, 'url(#skinD)')}${crease(ring.fr, 2, -1)}
    ${sk(middle.d, 'url(#skinD)')}${crease(middle.fr, 2, -1)}
    <ellipse cx="-4" cy="236" rx="22" ry="40" fill="url(#ao)"/>
    ${sk(thumb.d)}${nail(thumb.fr, -1, 1.05)}${crease(thumb.fr, 2, 1, .7)}
    <path d="M -66 286 Q -66 246 -50 216" fill="none" stroke="#FFE6D4" stroke-width="5" stroke-linecap="round" opacity=".6"/>
    ${sk(index.d)}${nail(index.fr, 1)}${crease(index.fr, 1, 1)}${crease(index.fr, 3, 1, .5)}
    <path d="M 84 250 Q 62 206 32 156" fill="none" stroke="#FFE8D8" stroke-width="6" stroke-linecap="round" opacity=".55"/>
  </g></svg>`;
  return svg;
}
if (typeof module !== 'undefined') module.exports = { buildHandSVG };

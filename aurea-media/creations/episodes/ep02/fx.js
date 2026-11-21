// Effets cartoon déterministes (fonction du temps) : impacts, poussière, étincelles, lignes de vitesse,
// tremblements, rebonds. Chaque effet est dessiné dans un groupe SVG <g id="..."> recalculé à chaque image.

// Rebond d'apparition (0 → 1 avec dépassement), squash and stretch associé
function apparition(t, t0, d = .35) { return t < t0 ? 0 : E.backS(clamp((t - t0) / d)); }
function squashImpact(t, t0, force = .25, d = .45) {
  // renvoie {sx, sy} : écrasement à l'impact puis rebond amorti
  if (t < t0 || t > t0 + d) return { sx: 1, sy: 1 };
  const u = (t - t0) / d, k = Math.exp(-u * 5) * Math.cos(u * 14) * force;
  return { sx: 1 + k, sy: 1 - k };
}
function tremble(t, t0, amp = 18, d = .35) {
  if (t < t0 || t > t0 + d) return { x: 0, y: 0, r: 0 };
  const k = (1 - (t - t0) / d) * amp;
  return { x: Math.sin(t * 91) * k, y: Math.cos(t * 77) * k, r: Math.sin(t * 63) * k * .05 };
}
// Saut cartoon : anticipation (écrasement), envol étiré, atterrissage écrasé
function saut(t, t0, montee = .3, h = 140) {
  const ant = .14;
  if (t < t0 - ant) return { y: 0, sx: 1, sy: 1 };
  if (t < t0) { const k = E.out((t - (t0 - ant)) / ant); return { y: 0, sx: 1 + .14 * k, sy: 1 - .16 * k }; }
  const u = (t - t0) / (2 * montee);
  if (u < 1) { const y = -h * 4 * u * (1 - u); const st = Math.abs(1 - 2 * u); return { y, sx: 1 - .1 * st, sy: 1 + .14 * st }; }
  const s = squashImpact(t, t0 + 2 * montee, .22, .4); return { y: 0, sx: s.sx, sy: s.sy };
}

// Explosion d'impact (étoile en zigzag + traits rayonnants)
function impact(x, y, t, t0, taille = 1, coul = '#fff', d = .32) {
  if (t < t0 || t > t0 + d) return '';
  const u = (t - t0) / d, r = (60 + 90 * E.out(u)) * taille, a = 1 - u;
  const pts = Array.from({ length: 16 }, (_, i) => { const ang = i * Math.PI / 8, rr = i % 2 ? r * .55 : r; return `${(x + Math.cos(ang) * rr).toFixed(1)},${(y + Math.sin(ang) * rr).toFixed(1)}`; }).join(' ');
  let s = `<polygon points="${pts}" fill="${coul}" opacity="${(a * .9).toFixed(2)}"/>`;
  for (let i = 0; i < 8; i++) {
    const ang = i * Math.PI / 4 + .2, r1 = r * 1.05, r2 = r * (1.25 + .5 * E.out(u));
    s += `<path d="M${x + Math.cos(ang) * r1},${y + Math.sin(ang) * r1} L${x + Math.cos(ang) * r2},${y + Math.sin(ang) * r2}" stroke="${coul}" stroke-width="${10 * taille * a}" stroke-linecap="round"/>`;
  }
  return s;
}
// Bouffées de poussière (à l'atterrissage, aux impacts)
function poussiere(x, y, t, t0, n = 6, taille = 1, coul = '#efe7da', d = .6) {
  if (t < t0 || t > t0 + d) return '';
  const u = (t - t0) / d; let s = '';
  for (let i = 0; i < n; i++) {
    const ang = Math.PI + (i / (n - 1)) * Math.PI, dist = (30 + 70 * E.out(u)) * taille;
    s += `<circle cx="${x + Math.cos(ang) * dist * 1.4}" cy="${y + Math.sin(ang) * dist * .5}" r="${(18 + 14 * (i % 3)) * taille * (1 - u * .6)}" fill="${coul}" opacity="${(1 - u) * .9}"/>`;
  }
  return s;
}
// Étincelles à quatre branches
function etoile4(x, y, r, coul, rot = 0) {
  return `<path transform="translate(${x},${y}) rotate(${rot})" d="M0,${-r} C${r * .12},${-r * .12} ${r * .12},${-r * .12} ${r},0 C${r * .12},${r * .12} ${r * .12},${r * .12} 0,${r} C${-r * .12},${r * .12} ${-r * .12},${r * .12} ${-r},0 C${-r * .12},${-r * .12} ${-r * .12},${-r * .12} 0,${-r} Z" fill="${coul}"/>`;
}
function etincelles(x, y, t, t0, n = 7, rayon = 160, coul = '#ffd86b', d = .7) {
  if (t < t0 || t > t0 + d) return '';
  const u = (t - t0) / d; let s = '';
  for (let i = 0; i < n; i++) {
    const ang = i * 2 * Math.PI / n + .3, dist = rayon * E.out(u) * (.7 + .3 * ((i * 7) % 3) / 2);
    const r = 22 * Math.sin(Math.PI * Math.min(1, u * 1.3)) * (i % 2 ? .7 : 1);
    if (r > .5) s += etoile4(x + Math.cos(ang) * dist, y + Math.sin(ang) * dist, r, coul, u * 90);
  }
  return s;
}
// Confettis
function confettis(x, y, t, t0, n = 26, d = 1.4) {
  if (t < t0 || t > t0 + d) return '';
  const u = t - t0; let s = '';
  const coul = ['#e8b84a', '#ff7b2c', '#3fb27f', '#4c7bd9', '#ff5d8f', '#fff'];
  for (let i = 0; i < n; i++) {
    const ang = -Math.PI / 2 + (((i * 37) % 100) / 100 - .5) * 2.2, v = 700 + ((i * 53) % 400);
    const px = x + Math.cos(ang) * v * u, py = y + Math.sin(ang) * v * u + 900 * u * u;
    s += `<rect x="-9" y="-5" width="18" height="10" rx="2" fill="${coul[i % 6]}" transform="translate(${px},${py}) rotate(${u * 500 + i * 40})" opacity="${1 - clamp((u - d + .3) / .3)}"/>`;
  }
  return s;
}
// Lignes de vitesse (plein écran, vers un point)
function lignesVitesse(t, t0, d, cx = 540, cy = 960, coul = '#fff', op0 = .7) {
  if (t < t0 || t > t0 + d) return '';
  const u = (t - t0) / d, a = Math.sin(Math.PI * u) * op0; let s = '';
  for (let i = 0; i < 36; i++) {
    const ang = i * Math.PI / 18 + ((i * 13) % 7) * .03, r1 = 520 + ((i * 97) % 300), r2 = r1 + 600;
    s += `<path d="M${cx + Math.cos(ang) * r1},${cy + Math.sin(ang) * r1} L${cx + Math.cos(ang) * r2},${cy + Math.sin(ang) * r2}" stroke="${coul}" stroke-width="${6 + (i % 4) * 4}" stroke-linecap="round" opacity="${a.toFixed(2)}"/>`;
  }
  return s;
}
// Traits de vibration autour d'un objet
function vibrations(x, y, t, actif, ecart = 60, coul = '#1b1f2a') {
  if (!actif || (t % .14) > .09) return '';
  return [[-1, 0], [1, 0]].map(([sx]) => `<g stroke="${coul}" stroke-width="7" stroke-linecap="round" fill="none"><path d="M${x + sx * ecart},${y - 30} q${sx * 14},12 0,24 q${-sx * 14},12 0,24"/><path d="M${x + sx * (ecart + 22)},${y - 42} q${sx * 18},18 0,36 q${-sx * 18},18 0,36"/></g>`).join('');
}
// Gouttes de sueur qui jaillissent
function gouttesSueur(x, y, t, t0, d = .6) {
  if (t < t0 || t > t0 + d) return '';
  const u = (t - t0) / d; let s = '';
  [[-1, -.6], [1, -.8], [.4, -1]].forEach(([sx, sy], i) => {
    const px = x + sx * 90 * u, py = y + sy * 120 * u + 260 * u * u;
    s += `<path transform="translate(${px},${py}) rotate(${sx * 30})" d="M0,-18 C8,-5 10,5 0,9 C-10,5 -8,-5 0,-18 Z" fill="#8fd3ff" opacity="${1 - u}"/>`;
  });
  return s;
}
// Notes de musique qui s'envolent (sifflote)
function notes(x, y, t, t0, t1) {
  if (t < t0 || t > t1) return '';
  let s = '';
  for (let i = 0; i < 3; i++) {
    const u = ((t - t0) * .8 + i / 3) % 1;
    s += `<g transform="translate(${x + 40 * u + Math.sin(u * 7 + i) * 18},${y - 160 * u}) rotate(${Math.sin(u * 5) * 15})" opacity="${Math.sin(Math.PI * u)}"><ellipse cx="0" cy="18" rx="12" ry="9" fill="#1b1f2a"/><path d="M10,18 V-14 l16,6" stroke="#1b1f2a" stroke-width="5" fill="none"/></g>`;
  }
  return s;
}
// Texte cartoon qui claque (onomatopée)
function onomatopee(x, y, txt, t, t0, d = .55, coul = '#ffd23f', taille = 110, rot = -8) {
  if (t < t0 || t > t0 + d) return '';
  const a = E.backS(clamp((t - t0) / .18)), b = clamp((t - t0 - d + .15) / .15);
  return `<g transform="translate(${x},${y}) rotate(${rot}) scale(${a * (1 + b * .2)})" opacity="${1 - b}"><text text-anchor="middle" font-family="Bricolage" font-weight="800" font-size="${taille}" fill="${coul}" stroke="#1b1f2a" stroke-width="${taille * .12}" paint-order="stroke" stroke-linejoin="round">${txt}</text></g>`;
}

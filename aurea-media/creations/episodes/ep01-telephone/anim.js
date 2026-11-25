// Moteur d'animation déterministe : chaque image est calculée à partir du temps t (secondes), sans horloge.
// Pistes d'images clés, courbes d'accélération, utilitaires SVG.
const E = {
  lin: x => x,
  in: x => x * x * x,
  out: x => 1 - Math.pow(1 - x, 3),
  io: x => (x < .5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2),
  sine: x => -(Math.cos(Math.PI * x) - 1) / 2,
  back: x => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2); },
  backS: x => { const c1 = 2.6, c3 = c1 + 1; return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2); },
  elastic: x => (x === 0 || x === 1 ? x : Math.pow(2, -10 * x) * Math.sin((x * 10 - .75) * (2 * Math.PI / 3)) + 1),
  expoOut: x => (x === 1 ? 1 : 1 - Math.pow(2, -10 * x)),
  expoIo: x => (x === 0 ? 0 : x === 1 ? 1 : x < .5 ? Math.pow(2, 20 * x - 10) / 2 : (2 - Math.pow(2, -20 * x + 10)) / 2),
};
const clamp = (x, a = 0, b = 1) => Math.max(a, Math.min(b, x));
const mix = (a, b, x) => a + (b - a) * x;
const prog = (t, a, b, ease = E.io) => ease(clamp((t - a) / (b - a)));

// Piste : liste de [temps, valeur, courbe vers ce point]. Les valeurs peuvent être des nombres ou des objets de nombres.
function piste(t, cles) {
  if (t <= cles[0][0]) return cles[0][1];
  for (let i = 1; i < cles.length; i++) {
    const [t1, v1, ease] = cles[i];
    if (t <= t1) {
      const [t0, v0] = cles[i - 1];
      const x = (ease || E.io)((t - t0) / (t1 - t0));
      if (typeof v0 === 'number') return mix(v0, v1, x);
      const o = {};
      for (const k in v1) o[k] = mix(v0[k] ?? v1[k], v1[k], x);
      for (const k in v0) if (!(k in o)) o[k] = v0[k];
      return o;
    }
  }
  return cles[cles.length - 1][1];
}

// Bruit doux (mouvements organiques, caméra tenue à la main)
function bruit(t, graine = 0) {
  return Math.sin(t * 1.13 + graine) * .5 + Math.sin(t * 2.31 + graine * 1.7) * .3 + Math.sin(t * 4.07 + graine * 2.9) * .2;
}

const $ = id => document.getElementById(id);
function tr(id, s) { const e = typeof id === 'string' ? $(id) : id; if (e) e.setAttribute('transform', s); }
function op(id, v) { const e = typeof id === 'string' ? $(id) : id; if (e) e.style.opacity = v; }
function attr(id, k, v) { const e = typeof id === 'string' ? $(id) : id; if (e) e.setAttribute(k, v); }
function vis(id, on) { const e = typeof id === 'string' ? $(id) : id; if (e) e.style.display = on ? '' : 'none'; }

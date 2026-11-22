// Sous-titres V3 : blocs plus longs (jusqu'à deux lignes, 8 mots) affichés d'un coup pour pouvoir lire en avance.
// Le mot prononcé grossit légèrement, les mots déjà dits sont pleins, ceux à venir un peu atténués. Mots-clés en jaune.
let GROUPES = [];
const ST_MAX_CAR = 40, ST_MAX_MOTS = 8;
function construireSousTitres(script, cal) {
  GROUPES = [];
  for (const ph of script.phrases) {
    if (ph.st === false) continue;
    const temps = cal.phrases[ph.id].mots;
    const jet = []; let cle = false;
    const bruts = []; for (const b of ph.sous_titre.split(/\s+/)) { if (/^[?!:;.,…]+\*?$/.test(b) && bruts.length) bruts[bruts.length - 1] += ' ' + b; else if (b) bruts.push(b); }
    bruts.forEach((brut, i) => {
      if (brut.startsWith('*')) cle = true;
      jet.push({ mot: brut.replace(/\*/g, ''), cle, debut: (temps[i] || temps[temps.length - 1]).debut, fin: (temps[i] || temps[temps.length - 1]).fin });
      if (/\*[?!:;.,…\s]*$/.test(brut)) cle = false;
    });
    let g = [];
    const pousser = () => { if (g.length) GROUPES.push(g); g = []; };
    const lng = () => g.reduce((a, x) => a + x.mot.length + 1, 0);
    jet.forEach((j, i) => {
      if (g.length && (g.length >= ST_MAX_MOTS || lng() + j.mot.length > ST_MAX_CAR)) {
        // on coupe de préférence après la dernière virgule du bloc, sinon avant les petits mots
        let c = -1; for (let k = g.length - 1; k >= 2; k--) if (/[,:;]$/.test(g[k].mot)) { c = k; break; }
        const report = c >= 0 ? g.splice(c + 1) : [];
        pousser(); g = report;
      }
      g.push(j);
      const reste = jet.slice(i + 1).reduce((a, x) => a + x.mot.length + 1, 0);
      // fin de phrase : nouveau bloc, sauf si la suite est très courte et tient sur le même bloc
      if (/[.?!…]$/.test(j.mot) && !(reste && lng() + reste <= 24 && g.length + jet.length - i - 1 <= 5)) pousser();
    });
    pousser();
  }
  GROUPES.forEach((g, i) => { g.debut = g[0].debut - .12; g.fin = GROUPES[i + 1] ? Math.min(GROUPES[i + 1][0].debut - .12, g[g.length - 1].fin + .7) : g[g.length - 1].fin + .7; });
}
let groupeAffiche = -1;
function rendreSousTitres(t) {
  const st = $('st');
  const i = GROUPES.findIndex(g => t >= g.debut && t < g.fin);
  if (i !== groupeAffiche) { st.innerHTML = i < 0 ? '' : GROUPES[i].map((j, k) => `<span class="m${j.cle ? ' cle' : ''}" id="stm${k}">${j.mot}</span>`).join(''); groupeAffiche = i; }
  if (i < 0) return;
  const g = GROUPES[i];
  const a = clamp((t - g.debut) / .16), s = E.backS(a);
  st.style.transform = `translateY(${((1 - a) * 30).toFixed(1)}px) scale(${(.85 + .15 * s).toFixed(3)})`;
  st.style.opacity = clamp(a * 2);
  g.forEach((j, k) => {
    const e = $('stm' + k); if (!e) return;
    const actif = t >= j.debut - .02 && t < j.fin + .04;
    const dit = t >= j.debut - .02;
    const pic = actif ? Math.sin(Math.PI * clamp((t - j.debut + .02) / .18)) : 0;
    e.style.opacity = dit ? 1 : .72;
    e.style.transform = `translateY(${(-pic * 6).toFixed(1)}px) scale(${(1 + .07 * (actif ? 1 : 0) + .05 * pic).toFixed(3)})`;
  });
}

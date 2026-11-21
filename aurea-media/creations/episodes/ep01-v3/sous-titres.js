// Sous-titres V2 : groupes courts (2 à 4 mots), apparition mot par mot avec rebond, mots-clés en jaune.
let GROUPES = [];
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
    const petits = /^(a|à|au|aux|de|du|des|d'un|le|la|les|l'.*|en|et|ton|ta|tes|ce|c'est|un|une|que|qui|sur|pour|avec|si|tu|il|t'es)$/i;
    jet.forEach(j => {
      const lng = g.reduce((a, x) => a + x.mot.length + 1, 0);
      if (g.length && (g.length >= 3 || lng + j.mot.length > 15) && !(j.cle && g[g.length - 1].cle && lng + j.mot.length < 20)) {
        const report = []; while (g.length > 1 && petits.test(g[g.length - 1].mot)) report.unshift(g.pop());
        pousser(); g = report;
      }
      g.push(j);
      if (/[.?!…]$/.test(j.mot)) pousser();
    });
    pousser();
  }
  GROUPES.forEach((g, i) => { g.debut = g[0].debut - .05; g.fin = GROUPES[i + 1] ? Math.min(GROUPES[i + 1][0].debut - .05, g[g.length - 1].fin + .6) : g[g.length - 1].fin + .6; });
}
let groupeAffiche = -1;
function rendreSousTitres(t) {
  const st = $('st');
  const i = GROUPES.findIndex(g => t >= g.debut && t < g.fin);
  if (i !== groupeAffiche) { st.innerHTML = i < 0 ? '' : GROUPES[i].map((j, k) => `<span class="m${j.cle ? ' cle' : ''}" id="stm${k}">${j.mot}</span>`).join(''); groupeAffiche = i; }
  if (i < 0) return;
  const g = GROUPES[i];
  g.forEach((j, k) => {
    const e = $('stm' + k); if (!e) return;
    const a = clamp((t - j.debut + .03) / .2), s = E.backS(a);
    const actif = t >= j.debut && t < j.fin + .05;
    e.style.opacity = clamp(a * 2.5);
    e.style.transform = `translateY(${(1 - a) * 40}px) scale(${(.5 + .5 * s) * (actif ? 1.06 : 1)}) rotate(${(1 - a) * (k % 2 ? 6 : -6)}deg)`;
  });
}

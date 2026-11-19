// Sous-titres dynamiques : groupes de 2 à 4 mots, chaque mot apparaît au moment où il est prononcé,
// mots-clés (entre * dans script.json) en or avec un soulignement animé.
let GROUPES = [];
function construireSousTitres(script, cal) {
  GROUPES = [];
  for (const ph of script.phrases) {
    const temps = cal.phrases[ph.id].mots;
    const jetons = []; let cle = false;
    ph.sous_titre.split(/\s+/).forEach((brut, i) => {
      const debutCle = brut.startsWith('*'); if (debutCle) cle = true;
      const mot = brut.replace(/\*/g, '');
      jetons.push({ mot, cle, debut: temps[i] ? temps[i].debut : temps[temps.length - 1].fin, fin: temps[i] ? temps[i].fin : temps[temps.length - 1].fin });
      if (/\*[.,?!…]*$/.test(brut)) cle = false;
    });
    // découpe en groupes : coupe après une ponctuation forte, ou à 3 mots / 17 caractères
    let g = [];
    const pousser = () => { if (g.length) GROUPES.push(g); g = []; };
    const petits = /^(a|à|au|aux|de|du|des|le|la|les|l'.*|en|et|ton|ta|tes|ce|c'est|un|une|que|qui|sur|pour|avec|si|tu|il)$/i;
    jetons.forEach((j, i) => {
      const lng = g.reduce((a, x) => a + x.mot.length + 1, 0);
      if (g.length && (g.length >= 4 || lng + j.mot.length > 17) && !(j.cle && g[g.length - 1].cle && lng + j.mot.length < 22)) {
        // ne pas finir un groupe sur un petit mot : il passe au groupe suivant
        const report = [];
        while (g.length > 1 && petits.test(g[g.length - 1].mot)) report.unshift(g.pop());
        pousser(); g = report;
      }
      g.push(j);
      if (/[.,?!…:]$/.test(j.mot)) pousser();
    });
    pousser();
  }
  GROUPES.forEach((g, i) => { g.debut = g[0].debut - .06; g.fin = GROUPES[i + 1] ? Math.min(GROUPES[i + 1][0].debut - .06, g[g.length - 1].fin + .7) : g[g.length - 1].fin + .7; });
}
let groupeAffiche = -1;
function rendreSousTitres(t) {
  const st = $('st');
  const i = GROUPES.findIndex(g => t >= g.debut && t < g.fin);
  if (i !== groupeAffiche) {
    st.innerHTML = i < 0 ? '' : GROUPES[i].map((j, k) => `<span class="m${j.cle ? ' cle' : ''}" id="stm${k}">${j.mot}${j.cle ? '<i class="soul"></i>' : ''}</span>`).join('');
    groupeAffiche = i;
  }
  if (i < 0) return;
  const g = GROUPES[i];
  g.forEach((j, k) => {
    const e = $('stm' + k); if (!e) return;
    const a = prog(t, j.debut - .04, j.debut + .16, E.backS);
    const sortie = prog(t, g.fin - .1, g.fin, E.in);
    e.style.opacity = clamp(a * 1.4) * (1 - sortie);
    e.style.transform = `translateY(${(1 - a) * 26 - sortie * 10}px) scale(${.72 + .28 * a})`;
    const s = e.querySelector('.soul');
    if (s) s.style.transform = `scaleX(${prog(t, j.debut + .05, j.debut + .35, E.out)})`;
  });
}

// Grain de pellicule (déterministe : le motif dépend de l'image)
let GRAINS = [];
function grainInit() {
  const c = $('grain');
  for (let n = 0; n < 4; n++) {
    const off = document.createElement('canvas'); off.width = 600; off.height = 1080;
    const x = off.getContext('2d'); const im = x.createImageData(600, 1080);
    let s = 1234 + n * 999;
    for (let i = 0; i < im.data.length; i += 4) { s = (s * 16807) % 2147483647; const v = (s / 2147483647) * 255; im.data[i] = im.data[i + 1] = im.data[i + 2] = v; im.data[i + 3] = 255; }
    x.putImageData(im, 0, 0); GRAINS.push(off);
  }
  c.style.width = '1200px'; c.style.height = '2160px';
}
function grain(t) {
  const c = $('grain'); const x = c.getContext('2d');
  x.drawImage(GRAINS[Math.floor(t * 24) % 4], 0, 0);
}

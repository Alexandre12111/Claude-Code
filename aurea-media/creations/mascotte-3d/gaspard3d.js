// Gaspard, le castor bâtisseur : modèle 3D paramétrique et articulé (Three.js, module ES).
// Source unique du personnage : mêmes proportions et mêmes couleurs que la version 2D (gaspard.js), 1 unité = 100 px.
// Repère : pieds au sol en (0, 0, 0), y vers le haut, z vers la caméra (face), x vers la droite du spectateur.
// Côtés : « D » = droite du spectateur (bras droit à l'écran), « G » = gauche du spectateur.
//
// Utilisation :
//   import { creerGaspard } from './gaspard3d.js';
//   const g = creerGaspard(THREE);  scene.add(g.racine);
//   g.poser({ brasD: { main: [2.1, 1.2, .3] }, mainD: { fermeture: 1, objet: 'tel' }, visage: { bouche: 'rire' } });
// Tous les os sont accessibles par leur nom dans g.os (voir README.md pour la liste complète).

export const COULEURS = {
  poil: 0xa8643a, poilOmbre: 0x7f4524, poilClair: 0xc98552, ventre: 0xf6d9ae, museau: 0xfbe6c6, nez: 0x3a2016,
  dents: 0xfffaf0, oeil: 0xffffff, pupille: 0x1a120d, casque: 0xffc21a, casqueOmbre: 0xe89b00, casqueClair: 0xffe27a,
  queue: 0x5e3420, queueTrait: 0x7d4a2c, pied: 0x6b3b20, ceinture: 0x3a2a22, boucle: 0xe8b84a, trait: 0x4a2615,
  joue: 0xff8a6b, bouche: 0x3a1610, langue: 0xff7a6b, poche: 0x5a4436, tel: 0x15181e, ecran: 0x3b5c8f, crayon: 0xffd23f,
};

// Dimensions de référence (unités) reprises du dessin 2D
export const DIM = {
  corps: { largeur: 3.3, hauteur: 4.7, profondeur: .8 },      // profondeur = aplatissement avant/arrière
  epaule: { x: 1.5, y: 2.5 }, bras: { haut: .88, avant: .84 },  // longueurs de l'humérus et de l'avant-bras
  hanche: { x: .8, y: .62 }, jambe: { haut: .3, bas: .3 },
  yeux: { x: .5, y: 3.7, rx: .4, ry: .48 }, museau: { y: 2.74, rx: .96, ry: .62 },
};

export function creerGaspard(THREE, options = {}) {
  const C = COULEURS, contour = options.contour !== false, epContour = options.epaisseurContour ?? .028;
  const os = {}, mats = {}, variantes = {};

  // ---------------- matériaux « cartoon » (ombrage en 3 paliers) ----------------
  const paliers = new Uint8Array([90, 175, 255]);
  const rampe = new THREE.DataTexture(paliers, 3, 1, THREE.RedFormat); rampe.needsUpdate = true;
  rampe.minFilter = rampe.magFilter = THREE.NearestFilter;
  const toon = (coul, extra = {}) => new THREE.MeshToonMaterial({ color: coul, gradientMap: rampe, ...extra });
  for (const [k, v] of Object.entries(C)) mats[k] = toon(v);
  mats.oeil = new THREE.MeshBasicMaterial({ color: C.oeil });
  mats.pupille = new THREE.MeshBasicMaterial({ color: C.pupille });
  mats.reflet = new THREE.MeshBasicMaterial({ color: 0xffffff });
  mats.bouche = new THREE.MeshBasicMaterial({ color: C.bouche, side: THREE.DoubleSide });
  mats.langue = new THREE.MeshBasicMaterial({ color: C.langue, side: THREE.DoubleSide });
  mats.trait = new THREE.MeshBasicMaterial({ color: C.trait, side: THREE.DoubleSide });
  mats.joue = new THREE.MeshBasicMaterial({ color: C.joue, transparent: true, opacity: .38, depthWrite: false, side: THREE.DoubleSide });
  // contour : coque inversée gonflée le long des normales (compatible avec le skinning)
  const matContour = new THREE.MeshBasicMaterial({ color: C.trait, side: THREE.BackSide });
  matContour.onBeforeCompile = sh => {
    sh.uniforms.ep = { value: epContour };
    sh.vertexShader = 'uniform float ep;\n' + sh.vertexShader.replace('#include <begin_vertex>', '#include <begin_vertex>\n transformed += normalize(normal) * ep;');
  };
  const avecContour = (mesh, ep = 1) => {
    if (!contour) return mesh;
    const c = mesh.isSkinnedMesh ? new THREE.SkinnedMesh(mesh.geometry, matContour) : new THREE.Mesh(mesh.geometry, matContour);
    if (ep !== 1) { c.material = matContour.clone(); c.material.onBeforeCompile = sh => { sh.uniforms.ep = { value: epContour * ep }; sh.vertexShader = 'uniform float ep;\n' + sh.vertexShader.replace('#include <begin_vertex>', '#include <begin_vertex>\n transformed += normalize(normal) * ep;'); }; }
    c.userData.contour = true; c.name = mesh.name + '_contour';
    if (mesh.isSkinnedMesh) { mesh.userData.contourSkin = c; } else mesh.add(c);
    return mesh;
  };
  const maillage = (geo, mat, nom, cont = true) => { const m = new THREE.Mesh(geo, mat); m.name = nom; if (cont) avecContour(m); return m; };
  const os_ = (nom, parent, x = 0, y = 0, z = 0) => { const b = new THREE.Bone(); b.name = nom; b.position.set(x, y, z); if (parent) parent.add(b); os[nom] = b; return b; };

  // ---------------- profil du corps (haricot) : mêmes courbes de Bézier que la 2D ----------------
  const bez = (p0, p1, p2, p3, t) => { const u = 1 - t; return [u * u * u * p0[0] + 3 * u * u * t * p1[0] + 3 * u * t * t * p2[0] + t * t * t * p3[0], u * u * u * p0[1] + 3 * u * u * t * p1[1] + 3 * u * t * t * p2[1] + t * t * t * p3[1]]; };
  const W = 1.65, H = 4.7;
  const segs = [[[0, H], [W * .92, H], [W * 1.02, H * .72], [W * 1.02, H * .52]], [[W * 1.02, H * .52], [W * 1.04, H * .3], [W * 1.12, H * .08], [W * .96, H * .02]],
    [[W * .96, H * .02], [W * .7, -H * .02], [-W * .7, -H * .02], [-W * .96, H * .02]]];
  let profil = [];
  segs.forEach((s, k) => { for (let i = 0; i <= 40; i++) { const p = bez(...s, i / 40); if (k < 2 || p[0] >= 0) profil.push(p); } });
  profil = profil.filter(p => p[0] >= 0).sort((a, b) => a[1] - b[1]);
  const rayon = y => { // demi-largeur du corps à la hauteur y
    if (y <= profil[0][1]) return profil[0][0]; if (y >= H) return 0;
    for (let i = 1; i < profil.length; i++) if (profil[i][1] >= y) { const a = profil[i - 1], b = profil[i], t = (y - a[1]) / (b[1] - a[1] || 1); return a[0] + (b[0] - a[0]) * t; }
    return 0;
  };
  const PZ = DIM.corps.profondeur;
  // point de la surface du corps (avant) pour un x, y donnés, et sa normale approchée
  const surface = (x, y, decal = 0) => {
    const r = rayon(y), xx = Math.min(Math.abs(x), r * .999), z = PZ * Math.sqrt(Math.max(0, r * r - xx * xx));
    const n = new THREE.Vector3(x / (r * r || 1), 0, z / (PZ * PZ * r * r || 1)).normalize();
    const dy = (rayon(y + .02) - rayon(y - .02)) / .04; n.y = -dy * .35; n.normalize();
    return { p: new THREE.Vector3(x, y, z).addScaledVector(n, decal), n };
  };

  // ---------------- squelette ----------------
  const racine = new THREE.Group(); racine.name = 'Gaspard';
  const bRacine = os_('racine', null); racine.add(bRacine);
  const bBassin = os_('bassin', bRacine, 0, .6, 0);
  const bTorse = os_('torse', bBassin, 0, 1.0, 0);          // y monde 1,6 : penche le haut du corps
  const bTete = os_('tete', bTorse, 0, 1.7, 0);             // y monde 3,3 : centre du visage
  // corps : tour (lathe) habillé sur bassin + torse
  const pts = profil.map(p => new THREE.Vector2(Math.max(.0001, p[0]), p[1]));
  const gCorps = new THREE.LatheGeometry(pts, 64); gCorps.scale(1, 1, 1);
  { const pos = gCorps.attributes.position; for (let i = 0; i < pos.count; i++) pos.setZ(i, pos.getZ(i) * PZ); gCorps.computeVertexNormals(); }
  const peau = (geo, poids) => { // poids(y) -> [indice os, poids] pour 2 os
    const pos = geo.attributes.position, si = [], sw = [];
    for (let i = 0; i < pos.count; i++) { const w = poids(pos.getX(i), pos.getY(i), pos.getZ(i)); si.push(w[0], w[1], 0, 0); sw.push(w[2], 1 - w[2], 0, 0); }
    geo.setAttribute('skinIndex', new THREE.Uint16BufferAttribute(si, 4)); geo.setAttribute('skinWeight', new THREE.Float32BufferAttribute(sw, 4));
  };
  const lisse = (a, b, x) => { const t = Math.min(1, Math.max(0, (x - a) / (b - a))); return t * t * (3 - 2 * t); };
  peau(gCorps, (x, y) => [0, 1, 1 - lisse(1.0, 3.0, y)]);
  const corps = new THREE.SkinnedMesh(gCorps, mats.poil); corps.name = 'corps'; avecContour(corps);
  racine.add(corps); if (corps.userData.contourSkin) racine.add(corps.userData.contourSkin);
  const skCorps = new THREE.Skeleton([bBassin, bTorse]);

  // éléments rigides de la face et de la tête, rattachés au torse (coordonnées monde converties)
  const surTorse = (obj, x, y, z) => { obj.position.set(x, y - 1.6, z); bTorse.add(obj); return obj; };
  racine.updateMatrixWorld(true);
  // oriente un objet (déjà placé sous le torse) pour que son +z suive la normale n (repère du personnage, torse au repos)
  const orienter = (obj, n) => { const q = new THREE.Quaternion().setFromUnitVectors(new THREE.Vector3(0, 0, 1), n.clone().normalize()); obj.quaternion.copy(q); };
  // ventre : ellipse claire épousant la surface avant, habillée sur le même squelette que le corps (aucune couture en se penchant)
  const gVentre = new THREE.SphereGeometry(1, 48, 32);
  { const pos = gVentre.attributes.position;
    for (let i = 0; i < pos.count; i++) { const x = pos.getX(i) * 1.22, y = 1.5 + pos.getY(i) * 1.5; const sf = surface(x, y, .012); pos.setXYZ(i, x, y, pos.getZ(i) >= 0 ? sf.p.z : sf.p.z - .02); }
    gVentre.computeVertexNormals(); peau(gVentre, (x, y) => [0, 1, 1 - lisse(1.0, 3.0, y)]); }
  const ventre = new THREE.SkinnedMesh(gVentre, mats.ventre); ventre.name = 'ventre'; racine.add(ventre);

  // ---------------- tête : yeux, paupières, sourcils, museau, nez, dents, bouche, joues, oreilles ----------------
  const visage = new THREE.Group(); visage.name = 'visage'; bTorse.add(visage);
  const yeux = {};
  for (const [cote, s] of [['G', -1], ['D', 1]]) {
    const { p, n } = surface(s * DIM.yeux.x, DIM.yeux.y, -.06);
    const pivot = os_('oeil' + cote, bTorse, p.x, p.y - 1.6, p.z);
    orienter(pivot, n);
    const blanc = new THREE.Mesh(new THREE.SphereGeometry(1, 40, 28), mats.oeil); blanc.scale.set(DIM.yeux.rx, DIM.yeux.ry, .2); blanc.name = 'oeil' + cote + '_blanc'; avecContour(blanc, .7); pivot.add(blanc);
    const pupille = new THREE.Group(); pupille.name = 'pupille' + cote; pivot.add(pupille);
    const pu = new THREE.Mesh(new THREE.SphereGeometry(1, 32, 20), mats.pupille); pu.scale.set(.19, .22, .06); pu.position.z = .17; pupille.add(pu);
    const r1 = new THREE.Mesh(new THREE.SphereGeometry(.075, 16, 12), mats.reflet); r1.position.set(.07, .09, .23); pupille.add(r1);
    const r2 = new THREE.Mesh(new THREE.SphereGeometry(.03, 12, 8), mats.reflet); r2.position.set(-.06, -.08, .23); pupille.add(r2);
    // paupières : calottes de fourrure qui pivotent autour de l'axe horizontal de l'œil
    const calotte = (haut) => { const g = new THREE.SphereGeometry(1.08, 40, 20, 0, Math.PI * 2, 0, Math.PI / 2); g.rotateX(haut ? Math.PI / 2 : -Math.PI / 2);   // pôle vers l'avant (haut) ou l'arrière (bas)
      const m = new THREE.Mesh(g, mats.poil); m.scale.set(DIM.yeux.rx, DIM.yeux.ry, .22);
      const piv = new THREE.Group(); piv.add(m); avecContour(m, .6); pivot.add(piv); return piv; };
    const paupH = calotte(true), paupB = calotte(false); paupH.name = 'paupiereHaut' + cote; paupB.name = 'paupiereBas' + cote;
    // sourcil : bande épaisse courbe
    const courbe = new THREE.QuadraticBezierCurve3(new THREE.Vector3(-.34, -.06, 0), new THREE.Vector3(0, .12, .05), new THREE.Vector3(.34, -.04, 0));
    const sourcil = new THREE.Mesh(new THREE.TubeGeometry(courbe, 24, .065, 10, false), mats.trait); sourcil.name = 'sourcil' + cote;
    const pivS = new THREE.Group(); pivS.name = 'sourcilPivot' + cote; pivS.add(sourcil);
    const sp = surface(s * .5, 4.28, .06); pivS.position.set(sp.p.x, sp.p.y - 1.6, sp.p.z); orienter(pivS, sp.n);
    bTorse.add(pivS);
    yeux[cote] = { pivot, pupille, paupH, paupB, sourcil: pivS, sy: sp.p.y - 1.6 };
  }
  // museau, nez, dents
  const museauZ = surface(0, DIM.museau.y).p.z;
  const museau = maillage(new THREE.SphereGeometry(1, 48, 32), mats.museau, 'museau'); museau.scale.set(DIM.museau.rx, DIM.museau.ry, .5); surTorse(museau, 0, DIM.museau.y, museauZ - .12);
  // surface avant du museau, prolongée par celle du corps hors de l'ellipse (la bouche peut déborder sous le museau)
  const zMus = (x, y) => { const q = 1 - (x / DIM.museau.rx) ** 2 - ((y - DIM.museau.y) / DIM.museau.ry) ** 2; return Math.max(q > 0 ? museauZ - .12 + .5 * Math.sqrt(q) : -9, surface(x, y).p.z); };
  const nez = maillage(new THREE.SphereGeometry(1, 32, 24), mats.nez, 'nez'); nez.scale.set(.36, .23, .2); surTorse(nez, 0, 3.08, zMus(0, 3.08) + .02);
  const refletNez = new THREE.Mesh(new THREE.SphereGeometry(1, 16, 10), new THREE.MeshBasicMaterial({ color: 0xffffff, transparent: true, opacity: .45 })); refletNez.scale.set(.11, .06, .04); surTorse(refletNez, -.12, 3.17, zMus(-.12, 3.17) + .19);
  const dents = new THREE.Group(); dents.name = 'dents'; surTorse(dents, 0, 2.46, zMus(0, 2.6) - .02);
  for (const x of [-.155, .155]) { const d = maillage(new THREE.CapsuleGeometry(.12, .3, 6, 16), new THREE.MeshBasicMaterial({ color: C.dents }), 'dent'); d.scale.set(1.15, 1, .45); d.position.set(x, 0, 0); dents.add(d); }
  // bouches : formes plaquées sur le museau, une seule visible à la fois (mêmes variantes que la 2D)
  const bouches = new THREE.Group(); bouches.name = 'bouches'; bTorse.add(bouches);
  const plaque = (shape, mat, dz = .035) => { const g = new THREE.ShapeGeometry(shape, 24); const pos = g.attributes.position;
    for (let i = 0; i < pos.count; i++) { const x = pos.getX(i), y = 2.68 + pos.getY(i); pos.setXYZ(i, x, y - 1.6, zMus(x, y) + dz); } g.computeVertexNormals(); const m = new THREE.Mesh(g, mat); m.renderOrder = 2; return m; };
  const trace = (pts2, ep = .07) => { const c = new THREE.CatmullRomCurve3(pts2.map(([x, y]) => new THREE.Vector3(x, 2.68 + y - 1.6, zMus(x, 2.68 + y) + .03))); return new THREE.Mesh(new THREE.TubeGeometry(c, 32, ep / 2, 8, false), mats.trait); };
  const ellipse = (cx, cy, rx, ry) => { const s = new THREE.Shape(); s.absellipse(cx, cy, rx, ry, 0, Math.PI * 2); return s; };
  const formeBouche = (l, prof) => { const s = new THREE.Shape(); s.moveTo(-l, .04); s.bezierCurveTo(-l * .9, -prof, l * .9, -prof, l, .04); s.closePath(); return s; };
  const defB = {
    sourire: () => [trace([[-.46, 0], [-.25, -.2], [0, -.24], [.25, -.2], [.46, 0]])],
    ouvert: () => [plaque(formeBouche(.44, .74), mats.bouche), plaque(ellipse(0, -.5, .22, .1), mats.langue, .042)],
    rire: () => [plaque(formeBouche(.56, .92), mats.bouche), plaque(ellipse(0, -.62, .3, .13), mats.langue, .042)],
    o: () => [plaque(ellipse(0, -.3, .2, .3), mats.bouche)],
    cri: () => [plaque(formeBouche(.44, 1.12), mats.bouche), plaque(ellipse(0, -.8, .22, .12), mats.langue, .042)],
    moue: () => [trace([[-.34, -.22], [0, -.1], [.34, -.22]])],
    plat: () => [trace([[-.3, -.14], [.3, -.14]])],
    grimace: () => { const s = new THREE.Shape(); s.moveTo(-.5, -.08); s.bezierCurveTo(-.3, .06, .3, .06, .5, -.08); s.bezierCurveTo(.3, -.22, -.3, -.22, -.5, -.08); return [plaque(s, mats.bouche)]; },
    baille: () => [plaque(ellipse(0, -.4, .34, .5), mats.bouche), plaque(ellipse(0, -.72, .2, .11), mats.langue, .042)],
  };
  for (const [k, f] of Object.entries(defB)) { const g = new THREE.Group(); g.name = 'bouche_' + k; f().forEach(m => g.add(m)); g.visible = k === 'sourire'; bouches.add(g); variantes[k] = g; }
  // moustaches, joues
  for (const s of [-1, 1]) for (let k = 0; k < 3; k++) {
    const a = new THREE.Vector3(s * .7, 2.74 - .44 - k * .14 + .2, 0), b = new THREE.Vector3(s * 1.16, 2.74 - .34 - k * .04 + .2 - k * .06, 0);
    a.z = zMus(a.x, a.y) + .01; b.z = surface(b.x, b.y, .02).p.z;
    const m = new THREE.Mesh(new THREE.TubeGeometry(new THREE.LineCurve3(a.clone().setY(a.y - 1.6), b.clone().setY(b.y - 1.6)), 4, .016, 6), mats.trait); m.name = 'moustache'; bTorse.add(m);
  }
  for (const s of [-1, 1]) { const { p, n } = surface(s * .92, 2.96, .015); const j = new THREE.Mesh(new THREE.CircleGeometry(1, 32), mats.joue); j.scale.set(.3, .18, 1); j.position.set(p.x, p.y - 1.6, p.z); orienter(j, n); j.name = 'joue'; bTorse.add(j); }
  // oreilles
  for (const s of [-1, 1]) { const o = maillage(new THREE.SphereGeometry(.34, 28, 20), mats.poil, 'oreille'); o.scale.set(1, 1, .55); surTorse(o, s * 1.2, 4.4, .25);
    const i = new THREE.Mesh(new THREE.SphereGeometry(.17, 20, 14), mats.poilOmbre); i.scale.set(1, 1, .4); i.position.z = .28; o.add(i); }

  // ---------------- casque de chantier ----------------
  const casque = new THREE.Group(); casque.name = 'casque'; const bCasque = os_('casque', bTorse, 0, 4.52 - 1.6, -.02); bCasque.add(casque);
  const dome = maillage(new THREE.SphereGeometry(1, 56, 28, 0, Math.PI * 2, 0, Math.PI / 2), mats.casque, 'casque_dome'); dome.scale.set(1.56, 1.42, 1.36); casque.add(dome);
  // bord : profil galbé (lathe) qui retombe légèrement sur l'extérieur, comme le dessin 2D
  { const prof = [[1.36, .1], [1.56, .075], [1.7, .02], [1.77, -.05], [1.76, -.1], [1.66, -.1], [1.5, -.04], [1.36, -.01]].map(([x, y]) => new THREE.Vector2(x < 1.4 ? x : 1.36 + (x - 1.36) * 1.55, y * 1.2));
    const g = new THREE.LatheGeometry(prof, 72); const pos = g.attributes.position; for (let i = 0; i < pos.count; i++) pos.setZ(i, pos.getZ(i) * .9); g.computeVertexNormals();
    const bord = maillage(g, mats.casqueOmbre, 'casque_bord'); bord.position.set(0, .02, .1); casque.add(bord); }
  const arete = maillage(new THREE.TorusGeometry(1, .14, 12, 48, Math.PI), mats.casqueClair, 'casque_arete'); arete.rotation.y = Math.PI / 2; arete.scale.set(1.36, 1.46, 1.4); casque.add(arete);
  { const cv = document.createElement('canvas'); cv.width = cv.height = 256; const x = cv.getContext('2d');
    x.fillStyle = '#15181e'; x.beginPath(); x.arc(128, 128, 124, 0, Math.PI * 2); x.fill(); x.fillStyle = '#ffc21a'; x.font = '800 150px Bricolage, Arial Black, sans-serif'; x.textAlign = 'center'; x.textBaseline = 'middle'; x.fillText('A', 128, 140);
    const tx = new THREE.CanvasTexture(cv); tx.colorSpace = THREE.SRGBColorSpace;
    const badge = new THREE.Mesh(new THREE.CircleGeometry(.22, 40), new THREE.MeshBasicMaterial({ map: tx, transparent: true })); badge.name = 'casque_badge';
    const ang = -.5, el = .55; const dir = new THREE.Vector3(Math.sin(ang) * Math.cos(el), Math.sin(el), Math.cos(ang) * Math.cos(el));
    badge.position.set(dir.x * 1.565, dir.y * 1.425, dir.z * 1.365); badge.lookAt(badge.position.clone().add(new THREE.Vector3(dir.x / 1.56, dir.y / 1.42, dir.z / 1.36))); casque.add(badge); }

  // ---------------- ceinture à outils, poche et téléphone, crayon ----------------
  { const band = profil.filter(p => p[1] > .74 && p[1] < 1.16).map(p => new THREE.Vector2(p[0] + .035, p[1]));
    const g = new THREE.LatheGeometry(band, 64); const pos = g.attributes.position; for (let i = 0; i < pos.count; i++) { pos.setZ(i, pos.getZ(i) * PZ); pos.setY(i, pos.getY(i) - .6); } g.computeVertexNormals();
    const ceinture = new THREE.Mesh(g, mats.ceinture); ceinture.name = 'ceinture'; avecContour(ceinture, .6); bBassin.add(ceinture);
    const fz = surface(0, .95, .05).p.z;
    const boucle = maillage(new THREE.BoxGeometry(.52, .46, .08, 2, 2, 1), mats.boucle, 'boucle'); boucle.position.set(0, .95 - .6, fz); bBassin.add(boucle);
    const tr = new THREE.Mesh(new THREE.BoxGeometry(.28, .22, .1), mats.ceinture); tr.position.z = .01; boucle.add(tr);
    const ps = surface(1.0, .7, .1); const poche = maillage(new THREE.BoxGeometry(.48, .66, .2), mats.poche, 'poche'); poche.position.set(ps.p.x, .67 - .6, ps.p.z - .02); poche.rotation.y = Math.atan2(ps.n.x, ps.n.z); bBassin.add(poche);
    const telC = maillage(new THREE.BoxGeometry(.34, .58, .08), mats.tel, 'telCeinture'); telC.position.set(0, .3, .02); poche.add(telC); os.telCeinture = telC;
    const ec = new THREE.Mesh(new THREE.PlaneGeometry(.24, .4), mats.ecran); ec.position.z = .045; telC.add(ec);
    const cs = surface(-1.1, .98, .06); const crayon = new THREE.Group(); crayon.name = 'crayon'; crayon.position.set(cs.p.x, .98 - .6, cs.p.z); crayon.rotation.set(0, Math.atan2(cs.n.x, cs.n.z), .21); bBassin.add(crayon);
    const cc = maillage(new THREE.CylinderGeometry(.075, .075, .7, 6), mats.crayon, 'crayon_corps'); crayon.add(cc);
    const pointe = new THREE.Mesh(new THREE.ConeGeometry(.075, .16, 6), mats.museau); pointe.rotation.x = Math.PI; pointe.position.y = -.43; crayon.add(pointe); }

  // ---------------- membres : tubes souples habillés (skinning) ----------------
  // tube le long de -y : longueurs l1 (segment 1) et l2 (segment 2), profil de rayon r(t)
  const tube = (l1, l2, rayonF, nom, os3) => {
    const N = 40, R = 20, L = l1 + l2, pos = [], idx = [], si = [], sw = [];
    for (let i = 0; i <= N; i++) { const t = i / N, y = -t * L, r = rayonF(t);
      const w2 = lisse(l1 / L - .08, l1 / L + .08, t);   // pli doux au coude
      for (let j = 0; j <= R; j++) { const a = j / R * Math.PI * 2; pos.push(Math.cos(a) * r, y, Math.sin(a) * r); si.push(0, 1, 2, 0); sw.push(1 - w2, w2, 0, 0); } }
    for (let i = 0; i < N; i++) for (let j = 0; j < R; j++) { const a = i * (R + 1) + j, b = a + R + 1; idx.push(a, a + 1, b, b, a + 1, b + 1); }
    const g = new THREE.BufferGeometry(); g.setAttribute('position', new THREE.Float32BufferAttribute(pos, 3)); g.setIndex(idx); g.computeVertexNormals();
    g.setAttribute('skinIndex', new THREE.Uint16BufferAttribute(si, 4)); g.setAttribute('skinWeight', new THREE.Float32BufferAttribute(sw, 4));
    const m = new THREE.SkinnedMesh(g, mats.poil); m.name = nom; avecContour(m); return m;
  };
  const membres = [];
  // bras : épaule → coude → poignet ; profil (diamètres 2D) : épaule 50, biceps, coude 40, avant-bras 44, poignet 30
  const rBras = t => (t < .5 ? .25 - .05 * Math.pow(t / .5, 1.3) + .015 * Math.sin(t / .5 * Math.PI) : .2 + .02 * Math.sin((t - .5) / .5 * Math.PI) - .05 * Math.pow((t - .5) / .5, 2.2));
  const mains = {};
  for (const [cote, s] of [['G', -1], ['D', 1]]) {
    const ep = os_('epaule' + cote, bTorse, s * DIM.epaule.x, DIM.epaule.y - 1.6, .05);
    const co = os_('coude' + cote, ep, 0, -DIM.bras.haut, 0);
    const po = os_('poignet' + cote, co, 0, -DIM.bras.avant, 0);
    const m = tube(DIM.bras.haut, DIM.bras.avant, rBras, 'bras' + cote); m.position.copy(ep.position); m.position.y += 1.6; membres.push({ m, os: [ep, co, po], parent: bTorse });
    const cap = maillage(new THREE.SphereGeometry(.25, 24, 16), mats.poil, 'epauleBoule' + cote); ep.add(cap);
    mains[cote] = creerMain(cote, s, po);
  }
  // jambes : hanche → genou → cheville, pieds plats
  for (const [cote, s] of [['G', -1], ['D', 1]]) {
    const ha = os_('hanche' + cote, bBassin, s * DIM.hanche.x, DIM.hanche.y - .6, 0);
    const ge = os_('genou' + cote, ha, 0, -DIM.jambe.haut, 0);
    const ch = os_('cheville' + cote, ge, 0, -DIM.jambe.bas, 0);
    const m = tube(DIM.jambe.haut, DIM.jambe.bas, t => .27 - .02 * t, 'jambe' + cote); membres.push({ m, os: [ha, ge, ch], parent: bBassin });
    const pied = new THREE.Group(); pied.name = 'pied' + cote; ch.add(pied);
    const fp = maillage(new THREE.SphereGeometry(1, 32, 20), mats.pied, 'pied' + cote + '_forme'); fp.scale.set(.36, .15, .58); fp.position.set(s * .1, .13, 1.0); pied.add(fp);
    for (let k = 0; k < 3; k++) { const o = new THREE.Mesh(new THREE.CapsuleGeometry(.025, .06, 4, 8), mats.trait); o.rotation.x = Math.PI / 2; o.position.set(s * .1 + (k - 1) * .14, .1, 1.54); o.rotation.z = 0; pied.add(o); }
    os['pied' + cote] = pied;
  }
  // queue en pagaie : 3 os, plaque épaisse avec croisillons
  const bQ0 = os_('queue0', bBassin, .7, .4 - .6, -.85), bQ1 = os_('queue1', bQ0, 0, 0, -1.0), bQ2 = os_('queue2', bQ1, 0, 0, -1.0);
  bQ0.rotation.set(.1, -1.35, 0);
  { const s = new THREE.Shape(); s.moveTo(-.3, 0); s.bezierCurveTo(-.55, -.6, -.6, -2.3, -.1, -3.0); s.bezierCurveTo(.2, -3.4, .9, -3.2, .96, -2.5); s.bezierCurveTo(1.04, -1.6, .6, -.4, .3, 0); s.closePath();
    const g = new THREE.ExtrudeGeometry(s, { depth: .1, bevelEnabled: true, bevelThickness: .06, bevelSize: .06, bevelSegments: 3, curveSegments: 32 });
    g.rotateX(Math.PI / 2); g.translate(-.35, .05, 0); g.rotateZ(.55);   // à plat vers -z, inclinée pour montrer sa face
    // subdivision pour le pliage : recalcul des poids selon la distance le long de -z
    const pos = g.attributes.position, si = [], sw = [];
    for (let i = 0; i < pos.count; i++) { const d = -pos.getZ(i); const k = d < 1 ? 0 : d < 2 ? 1 : 2, f = d < 1 ? d : d < 2 ? d - 1 : d - 2; const w2 = lisse(.7, 1.0, f) * (k < 2 ? 1 : 0); si.push(k, Math.min(2, k + 1), 0, 0); sw.push(1 - w2, w2, 0, 0); }
    g.setAttribute('skinIndex', new THREE.Uint16BufferAttribute(si, 4)); g.setAttribute('skinWeight', new THREE.Float32BufferAttribute(sw, 4));
    const cv = document.createElement('canvas'); cv.width = 512; cv.height = 512; const x = cv.getContext('2d'); x.fillStyle = '#5e3420'; x.fillRect(0, 0, 512, 512); x.strokeStyle = '#7d4a2c'; x.lineWidth = 9; x.lineCap = 'round';
    for (let i = 0; i < 9; i++) { x.beginPath(); x.moveTo(i * 64 - 40, 0); x.quadraticCurveTo(i * 64 + 10, 256, i * 64 - 40, 512); x.stroke(); }
    for (let i = 0; i < 9; i++) { x.beginPath(); x.moveTo(0, i * 64 + 20); x.quadraticCurveTo(256, i * 64 - 10, 512, i * 64 + 20); x.stroke(); }
    const tx = new THREE.CanvasTexture(cv); tx.wrapS = tx.wrapT = THREE.RepeatWrapping; tx.repeat.set(1.6, 1.6); tx.colorSpace = THREE.SRGBColorSpace;
    const mq = new THREE.SkinnedMesh(g, toon(0xffffff, { map: tx })); mq.name = 'queue'; avecContour(mq);
    membres.push({ m: mq, os: [bQ0, bQ1, bQ2], parent: bBassin, local: true }); }

  // attache des maillages habillés : liés après que le squelette est en place (pose de repos)
  racine.updateMatrixWorld(true);
  corps.bind(skCorps); if (corps.userData.contourSkin) corps.userData.contourSkin.bind(skCorps); ventre.bind(skCorps);
  for (const { m, os: ob, local } of membres) {
    const sk = new THREE.Skeleton(ob); racine.add(m);
    if (local) { m.position.copy(ob[0].getWorldPosition(new THREE.Vector3())); m.quaternion.copy(ob[0].getWorldQuaternion(new THREE.Quaternion())); }
    else { m.position.copy(ob[0].getWorldPosition(new THREE.Vector3())); m.quaternion.copy(ob[0].getWorldQuaternion(new THREE.Quaternion())); }
    m.updateMatrixWorld(true); m.bind(sk, m.matrixWorld);
    if (m.userData.contourSkin) { const c = m.userData.contourSkin; c.position.copy(m.position); c.quaternion.copy(m.quaternion); racine.add(c); c.updateMatrixWorld(true); c.bind(sk, m.matrixWorld); }
  }

  // ---------------- mains : paume, 4 doigts à 3 phalanges, pouce opposable ----------------
  function creerMain(cote, s, poignet) {
    const main = os_('main' + cote, poignet, 0, -.04, 0);
    const paume = maillage(new THREE.SphereGeometry(1, 32, 24), mats.poil, 'paume' + cote); paume.scale.set(.27, .25, .17); paume.position.y = -.2; main.add(paume);
    const coussinet = new THREE.Mesh(new THREE.SphereGeometry(1, 20, 14), mats.poilClair); coussinet.scale.set(.15, .12, .05); coussinet.position.set(0, -.2, .14); main.add(coussinet);
    const doigts = [];
    const L = [[.12, .1, .08], [.14, .11, .09], [.13, .11, .085], [.1, .085, .07]], R = [.072, .075, .072, .062];
    for (let i = 0; i < 4; i++) {
      const xb = (-.165 + i * .11) * -s;    // auriculaire côté extérieur, index côté pouce
      const base = os_(`doigt${i + 1}_1${cote}`, main, xb, -.38 + Math.abs(i - 1.5) * .025, 0);
      base.rotation.z = (i - 1.5) * .12 * -s;
      const ph = [base]; let parent = base;
      for (let k = 0; k < 3; k++) {
        const seg = maillage(new THREE.CapsuleGeometry(R[i] * (1 - k * .08), L[i][k], 6, 12), mats.poil, `phalange${i + 1}_${k + 1}${cote}`); seg.position.y = -L[i][k] / 2; parent.add(seg);
        if (k < 2) { const b = os_(`doigt${i + 1}_${k + 2}${cote}`, parent, 0, -L[i][k], 0); ph.push(b); parent = b; }
      }
      doigts.push(ph);
    }
    const p1 = os_('pouce1' + cote, main, .2 * s, -.16, .06), p2 = os_('pouce2' + cote, p1, 0, -.12, 0), p3 = os_('pouce3' + cote, p2, 0, -.1, 0);
    [[p1, .12], [p2, .1], [p3, .08]].forEach(([b, l], k) => { const seg = maillage(new THREE.CapsuleGeometry(.078 * (1 - k * .06), l, 6, 12), mats.poil, 'pouce_ph' + (k + 1) + cote); seg.position.y = -l / 2; b.add(seg); });
    const prise = new THREE.Group(); prise.name = 'prise' + cote; prise.position.set(0, -.34, .2); main.add(prise);
    return { main, doigts, pouce: [p1, p2, p3], prise, s, objets: {} };
  }
  // objets tenus (prêts dans la prise de chaque main)
  for (const cote of ['G', 'D']) {
    const pr = mains[cote].prise, s = mains[cote].s;
    const tel = maillage(new THREE.BoxGeometry(.42, .86, .08), mats.tel, 'objet_tel' + cote); const ec = new THREE.Mesh(new THREE.PlaneGeometry(.35, .76), mats.ecran); ec.position.z = -.045; ec.rotation.y = Math.PI; tel.add(ec); tel.position.set(0, -.02, -.06); tel.visible = false; pr.add(tel);
    const tasse = new THREE.Group(); tasse.name = 'objet_tasse' + cote; const tc = maillage(new THREE.CylinderGeometry(.3, .26, .62, 32, 1, true), new THREE.MeshToonMaterial({ color: 0xffffff, gradientMap: rampe, side: THREE.DoubleSide }), 'tasse_corps'); tasse.add(tc);
    const fond = new THREE.Mesh(new THREE.CircleGeometry(.29, 32), mats.poilOmbre); fond.rotation.x = -Math.PI / 2; fond.position.y = .26; tasse.add(fond);
    const anse = maillage(new THREE.TorusGeometry(.17, .045, 10, 24, Math.PI * 1.3), mats.trait, 'tasse_anse'); anse.rotation.z = -Math.PI * .65 * s; anse.position.x = .3 * -s; tasse.add(anse);
    tasse.scale.setScalar(.8); tasse.position.set(0, -.05, .14); tasse.visible = false; pr.add(tasse);
    const truelle = new THREE.Group(); truelle.name = 'objet_truelle' + cote; const manche = maillage(new THREE.CylinderGeometry(.07, .08, .5, 12), mats.pied, 'truelle_manche'); truelle.add(manche);
    const lame = new THREE.Shape(); lame.moveTo(-.5, 0); lame.lineTo(.5, 0); lame.lineTo(0, -.86); lame.closePath();
    const gl = new THREE.ExtrudeGeometry(lame, { depth: .03, bevelEnabled: false }); const ml = maillage(gl, new THREE.MeshToonMaterial({ color: 0xcfd6de, gradientMap: rampe }), 'truelle_lame'); ml.position.set(0, -.3, -.015); truelle.add(ml);
    truelle.scale.setScalar(.6); truelle.rotation.z = Math.PI / 2; truelle.position.set(0, -.02, .02); truelle.visible = false; pr.add(truelle);
    mains[cote].objets = { tel, tasse, truelle };
  }

  // ---------------- contrôle : IK des bras et des jambes, mains, visage ----------------
  const v3 = (a) => new THREE.Vector3(...a);
  // résout l'IK à deux segments dans le repère du parent de l'épaule ; cible en coordonnées monde du personnage
  // IK à deux segments : le coude est placé dans le plan (épaule, cible, pôle), puis chaque os oriente son -y vers l'articulation suivante
  function ik2(o1, o2, o3, l1, l2, cible, pole, etirement = 1.06) {
    racine.updateMatrixWorld(true);
    const inv = new THREE.Matrix4().copy(o1.parent.matrixWorld).invert();
    const T = v3(cible).applyMatrix4(racine.matrixWorld).applyMatrix4(inv);
    const P = pole.clone().transformDirection(racine.matrixWorld).transformDirection(inv);
    const S = o1.position.clone(), d = T.clone().sub(S), n = d.length() || 1e-6, dir = d.clone().divideScalar(n);
    const k = Math.min(etirement, Math.max(1, n / ((l1 + l2) * .985))), L1 = l1 * k, L2 = l2 * k;
    o2.position.y = -L1; if (o3) o3.position.y = -L2;
    const D = Math.min(n, L1 + L2 - 1e-4), a = (L1 * L1 - L2 * L2 + D * D) / (2 * D), h = Math.sqrt(Math.max(0, L1 * L1 - a * a));
    const perp = P.clone().addScaledVector(dir, -P.dot(dir)); if (perp.lengthSq() < 1e-8) perp.set(0, 0, 1); perp.normalize();
    const E = S.clone().addScaledVector(dir, a).addScaledVector(perp, h);
    const Wp = S.clone().addScaledVector(dir, D);
    o1.quaternion.setFromUnitVectors(new THREE.Vector3(0, -1, 0), E.clone().sub(S).normalize());
    // vrille de l'épaule : l'axe local +x reste perpendiculaire au plan du coude (le pli se fait toujours dans le bon sens)
    const yAxe = E.clone().sub(S).normalize().negate(), xVoulu = new THREE.Vector3().crossVectors(perp, yAxe.clone().negate()).normalize();
    const xAct = new THREE.Vector3(1, 0, 0).applyQuaternion(o1.quaternion);
    let ang = Math.atan2(new THREE.Vector3().crossVectors(xAct, xVoulu).dot(yAxe), xAct.dot(xVoulu));
    o1.quaternion.premultiply(new THREE.Quaternion().setFromAxisAngle(yAxe, ang));
    const dLoc = Wp.clone().sub(E).normalize().applyQuaternion(o1.quaternion.clone().invert());
    o2.quaternion.setFromUnitVectors(new THREE.Vector3(0, -1, 0), dLoc);
  }
  function ikBras(cote, cible, coude = 1) {
    const s = cote === 'D' ? 1 : -1;
    const pole = coude >= 0 ? new THREE.Vector3(s, -.35, -.35) : new THREE.Vector3(s * .35, -1, -.25);
    ik2(os['epaule' + cote], os['coude' + cote], os['poignet' + cote], DIM.bras.haut, DIM.bras.avant, cible, pole);
  }
  function ikJambe(cote, cible) {
    const ha = os['hanche' + cote], ge = os['genou' + cote], ch = os['cheville' + cote];
    ik2(ha, ge, ch, DIM.jambe.haut, DIM.jambe.bas, cible, new THREE.Vector3(0, 0, 1), 1);
    ch.quaternion.copy(ha.quaternion.clone().multiply(ge.quaternion).invert());   // pied à plat
  }
  // fermeture : 0 main ouverte … 1 poing ; formes prédéfinies (mêmes noms que la 2D)
  function poserMain(cote, m = {}) {
    const M = mains[cote], s = M.s, forme = m.forme || 'ouverte';
    const base = { ouverte: 0, salut: 0, poing: 1, tient: .85, pointe: 1, pouce: 1 }[forme] ?? 0;
    const f = m.fermeture ?? base, ecart = (m.ecart ?? (forme === 'salut' ? 1.6 : 1));
    M.doigts.forEach((ph, i) => {
      let fi = f; if (forme === 'pointe' && i === 3) fi = 0;   // index (côté pouce) tendu
      if (m.doigts && m.doigts[i] != null) fi = m.doigts[i];
      ph[0].rotation.set(-fi * 1.45, 0, (i - 1.5) * .12 * -s * ecart * (1 - fi * .7));
      ph[1].rotation.set(-fi * 1.55, 0, 0); ph[2].rotation.set(-fi * 1.25, 0, 0);
    });
    const pf = m.pouceFermeture ?? (forme === 'pouce' ? 0 : forme === 'ouverte' || forme === 'salut' ? .1 : f);
    M.pouce[0].rotation.set(-.35 - pf * .8, 0, s * (.85 - pf * .75)); M.pouce[1].rotation.set(-pf * .5, 0, -s * pf * .35); M.pouce[2].rotation.set(-pf * .6, 0, 0);
    M.pouceHaut = forme === 'pouce';
    if (M.pouceHaut) { M.pouce[1].rotation.set(0, 0, 0); M.pouce[2].rotation.set(0, 0, 0); }
    for (const [k, o] of Object.entries(M.objets)) o.visible = m.objet === k;
    if (m.rotation) M.main.rotation.set(...m.rotation);
  }
  function poserVisage(v = {}) {
    for (const cote of ['G', 'D']) {
      const Y = yeux[cote], gx = v.regardX ?? 0, gy = v.regardY ?? 0, taille = v.oeil ?? 1;
      Y.pupille.position.set(gx * .16, gy * .18, 0); Y.pivot.scale.setScalar(taille);
      const ferme = Math.max(v.paupieres ?? .08, v.cligne ?? 0);
      // calotte haute : derrière l'œil (ouvert) → devant (fermé) ; calotte basse : remonte un peu au clignement complet
      Y.pupille.visible = ferme < .85;
      Y.paupH.rotation.x = -Math.PI * (1 - Math.min(1, ferme)) * .98; Y.paupB.rotation.x = -Math.max(0, (v.cligne ?? 0) - .5) * Math.PI * .5 - .05;
      const sc = cote === 'D' ? -1 : 1;
      Y.sourcil.position.y = Y.sy + (v.sourcils ?? 0) * .2 + (taille - 1) * .4;
      Y.sourcil.children[0].rotation.z = (v.froncer ?? 0) * .24 * sc;
    }
    for (const [k, g] of Object.entries(variantes)) g.visible = (v.bouche ?? 'sourire') === k;
    dents.scale.y = v.dents ?? 1;
    const dy = { ouvert: .02, rire: .04, cri: .06, baille: .06 }[v.bouche] ?? 0; dents.position.y = 2.46 - 1.6 - dy;
  }
  // pose complète. Cibles des mains et des pieds en coordonnées du personnage (unités).
  const REPOS = { mainG: [-2.05, 1.2, .25], mainD: [2.05, 1.2, .25] };
  function poser(p = {}) {
    racine.position.set(...(p.position || [0, 0, 0])); racine.rotation.y = p.rotationY ?? 0;
    bTorse.rotation.set((p.pencheAvant ?? 0), 0, -(p.penche ?? 0)); bTorse.scale.set(1, p.etire ?? 1, 1);
    bCasque.position.y = 4.52 - 1.6 + (p.casque ?? 0);
    bQ0.rotation.set(.1 + (p.queueLeve ?? 0), -1.35 - (p.queue ?? 0), 0); bQ1.rotation.x = (p.queueCourbe ?? .08); bQ2.rotation.x = (p.queueCourbe ?? .08);
    ikBras('G', p.brasG?.main || REPOS.mainG, p.brasG?.coude ?? 1);
    ikBras('D', p.brasD?.main || REPOS.mainD, p.brasD?.coude ?? 1);
    ikJambe('G', p.piedG || [-.8, .02, 0]); ikJambe('D', p.piedD || [.8, .02, 0]);
    poserMain('G', p.mainG); poserMain('D', p.mainD);
    if (os.telCeinture) os.telCeinture.visible = p.telCeinture ?? !(p.mainG?.objet === 'tel' || p.mainD?.objet === 'tel');
    poserVisage(p.visage);
    racine.updateMatrixWorld(true);
    for (const cote of ['G', 'D']) {
      const M = mains[cote];
      const versMonde = (obj, dirLocal, dirMonde) => {   // oriente dirLocal de obj vers dirMonde (repère du personnage)
        const qp = obj.parent.getWorldQuaternion(new THREE.Quaternion()), qr = racine.getWorldQuaternion(new THREE.Quaternion());
        const cible = dirMonde.clone().applyQuaternion(qr).applyQuaternion(qp.clone().invert());
        obj.quaternion.setFromUnitVectors(dirLocal, cible.normalize());
      };
      const [p1, p2, p3] = M.pouce;
      p1.position.copy(p1.userData.repos || (p1.userData.repos = p1.position.clone())); p2.position.y = -.12; p3.position.y = -.1;
      if (M.pouceHaut) {   // pouce levé : base remontée au-dessus du poing, phalanges un peu allongées, toujours vers le haut de l'écran
        const qp = M.main.getWorldQuaternion(new THREE.Quaternion()).invert(), haut = new THREE.Vector3(0, 1, 0).applyQuaternion(racine.getWorldQuaternion(new THREE.Quaternion())).applyQuaternion(qp);
        p1.position.addScaledVector(haut, .2); p2.position.y = -.15; p3.position.y = -.13;
        racine.updateMatrixWorld(true);
        versMonde(p1, new THREE.Vector3(0, -1, 0), new THREE.Vector3(.1 * M.s, 1, .2));
      }
      if (M.objets.tasse.visible) versMonde(M.objets.tasse, new THREE.Vector3(0, 1, 0), new THREE.Vector3(0, 1, 0));
    }
    racine.updateMatrixWorld(true);
  }
  poser({});
  return { racine, os, mats, mains, yeux, variantes, poser, poserMain, poserVisage, ikBras, rayon, surface, DIM };
}

// Poses de référence (reprennent la planche 2D)
export const POSES = {
  content: { visage: { bouche: 'sourire' } },
  panique: { visage: { bouche: 'cri', oeil: 1.3, paupieres: 0, sourcils: 1.4, froncer: 1 }, brasG: { main: [-1.6, 4.4, .6], coude: -1 }, brasD: { main: [1.6, 4.4, .6], coude: -1 }, mainG: { forme: 'salut' }, mainD: { forme: 'salut' }, casque: .25, queueLeve: .5 },
  epuise: { visage: { bouche: 'baille', paupieres: .62, sourcils: -.4, froncer: .6 }, penche: -.05, pencheAvant: .08, brasG: { main: [-2.0, 1.05, .3] }, brasD: { main: [2.0, 1.05, .3] } },
  declic: { visage: { bouche: 'o', oeil: 1.2, paupieres: 0, sourcils: 1.3 }, brasD: { main: [2.0, 4.6, .5] }, mainD: { forme: 'pointe' }, casque: .12 },
  joie: { visage: { bouche: 'rire', paupieres: 0, sourcils: 1 }, brasG: { main: [-2.2, 5.0, .4], coude: -1 }, brasD: { main: [2.2, 5.0, .4], coude: -1 }, mainG: { forme: 'poing' }, mainD: { forme: 'poing' }, etire: 1.04 },
  telephone: { visage: { bouche: 'ouvert', sourcils: .6, froncer: .8, regardX: .6, regardY: .2 }, brasD: { main: [1.55, 3.95, .9] }, mainD: { forme: 'tient', objet: 'tel', rotation: [0, 0, 0] } },
  pouce: { visage: { bouche: 'sourire' }, brasD: { main: [2.4, 3.2, .7] }, mainD: { forme: 'pouce' }, brasG: { main: [-1.9, 1.5, .8] }, mainG: { forme: 'tient', objet: 'tasse' } },
};

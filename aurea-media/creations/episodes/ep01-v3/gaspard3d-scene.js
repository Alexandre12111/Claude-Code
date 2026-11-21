// V5 : Gaspard 3D dans les scènes. Même interface que la classe 2D (svg(), poser(p)) : chaque appel à poser() rend le modèle
// 3D (mascotte-3d/gaspard3d.js) dans un canevas transparent, puis l'image prend la place exacte du Gaspard 2D dans le SVG.
// Les poses 2D déjà calées sur la voix sont traduites en poses 3D (mains, coudes, bouche, paupières, objets, queue...).
import * as THREE from 'three';
import { creerGaspard } from '../../mascotte-3d/gaspard3d.js';

// zone rendue, en coordonnées locales du Gaspard 2D (px, pieds en 0,0, y vers le bas)
const Z = { x: -430, y: -740, w: 960, h: 800 }, K = 2;          // K : suréchantillonnage (net même zoomé)
const rendu = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: true });
rendu.setPixelRatio(1); rendu.setSize(Z.w * K, Z.h * K, false); rendu.setClearColor(0x000000, 0); rendu.outputColorSpace = THREE.SRGBColorSpace;
const scene = new THREE.Scene();
scene.add(new THREE.HemisphereLight(0xfff4e0, 0x6b5440, 1.15));
const soleil = new THREE.DirectionalLight(0xffffff, 1.55); soleil.position.set(3, 6, 6); scene.add(soleil);
// caméra orthographique de face : 1 unité 3D = 100 px du dessin 2D, aucune déformation de perspective
const cam = new THREE.OrthographicCamera(Z.x / 100, (Z.x + Z.w) / 100, -Z.y / 100, -(Z.y + Z.h) / 100, .1, 100);
cam.position.set(0, 0, 30); cam.lookAt(0, 0, 0);
const G = creerGaspard(THREE, { epaisseurContour: .03 }); scene.add(G.racine);

const deg = Math.PI / 180;
const versBras = (m, cote, defaut) => {
  if (!m) return defaut;
  const y = -m.y / 100, x = m.x / 100;
  const z = y > 3.4 ? .85 : y > 2.4 ? .6 : .4;               // devant le visage quand la main monte
  return [x, y, z];
};
// traduction d'une pose 2D (paramètres de gaspard.js) en pose 3D (paramètres de gaspard3d.js)
function traduire(p) {
  const main = (cote) => {
    const forme = p['forme' + cote] || 'ouverte', objet = p['objet' + cote] || undefined;
    const r = { forme: objet ? 'tient' : forme, objet };
    if (p['ferme' + cote] != null && !objet) r.fermeture = forme === 'ouverte' || forme === 'salut' ? p['ferme' + cote] * .9 : Math.max(.6, p['ferme' + cote]);
    return r;
  };
  return {
    rotationY: (p.tourne ?? 0) - .2,
    penche: (p.pench ?? 0) * .6 * deg * 1.6, pencheAvant: 0, etire: 1,
    casque: -(p.casque ?? 0) / 100,
    queue: (p.queue ?? 0) * deg * .3, queueLeve: Math.max(-.1, -(p.queue ?? 0) * deg * .6),
    brasG: { main: versBras(p.mainG, 'G', [-2.05, 1.2, .3]), coude: -(p.coudeG ?? -1) },
    brasD: { main: versBras(p.mainD, 'D', [2.05, 1.2, .3]), coude: (p.coudeD ?? 1) },
    mainG: main('G'), mainD: main('D'),
    piedG: p.piedG ? [p.piedG.x / 100, -p.piedG.y / 100 + .02, 0] : undefined,
    piedD: p.piedD ? [p.piedD.x / 100, -p.piedD.y / 100 + .02, 0] : undefined,
    telCeinture: p.telCeinture ?? true,
    visage: { bouche: p.bouche || 'sourire', paupieres: p.paup ?? .1, cligne: p.cligne ?? 0, sourcils: p.sourcils ?? 0, froncer: p.froncer ?? 0,
      regardX: p.regardX ?? 0, regardY: -(p.regardY ?? 0), oeil: p.oeil ?? 1, dents: p.dents ?? 1 },
  };
}

class Gaspard3D {
  constructor(id) { this.id = id; }
  g(n) { return `${this.id}-${n}`; }
  svg() {
    const g = n => this.g(n);
    return `<g id="${g('root')}">
      <ellipse id="${g('ombre')}" cx="0" cy="4" rx="210" ry="30" fill="#2a1a10" opacity=".22"/>
      <image id="${g('img')}" x="${Z.x}" y="${Z.y}" width="${Z.w}" height="${Z.h}" preserveAspectRatio="none"/>
      <g id="${g('sueur')}" opacity="0" transform="translate(150,-420)"><path d="M0,-30 C14,-8 18,8 0,14 C-18,8 -14,-8 0,-30 Z" fill="#8fd3ff" stroke="#2a6f99" stroke-width="3"/><path d="M-5,-8 C-6,0 -5,6 0,8" stroke="#fff" stroke-width="4" fill="none"/></g>
    </g>`;
  }
  poser(p) {
    p = Object.assign({ x: 0, y: 0, s: 1, sx: 1, sy: 1, rot: 0, ombre: .22 }, p);
    const g = n => document.getElementById(this.g(n));
    g('root').setAttribute('transform', `translate(${p.x},${p.y}) rotate(${p.rot}) scale(${p.s * p.sx},${p.s * p.sy})`);
    g('ombre').setAttribute('opacity', p.ombre);
    g('sueur').setAttribute('opacity', p.sueur ?? 0);
    G.poser(traduire(p));
    rendu.render(scene, cam);
    g('img').setAttribute('href', rendu.domElement.toDataURL('image/webp', .93));
  }
}
window.Gaspard3D = Gaspard3D;
window.G3D = { G, traduire, rendu };
window.dispatchEvent(new Event('g3d-pret'));

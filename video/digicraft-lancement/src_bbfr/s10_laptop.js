// Version française : plan « portable » refait en plan héros premium. Mise en scène sombre (Ink, lueur Ultramarine),
// ordinateur portable moderne en perspective, accueil YouCraft en mode sombre, cartes d'interface flottantes en 3D,
// travelling lent puis plongée dans l'écran vers la scène suivante. Même créneau que la scène d'origine (12,5 → 14,65 s).
(function () {
  const { h, S, P, Ease, lerp, clamp, icon, spring } = E;
  const el = C.el;
  const INK = '#0B0B0F', STONE = '#F5F2EA', UM = '#1E3BFF', UMD = '#4F6BFF', LIME = '#D7FF3F', MUTED = '#9A968E';

  // Accueil YouCraft en mode sombre (taille virtuelle 1600 × 1000, mise à l'échelle de l'écran).
  function home(parent) {
    const v = el(parent, { left: '0', top: '0', width: '1600px', height: '1000px', overflow: 'hidden', transformOrigin: '0 0', fontFamily: 'DM Sans', color: STONE,
      background: 'radial-gradient(1100px 560px at 50% -8%, rgba(30,59,255,0.55), rgba(30,59,255,0) 70%), radial-gradient(700px 420px at 92% 108%, rgba(215,255,63,0.10), rgba(215,255,63,0) 70%), #0B0B0F' });
    el(v, { inset: '0', backgroundImage: 'linear-gradient(rgba(245,242,234,0.035) 1px, transparent 1px), linear-gradient(90deg, rgba(245,242,234,0.035) 1px, transparent 1px)', backgroundSize: '64px 64px',
      webkitMask: 'radial-gradient(ellipse 70% 60% at 50% 40%, #000, rgba(0,0,0,0) 80%)', mask: 'radial-gradient(ellipse 70% 60% at 50% 40%, #000, rgba(0,0,0,0) 80%)' });
    const sk = C.ycSketch(v, 760, 'rgba(245,242,234,0.07)');
    Object.assign(sk.style, { left: '420px', top: '40px' });
    el(v, { left: '56px', top: '40px' }, window.ycLogoSvg(40, true));
    el(v, { right: '56px', top: '38px', display: 'flex', alignItems: 'center', gap: '34px', fontSize: '19px', color: '#C9C5BC', fontWeight: '500' },
      `<span>Modèles</span><span>Clients</span><span>Tarifs</span><span style="height:44px;padding:0 22px;border-radius:22px;background:${LIME};color:${INK};font-weight:700;display:flex;align-items:center">Commencer</span>`);
    el(v, { left: '0', width: '1600px', top: '190px', display: 'flex', justifyContent: 'center' },
      `<span style="height:38px;padding:0 16px;border-radius:19px;border:1px solid rgba(245,242,234,0.16);background:rgba(245,242,234,0.05);display:flex;align-items:center;gap:10px;font-size:16px;color:#C9C5BC">${icon('sparkles', 16, 2, LIME)}Nouveau · assistant de création par l’intelligence artificielle</span>`);
    el(v, { left: '0', width: '1600px', top: '256px', textAlign: 'center', fontFamily: 'Plus Jakarta Sans', fontWeight: '800', fontSize: '86px', letterSpacing: '-0.035em', lineHeight: '1.04' },
      `Que voulez-vous<br><span style="color:${UMD}">créer</span> aujourd’hui ?`);
    el(v, { left: '0', width: '1600px', top: '455px', textAlign: 'center', fontSize: '23px', color: MUTED }, 'Décrivez votre idée. YouCraft crée l’application, prête pour votre équipe.');
    const box = el(v, { left: '300px', top: '530px', width: '1000px', height: '160px', borderRadius: '26px', background: 'rgba(245,242,234,0.05)', border: '1.5px solid rgba(79,107,255,0.65)',
      boxShadow: '0 0 0 6px rgba(30,59,255,0.10), 0 30px 90px rgba(30,59,255,0.35)', backdropFilter: 'blur(8px)' });
    const txt = el(box, { left: '32px', top: '30px', right: '110px', fontSize: '25px', lineHeight: '1.4', color: STONE });
    el(box, { left: '32px', bottom: '24px', display: 'flex', gap: '18px', alignItems: 'center', fontSize: '16px', color: MUTED }, `${icon('file-plus', 20, 2, MUTED)}${icon('image', 20, 2, MUTED)}<span>Joindre une procédure</span>`);
    el(box, { right: '22px', bottom: '22px', width: '58px', height: '58px', borderRadius: '29px', background: LIME, display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 10px 30px rgba(215,255,63,0.25)' }, icon('arrow-up-right', 26, 2.6, INK));
    const chips = el(v, { left: '0', width: '1600px', top: '720px', display: 'flex', justifyContent: 'center', gap: '14px' });
    const C1 = [['receipt', 'Notes de frais'], ['target', 'Pipeline commercial'], ['clipboard-check', 'Audits fournisseurs'], ['users', 'Accueil des recrues']].map(([ic, t]) =>
      h('span', '', chips, `${icon(ic, 16, 2, UMD)}${t}`));
    C1.forEach((c) => Object.assign(c.style, { height: '42px', padding: '0 18px', borderRadius: '21px', border: '1px solid rgba(245,242,234,0.14)', background: 'rgba(245,242,234,0.04)', display: 'flex', alignItems: 'center', gap: '9px', fontSize: '16px', color: '#D9D5CC' }));
    // Modèles en bas de page, à moitié visibles : invitation à faire défiler.
    const row = el(v, { left: '230px', top: '812px', width: '1140px', height: '260px' });
    el(row, { left: '0', top: '0', fontSize: '15px', fontWeight: '600', letterSpacing: '0.12em', color: MUTED }, 'MODÈLES POPULAIRES');
    const T = [0, 1, 3].map((k, i) => {
      const t = U.dash(row, k, true);
      Object.assign(t.style, { boxShadow: '0 20px 50px rgba(0,0,0,0.5)' });
      S(t, { x: i * 390, y: 34, s: 1.28 });
      return t;
    });
    return { v, txt, C1, T, box };
  }

  // Carte d'interface flottante (verre sombre).
  function floatCard(parent, w, hh, html) {
    const c = el(parent, { left: '0', top: '0', width: w + 'px', height: hh + 'px', borderRadius: '18px', background: 'rgba(22,22,28,0.78)', border: '1px solid rgba(245,242,234,0.14)',
      boxShadow: '0 30px 80px rgba(0,0,0,0.55)', backdropFilter: 'blur(14px)', fontFamily: 'DM Sans', color: STONE, padding: '18px 20px', boxSizing: 'border-box' }, html);
    return c;
  }

  const a = {};
  replaceScene({
    id: 'laptop',
    build(root) {
      a.root = root;
      el(root, { inset: '0', background: INK });
      a.g1 = C.glow(root, 1800, 'rgba(30,59,255,0.42)', 1);
      a.g2 = C.glow(root, 1100, 'rgba(215,255,63,0.07)', 1);
      // Sol réfléchissant : dégradé et trame en perspective.
      a.floor = el(root, { left: '-200px', width: '2320px', top: '760px', height: '520px', background: 'linear-gradient(180deg, rgba(30,59,255,0.10), rgba(11,11,15,0) 70%)' });
      a.cam = el(root, { inset: '0', perspective: '2600px', perspectiveOrigin: '50% 38%' });
      a.rig = el(a.cam, { left: '0', top: '0', width: '1920px', height: '1080px', transformStyle: 'preserve-3d', transformOrigin: '960px 520px' });
      // Ordinateur portable : capot fin, écran bord à bord, encoche, socle en trapèze avec liseré lumineux.
      const W0 = 1160, H0 = 728, X0 = 960 - W0 / 2, Y0 = 150;
      a.lid = el(a.rig, { left: X0 + 'px', top: Y0 + 'px', width: W0 + 'px', height: H0 + 'px', borderRadius: '30px', background: 'linear-gradient(180deg,#26262D,#121216)', boxShadow: '0 0 0 1.5px rgba(245,242,234,0.10), 0 60px 140px rgba(0,0,0,0.65), 0 0 120px rgba(30,59,255,0.25)' });
      a.screen = el(a.lid, { left: '14px', top: '14px', right: '14px', bottom: '14px', borderRadius: '18px', overflow: 'hidden', background: INK });
      a.home = home(a.screen);
      a.k = (W0 - 28) / 1600;
      a.home.v.style.transform = `scale(${a.k})`;
      el(a.lid, { left: W0 / 2 - 60 + 'px', top: '14px', width: '120px', height: '20px', borderRadius: '0 0 12px 12px', background: '#121216' });
      a.gloss = el(a.screen, { inset: '0', background: 'linear-gradient(115deg, rgba(255,255,255,0.10) 0%, rgba(255,255,255,0.02) 28%, rgba(255,255,255,0) 45%)', pointerEvents: 'none' });
      a.base = el(a.rig, { left: X0 - 90 + 'px', top: Y0 + H0 - 6 + 'px', width: W0 + 180 + 'px', height: '34px', clipPath: 'polygon(4% 0, 96% 0, 100% 62%, 98.6% 100%, 1.4% 100%, 0 62%)',
        background: 'linear-gradient(180deg,#3A3A42 0%,#1C1C22 55%,#0E0E12 100%)' });
      el(a.base, { left: '44%', width: '12%', top: '0', height: '7px', borderRadius: '0 0 8px 8px', background: '#0E0E12' });
      el(a.base, { left: '4%', right: '4%', top: '0', height: '1.5px', background: 'linear-gradient(90deg, rgba(245,242,234,0), rgba(245,242,234,0.45), rgba(245,242,234,0))' });
      a.shadow = el(a.rig, { left: X0 - 40 + 'px', top: Y0 + H0 + 18 + 'px', width: W0 + 80 + 'px', height: '70px', borderRadius: '50%', background: 'radial-gradient(closest-side, rgba(0,0,0,0.75), rgba(0,0,0,0))' });
      a.refl = el(a.rig, { left: X0 + 'px', top: Y0 + H0 + 40 + 'px', width: W0 + 'px', height: '200px', background: 'linear-gradient(180deg, rgba(30,59,255,0.16), rgba(30,59,255,0))', filter: 'blur(30px)' });
      // Cartes flottantes à différentes profondeurs.
      a.cards = [
        [floatCard(a.rig, 330, 96, `<div style="display:flex;gap:14px;align-items:center"><span style="width:44px;height:44px;border-radius:22px;background:rgba(28,140,78,0.2);display:flex;align-items:center;justify-content:center">${icon('circle-check', 24, 2.4, '#3DD68C')}</span><div><div style="font-size:16px;font-weight:700">Application publiée</div><div style="font-size:13.5px;color:${MUTED};margin-top:3px">Portail client · il y a 2 min</div></div></div>`), 175, 200, 220, -7],
        [floatCard(a.rig, 300, 150, `<div style="font-size:13.5px;color:${MUTED};display:flex;align-items:center;gap:8px">${icon('timer', 15, 2, LIME)}Temps de création moyen</div><div style="font-family:'Plus Jakarta Sans';font-size:38px;font-weight:800;margin-top:6px;letter-spacing:-0.02em">12 min</div><svg width="260" height="34" viewBox="0 0 260 34"><path d="M0 28 L40 24 L80 26 L120 16 L160 18 L200 8 L260 4" fill="none" stroke="${LIME}" stroke-width="3" stroke-linecap="round"/></svg>`), 1470, 260, 260, 6],
        [floatCard(a.rig, 300, 92, `<div style="display:flex;align-items:center;gap:12px"><div style="display:flex">${[0, 3, 9, 10].map((k, i) => `<img src="../assets/img/people/p${String([0, 3, 9, 16][i]).padStart(2, '0')}.jpg" style="width:38px;height:38px;border-radius:19px;object-fit:cover;border:2px solid #16161C;margin-left:${i ? -12 : 0}px">`).join('')}</div><div><div style="font-size:16px;font-weight:700">48 équipes</div><div style="font-size:13.5px;color:${MUTED};margin-top:2px">créent avec YouCraft</div></div></div>`), 140, 600, 240, 5],
      ].map(([c, x, y, z, r]) => ({ c, x, y, z, r }));
    },
    update(T) {
      const u = T - 12.5;
      a.root.style.opacity = String(P(T, 12.5, 0.35, Ease.inOutSine));
      S(a.g1, { x: 960 + Math.sin(T * 0.4) * 60, y: 130, s: 1 + 0.04 * Math.sin(T) });
      S(a.g2, { x: 1650, y: 980 });
      // Travelling : légère vue de haut et rotation qui se redressent, avancée lente, puis plongée dans l'écran.
      const settle = Ease.outCubic(clamp(u / 1.6));
      const push = Ease.inExpo(clamp((T - 13.95) / 0.65));
      const ry = lerp(-14, -3, settle), rx = lerp(12, 4, settle);
      const z = lerp(0.86, 0.96, settle) * lerp(1, 3.6, push);
      a.rig.style.transform = `translateY(${lerp(40, 0, settle) + 120 * push}px) scale(${z.toFixed(4)}) rotateX(${rx.toFixed(2)}deg) rotateY(${ry.toFixed(2)}deg)`;
      a.cam.style.filter = push > 0.25 ? `blur(${((push - 0.25) * 18).toFixed(1)}px)` : 'none';
      // Reflet qui glisse sur l'écran.
      S(a.gloss, { x: lerp(-200, 260, clamp(u / 2.1)) });
      // Contenu de l'écran : titre et boîte de saisie, frappe de l'invite, puces et modèles.
      const tp = P(T, 12.7, 0.6, Ease.outCubic);
      S(a.home.box, { y: (1 - tp) * 30, o: tp });
      const msg = 'Décrivez l’application dont votre équipe a besoin…';
      const n = Math.max(0, Math.min(msg.length, Math.floor((T - 12.95) * 30)));
      a.home.txt.innerHTML = `<span style="color:${n < msg.length ? STONE : '#C9C5BC'}">${msg.slice(0, n)}</span><span class="caret" style="color:${LIME}"></span>`;
      a.home.C1.forEach((c, i) => { const p = P(T, 13.05 + i * 0.07, 0.45, Ease.outQuint); S(c, { y: (1 - p) * 16, o: p }); });
      a.home.T.forEach((t, i) => { const p = P(T, 13.25 + i * 0.08, 0.6, Ease.outQuint); S(t, { x: i * 390, y: 34 + (1 - p) * 60, s: 1.28, o: p }); });
      // Cartes flottantes : arrivée en ressort, flottement lent, parallaxe.
      a.cards.forEach((k, i) => {
        const p = spring(T - 12.85 - i * 0.14, 160, 16);
        k.c.style.transform = `translate3d(${k.x}px,${(k.y + (1 - p) * 60 + Math.sin(T * 1.3 + i) * 8).toFixed(1)}px,${k.z}px) rotate(${k.r * 0.4}deg) scale(${Math.max(0.001, 0.85 + 0.15 * p).toFixed(3)})`;
        k.c.style.opacity = String(clamp(p * 1.3) * (1 - push));
      });
    },
  });
})();

// Site vitrine de l'artisan (fictif) « Atelier Morel », affiché dans le téléphone. Conçu à 390 px de large (mobile).
// Direction artistique : pierre claire, encre, laiton. Photos réelles (Unsplash). Animable via siteEtat().
const SITE_CSS = `
#site{position:relative;width:390px;height:844px;overflow:hidden;background:#f5f0e8;font-family:"Geist",sans-serif;color:#15181e;-webkit-font-smoothing:antialiased}
#site *{box-sizing:border-box}
#site .barre{position:absolute;left:0;right:0;top:0;height:50px;z-index:5;display:flex;align-items:center;justify-content:space-between;padding:14px 26px 0 30px;font:600 15px "Geist";color:#15181e;background:#f5f0e8}
#site .barre i{display:inline-block;margin-left:5px;background:#15181e;border-radius:2px}
#site .url{position:absolute;left:0;right:0;bottom:0;height:86px;z-index:6;background:rgba(245,240,232,.94);backdrop-filter:blur(8px);border-top:1px solid rgba(0,0,0,.06);display:flex;flex-direction:column;align-items:center;padding-top:10px}
#site .url .champ{width:350px;height:40px;border-radius:12px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.08);display:flex;align-items:center;justify-content:center;gap:7px;font:500 15px "Geist";color:#15181e}
#site .url .ligne{width:134px;height:5px;border-radius:3px;background:#15181e;margin-top:16px}
#site .defile{position:absolute;left:0;right:0;top:50px;will-change:transform}
#site .tete{height:64px;display:flex;align-items:center;justify-content:space-between;padding:0 20px;background:#f5f0e8}
#site .logo{display:flex;align-items:center;gap:10px}
#site .mono{width:38px;height:38px;border-radius:50%;background:#15181e;color:#d6ad62;display:grid;place-items:center;font:700 19px "Bricolage";letter-spacing:-.02em}
#site .logo b{display:block;font:800 18px/1 "Bricolage";letter-spacing:-.02em}
#site .logo small{display:block;font:500 11px/1.4 "Geist";color:#7a7166;letter-spacing:.08em;text-transform:uppercase;margin-top:3px}
#site .burger{width:40px;height:40px;border-radius:50%;border:1px solid #d9cfbf;display:grid;place-items:center}
#site .burger span{display:block;width:16px;height:2px;background:#15181e;box-shadow:0 5px 0 #15181e,0 -5px 0 #15181e}
#site .hero{position:relative;margin:0 12px;height:520px;border-radius:28px;overflow:hidden;background:#333}
#site .hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:50% 50%}
#site .hero:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,12,16,0) 30%,rgba(10,12,16,.82) 100%)}
#site .hero .note{position:absolute;z-index:2;top:16px;left:16px;display:flex;align-items:center;gap:6px;padding:8px 12px;border-radius:99px;background:rgba(255,255,255,.92);font:600 13px "Geist"}
#site .hero .note em{color:#c48a1a;font-style:normal;letter-spacing:1px}
#site .hero .txt{position:absolute;z-index:2;left:22px;right:22px;bottom:22px;color:#fff}
#site .eyebrow{font:600 11px "Geist";letter-spacing:.16em;text-transform:uppercase;color:#e8c886}
#site .hero h1{font:800 37px/1.02 "Bricolage";letter-spacing:-.035em;margin:10px 0 10px}
#site .hero p{font:400 15px/1.45 "Geist";color:rgba(255,255,255,.82);margin:0 0 18px}
#site .btns{display:flex;gap:10px}
#site .btn{height:50px;border-radius:99px;padding:0 17px;display:inline-flex;align-items:center;gap:7px;font:600 14.5px "Geist";white-space:nowrap}
#site .btn.or{background:#d6ad62;color:#15181e}
#site .btn.verre{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.35)}
#site .rassure{display:flex;gap:8px;padding:16px 12px 6px}
#site .rassure div{flex:1;background:#fff;border-radius:18px;padding:12px 10px;text-align:center;font:600 12px/1.25 "Geist";color:#3c372f;box-shadow:0 1px 0 rgba(0,0,0,.04)}
#site .rassure svg{display:block;margin:0 auto 6px;width:22px;height:22px}
#site section{padding:30px 20px 6px}
#site h2{font:800 28px/1.05 "Bricolage";letter-spacing:-.03em;margin:8px 0 6px}
#site .sous{font:400 14px/1.45 "Geist";color:#6d655a;margin:0 0 16px}
#site .carte{position:relative;height:250px;border-radius:24px;background:#15181e;overflow:hidden}
#site .villes{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
#site .villes span{padding:8px 13px;border-radius:99px;background:#fff;border:1px solid #e3d9c9;font:600 13px "Geist"}
#site .villes span.on{background:#15181e;color:#fff;border-color:#15181e}
#site .aa{position:relative;height:300px;border-radius:24px;overflow:hidden}
#site .aa img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
#site .aa .avant{clip-path:inset(0 50% 0 0)}
#site .aa .poignee{position:absolute;top:0;bottom:0;left:50%;width:3px;margin-left:-1.5px;background:#fff}
#site .aa .poignee b{position:absolute;top:50%;left:50%;width:44px;height:44px;margin:-22px 0 0 -22px;border-radius:50%;background:#fff;box-shadow:0 4px 14px rgba(0,0,0,.25);display:grid;place-items:center;font:700 16px "Geist"}
#site .aa .tag{position:absolute;top:14px;padding:6px 11px;border-radius:99px;font:700 11px "Geist";letter-spacing:.1em;text-transform:uppercase}
#site .aa .tag.g{left:14px;background:rgba(255,255,255,.9)}
#site .aa .tag.d{right:14px;background:#15181e;color:#d6ad62}
#site .grille{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px}
#site .grille figure{margin:0}
#site .grille img{width:100%;height:150px;object-fit:cover;border-radius:18px;display:block}
#site .grille figcaption{font:600 13px "Geist";margin-top:7px}
#site .grille figcaption span{display:block;font:400 12px "Geist";color:#7a7166}
#site .etapes{display:grid;gap:10px}
#site .etape{display:flex;gap:14px;align-items:center;background:#fff;border-radius:20px;padding:16px}
#site .etape .n{width:46px;height:46px;border-radius:14px;background:#efe6d6;display:grid;place-items:center;font:800 18px "Bricolage";color:#a87a2c;flex:none}
#site .etape b{display:block;font:700 16px "Geist"}
#site .etape span{font:400 13px "Geist";color:#6d655a}
#site .etape .duree{margin-left:auto;font:800 17px "Bricolage";color:#15181e;white-space:nowrap}
#site .form{background:#15181e;border-radius:28px;padding:22px 18px 20px;color:#fff;margin:0 -8px}
#site .form h2{color:#fff}
#site .form .sous{color:#a9a196}
#site .label{font:600 11px "Geist";letter-spacing:.14em;text-transform:uppercase;color:#9b9387;margin:14px 0 8px}
#site .chips{display:flex;gap:8px;flex-wrap:wrap}
#site .chips span{padding:10px 14px;border-radius:14px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);font:600 14px "Geist";color:#d7d0c5}
#site .chips span.on{background:#d6ad62;color:#15181e;border-color:#d6ad62}
#site .champ2{height:50px;border-radius:14px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);display:flex;align-items:center;padding:0 16px;font:500 15px "Geist";color:#fff}
#site .photos{display:flex;gap:8px}
#site .photos .ph{width:64px;height:64px;border-radius:14px;overflow:hidden;flex:none}
#site .photos .ph img{width:100%;height:100%;object-fit:cover}
#site .photos .plus{width:64px;height:64px;border-radius:14px;border:1.5px dashed rgba(255,255,255,.3);display:grid;place-items:center;font:300 28px "Geist";color:#9b9387}
#site .envoyer{margin-top:18px;height:56px;border-radius:99px;background:#d6ad62;color:#15181e;display:flex;align-items:center;justify-content:center;gap:10px;font:700 16px "Geist"}
#site .pied{padding:22px 20px 160px;text-align:center;font:500 12px "Geist";color:#9b9387}
#site .pied b{color:#15181e}
#site .collant{position:absolute;left:12px;right:12px;bottom:96px;z-index:4;height:58px;border-radius:99px;background:#15181e;display:flex;align-items:center;padding:5px;gap:6px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
#site .collant span{flex:1;height:48px;border-radius:99px;display:flex;align-items:center;justify-content:center;gap:8px;font:600 15px "Geist";color:#fff}
#site .collant span.or{background:#d6ad62;color:#15181e}
#site .ok{position:absolute;inset:0;z-index:8;background:rgba(21,24,30,.0);display:grid;place-items:center;pointer-events:none}
#site .ok div{width:300px;border-radius:28px;background:#fff;padding:28px 22px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.3)}
#site .ok i{display:grid;place-items:center;width:64px;height:64px;margin:0 auto 12px;border-radius:50%;background:#1f8f5a}
#site .ok b{display:block;font:800 22px/1.1 "Bricolage";letter-spacing:-.02em}
#site .ok span{display:block;font:400 14px/1.4 "Geist";color:#6d655a;margin-top:6px}
`;

const ico = {
  devis: '<svg viewBox="0 0 24 24" fill="none" stroke="#a87a2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>',
  garantie: '<svg viewBox="0 0 24 24" fill="none" stroke="#a87a2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/></svg>',
  propre: '<svg viewBox="0 0 24 24" fill="none" stroke="#a87a2c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18.5 9l-4.7 1.5L12 15l-1.8-4.5L5.5 9l4.7-1.4z"/><path d="M19 15l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8z"/></svg>',
  fleche: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
  tel: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>',
  cadenas: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#15181e" stroke-width="2.6" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>',
};

const SITE_HTML = `
<div id="site">
  <div class="barre"><span>9:41</span><span><i style="width:3px;height:6px"></i><i style="width:3px;height:8px"></i><i style="width:3px;height:10px"></i><i style="width:3px;height:12px"></i><i style="width:24px;height:11px;border-radius:3px;margin-left:8px"></i></span></div>
  <div class="defile" id="siteDefile">
    <div class="tete"><div class="logo"><div class="mono">M</div><div><b>Atelier Morel</b><small>Rénovation · Ouest parisien</small></div></div><div class="burger"><span></span></div></div>
    <div class="hero"><img src="photos/hero.webp" alt="">
      <div class="note"><em>★★★★★</em> 4,9 · 87 avis Google</div>
      <div class="txt"><div class="eyebrow">Salles de bain · Boulogne et 92</div><h1>Votre salle de bain, refaite en 3 semaines.</h1><p>Rénovation clé en main, un seul interlocuteur, un chantier propre du premier au dernier jour.</p>
        <div class="btns"><span class="btn or">Demander un devis ${ico.fleche}</span><span class="btn verre">Réalisations</span></div></div></div>
    <div class="rassure"><div>${ico.devis}Devis<br>sous 48 h</div><div>${ico.garantie}Garantie<br>décennale</div><div>${ico.propre}Chantier<br>propre</div></div>
    <section id="sZone"><div class="eyebrow" style="color:#a87a2c">Zone d’intervention</div><h2>On vient chez vous, dans tout l’ouest parisien.</h2>
      <div class="carte" id="sCarte">
        <svg viewBox="0 0 350 250" width="350" height="250" style="position:absolute;inset:0">
          <defs><radialGradient id="zg" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#d6ad62" stop-opacity=".35"/><stop offset="1" stop-color="#d6ad62" stop-opacity="0"/></radialGradient></defs>
          ${Array.from({ length: 9 }, (_, i) => `<path d="M${-20 + i * 46},0 L${30 + i * 46},250" stroke="#232833" stroke-width="1"/>`).join('')}${Array.from({ length: 7 }, (_, i) => `<path d="M0,${i * 40} L350,${i * 40 + 30}" stroke="#232833" stroke-width="1"/>`).join('')}
          <path d="M-10,60 C60,70 90,140 140,150 C200,162 210,90 260,80 C300,72 320,110 360,120" stroke="#3a5a80" stroke-width="14" fill="none" stroke-linecap="round"/>
          <circle id="sRayon" cx="168" cy="132" r="96" fill="url(#zg)" stroke="#d6ad62" stroke-width="1.5" stroke-dasharray="5 6"/>
          <g font-family="Geist" font-weight="600" font-size="11" fill="#cfc6b8">
            ${[['Boulogne', 168, 132, 1], ['Issy', 214, 168, 0], ['Neuilly', 150, 62, 0], ['Sèvres', 96, 160, 0], ['Clamart', 200, 210, 0], ['Paris 16e', 236, 96, 0]].map(([n, x, y, on]) => `<g transform="translate(${x},${y})"><circle r="${on ? 7 : 4}" fill="${on ? '#d6ad62' : '#cfc6b8'}"/>${on ? '<circle r="13" fill="none" stroke="#d6ad62" stroke-width="2" opacity=".6"/>' : ''}<text x="0" y="${on ? 26 : 18}" text-anchor="middle" fill="${on ? '#fff' : '#cfc6b8'}" font-size="${on ? 13 : 11}">${n}</text></g>`).join('')}
          </g>
        </svg></div>
      <div class="villes"><span class="on">Boulogne</span><span>Issy</span><span>Neuilly</span><span>Sèvres</span><span>Paris 15e et 16e</span></div></section>
    <section id="sReal"><div class="eyebrow" style="color:#a87a2c">Réalisations</div><h2>Avant, après. Jugez par vous-même.</h2>
      <div class="aa"><img src="photos/apres.webp" alt=""><img class="avant" id="sAvant" src="photos/avant.webp" alt="" style="object-position:50% 70%"><div class="poignee" id="sPoignee"><b>⇆</b></div><span class="tag g">Avant</span><span class="tag d">Après</span></div>
      <div class="grille"><figure><img src="photos/real1.webp" alt=""><figcaption>Double vasque marbre<span>Neuilly · 3 semaines</span></figcaption></figure><figure><img src="photos/real3.webp" alt=""><figcaption>Baignoire îlot<span>Boulogne · 4 semaines</span></figcaption></figure></div></section>
    <section id="sDelais"><div class="eyebrow" style="color:#a87a2c">Délais</div><h2>Des délais clairs, tenus.</h2>
      <div class="etapes"><div class="etape"><div class="n">1</div><div><b>Visite gratuite</b><span>On mesure, on conseille</span></div><div class="duree">J+2</div></div>
        <div class="etape"><div class="n">2</div><div><b>Devis détaillé</b><span>Ligne par ligne, sans surprise</span></div><div class="duree">48 h</div></div>
        <div class="etape"><div class="n">3</div><div><b>Travaux</b><span>Clé en main, chantier propre</span></div><div class="duree">3 sem.</div></div></div></section>
    <section id="sForm"><div class="form"><div class="eyebrow">Devis gratuit</div><h2>Votre devis en 2 minutes.</h2><p class="sous">Décrivez votre projet, ajoutez des photos : on vous rappelle avec un prix.</p>
      <div class="label">Type de travaux</div><div class="chips"><span class="on" id="sChip">Salle de bain ✓</span><span>Cuisine</span><span>Douche</span><span>Carrelage</span></div>
      <div class="label">Ville</div><div class="champ2" id="sVille">Boulogne-Billancourt</div>
      <div class="label">Photos de votre salle de bain</div><div class="photos">${['avant', 'real4', 'real5', 'real2'].map((p, i) => `<div class="ph" id="sPh${i}"><img src="photos/${p}.webp" alt=""></div>`).join('')}<div class="plus">+</div></div>
      <div class="envoyer" id="sEnvoyer">Recevoir mon devis ${ico.fleche}</div></div></section>
    <div class="pied">© Atelier Morel · Site réalisé par <b>Aurea Media</b></div>
  </div>
  <div class="collant" id="sCollant"><span>${ico.tel} Appeler</span><span class="or">Devis gratuit</span></div>
  <div class="url"><div class="champ">${ico.cadenas} ateliermorel.fr</div><div class="ligne"></div></div>
  <div class="ok" id="sOk"><div><i><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></i><b>Demande envoyée !</b><span>Atelier Morel vous rappelle sous 48 h avec votre devis.</span></div></div>
</div>`;

// Position des sections (en px de défilement) : mesurées au chargement
let SITE_Y = {};
function siteMesurer() {
  const base = $('siteDefile').getBoundingClientRect().top;
  for (const id of ['sZone', 'sReal', 'sDelais', 'sForm']) SITE_Y[id] = $(id).getBoundingClientRect().top - base;
}
// e : { defile, poignee (0..1), chip (0..1), photos (0..4), envoi (0..1), ok (0..1), rayon (0..1) }
function siteEtat(e) {
  $('siteDefile').style.transform = `translateY(${-e.defile}px)`;
  const p = e.poignee ?? .5;
  $('sAvant').style.clipPath = `inset(0 ${(1 - p) * 100}% 0 0)`;
  $('sPoignee').style.left = `${p * 100}%`;
  for (let i = 0; i < 4; i++) { const a = clamp((e.photos ?? 4) - i); $('sPh' + i).style.transform = `scale(${E.backS(a)})`; $('sPh' + i).style.opacity = clamp(a * 2); }
  $('sEnvoyer').style.transform = `scale(${1 - .06 * Math.sin(Math.PI * clamp(e.envoi ?? 0))})`;
  const ok = e.ok ?? 0;
  $('sOk').style.background = `rgba(21,24,30,${.45 * clamp(ok * 2)})`;
  $('sOk').firstElementChild.style.transform = `translateY(${(1 - E.backS(ok)) * 60}px) scale(${.6 + .4 * E.backS(ok)})`;
  $('sOk').firstElementChild.style.opacity = clamp(ok * 3);
  $('sRayon').setAttribute('r', 40 + 56 * E.backS(clamp(e.rayon ?? 1)));
  $('sCollant').style.transform = `translateY(${(e.collant ?? 0) * 160}px)`;
}

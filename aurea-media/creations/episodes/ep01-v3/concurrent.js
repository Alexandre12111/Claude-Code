// V4 : écran du téléphone de la prospecte (scène « le client que tu rates »). Deux écrans d'appel et le site premium
// du concurrent fictif « Rénov Express ». Conçu à 390 x 844 px comme site.js. Animable via concEtat().
const CONC_CSS = `
#conc,#appel{position:absolute;z-index:30;left:0;top:0;width:390px;height:844px;overflow:hidden;font-family:"Geist",sans-serif;-webkit-font-smoothing:antialiased;display:none}
#conc *,#appel *{box-sizing:border-box}
#conc{background:#f3eee6;color:#101826}
#conc .barre{position:absolute;left:0;right:0;top:0;height:50px;z-index:5;display:flex;align-items:center;justify-content:space-between;padding:14px 28px 0 32px;font:600 15px "Geist";color:#101826;background:#f3eee6}
#conc .defile{position:absolute;left:0;right:0;top:50px}
#conc .tete{height:62px;display:flex;align-items:center;justify-content:space-between;padding:0 20px}
#conc .logo{display:flex;align-items:center;gap:10px}
#conc .mono{width:38px;height:38px;border-radius:12px;background:#101826;display:grid;place-items:center}
#conc .mono i{width:16px;height:16px;border:3px solid #c8875a;border-radius:4px;transform:rotate(45deg)}
#conc .logo b{display:block;font:800 17px/1 "Bricolage";letter-spacing:.01em}
#conc .logo small{display:block;font:500 10.5px/1.4 "Geist";color:#7c7468;letter-spacing:.12em;text-transform:uppercase;margin-top:3px}
#conc .menu{font:600 13px "Geist";padding:9px 14px;border-radius:999px;background:#101826;color:#f3eee6}
#conc .hero{position:relative;margin:4px 12px 0;height:452px;border-radius:30px;overflow:hidden;background:#222}
#conc .hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
#conc .hero:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(8,12,20,.05) 20%,rgba(8,12,20,.82) 78%)}
#conc .avis{position:absolute;left:18px;top:18px;z-index:2;display:flex;align-items:center;gap:8px;padding:8px 13px 8px 10px;border-radius:999px;background:rgba(255,255,255,.92);font:600 13px "Geist";color:#101826}
#conc .avis .et{color:#e3a33b;letter-spacing:1px;font-size:13px}
#conc .txt{position:absolute;left:22px;right:22px;bottom:22px;z-index:2;color:#fff}
#conc .txt small{display:block;font:600 11px "Geist";letter-spacing:.14em;text-transform:uppercase;color:#e9c3a1;margin-bottom:10px}
#conc h1{margin:0 0 16px;font:800 33px/1.03 "Bricolage";letter-spacing:-.025em}
#conc .btns{display:flex;gap:9px}
#conc .btn{flex:1;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;gap:8px;font:700 15px "Geist"}
#conc .btn.appel{background:#fff;color:#101826}
#conc .btn.devis{background:#c8875a;color:#fff}
#conc .btn svg{width:17px;height:17px}
#conc .zones{display:flex;gap:7px;padding:16px 16px 0;flex-wrap:wrap}
#conc .zones span{font:600 12.5px "Geist";padding:8px 12px;border-radius:999px;background:#fff;border:1px solid #e3dacb;color:#101826}
#conc .zones span b{color:#3f8f5c;margin-right:4px}
#conc .reals{display:flex;gap:8px;padding:14px 12px 0}
#conc .reals div{flex:1;height:118px;border-radius:18px;overflow:hidden;position:relative}
#conc .reals img{width:100%;height:100%;object-fit:cover}
#conc .url{position:absolute;left:0;right:0;bottom:0;height:86px;z-index:6;background:rgba(243,238,230,.95);border-top:1px solid rgba(0,0,0,.06);display:flex;flex-direction:column;align-items:center;padding-top:10px}
#conc .url .champ{width:350px;height:40px;border-radius:12px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.08);display:flex;align-items:center;justify-content:center;gap:7px;font:500 15px "Geist";color:#101826}
#conc .url .ligne{width:134px;height:5px;border-radius:3px;background:#101826;margin-top:16px}
#appel{color:#fff;background:radial-gradient(120% 70% at 50% 20%,#3a4a6b 0%,#1a2234 55%,#0d1220 100%)}
#appel .barre{position:absolute;left:0;right:0;top:0;height:50px;display:flex;align-items:center;justify-content:space-between;padding:14px 28px 0 32px;font:600 15px "Geist"}
#appel .ava{position:absolute;left:50%;top:150px;width:132px;height:132px;margin-left:-66px;border-radius:50%;display:grid;place-items:center;font:800 46px "Bricolage";letter-spacing:-.02em}
#appel .onde{position:absolute;left:50%;top:216px;width:132px;height:132px;margin:-66px 0 0 -66px;border-radius:50%;border:2px solid rgba(255,255,255,.5)}
#appel .nom{position:absolute;left:0;right:0;top:308px;text-align:center;font:700 34px "Geist";letter-spacing:-.01em}
#appel .etat{position:absolute;left:0;right:0;top:356px;text-align:center;font:500 18px "Geist";color:#aeb8cc}
#appel .grille{position:absolute;left:46px;right:46px;top:500px;display:grid;grid-template-columns:repeat(3,1fr);gap:26px 0;justify-items:center}
#appel .grille div{width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.12);display:grid;place-items:center}
#appel .grille i{display:block;width:24px;height:24px;border-radius:7px;border:2.5px solid rgba(255,255,255,.85)}
#appel .fin{position:absolute;left:50%;bottom:92px;width:78px;height:78px;margin-left:-39px;border-radius:50%;background:#ff3b30;display:grid;place-items:center}
#appel .fin svg{width:36px;height:36px}
`;
const ICO_TEL = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 5a2 2 0 0 1 2-2"/></svg>';
const CONC_HTML = `
<div id="conc">
  <div class="barre"><span>18:42</span><span>●●● ▮</span></div>
  <div class="defile" id="concDefile">
    <div class="tete"><div class="logo"><div class="mono"><i></i></div><div><b>RÉNOV EXPRESS</b><small>Salles de bain · 92</small></div></div><div class="menu">Menu</div></div>
    <div class="hero"><img src="photos/real2.webp" alt="">
      <div class="avis"><span class="et">★★★★★</span>4,9 · 212 avis Google</div>
      <div class="txt"><small>Hauts-de-Seine · Devis sous 24 h</small><h1>Votre salle de bain rénovée en 3&nbsp;semaines.</h1>
        <div class="btns"><div class="btn appel" id="concAppel">${ICO_TEL}Appeler</div><div class="btn devis">Devis en 2 min</div></div></div></div>
    <div class="zones"><span><b>✓</b>Boulogne</span><span><b>✓</b>Issy</span><span><b>✓</b>Clamart</span><span><b>✓</b>Meudon</span></div>
    <div class="reals"><div><img src="photos/real3.webp" alt=""></div><div><img src="photos/real4.webp" alt=""></div><div><img src="photos/real5.webp" alt=""></div></div>
  </div>
  <div class="url"><div class="champ">🔒 renov-express.fr</div><div class="ligne"></div></div>
</div>
<div id="appel">
  <div class="barre"><span>18:42</span><span>●●● ▮</span></div>
  <div class="onde" id="apOnde0"></div><div class="onde" id="apOnde1"></div>
  <div class="ava" id="apAva">GB</div>
  <div class="nom" id="apNom">Gaspard BTP</div>
  <div class="etat" id="apEtat">Appel en cours…</div>
  <div class="grille">${'<div><i></i></div>'.repeat(6)}</div>
  <div class="fin"><svg viewBox="0 0 24 24" fill="#fff"><path d="M2 14.5c5.5-5 14.5-5 20 0l-2.6 2.6-3.6-1.6v-2.4a12 12 0 0 0-7.6 0v2.4l-3.6 1.6z"/></svg></div>
</div>`;

// e : ecran ('appel'|'site'), qui ('gaspard'|'renov'), etat (texte), etatCoul, defile (px), appui (0..1 bouton Appeler), t
function concEtat(e) {
  $('conc').style.display = e.site > 0 ? 'block' : 'none';
  $('site').style.visibility = (e.site > 0 || e.appel > 0) ? 'hidden' : 'visible';
  $('appel').style.display = e.appel > 0 ? 'block' : 'none';
  $('appel').style.transform = `translateX(${(e.appelX || 0) * 390}px)`;
  $('conc').style.transform = `translateX(${(e.siteX || 0) * 390}px)`;
  $('concDefile').style.transform = `translateY(${-(e.defile || 0)}px)`;
  $('concAppel').style.transform = `scale(${1 - .08 * (e.appui || 0)})`;
  $('concAppel').style.background = e.appui > .5 ? '#e9e4dc' : '#fff';
  const g = e.qui !== 'renov';
  $('apAva').textContent = g ? 'GB' : 'RE';
  $('apAva').style.background = g ? '#ffc21a' : '#c8875a';
  $('apAva').style.color = g ? '#3a2016' : '#fff';
  $('apNom').textContent = g ? 'Gaspard BTP' : 'Rénov Express';
  $('apEtat').textContent = e.etat; $('apEtat').style.color = e.etatCoul || '#aeb8cc';
  for (let i = 0; i < 2; i++) {
    const k = (((e.t || 0) * .9 + i * .5) % 1), on = e.ondes ?? 1;
    $('apOnde' + i).style.transform = `scale(${1 + k * .9})`; $('apOnde' + i).style.opacity = (1 - k) * .7 * on;
    $('apOnde' + i).style.borderColor = g ? 'rgba(255,255,255,.5)' : 'rgba(126,224,160,.8)';
  }
}

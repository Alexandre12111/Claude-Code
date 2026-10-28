"""Génère teaser.html : le teaser motion design de DualTech 2026 (45,5 s, 1920 x 1080).

Chaque image est calculée par window.rendre(t) (aucune animation CSS en temps réel) : exporter.js capture
30 images par seconde dans Chromium, puis ffmpeg assemble la vidéo avec la bande son de son.py.
Tempo de référence : 120 battements par minute, chaque coupe tombe sur un temps (multiple de 0,5 s).
Usage : python3 construire_teaser.py
"""
import json, sys
from pathlib import Path

ICI = Path(__file__).resolve().parent
sys.path.insert(0, str(ICI.parent / "maquette" / "outils"))
from donnees import FILIERES, PHOTOS_FILIERES, INTERVENANTS, PAR_NOM, slug
from icones import icone

A = "../maquette/assets/"

slides = "".join(
    f'<div class="slide abs" id="sl{n}"><img src="{A}photos/{PHOTOS_FILIERES[t]}.webp" alt=""><div class="voile"></div>'
    f'<div class="leg abs"><span class="ico">{icone(t, "ic")}</span><span class="num">{n + 1:02d} / 20</span><span class="nom">{t}</span></div></div>'
    for n, (t, _) in enumerate(FILIERES))
grille = "".join(f'<div class="cel" id="ce{n}"><span class="ico">{icone(t, "ic")}</span><span>{t}</span></div>' for n, (t, _) in enumerate(FILIERES))
mosaique = "".join(f'<img class="mo" src="../maquette/{i["photo"]}" alt="">' for i in (INTERVENANTS * 2)[:40])
VEDETTES = ["Stéphane Boujnah", "Emmanuel Chiva", "Stéphane Ouaki", "Eric Ottou", "Alexandre Papaemmanuel", "Benoist Grossmann"]
cartes = "".join(f'<div class="carte abs" id="ca{n}"><img src="../maquette/{PAR_NOM[v]["photo"]}" alt=""><div class="carte-t"><b>{v}</b><span>{PAR_NOM[v]["fonction"]}</span><em>{PAR_NOM[v]["orga"]}</em></div></div>'
                 for n, v in enumerate(VEDETTES))

HTML = r"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Teaser DualTech 2026</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&family=Public+Sans:wght@300..700&display=swap">
<style>
/* Teaser DualTech 2026 : charte du site (marine, bleu, rouge du T, or du Prix ; Archivo et Public Sans) */
:root{--nuit:#041226;--marine:#0B2244;--bleu:#1B5FC1;--tech:#49A7FF;--rouge:#DF2229;--or:#C9A24A;--brume:#E8EFFA;--acier:#9DB6DC}
*{box-sizing:border-box;margin:0;padding:0}
html,body{width:1920px;height:1080px;overflow:hidden;background:var(--nuit)}
#scene{position:relative;width:1920px;height:1080px;overflow:hidden;background:var(--nuit);color:var(--brume);font-family:"Public Sans",sans-serif}
.abs{position:absolute;will-change:transform,opacity}
.sc{position:absolute;inset:0;opacity:0}
.titre{font-family:"Archivo";font-weight:700;font-variation-settings:"wdth" 106;letter-spacing:-.02em}
.lib{font:600 22px "Archivo";font-variation-settings:"wdth" 85;letter-spacing:.2em;text-transform:uppercase}
.masque{position:absolute;overflow:hidden;left:0;right:0}
.masque>span{display:block;will-change:transform}
/* fond commun */
#fond{inset:0;background:radial-gradient(1300px 800px at 60% 40%,#0E2A55,transparent 70%)}
#grille{inset:-96px;background-image:linear-gradient(rgba(157,182,220,.07) 1px,transparent 1px),linear-gradient(90deg,rgba(157,182,220,.07) 1px,transparent 1px);background-size:96px 96px;-webkit-mask-image:radial-gradient(ellipse 65% 60% at 50% 50%,#000,transparent);mask-image:radial-gradient(ellipse 65% 60% at 50% 50%,#000,transparent)}
#grain{inset:-50%;opacity:.07;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)'/%3E%3C/svg%3E");z-index:40;pointer-events:none}
#vignette{inset:0;background:radial-gradient(ellipse at center,transparent 50%,rgba(2,8,18,.75));z-index:39;pointer-events:none}
/* interface type « instrument » */
#hud{inset:36px;z-index:41;pointer-events:none}
#hud i{position:absolute;width:34px;height:34px;border:0 solid rgba(232,239,250,.6)}
#hud i:nth-child(1){left:0;top:0;border-top-width:2px;border-left-width:2px}
#hud i:nth-child(2){right:0;top:0;border-top-width:2px;border-right-width:2px}
#hud i:nth-child(3){left:0;bottom:0;border-bottom-width:2px;border-left-width:2px}
#hud i:nth-child(4){right:0;bottom:0;border-bottom-width:2px;border-right-width:2px}
.hudtxt{position:absolute;font:600 16px "Archivo";font-variation-settings:"wdth" 85;letter-spacing:.22em;color:rgba(232,239,250,.75);font-variant-numeric:tabular-nums}
#h-g{left:56px;top:10px}#h-d{right:56px;top:10px}#h-b{left:56px;bottom:10px}#h-bd{right:56px;bottom:10px}
#h-g b{color:var(--rouge)}
#barre{left:0;bottom:0;height:4px;width:1920px;background:var(--rouge);transform-origin:left;z-index:42}
#volet{inset:0;z-index:30;background:var(--rouge);transform:translateX(-101%)}
#volet2{inset:0;z-index:31;background:var(--nuit);transform:translateX(-101%)}
/* S1 ouverture */
#l1{left:50%;top:540px;height:3px;width:720px;margin-left:-360px;background:var(--rouge);transform-origin:center}
#coord{left:0;right:0;top:470px;text-align:center;font:600 34px "Archivo";font-variation-settings:"wdth" 85;letter-spacing:.3em;font-variant-numeric:tabular-nums}
#lieu1{left:0;right:0;top:572px;text-align:center;color:var(--acier)}
/* S2 typographie cinétique */
.ph{inset:0;opacity:0}.ph img{width:100%;height:100%;object-fit:cover;filter:grayscale(.6) brightness(.45)}
.mot{left:0;right:0;text-align:center;font-size:260px;line-height:1}
#m1{top:380px}#m2{top:380px}#m2 span{color:var(--tech)}#m3{top:380px}
#m3 span{font-size:200px}
#m3b{left:50%;top:640px;height:12px;width:900px;margin-left:-450px;background:var(--rouge);transform-origin:left}
#m4{top:330px;font-size:150px}#m5{top:520px;font-size:150px}#m5 span{color:var(--tech)}
#m6{left:0;right:0;top:730px;text-align:center;color:var(--acier)}
/* S3 filières */
.slide{inset:0;opacity:0}.slide img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.slide .voile{position:absolute;inset:0;background:linear-gradient(90deg,rgba(4,18,38,.9),rgba(4,18,38,.35) 55%,rgba(4,18,38,.15)),linear-gradient(0deg,rgba(4,18,38,.85),transparent 45%)}
.leg{left:140px;bottom:150px;display:grid;grid-template-columns:150px auto;grid-template-rows:auto auto;column-gap:40px;align-items:end}
.leg .ico{grid-row:span 2;width:150px;height:150px;color:#fff}
.leg .num{font:600 26px "Archivo";font-variation-settings:"wdth" 85;letter-spacing:.24em;color:var(--rouge)}
.leg .nom{font:700 96px/1.02 "Archivo";font-variation-settings:"wdth" 106;letter-spacing:-.02em;max-width:1300px}
.ic{width:100%;height:100%;overflow:visible}.ic .trace{stroke-dasharray:1}
.ic [class*="a-"]{animation:none!important}.ic .a-radar{fill:currentColor;fill-opacity:.2;stroke:none}.ic .a-ecg,.ic .a-courant,.ic .a-faisceau{stroke:var(--tech)}
.ic .a-tirets,.ic .a-trafic,.ic .a-route,.ic .a-orbite-tirets{stroke-dasharray:2 4}
#grilleF{left:160px;right:160px;top:300px;display:grid;grid-template-columns:repeat(5,1fr);gap:18px}
.cel{display:flex;align-items:center;gap:20px;height:120px;padding:0 24px;border:1px solid rgba(157,182,220,.22);background:rgba(11,34,68,.55);font:600 24px "Archivo";font-variation-settings:"wdth" 100;opacity:0}
.cel .ico{width:64px;height:64px;flex:none;color:var(--tech)}
#tF{left:160px;top:150px;font-size:96px}#tF span{color:var(--tech)}
#tF2{left:160px;top:930px;color:var(--acier)}
/* S4 chiffres */
#chif{left:160px;right:160px;top:300px;display:grid;grid-template-columns:repeat(4,1fr);gap:0}
.ch{padding:40px 36px;border-left:1px solid rgba(157,182,220,.25)}
.ch svg{width:110px;height:110px;transform:rotate(-90deg);margin-bottom:34px}
.ch circle{fill:none;stroke-width:3}.ch .f{stroke:rgba(157,182,220,.18)}.ch .v{stroke:var(--tech);stroke-linecap:round;stroke-dasharray:1}
.ch b{display:block;font:700 150px/1 "Archivo";font-variation-settings:"wdth" 112;letter-spacing:-.04em;color:#fff;font-variant-numeric:tabular-nums}
.ch b sup{font-size:.45em;vertical-align:top;color:var(--tech)}
.ch span{display:block;margin-top:20px;font-size:30px;color:var(--acier);max-width:12ch;line-height:1.25}
#tC{left:160px;top:150px}
#s4b{inset:0;opacity:0}#s4b img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;filter:brightness(.4) saturate(.8)}
#phr{left:160px;right:160px;top:330px;font-size:104px;line-height:1.06}
#phr .l{display:block;overflow:hidden}#phr .l span{display:block}
#phr em{font-style:normal;color:var(--tech)}
/* S5 intervenants */
#mos{left:-40px;right:-40px;top:-40px;bottom:-40px;display:grid;grid-template-columns:repeat(10,1fr);gap:8px;opacity:0}
.mo{width:100%;aspect-ratio:1;object-fit:cover;filter:grayscale(1) brightness(.38)}
#s5v{inset:0;background:linear-gradient(90deg,rgba(4,18,38,.96) 0%,rgba(4,18,38,.8) 50%,rgba(4,18,38,.35) 100%)}
#tS{left:160px;top:160px;font-size:110px;line-height:1}#tS span{color:var(--tech)}
#tS2{left:160px;top:410px;color:var(--acier)}
.carte{left:1060px;top:200px;width:620px;height:700px;opacity:0;overflow:hidden;background:var(--marine);box-shadow:0 60px 120px -40px #000}
.carte img{width:100%;height:470px;object-fit:cover;object-position:center 22%}
.carte-t{padding:26px 34px}.carte-t b{display:block;font:700 46px/1.1 "Archivo";color:#fff}
.carte-t span{display:block;margin-top:8px;font-size:22px;color:var(--acier)}.carte-t em{display:block;font-style:normal;font-size:24px;font-weight:600;color:var(--tech)}
.carte::after{content:"";position:absolute;left:0;top:0;height:6px;width:100%;background:var(--rouge)}
#compteurS{left:160px;top:820px;font:700 180px/1 "Archivo";font-variation-settings:"wdth" 112;color:#fff;letter-spacing:-.04em}
#compteurS small{font-size:30px;letter-spacing:.2em;color:var(--acier);margin-left:20px;font-weight:600;vertical-align:middle;text-transform:uppercase}
/* S6 Prix */
#s6 .ph img{filter:brightness(.35) saturate(.6) sepia(.35) hue-rotate(-10deg)}
#med{left:1330px;top:250px;width:440px;height:440px;color:var(--or)}
#tP0{left:160px;top:250px;color:var(--or)}
#tP{left:160px;top:310px;font-size:100px;line-height:1.04}
#tP .l{display:block;overflow:hidden}#tP .l span{display:block}
#dot{left:160px;top:640px;font:700 170px/1 "Archivo";font-variation-settings:"wdth" 112;letter-spacing:-.04em;background:linear-gradient(110deg,#C9A24A 0%,#C9A24A 40%,#FFF3D1 50%,#C9A24A 60%,#C9A24A 100%);background-size:250% 100%;-webkit-background-clip:text;background-clip:text;color:transparent}
#dot2{left:160px;top:830px;font-size:32px;color:var(--brume)}
#cats{left:160px;top:900px;display:flex;gap:16px}
#cats span{padding:14px 26px;border:1px solid rgba(201,162,74,.6);font:600 24px "Archivo";letter-spacing:.14em;text-transform:uppercase;color:var(--or);opacity:0}
/* S7 lieu */
#tour{inset:0;opacity:0}#tour img{width:100%;height:100%;object-fit:cover;object-position:50% 50%;transform-origin:45% 30%}
#tour::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(4,18,38,.1) 35%,rgba(4,18,38,.75) 60%,rgba(4,18,38,.92) 100%)}
#ret{left:870px;top:250px;width:300px;height:300px;margin:-150px 0 0 -150px}
#ret i{position:absolute;width:44px;height:44px;border:0 solid var(--rouge)}
#ret i:nth-child(1){left:0;top:0;border-top-width:3px;border-left-width:3px}#ret i:nth-child(2){right:0;top:0;border-top-width:3px;border-right-width:3px}
#ret i:nth-child(3){left:0;bottom:0;border-bottom-width:3px;border-left-width:3px}#ret i:nth-child(4){right:0;bottom:0;border-bottom-width:3px;border-right-width:3px}
#retc{position:absolute;left:50%;top:50%;width:12px;height:12px;margin:-6px 0 0 -6px;border-radius:50%;background:var(--rouge)}
#retl{position:absolute;left:calc(100% + 18px);top:0;white-space:nowrap;font:600 18px "Archivo";letter-spacing:.2em;color:#fff}
#tL0{left:1150px;top:330px;color:var(--rouge)}
#tL{left:1150px;top:380px;font-size:104px;line-height:1;white-space:nowrap}
#tL2{left:1150px;top:510px;font-size:44px;color:var(--acier);white-space:nowrap}
#tL3{left:1150px;top:620px;padding:22px 30px;border:2px solid #fff;font:700 40px "Archivo";letter-spacing:.06em}
/* S8 final */
#logo{left:50%;top:400px;width:900px;margin-left:-450px}
#logo img{width:900px;display:block}
#date{left:0;right:0;top:600px;text-align:center;font:700 64px "Archivo";font-variation-settings:"wdth" 106;letter-spacing:.02em}
#date span{color:var(--rouge)}
#cta{left:50%;top:720px;transform:translateX(-50%);padding:22px 44px;background:var(--bleu);font:600 30px "Public Sans";white-space:nowrap}
#url{left:0;right:0;top:820px;text-align:center;color:var(--acier)}
#orgas{left:0;right:0;bottom:120px;display:flex;justify-content:center;align-items:center;gap:40px;color:var(--acier);font-size:20px}
#orgas img{height:62px}
</style>
</head>
<body>
<div id="scene">
  <div id="fond" class="abs"></div>
  <div id="grille" class="abs"></div>

  <div class="sc" id="s1">
    <div id="l1" class="abs"></div>
    <p id="coord" class="abs"></p>
    <p id="lieu1" class="abs lib" style="text-align:center"></p>
  </div>

  <div class="sc" id="s2">
    <div class="ph abs" id="p2a"><img src="__A__photos/aeronautique-avion-de-chasse.webp" alt=""></div>
    <div class="ph abs" id="p2b"><img src="__A__photos/spatial-satellite.webp" alt=""></div>
    <div class="ph abs" id="p2c"><img src="__A__photos/cybersecurite-circuit.webp" alt=""></div>
    <div class="masque mot titre" id="m1"><span>CIVIL</span></div>
    <div class="masque mot titre" id="m2"><span>DÉFENSE</span></div>
    <div class="masque mot titre" id="m3"><span>DOUBLE USAGE</span></div>
    <div id="m3b" class="abs"></div>
    <div class="masque mot titre" id="m4"><span>L'industrie duale</span></div>
    <div class="masque mot titre" id="m5"><span>a son rendez-vous</span></div>
    <p id="m6" class="abs lib">Seconde édition · Jeudi 26 novembre 2026</p>
  </div>

  <div class="sc" id="s3">
    __SLIDES__
    <div id="s3g" class="abs" style="inset:0;opacity:0">
      <p id="tF" class="abs titre">20 filières <span>stratégiques</span></p>
      <div id="grilleF" class="abs">__GRILLE__</div>
      <p id="tF2" class="abs lib">Civil · Défense · Sécurité · Souveraineté</p>
    </div>
  </div>

  <div class="sc" id="s4">
    <p id="tC" class="abs lib">Édition 2026</p>
    <div id="chif" class="abs">
      <div class="ch" id="c0"><svg viewBox="0 0 110 110"><circle class="f" cx="55" cy="55" r="52"/><circle class="v" cx="55" cy="55" r="52" pathLength="1"/></svg><b>0</b><span>participants attendus</span></div>
      <div class="ch" id="c1"><svg viewBox="0 0 110 110"><circle class="f" cx="55" cy="55" r="52"/><circle class="v" cx="55" cy="55" r="52" pathLength="1"/></svg><b>0</b><span>intervenants et animateurs</span></div>
      <div class="ch" id="c2"><svg viewBox="0 0 110 110"><circle class="f" cx="55" cy="55" r="52"/><circle class="v" cx="55" cy="55" r="52" pathLength="1"/></svg><b>0</b><span>filières dual-use</span></div>
      <div class="ch" id="c3"><svg viewBox="0 0 110 110"><circle class="f" cx="55" cy="55" r="52"/><circle class="v" cx="55" cy="55" r="52" pathLength="1"/></svg><b>0</b><span>partenaires média et institutionnels</span></div>
    </div>
    <div id="s4b" class="abs"><img src="__A__photos/conference-keynote.webp" alt="">
      <p id="phr" class="abs titre"><span class="l"><span>Une journée unique au cœur</span></span><span class="l"><span>du deuxième quartier d'affaires</span></span><span class="l"><span><em>le plus attractif en Europe</em></span></span></p>
    </div>
  </div>

  <div class="sc" id="s5">
    <div id="mos" class="abs">__MOSAIQUE__</div>
    <div id="s5v" class="abs"></div>
    <p id="tS" class="abs titre">Ils prendront<br><span>la parole</span></p>
    <p id="tS2" class="abs lib">Décideurs publics · Industriels · Investisseurs</p>
    __CARTES__
    <p id="compteurS" class="abs"><span id="nS">0</span><small>intervenants</small></p>
  </div>

  <div class="sc" id="s6">
    <div class="ph abs" id="p6" style="opacity:1"><img src="__A__img/prix-poster.webp" alt=""></div>
    <svg id="med" class="abs" viewBox="0 0 88 88" fill="none" stroke="currentColor" stroke-width=".8"><g id="medr"><circle cx="44" cy="44" r="42" stroke-dasharray="2 5"/><circle cx="44" cy="44" r="36" stroke-opacity=".5"/></g><circle id="medc" cx="44" cy="44" r="27" pathLength="1" stroke-dasharray="1"/><path id="meds" d="M44 29l4.4 9 9.9 1.4-7.2 7 1.7 9.8L44 51.6l-8.8 4.6 1.7-9.8-7.2-7 9.9-1.4Z" fill="currentColor" fill-opacity=".2" pathLength="1" stroke-dasharray="1"/></svg>
    <p id="tP0" class="abs lib">Prix de l'innovation duale · Paris Ouest La Défense</p>
    <p id="tP" class="abs titre"><span class="l"><span>Faire émerger les</span></span><span class="l"><span>innovations de demain</span></span></p>
    <p id="dot" class="abs">5 000 €</p>
    <p id="dot2" class="abs">par lauréat, remis à DualTech 2026</p>
    <div id="cats" class="abs"><span>Robotique</span><span>Santé</span><span>Deeptech</span></div>
  </div>

  <div class="sc" id="s7">
    <div id="tour" class="abs"><img src="__A__img/hero-poster.webp" alt=""></div>
    <div id="ret" class="abs"><i></i><i></i><i></i><i></i><span id="retc"></span><span id="retl">CIBLE VERROUILLÉE</span></div>
    <p id="tL0" class="abs lib">Le lieu</p>
    <p id="tL" class="abs titre">Campus Cyber</p>
    <p id="tL2" class="abs titre">La Défense · Puteaux</p>
    <p id="tL3" class="abs">Jeudi 26 novembre 2026</p>
  </div>

  <div class="sc" id="s8">
    <div id="logo" class="abs"><img src="__A__logo-dualtech.webp" alt="DualTech"></div>
    <p id="date" class="abs">26<span>.</span>11<span>.</span>2026 · Campus Cyber · La Défense</p>
    <p id="cta" class="abs">Inscriptions ouvertes</p>
    <p id="url" class="abs lib">dualtech-event.eu</p>
    <div id="orgas" class="abs"><span>Organisé par</span><img src="__A__partenaires/inas-blanc.webp" alt="INAS"><span>avec</span><img src="__A__partenaires/pold-blanc.webp" alt="Paris Ouest La Défense"></div>
  </div>

  <div id="volet" class="abs"></div>
  <div id="volet2" class="abs"></div>
  <div id="vignette" class="abs"></div>
  <div id="grain" class="abs"></div>
  <div id="hud" class="abs"><i></i><i></i><i></i><i></i>
    <span class="hudtxt" id="h-g"><b>●</b> DUALTECH 2026</span>
    <span class="hudtxt" id="h-d">48°53′ N · 2°14′ E</span>
    <span class="hudtxt" id="h-b">00:00:00</span>
    <span class="hudtxt" id="h-bd">ÉDITION 02</span>
  </div>
  <div id="barre" class="abs"></div>
</div>
<script>
/* ---------- outils ---------- */
const $ = (s) => document.querySelector(s), $$ = (s) => [...document.querySelectorAll(s)];
const cl = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
const p = (t, a, b) => cl((t - a) / (b - a));
const eo = (x) => (x >= 1 ? 1 : 1 - Math.pow(2, -10 * x));          // expo out
const eio = (x) => (x < .5 ? 16 * x ** 5 : 1 - Math.pow(-2 * x + 2, 5) / 2); // quint in out
const ei = (x) => x * x * x;
const vis = (t, a, b, fi = .3, fo = .3) => p(t, a, a + fi) * (1 - p(t, b - fo, b));
const S = (el, o) => { for (const k in o) el.style[k] = o[k]; };
const hash = (n) => { const x = Math.sin(n * 127.1) * 43758.5453; return x - Math.floor(x); };
const SIGNES = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789°·/';
const dechiffre = (txt, pr, f) => [...txt].map((c, i) => (c === ' ' || i < pr * txt.length ? c : SIGNES[(hash(i * 13 + f * 7) * SIGNES.length) | 0])).join('');
const masque = (el, pr, sortie = 0) => { el.firstElementChild.style.transform = `translateY(${(1 - eo(pr)) * 105 - eio(sortie) * 105}%)`; };
const trace = (svg, pr) => svg.querySelectorAll('.trace').forEach((e, i) => { e.style.strokeDashoffset = 1 - cl(pr * 1.4 - i * .04); });
const FIL = __FIL__;

/* changements de séquence : volet rouge puis marine, 0,5 s */
const COUPES = [4, 9, 17, 24, 31, 36, 41];
const SC = { s1: [0, 4.15], s2: [3.85, 9.15], s3: [8.85, 17.15], s4: [16.85, 24.15], s5: [23.85, 31.15], s6: [30.85, 36.15], s7: [35.85, 41.15], s8: [40.85, 45.5] };

window.rendre = (t) => {
  const f = Math.round(t * 30);
  /* fond, grain, interface */
  S($('#grille'), { transform: `translate(${(t * 12) % 96}px, ${(t * 6) % 96}px)` });
  S($('#grain'), { transform: `translate(${hash(f) * 200 - 100}px, ${hash(f + 1) * 200 - 100}px)` });
  S($('#hud'), { opacity: p(t, .6, 1.4) * (1 - p(t, 44.6, 45.4)) });
  $('#h-b').textContent = `00:${String(Math.floor(t)).padStart(2, '0')}:${String(f % 30).padStart(2, '0')} · 26.11.2026`;
  $('#h-g b').style.opacity = Math.floor(t * 2) % 2 ? .25 : 1;
  S($('#barre'), { transform: `scaleX(${t / 45.5})` });
  for (const k in SC) $('#' + k).style.opacity = vis(t, SC[k][0], SC[k][1], .15, .15);
  let v1 = -101, v2 = -101;
  for (const c of COUPES) if (t > c - .45 && t < c + .45) { const q = p(t, c - .45, c + .45); v1 = -101 + eio(q) * 202; v2 = -101 + eio(cl(q * 1.15 - .1)) * 202; }
  S($('#volet'), { transform: `translateX(${v1}%)` }); S($('#volet2'), { transform: `translateX(${v2}%)` });

  /* S1 : ouverture (0 à 4 s) */
  S($('#l1'), { transform: `scaleX(${eo(p(t, .2, 1.4)) * (1 - eio(p(t, 3.4, 3.9)))})` });
  $('#coord').textContent = dechiffre('48°53′ N · 2°14′ E', p(t, .9, 2.3), f);
  S($('#coord'), { opacity: p(t, .8, 1) * (1 - p(t, 3.3, 3.7)), transform: `translateY(${-eo(p(t, 3.3, 3.8)) * 30}px)` });
  $('#lieu1').textContent = dechiffre('LA DÉFENSE · 26.11.2026', p(t, 1.6, 3), f + 3);
  S($('#lieu1'), { opacity: p(t, 1.5, 1.7) * (1 - p(t, 3.3, 3.7)) });

  /* S2 : typographie cinétique (4 à 9 s) */
  S($('#p2a'), { opacity: vis(t, 4, 5, .1, .1) * .9, transform: `scale(${1.1 - p(t, 4, 5) * .08})` });
  S($('#p2b'), { opacity: vis(t, 5, 6, .1, .1) * .9, transform: `scale(${1.1 - p(t, 5, 6) * .08})` });
  S($('#p2c'), { opacity: vis(t, 6, 7.5, .1, .2) * .9, transform: `scale(${1.1 - p(t, 6, 7.5) * .08})` });
  masque($('#m1'), p(t, 4.05, 4.45), p(t, 4.8, 5.05));
  masque($('#m2'), p(t, 5.05, 5.45), p(t, 5.8, 6.05));
  masque($('#m3'), p(t, 6.05, 6.45), p(t, 7.3, 7.55));
  S($('#m3b'), { transform: `scaleX(${eo(p(t, 6.3, 6.9)) * (1 - eio(p(t, 7.2, 7.5)))})` });
  masque($('#m4'), p(t, 7.55, 8.05), p(t, 8.7, 9));
  masque($('#m5'), p(t, 7.7, 8.2), p(t, 8.7, 9));
  S($('#m6'), { opacity: p(t, 8.0, 8.3) * (1 - p(t, 8.7, 8.95)) });
  S($('#s2'), { transform: `scale(${1 + p(t, 4, 9) * .04})` });

  /* S3 : les 20 filières (9 à 15 s), puis la grille (15 à 17 s) */
  FIL.forEach((n, i) => {
    const a = 9 + i * .3, b = a + .3, el = $('#sl' + i);
    const on = t >= a - .02 && t < b + .02 && t < 15.05;
    el.style.opacity = on ? 1 : 0;
    if (on) {
      const q = p(t, a, b);
      S(el.querySelector('img'), { transform: `scale(${1.18 - q * .1}) translateX(${(i % 2 ? 1 : -1) * q * 20}px)` });
      trace(el.querySelector('.ic'), cl(q * 2.2));
      S(el.querySelector('.leg'), { transform: `translateY(${(1 - eo(cl(q * 3))) * 40}px)` });
    }
  });
  S($('#s3g'), { opacity: p(t, 15, 15.2) });
  $$('.cel').forEach((c, i) => {
    const q = p(t, 15.05 + i * .045, 15.55 + i * .045);
    S(c, { opacity: eo(q), transform: `translateY(${(1 - eo(q)) * 40}px) scale(${.92 + eo(q) * .08})` });
    trace(c.querySelector('.ic'), q);
  });
  S($('#tF'), { opacity: p(t, 15.1, 15.4), transform: `translateY(${(1 - eo(p(t, 15.1, 15.7))) * 40}px)` });
  S($('#tF2'), { opacity: p(t, 15.9, 16.2) });
  S($('#grilleF'), { transform: `scale(${1 + p(t, 15, 17) * .03})` });

  /* S4 : chiffres (17 à 21 s), puis phrase (21 à 24 s) */
  const CH = [[400, ''], [40, ''], [20, '+'], [30, '']];
  CH.forEach(([v, s], i) => {
    const q = p(t, 17.2 + i * .25, 19.2 + i * .25), el = $('#c' + i);
    S(el, { opacity: p(t, 17.1 + i * .25, 17.4 + i * .25) * (1 - p(t, 20.7, 21)), transform: `translateY(${(1 - eo(p(t, 17.1 + i * .25, 17.9 + i * .25))) * 60}px)` });
    el.querySelector('b').innerHTML = (s ? `<sup>${s}</sup>` : '') + Math.round(v * eo(q));
    el.querySelector('.v').style.strokeDashoffset = 1 - eo(q) * [1, .9, .5, .75][i];
  });
  S($('#tC'), { opacity: p(t, 17.1, 17.4) * (1 - p(t, 20.7, 21)) });
  S($('#s4b'), { opacity: p(t, 20.9, 21.2) });
  S($('#s4b img'), { transform: `scale(${1.12 - p(t, 21, 24) * .1})` });
  $$('#phr .l > span').forEach((l, i) => { l.style.transform = `translateY(${(1 - eo(p(t, 21.2 + i * .15, 21.9 + i * .15))) * 105}%)`; });

  /* S5 : intervenants (24 à 31 s) */
  S($('#mos'), { opacity: p(t, 24, 24.4), transform: `translate(${-p(t, 24, 31) * 120}px, ${-p(t, 24, 31) * 60}px) scale(1.05)` });
  $$('.mo').forEach((m, i) => { const on = hash(i * 3 + Math.floor(t * 4)) > .9; m.style.filter = on ? 'grayscale(0) brightness(.75)' : 'grayscale(1) brightness(.38)'; });
  S($('#tS'), { opacity: p(t, 24.2, 24.5), transform: `translateY(${(1 - eo(p(t, 24.2, 24.9))) * 50}px)` });
  S($('#tS2'), { opacity: p(t, 24.6, 24.9) });
  for (let i = 0; i < 6; i++) {
    const a = 24.5 + i * 1, b = a + 1, el = $('#ca' + i), q = p(t, a, a + .5), o = p(t, b - .05, b + .35);
    S(el, { opacity: (t >= a && t < b + .35 && t < 31) ? eo(q) * (1 - o) : 0,
            transform: `translateX(${(1 - eo(q)) * 120 - eio(o) * 80}px) rotate(${(1 - eo(q)) * 3}deg)` });
    S(el.querySelector('img'), { transform: `scale(${1.15 - p(t, a, b + .3) * .12})` });
  }
  $('#nS').textContent = Math.round(34 * eo(p(t, 24.5, 30)));
  S($('#compteurS'), { opacity: p(t, 24.5, 24.8) });

  /* S6 : Prix de l'innovation duale (31 à 36 s) */
  S($('#p6'), { transform: `scale(${1.1 - p(t, 31, 36) * .08})` });
  S($('#medr'), { transform: `rotate(${t * 18}deg)`, transformOrigin: '44px 44px' });
  $('#medc').style.strokeDashoffset = 1 - eo(p(t, 31.3, 32.5));
  $('#meds').style.strokeDashoffset = 1 - eo(p(t, 31.8, 33));
  S($('#med'), { opacity: p(t, 31.2, 31.5), transform: `scale(${.85 + eo(p(t, 31.2, 32.2)) * .15}) rotate(${(1 - eo(p(t, 31.2, 32.4))) * -20}deg)` });
  S($('#tP0'), { opacity: p(t, 31.2, 31.5) });
  $$('#tP .l > span').forEach((l, i) => { l.style.transform = `translateY(${(1 - eo(p(t, 31.3 + i * .15, 32 + i * .15))) * 105}%)`; });
  S($('#dot'), { opacity: p(t, 32.4, 32.6), backgroundPosition: `${100 - p(t, 33, 35) * 150}% 0`, transform: `translateY(${(1 - eo(p(t, 32.4, 33))) * 40}px)` });
  S($('#dot2'), { opacity: p(t, 32.9, 33.2) });
  $$('#cats span').forEach((c, i) => S(c, { opacity: p(t, 33.4 + i * .2, 33.6 + i * .2), transform: `translateY(${(1 - eo(p(t, 33.4 + i * .2, 34 + i * .2))) * 30}px)` }));

  /* S7 : le lieu (36 à 41 s) */
  S($('#tour'), { opacity: p(t, 36, 36.3) }); S($('#tour img'), { transform: `scale(${1.25 - eio(p(t, 36, 41)) * .2})` });
  const r = eo(p(t, 36.4, 37.6));
  S($('#ret'), { opacity: p(t, 36.3, 36.5), width: `${700 - r * 400}px`, height: `${700 - r * 400}px`, marginLeft: `${-(700 - r * 400) / 2}px`, marginTop: `${-(700 - r * 400) / 2}px` });
  S($('#retc'), { opacity: t > 37.6 ? (Math.floor(t * 4) % 2 ? .3 : 1) : 0 });
  $('#retl').textContent = t > 37.6 ? dechiffre('CAMPUS CYBER · LA DÉFENSE', p(t, 37.6, 38.6), f) : '';
  S($('#tL0'), { opacity: p(t, 37.8, 38.1) });
  S($('#tL'), { opacity: p(t, 37.9, 38.2), transform: `translateX(${(1 - eo(p(t, 37.9, 38.8))) * 80}px)` });
  S($('#tL2'), { opacity: p(t, 38.3, 38.6), transform: `translateX(${(1 - eo(p(t, 38.3, 39.1))) * 80}px)` });
  S($('#tL3'), { opacity: p(t, 38.9, 39.2), transform: `translateY(${(1 - eo(p(t, 38.9, 39.6))) * 30}px)` });

  /* S8 : signature (41 à 45,5 s) */
  S($('#logo'), { clipPath: `inset(0 ${100 - eo(p(t, 41.2, 42.4)) * 100}% 0 0)`, transform: `scale(${1.06 - eo(p(t, 41.2, 43)) * .06})` });
  S($('#date'), { opacity: p(t, 42.2, 42.5), transform: `translateY(${(1 - eo(p(t, 42.2, 42.9))) * 30}px)` });
  S($('#cta'), { opacity: p(t, 42.7, 43), transform: `translateX(-50%) scale(${.9 + eo(p(t, 42.7, 43.4)) * .1})` });
  S($('#url'), { opacity: p(t, 43, 43.3) });
  S($('#orgas'), { opacity: p(t, 43.3, 43.7) });
  S($('#scene'), { opacity: 1 - p(t, 45.1, 45.5) });
};
window.rendre(0);
</script>
</body>
</html>
"""

html = (HTML.replace("__SLIDES__", slides).replace("__GRILLE__", grille).replace("__MOSAIQUE__", mosaique)
        .replace("__CARTES__", cartes).replace("__FIL__", json.dumps(list(range(20)))).replace("__A__", A))
(ICI / "teaser.html").write_text(html)
print("teaser.html généré")

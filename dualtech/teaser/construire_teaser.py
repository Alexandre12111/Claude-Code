"""Génère teaser.html : le teaser motion design de DualTech 2026, version 2 avec voix off et sous titres (48 s, 1920 x 1080).

Le montage suit la voix (voix/voix.json) : DualTech et sa promesse d'abord, puis le but du salon, son objectif,
et seulement ensuite les filières, les chiffres, les intervenants, le Prix et le lieu.
Chaque image est calculée par window.rendre(t) ; exporter.js capture 30 images par seconde.
Usage : python3 construire_teaser.py   (écrit aussi sous-titres.srt)
"""
import json, sys, textwrap
from pathlib import Path

ICI = Path(__file__).resolve().parent
sys.path.insert(0, str(ICI.parent / "maquette" / "outils"))
from donnees import FILIERES, PHOTOS_FILIERES, INTERVENANTS, PAR_NOM
from icones import icone

A = "../maquette/assets/"
DUREE = 48.0
# début de chaque phrase de la voix off (s), calé sur le tempo de 120 (un temps = 0,5 s)
DEBUTS = {"v1": 1.0, "v2": 4.6, "v3": 10.0, "v4": 14.4, "v5": 19.6, "v6": 24.7, "v7": 30.4, "v8": 35.9, "v9": 38.6, "v10": 43.7}
VOIX = json.loads((ICI / "voix" / "voix.json").read_text())

# ---------- sous titres : deux lignes au plus, 52 caractères par ligne ----------
SOUS_TITRES = []
for v in VOIX:
    debut = DEBUTS[v["id"]]; fin = debut + v["duree"] - .1
    lignes = textwrap.wrap(v["sous_titre"], 52)
    if len(lignes) > 2:       # phrase longue : deux cartons successifs
        moitie = (len(lignes) + 1) // 2; coupe = debut + (fin - debut) * moitie / len(lignes)
        SOUS_TITRES += [(debut, coupe, lignes[:moitie]), (coupe, fin, lignes[moitie:])]
    else:
        SOUS_TITRES.append((debut, fin, lignes))

def hms(s):
    return f"00:00:{int(s):02d},{int(round((s % 1) * 1000)):03d}"
(ICI / "sous-titres.srt").write_text("\n".join(f"{n + 1}\n{hms(a)} --> {hms(b)}\n" + "\n".join(l) + "\n" for n, (a, b, l) in enumerate(SOUS_TITRES)))

slides = "".join(
    f'<div class="slide abs" id="sl{n}"><img src="{A}photos/{PHOTOS_FILIERES[t]}.webp" alt=""><div class="voile"></div>'
    f'<div class="leg abs"><span class="ico">{icone(t, "ic")}</span><span class="num">{n + 1:02d} / 20</span><span class="nom">{t}</span></div></div>'
    for n, (t, _) in enumerate(FILIERES))
flash = "".join(f'<div class="fl"><span class="ico">{icone(t, "ic")}</span></div>' for t, _ in FILIERES)
mosaique = "".join(f'<img class="mo" src="../maquette/{i["photo"]}" alt="">' for i in (INTERVENANTS * 2)[:48])
VEDETTES = ["Stéphane Boujnah", "Emmanuel Chiva", "Stéphane Ouaki"]
cartes = "".join(f'<div class="carte abs" id="ca{n}"><img src="../maquette/{PAR_NOM[v]["photo"]}" alt=""><div class="carte-t"><b>{v}</b><em>{PAR_NOM[v]["orga"]}</em></div></div>'
                 for n, v in enumerate(VEDETTES))
panneaux = "".join(f'<div class="pan abs" id="pa{n}"><img src="{A}photos/{f}.webp" alt=""><span class="pan-l">{l}</span></div>'
                   for n, (f, l) in enumerate([("conference-keynote", "Acteurs publics"), ("robotique-bras", "Industriels"),
                                                ("biotechnologies-microscope", "Scientifiques"), ("conference-networking", "Financiers")]))
st_json = json.dumps([[a, b, "<br>".join(l)] for a, b, l in SOUS_TITRES], ensure_ascii=False)

HTML = r"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Teaser DualTech 2026</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&family=Public+Sans:wght@300..700&display=swap">
<style>
/* Teaser DualTech 2026, version 2 : charte du site (marine, bleu, rouge du T, or du Prix ; Archivo et Public Sans) */
:root{--nuit:#041226;--marine:#0B2244;--bleu:#1B5FC1;--tech:#49A7FF;--rouge:#DF2229;--or:#C9A24A;--brume:#E8EFFA;--acier:#9DB6DC}
*{box-sizing:border-box;margin:0;padding:0}
html,body{width:1920px;height:1080px;overflow:hidden;background:#000}
#scene{position:relative;width:1920px;height:1080px;overflow:hidden;background:var(--nuit);color:var(--brume);font-family:"Public Sans",sans-serif}
#monde{position:absolute;inset:0;will-change:transform,filter}
.abs{position:absolute;will-change:transform,opacity}
.sc{position:absolute;inset:0;opacity:0;overflow:hidden}
.titre{font-family:"Archivo";font-weight:700;font-variation-settings:"wdth" 106;letter-spacing:-.02em}
.lib{font:600 22px "Archivo";font-variation-settings:"wdth" 85;letter-spacing:.22em;text-transform:uppercase}
.l{display:block;overflow:hidden}.l>span{display:block;will-change:transform}
.ph{position:absolute;inset:0}.ph img{width:100%;height:100%;object-fit:cover}
#fond{inset:0;background:radial-gradient(1300px 800px at 60% 40%,#0E2A55,transparent 70%)}
#grille{inset:-96px;background-image:linear-gradient(rgba(157,182,220,.07) 1px,transparent 1px),linear-gradient(90deg,rgba(157,182,220,.07) 1px,transparent 1px);background-size:96px 96px;-webkit-mask-image:radial-gradient(ellipse 65% 60% at 50% 50%,#000,transparent);mask-image:radial-gradient(ellipse 65% 60% at 50% 50%,#000,transparent)}
#grain{inset:-50%;opacity:.07;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)'/%3E%3C/svg%3E");z-index:60;pointer-events:none}
#vignette{inset:0;background:radial-gradient(ellipse at center,transparent 50%,rgba(2,8,18,.78));z-index:59}
#fuite{left:0;top:0;width:1400px;height:1400px;border-radius:50%;background:radial-gradient(closest-side,rgba(73,167,255,.22),transparent);mix-blend-mode:screen;z-index:58}
.pt{position:absolute;width:3px;height:3px;border-radius:50%;background:#fff;z-index:57}
/* interface */
#hud{inset:36px;z-index:61}
#hud i{position:absolute;width:34px;height:34px;border:0 solid rgba(232,239,250,.6)}
#hud i:nth-child(1){left:0;top:0;border-top-width:2px;border-left-width:2px}#hud i:nth-child(2){right:0;top:0;border-top-width:2px;border-right-width:2px}
#hud i:nth-child(3){left:0;bottom:0;border-bottom-width:2px;border-left-width:2px}#hud i:nth-child(4){right:0;bottom:0;border-bottom-width:2px;border-right-width:2px}
.hudtxt{position:absolute;font:600 16px "Archivo";font-variation-settings:"wdth" 85;letter-spacing:.22em;color:rgba(232,239,250,.75);font-variant-numeric:tabular-nums}
#h-g{left:56px;top:10px}#h-d{right:56px;top:10px}#h-b{left:56px;bottom:10px}#h-bd{right:56px;bottom:10px}#h-g b{color:var(--rouge)}
#barre{left:0;bottom:0;height:4px;width:1920px;background:var(--rouge);transform-origin:left;z-index:62}
#volet{inset:0;z-index:50;background:var(--rouge);transform:translateX(-101%)}
#volet2{inset:0;z-index:51;background:var(--nuit);transform:translateX(-101%)}
#eclair{inset:0;z-index:52;background:#fff;opacity:0;mix-blend-mode:overlay}
/* sous titres */
#st{left:0;right:0;bottom:92px;z-index:63;text-align:center}
#st span{display:inline-block;padding:12px 26px;background:rgba(4,18,38,.72);border-left:4px solid var(--rouge);font:500 38px/1.35 "Public Sans";color:#fff;letter-spacing:.005em;backdrop-filter:blur(4px)}
/* A : ouverture DualTech */
#aFond img{filter:brightness(.28) saturate(.7)}
#aRai{left:-600px;top:536px;width:600px;height:8px;background:linear-gradient(90deg,transparent,#fff,var(--tech),transparent);filter:blur(2px);z-index:3}
#aLogo{left:50%;top:330px;width:1180px;margin-left:-590px}
#aLogo img{width:1180px;display:block;filter:drop-shadow(0 0 40px rgba(73,167,255,.35))}
#aSlogan{left:0;right:0;top:560px;text-align:center;font-size:66px}
#aSlogan em{font-style:normal;color:var(--tech)}
#aInfo{left:0;right:0;top:680px;text-align:center;color:var(--acier)}
#aInfo b{color:var(--rouge);font-weight:600}
/* B : le salon */
#bTitre{left:140px;top:120px;font-size:64px}
#bTitre em{font-style:normal;color:var(--tech)}
.pan{top:240px;width:390px;height:600px;overflow:hidden;background:var(--marine)}
.pan img{position:absolute;inset:-20px -60px;width:calc(100% + 120px);height:calc(100% + 40px);object-fit:cover;filter:grayscale(.3) brightness(.7)}
.pan::after{content:"";position:absolute;inset:0;background:linear-gradient(transparent 45%,rgba(4,18,38,.92))}
.pan-l{position:absolute;left:24px;right:20px;bottom:26px;z-index:1;font:700 40px/1.05 "Archivo";font-variation-settings:"wdth" 104;color:#fff}
.pan::before{content:"";position:absolute;left:0;top:0;height:5px;width:100%;background:var(--rouge);z-index:2;transform-origin:left;transform:scaleX(var(--b,0))}
#pa0{left:140px}#pa1{left:550px}#pa2{left:960px}#pa3{left:1370px}
#bTerre img{filter:brightness(.55) saturate(1.1)}
#bSouv{left:140px;top:300px;font-size:120px;line-height:1.02}
#bSouv em{font-style:normal;color:var(--tech)}
#bDemain{left:140px;top:580px;color:var(--acier)}
/* C : l'objectif */
#cG,#cD{top:0;bottom:0;width:960px;overflow:hidden}
#cG{left:0}#cD{right:0}
#cG img,#cD img{width:100%;height:100%;object-fit:cover;filter:brightness(.55)}
.cMot{position:absolute;left:0;right:0;top:430px;text-align:center;font-size:150px}
#cD .cMot{color:var(--tech)}
#cTrait{left:957px;top:0;width:6px;height:1080px;background:var(--rouge);transform-origin:top;z-index:3}
#cObj{left:0;right:0;top:150px;text-align:center;z-index:4;color:#fff}
#cDU{left:50%;top:470px;transform:translateX(-50%);z-index:5;padding:26px 54px;background:var(--rouge);white-space:nowrap;font-size:110px;line-height:1}
#c2{inset:0}
.noeud{width:300px;height:300px;margin:-150px 0 0 -150px;border-radius:50%;border:2px solid rgba(73,167,255,.6);background:rgba(11,34,68,.9);display:grid;place-items:center;align-content:center;gap:14px;text-align:center}
.noeud .ico{width:96px;height:96px;color:var(--tech);display:block;margin:auto}
.noeud b{font:700 38px "Archivo";font-variation-settings:"wdth" 104;color:#fff}
#n0{left:480px;top:600px}#n1{left:960px;top:330px}#n2{left:1440px;top:600px}
#liens{inset:0;z-index:0}
#liens line{stroke:var(--tech);stroke-width:3;stroke-dasharray:1;opacity:.7}
.puls{fill:var(--rouge)}
#cTitre2{left:0;right:0;top:820px;text-align:center;color:var(--acier)}
/* D : les filières */
.slide{inset:0;opacity:0}.slide img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.slide .voile{position:absolute;inset:0;background:linear-gradient(90deg,rgba(4,18,38,.85),rgba(4,18,38,.3) 55%,rgba(4,18,38,.1)),linear-gradient(0deg,rgba(4,18,38,.8),transparent 45%)}
.leg{left:140px;top:300px;display:grid;grid-template-columns:130px auto;grid-template-rows:auto auto;column-gap:36px;align-items:end}
.leg .ico{grid-row:span 2;width:130px;height:130px;color:#fff}
.leg .num{font:600 24px "Archivo";font-variation-settings:"wdth" 85;letter-spacing:.24em;color:var(--rouge)}
.leg .nom{font:700 92px/1.02 "Archivo";font-variation-settings:"wdth" 106;letter-spacing:-.02em;max-width:1300px}
.ic{width:100%;height:100%;overflow:visible}.ic .trace{stroke-dasharray:1}
.ic [class*="a-"]{animation:none!important}.ic .a-radar{fill:currentColor;fill-opacity:.2;stroke:none}.ic .a-ecg,.ic .a-courant,.ic .a-faisceau{stroke:var(--tech)}
.ic .a-tirets,.ic .a-trafic,.ic .a-route,.ic .a-orbite-tirets{stroke-dasharray:2 4}
#dCompte{right:140px;top:130px;font:700 200px/1 "Archivo";font-variation-settings:"wdth" 112;letter-spacing:-.04em;color:#fff;text-align:right}
#dCompte small{display:block;font:600 22px "Archivo";letter-spacing:.22em;color:var(--acier);text-transform:uppercase}
#dFlash{inset:0;display:grid;grid-template-columns:repeat(5,1fr);grid-template-rows:repeat(4,1fr);opacity:0;background:var(--nuit)}
.fl{display:grid;place-items:center;border:1px solid rgba(157,182,220,.15)}
.fl .ico{width:110px;height:110px;color:var(--tech)}
#dTitre{left:0;right:0;top:470px;text-align:center;font-size:120px;opacity:0;z-index:2}
#dTitre span{background:var(--nuit);padding:10px 40px}
/* E : chiffres et intervenants */
#eN{left:140px;top:250px;display:flex;gap:120px}
.eC b{display:block;font:700 260px/1 "Archivo";font-variation-settings:"wdth" 112;letter-spacing:-.05em;color:#fff;font-variant-numeric:tabular-nums}
.eC span{display:block;margin-top:10px;font:600 34px "Archivo";letter-spacing:.14em;text-transform:uppercase;color:var(--tech)}
#mos{left:-60px;right:-60px;top:-60px;bottom:-60px;display:grid;grid-template-columns:repeat(12,1fr);gap:8px;opacity:0}
.mo{width:100%;aspect-ratio:1;object-fit:cover;filter:grayscale(1) brightness(.35)}
#e2v{inset:0;background:linear-gradient(90deg,rgba(4,18,38,.95) 0%,rgba(4,18,38,.6) 55%,rgba(4,18,38,.2))}
#eFormats{left:140px;top:300px;font-size:96px;line-height:1.05}
#eFormats .l:nth-child(2) span{color:var(--tech)}
.carte{left:1180px;top:200px;width:520px;height:620px;opacity:0;overflow:hidden;background:var(--marine);box-shadow:0 60px 120px -40px #000}
.carte img{width:100%;height:500px;object-fit:cover;object-position:center 22%}
.carte-t{padding:22px 30px}.carte-t b{display:block;font:700 38px/1.1 "Archivo";color:#fff}.carte-t em{display:block;font-style:normal;font-size:24px;font-weight:600;color:var(--tech)}
.carte::after{content:"";position:absolute;left:0;top:0;height:6px;width:100%;background:var(--rouge)}
/* F : Prix */
#fFond img{filter:brightness(.32) saturate(.6) sepia(.4)}
#med{left:1320px;top:230px;width:440px;height:440px;color:var(--or)}
#fT{left:140px;top:280px;font-size:110px;line-height:1.02}
#fT .l:nth-child(2) span{color:var(--or)}
#fDot{left:140px;top:560px;font:700 150px/1 "Archivo";font-variation-settings:"wdth" 112;letter-spacing:-.04em;background:linear-gradient(110deg,#C9A24A 0%,#C9A24A 40%,#FFF3D1 50%,#C9A24A 60%,#C9A24A 100%);background-size:250% 100%;-webkit-background-clip:text;background-clip:text;color:transparent}
#fCats{left:140px;top:740px;display:flex;gap:16px}
#fCats span{padding:12px 24px;border:1px solid rgba(201,162,74,.6);font:600 24px "Archivo";letter-spacing:.14em;text-transform:uppercase;color:var(--or)}
/* G : lieu */
#gTour img{object-position:50% 50%;transform-origin:45% 30%}
#gTour::after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(4,18,38,.1) 35%,rgba(4,18,38,.75) 60%,rgba(4,18,38,.92) 100%)}
#ret{left:870px;top:250px;width:300px;height:300px;margin:-150px 0 0 -150px}
#ret i{position:absolute;width:44px;height:44px;border:0 solid var(--rouge)}
#ret i:nth-child(1){left:0;top:0;border-top-width:3px;border-left-width:3px}#ret i:nth-child(2){right:0;top:0;border-top-width:3px;border-right-width:3px}
#ret i:nth-child(3){left:0;bottom:0;border-bottom-width:3px;border-left-width:3px}#ret i:nth-child(4){right:0;bottom:0;border-bottom-width:3px;border-right-width:3px}
#retc{position:absolute;left:50%;top:50%;width:12px;height:12px;margin:-6px 0 0 -6px;border-radius:50%;background:var(--rouge)}
#retl{position:absolute;left:calc(100% + 18px);top:0;white-space:nowrap;font:600 18px "Archivo";letter-spacing:.2em;color:#fff}
#gJour{left:1150px;top:300px;font-size:92px;line-height:1.02}
#gJour .l:nth-child(2) span{color:var(--tech)}
#gLieu{left:1150px;top:520px;font-size:44px;color:var(--acier);white-space:nowrap}
/* H : final */
#hLogo{left:50%;top:360px;width:1000px;margin-left:-500px}
#hLogo img{width:1000px;display:block;filter:drop-shadow(0 0 40px rgba(73,167,255,.3))}
#hDate{left:0;right:0;top:570px;text-align:center;font:700 58px "Archivo";font-variation-settings:"wdth" 106}
#hDate span{color:var(--rouge)}
#hCta{left:50%;top:680px;transform:translateX(-50%);padding:22px 44px;background:var(--bleu);font:600 30px "Public Sans";white-space:nowrap}
#hUrl{left:0;right:0;top:780px;text-align:center;color:var(--acier)}
#hOrgas{left:0;right:0;top:860px;display:flex;justify-content:center;align-items:center;gap:36px;color:var(--acier);font-size:20px}
#hOrgas img{height:58px}
</style>
</head>
<body>
<div id="scene">
 <div id="monde">
  <div id="fond" class="abs"></div>
  <div id="grille" class="abs"></div>

  <div class="sc" id="sA">
    <div class="ph abs" id="aFond"><img src="__A__img/hero-poster.webp" alt=""></div>
    <div id="aRai" class="abs"></div>
    <div id="aLogo" class="abs"><img src="__A__logo-dualtech.webp" alt="DualTech"></div>
    <p id="aSlogan" class="abs titre"><span class="l"><span>Le rendez-vous de <em>l'industrie duale</em></span></span></p>
    <p id="aInfo" class="abs lib">Seconde édition <b>·</b> Jeudi 26 novembre 2026 <b>·</b> Campus Cyber, La Défense</p>
  </div>

  <div class="sc" id="sB">
    <div id="b1" class="abs" style="inset:0">
      <p id="bTitre" class="abs titre"><span class="l"><span>Une journée pour <em>réunir</em></span></span></p>
      __PANNEAUX__
    </div>
    <div id="b2" class="abs" style="inset:0;opacity:0">
      <div class="ph abs" id="bTerre"><img src="__A__photos/terre-nuit.webp" alt=""></div>
      <p id="bSouv" class="abs titre"><span class="l"><span>La souveraineté</span></span><span class="l"><span>technologique</span></span><span class="l"><span><em>française et européenne</em></span></span></p>
      <p id="bDemain" class="abs lib">Celle qui se construit aujourd'hui</p>
    </div>
  </div>

  <div class="sc" id="sC">
    <div id="c1" class="abs" style="inset:0">
      <div id="cG" class="abs"><img src="__A__photos/spatial-satellite.webp" alt=""><p class="cMot titre">CIVIL</p></div>
      <div id="cD" class="abs"><img src="__A__photos/aeronautique-avion-de-chasse.webp" alt=""><p class="cMot titre">DÉFENSE</p></div>
      <div id="cTrait" class="abs"></div>
      <p id="cObj" class="abs lib">Notre objectif</p>
      <p id="cDU" class="abs titre">DOUBLE USAGE</p>
    </div>
    <div id="c2" class="abs" style="opacity:0">
      <svg id="liens" class="abs" viewBox="0 0 1920 1080"><line id="li0" x1="480" y1="600" x2="960" y2="330" pathLength="1"/><line id="li1" x1="960" y1="330" x2="1440" y2="600" pathLength="1"/><line id="li2" x1="1440" y1="600" x2="480" y2="600" pathLength="1"/><circle class="puls" id="pu0" r="9"/><circle class="puls" id="pu1" r="9"/><circle class="puls" id="pu2" r="9"/></svg>
      <div class="noeud abs" id="n0"><span class="ico">__ICO_INNOVER__</span><b>Innover</b></div>
      <div class="noeud abs" id="n1"><span class="ico">__ICO_FINANCER__</span><b>Financer</b></div>
      <div class="noeud abs" id="n2"><span class="ico">__ICO_DECIDER__</span><b>Décider</b></div>
      <p id="cTitre2" class="abs lib">Accélérer les rencontres</p>
    </div>
  </div>

  <div class="sc" id="sD">
    __SLIDES__
    <p id="dCompte" class="abs"><span id="dN">01</span><small>sur 20 filières</small></p>
    <div id="dFlash" class="abs">__FLASH__</div>
    <p id="dTitre" class="abs titre"><span>20 filières stratégiques</span></p>
  </div>

  <div class="sc" id="sE">
    <div id="e1" class="abs" style="inset:0">
      <div class="ph abs"><img src="__A__photos/conference-salle.webp" alt="" style="filter:brightness(.25)"></div>
      <div id="eN" class="abs"><div class="eC" id="eC0"><b>0</b><span>participants</span></div><div class="eC" id="eC1"><b>0</b><span>intervenants</span></div></div>
    </div>
    <div id="e2" class="abs" style="inset:0;opacity:0">
      <div id="mos" class="abs">__MOSAIQUE__</div>
      <div id="e2v" class="abs"></div>
      <p id="eFormats" class="abs titre"><span class="l"><span>Tables rondes</span></span><span class="l"><span>Conférences</span></span><span class="l"><span>Ateliers</span></span></p>
      __CARTES__
    </div>
  </div>

  <div class="sc" id="sF">
    <div class="ph abs" id="fFond"><img src="__A__img/prix-poster.webp" alt=""></div>
    <svg id="med" class="abs" viewBox="0 0 88 88" fill="none" stroke="currentColor" stroke-width=".8"><g id="medr"><circle cx="44" cy="44" r="42" stroke-dasharray="2 5"/><circle cx="44" cy="44" r="36" stroke-opacity=".5"/></g><circle id="medc" cx="44" cy="44" r="27" pathLength="1" stroke-dasharray="1"/><path id="meds" d="M44 29l4.4 9 9.9 1.4-7.2 7 1.7 9.8L44 51.6l-8.8 4.6 1.7-9.8-7.2-7 9.9-1.4Z" fill="currentColor" fill-opacity=".2" pathLength="1" stroke-dasharray="1"/></svg>
    <p id="fT" class="abs titre"><span class="l"><span>Prix de l'innovation</span></span><span class="l"><span>duale</span></span></p>
    <p id="fDot" class="abs">5 000 €</p>
    <div id="fCats" class="abs"><span>Robotique</span><span>Santé</span><span>Deeptech</span></div>
  </div>

  <div class="sc" id="sG">
    <div class="ph abs" id="gTour"><img src="__A__img/hero-poster.webp" alt=""></div>
    <div id="ret" class="abs"><i></i><i></i><i></i><i></i><span id="retc"></span><span id="retl"></span></div>
    <p id="gJour" class="abs titre"><span class="l"><span>Jeudi 26</span></span><span class="l"><span>novembre 2026</span></span></p>
    <p id="gLieu" class="abs titre">Campus Cyber · La Défense</p>
  </div>

  <div class="sc" id="sH">
    <div id="hLogo" class="abs"><img src="__A__logo-dualtech.webp" alt="DualTech"></div>
    <p id="hDate" class="abs">26<span>.</span>11<span>.</span>2026 · Campus Cyber · La Défense</p>
    <p id="hCta" class="abs">Inscrivez-vous dès maintenant</p>
    <p id="hUrl" class="abs lib">dualtech-event.eu</p>
    <div id="hOrgas" class="abs"><span>Organisé par</span><img src="__A__partenaires/inas-blanc.webp" alt="INAS"><span>avec</span><img src="__A__partenaires/pold-blanc.webp" alt="Paris Ouest La Défense"></div>
  </div>
 </div>

  <div id="volet" class="abs"></div>
  <div id="volet2" class="abs"></div>
  <div id="eclair" class="abs"></div>
  <div id="fuite" class="abs"></div>
  __POINTS__
  <div id="vignette" class="abs"></div>
  <div id="grain" class="abs"></div>
  <div id="hud" class="abs"><i></i><i></i><i></i><i></i>
    <span class="hudtxt" id="h-g"><b>●</b> DUALTECH 2026</span>
    <span class="hudtxt" id="h-d">48°53′ N · 2°14′ E</span>
    <span class="hudtxt" id="h-b">00:00:00</span>
    <span class="hudtxt" id="h-bd">ÉDITION 02</span>
  </div>
  <p id="st" class="abs"><span></span></p>
  <div id="barre" class="abs"></div>
</div>
<script>
const $ = (s) => document.querySelector(s), $$ = (s) => [...document.querySelectorAll(s)];
const cl = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
const p = (t, a, b) => cl((t - a) / (b - a));
const eo = (x) => (x >= 1 ? 1 : 1 - Math.pow(2, -10 * x));
const eio = (x) => (x < .5 ? 16 * x ** 5 : 1 - Math.pow(-2 * x + 2, 5) / 2);
const vis = (t, a, b, fi = .2, fo = .2) => p(t, a, a + fi) * (1 - p(t, b - fo, b));
const S = (el, o) => { for (const k in o) el.style[k] = o[k]; };
const hash = (n) => { const x = Math.sin(n * 127.1) * 43758.5453; return x - Math.floor(x); };
const SIGNES = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789°·/';
const dechiffre = (txt, pr, f) => [...txt].map((c, i) => (c === ' ' || i < pr * txt.length ? c : SIGNES[(hash(i * 13 + f * 7) * SIGNES.length) | 0])).join('');
const monte = (els, t, a, pas = .12, d = .7) => els.forEach((l, i) => { l.style.transform = `translateY(${(1 - eo(p(t, a + i * pas, a + i * pas + d))) * 110}%)`; });
const trace = (svg, pr) => svg.querySelectorAll('.trace').forEach((e, i) => { e.style.strokeDashoffset = 1 - cl(pr * 1.4 - i * .04); });
const ST = __ST__;
const SC = { sA: [0, 4.75], sB: [4.35, 14.25], sC: [13.85, 24.75], sD: [24.35, 30.5], sE: [30.0, 36.0], sF: [35.5, 38.75], sG: [38.25, 43.75], sH: [43.25, 48] };
const COUPES = [4.5, 14, 24.5, 30.25, 35.75, 38.5, 43.5];
const IMPACTS = [1.0, 4.5, 10, 14, 17.5, 19.6, 24.5, 30.25, 35.75, 38.5, 43.5];

window.rendre = (t) => {
  const f = Math.round(t * 30);
  /* caméra : zooms sur les temps forts, secousse aux impacts, décalage chromatique aux coupes */
  let zoom = 1, dx = 0, dy = 0, rgb = 0;
  for (const c of IMPACTS) if (t >= c && t < c + .6) { const q = p(t, c, c + .6); zoom += .035 * (1 - eo(q)); dx += (hash(f) - .5) * 18 * (1 - q); dy += (hash(f + 9) - .5) * 12 * (1 - q); }
  if ((t > 24.6 && t < 30.2) || (t > 30.3 && t < 35.6)) { const q = (t % .5) / .5; zoom += .012 * (1 - eo(q * 2)); }
  for (const c of COUPES) if (Math.abs(t - c) < .25) rgb = Math.max(rgb, 1 - Math.abs(t - c) / .25);
  S($('#monde'), { transform: `translate(${dx}px, ${dy}px) scale(${zoom + p(t, 0, 48) * .02})`, filter: rgb > 0 ? `drop-shadow(${rgb * 10}px 0 0 rgba(223,34,41,.6)) drop-shadow(${-rgb * 10}px 0 0 rgba(73,167,255,.6))` : 'none' });
  S($('#grille'), { transform: `translate(${(t * 14) % 96}px, ${(t * 7) % 96}px)` });
  S($('#grain'), { transform: `translate(${hash(f) * 200 - 100}px, ${hash(f + 1) * 200 - 100}px)` });
  S($('#fuite'), { transform: `translate(${300 + Math.sin(t * .4) * 600}px, ${-300 + Math.cos(t * .3) * 300}px)`, opacity: .6 + .4 * Math.sin(t * .7) });
  $$('.pt').forEach((d, i) => { const v = 40 + hash(i) * 120, y = (hash(i + 50) * 1200 - t * v) % 1200; S(d, { transform: `translate(${hash(i + 7) * 1920}px, ${(y < 0 ? y + 1200 : y) - 60}px)`, opacity: .15 + hash(i + 3) * .5 }); });
  S($('#hud'), { opacity: p(t, 2.2, 3) * (1 - p(t, 47.2, 47.9)) });
  $('#h-b').textContent = `00:${String(Math.floor(t)).padStart(2, '0')}:${String(f % 30).padStart(2, '0')} · 26.11.2026`;
  $('#h-g b').style.opacity = Math.floor(t * 2) % 2 ? .25 : 1;
  S($('#barre'), { transform: `scaleX(${t / 48})` });
  for (const k in SC) $('#' + k).style.opacity = vis(t, SC[k][0], SC[k][1], .12, .12);
  let v1 = -101, v2 = -101, ecl = 0;
  for (const c of COUPES) if (t > c - .4 && t < c + .4) { const q = p(t, c - .4, c + .4); v1 = -101 + eio(q) * 202; v2 = -101 + eio(cl(q * 1.15 - .1)) * 202; }
  for (const c of [1.0, 17.5, 43.5]) if (t >= c && t < c + .3) ecl = Math.max(ecl, 1 - p(t, c, c + .3));
  S($('#volet'), { transform: `translateX(${v1}%)` }); S($('#volet2'), { transform: `translateX(${v2}%)` }); $('#eclair').style.opacity = ecl * .7;
  /* sous titres */
  const s = ST.find(([a, b]) => t >= a && t < b);
  const span = $('#st span');
  if (s) { span.innerHTML = s[2]; S($('#st'), { opacity: p(t, s[0], s[0] + .12) * (1 - p(t, s[1] - .1, s[1])), transform: `translateY(${(1 - eo(p(t, s[0], s[0] + .3))) * 16}px)` }); }
  else $('#st').style.opacity = 0;

  /* A : DualTech d'entrée (0 à 4,5 s) */
  S($('#aFond'), { opacity: p(t, .6, 1.6) * .9, transform: `scale(${1.2 - p(t, 0, 4.5) * .12})` });
  S($('#aRai'), { transform: `translateX(${eio(p(t, .3, 1.1)) * 3200}px)`, opacity: 1 - p(t, 1.0, 1.15) });
  const ql = eo(p(t, .95, 1.9));
  S($('#aLogo'), { clipPath: `inset(0 ${50 - ql * 50}% 0 ${50 - ql * 50}%)`, transform: `scale(${1.12 - ql * .12 + eio(p(t, 4.0, 4.5)) * 1.6})`, opacity: 1 - p(t, 4.2, 4.5) });
  monte($$('#aSlogan .l > span'), t, 1.9);
  S($('#aSlogan'), { opacity: 1 - p(t, 4.0, 4.3) });
  $('#aInfo').innerHTML = t < 3.6 ? dechiffre('SECONDE ÉDITION · JEUDI 26 NOVEMBRE 2026 · CAMPUS CYBER, LA DÉFENSE', p(t, 2.5, 3.5), f) : 'Seconde édition <b>·</b> Jeudi 26 novembre 2026 <b>·</b> Campus Cyber, La Défense';
  S($('#aInfo'), { opacity: p(t, 2.4, 2.6) * (1 - p(t, 4.0, 4.3)) });

  /* B : le salon (4,5 à 14 s) */
  monte($$('#bTitre .l > span'), t, 4.6);
  const MOTS = [6.0, 7.3, 8.2, 9.1];
  $$('.pan').forEach((el, i) => {
    const q = p(t, MOTS[i] - .35, MOTS[i] + .35);
    S(el, { clipPath: `inset(${(1 - eo(q)) * 100}% 0 0 0)`, transform: `translateY(${(1 - eo(q)) * 60}px)` });
    S(el.querySelector('img'), { transform: `translateX(${(p(t, 4.5, 10) - .5) * -60}px) scale(${1.08 - p(t, MOTS[i], 10) * .06})` });
    el.style.setProperty('--b', eo(p(t, MOTS[i], MOTS[i] + .5)));
  });
  S($('#b1'), { opacity: 1 - p(t, 9.85, 10.05) });
  S($('#b2'), { opacity: p(t, 9.85, 10.1) });
  S($('#bTerre'), { transform: `scale(${1.1 + p(t, 10, 14) * .1}) rotate(${p(t, 10, 14) * 3}deg)` });
  monte($$('#bSouv .l > span'), t, 10.1, .3, .8);
  S($('#bDemain'), { opacity: p(t, 12.4, 12.8) });

  /* C : l'objectif (14 à 24,5 s) */
  const sep = eio(p(t, 17.2, 17.9));
  S($('#cG'), { transform: `translateX(${(1 - eo(p(t, 14.2, 15.2))) * -960 + sep * 240}px)` });
  S($('#cD'), { transform: `translateX(${(1 - eo(p(t, 14.6, 15.6))) * 960 - sep * 240}px)` });
  S($('#cG img'), { transform: `scale(${1.15 - p(t, 14, 19.6) * .1})` }); S($('#cD img'), { transform: `scale(${1.15 - p(t, 14, 19.6) * .1})` });
  $$('.cMot').forEach((m, i) => S(m, { opacity: p(t, 15.6 + i * .5, 15.9 + i * .5) * (1 - p(t, 17.3, 17.6)), transform: `translateY(${(1 - eo(p(t, 15.6 + i * .5, 16.2 + i * .5))) * 60}px)` }));
  S($('#cTrait'), { transform: `scaleY(${eo(p(t, 14.4, 15.2))})`, opacity: 1 - p(t, 17.2, 17.5) });
  S($('#cObj'), { opacity: p(t, 14.5, 14.8) * (1 - p(t, 19.3, 19.6)) });
  S($('#cDU'), { opacity: p(t, 17.5, 17.6) * (1 - p(t, 19.3, 19.6)), transform: `translateX(-50%) scale(${1.3 - eo(p(t, 17.5, 18.1)) * .3})` });
  S($('#c1'), { opacity: 1 - p(t, 19.45, 19.65) });
  S($('#c2'), { opacity: p(t, 19.45, 19.7) });
  const NO = [20.6, 22.0, 23.4];
  ['#n0', '#n1', '#n2'].forEach((n, i) => { const q = p(t, NO[i] - .3, NO[i] + .3); S($(n), { opacity: eo(q), transform: `scale(${.6 + eo(q) * .4})` }); trace($(n).querySelector('.ic'), p(t, NO[i], NO[i] + .8)); });
  ['#li0', '#li1', '#li2'].forEach((l, i) => { $(l).style.strokeDashoffset = 1 - eo(p(t, NO[i] + .1, NO[i] + .9)); });
  const SEG = [[480, 600, 960, 330], [960, 330, 1440, 600], [1440, 600, 480, 600]];
  ['#pu0', '#pu1', '#pu2'].forEach((c, i) => { const [x1, y1, x2, y2] = SEG[i], q = ((t - NO[i] - .9) * .8) % 1; const on = t > NO[i] + .9; S($(c), { opacity: on ? 1 : 0 }); $(c).setAttribute('cx', x1 + (x2 - x1) * q); $(c).setAttribute('cy', y1 + (y2 - y1) * q); });
  S($('#cTitre2'), { opacity: p(t, 20.2, 20.6) });

  /* D : les filières (24,5 à 30,25 s) */
  for (let i = 0; i < 20; i++) {
    const a = 24.75 + i * .25, b = a + .25, el = $('#sl' + i), on = t >= a - .01 && t < b + .01 && t < 29.75;
    el.style.opacity = on ? 1 : 0;
    if (on) {
      const q = p(t, a, b);
      S(el.querySelector('img'), { transform: `scale(${1.2 - q * .1}) translate(${(i % 2 ? 1 : -1) * q * 24}px, ${(i % 3 - 1) * q * 12}px)` });
      trace(el.querySelector('.ic'), cl(q * 2.4));
      S(el.querySelector('.leg'), { transform: `translateX(${(1 - eo(cl(q * 4))) * -40}px)` });
    }
  }
  $('#dN').textContent = String(cl(Math.floor((t - 24.75) / .25) + 1, 1, 20)).padStart(2, '0');
  S($('#dCompte'), { opacity: p(t, 24.75, 24.9) * (1 - p(t, 29.6, 29.75)) });
  S($('#dFlash'), { opacity: p(t, 29.75, 29.8) });
  $$('.fl').forEach((c, i) => { const q = p(t, 29.75 + (i % 5 + Math.floor(i / 5)) * .03, 30.15); S(c, { opacity: eo(q) }); trace(c.querySelector('.ic'), q); });
  S($('#dTitre'), { opacity: p(t, 29.8, 29.9), transform: `scale(${1.15 - eo(p(t, 29.8, 30.2)) * .15})` });

  /* E : chiffres puis intervenants (30,25 à 35,75 s) */
  [[400, 30.45], [40, 31.6]].forEach(([v, a], i) => {
    const el = $('#eC' + i), q = p(t, a, a + 1.1);
    el.querySelector('b').textContent = Math.round(v * eo(q));
    S(el, { opacity: p(t, a - .1, a + .1), transform: `translateY(${(1 - eo(p(t, a - .1, a + .6))) * 80}px)` });
  });
  S($('#e1'), { opacity: 1 - p(t, 32.8, 33.0) });
  S($('#e2'), { opacity: p(t, 32.8, 33.05) });
  S($('#mos'), { opacity: 1, transform: `translate(${-p(t, 32.8, 36) * 160}px, ${-p(t, 32.8, 36) * 90}px)` });
  $$('.mo').forEach((m, i) => { m.style.filter = hash(i * 3 + Math.floor(t * 6)) > .88 ? 'grayscale(0) brightness(.8)' : 'grayscale(1) brightness(.35)'; });
  monte($$('#eFormats .l > span'), t, 33.0, .35, .6);
  for (let i = 0; i < 3; i++) {
    const a = 33.1 + i * .85, el = $('#ca' + i), q = p(t, a, a + .4), o = p(t, a + .85, a + 1.1);
    S(el, { opacity: (t >= a && t < a + 1.1 && t < 35.75) || (i === 2 && t >= a && t < 35.75) ? eo(q) * (i === 2 ? 1 : 1 - o) : 0, transform: `translateX(${(1 - eo(q)) * 140 - (i === 2 ? 0 : eio(o) * 100)}px) rotate(${(1 - eo(q)) * 4}deg)` });
  }

  /* F : Prix (35,75 à 38,5 s) */
  S($('#fFond'), { transform: `scale(${1.15 - p(t, 35.75, 38.5) * .1})` });
  S($('#medr'), { transform: `rotate(${t * 40}deg)`, transformOrigin: '44px 44px' });
  $('#medc').style.strokeDashoffset = 1 - eo(p(t, 35.9, 36.8)); $('#meds').style.strokeDashoffset = 1 - eo(p(t, 36.2, 37.2));
  S($('#med'), { opacity: p(t, 35.8, 36), transform: `scale(${.8 + eo(p(t, 35.8, 36.6)) * .2}) rotate(${(1 - eo(p(t, 35.8, 36.8))) * -30}deg)` });
  monte($$('#fT .l > span'), t, 35.9, .15, .6);
  S($('#fDot'), { opacity: p(t, 36.6, 36.75), backgroundPosition: `${100 - p(t, 36.8, 38.3) * 150}% 0`, transform: `translateY(${(1 - eo(p(t, 36.6, 37.1))) * 40}px)` });
  $$('#fCats span').forEach((c, i) => S(c, { opacity: p(t, 37.0 + i * .15, 37.2 + i * .15) }));

  /* G : lieu et date (38,5 à 43,5 s) */
  S($('#gTour img'), { transform: `scale(${1.3 - eio(p(t, 38.5, 43.5)) * .25})` });
  const r = eo(p(t, 38.7, 39.8));
  S($('#ret'), { opacity: p(t, 38.6, 38.8), width: `${700 - r * 400}px`, height: `${700 - r * 400}px`, marginLeft: `${-(700 - r * 400) / 2}px`, marginTop: `${-(700 - r * 400) / 2}px` });
  S($('#retc'), { opacity: t > 39.8 ? (Math.floor(t * 4) % 2 ? .3 : 1) : 0 });
  $('#retl').textContent = t > 39.8 ? dechiffre('CAMPUS CYBER · LA DÉFENSE', p(t, 39.8, 40.8), f) : '';
  monte($$('#gJour .l > span'), t, 38.8, .2, .7);
  S($('#gLieu'), { opacity: p(t, 41.0, 41.3), transform: `translateX(${(1 - eo(p(t, 41.0, 41.8))) * 80}px)` });

  /* H : signature (43,5 à 48 s) */
  const qh = eo(p(t, 43.6, 44.6));
  S($('#hLogo'), { clipPath: `inset(0 ${50 - qh * 50}% 0 ${50 - qh * 50}%)`, transform: `scale(${1.08 - qh * .08})` });
  S($('#hDate'), { opacity: p(t, 44.4, 44.7), transform: `translateY(${(1 - eo(p(t, 44.4, 45))) * 30}px)` });
  S($('#hCta'), { opacity: p(t, 44.8, 45.1), transform: `translateX(-50%) scale(${.9 + eo(p(t, 44.8, 45.4)) * .1})` });
  S($('#hUrl'), { opacity: p(t, 45.1, 45.4) });
  S($('#hOrgas'), { opacity: p(t, 45.4, 45.8) });
  S($('#scene'), { opacity: p(t, 0, .15) * (1 - p(t, 47.5, 48)) });
};
window.rendre(0);
</script>
</body>
</html>
"""

points = "".join('<span class="pt"></span>' for _ in range(36))
html = (HTML.replace("__PANNEAUX__", panneaux).replace("__SLIDES__", slides).replace("__FLASH__", flash).replace("__MOSAIQUE__", mosaique)
        .replace("__CARTES__", cartes).replace("__POINTS__", points).replace("__ST__", st_json)
        .replace("__ICO_INNOVER__", icone("Intelligence artificielle", "ic")).replace("__ICO_FINANCER__", icone("Data", "ic"))
        .replace("__ICO_DECIDER__", icone("Infrastructures critiques", "ic")).replace("__A__", A))
(ICI / "teaser.html").write_text(html)
print("teaser.html et sous-titres.srt générés,", len(SOUS_TITRES), "sous titres")

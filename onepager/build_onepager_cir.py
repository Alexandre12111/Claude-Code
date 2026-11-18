"""One-pager client YouCraft (FR, A4 portrait), à glisser dans les rapports de mission CIR ou IP Box.
Document autonome et destiné aux clients : accroche sur l'effort de suivi demandé par le dossier CIR, exemples d'applications
de suivi R&D, puis tout ce que YouCraft permet au-delà (CRM, gestion de projet, RH...), preuves, méthode et appel à l'action.
Usage : python3 build_onepager_cir.py  ->  YouCraft_OnePager_Client_CIR.html, puis PDF via render_pdf.cjs"""
import io, os, re, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from build_onepager_consultants import FONTS, logo, mark, ic, NB  # noqa: E402
import qrcode
import qrcode.image.svg

HERE = os.path.dirname(os.path.abspath(__file__))
RDV = 'https://calendly.com/youcraft/30min'


def qr(url, size):
    img = qrcode.make(url, image_factory=qrcode.image.svg.SvgPathImage, border=0, box_size=10)
    buf = io.BytesIO(); img.save(buf); s = buf.getvalue().decode()
    s = re.sub(r'<\?xml[^>]*\?>', '', s)
    s = re.sub(r'width="[^"]*mm" height="[^"]*mm"', f'width="{size}" height="{size}"', s, count=1)
    return s.replace('fill="#000000"', 'fill="#0B0B0F"')


CIR = [
    ('timer', 'Temps de travail R&D', 'Feuilles de temps par projet et par chercheur, validées par les managers, à jour toute l’année.',
     [('Projet Atlas', 'Camille L.', '38 h', 'ok'), ('Projet Nova', 'Hugo M.', '24 h', 'ok'), ('Projet Helios', 'Inès D.', '31 h', 'wa')]),
    ('activity', 'Procédés et essais', 'Journal des expériences : hypothèses, protocoles, versions et résultats, horodatés et rattachés aux projets.',
     [('Essai n° 14', 'Formulation B', 'Concluant', 'ok'), ('Essai n° 15', 'Formulation C', 'En cours', 'in'), ('Essai n° 16', 'Prototype v2', 'À relancer', 'wa')]),
    ('folder', 'Justificatifs et dossier', 'Pièces centralisées, contrôle des manques et export prêt pour la rédaction du dossier.',
     [('Comptes rendus', '42 pièces', 'Complet', 'ok'), ('Sous‑traitance', '9 pièces', 'Complet', 'ok'), ('Diplômes chercheurs', '5 / 6', 'Manquant', 'wa')]),
]
MORE = [
    ('trending-up', 'CRM commercial', 'Prospects, opportunités et relances'),
    ('folder-kanban', 'Gestion de projet', 'Tâches, jalons, charge des équipes'),
    ('users', 'Tableau de bord RH', 'Effectifs, recrutements, intégration'),
    ('gauge', 'Suivi de production', 'Cadences, incidents, maintenance'),
    ('clipboard-check', 'Validations', 'Achats, notes de frais, congés'),
    ('building-2', 'Portail client', 'Documents partagés, suivi des dossiers'),
]
STEPS = [('message-square-text', 'Vous décrivez', 'Votre besoin, avec vos mots, comme à un collègue.'),
         ('wand-sparkles', 'YouCraft construit', 'Écrans, données, validations et droits d’accès.'),
         ('rocket', 'Vous utilisez', 'En un clic, sur ordinateur comme sur mobile.')]
WHY = [('shield-check', 'Sécurisée et souveraine', 'Données hébergées en Europe, aucun entraînement sur vos données, accès par rôles.'),
       ('app-window', 'De vraies applications', 'Base de données, connexion et déploiement en un clic. Pas une simple maquette.'),
       ('building-2', 'Conçue par le groupe Leyton', 'Développée par CognitX, l’entité tech et IA du groupe.')]
TRIAL = [('J0 · 1 h', 'On crée ensemble votre application'), ('J0 à J7', 'Vos équipes l’utilisent'), ('J7', 'Bilan et suite')]

ST = {'ok': ('#E7F6EC', '#1C8C4E'), 'in': ('#EEF0FF', '#1E3BFF'), 'wa': ('#FDF3E1', '#A8670A')}


def badge(t, k):
    bg, fg = ST[k]
    return f'<span class="bd" style="background:{bg};color:{fg}">{t}</span>'


def cir_cards():
    out = []
    for i, (icn, t, d, rows) in enumerate(CIR):
        r = ''.join(f'<div class="mr"><b>{a}</b><span>{b}</span>{badge(c, k)}</div>' for a, b, c, k in rows[:2] + rows[2:][:0] + ([rows[2]] if rows[2][3] == 'wa' else []))
        out.append(f'<div class="cc"><div class="cch"><span class="tl">{ic(icn, 15, "#1E3BFF")}</span><h4>{t}</h4></div><p>{d}</p><div class="mini">{r}</div></div>')
    return ''.join(out)


def more():
    return ''.join(f'<div class="mo"><span class="tl s">{ic(i, 13, "#1E3BFF")}</span><div><b>{t}</b><span>{d}</span></div></div>' for i, t, d in MORE)


def steps():
    return ''.join(f'<div class="st"><span class="n">{k + 1}</span><div><b>{t}</b><span>{d}</span></div></div>' for k, (i, t, d) in enumerate(STEPS))


def why():
    return ''.join(f'<div class="wy">{ic(i, 15, "#1E3BFF")}<div><b>{t}</b><span>{d}</span></div></div>' for i, t, d in WHY)


def trial():
    return ''.join(f'<div class="tr"><em>{a}</em><span>{b}</span></div>' + ('' if k == 2 else '<i></i>') for k, (a, b) in enumerate(TRIAL))


MOCK = f'''<div class="mock">
  <div class="mb"><span class="dots"><b></b><b></b><b></b></span><span>Suivi R&amp;D · 2026</span><em>En ligne</em></div>
  <div class="mk"><div><small>Heures R&amp;D ce mois</small><strong>412 h</strong></div><div><small>Projets suivis</small><strong>6</strong></div><div><small>Justificatifs</small><strong>128</strong></div></div>
  <div class="mc">{"".join(f'<i style="height:{h}%"{" class=hi" if k == 5 else ""}></i>' for k, h in enumerate([44, 58, 50, 66, 61, 88, 72]))}</div>
  <div class="mt"><span>Projet Atlas · 38 h cette semaine</span>{badge("Validé", "ok")}</div>
</div>'''

CSS = """
:root{--ink:#0B0B0F;--stone:#F5F2EA;--paper:#FBFAF6;--line:#E4DFD3;--muted:#6E6B63;--text2:#45433E;--um:#1E3BFF;--um-soft:#EDF0FF;--lime:#D7FF3F}
*{box-sizing:border-box;margin:0;padding:0}
html,body{background:#D9D5CB}
body{font-family:'DM Sans',system-ui,sans-serif;color:var(--ink);-webkit-font-smoothing:antialiased}
h1,h2,h3,h4,b,strong{font-family:'Plus Jakarta Sans',system-ui,sans-serif}
.ic{flex:none;display:inline-block;vertical-align:middle}
@page{size:A4;margin:0}
.page{width:210mm;height:297mm;margin:10mm auto;background:var(--paper);position:relative;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(11,11,15,.18)}
@media print{html,body{background:var(--paper)}.page{margin:0;box-shadow:none;height:296.6mm}}
/* bandeau */
.hero{flex:none;background:var(--ink);color:var(--stone);padding:7mm 12mm 6mm;position:relative;overflow:hidden}
.hero .mark{position:absolute;left:66mm;top:-26mm}
.hero .glow{position:absolute;right:-12mm;top:-22mm;width:115mm;height:95mm;border-radius:50%;background:radial-gradient(closest-side,rgba(30,59,255,.55),rgba(30,59,255,0))}
.top{display:flex;justify-content:space-between;align-items:center;position:relative}
.tag{font-size:6.8pt;letter-spacing:.14em;text-transform:uppercase;color:var(--lime);font-weight:700;border:1px solid rgba(215,255,63,.35);padding:1mm 2.4mm;border-radius:10mm}
.hg{display:grid;grid-template-columns:1fr 60mm;gap:8mm;margin-top:4.6mm;position:relative;align-items:center}
.k1{font:700 7pt/1 'DM Sans';letter-spacing:.16em;color:var(--lime);text-transform:uppercase}
.hero h1{margin-top:2.2mm;font-size:22pt;line-height:1.05;letter-spacing:-.035em;font-weight:800}
.hero h1 span{color:var(--lime)}
.hero .lead{margin-top:2.6mm;font-size:8.8pt;line-height:1.5;color:#C9C5BB}
.hero .lead b{color:var(--stone);font-weight:700}
.stats{display:flex;margin-top:3.6mm}
.stat{padding:0 4.6mm;border-left:1px solid rgba(245,242,234,.18)}
.stat:first-child{padding-left:0;border-left:0}
.stat strong{display:block;font-size:17pt;letter-spacing:-.03em;line-height:1;color:var(--stone)}
.stat strong em{font-style:normal;color:var(--lime)}
.stat span{display:block;font-size:7pt;color:#A39F95;margin-top:1mm}
.mock{background:#F7F5EF;color:var(--ink);border-radius:3mm;overflow:hidden;box-shadow:0 16px 40px rgba(0,0,0,.45),0 0 60px rgba(79,107,255,.25);transform:rotate(-1.5deg)}
.mb{height:6mm;background:#fff;border-bottom:1px solid #E8E4DA;display:flex;align-items:center;gap:2mm;padding:0 2.6mm;font:700 7pt 'Plus Jakarta Sans'}
.mb .dots{display:flex;gap:1mm}.mb .dots b{width:1.5mm;height:1.5mm;border-radius:50%;background:#D9D4C8}
.mb em{margin-left:auto;font-style:normal;font-size:6pt;color:#0A7A35;background:#DDF7E5;padding:.4mm 1.6mm;border-radius:4mm}
.mk{display:grid;grid-template-columns:repeat(3,1fr);gap:1.6mm;padding:2.4mm 2.4mm 0}
.mk div{background:#fff;border-radius:1.6mm;padding:1.6mm 1.8mm;border-top:2px solid var(--um)}
.mk div:nth-child(2){border-top-color:var(--lime)}
.mk small{display:block;font-size:5.4pt;color:var(--muted)}
.mk strong{font-size:10pt;letter-spacing:-.02em}
.mc{margin:2mm 2.4mm 0;height:11mm;background:#fff;border-radius:1.6mm;padding:1.6mm;display:flex;align-items:flex-end;gap:1.4mm}
.mc i{flex:1;border-radius:.8mm .8mm 0 0;background:#C9D1FF}.mc i.hi{background:var(--um)}
.mt{margin:2mm 2.4mm 2.4mm;background:#fff;border-radius:1.6mm;padding:1.6mm 1.8mm;display:flex;align-items:center;justify-content:space-between;font-size:6.4pt}
.bd{display:inline-flex;align-items:center;padding:.4mm 1.6mm;border-radius:3mm;font:700 6pt 'DM Sans';white-space:nowrap}
/* corps */
.body{flex:1;padding:4.4mm 12mm 0;display:flex;flex-direction:column;gap:3.6mm}
.sh{display:flex;align-items:baseline;gap:2.2mm;margin-bottom:2mm}
.sh i{font-style:normal;font-family:'Plus Jakarta Sans';font-weight:800;font-size:7pt;color:var(--um);letter-spacing:.06em}
.sh h2{font-size:11.6pt;letter-spacing:-.02em;font-weight:800}
.sh small{margin-left:auto;font-size:7pt;color:var(--muted)}
.tl{width:7mm;height:7mm;border-radius:2mm;background:var(--um-soft);display:flex;align-items:center;justify-content:center;flex:none}
.tl.s{width:6.2mm;height:6.2mm}
.cir{display:grid;grid-template-columns:repeat(3,1fr);gap:2.4mm}
.cc{background:#fff;border:1px solid var(--line);border-radius:3mm;padding:2.6mm 2.8mm;display:flex;flex-direction:column}
.cch{display:flex;align-items:center;gap:2mm}
.cc h4{font-size:9pt;letter-spacing:-.01em}
.cc p{font-size:7.4pt;line-height:1.4;color:var(--text2);margin-top:1.6mm}
.mini{margin-top:auto;padding-top:2mm;display:flex;flex-direction:column;gap:1mm}
.mr{display:grid;grid-template-columns:1fr auto auto;gap:1.6mm;align-items:center;padding:1.2mm 1.6mm;border-radius:1.6mm;background:var(--stone);font-size:6.6pt}
.mr b{font-size:6.8pt;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mr span{color:var(--muted);white-space:nowrap}
.beyond{background:var(--um-soft);border-radius:3.4mm;padding:3mm 3.4mm}
.beyond .sh h2 span{color:var(--um)}
.mos{display:grid;grid-template-columns:repeat(3,1fr);gap:2mm}
.mo{display:flex;gap:2mm;align-items:center;background:#fff;border-radius:2.4mm;padding:1.6mm 2.2mm}
.mo b{display:block;font-size:8pt;letter-spacing:-.01em}
.mo span span{display:block;font-size:6.9pt;color:var(--text2);margin-top:.4mm}
.mo>div>span{display:block;font-size:6.9pt;color:var(--text2);margin-top:.4mm}
.beyond p{margin-top:2mm;font-size:7.6pt;color:var(--text2)}
.beyond p b{color:var(--ink)}
.two{display:grid;grid-template-columns:1fr 1fr;gap:6mm}
.st{display:flex;gap:2.6mm;align-items:flex-start;padding:1.5mm 0;border-top:1px solid var(--line)}
.st:first-of-type{border-top:0;padding-top:0}
.st .n{flex:none;width:6.2mm;height:6.2mm;border-radius:50%;background:var(--ink);color:var(--lime);display:flex;align-items:center;justify-content:center;font:800 7.6pt 'Plus Jakarta Sans'}
.st b,.wy b{display:block;font-size:8.4pt;letter-spacing:-.01em}
.st span,.wy span{display:block;font-size:7.3pt;line-height:1.38;color:var(--text2);margin-top:.4mm}
.wy{display:flex;gap:2.6mm;align-items:flex-start;padding:1.5mm 0;border-top:1px solid var(--line)}
.wy:first-of-type{border-top:0;padding-top:0}
.wy .ic{margin-top:.4mm}
/* appel à l'action */
.cta{background:var(--ink);color:var(--stone);border-radius:4mm;padding:3.8mm 4.6mm;display:grid;grid-template-columns:1fr 30mm;gap:5mm;align-items:center;position:relative;overflow:hidden}
.cta::after{content:"";position:absolute;right:20mm;bottom:-30mm;width:80mm;height:60mm;border-radius:50%;background:radial-gradient(closest-side,rgba(30,59,255,.5),transparent)}
.cta>*{position:relative;z-index:1}
.cta .of{display:inline-flex;align-items:center;gap:1.6mm;font:700 6.8pt/1 'DM Sans';letter-spacing:.12em;text-transform:uppercase;color:var(--ink);background:var(--lime);padding:1.2mm 2.4mm;border-radius:4mm}
.cta h3{margin-top:2mm;font-size:13pt;letter-spacing:-.025em;line-height:1.1}
.cta h3 span{color:var(--lime)}
.trs{display:flex;align-items:stretch;margin-top:2.4mm}
.tr{flex:1;padding:1.6mm 2.2mm;border-radius:2.4mm;background:rgba(245,242,234,.07);border:1px solid rgba(245,242,234,.14)}
.tr em{display:block;font:700 6.8pt 'DM Sans';font-style:normal;color:var(--lime)}
.tr span{display:block;font-size:7.4pt;line-height:1.3;margin-top:.8mm}
.trs>i{width:4mm;flex:none;display:flex;align-items:center;justify-content:center}
.trs>i::before{content:"";width:1.6mm;height:1.6mm;border-right:1.5px solid var(--lime);border-top:1.5px solid var(--lime);transform:rotate(45deg)}
.qr{background:#fff;border-radius:3mm;padding:2.4mm;text-align:center}
.qr svg{display:block;width:23mm;height:23mm}
.qr span{display:block;margin-top:1.4mm;font:700 6.6pt 'DM Sans';color:var(--ink)}
.ct{display:flex;gap:4mm;margin-top:2.6mm;font-size:7pt;color:#A39F95}
.ct span{flex:1;border-bottom:1px dotted rgba(245,242,234,.3);padding-bottom:1mm}
.foot{flex:none;margin-top:auto;padding:2.4mm 12mm;display:flex;justify-content:space-between;align-items:center;font-size:7pt;color:var(--muted)}
.foot b{color:var(--ink);font-size:7pt}
@media screen and (max-width:820px){.page{transform-origin:top left;transform:scale(calc(100vw / 220mm));margin:0}}
"""

HTML = f"""<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>YouCraft · Pour les équipes qui innovent</title>
<style>{FONTS}{CSS}</style></head>
<body><main class="page">
<header class="hero">{mark(250)}<div class="glow"></div>
  <div class="top">{logo(22)}<span class="tag">Pour les équipes qui innovent</span></div>
  <div class="hg"><div>
    <div class="k1">Après votre dossier CIR</div>
    <h1>Votre dossier CIR vous a demandé <span>des efforts de suivi</span>{NB}?</h1>
    <p class="lead">Procédés, essais, temps de travail, justificatifs… Et si vos équipes disposaient de leurs propres applications pour tout suivre, toute l’année{NB}? Avec <b>YouCraft</b>, elles les créent simplement en décrivant leur besoin, <b>en quelques heures plutôt qu’en quelques mois</b>.</p>
    <div class="stats"><div class="stat"><strong><em>1\u00a0300+</em></strong><span>utilisateurs chez Leyton</span></div><div class="stat"><strong><em>500+</em></strong><span>applications déployées</span></div>
      <div class="stat"><strong>0</strong><span>ligne de code à écrire</span></div></div>
  </div>{MOCK}</div>
</header>
<section class="body">
  <div><div class="sh"><i>01</i><h2>Pour le CIR : vos efforts de suivi, enfin outillés</h2><small>Exemples d’applications créées avec YouCraft</small></div><div class="cir">{cir_cards()}</div></div>
  <div class="beyond"><div class="sh"><i>02</i><h2>Et <span>bien au‑delà</span> du CIR</h2><small>Un seul outil pour toutes vos équipes</small></div><div class="mos">{more()}</div>
    <p><b>Un besoin, une application.</b> Vos équipes construisent les outils qu’elles attendent, sans écrire une ligne de code.</p></div>
  <div class="two">
    <div><div class="sh"><i>03</i><h2>Comment ça marche</h2></div>{steps()}</div>
    <div><div class="sh"><i>04</i><h2>Pourquoi YouCraft</h2></div>{why()}</div>
  </div>
  <div class="cta"><div>
      <span class="of">{ic("sparkles", 10, "#0B0B0F", 2.4)}Offre clients Leyton</span>
      <h3>Essayez YouCraft sur votre propre cas : <span>7 jours offerts</span></h3>
      <div class="trs">{trial()}</div>
      <div class="ct"><span>Votre consultant Leyton :</span><span>Contact :</span></div>
    </div>
    <div class="qr">{qr(RDV, 100)}<span>Prenez rendez‑vous</span></div></div>
</section>
<footer class="foot"><b>YouCraft · Leyton CognitX</b><span>youcraft.build</span></footer>
</main></body></html>"""

out = os.path.join(HERE, 'YouCraft_OnePager_Client_CIR.html')
open(out, 'w', encoding='utf-8').write(HTML)
print(out)

"""One-pager YouCraft (FR, v0.6) : document interne A4, mise en page éditoriale en deux colonnes.
Assemble un fichier HTML autonome : polices intégrées (fonts.css, reprises de la v0.4), logo vectoriel officiel
(brand_yc/yc_logo_data.js) et icônes Lucide (src6/icons.js). Usage : python3 build_onepager.py"""
import json, os, re

HERE = os.path.dirname(os.path.abspath(__file__))
VID = os.path.join(HERE, '..', 'video', 'digicraft-lancement')
FONTS = open(os.path.join(HERE, 'fonts.css'), encoding='utf-8').read()
LOGO = json.loads(re.search(r'window\.YC_LOGO = (\{.*\});', open(os.path.join(VID, 'brand_yc', 'yc_logo_data.js'), encoding='utf-8').read(), re.S).group(1))
ICONS = json.loads(re.search(r'window\.ICONS=(\{.*\})', open(os.path.join(VID, 'src6', 'icons.js'), encoding='utf-8').read(), re.S).group(1))


def logo(h, dark=True):
    g = ''.join(f'<stop offset="{o}" stop-color="{c}"/>' for o, c in LOGO['grad'])
    bx, by, bw, bh = LOGO['sym']['block']
    col = '#F5F2EA' if dark else '#0B0B0F'
    glyphs = ''.join(f'<path d="{d}" fill="{col}" transform="translate({LOGO["tx"]} {LOGO["ty"]})"/>' for d in LOGO['glyphs'])
    w = h * LOGO['w'] / LOGO['h']
    return (f'<svg class="logo" width="{w:.1f}" height="{h}" viewBox="0 0 {LOGO["w"]} {LOGO["h"]}" role="img" aria-label="YouCraft"><defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1">{g}</linearGradient></defs>'
            f'<path d="{LOGO["sym"]["dome"]}" fill="url(#lg)"/><path d="{LOGO["sym"]["quarter"]}" fill="{col}"/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" fill="#E4F53C"/>{glyphs}</svg>')


def sketch():
    bx, by, bw, bh = LOGO['sym']['block']
    st = 'fill="none" stroke="rgba(245,242,234,0.09)" stroke-width="1.2" vector-effect="non-scaling-stroke"'
    return f'<svg class="sketch" viewBox="-8 -8 264 263" aria-hidden="true"><path d="{LOGO["sym"]["dome"]}" {st}/><path d="{LOGO["sym"]["quarter"]}" {st}/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" {st}/></svg>'


def ic(name, size=14, color='currentColor', sw=2):
    return f'<svg class="ic" width="{size}" height="{size}" viewBox="0 0 24 24" fill="none" stroke="{color}" stroke-width="{sw}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{ICONS[name]}</svg>'


# Longue traîne : quelques grands projets portés par la DSI, puis la multitude d'outils d'équipe que vise YouCraft.
def tail_chart():
    bars, x = [], 0
    for i in range(26):
        h = 46 * (0.86 ** i) + 4
        cls = 'dsi' if i < 4 else 'yc'
        bars.append(f'<rect class="{cls}" x="{x}" y="{50 - h:.1f}" width="5.2" height="{h:.1f}" rx="1.2"/>')
        x += 7.4
    return (f'<svg class="tail" viewBox="0 0 192 66" aria-hidden="true">{"".join(bars)}'
            '<text x="0" y="62" class="tl">Grands projets · DSI</text><text x="192" y="62" text-anchor="end" class="tl yc">La longue traîne · YouCraft</text></svg>')



def tail_v():
    """Longue traîne sur fond sombre : quatre grands projets DSI puis la multitude d'outils d'équipe."""
    bars, x = [], 0
    for i in range(30):
        h = 52 * (0.87 ** i) + 3
        bars.append(f'<rect class="{"dsi" if i < 4 else "yc"}" x="{x:.1f}" y="{56 - h:.1f}" width="4.6" height="{h:.1f}" rx="1.1"/>')
        x += 6.6
    return (f'<svg class="tail" viewBox="0 0 196 72" aria-hidden="true">{"".join(bars)}'
            '<text x="0" y="68" class="tl">Grands projets · DSI</text><text x="196" y="68" text-anchor="end" class="tl yc">Notre terrain</text></svg>')


PLANS = [  # (code, nom, prix, unité, utilisateurs, crédits, engagement, hauteur de marche en mm, classe)
    ('Build', 'Starter', '25\u00a0€', '/ builder / mois', 'Jusqu’à 5, sans authentification', '100', 'Trimestriel', 20, 's1'),
    ('Control', 'Business', '50\u00a0€', '/ builder / mois', '10 inclus, puis 10\u00a0€', '300', 'Trimestriel', 24, 's2'),
    ('Protect', 'Sovereign', '100\u00a0€', '/ builder / mois', '10 inclus, puis 10\u00a0€', '500', 'Trimestriel', 28, 's3'),
    ('Scale', 'Enterprise', 'Sur contrat', '', 'Tarifs de volume', 'Mutualisés', 'Annuel, dès 12\u00a0k€', 32, 's4'),
]


def plans():
    cols = []
    for code, name, price, unit, users, cred, eng, h, cls in PLANS:
        cols.append(f'''<div class="pl {cls}">
      <div class="step" style="height:{h}mm"><span class="code">{code}</span><b class="nm">{name}</b><span class="pr">{price}</span><span class="un">{unit}</span></div>
      <div class="row">{users}</div><div class="row">{cred}</div><div class="row">{eng}</div></div>''')
    return ('<div class="plans"><div class="lab"><div class="row">Utilisateurs d’application</div><div class="row">Crédits IA par builder et par mois</div><div class="row">Engagement</div></div>'
            + ''.join(cols) + '</div>')


HTML = f"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>YouCraft en une page</title>
<style>
{FONTS}
:root {{
  --ink: #0B0B0F; --ink2: #17171D; --stone: #F5F2EA; --paper: #FBFAF6; --um: #1E3BFF; --um-d: #6F86FF; --lime: #D7FF3F;
  --text: #0B0B0F; --text2: #3F3D39; --muted: #6A675F; --line: #DDD8CB;
  --head: 'Plus Jakarta Sans', 'DM Sans', system-ui, sans-serif; --body: 'DM Sans', system-ui, sans-serif;
}}
* {{ box-sizing: border-box; }}
html, body {{ margin: 0; background: #D4D0C6; }}
body {{ font-family: var(--body); color: var(--text); -webkit-font-smoothing: antialiased; print-color-adjust: exact; -webkit-print-color-adjust: exact; }}
@page {{ size: A4; margin: 0; }}
.page {{ width: 210mm; height: 297mm; margin: 10mm auto; display: grid; grid-template-columns: 70mm 1fr; overflow: hidden; background: var(--stone); box-shadow: 0 24px 70px rgba(11,11,15,.2); }}
h1, h2, h3, b.nm {{ font-family: var(--head); }}

/* Colonne gauche : le message, le pitch, notre terrain. */
aside {{ position: relative; background: var(--ink); color: var(--stone); padding: 10mm 7.5mm 7mm 9mm; display: flex; flex-direction: column; overflow: hidden; }}
aside::before {{ content: ''; position: absolute; width: 120mm; height: 120mm; right: -62mm; top: -46mm; border-radius: 50%; background: radial-gradient(circle, rgba(30,59,255,.55), rgba(30,59,255,0) 68%); }}
aside > * {{ position: relative; }}
.tag {{ display: inline-flex; align-items: center; gap: 1.4mm; margin-top: 6mm; font-size: 7.6px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #9C988E; }}
aside h1 {{ margin: 13mm 0 0; font-weight: 800; font-size: 29px; line-height: 1.02; letter-spacing: -.04em; color: #fff; }}
aside h1 em {{ font-style: normal; color: var(--lime); }}
.sub {{ margin: 4.4mm 0 0; font-size: 9.6px; line-height: 1.55; color: #BDB8AD; }}
.quote {{ margin-top: auto; padding-top: 7mm; }}
.kf {{ margin-top: 9mm; display: grid; gap: 4.2mm; }}
.kf div {{ display: grid; grid-template-columns: 19mm 1fr; align-items: baseline; padding-bottom: 3.6mm; border-bottom: 1px solid rgba(245,242,234,.12); }}
.kf b {{ font-family: var(--head); font-weight: 800; font-size: 25px; letter-spacing: -.04em; color: #fff; line-height: 1; }}
.kf b i {{ font-style: normal; font-size: 12px; color: var(--lime); margin-left: .6mm; }}
.kf span {{ font-size: 8.8px; line-height: 1.4; color: #BDB8AD; }}
.qh {{ display: flex; align-items: center; justify-content: space-between; font-size: 7.6px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--lime); }}
.qh span {{ display: inline-flex; align-items: center; gap: 1.2mm; color: #9C988E; letter-spacing: .04em; text-transform: none; font-weight: 500; }}
.quote blockquote {{ margin: 3mm 0 0; padding-left: 3.4mm; border-left: 2px solid var(--lime); font-size: 10px; line-height: 1.55; color: #EAE6DC; }}
.quote blockquote b {{ color: #fff; }}
.terrain {{ margin-top: 7mm; padding-top: 4.4mm; border-top: 1px solid rgba(245,242,234,.14); }}
.terrain h2 {{ margin: 0; font-size: 11px; font-weight: 800; color: #fff; letter-spacing: -.01em; }}
.terrain p {{ margin: 1.4mm 0 3mm; font-size: 8.6px; line-height: 1.5; color: #A8A397; }}
.tail {{ width: 100%; height: auto; display: block; }}
.tail rect.dsi {{ fill: #4A4842; }} .tail rect.yc {{ fill: var(--um-d); }}
.tail .tl {{ font-family: var(--body); font-size: 6px; fill: #8D897F; }} .tail .tl.yc {{ fill: var(--lime); font-weight: 700; }}
.int {{ margin-top: 6mm; display: flex; align-items: center; gap: 1.6mm; font-size: 7.8px; font-weight: 700; color: #FF8E7A; }}

/* Colonne droite. */
main {{ padding: 10mm 10mm 7mm 9mm; display: flex; flex-direction: column; min-width: 0; }}
.sec {{ display: grid; grid-template-columns: 8mm 1fr; column-gap: 2mm; }}
.sec + .sec {{ margin-top: 8.6mm; }}
.no {{ font-family: var(--head); font-weight: 800; font-size: 10px; color: var(--um); padding-top: .6mm; }}
.sec h2 {{ margin: 0 0 3mm; font-size: 15px; font-weight: 800; letter-spacing: -.025em; line-height: 1.15; }}

/* 1. Le délai : une barre longue contre une barre courte. */
.race {{ display: grid; gap: 2.6mm; }}
.lane .lh {{ display: flex; justify-content: space-between; align-items: baseline; font-size: 8px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); margin-bottom: 1.2mm; }}
.lane .lh b {{ font-family: var(--head); font-size: 12px; letter-spacing: -.02em; text-transform: none; color: var(--text); }}
.bar {{ height: 3.2mm; border-radius: 2mm; }}
.lane.old .bar {{ width: 100%; background: repeating-linear-gradient(-45deg, #CFCABE 0 4px, #DED9CE 4px 8px); }}
.lane.new .bar {{ width: 9%; background: var(--um); box-shadow: 0 0 0 2.4mm rgba(30,59,255,.1); }}
.lane p {{ margin: 1.4mm 0 0; font-size: 9px; line-height: 1.5; color: var(--text2); }}
.lane.new .lh b {{ color: var(--um); }}

/* 2. À qui le vendre. */
.who {{ display: grid; grid-template-columns: 1.25fr 1fr; gap: 5mm; }}
.who ul {{ list-style: none; margin: 0; padding: 0; display: grid; gap: 1.6mm; }}
.who li {{ display: grid; grid-template-columns: 4.4mm 1fr; font-size: 9px; line-height: 1.45; color: var(--text2); }}
.who li .ic {{ margin-top: .4mm; }}
.who .hd {{ font-size: 7.8px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; margin-bottom: 1.8mm; }}
.who .yes .hd {{ color: var(--um); }} .who .nope .hd {{ color: #B4331F; }}
.who .nope {{ border-left: 1px solid var(--line); padding-left: 5mm; }}

/* 3. Ce qui nous distingue : liste numérotée, sans cartes. */
.dl {{ border-top: 1.5px solid var(--ink); }}
.d {{ display: grid; grid-template-columns: 13mm 1fr; padding: 2.6mm 0; border-bottom: 1px solid var(--line); }}
.d .k {{ font-family: var(--head); font-weight: 800; font-size: 22px; line-height: .9; letter-spacing: -.04em; color: transparent; -webkit-text-stroke: 1px #9A968C; }}
.d h3 {{ margin: 0; font-size: 11.4px; font-weight: 800; letter-spacing: -.015em; display: flex; align-items: center; gap: 2mm; }}
.d p {{ margin: .8mm 0 0; font-size: 9px; line-height: 1.48; color: var(--text2); }}
.d.usp .k {{ color: var(--um); -webkit-text-stroke: 0; }}
.usp-t {{ font-family: var(--body); font-size: 7px; font-weight: 700; letter-spacing: .1em; background: var(--lime); color: var(--ink); padding: .5mm 1.6mm; border-radius: 1mm; }}
mark {{ background: linear-gradient(transparent 58%, rgba(215,255,63,.85) 58%); color: inherit; padding: 0 .3mm; }}

/* 4. Les offres : quatre marches. */
.plans {{ display: grid; grid-template-columns: 20mm repeat(4, 1fr); align-items: end; }}
.pl, .lab {{ display: flex; flex-direction: column; }}
.step {{ display: flex; flex-direction: column; justify-content: flex-start; padding: 2.2mm 2.4mm; border-radius: 2.4mm 2.4mm 0 0; color: #fff; }}
.step .code {{ font-size: 7px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; opacity: .7; }}
.step .nm {{ font-weight: 800; font-size: 11px; margin-top: .3mm; }}
.step .pr {{ font-family: var(--head); font-weight: 800; font-size: 15px; letter-spacing: -.03em; margin-top: auto; line-height: 1; }}
.step .un {{ font-size: 7px; opacity: .72; margin-top: .6mm; }}
.s1 .step {{ background: #3A3A44; }} .s2 .step {{ background: var(--um); }} .s3 .step {{ background: var(--ink); }} .s4 .step {{ background: var(--lime); color: var(--ink); }}
.s4 .step .pr {{ font-size: 13px; }}
.plans .row {{ height: 9.4mm; display: flex; align-items: center; padding: 0 2.4mm; font-size: 8.4px; line-height: 1.3; border-top: 1px solid var(--line); background: var(--paper); }}
.pl .row {{ border-left: 1px solid var(--line); }}
.s4 .row {{ border-right: 1px solid var(--line); }}
.pl .row:last-child, .lab .row:last-child {{ border-bottom: 1px solid var(--line); }}
.lab .row {{ background: none; padding-left: 0; font-size: 7.6px; font-weight: 700; color: var(--muted); line-height: 1.25; }}
.notes {{ margin-top: 3.4mm; display: grid; gap: 1.5mm; }}
.notes div {{ display: grid; grid-template-columns: 5mm 1fr; font-size: 8.6px; line-height: 1.45; color: var(--text2); }}
.notes b {{ color: var(--text); }}
.notes .warn b {{ color: #B4331F; }}
.foot {{ margin-top: auto; display: flex; justify-content: space-between; font-size: 7.6px; color: var(--muted); padding-top: 3mm; }}

@media print {{ html, body {{ background: none; height: 297mm; overflow: hidden; }} .page {{ margin: 0; box-shadow: none; height: 296.6mm; }} }}
@media screen and (max-width: 820px) {{
  .page {{ width: 100%; height: auto; margin: 0; grid-template-columns: 1fr; }}
  .who {{ grid-template-columns: 1fr; }} .who .nope {{ border-left: 0; padding-left: 0; }}
  .plans {{ grid-template-columns: 1fr 1fr; gap: 3mm; }} .lab {{ display: none; }}
}}
</style>
</head>
<body>
<div class="page">
  <aside>
    {logo(30)}
    <span class="tag">{ic('lock', 10, '#9C988E')}Document interne · équipes Leyton</span>
    <h1>Vos équipes ont les idées. <em>YouCraft livre les applications.</em></h1>
    <p class="sub">La plateforme d’IA qui transforme un besoin métier, décrit en français, en application prête à l’emploi. Sans code, sans file d’attente, sans risque pour les données.</p>
    <div class="kf">
      <div><b>0</b><span>ligne de code à écrire pour l’équipe métier</span></div>
      <div><b>1<i>clic</i></b><span>pour mettre une application en production</span></div>
      <div><b>100<i>%</i></b><span>des données sur une infrastructure souveraine</span></div>
    </div>

    <div class="quote">
      <div class="qh">Le pitch en 30 secondes <span>{ic('timer', 10, '#9C988E')}à dire tel quel</span></div>
      <blockquote>« Imaginez transformer vos fichiers Excel et vos idées en <b>vraies applications</b>, en quelques heures et sans une ligne de code. Avec YouCraft, vos équipes créent leurs propres outils. Vos données restent chez vous, chaque application est prête pour la production, et tout reste sous votre contrôle. <b>Ce qui prenait des mois se fait en quelques heures.</b> »</blockquote>
    </div>

    <div class="terrain">
      <h2>Notre terrain : la longue traîne</h2>
      <p>La DSI porte les grands projets. Restent des centaines d’outils propres à chaque équipe, jamais prioritaires. C’est là que nous jouons, <b style="color:#fff">avec la DSI, pas à sa place</b>.</p>
      {tail_v()}
    </div>
    <div class="int">{ic('lock-keyhole', 10, '#FF8E7A')}Usage interne uniquement · ne pas partager avec les clients</div>
  </aside>

  <main>
    <section class="sec"><span class="no">01</span><div>
      <h2>Le délai change tout</h2>
      <div class="race">
        <div class="lane old"><div class="lh">Aujourd’hui <b>Des mois</b></div><div class="bar"></div>
          <p>Chaque idée d’outil attend dans la file de la DSI. En attendant, les équipes bricolent sur Excel.</p></div>
        <div class="lane new"><div class="lh">Avec YouCraft <b>Quelques heures</b></div><div class="bar"></div>
          <p>L’équipe décrit son besoin, YouCraft construit l’<b>application complète</b> : écrans, données, accès. Sans développeur.</p></div>
      </div>
    </div></section>

    <section class="sec"><span class="no">02</span><div>
      <h2>À qui le proposer</h2>
      <div class="who">
        <div class="yes"><div class="hd">Les bons clients</div><ul>
          <li>{ic('check', 11, '#1E3BFF', 2.6)}<span>Les clients Leyton dont les <b>équipes métiers</b> n’ont pas de profil développeur</span></li>
          <li>{ic('check', 11, '#1E3BFF', 2.6)}<span>Des processus gérés sur <b>Excel</b></span></li>
          <li>{ic('check', 11, '#1E3BFF', 2.6)}<span>Des demandes d’outils <b>bloquées</b> faute de ressources</span></li></ul></div>
        <div class="nope"><div class="hd">À éviter pour l’instant</div><ul>
          <li>{ic('x', 11, '#B4331F', 2.6)}<span>Les <b>entreprises tech</b> : cycle de vente plus long et objections plus pointues</span></li></ul></div>
      </div>
    </div></section>

    <section class="sec"><span class="no">03</span><div>
      <h2>Pourquoi nous plutôt qu’un autre AI builder</h2>
      <div class="dl">
        <div class="d usp"><span class="k">01</span><div><h3>Une IA souveraine et sécurisée, dès la conception <span class="usp-t">NOTRE USP</span></h3>
          <p>Modèles open source, infrastructure souveraine, données sous contrôle. <mark>L’argument qu’aucun concurrent ne peut reprendre.</mark></p></div></div>
        <div class="d"><span class="k">02</span><div><h3>De vraies applications, pas des maquettes</h3>
          <p>Backend, authentification, données persistantes et déploiement en un clic. Le client repart avec un outil en production.</p></div></div>
        <div class="d"><span class="k">03</span><div><h3>Gouverné à grande échelle</h3>
          <p>Toutes les applications de l’entreprise pilotées, sécurisées et déployées depuis un point central.</p></div></div>
      </div>
    </div></section>

    <section class="sec"><span class="no">04</span><div>
      <h2>Les offres, par licence builder</h2>
      {plans()}
      <div class="notes">
        <div>{ic('trending-up', 12, '#1E3BFF')}<span><b>Au‑delà de 12 k€ par an</b> (40 builders Starter, 20 Business ou 10 Sovereign), on passe en Enterprise.</span></div>
        <div>{ic('target', 12, '#1E3BFF')}<span><b>Monter en gamme</b> : applications privées ou SSO, Business. Données sensibles, Sovereign. Plusieurs équipes ou gouvernance centrale, Enterprise.</span></div>
        <div class="warn">{ic('circle-alert', 12, '#B4331F')}<span><b>Vigilance Starter</b> : applications publiques uniquement, jamais de données confidentielles.</span></div>
      </div>
    </div></section>

    <div class="foot"><span>YouCraft · Leyton</span><span>One‑pager commercial · v0.6</span></div>
  </main>
</div>
</body>
</html>
"""
open(os.path.join(HERE, 'YouCraft_OnePager_FR_v0.6.html'), 'w', encoding='utf-8').write(HTML)
print('ok')

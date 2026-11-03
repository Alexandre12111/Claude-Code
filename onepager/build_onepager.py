"""One-pager YouCraft (FR, v0.5) : document interne A4, refait de zéro à la charte YouCraft.
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


HTML = f"""<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>YouCraft en une page</title>
<style>
{FONTS}
:root {{
  --ink: #0B0B0F; --ink2: #16161C; --stone: #F5F2EA; --paper: #FBFAF6; --um: #1E3BFF; --um-d: #4F6BFF; --lime: #D7FF3F;
  --text: #0B0B0F; --text2: #45433E; --muted: #6F6C66; --line: #E3DFD3; --soft: #EFEBE2;
  --head: 'Plus Jakarta Sans', 'DM Sans', system-ui, sans-serif; --body: 'DM Sans', system-ui, sans-serif;
}}
* {{ box-sizing: border-box; }}
html, body {{ margin: 0; background: #D9D5CB; }}
body {{ font-family: var(--body); color: var(--text); -webkit-font-smoothing: antialiased; -webkit-print-color-adjust: exact; print-color-adjust: exact; }}
.page {{ width: 210mm; height: 297mm; margin: 10mm auto; background: var(--stone); position: relative; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(11,11,15,.18); }}
.ic {{ display: inline-block; vertical-align: -2px; flex: none; }}
h1, h2, h3 {{ font-family: var(--head); margin: 0; letter-spacing: -.02em; }}

/* En-tête */
.hero {{ flex: none; background: var(--ink); color: var(--stone); padding: 7mm 12mm 6.4mm; display: grid; grid-template-columns: 1fr 58mm; column-gap: 8mm; position: relative; overflow: hidden; }}
.hero::before {{ content: ''; position: absolute; right: -40mm; top: -50mm; width: 150mm; height: 150mm; border-radius: 50%; background: radial-gradient(closest-side, rgba(30,59,255,.55), rgba(30,59,255,0)); }}
.hero::after {{ content: ''; position: absolute; left: -30mm; bottom: -60mm; width: 110mm; height: 110mm; border-radius: 50%; background: radial-gradient(closest-side, rgba(215,255,63,.10), rgba(215,255,63,0)); }}
.sketch {{ position: absolute; right: 6mm; top: -6mm; width: 70mm; height: 70mm; }}
.top {{ grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }}
.badge {{ display: inline-flex; align-items: center; gap: 1.6mm; height: 6mm; padding: 0 3mm; border-radius: 3mm; border: 1px solid rgba(245,242,234,.22); font-size: 8.4px; font-weight: 500; letter-spacing: .08em; text-transform: uppercase; color: #CFCAC0; }}
.hero h1 {{ position: relative; z-index: 1; margin-top: 5.4mm; font-weight: 800; font-size: 26px; line-height: 1.07; letter-spacing: -.035em; }}
.hero h1 em {{ font-style: normal; color: var(--lime); }}
.hero p.lead {{ position: relative; z-index: 1; margin: 2.6mm 0 0; font-size: 10.2px; line-height: 1.48; color: #CFCAC0; }}
.chips {{ position: relative; z-index: 1; display: flex; gap: 2mm; margin-top: 3.6mm; }}
.tailbox {{ position: relative; z-index: 1; align-self: end; background: rgba(245,242,234,.05); border: 1px solid rgba(245,242,234,.12); border-radius: 3mm; padding: 3mm 3.2mm 2mm; }}
.tailbox b {{ font-family: var(--head); font-weight: 800; font-size: 9.6px; color: #fff; }}
.tailbox p {{ margin: .8mm 0 1.6mm; font-size: 8.2px; line-height: 1.4; color: #A9A49A; }}
.chip {{ display: inline-flex; align-items: center; gap: 1.6mm; height: 6.6mm; padding: 0 3.2mm; border-radius: 3.3mm; background: rgba(245,242,234,.07); border: 1px solid rgba(245,242,234,.14); font-size: 9px; font-weight: 500; color: var(--stone); }}

/* Corps */
main {{ flex: 1; padding: 5mm 12mm 0; display: flex; flex-direction: column; gap: 4mm; }}
.kicker {{ font-family: var(--body); font-weight: 700; font-size: 8.2px; letter-spacing: .16em; text-transform: uppercase; color: var(--um); margin-bottom: 1.8mm; display: flex; align-items: center; gap: 2mm; }}
.kicker::after {{ content: ''; flex: 1; height: 1px; background: var(--line); }}
.grid2 {{ display: grid; grid-template-columns: repeat(4, 1fr); gap: 2.6mm; }}
.card {{ background: var(--paper); border: 1px solid var(--line); border-radius: 3mm; padding: 3mm 3.2mm; }}
.card h3 {{ font-size: 10.8px; font-weight: 800; display: flex; align-items: center; gap: 2mm; margin-bottom: 1.4mm; }}
.card h3 .n {{ width: 5.4mm; height: 5.4mm; border-radius: 1.6mm; background: var(--ink); color: var(--lime); display: inline-flex; align-items: center; justify-content: center; font-size: 9.4px; }}
.card p {{ margin: 0; font-size: 9px; line-height: 1.46; color: var(--text2); }}
.card p b {{ color: var(--text); font-weight: 700; }}
.card .no {{ margin-top: 1.6mm; padding-top: 1.4mm; border-top: 1px dashed var(--line); font-size: 8.2px; color: var(--muted); display: flex; gap: 1.4mm; }}
.pos {{ display: grid; grid-template-columns: minmax(0, 1fr) 54mm; gap: 3.4mm; align-items: center; }}
.tail {{ width: 100%; height: auto; display: block; }}
.tail rect.dsi {{ fill: #55534E; }} .tail rect.yc {{ fill: var(--um); }}
.tail .tl {{ font-family: var(--body); font-size: 5.4px; fill: #A9A49A; }} .tail .tl.yc {{ fill: var(--lime); font-weight: 700; }}

/* Différenciants */
.diff {{ display: grid; grid-template-columns: 1.18fr 1fr 1fr; gap: 3mm; }}
.d {{ border-radius: 3mm; padding: 3mm 3.4mm; background: var(--paper); border: 1px solid var(--line); position: relative; }}
.d .i {{ width: 7mm; height: 7mm; border-radius: 2.2mm; background: var(--soft); display: flex; align-items: center; justify-content: center; margin-bottom: 1.8mm; }}
.d h3 {{ font-size: 10.8px; font-weight: 800; line-height: 1.2; margin-bottom: 1.4mm; }}
.d p {{ margin: 0; font-size: 8.8px; line-height: 1.48; color: var(--text2); }}
.d.usp {{ background: var(--um); border-color: var(--um); color: #fff; }}
.d.usp .i {{ background: rgba(255,255,255,.16); }}
.d.usp p {{ color: rgba(255,255,255,.9); }}
.tagusp {{ position: absolute; right: 3.4mm; top: 3.4mm; height: 5mm; padding: 0 2.2mm; border-radius: 2.5mm; background: var(--lime); color: var(--ink); font-family: var(--head); font-weight: 800; font-size: 8px; display: flex; align-items: center; letter-spacing: .04em; }}
.note {{ font-size: 8.4px; color: var(--muted); margin-top: 1.6mm; }}
.note b {{ color: var(--text); }}

/* Pitch */
.pitch {{ background: var(--ink); color: var(--stone); border-radius: 3.4mm; padding: 3.4mm 4.4mm 3.4mm 13mm; position: relative; overflow: hidden; }}
.pitch::before {{ content: '“'; position: absolute; left: 4.4mm; top: -1mm; font-family: var(--head); font-weight: 800; font-size: 46px; color: var(--lime); line-height: 1; }}
.pitch .h {{ display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.8mm; }}
.pitch .h b {{ font-family: var(--head); font-weight: 800; font-size: 11.4px; color: #fff; }}
.pitch .h span {{ font-size: 8.4px; color: #A9A49A; display: inline-flex; gap: 1.2mm; align-items: center; }}
.pitch p {{ margin: 0 0 1.2mm; font-size: 9.2px; line-height: 1.5; color: rgba(245,242,234,.9); }}
.pitch p b {{ color: #fff; }}
.pitch ol {{ margin: .6mm 0 1.6mm; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(3, 1fr); gap: 2mm; }}
.pitch li {{ font-size: 8.7px; line-height: 1.4; color: rgba(245,242,234,.88); padding: 1.6mm 2.2mm; border-radius: 2.2mm; background: rgba(245,242,234,.06); border: 1px solid rgba(245,242,234,.1); }}
.pitch li b {{ display: block; color: var(--um-d); font-family: var(--head); font-weight: 800; font-size: 9.6px; margin-bottom: .4mm; }}
.pitch p.end {{ margin: 0; font-family: var(--head); font-weight: 700; font-size: 9.8px; color: var(--lime); }}

/* Offres */
.plans {{ display: grid; grid-template-columns: 31mm repeat(4, 1fr); border-radius: 3.4mm; overflow: hidden; border: 1px solid var(--line); background: var(--paper); }}
.plans > div {{ padding: 1.4mm 2.2mm; font-size: 8.6px; line-height: 1.3; border-top: 1px solid var(--line); text-align: center; display: flex; align-items: center; justify-content: center; }}
.plans .rh {{ justify-content: flex-start; text-align: left; font-family: var(--head); font-weight: 800; font-size: 8.8px; background: rgba(11,11,15,.03); }}
.plans .ph {{ border-top: 0; flex-direction: column; gap: .2mm; padding: 1.8mm 2mm; color: #fff; }}
.plans .ph b {{ font-family: var(--head); font-weight: 800; font-size: 11px; }}
.plans .ph small {{ font-size: 7.8px; opacity: .8; letter-spacing: .08em; text-transform: uppercase; }}
.plans .p1 {{ background: #2A2A33; }} .plans .p2 {{ background: var(--um); }} .plans .p3 {{ background: var(--ink2); }} .plans .p4 {{ background: var(--lime); color: var(--ink); }}
.plans .c0 {{ border-top: 0; background: var(--paper); justify-content: flex-start; font-size: 7.8px; color: var(--muted); letter-spacing: .08em; text-transform: uppercase; font-weight: 700; }}
.plans .price b {{ font-family: var(--head); font-weight: 800; font-size: 12px; }}
.plans .price span {{ font-size: 7.8px; color: var(--muted); margin-left: .8mm; }}
.rules {{ display: grid; grid-template-columns: 1fr 1.3fr 1fr; gap: 2.6mm; margin-top: 2.2mm; }}
.r {{ border-radius: 2.8mm; padding: 2.2mm 2.8mm; font-size: 8.4px; line-height: 1.45; color: var(--text2); background: var(--paper); border: 1px solid var(--line); }}
.r b {{ display: flex; align-items: center; gap: 1.4mm; font-family: var(--head); font-weight: 800; font-size: 9.6px; color: var(--text); margin-bottom: 1mm; }}
.r.warn {{ border-color: #F0C9C6; background: #FCF3F2; }}
.r .arr {{ color: var(--um); font-weight: 700; }}
footer {{ flex: none; display: flex; justify-content: space-between; align-items: center; padding: 2.6mm 12mm 4.6mm; font-size: 8.2px; color: var(--muted); }}
footer .int {{ display: inline-flex; align-items: center; gap: 1.4mm; color: #B23A30; font-weight: 700; }}
@page {{ size: A4; margin: 0; }}
@media print {{ html, body {{ background: none; }} .page {{ margin: 0; box-shadow: none; height: 296.6mm; page-break-after: avoid; }} html, body {{ height: 296.6mm; overflow: hidden; }} }}
@media screen and (max-width: 820px) {{ .page {{ width: 100%; height: auto; margin: 0; }} .grid2, .diff, .rules, .pos {{ grid-template-columns: 1fr; }} .pitch ol {{ grid-template-columns: 1fr; }} .plans {{ font-size: 8px; }} }}
</style>
</head>
<body>
<div class="page">
  <header class="hero">
    {sketch()}
    <div class="top">{logo(26)}<span class="badge">{ic('lock', 11, '#CFCAC0')}Document interne</span></div>
    <div>
    <h1>Des applications métier en quelques <em>heures</em>, pas en quelques mois.</h1>
    <p class="lead">YouCraft permet aux équipes métiers de créer elles-mêmes les outils qu’elles attendent, sans écrire de code. Tout ce qu’il faut pour le présenter tient sur cette page.</p>
    <div class="chips"><span class="chip">{ic('shield-check', 12, '#D7FF3F')}IA souveraine</span><span class="chip">{ic('rocket', 12, '#D7FF3F')}Prêt pour la production</span><span class="chip">{ic('layers', 12, '#D7FF3F')}Gouverné à l’échelle</span></div>
    </div>
    <div class="tailbox"><b>Notre terrain : la longue traîne</b><p>Les outils propres à chaque équipe, trop nombreux et pas assez prioritaires pour la DSI.</p>{tail_chart()}</div>
  </header>

  <main>
    <section>
      <div class="kicker">L’essentiel</div>
      <div class="grid2">
        <div class="card"><h3><span class="n">1</span>Le problème</h3><p>Dans chaque entreprise, des dizaines d’idées d’outils attendent des mois dans la file de la DSI. En attendant, les équipes <b>bricolent sur Excel</b>.</p></div>
        <div class="card"><h3><span class="n">2</span>Notre réponse</h3><p>Les équipes décrivent leur besoin et YouCraft construit <b>l’application complète</b> : écrans, données, accès. En ligne <b>en quelques heures</b>, sans développeur.</p></div>
        <div class="card"><h3><span class="n">3</span>Pour qui</h3><p>Les clients Leyton dont les <b>équipes métiers</b>, sans profil développeur, travaillent sur Excel et attendent la DSI.</p>
          <div class="no">{ic('circle-alert', 12, '#8F8C86')}<span>À éviter pour l’instant : les entreprises tech, au cycle de vente plus long et aux objections plus pointues.</span></div></div>
        <div class="card"><h3><span class="n">4</span>Notre place</h3><p>L’<b>AI application builder B2B</b> de la longue traîne. Nous travaillons <b>avec la DSI, pas à sa place</b> : elle garde les grands projets, les équipes gagnent leurs outils.</p></div>
      </div>
    </section>

    <section>
      <div class="kicker">Pourquoi YouCraft plutôt qu’un autre AI builder</div>
      <div class="diff">
        <div class="d usp"><span class="tagusp">USP</span><div class="i">{ic('shield-check', 18, '#fff')}</div><h3>Une IA souveraine et sécurisée, dès la conception</h3><p>Des modèles open source, une infrastructure souveraine, des données sous contrôle. L’argument qu’aucun concurrent ne peut reprendre.</p></div>
        <div class="d"><div class="i">{ic('rocket', 18, '#1E3BFF')}</div><h3>De vraies applications, pas des maquettes</h3><p>Backend, authentification, données persistantes et déploiement en un clic : prêt pour la production.</p></div>
        <div class="d"><div class="i">{ic('layers', 18, '#1E3BFF')}</div><h3>Gouverné à grande échelle</h3><p>Toutes les applications de l’entreprise pilotées, sécurisées et déployées sous contrôle central.</p></div>
      </div>
      <div class="note"><b>USP</b> (Unique Selling Proposition) : l’argument de vente unique, celui que nos concurrents n’ont pas.</div>
    </section>

    <section class="pitch">
      <div class="h"><b>Le pitch en 30 secondes</b><span>{ic('timer', 11, '#A9A49A')}À dire tel quel</span></div>
      <p>« Imaginez transformer vos fichiers Excel et vos idées métier en <b>vraies applications</b>, en quelques heures et sans une ligne de code. Avec YouCraft, vos équipes créent leurs propres outils grâce à l’IA, sans attendre la DSI. Trois choses nous distinguent. »</p>
      <ol><li><b>La souveraineté</b>Vos données restent maîtrisées, avec des modèles open source.</li><li><b>La production</b>Pas un prototype : une application complète, en ligne en un clic.</li><li><b>Le contrôle</b>Une gouvernance centrale, même quand les usages se multiplient.</li></ol>
      <p class="end">« Ce qui prenait des mois se fait en quelques heures, en toute sécurité. »</p>
    </section>

    <section>
      <div class="kicker">Les offres · par licence builder et par mois</div>
      <div class="plans">
        <div class="c0">Offre</div>
        <div class="ph p1"><small>Build</small><b>Starter</b></div><div class="ph p2"><small>Control</small><b>Business</b></div><div class="ph p3"><small>Protect</small><b>Sovereign</b></div><div class="ph p4"><small>Scale</small><b>Enterprise</b></div>
        <div class="rh">Licence builder</div><div class="price"><b>25 €</b><span>/ utilisateur</span></div><div class="price"><b>50 €</b><span>/ utilisateur</span></div><div class="price"><b>100 €</b><span>/ utilisateur</span></div><div class="price"><b>Sur contrat</b></div>
        <div class="rh">Utilisateurs d’application</div><div>Jusqu’à 5, sans authentification</div><div>10 inclus, puis 10 €</div><div>10 inclus, puis 10 €</div><div>Tarifs de volume</div>
        <div class="rh">Crédits IA / utilisateur / mois</div><div>100</div><div>300</div><div>500</div><div>Mutualisés</div>
        <div class="rh">Engagement</div><div>Trimestriel</div><div>Trimestriel</div><div>Trimestriel</div><div>Annuel, dès 12 k€</div>
      </div>
      <div class="rules">
        <div class="r"><b>{ic('trending-up', 13, '#1E3BFF')}Au-delà de 12 k€ par an</b>40 builders Starter, 20 Business ou 10 Sovereign : on passe en Enterprise.</div>
        <div class="r"><b>{ic('target', 13, '#1E3BFF')}Signaux de montée en gamme</b>Applications privées ou SSO <span class="arr">→</span> Business · données sensibles <span class="arr">→</span> Sovereign · plusieurs équipes ou gouvernance centrale <span class="arr">→</span> Enterprise.</div>
        <div class="r warn"><b>{ic('circle-alert', 13, '#B23A30')}Point de vigilance</b>Starter = applications publiques uniquement. Jamais de données confidentielles.</div>
      </div>
    </section>
  </main>

  <footer><span class="int">{ic('lock', 11, '#B23A30')}Usage interne uniquement · ne pas partager avec les clients</span><span>YouCraft · Leyton · v0.5</span></footer>
</div>
</body>
</html>
"""
open(os.path.join(HERE, 'YouCraft_OnePager_FR_v0.5.html'), 'w', encoding='utf-8').write(HTML)
print('ok', len(HTML))

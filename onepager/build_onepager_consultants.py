"""One-pager YouCraft pour les consultants (FR) : comprendre l'outil et savoir le vendre, sur une page A4.
Document autonome : polices intégrées (fonts.css), logo vectoriel officiel (brand_yc/yc_logo_data.js), icônes Lucide (src6/icons.js).
Contenus repris des textes déjà validés (one-pager interne, vidéo consultants) ; seules la méthode de vente,
les questions d'ouverture et les réponses aux objections sont nouvelles et reformulent ces mêmes arguments.
Usage : python3 build_onepager_consultants.py  →  YouCraft_Guide_Consultants_FR.html (puis PDF via render_pdf.cjs)"""
import json, os, re

HERE = os.path.dirname(os.path.abspath(__file__))
VID = os.path.join(HERE, '..', 'video', 'digicraft-lancement')
FONTS = open(os.path.join(HERE, 'fonts.css'), encoding='utf-8').read()
LOGO = json.loads(re.search(r'window\.YC_LOGO = (\{.*\});', open(os.path.join(VID, 'brand_yc', 'yc_logo_data.js'), encoding='utf-8').read(), re.S).group(1))
ICONS = json.loads(re.search(r'window\.ICONS=(\{.*\})', open(os.path.join(VID, 'src6', 'icons.js'), encoding='utf-8').read(), re.S).group(1))
NB = ' '


def logo(h, dark=True):
    g = ''.join(f'<stop offset="{o}" stop-color="{c}"/>' for o, c in LOGO['grad'])
    bx, by, bw, bh = LOGO['sym']['block']
    col = '#F5F2EA' if dark else '#0B0B0F'
    glyphs = ''.join(f'<path d="{d}" fill="{col}" transform="translate({LOGO["tx"]} {LOGO["ty"]})"/>' for d in LOGO['glyphs'])
    w = h * LOGO['w'] / LOGO['h']
    return (f'<svg class="logo" width="{w:.1f}" height="{h}" viewBox="0 0 {LOGO["w"]} {LOGO["h"]}" role="img" aria-label="YouCraft"><defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1">{g}</linearGradient></defs>'
            f'<path d="{LOGO["sym"]["dome"]}" fill="url(#lg)"/><path d="{LOGO["sym"]["quarter"]}" fill="{col}"/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" fill="#E4F53C"/>{glyphs}</svg>')


def mark(size):
    """Symbole seul (dôme, quart, bloc), en filigrane du bandeau."""
    bx, by, bw, bh = LOGO['sym']['block']
    st = 'fill="none" stroke="rgba(245,242,234,0.10)" stroke-width="1.1" vector-effect="non-scaling-stroke"'
    return f'<svg class="mark" width="{size}" height="{size}" viewBox="-8 -8 264 263" aria-hidden="true"><path d="{LOGO["sym"]["dome"]}" {st}/><path d="{LOGO["sym"]["quarter"]}" {st}/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" {st}/></svg>'


def ic(name, size=12, color='currentColor', sw=2):
    return f'<svg class="ic" width="{size}" height="{size}" viewBox="0 0 24 24" fill="none" stroke="{color}" stroke-width="{sw}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{ICONS[name]}</svg>'


# ---------------------------------------------------------------- contenus
STEPS = [
    ('message-square-text', 'Décrire', 'Le client explique son besoin avec ses mots, comme à un collègue.'),
    ('wand-sparkles', 'YouCraft construit', 'Écrans, données, circuits de validation et droits d’accès, en quelques minutes.'),
    ('rocket', 'Publier', 'En un clic, l’application est en ligne, sur ordinateur comme sur mobile.'),
]
CASES = [
    ('user-plus', 'Ressources humaines', 'Portail d’accueil des nouveaux arrivants'),
    ('plane', 'Logistique', 'Tour de contrôle des livraisons'),
    ('gauge', 'Industrie', 'Surveillance des machines'),
    ('layers', 'Production', 'Suivi des cycles et des moules'),
    ('trending-up', 'Commercial', 'Pipeline des opportunités'),
    ('receipt', 'Finance', 'Notes de frais et audits'),
]
SIGNALS = ['Des processus qui tournent sur Excel', 'Des relances par courriel',
           'Des informations saisies deux fois', 'Un outil attendu depuis des mois']
PLAY = [
    ('search', 'Repérer', 'Écoutez les irritants : tableurs partagés, relances, doubles saisies.'),
    ('presentation', 'Ouvrir', 'Le pitch de 30 secondes, puis la question qui ouvre la porte.'),
    ('square-pen', 'Atelier', 'En une séance, construisez avec eux une première application sur leur cas.'),
    ('chart-column', 'Pilote, puis étendre', 'Mesurez le temps gagné, puis élargissez à d’autres équipes.'),
]
QUESTIONS = ['Quels fichiers Excel font tourner votre équipe ?', 'Où ressaisissez‑vous les mêmes informations ?',
             'Quel outil attendez‑vous de l’informatique ?']
OBJ = [
    ('Notre informatique ne suivra pas.', 'YouCraft travaille à ses côtés, pas à sa place : elle garde la main sur la gouvernance et la sécurité.'),
    ('Nos données sont sensibles.', 'Hébergement en Europe, modèles open source sur infrastructure souveraine, offre Sovereign dédiée.'),
    ('Encore un prototype de plus.', 'De vraies applications en production : base de données, authentification, déploiement en un clic.'),
    ('Nous n’avons pas de développeurs.', 'C’est justement la cible : on décrit son besoin, sans écrire une ligne de code.'),
]
DIFF = [
    ('shield-check', 'Sécurisée et souveraine', 'Sécurité dès la conception, modèles open source, infrastructure souveraine.', True),
    ('app-window', 'Plus qu’un prototype', 'Des applications prêtes pour la production, pas des maquettes.', False),
    ('server-cog', 'Gouvernée à grande échelle', 'Toutes les applications métiers pilotées, sous contrôle total.', False),
]
PLANS = [
    ('Starter', f'25{NB}€', 'Applications publiques uniquement'),
    ('Business', f'50{NB}€', 'Applications privées et sécurisées'),
    ('Sovereign', f'100{NB}€', 'Données sensibles, souveraineté'),
    ('Enterprise', 'Sur contrat', f'Plusieurs équipes, dès 12{NB}k€ par{NB}an'),
]



CSS = """
:root { --ink: #0B0B0F; --stone: #F5F2EA; --paper: #FBFAF6; --line: #E3DED2; --muted: #6A675F;
  --text: #0B0B0F; --text2: #3F3D39; --um: #1E3BFF; --um-soft: #ECEFFF; --lime: #D7FF3F; }
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { background: #D9D5CB; }
body { font-family: 'DM Sans', system-ui, sans-serif; color: var(--text); -webkit-font-smoothing: antialiased; }
h1, h2, h3, h4, b, strong { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.ic { flex: none; display: inline-block; vertical-align: middle; }
@page { size: A4; margin: 0; }
.page { width: 210mm; height: 297mm; margin: 10mm auto; background: var(--paper); position: relative; overflow: hidden;
  display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(11,11,15,.18); }
@media print { html, body { background: var(--paper); } .page { margin: 0; box-shadow: none; height: 296.6mm; } }

.hero { flex: none; background: var(--ink); color: var(--stone); padding: 6.5mm 12mm 5.5mm; position: relative; overflow: hidden; }
.hero .mark { position: absolute; right: -16mm; top: -16mm; }
.hero .glow { position: absolute; right: 20mm; bottom: -46mm; width: 110mm; height: 76mm; border-radius: 50%;
  background: radial-gradient(closest-side, rgba(30,59,255,.6), rgba(30,59,255,0)); }
.top { display: flex; justify-content: space-between; align-items: center; position: relative; }
.tag { font-size: 6.8pt; letter-spacing: .14em; text-transform: uppercase; color: var(--lime); font-weight: 700;
  border: 1px solid rgba(215,255,63,.35); padding: 1mm 2.4mm; border-radius: 10mm; }
.hgrid { display: grid; grid-template-columns: 1fr 56mm; gap: 8mm; margin-top: 4mm; position: relative; align-items: end; }
.hero h1 { font-size: 21pt; line-height: 1.05; letter-spacing: -.035em; font-weight: 800; }
.hero h1 span { color: var(--lime); }
.hero .lead { margin-top: 2.8mm; font-size: 8.8pt; line-height: 1.45; color: #C9C5BB; }
.hero .lead b { color: var(--stone); }
.proof { display: grid; grid-template-columns: 1fr 1fr; gap: 2mm; }
.kpi { background: rgba(245,242,234,.06); border: 1px solid rgba(245,242,234,.12); border-radius: 2.6mm; padding: 2.4mm 3mm; }
.kpi strong { display: block; font-size: 16pt; letter-spacing: -.03em; line-height: 1; }
.kpi span { display: block; margin-top: .9mm; font-size: 7pt; color: #A9A59B; }
.kpi.wide { grid-column: span 2; display: flex; align-items: center; gap: 2mm; padding: 2mm 3mm; }
.kpi.wide span { margin: 0; font-size: 7pt; color: #D4D0C6; line-height: 1.3; }

.body { flex: 1; padding: 4.4mm 12mm 0; display: flex; flex-direction: column; gap: 3.4mm; }
.sh { display: flex; align-items: baseline; gap: 2.2mm; margin-bottom: 2.2mm; }
.sh i { font-style: normal; font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 7pt; color: var(--um); letter-spacing: .08em; }
.sh h2 { font-size: 11pt; letter-spacing: -.02em; font-weight: 800; }
.sh small { margin-left: auto; font-size: 7.2pt; color: var(--muted); }
.two { display: grid; grid-template-columns: 1fr 60mm; gap: 6mm; }

.flow { display: flex; align-items: stretch; gap: 1.4mm; }
.step { flex: 1; background: #fff; border: 1px solid var(--line); border-radius: 2.6mm; padding: 2.2mm 3mm; }
.sn { display: flex; align-items: center; justify-content: space-between; }
.sn span { font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 7pt; color: #B8B2A4; }
.step h4 { font-size: 8.8pt; letter-spacing: -.01em; display: flex; align-items: center; gap: 1.5mm; }
.step p { font-size: 7.6pt; line-height: 1.38; color: var(--text2); margin-top: .8mm; }
.arrow { display: flex; align-items: center; }

.cases { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.6mm; }
.case { padding: 2.2mm 2.6mm; border-radius: 2.4mm; background: var(--stone); }
.case b { display: flex; align-items: center; gap: 1.5mm; font-size: 7.9pt; letter-spacing: -.01em; }
.case span { display: block; font-size: 7.3pt; color: var(--text2); line-height: 1.3; margin-top: .7mm; }
.who { border-radius: 2.6mm; background: var(--um-soft); padding: 2.4mm 3mm; }
.who h4 { font-size: 8pt; display: flex; align-items: center; gap: 1.5mm; color: var(--um); }
.who ul { list-style: none; margin-top: 1.4mm; display: grid; gap: .9mm; }
.who li { font-size: 7.5pt; line-height: 1.3; display: flex; gap: 1.4mm; }
.who li .ic { margin-top: .3mm; }
.who .no { margin-top: 1.8mm; padding-top: 1.6mm; border-top: 1px solid rgba(30,59,255,.18); font-size: 7.1pt; line-height: 1.35; color: var(--text2); }

.play { list-style: none; display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; position: relative; }
.play::before { content: ''; position: absolute; left: 3mm; right: 3mm; top: 3mm; height: 1px; background: repeating-linear-gradient(90deg, #C9C3B4 0 1.4mm, transparent 1.4mm 2.6mm); }
.play li { position: relative; padding-right: 4mm; }
.pn { display: flex; width: 6mm; height: 6mm; border-radius: 50%; background: var(--ink); color: var(--lime); position: relative;
  font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 7.6pt; align-items: center; justify-content: center; box-shadow: 0 0 0 1.4mm var(--paper); }
.play h4 { font-size: 8.8pt; display: flex; align-items: center; gap: 1.4mm; letter-spacing: -.01em; margin-top: 2.2mm; }
.play p { font-size: 7.5pt; color: var(--text2); line-height: 1.38; margin-top: .7mm; }

.objs { display: grid; grid-template-columns: 1fr 1fr; gap: 2.2mm 5mm; }
.obj { border-top: 1px solid var(--line); padding-top: 1.8mm; }
.obj .q { font-size: 7.9pt; font-weight: 700; font-family: 'Plus Jakarta Sans'; display: flex; gap: 1.4mm; align-items: flex-start; letter-spacing: -.01em; }
.obj .a { font-size: 7.5pt; line-height: 1.38; color: var(--text2); margin-top: .8mm; display: flex; gap: 1.4mm; }
.obj .ic { margin-top: .4mm; }
.pitch { background: var(--ink); color: var(--stone); border-radius: 2.6mm; padding: 2.8mm 3.2mm; align-self: start; }
.pitch em { font-style: normal; font-size: 6.6pt; letter-spacing: .12em; text-transform: uppercase; color: var(--lime); font-weight: 700; display: flex; align-items: center; gap: 1.3mm; }
.pitch p { font-size: 7.7pt; line-height: 1.42; margin-top: 1.5mm; color: #E2DED4; }
.pitch p b { color: #fff; }
.pitch .q2 { margin-top: 2.4mm; padding-top: 2.2mm; border-top: 1px solid rgba(245,242,234,.14); }
.pitch .big { font-family: 'Plus Jakarta Sans'; font-weight: 700; font-size: 8.6pt; color: #fff; margin-top: 1.2mm; }
.qs { list-style: none; margin-top: 1.3mm; display: grid; gap: .8mm; }
.qs li { font-size: 7.4pt; line-height: 1.3; color: #E2DED4; padding-left: 2.8mm; position: relative; }
.qs li::before { content: ''; position: absolute; left: 0; top: 1.3mm; width: 1.1mm; height: 1.1mm; border-radius: 50%; background: var(--lime); }

.last { display: grid; grid-template-columns: 1fr 1fr; gap: 6mm; }
.diffs { display: grid; gap: 1.5mm; }
.diff { border: 1px solid var(--line); border-radius: 2.4mm; padding: 2mm 2.8mm; background: #fff; position: relative; }
.diff.usp { background: var(--ink); border-color: var(--ink); color: var(--stone); }
.diff em { position: absolute; right: 2.4mm; top: 2mm; font-style: normal; font-size: 6.2pt; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
  color: var(--ink); background: var(--lime); padding: .5mm 1.6mm; border-radius: 10mm; }
.dh { display: flex; align-items: center; gap: 1.6mm; }
.diff h4 { font-size: 8.3pt; letter-spacing: -.01em; }
.diff p { font-size: 7.3pt; line-height: 1.34; color: var(--text2); margin-top: .5mm; }
.diff.usp p { color: #C9C5BB; }
.plans { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5mm; }
.plan { border-radius: 2.4mm; padding: 2mm 2.8mm; background: var(--stone); }
.plan b { display: block; font-size: 6.8pt; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
.plan strong { display: inline-block; font-size: 13pt; letter-spacing: -.03em; margin-top: .5mm; line-height: 1.05; }
.plan small { font-size: 6.8pt; color: var(--muted); margin-left: 1.2mm; }
.plan span { display: block; font-size: 7.2pt; line-height: 1.3; color: var(--text2); margin-top: .6mm; }
.plan.p3 { background: var(--um); color: #fff; }
.plan.p3 b, .plan.p3 small { color: rgba(255,255,255,.72); }
.plan.p3 span { color: rgba(255,255,255,.9); }
.warn { margin-top: 1.6mm; font-size: 7.1pt; line-height: 1.35; color: var(--text2); display: flex; gap: 1.4mm; }
.warn .ic { margin-top: .3mm; }

.foot { flex: none; margin-top: auto; padding: 2.4mm 12mm; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; font-size: 7pt; color: var(--muted); }
.foot b { color: var(--text); font-size: 7pt; display: flex; align-items: center; gap: 1.3mm; }
@media screen and (max-width: 820px) { .page { transform-origin: top left; transform: scale(calc(100vw / 220mm)); margin: 0; } }
"""


def steps():
    out = []
    for i, (icn, t, d) in enumerate(STEPS):
        out.append(f'<div class="step"><div class="sn"><h4>{ic(icn, 12, "#1E3BFF")}{t}</h4><span>0{i + 1}</span></div><p>{d}</p></div>')
        if i < len(STEPS) - 1:
            out.append(f'<div class="arrow">{ic("chevron-right", 13, "#B8B2A4", 2.4)}</div>')
    return ''.join(out)


def cases():
    return ''.join(f'<div class="case"><b>{ic(i, 12, "#1E3BFF")}{t}</b><span>{d}</span></div>' for i, t, d in CASES)


def play():
    return ''.join(f'<li><div class="pn">{k + 1}</div><h4>{ic(i, 12, "#1E3BFF")}{t}</h4><p>{d}</p></li>' for k, (i, t, d) in enumerate(PLAY))


def objections():
    return ''.join(f'<div class="obj"><p class="q">{ic("message-square", 11, "#8C877C")}« {q} »</p><p class="a">{ic("check", 11, "#1E3BFF", 2.6)}{a}</p></div>' for q, a in OBJ)


def diffs():
    return ''.join(f'<div class="diff{" usp" if u else ""}">{"<em>Notre argument unique</em>" if u else ""}<div class="dh">{ic(i, 13, "#D7FF3F" if u else "#1E3BFF")}<h4>{t}</h4></div><p>{d}</p></div>' for i, t, d, u in DIFF)


def plans():
    return ''.join(f'<div class="plan p{k}"><b>{n}</b><strong>{p}</strong>{"<small>par builder et par mois</small>" if k < 3 else ""}<span>{d}</span></div>' for k, (n, p, d) in enumerate(PLANS))


HTML = f"""<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>YouCraft · Guide consultant</title>
<style>{FONTS}{CSS}</style></head>
<body><main class="page">
<header class="hero">{mark(300)}<div class="glow"></div>
  <div class="top">{logo(22)}<span class="tag">Guide consultant · usage interne</span></div>
  <div class="hgrid">
    <div>
      <h1>Les idées de vos clients<br>deviennent des <span>applications.</span></h1>
      <p class="lead"><b>YouCraft</b> est la plateforme d’intelligence artificielle qui transforme un besoin métier en application. Vos clients décrivent ce qu’ils veulent, YouCraft construit une application complète et sécurisée, <b>en quelques heures plutôt qu’en quelques mois</b>.</p>
    </div>
    <div class="proof">
      <div class="kpi"><strong>750+</strong><span>utilisateurs</span></div>
      <div class="kpi"><strong>350</strong><span>applications déployées</span></div>
      <div class="kpi wide">{ic("map-pin-check", 12, "#D7FF3F")}<span>Données hébergées en Europe, connexion sécurisée, droits d’accès par rôle</span></div>
    </div>
  </div>
</header>
<section class="body">
  <div><div class="sh"><i>01</i><h2>Comment ça marche</h2><small>Trois étapes, aucune ligne de code</small></div><div class="flow">{steps()}</div></div>

  <div class="two">
    <div><div class="sh"><i>02</i><h2>Ce que vos clients peuvent construire</h2></div><div class="cases">{cases()}</div></div>
    <div><div class="sh"><i>03</i><h2>Le bon client</h2></div>
      <div class="who"><h4>{ic("target", 11, "#1E3BFF")}Les signaux à repérer</h4><ul>{''.join(f'<li>{ic("check", 10, "#1E3BFF", 2.6)}{s}</li>' for s in SIGNALS)}</ul>
      <p class="no">Cible : les équipes métiers sans développeurs. On évite les entreprises tech.</p></div>
    </div>
  </div>

  <div><div class="sh"><i>04</i><h2>Vendre YouCraft en quatre temps</h2><small>Du premier échange au déploiement</small></div><ol class="play">{play()}</ol></div>

  <div class="two">
    <div><div class="sh"><i>05</i><h2>Répondre aux objections</h2><small>Ils disent… vous répondez</small></div><div class="objs">{objections()}</div></div>
    <div class="pitch"><em>{ic("sparkles", 10, "#D7FF3F")}Le pitch de 30 secondes</em>
      <p>« Imaginez transformer vos fichiers Excel et vos idées métier en <b>vraies applications</b>, sans écrire une ligne de code. Avec YouCraft, vos équipes les construisent elles‑mêmes, de façon <b>sécurisée et souveraine</b>, en quelques heures et non en quelques mois. »</p>
      <em class="q2">{ic("search", 10, "#D7FF3F")}La question qui ouvre la porte</em><p class="big">« Quel outil attendez‑vous depuis des mois ? »</p></div>
  </div>

  <div class="last">
    <div><div class="sh"><i>06</i><h2>Pourquoi YouCraft</h2></div><div class="diffs">{diffs()}</div></div>
    <div><div class="sh"><i>07</i><h2>Les offres</h2></div><div class="plans">{plans()}</div>
      <p class="warn">{ic("circle-alert", 10, "#C2410C")}<span>Starter : jamais de données confidentielles.</span></p></div>
  </div>
</section>
<footer class="foot"><b>{ic("lock", 10)}Usage interne uniquement. Ne pas partager avec les clients.</b><span>YouCraft · Leyton</span></footer>
</main></body></html>"""

out = os.path.join(HERE, 'YouCraft_Guide_Consultants_FR.html')
open(out, 'w', encoding='utf-8').write(HTML)
print(out)

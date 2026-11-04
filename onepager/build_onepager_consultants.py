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
    ('message-square-text', 'Décrire', 'Le besoin, avec ses mots, comme à un collègue.'),
    ('wand-sparkles', 'YouCraft construit', 'Écrans, données, validations et droits d’accès.'),
    ('rocket', 'Publier', 'En un clic, sur ordinateur comme sur mobile.'),
]
CASES = [
    ('user-plus', 'Intégration', 'Accueil des nouveaux arrivants'),
    ('plane', 'Logistique', 'Suivi des livraisons en direct'),
    ('gauge', 'Industrie', 'Surveillance des machines'),
    ('layers', 'Production', 'Suivi des cycles et des moules'),
    ('trending-up', 'Commercial', 'Pipeline des opportunités'),
    ('receipt', 'Finance', 'Notes de frais et audits'),
]
SIGNALS = ['Des équipes métiers sans développeurs', 'Des relances par courriel',
           'Des doubles saisies sur Excel', 'Un outil attendu depuis des mois']
PLAY = [
    ('search', 'Repérer', 'Écoutez les irritants : tableurs partagés, relances, doubles saisies.'),
    ('presentation', 'Ouvrir', 'Le pitch de 30 secondes, puis la question qui ouvre la porte.'),
    ('square-pen', 'Atelier', 'En une séance, construisez avec eux une première application sur leur cas.'),
    ('chart-column', 'Pilote, puis étendre', 'Mesurez le temps gagné, puis élargissez à d’autres équipes.'),
]
QUESTIONS = ['Quels fichiers Excel font tourner votre équipe ?', 'Où ressaisissez‑vous les mêmes informations ?',
             'Quel outil attendez‑vous de l’informatique ?']
OBJ = [
    ('Notre informatique ne suivra pas.', 'YouCraft travaille à ses côtés : elle garde la main sur la gouvernance et la sécurité.'),
    ('Nos données sont sensibles.', 'Hébergement en Europe, modèles open source, offre Sovereign dédiée.'),
    ('Encore un prototype de plus.', 'Base de données, authentification, déploiement en un clic : une vraie application.'),
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
:root { --ink: #0B0B0F; --ink2: #16161C; --stone: #F3EFE6; --paper: #FBFAF6; --line: #E4DFD3; --muted: #6E6B63;
  --text: #0B0B0F; --text2: #45433E; --um: #1E3BFF; --um-soft: #EDF0FF; --lime: #D7FF3F; --warn: #C2410C; }
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { background: #D9D5CB; }
body { font-family: 'DM Sans', system-ui, sans-serif; color: var(--text); -webkit-font-smoothing: antialiased; }
h1, h2, h3, h4, b, strong { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.ic { flex: none; display: inline-block; vertical-align: middle; }
@page { size: A4; margin: 0; }
.page { width: 210mm; height: 297mm; margin: 10mm auto; background: var(--paper); position: relative; overflow: hidden;
  display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(11,11,15,.18); }
@media print { html, body { background: var(--paper); } .page { margin: 0; box-shadow: none; height: 296.6mm; } }

/* ---------- Bandeau */
.hero { flex: none; background: var(--ink); color: var(--stone); padding: 6mm 12mm 5.5mm; position: relative; overflow: hidden; }
.hero .mark { position: absolute; left: 78mm; top: -30mm; }
.hero .glow { position: absolute; right: -10mm; top: -20mm; width: 110mm; height: 90mm; border-radius: 50%;
  background: radial-gradient(closest-side, rgba(30,59,255,.55), rgba(30,59,255,0)); }
.top { display: flex; justify-content: space-between; align-items: center; position: relative; }
.tag { font-size: 6.6pt; letter-spacing: .14em; text-transform: uppercase; color: var(--lime); font-weight: 700;
  border: 1px solid rgba(215,255,63,.35); padding: 1mm 2.4mm; border-radius: 10mm; }
.hgrid { display: grid; grid-template-columns: 1fr 60mm; gap: 8mm; margin-top: 3.5mm; position: relative; align-items: center; }
.hero h1 { font-size: 21pt; line-height: 1.04; letter-spacing: -.035em; font-weight: 800; }
.hero h1 span { color: var(--lime); }
.hero .lead { margin-top: 2.8mm; font-size: 8.6pt; line-height: 1.48; color: #C4C0B6; }
.hero .lead b { color: var(--stone); }
.stats { display: flex; gap: 0; margin-top: 3mm; }
.stat { padding: 0 4.5mm; border-left: 1px solid rgba(245,242,234,.16); }
.stat:first-child { padding-left: 0; border-left: 0; }
.stat strong { display: block; font-size: 15pt; letter-spacing: -.03em; line-height: 1; }
.stat span { display: block; font-size: 6.8pt; color: #A39F95; margin-top: .9mm; }
.stat.eu strong { font-size: 9pt; display: flex; align-items: center; gap: 1.2mm; height: 15pt; }

/* Maquette produit */
.mock { position: relative; }
.prompt { background: #fff; color: var(--ink); border-radius: 2.6mm 2.6mm 2.6mm .8mm; padding: 2mm 2.6mm; font-size: 7.2pt; line-height: 1.35;
  display: flex; gap: 1.6mm; align-items: flex-start; box-shadow: 0 10px 30px rgba(0,0,0,.35); width: 47mm; position: relative; z-index: 2; }
.prompt .ic { margin-top: .2mm; }
.win { margin: 1.2mm 0 0 9mm; background: #F7F5EF; border-radius: 2.6mm; overflow: hidden; box-shadow: 0 16px 40px rgba(0,0,0,.45); color: var(--ink); }
.wbar { height: 4.2mm; background: #fff; border-bottom: 1px solid #E8E4DA; display: flex; align-items: center; gap: .9mm; padding: 0 2mm; }
.wbar i { width: 1.3mm; height: 1.3mm; border-radius: 50%; background: #D9D4C8; }
.wbar b { margin-left: 1.4mm; font-size: 5.8pt; }
.wbar em { margin-left: auto; font-style: normal; font-size: 5.2pt; font-weight: 700; color: #0A7A35; background: #DDF7E5; padding: .3mm 1.2mm; border-radius: 4mm; }
.wbody { padding: 2mm; display: grid; gap: 1.4mm; }
.wk { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2mm; }
.wk div { background: #fff; border-radius: 1.2mm; padding: 1.2mm 1.4mm; }
.wk small { display: block; font-size: 4.8pt; color: var(--muted); }
.wk strong { font-size: 8pt; letter-spacing: -.02em; }
.wch { background: #fff; border-radius: 1.2mm; padding: 1.4mm; display: flex; align-items: flex-end; gap: 1.1mm; height: 7mm; }
.wch span { flex: 1; border-radius: .6mm .6mm 0 0; background: #C9D1FF; }
.wch span.hi { background: var(--um); }
.wrow { display: flex; align-items: center; justify-content: space-between; background: #fff; border-radius: 1.2mm; padding: 1mm 1.4mm; font-size: 5.4pt; }
.wrow em { font-style: normal; font-weight: 700; font-size: 4.8pt; padding: .2mm 1.1mm; border-radius: 4mm; }
.ok { color: #0A7A35; background: #DDF7E5; } .late { color: #B42318; background: #FDE4E1; }

/* ---------- Parties */
.body { flex: 1; padding: 4mm 12mm 0; display: flex; flex-direction: column; gap: 3.6mm; }
.part { display: flex; flex-direction: column; gap: 2.6mm; }
.part { position: relative; }
.chap { position: absolute; left: -9.2mm; top: 0; bottom: 0; width: 5mm; display: flex; align-items: center; gap: 2mm; writing-mode: vertical-rl; transform: rotate(180deg); flex-direction: row-reverse; white-space: nowrap; }
.chap span { font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 6pt; letter-spacing: .14em; text-transform: uppercase;
  color: #fff; background: var(--ink); padding: 2mm .9mm; border-radius: 1.2mm; }
.chap b { font-size: 6pt; letter-spacing: .14em; text-transform: uppercase; color: var(--muted); font-weight: 700; }
.chap i { flex: 1; width: 1px; background: var(--line); }
.sh { display: flex; align-items: baseline; gap: 2mm; margin-bottom: 2mm; }
.sh i { font-style: normal; font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 6.8pt; color: var(--um); letter-spacing: .06em; }
.sh h2 { font-size: 10.6pt; letter-spacing: -.02em; font-weight: 800; }
.sh small { margin-left: auto; font-size: 6.9pt; color: var(--muted); display: flex; align-items: center; gap: 1.2mm; }
.sh small.w { color: var(--warn); font-weight: 600; }
.g2 { display: grid; grid-template-columns: 1fr 60mm; gap: 5mm; }
.tile { flex: none; width: 6.2mm; height: 6.2mm; border-radius: 1.6mm; display: flex; align-items: center; justify-content: center; background: var(--um-soft); }

/* Étapes */
.flow { display: grid; grid-template-columns: 1fr 5mm 1fr 5mm 1fr; align-items: stretch; }
.step { background: #fff; border: 1px solid var(--line); border-radius: 2.6mm; padding: 2.2mm 2.8mm; display: flex; gap: 2.4mm; align-items: flex-start; }
.step h4 { font-size: 8.6pt; letter-spacing: -.01em; display: flex; align-items: baseline; gap: 1.4mm; }
.step h4 small { font-size: 6.4pt; color: #B0AA9B; font-weight: 800; }
.step p { font-size: 7.3pt; line-height: 1.36; color: var(--text2); margin-top: .5mm; }
.link { display: flex; align-items: center; justify-content: center; position: relative; }
.link::before { content: ''; position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: var(--um); opacity: .35; }
.link .ic { position: relative; background: var(--paper); border-radius: 50%; }

/* Cas d'usage */
.cases { display: grid; grid-template-columns: repeat(6, 1fr); gap: 1.6mm; }
.case { padding: 2mm 2.2mm; border-radius: 2.4mm; background: var(--stone); }
.case .tile { background: #fff; width: 5.6mm; height: 5.6mm; }
.ch { display: flex; align-items: center; gap: 1.5mm; }
.case b { display: block; font-size: 7.6pt; letter-spacing: -.01em; }
.case > span { display: block; font-size: 6.9pt; color: var(--text2); line-height: 1.3; margin-top: .5mm; }

/* Bon client */
.who { border-radius: 2.6mm; background: var(--um-soft); padding: 2.6mm 3.2mm; height: calc(100% - 7.6mm); display: flex; flex-direction: column; }
.who h4 { font-size: 7.8pt; display: flex; align-items: center; gap: 1.4mm; color: var(--um); }
.who ul { list-style: none; margin-top: 1.6mm; display: grid; gap: 1.1mm; }
.who li { font-size: 7.4pt; line-height: 1.3; display: flex; gap: 1.4mm; }
.who li .ic { margin-top: .3mm; }
.who .no { margin-top: auto; padding-top: 2mm; border-top: 1px solid rgba(30,59,255,.18); font-size: 7pt; line-height: 1.38; color: var(--text2); }
.who .no b { display: block; color: var(--um); font-size: 6.4pt; letter-spacing: .1em; text-transform: uppercase; }
.who .no span { display: block; font-family: 'Plus Jakarta Sans'; font-weight: 700; font-size: 8pt; color: var(--text); margin-top: .8mm; }

/* Méthode */
.play { list-style: none; display: grid; grid-template-columns: 1fr 1fr; gap: 1.6mm; }
.play li { background: #fff; border: 1px solid var(--line); border-radius: 2.6mm; padding: 2.4mm 2.8mm; display: flex; gap: 2.4mm; }
.pn { flex: none; display: flex; width: 6.2mm; height: 6.2mm; border-radius: 50%; background: var(--ink); color: var(--lime);
  font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 7.6pt; align-items: center; justify-content: center; }
.play h4 { font-size: 8.4pt; display: flex; align-items: center; gap: 1.3mm; letter-spacing: -.01em; }
.play p { font-size: 7.2pt; color: var(--text2); line-height: 1.36; margin-top: .5mm; }

/* Objections */
.objs { display: grid; grid-template-columns: 1fr 1fr; gap: 1.6mm; }
.obj { background: #fff; border: 1px solid var(--line); border-left: .8mm solid var(--um); border-radius: 1.4mm 2.4mm 2.4mm 1.4mm; padding: 2.2mm 2.8mm; }
.obj .q { font-size: 7.8pt; font-weight: 700; font-family: 'Plus Jakarta Sans'; letter-spacing: -.01em; }
.obj .a { font-size: 7.2pt; line-height: 1.36; color: var(--text2); margin-top: .7mm; display: flex; gap: 1.3mm; }
.obj .a .ic { margin-top: .4mm; }
.pitch { background: var(--ink); color: var(--stone); border-radius: 2.6mm; padding: 3mm 3.4mm; position: relative; overflow: hidden; display: flex; flex-direction: column; }
.pitch::after { content: '“'; position: absolute; right: 2mm; top: -6mm; font-family: 'Plus Jakarta Sans'; font-weight: 800; font-size: 60pt; color: rgba(215,255,63,.12); }
.pitch em { font-style: normal; font-size: 6.4pt; letter-spacing: .12em; text-transform: uppercase; color: var(--lime); font-weight: 700; display: flex; align-items: center; gap: 1.3mm; }
.pitch p { font-size: 7.7pt; line-height: 1.5; margin-top: 1.6mm; color: #DCD8CE; }
.pitch p b { color: #fff; }
.pitch .q2 { margin-top: auto; padding-top: 2.4mm; border-top: 1px solid rgba(245,242,234,.14); }
.pitch .sig { margin-top: auto; padding-top: 2.2mm; border-top: 1px solid rgba(245,242,234,.14); font-size: 7pt; color: #A39F95; display: flex; gap: 1.3mm; align-items: flex-start; line-height: 1.35; }
.pitch .sig .ic { margin-top: .3mm; }
.pitch .big { font-family: 'Plus Jakarta Sans'; font-weight: 700; font-size: 8.4pt; color: #fff; margin-top: 1mm; }

/* Différenciants et offres */
.diffs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.6mm; }
.diff { border: 1px solid var(--line); border-radius: 2.6mm; padding: 2.6mm 3mm; background: #fff; position: relative; }
.diff.usp { background: var(--ink); border-color: var(--ink); color: var(--stone); }
.diff.usp .tile { background: rgba(215,255,63,.12); }
.diff em { margin-left: auto; font-style: normal; font-size: 5.8pt; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
  color: var(--ink); background: var(--lime); padding: .5mm 1.5mm; border-radius: 10mm; }
.diff h4 { font-size: 8.2pt; letter-spacing: -.01em; }
.diff p { font-size: 7.1pt; line-height: 1.34; color: var(--text2); margin-top: 1.2mm; }
.diff.usp p { color: #C4C0B6; }
.plans { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.6mm; }
.plan { border-radius: 2.6mm; padding: 2.4mm 3mm; background: var(--stone); position: relative; }
.plan b { display: block; font-size: 6.4pt; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
.plan strong { display: block; font-size: 14pt; letter-spacing: -.03em; margin-top: .6mm; line-height: 1.05; }
.plan small { display: block; font-size: 6.4pt; color: var(--muted); margin-top: .3mm; }
.plan span { display: block; font-size: 7pt; line-height: 1.3; color: var(--text2); margin-top: 1.2mm; padding-top: 1.2mm; border-top: 1px solid rgba(11,11,15,.08); }
.plan.p3 { background: var(--um); color: #fff; }
.plan.p3 b, .plan.p3 small { color: rgba(255,255,255,.72); }
.plan.p3 span { color: rgba(255,255,255,.92); border-top-color: rgba(255,255,255,.2); }

.foot { flex: none; margin-top: auto; padding: 2.4mm 12mm; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; font-size: 6.8pt; color: var(--muted); }
.foot b { color: var(--text); font-size: 6.8pt; display: flex; align-items: center; gap: 1.3mm; }
@media screen and (max-width: 820px) { .page { transform-origin: top left; transform: scale(calc(100vw / 220mm)); margin: 0; } }
"""


def tile(name, color='#1E3BFF', size=11):
    return f'<span class="tile">{ic(name, size, color)}</span>'


def chap(n, t):
    return f'<div class="chap"><span>Partie {n}</span><b>{t}</b><i></i></div>'


def steps():
    out = []
    for i, (icn, t, d) in enumerate(STEPS):
        out.append(f'<div class="step">{tile(icn)}<div><h4>{t}<small>0{i + 1}</small></h4><p>{d}</p></div></div>')
        if i < len(STEPS) - 1:
            out.append(f'<div class="link">{ic("chevron-right", 11, "#1E3BFF", 2.4)}</div>')
    return ''.join(out)


def cases():
    return ''.join(f'<div class="case"><div class="ch">{tile(i, "#1E3BFF", 10)}<b>{t}</b></div><span>{d}</span></div>' for i, t, d in CASES)


def play():
    return ''.join(f'<li><div class="pn">{k + 1}</div><div><h4>{t}</h4><p>{d}</p></div></li>' for k, (i, t, d) in enumerate(PLAY))


def objections():
    return ''.join(f'<div class="obj"><p class="q">« {q} »</p><p class="a">{ic("check", 10, "#1E3BFF", 2.6)}{a}</p></div>' for q, a in OBJ)


def diffs():
    return ''.join(f'<div class="diff{" usp" if u else ""}"><div class="ch">{tile(i, "#D7FF3F" if u else "#1E3BFF")}<h4>{t}</h4>{"<em>Unique</em>" if u else ""}</div><p>{d}</p></div>' for i, t, d, u in DIFF)


def plans():
    return ''.join(f'<div class="plan p{k}"><b>{n}</b><strong>{p}</strong>{"<small>par builder et par mois</small>" if k < 3 else "<small>tarifs de volume</small>"}<span>{d}</span></div>' for k, (n, p, d) in enumerate(PLANS))


MOCK = f"""<div class="mock">
  <div class="prompt">{ic("sparkles", 10, "#1E3BFF")}<span>« Suivre mes livraisons et alerter le client en cas de retard. »</span></div>
  <div class="win"><div class="wbar"><i></i><i></i><i></i><b>Tour de contrôle</b><em>En ligne</em></div>
    <div class="wbody">
      <div class="wk"><div><small>En cours</small><strong>48</strong></div><div><small>À l’heure</small><strong>94{NB}%</strong></div><div><small>Retards</small><strong>3</strong></div></div>
      <div class="wch">{''.join(f'<span{" class=hi" if k == 5 else ""} style="height:{h}%"></span>' for k, h in enumerate([42, 58, 50, 72, 64, 92, 70]))}</div>
      <div class="wrow"><span>Livraison 2321 · Nantes</span><em class="late">Retard 20 min</em></div>
    </div></div>
</div>"""

HTML = f"""<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>YouCraft · Guide consultant</title>
<style>{FONTS}{CSS}</style></head>
<body><main class="page">
<header class="hero">{mark(250)}<div class="glow"></div>
  <div class="top">{logo(21)}<span class="tag">Guide consultant · usage interne</span></div>
  <div class="hgrid">
    <div>
      <h1>Les idées de vos clients<br>deviennent des <span>applications.</span></h1>
      <p class="lead"><b>YouCraft</b> transforme un besoin métier en application grâce à l’intelligence artificielle. Vos clients décrivent ce qu’ils veulent, YouCraft construit une application complète et sécurisée, <b>en quelques heures plutôt qu’en quelques mois</b>.</p>
      <div class="stats"><div class="stat"><strong>750+</strong><span>utilisateurs</span></div><div class="stat"><strong>350</strong><span>applications déployées</span></div>
        <div class="stat eu"><strong>{ic("map-pin-check", 11, "#D7FF3F")}Europe</strong><span>données hébergées en Europe</span></div></div>
    </div>
    {MOCK}
  </div>
</header>
<section class="body">
  <div class="part">{chap(1, 'Comprendre YouCraft')}
    <div><div class="sh"><i>01</i><h2>Comment ça marche</h2><small>Trois étapes, aucune ligne de code</small></div><div class="flow">{steps()}</div></div>
    <div><div class="sh"><i>02</i><h2>Ce que vos clients peuvent construire</h2><small>Chaque métier peut avoir son propre outil</small></div><div class="cases">{cases()}</div></div>
  </div>

  <div class="part">{chap(2, 'Vendre YouCraft')}
    <div class="g2">
      <div><div class="sh"><i>03</i><h2>La méthode en quatre temps</h2><small>Du premier échange au déploiement</small></div><ol class="play">{play()}</ol></div>
      <div><div class="sh"><i>04</i><h2>Le bon client</h2><small>Pas d’entreprises tech</small></div>
        <div class="who"><h4>{ic("target", 11, "#1E3BFF")}Les signaux à repérer</h4><ul>{''.join(f'<li>{ic("check", 10, "#1E3BFF", 2.6)}{s}</li>' for s in SIGNALS)}</ul>
        <p class="no"><b>La question qui ouvre la porte</b><span>« Quel outil attendez‑vous depuis des mois ? »</span></p></div></div>
    </div>
    <div class="g2">
      <div><div class="sh"><i>05</i><h2>Répondre aux objections</h2><small>Ils disent… vous répondez</small></div><div class="objs">{objections()}</div></div>
      <div class="pitch"><em>{ic("sparkles", 10, "#D7FF3F")}Le pitch de 30 secondes</em>
        <p>« Imaginez transformer vos fichiers Excel et vos idées métier en <b>vraies applications</b>, sans écrire une ligne de code. Avec YouCraft, vos équipes les construisent elles‑mêmes, de façon <b>sécurisée et souveraine</b>, en quelques heures et non en quelques mois. »</p></div>
    </div>
  </div>

  <div class="part">{chap(3, 'Convaincre et conclure')}
    <div><div class="sh"><i>06</i><h2>Pourquoi YouCraft</h2><small>Trois différenciants</small></div><div class="diffs">{diffs()}</div></div>
    <div><div class="sh"><i>07</i><h2>Les offres</h2><small class="w">{ic("circle-alert", 10, "#C2410C")}Starter : jamais de données confidentielles</small></div><div class="plans">{plans()}</div></div>
  </div>
</section>
<footer class="foot"><b>{ic("lock", 10)}Usage interne uniquement. Ne pas partager avec les clients.</b><span>YouCraft · Leyton</span></footer>
</main></body></html>"""

out = os.path.join(HERE, 'YouCraft_Guide_Consultants_FR.html')
open(out, 'w', encoding='utf-8').write(HTML)
print(out)

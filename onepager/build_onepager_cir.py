"""One-pager client YouCraft (FR, A4 portrait), à glisser dans les rapports de mission CIR ou IP Box.
Document autonome destiné aux clients : accroche sur l'effort de suivi du dossier CIR, vrai tableau de bord en visuel,
exemples d'applications de suivi R&D, tout ce que YouCraft permet au-delà, méthode, garanties et appel à l'action.
Usage : python3 build_onepager_cir.py  ->  YouCraft_OnePager_Client_CIR.html, puis PDF via render_pdf.cjs"""
import io, json, os, re, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from build_onepager_consultants import FONTS, logo, mark, ic  # noqa: E402
import qrcode
import qrcode.image.svg

HERE = os.path.dirname(os.path.abspath(__file__))
RDV = 'https://calendly.com/youcraft/30min'
NB = ' '
PH = json.loads(re.search(r'window\.YA_PHOTOS=(\{.*?\});', open(os.path.join(HERE, '..', 'presentation', 'apps_assets.js'), encoding='utf-8').read()).group(1))


def av(k, cls='av'):
    return f'<img class="{cls}" src="{PH["p%02d" % k]}" alt="">'


def qr(url, size):
    img = qrcode.make(url, image_factory=qrcode.image.svg.SvgPathImage, border=0, box_size=10)
    buf = io.BytesIO(); img.save(buf); s = buf.getvalue().decode()
    s = re.sub(r'<\?xml[^>]*\?>', '', s)
    s = re.sub(r'width="[^"]*mm" height="[^"]*mm"', f'width="{size}" height="{size}"', s, count=1)
    return s.replace('fill="#000000"', 'fill="#0B0B0F"')


ST = {'ok': ('#E7F6EC', '#1C8C4E'), 'in': ('#EEF0FF', '#1E3BFF'), 'wa': ('#FDF3E1', '#A8670A'), 'ba': ('#FCEBEB', '#CF3B3B')}


def badge(t, k):
    bg, fg = ST[k]
    return f'<span class="bd" style="background:{bg};color:{fg}"><i style="background:{fg}"></i>{t}</span>'


def spark(vals, c, w=34, h=10):
    mx, mn = max(vals), min(vals)
    pts = ' '.join(f'{i / (len(vals) - 1) * w:.1f},{h - 1 - (v - mn) / (mx - mn or 1) * (h - 2):.1f}' for i, v in enumerate(vals))
    return f'<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}"><polyline points="{pts}" fill="none" stroke="{c}" stroke-width="1.2" stroke-linejoin="round" stroke-linecap="round"/></svg>'


# ---------------------------------------------------------------- tableau de bord du bandeau
def dashboard(D):
    nav = [(a, b, k == 0) for k, (a, b) in enumerate(D['nav'])]
    navh = ''.join(f'<div class="dn{" on" if on else ""}">{ic(i, 7, "#0B0B0F" if on else "#A9A6A0")}<span>{t}</span>' + ('<em>3</em>' if t == D['badge_nav'] else '') + '</div>' for i, t, on in nav)
    kp = D['kpi']
    CK = {'u': ('#EEF0FF', '#1E3BFF'), 'g': ('#E7F6EC', '#1C8C4E'), 'l': ('#F3FAD0', '#5A6608')}
    kph = ''.join(f'<div class="dk"><div class="dkt"><span class="dki" style="background:{CK[c][0]}">{ic(i, 7, CK[c][1], 2.2)}</span><small>{l}</small></div>'
                  f'<div class="dkv"><b>{v}<u>{u}</u></b><em style="background:{CK[c][0]};color:{CK[c][1]}">{d}</em>{spark(sp, CK[c][1], 30, 10)}</div></div>' for i, l, v, u, d, sp, c in kp)
    # courbe : heures R&D par mois et objectif
    ctitle, l1, l2, H, T, mx, tip, _ = D['chart']
    W, Hh, pl, pb = 120, 46, 11, 8
    X = lambda i: pl + i / (len(H) - 1) * (W - pl - 3)
    Y = lambda v: 4 + (1 - v / mx) * (Hh - pb - 4)
    grid = ''.join(f'<line x1="{pl}" x2="{W - 3}" y1="{Y(v):.1f}" y2="{Y(v):.1f}" stroke="#EFEDE7" stroke-width=".5"/><text x="{pl - 2}" y="{Y(v) + 1.3:.1f}" text-anchor="end" font-size="3.3" fill="#A9A6A0">{v}</text>' for v in (0, mx // 3, 2 * mx // 3, mx))
    mo = ['J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S']
    xl = ''.join(f'<text x="{X(i):.1f}" y="{Hh - 1.5}" text-anchor="middle" font-size="3.3" fill="#A9A6A0">{m}</text>' for i, m in enumerate(mo))
    p = ' '.join(f'{X(i):.1f},{Y(v):.1f}' for i, v in enumerate(H)); t = ' '.join(f'{X(i):.1f},{Y(v):.1f}' for i, v in enumerate(T))
    area = f'M{X(0):.1f},{Y(0):.1f} L' + ' L'.join(p.split()) + f' L{X(len(H) - 1):.1f},{Y(0):.1f} Z'
    lx, ly = X(len(H) - 1), Y(H[-1])
    chart = (f'<svg viewBox="0 0 {W} {Hh}" class="dcv"><defs><linearGradient id="dg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1E3BFF" stop-opacity=".28"/><stop offset="1" stop-color="#1E3BFF" stop-opacity="0"/></linearGradient></defs>'
             f'{grid}{xl}<path d="{area}" fill="url(#dg)"/><polyline points="{t}" fill="none" stroke="#C9C4B8" stroke-width=".7" stroke-dasharray="1.6 1.6"/>'
             f'<line x1="{lx:.1f}" x2="{lx:.1f}" y1="{Y(mx):.1f}" y2="{Y(0):.1f}" stroke="#1E3BFF" stroke-opacity=".25" stroke-width=".5"/>'
             f'<polyline points="{p}" fill="none" stroke="#1E3BFF" stroke-width="1.2" stroke-linejoin="round" stroke-linecap="round"/>'
             f'<circle cx="{lx:.1f}" cy="{ly:.1f}" r="2.6" fill="#1E3BFF" fill-opacity=".18"/><circle cx="{lx:.1f}" cy="{ly:.1f}" r="1.4" fill="#fff" stroke="#1E3BFF" stroke-width=".9"/>'
             f'<g transform="translate({lx - 30:.1f} {ly - 2:.1f})"><rect width="26" height="10" rx="2" fill="#0B0B0F"/><text x="3" y="4.2" font-size="2.9" fill="#A9A6A0">{tip[0]}</text><text x="23.5" y="4.2" text-anchor="end" font-size="2.9" font-weight="700" fill="#D7FF3F">{tip[2]}</text><text x="3" y="8.4" font-size="3.6" font-weight="700" fill="#fff">{tip[1]}</text></g></svg>')
    # anneau : répartition des heures par projet
    dtitle, dseg, dval, dlab = D['donut']
    seg = [(n, v, c) for (n, v), c in zip(dseg, ('#1E3BFF', '#6F86FF', '#B9C4FF', '#D7FF3F'))]
    tot = sum(v for _, v, _ in seg); R, r0, cx = 15, 10, 18
    import math
    a0, arcs = -math.pi / 2, ''
    for n, v, c in seg:
        a1 = a0 + v / tot * 2 * math.pi; lg = 1 if a1 - a0 > math.pi else 0
        P = lambda a, rr: f'{cx + rr * math.cos(a):.2f} {cx + rr * math.sin(a):.2f}'
        arcs += f'<path d="M{P(a0, R)} A{R} {R} 0 {lg} 1 {P(a1 - .03, R)} L{P(a1 - .03, r0)} A{r0} {r0} 0 {lg} 0 {P(a0, r0)} Z" fill="{c}"/>'
        a0 = a1
    donut = (f'<svg viewBox="0 0 36 36" class="dnut">{arcs}<text x="18" y="17.6" text-anchor="middle" font-size="5.2" font-weight="800" fill="#0B0B0F">{dval}</text>'
             f'<text x="18" y="22" text-anchor="middle" font-size="2.9" fill="#8A8780">{dlab}</text></svg>')
    leg = ''.join(f'<div><i style="background:{c}"></i>{n}<b>{round(v / tot * 100)} %</b></div>' for n, v, c in seg)
    rows = D['rows']
    lead = lambda k: av(k) if isinstance(k, int) else f'<span class="rico">{ic(k, 7, "#1E3BFF")}</span>'
    tr = ''.join(f'<div class="dr">{lead(k)}<div class="dp"><b>{n}</b><span>{d}</span></div><b class="dh">{h}</b><span class="pb"><i style="width:{int(v * 100)}%"></i></span>{badge(*b)}</div>' for k, n, d, h, v, b in rows)
    team = ''.join(av(k, 'tav') for k in (19, 0, 3, 2))
    return f'''<div class="dwrap">
<div class="dash">
  <div class="db"><span class="dots"><b></b><b></b><b></b></span><span class="url">{ic("lock", 6, "#9A9790")}{D['url']}</span><em><i></i>En ligne</em></div>
  <div class="dbody">
    <aside class="dside"><div class="dl"><span>{ic(D['icon'], 7, "#0B0B0F", 2.4)}</span><b>{D['app']}<small>Northwind</small></b></div>{navh}
      <div class="dme">{av(D['user'][0])}<span>{D['user'][1]}<small>{D['user'][2]}</small></span></div></aside>
    <div class="dmain">
      <div class="dhd"><div><b>{D['h']}</b><small>{D['sub']}</small></div><span class="team">{team}</span><span class="chip p">{ic("download", 6, "#fff")}{D['btn']}</span></div>
      <div class="dks">{kph}</div>
      <div class="drow"><div class="dc"><div class="dct"><b>{ctitle}</b><span><i class="l1"></i>{l1}<i class="l2"></i>{l2}</span></div>{chart}</div>
        <div class="dc dd"><div class="dct"><b>{dtitle}</b></div><div class="ddi">{donut}<div class="dlg">{leg}</div></div></div></div>
      <div class="dc dt"><div class="dth">{''.join(f'<span>{c}</span>' for c in D['cols'])}</div>{tr}</div>
    </div>
  </div>
</div>
<div class="fl f1"><span>{ic("check", 7, "#0B0B0F", 3)}</span><div><b>{D['f1'][0]}</b><small>{D['f1'][1]}</small></div></div>
<div class="fl f2">{av(D['f2'][0])}<div><b>{D['f2'][1]}</b><small>{D['f2'][2]}</small></div></div>
</div>'''


# ---------------------------------------------------------------- contenus
CIR = [
    ('timer', 'Temps passés', 'Chaque chercheur saisit ses heures par projet. Les managers valident, le total est toujours à jour.',
     [(0, 'Camille L.', 'Projet Atlas', '38 h', 'ok', 'Validé'), (3, 'Hugo M.', 'Projet Nova', '24 h', 'in', 'À valider')]),
    ('activity', 'Procédés et essais', 'Hypothèses, protocoles et résultats consignés au fil des essais, datés et rattachés au bon projet.',
     [(None, 'Essai n° 14', 'Formulation B', '', 'ok', 'Concluant'), (None, 'Essai n° 15', 'Prototype v2', '', 'wa', 'À relancer')]),
    ('folder', 'Justificatifs', 'Toutes les pièces au même endroit, les manques signalés, l’export prêt pour votre dossier.',
     [(None, 'Comptes rendus', '42 pièces', '', 'ok', 'Complet'), (None, 'Diplômes', '5 sur 6', '', 'ba', 'Manquant')]),
]
MORE = [
    ('trending-up', 'CRM commercial', 'Prospects, opportunités, relances'),
    ('folder-kanban', 'Gestion de projet', 'Tâches, jalons, charge des équipes'),
    ('users', 'Tableau de bord RH', 'Effectifs, recrutements, intégrations'),
    ('gauge', 'Suivi de production', 'Cadences, incidents, maintenance'),
    ('clipboard-check', 'Circuits de validation', 'Achats, notes de frais, congés'),
    ('building-2', 'Portail client', 'Documents partagés, suivi des dossiers'),
]
STEPS = [('message-square-text', 'Décrivez votre besoin', 'Comme vous l’expliqueriez à un collègue.'),
         ('wand-sparkles', 'YouCraft construit l’application', 'Écrans, données, validations et droits d’accès.'),
         ('rocket', 'Partagez‑la à vos équipes', 'En un clic, sur ordinateur comme sur mobile.')]
WHY = [('shield-check', 'Vos données restent protégées', 'Hébergées en Europe, jamais utilisées pour entraîner l’IA.'),
       ('app-window', 'De vraies applications', 'Base de données, connexion sécurisée, mise en ligne en un clic.'),
       ('building-2', 'Signé Leyton', 'Conçue par CognitX, l’entité tech et IA du groupe.')]
TRIAL = [('J0 · 1 h', 'Nous créons ensemble votre première application'), ('J0 à J7', 'Vos équipes l’utilisent et l’améliorent'), ('J7', 'Bilan et suite, sans engagement')]


def cir_cards(cards):
    out = []
    for icn, t, d, rows in cards:
        r = ''
        for k, a, b, st, lab in rows:
            lead = av(k, 'mav') if isinstance(k, int) else f'<span class="mdot">{ic(k, 7, "#1E3BFF")}</span>'
            r += f'<div class="mr">{lead}<div class="mrt"><b>{a}</b><span>{b}</span></div>{badge(lab, st)}</div>'
        out.append(f'<div class="cc"><div class="cch"><span class="tl">{ic(icn, 13, "#fff", 2.2)}</span><h4>{t}</h4></div><p>{d}</p><div class="mini">{r}</div></div>')
    return ''.join(out)


def more(items):
    return ''.join(f'<div class="mo"><span class="tl s">{ic(i, 12, "#1E3BFF")}</span><div><b>{t}</b><span>{d}</span></div></div>' for i, t, d in items)


def steps():
    return ''.join(f'<div class="st"><span class="n">{k + 1}</span><b>{t}</b><span>{d}</span></div>' for k, (i, t, d) in enumerate(STEPS))


def why():
    return ''.join(f'<div class="wy"><span class="wi">{ic(i, 13, "#D7FF3F")}</span><div><b>{t}</b><span>{d}</span></div></div>' for i, t, d in WHY)


def trial():
    return ''.join(f'<div class="tr"><em>{a}</em><span>{b}</span></div>' + ('' if k == 2 else '<i></i>') for k, (a, b) in enumerate(TRIAL))


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
.bd{display:inline-flex;align-items:center;gap:.8mm;padding:.5mm 1.5mm;border-radius:3mm;font:700 5.6pt/1.2 'DM Sans';white-space:nowrap}
.bd i{width:1.1mm;height:1.1mm;border-radius:50%}
/* ---------- bandeau ---------- */
.hero{flex:none;background:var(--ink);color:var(--stone);padding:5.2mm 12mm 5.6mm;position:relative;overflow:hidden}
.hero .mark{position:absolute;left:40mm;top:-30mm}
.hero .glow{position:absolute;right:-20mm;top:-10mm;width:130mm;height:110mm;border-radius:50%;background:radial-gradient(closest-side,rgba(30,59,255,.6),rgba(30,59,255,0))}
.top{display:flex;justify-content:space-between;align-items:center;position:relative}
.tag{font-size:6.6pt;letter-spacing:.14em;text-transform:uppercase;color:var(--lime);font-weight:700;border:1px solid rgba(215,255,63,.35);padding:1mm 2.4mm;border-radius:10mm}
.hg{display:grid;grid-template-columns:84mm minmax(0,1fr);gap:8mm;margin-top:3.4mm;position:relative;align-items:center}
.k1{display:inline-flex;align-items:center;gap:1.6mm;font:700 6.8pt/1 'DM Sans';letter-spacing:.16em;color:var(--lime);text-transform:uppercase}
.k1::before{content:"";width:5mm;height:.5mm;background:var(--lime)}
.hero h1{margin-top:2.2mm;font-size:19.5pt;line-height:1.06;letter-spacing:-.035em;font-weight:800}
.hero h1 span{color:var(--lime)}
.hero .lead{margin-top:2.4mm;font-size:8.3pt;line-height:1.5;color:#C9C5BB}
.hero .lead b{color:var(--stone);font-weight:700}
.stats{display:flex;margin-top:3.2mm;padding-top:2.8mm;border-top:1px solid rgba(245,242,234,.14)}
.stat{flex:1;padding-left:3.4mm;border-left:1px solid rgba(245,242,234,.16)}
.stat:first-child{padding-left:0;border-left:0}
.stat strong{display:block;font-size:14pt;letter-spacing:-.03em;line-height:1;color:var(--lime)}
.stat:last-child strong{color:var(--stone)}
.stat span{display:block;font-size:6.6pt;line-height:1.3;color:#A39F95;margin-top:1mm}
/* tableau de bord */
.dwrap{position:relative;min-width:0}
.dash{background:#F6F5F1;color:var(--ink);border-radius:3mm;overflow:hidden;box-shadow:0 28px 60px rgba(0,0,0,.6),0 0 0 .3mm rgba(255,255,255,.12),0 0 90px rgba(79,107,255,.35);font-size:5.2pt}
.db{height:5mm;background:#fff;border-bottom:1px solid #ECEAE4;display:flex;align-items:center;gap:2mm;padding:0 2.4mm}
.db .dots{display:flex;gap:.9mm}.db .dots b{width:1.4mm;height:1.4mm;border-radius:50%;background:#E2DED4}
.db .url{display:flex;align-items:center;gap:1mm;padding:.5mm 2.2mm;border-radius:1.4mm;background:#F3F2EE;color:#77746E;font-size:4.8pt}
.db em{margin-left:auto;font-style:normal;display:flex;align-items:center;gap:.8mm;font:700 4.8pt 'DM Sans';color:#1C8C4E}
.db em i{width:1.2mm;height:1.2mm;border-radius:50%;background:#1C8C4E;box-shadow:0 0 0 .6mm rgba(28,140,78,.18)}
.dbody{display:flex}
.dside{width:19mm;flex:none;background:#0B0B0F;color:#F5F2EA;padding:2.2mm 1.4mm;display:flex;flex-direction:column;gap:.5mm}
.dl{display:flex;align-items:center;gap:1.2mm;margin-bottom:2mm;padding:0 .4mm}
.dl span{width:4.4mm;height:4.4mm;border-radius:1.2mm;background:#D7FF3F;display:flex;align-items:center;justify-content:center;flex:none}
.dl b{font-size:5.4pt;line-height:1.1}
.dl small{display:block;font:400 4.3pt 'DM Sans';color:#8F8C86}
.dn{display:flex;align-items:center;gap:1.1mm;padding:1.05mm 1.2mm;border-radius:1.1mm;color:#A9A6A0;font-size:4.9pt;white-space:nowrap}
.dn.on{background:#D7FF3F;color:#0B0B0F;font-weight:700}
.dn em{margin-left:auto;font-style:normal;min-width:2.8mm;height:2.8mm;border-radius:1.4mm;background:#CF3B3B;color:#fff;font:700 4pt/2.8mm 'DM Sans';text-align:center}
.dme{margin-top:auto;display:flex;align-items:center;gap:1.1mm;padding-top:1.8mm;border-top:1px solid rgba(245,242,234,.12);font:700 4.7pt 'DM Sans'}
.dme small{display:block;font-weight:400;color:#8F8C86;font-size:4.2pt}
.av{width:4.4mm;height:4.4mm;border-radius:50%;object-fit:cover;flex:none}
.dmain{flex:1;padding:2mm 2.2mm 2.2mm;display:flex;flex-direction:column;gap:1.4mm;min-width:0}
.dhd{display:flex;align-items:center;gap:1.6mm}
.dhd>div{margin-right:auto;min-width:0}
.dhd b{display:block;font-size:7.6pt;letter-spacing:-.015em}
.dhd small{display:block;color:#8A8780;font-size:4.5pt;margin-top:.3mm;white-space:nowrap}
.team{display:flex}
.tav{width:4mm;height:4mm;border-radius:50%;object-fit:cover;border:.4mm solid #F6F5F1;margin-left:-1.2mm}
.chip{display:flex;align-items:center;gap:.8mm;padding:.9mm 1.8mm;border-radius:1.2mm;font:700 4.8pt 'DM Sans';white-space:nowrap}
.chip.p{background:#1E3BFF;color:#fff;box-shadow:0 2px 6px rgba(30,59,255,.35)}
.dks{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.4mm}
.dk{background:#fff;border-radius:1.6mm;padding:1.5mm 1.7mm;box-shadow:0 1px 2px rgba(11,11,15,.05),0 0 0 .2mm #ECEAE4;position:relative;min-width:0;overflow:hidden}
.dkt{display:flex;align-items:center;gap:1mm}
.dki{width:3.6mm;height:3.6mm;border-radius:1mm;display:flex;align-items:center;justify-content:center}
.dk small{color:#77746E;font-size:4.6pt;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:13mm}
.dkv{display:flex;align-items:center;gap:1.2mm;margin-top:.9mm}
.dk b{font-size:9.6pt;letter-spacing:-.03em;line-height:1;white-space:nowrap}
.dk b u{text-decoration:none;font-size:5.4pt;color:#8A8780;margin-left:.4mm;letter-spacing:0}
.dkv>svg{margin-left:auto;width:5.2mm;height:2.6mm;flex:none}
.dk em{font-style:normal;font:700 4.2pt 'DM Sans';padding:.3mm 1mm;border-radius:2mm;white-space:nowrap}
.drow{display:grid;grid-template-columns:minmax(0,1fr) 26mm;gap:1.4mm}
.dc{background:#fff;border-radius:1.6mm;padding:1.5mm 1.8mm;box-shadow:0 1px 2px rgba(11,11,15,.05),0 0 0 .2mm #ECEAE4}
.dct{display:flex;align-items:center;justify-content:space-between;margin-bottom:.4mm}
.dct b{font-size:5.6pt}
.dct span{display:flex;align-items:center;gap:.8mm;color:#8A8780;font-size:4.3pt}
.dct .l1,.dct .l2{width:2.2mm;height:.6mm;border-radius:.3mm;background:var(--um);display:inline-block}
.dct .l2{background:#C9C4B8;margin-left:.8mm}
.dcv{display:block;width:100%;height:auto}
.ddi{display:flex;flex-direction:column;align-items:center;gap:1mm}
.dnut{width:15mm;height:15mm;display:block}
.dlg{width:100%;display:grid;grid-template-columns:1fr 1fr;gap:.5mm 1.2mm}
.dlg div{display:flex;align-items:center;gap:.6mm;font-size:4.2pt;color:#55534E;white-space:nowrap}
.dlg i{width:1.3mm;height:1.3mm;border-radius:.4mm;flex:none}
.dlg b{margin-left:auto;font-size:4.2pt}
.dth{display:grid;grid-template-columns:1fr 8mm 11mm 12mm;gap:1.4mm;padding:0 0 .8mm 5.6mm;border-bottom:1px solid #F0EEE8;font:700 4.1pt 'DM Sans';letter-spacing:.06em;text-transform:uppercase;color:#A9A6A0}
.dr{display:grid;grid-template-columns:4.4mm 1fr 8mm 11mm 12mm;gap:1.4mm;align-items:center;padding:.7mm 0;border-top:1px solid #F4F2EE}
.dth+.dr{border-top:0}
.dp{min-width:0}
.rico{width:4.4mm;height:4.4mm;border-radius:1.2mm;background:#EEF0FF;display:flex;align-items:center;justify-content:center}
.dp b{display:block;font-size:5.3pt}
.dp span{display:block;color:#8A8780;font-size:4.4pt;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dh{font-size:5.3pt;font-variant-numeric:tabular-nums}
.pb{height:1.1mm;border-radius:.6mm;background:#EEEDE8;overflow:hidden}
.pb i{display:block;height:100%;background:linear-gradient(90deg,#1E3BFF,#6F86FF);border-radius:.6mm}
.dash .bd{font-size:4.4pt;padding:.35mm 1.1mm;justify-self:start}
.fl{position:absolute;display:flex;align-items:center;gap:1.4mm;padding:1.6mm 2.2mm;border-radius:2.2mm;background:#fff;color:var(--ink);box-shadow:0 10px 26px rgba(0,0,0,.45);font-size:5pt;white-space:nowrap}
.fl b{display:block;font-size:5.6pt}
.fl small{display:block;color:#8A8780;font-size:4.6pt;margin-top:.2mm}
.f1{left:-8mm;top:40mm}
.f1>span{width:4.6mm;height:4.6mm;border-radius:50%;background:#D7FF3F;display:flex;align-items:center;justify-content:center}
.f2{right:-4mm;bottom:-4mm}
/* ---------- corps ---------- */
.body{flex:1;padding:3.8mm 12mm 0;display:flex;flex-direction:column;gap:2.9mm}
.sh{display:flex;align-items:center;gap:2.4mm;margin-bottom:2.2mm}
.sh .no{flex:none;width:6mm;height:6mm;border-radius:1.8mm;background:var(--ink);color:var(--lime);display:flex;align-items:center;justify-content:center;font:800 7pt 'Plus Jakarta Sans'}
.sh h2{font-size:11.4pt;letter-spacing:-.025em;font-weight:800;line-height:1.1}
.sh h2 span{color:var(--um)}
.sh small{margin-left:auto;font-size:7pt;color:var(--muted);padding-bottom:.4mm}
.tl{width:6.4mm;height:6.4mm;border-radius:1.8mm;background:var(--um);display:flex;align-items:center;justify-content:center;flex:none}
.tl.s{width:6.6mm;height:6.6mm;background:var(--um-soft)}
.cir{display:grid;grid-template-columns:repeat(3,1fr);gap:2.6mm}
.cc{background:#fff;border:1px solid var(--line);border-radius:3.2mm;padding:2.6mm 2.8mm;display:flex;flex-direction:column;box-shadow:0 6px 18px rgba(11,11,15,.05);position:relative;overflow:hidden}
.cc::before{content:"";position:absolute;left:0;right:0;top:0;height:.8mm;background:linear-gradient(90deg,var(--um),#6F86FF)}
.cch{display:flex;align-items:center;gap:2mm}
.cc h4{font-size:9.2pt;letter-spacing:-.015em}
.cc p{font-size:7.1pt;line-height:1.4;color:var(--text2);margin-top:1.4mm}
.mini{margin-top:auto;padding-top:2mm;display:flex;flex-direction:column;gap:1.1mm}
.mr{display:flex;align-items:center;gap:1.6mm;padding:1mm 1.5mm;border-radius:1.8mm;background:#F6F4EE;border:1px solid #EFEBE2}
.mav{width:4.6mm;height:4.6mm;border-radius:50%;object-fit:cover;flex:none}
.mdot{width:4.6mm;height:4.6mm;border-radius:1.2mm;background:var(--um-soft);display:flex;align-items:center;justify-content:center;flex:none}
.mrt{flex:1;min-width:0}
.mrt b{display:block;font-size:6.6pt;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mrt span{display:block;font-size:5.9pt;color:var(--muted);white-space:nowrap}
.beyond{background:linear-gradient(135deg,#EDF0FF,#F4F6FF);border:1px solid #DCE2FF;border-radius:3.6mm;padding:3mm 3.4mm}
.beyond .intro{font-size:7.3pt;line-height:1.4;color:var(--text2);margin:-.6mm 0 2.2mm 8.4mm}
.beyond .intro b{color:var(--ink)}
.mos{display:grid;grid-template-columns:repeat(3,1fr);gap:2mm}
.mo{display:flex;gap:2mm;align-items:center;background:#fff;border-radius:2.4mm;padding:1.4mm 2.2mm;box-shadow:0 3px 10px rgba(30,59,255,.06)}
.mo b{display:block;font-size:8pt;letter-spacing:-.01em}
.mo div>span{display:block;font-size:6.7pt;line-height:1.3;color:var(--text2);margin-top:.4mm}
.hw{display:grid;grid-template-columns:1fr 66mm;gap:5mm;align-items:stretch}
.how{display:grid;grid-template-columns:1fr 1fr 1fr;gap:2.4mm;position:relative}
.how::before{content:"";position:absolute;left:10mm;right:10mm;top:3.3mm;height:.4mm;background:repeating-linear-gradient(90deg,#C9C3B4 0 1.4mm,transparent 1.4mm 2.6mm)}
.st{position:relative}
.st .n{display:flex;width:6.6mm;height:6.6mm;border-radius:50%;background:var(--um);color:#fff;align-items:center;justify-content:center;font:800 7.4pt 'Plus Jakarta Sans';box-shadow:0 0 0 1.4mm var(--paper)}
.st b{display:block;margin-top:1.8mm;font-size:8.2pt;letter-spacing:-.01em}
.st span{display:block;font-size:6.9pt;line-height:1.4;color:var(--text2);margin-top:.6mm}
.why{display:flex;flex-direction:column;justify-content:center;gap:2.2mm;background:var(--ink);border-radius:3.4mm;padding:3mm 3.2mm}
.wy{display:flex;gap:2.2mm;align-items:center;color:var(--stone)}
.wi{flex:none;width:6mm;height:6mm;border-radius:1.8mm;background:rgba(215,255,63,.12);display:flex;align-items:center;justify-content:center}
.wy b{display:block;font-size:7.6pt;letter-spacing:-.01em}
.wy div>span{display:block;font-size:6.5pt;line-height:1.3;color:#BDB9AF;margin-top:.3mm}
/* appel à l'action */
.cta{background:linear-gradient(135deg,#1E3BFF 0%,#3550FF 60%,#5E78FF 100%);color:#fff;border-radius:4mm;padding:3.6mm 4.4mm;display:grid;grid-template-columns:1fr 30mm;gap:5mm;align-items:center;position:relative;overflow:hidden;box-shadow:0 14px 40px rgba(30,59,255,.3)}
.cta::after{content:"";position:absolute;right:-20mm;bottom:-34mm;width:90mm;height:70mm;border-radius:50%;background:radial-gradient(closest-side,rgba(215,255,63,.28),transparent)}
.cta>*{position:relative;z-index:1}
.cta .of{display:inline-flex;align-items:center;gap:1.4mm;font:700 6.6pt/1 'DM Sans';letter-spacing:.12em;text-transform:uppercase;color:var(--ink);background:var(--lime);padding:1.2mm 2.4mm;border-radius:4mm}
.cta h3{margin-top:2mm;font-size:12.8pt;letter-spacing:-.025em;line-height:1.12}
.cta h3 span{color:var(--lime)}
.trs{display:flex;align-items:stretch;margin-top:2.4mm}
.tr{flex:1;padding:1.8mm 2.2mm;border-radius:2.4mm;background:rgba(11,11,15,.22);border:1px solid rgba(255,255,255,.22)}
.tr em{display:block;font:700 6.6pt 'DM Sans';font-style:normal;color:var(--lime)}
.tr span{display:block;font-size:7.1pt;line-height:1.3;margin-top:.7mm}
.trs>i{width:4mm;flex:none;display:flex;align-items:center;justify-content:center}
.trs>i::before{content:"";width:1.6mm;height:1.6mm;border-right:1.5px solid var(--lime);border-top:1.5px solid var(--lime);transform:rotate(45deg)}
.qr{background:#fff;border-radius:3mm;padding:2.4mm;text-align:center;box-shadow:0 10px 30px rgba(11,11,15,.25)}
.qr svg{display:block;width:22mm;height:22mm}
.qr span{display:block;margin-top:1.4mm;font:700 6.4pt/1.25 'DM Sans';color:var(--ink)}
.ct{display:flex;gap:4mm;margin-top:2.4mm;font-size:6.9pt;color:rgba(255,255,255,.85)}
.ct span{flex:1;border-bottom:1px dotted rgba(255,255,255,.5);padding-bottom:1mm}
.foot{flex:none;margin-top:auto;padding:2mm 12mm;display:flex;justify-content:space-between;align-items:center;font-size:7pt;color:var(--muted)}
.foot b{color:var(--ink);font-size:7pt}
@media screen and (max-width:820px){.page{transform-origin:top left;transform:scale(calc(100vw / 220mm));margin:0}}
"""

def page(V):
    return f"""<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{V['title']}</title>
<style>{FONTS}{CSS}</style></head>
<body><main class="page">
<header class="hero">{mark(250)}<div class="glow"></div>
  <div class="top">{logo(22)}<span class="tag">{V['tag']}</span></div>
  <div class="hg"><div>
    <div class="k1">{V['kicker']}</div>
    <h1>{V['h1']}</h1>
    <p class="lead">{V['lead']}</p>
    <div class="stats"><div class="stat"><strong>1{NB}300+</strong><span>utilisateurs dans le groupe Leyton</span></div><div class="stat"><strong>500+</strong><span>applications déjà déployées</span></div>
      <div class="stat"><strong>0</strong><span>ligne de code à écrire</span></div></div>
  </div>{dashboard(V['dash'])}</div>
</header>
<section class="body">
  <div><div class="sh"><span class="no">01</span><h2>{V['s1'][0]}</h2><small>{V['s1'][1]}</small></div><div class="cir">{cir_cards(V['cards'])}</div></div>
  <div class="beyond"><div class="sh"><span class="no">02</span><h2>{V['s2'][0]}</h2><small>Une plateforme pour toutes vos équipes</small></div>
    <p class="intro">{V['s2'][1]}</p><div class="mos">{more(V['more'])}</div></div>
  <div class="hw"><div><div class="sh"><span class="no">03</span><h2>Simple comme <span>une conversation</span></h2></div><div class="how">{steps()}</div></div>
    <div class="why">{why()}</div></div>
  <div class="cta"><div>
      <span class="of">{ic("sparkles", 10, "#0B0B0F", 2.4)}Offre réservée aux clients Leyton</span>
      <h3>Testez YouCraft sur votre propre cas, <span>7 jours offerts</span></h3>
      <div class="trs">{trial()}</div>
      <div class="ct"><span>Votre consultant Leyton :</span><span>Contact :</span></div>
    </div>
    <div class="qr">{qr(RDV, 100)}<span>Scannez pour prendre rendez‑vous</span></div></div>
</section>
<footer class="foot"><b>YouCraft · Leyton CognitX</b><span>youcraft.tech</span></footer>
</main></body></html>"""


# ---------------------------------------------------------------- version diapositives (16:9)
SL_CSS = """
.deck{display:flex;flex-direction:column;gap:10mm;padding:10mm 0;align-items:center}
.sl{width:338.67mm;height:190.5mm;position:relative;overflow:hidden;background:var(--paper);box-shadow:0 20px 60px rgba(11,11,15,.18);display:flex;flex-direction:column}
.sl.dk1{background:var(--ink)}
.sl .z{zoom:var(--z);width:var(--w)}
.sl .hero{padding:9mm 12mm 0;flex:1;overflow:visible}
.sl .hero .glow{right:-10mm;top:0}
.sl .hero .hg{margin-top:9mm;grid-template-columns:88mm minmax(0,1fr);gap:12mm}
.sl .sb{flex:1;display:flex;flex-direction:column;justify-content:center;gap:4.2mm;padding:0 0 0}
.sl .shd{display:flex;justify-content:space-between;align-items:center;padding:7mm 12mm 0;flex:none}
.sl .shd span{font:700 7.6pt 'DM Sans';letter-spacing:.16em;text-transform:uppercase;color:var(--um)}
.sl .sft{flex:none;display:flex;justify-content:space-between;align-items:center;padding:0 12mm 6mm;font-size:8pt;color:var(--muted)}
.sl .sft b{color:var(--ink)}
.sl.dk1 .sft{color:#8A8780}.sl.dk1 .sft b{color:var(--stone)}
.s2{gap:3.2mm}.s2 .r2{display:grid;grid-template-columns:1.32fr 1fr;gap:4mm;align-items:stretch}.s2 .r3{display:grid;grid-template-columns:1fr 70mm;gap:4mm;align-items:stretch}
.s2 .hw1{display:flex;flex-direction:column;justify-content:center;background:#fff;border-radius:3.4mm;padding:3mm 3.6mm;box-shadow:0 0 0 .25mm var(--line)}
.s2 .hw1 .how{margin-top:3mm}
@media print{.deck{padding:0;gap:0}.sl{box-shadow:none;break-after:page}}
"""


def slides(V):
    foot = '<footer class="sft"><b>YouCraft · Leyton CognitX</b><span>youcraft.tech</span></footer>'
    foot2 = f'<footer class="sft" style="padding-top:2.4mm">{logo(14, False)}<span>youcraft.tech</span></footer>'
    head = lambda t: f'<div class="shd">{logo(20, False)}<span>{t}</span></div>'
    return f"""<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{V['title']}</title>
<style>{FONTS}{CSS}{SL_CSS}@page{{size:338.67mm 190.5mm;margin:0}}</style></head>
<body><div class="deck">
<section class="sl dk1"><div class="z" style="--z:1.5;--w:225.8mm;height:calc(190.5mm / 1.5);display:flex;flex-direction:column">
<header class="hero">{mark(250)}<div class="glow"></div>
  <div class="top">{logo(22)}<span class="tag">{V['tag']}</span></div>
  <div class="hg"><div>
    <div class="k1">{V['kicker']}</div>
    <h1>{V['h1']}</h1>
    <p class="lead">{V['lead']}</p>
    <div class="stats"><div class="stat"><strong>1{NB}300+</strong><span>utilisateurs dans le groupe Leyton</span></div><div class="stat"><strong>500+</strong><span>applications déjà déployées</span></div>
      <div class="stat"><strong>0</strong><span>ligne de code à écrire</span></div></div>
  </div>{dashboard(V['dash'])}</div>
</header>{foot}</div></section>
<section class="sl"><div class="z" style="--z:1.12;--w:302.4mm;height:calc(190.5mm / 1.12);display:flex;flex-direction:column">
<div class="body sb s2" style="padding:6mm 12mm 0">
  <div><div class="sh"><span class="no">01</span><h2>{V['s1'][0]}</h2><small>{V['s1'][1]}</small></div><div class="cir">{cir_cards(V['cards'])}</div></div>
  <div class="r2"><div class="beyond"><div class="sh"><span class="no">02</span><h2>{V['s2'][0]}</h2></div>
    <p class="intro">{V['s2'][1]}</p><div class="mos">{more(V['more'])}</div></div>
    <div class="hw1"><div class="sh"><span class="no">03</span><h2>Simple comme <span>une conversation</span></h2></div><div class="how">{steps()}</div></div></div>
  <div class="r3"><div class="cta"><div>
      <span class="of">{ic("sparkles", 10, "#0B0B0F", 2.4)}Offre réservée aux clients Leyton</span>
      <h3>Testez YouCraft sur votre propre cas, <span>7 jours offerts</span></h3>
      <div class="trs">{trial()}</div>
      <div class="ct"><span>Votre consultant Leyton :</span><span>Contact :</span></div>
    </div>
    <div class="qr">{qr(RDV, 100)}<span>Scannez pour prendre rendez‑vous</span></div></div>
    <div class="why">{why()}</div></div>
</div>{foot2}</div></section>
</div></body></html>"""

from variants_onepager import VARIANTS  # noqa: E402
for key in (sys.argv[1:] or VARIANTS):
    V = VARIANTS[key]
    out = os.path.join(HERE, V['file'] + '.html')
    open(out, 'w', encoding='utf-8').write(page(V))
    open(out.replace('.html', '_Slides.html'), 'w', encoding='utf-8').write(slides(V))
    print(out)

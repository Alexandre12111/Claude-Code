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
def dashboard():
    nav = [('layout-dashboard', 'Vue d’ensemble', 1), ('timer', 'Temps passés', 0), ('activity', 'Essais', 0), ('folder', 'Justificatifs', 0), ('chart-column', 'Rapports', 0)]
    navh = ''.join(f'<div class="dn{" on" if on else ""}">{ic(i, 7, "#1E3BFF" if on else "#8A8780")}{t}</div>' for i, t, on in nav)
    kp = [('Heures R&amp;D', '1 284 h', '+12 %', [40, 44, 43, 50, 54, 58, 63], '#1C8C4E'), ('Projets éligibles', '6 / 8', '+2', [3, 4, 4, 5, 5, 6, 6], '#1C8C4E'),
          ('Justificatifs', '128', '94 % complets', [60, 70, 78, 84, 88, 92, 94], '#1C8C4E')]
    kph = ''.join(f'<div class="dk"><small>{l}</small><b>{v}</b><span><em style="color:{c}">{d}</em>{spark(sp, c)}</span></div>' for l, v, d, sp, c in kp)
    # courbe heures R&D par mois et objectif
    H = [96, 104, 118, 112, 131, 140, 152, 147, 163]
    T = [100, 106, 112, 118, 124, 130, 136, 142, 148]
    W, Hh, pl, pb = 170, 44, 14, 9
    mx = 180
    X = lambda i: pl + i / (len(H) - 1) * (W - pl - 2)
    Y = lambda v: 2 + (1 - v / mx) * (Hh - pb - 2)
    grid = ''.join(f'<line x1="{pl}" x2="{W - 2}" y1="{Y(v):.1f}" y2="{Y(v):.1f}" stroke="#EEEBE3" stroke-width=".5"/><text x="{pl - 2}" y="{Y(v) + 1.5:.1f}" text-anchor="end" font-size="3.6" fill="#9A9790">{v}</text>' for v in (0, 60, 120, 180))
    mo = ['jan', 'fév', 'mar', 'avr', 'mai', 'juin', 'juil', 'août', 'sep']
    xl = ''.join(f'<text x="{X(i):.1f}" y="{Hh - 2}" text-anchor="middle" font-size="3.6" fill="#9A9790">{m}</text>' for i, m in enumerate(mo))
    p = ' '.join(f'{X(i):.1f},{Y(v):.1f}' for i, v in enumerate(H)); t = ' '.join(f'{X(i):.1f},{Y(v):.1f}' for i, v in enumerate(T))
    area = f'M{X(0):.1f},{Y(0):.1f} L' + ' L'.join(p.split()) + f' L{X(len(H) - 1):.1f},{Y(0):.1f} Z'
    lx, ly = X(len(H) - 1), Y(H[-1])
    chart = (f'<svg viewBox="0 0 {W} {Hh}" class="dcv"><defs><linearGradient id="dg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1E3BFF" stop-opacity=".22"/><stop offset="1" stop-color="#1E3BFF" stop-opacity="0"/></linearGradient></defs>'
             f'{grid}{xl}<path d="{area}" fill="url(#dg)"/><polyline points="{t}" fill="none" stroke="#B5B0A5" stroke-width=".8" stroke-dasharray="2 2"/>'
             f'<polyline points="{p}" fill="none" stroke="#1E3BFF" stroke-width="1.3" stroke-linejoin="round" stroke-linecap="round"/>'
             f'<circle cx="{lx:.1f}" cy="{ly:.1f}" r="2" fill="#fff" stroke="#1E3BFF" stroke-width="1"/>'
             f'<g transform="translate({lx - 31:.1f} {ly + 3:.1f})"><rect width="27" height="9" rx="2" fill="#0B0B0F"/><text x="13.5" y="6" text-anchor="middle" font-size="4" font-weight="700" fill="#fff">163 h · sep</text></g></svg>')
    rows = [(0, 'Atlas', 'Capteur nouvelle génération', '412 h', 0.92, ('Éligible', 'ok')), (3, 'Nova', 'Procédé de recyclage', '326 h', 0.74, ('Éligible', 'ok'))]
    tr = ''.join(f'<div class="dr">{av(k)}<div class="dp"><b>{n}</b><span>{d}</span></div><b class="dh">{h}</b><span class="pb"><i style="width:{int(v * 100)}%"></i></span>{badge(*b)}</div>' for k, n, d, h, v, b in rows)
    return f'''<div class="dash">
  <div class="db"><span class="dots"><b></b><b></b><b></b></span><span class="url">{ic("lock", 6, "#9A9790")}suivi-rd.northwind.youcraft.build</span><em><i></i>En ligne</em></div>
  <div class="dbody">
    <aside class="dside"><div class="dl"><span>{ic("flask" if False else "activity", 7, "#fff", 2.2)}</span><b>Suivi R&amp;D</b></div>{navh}<div class="dme">{av(19)}<span>Claire D.<small>Direction R&amp;D</small></span></div></aside>
    <div class="dmain">
      <div class="dhd"><div><b>Vue d’ensemble R&amp;D</b><small>Exercice 2026 · 8 projets</small></div><span class="chip">{ic("calendar", 6, "#45433E")}Jan. – sep. 2026</span><span class="chip p">{ic("download", 6, "#fff")}Exporter</span></div>
      <div class="dks">{kph}</div>
      <div class="dc"><div class="dct"><b>Heures R&amp;D par mois</b><span><i class="l1"></i>Réalisé<i class="l2"></i>Objectif</span></div>{chart}</div>
      <div class="dc dt"><div class="dct"><b>Projets</b><span>Trier par heures ▾</span></div>{tr}</div>
    </div>
  </div>
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


def cir_cards():
    out = []
    for icn, t, d, rows in CIR:
        r = ''
        for k, a, b, h, st, lab in rows:
            lead = av(k, 'mav') if k is not None else f'<span class="mdot">{ic("file-pen-line" if icn == "folder" else "activity", 7, "#1E3BFF")}</span>'
            r += f'<div class="mr">{lead}<div class="mrt"><b>{a}</b><span>{b}{(" · " + h) if h else ""}</span></div>{badge(lab, st)}</div>'
        out.append(f'<div class="cc"><div class="cch"><span class="tl">{ic(icn, 13, "#fff", 2.2)}</span><h4>{t}</h4></div><p>{d}</p><div class="mini">{r}</div></div>')
    return ''.join(out)


def more():
    return ''.join(f'<div class="mo"><span class="tl s">{ic(i, 12, "#1E3BFF")}</span><div><b>{t}</b><span>{d}</span></div></div>' for i, t, d in MORE)


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
.hero{flex:none;background:var(--ink);color:var(--stone);padding:6mm 12mm 5.4mm;position:relative;overflow:hidden}
.hero .mark{position:absolute;left:40mm;top:-30mm}
.hero .glow{position:absolute;right:-20mm;top:-10mm;width:130mm;height:110mm;border-radius:50%;background:radial-gradient(closest-side,rgba(30,59,255,.6),rgba(30,59,255,0))}
.top{display:flex;justify-content:space-between;align-items:center;position:relative}
.tag{font-size:6.6pt;letter-spacing:.14em;text-transform:uppercase;color:var(--lime);font-weight:700;border:1px solid rgba(215,255,63,.35);padding:1mm 2.4mm;border-radius:10mm}
.hg{display:grid;grid-template-columns:88mm 1fr;gap:6mm;margin-top:3.6mm;position:relative;align-items:center}
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
.dash{background:#F7F7F5;color:var(--ink);border-radius:2.6mm;overflow:hidden;box-shadow:0 24px 50px rgba(0,0,0,.55),0 0 70px rgba(79,107,255,.3);font-size:5.4pt}
.db{height:5.4mm;background:#fff;border-bottom:1px solid #E7E6E2;display:flex;align-items:center;gap:2mm;padding:0 2.4mm}
.db .dots{display:flex;gap:.9mm}.db .dots b{width:1.4mm;height:1.4mm;border-radius:50%;background:#DEDAD0}
.db .url{display:flex;align-items:center;gap:1mm;padding:.6mm 2mm;border-radius:1.2mm;background:#F3F3F1;color:#77746E;font-size:5pt}
.db em{margin-left:auto;font-style:normal;display:flex;align-items:center;gap:.8mm;font:700 5pt 'DM Sans';color:#1C8C4E}
.db em i{width:1.2mm;height:1.2mm;border-radius:50%;background:#1C8C4E;box-shadow:0 0 0 .6mm rgba(28,140,78,.18)}
.dbody{display:flex}
.dside{width:20mm;flex:none;background:#FBFBFA;border-right:1px solid #E7E6E2;padding:2mm 1.4mm;display:flex;flex-direction:column;gap:.6mm}
.dl{display:flex;align-items:center;gap:1.2mm;margin-bottom:1.6mm;padding:0 .4mm}
.dl span{width:4.2mm;height:4.2mm;border-radius:1.2mm;background:var(--um);display:flex;align-items:center;justify-content:center}
.dl b{font-size:5.8pt}
.dn{display:flex;align-items:center;gap:1.1mm;padding:1mm 1.1mm;border-radius:1mm;color:#55534E;font-size:5.1pt;white-space:nowrap}
.dn.on{background:#EEF0FF;color:var(--um);font-weight:700}
.dme{margin-top:auto;display:flex;align-items:center;gap:1.1mm;padding-top:1.6mm;font:700 4.9pt 'DM Sans'}
.dme small{display:block;font-weight:400;color:#8A8780;font-size:4.4pt}
.av{width:4.2mm;height:4.2mm;border-radius:50%;object-fit:cover;flex:none}
.dmain{flex:1;padding:2mm 2.2mm;display:flex;flex-direction:column;gap:1.5mm;min-width:0}
.dhd{display:flex;align-items:center;gap:1.2mm}
.dhd>div{margin-right:auto}
.dhd b{display:block;font-size:7.4pt;letter-spacing:-.01em}
.dhd small{display:block;color:#8A8780;font-size:4.8pt;margin-top:.3mm}
.chip{display:flex;align-items:center;gap:.8mm;padding:.8mm 1.6mm;border-radius:1.1mm;border:1px solid #E1DFDA;background:#fff;font:700 4.8pt 'DM Sans';color:#45433E;white-space:nowrap}
.chip.p{background:var(--um);border-color:var(--um);color:#fff}
.dks{display:grid;grid-template-columns:repeat(3,1fr);gap:1.4mm}
.dk{background:#fff;border:1px solid #E7E6E2;border-radius:1.4mm;padding:1.4mm 1.6mm}
.dk small{display:block;color:#77746E;font-size:4.7pt;white-space:nowrap}
.dk b{display:block;font-size:8.6pt;letter-spacing:-.02em;margin-top:.5mm;white-space:nowrap}
.dk span{display:flex;align-items:center;justify-content:space-between;margin-top:.4mm;gap:1mm}
.dk em{font-style:normal;font:700 4.5pt 'DM Sans';white-space:nowrap}
.dk svg{width:7mm;height:2.2mm}
.dc{background:#fff;border:1px solid #E7E6E2;border-radius:1.4mm;padding:1.4mm 1.8mm}
.dct{display:flex;align-items:center;justify-content:space-between;margin-bottom:.6mm}
.dct b{font-size:5.8pt}
.dct span{display:flex;align-items:center;gap:1mm;color:#77746E;font-size:4.6pt}
.dct .l1,.dct .l2{width:2.4mm;height:.6mm;border-radius:.3mm;background:var(--um);display:inline-block}
.dct .l2{background:#B5B0A5;margin-left:1mm}
.dcv{display:block;width:100%;height:auto}
.dr{display:grid;grid-template-columns:4.2mm 1fr auto 10mm auto;gap:1.4mm;align-items:center;padding:.9mm 0;border-top:1px solid #F0EFEC}
.dr:nth-of-type(2){border-top:0}
.dp b{display:block;font-size:5.4pt}
.dp span{display:block;color:#8A8780;font-size:4.6pt;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dh{font-size:5.4pt;font-variant-numeric:tabular-nums}
.pb{height:1mm;border-radius:.5mm;background:#EEEEEB;overflow:hidden}
.pb i{display:block;height:100%;background:var(--um);border-radius:.5mm}
.dash .bd{font-size:4.6pt;padding:.35mm 1.1mm}
/* ---------- corps ---------- */
.body{flex:1;padding:4.2mm 12mm 0;display:flex;flex-direction:column;gap:3.4mm}
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
.mo{display:flex;gap:2mm;align-items:center;background:#fff;border-radius:2.4mm;padding:1.6mm 2.2mm;box-shadow:0 3px 10px rgba(30,59,255,.06)}
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
    <p class="lead">Temps passés, procédés, essais, justificatifs{NB}: et si ce suivi se faisait naturellement, toute l’année{NB}? Avec <b>YouCraft</b>, vos équipes décrivent leur besoin et obtiennent leur propre application <b>en quelques heures</b>.</p>
    <div class="stats"><div class="stat"><strong>1{NB}300+</strong><span>utilisateurs dans le groupe Leyton</span></div><div class="stat"><strong>500+</strong><span>applications déjà déployées</span></div>
      <div class="stat"><strong>0</strong><span>ligne de code à écrire</span></div></div>
  </div>{dashboard()}</div>
</header>
<section class="body">
  <div><div class="sh"><span class="no">01</span><h2>Un suivi R&amp;D qui se fait <span>au fil de l’eau</span></h2><small>Exemples d’applications créées avec YouCraft</small></div><div class="cir">{cir_cards()}</div></div>
  <div class="beyond"><div class="sh"><span class="no">02</span><h2>Et <span>bien au‑delà</span> du CIR</h2><small>Une plateforme pour toutes vos équipes</small></div>
    <p class="intro"><b>YouCraft n’est pas qu’un outil de suivi CIR</b>{NB}: chaque équipe y crée l’outil qu’elle attend depuis longtemps.</p><div class="mos">{more()}</div></div>
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
<footer class="foot"><b>YouCraft · Leyton CognitX</b><span>youcraft.build</span></footer>
</main></body></html>"""

out = os.path.join(HERE, 'YouCraft_OnePager_Client_CIR.html')
open(out, 'w', encoding='utf-8').write(HTML)
print(out)

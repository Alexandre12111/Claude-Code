"""Slide unique de présentation d'Intertek, à la charte YouCraft (1920 x 1080).
Contenu repris de « Présentation Intertek France » (pptx fourni) : raison d'être, chiffres monde, approche ATIC,
divisions, réseau France. Usage : python3 build_slide.py -> Intertek_YouCraft_slide.html"""
import json, os, re

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.join(HERE, '..')
FONTS = open(os.path.join(ROOT, 'onepager', 'fonts.css'), encoding='utf-8').read()
LOGO = json.loads(re.search(r'window\.YC_LOGO = (\{.*\});', open(os.path.join(ROOT, 'video', 'digicraft-lancement', 'brand_yc', 'yc_logo_data.js'), encoding='utf-8').read(), re.S).group(1))
ICONS = json.loads(re.search(r'window\.ICONS=(\{.*\})', open(os.path.join(ROOT, 'video', 'digicraft-lancement', 'src6', 'icons.js'), encoding='utf-8').read(), re.S).group(1))


def logo(h):
    g = ''.join(f'<stop offset="{o}" stop-color="{c}"/>' for o, c in LOGO['grad'])
    bx, by, bw, bh = LOGO['sym']['block']
    glyphs = ''.join(f'<path d="{d}" fill="#F5F2EA" transform="translate({LOGO["tx"]} {LOGO["ty"]})"/>' for d in LOGO['glyphs'])
    return (f'<svg height="{h}" viewBox="0 0 {LOGO["w"]} {LOGO["h"]}" role="img" aria-label="YouCraft"><defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1">{g}</linearGradient></defs>'
            f'<path d="{LOGO["sym"]["dome"]}" fill="url(#lg)"/><path d="{LOGO["sym"]["quarter"]}" fill="#F5F2EA"/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" fill="#E4F53C"/>{glyphs}</svg>')


def ic(n, s=26, c='currentColor', sw=2):
    return f'<svg width="{s}" height="{s}" viewBox="0 0 24 24" fill="none" stroke="{c}" stroke-width="{sw}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{ICONS[n]}</svg>'


KPI = [('44 000+', 'collaborateurs'), ('1 000+', 'laboratoires et bureaux'), ('100+', 'pays'), ('80+', 'langues'), ('3 000', 'auditeurs'), ('100 000+', 'audits')]
ATIC = [('A', 'Assurance', 'shield-check'), ('T', 'Test', 'activity'), ('I', 'Inspection', 'search'), ('C', 'Certification', 'badge-check')]
DIV = [('Products', 'Biens de consommation, chimie, pharma, agroalimentaire'), ('Trade', 'Pétrochimie (Caleb Brett), services aux gouvernements'), ('Resources', 'Énergie et services industriels')]
SECT = [('Agroalimentaire', 'leaf'), ('Biens de consommation et distribution', 'layers'), ('Certification de système', 'badge-check'), ('Évaluation des fournisseurs (RSE)', 'users'), ('Électrique et électronique', 'zap'), ('Énergie et pétrochimie', 'gauge'), ('Gouvernements et commerce international', 'globe'), ('Industrie cosmétique, pharmaceutique, médicale et chimique', 'flask'), ('Industrie du luxe', 'sparkles'), ('Inspections pour sites industriels', 'search')]

kpis = ''.join(f'<div class="k"><b>{v}</b><span>{l}</span></div>' for v, l in KPI)
atic = ''.join(f'<div class="at"><i>{ic(i, 26, "#D7FF3F")}</i><b><em>{a}</em>{n[1:]}</b></div>' for a, n, i in ATIC)
div = ''.join(f'<div class="dv"><b>{n}</b><span>{d}</span></div>' for n, d in DIV)
sect = ''.join(f'<li><i>{ic(i if i in ICONS else "circle-check", 18, "#D7FF3F")}</i>{n}</li>' for n, i in SECT)

HTML = f'''<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Intertek en bref</title>
<style>{FONTS}
:root{{--ink:#050508;--text:#F5F2EA;--muted:#9C99A3;--line:rgba(245,242,234,.14);--um:#1E3BFF;--um2:#4F6BFF;--lime:#D7FF3F}}
*{{box-sizing:border-box;margin:0;padding:0}}
html,body{{height:100%;background:var(--ink);overflow:hidden}}
body{{display:flex;align-items:center;justify-content:center;font-family:'DM Sans',system-ui,sans-serif;color:var(--text);-webkit-font-smoothing:antialiased}}
#s{{position:relative;width:1920px;height:1080px;flex:none;overflow:hidden;transform-origin:center;
  background:radial-gradient(60% 70% at 85% 0%,rgba(30,59,255,.38),transparent 60%),radial-gradient(50% 60% at 0% 100%,rgba(79,107,255,.22),transparent 60%),var(--ink)}}
#s::before{{content:"";position:absolute;inset:0;background-image:radial-gradient(rgba(245,242,234,.07) 1.2px,transparent 1.4px);background-size:34px 34px;mask-image:radial-gradient(70% 70% at 50% 40%,#000,transparent 85%);-webkit-mask-image:radial-gradient(70% 70% at 50% 40%,#000,transparent 85%)}}
.top{{position:absolute;left:110px;right:110px;top:56px;display:flex;align-items:center;justify-content:space-between}}
.tag{{font:700 18px/1 'DM Sans';letter-spacing:.2em;color:var(--lime);padding:11px 18px;border:1px solid rgba(215,255,63,.4);border-radius:30px}}
.hd{{position:absolute;left:110px;right:110px;top:150px;display:flex;align-items:flex-end;justify-content:space-between;gap:60px}}
h1{{font:800 72px/1.04 'Plus Jakarta Sans';letter-spacing:-.035em}}
h1 span{{background:linear-gradient(100deg,#D7FF3F,#4F6BFF);-webkit-background-clip:text;background-clip:text;color:transparent}}
.q{{max-width:560px;padding-left:24px;border-left:4px solid var(--lime);font:500 24px/1.45 'DM Sans';color:#D6D3CC}}
.q b{{display:block;font:700 14px/1 'DM Sans';letter-spacing:.2em;color:var(--lime);margin-bottom:10px}}
.g{{position:absolute;left:110px;right:110px;top:350px;bottom:84px;display:grid;grid-template-columns:1.05fr 1fr;gap:28px}}
.c{{border-radius:28px;border:1px solid var(--line);background:linear-gradient(170deg,rgba(20,26,66,.9),rgba(8,8,14,.88));box-shadow:0 30px 80px rgba(0,0,0,.4);padding:26px 34px;position:relative;overflow:hidden}}
.ch{{display:flex;align-items:center;gap:12px;font:700 16px/1 'DM Sans';letter-spacing:.18em;color:var(--muted);text-transform:uppercase}}
.ch::before{{content:"";width:10px;height:10px;border-radius:50%;background:var(--lime);box-shadow:0 0 12px var(--lime)}}
.l{{display:grid;grid-template-rows:auto 1fr;gap:24px}}
.r{{display:grid;grid-template-rows:auto 1fr;gap:24px}}
.l .c,.r .c{{display:flex;flex-direction:column;justify-content:center}}
.kp{{display:grid;grid-template-columns:repeat(3,1fr);gap:26px 20px;margin-top:24px}}
.k b{{display:block;font:800 52px/1 'Plus Jakarta Sans';letter-spacing:-.035em;color:var(--text)}}
.k:nth-child(-n+3) b{{color:var(--lime)}}
.k span{{display:block;margin-top:8px;font-size:19px;color:var(--muted)}}
.ats{{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:22px}}
.at{{display:flex;flex-direction:column;gap:14px;padding:18px;border-radius:18px;background:rgba(30,59,255,.18);border:1px solid rgba(118,140,255,.4)}}
.at i{{width:48px;height:48px;border-radius:14px;background:rgba(215,255,63,.1);display:grid;place-items:center}}
.at b{{font:700 22px/1 'Plus Jakarta Sans'}}
.at em{{font-style:normal;color:var(--lime)}}
.ats+p{{margin-top:16px;font-size:18px;color:var(--muted)}}
.fr{{background:linear-gradient(150deg,#1E3BFF 0%,#3550FF 55%,#5E78FF 100%);border-color:#7A90FF;box-shadow:0 30px 90px rgba(30,59,255,.45)}}
.fr::after{{content:"";position:absolute;right:-120px;bottom:-140px;width:380px;height:380px;border-radius:50%;background:radial-gradient(circle,rgba(215,255,63,.28),transparent 65%)}}
.fr .ch{{color:rgba(245,242,234,.85)}}
.fk{{display:flex;gap:38px;margin-top:16px;position:relative;z-index:1}}
.fk b{{display:block;font:800 60px/1 'Plus Jakarta Sans';letter-spacing:-.04em;color:var(--lime)}}
.fk span{{display:block;margin-top:8px;font-size:20px;color:rgba(245,242,234,.92)}}
.fk div+div{{padding-left:38px;border-left:1px solid rgba(245,242,234,.3)}}
.hq{{margin-top:16px;align-self:flex-start;display:inline-flex;align-items:center;gap:10px;padding:10px 16px;border-radius:14px;background:rgba(11,11,15,.25);font-size:18px;position:relative;z-index:1}}
.sc{{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;position:relative;z-index:1}}
.sc span{{padding:9px 14px;border-radius:20px;background:rgba(11,11,15,.28);border:1px solid rgba(245,242,234,.28);font:500 17px/1 'DM Sans'}}

/* secteurs */
.se{{list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:10px 22px;margin-top:18px}}
.se li{{display:flex;align-items:center;gap:12px;font-size:18px;line-height:1.3;color:#E6E3DC}}
.se i{{width:34px;height:34px;flex:none;border-radius:10px;background:rgba(215,255,63,.1);border:1px solid rgba(215,255,63,.3);display:grid;place-items:center}}
.dvs{{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:16px}}
.dv{{padding:18px;border-radius:18px;border:1px solid var(--line);background:rgba(245,242,234,.04)}}
.dv b{{display:block;font:700 24px/1 'Plus Jakarta Sans'}}
.dv span{{display:block;margin-top:8px;font-size:16px;line-height:1.4;color:var(--muted)}}
.ft{{position:absolute;left:110px;right:110px;bottom:34px;display:flex;justify-content:space-between;font-size:16px;color:#6E6B75}}
@media (max-width:1919px){{#s{{transform:scale(var(--k,1))}}}}
</style></head>
<body><div id="s">
  <div class="top">{logo(40)}<span class="tag">PRÉSENTATION CLIENT</span></div>
  <div class="hd"><h1>Intertek, leader mondial<br>de <span>l’assurance qualité</span></h1>
    <p class="q"><b>RAISON D’ÊTRE</b>Donner vie à la qualité et à la sécurité, durablement, pour devenir le partenaire le plus fiable au monde en assurance qualité.</p></div>
  <div class="g">
    <div class="l">
      <div class="c"><div class="ch">Dans le monde</div><div class="kp">{kpis}</div></div>
      <div class="c"><div class="ch">Approche ATIC</div><div class="ats">{atic}</div><p>Une approche systémique de la qualité et de la sécurité, de la R&amp;D au service client.</p></div>
    </div>
    <div class="r">
      <div class="c fr"><div class="ch">En France</div>
        <div class="fk"><div><b>500+</b><span>collaborateurs</span></div><div><b>14</b><span>laboratoires et bureaux</span></div></div>
        <div class="hq">{ic("map-pin", 20, "#D7FF3F")}Siège : Ecoparc 2, Heudebouville (27)</div></div>
      <div class="c"><div class="ch">Nos secteurs d’activité en France</div><ul class="se">{sect}</ul></div>
    </div>
  </div>
  <div class="ft"><span>Source : présentation Intertek France</span><span>intertek-france.com</span></div>
</div>
<script>(function(){{var s=document.getElementById('s');function f(){{var k=Math.min(innerWidth/1920,innerHeight/1080);s.style.setProperty('--k',k);s.style.transform='scale('+k+')';}}addEventListener('resize',f);f();}})();</script>
</body></html>'''
open(os.path.join(HERE, 'Intertek_YouCraft_slide.html'), 'w', encoding='utf-8').write(HTML)
print('ok')

"""Présentation YouCraft v1.7, construite sur la v1.6 :
- vraies applications interactives (People Hub, Control Tower, Asset Monitor, Client Portal), même système visuel que le film ;
- diapositive 4 : builder YouCraft en grand (conversation, application en direct, vue mobile, publication) ;
- diapositive 5 : nouvelle mise en page en mosaïque, chaque tuile ouvre l'application correspondante ;
- diapositive 8 : espace « applications » avec les 4 applications ;
- simulateur de budget retiré ; mode lecture pour téléphones et tablettes en portrait.
Usage : python3 build_v15.py && python3 build_v16.py && python3 make_apps_assets.py && python3 build_v17.py"""
import json, os, re
from i18n_ix import LANGS

HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'youcraft-presentation_1_6.html'), encoding='utf-8').read()
AS = open(os.path.join(HERE, 'apps_assets.js'), encoding='utf-8').read()
ICONS = json.loads(re.search(r'window\.YA_ICONS=(\{.*?\});', AS).group(1))
PHOTOS = json.loads(re.search(r'window\.YA_PHOTOS=(\{.*?\});', AS).group(1))


def rep(s, a, b):
    assert s.count(a) == 1, (s.count(a), a[:90])
    return s.replace(a, b)


def ic(n, s=18, c='currentColor', sw=2):
    return f'<svg width="{s}" height="{s}" viewBox="0 0 24 24" fill="none" stroke="{c}" stroke-width="{sw}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{ICONS[n]}</svg>'


def av(k, d):
    return f'<img class="ya-av" src="{PHOTOS["p%02d" % k]}" style="width:{d}px;height:{d}px" alt="">'


def section(sid, inner):
    global src
    a = src.index(f'<section class="slide" id="{sid}">')
    b = src.index('</section>', a)
    head = '\n  <div class="brand logo-bg" role="img" aria-label="YouCraft"></div><div class="pg"></div>\n'
    src = src[:a] + f'<section class="slide" id="{sid}">' + head + inner + '\n' + src[b:]


# 1. Simulateur de budget retiré.
src, n = re.subn(r'<div class="pop" id="sim".*?(?=<div class="pop" id="ov")', '', src, flags=re.S); assert n == 1
src = rep(src, '<button class="sim-open">Estimate your budget<span aria-hidden="true"> →</span></button>\n    ', '')
src = src.replace('.plan{cursor:pointer;transition:transform .25s,box-shadow .25s,border-color .25s}\n.plan:hover{border-color:#D7FF3F}\n', '')

# 2. Diapositive 4 : le builder.
TPL = [('people', 'Onboarding hub', 'HR · new hires', '#1E3BFF', 'users'), ('tower', 'Control tower', 'Logistics · live map', '#0B0B0F', 'map-pin'),
       ('assets', 'Machine monitoring', 'Industry · sensors', '#1E3BFF', 'gauge'), ('portal', 'Client portal', 'Consulting · documents', '#1E3BFF', 'building-2')]
tpl = ''.join(f'<button data-k="{k}"><span class="ti" style="background:{c}">{ic(i, 22, "#fff")}</span><span>{t}<small>{d}</small></span></button>' for k, t, d, c, i in TPL)
sk = ''.join(f'<i style="left:{l}%;top:{t}%;width:{w}%;height:{h}%"></i>' for l, t, w, h in [(1, 2, 12, 96), (16, 4, 28, 6), (16, 15, 19, 12), (37, 15, 19, 12), (58, 15, 19, 12), (79, 15, 19, 12), (16, 31, 55, 66), (73, 31, 25, 66)])
S3 = f'''  <div class="content">
    <div class="b4-h"><h2 class="title rise">Your ideas. <span class="g">Real applications.</span></h2><p class="lead rise" style="--d:.15s">Describe what you need. YouCraft builds the complete application live, then you refine it in plain language.</p></div>
    <div class="bld rise" style="--d:.25s">
      <div class="bld-top"><span class="dots"><b></b><b></b><b></b></span><span class="prj"><span class="lg">{ic("sparkles", 16, "#D7FF3F", 2.2)}</span><span><b>New application</b><small>Describe your need to start</small></span></span>
        <div class="bld-dev"><button data-d="desk" class="on" aria-label="Desktop view">{ic("monitor", 18)}</button><button data-d="mob" aria-label="Mobile view">{ic("smartphone", 18)}</button></div>
        <button class="bld-share">{ic("external-link", 15, "#6B6B70")}Share</button><button class="bld-pub">{ic("rocket", 16, "#fff")}Publish</button></div>
      <div class="bld-b">
        <div class="bld-chat"><div class="bld-msgs"><div class="bm ai welcome"><div class="who"><i>{ic("sparkles", 13, "#D7FF3F", 2.2)}</i>YouCraft</div>Hi Claire. What would you like to build today? Describe it in your own words, or pick an example.</div></div>
          <div class="bld-sg"></div><div class="bld-cmp"><textarea rows="2" aria-label="Describe the app you need"></textarea><button class="snd" disabled aria-label="Send">{ic("arrow-up", 18, "#fff", 2.4)}</button></div></div>
        <div class="bld-pv"><div class="bld-empty"><h5>What would you like to <span>build</span> today?</h5><div class="bld-tpl">{tpl}</div></div>
          <div class="bld-frame" style="opacity:0;visibility:hidden"><div class="url">{ic("lock", 13, "#9A9AA0")}<span></span><span class="lv"><span class="ya-live"></span>Live</span></div><div class="bld-host" data-fit="w"></div><div class="bld-sk">{sk}</div></div>
          <div class="bld-ph"></div><div class="bld-toast"></div></div>
      </div>
      <div class="ix-hint">Interactive · type your own idea</div>
    </div>
  </div>'''
section('s3', S3)

# 3. Diapositive 5 : mosaïque.
tbl = ''.join(f'<div class="r">{av(k, 24)}<b>{n}</b><span style="color:#6B6B70">{d}</span><span class="ya-bar" style="width:70px"><i style="width:{p}%;background:{"#1C8C4E" if p == 100 else "#1E3BFF"}"></i></span><span class="ya-badge ya-{b[1]}"><i></i>{b[0]}</span></div>'
              for k, n, d, p, b in [(0, 'Sarah Martin', 'Consulting', 92, ('On track', 'ok')), (3, 'Tom Becker', 'Data', 66, ('Laptop in delivery', 'warn')), (9, 'Amara Okafor', 'Human resources', 100, ('Ready', 'ok')), (7, 'Marc Petit', 'Finance', 33, ('Contract not signed', 'bad'))])
flow = (f'<div class="nd" style="--dl:0s"><i>{ic("file-pen-line", 26, "#D7FF3F")}</i>New request</div><span class="cn"></span>'
        f'<div class="nd" style="--dl:1.5s"><i>{ic("user-check", 26, "#D7FF3F")}</i>Manager approval</div><span class="cn"></span>'
        f'<div class="nd" style="--dl:3s"><i>{ic("bell", 26, "#D7FF3F")}</i>Team notified</div>')
pts = [52, 54, 50, 57, 55, 60, 58, 64, 61, 67, 70, 68, 76, 79, 77, 84, 88]
path = ' L'.join(f'{i * 410 / (len(pts) - 1):.0f} {110 - (v - 45) * 2.4:.0f}' for i, v in enumerate(pts))
dash = (f'<div class="k"><span>Health score<b>62 / 100</b></span><span>Vibration<b style="color:#CF3B3B">7.84 mm/s</b></span><span>Efficiency<b>87.4 %</b></span></div>'
        f'<svg width="410" height="112" viewBox="0 0 410 112"><path d="M{path} L410 112 L0 112 Z" fill="#1E3BFF" opacity=".08"/><path d="M{path}" fill="none" stroke="#1E3BFF" stroke-width="3" stroke-linejoin="round" pathLength="1" style="stroke-dasharray:1;stroke-dashoffset:1;animation:dbdraw 2.4s ease-out .4s forwards"/></svg>')
cl = ''.join(f'<div class="r"><span class="ya-ini">{i}</span>{n}<span class="ya-badge ya-{k}"><i></i>{s}</span></div>' for i, n, s, k in [('NL', 'Northwind Logistics', 'In progress', 'info'), ('AR', 'Atlas Retail', 'Missing documents', 'warn')])
chat = ('<div class="ag"><span class="ti">' + ic('bot' if 'bot' in ICONS else 'sparkles', 22, '#0B0B0F') + '</span><span><b>Invoice checker</b><small>Runs every night · 1,284 invoices</small></span><span class="ya-badge ya-ok"><i></i>Active</span></div>'
        '<div class="q">Which supplier invoices look unusual this month?</div><div class="a"><b>3 invoices</b> are more than 40 % above the usual amount for these suppliers. I flagged them for review.</div>'
        '<div class="tg"><span>1,284 invoices analysed</span><span>3 flagged</span></div>')
go = '<span class="go">See it live →</span>'
S4 = f'''  <div class="content">
    <div class="ix-hint">Click a tile to see it live</div>
    <div class="b5-h"><h2 class="title rise">Solve real business challenges</h2><div><p class="lead rise" style="--d:.15s">From simple automations to complex multi-step workflows, YouCraft turns your knowledge and processes into working applications.</p><div class="pills rise" style="--d:.3s"><span>Applications</span><span>Agents</span><span>Workflows</span></div></div></div>
    <div class="bento">
      <div class="bt wide bt-tools rise" style="--d:.2s" data-go="people" tabindex="0" role="button"><div class="hd"><svg class="ic"><use href="#i-cube"/></svg><h3>Internal tools</h3></div><p>Replace fragile spreadsheets with secure apps your teams actually use.</p>{go}<div class="vz vz-tbl">{tbl}</div><i class="ln"></i></div>
      <div class="bt sm bt-flow rise" style="--d:.3s" data-go="expenses" tabindex="0" role="button"><div class="vz vz-flow">{flow}</div><div class="tx"><div class="hd"><svg class="ic"><use href="#i-flow"/></svg><h3>Process automation</h3></div><p>Approvals, reminders and notifications run on their own.</p></div>{go}<i class="ln"></i></div>
      <div class="bt bt-agents rise" style="--d:.4s" data-go="builder" tabindex="0" role="button"><div class="hd"><svg class="ic"><use href="#i-bot"/></svg><h3>AI agents</h3></div><p>Agents that read, check and act on your data, under your rules.</p>{go}<div class="vz vz-chat">{chat}</div><i class="ln"></i></div>
      <div class="bt wide bt-dash rise" style="--d:.5s" data-go="assets" tabindex="0" role="button"><div class="hd"><svg class="ic"><use href="#i-chart"/></svg><h3>Data dashboards</h3></div><p>Live indicators on your own data, refreshed in real time.</p>{go}<div class="vz vz-dash">{dash}</div><i class="ln"></i></div>
      <div class="bt sm bt-client rise" style="--d:.6s" data-go="portal" tabindex="0" role="button"><div class="vz vz-cl">{cl}</div><div class="tx"><div class="hd"><svg class="ic"><use href="#i-users"/></svg><h3>Client applications</h3></div><p>Portals where clients share documents and follow progress.</p></div>{go}<i class="ln"></i></div>
    </div>
  </div>'''
section('s4', S4)

# 4. Diapositive 8 : espace applications.
DOCK = [('people', 'People Hub', 'Onboarding: checklists, buddies, live activity', '#1E3BFF', 'users'), ('tower', 'Control Tower', 'Live truck map, delays and incidents', '#0B0B0F', 'map-pin'),
        ('assets', 'Asset Monitor', 'Live sensors, anomalies and work orders', '#1E3BFF', 'gauge'), ('portal', 'Client Portal', 'Documents, validation and follow-up', '#1E3BFF', 'building-2')]
dock = ''.join(f'<button data-k="{k}"><span class="ti" style="background:{c};border:1px solid rgba(245,242,234,.2)">{ic(i, 22, "#fff")}</span><span><b>{t}</b><span>{d}</span></span></button>' for k, t, d, c, i in DOCK)
S8 = f'''  <div class="content">
    <div class="ws-h"><h2 class="title rise">Explore real apps built with YouCraft</h2><div class="ix-hint" style="position:relative;right:auto;top:auto">Interactive · click, filter, type</div></div>
    <div class="ws rise" style="--d:.15s"><div class="ws-dock">{dock}<p class="ws-note">Sample data. Apps like these start from a prompt and are refined in plain language.</p></div>
      <div class="ws-fr"><div class="url"><span class="dots"><b></b><b></b><b></b></span>{ic("lock", 14, "#9A9AA0")}<span></span><span class="lv"><span class="ya-live"></span>Live</span></div><div class="ws-host" data-fit="w"></div></div></div>
  </div>'''
section('s6b', S8)

# 5. Navigation : crochets d'entrée et de sortie, mode lecture.
src = rep(src, "    if(s.id === 's1' && window.__ycCoverEnter){ window.__ycCoverEnter(); }\n",
          "    if(s.id === 's1' && window.__ycCoverEnter){ window.__ycCoverEnter(); }\n    if(s.id === 's3' && window.__ycBuilderEnter){ window.__ycBuilderEnter(); }\n    if(s.id === 's6b' && window.__ycWsEnter){ window.__ycWsEnter(); }\n")
src = rep(src, "    if(s.id === 's6b' && window.__ycDashLeave) window.__ycDashLeave();\n",
          "    if(s.id === 's6b' && window.__ycDashLeave) window.__ycDashLeave();\n    if(s.id === 's3' && window.__ycBuilderLeave) window.__ycBuilderLeave();\n    if(s.id === 's6b' && window.__ycWsLeave) window.__ycWsLeave();\n")
src = rep(src, "  function fit(){\n", "  function fit(){\n    if(window.__ycFlow){ stage.style.transform = 'none'; stage.style.zoom = String(document.documentElement.clientWidth / 760); return; }\n    stage.style.zoom = '';\n")
# Le clavier ne change pas de diapositive en mode lecture.
src = rep(src, "    var tg = e.target;\n", "    var tg = e.target;\n    if(window.__ycFlow) return;\n")

# Petites maquettes : traduites en français seulement, comme le contenu des applications.
src = rep(src, '        n.tagName !== "STYLE"\n      ) {', '        n.tagName !== "STYLE" &&\n        !(lang !== "fr" && n.classList && n.classList.contains("vz"))\n      ) {')
for c in ('vz vz-tbl', 'vz vz-flow', 'vz vz-chat', 'vz vz-dash', 'vz vz-cl'):
    pass
# 6. Styles, ressources, scripts et traductions.
css = ''.join(open(os.path.join(HERE, f), encoding='utf-8').read() for f in ('apps.css', 'apps2.css', 'resp.css'))
src = rep(src, '</style>\n<style>\n:root{', css + '</style>\n<style>\n:root{')
try:
    from i18n_apps import T as TA
    add = {l: {k: v[i] for k, v in TA.items() if v[i] is not None} for i, l in enumerate(LANGS)}
    src = rep(src, '<script>\n/* language layer (inlined) */', '<script>\n/* v1.7 : textes des applications */\n(function(){var A=' + json.dumps(add, ensure_ascii=False) + ';for(var l in A){window.__YC_DICTS__[l]=window.__YC_DICTS__[l]||{};for(var k in A[l])window.__YC_DICTS__[l][k]=A[l][k];}})();\n</script>\n<script>\n/* language layer (inlined) */')
except ImportError:
    pass
js = AS + '\n' + open(os.path.join(HERE, 'apps.js'), encoding='utf-8').read() + '\n' + open(os.path.join(HERE, 'apps2.js'), encoding='utf-8').read()
i = src.rindex('<script>if(document.getElementById("s1")')
src = src[:i] + '<script>\n' + js + '\n</script>\n' + src[i:]
src = src.replace('<title>YouCraft – AI App Builder for Business Teams</title>', '<title>YouCraft – AI App Builder for Business Teams</title>', 1)

out = os.path.join(HERE, 'youcraft-presentation_1_7.html')
open(out, 'w', encoding='utf-8').write(src)
print(out, round(len(src) / 1024), 'Ko')

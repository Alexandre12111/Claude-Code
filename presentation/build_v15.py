"""Présentation YouCraft v1.5 : construite à partir de la v1.4 fournie.
- logo en vectoriel (logo officiel de brand_yc/yc_logo_data.js) à la place du PNG ;
- cubes 3D retirés de la couverture ;
- écrans de la plateforme interactifs (builder, application de notes de frais) et nouvelle diapositive
  « Explore a live dashboard » (tableau de bord complet, filtrable, avec survols) ;
- textes ajoutés traduits dans les 5 langues du sélecteur (i18n_ix.py).
Usage : python3 build_v15.py  ->  youcraft-presentation_1_5.html"""
import base64, json, os, re
from i18n_ix import T, LANGS

HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'youcraft-presentation_1_4.html'), encoding='utf-8').read()
LOGO = json.loads(re.search(r'window\.YC_LOGO = (\{.*\});', open(os.path.join(HERE, '..', 'video', 'digicraft-lancement', 'brand_yc', 'yc_logo_data.js'), encoding='utf-8').read(), re.S).group(1))


def rep(s, a, b, n=1):
    assert s.count(a) >= 1, a[:80]
    return s.replace(a, b) if n == 0 else s.replace(a, b, n)


# 1. Logo vectoriel (texte clair pour le fond sombre), en fond CSS comme l'original.
g = ''.join(f'<stop offset="{o}" stop-color="{c}"/>' for o, c in LOGO['grad'])
bx, by, bw, bh = LOGO['sym']['block']
glyphs = ''.join(f'<path d="{d}" fill="#F5F2EA" transform="translate({LOGO["tx"]} {LOGO["ty"]})"/>' for d in LOGO['glyphs'])
svg = (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {LOGO["w"]} {LOGO["h"]}"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">{g}</linearGradient></defs>'
       f'<path d="{LOGO["sym"]["dome"]}" fill="url(#g)"/><path d="{LOGO["sym"]["quarter"]}" fill="#F5F2EA"/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" fill="#E4F53C"/>{glyphs}</svg>')
uri = 'data:image/svg+xml;base64,' + base64.b64encode(svg.encode()).decode()
src, n = re.subn(r'\.logo-bg\{background:url\(data:image/png;base64,[A-Za-z0-9+/=]+\)', '.logo-bg{background:url(' + uri + ')', src)
assert n == 1

# 2. Couverture sans cubes.
src, n = re.subn(r'\n  <div class="scene" aria-hidden="true">.*?\n  </div>\n</section>', '\n</section>', src, count=1, flags=re.S)
assert n == 1

# 3. Builder interactif (diapositive 3).
SW = lambda on: f'<span class="ix-sw{" on" if on else ""}" role="switch" aria-checked="{"true" if on else "false"}"></span>'
li = lambda ic, b, s, right: f'<div class="ix-li"><svg class="ic"><use href="#{ic}"/></svg><div class="m"><b>{b}</b><span{s[1]}>{s[0]}</span></div>{right}</div>'
st = lambda on: (('Active' if on else 'Paused'), ' data-on="Active" data-off="Paused"')
panes = f'''<div class="main">
          <div class="pane on">
          <h4>Create with AI</h4>
          <div class="prompt"><span class="t typing" data-type>Build a client onboarding tracker with reminders and a dashboard</span><span class="go" role="button" aria-label="Generate">→</span></div>
          <div class="tiles">
            <div class="tile"><svg class="ic"><use href="#i-cube"/></svg>Business app</div>
            <div class="tile"><svg class="ic"><use href="#i-bot"/></svg>AI agent</div>
            <div class="tile"><svg class="ic"><use href="#i-gear"/></svg>Automation</div>
            <div class="tile"><svg class="ic"><use href="#i-chart"/></svg>Dashboard</div>
          </div>
          <div class="ix-list">{li('i-cube', 'Client onboarding tracker', ('Edited 2 h ago · 14 users', ''), '<span class="ix-cn on">Live</span>')}{li('i-chart', 'CEO dashboard', ('Updated yesterday · 6 users', ''), '<span class="ix-cn on">Live</span>')}</div>
          </div>
          <div class="pane"><h4>AI agents</h4><div class="ix-list">
            {li('i-bot', 'Invoice checker', ('Checks supplier invoices and flags anomalies', ''), SW(True))}
            {li('i-bot', 'Lead qualifier', ('Scores inbound leads in your CRM', ''), SW(True))}
            {li('i-bot', 'HR assistant', ('Answers employee questions from the HR handbook', ''), SW(False))}</div></div>
          <div class="pane"><h4>Automations</h4><div class="ix-list">
            {li('i-flow', 'Weekly sales report', ('Every Monday at 8:00', ''), SW(True))}
            {li('i-flow', 'Invoice reminders', ('Every day at 9:00', ''), SW(True))}
            {li('i-flow', 'New hire onboarding', ('When a new employee is added', ''), SW(False))}</div></div>
          <div class="pane"><h4>Tables</h4><div class="ix-list">
            {li('i-db', 'Clients', ('1,284 rows · synced from Salesforce', ''), '')}
            {li('i-db', 'Invoices', ('9,412 rows · imported from Excel', ''), '')}
            {li('i-db', 'Employees', ('1,240 rows · built in YouCraft', ''), '')}</div></div>
          <div class="pane"><h4>Integrations</h4><div class="ix-list">
            {li('i-plug', 'Salesforce', ('CRM data, accounts and opportunities', ''), '<span class="ix-cn on">Connected</span>')}
            {li('i-plug', 'Microsoft 365', ('Outlook, Teams and SharePoint', ''), '<span class="ix-cn">Connect</span>')}
            {li('i-plug', 'Google Workspace', ('Gmail, Drive and Sheets', ''), '<span class="ix-cn">Connect</span>')}
            {li('i-grid', 'Excel and CSV', ('Import your existing files', ''), '<span class="ix-cn on">Connected</span>')}</div></div>
        </div>
      </div>
      <div class="ix-gen"><h5></h5>
        <div class="ix-step"><i></i>Screens and navigation</div><div class="ix-step"><i></i>Data model and database</div>
        <div class="ix-step"><i></i>Business logic and automations</div><div class="ix-step"><i></i>Login and access rights</div>
        <div class="ix-res"><b>Your app is ready</b><span>Live preview available. Share it with your team in one click.</span>
          <div class="row2"><button class="ix-btn ix-open">Open live dashboard</button><button class="ix-btn gh ix-back">Back to the builder</button></div></div>
      </div>'''
# Agents / automations : libellé d'état piloté par l'interrupteur.
panes = re.sub(r'<span>(Checks supplier invoices and flags anomalies|Scores inbound leads in your CRM|Answers employee questions from the HR handbook|Every Monday at 8:00|Every day at 9:00|When a new employee is added)</span>', r'<span>\1</span>', panes)
a = src.index('        <div class="main">\n          <h4>Create with AI</h4>')
b = src.index('    <div class="badge" style="right:-10px;top:-20px')
old = src[a:b]
src = src[:a] + '        ' + panes + '\n    </div>\n' + src[b:]
src = rep(src, '<div class="badge" style="right:-10px;top:-20px;animation-delay:-2s">Your ideas.<br>Real applications.</div>',
          '<div class="ix-hint" style="right:10px;top:-30px">Interactive · click to explore</div>')

# Texte d'introduction un peu plus étroit : en français il passait sous la maquette.
src = rep(src, '    <div style="width:760px">\n      <h2 class="title rise">Your ideas.', '    <div style="width:690px">\n      <h2 class="title rise">Your ideas.')

# 4. Application de notes de frais (diapositive « From a prompt to a live app »).
src = rep(src, '<div class="note">Illustrative example with sample data, from youcraft.build</div>',
          '<div class="note">Illustrative example with sample data, from youcraft.build</div>\n      <button class="ix-btn gh ix-replay">↻ Replay</button>\n      <div class="ix-hint" style="right:10px;top:-62px">Click a request to approve or reject it</div>')

# 5. Nouvelle diapositive : tableau de bord en direct.
DASH = '''
<!-- 6b LIVE DASHBOARD (interactive) -->
<section class="slide" id="s6b">
  <div class="brand logo-bg" role="img" aria-label="YouCraft"></div><div class="pg"></div>
  <div class="content">
    <h2 class="title rise" style="font-size:64px">Explore a live dashboard</h2>
    <div class="ix-hint" style="right:0;top:18px">Interactive · click, filter, hover</div>
    <div class="db ix rise" style="--d:.15s">
      <div class="url"><span class="dots"><b></b><b></b><b></b></span><svg class="ic"><use href="#i-lock"/></svg>ceo-dashboard.acme.youcraft.build<span class="live">Live</span><span style="margin-left:18px;color:#8F8C86">Sample data</span></div>
      <div class="db-b">
        <div class="db-nav">
          <div class="db-app"><i><svg class="ic"><use href="#i-chart"/></svg></i>CEO Dashboard</div>
          <button class="on" data-v="overview"><svg class="ic"><use href="#i-grid"/></svg>Overview</button>
          <button data-v="sales"><svg class="ic"><use href="#i-chart"/></svg>Sales</button>
          <button data-v="finance"><svg class="ic"><use href="#i-euro"/></svg>Finance</button>
          <button data-v="people"><svg class="ic"><use href="#i-users"/></svg>People</button>
          <div class="who"><i></i><span>Camille M. · Admin</span></div>
        </div>
        <div class="db-m"></div>
      </div>
    </div>
  </div>
</section>
'''
src = rep(src, '\n<!-- 7 USE CASES -->', DASH + '\n<!-- 7 USE CASES -->')

# 6. Styles et script interactifs, traductions.
css = open(os.path.join(HERE, 'ix.css'), encoding='utf-8').read() + '\n@keyframes dbdraw{to{stroke-dashoffset:0}}\n'
src = rep(src, '\n<style>\n:root{', '\n<style id="ix-css">\n' + css + '</style>\n<style>\n:root{')
add = {l: {k: v[i] for k, v in T.items()} for i, l in enumerate(LANGS)}
dict_js = '<script>\n/* v1.5 : textes des écrans interactifs */\n(function(){var A=' + json.dumps(add, ensure_ascii=False) + ';for(var l in A){window.__YC_DICTS__[l]=window.__YC_DICTS__[l]||{};for(var k in A[l])window.__YC_DICTS__[l][k]=A[l][k];}})();\n</script>\n'
src = rep(src, '<script>\n/* language layer (inlined) */', dict_js + '<script>\n/* language layer (inlined) */')
# Langue courante exposée aux écrans interactifs.
src = rep(src, '    lang = next;\n', '    lang = next;\n    window.__ycCurLang = lang;\n    setTimeout(function(){ document.dispatchEvent(new CustomEvent("yc-lang")); }, 0);\n')

# 7. Navigation : saisie et boutons ne font pas changer de diapositive ; accès au tableau de bord.
src = rep(src, "    var k = e.key;\n", "    var k = e.key;\n    var tg = e.target;\n    if(tg && (tg.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(tg.tagName))) return;\n    if((k===' '||k==='Enter') && tg && tg.tagName==='BUTTON' && !(tg.closest && tg.closest('#ui'))) return;\n")
src = rep(src, '  function go(i){\n', '  window.__ycGo = function(i){ go(i); };\n  window.__ycReplay = function(i){ leave(i); enter(i); };\n  function go(i){\n')
# La couverture garde sa classe « active » quand on ouvre la présentation sur une autre diapositive (lien #n) : on la retire.
src = rep(src, '    cur = i; enter(i);\n', '    cur = i; enter(i);\n    slides.forEach(function(s,k){ if(k!==i) s.classList.remove("active"); });\n')
src = rep(src, "    if(s.id === 's6'){\n", "    if(s.id === 's6b' && window.__ycDashEnter){ window.__ycDashEnter(); }\n    if(s.id === 's6'){\n      if(window.__ycResetExpenses) window.__ycResetExpenses();\n")
src = rep(src, '  var tx = null;\n  document.addEventListener(\'touchstart\', function(e){ tx = e.touches[0].clientX; }, {passive:true});',
          "  var tx = null;\n  document.addEventListener('touchstart', function(e){ tx = (e.target.closest && e.target.closest('.ix')) ? null : e.touches[0].clientX; }, {passive:true});")
js = open(os.path.join(HERE, 'ix.js'), encoding='utf-8').read()
i = src.rindex('</body>')
src = src[:i] + '<script>\n' + js + '\n</script>\n' + src[i:]

out = os.path.join(HERE, 'youcraft-presentation_1_5.html')
open(out, 'w', encoding='utf-8').write(src)
print(out, round(len(src) / 1024), 'Ko')

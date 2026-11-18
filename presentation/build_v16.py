"""Présentation YouCraft v1.6, construite sur la v1.5 (build_v15.py) :
- couverture : aperçu produit animé (la demande se tape, l'application se construit), cliquable vers le builder ;
- cas d'usage cliquables : un exemple d'application par équipe ;
- simulateur de budget sur la diapositive des tarifs ;
- vue d'ensemble des diapositives (touche O ou bouton) ;
- assistant « Interroger les données » dans le tableau de bord ;
- contours de focus visibles sur toutes les commandes interactives.
Usage : python3 build_v15.py && python3 build_v16.py  ->  youcraft-presentation_1_6.html"""
import json, os
from i18n_ix2 import T
from i18n_ix import LANGS

HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'youcraft-presentation_1_5.html'), encoding='utf-8').read()


def rep(s, a, b):
    assert s.count(a) == 1, (s.count(a), a[:90])
    return s.replace(a, b)


# Couverture : texte un peu plus étroit et aperçu produit à droite.
src = rep(src, '.cover-l{position:absolute;left:120px;top:190px;width:980px}', '.cover-l{position:absolute;left:120px;top:190px;width:860px}')
HERO = '''  <div class="hero-v rise" style="--d:.4s" tabindex="0" role="button" aria-label="Open the builder">
    <div class="glowc"></div>
    <div class="hv">
      <div class="pc"><span class="av"><svg class="ic"><use href="#i-spark"/></svg></span><span class="tx"></span></div>
      <div class="aw"><div class="u"><svg class="ic"><use href="#i-lock"/></svg><span>ceo-dashboard.acme.youcraft.build</span><span class="live">Live</span></div>
        <div class="bd"><h6></h6><div class="k3"><div><small></small><b></b></div><div><small></small><b></b></div><div><small></small><b></b></div></div>
          <div class="cb"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></div><div class="shim2"></div></div>
      <div class="chip2" style="right:-60px;top:290px;animation-delay:-1.5s">Live in one click</div>
      <div class="chip2" style="left:10px;top:640px;animation-delay:-3s">Sovereign options</div>
    </div>
    <div class="cta2">Click to explore the builder <b>→</b></div>
  </div>
'''
src = rep(src, '  <div class="chips rise" style="--d:.5s"><span>No code</span><span>Secure</span><span>Ready to deploy</span></div>\n',
          '  <div class="chips rise" style="--d:.5s"><span>No code</span><span>Secure</span><span>Ready to deploy</span></div>\n' + HERO)

# Tarifs : bouton du simulateur.
src = rep(src, '<div class="fine">All prices excl. VAT.', '<button class="sim-open">Estimate your budget<span aria-hidden="true"> →</span></button>\n    <div class="fine">All prices excl. VAT.')

# Tableau de bord : tiroir de l'assistant.
src = rep(src, '        <div class="db-m"></div>\n', '        <div class="db-m"></div>\n        <div class="db-dr" role="dialog" aria-label="Ask your data"><button class="cl" aria-label="Close">×</button><h5><svg class="ic"><use href="#i-spark"/></svg>Ask your data</h5><div class="sg"></div><div class="th"></div><div class="fine2">Answers computed from the dashboard data.</div></div>\n')

# Fenêtres superposées, dans la scène 1920x1080.
POPS = '''
<div class="pop" id="ucp" role="dialog" aria-modal="true"><div class="pw"><button class="x" aria-label="Close">×</button><h3></h3><p class="sub3"></p>
  <div class="ucp-g"><div class="ucp-l"><div class="q"></div><div class="feat"></div></div>
  <div class="ucp-a"><div class="u"><svg class="ic"><use href="#i-lock"/></svg><span></span><span class="live">Live</span></div><div class="bd"><h6></h6><div class="k3"></div><div class="rows"></div></div></div></div>
  <div class="ucp-nav"></div></div></div>
<div class="pop" id="sim" role="dialog" aria-modal="true"><div class="pw"><button class="x" aria-label="Close">×</button><h3>Estimate your budget</h3><p class="sub3">Based on the public prices, excl. VAT.</p>
  <div class="sim-g"><div class="sim-l"><label>Plan</label>
    <div class="sim-seg"><button>Starter<small>€25 / user / month</small></button><button>Business<small>€50 / user / month</small></button><button>Sovereign<small>€100 / user / month</small></button></div>
    <label>Number of users</label><div class="sim-n"><b>10</b><span>users</span></div>
    <input type="range" class="sim-r" min="1" max="60" step="1" value="10" aria-label="Number of users"><div class="sim-sc"><span>1</span><span>60</span></div></div>
  <div class="sim-o"><small>Per month</small><div class="big2"></div><div class="row3"><span>Per year</span><b class="v1"></b></div><div class="row3"><span>AI credits per month</span><b class="v2"></b></div></div></div>
  <div class="sim-tip"></div><p class="sim-fine">All prices excl. VAT. Quarterly commitment, annual for Enterprise.</p></div></div>
<div class="pop" id="ov" role="dialog" aria-modal="true"><div class="pw"><button class="x" aria-label="Close">×</button><h3>Slide overview</h3><div class="ovg"></div>
  <div class="keys"><span><kbd>←</kbd><kbd>→</kbd>Navigate</span><span><kbd>O</kbd>Slide overview</span><span><kbd>F</kbd>Full screen</span><span><kbd>Esc</kbd>Close</span></div></div></div>
'''
src = rep(src, '\n</div></div>\n\n<nav id="ui"', '\n' + POPS + '\n</div></div>\n\n<nav id="ui"')
src = rep(src, '  <button id="fs" aria-label="Toggle fullscreen">⛶</button>', '  <button id="ovbtn" aria-label="Slide overview">▦</button>\n  <button id="fs" aria-label="Toggle fullscreen">⛶</button>')

# Navigation : rien ne bouge derrière une fenêtre ouverte ; crochets d'entrée et de sortie.
src = rep(src, "    var tg = e.target;\n", "    var tg = e.target;\n    if(window.__ycPopOpen && window.__ycPopOpen()) return;\n")
src = rep(src, "    if(s.id === 's6b' && window.__ycDashEnter){ window.__ycDashEnter(); }\n",
          "    if(s.id === 's6b' && window.__ycDashEnter){ window.__ycDashEnter(); }\n    if(s.id === 's1' && window.__ycCoverEnter){ window.__ycCoverEnter(); }\n")
src = rep(src, "    s.classList.remove('active');\n    timers.forEach(clearTimeout); timers = [];\n",
          "    s.classList.remove('active');\n    timers.forEach(clearTimeout); timers = [];\n    if(s.id === 's1' && window.__ycCoverLeave) window.__ycCoverLeave();\n    if(s.id === 's6b' && window.__ycDashLeave) window.__ycDashLeave();\n    document.querySelectorAll('.pop.on').forEach(function(p){ p.classList.remove('on'); });\n")

# Styles, traductions, script.
css = open(os.path.join(HERE, 'ix2.css'), encoding='utf-8').read() + '\n.db-b{position:relative}\n'
src = rep(src, '</style>\n<style>\n:root{', css + '</style>\n<style>\n:root{')
add = {l: {k: v[i] for k, v in T.items()} for i, l in enumerate(LANGS)}
src = rep(src, '<script>\n/* language layer (inlined) */', '<script>\n/* v1.6 : textes ajoutés */\n(function(){var A=' + json.dumps(add, ensure_ascii=False) + ';for(var l in A){window.__YC_DICTS__[l]=window.__YC_DICTS__[l]||{};for(var k in A[l])window.__YC_DICTS__[l][k]=A[l][k];}})();\n</script>\n<script>\n/* language layer (inlined) */')
js = open(os.path.join(HERE, 'ix2.js'), encoding='utf-8').read()
i = src.rindex('</body>')
src = src[:i] + '<script>\n' + js + '\n</script>\n' + src[i:]
# L'aperçu de la couverture démarre aussi au premier affichage.
src = src[:src.rindex('</body>')] + '<script>if(document.getElementById("s1").classList.contains("active")&&window.__ycCoverEnter)window.__ycCoverEnter();</script>\n' + src[src.rindex('</body>'):]

out = os.path.join(HERE, 'youcraft-presentation_1_6.html')
open(out, 'w', encoding='utf-8').write(src)
print(out, round(len(src) / 1024), 'Ko')

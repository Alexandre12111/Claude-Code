"""v1.9 (fichier modifié sur place, déjà appliqué : ne pas relancer) :
- Chiffres clés : « 1300+ utilisateurs » sans « chez Leyton », retrait de la phrase et de la jauge sur les 3 000 collaborateurs ;
- Prochaines étapes : plus de mention d'essai (« Une semaine avec vos équipes, sur un cas d'usage réel. ») ;
- Design plus fin et premium sur toutes les diapositives : Plus Jakarta Sans 500 et 600 intégrées (titres plus fins),
  icônes du sprite remplacées par des icônes Lucide, cartes en verre homogènes, tuiles d'icônes en dégradé, grain léger."""
import base64, json, os, re

HERE = os.path.dirname(os.path.abspath(__file__))
F = os.path.join(HERE, '..', 'youcraft-presentation_1_9.html')
src = open(F, encoding='utf-8').read()
B64 = []
def stash(m):
    B64.append(m.group(0)); return f'@@B64_{len(B64) - 1}@@'
src = re.sub(r'data:[a-z/+.-]+;base64,[A-Za-z0-9+/=]+', stash, src)


def rep(a, b, n=1):
    global src
    c = src.count(a)
    assert c == n, (c, a[:90])
    src = src.replace(a, b)


# ---------------------------------------------------------------- contenus
rep('<span>Users at Leyton</span>', '<span>Users</span>')
rep('<div class="kf-ad"><div class="kf-bar"><i></i></div><p><b>1300+</b> of the group’s <b>3,000</b> employees already use YouCraft</p></div>', '')
rep('<p class="lead rise" style="--d:.1s">A 7-day trial with your teams, on a real use case.</p>', '<p class="lead rise" style="--d:.1s">One week with your teams, on a real use case.</p>')
TR = {'fr': {'Users': 'Utilisateurs', 'One week with your teams, on a real use case.': 'Une semaine avec vos équipes, sur un cas d’usage réel.'},
      'es': {'Users': 'Usuarios', 'One week with your teams, on a real use case.': 'Una semana con tus equipos, sobre un caso de uso real.'},
      'sv': {'Users': 'Användare', 'One week with your teams, on a real use case.': 'En vecka med era team, på ett verkligt användningsfall.'},
      'de': {'Users': 'Nutzer', 'One week with your teams, on a real use case.': 'Eine Woche mit Ihren Teams, an einem echten Anwendungsfall.'},
      'en': {'Users': 'Users', 'One week with your teams, on a real use case.': 'One week with your teams, on a real use case.'}}
i = src.index('/* v1.9 : tarifs détaillés, prochaines étapes */'); j = src.index('</script>', i) + len('</script>')
src = src[:j] + '\n<script>\n/* v1.9c : textes */\n(function(){var A=' + json.dumps(TR, ensure_ascii=False) + \
    ';for(var l in A){window.__YC_DICTS__[l]=window.__YC_DICTS__[l]||{};for(var k in A[l])window.__YC_DICTS__[l][k]=A[l][k];}})();\n</script>' + src[j:]

# ---------------------------------------------------------------- icônes Lucide (licence ISC) à la place du sprite d'origine
L = {
    'clock': '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'grid': '<path d="M12 3v18"/><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/>',
    'chat': '<path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/><path d="M7 11h10"/><path d="M7 15h6"/><path d="M7 7h8"/>',
    'code': '<path d="m18 16 4-4-4-4"/><path d="m6 8-4 4 4 4"/><path d="m14.5 4-5 16"/>',
    'bot': '<path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/>',
    'flow': '<rect width="8" height="8" x="3" y="3" rx="2"/><path d="M7 11v4a2 2 0 0 0 2 2h4"/><rect width="8" height="8" x="13" y="13" rx="2"/>',
    'bolt': '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
    'users': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/>',
    'gear': '<path d="M20 7h-9"/><path d="M14 17H5"/><circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>',
    'chart': '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
    'lock': '<circle cx="12" cy="16" r="1"/><rect x="3" y="10" width="18" height="12" rx="2"/><path d="M7 10V7a5 5 0 0 1 10 0v3"/>',
    'shield': '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
    'db': '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/>',
    'plane': '<path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/>',
    'spark': '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/>',
    'sliders': '<path d="M10 5H3"/><path d="M12 19H3"/><path d="M14 3v4"/><path d="M16 17v4"/><path d="M21 12h-9"/><path d="M21 19h-5"/><path d="M21 5h-7"/><path d="M8 10v4"/><path d="M8 12H3"/>',
    'bulb': '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
    'cube': '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
    'scale': '<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>',
    'flask': '<path d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/><path d="M8.5 2h7"/><path d="M7 16h10"/>',
    'euro': '<path d="M4 10h12"/><path d="M4 14h9"/><path d="M19 6a7.7 7.7 0 0 0-5.2-2A7.9 7.9 0 0 0 6 12c0 4.4 3.5 8 7.8 8 2 0 3.8-.8 5.2-2"/>',
    'mega': '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
    'globe': '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
    'plug': '<path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"/>',
}
for k, v in L.items():
    src, n = re.subn(r'<symbol id="i-%s" viewBox="0 0 24 24">.*?</symbol>' % k, f'<symbol id="i-{k}" viewBox="0 0 24 24">{v}</symbol>', src, flags=re.S)
    assert n == 1, k

# ---------------------------------------------------------------- polices fines intégrées
ff = ''.join("@font-face{font-family:'Plus Jakarta Sans';font-style:normal;font-weight:%d;font-display:swap;src:url(data:font/woff2;base64,%s) format('woff2')}"
             % (w, base64.b64encode(open(os.path.join(HERE, f'plus-jakarta-sans-latin-{w}-normal.woff2'), 'rb').read()).decode()) for w in (500, 600))

GLASS = 'background:radial-gradient(120% 90% at 100% 0%,rgba(79,107,255,.20),rgba(79,107,255,0) 55%),linear-gradient(165deg,rgba(24,30,78,.9),rgba(9,9,16,.95) 72%);border:1px solid rgba(245,242,234,.11);box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 30px 80px rgba(3,3,8,.5)'
HERO = 'background:radial-gradient(90% 80% at 100% 0%,rgba(215,255,63,.16),rgba(215,255,63,0) 55%),linear-gradient(150deg,#4A66FF 0%,#2340FF 42%,#1530D8 100%);border:1px solid rgba(160,178,255,.45);box-shadow:inset 0 1px 0 rgba(255,255,255,.35),0 30px 80px rgba(30,59,255,.42)'
TILE = 'background:linear-gradient(150deg,#7D92FF 0%,#2E4BFF 48%,#1530D8 100%);border:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.5),inset 0 -3px 8px rgba(0,0,20,.28),0 0 0 1px rgba(255,255,255,.12),0 14px 30px rgba(30,59,255,.45);color:#fff'
CSS = f'''
<style id="v19c-css">
/* v1.9c : finesse et finitions premium */
{ff}
.slide .title{{font-weight:600;letter-spacing:-.035em}}
.slide h3,.bn-c b,.ns-c h3,.pc-h h3,.step h3{{font-weight:600}}
#bg{{opacity:.7}}
body::after{{content:"";position:fixed;inset:0;pointer-events:none;z-index:9;opacity:.05;mix-blend-mode:overlay;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}}
/* cartes en verre */
#s2 .problem,#s5 .step .box,#s10 .bn-c,#s11 .kf-cx,#s14 .ns-c:not(:nth-child(3)),#s12 .pc,#s4 .bt{{{GLASS}}}
#s2 .problem::after{{height:1.5px;left:30px;right:30px;opacity:.6;background:linear-gradient(90deg,rgba(215,255,63,0),rgba(215,255,63,.8),rgba(111,134,255,0))}}
#s11 .kf-yc,#s10 .bn-hero,#s14 .ns-c:nth-child(3),#s2 .pb-sol{{{HERO}}}
#s2 .pb-sol{{border-radius:18px}}
/* tuiles d'icônes */
#s2 .pb-ic,#s10 .bn-ti,#s10 .bn-c span.bn-ti{{{TILE}}}
#s2 .pb-ic{{width:64px;height:64px;border-radius:19px}}
#s2 .pb-ic .ic,#s10 .bn-c span.bn-ti .ic{{width:30px;height:30px;color:#fff;stroke-width:1.85}}
#s10 .bn-c span.bn-ti{{width:64px;height:64px;border-radius:19px}}
#s5 .step .box .ic{{width:64px;height:64px;padding:16px;box-sizing:border-box;border-radius:19px;{TILE};stroke-width:1.85}}
#s4 .bt .hd .ic{{width:46px;height:46px;padding:11px;box-sizing:border-box;border-radius:14px;{TILE};stroke-width:1.9}}
#s9 .cmp .li .ic{{width:44px;height:44px;padding:10px;box-sizing:border-box;border-radius:13px;{TILE};stroke-width:1.9}}
#s5 .num{{font-weight:600;background:linear-gradient(150deg,#6F86FF,#1E3BFF 55%,#1530D8);box-shadow:0 0 0 10px #0F1A63,0 0 0 11px rgba(118,140,255,.45),inset 0 1px 0 rgba(255,255,255,.4),0 0 46px rgba(30,59,255,.6)}}
/* tarifs : bandeaux plus fins */
#s12 .pc{{border-radius:24px}}
#s12 .pc-b>p{{font-size:19px}}
#s12 .pc-b li{{font-size:18px;padding:8px 0 8px 32px}}
#s12 .pc-b li::before{{top:10px}}
#s12 .pc-b li small{{font-size:14.5px}}
#s12 .pc-st .pc-h{{background:linear-gradient(135deg,#2E4BFF,#1530D8)}}
#s12 .pc-bu .pc-h{{background:linear-gradient(135deg,#6F86FF,#1E3BFF 60%)}}
#s12 .pc-sv .pc-h{{background:linear-gradient(135deg,#EFFF9A,#D7FF3F 55%,#C2EE10)}}
#s12 .pc-en .pc-h{{background:linear-gradient(135deg,rgba(255,255,255,.06),rgba(255,255,255,0))}}
#s12 .pc-bu{{border-color:rgba(160,178,255,.55);box-shadow:inset 0 1px 0 rgba(255,255,255,.1),0 0 0 1px rgba(111,134,255,.25),0 30px 80px rgba(30,59,255,.35)}}
#s12 .pc-v b{{font-weight:600}}
/* chiffres clés : carte YouCraft sans la phrase retirée */
#s11 .kf-yc{{display:flex;flex-direction:column;justify-content:center}}
</style>
'''
rep('</head>', CSS + '</head>')

src = re.sub(r'@@B64_(\d+)@@', lambda m: B64[int(m.group(1))], src)
open(F, 'w', encoding='utf-8').write(src)
print('ok', round(len(src) / 1024), 'Ko')

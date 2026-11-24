"""(Déjà appliqué à youcraft-presentation_1_9.html : ne pas relancer sur ce fichier.)
Design des diapositives 8 (cas d'usage, #s7) et 9 (sécurité et souveraineté, #s8) de la v1.9, fichier modifié sur place :
icônes Lucide (symboles j-*), tuiles d'icônes en dégradé, cartes en verre avec filigrane, emblème du bouclier
avec satellites en orbite, niveaux d'hébergement illustrés. Les textes et l'interactivité (exemples d'application) ne changent pas."""
import os, re

F = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'youcraft-presentation_1_9.html')
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


# Icônes Lucide (licence ISC)
J = {
    'workflow': '<rect width="8" height="8" x="3" y="3" rx="2"/><path d="M7 11v4a2 2 0 0 0 2 2h4"/><rect width="8" height="8" x="13" y="13" rx="2"/>',
    'megaphone': '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
    'pie': '<path d="M21 12c.552 0 1.005-.449.95-.998a10 10 0 0 0-8.953-8.951c-.55-.055-.998.398-.998.95v8a1 1 0 0 0 1 1z"/><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>',
    'users': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><path d="M16 3.128a4 4 0 0 1 0 7.744"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/>',
    'scale': '<path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/><path d="M7 21h10"/><path d="M12 3v18"/><path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>',
    'flask': '<path d="M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2"/><path d="M8.5 2h7"/><path d="M7 16h10"/>',
    'lock': '<circle cx="12" cy="16" r="1"/><rect x="3" y="10" width="18" height="12" rx="2"/><path d="M7 10V7a5 5 0 0 1 10 0v3"/>',
    'shield': '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
    'plug': '<path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"/>',
    'landmark': '<path d="M10 18v-7"/><path d="M11.119 2.205a2 2 0 0 1 1.762 0l7.84 3.846A.5.5 0 0 1 20.5 7h-17a.5.5 0 0 1-.22-.949z"/><path d="M14 18v-7"/><path d="M18 18v-7"/><path d="M3 22h18"/><path d="M6 18v-7"/>',
    'cloud': '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/>',
    'pin': '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
    'server': '<rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><path d="M6 6h.01"/><path d="M6 18h.01"/>',
    'key': '<path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5"/>',
    'eye': '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/>',
    'database': '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/>',
}
rep('<symbol id="i-clock"', ''.join(f'<symbol id="j-{k}" viewBox="0 0 24 24">{v}</symbol>' for k, v in J.items()) + '\n<symbol id="i-clock"')
U = lambda k, cls='ic': f'<svg class="{cls}"><use href="#j-{k}"/></svg>'

# ---------- Diapositive 8 : cas d'usage
for old, new in [('gear', 'workflow'), ('mega', 'megaphone'), ('euro', 'pie'), ('users', 'users'), ('scale', 'scale'), ('flask', 'flask')]:
    rep(f'<div class="uc-top"><span class="uc-ic"><svg class="ic"><use href="#i-{old}"/></svg></span>',
        f'{U(new, "ic uc-wm")}<div class="uc-top"><span class="uc-ic">{U(new)}</span>')

# ---------- Diapositive 9 : sécurité et souveraineté
a = src.index('      <div class="shield-wrap">'); b = src.index('      <div class="assure">')
SAT = [('lock', 0, 50), ('key', 50, 100), ('server', 100, 50), ('eye', 50, 0)]
sats = ''.join(f'<span class="sw-sat" style="left:{x}%;top:{y}%"><i>{U(k)}</i></span>' for k, y, x in SAT)
src = src[:a] + f'''      <div class="shield-wrap sw2">
        <div class="sw-halo"></div><div class="ring"></div><div class="ring two"></div><div class="sw-orbit">{sats}</div>
        <svg class="shield" viewBox="0 0 330 390" aria-hidden="true">
          <defs><linearGradient id="sgA" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6F86FF"/><stop offset=".55" stop-color="#1E3BFF"/><stop offset="1" stop-color="#0E1C8C"/></linearGradient>
            <linearGradient id="sgB" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2A44FF" stop-opacity=".55"/><stop offset="1" stop-color="#05061A" stop-opacity=".95"/></linearGradient>
            <linearGradient id="sgC" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".35"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient>
            <filter id="sgG" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="8"/></filter></defs>
          <path d="M165 10 L310 62 V190 C310 280 250 345 165 380 C80 345 20 280 20 190 V62 Z" fill="url(#sgB)" stroke="url(#sgA)" stroke-width="7"/>
          <path d="M165 10 L310 62 V190 C310 280 250 345 165 380 C80 345 20 280 20 190 V62 Z" fill="url(#sgC)"/>
          <path d="M165 42 L280 84 V190 C280 260 234 313 165 344 C96 313 50 260 50 190 V84 Z" fill="none" stroke="#D7FF3F" stroke-opacity=".28" stroke-width="2" stroke-dasharray="2 9" stroke-linecap="round"/>
          <path d="M112 196 L153 237 L228 152" fill="none" stroke="#D7FF3F" stroke-width="26" stroke-linecap="round" stroke-linejoin="round" opacity=".55" filter="url(#sgG)"/>
          <path d="M112 196 L153 237 L228 152" fill="none" stroke="#D7FF3F" stroke-width="22" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
''' + src[b:]
for old, new in [('lock', 'lock'), ('shield', 'shield'), ('plug', 'database'), ('globe', 'landmark')]:
    rep(f'<span class="ar-ic"><svg class="ic"><use href="#i-{old}"/></svg></span>', f'<span class="ar-ic">{U(new)}</span>')
for small, k in [('Starter', 'cloud'), ('Business', 'pin'), ('Sovereign · Enterprise', 'landmark')]:
    rep(f'<small>{small}</small><b>', f'<span class="t-ic">{U(k)}</span><small>{small}</small><b>')

CSS = r'''
<style id="v19b-css">
/* v1.9b : diapositives 8 et 9 */
#s7 .uc,#s8 .arow,#s8 .tier{background:radial-gradient(120% 90% at 100% 0%,rgba(79,107,255,.24),rgba(79,107,255,0) 55%),linear-gradient(165deg,rgba(24,30,78,.92),rgba(9,9,16,.95) 72%);border:1px solid rgba(245,242,234,.12);box-shadow:inset 0 1px 0 rgba(255,255,255,.09),0 30px 80px rgba(3,3,8,.55)}
#s7 .uc::before,#s8 .arow::before{content:"";position:absolute;left:30px;right:30px;top:0;height:1.5px;background:linear-gradient(90deg,rgba(215,255,63,0),rgba(215,255,63,.75),rgba(111,134,255,0));opacity:.6}
#s8 .arow{position:relative;overflow:hidden}
#s7 .uc .ic.uc-wm{position:absolute;right:-34px;top:-34px;width:190px;height:190px;color:#fff;opacity:.045;stroke-width:1.3;pointer-events:none}
#s7 .uc-ic,#s8 .arow span.ar-ic,#s8 .t-ic{background:linear-gradient(150deg,#7D92FF 0%,#2E4BFF 48%,#1530D8 100%);border:0;box-shadow:inset 0 1px 0 rgba(255,255,255,.5),inset 0 -3px 8px rgba(0,0,20,.28),0 0 0 1px rgba(255,255,255,.12),0 14px 30px rgba(30,59,255,.5)}
#s7 .uc-ic{width:64px;height:64px;border-radius:19px}
#s7 .uc-ic .ic,#s8 .arow span.ar-ic .ic{width:30px;height:30px;color:#fff;stroke-width:1.85}
#s7 .uc h3{font-weight:700;letter-spacing:-.02em}
#s7 .uc li{color:#E2DFD7}
#s7 .uc li::before{width:24px;height:24px;margin-top:-12px;border-radius:8px;border:1px solid rgba(215,255,63,.38);background:rgba(215,255,63,.12) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23D7FF3F' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E") center/14px no-repeat}
#s7 .uc li::after{display:none}
#s7 .uc:hover .ic.uc-wm{opacity:.08}
/* Diapositive 9 : cartes */
#s8 .assure{gap:20px}
#s8 .arow{padding:36px 30px;align-items:center;gap:22px}
#s8 .tiers{bottom:26px}
html.ycflow #s8 .tiers{bottom:auto}
#s8 .arow span.ar-ic{width:60px;height:60px;border-radius:18px}
#s8 .arow b{font-size:27px;font-weight:700;letter-spacing:-.015em;margin-bottom:6px}
#s8 .arow span:not(.ar-ic){font-size:21px}
/* Diapositive 9 : niveaux d'hébergement */
#s8 .tier{padding:20px 24px 22px}
#s8 .tier .t-ic{float:right;width:44px;height:44px;border-radius:14px;display:grid;place-items:center;margin:-2px -4px 0 12px}
#s8 .tier .t-ic .ic{width:22px;height:22px;color:#fff;stroke-width:1.9}
#s8 .tier.hi{background:radial-gradient(120% 120% at 100% 0%,rgba(215,255,63,.16),rgba(215,255,63,0) 50%),linear-gradient(150deg,rgba(30,59,255,.65),rgba(79,107,255,.2));border-color:rgba(215,255,63,.55);box-shadow:0 0 0 1px rgba(215,255,63,.15),0 0 60px rgba(30,59,255,.45)}
#s8 .tier.hi .t-ic{background:linear-gradient(150deg,#EFFF9A,#D7FF3F 55%,#B5E000);box-shadow:inset 0 1px 0 rgba(255,255,255,.7),0 10px 26px rgba(215,255,63,.35)}
#s8 .tier.hi .t-ic .ic{color:#0B0B0F}
/* Diapositive 9 : emblème */
.sw2 .shield{width:300px;height:355px;filter:drop-shadow(0 0 40px rgba(79,107,255,.75)) drop-shadow(0 20px 40px rgba(0,0,0,.6))}
.sw2 .sw-halo{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(closest-side,rgba(30,59,255,.45),rgba(30,59,255,.12) 60%,rgba(30,59,255,0))}
.sw2 .ring{width:500px;height:500px;border:1.5px solid rgba(111,134,255,.28)}
.sw2 .ring.two{width:400px;height:400px;border:1.5px dashed rgba(215,255,63,.28)}
.sw2 .sw-orbit{position:absolute;width:500px;height:500px;animation:rot 60s linear infinite}
.sw2 .sw-sat{position:absolute;width:62px;height:62px;margin:-31px 0 0 -31px}
.sw2 .sw-sat i{display:grid;place-items:center;width:100%;height:100%;border-radius:18px;background:linear-gradient(160deg,rgba(40,50,110,.95),rgba(12,12,24,.95));border:1px solid rgba(245,242,234,.18);box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 12px 30px rgba(0,0,0,.5);animation:rot 60s linear infinite reverse}
.sw2 .sw-sat .ic{width:26px;height:26px;color:#D7FF3F;stroke-width:1.9}
html.ycflow .sw2 .sw-sat{display:none}
</style>
'''
rep('</head>', CSS + '</head>')

src = re.sub(r'@@B64_(\d+)@@', lambda m: B64[int(m.group(1))], src)
open(F, 'w', encoding='utf-8').write(src)
print('ok')

"""YouCraft · présentation commerciale v2 (refonte complète, français).
Assemble deck.css, slides.html et deck.js, et embarque le moteur des applications interactives YouCraft
(icônes, photos, écrans, navigation) repris de la v1.9, avec ses textes français.
Usage : python3 build_v2.py  ->  ../youcraft-presentation_2_0.html"""
import base64, json, os, re

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.abspath(os.path.join(HERE, '..', '..'))
OLD = open(os.path.join(HERE, '..', 'youcraft-presentation_1_9.html'), encoding='utf-8').read()
rd = lambda f: open(os.path.join(HERE, f), encoding='utf-8').read()

# ---------------------------------------------------------------- moteur des applications (v1.9)
scripts = re.findall(r'<script>.*?</script>', OLD, re.S)
ENGINE = next(s for s in scripts if s.startswith('<script>\nwindow.YA_ICONS='))
NAVJS = next(s for s in scripts if '/* ===== v1.8 : navigation complète' in s[:200])
ICONS = json.loads(re.search(r'window\.YA_ICONS=(\{.*?\});\n', ENGINE, re.S).group(1))


def css_blocks(c):
    out, i, n = [], 0, len(c)
    while i < n:
        j = c.find('{', i)
        if j < 0: break
        d, k = 1, j + 1
        while d and k < n:
            d += {'{': 1, '}': -1}.get(c[k], 0); k += 1
        out.append((re.sub(r'/\*.*?\*/', '', c[i:j], flags=re.S).strip(), c[j + 1:k - 1])); i = k
    return out


oldcss = '\n'.join(re.findall(r'<style[^>]*>(.*?)</style>', re.sub(r'data:[a-z/+.-]+;base64,[A-Za-z0-9+/=]+', 'B64', OLD), re.S))
isya = lambda sel: re.search(r'\.(ya|yx|ym|yi)(\b|-)', sel) and not re.search(r'html\.ycflow|#s\d|\.ucx|\.ws-|\.hv |\.cvw|\.ex-', sel)
YACSS, anims = [], set()
for sel, body in css_blocks(oldcss):
    if sel.startswith('@media'):
        inner = [f'{a}{{{b}}}' for a, b in css_blocks(body) if isya(a)]
        if inner: YACSS.append(f'{sel}{{{"".join(inner)}}}')
    elif isya(sel):
        YACSS.append(f'{sel}{{{body}}}'); anims |= set(re.findall(r'animation(?:-name)?\s*:\s*([\w-]+)', body))
for sel, body in css_blocks(oldcss):
    m = re.match(r'@keyframes\s+([\w-]+)', sel)
    if m and m.group(1) in anims: YACSS.append(f'{sel}{{{body}}}')
assert 'B64' not in ''.join(YACSS)

# Palette propre à chaque application : les bleus du moteur deviennent des variables CSS (--yb0, --yb1…),
# et deck.js recolore aussi les couleurs écrites directement dans les écrans.
BLUES = ['#1E3BFF', '#1530E0', '#4F6BFF', '#6F86FF', '#8C9BFF', '#8FA2FF', '#9BA8F5', '#B9BDD6', '#C7CEFA', '#C9CEF7', '#E1E5FF', '#EEF0FF', '#F3F5FF', '#F6F7FF']
def _t(h):
    r = int(h[1:3], 16)
    return round((r - 30) / 225, 3) if r >= 30 else round(-(30 - r) / 30 * 0.25, 3)
YB = [[h, _t(h)] for h in BLUES]
_css = ''.join(YACSS)
for i, (h, t) in enumerate(YB):
    _css = re.sub(re.escape(h), f'var(--yb{i},{h})', _css, flags=re.I)
_css = re.sub(r'rgba\(\s*30\s*,\s*59\s*,\s*255\s*,', 'rgba(var(--yrgb,30,59,255),', _css)
YACSS = [_css]
SHIM_YB = '<script>window.__YB = ' + json.dumps(YB) + ';</script>'


FR = json.load(open(os.path.join(HERE, 'apps_fr.json'), encoding='utf-8'))
SHIM = '''<script>
/* Textes français des applications et formats de nombres */
window.__ycCurLang = 'fr'; window.__ycFlow = false;
window.__YC_DICTS__ = { fr: ''' + json.dumps(FR, ensure_ascii=False) + ''' };
(function () {
  var D = window.__YC_DICTS__.fr;
  function tr(s) { return Object.prototype.hasOwnProperty.call(D, s) ? D[s] : s; }
  function nf(v, dec) { return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: dec || 0, maximumFractionDigits: dec || 0 }).format(v); }
  function eur(v, dec, unit) { var n = nf(v, dec); var u = unit ? tr(unit) : ''; return n + ' ' + (u ? u + (u.length > 1 ? ' ' : '') : '') + '€'; }
  window.__ycTr = tr; window.__ycNf = nf; window.__ycEur = eur; window.__ycDecimal = function () { return true; };
})();
</script>'''

# ---------------------------------------------------------------- icônes, logo, polices
EXTRA = {
    'workflow': '<rect width="8" height="8" x="3" y="3" rx="2"/><path d="M7 11v4a2 2 0 0 0 2 2h4"/><rect width="8" height="8" x="13" y="13" rx="2"/>',
    'bot': '<path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/>',
    'database': '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/>',
    'key': '<path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5"/>',
    'fingerprint': '<path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/><path d="M14 13.12c0 2.38 0 6.38-1 8.88"/><path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/><path d="M2 12a10 10 0 0 1 18-6"/><path d="M2 16h.01"/><path d="M21.8 16c.2-2 .131-5.354 0-6"/><path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/><path d="M8.65 22c.21-.66.45-1.32.57-2"/><path d="M9 6.8a6 6 0 0 1 9 5.2v2"/>',
    'arrow-left2': '<path d="m15 18-6-6 6-6"/>', 'arrow-right2': '<path d="m9 18 6-6-6-6"/>',
    'list': '<path d="M3 12h.01"/><path d="M3 18h.01"/><path d="M3 6h.01"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M8 6h13"/>',
    'maximize': '<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>',
}
ICONS.update(EXTRA)
ic = lambda n: f'<svg class="ic" viewBox="0 0 24 24" aria-hidden="true">{ICONS[n]}</svg>'

LS = open(os.path.join(ROOT, 'video', 'digicraft-lancement', 'brand_yc', 'yc_logo_data.js'), encoding='utf-8').read()
L = json.loads(LS[LS.index('{'):LS.rindex('}') + 1])
_n = [0]


def logo(h, light=True):
    _n[0] += 1; gid = f'lg{_n[0]}'
    col = '#F5F2EA' if light else '#0B0B0F'
    g = ''.join(f'<stop offset="{o}" stop-color="{c}"/>' for o, c in L['grad'])
    bx, by, bw, bh = L['sym']['block']
    w = h * L['w'] / L['h']
    gl = ''.join(f'<path d="{d}" fill="{col}" transform="translate({L["tx"]} {L["ty"]})"/>' for d in L['glyphs'])
    return (f'<svg width="{w:.1f}" height="{h}" viewBox="0 0 {L["w"]} {L["h"]}" role="img" aria-label="YouCraft"><defs><linearGradient id="{gid}" x1="0" y1="0" x2="1" y2="1">{g}</linearGradient></defs>'
            f'<path d="{L["sym"]["dome"]}" fill="url(#{gid})"/><path d="{L["sym"]["quarter"]}" fill="{col}"/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" fill="#E4F53C"/>{gl}</svg>')


def mark(w):
    bx, by, bw, bh = L['sym']['block']
    return (f'<svg width="{w}" viewBox="-4 -4 256 255" aria-hidden="true"><path d="{L["sym"]["dome"]}" fill="#F5F2EA" opacity=".95"/>'
            f'<path d="{L["sym"]["quarter"]}" fill="#F5F2EA"/><rect x="{bx}" y="{by}" width="{bw}" height="{bh}" fill="#D7FF3F"/></svg>')


fonts = open(os.path.join(ROOT, 'onepager', 'fonts.css'), encoding='utf-8').read()
fonts += ''.join("@font-face{font-family:'Plus Jakarta Sans';font-style:normal;font-weight:%d;font-display:swap;src:url(data:font/woff2;base64,%s) format('woff2')}"
                 % (w, base64.b64encode(open(os.path.join(HERE, f'plus-jakarta-sans-latin-{w}-normal.woff2'), 'rb').read()).decode()) for w in (500, 600))
fonts += ''.join("@font-face{font-family:'%s';font-style:normal;font-weight:%d;font-display:swap;src:url(data:font/woff2;base64,%s) format('woff2')}"
                 % (fam, w, base64.b64encode(open(os.path.join(HERE, f), 'rb').read()).decode())
                 for fam, w, f in [('Instrument Serif', 400, 'instrument-serif-latin-400-normal.woff2'), ('JetBrains Mono', 400, 'jetbrains-mono-latin-400-normal.woff2'), ('JetBrains Mono', 500, 'jetbrains-mono-latin-500-normal.woff2')])

# ---------------------------------------------------------------- page
slides = rd('slides.html')
slides = re.sub(r'\{\{i:([\w-]+)\}\}', lambda m: ic(m.group(1)), slides)
slides = re.sub(r'\{\{logo:(\d+):(light|dark)\}\}', lambda m: logo(int(m.group(1)), m.group(2) == 'light'), slides)
slides = re.sub(r'\{\{mark:(\d+)\}\}', lambda m: mark(int(m.group(1))), slides)
assert '{{' not in slides

html = f'''<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>YouCraft · Présentation</title>
<style>{fonts}</style>
<style>{rd('deck.css')}</style>
<style>/* écrans des applications YouCraft */{''.join(YACSS)}</style>
</head><body>
<main id="deck" aria-live="polite">
<div id="chrome"><div class="lg"><span class="l1">{logo(34, False)}</span><span class="l2">{logo(34, True)}</span></div><div class="ch"></div><div id="prog"></div></div>
{slides}
</main>
<nav id="nav" aria-label="Commandes de présentation">
  <button data-prev aria-label="Diapositive précédente">{ic('arrow-left2')}</button><span>1 / 1</span><button data-next aria-label="Diapositive suivante">{ic('arrow-right2')}</button>
  <button data-menu aria-label="Sommaire (touche M)">{ic('list')}</button><button data-full aria-label="Plein écran (touche F)">{ic('maximize')}</button>
</nav>
<div id="fsv" role="dialog" aria-modal="true" aria-label="Application en plein écran"><div class="dwin fw"><div class="dbar"><span class="ddots"><b></b><b></b><b></b></span><span class="durl">{ic('lock')}<span data-url></span></span><span class="dlive">En ligne</span><button class="fx" data-close aria-label="Fermer le plein écran (Échap)">{ic('x')}</button></div><div class="host" id="h-full"></div></div><span class="fhint">Échap pour revenir à la présentation</span></div>
<div id="menu" role="dialog" aria-modal="true" aria-label="Sommaire"><div class="mw"><h4>Aller à une diapositive<small>Touche M pour ouvrir, Échap pour fermer</small></h4><ol></ol></div></div>
{SHIM}
{SHIM_YB}
{ENGINE}
{NAVJS}
<script>{rd('deck.js')}</script>
</body></html>'''
out = os.path.join(HERE, '..', 'youcraft-presentation_2_0.html')
open(out, 'w', encoding='utf-8').write(html)
print(os.path.abspath(out), round(len(html) / 1024), 'Ko', len(YACSS), 'règles CSS des applications')

"""Charte YouCraft pour le film « Bring your ideas to life » (src_b50 -> src_bb), sans toucher au déroulé.
Usage : python3 apply_brand_bb.py src_b50 src_bb
- charge brand_yc.css / yc_logo_data.js / brand_yc.js, puis les dashboards métier (dash_yc.js) et les scènes YouCraft (s8_yc.js) ;
- convertit l'ancienne palette (marine, bleu ciel, violets) vers Ink, Stone, Ultramarine et Lime (mêmes tables que apply_brand.py) ;
- Plus Jakarta Sans pour les titres, DM Sans pour le texte ; le nom DigiCraft devient YouCraft."""
import ast, os, re, shutil, sys

HERE = os.path.dirname(os.path.abspath(__file__))
src, dst = sys.argv[1], sys.argv[2]
if os.path.exists(dst):
    shutil.rmtree(dst)
shutil.copytree(src, dst)

# Tables de couleurs de la vidéo de lancement, reprises telles quelles.
tables = {}
for node in ast.parse(open(os.path.join(HERE, 'apply_brand.py'), encoding='utf-8').read()).body:
    if isinstance(node, ast.Assign) and isinstance(node.targets[0], ast.Name) and node.targets[0].id in ('HEX', 'RGB'):
        tables[node.targets[0].id] = ast.literal_eval(node.value)
HEX, RGB = tables['HEX'], tables['RGB']
# Teintes propres à ce film (aurores, fonds de plans, accents produit).
HEX.update({
    '#0B4471': '#16161C', '#062A4C': '#0B0B0F', '#010F1F': '#0B0B0F', '#0F4F80': '#16161C', '#3B2A8C': '#16161C', '#1C1550': '#0B0B0F',
    '#0A0826': '#0B0B0F', '#0B5F6E': '#16161C', '#063B4A': '#0B0B0F', '#021A24': '#0B0B0F', '#0A3358': '#16161C', '#2A1F6B': '#16161C',
    '#022446': '#0B0B0F', '#06213C': '#16161C', '#010A14': '#0B0B0F', '#04203C': '#0B0B0F', '#0B3A63': '#16161C', '#063B3A': '#16161C',
    '#3A1D5C': '#16161C', '#3B6FD8': '#4F6BFF', '#7CC3EC': '#DFE8F0', '#A99BFF': '#B4C0FF', '#5FD3E0': '#DFE8F0', '#C9B8FF': '#E8ECFF',
    '#E2D4FF': '#E8ECFF', '#E2F1FA': '#FBFAF6', '#EAF4FB': '#FBFAF6', '#CFE6F5': '#DFE8F0', '#F4F8FC': '#FBFAF6', '#E9F1FA': '#F5F2EA',
    '#F3EEFF': '#F1F3FF', '#8F7CF5': '#4F6BFF', '#E7E3FF': '#E8ECFF', '#CFC7FF': '#D3DAFF', '#DDF0FB': '#F5F2EA', '#B8DDF2': '#DFE8F0',
    '#ECE4FF': '#E8ECFF', '#D6C8FF': '#D3DAFF', '#3B2F9E': '#1E3BFF', '#4C3F99': '#55534E', '#BFD8F2': '#DFE8F0', '#DCD3FF': '#E8ECFF',
    '#F2EEFF': '#F5F2EA', '#EEF2FF': '#EEF0FF', '#F4F2FF': '#F1F3FF', '#0B2545': '#0B0B0F', '#64748B': '#77746E', '#94A3B8': '#8F8C86',
    '#334155': '#3A3833', '#475569': '#55534E', '#1E293B': '#16161C', '#CBD5E1': '#D3CEC3', '#E2E8F0': '#E6E1D6', '#F1F5F9': '#F1EEE7',
    '#F7FAFC': '#F5F2EA', '#F6F8FB': '#F6F4EE', '#E5ECF2': '#E6E1D6', '#DCE5EE': '#E3DED3', '#E8EEF4': '#ECE8DF', '#F4F7FA': '#F4F2EC',
    '#E8F0FB': '#EEF0FF', '#F1F5FB': '#F4F2EC', '#1E3A8A': '#1E3BFF', '#1D6FA3': '#1E3BFF', '#E0EEFA': '#E8ECFF', '#EDE9FF': '#E8ECFF',
    '#1C2430': '#16161C', '#0B1118': '#0B0B0F', '#10161D': '#0B0B0F', '#5D6670': '#55534E', '#0E5F5C': '#0B0B0F', '#0B5F5C': '#16161C',
    '#E6F6F5': '#F1F3FF', '#0EA5A4': '#1E3BFF', '#A855F7': '#4F6BFF',
})
RGB.update({
    (109, 93, 246): (30, 59, 255), (120, 100, 255): (30, 59, 255), (190, 215, 255): (223, 232, 240), (183, 166, 255): (223, 232, 240),
    (143, 198, 231): (223, 232, 240), (1, 36, 70): (11, 11, 15), (1, 30, 60): (11, 11, 15), (6, 28, 52): (11, 11, 15), (8, 20, 36): (11, 11, 15),
    (20, 16, 70): (11, 11, 15), (10, 8, 50): (11, 11, 15), (8, 6, 40): (11, 11, 15), (2, 20, 44): (11, 11, 15), (1, 8, 20): (11, 11, 15),
    (1, 40, 60): (11, 11, 15), (30, 60, 90): (11, 11, 15), (20, 40, 60): (11, 11, 15), (43, 30, 120): (22, 22, 28), (46, 139, 192): (30, 59, 255),
    (226, 241, 250): (251, 250, 246), (255, 214, 170): (245, 242, 234), (15, 88, 133): (30, 59, 255),
})

hex_re = re.compile(r'#[0-9A-Fa-f]{6}\b')
rgb_re = re.compile(r'rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,')
for f in sorted(os.listdir(dst)):
    if not f.endswith(('.js', '.css')):
        continue
    p = os.path.join(dst, f)
    s = open(p, encoding='utf-8').read()
    s = hex_re.sub(lambda m: HEX.get(m.group(0).upper(), m.group(0)), s)
    s = rgb_re.sub(lambda m: 'rgba(%d,%d,%d,' % RGB.get(tuple(int(v) for v in m.groups()), tuple(int(v) for v in m.groups())), s)
    s = s.replace("'Outfit'", "'Plus Jakarta Sans'").replace("'Montserrat'", "'DM Sans'")
    s = s.replace('digicraft.leyton-cognitx.com', 'youcraft.leyton-cognitx.com').replace('digicraft.app', 'youcraft.app').replace('DigiCraft', 'YouCraft')
    open(p, 'w', encoding='utf-8').write(s)

# Interface produit : logo officiel dans l'accueil, portraits des contacts sur les fiches clients du portail.
p = os.path.join(dst, 's2_build.js')
s = open(p, encoding='utf-8').read()
for a, b in [
    (r"window\.dcIcon\(40, 'hm', 'brand'\) \+ '<span class=\"nh\"[^\n]*?Craft</span></span>'", "window.ycLogoSvg(40)"),
    (r"el\(cd, \{ left: '22px', top: '22px', width: '46px', height: '46px', borderRadius: '13px', background: col \+ '22', display: 'flex', alignItems: 'center', justifyContent: 'center' \}, icon\('landmark', 22, 2, col\)\);",
     "el(cd, { left: '22px', top: '22px', width: '46px', height: '46px', borderRadius: '23px', overflow: 'hidden' }, window.ycFace(46, i * 3 + 1));"),
]:
    s, n = re.subn(a, b, s)
    assert n == 1, (a[:60], n)
open(p, 'w', encoding='utf-8').write(s)

# Chargement de la charte et des nouveaux modules.
p = os.path.join(dst, 'index.html')
s = open(p, encoding='utf-8').read()
for a, b in [
    ('<link rel="stylesheet" href="b44.css">', '<link rel="stylesheet" href="b44.css">\n<link rel="stylesheet" href="../brand_yc/brand_yc.css">\n<link rel="stylesheet" href="bb.css">'),
    ('<script src="../src6/common.js"></script>', '<script src="../src6/common.js"></script>\n<script src="../brand_yc/yc_logo_data.js"></script>\n<script src="../brand_yc/brand_yc.js"></script>'),
    ('<script src="s7_v5.js"></script>', '<script src="s7_v5.js"></script>\n<script src="dash_yc.js"></script>\n<script src="s8_yc.js"></script>'),
    ('<title>DigiCraft Bring your ideas to life</title>', '<title>YouCraft Bring your ideas to life</title>'),
]:
    assert s.count(a) == 1, a
    s = s.replace(a, b)
open(p, 'w', encoding='utf-8').write(s)
for f in ('bb.css', 'dash_yc.js', 's8_yc.js'):
    shutil.copy(os.path.join(HERE, 'bb', f), os.path.join(dst, f))
print('charte YouCraft appliquée :', dst)

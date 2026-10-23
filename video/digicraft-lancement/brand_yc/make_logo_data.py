"""Extrait du brand kit (SVG officiels) le symbole et le wordmark YouCraft, lettre par lettre, pour l'animation.
Usage : python3 -I make_logo_data.py <dossier svg du kit> <sortie.js>"""
import json, re, sys

src, out = sys.argv[1], sys.argv[2]
logo = open(f'{src}/horizontal/YouCraft_logo_color_on-dark.svg').read()
vb = [float(v) for v in re.search(r'viewBox="([^"]+)"', logo).group(1).split()]
word = re.findall(r'<path d="([^"]+)" fill="#F5F2EA" transform="translate\(([^)]+)\)"', logo)
d, tr = [w for w in word if len(w[0]) > 500][0]
tx, ty = [float(v) for v in tr.split()]
subs = [s for s in re.split(r'(?=M)', d) if s.strip()]


def bbox(s):
    n = [float(v) for v in re.findall(r'-?\d+\.?\d*', s)]
    return min(n[0::2]), max(n[0::2]), min(n[1::2]), max(n[1::2])


# Les contours intérieurs (o, a) rejoignent la lettre qui les contient.
glyphs = []
for s in subs:
    b = bbox(s)
    host = next((g for g in glyphs if g['b'][0] <= b[0] and b[1] <= g['b'][1] and g['b'][2] <= b[2] and b[3] <= g['b'][3]), None)
    if host:
        host['d'] += s
    else:
        glyphs.append({'d': s, 'b': b})
sym = {
    'dome': re.search(r'<path d="(M0 115[^"]+)"', logo).group(1),
    'quarter': re.search(r'<path d="(M32 247[^"]+)"', logo).group(1),
    'block': [float(v) for v in re.search(r'<rect x="([\d.]+)" y="([\d.]+)" width="([\d.]+)" height="([\d.]+)"', logo).groups()],
}
data = {'w': vb[2], 'h': vb[3], 'tx': tx, 'ty': ty, 'glyphs': [g['d'] for g in glyphs], 'sym': sym,
        'grad': [[0, '#1F3FE6'], [0.55, '#2C4CF0'], [0.82, '#DFE8F0'], [1, '#E4F53C']]}
open(out, 'w').write('// Généré par make_logo_data.py depuis le brand kit YouCraft (SVG officiels, texte vectorisé).\nwindow.YC_LOGO = ' + json.dumps(data) + ';\n')
print(len(glyphs), 'lettres', vb)

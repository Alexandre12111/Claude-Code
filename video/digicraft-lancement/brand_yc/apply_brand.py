"""Applique la charte YouCraft à une version de la vidéo de lancement, sans toucher au déroulé ni aux textes.
Usage : python3 apply_brand.py src_yc_en src_yb_en
- charge brand_yc.css / yc_logo_data.js / brand_yc.js (logo, symbole, fonds, accents) ;
- remplace le logo et le symbole de l'ancienne charte dans les scènes (intro, fin, interface produit) ;
- convertit toutes les couleurs de l'ancienne palette vers Ink, Stone, Ultramarine et Lime ;
- Plus Jakarta Sans pour les titres, DM Sans pour le texte."""
import os, re, shutil, sys

src, dst = sys.argv[1], sys.argv[2]
if os.path.exists(dst):
    shutil.rmtree(dst)
shutil.copytree(src, dst)


def edit(name, pairs, regex=False):
    p = os.path.join(dst, name)
    s = open(p, encoding='utf-8').read()
    for a, b in pairs:
        if regex:
            s, n = re.subn(a, b, s, flags=re.S)
        else:
            n = s.count(a)
            s = s.replace(a, b)
        assert n == 1, (name, a[:60], n)
    open(p, 'w', encoding='utf-8').write(s)


# Chargement de la charte.
edit('index.html', [
    ('<link rel="stylesheet" href="style.css">', '<link rel="stylesheet" href="style.css">\n<link rel="stylesheet" href="../brand_yc/brand_yc.css">'),
    ('<script src="common.js"></script>', '<script src="common.js"></script>\n<script src="../brand_yc/yc_logo_data.js"></script>\n<script src="../brand_yc/brand_yc.js"></script>'),
])

# Ouverture : logo YouCraft officiel en calques, symbole en traits fins à la place de l'anneau, pas de halo sur le logo.
edit('p01_logo.js', [
    (r"a\.halo = glow\(a\.zoom, 900, '[^']+', 0\);", "a.halo = glow(a.zoom, 900, 'rgba(251,250,246,0)', 0);"),
    (r"      a\.ring = h\('img', 'abs', a\.zoom\);\n.*?opacity: '0\.55' \}\);\n",
     "      a.ring = C.ycSketch(a.zoom, 1000, 'rgba(11,11,15,0.09)');\n      Object.assign(a.ring.style, { left: CX - 500 + 'px', top: CY - 500 + 'px', opacity: '0' });\n"),
    (r"      const ic = h\('div', '', lock\);\n.*?a\.craft\._c\.forEach\(\(c\) => c\.classList\.add\('o'\)\);\n",
     "      a.L = C.ycLogo(lock, 220);\n      a.ic = a.L.sym;\n"),
    (r"S\(a\.ring, \{ s: lerp\(0\.7, 1, rp\), r: lerp\(-60, 0, rp\) \+ T \* 4, o: rp \* 0\.55 \}\);",
     "S(a.ring, { s: lerp(0.9, 1, rp) + 0.012 * T, o: rp });"),
    (r"      const dirs = \[\[0, 0\].*?riseChars\(T, \[\.\.\.a\.digi\._c, \.\.\.a\.craft\._c\], 0\.7, 0\.04, 0\.7, 90\);",
     "      C.ycLogoAnim(a.L, T, 0.45);\n      riseChars(T, a.L.glyphs, 0.7, 0.04, 0.7, 90);"),
], regex=True)

# Signature finale : même logo, bouton d'appel à l'action Ink avec pastille Lime.
edit('p13_fin.js', [
    (r"      a\.ring = h\('img', 'abs', root\);\n.*?opacity: '0\.35' \}\);\n",
     "      a.ring = C.ycSketch(root, 1000, 'rgba(11,11,15,0.08)');\n      Object.assign(a.ring.style, { left: 960 - 500 + 'px', top: 400 - 500 + 'px', opacity: '0' });\n"),
    (r"      const ic = h\('div', '', lock\);\n.*?a\.craft\._c\.forEach\(\(c\) => c\.classList\.add\('o'\)\);\n",
     "      a.L = C.ycLogo(lock, 190);\n"),
    (r"S\(a\.ring, \{ r: T \* 5, s: 0\.9 \+ 0\.1 \* P\(T, 46\.2, 3\), o: 0\.35 \* P\(T, 46\.2, 1\) \}\);",
     "S(a.ring, { s: 0.92 + 0.08 * P(T, 46.2, 3, Ease.outSine), o: P(T, 46.2, 1) });"),
    (r"      const dirs = \[\[0, 0\].*?riseChars\(T, \[\.\.\.a\.digi\._c, \.\.\.a\.craft\._c\], 46\.6, 0\.035, 0\.6, 90\);",
     "      C.ycLogoAnim(a.L, T, 46.35);\n      riseChars(T, a.L.glyphs, 46.6, 0.035, 0.6, 90);"),
    (r"background:#012D48;color:#fff;", "background:#0B0B0F;color:#F5F2EA;"),
    (r"border-radius:32px;background:#fff;", "border-radius:32px;background:#D7FF3F;"),
], regex=True)

# Plan « fait par vous » : symbole en traits fins (Stone), sans rotation.
edit('p12_cree.js', [
    (r"      a\.ringImg = E\.h\('img', 'abs', root\);\n.*?opacity: '0' \}\);\n",
     "      a.ringImg = C.ycSketch(root, 1200, 'rgba(245,242,234,0.10)');\n      Object.assign(a.ringImg.style, { left: 960 - 600 + 'px', top: 540 - 600 + 'px', opacity: '0' });\n"),
    (r"S\(a\.ringImg, \{ r: T \* 12, ", "S(a.ringImg, { "),
    (r"o: 0\.22 \* P\(T, 44\.5, 0\.8\)", "o: P(T, 44.5, 0.8)"),
], regex=True)

# Valeur : surtitre en DM Sans Medium gris, barre clé en Lime.
edit('p02_valeur.js', [
    (r"letterSpacing: '0\.34em', textTransform: 'uppercase', color: '#82BEE1' \}\);",
     "letterSpacing: '0.3em', textTransform: 'uppercase', color: '#8F8C86', fontFamily: 'DM Sans', fontWeight: '500' });"),
    (r"background: i === 5 \? '#2E8BC0'", "background: i === 5 ? '#D7FF3F'"),
], regex=True)

# Idées : le cadenas devient le symbole (sur pastille Ink).
edit('p04_idees.js', [("window.dcIcon(84, 'g4', 'brand')", "window.dcIcon(84, 'g4', 'dark')")])

# Interface produit : logo officiel, titre Ultramarine, boutons principaux Lime à texte Ink.
edit('p06_demo.js', [
    (r"\$\{window\.dcIcon\(36, 'h'\)\}<span><span style=\"color:#012D48\">You</span><span style=\"color:#2E8BC0\">Craft</span></span>", "${window.ycLogoSvg(34)}"),
    (r"<span style=\"background:linear-gradient\(90deg,#A855F7,#C084FC 60%,#D8B4FE\);-webkit-background-clip:text;background-clip:text;color:transparent\">", '<span style="color:#1E3BFF">'),
    (r"background: 'linear-gradient\(90deg,#7C3AED,#9333EA\)', color: '#fff'", "background: '#D7FF3F', color: '#0B0B0F'"),
    (r"boxShadow: '0 10px 24px rgba\(124,58,237,0\.35\)'", "boxShadow: '0 10px 24px rgba(11,11,15,0.16)'"),
    (r"icon\('send', 24, 2\.2, '#fff'\)", "icon('send', 24, 2.2, '#0B0B0F')"),
    (r"background: 'rgba\(255,255,255,0\.55\)' \}\)\);\n    const chips", "background: 'rgba(11,11,15,0.22)' }));\n    const chips"),
    (r"background: 'linear-gradient\(90deg,#6D5DF6,#3B82F6\)', color: '#fff'", "background: '#D7FF3F', color: '#0B0B0F'"),
    (r"boxShadow: '0 8px 20px rgba\(79,70,229,0\.30\)'", "boxShadow: '0 8px 20px rgba(11,11,15,0.16)'"),
    (r"icon\('rocket', 22, 2\.2, '#fff'\)", "icon('rocket', 22, 2.2, '#0B0B0F')"),
], regex=True)

# Plans sombres : texte Stone plutôt que blanc pur.
for f in ('p02_valeur.js', 'p10_adopte.js', 'p12_cree.js'):
    p = os.path.join(dst, f)
    s = open(p, encoding='utf-8').read().replace("color: '#fff'", "color: '#F5F2EA'").replace('#FFFFFF', '#F5F2EA')
    open(p, 'w', encoding='utf-8').write(s)

# Fuites de lumière : Paper et brume au lieu du bleu ciel.
edit('fx.js', [
    ("rgba(187,223,244,0.95), rgba(143,198,231,0.55) 38%, rgba(46,139,192,0.18) 70%, rgba(46,139,192,0) 100%", "rgba(251,250,246,0.9), rgba(223,232,240,0.5) 38%, rgba(30,59,255,0.10) 70%, rgba(30,59,255,0) 100%"),
    ("rgba(225,241,250,0.9), rgba(130,190,225,0.45) 40%, rgba(46,139,192,0) 100%", "rgba(251,250,246,0.9), rgba(223,232,240,0.45) 40%, rgba(30,59,255,0) 100%"),
])

# Palette : ancienne charte (marine, bleu ciel, violets produit, gris froids) vers la palette YouCraft.
HEX = {
    # Ink et surfaces sombres
    '#0B2545': '#0B0B0F', '#012D48': '#0B0B0F', '#022446': '#0B0B0F', '#011A33': '#0B0B0F', '#111827': '#0B0B0F', '#0B1B2E': '#0B0B0F',
    '#0F5885': '#0B0B0F', '#1F2937': '#16161C', '#312E81': '#16161C', '#2A3B52': '#2A2A31', '#374151': '#3A3833', '#21597A': '#16161C',
    # Ultramarine (accent) et Ultramarine éclairci (sur Ink)
    '#2E8BC0': '#1E3BFF', '#2E9AD8': '#1E3BFF', '#1E9BD7': '#1E3BFF', '#23709D': '#1E3BFF', '#3880A9': '#1E3BFF',
    '#7C3AED': '#1E3BFF', '#6D5DF6': '#1E3BFF', '#9333EA': '#1E3BFF', '#6D28D9': '#1E3BFF',
    '#3B82F6': '#4F6BFF', '#4AAAE2': '#4F6BFF', '#82BEE1': '#4F6BFF', '#8FC6E7': '#4F6BFF', '#94C8E6': '#4F6BFF', '#8F8AFF': '#4F6BFF',
    '#8B5CF6': '#4F6BFF', '#A855F7': '#4F6BFF', '#4AA6DC': '#4F6BFF', '#6BB8E6': '#4F6BFF',
    '#C084FC': '#7F93FF', '#D8B4FE': '#B4C0FF', '#B3D8ED': '#C7D0FF', '#B7A6FF': '#7F93FF',
    # Teintes claires bleutées et violettes
    '#EDE9FE': '#E8ECFF', '#F4F1FF': '#F1F3FF', '#E4F2FB': '#E8ECFF', '#E0F2FE': '#E8ECFF', '#DBEEF9': '#E3E8FF', '#EFF7FC': '#F1F3FF',
    '#F6FBFD': '#F7F8FF', '#F0F8FD': '#F3F5FF', '#D5EAF7': '#D3DAFF', '#CBE6F6': '#DFE8F0', '#C4E0F0': '#DFE8F0', '#B6DDF3': '#DFE8F0',
    '#BFE3F7': '#DFE8F0', '#E6EEF6': '#F5F2EA', '#DDEFFA': '#F5F2EA', '#EDF7FC': '#FBFAF6', '#D3EAF8': '#DFE8F0',
    '#ECEAF5': '#ECEAE4', '#E9E5F5': '#E7E3DA',
    # Gris froids vers gris chauds de la marque
    '#6B7280': '#77746E', '#9CA3AF': '#8F8C86', '#4A5568': '#55534E', '#4B5563': '#55534E', '#6B7B88': '#8F8C86', '#899AA8': '#8F8C86',
    '#E5EAF0': '#E6E1D6', '#F3F4F6': '#F1EEE7', '#A9BCD0': '#B5B0A5', '#EEF0F4': '#EEEBE4', '#F4F6FA': '#F4F2EC', '#EEF2F6': '#EEEBE4',
    '#E5E7EB': '#E6E1D6', '#E3E6EA': '#E3DED3', '#E1E9ED': '#E3DED3', '#F9FAFB': '#FBFAF6', '#F7FAFC': '#FBFAF6', '#F8F9FB': '#F8F6F1',
    '#F7F9FC': '#F8F6F1', '#F6F8FB': '#F6F4EE', '#EEF0F3': '#EEEBE4', '#EBEFF2': '#EDEAE3', '#D5DEE8': '#D9D4C8', '#D1D5DB': '#D3CEC3',
    '#CAD7DF': '#CFC9BD', '#C9D9E8': '#DFE8F0', '#E2E8F0': '#E6E1D6',
}
RGB = {
    (46, 139, 192): (30, 59, 255), (1, 45, 72): (11, 11, 15), (2, 36, 70): (11, 11, 15), (1, 25, 42): (11, 11, 15), (17, 24, 39): (11, 11, 15),
    (182, 221, 243): (223, 232, 240), (143, 198, 231): (79, 107, 255), (130, 190, 225): (79, 107, 255), (168, 85, 247): (79, 107, 255),
    (79, 70, 229): (30, 59, 255), (76, 29, 149): (22, 22, 28), (124, 58, 237): (30, 59, 255), (225, 241, 250): (251, 250, 246),
    (187, 223, 244): (245, 242, 234), (177, 218, 242): (245, 242, 234), (24, 110, 168): (30, 59, 255), (15, 88, 133): (30, 59, 255),
    (11, 68, 113): (22, 22, 28), (173, 217, 242): (245, 242, 234),
}
hex_re = re.compile(r'#[0-9A-Fa-f]{6}\b')
rgb_re = re.compile(r'rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,')
for f in sorted(os.listdir(dst)):
    if not (f.startswith('p') and f.endswith('.js')) and f != 'fx.js':
        continue
    p = os.path.join(dst, f)
    s = open(p, encoding='utf-8').read()
    s = hex_re.sub(lambda m: HEX.get(m.group(0).upper(), m.group(0)), s)
    s = rgb_re.sub(lambda m: 'rgba(%d,%d,%d,' % RGB.get(tuple(int(v) for v in m.groups()), tuple(int(v) for v in m.groups())), s)
    # Polices de la marque.
    s = s.replace("'Outfit'", "'Plus Jakarta Sans'").replace("'Montserrat'", "'DM Sans'")
    open(p, 'w', encoding='utf-8').write(s)
print('charte YouCraft appliquée :', dst)

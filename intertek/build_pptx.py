"""Version PowerPoint modifiable de la slide Intertek (charte YouCraft).
Positions et textes mesurés sur la slide HTML (measure.cjs) ; textes et cartes en objets PowerPoint natifs,
fond, logo et pictogrammes en images.
Usage : node measure.cjs <slide.html> <dossier> && python3 build_pptx.py <dossier>  ->  Intertek_YouCraft_slide.pptx"""
import json, os, sys
from pptx import Presentation
from pptx.util import Emu, Pt
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.oxml.ns import qn
from lxml import etree

D = sys.argv[1]
M = json.load(open(os.path.join(D, 'm.json')))
HERE = os.path.dirname(os.path.abspath(__file__))
PX = 6350  # EMU par pixel (1920 px = 13,333 pouces)
H, B = 'Plus Jakarta Sans', 'DM Sans'
TXT, MUT, LIME = 'F5F2EA', '9C99A3', 'D7FF3F'

prs = Presentation(); prs.slide_width = Emu(1920 * PX); prs.slide_height = Emu(1080 * PX)
s = prs.slides.add_slide(prs.slide_layouts[6])
E = lambda v: Emu(int(round(v * PX)))
rgb = lambda h: RGBColor.from_string(h)


def pic(f, r, w=None, h=None):
    s.shapes.add_picture(os.path.join(D, f), E(r['x']), E(r['y']), E(w or r['w']), E(h or r['h']))


def text(r, runs, size, font=B, color=TXT, bold=False, wrap=False, align=None, spacing=None, line=None, pad_w=40, anchor=MSO_ANCHOR.TOP, w=None):
    tb = s.shapes.add_textbox(E(r['x']), E(r['y']), E(w if w is not None else r['w'] + pad_w), E(r['h']))
    tf = tb.text_frame; tf.word_wrap = True; tf.vertical_anchor = anchor; tf.auto_size = None
    for a in ('margin_left', 'margin_right', 'margin_top', 'margin_bottom'): setattr(tf, a, 0)
    lines = runs if isinstance(runs, list) and runs and isinstance(runs[0], list) else [runs if isinstance(runs, list) else [(runs, {})]]
    for i, ln in enumerate(lines):
        para = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        para.alignment = align or PP_ALIGN.LEFT
        if line: para.line_spacing = line
        for t, o in ln:
            run = para.add_run(); run.text = t
            f = run.font; f.name = o.get('font', font); f.size = Pt(o.get('size', size) * 0.5); f.bold = o.get('bold', bold)
            f.color.rgb = rgb(o.get('color', color))
            if spacing is not None or 'spacing' in o:
                run._r.get_or_add_rPr().set('spc', str(int((o.get('spacing', spacing)) * 50)))
            if o.get('grad'):
                rPr = run._r.get_or_add_rPr()
                for sf in rPr.findall(qn('a:solidFill')): rPr.remove(sf)
                g = etree.SubElement(rPr, qn('a:gradFill')); gs = etree.SubElement(g, qn('a:gsLst'))
                for pos, c in ((0, LIME), (100000, '4F6BFF')):
                    st = etree.SubElement(gs, qn('a:gs'), pos=str(pos)); etree.SubElement(st, qn('a:srgbClr'), val=c)
                etree.SubElement(g, qn('a:lin'), ang='0', scaled='0')
                rPr.insert(0, g) if False else None
                # gradFill doit précéder latin dans rPr
                lat = rPr.find(qn('a:latin'))
                if lat is not None: rPr.remove(g); lat.addprevious(g)
    return tb


def box(r, fill, line=None, radius=28, grad=None, lw=1):
    sh = s.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, E(r['x']), E(r['y']), E(r['w']), E(r['h']))
    sh.adjustments[0] = min(0.5, radius / min(r['w'], r['h']))
    if grad:
        sh.fill.gradient(); sh.fill.gradient_angle = grad[0]
        st = sh.fill.gradient_stops; st[0].color.rgb = rgb(grad[1]); st[0].position = 0; st[1].color.rgb = rgb(grad[2]); st[1].position = 1
    elif fill is None:
        sh.fill.background()
    else:
        sh.fill.solid(); sh.fill.fore_color.rgb = rgb(fill)
    if line: sh.line.color.rgb = rgb(line); sh.line.width = Pt(lw * 0.75)
    else: sh.line.fill.background()
    sh.shadow.inherit = False
    sh.text_frame.text = ''
    return sh


# Fond, logo, étiquette
s.shapes.add_picture(os.path.join(D, 'bg.png'), 0, 0, prs.slide_width, prs.slide_height)
pic('logo.png', M['logo'][0])
tg = M['tag'][0]; box(tg, None, '6B7A2A', radius=tg['h'] / 2)
text(tg, 'PRÉSENTATION CLIENT', 18, bold=True, color=LIME, spacing=3.6, align=PP_ALIGN.CENTER, anchor=MSO_ANCHOR.MIDDLE, w=tg['w'], pad_w=0)

# Titre et raison d'être
h1 = M['h1'][0]
text(h1, [[('Intertek, leader mondial', {})], [('de ', {}), ('l’assurance qualité', {'grad': True})]], 72, font=H, bold=True, line=0.95, pad_w=120, spacing=-2.5)
q = M['q'][0]
bar = s.shapes.add_shape(MSO_SHAPE.RECTANGLE, E(q['x']), E(q['y']), E(4), E(q['h'])); bar.fill.solid(); bar.fill.fore_color.rgb = rgb(LIME); bar.line.fill.background()
qb = M['qb'][0]
text({'x': q['x'] + 28, 'y': qb['y'], 'w': q['w'] - 28, 'h': q['h']}, [[('RAISON D’ÊTRE', {'size': 14, 'bold': True, 'color': LIME, 'spacing': 2.8})], [('Donner vie à la qualité et à la sécurité, durablement', {'size': 24, 'color': 'D6D3CC'})]], 24, wrap=True, line=1.3, pad_w=0)

# Cartes
for r, cls in zip(M['cards'], M['cardcls']):
    if 'fr' in cls: box(r, None, '7A90FF', grad=(315, '1E3BFF', '5E78FF'))
    else: box(r, '0F1330', '2A2D3F')
for r in M['ch']:
    dot = s.shapes.add_shape(MSO_SHAPE.OVAL, E(r['x']), E(r['y'] + r['h'] / 2 - 5), E(10), E(10)); dot.fill.solid(); dot.fill.fore_color.rgb = rgb(LIME); dot.line.fill.background()
    inside_fr = any('fr' in c and cr['x'] <= r['x'] <= cr['x'] + cr['w'] and cr['y'] <= r['y'] <= cr['y'] + cr['h'] for cr, c in zip(M['cards'], M['cardcls']))
    text({'x': r['x'] + 22, 'y': r['y'], 'w': r['w'], 'h': r['h']}, r['t'].upper(), 16, bold=True, color='DCE1FF' if inside_fr else MUT, spacing=2.9, pad_w=60)

# Chiffres monde
for i, (k, l) in enumerate(zip(M['kb'], M['ks'])):
    text(k, k['t'], 52, font=H, bold=True, color=LIME if i < 3 else TXT, pad_w=60, spacing=-1.8)
    text(l, l['t'], 19, color=MUT, pad_w=40)

# ATIC
for i, (a, ai, ab) in enumerate(zip(M['at'], M['ati'], M['atb'])):
    box(a, '17205A', '4D5FC0', radius=18)
    box(ai, '242A33', None, radius=14)
    pic(f'ic{i}.png', {'x': ai['x'] + 11, 'y': ai['y'] + 11}, 26, 26)
    t = ab['t']; text(ab, [(t[0], {'color': LIME}), (t[1:], {})], 22, font=H, bold=True, pad_w=40)
ap = M['atp'][0]; text(ap, ap['t'], 18, color=MUT, pad_w=60)

# France
for k, l in zip(M['fkb'], M['fks']):
    text(k, k['t'], 60, font=H, bold=True, color=LIME, pad_w=60, spacing=-2.4)
    text(l, l['t'], 20, color='EDEBF5', pad_w=60)
d = M['fkd'][0]
ln = s.shapes.add_connector(1, E(d['x']), E(d['y']), E(d['x']), E(d['y'] + d['h'])); ln.line.color.rgb = rgb('8C9DFF'); ln.line.width = Pt(0.75)
hq = M['hq'][0]; box(hq, '2236C8', None, radius=14)
pic('ic4.png', {'x': hq['x'] + 16, 'y': hq['y'] + hq['h'] / 2 - 10}, 20, 20)
text({'x': hq['x'] + 46, 'y': hq['y'], 'w': hq['w'] - 46, 'h': hq['h']}, hq['t'].strip(), 18, anchor=MSO_ANCHOR.MIDDLE, pad_w=10)

# Secteurs
for i, (ic, li) in enumerate(zip(M['sei'], M['sel'])):
    box(ic, '1D2316', '5A6B1E', radius=10)
    pic(f'ic{5 + i}.png', {'x': ic['x'] + 8, 'y': ic['y'] + 8}, 18, 18)
    x0 = ic['x'] + ic['w'] + 12
    text({'x': x0, 'y': li['y'], 'w': li['x'] + li['w'] - x0, 'h': li['h']}, li['t'].strip(), 18, color='E6E3DC', wrap=True, anchor=MSO_ANCHOR.MIDDLE, line=1.15, pad_w=10)

# Pied de page
f0, f1 = M['ft']
text(f0, f0['t'], 16, color='6E6B75', pad_w=40)
text({'x': f1['x'] - 60, 'y': f1['y'], 'w': f1['w'] + 60, 'h': f1['h']}, f1['t'], 16, color='6E6B75', align=PP_ALIGN.RIGHT, pad_w=0)

out = os.path.join(HERE, 'Intertek_YouCraft_slide.pptx')
prs.save(out); print(out)

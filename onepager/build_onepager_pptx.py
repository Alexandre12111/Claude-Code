"""Versions PowerPoint (A4 portrait) des one-pagers client YouCraft.
Fond et visuels en image, chaque ligne de texte en zone de texte PowerPoint modifiable, aux positions mesurées
sur la page HTML (pptx_measure.cjs). Polices : Plus Jakarta Sans et DM Sans (Google Fonts).
Usage : python3 build_onepager_pptx.py [CIR IPBox General]"""
import json, os, re, subprocess, sys
from pptx import Presentation
from pptx.util import Emu, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import MSO_ANCHOR
from pptx.oxml.ns import qn

HERE = os.path.dirname(os.path.abspath(__file__))
TMP = os.environ.get('PPTX_TMP', '/tmp/onepager_pptx')
PX = 9525  # EMU par pixel CSS (96 ppp)
E = lambda v: Emu(int(round(v * PX)))


def rgb(c):
    v = [float(x) for x in re.findall(r'[\d.]+', c)]
    return RGBColor(*[int(round(x)) for x in v[:3]]), (v[3] if len(v) > 3 else 1)


def merge(lines):
    """Regroupe les fragments consécutifs d'une même ligne (ex. titre bicolore) dans une seule zone de texte."""
    out = []
    for L in lines:
        prev = out[-1] if out else None
        if prev and abs(L['y'] + L['h'] / 2 - (prev['y'] + prev['h'] / 2)) < 3 and -2 < L['x'] - prev['r'] < 2:
            gap = ' ' if L['x'] - prev['r'] > 1.5 or L['lead'] or prev['trail'] else ''
            prev['runs'].append((gap + L['t'], L)); prev['r'] = L['x'] + L['w']; prev['h'] = max(prev['h'], L['h']); prev['trail'] = L.get('trail', 0)
        else:
            out.append({'x': L['x'], 'y': L['y'], 'r': L['x'] + L['w'], 'h': L['h'], 'runs': [(L['t'], L)], 'trail': L.get('trail', 0)})
    return out


def build(name):
    html = os.path.join(HERE, f'YouCraft_OnePager_Client_{name}.html')
    d = os.path.join(TMP, name)
    subprocess.run(['node', os.path.join(HERE, 'pptx_measure.cjs'), html, d], check=True,
                   env={**os.environ, 'NODE_PATH': '/opt/node22/lib/node_modules'})
    lines = json.load(open(os.path.join(d, 'lines.json')))
    prs = Presentation(); prs.slide_width = Emu(7560000); prs.slide_height = Emu(10692000)  # A4 portrait
    s = prs.slides.add_slide(prs.slide_layouts[6])
    s.shapes.add_picture(os.path.join(d, 'bg.png'), 0, 0, prs.slide_width, prs.slide_height)
    for g in merge(lines):
        w = g['r'] - g['x']
        tb = s.shapes.add_textbox(E(g['x']), E(g['y']), E(w * 1.12 + 4), E(g['h']))
        tf = tb.text_frame; tf.word_wrap = False; tf.auto_size = None; tf.vertical_anchor = MSO_ANCHOR.MIDDLE
        for a in ('margin_left', 'margin_right', 'margin_top', 'margin_bottom'): setattr(tf, a, 0)
        para = tf.paragraphs[0]
        for t, L in g['runs']:
            r = para.add_run(); r.text = t.replace(' ', ' ')
            f = r.font; f.name = L['font']; f.size = Pt(round(L['size'] * 0.75 * 2) / 2); f.bold = L['weight'] >= 600
            col, a = rgb(L['color']); f.color.rgb = col
            a *= L.get('op', 1)
            rPr = r._r.get_or_add_rPr()
            if a < 0.98:
                sf = rPr.find(qn('a:solidFill')); clr = sf[0]
                clr.append(clr.makeelement(qn('a:alpha'), {'val': str(int(a * 100000))}))
            ls = L['ls']
            if ls not in ('normal', '0px'):
                rPr.set('spc', str(int(round(float(ls.rstrip('px')) * 0.75 * 100))))
    out = os.path.join(HERE, f'YouCraft_OnePager_Client_{name}.pptx')
    prs.save(out); print(out, len(lines), 'lignes')


for n in sys.argv[1:] or ['CIR', 'IPBox', 'General']:
    build(n)

"""Sous-titres v2 : un seul bandeau arrondi ajusté au texte (plus de rectangles qui se chevauchent),
texte plus grand, calage sur les pauses de la voix. Marchés : voix de synthèse ; 'fr' : voix enregistrée du client.
Usage : python3 subs_markets.py en uk ... fr"""
import json, os, sys
import numpy as np
import soundfile as sf
from PIL import ImageFont
from mix_markets import chunks, pauses, ts_ass, ts_srt

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.join(HERE, '..')
SUBS = os.path.join(ROOT, 'out', 'subs')
FONT = os.path.join(ROOT, 'assets', 'fonts', 'ttf', 'Montserrat-SemiBold.ttf')
SR = 48000

FS = 52                 # taille ASS (hauteur de ligne en px)
EM = FS / 1.5435        # taille réelle des glyphes rendus par libass pour Montserrat (mesurée)
SPACING = 0.3
PAD_X, PAD_Y, RADIUS = 34, 16, 20
BOTTOM = 1080 - 62      # bas du bloc de texte
BOX = '&H462402&'       # bleu marine CognitX #022446
BOX_ALPHA = '&H2E&'     # environ 82 % d'opacité
_font = ImageFont.truetype(FONT, round(EM))

# Voix enregistrée du client (version FR) : (début, fin) dans la prise -> instant de pose, texte réellement lu.
FR_CLIPS = [
    ((2.44, 6.95), 3.4, "Notre métier, depuis près de trente ans ? Trouver la valeur que les entreprises ne voient pas."),
    ((7.68, 10.64), 9.2, "Et aujourd'hui, le plus gros gisement se cache dans votre quotidien."),
    ((11.51, 14.85), 12.9, "Des idées d'outils pour gagner en productivité, vos équipes en ont plein."),
    ((15.42, 18.73), 16.85, "Ce qui leur manque, c'est de pouvoir les construire rapidement et simplement."),
    ((19.02, 20.66), 20.35, "Et cet outil existe : c'est DigiCraft."),
    ((20.92, 22.02), 22.6, "Le principe est très simple."),
    ((22.25, 25.12), 24.0, "Vous décrivez votre besoin avec vos propres mots."),
    ((25.4, 31.18), 27.35, "Et DigiCraft construit l'application sous vos yeux : les écrans, les données, les agents IA, les calculs, le code."),
    ((31.4, 35.78), 33.4, "Un clic, et votre outil est en ligne. Sur ordinateur comme sur mobile."),
    ((36.0, 41.08), 38.05, "DigiCraft a déjà fait ses preuves : plus de 750 utilisateurs, et plus de 350 applications déployées."),
    ((41.33, 46.64), 44.2, "Côté sécurité, vos données restent en Europe, les accès sont maîtrisés, et la prise en main est immédiate, sans aucune formation."),
    ((47.27, 48.19), 51.35, "Parce que c'est votre outil."),
    ((48.58, 49.33), 52.55, "Vraiment le vôtre."),
]


def clip_events(start, dur, text, audio):
    ch = chunks(text)
    total = sum(len(r) for r, _ in ch)
    ps = pauses(audio)
    out, t0, acc = [], 0.0, 0
    for k, (raw, txt) in enumerate(ch):
        acc += len(raw)
        if k == len(ch) - 1:
            t1 = dur - 0.08
        else:
            est = dur * acc / total
            near = [p for p in ps if abs(p - est) < 0.45]
            t1 = min(near, key=lambda p: abs(p - est)) if near else est
        out.append([start + t0, start + t1, txt])
        t0 = t1
    return out


def events_for(code):
    ev = []
    if not os.path.exists(os.path.join(HERE, 'vo_mk', code, 'meta.json')):
        # Ancienne version FR : voix enregistrée du client.
        x, _ = sf.read(os.path.join(HERE, 'stems', 'vo14_src.wav'))
        for (a, b), t, text in FR_CLIPS:
            ev += clip_events(t, b - a, text, x[int(a * SR):int(b * SR)])
    else:
        meta = json.load(open(os.path.join(HERE, 'vo_mk', code, 'meta.json')))
        for i, m in enumerate(meta):
            c, _ = sf.read(os.path.join(HERE, 'vo_mk', code, f'l{i:02d}.wav'))
            ev += clip_events(m['start'], m['dur'], m['sub'], c)
    for k, e in enumerate(ev):
        e[1] = max(e[1] + 0.15, e[0] + 0.9)
        if k + 1 < len(ev) and ev[k + 1][0] - e[1] < 0.3:
            e[1] = ev[k + 1][0] - 0.04
    return ev


def width(line):
    return _font.getlength(line) + SPACING * max(0, len(line) - 1)


def box(lines):
    """Rectangle arrondi qui englobe les glyphes de toutes les lignes, avec une marge régulière."""
    n = len(lines)
    w = max(width(l) for l in lines) + 2 * PAD_X
    top = BOTTOM - n * FS + 0.2 * FS - PAD_Y
    bot = BOTTOM - 0.14 * FS + PAD_Y
    h = bot - top
    r = RADIUS
    path = (f'm {r} 0 l {w - r:.0f} 0 b {w:.0f} 0 {w:.0f} 0 {w:.0f} {r} l {w:.0f} {h - r:.0f} b {w:.0f} {h:.0f} {w:.0f} {h:.0f} {w - r:.0f} {h:.0f} '
            f'l {r} {h:.0f} b 0 {h:.0f} 0 {h:.0f} 0 {h - r:.0f} l 0 {r} b 0 0 0 0 {r} 0')
    return 960 - w / 2, top, path


def write(code, ev):
    os.makedirs(SUBS, exist_ok=True)
    head = f"""[Script Info]
ScriptType: v4.00+
PlayResX: 1920
PlayResY: 1080
WrapStyle: 2
ScaledBorderAndShadow: yes

[V4+ Styles]
Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
Style: Sub,Montserrat SemiBold,{FS},&H00FFFFFF,&H00FFFFFF,&H00000000,&H00000000,0,0,0,0,100,100,{SPACING},0,1,0,0,2,0,0,{1080 - BOTTOM},1
Style: Box,Montserrat SemiBold,{FS},&H00462402,&H00462402,&H00000000,&H00000000,0,0,0,0,100,100,0,0,1,0,0,7,0,0,0,1

[Events]
Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text
"""
    with open(os.path.join(SUBS, f'{code}.ass'), 'w', encoding='utf-8') as f:
        f.write(head)
        for a, b, txt in ev:
            x0, y0, path = box(txt.split(r'\N'))
            f.write(f'Dialogue: 0,{ts_ass(a)},{ts_ass(b)},Box,,0,0,0,,{{\\an7\\pos({x0:.0f},{y0:.0f})\\1c{BOX}\\1a{BOX_ALPHA}\\bord0\\shad0\\blur0.6\\fad(140,140)\\p1}}{path}{{\\p0}}\n')
            f.write(f'Dialogue: 1,{ts_ass(a)},{ts_ass(b)},Sub,,0,0,0,,{{\\an2\\pos(960,{BOTTOM})\\fad(140,140)}}{txt}\n')
    with open(os.path.join(SUBS, f'{code}.srt'), 'w', encoding='utf-8') as f:
        for k, (a, b, txt) in enumerate(ev, 1):
            f.write(f'{k}\n{ts_srt(a)} --> {ts_srt(b)}\n{txt.replace(chr(92) + "N", chr(10))}\n\n')
    print(code, 'sous-titres', len(ev))


if __name__ == '__main__':
    for c in sys.argv[1:]:
        write(c, events_for(c))

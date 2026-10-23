"""Sous-titres v2 : un seul bandeau arrondi ajusté au texte (plus de rectangles qui se chevauchent),
texte plus grand, calage sur les pauses de la voix. Marchés : voix de synthèse ; 'fr' : voix enregistrée du client.
Usage : python3 subs_markets.py en uk ... fr"""
import json, os, subprocess, sys
import numpy as np
from scipy import ndimage
import soundfile as sf
from PIL import ImageFont
from mix_markets import chunks, pauses, ts_ass, ts_srt
from vo_markets import MARKETS

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.join(HERE, '..')
SR = 48000
# Nouvelle charte YouCraft (YC_BRAND=1) : DM Sans Medium sur bandeau Ink, texte Stone, image rebrandée.
BRAND = os.environ.get('YC_BRAND') == '1'
if BRAND:
    SUBS = os.path.join(ROOT, 'out', 'subs_yb')
    FONT = os.path.join(ROOT, 'assets', 'fonts', 'ttf_yc', 'DMSans-Medium.ttf')
    FONT_NAME, FS, EM_RATIO, CAP_TOP, DESC_BOT, SPACING = 'DM Sans Medium', 48, 1.343, 0.23, 0.08, 0
    BOX, BOX_ALPHA, TEXT = '&H0F0B0B&', '&H24&', '&H00EAF2F5'     # Ink #0B0B0F à 86 %, texte Stone #F5F2EA
else:
    SUBS = os.path.join(ROOT, 'out', 'subs')
    FONT = os.path.join(ROOT, 'assets', 'fonts', 'ttf', 'Montserrat-SemiBold.ttf')
    FONT_NAME, FS, EM_RATIO, CAP_TOP, DESC_BOT, SPACING = 'Montserrat SemiBold', 52, 1.5435, 0.2, 0.14, 0.3
    BOX, BOX_ALPHA, TEXT = '&H462402&', '&H2E&', '&H00FFFFFF'     # bleu marine CognitX #022446 à 82 %
EM = FS / EM_RATIO      # taille réelle des glyphes rendus par libass (mesurée pour chaque police)
PAD_X, PAD_Y, RADIUS = 34, 16, 20
BOTTOM = 1080 - 62      # bas du bloc de texte
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


def rect(lines, cx=960, bottom=BOTTOM):
    n = len(lines)
    w = max(width(l) for l in lines) + 2 * PAD_X
    return cx - w / 2, bottom - n * FS + CAP_TOP * FS - PAD_Y, cx + w / 2, bottom - DESC_BOT * FS + PAD_Y


def box(lines, cx=960, bottom=BOTTOM):
    """Rectangle arrondi qui englobe les glyphes de toutes les lignes, avec une marge régulière."""
    x0, top, x1, bot = rect(lines, cx, bottom)
    w, h, r = x1 - x0, bot - top, RADIUS
    path = (f'm {r} 0 l {w - r:.0f} 0 b {w:.0f} 0 {w:.0f} 0 {w:.0f} {r} l {w:.0f} {h - r:.0f} b {w:.0f} {h:.0f} {w:.0f} {h:.0f} {w - r:.0f} {h:.0f} '
            f'l {r} {h:.0f} b 0 {h:.0f} 0 {h:.0f} 0 {h - r:.0f} l 0 {r} b 0 0 0 0 {r} 0')
    return x0, top, path


# Placement : le bandeau reste centré en bas, sauf si l'image montre une interface à cet endroit pendant
# le sous-titre (contours détectés sur trois images) ; il descend alors un peu, puis se décale de côté.
SPOTS = [(960, BOTTOM), (960, BOTTOM + 26), (1200, BOTTOM + 26), (720, BOTTOM + 26), (1440, BOTTOM + 26), (480, BOTTOM + 26)]
ORIG = {'fr': 'video_cxfr2_silent.mp4', 'en': 'video_cxen_silent.mp4', 'uk': 'video_lt_uken_silent.mp4', 'us': 'video_lt_usen_silent.mp4', 'caen': 'video_lt_caen_silent.mp4',
        'cafr': 'video_lt_cafr_silent.mp4', 'de': 'video_lt_de_silent.mp4', 'es': 'video_lt_es_silent.mp4', 'it': 'video_lt_it_silent.mp4', 'nl': 'video_lt_nl_silent.mp4',
        'pl': 'video_lt_pl_silent.mp4', 'sv': 'video_lt_sv_silent.mp4'}


BAND = 780          # haut de la bande analysée (px) : les sous-titres vivent entre 780 et 1080
FPS = 10            # cadence d'analyse
_bands = {}


def band_edges(video):
    """Contours de la bande basse de toute la vidéo, 10 images par seconde, demi-résolution."""
    if video not in _bands:
        r = subprocess.run(['ffmpeg', '-v', 'error', '-i', video, '-vf', f'fps={FPS},crop=1920:{1080 - BAND}:0:{BAND},scale=960:{(1080 - BAND) // 2},format=gray',
                            '-f', 'rawvideo', '-'], capture_output=True, check=True)
        h = (1080 - BAND) // 2
        f = np.frombuffer(r.stdout, np.uint8).reshape(-1, h, 960).astype(np.float32)
        _bands[video] = np.stack([np.hypot(ndimage.sobel(x, 0), ndimage.sobel(x, 1)) / 8 > 6 for x in f])
    return _bands[video]


def conflicts(E, lines, cx, bottom, a, b, m=4):
    """Pour chaque image entre a et b : vrai si le bandeau à cet endroit recouvre une interface."""
    x0, top, x1, bot = rect(lines, cx, bottom)
    if x0 < 40 or x1 > 1880:
        return None
    i0, i1 = int(np.ceil(a * FPS)), int(b * FPS) + 1
    reg = E[i0:i1, max(0, int((top - m - BAND) / 2)):int((bot + m - BAND) / 2), int((x0 - m) / 2):int((x1 + m) / 2)]
    return reg.reshape(len(reg), -1).mean(1) > 0.004


def best_spot(E, lines, a, b, tol=2):
    """Premier emplacement (dans l'ordre de préférence) qui ne recouvre l'interface que sur tol images au plus
    (passage d'une transition) ; si le recouvrement n'arrive qu'en fin de sous-titre, on abrège celui-ci."""
    best = None
    for cx, bottom in SPOTS:
        c = conflicts(E, lines, cx, bottom, a, b)
        if c is None:
            continue
        end = b
        if c[-1]:
            k = len(c) - 1
            while k > 0 and c[k - 1]:
                k -= 1
            t = np.ceil(a * FPS) / FPS + k / FPS - 0.05
            if t - a >= 0.8:
                end, c = t, c[:k]
        n = int(c.sum())
        if n <= tol:
            return n, cx, bottom, end
        if best is None or n < best[0]:
            best = (n, cx, bottom, end)
    return best


def place(code, ev):
    """Renvoie (début, fin, texte, x, bas) ; un sous-titre de deux lignes sans place libre est scindé en deux lignes successives."""
    # Image muette renommée si elle existe déjà, sinon l'originale (même mise en page, seul le nom change).
    v = os.path.join(ROOT, 'out', f'video_yb_{code}_silent.mp4' if BRAND else (MARKETS[code][3] if code in MARKETS else ''))
    if not os.path.exists(v):
        v = os.path.join(ROOT, 'out', ORIG[code])
    E = band_edges(v)
    out = []
    for a, b, txt in ev:
        lines = txt.split(r'\N')
        n, cx, bottom, end = best_spot(E, lines, a, b)
        if n and len(lines) == 2:
            # Une ligne à la fois, partage du temps au prorata des caractères.
            t = a + (b - a) * len(lines[0]) / (len(lines[0]) + len(lines[1]))
            for (a2, b2, l) in ((a, t, lines[0]), (t, b, lines[1])):
                n2, cx2, bo2, e2 = best_spot(E, [l], a2, b2)
                out.append((a2, e2, l, cx2, bo2))
                print(f'  {code} {a2:5.1f} s ligne seule en {cx2},{bo2}{" (fin abrégée)" if e2 < b2 else ""}{" (reste " + str(n2) + " images)" if n2 else ""} | {l[:42]}')
            continue
        out.append((a, end, txt, cx, bottom))
        if (cx, bottom) != SPOTS[0] or end < b or n:
            print(f'  {code} {a:5.1f} s en {cx},{bottom}{" (fin abrégée)" if end < b else ""}{" (reste " + str(n) + " images)" if n else ""} | {txt[:42]}')
    return out


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
Style: Sub,{FONT_NAME},{FS},{TEXT},{TEXT},&H00000000,&H00000000,0,0,0,0,100,100,{SPACING},0,1,0,0,2,0,0,{1080 - BOTTOM},1
Style: Box,{FONT_NAME},{FS},&H00462402,&H00462402,&H00000000,&H00000000,0,0,0,0,100,100,0,0,1,0,0,7,0,0,0,1

[Events]
Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text
"""
    with open(os.path.join(SUBS, f'{code}.ass'), 'w', encoding='utf-8') as f:
        f.write(head)
        for a, b, txt, cx, bottom in place(code, ev):
            x0, y0, path = box(txt.split(r'\N'), cx, bottom)
            f.write(f'Dialogue: 0,{ts_ass(a)},{ts_ass(b)},Box,,0,0,0,,{{\\an7\\pos({x0:.0f},{y0:.0f})\\1c{BOX}\\1a{BOX_ALPHA}\\bord0\\shad0\\blur0.6\\fad(140,140)\\p1}}{path}{{\\p0}}\n')
            f.write(f'Dialogue: 1,{ts_ass(a)},{ts_ass(b)},Sub,,0,0,0,,{{\\an2\\pos({cx},{bottom})\\fad(140,140)}}{txt}\n')
    with open(os.path.join(SUBS, f'{code}.srt'), 'w', encoding='utf-8') as f:
        for k, (a, b, txt) in enumerate(ev, 1):
            f.write(f'{k}\n{ts_srt(a)} --> {ts_srt(b)}\n{txt.replace(chr(92) + "N", chr(10))}\n\n')
    print(code, 'sous-titres', len(ev))


if __name__ == '__main__':
    for c in sys.argv[1:]:
        write(c, events_for(c))

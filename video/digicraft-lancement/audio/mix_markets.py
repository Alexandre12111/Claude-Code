"""Mixage voix off + musique + sound design (minutage long) et sous-titres ASS/SRT calés sur les pauses de la voix.
Usage : python3 mix_markets.py en uk ..."""
import json, os, subprocess, sys
import numpy as np
import soundfile as sf
from scipy import signal
from scipy.ndimage import maximum_filter1d, uniform_filter1d
from vo_markets import END

HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
SUBS = os.path.join(HERE, '..', 'out', 'subs')
SR = 48000
N = int(60.0 * SR)


def peq(x, f0, g, q):
    b, a = signal.iirpeak(f0, q, fs=SR)
    return x + (10 ** (g / 20) - 1) * signal.lfilter(b, a, x)


def shelf(x, f0, g, high=True):
    sos = signal.butter(2, f0, 'high' if high else 'low', fs=SR, output='sos')
    return x + (10 ** (g / 20) - 1) * signal.sosfiltfilt(sos, x)


def comp(x, thr_db, ratio, att, rel):
    env = np.sqrt(np.maximum(uniform_filter1d(x ** 2, 48), 0))[::48]
    ca, cr = np.exp(-1 / (att * 1000)), np.exp(-1 / (rel * 1000))
    e = np.empty_like(env); v = 0.0
    for i in range(len(env)):
        c = ca if env[i] > v else cr
        v = c * v + (1 - c) * env[i]; e[i] = v
    g = -np.maximum(20 * np.log10(e + 1e-9) - thr_db, 0) * (1 - 1 / ratio)
    return x * 10 ** (np.interp(np.arange(len(x)), np.arange(len(g)) * 48 + 24, g) / 20)


def room(x):
    """Petite acoustique de studio : réflexions précoces et queue courte (RT60 ≈ 0,35 s), très discrète."""
    rng = np.random.default_rng(3)
    n = int(0.45 * SR)
    t = np.arange(n) / SR
    ir = rng.normal(0, 1, n) * np.exp(-t * 6.9 / 0.35)
    ir = signal.sosfiltfilt(signal.butter(2, [250, 6500], 'bandpass', fs=SR, output='sos'), ir)
    ir[: int(0.012 * SR)] = 0
    for d, g in ((0.007, 0.5), (0.013, 0.35), (0.021, 0.25)):
        ir[int(d * SR)] += g * np.max(np.abs(ir)) * 4
    ir /= np.sqrt(np.sum(ir ** 2))
    wet = signal.fftconvolve(x, ir)[: len(x)]
    return x + wet * 10 ** (-19 / 20)


def chain(x):
    """Voix de synthèse plus naturelle : grave propre, un peu de chaleur, présence, air, de-esser, compression douce, pièce."""
    x = signal.sosfiltfilt(signal.butter(4, 70, 'high', fs=SR, output='sos'), x)
    x = peq(x, 190, 1.2, 1.0)        # chaleur de poitrine
    x = peq(x, 420, -1.5, 1.2)       # moins de « boîte »
    x = peq(x, 3000, 1.8, 0.9)       # présence
    x = shelf(x, 10000, 1.5)          # air
    sib = signal.sosfiltfilt(signal.butter(4, [5000, 10000], 'bandpass', fs=SR, output='sos'), x)
    ratio = uniform_filter1d(np.abs(sib), 480) / (uniform_filter1d(np.abs(x), 480) + 1e-9)
    x = x - sib * uniform_filter1d(np.clip((ratio - 0.45) / 0.35, 0, 1) * 0.45, 240)
    x = comp(x, -24, 2.2, 0.006, 0.12)
    x = comp(x, -27, 1.6, 0.05, 0.4)
    return room(x)


def pauses(c):
    """Centres des silences de plus de 90 ms dans une réplique (secondes)."""
    e = np.sqrt(uniform_filter1d(c ** 2, int(0.02 * SR)))
    q = e < 0.06 * e.max()
    out, i = [], 0
    while i < len(q):
        if q[i]:
            j = i
            while j < len(q) and q[j]:
                j += 1
            if (j - i) / SR > 0.09 and i > 0 and j < len(q):
                out.append((i + j) / 2 / SR)
            i = j
        else:
            i += 1
    return out


def chunks(text, maxc=84, line=44):
    """Découpe une réplique en sous-titres d'au plus deux lignes, aux ponctuations puis aux mots."""
    import re
    parts = [p.strip() for p in re.split(r'(?<=[.?!:;,])\s+', text) if p.strip()]
    res, cur = [], ''
    for p in parts:
        cand = (cur + ' ' + p).strip()
        if len(cand) <= maxc:
            cur = cand
        else:
            if cur:
                res.append(cur)
            while len(p) > maxc:
                k = p.rfind(' ', 0, maxc)
                res.append(p[:k]); p = p[k + 1:]
            cur = p
    if cur:
        res.append(cur)

    def wrap(s):
        if len(s) <= line:
            return s
        sp = [i for i, ch in enumerate(s) if ch == ' ']
        def cost(i):
            a, b = i, len(s) - i - 1
            c = abs(a - b) + (40 if max(a, b) > line else 0)
            return c - (14 if s[i - 1] in '.?!:;' else 8 if s[i - 1] == ',' else 0)
        best = min(sp, key=cost)
        return s[:best] + r'\N' + s[best + 1:]
    return [(r, wrap(r)) for r in res]


def ts_ass(t):
    h, r = divmod(t, 3600); m, s = divmod(r, 60)
    return f'{int(h)}:{int(m):02d}:{s:05.2f}'


def ts_srt(t):
    ms = int(round(t * 1000)); h, ms = divmod(ms, 3600000); m, ms = divmod(ms, 60000); s, ms = divmod(ms, 1000)
    return f'{h:02d}:{m:02d}:{s:02d},{ms:03d}'


def run(code):
    meta = json.load(open(os.path.join(HERE, 'vo_mk', code, 'meta.json')))
    vo = np.zeros(N)
    events = []
    for i, m in enumerate(meta):
        c, _ = sf.read(os.path.join(HERE, 'vo_mk', code, f'l{i:02d}.wav'))
        act = np.abs(c) > 0.05 * np.max(np.abs(c))
        c = c / np.sqrt(np.mean(c[act] ** 2)) * 0.16
        fi, fo = int(0.02 * SR), int(0.12 * SR)
        c[:fi] *= np.linspace(0, 1, fi); c[-fo:] *= np.linspace(1, 0, fo)
        a = int(m['start'] * SR)
        vo[a:a + len(c)] += c[: N - a]
        # Sous-titres : frontières calées sur les pauses les plus proches de l'estimation au prorata des caractères.
        ch = chunks(m['sub'])
        total = sum(len(r) for r, _ in ch)
        ps = pauses(c)
        t0, acc = 0.0, 0
        for k, (raw, txt) in enumerate(ch):
            acc += len(raw)
            if k == len(ch) - 1:
                t1 = m['dur'] - 0.12
            else:
                est = m['dur'] * acc / total
                near = [p for p in ps if abs(p - est) < 0.45]
                t1 = min(near, key=lambda p: abs(p - est)) if near else est
            events.append([m['start'] + t0, m['start'] + t1, txt])
            t0 = t1
    # Lisibilité : durée mini, pas de clignotement entre deux sous-titres proches.
    for k, e in enumerate(events):
        e[1] = max(e[1] + 0.15, e[0] + 0.9)
        if k + 1 < len(events):
            nxt = events[k + 1][0]
            if nxt - e[1] < 0.3:
                e[1] = nxt - 0.04
    vo = chain(vo)
    pk = maximum_filter1d(np.abs(vo), 145)
    vo = vo * uniform_filter1d(np.minimum(1, 0.5 / np.maximum(pk, 1e-9)), 145)
    vo = np.stack([vo, vo], 1)
    music, _ = sf.read(os.path.join(STEMS, 'music_en.wav'))
    sfx, _ = sf.read(os.path.join(STEMS, 'sfx_en.wav'))
    music = np.pad(music[:N], ((0, max(0, N - len(music))), (0, 0))); sfx = np.pad(sfx[:N], ((0, max(0, N - len(sfx))), (0, 0)))
    on = (signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(vo[:, 0])) > 0.01).astype(float)
    on = maximum_filter1d(on, int(0.35 * SR))
    att = np.clip(signal.filtfilt(*signal.butter(1, 3 / (SR / 2)), on), 0, 1)
    bed = music * 0.8 * (1 - 0.72 * att)[:, None] + sfx * 1.5 * (1 - 0.6 * att)[:, None]
    mix = (bed + vo * 1.4)[: int(END * SR)]
    t = np.arange(len(mix)) / SR
    fade = np.ones(len(mix)); fade[t > END - 0.75] = np.clip(1 - (t[t > END - 0.75] - (END - 0.75)) / 0.75, 0, 1)
    mix *= fade[:, None]
    tmp = os.path.join(STEMS, f'mk_{code}_raw.wav')
    sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, f'mk_{code}.wav')], check=True)
    # Fichiers de sous-titres.
    os.makedirs(SUBS, exist_ok=True)
    head = """[Script Info]
ScriptType: v4.00+
PlayResX: 1920
PlayResY: 1080
WrapStyle: 2
ScaledBorderAndShadow: yes

[V4+ Styles]
Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
Style: Sub,Montserrat SemiBold,44,&H00FFFFFF,&H00FFFFFF,&H40462402,&H00000000,0,0,0,0,100,100,0.3,0,3,16,0,2,160,160,64,1

[Events]
Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text
"""
    with open(os.path.join(SUBS, f'{code}.ass'), 'w', encoding='utf-8') as f:
        f.write(head)
        for a, b, txt in events:
            f.write(f'Dialogue: 0,{ts_ass(a)},{ts_ass(b)},Sub,,0,0,0,,{{\\fad(120,120)}}{txt}\n')
    with open(os.path.join(SUBS, f'{code}.srt'), 'w', encoding='utf-8') as f:
        for k, (a, b, txt) in enumerate(events, 1):
            f.write(f'{k}\n{ts_srt(a)} --> {ts_srt(b)}\n{txt.replace(chr(92) + "N", chr(10))}\n\n')
    v = vo[: len(mix), 0] * 1.4; bb = bed[: len(mix)].mean(1)
    m = signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(v)) > 0.05
    print(code, 'mix ok, voix/fond dB', round(20 * np.log10(np.sqrt(np.mean(v[m] ** 2)) / np.sqrt(np.mean(bb[m] ** 2))), 1), 'sous-titres', len(events))


if __name__ == '__main__':
    for c in sys.argv[1:]:
        run(c)

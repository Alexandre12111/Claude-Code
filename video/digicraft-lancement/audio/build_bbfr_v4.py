"""Assemblage de la voix off française dynamique à partir des prises de tts_bbfr_takes.py (aucune génération ici).
Pour chaque réplique :
1. prises valides : aucun mot manquant ni inventé (contrôle Whisper), mot repère présent ;
2. meilleur segment : la réplique est coupée aux points de suspension « … » ; pour chaque segment on garde la prise la plus
   vivante (étendue de hauteur + variation d'intensité), raccord dans le silence de la pause, pause ramenée à 0,32 s ;
3. pauses internes de plus de 0,35 s ramenées à 0,25 s ; débit ajusté vers le débit visé (syllabes par seconde) ;
4. appuis sur les mots clés (+4 dB, présence), écho ou réverbération sur les phrases fortes ;
5. contrôle final : la réplique traitée (débit, effets) doit être comprise sans faute par Whisper, sinon prise suivante ;
6. calage : le mot repère tombe à l'instant où son texte apparaît à l'écran.
Sortie : vo_bbfr_v4/ (lNN.wav + meta.json lu par mix_bbfr.py). Usage : python3 build_bbfr_v4.py"""
import glob, json, os, re, subprocess, sys, unicodedata
import numpy as np, soundfile as sf, whisper
HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import warp_bbfr as W
from script_bbfr_v4 import L

TAKES, OUT = os.path.join(HERE, 'vo_bbfr_takes'), os.path.join(HERE, 'vo_bbfr_v4')
HOMO = {'aux', 'donnons', 'plateformes', 'pretes', 'creee', 'faites', 'planifiez', 'personnalisez'}
norm = lambda s: re.sub(r'[^a-z ]', ' ', unicodedata.normalize('NFKD', s.lower()).encode('ascii', 'ignore').decode()).split()
nw = lambda w: ''.join(norm(w))
syll = lambda s: sum(len(re.findall(r'[aeiouy]+', w)) for w in norm(s))


def liveliness(x, sr):
    """Étendue de hauteur (demi-tons, déciles 10-90) + variation d'intensité (écart type en dB des trames parlées)."""
    f, n, hop, lo, hi = [], int(0.04 * sr), int(0.01 * sr), int(sr / 260), int(sr / 70)
    db = []
    for i in range(0, len(x) - n, hop):
        s = x[i:i + n] - x[i:i + n].mean(); e = np.dot(s, s)
        if e < 1e-4 * n: continue
        db.append(10 * np.log10(e / n + 1e-12))
        c = np.correlate(s, s, 'full')[n - 1:]; k = lo + int(np.argmax(c[lo:hi]))
        if c[k] / e > 0.45: f.append(sr / k)
    if len(f) < 8: return 0.0
    st = 12 * np.log2(np.array(f))
    return float(np.percentile(st, 90) - np.percentile(st, 10)) + 0.5 * float(np.std(db))


def fft_filter(x, sr, fn):
    X = np.fft.rfft(x); f = np.fft.rfftfreq(len(x), 1 / sr); return np.fft.irfft(X * fn(f), len(x))


def reverb(x, sr, rt, wet):
    rng = np.random.default_rng(5); n = int(rt * sr); t = np.arange(n) / sr
    ir = rng.normal(0, 1, n) * np.exp(-t * 6.9 / rt); ir[: int(0.015 * sr)] = 0
    ir = fft_filter(ir, sr, lambda f: 1 / np.sqrt(1 + (f / 5000) ** 4)); ir /= np.sqrt(np.sum(ir ** 2))
    return x + wet * np.convolve(x, ir)[: len(x)]


def tempo(x, sr, k):
    if abs(k - 1) < 0.005: return x
    a, b = os.path.join(OUT, '_a.wav'), os.path.join(OUT, '_b.wav')
    sf.write(a, x, sr)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', a, '-af', f'atempo={k:.4f}', b], check=True)
    y, _ = sf.read(b); os.remove(a); os.remove(b); return y


def load(i):
    out = []
    for j in sorted(glob.glob(f'{TAKES}/l{i:02d}_s*.json')):
        d = json.load(open(j, encoding='utf-8'))
        x, sr = sf.read(j[:-5] + '.wav')
        out.append((d, x, sr))
    return out


def valid(d, line):
    ref, got = norm(line['text'].replace('-', ' ')), norm(d['heard'].replace('-', ' '))
    miss = [m for m in ref if m not in set(got) and len(m) > 2 and m not in HOMO]
    ws = [nw(w['w']) for w in d['words']]
    anchor_ok = line['anchor'] is None or any(line['anchor'] in w for w in ws)
    return not miss and len(got) - len(ref) <= 1 and anchor_ok


def segments(line, d):
    """Points de coupe aux « … » : (fin du mot avant la pause, début du mot après) dans la prise, ou None si absents."""
    parts = line['text'].split('…')
    cuts, n = [], 0
    for p in parts[:-1]:
        n += len(norm(p.replace('-', ' ')))
        ws = d['words']
        # Whisper découpe parfois autrement (tirets, élisions) : on cherche le mot de fin de segment par son texte.
        last = norm(p.replace('-', ' '))[-1]
        k = next((j for j, w in enumerate(ws) if last in nw(w['w']) and j + 1 < len(ws)), None)
        if k is None or ws[k + 1]['a'] - ws[k]['b'] < 0.1: return None
        cuts.append((ws[k]['b'], ws[k + 1]['a']))
    return cuts


def assemble(line, takes):
    """Meilleur segment par segment, raccords dans les pauses. Renvoie (audio, liste de mots horodatés)."""
    sr = takes[0][2]
    segs = [(d, x, segments(line, d)) for d, x, _ in takes]
    usable = [s for s in segs if s[2] is not None]
    nseg = line['text'].count('…') + 1
    if nseg == 1 or len(usable) < 2:
        d, x = max(((d, x) for d, x, _ in segs), key=lambda t: liveliness(t[1], sr))
        return x, [(nw(w['w']), w['a'], w['b']) for w in d['words']], [d['seed']]
    pieces, words, seeds, t = [], [], [], 0.0
    lvl = np.median([np.sqrt(np.mean(x ** 2)) for _, x, _ in usable])
    for k in range(nseg):
        best = None
        for d, x, cuts in usable:
            a = 0.0 if k == 0 else cuts[k - 1][1] - 0.16
            b = len(x) / sr if k == nseg - 1 else cuts[k][0] + 0.16
            seg = x[int(max(0, a) * sr): int(b * sr)]
            sc = liveliness(seg, sr)
            if best is None or sc > best[0]: best = (sc, d, seg, max(0, a), b)
        _, d, seg, a, b = best
        seg = seg * (lvl / max(1e-6, np.sqrt(np.mean(seg ** 2))))
        f = int(0.03 * sr)
        if pieces: seg = seg.copy(); seg[:f] *= np.linspace(0, 1, f); pieces[-1][-f:] *= np.linspace(1, 0, f)
        pieces.append(seg.copy())
        words += [(nw(w['w']), w['a'] - a + t, w['b'] - a + t) for w in d['words'] if w['a'] >= a - 0.02 and w['b'] <= b + 0.02]
        t += len(seg) / sr; seeds.append(d['seed'])
    return np.concatenate(pieces), words, seeds


def squeeze(x, sr, words, keep_after):
    """Pauses internes > 0,35 s ramenées à 0,25 s (sauf après « … », gérées à l'assemblage)."""
    out, pos, shift, W2 = [], 0, 0.0, []
    for j, (w, a, b) in enumerate(words):
        W2.append((w, a - shift, b - shift))
        if j + 1 < len(words):
            gap = words[j + 1][1] - b
            if gap > 0.35 and j not in keep_after:
                cut_a, cut_b = int((b + 0.125) * sr), int((words[j + 1][1] - 0.125) * sr)
                seg = x[pos:cut_a].copy(); f = int(0.015 * sr)
                if len(seg) > f: seg[-f:] *= np.linspace(1, 0, f)
                out.append(seg); pos = cut_b; shift += (cut_b - cut_a) / sr
    out.append(x[pos:])
    return np.concatenate(out), W2


def effects(x, sr, words, emph, fx):
    gain, pres = np.ones(len(x)), np.zeros(len(x))
    for wd, a, b in words:
        if any(k in wd for k in emph):
            ia, ib = int(max(0, a - 0.03) * sr), int(min(len(x) / sr, b + 0.05) * sr)
            gain[ia:ib] = 10 ** (4 / 20); pres[ia:ib] = 1
    k = int(0.04 * sr); ker = np.ones(k) / k
    gain, pres = np.convolve(gain, ker, 'same'), np.convolve(pres, ker, 'same')
    y = x * gain + 0.45 * fft_filter(x, sr, lambda f: ((f > 2000) & (f < 5000)).astype(float)) * pres
    if fx:
        y = np.concatenate([y, np.zeros(int((0.6 if fx == 'echo' else 0.5) * sr))])
        if fx == 'echo' and words:
            a = int(words[-1][1] * sr); last = np.zeros(len(y)); last[a:len(x)] = y[a:len(x)]
            for dly, g in ((0.2, 0.32), (0.4, 0.16)):
                d = int(dly * sr); e = np.zeros(len(y)); e[d:] = last[:-d]
                y += g * fft_filter(e, sr, lambda f: 1 / np.sqrt(1 + (f / 3500) ** 4))
        y = reverb(y, sr, 1.1 if fx == 'echo' else 0.8, 0.22 if fx == 'echo' else 0.14)
        f = int(0.15 * sr); y[-f:] *= np.linspace(1, 0, f)
    else:
        end = min(len(y), int((words[-1][2] + 0.3) * sr)) if words else len(y)
        y = y[:end]; f = int(0.12 * sr); y[-f:] *= np.linspace(1, 0, f)
    return y / max(1e-9, np.max(np.abs(y))) * 0.9


os.makedirs(OUT, exist_ok=True)
ASR = whisper.load_model('small')
meta, prev_end = [], 0.0
for i, line in enumerate(L):
    takes = [t for t in load(i) if valid(t[0], line)]
    if not takes:
        takes = load(i)[:1]; print(i, 'aucune prise valide, première prise gardée')
    sr = takes[0][2]
    takes.sort(key=lambda t: -liveliness(t[1], t[2]))
    for attempt in range(len(takes)):
        x, words, seeds = assemble(line, takes[attempt:attempt + 1] if attempt else takes)
        # Pauses : celles des « … » sont gardées (0,32 s), les autres resserrées.
        keep, n = set(), 0
        for p in line['text'].split('…')[:-1]:
            n += len(norm(p.replace('-', ' '))); keep.add(n - 1)
        x, words = squeeze(x, sr, words, keep)
        # Débit vers la cible.
        k = 1.0
        if line['rate'] and words:
            rate = syll(line['text']) / max(0.3, words[-1][2] - words[0][1])
            k = float(np.clip(line['rate'] / rate, 0.97, 1.07))  # au-delà de +7 %, la diction se brouille
            x = tempo(x, sr, k); words = [(w, a / k, b / k) for w, a, b in words]
        y = effects(x, sr, words, line['emph'], line['fx'])
        chk = os.path.join(OUT, '_chk.wav'); sf.write(chk, x.astype(np.float32), sr)
        heard = ASR.transcribe(chk, language='fr', fp16=False, initial_prompt='YouCraft')['text'].strip(); os.remove(chk)
        if valid({'heard': heard, 'words': [{'w': w} for w, a, b in words]}, line): break
        print(i, 'prise', seeds, 'mal comprise après traitement :', heard)
    off = next((a for w, a, b in words if line['anchor'] and line['anchor'] in w), words[0][1] if words else 0.0)
    if line['anchor'] is None: off = 0.0
    start = max(0.0, W.fwd(line['t0']) - off, prev_end + 0.08)
    sf.write(f'{OUT}/l{i:02d}.wav', y.astype(np.float32), sr)
    speech_end = start + (words[-1][2] if words else len(y) / sr)
    nxt = W.fwd(L[i + 1]['t0']) if i + 1 < len(L) else W.END
    meta.append({'text': line['text'], 'start': round(start, 3), 'dur': round(len(y) / sr, 2), 'ecran': round(W.fwd(line['t0']), 2), 'repere': line['anchor'],
                 'mot_repere_a': round(start + off, 2) if line['anchor'] else None, 'tempo': round(k, 3), 'prises': seeds, 'vivacite': round(liveliness(x, sr), 1), 'asr': heard})
    print(f"{i:2d} début {start:6.2f} fin parole {speech_end:6.2f} (texte suivant {nxt:6.2f}) tempo {k:.2f} prises {seeds} vivacité {meta[-1]['vivacite']}")
    prev_end = speech_end
json.dump(meta, open(f'{OUT}/meta.json', 'w'), ensure_ascii=False, indent=1)
print('ok')

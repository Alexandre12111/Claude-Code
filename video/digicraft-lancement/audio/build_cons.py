"""Assemblage de la voix off de la vidéo consultants et calcul du minutage de toute la vidéo.
- par réplique : prise valide (aucun mot manquant ni inventé, contrôle Whisper) la plus vivante ; pauses internes resserrées ;
  débit légèrement ajusté (au plus +5 %) ; contrôle final de la version traitée ;
- minutage : répliques enchaînées (0,45 s entre deux répliques d'une même scène, 1,1 s à chaque changement de scène),
  durées minimales pour les plans visuels ; créneaux de scènes écrits dans src_cons/cons_timeline.js ;
- sous-titres : blocs de deux lignes au plus, calés mot à mot, dans cons_timeline.js et en .srt.
Usage : python3 build_cons.py (environnement avec openai-whisper)"""
import glob, json, os, re, subprocess, sys, unicodedata
import numpy as np, soundfile as sf, whisper
HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
from script_cons import L

TAKES, OUT = os.path.join(HERE, 'vo_cons_takes'), os.path.join(HERE, 'vo_cons')
ROOT = os.path.join(HERE, '..')
HOMO = {'aux', 'creee', 'faites', 'applications', 'professionnelles', 'courriels', 'ressaisies', 'deployees', 'hebergees'}
MIN = {'logo': 3.0, 'produit': 7.0, 'cas_intro': 3.2, 'etape3': 6.5, 'preuves': 7.5, 'fin': 6.5, 'vendre': 4.0}
LEAD, GAP, TAIL = 1.1, 0.45, 2.0
norm = lambda s: re.sub(r'[^a-z0-9 ]', ' ', unicodedata.normalize('NFKD', s.lower()).encode('ascii', 'ignore').decode()).split()
nw = lambda w: ''.join(norm(w))
syll = lambda s: sum(len(re.findall(r'[aeiouy]+', w)) for w in norm(s))


def liveliness(x, sr):
    f, n, hop, lo, hi, db = [], int(0.04 * sr), int(0.01 * sr), int(sr / 260), int(sr / 70), []
    for i in range(0, len(x) - n, hop):
        s = x[i:i + n] - x[i:i + n].mean(); e = np.dot(s, s)
        if e < 1e-4 * n: continue
        db.append(10 * np.log10(e / n + 1e-12))
        c = np.correlate(s, s, 'full')[n - 1:]; k = lo + int(np.argmax(c[lo:hi]))
        if c[k] / e > 0.45: f.append(sr / k)
    if len(f) < 8: return 0.0
    st = 12 * np.log2(np.array(f))
    return float(np.percentile(st, 90) - np.percentile(st, 10)) + 0.5 * float(np.std(db))


def valid(text, heard):
    ref, got = norm(text.replace('-', ' ')), norm(heard.replace('-', ' '))
    miss = [m for m in ref if m not in set(got) and len(m) > 2 and m not in HOMO]
    return not miss and len(got) <= len(ref)


def tempo(x, sr, k):
    if abs(k - 1) < 0.005: return x
    a, b = os.path.join(OUT, '_a.wav'), os.path.join(OUT, '_b.wav')
    sf.write(a, x, sr); subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', a, '-af', f'atempo={k:.4f}', b], check=True)
    y, _ = sf.read(b); os.remove(a); os.remove(b); return y


def squeeze(x, sr, words):
    out, pos, shift, W2 = [], 0, 0.0, []
    for j, (w, a, b) in enumerate(words):
        W2.append((w, a - shift, b - shift))
        if j + 1 < len(words) and words[j + 1][1] - b > 0.5:
            ca, cb = int((b + 0.2) * sr), int((words[j + 1][1] - 0.2) * sr)
            seg = x[pos:ca].copy(); f = int(0.015 * sr)
            if len(seg) > f: seg[-f:] *= np.linspace(1, 0, f)
            out.append(seg); pos = cb; shift += (cb - ca) / sr
    out.append(x[pos:])
    return np.concatenate(out), W2


def chunks(sub, maxc=84, line=44):
    """Blocs de sous-titres (deux lignes au plus), coupés aux ponctuations puis aux mots."""
    parts = [p.strip() for p in re.split(r'(?<=[.?!:;,…])\s+', sub) if p.strip()]
    res, cur = [], ''
    for p in parts:
        cand = (cur + ' ' + p).strip()
        if len(cand) <= maxc: cur = cand
        else:
            if cur: res.append(cur)
            while len(p) > maxc:
                k = p.rfind(' ', 0, maxc); res.append(p[:k]); p = p[k + 1:]
            cur = p
    if cur: res.append(cur)
    def wrap(s):
        if len(s) <= line: return s
        sp = [i for i, ch in enumerate(s) if ch == ' ']
        best = min(sp, key=lambda i: abs(i - (len(s) - i)) - (10 if s[i - 1] in ',.:;…' else 0))
        return s[:best] + '<br>' + s[best + 1:]
    return [(r, wrap(r)) for r in res]


os.makedirs(OUT, exist_ok=True)
ASR = whisper.load_model('small')
meta, t, prev_scene = [], 1.0, None
for i, line in enumerate(L):
    takes = []
    for j in sorted(glob.glob(f'{TAKES}/l{i:02d}_s*.json')):
        d = json.load(open(j, encoding='utf-8'))
        if d['text'] != line['text']: continue
        x, sr = sf.read(j[:-5] + '.wav'); takes.append((d, x, sr))
    ok = [tk for tk in takes if valid(line['text'], tk[0]['heard'])] or takes
    ok.sort(key=lambda tk: -liveliness(tk[1], tk[2]))
    for d, x, sr in ok:
        words = [(nw(w['w']), w['a'], w['b']) for w in d['words']]
        x2, w2 = squeeze(x, sr, words)
        rate = syll(line['text']) / max(0.3, w2[-1][2] - w2[0][1])
        k = float(np.clip(6.0 / rate, 0.98, 1.05))
        x2 = tempo(x2, sr, k); w2 = [(w, a / k, b / k) for w, a, b in w2]
        end = min(len(x2), int((w2[-1][2] + 0.3) * sr)); x2 = x2[:end].copy(); f = int(0.12 * sr); x2[-f:] *= np.linspace(1, 0, f)
        chk = os.path.join(OUT, '_chk.wav'); sf.write(chk, x2.astype(np.float32), sr)
        heard = ASR.transcribe(chk, language='fr', fp16=False, initial_prompt='YouCraft')['text'].strip(); os.remove(chk)
        if valid(line['text'], heard): break
        print(i, 'prise', d['seed'], 'mal comprise après traitement :', heard)
    x2 = x2 / max(1e-9, np.max(np.abs(x2))) * 0.9
    sf.write(f'{OUT}/l{i:02d}.wav', x2.astype(np.float32), sr)
    if line['scene'] != prev_scene and i: t += LEAD - GAP
    meta.append({'text': line['text'], 'sub': line['sub'], 'scene': line['scene'], 'start': round(t, 3), 'dur': round(len(x2) / sr, 2), 'seed': d['seed'], 'asr': heard,
                 'words': [[w, round(a, 3), round(b, 3)] for w, a, b in w2]})
    print(f"{i:2d} {line['scene']:10s} {t:6.2f} → {t + len(x2) / sr:6.2f}  prise {d['seed']}  | {heard}")
    t += len(x2) / sr + GAP
    prev_scene = line['scene']
# Créneaux de scènes : du début de leur première réplique (moins l'amorce) au début de la scène suivante, durées minimales.
order = []
for m in meta:
    if m['scene'] not in order: order.append(m['scene'])
first = {s: min(m['start'] for m in meta if m['scene'] == s) for s in order}
last = {s: max(m['start'] + m['dur'] for m in meta if m['scene'] == s) for s in order}
scenes, shift = {}, 0.0
for k, s in enumerate(order):
    a = 0.0 if k == 0 else scenes[order[k - 1]][1]
    b0 = (first[order[k + 1]] - 0.55 + shift) if k + 1 < len(order) else last[s] + shift + TAIL
    if b0 - a < MIN.get(s, 0):
        extra = MIN[s] - (b0 - a); shift += extra; b0 += extra
        for m in meta:
            if order.index(m['scene']) > k: m['start'] = round(m['start'] + extra, 3)
    scenes[s] = [round(a, 3), round(b0, 3)]
END = scenes[order[-1]][1]
# Sous-titres.
subs = []
for m in meta:
    W = m['words']; ch = chunks(m['sub'])
    if m['sub'] == m['text'] and W:
        k0 = 0
        for raw, html in ch:
            n = len(norm(raw.replace('-', ' ')))
            seg = W[k0:k0 + n] or W[-1:]
            subs.append([round(m['start'] + seg[0][1] - 0.05, 3), round(m['start'] + seg[-1][2] + 0.25, 3), html]); k0 += n
    else:
        tot = sum(len(r) for r, _ in ch); acc = 0; a0 = W[0][1] if W else 0; d = (W[-1][2] if W else m['dur']) - a0
        for raw, html in ch:
            b = a0 + d * (acc + len(raw)) / tot
            subs.append([round(m['start'] + a0 + d * acc / tot - 0.05, 3), round(m['start'] + b + 0.25, 3), html]); acc += len(raw)
for k in range(len(subs) - 1):
    subs[k][1] = min(subs[k][1], subs[k + 1][0] - 0.04)
    if subs[k + 1][0] - subs[k][1] < 0.35: subs[k][1] = subs[k + 1][0] - 0.04
json.dump(meta, open(f'{OUT}/meta.json', 'w'), ensure_ascii=False, indent=1)
CTd = {'END': END, 'scenes': scenes, 'lines': [{'scene': m['scene'], 'start': m['start'], 'dur': m['dur'], 'words': [[w, a] for w, a, b in m['words']]} for m in meta], 'subs': subs}
open(os.path.join(ROOT, 'src_cons', 'cons_timeline.js'), 'w', encoding='utf-8').write('// Généré par audio/build_cons.py : minutage des scènes, des répliques et des sous-titres.\nwindow.CT = ' + json.dumps(CTd, ensure_ascii=False) + ';\n')
ts = lambda v: f'{int(v // 3600):02d}:{int(v % 3600 // 60):02d}:{int(v % 60):02d},{int(round(v % 1 * 1000)) % 1000:03d}'
with open(os.path.join(ROOT, 'out', 'YouCraft_Consultants_FR_soustitres.srt'), 'w', encoding='utf-8') as f:
    for k, (a, b, html) in enumerate(subs, 1): f.write(f"{k}\n{ts(a)} --> {ts(b)}\n{html.replace('<br>', chr(10))}\n\n")
print('scènes', {k: v for k, v in scenes.items()})
print('durée totale', round(END, 1), 's')

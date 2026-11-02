"""Effets de voix de « Donnez vie à vos idées » : appui sur les mots clés et écho sur les phrases fortes.
- mots clés (repérés mot à mot par Whisper) : +3 dB et un peu de présence (2 à 5 kHz), fondus de 40 ms ;
- phrases fortes (« Voici YouCraft ! », « prêt à être lancé ! », « Donnons-lui vie ! ») : écho filtré sur le dernier mot
  et courte queue de réverbération ; « Vous l'imaginez ? » : réverbération légère.
Entrée vo_bbfr_expr_brut/, sortie vo_bbfr_expr/ (même meta.json, durées mises à jour).
Usage : python3 vo_fx_bbfr.py (environnement avec openai-whisper)"""
import json, os, re, shutil, unicodedata
import numpy as np, soundfile as sf, whisper

HERE = os.path.dirname(os.path.abspath(__file__))
SRC, DST = os.path.join(HERE, 'vo_bbfr_expr_brut'), os.path.join(HERE, 'vo_bbfr_expr')
EMPH = {0: ['imaginez', 'application'], 1: ['equipe'], 2: ['processus'], 3: ['echelle', 'simplement'], 4: ['youcraft'],
        5: ['complete', 'intelligence', 'artificielle', 'idee'], 6: ['forme'], 7: ['toutes', 'directions'], 8: ['toute', 'entreprise'],
        9: ['rapides', 'entreprise'], 10: ['integre', 'lance'], 11: ['nouvelle', 'loin'], 12: ['imaginez'], 13: ['vie']}
THROW = {4: 0.55, 10: 0.45, 13: 0.8}   # écho + réverbération (longueur de queue en s)
VERB = {12: 0.5}                        # réverbération légère seule
norm = lambda s: re.sub(r'[^a-z]', '', unicodedata.normalize('NFKD', s.lower()).encode('ascii', 'ignore').decode())


def biquad_band(x, sr, lo, hi):
    """Passe-bande simple par FFT (pas de dépendance à scipy)."""
    X = np.fft.rfft(x); f = np.fft.rfftfreq(len(x), 1 / sr)
    X[(f < lo) | (f > hi)] = 0
    return np.fft.irfft(X, len(x))


def lowpass(x, sr, fc):
    X = np.fft.rfft(x); f = np.fft.rfftfreq(len(x), 1 / sr)
    X *= 1 / np.sqrt(1 + (f / fc) ** 4)
    return np.fft.irfft(X, len(x))


def reverb(x, sr, rt, wet):
    rng = np.random.default_rng(5)
    n = int(rt * sr); t = np.arange(n) / sr
    ir = rng.normal(0, 1, n) * np.exp(-t * 6.9 / rt); ir[: int(0.015 * sr)] = 0
    ir = lowpass(ir, sr, 5000); ir /= np.sqrt(np.sum(ir ** 2))
    y = np.convolve(x, ir)[: len(x)]
    return x + wet * y


asr = whisper.load_model('small')
os.makedirs(DST, exist_ok=True)
meta = json.load(open(os.path.join(SRC, 'meta.json'), encoding='utf-8'))
for i, m in enumerate(meta):
    x, sr = sf.read(os.path.join(SRC, f'l{i:02d}.wav'))
    r = asr.transcribe(os.path.join(SRC, f'l{i:02d}.wav'), language='fr', fp16=False, word_timestamps=True, initial_prompt='YouCraft')
    words = [(norm(w['word']), w['start'], w['end']) for s in r['segments'] for w in s['words']]
    gain = np.ones(len(x)); pres = np.zeros(len(x))
    hit = []
    for wd, a, b in words:
        if any(k in wd for k in EMPH.get(i, [])):
            ia, ib = int(max(0, a - 0.03) * sr), int(min(len(x) / sr, b + 0.05) * sr)
            gain[ia:ib] = np.maximum(gain[ia:ib], 10 ** (3 / 20)); pres[ia:ib] = 1; hit.append(wd)
    k = int(0.04 * sr); ker = np.ones(k) / k
    gain = np.convolve(gain, ker, 'same'); pres = np.convolve(pres, ker, 'same')
    y = x * gain + 0.35 * biquad_band(x, sr, 2000, 5000) * pres
    if i in THROW or i in VERB:
        tail = THROW.get(i, VERB.get(i))
        y = np.concatenate([y, np.zeros(int(tail * sr))])
        if i in THROW and words:
            a = int(words[-1][1] * sr)
            last = np.zeros(len(y)); last[a:len(x)] = y[a:len(x)]
            for dly, g in ((0.21, 0.30), (0.42, 0.15)):
                d = int(dly * sr); e = np.zeros(len(y)); e[d:] = last[:-d]
                y += g * lowpass(e, sr, 3500)
        y = reverb(y, sr, 1.1 if i in THROW else 0.8, 0.22 if i in THROW else 0.14)
        f = int(0.15 * sr); y[-f:] *= np.linspace(1, 0, f)
    y *= 0.95 / max(1e-9, np.max(np.abs(y))) * min(1.0, np.max(np.abs(x)) / 0.95 * 1.12)
    sf.write(os.path.join(DST, f'l{i:02d}.wav'), y.astype(np.float32), sr)
    m['dur'] = round(len(y) / sr, 2); m['appuis'] = hit
    print(i, 'appuis', hit, 'écho' if i in THROW else 'réverb' if i in VERB else '', flush=True)
json.dump(meta, open(os.path.join(DST, 'meta.json'), 'w'), ensure_ascii=False, indent=1)
print('ok')

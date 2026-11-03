"""Voix off française dynamique et synchronisée de « Donnez vie à vos idées » (YouCraft, 79 s).
- Chatterbox Multilingual, timbre « pierre » (voix masculine française de synthèse, Piper fr_FR-upmc) ;
- texte plus rythmé, une direction de jeu par réplique (expressivité, guidage, accélération légère) ;
- synchronisation : chaque réplique est calée pour que son mot repère tombe à l'instant où le texte correspondant
  apparaît à l'écran (horodatage mot à mot par Whisper, temps de l'image d'origine passés par warp_bbfr.fwd) ;
- choix de la prise : bien prononcée (aucun mot manquant ni inventé), tient avant la réplique suivante, la plus mélodique ;
- effets : appui sur les mots clés (+4 dB, présence), écho et réverbération sur les phrases fortes.
Sortie : vo_bbfr_sync/ (lNN.wav + meta.json lu par mix_bbfr.py). Usage : python3 tts_bbfr_sync.py"""
import json, os, re, subprocess, sys, unicodedata
import numpy as np, soundfile as sf, torch, torchaudio as ta, whisper
from chatterbox.mtl_tts import ChatterboxMultilingualTTS
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import warp_bbfr as W

HERE = os.path.dirname(os.path.abspath(__file__))
REF = os.path.join(HERE, 'vo_ref', 'ref_homme_piper_upmc_pierre.wav')
OUT = os.path.join(HERE, 'vo_bbfr_sync')
# (texte, instant d'apparition à l'écran dans le film d'origine, mot repère ou None = début de phrase,
#  expressivité, guidage, accélération, mots appuyés, effet)
L = [
    ("Imaginez… pouvoir créer n'importe quelle application,", 1.62, 'importe', 0.85, 0.35, 1.0, ['imaginez', 'application'], None),
    ("Pour n'importe quelle équipe…", 3.45, None, 0.85, 0.35, 1.04, ['equipe'], None),
    ("pour n'importe quel processus…", 4.85, None, 0.9, 0.35, 1.04, ['processus'], None),
    ("à n'importe quelle échelle !", 6.4, None, 1.0, 0.32, 1.04, ['echelle'], None),
    ("Simplement… en la décrivant.", 7.82, 'simplement', 0.8, 0.35, 1.0, ['simplement', 'decrivant'], None),
    ("Voici… YouCraft !", 11.1, 'youcraft', 1.15, 0.3, 1.0, ['youcraft'], 'echo'),
    ("Une plateforme complète de création par l'intelligence artificielle, pensée pour les entreprises !", 12.65, None, 0.85, 0.4, 1.06, ['complete', 'intelligence', 'artificielle', 'entreprises'], None),
    ("Partez d'une idée… ou d'un modèle.", 16.95, None, 0.9, 0.35, 1.04, ['idee', 'modele'], None),
    ("Décrivez votre besoin… et regardez-le prendre forme !", 18.45, None, 0.9, 0.35, 1.05, ['decrivez', 'forme'], None),
    ("Créée en quelques minutes !", 22.45, None, 1.05, 0.32, 1.04, ['minutes'], None),
    ("Planifiez, personnalisez, faites-la évoluer… dans toutes les directions !", 23.45, None, 0.95, 0.35, 1.06, ['planifiez', 'personnalisez', 'directions'], None),
    ("Demandez… c'est prêt !", 26.45, 'demandez', 1.05, 0.32, 1.0, ['pret'], None),
    ("Des outils conçus pour une équipe… jusqu'aux plateformes prêtes pour toute l'entreprise.", 30.0, None, 0.9, 0.38, 1.06, ['toute', 'entreprise'], None),
    ("Des gains rapides… aux applications de niveau entreprise !", 35.9, 'niveau', 0.95, 0.35, 1.05, ['rapides', 'niveau', 'entreprise'], None),
    ("Tout est intégré, sécurisé par conception, et prêt à être lancé !", 38.15, 'securise', 1.0, 0.33, 1.05, ['integre', 'securise', 'lance'], 'echo'),
    ("C'est une nouvelle façon de travailler… où vos équipes vont aussi loin que leurs idées !", 41.0, None, 0.95, 0.35, 1.05, ['nouvelle', 'loin', 'idees'], None),
    ("Vous l'imaginez ?", 46.35, None, 1.0, 0.32, 1.0, ['imaginez'], 'verb'),
    ("Donnons-lui vie !", 48.45, None, 1.15, 0.3, 1.0, ['vie'], 'echo'),
    ("Demandez votre démo.", 50.4, None, 0.85, 0.35, 1.0, ['demo'], None),
]
HOMO = {'aux', 'donnons', 'plateformes', 'pretes', 'creee', 'faites'}
norm = lambda s: re.sub(r'[^a-z ]', ' ', unicodedata.normalize('NFKD', s.lower()).encode('ascii', 'ignore').decode()).split()
nw = lambda w: ''.join(norm(w))


def melody(x, sr):
    f, n, hop, lo, hi = [], int(0.04 * sr), int(0.01 * sr), int(sr / 260), int(sr / 70)
    for i in range(0, len(x) - n, hop):
        s = x[i:i + n] - x[i:i + n].mean(); e = np.dot(s, s)
        if e < 1e-4 * n: continue
        c = np.correlate(s, s, 'full')[n - 1:]; k = lo + int(np.argmax(c[lo:hi]))
        if c[k] / e > 0.45: f.append(sr / k)
    if len(f) < 10: return 0.0
    st = 12 * np.log2(np.array(f)); return float(np.percentile(st, 90) - np.percentile(st, 10))


def fft_filter(x, sr, fn):
    X = np.fft.rfft(x); f = np.fft.rfftfreq(len(x), 1 / sr); return np.fft.irfft(X * fn(f), len(x))


def reverb(x, sr, rt, wet):
    rng = np.random.default_rng(5); n = int(rt * sr); t = np.arange(n) / sr
    ir = rng.normal(0, 1, n) * np.exp(-t * 6.9 / rt); ir[: int(0.015 * sr)] = 0
    ir = fft_filter(ir, sr, lambda f: 1 / np.sqrt(1 + (f / 5000) ** 4)); ir /= np.sqrt(np.sum(ir ** 2))
    return x + wet * np.convolve(x, ir)[: len(x)]


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
        tail = 0.6 if fx == 'echo' else 0.5
        y = np.concatenate([y, np.zeros(int(tail * sr))])
        if fx == 'echo' and words:
            a = int(words[-1][1] * sr); last = np.zeros(len(y)); last[a:len(x)] = y[a:len(x)]
            for dly, g in ((0.2, 0.32), (0.4, 0.16)):
                d = int(dly * sr); e = np.zeros(len(y)); e[d:] = last[:-d]
                y += g * fft_filter(e, sr, lambda f: 1 / np.sqrt(1 + (f / 3500) ** 4))
        y = reverb(y, sr, 1.1 if fx == 'echo' else 0.8, 0.22 if fx == 'echo' else 0.14)
        f = int(0.15 * sr); y[-f:] *= np.linspace(1, 0, f)
    return y / max(1e-9, np.max(np.abs(y))) * min(0.95, np.max(np.abs(x)) * 1.12)


def tempo(x, sr, k, tmp):
    if abs(k - 1) < 1e-3: return x
    sf.write(tmp + '_a.wav', x, sr)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp + '_a.wav', '-af', f'atempo={k}', tmp + '_b.wav'], check=True)
    y, _ = sf.read(tmp + '_b.wav'); return y


tts = ChatterboxMultilingualTTS.from_pretrained(device='cpu')
asr = whisper.load_model('small')
os.makedirs(OUT, exist_ok=True)
tmp = os.path.join(OUT, 'tmp')
meta, prev_end = [], 0.0
for i, (txt, t0, anchor, ex, cfg, spd, emph, fx) in enumerate(L):
    nxt = W.fwd(L[i + 1][1]) - 0.15 if i + 1 < len(L) else W.END - 0.8
    takes = []
    for seed in (11, 23, 37, 51, 64):
        if seed > 37 and any(t[0] for t in takes): break
        torch.manual_seed(seed)
        w = tts.generate(txt, language_id='fr', audio_prompt_path=REF, exaggeration=ex, cfg_weight=cfg)
        x = w[0].numpy().astype(np.float64)
        env = np.abs(x); idx = np.where(env > 0.02 * env.max())[0]
        x = x[max(0, idx[0] - int(0.03 * tts.sr)): min(len(x), idx[-1] + int(0.2 * tts.sr))]
        x = tempo(x, tts.sr, spd, tmp)
        sf.write(tmp + '.wav', x, tts.sr)
        r = asr.transcribe(tmp + '.wav', language='fr', fp16=False, word_timestamps=True, initial_prompt='YouCraft')
        heard = r['text'].strip()
        words = [(nw(wd['word']), wd['start'], wd['end']) for s in r['segments'] for wd in s['words']]
        ref = norm(txt.replace('-', ' ')); got = norm(heard.replace('-', ' '))
        miss = [m for m in ref if m not in set(got) and len(m) > 2 and m not in HOMO]
        extra = len(got) - len(ref)
        hit = [a for wd, a, b in words if anchor and anchor in wd]
        off = hit[0] if hit else 0.0
        start = W.fwd(t0) - off
        y = effects(x, tts.sr, words, emph, fx)
        d = len(x) / tts.sr
        fits = start >= prev_end + 0.08 and start + d <= nxt
        ok = not miss and extra <= 1 and (anchor is None or bool(hit))
        takes.append((ok, fits, melody(x, tts.sr), seed, y, heard, start, d))
        print(i, 'graine', seed, f'début {start:.2f} fin {start + d:.2f} (avant {nxt:.2f})', 'ok' if ok else f'rejet {miss} +{extra}', 'tient' if fits else 'déborde', '|', heard, flush=True)
    good = [t for t in takes if t[0] and t[1]] or [t for t in takes if t[0]] or takes
    ok, fits, mel, seed, y, heard, start, d = max(good, key=lambda t: t[2])
    if start < prev_end + 0.08:
        start = prev_end + 0.08   # recalage minimal si la réplique précédente déborde
    sf.write(f'{OUT}/l{i:02d}.wav', y.astype(np.float32), tts.sr)
    meta.append({'text': txt, 'start': round(max(0.0, start), 3), 'dur': round(len(y) / tts.sr, 2), 'ecran': round(W.fwd(t0), 2), 'repere': anchor, 'seed': seed, 'melodie_dt': round(mel, 1), 'asr': heard})
    prev_end = start + d
for f in ('.wav', '_a.wav', '_b.wav'):
    if os.path.exists(tmp + f): os.remove(tmp + f)
json.dump(meta, open(f'{OUT}/meta.json', 'w'), ensure_ascii=False, indent=1)
print('ok')

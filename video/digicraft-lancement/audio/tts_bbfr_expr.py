"""Voix off française expressive de « Donnez vie à vos idées » : Chatterbox Multilingual, timbre « pierre » (Piper, voix
masculine française de synthèse). Chaque réplique a sa direction de jeu (ponctuation, expressivité, rythme). Pour chacune,
plusieurs prises sont générées ; on garde, parmi celles qui sont bien prononcées (contrôle Whisper) et tiennent dans leur
créneau, la plus mélodique (plus grande étendue de hauteur, en demi-tons). Sortie : vo_bbfr_expr_brut/ et meta.json.
Usage : python3 tts_bbfr_expr.py [n° de répliques à refaire] (environnement avec chatterbox-tts et openai-whisper)"""
import json, os, re, sys, unicodedata
import numpy as np, torch, torchaudio as ta, whisper
from chatterbox.mtl_tts import ChatterboxMultilingualTTS
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import warp_bbfr as W

HERE = os.path.dirname(os.path.abspath(__file__))
REF = os.path.join(HERE, 'vo_ref', 'ref_homme_piper_upmc_pierre.wav')
STARTS0 = [0.30, 3.30, 4.75, 6.35, 10.55, 12.6, 19.9, 23.6, 30.0, 34.7, 37.9, 41.0, 46.3, 48.4]
# (texte joué, expressivité, guidage : plus bas = débit plus libre et plus joué)
DIR = [
    ("Imaginez… pouvoir créer n'importe quelle application,", 0.75, 0.4),          # intrigue
    ("pour n'importe quelle équipe,", 0.7, 0.45),                                   # montée en trois temps
    ("pour n'importe quel processus,", 0.8, 0.42),
    ("à n'importe quelle échelle. Simplement… en la décrivant.", 0.85, 0.38),      # sommet puis confidence
    ("Voici YouCraft !", 1.05, 0.3),                                                # révélation
    ("Une plateforme complète de création par l'intelligence artificielle, pensée pour les entreprises qui ont une idée… et l'envie de la concrétiser.", 0.7, 0.45),
    ("Décrivez votre besoin, et regardez-le prendre forme.", 0.75, 0.4),
    ("Vous pouvez planifier, personnaliser, et faire évoluer votre application, dans toutes les directions !", 0.85, 0.4),
    ("Des outils conçus pour une équipe, jusqu'aux plateformes prêtes pour toute l'entreprise.", 0.8, 0.42),
    ("Des gains rapides, aux applications de niveau entreprise,", 0.85, 0.4),
    ("tout est intégré, et prêt à être lancé !", 0.95, 0.35),                       # affirmation
    ("C'est une nouvelle façon de travailler, où vos équipes vont aussi loin… que leurs idées.", 0.85, 0.4),
    ("Vous l'imaginez ?", 0.95, 0.35),                                              # question qui monte
    ("Donnons-lui vie !", 1.15, 0.3),                                               # final
]
HOMO = {'aux', 'donnons', 'plateformes', 'pretes'}  # pluriels muets et homophones : écrits autrement, prononcés pareil
norm = lambda s: re.sub(r'[^a-z ]', ' ', unicodedata.normalize('NFKD', s.lower()).encode('ascii', 'ignore').decode()).split()


def melody(x, sr):
    """Étendue de hauteur (demi-tons entre les déciles 10 et 90 des trames voisées)."""
    f = []
    n, hop = int(0.04 * sr), int(0.01 * sr)
    lo, hi = int(sr / 260), int(sr / 70)
    for i in range(0, len(x) - n, hop):
        s = x[i:i + n] - x[i:i + n].mean()
        e = np.dot(s, s)
        if e < 1e-4 * n: continue
        c = np.correlate(s, s, 'full')[n - 1:]
        k = lo + int(np.argmax(c[lo:hi]))
        if c[k] / e > 0.45: f.append(sr / k)
    if len(f) < 10: return 0.0
    st = 12 * np.log2(np.array(f))
    return float(np.percentile(st, 90) - np.percentile(st, 10))


tts = ChatterboxMultilingualTTS.from_pretrained(device='cpu')
asr = whisper.load_model('small')
OUT = os.path.join(HERE, 'vo_bbfr_expr_brut'); os.makedirs(OUT, exist_ok=True)
starts = [round(W.fwd(t), 3) for t in STARTS0]
ONLY = [int(a) for a in sys.argv[1:]]
meta = json.load(open(f'{OUT}/meta.json', encoding='utf-8')) if ONLY else []
for i, (txt, ex, cfg) in enumerate(DIR):
    if ONLY and i not in ONLY:
        continue
    limit = (starts[i + 1] if i + 1 < len(starts) else W.END - 1.0) - starts[i] - 0.12
    takes = []
    for seed in (11, 23, 37, 51, 64):
        if seed > 37 and any(t[0] for t in takes): break
        torch.manual_seed(seed)
        w = tts.generate(txt, language_id='fr', audio_prompt_path=REF, exaggeration=ex, cfg_weight=cfg)
        x = w[0].numpy()
        env = np.abs(x); idx = np.where(env > 0.02 * env.max())[0]
        x = x[max(0, idx[0] - int(0.03 * tts.sr)): min(len(x), idx[-1] + int(0.25 * tts.sr))]
        p = f'{OUT}/tmp.wav'; ta.save(p, torch.from_numpy(x)[None], tts.sr)
        heard = asr.transcribe(p, language='fr', fp16=False, initial_prompt='YouCraft')['text'].strip()
        miss = [m for m in norm(txt.replace('-', ' ')) if m not in set(norm(heard.replace('-', ' '))) and len(m) > 2 and m not in HOMO]
        d, mel = len(x) / tts.sr, melody(x, tts.sr)
        # Mots en trop : la prise a inventé une suite (hallucination).
        extra = len(norm(heard.replace('-', ' '))) - len(norm(txt.replace('-', ' ')))
        ok = not miss and extra <= 1 and d <= limit
        takes.append((ok, mel, seed, x, heard, d))
        print(i, 'graine', seed, f'{d:.2f}s / {limit:.2f}s', f'mélodie {mel:.1f} dt', 'OK' if ok else f'rejet {miss}', '|', heard, flush=True)
    good = [t for t in takes if t[0]] or sorted(takes, key=lambda t: t[5])[:1]
    ok, mel, seed, x, heard, d = max(good, key=lambda t: t[1])
    ta.save(f'{OUT}/l{i:02d}.wav', torch.from_numpy(x)[None], tts.sr)
    entry = {'text': txt, 'start': starts[i], 'dur': round(d, 2), 'seed': seed, 'melodie_dt': round(mel, 1), 'asr': heard}
    if ONLY: meta[i] = entry
    else: meta.append(entry)
if os.path.exists(f'{OUT}/tmp.wav'): os.remove(f'{OUT}/tmp.wav')
json.dump(meta, open(f'{OUT}/meta.json', 'w'), ensure_ascii=False, indent=1)
print('ok')

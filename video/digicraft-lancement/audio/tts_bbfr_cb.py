"""Voix off française de « Donnez vie à vos idées » avec Chatterbox Multilingual (Resemble AI, licence MIT) :
diction et intonation françaises naturelles. Le timbre vient d'une voix de synthèse de référence (aucune voix réelle clonée) :
homme = voix Kokoro du film anglais (am_michael), femme = voix Kokoro ff_siwis (corpus SIWIS sous licence libre).
Chaque réplique est vérifiée par reconnaissance vocale (Whisper) et régénérée si un mot manque.
Usage : python3 tts_bbfr_cb.py homme|femme   (dans un environnement où chatterbox-tts et openai-whisper sont installés)"""
import json, os, re, sys, unicodedata
import numpy as np, torch, torchaudio as ta, whisper
from chatterbox.mtl_tts import ChatterboxMultilingualTTS
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import warp_bbfr as W

HERE = os.path.dirname(os.path.abspath(__file__))
VOICE = sys.argv[1]
REF = os.path.join(HERE, 'vo_ref', {'homme': 'ref_homme_kokoro_am_michael.wav', 'femme': 'ref_femme_kokoro_ff_siwis.wav'}[VOICE])
STARTS0 = [0.30, 3.30, 4.75, 6.35, 10.55, 12.6, 19.9, 23.6, 30.0, 34.7, 37.9, 41.0, 46.3, 48.4]
LINES = [
    "Imaginez pouvoir créer n'importe quelle application,",
    "pour n'importe quelle équipe,",
    "pour n'importe quel processus,",
    "à n'importe quelle échelle. Simplement en la décrivant.",
    "Voici YouCraft.",
    "Une plateforme complète de création par l'intelligence artificielle, pensée pour les entreprises qui ont une idée, et l'envie de la concrétiser.",
    "Décrivez votre besoin, et regardez-le prendre forme.",
    "Vous pouvez planifier, personnaliser, et faire évoluer votre application, dans toutes les directions.",
    "Des outils conçus pour une équipe, jusqu'aux plateformes prêtes pour toute l'entreprise.",
    "Des gains rapides, aux applications de niveau entreprise,",
    "tout est intégré, et prêt à être lancé.",
    "C'est une nouvelle façon de travailler, où vos équipes vont aussi loin que leurs idées.",
    "Vous l'imaginez ?",
    "Donnons-lui vie.",
]
SAY = lambda t: t.replace('YouCraft', 'You Craft')
OUT = os.path.join(HERE, f'vo_bbfr_cb_{VOICE}')
os.makedirs(OUT, exist_ok=True)
norm = lambda s: re.sub(r'[^a-z ]', ' ', unicodedata.normalize('NFKD', s.lower()).encode('ascii', 'ignore').decode()).split()

tts = ChatterboxMultilingualTTS.from_pretrained(device='cpu')
asr = whisper.load_model('small')
starts = [round(W.fwd(t), 3) for t in STARTS0]
meta = []
for i, line in enumerate(LINES):
    nxt = starts[i + 1] if i + 1 < len(starts) else W.END - 1.0
    best = None
    for seed in (11, 23, 37, 51):
        torch.manual_seed(seed)
        w = tts.generate(SAY(line), language_id='fr', audio_prompt_path=REF, exaggeration=0.55, cfg_weight=0.45)
        x = w[0].numpy()
        env = np.abs(x); idx = np.where(env > 0.02 * env.max())[0]
        x = x[max(0, idx[0] - int(0.03 * tts.sr)): min(len(x), idx[-1] + int(0.2 * tts.sr))]
        path = f'{OUT}/l{i:02d}.wav'
        ta.save(path, torch.from_numpy(x)[None], tts.sr)
        heard = asr.transcribe(path, language='fr', fp16=False)['text'].strip()
        ref, got = norm(SAY(line).replace('-', ' ')), set(norm(heard.replace('-', ' ')))
        miss = [m for m in ref if m not in got and len(m) > 2]
        dur = len(x) / tts.sr
        score = len(miss) + (5 if dur > nxt - starts[i] else 0)
        print(i, 'graine', seed, round(dur, 2), 's', 'mots manquants', miss, '|', heard, flush=True)
        if best is None or score < best[0]:
            best = (score, seed, x.copy(), heard, dur)
        if score == 0:
            break
    ta.save(f'{OUT}/l{i:02d}.wav', torch.from_numpy(best[2])[None], tts.sr)
    meta.append({'text': line, 'start': starts[i], 'dur': round(best[4], 2), 'seed': best[1], 'asr': best[3], 'marge': round(nxt - starts[i] - best[4], 2)})
json.dump(meta, open(f'{OUT}/meta.json', 'w'), ensure_ascii=False, indent=1)
print('ok', VOICE)

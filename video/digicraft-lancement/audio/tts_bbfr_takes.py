"""Prises de voix off pour « Donnez vie à vos idées » (version française dynamique) : 6 prises par réplique avec
Chatterbox Multilingual (timbre « pierre »), chacune enregistrée avec son horodatage mot à mot (Whisper), pour que
l'assemblage (build_bbfr_v4.py) puisse être refait et réglé sans régénérer. Reprise possible : les prises existantes
sont conservées. Usage : python3 tts_bbfr_takes.py [module de texte] [dossier de sortie] [nombre de prises]
(par défaut script_bbfr_v4, vo_bbfr_takes, 6 ; environnement avec chatterbox-tts et openai-whisper)"""
import json, os, sys
import numpy as np, soundfile as sf, torch, whisper
from chatterbox.mtl_tts import ChatterboxMultilingualTTS
HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, HERE)
import importlib
MOD = importlib.import_module(sys.argv[1] if len(sys.argv) > 1 else 'script_bbfr_v4')
L, REF = MOD.L, MOD.REF
OUT = os.path.join(HERE, sys.argv[2] if len(sys.argv) > 2 else 'vo_bbfr_takes'); os.makedirs(OUT, exist_ok=True)
SEEDS = (11, 23, 37, 51, 64, 77)[: int(sys.argv[3]) if len(sys.argv) > 3 else 6]
tts = ChatterboxMultilingualTTS.from_pretrained(device='cpu')
asr = whisper.load_model('small')
for i, line in enumerate(L):
    txt, ex, cfg = line['text'], line['ex'], line['cfg']
    for seed in SEEDS:
        base = f'{OUT}/l{i:02d}_s{seed}'
        if os.path.exists(base + '.json') and json.load(open(base + '.json', encoding='utf-8'))['text'] == txt: continue
        torch.manual_seed(seed)
        x = tts.generate(txt, language_id='fr', audio_prompt_path=os.path.join(HERE, REF), exaggeration=ex, cfg_weight=cfg)[0].numpy()
        env = np.abs(x); idx = np.where(env > 0.02 * env.max())[0]
        x = x[max(0, idx[0] - int(0.03 * tts.sr)): min(len(x), idx[-1] + int(0.2 * tts.sr))]
        sf.write(base + '.wav', x.astype(np.float32), tts.sr)
        r = asr.transcribe(base + '.wav', language='fr', fp16=False, word_timestamps=True, initial_prompt='YouCraft')
        words = [{'w': w['word'], 'a': w['start'], 'b': w['end']} for s in r['segments'] for w in s['words']]
        json.dump({'text': txt, 'seed': seed, 'heard': r['text'].strip(), 'words': words, 'sr': tts.sr}, open(base + '.json', 'w'), ensure_ascii=False)
        print(i, seed, round(len(x) / tts.sr, 2), '|', r['text'].strip(), flush=True)
print('ok')

"""Nettoyage de la voix synchronisée : coupe ce qui suit le dernier mot (bruit, respiration traînante) sur les répliques
sans écho, fondu de 120 ms. Usage : python3 trim_bbfr_sync.py (environnement avec openai-whisper)"""
import json, os
import numpy as np, soundfile as sf, whisper
D = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'vo_bbfr_sync')
FX = {5, 14, 16, 17}
asr = whisper.load_model('small')
meta = json.load(open(f'{D}/meta.json', encoding='utf-8'))
for i, m in enumerate(meta):
    p = f'{D}/l{i:02d}.wav'
    x, sr = sf.read(p)
    r = asr.transcribe(p, language='fr', fp16=False, word_timestamps=True, initial_prompt='YouCraft')
    ws = [w for s in r['segments'] for w in s['words']]
    if i in FX or not ws:
        print(i, 'inchangé', round(len(x) / sr, 2)); continue
    end = min(len(x), int((ws[-1]['end'] + 0.3) * sr))
    y = x[:end].copy(); f = int(0.12 * sr); y[-f:] *= np.linspace(1, 0, f)
    sf.write(p, y.astype(np.float32), sr)
    m['dur'] = round(len(y) / sr, 2)
    print(i, round(len(x) / sr, 2), '->', m['dur'], '|', r['text'].strip())
json.dump(meta, open(f'{D}/meta.json', 'w'), ensure_ascii=False, indent=1)

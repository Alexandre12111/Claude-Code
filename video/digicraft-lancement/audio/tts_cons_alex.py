"""Prises de voix off de la vidéo consultants avec la voix clonée d'Alexandre (Chatterbox Multilingual).
Pour chaque réplique : 2 extraits de référence x 2 graines ; chaque prise est enregistrée avec son horodatage mot à mot
(Whisper) et sa similarité de timbre avec la voix source (encodeur vocal de Chatterbox), utilisée par build_cons.py.
Usage : VOICE_REF_DIR=... python3 tts_cons_alex.py (environnement avec chatterbox-tts et openai-whisper)"""
import json, os, sys
import numpy as np, soundfile as sf, torch, whisper, librosa
from chatterbox.mtl_tts import ChatterboxMultilingualTTS
HERE = os.path.dirname(os.path.abspath(__file__)); sys.path.insert(0, HERE)
from script_cons_alex import L, REFS, REF_DIR
OUT = os.path.join(HERE, 'vo_cons_alex_takes'); os.makedirs(OUT, exist_ok=True)
tts = ChatterboxMultilingualTTS.from_pretrained(device='cpu'); asr = whisper.load_model('small')
src, _ = librosa.load(os.path.join(REF_DIR, 'vocals_mono.wav'), sr=16000)
E0 = tts.ve.embeds_from_wavs([src[:50 * 16000]], sample_rate=16000)[0]
for i, line in enumerate(L):
    txt = line['text']
    for rk, ref in REFS.items():
        for seed in (11, 23):
            base = f'{OUT}/l{i:02d}_s{rk}{seed}'
            if os.path.exists(base + '.json') and json.load(open(base + '.json', encoding='utf-8'))['text'] == txt: continue
            torch.manual_seed(seed)
            x = tts.generate(txt, language_id='fr', audio_prompt_path=ref, exaggeration=line['ex'], cfg_weight=line['cfg'])[0].numpy()
            env = np.abs(x); idx = np.where(env > 0.02 * env.max())[0]
            x = x[max(0, idx[0] - int(0.03 * tts.sr)): min(len(x), idx[-1] + int(0.2 * tts.sr))]
            sf.write(base + '.wav', x.astype(np.float32), tts.sr)
            r = asr.transcribe(base + '.wav', language='fr', fp16=False, word_timestamps=True, initial_prompt='YouCraft')
            words = [{'w': w['word'], 'a': w['start'], 'b': w['end']} for s in r['segments'] for w in s['words']]
            e = tts.ve.embeds_from_wavs([librosa.resample(x, orig_sr=tts.sr, target_sr=16000)], sample_rate=16000)[0]
            sim = float(np.dot(e, E0) / np.linalg.norm(e) / np.linalg.norm(E0))
            json.dump({'text': txt, 'seed': f'{rk}{seed}', 'heard': r['text'].strip(), 'words': words, 'sr': tts.sr, 'sim': sim}, open(base + '.json', 'w'), ensure_ascii=False)
            print(i, rk, seed, round(sim, 3), round(len(x) / tts.sr, 2), '|', r['text'].strip(), flush=True)
print('ok')

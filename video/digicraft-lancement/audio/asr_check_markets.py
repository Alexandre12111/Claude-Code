"""Contrôle de diction : transcrit chaque réplique (Whisper small multilingue) et compare au texte lu."""
import difflib, json, os, re, sys, unicodedata
import numpy as np
import soundfile as sf
import sherpa_onnx as so
from scipy.signal import resample_poly

HERE = os.path.dirname(os.path.abspath(__file__))
W = os.environ.get('WHISPER', '/tmp/claude-0/-home-user-Claude-Code/b2d349ab-5523-52d5-8843-d14029de2d00/scratchpad/tts/sherpa-onnx-whisper-small') + '/small-'
LANG = {'fr': 'fr', 'en': 'en', 'uk': 'en', 'us': 'en', 'caen': 'en', 'cafr': 'fr', 'de': 'de', 'es': 'es', 'it': 'it', 'nl': 'nl', 'pl': 'pl', 'sv': 'sv'}


def norm(t):
    t = unicodedata.normalize('NFKD', t.lower())
    t = ''.join(c for c in t if not unicodedata.combining(c))
    return re.sub(r'[^a-z0-9 ]+', ' ', t.replace('didgy craft', 'digicraft').replace('digi craft', 'digicraft').replace('you craft', 'youcraft').replace('u craft', 'youcraft').replace('ucraft', 'youcraft')).split()


for code in sys.argv[1:]:
    r = so.OfflineRecognizer.from_whisper(encoder=W + 'encoder.int8.onnx', decoder=W + 'decoder.int8.onnx', tokens=W + 'tokens.txt', language=LANG[code], task='transcribe', num_threads=2)
    meta = json.load(open(os.path.join(HERE, 'vo_mk', code, 'meta.json')))
    scores = []
    for i, m in enumerate(meta):
        x, sr = sf.read(os.path.join(HERE, 'vo_mk', code, f'l{i:02d}.wav'))
        y = resample_poly(x, 16000, sr).astype(np.float32)
        s = r.create_stream(); s.accept_waveform(16000, np.concatenate([np.zeros(1600, np.float32), y, np.zeros(4800, np.float32)])); r.decode_stream(s)
        hyp = s.result.text.strip()
        sc = difflib.SequenceMatcher(None, norm(m['tts']), norm(hyp)).ratio()
        scores.append(sc)
        if sc < 0.85:
            print(f'  {code} {i + 1:02d} {sc:.2f} | {m["tts"]}\n            -> {hyp}')
    print(code, 'diction moyenne', round(float(np.mean(scores)), 3))

"""Sound design discret de la vidéo consultants : souffle à chaque changement de scène, petits sons sur les apparitions.
Générateurs repris du sound design du film (sfx5.py)."""
import json, os, re
import numpy as np
import soundfile as sf
HERE = os.path.dirname(os.path.abspath(__file__))
_src = open(os.path.join(HERE, 'sfx5.py'), encoding='utf-8').read()
exec(_src.split('\ndry = np.zeros((N, 2))')[0].split('# La scène « idées »')[0])
SUF = os.environ.get('VOICE_SUF', '')  # '_alex' : version avec la voix clonée d'Alexandre
CT = json.loads(re.search(r'window\.CT = (\{.*\});', open(os.path.join(HERE, '..', 'src_cons', f'cons_timeline{SUF}.js'), encoding='utf-8').read()).group(1))
N = int((CT['END'] + 1.5) * SR); Mu.N = N
dry, wet = np.zeros((N, 2)), np.zeros((N, 2))
P = lambda buf, t, x, g=1.0, pan=0.0: place(buf, t, x, g, pan)
P(wet, 0.4, sparkle(0.8, 0.35, n=6))
for k, (s0, s1) in CT['scenes'].items():
    if s0 > 0.5:
        P(dry, s0 - 0.25, whoosh(0.5, True, 0.35, 0.4, -0.4))
for k in ('logo', 'fin'):
    P(wet, CT['scenes'][k][0] + 0.1, chime((79, 84, 88, 91), 0.45))
for k in ('cas_rh', 'cas_log', 'cas_ind', 'securite', 'vendre1', 'vendre2', 'vendre3'):
    P(dry, CT['scenes'][k][0] + 0.2, pop(700, 0.35), pan=-0.3)
mix = dry + Mu.reverb(wet, 2.4, 0.35) if hasattr(Mu, 'reverb') else dry + wet
sf.write(os.path.join(HERE, 'stems', f'sfx_cons{SUF}.wav'), (mix * 0.8).astype(np.float32), SR)
print('sfx_cons ok', round(float(np.max(np.abs(mix))), 3))

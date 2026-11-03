"""Mixage de la vidéo consultants : voix off (vo_cons), musique et sound design, musique en retrait sous la voix."""
import json, os, subprocess
import numpy as np
import soundfile as sf
from scipy.ndimage import uniform_filter1d
from scipy.signal import resample_poly

HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
import re as _re
SUF = os.environ.get('VOICE_SUF', '')  # '_alex' : version avec la voix clonée d'Alexandre
CTF = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'src_cons', f'cons_timeline{SUF}.js')
END_CT = json.loads(_re.search(r'window\.CT = (\{.*\});', open(CTF, encoding='utf-8').read()).group(1))['END']
SR, END = 48000, END_CT
# Voix choisie (dossier de répliques) et suffixe des fichiers : VO=vo_bbfr_cb_homme TAG=_homme python3 mix_bbfr.py
VO = os.environ.get('VO', 'vo_cons' + SUF)
TAG = os.environ.get('TAG', '')
N = int((END_CT + 1.5) * SR)
STARTS = [m['start'] for m in json.load(open(os.path.join(HERE, VO, 'meta.json'), encoding='utf-8'))]

# Voix : traitement léger (passe-haut, chaleur, présence, compression douce).
raw = os.path.join(STEMS, f'vo_cons_raw{SUF}.wav')
vo = np.zeros(N)
for i, t in enumerate(STARTS):
    x, sr = sf.read(os.path.join(HERE, VO, f'l{i:02d}.wav'))
    x = resample_poly(x, SR, sr)
    a = int(t * SR); vo[a:a + len(x)] += x[: N - a]
sf.write(raw, vo.astype(np.float32), SR)
proc = os.path.join(STEMS, f'vo_cons{SUF}.wav')
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', raw, '-af',
                # Traitement voix de publicité : chaleur, moins de « boîte », présence et air, de-esser, compression marquée,
                # légère saturation douce, limiteur.
                'highpass=f=80,equalizer=f=140:t=q:w=0.9:g=2.5,equalizer=f=320:t=q:w=1.2:g=-2.5,equalizer=f=3500:t=q:w=1.0:g=3,'
                'equalizer=f=10000:t=h:w=0.7:g=2.5,deesser=i=0.4:m=0.5:f=0.5,acompressor=threshold=-24dB:ratio=3.5:attack=4:release=90:makeup=3,'
                'asoftclip=type=tanh:threshold=0.9,alimiter=limit=0.95',
                '-ar', str(SR), '-ac', '1', proc], check=True)
v, _ = sf.read(proc)
v = np.pad(v[:N], (0, max(0, N - len(v))))

music, _ = sf.read(os.path.join(STEMS, f'music_cons{SUF}.wav'))
sfx, _ = sf.read(os.path.join(STEMS, f'sfx_cons{SUF}.wav'))
pad = lambda x: np.pad(x[:N], ((0, max(0, N - len(x))), (0, 0)))
music, sfx = pad(music), pad(sfx)

# Ducking : enveloppe de la voix lissée (attaque 60 ms, relâche 350 ms).
env = np.sqrt(np.maximum(uniform_filter1d(v ** 2, int(0.03 * SR)), 0))
on = (env > 0.01).astype(float)
k = np.zeros(N); a_, r_ = 1 - np.exp(-1 / (0.06 * SR)), 1 - np.exp(-1 / (0.35 * SR))
acc = 0.0
for i in range(0, N, 48):
    tgt = on[i]
    acc += (a_ if tgt > acc else r_) * 48 * (tgt - acc)
    acc = min(1, max(0, acc))
    k[i:i + 48] = acc
duck = 1 - 0.72 * k   # musique plus en retrait sous la voix
mix = music * 0.85 * duck[:, None] + sfx * 1.25 * (1 - 0.4 * k)[:, None] + (v * 1.0)[:, None]
mix = mix[: int(END * SR)]
t = np.arange(len(mix)) / SR
fade = np.ones(len(mix)); fade[t > END - 1.0] = np.clip((END - t[t > END - 1.0]) / 1.0, 0, 1)
mix *= fade[:, None]
tmp = os.path.join(STEMS, f'mix_cons_raw{SUF}.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, f'mix_cons{TAG}{SUF}.wav')], check=True)
# Version sans voix (musique + effets).
m2 = (music * 0.85 + sfx * 1.25)[: int(END * SR)] * fade[:, None]
sf.write(tmp, (m2 / np.max(np.abs(m2)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, f'mix_cons_musique{SUF}.wav')], check=True)
print('mix_cons ok')

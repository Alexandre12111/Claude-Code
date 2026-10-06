"""Mixage du film « Bring your ideas to life » DigiCraft : voix guide + musique + sound design, ducking sous la voix."""
import os, subprocess
import numpy as np
import soundfile as sf
from scipy.ndimage import uniform_filter1d
from scipy.signal import resample_poly

HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
SR, END = 48000, 54.0
N = int(55.5 * SR)
STARTS = [0.30, 3.30, 4.75, 6.35, 10.55, 12.6, 19.9, 23.6, 30.0, 34.7, 37.9, 41.0, 46.3, 48.4]

# Voix : traitement léger (passe-haut, chaleur, présence, compression douce).
raw = os.path.join(STEMS, 'vo_b44_raw.wav')
vo = np.zeros(N)
for i, t in enumerate(STARTS):
    x, sr = sf.read(os.path.join(HERE, 'vo_b44', f'l{i:02d}.wav'))
    x = resample_poly(x, SR, sr)
    a = int(t * SR); vo[a:a + len(x)] += x[: N - a]
sf.write(raw, vo.astype(np.float32), SR)
proc = os.path.join(STEMS, 'vo_b44.wav')
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', raw, '-af',
                'highpass=f=75,equalizer=f=180:t=q:w=1:g=1.5,equalizer=f=3200:t=q:w=1.2:g=2,equalizer=f=9000:t=h:w=0.7:g=2,'
                'acompressor=threshold=-20dB:ratio=2.5:attack=8:release=120:makeup=2,alimiter=limit=0.95',
                '-ar', str(SR), '-ac', '1', proc], check=True)
v, _ = sf.read(proc)
v = np.pad(v[:N], (0, max(0, N - len(v))))

music, _ = sf.read(os.path.join(STEMS, 'music_b44.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx_b48.wav'))
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
duck = 1 - 0.5 * k
mix = music * 0.85 * duck[:, None] + sfx * 1.25 * (1 - 0.25 * k)[:, None] + (v * 1.0)[:, None]
mix = mix[: int(END * SR)]
t = np.arange(len(mix)) / SR
fade = np.ones(len(mix)); fade[t > END - 1.0] = np.clip((END - t[t > END - 1.0]) / 1.0, 0, 1)
mix *= fade[:, None]
tmp = os.path.join(STEMS, 'mix_b48_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix_b48.wav')], check=True)
# Version sans voix (musique + effets).
m2 = (music * 0.85 + sfx * 1.25)[: int(END * SR)] * fade[:, None]
sf.write(tmp, (m2 / np.max(np.abs(m2)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix_b48_musique.wav')], check=True)
print('mix_b48 ok')

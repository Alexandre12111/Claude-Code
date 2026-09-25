"""Mixage : musique + sound design (+ voix off témoin), normalisation web."""
import json, os, re, subprocess
import numpy as np
import soundfile as sf
from scipy import signal
from music import SR, N, lp, hp, reverb

HERE = os.path.dirname(__file__)
STEMS = os.path.join(HERE, 'stems')
tl = json.loads(re.search(r'=\s*(\{[\s\S]*\})\s*;', open(os.path.join(HERE, '../src/timeline.js')).read()).group(1))

music, _ = sf.read(os.path.join(STEMS, 'music.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx.wav'))

vo = np.zeros((N, 2))
for key, t in tl['vo'].items():
    x, sr = sf.read(os.path.join(STEMS, 'vo', key + '.wav'))
    if x.ndim > 1:
        x = x.mean(1)
    x = signal.resample_poly(x, SR, sr)
    x = hp(x, 90)
    b, a = signal.iirpeak(3200 / (SR / 2), 1.2)
    x = x + 0.25 * signal.lfilter(b, a, x)
    x = np.tanh(x / np.max(np.abs(x)) * 1.6) / np.tanh(1.6)
    i = int(t * SR)
    j = min(N, i + len(x))
    vo[i:j, 0] += x[: j - i] * 0.5
    vo[i:j, 1] += x[: j - i] * 0.5
vo = reverb(vo, 0.6, 0.07)

env = np.abs(vo).mean(1)
env = signal.lfilter(*signal.butter(1, 6 / (SR / 2)), env)
env = np.clip(env / (env.max() + 1e-9) * 4, 0, 1)
att = signal.lfilter(*signal.butter(1, 3 / (SR / 2)), (env > 0.05).astype(float))
duck = 1 - 0.74 * np.clip(att * 1.3, 0, 1)
sduck = 1 - 0.45 * np.clip(att * 1.3, 0, 1)

def export(mix, name):
    tmp = os.path.join(STEMS, name + '_raw.wav')
    sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
    out = os.path.join(STEMS, name + '.wav')
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=11', '-ar', str(SR), out], check=True)
    print('export', out)

export(music * 0.8 + sfx * 0.9, 'mix_sans_voix')
export(music * 0.8 * duck[:, None] + sfx * 0.75 * sduck[:, None] + vo * 1.35, 'mix_voix')

bg = (music * 0.8 * duck[:, None] + sfx * 0.75 * sduck[:, None]).mean(1)
v = (vo * 1.35).mean(1)
for key, t in tl['vo'].items():
    s = slice(int((t + 0.2) * SR), int((t + 1.8) * SR))
    r = lambda x: 20 * np.log10(np.sqrt(np.mean(x[s] ** 2)) + 1e-9)
    print(key, 'voix/fond dB', round(r(v) - r(bg), 1))

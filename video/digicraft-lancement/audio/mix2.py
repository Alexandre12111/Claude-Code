"""Mixage V2 : musique + sound design + voix off Supertonic (féminine et masculine)."""
import json, os, re, subprocess
import numpy as np
import soundfile as sf
from scipy import signal
import music as Mu
Mu.N = int(31.5 * 48000)
from music import SR, hp, reverb

N = Mu.N
HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
tl = json.loads(re.search(r'=\s*(\{[\s\S]*\})\s*;', open(os.path.join(HERE, '../src/timeline.js')).read()).group(1))
music, _ = sf.read(os.path.join(STEMS, 'music2.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx2.wav'))


def voice(folder):
    vo = np.zeros((N, 2))
    for key, t in tl['vo'].items():
        x, sr = sf.read(os.path.join(STEMS, folder, key + '.wav'))
        x = signal.resample_poly(x, SR, sr) if sr != SR else x
        x = hp(x, 80)
        b, a = signal.iirpeak(3500 / (SR / 2), 1.0)
        x = x + 0.2 * signal.lfilter(b, a, x)
        x = x / np.max(np.abs(x))
        env = signal.lfilter(*signal.butter(1, 20 / (SR / 2)), np.abs(x))
        g = 1 / np.maximum(env / (env.max() + 1e-9), 0.25) ** 0.35
        x = np.tanh(x * g * 1.2) / np.tanh(1.2)
        i = int(t * SR); j = min(N, i + len(x))
        vo[i:j] += x[: j - i, None] * 0.5
    return reverb(vo, 0.5, 0.06)


def export(mix, name):
    tmp = os.path.join(STEMS, name + '_raw.wav')
    sf.write(tmp, (mix[: int(30.0 * SR)] / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, name + '.wav')], check=True)
    print('export', name)


export(music * 0.8 + sfx * 0.9, 'mix2_sans_voix')
for folder, name, vg in (('vo2_f', 'mix2_voix_f', 1.9), ('vo2_m', 'mix2_voix_m', 2.4)):
    vo = voice(folder) * vg
    env = signal.lfilter(*signal.butter(1, 4 / (SR / 2)), (np.abs(vo).mean(1) > 0.01).astype(float))
    att = np.clip(env * 1.4, 0, 1)
    mix = music * 0.8 * (1 - 0.74 * att)[:, None] + sfx * 0.75 * (1 - 0.5 * att)[:, None] + vo
    export(mix, name)
    bg = (music * 0.8 * (1 - 0.74 * att)[:, None] + sfx * 0.75 * (1 - 0.5 * att)[:, None]).mean(1); v = vo.mean(1)
    rs = []
    for key, t in tl['vo'].items():
        d = sf.info(os.path.join(STEMS, folder, key + '.wav')).duration
        s = slice(int((t + 0.1) * SR), int((t + d - 0.1) * SR))
        rs.append(round(20 * np.log10(np.sqrt(np.mean(v[s] ** 2)) / np.sqrt(np.mean(bg[s] ** 2))), 1))
    print(name, 'voix/fond dB', rs)

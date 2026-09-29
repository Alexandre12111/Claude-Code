"""Mixage version anglaise : voix client posée phrase par phrase (musique EN + sfx EN)."""
import os, subprocess, sys
import numpy as np
import soundfile as sf
from scipy import signal
from scipy.ndimage import maximum_filter1d, uniform_filter1d

HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
SR, END = 48000, 58.25
N = int(60.0 * SR)
SRC = sys.argv[1]

# (début, fin) dans l'enregistrement -> instant de pose dans la vidéo V9
CLIPS = [  # prise anglaise n°2
    ((1.98, 4.04), 3.4),     # What we've done for almost thirty years? (VALUE)
    ((4.2, 6.73), 5.6),      # Find the value companies leave on the table.
    ((7.22, 11.49), 8.7),    # And today, the biggest opportunity... (PRODUCTIVITY)
    ((11.63, 15.95), 13.1),  # Your teams are full of ideas... (ideas)
    ((16.28, 19.84), 17.6),  # The problem is... come to life. (They lack a tool)
    ((20.22, 23.44), 21.4),  # So we came up with something different. The idea is very simple.
    ((23.59, 26.12), 24.85), # You explain what you need, in your own words. (prompt)
    ((26.21, 31.45), 27.55), # And DigiCraft takes care of the rest: the design... (building)
    ((31.67, 34.9), 33.0),   # One click, and your tool is live... (publish, mobile)
    ((35.15, 42.45), 36.5),  # At Leyton... (figures)
    ((42.75, 44.06), 44.1),  # When it comes to security, (security)
    ((44.23, 49.82), 45.55), # you can rest easy. Your data stays in Europe...
    ((50.09, 51.23), 51.4),  # Because it's your tool. (Built by you)
    ((51.53, 52.51), 53.7),  # Truly yours. (final logo)
]


def load():
    tmp = os.path.join(STEMS, 'vo_en_src.wav')
    subprocess.run(['ffmpeg', '-y', '-v', 'quiet', '-i', SRC, '-ac', '1', '-ar', str(SR), '-c:a', 'pcm_f32le', tmp])
    x, _ = sf.read(tmp)
    return x


def chain(x):
    # Prise studio : passe haut, léger désembouage, présence douce, compression 2:1 lissée.
    x = signal.sosfiltfilt(signal.butter(3, 75, 'high', fs=SR, output='sos'), x)
    b, a = signal.iirpeak(280, 1.2, fs=SR)
    x = x - 0.12 * signal.lfilter(b, a, x)
    b, a = signal.iirpeak(3500, 1.0, fs=SR)
    x = x + 0.10 * signal.lfilter(b, a, x)
    env = signal.filtfilt(*signal.butter(1, 10 / (SR / 2)), np.abs(x))
    thr = np.percentile(env[env > 0.1 * env.max()], 65)
    g = np.where(env > thr, (thr + (env - thr) / 2) / np.maximum(env, 1e-9), 1.0)
    return x * g


x = chain(load())
vo = np.zeros(N)
placed = []
for (a, b), t in CLIPS:
    c = x[int((a - 0.08) * SR):int((b + 0.25) * SR)].copy()
    fi, fo = int(0.03 * SR), int(0.2 * SR)
    c[:fi] *= np.linspace(0, 1, fi); c[-fo:] *= np.linspace(1, 0, fo)
    act = np.abs(c) > 0.05 * np.max(np.abs(c))
    c = c / np.sqrt(np.mean(c[act] ** 2)) * 0.16
    i = int((t - 0.08) * SR)
    vo[i:i + len(c)] += c[:N - i]
    placed.append((round(t, 2), round(t + b - a, 2)))
print(placed)
# limiteur doux de crête
pk = maximum_filter1d(np.abs(vo), 145)
vo = vo * uniform_filter1d(np.minimum(1, 0.5 / np.maximum(pk, 1e-9)), 145)
vo = np.stack([vo, vo], 1)

music, _ = sf.read(os.path.join(STEMS, 'music_en.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx_en.wav'))
music = np.pad(music[:N], ((0, max(0, N - len(music))), (0, 0))); sfx = np.pad(sfx[:N], ((0, max(0, N - len(sfx))), (0, 0)))
on = (signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(vo[:, 0])) > 0.01).astype(float)
on = maximum_filter1d(on, int(0.35 * SR))
att = np.clip(signal.filtfilt(*signal.butter(1, 3 / (SR / 2)), on), 0, 1)
bed = music * 0.8 * (1 - 0.72 * att)[:, None] + sfx * 1.5 * (1 - 0.6 * att)[:, None]
mix = (bed + vo * 1.4)[: int(END * SR)]
t = np.arange(len(mix)) / SR
fade = np.ones(len(mix)); fade[t > END - 0.75] = np.clip(1 - (t[t > END - 0.75] - (END - 0.75)) / 0.75, 0, 1)
mix *= fade[:, None]
tmp = os.path.join(STEMS, 'mix_en_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix_en.wav')], check=True)
v = vo[: len(mix), 0] * 1.4; bb = bed[: len(mix)].mean(1)
m = signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(v)) > 0.05
print('voix/fond pendant la parole dB', round(20 * np.log10(np.sqrt(np.mean(v[m] ** 2)) / np.sqrt(np.mean(bb[m] ** 2))), 1))

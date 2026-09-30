"""Mixage FR CognitX (prise du 30/09) : voix légèrement ralentie pour remplir chaque plan (Rubber Band, 15 % max),\nclaquement de bouche initial retiré, musique et sound design en fond."""
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
# ((début, fin) dans la prise, instant de pose, fin du plan disponible)
CLIPS = [  # prise FR du 30/09, minutage long (58,25 s)
    ((2.44, 6.95), 3.4, 9.0),      # Notre métier... (claquement de bouche à 1,94 s écarté)
    ((7.68, 10.64), 9.2, 12.7),    # Et aujourd'hui, le plus gros gisement...
    ((11.51, 14.85), 12.9, 16.7),  # Des idées d'outils...
    ((15.42, 18.73), 16.85, 20.1), # Ce qui leur manque... rapidement et simplement.
    ((19.02, 20.66), 20.25, 22.4), # Et cet outil existe : c'est DigiCraft.
    ((20.92, 22.02), 22.6, 23.95), # Le principe est très simple.
    ((22.25, 25.12), 24.1, 27.2),  # Vous décrivez votre besoin avec vos propres mots.
    ((25.4, 31.18), 27.35, 33.3),  # Et DigiCraft construit l'application sous vos yeux...
    ((31.4, 35.78), 33.4, 37.9),   # Un clic, et votre outil est en ligne...
    ((36.0, 41.08), 38.05, 43.8),  # DigiCraft a déjà fait ses preuves...
    ((41.33, 46.64), 44.2, 50.8),  # Côté sécurité...
    ((47.27, 48.19), 51.35, 52.4), # Parce que c'est votre outil.
    ((48.58, 49.33), 52.6, 53.5),  # Vraiment le vôtre.
]
MIN_TEMPO = 0.85  # ralentissement maximal : 15 %


def stretch(c, tempo):
    """Étirement temporel sans changement de hauteur (Rubber Band)."""
    if tempo > 0.995:
        return c
    src, dst = os.path.join(STEMS, 'st_in.wav'), os.path.join(STEMS, 'st_out.wav')
    sf.write(src, c.astype(np.float32), SR)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', src, '-af', f'rubberband=tempo={tempo:.4f}:pitchq=quality:window=standard', dst], check=True)
    y, _ = sf.read(dst)
    return y


def load():
    tmp = os.path.join(STEMS, 'vo13_src.wav')
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
for (a, b), t, end in CLIPS:
    c = x[int((a - 0.08) * SR):int((b + 0.25) * SR)].copy()
    tempo = min(1.0, max(MIN_TEMPO, (b - a) / max(0.1, end - t)))
    c = stretch(c, tempo)
    fi, fo = int(0.03 * SR), int(0.2 * SR)
    c[:fi] *= np.linspace(0, 1, fi); c[-fo:] *= np.linspace(1, 0, fo)
    act = np.abs(c) > 0.05 * np.max(np.abs(c))
    c = c / np.sqrt(np.mean(c[act] ** 2)) * 0.16
    i = int((t - 0.08 / tempo) * SR)
    vo[i:i + len(c)] += c[:N - i]
    placed.append((round(t, 2), round(t + (b - a) / tempo, 2), round(tempo, 2)))
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
tmp = os.path.join(STEMS, 'mix13_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix13.wav')], check=True)
v = vo[: len(mix), 0] * 1.4; bb = bed[: len(mix)].mean(1)
m = signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(v)) > 0.05
print('voix/fond pendant la parole dB', round(20 * np.log10(np.sqrt(np.mean(v[m] ** 2)) / np.sqrt(np.mean(bb[m] ** 2))), 1))

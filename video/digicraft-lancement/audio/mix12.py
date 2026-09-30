"""Mixage FR CognitX (prise du 30/09) sur le minutage long : voix phrase par phrase, musique et sound design en fond."""
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
CLIPS = [  # prise FR du 30/09, minutage long (58,25 s)
    ((1.93, 6.95), 3.4),     # Notre métier... ne voient pas. (VALEUR)
    ((7.68, 10.64), 9.2),    # Et aujourd'hui, le plus gros gisement... (PRODUCTIVITÉ)
    ((11.51, 14.85), 12.9),  # Des idées d'outils... (idées)
    ((15.42, 18.73), 16.85), # Ce qui leur manque... rapidement et simplement. (outil pour les construire)
    ((19.02, 20.66), 20.35), # Et cet outil existe : c'est DigiCraft. (Découvrez la solution)
    ((20.92, 22.02), 22.6),  # Le principe est très simple. (Comment ça marche ?)
    ((22.25, 25.12), 24.0),   # Vous décrivez votre besoin avec vos propres mots. (saisie)
    ((25.4, 31.18), 27.35),   # Et DigiCraft construit l'application sous vos yeux... (construction)
    ((31.4, 35.78), 33.4),    # Un clic, et votre outil est en ligne... (publication, mobile)
    ((36.0, 41.08), 38.05),  # DigiCraft a déjà fait ses preuves... (chiffres)
    ((41.33, 46.64), 44.2),  # Côté sécurité... sans aucune formation. (sérénité)
    ((47.27, 48.19), 51.35), # Parce que c'est votre outil. (Créé par vous)
    ((48.58, 49.33), 52.55), # Vraiment le vôtre.
]


def load():
    tmp = os.path.join(STEMS, 'vo12_src.wav')
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
tmp = os.path.join(STEMS, 'mix12_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix12.wav')], check=True)
v = vo[: len(mix), 0] * 1.4; bb = bed[: len(mix)].mean(1)
m = signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(v)) > 0.05
print('voix/fond pendant la parole dB', round(20 * np.log10(np.sqrt(np.mean(v[m] ** 2)) / np.sqrt(np.mean(bb[m] ** 2))), 1))

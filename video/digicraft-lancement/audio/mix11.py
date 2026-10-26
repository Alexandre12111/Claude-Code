"""Mixage V11 CognitX sans musique : voix client phrase par phrase, sans « nos équipes vous accompagnent »,
sound design discret (variante voix seule avec l'argument « seule »)."""
import os, subprocess, sys
import numpy as np
import soundfile as sf
from scipy import signal
from scipy.ndimage import maximum_filter1d, uniform_filter1d

HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
SR, END = 48000, 54.65
N = int(56.2 * SR)
SRC = sys.argv[1]
VOICE_ONLY = len(sys.argv) > 2 and sys.argv[2] == 'seule'

# (début, fin) dans l'enregistrement -> instant de pose dans la vidéo V9
CLIPS = [
    ((1.18, 5.62), 3.4),    # Notre métier... (VALEUR)
    ((6.28, 9.58), 9.3),    # Et aujourd'hui... (PRODUCTIVITÉ)
    ((10.48, 13.96), 13.0), # Des idées d'outils... (idées)
    ((14.54, 17.5), 16.85),  # Le problème... (Elles manquent d'un outil)
    ((17.9, 19.46), 20.1),  # Alors on a imaginé... (Découvrez)
    ((19.9, 20.9), 22.5),   # Le principe est très simple (Comment ça marche)
    ((21.58, 25.82), 24.0), # Vous expliquez... DigiCraft s'occupe du reste (prompt)
    ((26.52, 30.76), 28.6), # Le design, les données... (construction)
    ((31.18, 34.58), 33.1), # Un clic... (publication, mobile)
    ((34.76, 40.12), 36.7), # Chez nous... (chiffres)
    ((40.52, 43.2), 42.3),  # Côté sécurité... vos données restent en Europe. (accompagnement retiré)
    ((45.96, 48.16), 47.7), # Parce que c'est votre outil... (Créé par vous)
]


def load():
    tmp = os.path.join(STEMS, 'vo11_src.wav')
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

music, _ = sf.read(os.path.join(STEMS, 'music5.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx6.wav'))
music = music[:N]; sfx = sfx[:N]
on = (signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(vo[:, 0])) > 0.01).astype(float)
on = maximum_filter1d(on, int(0.35 * SR))
att = np.clip(signal.filtfilt(*signal.butter(1, 3 / (SR / 2)), on), 0, 1)
bed = np.zeros_like(sfx) if VOICE_ONLY else sfx * 0.9 * (1 - 0.5 * att)[:, None]
mix = (bed + vo * 1.4)[: int(END * SR)]
t = np.arange(len(mix)) / SR
fade = np.ones(len(mix)); fade[t > END - 0.75] = np.clip(1 - (t[t > END - 0.75] - (END - 0.75)) / 0.75, 0, 1)
mix *= fade[:, None]
tmp = os.path.join(STEMS, 'mix11_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix11_voix.wav' if VOICE_ONLY else 'mix11_voix_sfx.wav')], check=True)
print('mix11 ok', 'voix seule' if VOICE_ONLY else 'voix + sound design')

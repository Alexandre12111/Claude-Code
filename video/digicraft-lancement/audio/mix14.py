"""Mixage FR CognitX (prise du 30/09), version sans ralentissement : claquement de bouche retiré,\nvoix traitée façon studio radio, musique et sound design en fond."""
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
    ((2.44, 6.95), 3.4),     # Notre métier... ne voient pas. (VALEUR)
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
    tmp = os.path.join(STEMS, 'vo14_src.wav')
    subprocess.run(['ffmpeg', '-y', '-v', 'quiet', '-i', SRC, '-ac', '1', '-ar', str(SR), '-c:a', 'pcm_f32le', tmp])
    x, _ = sf.read(tmp)
    return x


def denoise(x):
    """Débruitage IA DPDFNet 48 kHz, mélangé à 75 % pour garder le naturel de la voix."""
    import sherpa_onnx
    mdir = '/tmp/claude-0/-home-user-Claude-Code/b2d349ab-5523-52d5-8843-d14029de2d00/scratchpad/enh_models'
    mc = sherpa_onnx.OfflineSpeechDenoiserModelConfig(dpdfnet=sherpa_onnx.OfflineSpeechDenoiserDpdfNetModelConfig(model=os.path.join(mdir, 'dpdfnet2_48khz_hr.onnx')), num_threads=4)
    d = sherpa_onnx.OfflineSpeechDenoiser(sherpa_onnx.OfflineSpeechDenoiserConfig(model=mc))
    y = np.asarray(d(x.astype(np.float32), SR).samples, dtype=np.float64)[: len(x)]
    y = np.pad(y, (0, len(x) - len(y)))
    return 0.75 * y + 0.25 * x


def peq(x, f0, g, q):
    b, a = signal.iirpeak(f0, q, fs=SR)
    return x + (10 ** (g / 20) - 1) * signal.lfilter(b, a, x)


def shelf(x, f0, g, high=True):
    sos = signal.butter(2, f0, 'high' if high else 'low', fs=SR, output='sos')
    return x + (10 ** (g / 20) - 1) * signal.sosfiltfilt(sos, x)


def comp(x, thr_db, ratio, att, rel):
    env = np.sqrt(np.maximum(uniform_filter1d(x ** 2, 48), 0))[::48]
    ca, cr = np.exp(-1 / (att * 1000)), np.exp(-1 / (rel * 1000))
    e = np.empty_like(env); v = 0.0
    for i in range(len(env)):
        c = ca if env[i] > v else cr
        v = c * v + (1 - c) * env[i]; e[i] = v
    g = -np.maximum(20 * np.log10(e + 1e-9) - thr_db, 0) * (1 - 1 / ratio)
    return x * 10 ** (np.interp(np.arange(len(x)), np.arange(len(g)) * 48 + 24, g) / 20)


def chain(x):
    """Traitement « studio radio » : débruitage léger, grave allégé, présence et brillance, de-esser, compression douce."""
    x = denoise(x)
    x = signal.sosfiltfilt(signal.butter(4, 80, 'high', fs=SR, output='sos'), x)
    x = peq(x, 180, -2.0, 1.2)      # moins de grave boomy
    x = peq(x, 350, -3.0, 1.0)      # moins de « carton »
    x = peq(x, 3200, 3.0, 0.9)      # présence, intelligibilité
    x = shelf(x, 9000, 4.0)          # brillance
    sib = signal.sosfiltfilt(signal.butter(4, [5000, 10000], 'bandpass', fs=SR, output='sos'), x)
    ratio = uniform_filter1d(np.abs(sib), 480) / (uniform_filter1d(np.abs(x), 480) + 1e-9)
    x = x - sib * uniform_filter1d(np.clip((ratio - 0.45) / 0.35, 0, 1) * 0.5, 240)   # de-esser
    act = np.abs(x) > 0.05 * np.max(np.abs(x))
    x = x / np.sqrt(np.mean(x[act] ** 2)) * 0.1
    x = comp(x, -24, 2.5, 0.005, 0.1)
    x = comp(x, -26, 1.8, 0.05, 0.4)
    return x


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
tmp = os.path.join(STEMS, 'mix14_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix14.wav')], check=True)
v = vo[: len(mix), 0] * 1.4; bb = bed[: len(mix)].mean(1)
m = signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(v)) > 0.05
print('voix/fond pendant la parole dB', round(20 * np.log10(np.sqrt(np.mean(v[m] ** 2)) / np.sqrt(np.mean(bb[m] ** 2))), 1))

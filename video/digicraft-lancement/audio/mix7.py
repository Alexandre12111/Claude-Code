"""Mixage V7 : voix off enregistrée par le client, posée d'un bloc sur la vidéo V5 (musique validée)."""
import os, subprocess, sys
import numpy as np
import soundfile as sf
from scipy import signal
from scipy.ndimage import maximum_filter1d
import music as Mu
HERE = os.path.dirname(os.path.abspath(__file__))
SR = 48000
END = 57.05
Mu.N = N = int(58.6 * SR)
STEMS = os.path.join(HERE, 'stems')


def biquad_peak(f0, gain_db, q):
    a = 10 ** (gain_db / 40); w = 2 * np.pi * f0 / SR; al = np.sin(w) / (2 * q)
    b = [1 + al * a, -2 * np.cos(w), 1 - al * a]; aa = [1 + al / a, -2 * np.cos(w), 1 - al / a]
    return np.array(b) / aa[0], np.array(aa) / aa[0]


def shelf(f0, gain_db, high=True):
    a = 10 ** (gain_db / 40); w = 2 * np.pi * f0 / SR; al = np.sin(w) / 2 * np.sqrt(2); c = np.cos(w); s = 2 * np.sqrt(a) * al
    if high:
        b = [a * ((a + 1) + (a - 1) * c + s), -2 * a * ((a - 1) + (a + 1) * c), a * ((a + 1) + (a - 1) * c - s)]
        aa = [(a + 1) - (a - 1) * c + s, 2 * ((a - 1) - (a + 1) * c), (a + 1) - (a - 1) * c - s]
    else:
        b = [a * ((a + 1) - (a - 1) * c + s), 2 * a * ((a - 1) - (a + 1) * c), a * ((a + 1) - (a - 1) * c - s)]
        aa = [(a + 1) + (a - 1) * c + s, -2 * ((a - 1) + (a + 1) * c), (a + 1) + (a - 1) * c - s]
    return np.array(b) / aa[0], np.array(aa) / aa[0]


def export(mix, name):
    mix = mix[: int(END * SR)]
    t = np.arange(len(mix)) / SR
    fade = np.ones(len(mix)); fade[t > END - 0.75] = np.clip(1 - (t[t > END - 0.75] - (END - 0.75)) / 0.75, 0, 1)
    mix = mix * fade[:, None]
    tmp = os.path.join(STEMS, name + '_raw.wav')
    sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, name + '.wav')], check=True)
    print('export', name)



# L'enregistrement est déjà calé sur les transitions : un seul décalage global.
SRC = sys.argv[1]
OFFSET = 1.45          # « Et côté sécurité » tombe sur la transition de 43,95 s
KEEP = (1.6, 51.0)     # on retire le bip de départ et la fin de prise


def load():
    tmp = os.path.join(STEMS, 'vo7_src.wav')
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', SRC, '-ac', '1', '-ar', str(SR),
                    '-af', 'afftdn=nr=10:nf=-60', tmp], check=True)
    x, _ = sf.read(tmp)
    x = x[int(KEEP[0] * SR):int(KEEP[1] * SR)]
    f = int(0.15 * SR)
    x[:f] *= np.linspace(0, 1, f); x[-f:] *= np.linspace(1, 0, f)
    return x


def chain(x):
    x = Mu.hp(x, 80)
    for b, a in (shelf(150, 1.5, high=False), biquad_peak(300, -2.5, 1.0), biquad_peak(3000, 2.5, 0.9), biquad_peak(6000, -1.5, 3.0)):
        x = signal.lfilter(b, a, x)
    # Compresseur doux 2,5:1 sur l'enveloppe lissée, pour une voix régulière sans pompage.
    env = signal.filtfilt(*signal.butter(1, 12 / (SR / 2)), np.abs(x))
    thr = np.percentile(env[env > 0.1 * env.max()], 60)
    g = np.where(env > thr, (thr + (env - thr) / 2.5) / np.maximum(env, 1e-9), 1.0)
    x = x * g
    act = np.abs(x) > 0.05 * np.max(np.abs(x))
    return x / (np.sqrt(np.mean(x[act] ** 2)) + 1e-9) * 0.16


x = chain(load())
vo = np.zeros(N)
i = int((KEEP[0] + OFFSET) * SR)
vo[i:i + len(x)] = x[:N - i]
vo = Mu.reverb(np.stack([vo, vo], 1) * 0.5, 0.4, 0.04, 0.01)

music, _ = sf.read(os.path.join(STEMS, 'music4.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx4.wav'))
music = music[:N]; sfx = sfx[:N]
# Ducking : maintien de 0,35 s puis lissage, pour que la musique ne remonte pas entre deux mots.
on = (signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(vo.mean(1))) > 0.01).astype(float)
on = maximum_filter1d(on, int(0.35 * SR))
att = np.clip(signal.filtfilt(*signal.butter(1, 3 / (SR / 2)), on), 0, 1)
bed = music * 0.8 * (1 - 0.72 * att)[:, None] + sfx * 0.75 * (1 - 0.7 * att)[:, None]
export(bed + vo * 2.5, 'mix7_voix')
v = vo.mean(1) * 2.5; b = bed.mean(1)
m = np.abs(signal.filtfilt(*signal.butter(1, 20 / (SR / 2)), np.abs(v))) > 0.05
print('voix/fond pendant la parole dB', round(20 * np.log10(np.sqrt(np.mean(v[m] ** 2)) / np.sqrt(np.mean(b[m] ** 2))), 1))

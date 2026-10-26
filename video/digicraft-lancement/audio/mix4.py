"""Mixage V4 : version musique seule et version avec voix off masculine (Supertonic 3, voix 5)."""
import json, os, subprocess, sys
import numpy as np
import soundfile as sf
from scipy import signal
import music as Mu

HERE = os.path.dirname(os.path.abspath(__file__))
sys.path.insert(0, os.path.join(HERE, '..', 'render'))
import retime  # noqa: E402

SR = 48000
END = 57.05
Mu.N = N = int(58.6 * SR)
STEMS = os.path.join(HERE, 'stems')

# Réplique -> instant d'apparition du texte correspondant (temps de la V3, converti par le retiming).
CUES = [('a01', 3.95), ('a02', 9.15), ('a03', 13.8), ('a04', 17.0), ('a05', 20.75), ('a06', 22.25),
        ('a07', 23.9), ('a08', 27.6), ('a09', 32.05), ('a10', 34.85), ('a11', 36.0), ('a12', 37.4),
        ('a13', 38.8), ('a14', 40.35), ('a15', 44.6), ('a16', 46.55), ('a17', 48.85)]


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


def voice_chain(x):
    x = Mu.hp(x, 75)
    for b, a in (shelf(160, 2.0, high=False), biquad_peak(350, -2.0, 1.0), biquad_peak(3200, 3.0, 0.9), shelf(9500, 2.5, high=True), biquad_peak(6800, -2.0, 3.0)):
        x = signal.lfilter(b, a, x)
    # Compresseur doux (3:1) avec attaque/relâchement, pour une voix posée et présente.
    env = np.abs(x)
    att, rel = np.exp(-1 / (0.004 * SR)), np.exp(-1 / (0.12 * SR))
    e = np.zeros_like(env); v = 0.0
    for i in range(len(env)):
        c = att if env[i] > v else rel
        v = c * v + (1 - c) * env[i]
        e[i] = v
    thr = 0.18
    gain = np.where(e > thr, (thr + (e - thr) / 3) / np.maximum(e, 1e-9), 1.0)
    x = x * gain
    act = np.abs(x) > 0.02 * np.max(np.abs(x))
    x = x / (np.sqrt(np.mean(x[act] ** 2)) + 1e-9) * 0.16
    return np.tanh(x * 1.3) / 1.3


def build_voice(folder):
    vo = np.zeros(N)
    t_prev = 0.0
    placed = []
    for key, t3 in CUES:
        x, sr = sf.read(os.path.join(STEMS, folder, key + '.wav'))
        if x.ndim > 1:
            x = x.mean(1)
        x = signal.resample_poly(x, SR, sr)
        x = voice_chain(x)
        t = max(retime.to_new(t3), t_prev + 0.12)
        i = int(t * SR); j = min(N, i + len(x))
        vo[i:j] += x[: j - i]
        t_prev = t + len(x) / SR
        placed.append((key, round(t, 2), round(t_prev, 2)))
    st = np.stack([vo, vo], 1) * 0.5
    st = Mu.reverb(st, 0.45, 0.05, 0.012)
    return st, placed


def export(mix, name):
    mix = mix[: int(END * SR)]
    t = np.arange(len(mix)) / SR
    fade = np.ones(len(mix)); fade[t > END - 0.75] = np.clip(1 - (t[t > END - 0.75] - (END - 0.75)) / 0.75, 0, 1)
    mix = mix * fade[:, None]
    tmp = os.path.join(STEMS, name + '_raw.wav')
    sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, name + '.wav')], check=True)
    print('export', name)


music, _ = sf.read(os.path.join(STEMS, 'music4.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx4.wav'))
music = music[:N]; sfx = sfx[:N]
export(music * 0.8 + sfx * 0.9, 'mix4')

vo, placed = build_voice(sys.argv[1] if len(sys.argv) > 1 else 'vo4_m5')
print(json.dumps(placed))
env = signal.lfilter(*signal.butter(1, 4 / (SR / 2)), (np.abs(vo).mean(1) > 0.008).astype(float))
att = np.clip(env * 1.4, 0, 1)
bed = music * 0.8 * (1 - 0.84 * att)[:, None] + sfx * 0.75 * (1 - 0.78 * att)[:, None]
vo = vo * 2.5
export(bed + vo, 'mix4_voix')
v = vo.mean(1); b = bed.mean(1)
rs = []
for key, t0, t1 in placed:
    s = slice(int((t0 + 0.1) * SR), int((t1 - 0.1) * SR))
    rs.append(round(20 * np.log10(np.sqrt(np.mean(v[s] ** 2)) / np.sqrt(np.mean(b[s] ** 2))), 1))
print('voix/fond dB', rs)

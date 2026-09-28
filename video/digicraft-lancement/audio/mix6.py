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
CUES = [('c01', 3.7), ('c02', 9.25), ('c03', 13.75), ('c04', 17.0), ('c05', 20.7), ('c06', 22.3),
        ('c07', 23.9), ('c08', 27.55), ('c09', 32.05), ('c10', 34.9), ('c11', 40.35), ('c12', 44.35), ('c13', 46.25)]


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
    env = np.abs(x)
    att, rel = np.exp(-1 / (0.012 * SR)), np.exp(-1 / (0.25 * SR))
    e = np.zeros_like(env); v = 0.0
    for i in range(len(env)):
        c = att if env[i] > v else rel
        v = c * v + (1 - c) * env[i]
        e[i] = v
    thr = 0.25 * e.max()
    gain = np.where(e > thr, (thr + (e - thr) / 2) / np.maximum(e, 1e-9), 1.0)
    x = x * gain
    act = np.abs(x) > 0.02 * np.max(np.abs(x))
    x = x / (np.sqrt(np.mean(x[act] ** 2)) + 1e-9) * 0.15
    n_in, n_out = int(0.03 * SR), int(0.15 * SR)
    x[:n_in] *= np.linspace(0, 1, n_in)
    x[-n_out:] *= np.linspace(1, 0, n_out) ** 2
    return x


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
        t = max(retime.to_new(t3), t_prev + 0.3)
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
    fade = np.ones(len(mix)); fade[t > END - 0.45] = np.clip(1 - (t[t > END - 0.45] - (END - 0.45)) / 0.45, 0, 1)
    mix = mix * fade[:, None]
    tmp = os.path.join(STEMS, name + '_raw.wav')
    sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, name + '.wav')], check=True)
    print('export', name)


music, _ = sf.read(os.path.join(STEMS, 'music4.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx4.wav'))
music = music[:N]; sfx = sfx[:N]

vo, placed = build_voice(sys.argv[1] if len(sys.argv) > 1 else 'vo6')
print(json.dumps(placed))
from scipy import ndimage
act = ndimage.maximum_filter1d((np.abs(vo).mean(1) > 0.006).astype(float), int(1.2 * SR))
env = signal.filtfilt(*signal.butter(1, 1.5 / (SR / 2)), act)
att = np.clip(env * 1.4, 0, 1)
bed = music * 0.8 * (1 - 0.84 * att)[:, None] + sfx * 0.75 * (1 - 0.78 * att)[:, None]
vo = vo * 2.5
export(bed + vo, 'mix6_voix')
v = vo.mean(1); b = bed.mean(1)
rs = []
for key, t0, t1 in placed:
    s = slice(int((t0 + 0.1) * SR), int((t1 - 0.1) * SR))
    rs.append(round(20 * np.log10(np.sqrt(np.mean(v[s] ** 2)) / np.sqrt(np.mean(b[s] ** 2))), 1))
print('voix/fond dB', rs)

act_env = signal.lfilter(*signal.butter(1, 20 / (SR / 2)), np.abs(v))
rs2 = []
for key, t0, t1 in placed:
    s = slice(int(t0 * SR), int(t1 * SR))
    m = act_env[s] > 0.25 * act_env[s].max()
    rs2.append(round(20 * np.log10(np.sqrt(np.mean(v[s][m] ** 2)) / np.sqrt(np.mean(b[s][m] ** 2))), 1))
print('voix/fond pendant la parole dB', rs2)

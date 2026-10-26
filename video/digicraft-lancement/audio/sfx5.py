"""Sound design V8 : whooshes stéréo sans clic (balayage spectral STFT), étincelles sur les reflets,
balayages circulaires sur les anneaux, scan et pings sonar sur la sécurité, sons d'interface affinés.
Les repères des animations sont en temps V3 (convertis par le retiming), les transitions en temps réel."""
import os, sys
import numpy as np
from scipy import signal
import soundfile as sf
import music as Mu
Mu.N = int(58.6 * 48000)
from music import SR, lp, hp, bp, reverb, OUT

sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'render'))
import retime  # noqa: E402

N = Mu.N
rng = np.random.default_rng(23)


def sweep_noise(dur, f0, f1, bw=0.8, curve=1.0):
    """Bruit stéréo filtré par une bande qui glisse de f0 à f1 (en octaves), sans discontinuité."""
    n = int(dur * SR)
    x = rng.standard_normal((2, n + 2048))
    f, t, Z = signal.stft(x, SR, nperseg=1024, noverlap=896)
    prog = np.clip(t / dur, 0, 1) ** curve
    fc = f0 * (f1 / f0) ** prog
    G = np.exp(-0.5 * (np.log2(np.maximum(f, 20)[:, None] / fc[None, :]) / bw) ** 2)
    _, y = signal.istft(Z * G[None], SR, nperseg=1024, noverlap=896)
    y = y[:, :n].T
    return y / (np.max(np.abs(y)) + 1e-9)


def pan_gain(p):
    return np.stack([np.cos((p + 1) * np.pi / 4), np.sin((p + 1) * np.pi / 4)], 1) * 1.414


def whoosh(dur=0.7, up=True, g=1.0, p0=-0.7, p1=0.7):
    f0, f1 = (220, 5200) if up else (5200, 260)
    y = sweep_noise(dur, f0, f1, 0.75, 1.4 if up else 0.7)
    body = sweep_noise(dur, 90, 420, 0.6) if up else sweep_noise(dur, 420, 80, 0.6)
    t = np.arange(len(y)) / SR / dur
    if up:
        env = np.where(t < 0.82, (t / 0.82) ** 2.2, np.clip(1 - (t - 0.82) / 0.18, 0, 1) ** 1.5)
    else:
        env = np.minimum(1, t / 0.06) * np.exp(-t * 3.2)
    y = (y * 0.8 + body * 0.35) * env[:, None]
    return y * pan_gain(np.linspace(p0, p1, len(y))) * 0.32 * g


def ring_swoosh(dur=0.7, g=1.0):
    """Balayage circulaire : le son tourne entre gauche et droite en montant."""
    y = sweep_noise(dur, 300, 3800, 0.55, 1.2)
    t = np.arange(len(y)) / SR
    env = np.sin(np.pi * np.clip(t / dur, 0, 1)) ** 1.3
    pan = 0.8 * np.sin(2 * np.pi * (1.2 * t + 0.6 * t * t / dur))
    tone = np.sin(2 * np.pi * np.cumsum(420 * (1 + 1.5 * t / dur)) / SR) * 0.12
    return (y * env[:, None] + (tone * env)[:, None]) * pan_gain(pan) * 0.3 * g


def riser(dur=1.2, g=1.0):
    y = sweep_noise(dur, 400, 7000, 0.9, 2.0)
    t = np.arange(len(y)) / SR / dur
    return y * (t ** 3)[:, None] * 0.3 * g


def sparkle(dur=0.9, g=1.0, n=14, lo=3200, hi=9000):
    """Étincelles : petites cloches aiguës dispersées + souffle aérien, pour les reflets lumineux."""
    L = int((dur + 0.6) * SR)
    out = np.zeros((L, 2))
    for k in range(n):
        u = (k + rng.uniform(0, 1)) / n
        i = int(u * dur * SR)
        f = np.exp(rng.uniform(np.log(lo), np.log(hi)))
        m = int(0.45 * SR)
        t = np.arange(m) / SR
        s = (np.sin(2 * np.pi * f * t) + 0.3 * np.sin(2 * np.pi * f * 2.01 * t)) * np.exp(-t * rng.uniform(9, 16)) * np.minimum(1, t / 0.002)
        s *= np.sin(np.pi * u) ** 0.7
        out[i:i + m] += s[:, None] * pan_gain(np.full(m, rng.uniform(-0.8, 0.8)))
    air = hp(rng.standard_normal(L), 7000)
    ta = np.arange(L) / SR
    air *= np.sin(np.pi * np.clip(ta / dur, 0, 1)) ** 2 * 0.25
    out += np.stack([air, np.roll(air, 37)], 1)
    return out * 0.07 * g


def ping(f=1318.5, g=1.0):
    n = int(1.6 * SR)
    t = np.arange(n) / SR
    ff = f * (1 - 0.015 * (1 - np.exp(-t * 6)))
    s = np.sin(2 * np.pi * np.cumsum(ff) / SR) + 0.25 * np.sin(2 * np.pi * np.cumsum(ff * 2.76) / SR) * np.exp(-t * 6)
    s *= np.exp(-t * 3.6) * np.minimum(1, t / 0.004)
    return s * 0.09 * g


def scan(dur=0.6, g=1.0):
    y = sweep_noise(dur, 700, 3400, 0.22, 1.0)
    t = np.arange(len(y)) / SR
    fm = np.sin(2 * np.pi * np.cumsum(900 + 700 * t / dur + 60 * np.sin(2 * np.pi * 38 * t)) / SR) * 0.35
    env = np.sin(np.pi * np.clip(t / dur, 0, 1)) ** 0.8
    y = (y + fm[:, None]) * env[:, None]
    return y * pan_gain(np.linspace(-0.5, 0.5, len(y))) * 0.12 * g


def pop(f=900, g=1.0):
    n = int(0.16 * SR)
    t = np.arange(n) / SR
    ff = f * (0.75 + 0.55 * (1 - np.exp(-t * 70)))
    x = np.sin(2 * np.pi * np.cumsum(ff) / SR) * np.exp(-t * 32) * np.minimum(1, t / 0.0015)
    x = lp(x, 5000) + 0.25 * bp(rng.standard_normal(n), 2500, 6000) * np.exp(-t * 400)
    return x * 0.22 * g


def tick(f=2600, g=1.0):
    n = int(0.03 * SR)
    t = np.arange(n) / SR
    return np.sin(2 * np.pi * f * t) * np.exp(-t * 220) * 0.14 * g


def key(g=1.0):
    n = int(0.05 * SR)
    t = np.arange(n) / SR
    x = bp(rng.standard_normal(n), 1800, 6500) * np.exp(-t * 150)
    x += 0.4 * np.sin(2 * np.pi * rng.uniform(180, 260) * t) * np.exp(-t * 120)
    return x * 0.1 * g * rng.uniform(0.7, 1.0)


def click(g=1.0):
    n = int(0.06 * SR)
    t = np.arange(n) / SR
    x = bp(rng.standard_normal(n), 2000, 7000) * np.exp(-t * 260) + 0.5 * np.sin(2 * np.pi * 1400 * t) * np.exp(-t * 180)
    return x * 0.26 * g


def chime(notes=(84, 88, 91), g=1.0):
    n = int(1.8 * SR)
    out = np.zeros((n, 2))
    for i, m in enumerate(notes):
        f = 440 * 2 ** ((m - 69) / 12)
        d = int(i * 0.06 * SR)
        t = np.arange(n - d) / SR
        for ch, det in ((0, 0.998), (1, 1.002)):
            out[d:, ch] += (np.sin(2 * np.pi * f * det * t) + 0.18 * np.sin(2 * np.pi * 2 * f * t)) * np.exp(-t * 3.0) * np.minimum(1, t / 0.003)
    return out * 0.1 * g


def place(buf, t, x, gain=1.0, pan=0.0):
    i = int(t * SR)
    if i >= N or i < 0:
        return
    if x.ndim == 1:
        x = x[:, None] * pan_gain(np.full(len(x), pan))
    x = x[: N - i]
    buf[i:i + len(x)] += x * gain


def at3(buf, t3, x, gain=1.0, pan=0.0):
    place(buf, retime.to_new(t3), x, gain, pan)


# La scène « idées » a sa propre horloge (remap V3 -> temps local) : on inverse ce remap.
IDEES = [[13.2, 12.95], [13.8, 13.55], [16.9, 15.5], [17.0, 15.62], [19.5, 18.9], [20.45, 19.8]]


def at_idees(buf, tl, x, gain=1.0, pan=0.0):
    v3 = np.interp(tl, [p[1] for p in IDEES], [p[0] for p in IDEES])
    at3(buf, float(v3), x, gain, pan)


dry = np.zeros((N, 2))
wet = np.zeros((N, 2))  # sons plus aériens (étincelles, pings, carillons) : plus de réverbération

# --- Logo
for i in range(5):
    at3(dry, 0.45 + (0 if i == 0 else 0.08 + i * 0.04), pop(700 + i * 110, 0.7), pan=-0.4 + 0.2 * i)
for i in range(9):
    at3(dry, 0.7 + i * 0.04, tick(1600 + i * 120, 0.45), pan=-0.4 + 0.1 * i)
at3(dry, 1.5, whoosh(0.6, True, 0.5, -0.3, 0.3))
at3(wet, 1.3, sparkle(0.9, 1.0))
at3(dry, 2.3, riser(0.75, 0.8))
at3(dry, 2.95, ring_swoosh(0.7, 1.2))
# --- VALEUR / PRODUCTIVITÉ
for i in range(11):
    at3(dry, 3.62 + i * 0.03, tick(2400, 0.3))
at3(dry, 3.85, whoosh(0.4, False, 0.45, 0.3, -0.3))
at3(dry, 4.35, pop(420, 0.8))
at3(wet, 5.25, sparkle(1.0, 1.1))
at3(dry, 8.3, whoosh(0.6, False, 0.6, 0.4, -0.4))
at3(dry, 9.0, whoosh(0.6, True, 0.8, -0.5, 0.5))
at3(wet, 10.1, sparkle(1.2, 1.0))
for i in range(6):
    at3(dry, 11.1 + i * 0.13, pop(500 + i * 90, 0.5), pan=-0.3 + i * 0.12)
at3(wet, 12.05, sparkle(1.1, 0.8))
# --- Idées (temps local de la scène)
at3(dry, 13.05, whoosh(0.7, True, 1.1, 0.8, -0.8))
for i in range(3):
    at_idees(dry, 13.65 + i * 0.1, pop(520 + i * 100, 0.8), pan=-0.5 + 0.5 * i)
for i in range(8):
    at_idees(dry, 13.95 + i * 0.1, pop(700 + (i % 4) * 110, 0.6), pan=-0.6 + 0.17 * i)
at_idees(dry, 15.45, whoosh(0.45, False, 0.5))
at_idees(dry, 15.65, whoosh(0.5, True, 0.6))
for i in range(8):
    at_idees(dry, 15.9 + i * 0.1, whoosh(0.3, True, 0.35, -0.2, 0.4))
for k in range(6):
    at_idees(dry, 16.5 + k * 0.45, tick(1500, 0.8), pan=0.5)
at_idees(dry, 18.9, click(0.8), pan=0.6)
at_idees(dry, 19.05, pop(900, 0.9), pan=0.6)
at_idees(dry, 18.7, riser(0.75, 1.1))
at_idees(dry, 19.4, whoosh(0.55, True, 1.2, -0.2, 0.2))
# --- Découvrez
at3(dry, 20.4, whoosh(0.45, True, 0.5, -0.4, 0.2))
at3(wet, 21.2, sparkle(0.5, 0.6, n=7))
at3(dry, 21.85, whoosh(0.5, False, 0.7, 0.2, -0.2))
# --- Comment ça marche / démo
at3(dry, 22.15, whoosh(0.5, True, 0.8))
at3(dry, 23.05, whoosh(0.4, False, 0.7, 0.6, -0.8))
at3(dry, 23.25, whoosh(0.8, True, 0.6, 0.4, -0.2))
for i in range(120):
    at3(dry, 23.9 + i * (2.2 / 120) + rng.uniform(-0.003, 0.003), key(0.8), pan=rng.uniform(-0.2, 0.2))
at3(dry, 26.88, click(1.1), pan=0.2)
at3(dry, 27.0, whoosh(0.5, True, 0.8))
at3(dry, 27.6, whoosh(0.35, False, 0.6, -0.2, -0.7))
for tt in (27.64, 28.07, 28.49, 28.92, 29.34, 29.77, 30.2, 30.66):
    at3(dry, tt, tick(2400, 0.55), pan=0.3)
at3(wet, 28.5, sparkle(0.8, 0.7, n=9))
for i in range(9):
    at3(dry, 29.2 + i * 0.1, pop(900, 0.3), pan=0.4)
at3(wet, 31.16, chime((79, 84, 88), 0.7))
at3(dry, 31.3, whoosh(0.4, False, 0.5, 0.0, -0.6))
at3(dry, 32.1, click(1.1), pan=0.5)
at3(wet, 32.25, chime((84, 88, 91, 96), 1.0))
at3(dry, 32.7, whoosh(0.7, True, 0.9, -0.2, 0.6))
at3(dry, 33.0, whoosh(0.35, False, 0.6, 0.2, -0.5))
at3(wet, 33.8, sparkle(0.8, 0.7, n=9))
at3(dry, 33.8, click(0.8), pan=0.4)
at3(wet, 34.3, chime((88, 93), 0.9), pan=0.4)
# --- Déjà adopté
at3(dry, 34.75, ring_swoosh(0.75, 1.3))
at3(dry, 35.3, whoosh(0.5, True, 0.5))
for t0 in (36.0, 37.4, 38.8):
    at3(dry, t0, pop(480, 1.0))
    at3(dry, t0 - 0.05, whoosh(0.35, False, 0.5))
    at3(dry, t0 - 0.42, whoosh(0.45, True, 0.35, 0.0, 0.5))
    for i in range(16):
        at3(dry, t0 + 0.05 + 0.9 * (i / 16) ** 1.6, tick(3000, 0.26))
    at3(wet, t0 + 0.75, sparkle(0.6, 0.45, n=6))
at3(dry, 39.85, whoosh(0.5, True, 0.6))
# --- Sérénité
at3(dry, 40.2, whoosh(0.7, True, 1.1, -0.8, 0.8))
at3(dry, 40.75, whoosh(0.6, True, 0.6, -0.7, -0.2))
at3(dry, 40.95, scan(0.6, 1.0), pan=-0.3)
at3(dry, 41.45, pop(300, 1.1), pan=-0.4)
at3(dry, 41.8, click(0.9), pan=-0.4)
for j in range(3):
    at3(wet, 41.7 + j * 0.45, ping(1318.5 * (1, 0.891, 0.749)[j], 0.9 - 0.2 * j), pan=-0.45)
for i, tt in enumerate((41.1, 41.5, 41.9)):
    at3(dry, tt, pop(600 + i * 150, 0.7), pan=0.4)
# --- Créé par vous
at3(dry, 44.2, whoosh(0.65, True, 1.2, 0.8, -0.8))
at3(dry, 44.65, whoosh(0.35, False, 0.7))
at3(dry, 45.05, whoosh(0.35, False, 0.7))
at3(wet, 45.5, sparkle(1.0, 1.1))
# --- Fin
at3(dry, 46.0, ring_swoosh(0.6, 1.1))
for i in range(5):
    at3(dry, 46.35 + (0 if i == 0 else 0.06 + i * 0.04), pop(700 + i * 110, 0.65), pan=-0.4 + 0.2 * i)
at3(wet, 47.25, sparkle(0.9, 0.9))
for i in range(18):
    at3(dry, 47.25 + i * (0.9 / 18), key(0.6))
at3(dry, 48.2, whoosh(0.5, True, 0.5))
at3(dry, 48.85, pop(520, 0.9))
at3(wet, 48.9, chime((84, 88, 91), 0.6))
at3(wet, 49.3, sparkle(0.7, 0.5, n=6, lo=4000))

mix = reverb(dry, 1.2, 0.12) + reverb(wet, 2.4, 0.38)
mix = np.tanh(mix * 1.1) / 1.1
sf.write(os.path.join(OUT, 'sfx5.wav'), mix.astype(np.float32), SR)
print('sfx5 ok', round(float(np.max(np.abs(mix))), 3))

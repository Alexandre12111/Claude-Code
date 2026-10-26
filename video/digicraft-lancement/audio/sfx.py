"""Sound design synchronisé sur les animations."""
import numpy as np
from scipy import signal
import soundfile as sf
import os
from music import SR, N, st, add, lp, hp, bp, reverb, OUT

rng = np.random.default_rng(11)


def whoosh(dur=0.6, up=True, g=1.0):
    n = int(dur * SR)
    t = np.arange(n) / SR
    x = rng.standard_normal(n)
    out = np.zeros(n)
    seg = 1024
    for i in range(0, n, seg):
        p = i / n
        fc = (300 + 3500 * p) if up else (3800 - 3300 * p)
        b, a = signal.butter(2, [fc * 0.6 / (SR / 2), min(0.98, fc * 1.6 / (SR / 2))], 'band')
        out[i:i + seg] = signal.lfilter(b, a, x[i:i + seg])
    e = np.sin(np.pi * np.clip(t / dur, 0, 1)) ** 1.6
    return out * e * 0.35 * g


def pop(f=900, g=1.0):
    n = int(0.12 * SR)
    t = np.arange(n) / SR
    ff = f * (1 + 0.6 * np.exp(-t * 60))
    x = np.sin(2 * np.pi * np.cumsum(ff) / SR) * np.exp(-t * 38)
    return x * 0.25 * g


def tick(f=2600, g=1.0):
    n = int(0.03 * SR)
    t = np.arange(n) / SR
    return np.sin(2 * np.pi * f * t) * np.exp(-t * 220) * 0.18 * g


def key(g=1.0):
    n = int(0.05 * SR)
    t = np.arange(n) / SR
    x = bp(rng.standard_normal(n), 1800, 6500) * np.exp(-t * 150)
    x += 0.4 * np.sin(2 * np.pi * rng.uniform(180, 260) * t) * np.exp(-t * 120)
    return x * 0.12 * g * rng.uniform(0.7, 1.0)


def click(g=1.0):
    n = int(0.06 * SR)
    t = np.arange(n) / SR
    x = bp(rng.standard_normal(n), 2000, 7000) * np.exp(-t * 260) + 0.5 * np.sin(2 * np.pi * 1400 * t) * np.exp(-t * 180)
    return x * 0.3 * g


def chime(notes=(84, 88, 91), g=1.0):
    n = int(1.4 * SR)
    out = np.zeros(n)
    for i, m in enumerate(notes):
        f = 440 * 2 ** ((m - 69) / 12)
        d = int(i * 0.07 * SR)
        t = np.arange(n - d) / SR
        out[d:] += (np.sin(2 * np.pi * f * t) + 0.2 * np.sin(2 * np.pi * 2 * f * t)) * np.exp(-t * 3.5) * np.minimum(1, t / 0.003)
    return out * 0.12 * g


def shimmer(dur=1.0, g=1.0):
    n = int(dur * SR)
    t = np.arange(n) / SR
    x = hp(rng.standard_normal(n), 6000) * np.sin(np.pi * np.clip(t / dur, 0, 1)) ** 2
    return x * 0.06 * g


def swish(g=1.0):
    return whoosh(0.25, True, 0.7 * g)


sfx = st()
# Ouverture
add(sfx, 0.1, shimmer(1.0, 1.2), pan=-0.2)
add(sfx, 0.85, whoosh(0.5, False, 0.6))
for i, t in enumerate([1.05, 1.08, 1.12, 1.19, 1.26]):
    add(sfx, t, tick(2200 + i * 150, 0.6), pan=-0.4 + 0.2 * i)
for i in range(6):
    add(sfx, 1.42 + i * 0.05, tick(1800 + i * 120, 0.5), pan=-0.3 + 0.12 * i)
add(sfx, 1.72, swish(1.2), pan=0.4)
add(sfx, 1.98, pop(700, 0.8), pan=0.4)
add(sfx, 2.9, whoosh(1.0, True, 1.2))
# Valeur / productivité
add(sfx, 6.5, shimmer(1.2, 1.3), pan=0.3)
add(sfx, 8.75, whoosh(0.7, False, 0.7))
for i in range(6):
    add(sfx, 10.3 + i * 0.16, pop(500 + i * 90, 0.55), pan=-0.3 + i * 0.12)
# Volet diagonal
add(sfx, 12.95, whoosh(0.8, True, 1.2), pan=-0.3)
# Cartes idées
for i in range(8):
    add(sfx, 13.9 + i * 0.13, pop(650 + (i % 4) * 120, 0.8), pan=-0.6 + 0.17 * i)
for i in range(8):
    add(sfx, 15.9 + i * 0.1, swish(0.5), pan=0.3)
for k in range(5):
    add(sfx, 16.5 + k * 0.5, tick(1500, 0.9), pan=0.5)
add(sfx, 18.9, click(0.8), pan=0.6)
add(sfx, 19.05, pop(900, 0.9), pan=0.6)
add(sfx, 19.2, whoosh(0.6, False, 0.9), pan=0.5)
add(sfx, 19.75, whoosh(0.8, True, 1.3))
# Démo
add(sfx, 20.0, whoosh(0.9, False, 0.8))
for i in range(112):
    add(sfx, 21.95 + i * (2.8 / 112) + rng.uniform(-0.004, 0.004), key(0.9), pan=rng.uniform(-0.2, 0.2))
add(sfx, 24.98, click(1.1), pan=0.2)
add(sfx, 25.2, whoosh(0.6, True, 0.8))
for t in (26.2, 26.75, 27.3, 27.85, 28.4, 28.95, 29.5, 30.1):
    add(sfx, t, tick(2400, 0.7), pan=-0.4)
for i in range(5):
    add(sfx, 28.35 + i * 0.1, pop(1000, 0.4), pan=0.4)
for i in range(4):
    add(sfx, 29.05 + i * 0.1, pop(800, 0.5), pan=0.3)
add(sfx, 29.25, swish(0.9), pan=-0.5)
add(sfx, 30.2, swish(0.9), pan=-0.5)
add(sfx, 30.75, chime((79, 84, 88), 0.8))
add(sfx, 32.3, click(1.1), pan=0.5)
add(sfx, 32.45, chime((84, 88, 91, 96), 1.0))
add(sfx, 32.85, whoosh(0.9, True, 1.0), pan=0.4)
add(sfx, 34.75, click(0.9), pan=0.4)
add(sfx, 35.7, chime((88, 93), 1.0), pan=0.4)
# Preuve
add(sfx, 36.6, whoosh(0.8, True, 1.2), pan=0.3)
for t0, n, d in ((38.1, 30, 1.6), (42.2, 18, 1.1)):
    for i in range(n):
        add(sfx, t0 + d * (i / n) ** 1.6, tick(3000, 0.35), pan=0.0)
add(sfx, 37.9, whoosh(0.5, False, 0.6), pan=-0.5)
add(sfx, 42.0, whoosh(0.5, False, 0.6))
add(sfx, 44.55, whoosh(0.5, False, 0.6), pan=0.5)
add(sfx, 44.8, pop(420, 0.6), pan=0.5)
add(sfx, 45.0, swish(0.5), pan=0.5)
# Confiance
add(sfx, 46.45, whoosh(0.8, True, 1.2), pan=-0.3)
for i, t in enumerate((47.4, 48.05, 48.7)):
    add(sfx, t, pop(600 + i * 150, 0.8), pan=0.4)
add(sfx, 50.2, shimmer(0.8, 1.0), pan=0.3)
add(sfx, 53.35, whoosh(0.8, False, 0.8))
# Clôture
for i in range(5):
    add(sfx, 54.05 + (0 if i == 0 else 0.12 + i * 0.05), pop(700 + i * 110, 0.7), pan=-0.4 + 0.2 * i)
add(sfx, 54.4, shimmer(1.4, 1.2))
for i in range(18):
    add(sfx, 55.95 + i * (1.15 / 18), key(0.7))
add(sfx, 57.15, swish(0.7))

sfx = reverb(sfx, 1.6, 0.18)
sfx = np.clip(sfx, -0.98, 0.98)
sf.write(os.path.join(OUT, 'sfx.wav'), sfx.astype(np.float32), SR)
print('sfx ok peak', float(np.abs(sfx).max()))

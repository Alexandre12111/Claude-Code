"""Sound design synchronisé sur les animations."""
import numpy as np
from scipy import signal
import soundfile as sf
import os
import music as Mu
Mu.N = int(52.5 * 48000)
from music import SR, add, lp, hp, bp, reverb, OUT
N = Mu.N
st = lambda: np.zeros((N, 2))

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
for i in range(5):
    add(sfx, 0.45 + (0 if i == 0 else 0.08 + i * 0.04), pop(700 + i * 110, 0.7), pan=-0.4 + 0.2 * i)
add(sfx, 0.1, shimmer(1.4, 1.2))
for i in range(9):
    add(sfx, 0.7 + i * 0.04, tick(1600 + i * 120, 0.5), pan=-0.4 + 0.1 * i)
add(sfx, 1.55, swish(0.8))
add(sfx, 2.95, whoosh(0.7, True, 1.2))
for i in range(11):
    add(sfx, 3.75 + i * 0.03, tick(2400, 0.35))
add(sfx, 4.1, whoosh(0.4, False, 0.5))
add(sfx, 4.6, pop(420, 0.9))
add(sfx, 5.6, shimmer(1.2, 1.3), pan=0.3)
add(sfx, 8.3, whoosh(0.6, False, 0.7))
add(sfx, 9.0, whoosh(0.6, True, 0.9))
add(sfx, 9.2, shimmer(0.9, 1.1))
for i in range(6):
    add(sfx, 11.1 + i * 0.13, pop(500 + i * 90, 0.55), pan=-0.3 + i * 0.12)
add(sfx, 13.15, whoosh(0.7, True, 1.2), pan=-0.3)
for i in range(3):
    add(sfx, 13.95 + i * 0.12, pop(520 + i * 100, 0.9), pan=-0.5 + 0.5 * i)
for i in range(8):
    add(sfx, 14.3 + i * 0.12, pop(700 + (i % 4) * 110, 0.7), pan=-0.6 + 0.17 * i)
add(sfx, 16.95, whoosh(0.5, True, 0.8))
for i in range(8):
    add(sfx, 17.3 + i * 0.12, swish(0.45), pan=0.3)
for k in range(6):
    add(sfx, 17.85 + k * 0.49, tick(1500, 0.9), pan=0.5)
add(sfx, 19.5, click(0.8), pan=0.6)
add(sfx, 19.65, pop(900, 0.9), pan=0.6)
add(sfx, 19.8, whoosh(0.5, False, 0.9), pan=0.5)
add(sfx, 20.0, whoosh(0.7, True, 1.3))
add(sfx, 20.4, swish(0.6))
add(sfx, 21.95, whoosh(0.4, False, 0.6))
add(sfx, 22.15, whoosh(0.5, True, 1.0))
add(sfx, 23.1, whoosh(0.5, False, 0.7))
add(sfx, 22.9, whoosh(0.8, False, 0.7))
add(sfx, 24.3, swish(0.9), pan=-0.5)
for i in range(120):
    add(sfx, 23.9 + i * (2.2 / 120) + rng.uniform(-0.003, 0.003), key(0.8), pan=rng.uniform(-0.2, 0.2))
add(sfx, 26.88, click(1.1), pan=0.2)
add(sfx, 27.0, whoosh(0.5, True, 0.9))
add(sfx, 27.6, whoosh(0.35, False, 0.7), pan=-0.5)
for t in (27.64, 28.07, 28.49, 28.92, 29.34, 29.77, 30.2, 30.66):
    add(sfx, t, tick(2400, 0.6), pan=0.3)
for i in range(9):
    add(sfx, 29.2 + i * 0.1, pop(900, 0.35), pan=0.4)
add(sfx, 31.16, chime((79, 84, 88), 0.7))
add(sfx, 31.3, whoosh(0.4, False, 0.6), pan=-0.4)
add(sfx, 32.1, click(1.1), pan=0.5)
add(sfx, 32.25, chime((84, 88, 91, 96), 1.0))
add(sfx, 32.7, whoosh(0.7, True, 0.9), pan=0.4)
add(sfx, 33.0, whoosh(0.35, False, 0.7), pan=-0.4)
add(sfx, 33.8, click(0.8), pan=0.4)
add(sfx, 34.3, chime((88, 93), 0.9), pan=0.4)
add(sfx, 34.75, whoosh(0.7, True, 1.2))
add(sfx, 35.3, swish(0.6))
for t0 in (36.0, 37.4, 38.8):
    add(sfx, t0, pop(480, 1.0))
    add(sfx, t0 - 0.05, whoosh(0.35, False, 0.6))
    for i in range(16):
        add(sfx, t0 + 0.05 + 0.9 * (i / 16) ** 1.6, tick(3000, 0.3))
add(sfx, 39.9, whoosh(0.5, True, 0.7))
add(sfx, 40.25, whoosh(0.7, True, 1.2), pan=-0.3)
add(sfx, 40.75, swish(0.8), pan=-0.4)
for i, tt in enumerate((41.1, 41.5, 41.9)):
    add(sfx, tt, pop(600 + i * 150, 0.8), pan=0.4)
add(sfx, 41.45, pop(300, 1.2), pan=-0.4)
add(sfx, 41.8, click(0.9), pan=-0.4)
add(sfx, 44.25, whoosh(0.6, True, 1.2))
add(sfx, 44.65, whoosh(0.35, False, 0.8))
add(sfx, 45.05, whoosh(0.35, False, 0.8))
add(sfx, 46.0, whoosh(0.6, True, 1.2))
for i in range(5):
    add(sfx, 46.35 + (0 if i == 0 else 0.06 + i * 0.04), pop(700 + i * 110, 0.7), pan=-0.4 + 0.2 * i)
add(sfx, 46.6, shimmer(1.3, 1.2))
for i in range(18):
    add(sfx, 47.25 + i * (0.9 / 18), key(0.7))
add(sfx, 48.2, swish(0.7))
add(sfx, 48.85, pop(520, 0.9))
add(sfx, 48.9, chime((84, 88, 91), 0.6))

sfx = reverb(sfx, 1.4, 0.16)
sfx = np.clip(sfx, -0.98, 0.98)
sf.write(os.path.join(OUT, 'sfx3.wav'), sfx.astype(np.float32), SR)
print('sfx3 ok')

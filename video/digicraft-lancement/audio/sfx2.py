"""Sound design synchronisé sur les animations."""
import numpy as np
from scipy import signal
import soundfile as sf
import os
import music as Mu
Mu.N = int(31.5 * 48000)
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
for i in range(8):
    add(sfx, 0.02 + i * 0.035, tick(1500 + i * 150, 0.6), pan=-0.5 + 0.12 * i)
add(sfx, 0.0, whoosh(0.35, False, 1.0))
add(sfx, 0.4, whoosh(0.35, False, 0.8))
for i in range(6):
    add(sfx, 0.55 + i * 0.07, pop(600 + i * 90, 0.7), pan=-0.6 + 0.24 * i)
add(sfx, 1.45, whoosh(0.4, True, 1.0))
for i in range(40):
    add(sfx, 1.7 + 1.8 * (i / 40) ** 0.6, tick(2800, 0.35), pan=0.1)
add(sfx, 3.8, whoosh(0.55, True, 1.3), pan=-0.3)
add(sfx, 4.35, whoosh(0.4, False, 0.8))
add(sfx, 5.15, whoosh(0.4, True, 1.2))
for i in range(5):
    add(sfx, 5.5 + i * 0.04, pop(700 + i * 120, 0.8), pan=-0.4 + 0.2 * i)
add(sfx, 5.6, shimmer(1.0, 1.4))
add(sfx, 6.75, whoosh(0.5, True, 1.1))
add(sfx, 7.25, swish(1.0), pan=-0.5)
for i in range(92):
    add(sfx, 7.7 + i * (1.3 / 92) + rng.uniform(-0.003, 0.003), key(0.85), pan=rng.uniform(-0.2, 0.2))
add(sfx, 9.18, click(1.1), pan=0.2)
add(sfx, 9.3, whoosh(0.45, True, 0.9))
for t in (9.8, 10.1, 10.4, 10.7, 11.0, 11.3, 11.6, 11.9):
    add(sfx, t, tick(2400, 0.6), pan=-0.4)
for i in range(9):
    add(sfx, 10.6 + i * 0.1, pop(900, 0.4), pan=0.4)
add(sfx, 12.1, chime((79, 84, 88), 0.7))
add(sfx, 12.3, whoosh(0.45, True, 1.4), pan=-0.4)
add(sfx, 13.45, whoosh(0.3, True, 1.0))
add(sfx, 14.65, whoosh(0.45, True, 1.2), pan=0.4)
add(sfx, 14.8, click(1.1), pan=0.5)
add(sfx, 15.0, chime((84, 88, 91, 96), 1.0))
add(sfx, 15.3, whoosh(0.6, True, 0.9), pan=0.4)
add(sfx, 16.35, click(0.8), pan=0.4)
add(sfx, 16.75, chime((88, 93), 0.9), pan=0.4)
add(sfx, 16.8, whoosh(0.6, True, 1.2))
for i in range(30):
    add(sfx, 17.75 + 1.4 * (i / 30) ** 1.6, tick(3000, 0.35))
for i in range(20):
    add(sfx, 20.95 + 0.9 * (i / 20) ** 1.6, tick(3200, 0.35))
add(sfx, 17.6, whoosh(0.4, False, 0.6), pan=-0.5)
add(sfx, 20.85, whoosh(0.4, False, 0.6))
add(sfx, 22.15, whoosh(0.4, False, 0.5), pan=0.5)
add(sfx, 22.9, whoosh(0.55, True, 1.2), pan=-0.3)
for i, tt in enumerate((23.35, 23.75, 24.15)):
    add(sfx, tt, pop(600 + i * 150, 0.8), pan=0.4)
add(sfx, 24.95, shimmer(0.8, 1.0))
add(sfx, 25.75, whoosh(0.5, False, 0.8))
for i in range(5):
    add(sfx, 26.2 + (0 if i == 0 else 0.07 + i * 0.03), pop(700 + i * 110, 0.7), pan=-0.4 + 0.2 * i)
add(sfx, 26.5, shimmer(1.2, 1.2))
for i in range(18):
    add(sfx, 27.35 + i * (0.75 / 18), key(0.7))
add(sfx, 28.1, swish(0.7))

sfx = reverb(sfx, 1.4, 0.16)
sfx = np.clip(sfx, -0.98, 0.98)
sf.write(os.path.join(OUT, 'sfx2.wav'), sfx.astype(np.float32), SR)
print('sfx2 ok')

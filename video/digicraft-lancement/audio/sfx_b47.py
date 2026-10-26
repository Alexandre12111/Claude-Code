"""Sound design du film « Bring your ideas to life » DigiCraft (temps réel, 54 s). Générateurs du sound design V5."""
import os
import numpy as np
import soundfile as sf

HERE = os.path.dirname(os.path.abspath(__file__))
_src = open(os.path.join(HERE, 'sfx5.py'), encoding='utf-8').read()
exec(_src.split('\ndry = np.zeros((N, 2))')[0].split('# La scène « idées »')[0])  # générateurs seulement

N = int(55.5 * SR)
Mu.N = N
dry = np.zeros((N, 2))
wet = np.zeros((N, 2))
P = lambda buf, t, x, g=1.0, pan=0.0: place(buf, t, x, g, pan)

# --- Ouverture
P(wet, 0.3, sparkle(0.9, 0.4, n=6))
P(dry, 1.9, riser(0.9, 0.55))
P(dry, 2.45, whoosh(0.9, True, 1.0, 0.0, 0.0))
P(dry, 2.75, whoosh(0.45, True, 0.5, -0.7, 0.7))
P(wet, 2.95, sparkle(1.0, 0.9))
for t in (4.62,):
    P(dry, t, whoosh(0.5, True, 0.7, 0.7, -0.7))
for i in range(5):
    P(dry, 3.6 + i * 0.18, tick(2400 + i * 80, 0.55), pan=0.3)
for i in range(4):
    P(dry, 4.85 + i * 0.367, pop(620 + i * 110, 0.6), pan=-0.4 + 0.27 * i)
P(dry, 5.75, click(1.0), pan=0.4)
P(wet, 5.8, chime((84, 88, 91), 0.55), pan=0.3)
for i in range(4):
    P(dry, 6.35 + i * 0.07, tick(3000, 0.35), pan=-0.5 + 0.33 * i)
P(dry, 5.98, whoosh(0.45, True, 0.55, -0.6, 0.6))
P(dry, 6.1, whoosh(0.5, False, 0.6, 0.0, 0.0))
P(dry, 3.1, whoosh(0.6, False, 0.6, 0.0, 0.0))
P(dry, 6.45, riser(1.0, 0.35))
P(dry, 8.2, riser(1.5, 0.6))
P(dry, 9.68, ring_swoosh(0.7, 1.0))
for i in range(5):
    P(dry, 9.68 + (0.1 + i * 0.05 if i else 0), pop(520 + i * 120, 0.7), pan=[0, 0, 0.5, 0, -0.5][i])
P(wet, 10.72, sparkle(1.0, 1.0))
P(dry, 10.72, whoosh(0.5, False, 0.5, 0.0, 0.0))
P(dry, 11.1, whoosh(0.5, True, 0.3, -0.3, 0.3))
P(dry, 11.6, pop(700, 0.5))
# --- Création
P(dry, 12.45, whoosh(0.6, False, 0.5, 0.4, -0.4))
rng2 = np.random.default_rng(9)
def typing(t0, n, cps, g=0.75, pan=0.1):
    for i in range(n):
        P(dry, t0 + i / cps + rng2.uniform(-0.006, 0.006), key(g), pan=pan + rng2.uniform(-0.12, 0.12))
P(dry, 12.6, whoosh(0.8, True, 0.35, 0.5, -0.2))
P(dry, 13.95, riser(0.6, 0.6))
P(dry, 14.45, whoosh(0.6, True, 0.9, 0.0, 0.0))
for i in range(4):
    P(dry, 14.7 + i * 0.12, pop(500 + i * 140, 0.6), pan=-0.3 + 0.2 * i)
for i in range(4):
    P(dry, 15.0 + i * 0.28, tick(2600 + i * 150, 0.6), pan=0.6)
P(wet, 15.9, chime((84, 88, 91), 0.5), pan=0.3)
P(dry, 16.7, whoosh(0.5, True, 0.6, -0.5, 0.5))
P(dry, 16.85, whoosh(1.2, False, 0.7, 0.8, -0.8))
for i in range(7):
    P(dry, 16.9 + i * (0.08 + i * 0.025), tick(1900 + i * 60, 0.45), pan=0.5 - i * 0.12)
P(dry, 18.2, whoosh(0.5, True, 0.6, 0.3, -0.3))
typing(18.45, 53, 34)
P(dry, 20.0, whoosh(0.35, True, 0.55, -0.5, 0.5))
typing(20.05, 31, 26, 0.8, -0.1)
P(dry, 21.55, whoosh(0.35, False, 0.5, 0.5, -0.5))
P(dry, 22.15, click(1.1))
P(dry, 22.3, whoosh(0.6, True, 0.6, 0.0, 0.0))
P(dry, 22.5, pop(760, 0.6), pan=-0.4)
P(dry, 22.8, pop(600, 0.6), pan=-0.4)
for i in range(5):
    P(dry, 23.25 + i * 0.32, tick(2600 + 70 * i, 0.55), pan=-0.4)
P(wet, 23.78, chime((79, 84, 88, 91), 0.8), pan=0.2)
P(wet, 23.8, sparkle(0.8, 0.6, n=10))
for i in range(6):
    P(dry, 23.8 + i * 0.06, pop(820 + i * 40, 0.35), pan=0.3)
P(dry, 24.55, whoosh(0.55, True, 0.45, 0.3, -0.4))
typing(24.95, 56, 40, 0.7, -0.2)
P(dry, 26.3, click(1.0), pan=-0.2)
P(dry, 26.35, whoosh(0.6, False, 0.45, -0.4, 0.3))
P(dry, 26.4, pop(760, 0.55), pan=-0.4)
for i in range(4):
    P(dry, 26.8 + i * 0.07, pop(700 + i * 110, 0.55), pan=0.2)
P(dry, 26.9, pop(560, 0.5), pan=-0.4)
P(dry, 27.25, whoosh(0.6, True, 0.25, -0.2, 0.5))
P(dry, 27.95, click(1.1), pan=0.6)
P(wet, 28.0, chime((84, 88, 91, 96), 1.0), pan=0.4)
P(dry, 28.05, pop(640, 0.7), pan=0.1)
P(dry, 28.4, whoosh(0.6, True, 0.7, -0.6, 0.6))
P(dry, 29.85, whoosh(0.5, False, 0.5, -0.3, 0.3))
# --- Rafale de demandes
LIST = [46, 42, 47, 28, 39]
for k, n in enumerate(LIST):
    t = 30.0 + k * 1.43
    P(dry, t - 0.1, whoosh(0.45, True, 0.4, 0.5, -0.5))
    typing(t + 0.12, min(n, 40), 38, 0.6, 0.0)
    P(dry, t + 1.18, click(0.8))
    P(wet, t + 1.22, ping(1046.5 * 2 ** (k / 12 * 2), 0.35))
P(dry, 36.85, whoosh(0.5, True, 0.6, -0.4, 0.4))
for i in range(6):
    P(dry, 37.05 + i * 0.07, pop(600 + i * 70, 0.55), pan=-0.5 + 0.2 * i)
for j in range(4):
    P(dry, 37.6 + j * 0.25, tick(2800, 0.5), pan=-0.3)
for i in range(4):
    P(dry, 38.0 + i * 0.22, pop(800 + i * 120, 0.6), pan=-0.4 + 0.27 * i)
P(wet, 38.9, chime((84, 88, 91), 0.6))
# --- Galaxie, orbe, signature
P(dry, 40.45, whoosh(1.0, False, 0.8, 0.0, 0.0))
P(dry, 40.7, riser(4.6, 0.35))
for i in range(14):
    P(wet, 41.0 + i * 0.22, ping(1318.5 * (1 + 0.06 * (i % 5)), 0.18), pan=-0.7 + 0.1 * i)
P(wet, 42.5, sparkle(1.2, 0.6, n=12))
P(dry, 42.9, whoosh(1.3, False, 0.7, 0.6, -0.6))
rng3 = np.random.default_rng(4)
for i in range(40):
    P(dry, 43.45 + i * 0.028 + rng3.uniform(0, 0.02), tick(2200 + rng3.uniform(0, 1400), 0.28), pan=rng3.uniform(-0.6, 0.6))
P(wet, 44.25, chime((79, 84, 88, 91), 0.7))
P(dry, 45.05, ring_swoosh(0.6, 0.8))
P(wet, 45.1, sparkle(1.0, 0.8))
P(dry, 45.6, whoosh(0.7, False, 0.7, 0.0, 0.0))
P(dry, 46.2, ring_swoosh(0.6, 1.0))
for i in range(5):
    P(dry, 46.2 + i * 0.04, pop(560 + i * 120, 0.65), pan=[0, 0, 0.5, 0, -0.5][i])
P(dry, 47.85, whoosh(0.7, True, 0.5, 0.3, -0.3))
P(wet, 48.45, sparkle(1.2, 0.9))
P(dry, 49.7, pop(520, 0.6))
P(wet, 50.4, sparkle(0.8, 0.4, n=6, lo=4000))
P(dry, 50.45, pop(760, 0.7))
for t in (23.0, 23.75, 26.35):
    P(dry, t, whoosh(0.35, True, 0.45, 0.4, -0.4))
for t in (3.45, 4.85, 6.4, 22.45, 26.45, 35.9, 38.15):
    P(dry, t, whoosh(0.3, True, 0.35, -0.6, -0.2))
    P(dry, t + 0.05, tick(3200, 0.45), pan=-0.4)

mix = reverb(dry, 1.2, 0.12) + reverb(wet, 2.4, 0.38)
mix = np.tanh(mix * 1.1) / 1.1
sf.write(os.path.join(OUT, 'sfx_b47.wav'), mix.astype(np.float32), SR)
print('sfx_b47 ok', round(float(np.max(np.abs(mix))), 3))

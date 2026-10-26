"""Sound design du tutoriel (temps réel de la vidéo, 60 s). Reprend les générateurs du sound design V5."""
import os
import numpy as np
import soundfile as sf

HERE = os.path.dirname(os.path.abspath(__file__))
_src = open(os.path.join(HERE, 'sfx5.py'), encoding='utf-8').read()
exec(_src.split('\ndry = np.zeros((N, 2))')[0].split('# La scène « idées »')[0])  # générateurs seulement

N = int(61.5 * SR)
Mu.N = N
dry = np.zeros((N, 2))
wet = np.zeros((N, 2))
P = lambda buf, t, x, g=1.0, pan=0.0: place(buf, t, x, g, pan)

# --- Intro
P(dry, 0.25, pop(620, 0.9))
P(dry, 0.55, whoosh(0.5, True, 0.5, -0.4, 0.4))
for i in range(13):
    P(dry, 0.55 + i * 0.03, tick(1800 + i * 90, 0.35), pan=-0.4 + 0.06 * i)
P(wet, 1.45, sparkle(0.9, 1.0))
for i in range(4):
    P(dry, 2.15 + i * 0.16, pop(600 + i * 120, 0.8), pan=-0.5 + 0.33 * i)
P(dry, 3.7, riser(0.65, 0.9))
P(dry, 4.3, ring_swoosh(0.65, 1.2))

# --- Étape 1 : décrire
P(dry, 4.95, whoosh(0.45, True, 0.5, -0.6, -0.2))
P(dry, 6.0, whoosh(0.5, False, 0.25, 0.4, 0.0))
P(dry, 6.8, tick(2600, 0.7), pan=0.2)
P(dry, 7.0, whoosh(0.6, True, 0.45))
P(dry, 7.8, click(1.0), pan=0.2)
rng2 = np.random.default_rng(5)
for i in range(110):
    P(dry, 7.95 + i * (3.6 / 110) + rng2.uniform(-0.004, 0.004), key(0.8), pan=rng2.uniform(-0.15, 0.25))
P(dry, 12.0, whoosh(0.4, False, 0.25, 0.1, 0.4))
P(dry, 12.7, click(1.1), pan=0.4)
P(wet, 12.75, chime((79, 84, 88), 0.6))
P(dry, 13.0, whoosh(0.55, True, 0.7, 0.3, -0.3))

# --- Étape 2 : construction
P(dry, 14.45, whoosh(0.45, True, 0.5, -0.6, -0.2))
P(dry, 15.1, whoosh(0.6, True, 0.35, 0.3, -0.4))
for k, t in enumerate((14.5, 15.1, 15.7, 16.3, 16.9, 17.5, 18.2)):
    P(dry, t, tick(2300 + 60 * k, 0.6), pan=-0.3)
for t in (15.8, 16.2, 16.6, 17.0):
    P(dry, t, pop(820, 0.45), pan=0.4)
P(dry, 18.0, whoosh(0.6, True, 0.35, -0.4, 0.3))
for i in range(4):
    P(dry, 18.0 + i * 0.1, pop(700 + i * 90, 0.5), pan=0.3)
for i in range(18):
    P(dry, 18.15 + 1.1 * (i / 18) ** 1.5, tick(3000, 0.22), pan=0.3)
P(wet, 19.0, chime((84, 88), 0.5), pan=-0.3)
P(dry, 21.0, pop(520, 0.7), pan=-0.3)
P(wet, 21.05, chime((79, 84, 88, 91), 0.8))
P(dry, 21.6, whoosh(0.6, False, 0.4, 0.3, -0.3))

# --- Étape 3 : affiner en discutant
P(dry, 26.45, whoosh(0.45, True, 0.5, -0.6, -0.2))
P(dry, 26.9, whoosh(0.6, True, 0.4, 0.4, -0.4))
P(dry, 27.6, click(1.0), pan=-0.2)
for i in range(38):
    P(dry, 27.8 + i * (2.2 / 38) + rng2.uniform(-0.004, 0.004), key(0.8), pan=rng2.uniform(-0.3, 0.1))
P(dry, 30.2, click(1.1), pan=-0.2)
P(dry, 30.25, whoosh(0.35, True, 0.5, -0.2, 0.2))
P(dry, 30.4, pop(560, 0.8), pan=-0.3)
for t in (30.9, 31.4):
    P(dry, t, tick(2500, 0.6), pan=-0.3)
P(dry, 31.9, whoosh(0.6, True, 0.4, -0.4, 0.4))
P(wet, 32.2, chime((84, 88), 0.5))
P(dry, 32.4, pop(420, 1.1), pan=0.1)
P(wet, 32.5, sparkle(0.8, 0.8, n=10))

# --- Étape 4 : publier et partager
P(dry, 36.05, whoosh(0.45, True, 0.5, -0.6, -0.2))
P(dry, 35.9, whoosh(0.6, True, 0.4, -0.3, 0.6))
P(dry, 37.2, click(1.1), pan=0.6)
P(wet, 37.35, chime((84, 88, 91, 96), 1.0), pan=0.4)
P(dry, 37.6, pop(640, 0.7), pan=0.2)
P(dry, 38.9, whoosh(0.6, False, 0.4, 0.4, 0.0))
P(dry, 39.2, pop(500, 0.9))
for i, t in enumerate((39.6, 39.9, 40.2)):
    P(dry, t, pop(680 + i * 120, 0.5), pan=0.2)
P(dry, 41.75, click(1.0), pan=0.3)
P(wet, 41.85, chime((88, 93), 0.6), pan=0.3)

# --- Bonus : mobile
P(dry, 45.5, whoosh(0.7, True, 0.8, -0.5, 0.5))
P(dry, 45.9, whoosh(0.8, True, 0.6, 0.2, 0.6))
P(wet, 48.2, ping(1318.5, 0.9), pan=0.5)
P(dry, 49.8, click(0.9), pan=0.5)
P(wet, 50.6, chime((84, 88, 91), 0.8), pan=0.4)

# --- Fin
P(dry, 52.65, ring_swoosh(0.6, 1.1))
P(dry, 53.0, pop(620, 0.9))
for i in range(9):
    P(dry, 53.15 + i * 0.035, tick(1800 + i * 110, 0.35), pan=-0.4 + 0.1 * i)
for i in range(4):
    P(dry, 53.9 + i * 0.22, pop(600 + i * 130, 0.75), pan=-0.5 + 0.33 * i)
P(wet, 55.2, sparkle(0.9, 0.8))
P(dry, 55.7, pop(520, 0.9))
P(wet, 56.4, sparkle(0.7, 0.5, n=6, lo=4000))

mix = reverb(dry, 1.2, 0.12) + reverb(wet, 2.4, 0.38)
mix = np.tanh(mix * 1.1) / 1.1
sf.write(os.path.join(OUT, 'sfx_tuto.wav'), mix.astype(np.float32), SR)
print('sfx_tuto ok', round(float(np.max(np.abs(mix))), 3))

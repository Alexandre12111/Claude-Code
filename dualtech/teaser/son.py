"""Bande son originale du teaser DualTech 2026 (45,5 s, 48 kHz, stéréo), entièrement synthétisée : aucun droit tiers.

Tempo 120 battements par minute (un temps = 0,5 s), calé sur les coupes du montage :
nappe grave en la mineur (la, fa, do, sol), pulsation rythmique dès 4 s, batterie électronique de 9 à 41 s,
montées avant les grandes sections, impacts sur chaque coupe, signes sonores sur les textes déchiffrés.
Usage : python3 son.py  (écrit son.wav)
"""
import wave
import numpy as np

SR = 48000
DUREE = 45.5
N = int(SR * DUREE)
t = np.arange(N) / SR
rng = np.random.default_rng(26112026)
L = np.zeros(N); R = np.zeros(N)


def ajoute(sig, debut, gain=1.0, pan=0.0):
    i = int(debut * SR)
    if i >= N: return
    sig = sig[: N - i] * gain
    L[i:i + len(sig)] += sig * np.sqrt(.5 * (1 - pan))
    R[i:i + len(sig)] += sig * np.sqrt(.5 * (1 + pan))


def env(n, a=.005, r=.2):
    x = np.arange(n) / SR
    return np.minimum(1, x / a) * np.exp(-x / r)


def passe_bas(sig, fc):
    S = np.fft.rfft(sig); f = np.fft.rfftfreq(len(sig), 1 / SR)
    return np.fft.irfft(S / np.sqrt(1 + (f / fc) ** 4), len(sig))


def passe_haut(sig, fc):
    S = np.fft.rfft(sig); f = np.fft.rfftfreq(len(sig), 1 / SR)
    return np.fft.irfft(S / np.sqrt(1 + (fc / np.maximum(f, 1)) ** 4), len(sig))


def note(m):  # numéro MIDI vers fréquence
    return 440 * 2 ** ((m - 69) / 12)


# ---------- nappe : accords de quatre temps (2 s), la mineur, fa, do, sol ----------
ACCORDS = [(45, 52, 57, 60, 64), (41, 48, 53, 57, 60), (48, 55, 60, 64, 67), (43, 50, 55, 59, 62)]
nappe = np.zeros(N)
for k in range(int(DUREE / 2) + 1):
    a = k * 2.0; i0 = int(a * SR); n = min(int(2.2 * SR), N - i0)
    if n <= 0: break
    tt = np.arange(n) / SR
    acc = ACCORDS[k % 4] if a >= 9 else ACCORDS[0]
    s = sum(np.sin(2 * np.pi * note(m) * tt * (1 + d)) for m in acc for d in (-.003, .003)) / 10
    fen = np.minimum(1, tt / .25) * np.minimum(1, (2.2 - tt) / .25)
    nappe[i0:i0 + n] += s * fen
nappe *= np.minimum(1, t / 3.0) * (0.55 + .45 * np.clip((t - 9) / 8, 0, 1))
nappe *= np.where(t > 41, np.exp(-(t - 41) / 3.5) * 1.4, 1)
nappe = passe_bas(nappe, 1800)
ajoute(nappe, 0, .3, -.15); ajoute(np.roll(nappe, int(.011 * SR)), 0, .3, .15)

# sous grave continu (la 55 Hz) qui respire
sub = np.sin(2 * np.pi * 55 * t) * (.5 + .5 * np.sin(2 * np.pi * .25 * t)) * np.minimum(1, t / 4) * np.where(t > 43, np.exp(-(t - 43) / 1.5), 1)
ajoute(sub, 0, .08)

# ---------- pulsation et charleston ----------
def tic(longueur=.04, fc=7000):
    n = int(longueur * SR); b = rng.standard_normal(n)
    return passe_haut(b, fc) * env(n, .001, longueur / 3)

for k in range(int(4 / .25), int(41 / .25)):
    a = k * .25
    if 31 <= a < 33: continue
    g = .10 if a < 9 else (.16 if k % 2 else .09)
    ajoute(tic(.05 if k % 2 else .03), a, g, .35 if k % 4 == 1 else -.35)

# ---------- grosse caisse et caisse claire (9 à 41 s) ----------
def kick():
    n = int(.45 * SR); tt = np.arange(n) / SR
    f = 45 + 110 * np.exp(-tt / .035)
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-tt / .16)

def caisse():
    n = int(.3 * SR); tt = np.arange(n) / SR
    corps = np.sin(2 * np.pi * 185 * tt) * np.exp(-tt / .05)
    bruit = passe_haut(rng.standard_normal(n), 1800) * np.exp(-tt / .09)
    return corps * .5 + bruit * .6

K = kick(); C = caisse()
for k in range(int(9 / .5), int(41 / .5)):
    a = k * .5
    if 31 <= a < 33: continue                      # respiration sur le Prix
    ajoute(K, a, 1.1)
    if k % 2 == 1 and a >= 17: ajoute(C, a, .45)

# ---------- basse en croches (17 à 41 s) ----------
for k in range(int(17 / .25), int(41 / .25)):
    a = k * .25
    if 31 <= a < 33: continue
    racine = ACCORDS[int(a // 2) % 4][0] - 12
    n = int(.22 * SR); tt = np.arange(n) / SR
    sc = np.sign(np.sin(2 * np.pi * note(racine) * tt)) * .5 + np.sin(2 * np.pi * note(racine) * tt)
    ajoute(passe_bas(sc * env(n, .004, .09), 900), a, .16)

# ---------- montées avant 9, 17 et 41 s ----------
def montee(d):
    n = int(d * SR); tt = np.arange(n) / SR
    b = rng.standard_normal(n)
    morceaux = np.array_split(b, 24)
    out = np.concatenate([passe_bas(m, 300 + 9000 * (i / 23) ** 2) for i, m in enumerate(morceaux)])
    ton = np.sin(2 * np.pi * np.cumsum(200 + 900 * (tt / d) ** 2) / SR) * .25
    return (out * .6 + ton) * (tt / d) ** 2

for fin, d in ((9, 2.0), (17, 2.0), (41, 2.5)):
    ajoute(montee(d), fin - d, .32)

# ---------- impacts sur les coupes ----------
def impact(longueur=1.8, force=1.0):
    n = int(longueur * SR); tt = np.arange(n) / SR
    boom = np.sin(2 * np.pi * np.cumsum(38 + 60 * np.exp(-tt / .08)) / SR) * np.exp(-tt / (longueur / 3))
    souffle = passe_bas(rng.standard_normal(n), 2500) * np.exp(-tt / .35)
    return (boom + souffle * .35) * force

for c in (4, 9, 17, 24, 31, 36):
    ajoute(impact(1.6, .8), c, .7)
ajoute(impact(4.0, 1.3), 41, .85)

# ---------- souffles des volets (transition rouge puis marine) ----------
def souffle(d=.9):
    n = int(d * SR); tt = np.arange(n) / SR
    b = passe_bas(passe_haut(rng.standard_normal(n), 400), 5000)
    return b * np.sin(np.pi * tt / d) ** 2

for i, c in enumerate((4, 9, 17, 24, 31, 36, 41)):
    ajoute(souffle(), c - .45, .22, -.6 + i * .2)

# ---------- signes sonores : textes déchiffrés, compteurs, cible verrouillée ----------
def bip(f=2400, d=.03):
    n = int(d * SR); tt = np.arange(n) / SR
    return np.sin(2 * np.pi * f * tt) * env(n, .001, d / 3)

for k in range(int((3.0 - .9) / .06)):
    ajoute(bip(1800 + rng.integers(0, 6) * 300), .9 + k * .06, .06, rng.uniform(-.5, .5))
for k in range(int((19.2 - 17.2) / .08)):
    ajoute(bip(3200, .02), 17.2 + k * .08, .05, .2)
for k in range(10):
    ajoute(bip(2600 + k * 120, .025), 37.6 + k * .1, .07, .3)
ajoute(bip(1320, .25), 37.6, .12); ajoute(bip(1760, .35), 37.85, .12)
for k in range(6):                                   # cartes des intervenants
    ajoute(souffle(.35), 24.5 + k, .12, .5)

# ---------- signature finale : accord tenu et carillon ----------
for m, a in ((69, 41.2), (76, 41.5), (81, 41.8), (84, 42.1)):
    n = int(3.6 * SR); tt = np.arange(n) / SR
    s = (np.sin(2 * np.pi * note(m) * tt) + .3 * np.sin(2 * np.pi * note(m) * 2.01 * tt)) * env(n, .003, 1.2)
    ajoute(s, a, .09, (m - 76) / 20)

# ---------- espace : réverbération par convolution ----------
def reverb(x, duree=2.2, mix=.22):
    n = int(duree * SR); ir = rng.standard_normal(n) * np.exp(-np.arange(n) / SR / (duree / 5))
    ir = passe_bas(ir, 6000); ir /= np.sqrt((ir ** 2).sum())
    taille = 1 << int(np.ceil(np.log2(len(x) + n)))
    w = np.fft.irfft(np.fft.rfft(x, taille) * np.fft.rfft(ir, taille), taille)[: len(x)]
    return x * (1 - mix) + w * mix * 3

L = reverb(L); R = reverb(R)

# ---------- finition : fondu, compression douce, normalisation ----------
fondu = np.minimum(1, t / .05) * np.where(t > 44.7, np.clip((45.5 - t) / .8, 0, 1), 1)
L *= fondu; R *= fondu
for c in (L, R):
    c[:] = np.tanh(c * 1.1) / np.tanh(1.1)
crete = max(np.abs(L).max(), np.abs(R).max())
L *= .89 / crete; R *= .89 / crete

st = (np.stack([L, R], 1) * 32767).astype(np.int16)
with wave.open("son.wav", "wb") as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(st.tobytes())
print("son.wav écrit", f"{DUREE} s")

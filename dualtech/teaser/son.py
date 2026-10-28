"""Bande son du teaser DualTech 2026, version 2 (48 s, 48 kHz, stéréo) : musique originale synthétisée + voix off.

Musique au tempo de 120 (un temps = 0,5 s) calée sur le montage : ouverture et impact sur le logo, accompagnement
discret sous le but et l'objectif du salon, rythmique pleine sur les filières et les chiffres, respiration sur le Prix,
reprise sur le lieu et impact final sur la signature. La musique baisse automatiquement quand la voix parle.
Usage : python3 son.py  (écrit son.wav, plus musique.wav et voix-mix.wav)
"""
import json, wave
from pathlib import Path
import numpy as np

ICI = Path(__file__).resolve().parent
SR = 48000
DUREE = 48.0
N = int(SR * DUREE)
t = np.arange(N) / SR
rng = np.random.default_rng(26112026)
DEBUTS = {"v1": 1.0, "v2": 4.6, "v3": 10.0, "v4": 14.4, "v5": 19.6, "v6": 24.7, "v7": 30.4, "v8": 35.9, "v9": 38.6, "v10": 43.7}


def piste():
    return np.zeros(N), np.zeros(N)

ML, MR = piste()


def ajoute(sig, debut, gain=1.0, pan=0.0, L=None, R=None):
    L = ML if L is None else L; R = MR if R is None else R
    i = int(debut * SR)
    if i >= N or i + len(sig) <= 0: return
    if i < 0: sig = sig[-i:]; i = 0
    sig = sig[: N - i] * gain
    L[i:i + len(sig)] += sig * np.sqrt(.5 * (1 - pan)); R[i:i + len(sig)] += sig * np.sqrt(.5 * (1 + pan))


def env(n, a=.005, r=.2):
    x = np.arange(n) / SR
    return np.minimum(1, x / a) * np.exp(-x / r)


def filtre(sig, bas=None, haut=None):
    S = np.fft.rfft(sig); f = np.fft.rfftfreq(len(sig), 1 / SR); g = np.ones_like(f)
    if bas: g /= np.sqrt(1 + (f / bas) ** 4)
    if haut: g /= np.sqrt(1 + (haut / np.maximum(f, 1)) ** 4)
    return np.fft.irfft(S * g, len(sig))


def note(m):
    return 440 * 2 ** ((m - 69) / 12)


def zone(a, b):
    return (t >= a) & (t < b)

# ---------- nappe : la mineur, fa, do, sol (deux mesures par accord) ----------
ACCORDS = [(45, 52, 57, 60, 64), (41, 48, 53, 57, 60), (48, 55, 60, 64, 67), (43, 50, 55, 59, 62)]
nappe = np.zeros(N)
for k in range(int(DUREE / 2) + 1):
    a = k * 2.0; i0 = int(a * SR); n = min(int(2.2 * SR), N - i0)
    if n <= 0: break
    tt = np.arange(n) / SR
    acc = ACCORDS[k % 4] if a >= 4 else ACCORDS[0]
    s = sum(np.sin(2 * np.pi * note(m) * tt * (1 + d)) for m in acc for d in (-.003, .003)) / 10
    nappe[i0:i0 + n] += s * np.minimum(1, tt / .25) * np.minimum(1, (2.2 - tt) / .25)
nappe = filtre(nappe, bas=1800) * np.minimum(1, t / 1.5) * np.where(t > 44, np.exp(-(t - 44) / 2.5), 1)
ajoute(nappe, 0, .3, -.15); ajoute(np.roll(nappe, int(.011 * SR)), 0, .3, .15)
ajoute(np.sin(2 * np.pi * 55 * t) * np.minimum(1, t / 2) * np.where(t > 45, np.exp(-(t - 45) / 1.2), 1), 0, .07)

# ---------- percussions ----------
def tic(longueur=.04, fc=7000):
    n = int(longueur * SR); return filtre(rng.standard_normal(n), haut=fc) * env(n, .001, longueur / 3)

def kick():
    n = int(.45 * SR); tt = np.arange(n) / SR
    return np.sin(2 * np.pi * np.cumsum(45 + 110 * np.exp(-tt / .035)) / SR) * np.exp(-tt / .16)

def caisse():
    n = int(.3 * SR); tt = np.arange(n) / SR
    return np.sin(2 * np.pi * 185 * tt) * np.exp(-tt / .05) * .5 + filtre(rng.standard_normal(n), haut=1800) * np.exp(-tt / .09) * .6

K, C = kick(), caisse()
PLEIN = [(24.5, 35.75), (38.5, 43.5)]          # rythmique pleine
DOUX = [(4.5, 24.5)]                            # accompagnement sous la voix
for k in range(int(4.5 / .25), int(43.5 / .25)):
    a = k * .25
    if 35.75 <= a < 38.5: continue
    plein = any(x <= a < y for x, y in PLEIN)
    ajoute(tic(.05 if k % 2 else .03), a, (.15 if k % 2 else .08) if plein else .06, .35 if k % 4 == 1 else -.35)
for k in range(int(4.5 / .5), int(43.5 / .5)):
    a = k * .5
    if 35.75 <= a < 38.5: continue
    plein = any(x <= a < y for x, y in PLEIN)
    if plein:
        ajoute(K, a, 1.1)
        if k % 2: ajoute(C, a, .45)
    elif a >= 14 and k % 2 == 0:
        ajoute(K, a, .55)                       # pulsation discrète à partir de l'objectif

# ---------- basse en croches sur les sections pleines ----------
for x, y in PLEIN:
    for k in range(int(x / .25), int(y / .25)):
        a = k * .25; racine = ACCORDS[int(a // 2) % 4][0] - 12
        n = int(.22 * SR); tt = np.arange(n) / SR
        sc = np.sign(np.sin(2 * np.pi * note(racine) * tt)) * .5 + np.sin(2 * np.pi * note(racine) * tt)
        ajoute(filtre(sc * env(n, .004, .09), bas=900), a, .16)

# ---------- montées, impacts, souffles ----------
def montee(d):
    n = int(d * SR); tt = np.arange(n) / SR
    out = np.concatenate([filtre(m, bas=300 + 9000 * (i / 23) ** 2) for i, m in enumerate(np.array_split(rng.standard_normal(n), 24))])
    ton = np.sin(2 * np.pi * np.cumsum(200 + 900 * (tt / d) ** 2) / SR) * .25
    return (out * .6 + ton) * (tt / d) ** 2

def impact(longueur=1.6, force=1.0):
    n = int(longueur * SR); tt = np.arange(n) / SR
    boom = np.sin(2 * np.pi * np.cumsum(38 + 60 * np.exp(-tt / .08)) / SR) * np.exp(-tt / (longueur / 3))
    return (boom + filtre(rng.standard_normal(n), bas=2500) * np.exp(-tt / .35) * .35) * force

def souffle(d=.8):
    n = int(d * SR); tt = np.arange(n) / SR
    return filtre(rng.standard_normal(n), bas=5000, haut=400) * np.sin(np.pi * tt / d) ** 2

ajoute(montee(1.0), 0.0, .3)                        # rai de lumière avant le logo
for fin, d in ((24.5, 2.0), (43.5, 2.5)):
    ajoute(montee(d), fin - d, .3)
for c in (4.5, 14, 17.5, 30.25, 35.75, 38.5):
    ajoute(impact(1.6, .8), c, .7)
ajoute(impact(2.5, 1.1), 1.0, .8); ajoute(impact(1.4, .7), 24.5, .7); ajoute(impact(4.0, 1.3), 43.5, .85)
for i, c in enumerate((4.5, 14, 24.5, 30.25, 35.75, 38.5, 43.5)):
    ajoute(souffle(), c - .4, .22, -.6 + i * .2)

# ---------- signes sonores ----------
def bip(f=2400, d=.03):
    n = int(d * SR); tt = np.arange(n) / SR
    return np.sin(2 * np.pi * f * tt) * env(n, .001, d / 3)

for k in range(int(1.0 / .06)):
    ajoute(bip(1800 + rng.integers(0, 6) * 300), 2.5 + k * .06, .05, rng.uniform(-.5, .5))
for k in range(16):
    ajoute(bip(1800 + rng.integers(0, 6) * 300), 39.8 + k * .06, .05, rng.uniform(-.5, .5))
for a in (6.0, 7.3, 8.2, 9.1, 20.6, 22.0, 23.4):      # panneaux et nœuds qui apparaissent
    ajoute(bip(1320, .12), a, .06, .3); ajoute(souffle(.4), a - .2, .08, -.3)
for k in range(int(1.1 / .07)):
    ajoute(bip(3200, .02), 30.45 + k * .07, .04, .2); ajoute(bip(3000, .02), 31.6 + k * .07, .04, -.2)
for k in range(20):                                  # chaque filière
    ajoute(tic(.03, 5000), 24.75 + k * .25, .05, (-1) ** k * .4)
for m, a in ((76, 35.9), (81, 36.15), (84, 36.4), (88, 36.65)):   # carillon du Prix
    n = int(2.0 * SR); tt = np.arange(n) / SR
    ajoute((np.sin(2 * np.pi * note(m) * tt) + .3 * np.sin(2 * np.pi * note(m) * 2.01 * tt)) * env(n, .003, .8), a, .07, (m - 82) / 15)
for m, a in ((69, 43.7), (76, 44.0), (81, 44.3), (84, 44.6)):     # signature
    n = int(3.4 * SR); tt = np.arange(n) / SR
    ajoute((np.sin(2 * np.pi * note(m) * tt) + .3 * np.sin(2 * np.pi * note(m) * 2.01 * tt)) * env(n, .003, 1.2), a, .08, (m - 76) / 20)

# ---------- réverbération de la musique ----------
def reverb(x, duree=2.2, mix=.22):
    n = int(duree * SR); ir = filtre(rng.standard_normal(n) * np.exp(-np.arange(n) / SR / (duree / 5)), bas=6000); ir /= np.sqrt((ir ** 2).sum())
    taille = 1 << int(np.ceil(np.log2(len(x) + n)))
    return x * (1 - mix) + np.fft.irfft(np.fft.rfft(x, taille) * np.fft.rfft(ir, taille), taille)[: len(x)] * mix * 3

ML, MR = reverb(ML), reverb(MR)

# ---------- voix off : rééchantillonnage, égalisation, compression douce, légère ambiance ----------
VL, VR = piste()
for v in json.loads((ICI / "voix" / "voix.json").read_text()):
    with wave.open(str(ICI / "voix" / f"{v['id']}.wav")) as w:
        sr = w.getframerate(); a = np.frombuffer(w.readframes(w.getnframes()), np.int16) / 32768
    a = np.interp(np.arange(int(len(a) * SR / sr)) / SR, np.arange(len(a)) / sr, a)
    a = filtre(a, haut=90, bas=11000)
    a = np.tanh(a * 2.2) / np.tanh(2.2)
    ajoute(a, DEBUTS[v["id"]], 1.0, 0, VL, VR)
voix = (VL + VR) / 2
voix = reverb(voix, 1.2, .1)
VL = VR = voix

# ---------- la musique s'efface sous la voix (atténuation d'environ 9 dB) ----------
niveau = np.abs(voix)
fen = int(.03 * SR); niveau = np.convolve(niveau, np.ones(fen) / fen, mode="same")
pres = np.clip(niveau / (niveau.max() * .15), 0, 1)
lisse = np.zeros_like(pres); g = 0.0
attaque, relache = 1 - np.exp(-1 / (.04 * SR)), 1 - np.exp(-1 / (.35 * SR))
for i in range(0, N, 64):                       # enveloppe par blocs de 64 échantillons
    cible = pres[i:i + 64].max()
    g += (cible - g) * (1 - (1 - (attaque if cible > g else relache)) ** 64)
    lisse[i:i + 64] = g
gain_musique = 1 - .5 * lisse

def normalise(x, c=.89):
    return x * c / np.abs(x).max()

musique_L, musique_R = normalise(ML), normalise(MR)
mixL = musique_L * gain_musique * .62 + normalise(voix) * .95
mixR = musique_R * gain_musique * .62 + normalise(voix) * .95
fondu = np.minimum(1, t / .05) * np.where(t > 47.2, np.clip((48 - t) / .8, 0, 1), 1)
mixL, mixR = mixL * fondu, mixR * fondu
for c in (mixL, mixR): c[:] = np.tanh(c * 1.1) / np.tanh(1.1)
crete = max(np.abs(mixL).max(), np.abs(mixR).max()); mixL *= .89 / crete; mixR *= .89 / crete

def ecrit(nom, L, R):
    st = (np.stack([L, R], 1) * 32767).astype(np.int16)
    with wave.open(str(ICI / nom), "wb") as w:
        w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(st.tobytes())

ecrit("son.wav", mixL, mixR)
ecrit("musique.wav", musique_L * fondu, musique_R * fondu)
ecrit("voix-mix.wav", normalise(voix) * fondu, normalise(voix) * fondu)
print("son.wav, musique.wav et voix-mix.wav écrits", f"{DUREE} s")

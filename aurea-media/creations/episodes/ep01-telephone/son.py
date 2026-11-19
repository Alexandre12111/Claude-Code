"""Bande son de l'épisode 1 (48 kHz, stéréo) : musique originale synthétisée, bruitages calés sur l'image, voix.

Tous les repères viennent de calage.json (mêmes formules que scenes.js), donc la bande son suit la voix réelle.
Musique : 100 battements par minute. Tension (la mineur) pendant le problème, coupure nette sur le déclic,
puis groove lumineux (do majeur) pendant la solution. La musique baisse automatiquement sous la voix.
Usage : python3 son.py  (écrit son.wav ; lit voix-mix.wav)
"""
import json, wave
from pathlib import Path
import numpy as np

ICI = Path(__file__).resolve().parent
SR = 48000
CAL = json.loads((ICI / "calage.json").read_text())
PH = CAL["phrases"]
DUREE = CAL["duree"]
N = int(DUREE * SR)
t = np.arange(N) / SR
rng = np.random.default_rng(1911)
P = lambda i: PH[i]["debut"]
F = lambda i: PH[i]["fin"]
Mo = lambda i, k: PH[i]["mots"][min(k, len(PH[i]["mots"]) - 1)]["debut"]
FIN_TEXTE = PH["v11"]["fin"] + .5
tWhip = P("v2") - .14
tCh, tCc, tSo, tSi, tAp = P("v5") - .12, P("v6") - .12, P("v7") - .18, P("v9") - .3, P("v10") - .35

ML, MR = np.zeros(N), np.zeros(N)      # musique
SL, SRt = np.zeros(N), np.zeros(N)     # bruitages


def ajoute(sig, debut, gain=1., pan=0., musique=False):
    L, R = (ML, MR) if musique else (SL, SRt)
    i = int(debut * SR)
    if i >= N or i + len(sig) <= 0: return
    if i < 0: sig = sig[-i:]; i = 0
    sig = sig[: N - i] * gain
    L[i:i + len(sig)] += sig * np.sqrt(.5 * (1 - pan)); R[i:i + len(sig)] += sig * np.sqrt(.5 * (1 + pan))


def env(n, a=.005, r=.2, courbe=4.):
    x = np.arange(n) / SR
    e = np.minimum(1, x / max(a, 1e-4)) * np.exp(-courbe * np.maximum(0, x - a) / max(r, 1e-4))
    return e


def sinus(f, d, phase=0):
    x = np.arange(int(d * SR)) / SR
    return np.sin(2 * np.pi * f * x + phase)


def bruit(d):
    return rng.standard_normal(int(d * SR))


def passe_bas(x, fc):
    a = np.exp(-2 * np.pi * fc / SR); y = np.zeros_like(x); s = 0.
    for i in range(len(x)): s = (1 - a) * x[i] + a * s; y[i] = s
    return y


def passe_bande(x, fc, q=2.):
    # biquad passe-bande
    w0 = 2 * np.pi * fc / SR; al = np.sin(w0) / (2 * q)
    b0, b1, b2, a0, a1, a2 = al, 0, -al, 1 + al, -2 * np.cos(w0), 1 - al
    y = np.zeros_like(x); x1 = x2 = y1 = y2 = 0.
    for i in range(len(x)):
        y0 = (b0 * x[i] + b1 * x1 + b2 * x2 - a1 * y1 - a2 * y2) / a0
        x2, x1, y2, y1 = x1, x[i], y1, y0; y[i] = y0
    return y


NOTE = lambda n: 440 * 2 ** ((n - 69) / 12)

# ================= Instruments =================
def kick(g=1.):
    d = .35; x = np.arange(int(d * SR)) / SR
    f = 50 + 90 * np.exp(-x * 30)
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-x * 9) * g


def charley(g=1., ouvert=False):
    d = .25 if ouvert else .06
    b = np.diff(bruit(d), prepend=0)
    return b * env(len(b), .001, .12 if ouvert else .025) * .25 * g


def clap(g=1.):
    b = passe_bande(bruit(.25), 1500, 1.2)
    e = np.zeros(len(b))
    for o in (0, .012, .024): e[int(o * SR):] += env(len(b) - int(o * SR), .001, .04 if o < .02 else .15)
    return b * e * .9 * g


def basse(n, d, g=1.):
    x = np.arange(int(d * SR)) / SR; f = NOTE(n)
    s = np.sin(2 * np.pi * f * x) + .35 * np.sin(4 * np.pi * f * x) + .12 * np.sign(np.sin(2 * np.pi * f * x))
    return passe_bas(s * env(len(x), .008, d * .7, 2.2), 900) * g


def pluck(n, d=.6, g=1., brillance=1.):
    x = np.arange(int(d * SR)) / SR; f = NOTE(n)
    s = sum(np.sin(2 * np.pi * f * k * x) * np.exp(-x * (3 + k * 2.5 / brillance)) / k for k in range(1, 6))
    return s * env(len(x), .002, d * .5, 3) * g


def nappe(notes, d, g=1.):
    x = np.arange(int(d * SR)) / SR
    s = sum(np.sin(2 * np.pi * NOTE(n) * x * (1 + .002 * k)) + .3 * np.sin(4 * np.pi * NOTE(n) * x) for k, n in enumerate(notes))
    a = np.minimum(1, x / .4) * np.minimum(1, (d - x) / .5)
    return passe_bas(s, 1800) * a * g / len(notes)


# ================= Musique =================
BPM = 100; B = 60 / BPM
fin_tension = P("v8")  # coupure nette sur le déclic
debut_solution = tSi + .05
# 1) tension : basse pulsée en la mineur, charleston serré, accords sombres
progression = [(45, [57, 60, 64]), (41, [57, 60, 65]), (43, [55, 59, 62]), (40, [55, 59, 64])]
mesure = 0
x0 = .0
while x0 < fin_tension - .05:
    racine, acc = progression[mesure % 4]
    ajoute(nappe(acc, min(4 * B, fin_tension - x0) + .05, .16), x0, musique=True)
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= fin_tension: break
        ajoute(basse(racine - 12 + (12 if k % 4 == 3 else 0), B / 2 * .9, .42), tt, musique=True)
        ajoute(charley(.55 if k % 2 else .35), tt, pan=.3, musique=True)
        if k in (0, 4) and tt > tWhip: ajoute(kick(.75), tt, musique=True)
    x0 += 4 * B; mesure += 1
# 2) solution : do majeur, plus lumineux et dansant
progression2 = [(48, [60, 64, 67, 71]), (45, [57, 60, 64, 67]), (41, [57, 60, 65, 69]), (43, [55, 59, 62, 67])]
mesure = 0
x0 = debut_solution
mel = [72, 76, 79, 76, 74, 72, 71, 74]
while x0 < DUREE - .3:
    racine, acc = progression2[mesure % 4]
    ajoute(nappe(acc, 4 * B + .1, .15), x0, musique=True)
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= DUREE - .3: break
        ajoute(basse(racine - 12 + (7 if k % 4 == 2 else 0), B / 2 * .9, .4), tt, musique=True)
        ajoute(charley(.5 if k % 2 else .3, ouvert=(k % 4 == 3)), tt, pan=.35, musique=True)
        if k in (0, 3, 4): ajoute(kick(.8), tt, musique=True)
        if k in (2, 6): ajoute(clap(.5), tt, pan=-.15, musique=True)
        if k % 2 == 0: ajoute(pluck(mel[(k // 2 + mesure * 2) % 8], .5, .22), tt + B / 4, pan=-.3 + .2 * (k % 4), musique=True)
    x0 += 4 * B; mesure += 1

# ================= Bruitages =================
def vibreur(d, g=1.):
    x = np.arange(int(d * SR)) / SR
    s = np.sign(np.sin(2 * np.pi * 165 * x)) * .5 + np.sin(2 * np.pi * 330 * x) * .3
    return passe_bas(s, 1200) * (.6 + .4 * np.sin(2 * np.pi * 28 * x)) * np.minimum(1, x / .01) * np.minimum(1, (d - x) / .02) * g


def pop(f=900, g=1.):
    d = .09; x = np.arange(int(d * SR)) / SR
    return np.sin(2 * np.pi * np.cumsum(f * (1 + 1.6 * np.exp(-x * 60))) / SR) * env(len(x), .001, .04) * g


def souffle(d, f0=300, f1=3000, g=1.):
    b = bruit(d); x = np.arange(len(b)) / SR
    y = np.zeros_like(b); blocs = 16
    for i in range(blocs):
        s, e = i * len(b) // blocs, (i + 1) * len(b) // blocs
        fc = f0 * (f1 / f0) ** (i / blocs)
        y[s:e] = passe_bande(b[max(0, s - 200):e], fc, 1.)[-(e - s):]
    return y * np.sin(np.pi * x / d) ** 2 * g


def tic(g=1.):
    b = passe_bande(bruit(.03), 3500, 4); return b * env(len(b), .0005, .01) * g


def carillon(notes, g=1.):
    s = np.zeros(int(1.6 * SR))
    for k, n in enumerate(notes):
        p = pluck(n, 1.2, 1., 2.5); i = int(k * .07 * SR); s[i:i + len(p)] += p[: len(s) - i]
    return s * g


# Scène 1 : salves de vibration et sonnerie discrète, compteur
u = 0.
while u < tWhip:
    d = min(.55, tWhip - u)
    ajoute(vibreur(d, .5), u)
    u += .9
for k, n in enumerate([NOTE(76), NOTE(79), NOTE(83), NOTE(79)] * 4):
    tt = .1 + k * .19
    if tt < tWhip - .1: ajoute(sinus(n, .16) * env(int(.16 * SR), .003, .06) * .1, tt, pan=-.2)
t14 = Mo("v1", 4); pas = (t14 - .35) / 5
for k in range(5): ajoute(pop(700 + 120 * k, .45 if k < 4 else .7), .35 + k * pas)
ajoute(souffle(.5, 200, 4000, 1.1), tWhip - .12)
ajoute(kick(1.), tWhip + .22)
# Scènes 2 à 4 : vibration dans la poche, gouttes d'enduit, bulles
u = tWhip + .3
while u < P("v3") + .3:
    ajoute(vibreur(.32, .22), u, pan=.1); u += .5
for i in range(6): ajoute(pop(260 + 30 * i, .25), P("v3") + .2 + i * .22 + .5, pan=.2)
for i in range(5): ajoute(pop(1100 + 90 * i, .5), P("v4") - .1 + i * .5, pan=-.4 if i % 2 == 0 else .4)
for i in range(5): ajoute(pop(1600, .25), P("v5") - .22 + i * .015, pan=(i - 2) * .2)
# Scène 5 : passage en iris, horloge qui s'emballe
ajoute(souffle(.4, 3000, 400, .6), tCh)
u = tCh + .1
while u < tCc:
    ajoute(tic(.5), u, pan=-.3); u += .11
# Scène 6 : glissement, tonalité d'appel, raccroché, tap
ajoute(souffle(.42, 500, 2500, .7), tCc)
ra = Mo("v6", 4) + .25
u = tCc + .3
while u < ra - .2:
    ajoute((sinus(440, .45)) * env(int(.45 * SR), .01, .4, .5) * .12, u); u += 1.0
for k in range(3): ajoute(sinus(480, .12) * env(int(.12 * SR), .005, .1, .5) * .14, ra - .1 + k * .17)
ajoute(pop(1900, .6), Mo("v6", 8))
# Scènes 7 et 8 : ambiance du soir, scratch sur le déclic, montée vers le site
ajoute(passe_bas(bruit(tSi - tSo), 300) * .05, tSo)
ajoute(sinus(60, tSi - tSo) * .02, tSo)
v8 = P("v8")
x = np.arange(int(.32 * SR)) / SR
scratch = passe_bande(bruit(.32), 1, 1) * 0 + np.sin(2 * np.pi * np.cumsum(900 * (1 + .8 * np.sin(2 * np.pi * 9 * x))) / SR) * .3 + passe_bande(bruit(.32), 1800, 1.5) * .9
ajoute(scratch * env(len(x), .005, .2, 3), v8 - .03, 1.)
mont = np.arange(int((tSi - v8 - .2) * SR)) / SR
ajoute(np.sin(2 * np.pi * np.cumsum(300 + 900 * (mont / mont[-1]) ** 2) / SR) * (mont / mont[-1]) ** 2 * .12 + souffle(len(mont) / SR, 400, 6000, .4)[:len(mont)], v8 + .2)
ajoute(kick(1.2), tSi + .2); ajoute(carillon([72, 76, 79, 84], .35), tSi + .2)
# Scène 9 : pastilles, formulaire, validation
for k in (1, 3, 7, 9): ajoute(pop(1000 + k * 40, .55), Mo("v9", k), pan=-.3 if k in (1, 7) else .3)
for i in range(4): ajoute(pop(1500 + 100 * i, .25), Mo("v9", 9) + .2 + i * .12)
ajoute(carillon([79, 84, 88], .45), Mo("v9", 17))
ajoute(souffle(.4, 2500, 400, .5), tAp)
# Scène 10 : notification de devis
n0 = P("v10") + .25
ajoute(carillon([88, 91], .55), n0 + .05)
for k in range(3): ajoute(vibreur(.14, .2), n0 + k * .2)
# Scène 11 : le collègue apparaît, sa bulle
ajoute(pop(700, .6), Mo("v11", 5) - .2); ajoute(pop(1300, .45), Mo("v11", 7))
# Carton final
ajoute(souffle(.3, 4000, 600, .4), FIN_TEXTE - .1)
ajoute(carillon([72, 79, 84, 88], .5), FIN_TEXTE + .2)
ajoute(pop(1200, .5), FIN_TEXTE + .6)

# ================= Voix et mixage =================
with wave.open(str(ICI / "voix-mix.wav")) as w:
    v = np.frombuffer(w.readframes(w.getnframes()), np.int16).astype(np.float32) / 32768
v = np.pad(v, (0, max(0, N - len(v))))[:N]
v = v / (np.abs(v).max() + 1e-9) * .9
# compression douce de la voix
v = np.tanh(v * 1.4) / np.tanh(1.4)
# ducking : la musique baisse quand la voix parle
blk = SR // 50
e = np.array([np.abs(v[i:i + blk]).mean() for i in range(0, N, blk)])
parle = (e > .02).astype(float)
lisse = np.zeros_like(parle); s = 0.
for i, p in enumerate(parle): s = max(p, s * .9); lisse[i] = s
duck = np.repeat(1 - .62 * lisse, blk)[:N]
mus = .55 * duck
L = ML * mus + SL * .8 + v * .95
R = MR * mus + SRt * .8 + v * .95
fondu = np.minimum(1, (DUREE - t) / .35)
L, R = L * fondu, R * fondu
crete = max(np.abs(L).max(), np.abs(R).max())
L, R = L / crete * .95, R / crete * .95
st = np.stack([L, R], 1)
with wave.open(str(ICI / "son.wav"), "wb") as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes((st * 32767).astype(np.int16).tobytes())
print("son.wav", round(DUREE, 2), "s")

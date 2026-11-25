"""Bande son originale de la vidéo « Nos réalisations » V3 (48 kHz stéréo, 30,6 s, 133 BPM, la mineur).
Entièrement synthétisée : aucun échantillon externe. Repères identiques à video.html :
mur de sites et rafale (0 → 3,6 s), avant/après (3,6 → 7,4 s), recommandations (7,4 → 10,2 s), focus sites, appel à l'action.
Usage : python3 son.py  →  son.wav
"""
import wave
from pathlib import Path
import numpy as np

SR = 48000; DUREE = 24.6; N = int(DUREE * SR)
ICI = Path(__file__).resolve().parent
rng = np.random.default_rng(7)
HE = 2.4
T0, DS, NS = HE, 2.7, 6
TMUR = T0 + NS * DS; TFIN = TMUR; CLIC = TFIN + 3.55
B = .45   # un temps à 133 BPM
L = np.zeros(N); R = np.zeros(N)

def X(d): return np.arange(int(d * SR)) / SR
def hz(n): return 440 * 2 ** ((n - 69) / 12)
def env(n, a=.005, r=.2):
    t = np.arange(n) / SR; e = np.minimum(1, t / max(a, 1e-4)) * np.exp(-t / r); return e
def pb(x, fc):   # passe-bas simple (un pôle, deux passes)
    a = np.exp(-2 * np.pi * fc / SR); y = np.zeros_like(x); s = 0.
    for _ in range(2):
        for i in range(len(x)): s = (1 - a) * x[i] + a * s; y[i] = s
        x = y.copy()
    return y
def ph(x, fc): return x - pb(x, fc)
def ajoute(sig, t, g=1., pan=0.):
    i = int(t * SR)
    if i >= N: return
    if i < 0: sig = sig[-i:]; i = 0
    n = min(len(sig), N - i); l, r = np.sqrt((1 - pan) / 2), np.sqrt((1 + pan) / 2)
    L[i:i + n] += sig[:n] * g * l * 1.414; R[i:i + n] += sig[:n] * g * r * 1.414

# --- instruments ---
def kick(g=1.):
    t = X(.45); f = 48 + 110 * np.exp(-t * 32); ph_ = 2 * np.pi * np.cumsum(f) / SR
    return np.sin(ph_) * np.exp(-t * 7) * g + np.tanh(np.sin(ph_) * 3) * np.exp(-t * 30) * .3 * g
def clap(g=1.):
    n = rng.standard_normal(int(.25 * SR)); e = np.zeros_like(n); t = np.arange(len(n)) / SR
    for d in (0, .011, .022): e += (t >= d) * np.exp(-(t - d).clip(0) * 60)
    e += np.exp(-t * 14) * .5
    return ph(n, 900) * e * .35 * g
def hat(g=1., d=.06):
    n = rng.standard_normal(int(d * SR)); return ph(n, 7000) * env(len(n), .001, d / 4) * .18 * g
def sous_basse(n, d, g=1.):
    t = X(d); s = np.sin(2 * np.pi * hz(n) * t) + .25 * np.sin(4 * np.pi * hz(n) * t)
    return s * np.minimum(1, t / .01) * np.minimum(1, (d - t) / .05) * .5 * g
def nappe(notes, d, g=1., brillance=2500):
    t = X(d); s = np.zeros_like(t)
    for n in notes:
        for det in (-.12, .12):
            f = hz(n) * 2 ** (det / 12); s += ((t * f) % 1 * 2 - 1)   # dents de scie désaccordées
    s = pb(s, brillance) / (len(notes) * 2)
    return s * np.minimum(1, t / .4) * np.minimum(1, (d - t) / .5) * .5 * g
def pluck(n, g=1., d=.35):
    t = X(d); f = hz(n); s = np.sign(np.sin(2 * np.pi * f * t)) * .4 + np.sin(2 * np.pi * f * t)
    return pb(s * np.exp(-t * 12), 4000) * .32 * g
def cloche(n, g=1., d=1.4):
    t = X(d); f = hz(n); return (np.sin(2 * np.pi * f * t) + .4 * np.sin(2 * np.pi * f * 2.76 * t) * np.exp(-t * 4)) * np.exp(-t * 2.2) * .3 * g
def whoosh(d=.5, f0=500, f1=5000, g=1.):
    n = rng.standard_normal(int(d * SR)); t = np.arange(len(n)) / SR; out = np.zeros_like(n); s = 0.
    fc = f0 * (f1 / f0) ** (t / d)
    for i in range(len(n)):
        a = np.exp(-2 * np.pi * fc[i] / SR); s = (1 - a) * n[i] + a * s; out[i] = n[i] - s
    return out * np.sin(np.pi * t / d) ** 2 * .45 * g
def montee(d, g=1.):
    t = X(d); n = rng.standard_normal(len(t)); return ph(n, 2000) * (t / d) ** 2.5 * .35 * g + np.sin(2 * np.pi * (200 + 900 * (t / d) ** 2) * t) * (t / d) ** 3 * .12 * g
def impact(g=1.):
    t = X(2.2); f = 34 + 70 * np.exp(-t * 9)
    boum = np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 2.2)
    souffle = pb(rng.standard_normal(len(t)), 3000) * np.exp(-t * 5) * .5
    return np.tanh((boum + souffle) * 1.6) * .8 * g
def clic(g=1.): n = rng.standard_normal(int(.02 * SR)); return pb(n, 5000) * env(len(n), .0003, .004) * 1.2 * g
def tic(g=1.): n = rng.standard_normal(int(.03 * SR)); return ph(n, 3000) * env(len(n), .0005, .006) * .5 * g
def glitch(d=.3, g=1.):
    t = X(d); s = np.sign(np.sin(2 * np.pi * 180 * t * (1 + 3 * (np.floor(t * 40) % 3)))) * .3
    return s * (np.floor(t * 30) % 2) * g

# ================= H. ACCROCHE (0 → 2,4 s) : mur de sites, énergie immédiate =================
GRILLE = [(57, [57, 60, 64]), (53, [53, 57, 60]), (48, [55, 60, 64]), (55, [55, 59, 62])]   # la m · fa · do · sol
ajoute(impact(.9), 0); ajoute(cloche(81, .5), 0)
for k in range(int(HE / B) + 1):
    tk = k * B
    if tk >= HE - .01: break
    ajoute(kick(1.), tk); ajoute(sous_basse([45, 41][(k // 4) % 2] - 12, B * .9, .9), tk)
    if k % 2: ajoute(clap(1.), tk)
    for h in range(2): ajoute(hat(1 if h else .6), tk + h * B / 2, pan=.35)
ajoute(nappe([57, 60, 64], HE, .5, 3500), 0)
ajoute(whoosh(.5, 300, 7000, .8), 0)                    # « à ça. »
ajoute(montee(.9, .9), HE - .9); ajoute(whoosh(.5, 400, 8000, .8), HE - .4)

# ================= C. DÉFILÉ DES SITES (4,5 → 20,5 s) : groove =================
for s in range(NS):
    t0 = T0 + s * DS; rac, acc = GRILLE[s % 4]
    ajoute(whoosh(.42, 400, 6000, .9), t0 - .3, pan=(-1) ** s * .4)                             # passage d'un site à l'autre
    ajoute(nappe(acc, DS, .55, 3200), t0)
    for k in range(6):
        tk = t0 + k * B
        ajoute(kick(1.), tk)
        if k % 2 == 1: ajoute(clap(1.), tk)
        ajoute(sous_basse(rac - 24, B * .9, .9), tk)
        for h in range(2): ajoute(hat(1 if h else .6), tk + h * B / 2, pan=.35)
    arp = [acc[0] + 12, acc[1] + 12, acc[2] + 12, acc[1] + 12]
    for k in range(12): ajoute(pluck(arp[k % 4], .55), t0 + k * B / 2, pan=(-.4 if k % 2 else .4))
ajoute(cloche(81, .5), T0 + .02)

# ================= E. FIN (24,5 → 30 s) : logo et appel =================
ajoute(impact(.8), TFIN); ajoute(nappe([45, 57, 60, 64, 71], 5.5, .8, 4500), TFIN)
for i, n in enumerate([69, 72, 76, 79, 81, 84]): ajoute(cloche(n, .4, 2.2), TFIN + 1.6 + i * .07, pan=-.5 + i * .2)   # scintillement du logo
ajoute(pluck(81, .7), TFIN + 1.9); ajoute(pluck(88, .5), TFIN + 2.05)                                          # bouton « Contactez-nous »
ajoute(clic(1.), CLIC); ajoute(cloche(88, .6), CLIC + .02); ajoute(cloche(93, .4), CLIC + .12)                   # clic du curseur

# ================= MIXAGE =================
st = np.stack([L, R], 1)
st /= np.max(np.abs(st)) + 1e-9
st = np.tanh(st * 1.3) / np.tanh(1.3)
fin = np.clip((DUREE - np.arange(N) / SR) / 1.2, 0, 1)[:, None]; st *= fin
st *= .89
with wave.open(str(ICI / "son.wav"), "wb") as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes((st * 32767).astype(np.int16).tobytes())
print("son.wav", DUREE, "s")

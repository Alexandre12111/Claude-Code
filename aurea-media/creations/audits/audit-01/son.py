"""Bande son de l'audit express n°1 : musique douce sous la voix d'Alexandre et bruitages calés sur calage.json.
La voix (voix-mix.wav, issue de calage.py) reste prioritaire : la musique baisse automatiquement pendant qu'il parle.
Usage : python3 son.py  →  son.wav
"""
import json, wave
from pathlib import Path
import numpy as np

ICI = Path(__file__).resolve().parent
SR = 48000; CAL = json.loads((ICI / "calage.json").read_text())
DUREE = CAL["duree"]; N = int(DUREE * SR); PH = CAL["phrases"]
P = lambda i: PH[i]["debut"]; F = lambda i: PH[i]["fin"]
def M(i, k): m = PH[i]["mots"]; return m[min(k, len(m) - 1)]["debut"]
rng = np.random.default_rng(11)
ML, MR, SL, SRR = (np.zeros(N) for _ in range(4))
def X(d): return np.arange(int(d * SR)) / SR
def hz(n): return 440 * 2 ** ((n - 69) / 12)
def env(n, a=.005, r=.2): t = np.arange(n) / SR; return np.minimum(1, t / max(a, 1e-4)) * np.exp(-t / r)
def pb(x, fc):
    a = np.exp(-2 * np.pi * fc / SR); y = np.zeros_like(x); s = 0.
    for i in range(len(x)): s = (1 - a) * x[i] + a * s; y[i] = s
    return y
def ajoute(sig, t, g=1., pan=0., mus=False):
    i = int(t * SR)
    if i >= N or t < 0: return
    n = min(len(sig), N - i); L, R = (ML, MR) if mus else (SL, SRR)
    L[i:i + n] += sig[:n] * g * np.sqrt((1 - pan) / 2) * 1.414; R[i:i + n] += sig[:n] * g * np.sqrt((1 + pan) / 2) * 1.414
# instruments doux (lo-fi)
def kick(g=1.): t = X(.35); f = 50 + 70 * np.exp(-t * 30); return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 9) * g
def hat(g=1.): n = rng.standard_normal(int(.05 * SR)); return (n - pb(n, 6000)) * env(len(n), .001, .012) * .12 * g
def rim(g=1.): t = X(.08); return (np.sin(2 * np.pi * 1700 * t) + rng.standard_normal(len(t)) * .4) * np.exp(-t * 60) * .25 * g
def rhodes(notes, d, g=1.):
    t = X(d); s = sum(np.sin(2 * np.pi * hz(n) * t) * (1 + .3 * np.sin(2 * np.pi * 5 * t)) + .2 * np.sin(4 * np.pi * hz(n) * t) * np.exp(-t * 3) for n in notes)
    return s / len(notes) * np.minimum(1, t / .02) * np.exp(-t * .9) * .35 * g
def basse(n, d, g=1.): t = X(d); return np.sin(2 * np.pi * hz(n) * t) * np.minimum(1, t / .01) * np.minimum(1, (d - t) / .05) * .4 * g
# bruitages
def clic(g=1.): n = rng.standard_normal(int(.02 * SR)); return pb(n, 5000) * env(len(n), .0003, .004) * 1.4 * g
def pop(f=900, g=1.): t = X(.12); return np.sin(2 * np.pi * f * (1 + 2 * np.exp(-t * 40)) * t) * np.exp(-t * 30) * .5 * g
def whoosh(d=.5, f0=500, f1=5000, g=1.):
    n = rng.standard_normal(int(d * SR)); t = np.arange(len(n)) / SR; out = np.zeros_like(n); s = 0.; fc = f0 * (f1 / f0) ** (t / d)
    for i in range(len(n)): a = np.exp(-2 * np.pi * fc[i] / SR); s = (1 - a) * n[i] + a * s; out[i] = n[i] - s
    return out * np.sin(np.pi * t / d) ** 2 * .4 * g
def erreur(g=1.): t = X(.32); return np.sign(np.sin(2 * np.pi * 140 * t)) * (np.sin(2 * np.pi * 9 * t) > -.2) * np.exp(-t * 6) * .18 * g
def feutre(d=.45, g=1.): n = rng.standard_normal(int(d * SR)); t = np.arange(len(n)) / SR; return (n - pb(n, 2500)) * np.sin(np.pi * t / d) * .15 * g
def bip(f=1000, g=1.): t = X(.14); return np.sin(2 * np.pi * f * t) * np.minimum(1, t / .005) * np.exp(-t * 18) * .4 * g
def cloche(n, g=1., d=1.2): t = X(d); f = hz(n); return (np.sin(2 * np.pi * f * t) + .4 * np.sin(2 * np.pi * f * 2.76 * t) * np.exp(-t * 4)) * np.exp(-t * 2.5) * .3 * g
def sonnerie(d=1.4, g=1.):
    t = X(d); s = (np.sin(2 * np.pi * 1320 * t) + .6 * np.sin(2 * np.pi * 1760 * t)) * (np.sin(2 * np.pi * 16 * t) > 0) * ((t % .8) < .5)
    return s * .2 * g
def impact(g=1.): t = X(1.4); return np.tanh(np.sin(2 * np.pi * np.cumsum(38 + 60 * np.exp(-t * 8)) / SR) * np.exp(-t * 3) * 2) * .6 * g

# ---------- musique : lo-fi en ré mineur, 92 BPM, plus lumineuse après la refonte ----------
B = 60 / 104; APRES = P("l07") + .9   # 104 BPM : plus entraînant
ACC_AV = [[50, 53, 57, 60], [46, 50, 53, 57], [48, 52, 55, 58], [45, 49, 52, 57]]      # rém7 · sib · do7 · la
ACC_AP = [[53, 57, 60, 64], [50, 53, 57, 62], [46, 50, 53, 57], [48, 52, 55, 60]]      # fa · rém · sib · do
t = 0.; k = 0
while t < DUREE - 2:
    ap = t >= APRES; acc = (ACC_AP if ap else ACC_AV)[(k // 4) % 4]
    if k % 4 == 0: ajoute(rhodes(acc, 4 * B, .9), t, mus=True); ajoute(basse(acc[0] - 12, 4 * B * .95, .8), t, mus=True)
    ajoute(kick(.8), t, mus=True) if k % 2 == 0 else ajoute(rim(.8), t, mus=True)
    ajoute(hat(.8), t + B / 2, pan=.3, mus=True)
    if ap: ajoute(hat(.5), t, pan=-.3, mus=True); ajoute(hat(.35), t + B / 4, pan=.4, mus=True); ajoute(hat(.35), t + 3 * B / 4, pan=-.4, mus=True)
    if ap and k % 2: ajoute(rim(.6), t + B / 2, mus=True)   # après la refonte : rythmique plus vive
    t += B; k += 1
# ---------- bruitages calés sur la voix ----------
ajoute(impact(.6), 0); ajoute(whoosh(.5, 300, 6000, .6), 0)
for i in ("l01", "l03", "l05"): ajoute(pop(700, .7), P(i) - .05); ajoute(erreur(.7), P(i) + .1)                  # cartes « problème »
for k in range(8): ajoute(clic(.35), P("l01") + .2 + k * .25)                                                     # le curseur cherche
ajoute(whoosh(1.1, 2000, 400, .5), P("l02"))                                                                       # défilement jusqu'au pied de page
ajoute(feutre(.5, 1.), P("l02") + 1.6)                                                                             # cercle rouge autour du numéro
ajoute(whoosh(.5, 400, 2000, .4), P("l03"))
ajoute(feutre(.3, 1.), M("l04", 3) - .1); ajoute(feutre(.3, 1.), M("l04", 3) + .1); ajoute(erreur(.5), M("l04", 3) + .2)   # croix
for i in range(3): ajoute(pop(1100 + i * 200, .4), M("l04", 8) - .1 + i * .12)                                    # vrais chantiers, sur « son travail » (pas sur « pas »)
ajoute(feutre(.45, 1.), M("l05", 5) - .1)                                                                          # surligneur
for i in range(3): ajoute(bip(880 if i < 2 else 1320, .8), M("l06", 1) - .2 + i * .4)                              # 3, 2, 1
ajoute(clic(1.), M("l06", 6)); ajoute(erreur(.8), M("l06", 6) + .1)                                                # le visiteur part
ajoute(whoosh(.9, 300, 8000, .9), P("l07")); ajoute(impact(.7), P("l07") + .85); ajoute(cloche(81, .6), P("l07") + .85)   # refonte
for a in (M("l08", 1), M("l08", 9), M("l08", 14)): ajoute(cloche(84, .55), a); ajoute(pop(1500, .4), a)            # coches dorées
ajoute(whoosh(.5, 400, 4000, .5), P("l09")); ajoute(sonnerie(1.6, .75), F("l09") + .05)                          # le client appelle (sonnerie après la phrase, pour ne pas couvrir « client »)
ajoute(whoosh(.5, 600, 5000, .5), F("l09") + .2); ajoute(cloche(88, .6), P("l10") + .1)

# ---------- mixage : la musique s'efface sous la voix ----------
with wave.open(str(ICI / "voix-mix.wav")) as w:
    sr0 = w.getframerate(); v = np.frombuffer(w.readframes(w.getnframes()), np.int16).astype(np.float32) / 32768
    if w.getnchannels() == 2: v = v.reshape(-1, 2).mean(1)
if sr0 != SR: v = np.interp(np.arange(int(len(v) * SR / sr0)) * sr0 / SR, np.arange(len(v)), v)
v = np.pad(v, (0, max(0, N - len(v))))[:N]
e = np.sqrt(np.convolve(v ** 2, np.ones(int(.05 * SR)) / int(.05 * SR), "same")); parle = np.clip(e / .02, 0, 1)
parle = np.convolve(parle, np.ones(int(.25 * SR)) / int(.25 * SR), "same")
gm = 1 - .8 * parle   # la musique s'efface davantage sous la voix (intelligibilité)
mus = np.stack([ML, MR], 1); mus /= np.max(np.abs(mus)) + 1e-9
sfx = np.stack([SL, SRR], 1); sfx /= np.max(np.abs(sfx)) + 1e-9
mix = v[:, None] * .95 + mus * gm[:, None] * .22 + sfx * .32
fin = np.clip((DUREE - np.arange(N) / SR) / 1.0, 0, 1)[:, None]; mix *= fin
mix = np.tanh(mix * 1.1) / np.tanh(1.1) * .9
with wave.open(str(ICI / "son.wav"), "wb") as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes((np.clip(mix, -1, 1) * 32767).astype(np.int16).tobytes())
print("son.wav", round(DUREE, 2), "s")

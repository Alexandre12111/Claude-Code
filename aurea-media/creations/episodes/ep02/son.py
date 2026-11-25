"""Bande son V4 (Gaspard) (48 kHz stéréo) : musique cartoon originale + bruitages synchronisés + voix (prioritaire).

Musique (synthèse) en quatre parties :
  A. Accroche : pizzicati pressés en la mineur, bloc de bois, tension qui monte.
  B. Problème : groove comique (tuba, pizzicati sur les contretemps, bloc de bois, caisse claire), plus frénétique
     sous les questions, version « endormie » le soir.
  C. Déclic : scratch, silence, glissando magique.
  D. Solution : groove joyeux en do majeur (mélodie sifflée, marimba, claquements de mains), jingle final.
Les repères sont les mêmes que dans scenes.js (lus dans calage.json).
"""
import json, wave
from pathlib import Path
import numpy as np

ICI = Path(__file__).resolve().parent
SR = 48000
CAL = json.loads((ICI / "calage.json").read_text())
PH = CAL["phrases"]; DUREE = CAL["duree"]; N = int(DUREE * SR)
rng = np.random.default_rng(2026)
P = lambda i: PH[i]["debut"]; F = lambda i: PH[i]["fin"]
def Mo(i, k): m = PH[i]["mots"]; return m[min(k, len(m) - 1)]["debut"]
# repères identiques à scenes.js (épisode 2)
T = dict(gel=Mo("l00", 7) - .04, tamp=Mo("l00", 11) - .02, rw=P("l01") - .8, j=P("l02") - .12, g=P("l03") - .12, ap=P("l06") - .1, v=P("l07") - .1,
         fa=P("l08") - .1, vi=P("l09") - .1, w=P("l10") - .1, wJ=Mo("l10", 10) + .1, fi=P("l11") - .1, sd=P("l12") - .1,
         fin=CAL["finTexte"] if "finTexte" in CAL else F("l12") + .4)
ML, MR, SL, SR_ = (np.zeros(N) for _ in range(4))
def ajoute(sig, debut, g=1., pan=0., mus=False):
    L, R = (ML, MR) if mus else (SL, SR_)
    i = int(debut * SR)
    if i >= N or i + len(sig) <= 0: return
    if i < 0: sig = sig[-i:]; i = 0
    sig = sig[:N - i] * g
    L[i:i + len(sig)] += sig * np.sqrt(.5 * (1 - pan)); R[i:i + len(sig)] += sig * np.sqrt(.5 * (1 + pan))

def X(d): return np.arange(int(d * SR)) / SR
def env(n, a=.004, r=.15, c=4.):
    x = np.arange(n) / SR; a = max(a, .002)
    return np.minimum(1, x / a) * np.exp(-c * np.maximum(0, x - a) / max(r, 1e-4))
def bruit(d): return rng.standard_normal(int(d * SR))
def pb(x, fc):
    a = np.exp(-2 * np.pi * fc / SR); y = np.empty_like(x); s = 0.
    for i in range(len(x)): s = (1 - a) * x[i] + a * s; y[i] = s
    return y
def bp(x, fc, q=2.):
    w0 = 2 * np.pi * fc / SR; al = np.sin(w0) / (2 * q); b0, b2, a0, a1, a2 = al, -al, 1 + al, -2 * np.cos(w0), 1 - al
    y = np.zeros_like(x); x1 = x2 = y1 = y2 = 0.
    for i in range(len(x)):
        y0 = (b0 * x[i] + b2 * x2 - a1 * y1 - a2 * y2) / a0; x2, x1, y2, y1 = x1, x[i], y1, y0; y[i] = y0
    return y
NOTE = lambda n: 440 * 2 ** ((n - 69) / 12)
def osc(f, d, forme='sin'):
    x = X(d); ph = 2 * np.pi * np.cumsum(np.broadcast_to(f, x.shape)) / SR if np.ndim(f) else 2 * np.pi * f * x
    if forme == 'sin': return np.sin(ph)
    if forme == 'tri': return 2 / np.pi * np.arcsin(np.sin(ph))
    if forme == 'car': return np.tanh(3 * np.sin(ph))
    return np.sin(ph)

# ---------------- Instruments ----------------
def pizz(n, g=1.):
    d = .32; x = X(d); f = NOTE(n)
    s = sum(np.sin(2 * np.pi * f * k * x) * np.exp(-x * (14 + 6 * k)) / k for k in range(1, 6))
    return s * env(len(x), .002, .2, 3) * g
def marimba(n, g=1.):
    d = .5; x = X(d); f = NOTE(n)
    return (np.sin(2 * np.pi * f * x) * np.exp(-x * 9) + .35 * np.sin(2 * np.pi * f * 4 * x) * np.exp(-x * 30)) * env(len(x), .001, .4, 2) * g
def tuba(n, d, g=1.):
    x = X(d); f = NOTE(n)
    s = osc(f, d, 'car') + .4 * np.sin(4 * np.pi * f * x)
    return pb(s, 700) * np.minimum(1, x / .015) * np.exp(-x * 3.5) * np.minimum(1, (d - x) / .03) * g
def bloc(g=1., f=1200):
    x = X(.08); return np.sin(2 * np.pi * f * x) * np.exp(-x * 60) * g
def caisse(g=1.):
    b = bp(bruit(.18), 2200, .7); x = X(.18)
    return (b * np.exp(-x * 25) + .5 * np.sin(2 * np.pi * 190 * x) * np.exp(-x * 30)) * g
def claque(g=1.):
    b = bp(bruit(.2), 1600, 1.1); e = np.zeros(len(b))
    for o in (0, .01, .02): e[int(o * SR):] += env(len(b) - int(o * SR), .001, .03 if o < .02 else .12)
    return b * e * g
def grosse(g=1.):
    x = X(.3); f = 55 + 80 * np.exp(-x * 35)
    return np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-x * 10) * g
def charley(g=1.):
    b = np.diff(bruit(.05), prepend=0); return b * env(len(b), .001, .02) * .3 * g
def siffle(n, d, g=1.):
    x = X(d); f = NOTE(n) * (1 + .012 * np.sin(2 * np.pi * 6 * x) * np.minimum(1, x / .15))
    return (osc(f, d) + .05 * bp(bruit(d), NOTE(n) * 2, 4)) * np.minimum(1, x / .03) * np.minimum(1, (d - x) / .06) * g
def glock(n, g=1.):
    x = X(1.); f = NOTE(n)
    return (np.sin(2 * np.pi * f * x) + .4 * np.sin(2 * np.pi * f * 2.76 * x) * np.exp(-x * 6)) * np.exp(-x * 3.5) * env(len(x), .001, 1, 1) * g

# ---------------- Bruitages ----------------
def somme(*sigs):
    n = max(len(x) for x in sigs); out = np.zeros(n)
    for x in sigs: out[:len(x)] += x
    return out
def pop(f=900, g=1.):
    x = X(.1); return np.sin(2 * np.pi * np.cumsum(f * (1 + 1.8 * np.exp(-x * 60))) / SR) * env(len(x), .001, .05) * g
def boing(f0=180, g=1., d=.45):
    x = X(d); f = f0 * (1 + .9 * np.exp(-x * 7)) * (1 + .12 * np.sin(2 * np.pi * 18 * x) * np.exp(-x * 4))
    return osc(f, d, 'tri') * np.exp(-x * 5) * g
def whoosh(d=.4, f0=300, f1=3000, g=1.):
    b = bruit(d); n = len(b); y = np.zeros(n); blocs = 14
    for i in range(blocs):
        s, e = i * n // blocs, (i + 1) * n // blocs
        y[s:e] = bp(b[max(0, s - 300):e], f0 * (f1 / f0) ** (i / blocs), .9)[-(e - s):]
    return y * np.sin(np.pi * X(d)[:n] / d) ** 2 * g
def impact(g=1.):
    return somme(grosse(1.2) * g, bp(bruit(.25), 900, .8) * env(int(.25 * SR), .001, .08) * .8 * g)
def splat(g=1.):
    b = pb(bruit(.35), 1500); x = X(.35)
    return b * np.exp(-x * 9) * (1 + np.sin(2 * np.pi * 30 * x)) * g * 2.5
def vibreur(d, g=1.):
    x = X(d); s = np.sign(np.sin(2 * np.pi * 160 * x)) * .5 + np.sin(2 * np.pi * 320 * x) * .3
    return pb(s, 1300) * (.6 + .4 * np.sin(2 * np.pi * 26 * x)) * np.minimum(1, x / .01) * np.minimum(1, (d - x) / .02) * g
def sonnette(d=.5, g=1.):
    x = X(d); s = (np.sin(2 * np.pi * 2350 * x) + .6 * np.sin(2 * np.pi * 3100 * x)) * (np.sin(2 * np.pi * 22 * x) > 0)
    return s * np.minimum(1, (d - x) / .05) * g
def toc(g=1.):
    b = bloc(.3 * g, 400); b[:int(.06 * SR)] += bp(bruit(.06), 600, 3) * env(int(.06 * SR), .001, .02) * 3 * g; return b
def trombone_triste(g=1.):
    s = np.zeros(0)
    for n, d in [(63, .32), (62, .32), (61, .32), (60, .9)]:
        x = X(d); f = NOTE(n - 12) * (1 + (.04 * np.sin(2 * np.pi * 6 * x) if d > .5 else 0))
        s = np.concatenate([s, pb(osc(f, d, 'car') + .5 * osc(f * 2, d, 'car'), 1200) * np.minimum(1, x / .03) * np.minimum(1, (d - x) / .08)])
    return s * g
def glissando(d=.8, g=1.):
    s = np.zeros(int(d * SR)); notes_ = [60, 64, 67, 72, 76, 79, 84, 88]
    for k, n in enumerate(notes_):
        p = glock(n, .5); i = int(k * d / len(notes_) * SR); s[i:i + len(p)] += p[:len(s) - i]
    return s * g
def carillon(notes_, g=1.):
    s = np.zeros(int(1.4 * SR))
    for k, n in enumerate(notes_):
        p = glock(n); i = int(k * .08 * SR); s[i:i + len(p)] += p[:len(s) - i]
    return s * g
def scratch(g=1.):
    x = X(.35); f = 700 * (1 + .9 * np.sin(2 * np.pi * 7 * x))
    return (osc(f, .35) * .4 + bp(bruit(.35), 1600, 1.5)) * env(len(x), .003, .25, 3) * g
def battement_ailes(d=.8, g=1.):
    x = X(d); return bp(bruit(d), 700, 1.2) * (np.sin(2 * np.pi * 14 * x) > .3) * np.exp(-x * 2) * g * 1.5
def tic(g=1.): return bp(bruit(.04), 2600, 3) * env(int(.04 * SR), .002, .012) * 2 * g
def page(g=1.): return whoosh(.18, 2000, 5000, .5) * g
def clic(g=1.): return bp(bruit(.04), 2200, 3) * env(int(.04 * SR), .002, .01) * 3 * g
def tinte(g=1.):
    x = X(.6); return (np.sin(2 * np.pi * 2900 * x) + .5 * np.sin(2 * np.pi * 4200 * x)) * np.exp(-x * 9) * g

# ================= MUSIQUE =================
B = .5
# A. Accroche joyeuse (do majeur), coupée net par le scratch de l'arrêt sur image
for k in range(int(T["gel"] / (B / 2)) + 1):
    tt = k * B / 2
    if tt >= T["gel"] - .02: break
    ajoute(marimba([72, 76, 79, 76][k % 4], .32), tt, pan=-.2, mus=True)
    if k % 2 == 0: ajoute(tuba([48, 43][(k // 4) % 2] - 12, B / 2 * .8, .4), tt, mus=True)
    ajoute(charley(.4), tt, pan=.35, mus=True)
ajoute(scratch(1.1), T["gel"] - .02)
# B. Retour en arrière : glissando inversé, puis groove doux pendant la salle de bain et la haie
# flash-back : souffle qui monte, arpège de glockenspiel vers l'aigu, cloche douce à l'arrivée dans le souvenir
_d = .7; _x = X(_d); ajoute(bp(bruit(_d), 2400, .7) * (_x / _d) ** 2 * .35, T["rw"] - .05)
for _i, _n in enumerate([84, 88, 91, 96, 100, 103]): ajoute(glock(_n, .35), T["rw"] + .05 + _i * .06, pan=(_i - 2.5) * .12)
ajoute(carillon([76, 83], .3), P("l01") - .1)
basse = [48, 55, 52, 55, 45, 52, 48, 52]; acc = [[60, 64, 67], [57, 60, 64]]
x0 = P("l01") - .05
while x0 < T["g"]:
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= T["g"]: break
        if k % 2 == 0: ajoute(tuba(basse[k] - 12, B / 2 * .8, .38), tt, mus=True)
        if k % 2 == 1:
            for n in acc[(k // 4) % 2]: ajoute(pizz(n + 12, .16), tt, pan=.25, mus=True)
        ajoute(bloc(.14, 1300 if k % 2 else 950), tt, pan=-.4, mus=True)
    x0 += 4 * B
# C. Google : tension en la mineur (pizzicati, tic-tac), silence complet sous les criquets, puis « ding » du concurrent
x0 = T["g"]
while x0 < T["ap"]:
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= T["ap"]: break
        if Mo("l04", 9) - .15 < tt < P("l05") - .05: continue          # silence gêné
        ajoute(pizz([57, 60, 64, 60][k % 4], .26), tt, pan=-.2 + .4 * (k % 2), mus=True)
        if k % 4 == 0: ajoute(tuba(45 - 12, B * .8, .32), tt, mus=True)
    x0 += 4 * B
# D. Appel / vérification / fantôme : basse descendante triste, très dépouillée
for i, n in enumerate([45, 44, 43, 41, 40, 40]):
    tt = T["ap"] + i * B * 2
    if tt < T["vi"] - .1: ajoute(tuba(n - 12, B * 1.8, .3), tt, mus=True); ajoute(marimba(n + 24, .12), tt + B, pan=.3, mus=True)
# E. Vitrine puis victoire : groove joyeux qui monte, mélodie sifflée, jingle final
basse2 = [48, 55, 52, 55, 45, 52, 48, 52, 41, 48, 45, 48, 43, 50, 47, 50]
acc2 = [[60, 64, 67], [57, 60, 64], [53, 57, 60, 65], [55, 59, 62, 67]]
melodie = [(72, 1), (76, 1), (79, 2), (77, 1), (76, 1), (74, 2), (72, 1), (74, 1), (76, 1), (79, 1), (81, 2), (79, 2), (77, 1), (76, 1), (74, 1), (72, 1), (74, 2), (76, 2), (72, 4)] * 2
ajoute(glissando(.6, .5), T["vi"] - .1)
x0 = T["vi"]; mes = 0; fin_mus = T["fin"] + .15
while x0 < fin_mus:
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= fin_mus: break
        ajoute(tuba(basse2[(mes * 4 + k // 2) % 16] - 12, B / 2 * .8, .42), tt, mus=True)
        if k % 2 == 1:
            for n in acc2[mes % 4]: ajoute(marimba(n, .13), tt, pan=-.3, mus=True)
        if k in (0, 4): ajoute(grosse(.6), tt, mus=True)
        if k in (2, 6): ajoute(claque(.4), tt, pan=.15, mus=True)
        ajoute(charley(.55 if k % 2 else .3), tt, pan=.35, mus=True)
    x0 += 4 * B; mes += 1
tm = T["w"]
for n, d in melodie:
    if tm + d * B / 2 > fin_mus: break
    ajoute(siffle(n, d * B / 2 * .92, .17), tm, pan=.2, mus=True); tm += d * B / 2
for k, n in enumerate([72, 76, 79]): ajoute(marimba(n, .5), T["fin"] + .05 + k * .09, mus=True)
ajoute(tuba(48, .7, .25), T["fin"] + .35, mus=True); ajoute(carillon([84, 88, 91, 96], .4), T["fin"] + .35, mus=True)

# ================= BRUITAGES =================
def criquet(d=1.2, g=1.):
    x = X(d); s = np.sin(2 * np.pi * 4300 * x) * (np.sin(2 * np.pi * 38 * x) > .2) * (np.sin(2 * np.pi * 2.2 * x) > -.2)
    return s * np.minimum(1, x / .05) * np.minimum(1, (d - x) / .1) * .25 * g
def mouche_son(d=2., g=1.):
    x = X(d); f = 220 + 25 * np.sin(2 * np.pi * 1.3 * x); s = np.sign(np.sin(2 * np.pi * np.cumsum(f) / SR)) * .3
    return pb(s, 900) * (.6 + .4 * np.sin(2 * np.pi * .9 * x)) * np.minimum(1, x / .1) * np.minimum(1, (d - x) / .2) * g
def tampon_son(g=1.): return somme(grosse(1.1 * g), bp(bruit(.12), 500, .8) * env(int(.12 * SR), .002, .05) * 1.2 * g)
def sonnerie(d=1.6, g=1.):
    x = X(d); s = (np.sin(2 * np.pi * 1320 * x) + .6 * np.sin(2 * np.pi * 1760 * x)) * (np.sin(2 * np.pi * 16 * x) > 0) * ((x % .8) < .5)
    return s * np.minimum(1, x / .02) * np.minimum(1, (d - x) / .05) * .25 * g
tope = Mo("l00", 4)
ajoute(claque(1.2), tope); ajoute(pop(1400, .5), tope + .02)
for i in range(8): ajoute(pop(1600 + 120 * i, .14), tope + .05 + i * .04, pan=(i - 4) * .15)
ajoute(impact(.7), T["gel"]); ajoute(tampon_son(1.), T["tamp"])
ajoute(whoosh(.6, 600, 5000, .45), T["rw"])                                                         # entrée dans le souvenir
for i in range(4): ajoute(tinte(.22), P("l01") + .3 + i * .35, pan=.4)                              # cœurs
ajoute(whoosh(.35, 300, 2600, .5), T["j"]); ajoute(page(.6), Mo("l02", 3))                          # post-it
ajoute(whoosh(.4, 400, 3000, .6), T["g"] - .05)
for i in range(len("Gaspard rénovation")): ajoute(clic(.35), Mo("l03", 9) + i * .7 / 18, pan=.2)    # frappe au clavier
ajoute(pop(700, .4), P("l04") - .1)
ajoute(criquet(max(.5, P("l05") - Mo("l04", 9)), 1.), Mo("l04", 9) - .1)
ajoute(carillon([88, 95], .45), P("l05") - .05)                                                    # ding du concurrent
for i in range(10): ajoute(clic(.2), Mo("l05", 12) + i * .05)                                       # compteur d'avis
ajoute(whoosh(.3, 3000, 500, .4), T["ap"])
ajoute(sonnerie(min(1.8, T["v"] - T["ap"] - .1), 1.), T["ap"] + .1)
ajoute(mouche_son(max(.6, T["v"] - T["ap"] - .3), .5), T["ap"] + .2, pan=.3)
ajoute(page(.6), T["v"]); ajoute(whoosh(.5, 600, 1800, .3), T["v"] + .45)
for i, d in enumerate((.2, .55, .9)): ajoute(osc(220, .18) * env(int(.18 * SR), .005, .15) * .2, Mo("l07", 5) - .1 + d + .2, pan=.2)   # croix rouges
ajoute(glissando(.7, .35)[::-1], T["fa"]); ajoute(tampon_son(.7), Mo("l08", 7) - .1)
for k in (6, 8, 10, 12): ajoute(pop(1100 + k * 25, .5), Mo("l09", k) - .1, pan=-.2); ajoute(bloc(.3, 700), Mo("l09", k) + .02)
ajoute(whoosh(.4, 400, 3000, .6), T["w"] - .05); ajoute(clic(1.), Mo("l10", 10) - .15)
ajoute(sonnerie(.8, 1.), T["wJ"]); ajoute(boing(260, .6, .5), T["wJ"] + .35); ajoute(whoosh(.5, 300, 3500, .6), T["wJ"] + .4)
ajoute(claque(1.3), Mo("l11", 4) - .1); ajoute(impact(.5), Mo("l11", 4) - .1)
for i in range(8): ajoute(pop(1700 + 140 * i, .14), Mo("l11", 4) + i * .04, pan=(i - 4) * .15)
ajoute(tampon_son(.9), Mo("l11", 9) - .1)
ajoute(tinte(.3), Mo("l12", 5)); ajoute(whoosh(.3, 4000, 600, .4), T["fin"] - .1); ajoute(boing(240, .5, .5), T["fin"] + .55)

# ================= VOIX ET MIXAGE =================
with wave.open(str(ICI / "voix-mix.wav")) as w:
    sr0 = w.getframerate(); v = np.frombuffer(w.readframes(w.getnframes()), np.int16).astype(np.float32) / 32768
    if w.getnchannels() == 2: v = v.reshape(-1, 2).mean(1)
v = np.pad(v, (0, max(0, N - len(v))))[:N]
vt = np.zeros(N)
if (ICI / "voix-tel.wav").exists():
    with wave.open(str(ICI / "voix-tel.wav")) as w:
        t2 = np.frombuffer(w.readframes(w.getnframes()), np.int16).astype(np.float32) / 32768
        if w.getnchannels() == 2: t2 = t2.reshape(-1, 2).mean(1)
    vt = np.pad(t2, (0, max(0, N - len(t2))))[:N] * .95
# voix déjà traitée par calage.py (chaîne douce anti grésillements, -16 LUFS) : pas de retraitement
blk = SR // 100
e = np.array([np.abs(v[i:i + blk] + vt[i:i + blk]).mean() for i in range(0, N, blk)])
parle = (e > .015).astype(float); lis = np.zeros_like(parle); s = 0.
for i, p in enumerate(parle):   # attaque 60 ms, relâche 350 ms
    s = min(1., s + p / 6) if p else s * np.exp(-1 / 35); lis[i] = s
duck = np.repeat(1 - .9 * lis, blk)[:N]   # environ -20 dB sous la voix
duckS = np.repeat(1 - .45 * lis, blk)[:N]  # bruitages un peu atténués sous la voix
L = ML * .55 * duck + SL * .5 * duckS + v + vt * .92
R = MR * .55 * duck + SR_ * .5 * duckS + v + vt * 1.0
fade = np.minimum(1, (DUREE - np.arange(N) / SR) / .3); L *= fade; R *= fade
c = max(np.abs(L).max(), np.abs(R).max()); L, R = L / c * .89, R / c * .89
with wave.open(str(ICI / "son.wav"), "wb") as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes((np.stack([L, R], 1) * 32767).astype(np.int16).tobytes())
print("son.wav", round(DUREE, 2), "s")

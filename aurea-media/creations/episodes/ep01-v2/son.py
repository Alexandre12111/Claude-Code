"""Bande son V2 (48 kHz stéréo) : musique cartoon originale + bruitages synchronisés + voix (prioritaire).

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
T = dict(whip=P("v2") - .12, ch=P("v5") - .14, pr=P("v6") - .14, so=P("v7") - .2, dec=P("v8"), si=P("v9") - .3, ap=P("v10") - .32, fin=F("v12") + .45)

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
    x = np.arange(n) / SR
    return np.minimum(1, x / max(a, 1e-4)) * np.exp(-c * np.maximum(0, x - a) / max(r, 1e-4))
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
def tic(g=1.): return bp(bruit(.03), 3800, 4) * env(int(.03 * SR), .0005, .01) * 3 * g
def page(g=1.): return whoosh(.18, 2000, 5000, .5) * g
def clic(g=1.): return bp(bruit(.03), 2500, 3) * env(int(.03 * SR), .0005, .008) * 4 * g
def tinte(g=1.):
    x = X(.6); return (np.sin(2 * np.pi * 2900 * x) + .5 * np.sin(2 * np.pi * 4200 * x)) * np.exp(-x * 9) * g

# ================= MUSIQUE =================
B = .5  # 120 battements par minute
# A. Accroche : pizzicati pressés + bloc de bois
x0 = 0.
while x0 < T["whip"] - .05:
    for k, n in enumerate([57, 60, 64, 60, 57, 60, 64, 67]):
        tt = x0 + k * B / 2
        if tt < T["whip"] - .05: ajoute(pizz(n, .5), tt, pan=-.2 + .4 * (k % 2), mus=True); ajoute(bloc(.25, 1500 if k % 2 else 1100), tt, pan=.4, mus=True)
    x0 += 4 * B
ajoute(whoosh(.35, 200, 4000, 1.), T["whip"] - .2)
# B. Problème : groove comique en la mineur
basse = [45, 45, 52, 52, 45, 45, 51, 52]
accords = [[57, 60, 64], [57, 60, 64], [56, 59, 64], [57, 60, 64]]
x0 = T["whip"] + .1; mes = 0
while x0 < T["dec"] - .05:
    endormi = x0 >= T["so"] - .1
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= T["dec"] - .05: break
        if not endormi or k % 2 == 0: ajoute(tuba(basse[k] - 12 + (0 if not endormi else 0), B / 2 * .85, .5 if not endormi else .35), tt, mus=True)
        if k % 2 == 1 and not endormi:
            for n in accords[mes % 4]: ajoute(pizz(n + 12, .18), tt, pan=.25, mus=True)
        if not endormi:
            ajoute(bloc(.18, 1300 if k % 2 else 900), tt, pan=-.4, mus=True)
            if k in (2, 6): ajoute(caisse(.32), tt, mus=True)
            frenetique = P("v4") - .2 < tt < P("v5")
            if frenetique: ajoute(caisse(.16), tt + B / 4, pan=.1, mus=True); ajoute(charley(.8), tt + B / 4, pan=.3, mus=True)
        else:
            if k in (0, 4): ajoute(marimba(accords[mes % 4][k // 4] + 12, .22), tt, pan=-.2, mus=True)
    x0 += 4 * B; mes += 1
# C. Déclic : scratch, silence, glissando magique
ajoute(scratch(1.), T["dec"] - .04)
ajoute(glissando(T["si"] - T["dec"] - .35, .5), T["dec"] + .3)
# D. Solution : groove joyeux en do majeur
basse2 = [48, 55, 52, 55, 45, 52, 48, 52, 41, 48, 45, 48, 43, 50, 47, 50]
acc2 = [[60, 64, 67], [57, 60, 64], [53, 57, 60, 65], [55, 59, 62, 67]]
melodie = [(72, 1), (76, 1), (79, 2), (77, 1), (76, 1), (74, 2), (72, 1), (74, 1), (76, 1), (79, 1), (81, 2), (79, 2),
           (77, 1), (76, 1), (74, 1), (72, 1), (74, 2), (76, 2), (72, 4)]
x0 = T["si"] + .05; mes = 0
fin_mus = T["fin"] + .15
while x0 < fin_mus:
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= fin_mus: break
        ajoute(tuba(basse2[(mes * 4 + k // 2) % 16] - 12, B / 2 * .8, .45), tt, mus=True)
        if k % 2 == 1:
            for n in acc2[mes % 4]: ajoute(marimba(n, .14), tt, pan=-.3, mus=True)
        if k in (0, 4): ajoute(grosse(.7), tt, mus=True)
        if k in (2, 6): ajoute(claque(.45), tt, pan=.15, mus=True)
        ajoute(charley(.6 if k % 2 else .35), tt, pan=.35, mus=True)
    x0 += 4 * B; mes += 1
# mélodie sifflée (à partir de la notification, sous la fin)
tm = P("v10") + .1
for n, d in melodie:
    if tm + d * B / 2 > fin_mus: break
    ajoute(siffle(n, d * B / 2 * .92, .2), tm, pan=.2, mus=True); tm += d * B / 2
# jingle final « ta-da »
for k, n in enumerate([72, 76, 79]): ajoute(marimba(n, .5), T["fin"] + .05 + k * .09, mus=True)
for n in [60, 64, 67, 72]: ajoute(tuba(n - 12, .7, .25) if n == 60 else marimba(n, .3), T["fin"] + .35, mus=True)
ajoute(carillon([84, 88, 91, 96], .4), T["fin"] + .35, mus=True)

# ================= BRUITAGES =================
# Accroche
u = 0.
while u < T["whip"] - .2:
    ajoute(vibreur(.3, .45), u); ajoute(toc(.5), u + .3, pan=-.1); u += .62
ajoute(sonnette(.5, .22), .05, pan=-.3)
for i in range(6): ajoute(pop(800 + 90 * i, .45), .05 + i * .2, pan=.3)
t14 = Mo("v1", 0); ajoute(impact(1.1), t14 - .03); ajoute(pop(1400, .5), t14 + .05)
ajoute(pop(1700, .4), Mo("v1", 3) - .05)
# Extérieur : sifflotement, sursaut, pigeon, jonglage, splat, questions
tToi = Mo("v2", 1); tSaut = tToi + .75
ajoute(boing(260, .5, .5), tSaut); ajoute(vibreur(.5, .3), tToi + .6)
ajoute(battement_ailes(.9, .5), tToi + .75, pan=.5)
tj, tOr = P("v3") + .1, Mo("v3", 3)
for k in range(4): ajoute(boing(300 + 60 * k, .3, .3), tj + k * (tOr - tj) / 4, pan=(-.3 if k % 2 else .3))
ajoute(splat(.7), tOr - .05)
for i in range(5):
    t0 = P("v4") - .15 + i * .4
    ajoute(whoosh(.22, 600, 2500, .5), t0 - .05, pan=-.5 if i % 2 == 0 else .5); ajoute(impact(.55), t0 + .22, pan=-.2 if i % 2 == 0 else .2); ajoute(pop(1200, .3), t0 + .22)
for i in range(5): ajoute(pop(1700, .2), P("v5") - .2 + i * .02, pan=(i - 2) * .2)
# Chantier : iris, horloge qui s'emballe, pages, pied qui tape, vapeur
ajoute(whoosh(.4, 3500, 400, .6), T["ch"])
u = T["ch"] + .1
while u < T["pr"]: ajoute(tic(.4), u, pan=-.4); u += .09
for i in range(9): ajoute(page(.6), T["ch"] + .9 + i * 1.4 / 9, pan=.2)
u = T["ch"] + .9
while u < T["pr"]: ajoute(toc(.35), u, pan=.4); u += .29
ajoute(whoosh(.7, 2500, 6000, .25), T["ch"] + 1.4, pan=.4)
# Prospect : glissement, tonalité, raccroché, tap, avion, trombone triste
ajoute(whoosh(.45, 400, 2500, .8), T["pr"])
tRate = Mo("v6", 4); rac = tRate + .2; tSuiv = Mo("v6", 8)
u = T["pr"] + .3
while u < tRate - .15: ajoute(osc(440, .5) * env(int(.5 * SR), .01, .45, .4) * .1, u); u += .9
for k in range(3): ajoute(osc(480, .12) * env(int(.12 * SR), .005, .1, .5) * .13, rac - .1 + k * .16)
ajoute(clic(1.), tSuiv); ajoute(whoosh(.9, 800, 3000, .5), tSuiv + .15, pan=.6)
ajoute(trombone_triste(.32), tSuiv + .35)
# Soir : ambiance, ronflement léger, tasses
ajoute(pb(bruit(T["dec"] - T["so"]), 250) * .06, T["so"])
for i in range(4): ajoute(tinte(.25), T["so"] + i * .5, pan=.5)
# Site : entrée, étiquettes, curseur, photos, envoi, confettis
ajoute(whoosh(.5, 300, 5000, .8), T["si"] - .05); ajoute(impact(.6), T["si"] + .3)
for k in (1, 3, 6, 10): ajoute(pop(1100 + k * 30, .55), Mo("v9", k) - .05, pan=-.3 if k in (1, 6) else .3)
mots = [Mo("v9", k) for k in (1, 3, 6, 10)]
ajoute(whoosh(.6, 1200, 2200, .3), mots[1] + .3)
for i in range(4): ajoute(pop(1500 + 100 * i, .3), mots[3] + .3 + i * .125)
tEnvoi = min(mots[3] + .6, T["ap"] - .55)
ajoute(clic(1.), tEnvoi); ajoute(carillon([79, 84, 88], .45), tEnvoi + .15)
for i in range(6): ajoute(pop(1800 + 150 * i, .18), tEnvoi + .25 + i * .05, pan=(i - 3) * .2)
# Après : notification, saut de joie
tn = P("v10") + .2; tJoie = tn + .9
ajoute(carillon([88, 91], .5), tn); ajoute(vibreur(.4, .2), tn - .1)
ajoute(boing(220, .5, .5), tJoie - .02); ajoute(grosse(.6), tJoie + .44)
# Collègue : chute, atterrissage, tope-là
tc = Mo("v11", 5) - .25
ajoute(whoosh(.3, 4000, 600, .5), tc); ajoute(boing(150, .7, .6), tc + .3); ajoute(impact(.4), tc + .3)
ajoute(claque(1.3), Mo("v11", 8)); ajoute(impact(.4), Mo("v11", 8))
ajoute(pop(1300, .4), Mo("v11", 9))
# Pause café : tasse, « non » pince-sans-rire
ajoute(tinte(.35), P("v12") + .05)
tNon = Mo("v12", 4)
for k in range(4): ajoute(osc(260 if k % 2 else 230, .09) * env(int(.09 * SR), .004, .07) * .25, tNon + k * .14)
ajoute(whoosh(.3, 4000, 600, .4), T["fin"] - .1)

# ================= VOIX ET MIXAGE =================
with wave.open(str(ICI / "voix-mix.wav")) as w:
    v = np.frombuffer(w.readframes(w.getnframes()), np.int16).astype(np.float32) / 32768
v = np.pad(v, (0, max(0, N - len(v))))[:N]; v = v / (np.abs(v).max() + 1e-9) * .9; v = np.tanh(v * 1.5) / np.tanh(1.5)
blk = SR // 50
e = np.array([np.abs(v[i:i + blk]).mean() for i in range(0, N, blk)])
parle = (e > .02).astype(float); lis = np.zeros_like(parle); s = 0.
for i, p in enumerate(parle): s = max(p, s * .88); lis[i] = s
duck = np.repeat(1 - .6 * lis, blk)[:N]
Lm, Rm = ML * .5 * duck, MR * .5 * duck
Ls, Rs = SL * .55 * (1 - .3 * duck * 0), SR_ * .55
L = Lm + Ls + v; R = Rm + Rs + v
L *= np.minimum(1, (DUREE - np.arange(N) / SR) / .3); R *= np.minimum(1, (DUREE - np.arange(N) / SR) / .3)
c = max(np.abs(L).max(), np.abs(R).max()); L, R = L / c * .95, R / c * .95
with wave.open(str(ICI / "son.wav"), "wb") as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes((np.stack([L, R], 1) * 32767).astype(np.int16).tobytes())
print("son.wav", round(DUREE, 2), "s")

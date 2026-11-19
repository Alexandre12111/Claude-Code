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
T = dict(whip=P("l01") - .12, ch=P("l04") - .14, pr=P("l05") - .14, so=P("l06") - .2, dec=P("l07"), ba=Mo("l07", 4) - .05, si=P("l08") + .2, cn=P("l09") - .2,
         vol=Mo("l09", 9) + .1, fil=P("l10") - .1, co=P("l11") - .2, fin=CAL["finTexte"] if "finTexte" in CAL else F("l12") + .4)
T["ret"] = T["vol"] + .75

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
# A. Accroche : pizzicati pressés, bloc de bois
x0 = 0.
while x0 < T["whip"] - .05:
    for k, n in enumerate([57, 60, 64, 60, 57, 60, 64, 67]):
        tt = x0 + k * B / 2
        if tt < T["whip"] - .05: ajoute(pizz(n, .5), tt, pan=-.2 + .4 * (k % 2), mus=True); ajoute(bloc(.25, 1500 if k % 2 else 1100), tt, pan=.4, mus=True)
    x0 += 4 * B
# B. Problème : groove comique (tuba, pizzicati, bloc, caisse claire), frénétique sous les questions, endormi le soir
basse = [45, 45, 52, 52, 45, 45, 51, 52]; accords = [[57, 60, 64], [57, 60, 64], [56, 59, 64], [57, 60, 64]]
x0 = T["whip"] + .1; mes = 0
while x0 < T["dec"] - .05:
    endormi = x0 >= T["so"] - .1
    for k in range(8):
        tt = x0 + k * B / 2
        if tt >= T["dec"] - .05: break
        if not endormi or k % 2 == 0: ajoute(tuba(basse[k] - 12, B / 2 * .85, .5 if not endormi else .32), tt, mus=True)
        if not endormi:
            if k % 2 == 1:
                for n in accords[mes % 4]: ajoute(pizz(n + 12, .18), tt, pan=.25, mus=True)
            ajoute(bloc(.18, 1300 if k % 2 else 900), tt, pan=-.4, mus=True)
            if k in (2, 6): ajoute(caisse(.32), tt, mus=True)
            if P("l03") - .2 < tt < P("l04"): ajoute(caisse(.16), tt + B / 4, mus=True); ajoute(charley(.8), tt + B / 4, pan=.3, mus=True)
        elif k in (0, 4): ajoute(marimba(accords[mes % 4][k // 4] + 12, .22), tt, pan=-.2, mus=True)
    x0 += 4 * B; mes += 1
# C. Déclic : scratch, glissando, puis montée pendant la construction du barrage
ajoute(scratch(1.), T["dec"] - .04)
ajoute(glissando(max(.4, T["ba"] - T["dec"] - .3), .5), T["dec"] + .3)
# D. Solution : groove joyeux en do majeur, mélodie sifflée
basse2 = [48, 55, 52, 55, 45, 52, 48, 52, 41, 48, 45, 48, 43, 50, 47, 50]
acc2 = [[60, 64, 67], [57, 60, 64], [53, 57, 60, 65], [55, 59, 62, 67]]
melodie = [(72, 1), (76, 1), (79, 2), (77, 1), (76, 1), (74, 2), (72, 1), (74, 1), (76, 1), (79, 1), (81, 2), (79, 2), (77, 1), (76, 1), (74, 1), (72, 1), (74, 2), (76, 2), (72, 4)] * 2
x0 = T["ba"]; mes = 0; fin_mus = T["fin"] + .15
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
tm = T["si"]
for n, d in melodie:
    if tm + d * B / 2 > fin_mus: break
    ajoute(siffle(n, d * B / 2 * .92, .18), tm, pan=.2, mus=True); tm += d * B / 2
for k, n in enumerate([72, 76, 79]): ajoute(marimba(n, .5), T["fin"] + .05 + k * .09, mus=True)
ajoute(tuba(48, .7, .25), T["fin"] + .35, mus=True); ajoute(carillon([84, 88, 91, 96], .4), T["fin"] + .35, mus=True)

# ================= BRUITAGES =================
u = 0.
while u < T["whip"] - .2:
    ajoute(vibreur(.3, .45), u); ajoute(toc(.5), u + .28, pan=-.1); u += .58
ajoute(sonnette(.45, .2), .02, pan=-.3)
t14 = Mo("l00", 0)
for i in range(14): ajoute(clic(.6), .05 + i * (t14 + .1) / 14, pan=.2)
ajoute(impact(1.1), t14 + .1); ajoute(pop(1400, .5), t14 + .15)
t9 = Mo("l00", 6); ajoute(pop(1700, .4), t9 - .05); ajoute(boing(320, .5, .4), t9 - .2)
for i in range(6): ajoute(clic(.5), t9 + .05 + i * .07, pan=.3)   # dents qui claquent
ajoute(whoosh(.35, 200, 4000, 1.), T["whip"] - .2)
tToi = Mo("l01", 1); tRing = tToi + .55
ajoute(boing(260, .55, .5), tRing); ajoute(vibreur(.5, .3), tToi + .5); ajoute(battement_ailes(.9, .5), tToi + .6, pan=.5)
# V4 · il décroche : main qui descend, prise du téléphone, levée, regard caméra (ploc d'enduit)
tA, tC = Mo("l02", 1) - .2, Mo("l02", 2) + .02; tF = tC + .16; tMains, tEnd = Mo("l02", 5), Mo("l02", 7)
ajoute(whoosh(.3, 250, 900, .25), tA)
ajoute(somme(bloc(.35, 650), bp(bruit(.05), 900, 2) * env(int(.05 * SR), .002, .02) * .6), tC + .06)   # « tac » de la prise
ajoute(whoosh(.4, 300, 1800, .35), tF - .05, pan=.3)
ajoute(pop(380, .35), tMains + .8)                                                       # ploc de l'enduit
ajoute(marimba(67, .25), tEnd - .05, mus=True); ajoute(marimba(60, .22), tEnd + .12, mus=True)   # temps mort comique
# V4 · les quatre questions : une bulle par question, la voix au téléphone vient de voix-tel.wav
for i in range(4):
    q = f"q{i + 1}"
    if q in PH: ajoute(pop(820 + 110 * i, .32), P(q) - .12, pan=(-.25 if i % 2 == 0 else .25))
# V4 · raccord vers le chantier : souffle montant pendant la plongée dans l'écran, puis l'horloge
ajoute(whoosh(.45, 300, 2600, .45), T["ch"] - .42)
ajoute(glock(84, .25), T["ch"] + .02)
u, k = T["ch"] + .5, 0
while u < T["pr"] - .1: ajoute(bloc(.16, 1150 if k % 2 == 0 else 900), u, pan=-.3); u += .5; k += 1   # tic-tac doux
for i in range(9): ajoute(page(.3), T["ch"] + .9 + i * 1.4 / 9, pan=.2)
ajoute(whoosh(.45, 400, 2500, .6), T["pr"])
# V4 · le client raté : sonnerie, pas de réponse, il appelle le suivant
tRate = Mo("l05", 5); rac = tRate + .12; tSuiv = Mo("l05", 9) - .05
u = T["pr"] + .3
while u < tRate - .15: ajoute(osc(440, .5) * env(int(.5 * SR), .01, .45, .4) * .1, u); u += .9
for k in range(3): ajoute(osc(480, .12) * env(int(.12 * SR), .005, .1, .5) * .12, tRate + k * .16)
ajoute(whoosh(.35, 600, 2000, .25), rac)
ajoute(bloc(.3, 1800), tSuiv + .04)                                                    # appui sur « Appeler »
for k in range(2): ajoute(osc(425, .35) * env(int(.35 * SR), .01, .3, .5) * .09, tSuiv + .25 + k * .45)
ajoute(pb(bruit(max(.5, T["dec"] - T["so"])), 250) * .06, T["so"])
for i in range(4): ajoute(tinte(.25), T["so"] + i * .45, pan=.5)
# V4 · le plan : tracé, téléphone coulé, briques lancées à la truelle, questions qui trouvent leur réponse
ajoute(impact(.5), T["ba"] - .2); ajoute(claque(.5), T["ba"] - .2)
ajoute(bp(bruit(.55), 3200, 1.2) * env(int(.55 * SR), .02, .4, 2) * .25, T["ba"], pan=-.2)   # crayon sur le calque
ajoute(boing(200, .3, .4), T["ba"] + .35)
tb = [Mo("l07", 6) - .12, Mo("l07", 8) - .05, Mo("l07", 11) - .1, F("l07") - .1]
for i, x in enumerate(tb):
    ajoute(whoosh(.3, 500, 1600, .3), x, pan=.4 - i * .1)
    ajoute(somme(bloc(.4, 520 + 40 * i), grosse(.15)), x + .38)
ajoute(pop(1300, .3), tb[3] + .55)
tq = [P("l07b") + .05 + i * .3 for i in range(4)]
for i, x in enumerate(tq):
    ajoute(pop(1000 + 90 * i, .28), x + .45, pan=-.3)
    ajoute(glock(84 + [0, 2, 4, 7][i], .22), x + .5, mus=True)
ajoute(whoosh(.5, 300, 5000, .8), T["si"] - .05)
for k in (4, 6, 10): ajoute(pop(1100 + k * 30, .55), Mo("l08", k) - .05, pan=-.3 if k != 6 else .3)
ajoute(whoosh(.6, 1200, 2200, .3), Mo("l08", 6) + .3)
ajoute(whoosh(.5, 600, 2400, .5), T["cn"])
conv, dem = Mo("l09", 4), Mo("l09", 6); tEnv = T["vol"] - .55
ajoute(carillon([84, 88], .3), conv - .1)
for i in range(10): ajoute(clic(.45), dem - .1 + i * .05)
for i in range(4): ajoute(pop(1500 + 100 * i, .3), dem + .1 + i * .1)
ajoute(clic(1.), tEnv); ajoute(carillon([79, 84, 88], .45), tEnv + .25)
for i in range(6): ajoute(pop(1800 + 150 * i, .18), tEnv + .3 + i * .05, pan=(i - 3) * .2)
ajoute(whoosh(.8, 400, 3000, .9), T["vol"] - .1)
ajoute(carillon([88, 91], .5), T["ret"] + .05); ajoute(impact(.4), T["ret"] + .25); ajoute(claque(.6), T["ret"] + .25)
# V4 · le planning : trois devis épinglés, il décroche, tampons « Rappelé », filé vers le collègue
tcs = [Mo("l10", 1) - .1, Mo("l10", 4) - .1, Mo("l10", 8) - .15]; tAppel = Mo("l10", 9) - .05
for i, x in enumerate(tcs):
    ajoute(whoosh(.4, 500, 2000, .3), x, pan=-.4); ajoute(bloc(.3, 1900), x + .42); ajoute(glock(79 + 3 * i, .18), x + .45, mus=True)
ajoute(somme(bloc(.3, 650), bp(bruit(.05), 900, 2) * env(int(.05 * SR), .002, .02) * .5), tAppel - .05)
for i in range(3): ajoute(somme(grosse(.25), bloc(.25, 480)), tAppel + .15 + i * .18, pan=(i - 1) * .3)
ajoute(whoosh(.4, 600, 3500, .55), T["co"] - .1)
tc = Mo("l11", 4) - .2
ajoute(whoosh(.3, 4000, 600, .5), tc); ajoute(boing(150, .7, .6), tc + .3); ajoute(impact(.4), tc + .3)
ajoute(claque(1.3), Mo("l11", 7)); ajoute(impact(.4), Mo("l11", 7))
ajoute(tinte(.35), P("l12") + .05)
tNon = Mo("l12", 4)
for k in range(4): ajoute(osc(260 if k % 2 else 230, .09) * env(int(.09 * SR), .004, .07) * .25, tNon + k * .14)
ajoute(carillon([76, 72], .35), tNon + .2)
ajoute(whoosh(.3, 4000, 600, .4), T["fin"] - .1); ajoute(boing(240, .5, .5), T["fin"] + .55)

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

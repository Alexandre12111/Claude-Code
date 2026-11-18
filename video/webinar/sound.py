"""Habillage sonore de l'intro et de l'outro YouCraft : nappe, arpège, basse et pulsation légère (100 BPM),
whooshes, impacts et petits sons d'interface calés sur les animations (anim.js). Synthèse numpy, 48 kHz stéréo.
Usage : python3 sound.py intro|outro <sortie.wav>"""
import sys
import numpy as np
from scipy import signal
import soundfile as sf

SR = 48000
MODE, OUT = sys.argv[1], sys.argv[2]
DUR = {'intro': 7.2, 'outro': 10.0}[MODE]
N = int(DUR * SR)
rng = np.random.default_rng(5)
mix = np.zeros((N, 2))
t_all = np.arange(N) / SR


def note(n):  # numéro MIDI vers Hz
    return 440.0 * 2 ** ((n - 69) / 12)


def lp(x, f, o=2): return signal.sosfilt(signal.butter(o, f, 'low', fs=SR, output='sos'), x, axis=0)
def hp(x, f, o=2): return signal.sosfilt(signal.butter(o, f, 'high', fs=SR, output='sos'), x, axis=0)
def bp(x, a, b, o=2): return signal.sosfilt(signal.butter(o, [a, b], 'band', fs=SR, output='sos'), x, axis=0)


def add(sig, t0, g=1.0, pan=0.0):
    i0 = int(t0 * SR)
    if i0 >= N: return
    if sig.ndim == 1:
        sig = np.stack([sig * np.cos((pan + 1) * np.pi / 4), sig * np.sin((pan + 1) * np.pi / 4)], 1) * 1.414
    j = min(N, i0 + len(sig)); s0 = max(0, -i0)
    mix[max(0, i0):j] += sig[s0:j - i0] * g


def env(n, a, r, curve=4.0):
    e = np.ones(n); na = max(1, int(a * SR)); e[:na] = np.linspace(0, 1, na)
    e *= np.exp(-curve * np.arange(n) / max(1, int(r * SR)))
    return e


def reverb(x, dec=1.6, wet=0.3):
    n = int(dec * SR)
    ir = rng.standard_normal((n, 2)) * np.exp(-6.9 * np.arange(n) / n)[:, None]
    ir = lp(ir, 6000)
    y = np.stack([signal.fftconvolve(x[:, c], ir[:, c])[:len(x)] for c in range(2)], 1)
    y /= np.max(np.abs(y)) + 1e-9
    return x * (1 - wet) + y * wet * np.max(np.abs(x))


# ---------------------------------------------------------------- matière musicale
BPM = 100; BEAT = 60 / BPM
CH = {'F': [53, 57, 60, 64], 'C': [48, 55, 60, 64], 'Am': [45, 52, 57, 60], 'G': [43, 50, 55, 59], 'Dm': [50, 53, 57, 62]}


def pad(chord, dur, g=1.0):
    n = int(dur * SR); t = np.arange(n) / SR; y = np.zeros(n)
    for m in chord:
        for d in (-0.08, 0.0, 0.08):
            f = note(m + 12) * 2 ** (d / 12)
            y += signal.sawtooth(2 * np.pi * f * t + rng.random() * 6.28)
    y = lp(y, 1400, 2)
    e = np.minimum(1, t / 0.6) * np.minimum(1, (dur - t) / 0.5).clip(0)
    yl = y * e; yr = np.roll(yl, 240)
    return np.stack([yl, yr], 1) * g / 12


def pluck(m, dur=0.5):
    n = int(dur * SR); t = np.arange(n) / SR; f = note(m)
    y = (np.sin(2 * np.pi * f * t) + 0.35 * np.sin(4 * np.pi * f * t) + 0.12 * np.sin(6 * np.pi * f * t)) * np.exp(-t * 7)
    return y * np.minimum(1, t / 0.003)


def bass(m, dur):
    n = int(dur * SR); t = np.arange(n) / SR; f = note(m - 12)
    y = np.sin(2 * np.pi * f * t) + 0.25 * np.sin(4 * np.pi * f * t)
    return y * np.minimum(1, t / 0.01) * np.exp(-t * 1.2) * np.minimum(1, (dur - t) / 0.05).clip(0)


def kick(g=1.0):
    n = int(0.45 * SR); t = np.arange(n) / SR
    f = 48 + 110 * np.exp(-t * 32)
    y = np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 7)
    return y * g


def hat(g=1.0):
    n = int(0.08 * SR); y = hp(rng.standard_normal(n), 7000) * np.exp(-np.arange(n) / SR * 60)
    return y * g


def sweep_noise(dur, f0, f1, bw=0.8, curve=1.0):
    n = int(dur * SR)
    x = rng.standard_normal((2, n + 2048))
    f, tt, Z = signal.stft(x, SR, nperseg=1024, noverlap=896)
    prog = np.clip(tt / dur, 0, 1) ** curve
    fc = f0 * (f1 / f0) ** prog
    G = np.exp(-0.5 * (np.log2(np.maximum(f, 20)[:, None] / fc[None, :]) / bw) ** 2)
    _, y = signal.istft(Z * G[None], SR, nperseg=1024, noverlap=896)
    y = y[:, :n].T
    return y / (np.max(np.abs(y)) + 1e-9)


def whoosh(dur=0.6, up=True):
    y = sweep_noise(dur, 250 if up else 5000, 5000 if up else 260, 0.8, 1.5 if up else 0.7)
    tt = np.linspace(0, 1, len(y))
    e = (np.sin(np.pi * tt ** (0.75 if up else 0.4))) ** 1.6
    return y * e[:, None]


def riser(dur):
    y = sweep_noise(dur, 300, 7000, 1.0, 2.0)
    tt = np.linspace(0, 1, len(y)); n = len(y); t = np.arange(n) / SR
    tone = np.sin(2 * np.pi * np.cumsum(note(60) * 2 ** (tt * 2)) / SR) * 0.25
    return (y * 0.8 + tone[:, None]) * (tt ** 2)[:, None]


def impact(g=1.0):
    n = int(1.6 * SR); t = np.arange(n) / SR
    sub = np.sin(2 * np.pi * np.cumsum(38 + 70 * np.exp(-t * 18)) / SR) * np.exp(-t * 3.2)
    nz = lp(rng.standard_normal(n), 2500) * np.exp(-t * 9) * 0.5
    y = sub + nz
    return np.stack([y, np.roll(y, 300)], 1) * g


def pop(f=900, g=1.0):
    n = int(0.12 * SR); t = np.arange(n) / SR
    y = np.sin(2 * np.pi * np.cumsum(f * (1 + 0.6 * np.exp(-t * 60))) / SR) * np.exp(-t * 38)
    return y * np.minimum(1, t / 0.002) * g


def chime(m, g=1.0):
    n = int(1.8 * SR); t = np.arange(n) / SR; f = note(m)
    y = sum(a * np.sin(2 * np.pi * f * k * t) * np.exp(-t * (2.5 + k)) for k, a in ((1, 1), (2.76, 0.4), (5.4, 0.15)))
    return y * np.minimum(1, t / 0.004) * g


def music(start, end, prog, beat_from, g=1.0, fade_out=None):
    bar = 4 * BEAT; i = 0; t = start
    while t < end:
        c = CH[prog[i % len(prog)]]
        d = min(bar, end - t) + 0.4
        add(pad(c, d, 0.9), t, g)
        add(bass(c[0], min(bar, end - t)), t, 0.35 * g)
        for k in range(8):  # arpège en croches
            tk = t + k * BEAT / 2
            if tk >= end: break
            m = c[[0, 1, 2, 3, 2, 1, 2, 3][k]] + 24
            add(pluck(m), tk, 0.10 * g, pan=[-0.4, 0.4][k % 2])
        for k in range(4):
            tk = t + k * BEAT
            if beat_from <= tk < end:
                add(kick(), tk, 0.45 * g); add(hat(), tk + BEAT / 2, 0.05 * g, pan=0.3)
        t += bar; i += 1


if MODE == 'intro':
    music(0.0, 6.45, ['F', 'C', 'Am', 'G'], beat_from=1.25, g=0.55)
    add(whoosh(0.5, True), 0.05, 0.25)
    for k, tk in enumerate((0.35, 0.47, 0.59)): add(pop(700 + 180 * k), tk, 0.22, pan=[-0.3, 0.3, 0][k])
    add(impact(), 0.6, 0.55); add(chime(76), 0.6, 0.18); add(chime(83), 0.62, 0.1)
    for i in range(8): add(pop(1400 + 60 * i, 0.4), 1.3 + i * 0.045, 0.07, pan=-0.5 + i / 7)
    add(whoosh(0.7, True), 2.0, 0.12)
    for i in range(5): add(pop(1000 + 90 * i), 3.25 + i * 0.12, 0.16, pan=-0.6 + 0.3 * i)
    add(riser(1.2), 5.3, 0.3)
    add(impact(1.0), 6.5, 0.7); add(chime(77), 6.5, 0.15)
    add(whoosh(0.45, False), 6.58, 0.28)
    fade = np.ones(N); fade[int(6.75 * SR):] = np.linspace(1, 0, N - int(6.75 * SR)) ** 2
else:
    add(whoosh(0.6, False), 0.0, 0.3)
    add(chime(72), 0.35, 0.12)
    music(0.3, 9.6, ['F', 'C', 'Am', 'G', 'F', 'C', 'Dm', 'G'], beat_from=3.2, g=0.5)
    add(whoosh(0.5, True), 2.25, 0.16)
    for k, tk in enumerate((2.75, 2.87, 2.99)): add(pop(700 + 180 * k), tk, 0.2, pan=[-0.3, 0.3, 0][k])
    add(impact(0.8), 3.0, 0.45); add(chime(76), 3.0, 0.15)
    add(pop(900), 4.7, 0.2); add(chime(84), 4.75, 0.1)
    for i in range(3): add(pop(1200 + 150 * i, 0.7), 5.5 + 0.15 * i, 0.12, pan=-0.4 + 0.4 * i)
    add(chime(79), 8.6, 0.12)
    fade = np.ones(N); fade[int(8.9 * SR):] = np.linspace(1, 0, N - int(8.9 * SR)) ** 1.5

y = reverb(mix, 1.8, 0.22) * fade[:, None]
y = hp(y, 30)
y = np.tanh(y * 1.4) / np.tanh(1.4)
y *= 0.89 / (np.max(np.abs(y)) + 1e-9)
sf.write(OUT, y.astype(np.float32), SR, subtype='PCM_24')
print(OUT, round(DUR, 2), 's')

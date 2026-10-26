"""Musique originale synthétisée, calée sur la timeline de la vidéo (100 BPM, mesures à 1,2 s + 2,4 s * k)."""
import numpy as np
from scipy import signal
import soundfile as sf
import os

SR = 48000
DUR = 63.5
BPM = 100
BEAT = 60 / BPM
BAR = 4 * BEAT
OFF = 1.2
N = int(DUR * SR)
rng = np.random.default_rng(3)
OUT = os.path.join(os.path.dirname(__file__), 'stems')
os.makedirs(OUT, exist_ok=True)


def st():
    return np.zeros((N, 2))


def add(buf, t, x, gain=1.0, pan=0.0):
    i = int(t * SR)
    if i >= N:
        return
    x = x[: N - i]
    if x.ndim == 1:
        l, r = np.cos((pan + 1) * np.pi / 4), np.sin((pan + 1) * np.pi / 4)
        buf[i:i + len(x), 0] += x * gain * l * 1.414
        buf[i:i + len(x), 1] += x * gain * r * 1.414
    else:
        buf[i:i + len(x)] += x * gain


def mtof(m):
    return 440 * 2 ** ((m - 69) / 12)


def env_adsr(n, a, d, s, r, sus_len):
    a, d, r = int(a * SR), int(d * SR), int(r * SR)
    sl = max(0, int(sus_len * SR) - a - d)
    e = np.concatenate([np.linspace(0, 1, max(a, 1)), np.linspace(1, s, max(d, 1)), np.full(sl, s), np.linspace(s, 0, max(r, 1))])
    return e[:n] if len(e) >= n else np.pad(e, (0, n - len(e)))


def lp(x, fc, order=2):
    b, a = signal.butter(order, fc / (SR / 2), 'low')
    return signal.lfilter(b, a, x, axis=0)


def hp(x, fc, order=2):
    b, a = signal.butter(order, fc / (SR / 2), 'high')
    return signal.lfilter(b, a, x, axis=0)


def bp(x, f1, f2, order=2):
    b, a = signal.butter(order, [f1 / (SR / 2), f2 / (SR / 2)], 'band')
    return signal.lfilter(b, a, x, axis=0)


def saw(f, n, detune=0.0):
    t = np.arange(n) / SR
    ff = f * 2 ** (detune / 1200)
    return signal.sawtooth(2 * np.pi * ff * t + rng.uniform(0, 6.28)) * 0.5


def pad_note(m, dur, bright=1600):
    n = int((dur + 1.2) * SR)
    f = mtof(m)
    L = saw(f, n, -7) + saw(f, n, 5) * 0.8
    R = saw(f, n, 7) + saw(f, n, -4) * 0.8
    x = np.stack([L, R], 1)
    x = lp(x, bright, 2)
    e = env_adsr(n, 0.35, 0.4, 0.8, 1.1, dur)
    return x * e[:, None] * 0.12


def pluck(m, dur=0.45, bright=1.0):
    n = int((dur + 0.3) * SR)
    t = np.arange(n) / SR
    f = mtof(m)
    x = np.zeros(n)
    for k, a in [(1, 1.0), (2, 0.45 * bright), (3, 0.22 * bright), (4, 0.12 * bright), (6, 0.05 * bright)]:
        x += a * np.sin(2 * np.pi * f * k * t) * np.exp(-t * (6 + k * 3))
    x *= np.minimum(1, t / 0.003)
    return x * 0.22


def bell(m, dur=1.6):
    n = int(dur * SR)
    t = np.arange(n) / SR
    f = mtof(m)
    x = np.sin(2 * np.pi * f * t + 0.8 * np.sin(2 * np.pi * f * 3.5 * t) * np.exp(-t * 4)) * np.exp(-t * 2.2)
    x += 0.3 * np.sin(2 * np.pi * f * 2 * t) * np.exp(-t * 3.5)
    x *= np.minimum(1, t / 0.002)
    return x * 0.2


def bass_note(m, dur):
    n = int((dur + 0.08) * SR)
    t = np.arange(n) / SR
    f = mtof(m)
    x = np.sin(2 * np.pi * f * t) + 0.25 * np.sin(2 * np.pi * 2 * f * t) + 0.08 * np.sin(2 * np.pi * 3 * f * t)
    x = np.tanh(x * 1.4)
    e = np.minimum(1, t / 0.006) * np.exp(-t * 2.2) * np.clip((dur + 0.08 - t) / 0.06, 0, 1)
    return lp(x * e, 900) * 0.32


def kick(g=1.0):
    n = int(0.5 * SR)
    t = np.arange(n) / SR
    f = 45 + 95 * np.exp(-t * 28)
    ph = 2 * np.pi * np.cumsum(f) / SR
    x = np.sin(ph) * np.exp(-t * 7.5)
    x += 0.25 * lp(rng.standard_normal(n), 3000) * np.exp(-t * 180)
    return np.tanh(x * 1.6) * 0.55 * g


def clap(g=1.0):
    n = int(0.35 * SR)
    t = np.arange(n) / SR
    nz = bp(rng.standard_normal(n), 900, 4200)
    e = np.zeros(n)
    for d in (0, 0.011, 0.022):
        e += (t >= d) * np.exp(-np.maximum(0, t - d) * 60) * 0.6
    e += (t >= 0.03) * np.exp(-np.maximum(0, t - 0.03) * 16)
    return nz * e * 0.22 * g


def hat(open_=False, g=1.0):
    n = int((0.3 if open_ else 0.08) * SR)
    t = np.arange(n) / SR
    x = hp(rng.standard_normal(n), 7500)
    return x * np.exp(-t * (14 if open_ else 70)) * 0.08 * g


def shaker(g=1.0):
    n = int(0.12 * SR)
    t = np.arange(n) / SR
    x = bp(rng.standard_normal(n), 5000, 11000)
    e = np.sin(np.pi * np.clip(t / 0.09, 0, 1)) ** 2
    return x * e * 0.05 * g


def riser(dur, g=1.0):
    n = int(dur * SR)
    t = np.arange(n) / SR
    x = rng.standard_normal(n)
    out = np.zeros(n)
    seg = 2048
    for i in range(0, n, seg):
        p = i / n
        fc = 400 + 7000 * p ** 2
        b, a = signal.butter(2, [fc * 0.7 / (SR / 2), min(0.99, fc * 1.3 / (SR / 2))], 'band')
        out[i:i + seg] = signal.lfilter(b, a, x[i:i + seg])
    out *= (t / dur) ** 2.2
    return out * 0.2 * g


def impact(g=1.0):
    n = int(2.5 * SR)
    t = np.arange(n) / SR
    f = 38 + 60 * np.exp(-t * 10)
    x = np.sin(2 * np.pi * np.cumsum(f) / SR) * np.exp(-t * 2.5)
    x += 0.35 * lp(rng.standard_normal(n), 1800) * np.exp(-t * 9)
    return np.tanh(x * 1.3) * 0.5 * g


def reverb(x, secs=2.4, mix=0.25, predelay=0.02):
    n = int(secs * SR)
    t = np.arange(n) / SR
    irL = rng.standard_normal(n) * np.exp(-t * 6.9 / secs)
    irR = rng.standard_normal(n) * np.exp(-t * 6.9 / secs)
    irL, irR = lp(irL, 5000), lp(irR, 5000)
    pd = int(predelay * SR)
    irL = np.concatenate([np.zeros(pd), irL]); irR = np.concatenate([np.zeros(pd), irR])
    irL /= np.sqrt(np.sum(irL ** 2)); irR /= np.sqrt(np.sum(irR ** 2))
    wl = signal.fftconvolve(x[:, 0], irL)[: len(x)]
    wr = signal.fftconvolve(x[:, 1], irR)[: len(x)]
    return x * (1 - mix) + np.stack([wl, wr], 1) * mix


def delay(x, time, fb=0.35, mix=0.3):
    d = int(time * SR)
    y = x.copy()
    for k in range(1, 5):
        sh = np.zeros_like(x)
        sh[d * k:] = x[: len(x) - d * k] * (fb ** k)
        if k % 2:
            sh = sh[:, ::-1]
        y += sh * mix
    return y


def bar_t(k, beat=0.0):
    return OFF + k * BAR + beat * BEAT


def main():
    # Accords (notes MIDI) par mesure.
    Am = [57, 60, 64]; F = [53, 57, 60]; C = [48, 55, 60, 64]; G = [55, 59, 62]; Dm = [50, 57, 62, 65]
    E_ = [52, 56, 59, 64]; Fmaj7 = [53, 57, 60, 64]; Csus = [48, 55, 60, 65]; Em = [52, 55, 59, 64]
    PROG = {
        -1: Am, 0: Am, 1: F, 2: C, 3: G, 4: Am,
        5: Dm, 6: Am, 7: E_,
        8: F, 9: G, 10: Am, 11: C, 12: F, 13: G, 14: Am,
        15: F, 16: G, 17: Am, 18: C,
        19: F, 20: C, 21: G,
        22: Fmaj7, 23: G, 24: C,
    }
    ROOT = {k: min(v) for k, v in PROG.items()}

    pad = st(); arp = st(); bass = st(); drums = st(); fx = st(); keys = st()

    # Pad
    for k, ch in PROG.items():
        t0 = bar_t(k)
        dur = BAR if k != 24 else 3.4
        if k == -1:
            t0, dur = 0.0, OFF
        bright = 900 if k in (5, 6, 7) else 1300 if k < 8 else 2000
        if k >= 22:
            bright = 1500
        for m in ch:
            add(pad, max(0, t0), pad_note(m + 12 if k >= 15 and k <= 18 else m, dur, bright), 1.0 if k >= 0 else 0.7)

    # Arpège (croches, puis doubles croches pendant la démo et la preuve)
    for k, ch in PROG.items():
        if k < 0 or k >= 24:
            continue
        notes = sorted(ch)
        seq = [notes[0] + 12, notes[1] + 12, notes[2] + 12, notes[-1] + 12 if len(notes) > 3 else notes[1] + 24]
        step = 0.5 if k < 8 or k >= 19 else 0.25
        if k in (5, 6, 7):
            step = 0.5
        nsteps = int(4 / step)
        for i in range(nsteps):
            m = seq[i % len(seq)] + (12 if (i // len(seq)) % 2 and step == 0.25 else 0)
            vel = 0.75 + 0.25 * ((i % 4) == 0)
            g = 0.55 if k < 5 else 0.4 if k < 8 else 0.62 if k < 19 else 0.5
            if k >= 22:
                g = 0.35
            add(arp, bar_t(k, i * step), pluck(m, bright=0.8 if k < 8 else 1.1), g * vel, pan=0.35 * np.sin(i * 1.3))
    arp = delay(arp, BEAT * 0.75, 0.35, 0.35)

    # Piano (notes tenues) sur l'intro et la fin
    for k in (0, 1, 2, 3, 4, 22, 23):
        ch = PROG[k]
        for j, m in enumerate(sorted(ch)):
            add(keys, bar_t(k) + j * 0.03, bell(m + 12, 2.2), 0.5, pan=-0.2 + j * 0.15)
    add(keys, bar_t(24), bell(72, 3.5), 0.6)
    add(keys, bar_t(24) + 0.04, bell(76, 3.5), 0.5, pan=0.2)
    add(keys, bar_t(24) + 0.08, bell(79, 3.5), 0.45, pan=-0.2)
    add(keys, bar_t(24) + 0.12, bell(84, 3.5), 0.4, pan=0.3)
    # Motif d'ouverture sur le logo
    for i, (t, m) in enumerate([(0.15, 76), (0.55, 79), (1.05, 84), (1.45, 83), (1.95, 79)]):
        add(keys, t, bell(m, 2.0), 0.55, pan=-0.3 + 0.15 * i)

    # Basse
    for k, ch in PROG.items():
        if k < 8 or k >= 24:
            continue
        r = ROOT[k] - 12
        if r < 36:
            r += 12
        pat = [0, 1, 1.5, 2, 3, 3.5] if k < 19 else [0, 2, 3]
        if k >= 22:
            pat = [0]
        for b in pat:
            add(bass, bar_t(k, b), bass_note(r, BEAT * (0.9 if b % 1 == 0 else 0.45)), 1.0)
    # basse longue sur l'accord final
    add(bass, bar_t(22), bass_note(41, BAR * 0.95), 0.9)
    add(bass, bar_t(23), bass_note(43, BAR * 0.95), 0.9)
    add(bass, bar_t(24), bass_note(36, 3.0), 1.0)

    # Batterie
    kicks = []
    for k in range(0, 24):
        if k in (0, 1):
            if k == 1:
                for b in (0, 2):
                    add(drums, bar_t(k, b), shaker(), 0.8)
            continue
        for b in range(4):
            t = bar_t(k, b)
            if 2 <= k <= 4:
                if b == 0:
                    add(drums, t, kick(0.6)); kicks.append(t)
                for s in (0, 0.5):
                    add(drums, bar_t(k, b + s), shaker(), 0.9 if s else 0.5, pan=0.3)
            elif 5 <= k <= 7:
                for s in (0, 0.25, 0.5, 0.75):
                    add(drums, bar_t(k, b + s), hat(), 0.5 + 0.4 * (s == 0.5), pan=0.25)
                if b == 0 and k != 7:
                    add(drums, t, kick(0.5)); kicks.append(t)
            elif 8 <= k <= 18:
                add(drums, t, kick(0.85 if k >= 15 else 0.75)); kicks.append(t)
                if b in (1, 3):
                    add(drums, t, clap(1.1 if k >= 15 else 0.9), 1.0, pan=0.05)
                add(drums, bar_t(k, b + 0.5), hat(), 1.0, pan=0.2)
                if k >= 15:
                    add(drums, bar_t(k, b + 0.25), hat(), 0.4, pan=-0.2)
                    add(drums, bar_t(k, b + 0.75), hat(), 0.4, pan=-0.2)
            elif 19 <= k <= 21:
                if b in (0, 2) and not (k == 21 and b == 2):
                    add(drums, t, kick(0.65)); kicks.append(t)
                if b == 3 and k < 21:
                    add(drums, t, clap(0.7))
                add(drums, bar_t(k, b + 0.5), shaker(), 1.0, pan=0.3)
            elif k in (22, 23):
                if b == 0 and k == 22:
                    add(drums, t, kick(0.7)); kicks.append(t)
    # Roulement avant la publication (mesure 12, 2e moitié)
    for i in range(8):
        add(drums, bar_t(12, 2 + i * 0.25), clap(0.35 + i * 0.08), 1.0)
    # Crash doux / impacts
    for t in (bar_t(8), bar_t(15), bar_t(22)):
        add(drums, t, hat(True, 2.5), 1.0)
    for t, g in ((3.9, 0.55), (20.4, 0.7), (37.2, 0.9), (54.0, 0.85)):
        add(fx, t, impact(g))
    add(fx, 20.4 - 2.4, riser(2.4, 1.0))
    add(fx, 37.2 - 2.0, riser(2.0, 0.9))
    add(fx, 13.2 - 1.2, riser(1.2, 0.6))
    add(fx, 54.0 - 1.6, riser(1.6, 0.6))

    # Sidechain sur pad / basse / arpège
    sc = np.ones(N)
    for t in kicks:
        i = int(t * SR)
        n = int(0.28 * SR)
        seg = 1 - 0.55 * np.exp(-np.arange(n) / SR * 14)
        j = min(N, i + n)
        sc[i:j] = np.minimum(sc[i:j], seg[: j - i])
    pad *= sc[:, None]; bass *= sc[:, None]; arp *= (0.5 + 0.5 * sc)[:, None]

    pad = reverb(pad, 3.0, 0.35)
    arp = reverb(arp, 2.2, 0.25)
    keys = reverb(keys, 3.2, 0.4)
    drums = reverb(drums, 1.2, 0.12)
    fx = reverb(fx, 2.5, 0.3)

    mix = pad * 0.9 + arp * 0.8 + keys * 0.9 + bass * 1.0 + drums * 0.95 + fx * 0.8
    mix = hp(mix, 30)
    # Fondu final
    fade = np.ones(N)
    t = np.arange(N) / SR
    fade[t > 60.2] = np.clip(1 - (t[t > 60.2] - 60.2) / 2.8, 0, 1) ** 1.5
    fade[t < 0.03] = t[t < 0.03] / 0.03
    mix *= fade[:, None]
    peak = np.max(np.abs(mix))
    mix = mix / peak * 0.89
    mix = np.tanh(mix * 1.15) / np.tanh(1.15)
    sf.write(os.path.join(OUT, 'music.wav'), mix.astype(np.float32), SR)
    print('music ok, peak before norm', round(float(peak), 3))


if __name__ == "__main__":
    main()

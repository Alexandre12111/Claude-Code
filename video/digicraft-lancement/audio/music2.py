"""Musique V2 (30 s, 120 BPM) : tension, drop sur DigiCraft à 5,5 s, résolution sur le logo final."""
import numpy as np
import soundfile as sf
import os
import music as M
from music import SR, st, add, lp, hp, reverb, delay, pad_note, pluck, bell, bass_note, kick, clap, hat, shaker, riser, impact

DUR = 31.5
M.N = N = int(DUR * SR)
BEAT = 0.5
BAR = 2.0
OFF = 5.5


def bt(k, b=0.0):
    return OFF + k * BAR + b * BEAT


def S():
    return np.zeros((N, 2))


def main():
    Am = [57, 60, 64]; F = [53, 57, 60]; C = [48, 55, 60, 64]; G = [55, 59, 62]; Dm = [50, 57, 62]
    Fmaj7 = [53, 57, 60, 64]
    # Mesures après le drop (k = 0 à 12, soit 5,5 s à 31,5 s)
    prog = {0: F, 1: G, 2: Am, 3: C, 4: F, 5: G, 6: C, 7: G, 8: Am, 9: F, 10: Fmaj7, 11: C, 12: C}
    pad, arp, bass, drums, fx, keys = S(), S(), S(), S(), S(), S()

    # Intro tendue 0 à 5,5 s : drone Am, basse pulsée, tic-tac.
    for m in (45, 57, 64):
        add(pad, 0.0, pad_note(m, 3.7, 900)[: int(4.6 * SR)], 0.9)
    for m in (53, 60, 65):
        add(pad, 3.9, pad_note(m, 1.3, 1400)[: int(2.2 * SR)], 0.9)
    for i in range(16):
        t = i * 0.25
        if t < 3.85:
            add(bass, t, bass_note(33 if i % 8 else 45, 0.2), 0.9)
    for i in range(32):
        t = i * 0.125
        if t < 3.85:
            add(drums, t, hat(False, 0.7 if i % 2 else 1.0), 0.8, pan=0.3)
    add(drums, 0.0, kick(1.0)); add(drums, 1.5, kick(0.8)); add(drums, 2.5, kick(0.8)); add(drums, 3.0, kick(0.8)); add(drums, 3.5, kick(0.9))
    add(fx, 0.0, impact(0.8))
    add(fx, 3.85, impact(0.5))
    add(fx, 4.0, riser(1.45, 1.2))
    for i, m in enumerate((76, 79, 83, 84)):
        add(keys, 4.0 + i * 0.25, bell(m, 1.2), 0.5, pan=-0.3 + 0.2 * i)

    kicks = []
    for k, ch in prog.items():
        t0 = bt(k)
        last = k >= 10
        for m in ch:
            add(pad, t0, pad_note(m + (12 if 6 <= k <= 9 else 0), BAR if k < 12 else 2.6, 2200 if k < 10 else 1500), 1.0)
        notes = sorted(ch)
        seq = [notes[0] + 12, notes[1] + 12, notes[2] + 12, notes[1] + 24]
        step = 0.25 if k < 10 else 0.5
        for i in range(int(4 / step)):
            if k == 12 and i > 1:
                break
            m = seq[i % 4] + (12 if (i // 4) % 2 and step == 0.25 else 0)
            add(arp, bt(k, i * step), pluck(m, bright=1.1), 0.6 if k < 10 else 0.4, pan=0.4 * np.sin(i * 1.3))
        r = min(ch) - 12
        if r < 36:
            r += 12
        if k < 10:
            for b in (0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5):
                add(bass, bt(k, b), bass_note(r + (12 if b % 1 else 0), 0.22), 1.0)
        else:
            add(bass, bt(k), bass_note(r, 1.8 if k < 12 else 2.6), 1.0)
        for b in range(4):
            t = bt(k, b)
            if k < 10 and k != 3:
                add(drums, t, kick(0.9)); kicks.append(t)
                if b in (1, 3):
                    add(drums, t, clap(1.1))
                add(drums, bt(k, b + 0.5), hat(False, 1.2), 1.0, pan=0.2)
                add(drums, bt(k, b + 0.25), hat(False, 0.5), 1.0, pan=-0.2)
                add(drums, bt(k, b + 0.75), hat(False, 0.5), 1.0, pan=-0.2)
            elif k == 3:
                if b < 2:
                    add(drums, t, kick(0.9)); kicks.append(t)
                add(drums, bt(k, b + 0.5), shaker(1.3), 1.0, pan=0.3)
            elif k in (10, 11) and b in (0,):
                add(drums, t, kick(0.7)); kicks.append(t)
                add(drums, bt(k, b + 2), shaker(1.2), 1.0)
    # roulement avant la preuve (mesure 5, fin)
    for i in range(8):
        add(drums, bt(5, 2 + i * 0.25), clap(0.3 + i * 0.1), 1.0)
    # breakdown avant les plans « 0 » : coupe à 12,3 s puis impacts
    for t, g in ((OFF, 1.1), (12.5, 0.9), (13.5, 0.9), (16.9, 0.9), (26.2, 1.0)):
        add(fx, t, impact(g))
        add(drums, t, hat(True, 2.5), 1.0)
    add(fx, 5.5 - 0.02, hat(True, 3.0).repeat(1))
    add(fx, 16.9 - 1.4, riser(1.4, 0.8))
    add(fx, 26.2 - 1.2, riser(1.2, 0.7))
    for j, m in enumerate((72, 76, 79, 84)):
        add(keys, 26.2 + j * 0.04, bell(m, 3.5), 0.55, pan=-0.3 + 0.2 * j)

    sc = np.ones(N)
    for t in kicks:
        i = int(t * SR); n = int(0.22 * SR)
        seg = 1 - 0.6 * np.exp(-np.arange(n) / SR * 16)
        j = min(N, i + n); sc[i:j] = np.minimum(sc[i:j], seg[: j - i])
    pad *= sc[:, None]; bass *= sc[:, None]; arp *= (0.5 + 0.5 * sc)[:, None]
    arp = delay(arp, 0.375, 0.3, 0.3)
    pad = reverb(pad, 2.6, 0.3); arp = reverb(arp, 2.0, 0.22); keys = reverb(keys, 3.0, 0.4)
    drums = reverb(drums, 1.0, 0.1); fx = reverb(fx, 2.2, 0.28)
    mix = pad * 0.85 + arp * 0.8 + keys * 0.9 + bass * 1.05 + drums * 1.0 + fx * 0.85
    mix = hp(mix, 30)
    t = np.arange(N) / SR
    fade = np.ones(N)
    fade[t > 29.0] = np.clip(1 - (t[t > 29.0] - 29.0) / 2.4, 0, 1) ** 1.5
    mix *= fade[:, None]
    mix = mix / np.max(np.abs(mix)) * 0.9
    mix = np.tanh(mix * 1.2) / np.tanh(1.2)
    os.makedirs(os.path.join(os.path.dirname(__file__), 'stems'), exist_ok=True)
    sf.write(os.path.join(os.path.dirname(__file__), 'stems', 'music2.wav'), mix.astype(np.float32), SR)
    print('music2 ok')


if __name__ == '__main__':
    main()

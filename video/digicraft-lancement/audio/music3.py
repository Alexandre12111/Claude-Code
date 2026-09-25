"""Musique V3 (51 s, 100 BPM) : mesures calées sur les transitions du montage (3,6 s + 2,4 s * k)."""
import os
import numpy as np
import soundfile as sf
import music as M
from music import SR, add, hp, reverb, delay, pad_note, pluck, bell, bass_note, kick, clap, hat, shaker, riser, impact

DUR = 52.5
M.N = N = int(DUR * SR)
BEAT = 0.6
BAR = 2.4
OFF = 3.6


def bt(k, b=0.0):
    return OFF + k * BAR + b * BEAT


def S():
    return np.zeros((N, 2))


def main():
    Am = [57, 60, 64]; F = [53, 57, 60]; C = [48, 55, 60, 64]; G = [55, 59, 62]
    Dm = [50, 57, 62, 65]; E_ = [52, 56, 59, 64]; Fmaj7 = [53, 57, 60, 64]
    prog = {0: Am, 1: F, 2: C, 3: G, 4: Dm, 5: Am, 6: E_, 7: F, 8: G, 9: Am, 10: C, 11: F, 12: G,
            13: C, 14: G, 15: Am, 16: F, 17: Am, 18: Fmaj7, 19: C}
    pad, arp, bass, drums, fx, keys = S(), S(), S(), S(), S(), S()

    # Ouverture sur le logo
    for m in (45, 57, 64, 69):
        add(pad, 0.0, pad_note(m, OFF, 1300), 0.8)
    for i, (t, m) in enumerate([(0.2, 76), (0.6, 79), (1.0, 84), (1.4, 83), (1.9, 79), (2.5, 88)]):
        add(keys, t, bell(m, 2.0), 0.55, pan=-0.3 + 0.12 * i)
    add(fx, OFF - 1.6, riser(1.6, 0.7))

    kicks = []
    for k, ch in prog.items():
        t0 = bt(k)
        dur = BAR if k < 19 else 3.6
        bright = 900 if 4 <= k <= 6 else 1400 if k < 7 else 2100 if k < 17 else 1500
        for m in ch:
            add(pad, t0, pad_note(m + (12 if 13 <= k <= 16 else 0), dur, bright), 1.0)
        notes = sorted(ch)
        seq = [notes[0] + 12, notes[1] + 12, notes[2] + 12, notes[-1] + 12 if len(notes) > 3 else notes[1] + 24]
        step = 0.25 if 7 <= k <= 16 else 0.5
        if k < 19:
            for i in range(int(4 / step)):
                m = seq[i % 4] + (12 if (i // 4) % 2 and step == 0.25 else 0)
                g = 0.5 if k < 4 else 0.38 if k < 7 else 0.6 if k < 17 else 0.4
                add(arp, bt(k, i * step), pluck(m, bright=0.8 if k < 7 else 1.1), g * (0.75 + 0.25 * (i % 4 == 0)), pan=0.35 * np.sin(i * 1.3))
        r = min(ch) - 12
        if r < 36:
            r += 12
        if 7 <= k <= 16:
            for b in (0, 1, 1.5, 2, 3, 3.5):
                add(bass, bt(k, b), bass_note(r, BEAT * (0.9 if b % 1 == 0 else 0.45)), 1.0)
        elif k >= 17:
            add(bass, t0, bass_note(r, dur * 0.95), 0.9)
        for b in range(4):
            t = bt(k, b)
            if 1 <= k <= 3:
                if b == 0 and k >= 2:
                    add(drums, t, kick(0.6)); kicks.append(t)
                for s in (0, 0.5):
                    add(drums, bt(k, b + s), shaker(), 0.9 if s else 0.5, pan=0.3)
            elif 4 <= k <= 6:
                for s in (0, 0.25, 0.5, 0.75):
                    add(drums, bt(k, b + s), hat(), 0.5 + 0.4 * (s == 0.5), pan=0.25)
                if b == 0 and k != 6:
                    add(drums, t, kick(0.5)); kicks.append(t)
            elif 7 <= k <= 16:
                add(drums, t, kick(0.85 if k >= 13 else 0.75)); kicks.append(t)
                if b in (1, 3):
                    add(drums, t, clap(1.1 if k >= 13 else 0.9))
                add(drums, bt(k, b + 0.5), hat(), 1.0, pan=0.2)
                if k >= 13:
                    add(drums, bt(k, b + 0.25), hat(), 0.4, pan=-0.2)
                    add(drums, bt(k, b + 0.75), hat(), 0.4, pan=-0.2)
            elif k == 18 and b == 0:
                add(drums, t, kick(0.6)); kicks.append(t)
                add(drums, bt(k, 2), shaker(1.2), 1.0)
    for i in range(8):
        add(drums, bt(12, 2 + i * 0.25), clap(0.3 + i * 0.09), 1.0)
    for t, g in ((OFF, 0.8), (bt(4), 0.6), (bt(7), 1.0), (bt(13), 1.0), (bt(17), 0.9), (46.2, 0.7)):
        add(fx, t, impact(g))
        add(drums, t, hat(True, 2.2), 1.0)
    add(fx, bt(7) - 2.4, riser(2.4, 1.0))
    add(fx, bt(13) - 2.0, riser(2.0, 0.9))
    add(fx, bt(17) - 1.4, riser(1.4, 0.7))
    for j, m in enumerate((72, 76, 79, 84)):
        add(keys, bt(19) + j * 0.04, bell(m, 3.6), 0.5, pan=-0.3 + 0.2 * j)
    for k in (17, 18):
        for j, m in enumerate(sorted(prog[k])):
            add(keys, bt(k) + j * 0.03, bell(m + 12, 2.2), 0.45, pan=-0.2 + j * 0.15)

    sc = np.ones(N)
    for t in kicks:
        i = int(t * SR); n = int(0.28 * SR)
        seg = 1 - 0.55 * np.exp(-np.arange(n) / SR * 14)
        j = min(N, i + n); sc[i:j] = np.minimum(sc[i:j], seg[: j - i])
    pad *= sc[:, None]; bass *= sc[:, None]; arp *= (0.5 + 0.5 * sc)[:, None]
    arp = delay(arp, BEAT * 0.75, 0.35, 0.33)
    pad = reverb(pad, 3.0, 0.33); arp = reverb(arp, 2.2, 0.25); keys = reverb(keys, 3.2, 0.4)
    drums = reverb(drums, 1.2, 0.12); fx = reverb(fx, 2.5, 0.3)
    mix = pad * 0.9 + arp * 0.8 + keys * 0.9 + bass * 1.0 + drums * 0.95 + fx * 0.8
    mix = hp(mix, 30)
    t = np.arange(N) / SR
    fade = np.ones(N)
    fade[t > 49.6] = np.clip(1 - (t[t > 49.6] - 49.6) / 1.4, 0, 1) ** 1.3
    fade[t < 0.03] = t[t < 0.03] / 0.03
    mix *= fade[:, None]
    mix = mix / np.max(np.abs(mix)) * 0.89
    mix = np.tanh(mix * 1.15) / np.tanh(1.15)
    sf.write(os.path.join(os.path.dirname(os.path.abspath(__file__)), 'stems', 'music3.wav'), mix.astype(np.float32), SR)
    print('music3 ok')


if __name__ == '__main__':
    main()

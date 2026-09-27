"""Musique V4 (57 s, 100 BPM) : grille calée sur 3,15 s, sections alignées sur les transitions du montage."""
import os
import sys
import numpy as np
import soundfile as sf
import music as M
from music import SR, add, hp, reverb, delay, pad_note, pluck, bell, bass_note, kick, clap, hat, shaker, riser, impact

sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'render'))
import retime  # noqa: E402

DUR = 58.6
M.N = N = int(DUR * SR)
BEAT = 0.6
BAR = 2.4
OFF = 3.15
END = 57.05


def bt(k, b=0.0):
    return OFF + k * BAR + b * BEAT


def S():
    return np.zeros((N, 2))


Am = [57, 60, 64]; F = [53, 57, 60]; C = [48, 55, 60, 64]; G = [55, 59, 62]
Dm = [50, 57, 62, 65]; E_ = [52, 56, 59, 64]; Fmaj7 = [53, 57, 60, 64]

# (mesure de départ, durée en mesures, accord, section)
CHORDS = [
    (0, 1, Am, 'valeur'), (1, 1, F, 'valeur'), (2, 1, C, 'valeur'), (3, 1, G, 'valeur'),
    (4, 1, Dm, 'idees'), (5, 1, Am, 'idees'), (6, 1, E_, 'idees'),
    (7, 1, F, 'demo'), (8, 1, G, 'demo'), (9, 1, Am, 'demo'), (10, 1, C, 'demo'), (11, 1, F, 'demo'), (12, 1, G, 'demo'), (13, 1, Am, 'demo'),
    (14, 1, C, 'chiffres'), (15, 1, G, 'chiffres'), (16, 1, Am, 'chiffres'),
    (17, 1, F, 'serenite'), (18, 1, C, 'serenite'), (19, 0.5, G, 'serenite'),
    (19.5, 1, Am, 'cree'),
    (20.5, 1, Fmaj7, 'fin'), (21.5, 1.4, C, 'fin'),
]
ENERGY = {'valeur': 0.5, 'idees': 0.38, 'demo': 0.62, 'chiffres': 0.66, 'serenite': 0.5, 'cree': 0.35, 'fin': 0.35}


def main():
    pad, arp, bass, drums, fx, keys = S(), S(), S(), S(), S(), S()
    for m in (45, 57, 64, 69):
        add(pad, 0.0, pad_note(m, OFF, 1300), 0.8)
    for i, (t, m) in enumerate([(0.2, 76), (0.6, 79), (1.0, 84), (1.4, 83), (1.9, 79), (2.4, 88)]):
        add(keys, t, bell(m, 2.0), 0.55, pan=-0.3 + 0.12 * i)
    add(fx, OFF - 1.4, riser(1.4, 0.7))

    kicks = []
    for k0, nb, ch, sec in CHORDS:
        t0 = bt(k0)
        dur = nb * BAR
        bright = 900 if sec == 'idees' else 1400 if sec == 'valeur' else 2100 if sec in ('demo', 'chiffres') else 1500
        up = 12 if sec == 'chiffres' else 0
        for m in ch:
            add(pad, t0, pad_note(m + up, dur, bright), 1.0)
        notes = sorted(ch)
        seq = [notes[0] + 12, notes[1] + 12, notes[2] + 12, notes[-1] + 12 if len(notes) > 3 else notes[1] + 24]
        step = 0.25 if sec in ('demo', 'chiffres') else 0.5
        if sec != 'fin' or k0 < 21:
            for i in range(int(4 * nb / step)):
                m = seq[i % 4] + (12 if (i // 4) % 2 and step == 0.25 else 0)
                add(arp, t0 + i * step * BEAT, pluck(m, bright=0.8 if sec in ('valeur', 'idees') else 1.1), ENERGY[sec] * (0.75 + 0.25 * (i % 4 == 0)), pan=0.35 * np.sin(i * 1.3))
        r = min(ch) - 12
        if r < 36:
            r += 12
        if sec in ('demo', 'chiffres'):
            for bar in range(int(nb)):
                for b in (0, 1, 1.5, 2, 3, 3.5):
                    add(bass, t0 + (bar * 4 + b) * BEAT, bass_note(r, BEAT * (0.9 if b % 1 == 0 else 0.45)), 1.0)
        elif sec in ('serenite', 'cree', 'fin'):
            add(bass, t0, bass_note(r, dur * 0.95), 0.9)
        nbeats = int(round(nb * 4))
        for b in range(nbeats):
            t = t0 + b * BEAT
            if sec == 'valeur' and k0 >= 1:
                if b % 4 == 0 and k0 >= 2:
                    add(drums, t, kick(0.6)); kicks.append(t)
                add(drums, t, shaker(), 0.5, pan=0.3)
                add(drums, t + 0.5 * BEAT, shaker(), 0.9, pan=0.3)
            elif sec == 'idees':
                for s in (0, 0.25, 0.5, 0.75):
                    add(drums, t + s * BEAT, hat(), 0.5 + 0.4 * (s == 0.5), pan=0.25)
                if b % 4 == 0 and k0 < 6:
                    add(drums, t, kick(0.5)); kicks.append(t)
            elif sec in ('demo', 'chiffres'):
                add(drums, t, kick(0.85 if sec == 'chiffres' else 0.75)); kicks.append(t)
                if b % 2 == 1:
                    add(drums, t, clap(1.1 if sec == 'chiffres' else 0.9))
                add(drums, t + 0.5 * BEAT, hat(), 1.0, pan=0.2)
                if sec == 'chiffres':
                    add(drums, t + 0.25 * BEAT, hat(), 0.4, pan=-0.2)
                    add(drums, t + 0.75 * BEAT, hat(), 0.4, pan=-0.2)
            elif sec == 'serenite':
                if b % 2 == 0:
                    add(drums, t, kick(0.65)); kicks.append(t)
                if b % 4 == 3:
                    add(drums, t, clap(0.7))
                add(drums, t + 0.5 * BEAT, shaker(), 1.0, pan=0.3)
            elif sec == 'fin' and k0 < 21 and b == 0:
                add(drums, t, kick(0.6)); kicks.append(t)
                add(drums, t + 2 * BEAT, shaker(1.2), 1.0)

    # Roulement avant les chiffres
    for i in range(8):
        add(drums, bt(13, 2 + i * 0.25), clap(0.3 + i * 0.09), 1.0)
    # Impacts sur les transitions
    for t, g in ((OFF, 0.8), (bt(4), 0.6), (bt(7), 1.0), (bt(14), 1.0), (bt(17), 0.7), (bt(19.5), 0.9), (bt(20.5), 0.7)):
        add(fx, t, impact(g))
        add(drums, t, hat(True, 2.2), 1.0)
    # Petits impacts sur les pop up de chiffres
    for t3 in (36.0, 37.4, 38.8):
        add(fx, retime.to_new(t3), impact(0.35))
    add(fx, bt(7) - 2.4, riser(2.4, 1.0))
    add(fx, bt(14) - 2.0, riser(2.0, 0.9))
    add(fx, bt(19.5) - 1.4, riser(1.4, 0.7))
    add(fx, bt(20.5) - 1.2, riser(1.2, 0.5))
    for j, m in enumerate((72, 76, 79, 84)):
        add(keys, bt(21.5) + j * 0.05, bell(m, 3.6), 0.5, pan=-0.3 + 0.2 * j)
    for k in (19.5, 20.5):
        ch = [c for c in CHORDS if c[0] == k][0][2]
        for j, m in enumerate(sorted(ch)):
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
    fade[t > END - 1.2] = np.clip(1 - (t[t > END - 1.2] - (END - 1.2)) / 1.2, 0, 1) ** 1.3
    fade[t < 0.03] = t[t < 0.03] / 0.03
    mix *= fade[:, None]
    mix = mix / np.max(np.abs(mix)) * 0.89
    mix = np.tanh(mix * 1.15) / np.tanh(1.15)
    sf.write(os.path.join(os.path.dirname(os.path.abspath(__file__)), 'stems', 'music4.wav'), mix.astype(np.float32), SR)
    print('music4 ok')


if __name__ == '__main__':
    main()

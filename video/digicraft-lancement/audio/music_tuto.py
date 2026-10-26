"""Musique du tutoriel produit (60 s, 100 BPM) : même univers que la vidéo de lancement,
une section par étape, transitions calées sur les mesures (intro 0, étapes 4,8 / 14,4 / 26,4 / 36 / 45,6, fin 52,8)."""
import os
import numpy as np
import soundfile as sf
import music as M
from music import SR, add, hp, reverb, delay, pad_note, pluck, bell, bass_note, kick, clap, hat, shaker, riser, impact

DUR = 61.5
M.N = N = int(DUR * SR)
BEAT = 0.6
BAR = 2.4
OFF = 0.0
END = 60.0


def bt(k, b=0.0):
    return OFF + k * BAR + b * BEAT


def S():
    return np.zeros((N, 2))


Am = [57, 60, 64]; F = [53, 57, 60]; C = [48, 55, 60, 64]; G = [55, 59, 62]
Dm = [50, 57, 62, 65]; Em = [52, 55, 59, 64]; Fmaj7 = [53, 57, 60, 64]

# (mesure de départ, durée en mesures, accord, section)
CHORDS = [
    (0, 1, Fmaj7, 'intro'), (1, 1, G, 'intro'),
    (2, 1, C, 'describe'), (3, 1, Am, 'describe'), (4, 1, F, 'describe'), (5, 1, G, 'describe'),
    (6, 1, Am, 'build'), (7, 1, F, 'build'), (8, 1, C, 'build'), (9, 1, G, 'build'), (10, 1, Am, 'build'),
    (11, 1, F, 'refine'), (12, 1, C, 'refine'), (13, 1, Dm, 'refine'), (14, 1, G, 'refine'),
    (15, 1, C, 'publish'), (16, 1, G, 'publish'), (17, 1, Am, 'publish'), (18, 1, F, 'publish'),
    (19, 1, C, 'mobile'), (20, 1, Em, 'mobile'), (21, 1, F, 'mobile'),
    (22, 1, Fmaj7, 'outro'), (23, 2, C, 'outro'),
]
ENERGY = {'intro': 0.4, 'describe': 0.45, 'build': 0.62, 'refine': 0.5, 'publish': 0.66, 'mobile': 0.5, 'outro': 0.35}


def main():
    pad, arp, bass, drums, fx, keys = S(), S(), S(), S(), S(), S()
    for i, (t, m) in enumerate([(0.15, 76), (0.5, 79), (0.85, 84), (1.3, 83), (1.8, 79), (2.3, 88)]):
        add(keys, t, bell(m, 2.0), 0.5, pan=-0.3 + 0.12 * i)

    kicks = []
    for k0, nb, ch, sec in CHORDS:
        t0 = bt(k0)
        dur = nb * BAR
        bright = 1400 if sec in ('intro', 'describe', 'outro') else 2100 if sec in ('build', 'publish') else 1600
        for m in ch:
            add(pad, t0, pad_note(m, dur, bright), 1.0)
        notes = sorted(ch)
        seq = [notes[0] + 12, notes[1] + 12, notes[2] + 12, notes[-1] + 12 if len(notes) > 3 else notes[1] + 24]
        step = 0.25 if sec in ('build', 'publish') else 0.5
        if sec != 'outro' or k0 < 23:
            for i in range(int(4 * nb / step)):
                m = seq[i % 4] + (12 if (i // 4) % 2 and step == 0.25 else 0)
                add(arp, t0 + i * step * BEAT, pluck(m, bright=0.8 if sec in ('intro', 'describe', 'outro') else 1.1), ENERGY[sec] * (0.75 + 0.25 * (i % 4 == 0)), pan=0.35 * np.sin(i * 1.3))
        r = min(ch) - 12
        if r < 36:
            r += 12
        if sec in ('build', 'publish'):
            for bar in range(int(nb)):
                for b in (0, 1, 1.5, 2, 3, 3.5):
                    add(bass, t0 + (bar * 4 + b) * BEAT, bass_note(r, BEAT * (0.9 if b % 1 == 0 else 0.45)), 1.0)
        elif sec in ('describe', 'refine', 'mobile', 'outro'):
            add(bass, t0, bass_note(r, dur * 0.95), 0.9)
        for b in range(int(round(nb * 4))):
            t = t0 + b * BEAT
            if sec == 'intro':
                add(drums, t + 0.5 * BEAT, shaker(), 0.7, pan=0.3)
            elif sec == 'describe':
                for s_ in (0, 0.5):
                    add(drums, t + s_ * BEAT, hat(), 0.5 + 0.3 * (s_ == 0.5), pan=0.25)
                if b % 4 == 0:
                    add(drums, t, kick(0.5)); kicks.append(t)
            elif sec in ('build', 'publish'):
                add(drums, t, kick(0.8 if sec == 'publish' else 0.75)); kicks.append(t)
                if b % 2 == 1:
                    add(drums, t, clap(1.0 if sec == 'publish' else 0.9))
                add(drums, t + 0.5 * BEAT, hat(), 1.0, pan=0.2)
                if sec == 'publish':
                    add(drums, t + 0.25 * BEAT, hat(), 0.4, pan=-0.2)
                    add(drums, t + 0.75 * BEAT, hat(), 0.4, pan=-0.2)
            elif sec in ('refine', 'mobile'):
                if b % 2 == 0:
                    add(drums, t, kick(0.65)); kicks.append(t)
                if b % 4 == 3:
                    add(drums, t, clap(0.7))
                add(drums, t + 0.5 * BEAT, shaker(), 1.0, pan=0.3)
            elif sec == 'outro' and k0 < 23 and b == 0:
                add(drums, t, kick(0.6)); kicks.append(t)
                add(drums, t + 2 * BEAT, shaker(1.2), 1.0)

    # Roulements et impacts sur les changements d'étape
    for i in range(6):
        add(drums, bt(5, 2.5 + i * 0.25), clap(0.25 + i * 0.08), 1.0)
        add(drums, bt(14, 2.5 + i * 0.25), clap(0.25 + i * 0.08), 1.0)
    for t, g in ((bt(2), 0.7), (bt(6), 0.9), (bt(11), 0.6), (bt(15), 1.0), (bt(19), 0.7), (bt(22), 0.8)):
        add(fx, t, impact(g))
        add(drums, t, hat(True, 2.2), 1.0)
    add(fx, bt(2) - 1.6, riser(1.6, 0.7))
    add(fx, bt(6) - 2.0, riser(2.0, 0.9))
    add(fx, bt(15) - 2.0, riser(2.0, 0.9))
    add(fx, bt(22) - 1.4, riser(1.4, 0.6))
    for j, m in enumerate((72, 76, 79, 84)):
        add(keys, bt(23) + j * 0.05, bell(m, 3.6), 0.5, pan=-0.3 + 0.2 * j)
    for k in (19, 22):
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
    fade[t > END - 1.5] = np.clip(1 - (t[t > END - 1.5] - (END - 1.5)) / 1.5, 0, 1) ** 1.3
    fade[t < 0.03] = t[t < 0.03] / 0.03
    mix *= fade[:, None]
    mix = mix / np.max(np.abs(mix)) * 0.89
    mix = np.tanh(mix * 1.15) / np.tanh(1.15)
    sf.write(os.path.join(os.path.dirname(os.path.abspath(__file__)), 'stems', 'music_tuto.wav'), mix.astype(np.float32), SR)
    print('music_tuto ok')


if __name__ == '__main__':
    main()

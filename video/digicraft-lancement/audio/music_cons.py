"""Musique de la vidéo consultants (environ 3 min) : même matière que music_b44.py (96 BPM, mesures de 2,5 s), arrangée
sur les scènes de la vidéo (cons_timeline.js) : intro calme, démonstration plus rythmée, cas d'usage, atouts, méthode, fin.
Les impacts et roulements du film sont retirés : la musique reste un fond sous la voix."""
import json, math, os, re
HERE = os.path.dirname(os.path.abspath(__file__))
SUF = os.environ.get('VOICE_SUF', '')  # '_alex' : version avec la voix clonée d'Alexandre
CT = json.loads(re.search(r'window\.CT = (\{.*\});', open(os.path.join(HERE, '..', 'src_cons', f'cons_timeline{SUF}.js'), encoding='utf-8').read()).group(1))
END = CT['END']
src = open(os.path.join(HERE, 'music_b44.py'), encoding='utf-8').read()
# Durée, sortie, et suppression des impacts calés sur l'image du film.
src = src.replace('DUR = 55.5', f'DUR = {END + 1.5}').replace('END = 54.0', f'END = {END}').replace("'stems', 'music_b44.wav'", "'stems', 'music_cons" + SUF + ".wav'").replace("print('music_b44 ok')", "print('music_cons ok')")
a, b = src.index('    # Roulements et impacts'), src.index('    sc = np.ones(N)')
src = src[:a] + src[b:]
# Grille d'accords : progression du film répétée, section de chaque mesure selon la scène en cours.
SEC = {'constat': 'intro', 'logo': 'describe', 'produit': 'describe', 'etape1': 'build', 'etape2': 'build', 'etape2b': 'build', 'etape3': 'publish', 'cas_intro': 'publish',
       'cas_rh': 'mobile', 'cas_log': 'mobile', 'cas_ind': 'mobile', 'cas_autres': 'publish', 'preuves': 'publish', 'securite': 'refine', 'vendre': 'describe', 'vendre1': 'refine',
       'vendre2': 'refine', 'vendre3': 'refine', 'fin': 'outro'}
PROG = ['Fmaj7', 'C', 'Am', 'G', 'F', 'C', 'Am', 'G', 'Am', 'F', 'C', 'G', 'F', 'G', 'Am', 'C', 'Dm', 'G']
nb = math.ceil((END - 0.7) / 2.5)
def sec_at(t):
    for k, (s0, s1) in CT['scenes'].items():
        if s0 <= t < s1: return SEC[k]
    return 'outro'
chords = []
for i in range(nb):
    t = 0.7 + i * 2.5 + 1.25
    s = sec_at(t)
    chords.append(f"({i}, 1, {PROG[i % len(PROG)] if s != 'outro' else ['Fmaj7', 'C'][min(1, i % 2)]}, '{s}')")
a, b = src.index('CHORDS = ['), src.index(']\nENERGY')
src = src[:a] + 'CHORDS = [' + ', '.join(chords) + src[b:]
src = src.replace("ENERGY = {'intro': 0.32, 'describe': 0.45, 'build': 0.62, 'refine': 0.5, 'publish': 0.66, 'mobile': 0.5, 'outro': 0.35}",
                  "ENERGY = {'intro': 0.3, 'describe': 0.4, 'build': 0.5, 'refine': 0.42, 'publish': 0.52, 'mobile': 0.45, 'outro': 0.32}")
# Cloches finales (le bloc d'impacts du film, qui les contenait, est retiré).
# Les conditions « k0 < 20 » du film visaient sa dernière mesure : ici, la dernière mesure de la vidéo.
src = src.replace('k0 < 20', f'k0 < {nb - 1}')
fe = src.index('    sc = np.ones(N)')
src = src[:fe] + f"    for j, m in enumerate((72, 76, 79, 84)):\n        add(keys, bt({nb - 1}) + j * 0.05, bell(m, 3.6), 0.5, pan=-0.3 + 0.2 * j)\n" + src[fe:]
exec(compile(src, 'music_cons', 'exec'))

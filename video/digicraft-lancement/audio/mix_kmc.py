"""Mixage de la vidéo KMC : même traitement que mix_cons.py (voix vo_kmc, musique et sound design de la vidéo KMC)."""
import os
HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'mix_cons.py'), encoding='utf-8').read()
src = src.replace("'src_cons', f'cons_timeline{SUF}.js'", "'src_kmc', 'kmc_timeline.js'").replace("os.environ.get('VO', 'vo_cons' + SUF)", "'vo_kmc'")
for n in ('vo_cons_raw', 'vo_cons', 'music_cons', 'sfx_cons', 'mix_cons_raw', 'mix_cons_musique'):
    src = src.replace(f"f'{n}{{SUF}}.wav'", f"'{n.replace('cons', 'kmc')}.wav'")
src = src.replace("f'mix_cons{TAG}{SUF}.wav'", "'mix_kmc.wav'").replace("print('mix_cons ok')", "print('mix_kmc ok')")
exec(compile(src, 'mix_kmc', 'exec'))

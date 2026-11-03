"""Musique de la vidéo KMC : même arrangement que music_cons.py, sections calées sur les scènes de src_kmc."""
import os
HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'music_cons.py'), encoding='utf-8').read()
src = src.replace("'src_cons', f'cons_timeline{SUF}.js'", "'src_kmc', 'kmc_timeline.js'").replace("'music_cons\" + SUF + \".wav'", "'music_kmc.wav'").replace("print('music_cons ok')", "print('music_kmc ok')")
i, j = src.index('SEC = {'), src.index("PROG = [")
src = src[:i] + ("SEC = {'intro': 'intro', 'procede': 'intro', 'logo': 'describe', 'decrire': 'build', 'atelier': 'publish', 'fiche': 'publish', 'mobile': 'mobile',\n"
                 "       'modif': 'build', 'existant': 'refine', 'prez': 'publish', 'site': 'publish', 'securite': 'refine', 'fin': 'outro'}\n") + src[j:]
exec(compile(src, 'music_kmc', 'exec'))

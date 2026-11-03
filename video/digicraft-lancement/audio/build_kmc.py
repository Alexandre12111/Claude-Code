"""Assemblage de la voix off de la vidéo KMC : même méthode que build_cons.py (prises choisies par similarité de timbre,
contrôle Whisper, minutage des scènes, sous-titres), appliquée à script_kmc et à src_kmc. Usage : python3 build_kmc.py"""
import os
HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'build_cons.py'), encoding='utf-8').read()
R = [
    ("L = importlib.import_module('script_cons' + SUF).L", "L = importlib.import_module('script_kmc').L"),
    ("os.path.join(HERE, f'vo_cons{SUF}_takes'), os.path.join(HERE, f'vo_cons{SUF}')", "os.path.join(HERE, 'vo_kmc_takes'), os.path.join(HERE, 'vo_kmc')"),
    ("'decrire'}", "'decrire', 'rapides', 'existantes', 'etuve', 'kmc'}"),
    ("'src_cons', f'cons_timeline{SUF}.js'), 'w'", "'src_kmc', 'kmc_timeline.js'), 'w'"),
    ("f'YouCraft_Consultants_FR_soustitres{SUF}.srt'", "'YouCraft_KMC_FR_soustitres.srt'"),
]
for a, b in R:
    assert a in src, a; src = src.replace(a, b)
i, j = src.index('MIN = {'), src.index('LEAD, GAP, TAIL')
src = src[:i] + ("MIN = {'intro': 8.0, 'procede': 17.0, 'logo': 5.0, 'decrire': 16.0, 'atelier': 14.0, 'fiche': 10.0, 'mobile': 12.0, 'modif': 11.0,\n"
                 "       'existant': 16.0, 'prez': 11.0, 'site': 10.0, 'securite': 13.0, 'fin': 9.0}\n") + src[j:]
src = src.replace('cons_timeline.js', 'kmc_timeline.js')
exec(compile(src, 'build_kmc', 'exec'))

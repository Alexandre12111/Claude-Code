"""Sound design de la vidéo KMC : souffles aux changements de scène, cloches sur le logo et la fin, petits sons sur les écrans."""
import os
HERE = os.path.dirname(os.path.abspath(__file__))
src = open(os.path.join(HERE, 'sfx_cons.py'), encoding='utf-8').read()
src = src.replace("'src_cons', f'cons_timeline{SUF}.js'", "'src_kmc', 'kmc_timeline.js'").replace("f'sfx_cons{SUF}.wav'", "'sfx_kmc.wav'").replace("print('sfx_cons ok'", "print('sfx_kmc ok'")
src = src.replace("('cas_rh', 'cas_log', 'cas_ind', 'securite', 'vendre1', 'vendre2', 'vendre3')", "('atelier', 'fiche', 'mobile', 'modif', 'prez', 'site', 'securite')")
exec(compile(src, 'sfx_kmc', 'exec'))

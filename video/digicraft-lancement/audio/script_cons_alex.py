"""Vidéo consultants, voix clonée d'Alexandre : même texte que script_cons, réglages adaptés au clonage.
- exagération plus basse (0,55 ; 0,65 pour les répliques appuyées) et guidage 0,5, pour garder le timbre de la référence ;
- « pipeline » écrit « païplaïne » pour la voix, afin qu'il soit prononcé à l'anglaise (les sous-titres gardent « pipeline »).
Références : extraits de la voix d'Alexandre (séparée de la musique), dossier VOICE_REF_DIR, non versionnés."""
import copy, os
from script_cons import L as L0
REF_DIR = os.environ.get('VOICE_REF_DIR', os.path.join(os.path.dirname(os.path.abspath(__file__)), 'vo_ref_alex'))
REFS = {'b': os.path.join(REF_DIR, 'ref_b.wav'), 'c': os.path.join(REF_DIR, 'ref_c.wav')}
L = copy.deepcopy(L0)
for d in L:
    d['ex'] = 0.65 if d['ex'] > 0.7 else 0.55
    d['cfg'] = 0.5
    if 'pipeline' in d['text']:
        d['text'] = d['text'].replace('pipeline', 'païplaïne')

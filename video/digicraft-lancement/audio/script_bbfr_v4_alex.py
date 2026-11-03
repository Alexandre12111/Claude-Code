"""Film « Donnez vie à vos idées » (69 s), voix clonée d'Alexandre : même texte, même calage que script_bbfr_v4.
Expressivité abaissée d'environ 0,15 et guidage 0,5 pour garder le timbre de la référence (voir script_cons_alex)."""
import copy
from script_bbfr_v4 import L as L0
from script_cons_alex import REFS, REF_DIR
L = copy.deepcopy(L0)
for d in L:
    d['ex'] = round(max(0.5, d['ex'] - 0.15), 2)
    d['cfg'] = 0.5

"""Assemble les tranches de capture.js en une page complète.

Chaque raccord est placé sur la ligne où deux tranches consécutives sont identiques,
pour qu'aucune jointure ne soit visible.

    python3 scripts/stitch.py <nom>
"""
import json
import sys
from pathlib import Path

import numpy as np
from PIL import Image

CAPS = Path(__file__).resolve().parent.parent / 'work' / 'caps'
name = sys.argv[1]
meta = json.loads((CAPS / name / 'meta.json').read_text())
s = meta['dsf']
Hs = meta['H'] * s
caps = meta['caps']
imgs = [np.asarray(Image.open(CAPS / name / c['file']).convert('RGB')) for c in caps]
total = max(c['y'] for c in caps) * s + Hs
canvas = np.zeros((int(total), imgs[0].shape[1], 3), np.uint8)
prev = None
for k, (c, im) in enumerate(zip(caps, imgs)):
    y0 = c['y'] * s
    if prev is None or prev[0] + Hs <= y0:
        canvas[y0:y0 + Hs] = im
    else:
        py0, pim = prev
        ov0, ov1 = y0, py0 + Hs
        a = pim[ov0 - py0: ov1 - py0].astype(np.int16)
        b = im[0: ov1 - y0].astype(np.int16)
        diff = np.abs(a - b).mean(axis=(1, 2))
        m = max(1, int(len(diff) * 0.08))          # on évite les bords de l'écran
        cand = np.arange(m, len(diff) - m)
        # On cherche une bande de 24 lignes identiques dans les deux tranches : le raccord y est invisible.
        win = np.convolve(diff, np.ones(24) / 24, mode='same')
        cut = int(cand[np.argmin(win[cand])])
        seam = ov0 + cut
        canvas[seam: y0 + Hs] = im[seam - y0:]
        flag = '  ATTENTION : contenu différent entre les tranches' if win[cut] > 1 else ''
        print(f'tranche {k}: y={c["y"]} raccord à {seam // s}px, écart {win[cut]:.2f}{flag}')
    prev = (y0, im)
Image.fromarray(canvas).save(CAPS / f'{name}-full.png')
print('page assemblée', canvas.shape)

"""Voix off française de « Donnez vie à vos idées » (YouCraft, version allongée 79 s) : voix Kokoro ff_siwis (n° 30),
la voix française native déjà utilisée pour la vidéo de lancement FR. Usage : TTS_DIR=... python3 tts_bbfr.py"""
import json, os, sys
import numpy as np, soundfile as sf, sherpa_onnx as so
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import warp_bbfr as W

M = os.path.join(os.environ['TTS_DIR'], 'kokoro-multi-lang-v1_0')
STARTS0 = [0.30, 3.30, 4.75, 6.35, 10.55, 12.6, 19.9, 23.6, 30.0, 34.7, 37.9, 41.0, 46.3, 48.4]
LINES = [
    "Imaginez pouvoir créer n'importe quelle application,",
    "pour n'importe quelle équipe,",
    "pour n'importe quel processus,",
    "à n'importe quelle échelle. Simplement en la décrivant.",
    "Voici YouCraft.",
    "Une plateforme complète de création par l'IA, pensée pour les entreprises qui ont une idée, et l'envie de la concrétiser.",
    "Décrivez votre besoin, et regardez-le prendre forme.",
    "Vous pouvez planifier, personnaliser, et faire évoluer votre application, dans toutes les directions.",
    "Des outils conçus pour une équipe, jusqu'aux plateformes prêtes pour toute l'entreprise.",
    "Des gains rapides, aux applications de niveau entreprise,",
    "tout est intégré, et prêt à être lancé.",
    "C'est une nouvelle façon de travailler, où vos équipes vont aussi loin que leurs idées.",
    "Vous l'imaginez ?",
    "Donnons-lui vie.",
]
SPEED = float(os.environ.get('SPEED', 1.0))
cfg = so.OfflineTtsConfig(model=so.OfflineTtsModelConfig(kokoro=so.OfflineTtsKokoroModelConfig(model=f'{M}/model.onnx', voices=f'{M}/voices.bin', tokens=f'{M}/tokens.txt',
      data_dir=f'{M}/espeak-ng-data', lexicon=f'{M}/lexicon-us-en.txt', lang='fr'), num_threads=4))
tts = so.OfflineTts(cfg)
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'vo_bbfr')
os.makedirs(OUT, exist_ok=True)
starts = [round(W.fwd(t), 3) for t in STARTS0]
meta = []
for i, t in enumerate(LINES):
    a = tts.generate(t.replace('YouCraft', 'You Craft'), sid=30, speed=SPEED)
    x = np.asarray(a.samples)
    sf.write(f'{OUT}/l{i:02d}.wav', x, a.sample_rate)
    d = len(x) / a.sample_rate
    nxt = starts[i + 1] if i + 1 < len(starts) else W.END - 1.0
    meta.append({'text': t, 'start': starts[i], 'dur': round(d, 2)})
    print(i, starts[i], round(d, 2), 'marge', round(nxt - starts[i] - d, 2), t)
json.dump(meta, open(f'{OUT}/meta.json', 'w'), ensure_ascii=False, indent=1)

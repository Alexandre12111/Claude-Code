"""Voix témoin (provisoire) de l'épisode 1 : Chatterbox multilingue, timbre de référence masculin synthétique.

Sert uniquement à caler le montage et les sous-titres. La version publiée utilise la voix enregistrée
d'Alexandre (voix/alexandre.wav, découpée par calage.py).
Usage : <python avec chatterbox-tts> voix_temoin.py <reference.wav> [ids...]
"""
import json, os, sys, wave
from pathlib import Path
import torch

ICI = Path(__file__).resolve().parent
torch.manual_seed(int(os.environ.get("GRAINE", 1911)))
_charge = torch.load
torch.load = lambda *a, **k: _charge(*a, **{**k, "map_location": "cpu"})
from chatterbox.mtl_tts import ChatterboxMultilingualTTS

script = json.loads((ICI / "script.json").read_text())
modele = ChatterboxMultilingualTTS.from_pretrained(device=torch.device("cpu"))
modele.prepare_conditionals(sys.argv[1], exaggeration=.55)
sortie = ICI / "voix" / "temoin"; sortie.mkdir(parents=True, exist_ok=True)
seulement = set(sys.argv[2:])
for ph in script["phrases"]:
    if seulement and ph["id"] not in seulement:
        continue
    wav = modele.generate(ph["texte"], language_id="fr", exaggeration=.55, cfg_weight=.45, temperature=.7)
    with wave.open(str(sortie / f"{ph['id']}.wav"), "wb") as w:
        w.setnchannels(1); w.setsampwidth(2); w.setframerate(modele.sr)
        w.writeframes((wav.squeeze().clamp(-1, 1).numpy() * 32767).astype("int16").tobytes())
    print(ph["id"], round(wav.shape[-1] / modele.sr, 2), "s", ph["texte"], flush=True)

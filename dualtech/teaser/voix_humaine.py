"""Voix off du teaser avec un rendu plus naturel : Chatterbox multilingue (Resemble AI, licence MIT).

Le timbre est pris sur un extrait de la locutrice du corpus SIWIS (enregistrements humains, licence CC BY 4.0),
pour une diction française naturelle. Même texte et mêmes fichiers de sortie que voix.py (voix/v1.wav … et
voix/voix.json) : le montage et les sous titres se recalent tout seuls.
Usage : <python avec chatterbox-tts> voix_humaine.py <extrait-de-reference.wav>
"""
import json, sys
from pathlib import Path
import torch, torchaudio
from voix import PHRASES

ICI = Path(__file__).resolve().parent
torch.manual_seed(2611)

from chatterbox.mtl_tts import ChatterboxMultilingualTTS
modele = ChatterboxMultilingualTTS.from_pretrained(device=torch.device("cpu"))
modele.prepare_conditionals(sys.argv[1], exaggeration=.45)

sortie = ICI / "voix"; sortie.mkdir(exist_ok=True)
seulement = set(sys.argv[2:])
infos = {i["id"]: i for i in json.loads((sortie / "voix.json").read_text())} if (sortie / "voix.json").exists() else {}
for ident, texte, st in PHRASES:
    if seulement and ident not in seulement:
        continue
    wav = modele.generate(texte, language_id="fr", exaggeration=.45, cfg_weight=.5, temperature=.7)
    torchaudio.save(str(sortie / f"{ident}.wav"), wav, modele.sr)
    d = wav.shape[-1] / modele.sr
    infos[ident] = {"id": ident, "texte": texte, "sous_titre": st, "duree": round(d, 3)}
    print(f"{ident} {d:5.2f} s  {texte}", flush=True)
(sortie / "voix.json").write_text(json.dumps([infos[i] for i, *_ in PHRASES if i in infos], ensure_ascii=False, indent=1))

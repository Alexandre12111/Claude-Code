"""Calage du montage sur la voix off : écrit calage.json.

Les phrases utilisées sont celles de voix/final (débit accéléré de 8 %).
Le montage est conçu sur une ligne de temps de référence (DEBUTS, 48 s). Une voix plus posée que prévu allonge
certaines phrases : chaque phrase démarre alors quand la précédente est terminée (plus une respiration), et la ligne
de temps est dilatée par morceaux entre ces points d'ancrage. teaser.html et son.py lisent ce fichier.
Usage : python3 calage.py
"""
import json, wave
from pathlib import Path
import numpy as np

ICI = Path(__file__).resolve().parent
DEBUTS = {"v1": 1.0, "v2": 4.6, "v3": 10.0, "v4": 14.4, "v5": 19.6, "v6": 24.7, "v7": 30.4, "v8": 35.9, "v9": 38.6, "v10": 43.7}
FIN_REFERENCE = 48.0
RESPIRATION = .25
ACCELERATION = 1.08   # débit accéléré de 8 % sans changer la hauteur (voix/final, ffmpeg atempo)

lignes = []
for ident in DEBUTS:
    with wave.open(str(ICI / "voix" / "final" / f"{ident}.wav")) as w:
        sr = w.getframerate(); a = np.frombuffer(w.readframes(w.getnframes()), np.int16) / 32768
    e = np.array([np.abs(a[i:i + sr // 100]).mean() for i in range(0, len(a), sr // 100)])
    actif = np.where(e > .01)[0]
    lignes.append({"id": ident, "attaque": actif[0] / 100, "parole": (actif[-1] - actif[0]) / 100 + .05})

reel = {}
ids = list(DEBUTS)
for n, l in enumerate(lignes):
    k = l["id"]
    if n == 0:
        reel[k] = DEBUTS[k]
    else:
        prec = lignes[n - 1]
        reel[k] = round(max(reel[prec["id"]] + DEBUTS[k] - DEBUTS[prec["id"]], reel[prec["id"]] + prec["parole"] + RESPIRATION), 3)
fin = round(reel[ids[-1]] + FIN_REFERENCE - DEBUTS[ids[-1]], 3)
calage = {
    "ancrages_reference": [0.0] + [DEBUTS[k] for k in ids] + [FIN_REFERENCE],
    "ancrages_reels": [0.0] + [reel[k] for k in ids] + [fin],
    "duree": fin,
    "phrases": [{**l, "debut": reel[l["id"]]} for l in lignes],
}
# repères visuels calés sur des mots précis (temps réels relevés par horodatage Whisper sur voix/final)
MOTS_REELS = {"panneaux": [6.67, 7.75, 8.83, 9.65], "double_usage": 18.73, "noeuds": [24.2, 25.46, 26.56]}
def vers_reference(tr):
    return float(np.interp(tr, calage["ancrages_reels"], calage["ancrages_reference"]))
calage["reperes"] = {"panneaux": [round(vers_reference(x), 3) for x in MOTS_REELS["panneaux"]],
                     "double_usage": round(vers_reference(MOTS_REELS["double_usage"]), 3),
                     "noeuds": [round(vers_reference(x), 3) for x in MOTS_REELS["noeuds"]]}
(ICI / "calage.json").write_text(json.dumps(calage, indent=1))
for l in calage["phrases"]:
    print(f'{l["id"]:>4} début {l["debut"]:6.2f} s  parole {l["parole"]:4.2f} s  (référence {DEBUTS[l["id"]]:5.2f} s)')
print("durée totale", fin, "s")

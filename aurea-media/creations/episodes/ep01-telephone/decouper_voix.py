"""Découpe l'enregistrement d'Alexandre en phrases (voix/alexandre/v1.wav … v11.wav).

L'enregistrement peut contenir plusieurs prises et des phrases recommencées : chaque segment entre deux silences
est transcrit par Whisper, puis associé à la phrase du script la plus proche. Pour chaque phrase, la prise
la plus fidèle au texte est gardée (la plus récente en cas d'égalité). Aucun traitement de la voix : seulement
un léger fondu aux bords et une normalisation du volume.
Usage : <python avec faster-whisper> decouper_voix.py <enregistrement.m4a|mp3|wav>  puis  calage.py
"""
import difflib, json, re, subprocess, sys, unicodedata, wave
from pathlib import Path
import numpy as np
from faster_whisper import WhisperModel

ICI = Path(__file__).resolve().parent
SR = 48000
script = json.loads((ICI / "script.json").read_text())


def normal(s):
    s = unicodedata.normalize("NFD", s.lower())
    return " ".join(re.sub(r"[^a-z0-9 ]", " ", "".join(c for c in s if unicodedata.category(c) != "Mn")).split())


brut = subprocess.run(["ffmpeg", "-loglevel", "error", "-i", sys.argv[1], "-ac", "1", "-ar", str(SR), "-f", "s16le", "-"], capture_output=True).stdout
a = np.frombuffer(brut, np.int16).astype(np.float32) / 32768
modele = WhisperModel("small", device="cpu", compute_type="int8")
a16 = a[::3].copy()
segs, _ = modele.transcribe(a16, language="fr", vad_filter=True, vad_parameters={"min_silence_duration_ms": 600}, initial_prompt="Échafaudage, enduit, chantier, devis.")
segments = [(s.start, s.end, s.text) for s in segs]
print(len(segments), "segments entendus")

meilleurs = {}
for debut, fin, texte in segments:
    for ph in script["phrases"]:
        score = difflib.SequenceMatcher(None, normal(texte), normal(ph["texte"])).ratio()
        if score > .72 and score >= meilleurs.get(ph["id"], (0,))[0] - .02:
            meilleurs[ph["id"]] = (score, debut, fin, texte)

sortie = ICI / "voix" / "alexandre"; sortie.mkdir(parents=True, exist_ok=True)
for ph in script["phrases"]:
    if ph["id"] not in meilleurs:
        print("MANQUANTE :", ph["id"], ph["texte"]); continue
    score, d, f, texte = meilleurs[ph["id"]]
    x = a[max(0, int((d - .12) * SR)): int((f + .2) * SR)].copy()
    n = int(.015 * SR); x[:n] *= np.linspace(0, 1, n); x[-n:] *= np.linspace(1, 0, n)
    x = x / (np.abs(x).max() + 1e-9) * .89
    with wave.open(str(sortie / f"{ph['id']}.wav"), "wb") as w:
        w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes((x * 32767).astype(np.int16).tobytes())
    print(f"{ph['id']:>4} {score:.2f}  « {texte.strip()} »")

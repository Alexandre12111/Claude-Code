"""Calage de l'épisode sur la voix : écrit calage.json (début et fin de chaque phrase, horodatage de chaque mot)
et voix-mix.wav (la narration placée sur la ligne de temps, 48 kHz).

Source de la voix : voix/alexandre/<id>.wav si présent (enregistrement d'Alexandre, une phrase par fichier),
sinon voix/temoin/<id>.wav (voix témoin provisoire).
Usage : <python avec faster-whisper> calage.py
"""
import json, re, subprocess, unicodedata, wave
from pathlib import Path
import numpy as np
from faster_whisper import WhisperModel

ICI = Path(__file__).resolve().parent
SR = 48000
script = json.loads((ICI / "script.json").read_text())
SOURCE = "alexandre" if (ICI / "voix" / "alexandre").exists() else "temoin"
# respiration avant chaque phrase (secondes) : plus longue avant les changements de ton
PAUSES = {"v1": .15, "v2": .12, "v3": .12, "v4": .1, "v5": .22, "v6": .2, "v7": .25, "v8": .3, "v9": .4, "v10": .35, "v11": .3, "v12": .3}
ACCEL = 1.07 if SOURCE == "temoin" else 1.0   # débit légèrement plus nerveux (sans changer la hauteur)


def lire(f):
    brut = subprocess.run(["ffmpeg", "-loglevel", "error", "-i", str(f), "-af", f"atempo={ACCEL}", "-ac", "1", "-ar", str(SR), "-f", "s16le", "-"], capture_output=True).stdout
    return np.frombuffer(brut, np.int16).astype(np.float32) / 32768


def rogner(a):
    e = np.array([np.abs(a[i:i + SR // 100]).mean() for i in range(0, len(a), SR // 100)])
    seuil = max(.004, e.max() * .04)
    actif = np.where(e > seuil)[0]
    d, f = max(0, actif[0] - 4), min(len(e), actif[-1] + 8)
    a = a[d * SR // 100: f * SR // 100].copy()
    n = int(.012 * SR); a[:n] *= np.linspace(0, 1, n); a[-n:] *= np.linspace(1, 0, n)
    return a


def normal(s):
    s = unicodedata.normalize("NFD", s.lower())
    return re.sub(r"[^a-z0-9 ]", " ", "".join(c for c in s if unicodedata.category(c) != "Mn")).split()


modele = WhisperModel("small", device="cpu", compute_type="int8")
t = 0.0
phrases, pistes = {}, []
for ph in script["phrases"]:
    a = rogner(lire(ICI / "voix" / SOURCE / f"{ph['id']}.wav"))
    t += PAUSES.get(ph["id"], .25)
    debut = round(t, 3)
    tmp = ICI / "voix" / f".{ph['id']}.wav"
    with wave.open(str(tmp), "wb") as w:
        w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes((a * 32767).astype(np.int16).tobytes())
    a16 = np.frombuffer(subprocess.run(["ffmpeg", "-loglevel", "error", "-i", str(tmp), "-ac", "1", "-ar", "16000", "-f", "s16le", "-"], capture_output=True).stdout, np.int16).astype(np.float32) / 32768
    segs, _ = modele.transcribe(a16, language="fr", word_timestamps=True, initial_prompt="Échafaudage, enduit, chantier, devis, quatorze appels, neuf heures.")
    mots_w = []
    for s in segs:
        for m in s.words:
            w = m.word.strip()
            if mots_w and (w.startswith("'") or w.startswith("-") or w in ".,?!"):
                mots_w[-1] = type("M", (), {"word": mots_w[-1].word + w, "start": mots_w[-1].start, "end": m.end})
            else:
                mots_w.append(type("M", (), {"word": w, "start": m.start, "end": m.end}))
    tmp.unlink()
    st0 = ph["sous_titre"].replace("*", "").split()
    if len(mots_w) > len(st0):   # bruit ou syllabe parasite après la phrase : on coupe après le dernier mot attendu
        coupe = mots_w[len(st0) - 1].end + .14
        a = a[:int(coupe * SR)]; n = int(.03 * SR); a[-n:] *= np.linspace(1, 0, n)
        mots_w = mots_w[:len(st0)]
    entendu = " ".join(m.word.strip() for m in mots_w)
    # association des mots du sous-titre aux mots entendus (même nombre en général ; sinon répartition proportionnelle)
    st = ph["sous_titre"].replace("*", "").split()
    if len(mots_w) == len(st):
        mots = [{"debut": round(debut + m.start, 3), "fin": round(debut + m.end, 3)} for m in mots_w]
    else:
        dur = len(a) / SR
        poids = [len(x) + 2 for x in st]; tot = sum(poids); c = debut; mots = []
        for p in poids:
            d = dur * p / tot; mots.append({"debut": round(c, 3), "fin": round(c + d, 3)}); c += d
    attendu, ok = normal(ph["texte"]), normal(entendu)
    manque = [m for m in attendu if m not in ok]
    phrases[ph["id"]] = {"debut": debut, "fin": round(debut + len(a) / SR, 3), "mots": mots, "entendu": entendu}
    pistes.append((debut, a))
    print(f"{ph['id']:>4} {debut:6.2f} → {debut + len(a) / SR:6.2f}  {len(mots_w):2d}/{len(st):2d} mots  {'OK' if len(manque) <= 1 else 'À VÉRIFIER ' + str(manque)}  « {entendu} »")
    t = debut + len(a) / SR

duree = round(t + .6 + script["fin"]["duree"], 3)
mix = np.zeros(int(duree * SR) + SR, np.float32)
for d, a in pistes:
    i = int(d * SR); mix[i:i + len(a)] += a
with wave.open(str(ICI / "voix-mix.wav"), "wb") as w:
    w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR)
    w.writeframes((np.clip(mix[:int(duree * SR)], -1, 1) * 32767).astype(np.int16).tobytes())
(ICI / "calage.json").write_text(json.dumps({"source": SOURCE, "duree": duree, "phrases": phrases}, ensure_ascii=False, indent=1))
print("source", SOURCE, "· durée", duree, "s")

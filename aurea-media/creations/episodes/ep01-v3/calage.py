"""Calage de la V4 sur la voix d'Alexandre (kit Voix_Alexandre, sortie de scripts/voix_alexandre.py --mix).

Lit <sortie>/lNN.json et lNN.wav, reconstitue la ligne de temps exacte de voix_off.wav (pauses de 0,45 s, 1 s après
les lignes vides), écrit calage.json (début, fin et mots horodatés de chaque réplique), copie voix_off.wav en voix-mix.wav
et produit voix_off.srt calé sur les mots. Aucun time-stretch : le montage s'adapte à la voix.
Usage : python3 calage.py <dossier_du_kit> <dossier_voix_telephone>
"""
import json, re, shutil, subprocess, sys, wave
from pathlib import Path

ICI = Path(__file__).resolve().parent
KIT = Path(sys.argv[1])          # dossier du kit Voix_Alexandre (contient sortie_ep01/, sortie_v4/) ; la voix téléphone est dans sys.argv[2]
TEL = Path(sys.argv[2])
script = json.loads((ICI / "script.json").read_text())
# respirations entre les répliques (le montage s'adapte à la voix ; aucune accélération de la voix)
PAUSE = .28
PAUSES = {"l00": .32, "l02": .3, "q1": .22, "q2": .22, "q3": .22, "q4": .35, "l06": .75, "l07": .1, "l07b": .5, "l10": .55}
# V4 : chaîne douce anti grésillements (aucun rehaussement au-dessus de 8 kHz, zone où le vocodeur crépite)
CHAINE = ("adeclick=w=55:o=75:t=2,highpass=f=85:p=2,lowpass=f=10500:p=2,afftdn=nr=6:nf=-52:tn=1,"
          "equalizer=f=150:t=q:w=0.9:g=2,equalizer=f=320:t=q:w=1.2:g=-2.5,equalizer=f=2800:t=q:w=1.0:g=1.5,"
          "equalizer=f=6500:t=q:w=1.6:g=-2.5,deesser=i=0.35:m=0.5:f=0.5,"
          "acompressor=threshold=-22dB:ratio=2.5:attack=8:release=120:makeup=2,"
          "loudnorm=I=-16:TP=-2:LRA=9,alimiter=limit=0.89:attack=5:release=60,aresample=48000:resampler=soxr")
# voix au téléphone (la prospecte) : bande passante téléphonique, sans saturation
CHAINE_TEL = ("highpass=f=320:p=2,lowpass=f=3400:p=2,equalizer=f=1800:t=q:w=1.0:g=3,"
              "acompressor=threshold=-20dB:ratio=3:attack=5:release=80:makeup=2,loudnorm=I=-19:TP=-3:LRA=7,aresample=48000:resampler=soxr")


def duree(f):
    with wave.open(str(f)) as w: return w.getnframes() / w.getframerate()


def jetons(st):
    out = []
    for b in st.replace("*", "").split():
        if re.fullmatch(r"[?!:;.,…]+", b) and out: out[-1] += " " + b
        else: out.append(b)
    return out


t, phrases, srt, morceaux = 0.0, {}, [], []
for ph in script["phrases"]:
    dossier, n = ph["source"]; base = (TEL if dossier == "tel" else KIT / dossier)
    stem = f"q{n}" if dossier == "tel" else f"l{n:02d}"
    j = json.loads((base / f"{stem}.json").read_text())
    best = next(x for x in j["takes"] if x["file"].endswith(Path(j["best"]).name))
    wav = base / f"{stem}.wav"; d = duree(wav)
    mots_w = [w for w in best["words"] if w["w"].strip()]
    fus = []   # élisions coupées par Whisper (« d » + « 'enduit ») : un seul mot
    for w in mots_w:
        if fus and (w["w"].startswith("'") or fus[-1]["w"].endswith("'")): fus[-1] = {"w": fus[-1]["w"] + w["w"], "a": fus[-1]["a"], "b": w["b"]}
        else: fus.append(dict(w))
    mots_w = fus
    toks = jetons(ph["sous_titre"])
    if len(mots_w) == len(toks):
        mots = [{"debut": round(t + w["a"], 3), "fin": round(t + w["b"], 3)} for w in mots_w]
    else:   # répartition proportionnelle entre le premier et le dernier mot entendu
        a0 = mots_w[0]["a"] if mots_w else 0; b1 = mots_w[-1]["b"] if mots_w else d
        poids = [len(x) + 2 for x in toks]; tot = sum(poids); c = t + a0; mots = []
        for p in poids:
            dd = (b1 - a0) * p / tot; mots.append({"debut": round(c, 3), "fin": round(c + dd, 3)}); c += dd
    phrases[ph["id"]] = {"debut": round(t + (mots_w[0]["a"] if mots_w else 0), 3), "fin": round(t + (mots_w[-1]["b"] if mots_w else d), 3), "mots": mots,
                         "entendu": best["heard"], "valide": best["ok"], "sim": round(best.get("sim", 0), 3), "voix": ph.get("voix", "alexandre")}
    if ph.get("st", True):   # sous-titres .srt : groupes de 3 à 5 mots (les questions du téléphone sont dans les bulles)
        groupe = []
        for k, (tok, m) in enumerate(zip(toks, mots)):
            groupe.append((tok, m))
            if len(groupe) >= 4 or k == len(toks) - 1 or re.search(r"[.?!:]$", tok):
                srt.append((groupe[0][1]["debut"], groupe[-1][1]["fin"] + .15, " ".join(x for x, _ in groupe))); groupe = []
    print(f"{ph['id']:5s} {t:6.2f}  {d:5.2f} s  {'OK ' if best['ok'] else 'À ÉCOUTER'} sim {best.get('sim', 0):.3f}  « {best['heard']} »")
    morceaux.append((wav, PAUSES.get(ph["id"], PAUSE), ph.get("voix", "alexandre")))
    t += d + PAUSES.get(ph["id"], PAUSE)

import numpy as np, soundfile as sf
import librosa
SR = 24000
pistes = {"alexandre": [], "tel": []}
for f, g, v in morceaux:
    x, sr = sf.read(str(f))
    if x.ndim > 1: x = x.mean(1)
    if sr != SR: x = librosa.resample(x, orig_sr=sr, target_sr=SR)
    n = int(len(x) + g * SR)
    for k in pistes: pistes[k].append(np.pad(x, (0, n - len(x))) if k == v else np.zeros(n))
brute = KIT / "voix_off_v4_brute.wav"; sf.write(str(brute), np.concatenate(pistes["alexandre"]).astype(np.float32), SR)
brute_tel = TEL / "voix_tel_brute.wav"; sf.write(str(brute_tel), np.concatenate(pistes["tel"]).astype(np.float32), SR)
voix = KIT / "voix_off_v4.wav"
subprocess.run(["ffmpeg", "-y", "-loglevel", "error", "-i", str(brute), "-af", CHAINE, str(voix)], check=True)
subprocess.run(["ffmpeg", "-y", "-loglevel", "error", "-i", str(brute_tel), "-af", CHAINE_TEL, str(ICI / "voix-tel.wav")], check=True)
shutil.copy(voix, ICI / "voix-mix.wav")
duree_voix = duree(voix)
total = round(max(t, duree_voix) + .1 + script["fin"]["duree"], 3)
(ICI / "calage.json").write_text(json.dumps({"source": "alexandre", "duree": total, "phrases": phrases}, ensure_ascii=False, indent=1))
def hms(x):
    h, r = divmod(x, 3600); m, s = divmod(r, 60); return f"{int(h):02d}:{int(m):02d}:{int(s):02d},{int(round((s - int(s)) * 1000)):03d}"
srt = [(a, min(b, srt[n + 1][0] - .02) if n + 1 < len(srt) else b, x) for n, (a, b, x) in enumerate(srt)]
(KIT / "voix_off_v4.srt").write_text("\n".join(f"{n + 1}\n{hms(a)} --> {hms(b)}\n{txt}\n" for n, (a, b, txt) in enumerate(srt)), encoding="utf-8")
print("durée totale", total, "s ; voix_off.wav", round(duree_voix, 2), "s ; srt :", len(srt), "sous-titres")

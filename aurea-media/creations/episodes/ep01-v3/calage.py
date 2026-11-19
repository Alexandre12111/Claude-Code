"""Calage de la V3 sur la voix d'Alexandre (kit Voix_Alexandre, sortie de scripts/voix_alexandre.py --mix).

Lit <sortie>/lNN.json et lNN.wav, reconstitue la ligne de temps exacte de voix_off.wav (pauses de 0,45 s, 1 s après
les lignes vides), écrit calage.json (début, fin et mots horodatés de chaque réplique), copie voix_off.wav en voix-mix.wav
et produit voix_off.srt calé sur les mots. Aucun time-stretch : le montage s'adapte à la voix.
Usage : python3 calage.py <dossier_sortie_du_kit>
"""
import json, re, shutil, subprocess, sys, wave
from pathlib import Path

ICI = Path(__file__).resolve().parent
SORTIE = Path(sys.argv[1])
script = json.loads((ICI / "script.json").read_text())
# respirations entre les répliques (le montage s'adapte à la voix ; aucune accélération de la voix)
PAUSE, PAUSES = .28, {"l00": .32, "l06": .75, "l07": .35, "l10": .55}
CHAINE = ("highpass=f=80,equalizer=f=140:t=q:w=0.9:g=2.5,equalizer=f=320:t=q:w=1.2:g=-2.5,equalizer=f=3500:t=q:w=1.0:g=3,"
          "equalizer=f=10000:t=h:w=0.7:g=2.5,deesser=i=0.4:m=0.5:f=0.5,acompressor=threshold=-24dB:ratio=3.5:attack=4:release=90:makeup=3,"
          "alimiter=limit=0.9,loudnorm=I=-16:TP=-1.5:LRA=9,aresample=48000")   # chaîne du kit Voix_Alexandre


def duree(f):
    with wave.open(str(f)) as w: return w.getnframes() / w.getframerate()


def jetons(st):
    out = []
    for b in st.replace("*", "").split():
        if re.fullmatch(r"[?!:;.,…]+", b) and out: out[-1] += " " + b
        else: out.append(b)
    return out


t, phrases, srt, morceaux = 0.0, {}, [], []
for i, ph in enumerate(script["phrases"]):
    j = json.loads((SORTIE / f"l{i:02d}.json").read_text())
    best = next(x for x in j["takes"] if x["file"].endswith(Path(j["best"]).name))
    d = duree(SORTIE / f"l{i:02d}.wav")
    mots_w = [w for w in best["words"] if w["w"].strip()]
    toks = jetons(ph["sous_titre"])
    if len(mots_w) == len(toks):
        mots = [{"debut": round(t + w["a"], 3), "fin": round(t + w["b"], 3)} for w in mots_w]
    else:   # répartition proportionnelle entre le premier et le dernier mot entendu
        a0 = mots_w[0]["a"] if mots_w else 0; b1 = mots_w[-1]["b"] if mots_w else d
        poids = [len(x) + 2 for x in toks]; tot = sum(poids); c = t + a0; mots = []
        for p in poids:
            dd = (b1 - a0) * p / tot; mots.append({"debut": round(c, 3), "fin": round(c + dd, 3)}); c += dd
    phrases[ph["id"]] = {"debut": round(t + (mots_w[0]["a"] if mots_w else 0), 3), "fin": round(t + (mots_w[-1]["b"] if mots_w else d), 3), "mots": mots,
                         "entendu": best["heard"], "valide": best["ok"], "sim": round(best["sim"], 3)}
    # sous-titres .srt : groupes de 3 à 5 mots
    groupe = []
    for k, (tok, m) in enumerate(zip(toks, mots)):
        groupe.append((tok, m))
        if len(groupe) >= 4 or k == len(toks) - 1 or re.search(r"[.?!:]$", tok):
            srt.append((groupe[0][1]["debut"], groupe[-1][1]["fin"] + .15, " ".join(x for x, _ in groupe))); groupe = []
    print(f"{ph['id']} {t:6.2f}  {d:5.2f} s  {'OK ' if best['ok'] else 'À ÉCOUTER'} sim {best['sim']:.3f}  « {best['heard']} »")
    morceaux.append((SORTIE / f"l{i:02d}.wav", PAUSES.get(ph["id"], PAUSE)))
    t += d + PAUSES.get(ph["id"], PAUSE)

import numpy as np, soundfile as sf
parts = []
for f, g in morceaux:
    x, sr = sf.read(str(f)); parts += [x, np.zeros(int(g * sr))]
brute = SORTIE / "voix_off_montage_brute.wav"; sf.write(str(brute), np.concatenate(parts).astype(np.float32), sr)
voix = SORTIE / "voix_off_montage.wav"
subprocess.run(["ffmpeg", "-y", "-loglevel", "error", "-i", str(brute), "-af", CHAINE, str(voix)], check=True)
shutil.copy(voix, ICI / "voix-mix.wav")
duree_voix = duree(voix)
total = round(max(t, duree_voix) + .1 + script["fin"]["duree"], 3)
(ICI / "calage.json").write_text(json.dumps({"source": "alexandre", "duree": total, "phrases": phrases}, ensure_ascii=False, indent=1))
def hms(x):
    h, r = divmod(x, 3600); m, s = divmod(r, 60); return f"{int(h):02d}:{int(m):02d}:{int(s):02d},{int(round((s - int(s)) * 1000)):03d}"
srt = [(a, min(b, srt[n + 1][0] - .02) if n + 1 < len(srt) else b, x) for n, (a, b, x) in enumerate(srt)]
(SORTIE / "voix_off.srt").write_text("\n".join(f"{n + 1}\n{hms(a)} --> {hms(b)}\n{txt}\n" for n, (a, b, txt) in enumerate(srt)), encoding="utf-8")
print("durée totale", total, "s ; voix_off.wav", round(duree_voix, 2), "s ; srt :", len(srt), "sous-titres")

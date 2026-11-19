"""Régénère certaines phrases de la voix témoin jusqu'à ce que Whisper y entende les mots clés attendus.
Usage : <python> voix_essais.py <reference.wav> id:mot1,mot2 [id:mots...]"""
import json, sys, wave, unicodedata, re, numpy as np, torch, scipy.signal as ss
_c = torch.load; torch.load = lambda *a, **k: _c(*a, **{**k, "map_location": "cpu"})
from chatterbox.mtl_tts import ChatterboxMultilingualTTS
from faster_whisper import WhisperModel
norm = lambda s: re.sub(r"[^a-z ]", " ", "".join(ch for ch in unicodedata.normalize("NFD", s.lower()) if unicodedata.category(ch) != "Mn"))
textes = {p["id"]: p["texte"] for p in json.load(open("script.json"))["phrases"]}
m = ChatterboxMultilingualTTS.from_pretrained(device=torch.device("cpu")); m.prepare_conditionals(sys.argv[1], exaggeration=.55)
w = WhisperModel("small", device="cpu", compute_type="int8")
for arg in sys.argv[2:]:
    ident, mots = arg.split(":"); mots = mots.split(",")
    for g in [5, 13, 21, 34, 55, 89, 144]:
        torch.manual_seed(g)
        a = m.generate(textes[ident], language_id="fr", exaggeration=.5, cfg_weight=.5, temperature=.6).squeeze().clamp(-1, 1).numpy()
        out = " ".join(s.text for s in w.transcribe(ss.resample_poly(a, 16000, m.sr).astype(np.float32), language="fr")[0])
        ok = all(x in norm(out) for x in mots)
        print(ident, g, "OK" if ok else "non", out, flush=True)
        if ok:
            with wave.open(f"voix/temoin/{ident}.wav", "wb") as f:
                f.setnchannels(1); f.setsampwidth(2); f.setframerate(m.sr); f.writeframes((a * 32767).astype("int16").tobytes())
            break

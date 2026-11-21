"""Voix off du teaser DualTech 2026 (synthèse Piper, voix française SIWIS, licence CC BY 4.0).

Chaque phrase est synthétisée séparément : voix.json donne sa durée, ce qui permet de caler le montage
et les sous titres sur la voix. Le texte reprend le site : le salon, son but, son objectif, puis le contenu.
Usage : <python avec piper-tts> voix.py <dossier des modèles>
"""
import json, sys, wave
from pathlib import Path

# (identifiant, texte prononcé, sous titre affiché)
PHRASES = [
    ("v1", "DualTech. Le rendez-vous de l'industrie duale.", "DualTech, le rendez-vous de l'industrie duale."),
    ("v2", "Une journée pour réunir les acteurs publics, industriels, scientifiques et financiers",
           "Une journée pour réunir les acteurs publics, industriels, scientifiques et financiers"),
    ("v3", "qui construisent la souveraineté technologique française et européenne de demain.",
           "qui construisent la souveraineté technologique française et européenne de demain."),
    ("v4", "Notre objectif : promouvoir les technologies à double usage, civil et défense,",
           "Notre objectif : promouvoir les technologies à double usage, civil et défense,"),
    ("v5", "et accélérer les rencontres entre ceux qui innovent, ceux qui financent et ceux qui décident.",
           "et accélérer les rencontres entre ceux qui innovent, ceux qui financent et ceux qui décident."),
    ("v6", "Vingt filières stratégiques. Du spatial à la cybersécurité, de la robotique au quantique.",
           "Vingt filières stratégiques : du spatial à la cybersécurité, de la robotique au quantique."),
    ("v7", "Quatre cents participants. Quarante intervenants. Tables rondes, conférences et ateliers.",
           "400 participants, 40 intervenants. Tables rondes, conférences et ateliers."),
    ("v8", "Et la remise du Prix de l'innovation duale.", "Et la remise du Prix de l'innovation duale."),
    ("v9", "Jeudi vingt-six novembre deux mille vingt-six, au Campus Cyber de La Défense.",
           "Jeudi 26 novembre 2026, au Campus Cyber de La Défense."),
    ("v10", "DualTech. Inscrivez-vous dès maintenant.", "DualTech. Inscrivez-vous dès maintenant."),
]

if __name__ == "__main__":
    from piper import PiperVoice, SynthesisConfig
    modeles = Path(sys.argv[1])
    voix = PiperVoice.load(str(modeles / "fr_FR-siwis-medium.onnx"))
    conf = SynthesisConfig(length_scale=0.95, noise_scale=0.6, noise_w_scale=0.75)
    sortie = Path(__file__).resolve().parent / "voix"
    sortie.mkdir(exist_ok=True)
    infos = []
    for ident, texte, st in PHRASES:
        f = sortie / f"{ident}.wav"
        with wave.open(str(f), "wb") as w:
            voix.synthesize_wav(texte, w, syn_config=conf)
        with wave.open(str(f)) as w:
            d = w.getnframes() / w.getframerate()
        infos.append({"id": ident, "texte": texte, "sous_titre": st, "duree": round(d, 3)})
        print(f"{ident} {d:5.2f} s  {texte}")
    (sortie / "voix.json").write_text(json.dumps(infos, ensure_ascii=False, indent=1))
    print("total", round(sum(i["duree"] for i in infos), 2), "s")

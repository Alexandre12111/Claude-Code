import sherpa_onnx, soundfile as sf, numpy as np, sys, json, os
VO = [
 ("vo1", "Depuis vingt-neuf ans, Leytonne trouve la valeur que vous ne voyiez pas."),
 ("vo2", "Aujourd'hui, on construit celle que vous n'avez jamais eu le temps de bâtir."),
 ("vo3", "Vos équipes ne manquent pas d'idées. Elles manquent d'un chemin pour les construire."),
 ("vo4", "Décrivez l'outil dont vous avez besoin."),
 ("vo5", "DigiCraft le construit sous vos yeux. Sans développeur, sans ticket ailleti."),
 ("vo6a", "Sept cent cinquante et un collaborateurs Leytonne l'utilisent déjà."),
 ("vo6b", "Cent vingt-neuf applications en ligne."),
 ("vo6c", "Zéro développeur."),
 ("vo7", "Hébergé en Europe, accompagné par les équipes Leytonne, à vos côtés."),
 ("vo8", "DigiCraft. Bild, sans coder."),
]
speed=float(sys.argv[1]) if len(sys.argv)>1 else 0.9
d='kokoro-multi-lang-v1_0'
mc=sherpa_onnx.OfflineTtsModelConfig(kokoro=sherpa_onnx.OfflineTtsKokoroModelConfig(
    model=f'{d}/model.onnx', voices=f'{d}/voices.bin', tokens=f'{d}/tokens.txt',
    data_dir=f'{d}/espeak-ng-data', dict_dir=f'{d}/dict',
    lexicon=f'{d}/lexicon-us-en.txt,{d}/lexicon-zh.txt', lang='fr'), num_threads=4)
tts=sherpa_onnx.OfflineTts(sherpa_onnx.OfflineTtsConfig(model=mc))
os.makedirs('vo', exist_ok=True); meta={}
for key,text in VO:
    a=tts.generate(text, sid=30, speed=speed)
    s=np.array(a.samples, dtype=np.float32)
    # trim silence
    thr=0.01; idx=np.where(np.abs(s)>thr)[0]
    s=s[max(0,idx[0]-240):min(len(s),idx[-1]+2400)]
    sf.write(f'vo/{key}.wav', s, a.sample_rate)
    meta[key]=round(len(s)/a.sample_rate,3)
    print(key, meta[key], flush=True)
json.dump(meta, open('vo/durations.json','w'), indent=1)
print('total', round(sum(meta.values()),2))

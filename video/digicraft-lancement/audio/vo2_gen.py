import sherpa_onnx as so, soundfile as sf, numpy as np, sys, os, json
d='sherpa-onnx-supertonic-3-tts-int8-2026-05-11'
mc=so.OfflineTtsModelConfig(supertonic=so.OfflineTtsSupertonicModelConfig(duration_predictor=f'{d}/duration_predictor.int8.onnx',text_encoder=f'{d}/text_encoder.int8.onnx',vector_estimator=f'{d}/vector_estimator.int8.onnx',vocoder=f'{d}/vocoder.int8.onnx',tts_json=f'{d}/tts.json',unicode_indexer=f'{d}/unicode_indexer.bin',voice_style=f'{d}/voice.bin'),num_threads=4)
tts=so.OfflineTts(so.OfflineTtsConfig(model=mc))
VO=[("v1","Une idée d'outil ? Elle attend des mois un créneau informatique."),
("v2","Et si elle existait aujourd'hui ?"),
("v3","Voici DigiCraft."),
("v4","Vous décrivez votre besoin, en français."),
("v5","DigiCraft construit l'application sous vos yeux."),
("v6","Zéro développeur. Zéro ticket à la DSI."),
("v7","En ligne le jour même."),
("v8","Chez Lèïtonne, sept cent cinquante et un collaborateurs l'utilisent déjà."),
("v9","Cent vingt-neuf applications en ligne."),
("v10","Hébergé en Europe. Accompagné par nos équipes."),
("v11","DigiCraft. Build, sans coder.")]
sid=int(sys.argv[1]); speed=float(sys.argv[2]); out=sys.argv[3]
os.makedirs(out,exist_ok=True); meta={}
for k,t in VO:
    g=so.GenerationConfig(); g.sid=sid; g.speed=speed; g.num_steps=16; g.extra={'lang':'fr'}
    a=tts.generate(t,g); s=np.array(a.samples,dtype=np.float32)
    idx=np.where(np.abs(s)>0.01)[0]; s=s[max(0,idx[0]-300):min(len(s),idx[-1]+6000)]
    sf.write(f'{out}/{k}.wav',s,a.sample_rate); meta[k]=round(len(s)/a.sample_rate,3)
json.dump(meta,open(f'{out}/durations.json','w'),indent=1); print(meta, 'total', round(sum(meta.values()),2))

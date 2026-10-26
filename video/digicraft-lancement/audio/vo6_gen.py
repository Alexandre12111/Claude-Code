import sherpa_onnx as so, soundfile as sf, numpy as np, sys, os, json
d='sherpa-onnx-supertonic-3-tts-int8-2026-05-11'
mc=so.OfflineTtsModelConfig(supertonic=so.OfflineTtsSupertonicModelConfig(duration_predictor=f'{d}/duration_predictor.int8.onnx',text_encoder=f'{d}/text_encoder.int8.onnx',vector_estimator=f'{d}/vector_estimator.int8.onnx',vocoder=f'{d}/vocoder.int8.onnx',tts_json=f'{d}/tts.json',unicode_indexer=f'{d}/unicode_indexer.bin',voice_style=f'{d}/voice.bin'),num_threads=4)
tts=so.OfflineTts(so.OfflineTtsConfig(model=mc))
VO=[("c01","Depuis près de trente ans, Lèïtonne aide les entreprises à révéler la valeur qu'elles ne voient pas."),
("c02","Aujourd'hui, ce gisement se trouve aussi dans votre productivité."),
("c03","Vos équipes savent précisément quels outils leur manquent."),
("c04","Mais ces projets attendent, souvent des mois, un créneau informatique."),
("c05","DigiCraft change la donne."),
("c06","Le principe est simple."),
("c07","Vos équipes décrivent leur besoin, en langage naturel."),
("c08","DigiCraft conçoit l'application : écrans, données et indicateurs."),
("c09","Elle est en ligne le jour même, sur ordinateur comme sur mobile."),
("c10","Chez Lèïtonne, plus de sept cent cinquante collaborateurs l'utilisent déjà, avec plus de trois cent cinquante applications en production."),
("c11","Données hébergées en Europe, gouvernance intégrée, et nos équipes à vos côtés à chaque étape."),
("c12","Des outils conçus par vos équipes, pour vos équipes."),
("c13","DigiCraft, par Lèïtonne. Parlons-en lors d'une démonstration.")]
sid=int(sys.argv[1]); speed=float(sys.argv[2]); out=sys.argv[3]; steps=int(sys.argv[4]) if len(sys.argv)>4 else 32
os.makedirs(out,exist_ok=True); meta={}
for k,t in VO:
    g=so.GenerationConfig(); g.sid=sid; g.speed=speed; g.num_steps=steps; g.extra={'lang':'fr'}
    a=tts.generate(t,g); s=np.array(a.samples,dtype=np.float32)
    idx=np.where(np.abs(s)>0.01)[0]; s=s[max(0,idx[0]-1200):min(len(s),idx[-1]+9000)]
    sf.write(f'{out}/{k}.wav',s,a.sample_rate); meta[k]=round(len(s)/a.sample_rate,2)
json.dump(meta,open(f'{out}/durations.json','w'),indent=1); print(meta)

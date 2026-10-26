import sherpa_onnx as so, soundfile as sf, numpy as np, sys, os, json
d='sherpa-onnx-supertonic-3-tts-int8-2026-05-11'
mc=so.OfflineTtsModelConfig(supertonic=so.OfflineTtsSupertonicModelConfig(duration_predictor=f'{d}/duration_predictor.int8.onnx',text_encoder=f'{d}/text_encoder.int8.onnx',vector_estimator=f'{d}/vector_estimator.int8.onnx',vocoder=f'{d}/vocoder.int8.onnx',tts_json=f'{d}/tts.json',unicode_indexer=f'{d}/unicode_indexer.bin',voice_style=f'{d}/voice.bin'),num_threads=4)
tts=so.OfflineTts(so.OfflineTtsConfig(model=mc))
VO=[("b01","Notre métier, depuis près de trente ans ? Trouver la valeur que les entreprises laissent sur la table."),
("b02","Et aujourd'hui, le plus gros gisement se cache dans votre quotidien."),
("b03","Des idées d'outils, vos équipes en ont plein."),
("b04","Le souci, c'est qu'elles attendent. Parfois des mois."),
("b05","Alors on a imaginé autre chose."),
("b06","Le principe est tout simple."),
("b07","Vous expliquez ce qu'il vous faut, avec vos mots, comme à un collègue."),
("b08","DigiCraft s'occupe du reste : les écrans, les données, les calculs."),
("b09","Un clic, et votre outil est en ligne. Sur ordinateur comme sur mobile."),
("b10","Chez nous, c'est déjà adopté : plus de sept cent cinquante collègues s'en servent, et plus de trois cent cinquante applis tournent au quotidien."),
("b11","Côté sécurité, vous êtes serein : vos données restent en Europe, et nos équipes vous accompagnent du début à la fin."),
("b12","Parce que c'est votre outil. Vraiment le vôtre."),
("b13","DigiCraft, par Lèïtonne. Demandez votre démo, on vous montre tout.")]
sid=int(sys.argv[1]); speed=float(sys.argv[2]); out=sys.argv[3]; steps=int(sys.argv[4]) if len(sys.argv)>4 else 32
os.makedirs(out,exist_ok=True); meta={}
for k,t in VO:
    g=so.GenerationConfig(); g.sid=sid; g.speed=speed; g.num_steps=steps; g.extra={'lang':'fr'}
    a=tts.generate(t,g); s=np.array(a.samples,dtype=np.float32)
    idx=np.where(np.abs(s)>0.01)[0]; s=s[max(0,idx[0]-300):min(len(s),idx[-1]+4000)]
    sf.write(f'{out}/{k}.wav',s,a.sample_rate); meta[k]=round(len(s)/a.sample_rate,2)
json.dump(meta,open(f'{out}/durations.json','w'),indent=1); print(meta)

import sherpa_onnx as so, soundfile as sf, numpy as np, sys, os, json
d='sherpa-onnx-supertonic-3-tts-int8-2026-05-11'
mc=so.OfflineTtsModelConfig(supertonic=so.OfflineTtsSupertonicModelConfig(duration_predictor=f'{d}/duration_predictor.int8.onnx',text_encoder=f'{d}/text_encoder.int8.onnx',vector_estimator=f'{d}/vector_estimator.int8.onnx',vocoder=f'{d}/vocoder.int8.onnx',tts_json=f'{d}/tts.json',unicode_indexer=f'{d}/unicode_indexer.bin',voice_style=f'{d}/voice.bin'),num_threads=4)
tts=so.OfflineTts(so.OfflineTtsConfig(model=mc))
VO=[("a01","Depuis 1997, Lèïtonne révèle et capte la valeur que ses clients ne voient pas."),
("a02","Aujourd'hui, cette valeur a un nom : votre productivité."),
("a03","Vos équipes ne manquent pas d'idées."),
("a04","Elles manquent d'un outil pour les construire."),
("a05","Découvrez la solution qui répond à votre besoin."),
("a06","Comment ça marche ?"),
("a07","Vous décrivez votre besoin, en français."),
("a08","DigiCraft construit l'application sous vos yeux."),
("a09","Vous publiez. Et c'est en ligne le jour même !"),
("a10","Déjà adopté par nos équipes :"),
("a11","cent pour cent no code,"),
("a12","plus de sept cent cinquante utilisateurs,"),
("a13","plus de trois cent cinquante applications déployées."),
("a14","Sérénité et sécurité assurées : hébergé en Europe, gouvernance intégrée, et l'accompagnement des équipes Lèïtonne."),
("a15","Créé par vous, pour vous."),
("a16","DigiCraft. Bild, sans coder."),
("a17","Demandez votre démo !")]
sid=int(sys.argv[1]); speed=float(sys.argv[2]); out=sys.argv[3]; steps=int(sys.argv[4]) if len(sys.argv)>4 else 32
os.makedirs(out,exist_ok=True); meta={}
for k,t in VO:
    g=so.GenerationConfig(); g.sid=sid; g.speed=speed; g.num_steps=steps; g.extra={'lang':'fr'}
    a=tts.generate(t,g); s=np.array(a.samples,dtype=np.float32)
    idx=np.where(np.abs(s)>0.01)[0]; s=s[max(0,idx[0]-300):min(len(s),idx[-1]+4000)]
    sf.write(f'{out}/{k}.wav',s,a.sample_rate); meta[k]=round(len(s)/a.sample_rate,2)
json.dump(meta,open(f'{out}/durations.json','w'),indent=1); print(meta)

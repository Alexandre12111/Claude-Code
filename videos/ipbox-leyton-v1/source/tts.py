import asyncio, json, sys, subprocess, edge_tts, ssl, edge_tts.communicate as _c
_c._SSL_CTX = ssl.create_default_context(cafile="/root/.ccr/ca-bundle.crt")
V="fr-FR-RemyMultilingualNeural"; S=json.load(open("script.json"))
async def one(text,path,rate):
    c=edge_tts.Communicate(text,V,rate=rate,boundary="WordBoundary");f=open(path,'wb');ws=[]
    async for ch in c.stream():
        if ch['type']=='audio':f.write(ch['data'])
        elif ch['type']=='WordBoundary':ws.append({'t':ch['offset']/1e7,'d':ch['duration']/1e7,'w':ch['text']})
    f.close();d=float(subprocess.check_output(['ffprobe','-v','0','-show_entries','format=duration','-of','csv=p=0',path]));return {'dur':d,'words':ws}
async def go():
    only=[int(x) for x in sys.argv[1:]]
    try: A=json.load(open(f'audio/{V}.json'))
    except Exception: A=[None]*len(S)
    A+= [None]*(len(S)-len(A))
    if not only:
        json.dump(await one("Qu'est-ce que le régime aille-pie-boxe ?","audio/intro.mp3","+2%"),open('audio/intro.json','w'),ensure_ascii=False)
    for i,s in enumerate(S):
        if only and i not in only: continue
        A[i]=await one(s['tts'],f'audio/{V}_{i}.mp3',"+6%");print(i,round(A[i]['dur'],2))
    json.dump(A,open(f'audio/{V}.json','w'),ensure_ascii=False,indent=1);print('total',round(sum(a['dur'] for a in A),2))
asyncio.run(go())

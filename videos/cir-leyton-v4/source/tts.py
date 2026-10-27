import asyncio, json, sys, edge_tts, subprocess
import ssl, edge_tts.communicate as _c
_c._SSL_CTX = ssl.create_default_context(cafile="/root/.ccr/ca-bundle.crt")
voice = sys.argv[1]; rate = sys.argv[2]
S = json.load(open("script.json"))
async def go():
    out=[]
    for i,s in enumerate(S):
        c = edge_tts.Communicate(s['tts'], voice, rate=rate, boundary="WordBoundary")
        words=[]; f=open(f'audio/{voice}_{i}.mp3','wb')
        async for ch in c.stream():
            if ch['type']=='audio': f.write(ch['data'])
            elif ch['type'] in ('WordBoundary','SentenceBoundary'):
                words.append({'t':ch['offset']/1e7,'d':ch['duration']/1e7,'w':ch['text'],'k':ch['type']})
        f.close()
        dur=float(subprocess.check_output(['ffprobe','-v','0','-show_entries','format=duration','-of','csv=p=0',f'audio/{voice}_{i}.mp3']))
        out.append({'dur':dur,'words':words})
        print(i, round(dur,2), len(words), words[:2])
    json.dump(out, open(f'audio/{voice}.json','w'), ensure_ascii=False, indent=1)
    print('total', round(sum(o['dur'] for o in out),2))
asyncio.run(go())

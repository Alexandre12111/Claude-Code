import json, re, unicodedata
VOICE='fr-FR-RemyMultilingualNeural'
LEAD, GAP, TOTAL = 0.35, 0.30, 45.0
S = json.load(open('script.json')); A = json.load(open(f'audio/{VOICE}.json'))
def norm(w): return re.sub(r"[^\w']", '', w.lower())
t = LEAD; scenes = []
for i,(s,a) in enumerate(zip(S,A)):
    words = [{'w':w['w'], 't':round(t+w['t'],3), 'e':round(t+w['t']+w['d'],3)} for w in a['words']]
    # subtitle line starts: match first two tokens
    starts = []; k = 0
    for line in s['sub']:
        toks = [norm(x) for x in line.split() if norm(x)][:2]
        while k < len(words):
            nxt = norm(words[k+1]['w']) if k+1 < len(words) else ''
            if norm(words[k]['w']) == toks[0] and (len(toks) < 2 or nxt == toks[1] or toks[1] in ('rd','30')):
                break
            k += 1
        assert k < len(words), (i, line)
        starts.append(words[k]['t']); k += 1
    end_speech = words[-1]['e']
    subs = [{'text':line, 'start':st, 'end': (starts[j+1] if j+1 < len(starts) else end_speech + 0.35)} for j,(line,st) in enumerate(zip(s['sub'], starts))]
    scenes.append({'start':round(t,3), 'dur':a['dur'], 'words':words, 'subs':subs})
    t += a['dur'] + GAP
json.dump({'total':TOTAL, 'fps':30, 'scenes':scenes}, open('timeline.json','w'), ensure_ascii=False, indent=1)
for sc in scenes:
    print(round(sc['start'],2), [ (x['text'][:25], x['start'], round(x['end'],2)) for x in sc['subs']])
print('voice ends', round(t-GAP,2))

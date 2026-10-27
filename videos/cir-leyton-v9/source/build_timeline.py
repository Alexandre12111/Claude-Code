import json, re, unicodedata
VOICE='fr-FR-RemyMultilingualNeural'
import sys
LEAD, GAP, TOTAL = 0.45, 0.45, float(sys.argv[1]) if len(sys.argv)>1 else 70.0
EXTRA = json.loads(sys.argv[2]) if len(sys.argv)>2 else {}
EXTRA = {int(k): v for k, v in EXTRA.items()}
EXTRA[3] = EXTRA.get(3, 0) + 0.9
S = json.load(open('script.json')); A = json.load(open(f'audio/{VOICE}.json'))
def norm(w):
    w = re.sub(r"[^\w']", '', w.lower())
    return 'cire' if w == 'cir' else ('rd' if w == 'erédé' else w)
INTRO_AT, INTRO_HOLD = 0.7, 1.15
IA = json.load(open('audio/intro.json'))
intro = {'start': INTRO_AT, 'dur': IA['dur'], 'words': [{'w': w['w'], 't': round(INTRO_AT + w['t'], 3), 'e': round(INTRO_AT + w['t'] + w['d'], 3)} for w in IA['words']]}
intro['subs'] = [{'text': "Qu'est-ce que le Crédit d'Impôt Recherche ?", 'start': intro['words'][0]['t'], 'end': intro['words'][-1]['e'] + .5, 'se': intro['words'][-1]['e']}]
t = INTRO_AT + IA['dur'] + INTRO_HOLD + LEAD; scenes = []
for i,(s,a) in enumerate(zip(S,A)):
    words = [{'w':w['w'], 't':round(t+w['t'],3), 'e':round(t+w['t']+w['d'],3)} for w in a['words']]
    # subtitle line starts: match first two tokens
    starts = []; k = 0
    for j0,line in enumerate(s['sub']):
        if j0==0:
            starts.append(words[0]['t']); k=1; continue
        toks = [norm(x) for x in line.split() if norm(x)][:2]
        while k < len(words):
            nxt = norm(words[k+1]['w']) if k+1 < len(words) else ''
            if norm(words[k]['w']) == toks[0] and (len(toks) < 2 or nxt == toks[1] or toks[1] in ('rd','30')):
                break
            k += 1
        assert k < len(words), (i, line)
        starts.append(words[k]['t']); k += 1
    end_speech = words[-1]['e']
    subs = [{'text':line, 'start':st, 'end': (starts[j+1] if j+1 < len(starts) else end_speech + 0.35), 'se': (starts[j+1]-0.12 if j+1 < len(starts) else end_speech)} for j,(line,st) in enumerate(zip(s['sub'], starts))]
    scenes.append({'start':round(t,3), 'dur':a['dur'], 'words':words, 'subs':subs})
    t += a['dur'] + GAP + EXTRA.get(i, 0)
json.dump({'total':TOTAL, 'fps':30, 'intro':intro, 'scenes':scenes}, open('timeline.json','w'), ensure_ascii=False, indent=1)
for sc in scenes:
    print(round(sc['start'],2), [ (x['text'][:25], x['start'], round(x['end'],2)) for x in sc['subs']])
print('voice ends', round(t-GAP,2))

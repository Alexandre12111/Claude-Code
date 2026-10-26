"""Génère la voix off des 11 marchés et la cale sur les plans (minutage long).
Anglais : voix Kokoro du film « Bring your ideas to life ». Autres langues : voix Supertonic 3 n° 5,
la plus proche en timbre (similarité 0,72). Usage : python3 gen_vo_markets.py en uk de ..."""
import json, os, sys
import numpy as np
import soundfile as sf
import sherpa_onnx as so
from scipy.signal import resample_poly
from vo_markets import CUES, END, MARKETS, split

HERE = os.path.dirname(os.path.abspath(__file__))
TTS = os.environ.get('TTS_DIR', '/tmp/claude-0/-home-user-Claude-Code/b2d349ab-5523-52d5-8843-d14029de2d00/scratchpad/tts')
SR = 48000
LAG = 0.8   # retard toléré sur le plan suivant
CAPS = {'kokoro': 1.08, 'kokoro_fr': 1.08, 'supertonic': 1.15}   # vitesse maximale restant naturelle pour chaque voix
BASE = {'kokoro': 0.97, 'kokoro_fr': 1.0, 'supertonic': 1.05}
# Français : voix Kokoro « ff_siwis » (n° 30), seule voix entraînée sur du français natif. Débit naturel : le texte
# tient dans le minutage sans accélération ni pauses resserrées (Supertonic parlait 35 % trop lentement en français).
TIGHT = {}
CAP_CODE = {}
YC = [(r'\bYouCraft\b', 'You Craft')]
# Graphies lues par la voix : « tool » néerlandais, et « YouCraft » en deux mots là où cette graphie sonne
# le plus près de l'anglais (mesuré par alignement sur la voix anglaise et par les formants du « you »).
SAYAS = {'nl': [(r'\btools\b', 'toels'), (r'\btool\b', 'toel')], 'fr': YC, 'de': YC, 'sv': YC}
_eng = {}


def engine(kind):
    if kind in _eng:
        return _eng[kind]
    if kind in ('kokoro', 'kokoro_fr'):
        M = f'{TTS}/kokoro-multi-lang-v1_0'
        extra = {'lang': 'fr'} if kind == 'kokoro_fr' else {}
        mc = so.OfflineTtsModelConfig(kokoro=so.OfflineTtsKokoroModelConfig(model=f'{M}/model.onnx', voices=f'{M}/voices.bin', tokens=f'{M}/tokens.txt', data_dir=f'{M}/espeak-ng-data', lexicon=f'{M}/lexicon-us-en.txt', **extra), num_threads=4)
    else:
        T = f'{TTS}/sherpa-onnx-supertonic-3-tts-int8-2026-05-11'
        mc = so.OfflineTtsModelConfig(supertonic=so.OfflineTtsSupertonicModelConfig(duration_predictor=f'{T}/duration_predictor.int8.onnx', text_encoder=f'{T}/text_encoder.int8.onnx', vector_estimator=f'{T}/vector_estimator.int8.onnx', vocoder=f'{T}/vocoder.int8.onnx', tts_json=f'{T}/tts.json', unicode_indexer=f'{T}/unicode_indexer.bin', voice_style=f'{T}/voice.bin'), num_threads=4)
    _eng[kind] = so.OfflineTts(so.OfflineTtsConfig(model=mc))
    return _eng[kind]


def say(kind, lang, text, speed):
    tts = engine(kind)
    if kind == 'kokoro':
        a = tts.generate(text.replace('DigiCraft', 'Didgy Craft').replace('YouCraft', 'You Craft'), sid=16, speed=speed)
    elif kind == 'kokoro_fr':
        a = tts.generate(text.replace('YouCraft', 'You Craft'), sid=30, speed=speed)
    else:
        # Deux-points lus comme une virgule : la voix y marquait une pause trop longue.
        text = text.replace(' : ', ', ').replace(': ', ', ')
        # Prononciation des mots anglais d'usage courant (le néerlandais lisait « tool » comme « tol »).
        import re
        for pat, rep in SAYAS.get(lang, []):
            text = re.sub(pat, rep, text)
        g = so.GenerationConfig(); g.sid = 5; g.speed = speed; g.num_steps = 32; g.extra = {'lang': lang}
        a = tts.generate(text, g)
    x = np.asarray(a.samples, dtype=np.float64)
    x = resample_poly(x, SR, a.sample_rate)
    env = np.abs(x)
    idx = np.where(env > 0.02 * env.max())[0]
    x = x[max(0, idx[0] - int(0.04 * SR)): min(len(x), idx[-1] + int(0.18 * SR))]
    return x


def tighten(x, keep):
    """Ramène les silences internes plus longs que keep à keep (fondu de 10 ms), sans toucher au débit."""
    from scipy.ndimage import uniform_filter1d
    e = np.sqrt(uniform_filter1d(x ** 2, int(0.02 * SR)))
    q = e < 0.04 * e.max()
    out, last, i, f = [], 0, 0, int(0.01 * SR)
    while i < len(q):
        if q[i]:
            j = i
            while j < len(q) and q[j]:
                j += 1
            if i > 0 and j < len(q) and (j - i) / SR > keep:
                a = i + int(keep * SR / 2); b = j - int(keep * SR / 2)
                seg = x[last:a].copy(); seg[-f:] *= np.linspace(1, 0, f)
                out.append(seg); x = x.copy(); x[b:b + f] *= np.linspace(0, 1, f); last = b
            i = j
        else:
            i += 1
    out.append(x[last:])
    return np.concatenate(out)


def fit(durs, capped=()):
    """Pose chaque réplique sur son plan. Renvoie les débuts et la réplique à accélérer (ou None) :
    si une réplique déborde parce qu'elle a commencé en retard, on accélère la précédente."""
    starts, todo = [], None
    for i, d in enumerate(durs):
        early = CUES[i] - (0.0 if i == 0 else 0.35)
        s = early if i == 0 else max(early, starts[-1] + durs[i - 1] + 0.14)
        starts.append(s)
        limit = CUES[i + 1] + LAG if i + 1 < len(CUES) else END - 1.2
        if s + d > limit + 1e-3 and todo is None:
            late = s > early + 1e-3
            for j in ([i - 1, i] if late else [i, i - 1]):
                if 0 <= j and j not in capped:
                    todo = (j, d - (limit - s)); break
    return starts, todo


def run(code):
    kind, lang, lines, _ = MARKETS[code]
    base = BASE[kind]
    CAP = CAP_CODE.get(code, CAPS[kind])
    keep = TIGHT.get(code)
    say_ = lambda text, sp: tighten(say(kind, lang, text, sp), keep) if keep else say(kind, lang, text, sp)
    out = os.path.join(HERE, 'vo_mk', code); os.makedirs(out, exist_ok=True)
    speeds = [base] * len(lines)
    clips = [say_(split(l)[1], base) for l in lines]
    capped = set()
    for _ in range(40):
        starts, todo = fit([len(c) / SR for c in clips], capped)
        if todo is None:
            break
        j, over = todo
        d = len(clips[j]) / SR
        sp = min(CAP, speeds[j] * d / max(0.3, d - over) * 1.01)
        if sp <= speeds[j] + 1e-3:
            capped.add(j); continue
        speeds[j] = sp
        if sp >= CAP:
            capped.add(j)
        clips[j] = say_(split(lines[j])[1], speeds[j])
    starts, _ = fit([len(c) / SR for c in clips])
    for i, st in enumerate(starts):
        lim = CUES[i + 1] + LAG if i + 1 < len(CUES) else END - 1.2
        if st + len(clips[i]) / SR > lim + 1e-3:
            print(code, 'réplique', i + 1, 'déborde de', round(st + len(clips[i]) / SR - lim, 2), 's')
    meta = []
    for i, (l, c, s) in enumerate(zip(lines, clips, starts)):
        sub, spoken = split(l)
        sf.write(os.path.join(out, f'l{i:02d}.wav'), c.astype(np.float32), SR)
        meta.append({'sub': sub, 'tts': spoken, 'start': round(s, 3), 'dur': round(len(c) / SR, 3), 'speed': round(speeds[i], 3)})
    json.dump(meta, open(os.path.join(out, 'meta.json'), 'w'), ensure_ascii=False, indent=1)
    print(code, 'ok', [m['speed'] for m in meta])


if __name__ == '__main__':
    for c in sys.argv[1:]:
        run(c)

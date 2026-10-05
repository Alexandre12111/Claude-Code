"""Voix off guide (synthèse Kokoro, voix masculine posée) pour le film DigiCraft façon « Bring your ideas to life »."""
import os, sys, sherpa_onnx, soundfile as sf
M = sys.argv[1] if len(sys.argv) > 1 else os.environ['KOKORO']
SID = int(os.environ.get('SID', 16))  # am_michael
SPEED = float(os.environ.get('SPEED', 0.92))
LINES = [
    "Imagine being able to build any app,",
    "for any team,",
    "for any process,",
    "at any scale. Just by describing it.",
    "Meet Didgy Craft.",
    "A complete AI creation platform, made for businesses with an idea, and the drive to bring it to life.",
    "Describe what you need, and watch it take shape.",
    "You can plan, customize, and expand, in any direction.",
    "From tools built for one team, to platforms ready for the whole company.",
    "From quick wins, to enterprise-grade apps,",
    "everything is built in, and ready to launch.",
    "It's a new way of working, where your teams can go as far as their ideas take them.",
    "Can you imagine it?",
    "Let's make it real.",
]
cfg = sherpa_onnx.OfflineTtsConfig(model=sherpa_onnx.OfflineTtsModelConfig(kokoro=sherpa_onnx.OfflineTtsKokoroModelConfig(
    model=f'{M}/model.onnx', voices=f'{M}/voices.bin', tokens=f'{M}/tokens.txt', data_dir=f'{M}/espeak-ng-data',
    lexicon=f'{M}/lexicon-us-en.txt'), num_threads=4))
tts = sherpa_onnx.OfflineTts(cfg)
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'vo_b44')
for i, t in enumerate(LINES):
    a = tts.generate(t, sid=SID, speed=SPEED)
    sf.write(f'{OUT}/l{i:02d}.wav', a.samples, a.sample_rate)
    print(i, round(len(a.samples) / a.sample_rate, 2), t)

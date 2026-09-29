"""Mixage du tutoriel (musique + sound design, sans voix)."""
import os, subprocess
import numpy as np
import soundfile as sf

HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
SR, END = 48000, 60.0
N = int(61.5 * SR)

music, _ = sf.read(os.path.join(STEMS, 'music_tuto.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx_tuto.wav'))
pad = lambda x: np.pad(x[:N], ((0, max(0, N - len(x))), (0, 0)))
mix = (pad(music) * 0.8 + pad(sfx) * 1.5)[: int(END * SR)]
t = np.arange(len(mix)) / SR
fade = np.ones(len(mix))
fade[t > END - 0.9] = np.clip(1 - (t[t > END - 0.9] - (END - 0.9)) / 0.9, 0, 1)
mix *= fade[:, None]
tmp = os.path.join(STEMS, 'mix_tuto_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix_tuto.wav')], check=True)
print('mix_tuto ok')

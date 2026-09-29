"""Mixage anglaise sans voix (sans voix) : musique validée + sound design V5."""
import os, subprocess
import numpy as np
import soundfile as sf

HERE = os.path.dirname(os.path.abspath(__file__))
STEMS = os.path.join(HERE, 'stems')
SR, END = 48000, 58.25
N = int(60.0 * SR)

music, _ = sf.read(os.path.join(STEMS, 'music_en.wav'))
sfx, _ = sf.read(os.path.join(STEMS, 'sfx_en.wav'))
music = np.pad(music[:N], ((0, max(0, N - len(music))), (0, 0))); sfx = np.pad(sfx[:N], ((0, max(0, N - len(sfx))), (0, 0)))
mix = music * 0.8 + sfx * 1.5
mix = mix[: int(END * SR)]
t = np.arange(len(mix)) / SR
fade = np.ones(len(mix))
fade[t > END - 0.75] = np.clip(1 - (t[t > END - 0.75] - (END - 0.75)) / 0.75, 0, 1)
mix *= fade[:, None]
tmp = os.path.join(STEMS, 'mix_en_musique_raw.wav')
sf.write(tmp, (mix / np.max(np.abs(mix)) * 0.9).astype(np.float32), SR)
subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', tmp, '-af', 'loudnorm=I=-14:TP=-1.2:LRA=9', '-ar', str(SR), os.path.join(STEMS, 'mix_en_musique.wav')], check=True)
print('mix_en_musique ok')

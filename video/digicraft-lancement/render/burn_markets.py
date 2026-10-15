"""Assemble image + mixage voix off et incruste les sous-titres. Usage : python3 burn_markets.py en uk ..."""
import os, subprocess, sys
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'audio'))
from vo_markets import MARKETS  # noqa: E402

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
NAME = {'fr': 'FR', 'en': 'EN', 'uk': 'UK_EN', 'us': 'US_EN', 'caen': 'Canada_EN', 'cafr': 'Canada_FR', 'de': 'DE', 'es': 'ES', 'it': 'IT', 'nl': 'NL', 'pl': 'PL', 'sv': 'SE'}

for code in sys.argv[1:]:
    # Version FR : voix enregistrée du client (mixage mix14) sur l'image FR au minutage long.
    vid = os.path.join(ROOT, 'out', 'video_cxfr2_silent.mp4' if code == 'fr' else MARKETS[code][3])
    aud = 'audio/stems/mix14.wav' if code == 'fr' else f'audio/stems/mk_{code}.wav'
    out = os.path.join(ROOT, 'out', f'DigiCraft_CognitX_{NAME[code]}_58s_voiceover_subtitles.mp4')
    vf = f"subtitles=out/subs/{code}.ass:fontsdir=assets/fonts/ttf"
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', vid, '-i', aud, '-map', '0:v', '-map', '1:a', '-vf', vf,
                    '-c:v', 'libx264', '-preset', 'slow', '-crf', '21', '-pix_fmt', 'yuv420p', '-c:a', 'aac', '-b:a', '224k', '-shortest', '-movflags', '+faststart', out], check=True, cwd=ROOT)
    print(code, 'ok', round(os.path.getsize(out) / 1e6, 1), 'Mo', out)

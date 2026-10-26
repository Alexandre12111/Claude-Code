"""Assemble image + mixage voix off et incruste les sous-titres. Usage : python3 burn_markets.py en uk ..."""
import os, subprocess, sys
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'audio'))
from vo_markets import MARKETS  # noqa: E402

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
NAME = {'fr': 'FR', 'en': 'EN', 'uk': 'UK_EN', 'us': 'US_EN', 'caen': 'Canada_EN', 'ie': 'IE_EN', 'cafr': 'Canada_FR', 'de': 'DE', 'es': 'ES', 'it': 'IT', 'nl': 'NL', 'pl': 'PL', 'sv': 'SE'}

for code in sys.argv[1:]:
    # YouCraft : les 12 versions, FR comprise, sont lues par la voix de synthèse sur l'image renommée.
    # YC_BRAND=1 : image et sous-titres à la nouvelle charte YouCraft.
    brand = os.environ.get('YC_BRAND') == '1'
    vid = os.path.join(ROOT, 'out', f'video_yb_{code}_silent.mp4' if brand else MARKETS[code][3])
    aud = f'audio/stems/mk_{code}.wav'
    out = os.path.join(ROOT, 'out', f'YouCraft_{"NewBrand" if brand else "CognitX"}_{NAME[code]}_58s_voiceover_subtitles.mp4')
    vf = f"subtitles=out/subs_yb/{code}.ass:fontsdir=assets/fonts/ttf_yc" if brand else f"subtitles=out/subs/{code}.ass:fontsdir=assets/fonts/ttf"
    subprocess.run(['ffmpeg', '-y', '-v', 'error', '-i', vid, '-i', aud, '-map', '0:v', '-map', '1:a', '-vf', vf,
                    '-c:v', 'libx264', '-preset', 'slow', '-crf', '21', '-pix_fmt', 'yuv420p', '-c:a', 'aac', '-b:a', '224k', '-shortest', '-movflags', '+faststart', out], check=True, cwd=ROOT)
    print(code, 'ok', round(os.path.getsize(out) / 1e6, 1), 'Mo', out)
